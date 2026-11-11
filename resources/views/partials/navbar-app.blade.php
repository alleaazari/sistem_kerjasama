{{--
    Navbar navy untuk halaman aplikasi (login mitra & area mitra).
    Sengaja TIDAK memakai partials/header.blade.php milik homepage: header
    homepage hanya memuat logo + navigasi + tombol pengajuan, sedangkan di
    sini ada aksi akun (nama mitra & tombol Keluar) dan tautan portal utama.
    Warna, radius, dan tipografi seluruhnya dari assets/shared/css/tokens.css
    lewat assets/shared/css/navbar-app.css.
--}}
<header class="app-navbar">
    <div class="app-navbar-inner">
        <a href="{{ route('landing') }}" class="app-navbar-brand">
            <span class="app-navbar-logo">
                <img src="{{ asset('assets/user/images/logo-komdigi.jpg') }}" alt="Logo Komdigi" width="40" height="40">
            </span>
            <span class="app-navbar-brand-text">
                <span class="app-navbar-brand-title">KEMKOMDIGI</span>
                <span class="app-navbar-brand-subtitle">Portal Mitra</span>
            </span>
        </a>

        <div class="app-navbar-actions">
            <a href="{{ route('landing') }}" class="app-navbar-portal">Lihat Portal Utama</a>

            @isset($mitra)
                <span class="app-navbar-user" title="Mitra yang sedang masuk">{{ $mitra['username'] }}</span>
                <form method="POST" action="{{ route('logout') }}" class="app-navbar-logout">
                    @csrf
                    <button type="submit" class="app-btn app-btn-ghost">Keluar</button>
                </form>
            @endisset
        </div>
    </div>
</header>
