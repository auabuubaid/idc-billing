<?php

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\UnitAddress;
use App\Models\CableTVService;
use App\Models\CableTVHistory;
use App\Models\InternetService;
use App\Models\InternetHistory;
use App\Models\BuildingLocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// CORE NETWORK
use \RouterOS\Query;
use \RouterOS\Config;
use \RouterOS\Client;

###################################################################
##                                                               ##
##                            Dates                              ##
##                                                               ##
###################################################################

// Return Months Between Two Dates
function monthsBetweenTwoDates($startDate, $endDate){
    $startDate= Carbon::createFromFormat('d-m-Y',Carbon::parse($startDate)->format('d-m-Y'));
    $endDate= Carbon::createFromFormat('d-m-Y',Carbon::parse($endDate)->format('d-m-Y'));
    $monthsDiff= $startDate->diffInMonths($endDate);
    //echo $endDate.'-'.$endDate.'='.$monthsDiff; die;
    return $monthsDiff;
}

// Return Days Between Two Dates //Y-m-d
function daysBetweenTwoDates($startDate, $endDate){
    $startDate= Carbon::createFromFormat('Y-m-d',Carbon::parse($startDate)->format('Y-m-d'));
    $endDate= Carbon::createFromFormat('Y-m-d',Carbon::parse($endDate)->format('Y-m-d'));
    $days= ($startDate->diffInDays($endDate)); //+1= include end day
    return $days;
}

// Return Month Number to Name
function monthNumberToName($month){
    return date("F", mktime(0,0,0,$month,12));
}

// Return Month Number to Name
function monthNumberToName2($month){
    return date("M", mktime(0,0,0,$month,12));
}

// Return Date (Apr 6, 2021)
function pdfDateFormat($dateString){
    $date = Carbon::parse($dateString)->isoFormat('MMM  DD, YYYY');
    return $date;
}

// Return Date Picker Format (DD-MM-YYYY)
function datePickerFormat($dateString){
    $date = Carbon::parse($dateString)->isoFormat('DD-MM-YYYY');
    return $date;
}

// Return Date Picker Format (DD-MMM-YYYY)
function datePickerFormat2($dateString){
    $date = Carbon::parse($dateString)->isoFormat('DD-MMM-YYYY');
    return $date;
}

// Return Date Picker Format (DD-MMM-YY)
function datePickerFormat3($dateString){
    $date = Carbon::parse($dateString)->isoFormat('DD-MMM-YY');
    return $date;
}

// Return DataBase Format (DD-MMM-YY)
function dmcDateFormat($dateString){
    $date = Carbon::parse($dateString)->isoFormat('D-MMM-YY');
    return $date;
}

// Return DataBase Format (YYYY-MM-DD)
function dataBaseFormat($dateString){
    $date = Carbon::parse($dateString)->isoFormat('YYYY-MM-DD');
    return $date;
}

// Return DateAndTime (02 April, 2021 01:35:55 PM)
function readDateTimeFormat($dateString){
    $date = Carbon::parse($dateString)->isoFormat('DD MMMM, YYYY | hh:mm:ss A');
    return $date;
}

// Return Current Date (yyyy-mm-dd)
function currentDate2(){
    $date = Carbon::now()->isoFormat('YYYY-MM-DD');
    return $date;
}

// Return Current Date (dd mmm yyyy - dd mmm yyyy)
function subscribeDate(){
    $date = Carbon::now()->isoFormat('DD MMM YYYY').' - '.Carbon::now()->endOfMonth()->isoFormat('DD MMM YYYY');
    return $date;
}

// Return Current Date (dd-mm-yyyy)
function currentDate(){
    $date = Carbon::now()->isoFormat('DD-MM-YYYY');
    return $date;
}

// Return Current Quarter (1,2,3,4)
function currentQuarter(){
    $date = Carbon::now();
    return $date->quarter;
}

// Return Previous Date (yyyy-mm-dd-1)
function previousDateOFGivenDate($dateString){
    $date = Carbon::parse($dateString)->sub('1 day')->isoFormat('YYYY-MM-DD');
    return $date;
}

// Return Next Date (yyyy-mm-dd+1)
function nextDateOFGivenDate($dateString){
    $date = Carbon::parse($dateString)->add('1 day')->isoFormat('YYYY-MM-DD');
    return $date;
}

// Return Only Day by Given Date (yyyy-mm-dd)
function dayOFGivenDate($dateString){
    $day = Carbon::parse($dateString)->isoFormat('DD');
    return $day;
}

// Return Due Date (MMM DD, YYYY)
function dueDateOfMonthlyPayment($dateString){
    $date = Carbon::parse(env('DUE_DATE').'-'.nextMonthYear($dateString))->isoFormat('DD-MM-YYYY');
    return pdfDateFormat($date);
}

// Return Due Date (MMM DD, YYYY)
function cableTVDueDate($dateString){
    $date = Carbon::parse(env('DUE_DATE').'-'.monthYear($dateString))->isoFormat('DD-MM-YYYY');
    return pdfDateFormat($date);
}

// Return Date Time Stamp (yyyy-mm-dd hh:mm:ss)
function dateTimeStampForDataBase($dateString){
    return Carbon::parse($dateString)->toDateTimeString();
}

// Return Date Time Stamp (yyyy-mm-dd hh:mm:ss)
function currentDateTimeStampForDataBase(){
    return Carbon::now()->toDateTimeString();
}

// Return previous date time stamp 
function previousDateTimeStampForGivenDate($dateString){
    return Carbon::parse($dateString)->sub('1 day')->toDateTimeString();
}

// Return next date time stamp 
function nextDateTimeStampForGivenDate($dateString){
    return Carbon::parse($dateString)->add('1 day')->toDateTimeString();
}

// Return n days next date time stamp 
function nextNDaysDateTimeStampForGivenDate($dateString, $days){
    return Carbon::parse($dateString)->add($days.' day')->toDateTimeString();
}

// Return Current Time (h-m AM/PM)
function currentTime(){
    $time = Carbon::now()->format('h:i A');
    return $time;
}

// Return Current Time (hh:mm:ss)
function currentHourMinute(){
    $time = Carbon::now()->isoFormat('H:mm:00');
    return $time;
}

// Return Current Month(m)
function currentMonth(){
    $date = Carbon::now();
    return $date->month;
}

// Return Current Month(mm)
function currentMonth2(){
    $date = Carbon::now()->isoFormat('MM');
    return $date;
}

// Return Current Year(Y)
function currentYear(){
    $date = Carbon::now();
    return $date->year;
}

// Return Current Year(yyyy)
function currentYear2(){
    $date = Carbon::now();
    return $date->isoFormat('YYYY');
}

// Return Previous Month Year(mm-yyyy)
function previousMonthYear($monthYear){    
    $date = Carbon::createFromFormat('m-Y', $monthYear)->subMonth()->isoFormat('MM-YYYY');
    return $date;
}

// Return Month Year(mm-yyyy)
function monthYear($dateString){
    $dateString = Carbon::parse($dateString)->isoFormat('MM-YYYY');
    $date = Carbon::createFromFormat('m-Y', $dateString)->isoFormat('MM-YYYY');
    return $date;
}

// Return Month Year(mm-yyyy)
function nextMonthYear($dateString){
    $dateString =  Carbon::parse($dateString)->isoFormat('MM-YYYY');
    $date = Carbon::createFromFormat('m-Y', $dateString)->addMonth()->isoFormat('MM-YYYY');
    return $date;
}

// Return Current Month Year(mm-yyyy)
function currentMonthYear(){
    $date = Carbon::now();
    return $date->isoFormat('MM-YYYY');
}

// Return Current Year Month(yyyy-mm)
function currentYearMonth(){
    $date = Carbon::now();
    return $date->isoFormat('YYYY-MM');
}

// Return Month Year(YYYY-MM)
function previousYearMonth($dateString){
    $dateString =  Carbon::parse($dateString)->isoFormat('YYYY-MM');
    $date = Carbon::createFromFormat('Y-m', $dateString)->subMonth()->isoFormat('YYYY-MM');
    return $date;
}

// Return Current Year Month(yyyy-mm)
function givenYearMonth($monthYear){
    $date = Carbon::parse($monthYear)->isoFormat('YYYY-MM');
    return $date;
}

// Return Current Year Month(yyyymm)
function givenYearMonth2($monthYear){
    $month = Carbon::parse('25-'.$monthYear)->isoFormat('MM');
    $year = Carbon::parse('25-'.$monthYear)->isoFormat('YYYY');
    return $year.$month;
}

// Return Month (MM)
function monthByGivenDate($dateString){
    $date = Carbon::parse($dateString)->isoFormat('MM');
    return $date;
}

// Return Month (MMM)
function monthByGivenDate2($dateString){
    $date = Carbon::parse($dateString)->isoFormat('MMM');
    return $date;
}

// Return Year (YYYY)
function yearByGivenDate($dateString){
    $date = Carbon::parse($dateString)->isoFormat('YYYY');
    return $date;
}

