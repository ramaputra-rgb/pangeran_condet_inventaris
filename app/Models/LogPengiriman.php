<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogPengiriman extends Model
{
    use HasFactory;

    protected $table = 'log_pengiriman';
    protected $primaryKey = 'id_log_pengiriman';

    protected $fillable = [
        'Tanggal',
        'no_po_produk',
        'bukti_serah_terima',
        'status_pengiriman',
    ];

    public function poProduk()
    {
        return $this->belongsTo(PoProduk::class, 'no_po_produk', 'no_po_produk');
    }
}