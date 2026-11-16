@extends('layouts.admin')
@section('title', 'Aktivitas Admin Kerjasama')

@section('content')
<div class="panel">
    <div class="panel-head">
        <div class="panel-head-row">
            <div>
                <h2 class="panel-title">Log Aktivitas</h2>
                <p class="panel-sub">Catatan aksi sensitif pada panel kerja sama. Hanya dapat dibaca.</p>
            </div>
        </div>
    </div>

    <div class="panel-body">
        <form method="GET" action="{{ route('superadmin.aktivitas') }}" class="panel-toolbar" role="search">
            <div class="filter-grid">
                <div class="row">
                    <label for="f-peran">Peran</label>
                    <select id="f-peran" name="peran">
                        <option value="">Semua peran</option>
                        @foreach ($daftarPeran as $peran)
                            <option value="{{ $peran }}" @selected($filter['peran'] === $peran)>
                                {{ \App\Support\AdminAkses::label($peran) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <label for="f-aksi">Aksi</label>
                    <select id="f-aksi" name="aksi">
                        <option value="">Semua aksi</option>
                        @foreach ($daftarAksi as $aksi)
                            <option value="{{ $aksi }}" @selected($filter['aksi'] === $aksi)>
                                {{ \App\Support\LogAktivitas::labelAksi($aksi) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row">
                    <label for="f-dari">Dari tanggal</label>
                    <input type="date" id="f-dari" name="dari" value="{{ $filter['dari'] }}">
                </div>

                <div class="row">
                    <label for="f-sampai">Sampai tanggal</label>
                    <input type="date" id="f-sampai" name="sampai" value="{{ $filter['sampai'] }}">
                </div>

                <div class="row">
                    <label for="q">Cari nama, target, atau IP</label>
                    <input type="text" id="q" name="q" value="{{ $filter['q'] }}" maxlength="100"
                           placeholder="Contoh: 12 atau 203.0.113.1" autocomplete="off">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-s">Terapkan filter</button>
                @if ($adaFilter)
                    <a href="{{ route('superadmin.aktivitas') }}" class="btn btn2 btn-s">Hapus filter</a>
                @endif
            </div>
        </form>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="table-aktivitas">
            <caption class="sr-only">Catatan aktivitas panel kerja sama, terbaru lebih dahulu</caption>
            <thead>
                <tr>
                    <th scope="col">Waktu</th>
                    <th scope="col">Peran</th>
                    <th scope="col">Nama Akun</th>
                    <th scope="col">Aksi</th>
                    <th scope="col">Target</th>
                    <th scope="col">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                <tr>
                    <td class="cell-muted">{{ $r['waktu'] ?? '-' }}</td>
                    <td>{{ \App\Support\AdminAkses::label((string) ($r['peran'] ?? '')) }}</td>
                    <td><b class="cell-strong">{{ $r['nama'] ?? '-' }}</b></td>
                    <td>{{ \App\Support\LogAktivitas::labelAksi((string) ($r['aksi'] ?? '')) }}</td>
                    <td class="cell-muted">{{ $r['target'] ?? '-' }}</td>
                    <td class="cell-faint">{{ $r['ip'] ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="cell-empty">
                        @if ($adaFilter)
                            Tidak ada aktivitas yang cocok dengan filter.
                            <br><a href="{{ route('superadmin.aktivitas') }}">Tampilkan semua aktivitas</a>
                        @else
                            Belum ada aktivitas tercatat. Catatan muncul setelah ada pengajuan yang
                            disetujui/ditolak atau masa aktif yang diubah.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($halamanAkhir > 1)
    <div class="panel-body">
        <nav class="pagination-nav" aria-label="Navigasi halaman aktivitas">
            <p class="cell-muted" role="status">
                Menampilkan halaman {{ $halaman }} dari {{ $halamanAkhir }} &middot; {{ $total }} catatan
            </p>
            <div class="table-actions">
                @if ($halaman > 1)
                    <a class="btn btn2 btn-s" rel="prev"
                       href="{{ route('superadmin.aktivitas', array_filter($filter + ['page' => $halaman - 1])) }}">Sebelumnya</a>
                @endif
                @if ($halaman < $halamanAkhir)
                    <a class="btn btn2 btn-s" rel="next"
                       href="{{ route('superadmin.aktivitas', array_filter($filter + ['page' => $halaman + 1])) }}">Berikutnya</a>
                @endif
            </div>
        </nav>
    </div>
    @endif
</div>
@endsection
