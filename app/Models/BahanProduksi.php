<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanProduksi extends Model
{
    use HasFactory;

    protected $table = 'bahan_produksi';
    protected $primaryKey = 'id_bahan';

    protected $fillable = [
        'nama_bahan',
        'tipe',
        'ukuran',
        'qty_stok',
    ];

    public function transaksiBeli()
    {
        return $this->hasMany(TransaksiBeliBahan::class, 'id_bahan', 'id_bahan');
    }

    public function logProduksi()
    {
        return $this->belongsToMany(LogProduksi::class, 'log_produksi_bahan', 'id_bahan', 'no_batch')
                    ->withPivot('qty_dipakai')
                    ->withTimestamps();
    }
}