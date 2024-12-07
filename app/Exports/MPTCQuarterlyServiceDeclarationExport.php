<?php

namespace App\Exports;

use DB;
use App\Models\InternetHistory;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithPreCalculateFormulas;

class MPTCQuarterlyServiceDeclarationExport implements WithMultipleSheets, WithPreCalculateFormulas
{
    protected $quarter;
    protected $year;
    
    function __construct($quarter, $year) {
        $this->quarter = $quarter;
        $this->year = $year;
    }

    public function sheets(): array
    {   
        $sheets = [            
            new Quarterly($this->quarter, $this->year),
            new Month1($this->quarter, $this->year),
            new Month2($this->quarter, $this->year),
            new Month3($this->quarter, $this->year),
        ];

        return $sheets;
    }
}

########################################### Quarterly Sheet Start ###########################################

class Quarterly implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $quarter;
    protected $year;

    function __construct($quarter, $year) {
        $this->quarter = $quarter;
        $this->year = $year;
    }

    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $monthlyArr = quaterArrByNumber($this->quarter);
        $serviceArr= ['internet_fee','installation_fee','maintenance_fee','other_revenues','credit_note','debit_note','sale'];
        
        foreach($serviceArr as $skey=>$service)
        {
            foreach($monthlyArr as $mkey=>$month)
            {
                $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($month.'-'.$this->year));
                $endDate = internetEndMonthlyPaymentDate($month.'-'.$this->year);

                $quarterlyArr[$skey][$mkey]['month'] = monthNumberToName2($month);
                $quarterlyArr[$skey][$mkey]['total_service_revenue'] = mptcTotalServiceRevenue($service,$startDate, $endDate);
                $quarterlyArr[$skey][$mkey]['vat'] = amountFormat2(mptcTotalServiceRevenue($service,$startDate, $endDate)/11);
                $quarterlyArr[$skey][$mkey]['spt'] = '-';
                $quarterlyArr[$skey][$mkey]['total_share'] = amountFormat2(mptcTotalServiceRevenue($service,$startDate, $endDate)/1.1);
                $quarterlyArr[$skey][$mkey]['mptc_sharing'] = amountFormat2(mptcTotalServiceRevenue($service,$startDate, $endDate)/11);
            }
        }
        $finalArr= array_reduce($quarterlyArr, function($carry, $array) {
            return array_merge($carry, $array);
        }, []);
        
        return collect($finalArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Quarter '.$this->quarter.' '.monthNumberToName2(quaterArrByNumber($this->quarter)[0]).'-'.monthNumberToName2(quaterArrByNumber($this->quarter)[2]).' '.$this->year;
    }

    public function styles($sheet)
    {
        return [
            
            1    => ['font' => ['name' => 'Khmer OS Siemreap', 'bold'=>true, 'size' => 11,],],

            // Style the 2 row as bold text.
            2    => ['font' => ['bold' => true, 'size' => 11,],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ],
                    
            // Style the 3 row as bold text.
            3    => ['font' => ['bold' => true, 'size' => 11,],],

            'A1:G3' => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                ],
            
            'A4:A25' => [
                'font' => ['bold' => true, 'size' => 11,],
                'alignment' => ['wrapText' => true,],
                ],

            'A4:B25' => [
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                ],

            'C4:G25' => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            'E4:E25' => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:G1');
                $event->sheet->setCellValue('A1','របាយការណ៍ចំណូលតាមប្រភេទសេវាប្រចាំត្រីមាសទី '.$this->quarter.' ឆ្នាំ '.$this->year);
                $event->sheet->getRowDimension(1)->setRowHeight(30);
                $event->sheet->setCellValue('A2','1');
                $event->sheet->setCellValue('B2','2');
                $event->sheet->setCellValue('C2','3');
                $event->sheet->setCellValue('D2','4');
                $event->sheet->setCellValue('E2','5');
                $event->sheet->setCellValue('F2','6');
                $event->sheet->setCellValue('G2','7');
                
            },
            AfterSheet::class => function(AfterSheet $event) {

                $styleTotal = [
                    'font' => ['bold'=>true, 'underline' => true, 'italic' => true,]
                ];

                $styleSign = [
                    'font' => ['name' => 'Khmer OS Siemreap', 'bold'=>true, 'size' => 11, ],
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ];

                $event->sheet->mergeCells('A4:A6');
                $event->sheet->setCellValue('A4','Internet Fee');
                $event->sheet->mergeCells('A7:A9');
                $event->sheet->setCellValue('A7','Installation fee');
                $event->sheet->mergeCells('A10:A12');
                $event->sheet->setCellValue('A10','Maintenance fee');
                $event->sheet->mergeCells('A13:A15');
                $event->sheet->setCellValue('A13','Other Revenues');
                $event->sheet->mergeCells('A16:A18');
                $event->sheet->setCellValue('A16','Credit note');
                $event->sheet->mergeCells('A19:A21');
                $event->sheet->setCellValue('A19','Debit note');
                $event->sheet->mergeCells('A22:A24');
                $event->sheet->setCellValue('A22',str_replace('\n', "\n",'Equipment /\n Telecom Material Sale'));

                $event->sheet->mergeCells('A25:B25');
                $event->sheet->setCellValue('A25','TOTAL:');
                $event->sheet->setCellValue('C25','=SUM(C4:C24)');
                $event->sheet->setCellValue('D25','=SUM(D4:D24)');
                $event->sheet->setCellValue('E25','-');
                $event->sheet->setCellValue('F25','=SUM(F4:F24)');
                $event->sheet->setCellValue('G25','=SUM(G4:G24)');
                $event->sheet->getStyle('C25:G25')->applyFromArray($styleTotal); 
                $event->sheet->getStyle('C4:D25')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1); 
                $event->sheet->getStyle('F4:G25')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1); 

                $event->sheet->mergeCells('C28:D28');
                $event->sheet->setCellValue('A28','ប្រធានក្រុមហ៊ុន');
                $event->sheet->setCellValue('C28','ប្រធានគណនេយ្យ/ហិរញ្ញវត្ថុ');
                $event->sheet->setCellValue('G28','គណនេយ្យករ');
                $event->sheet->getStyle('A28:G28')->applyFromArray($styleSign);
                
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'Type of Services',
            'Month',
            'Total Service Revenue',
            'VAT',
            'SPT',
            'Total for share',
            'Sharing 10% to MPTC',
        ];
    }
  
    /**
    * @return MappingWithHeadings
    */
    public function map($finalArr): array
    {   
        return [
            '',
            $finalArr['month'],
            amountFormat2($finalArr['total_service_revenue'])>0 ? amountFormat2($finalArr['total_service_revenue']) : '-',
            amountFormat2($finalArr['vat'])>0 ? amountFormat2($finalArr['vat']) : '-',
            $finalArr['spt'],
            amountFormat2($finalArr['total_share'])>0 ? amountFormat2($finalArr['total_share']) : '-',
            amountFormat2($finalArr['mptc_sharing'])>0 ? amountFormat2($finalArr['mptc_sharing']) : '-',            
        ];
        
    }
}

