<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialPurchase extends Model
{
    protected $guarded = ['id'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
