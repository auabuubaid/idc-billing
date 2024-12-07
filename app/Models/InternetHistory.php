<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternetHistory extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'internet_history';

    protected $dates = ['created_at', 'updated_at', 'entry_date', 'month_end_date', 'suspension_start_date', 'suspension_end_date'];
}
