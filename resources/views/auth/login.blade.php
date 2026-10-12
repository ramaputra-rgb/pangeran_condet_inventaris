<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Pangeran Condet</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        
        body {
            background-color: #580B0B;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow-x: hidden;
            padding: 20px;
        }

        /* Watermark Logo Blur di Background */
        body::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 1200px;
            height: 800px;
            background-image: url("{{ asset('images/Logo.png') }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            filter: blur(14px) opacity(0.35);
            z-index: 1;
            pointer-events: none;
        }

        .login-box {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 45px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 255, 255, 0.2);
            text-align: center;
        }

        .logo { 
            width: 170px; 
            margin-bottom: 20px;
            height: auto;
            object-fit: contain;
        }

        .system-title {
            font-size: 18px;
            font-weight: 800;
            color: #580B0B;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }

        .system-subtitle {
            font-size: 12px;
            color: #6B7280;
            font-weight: 500;
            margin-bottom: 25px;
        }

        /* Alert Notifications */
        .alert-box {
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: left;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            line-height: 1.4;
        }

        .alert-box.error {
            background-color: #FEF2F2;
            border: 1px solid #FCA5A5;
            color: #991B1B;
        }

        .alert-box.success {
            background-color: #ECFDF5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-weight: 700;
            color: #374151;
            font-size: 13px;
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #9CA3AF;
            width: 18px;
            height: 18px;
            pointer-events: none;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #E5E7EB;
            border-radius: 12px;
            background: #FFFFFF;
            outline: none;
            font-size: 13px;
            color: #1F2937;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .form-group input:focus {
            border-color: #D96B36;
            box-shadow: 0 0 0 3px rgba(217, 107, 54, 0.15);
        }

        .form-group input.is-invalid {
            border-color: #EF4444;
            background-color: #FFF5F5;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            cursor: pointer;
            color: #9CA3AF;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .toggle-password:hover {
            color: #4B5563;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: -6px;
            margin-bottom: 22px;
            font-size: 12px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4B5563;
            font-weight: 500;
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            accent-color: #D96B36;
            width: 15px;
            height: 15px;
            cursor: pointer;
        }

        .btn-masuk {
            width: 100%;
            background-color: #D96B36;
            color: #FFFFFF;
            border: none;
            padding: 13px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 6px 14px rgba(217, 107, 54, 0.35);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-masuk:hover {
            background-color: #BE5624;
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(217, 107, 54, 0.45);
        }

        .btn-masuk:active {
            transform: translateY(1px);
        }

        .credentials-hint {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px dashed #E5E7EB;
            font-size: 11px;
            color: #6B7280;
            text-align: left;
            background: #F9FAFB;
            border-radius: 10px;
            padding: 10px 14px;
        }

        .credentials-hint strong {
            color: #374151;
        }

        .hint-item {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
        }

        .badge-hint {
            background: #E5E7EB;
            padding: 1px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 11px;
            color: #1F2937;
        }

        .security-badge {
            margin-top: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 11px;
            color: #9CA3AF;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="login-box">
        <img src="{{ asset('images/Logo.png') }}" alt="Logo Pangeran Condet" class="logo">
        
        <h1 class="system-title">Pangeran Condet</h1>
        <p class="system-subtitle">Sistem Inventaris Manufaktur & Distribusi</p>

        {{-- Flash Pesan Sukses (Misal setelah logout) --}}
        @if (session('success'))
            <div class="alert-box success">
                <i data-lucide="check-circle" style="width: 18px; height: 18px; shrink: 0;"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- Flash Pesan Error / Validasi --}}
        @if ($errors->any())
            <div class="alert-box error">
                <i data-lucide="alert-circle" style="width: 18px; height: 18px; shrink: 0;"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" id="loginForm">
            @csrf

            <div class="form-group">
                <label for="username">Username atau Email</label>
                <div class="input-wrapper">
                    <i data-lucide="user" class="input-icon"></i>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        value="{{ old('username') }}" 
                        placeholder="Contoh: owner atau owner@pangerancondet.com" 
                        class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                        required 
                        autofocus 
                        autocomplete="username"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <div class="input-wrapper">
                    <i data-lucide="lock" class="input-icon"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Masukkan kata sandi Anda" 
                        class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        required 
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-password" id="togglePasswordBtn" title="Tampilkan kata sandi">
                        <i data-lucide="eye" id="eyeIcon" style="width: 18px; height: 18px;"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="btn-masuk" id="submitBtn">
                <span>Masuk ke Sistem</span>
                <i data-lucide="arrow-right" style="width: 16px; height: 16px;"></i>
            </button>
        </form>

        {{-- Informasi Akses Masuk Owner --}}
        <div class="credentials-hint">
            <div style="font-weight: 700; margin-bottom: 4px; color: #4B5563;">Akses Masuk Owner:</div>
            <div class="hint-item">
                <span>Username:</span>
                <span class="badge-hint">owner</span>
            </div>
            <div class="hint-item">
                <span>Kata Sandi:</span>
                <span class="badge-hint">owner123</span>
            </div>
        </div>

        <div class="security-badge">
            <i data-lucide="shield-check" style="width: 14px; height: 14px; color: #10B981;"></i>
            <span>Dilindungi Enkripsi Sesi & Rate Limiter</span>
        </div>
    </div>

    <script>
        // Inisialisasi ikon Lucide
        lucide.createIcons();

        // Toggle visibilitas kata sandi
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                
                // Ubah ikon mata
                if (isPassword) {
                    toggleBtn.innerHTML = '<i data-lucide="eye-off" style="width: 18px; height: 18px;"></i>';
                } else {
                    toggleBtn.innerHTML = '<i data-lucide="eye" style="width: 18px; height: 18px;"></i>';
                }
                lucide.createIcons();
            });
        }

        // Mencegah multiple submit pada form login
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        if (form && submitBtn) {
            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span>Memverifikasi...</span>';
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'not-allowed';
            });
        }
    </script>
</body>
</html>