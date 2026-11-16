@extends('layouts.admin')
@section('title', 'Masa Aktif Mitra')

@section('content')
<div class="panel">
    <div class="panel-head">
        <div class="panel-head-row">
            <div>
                <h2 class="panel-title">Masa Aktif Mitra</h2>
                <p class="panel-sub">Periode berlaku kerja sama tiap instansi mitra.</p>
            </div>
        </div>
    </div>

    {{-- Pencarian --}}
    <div class="panel-body">
        <form method="GET" action="{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.masa_aktif') }}" class="panel-toolbar" role="search">
            <div class="row">
                <label for="q">Cari nama instansi mitra</label>
                <input type="text" id="q" name="q" value="{{ $kata }}" maxlength="100"
                       placeholder="Contoh: Universitas Indonesia" autocomplete="off">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-s">Cari</button>
                @if ($kata !== '')
                    <a href="{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.masa_aktif') }}" class="btn btn2 btn-s">Hapus pencarian</a>
                @endif
            </div>
        </form>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="table-masa-aktif">
            <caption class="sr-only">Daftar masa aktif kerja sama tiap instansi mitra</caption>
            <thead>
                <tr>
                    <th scope="col">Nama Instansi Mitra</th>
                    <th scope="col">Jangka Waktu Masa Aktif</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                {{-- Kerangka muat: tampil saat berpindah halaman/mencari (lihat skrip di bawah). --}}
                <tr class="baris-skeleton" hidden aria-hidden="true">
                    <td><span class="skeleton skeleton-wide"></span></td>
                    <td><span class="skeleton skeleton-wide"></span></td>
                    <td><span class="skeleton skeleton-wide"></span></td>
                    <td><span class="skeleton"></span></td>
                </tr>
                <tr class="baris-error" hidden>
                    <td colspan="4" class="cell-empty" role="alert">
                        <span class="teks-error">Data masa aktif gagal dimuat.</span>
                        <br><button type="button" class="btn btn2 btn-s" data-ulang>Muat ulang</button>
                    </td>
                </tr>
                @forelse ($rows as $r)
                @php
                    $kondisi = $r['status']['kondisi'];
                    $kelas = match ($kondisi) {
                        \App\Support\StatusKerjasama::AKTIF => 'badge badge-success',
                        \App\Support\StatusKerjasama::SEGERA => 'badge badge-warning',
                        default => 'badge badge-danger',
                    };
                    // Label tampilan khusus halaman admin. StatusKerjasama tidak diubah
                    // agar halaman mitra tetap memakai labelnya sendiri.
                    $labelTampil = match ($kondisi) {
                        \App\Support\StatusKerjasama::AKTIF => 'Aktif',
                        \App\Support\StatusKerjasama::SEGERA => 'Segera Berakhir',
                        default => 'Kedaluwarsa',
                    };
                @endphp
                <tr>
                    <td><b class="cell-strong">{{ $r['instansi'] }}</b></td>
                    <td class="cell-muted">
                        @if ($r['status']['mulai'] && $r['status']['selesai'])
                            {{ $r['status']['mulai']->translatedFormat('d M Y') }}
                            &nbsp;s/d&nbsp;
                            {{ $r['status']['selesai']->translatedFormat('d M Y') }}
                        @else
                            Tanggal periode belum tersedia
                        @endif
                    </td>
                    <td>
                        <span class="{{ $kelas }}">{{ $labelTampil }}</span>
                        <div class="cell-faint">{{ $r['status']['keterangan'] }}</div>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button type="button" class="btn btn2 btn-s" data-atur>Atur Masa Aktif</button>
                        </div>

                        {{-- Form per baris: hanya muncul setelah tombol diklik. --}}
                        <form method="POST" action="{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.masa_aktif.atur') }}" class="form-atur" hidden>
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="uuid" value="{{ $r['uuid'] }}">

                            <div class="row">
                                <label for="mulai-{{ $r['uuid'] }}">Tanggal mulai</label>
                                <input type="date" id="mulai-{{ $r['uuid'] }}" name="tanggal_mulai" required
                                       value="{{ $r['tanggal_mulai'] ?? '' }}">
                            </div>

                            <div class="row">
                                <label for="akhir-{{ $r['uuid'] }}">Tanggal berakhir</label>
                                <input type="date" id="akhir-{{ $r['uuid'] }}" name="tanggal_berakhir" required
                                       value="{{ $r['tanggal_berakhir'] ?? '' }}">
                            </div>

                            <div class="row">
                                <label for="alasan-{{ $r['uuid'] }}">Alasan perubahan (opsional)</label>
                                <input type="text" id="alasan-{{ $r['uuid'] }}" name="alasan" maxlength="255"
                                       value="{{ $r['alasan'] ?? '' }}" placeholder="Contoh: perpanjangan 2 tahun">
                            </div>

                            <div class="table-actions">
                                <button type="submit" class="btn btn-s">Simpan</button>
                                <button type="button" class="btn btn2 btn-s" data-batal>Batal</button>
                            </div>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="cell-empty">
                        @if ($kata !== '')
                            Tidak ada mitra yang cocok dengan pencarian &ldquo;{{ $kata }}&rdquo;.
                            <br><a href="{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.masa_aktif') }}">Tampilkan semua mitra</a>
                        @else
                            Belum ada data masa aktif mitra.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($halamanAkhir > 1)
    <div class="panel-body">
        <nav class="pagination-nav" aria-label="Navigasi halaman masa aktif">
            <p class="cell-muted" role="status">
                Menampilkan halaman {{ $halaman }} dari {{ $halamanAkhir }} &middot; {{ $total }} mitra
            </p>
            <div class="table-actions">
                @if ($halaman > 1)
                    <a class="btn btn2 btn-s" rel="prev"
                       href="{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.masa_aktif', array_filter(['q' => $kata, 'page' => $halaman - 1])) }}">Sebelumnya</a>
                @endif
                @if ($halaman < $halamanAkhir)
                    <a class="btn btn2 btn-s" rel="next"
                       href="{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.masa_aktif', array_filter(['q' => $kata, 'page' => $halaman + 1])) }}">Berikutnya</a>
                @endif
            </div>
        </nav>
    </div>
    @endif