// Return Yearly Start & End Date By Year(YYYY-MM-DD)
function yearlyStartEndDate($dateString){  
    $startDate = Carbon::parse($dateString)->startOfMonth()->isoFormat('YYYY-MM-DD');
    $endDate = Carbon::parse($dateString)->endOfMonth()->isoFormat('YYYY-MM-DD');
    return ['startDate'=>$startDate, 'endDate'=>$endDate];
}
// Return Quarterly Start & End Date By Year(YYYY-MM-DD)
function quarterlyStartEndDate($dateString){
    $dateString = Carbon::parse($dateString)->isoFormat('YYYY-MM');    
    $startDate = Carbon::parse($dateString)->startOfMonth()->isoFormat('YYYY-MM-DD');
    $endDate = Carbon::parse($dateString)->addMonth(2)->endOfMonth()->isoFormat('YYYY-MM-DD');
    return ['startDate'=>$startDate, 'endDate'=>$endDate];
}

// Return Monthly Start & End Date By Month & Year(YYYY-MM-DD)
function monthlyStartEndDate($mmyyyy){
    $mmyyyy = Carbon::parse($mmyyyy)->isoFormat('YYYY-MM');   
    $startDate = Carbon::createFromFormat('Y-m', $mmyyyy)->startOfMonth()->isoFormat('YYYY-MM-DD');
    $endDate = Carbon::createFromFormat('Y-m', $mmyyyy)->endOfMonth()->isoFormat('YYYY-MM-DD');
    return ['startDate'=>$startDate, 'endDate'=>$endDate];
}
###################################################################
##                                                               ##
##                           End Dates                           ##
##                                                               ##
###################################################################


###################################################################
##                                                               ##
##                           Address                             ##
##                                                               ##
###################################################################

// Building Location & Building Name by Location ID example- R1-A101
function buildingLocationAndNameByID($id){
    $building_location = new BuildingLocation;
    $building_location_arr= $building_location->where('id', $id)->first();
    if($building_location_arr){
        return $building_location_arr->location.'-'.$building_location_arr->name;
    }else{
        return false;
    }
}

// Building Location by Location ID example- R1-Apartment-A101
function buildingLocationByLocationID($id){
    $building_location = new BuildingLocation;
    $building_location_arr= $building_location->where('id', $id)->first();
    if($building_location_arr){
        return $building_location_arr->location.'-'.buildingTypeAbbreviationToFullName($building_location_arr->type).'-'.$building_location_arr->name;
    }else{
        return false;
    }
}

// Building Name by Location ID example- A101
function buildingNameByLocationID($locID){
    $building_location = new BuildingLocation;
    $building_location_arr= $building_location->where('id', $locID)->first();
    if($building_location_arr){
        return $building_location_arr->name;
    }else{
        return false;
    }
}

// Unit Number by Unit ID example- 101
function unitNumberByUnitID($id){
    $unit_address = new UnitAddress;
    $unit_address_arr= $unit_address->where('id', $id)->first();
    if($unit_address_arr){
        return $unit_address_arr->unit_number;
    }else{
        return false;
    }
 }
 
// Building Location by Unit ID example- A101-101
function buildingLocationAndUnitNumberByUnitID($id){
    $unit_address = new UnitAddress;
    $building_location = new BuildingLocation;
    $unit_address_arr= $unit_address->where('id', $id)->first();
    if($unit_address_arr){
         $building_location_arr= $building_location->where('id', $unit_address_arr->location_id)->first();
         if($building_location_arr){
            return $building_location_arr->name.'-'.unitNumberByUnitID($id);
         }else{
            return false;
         }
    }else{
        return false;
    }
}

// Building Location by Unit ID example- A101-101
function reportUnitAddressByUnitID($id){
    $unit_address = new UnitAddress;
    $building_location = new BuildingLocation;
    $unit_address_arr= $unit_address->where('id', $id)->first();
    if($unit_address_arr){
         $building_location_arr= $building_location->where('id', $unit_address_arr->location_id)->first();
         if($building_location_arr){
             if(in_array($building_location_arr->type,['A','S','T']))
                return $building_location_arr->name.'-'.unitNumberByUnitID($id);
             else
                return unitNumberByUnitID($id);
         }else{
            return false;
         }
    }else{
        return false;
    }
}

// Unit Address by Unit ID example- R1-Apartment-A101-101
function unitAddressByUnitID($id){
    $unit_address = new UnitAddress;
    $unit_address_arr= $unit_address->where('id', $id)->first();
    if($unit_address_arr){
        return buildingLocationByLocationID($unit_address_arr->location_id).'-'.$unit_address_arr->unit_number;
    }else{
        return false;
    }
 }
 
// Return FullName by Building Type Abbreviation
function buildingTypeAbbreviationToFullName($type){
    switch($type){
        case 'A':
            $result= 'Apartment';
        break;

        case 'T':
            $result= 'Town House';
        break;

        case 'S':
            $result= 'Shops';
        break;

        case 'V':
            $result= 'Villa';
        break;

        default:
            $result= '';
    }
   return $result;
}

###################################################################
##                                                               ##
##                          End Address                          ##
##                                                               ##
###################################################################

###################################################################
##                                                               ##
##                        Miscellaneous                          ##
##                                                               ##
###################################################################

// logs data
function logsArea($string){
    return $string;
}

// Return Quarter Array By Number
function quaterArrByNumber($quater){
    switch($quater){
        case '4':
            $quarterArr = ['10','11','12'];
        break;
 
        case '3':
            $quarterArr = ['07','08','09'];
        break;

        case '2':
            $quarterArr = ['04','05','06'];
        break;
 
        default:
            $quarterArr = ['01','02','03'];
    }
    return $quarterArr;
}

// FullName by Belongs To Abbreviation
function belongsToAbbreviationToFullName($type){
    switch($type){
        case 'W':
            $result= 'World City';
        break;
 
        case 'S':
            $result= 'Sold Out';
        break;
 
        default:
            $result= '';
    }
    return $result;
}

// Return 1 If Belongs To = "World City"
function returnOneByBelongsType($type){
    $result='';
    if($type=='W')
        return $result=1;
}

// Return 1 For Vacant
function returnOneForVacant($belongs_to, $occupied){
    $result=1;
    if($belongs_to=='W' && $occupied=='Y')
        return $result='';

    elseif($belongs_to=='S' && $occupied=='N')
        return $result='';
}

// Return 1 For Unsold
function returnOneForUnsold($belongs_to, $rantal){
    $result='';
    if($belongs_to=='W' && $rantal=='N')
        return $result=1;
}

// Return 1 For Unpaid Rental
function returnOneForUnpaidRental($belongs_to, $rantal){
    $result='';
    if($belongs_to=='W' && $rantal=='N')
        return $result=1;
}
 
// Return 1 If Value="Y"
function returnOneByValIsY($val){
    $result='';
    if($val=='Y')
        return $result=1;
}

// Return 1 If Value="N"
function returnOneByValIsN($val){
    $result='';
    if($val=='N')
        return $result=1;
}

// Return Full by Y/N Abbreviation
function yesnoAbbreviationToFull($abbr){
    switch($abbr){
        case 'Y':
            $result= 'Yes';
        break;
 
        case 'N':
            $result= 'No';
        break;
 
        default:
            $result= '';
    }
    return $result;
}

// Return Full by A/N Abbreviation
function activeNotActiveAbbreviationToFull($abbr){
    switch($abbr){
        case 'A':
            $result= 'Active';
        break;
 
        case 'N':
            $result= 'Not Active';
        break;
 
        default:
            $result= '';
    }
    return $result;
}
 
// Return Full by U/L Abbreviation
function LimitedUnlimitedAbbreviationToFull($abbr){
    switch($abbr){
        case 'U':
            $result= 'Unlimited';
        break;
 
        case 'L':
            $result= 'Limited';
        break;
 
        default:
            $result= '';
    }
    return $result;
}

// Return Full by Agreement Abbreviation
function agreementAbbreviationToFull($abbr){

    switch($abbr){
        case 'N':
            $result= 'None';
        break;
 
        case '1':
            $result= '1 Year';
        break;

        case '2':
            $result= '2 Years';
        break;

        case '3':
            $result= '3 Years';
        break;

        case '4':
            $result= '4 Years';
        break;

        case '5':
            $result= '5 Years';
        break;
 
        default:
            $result= '';
    }
    return $result;
}

// Return Full by Payment Mode Abbreviation
function paymentModeAbbreviationToFull($abbr){

    switch($abbr){
        case 'CA':
            $result= 'Cash';
        break;
 
        case 'BA':
            $result= 'Bank';
        break;

        case 'CH':
            $result= 'Cheque';
        break;

        case 'OT':
            $result= 'Other';
        break;
 
        default:
            $result= '';
    }
    return $result;
}

// Return Full by Subscription Level
function subscriptionLevelToFull($level){

    switch($level){
        case 'UPGRADE':
            $result= ['color'=>'success', 'icon'=>'fas fa-level-up-alt', 'full'=>'Upgraded'];
        break;
 
        case 'DOWNGRADE':
            $result= ['color'=>'danger', 'icon'=>'fas fa-level-down-alt', 'full'=>'Downgraded'];
        break;

        case 'NORMAL':
            $result= ['color'=>'primary', 'icon'=>'fas fa-arrows-alt-h', 'full'=>'Normal'];
        break;
 
        default:
            $result= [''];
    }
    return $result;
}

