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

class UnpaidMonthlyInternetExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    
    protected $year;
    protected $month;
    protected $totalRows;

    function __construct($month, $year) {
        
        $ihistory = new InternetHistory;
        $this->year = $year;
        $this->month = $month;        
        $this->totalRows = !empty($this->month) ? $ihistory->select('id')->where('entry_type','MP')->whereMonth('monthly_invoice_date', $this->month)->whereYear('monthly_invoice_date', $this->year)->where('paid','N')->count('id')+1 : $ihistory->select('id')->where('entry_type','MP')->where('paid','N')->count('id')+1;
    }
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $ihistory = new InternetHistory;
        $unPaidArr = !empty($this->month) ? $ihistory->select('id','customer_id','address_id','plan_id','invoice_number','monthly_invoice_date','balance','vat_amount','total_amount','payment_mode','payment_description','paid','paid_date','remark')->where('entry_type','MP')->whereMonth('monthly_invoice_date', $this->month)->whereYear('monthly_invoice_date', $this->year)->where('paid','N')->orderBy('address_id')->get() : $ihistory->select('id','customer_id','address_id','plan_id','invoice_number','monthly_invoice_date','balance','vat_amount','total_amount','payment_mode','payment_description','paid','paid_date','remark')->where('entry_type','MP')->where('paid','N')->orderBy('address_id')->get();
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
            
            'A1:N'.($this->totalRows+1) => ['borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
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
        
                $event->sheet->mergeCells('A1:N1');
                $event->sheet->setCellValue('A1', str_replace('\n', "\n", '1- Do not change Ids.\n2- Do not change column positions.\n3- If customer paid change paid column as Y and enter paid date in (DD-MMM-YYYY) format only.\n4- Customers who paid \n(i)by Cash payment mode column should be "CA" \n(ii)by Bank payment mode column should be "BA" \n(iii)by Cheque payment mode column should be "CH" \n(iv)by Other payment mode column should be "OT".\n5- If Payment mode is Bank or Cheque or Other please enter transaction details at payment description column.\n6- You can add your comment at remark column.'));
                $event->sheet->getStyle('A1')->applyFromArray($style);
                $event->sheet->getRowDimension(1)->setRowHeight(150);
            },
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->getRowDimension(2)->setRowHeight(30);
                $event->sheet->getDelegate()->getStyle('A3:N'.($this->totalRows+1))->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
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
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'G' => NumberFormat::FORMAT_CURRENCY_USD,
            'H' => NumberFormat::FORMAT_CURRENCY_USD,
            'I' => NumberFormat::FORMAT_CURRENCY_USD,
            'J' => NumberFormat::FORMAT_TEXT,
            'K' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
            'M' => NumberFormat::FORMAT_TEXT,
            'N' => NumberFormat::FORMAT_TEXT,
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
            'Plan Name',
            'Invoice No.',
            'Invoice Date',
            'Balance',            
            'VAT',
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
    public function map($ihistory): array
    {   
        return [
            $ihistory->id,
            (customerDetailsByID($ihistory->customer_id)['type']=='P')?customerDetailsByID($ihistory->customer_id)['name']:customerDetailsByID($ihistory->customer_id)['shop_name'],
            buildingLocationAndUnitNumberByUnitID($ihistory->address_id),
            planDetailsByPlanID($ihistory->plan_id)['plan_name'],
            invoiceName($ihistory->invoice_number, $ihistory->monthly_invoice_date),
            datePickerFormat2($ihistory->monthly_invoice_date),
            amountFormat2($ihistory->balance),
            amountFormat2($ihistory->vat_amount),
            amountFormat2($ihistory->total_amount),
            $ihistory->payment_mode,
            'Paid at MO by Bank',
            $ihistory->paid,
            ($ihistory->paid=='Y') ? datePickerFormat2($ihistory->paid_date) : '',
            $ihistory->remark,
        ];
        
    }
}
