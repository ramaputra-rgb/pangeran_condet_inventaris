@extends('layouts.app')

@section('title', 'Pangeran Condet, Pengaturan web')

@section('content')
<section class="masthead" aria-labelledby="j-settings">
    <div>
        <span class="eyebrow">Preferensi sistem</span>
        <h1 id="j-settings">Pengaturan web</h1>
        <p>Atur tampilan antarmuka aplikasi antara mode light dan mode dark. Perubahan langsung diterapkan di seluruh halaman dan tersimpan di browser ini.</p>
    </div>
</section>

<section class="panel" aria-labelledby="h-tema">
    <div class="head">
        <div>
            <h2 id="h-tema">Mode tampilan</h2>
            <p class="sub">Pilih tema warna yang paling nyaman untuk ruang kerja Anda.</p>
        </div>
        <span class="pill-meta ok" data-theme-status>Aktif: Mode Light</span>
    </div>

    <div class="theme-grid" role="group" aria-label="Pilihan mode tampilan web">
        <button
            type="button"
            class="theme-card"
            data-theme-choice="light"
            aria-pressed="false"
        >
            <div class="theme-preview is-light" aria-hidden="true">
                <div class="theme-preview-side">
                    <div class="theme-preview-pill"></div>
                    <div class="theme-preview-line"></div>
                    <div class="theme-preview-line short"></div>
                    <div class="theme-preview-line"></div>
                </div>
                <div class="theme-preview-main">
                    <div class="theme-preview-hero">
                        <span></span>
                        <small></small>
                    </div>
                    <div class="theme-preview-cards">
                        <div class="theme-preview-card">
                            <i></i>
                            <em></em>
                        </div>
                        <div class="theme-preview-card">
                            <i></i>
                            <em></em>
                        </div>
                    </div>
                </div>
            </div>

            <div class="theme-card-head">
                <span class="theme-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2" />
                        <path d="M12 20v2" />
                        <path d="m4.93 4.93 1.41 1.41" />
                        <path d="m17.66 17.66 1.41 1.41" />
                        <path d="M2 12h2" />
                        <path d="M20 12h2" />
                        <path d="m6.34 17.66-1.41 1.41" />
                        <path d="m19.07 4.93-1.41 1.41" />
                    </svg>
                    Mode Light
                </span>
            </div>

            <p class="theme-card-desc">
                Latar krem hangat dengan teks cokelat tua ber-kontras jelas untuk pencahayaan ruangan terang.
            </p>

            <div class="theme-card-foot">
                <span class="theme-badge" data-theme-badge>Pilih mode</span>
                <span class="theme-swatches" aria-hidden="true">
                    <span class="theme-swatch light-bg"></span>
                    <span class="theme-swatch light-panel"></span>
                    <span class="theme-swatch light-primary"></span>
                </span>
            </div>
        </button>

        <button
            type="button"
            class="theme-card"
            data-theme-choice="dark"
            aria-pressed="false"
        >
            <div class="theme-preview is-dark" aria-hidden="true">
                <div class="theme-preview-side">
                    <div class="theme-preview-pill"></div>
                    <div class="theme-preview-line"></div>
                    <div class="theme-preview-line short"></div>
                    <div class="theme-preview-line"></div>
                </div>
                <div class="theme-preview-main">
                    <div class="theme-preview-hero">
                        <span></span>
                        <small></small>
                    </div>
                    <div class="theme-preview-cards">
                        <div class="theme-preview-card">
                            <i></i>
                            <em></em>
                        </div>
                        <div class="theme-preview-card">
                            <i></i>
                            <em></em>
                        </div>
                    </div>
                </div>
            </div>

            <div class="theme-card-head">
                <span class="theme-card-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                    </svg>
                    Mode Dark
                </span>
            </div>

            <p class="theme-card-desc">
                Latar cokelat gelap yang meredupkan silau layar agar tetap nyaman digunakan saat malam atau di ruang redup.
            </p>

            <div class="theme-card-foot">
                <span class="theme-badge" data-theme-badge>Pilih mode</span>
                <span class="theme-swatches" aria-hidden="true">
                    <span class="theme-swatch dark-bg"></span>
                    <span class="theme-swatch dark-panel"></span>
                    <span class="theme-swatch dark-accent"></span>
                </span>
            </div>
        </button>
    </div>
</section>
@endsection
