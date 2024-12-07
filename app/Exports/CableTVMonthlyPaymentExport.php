<?php

namespace App\Exports;

use DB;
use App\Models\CableTVHistory;
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

class CableTVMonthlyPaymentExport implements WithMultipleSheets, WithPreCalculateFormulas
{
    protected $month;
    protected $year;

    function __construct($month, $year) {
        $this->month = $month;
        $this->year = $year;
    }

    public function sheets(): array
    {
        $sheets = [
            new ApartmentSheet($this->month, $this->year),
            new RetailShopSheet($this->month, $this->year),
            new TownhouseSheet($this->month, $this->year),
            new SecretVillaSheet($this->month, $this->year),
        ];

        return $sheets;
    }
}

########################################### Apartment Sheet Start ###########################################

class ApartmentSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $apartmentArr;
    protected $totalRows;    

    function __construct($month, $year) {
            $this->month = $month;
            $this->year = $year;
            $this->apartmentArr = ['A101','A102','A103','A104','A105','A106','A107','A108','A109','A110'];
            $this->totalRows = (DB::table('cabletv_history')->leftJoin('units_address','cabletv_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('cabletv_history.id')->whereIn('buildings_location.name', $this->apartmentArr)->where('cabletv_history.entry_type', 'MP')->whereMonth('cabletv_history.monthly_invoice_date', "=", $this->month)->whereYear('cabletv_history.monthly_invoice_date', "=", $this->year)->orderBy('cabletv_history.id')->count('cabletv_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('cabletv_history')->leftJoin('units_address','cabletv_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('cabletv_history.id','cabletv_history.customer_id','cabletv_history.address_id','cabletv_history.start_date_time','cabletv_history.end_date_time','cabletv_history.customer_mobile','cabletv_history.monthly_invoice_date','cabletv_history.total_amount','cabletv_history.refrence_id','cabletv_history.remark')->whereIn('buildings_location.name', $this->apartmentArr)->where('cabletv_history.entry_type', 'MP')->whereMonth('cabletv_history.monthly_invoice_date', "=", $this->month)->whereYear('cabletv_history.monthly_invoice_date', "=", $this->year)->orderBy('units_address.id')->get();
        return $monthlyPaymentArr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Apartment';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            
            2    => ['font' => ['bold' => true]],

            2    => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]],
            
            'A1:G'.($this->totalRows+1) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],

            'A'.($this->totalRows+2).':G'.($this->totalRows+2) => ['font' => ['bold' => true], 'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_XLSX15,
            'E' => NumberFormat::FORMAT_DATE_XLSX15,
            'F' => NumberFormat::FORMAT_CURRENCY_USD,
            'G' => NumberFormat::FORMAT_TEXT,
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
        
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->setCellValue('A1','Cable TV Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':E'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->getStyle('F'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(40);
            }
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
            'Start Date',
            'End Date',
            'Amount',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ctvhistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ctvhistory->customer_id)['type']=='P')?customerDetailsByID($ctvhistory->customer_id)['name']:customerDetailsByID($ctvhistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ctvhistory->address_id),
            cableTVExportDateFormat($ctvhistory->start_date_time),
            cableTVExportDateFormat($ctvhistory->end_date_time),
            amountFormat2($ctvhistory->total_amount),
            ($ctvhistory->remark),
        ];
        
    }
}
########################################### Apartment Sheet End ###########################################

########################################### Retail Shop Sheet Start ###########################################

class RetailShopSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $shopArr;
    protected $totalRows;    

    function __construct($month, $year) {
            $this->month = $month;
            $this->year = $year;
            $this->shopArr = ['S'];
            $this->totalRows = (DB::table('cabletv_history')->leftJoin('units_address','cabletv_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('cabletv_history.id')->where('buildings_location.location', 'R1')->whereIn('buildings_location.type', $this->shopArr)->where('cabletv_history.entry_type', 'MP')->whereMonth('cabletv_history.monthly_invoice_date', "=", $this->month)->whereYear('cabletv_history.monthly_invoice_date', "=", $this->year)->orderBy('cabletv_history.id')->count('cabletv_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('cabletv_history')->leftJoin('units_address','cabletv_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('cabletv_history.id','cabletv_history.customer_id','cabletv_history.address_id','cabletv_history.start_date_time','cabletv_history.end_date_time','cabletv_history.customer_mobile','cabletv_history.monthly_invoice_date','cabletv_history.total_amount','cabletv_history.refrence_id','cabletv_history.remark')->where('buildings_location.location', 'R1')->whereIn('buildings_location.type', $this->shopArr)->where('cabletv_history.entry_type', 'MP')->whereMonth('cabletv_history.monthly_invoice_date', "=", $this->month)->whereYear('cabletv_history.monthly_invoice_date', "=", $this->year)->orderBy('units_address.id')->get();
        return $monthlyPaymentArr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Retail Shop';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            
            2    => ['font' => ['bold' => true]],

            2    => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]],
            
            'A1:G'.($this->totalRows+1) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],

            'A'.($this->totalRows+2).':G'.($this->totalRows+2) => ['font' => ['bold' => true], 'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_XLSX15,
            'E' => NumberFormat::FORMAT_DATE_XLSX15,
            'F' => NumberFormat::FORMAT_CURRENCY_USD,
            'G' => NumberFormat::FORMAT_TEXT,
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
        
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->setCellValue('A1','Cable TV Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':E'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->getStyle('F'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(40);
            }
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
            'Start Date',
            'End Date',
            'Amount',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ctvhistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ctvhistory->customer_id)['type']=='P')?customerDetailsByID($ctvhistory->customer_id)['name']:customerDetailsByID($ctvhistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ctvhistory->address_id),
            cableTVExportDateFormat($ctvhistory->start_date_time),
            cableTVExportDateFormat($ctvhistory->end_date_time),
            amountFormat2($ctvhistory->total_amount),
            ($ctvhistory->remark),
        ];
        
    }
}
########################################### Retail Shop Sheet End ###########################################

