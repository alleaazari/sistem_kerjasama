<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk Admin - KEMKOMDIGI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">
</head>
<body>
<div class="head">
    <div class="wrap">
        <img src="{{ asset('assets/admin/images/logo-komdigi.jpg') }}" alt="Logo Komdigi">
        <div>
            <h1>KEMKOMDIGI ADMIN</h1>
            <div class="sub">Panel Administrator Portal Kerja Sama</div>
        </div>
    </div>
</div>

<div class="wrap" style="max-width:420px;margin:30px auto;">
    <div class="box">
        <h2>Masuk Panel Admin</h2>
        <div class="in">

            {{-- AKUN DEMO: hanya tampil saat APP_DEBUG=true (lokal/presentasi). Otomatis hilang di produksi. --}}
            @if(config('app.debug'))
            <div class="demo-box">
                <h4>Akun Demo (Klik untuk Isi Otomatis)</h4>
                <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px;">
                    Admin: <b>admin</b> / <b>Admin#2026</b><br>
                    Developer: <b>developer</b> / <b>Dev#2026</b>
                </p>
                <button type="button" class="demo-btn" onclick="fillLogin('admin', 'Admin#2026')" style="width:100%;margin-bottom:6px;">Gunakan Akun Demo Admin</button>
                <button type="button" class="demo-btn" onclick="fillLogin('developer', 'Dev#2026')" style="width:100%;">Gunakan Akun Demo Developer</button>
            </div>
            @endif

            @if (session('login_error'))
                <div style="background:#fee2e2;color:#b91c1c;padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:14px;">
                    {{ session('login_error') }}
                </div>
            @endif

            @if ($errors->any())
                <div style="background:#fee2e2;color:#b91c1c;padding:10px 14px;border-radius:8px;margin-bottom:12px;font-size:14px;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}">
                @csrf
                <div class="row">
                    <label for="u">Username Admin</label>
                    <input type="text" id="u" name="username" value="{{ old('username') }}" placeholder="Username" autocomplete="username" required>
                </div>
                <div class="row">
                    <label for="p">Password</label>
                    <input type="password" id="p" name="password" placeholder="Password" autocomplete="current-password" required>
                </div>
                <button class="btn" style="width:100%;padding:10px;margin-top:8px;">Masuk ke Panel Admin</button>
            </form>
        </div>
    </div>
</div>

<script>
function fillLogin(u, p) {
    document.getElementById('u').value = u;
    document.getElementById('p').value = p;
}
</script>
</body>
</html>