<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PoProduk;
use App\Models\DetailAlokasiPo;
use App\Models\ProdukJadi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PoApiController extends Controller
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
     * Endpoint Sinkronisasi Pesanan E-Commerce menjadi PO Inventaris
     * POST /api/v1/po/sync-order
     */
    public function syncOrder(Request $request)
    {
        // 1. Verifikasi Keamanan API Key
        if (!$this->verifyApiKey($request)) {
            Log::warning('[PoApiController@syncOrder] Akses API ditolak: API Key tidak valid / hilang.', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: API Key inventaris tidak valid.',
            ], 401);
        }

        // 2. Validasi Payload Request
        $validator = Validator::make($request->all(), [
            'nomor_pesanan'     => 'required|string|max:100',
            'nama_pelanggan'    => 'required|string|max:255',
            'nama_penerima'     => 'nullable|string|max:255',
            'alamat_pelanggan'  => 'required|string',
            'tipe_jual'         => 'nullable|string|max:50',
            'tgl_po'            => 'nullable|date',
            'tgl_jatuh_tempo_po'=> 'nullable|date',
            'catatan'           => 'nullable|string',
            'items'             => 'required|array|min:1',
            'items.*.nama_produk' => 'required|string',
            'items.*.qty'         => 'required|integer|min:1',
            'items.*.id_produk_jadi' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $nomorPesanan = trim($request->input('nomor_pesanan'));

        // 3. Idempotency Check: Jika PO untuk pesanan ini sudah ada, kembalikan data yang sudah ada
        $existingPo = PoProduk::with('detailAlokasi.produkJadi')
            ->where('nomor_pesanan_ecommerce', $nomorPesanan)
            ->first();

        if ($existingPo) {
            Log::info("[PoApiController@syncOrder] Pesanan {$nomorPesanan} sudah ada di inventaris (PO #{$existingPo->no_po_produk}).", [
                'nomor_pesanan' => $nomorPesanan,
                'no_po_produk'  => $existingPo->no_po_produk,
            ]);

            return response()->json([
                'status'       => 'already_exists',
                'message'      => 'Pesanan ini sudah pernah tercatat sebagai PO inventaris sebelumnya.',
                'data'         => [
                    'no_po_produk'            => $existingPo->no_po_produk,
                    'kode_po'                 => 'PO-00' . $existingPo->no_po_produk,
                    'nomor_pesanan_ecommerce' => $existingPo->nomor_pesanan_ecommerce,
                    'status_po'               => $existingPo->status_po,
                    'jumlah_po'               => $existingPo->jumlah_po,
                    'created_at'              => $existingPo->created_at?->toIso8601String(),
                ],
            ], 200);
        }

        // 4. Proses Pembuatan PO & Detail Alokasi
        try {
            $po = DB::transaction(function () use ($request, $nomorPesanan) {
                $items = $request->input('items', []);
                $totalQty = 0;
                $alokasiList = [];

                foreach ($items as $item) {
                    $qty = (int) ($item['qty'] ?? 1);
                    $totalQty += $qty;

                    // Resolusi Produk Jadi di Inventaris
                    $idProdukJadi = $this->resolveProdukJadiId($item);

                    $alokasiList[] = [
                        'id_produk_jadi' => $idProdukJadi,
                        'qty'            => $qty,
                    ];
                }

                $tglPo = $request->input('tgl_po', now()->toDateString());
                $tglTempo = $request->input('tgl_jatuh_tempo_po', now()->addDays(3)->toDateString());

                // Buat Master Record PO
                $newPo = PoProduk::create([
                    'nama_pelanggan'          => $request->input('nama_pelanggan'),
                    'nama_penerima'           => $request->input('nama_penerima') ?: $request->input('nama_pelanggan'),
                    'tipe_jual'               => $request->input('tipe_jual', 'E-Commerce Online'),
                    'jumlah_po'               => $totalQty,
                    'tgl_po'                  => $tglPo,
                    'tgl_jatuh_tempo_po'      => $tglTempo,
                    'status_po'               => 'PENDING',
                    'alamat_pelanggan'        => $request->input('alamat_pelanggan'),
                    'nomor_pesanan_ecommerce' => $nomorPesanan,
                    'sumber_transaksi'        => 'ONLINE_ECOMMERCE',
                    'catatan'                 => $request->input('catatan', 'Pesanan masuk otomatis dari Web E-Commerce Ikan Pangeran'),
                    'no_ref'                  => null,
                ]);

                // Buat Detail Alokasi PO
                foreach ($alokasiList as $alokasi) {
                    DetailAlokasiPo::create([
                        'no_po_produk'   => $newPo->no_po_produk,
                        'id_produk_jadi' => $alokasi['id_produk_jadi'],
                        'jml_alokasi'    => $alokasi['qty'],
                    ]);
                }

                return $newPo;
            });

            Log::info("[PoApiController@syncOrder] Berhasil membuat PO #{$po->no_po_produk} untuk pesanan e-commerce {$nomorPesanan}", [
                'no_po_produk'  => $po->no_po_produk,
                'nomor_pesanan' => $nomorPesanan,
                'total_qty'     => $po->jumlah_po,
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'PO pesanan e-commerce berhasil dicatat dalam inventaris.',
                'data'    => [
                    'no_po_produk'            => $po->no_po_produk,
                    'kode_po'                 => 'PO-00' . $po->no_po_produk,
                    'nomor_pesanan_ecommerce' => $po->nomor_pesanan_ecommerce,
                    'nama_pelanggan'          => $po->nama_pelanggan,
                    'status_po'               => $po->status_po,
                    'jumlah_po'               => $po->jumlah_po,
                    'tgl_po'                  => $po->tgl_po,
                    'tgl_jatuh_tempo_po'      => $po->tgl_jatuh_tempo_po,
                ],
            ], 201);

        } catch (\Throwable $e) {
            Log::error("[PoApiController@syncOrder] Gagal menyimpan PO pesanan {$nomorPesanan}: " . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses PO di sistem inventaris: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint Cek Status PO berdasarkan Nomor Pesanan E-Commerce
     * GET /api/v1/po/check/{orderNumber}
     */
    public function check(Request $request, string $orderNumber)
    {
        if (!$this->verifyApiKey($request)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: API Key inventaris tidak valid.',
            ], 401);
        }

        $po = PoProduk::with(['detailAlokasi.produkJadi', 'suratJalan'])
            ->where('nomor_pesanan_ecommerce', $orderNumber)
            ->first();

        if (!$po) {
            return response()->json([
                'status'  => 'not_found',
                'message' => "PO untuk pesanan {$orderNumber} belum terdaftar di inventaris.",
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'no_po_produk'            => $po->no_po_produk,
                'kode_po'                 => 'PO-00' . $po->no_po_produk,
                'nomor_pesanan_ecommerce' => $po->nomor_pesanan_ecommerce,
                'nama_pelanggan'          => $po->nama_pelanggan,
                'status_po'               => $po->status_po,
                'jumlah_po'               => $po->jumlah_po,
                'tgl_po'                  => $po->tgl_po,
                'tgl_jatuh_tempo_po'      => $po->tgl_jatuh_tempo_po,
                'items'                   => $po->detailAlokasi->map(function ($det) {
                    return [
                        'id_produk_jadi' => $det->id_produk_jadi,
                        'nama_produk'    => $det->produkJadi?->nama_produk,
                        'varian'         => $det->produkJadi?->varian,
                        'jml_alokasi'    => $det->jml_alokasi,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Cerdas mencocokkan item e-commerce ke id_produk_jadi inventaris
     */
    protected function resolveProdukJadiId(array $item): int
    {
        // 1. Jika caller mengirimkan id_produk_jadi langsung dan valid
        if (!empty($item['id_produk_jadi'])) {
            $exist = ProdukJadi::where('id_produk_jadi', $item['id_produk_jadi'])->exists();
            if ($exist) {
                return (int) $item['id_produk_jadi'];
            }
        }

        $nama = trim($item['nama_produk'] ?? '');
        if (empty($nama)) {
            return $this->getFirstProdukJadiId();
        }

        // 2. Cocokkan Nama Lengkap Persis
        $match = ProdukJadi::where('nama_produk', $nama)->first();
        if ($match) {
            return $match->id_produk_jadi;
        }

        // 3. Cocokkan Varian Persis
        $matchVarian = ProdukJadi::where('varian', $nama)->first();
        if ($matchVarian) {
            return $matchVarian->id_produk_jadi;
        }

        // 4. Pencarian Substring / LIKE
        $cleanName = preg_replace('/\s*\(.*?\)/', '', $nama);
        $likeMatch = ProdukJadi::where('nama_produk', 'LIKE', '%' . $cleanName . '%')
            ->orWhere('varian', 'LIKE', '%' . $cleanName . '%')
            ->first();

        if ($likeMatch) {
            return $likeMatch->id_produk_jadi;
        }

        // 5. Pencocokan berbasis kata kunci rasa / jenis (Rengginang Pedas, Ori, Bawang, Udang, dll)
        $keywords = [
            'pedas'    => ['pedas', 'spicy', 'balado'],
            'ori'      => ['ori', 'original', 'gurih'],
            'udang'    => ['udang', 'shrimp'],
            'bawang'   => ['bawang', 'garlic'],
            'cumi'     => ['cumi', 'squid'],
            'ikan'     => ['ikan', 'fish', 'cakalang'],
            'emping'   => ['emping', 'singkong'],
            'remahan'  => ['remahan', 'grade b'],
        ];

        $lowerName = strtolower($nama);
        foreach ($keywords as $category => $synonyms) {
            foreach ($synonyms as $syn) {
                if (str_contains($lowerName, $syn)) {
                    $prod = ProdukJadi::where('nama_produk', 'LIKE', '%' . $category . '%')
                        ->orWhere('varian', 'LIKE', '%' . $category . '%')
                        ->first();
                    if ($prod) {
                        return $prod->id_produk_jadi;
                    }
                }
            }
        }

        // Fallback terakhir: ambil produk pertama yang ada agar PO tidak gagal
        return $this->getFirstProdukJadiId();
    }

    /**
     * Fallback ID jika tidak ada yang cocok sama sekali
     */
    protected function getFirstProdukJadiId(): int
    {
        $first = ProdukJadi::first();
        return $first ? $first->id_produk_jadi : 1;
    }
}