########################################### Townhouse & Villa Sheet Start ###########################################

class TownhouseSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $townhouseArr;
    protected $totalRows;    

    function __construct($month, $year) {
            $this->month = $month;
            $this->year = $year;
            $this->townhouseArr = ['T','V'];
            $this->totalRows = (DB::table('cabletv_history')->leftJoin('units_address','cabletv_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('cabletv_history.id')->where('buildings_location.location', 'R1')->whereIn('buildings_location.type', $this->townhouseArr)->where('cabletv_history.entry_type', 'MP')->whereMonth('cabletv_history.monthly_invoice_date', "=", $this->month)->whereYear('cabletv_history.monthly_invoice_date', "=", $this->year)->orderBy('cabletv_history.id')->count('cabletv_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('cabletv_history')->leftJoin('units_address','cabletv_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('cabletv_history.id','cabletv_history.customer_id','cabletv_history.address_id','cabletv_history.start_date_time','cabletv_history.end_date_time','cabletv_history.customer_mobile','cabletv_history.monthly_invoice_date','cabletv_history.total_amount','cabletv_history.refrence_id','cabletv_history.remark')->where('buildings_location.location', 'R1')->whereIn('buildings_location.type', $this->townhouseArr)->where('cabletv_history.entry_type', 'MP')->whereMonth('cabletv_history.monthly_invoice_date', "=", $this->month)->whereYear('cabletv_history.monthly_invoice_date', "=", $this->year)->orderBy('units_address.id')->get();
        return $monthlyPaymentArr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Town House & Villa';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            
            2    => ['font' => ['bold' => true]],

            2    => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]],
            
            'A1:G'.($this->totalRows+1) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],

            'A'.($this->totalRows+2).':G'.($this->totalRows+2) => ['font' => ['bold' => true], 'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_XLSX15,
            'E' => NumberFormat::FORMAT_DATE_XLSX15,
            'F' => NumberFormat::FORMAT_CURRENCY_USD,
            'G' => NumberFormat::FORMAT_TEXT,
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
        
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->setCellValue('A1','Cable TV Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':E'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->getStyle('F'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(40);
            }
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
            'Start Date',
            'End Date',
            'Amount',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ctvhistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ctvhistory->customer_id)['type']=='P')?customerDetailsByID($ctvhistory->customer_id)['name']:customerDetailsByID($ctvhistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ctvhistory->address_id),
            cableTVExportDateFormat($ctvhistory->start_date_time),
            cableTVExportDateFormat($ctvhistory->end_date_time),
            amountFormat2($ctvhistory->total_amount),
            ($ctvhistory->remark),
        ];
        
    }
}
########################################### Townhouse Sheet End ###########################################

########################################### Secret Villa Sheet Start ######################################

class SecretVillaSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $secretArr;
    protected $totalRows;    

    function __construct($month, $year) {
            $this->month = $month;
            $this->year = $year;
            $this->secretArr = ['V'];
            $this->totalRows = (DB::table('cabletv_history')->leftJoin('units_address','cabletv_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('cabletv_history.id')->where('buildings_location.location', 'R2')->whereIn('buildings_location.type', $this->secretArr)->where('cabletv_history.entry_type', 'MP')->whereMonth('cabletv_history.monthly_invoice_date', "=", $this->month)->whereYear('cabletv_history.monthly_invoice_date', "=", $this->year)->orderBy('cabletv_history.id')->count('cabletv_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('cabletv_history')->leftJoin('units_address','cabletv_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('cabletv_history.id','cabletv_history.customer_id','cabletv_history.address_id','cabletv_history.start_date_time','cabletv_history.end_date_time','cabletv_history.customer_mobile','cabletv_history.monthly_invoice_date','cabletv_history.total_amount','cabletv_history.refrence_id','cabletv_history.remark')->where('buildings_location.location', 'R2')->whereIn('buildings_location.type', $this->secretArr)->where('cabletv_history.entry_type', 'MP')->whereMonth('cabletv_history.monthly_invoice_date', "=", $this->month)->whereYear('cabletv_history.monthly_invoice_date', "=", $this->year)->orderBy('units_address.id')->get();
        return $monthlyPaymentArr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Secret Villa';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
            
            2    => ['font' => ['bold' => true]],

            2    => ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]],
            
            'A1:G'.($this->totalRows+1) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],

            'A'.($this->totalRows+2).':G'.($this->totalRows+2) => ['font' => ['bold' => true], 'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_DATE_XLSX15,
            'E' => NumberFormat::FORMAT_DATE_XLSX15,
            'F' => NumberFormat::FORMAT_CURRENCY_USD,
            'G' => NumberFormat::FORMAT_TEXT,
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
        
                $event->sheet->mergeCells('A1:G1');
                $event->sheet->setCellValue('A1','Cable TV Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':E'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->getStyle('F'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(40);
            }
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
            'Start Date',
            'End Date',
            'Amount',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ctvhistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ctvhistory->customer_id)['type']=='P')?customerDetailsByID($ctvhistory->customer_id)['name']:customerDetailsByID($ctvhistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ctvhistory->address_id),
            cableTVExportDateFormat($ctvhistory->start_date_time),
            cableTVExportDateFormat($ctvhistory->end_date_time),
            amountFormat2($ctvhistory->total_amount),
            ($ctvhistory->remark),
        ];
        
    }
}
########################################### Secret Villa Sheet End ###########################################