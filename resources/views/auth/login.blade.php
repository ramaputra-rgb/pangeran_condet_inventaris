<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Masuk Sistem &middot; Pangeran Condet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Young+Serif&family=Hanken+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom-style.css') }}">
</head>
<body>
<main class="login-shell">
    <section class="login-story" aria-label="Sorotan operasional Pangeran Condet">
        <img
            src="{{ asset('images/rengginang-login.jpg') }}"
            alt=""
            aria-hidden="true"
            class="login-story-photo"
        >

        <div class="login-story-brand">
            <img src="{{ asset('images/Logo.png') }}" alt="Logo Pangeran Condet" class="login-logo">
            <div>
                <b class="brand">Pangeran Condet</b>
                <small>Sistem Produksi &amp; Inventaris Rengginang</small>
            </div>
        </div>

        <ul class="login-pillars" aria-label="Ringkasan modul">
            <li>
                <b>Bahan baku</b>
                <span>Nota supplier &amp; batas stok minimum</span>
            </li>
            <li>
                <b>Antrean PO</b>
                <span>Urutan otomatis berdasar deadline</span>
            </li>
            <li>
                <b>Pengiriman</b>
                <span>Surat Jalan &amp; bukti serah terima</span>
            </li>
        </ul>
    </section>

    <section class="login-card" aria-labelledby="login-card-title">
        <div class="login-card-head">
            <span class="eyebrow">Otentikasi staf</span>
            <h1 id="login-card-title">Masuk ke ruang kerja</h1>
            <p>Gunakan akun yang terdaftar untuk melanjutkan operasional hari ini.</p>
        </div>

        @if(session('success'))
            <div class="flash-banner is-success" role="status">
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="flash-banner is-error" role="alert">
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" id="login-form" class="form-grid">
            @csrf
            <div class="form-field">
                <label class="form-label" for="username">Username atau Email</label>
                <input
                    id="username"
                    type="text"
                    name="username"
                    value="{{ old('username') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="Masukkan username atau email"
                    class="form-input"
                >
            </div>

            <div class="form-field">
                <label class="form-label" for="password">Kata Sandi</label>
                <div class="login-pw-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi"
                        class="form-input"
                    >
                    <button type="button" class="login-pw-toggle" onclick="togglePasswordVisibility(this)">Tampil</button>
                </div>
            </div>

            <label class="form-check">
                <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <span>Ingat sesi saya di perangkat ini</span>
            </label>

            <button type="submit" id="btn-login-submit" class="btn login-submit">Masuk sistem</button>
        </form>

        <div class="login-hint">
            <div class="login-hint-row">
                <span>Akun owner</span>
                <code>owner</code>
            </div>
            <div class="login-hint-row">
                <span>Kata sandi</span>
                <code>owner123</code>
            </div>
        </div>
    </section>
</main>

<script>
    function togglePasswordVisibility(btn) {
        var input = document.getElementById('password');
        if (!input) return;
        var nextType = input.type === 'password' ? 'text' : 'password';
        input.type = nextType;
        btn.textContent = nextType === 'text' ? 'Sembunyi' : 'Tampil';
    }

    (function () {
        var form = document.getElementById('login-form');
        var btn = document.getElementById('btn-login-submit');
        if (!form || !btn) return;
        form.addEventListener('submit', function () {
            btn.disabled = true;
            btn.textContent = 'Memproses...';
        });
    })();
</script>
</body>
</html>
