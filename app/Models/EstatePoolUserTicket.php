<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstatePoolUserTicket extends Model
{
    protected $table = 'estatepool_usertickets';

    protected $fillable = [
        'ticket',
        'id_ticket',
        'id_user',
        'id_pool',
        'id_gift',
        'win',
    ];

    public function pool()
    {
        return $this->belongsTo(EstatePool::class, 'id_pool', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
}
