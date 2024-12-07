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
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;

class InternetDMCSummaryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $previousDate;
    protected $invoiceDate;
    protected $totalRows;    

    function __construct($previousDate,$invoiceDate) {
        $this->previousDate = $previousDate;    
        $this->invoiceDate = $invoiceDate;
        $this->totalRows = (DB::table('internet_history')->select('customer_id')->whereDate('start_date_time', '>=', dataBaseFormat($this->previousDate))->whereDate('start_date_time', '<=', dataBaseFormat($this->invoiceDate))->whereIn('entry_type', ['NR','RC','CL','MP'])->orderByDesc('start_date_time')->get()->count());
        //$this->totalRows = (DB::table('internet_history')->select('customer_id')->whereDate('monthly_invoice_date', "=", $this->invoiceDate)->whereIn('entry_type',['NR','RC','CL','MP'])->orderByDesc('start_date_time')->get()->count());
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $monthlySummaryArr= DB::select("select `id`,`invoice_number`, `customer_id`, `entry_type`, `address_id`, `plan_id`,`start_date_time`, `monthly_invoice_date`, `installation_fee`, `reinstallation_fee`, `reconnect_fee`, `balance`, `vat_amount`, `total_amount`, `refrence_id` from `internet_history` where date(`start_date_time`) >='".dataBaseFormat($this->previousDate)."' and date(`start_date_time`) <='".dataBaseFormat($this->invoiceDate)."' and `entry_type` in ('NR','RC','CL','MP') order by `invoice_number` asc");
        return collect($monthlySummaryArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'DMC Summary Invoice';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']]   
                    ],
            // Style the first row as bold text.
            2    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']]   
                    ],

            'A1:K'.($this->totalRows+2) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['arIb' => '000000'], ],],],
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
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_CURRENCY_USD,
            'I' => NumberFormat::FORMAT_CURRENCY_USD,
            'J' => NumberFormat::FORMAT_CURRENCY_USD,
            'K' => NumberFormat::FORMAT_TEXT,
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

                $event->sheet->mergeCells('A1:K1');
                $event->sheet->setCellValue('A1','Internet  for '.monthByGivenDate2($this->invoiceDate).' '.yearByGivenDate($this->invoiceDate));
                $event->sheet->getStyle('A1')->applyFromArray($style);
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
            'No.',
            'Invoice',
            'Address',
            'Service Name',
            'Service Type',
            'Fixed or Wireless Internet',
            'Bandwith',            
            'Amount',
            'Vat',
            'Total',
            'Remark',
        ];
    }
  
    /**
    * @return MappingWithHeadings
    */
    public function map($monthlySummaryArr): array
    {   
        return [
            '=ROW()-2',
            ($monthlySummaryArr->entry_type=='MP') ? invoiceName($monthlySummaryArr->invoice_number, $monthlySummaryArr->monthly_invoice_date) : NULL,
            buildingLocationAndUnitNumberByUnitID($monthlySummaryArr->address_id),
            planDetailsByPlanID($monthlySummaryArr->plan_id)['plan_name'],
            internetTypeAbbreviationToFullName($monthlySummaryArr->entry_type),
            'Fixed Internet',            
            planDetailsByPlanID($monthlySummaryArr->plan_id)['speed'].' '.planDetailsByPlanID($monthlySummaryArr->plan_id)['speed_unit'],
            ($monthlySummaryArr->entry_type=='MP') ? amountFormat2($monthlySummaryArr->balance) : overviewAmountVatTotal($monthlySummaryArr->entry_type,$monthlySummaryArr->installation_fee,$monthlySummaryArr->reinstallation_fee,$monthlySummaryArr->reconnect_fee,$monthlySummaryArr->refrence_id)['amount'],
            ($monthlySummaryArr->entry_type=='MP') ? amountFormat2($monthlySummaryArr->vat_amount) : overviewAmountVatTotal($monthlySummaryArr->entry_type,$monthlySummaryArr->installation_fee,$monthlySummaryArr->reinstallation_fee,$monthlySummaryArr->reconnect_fee,$monthlySummaryArr->refrence_id)['vat'],
            ($monthlySummaryArr->entry_type=='MP') ? amountFormat2($monthlySummaryArr->total_amount) : overviewAmountVatTotal($monthlySummaryArr->entry_type,$monthlySummaryArr->installation_fee,$monthlySummaryArr->reinstallation_fee,$monthlySummaryArr->reconnect_fee,$monthlySummaryArr->refrence_id)['total'],
            ($monthlySummaryArr->entry_type=='MP') ? remarkForSuspendTerminateAndLocation($monthlySummaryArr->entry_type, $monthlySummaryArr->refrence_id) : overviewAmountVatTotal($monthlySummaryArr->entry_type,$monthlySummaryArr->installation_fee,$monthlySummaryArr->reinstallation_fee,$monthlySummaryArr->reconnect_fee,$monthlySummaryArr->refrence_id)['remark'],
        ];
        
    }
}