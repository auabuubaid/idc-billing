<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Http\Request;
use App\Models\InternetHistory;

class UploadMonthlyIDCFilesToFTPServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploadMonthlyIDCFilesToFTPServer:monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload monthly subscribers internet invoices, summary list, customer info  and upstream/downstream files to "Test" directory in FTP server on 25th at 04:50 PM of each month.';

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
        /**
         * 
         * Start FTP Connection & Login.
         */
        $conn_id = ftp_connect(env('FTP_SERVER_HOST'));
        $login = ftp_login($conn_id, env('FTP_SERVER_USERNAME'), env('FTP_SERVER_PASSWORD'));
        ftp_pasv($conn_id, true);

        /**
         * 
         * Uploading ISP_Invoice_Summary to FTP server on "Test" directory.
         */        
        $summaryFileName = givenYearMonth2(currentMonthYear()).'_List_Invoice_Internet.xlsx'; 
        $content = file_get_contents(public_path($summaryFileName));        
        // create
        $tmp = fopen(tempnam(sys_get_temp_dir(), $summaryFileName), "w+");
        fwrite($tmp, $content);
        rewind($tmp);            
        // upload to Test for IDC 
        ftp_fput($conn_id, 'Test/'.$summaryFileName, $tmp, FTP_ASCII);        

        if (@file_exists(public_path($summaryFileName))) {
            unlink(public_path($summaryFileName));
        }

        /**
         * 
         * Uploading ISP_Customer_Info_List to FTP server on "Test" directory.
         */
        $infoFileName = givenYearMonth2(currentMonthYear()).'_ISP_Customer_Info.csv';   
        $content = file_get_contents(public_path($infoFileName));
        // create
        $tmp = fopen(tempnam(sys_get_temp_dir(), $infoFileName), "w+");
        fwrite($tmp, $content);
        rewind($tmp);            
        // upload to Test for IDC
        ftp_fput($conn_id, 'Test/'.$infoFileName, $tmp, FTP_ASCII);

        if (@file_exists(public_path($infoFileName))) {
            unlink(public_path($infoFileName));
        } 

        /**
         * 
         * Uploading _ISP_Upstream_Downstream to FTP server on "Test" directory.
         */
        $upDownStreamfileName = givenYearMonth2(currentMonthYear()).'_ISP_Upstream_Downstream.csv';        
        $content = file_get_contents(public_path($upDownStreamfileName));
        // create
        $tmp = fopen(tempnam(sys_get_temp_dir(), $upDownStreamfileName), "w+");
        fwrite($tmp, $content);
        rewind($tmp);            
        // upload to Test for IDC
        ftp_fput($conn_id, 'Test/'.$upDownStreamfileName, $tmp, FTP_ASCII);        

        if (@file_exists(public_path($upDownStreamfileName))) {
            unlink(public_path($upDownStreamfileName));
        }

        /**
         * 
         * Uploading ISP_Invoice to FTP server on "ISP_Invoice" & "Test" directory.
         */
        $ihistory = new InternetHistory;
        $invoiceDate = internetEndMonthlyPaymentDate(currentMonthYear());
        $iInvoiceArr = $ihistory->where('monthly_invoice_date', $invoiceDate)->orderBy('invoice_number','asc')->get()->all();
        
        foreach($iInvoiceArr as $ikey=>$iArr)
        {
            //pdfName
            $pdfName= invoiceName($iArr->invoice_number, $iArr->monthly_invoice_date).'.pdf';
            
            $content = file_get_contents(public_path($pdfName));
            // create
            $tmp = fopen(tempnam(sys_get_temp_dir(), $pdfName), "w+");
            fwrite($tmp, $content);
            rewind($tmp);            
            // upload to Test for IDC
            ftp_fput($conn_id, 'Test/'.$pdfName, $tmp, FTP_ASCII);            

            if (@file_exists(public_path($pdfName))) {
                unlink(public_path($pdfName));
            }
        }

        // close ftp connection
        ftp_close($conn_id);
        $this->info('Upload internet monthly invoices, customer info, summary list to FTP server for IDC on 25th day at 04:50 PM of each month.');
    }
}
