<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMaterial extends Model
{
    protected $guarded = ['id'];

    public function purchases()
    {
        return $this->hasMany(MaterialPurchase::class);
    }

    public function boms()
    {
        return $this->hasMany(Bom::class);
    }
}
