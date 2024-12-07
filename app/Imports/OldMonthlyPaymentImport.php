<?php

namespace App\Imports;

use App\Models\OldMonthlyPayment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class OldMonthlyPaymentImport implements ToCollection,WithHeadingRow,WithBatchInserts
{
    public function headingRow():int
    {
        return 1;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Collection|null
    */
    public function collection(Collection $rows)
    {
        ini_set('max_execution_time', 100000);
        ini_set('memory_limit', '8096M');
        ini_set('post_max_size', '5000M');
        ini_set('upload_max_filesize', '5000M');
        
        OldMonthlyPayment::create([
                'customer_no'    => $row['cust_id'],
                'customer_name'  => $row['customer_name'], 
                'address'        => $row['address'],
                'invoice_no'     => $row['invoice_no'],
                'invoice_date'   => $row['invoice_date'], 
                'due_date'       => $row['due_date'],
                'plan_name'      => $row['plan_name'],
                'speed'          => $row['speed'],
                'monthly_fee'    => $row['total'], 
                'balance'        => $row['fee'],
                'vat_amount'     => $row['vat'],
                'total_amount'   => $row['total'],
            ]);
    }

    public function batchSize():int
    {
        return 100;
    }
}