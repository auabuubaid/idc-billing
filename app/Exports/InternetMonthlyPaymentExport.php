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

class InternetMonthlyPaymentExport implements WithMultipleSheets, WithPreCalculateFormulas
{
    protected $month;
    protected $year;

    function __construct($month, $year, $status, $addressID) {
        $this->month = $month;
        $this->year = $year;
        $this->status = $status;
        $this->addressID = $addressID;
    }

    public function sheets(): array
    {
        $sheets = [
            new ApartmentSheet($this->month, $this->year, $this->status, $this->addressID),
            new TownhouseSheet($this->month, $this->year, $this->status, $this->addressID),
            new ShopSheet($this->month, $this->year, $this->status, $this->addressID),
            new Shop2Sheet($this->month, $this->year, $this->status, $this->addressID),
            new R2Sheet($this->month, $this->year, $this->status, $this->addressID),
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

    function __construct($month, $year, $status, $addressID) 
    {
        $this->month = $month;
        $this->year = $year;
        $this->status = empty($status)?['Y', 'N']:[$status];
        $this->addressID = $addressID;
        $this->apartmentArr = ['A101','A102','A103','A104','A105','A106','A107','A108','A109','A110'];
        $this->totalRows = (DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id')->whereIn('buildings_location.name', $this->apartmentArr)->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('internet_history.id')->count('internet_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id','internet_history.customer_id','internet_history.address_id','internet_history.customer_mobile','internet_history.balance','internet_history.vat_amount','internet_history.total_amount','internet_history.remark')->whereIn('buildings_location.name', $this->apartmentArr)->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('units_address.id')->get();
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
            1    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]   
                    ],
            
            2    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    ],
            
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
            'D' => NumberFormat::FORMAT_CURRENCY_USD,
            'E' => NumberFormat::FORMAT_CURRENCY_USD,
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
                $event->sheet->setCellValue('A1','Internet Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $style2 = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':C'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->setCellValue('D'. ($event->sheet->getHighestRow()), '=F'.($event->sheet->getHighestRow()).'/1.1');
                $event->sheet->setCellValue('E'. ($event->sheet->getHighestRow()), '=D'.($event->sheet->getHighestRow()).'*0.1');                
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->applyFromArray($style2);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(30);
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
            'Amount',
            'VAT',
            'Total',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ihistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ihistory->customer_id)['type']=='P')?customerDetailsByID($ihistory->customer_id)['name']:customerDetailsByID($ihistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ihistory->address_id),
            amountFormat2($ihistory->balance),
            amountFormat2($ihistory->vat_amount),
            amountFormat2($ihistory->total_amount),
            $ihistory->remark,
        ];
        
    }
}
########################################### Apartment Sheet End ###########################################

########################################### Townhouse Sheet Start ###########################################

class TownhouseSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $townhouseArr;
    protected $totalRows;    

    function __construct($month, $year, $status, $addressID) 
    {
        $this->month = $month;
        $this->year = $year;
        $this->status = empty($status)?['Y', 'N']:[$status];
        $this->addressID = $addressID;
        $this->townhouseArr = ['T','V'];
        $this->totalRows = (DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id')->where('buildings_location.location', 'R1')->whereIn('buildings_location.type', $this->townhouseArr)->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('internet_history.id')->count('internet_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id','internet_history.customer_id','internet_history.address_id','internet_history.customer_mobile','internet_history.balance','internet_history.vat_amount','internet_history.total_amount','internet_history.remark')->where('buildings_location.location', 'R1')->whereIn('buildings_location.type', $this->townhouseArr)->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('units_address.id')->get();
        return $monthlyPaymentArr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Townhouse & Villa';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]   
                    ],

            2    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    ],

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
            'D' => NumberFormat::FORMAT_CURRENCY_USD,
            'E' => NumberFormat::FORMAT_CURRENCY_USD,
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
                $event->sheet->setCellValue('A1','Internet Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $style2 = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':C'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->setCellValue('D'. ($event->sheet->getHighestRow()), '=F'.($event->sheet->getHighestRow()).'/1.1');
                $event->sheet->setCellValue('E'. ($event->sheet->getHighestRow()), '=D'.($event->sheet->getHighestRow()).'*0.1');                
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->applyFromArray($style2);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(30);
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
            'Amount',
            'VAT',
            'Total',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ihistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ihistory->customer_id)['type']=='P')?customerDetailsByID($ihistory->customer_id)['name']:customerDetailsByID($ihistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ihistory->address_id),
            amountFormat2($ihistory->balance),
            amountFormat2($ihistory->vat_amount),
            amountFormat2($ihistory->total_amount),
            $ihistory->remark,
        ];
        
    }
}

