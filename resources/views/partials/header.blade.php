<header id="main-navbar" class="main-navbar{{ request()->routeIs('landing') ? '' : ' navbar-solid' }}">
    <div class="nav-container">
        <!-- Logo & Title (SAMA SEPERTI BRAND ADMIN) -->
        <a href="{{ route('landing') }}" class="nav-brand">
            <div class="nav-logo">
                <img src="{{ asset('assets/user/images/logo-komdigi.jpg') }}" alt="Logo Komdigi">
            </div>
            <span class="nav-brand-text">
                <span class="nav-brand-title">KEMKOMDIGI</span>
                <span class="nav-brand-subtitle">PORTAL MITRA</span>
            </span>
        </a>

        <!-- Desktop Navigation Menu -->
        <nav class="nav-menu">
            <a href="{{ route('landing') }}">Beranda</a>
            <a href="{{ route('landing') }}#tentang">Tentang</a>
            <a href="{{ route('landing') }}#berita">Berita</a>
            <a href="{{ route('landing') }}#galeri">Galeri</a>
        </nav>

        <!-- Action Buttons & Mobile Hamburger Toggle -->
        <div class="nav-actions">
            <a href="{{ route('login') }}" class="nav-btn-outline">Masuk Mitra</a>
            <a href="{{ route('pengajuan.create') }}" class="nav-btn-cyan">Pengajuan Baru</a>
            <button type="button" class="mobile-menu-btn" onclick="toggleMobileNav()" aria-label="Buka Menu Navigasi HP">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobile-nav-dropdown" class="mobile-nav-dropdown">
        <a href="{{ route('landing') }}">Beranda</a>
        <a href="{{ route('landing') }}#tentang">Tentang Komdigi</a>
        <a href="{{ route('landing') }}#berita">Berita &amp; Artikel</a>
        <a href="{{ route('landing') }}#galeri">Galeri Kegiatan</a>
        <a href="{{ route('login') }}" class="mobile-nav-btn-outline">Masuk Portal Mitra</a>
        <a href="{{ route('pengajuan.create') }}" class="mobile-nav-btn-pill">Pengajuan Baru &rarr;</a>
    </div>
</header>

<script>
function toggleMobileNav() {
    var dd = document.getElementById('mobile-nav-dropdown');
    if (dd) {
        dd.classList.toggle('active');
    }
}

window.addEventListener('scroll', function() {
    var navbar = document.getElementById('main-navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});
</script>
