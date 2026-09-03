<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login B - Pangeran Condet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            height: 100vh;
            display: flex;
            background-color: #FFF9E6; /* Warna Krem Dasar */
            overflow: hidden;
            position: relative;
        }

        /* Latar Belakang Blur Produk/Logo Keseluruhan */
        body::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 1512px; /* Ukuran diperbesar */
            height: 1008px;
            background-image: url("{{ asset('images/logo.png') }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            filter: blur(13px) opacity(38.7); /* Efek Blur & Transparansi */
            z-index: 1;
        }

        /* Sisi Kiri (Teks Branding) */
        .left-section {
            flex: 1;
            padding: 80px;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            margin-bottom: 120px;
            z-index: 2;
        }

        .brand-subtitle {
            font-size: 26px;
            font-weight: 700;
            color: #FFFFFF;
            text-shadow: 0 2px 4px rgba(0,0,0,0.4);
            letter-spacing: 1px;
        }

        .brand-title {
            font-size: 44px;
            font-weight: 800;
            color: #000000;
            line-height: 1.1;
            margin: 4px 0;
        }

        .brand-desc {
            font-size: 38px;
            font-weight: 700;
            font-style: italic;
            color: #3E1F07;
        }

        /* Sisi Kanan (Form Card Login dengan Border Cyan Gambar 2) */
        .right-section {
            width: 460px;
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 2;
            margin: 20px;
            border-radius: 4px;
        }

        .logo { width: 170px; margin-bottom: 45px; }

        .form-group {
            width: 100%;
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid rgba(0,0,0,0.1);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.6);
            outline: none;
            font-size: 14px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.04);
        }

        .btn-masuk {
            width: 100%;
            background-color: rgba(240, 240, 240, 0.8);
            color: #222222;
            border: none;
            padding: 12px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            margin-top: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: 0.2s;
        }

        .btn-masuk:hover {
            background-color: #F47C20;
            color: #FFFFFF;
        }
    </style>
</head>
<body>

    <!-- Sisi Kiri -->
    <div class="left-section">
        <div class="brand-subtitle">PANGERAN JAYA</div>
        <div class="brand-title">RENGGINANG IKAN</div>
        <div class="brand-desc">PANGERAN CONDET</div>
    </div>

    <!-- Sisi Kanan -->
    <div class="right-section">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Pangeran Condet" class="logo">

        <form action="{{ route('dashboard') }}" method="GET" style="width: 100%;">
            <div class="form-group">
                <label for="username">ID</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="btn-masuk">Masuk</button>
        </form>
    </div>

</body>
</html>