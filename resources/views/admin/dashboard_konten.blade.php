@extends('layouts.admin')
@section('title', 'Data Mitra Diterima')

@section('content')
    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Mitra Kerja Sama yang Diterima</h2>
            <p class="panel-sub">Daftar instansi mitra yang pengajuannya sudah disetujui. Halaman ini baca-saja: tidak ada tombol keputusan dan tidak memuat kontak PIC.</p>
        </div>

        <div class="table-scroll">
            <table class="data-table" id="table-mitra-diterima">
                <caption class="sr-only">Daftar mitra yang sudah disetujui beserta bidang, status, dan masa aktif kerja sama</caption>
                <thead>
                    <tr>
                        <th scope="col">Nama Instansi Mitra</th>
                        <th scope="col">Bidang Kerja Sama</th>
                        <th scope="col">Status</th>
                        <th scope="col">Masa Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mitraDiterima as $m)
                        <tr>
                            <td><b class="cell-strong">{{ $m['instansi'] }}</b></td>
                            <td class="cell-muted">{{ $m['bidang'] !== '' ? $m['bidang'] : '—' }}</td>
                            <td><span class="badge badge-success">{{ $m['status'] }}</span></td>
                            <td class="cell-muted">
                                @if ($m['mulai'] && $m['selesai'])
                                    {{ $m['mulai']->translatedFormat('d M Y') }} &ndash; {{ $m['selesai']->translatedFormat('d M Y') }}
                                    <span class="cell-faint cell-sub">
                                        @if ($m['kelas_masa'] !== '')
                                            <span class="{{ $m['kelas_masa'] }}">{{ $m['masa'] }}</span>
                                            {{ $m['keterangan'] }}
                                        @else
                                            {{ $m['masa'] }}
                                        @endif
                                    </span>
                                @else
                                    <span class="cell-faint">{{ $m['keterangan'] }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="cell-empty">Belum ada mitra yang disetujui.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
