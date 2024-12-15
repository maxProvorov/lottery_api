<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $table = 'balances';

    protected $fillable = [
        'title',
        'paysystem',
        'currency',
        'status',
        'type',
    ];
}
