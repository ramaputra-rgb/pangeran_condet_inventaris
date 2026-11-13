@extends('layouts.app')

@section('title', 'Pangeran Condet, Produk jadi')

@section('content')
@php
    $gradeACount = 0;
    $gradeBCount = 0;
    $terkunciCount = 0;
    foreach ($products as $p) {
        $isB = str_contains(strtolower((string) $p->varian), 'grade b')
            || str_contains(strtolower((string) $p->nama_produk), 'grade b');
        if ($isB) {
            $gradeBCount++;
        } else {
            $gradeACount++;
        }
        if (($p->stok_terkunci_po ?? 0) > 0) {
            $terkunciCount++;
        }
    }
    $auditCount = count($auditLogs ?? []);
@endphp

<section class="masthead" aria-labelledby="j-produk">
    <div>
        <span class="eyebrow">Produk jadi</span>
        <h1 id="j-produk">Stok produk siap jual &amp; histori audit</h1>
        <p>Pantau stok fisik gudang, kuota bebas untuk dijual (ATP) setelah dipotong PO aktif, serta lakukan penyesuaian stok manual dengan jejak audit.</p>
    </div>
    <div class="acts">
        <button type="button" class="btn" onclick="openDialog('modal-create-fg')">Tambah produk baru</button>
    </div>
</section>

<section class="stub-grid" aria-label="Ringkasan produk jadi">
    <x-stub-card
        title="Total varian aktif"
        subtitle="SKU terdaftar di katalog"
        :value="number_format($totalVarian ?? $products->count())"
        unit="varian"
    />
    <x-stub-card
        title="Stok fisik gudang"
        subtitle="Nilai aset Rp {{ number_format($totalNilaiAset ?? 0, 0, ',', '.') }}"
        :value="number_format($totalStokFisik ?? 0)"
        unit="pcs fisik"
        variant="warm"
    />
    <x-stub-card
        title="Terkunci alokasi PO"
        subtitle="Bebas dijual: {{ number_format(max(0, ($totalStokFisik ?? 0) - ($totalAlokasiPo ?? 0))) }} pcs"
        :value="number_format($totalAlokasiPo ?? 0)"
        unit="pcs terkunci"
        :variant="($totalAlokasiPo ?? 0) > 0 ? 'alert' : null"
    />
</section>

