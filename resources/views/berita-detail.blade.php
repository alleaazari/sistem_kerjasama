@extends('layouts.user-public')
@section('title', $artikel['judul'])
@section('body_class', 'berita-detail-page')

@section('content')
@php
    $slug = \Illuminate\Support\Str::slug($artikel['judul'] ?? '');
@endphp

<!-- Animasi masuk halaman -->
<div class="berita-enter">
    <!-- Tombol kembali -->
    <div class="berita-back-bar">
        <a href="{{ route('landing') }}#berita" class="berita-back-link" aria-label="Kembali ke beranda">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    <!-- Header artikel -->
    <header class="berita-header">
        <time class="berita-date" datetime="">
            {{ $artikel['tanggal'] ?? '' }}
        </time>
        <h1 class="berita-title">{{ $artikel['judul'] }}</h1>
        <div class="berita-hero-img" aria-hidden="true">
            <img src="{{ $artikel['gambar'] ?? '' }}" alt="" loading="lazy">
        </div>
    </header>

    <!-- Konten artikel -->
    <article class="berita-konten">
        <div class="berita-paragraph">
            @php
                // Pecah konten jadi paragraf jika mengandung \n\n
                $paragraf = preg_split('/\n{2,}/', trim($artikel['konten'] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            @endphp
            @forelse ($paragraf as $p)
                <p>{{ $p }}</p>
            @empty
                <p>{{ $artikel['konten'] }}</p>
            @endforelse
        </div>
    </article>

    <!-- Artikel lain -->
    @if (count($daftar) > 1)
    <section class="berita-lain" aria-label="Artikel lainnya">
        <h2 class="berita-lain-title">Artikel Lainnya</h2>
        <div class="berita-lain-grid">
            @foreach ($daftar as $item)
                @if ($item['slug'] !== $slug)
                <a href="{{ route('berita.detail', $item['slug']) }}" class="berita-lain-card">
                    <div class="berita-lain-img">
                        <img src="{{ $item['gambar'] }}" alt="" loading="lazy">
                    </div>
                    <div class="berita-lain-text">
                        <time class="berita-lain-date">{{ $item['tanggal'] }}</time>
                        <h3 class="berita-lain-judul">{{ $item['judul'] }}</h3>
                    </div>
                </a>
                @endif
            @endforeach
        </div>
    </section>
    @endif
</div>

<script>
// Tambahkan class siap di-load setelah DOM ready
document.addEventListener('DOMContentLoaded', function () {
    requestAnimationFrame(function () {
        document.querySelector('.berita-enter').classList.add('is-visible');
    });
});
</script>
@endsection
