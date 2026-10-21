<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoProduk extends Model
{
    use HasFactory;

    protected $table = 'po_produk';
    protected $primaryKey = 'no_po_produk';

    protected $fillable = [
        'nama_pelanggan',
        'nama_penerima',
        'tipe_jual',
        'jumlah_po',
        'tgl_po',
        'tgl_jatuh_tempo_po',
        'status_po',
        'alamat_pelanggan',
        'no_ref',
        'nomor_pesanan_ecommerce',
        'sumber_transaksi',
        'catatan',
    ];

    public function suratJalan()
    {
        return $this->belongsTo(SuratJalan::class, 'no_ref', 'no_ref');
    }

    public function logProduksi()
    {
        return $this->hasMany(LogProduksi::class, 'no_po_produk', 'no_po_produk');
    }

    public function detailAlokasi()
    {
        return $this->hasMany(DetailAlokasiPo::class, 'no_po_produk', 'no_po_produk');
    }

    public function logPengiriman()
    {
        return $this->hasMany(LogPengiriman::class, 'no_po_produk', 'no_po_produk');
    }
}