<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukJadi extends Model
{
    use HasFactory;

    protected $table = 'produk_jadi';
    protected $primaryKey = 'id_produk_jadi';

    protected $fillable = [
        'nama_produk',
        'varian',
        'netto',
        'harga_jual',
        'stok',
    ];

    public function detailProduksi()
    {
        return $this->hasMany(DetailProduksi::class, 'id_produk_jadi', 'id_produk_jadi');
    }

    public function detailAlokasiPo()
    {
        return $this->hasMany(DetailAlokasiPo::class, 'id_produk_jadi', 'id_produk_jadi');
    }
}