// Return Full by Paid By Abbreviation
function paidByAbbreviationToFull($abbr){

    switch($abbr){
        case 'CP':
            $result= ['color'=>'success', 'full'=>'Customer Paid'];
        break;
 
        case 'CR':
            $result= ['color'=>'success', 'full'=>'Company Refunded'];
        break;
 
        default:
            $result= [''];
    }
    return $result;
}

// Return True/False To Check Permission
function checkPermission($response){
    if($response=='Y'){
        return true;
    }else{
        return false;
    }
}

// Return Electricity Fee
function electricityFee($consumption){
    $rate=0.18;
    return $consumption * $rate;
}

// Amount Format for round till 2 decimal point
function amountFormat($price){
    return number_format((float)round($price,2), 2, '.', '');
}

// Amount Format for 2 decimal point
function amountFormat2($price){
    return number_format((float)($price), 2, '.', '');
}

// Amount Format for 2 decimal point with ,
function reportAmountFormat($amount){
    return number_format($amount, 2);
}

// US amount format
function usAmountFormat($amount){
    $fmt = new NumberFormatter( app()->getLocale(), NumberFormatter::CURRENCY );
    return $fmt->formatCurrency($amount, $currency);
    // setlocale(LC_MONETARY,"en_US");
    // return money_format($amount);
}

// Print & Die
function pad($variable){    
    echo "<pre>"; print_r($variable); echo "</pre>"; die;
}

 // Print only
 function pa($variable){
    echo "<pre>"; print_r($variable); echo "</pre>";
}

// FullName by Internet History Type Abbreviation
function userTypeAbbreviationToFullName($type){
    switch($type){
        case 'MST':
            $result= ['folder'=>'mst', 'full'=>'Marketing Support Team'];
        break;
 
        case 'MT':
            $result= ['folder'=>'mt', 'full'=>'Marketing Team'];
        break;
 
        case 'IDC':
            $result= ['folder'=>'idc', 'full'=>'Information Data Centre'];
        break;
 
        case 'ET':
            $result= ['folder'=>'et', 'full'=>'Electricity Team'];
        break;

        case 'N':
            $result= ['folder'=>'n', 'full'=>'Supervisor'];
        break;
 
        default:
            $result= [''];
    }
    return $result;
}

// FullName by Resolution Payment Type Abbreviation
function resolutionPaymentAbbreviationToFullName($type){
    switch($type){
        case 'R':
            $result= ['icon'=>'fas fa-clipboard-list', 'color'=>'warning', 'full'=>'Reported'];
        break;
 
        case 'C':
            $result= ['icon'=>'fas fa-clipboard-check', 'color'=>'primary', 'full'=>'Checked'];
        break;
 
        case 'A':
            $result= ['icon'=>'fas fa-thumbs-up', 'color'=>'success', 'full'=>'Approved'];
        break;
 
        default:
            $result= [''];
    }
    return $result;
}

// FullName by Internet History Type Abbreviation
function internetHistoryTypeAbbreviationToFullName($type){
    switch($type){
        case 'NR':
            $result= ['icon'=>'fas fa-user-plus', 'color'=>'primary', 'full'=>'New Registration'];
        break;
 
        case 'CP':
            $result= ['icon'=>'fas fa-exchange-alt', 'color'=>'success', 'full'=>'Change Plan'];
        break;
 
        case 'CL':
            $result= ['icon'=>'fas fa-house-damage', 'color'=>'fuchsia', 'full'=>'Change Location'];
        break;
 
        case 'SS':
            $result= ['icon'=>'fas fa-power-off', 'color'=>'orange', 'full'=>'Suspend'];
        break;

        case 'RC':
            $result= ['icon'=>'fas fa-plug', 'color'=>'indigo', 'full'=>'Reconnect'];
        break;

        case 'TS':
            $result= ['icon'=>'fas fa-times-circle', 'color'=>'danger', 'full'=>'Terminate'];
        break;

        case 'MP':
            $result= ['icon'=>'fas fa-calendar-alt', 'color'=>'navy', 'full'=>'Monthly Invoice'];
        break;
 
        default:
            $result= [''];
    }
    return $result;
}

// FullName by Internet History Type Abbreviation
function internetTypeAbbreviationToFullName($type){
    switch($type){
        case 'NR':
            $result= 'New Installation';
        break;
 
        case 'CP':
            $result='Changed Plan';
        break;
 
        case 'CL':
            $result= 'Changed Location';
        break;
 
        case 'SS':
            $result= 'Suspended';
        break;

        case 'RC':
            $result= 'Reconnect';
        break;

        case 'TS':
            $result= 'Terminated';
        break;

        case 'MP':
            $result= 'Monthly Internet Fee';
        break;
 
        default:
            $result= '';
    }
    return $result;
}

// Return Amount by MPTC Service Type
function mptcTotalServiceRevenue($type, $startDate, $endDate){
    switch($type){
        case 'internet_fee':
            $result= amountFormat2(getInvoiceAmountByMonth($startDate, $endDate));
        break;
 
        case 'installation_fee':
            $result= amountFormat2(getInstallationAmountByMonth($startDate, $endDate));
        break;
 
        case 'maintenance_fee':
            $result= NULL;
        break;
 
        case 'other_revenues':
            $result= amountFormat2(getServiceAmountByMonth($startDate, $endDate));
        break;

        case 'credit_note':
            $result= amountFormat2(0);
        break;

        case 'debit_note':
            $result= amountFormat2(0);
        break;

        case 'sale':
            $result= amountFormat2(0);
        break;
 
        default:
            $result= amountFormat2(0);
    }
    return $result;
}

###################################################################
##                                                               ##
##                      End Miscellaneous                        ##
##                                                               ##
###################################################################


###################################################################
##                                                               ##
##                           Internet                            ##
##                                                               ##
###################################################################

// Return Days Between Two Dates for Internet Payment//Y-m-d H:s:i #not in use
function daysBetweenTwoDatesForInternet($startDate, $endDate){
    $startDate= Carbon::createFromFormat('Y-m-d',Carbon::parse($startDate)->format('Y-m-d'));
    $endDate= Carbon::createFromFormat('Y-m-d',Carbon::parse($endDate)->format('Y-m-d'));
    $days= ($startDate->diffInDays($endDate)+1);//+1= include end day
    //echo $endDate.' | '.$startDate.' = '.$days.'<br>';
    if((in_array(monthByGivenDate($startDate),['01','03','05','07','08','10','12'])) && (in_array(monthByGivenDate($endDate),['01','03','05','07','08','10','12']))){ 
        if(monthByGivenDate($startDate)==monthByGivenDate($endDate)) 
        {
            if(dayOFGivenDate($endDate)=='31') 
            {
                $days = ($days-1);
            }
            else
            {
                $days = $days;
            }
        }
        else
        {
            $days = ($days-1);
        } 
    }
    if((in_array(monthByGivenDate($startDate),['01','03','05','07','08','10','12'])) && (in_array(monthByGivenDate($endDate),['02','04','06','09','11']))){ 
        $days = ($days-1);
    }
    if((monthByGivenDate($startDate)=='02') && (monthByGivenDate($endDate)=='03')){ 
        $days = (yearByGivenDate($startDate)%4==0) ? $days+1 : ($days+2);
    }
    if($days>30){ $days= env('MONTHLY_DAYS_COUNT'); }  
    return $days;
}

// Return Payment Date From to To
function fromToMonthlyInvoiceDate($id){    
    $ihistory = new InternetHistory;
    $invoiceArr = $ihistory->select('customer_id', 'start_date_time', 'end_date_time', 'monthly_invoice_date')->where('id', $id)->where('entry_type', 'MP')->get()->first();

    if (is_object($invoiceArr)) {
        $monthYear = monthYear($invoiceArr->start_date_time);
        $startDate = nextDateOFGivenDate(dataBaseFormat(env('MONTHLY_INVOICE_DATE') . '-' . previousMonthYear($monthYear)));
        $endDate = dataBaseFormat($invoiceArr->monthly_invoice_date);
    }

    return 'From ' . Carbon::parse($startDate)->isoFormat('MMM DD') . ' To ' . Carbon::parse($endDate)->isoFormat('MMM DD, YYYY');
}

// Return Internet Start Monthly Payment Date (yyyy-(mm-1)-25)
function internetStartMonthlyPaymentDate($monthYear){
    $date = Carbon::parse((env('MONTHLY_INVOICE_DATE')).'-'.previousMonthYear($monthYear))->isoFormat('YYYY-MM-'.(env('MONTHLY_INVOICE_DATE')));
    return $date;
}

// Return Internet subscribe End Date (yyyy-mm-25)
function internetSubscribeEndDate($startDate,$months){
    $endDate= Carbon::parse($startDate)->addMonth($months)->isoFormat('YYYY-MM-'.env('MONTHLY_INVOICE_DATE'));  
        return $endDate;
}

// Return Internet End Monthly Payment Date (yyyy-mm-25)
function internetEndMonthlyPaymentDate($monthYear){    
    $date = Carbon::parse('25-'.$monthYear)->isoFormat('YYYY-MM-25');
    return $date;
}

