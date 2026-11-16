@extends('layouts.admin')
@section('title', 'Inbox Pengajuan')

@section('content')

@php
    // Server yang memutuskan (route dijaga middleware); ini hanya menyesuaikan
    // tampilan agar peran tanpa hak tidak melihat tombol yang pasti ditolak.
    $peranAktif = \App\Support\AdminAkses::peranSesi();
    $bolehSetujui = \App\Support\AdminAkses::boleh((string) $peranAktif, 'inbox.approve');
    $bolehTolak = \App\Support\AdminAkses::boleh((string) $peranAktif, 'inbox.reject');
@endphp

{{-- Notifikasi hasil tindakan --}}
@if(session('reject_done'))
<div class="alert-box alert-box-danger">
    <div class="alert-box-head">
        <h2 class="alert-box-title">Pengajuan dari {{ session('instansi') }} Ditolak</h2>
        <p class="alert-box-text">Alasan: <em>{{ session('alasan') }}</em></p>
        <p class="alert-box-meta">
            Email pemberitahuan diproses ke <b>{{ session('email_sent') }}</b> &middot;
            WhatsApp diproses ke <b>{{ session('wa_sent') }}</b>
        </p>
    </div>
    <div class="alert-box-actions">
        @if(session('wa_url'))
        <a href="{{ session('wa_url') }}" target="_blank" rel="noopener noreferrer" class="btn btn2">Buka Chat WhatsApp</a>
        @endif
        @if(session('surat_preview'))
        <button type="button" class="btn btn2" onclick="bukaModalSurat()">Lihat Salinan Surat</button>
        @endif
    </div>
</div>
@elseif(session('ok'))
<div class="note-box note-box-success" role="status">{{ session('ok') }}</div>
@endif

<div class="panel">
    <div class="panel-head">
        <h2 class="panel-title">Daftar Pengajuan Kerja Sama</h2>
        <p class="panel-sub">Tinjau data instansi, lalu setujui atau tolak pengajuan.</p>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="table-inbox">
            <caption class="sr-only">Daftar pengajuan kerja sama yang masuk</caption>
            <thead>
                <tr>
                    <th scope="col">Instansi</th>
                    <th scope="col">Organisasi</th>
                    <th scope="col">PIC</th>
                    <th scope="col">Kontak</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pengajuan as $row)
                <tr id="row-inbox-{{ $row['id'] }}">
                    <td><b class="cell-strong">{{ $row['instansi'] }}</b></td>
                    <td class="cell-muted">{{ $row['organisasi'] }}</td>
                    <td class="cell-muted">{{ $row['pic'] }}</td>
                    <td class="cell-muted">
                        <div>{{ $row['email'] }}</div>
                        <div class="cell-faint">{{ $row['telp'] }}</div>
                    </td>
                    <td>
                        @php
                            $st = $row['status'];
                            $cls = $st === 'Disetujui' ? 'badge badge-success'
                                 : ($st === 'Ditolak' ? 'badge badge-danger' : 'badge badge-warning');
                        @endphp
                        <span class="{{ $cls }}">{{ $st }}</span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-s" onclick="bukaModal({{ json_encode($row) }})">Review</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="cell-empty">Belum ada pengajuan yang masuk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MODAL DETAIL & AKSI --}}