<section class="panel" id="panel-produk" aria-label="Katalog & Audit Produk">
    <div class="tabs" role="group" aria-label="Tampilan">
        <button type="button" data-t="katalog" aria-selected="true">Katalog stok jadi</button>
        <button type="button" data-t="audit-produk" aria-selected="false">Histori audit produk ({{ $auditCount }})</button>
    </div>

    <div id="t-katalog" data-filter-box data-total="{{ $products->count() }}" data-unit="produk">
        <div class="controls">
            <input
                type="search"
                class="field grow"
                data-search
                placeholder="Cari nama produk, varian, netto, atau kode PRD..."
                aria-label="Cari produk jadi"
            >
            <button type="button" class="reset-btn" data-reset>Reset filter</button>
        </div>

        <div class="chips">
            <button type="button" data-f="semua" aria-pressed="true">Semua ({{ $products->count() }})</button>
            <button type="button" data-f="grade-a" aria-pressed="false">Grade A ({{ $gradeACount }})</button>
            <button type="button" data-f="grade-b" aria-pressed="false">Grade B ({{ $gradeBCount }})</button>
            <button type="button" data-f="terkunci" aria-pressed="false">Terkunci PO ({{ $terkunciCount }})</button>
            <p>Angka ATP menunjukkan stok siap jual di luar pesanan PO aktif.</p>
        </div>

        @if($products->isNotEmpty())
            <ul class="rows">
                @foreach($products as $product)
                    @php
                        $isGradeB = str_contains(strtolower((string) $product->varian), 'grade b')
                            || str_contains(strtolower((string) $product->nama_produk), 'grade b');
                        $stokFisik = (int) $product->stok;
                        $stokLocked = (int) ($product->stok_terkunci_po ?? 0);
                        $stokAtp = (int) ($product->stok_atp ?? max(0, $stokFisik - $stokLocked));
                        $kodePrd = 'PRD-' . str_pad($product->id_produk_jadi, 3, '0', STR_PAD_LEFT);

                        if ($stokFisik <= 0) {
                            $rowState = 'low';
                            $statusLabel = 'Stok kosong';
                        } elseif ($stokAtp <= 0 && $stokLocked > 0) {
                            $rowState = 'mid';
                            $statusLabel = 'Terkunci PO';
                        } else {
                            $rowState = 'ok';
                            $statusLabel = 'Siap jual';
                        }
                    @endphp
                    <li class="row fg {{ $rowState }}"
                        data-s="{{ $isGradeB ? 'grade-b' : 'grade-a' }}"
                        data-locked="{{ $stokLocked > 0 ? '1' : '0' }}"
                        data-q="{{ strtolower($kodePrd . ' ' . $product->nama_produk . ' ' . $product->varian . ' ' . $product->netto) }}"
                        @if($loop->index >= 6) data-more="1" hidden @endif>
                        <div>
                            <b>{{ $product->nama_produk }}</b>
                            <small>{{ $kodePrd }} &middot; {{ $product->varian }} &middot; {{ $product->netto }}</small>
                            <div class="tags">
                                <span class="tag {{ $isGradeB ? 'warn' : 'ok' }}">{{ $isGradeB ? 'Grade B' : 'Grade A' }}</span>
                                @if($stokLocked > 0)
                                    <span class="tag warn">{{ number_format($stokLocked) }} pcs terkunci PO</span>
                                @endif
                            </div>
                        </div>

                        <div class="price">
                            <b>Rp {{ number_format($product->harga_jual, 0, ',', '.') }}</b>
                            <small>harga jual / pcs</small>
                        </div>

                        <div class="qty">
                            {{ number_format($stokFisik) }} <small>pcs fisik ({{ number_format($stokAtp) }} ATP)</small>
                        </div>

                        <span class="st">{{ $statusLabel }}</span>

                        <div class="act">
                            <button type="button" onclick="openAdjustStockModal({{ $product->id_produk_jadi }}, {{ \Illuminate\Support\Js::from($product->nama_produk) }}, {{ $stokFisik }}, {{ $stokLocked }}, {{ $stokAtp }})">Stok</button>
                            <button type="button" onclick="openEditProductModal({{ $product->id_produk_jadi }}, {{ \Illuminate\Support\Js::from($product->nama_produk) }}, {{ \Illuminate\Support\Js::from($product->varian) }}, {{ \Illuminate\Support\Js::from($product->netto) }}, {{ (float) $product->harga_jual }})">Ubah</button>
                            <button type="button" class="del" onclick="openDeleteProductModal({{ $product->id_produk_jadi }}, {{ \Illuminate\Support\Js::from($product->nama_produk) }}, {{ $stokFisik }})">Hapus</button>
                        </div>
                    </li>
                @endforeach
            </ul>

            <div class="empty-filter" hidden>
                <strong>Tidak ada produk yang cocok</strong>
                Ubah kata kunci pencarian atau pilih chip kategori stok lain.
            </div>

            <div class="foot">
                <span id="cnt" data-cnt>Menampilkan {{ min(6, $products->count()) }} dari {{ $products->count() }} produk</span>
                @if($products->count() > 6)
                    <button type="button" class="btn" data-expand="Lihat semua {{ $products->count() }} produk" data-collapse="Tampilkan 6 produk">Lihat semua {{ $products->count() }} produk</button>
                @endif
            </div>
        @else
            <x-void-state
                title="Belum ada produk jadi"
                description="Tambahkan master produk jadi pertama untuk mencatat harga jual dan stok gudang siap kirim."
            >
                <x-slot:action>
                    <button type="button" class="btn" onclick="openDialog('modal-create-fg')">Tambah produk baru</button>
                </x-slot:action>
            </x-void-state>
        @endif
    </div>

    <div id="t-audit-produk" hidden>
        @if($auditCount > 0)
            <ul class="rows">
                @foreach($auditLogs as $log)
                    <li class="row audit">
                        <div>
                            <b>{{ $log['aksi'] ?? '-' }}</b>
                            <small>{{ $log['datetime'] ?? '-' }}</small>
                        </div>
                        <div>
                            <b>{{ $log['produk'] ?? '-' }}</b>
                            <small>{{ $log['user_name'] ?? '-' }} ({{ $log['role'] ?? '-' }})</small>
                        </div>
                        <div>
                            <b>{{ $log['detail'] ?? '-' }}</b>
                            @if(!empty($log['keterangan']))
                                <small>{{ $log['keterangan'] }}</small>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <x-void-state
                title="Belum ada histori audit produk"
                description="Setiap penambahan, koreksi stok manual, atau pengubahan master produk akan tercatat otomatis di sini."
            />
        @endif
    </div>
</section>

