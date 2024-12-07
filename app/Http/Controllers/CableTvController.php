<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Exports\UnpaidMonthlyCableTVExport;
use App\Exports\CableTVMonthlyPaymentExport;

use App\Imports\UnpaidMonthlyCableTVImport;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PDF_Code128;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\PdfParser\StreamReader;

use App\Models\User;
use App\Models\Logs;
use App\Models\Customer;
use App\Models\UnitAddress;
use App\Models\CableTVService;
use App\Models\CableTVHistory;
use App\Models\BuildingLocation;
use App\Models\ServiceAdvancePayment;

class CableTvController extends Controller
{

    /**
    * Show CableTV Subscribers Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function subscribers(Request $request)
    {       
        $user = new User;
        $unit_address = new UnitAddress;
        $ctvhistory = new CableTVHistory;

        $year = '';
        $month = '';
        $addressID = '';
        $monthYear = '';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');
        
        if($request->input('monthYear') || $request->input('addressID')){
            if($request->input('monthYear')){
                $monthYear = $request->get('monthYear');
                $monthYearArr = explode('-',$monthYear);
                $month = $monthYearArr[0];
                $year = $monthYearArr[1];
            }
            if($request->input('addressID')){
                $addressID = $request->input('addressID');
            }        
            $subscribersArr = $ctvhistory->where('entry_type', 'NR')
                                ->when($monthYear, function($query) use ($monthYear,$month,$year) {
                                    return $query->whereMonth('start_date_time','=',$month)->whereYear('start_date_time','=',$year); })
                                ->when($addressID, function($query) use ($addressID) {
                                    return $query->where('address_id', '=', $addressID); })
                                        ->groupBy('customer_id')->orderByDesc('id')->paginate($records)->withQueryString(); 
        }else{ 
            $subscribersArr = $ctvhistory->where('entry_type', 'NR')->orderByDesc('id')->paginate($records)->withQueryString();
        }
        return view('users.idc.cabletv.index')
                    ->with(['userArr'=>$userArr, 'subscribersArr'=>$subscribersArr,'monthYear'=>$monthYear,'addressID'=>$addressID, 'records'=>$records, 'unitsAddressArr'=>$unitsGroupAddressArr]);
    }

    /**
    * Show New Connection Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function new_connection(Request $request)
    {    
        $user = new User;
        $cabletv = new CableTVService;

        $userArr= $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-subscribers');
        }

        $cabletvServicesArr = $cabletv->where('status','A')->get();
        $customersArr = DB::select('select `customers`.`id`,`customers`.`address_id`,`customers`.`type`, `customers`.`name` ,`customers`.`shop_name` from `customers` left join `cabletv_history` on `customers`.`id` = `cabletv_history`.`customer_id` where `cabletv_id` is not null and `customers`.`id` not in (select distinct `cabletv_history`.`customer_id` from `cabletv_history`) order by `customers`.`id` desc');
        
        return view('users.idc.cabletv.new_connection')
                ->with(['customersArr'=>$customersArr,'cabletvServicesArr'=>$cabletvServicesArr, 'userArr'=>$userArr]);
    }

    /**
    * Validate & Store New Connection.
    *
    * @param  Request  $request
    * @return Response
    */
    public function store_connection(Request $request)
    {   
        $rules = array(
            'installation_date' => 'required|date|date_format:d-m-Y|date_equals:subscribe_date',
            'installation_time' => 'required|date_format:h:i A',
            // Customer Personal Details
            'customer_name' => 'required',
            // CableTV Plan
            'cabletv_plan' => 'required',
            'extra_tv' => !empty($request->input('extra_tv')) ? 'required|numeric|in:1,2,3,4,5' : '',
            'subscribe_date' => 'required|date|date_format:d-m-Y',
            'period' => 'required|numeric|in:1,2,3,4,5,6,7,8,9,10,11,12',
            'monthly_fee'=> 'required|numeric|between:0.00,9999.99',
            //Payment
            'installation_fee' => 'required|numeric|between:0.00,9999.99',
            'extra_tvs_fee' => !empty($request->input('extra_tv')) ? 'required|numeric|between:0.00,9999.99' : '',
            'total_fee' => 'required|numeric|between:0.00,9999.99',
            'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.00,9999.99' : '',
            'total_amount' => 'required|numeric|between:0.00,99999.99',
            'payment_mode' => 'required|in:CA,BA,CH,OT',
            'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
            'paid' => 'required|in:Y,N',
            'remark' => !empty($request->input('remark')) ? 'required' : ''
        );
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');            
            return redirect()->route('new-cabletv-connection')
                        ->withErrors($validator) 
                            ->withInput();
        }else {
            // saving cabletv history in database 
            $logs = new Logs;
            $ctvhistory = new CableTVHistory;
            $payment = new ServiceAdvancePayment;

            $day = (int) dayOFGivenDate(dataBaseFormat($request->input('subscribe_date')));
            $period = ($day <=16) ? ($request->input('period')-1) : $request->input('period');

            $ctvhistory->address_id = $request->input('address_id');
            $ctvhistory->customer_id = $request->input('customer_name');
            $ctvhistory->plan_id = $request->input('cabletv_plan');
            $ctvhistory->entry_type = 'NR'; //New Registration
            $ctvhistory->entry_status = 'PAY'; //PAYMENT
            $ctvhistory->registration_date = dataBaseFormat($request->input('installation_date'));
            $ctvhistory->start_date_time = dateTimeStampForDataBase($request->input('installation_date').' '.$request->input('installation_time'));
            $ctvhistory->customer_mobile = (customerDetailsByID($request->input('customer_name'))['type']=='P') ? rtrim($request->input('customer_mobile'), "_") : rtrim($request->input('shop_mobile'), "_");
            $ctvhistory->extra_tvs = $request->input('extra_tv');
            $ctvhistory->subscribe_start_date = dataBaseFormat($request->input('subscribe_date'));            
            $ctvhistory->subscribe_end_date = cableTVSubscribeEndDate(dataBaseFormat($request->input('subscribe_date')), $period);
            $ctvhistory->period = $request->input('period');
            $ctvhistory->installation_fee = amountFormat2($request->input('installation_fee'));
            $ctvhistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ctvhistory->extra_tvs_fee = amountFormat2($request->input('extra_tvs_fee'));
            $ctvhistory->months_total_fee = amountFormat2($request->input('total_fee'));
            $ctvhistory->others_fee = amountFormat2($request->input('others_fee'));
            $ctvhistory->total_amount = amountFormat2($request->input('total_amount'));
            $ctvhistory->payment_mode = $request->input('payment_mode');
            $ctvhistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ctvhistory->payment_by = 'CP';
            $ctvhistory->paid = $request->input('paid');
            $ctvhistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('installation_date')) : NULL;
            $ctvhistory->remark = $request->input('remark');
            $ctvhistory->user_id = $request->session()->get('userID');
            $flag = $ctvhistory->save(); 

