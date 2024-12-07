<?php

namespace App\Imports;

use App\Models\BuildingLocation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class BuildingsLocationImport implements ToModel,WithStartRow,WithBatchInserts
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

        BuildingLocation::create([
                'location'    => $row[0],
                'type'        => $row[1], 
                'name'        => $row[2],
                'sort_order'  => $row[3],
                'status'      => $row[4],
            ]);
    }

    public function batchSize():int
    {
        return 100;
    }
}
