<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Portal Mitra KEMKOMDIGI</title>
    {{-- Font dimuat SEKALI di sini: Plus Jakarta Sans (heading) + Inter (body). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Urutan muat: tokens -> base -> style area -> components --}}
    <x-asset path="assets/shared/css/tokens.css" />
    <x-asset path="assets/shared/css/base.css" />
    <x-asset path="assets/user/css/style.css" />
    <x-asset path="assets/shared/css/components.css" />
</head>
<body class="admin-app-body">

<div class="admin-app-layout">
    <!-- Backdrop overlay untuk Mobile Drawer -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar(false)"></div>

    <!-- SIDEBAR KIRI - BLUR BIRU GLASSMORPHISM (SAMA SEPERTI ADMIN) -->
    <aside class="admin-sidebar" id="adminSidebar">
        <!-- BRAND / LOGO HEADER -->
        <div class="sidebar-brand">
            <div class="brand-logo-wrap">
                <img src="{{ asset('assets/user/images/logo-komdigi.jpg') }}" alt="Logo Komdigi" class="brand-logo-img">
            </div>
            <div class="brand-info">
                <span class="brand-title">KEMKOMDIGI</span>
                <span class="brand-subtitle">MITRA PANEL</span>
            </div>
            <button type="button" class="sidebar-close-btn" onclick="toggleSidebar(false)" aria-label="Tutup Menu">&times;</button>
        </div>

        <!-- DAFTAR NAVIGASI MENU (KIRI) -->
        <div class="sidebar-nav-container">
            <div class="sidebar-nav-group">
                <span class="sidebar-group-title">Menu Dashboard Mitra</span>

                <!-- 1. Status & Masa Aktif -->
                <a href="{{ route('mitra.status') }}" class="sidebar-link {{ request()->routeIs('mitra.status') ? 'on' : '' }}">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
                            <path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
                        </svg>
                    </div>
                    <span class="link-label">Status &amp; Masa Aktif</span>
                    <span class="link-badge">Aktif</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>

                <!-- 2. Edit Profil Instansi -->
                <a href="{{ route('mitra.profile') }}" class="sidebar-link {{ request()->routeIs('mitra.profile') ? 'on' : '' }}">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <span class="link-label">Edit Profil Instansi</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>

                <!-- 3. Generator Draf Proposal AI -->
                <a href="{{ route('mitra.draf') }}" class="sidebar-link {{ request()->routeIs('mitra.draf') ? 'on' : '' }}">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
                        </svg>
                    </div>
                    <span class="link-label">Generator Draf Proposal AI</span>
                    <span class="link-badge ai-badge">AI</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>
            </div>

            <!-- MENU SISTEM & PORTAL -->
            <div class="sidebar-nav-group">
                <span class="sidebar-group-title">Sistem &amp; Tautan Luar</span>
                <a href="{{ route('landing') }}" target="_blank" rel="noopener noreferrer" class="sidebar-link">
                    <div class="link-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </div>
                    <span class="link-label">Lihat Portal Utama</span>
                    <span class="link-arrow">&rsaquo;</span>
                </a>
            </div>
        </div>

        <!-- PROFILE CARD DI BAGIAN BAWAH SIDEBAR -->
        <div class="sidebar-profile-card">
            <div class="profile-avatar-wrap">
                <div class="profile-avatar" style="padding:0;overflow:hidden;background:#ffffff;">
                    <img src="{{ $mitra['foto'] }}" alt="{{ $mitra['username'] }}" id="top-user-avatar" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                </div>
                <span class="avatar-status-dot" title="Mitra Aktif"></span>
            </div>
            <div class="profile-info">
                <span class="profile-name">{{ $mitra['username'] }}</span>
                <span class="profile-role">{{ $mitra['pic'] }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;display:inline;">
                @csrf
                <button type="submit" class="profile-logout-btn" title="Keluar dari Portal Mitra">
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
                    <span class="breadcrumb-root">Mitra Panel</span>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-current">@yield('title')</span>
                </div>
            </div>

            <div class="topbar-right">
                <div class="status-live-indicator">
                    <span class="pulse-dot"></span>
                    <span>Sistem Aktif &bull; Komdigi RI</span>
                </div>
                <span class="who-logged">Portal Mitra: <b>{{ $mitra['instansi'] }}</b></span>
                <form method="POST" action="{{ route('logout') }}" style="margin:0;display:inline;">
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

            <!-- CARDS STATUS MASA AKTIF KERJASAMA -->
            <div class="status-card">
                <div class="status-card-header">
                    <div>
                        <div style="font-size:12px;color:var(--text-muted);font-weight:600;text-transform:uppercase;">Status Kemitraan Komdigi</div>
                        <div style="font-size:18px;font-weight:700;color:var(--primary);margin-top:2px;">{{ $mitra['instansi'] }}</div>
                    </div>
                    <div class="status-badge-active">
                        <span>STATUS: {{ $mitra['status'] }}</span>
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);margin-top:8px;">
                    <span>Masa Berlaku: <b>{{ $mitra['mulai'] }}</b> s/d <b>{{ $mitra['selesai'] }}</b></span>
                    <span><b>365 Hari Tersisa</b></span>
                </div>
                <div class="progress-bar-bg">
                    <div class="progress-bar-fill" style="width: 85%;"></div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:10px;border-top:1px solid var(--border-color);">
                    <span style="font-size:12px;color:var(--text-muted);">Kerja sama aktif dalam program Transformasi Digital &amp; SDM Komdigi</span>
                    <button type="button" class="btn btn2 btn-s" onclick="alert('Pengajuan perpanjangan masa aktif kerjasama telah dikirimkan ke Admin Komdigi!')">Perpanjang Masa Aktif</button>
                </div>
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
