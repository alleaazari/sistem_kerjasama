{{--
    Komponen aset dengan cache-busting sederhana.

    Pemakaian:
        <x-asset path="assets/shared/css/tokens.css" />
        <x-asset path="assets/shared/js/ui.js" type="js" />

    URL akhir: asset(path) + "?v=" + filemtime(public_path(path)).
    Bila file tidak ditemukan, URL tetap dihasilkan tanpa query string.
--}}
@props(['path', 'type' => 'css'])

@php
    $file = public_path($path);
    $version = is_file($file) ? filemtime($file) : null;
    $url = asset($path) . ($version ? '?v=' . $version : '');
@endphp

@if ($type === 'js')
    <script src="{{ $url }}"></script>
@else
    <link rel="stylesheet" href="{{ $url }}">
@endif
