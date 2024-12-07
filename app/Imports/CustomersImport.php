<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class CustomersImport implements ToModel,WithStartRow,WithBatchInserts
{
    public function startRow():int
    {
        return 2;
    }
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        ini_set('max_execution_time', 100000);
        ini_set('memory_limit', '8096M');
        ini_set('post_max_size', '5000M');
        ini_set('upload_max_filesize', '5000M');

        Customer::create([
                'cabletv_id'     => $row[0],    
                'address_id'     => $row[1],
                'type'           => $row[2], 
                'is_living'      => $row[3],
                'name'           => $row[4],
                'email'          => $row[5], 
                'mobile'         => $row[6],
                'sex'            => $row[7],
                'shop_name'      => $row[8], 
                'shop_email'     => $row[9],
                'shop_mobile'    => $row[10],
                'vat_no'         => $row[11], 
                'country'        => $row[12],
                'created_by'     => $row[13],
            ]);
    }
    
    public function batchSize():int
    {
        return 100;
    }
}