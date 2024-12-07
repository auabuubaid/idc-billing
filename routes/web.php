<?php
// Controller
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IDCController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CableTvController;
use App\Http\Controllers\BuildingConroller;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InternetController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\UnitAddressController;

// Middleware
use App\Http\Middleware\XssSanitizer;
use App\Http\Middleware\UserAuthenticate;
use App\Http\Middleware\AdminAuthenticate;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware([XssSanitizer::class])->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login');
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/authenticate', [LoginController::class, 'authenticate'])->name('authenticate');
    Route::get('/forgot-password', [UserController::class, 'forgot_password'])->name('forgot-password');
    Route::post('/validate-email', [UserController::class, 'validate_email'])->name('validate-email');
    Route::get('/reset-password/{token}', [UserController::class, 'reset_password'])->name('reset-password');
    Route::post('/recover-password', [UserController::class, 'recover_password'])->name('recover-password');
});

//XssSanitizer::class
Route::middleware([UserAuthenticate::class])->group(function () {

Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
Route::post('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
Route::get('/add-customer', [CustomerController::class, 'add_customer'])->name('add-customer');
Route::post('/store-customer', [CustomerController::class, 'store_customer'])->name('store-customer');
Route::get('/view-customer/{id}', [CustomerController::class, 'view_customer'])->name('view-customer');
Route::get('/edit-customer/{id}', [CustomerController::class, 'edit_customer'])->name('edit-customer');
Route::post('/update-customer', [CustomerController::class, 'update_customer'])->name('update-customer');
Route::get('/destroy-customer/{id}', [CustomerController::class, 'destroy_customer'])->name('destroy-customer');
// Internet Section 
Route::get('/internet-subscribers', [InternetController::class, 'internet_subscribers'])->name('internet-subscribers');
Route::get('/pppoe-connection', [InternetController::class, 'pppoe_connection'])->name('pppoe-connection');
Route::get('/pppoe-connection-image/{custID}', [InternetController::class, 'pppoe_connection_image'])->name('pppoe-connection-image');
Route::get('/change-pppoe-status/{custID}/{plan}', [InternetController::class, 'change_pppoe_status'])->name('change-pppoe-status');
Route::get('/internet-monthly-invoice', [InternetController::class, 'monthly_invoice'])->name('internet-monthly-invoice');
Route::get('/generate-monthly-payment-invoice', [InternetController::class, 'monthly_payment_invoice'])->name('generate-monthly-payment-invoice');
Route::get('/edit-monthly-invoice/{id}', [InternetController::class, 'edit_monthly_invoice'])->name('edit-monthly-invoice');
Route::post('/update-monthly-invoice', [InternetController::class, 'update_monthly_invoice'])->name('update-monthly-invoice');
Route::get('/new-internet-connection', [InternetController::class, 'new_connection'])->name('new-internet-connection');
Route::post('/store-internet-connection', [InternetController::class, 'store_internet_connection'])->name('store-internet-connection');
Route::get('/view-internet-transaction/{id}', [InternetController::class, 'view_internet_transaction'])->name('view-internet-transaction');
Route::get('/internet-change-service', [InternetController::class, 'internet_change_service'])->name('internet-change-service');
Route::post('/update-internet-plan', [InternetController::class, 'update_internet_plan'])->name('update-internet-plan');
Route::get('/internet-change-location', [InternetController::class, 'internet_change_location'])->name('internet-change-location');
Route::post('/update-internet-relocation', [InternetController::class, 'update_internet_relocation'])->name('update-internet-relocation');
Route::get('/internet-suspend-service', [InternetController::class, 'internet_suspend_service'])->name('internet-suspend-service');
Route::post('/update-internet-suspension', [InternetController::class, 'update_internet_suspension'])->name('update-internet-suspension');
Route::get('/internet-reconnect-service', [InternetController::class, 'internet_reconnect_service'])->name('internet-reconnect-service');
Route::post('/update-internet-reconnect', [InternetController::class, 'update_internet_reconnection'])->name('update-internet-reconnection');
Route::get('/internet-terminate-service', [InternetController::class, 'internet_terminate_service'])->name('internet-terminate-service');
Route::post('/update-internet-termination', [InternetController::class, 'update_internet_termination'])->name('update-internet-termination');
Route::get('/internet-history', [InternetController::class, 'internet_history'])->name('internet-history');
Route::get('/export-dmc-invoice', [InternetController::class, 'export_idc_dmc_invoice'])->name('export-dmc-invoice');
Route::get('/export-customer-info', [InternetController::class, 'export_customer_info'])->name('export-customer-info');
Route::get('/upstream-downstream', [InternetController::class, 'export_updown_stream'])->name('upstream-downstream');
Route::get('/model-house-summary', [InternetController::class, 'model_house_summary'])->name('model-house-summary');
Route::get('/dmc-summary', [InternetController::class, 'dmc_summary'])->name('dmc-summary');
Route::post('/export-monthly-payment', [InternetController::class, 'export_monthly_payment'])->name('export-monthly-payment');
Route::get('/export-unpaid-internet', [InternetController::class, 'export_unpaid_internet'])->name('export-unpaid-internet');
Route::get('/import-unpaid-internet', [InternetController::class, 'import_unpaid_internet'])->name('import-unpaid-internet');
Route::post('/update-unpaid-internet', [InternetController::class, 'update_unpaid_internet'])->name('update-unpaid-internet');
Route::get('/all-active-internet-subscribers', [InternetController::class, 'all_active_subscribers'])->name('all-active-internet-subscribers');
Route::get('/active-internet-subscribers', [InternetController::class, 'active_subscribers'])->name('active-internet-subscribers');

Route::get('/generate-internet-invoice/{id}', [InternetController::class, 'generate_internet_invoice'])->name('generate-internet-invoice');
Route::post('/get-customer-details', [AjaxController::class, 'get_customer_details'])->name('get-customer-details');
Route::post('/get-customer-with-internet-plan', [AjaxController::class, 'get_customer_with_internet_plan'])->name('get-customer-with-internet-plan');
Route::post('/get-customer-with-suspend-details', [AjaxController::class, 'get_customer_with_suspend_details'])->name('get-customer-with-suspend-details');
Route::post('/get-customer-with-reconnect-details', [AjaxController::class, 'get_customer_with_reconnect_details'])->name('get-customer-with-reconnect-details');
Route::post('/get-customer-with-terminate-details', [AjaxController::class, 'get_customer_with_terminate_details'])->name('get-customer-with-terminate-details');
Route::post('/get-plan-details', [AjaxController::class, 'internet_plan_details'])->name('get-plan-details');

Route::get('/internet-advance-payment', [InternetController::class, 'advance_payment'])->name('internet-advance-payment');
Route::get('/add-internet-advance-payment/{id}', [InternetController::class, 'add_advance_payment'])->name('add-internet-advance-payment');
Route::post('/store-internet-advance-payment', [InternetController::class, 'store_advance_payment'])->name('store-internet-advance-payment');

Route::get('/exchange-rate', [InternetController::class, 'exchange_rate'])->name('exchange-rate');
Route::get('/add-exchange-rate', [InternetController::class, 'add_exchange_rate'])->name('add-exchange-rate');
Route::post('/store-exchange-rate', [InternetController::class, 'store_exchange_rate'])->name('store-exchange-rate');
Route::get('/view-exchange-rate/{id}', [InternetController::class, 'view_exchange_rate'])->name('view-exchange-rate');

Route::get('/internet-report', [InternetController::class, 'internet_report'])->name('internet-report');
Route::post('/internet-report', [InternetController::class, 'internet_report'])->name('internet-report');
Route::get('/mptc-report', [InternetController::class, 'mptc_report'])->name('mptc-report');
Route::get('/mptc-income-statement', [InternetController::class, 'mptc_income_statement'])->name('mptc-income-statement');
Route::get('/mptc-service-declaration', [InternetController::class, 'mptc_service_declaration'])->name('mptc-service-declaration');

// Route::get('/edit-exchange-rate/{id}', [InternetController::class, 'edit_exchange_rate'])->name('edit-exchange-rate');
// Route::post('/update-exchange-rate', [InternetController::class, 'update_exchange_rate'])->name('update-exchange-rate');
// Route::get('/destroy-exchange-rate/{id}', [InternetController::class, 'destroy_exchange_rate'])->name('destroy-exchange-rate');

// Cable TV Section
Route::get('/cabletv-subscribers', [CableTvController::class, 'subscribers'])->name('cabletv-subscribers');
Route::get('/cabletv-monthly-invoice', [CableTvController::class, 'monthly_invoice'])->name('cabletv-monthly-invoice');
Route::post('/generate-cabletv-monthly-invoice', [CableTvController::class, 'monthly_payment_invoice'])->name('generate-cabletv-monthly-invoice');
Route::get('/edit-cabletv-monthly-invoice/{id}', [CableTvController::class, 'edit_monthly_invoice'])->name('edit-cabletv-monthly-invoice');
Route::post('/update-cabletv-monthly-invoice', [CableTvController::class, 'update_monthly_invoice'])->name('update-cabletv-monthly-invoice');
Route::get('/new-cabletv-connection', [CableTvController::class, 'new_connection'])->name('new-cabletv-connection');
Route::post('/store-cabletv-connection', [CableTvController::class, 'store_connection'])->name('store-cabletv-connection');
Route::get('/view-cabletv-transaction/{id}', [CableTvController::class, 'view_transaction'])->name('view-cabletv-transaction');
Route::get('/cabletv-change-location', [CableTvController::class, 'change_location'])->name('cabletv-change-location');
Route::post('/update-cabletv-location', [CableTvController::class, 'update_location'])->name('update-cabletv-location');
Route::get('/cabletv-terminate-service', [CableTvController::class, 'terminate_service'])->name('cabletv-terminate-service');
Route::post('/update-cabletv-termination', [CableTvController::class, 'update_termination'])->name('update-cabletv-termination');
Route::get('/cabletv-reconnect-service', [CableTvController::class, 'reconnect_service'])->name('cabletv-reconnect-service');
Route::post('/update-cabletv-reconnection', [CableTvController::class, 'update_reconnection'])->name('update-cabletv-reconnection');
Route::get('/cabletv-change-owner', [CableTvController::class, 'change_owner'])->name('cabletv-change-owner');
Route::post('/update-cabletv-owner', [CableTvController::class, 'update_owner'])->name('update-cabletv-owner');
Route::get('/cabletv-history', [CableTvController::class, 'history'])->name('cabletv-history');
Route::get('/cabletv-advance-payment', [CableTvController::class, 'advance_payment'])->name('cabletv-advance-payment');
Route::get('/edit-cabletv-advance-payment/{id}', [CableTvController::class, 'edit_advance_payment'])->name('edit-cabletv-advance-payment');
Route::post('/update-cabletv-advance-payment', [CableTvController::class, 'update_advance_payment'])->name('update-cabletv-advance-payment');
Route::post('/export-cabletv-monthly-payment', [CableTvController::class, 'export_monthly_payment'])->name('export-cabletv-monthly-payment');
Route::post('/export-cabletv-monthly-invoice', [CableTvController::class, 'export_monthly_invoice'])->name('export-cabletv-monthly-invoice');
Route::get('/export-unpaid-cabletv', [CableTvController::class, 'export_unpaid_cabletv'])->name('export-unpaid-cabletv');
Route::get('/import-unpaid-cabletv', [CableTvController::class, 'import_unpaid_cabletv'])->name('import-unpaid-cabletv');
Route::post('/update-unpaid-cabletv', [CableTvController::class, 'update_unpaid_cabletv'])->name('update-unpaid-cabletv');
Route::get('/generate-cabletv-invoice/{id}', [CableTvController::class, 'generate_cabletv_invoice'])->name('generate-cabletv-invoice');
Route::get('/cabletv-report', [CableTvController::class, 'cabletv_report'])->name('cabletv-report');
Route::post('/cabletv-report', [CableTvController::class, 'cabletv_report'])->name('cabletv-report');
Route::post('/customer-with-cabletv-plan', [AjaxController::class, 'customer_with_cabletv_plan'])->name('customer-with-cabletv-plan');
Route::post('/cabletv-reconnect-details', [AjaxController::class, 'cabletv_reconnect_details'])->name('cabletv-reconnect-details');
Route::post('/cabletv-terminate-details', [AjaxController::class, 'cabletv_terminate_details'])->name('cabletv-terminate-details');
Route::post('/cabletv-plan-details', [AjaxController::class, 'cabletv_plan_details'])->name('cabletv-plan-details');

##################### Supervisor #######################
Route::get('/summary-report', [SupervisorController::class, 'summary_report'])->name('summary-report');
Route::post('/summary-report', [SupervisorController::class, 'summary_report'])->name('summary-report');
Route::post('/export-summary-report', [SupervisorController::class, 'export_summary_report'])->name('export-summary-report');
##################### End Supervisor ###################

});
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');


##################### Admin ###################
Route::middleware([XssSanitizer::class])->group(function () {
    Route::get('/admin/login', [LoginController::class, 'admin_login'])->name('admin-login');
    Route::post('/admin/authenticate', [LoginController::class, 'admin_authenticate'])->name('admin-authenticate');
    Route::get('/admin/forgot-password', [AdminController::class, 'forgot_password'])->name('admin-forgot-password');
    Route::post('/admin/validate-email', [AdminController::class, 'validate_admin_email'])->name('validate-admin-email');
    Route::get('/admin/reset-password/{token}', [AdminController::class, 'reset_password'])->name('admin-reset-password');
    Route::post('/admin/recover-password', [AdminController::class, 'recover_password'])->name('recover-admin-password');
});

Route::middleware([AdminAuthenticate::class, XssSanitizer::class])->group(function () {

Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin-dashboard');
Route::post('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin-dashboard');
// Building Location
Route::get('/buildings-location', [BuildingConroller::class, 'index'])->name('buildings-location');
Route::get('/import-buildings-location', [BuildingConroller::class, 'import_building_location'])->name('import-buildings-location');
Route::post('/store-import-buildings-location', [BuildingConroller::class, 'store_import_buildings_location'])->name('store-import-buildings-location');
Route::get('/add-building-location', [BuildingConroller::class, 'add_building_location'])->name('add-building-location');
Route::post('/store-building-location', [BuildingConroller::class, 'store_building_location'])->name('store-building-location');
Route::get('/view-building-location/{id}', [BuildingConroller::class, 'view_building_location'])->name('view-building-location');
Route::get('/edit-building-location/{id}', [BuildingConroller::class, 'edit_building_location'])->name('edit-building-location');
Route::post('/update-building-location', [BuildingConroller::class, 'update_building_location'])->name('update-building-location');
Route::get('/destroy-building-location/{id}', [BuildingConroller::class, 'destroy_building_location'])->name('destroy-building-location');
// Unit Address
Route::get('/units-address', [UnitAddressController::class, 'index'])->name('units-address');
Route::get('/import-units-address', [UnitAddressController::class, 'import_units_address'])->name('import-units-address');
Route::post('/store-import-units-address', [UnitAddressController::class, 'store_import_units_address'])->name('store-import-units-address');
Route::get('/add-unit-address', [UnitAddressController::class, 'add_unit_address'])->name('add-unit-address');
Route::post('/store-unit-address', [UnitAddressController::class, 'store_unit_address'])->name('store-unit-address');
Route::get('/view-unit-address/{id}', [UnitAddressController::class, 'view_unit_address'])->name('view-unit-address');
Route::get('/edit-unit-address/{id}', [UnitAddressController::class, 'edit_unit_address'])->name('edit-unit-address');
Route::post('/update-unit-address', [UnitAddressController::class, 'update_unit_address'])->name('update-unit-address');
Route::get('/destroy-unit-address/{id}', [UnitAddressController::class, 'destroy_unit_address'])->name('destroy-unit-address');
// Customer
Route::get('/admin/customers', [CustomerController::class, 'admin_customers'])->name('admin-customers');
Route::get('/admin/import-customers', [CustomerController::class, 'admin_import_customers'])->name('admin-import-customers');
Route::post('/store-import-customers', [CustomerController::class, 'store_import_customers'])->name('store-import-customers');
Route::get('/view-admin-customer/{id}', [CustomerController::class, 'view_admin_customer'])->name('view-admin-customer');

// Old Internet Monthly Payment Invoices
Route::get('/import-old-monthly-payment', [CustomerController::class, 'import_old_monthly_payment'])->name('import-old-monthly-payment');
Route::post('/store-old-monthly-payment', [CustomerController::class, 'store_old_monthly_payment'])->name('store-old-monthly-payment');
Route::post('/export-old-monthly-invoices', [CustomerController::class, 'export_old_monthly_invoices'])->name('export-old-monthly-invoices');
Route::get('/generate-old-monthly-invoice/{id}', [CustomerController::class, 'generate_old_monthly_invoice'])->name('generate-old-monthly-invoice');
Route::get('/old-monthly-payment', [CustomerController::class, 'old_monthly_payment'])->name('old-monthly-payment');
Route::post('/old-monthly-payment', [CustomerController::class, 'old_monthly_payment'])->name('old-monthly-payment');

// Internet Services
Route::get('/internet-services', [IDCController::class, 'internet_services'])->name('internet-services');
Route::get('/add-internet-service', [IDCController::class, 'add_internet_service'])->name('add-internet-service');
Route::post('/store-internet-service', [IDCController::class, 'store_internet_service'])->name('store-internet-service');
Route::get('/edit-internet-service/{id}', [IDCController::class, 'edit_internet_service'])->name('edit-internet-service');
Route::post('/update-internet-service', [IDCController::class, 'update_internet_service'])->name('update-internet-service');
Route::get('/view-internet-service/{id}', [IDCController::class, 'view_internet_service'])->name('view-internet-service');
Route::get('/destroy-internet-service/{id}', [IDCController::class, 'destroy_internet_service'])->name('destroy-internet-service');
// CableTV Services
Route::get('/cabletv-services', [IDCController::class, 'cabletv_services'])->name('cabletv-services');
Route::get('/add-cabletv-service', [IDCController::class, 'add_cabletv_service'])->name('add-cabletv-service');
Route::post('/store-cabletv-service', [IDCController::class, 'store_cabletv_service'])->name('store-cabletv-service');
Route::get('/edit-cabletv-service/{id}', [IDCController::class, 'edit_cabletv_service'])->name('edit-cabletv-service');
Route::post('/update-cabletv-service', [IDCController::class, 'update_cabletv_service'])->name('update-cabletv-service');
Route::get('/view-cabletv-service/{id}', [IDCController::class, 'view_cabletv_service'])->name('view-cabletv-service');
Route::get('/destroy-cabletv-service/{id}', [IDCController::class, 'destroy_cabletv_service'])->name('destroy-cabletv-service');
// Users
Route::get('/users', [UserController::class, 'index'])->name('users');
Route::get('/add-user', [UserController::class, 'add'])->name('add-user');
Route::post('/store-user', [UserController::class, 'store'])->name('store-user');
Route::get('/view-user/{id}', [UserController::class, 'view'])->name('view-user');
Route::get('/edit-user/{id}', [UserController::class, 'edit'])->name('edit-user');
Route::post('/update-user', [UserController::class, 'update'])->name('update-user');
Route::get('/destroy-user/{id}', [UserController::class, 'destroy'])->name('destroy-user');

});
Route::get('/admin/logout', [LoginController::class, 'admin_logout'])->name('admin-logout');