@extends('layouts.user')
@section('title', 'Detail MoU')

@php
    $keterangan = (string) ($mou['keterangan'] ?? '');
    $isi = is_array($mou['isi'] ?? null) && count($mou['isi']) > 0 ? $mou['isi'] : null;
    $berkas = $mou['berkas'] ?? null;
    $ekstensi = is_string($berkas) && $berkas !== '' ? strtolower(pathinfo($berkas, PATHINFO_EXTENSION)) : '';
    $disetujui = $mou['status'] === 'Disetujui';
    $bolehUnduh = $disetujui && is_string($berkas) && $berkas !== '';
@endphp

@section('content')
    <div>
        <a href="{{ route('mitra.mou') }}" class="reader-back" data-anim="naik" data-kelompok="mou">&larr; Daftar MoU</a>
    </div>

    <article class="reader" data-anim="naik" data-kelompok="mou" aria-labelledby="judul-mou">
        <header class="reader-head">
            <h2 id="judul-mou">{{ $mou['judul'] }}</h2>
            <div class="reader-meta">
                <span>Nomor: <strong>{{ $mou['nomor'] }}</strong></span>
                <span>{{ \Illuminate\Support\Carbon::parse($mou['tanggal'])->translatedFormat('d F Y') }}</span>
                <span class="status-badge {{ $disetujui ? 'status-badge-ok' : ($mou['status'] === 'Perlu Revisi' ? 'status-badge-danger' : 'status-badge-warn') }}">{{ $mou['status'] }}</span>
            </div>
            @if ($keterangan !== '')
                <p class="reader-keterangan">{{ $keterangan }}</p>
            @endif
        </header>

        <h3 class="reader-title">Isi MoU</h3>

        @if ($isi)
            {{-- Isi berbentuk bagian (judul + paragraf), dieksekusi sebagai teks biasa. --}}
            <div class="reader-isi">
                @foreach ($isi as $bagian)
                    <h3>{{ $bagian['judul'] }}</h3>
                    @foreach (($bagian['paragraf'] ?? []) as $paragraf)
                        <p>{{ $paragraf }}</p>
                    @endforeach
                @endforeach
            </div>
        @elseif ($ekstensi === 'pdf')
            <p class="reader-catatan">Dokumen PDF ditampilkan langsung di bawah ini. Bila peramban Anda tidak mendukung pratinjau PDF, gunakan tautan unduh.</p>
            <iframe class="pdf-frame" src="{{ route('mitra.mou.berkas', $mou['id']) }}"
                    title="Pratinjau dokumen MoU: {{ $mou['judul'] }}" loading="lazy"></iframe>
            <div class="form-actions">
                @if ($bolehUnduh)
                    <a href="{{ route('mitra.mou.unduh', $mou['id']) }}" class="mitra-btn mitra-btn-primary">Unduh Dokumen</a>
                @endif
            </div>
        @elseif ($ekstensi === 'docx')
            <p class="reader-catatan">Pratinjau tidak tersedia untuk DOCX. Unduh dokumen untuk membukanya di aplikasi pengolah kata.</p>
            <div class="form-actions">
                @if ($bolehUnduh)
                    <a href="{{ route('mitra.mou.unduh', $mou['id']) }}" class="mitra-btn mitra-btn-primary">Unduh Dokumen</a>
                @else
                    <span class="field-help">Unduhan aktif setelah MoU disetujui admin.</span>
                @endif
            </div>
        @else
            <p class="reader-catatan">Isi dokumen belum tersedia. Berkas MoU akan tampil setelah diunggah dan disetujui admin.</p>
            @if ($bolehUnduh)
                <div class="form-actions">
                    <a href="{{ route('mitra.mou.unduh', $mou['id']) }}" class="mitra-btn mitra-btn-primary">Unduh Dokumen</a>
                </div>
            @endif
        @endif
    </article>
@endsection
