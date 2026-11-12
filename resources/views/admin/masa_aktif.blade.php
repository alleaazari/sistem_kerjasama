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
        <form method="GET" action="{{ route('admin.masa_aktif') }}" class="panel-toolbar" role="search">
            <div class="row">
                <label for="q">Cari nama instansi mitra</label>
                <input type="text" id="q" name="q" value="{{ $kata }}" maxlength="100"
                       placeholder="Contoh: Universitas Indonesia" autocomplete="off">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-s">Cari</button>
                @if ($kata !== '')
                    <a href="{{ route('admin.masa_aktif') }}" class="btn btn2 btn-s">Hapus pencarian</a>
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
                @forelse ($rows as $r)
                @php
                    $kondisi = $r['status']['kondisi'];
                    $kelas = match ($kondisi) {
                        \App\Support\StatusKerjasama::AKTIF => 'badge badge-success',
                        \App\Support\StatusKerjasama::SEGERA => 'badge badge-warning',
                        default => 'badge badge-danger',
                    };
                @endphp
                <tr>
                    <td><b class="cell-strong">{{ $r['instansi'] }}</b></td>
                    <td class="cell-muted">
                        @if ($r['status']['mulai'] && $r['status']['selesai'])
                            {{ $r['status']['mulai']->translatedFormat('d F Y') }}
                            &nbsp;s/d&nbsp;
                            {{ $r['status']['selesai']->translatedFormat('d F Y') }}
                        @else
                            Tanggal periode belum tersedia
                        @endif
                    </td>
                    <td>
                        <span class="{{ $kelas }}">{{ $r['status']['label'] }}</span>
                        <div class="cell-faint">{{ $r['status']['keterangan'] }}</div>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button type="button" class="btn btn2 btn-s" data-atur>Atur Masa Aktif</button>
                        </div>

                        {{-- Form per baris: hanya muncul setelah tombol diklik. --}}
                        <form method="POST" action="{{ route('admin.masa_aktif.atur') }}" class="form-atur" hidden>
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
                            <br><a href="{{ route('admin.masa_aktif') }}">Tampilkan semua mitra</a>
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
                       href="{{ route('admin.masa_aktif', array_filter(['q' => $kata, 'page' => $halaman - 1])) }}">Sebelumnya</a>
                @endif
                @if ($halaman < $halamanAkhir)
                    <a class="btn btn2 btn-s" rel="next"
                       href="{{ route('admin.masa_aktif', array_filter(['q' => $kata, 'page' => $halaman + 1])) }}">Berikutnya</a>
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
</script>
@endsection