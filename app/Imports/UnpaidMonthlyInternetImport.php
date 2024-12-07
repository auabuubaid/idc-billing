<?php

namespace App\Imports;

use App\Models\InternetHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UnpaidMonthlyInternetImport implements ToCollection, WithHeadingRow
{
    public function headingRow():int
    {
        return 2;
    }
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {   
        ini_set('max_execution_time', -1);
        ini_set('memory_limit', '8096M');
        ini_set('post_max_size', '5000M');
        ini_set('upload_max_filesize', '5000M');
        

        foreach($rows as $key=>$row)
        {
            Validator::make($row->toArray(), [
                'id'  => 'required|numeric',
                'payment_mode' => 'required|in:CA,BA,CH,OT',
                'payment_description' => in_array($row['payment_mode'],['BA','CH','OT'])?'required|string':'',
                'paid' => 'required|in:Y,N',
                'paid_date' => 'required|date|date_format:d-M-Y',
                'remark'=> !empty($row['remark'])?'required|string':''

            ], $messages = [
                    'required' => $row['unit_no'].' :attribute is required.',
                    'in' => $row['unit_no'].' :attribute must be one of the following types: :values.',
                    'date' => $row['unit_no'].' :attribute must be date.',
                    'date_format' => $row['unit_no'].' :attribute must be in DD-MMM-YYYY format only.',
                    'numeric' => $row['unit_no'].' :attribute must be numeric value.',
                    'string' => $row['unit_no'].' :attribute  must be a string.',
                ])->validate();
        }
        
        foreach ($rows as $row) 
        {   
            $ihistory = InternetHistory::firstWhere('id','=',$row['id']);
           
            $ihistory->payment_mode          = $row['payment_mode'];
            $ihistory->payment_description   = $row['payment_description'];
            $ihistory->paid                  = $row['paid'];
            $ihistory->paid_date             = dataBaseFormat($row['paid_date']);
            $ihistory->remark                = $row['remark'];
            $ihistory->save();
        }
    }
}
