<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\Models\Logs;
use Illuminate\Http\Request;
use App\Models\CableTVHistory;
use App\Models\ServiceAdvancePayment;

class GenerateCableTVMonthlyInvoices extends Command
{     
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateCableTVInvoices:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate cable tv monthly invoices on 01st day at 08:00 AM of each month.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Request $request)
    {
        $ctvhistory = new CableTVHistory;
        $startDate = cableTVGenerateStartMonthlyPaymentDate(currentMonthYear()); 
        $endDate = cableTVGenerateEndMonthlyPaymentDate(currentMonthYear());
        $invoiceGeneratedDate = nextDateOFGivenDate($endDate); 
       
        $alreadyGeneratedArr= $ctvhistory->select('customer_id')->distinct()->where('entry_type','MP')->whereDate('monthly_invoice_date',$invoiceGeneratedDate)->get()->toArray();
        $monthlyCustomerArr =  DB::select("select distinct `customer_id` from `cabletv_history` where (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `cabletv_history` group by `customer_id`) and date(`start_date_time`) >= '".$startDate."' and date(`start_date_time`) <= '".$endDate."' and `entry_type` not in ('TS') and `plan_level` != 'DOWNGRADE' group by `customer_id` order by `start_date_time` desc");
        $finalCustomerIDs = collect($monthlyCustomerArr)->pluck('customer_id');        
        $customerMonthlyPaymentArr = $ctvhistory->select('id','address_id','customer_id','plan_id','entry_type','start_date_time','end_date_time','subscribe_start_date','subscribe_end_date','customer_mobile','monthly_fee','paid','paid_date')->whereIn('customer_id',$finalCustomerIDs)->whereDate('start_date_time','>=',dataBaseFormat($startDate))->whereDate('start_date_time','<=',dataBaseFormat($endDate))->orderByDesc('start_date_time')->get()->groupBy('customer_id')->toArray();
        
        if(count($alreadyGeneratedArr)==0)
        {       
            if(is_array($customerMonthlyPaymentArr) && count($customerMonthlyPaymentArr)>0)
            {
                $count = 1;              
                foreach($customerMonthlyPaymentArr as $ckey=>$cmval)
                {                
                    $logs = new Logs;
                    $ctvhistory = new CableTVHistory;
                    $total_amount = amountFormat2(0);   

                    foreach($cmval as $cmkey=>$ccmval)
                    {
                        if(count($cmval)>1 && in_array($ccmval['entry_type'],["NR","CL","RC","CH","TS"])){
                            
                            $total_amount = amountFormat2(0.00);
                            
                        }elseif(count($cmval)>1 && $ccmval['entry_type']=="MP") { 
                            
                            $total_amount = amountFormat2($ccmval['monthly_fee']);

                        }elseif(count($cmval)==1 && in_array($ccmval['entry_type'],["NR","MP"])){
                            
                            $total_amount = amountFormat2($ccmval['monthly_fee']);
                        }
                    }

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
                    $ctvhistory->user_id = '5'; //idc@worldcity.local
                    $flag = $ctvhistory->save();

                    if(in_array($cmval[0]['entry_type'],['MP','TS']))
                    {
                        $flag1 = true;
                    }
                    else
                    {
                        $flag1 = $ctvhistory->where('id',$cmval[0]['id'])->
                                        update([
                                            'end_date_time'=>previousDateTimeStampForGivenDate($invoiceGeneratedDate.' '.currentHourMinute()) 
                                        ]);
                    }                      
                    if($flag) 
                    {                    
                        // Entry in logs table
                        $logs->user_id = '5'; //idc@worldcity.local
                        $logs->logs_area = logsArea("CableTV monthly payment gereration has done reference id [".$ctvhistory->id."].");
                        $logs->ip_address = $request->ip();
                        $logs->save();
                    } 
                    else 
                    {
                        // Entry in logs table
                        $logs->user_id = '5'; //idc@worldcity.local
                        $logs->logs_area = logsArea("CableTV monthly payment gereration has problem reference id [".$ctvhistory->id."].");
                        $logs->ip_address = $request->ip();
                        $logs->save();
                    }
                    $count++;
                }
            }
            $this->info('Generate cable tv monthly invoices on 01st day at 08:00 AM of each month');
        }
        else
        {
            $this->info('Cable tv monthly invoices already have been generated.');
        }  
    }
}