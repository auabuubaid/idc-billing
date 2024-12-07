<?php

namespace App\Http\Controllers;

use DB;
use Image;
use QrCode;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

// IMPORT & EXPORT
use App\Exports\InternetDMCSummaryExport;
use App\Exports\UnpaidMonthlyInternetExport;
use App\Exports\InternetMonthlyPaymentExport;
use App\Exports\InternetModelHouseSummaryExport;
use App\Exports\InternetMonthlyUpDownStreamExport;
use App\Exports\InternetMonthlyCustomerInfoExport;
use App\Exports\MPTCQuarterlyIncomeStatementExport;
use App\Exports\ActiveInternetSubsWithSuspendExport;
use App\Exports\MPTCQuarterlyServiceDeclarationExport;

use App\Imports\UnpaidMonthlyInternetImport;

// PDF & EXCEL
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PDF_Code128;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\PdfParser\StreamReader;

// CORE NETWORK
use \RouterOS\Query;
use \RouterOS\Config;
use \RouterOS\Client;

// MODELS
use App\Models\User;
use App\Models\Logs;
use App\Models\Customer;
use App\Models\UnitAddress;
use App\Models\ExchangeRate;
use App\Models\InternetService;
use App\Models\InternetHistory;
use App\Models\BuildingLocation;
use App\Models\ServiceAdvancePayment;

