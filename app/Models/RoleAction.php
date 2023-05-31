<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleAction extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table= 'public.roleactions';
    protected $fillable = [
        'role', 'actionlist', 'receiverlist','email','sms','push','msgcontent',
    ];
}
