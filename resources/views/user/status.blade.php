@extends('layouts.user')
@section('title', 'Status & Masa Aktif Kerja Sama')

@section('content')
<div class="box">
    <h2>Detail Kemitraan &amp; Masa Aktif Komdigi</h2>
    <div class="in">
        <table class="info">
            <tr>
                <td>Nama Instansi</td>
                <td><b>{{ $mitra['instansi'] }}</b></td>
            </tr>
            <tr>
                <td>Unit Organisasi</td>
                <td>{{ $mitra['organisasi'] }}</td>
            </tr>
            <tr>
                <td>Penanggung Jawab (PIC)</td>
                <td>{{ $mitra['pic'] }}</td>
            </tr>
            <tr>
                <td>Email Resmi</td>
                <td>{{ $mitra['email'] }}</td>
            </tr>
            <tr>
                <td>Telepon / WhatsApp</td>
                <td>{{ $mitra['telp'] }}</td>
            </tr>
            <tr>
                <td>Alamat Instansi</td>
                <td>{{ $mitra['alamat'] }}</td>
            </tr>
            <tr>
                <td>Status Kerja Sama</td>
                <td><span class="status-badge-active">● {{ $mitra['status'] }}</span></td>
            </tr>
            <tr>
                <td>Periode Kemitraan</td>
                <td><b>{{ $mitra['mulai'] }}</b> s/d <b>{{ $mitra['selesai'] }}</b></td>
            </tr>
        </table>

        <div style="margin-top:16px;display:flex;gap:10px;">
            <a href="{{ route('mitra.profile') }}" class="btn">Edit Profil Instansi</a>
            <a href="{{ route('mitra.draf') }}" class="btn btn2">Generate Draf Proposal AI</a>
        </div>
    </div>
</div>

<div class="box" style="margin-top:20px;">
    <h2>Dokumen &amp; Program Kerja Sama Aktif</h2>
    <div class="in">
        <table class="data">
            <thead>
                <tr>
                    <th>Nama Program / MoU</th>
                    <th>Nomor Dokumen</th>
                    <th>Tanggal Penandatanganan</th>
                    <th>Status Dokumen</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Kerja Sama Pengembangan Literasi Digital &amp; SPBE</b></td>
                    <td><code>045/KS/KOMDIGI/2026</code></td>
                    <td>12 Oktober 2026</td>
                    <td><span style="color:#15803d;font-weight:600;">Berlaku</span></td>
                    <td><a href="{{ route('mitra.draf') }}" class="btn btn2 btn-s">Lihat Dokumen AI</a></td>
                </tr>
                <tr>
                    <td><b>Fasilitasi Uji Kompetensi SKKNI Digitalent</b></td>
                    <td><code>088/KS/BBLSDM/2026</code></td>
                    <td>15 November 2026</td>
                    <td><span style="color:#b45309;font-weight:600;">Draf Proses</span></td>
                    <td><a href="{{ route('mitra.draf') }}" class="btn btn2 btn-s">Edit Draf AI</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
