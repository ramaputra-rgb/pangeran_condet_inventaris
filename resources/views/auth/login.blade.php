<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pangeran Condet</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            background-color: #580B0B; /* Warna Merah Marun Gelap Gambar 1 */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Watermark Logo Blur di Background Body */
        body::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 1440px; /* Ukuran diperbesar */
            height: 960px;
            background-image: url("{{ asset('images/logo.png') }}");
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            filter: blur(13px) opacity(38.7); /* Efek Blur & Transparansi */
            z-index: 1;
        }

        .login-box {
            position: relative;
            z-index: 2; /* Agar tetap berada di atas background blur */
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 45px 50px;
            width: 480px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .logo { width: 190px; margin-bottom: 30px; }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            width: 120px;
            font-weight: 700;
            color: #222;
            font-size: 14px;
        }

        .form-group input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.7);
            outline: none;
            font-size: 14px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);
        }

        .btn-masuk {
            background-color: #D96B36; /* Warna Cokelat Oranye Gambar 1 */
            color: #FFF;
            border: none;
            padding: 10px 45px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
            transition: 0.2s;
        }

        .btn-masuk:hover { background-color: #be5624; }
    </style>
</head>
<body>

    <div class="login-box">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Pangeran Condet" class="logo">
        
        <form action="{{ route('dashboard') }}" method="GET">
            <div class="form-group">
                <label for="username">Pengguna</label>
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