// Return Internet Customers Current Status 
function currentInternetCustomersStatusByID($custID){
    $ihistory = new InternetHistory;
    $iStatusArr = $ihistory->select('id','entry_type','plan_remark','refrence_id')->where('customer_id',$custID)->orderByDesc('start_date_time')->first();
    if(in_array($iStatusArr->entry_type,['TS'])){
        return 'Deactive'; 
    }elseif(in_array($iStatusArr->entry_type,['SS'])){
        return 'Suspend'; 
    }elseif($iStatusArr->entry_type=='MP' && $iStatusArr->plan_remark!=null){
        $ihistory = new InternetHistory;
        $entryTypeArr = $ihistory->select('entry_type')->where('customer_id',$custID)->where('id',$iStatusArr->refrence_id)->first();
        return ($entryTypeArr->entry_type == 'SS') ? 'Suspend' : 'Deactive';
        
    }else{
        return 'Active';
    }
}

// Return Internet Customers Current Status 
function internetSuspendOrActiveStatus($type, $plan_remark){
    if($type=='SS'){
        return 'Suspend'; 
    }elseif($type=='MP' && $plan_remark!=NULL){
        return 'Suspend';
    }else{
        return 'Active';
    }
}



// Return Not Paid Internet Invoices Count
function notPaidInternetCount($custID,$address_id){
    $ihistory = new InternetHistory;
    $notPaidCount = $ihistory->select('id')->where('customer_id',$custID)->where('address_id',$address_id)->where('paid','N')->where('entry_type','MP')->orderByDesc('start_date_time')->count();
    return $notPaidCount;
}

// Return Internet Customers Current Address 
function currentInternetCustomersAddressByID($custID){
    $ihistory = new InternetHistory;
    $iAddressArr = $ihistory->select('id','address_id')->where('customer_id',$custID)->orderBy('start_date_time', 'desc')->first();
    return $iAddressArr->address_id;
}

// Return Internet Customers Current Plan 
function currentInternetCustomersPlanByID($custID){
    $ihistory = new InternetHistory;
    $iAddressArr = $ihistory->select('id','plan_id')->where('customer_id',$custID)->orderBy('start_date_time', 'desc')->first();
    return $iAddressArr->plan_id;
}

// Return Total Active Internet Subscribers
function totalActiveInternetSubscribers(){
    //DB::enableQueryLog();
    $customerCount = DB::select('select count(`customer_id`) as `active_subscribers` from `internet_history` where (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `internet_history` group by `customer_id`) and `customer_id` not in (select `customer_id` from `internet_history` where `entry_type`="SS" and `suspension_end_date` is NULL) and `customer_id` not in (select `customer_id` from `internet_history` where `entry_type`="TS")');
    //$sql= DB::getQueryLog();
    //dd($sql);
    return $customerCount[0]->active_subscribers;
}

// Return Total Active Internet Subscribers Included Suspend
function totalActiveWithSuspendInternetSubscribers(){
    //DB::enableQueryLog();
    $customerCount = DB::select('select count(`customer_id`) as `active_subscribers` from `internet_history` where `entry_type`="SS" and `suspension_end_date` is NULL');
    //$sql= DB::getQueryLog();
    //dd($sql);
    return (totalActiveInternetSubscribers()+$customerCount[0]->active_subscribers);
}

// Return Total Internet Amount
function totalInternetAmountTIllToday(){

    $ihistory = new InternetHistory;
    $todayDate = currentDate2();
    $previousDate = $ihistory->all('monthly_invoice_date')->max('monthly_invoice_date');  
    $total_amount= $ihistory->select('total_amount')->where('payment_by','CP')->where('paid','Y')->whereNotIn('entry_type',['SS','TS'])->whereDate('start_date_time','>=', $previousDate)->whereDate('start_date_time','<=',$todayDate)->sum('total_amount');
    return ['startDate'=>pdfDateFormat($previousDate) , 'endDate'=>pdfDateFormat($todayDate) , 'total_amount'=>amountFormat2($total_amount)];
}

// Return  overview amount field
function overviewAmountVatTotal($type, $installFee, $reInstallFee, $reconnectFee, $refID){
    switch($type){
        case 'NR':
            $result= ['amount'=>amountFormat2($installFee-($installFee/11)), 'vat'=>amountFormat2($installFee/11), 'total'=>amountFormat2($installFee), 'remark'=>'New Connection'];
        break;
 
        case 'CL':
            $result= ['amount'=>amountFormat2($reInstallFee-($reInstallFee/11)), 'vat'=>amountFormat2($reInstallFee/11), 'total'=>amountFormat2($reInstallFee), 'remark'=>remarkForSuspendTerminateAndLocation($type,$refID)];
        break;
 
        case 'RC':
            $result= ['amount'=>amountFormat2($reconnectFee-($reconnectFee/11)), 'vat'=>amountFormat2($reconnectFee/11), 'total'=>amountFormat2($reconnectFee), 'remark'=>(amountFormat2($reconnectFee)<1)?'Reconnection Not Over 2 months': 'Reconnection'];
        break;
 
        default:
            $result= ['amount'=>amountFormat2(0), 'vat'=>amountFormat2(0), 'total'=>amountFormat2(0), 'remark'=>NULL];
    }
    return $result;
}

// Return daily charged customer status
function remarkForSuspendTerminateAndLocation($entryType, $id){
    $ihistory = new InternetHistory;
    $ihistoryArr= $ihistory->where('id', $id)->first();

    if($entryType=="MP" && in_array($ihistoryArr->entry_type,['SS','TS'])){
        return internetHistoryTypeAbbreviationToFullName($ihistoryArr->entry_type)['full'].' on '.datePickerFormat2($ihistoryArr->start_date_time);
    }elseif($entryType=="MP" && $ihistoryArr->entry_type=="CL"){
        $ihistory = new InternetHistory;
        $ihistoryArr2= $ihistory->where('id', $ihistoryArr->refrence_id)->first();
        return 'Changed Location from '.buildingLocationAndUnitNumberByUnitID($ihistoryArr2->address_id);
    }elseif($entryType=="CL"){
        return 'Changed Location from '.buildingLocationAndUnitNumberByUnitID($ihistoryArr->address_id);
    }elseif($entryType=="TS"){
        return 'Terminate';
    }else{
        return NULL;
    }    
}

// Return remark for new connection
function remarkForNewConnection($id){
    $ihistory = new InternetHistory;
    $ihistoryArr= $ihistory->where('id', $id)->first();

    if(($ihistoryArr->entry_type=="NR") && ($ihistoryArr->plan_id=="13")){
        return 'Rental Promotion';
    }elseif(($ihistoryArr->entry_type=="NR") && ($ihistoryArr->plan_id!="13")){
        if(amountFormat2($ihistoryArr->deposit_fee) > (amountFormat2(1.9*$ihistoryArr->monthly_fee))){
            return 'Two month deposit';
        }else{
            return 'One month deposit';
        }        
    }else{
        return NULL;
    }    
}

// Return daily charged customer status
function returnLatestDepositFee($custID){
    $ihistory = new InternetHistory;
    $depositAmountArr = $ihistory->select('deposit_fee')->where('customer_id',$custID)->where('entry_type', 'CP')->orderBy('start_date_time', 'desc')->get()->first();
    if(is_object($depositAmountArr)){
        $depositFee = $depositAmountArr->deposit_fee;
    }else{
        $depositFeeArr = $ihistory->select('deposit_fee')->where('customer_id',$custID)->where('entry_type', 'NR')->get()->first();
        $depositFee = $depositFeeArr->deposit_fee;
    } 
    return  amountFormat2($depositFee);
}

// Return  Balance , VAT & Total Amount
function balanceVatTotalAmountByDaysAndMonthlyFee($days,$monthly_fee){
    $response_array= array();
    $total_amount= amountFormat(($monthly_fee*$days)/env('MONTHLY_DAYS_COUNT'));
    $balance= amountFormat($total_amount/1.1);
	$vat_amount= amountFormat($total_amount - $balance);
    return $response_array= ['balance'=>($balance), 'vat_amount'=>($vat_amount), 'total_amount'=>($total_amount)];
}

// Return  Balance , VAT & Total Amount
function balanceVatTotalAmountByDays($startDate, $endDate, $monthly_fee){
    $response_array= array();
    $days= daysBetweenTwoDatesForInternet($startDate, $endDate); 
    $total_amount= amountFormat(($monthly_fee*$days)/env('MONTHLY_DAYS_COUNT'));
    $balance= amountFormat($total_amount/1.1);
	$vat_amount= amountFormat($total_amount - $balance);
    return $response_array= ['balance'=>($balance), 'vat_amount'=>($vat_amount), 'total_amount'=>($total_amount)];
}

// Return  Balance , VAT & Total Amount for Terminate & Suspend
function balanceVatTotalAmountSuspendAndTerminate($startDate, $endDate, $monthly_fee){
    $response_array= array();
    $days= daysBetweenTwoDates($startDate, $endDate);
    $total_amount= amountFormat(($monthly_fee*$days)/env('MONTHLY_DAYS_COUNT'));
    $balance= amountFormat($total_amount/1.1);
    $vat_amount= amountFormat($total_amount - $balance);
    return $response_array= ['balance'=>($balance), 'vat_amount'=>($vat_amount), 'total_amount'=>($total_amount)];
}

