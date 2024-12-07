<?php

namespace App\Http\Controllers;


use DB;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Customer;
use App\Models\UnitAddress;
use App\Models\CableTVHistory;
use App\Models\InternetHistory;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\WorldCitySummaryExport;

class SupervisorController extends Controller
{
    /**
     * Show Summary Report Page.
     *
     * @param  Request  $request
     * @return View
     */
    public function summary_report(Request $request){
        
        $user = new User;
        $unit_address = new UnitAddress;
        $ctvhistory = new CableTVHistory;
        $ihistory = new InternetHistory;
        $userArr= $user->where('id', $request->session()->get('userID'))->first();
  
        if(!checkPermission($userArr->read)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Read permission not granted!');
            return redirect()->route('dashboard');
        }
          
        $iReportArr=array();
        $unitsArr=array();
        
        if($request->input()){
          $buildingAddress= $request->input('building');
          $buildingArr= explode('|',$buildingAddress);
          $project=$buildingArr[0];
          $type=$buildingArr[1];
          $building=$buildingArr[2];
          $monthlyYear=$request->input('monthlyYear');
          $startDate= monthlyStartEndDate($monthlyYear)['startDate'];
          $endDate= monthlyStartEndDate($monthlyYear)['endDate'];
        }else{
          $buildingAddress='R1|A|A101';
          $project='R1';
          $type='A';
          $building='A101';
          $monthlyYear=currentYearMonth();
          $startDate= monthlyStartEndDate($monthlyYear)['startDate'];
          $endDate= monthlyStartEndDate($monthlyYear)['endDate'];
        }
        //echo $project.' * '.$type.' * '.$building.' * '.$monthlyYear.' * '.$startDate.' * '.$endDate;
        $buildingArr= [
          '0'=>['project'=>'R1','type'=>'A','building'=>'A101','name'=>'A101'],
          '1'=>['project'=>'R1','type'=>'A','building'=>'A102','name'=>'A102'],
          '2'=>['project'=>'R1','type'=>'A','building'=>'A103','name'=>'A103'],
          '3'=>['project'=>'R1','type'=>'A','building'=>'A104','name'=>'A104'],
          '4'=>['project'=>'R1','type'=>'A','building'=>'A105','name'=>'A105'],
          '5'=>['project'=>'R1','type'=>'A','building'=>'A106','name'=>'A106'],
          '6'=>['project'=>'R1','type'=>'A','building'=>'A107','name'=>'A107'],
          '7'=>['project'=>'R1','type'=>'A','building'=>'A108','name'=>'A108'],
          '8'=>['project'=>'R1','type'=>'T','building'=>'Town House','name'=>'Town House'],
          '9'=>['project'=>'R1','type'=>'V','building'=>'Villa','name'=>'Villa'],
          '10'=>['project'=>'R1','type'=>'S','building'=>"'SM-A','SM-B','SM-C','SM-D'",'name'=>'East Shops'],
          '11'=>['project'=>'R1','type'=>'S','building'=>"'RS-A','RS-B'",'name'=>'West Shops'],
          '12'=>['project'=>'R2','type'=>'V','building'=>'Villa','name'=>'Secret Garden']
        ];  
            if($type=='A'){
                $unitsArr= DB::table('units_address')
                    ->select('units_address.id','units_address.location_id','units_address.unit_number','units_address.sort_order')
                    ->join('buildings_location', 'buildings_location.id', '=', 'units_address.location_id')
                    ->where('buildings_location.location',$project)
                    ->where('buildings_location.type',$type)
                    ->where('buildings_location.name',$building)
                    ->get()->all();
            }elseif($type=='S'){
                //DB::enableQueryLog();
                $unitsArr= DB::select('select `units_address`.`id`,`units_address`.`location_id`,`units_address`.`unit_number`,`units_address`.`sort_order`
                    from `units_address` join `buildings_location` on `buildings_location`.`id`= `units_address`.`location_id`
                    where `buildings_location`.`location`="'.$project.'"
                    and `buildings_location`.`type`="'.$type.'"
                    and `buildings_location`.`name` in('.$building.')');
                    //$sql= DB::getQueryLog();
                    //dd($sql);
            }else{
                $unitsArr=  DB::table('units_address')
                    ->select('units_address.id','units_address.location_id','units_address.unit_number','units_address.sort_order')
                    ->join('buildings_location', 'buildings_location.id', '=', 'units_address.location_id')
                    ->where('buildings_location.location',$project)
                    ->where('buildings_location.type',$type)
                    ->get()->all();
            }
            
        $iReportArr= json_decode(json_encode($unitsArr), true);        
        
        if(is_array($iReportArr) && count($iReportArr)>0){
            foreach($iReportArr as $ikey=>$ival){
                $iReportArr[$ikey]['internet_subs']= internetSubscriberByAddress($ival['id'],$startDate,$endDate);
                $iReportArr[$ikey]['internet_total_fee']= internetSubscriberMonthlyFee($ival['id'],$startDate,$endDate);
                $iReportArr[$ikey]['internet_unpaid_subs']= internetUnpaidSubscriberByAddress($ival['id'],$startDate,$endDate);
                $iReportArr[$ikey]['internet_unpaid_fee']= internetSubscriberUnpaidFee($ival['id'],$endDate);
                $iReportArr[$ikey]['cabletv_subs']= cableTVSubscriberByAddress($ival['id'],$startDate,$endDate);
                $iReportArr[$ikey]['cabletv_total_fee']= cableTVSubscriberMonthlyFee($ival['id'],$startDate,$endDate);
                $iReportArr[$ikey]['cabletv_unpaid_subs']= cableTVUnpaidSubscriberByAddress($ival['id'],$startDate,$endDate);
                $iReportArr[$ikey]['cabletv_unpaid_fee']= cableTVSubscriberUnpaidFee($ival['id'],$endDate);
            }
        }
        //pa($iReportArr);
        return view('users.n.summary_report')
                    ->with(['userArr'=>$userArr, 'buildingArr'=>$buildingArr, 'building'=>$buildingAddress, 'reportArr'=>$iReportArr, 'monthlyYear'=>$monthlyYear]);
    }
   
    /**
     * Export Summary Report.
     *
     * @param  Request  $request
     * @return View
     */
    public function export_summary_report(Request $request){
        
        $user = new User;
        $userArr= $user->where('id', $request->session()->get('userID'))->first();
        
        if(!checkPermission($userArr->download)){
            // Permission not granted
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Download permission not granted!');
            return redirect()->route('summary-report');
        }
          
        if($request->input('monthYear')){
            $monthYear= $request->input('monthYear');
            $monthYearArr = explode('-',$monthYear);
            $year=$monthYearArr[0];
            $month=$monthYearArr[1];

            return Excel::download(new WorldCitySummaryExport($monthYear), 'WC Income Management Report for '.monthNumberToName($month).' '.$year.'.xlsx');
            
        }else{
            // Month Year not Selected
            Session::flash('color','warning');
            Session::flash('icon','fa fa-exclamation-triangle');
            Session::flash('msg','Please select month and year.');
            return redirect()->route('summary-report');
        }
        
    }
}
