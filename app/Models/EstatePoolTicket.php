<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstatePoolTicket extends Model
{
    protected $table = 'estatepool_tickets';

     protected $fillable = [
        'count',
        'sum',
        'status',
    ];

    public function pool()
    {
        return $this->hasMany(EstatePoolUserTicket::class, 'id_ticket');
    }
}
