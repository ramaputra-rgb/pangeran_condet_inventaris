<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProdukJadi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProdukJadiApiController extends Controller
{
    /**
     * Verifikasi API Key dari Header request
     */
    protected function verifyApiKey(Request $request): bool
    {
        $expectedKey = env('INVENTARIS_API_KEY', 'pangeran_inventaris_secret_key_2026');
        
        $authHeader = $request->header('X-Api-Key') 
                   ?: $request->header('X-API-KEY') 
                   ?: $request->bearerToken();

        return !empty($authHeader) && hash_equals($expectedKey, (string) $authHeader);
    }

    /**
     * Endpoint Mengambil Semua Produk Jadi untuk Katalog E-Commerce
     * GET /api/v1/produk-jadi
     */
    public function index(Request $request)
    {
        // 1. Verifikasi Keamanan API Key
        if (!$this->verifyApiKey($request)) {
            Log::warning('[ProdukJadiApiController@index] Akses API ditolak: API Key tidak valid / hilang.', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: API Key inventaris tidak valid.',
            ], 401);
        }

        try {
            $query = ProdukJadi::query();

            if ($request->has('q')) {
                $q = trim($request->input('q'));
                $query->where('nama_produk', 'like', "%{$q}%")
                      ->orWhere('varian', 'like', "%{$q}%");
            }

            $products = $query->orderBy('id_produk_jadi', 'asc')->get();

            $formatted = $products->map(function ($item) {
                return [
                    'id_produk_jadi'   => (int) $item->id_produk_jadi,
                    'nama_produk'      => $item->nama_produk,
                    'varian'           => $item->varian,
                    'netto'            => $item->netto,
                    'harga_jual'       => (float) $item->harga_jual,
                    'stok'             => (int) $item->stok,
                    'stok_terkunci_po' => (int) $item->stok_terkunci_po,
                    'stok_atp'         => (int) $item->stok_atp,
                    'created_at'       => $item->created_at?->toIso8601String(),
                    'updated_at'       => $item->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Data master produk jadi berhasil diambil.',
                'count'   => $formatted->count(),
                'data'    => $formatted,
            ], 200);

        } catch (\Throwable $e) {
            Log::error('[ProdukJadiApiController@index] Gagal mengambil data produk jadi: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data produk jadi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint Mengambil Detail Satu Produk Jadi
     * GET /api/v1/produk-jadi/{id}
     */
    public function show(Request $request, $id)
    {
        if (!$this->verifyApiKey($request)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: API Key inventaris tidak valid.',
            ], 401);
        }

        $item = ProdukJadi::where('id_produk_jadi', $id)->first();

        if (!$item) {
            return response()->json([
                'status'  => 'not_found',
                'message' => "Produk jadi dengan ID {$id} tidak ditemukan di inventaris.",
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail produk jadi berhasil ditemukan.',
            'data'    => [
                'id_produk_jadi'   => (int) $item->id_produk_jadi,
                'nama_produk'      => $item->nama_produk,
                'varian'           => $item->varian,
                'netto'            => $item->netto,
                'harga_jual'       => (float) $item->harga_jual,
                'stok'             => (int) $item->stok,
                'stok_terkunci_po' => (int) $item->stok_terkunci_po,
                'stok_atp'         => (int) $item->stok_atp,
                'created_at'       => $item->created_at?->toIso8601String(),
                'updated_at'       => $item->updated_at?->toIso8601String(),
            ],
        ], 200);
    }
}
