<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstatePool extends Model
{
    protected $table = 'estatepool';

    protected $fillable = [
        'date_start',
        'date_close',
        'sum',
        'sum_goal',
        'status',
    ];

    protected $casts = [
        'date_start' => 'datetime',
        'date_close' => 'datetime',
    ];
    public function gifts()
    {
        return $this->hasMany(EstatePoolGift::class, 'id_pool');
    }

    public function tickets()
    {
        return $this->hasMany(EstatePoolTicket::class, 'id_pool');
    }

    public function userTickets()
    {
        return $this->hasMany(EstatePoolUserTicket::class, 'id_pool');
    }
}
