<?php

namespace App\Exports;

use App\Models\InternetHistory;
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

class MPTCQuarterlyIncomeStatementExport implements WithMultipleSheets, WithPreCalculateFormulas
{
    protected $year;
    protected $quarter;
    
    function __construct($year, $quarter) {
        $this->year = $year;
        $this->quarter = $quarter;
        $this->totalRows = 12;
    }

    public function sheets(): array
    {
        $sheets = [            
            new Monthly($this->year, $this->quarter, $this->totalRows),
            new Quarterly($this->year, $this->quarter, $this->totalRows),
        ];

        return $sheets;
    }
}

########################################### Monthly Sheet Start ###########################################

class Monthly implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle, WithDrawings
{
    protected $year;
    protected $quarter;
    protected $totalRows;    

    function __construct($quarter, $year, $totalRows) {
        $this->year = $year;    
        $this->quarter = $quarter;
        $this->totalRows = $totalRows;
    }

    /**
    * @return PhpOffice\PhpSpreadsheet\Worksheet\Drawing
    */
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/assets/logo/world city.png'));
        $drawing->setHeight(120);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $quarterArr = quaterArrByNumber($this->quarter);
        $monthlyArr= [];
        
        foreach($quarterArr as $qkey=> $qval)
        {
            $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($qval.'-'.$this->year));
            $endDate = internetEndMonthlyPaymentDate($qval.'-'.$this->year);
            
            $monthlyArr[$qkey]['invoice_number'] = getStartAndEndInvoiceNumberByMonth($startDate, $endDate);
            $monthlyArr[$qkey]['invoice_date'] = datePickerFormat2($this->year.'-'.$qval.'-'.env('MONTHLY_INVOICE_DATE'));
            $monthlyArr[$qkey]['total_amount'] = amountFormat2(getInvoiceAmountByMonth($startDate,$endDate)+getInstallationAmountByMonth($startDate, $endDate)+getServiceAmountByMonth($startDate, $endDate));
        }
        return collect($monthlyArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return monthNumberToName2(quaterArrByNumber($this->quarter)[0]).'-'.monthNumberToName2(quaterArrByNumber($this->quarter)[1]).' and '.monthNumberToName2(quaterArrByNumber($this->quarter)[2]);
    }

    public function styles($sheet)
    {
        return [
            
            // Style the 2 row as bold text.
            2    => ['font' => ['bold' => true, 'size' => 14,],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ],

            // Style the 3 row as bold text.
            3    => ['font' => ['bold' => true, 'size' => 14,],],

            'A3:H'.($this->totalRows+4) => [
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
            'C' => NumberFormat::FORMAT_DATE_XLSX15,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:H1');
                $event->sheet->getRowDimension(1)->setRowHeight(120);

                $event->sheet->mergeCells('A2:H2');
                $event->sheet->setCellValue('A2',str_replace('\n', "\n",'Monthly Income Statement\n From '.monthNumberToName(quaterArrByNumber($this->quarter)[0]).' To '. monthNumberToName(quaterArrByNumber($this->quarter)[2]).' '.$this->year));
                $event->sheet->getRowDimension(2)->setRowHeight(75);
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                ];
                

                $event->sheet->mergeCells('A23:B23');
                $event->sheet->mergeCells('G23:H23');
                $event->sheet->setCellValue('A23','Prepared By:');
                $event->sheet->setCellValue('G23','Verify By:');
                $event->sheet->getStyle('A23:B23')->applyFromArray($style);               
                $event->sheet->getStyle('G23:H23')->applyFromArray($style);

                for( $row = 3; $row <= 22; $row++){
                    $event->sheet->getRowDimension($row)->setRowHeight(40);
                }
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
            'Date',
            'Company Name',
            'Description',
            'Service Fee',
            'VAT',
            'Total',
        ];
    }
  
    /**
    * @return MappingWithHeadings
    */
    public function map($monthlyArr): array
    {   
        return [
            '=ROW()-3',
            $monthlyArr['invoice_number'],
            $monthlyArr['invoice_date'],
            'World City Co., Ltd',
            'Internet Service Fee',
            amountFormat2($monthlyArr['total_amount']/1.1),
            amountFormat2($monthlyArr['total_amount']/11),
            amountFormat2($monthlyArr['total_amount']),
        ];
        
    }
}

