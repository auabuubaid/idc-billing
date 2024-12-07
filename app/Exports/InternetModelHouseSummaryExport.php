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

class InternetModelHouseSummaryExport implements WithMultipleSheets, WithPreCalculateFormulas
{
    protected $previousDate;
    protected $invoiceDate;
    protected $overViewRows;
    protected $invoiceRows;

    function __construct($previousDate, $invoiceDate) {
        $this->previousDate = $previousDate;
        $this->invoiceDate = $invoiceDate;
        $this->overViewRows = (DB::table('internet_history')->select('customer_id')->whereDate('start_date_time', '>=', dataBaseFormat($this->previousDate))->whereDate('start_date_time', '<=', dataBaseFormat($this->invoiceDate))->whereIn('entry_type', ['NR','RC','CL'])->orderByDesc('start_date_time')->get()->count());
        $this->invoiceRows = (DB::table('internet_history')->select('customer_id')->whereDate('monthly_invoice_date', "=", $this->invoiceDate)->where('entry_type', "=", 'MP')->orderByDesc('start_date_time')->get()->count());
    }

    public function sheets(): array
    {
        $sheets = [            
            new OverviewSummary($this->previousDate, $this->invoiceDate),
            new MonthlySummary($this->previousDate, $this->invoiceDate),
            new DepositSummary($this->previousDate, $this->invoiceDate, $this->invoiceRows, $this->overViewRows),
        ];

        return $sheets;
    }
}

########################################### Overview Summary Sheet Start ###########################################

