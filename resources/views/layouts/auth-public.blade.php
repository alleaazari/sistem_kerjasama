<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') - Portal Kerja Sama KEMKOMDIGI</title>
    {{-- Font dimuat SEKALI di sini: Plus Jakarta Sans (heading) + Inter (body). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    {{-- Urutan muat sama dengan homepage: tokens -> base -> komponen aplikasi -> auth --}}
    <x-asset path="assets/shared/css/tokens.css" />
    <x-asset path="assets/shared/css/base.css" />
    <x-asset path="assets/shared/css/navbar-app.css" />
    <x-asset path="assets/shared/css/footer.css" />
    <x-asset path="assets/user/css/login.css" />
</head>
<body class="auth-page">
<a href="#konten-utama" class="skip-link">Lewati ke konten utama</a>

{{-- Login mitra tampil sebagai kartu mandiri di tengah, tanpa navbar atas. --}}
@unless (request()->routeIs('login'))
    @include('partials.navbar-app')
@endunless

<main class="auth-main" id="konten-utama">
    @yield('content')
</main>

@include('partials.footer-komdigi')

<x-asset path="assets/user/js/login-form.js" type="js" />
</body>
</html>