<!-- Modal Tambah Produk Jadi -->
<x-modal
    id="modal-create-fg"
    title="Tambah produk jadi baru"
    subtitle="Daftarkan SKU produk siap jual beserta harga dan stok awal gudang."
>
    <form action="{{ route('finished-goods.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="create-fg-nama">Nama Produk *</label>
                <input id="create-fg-nama" type="text" name="nama_produk" required placeholder="Misal: Basreng Daun Jeruk" class="form-input">
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="create-fg-varian">Varian / Grade *</label>
                    <input id="create-fg-varian" type="text" name="varian" required placeholder="Misal: Pedas / Grade A" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="create-fg-netto">Netto / Kemasan *</label>
                    <input id="create-fg-netto" type="text" name="netto" required placeholder="Misal: 100g" class="form-input">
                </div>
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="create-fg-harga">Harga Jual (Rp) *</label>
                    <input id="create-fg-harga" type="number" name="harga_jual" min="0" required placeholder="15000" class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="create-fg-stok">Stok Awal (pcs) *</label>
                    <input id="create-fg-stok" type="number" name="stok" min="0" value="0" required class="form-input">
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Simpan produk</button>
        </div>
    </form>
</x-modal>

<!-- Modal Ubah Master Produk Jadi -->
<x-modal
    id="modal-edit-fg"
    title="Ubah data produk jadi"
    subtitle="Perbarui informasi SKU, varian, netto, atau harga jual produk."
    subtitleId="edit-fg-subtitle"
>
    <form id="form-edit-fg" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
            <div class="form-field">
                <label class="form-label" for="edit-fg-nama">Nama Produk *</label>
                <input id="edit-fg-nama" type="text" name="nama_produk" required class="form-input">
            </div>

            <div class="form-grid two">
                <div class="form-field">
                    <label class="form-label" for="edit-fg-varian">Varian / Grade *</label>
                    <input id="edit-fg-varian" type="text" name="varian" required class="form-input">
                </div>
                <div class="form-field">
                    <label class="form-label" for="edit-fg-netto">Netto *</label>
                    <input id="edit-fg-netto" type="text" name="netto" required class="form-input">
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="edit-fg-harga">Harga Jual (Rp) *</label>
                <input id="edit-fg-harga" type="number" name="harga_jual" min="0" required class="form-input">
            </div>

            <div class="form-field">
                <label class="form-label" for="edit-fg-ket">Catatan Perubahan (Opsional)</label>
                <input id="edit-fg-ket" type="text" name="keterangan" placeholder="Misal: Penyesuaian harga jual Q3" class="form-input">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" class="btn">Simpan perubahan</button>
        </div>
    </form>
</x-modal>

<!-- Modal Penyesuaian Stok Manual -->
<x-modal
    id="modal-adjust-fg"
    title="Penyesuaian stok manual"
    subtitle="Masukkan angka positif untuk menambah stok, atau angka negatif untuk koreksi turun."
    subtitleId="adjust-fg-subtitle"
>
    <form id="form-adjust-fg" method="POST" onsubmit="return validateAdjustStock()">
        @csrf
        <div class="modal-body">
            <div class="modal-meta">
                <div>
                    <span>Stok fisik</span>
                    <b id="adjust-info-fisik">0 pcs</b>
                </div>
                <div>
                    <span>Terkunci PO / Bebas (ATP)</span>
                    <b id="adjust-info-atp">0 terkunci &middot; 0 ATP</b>
                </div>
            </div>

            <div class="form-field">
                <label class="form-label" for="adjust-fg-delta">Perubahan Stok (+ / -) *</label>
                <input
                    id="adjust-fg-delta"
                    type="number"
                    name="stok_tambahan"
                    required
                    placeholder="Misal: 25 atau -5"
                    oninput="validateAdjustStock()"
                    class="form-input"
                >
                <p class="form-hint">Tidak boleh bernilai 0 dan pengurangan tidak dapat melebihi stok bebas (ATP).</p>
            </div>

            <div id="adjust-warning-note" class="form-note is-danger is-hidden"></div>

            <div class="form-field">
                <label class="form-label" for="adjust-fg-ket">Alasan Penyesuaian *</label>
                <input id="adjust-fg-ket" type="text" name="keterangan" required placeholder="Misal: Hasil stok opname gudang" class="form-input">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" id="btn-submit-adjust" class="btn">Simpan penyesuaian</button>
        </div>
    </form>
</x-modal>

<!-- Modal Hapus Produk Jadi -->
<x-modal
    id="modal-delete-fg"
    title="Hapus produk jadi"
    subtitle="Penghapusan hanya diperbolehkan jika stok fisik = 0 dan tidak terikat PO/batch aktif."
    subtitleId="delete-fg-subtitle"