########################################### Quarterly Sheet End ###########################################

########################################### Month1 Sheet Start ###########################################

class Month1 implements FromCollection, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $quarter;
    protected $year;

    function __construct($quarter, $year) {
        $this->quarter = $quarter;
        $this->year = $year;
        
        $ihistory = new InternetHistory;
        $month = quaterArrByNumber($this->quarter)[0];
        $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($month.'-'.$this->year));
        $endDate = internetEndMonthlyPaymentDate($month.'-'.$this->year);

        $this->invoiceRows = $ihistory->select('id')->whereDate('monthly_invoice_date', $endDate)->where('entry_type', 'MP')->orderBy('invoice_number')->get()->count();
        $this->installRows = $ihistory->select('id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->where('entry_type', 'NR')->orderBy('start_date_time')->get()->count();
        $this->serviceRows = $ihistory->select('id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->whereIn('entry_type', ['CL','RC'])->orderBy('start_date_time')->get()->count();
    }

    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        
        $ihistory = new InternetHistory;
        $month = quaterArrByNumber($this->quarter)[0];
        $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($month.'-'.$this->year));
        $endDate = internetEndMonthlyPaymentDate($month.'-'.$this->year);
        
        $invoiceArr = $ihistory->select(DB::raw('row_number() over(order by invoice_number asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date','total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('monthly_invoice_date', $endDate)->where('entry_type', 'MP')->orderBy('invoice_number')->get()->toArray();
        $installArr = $ihistory->select(DB::raw('row_number() over(order by internet_history.id asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date','installation_fee as total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->where('entry_type', 'NR')->orderBy('start_date_time')->get()->toArray();
        $serviceArr = $ihistory->select(DB::raw('row_number() over(order by internet_history.id asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date')->selectRaw('coalesce(reinstallation_fee,0) + coalesce(reconnect_fee,0) as total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->whereIn('entry_type', ['CL','RC'])->orderBy('start_date_time')->get()->toArray();
        
        $invoiceCollection = new Collection($invoiceArr);
        $installCollection = new Collection($installArr);
        $serviceCollection = new Collection($serviceArr);

        for($i=$this->invoiceRows; $i<=$this->invoiceRows+1; $i++)
        {
            $invoiceArr[$i]['id'] = ($i==$this->invoiceRows) ? 'Total' : NULL;
            $invoiceArr[$i]['num'] = NULL;
            $invoiceArr[$i]['name'] = NULL;
            $invoiceArr[$i]['internet_id'] = NULL;
            $invoiceArr[$i]['address_id'] = NULL;
            $invoiceArr[$i]['entry_type'] = 'MP';
            $invoiceArr[$i]['monthly_invoice_date'] = NULL;
            $invoiceArr[$i]['invoice_number'] = NULL;
            $invoiceArr[$i]['start_date_time'] = NULL;
            $invoiceArr[$i]['total_amount'] = ($i==$this->invoiceRows) ? amountFormat2($invoiceCollection->sum('total_amount')) : NULL;
        }

        for($i=$this->installRows; $i<=$this->installRows+1; $i++)
        {
            $installArr[$i]['id'] = ($i==$this->installRows) ? 'Total' : NULL;
            $installArr[$i]['num'] = NULL;
            $installArr[$i]['name'] = NULL;
            $installArr[$i]['internet_id'] = NULL;
            $installArr[$i]['address_id'] = NULL;
            $installArr[$i]['entry_type'] = 'NR';
            $installArr[$i]['monthly_invoice_date'] = NULL;
            $installArr[$i]['invoice_number'] = NULL;
            $installArr[$i]['start_date_time'] = NULL;
            $installArr[$i]['total_amount'] = ($i==$this->installRows) ? amountFormat2($installCollection->sum('total_amount')) : NULL;
        }
        
        $serviceArr[$this->serviceRows]['id'] = 'Total';
        $serviceArr[$this->serviceRows]['num'] = NULL;
        $serviceArr[$this->serviceRows]['name'] = NULL;
        $serviceArr[$this->serviceRows]['internet_id'] = NULL;
        $serviceArr[$this->serviceRows]['address_id'] = NULL;
        $serviceArr[$this->serviceRows]['entry_type'] = 'RC';
        $serviceArr[$this->serviceRows]['monthly_invoice_date'] = NULL;
        $serviceArr[$this->serviceRows]['invoice_number'] = NULL;
        $serviceArr[$this->serviceRows]['start_date_time'] = NULL;
        $serviceArr[$this->serviceRows]['total_amount'] = amountFormat2($serviceCollection->sum('total_amount'));
        
        $declarationArr = array_merge($invoiceArr, $installArr, $serviceArr);
        //pad($declarationArr);
        return collect($declarationArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Quarter '.$this->quarter.' - '.monthNumberToName2(quaterArrByNumber($this->quarter)[0]).' '.$this->year;
    }

    public function styles($sheet)
    {
        return [
            // Style the 1 row as bold text.
            1    => ['font' => ['name'=>'Khmer Muol', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                     'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            // Style the 2 row as bold text.
            2    => ['font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                     'borders' => [ 'vertical' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            // Style the 3 row as bold text.
            3    => ['font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ],

            'K2' => ['borders' => [ 'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
                
            'A4:K'.($this->invoiceRows+4) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],
            
            'A'.($this->invoiceRows+6).':K'.($this->invoiceRows+$this->installRows+6) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            'A'.($this->invoiceRows+8).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_CURRENCY_USD,
            'H' => NumberFormat::FORMAT_CURRENCY_USD,
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_CURRENCY_USD,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($declarationArr): array
    {   
        return [
            $declarationArr['num'],
            ($declarationArr['entry_type']=='MP') ? invoiceName($declarationArr['invoice_number'], $declarationArr['monthly_invoice_date']) : NULL,
            datePickerFormat3($declarationArr['start_date_time']),
            $declarationArr['internet_id'],            
            $declarationArr['name'],
            buildingLocationAndUnitNumberByUnitID($declarationArr['address_id']),
            amountFormat2($declarationArr['total_amount']/1.1),
            amountFormat2($declarationArr['total_amount']/11),
            '',
            amountFormat2($declarationArr['total_amount']),
            amountFormat2($declarationArr['total_amount']/11),
        ];
        
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:K1');
                $event->sheet->setCellValue('A1','តារាងលម្អិតប្រចាំខែ '.monthNumberToName2(quaterArrByNumber($this->quarter)[0]).' '.$this->year);
                $event->sheet->getRowDimension(1)->setRowHeight(30);

                $event->sheet->setCellValue('A2','No');
                $event->sheet->setCellValue('B2','Invoice');
                $event->sheet->setCellValue('C2','Date');
                $event->sheet->setCellValue('D2','Cust ID');
                $event->sheet->setCellValue('E2','Customer Name');
                $event->sheet->setCellValue('F2','Address');
                $event->sheet->setCellValue('G2','Internet Fee');
                $event->sheet->setCellValue('H2','VAT');
                $event->sheet->setCellValue('I2','SPT');
                $event->sheet->setCellValue('J2','Total');
                $event->sheet->setCellValue('K2','Amout pay to MPTC(10%)');

                $event->sheet->mergeCells('A3:K3');
                $event->sheet->setCellValue('A3','Internet Fee');
                $event->sheet->getRowDimension(3)->setRowHeight(25);
               
                
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                ];
                $style2 = [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                    'font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11,],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                ];

                $style3 = [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                    'font' => ['name'=>'Khmer OS Siemreap', 'bold' => true, 'size' => 11,],
                ];

                // Invoice Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+4).':F'.($this->invoiceRows+4));
                $event->sheet->setCellValue('A'.($this->invoiceRows+4),'Total');    
                $event->sheet->getStyle('A'.($this->invoiceRows+4).':K'.($this->invoiceRows+4))->applyFromArray($style2);             
                $event->sheet->getRowDimension(($this->invoiceRows+4))->setRowHeight(25); 

                // Installation fee
                $event->sheet->mergeCells('A'.($this->invoiceRows+5).':K'.($this->invoiceRows+5));
                $event->sheet->setCellValue('A'.($this->invoiceRows+5),'Installation Fee');   
                $event->sheet->getStyle('A'.($this->invoiceRows+5))->applyFromArray($style);              
                $event->sheet->getRowDimension(($this->invoiceRows+5))->setRowHeight(25); 

                // Installation Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+6).':F'.($this->invoiceRows+$this->installRows+6));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+6),'Total');       
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+6).':K'.($this->invoiceRows+$this->installRows+6))->applyFromArray($style2);          
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+6))->setRowHeight(25);  

                // Other Revenue
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+7).':K'.($this->invoiceRows+$this->installRows+7));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+7),'Other Revenue');   
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+7))->applyFromArray($style);              
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+7))->setRowHeight(25);  

                // Service Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8).':F'.($this->invoiceRows+$this->installRows+$this->serviceRows+8));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8),'Total');    
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8))->applyFromArray($style2);             
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+8))->setRowHeight(25);
                
                // Grand Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9).':F'.($this->invoiceRows+$this->installRows+$this->serviceRows+9));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'TOTAL:');  
                $event->sheet->setCellValue('G'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=G'.($this->invoiceRows+4).'+G'.($this->invoiceRows+$this->installRows+6).'+G'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('H'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=H'.($this->invoiceRows+4).'+H'.($this->invoiceRows+$this->installRows+6).'+H'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=J'.($this->invoiceRows+4).'+J'.($this->invoiceRows+$this->installRows+6).'+J'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('K'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=K'.($this->invoiceRows+4).'+K'.($this->invoiceRows+$this->installRows+6).'+K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8));
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+9))->applyFromArray($style2)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+8))->setRowHeight(25);

                // Signatures
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':B'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'ប្រធានក្រុមហ៊ុន');  
                $event->sheet->mergeCells('F'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':G'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('F'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'ប្រធានគណនេយ្យ/ហិរញ្ញវត្ថុ'); 
                $event->sheet->mergeCells('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'គណនេយ្យករ'); 
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+12))->applyFromArray($style3);
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+12))->setRowHeight(18);
                
            }
        ];
    }
    
}

