<?php

namespace App\Exports;

use DB;
use App\Models\UnitAddress;
use App\Models\CableTVHistory;
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

class WorldCitySummaryExport implements WithMultipleSheets, WithPreCalculateFormulas
{
    protected $yearMonth;

    function __construct($yearMonth) {
        $this->yearMonth = $yearMonth;
    }

    public function sheets(): array
    {
        $sheets = [
            //new Summary($this->yearMonth),
            //new 상세내역(주거)($this->yearMonth),
            //new 상세내역(상가)($this->yearMonth),
            new A101($this->yearMonth),
            new A102($this->yearMonth),
            new A103($this->yearMonth),
            new A104($this->yearMonth),
            new A105($this->yearMonth),
            new A106($this->yearMonth),
            new A107($this->yearMonth),
            new A108($this->yearMonth),
            new East_Shops($this->yearMonth),
            new South_Shops($this->yearMonth),
            new R1_Town($this->yearMonth),
            new R1_Villa($this->yearMonth),
            new R2_Villa($this->yearMonth),
        ];

        return $sheets;
    }
}

########################################### A101 Sheet Start ###########################################

class A101 implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'A','building'=>'A101','name'=>'A101'];
            //DB::enableQueryLog();
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderBy('units_address.id')->count('units_address.id'));
            //$sql= DB::getQueryLog();
            //dd($sql);
            //dd($this->totalRows);
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderByDesc('units_address.id')->get()->all();
        
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];
        //echo $startDate.' * '.$endDate; die;
        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        //pad($summaryReportArr);
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'A101';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['building']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### A101 Sheet End ###########################################

