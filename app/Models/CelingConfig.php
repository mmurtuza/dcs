<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CelingConfig extends Model
{
    protected $fillable = [
        'projectcode', 'approver','growth_rate','limit_form','limit_to','repeat_limit_form','repeat_limit_to','createdby',
    ];
}