########################################### Month1 Sheet End ###########################################

########################################### Month2 Sheet Start ###########################################

class Month2 implements FromCollection, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $quarter;
    protected $year;

    function __construct($quarter, $year) {
        $this->quarter = $quarter;
        $this->year = $year;
        
        $ihistory = new InternetHistory;
        $month = quaterArrByNumber($this->quarter)[1];
        $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($month.'-'.$this->year));
        $endDate = internetEndMonthlyPaymentDate($month.'-'.$this->year);

        $this->invoiceRows = $ihistory->select('id')->whereDate('monthly_invoice_date', $endDate)->where('entry_type', 'MP')->orderBy('invoice_number')->get()->count();
        $this->installRows = $ihistory->select('id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->where('entry_type', 'NR')->orderBy('start_date_time')->get()->count();
        $this->serviceRows = $ihistory->select('id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->whereIn('entry_type', ['CL','RC'])->orderBy('start_date_time')->get()->count();
    }

    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        
        $ihistory = new InternetHistory;
        $month = quaterArrByNumber($this->quarter)[1];
        $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($month.'-'.$this->year));
        $endDate = internetEndMonthlyPaymentDate($month.'-'.$this->year);

        $invoiceArr = $ihistory->select(DB::raw('row_number() over(order by invoice_number asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date','total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('monthly_invoice_date', $endDate)->where('entry_type', 'MP')->orderBy('invoice_number')->get()->toArray();
        $installArr = $ihistory->select(DB::raw('row_number() over(order by internet_history.id asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date','installation_fee as total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->where('entry_type', 'NR')->orderBy('start_date_time')->get()->toArray();
        $serviceArr = $ihistory->select(DB::raw('row_number() over(order by internet_history.id asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date')->selectRaw('coalesce(reinstallation_fee,0) + coalesce(reconnect_fee,0) as total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->whereIn('entry_type', ['CL','RC'])->orderBy('start_date_time')->get()->toArray();
        
        $invoiceCollection = new Collection($invoiceArr);
        $installCollection = new Collection($installArr);
        $serviceCollection = new Collection($serviceArr);

        for($i=$this->invoiceRows; $i<=$this->invoiceRows+1; $i++)
        {
            $invoiceArr[$i]['id'] = ($i==$this->invoiceRows) ? 'Total' : NULL;
            $invoiceArr[$i]['num'] = NULL;
            $invoiceArr[$i]['name'] = NULL;
            $invoiceArr[$i]['internet_id'] = NULL;
            $invoiceArr[$i]['address_id'] = NULL;
            $invoiceArr[$i]['entry_type'] = 'MP';
            $invoiceArr[$i]['monthly_invoice_date'] = NULL;
            $invoiceArr[$i]['invoice_number'] = NULL;
            $invoiceArr[$i]['start_date_time'] = NULL;
            $invoiceArr[$i]['total_amount'] = ($i==$this->invoiceRows) ? amountFormat2($invoiceCollection->sum('total_amount')) : NULL;
        }

        for($i=$this->installRows; $i<=$this->installRows+1; $i++)
        {
            $installArr[$i]['id'] = ($i==$this->installRows) ? 'Total' : NULL;
            $installArr[$i]['num'] = NULL;
            $installArr[$i]['name'] = NULL;
            $installArr[$i]['internet_id'] = NULL;
            $installArr[$i]['address_id'] = NULL;
            $installArr[$i]['entry_type'] = 'NR';
            $installArr[$i]['monthly_invoice_date'] = NULL;
            $installArr[$i]['invoice_number'] = NULL;
            $installArr[$i]['start_date_time'] = NULL;
            $installArr[$i]['total_amount'] = ($i==$this->installRows) ? amountFormat2($installCollection->sum('total_amount')) : NULL;
        }
        
        $serviceArr[$this->serviceRows]['id'] = 'Total';
        $serviceArr[$this->serviceRows]['num'] = NULL;
        $serviceArr[$this->serviceRows]['name'] = NULL;
        $serviceArr[$this->serviceRows]['internet_id'] = NULL;
        $serviceArr[$this->serviceRows]['address_id'] = NULL;
        $serviceArr[$this->serviceRows]['entry_type'] = 'RC';
        $serviceArr[$this->serviceRows]['monthly_invoice_date'] = NULL;
        $serviceArr[$this->serviceRows]['invoice_number'] = NULL;
        $serviceArr[$this->serviceRows]['start_date_time'] = NULL;
        $serviceArr[$this->serviceRows]['total_amount'] = amountFormat2($serviceCollection->sum('total_amount'));

        $declarationArr = array_merge($invoiceArr, $installArr, $serviceArr);
        //pad($declarationArr);
        return collect($declarationArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Quarter '.$this->quarter.' - '.monthNumberToName2(quaterArrByNumber($this->quarter)[1]).' '.$this->year;
    }

    public function styles($sheet)
    {
        return [
            // Style the 1 row as bold text.
            1    => ['font' => ['name'=>'Khmer Muol', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                     'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            // Style the 2 row as bold text.
            2    => ['font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                     'borders' => [ 'vertical' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            // Style the 3 row as bold text.
            3    => ['font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ],

            'K2' => ['borders' => [ 'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],

            'A4:K'.($this->invoiceRows+4) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],
            
            'A'.($this->invoiceRows+6).':K'.($this->invoiceRows+$this->installRows+6) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            'A'.($this->invoiceRows+8).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_CURRENCY_USD,
            'H' => NumberFormat::FORMAT_CURRENCY_USD,
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_CURRENCY_USD,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($declarationArr): array
    {   
        return [
            $declarationArr['num'],
            ($declarationArr['entry_type']=='MP') ? invoiceName($declarationArr['invoice_number'], $declarationArr['monthly_invoice_date']) : NULL,
            datePickerFormat3($declarationArr['start_date_time']),
            $declarationArr['internet_id'],
            $declarationArr['name'],
            buildingLocationAndUnitNumberByUnitID($declarationArr['address_id']),            
            amountFormat2($declarationArr['total_amount']/1.1),
            amountFormat2($declarationArr['total_amount']/11),
            '',
            amountFormat2($declarationArr['total_amount']),
            amountFormat2($declarationArr['total_amount']/11),
        ];
        
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:K1');
                $event->sheet->setCellValue('A1','តារាងលម្អិតប្រចាំខែ '.monthNumberToName2(quaterArrByNumber($this->quarter)[1]).' '.$this->year);
                $event->sheet->getRowDimension(1)->setRowHeight(30);

                $event->sheet->setCellValue('A2','No');
                $event->sheet->setCellValue('B2','Invoice');
                $event->sheet->setCellValue('C2','Date');
                $event->sheet->setCellValue('D2','Cust ID');
                $event->sheet->setCellValue('E2','Customer Name');
                $event->sheet->setCellValue('F2','Address');
                $event->sheet->setCellValue('G2','Internet Fee');
                $event->sheet->setCellValue('H2','VAT');
                $event->sheet->setCellValue('I2','SPT');
                $event->sheet->setCellValue('J2','Total');
                $event->sheet->setCellValue('K2','Amout pay to MPTC(10%)');

                $event->sheet->mergeCells('A3:K3');
                $event->sheet->setCellValue('A3','Internet Fee');
                $event->sheet->getRowDimension(3)->setRowHeight(25);
               
                
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                ];
                $style2 = [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                    'font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11,],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                ];
                $style3 = [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                    'font' => ['name'=>'Khmer OS Siemreap', 'bold' => true, 'size' => 11,],
                ];

                // Invoice Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+4).':F'.($this->invoiceRows+4));
                $event->sheet->setCellValue('A'.($this->invoiceRows+4),'Total');    
                $event->sheet->getStyle('A'.($this->invoiceRows+4).':K'.($this->invoiceRows+4))->applyFromArray($style2);             
                $event->sheet->getRowDimension(($this->invoiceRows+4))->setRowHeight(25); 

                // Installation fee
                $event->sheet->mergeCells('A'.($this->invoiceRows+5).':K'.($this->invoiceRows+5));
                $event->sheet->setCellValue('A'.($this->invoiceRows+5),'Installation Fee');   
                $event->sheet->getStyle('A'.($this->invoiceRows+5))->applyFromArray($style);              
                $event->sheet->getRowDimension(($this->invoiceRows+5))->setRowHeight(25); 

                // Installation Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+6).':F'.($this->invoiceRows+$this->installRows+6));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+6),'Total');       
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+6).':K'.($this->invoiceRows+$this->installRows+6))->applyFromArray($style2);          
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+6))->setRowHeight(25);  

                // Other Revenue
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+7).':K'.($this->invoiceRows+$this->installRows+7));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+7),'Other Revenue');   
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+7))->applyFromArray($style);              
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+7))->setRowHeight(25);  

                // Service Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8).':F'.($this->invoiceRows+$this->installRows+$this->serviceRows+8));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8),'Total');    
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8))->applyFromArray($style2);             
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+8))->setRowHeight(25);
                
                // Grand Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9).':F'.($this->invoiceRows+$this->installRows+$this->serviceRows+9));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'TOTAL:');  
                $event->sheet->setCellValue('G'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=G'.($this->invoiceRows+4).'+G'.($this->invoiceRows+$this->installRows+6).'+G'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('H'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=H'.($this->invoiceRows+4).'+H'.($this->invoiceRows+$this->installRows+6).'+H'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=J'.($this->invoiceRows+4).'+J'.($this->invoiceRows+$this->installRows+6).'+J'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('K'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=K'.($this->invoiceRows+4).'+K'.($this->invoiceRows+$this->installRows+6).'+K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8));
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+9))->applyFromArray($style2)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+8))->setRowHeight(25);

                // Signatures
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':B'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'ប្រធានក្រុមហ៊ុន');  
                $event->sheet->mergeCells('F'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':G'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('F'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'ប្រធានគណនេយ្យ/ហិរញ្ញវត្ថុ'); 
                $event->sheet->mergeCells('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'គណនេយ្យករ'); 
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+12))->applyFromArray($style3);
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+12))->setRowHeight(18);
                
            }
        ];
    }
    
}

