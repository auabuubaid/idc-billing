<?php

namespace App\Exports;

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

class UnpaidMonthlyCableTVExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $year;
    protected $month;
    protected $totalRows;

    function __construct($month, $year) {
        
        $chistory = new CableTVHistory;
        $this->year = $year;
        $this->month = $month;
        $this->totalRows = $chistory->select('id')->where('entry_type','MP')->whereMonth('monthly_invoice_date', $this->month)->whereYear('monthly_invoice_date', $this->year)->where('paid', 'N')->count('id')+1;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $chistory = new CableTVHistory;
        $unPaidArr = $chistory->select('id','customer_id','address_id','plan_id','invoice_number','monthly_invoice_date','monthly_fee','total_amount','payment_mode','payment_description','paid','paid_date','remark')->where('entry_type','MP')->whereMonth('monthly_invoice_date', $this->month)->whereYear('monthly_invoice_date', $this->year)->where('paid', 'N')->orderBy('address_id')->get();
        return $unPaidArr;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Unpaid Invoices';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1   => ['font' => ['italic' => true, 'color'=> ['rgb' => 'FF0000']],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFFFF']],
                     'alignment' => ['wrapText' => true],   
                    ],
            
            2   => ['font' => ['bold' => true, 'color'=> ['rgb' => 'FFFFFF']],
                     'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => '3464AB']],
                     'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,],
                    ],
            
            'A1:L'.($this->totalRows+1) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
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
        
                $event->sheet->mergeCells('A1:L1');
                $event->sheet->setCellValue('A1', str_replace('\n', "\n", '1- Do not change Ids.\n2- Do not change column positions.\n3- If customer paid change paid column as Y and enter paid date in (DD-MMM-YYYY) format only.\n4- Customers who paid \n(i)by Cash payment mode column should be "CA" \n(ii)by Bank payment mode column should be "BA" \n(iii)by Cheque payment mode column should be "CH" \n(iv)by Other payment mode column should be "OT".\n5- If Payment mode is Bank or Cheque or Other please enter transaction details at payment description column.\n6- You can add your comment at remark column.'));
                $event->sheet->getStyle('A1')->applyFromArray($style);
                $event->sheet->getRowDimension(1)->setRowHeight(150);
            },
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getRowDimension(2)->setRowHeight(30);
                $event->sheet->getDelegate()->getStyle('A3:L'.($this->totalRows+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'F' => NumberFormat::FORMAT_CURRENCY_USD,
            'G' => NumberFormat::FORMAT_CURRENCY_USD,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'ID',
            'Customer Name',
            'Unit No.',
            'Invoice No.',
            'Invoice Date',
            'Monthly Fee',  
            'Total',
            'Payment Mode',
            'Payment Description',
            'Paid',
            'Paid Date',
            'Remark',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($chistory): array
    {   
        return [
            $chistory->id,
            (customerDetailsByID($chistory->customer_id)['type']=='P')?customerDetailsByID($chistory->customer_id)['name']:customerDetailsByID($chistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($chistory->address_id),
            invoiceName($chistory->invoice_number, $chistory->monthly_invoice_date),
            datePickerFormat2($chistory->monthly_invoice_date),
            amountFormat2($chistory->monthly_fee),
            amountFormat2($chistory->total_amount),
            $chistory->payment_mode,
            'Paid at MO by Bank',
            $chistory->paid,
            ($chistory->paid=='Y') ? datePickerFormat2($chistory->paid_date) : '',
            $chistory->remark,
        ];
        
    }
}
