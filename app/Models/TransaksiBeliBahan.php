<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiBeliBahan extends Model
{
    use HasFactory;

    protected $table = 'transaksi_beli_bahan';
    protected $primaryKey = 'no_transaksi_beli';

    protected $fillable = [
        'nama_supplier',
        'total_qty',
        'total_harga',
        'total_ongkir',
        'id_bahan',
    ];

    public function bahanProduksi()
    {
        return $this->belongsTo(BahanProduksi::class, 'id_bahan', 'id_bahan');
    }
}