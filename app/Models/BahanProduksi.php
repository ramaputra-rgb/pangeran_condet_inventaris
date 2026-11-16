<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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

    /**
     * Normalisasi nama bahan ke lowercase dengan spasi tunggal untuk penyimpanan unik di database.
     */
    public static function normalizeName(?string $value): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', trim((string) $value));

        return mb_strtolower((string) $collapsed, 'UTF-8');
    }

    /**
     * Format nama bahan dari lowercase menjadi Pascal/Title Case untuk tampilan web.
     */
    public static function formatDisplayName(?string $value): string
    {
        $normalized = self::normalizeName($value);
        if ($normalized === '') {
            return '';
        }

        return (string) preg_replace_callback(
            '/(?:^|[\s\/\-])(\p{L})/u',
            fn (array $matches): string => mb_strtoupper($matches[0], 'UTF-8'),
            $normalized
        );
    }

    protected function namaBahan(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value): string => self::formatDisplayName($value),
            set: fn (?string $value): string => self::normalizeName($value),
        );
    }

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