########################################### Townhouse Sheet End ###########################################

########################################### East-Shops Sheet Start ###########################################

class ShopSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $shopArr;
    protected $totalRows;    

    function __construct($month, $year, $status, $addressID) 
    {
        $this->month = $month;
        $this->year = $year;
        $this->status = empty($status)?['Y', 'N']:[$status];
        $this->addressID = $addressID;
        $this->shopArr = ['SMA','SMB','SMC','SMD'];
        $this->totalRows = (DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id')->where('buildings_location.location', 'R1')->whereIn('buildings_location.name', $this->shopArr)->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('internet_history.id')->count('internet_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id','internet_history.customer_id','internet_history.address_id','internet_history.customer_mobile','internet_history.balance','internet_history.vat_amount','internet_history.total_amount','internet_history.remark')->where('buildings_location.location', 'R1')->whereIn('buildings_location.name', $this->shopArr)->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('units_address.id')->get();
        return $monthlyPaymentArr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'East Shops';
    }

    public function styles($sheet)
    {
        return [
            1    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]   
                    ],

            2    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    ],

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
            'D' => NumberFormat::FORMAT_CURRENCY_USD,
            'E' => NumberFormat::FORMAT_CURRENCY_USD,
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
                $event->sheet->setCellValue('A1','Internet Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $style2 = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':C'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->setCellValue('D'. ($event->sheet->getHighestRow()), '=F'.($event->sheet->getHighestRow()).'/1.1');
                $event->sheet->setCellValue('E'. ($event->sheet->getHighestRow()), '=D'.($event->sheet->getHighestRow()).'*0.1');                
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->applyFromArray($style2);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(30);
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
            'Amount',
            'VAT',
            'Total',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ihistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ihistory->customer_id)['type']=='P')?customerDetailsByID($ihistory->customer_id)['name']:customerDetailsByID($ihistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ihistory->address_id),
            amountFormat2($ihistory->balance),
            amountFormat2($ihistory->vat_amount),
            amountFormat2($ihistory->total_amount),
            $ihistory->remark,
        ];
        
    }
}

########################################### East-Shops Sheet End ###########################################

########################################### South-Shops Sheet Start ###########################################

class Shop2Sheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $shop2Arr;
    protected $totalRows;

    function __construct($month, $year, $status, $addressID) 
    {
        $this->month = $month;
        $this->year = $year;
        $this->status = empty($status)?['Y', 'N']:[$status];
        $this->addressID = $addressID;
        $this->shop2Arr = ['RSA','RSB'];
        $this->totalRows = (DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id')->where('buildings_location.location', 'R1')->whereIn('buildings_location.name', $this->shop2Arr)->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('internet_history.id')->count('internet_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id','internet_history.customer_id','internet_history.address_id','internet_history.customer_mobile','internet_history.balance','internet_history.vat_amount','internet_history.total_amount','internet_history.remark')->where('buildings_location.location', 'R1')->whereIn('buildings_location.name', $this->shop2Arr)->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('units_address.id')->get();
        return $monthlyPaymentArr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'South Shops';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]   
                    ],

            2    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    ],

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
            'D' => NumberFormat::FORMAT_CURRENCY_USD,
            'E' => NumberFormat::FORMAT_CURRENCY_USD,
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
                $event->sheet->setCellValue('A1','Internet Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $style2 = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];
                
                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':C'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->setCellValue('D'. ($event->sheet->getHighestRow()), '=F'.($event->sheet->getHighestRow()).'/1.1');
                $event->sheet->setCellValue('E'. ($event->sheet->getHighestRow()), '=D'.($event->sheet->getHighestRow()).'*0.1');                
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->applyFromArray($style2);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(30);
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
            'Amount',
            'VAT',
            'Total',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ihistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ihistory->customer_id)['type']=='P')?customerDetailsByID($ihistory->customer_id)['name']:customerDetailsByID($ihistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ihistory->address_id),
            amountFormat2($ihistory->balance),
            amountFormat2($ihistory->vat_amount),
            amountFormat2($ihistory->total_amount),
            $ihistory->remark,
        ];
        
    }
}

