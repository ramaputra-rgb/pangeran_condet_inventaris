<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailProduksi extends Model
{
    use HasFactory;

    protected $table = 'detail_produksi';

    protected $fillable = [
        'id_produk_jadi',
        'no_batch',
        'jml_produk_jadi',
    ];

    public function produkJadi()
    {
        return $this->belongsTo(ProdukJadi::class, 'id_produk_jadi', 'id_produk_jadi');
    }

    public function logProduksi()
    {
        return $this->belongsTo(LogProduksi::class, 'no_batch', 'no_batch');
    }
}