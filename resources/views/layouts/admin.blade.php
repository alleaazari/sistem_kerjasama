<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - Panel Admin KEMKOMDIGI</title>
    {{-- Font dimuat SEKALI di sini: Plus Jakarta Sans (heading) + Inter (body). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Urutan muat: tokens -> base -> style area -> components --}}
    <x-asset path="assets/shared/css/tokens.css" />
    <x-asset path="assets/shared/css/base.css" />
    <x-asset path="assets/admin/css/style.css" />
    <x-asset path="assets/shared/css/components.css" />
</head>
<body class="admin-app-body">

<div class="admin-app-layout">
    <!-- Backdrop overlay untuk Mobile Drawer -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar(false)"></div>

    <!-- SIDEBAR KIRI - BLUR BIRU GLASSMORPHISM -->
    <aside class="admin-sidebar" id="adminSidebar">
        <!-- BRAND / LOGO HEADER -->
        <div class="sidebar-brand">
            <div class="brand-logo-wrap">
                <img src="{{ asset('assets/admin/images/logo-komdigi.jpg') }}" alt="Logo Komdigi" class="brand-logo-img">
            </div>
            <div class="brand-info">
                <span class="brand-title">KEMKOMDIGI</span>
                <span class="brand-subtitle">ADMIN PANEL</span>
            </div>
            <button type="button" class="sidebar-close-btn" onclick="toggleSidebar(false)" aria-label="Tutup Menu">&times;</button>
        </div>

        <!-- DAFTAR NAVIGASI MENU (KIRI) -->
        <div class="sidebar-nav-container">
            <div class="sidebar-nav-group">
                <span class="sidebar-group-title">Menu Administrator</span>

                <!-- 1. Inbox Pengajuan -->
                <a href="{{ route('admin.inbox') }}" class="sidebar-link {{ request()->routeIs('admin.inbox') ? 'on' : '' }}">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                            <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
                        </svg>
                    </div>
                    <span class="link-label">Inbox Pengajuan</span>
                    <span class="link-badge">Aktif</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>

                <!-- 2. Profil & Visi Misi -->
                <a href="{{ route('admin.tentang') }}" class="sidebar-link {{ request()->routeIs('admin.tentang') ? 'on' : '' }}">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <span class="link-label">Profil &amp; Visi Misi</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>

                <!-- 3. Kelola Artikel -->
                <a href="{{ route('admin.artikel') }}" class="sidebar-link {{ request()->routeIs('admin.artikel') ? 'on' : '' }}">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <span class="link-label">Kelola Artikel</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>

                <!-- 4. Kelola Galeri -->
                <a href="{{ route('admin.galeri') }}" class="sidebar-link {{ request()->routeIs('admin.galeri') ? 'on' : '' }}">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                            <polyline points="21 15 16 10 5 21"></polyline>
                        </svg>
                    </div>
                    <span class="link-label">Kelola Galeri</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>

                <!-- 5. Draf Proposal AI & Masa Aktif -->
                <a href="{{ route('admin.mitra_draf') }}" class="sidebar-link {{ request()->routeIs('admin.mitra_draf') ? 'on' : '' }}">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                        </svg>
                    </div>
                    <span class="link-label">Draf Proposal AI &amp; Masa Aktif</span>
                    <span class="link-badge ai-badge">AI</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>
            </div>

            <!-- MENU SISTEM & PORTAL -->
            <div class="sidebar-nav-group">
                <span class="sidebar-group-title">Sistem &amp; Tautan Luar</span>
                <a href="http://127.0.0.1:8000" target="_blank" rel="noopener noreferrer" class="sidebar-link">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </div>
                    <span class="link-label">Lihat Portal Publik</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>
            </div>
        </div>

        <!-- PROFILE CARD DI BAGIAN BAWAH SIDEBAR -->
        <div class="sidebar-profile-card">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar">
                    <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <span class="avatar-status-dot" title="Admin Aktif"></span>
            </div>
            <div class="profile-info">
                <span class="profile-name">Admin Utama</span>
                <span class="profile-role">Super Administrator</span>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;display:inline;">
                @csrf
                <button type="submit" class="profile-logout-btn" title="Keluar dari Panel Admin">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- KONTEN UTAMA DAN HEADER -->
    <div class="admin-main-wrap">
        <!-- TOPBAR HEADER -->
        <header class="admin-topbar">
            <div class="topbar-left">
                <!-- Hamburger Menu untuk Mobile & Tablet -->
                <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar(true)" aria-label="Buka Menu Navigasi">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>

                <div class="topbar-breadcrumb">
                    <span class="breadcrumb-root">Admin Panel</span>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-current">@yield('title')</span>
                </div>
            </div>

            <div class="topbar-right">
                <div class="status-live-indicator">
                    <span class="pulse-dot"></span>
                    <span>Sistem Aktif &bull; Komdigi RI</span>
                </div>
                <span class="who-logged">Logged in as: <b>Admin Utama Kerja Sama</b></span>
                <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;display:inline;">
                    @csrf
                    <button class="btn btn2 btn-s" style="padding:5px 12px;font-size:12px;">Keluar</button>
                </form>
            </div>
        </header>

        <!-- AREA KONTEN HALAMAN (FLEX: 1 MEMASTIKAN FOOTER SELALU DI PALING BAWAH) -->
        <main class="admin-page-content">
            <div class="content-header-row">
                <h1 class="page-main-title">@yield('title')</h1>
            </div>

            @if (session('ok'))
                <div class="alert-success-box">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;"><circle cx="12" cy="12" r="12" fill="#16a34a"/><path d="M7 12.5l3.5 3.5 6-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <div>{{ session('ok') }}</div>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-error-box">
                    <ul>
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- FOOTER RESMI KOMDIGI -->
        @include('partials.footer-komdigi')
    </div>
</div>

<script>
function toggleSidebar(open) {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (!sidebar || !backdrop) return;
    
    if (open) {
        sidebar.classList.add('active');
        backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    } else {
        sidebar.classList.remove('active');
        backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Tutup sidebar otomatis saat menekan tombol Escape di keyboard
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        toggleSidebar(false);
    }
});
</script>
</body>
</html>
