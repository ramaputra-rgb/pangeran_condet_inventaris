<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogProduksi extends Model
{
    use HasFactory;

    protected $table = 'log_produksi';
    protected $primaryKey = 'no_batch';

    protected $fillable = [
        'tgl_produksi',
        'rentang_tgl_exp',
        'waktu_produksi',
        'jml_grade_A',
        'jml_grade_B',
        'no_po_produk',
        'ket',
    ];

    public function poProduk()
    {
        return $this->belongsTo(PoProduk::class, 'no_po_produk', 'no_po_produk');
    }

    public function bahanProduksi()
    {
        return $this->belongsToMany(BahanProduksi::class, 'log_produksi_bahan', 'no_batch', 'id_bahan')
                    ->withPivot('qty_dipakai')
                    ->withTimestamps();
    }

    public function detailProduksi()
    {
        return $this->hasMany(DetailProduksi::class, 'no_batch', 'no_batch');
    }
}