<?php

namespace App\Imports;

use App\Models\UnitAddress;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class UnitsAddressImport implements ToModel,WithStartRow,WithBatchInserts
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

        UnitAddress::create([
                'location_id' => $row[0],
                'unit_number' => $row[1], 
                'sort_order'  => $row[2],
                'status'      => $row[3],
            ]);
    }

    public function batchSize():int
    {
        return 100;
    }
}
