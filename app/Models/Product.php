<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function owner(){
        $this->belongsTo(User::class, 'owner_id');
    }

    public function transactionDetails(){
        $this->hasMany(TransactionDetail::class, 'product_id');
    }
}
