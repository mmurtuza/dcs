<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RcaTable extends Model
{
    protected $table= 'dcs.rca';
    protected $guarded = [];

    protected $casts = [
        'DynamicFieldValue' => 'array'
    ];

}
