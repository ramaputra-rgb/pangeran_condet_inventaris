<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogCetakLaporan extends Model
{
    use HasFactory;

    protected $table = 'log_cetak_laporan';
    protected $primaryKey = 'id_log_cetak';

    protected $fillable = [
        'jenis_laporan',
        'kriteria_filter',
        'tgl_cetak',
        'status_cetak',
    ];
}