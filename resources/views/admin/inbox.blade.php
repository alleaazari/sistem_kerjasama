@extends('layouts.admin')
@section('title', 'Inbox Pengajuan')

@section('content')

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
            <div class="note-box">
                <p><b>Alur tindakan:</b></p>
                <ul>
                    <li><b>Tolak:</b> sistem menyiapkan surat pemberitahuan resmi ke email PIC dan pesan ke WhatsApp PIC.</li>
                    <li><b>Setujui:</b> status berubah menjadi Disetujui. Kredensial akun mitra dikirim terpisah oleh Admin.</li>
                </ul>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn2" onclick="modalShow('reject')">Tolak Pengajuan</button>
                <button type="button" class="btn" onclick="modalShow('approve')">Setujui Pengajuan</button>
            </div>
        </div>

        {{-- Form REJECT --}}
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
                    <textarea id="reject-feedback" name="feedback" rows="3" required maxlength="1000" placeholder="Tuliskan alasan penolakan di sini"></textarea>
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

        {{-- Form APPROVE --}}
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

    document.getElementById('confirm-instansi').textContent = item.instansi || '-';
    document.getElementById('confirm-email').textContent = item.email || '-';
    document.getElementById('r-email').textContent = item.email || '-';
    document.getElementById('r-telp').textContent = item.telp || '-';

    var approveTpl = "{{ route('admin.inbox.approve', ['id' => '__ID__']) }}";
    var rejectTpl = "{{ route('admin.inbox.reject', ['id' => '__ID__']) }}";
    document.getElementById('f-approve').action = approveTpl.replace('__ID__', item.id);
    document.getElementById('f-reject').action  = rejectTpl.replace('__ID__', item.id);

    modalShow('pilih');
    document.getElementById('ov').classList.add('active');
}

function isiAlasan(teks) {
    document.getElementById('reject-feedback').value = teks;
}

function modalShow(m) {
    document.getElementById('m-pilih').hidden   = (m !== 'pilih');
    document.getElementById('m-reject').hidden  = (m !== 'reject');
    document.getElementById('m-approve').hidden = (m !== 'approve');
}

function modalClose() {
    document.getElementById('ov').classList.remove('active');
}
</script>

@endsection