########################################### Monthly Sheet End ###########################################

########################################### Quarterly Sheet Start ###########################################

class Quarterly implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle, WithDrawings
{
    protected $year;
    protected $quarter;
    protected $totalRows;    

    function __construct($quarter, $year, $totalRows) {
        $this->year = $year;    
        $this->quarter = $quarter;
        $this->totalRows = $totalRows;
    }

    /**
    * @return PhpOffice\PhpSpreadsheet\Worksheet\Drawing
    */
    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setPath(public_path('/assets/logo/world city.png'));
        $drawing->setHeight(120);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $quarterlyArr= [];
        $quarterArr = quaterArrByNumber($this->quarter);
        $startDate = nextDateOFGivenDate(internetStartMonthlyPaymentDate($quarterArr[0].'-'.$this->year));
        $endDate = internetEndMonthlyPaymentDate($quarterArr[2].'-'.$this->year);    
        $quarterlyArr[0]['description'] = 'Internet Service Fee\n'.monthNumberToName(quaterArrByNumber($this->quarter)[0]).' to '.monthNumberToName(quaterArrByNumber($this->quarter)[2]).' '.$this->year;
        $quarterlyArr[0]['total_amount'] = amountFormat2(getInvoiceAmountByMonth($startDate,$endDate)+getInstallationAmountByMonth($startDate, $endDate)+getServiceAmountByMonth($startDate, $endDate));
        
        return collect($quarterlyArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Q'.$this->quarter;
    }

    public function styles($sheet)
    {
        return [
            
            // Style the 2 row as bold text.
            2    => ['font' => ['bold' => true, 'size' => 14,],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ],

            // Style the 3 row as bold text.
            3    => ['font' => ['bold' => true, 'size' => 14,],],

            'A3:F16' => [
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                    ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'F' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:F1');
                $event->sheet->getRowDimension(1)->setRowHeight(120);

                $event->sheet->mergeCells('A2:F2');
                $event->sheet->setCellValue('A2',str_replace('\n', "\n",'Quarterly Income Statement\n From '.monthNumberToName(quaterArrByNumber($this->quarter)[0]).' To '. monthNumberToName(quaterArrByNumber($this->quarter)[2]).' '.$this->year));
                $event->sheet->getRowDimension(2)->setRowHeight(75);
            },
            AfterSheet::class => function(AfterSheet $event) {

                $style = [
                    'alignment' => [ 
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'top' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],
                ];
                
                $event->sheet->setCellValue('A16','Total');
                $event->sheet->setCellValue('C16','=SUM(C4:C15)');
                $event->sheet->setCellValue('D16','=SUM(D4:D15)');
                $event->sheet->setCellValue('E16','=SUM(E4:E15)');
                $event->sheet->setCellValue('F16','=SUM(F4:F15)');
                $event->sheet->getStyle('C16:F16')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

                $event->sheet->mergeCells('A23:B23');
                $event->sheet->mergeCells('G23:H23');
                $event->sheet->setCellValue('A23','Prepared By:');
                $event->sheet->setCellValue('F23','Verify By:');
                $event->sheet->getStyle('A23:B23')->applyFromArray($style);               
                $event->sheet->getStyle('F23')->applyFromArray($style);

                for( $row = 3; $row <= 19; $row++){
                    $event->sheet->getRowDimension($row)->setRowHeight(60);
                }
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
            'Description',
            'Service Fee',
            'VAT',
            'Total',
            str_replace('\n', "\n",'Amount Pay to MPTC (KHR/US)\nយោងទៅតាមអាជ្ញាបណ្ណ\n(10%)'),
        ];
    }
  
    /**
    * @return MappingWithHeadings
    */
    public function map($quarterlyArr): array
    {   
        return [
            '=ROW()-3',
            str_replace('\n', "\n",$quarterlyArr['description']),
            amountFormat2($quarterlyArr['total_amount']/1.1),
            amountFormat2($quarterlyArr['total_amount']/11),
            amountFormat2($quarterlyArr['total_amount']),
            amountFormat2($quarterlyArr['total_amount']/11),
        ];
        
    }
}

########################################### Quarterly Sheet End ###########################################