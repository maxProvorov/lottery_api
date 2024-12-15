<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstatePoolGift extends Model
{
    protected $table = 'estatepool_gifts';

    protected $fillable = [
        'id_pool',
        'name',
        'sum',
        'general',
        'date_close',
        'id_winner',
    ];

    public function pool()
    {
        return $this->belongsTo(EstatePool::class, 'id_pool');
    }
}
