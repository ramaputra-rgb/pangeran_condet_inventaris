<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BahanProduksi;
use App\Models\TransaksiBeliBahan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RawMaterialsController extends Controller
{
    public function index(Request $request)
    {
        $materials = BahanProduksi::all();

        // 1. Hitung Statistik Ringkasan (Top-Bar)
        $totalBahanCount = $materials->count();
        $stokKritisCount = $materials->where('qty_stok', '<=', 5)->count();
        $totalPengadaanBulanIni = TransaksiBeliBahan::whereMonth('created_at', now()->month)
                                    ->whereYear('created_at', now()->year)
                                    ->sum('total_harga');

        // Preview kode transaksi berikutnya untuk form input nota
        $nextNo = (TransaksiBeliBahan::max('no_transaksi_beli') ?? 0) + 1;
        $nextTrsCode = 'TRS - ' . str_pad($nextNo, 3, '0', STR_PAD_LEFT) . ' - ' . now()->format('dmy');

        // 2. Query Riwayat Transaksi Beli Bahan
        $queryTx = TransaksiBeliBahan::with('bahanProduksi')->latest();

        if ($request->filled('periode')) {
            if ($request->periode === 'minggu_ini') {
                $queryTx->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($request->periode === 'bulan_ini') {
                $queryTx->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }
        }

        $rawTransactions = $queryTx->get();

        // Kelompokkan berdasarkan supplier & waktu transaksi (created_at)
        $groupedTransactions = $rawTransactions->groupBy(function ($item) {
            $waktu = $item->created_at ?? $item->tanggal ?? now();
            return $item->nama_supplier . '_' . Carbon::parse($waktu)->format('Y-m-d_H:i');
        });

        // Siapkan data detail transaksi terstruktur untuk modal pop-up
        $txDetailMap = [];
        foreach ($groupedTransactions as $groupKey => $items) {
            $firstItem = $items->first();
            $dt = Carbon::parse($firstItem->created_at ?? $firstItem->tanggal ?? now());
            $kodeTrs = 'TRS - ' . str_pad($firstItem->no_transaksi_beli, 3, '0', STR_PAD_LEFT) . ' - ' . $dt->format('dmy');

            $detailRows = [];
            $subtotalBarang = 0;
            $totalOngkirGroup = 0;

            foreach ($items as $item) {
                $qty = (float) $item->total_qty;
                $ongkirItem = (float) ($item->total_ongkir ?? 0);
                $hargaItemMurni = max(0, (float) $item->total_harga - $ongkirItem);
                $hargaSatuan = $qty > 0 ? round($hargaItemMurni / $qty) : 0;

                $subtotalBarang += $hargaItemMurni;
                $totalOngkirGroup += $ongkirItem;

                $detailRows[] = [
                    'nama_bahan'   => $item->bahanProduksi->nama_bahan ?? ('Bahan #' . $item->id_bahan),
                    'tipe'         => $item->bahanProduksi->tipe ?? '-',
                    'qty'          => $qty,
                    'satuan'       => $item->bahanProduksi->ukuran ?? 'Unit',
                    'harga_satuan' => $hargaSatuan,
                    'total_harga'  => $hargaItemMurni,
                ];
            }

            $txDetailMap[$groupKey] = [
                'kode_transaksi'  => $kodeTrs,
                'waktu_transaksi' => $dt->format('d/m/Y H:i:s'),
                'waktu_ringkas'   => $dt->format('d M Y H:i'),
                'nama_toko'       => $firstItem->nama_supplier,
                'total_item'      => $items->count(),
                'total_qty'       => $items->sum('total_qty'),
                'subtotal_barang' => round($subtotalBarang),
                'total_ongkir'    => round($totalOngkirGroup),
                'total_harga'     => round($items->sum('total_harga')),
                'items'           => $detailRows,
            ];
        }

        return view('inventory.raw_materials', compact(
            'materials',
            'groupedTransactions',
            'txDetailMap',
            'nextTrsCode',
            'totalBahanCount',
            'stokKritisCount',
            'totalPengadaanBulanIni'
        ));
    }

    /**
     * 1. INPUT MASTER BAHAN PRODUKSI BARU
     */
    public function storeMaterial(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'tipe'       => 'required|string',
            'ukuran'     => 'required|string',
            'qty_stok'   => 'required|numeric|min:0',
        ]);

        $existing = BahanProduksi::where('nama_bahan', trim($request->nama_bahan))
            ->where('ukuran', trim($request->ukuran))
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! Bahan baku dengan nama dan satuan ukuran tersebut sudah ada di sistem.');
        }

        BahanProduksi::create([
            'nama_bahan' => trim($request->nama_bahan),
            'tipe'       => $request->tipe,
            'ukuran'     => trim($request->ukuran),
            'qty_stok'   => $request->qty_stok,
        ]);

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Data Berhasil Disimpan! Master bahan baku baru telah terdaftar.');
    }

    /**
     * EDIT MASTER BAHAN PRODUKSI
     */
    public function updateMaterial(Request $request, $id)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'tipe'       => 'required|string',
            'ukuran'     => 'required|string',
            'qty_stok'   => 'required|numeric|min:0',
        ]);

        $bahan = BahanProduksi::findOrFail($id);

        $existing = BahanProduksi::where('nama_bahan', trim($request->nama_bahan))
            ->where('ukuran', trim($request->ukuran))
            ->where('id_bahan', '!=', $id)
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! Kombinasi nama dan ukuran bahan baku telah digunakan item lain.');
        }

        $bahan->update([
            'nama_bahan' => trim($request->nama_bahan),
            'tipe'       => $request->tipe,
            'ukuran'     => trim($request->ukuran),
            'qty_stok'   => $request->qty_stok,
        ]);

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Master bahan baku berhasil diperbarui!');
    }

    /**
     * HAPUS MASTER BAHAN PRODUKSI
     */
    public function destroyMaterial($id)
    {
        $bahan = BahanProduksi::findOrFail($id);
        $bahan->delete();

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Master bahan baku berhasil dihapus!');
    }

    /**
     * 2. CATAT NOTA PEMBELIAN BAHAN
     */
    public function storePurchase(Request $request)
    {
        $request->validate([
            'nama_supplier'  => 'required|string|max:255',
            'total_ongkir'   => 'nullable|numeric|min:0',
            'bahan_ids'      => 'required|array|min:1',
            'qty'            => 'required|array|min:1',
            'harga_satuan'   => 'nullable|array',
            'harga_total'    => 'required|array|min:1',
        ]);

        $recentDuplicate = TransaksiBeliBahan::where('nama_supplier', trim($request->nama_supplier))
            ->where('created_at', '>=', now()->subMinute())
            ->first();

        if ($recentDuplicate) {
            return redirect()->back()->with('error', 'Data Sudah Terdaftar! Nota pembelian dari supplier ini baru saja diinput.');
        }

        DB::transaction(function () use ($request) {
            $jumlahBaris = count($request->bahan_ids);
            $ongkirPerItem = $jumlahBaris > 0 ? (($request->total_ongkir ?? 0) / $jumlahBaris) : 0;

            foreach ($request->bahan_ids as $idx => $idBahan) {
                $q = (float) ($request->qty[$idx] ?? 0);
                $hargaSatuan = (float) ($request->harga_satuan[$idx] ?? 0);
                $h = isset($request->harga_total[$idx]) && $request->harga_total[$idx] !== ''
                    ? (float) $request->harga_total[$idx]
                    : ($q * $hargaSatuan);

                if ($idBahan && $q > 0) {
                    TransaksiBeliBahan::create([
                        'nama_supplier' => trim($request->nama_supplier),
                        'total_qty'     => $q,
                        'total_harga'   => $h + $ongkirPerItem,
                        'total_ongkir'  => $ongkirPerItem,
                        'id_bahan'      => $idBahan,
                    ]);

                    BahanProduksi::where('id_bahan', $idBahan)->increment('qty_stok', $q);
                }
            }
        });

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Data Berhasil Disimpan! Nota pembelian dicatat dan stok bahan baku bertambah.');
    }

    /**
     * 3. HAPUS / PENYESUAIAN NOTA PEMBELIAN
     */
    public function destroyPurchaseGroup(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->ids as $id) {
                $tx = TransaksiBeliBahan::find($id);
                if ($tx) {
                    if ($tx->id_bahan && $tx->total_qty > 0) {
                        $bahan = BahanProduksi::find($tx->id_bahan);
                        if ($bahan) {
                            $newStok = max(0, $bahan->qty_stok - $tx->total_qty);
                            $bahan->update(['qty_stok' => $newStok]);
                        }
                    }
                    $tx->delete();
                }
            }
        });

        return redirect()->route('raw-materials.index')
                         ->with('success', 'Hapus Data Berhasil! Nota pembelian dihapus dan penyesuaian stok bahan telah dilakukan.');
    }
}