// Return Total Internet Customers Weekly Count
function totalWeeklyCustomers($type){
    $startDate= Carbon::now()->startOfWeek()->isoFormat('YYYY-MM-DD');
    $endDate= Carbon::now()->endOfWeek()->isoFormat('YYYY-MM-DD');    
    $ihistory = new InternetHistory;
    $total= $ihistory->select('customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->where('entry_type', '=', $type)->count();
    return $total;
}

// Return Total Internet Customers Monthly Count
function totalMonthlyCustomers($type){
    $startDate= Carbon::now()->month(currentMonth())->day(1)->isoFormat('YYYY-MM-DD');
    $endDate= Carbon::now()->month(currentMonth())->endOfMonth()->isoFormat('YYYY-MM-DD');    
    $ihistory = new InternetHistory;
    $total= $ihistory->select('customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->where('entry_type', '=', $type)->count();
    return $total;
}

// Return Total Internet Customers Yearly Count
function totalYearlyCustomers($type,$monthNo){
    $startDate= Carbon::now()->month($monthNo)->day(1)->isoFormat('YYYY-MM-DD');
    $endDate= Carbon::now()->month($monthNo)->endOfMonth()->isoFormat('YYYY-MM-DD');    
    $ihistory = new InternetHistory;
    $total= $ihistory->select('customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->where('entry_type', '=', $type)->count();
    return $total;
}

// Return Total Internet Yearly Revenue
function totalInternetYearlyRevenue($monthNo){
    $startDate= Carbon::now()->month($monthNo)->day(1)->isoFormat('YYYY-MM-DD');
    $endDate= Carbon::now()->month($monthNo)->endOfMonth()->isoFormat('YYYY-MM-DD');    
    $ihistory = new InternetHistory;
    $total= $ihistory->select('total_amount')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->whereNotIn('entry_type',['SS','TS'])->where('paid', 'Y')->where('payment_by', 'CP')->sum('total_amount');
    return amountFormat2($total);
}

// Return Total Paid /Not PaidInternet Customers Count
function paidNotPaidInternetMonthlyCustomers($paidStatus, $date){
        $totalCount = DB::table('internet_history')
        ->select('customer_id')
        ->where('entry_type','MP')
        ->where('paid',$paidStatus)
        ->whereDate('monthly_invoice_date','=',$date)
        ->count();
    return $totalCount;
}

// Return Total Internet Customers Count Building Name
function totalCustomerCountByBuildingName($buildingName,$custType,$startDate=null,$endDate=null){    
    $startDate= empty($startDate) ? internetStartMonthlyPaymentDate(currentMonthYear()) : $startDate;
    $endDate= empty($endDate) ? previousDateOFGivenDate(internetEndMonthlyPaymentDate(currentMonthYear())) : $endDate;
    //DB::enableQueryLog();
    $totalCount = DB::table('internet_history')
            ->join('units_address', 'internet_history.address_id', '=', 'units_address.id')
            ->join('buildings_location', 'buildings_location.id', '=', 'units_address.location_id')
            ->select('internet_history.customer_id')
            ->where('buildings_location.name',$buildingName)
            ->where('internet_history.entry_type',$custType)
            ->whereDate('internet_history.start_date_time','>=',$startDate)
            ->whereDate('internet_history.start_date_time','<=',$endDate)
            ->count();
    //$sql= DB::getQueryLog();
    //dd($sql);
    
    return $totalCount;
}

// Return Total Active Internet Subscribers Count Building Type
function totalSubscribersCountByBuildingType($projectName,$buildingType){   
    //DB::enableQueryLog();
    $customerCount = DB::select('select count(`customer_id`) as `active_subscribers` from `internet_history` left join `units_address` on `internet_history`.`address_id` =  `units_address`.`id` left join `buildings_location` on `units_address`.`location_id` =  `buildings_location`.`id` where `location`="'.$projectName.'" and `type`="'.$buildingType.'" and (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `internet_history` group by `customer_id`) and `customer_id` not in (select `customer_id` from `internet_history` where `entry_type`="TS") order by `address_id`');
    //$sql= DB::getQueryLog();
    //dd($sql);
    return $customerCount[0]->active_subscribers;
}

// Return Total Internet Customers Count Building Type
function totalCustomerCountByBuildingType($projectName,$buildingType,$custType,$startDate=null,$endDate=null){   
    $startDate= empty($startDate) ? internetStartMonthlyPaymentDate(currentMonthYear()) : $startDate;
    $endDate= empty($endDate) ? previousDateOFGivenDate(internetEndMonthlyPaymentDate(currentMonthYear())) : $endDate; 
    $totalCount = DB::table('internet_history')
            ->join('units_address', 'internet_history.address_id', '=', 'units_address.id')
            ->join('buildings_location', 'buildings_location.id', '=', 'units_address.location_id')
            ->select('internet_history.customer_id')
            ->where('buildings_location.location',$projectName)
            ->where('buildings_location.type',$buildingType)
            ->where('internet_history.entry_type',$custType)
            ->whereDate('internet_history.start_date_time','>=',$startDate)
            ->whereDate('internet_history.start_date_time','<=',$endDate)
            ->count();
    return $totalCount;
}

// Return Total Internet Customers Monthly Count
function totalCustomerCountByMonth($custType){    
    $totalCount = DB::table('internet_history')
            ->join('units_address', 'internet_history.address_id', '=', 'units_address.id')
            ->join('buildings_location', 'buildings_location.id', '=', 'units_address.location_id')
            ->select('internet_history.customer_id')
            ->where('internet_history.entry_type',$custType)
            ->whereDate('internet_history.start_date_time','>=',internetStartMonthlyPaymentDate(currentMonthYear()))
            ->whereDate('internet_history.start_date_time','<=',previousDateOFGivenDate(internetEndMonthlyPaymentDate(currentMonthYear())))
            ->count();
    return $totalCount;
}

// Return Internet History Details by ID
function ihistoryDetailsByID($id){
    $ihistory = new InternetHistory;
    $ihistoryArr= $ihistory->where('id', $id)->first();
    if($ihistoryArr){
        return $ihistoryArr;
    }else{
        return false;
    }
}

// Return New Connection, Reconnect & Change Location Row
function ihistoryFeeByMonthYear($custID, $addressID, $startDate, $endDate){
    $ihistory = new InternetHistory;	
	$previousInvoiceArr= $ihistory->where('customer_id', $custID)->whereDate('monthly_invoice_date', $startDate)->where('entry_type', 'MP')->get();
		
	if(is_object($previousInvoiceArr) && count($previousInvoiceArr)>0)
	{
		$ihistoryArr= $ihistory->where('customer_id', $custID)->whereDate('start_date_time', '>' , $startDate)->whereDate('start_date_time', '<=' , $endDate)->whereIn('entry_type', ['RC', 'CL'])->orderByDesc('start_date_time')->get();
	}else
	{
		$ihistoryArr= $ihistory->where('customer_id', $custID)->where('address_id', $addressID)->whereDate('start_date_time', '>=' , $startDate)->whereDate('start_date_time', '<=' , $endDate)->whereIn('entry_type', ['NR', 'RC', 'CL'])->orderByDesc('start_date_time')->get();
	}
	
	if(is_object($ihistoryArr) && count($ihistoryArr)>0){
		return $ihistoryArr;
	}else{
		return false;
	}
}

// Return Internet Plan Name by Plan ID
function planNameByPlanID($id){
    $iservices = new InternetService;
    $iPlanArr= $iservices->select('service_name')->where('id', $id)->first();
    if($iPlanArr){
        return $iPlanArr->service_name;
    }else{
        return false;
    }
}

// Return Internet Plan Details by Plan ID
function planDetailsByPlanID($id){
    $iservices = new InternetService;
    $iPlanArr= $iservices->where('id', $id)->first();
    if($iPlanArr){
        return $iPlanArr;
    }else{
        return false;
    }
}

// Return Internet Registration Date
function internetRegisterDateByCustID($customerID){
    $ihistory = new InternetHistory;
    $ihistoryArr= $ihistory->select('registration_date')->where('customer_id', $customerID)->where('entry_type', 'NR')->first();
    if($ihistoryArr){
        return $ihistoryArr->registration_date;
    }else{
        return false;
    }
}

// Return Internet Deactive Date
function internetDeactiveDateByCustID($customerID){
    $ihistory = new InternetHistory;
    $ihistoryArr= $ihistory->select('start_date_time')->where('customer_id', $customerID)->whereIn('entry_type', ['TS','SS'])->first();
    if($ihistoryArr){
        return $ihistoryArr->start_date_time;
    }else{
        return false;
    }
}


###################################################################
##                                                               ##
##                       End Internet                            ##
##                                                               ##
###################################################################

###################################################################
##                                                               ##
##                           Customer                            ##
##                                                               ##
###################################################################

// Return Customer Name by their ID
function customerNameByID($id){
    $customer = new Customer;
    $customerArr= $customer->select('name')->where('id', $id)->first();
    if($customerArr){
        return $customerArr->name;
    }else{
        return false;
    }
}

// Return Internet ID
function newInternetCustomerID(){
    $customer = new Customer;
    $internetID= $customer->max('internet_id');   
    return ($internetID+1); //+1 for new customer
}

// Return CableTV ID
function newCableTVCustomerID(){
    $customer = new Customer;
    $CableTVID= $customer->max('cabletv_id');   
    return ($CableTVID+1); //+1 for new customer
}

// Return Customer Details by their ID
function customerDetailsByID($id){
    $customer = new Customer;
    $customerArr= $customer->where('id', $id)->first();
    if($customerArr){
        return $customerArr;
    }else{
        return false;
    }
}

// Return Customer Details by their InternetID
function customerDetailsByInternetID($internetID)
{
    $customer = new Customer;
    $subscriberArr = [];
    $customerArr = $customer->where('internet_id', $internetID)->first();
    
    if(is_object($customerArr)){
        $subscriberArr['address'] = buildingLocationAndUnitNumberByUnitID($customerArr->address_id);
        $subscriberArr['name'] = $customerArr->name;
    }else{
        $subscriberArr['address'] = '-';
        $subscriberArr['name'] = '-';
    }  
    return $subscriberArr;
}

// Return Customer Number by their ID
function customerNumberByID($id){      
    return sprintf("%04s",$id);
}

// Return FullForm by Application Type Abbreviation
function customerApplicationTypeAbbreviationToFullName($type){
    switch($type){
        case 'P':
            $result= ['icon'=>'fas fa-user', 'color'=>'info', 'full'=>'Personal Application'];
        break;
 
        case 'S':
            $result= ['icon'=>'fas fa-store', 'color'=>'primary', 'full'=>'Shop Application'];
        break;
 
        default:
            $result= [''];
    }
    return $result;
}

// Return FullForm by Customer Sex Abbreviation
function customerSexAbbreviationToFullName($type){
    switch($type){
        case 'M':
            $result= 'Male';
        break;
 
        case 'F':
            $result= 'Female';
        break;

        case 'O':
            $result= 'Other';
        break;
 
        default:
            $result= '';
    }
    return $result;
}

// Return Invoice Number
function monthlyInvoiceNumber($dateString,$id){    
    return 'IC-'.Carbon::parse($dateString)->isoFormat('YYYY').'-'.Carbon::parse($dateString)->isoFormat('MM').sprintf("%04s",$id);
}

// Return Invoice Name
function invoiceName($invoiceNumber, $invoiceDate=null){    
    if ($invoiceDate) {
        return Carbon::parse($invoiceDate)->isoFormat('YYYYMMDD').'_'.($invoiceNumber);
    } else {
        return $invoiceNumber;
    }
}

// Return x cordinate by digit count
function monthlyInvoiceXCordinate($amount, $column){    
    $digitCount = strlen((int)amountFormat2($amount));
    switch($digitCount){
        case 1:
            $x= 175.8+$column;
        break;
 
        case 2:
            $x= 174+$column;
        break;

        case 3:
            $x= 172.2+$column;
        break;

        case 4:
            $x= 170.4+$column;
        break;

        case 5:
            $x= 168.6+$column;
        break;

        case 6:
            $x= 171.2+$column;
        break;
 
        default:
            $x= 169.3+$column;
    }
    return $x;
    
}

###################################################################
##                                                               ##
##                       End Customer                            ##
##                                                               ##
###################################################################


###################################################################
##                                                               ##
##                           Cable TV                            ##
##                                                               ##
###################################################################

// Return Days Between Two Dates Including Last Day//Y-m-d H:s:i #not in use
function daysBetweenTwoDatesForCableTV($startDate, $endDate){
    $startDate= Carbon::createFromFormat('Y-m-d',Carbon::parse($startDate)->format('Y-m-d'));
    $endDate= Carbon::createFromFormat('Y-m-d',Carbon::parse($endDate)->format('Y-m-d'));
    $days= ($startDate->diffInDays($endDate)+1); //+1= include end day
    if(monthByGivenDate($startDate)=='02' && $days>='28'){ $days= env('MONTHLY_DAYS_COUNT'); }
        return $days;
}


// Return Date (dd-mmm-yy)
function cableTVExportDateFormat($dateString){
    $date = Carbon::parse($dateString)->isoFormat('DD-MMM-YY');
    return $date;
}

// Return Cable TV Current Month Start Date (yyyy-mm-01)
function cableTVCurrentMonthStartDate(){
    $date = Carbon::now()->startOfMonth()->toDateString();
        return $date;
}

// Return Cable TV Current Month End Date (yyyy-mm-30/31)
function cableTVCurrentMonthEndDate(){
    $date = Carbon::now()->endOfMonth()->toDateString();
        return $date;
}

// Return CableTV Subscribe End Date (yyyy-mm-dd)
function cableTVSubscribeEndDate($startDate,$months){
    $endDate= Carbon::parse($startDate)->addMonth($months)->endOfMonth()->isoFormat('YYYY-MM-DD');  
        return $endDate;
}

// Return Cable TV Start Monthly Payment Date (yyyy-(mm-1)-01)
function cableTVGenerateStartMonthlyPaymentDate($monthYear){
    $monthYear= Carbon::createFromFormat('d-m-Y', '01-'.previousMonthYear($monthYear))->isoFormat('DD-MM-YYYY');
    $date = Carbon::createFromFormat('d-m-Y',$monthYear)->startOfMonth()->toDateString();
        return $date;
}

// Return Cable TV End Monthly Payment Date (yyyy-(mm-1)-31)
function cableTVGenerateEndMonthlyPaymentDate($monthYear){
    $monthYear= Carbon::createFromFormat('d-m-Y', '01-'.previousMonthYear($monthYear))->isoFormat('DD-MM-YYYY');
    $date = Carbon::createFromFormat('d-m-Y',$monthYear)->endOfMonth()->toDateString();
        return $date;
}

// Return Cable TV Start Monthly Payment Date (yyyy-mm-01)
function cableTVStartMonthlyPaymentDate($monthYear){
    $monthYear= Carbon::createFromFormat('d-m-Y', '01-'.$monthYear)->isoFormat('DD-MM-YYYY');
    $date = Carbon::createFromFormat('d-m-Y',$monthYear)->startOfMonth()->toDateString();
        return $date;
}

// Return Cable TV End Monthly Payment Date (yyyy-mm-31)
function cableTVEndMonthlyPaymentDate($monthYear){
    $monthYear= Carbon::createFromFormat('d-m-Y', '01-'.$monthYear)->isoFormat('DD-MM-YYYY');
    $date = Carbon::createFromFormat('d-m-Y',$monthYear)->endOfMonth()->toDateString();
        return $date;
}

// Return Cable TV Given Month Start Date (01-mm-yyyy)
function cableTVGivenMonthStartDate($dateString){
    $dateString= Carbon::parse($dateString)->isoFormat('DD-MM-YYYY');
    $date = Carbon::createFromFormat('d-m-Y',$dateString)->startOfMonth()->toDateString();
        return $date;
}

// Return Cable TV Given Month End Date (30/31-mm-yyyy)
function cableTVGivenMonthEndDate($dateString){
    $dateString= Carbon::parse($dateString)->isoFormat('DD-MM-YYYY');
    $date = Carbon::createFromFormat('d-m-Y',$dateString)->endOfMonth()->toDateString();
        return $date;
}

// Return CableTV Invoice Number
function cableTVMonthlyInvoiceNumber($dateString,$id){    
    return 'CT-'.Carbon::parse($dateString)->isoFormat('YYYY').'-'.Carbon::parse($dateString)->isoFormat('MM').sprintf("%04s",$id);
}

// Return CableTV History Details by ID
function cTVHistoryDetailsByID($id){
    $ctvhistory = new CableTVHistory;
    $ctvhistoryArr= $ctvhistory->where('id', $id)->first();
    if($ctvhistoryArr){
        return $ctvhistoryArr;
    }else{
        return false;
    }
}

// Return CableTV Registration Date
function cableTVRegisterDateByCustID($customerID){
    $ctvhistory = new CableTVHistory;
    $ctvhistoryArr= $ctvhistory->select('registration_date')->where('customer_id', $customerID)->where('entry_type', 'NR')->first();
    if($ctvhistoryArr){
        return $ctvhistoryArr->registration_date;
    }else{
        return false;
    }
}

// Return CableTV History Registration Details
function cableTVRegArrByCustID($customerID){
    $ctvhistory = new CableTVHistory;
    $ctvhistoryArr= $ctvhistory->where('customer_id', $customerID)->where('entry_type', 'NR')->first();
    if($ctvhistoryArr){
        return $ctvhistoryArr;
    }else{
        return false;
    }
}


// Return Cable TV Plan Name by ID
function cableTVPlanNameByID($id){
    $ctvservice = new CableTVService;
    $cPlanArr= $ctvservice->select('plan_name')->where('id', $id)->first();
    if($cPlanArr){
        return $cPlanArr->plan_name;
    }else{
        return false;
    }
}

// Return Cable TV Plan Details by ID
function cableTVPlanDetailsByID($id){
    $ctvservice = new CableTVService;
    $cPlanArr= $ctvservice->where('id', $id)->first();
    if($cPlanArr){
        return $cPlanArr;
    }else{
        return false;
    }
}

// Return CableTV Subscribers Current Status 
function currentCableTVSubscribersStatusByID($custID){
    $ctvhistory = new CableTVHistory;
    $cTVStatusArr = $ctvhistory->select('id','entry_type','plan_level')->where('customer_id',$custID)->orderByDesc('start_date_time')->first();
    if(in_array($cTVStatusArr->entry_type,['TS'])){
        return 'Deactive'; 
    }elseif($cTVStatusArr->entry_type=='MP' && $cTVStatusArr->plan_level=='DOWNGRADE'){
        return 'Deactive';
    }else{
        return 'Active';
    }
}

// Return CableTV Subscribers Period Count 
function cableTVPeriodCountByID($custID){
    $ctvhistory = new CableTVHistory;
    $ctvPeriodCount = $ctvhistory->select('period')->where('customer_id',$custID)->where('entry_type','MP')->orderBy('start_date_time', 'desc')->sum('period');
    return $ctvPeriodCount;
}

// Return Not Paid Cable TV Invoices Count
function notPaidCableTVCount($custID,$address_id){
    $ctvhistory = new CableTVHistory;
    $notPaidCount = $ctvhistory->select('id')->where('customer_id',$custID)->where('address_id',$address_id)->where('paid','N')->where('entry_type','MP')->orderByDesc('start_date_time')->count();
    return $notPaidCount;
}

// Return CableTV Plan Name by Plan ID
function cableTVPlanNameByPlanID($id){
    $ctvservice = new CableTVService;
    $cPlanArr= $ctvservice->select('plan_name')->where('id', $id)->first();
    if($cPlanArr){
        return $cPlanArr->plan_name;
    }else{
        return false;
    }
}

// Return CableTV Plan Details by Plan ID
function cableTVPlanDetailsByPlanID($id){
    $ctvservice = new CableTVService;
    $cPlanArr= $ctvservice->where('id', $id)->first();
    if($cPlanArr){
        return $cPlanArr;
    }else{
        return false;
    }
}

// Return Payment Date From to To
function fromToCableTVMonthlyInvoiceDate($startDate){
    return Carbon::parse($startDate)->isoFormat('MMM DD').' to '.Carbon::parse(cableTVGivenMonthEndDate($startDate))->isoFormat('MMM DD, YYYY');
}

// Return Total CableTV Customers
function totalActiveCableTVSubscribers(){
    $subscriberCount = DB::select('select count(`customer_id`) as `active_subscribers` from `cabletv_history` where (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `cabletv_history` group by `customer_id`) and `entry_type` not in ("TS")');
    return $subscriberCount[0]->active_subscribers;
}

// Return Total Active CableTV Subscribers Count By Building Type
function activeCableTVSubscribersByBuildingType($projectName,$buildingType){   
    //DB::enableQueryLog();
    $customerCount = DB::select('select count(`customer_id`) as `active_subscribers` from `cabletv_history` left join `units_address` on `cabletv_history`.`address_id` =  `units_address`.`id` left join `buildings_location` on `units_address`.`location_id` =  `buildings_location`.`id` where `location`="'.$projectName.'" and `type`="'.$buildingType.'" and (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `cabletv_history` group by `customer_id`) and `entry_type` !="TS"');
    //$sql= DB::getQueryLog();
    //dd($sql);
    return $customerCount[0]->active_subscribers;
}

// Return Total CableTV Amount
function totalCableTVAmountMonthly(){

    $ctvhistory = new CableTVHistory;
    $startDate = cableTVCurrentMonthStartDate();
    $endDate = cableTVCurrentMonthEndDate();        
    $total_amount= $ctvhistory->select('total_amount')->where('payment_by','CP')->where('paid','Y')->whereNotIn('entry_type',['SS','TS'])->whereDate('start_date_time','>=', $startDate)->whereDate('start_date_time','<=',$endDate)->sum('total_amount');
    return ['startDate'=>pdfDateFormat($startDate) , 'endDate'=>pdfDateFormat($endDate) , 'total_amount'=>amountFormat2($total_amount)];
}


// Return Total Paid /Not Paid CableTV Customers Count
function paidNotPaidCableTVMonthlyCustomers($paidStatus, $date){
    $totalCount = DB::table('cabletv_history')
    ->select('customer_id')
    ->where('entry_type','MP')
    ->where('paid',$paidStatus)
    ->whereDate('monthly_invoice_date','=',$date)
    ->count();
return $totalCount;
}

// Return Total CableTV Customers Count Building Name
function totalCableTVCustomerCountByBuildingName($buildingName,$custType,$startDate=null,$endDate=null){
    $startDate= empty($startDate) ? cableTVStartMonthlyPaymentDate(currentMonthYear()) : $startDate;
    $endDate= empty($endDate) ? cableTVEndMonthlyPaymentDate(currentMonthYear()) : $endDate;
    //pad($startDate.' && '.$endDate);
    //DB::enableQueryLog();
    $totalCount = DB::table('cabletv_history')
            ->join('units_address', 'cabletv_history.address_id', '=', 'units_address.id')
            ->join('buildings_location', 'buildings_location.id', '=', 'units_address.location_id')
            ->select('cabletv_history.customer_id')
            ->where('buildings_location.name',$buildingName)
            ->where('cabletv_history.entry_type',$custType)
            ->whereDate('cabletv_history.start_date_time','>=',$startDate)
            ->whereDate('cabletv_history.start_date_time','<=',$endDate)
            ->count();
    //$sql= DB::getQueryLog();
    //dd($sql);
    return $totalCount;
}

// Return Total CableTV Customers Count Building Type
function totalCableTVCustomerCountByBuildingType($projectName,$buildingType,$custType,$startDate=null,$endDate=null){
    
    $startDate= empty($startDate) ? cableTVStartMonthlyPaymentDate(currentMonthYear()) : $startDate;
    $endDate= empty($endDate) ? cableTVEndMonthlyPaymentDate(currentMonthYear()) : $endDate;

    $totalCount = DB::table('cabletv_history')
            ->join('units_address', 'cabletv_history.address_id', '=', 'units_address.id')
            ->join('buildings_location', 'buildings_location.id', '=', 'units_address.location_id')
            ->select('cabletv_history.customer_id')
            ->where('buildings_location.location',$projectName)
            ->where('buildings_location.type',$buildingType)
            ->where('cabletv_history.entry_type',$custType)
            ->whereDate('cabletv_history.start_date_time','>=',$startDate)
            ->whereDate('cabletv_history.start_date_time','<=',$endDate)
            ->count();
    return $totalCount;
}

// Return Total CableTV Customers Count By Month
function totalCableTVCustomerCountByMonth($custType){
    
    $totalCount = DB::table('cabletv_history')
            ->join('units_address', 'cabletv_history.address_id', '=', 'units_address.id')
            ->join('buildings_location', 'buildings_location.id', '=', 'units_address.location_id')
            ->select('cabletv_history.customer_id')
            ->where('cabletv_history.entry_type',$custType)
            ->whereDate('cabletv_history.start_date_time','>=',cableTVStartMonthlyPaymentDate(currentMonthYear()))
            ->whereDate('cabletv_history.start_date_time','<=',previousDateOFGivenDate(cableTVEndMonthlyPaymentDate(currentMonthYear())))
            ->count();
    return $totalCount;
}

// Return CableTV days fee
function cableTVMonthlyFee($startDate, $monthly_fee){

    $day = intval(Carbon::parse($startDate)->isoFormat('D'));
    switch ($day) {
        case 1 :
        case 2 :
            $fee = $monthly_fee;
            break;
        case 3 :
        case 4 :
            $fee = $monthly_fee-1;
            break;
        case 5 :
        case 6 :
        case 7 :
            $fee = $monthly_fee-2;
            break;
        case 8 :
        case 9 :
        case 10 :
            $fee = $monthly_fee-3;
            break;
        case 11 :
        case 12 :
        case 13 :
            $fee = $monthly_fee-4;
            break;
        case 14 :
        case 15 :
        case 16 :
            $fee = $monthly_fee-5;
            break;
        case 17 :
        case 18 :
        case 19 :
            $fee = 4;
            break;
        case 20 :
        case 21 :
        case 22 :
            $fee = 3;
            break;
        case 23 :
        case 24 :
        case 25 :
            $fee = 2;
            break;
        case 26 :
        case 27 :
        case 28 :
        case 29 :       
            $fee = 1;
            break;
        default:
            $fee = 0;
    }
    return $fee;
}

// Return Total CableTV Tab Customers Count
function totalCableTVTabCustomers($tabType,$monthNo,$custType){
    
    if($tabType=="W"){
        $startDate= Carbon::now()->startOfWeek()->isoFormat('YYYY-MM-DD');
        $endDate= Carbon::now()->endOfWeek()->isoFormat('YYYY-MM-DD');
    }elseif($tabType=="M"){
        $startDate= Carbon::now()->month(currentMonth())->day(1)->isoFormat('YYYY-MM-DD');
        $endDate= Carbon::now()->month(currentMonth())->endOfMonth()->isoFormat('YYYY-MM-DD');
    }else{
        $startDate= Carbon::now()->month($monthNo)->day(1)->isoFormat('YYYY-MM-DD');
        $endDate= Carbon::now()->month($monthNo)->endOfMonth()->isoFormat('YYYY-MM-DD');
    }
    $ctvhistory = new CableTVHistory;
    $total= $ctvhistory->select('customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->where('entry_type', '=', $custType)->count();
    return $total;
}

// Return Cable TV Yearly Revenue
function cableTVYearlyRevenue($monthNo){
    $startDate= Carbon::now()->month($monthNo)->day(1)->isoFormat('YYYY-MM-DD');
    $endDate= Carbon::now()->month($monthNo)->endOfMonth()->isoFormat('YYYY-MM-DD');
    $ctvhistory = new CableTVHistory;
    $total= $ctvhistory->select('total_amount')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->whereNotIn('entry_type',['SS','TS'])->where('paid', 'Y')->where('payment_by', 'CP')->sum('total_amount');
    return amountFormat2($total);
}

###################################################################
##                                                               ##
##                       End Cable TV                            ##
##                                                               ##
###################################################################

###################################################################
##                                                               ##
##                             User                              ##
##                                                               ##
###################################################################

// Return User Name by their ID
function userNameByUserID($id){
    $user = new User;
    $userArr= $user->select('name')->where('id', $id)->first();
    if($userArr){
        return $userArr->name;
    }else{
        return false;
    }
}

###################################################################
##                                                               ##
##                           End User                            ##
##                                                               ##
###################################################################

###################################################################
##                                                               ##
##                         Admin User                            ##
##                                                               ##
###################################################################

// Return Admin Name by their ID
function adminNameByAdminID($id){
    $admin = new Admin;
    $adminArr= $admin->select('name')->where('id', $id)->first();
    if($adminArr){
        return $adminArr->name;
    }else{
        return false;
    }
}

###################################################################
##                                                               ##
##                       End Admin User                          ##
##                                                               ##
###################################################################

###################################################################
##                                                               ##
##                           Report                              ##
##                                                               ##
###################################################################

// Return Internet Subscriber Count  
function internetSubscriberByAddress($addressID,$startDate,$endDate){
    $ihistory = new InternetHistory;
    $total= $ihistory->select('customer_id')->where('address_id', $addressID)->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->first();
    if($total){
        return 1;
    }else{
        return null;
    }
}

// Return Internet Subscriber Monthly Fee 
function internetSubscriberMonthlyFee($addressID,$startDate,$endDate){
    $ihistory = new InternetHistory;
    $Arr= $ihistory->select('monthly_fee')->where('address_id', $addressID)->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->first();
    if($Arr){
        return $Arr->monthly_fee;
    }else{
        return null;
    }
}

// Return Unpaid Internet Subscriber Count  
function internetUnpaidSubscriberByAddress($addressID,$startDate,$endDate){
    $ihistory = new InternetHistory;
    $total= $ihistory->select('customer_id')->where('address_id', $addressID)->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->where('paid', "N")->first();
    if($total){
        return 1;
    }else{
        return null;
    }
}

// Return Internet Subscriber Unpaid Fee 
function internetSubscriberUnpaidFee($addressID,$endDate){
    $ihistory = new InternetHistory;
    $total= $ihistory->select('total_amount')->where('address_id', $addressID)->whereDate('start_date_time','<=', $endDate)->where('paid', "N")->sum('total_amount');
    return $total;
}

// Return Monthly Invoice Start & End Number  
function getStartAndEndInvoiceNumberByMonth($startDate,$endDate){
    $ihistory = new InternetHistory;
    $invoiceNumber= $ihistory->select(DB::raw('min(invoice_number) as start_invoice, max(invoice_number) as end_invoice'), 'monthly_invoice_date')->where('entry_type', 'MP')->whereDate('start_date_time','>=', $startDate)->whereDate('start_date_time','<=', $endDate)->get()->first()->toArray();
    return invoiceName($invoiceNumber['start_invoice'], $invoiceNumber['monthly_invoice_date']).' ~ '.substr($invoiceNumber['end_invoice'],8);
}

// Return Monthly Invoice Amount  
function getInvoiceAmountByMonth($startDate,$endDate){
    $ihistory = new InternetHistory;
    $monthlyAmount= $ihistory->select('total_amount')->where('entry_type', 'MP')->whereDate('start_date_time','>=', $startDate)->whereDate('start_date_time','<=', $endDate)->sum('total_amount');
    return $monthlyAmount;
}

// Return Monthly Installation Amount  
function getInstallationAmountByMonth($startDate,$endDate){
    $ihistory = new InternetHistory;
    $installAmount= $ihistory->select('installation_fee')->where('entry_type', 'NR')->whereDate('start_date_time','>=', $startDate)->whereDate('start_date_time','<=', $endDate)->sum('installation_fee');
    return $installAmount;
}

// Return Monthly Service Amount  
function getServiceAmountByMonth($startDate,$endDate){
    $ihistory = new InternetHistory;
    $serviceAmount= $ihistory->select(DB::raw('sum(reinstallation_fee) as reinstallation_amount, sum(reconnect_fee) as reconnect_amount'))->whereIn('entry_type', ['CL','RC'])->whereDate('start_date_time','>=', $startDate)->whereDate('start_date_time','<=', $endDate)->get()->first()->toArray();
    return ($serviceAmount['reinstallation_amount']+$serviceAmount['reconnect_amount']);
}


// Return CableTV Subscriber Count  
function cableTVSubscriberByAddress($addressID,$startDate,$endDate){
    $ctvhistory = new CableTVHistory;
    $total= $ctvhistory->select('customer_id')->where('address_id', $addressID)->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->first();
    if($total){
        return 1;
    }else{
        return null;
    }
}

// Return CableTV Subscriber Monthly Fee  
function cableTVSubscriberMonthlyFee($addressID,$startDate,$endDate){
    $ctvhistory = new CableTVHistory;
    $Arr= $ctvhistory->select('monthly_fee')->where('address_id', $addressID)->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->first();
    if($Arr){
        return $Arr->monthly_fee;
    }else{
        return null;
    }
}

// Return Unpaid CableTV Subscriber Count  
function cableTVUnpaidSubscriberByAddress($addressID,$startDate,$endDate){
    $ctvhistory = new CableTVHistory;
    $total= $ctvhistory->select('customer_id')->where('address_id', $addressID)->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time','<=', $endDate)->where('paid', "N")->first();
    if($total){
        return 1;
    }else{
        return null;
    }
}

// Return CableTV Subscriber Unpaid Fee 
function cableTVSubscriberUnpaidFee($addressID,$endDate){
    $ctvhistory = new CableTVHistory;
    $total= $ctvhistory->select('total_amount')->where('address_id', $addressID)->whereDate('start_date_time','<=', $endDate)->where('paid', "N")->sum('total_amount');
    return $total;
}

###################################################################
##                                                               ##
##                          End Report                           ##
##                                                               ##
###################################################################

###################################################################
##                                                               ##
##                      Start Core Network                       ##
##                                                               ##
###################################################################

//Get Status & IP Address from active connection from switch 
function activeCoreSubscriberByInternetID($internetID)
{
    $statusArr = [];
    $config = (new Config())
    ->set('timeout', 1)
    ->set('host', env('CORE_NETWORK_HOST'))
    ->set('user', env('CORE_NETWORK_USER'))
    ->set('pass', env('CORE_NETWORK_PASSWORD'));
    $client = new Client($config);
    $query = new Query('/ppp/active/print');
    $query->where('name', $internetID);
    $activeConnectionArr = $client->query($query)->read();
    
    if(is_array($activeConnectionArr) && count($activeConnectionArr)>0)
    {
        $statusArr = @$activeConnectionArr[0];
        $statusArr['status'] = 'A';
        
    }
    else{
        $statusArr['status']= 'N';
        $statusArr['address']= '-';
        $statusArr['password']= '-';
        $statusArr['caller-id']= '-';
    }
    return $statusArr;
    
}

//Get Password from ppp secret from switch 
function passwordPlanCoreSubscriberByInternetID($internetID)
{
    $statusArr = [];
    $config = (new Config())
    ->set('timeout', 1)
    ->set('host', env('CORE_NETWORK_HOST'))
    ->set('user', env('CORE_NETWORK_USER'))
    ->set('pass', env('CORE_NETWORK_PASSWORD'));
    $client = new Client($config);
    $query = new Query('/ppp/secret/print');
    $query->where('name', $internetID);
    $activeConnectionArr = $client->query($query)->read();
    
    if(is_array($activeConnectionArr) && count($activeConnectionArr)>0)
    {
        $statusArr['password']  = @$activeConnectionArr[0]['password'];
        $statusArr['plan-name'] = @$activeConnectionArr[0]['profile'];        
    }
    else
    {        
        $statusArr['password']= '-';
        $statusArr['plan-name']= 'Expired-Alert';
    }
    return $statusArr;
    
}

###################################################################
##                                                               ##
##                       End Core Network                        ##
##                                                               ##
###################################################################