<div id="ov" class="lightbox" onclick="if(event.target===this) modalClose()">
<div class="lightbox-content modal-review">

    <div class="modal-head">
        <h2 class="modal-head-title">Detail Pengajuan Kemitraan</h2>
        <button type="button" onclick="modalClose()" class="modal-close" aria-label="Tutup dialog">&times;</button>
    </div>

    <div class="modal-body">
        <dl class="detail-grid">
            <div><dt>Instansi</dt><dd id="d-instansi"></dd></div>
            <div><dt>Organisasi</dt><dd id="d-organisasi"></dd></div>
            <div><dt>PIC</dt><dd id="d-pic"></dd></div>
            <div><dt>Email PIC</dt><dd id="d-email"></dd></div>
            <div><dt>No. WhatsApp</dt><dd id="d-telp"></dd></div>
            <div class="detail-full"><dt>Tujuan Usulan</dt><dd id="d-tujuan"></dd></div>
        </dl>

        {{-- Pilih Aksi --}}
        <div id="m-pilih">
            @if ($bolehSetujui || $bolehTolak)
            <div class="note-box">
                <p><b>Alur tindakan:</b></p>
                <ul>
                    @if ($bolehTolak)
                    <li><b>Tolak:</b> sistem menyiapkan surat pemberitahuan resmi ke email PIC dan pesan ke WhatsApp PIC.</li>
                    @endif
                    @if ($bolehSetujui)
                    <li><b>Setujui:</b> status berubah menjadi Disetujui. Kredensial akun mitra dikirim terpisah oleh Admin.</li>
                    @endif
                </ul>
            </div>
            <div class="modal-actions">
                @if ($bolehTolak)
                    <button type="button" class="btn btn2" onclick="modalShow('reject')">Tolak Pengajuan</button>
                @endif
                @if ($bolehSetujui)
                    <button type="button" class="btn" onclick="modalShow('approve')">Setujui Pengajuan</button>
                @endif
            </div>
            @else
            <div class="note-box">
                <p>
                    Keputusan atas pengajuan (setujui/tolak) hanya dapat dilakukan oleh
                    <b>Super Administrator</b>. Anda dapat memeriksa kelengkapan data di sini.
                </p>
            </div>
            @endif

            <div class="modal-actions">
                <button type="button" class="btn btn2" onclick="modalClose()">Tutup</button>
            </div>
        </div>

        {{-- Form REJECT --}}
        @if ($bolehTolak)
        <div id="m-reject" hidden>
            <form id="f-reject" method="POST" action="">
                @csrf
                <div class="row">
                    <label for="reject-feedback">Alasan penolakan <span aria-hidden="true">*</span></label>
                    <p class="field-help field-help-top">Alasan ini dimuat pada surat pemberitahuan resmi.</p>
                    <div class="chip-row">
                        <button type="button" class="btn btn2 btn-s" onclick="isiAlasan('Kuota kemitraan untuk pilar program ini pada tahun anggaran berjalan telah terpenuhi.')">Kuota penuh</button>
                        <button type="button" class="btn btn2 btn-s" onclick="isiAlasan('Dokumen proposal usulan belum memenuhi standar kelengkapan administrasi dan legalitas yang dipersyaratkan.')">Berkas belum lengkap</button>
                        <button type="button" class="btn btn2 btn-s" onclick="isiAlasan('Fokus program yang diajukan belum selaras dengan skala prioritas transformasi digital nasional tahun ini.')">Belum sesuai prioritas</button>
                    </div>
                    <textarea id="reject-feedback" name="feedback" rows="3" required minlength="5" maxlength="1000" placeholder="Tuliskan alasan penolakan di sini"></textarea>
                </div>

                <div class="note-box note-box-danger">
                    <p><b>Pemberitahuan akan diproses ke:</b></p>
                    <ul>
                        <li>Email: <span id="r-email"></span></li>
                        <li>WhatsApp: <span id="r-telp"></span></li>
                    </ul>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn2" onclick="modalShow('pilih')">Kembali</button>
                    <button type="submit" class="btn btn-danger">Kirim Penolakan</button>
                </div>
            </form>
        </div>
        @endif

        {{-- Form APPROVE --}}
        @if ($bolehSetujui)
        <div id="m-approve" hidden>
            <form id="f-approve" method="POST" action="">
                @csrf
                <div class="note-box note-box-success">
                    <p>Setujui pengajuan dari <b><span id="confirm-instansi"></span></b>?</p>
                    <ul>
                        <li>Status pengajuan berubah menjadi <b>Disetujui</b>.</li>
                        <li>Kredensial akun dikirim terpisah ke <b><span id="confirm-email"></span></b>.</li>
                    </ul>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn2" onclick="modalShow('pilih')">Kembali</button>
                    <button type="submit" class="btn btn-success">Konfirmasi Persetujuan</button>
                </div>
            </form>
        </div>
        @endif
    </div>

</div>
</div>

{{-- MODAL PREVIEW SALINAN SURAT --}}
@if(session('surat_preview'))
<div id="modal-surat" class="lightbox" onclick="if(event.target===this) tutupModalSurat()">
    <div class="lightbox-content modal-review">
        <div class="modal-head">
            <h2 class="modal-head-title">Salinan Surat Pemberitahuan</h2>
            <button type="button" onclick="tutupModalSurat()" class="modal-close" aria-label="Tutup dialog">&times;</button>
        </div>
        <div class="modal-body">
            <pre class="mail-preview">{{ session('surat_preview') }}</pre>
            <div class="modal-actions">
                <button type="button" class="btn" onclick="tutupModalSurat()">Tutup Pratinjau</button>
            </div>
        </div>
    </div>
</div>
@endif

<script>
var currentItem = null;

function bukaModalSurat() {
    var el = document.getElementById('modal-surat');
    if (el) el.classList.add('active');
}

function tutupModalSurat() {
    var el = document.getElementById('modal-surat');
    if (el) el.classList.remove('active');
}

function bukaModal(item) {
    currentItem = item;
    ['instansi','organisasi','pic','email','telp','tujuan'].forEach(function(k) {
        var el = document.getElementById('d-' + k);
        if (el) el.textContent = item[k] || '-';
    });

    // Elemen di bawah hanya ada bila peran berhak; guard agar peran lain aman.
    var isi = function (id, teks) {
        var el = document.getElementById(id);
        if (el) el.textContent = teks;
    };
    isi('confirm-instansi', item.instansi || '-');
    isi('confirm-email', item.email || '-');
    isi('r-email', item.email || '-');
    isi('r-telp', item.telp || '-');

    var approveForm = document.getElementById('f-approve');
    var rejectForm = document.getElementById('f-reject');

    @if ($bolehSetujui)
    if (approveForm) {
        approveForm.action = "{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.inbox.approve', ['id' => '__ID__']) }}".replace('__ID__', item.id);
    }
    @endif

    @if ($bolehTolak)
    if (rejectForm) {
        rejectForm.action = "{{ route(\App\Support\AdminAkses::awalanRouteSesi().'.inbox.reject', ['id' => '__ID__']) }}".replace('__ID__', item.id);
    }
    @endif

    modalShow('pilih');
    document.getElementById('ov').classList.add('active');
}

function isiAlasan(teks) {
    var el = document.getElementById('reject-feedback');
    if (el) el.value = teks;
}

function modalShow(m) {
    ['pilih', 'reject', 'approve'].forEach(function (kunci) {
        var el = document.getElementById('m-' + kunci);
        if (el) el.hidden = (m !== kunci);
    });
}

function modalClose() {
    document.getElementById('ov').classList.remove('active');
}
</script>

@endsection