########################################### Month2 Sheet End ###########################################

########################################### Month3 Sheet Start ###########################################

class Month3 implements FromCollection, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $quarter;
    protected $year;

    function __construct($quarter, $year) {
        $this->quarter = $quarter;
        $this->year = $year;
        
        $ihistory = new InternetHistory;
        $month = quaterArrByNumber($this->quarter)[2];
        $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($month.'-'.$this->year));
        $endDate = internetEndMonthlyPaymentDate($month.'-'.$this->year);

        $this->invoiceRows = $ihistory->select('id')->whereDate('monthly_invoice_date', $endDate)->where('entry_type', 'MP')->orderBy('invoice_number')->get()->count();
        $this->installRows = $ihistory->select('id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->where('entry_type', 'NR')->orderBy('start_date_time')->get()->count();
        $this->serviceRows = $ihistory->select('id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->whereIn('entry_type', ['CL','RC'])->orderBy('start_date_time')->get()->count();
    }

    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        
        $ihistory = new InternetHistory;
        $month = quaterArrByNumber($this->quarter)[2];
        $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($month.'-'.$this->year));
        $endDate = internetEndMonthlyPaymentDate($month.'-'.$this->year);

        $invoiceArr = $ihistory->select(DB::raw('row_number() over(order by invoice_number asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date','total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('monthly_invoice_date', $endDate)->where('entry_type', 'MP')->orderBy('invoice_number')->get()->toArray();
        $installArr = $ihistory->select(DB::raw('row_number() over(order by internet_history.id asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date','installation_fee as total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->where('entry_type', 'NR')->orderBy('start_date_time')->get()->toArray();
        $serviceArr = $ihistory->select(DB::raw('row_number() over(order by internet_history.id asc) AS num'),'internet_history.id','name','internet_id','internet_history.address_id','entry_type','invoice_number','start_date_time','monthly_invoice_date')->selectRaw('coalesce(reinstallation_fee,0) + coalesce(reconnect_fee,0) as total_amount')->leftJoin('customers', 'customers.id', '=', 'internet_history.customer_id')->whereDate('start_date_time', '>=', $startDate)->whereDate('start_date_time', '<=', $endDate)->whereIn('entry_type', ['CL','RC'])->orderBy('start_date_time')->get()->toArray();
        
        $invoiceCollection = new Collection($invoiceArr);
        $installCollection = new Collection($installArr);
        $serviceCollection = new Collection($serviceArr);

        for($i=$this->invoiceRows; $i<=$this->invoiceRows+1; $i++)
        {
            $invoiceArr[$i]['id'] = ($i==$this->invoiceRows) ? 'Total' : NULL;
            $invoiceArr[$i]['num'] = NULL;
            $invoiceArr[$i]['name'] = NULL;
            $invoiceArr[$i]['internet_id'] = NULL;
            $invoiceArr[$i]['address_id'] = NULL;
            $invoiceArr[$i]['entry_type'] = 'MP';
            $invoiceArr[$i]['monthly_invoice_date'] = NULL;
            $invoiceArr[$i]['invoice_number'] = NULL;
            $invoiceArr[$i]['start_date_time'] = NULL;
            $invoiceArr[$i]['total_amount'] = ($i==$this->invoiceRows) ? amountFormat2($invoiceCollection->sum('total_amount')) : NULL;
        }

        for($i=$this->installRows; $i<=$this->installRows+1; $i++)
        {
            $installArr[$i]['id'] = ($i==$this->installRows) ? 'Total' : NULL;
            $installArr[$i]['num'] = NULL;
            $installArr[$i]['name'] = NULL;
            $installArr[$i]['internet_id'] = NULL;
            $installArr[$i]['address_id'] = NULL;
            $installArr[$i]['entry_type'] = 'NR';
            $installArr[$i]['monthly_invoice_date'] = NULL;
            $installArr[$i]['invoice_number'] = NULL;
            $installArr[$i]['start_date_time'] = NULL;
            $installArr[$i]['total_amount'] = ($i==$this->installRows) ? amountFormat2($installCollection->sum('total_amount')) : NULL;
        }
        
        $serviceArr[$this->serviceRows]['id'] = 'Total';
        $serviceArr[$this->serviceRows]['num'] = NULL;
        $serviceArr[$this->serviceRows]['name'] = NULL;
        $serviceArr[$this->serviceRows]['internet_id'] = NULL;
        $serviceArr[$this->serviceRows]['address_id'] = NULL;
        $serviceArr[$this->serviceRows]['entry_type'] = 'RC';
        $serviceArr[$this->serviceRows]['monthly_invoice_date'] = NULL;
        $serviceArr[$this->serviceRows]['invoice_number'] = NULL;
        $serviceArr[$this->serviceRows]['start_date_time'] = NULL;
        $serviceArr[$this->serviceRows]['total_amount'] = amountFormat2($serviceCollection->sum('total_amount'));
        
        $declarationArr = array_merge($invoiceArr, $installArr, $serviceArr);
        //pad($declarationArr);
        return collect($declarationArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Quarter '.$this->quarter.' - '.monthNumberToName2(quaterArrByNumber($this->quarter)[2]).' '.$this->year;
    }

    public function styles($sheet)
    {
        return [
            // Style the 1 row as bold text.
            1    => ['font' => ['name'=>'Khmer Muol', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                     'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            // Style the 2 row as bold text.
            2    => ['font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                     'borders' => [ 'vertical' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            // Style the 3 row as bold text.
            3    => ['font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ],
                    
            'K2' => ['borders' => [ 'right' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],

            'A4:K'.($this->invoiceRows+4) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],
            
            'A'.($this->invoiceRows+6).':K'.($this->invoiceRows+$this->installRows+6) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],

            'A'.($this->invoiceRows+8).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8) => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_CURRENCY_USD,
            'H' => NumberFormat::FORMAT_CURRENCY_USD,
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_CURRENCY_USD,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
        ];
    }
    
    /**
    * @return MappingWithHeadings
    */
    public function map($declarationArr): array
    {   
        return [
            $declarationArr['num'],
            ($declarationArr['entry_type']=='MP') ? invoiceName($declarationArr['invoice_number'], $declarationArr['monthly_invoice_date']) : NULL,
            datePickerFormat3($declarationArr['start_date_time']),
            $declarationArr['internet_id'],
            $declarationArr['name'],
            buildingLocationAndUnitNumberByUnitID($declarationArr['address_id']),            
            amountFormat2($declarationArr['total_amount']/1.1),
            amountFormat2($declarationArr['total_amount']/11),
            '',
            amountFormat2($declarationArr['total_amount']),
            amountFormat2($declarationArr['total_amount']/11),
        ];
        
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:K1');
                $event->sheet->setCellValue('A1','តារាងលម្អិតប្រចាំខែ '.monthNumberToName2(quaterArrByNumber($this->quarter)[2]).' '.$this->year);
                $event->sheet->getRowDimension(1)->setRowHeight(30);

                $event->sheet->setCellValue('A2','No');
                $event->sheet->setCellValue('B2','Invoice');
                $event->sheet->setCellValue('C2','Date');
                $event->sheet->setCellValue('D2','Cust ID');
                $event->sheet->setCellValue('E2','Customer Name');
                $event->sheet->setCellValue('F2','Address');
                $event->sheet->setCellValue('G2','Internet Fee');
                $event->sheet->setCellValue('H2','VAT');
                $event->sheet->setCellValue('I2','SPT');
                $event->sheet->setCellValue('J2','Total');
                $event->sheet->setCellValue('K2','Amout pay to MPTC(10%)');

                $event->sheet->mergeCells('A3:K3');
                $event->sheet->setCellValue('A3','Internet Fee');
                $event->sheet->getRowDimension(3)->setRowHeight(25);
               
                
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11, 'color' => ['argb' => '002060'],],
                ];
                $style2 = [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                    'font' => ['name' => 'Times New Roman', 'bold' => true, 'size' => 11,],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                ];
                $style3 = [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                    'font' => ['name'=>'Khmer OS Siemreap', 'bold' => true, 'size' => 11,],
                ];

                // Invoice Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+4).':F'.($this->invoiceRows+4));
                $event->sheet->setCellValue('A'.($this->invoiceRows+4),'Total');    
                $event->sheet->getStyle('A'.($this->invoiceRows+4).':K'.($this->invoiceRows+4))->applyFromArray($style2);             
                $event->sheet->getRowDimension(($this->invoiceRows+4))->setRowHeight(25); 

                // Installation fee
                $event->sheet->mergeCells('A'.($this->invoiceRows+5).':K'.($this->invoiceRows+5));
                $event->sheet->setCellValue('A'.($this->invoiceRows+5),'Installation Fee');   
                $event->sheet->getStyle('A'.($this->invoiceRows+5))->applyFromArray($style);              
                $event->sheet->getRowDimension(($this->invoiceRows+5))->setRowHeight(25); 

                // Installation Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+6).':F'.($this->invoiceRows+$this->installRows+6));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+6),'Total');       
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+6).':K'.($this->invoiceRows+$this->installRows+6))->applyFromArray($style2);          
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+6))->setRowHeight(25);  

                // Other Revenue
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+7).':K'.($this->invoiceRows+$this->installRows+7));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+7),'Other Revenue');   
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+7))->applyFromArray($style);              
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+7))->setRowHeight(25);  

                // Service Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8).':F'.($this->invoiceRows+$this->installRows+$this->serviceRows+8));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8),'Total');    
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+8).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8))->applyFromArray($style2);             
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+8))->setRowHeight(25);
                
                // Grand Total
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9).':F'.($this->invoiceRows+$this->installRows+$this->serviceRows+9));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'TOTAL:');  
                $event->sheet->setCellValue('G'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=G'.($this->invoiceRows+4).'+G'.($this->invoiceRows+$this->installRows+6).'+G'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('H'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=H'.($this->invoiceRows+4).'+H'.($this->invoiceRows+$this->installRows+6).'+H'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=J'.($this->invoiceRows+4).'+J'.($this->invoiceRows+$this->installRows+6).'+J'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->setCellValue('K'.($this->invoiceRows+$this->installRows+$this->serviceRows+9),'=K'.($this->invoiceRows+4).'+K'.($this->invoiceRows+$this->installRows+6).'+K'.($this->invoiceRows+$this->installRows+$this->serviceRows+8)); 
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+9).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+9))->applyFromArray($style2)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_USD);
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+8))->setRowHeight(25);

                // Signatures
                $event->sheet->mergeCells('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':B'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'ប្រធានក្រុមហ៊ុន');  
                $event->sheet->mergeCells('F'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':G'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('F'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'ប្រធានគណនេយ្យ/ហិរញ្ញវត្ថុ'); 
                $event->sheet->mergeCells('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+12));
                $event->sheet->setCellValue('J'.($this->invoiceRows+$this->installRows+$this->serviceRows+12),'គណនេយ្យករ'); 
                $event->sheet->getStyle('A'.($this->invoiceRows+$this->installRows+$this->serviceRows+12).':K'.($this->invoiceRows+$this->installRows+$this->serviceRows+12))->applyFromArray($style3);
                $event->sheet->getRowDimension(($this->invoiceRows+$this->installRows+$this->serviceRows+12))->setRowHeight(18);
                
            }
        ];
    }
    
}

########################################### Month3 Sheet End ###########################################