            $payment->address_id = $request->input('address_id');
            $payment->customer_id = $request->input('customer_name');
            $payment->service_type = 'CT'; //Cable TV
            $payment->start_date = dataBaseFormat($request->input('subscribe_date'));
            $payment->end_date = cableTVSubscribeEndDate(dataBaseFormat($request->input('subscribe_date')), $period);
            $payment->period = $request->input('period');
            $payment->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $payment->total_amount = amountFormat2($request->input('total_fee'));
            $payment->payment_mode = $request->input('payment_mode');
            $payment->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $payment->payment_by = 'CP';
            $payment->paid = $request->input('paid');
            $payment->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('installation_date')) : NULL;
            $payment->remark = $request->input('remark');
            $payment->user_id = $request->session()->get('userID');                
            $flag2 = $payment->save();           
                               
            if($flag) 
            {
                // Entry in logs table
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("New cable tv connection has done reference id [".$ctvhistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();
                
                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('cabletv-subscribers');
            } 
            else 
            {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('new-cabletv-connection');
            }
        }
    }

    /**
    * Show CableTV Change Location Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function change_location(Request $request)
    {    
        $user = new User;
        $unit_address = new UnitAddress;
        $ctvhistory = new CableTVHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-subscribers');
        }

        $excludeAddressIDsArr = $ctvhistory->select('address_id')->distinct()->whereDate('start_date_time','>=',cableTVCurrentMonthStartDate())->whereDate('start_date_time','<=',cableTVCurrentMonthEndDate())->where('entry_type','!=','TS')->get()->all();
        $finalExcludeAddressIDs = collect($excludeAddressIDsArr)->pluck('address_id');
        $unitsGroupAddressArr = $unit_address->whereNotIn('id',$finalExcludeAddressIDs)->where('status','A')->get()->groupBy('location_id');
        $customersArr = DB::select('select * from (select max(`id`) as `id`,`customer_id`, `address_id`,`entry_type`,`start_date_time` from `cabletv_history` group by `customer_id` order by `start_date_time` desc) as `cust_max` where `customer_id` not in (select `customer_id` from `cabletv_history` where `entry_type` in("TS")) group by `cust_max`.`customer_id`');
        
        return view('users.idc.cabletv.change_location')
                    ->with(['customersArr'=>$customersArr, 'unitsAddressArr'=>$unitsGroupAddressArr, 'userArr'=>$userArr]);
    }

    /**
    * Validate & Update CableTV Relocation.
    *
    * @param  Request  $request
    * @return Response
    */
    public function update_location(Request $request)
    {   
        $rules = array(
        'service_date' => 'required|date|date_format:d-m-Y|after_or_equal:last_entry_date',
        'service_time' => 'required|date_format:h:i A',
        // Customer Personal Details
        'customer_name' => 'required',
        // Customer Current Details
        // Relocation Details
        'relocate_address' => 'required',
        'relocate_customer_mobile' => 'required|alpha_dash|max:12',
        // Payment Details
        'reinstallation_fee' => 'required|numeric|between:0.00,9999.99',
        'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.00,9999.99' : '',
        'total_amount' => 'required|numeric|between:0.00,99999.99',
        'payment_mode' => 'required|in:CA,BA,CH,OT',
        'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
        'payment_by' => 'required|in:CP',
        'paid' => 'required|in:Y,N',
        // Remark
        'remark' => !empty($request->input('remark')) ? 'required' : ''
    );
    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        // validation unsuccessful!
        Session::flash('color','warning');
        Session::flash('icon','fas fa-exclamation-triangle');
        Session::flash('msg','Please fill all mandatory fields!');    
        return redirect()->route('cabletv-change-location')
                    ->withErrors($validator) 
                        ->withInput();
        
    } else {  
            $logs = new Logs;
            $customer = new Customer;
            $ctvhistory = new CableTVHistory;
            $payment = new ServiceAdvancePayment;

            $ctvhistory->address_id = $request->input('relocate_address');
            $ctvhistory->customer_id = $request->input('customer_name');
            $ctvhistory->plan_id = $request->input('plan_id');
            $ctvhistory->entry_type = 'CL'; //Change Location
            $ctvhistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ctvhistory->customer_mobile = rtrim($request->input('relocate_customer_mobile'), "_");
            $ctvhistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ctvhistory->reinstallation_fee = amountFormat2($request->input('reinstallation_fee'));
            $ctvhistory->others_fee = amountFormat2($request->input('others_fee'));
            $ctvhistory->total_amount = amountFormat2($request->input('total_amount'));
            $ctvhistory->payment_mode = $request->input('payment_mode');
            $ctvhistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT'])? $request->input('payment_description'):NULL;
            $ctvhistory->payment_by = $request->input('payment_by');
            $ctvhistory->paid= $request->input('paid');
            $ctvhistory->paid_date= ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ctvhistory->remark = $request->input('remark');
            $ctvhistory->refrence_id = $request->input('refrence');
            $ctvhistory->user_id = $request->session()->get('userID');
            $flag = $ctvhistory->save();

            if(in_array($request->input('refrence_type'),['MP','TS'])){
                $flag1 = true;
            }else{
                $flag1 = $ctvhistory->where('id',$request->input('refrence'))->
                            update([
                                'end_date_time' => previousDateTimeStampForGivenDate($request->input('service_date').' '.$request->input('service_time')) 
                            ]);
            }

            $advancePaidArr = $payment->select('id')->where('address_id',$request->input('previous_address_id'))->where('customer_id',$request->input('customer_name'))->where('service_type','CT')->whereDate('end_date','>=',dataBaseFormat($request->input('service_date')))->orderByDesc('start_date')->first();
            if(is_object($advancePaidArr)){
                $flag2 = $payment->where('id',$advancePaidArr->id)->
                            update([
                                'address_id' => $request->input('relocate_address'), 
                                'user_id' => $request->session()->get('userID') 
                            ]);
            }else{
                $flag2 = true;
            }    

            $mobile_field = customerDetailsByID($request->input('customer_name'))['type']=='P' ? 'mobile' : 'shop_mobile';
            $flag4 = $customer->where('id',$request->input('customer_name'))->
                        update([
                            'address_id' => $request->input('relocate_address'), 
                            $mobile_field => rtrim($request->input('relocate_customer_mobile'), "_"), 
                            'updated_by' => $request->session()->get('userID')
                        ]);
                
            if ($flag) {
                // Entry in logs table for change internet location
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("CableTV change location has done reference id [".$ctvhistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('cabletv-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('cabletv-change-location')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
    * Show CableTV Terminate page.
    *
    * @param  Request  $request
    * @return View
    */
    public function terminate_service(Request $request)
    {   
        $user = new User;
        $ctvhistory = new CableTVHistory;    

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-subscribers');
        }
        
        $customersArr = DB::select('select `id`, `customer_id`,`address_id`,`entry_type`,`start_date_time` from `cabletv_history` where (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `cabletv_history` group by `customer_id`) and `entry_type` not in ("TS")');

        return view('users.idc.cabletv.terminate_service')
                    ->with(['customersArr'=>$customersArr, 'userArr'=>$userArr]);
    }

    /**
    * Validate & Update CableTV Termination.
    *
    * @param  Request  $request
    * @return Response
    */
    public function update_termination(Request $request)
    {   
        $rules = array(
            'service_date' => 'required|date|date_format:d-m-Y|after_or_equal:last_entry_date',
            'service_time' => 'required|date_format:h:i A',
            // Customer Personal Details
            'customer_name' => 'required',
            // Payment Details
            'refund_amount' => 'required|numeric|between:0.00,9999.99',
            'due_amount' => 'required|numeric|between:0.00,9999.99',
            'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.00,9999.99' : '',
            'total_amount' => 'required|numeric|between:0.00,99999.99',
            'payment_mode' => 'required|in:CA,BA,CH,OT',
            'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
            'payment_by' => 'required|in:CP,CR',
            'paid' => 'required|in:Y,N',
            // Remark
            'remark' => !empty($request->input('remark')) ? 'required' : ''
        ); 
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');          
            return redirect()->route('cabletv-terminate-service')
                        ->withErrors($validator) 
                            ->withInput();     
        } 
        else
        {   
            // saving cabletv service in database
            $logs = new Logs;
            $ctvhistory = new CableTVHistory;

            $ctvhistory->address_id = $request->input('address_id');
            $ctvhistory->customer_id = $request->input('customer_name');
            $ctvhistory->plan_id = $request->input('plan_id');
            $ctvhistory->entry_type = 'TS'; //Terminate Service
            $ctvhistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ctvhistory->end_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ctvhistory->terminate_date = dataBaseFormat($request->input('service_date'));
            $ctvhistory->customer_mobile = (customerDetailsByID($request->input('customer_name'))['type']=='P') ? $request->input('customer_mobile') : $request->input('shop_mobile');
            $ctvhistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ctvhistory->refund_amount = amountFormat2($request->input('refund_amount'));
            $ctvhistory->due_amount = amountFormat2($request->input('due_amount'));
            $ctvhistory->others_fee = amountFormat2($request->input('others_fee'));
            $ctvhistory->total_amount = amountFormat2($request->input('total_amount'));
            $ctvhistory->payment_mode = $request->input('payment_mode');
            $ctvhistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ctvhistory->payment_by = $request->input('payment_by');
            $ctvhistory->paid = $request->input('paid');
            $ctvhistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ctvhistory->remark = $request->input('remark');
            $ctvhistory->refrence_id = $request->input('refrence');
            $ctvhistory->user_id = $request->session()->get('userID');
            $flag = $ctvhistory->save();            

            if ($flag) {
                // Entry in logs table for terminate cabletv service
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Terminate cabletv service has done reference id [".$ctvhistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();
                
                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('cabletv-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('cabletv-terminate-service')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
    * Show CableTV Reconnection page.
    *
    * @param  Request  $request
    * @return View
    */
    public function reconnect_service(Request $request)
    {
        $user = new User;
        $ctvhistory = new CableTVHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-history');
        }

        $customersArr = $ctvhistory->select('id','customer_id','address_id','entry_type','start_date_time')->where('entry_type','TS')->get();

        return view('users.idc.cabletv.reconnect_service')
                    ->with(['customersArr'=>$customersArr, 'userArr'=>$userArr]);
    }

    /**
    * Validate & Update CableTV Reconnection.
    *
    * @param  Request  $request
    * @return Response
    */
    public function update_reconnection(Request $request)
    {
        $rules = array(
            'service_date' => 'required|date|date_format:d-m-Y|after_or_equal:end_date',
            'service_time' => 'required|date_format:h:i A',
            // Customer Personal Details
            'customer_name' => 'required',
            // Terminate Details
            'start_date' => 'required|date|date_format:d-m-Y',
            'end_date' => 'required|date|date_format:d-m-Y|after_or_equal:start_date',
            // Reconnect Details
            'previous_due' => 'required|numeric|between:0.00,99999.99',
            'reconnect_fee' => 'required|numeric|between:0.00,9999.99',
            'others_fee'=> !empty($request->input('others_fee')) ? 'required|numeric|between:0.01,9999.99' : '',
            'total_amount' => 'required|numeric|between:0.00,99999.99',
            'payment_mode' => 'required|in:CA,BA,CH,OT',
            'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
            'payment_by' => 'required|in:CP',
            'paid' => 'required|in:Y,N',
            // Remark
            'remark' => !empty($request->input('remark')) ? 'required' : ''
        ); 
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');        
            return redirect()->route('cabletv-reconnect-service')
                        ->withErrors($validator) 
                            ->withInput();        
        } 
        else
        {   
            $logs = new Logs;
            $ctvhistory = new CableTVHistory;

            $ctvhistory->address_id = $request->input('address_id');
            $ctvhistory->customer_id = $request->input('customer_name');
            $ctvhistory->plan_id = $request->input('plan_id');
            $ctvhistory->entry_type = 'RC'; //Reconnect Service
            $ctvhistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ctvhistory->customer_mobile = (customerDetailsByID($request->input('customer_name'))['type']=='P') ? $request->input('customer_mobile') : $request->input('shop_mobile');
            $ctvhistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ctvhistory->reconnect_fee = amountFormat2($request->input('reconnect_fee'));
            $ctvhistory->due_amount = amountFormat2($request->input('previous_due'));
            $ctvhistory->others_fee = amountFormat2($request->input('others_fee'));
            $ctvhistory->total_amount = amountFormat2($request->input('total_amount'));
            $ctvhistory->payment_mode = $request->input('payment_mode');
            $ctvhistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ctvhistory->payment_by = $request->input('payment_by');
            $ctvhistory->paid = $request->input('paid');
            $ctvhistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ctvhistory->remark = $request->input('remark');
            $ctvhistory->refrence_id = $request->input('refrence');
            $ctvhistory->user_id = $request->session()->get('userID');
            $flag = $ctvhistory->save();

            if(($request->input('refrence_type')=='TS')){
                $flag1 = $ctvhistory->where('id',$request->input('refrence'))->
                            update([
                                'suspension_end_date' => dataBaseFormat($request->input('end_date'))
                            ]);
            }else{
                $flag1=true;
            }       
                
            if ($flag) {
                // Entry in logs table for reconnect cabletv service
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Reconnect cabletv service has done reference id [".$ctvhistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('cabletv-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('cabletv-reconnect-service')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
    * Show CableTV Change Owner page.
    *
    * @param  Request  $request
    * @return View
    */
    public function change_owner(Request $request)
    {   
        $user = new User;
        $ctvhistory = new CableTVHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-history');
        }

        $customersArr = $ctvhistory->select('id','customer_id','address_id','entry_type','start_date_time')->where('entry_type','!=','TS')->get();

        return view('users.idc.cabletv.change_owner')
                    ->with(['customersArr'=>$customersArr, 'userArr'=>$userArr]);
    }

    /**
    * Validate & Update CableTV Owner.
    *
    * @param  Request  $request
    * @return Response
    */
    public function update_owner(Request $request)
    {
        $rules = array(
            'service_date' => 'required|date|date_format:d-m-Y|after:end_date',
            'service_time' => 'required|date_format:h:i A',
            // Customer Personal Details
            'customer_name' => 'required',
            'customer_rename' => 'required|string',
            'customer_mobile' => 'required|alpha_dash|max:12',
            // Payment Details
            'rename_fee' => 'required|numeric|between:0.00,9999.99',
            'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.01,9999.99' : '',
            'total_amount' => 'required|numeric|between:0.00,99999.99',
            'payment_mode' => 'required|in:CA,BA,CH,OT',
            'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
            'payment_by' => 'required|in:CP',
            'paid' => 'required|in:Y,N',
            // Remark
            'remark' => !empty($request->input('remark')) ? 'required' : ''
        ); 
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');        
            return redirect()->route('cabletv-change-owner')
                        ->withErrors($validator) 
                            ->withInput();        
        } 
        else
        {   
            $logs = new Logs;
            $ctvhistory = new CableTVHistory;

            $ctvhistory->address_id = $request->input('address_id');
            $ctvhistory->customer_id = $request->input('customer_name');
            $ctvhistory->plan_id = $request->input('plan_id');
            $ctvhistory->entry_type = 'CH'; //Change Owner
            $ctvhistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ctvhistory->customer_mobile = rtrim($request->input('customer_mobile'), "_");
            $ctvhistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ctvhistory->change_owner_fee = amountFormat2($request->input('rename_fee'));
            $ctvhistory->others_fee = amountFormat2($request->input('others_fee'));
            $ctvhistory->total_amount = amountFormat2($request->input('total_amount'));
            $ctvhistory->payment_mode = $request->input('payment_mode');
            $ctvhistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ctvhistory->payment_by = $request->input('payment_by');
            $ctvhistory->paid = $request->input('paid');
            $ctvhistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ctvhistory->remark = $request->input('remark');
            $ctvhistory->refrence_id = $request->input('refrence');
            $ctvhistory->user_id = $request->session()->get('userID');
            $flag = $ctvhistory->save();

            // Entry in logs table for rename customer cabletv service
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Rename cabletv customer old name: ".customerDetailsByID($request->input('customer_name'))['name']." => new name: ".$request->input('customer_rename')." customer id [".$request->input('customer_name')."].");
            $logs->ip_address = $request->ip();
            $logs->save();

            $mobile_field = customerDetailsByID($request->input('customer_name'))['type']=='P' ? 'mobile' : 'shop_mobile';
            $flag1 = $customer->where('id',$request->input('customer_name'))->
                        update([
                            'name' => $request->input('customer_rename'), 
                            $mobile_field => rtrim($request->input('customer_mobile'), "_"), 
                            'updated_by' => $request->session()->get('userID')
                        ]);
                
            if ($flag) {
                // Entry in logs table for rename customer cabletv service
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Rename cabletv service has done reference id [".$ctvhistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Customer rename has been done successfully!');
                return redirect()->route('cabletv-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Something went wrong, Please try again!');
                return redirect()->route('cabletv-change-owner')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
    * Show CableTV History Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function history(Request $request)
    {
        $user = new User;
        $unit_address = new UnitAddress;
        $ctvhistory = new CableTVHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('cabletv-subscribers');
        }
        
        $year = '';
        $month = '';
        $monthYear = '';
        $entry_type = '';
        $addressID = '';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');

        if($request->input('monthlyYear') || $request->input('entry_type') || $request->input('addressID')){
            if($request->input('monthlyYear')){
                $monthYearArr = explode('-',$request->input('monthlyYear'));
                $month = $monthYearArr[0];
                $year = $monthYearArr[1];
                $monthYear = $request->input('monthlyYear');
            }
            if($request->input('entry_type')){
                $entry_type = $request->input('entry_type');
            }
            if($request->input('addressID')){
                $addressID = $request->input('addressID');
            }
            $ctvhistoryArr = $ctvhistory->where('entry_type','!=','MP')
                                ->when($monthYear, function($query) use ($monthYear,$month,$year) {
                                    return $query->whereMonth('start_date_time','=',$month)->whereYear('start_date_time','=',$year); })
                                ->when($addressID, function($query) use ($addressID) {
                                    return $query->where('address_id', '=', $addressID); })
                                ->when($entry_type, function($query) use ($entry_type) {
                                    return $query->where('entry_type', '=', $entry_type); })
                                        ->orderByDesc('start_date_time')->paginate($records)->withQueryString();
        }else{ 
            $ctvhistoryArr = $ctvhistory->whereIn('entry_type',["NR","CL","RC","TS","CH"])->orderByDesc('start_date_time')->paginate($records)->withQueryString();
        }

        return view('users.idc.cabletv.history')
                    ->with(['userArr'=>$userArr, 'ctvhistoryArr'=>$ctvhistoryArr,'monthYear'=>$monthYear,'entry_type'=>$entry_type,'addressID'=>$addressID, 'records'=>$records, 'unitsAddressArr'=>$unitsGroupAddressArr]);
    }

    /**
    * Add CableTV Advance Payment.
    *
    * @param  Request  $request
    * @return View
    */
    public function edit_advance_payment(Request $request)
    {
        $user = new User;
        $ctvhistory = new CableTVHistory;

        $customerID = $request->segment('2');
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        $ctvhistoryArr = $ctvhistory->select('id','customer_id', 'address_id','start_date_time','monthly_fee')->where('customer_id', $customerID)->orderByDesc('start_date_time')->get()->first();
        
        return view('users.idc.cabletv.edit_advance_payment')
                ->with(['userArr'=>$userArr, 'ctvhistoryArr'=>$ctvhistoryArr]);
    }

    /**
    * Validate & Update CableTV Advance Payment.
    *
    * @param  Request  $request
    * @return Response
    */
    public function update_advance_payment(Request $request)
    {    
        $rules = array(
            'from_date' => 'required|date|date_format:d-m-Y',
            'period' => 'required|numeric|between:1,12',
            // Payment Details
            'total_amount' => 'required|numeric|between:0.00,99999.99',
            'payment_mode' => 'required|in:CA,BA,CH,OT',
            'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
            'paid' => 'required|in:Y,N',
            'paid_date' => 'required|date|date_format:d-m-Y',
            // Remark
            'remark' => !empty($request->input('remark')) ? 'required|string' : ''
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');        
            return redirect()->route('edit-cabletv-advance-payment',['id'=>$request->input('customer_id')])
                        ->withErrors($validator) 
                            ->withInput();         
        } 
        else
        {   
            $logs = new Logs;
            $ctvhistory = new CableTVHistory;
            $payment = new ServiceAdvancePayment;
            
            $payment->address_id = $request->input('address_id');
            $payment->customer_id = $request->input('customer_id');
            $payment->service_type = 'CT'; //Cable TV
            $payment->start_date = dataBaseFormat($request->input('from_date'));
            $payment->end_date = cableTVSubscribeEndDate(dataBaseFormat($request->input('from_date')), ($request->input('period')-1));
            $payment->period = $request->input('period');
            $payment->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $payment->total_amount = amountFormat2($request->input('total_amount'));
            $payment->payment_mode = $request->input('payment_mode');
            $payment->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $payment->payment_by = 'CP';
            $payment->paid = $request->input('paid');
            $payment->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('paid_date')) : NULL;
            $payment->remark = $request->input('remark');
            $payment->user_id = $request->session()->get('userID');
            $flag = $payment->save();
            
            $flag1 = $ctvhistory->where('customer_id',$request->input('customer_id'))->
                            where('entry_type','NR')->
                            update([
                                'subscribe_start_date' => dataBaseFormat($request->input('from_date')),
                                'subscribe_end_date' => cableTVSubscribeEndDate(dataBaseFormat($request->input('from_date')), ($request->input('period')-1)),
                                'period' => $request->input('period') 
                            ]);        
                
            if($flag) 
            {
                // Entry in logs table for cabletv advance payment
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Cable TV advance payment has done reference id [".$payment->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('cabletv-advance-payment');
            } 
            else
            {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('edit-cabletv-advance-payment',['id'=>$request->input('customer_id')])
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
    * Show Cable TV Advance Payment Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function advance_payment(Request $request)
    {
        $user = new User;
        $unit_address = new UnitAddress;
        $payment = new ServiceAdvancePayment;
        
        $addressID = '';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        $customerArr = $payment->select('customer_id')->where('service_type', 'CT')->orderByDesc('created_at')->get()->all();
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');

        if($request->input('addressID')){
            $addressID = $request->input('addressID');  
            $advancePaymentArr = $payment->select('id','address_id','customer_id','start_date','end_date','period','monthly_fee','total_amount','paid_date','remark')->where('service_type', 'CT')->when($addressID, function($query) use ($addressID) {
                return $query->where('address_id', '=', $addressID); })->orderByDesc('paid_date')->paginate($records)->withQueryString();
        }else{
            $advancePaymentArr = $payment->select('id','address_id','customer_id','start_date','end_date','period','monthly_fee','total_amount','paid_date','remark')->where('service_type', 'CT')->orderByDesc('paid_date')->paginate($records)->withQueryString();
        }
        
        return view('users.idc.cabletv.advance_payment')
                    ->with(['userArr'=>$userArr, 'customerArr'=>$customerArr, 'unitsAddressArr'=>$unitsGroupAddressArr, 'addressID'=>$addressID, 'records'=>$records, 'advancePaymentArr'=>$advancePaymentArr]);
    }

    /**
    * Show CableTV Transaction History Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function view_transaction(Request $request)
    {
        $user = new User;
        $ctvhistory = new CableTVHistory;   
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('cabletv-history');
        }

        $id = $request->segment('2');
        $ctvhistoryArr = $ctvhistory->where('id', $id)->get()->first();

        return view('users.idc.cabletv.view')
                    ->with(['ctvhistoryArr'=>$ctvhistoryArr,'userArr'=>$userArr]);
    }

    /**
    * Show Monthly Invoice Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function monthly_invoice(Request $request)
    {
        $user = new User;
        $unit_address = new UnitAddress;
        $ctvhistory = new CableTVHistory;
        $blocation = new BuildingLocation;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->read)){
            // Permission not granteds
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('cabletv-subscribers');
        }

        $paid = '';
        $year = '';
        $month = '';
        $custID = '';
        $unitType = '';
        $addressID = '';    
        $monthYear = '';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $blocationArr = $blocation->where('status','A')->get();
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');

        if($request->input('monthlyYear') || $request->get('unitType') || $request->input('addressID') || $request->input('paid'))
        {
            if($request->input('monthlyYear')){
                $monthYear = $request->input('monthlyYear');
                $monthYearArr = explode('-',$monthYear);
                $month = $monthYearArr[0];
                $year = $monthYearArr[1];
            }
            if($request->get('unitType')){
                $unitType = $request->get('unitType');
            }
            if($request->input('addressID')){
                $addressID = $request->input('addressID');
            }
            if($request->get('custID')){
                $custID = $request->get('custID');
            }
            if($request->input('paid')){
                $paid = $request->input('paid');
            }
            $customerArr = $ctvhistory->select('cabletv_history.id','customer_id','start_date_time','address_id','plan_id','invoice_number','monthly_invoice_date','monthly_fee','total_amount','paid','paid_date')
                                ->leftJoin('units_address', 'cabletv_history.address_id', '=' ,'units_address.id')
                                ->leftJoin('buildings_location', 'units_address.location_id', '=' ,'buildings_location.id')
                                ->where('entry_type', 'MP')
                                ->when($monthYear, function($query) use ($monthYear,$month,$year) {
                                    return $query->whereMonth('monthly_invoice_date','=',$month)->whereYear('monthly_invoice_date','=',$year); })
                                ->when($unitType, function($query) use ($unitType) {
                                    return $query->where('buildings_location.id', '=', $unitType); })
                                ->when($addressID, function($query) use ($addressID) {
                                    return $query->where('address_id', '=', $addressID); })
                                ->when($custID, function($query) use ($custID) {
                                    return $query->where('customer_id', '=', $custID); })
                                ->when($paid, function($query) use ($paid) {
                                    return $query->where('paid', '=', $paid); })
                                        ->orderBy('units_address.id')->orderByDesc('invoice_number')->paginate($records)->withQueryString();
        }
        else
        {
            $customerArr = $ctvhistory->select('id','customer_id','start_date_time','address_id','plan_id','invoice_number','monthly_invoice_date','monthly_fee','total_amount','paid','paid_date')->where('entry_type','MP')->orderByDesc('invoice_number')->paginate($records)->withQueryString();
        }
        
        return view('users.idc.cabletv.monthly_invoice')
                    ->with(['userArr'=>$userArr, 'customerArr'=>$customerArr, 'blocationArr'=>$blocationArr, 'unitsAddressArr'=>$unitsGroupAddressArr, 'monthYear'=>$monthYear, 'unitType'=>$unitType, 'addressID'=>$addressID, 'custID'=>$custID, 'paid'=>$paid, 'records'=>$records]);
    }

    
    /**
    * Generate Monthly Payment Invoices.
    *
    * @param  Request  $request
    * @return Response
    */
    public function monthly_payment_invoice(Request $request)
    {      
        $user = new User;
        $ctvhistory = new CableTVHistory;

        $userArr= $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-subscribers');
        }
        
        if(!empty($request->input('invoice_month_year'))){        
            $startDate = cableTVGenerateStartMonthlyPaymentDate($request->input('invoice_month_year'));
            $endDate = cableTVGenerateEndMonthlyPaymentDate($request->input('invoice_month_year'));
            $invoiceGeneratedDate = nextDateOFGivenDate($endDate);        
            if(currentDate2() < $invoiceGeneratedDate){
                // Already Generated
                Session::flash('color','warning');
                Session::flash('icon','fa fa-exclamation-triangle');
                Session::flash('msg','Invoice can not generate before '.datePickerFormat($invoiceGeneratedDate).'.');
                return redirect()->route('cabletv-monthly-invoice',['monthlyYear'=>$request->input('invoice_month_year')]);
            }
            //$ctvhistory->select('customer_id')->distinct()->whereDate('start_date_time','>=',$startDate)->whereDate('start_date_time','<=',$endDate)->whereNotIn('entry_type',['TS'])->where('plan_level','!=','DOWNGRADE')->groupBy('customer_id')->orderByDesc('start_date_time')->get();
            $monthlyCustomerArr =  DB::select("select distinct `customer_id` from `cabletv_history` where (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `cabletv_history` group by `customer_id`) and date(`start_date_time`) >= '".$startDate."' and date(`start_date_time`) <= '".$endDate."' and `entry_type` not in ('TS') and `plan_level` != 'DOWNGRADE' group by `customer_id` order by `start_date_time` desc");
            $alreadyGeneratedArr= $ctvhistory->select('customer_id')->distinct()->where('entry_type','MP')->whereDate('monthly_invoice_date',$invoiceGeneratedDate)->get();
            if(count($alreadyGeneratedArr)>0){
                // Already Generated
                Session::flash('color','warning');
                Session::flash('icon','fa fa-exclamation-triangle');
                Session::flash('msg','Monthly invoices already generated.');
                return redirect()->route('cabletv-monthly-invoice',['monthlyYear'=>$request->input('invoice_month_year')]);
            }
            $finalCustomerIDs = collect($monthlyCustomerArr)->pluck('customer_id');        
            $customerMonthlyPaymentArr = $ctvhistory->select('id','address_id','customer_id','plan_id','entry_type','start_date_time','end_date_time','subscribe_start_date','subscribe_end_date','customer_mobile','monthly_fee','paid','paid_date')->whereIn('customer_id',$finalCustomerIDs)->whereDate('start_date_time','>=',dataBaseFormat($startDate))->whereDate('start_date_time','<=',dataBaseFormat($endDate))->orderByDesc('start_date_time')->get()->groupBy('customer_id')->toArray();
           
            //pad($customerMonthlyPaymentArr);
            if(is_array($customerMonthlyPaymentArr) && count($customerMonthlyPaymentArr)>0){
                $count = 1;              
                foreach($customerMonthlyPaymentArr as $ckey=>$cmval){                
                    $logs = new Logs;
                    $ctvhistory = new CableTVHistory;
                    $total_amount = amountFormat2(0);                
                    foreach($cmval as $cmkey=>$ccmval){

                        if(count($cmval)>1 && in_array($ccmval['entry_type'],["NR","CL","RC","CH","TS"])){
                            
                            $total_amount = amountFormat2(0.00);
                            
                        }elseif(count($cmval)>1 && $ccmval['entry_type']=="MP") { 
                            
                            $total_amount = amountFormat2($ccmval['monthly_fee']);

                        }elseif(count($cmval)==1 && in_array($ccmval['entry_type'],["NR","MP"])){
                            
                            $total_amount = amountFormat2($ccmval['monthly_fee']);
                        }
                    }

                    //pa('Name=>'.customerNameByID($ckey)." address=>".buildingLocationAndUnitNumberByUnitID($cmval[0]['address_id']).' total=>'.$total_amount." plan_id=>".$cmval[0]['plan_id']." entry_type=>".$cmval[0]['entry_type']." month_year=>".$request->input('invoice_month_year'));
                    
                    $payment = new ServiceAdvancePayment;
                    $advancePaidArr = $payment->select('start_date','end_date','paid_date','remark')->where('address_id', $cmval[0]['address_id'])->where('customer_id', $ckey)->where('service_type', 'CT')->orderByDesc('id')->first();
                    
                    if(is_object($advancePaidArr) && !is_null($advancePaidArr)){
                        if(dataBaseFormat(cableTVGivenMonthEndDate($invoiceGeneratedDate)) <= dataBaseFormat($advancePaidArr->end_date)){
                            $paid_status = "Y";
                            $paid_date = dataBaseFormat($advancePaidArr->paid_date);
                            $remark = $advancePaidArr->remark;
                        }else{
                            $paid_status = "N";
                            $paid_date = NULL;
                            $remark = NULL;
                        }
                    } 
                    else{
                        $paid_status = "N";
                        $paid_date = NULL;
                        $remark = NULL;
                    }
                    $ctvhistory->address_id = $cmval[0]['address_id'];
                    $ctvhistory->customer_id = $ckey;
                    $ctvhistory->plan_id = $cmval[0]['plan_id'];
                    $plan_level = (in_array($cmval[0]['entry_type'],["TS"])) ? 'DOWNGRADE' : 'NORMAL';
                    $ctvhistory->plan_level = $plan_level;
                    $ctvhistory->entry_type = 'MP'; //Monthly Payment
                    $ctvhistory->start_date_time = dateTimeStampForDataBase($invoiceGeneratedDate.' '.currentHourMinute());
                    $ctvhistory->end_date_time = dateTimeStampForDataBase(cableTVGivenMonthEndDate($invoiceGeneratedDate).' '.currentHourMinute());
                    $ctvhistory->monthly_invoice_date = $invoiceGeneratedDate;
                    $ctvhistory->invoice_number = cableTVMonthlyInvoiceNumber($invoiceGeneratedDate,$count);
                    $ctvhistory->customer_mobile= $cmval[0]['customer_mobile'];
                    $ctvhistory->period =  intval(1);
                    $ctvhistory->monthly_fee =  amountFormat2($cmval[0]['monthly_fee']);
                    $ctvhistory->total_amount = $total_amount;
                    $ctvhistory->payment_mode = 'BA'; //Bank
                    $ctvhistory->payment_by = 'CP'; //Customer Pay
                    $ctvhistory->paid = $paid_status;
                    $ctvhistory->paid_date = $paid_date;
                    $ctvhistory->remark = $remark;
                    $ctvhistory->refrence_id = $cmval[0]['id'];
                    $ctvhistory->user_id = $request->session()->get('userID');
                    $flag = $ctvhistory->save();

                    if(in_array($cmval[0]['entry_type'],['MP','TS'])){
                        $flag1 = true;
                    }else{
                        $flag1 = $ctvhistory->where('id',$cmval[0]['id'])->
                                        update([
                                            'end_date_time'=>previousDateTimeStampForGivenDate($invoiceGeneratedDate.' '.currentHourMinute()) 
                                        ]);
                    }
                      
                    if($flag) {
                    
                        // Entry in logs table
                        $logs->user_id = $request->session()->get('userID');
                        $logs->logs_area = logsArea("CableTV monthly payment gereration has done reference id [".$ctvhistory->id."].");
                        $logs->ip_address = $request->ip();
                        $logs->save();
                    } else {
                        // Entry in logs table
                        $logs->user_id = $request->session()->get('userID');
                        $logs->logs_area = logsArea("CableTV monthly payment gereration has problem reference id [".$ctvhistory->id."].");
                        $logs->ip_address = $request->ip();
                        $logs->save();
                    }
                    $count++;
                } //die;
            }
            // Monthly Payment Generated Successfully
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Monthly invoices has been generated successfully.');
            return redirect()->route('cabletv-monthly-invoice',['monthlyYear'=>$request->input('invoice_month_year')]);
        }else{
            // Month & Year Not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month & year!');
            return redirect()->route('cabletv-monthly-invoice',['monthlyYear'=>$request->input('invoice_month_year')]);
        } 
    }

    /**
    * Show Edit Monthly Invoice Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function edit_monthly_invoice(Request $request)
    {
        
        $user = new User;
        $ctvhistory = new CableTVHistory;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('cabletv-monthly-invoice');
        }

        $id = $request->segment('2');
        $ctvhistoryArr = $ctvhistory->where('id', $id)->first();    
        
        return view('users.idc.cabletv.edit_monthly_invoice')
                    ->with(['userArr'=>$userArr, 'ctvhistoryArr'=>$ctvhistoryArr]);
    }

    /**
    * Validate & Update Monthly Invoice.
    *
    * @param  Request  $request
    * @return View
    */
    public function update_monthly_invoice(Request $request)
    {   
        $rules = array(
            'paid_date' => 'required|date|date_format:d-m-Y',
            'total_amount' => 'required|numeric|between:0.00,99999.99',
            'paid' => 'required|in:Y,N',
            'payment_mode' => 'required|in:CA,BA,CH,OT',
            'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
            'remark' => !empty($request->input('remark')) ? 'required' : ''
        );    
        $validator = Validator::make($request->all(), $rules);

        $id = $request->input('refrenceID');
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');            
            return redirect()->route('edit-cabletv-monthly-invoice',[$id])
                        ->withErrors($validator) 
                            ->withInput();     
        } 
        else
        {   
            $logs = new Logs;
            $ctvhistory = new CableTVHistory;
                
            $flag = $ctvhistory->where('id',$id)->
                    update([
                        'total_amount' => amountFormat2($request->input('total_amount')), 
                        'payment_mode' => $request->input('payment_mode'),
                        'payment_description' => $request->input('payment_description'),
                        'paid' => $request->input('paid'),
                        'paid_date' => dataBaseFormat($request->input('paid_date')), 
                        'remark' => $request->input('remark'),
                        'user_id' => $request->session()->get('userID') 
                    ]);
                
            if ($flag) {
                // Entry in logs table for update monthly payment invoice
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("CableTV monthly payment has been updated reference id [".$id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
                return redirect()->route('cabletv-monthly-invoice');
            } else {
                // database updation was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not updated, please try again!');
                return redirect()->route('edit-cabletv-monthly-invoice',[$id]);
            }
                    
        }
    }

    /**
    * Export Monthly Payment.
    *
    * @param  Request  $request
    * @return Response
    */
    public function export_monthly_payment(Request $request)
    {
        $user = new User;  

        $userArr = $user->where('id', $request->session()->get('userID'))->first();    

        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('cabletv-monthly-invoice');
        }
        
        if($request->input('export_month_year'))
        {
            $monthYear = $request->input('export_month_year');
            $monthYearArr = explode('-',$monthYear);
            $month = $monthYearArr[0];
            $year = $monthYearArr[1];
            return Excel::download(new CableTVMonthlyPaymentExport($month, $year), 'CableTV Monthly Payment for '.monthNumberToName($month).' '.$year.'.xlsx');
        }
        else
        {
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('cabletv-monthly-invoice');
        }
    }

    /**
    * Export Unpaid Invoices.
    *
    * @param  Request  $request
    * @return Response
    */
    public function export_unpaid_cabletv(Request $request)
    {
        $user = new User; 

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('cabletv-monthly-invoice');
        }

        if($request->input('monthYear')){
            $monthYear = $request->input('monthYear');
            $monthYearArr = explode('-',$monthYear);
            $month = $monthYearArr[0];
            $year = $monthYearArr[1];
            return Excel::download(new UnpaidMonthlyCableTVExport($month, $year), 'CableTV Unpaid Payment for '.monthNumberToName($month).' '.$year.'.xlsx');
        }else{
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('cabletv-monthly-invoice');
        }
    }

    /**
    * Show Import Unpaid Invoice Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function import_unpaid_cabletv(Request $request)
    {
        $user = new User;
        
        $userArr= $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->upload)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Upload permission not granted!');
            return redirect()->route('cabletv-monthly-invoice');
        }
        
        return view('users.idc.cabletv.import_unpaid')
                    ->with(['userArr'=>$userArr]);
    }

    /**
    * Validate & Update Momthly Unpaid Invoice.
    *
    * @param  Request  $request
    * @return Response
    */
    public function update_unpaid_cabletv(Request $request)
    {
        $rules = array(
            'import_file'=> 'required|mimes:xlsx,xls',
        );
        $validator = Validator::make($request->all(), $rules);   

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');            
            return redirect()->route('import-unpaid-internet')
                    ->withErrors($validator) 
                        ->withInput();            
        } 
        else
        {
            $logs = new Logs;
            Excel::import(new UnpaidMonthlyCableTVImport,request()->file('import_file'));
            // Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Unpaid invoices has been updated.");
            $logs->ip_address = $request->ip();
            $logs->save();

            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Unpaid invoices has been updated successfully.');
            return redirect()->route('cabletv-monthly-invoice');
        }
    }

    /**
    * Export Monthly Invoice.
    *
    * @param  Request  $request
    * @return Response
    */
    public function export_monthly_invoice(Request $request)
    {
        $user = new User;        
        $ctvhistory = new CableTVHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('cabletv-monthly-invoice');
        }    
        if($request->input('invoice_month_year'))
        {
            $monthYear = $request->input('invoice_month_year');
            $invoiceDate = cableTVStartMonthlyPaymentDate($monthYear);
            $ctvInvoiceArr = $ctvhistory->where('monthly_invoice_date', $invoiceDate)->orderBy('invoice_number')->get();
            $pdf = new Fpdi('P','mm', 'A4');

            foreach($ctvInvoiceArr as $ckey=>$cArr)
            {
                ini_set('memory_limit', -1);
                ini_set('max_execution_time', -1);

                $pdf->AddPage();
                $pdf->SetFont('times','B',14);
                $fileContent = file_get_contents(asset('assets/cabletv_pdf/monthly_invoice.pdf'),'rb');
                $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
                $tplIdx = $pdf->importPage(1);
                $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
                
                //Personal Application
                // Customer Name 
                customerDetailsByID($cArr->customer_id)['type']=='P'? $pdf->Text(11,50.5, customerDetailsByID($cArr->customer_id)['name']): $pdf->Text(11,50.5, customerDetailsByID($cArr->customer_id)['shop_name']);
                $pdf->SetFont('times','',10);
                // Customer Mobile 
                $pdf->Text(35, 61, $cArr->customer_mobile);
                // Customer Address 
                $pdf->Text(35, 71.8, unitAddressByUnitID($cArr->address_id));
                $pdf->Text(35, 76.8, 'CamKo Street, Sangkat Toul Sangke 2, Khan Russey Keo,');
                $pdf->Text(35, 81.8, 'Phnom Penh, Cambodia - 120707');
                // VAT Number
                $pdf->Text(68.5, 87.9, customerDetailsByID($cArr->customer_id)['vat_no']);
                // Invoice Date 
                $pdf->Text(175, 54.4, pdfDateFormat($cArr->monthly_invoice_date));
                // Due Date 
                $pdf->Text(175, 64.5, cableTVDueDate($cArr->monthly_invoice_date));
                // Customer CableTV ID 
                $pdf->Text(175, 74.3, customerNumberByID(customerDetailsByID($cArr->customer_id)['cabletv_id']));
                $pdf->SetFont('times','B',10);
                // Invoice Number 
                $pdf->Text(158, 87.3, invoiceName($cArr->invoice_number, $cArr->monthly_invoice_date));   
                $pdf->SetFont('times','',10);         
                // Service Date 
                $pdf->Text(95.5, 105.1, fromToCableTVMonthlyInvoiceDate($cArr->monthly_invoice_date)); 
                // Balance 
                $pdf->Text(174, 105.3, '$'.amountFormat2($cArr->total_amount));
                // Discount 
                //$pdf->Text(176, 132, '$'.amountFormat($cArr->discount));
                $pdf->SetFont('times','B',11);
                // Total Amount 
                $pdf->Text(174, 139.3, '$'.amountFormat2($cArr->total_amount));
                $pdf->Rect(12.2, 207, 3.2, 3.5, 'DF');
                // Monthly Fee
                $pdf->Text(179, 222.4, '$'.amountFormat2($cArr->monthly_fee));
                // Extra TV(s)
                $pdf->Text(35, 220.1, cableTVRegArrByCustID($cArr->customer_id)['extra_tvs']);
                $pdf->SetFont('times','',10); 
                // Reg Date 
                $pdf->Text(179, 230.2, pdfDateFormat(cableTVRegisterDateByCustID($cArr->customer_id)));
                $pdf->Rect(12, 245.8, 3.2, 3.5, 'DF');
                //Barcode
                $code = invoiceName($cArr->invoice_number, $cArr->monthly_invoice_date);
                $pdf->Code128(144.6,245.2,$code,55,8);
                $pdf->SetXY(143.4,253.5);
                $pdf->Write(5,$code);
            }
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($request->session()->get('userID')),ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die;
            //Download Monthly Invoices
            $pdf->Output('D', 'CableTV-Monthly-Invoices['.$monthYear.'].pdf');
            return redirect()->route('cabletv-monthly-invoice',['monthlyYear'=>$monthYear]); 
        }
        else
        {
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('cabletv-monthly-invoice');
        }
    }

    /**
    * Generate Invoices.
    *
    * @param  Request  $request
    * @return Response
    */
    public function generate_cabletv_invoice(Request $request)
    {        
        $user = new User;
        $logs = new Logs;
        $ctvhistory = new CableTVHistory;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('cabletv-monthly-invoice');
        }
        
        $id = $request->segment('2');
        $ctvhistoryArr = $ctvhistory->where('id', $id)->get()->first();        
        //Generate Invoice for Monthly Payment CableTV
        if($ctvhistoryArr->entry_type=='MP'){
            
            $pdf = new Fpdi('P','mm', 'A4');
            $pdf->AddPage();
            $pdf->SetFont('times','B',14);
            $fileContent = file_get_contents(asset('assets/cabletv_pdf/monthly_invoice.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            
            //Personal Application
            // Customer Name 
            customerDetailsByID($ctvhistoryArr->customer_id)['type']=='P'? $pdf->Text(11,50.5, customerDetailsByID($ctvhistoryArr->customer_id)['name']): $pdf->Text(11,50.5, customerDetailsByID($ctvhistoryArr->customer_id)['shop_name']);
            $pdf->SetFont('times','',10);
            // Customer Mobile 
            $pdf->Text(35, 61, $ctvhistoryArr->customer_mobile);
            // Customer Address 
            $pdf->Text(35, 71.8, unitAddressByUnitID($ctvhistoryArr->address_id));
            $pdf->Text(35, 76.8, 'CamKo Street, Sangkat Toul Sangke 2, Khan Russey Keo,');
            $pdf->Text(35, 81.8, 'Phnom Penh, Cambodia - 120707');
            // VAT Number
            $pdf->Text(68.5, 87.9, customerDetailsByID($ctvhistoryArr->customer_id)['vat_no']);
            // Invoice Date 
            $pdf->Text(175, 54.4, pdfDateFormat($ctvhistoryArr->monthly_invoice_date));
            // Due Date 
            $pdf->Text(175, 64.5, cableTVDueDate($ctvhistoryArr->monthly_invoice_date));
            // Customer CableTV ID 
            $pdf->Text(175, 74.3, customerNumberByID(customerDetailsByID($ctvhistoryArr->customer_id)['cabletv_id']));
            $pdf->SetFont('times','B',10);            
            // Invoice Number 
            $pdf->Text(158, 87.3, invoiceName($ctvhistoryArr->invoice_number, $ctvhistoryArr->monthly_invoice_date));   
            $pdf->SetFont('times','',10);         
            // Service Date 
            $pdf->Text(95.5, 105.1, fromToCableTVMonthlyInvoiceDate($ctvhistoryArr->monthly_invoice_date)); 
            // Balance 
            $pdf->Text(174, 105.3, '$'.amountFormat2($ctvhistoryArr->total_amount));
            // Discount 
            //$pdf->Text(176, 132, '$'.amountFormat($ctvhistoryArr->discount));
            $pdf->SetFont('times','B',11);
            // Total Amount 
            $pdf->Text(174, 139.3, '$'.amountFormat2($ctvhistoryArr->total_amount));
            $pdf->Rect(12.2, 207, 3.2, 3.5, 'DF');
            // Monthly Fee
            $pdf->Text(179, 222.4, '$'.amountFormat2($ctvhistoryArr->monthly_fee));
            // Extra TV(s)
            $pdf->Text(35, 220.1, cableTVRegArrByCustID($ctvhistoryArr->customer_id)['extra_tvs']);
            $pdf->SetFont('times','',10); 
            // Reg Date 
            $pdf->Text(179, 230.2, pdfDateFormat(cableTVRegArrByCustID($ctvhistoryArr->customer_id)['registration_date']));
            $pdf->Rect(12, 245.8, 3.2, 3.5, 'DF');
            //Barcode
            $code = invoiceName($ctvhistoryArr->invoice_number, $ctvhistoryArr->monthly_invoice_date);
            $pdf->Code128(144.6,245.2,$code,55,8);
            $pdf->SetXY(143.4,253.5);
            $pdf->Write(5,$code);
            $pdfName = $code.'.pdf';
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($ctvhistoryArr->user_id),ENT_COMPAT,'UTF-8'), true);
            //Download Monthly Payment Invoice
            //$pdf->Output(); die;
            $pdf->Output('D', $pdfName);
            
            // Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("CableTV monthly payment invoice downloaded reference id [".$ctvhistoryArr->id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

        }else{

            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Something went wrong, Please try agian!');
            return redirect()->route('cabletv-monthly-invoice');
        }    
    }

    /**
    * Show CableTV Report Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function cabletv_report(Request $request)
    {
        $user = new User;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('cabletv-subscribers');
        }

        $year = '';
        $quarter = '';
        $month = '';
        $ctvReportArr = array();

        if($request->input('type')=='Y'){
            $type = $request->input('type');
            $year = $request->input('year');
            $startDate = yearlyStartEndDate($year.'-01')['startDate'];
            $endDate = yearlyStartEndDate($year.'-12')['endDate'];
        }
        elseif($request->input('type')=='Q'){
            $type = $request->input('type');
            $year = $request->input('year');
            $quarter = $request->input('quarter');
            $startDate = quarterlyStartEndDate($year.'-'.$quarter)['startDate'];
            $endDate = quarterlyStartEndDate($year.'-'.$quarter)['endDate'];
        }
        elseif($request->input('type')=='M'){
            $type = $request->input('type');
            $year = $request->input('year');
            $month = $request->input('month');
            $startDate = monthlyStartEndDate($year.'-'.$month)['startDate'];
            $endDate = monthlyStartEndDate($year.'-'.$month)['endDate'];
        }else{
            $type = 'Y';
            $year = currentYear();  
            $startDate = yearlyStartEndDate($year.'-01')['startDate'];
            $endDate = yearlyStartEndDate($year.'-12')['endDate'];
        }

        $ctvReportArr = [
        'A101'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingName('A101','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingName('A101','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingName('A101','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingName('A101','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingName('A101','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingName('A101','MP',$startDate,$endDate)
                ],
        'A102'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingName('A102','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingName('A102','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingName('A102','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingName('A102','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingName('A102','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingName('A102','MP',$startDate,$endDate)
                ],
        'A103'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingName('A103','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingName('A103','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingName('A103','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingName('A103','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingName('A103','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingName('A103','MP',$startDate,$endDate)            
                ],
        'A104'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingName('A104','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingName('A104','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingName('A104','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingName('A104','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingName('A104','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingName('A104','MP',$startDate,$endDate)
                ],
        'A105'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingName('A105','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingName('A105','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingName('A105','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingName('A105','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingName('A105','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingName('A105','MP',$startDate,$endDate)
                ],
        'A106'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingName('A106','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingName('A106','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingName('A106','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingName('A106','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingName('A106','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingName('A106','MP',$startDate,$endDate)
                ],
        'A107'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingName('A107','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingName('A107','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingName('A107','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingName('A107','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingName('A107','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingName('A107','MP',$startDate,$endDate)
                ],
        'A108'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingName('A108','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingName('A108','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingName('A108','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingName('A108','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingName('A108','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingName('A108','MP',$startDate,$endDate)
                ],
        'Town House'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingType('R1','T','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingType('R1','T','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingType('R1','T','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingType('R1','T','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingType('R1','T','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingType('R1','T','MP',$startDate,$endDate)
                ],
        'Villa (R1)'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingType('R1','V','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingType('R1','V','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingType('R1','V','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingType('R1','V','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingType('R1','V','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingType('R1','V','MP',$startDate,$endDate)
                ],
        'Retail Shops'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingType('R1','S','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingType('R1','S','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingType('R1','S','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingType('R1','S','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingType('R1','S','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingType('R1','S','MP',$startDate,$endDate)
                ],
        'Secret Garden (R2)'=>[
                    'NR'=>totalCableTVCustomerCountByBuildingType('R2','V','NR',$startDate,$endDate),
                    'CL'=>totalCableTVCustomerCountByBuildingType('R2','V','CL',$startDate,$endDate),
                    'TS'=>totalCableTVCustomerCountByBuildingType('R2','V','TS',$startDate,$endDate),
                    'RC'=>totalCableTVCustomerCountByBuildingType('R2','V','RC',$startDate,$endDate),
                    'CH'=>totalCableTVCustomerCountByBuildingType('R2','V','CH',$startDate,$endDate),
                    'MP'=>totalCableTVCustomerCountByBuildingType('R2','V','MP',$startDate,$endDate)
                ]
        ];
        
        return view('users.idc.cabletv.report')
                    ->with(['userArr'=>$userArr, 'reportArr'=>$ctvReportArr, 'type'=>$type, 'year'=>$year,'quarter'=>$quarter,'month'=>$month]);
    }
}
