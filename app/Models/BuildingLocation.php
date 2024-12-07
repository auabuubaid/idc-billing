<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuildingLocation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'buildings_location';

    /**
    * The fillable fields.
    *
    * @var string
    */
    protected $fillable = ['name','location','type','status','sort_order','created_at','updated_at'];
}
