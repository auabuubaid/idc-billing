<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer  extends Model
{
    use HasFactory;
    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'customers';

    /**
    * The fillable fields.
    *
    * @var string
    */
    protected $fillable = ['internet_id', 'cabletv_id','address_id','type','is_living','name','email','mobile','sex','shop_name','shop_email','shop_mobile','vat_no','country','created_by','updated_by','created_at','updated_at'];

    /**
     * Mutator for Name column.
     * when "name" will save, it will uppercase first letter of name
     * @var string
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ucfirst($value);
    }

}