class InternetController extends Controller
{
    /**
     * Show Internet Subscribers Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_subscribers(Request $request)
    {   
        $user = new User;
        $unit_address = new UnitAddress;
        $ihistory = new InternetHistory;
        
        $monthYear = '';
        $addressID = '';    
        $currentDate = '';
        $previousDate = '';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');

        //DB::enableQueryLog();
        if($request->input('monthYear') || $request->input('buildingID') || $request->input('addressID')){
            if($request->input('monthYear')){
                $previousDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($request->get('monthYear')));
                $currentDate = internetEndMonthlyPaymentDate($request->get('monthYear'));
                $monthYear = $request->input('monthYear');
            }
            if($request->input('addressID')){
                $addressID = $request->input('addressID');
            }
            
            $subscribersArr = $ihistory
                ->when($monthYear, function($query) use ($monthYear,$previousDate,$currentDate) {
                    return $query->whereDate('start_date_time','>=',$previousDate)->whereDate('start_date_time','<=',$currentDate); })
                ->when($addressID, function($query) use ($addressID) {
                    return $query->where('address_id', '=', $addressID); })
                ->groupBy('customer_id')->orderBy('address_id')->paginate($records)->withQueryString();            
        }else{ 
            $subscribersArr = $ihistory->where('entry_type', 'NR')->orderByDesc('start_date_time')->paginate($records)->withQueryString();
        }
        //$sql= DB::getQueryLog();
        //dd($sql);
        return view('users.idc.internet.index')
                    ->with(['userArr'=>$userArr, 'subscribersArr'=>$subscribersArr, 'monthYear'=>$monthYear, 'addressID'=>$addressID, 'records'=>$records, 'unitsAddressArr'=>$unitsGroupAddressArr]);
    }

    /**
     * Download PPPOE Connection Image.
     *
     * @param  Request  $request
     * @return View
     */
    public function pppoe_connection_image(Request $request)
    {   
        $customer = new Customer;
        $custID =  $request->segment(2);        
        $customerArr = $customer->where('id', $custID)->first();

        if(!is_object($customerArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('internet-subscribers');
        }

        if(file_exists(public_path("pppoe-connection.png")))
        {
            unlink(public_path("pppoe-connection.png"));
        }
        
        $image = Image::make(asset('assets/invoice_images/pppoe-connection-details.png'));
        //Address
        $image->text(buildingLocationAndUnitNumberByUnitID(currentInternetCustomersAddressByID($custID)), 280, 276,function($font) {
            $font->file(public_path('font/source-sans-pro.regular.ttf'));
            $font->size(20);
            $font->color('#fff');
            $font->valign('top');
        }); 
        //Internet ID
        $image->text(customerDetailsByID($custID)['internet_id'], 280, 309,function($font) {
            $font->file(public_path('font/source-sans-pro.regular.ttf'));
            $font->size(20);
            $font->color('#fff');
            $font->valign('top');
        });
        //Password
        $image->text(customerDetailsByID($custID)['internet_password'], 280, 342,function($font) {
            $font->file(public_path('font/source-sans-pro.regular.ttf'));
            $font->size(20);
            $font->color('#fff');
            $font->valign('top');
        });
        //IP Address
        $image->text('xxx.xxx.xxx.xxx', 280, 376,function($font) {
            $font->file(public_path('font/source-sans-pro.regular.ttf'));
            $font->size(20);
            $font->color('#fff');
            $font->valign('top');
        });
        $path= $image->save(public_path('pppoe-connection.png'));
        $filePath = public_path("pppoe-connection.png");         
        return response()->download($filePath);
    }

    /**
     * Show PPPOE Connection View Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function pppoe_connection(Request $request)
    {   
        return view('users.idc.internet.payment_warning');
    }

    /**
     * Change PPPOE Status.
     *
     * @param  Request  $request
     * @return View
     */
    public function change_pppoe_status(Request $request)
    {   
        $customer = new Customer;
        $custID =  $request->segment(2);        
        $customerArr = $customer->where('id', $custID)->first();

        if(!is_object($customerArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('internet-subscribers');
        }

        $name = customerDetailsByID($custID)['internet_id'];
        $plan = ($request->segment(3)==env('EXPIRED_PLAN')) ? planDetailsByPlanID(currentInternetCustomersPlanByID($custID))['plan_name'] : 'Expired-Alert';
        $active = ($request->segment(3)==env('EXPIRED_PLAN')) ? 1 : 0;
        
        $config = (new Config())
        ->set('timeout', 1)
        ->set('host', env('CORE_NETWORK_HOST'))
        ->set('user', env('CORE_NETWORK_USER'))
        ->set('pass', env('CORE_NETWORK_PASSWORD'));
        $client = new Client($config);

        // Remove from PPP Active connection 
        $query = new Query('/ppp/active/print');
        $query->where('name', $name);
        $pppActive = $client->query($query)->read();
        $query = (new Query('/ppp/active/remove'))
                ->equal('.id', @$pppActive[0]['.id']);
        $client->query($query)->read();

        // Update internet profile in PPP Secret connection
        $query = new Query('/ppp/secret/print');
        $query->where('name', $name);
        $subscriberSecret = $client->query($query)->read();
        $query = (new Query('/ppp/secret/set'))
                ->equal('.id', @$subscriberSecret[0]['.id'])
                ->equal('profile', $plan);
        $client->query($query)->read();   

        return redirect()->route('internet-subscribers',['addressID'=>currentInternetCustomersAddressByID($custID)]);
        
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
        $internet = new InternetService;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        $internetServicesArr = $internet->where('status','A')->get();
        $customersArr = DB::select('select `customers`.`id`,`customers`.`type`, `customers`.`name` ,`customers`.`shop_name`,`customers`.`address_id` from `customers` left join `internet_history` on `customers`.`id` = `internet_history`.`customer_id` where `internet_id` is not null and `customers`.`id` not in (select distinct `internet_history`.`customer_id` from `internet_history`) order by `customers`.`id` desc');
        
        return view('users.idc.internet.new_connection')
                    ->with(['customersArr'=>$customersArr,'internetServicesArr'=>$internetServicesArr, 'userArr'=>$userArr]);
    }

    /**
     * Validate & Store New Connection.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_internet_connection(Request $request)
    {  
       $rules = array(
            'installation_date' => 'required|date|date_format:d-m-Y',
            'installation_time' => 'required|date_format:h:i A',
            // Customer Personal Details
            'customer_name' => 'required',
            // Internet Plan
            'internet_plan' => 'required',
            'speed' => 'required',
            'upload_speed' => 'required',
            'monthly_fee' => 'required|numeric|between:0.00,9999.99',
            //Payment
            'installation_fee' => 'required|numeric|between:0.00,9999.99',
            'deposit_fee' => 'required|numeric|between:0.00,9999.99',
            'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.01,9999.99' : '',
            'total_amount' => 'required|numeric|between:0.00,9999.99',
            'payment_mode' => 'required|in:CA,BA,CH,OT',
            'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
            'remark' => !empty($request->input('remark')) ? 'required' : ''
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');            
            return redirect()->route('new-internet-connection')
                        ->withErrors($validator) 
                            ->withInput();            
        } else {            
            $logs = new Logs;
            $ihistory = new InternetHistory;
            
            $ihistory->address_id = $request->input('address_id');
            $ihistory->customer_id = $request->input('customer_name');
            $ihistory->plan_id = $request->input('internet_plan');
            $ihistory->entry_type = 'NR'; //New Registration
            $ihistory->entry_status = 'PAY'; //PAYMENT
            $ihistory->registration_date = dataBaseFormat($request->input('installation_date'));
            $ihistory->start_date_time = dateTimeStampForDataBase($request->input('installation_date').' '.$request->input('installation_time'));
            $ihistory->customer_mobile = (customerDetailsByID($request->input('customer_name'))['type']=='P') ? $request->input('customer_mobile') : $request->input('shop_mobile');
            $ihistory->agreement_period = 'N';
            $ihistory->deposit_fee = amountFormat2($request->input('deposit_fee'));
            $ihistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ihistory->installation_fee = amountFormat2($request->input('installation_fee'));
            $ihistory->others_fee = amountFormat2($request->input('others_fee'));
            $ihistory->total_amount = amountFormat2($request->input('total_amount'));
            $ihistory->payment_mode = $request->input('payment_mode');
            $ihistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ihistory->payment_by = 'CP';
            $ihistory->paid = 'Y';
            $ihistory->paid_date = dataBaseFormat($request->input('installation_date'));
            $ihistory->remark = $request->input('remark');
            $ihistory->user_id = $request->session()->get('userID');            
            $flag = $ihistory->save();    


            // Create core network config object with parameters
            $config = (new Config())
                ->set('timeout', 1)
                ->set('host', env('CORE_NETWORK_HOST'))
                ->set('user', env('CORE_NETWORK_USER'))
                ->set('pass', env('CORE_NETWORK_PASSWORD'));

            // Initiate core network client with config object
            $client = new Client($config);
            
            $internet_id= customerDetailsByID($request->input('customer_name'))['internet_id'];
            $internet_password = customerDetailsByID($request->input('customer_name'))['internet_password'];
            $ip_address = customerDetailsByID($request->input('customer_name'))['ip_address'];
            $internet_plan = planDetailsByPlanID($request->input('internet_plan'))['plan_name'];

            $query = (new Query('/ppp/secret/add'))
                ->equal('name', $internet_id)
                ->equal('password', $internet_password)
                ->equal('service', 'pppoe')
                ->equal('profile', $internet_plan)
                ->equal('remote-address', $ip_address)
                ->equal('comment', buildingLocationAndUnitNumberByUnitID($request->input('address_id')).' new installation on '.dateTimeStampForDataBase($request->input('installation_date').' '.$request->input('installation_time')));
            $response = $client->query($query)->read();
                
            if ($flag) {
                // Entry in logs table
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("New internet connection has done reference id [".$ihistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('internet-subscribers');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('new-internet-connection');
            }
        }
    }

    /**
     * Show Internet Change Plan Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_change_service(Request $request)
    {   
        $user = new User;
        $internet = new InternetService;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();        
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        $internetServicesArr = $internet->where('status','A')->get();
        $customersArr = DB::select('select * from (select max(`id`) as `id`,`customer_id`,`address_id`,`entry_type`,`start_date_time` from `internet_history` group by `customer_id` desc, `id` desc) as `cust_max` where `customer_id` not in (select `customer_id` from `internet_history` where `entry_type` in("TS") union select `customer_id` from `internet_history` where `entry_type`="SS" and `suspension_end_date` is null ) group by `cust_max`.`customer_id`');

        return view('users.idc.internet.internet_change_service')
                    ->with(['customersArr'=>$customersArr,'internetServicesArr'=>$internetServicesArr, 'userArr'=>$userArr]);
    }

    /**
     * Validate & Update Internet Plan.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_internet_plan(Request $request)
    {   
        $rules = array(
         'service_date' => 'required|date|date_format:d-m-Y|after_or_equal:last_entry_date',
         'service_time' => 'required|date_format:h:i A',
         'last_entry_date' => 'required|date|date_format:d-m-Y',
        // Customer Personal Details
         'customer_name' => 'required',
         // New Plan Details
         'internet_plan' => 'required',
         'speed' => 'required|numeric',
         'upload_speed' => 'required|numeric',
         'monthly_fee' => 'required|numeric|between:0.00,9999.99',
         // Payment Details
         'previous_deposit_fee' => 'required|numeric|between:0.00,9999.99',
         'new_deposit_fee' => !empty($request->input('new_deposit_fee')) ? 'required|numeric|between:0.00,9999.99' : '',
         'refund_amount' => !empty($request->input('refund_amount')) ? 'required|numeric|between:0.00,9999.99' : '',
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
            return redirect()->route('internet-change-service')
                        ->withErrors($validator) 
                            ->withInput();         
        } 
        else {   
            $logs = new Logs;
            $ihistory = new InternetHistory;
            
            $ihistory->address_id = $request->input('address_id');
            $ihistory->customer_id = $request->input('customer_name');
            $ihistory->plan_id = $request->input('internet_plan');
            $ihistory->entry_type = 'CP'; //Change Plan
            $ihistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ihistory->customer_mobile = (customerDetailsByID($request->input('customer_name'))['type']=='P') ? $request->input('customer_mobile') : $request->input('shop_mobile');
            if($request->input('new_plan_deposit_fee')>$request->input('previous_deposit_fee')){
                $ihistory->plan_level = 'UPGRADE';
            }else{
                
                $ihistory->refund_amount = amountFormat2($request->input('refund_amount'));
                $ihistory->plan_level = 'DOWNGRADE';
            }
            $ihistory->deposit_fee = amountFormat2($request->input('new_plan_deposit_fee'));
            $ihistory->previous_deposit_fee = amountFormat2($request->input('previous_deposit_fee'));
            $ihistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ihistory->others_fee = amountFormat2($request->input('others_fee'));
            $ihistory->total_amount = amountFormat2($request->input('total_amount'));
            $ihistory->payment_mode = $request->input('payment_mode');
            $ihistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ihistory->payment_by = $request->input('payment_by');
            $ihistory->paid = $request->input('paid');
            $ihistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ihistory->remark = $request->input('remark');
            $ihistory->refrence_id = $request->input('refrence');
            $ihistory->user_id = $request->session()->get('userID');
            $flag = $ihistory->save(); 

            if(in_array($request->input('refrence_type'),['MP','SS'])){
                $flag1 = true;
            }else{
                $flag1 = $ihistory->where('id',$request->input('refrence'))->
                            update([
                                'end_date_time' => previousDateTimeStampForGivenDate($request->input('service_date').' '.$request->input('service_time')) 
                            ]);
            }  

            $name = customerDetailsByID($request->input('customer_name'))['internet_id'];
            $plan = planDetailsByPlanID($request->input('internet_plan'))['plan_name'];
            
            $config = (new Config())
            ->set('timeout', 1)
            ->set('host', env('CORE_NETWORK_HOST'))
            ->set('user', env('CORE_NETWORK_USER'))
            ->set('pass', env('CORE_NETWORK_PASSWORD'));
            $client = new Client($config);
            
            // Get PPP Active detail 
            $query = new Query('/ppp/active/print');
            $query->where('name', $name);
            $pppActive = $client->query($query)->read();
            // Remove from PPP Active connection 
            $query = (new Query('/ppp/active/remove'))
                    ->equal('.id', @$pppActive[0]['.id']);
            $client->query($query)->read();

            // Get PPP Secret detail
            $query = new Query('/ppp/secret/print');
            $query->where('name', $name);
            $pppSecret = $client->query($query)->read();
            // Upgrade/Downgrade internet profile in PPP Secret
            $query = (new Query('/ppp/secret/set'))
                    ->equal('.id', @$pppSecret[0]['.id'])
                    ->equal('profile', $plan);
            $client->query($query)->read(); 
                
            if ($flag) {
                // Entry in logs table
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Internet change plan has done reference id [".$ihistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('internet-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('internet-change-service')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
     * Show Internet Change Location Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_change_location(Request $request)
    {
    	$user = new User;
        $ihistory = new InternetHistory;
        $unit_address = new UnitAddress;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');
        $customersArr = DB::select('select * from (select max(`id`) as `id`,`customer_id`, `address_id`,`entry_type`,`start_date_time` from `internet_history` group by `customer_id` desc, `id` desc) as `cust_max` where `customer_id` not in (select `customer_id` from `internet_history` where `entry_type` in("TS") union select `customer_id` from `internet_history` where `entry_type`="SS" and `suspension_end_date` is null ) group by `cust_max`.`customer_id`');
        
        return view('users.idc.internet.internet_change_location')
                    ->with(['customersArr'=>$customersArr, 'unitsAddressArr'=>$unitsGroupAddressArr, 'userArr'=>$userArr]);
    }

    /**
     * Validate & Update Internet Relocation.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_internet_relocation(Request $request)
    { 
        $rules = array(
        'service_date' => 'required|date|date_format:d-m-Y|after_or_equal:last_entry_date',
        'service_time' => 'required|date_format:h:i A',
        'last_entry_date' => 'required|date|date_format:d-m-Y',
        // Customer Personal Details
        'customer_name' => 'required',
        // Relocation Details
        'relocate_customer_address' => 'required',
        'relocate_customer_mobile' => 'required|alpha_dash|max:12',
        // Payment Details
        'reinstallation_fee' => 'required|numeric|between:0.01,9999.99',
        'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.01,9999.99' : '',
        'total_amount' => 'required|numeric|between:0.01,9999.99',
        'payment_mode' => 'required|in:CA,BA,CH,OT',
        'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
        'payment_by' =>'required|in:CP',
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
            return redirect()->route('internet-change-location')
                        ->withErrors($validator) 
                            ->withInput();            
        } else {
            $logs = new Logs;
            $customer = new Customer;
            $ihistory = new InternetHistory;
            $payment = new ServiceAdvancePayment;

            $ihistory->address_id = $request->input('relocate_customer_address');
            $ihistory->customer_id = $request->input('customer_name');
            $ihistory->plan_id = $request->input('current_plan');
            $ihistory->entry_type = 'CL'; //Change Location
            $ihistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ihistory->deposit_fee = amountFormat2($request->input('deposit_fee'));
            $ihistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ihistory->customer_mobile = rtrim($request->input('relocate_customer_mobile'), "_");
            $ihistory->reinstallation_fee = amountFormat2($request->input('reinstallation_fee'));
            $ihistory->others_fee = amountFormat2($request->input('others_fee'));
            $ihistory->total_amount = amountFormat2($request->input('total_amount'));
            $ihistory->payment_mode = $request->input('payment_mode');
            $ihistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ihistory->payment_by = $request->input('payment_by');
            $ihistory->paid = $request->input('paid');
            $ihistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ihistory->remark = $request->input('remark');
            $ihistory->refrence_id = $request->input('refrence');
            $ihistory->user_id = $request->session()->get('userID');
            $flag = $ihistory->save();

            if(in_array($request->input('refrence_type'),['MP','SS'])){
                $flag1 = true;
            }else{
                $flag1 = $ihistory->where('id',$request->input('refrence'))->
                            update([
                                'end_date_time' => previousDateTimeStampForGivenDate($request->input('service_date').' '.$request->input('service_time')) 
                            ]);
            }

            $mobile_field = customerDetailsByID($request->input('customer_name'))['type']=='P' ? 'mobile' : 'shop_mobile';
            $flag3 = $customer->where('id',$request->input('customer_name'))->
                        update([
                            'address_id' => $request->input('relocate_customer_address'), 
                            $mobile_field => rtrim($request->input('relocate_customer_mobile'), "_"), 
                            'updated_by'=> $request->session()->get('userID')
                        ]);
            
            $advancePaymentArr = $payment->where('customer_id',$request->input('customer_name'))->where('service_type','IN')->whereDate('end_date','>=',dataBaseFormat($request->input('service_date')))->orderByDesc('start_date')->first();
            if(is_object($advancePaymentArr)){
                $flag4 = $payment->where('id',$advancePaymentArr->id)->
                            update([
                                'address_id' => $request->input('relocate_customer_address'), 
                                'user_id' => $request->session()->get('userID')
                            ]);
            }else{
                $flag4 = true;
            }
                
            if ($flag) {
                // Entry in logs table for change internet location
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Internet change location has done reference id [".$ihistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // Entry in logs table for update customer address
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Updated customer address refrence id [".$request->input('customer_name')."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('internet-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('internet-change-location')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
     * Show Internet Suspend Service Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_suspend_service(Request $request)
    {   
        $user = new User;
        $ihistory = new InternetHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        $customersArr = DB::select('select * from (select max(`id`) as `id`,`customer_id`,`address_id`,`entry_type`,`start_date_time` from `internet_history` group by `customer_id` desc, `id` desc) as `cust_max` where `customer_id` not in (select `customer_id` from `internet_history` where `entry_type` in("TS") union select `customer_id` from `internet_history` where `entry_type`="SS" and `suspension_end_date` is null ) group by `cust_max`.`customer_id`');

        return view('users.idc.internet.internet_suspend_service')
                    ->with(['customersArr'=>$customersArr, 'userArr'=>$userArr]);
    }

    /**
     * Validate & Update Internet Suspension.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_internet_suspension(Request $request)
    {        
        $rules = array(
         'service_date' => 'required|date|date_format:d-m-Y|after_or_equal:last_entry_date',
         'service_time' => 'required|date_format:h:i A',
         'last_entry_date' => 'required|date|date_format:d-m-Y',
         // Customer Personal Details
         'customer_name' => 'required',
         // Suspension Dates
         'suspension_start' => 'required|date|date_format:d-m-Y|date_equals:service_date',
         'due_amount' => 'required|numeric|between:0.00,9999.99',
         'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.00,9999.99' : '',
         'total_amount' => 'required|numeric|between:0.00,9999.99',
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
            return redirect()->route('internet-suspend-service')
                        ->withErrors($validator) 
                            ->withInput();            
        }else {   
            $logs = new Logs;
            $ihistory = new InternetHistory;

            $ihistory->address_id = $request->input('address_id');
            $ihistory->customer_id = $request->input('customer_name');
            $ihistory->plan_id = $request->input('plan_id');
            $ihistory->entry_type = 'SS'; //Suspend Service
            $ihistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ihistory->suspension_start_date = dataBaseFormat($request->input('suspension_start'));
            $ihistory->customer_mobile = (customerDetailsByID($request->input('customer_name'))['type']=='P') ? $request->input('customer_mobile') : $request->input('shop_mobile');
            $ihistory->deposit_fee = amountFormat2($request->input('deposit_fee'));
            $ihistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ihistory->due_amount = amountFormat2($request->input('due_amount'));
            $ihistory->others_fee = amountFormat2($request->input('others_fee'));
            $ihistory->total_amount = amountFormat2($request->input('total_amount'));
            $ihistory->payment_mode = $request->input('payment_mode');
            $ihistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ihistory->payment_by = $request->input('payment_by');
            $ihistory->paid = $request->input('paid');
            $ihistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ihistory->remark = $request->input('remark');
            $ihistory->refrence_id = $request->input('refrence');
            $ihistory->user_id = $request->session()->get('userID');
            $flag = $ihistory->save();

            if((in_array($request->input('refrence_type'),['MP']))){
                $flag1 = true;
            }else{
                $flag1 = $ihistory->where('id',$request->input('refrence'))->
                            update([
                                'end_date_time' => dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time')) 
                            ]);
            }

            $name = customerDetailsByID($request->input('customer_name'))['internet_id'];
            $plan = env('EXPIRED_PLAN');
            
            $config = (new Config())
            ->set('timeout', 1)
            ->set('host', env('CORE_NETWORK_HOST'))
            ->set('user', env('CORE_NETWORK_USER'))
            ->set('pass', env('CORE_NETWORK_PASSWORD'));
            $client = new Client($config);
            
            // Get PPP Active detail 
            $query = new Query('/ppp/active/print');
            $query->where('name', $name);
            $pppActive = $client->query($query)->read();
            // Remove from PPP Active connection 
            $query = (new Query('/ppp/active/remove'))
                    ->equal('.id', @$pppActive[0]['.id']);
            $client->query($query)->read();

            // Get PPP Secret detail
            $query = new Query('/ppp/secret/print');
            $query->where('name', $name);
            $pppSecret = $client->query($query)->read();
            // Update internet profile as "Expired-Alert" in PPP Secret
            $query = (new Query('/ppp/secret/set'))
                    ->equal('.id', @$pppSecret[0]['.id'])
                    ->equal('profile', $plan);
            $client->query($query)->read(); 
            // Disable PPP Secret customer
            $query = (new Query('/ppp/secret/disable'))
                    ->equal('.id', @$pppSecret[0]['.id']);
            $client->query($query)->read();

            if ($flag) {
                // Entry in logs table for suspend internet service
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Suspend internet service has done reference id [".$ihistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('internet-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('internet-suspend-service')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
     * Show Internet Reconnection page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_reconnect_service(Request $request)
    {
        $user = new User;
        $ihistory = new InternetHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-history');
        }
        
        $customersArr = $ihistory->select('id','customer_id','address_id','entry_type','start_date_time')->where('entry_type', "SS")->whereNull('suspension_end_date')->get();
        
        return view('users.idc.internet.internet_reconnect_service')
                    ->with(['customersArr'=>$customersArr, 'userArr'=>$userArr]);
    }

    /**
     * Validate & Update Internet Reconnection.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_internet_reconnection(Request $request)
    {
        $rules = array(
         'service_date' => 'required|date|date_format:d-m-Y|after:suspend_end_date',
         'service_time' => 'required|date_format:h:i A',
         // Customer Personal Details
         'customer_name' => 'required',
         // Suspend Details
         'suspend_start_date' => 'required|date|date_format:d-m-Y',
         'suspend_end_date' => 'required|date|date_format:d-m-Y|after:suspend_start_date',
         // Reconnect Details
         'previous_due' => 'required|numeric|between:0.00,9999.99',
         'reconnect_fee' => 'required|numeric|between:0.00,9999.99',
         'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.01,9999.99' : '',
         'total_amount' => 'required|numeric|between:0.00,9999.99',
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
            return redirect()->route('internet-reconnect-service')
                        ->withErrors($validator) 
                            ->withInput();         
        }else {   
            $logs = new Logs;
            $ihistory = new InternetHistory;

            $ihistory->address_id = $request->input('address_id');
            $ihistory->customer_id = $request->input('customer_name');
            $ihistory->plan_id = $request->input('plan_id');
            $ihistory->entry_type = 'RC'; //Reconnect Service
            $ihistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ihistory->customer_mobile = (customerDetailsByID($request->input('customer_name'))['type']=='P') ? $request->input('customer_mobile') : $request->input('shop_mobile');
            $ihistory->deposit_fee = amountFormat2($request->input('deposit_fee'));
            $ihistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ihistory->reconnect_fee = amountFormat2($request->input('reconnect_fee'));
            $ihistory->due_amount = amountFormat2($request->input('previous_due'));
            $ihistory->others_fee = amountFormat2($request->input('others_fee'));
            $ihistory->total_amount = amountFormat2($request->input('total_amount'));
            $ihistory->payment_mode = $request->input('payment_mode');
            $ihistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ihistory->payment_by = $request->input('payment_by');
            $ihistory->paid = $request->input('paid');
            $ihistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ihistory->remark = $request->input('remark');
            $ihistory->refrence_id = $request->input('refrence');
            $ihistory->user_id = $request->session()->get('userID');
            $flag = $ihistory->save();
            $flag1 = $ihistory->where('id',$request->input('refrence'))->
                        update([
                            'end_date_time' => previousDateTimeStampForGivenDate($request->input('service_date').' '.$request->input('service_time')), 
                            'suspension_end_date' => dataBaseFormat($request->input('suspend_end_date')),
                            'suspension_period' => $request->input('suspend_period') 
                        ]);

            $name = customerDetailsByID($request->input('customer_name'))['internet_id'];
            $plan = planDetailsByPlanID($request->input('plan_id'))['plan_name'];
            
            $config = (new Config())
            ->set('timeout', 1)
            ->set('host', env('CORE_NETWORK_HOST'))
            ->set('user', env('CORE_NETWORK_USER'))
            ->set('pass', env('CORE_NETWORK_PASSWORD'));
            $client = new Client($config);
            
            // Get PPP Secret detail
            $query = new Query('/ppp/secret/print');
            $query->where('name', $name);
            $pppSecret = $client->query($query)->read();
            // Update internet profile in PPP Secret
            $query = (new Query('/ppp/secret/set'))
                    ->equal('.id', @$pppSecret[0]['.id'])
                    ->equal('profile', $plan);
            $client->query($query)->read(); 
            // Enable PPP Secret customer
            $query = (new Query('/ppp/secret/enable'))
                    ->equal('.id', @$pppSecret[0]['.id']);
            $client->query($query)->read();
                                
            if ($flag) {
                // Entry in logs table for terminate internet service
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Reconnect internet service has done reference id [".$ihistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('internet-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('internet-reconnect-service')
                            ->withErrors($validator) 
                                    ->withInput();
            }
        }
    }

    /**
     * Show Internet Terminate page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_terminate_service(Request $request)
    {   
        $user = new User;
        $ihistory = new InternetHistory;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        $customersArr = DB::select('select `id`, `customer_id`,`address_id`,`entry_type`,`start_date_time` from `internet_history` where (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `internet_history` group by `customer_id`) and `entry_type` not in ("TS","SS") and `plan_remark` is null');

        return view('users.idc.internet.internet_terminate_service')
                    ->with(['customersArr'=>$customersArr, 'userArr'=>$userArr]);
    }

    /**
     * Validate & Update Internet Termination.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_internet_termination(Request $request)
    {  
        $rules = array(
         'service_date' => 'required|date|date_format:d-m-Y|after_or_equal:last_entry_date',
         'service_time' => 'required|date_format:h:i A',
         // Customer Personal Details
         'customer_name' => 'required',
         // Current Plan Details
         'last_entry_date' => 'required|date|date_format:d-m-Y',
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
            return redirect()->route('internet-terminate-service')
                        ->withErrors($validator) 
                            ->withInput();            
        } else {
            // saving internet service in database
            $logs = new Logs;
            $ihistory = new InternetHistory;

            $ihistory->address_id = $request->input('address_id');
            $ihistory->customer_id = $request->input('customer_name');
            $ihistory->plan_id = $request->input('plan_id');
            $ihistory->entry_type = 'TS'; //Terminate Service
            $ihistory->start_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ihistory->end_date_time = dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time'));
            $ihistory->terminate_date = dataBaseFormat($request->input('service_date'));
            $ihistory->customer_mobile = (customerDetailsByID($request->input('customer_name'))['type']=='P') ? $request->input('customer_mobile') : $request->input('shop_mobile');
            $ihistory->deposit_fee = amountFormat2(0);
            $ihistory->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $ihistory->refund_amount = amountFormat2($request->input('refund_amount'));
            $ihistory->due_amount = amountFormat2($request->input('due_amount'));
            $ihistory->others_fee = amountFormat2($request->input('others_fee'));
            $ihistory->total_amount = amountFormat2($request->input('total_amount'));
            $ihistory->payment_mode = $request->input('payment_mode');
            $ihistory->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $ihistory->payment_by = $request->input('payment_by');
            $ihistory->paid = $request->input('paid');
            $ihistory->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('service_date')) : NULL;
            $ihistory->remark = $request->input('remark');
            $ihistory->refrence_id = $request->input('refrence');
            $ihistory->user_id = $request->session()->get('userID');
            $flag = $ihistory->save();

            if((in_array($request->input('refrence_type'),['MP']))){
                $flag1 = true;
            }else{
                $flag1 = $ihistory->where('id',$request->input('refrence'))->
                            update([
                                'end_date_time'=>dateTimeStampForDataBase($request->input('service_date').' '.$request->input('service_time')) 
                            ]);
            }

            $name = customerDetailsByID($request->input('customer_name'))['internet_id'];
            
            $config = (new Config())
            ->set('timeout', 1)
            ->set('host', env('CORE_NETWORK_HOST'))
            ->set('user', env('CORE_NETWORK_USER'))
            ->set('pass', env('CORE_NETWORK_PASSWORD'));
            $client = new Client($config);
            
            // Get PPP Active detail 
            $query = new Query('/ppp/active/print');
            $query->where('name', $name);
            $pppActive = $client->query($query)->read();
            // Remove from PPP Active connection 
            $query = (new Query('/ppp/active/remove'))
                    ->equal('.id', @$pppActive[0]['.id']);
            $client->query($query)->read();

            // Get PPP Secret detail
            $query = new Query('/ppp/secret/print');
            $query->where('name', $name);
            $pppSecret = $client->query($query)->read();
            // Remove from PPP Secret 
            $query = (new Query('/ppp/secret/remove'))
                    ->equal('.id', @$pppSecret[0]['.id']);
            $client->query($query)->read(); 
            
            if ($flag) {
                // Entry in logs table for terminate internet service
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Terminate internet service has done reference id [".$ihistory->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('internet-history');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('internet-terminate-service')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
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
        $ihistory = new InternetHistory;
        $iservices = new InternetService; 
        $blocation = new BuildingLocation;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();  

        if(!checkPermission($userArr->read)){
            // Permission not granteds
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        $paid = '';
        $year = '';
        $month = '';
        $iplan = '';
        $custID = '';
        $unitType = '';
        $addressID = '';        
        $monthlyYear = '';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $iPlanArr = $iservices->select('id','plan_name')->get();
        $blocationArr = $blocation->where('status','A')->get();
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');
        
        if($request->get('monthlyYear') || $request->get('iplan') || $request->get('unitType') || $request->get('addressID') || $request->get('paid')){
            if($request->get('monthlyYear')){
                $monthlyYear = $request->get('monthlyYear');
                $monthYearArr = explode('-',$monthlyYear);
                $month = $monthYearArr[0];
                $year = $monthYearArr[1];
            }
            if($request->get('iplan')){
                $iplan = $request->get('iplan');
            }
            if($request->get('unitType')){
                $unitType = $request->get('unitType');
            }
            if($request->get('addressID')){
                $addressID = $request->get('addressID');
            }
            if($request->get('custID')){
                $custID = $request->get('custID');
            }           
            if($request->get('paid')){
                $paid = $request->get('paid');
            }
            $customerArr = $ihistory->select('internet_history.id','customer_id','start_date_time','address_id','plan_id','monthly_invoice_date', 'invoice_number','monthly_fee','balance','vat_amount','total_amount','paid','paid_date')
                ->leftJoin('units_address', 'internet_history.address_id', '=' ,'units_address.id')
                ->leftJoin('buildings_location', 'units_address.location_id', '=' ,'buildings_location.id')
                ->where('entry_type', 'MP')
                ->when($monthlyYear, function($query) use ($monthlyYear,$month,$year) {
                    return $query->whereMonth('monthly_invoice_date','=',$month)->whereYear('monthly_invoice_date','=',$year); })
                ->when($iplan, function($query) use ($iplan) {
                    return $query->where('plan_id', '=', $iplan); })
                ->when($unitType, function($query) use ($unitType) {
                    return $query->where('buildings_location.id', '=', $unitType); })
                ->when($addressID, function($query) use ($addressID) {
                    return $query->where('address_id', '=', $addressID); })
                ->when($custID, function($query) use ($custID) {
                    return $query->where('customer_id', '=', $custID); })
                ->when($paid, function($query) use ($paid) {
                    return $query->where('paid', '=', $paid); })
                        ->orderBy('units_address.id')->orderByDesc('invoice_number')->paginate($records)->withQueryString();
        }else{
            $customerArr = $ihistory->select('id','customer_id','start_date_time','address_id','plan_id','monthly_invoice_date', 'invoice_number','monthly_fee','balance','vat_amount','total_amount','paid','paid_date')->where('entry_type','MP')->orderByDesc('invoice_number')->paginate($records)->withQueryString();
        }

        return view('users.idc.internet.monthly_invoice')
                    ->with(['userArr'=>$userArr, 'customerArr'=>$customerArr, 'iPlanArr'=>$iPlanArr, 'blocationArr'=>$blocationArr, 'unitsAddressArr'=>$unitsGroupAddressArr, 'monthlyYear'=>$monthlyYear, 'iplan'=>$iplan, 'unitType'=>$unitType, 'custID'=>$custID, 'addressID'=>$addressID, 'paid'=>$paid, 'records'=>$records]);
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
        $ihistory = new InternetHistory; 

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-subscribers');
        }
        if(!empty($request->get('invoice_month_year'))){

            $previousDate = internetStartMonthlyPaymentDate($request->get('invoice_month_year'));
            $currentDate = internetEndMonthlyPaymentDate($request->get('invoice_month_year'));
            if(currentDate2() != $currentDate){
                // Already Generated
                Session::flash('color','warning');
                Session::flash('icon','fa fa-exclamation-triangle');
                Session::flash('msg','Invoice can not generate before or after'.datePickerFormat($currentDate).'.');
                return redirect()->route('internet-monthly-invoice',['monthlyYear'=>$request->get('invoice_month_year')]);
            }
            $monthlyCustomerArr = DB::select("select distinct `customer_id` from `internet_history` join `customers` on `internet_history`.`customer_id` = `customers`.`id` where date(`start_date_time`) >='".$previousDate."' and date(`start_date_time`) <='".$currentDate."' and `plan_remark` is null and `customer_id` not in (select `customer_id` from `internet_history` where date(`start_date_time`)='".$previousDate."' and `entry_type` in ('TS', 'SS'))  group by `customer_id` order by `customers`.`internet_id` asc");
            //$monthlyCustomerArr = $ihistory->select('customer_id')->distinct()->whereNotIn()->whereDate('start_date_time','>=',$previousDate)->whereDate('start_date_time','<=',$currentDate)->whereNull('plan_remark')->groupBy('customer_id')->orderByDesc('start_date_time')->get();
            $alreadyGeneratedArr = $ihistory->select('customer_id')->distinct()->where('entry_type','MP')->whereDate('monthly_invoice_date',$currentDate)->get();
            if(count($alreadyGeneratedArr)>0){
                // Already Generated
                Session::flash('color','warning');
                Session::flash('icon','fa fa-exclamation-triangle');
                Session::flash('msg','Monthly payment invoice already generated.');
                return redirect()->route('internet-monthly-invoice',['monthlyYear'=>$request->get('invoice_month_year')]);
            }
            
            $finalCustomerIDs = collect($monthlyCustomerArr)->pluck('customer_id');
            $invoiceGeneratedDate = internetEndMonthlyPaymentDate($request->get('invoice_month_year'));
            $customerMonthlyPaymentArr = $ihistory->select('id','address_id','customer_id','plan_id','entry_type','start_date_time','end_date_time','customer_mobile','monthly_fee','deposit_fee','paid','paid_date','payment_by')->whereIn('customer_id',$finalCustomerIDs)->whereDate('start_date_time', '>=', dataBaseFormat($previousDate))->whereDate('start_date_time', '<=', dataBaseFormat($currentDate))->orderByDesc('start_date_time')->get()->groupBy('customer_id')->toArray();
            
            //pa($customerMonthlyPaymentArr);
            if(is_array($customerMonthlyPaymentArr) && count($customerMonthlyPaymentArr)>0){
                $count = 1;
                foreach($customerMonthlyPaymentArr as $ckey=>$cmval){
                    
                    $logs = new Logs;
                    $ihistory = new InternetHistory;

                    $balance = amountFormat2(0);
                    $vat_amount = amountFormat2(0);
                    $total_amount = amountFormat2(0);
                    $no_of_days = 0;
                    $payPreviousAmount = false;

                    foreach($cmval as $cmkey=>$ccmval){
                        //If New Connection or Reconnect on 25 date of any month don't charge any amount have to include in monthly list and amount will be 0.
                        if((count($cmval)==1) && (in_array($ccmval['entry_type'],["NR","RC"])) && (dataBaseFormat($ccmval['start_date_time'])==dataBaseFormat($invoiceGeneratedDate))){
                            
                            $balance = amountFormat2($balance+0);
                            $vat_amount = amountFormat2($vat_amount+0);
                            $total_amount = amountFormat2($total_amount+0);
                            $no_of_days = 0;
                            $payPreviousAmount = false;

                        }
                        //If New Connection or Reconnect after 25 date of any month have to pay on daily basis.
                        elseif((count($cmval)==1) && (in_array($ccmval['entry_type'],["NR","RC"])) && (dataBaseFormat($ccmval['start_date_time'])>=nextDateOFGivenDate($previousDate))){
                            
                            $startDate = dateTimeStampForDataBase($ccmval['start_date_time']); 
                            $endDate = empty($ccmval['end_date_time']) ?  dateTimeStampForDataBase($invoiceGeneratedDate) : dateTimeStampForDataBase($ccmval['end_date_time']);
                            
                            $days_amount = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['balance'];
                            $days_vat = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['vat_amount'];
                            $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['total_amount'];
                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            
                            $balance = amountFormat2($balance+$days_amount);
                            $vat_amount = amountFormat2($vat_amount+$days_vat);
                            $total_amount = amountFormat2($total_amount+$days_total);
                            $payPreviousAmount = false;

                        }
                        //If Suspend or Terminate on 25 date of any month have to pay on daily basis.
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["SS","TS"])) && (dataBaseFormat($ccmval['start_date_time'])==dataBaseFormat($invoiceGeneratedDate))){
                            
                            $startDate = dateTimeStampForDataBase($ccmval['start_date_time']); 
                            $endDate = dateTimeStampForDataBase($invoiceGeneratedDate);
                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            $days_amount = balanceVatTotalAmountByDaysAndMonthlyFee($no_of_days, $ccmval['monthly_fee'])['balance'];
                            $days_vat = balanceVatTotalAmountByDaysAndMonthlyFee($no_of_days, $ccmval['monthly_fee'])['vat_amount'];
                            $days_total = balanceVatTotalAmountByDaysAndMonthlyFee($no_of_days, $ccmval['monthly_fee'])['total_amount'];

                            $balance = amountFormat2($balance+$days_amount);
                            $vat_amount = amountFormat2($vat_amount+$days_vat);
                            $total_amount = amountFormat2($total_amount+$days_total);
                            $payPreviousAmount = true;

                        }
                        //If Suspend or Terminate on 26 date of any month charge 0 till Reconnect or Register not happend.
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["SS","TS"])) && (dayOFGivenDate(dataBaseFormat($ccmval['start_date_time']))=='26') && (dataBaseFormat($ccmval['start_date_time'])<dataBaseFormat($invoiceGeneratedDate))) { 
                                    
                            $startDate = dateTimeStampForDataBase($ccmval['start_date_time']); 
                            $endDate =  dateTimeStampForDataBase($invoiceGeneratedDate);
                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            $balance = amountFormat2($balance+0);
                            $vat_amount = amountFormat2($vat_amount+0);
                            $total_amount = amountFormat2($total_amount+0);
                            $payPreviousAmount = false;

                        }
                        //If Suspend after 25 date of any month charge 0 till Reconnect not happend.
                        elseif ((count($cmval) > 1) && (in_array($ccmval['entry_type'], ["SS"])) && (dataBaseFormat($ccmval['start_date_time']) > dataBaseFormat($previousDate)) && (dataBaseFormat($ccmval['start_date_time']) < dataBaseFormat($invoiceGeneratedDate))) {

                            $startDate = nextDateTimeStampForGivenDate($ccmval['start_date_time']);
                            $endDate =  empty($ccmval['end_date_time']) ?  dateTimeStampForDataBase($invoiceGeneratedDate) : dateTimeStampForDataBase($ccmval['end_date_time']);
                            $no_of_days = ($no_of_days + daysBetweenTwoDatesForInternet($startDate, $endDate));
                            $balance = amountFormat2($balance + 0);
                            $vat_amount = amountFormat2($vat_amount + 0);
                            $total_amount = amountFormat2($total_amount + 0);
                            $payPreviousAmount = true;
                        }
                        //If Terminate after 25 date of any month charge 0 till Register not happend.
                        elseif ((count($cmval) > 1) && (in_array($ccmval['entry_type'], ["TS"])) && (dataBaseFormat($ccmval['start_date_time']) > dataBaseFormat($previousDate)) && (dataBaseFormat($ccmval['start_date_time']) < dataBaseFormat($invoiceGeneratedDate))) {

                            $startDate = nextDateTimeStampForGivenDate($ccmval['start_date_time']);
                            $endDate =  dateTimeStampForDataBase($invoiceGeneratedDate);
                            $no_of_days = ($no_of_days + daysBetweenTwoDatesForInternet($startDate, $endDate));
                            $balance = amountFormat2($balance + 0);
                            $vat_amount = amountFormat2($vat_amount + 0);
                            $total_amount = amountFormat2($total_amount + 0);
                            $payPreviousAmount = true;
                        }
                        //If reconnect then have to pay on daily basis.
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["RC"]))) { 
                                    
                            $startDate = dateTimeStampForDataBase($ccmval['start_date_time']); 
                            $endDate = empty($ccmval['end_date_time']) ?  dateTimeStampForDataBase($invoiceGeneratedDate) : dateTimeStampForDataBase($ccmval['end_date_time']);

                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            $days_amount = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['balance'];
                            $days_vat = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['vat_amount'];
                            $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['total_amount'];

                            $balance = amountFormat2($balance+$days_amount);
                            $vat_amount = amountFormat2($vat_amount+$days_vat);
                            $total_amount = amountFormat2($total_amount+$days_total);
                            $payPreviousAmount = false;

                        }
                        //If change plan or location then have to pay on daily basis for new plan or location.
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["CP","CL"]))) { 
                                    
                            $startDate = dateTimeStampForDataBase($ccmval['start_date_time']); 
                            $endDate = empty($ccmval['end_date_time']) ?  dateTimeStampForDataBase($invoiceGeneratedDate) : dateTimeStampForDataBase($ccmval['end_date_time']);

                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            $days_amount = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['balance'];
                            $days_vat = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['vat_amount'];
                            $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['total_amount'];

                            $balance = amountFormat2($balance+$days_amount);
                            $vat_amount = amountFormat2($vat_amount+$days_vat);
                            $total_amount = amountFormat2($total_amount+$days_total);
                            $payPreviousAmount = (dayOFGivenDate(dataBaseFormat($startDate))=='26') ? false: true;

                        }
                        //If new registration on 25 of last month and have only last month monthly invoice.
                        elseif((count($cmval)==2) && (in_array($ccmval['entry_type'],["NR"])) && (dataBaseFormat($ccmval['start_date_time'])==dataBaseFormat($previousDate))) { 
                                    
                            $startDate = nextDateTimeStampForGivenDate($ccmval['start_date_time']); 
                            $endDate =  dateTimeStampForDataBase($invoiceGeneratedDate);

                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            $days_amount = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['balance'];
                            $days_vat = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['vat_amount'];
                            $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['total_amount'];

                            $balance = amountFormat2($balance+$days_amount);
                            $vat_amount = amountFormat2($vat_amount+$days_vat);
                            $total_amount = amountFormat2($total_amount+$days_total);
                            $payPreviousAmount = false;

                        }
                        //If new registration then have to pay on daily basis from after 26.
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["NR"])) && (dataBaseFormat($ccmval['start_date_time'])>dataBaseFormat($previousDate)) && (dataBaseFormat($ccmval['start_date_time'])<dataBaseFormat($invoiceGeneratedDate))) { 
                                    
                            $startDate = dateTimeStampForDataBase($ccmval['start_date_time']); 
                            $endDate = empty($ccmval['end_date_time']) ?  dateTimeStampForDataBase($invoiceGeneratedDate) : dateTimeStampForDataBase($ccmval['end_date_time']);

                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            $days_amount = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['balance'];
                            $days_vat = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['vat_amount'];
                            $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $ccmval['monthly_fee'])['total_amount'];

                            $balance = amountFormat2($balance+$days_amount);
                            $vat_amount = amountFormat2($vat_amount+$days_vat);
                            $total_amount = amountFormat2($total_amount+$days_total);
                            $payPreviousAmount = false;

                        }
                        //Have to pay using last month plan till 30 days not complete.
                        elseif((count($cmval)>1) && ($ccmval['entry_type']=="MP") && ($payPreviousAmount == true)) { 
                            $startDate = nextDateTimeStampForGivenDate($ccmval['start_date_time']); 
                            $endDate =  nextNDaysDateTimeStampForGivenDate($ccmval['start_date_time'],(30-$no_of_days));
                            
                            $days_amount = balanceVatTotalAmountByDaysAndMonthlyFee((30-$no_of_days), $ccmval['monthly_fee'])['balance'];
                            $days_vat = balanceVatTotalAmountByDaysAndMonthlyFee((30-$no_of_days), $ccmval['monthly_fee'])['vat_amount'];
                            $days_total = balanceVatTotalAmountByDaysAndMonthlyFee((30-$no_of_days), $ccmval['monthly_fee'])['total_amount'];
                            $no_of_days = ($no_of_days+(30-$no_of_days));

                            $balance = amountFormat2($balance+$days_amount);
                            $vat_amount = amountFormat2($vat_amount+$days_vat);
                            $total_amount = amountFormat2($total_amount+$days_total);
                            $payPreviousAmount = false;
                        }
                        //If not done anything like Change Plan, Change Location, Suspend, Reconnect, Terminate then have to pay using previous month plan.
                        elseif((count($cmval)==1) && ($ccmval['entry_type']=="MP")) { 
                                    
                            $startDate = nextDateTimeStampForGivenDate($ccmval['start_date_time']); 
                            $endDate = dateTimeStampForDataBase($invoiceGeneratedDate);

                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            $days_amount = balanceVatTotalAmountByDays($startDate, $endDate, $cmval[0]['monthly_fee'])['balance'];
                            $days_vat = balanceVatTotalAmountByDays($startDate, $endDate, $cmval[0]['monthly_fee'])['vat_amount'];
                            $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cmval[0]['monthly_fee'])['total_amount'];

                            $balance = amountFormat2($balance+$days_amount);
                            $vat_amount = amountFormat2($vat_amount+$days_vat);
                            $total_amount = amountFormat2($total_amount+$days_total);
                            $payPreviousAmount = false;
                        }
                    }
                                        
                    //pa('Name=>'.customerNameByID($ckey)." address=>".buildingLocationAndUnitNumberByUnitID($cmval[0]['address_id'])." balance=>".$balance." vat=>".$vat_amount.' total=>'.$total_amount." plan_id=>".$cmval[0]['plan_id']." entry_type=>".$cmval[0]['entry_type']." month_year=>".$request->get('invoice_month_year'));
                    //pa(($ckey).'-'.$no_of_days.' days');
                    //pa(buildingLocationAndUnitNumberByUnitID($cmval[0]['address_id']));
                    //pa($total_amount);

                    $payment = new ServiceAdvancePayment;
                    $advancePaidArr = $payment->select('start_date','end_date','paid_date','remark')->where('address_id', $cmval[0]['address_id'])->where('customer_id', $ckey)->where('service_type', 'IN')->orderByDesc('id')->first();
                    
                    if(is_object($advancePaidArr) && !is_null($advancePaidArr)){
                        if(dataBaseFormat($invoiceGeneratedDate) <= dataBaseFormat($advancePaidArr->end_date)){
                            $paid_status = "Y";
                            $paid_date = dataBaseFormat($advancePaidArr->paid_date);
                            $payment_description = "Paid at IDC ABA virtual account";
                            $remark = $advancePaidArr->remark;
                        }else{
                            $paid_status = "N";
                            $paid_date = NULL;
                            $payment_description = NULL;
                            $remark = NULL;
                        }
                    }
                    elseif((in_array($cmval[0]['entry_type'],['SS','TS'])) && ($cmval[0]['paid']=='Y') && ($cmval[0]['payment_by']=='CP'))
                    {
                        $paid_status = "Y"; //Paid Status Yes
                        $paid_date = dataBaseFormat($cmval[0]['paid_date']); //Paid date
                        $payment_description = "Paid at IDC ABA virtual account";
                        $remark = "Paid at IDC on ".datePickerFormat3($paid_date); //Paid remark
                    }   
                    elseif((in_array($cmval[0]['entry_type'],['SS','TS'])) && ($cmval[0]['payment_by']=='CR'))
                    {
                        $paid_status = "Y"; //Paid Status Yes
                        $paid_date = dataBaseFormat($cmval[0]['paid_date']); //Paid date
                        $payment_description = "Paid at IDC ABA virtual account";
                        $remark = "Paid at IDC on ".datePickerFormat3($paid_date); //Paid remark
                    }                 
                    else{
                        $paid_status = "N";
                        $paid_date = NULL;
                        $payment_description = NULL;
                        $remark = NULL;
                    }

                    $ihistory->address_id = $cmval[0]['address_id'];
                    $ihistory->customer_id = $ckey;
                    $ihistory->plan_id = $cmval[0]['plan_id'];
                    $ihistory->entry_type = 'MP'; //Monthly Payment
                    $ihistory->start_date_time = dateTimeStampForDataBase($invoiceGeneratedDate.' '.currentHourMinute());
                    $ihistory->end_date_time = dateTimeStampForDataBase($invoiceGeneratedDate.' '.currentHourMinute());
                    $plan_remark = (in_array($cmval[0]['entry_type'],["SS","TS"])) ? "Dont include in next monthly invoice" : NULL;
                    $ihistory->plan_remark = $plan_remark;
                    $ihistory->monthly_invoice_date = $invoiceGeneratedDate;
                    $ihistory->invoice_number =monthlyInvoiceNumber($invoiceGeneratedDate,$count);
                    $ihistory->customer_mobile= $cmval[0]['customer_mobile'];
                    $ihistory->deposit_fee =  amountFormat2($cmval[0]['deposit_fee']);
                    $ihistory->monthly_fee =  amountFormat2($cmval[0]['monthly_fee']);
                    $ihistory->balance = $balance;
                    $ihistory->vat_amount = $vat_amount;
                    $ihistory->total_amount = $total_amount;
                    $ihistory->payment_mode = 'BA'; //Bank
                    $ihistory->payment_description = $payment_description;
                    $ihistory->payment_by = 'CP'; //Customer Pay
                    $ihistory->paid = $paid_status;
                    $ihistory->paid_date = $paid_date;
                    $ihistory->remark = $remark;
                    $ihistory->refrence_id = $cmval[0]['id'];
                    $ihistory->user_id = $request->session()->get('userID');
                    $flag = $ihistory->save(); 

                    if(in_array($cmval[0]['entry_type'],['MP','SS','TS'])){
                        $flag1 = true;
                    }else{
                        $flag1 = $ihistory->where('id',$cmval[0]['id'])->
                                    update([
                                        'end_date_time' => dateTimeStampForDataBase($invoiceGeneratedDate.' '.currentHourMinute()) 
                                    ]);
                    }
                    
                    if ($flag) {                    
                        // Entry in logs table
                        $logs->user_id = $request->session()->get('userID');
                        $logs->logs_area = logsArea("Internet monthly payment gereration has done reference id [".$ihistory->id."].");
                        $logs->ip_address = $request->ip();
                        $logs->save();

                    } else {
                        // Entry in logs table
                        $logs->user_id = $request->session()->get('userID');
                        $logs->logs_area = logsArea("Internet monthly payment gereration has problem reference id [".$ihistory->id."].");
                        $logs->ip_address = $request->ip();
                        $logs->save();
                    }
                    $count++;
                } //die;
            }

            // Monthly Payment Generated Successfully
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Internet monthly payment invoices has been generated successfully.');
            return redirect()->route('export-dmc-invoice',['invoice_month_year'=>$request->get('invoice_month_year')]); 
        }else{
            // Month & Year Not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month & year!');
            return redirect()->route('internet-monthly-invoice')
                        ->with(['monthlyYear'=>$request->get('invoice_month_year')]);
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
        $ihistory = new InternetHistory;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('internet-monthly-invoice');
        }

        $id = $request->segment('2');
        $ihistoryArr = $ihistory->where('id', $id)->first();

        if(!is_object($ihistoryArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('internet-monthly-invoice');
        }

        return view('users.idc.internet.edit_monthly_invoice')
                    ->with(['userArr'=>$userArr, 'ihistoryArr'=>$ihistoryArr]);
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
            'balance' =>'required|numeric|between:0.00,9999.99',
            'vat' =>'required|numeric|between:0.00,9999.99',
            'others_fee' => !empty($request->input('others_fee')) ? 'required|numeric|between:0.00,9999.99' : '',
            'total_amount' => 'required|numeric|between:0.00,99999.99',
            'paid' =>'required|in:Y,N',
            'payment_mode' => 'required|in:CA,BA,CH,OT',
            'payment_description' => in_array($request->input('payment_mode'),['BA','CH','OT'])?'required|string' : '',
            'remark' => !empty($request->input('remark')) ? 'required' : ''
        );        
        $validator = Validator::make($request->all(), $rules);
        $id = $request->input('refrenceID');

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('edit-monthly-invoice',[$id])
                       ->withErrors($validator) 
                            ->withInput();            
        } 
        else
        {    
            // saving internet service in database
            $logs = new Logs;
            $ihistory = new InternetHistory;
            
            $flag = $ihistory->where('id',$id)->
                        update([
                            'balance' => amountFormat2($request->input('balance')),
                            'vat_amount' => amountFormat2($request->input('vat')),
                            'others_fee' => amountFormat2($request->input('others_fee')),
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
                $logs->logs_area = logsArea("Internet monthly payment has been updated reference id [".$id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
                return redirect()->route('internet-monthly-invoice');
            } else {
                // database updation was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not updated, please try again!');
                return redirect()->route('edit-monthly-invoice',[$id]);
            }                    
        }
    }

    /**
     * Show Internet History Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_history(Request $request)
    {        
        $user = new User;
        $unit_address = new UnitAddress;
        $ihistory = new InternetHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('internet-subscribers');
        }        

        $year = ''; 
        $month = '';                      
        $status = '';
        $addressID = '';
        $monthYear = '';
        $entry_type = '';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS'); 
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');

        if($request->input('monthlyYear') || $request->input('entry_type') || $request->input('addressID') || $request->input('status')){
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
            if($request->input('status')){
                $status = $request->input('status');
            }

            $ihistoryArr = $ihistory->whereIn('entry_type', ['NR','CP','CL','SS','RC','TS'])
                            ->when($monthYear, function($query) use ($monthYear,$month,$year) {
                                return $query->whereMonth('start_date_time','=',$month)->whereYear('start_date_time','=',$year); })
                            ->when($entry_type, function($query) use ($entry_type) {
                                return $query->where('entry_type', '=', $entry_type); })
                            ->when($addressID, function($query) use ($addressID) {
                                return $query->where('address_id', '=', $addressID); })
                            ->when($status, function($query) use ($status) {
                                return $query->where('paid', '=', $status); })
                                    ->orderByDesc('start_date_time')->paginate($records)->withQueryString();
        }
        else
        { 
            $ihistoryArr = $ihistory->whereIn('entry_type',["NR","CP","CL","SS","RC","TS"])->orderByDesc('start_date_time')->paginate($records)->withQueryString();
        }      

        return view('users.idc.internet.history')
                    ->with(['userArr'=>$userArr, 'ihistoryArr'=>$ihistoryArr,'monthYear'=>$monthYear,'entry_type'=>$entry_type,'addressID'=>$addressID,'status'=>$status, 'records'=>$records,'unitsAddressArr'=>$unitsGroupAddressArr]);
    }

    /**
     * Generate Invoices.
     *
     * @param  Request  $request
     * @return Response
     */
    public function generate_internet_invoice(Request $request)
    {
        $user = new User;
        $logs = new Logs;
        $ihistory = new InternetHistory;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-monthly-invoice');
        }

        $id = $request->segment('2');
        $ihistoryArr = $ihistory->where('id', $id)->get()->first();

        if(!is_object($ihistoryArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('internet-monthly-invoice');
        }
        // Create Invoice for New Connection
        if($ihistoryArr->entry_type=='NR'){
       
            $pdf = new Fpdi('P','mm', 'A4');
            $pdf->AddPage();
            $pdf->SetFont('times','',11);
            $fileContent = file_get_contents(asset('assets/internet_pdf/internet_new.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            // Customer Type 
            customerDetailsByID($ihistoryArr->customer_id)['type']=='P' ? $pdf->Rect(81.5, 47.3, 3.2, 3, 'DF'): $pdf->Rect(165.3, 47.3, 3.2, 3, 'DF');
            
            //Personal Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='P'){
                // Customer Name 
                $pdf->Text(76,60.5, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Customer Address 
                $pdf->Text(70, 76.5, unitAddressByUnitID($ihistoryArr->address_id));
                // Customer Mobile 
                $pdf->Text(155, 85, $ihistoryArr->customer_mobile);
                // Customer Email 
                $pdf->Text(70, 84.5, customerDetailsByID($ihistoryArr->customer_id)['email']);
            }
            //Shop Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='S'){
                // Shop Name 
                $pdf->Text(75,93, customerDetailsByID($ihistoryArr->customer_id)['shop_name']);
                // Authorized Person 
                $pdf->Text(90,119, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Shop Address 
                $pdf->Text(65, 110, unitAddressByUnitID($ihistoryArr->address_id));
                // Shop Mobile 
                $pdf->Text(75, 101.8, $ihistoryArr->customer_mobile);
                // Shop Email 
                $pdf->Text(65, 127, customerDetailsByID($ihistoryArr->customer_id)['shop_email']);
                // VAT Number
                $pdf->Text(155, 93, customerDetailsByID($ihistoryArr->customer_id)['vat_no']);
            }
            // Same as person/Company information 
            $pdf->SetDrawColor(19, 176, 93);
            $pdf->SetFillColor(19, 176, 93);
            $pdf->Rect(46.8, 132.3, 3, 3, 'DF');
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='S'){
                // Shop Name 
                $pdf->Text(60, 143.5, customerDetailsByID($ihistoryArr->customer_id)['shop_name']);
            }else{
                // Customer Name 
                $pdf->Text(60, 143.5, customerDetailsByID($ihistoryArr->customer_id)['name']);
            }
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='S'){
                // Shop Mobile 
                $pdf->Text(78, 152, $ihistoryArr->customer_mobile);
            }else{
                // Customer Mobile 
                $pdf->Text(78, 152, $ihistoryArr->customer_mobile);
            }
            // Customer Address 
            $pdf->Text(65, 160, unitAddressByUnitID($ihistoryArr->address_id));
            // Plan Name 
            $pdf->Text(71, 168.5, planDetailsByPlanID($ihistoryArr->plan_id)['plan_name']);
            // Plan Unlimited 
            $pdf->Text(123.5, 168.3, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));
            // Plan Speed 
            $pdf->Text(141.9, 168.3, planDetailsByPlanID($ihistoryArr->plan_id)['speed'].' '.planDetailsByPlanID($ihistoryArr->plan_id)['speed_unit']);
            // Plan Monthly Fee 
            $pdf->Text(183, 168, '$'.amountFormat2($ihistoryArr->monthly_fee));
            // Plan Data Usage 
            $pdf->Text(79.5, 176.5, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));
            // Plan Deposit Fee 
            $pdf->Text(63.9, 226.8, amountFormat2($ihistoryArr->deposit_fee));
            // Plan Installation Fee 
            $pdf->Text(63.9, 234.3, amountFormat2($ihistoryArr->installation_fee));
            // Plan Other Fee 
            $pdf->Text(128, 227.2, amountFormat2($ihistoryArr->others_fee));
            // Total Fee 
            $pdf->Text(128, 234.2, amountFormat2($ihistoryArr->total_amount));
            // Date 
            $pdf->Text(176, 214.7, pdfDateFormat($ihistoryArr->registration_date));
            // Officer 
            $pdf->Text(176, 221.9, html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'));
            // Payment Mode 
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(0, 0, 0);
            if($ihistoryArr->payment_mode=='BA') { $pdf->Rect(179, 227, 3, 3, 'DF'); }
            else if($ihistoryArr->payment_mode=='CH') { $pdf->Rect(149.3, 232.5, 3, 3, 'DF'); }
            else if($ihistoryArr->payment_mode=='CA') { $pdf->Rect(149.3, 227, 3, 3, 'DF'); } 
            else { $pdf->Rect(179, 232.3, 3, 3, 'DF'); }
            // Footer Customer Name 
            $pdf->Text(20, 256, customerDetailsByID($ihistoryArr->customer_id)['name']);
            // Footer Customer Date 
            $pdf->Text(75, 256, pdfDateFormat($ihistoryArr->registration_date));

            // Footer Offiser Name 
            $pdf->Text(114, 256.5, html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'));
            // Footer Offiser Date 
            $pdf->Text(169, 256.5, pdfDateFormat($ihistoryArr->registration_date));
            // Footer Remark
            $pdf->Text(35, 272, Str::substr($ihistoryArr->remark, 0, 105)."\r\n");
            $pdf->Text(35, 276, Str::substr($ihistoryArr->remark, 105, 100)."\r\n");
            $pdf->Text(35, 280, Str::substr($ihistoryArr->remark, 205, 100)."\r\n");
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'), true);
            //Download New Connection Invoice
            $pdf->Output('D', 'New Connection - '.buildingLocationAndUnitNumberByUnitID($ihistoryArr->address_id).'.pdf');
            
            // Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("New internet connection invoice generated reference id [".$ihistoryArr->id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

        }
        // Generate Invoice for Change Internet Plan
        elseif($ihistoryArr->entry_type=='CP'){

            $pdf = new Fpdi('P','mm', 'A4');
            $pdf->AddPage();
            $pdf->SetFont('times','',11);
            $fileContent = file_get_contents(asset('assets/internet_pdf/internet_chg.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            
            // Service Type 
            $pdf->Rect(15, 29.9, 3.2, 3, 'DF');
            
            // Customer Type 
            customerDetailsByID($ihistoryArr->customer_id)['type']=='P' ? $pdf->Rect(81.5, 43.8, 3.2, 3, 'DF'): $pdf->Rect(165.5, 43.8, 3.2, 3, 'DF');
            //Personal Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='P'){
                // Customer Name 
                $pdf->Text(76,55.8, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Customer Address 
                $pdf->Text(70, 71.5, unitAddressByUnitID($ihistoryArr->address_id));
                // Customer Mobile 
                $pdf->Text(155, 79.3, $ihistoryArr->customer_mobile);
                // Customer Email 
                $pdf->Text(70, 79.2, customerDetailsByID($ihistoryArr->customer_id)['email']);
            }
            //Shop Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='S'){
                // Shop Name 
                $pdf->Text(75,87.5, customerDetailsByID($ihistoryArr->customer_id)['shop_name']);
                // Authorized Person 
                $pdf->Text(90,109.5, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Shop Address 
                $pdf->Text(65, 102.3, unitAddressByUnitID($ihistoryArr->address_id));
                // Shop Mobile 
                $pdf->Text(75, 95.3, $ihistoryArr->customer_mobile);
                // Shop Email 
                $pdf->Text(65, 116.6, customerDetailsByID($ihistoryArr->customer_id)['shop_email']);
                // VAT Number
                $pdf->Text(155, 87, customerDetailsByID($ihistoryArr->customer_id)['vat_no']);
            }
            //Current Plan Details
            $currentPlanArr= $ihistoryArr->select('plan_id','monthly_fee')->where('id', $ihistoryArr->refrence_id)->get()->first();
            // Plan Name 
            $pdf->Text(71, 165.2, planDetailsByPlanID($currentPlanArr->plan_id)['plan_name']);
            // Plan Unlimited 
            $pdf->Text(125, 165.2, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($currentPlanArr->plan_id)['data_usage'])." / ",ENT_COMPAT,'UTF-8'));
            // Plan Speed 
            $pdf->Text(143.6, 165.2, planDetailsByPlanID($currentPlanArr->plan_id)['speed'].' '.planDetailsByPlanID($currentPlanArr->plan_id)['speed_unit']);
            // Plan Monthly Fee 
            $pdf->Text(185, 165.2, '$'.amountFormat2($currentPlanArr->monthly_fee));
            // Plan Data Usage 
            $pdf->Text(81.5, 172.8, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($currentPlanArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));

            //Changed Plan Details
            // Plan Name 
            $pdf->Text(71, 186.5, planDetailsByPlanID($ihistoryArr->plan_id)['plan_name']);
            // Plan Unlimited 
            $pdf->Text(125, 186.3, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));
            // Plan Speed 
            $pdf->Text(143, 186.3, planDetailsByPlanID($ihistoryArr->plan_id)['speed'].' '.planDetailsByPlanID($ihistoryArr->plan_id)['speed_unit']);
            // Plan Monthly Fee 
            $pdf->Text(185, 186.5, '$'.amountFormat2($ihistoryArr->monthly_fee));
            // Plan Data Usage 
            $pdf->Text(81.5, 193.8, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));
            
            // Plan Deposit Fee 
            $pdf->Text(65, 222.8, amountFormat2($ihistoryArr->deposit_fee));
            // Previous Deposit Fee 
            $pdf->Text(149, 222.8, amountFormat2($ihistoryArr->previous_deposit_fee));
            // Plan Other Fee 
            $pdf->Text(150.5, 229.2, amountFormat2($ihistoryArr->others_fee));
            // Total Fee 
            $pdf->Text(45, 236.2, amountFormat2($ihistoryArr->total_amount));
            // Payment By 
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(0, 0, 0);
            // Payment by Customer Pay
            if($ihistoryArr->payment_by=='CP') { $pdf->Rect(115, 233.2, 3.2, 3, 'DF'); }
            // Payment by Company Refund
            else { $pdf->Rect(164, 233.2, 3.2, 3, 'DF'); }

            // Footer Customer Name 
            $pdf->Text(22, 257.6, customerDetailsByID($ihistoryArr->customer_id)['name']);
            // Footer Customer Date 
            $pdf->Text(75, 257.6, pdfDateFormat($ihistoryArr->start_date_time));

            // Footer Offiser Name 
            $pdf->Text(116, 257.6, html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'));
            // Footer Offiser Date 
            $pdf->Text(169, 257.6, pdfDateFormat($ihistoryArr->start_date_time));
            // Footer Remark
            $pdf->Text(35, 271.5, Str::substr($ihistoryArr->remark, 0, 105)."\r\n");
            $pdf->Text(35, 275.6, Str::substr($ihistoryArr->remark, 105, 100)."\r\n");
            $pdf->Text(35, 279.6, Str::substr($ihistoryArr->remark, 205, 100)."\r\n");
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die;
            //Download New Connection Invoice
            $pdf->Output('D', 'Change-Plan - '.buildingLocationAndUnitNumberByUnitID($ihistoryArr->address_id).'.pdf');
            
            //Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Change internet plan invoice generated reference id [".$ihistoryArr->id."].");
            $logs->ip_address = $request->ip();
            $logs->save();
        }
        // Generate Invoice for Change Internet Location
        elseif($ihistoryArr->entry_type=='CL'){

            $pdf = new Fpdi('P','mm', 'A4');
            $pdf->AddPage();
            $pdf->SetFont('times','',11);
            $fileContent = file_get_contents(asset('assets/internet_pdf/internet_chg.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            
            // Service Type 
            $pdf->Rect(71.8, 29.9, 3.2, 3.2, 'DF');
            
            // Customer Type 
            customerDetailsByID($ihistoryArr->customer_id)['type']=='P' ? $pdf->Rect(81.5, 43.8, 3.2, 3, 'DF'): $pdf->Rect(165.5, 43.8, 3.2, 3, 'DF');
            //Personal Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='P'){
                // Customer Name 
                $pdf->Text(76,55.8, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Customer Address 
                $pdf->Text(70, 71.5, unitAddressByUnitID($ihistoryArr->address_id));
                // Customer Mobile 
                $pdf->Text(155, 79.3, $ihistoryArr->customer_mobile);
                // Customer Email 
                $pdf->Text(70, 79.2, customerDetailsByID($ihistoryArr->customer_id)['email']);
            }
            //Shop Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='S'){
                // Shop Name 
                $pdf->Text(75,87.5, customerDetailsByID($ihistoryArr->customer_id)['shop_name']);
                // Authorized Person 
                $pdf->Text(90,109.5, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Shop Address 
                $pdf->Text(65, 102.3, unitAddressByUnitID($ihistoryArr->address_id));
                // Shop Mobile 
                $pdf->Text(75, 95.3, $ihistoryArr->customer_mobile);
                // Shop Email 
                $pdf->Text(65, 116.6, customerDetailsByID($ihistoryArr->customer_id)['shop_email']);
                // VAT Number
                $pdf->Text(155, 87, customerDetailsByID($ihistoryArr->customer_id)['vat_no']);
            }
            //Previous Location Details
            $pdf->Rect(45.4, 126.2, 3.2, 3, 'DF');
            $currentPlanArr= $ihistoryArr->select('address_id','customer_id','customer_mobile','plan_id','monthly_fee')->where('id', $ihistoryArr->refrence_id)->get()->first();
            // Customer Address 
            $pdf->Text(70, 136.6, unitAddressByUnitID($currentPlanArr->address_id));
            // Customer Mobile 
            $pdf->Text(160, 129.6, $currentPlanArr->customer_mobile);
            
            //Changed Location Details
            $pdf->Rect(45.48, 139.5, 3.2, 3, 'DF');
            // Customer Address 
            $pdf->Text(70, 151, unitAddressByUnitID($ihistoryArr->address_id));
            // Customer Mobile 
            $pdf->Text(160, 143.7, $ihistoryArr->customer_mobile);
            
            //Current Plan Details
            // Plan Name 
            $pdf->Text(71, 165.2, planDetailsByPlanID($currentPlanArr->plan_id)['plan_name']);
            // Plan Unlimited 
            $pdf->Text(125, 165.2, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($currentPlanArr->plan_id)['data_usage'])." / ",ENT_COMPAT,'UTF-8'));
            // Plan Speed 
            $pdf->Text(143.6, 165.2, planDetailsByPlanID($currentPlanArr->plan_id)['speed'].' '.planDetailsByPlanID($currentPlanArr->plan_id)['speed_unit']);
            // Plan Monthly Fee 
            $pdf->Text(185, 165.2, '$'.amountFormat2($currentPlanArr->monthly_fee));
            // Plan Data Usage 
            $pdf->Text(81.5, 172.8, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($currentPlanArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));

            // Reinstallation Fee 
            $pdf->Text(50, 230, amountFormat2($ihistoryArr->reinstallation_fee));
            // Other Fee 
            $pdf->Text(150.5, 229.2, amountFormat2($ihistoryArr->others_fee));
            // Total Fee 
            $pdf->Text(45, 236.2, amountFormat2($ihistoryArr->total_amount));
            // Payment By 
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(0, 0, 0);
            // Payment by Customer Pay
            if($ihistoryArr->payment_by=='CP') { $pdf->Rect(115, 233.2, 3.2, 3, 'DF'); }
            // Payment by Company Refund
            else { $pdf->Rect(164, 233.2, 3.2, 3, 'DF'); }

            // Footer Customer Name 
            $pdf->Text(22, 257.6, customerDetailsByID($ihistoryArr->customer_id)['name']);
            // Footer Customer Date 
            $pdf->Text(75, 257.6, pdfDateFormat($ihistoryArr->start_date_time));

            // Footer Offiser Name 
            $pdf->Text(116, 257.6, html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'));
            // Footer Offiser Date 
            $pdf->Text(169, 257.6, pdfDateFormat($ihistoryArr->start_date_time));
            // Footer Remark
            $pdf->Text(35, 271.5, Str::substr($ihistoryArr->remark, 0, 105)."\r\n");
            $pdf->Text(35, 275.6, Str::substr($ihistoryArr->remark, 105, 100)."\r\n");
            $pdf->Text(35, 279.6, Str::substr($ihistoryArr->remark, 205, 100)."\r\n");
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die;
            //Download New Connection Invoice
            $pdf->Output('D', 'Change-Location - '.buildingLocationAndUnitNumberByUnitID($ihistoryArr->address_id).'.pdf');
            
            //Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Change internet location invoice generated reference id [".$ihistoryArr->id."].");
            $logs->ip_address = $request->ip();
            $logs->save();
        
        }
        // Generate Invoice for Terminate Internet Service
        elseif($ihistoryArr->entry_type=='TS'){
            
            $pdf = new Fpdi('P','mm', 'A4');
            $pdf->AddPage();
            $pdf->SetFont('times','',11);
            $fileContent = file_get_contents(asset('assets/internet_pdf/internet_chg.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            
            // Service Type 
            $pdf->Rect(135.9, 29.9, 3.2, 3, 'DF');
            
            // Customer Type 
            customerDetailsByID($ihistoryArr->customer_id)['type']=='P' ? $pdf->Rect(81.5, 43.8, 3.2, 3, 'DF'): $pdf->Rect(165.5, 43.8, 3.2, 3, 'DF');
            //Personal Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='P'){
                // Customer Name 
                $pdf->Text(76,55.8, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Customer Address 
                $pdf->Text(70, 71.5, unitAddressByUnitID($ihistoryArr->address_id));
                // Customer Mobile 
                $pdf->Text(155, 79.3, $ihistoryArr->customer_mobile);
                // Customer Email 
                $pdf->Text(70, 79.2, customerDetailsByID($ihistoryArr->customer_id)['email']);
            }
            //Shop Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='S'){
                // Shop Name 
                $pdf->Text(75,87.5, customerDetailsByID($ihistoryArr->customer_id)['shop_name']);
                // Authorized Person 
                $pdf->Text(90,109.5, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Shop Address 
                $pdf->Text(65, 102.3, unitAddressByUnitID($ihistoryArr->address_id));
                // Shop Mobile 
                $pdf->Text(75, 95.3, $ihistoryArr->customer_mobile);
                // Shop Email 
                $pdf->Text(65, 116.6, customerDetailsByID($ihistoryArr->customer_id)['shop_email']);
                // VAT Number
                $pdf->Text(155, 87, customerDetailsByID($ihistoryArr->customer_id)['vat_no']);
            }
            //Previous Location Details
            $pdf->Rect(45.4, 126.2, 3.2, 3, 'DF');
            $currentPlanArr= $ihistoryArr->select('address_id','customer_id','customer_mobile','plan_id')->where('id', $ihistoryArr->refrence_id)->get()->first();
            // Customer Address 
            $pdf->Text(70, 136.6, unitAddressByUnitID($currentPlanArr->address_id));
            // Customer Mobile 
            $pdf->Text(160, 129.6, $ihistoryArr->customer_mobile);
            
            //Current Plan Details
            // Plan Name 
            $pdf->Text(71, 165.2, planDetailsByPlanID($currentPlanArr->plan_id)['plan_name']);
            // Plan Unlimited 
            $pdf->Text(125, 165.2, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($currentPlanArr->plan_id)['data_usage'])." / ",ENT_COMPAT,'UTF-8'));
            // Plan Speed 
            $pdf->Text(143.6, 165.2, planDetailsByPlanID($currentPlanArr->plan_id)['speed'].' '.planDetailsByPlanID($currentPlanArr->plan_id)['speed_unit']);
            // Plan Monthly Fee 
            $pdf->Text(185, 165.2, '$'.amountFormat2($ihistoryArr->monthly_fee));
            // Plan Data Usage 
            $pdf->Text(81.5, 172.8, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($currentPlanArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));
            
            // Effective Date 
            $pdf->Text(55, 209.6, pdfDateFormat($ihistoryArr->terminate_date));

            // Other Fee 
            $pdf->Text(150.5, 229.2, amountFormat2($ihistoryArr->others_fee));
            // Total Fee 
            $pdf->Text(45, 236.2, amountFormat2($ihistoryArr->total_amount));
            // Payment By 
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(0, 0, 0);
            // Payment by Customer Pay
            if($ihistoryArr->payment_by=='CP') { $pdf->Rect(115.1, 233.2, 3.2, 3, 'DF'); }
            // Payment by Company Refund
            else { $pdf->Rect(164.17, 233.2, 3.2, 3, 'DF'); }

            // Footer Customer Name 
            $pdf->Text(22, 257.6, customerDetailsByID($ihistoryArr->customer_id)['name']);
            // Footer Customer Date 
            $pdf->Text(75, 257.6, pdfDateFormat($ihistoryArr->terminate_date));

            // Footer Offiser Name 
            $pdf->Text(116, 257.6, html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'));
            // Footer Offiser Date 
            $pdf->Text(169, 257.6, pdfDateFormat($ihistoryArr->terminate_date));
            // Footer Remark
            $pdf->Text(35, 271.5, Str::substr($ihistoryArr->remark, 0, 105)."\r\n");
            $pdf->Text(35, 275.6, Str::substr($ihistoryArr->remark, 105, 100)."\r\n");
            $pdf->Text(35, 279.6, Str::substr($ihistoryArr->remark, 205, 100)."\r\n");
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die;
            //Download New Connection Invoice
            $pdf->Output('D', 'Terminate-Internet - '.buildingLocationAndUnitNumberByUnitID($ihistoryArr->address_id).'.pdf');
            
            //Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Terminate internet service invoice generated reference id [".$ihistoryArr->id."].");
            $logs->ip_address = $request->ip();
            $logs->save();
        
        }
        // Generate Invoice for Suspend Internet Service
        elseif($ihistoryArr->entry_type=='SS'){
            
            $pdf = new Fpdi('P','mm', 'A4');
            $pdf->AddPage();
            $pdf->SetFont('times','',11);
            $fileContent = file_get_contents(asset('assets/internet_pdf/internet_chg.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            
            // Service Type 
            $pdf->Rect(174.59, 29.8, 3.2, 3, 'DF');
            
            // Customer Type 
            customerDetailsByID($ihistoryArr->customer_id)['type']=='P' ? $pdf->Rect(81.5, 43.8, 3.2, 3, 'DF'): $pdf->Rect(165.4, 43.8, 3.2, 3, 'DF');
            //Personal Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='P'){
                // Customer Name 
                $pdf->Text(76,55.8, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Customer Address 
                $pdf->Text(70, 71.5, unitAddressByUnitID($ihistoryArr->address_id));
                // Customer Mobile 
                $pdf->Text(155, 79.3, $ihistoryArr->customer_mobile);
                // Customer Email 
                $pdf->Text(70, 79.2, customerDetailsByID($ihistoryArr->customer_id)['email']);
            }
            //Shop Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='S'){
                // Shop Name 
                $pdf->Text(75,87.5, customerDetailsByID($ihistoryArr->customer_id)['shop_name']);
                // Authorized Person 
                $pdf->Text(90,109.5, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Shop Address 
                $pdf->Text(65, 102.3, unitAddressByUnitID($ihistoryArr->address_id));
                // Shop Mobile 
                $pdf->Text(75, 95.3, $ihistoryArr->customer_mobile);
                // Shop Email 
                $pdf->Text(65, 116.6, customerDetailsByID($ihistoryArr->customer_id)['shop_email']);
                // VAT Number
                $pdf->Text(155, 87, customerDetailsByID($ihistoryArr->customer_id)['vat_no']);
            }
            //Previous Location Details
            $pdf->Rect(45.4, 126.2, 3.2, 3, 'DF');
            // Customer Address 
            $pdf->Text(70, 136.6, unitAddressByUnitID($ihistoryArr->address_id));
            // Customer Mobile 
            $pdf->Text(160, 129.6, $ihistoryArr->customer_mobile);
            
            //Current Plan Details
            // Plan Name 
            $pdf->Text(71, 165.2, planDetailsByPlanID($ihistoryArr->plan_id)['plan_name']);
            // Plan Unlimited 
            $pdf->Text(125, 165.2, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage'])." / ",ENT_COMPAT,'UTF-8'));
            // Plan Speed 
            $pdf->Text(143.6, 165.2, planDetailsByPlanID($ihistoryArr->plan_id)['speed'].' '.planDetailsByPlanID($ihistoryArr->plan_id)['speed_unit']);
            // Plan Monthly Fee 
            $pdf->Text(185, 165.2, '$'.amountFormat2($ihistoryArr->monthly_fee));
            // Plan Data Usage 
            $pdf->Text(81.5, 172.8, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));
            
            // Effective Date 
            $pdf->Text(55, 209.6, pdfDateFormat($ihistoryArr->start_date_time));
            // Suspension Date Period
            $pdf->Text(135, 209.6, pdfDateFormat($ihistoryArr->suspension_start_date).'   ~   '.pdfDateFormat($ihistoryArr->suspension_end_date));

            // Plan Others Fee 
            $pdf->Text(135, 230.2, '$'.amountFormat2($ihistoryArr->due_amount+$ihistoryArr->others_fee));
            // Plan Total Fee 
            $pdf->Text(50, 236.6, '$'.amountFormat2($ihistoryArr->total_amount));
            // Payment by Customer Pay
            if($ihistoryArr->payment_by=='CP') { $pdf->Rect(115.1, 233.2, 3.2, 3, 'DF'); }
            // Payment by Company Refund
            else { $pdf->Rect(164.17, 233.2, 3.2, 3, 'DF'); }

            // Footer Customer Name 
            $pdf->Text(22, 257.6, customerDetailsByID($ihistoryArr->customer_id)['name']);
            // Footer Customer Date 
            $pdf->Text(75, 257.6, pdfDateFormat($ihistoryArr->start_date_time));

            // Footer Offiser Name 
            $pdf->Text(116, 257.6, html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'));
            // Footer Offiser Date 
            $pdf->Text(169, 257.6, pdfDateFormat($ihistoryArr->start_date_time));
            // Footer Remark
            $pdf->Text(35, 271.5, Str::substr($ihistoryArr->remark, 0, 105)."\r\n");
            $pdf->Text(35, 275.6, Str::substr($ihistoryArr->remark, 105, 100)."\r\n");
            $pdf->Text(35, 279.6, Str::substr($ihistoryArr->remark, 205, 100)."\r\n");
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die;
            //Download New Connection Invoice
            $pdf->Output('D', 'Suspend-Internet - '.buildingLocationAndUnitNumberByUnitID($ihistoryArr->address_id).'.pdf');
            
            //Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Suspend internet service invoice generated reference id [".$ihistoryArr->id."].");
            $logs->ip_address = $request->ip();
            $logs->save();
        
        }else if($ihistoryArr->entry_type=='MP'){
            
            $exchange_rate = new ExchangeRate; 
            $exchangeRateArr= $exchange_rate->whereDate('monthly_date', $ihistoryArr->monthly_invoice_date)->get()->first();
            
            $pdf = new Fpdi('P','mm', 'A4');                
            $fileContent = file_get_contents(asset('assets/internet_pdf/dmc_invoice.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            $pdf->SetFont('times','B',13); 
            //Personal Application
            // Customer Name 
            customerDetailsByID($ihistoryArr->customer_id)['type']=='P'? $pdf->Text(11,50.5, customerDetailsByID($ihistoryArr->customer_id)['name']): $pdf->Text(11,50.5, customerDetailsByID($ihistoryArr->customer_id)['shop_name']);
            $pdf->SetFont('times','',10);
            // Customer Mobile 
            $pdf->Text(35, 61, $ihistoryArr->customer_mobile);
            // Customer Address 
            $pdf->Text(35, 71.8, unitAddressByUnitID($ihistoryArr->address_id));
            $pdf->Text(35, 76.8, 'CamKo Street, Sangkat Toul Sangke 2, Khan Russey Keo,');
            $pdf->Text(35, 81.8, 'Phnom Penh, Cambodia - 120707');
            // VAT Number
            $pdf->Text(68.5, 87.9, customerDetailsByID($ihistoryArr->customer_id)['vat_no']);
            // Invoice Date 
            $pdf->Text(175, 54.4, pdfDateFormat($ihistoryArr->monthly_invoice_date));
            // Due Date 
            $pdf->Text(175, 64.5, dueDateOfMonthlyPayment($ihistoryArr->monthly_invoice_date));
            // Customer Internet ID
            $pdf->Text(175, 74.3, customerNumberByID(customerDetailsByID($ihistoryArr->customer_id)['internet_id']));
            // Invoice Number
            $pdf->Text(158, 87.4, invoiceName($ihistoryArr->invoice_number, $ihistoryArr->monthly_invoice_date));    
            // Service Date 
            $pdf->Text(85.5, 105.3, fromToMonthlyInvoiceDate($ihistoryArr->id)); 
            // Balance 
            $pdf->Text(monthlyInvoiceXCordinate($ihistoryArr->balance,0), 105, '$'.amountFormat2($ihistoryArr->balance));
            // Other Fee 
            $pdf->Text(monthlyInvoiceXCordinate($ihistoryArr->others_fee,0), 125.7, '$'.amountFormat2($ihistoryArr->others_fee));
            // Sub Total 
            $pdf->Text(monthlyInvoiceXCordinate(($ihistoryArr->balance+$ihistoryArr->others_fee),0), 138.8, '$'.amountFormat2($ihistoryArr->balance+$ihistoryArr->others_fee));
            // VAT Amount 
            $pdf->Text(monthlyInvoiceXCordinate($ihistoryArr->vat_amount,0), 145.8, '$'.amountFormat2($ihistoryArr->vat_amount));
            // Exchange Rate 
            $pdf->Text(175.9, 160.5, number_format($exchangeRateArr->rate, 0, '.',','));
            $pdf->SetFont('times','B',11);
            // Grand Amount 
            $pdf->Text(monthlyInvoiceXCordinate($ihistoryArr->total_amount,-0.8), 153.3, '$'.amountFormat2($ihistoryArr->total_amount));
            // Grand Amount in KHR 
            $pdf->Text(monthlyInvoiceXCordinate(($exchangeRateArr->rate*$ihistoryArr->total_amount),0), 168.2, number_format($exchangeRateArr->rate*$ihistoryArr->total_amount), 2, '.',',');
            $pdf->Rect(12.2, 207, 3.2, 3.5, 'DF');
            // Plan Name 
            $pdf->Text(40, 218.7, planDetailsByPlanID($ihistoryArr->plan_id)['plan_name']);                
            $pdf->SetFont('times','B',10);
            // All Day Speed
            $pdf->Text(52, 224, planDetailsByPlanID($ihistoryArr->plan_id)['speed'].' '.planDetailsByPlanID($ihistoryArr->plan_id)['speed_unit']);
            // Day Time Speed
            $pdf->Text(90, 224, planDetailsByPlanID($ihistoryArr->plan_id)['speed'].' '.planDetailsByPlanID($ihistoryArr->plan_id)['speed_unit']);
            // Night Time Speed
            $pdf->Text(130, 224, planDetailsByPlanID($ihistoryArr->plan_id)['speed'].' '.planDetailsByPlanID($ihistoryArr->plan_id)['speed_unit']);
            // All Day Data Usage
            $pdf->Text(52, 229.8, LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']));
            // Day Time Data Usage
            $pdf->Text(90, 229.8, LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']));
            // Night Time Data Usage
            $pdf->Text(130, 229.8, LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']));
            // Monthly Fee
            $pdf->Text(179, 222.4, '$'.amountFormat2($ihistoryArr->monthly_fee));
            // Reg Date 
            $pdf->Text(179, 230.2, pdfDateFormat(internetRegisterDateByCustID($ihistoryArr->customer_id)));
            $pdf->Rect(12, 245.8, 3.2, 3.5, 'DF');
            //Barcode            
            $code = invoiceName($ihistoryArr->invoice_number, $ihistoryArr->monthly_invoice_date);
            $pdf->Code128(145,245.8,$code,55,10);
            $pdf->SetXY(143.6,256);
            $pdf->Write(5,$code);
            //Invoice Name
            $pdfName = invoiceName($ihistoryArr->invoice_number, $ihistoryArr->monthly_invoice_date).'.pdf';
            //Set Title
            $pdf->SetTitle(html_entity_decode($pdfName,ENT_COMPAT,'UTF-8'), true);
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($request->session()->get('userID')),ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die; 
            //Compress Pdf                                  
            $pdf->SetCompression(true);  
            $pdf->Output('D', $pdfName);
            $pdf->Close(); 
            //Download Monthly Payment Invoice            
            
            // Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Monthly Payment invoice generated reference id [".$ihistoryArr->id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

        }//Reconnect Service
        else if($ihistoryArr->entry_type=='RC'){

            $pdf = new Fpdi('P','mm', 'A4');
            $pdf->AddPage();
            $pdf->SetFont('times','',11);
            $fileContent = file_get_contents(asset('assets/internet_pdf/internet_chg.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            
            // Service Type 
            $pdf->Rect(15, 29.9, 3.2, 3, 'DF');
            
            // Customer Type 
            customerDetailsByID($ihistoryArr->customer_id)['type']=='P' ? $pdf->Rect(81.5, 43.8, 3.2, 3, 'DF'): $pdf->Rect(165.5, 43.8, 3.2, 3, 'DF');
            //Personal Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='P'){
                // Customer Name 
                $pdf->Text(76,55.8, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Customer Address 
                $pdf->Text(70, 71.5, unitAddressByUnitID($ihistoryArr->address_id));
                // Customer Mobile 
                $pdf->Text(155, 79.3, $ihistoryArr->customer_mobile);
                // Customer Email 
                $pdf->Text(70, 79.2, customerDetailsByID($ihistoryArr->customer_id)['email']);
            }
            //Shop Application
            if(customerDetailsByID($ihistoryArr->customer_id)['type']=='S'){
                // Shop Name 
                $pdf->Text(75,87.5, customerDetailsByID($ihistoryArr->customer_id)['shop_name']);
                // Authorized Person 
                $pdf->Text(90,109.5, customerDetailsByID($ihistoryArr->customer_id)['name']);
                // Shop Address 
                $pdf->Text(65, 102.3, unitAddressByUnitID($ihistoryArr->address_id));
                // Shop Mobile 
                $pdf->Text(75, 95.3, $ihistoryArr->customer_mobile);
                // Shop Email 
                $pdf->Text(65, 116.6, customerDetailsByID($ihistoryArr->customer_id)['shop_email']);
                // VAT Number
                $pdf->Text(155, 87, customerDetailsByID($ihistoryArr->customer_id)['vat_no']);
            }
            // Plan Name 
            $pdf->Text(71, 165.2, planDetailsByPlanID($ihistoryArr->plan_id)['plan_name']);
            // Plan Unlimited 
            $pdf->Text(125, 165.2, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage'])." / ",ENT_COMPAT,'UTF-8'));
            // Plan Speed 
            $pdf->Text(143.6, 165.2, planDetailsByPlanID($ihistoryArr->plan_id)['speed'].' '.planDetailsByPlanID($ihistoryArr->plan_id)['speed_unit']);
            // Plan Monthly Fee 
            $pdf->Text(185, 165.2, '$'.amountFormat2($ihistoryArr->monthly_fee));
            // Plan Data Usage 
            $pdf->Text(81.5, 172.8, html_entity_decode(LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistoryArr->plan_id)['data_usage']),ENT_COMPAT,'UTF-8'));
            
            //Suspend Details
            $SuspendArr= $ihistoryArr->select('suspension_start_date','suspension_end_date','suspension_period')->where('id', $ihistoryArr->refrence_id)->get()->first();
            // Effective Date 
            $pdf->Text(55, 209.6, pdfDateFormat($ihistoryArr->start_date_time));
            // Suspension Date Period
            $pdf->Text(132, 209.6, pdfDateFormat($SuspendArr->suspension_start_date).' ~ '.pdfDateFormat($SuspendArr->suspension_end_date).' = '.$SuspendArr->suspension_period.' Days');
            // Previous Due Fee 
            $pdf->Text(149, 222.8, amountFormat2($ihistoryArr->due_amount));
            // Reconnect Fee 
            $pdf->Text(48, 230.5, amountFormat2($ihistoryArr->reconnect_fee));
            // Plan Other Fee 
            $pdf->Text(150.5, 229.8, amountFormat2($ihistoryArr->others_fee));
            // Total Fee 
            $pdf->Text(45, 236.5, amountFormat2($ihistoryArr->total_amount));
            // Payment By 
            $pdf->SetDrawColor(0, 0, 0);
            $pdf->SetFillColor(0, 0, 0);
            // Payment by Customer Pay
            if($ihistoryArr->payment_by=='CP') { $pdf->Rect(115.1, 233.2, 3.2, 3, 'DF'); }
            // Payment by Company Refund
            else { $pdf->Rect(164.17, 233.2, 3.2, 3, 'DF'); }

            // Footer Customer Name 
            $pdf->Text(22, 257.6, customerDetailsByID($ihistoryArr->customer_id)['name']);
            // Footer Customer Date 
            $pdf->Text(75, 257.6, pdfDateFormat($ihistoryArr->start_date_time));

            // Footer Offiser Name 
            $pdf->Text(116, 257.6, html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'));
            // Footer Offiser Date 
            $pdf->Text(169, 257.6, pdfDateFormat($ihistoryArr->start_date_time));
            // Footer Remark
            $pdf->Text(35, 271.5, Str::substr($ihistoryArr->remark, 0, 105)."\r\n");
            $pdf->Text(35, 275.6, Str::substr($ihistoryArr->remark, 105, 100)."\r\n");
            $pdf->Text(35, 279.6, Str::substr($ihistoryArr->remark, 205, 100)."\r\n");
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode(userNameByUserID($ihistoryArr->user_id),ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die;
            //Download New Connection Invoice
            $pdf->Output('D', 'Reconnect - '.buildingLocationAndUnitNumberByUnitID($ihistoryArr->address_id).'.pdf');
            
            //Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Reconnect internet plan invoice generated reference id [".$ihistoryArr->id."].");
            $logs->ip_address = $request->ip();
            $logs->save();
        }
        else{
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Something went wrong, Please try agian!');
            return redirect()->route('internet-history');
        }
        
    }

    /**
     * Export Customer Info.
     *
     * @param  Request  $request
     * @return Response
     */
    public function export_customer_info(Request $request)
    {        
        $user = new User;        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        if($request->get('monthYear'))
        {
            $monthYear = $request->get('monthYear');
            $fileName = givenYearMonth2($monthYear).'_ISP_Customer_Info.csv';
            
            
            $flag = Excel::store(new InternetMonthlyCustomerInfoExport(), $fileName,'file_location');
            //return Excel::download(new InternetMonthlyCustomerInfoExport, $fileName);
            
            if($flag)
            {
                $content = file_get_contents(asset($fileName));
                $conn_id = ftp_connect(env('FTP_SERVER_HOST'));
                $login = ftp_login($conn_id, env('FTP_SERVER_USERNAME'), env('FTP_SERVER_PASSWORD'));
                ftp_pasv($conn_id, true);
                // create
                $tmp = fopen(tempnam(sys_get_temp_dir(), $fileName), "w+");
                fwrite($tmp, $content);
                rewind($tmp);
                // upload
                $upload = ftp_fput($conn_id, 'ISP_Customer_Info_List/'.$fileName, $tmp, FTP_ASCII);//Test
                // close
                ftp_close($conn_id);

                if (@file_exists($fileName)) {
                    unlink($fileName);
                }
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg',$fileName.' has been sent to FTP server successfully!');
                return redirect()->route('internet-subscribers',['monthYear'=>$monthYear]); 
            }
            else
            {
                Session::flash('color','warning');
                Session::flash('icon','fa fa-exclamation-triangle');
                Session::flash('msg','Something went wrong, Please try again.');
                return redirect()->route('internet-subscribers',['monthYear'=>$monthYear]);
            }
        }
        else
        {
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('internet-subscribers');
        }
    }

    /**
     * Export Up/Down Stream.
     *
     * @param  Request  $request
     * @return Response
     */
    public function export_updown_stream(Request $request)
    {        
        $user = new User;        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        if($request->get('monthYear'))
        {
            $monthYear = $request->get('monthYear');
            $fileName = givenYearMonth2($monthYear).'_ISP_Upstream_Downstream.csv';
            
            $flag = Excel::store(new InternetMonthlyUpDownStreamExport(givenYearMonth2($monthYear)), $fileName,'file_location');
            if($flag)
            {
                $content = file_get_contents(asset($fileName));
                $conn_id = ftp_connect(env('FTP_SERVER_HOST'));
                $login = ftp_login($conn_id, env('FTP_SERVER_USERNAME'), env('FTP_SERVER_PASSWORD'));
                ftp_pasv($conn_id, true);
                // create
                $tmp = fopen(tempnam(sys_get_temp_dir(), $fileName), "w+");
                fwrite($tmp, $content);
                rewind($tmp);
                // upload
                $upload = ftp_fput($conn_id, 'ISP_Upstream_BW/'.$fileName, $tmp, FTP_ASCII);//Test ISP_Upstream_BW
                // close
                ftp_close($conn_id);

                if (@file_exists($fileName)) {
                    unlink($fileName);
                }
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg',$fileName.' has been sent to FTP server successfully!');
                return redirect()->route('internet-subscribers',['monthYear'=>$monthYear]); 
            }
            else
            {
                Session::flash('color','warning');
                Session::flash('icon','fa fa-exclamation-triangle');
                Session::flash('msg','Something went wrong, Please try again.');
                return redirect()->route('internet-subscribers',['monthYear'=>$monthYear]);
            }
        }
        else
        {
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('internet-subscribers');
        }
    }

    /**
     * Export Model House Summary.
     *
     * @param  Request  $request
     * @return Response
     */
    public function model_house_summary(Request $request)
    {        
        $user = new User;        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-monthly-invoice');
        }

        if($request->get('monthYear')){
            $monthYear= $request->get('monthYear');
            $previousDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($request->get('monthYear')));
            $currentDate = internetEndMonthlyPaymentDate($request->get('monthYear'));            
            return Excel::download(new InternetModelHouseSummaryExport($previousDate,$currentDate), 'List_Invoice_'.datePickerFormat2($currentDate).'_For_Model_House.xlsx');
            
        }else{
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('internet-monthly-invoice');
        }
    }

    /**
     * Export DMC Summary.
     *
     * @param  Request  $request
     * @return Response
     */
    public function dmc_summary(Request $request)
{        
    $user = new User;        
    $userArr = $user->where('id', $request->session()->get('userID'))->first();
    
    if(!checkPermission($userArr->download)){
        // Permission not granted
        Session::flash('color','warning');
        Session::flash('icon','fa fa-exclamation-triangle');
        Session::flash('msg','Download permission not granted!');
        return redirect()->route('internet-subscribers');
    }

    if($request->get('monthYear')){
        $monthYear = $request->get('monthYear');
        $previousDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($request->get('monthYear')));
        $invoiceDate = internetEndMonthlyPaymentDate($request->get('monthYear'));
        $fileName = givenYearMonth2($monthYear).'_List_Invoice_Internet.xlsx';
        
        // Excel 파일을 직접 다운로드
        return Excel::download(new InternetDMCSummaryExport($previousDate,$invoiceDate), $fileName);
    }else{
        // Month Year not Selected
        Session::flash('color','warning');
        Session::flash('icon','fa fa-exclamation-triangle');
        Session::flash('msg','Please select month and year.');
        return redirect()->route('internet-subscribers');
    }
}

    // public function dmc_summary(Request $request)
    // {        
    //     $user = new User;        
    //     $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
    //     if(!checkPermission($userArr->download)){
    //         // Permission not granted
    //         Session::flash('color','warning');
    //         Session::flash('icon','fa fa-exclamation-triangle');
    //         Session::flash('msg','Download permission not granted!');
    //         return redirect()->route('internet-subscribers');
    //     }

    //     if($request->get('monthYear')){
    //         $monthYear = $request->get('monthYear');
    //         $previousDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($request->get('monthYear')));
    //         $invoiceDate = internetEndMonthlyPaymentDate($request->get('monthYear'));
    //         $fileName = givenYearMonth2($monthYear).'_List_Invoice_Internet.xlsx';
            
    //         $flag = Excel::store(new InternetDMCSummaryExport($previousDate,$invoiceDate), $fileName,'file_location');
    //         if($flag)
    //         {
    //             $content = file_get_contents(asset($fileName));
    //             $conn_id = ftp_connect(env('FTP_SERVER_HOST'));
    //             $login = ftp_login($conn_id, env('FTP_SERVER_USERNAME'), env('FTP_SERVER_PASSWORD'));
    //             ftp_pasv($conn_id, true);
    //             // create
    //             $tmp = fopen(tempnam(sys_get_temp_dir(), $fileName), "w+");
    //             fwrite($tmp, $content);
    //             rewind($tmp);
    //             // upload
    //             $upload = ftp_fput($conn_id, 'ISP_Invoice_Summary/'.$fileName, $tmp, FTP_ASCII);//Test
    //             // close
    //             ftp_close($conn_id);

    //             if (@file_exists($fileName)) {
    //                 unlink($fileName);
    //             }
    //             Session::flash('color','success');
    //             Session::flash('icon','fa fa-check');
    //             Session::flash('msg',$fileName.' has been sent to FTP server successfully!');
    //             return redirect()->route('internet-subscribers'); 
    //         }else{
    //             Session::flash('color','warning');
    //             Session::flash('icon','fa fa-exclamation-triangle');
    //             Session::flash('msg','Something went wrong, Please try again.');
    //             return redirect()->route('internet-subscribers');
    //         }
    //     }else{
    //         // Month Year not Selected
    //         Session::flash('color','warning');
    //         Session::flash('icon','fa fa-exclamation-triangle');
    //         Session::flash('msg','Please select month and year.');
    //         return redirect()->route('internet-subscribers');
    //     }
    // }

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
            return redirect()->route('internet-monthly-invoice');
        }

        if($request->input('export_month_year')){
            $monthYear = $request->input('export_month_year');
            $monthYearArr = explode('-',$monthYear);
            $month = $monthYearArr[0];
            $year = $monthYearArr[1];
            $status = $request->input('status');
            $addressID = $request->input('address');
            return Excel::download(new InternetMonthlyPaymentExport($month, $year, $status, $addressID), 'Internet Monthly Payment for '.monthNumberToName($month).' '.$year.'.xlsx');
            
        }else{
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('internet-monthly-invoice');
        }
    }

    /**
     * Export Unpaid Invoices.
     *
     * @param  Request  $request
     * @return Response
     */
    public function export_unpaid_internet(Request $request)
    {        
        $user = new User;        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-monthly-invoice');
        }

        $year = NULL;
        $month = NULL;        
        if($request->input('monthYear')){    
            $monthYear = $request->input('monthYear');
            $monthYearArr = explode('-',$monthYear);
            $month = $monthYearArr[0];
            $year = $monthYearArr[1];
        }
        
        return Excel::download(new UnpaidMonthlyInternetExport($month, $year), 'Internet Unpaid Payment for '.monthNumberToName($month).' '.$year.'.xlsx');
    }

    /**
     * Show Import Unpaid Invoice Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function import_unpaid_internet(Request $request)
    {        
        $user = new User;
        
        $userArr= $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->upload)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Upload permission not granted!');
            return redirect()->route('internet-monthly-invoice');
        }
       
        return view('users.idc.internet.import_unpaid')
                    ->with(['userArr'=>$userArr]);
    }

    /**
     * Validate & Update Momthly Unpaid Invoice.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_unpaid_internet(Request $request)
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
            Excel::import(new UnpaidMonthlyInternetImport,request()->file('import_file'));
            // Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Unpaid invoices has been updated.");
            $logs->ip_address = $request->ip();
            $logs->save();

            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Unpaid invoices has been updated successfully.');
            return redirect()->route('internet-monthly-invoice');
        }
    }

    /**
     * Export IDC/DMC Invoice.
     *
     * @param  Request  $request
     * @return Response
     */
    public function export_idc_dmc_invoice(Request $request)
    {
        $user = new User;
        $ihistory = new InternetHistory;
        $exchange_rate = new ExchangeRate;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-monthly-invoice');
        }
       
        if($request->get('invoice_month_year')){
            $monthYear = $request->get('invoice_month_year');
            $invoiceDate = internetEndMonthlyPaymentDate($monthYear);
            $iInvoiceArr = $ihistory->where('monthly_invoice_date', $invoiceDate)->orderBy('invoice_number','asc')->get()->all();
            $exchangeRateArr = $exchange_rate->where('monthly_date', $invoiceDate)->orderByDesc('id')->get()->first();
            
            if(!is_object($exchangeRateArr)){
                // Permission not granted
                Session::flash('color','warning');
                Session::flash('icon','fa fa-exclamation-triangle');
                Session::flash('msg','Please add monthly exchange rate first.');
                return redirect()->route('internet-monthly-invoice',['monthlyYear'=>$monthYear]);
            }

            foreach($iInvoiceArr as $ikey=>$iArr){
                ini_set('memory_limit', -1);
                ini_set ('max_execution_time', -1);
                $pdf = new Fpdi('P','mm', 'A4');                
                $fileContent = file_get_contents(asset('assets/internet_pdf/new_dmc_invoice.pdf'),'rb');
                $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
                $tplIdx = $pdf->importPage(1);
                $pdf->AddPage();
                $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
                $pdf->SetFont('times','B',13); 
                //Personal Application
                // Customer Name 
                customerDetailsByID($iArr->customer_id)['type']=='P'? $pdf->Text(11,50.5, customerDetailsByID($iArr->customer_id)['name']): $pdf->Text(11,50.5, customerDetailsByID($iArr->customer_id)['shop_name']);
                $pdf->SetFont('times','',10);
                // Customer Mobile 
                $pdf->Text(35, 61, $iArr->customer_mobile);
                // Customer Address 
                $pdf->Text(35, 71.8, unitAddressByUnitID($iArr->address_id));
                $pdf->Text(35, 76.8, 'CamKo Street, Sangkat Toul Sangke 2, Khan Russey Keo,');
                $pdf->Text(35, 81.8, 'Phnom Penh, Cambodia - 120707');
                // VAT Number
                $pdf->Text(68.5, 87.9, customerDetailsByID($iArr->customer_id)['vat_no']);
                // Invoice Date 
                $pdf->Text(175, 54.4, pdfDateFormat($iArr->monthly_invoice_date));
                // Due Date 
                $pdf->Text(175, 64.5, dueDateOfMonthlyPayment($iArr->monthly_invoice_date));
                // Customer Internet ID
                $pdf->Text(175, 74.3, customerNumberByID(customerDetailsByID($iArr->customer_id)['internet_id']));
                // Invoice Number
                $pdf->Text(158, 87.4, invoiceName($iArr->invoice_number, $iArr->monthly_invoice_date));
                // Service Date 
                $pdf->Text(85.5, 105.3, fromToMonthlyInvoiceDate($iArr->id)); 
                if(strlen((int)amountFormat2($iArr->balance))=='1'){
                    $balanceX = 174;
                    $installX = 174;
                    $changeX = 174;
                    $reconnectX = 174;
                    $otherX = 174;
                    $subX =   174;
                    $vatX = 174;
                    $exchangeX = 174;
                    $grandX = 174;
                    $exchangeGrandX = 174;
                }elseif(strlen((int)amountFormat2($iArr->balance))=='2'){
                    $balanceX = 174;
                    $installX = 174;
                    $changeX = 174;
                    $reconnectX = 174;
                    $otherX = 175.7;
                    $subX = 174;
                    $vatX = 175.6;
                    $exchangeX = 174;
                    $grandX = 174;
                    $exchangeGrandX = 174;
                }else{
                    $balanceX = 174;
                    $installX = 174;
                    $changeX = 174;
                    $reconnectX = 174;
                    $otherX = 177.5;
                    $subX = 174;
                    $vatX = 175.8;
                    $exchangeX = 177;
                    $grandX = 174;
                    $exchangeGrandX = 174;
                }
                // Balance 
                $pdf->Text($balanceX, 105, '$'.amountFormat2($iArr->balance));
                $refrenceFee = amountFormat2(0);
                $refrenceVat = amountFormat2(0);
                $refrenceAmount = amountFormat2(0);
                $startDate = internetStartMonthlyPaymentDate(monthYear($iArr->monthly_invoice_date));
                $endDate = internetEndMonthlyPaymentDate(monthYear($iArr->monthly_invoice_date));
                $referenceArr = ihistoryFeeByMonthYear($iArr->customer_id, $iArr->address_id, $startDate, $endDate);

                if(is_object($referenceArr))
                {
                    foreach($referenceArr as $rkey=>$rval)
                    {
                        if($rval->entry_type=='NR')
                        {
                            // Install
                            $refrenceFee = $refrenceFee+($rval->installation_fee/1.1);
                            $refrenceVat = $refrenceVat+($rval->installation_fee/11);
                            $refrenceAmount = $refrenceAmount+($rval->installation_fee);
                            $pdf->Text($installX, 112, '$'.amountFormat2($rval->installation_fee/1.1));
                        }
                        if($rval->entry_type=='CL')
                        {
                            // Change Location 
                            $refrenceFee = $refrenceFee+($rval->reinstallation_fee/1.1);
                            $refrenceVat = $refrenceVat+($rval->reinstallation_fee/11);
                            $refrenceAmount = $refrenceAmount+($rval->reinstallation_fee);
                            $pdf->Text($changeX, 118.3, '$'.amountFormat2($rval->reinstallation_fee/1.1));
                        }
                        if($rval->entry_type=='RC')
                        {
                            // Reconnect
                            $refrenceFee = $refrenceFee+($rval->reconnect_fee/1.1); 
                            $refrenceVat = $refrenceVat+($rval->reconnect_fee/11);
                            $refrenceAmount = $refrenceAmount+($rval->reconnect_fee);
                            $reconnectX = ($rval->reconnect_fee/1.1) > 0 ? 174 : 175.7;
                            $pdf->Text($reconnectX, 125.5, '$'.amountFormat2($rval->reconnect_fee/1.1));
                        }
                    }                        
                }                
                // Other Fee 
                $pdf->Text($otherX, 131.9, '$'.amountFormat2($iArr->others_fee));
                // Exchange Rate 
                $pdf->Text($exchangeX, 160.4, number_format($exchangeRateArr->rate, 0, '.',','));
                // Discount 
                //$pdf->Text(176, 132, '$'.amountFormat2($iArr->discount));
                // Sub Total 
                $pdf->Text($subX, 138.8, '$'.amountFormat2($iArr->balance+$iArr->others_fee+$refrenceFee));
                // VAT Amount 
                $pdf->Text($vatX, 145.5, '$'.amountFormat2($iArr->vat_amount+$refrenceVat));
                $pdf->SetFont('times','B',11);
                // Grand Amount 
                $pdf->Text($grandX, 152.8, '$'.amountFormat2($iArr->total_amount+$refrenceAmount));
                // Grand Amount in KHR 
                $pdf->Text($exchangeGrandX, 168.2, number_format($exchangeRateArr->rate*($iArr->total_amount+$refrenceAmount)), 2, '.',',');   
                $pdf->Rect(12.2, 207, 3.2, 3.5, 'DF');
                // Plan Name 
                $pdf->Text(40, 218.7, planDetailsByPlanID($iArr->plan_id)['plan_name']);                
                $pdf->SetFont('times','B',10);
                // All Day Speed
                $pdf->Text(52, 224, planDetailsByPlanID($iArr->plan_id)['speed'].' '.planDetailsByPlanID($iArr->plan_id)['speed_unit']);
                // Day Time Speed
                $pdf->Text(90, 224, planDetailsByPlanID($iArr->plan_id)['speed'].' '.planDetailsByPlanID($iArr->plan_id)['speed_unit']);
                // Night Time Speed
                $pdf->Text(130, 224, planDetailsByPlanID($iArr->plan_id)['speed'].' '.planDetailsByPlanID($iArr->plan_id)['speed_unit']);
                // All Day Data Usage
                $pdf->Text(52, 229.8, LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($iArr->plan_id)['data_usage']));
                // Day Time Data Usage
                $pdf->Text(90, 229.8, LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($iArr->plan_id)['data_usage']));
                // Night Time Data Usage
                $pdf->Text(130, 229.8, LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($iArr->plan_id)['data_usage']));
                // Monthly Fee
                $pdf->Text(179, 222.4, '$'.amountFormat2($iArr->monthly_fee));
                // Reg Date 
                $pdf->Text(179, 230.2, pdfDateFormat(internetRegisterDateByCustID($iArr->customer_id)));
                $pdf->Rect(12, 245.8, 3.2, 3.5, 'DF');
                //Barcode
                $code = invoiceName($iArr->invoice_number, $iArr->monthly_invoice_date);
                $pdf->Code128(145,245.8,$code,55,10);
                $pdf->SetXY(143.6,256);
                $pdf->Write(5,$code);
                //$pdf->Output(); die;
                $pdfName= $code.'.pdf';
                //Set Title
                $pdf->SetTitle(html_entity_decode($pdfName,ENT_COMPAT,'UTF-8'), true);
                //Set Author Name
                $pdf->SetAuthor(html_entity_decode(userNameByUserID($request->session()->get('userID')),ENT_COMPAT,'UTF-8'), true);
                //$pdf->Output(); die; 
                //Compress Pdf                                  
                $pdf->SetCompression(true);   
                //Download Monthly Invoices                                  
                $pdf->Output('F',$pdfName);     
                $pdf->Close();  
                
                $content = file_get_contents(asset($pdfName));
                $conn_id = ftp_connect(env('FTP_SERVER_HOST'));
                $login = ftp_login($conn_id, env('FTP_SERVER_USERNAME'), env('FTP_SERVER_PASSWORD'));
                ftp_pasv($conn_id, true);
                // create
                $tmp = fopen(tempnam(sys_get_temp_dir(), $pdfName), "w+");
                fwrite($tmp, $content);
                rewind($tmp);
                // upload
                $upload = ftp_fput($conn_id, 'ISP_Invoice/'.$pdfName, $tmp, FTP_ASCII);//Test
                // close
                ftp_close($conn_id);

                if (@file_exists($pdfName)) {
                    unlink($pdfName);
                }
            }
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg',$monthYear.' invoices has been sent to FTP server successfully!');
            return redirect()->route('internet-monthly-invoice',['monthlyYear'=>$monthYear]); 
        }else{
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('internet-monthly-invoice');
        }
    }

    /**
     * Show Internet Transaction History Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function view_internet_transaction(Request $request)
    {        
        $user = new User;
        $ihistory = new InternetHistory;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('internet-history');
        }
        
        $id = $request->segment('2');
        $ihistoryArr = $ihistory->where('id', $id)->get()->first();

        if(!is_object($ihistoryArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('internet-history');
        }

        return view('users.idc.internet.view')
                    ->with(['ihistoryArr'=>$ihistoryArr,'userArr'=>$userArr]);
    }


    /**
     * Export Active & Suspended Subscribers.
     *
     * @param  Request  $request
     * @return Response
     */
    public function all_active_subscribers(Request $request)
    {        
        $user = new User;    

        $userArr= $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        return Excel::download(new ActiveInternetSubsWithSuspendExport(), 'Suspend-And-Active-Internet-Subscribers.xlsx');
    }

    /**
    * Show Internet Advance Payment Page.
    *
    * @param  Request  $request
    * @return View
    */
    public function advance_payment(Request $request)
    {            
        $user = new User;
        $unit_address = new UnitAddress;
        $payment = new ServiceAdvancePayment;

        $addressID ='';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS');
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');      
        
        if($request->input('addressID')){
            $addressID = $request->input('addressID');
            $advancePaymentArr = $payment->select('id','address_id','customer_id','start_date','end_date','period','monthly_fee','total_amount','paid_date','remark')->where('service_type', 'IN')->when($addressID, function($query) use ($addressID) {
                return $query->where('address_id', '=', $addressID); })->orderByDesc('id')->paginate($records)->withQueryString();           
        }else{
            $advancePaymentArr = $payment->select('id','address_id','customer_id','start_date','end_date','period','monthly_fee','total_amount','paid_date','remark')->where('service_type', 'IN')->orderByDesc('id')->paginate($records)->withQueryString();
        }
           
        return view('users.idc.internet.advance_payment')
                    ->with(['userArr'=>$userArr, 'unitsAddressArr'=>$unitsGroupAddressArr, 'addressID'=>$addressID, 'records'=>$records, 'advancePaymentArr'=>$advancePaymentArr]);
    }

    /**
    * Add Internet Advance Payment.
    *
    * @param  Request  $request
    * @return View
    */
    public function add_advance_payment(Request $request)
    {        
        $user = new User;
        $ihistory = new InternetHistory;

        $customerID = $request->segment('2');
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        $ihistoryArr = $ihistory->select('id','customer_id', 'address_id','start_date_time','monthly_fee')->where('customer_id', $customerID)->orderByDesc('start_date_time')->get()->first();
        
        if(!is_object($ihistoryArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('internet-subscribers');
        }

        return view('users.idc.internet.add_advance_payment')
                ->with(['userArr'=>$userArr, 'ihistoryArr'=>$ihistoryArr]);
    }

    /**
     * Validate & store Internet Advance Payment.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_advance_payment(Request $request)
    {      
        $rules = array(
         'from_date'=> 'required|date|date_format:d-m-Y',
         'period'=> 'required|numeric|between:1,12',
         // Payment Details
         'total_amount'=> 'required|numeric|between:0.00,999999.99',
         'payment_mode'=> 'required|in:CA,BA,CH,OT',
         'payment_description'=> in_array($request->input('payment_mode'),['BA','CH','OT']) ? 'required|string' : '',
         'paid'=> 'required|in:Y,N',
         'paid_date'=> 'required|date|date_format:d-m-Y',
         // Remark 
         'remark'=> !empty($request->input('remark')) ? 'required' : ''
        );
        $validator = Validator::make($request->all(), $rules);
     
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');        
            return redirect()->route('add-internet-advance-payment',['id'=>$request->input('customer_id')])
                        ->withErrors($validator) 
                            ->withInput();        
        }
        else
        {   
            $logs = new Logs;
            $payment = new ServiceAdvancePayment;

            $payment->address_id = $request->input('address_id');
            $payment->customer_id = $request->input('customer_id');
            $payment->service_type = 'IN'; //Internet
            $payment->start_date = dataBaseFormat($request->input('from_date'));
            $payment->end_date = internetSubscribeEndDate(dataBaseFormat($request->input('from_date')), ($request->input('period')-1));
            $payment->period = $request->input('period');
            $payment->monthly_fee = amountFormat2($request->input('monthly_fee'));
            $payment->total_amount = amountFormat2($request->input('total_amount'));
            $payment->payment_mode = $request->input('payment_mode');
            $payment->payment_description = in_array($request->input('payment_mode'),['BA','CH','OT']) ? $request->input('payment_description') : NULL;
            $payment->payment_by = 'CP';
            $payment->paid = $request->input('paid');
            $payment->paid_date = ($request->input('paid')=="Y") ? dataBaseFormat($request->input('paid_date')): NULL;
            $payment->remark = $request->input('remark');
            $payment->user_id = $request->session()->get('userID');
            $flag = $payment->save();
                    
            if ($flag) {
                // Entry in logs table for internet advance payment
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Internet advance payment has done reference id [".$payment->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('internet-advance-payment');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('add-internet-advance-payment',['id'=>$request->input('customer_id')])
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
     * Show USD to KHR Exchange Rate Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function exchange_rate(Request $request)
    {        
        $user = new User;
        $exrate = new ExchangeRate;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('dashboard');
        }

        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS');
        $exchangeRateArr = $exrate->orderByDesc('monthly_date')->paginate($records)->withQueryString();
        
        return view('users.idc.internet.exchange_rate')
                    ->with(['exchangeRateArr'=>$exchangeRateArr, 'userArr'=>$userArr, 'records'=>$records]);
    }

    /**
     * Show USD to KHR Exchange Rate View Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function view_exchange_rate(Request $request)
    {        
        $user = new User;
        $exrate = new ExchangeRate;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('dashboard');
        }

        $id = $request->segment(2);
        $exchangeRateArr = $exrate->where('id', $id)->first();

        if(!is_object($exchangeRateArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('exchange-rate');
        }

        return view('users.idc.internet.view_exchange_rate')
                    ->with(['exchangeRateArr'=>$exchangeRateArr, 'userArr'=>$userArr]);
    }

    /**
     * Show USD to KHR Exchange Rate Add Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function add_exchange_rate(Request $request)
    {        
        $user = new User;
        
        $userArr= $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('exchange-rate');
        }

        return view('users.idc.internet.add_exchange_rate')
                    ->with(['userArr'=>$userArr]);
    }

    /**
     * Validate & Store USD to KHR Exchange Rate.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_exchange_rate(Request $request)
    {
        $rules = array(
         'from_currency' => 'required|in:USD',
         'to_currency' => 'required|in:KHR',
         'rate' => 'required|numeric|between:0.01,999999.99',
         'exchange_month_year' => 'required|date|date_format:Y-m-d|unique:exchange_rate,monthly_date'
        ); 
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');            
            return redirect()->route('add-exchange-rate')
                        ->withErrors($validator) 
                            ->withInput();
        } 
        else
        {   
            if(internetEndMonthlyPaymentDate(currentMonthYear()) != dataBaseFormat($request->input('exchange_month_year'))){
                // Already Generated
                Session::flash('color','warning');
                Session::flash('icon','fa fa-exclamation-triangle');
                Session::flash('msg','Exchange rate can not save before or after '.datePickerFormat(internetEndMonthlyPaymentDate(currentMonthYear())).'.');
                return redirect()->route('add-exchange-rate')->withInput();
            }

            $logs = new Logs;
            $exrate = new ExchangeRate;
            
            $exrate->from_currency = 'USD';
            $exrate->to_currency = 'KHR';
            $exrate->rate = $request->input('rate');                
            $exrate->monthly_date = dataBaseFormat($request->input('exchange_month_year'));
            $exrate->created_by = $request->session()->get('userID');
            $flag = $exrate->save();                   
                    
            if ($flag) {
                // Entry in logs table
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Exchange rate has added reference id [".$exrate->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
                return redirect()->route('generate-monthly-payment-invoice',['invoice_month_year'=>monthYear($request->input('exchange_month_year'))]);
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('add-exchange-rate')
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
     * Show USD to KHR Exchange Rate Edit Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function edit_exchange_rate(Request $request)
    {   
        $user = new User;
        $exrate = new ExchangeRate;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('exchange-rate');
        }

        $id = $request->segment(2);
        $exchangeRateArr = $exrate->where('id', $id)->first();

        return view('users.idc.internet.edit_exchange_rate')
                    ->with(['exchangeRateArr'=>$exchangeRateArr, 'userArr'=>$userArr]);
    }

    /**
     * Validate & Update USD to KHR Exchange Rate.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_exchange_rate(Request $request)
    {        
        $rules = array(
         'from_currency' => 'required|in:USD',
         'to_currency' => 'required|in:KHR',
         'rate' => 'required|numeric|between:0.01,99999.99'
        ); 
        $validator = Validator::make($request->all(), $rules);
     
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');          
            return redirect()->route('edit-exchange-rate',['id'=>$request->post('id')])
                        ->withErrors($validator) 
                            ->withInput();
        } else {   
            // saving exchange rate in database
            $logs = new Logs;
            $exrate = new ExchangeRate;
            
            $flag = $exrate->where('id',$request->post('id'))->
                            update([
                                'from_currency' => 'USD',
                                'to_currency' => 'KHR',
                                'rate' => $request->input('rate'),
                                'updated_by' => $request->session()->get('userID')
                            ]);
                
            if ($flag) {
                // Entry in logs table
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Exchange rate has updated reference id [".$request->post('id')."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
                return redirect()->route('exchange-rate');
            } else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
                return redirect()->route('edit-exchange-rate',['id'=>$request->post('id')])
                            ->withErrors($validator) 
                                ->withInput();
            }
        }
    }

    /**
     * Destroy USD to KHR Exchange Rate.
     *
     * @param  Request  $request
     * @return Response
     */
    public function destroy_exchange_rate(Request $request)
    {
        $logs = new Logs;
        $user = new User;
        $exrate = new ExchangeRate;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->delete)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Delete permission not granted!');
            return redirect()->route('exchange-rate');
        }

        $id = $request->segment(2);
        $flag = $exrate->where('id', $id)->delete();

        if ($flag) {
             // Entry in logs table
             $logs->user_id = $request->session()->get('userID');
             $logs->logs_area = logsArea("Deleted exchange rate reference id [".$id."].");
             $logs->ip_address = $request->ip();
             $logs->save();

            // database deletion was successful, send back to listing form
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Record has been deleted successfully!');            
            return redirect()->route('exchange-rate');
        } else {
            // database deletion was not successful, send back to listing form
            Session::flash('color','danger');
            Session::flash('icon','fa fa-ban');
            Session::flash('msg','Record is not deleted, please try again!');
            return redirect()->route('exchange-rate');
        }
    }

    /**
     * Show Internet Report Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function internet_report(Request $request)
    {        
        $user = new User;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        $year = '';
        $quarter = '';
        $month = '';
        $iReportArr = array();

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

        $iReportArr= [
            'A101'=>[
                        'NR'=>totalCustomerCountByBuildingName('A101','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingName('A101','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingName('A101','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingName('A101','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingName('A101','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingName('A101','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingName('A101','MP',$startDate,$endDate)
                    ],
            'A102'=>[
                        'NR'=>totalCustomerCountByBuildingName('A102','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingName('A102','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingName('A102','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingName('A102','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingName('A102','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingName('A102','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingName('A102','MP',$startDate,$endDate)
                    ],
            'A103'=>[
                        'NR'=>totalCustomerCountByBuildingName('A103','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingName('A103','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingName('A103','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingName('A103','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingName('A103','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingName('A103','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingName('A103','MP',$startDate,$endDate)            
                    ],
            'A104'=>[
                        'NR'=>totalCustomerCountByBuildingName('A104','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingName('A104','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingName('A104','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingName('A104','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingName('A104','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingName('A104','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingName('A104','MP',$startDate,$endDate)
                    ],
            'A105'=>[
                        'NR'=>totalCustomerCountByBuildingName('A105','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingName('A105','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingName('A105','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingName('A105','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingName('A105','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingName('A105','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingName('A105','MP',$startDate,$endDate)
                    ],
            'A106'=>[
                        'NR'=>totalCustomerCountByBuildingName('A106','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingName('A106','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingName('A106','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingName('A106','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingName('A106','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingName('A106','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingName('A106','MP',$startDate,$endDate)
                    ],
            'A107'=>[
                        'NR'=>totalCustomerCountByBuildingName('A107','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingName('A107','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingName('A107','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingName('A107','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingName('A107','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingName('A107','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingName('A107','MP',$startDate,$endDate)
                    ],
            'A108'=>[
                        'NR'=>totalCustomerCountByBuildingName('A108','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingName('A108','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingName('A108','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingName('A108','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingName('A108','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingName('A108','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingName('A108','MP',$startDate,$endDate)
                    ],
            'Town House'=>[
                        'NR'=>totalCustomerCountByBuildingType('R1','T','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingType('R1','T','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingType('R1','T','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingType('R1','T','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingType('R1','T','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingType('R1','T','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingType('R1','T','MP',$startDate,$endDate)
                        ],
            'Villa (R1)'=>[
                        'NR'=>totalCustomerCountByBuildingType('R1','V','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingType('R1','V','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingType('R1','V','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingType('R1','V','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingType('R1','V','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingType('R1','V','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingType('R1','V','MP',$startDate,$endDate)
                        ],
            'Retail Shops'=>[
                        'NR'=>totalCustomerCountByBuildingType('R1','S','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingType('R1','S','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingType('R1','S','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingType('R1','S','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingType('R1','S','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingType('R1','S','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingType('R1','S','MP',$startDate,$endDate)
                        ],
            'Secret Garden (R2)'=>[
                        'NR'=>totalCustomerCountByBuildingType('R2','V','NR',$startDate,$endDate),
                        'CP'=>totalCustomerCountByBuildingType('R2','V','CP',$startDate,$endDate),
                        'CL'=>totalCustomerCountByBuildingType('R2','V','CL',$startDate,$endDate),
                        'SS'=>totalCustomerCountByBuildingType('R2','V','SS',$startDate,$endDate),
                        'RC'=>totalCustomerCountByBuildingType('R2','V','RC',$startDate,$endDate),
                        'TS'=>totalCustomerCountByBuildingType('R2','V','TS',$startDate,$endDate),
                        'MP'=>totalCustomerCountByBuildingType('R2','V','MP',$startDate,$endDate)
                        ]
        ];
      
        return view('users.idc.internet.report')
                    ->with(['userArr'=>$userArr, 'reportArr'=>$iReportArr, 'type'=>$type, 'year'=>$year,'quarter'=>$quarter,'month'=>$month]);
    }
    
    /**
     * Show MPTC Report Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function mptc_report(Request $request)
    {        
        $user = new User;
        $ihistory = new InternetHistory;
        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('internet-subscribers');
        }

        $year = !empty($request->input('year')) ? $request->input('year') : currentYear();
        $quarter = !empty($request->input('quarter')) ? $request->input('quarter') : currentQuarter();
        $quarterArr = quaterArrByNumber($quarter);
        $mReportArr= [];
        
        foreach($quarterArr as $qkey=> $qval)
        {
            $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($qval.'-'.$year));
            $endDate = internetEndMonthlyPaymentDate($qval.'-'.$year);
            $mReportArr[$qkey]['invoice_number'] = getStartAndEndInvoiceNumberByMonth($startDate, $endDate);
            $mReportArr[$qkey]['invoice_date'] = datePickerFormat2($year.'-'.$qval.'-'.env('MONTHLY_INVOICE_DATE'));
            $mReportArr[$qkey]['total_amount'] = amountFormat2(getInvoiceAmountByMonth($startDate,$endDate)+getInstallationAmountByMonth($startDate, $endDate)+getServiceAmountByMonth($startDate, $endDate));
        }
        
        return view('users.idc.internet.mptc_report')
                    ->with(['userArr'=>$userArr, 'mReportArr'=>$mReportArr, 'year'=>$year,'quarter'=>$quarter]);
    }

    /**
     * MPTC Quarterly Income Statement.
     *
     * @param  Request  $request
     * @return Response
     */
    public function mptc_income_statement(Request $request)
    {        
        $user = new User;        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-report');
        }

        $year = !empty($request->input('year')) ? $request->input('year') : currentYear();
        $quarter = !empty($request->input('quarter')) ? $request->input('quarter') : currentQuarter();
        
        return Excel::download(new MPTCQuarterlyIncomeStatementExport($quarter, $year), 'Q'.$quarter.'-'.$year.'.xlsx');
        
    }

    /**
     * MPTC Quarterly Service Declaration.
     *
     * @param  Request  $request
     * @return Response
     */
    public function mptc_service_declaration(Request $request)
    {        
        $user = new User;        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('internet-report');
        }

        $year = !empty($request->input('year')) ? $request->input('year') : currentYear();
        $quarter = !empty($request->input('quarter')) ? $request->input('quarter') : currentQuarter();
        
        return Excel::download(new MPTCQuarterlyServiceDeclarationExport($quarter, $year), 'Internet Service Declaration List in Quarter_'.$quarter.'-'.$year.'.xlsx');
        
    }
}