<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function balance()
    {
        return $this->hasOne(UserBalance::class, 'id_user');
    }

    public function userTickets()
    {
        return $this->hasMany(EstatePoolUserTicket::class, 'id_user');
    }
}