</div>

<script>
// Buka/tutup formulir "Atur Masa Aktif" milik baris sendiri.
document.querySelectorAll('[data-atur]').forEach(function (tombol) {
    tombol.addEventListener('click', function () {
        var form = tombol.closest('td').querySelector('.form-atur');
        if (!form) return;
        form.hidden = false;
        tombol.hidden = true;
        form.querySelector('input[type="date"]').focus();
    });
});

document.querySelectorAll('[data-batal]').forEach(function (tombol) {
    tombol.addEventListener('click', function () {
        var form = tombol.closest('.form-atur');
        var sel = tombol.closest('td');
        if (!form || !sel) return;
        form.hidden = true;
        var pemicu = sel.querySelector('[data-atur]');
        if (pemicu) {
            pemicu.hidden = false;
            pemicu.focus();
        }
    });
});

// Keadaan muat: tampilkan kerangka saat halaman ditinggal lewat pencarian/paginasi,
// dan pulihkan bila halaman kembali dari cache peramban.
(function () {
    var kerangka = document.querySelector('.baris-skeleton');
    var galat = document.querySelector('.baris-error');
    var isi = document.querySelectorAll('tbody tr:not(.baris-skeleton):not(.baris-error)');

    function tampilkanKerangka() {
        if (!kerangka) return;
        if (galat) galat.hidden = true;
        isi.forEach(function (tr) { tr.hidden = true; });
        kerangka.hidden = false;
        kerangka.setAttribute('aria-hidden', 'false');
    }

    function sembunyikanKerangka() {
        if (!kerangka) return;
        kerangka.hidden = true;
        kerangka.setAttribute('aria-hidden', 'true');
        isi.forEach(function (tr) { tr.hidden = false; });
    }

    document.querySelectorAll('form[role="search"], .pagination-nav a').forEach(function (el) {
        el.addEventListener('submit', tampilkanKerangka);
        el.addEventListener('click', tampilkanKerangka);
    });

    window.addEventListener('pageshow', sembunyikanKerangka);

    var ulang = document.querySelector('[data-ulang]');
    if (ulang) {
        ulang.addEventListener('click', function () { window.location.reload(); });
    }
})();
</script>
@endsection