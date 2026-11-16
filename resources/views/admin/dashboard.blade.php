@extends('layouts.admin')
@section('title', ! empty($ringkas) ? 'Admin Kelola Komdigi' : 'Dashboard')

@section('content')

@if (! empty($ringkas))
{{-- Dashboard ringkas pengelola konten: hanya angka konten, tanpa data kerja sama. --}}
<div class="panel">
    <div class="panel-body">
        <dl class="detail-grid">
            <div>
                <dt>Artikel terbit</dt>
                <dd>{{ $jumlahArtikel }}</dd>
            </div>
            <div>
                <dt>Foto galeri</dt>
                <dd>{{ $jumlahGaleri }}</dd>
            </div>
            <div>
                <dt>Profil pejabat</dt>
                <dd>{{ $jumlahPetinggi }}</dd>
            </div>
            <div>
                <dt>Visi &amp; misi</dt>
                <dd>{{ $visiTerisi ? 'Sudah diisi' : 'Belum diisi' }}</dd>
            </div>
        </dl>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2 class="panel-title">Mulai mengelola konten</h2>
        <p class="panel-sub">Perubahan langsung tampil di portal publik setelah disimpan.</p>
    </div>
    <div class="panel-body">
        <div class="table-actions">
            <a class="btn btn-s" href="{{ route('admin.tentang') }}">Ubah profil &amp; visi misi</a>
            <a class="btn btn2 btn-s" href="{{ route('admin.artikel') }}">Kelola artikel</a>
            <a class="btn btn2 btn-s" href="{{ route('admin.galeri') }}">Kelola galeri</a>
        </div>
    </div>
</div>
@else
{{-- Bagian data kerja sama hanya dirender untuk peran kerja sama; view ringkas
     tidak menerima variabel-variabel ini sama sekali. --}}
{{-- Ringkasan singkat: dua angka saja, tanpa kartu berjajar. --}}
<div class="panel">
    <div class="panel-body">
        <dl class="detail-grid">
            <div>
                <dt>Permohonan perpanjangan menunggu</dt>
                <dd>{{ count($menunggu) }}</dd>
            </div>
            <div>
                <dt>Pengajuan kerja sama baru menunggu</dt>
                <dd>{{ $jumlahPengajuanBaru }}</dd>
            </div>
        </dl>
    </div>
</div>

<div class="panel">
    <div class="panel-head">
        <h2 class="panel-title">Permohonan Perpanjangan Masa Aktif</h2>
        <p class="panel-sub">Tinjau permohonan mitra, lalu setujui dengan durasi yang Anda tetapkan atau tolak beserta alasannya.</p>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="table-perpanjangan">
            <caption class="sr-only">Daftar permohonan perpanjangan yang menunggu keputusan</caption>
            <thead>
                <tr>
                    <th scope="col">Instansi Mitra</th>
                    <th scope="col">Diajukan</th>
                    <th scope="col">Usulan Mitra</th>
                    <th scope="col">Catatan Mitra</th>
                    <th scope="col" class="col-aksi">Keputusan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($menunggu as $row)
                <tr>
                    <td><b class="cell-strong">{{ $row['instansi'] }}</b></td>
                    <td class="cell-muted">{{ \App\Support\StatusKerjasama::parseTanggal($row['diajukan_pada'])?->translatedFormat('d F Y') ?? '—' }}</td>
                    <td class="cell-muted">{{ $row['durasi'] }} tahun</td>
                    <td class="cell-muted">{{ $row['catatan'] !== '' ? $row['catatan'] : '—' }}</td>
                    <td>
                        <div class="table-actions">
                            <button type="button" class="btn btn-s"
                                    onclick="bukaSetujui({{ json_encode($row) }})">Setujui</button>
                            <button type="button" class="btn btn2 btn-s"
                                    onclick="bukaTolak({{ json_encode($row) }})">Tolak</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="cell-empty">Tidak ada permohonan perpanjangan yang menunggu.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if (count($riwayat) > 0)
