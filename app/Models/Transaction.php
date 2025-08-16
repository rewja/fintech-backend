<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = [];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function toUser(){
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function details(){
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }
}
