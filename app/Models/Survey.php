<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    // use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'dcs.surveys';
    protected $fillable = [
        'surveyid', 'entollmentid', 'name', 'mainidtypeid', 'idno', 'phone', 'status', 'label', 'targetdate', 'refferdbyid',
    ];

    protected $casts = [
        'dynamicFieldValue' => 'array'
    ];
}
