<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukJadi;
use App\Models\DetailAlokasiPo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinishedGoodsController extends Controller
{
    private $auditLogFile = 'audit_log_finished_goods.json';

    /**
     * Helper Internal: Mencatat Log Aktivitas (Datetime, User, Role, Aksi, Detail Perubahan)
     */
    private function recordAuditLog(string $aksi, string $namaProduk, string $detailPerubahan, ?string $keterangan = null)
    {
        $user = Auth::user();
        $logs = Storage::exists($this->auditLogFile)
            ? json_decode(Storage::get($this->auditLogFile), true) ?? []
            : [];

        array_unshift($logs, [
            'datetime'   => now()->format('d M Y H:i:s'),
            'user_name'  => $user->name ?? 'System',
            'role'       => strtoupper($user->role ?? 'STAFF'),
            'aksi'       => $aksi,
            'produk'     => $namaProduk,
            'detail'     => $detailPerubahan,
            'keterangan' => $keterangan ?: '-',
        ]);

        // Simpan maksimal 200 riwayat terbaru agar tetap ringan
        $logs = array_slice($logs, 0, 200);
        Storage::put($this->auditLogFile, json_encode($logs, JSON_PRETTY_PRINT));
    }

    public function index()
    {
        $products = ProdukJadi::with(['detailProduksi.logProduksi', 'detailAlokasiPo.poProduk'])->get();

        $totalStokFisik = $products->sum('stok');
        $totalVarian    = $products->count();
        $totalNilaiAset = $products->sum(function ($p) {
            return $p->stok * $p->harga_jual;
        });

        $totalAlokasiPo = DetailAlokasiPo::sum('jml_alokasi');

        $auditLogs = Storage::exists($this->auditLogFile)
            ? json_decode(Storage::get($this->auditLogFile), true) ?? []
            : [];

        return view('inventory.finished_goods', compact(
            'products',
            'totalStokFisik',
            'totalVarian',
            'totalNilaiAset',
            'totalAlokasiPo',
            'auditLogs'
        ));
    }

    /**
     * TAMBAH MASTER PRODUK JADI + LOG AKTIVITAS
     */
    public function storeProduct(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'varian'      => 'required|string|max:255',
            'netto'       => 'required|string|max:50',
            'harga_jual'  => 'required|numeric|min:0',
            'stok'        => 'required|integer|min:0',
        ]);

        $existing = ProdukJadi::where('nama_produk', trim($request->nama_produk))
            ->where('netto', trim($request->netto))
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! Produk jadi dengan nama dan netto tersebut sudah ada di sistem.');
        }

        $product = ProdukJadi::create([
            'nama_produk' => trim($request->nama_produk),
            'varian'      => trim($request->varian),
            'netto'       => trim($request->netto),
            'harga_jual'  => $request->harga_jual,
            'stok'        => $request->stok,
        ]);

        $this->recordAuditLog(
            'ADD BARANG',
            $product->nama_produk,
            "Menambahkan SKU baru (Varian: {$product->varian}, Netto: {$product->netto}, Harga: Rp " . number_format($product->harga_jual, 0, ',', '.') . ", Stok Awal: {$product->stok} Pcs)",
            'Input Master Produk Jadi Baru'
        );

        return redirect()->route('finished-goods.index')
                         ->with('success', 'Data Berhasil Disimpan! Master Produk Jadi baru berhasil ditambahkan.');
    }

    /**
     * EDIT MASTER PRODUK JADI + LOG DETAIL PERUBAHAN
     */
    public function updateProduct(Request $request, $id)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'varian'      => 'required|string|max:255',
            'netto'       => 'required|string|max:50',
            'harga_jual'  => 'required|numeric|min:0',
        ]);

        $product = ProdukJadi::findOrFail($id);

        $duplicate = ProdukJadi::where('nama_produk', trim($request->nama_produk))
            ->where('netto', trim($request->netto))
            ->where('id_produk_jadi', '!=', $id)
            ->first();

        if ($duplicate) {
            return redirect()->back()->with('error', 'Gagal Edit! Nama produk dan netto tersebut sudah digunakan oleh item lain.');
        }

        $perubahan = [];
        if ($product->nama_produk !== trim($request->nama_produk)) {
            $perubahan[] = "Nama: '{$product->nama_produk}' → '" . trim($request->nama_produk) . "'";
        }
        if ($product->varian !== trim($request->varian)) {
            $perubahan[] = "Varian: '{$product->varian}' → '" . trim($request->varian) . "'";
        }
        if ($product->netto !== trim($request->netto)) {
            $perubahan[] = "Netto: '{$product->netto}' → '" . trim($request->netto) . "'";
        }
        if ((float) $product->harga_jual !== (float) $request->harga_jual) {
            $perubahan[] = "Harga: Rp " . number_format($product->harga_jual, 0, ',', '.') . " → Rp " . number_format($request->harga_jual, 0, ',', '.');
        }

        $product->update([
            'nama_produk' => trim($request->nama_produk),
            'varian'      => trim($request->varian),
            'netto'       => trim($request->netto),
            'harga_jual'  => $request->harga_jual,
        ]);

        if (!empty($perubahan)) {
            $this->recordAuditLog(
                'EDIT BARANG',
                $product->nama_produk,
                implode(' | ', $perubahan),
                $request->keterangan ?? 'Pembaruan data master produk'
            );
        }

        return redirect()->route('finished-goods.index')
                         ->with('success', 'Data produk jadi berhasil diperbarui!');
    }

    /**
     * PENYESUAIAN STOK DENGAN VALIDASI ANTI-MINUS / ANTI-PIUTANG STOK + LOG AKTIVITAS
     */
    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'stok_tambahan' => 'required|integer|not_in:0',
            'keterangan'    => 'nullable|string|max:255',
        ], [
            'stok_tambahan.not_in' => 'Jumlah penyesuaian stok tidak boleh 0.',
        ]);

        $product      = ProdukJadi::findOrFail($id);
        $adj          = (int) $request->stok_tambahan;
        $stokLama     = (int) $product->stok;
        $stokTerkunci = (int) $product->stok_terkunci_po;
        $stokBebas    = max(0, $stokLama - $stokTerkunci);

        // VALIDASI ANTI-MINUS & ANTI-PIUTANG STOK (JIKA PENGURANGAN)
        if ($adj < 0) {
            $jumlahKurang = abs($adj);

            if ($stokLama <= 0 || $stokBebas <= 0) {
                return redirect()->back()->with(
                    'error',
                    "Ditolak! Stok fisik {$product->nama_produk} saat ini {$stokLama} Pcs dengan kuncian PO {$stokTerkunci} Pcs (Sisa Stok Bebas/ATP = 0). Stok tidak boleh dikurangi karena akan menjadi minus/piutang!"
                );
            }

            if ($jumlahKurang > $stokBebas) {
                return redirect()->back()->with(
                    'error',
                    "Ditolak! Pengurangan {$jumlahKurang} Pcs melebihi sisa stok bebas (ATP: {$stokBebas} Pcs dari total {$stokLama} Pcs fisik - {$stokTerkunci} Pcs terkunci PO). Maksimal pengurangan yang diizinkan adalah {$stokBebas} Pcs!"
                );
            }
        }

        $stokBaru = $stokLama + $adj;
        $product->update(['stok' => $stokBaru]);

        $tanda = $adj > 0 ? "+{$adj}" : "{$adj}";
        $this->recordAuditLog(
            'ADJUST STOK',
            $product->nama_produk,
            "Stok Fisik: {$stokLama} Pcs → {$stokBaru} Pcs ({$tanda} Pcs) | Terkunci PO: {$stokTerkunci} Pcs",
            $request->keterangan ?? ($adj > 0 ? 'Penambahan stok manual' : 'Pengurangan / Opname stok')
        );

        return redirect()->route('finished-goods.index')
                         ->with('success', "Penyesuaian stok {$product->nama_produk} ({$tanda} Pcs) berhasil diperbarui!");
    }

    /**
     * HAPUS MASTER PRODUK JADI + VALIDASI INTEGRITAS + LOG AKTIVITAS
     */
    public function destroyProduct(Request $request, $id)
    {
        $product = ProdukJadi::withCount(['detailAlokasiPo', 'detailProduksi'])->findOrFail($id);

        // 1. Cek apakah stok fisik masih ada (tidak boleh dihapus jika stok != 0)
        if ((int) $product->stok !== 0) {
            return redirect()->back()->with(
                'error',
                "Ditolak! Produk '{$product->nama_produk}' tidak dapat dihapus karena stok fisiknya masih {$product->stok} Pcs. Nol-kan stok terlebih dahulu melalui Adjust Stok."
            );
        }

        // 2. Cek apakah produk sudah terikat dengan riwayat PO Pelanggan atau Batch Produksi Dapur
        if ($product->detail_alokasi_po_count > 0 || $product->detail_produksi_count > 0) {
            return redirect()->back()->with(
                'error',
                "Ditolak! Produk '{$product->nama_produk}' tidak dapat dihapus karena sudah tercatat pada riwayat PO Pelanggan ({$product->detail_alokasi_po_count}x) atau Log Produksi ({$product->detail_produksi_count}x)."
            );
        }

        $namaProduk = $product->nama_produk;
        $detailHapus = "Menghapus SKU FG-00{$product->id_produk_jadi} (Varian: {$product->varian}, Netto: {$product->netto}, Harga: Rp " . number_format($product->harga_jual, 0, ',', '.') . ")";

        $product->delete();

        $this->recordAuditLog(
            'DELETE BARANG',
            $namaProduk,
            $detailHapus,
            $request->keterangan ?: 'Penghapusan data master produk jadi'
        );

        return redirect()->route('finished-goods.index')
                         ->with('success', "Produk '{$namaProduk}' berhasil dihapus dan tercatat di riwayat audit!");
    }
}