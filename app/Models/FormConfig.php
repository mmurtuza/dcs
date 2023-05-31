<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormConfig extends Model
{
    protected $table= 'dcs.form_configs';

    protected $fillable = [
        'templateID', 'formID','dataType','columnType','displayOrder','status','updated_at','groupNo',
    ];

    protected $casts = [
        'lebel' => 'array',
        'groupLabel'=>'array',
        'captions' => 'array',
        'values'=>'array'
    ];
}