>
    <form id="form-delete-fg" method="POST">
        @csrf
        @method('DELETE')
        <div class="modal-body">
            <div id="delete-fg-warning" class="form-note is-danger is-hidden"></div>

            <div class="form-field">
                <label class="form-label" for="delete-fg-ket">Alasan Penghapusan *</label>
                <input id="delete-fg-ket" type="text" name="keterangan" required placeholder="Misal: SKU dihentikan permanen" class="form-input">
            </div>
        </div>
        <div class="modal-foot">
            <button type="button" class="btn outline" data-close>Batal</button>
            <button type="submit" id="btn-submit-delete-fg" class="btn danger">Hapus permanen</button>
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
    var adjustState = { fisik: 0, locked: 0, atp: 0 };

    function openEditProductModal(id, nama, varian, netto, harga) {
        document.getElementById('form-edit-fg').action = '/finished-goods/update/' + id;
        document.getElementById('edit-fg-subtitle').textContent = 'Mengubah SKU: ' + nama + ' (' + varian + ')';
        document.getElementById('edit-fg-nama').value = nama;
        document.getElementById('edit-fg-varian').value = varian;
        document.getElementById('edit-fg-netto').value = netto;
        document.getElementById('edit-fg-harga').value = harga;
        document.getElementById('edit-fg-ket').value = '';
        openDialog('modal-edit-fg');
    }

    function openAdjustStockModal(id, nama, fisik, locked, atp) {
        adjustState = { fisik: Number(fisik), locked: Number(locked), atp: Number(atp) };
        document.getElementById('form-adjust-fg').action = '/finished-goods/update-stock/' + id;
        document.getElementById('adjust-fg-subtitle').textContent = 'Produk: ' + nama;
        document.getElementById('adjust-info-fisik').textContent = adjustState.fisik.toLocaleString('id-ID') + ' pcs';
        document.getElementById('adjust-info-atp').textContent =
            adjustState.locked.toLocaleString('id-ID') + ' terkunci \u00B7 ' +
            adjustState.atp.toLocaleString('id-ID') + ' ATP';

        var deltaInput = document.getElementById('adjust-fg-delta');
        deltaInput.value = '';
        document.getElementById('adjust-fg-ket').value = '';
        validateAdjustStock();
        openDialog('modal-adjust-fg');
    }

    function validateAdjustStock() {
        var input = document.getElementById('adjust-fg-delta');
        var note = document.getElementById('adjust-warning-note');
        var btn = document.getElementById('btn-submit-adjust');
        var raw = input.value.trim();

        if (raw === '') {
            note.classList.add('is-hidden');
            note.textContent = '';
            btn.disabled = false;
            return true;
        }

        var val = Number(raw);
        var msg = '';

        if (!Number.isInteger(val) || val === 0) {
            msg = 'Perubahan stok harus bilangan bulat selain 0.';
        } else if (val < 0 && (adjustState.fisik <= 0 || adjustState.atp <= 0)) {
            msg = 'Tidak dapat mengurangi stok karena stok bebas (ATP) saat ini 0 pcs.';
        } else if (val < 0 && Math.abs(val) > adjustState.atp) {
            msg = 'Pengurangan (' + Math.abs(val) + ' pcs) melebihi stok bebas ATP (' + adjustState.atp + ' pcs).';
        }

        if (msg) {
            note.textContent = msg;
            note.classList.remove('is-hidden');
            btn.disabled = true;
            return false;
        }

        note.classList.add('is-hidden');
        note.textContent = '';
        btn.disabled = false;
        return true;
    }

    function openDeleteProductModal(id, nama, stokFisik) {
        var fisik = Number(stokFisik);
        document.getElementById('form-delete-fg').action = '/finished-goods/destroy/' + id;
        document.getElementById('delete-fg-subtitle').textContent = 'Produk: ' + nama;
        document.getElementById('delete-fg-ket').value = '';

        var warn = document.getElementById('delete-fg-warning');
        var btn = document.getElementById('btn-submit-delete-fg');
        if (fisik !== 0) {
            warn.textContent = 'Produk masih memiliki ' + fisik + ' pcs stok fisik. Kosongkan stok menjadi 0 terlebih dahulu sebelum menghapus.';
            warn.classList.remove('is-hidden');
            btn.disabled = true;
        } else {
            warn.textContent = '';
            warn.classList.add('is-hidden');
            btn.disabled = false;
        }

        openDialog('modal-delete-fg');
    }
</script>
@endpush
