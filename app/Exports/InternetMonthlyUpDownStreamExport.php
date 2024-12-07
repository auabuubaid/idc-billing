<?php

namespace App\Exports;

use DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;

class InternetMonthlyUpDownStreamExport implements  WithEvents, WithTitle
{
    protected $yearMonth;
    function __construct($yearMonth) {
        $this->yearMonth = $yearMonth;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->yearMonth.'_ISP_Upstream_Downstream';
    }

    public function registerEvents(): array
    {
        
        return [

            BeforeSheet::class => function(BeforeSheet $event) {     
                
                $downstreamBandWidthArr = DB::select('select `plan_id`, `plan_name`, `speed`, `speed_unit` from `internet_history` left join `internet_services` on `internet_history`.`plan_id` = `internet_services`.`id` where (`customer_id`, `start_date_time`) in ( select `customer_id`, max(`start_date_time`) as `start_date_time` from `internet_history` group by `customer_id`) and `customer_id` not in (select `customer_id` from `internet_history` where `entry_type`="SS" and `suspension_end_date` is NULL) and `customer_id` not in (select `customer_id` from `internet_history` where `entry_type`="TS")');
                $downstreamBandWidth = new Collection($downstreamBandWidthArr);
                $totalDownstreamBandWidth = amountFormat2($downstreamBandWidth->sum('speed')/1024); // divide by 1024 to convert to Gbps
        
                $event->sheet->setCellValue('A1','Operator Name:');
                $event->sheet->setCellValue('B1','Worldcity (Camko broadband)');

                $event->sheet->setCellValue('A3','Internet Backbone Capacity');

                $event->sheet->setCellValue('A4','No.');
                $event->sheet->setCellValue('B4','Provider Name');
                $event->sheet->setCellValue('C4','Type');
                $event->sheet->setCellValue('D4','Capacity');
                $event->sheet->setCellValue('E4','Remarks');

                $event->sheet->setCellValue('A5','1');
                $event->sheet->setCellValue('B5','CAMINTEL');
                $event->sheet->setCellValue('C5','Fiber');
                $event->sheet->setCellValue('D5','2Gbps');
                $event->sheet->setCellValue('E5','Load Balancing');

                $event->sheet->setCellValue('A6','2');
                $event->sheet->setCellValue('A7','3');

                $event->sheet->setCellValue('A8','ISP Direct Peering ');

                $event->sheet->setCellValue('A9','No.');
                $event->sheet->setCellValue('B9','Operator Name');
                $event->sheet->setCellValue('C9','ASN');
                $event->sheet->setCellValue('D9','Capacity');
                $event->sheet->setCellValue('E9','Remarks');

                $event->sheet->setCellValue('A10','1');
                $event->sheet->setCellValue('B10','');
                $event->sheet->setCellValue('C10','');
                $event->sheet->setCellValue('D10','');
                $event->sheet->setCellValue('E10','');

                $event->sheet->setCellValue('A11','2');
                $event->sheet->setCellValue('A12','3');

                $event->sheet->setCellValue('A14','Total Downstream Banwdith');
                $event->sheet->setCellValue('B14',$totalDownstreamBandWidth.'Gbps');
            }            
        ];
    }
}
