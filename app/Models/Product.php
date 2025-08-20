<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function owner(){
       return $this->belongsTo(User::class, 'owner_id');
    }

    public function details(){
        return $this->hasMany(TransactionDetail::class, 'product_id');
    }
}
