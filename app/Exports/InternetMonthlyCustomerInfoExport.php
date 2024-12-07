<?php

namespace App\Exports;

use DB;
use App\Models\InternetHistory;
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
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;

class InternetMonthlyCustomerInfoExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $totalRows;    

    function __construct() {
        $this->totalRows = (DB::table('internet_history')->select('customer_id')->groupBy('customer_id')->orderByDesc('start_date_time')->get()->count());
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyCustomerInfoArr= DB::select("select `internet_history`.`id`, `internet_history`.`customer_id`, `customers`.`name`, `customers`.`type`, `customers`.`internet_id`, `internet_history`.`address_id`, `internet_history`.`customer_mobile`, `internet_history`.`plan_id`, `internet_history`.`entry_type`, `internet_history`.`plan_remark` from `internet_history` left join `customers` on `internet_history`.`customer_id` = `customers`.`id` where `internet_history`.`id` in (select max(`internet_history`.`id`) from `internet_history` group by `internet_history`.`customer_id`) group by `internet_history`.`customer_id` order by `customers`.`internet_id` asc");
        return collect($monthlyCustomerInfoArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Customer Info';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]   
                    ],
            
            2    => ['font' => ['bold' => true]],

            2    => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]],
            
            'A1:K'.($this->totalRows+2) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['arIb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_XLSX15,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_DATE_XLSX15,
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $style = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];
        
                $event->sheet->mergeCells('A1:K1');
                $event->sheet->setCellValue('A1','Operator Name : World City Co., Ltd.');
                $event->sheet->getStyle('A1')->applyFromArray($style);
                $event->sheet->getRowDimension(1)->setRowHeight(50);
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'Register Date (DD-MM-YYYY)',
            'Customer ID',
            'Service ID/Account ID',
            'Name',
            'Customer Type (Firm or Individual)',
            'Address',
            'Location',
            'Bandwidth',
            'Service/Package Type (Tariff)',
            'Connection Type', 
            'Data Usage (Limited or Unlimited)',        
            'Status (Active, Deactive, Suspend)',
            'Deactivate Date (DD-MM-YYYY)',
        ];
    }
  
    /**
    * @return MappingWithHeadings
    */
    public function map($ihistory): array
    {   
        return [
            dmcDateFormat(internetRegisterDateByCustID($ihistory->customer_id)),
            ($ihistory->internet_id),
            '',
            $ihistory->name,
            ($ihistory->type=='P')? 'Individual' : 'Firm',
            unitAddressByUnitID($ihistory->address_id).' CamKo Street, Sangkat Toul Sangke 2, Khan Russey Keo, Phnom Penh, Cambodia - 120707',
            'Phnom Penh',
            planDetailsByPlanID($ihistory->plan_id)['speed'].' '.planDetailsByPlanID($ihistory->plan_id)['speed_unit'],
            planDetailsByPlanID($ihistory->plan_id)['plan_name'],
            'FTTH',
            LimitedUnlimitedAbbreviationToFull(planDetailsByPlanID($ihistory->plan_id)['data_usage']),
            currentInternetCustomersStatusByID($ihistory->customer_id),
            in_array(currentInternetCustomersStatusByID($ihistory->customer_id), ['Deactive', 'Suspend']) ? dmcDateFormat(internetDeactiveDateByCustID($ihistory->customer_id)) : '',
        ];
        
    }
}