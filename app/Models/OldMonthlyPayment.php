<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldMonthlyPayment extends Model
{
    use HasFactory;
    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'old_monthly_payament';

    /**
    * The fillable fields.
    *
    * @var string
    */
    protected $fillable = ['customer_no','customer_name','address','invoice_no','invoice_date','due_date','plan_name','speed','from_to_date','monthly_fee','balance','vat_amount','vat_no','others_fee','discount','total_amount','paid_date','remark','created_at','updated_at'];

}
