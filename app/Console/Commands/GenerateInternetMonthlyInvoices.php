<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\Models\Logs;
use Illuminate\Http\Request;
use App\Models\InternetHistory;
use App\Models\ServiceAdvancePayment;

class GenerateInternetMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generateInternetInvoices:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate internet monthly invoices on 25th day at 04:30 PM of each month.';

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
        $ihistory = new InternetHistory;
        $previousDate = internetStartMonthlyPaymentDate(currentMonthYear());
        $currentDate = internetEndMonthlyPaymentDate(currentMonthYear());
        $invoiceGeneratedDate = internetEndMonthlyPaymentDate(currentMonthYear());
       
        $alreadyGeneratedArr = $ihistory->select('customer_id')->distinct()->where('entry_type','MP')->whereDate('monthly_invoice_date',$currentDate)->get()->toArray();
        $monthlyCustomerArr = DB::select("select distinct `customer_id` from `internet_history` join `customers` on `internet_history`.`customer_id` = `customers`.`id` where date(`start_date_time`) >='".$previousDate."' and date(`start_date_time`) <='".$currentDate."' and `plan_remark` is null and `customer_id` not in (select `customer_id` from `internet_history` where date(`start_date_time`)='".$previousDate."' and `entry_type` in ('TS', 'SS'))  group by `customer_id` order by `customers`.`internet_id` asc");
        $finalCustomerIDs = collect($monthlyCustomerArr)->pluck('customer_id');        
        $customerMonthlyPaymentArr = $ihistory->select('id','address_id','customer_id','plan_id','entry_type','start_date_time','end_date_time','customer_mobile','monthly_fee','deposit_fee','paid','paid_date','payment_by')->whereIn('customer_id',$finalCustomerIDs)->whereDate('start_date_time', '>=', dataBaseFormat($previousDate))->whereDate('start_date_time', '<=', dataBaseFormat($currentDate))->orderByDesc('start_date_time')->get()->groupBy('customer_id')->toArray();
        
        if(count($alreadyGeneratedArr)==0)
        {       
            if(is_array($customerMonthlyPaymentArr) && count($customerMonthlyPaymentArr)>0)
            {
                $count = 1;
                foreach($customerMonthlyPaymentArr as $ckey=>$cmval)
                {   
                    $logs = new Logs;
                    $ihistory = new InternetHistory;

                    $no_of_days = 0;
                    $balance = amountFormat2(0);
                    $vat_amount = amountFormat2(0);
                    $total_amount = amountFormat2(0);                    
                    $payPreviousAmount = false;

                    foreach($cmval as $cmkey=>$ccmval)
                    {
                        //If New Connection or Reconnect on 25 date of any month don't charge any amount have to include in monthly list and amount will be 0.
                        if((count($cmval)==1) && (in_array($ccmval['entry_type'],["NR","RC"])) && (dataBaseFormat($ccmval['start_date_time'])==dataBaseFormat($invoiceGeneratedDate)))
                        {   
                            $balance = amountFormat2($balance+0);
                            $vat_amount = amountFormat2($vat_amount+0);
                            $total_amount = amountFormat2($total_amount+0);
                            $no_of_days = 0;
                            $payPreviousAmount = false;
                        }
                        //If New Connection or Reconnect after 25 date of any month have to pay on daily basis.
                        elseif((count($cmval)==1) && (in_array($ccmval['entry_type'],["NR","RC"])) && (dataBaseFormat($ccmval['start_date_time'])>=nextDateOFGivenDate($previousDate)))
                        {   
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
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["SS","TS"])) && (dataBaseFormat($ccmval['start_date_time'])==dataBaseFormat($invoiceGeneratedDate)))
                        {   
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
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["SS","TS"])) && (dayOFGivenDate(dataBaseFormat($ccmval['start_date_time']))=='26') && (dataBaseFormat($ccmval['start_date_time'])<dataBaseFormat($invoiceGeneratedDate))) 
                        {           
                            $startDate = dateTimeStampForDataBase($ccmval['start_date_time']); 
                            $endDate =  dateTimeStampForDataBase($invoiceGeneratedDate);
                            $no_of_days = ($no_of_days+daysBetweenTwoDatesForInternet($startDate,$endDate));
                            $balance = amountFormat2($balance+0);
                            $vat_amount = amountFormat2($vat_amount+0);
                            $total_amount = amountFormat2($total_amount+0);
                            $payPreviousAmount = false;
                        }
                        //If Suspend after 25 date of any month charge 0 till Reconnect not happend.
                        elseif ((count($cmval) > 1) && (in_array($ccmval['entry_type'], ["SS"])) && (dataBaseFormat($ccmval['start_date_time']) > dataBaseFormat($previousDate)) && (dataBaseFormat($ccmval['start_date_time']) < dataBaseFormat($invoiceGeneratedDate))) 
                        {
                            $startDate = nextDateTimeStampForGivenDate($ccmval['start_date_time']);
                            $endDate =  empty($ccmval['end_date_time']) ?  dateTimeStampForDataBase($invoiceGeneratedDate) : dateTimeStampForDataBase($ccmval['end_date_time']);
                            $no_of_days = ($no_of_days + daysBetweenTwoDatesForInternet($startDate, $endDate));
                            $balance = amountFormat2($balance + 0);
                            $vat_amount = amountFormat2($vat_amount + 0);
                            $total_amount = amountFormat2($total_amount + 0);
                            $payPreviousAmount = true;
                        }
                        //If Terminate after 25 date of any month charge 0 till Register not happend.
                        elseif ((count($cmval) > 1) && (in_array($ccmval['entry_type'], ["TS"])) && (dataBaseFormat($ccmval['start_date_time']) > dataBaseFormat($previousDate)) && (dataBaseFormat($ccmval['start_date_time']) < dataBaseFormat($invoiceGeneratedDate))) 
                        {
                            $startDate = nextDateTimeStampForGivenDate($ccmval['start_date_time']);
                            $endDate =  dateTimeStampForDataBase($invoiceGeneratedDate);
                            $no_of_days = ($no_of_days + daysBetweenTwoDatesForInternet($startDate, $endDate));
                            $balance = amountFormat2($balance + 0);
                            $vat_amount = amountFormat2($vat_amount + 0);
                            $total_amount = amountFormat2($total_amount + 0);
                            $payPreviousAmount = true;
                        }
                        //If reconnect then have to pay on daily basis.
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["RC"]))) 
                        {           
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
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["CP","CL"]))) 
                        {           
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
                        elseif((count($cmval)==2) && (in_array($ccmval['entry_type'],["NR"])) && (dataBaseFormat($ccmval['start_date_time'])==dataBaseFormat($previousDate))) 
                        {           
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
                        elseif((count($cmval)>1) && (in_array($ccmval['entry_type'],["NR"])) && (dataBaseFormat($ccmval['start_date_time'])>dataBaseFormat($previousDate)) && (dataBaseFormat($ccmval['start_date_time'])<dataBaseFormat($invoiceGeneratedDate))) 
                        {           
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
                        elseif((count($cmval)>1) && ($ccmval['entry_type']=="MP") && ($payPreviousAmount == true)) 
                        { 
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
                        elseif((count($cmval)==1) && ($ccmval['entry_type']=="MP")) 
                        {           
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
                                        
                    $payment = new ServiceAdvancePayment;
                    $advancePaidArr = $payment->select('start_date','end_date','paid_date','remark')->where('address_id', $cmval[0]['address_id'])->where('customer_id', $ckey)->where('service_type', 'IN')->orderByDesc('id')->first();
                    
                    if(is_object($advancePaidArr) && !is_null($advancePaidArr))
                    {
                        if(dataBaseFormat($invoiceGeneratedDate) <= dataBaseFormat($advancePaidArr->end_date))
                        {
                            $paid_status = "Y";
                            $paid_date = dataBaseFormat($advancePaidArr->paid_date);
                            $payment_description = "Paid at IDC ABA virtual account";
                            $remark = $advancePaidArr->remark;
                        }
                        else
                        {
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
                    else
                    {
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
                    $ihistory->user_id = '5'; //idc@worldcity.local
                    $flag = $ihistory->save(); 

                    if(in_array($cmval[0]['entry_type'],['MP','SS','TS']))
                    {
                        $flag1 = true;
                    }
                    else
                    {
                        $flag1 = $ihistory->where('id',$cmval[0]['id'])->
                                    update([
                                        'end_date_time' => dateTimeStampForDataBase($invoiceGeneratedDate.' '.currentHourMinute()) 
                                    ]);
                    }                    
                    if($flag) 
                    {                    
                        // Entry in logs table
                        $logs->user_id = '5'; //idc@worldcity.local
                        $logs->logs_area = logsArea("Internet monthly payment gereration has done reference id [".$ihistory->id."].");
                        $logs->ip_address = $request->ip();
                        $logs->save();
                    } 
                    else 
                    {
                        // Entry in logs table
                        $logs->user_id = '5'; //idc@worldcity.local
                        $logs->logs_area = logsArea("Internet monthly payment gereration has problem reference id [".$ihistory->id."].");
                        $logs->ip_address = $request->ip();
                        $logs->save();
                    }
                    $count++;
                }
            }
            $this->info('Generate internet monthly invoices on 25th day at 04:30 PM of each month.');
        }
        else
        {
            $this->info('Internet monthly invoices already have been generated.');
        }
    }
}