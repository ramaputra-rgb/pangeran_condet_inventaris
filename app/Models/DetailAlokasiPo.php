<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailAlokasiPo extends Model
{
    use HasFactory;

    protected $table = 'detail_alokasi_po';

    protected $fillable = [
        'id_produk_jadi',
        'no_po_produk',
        'jml_alokasi',
    ];

    public function produkJadi()
    {
        return $this->belongsTo(ProdukJadi::class, 'id_produk_jadi', 'id_produk_jadi');
    }

    public function poProduk()
    {
        return $this->belongsTo(PoProduk::class, 'no_po_produk', 'no_po_produk');
    }
}