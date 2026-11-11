<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - Portal Mitra KEMKOMDIGI</title>
    {{-- Font dimuat SEKALI di sini: Plus Jakarta Sans (heading) + Inter (body). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Urutan muat sama dengan homepage: tokens -> base -> komponen -> area mitra --}}
    <x-asset path="assets/shared/css/tokens.css" />
    <x-asset path="assets/shared/css/base.css" />
    <x-asset path="assets/shared/css/footer.css" />
    <x-asset path="assets/user/css/mitra.css" />
</head>
@php
    $username = $mitra['username'] ?? 'mitra';
    $namaPj = $mitra['pic'] ?? '';
    $fotoPath = $mitra['foto'] ?? null;
    $fotoUrl = $fotoPath
        ? route('mitra.profile.foto') . '?v=' . rawurlencode((string) ($mitra['foto_versi'] ?? '1'))
        : null;
    $inisial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($namaPj !== '' ? $namaPj : $username), 0, 1));
@endphp
<body class="mitra-page">
<a href="#konten-utama" class="skip-link">Lewati ke konten utama</a>

<div class="mitra-shell">
    {{-- Latar redup laci (hanya <1024px). --}}
    <div class="mitra-scrim" data-tutup-laci hidden></div>

    {{-- Sidebar navy: logo + nama sistem, 3 item, lalu tautan portal & keluar. --}}
    <aside class="mitra-sidebar" id="laci-mitra" aria-label="Navigasi area mitra">
        <div class="mitra-sidebar-head">
            <span class="mitra-logo">
                <img src="{{ asset('assets/user/images/logo-komdigi.jpg') }}" alt="Logo Komdigi" width="40" height="40">
            </span>
            <span class="mitra-brand-text">
                <span class="mitra-brand-title">KEMKOMDIGI</span>
                <span class="mitra-brand-sub">Portal Mitra</span>
            </span>
        </div>

        <nav class="mitra-nav" aria-label="Menu area mitra">
            <a href="{{ route('mitra.status') }}" class="mitra-nav-item"
               @if (request()->routeIs('mitra.status')) aria-current="page" @endif>Status dan Masa Aktif</a>
            <a href="{{ route('mitra.profile') }}" class="mitra-nav-item"
               @if (request()->routeIs('mitra.profile')) aria-current="page" @endif>Profil Instansi</a>
            <a href="{{ route('mitra.mou') }}" class="mitra-nav-item"
               @if (request()->routeIs('mitra.mou') || request()->routeIs('mitra.mou.*')) aria-current="page" @endif>MoU</a>
        </nav>

        <div class="mitra-sidebar-foot">
            <a href="{{ route('landing') }}" class="mitra-side-link">Lihat Portal Umum</a>
            <form method="POST" action="{{ route('logout') }}" class="mitra-logout">
                @csrf
                <button type="submit" class="mitra-side-link">Keluar</button>
            </form>
        </div>
    </aside>

    <div class="mitra-column">
        {{-- Bilah atas putih: judul halaman (kiri) + profil (kanan). --}}
        <header class="mitra-topbar">
            <button type="button" class="mitra-menu-btn" data-buka-laci
                    aria-expanded="false" aria-controls="laci-mitra" aria-label="Buka menu navigasi">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <h1 class="mitra-topbar-title">@yield('title')</h1>

            <a href="{{ route('mitra.profile') }}" class="mitra-user" aria-label="Buka halaman Profil Instansi">
                @if ($fotoUrl)
                    <img class="mitra-user-avatar" src="{{ $fotoUrl }}" alt="" width="40" height="40">
                @else
                    <span class="mitra-user-avatar mitra-user-inisial" aria-hidden="true">{{ $inisial }}</span>
                @endif
                <span class="mitra-user-text">
                    <span class="mitra-user-name">{{ $username }}</span>
                    @if ($namaPj !== '')
                        <span class="mitra-user-pj">{{ $namaPj }}</span>
                    @endif
                </span>
            </a>
        </header>

        <main class="mitra-main" id="konten-utama">
            <div class="mitra-container">
                @if (session('ok'))
                    <div class="mitra-alert mitra-alert-success" role="status" data-anim="naik" data-kelompok="pesan">
                        {{ session('ok') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</div>

@include('partials.footer-komdigi')

<x-asset path="assets/user/js/mitra-ui.js" type="js" />
<x-asset path="assets/user/js/mitra-motion.js" type="js" />
@stack('scripts')
</body>
</html>
