<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function inventory()
    {
        return $this->hasOne(StockInventory::class);
    }

    public function boms()
    {
        return $this->hasMany(Bom::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
