<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    protected $guarded = [];

    public function transaction(){
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function product(){
        return $this->belongsTo(Product::class, 'product_id');
    }
}
