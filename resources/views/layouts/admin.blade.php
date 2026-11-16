<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - Panel {{ \App\Support\AdminAkses::labelSesi() }} KEMKOMDIGI</title>
    {{-- Font dimuat SEKALI di sini: Plus Jakarta Sans (heading) + Inter (body). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Urutan muat: tokens -> base -> style area -> components --}}
    <x-asset path="assets/shared/css/tokens.css" />
    <x-asset path="assets/shared/css/base.css" />
    <x-asset path="assets/admin/css/style.css" />
    <x-asset path="assets/shared/css/components.css" />
    <x-asset path="assets/admin/css/panel.css" />
    <x-asset path="assets/shared/css/footer.css" />
</head>
<body class="admin-app-body">

@php
    // Menu & identitas dirender dari peran yang login, bukan hardcode.
    $peranSesi = \App\Support\AdminAkses::peranSesi();
    $labelPeran = \App\Support\AdminAkses::labelSesi();
    $namaPengguna = \App\Support\AdminAkses::namaSesi();
    $menuPanel = \App\Support\AdminAkses::menuSesi();
@endphp

<div class="admin-app-layout">
    <!-- Backdrop overlay untuk Mobile Drawer -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleSidebar(false)"></div>

    <div class="admin-shell">
    <!-- SIDEBAR KIRI -->
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

        <!-- DAFTAR NAVIGASI MENU (KIRI), dirender dari kemampuan peran login -->
        <div class="sidebar-nav-container">
            @foreach ($menuPanel as $section => $grup)
                <div class="sidebar-nav-group">
                    <span class="sidebar-group-title">{{ $grup['judul'] }}</span>

                    @foreach ($grup['item'] as $item)
                        <a href="{{ $item['url'] }}"
                           class="sidebar-link {{ $item['aktif'] !== '' && request()->routeIs($item['aktif'].'*') ? 'on' : '' }}"
                           @if ($item['luar']) target="_blank" rel="noopener noreferrer" @endif>
                            <div class="link-icon">
                                @include('partials.ikon-admin', ['ikon' => $item['ikon']])
                            </div>
                            <span class="link-label">{{ $item['label'] }}</span>
                            @if ($item['kemampuan'] === 'dashboard')
                                {{-- Counter hanya untuk peran yang punya dashboard. --}}
                                @php($jumlahMenungguPerpanjangan = \App\Support\PermohonanPerpanjangan::jumlahMenunggu())
                                @if ($jumlahMenungguPerpanjangan > 0)
                                    <span class="link-badge" title="Permohonan perpanjangan menunggu">{{ $jumlahMenungguPerpanjangan }}</span>
                                @endif
                            @elseif ($item['badge'] !== '')
                                <span class="link-badge ai-badge">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
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
                <span class="avatar-status-dot" title="{{ $labelPeran }} Aktif"></span>
            </div>
            <div class="profile-info">
                <span class="profile-name">{{ $namaPengguna }}</span>
                <span class="profile-role">{{ $labelPeran }}</span>
            </div>
            <form method="POST" action="{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.logout') }}" style="margin:0;display:inline;">
                @csrf
                <button type="submit" class="profile-logout-btn" title="Keluar dari Panel {{ $labelPeran }}">
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
                    <span class="breadcrumb-root">Panel {{ $labelPeran }}</span>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-current">@yield('title')</span>
                </div>
            </div>

            <div class="topbar-right">
                <div class="status-live-indicator">
                    <span class="pulse-dot"></span>
                    <span>Sistem Aktif &bull; Komdigi RI</span>
                </div>
                <span class="who-logged">Masuk sebagai: <b>{{ $namaPengguna }} ({{ $labelPeran }})</b></span>
                <form method="POST" action="{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.logout') }}" style="margin:0;display:inline;">
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

            @if (session('info'))
                <div class="note-box" role="status">{{ session('info') }}</div>
            @endif

            @if (session('ok'))
                <div class="note-box note-box-success" role="status">{{ session('ok') }}</div>
            @endif

            @if (session('error'))
                <div class="note-box note-box-danger" role="alert">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="note-box note-box-danger">
                    <ul>
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>

        <!-- FOOTER RESMI KOMDIGI — mengikuti kolom konten, di bawah sidebar -->
        @include('partials.footer-komdigi')
    </div>
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