########################################### A102 Sheet Start ###########################################

    class A102 implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
    {
        protected $yearMonth;
        protected $buildingArr;
        protected $totalRows;    
    
        function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'A','building'=>'A102','name'=>'A102'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderBy('units_address.id')->count('units_address.id'));
        }
    
        /**
        * @return \Illuminate\Support\Collection
        */
        public function collection()
        {   
            $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderByDesc('units_address.id')->get()->all();
            $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
            $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];
            
            if(count($summaryReportArr)>0){
                foreach($summaryReportArr as $rkey=>$rval){
                    $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                    $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                    $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                    $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                    $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                    $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                    $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                    $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
                }
            }
            return collect($summaryReportArr);
        }
    
        /**
         * @return string
         */
        public function title(): string
        {
            return 'A102';
        }
    
        public function styles($sheet)
        {
            return [
                // Style the first row as bold text.
                1    => ['font' => ['bold' => true],
                         //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                         'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],],
                
                2    => ['font' => ['bold' => true],
                        //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],],
    
                3    => ['font' => ['bold' => true],
                        //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],],
                
                'A1:Y'.($this->totalRows+3) => [
                        'alignment' => [
                            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
            ];
        }
    
        public function columnFormats(): array
        {
            return [
                'A' => NumberFormat::FORMAT_TEXT,
                'B' => NumberFormat::FORMAT_NUMBER,
                'C' => NumberFormat::FORMAT_NUMBER,
                'D' => NumberFormat::FORMAT_NUMBER,
                'E' => NumberFormat::FORMAT_NUMBER,
                'F' => NumberFormat::FORMAT_NUMBER,
                'G' => NumberFormat::FORMAT_NUMBER,
                'H' => NumberFormat::FORMAT_NUMBER,
                'I' => NumberFormat::FORMAT_NUMBER,
                'J' => NumberFormat::FORMAT_NUMBER,
                'K' => NumberFormat::FORMAT_CURRENCY_USD,
                'L' => NumberFormat::FORMAT_NUMBER,
                'M' => NumberFormat::FORMAT_CURRENCY_USD,
                'N' => NumberFormat::FORMAT_CURRENCY_USD,
                'O' => NumberFormat::FORMAT_NUMBER,
                'P' => NumberFormat::FORMAT_CURRENCY_USD,
                'Q' => NumberFormat::FORMAT_CURRENCY_USD,
                'R' => NumberFormat::FORMAT_NUMBER,
                'S' => NumberFormat::FORMAT_CURRENCY_USD,
                'T' => NumberFormat::FORMAT_CURRENCY_USD,
                'U' => NumberFormat::FORMAT_NUMBER,
                'V' => NumberFormat::FORMAT_CURRENCY_USD,
                'W' => NumberFormat::FORMAT_NUMBER,
                'X' => NumberFormat::FORMAT_NUMBER,
                'Y' => NumberFormat::FORMAT_NUMBER,            
            ];
        }
    
        public function registerEvents(): array
        {
            return [
    
                BeforeSheet::class => function(BeforeSheet $event) {
    
                    $event->sheet->mergeCells('A1:A3');
                    $event->sheet->setCellValue('A1',$this->buildingArr['building']);
    
                    $event->sheet->mergeCells('B1:B2');
                    $event->sheet->setCellValue('B1','Total');
    
                    $event->sheet->mergeCells('C1:C2');
                    $event->sheet->setCellValue('C1','World City');
    
                    $event->sheet->mergeCells('D1:G1');
                    $event->sheet->setCellValue('D1','Sales');
    
                    $event->sheet->setCellValue('H1','MO');
    
                    $event->sheet->mergeCells('I1:J1');
                    $event->sheet->setCellValue('I1','IDC');
    
                    $event->sheet->mergeCells('K1:M1');
                    $event->sheet->setCellValue('K1','Rental');
    
                    $event->sheet->mergeCells('N1:P1');
                    $event->sheet->setCellValue('N1','Internet');
    
                    $event->sheet->mergeCells('Q1:S1');
                    $event->sheet->setCellValue('Q1','Cable TV');
    
                    $event->sheet->mergeCells('T1:Y1');
                    $event->sheet->setCellValue('T1','Electricity');
    
                    $event->sheet->mergeCells('W2:W3');
                    $event->sheet->setCellValue('W2','Previous');
    
                    $event->sheet->mergeCells('X2:X3');
                    $event->sheet->setCellValue('X2','Current');
    
                    $event->sheet->mergeCells('Y2:Y3');
                    $event->sheet->setCellValue('Y2','Consumption');
    
                    $event->sheet->setCellValue('D2','Rent');
                    $event->sheet->setCellValue('E2','Occupied');
                    $event->sheet->setCellValue('F2','Vacant');
                    $event->sheet->setCellValue('G2','Unsold');
                    $event->sheet->setCellValue('H2','Occupied');
                    $event->sheet->setCellValue('I2','Internet');
                    $event->sheet->setCellValue('J2','Cable TV');
                    $event->sheet->setCellValue('K2','Total Fee');
                    $event->sheet->setCellValue('L2','Unpaid Units');
                    $event->sheet->setCellValue('M2','Unpaid Fee');
                    $event->sheet->setCellValue('N2','Total Fee');
                    $event->sheet->setCellValue('O2','Unpaid Units');
                    $event->sheet->setCellValue('P2','Unpaid Fee');
                    $event->sheet->setCellValue('Q2','Total Fee');
                    $event->sheet->setCellValue('R2','Unpaid Units');
                    $event->sheet->setCellValue('S2','Unpaid Fee');
                    $event->sheet->setCellValue('T2','Total Fee');
                    $event->sheet->setCellValue('U2','Unpaid Units');
                    $event->sheet->setCellValue('V2','Unpaid Fee');
                },
                AfterSheet::class => function(AfterSheet $event) {
    
                    $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                    $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
                }
            ];
        }
    
        /**
        * @return Headings
        */
        public function headings(): array
        {
            return [
                'A',
                'B',
                'C',
                'D',
                'E',
                'F',
                'G',
                'H',
                'I',
                'J',
                'K',
                'L',
                'M',
                'N',
                'O',
                'P',
                'Q',
                'R',
                'S',
                'T',
                'U',
                'V',
                'W',
                'X',
                'Y',
            ];
        }
    
        /**
        * @return MappingWithHeadings
        */
        public function map($reportArr): array
        {   
            return [
                reportUnitAddressByUnitID($reportArr->id),
                intval(1),
                '',
                '',
                '',
                '',
                '',
                '',
                $reportArr->internet_subs,
                $reportArr->cabletv_subs,
                amountFormat2(850.00),
                '',
                amountFormat2(350.00),
                amountFormat2($reportArr->internet_total_fee),
                $reportArr->internet_unpaid_subs,
                amountFormat2($reportArr->internet_unpaid_fee),
                amountFormat2($reportArr->cabletv_total_fee),
                $reportArr->cabletv_unpaid_subs,
                amountFormat2($reportArr->cabletv_unpaid_fee),
                amountFormat2(176.22),
                '',
                amountFormat2(0),
                2102,
                3081,
                979,            
            ];
            
        }
    }
########################################### A102 Sheet End ###########################################

########################################### A103 Sheet Start ###########################################

class A103 implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'A','building'=>'A103','name'=>'A103'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'A103';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['building']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### A103 Sheet End ###########################################

########################################### A104 Sheet Start ###########################################

class A104 implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'A','building'=>'A104','name'=>'A104'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'A104';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['building']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### A104 Sheet End ###########################################

########################################### A105 Sheet Start ###########################################

class A105 implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'A','building'=>'A105','name'=>'A105'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'A105';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['building']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### A105 Sheet End ###########################################

########################################### A106 Sheet Start ###########################################

class A106 implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'A','building'=>'A106','name'=>'A106'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'A106';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['building']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### A106 Sheet End ###########################################

########################################### A107 Sheet Start ###########################################

class A107 implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'A','building'=>'A107','name'=>'A107'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'A107';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['building']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### A107 Sheet End ###########################################

########################################### A108 Sheet Start ###########################################

class A108 implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'A','building'=>'A108','name'=>'A108'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->where('buildings_location.name', $this->buildingArr['building'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'A108';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['building']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### A108 Sheet End ###########################################

########################################### East Shops Sheet Start ###########################################

class East_Shops implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'S','building'=>"'SM-A','SM-B','SM-C','SM-D'",'name'=>'East Shops'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->whereIn('buildings_location.name', ['SM-A','SM-B','SM-C','SM-D'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   //DB::enableQueryLog();
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->whereIn('buildings_location.name', ['SM-A','SM-B','SM-C','SM-D'])->orderByDesc('units_address.id')->get()->all();
        //$sql= DB::getQueryLog();
        //dd($sql);
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
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
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['name']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### East Shops Sheet End ###########################################

########################################### South Shops Sheet Start ###########################################

class South_Shops implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'S','building'=>"'RS-A','RS-B'",'name'=>'South Shops'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->whereIn('buildings_location.name', ['RS-A','RS-B'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->whereIn('buildings_location.name', ['RS-A','RS-B'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
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
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['name']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### South Shops Sheet End ###########################################

########################################### R1 Town Sheet Start ###########################################

class R1_Town implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'T','building'=>"R1-Town",'name'=>'R1-Town'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'R1-Town';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['name']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### R1 Town Sheet End ###########################################

########################################### R1 Villa Sheet Start ###########################################

class R1_Villa implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R1','type'=>'V','building'=>"R1-Villa",'name'=>'R1-Villa'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'R1-Villa';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['name']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### R1 Villa Sheet End ###########################################

########################################### R2 Villa Sheet Start ###########################################

class R2_Villa implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithPreCalculateFormulas, WithColumnFormatting, ShouldAutoSize, WithTitle
{
    protected $yearMonth;
    protected $buildingArr;
    protected $totalRows;    

    function __construct($yearMonth) {
            $this->yearMonth = $yearMonth;
            $this->buildingArr = ['project'=>'R2','type'=>'V','building'=>"R2-Villa",'name'=>'R2-Villa'];
            $this->totalRows = (DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.id')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->orderBy('units_address.id')->count('units_address.id'));
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $summaryReportArr= DB::table('units_address')->leftJoin('buildings_location','units_address.location_id', '=', 'buildings_location.id')->select('units_address.*')->where('buildings_location.location', $this->buildingArr['project'])->where('buildings_location.type', $this->buildingArr['type'])->orderByDesc('units_address.id')->get()->all();
        $startDate= monthlyStartEndDate($this->yearMonth)['startDate'];
        $endDate= monthlyStartEndDate($this->yearMonth)['endDate'];

        if(count($summaryReportArr)>0){
            foreach($summaryReportArr as $rkey=>$rval){
                $summaryReportArr[$rkey]->internet_subs= internetSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_total_fee= internetSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_subs= internetUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->internet_unpaid_fee= internetSubscriberUnpaidFee($rval->id,$endDate);
                $summaryReportArr[$rkey]->cabletv_subs= cableTVSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_total_fee= cableTVSubscriberMonthlyFee($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_subs= cableTVUnpaidSubscriberByAddress($rval->id,$startDate,$endDate);
                $summaryReportArr[$rkey]->cabletv_unpaid_fee= cableTVSubscriberUnpaidFee($rval->id,$endDate);
            }
        }
        return collect($summaryReportArr);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'R2-Villa';
    }

    public function styles($sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true],
                     //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'B3B820']],
                     'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            2    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],

            3    => ['font' => ['bold' => true],
                    //'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['rgb' => 'FFFF00']]
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],],
            
            'A1:Y'.($this->totalRows+3) => [
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [ 'allBorders' => [ 'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['argb' => '000000'], ],],],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_NUMBER,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => NumberFormat::FORMAT_NUMBER,
            'K' => NumberFormat::FORMAT_CURRENCY_USD,
            'L' => NumberFormat::FORMAT_NUMBER,
            'M' => NumberFormat::FORMAT_CURRENCY_USD,
            'N' => NumberFormat::FORMAT_CURRENCY_USD,
            'O' => NumberFormat::FORMAT_NUMBER,
            'P' => NumberFormat::FORMAT_CURRENCY_USD,
            'Q' => NumberFormat::FORMAT_CURRENCY_USD,
            'R' => NumberFormat::FORMAT_NUMBER,
            'S' => NumberFormat::FORMAT_CURRENCY_USD,
            'T' => NumberFormat::FORMAT_CURRENCY_USD,
            'U' => NumberFormat::FORMAT_NUMBER,
            'V' => NumberFormat::FORMAT_CURRENCY_USD,
            'W' => NumberFormat::FORMAT_NUMBER,
            'X' => NumberFormat::FORMAT_NUMBER,
            'Y' => NumberFormat::FORMAT_NUMBER,            
        ];
    }

    public function registerEvents(): array
    {
        return [

            BeforeSheet::class => function(BeforeSheet $event) {

                $event->sheet->mergeCells('A1:A3');
                $event->sheet->setCellValue('A1',$this->buildingArr['name']);

                $event->sheet->mergeCells('B1:B2');
                $event->sheet->setCellValue('B1','Total');

                $event->sheet->mergeCells('C1:C2');
                $event->sheet->setCellValue('C1','World City');

                $event->sheet->mergeCells('D1:G1');
                $event->sheet->setCellValue('D1','Sales');

                $event->sheet->setCellValue('H1','MO');

                $event->sheet->mergeCells('I1:J1');
                $event->sheet->setCellValue('I1','IDC');

                $event->sheet->mergeCells('K1:M1');
                $event->sheet->setCellValue('K1','Rental');

                $event->sheet->mergeCells('N1:P1');
                $event->sheet->setCellValue('N1','Internet');

                $event->sheet->mergeCells('Q1:S1');
                $event->sheet->setCellValue('Q1','Cable TV');

                $event->sheet->mergeCells('T1:Y1');
                $event->sheet->setCellValue('T1','Electricity');

                $event->sheet->mergeCells('W2:W3');
                $event->sheet->setCellValue('W2','Previous');

                $event->sheet->mergeCells('X2:X3');
                $event->sheet->setCellValue('X2','Current');

                $event->sheet->mergeCells('Y2:Y3');
                $event->sheet->setCellValue('Y2','Consumption');

                $event->sheet->setCellValue('D2','Rent');
                $event->sheet->setCellValue('E2','Occupied');
                $event->sheet->setCellValue('F2','Vacant');
                $event->sheet->setCellValue('G2','Unsold');
                $event->sheet->setCellValue('H2','Occupied');
                $event->sheet->setCellValue('I2','Internet');
                $event->sheet->setCellValue('J2','Cable TV');
                $event->sheet->setCellValue('K2','Total Fee');
                $event->sheet->setCellValue('L2','Unpaid Units');
                $event->sheet->setCellValue('M2','Unpaid Fee');
                $event->sheet->setCellValue('N2','Total Fee');
                $event->sheet->setCellValue('O2','Unpaid Units');
                $event->sheet->setCellValue('P2','Unpaid Fee');
                $event->sheet->setCellValue('Q2','Total Fee');
                $event->sheet->setCellValue('R2','Unpaid Units');
                $event->sheet->setCellValue('S2','Unpaid Fee');
                $event->sheet->setCellValue('T2','Total Fee');
                $event->sheet->setCellValue('U2','Unpaid Units');
                $event->sheet->setCellValue('V2','Unpaid Fee');
            },
            AfterSheet::class => function(AfterSheet $event) {

                $event->sheet->setCellValue('B3', '=SUM(B4:B'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('C3', '=SUM(C4:C'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('D3', '=SUM(D4:D'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('E3', '=SUM(E4:E'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('F3', '=SUM(F4:F'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('G3', '=SUM(G4:G'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('H3', '=SUM(H4:H'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('I3', '=SUM(I4:I'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('J3', '=SUM(J4:J'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('K3', '=SUM(K4:K'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('L3', '=SUM(L4:L'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('M3', '=SUM(M4:M'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('N3', '=SUM(N4:N'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('O3', '=SUM(O4:O'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('P3', '=SUM(P4:P'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('Q3', '=SUM(Q4:Q'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('R3', '=SUM(R4:R'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('S3', '=SUM(S4:S'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('T3', '=SUM(T4:T'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('U3', '=SUM(U4:U'.($event->sheet->getHighestRow()).')');
                $event->sheet->setCellValue('V3', '=SUM(V4:V'.($event->sheet->getHighestRow()).')');
            }
        ];
    }

    /**
    * @return Headings
    */
    public function headings(): array
    {
        return [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
        ];
    }

    /**
    * @return MappingWithHeadings
    */
    public function map($reportArr): array
    {   
        return [
            reportUnitAddressByUnitID($reportArr->id),
            intval(1),
            '',
            '',
            '',
            '',
            '',
            '',
            $reportArr->internet_subs,
            $reportArr->cabletv_subs,
            amountFormat2(850.00),
            '',
            amountFormat2(350.00),
            amountFormat2($reportArr->internet_total_fee),
            $reportArr->internet_unpaid_subs,
            amountFormat2($reportArr->internet_unpaid_fee),
            amountFormat2($reportArr->cabletv_total_fee),
            $reportArr->cabletv_unpaid_subs,
            amountFormat2($reportArr->cabletv_unpaid_fee),
            amountFormat2(176.22),
            '',
            amountFormat2(0),
            2102,
            3081,
            979,            
        ];
        
    }
}
########################################### R2 Villa Sheet End ###########################################