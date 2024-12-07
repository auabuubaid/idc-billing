<?php

namespace App\Exports;

use DB;
use App\Models\InternetHistory;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;

class ActiveInternetSubsWithSuspendExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $totalRows; 

    function __construct() {
        
        $countArr = (DB::select('select count(`customer_id`) as `total_subscribers` from `internet_history` where (`customer_id`, `start_date_time`) in (select `customer_id`, max(`start_date_time`) as `start_date_time` from `internet_history` group by `customer_id`) and `customer_id` not in (select `customer_id` from `internet_history` where `entry_type`="TS") order by `address_id`'));
        $this->totalRows = $countArr[0]->total_subscribers+1;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        //DB::enableQueryLog();
        $subscribersArr= DB::select('select `customer_id`, `address_id`, `entry_type`, `plan_id`, `plan_remark` from `internet_history` where (`customer_id`, `start_date_time`) in (select `customer_id`, max(`start_date_time`) as `start_date_time` from `internet_history` group by `customer_id`) and `customer_id` not in (select `customer_id` from `internet_history` where `entry_type`="TS") order by `address_id`');
        //$sql= DB::getQueryLog();
        //dd($sql);
        return collect($subscribersArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Suspend-And-Active-Internet-Subscribers';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true, 'color'=> ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '3464AB']],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                    ],
            
            'A1:E'.($this->totalRows) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            // 'F' => NumberFormat::FORMAT_TEXT,
            // 'G' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $style = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];
        
                $event->sheet->getRowDimension(1)->setRowHeight(25);
            },            
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            '#',
            'Customer Name',
            'Address',
            'Plan Name',
            'Remark',
            // 'CustomerID',
            // 'Internet Password',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($subsArr): array
    {   
        return [
            '=ROW()-1',
            (customerDetailsByID($subsArr->customer_id)['type']=='P')?customerDetailsByID($subsArr->customer_id)['name']:customerDetailsByID($subsArr->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($subsArr->address_id),
            planDetailsByPlanID($subsArr->plan_id)['plan_name'],
            internetSuspendOrActiveStatus($subsArr->entry_type, $subsArr->plan_remark),
            // customerDetailsByID($subsArr->customer_id)['internet_id'],
            // customerDetailsByID($subsArr->customer_id)['internet_password']
        ];        
    }
}
