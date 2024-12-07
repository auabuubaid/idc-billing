<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Models\Logs;
use Illuminate\Http\Request;
use App\Models\ExchangeRate;
use App\Models\InternetHistory;
use App\Exports\InternetDMCSummaryExport;
use App\Exports\InternetMonthlyCustomerInfoExport;
use App\Exports\InternetMonthlyUpDownStreamExport;
// PDF & EXCEL
use setasign\Fpdi\Fpdi;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\PdfParser\StreamReader;

class UploadMonthlyDMCFilesToFTPServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploadMonthlyDMCFilesToFTPServer:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload monthly subscribers internet invoices to "ISP_Invoice" directory, monthly summary excel file to "ISP_Invoice_Summary" directory, 
                                monthly customer info csv file to "ISP_Customer_Info_List" directory and upstream/downstream csv file to "ISP_Upstream_BW" directory
                                in DMC FTP server on 25th at 04:45 PM of each month.';

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
        $logs = new Logs;
        $exrate = new ExchangeRate;
        
        $alreadyExistsRateArr = $exrate->whereDate('monthly_date',dataBaseFormat(env('MONTHLY_INVOICE_DATE').'-'.currentMonthYear()))->get()->toArray();
        if(count($alreadyExistsRateArr)==0)
        {  
            $curl = curl_init();
            curl_setopt_array($curl, array(
            CURLOPT_URL => env('EXCHANGE_RATE_URL').env('MONTHLY_INVOICE_DATE').'-'.currentMonthYear(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $exchangeRateArr = simplexml_load_string(curl_exec($curl));
            curl_close($curl);
            $rate = (array) @$exchangeRateArr->ex[0]->bid;
            
            /**
             * 
             * Store exchange rate in database.
             */
            $exrate->from_currency = 'USD';
            $exrate->to_currency = 'KHR';
            $exrate->rate = @$rate[0];                
            $exrate->monthly_date = dataBaseFormat(env('MONTHLY_INVOICE_DATE').'-'.currentMonthYear());
            $exrate->created_by = '5'; //idc@worldcity.local;
            $flag = $exrate->save();  
            if ($flag) 
            {
                // Entry in logs table
                $logs->user_id =  '5'; //idc@worldcity.local
                $logs->logs_area = logsArea("Exchange rate has added reference id [".$exrate->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();
            }
        }

        /**
         * 
         * Start FTP Connection & Login.
         */
        $conn_id = ftp_connect(env('FTP_SERVER_HOST'));
        $login = ftp_login($conn_id, env('FTP_SERVER_USERNAME'), env('FTP_SERVER_PASSWORD'));
        ftp_pasv($conn_id, true);

        /**
         * 
         * Uploading ISP_Invoice_Summary to FTP server on "ISP_Invoice_Summary" directory.
         */
        $summaryStartDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate(currentMonthYear()));
        $summaryEndDate = internetEndMonthlyPaymentDate(currentMonthYear());
        $summaryFileName = givenYearMonth2(currentMonthYear()).'_List_Invoice_Internet.xlsx';            
        $summaryFlag = Excel::store(new InternetDMCSummaryExport($summaryStartDate,$summaryEndDate), $summaryFileName,'file_location');
        if($summaryFlag)
        {
            $content = file_get_contents(public_path($summaryFileName));
            // create
            $tmp = fopen(tempnam(sys_get_temp_dir(), $summaryFileName), "w+");
            fwrite($tmp, $content);
            rewind($tmp);
            // upload to ISP_Invoice_Summary for DMC 
            $upload1 = ftp_fput($conn_id, 'ISP_Invoice_Summary/'.$summaryFileName, $tmp, FTP_ASCII);            
        }

        /**
         * 
         * Uploading ISP_Customer_Info_List to FTP server on "ISP_Customer_Info_List" directory.
         */
        $infoFileName = givenYearMonth2(currentMonthYear()).'_ISP_Customer_Info.csv';        
        $infoFlag = Excel::store(new InternetMonthlyCustomerInfoExport(), $infoFileName,'file_location');
        if($infoFlag)
        {
            $content = file_get_contents(public_path($infoFileName));
            // create
            $tmp = fopen(tempnam(sys_get_temp_dir(), $infoFileName), "w+");
            fwrite($tmp, $content);
            rewind($tmp);
            // upload to ISP_Customer_Info_List for DMC
            $upload2 = ftp_fput($conn_id, 'ISP_Customer_Info_List/'.$infoFileName, $tmp, FTP_ASCII);            
        }

        /**
         * 
         * Uploading _ISP_Upstream_Downstream to FTP server on "_ISP_Upstream_Downstream" directory.
         */
        $upDownStreamfileName = givenYearMonth2(currentMonthYear()).'_ISP_Upstream_Downstream.csv';        
        $streamFlag = Excel::store(new InternetMonthlyUpDownStreamExport(givenYearMonth2(currentMonthYear())), $upDownStreamfileName,'file_location');
        if($streamFlag)
        {
            $content = file_get_contents(public_path($upDownStreamfileName));
            // create
            $tmp = fopen(tempnam(sys_get_temp_dir(), $upDownStreamfileName), "w+");
            fwrite($tmp, $content);
            rewind($tmp);
            // upload to ISP_Upstream_BW for DMC
            $upload3 = ftp_fput($conn_id, 'ISP_Upstream_BW/'.$upDownStreamfileName, $tmp, FTP_ASCII);             
        }

        /**
         * 
         * Uploading ISP_Invoice to FTP server on "ISP_Invoice" directory.
         */
        $ihistory = new InternetHistory;
        $exchange_rate = new ExchangeRate;
        $invoiceDate = internetEndMonthlyPaymentDate(currentMonthYear());
        $iInvoiceArr = $ihistory->where('monthly_invoice_date', $invoiceDate)->orderBy('invoice_number','asc')->get()->all();
        $exchangeRateArr = $exchange_rate->where('monthly_date', $invoiceDate)->orderByDesc('id')->get()->first();
        
        foreach($iInvoiceArr as $ikey=>$iArr)
        {
            ini_set('memory_limit', -1);
            ini_set ('max_execution_time', -1);
            $pdf = new Fpdi('P','mm', 'A4');                
            $fileContent = file_get_contents(public_path('assets/internet_pdf/new_dmc_invoice.pdf'),'rb');
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
            $pdf->SetAuthor(html_entity_decode(userNameByUserID('5'),ENT_COMPAT,'UTF-8'), true);//idc@worldcity.local
            //Compress Pdf                                  
            $pdf->SetCompression(true);   
            //Download Monthly Invoices                                  
            $pdf->Output('F',public_path($pdfName));     
            $pdf->Close();  
            
            $content = file_get_contents(public_path($pdfName));
            // create
            $tmp = fopen(tempnam(sys_get_temp_dir(), $pdfName), "w+");
            fwrite($tmp, $content);
            rewind($tmp);
            // upload to ISP_Invoice for DMC
            $upload4 = ftp_fput($conn_id, 'ISP_Invoice/'.$pdfName, $tmp, FTP_ASCII);  
        }
        // close FTP connection
        ftp_close($conn_id);

        $this->info('Store monthly exchange rate and upload internet monthly invoices, customer info, summary list to FTP server for DMC on 25th day at 04:45 PM of each month.');
    }
}
