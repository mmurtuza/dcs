<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table= 'dcs.projects';
    protected $fillable = [
        'projectcode', 'projecttitle', 'isactive','entrytime','templateID',
    ];
}