########################################### South-Shops Sheet End ###########################################

########################################### Secret Villa Sheet Start ###########################################

class R2Sheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $month;
    protected $year;
    protected $totalRows;

    function __construct($month, $year, $status, $addressID) 
    {
        $this->month = $month;
        $this->year = $year;
        $this->status = empty($status)?['Y', 'N']:[$status];
        $this->addressID = $addressID;
        $this->totalRows = (DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id')->where('buildings_location.location', 'R2')->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('internet_history.id')->count('internet_history.id')+1);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $monthlyPaymentArr= DB::table('internet_history')->leftJoin('units_address','internet_history.address_id', '=', 'units_address.id')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('internet_history.id','internet_history.customer_id','internet_history.address_id','internet_history.customer_mobile','internet_history.balance','internet_history.vat_amount','internet_history.total_amount','internet_history.remark')->where('buildings_location.location', 'R2')->where('internet_history.entry_type', 'MP')->whereMonth('internet_history.monthly_invoice_date', "=", $this->month)->whereYear('internet_history.monthly_invoice_date', "=", $this->year)->whereIn('internet_history.paid', $this->status)->orderBy('units_address.id')->get();
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
            1    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']]   
                    ],

            2    => ['font' => ['bold' => true],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    ],

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
            'D' => NumberFormat::FORMAT_CURRENCY_USD,
            'E' => NumberFormat::FORMAT_CURRENCY_USD,
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
                $event->sheet->setCellValue('A1','Internet Invoice for '.monthNumberToName($this->month).' '.$this->year);
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

                $style2 = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'CCCCCC']],
                ];

                $event->sheet->mergeCells('A'.($event->sheet->getHighestRow()).':C'.($event->sheet->getHighestRow()));
                $event->sheet->setCellValue('A'.($event->sheet->getHighestRow()),'Grand Total');
                $event->sheet->getStyle('A'.($event->sheet->getHighestRow()))->applyFromArray($style);
                $event->sheet->setCellValue('F'. ($event->sheet->getHighestRow()), '=SUM(F3:F'.($event->sheet->getHighestRow()-1).')');
                $event->sheet->setCellValue('D'. ($event->sheet->getHighestRow()), '=F'.($event->sheet->getHighestRow()).'/1.1');
                $event->sheet->setCellValue('E'. ($event->sheet->getHighestRow()), '=D'.($event->sheet->getHighestRow()).'*0.1');                
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getStyle('D'.($event->sheet->getHighestRow()).':F'.($event->sheet->getHighestRow()))->applyFromArray($style2);
                $event->sheet->getRowDimension(($event->sheet->getHighestRow()))->setRowHeight(30);
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
            'Amount',
            'VAT',
            'Total',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($ihistory): array
    {   
        return [
            '=ROW()-2',
            (customerDetailsByID($ihistory->customer_id)['type']=='P')?customerDetailsByID($ihistory->customer_id)['name']:customerDetailsByID($ihistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ihistory->address_id),
            amountFormat2($ihistory->balance),
            amountFormat2($ihistory->vat_amount),
            amountFormat2($ihistory->total_amount),
            $ihistory->remark,
        ];
        
    }
}

########################################### Secret Villa Sheet End ###########################################