<div class="panel">
    <div class="panel-head">
        <h2 class="panel-title">Keputusan Terakhir</h2>
        <p class="panel-sub">Sepuluh keputusan perpanjangan terakhir.</p>
    </div>

    <div class="table-scroll">
        <table class="data-table">
            <caption class="sr-only">Riwayat keputusan permohonan perpanjangan</caption>
            <thead>
                <tr>
                    <th scope="col">Instansi Mitra</th>
                    <th scope="col">Keputusan</th>
                    <th scope="col">Masa Aktif Baru</th>
                    <th scope="col">Alasan Penolakan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($riwayat as $row)
                <tr>
                    <td><b class="cell-strong">{{ $row['instansi'] }}</b></td>
                    <td>
                        @php
                            $disetujui = ($row['status'] ?? '') === \App\Support\PermohonanPerpanjangan::DISETUJUI;
                        @endphp
                        <span class="badge {{ $disetujui ? 'badge-success' : 'badge-danger' }}">{{ $row['status'] }}</span>
                    </td>
                    <td class="cell-muted">
                        @if ($disetujui && ! empty($row['mulai_baru']))
                            {{ \App\Support\StatusKerjasama::parseTanggal($row['mulai_baru'])?->translatedFormat('d F Y') }}
                            s/d
                            {{ \App\Support\StatusKerjasama::parseTanggal($row['selesai_baru'])?->translatedFormat('d F Y') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="cell-muted">{{ $row['alasan'] !== '' ? $row['alasan'] : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- MODAL SETUJUI --}}
<div id="modal-setujui" class="lightbox" onclick="if(event.target===this) tutupModal('modal-setujui')">
    <div class="lightbox-content modal-review">
        <div class="modal-head">
            <h2 class="modal-head-title">Setujui Perpanjangan</h2>
            <button type="button" class="modal-close" onclick="tutupModal('modal-setujui')" aria-label="Tutup dialog">&times;</button>
        </div>
        <div class="modal-body">
            <dl class="detail-grid">
                <div class="detail-full"><dt>Instansi</dt><dd id="s-instansi"></dd></div>
                <div><dt>Usulan mitra</dt><dd id="s-usulan"></dd></div>
                <div><dt>Tanggal berakhir saat ini</dt><dd id="s-selesai-lama"></dd></div>
            </dl>

            <form id="f-setujui" method="POST" action="">
                @csrf
                <div class="row">
                    <label for="s-durasi">Durasi perpanjangan <span class="req">*</span></label>
                    <p class="field-help field-help-top">Periode baru dihitung dari tanggal berakhir saat ini, atau dari hari ini bila masa aktif sudah lewat.</p>
                    <select id="s-durasi" name="durasi" required>
                        <option value="1">1 tahun</option>
                        <option value="2">2 tahun</option>
                        <option value="3">3 tahun</option>
                    </select>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn2" onclick="tutupModal('modal-setujui')">Batal</button>
                    <button type="submit" class="btn btn-success">Setujui dan Perpanjang</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL TOLAK --}}
<div id="modal-tolak" class="lightbox" onclick="if(event.target===this) tutupModal('modal-tolak')">
    <div class="lightbox-content modal-review">
        <div class="modal-head">
            <h2 class="modal-head-title">Tolak Permohonan Perpanjangan</h2>
            <button type="button" class="modal-close" onclick="tutupModal('modal-tolak')" aria-label="Tutup dialog">&times;</button>
        </div>
        <div class="modal-body">
            <dl class="detail-grid">
                <div class="detail-full"><dt>Instansi</dt><dd id="t-instansi"></dd></div>
            </dl>

            <form id="f-tolak" method="POST" action="">
                @csrf
                <div class="row">
                    <label for="t-alasan">Alasan penolakan <span class="req">*</span></label>
                    <p class="field-help field-help-top">Alasan ini tampil pada halaman Status mitra.</p>
                    <textarea id="t-alasan" name="alasan" rows="3" required minlength="10" maxlength="1000"
                              placeholder="Tuliskan alasan penolakan di sini"></textarea>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn2" onclick="tutupModal('modal-tolak')">Batal</button>
                    <button type="submit" class="btn btn-danger">Kirim Penolakan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var setujuiTpl = "{{ route('admin.dashboard.setujui', ['id' => '__ID__']) }}";
var tolakTpl = "{{ route('admin.dashboard.tolak', ['id' => '__ID__']) }}";

function bukaSetujui(item) {
    document.getElementById('s-instansi').textContent = item.instansi || '-';
    document.getElementById('s-usulan').textContent = (item.durasi || 1) + ' tahun';
    document.getElementById('s-selesai-lama').textContent = item.selesai_lama_tampil || '-';
    document.getElementById('s-durasi').value = String(item.durasi || 1);
    document.getElementById('f-setujui').action = setujuiTpl.replace('__ID__', encodeURIComponent(item.id));
    document.getElementById('modal-setujui').classList.add('active');
}

function bukaTolak(item) {
    document.getElementById('t-instansi').textContent = item.instansi || '-';
    document.getElementById('f-tolak').action = tolakTpl.replace('__ID__', encodeURIComponent(item.id));
    document.getElementById('modal-tolak').classList.add('active');
}

function tutupModal(id) {
    document.getElementById(id).classList.remove('active');
}
</script>
@endif

@endsection
