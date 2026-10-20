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

    // TAMBAHKAN INI AGAR ACCESSOR TERBACA OTOMATIS
    protected $appends = [
        'stok_terkunci_po',
        'stok_atp',
    ];

    public function detailProduksi()
    {
        return $this->hasMany(DetailProduksi::class, 'id_produk_jadi', 'id_produk_jadi');
    }

    public function detailAlokasiPo()
    {
        return $this->hasMany(DetailAlokasiPo::class, 'id_produk_jadi', 'id_produk_jadi');
    }

    // Accessor: Kuncian Alokasi PO Aktif
    public function getStokTerkunciPoAttribute()
    {
        return (int) $this->detailAlokasiPo()
            ->whereHas('poProduk', function ($query) {
                $query->whereIn('status_po', ['PENDING', 'PROSES', 'STOK_DIPOTONG', 'DIKIRIM']);
            })
            ->sum('jml_alokasi');
    }

    // Accessor: Stok Siap Jual / Available to Promise (ATP)
    public function getStokAtpAttribute()
    {
        return max(0, (int) $this->stok - $this->stok_terkunci_po);
    }
}