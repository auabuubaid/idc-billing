<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use App\Models\Customer;
use App\Models\CableTVHistory;
use App\Models\CableTVService;
use App\Models\InternetService;
use App\Models\InternetHistory;

class AjaxController extends Controller
{
    /**
     * Return Customer Details.
     *
     * @param  Request  $request
     * @return View
     */
    public function get_customer_details(Request $request)
    {   
        $customer = new Customer;
        
        $customerArr = $customer->select('id','address_id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        
        if(!empty($customerArr)){
            $customerArr["full_address"] = unitAddressByUnitID($customerArr->address_id);
            return response()->json($customerArr);
        }else{
            return null;
        }
    }

    /**
     * Return Customer Details With Current Internet Plan.
     *
     * @param  Request  $request
     * @return View
     */
    public function get_customer_with_internet_plan(Request $request)
    {
        $customer = new Customer;
        $ihistory = new InternetHistory;
    
        $customerArr = $customer->select('id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        
        if(!empty($customerArr)){
            $ihistoryArr = $ihistory->select('id','address_id','plan_id','start_date_time','entry_type','monthly_fee','deposit_fee','previous_deposit_fee','agreement_period')->where('customer_id',$request->post('custID'))->orderBy('start_date_time', 'desc')->first();
            $depositAmountArr = $ihistory->select('deposit_fee')->where('customer_id',$request->post('custID'))->where('entry_type', 'CP')->orderBy('start_date_time', 'desc')->get()->first();
            if(is_object($depositAmountArr)){
                $depositAmount = $depositAmountArr->deposit_fee;
            }else{
                $depositFeeArr = $ihistory->select('deposit_fee')->where('customer_id',$request->post('custID'))->where('entry_type', 'NR')->get()->first();
                $depositAmount = $depositFeeArr->deposit_fee;
            }     
            $customerArr["address_id"] = $ihistoryArr->address_id;
            $customerArr["full_address"] = unitAddressByUnitID($ihistoryArr->address_id);
            $customerArr["current_plan_id"] = $ihistoryArr->plan_id;
            $customerArr["current_plan_name"] = planDetailsByPlanID($ihistoryArr->plan_id)["plan_name"];
            $customerArr["current_speed"] = planDetailsByPlanID($ihistoryArr->plan_id)["speed"];
            $customerArr["current_upload_speed"] = planDetailsByPlanID($ihistoryArr->plan_id)["upload_speed"];
            $customerArr["current_deposit_fee"] = $depositAmount;
            $customerArr["previous_deposit_fee"] = $depositAmount;
            $customerArr["current_monthly_fee"] = $ihistoryArr->monthly_fee;
            $customerArr["plan_deposit_fee"] = planDetailsByPlanID($ihistoryArr->plan_id)["deposit_fee"];
            $customerArr["current_agreement_period"] = $ihistoryArr->agreement_period;
            $customerArr["last_entry_date"] = datePickerFormat($ihistoryArr->start_date_time);
            $customerArr["refrence_id"] = $ihistoryArr->id;
            $customerArr["entry_type"] = $ihistoryArr->entry_type;
            return response()->json($customerArr);
        }else{
            return null;
        }
    }

    /**
     * Return Customer With Suspend Internet Details.
     *
     * @param  Request  $request
     * @return View
     */
    public function get_customer_with_suspend_details(Request $request)
    {
        $customer = new Customer;
        $ihistory = new InternetHistory;

        $customerArr = $customer->select('id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        $due_amount = amountFormat2(0);
        $days_total = amountFormat2(0);        
        if(!empty($customerArr)){
            $notPaidArr = $ihistory->select('total_amount','monthly_invoice_date','monthly_fee')->where('customer_id',$request->post('custID'))->where('entry_type','MP')->where('paid','N')->orderByDesc('start_date_time')->get()->all();
            if(count($notPaidArr)>0){
                foreach($notPaidArr as $nval){
                        $due_amount = amountFormat2($due_amount+$nval->total_amount);
                } 
            }
            $latestIHistoryArr = $ihistory->select('id','address_id','plan_id','start_date_time','entry_type','monthly_fee','deposit_fee','refrence_id')->where('customer_id',$request->post('custID'))->orderByDesc('start_date_time')->get()->first();          
            $startDate = in_array($latestIHistoryArr->entry_type,["MP","NR","RC"]) ? dataBaseFormat($latestIHistoryArr->start_date_time) : dataBaseFormat(ihistoryDetailsByID($latestIHistoryArr->refrence_id)->start_date_time);
            $endDate = dataBaseFormat($request->post('suspendDateTime'));
            $currentNotPaidArr = $ihistory->select('id','entry_type','monthly_fee','start_date_time','end_date_time','monthly_invoice_date','paid')->whereDate('start_date_time','>=',$startDate)->whereDate('start_date_time','<=',$endDate)->where('customer_id',$request->post('custID'))->orderByDesc('start_date_time')->get()->all();
            $chargeOldMonth = false;

            if(count($currentNotPaidArr)>0){            
                foreach($currentNotPaidArr as $cnval){
                    //If customer is new register or reconnect
                    if((count($currentNotPaidArr)==1) &&  (in_array($cnval->entry_type,["NR","RC"]))){
                        $startDate = dateTimeStampForDataBase($cnval->start_date_time);
                        $endDate = dateTimeStampForDataBase($request->post('suspendDateTime'));
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = false;
                    }
                    elseif((count($currentNotPaidArr)>1) && (in_array($cnval->entry_type,["CP","CL"])) && (dayOFGivenDate(dataBaseFormat($cnval->start_date_time))=='26')){
                        $startDate = dateTimeStampForDataBase($cnval->start_date_time);
                        $endDate = empty($cnval->end_date_time) ? dateTimeStampForDataBase($request->post('suspendDateTime')): dateTimeStampForDataBase($cnval->end_date_time);
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = false;
                    }
                    //If customer changed plan, location or reconnect 
                    elseif((count($currentNotPaidArr)>1) && (in_array($cnval->entry_type,["CP","CL"]))){
                        $startDate = dateTimeStampForDataBase($cnval->start_date_time);
                        $oldPlanEndDate = previousDateTimeStampForGivenDate($cnval->start_date_time);
                        $endDate = empty($cnval->end_date_time) ? dateTimeStampForDataBase($request->post('suspendDateTime')): dateTimeStampForDataBase($cnval->end_date_time);
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = true;
                    }
                    //If suspend record occured 
                    elseif((count($currentNotPaidArr)>1) && (in_array($cnval->entry_type,["SS"]))){
                        $due_amount = amountFormat2($due_amount+0);
                        $chargeOldMonth = false;
                    }
                    // Charge by daliy basis using old plan 
                    elseif((count($currentNotPaidArr)>1) && (in_array($cnval->entry_type,["MP"])) && ($chargeOldMonth==true)){
                        $startDate = nextDateTimeStampForGivenDate($cnval->start_date_time);
                        $endDate = dateTimeStampForDataBase($oldPlanEndDate);
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = false;
                    }                    
                    //Charge by daily basis if customer come to terminate after 25 or 26 date
                    elseif((count($currentNotPaidArr)==1) &&  ($cnval->entry_type=="MP") && ($endDate>nextDateOFGivenDate($cnval->monthly_invoice_date))){
                        $startDate = nextDateTimeStampForGivenDate($cnval->start_date_time);
                        $endDate = dateTimeStampForDataBase($request->post('suspendDateTime'));
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = false;
                    }
                    //Not charge if customer come to terminate on 25 or 26 date
                    elseif(($cnval->entry_type=="MP") && (in_array(dayOFGivenDate($endDate),["25","26"]))){
                        $due_amount = amountFormat2($due_amount+0);
                        $chargeOldMonth = false;
                    }
                }    
            } 
            $latestIHistoryArr = $ihistory->select('id','address_id','plan_id','entry_type','monthly_fee','deposit_fee','start_date_time')->where('customer_id',$request->post('custID'))->orderBy('start_date_time', 'desc')->get()->first();
            $depositAmountArr = $ihistory->select('deposit_fee')->where('customer_id',$request->post('custID'))->where('entry_type', 'CP')->orderBy('start_date_time', 'desc')->get()->first();
            if(is_object($depositAmountArr)){
                $depositAmount = $depositAmountArr->deposit_fee;
            }else{
                $depositFeeArr = $ihistory->select('deposit_fee')->where('customer_id',$request->post('custID'))->where('entry_type', 'NR')->get()->first();
                $depositAmount = $depositFeeArr->deposit_fee;
            }            
            $customerArr["full_address"] = unitAddressByUnitID($latestIHistoryArr->address_id);
            $customerArr["address_id"] = $latestIHistoryArr->address_id;
            $customerArr["plan_id"] = $latestIHistoryArr->plan_id;
            $customerArr["plan_name"] = planDetailsByPlanID($latestIHistoryArr->plan_id)["plan_name"];
            $customerArr["speed"] = planDetailsByPlanID($latestIHistoryArr->plan_id)["speed"];
            $customerArr["upload_speed"] = planDetailsByPlanID($latestIHistoryArr->plan_id)["upload_speed"];
            $customerArr["deposit_fee"] = $depositAmount;
            $customerArr["monthly_fee"] = $latestIHistoryArr->monthly_fee;
            $customerArr["due_amount"] = $due_amount;
            $customerArr["last_entry_date"] = datePickerFormat($latestIHistoryArr->start_date_time);
            $customerArr["refrence_id"] = $latestIHistoryArr->id;
            $customerArr["entry_type"] = $latestIHistoryArr->entry_type; 
            return response()->json($customerArr);
        }else{
            return null;
        }
    }

    /**
     * Return Customer With Reconnect Internet Details.
     *
     * @param  Request  $request
     * @return View
     */
    public function get_customer_with_reconnect_details(Request $request)
    {
        $customer = new Customer;
        $ihistory = new InternetHistory;
        
        $customerArr= $customer->select('id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        $due_amount=amountFormat2(0);
        
        if(!empty($customerArr)){
            $notPaidArr = $ihistory->select('total_amount')->where('customer_id',$request->post('custID'))->where('entry_type','=','MP')->where('paid','N')->orderByDesc('start_date_time')->get()->all();
            if(count($notPaidArr)>0){
                foreach($notPaidArr as $pval){
                        $due_amount = amountFormat2($due_amount+$pval->total_amount);
                } 
            }

            $ihistoryArr = $ihistory->select('id','address_id','plan_id','entry_type','monthly_fee','deposit_fee','suspension_start_date','suspension_end_date','suspension_period')->where('customer_id',$request->post('custID'))
            ->where('entry_type','SS')->whereNull('suspension_end_date')->orderByDesc('start_date_time')->get()->first();
            $depositAmountArr = $ihistory->select('deposit_fee')->where('customer_id',$request->post('custID'))->where('entry_type', 'CP')->orderByDesc('start_date_time')->get()->first();
            if(is_object($depositAmountArr)){
                $depositAmount = $depositAmountArr->deposit_fee;
            }else{
                $depositFeeArr = $ihistory->select('deposit_fee')->where('customer_id',$request->post('custID'))->where('entry_type', 'NR')->get()->first();
                $depositAmount = $depositFeeArr->deposit_fee;
            }
            $customerArr["address_id"] = $ihistoryArr->address_id;
            $customerArr["full_address"] = unitAddressByUnitID($ihistoryArr->address_id);
            $customerArr["plan_id"] = $ihistoryArr->plan_id;
            $customerArr["plan_name"] = planDetailsByPlanID($ihistoryArr->plan_id)["plan_name"];
            $customerArr["speed"] = planDetailsByPlanID($ihistoryArr->plan_id)["speed"];
            $customerArr["upload_speed"] = planDetailsByPlanID($ihistoryArr->plan_id)["upload_speed"];
            $customerArr["deposit_fee"] = $depositAmount;
            $customerArr["monthly_fee"] = $ihistoryArr->monthly_fee;
            $customerArr["due_amount"] = $due_amount;
            $customerArr["suspend_start_date"] = datePickerFormat($ihistoryArr->suspension_start_date);
            $customerArr["suspend_end_date"] = datePickerFormat(previousDateOFGivenDate($request->input('reconnectDate')));
            $customerArr["suspend_period"] = daysBetweenTwoDates($ihistoryArr->suspension_start_date,previousDateOFGivenDate($request->input('reconnectDate')))+1; //+1 for count last day also
            $customerArr["refrence_id"] = $ihistoryArr->id;
            $customerArr["entry_type"] = $ihistoryArr->entry_type;
            return response()->json($customerArr);
        }else{
            return null;
        }
    }

    /**
     * Return Customer With Terminate Details.
     *
     * @param  Request  $request
     * @return View
     */
    public function get_customer_with_terminate_details(Request $request)
    {
        $customer = new Customer;
        $ihistory = new InternetHistory;
        
        $customerArr = $customer->select('id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        $due_amount = amountFormat2(0);
        $days_total = amountFormat2(0);
        
        if(!empty($customerArr)){
            $notPaidArr = $ihistory->select('total_amount','monthly_invoice_date','monthly_fee')->where('customer_id',$request->post('custID'))->where('entry_type','=','MP')->where('paid','N')->orderBy('start_date_time', 'desc')->get()->all();
            if(count($notPaidArr)>0){
                foreach($notPaidArr as $nval){
                        $due_amount = amountFormat2($due_amount+$nval->total_amount);
                } 
            }            
            $latestIHistoryArr = $ihistory->select('id','address_id','plan_id','start_date_time','entry_type','monthly_fee','deposit_fee','refrence_id')->where('customer_id',$request->post('custID'))->orderBy('start_date_time', 'desc')->get()->first();          
            $startDate = in_array($latestIHistoryArr->entry_type,["MP","NR","RC"]) ? dataBaseFormat($latestIHistoryArr->start_date_time) : dataBaseFormat(ihistoryDetailsByID($latestIHistoryArr->refrence_id)->start_date_time);
            $endDate = dataBaseFormat($request->post('terminateDateTime'));
            $currentNotPaidArr = $ihistory->select('id','entry_type','monthly_fee','start_date_time','end_date_time','monthly_invoice_date','paid')->whereDate('start_date_time','>=',$startDate)->whereDate('start_date_time','<=',$endDate)->where('customer_id',$request->post('custID'))->orderByDesc('start_date_time')->get();
            $chargeOldMonth = false;            
            if(count($currentNotPaidArr)>0){                      
                foreach($currentNotPaidArr as $cnval){
                    //If customer is new register or reconnect
                    if((count($currentNotPaidArr)==1) &&  (in_array($cnval->entry_type,["NR","RC"]))){
                        $startDate = dateTimeStampForDataBase($cnval->start_date_time);
                        $endDate = dateTimeStampForDataBase($request->post('terminateDateTime'));
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = false;
                    }
                    //If customer changed plan or location on 26 date
                    elseif((count($currentNotPaidArr)>1) && (in_array($cnval->entry_type,["CP","CL"])) && (dayOFGivenDate(dataBaseFormat($cnval->start_date_time))=='26')){
                        $startDate = dateTimeStampForDataBase($cnval->start_date_time);
                        $endDate = empty($cnval->end_date_time) ? dateTimeStampForDataBase($request->post('terminateDateTime')): dateTimeStampForDataBase($cnval->end_date_time);
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = false;
                    }
                    //If customer changed plan or location after 26 date
                    elseif((count($currentNotPaidArr)>1) && (in_array($cnval->entry_type,["CP","CL"]))){
                        $startDate = dateTimeStampForDataBase($cnval->start_date_time);
                        $endDate = empty($cnval->end_date_time) ? dateTimeStampForDataBase($request->post('terminateDateTime')): dateTimeStampForDataBase($cnval->end_date_time);
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $oldPlanEndDate = previousDateTimeStampForGivenDate($cnval->start_date_time);
                        $chargeOldMonth = true;
                    }
                    //If suspend record occured 
                    elseif((count($currentNotPaidArr)>1) && (in_array($cnval->entry_type,["SS"]))){
                        $due_amount = amountFormat2($due_amount+0);
                        $chargeOldMonth = false;
                    }
                    // Charge by daliy basis using old plan 
                    elseif((count($currentNotPaidArr)>1) && (in_array($cnval->entry_type,["MP"])) && ($chargeOldMonth==true)){
                        $startDate = nextDateTimeStampForGivenDate($cnval->start_date_time);
                        $endDate = dateTimeStampForDataBase($oldPlanEndDate);
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = false;
                    }                    
                    //Charge by daily basis if customer come to terminate after 25 date
                    elseif((count($currentNotPaidArr)==1) &&  ($cnval->entry_type=="MP") && (dataBaseFormat($request->post('terminateDateTime'))>nextDateOFGivenDate($cnval->monthly_invoice_date))){
                        $startDate = nextDateTimeStampForGivenDate($cnval->start_date_time);
                        $endDate = dateTimeStampForDataBase($request->post('terminateDateTime'));                        
                        $days_total = balanceVatTotalAmountByDays($startDate, $endDate, $cnval->monthly_fee)['total_amount'];
                        $due_amount = amountFormat2($due_amount+$days_total);
                        $chargeOldMonth = false;
                    }
                    //Not charge if customer come to terminate on 25 or 26 date
                    elseif((count($currentNotPaidArr)==1) && ($cnval->entry_type=="MP") && (in_array(dayOFGivenDate(dataBaseFormat($request->post('terminateDateTime'))),["25","26"]))){
                        $due_amount = amountFormat2($due_amount+0);
                        $chargeOldMonth = false;
                    }
                }    
            }
            
            $latestIHistoryArr = $ihistory->select('id','address_id','plan_id','start_date_time','entry_type','monthly_fee','deposit_fee')->where('customer_id',$request->post('custID'))->orderBy('start_date_time', 'desc')->get()->first();
            $depositAmountArr = $ihistory->select('deposit_fee')->where('customer_id',$request->post('custID'))->where('entry_type', 'CP')->orderBy('start_date_time', 'desc')->get()->first();
            if(is_object($depositAmountArr)){
                $depositAmount = $depositAmountArr->deposit_fee;
            }else{
                $depositFeeArr = $ihistory->select('deposit_fee')->where('customer_id',$request->post('custID'))->where('entry_type', 'NR')->get()->first();
                $depositAmount = $depositFeeArr->deposit_fee;
            }
            
            $customerArr["full_address"] = unitAddressByUnitID($latestIHistoryArr->address_id);
            $customerArr["address_id"] = $latestIHistoryArr->address_id;
            $customerArr["plan_id"] = $latestIHistoryArr->plan_id;
            $customerArr["plan_name"] = planDetailsByPlanID($latestIHistoryArr->plan_id)["plan_name"];
            $customerArr["speed"] = planDetailsByPlanID($latestIHistoryArr->plan_id)["speed"];
            $customerArr["upload_speed"] = planDetailsByPlanID($latestIHistoryArr->plan_id)["upload_speed"];
            $customerArr["deposit_fee"] = $depositAmount;
            $customerArr["monthly_fee"] = $latestIHistoryArr->monthly_fee;
            $customerArr["due_amount"] = $due_amount;
            $customerArr["last_entry_date"] = datePickerFormat($latestIHistoryArr->start_date_time);
            $customerArr["refrence_id"] = $latestIHistoryArr->id;
            $customerArr["entry_type"] = $latestIHistoryArr->entry_type;
            return response()->json($customerArr);
        }else{
            return null;
        }
    }

    /**
     * Return Internet Plan Details.
     *
     * @param  Request  $request
     * @return Response
     */
    public function internet_plan_details(Request $request)
    {
        $internet = new InternetService;
        
        $planArr = $internet->where('id', $request->post('planID'))->first();
        
        if(!empty($planArr)){
            return response()->json($planArr);
        }else{
            return response()->json();
        }
    }


    /**
     * Return Customer Details With CableTV Plan.
     *
     * @param  Request  $request
     * @return View
     */
    public function customer_with_cabletv_plan(Request $request)
    {
        $customer = new Customer;
        $ctvhistory = new CableTVHistory;

        $customerArr = $customer->select('id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        
        if(!empty($customerArr)){
            $ctvhistoryArr = $ctvhistory->select('id','address_id','plan_id','entry_type','start_date_time','monthly_fee','period')->where('customer_id',$request->post('custID'))->orderByDesc('start_date_time')->first();
            $customerArr["address_id"] = $ctvhistoryArr->address_id;
            $customerArr["full_address"] = unitAddressByUnitID($ctvhistoryArr->address_id);
            $customerArr["plan_id"] = $ctvhistoryArr->plan_id;
            $customerArr["plan_name"] = cableTVPlanNameByID($ctvhistoryArr->plan_id);
            $customerArr["monthly_fee"] = $ctvhistoryArr->monthly_fee;
            $customerArr["period"] = $ctvhistoryArr->period;
            $customerArr["refrence_id"] = $ctvhistoryArr->id;
            $customerArr["entry_type"] = $ctvhistoryArr->entry_type;
            $customerArr["last_entry_date"] = datePickerFormat($ctvhistoryArr->start_date_time);
            return response()->json($customerArr);
        }else{
            return null;
        }
    }

    /**
     * Return Customer With Suspend CableTV Details.
     *
     * @param  Request  $request
     * @return View
     */
    public function customer_with_cabletv_suspend_details(Request $request)
    {
        $customer = new Customer;
        $ctvhistory = new CableTVHistory;

        $customerArr = $customer->select('id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        
        $due_amount = amountFormat2(0);
        $days_total = amountFormat2(0);
        if(!empty($customerArr)){
            $notPaidArr = $ctvhistory->select('total_amount','monthly_invoice_date','monthly_fee')->where('customer_id',$request->post('custID'))->where('paid','N')->orderBy('start_date_time', 'desc')->get()->all();
            if(count($notPaidArr)>0){
                foreach($notPaidArr as $nval){
                        $due_amount = amountFormat2($due_amount+$nval->total_amount);
                } 
            }
            $latestCTVHistoryArr = $ctvhistory->select('id','entry_type','monthly_fee','start_date_time','monthly_invoice_date')->where('customer_id',$request->post('custID'))->orderBy('start_date_time', 'desc')->get()->first();
            $startDate = ($latestCTVHistoryArr->entry_type=="MP") ? nextDateOFGivenDate($latestCTVHistoryArr->monthly_invoice_date) : dataBaseFormat($latestCTVHistoryArr->start_date_time);
            $compareStartDate = dataBaseFormat($latestCTVHistoryArr->start_date_time);
            $monthly_fee = $latestCTVHistoryArr->monthly_fee;
            
            if($compareStartDate < dataBaseFormat($request->input('suspendDateTime'))){
                $currentMonthNotPaidArr = $ctvhistory->select('id','entry_type','monthly_fee','start_date_time','end_date_time','paid')->where('customer_id',$request->post('custID'))->where('entry_type','!=','MP')
                ->whereDate('start_date_time','>=',$startDate)->whereDate('start_date_time','<=',dataBaseFormat($request->input('suspendDateTime')))->orderBy('start_date_time', 'desc')->get()->all();
                
                if(count($currentMonthNotPaidArr)>0){
                    foreach($currentMonthNotPaidArr as $cval){
                        $startDate = $cval->start_date_time;
                        $endDate = empty($cval->end_date_time) ? dateTimeStampForDataBase($request->post('suspendDateTime')): $cval->end_date_time;
                        $days_total= cableTVMonthlyFee($startDate,$cval->monthly_fee);
                        $due_amount = amountFormat2($due_amount+$days_total);
                    } 
                }else{
                    $startDate = dateTimeStampForDataBase($startDate);
                    $endDate = dateTimeStampForDataBase($request->post('suspendDateTime'));
                    $days_total= cableTVMonthlyFee($startDate,$monthly_fee);
                    $due_amount = amountFormat2($due_amount+$days_total);
                }
            }

            $latestCTVHistoryArr = $ctvhistory->select('id','address_id','plan_id','entry_type','monthly_fee','start_date_time')->where('customer_id',$request->post('custID'))->orderBy('start_date_time', 'desc')->get()->first();
            $customerArr["full_address"] = unitAddressByUnitID($latestCTVHistoryArr->address_id);
            $customerArr["address_id"] = $latestCTVHistoryArr->address_id;
            $customerArr["plan_id"] = $latestCTVHistoryArr->plan_id;
            $customerArr["plan_name"] = cableTVPlanNameByID($latestCTVHistoryArr->plan_id);
            $customerArr["monthly_fee"] = amountFormat2($latestCTVHistoryArr->monthly_fee);
            $customerArr["due_amount"] = amountFormat2($due_amount);
            $customerArr["refrence_id"] = $latestCTVHistoryArr->id;
            $customerArr["entry_type"] = $latestCTVHistoryArr->entry_type;
            $customerArr["last_entry_date"] = datePickerFormat($compareStartDate);
            return response()->json($customerArr);            
        }else{
            return null;
        }
    }

    /**
     * Return Customer With Reconnect CableTV Details.
     *
     * @param  Request  $request
     * @return View
     */
    public function cabletv_reconnect_details(Request $request)
    {
        $customer = new Customer;
        $ctvhistory = new CableTVHistory;

        $customerArr = $customer->select('id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        $due_amount=amountFormat2(0);
        if(!empty($customerArr)){
            $notPaidArr = $ctvhistory->select('total_amount')->where('customer_id',$request->post('custID'))->where('entry_type','MP')->where('paid','N')->orderBy('start_date_time', 'desc')->get()->all();
            if(count($notPaidArr)>0){
                foreach($notPaidArr as $pval){
                        $due_amount = amountFormat2($due_amount+$pval->total_amount);
                } 
            }

            $ctvhistoryArr = $ctvhistory->select('id','address_id','plan_id','entry_type','monthly_fee','terminate_date')->where('customer_id',$request->post('custID'))->where('entry_type','TS')->orderByDesc('start_date_time')->first();
            $customerArr["address_id"] = $ctvhistoryArr->address_id;
            $customerArr["full_address"] = unitAddressByUnitID($ctvhistoryArr->address_id);
            $customerArr["plan_id"] = $ctvhistoryArr->plan_id;
            $customerArr["plan_name"] = cableTVPlanNameByID($ctvhistoryArr->plan_id);
            $customerArr["monthly_fee"] = amountFormat2($ctvhistoryArr->monthly_fee);
            $customerArr["due_amount"] = amountFormat2($due_amount);
            $customerArr["terminate_start_date"] = datePickerFormat($ctvhistoryArr->terminate_date);
            $customerArr["terminate_end_date"] = datePickerFormat(previousDateOFGivenDate($request->input('reconnectDate')));
            $customerArr["terminate_period"] = monthsBetweenTwoDates($ctvhistoryArr->suspension_start_date,previousDateOFGivenDate($request->input('reconnectDate')));
            $customerArr["refrence_id"] = $ctvhistoryArr->id;
            $customerArr["entry_type"] = $ctvhistoryArr->entry_type;
            return response()->json($customerArr);
        }else{
            return null;
        }
    }

    /**
     * Return Customer With CableTV Terminate Details.
     *
     * @param  Request  $request
     * @return View
     */
    public function cabletv_terminate_details(Request $request)
    {   
        $customer = new Customer;
        $ctvhistory = new CableTVHistory;

        $customerArr = $customer->select('id','type','name','email','mobile','shop_name','shop_email','shop_mobile','vat_no')->where('id', $request->post('custID'))->first();
        $due_amount = amountFormat2(0);
        if(!empty($customerArr)){
            $notPaidArr = $ctvhistory->select('total_amount','monthly_invoice_date','monthly_fee')->where('customer_id',$request->post('custID'))->where('entry_type','MP')->where('paid','N')->orderBy('start_date_time', 'desc')->get()->all();
            if(count($notPaidArr)>0){
                foreach($notPaidArr as $nval){
                        $due_amount = amountFormat2($due_amount+$nval->total_amount);
                } 
            }           
           
            $latestCTVHistoryArr = $ctvhistory->select('id','address_id','plan_id','entry_type','monthly_fee','start_date_time')->where('customer_id',$request->post('custID'))->orderBy('start_date_time', 'desc')->get()->first();
            $customerArr["full_address"] = unitAddressByUnitID($latestCTVHistoryArr->address_id);
            $customerArr["address_id"] = $latestCTVHistoryArr->address_id;
            $customerArr["plan_id"] = $latestCTVHistoryArr->plan_id;
            $customerArr["plan_name"] = cableTVPlanNameByID($latestCTVHistoryArr->plan_id);
            $customerArr["monthly_fee"] = amountFormat2($latestCTVHistoryArr->monthly_fee);
            $customerArr["due_amount"] = amountFormat2($due_amount);
            $customerArr["refund"] = amountFormat2(0);
            $customerArr["refrence_id"] = $latestCTVHistoryArr->id;
            $customerArr["entry_type"] = $latestCTVHistoryArr->entry_type;
            $customerArr["last_entry_date"] = datePickerFormat($latestCTVHistoryArr->start_date_time);
            return response()->json($customerArr);
        }else{
            return null;
        }
    }

    /**
     * Return CableTV Plan Details.
     *
     * @param  Request  $request
     * @return Response
     */
    public function cabletv_plan_details(Request $request)
    {
        $cabletv = new CableTVService;
     
        $planArr = $cabletv->select('id','plan_name','installation_fee','monthly_fee','per_tv_fee')->where('id', $request->post('planID'))->first();
     
        if(!empty($planArr)){
            return response()->json($planArr);
        }else{
            return response()->json();
        }
    }
}
