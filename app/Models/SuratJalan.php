<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    use HasFactory;

    protected $table = 'surat_jalan';
    protected $primaryKey = 'no_ref';

    protected $fillable = [
        'tgl_terbit',
        'nama_penerima',
        'nama_kurir',
        'ket',
    ];

    public function poProduk()
    {
        return $this->hasMany(PoProduk::class, 'no_ref', 'no_ref');
    }
}