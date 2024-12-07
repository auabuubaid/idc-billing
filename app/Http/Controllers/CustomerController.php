<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use App\Imports\CustomersImport;
use App\Imports\OldMonthlyPaymentImport;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PDF_Code128;
use Maatwebsite\Excel\Facades\Excel;
use setasign\Fpdi\PdfParser\StreamReader;

use App\Models\Logs;
use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\UnitAddress;
use App\Models\OldMonthlyPayment;
use Carbon\Carbon;


class CustomerController extends Controller
{
    /**
     * Show Customers Listing Page For Admin.
     *
     * @param  Request  $request
     * @return View
     */
    public function admin_customers(Request $request)
    {
        $admin = new Admin;
        $customer = new Customer;
        
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS');
        $customersArr = $customer->orderByDesc('id')->paginate($records)->withQueryString();

        return view('admin.customers.index')
                    ->with(['customersArr'=>$customersArr, 'admin_arr'=>$admin_arr, 'records'=>$records]);
    }

    /**
     * Show View Customer Page For Admin.
     *
     * @param  Request  $request
     * @return View
     */
    public function view_admin_customer(Request $request)
    {
        $admin = new Admin;
        $customer = new Customer;
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('admin-customers');
        }        
        $id = $request->segment(2);
        $customerArr = $customer->where('id', $id)->first();
        
