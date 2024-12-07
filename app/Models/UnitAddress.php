<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitAddress extends Model
{
    use HasFactory;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'units_address';

    /**
    * The fillable fields.
    *
    * @var string
    */
    protected $fillable = ['location_id','unit_number','status','sort_order','created_at','updated_at'];
}