class OverviewSummary implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $previousDate;
    protected $invoiceDate;
    protected $totalRows;    

    function __construct($previousDate,$invoiceDate) {
        $this->previousDate = $previousDate;    
        $this->invoiceDate = $invoiceDate;
        $this->totalRows = (DB::table('internet_history')->select('customer_id')->whereDate('start_date_time', '>=', dataBaseFormat($this->previousDate))->whereDate('start_date_time', '<=', dataBaseFormat($this->invoiceDate))->whereIn('entry_type', ['NR','RC','CL'])->orderByDesc('start_date_time')->get()->count());
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $overviewSummaryArr= DB::select("select `id`, `customer_id`, `address_id`, `plan_id`, `entry_type`,`start_date_time`, `installation_fee`, `reinstallation_fee`, `reconnect_fee`, `refrence_id` from `internet_history` where date(`start_date_time`) >='".dataBaseFormat($this->previousDate)."' and date(`start_date_time`) <='".dataBaseFormat($this->invoiceDate)."' and `entry_type` in('NR','RC','CL') order by `start_date_time` desc");
        return collect($overviewSummaryArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Overview Summary';
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
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    ],

            'A1:I'.($this->totalRows+3) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_DATE_XLSX15,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_CURRENCY_USD,
            'G' => NumberFormat::FORMAT_CURRENCY_USD,
            'H' => NumberFormat::FORMAT_CURRENCY_USD,
            'I' => NumberFormat::FORMAT_TEXT,
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

                $event->sheet->mergeCells('A1:I1');
                $event->sheet->setCellValue('A1','List of New Connection, Change Location and Reconnect Customers for '.monthByGivenDate2($this->invoiceDate).' '.yearByGivenDate($this->invoiceDate));
                $event->sheet->getStyle('A1')->applyFromArray($style);
                $event->sheet->getRowDimension(1)->setRowHeight(35);
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':E'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->setCellValue('G'. ($event->sheet->getHighestRow()), '=SUM(G3:G'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->setCellValue('H'. ($event->sheet->getHighestRow()), '=SUM(H3:H'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->getStyle('F'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->getStyle('G'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->getStyle('H'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(35);
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
            'Cust ID',
            'Date',
            'Customer Name',
            'Address',
            'Fee',
            'Vat',
            'Total',
            'Remark',
        ];
    }
  
    /**
    * @return MappingWithHeadings
    */
    public function map($overviewSummaryArr): array
    {   
        return [
            '=ROW()-2',
            customerDetailsByID($overviewSummaryArr->customer_id)['internet_id'],
            dataBaseFormat($overviewSummaryArr->start_date_time),
            (customerDetailsByID($overviewSummaryArr->customer_id)['type']=='P') ? customerDetailsByID($overviewSummaryArr->customer_id)['name'] : customerDetailsByID($overviewSummaryArr->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($overviewSummaryArr->address_id),
            overviewAmountVatTotal($overviewSummaryArr->entry_type,$overviewSummaryArr->installation_fee,$overviewSummaryArr->reinstallation_fee,$overviewSummaryArr->reconnect_fee,$overviewSummaryArr->refrence_id)['amount'],
            overviewAmountVatTotal($overviewSummaryArr->entry_type,$overviewSummaryArr->installation_fee,$overviewSummaryArr->reinstallation_fee,$overviewSummaryArr->reconnect_fee,$overviewSummaryArr->refrence_id)['vat'],
            overviewAmountVatTotal($overviewSummaryArr->entry_type,$overviewSummaryArr->installation_fee,$overviewSummaryArr->reinstallation_fee,$overviewSummaryArr->reconnect_fee,$overviewSummaryArr->refrence_id)['total'],
            overviewAmountVatTotal($overviewSummaryArr->entry_type,$overviewSummaryArr->installation_fee,$overviewSummaryArr->reinstallation_fee,$overviewSummaryArr->reconnect_fee,$overviewSummaryArr->refrence_id)['remark'],
        ];
        
    }
}

########################################### Overview Summary Sheet End ###########################################

########################################### Monthly Summary Sheet Start ###########################################

class MonthlySummary implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $previousDate;
    protected $invoiceDate;
    protected $totalRows;    

    function __construct($previousDate,$invoiceDate) {
        $this->previousDate = $previousDate;    
        $this->invoiceDate = $invoiceDate;
        $this->totalRows = (DB::table('internet_history')->select('customer_id')->whereDate('monthly_invoice_date', "=", $this->invoiceDate)->where('entry_type', "=", 'MP')->orderByDesc('start_date_time')->get()->count());
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $monthlySummaryArr= DB::select("select `id`, `monthly_invoice_date`, `invoice_number`, `customer_id`, `entry_type`, `address_id`, `plan_id`, `balance`, `vat_amount`, `total_amount`, `refrence_id` from `internet_history` where date(`monthly_invoice_date`) ='".dataBaseFormat($this->invoiceDate)."' and `entry_type`='MP' order by `invoice_number` asc");
        //pad($monthlySummaryArrsss);
        return collect($monthlySummaryArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Invoice Summary';
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
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    ],
            
            'A1:I'.($this->totalRows+3) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_CURRENCY_USD,
            'G' => NumberFormat::FORMAT_CURRENCY_USD,
            'H' => NumberFormat::FORMAT_CURRENCY_USD,
            'I' => NumberFormat::FORMAT_TEXT,
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
            'Cust.ID',
            'Customer Name',
            'Address',      
            'Fee',
            'Vat',
            'Total',
            'Remark',
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

                $event->sheet->mergeCells('A1:I1');
                $event->sheet->setCellValue('A1','List of Monthly Invoice for '.monthByGivenDate2($this->invoiceDate).' '.yearByGivenDate($this->invoiceDate));
                $event->sheet->getStyle('A1')->applyFromArray($style);
                $event->sheet->getRowDimension(1)->setRowHeight(35);
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];

                $style2 = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':E'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('H'. ($event->sheet->getHighestRow()), '=SUM(H3:H'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=H'.($event->sheet->getHighestRow()).'/1.1');
                $event->sheet->setCellValue('G'. ($event->sheet->getHighestRow()), '=F'.($event->sheet->getHighestRow()).'*0.1');                
                $event->sheet->getStyle('F'.($event->sheet->getHighestRow()).':H'.($event->sheet->getHighestRow()))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getStyle('F'.($event->sheet->getHighestRow()).':H'.($event->sheet->getHighestRow()))->applyFromArray($style2);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(35);
            }
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($monthlySummaryArr): array
    {   
        return [
            '=ROW()-2',
            invoiceName($monthlySummaryArr->invoice_number, $monthlySummaryArr->monthly_invoice_date),
            customerDetailsByID($monthlySummaryArr->customer_id)['internet_id'],
            (customerDetailsByID($monthlySummaryArr->customer_id)['type']=='P') ? customerDetailsByID($monthlySummaryArr->customer_id)['name'] : customerDetailsByID($monthlySummaryArr->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($monthlySummaryArr->address_id),
            amountFormat2($monthlySummaryArr->balance),
            amountFormat2($monthlySummaryArr->vat_amount),
            amountFormat2($monthlySummaryArr->total_amount),
            remarkForSuspendTerminateAndLocation($monthlySummaryArr->entry_type, $monthlySummaryArr->refrence_id),
        ];
        
    }
}
########################################### Monthly Summary Sheet End ###########################################

########################################### Deposit Summary Sheet Start ###########################################

class DepositSummary implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $previousDate;
    protected $invoiceDate;
    protected $totalRows;    

    function __construct($previousDate,$invoiceDate, $invoiceRows, $overViewRows) {
        $this->previousDate = $previousDate;    
        $this->invoiceDate = $invoiceDate;
        $this->invoiceRows = ($invoiceRows+3);
        $this->overViewRows = ($overViewRows+3);
        $this->totalRows = (DB::table('internet_history')->select('customer_id')->whereDate('start_date_time', ">=", $this->previousDate)->whereDate('start_date_time', "<=", $this->invoiceDate)->whereIn('entry_type', ['NR','TS'])->orderByDesc('start_date_time')->get()->count());
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $depositSummaryArr= DB::select("select `id`, `customer_id`, `address_id`, `plan_id`, `entry_type`,`start_date_time`, `payment_by`, `deposit_fee`, `refund_amount`, `due_amount`, `total_amount`, `payment_by`, `refrence_id` from `internet_history` where date(`start_date_time`) >='".dataBaseFormat($this->previousDate)."' and date(`start_date_time`) <='".dataBaseFormat($this->invoiceDate)."' and `entry_type` in('NR','TS') order by `start_date_time` desc");
        return collect($depositSummaryArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Deposit Summary';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']]   
                    ],
            // Style the second row as bold text.
            2    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    ],

            'A1:K'.($this->totalRows+3) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_DATE_XLSX15,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_CURRENCY_USD,
            'G' => NumberFormat::FORMAT_CURRENCY_USD,
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
                $event->sheet->setCellValue('A1','Deposit List for Internet '.monthByGivenDate2($this->invoiceDate).' '.yearByGivenDate($this->invoiceDate));
                $event->sheet->getStyle('A1')->applyFromArray($style);
                $event->sheet->getRowDimension(1)->setRowHeight(35);
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];

                $style2 = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['weight' => 600,'size'=> 16,],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],  
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                ];

                $event->sheet->mergeCells('A'.(($this->totalRows+3)).':E'.($this->totalRows+3));
                $event->sheet->setCellValue('A'.($this->totalRows+3),'Grand Total');
                $event->sheet->getStyle('A'.($this->totalRows+3))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($this->totalRows+3), '=SUM(F3:F'.($this->totalRows+2).')');
                $event->sheet->setCellValue('G'. ($this->totalRows+3), '=SUM(G3:G'.($this->totalRows+2).')');
                $event->sheet->setCellValue('H'. ($this->totalRows+3), '=SUM(H3:H'.($this->totalRows+2).')');
                $event->sheet->setCellValue('I'. ($this->totalRows+3), '=SUM(I3:I'.($this->totalRows+2).')');
                $event->sheet->setCellValue('J'. ($this->totalRows+3), '=F'.($this->totalRows+3).'+(H'.($this->totalRows+3).'+I'.($this->totalRows+3).')');
                $event->sheet->getStyle('F'.($this->totalRows+3))->applyFromArray($style);
                $event->sheet->getStyle('G'.($this->totalRows+3))->applyFromArray($style);
                $event->sheet->getStyle('H'.($this->totalRows+3))->applyFromArray($style);
                $event->sheet->getStyle('I'.($this->totalRows+3))->applyFromArray($style);
                $event->sheet->getStyle('J'.($this->totalRows+3))->applyFromArray($style);
                $event->sheet->getRowDimension(($this->totalRows+3))->setRowHeight(35);

                //Type
                $event->sheet->setCellValue('I'.($this->totalRows+6),'Type');
                $event->sheet->getStyle('I'.($this->totalRows+6))->applyFromArray($style2);
                $event->sheet->setCellValue('J'.($this->totalRows+6),'Sub Total');
                $event->sheet->getStyle('J'.($this->totalRows+6))->applyFromArray($style2);

                //Invoice
                $event->sheet->setCellValue('I'.($this->totalRows+7),'Invoice');
                $event->sheet->getStyle('I'.($this->totalRows+7))->applyFromArray($style2);
                $event->sheet->setCellValue('J'.($this->totalRows+7),"='Invoice Summary'!H".$this->invoiceRows);
                $event->sheet->getStyle('J'.($this->totalRows+7))->applyFromArray($style2);

                //Deposit
                $event->sheet->setCellValue('I'.($this->totalRows+8),'Deposit');
                $event->sheet->getStyle('I'.($this->totalRows+8))->applyFromArray($style2);
                $event->sheet->setCellValue('J'.($this->totalRows+8),'=SUM(F'.($this->totalRows+3).')');
                $event->sheet->getStyle('J'.($this->totalRows+8))->applyFromArray($style2);

                //Installation
                $event->sheet->setCellValue('I'.($this->totalRows+9),'Installation');
                $event->sheet->getStyle('I'.($this->totalRows+9))->applyFromArray($style2);
                $event->sheet->setCellValue('J'.($this->totalRows+9),"='Overview Summary'!H".$this->overViewRows);
                $event->sheet->getStyle('J'.($this->totalRows+9))->applyFromArray($style2);

                //Installation
                $event->sheet->setCellValue('I'.($this->totalRows+10),'Refund');
                $event->sheet->getStyle('I'.($this->totalRows+10))->applyFromArray($style2);
                $event->sheet->setCellValue('J'.($this->totalRows+10),"=SUM(G".($this->totalRows+3).')');
                $event->sheet->getStyle('J'.($this->totalRows+10))->applyFromArray($style2);

                //Total
                $event->sheet->setCellValue('I'.($this->totalRows+11),'Total');
                $event->sheet->getStyle('I'.($this->totalRows+11))->applyFromArray($style2);
                $event->sheet->setCellValue('J'.($this->totalRows+11),'=SUM(J'.($this->totalRows+7).':J'.($this->totalRows+10).')');
                $event->sheet->getStyle('J'.($this->totalRows+11))->applyFromArray($style2);
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
            'Address', 
            'Cust.ID',
            'Deposit Date',            
            'Customer Name',                 
            'Input',
            'Refund',
            'Change Deposit',
            'Clear with Fee',
            'Total',
            'Remark',
        ];
    }
  
    /**
    * @return MappingWithHeadings
    */
    public function map($depositSummaryArr): array
    {   
        return [
            '=ROW()-2',
            buildingLocationAndUnitNumberByUnitID($depositSummaryArr->address_id),
            customerDetailsByID($depositSummaryArr->customer_id)['internet_id'],
            dataBaseFormat($depositSummaryArr->start_date_time),            
            (customerDetailsByID($depositSummaryArr->customer_id)['type']=='P') ? customerDetailsByID($depositSummaryArr->customer_id)['name'] : customerDetailsByID($depositSummaryArr->customer_id)['shop_name'],            
            (($depositSummaryArr->entry_type=='NR')) ? returnLatestDepositFee($depositSummaryArr->customer_id) : NULL,
            (($depositSummaryArr->entry_type=='TS') && ($depositSummaryArr->payment_by=='CR')) ? amountFormat2(0-$depositSummaryArr->total_amount) : NULL,
            ($depositSummaryArr->entry_type=='TS') ? returnLatestDepositFee($depositSummaryArr->customer_id) : NULL,
            ($depositSummaryArr->entry_type=='TS') ? amountFormat2($depositSummaryArr->due_amount) : NULL,
            '',
            ($depositSummaryArr->entry_type=='NR') ? remarkForNewConnection($depositSummaryArr->id) : remarkForSuspendTerminateAndLocation($depositSummaryArr->entry_type, $depositSummaryArr->refrence_id),
        ];
        
    }
}
########################################### Deposit Summary Sheet End ###########################################
