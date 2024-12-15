<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBalance extends Model
{
    protected $table = 'users_balances';

    protected $fillable = [
        'id_user',
        'sum',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