        return view('admin.customers.view')
                    ->with(['customerArr'=>$customerArr,'admin_arr'=>$admin_arr]);
    }

    /**
     * Show Import Customer page For Admin.
     *
     * @param  Request  $request
     * @return View
     */
    public function admin_import_customers(Request $request)
    {
        $admin = new Admin;
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->upload)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Upload permission not granted!');
            return redirect()->route('admin-customers');
        }

        return view('admin.customers.import')
                    ->with(['admin_arr'=>$admin_arr]);
    }

    /**
     * Validate & Import Customer For Admin.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_import_customers(Request $request)
    {   
        $rules= array(
            'import_file'=> 'required|mimes:xlsx,xls',
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('admin-import-customers')
                    ->withErrors($validator) 
                        ->withInput();            
        } else {

            $logs = new Logs;
            Excel::import(new CustomersImport,request()->file('import_file'));
            
            // Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Customer import has done.");
            $logs->ip_address = $request->ip();
            $logs->save();

            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Import has done successfully.');
            return redirect()->route('admin-import-customers');
        }
    }

    /**
     * Show Old Monthly Payment List For Admin.
     *
     * @param  Request  $request
     * @return View
     */
    public function old_monthly_payment(Request $request)
    {
		$admin = new Admin;
        $omp = new OldMonthlyPayment;
        
        $monthYear ="Jan 25, 2021";
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS');
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        $ompArr = $omp->where('invoice_date',$monthYear)->orderBy('invoice_no')->paginate($records)->withQueryString();
        
        return view('admin.customers.old_monthly_invoice')
                    ->with(['ompArr'=>$ompArr, 'admin_arr'=>$admin_arr,'monthYear'=>$monthYear,'records'=>$records]);
    }

    /**
     * Show Old Monthly Payment Import page For Admin.
     *
     * @param  Request  $request
     * @return View
     */
    public function import_old_monthly_payment(Request $request)
    {   
        $admin = new Admin;
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->upload)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Upload permission not granted!');
            return redirect()->route('admin-customers');
        }

        return view('admin.customers.old_import')
                    ->with(['admin_arr'=>$admin_arr]);
    }

    /**
     * Validate & Import Old Monthly Payment For Admin.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_old_monthly_payment(Request $request)
    {   
        $rules= array(
            'import_file'=> 'required|mimes:xlsx,xls',
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('import-old-monthly-payment')
                    ->withErrors($validator) 
                        ->withInput();            
        } else {

            $logs = new Logs;
            Excel::import(new OldMonthlyPaymentImport,request()->file('import_file'));

            //Entry in logs table
            $logs->admin_id = $request->session()->get('adminID');
            $logs->logs_area = logsArea("Old monthly payment import has done.");
            $logs->ip_address = $request->ip();
            $logs->save();

            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Import has done successfully.');
            return redirect()->route('old-monthly-payment');
        }
    }

    /**
     * Export Old Internet Monthly Invoices For Admin.
     *
     * @param  Request  $request
     * @return Response
     */
    public function export_old_monthly_invoices(Request $request)
    {   
        $admin = new Admin;        
        $omp = new OldMonthlyPayment;
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();
        
        if(!checkPermission($admin_arr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('old-monthly-payment');
        }
        
        if($request->input('invoice_month_year')){
            $monthYear = $request->input('invoice_month_year');
            $iInvoiceArr = $omp->where('invoice_date', $monthYear)->orderBy('id','asc')->get()->all();

            ini_set('memory_limit', -1);
            $pdf = new Fpdi('P','mm', 'A4');
            foreach($iInvoiceArr as $ikey=>$iArr){  
                $pdf->AddPage();
                $fileContent = file_get_contents(asset('assets/internet_pdf/internet_invoice_old.pdf'),'rb');
                $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
                $tplIdx = $pdf->importPage(1);                
                $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
                $pdf->SetFont('times','B',15);         
                //Personal Application
                // Customer Name 
                $pdf->Text(11,53.5, $iArr->customer_name);
                $pdf->SetFont('times','',10);
                // Customer Address 
                $pdf->Text(35, 71, $iArr->address);
                $pdf->Text(35, 76, 'CamKo Street, Sangkat Toul Sangke 2, Khan Russey Keo,');
                $pdf->Text(35, 80.7, 'Phnom Penh, Cambodia - 120707');
                // Invoice Date 
                $pdf->Text(175, 54.3, $iArr->invoice_date);
                // Due Date 
                $pdf->Text(175, 64.3, $iArr->due_date);
                // Customer Number 
                $pdf->Text(175, 74.2, $iArr->customer_no);
                // Invoice Number 
                $pdf->Text(172, 87.5, $iArr->invoice_no);    
                // Balance 
                $pdf->Text(monthlyInvoiceXCordinate($iArr->balance,0), 105, '$'.amountFormat2($iArr->balance));
                // Other Fee 
                $pdf->Text(monthlyInvoiceXCordinate($iArr->others_fee,0), 125.7, '$'.amountFormat2($iArr->others_fee));
                // Sub Total 
                $pdf->Text(monthlyInvoiceXCordinate(($iArr->balance+$iArr->others_fee),0), 138.8, '$'.amountFormat2($iArr->balance+$iArr->others_fee));
                // VAT Amount 
                $pdf->Text(monthlyInvoiceXCordinate($iArr->vat_amount,0), 145.5, '$'.amountFormat2($iArr->vat_amount));                
                // Grand Total 
                $pdf->SetFont('times','B',10);
                $pdf->Text(monthlyInvoiceXCordinate($iArr->total_amount,0), 152.8, '$'.amountFormat2($iArr->total_amount));
                $pdf->SetFont('times','',10);
                // Exchange Rate 
                $pdf->Text(176.7, 160.5, exchangeRate($iArr->invoice_date));
                $pdf->SetFont('times','B',11);
                // Total in KHR 
                $pdf->Text(monthlyInvoiceXCordinate((exchangeRate($iArr->invoice_date)*$iArr->total_amount),0), 168, amountFormat2(exchangeRate($iArr->invoice_date)*$iArr->total_amount));
                
                $pdf->Rect(12.2, 207, 3.2, 3.5, 'DF');
                // Plan Name 
                $pdf->Text(40, 218.7, $iArr->plan_name);
                $pdf->SetFont('times','B',10);
                // All Day Speed
                $pdf->Text(52, 224, $iArr->speed);
                // Day Time Speed
                $pdf->Text(90, 224, $iArr->speed);
                // Night Time Speed
                $pdf->Text(130, 224, $iArr->speed);
                // All Day Data Usage
                $pdf->Text(52, 229.8, "Unlimited");
                // Day Time Data Usage
                $pdf->Text(90, 229.8, "Unlimited");
                // Night Time Data Usage
                $pdf->Text(130, 229.8, "Unlimited");
                $pdf->Rect(11.9, 245.7, 3.2, 3.5, 'DF');
                //Barcode
                $code=$iArr->invoice_no;
                $pdf->Code128(145,245.8,$code,55,10);
                $pdf->SetXY(143.6,256);
                $pdf->Write(5,$code);                                      
            }
            //Set Title
            $pdf->SetTitle(html_entity_decode($monthYear,ENT_COMPAT,'UTF-8'), true);
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode("Admin",ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die;  
            //Download Monthly Invoices                                  
            $pdf->Output('D',$monthYear.'.pdf');     
            //$pdf->Close();    
            return redirect()->route('old-monthly-payment',['monthlyYear'=>$monthYear]); 
        }else{
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('old-monthly-payment');
        }
    }
    
    /**
     * Generate Old Internet Monthly Invoice For Admin.
     *
     * @param  Request  $request
     * @return Response
     */
    public function generate_old_monthly_invoice(Request $request)
    {   
        $admin = new Admin;        
        $omp = new OldMonthlyPayment;
        $admin_arr = $admin->where('id', $request->session()->get('adminID'))->first();        

        if(!checkPermission($admin_arr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('old-monthly-payment');
        }

        $id = $request->segment(2);
        $iArr= $omp->where('id', $id)->get()->first();
        ini_set('memory_limit', -1);
        if($iArr){
                
            $pdf = new Fpdi('P','mm', 'A4');                
            $fileContent = file_get_contents(asset('assets/internet_pdf/internet_invoice_old.pdf'),'rb');
            $source = $pdf->setSourceFile(StreamReader::createByString($fileContent));
            $tplIdx = $pdf->importPage(1);
            $pdf->AddPage();
            $pdf->useTemplate($tplIdx, 0, 0, 210,297, true);
            $pdf->SetFont('times','B',15);         
            // Customer Name 
            $pdf->Text(11,53.5, $iArr->customer_name);
            $pdf->SetFont('times','',10);
            // Customer Address 
            $pdf->Text(35, 71, $iArr->address);
            $pdf->Text(35, 76, 'CamKo Street, Sangkat Toul Sangke 2, Khan Russey Keo,');
            $pdf->Text(35, 80.7, 'Phnom Penh, Cambodia - 120707');
            // Invoice Date 
            $pdf->Text(175, 54.3, $iArr->invoice_date);
            // Due Date 
            $pdf->Text(175, 64.3, $iArr->due_date);
            // Customer Number 
            $pdf->Text(175, 74.2, $iArr->customer_no);
            // Invoice Number 
            $pdf->Text(172, 87.5, $iArr->invoice_no); 
            // Balance 
            $pdf->Text(monthlyInvoiceXCordinate($iArr->balance,0), 105, '$'.amountFormat2($iArr->balance));
            // Other Fee 
            $pdf->Text(monthlyInvoiceXCordinate($iArr->others_fee,0), 125.7, '$'.amountFormat2($iArr->others_fee));
            // Sub Total 
            $pdf->Text(monthlyInvoiceXCordinate(($iArr->balance+$iArr->others_fee),0), 138.8, '$'.amountFormat2($iArr->balance+$iArr->others_fee));
            // VAT Amount 
            $pdf->Text(monthlyInvoiceXCordinate($iArr->vat_amount,0), 145.5, '$'.amountFormat2($iArr->vat_amount));                
            // Grand Total 
            $pdf->SetFont('times','B',10);
            $pdf->Text(monthlyInvoiceXCordinate($iArr->total_amount,0), 152.8, '$'.amountFormat2($iArr->total_amount));
            $pdf->SetFont('times','',10);
            // Exchange Rate 
            $pdf->Text(176.7, 160.5, exchangeRate($iArr->invoice_date));
            $pdf->SetFont('times','B',11);
            // Total in KHR 
            $pdf->Text(monthlyInvoiceXCordinate((exchangeRate($iArr->invoice_date)*$iArr->total_amount),0), 168, amountFormat2(exchangeRate($iArr->invoice_date)*$iArr->total_amount));
              
            $pdf->Rect(12.2, 207, 3.2, 3.5, 'DF');
            // Plan Name 
            $pdf->Text(40, 218.7, $iArr->plan_name);
            $pdf->SetFont('times','B',10);
            // All Day Speed
            $pdf->Text(52, 224, $iArr->speed);
            // Day Time Speed
            $pdf->Text(90, 224, $iArr->speed);
            // Night Time Speed
            $pdf->Text(130, 224, $iArr->speed);
            // All Day Data Usage
            $pdf->Text(52, 229.8, "Unlimited");
            // Day Time Data Usage
            $pdf->Text(90, 229.8, "Unlimited");
            // Night Time Data Usage
            $pdf->Text(130, 229.8, "Unlimited");
            $pdf->Rect(11.9, 245.7, 3.2, 3.5, 'DF');
            //Barcode
            $code=$iArr->invoice_no;
            $pdf->Code128(145,245.8,$code,55,10);
            $pdf->SetXY(143.6,256);
            $pdf->Write(5,$code);   
            $pdfName= $code.'.pdf';
            //Set Title
            $pdf->SetTitle(html_entity_decode($pdfName,ENT_COMPAT,'UTF-8'), true);
            //Set Author Name
            $pdf->SetAuthor(html_entity_decode("Admin",ENT_COMPAT,'UTF-8'), true);
            //$pdf->Output(); die;  
            //Download Monthly Invoices                                  
            $pdf->Output('D',$pdfName);     
            $pdf->Close();       
            return redirect()->route('internet-monthly-invoice',['monthlyYear'=>$iArr->invoice_date]); 
        }else{
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('old-monthly-payment');
        }
    }

    /**
     * Show Customers Listing Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function index(Request $request)
    {
        $user = new User;
        $customer = new Customer; 
        $unit_address = new UnitAddress;

        $year = '';
        $month = '';
        $monthYear = '';
        $cust_type = '';
        $addressID = '';
        $serviceType = 'I';
        $orderField = 'internet_id';
        $records = !empty($request->input('records')) ? $request->input('records') : env('PER_PAGE_RECORDS');
        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');       
        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        if($request->input('monthlyYear') || $request->input('cust_type') || $request->input('addressID') || $request->input('serviceType')){
            if($request->input('monthlyYear')){
                $monthYearArr = explode('-',$request->input('monthlyYear'));
                $month = $monthYearArr[0];
                $year = $monthYearArr[1];
                $monthYear = $request->input('monthlyYear');
            }
            if($request->input('cust_type')){
                $cust_type = $request->input('cust_type');
            }
            if($request->input('addressID')){
                $addressID = $request->input('addressID');
            }
            if($request->input('serviceType')){
                $serviceType = $request->input('serviceType');
                $orderField = ($serviceType == 'I') ? 'internet_id' : 'cabletv_id';
            }            
            $customerArr = $customer->whereIn('sex',['M','F','O'])->whereNull('deleted_at')
                ->when($month, function($query) use ($month, $year) {
                    return $query->whereMonth('created_at','=',$month)->whereYear('created_at','=',$year); })
                ->when($cust_type, function($query) use ($cust_type) {
                    return $query->where('type', '=', $cust_type); })
                ->when($addressID, function($query) use ($addressID) {
                    return $query->where('address_id', '=', $addressID); })
                ->when($orderField, function($query) use ($orderField) {
                    return $query->whereNotNull($orderField); })
                        ->orderByDesc($orderField)->paginate($records)->withQueryString();
        }else{ 
            $customerArr = $customer->whereNotNull('internet_id')->whereNull('deleted_at')->orderByDesc('internet_id')->paginate($records)->withQueryString();
        }        

        return view('users.customers.index')
                    ->with(['customers_arr'=>$customerArr,'userArr'=>$userArr, 'unitsAddressArr'=>$unitsGroupAddressArr,'monthYear'=>$monthYear,'cust_type'=>$cust_type,'addressID'=>$addressID,'serviceType'=>$serviceType,'records'=>$records]);
    }

    /**
     * Show Add Customer page.
     *
     * @param  Request  $request
     * @return View
     */
    public function add_customer(Request $request)
    {   
        $user = new User;
        $customer = new Customer;
        $unit_address = new UnitAddress;
        
        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('customers');
        }

        $unitsGroupAddressArr = $unit_address->where('status','A')->get()->groupBy('location_id');
        return view('users.customers.add')
                    ->with(['unit_address_arr'=>$unitsGroupAddressArr, 'userArr'=>$userArr]);
    }

    /**
     * Validate & Store Customer.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store_customer(Request $request)
    {   
        $rules= array(
            'application_type'=> 'required|in:P,S',
            'full_address'=> 'required',
            'country'=> 'required',
            'service_type' => 'required|in:I,C,B',
            'customer_name'=> ($request->input('application_type')=='P') ? 'required|string' : '',
            'customer_email'=> ($request->input('customer_email')!='') ? 'required|email|unique:customers,email' : '',
            'customer_mobile'=> ($request->input('application_type')=='P') ? 'required|alpha_dash|max:12' : '',
            'is_living'=> ($request->input('application_type')=='P') ? 'required|in:Y,N' : '',
            'sex'=> ($request->input('application_type')=='P') ? 'required|in:M,F,O' : '',
            'shop_name'=> ($request->input('application_type')=='S') ? 'required|string' : '',
            'authorized_person'=> ($request->input('application_type')=='S') ? 'required|string' : '',
            'shop_email'=> ($request->input('shop_email')!='') ? 'required|email|unique:customers,shop_mobile' : '',
            'shop_mobile'=> ($request->input('application_type')=='S') ? 'required|alpha_dash|max:12' : '',
            'shop_vat_no'=> ($request->input('shop_vat_no')!='') ? 'required|alpha_dash' : '',
            'ip_address'=> ($request->input('service_type')!='C') ? 'required|ip' : '',
            'internet_password'=> ($request->input('service_type')!='C') ? 'required|string' : ''
        );
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('add-customer')
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving unit address in database
            $logs = new Logs;
			$customer= new Customer;

            //For Only Internet Customers
            if($request->input('service_type')=='I')
            {
                $customer->internet_id = newInternetCustomerID();
            } //For Only CableTV Customers
            elseif($request->input('service_type')=='C')
            {
                $customer->cabletv_id = newCableTVCustomerID();
            } //For Both Internet & CableTV Customers
            else
            {
                $customer->internet_id = newInternetCustomerID();
                $customer->cabletv_id = newCableTVCustomerID();
            }
            $customer->address_id = $request->input('full_address');
            $customer->type = $request->input('application_type');
            $customer->country = $request->input('country');
            $customer->name = ($request->input('application_type')=='P')?$request->input('customer_name'):$request->input('authorized_person');
            $customer->email = $request->input('customer_email');
            $customer->mobile = rtrim($request->input('customer_mobile'), "_");
            $customer->is_living = ($request->input('application_type')=='P')?$request->input('is_living'):'N';
            $customer->sex = ($request->input('application_type')=='P')?$request->input('sex'):'M';
            $customer->shop_name = $request->input('shop_name');
            $customer->shop_email = $request->input('shop_email');
            $customer->shop_mobile = rtrim($request->input('shop_mobile'), "_");
            $customer->vat_no = $request->input('shop_vat_no');
            $customer->ip_address = $request->input('ip_address');
            $customer->internet_password = $request->input('internet_password');
            $customer->created_by = $request->session()->get('userID');            
            $flag = $customer->save();

			if ($flag) {
                // Entry in logs table
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Added new customer reference id [".$customer->id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // validation successful!
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been added successfully!');
				return redirect()->route('customers');
			} else {
                // database insertion was not successful, send back to add form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not added, please try again!');
				return redirect()->route('add-customer');
			}
        }
    }

    /**
     * Show View Customer Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function view_customer(Request $request)
    {
        $user = new User;
        $customer = new Customer; 

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('customers');
        }

        $id = $request->segment(2);
        $customerArr = $customer->where('id', $id)->first();
        
        if(!is_object($customerArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('customers');
        }
        return view('users.customers.view')
                    ->with(['customerArr'=>$customerArr,'userArr'=>$userArr]);
    }

    /**
     * Show Edit Customer Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function edit_customer(Request $request)
    {
        $user = new User;
        $customer = new Customer;
        $unit_address = new UnitAddress;

        $userArr = $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->write)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Write permission not granted!');
            return redirect()->route('customers');
        }
        
        $id = $request->segment(2);
        $customerArr = $customer->where('id', $id)->first();
        if(!is_object($customerArr)){
            // Record not found
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Record not found!');
            return redirect()->route('customers');
        }

        return view('users.customers.edit')
                    ->with(['customerArr'=>$customerArr,'userArr'=>$userArr]);
    }

    /**
     * Validate & Update Customer.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update_customer(Request $request)
    {   
        $rules= array(
            'application_type'=> 'required|in:P,S',
            'country'=> 'required',
            'customer_name'=> ($request->input('application_type')=='P')?'required|string':'',
            'customer_email'=> ($request->input('customer_email')!='')?'required|email':'',
            'customer_mobile'=> ($request->input('application_type')=='P')?'required|alpha_dash|max:12':'',
            'is_living'=> ($request->input('application_type')=='P')?'required|in:Y,N':'',
            'sex'=> ($request->input('application_type')=='P')?'required|in:M,F,O':'',
            'shop_name'=> ($request->input('application_type')=='S')?'required|string':'',
            'authorized_person'=> ($request->input('application_type')=='S')?'required|string':'',
            'shop_email'=> ($request->input('shop_email')!='')?'required|email':'',
            'shop_mobile'=> ($request->input('application_type')=='S')?'required|alpha_dash|max:12':'',
            'shop_vat_no'=> ($request->input('shop_vat_no')!='')?'required|alpha_dash':'',
        );
        $validator = Validator::make($request->all(), $rules);
        $id = $request->input('custID');

        if ($validator->fails()) {
            // validation unsuccessful!
            Session::flash('color','warning');
            Session::flash('icon','fas fa-exclamation-triangle');
            Session::flash('msg','Please fill all mandatory fields!');             
            return redirect()->route('edit-customer', ['id' => $id])
                    ->withErrors($validator) 
                        ->withInput();            
        } else {
            // saving building in database
            $logs = new Logs;
			$customer = new Customer;
            
            $flag = $customer->where('id',$id)
                        ->update([
                            'type'=> $request->input('application_type'), 
                            'is_living'=> !empty($request->input('is_living')) ? $request->input('is_living') : 'N', 
                            'name'=> ($request->input('application_type')=='P') ? $request->input('customer_name') : $request->input('authorized_person'), 
                            'email'=> $request->input('customer_email'),
                            'mobile'=> rtrim($request->input('customer_mobile'), "_"), 
                            'sex'=> !empty($request->input('sex')) ? $request->input('sex') : 'M', 
                            'shop_name'=> $request->input('shop_name'), 
                            'shop_email'=> $request->input('shop_email'), 
                            'shop_mobile'=> rtrim($request->input('shop_mobile'), "_"), 
                            'vat_no'=> $request->input('shop_vat_no'), 
                            'country'=> $request->input('country')
                        ]);

			if ($flag) {
                // Entry in logs table
                $logs->user_id = $request->session()->get('userID');
                $logs->logs_area = logsArea("Updated customer details reference id [".$id."].");
                $logs->ip_address = $request->ip();
                $logs->save();

                // database insertion was successful, send back to edit form
                Session::flash('color','success');
                Session::flash('icon','fa fa-check');
                Session::flash('msg','Record has been updated successfully!');
				return redirect()->route('edit-customer', ['id' => $id]);
			} else {
                // database insertion was not successful, send back to edit form
                Session::flash('color','danger');
                Session::flash('icon','fa fa-ban');
                Session::flash('msg','Record is not updated, please try again!');
				return redirect()->route('edit-customer', ['id' => $id]);
			}
			
        }
    }

    /**
     * Delete Customer.
     *
     * @param  Request  $request
     * @return View
     */
    public function destroy_customer(Request $request)
    {
        $logs = new Logs;
        $user = new User;
        $customer = new Customer;  

        $userArr = $user->where('id', $request->session()->get('userID'))->first();

        if(!checkPermission($userArr->delete)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Delete permission not granted!');
            return redirect()->route('customers');
        }

        $id = $request->segment(2);
        $flag = $customer->where('id', $id)->delete();
        $updateArr = $customer->where('id',$id)->update(['internet_id'=> null, 'cabletv_id'=> null, 'deleted_at'=>Carbon::now()]);

        if ($updateArr) {
            // Entry in logs table
            $logs->user_id = $request->session()->get('userID');
            $logs->logs_area = logsArea("Deleted customer reference id [".$id."].");
            $logs->ip_address = $request->ip();
            $logs->save();

            // database deletion was successful, send back to listing form
            Session::flash('color','success');
            Session::flash('icon','fa fa-check');
            Session::flash('msg','Record has been deleted successfully!');
            return redirect()->route('customers');
        } else {
            // database deletion was not successful, send back to listing form
            Session::flash('color','danger');
            Session::flash('icon','fa fa-ban');
            Session::flash('msg','Record is not deleted, please try again!');
            return redirect()->route('customers');
        }
    }
}
