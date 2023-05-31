<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanProposal extends Model
{
    protected $table= 'public.loan_proposals';
    protected $guarded =[];

    protected $casts = [
        'DynamicFieldValue' => 'array'
    ];

}
