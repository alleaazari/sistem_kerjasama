@extends('layouts.admin')
@section('title', 'Inbox Pengajuan')

@section('content')

{{-- BANNER SUKSES PENOLAKAN DENGAN NOTIFIKASI EMAIL & WHATSAPP OTOMATIS --}}
@if(session('reject_done'))
<div style="background:#fef2f2;border:1px solid #fecaca;border-left:5px solid #ef4444;border-radius:10px;padding:18px 20px;margin-bottom:24px;box-shadow:0 4px 12px rgba(239,68,68,0.08);">
    <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:14px;">
        <div style="display:flex;gap:14px;align-items:flex-start;">
            <div style="width:38px;height:38px;border-radius:50%;background:#fee2e2;color:#ef4444;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            </div>
            <div>
                <h4 style="margin:0 0 4px;color:#991b1b;font-size:15px;font-weight:700;">Pengajuan dari {{ session('instansi') }} Telah Ditolak</h4>
                <p style="margin:0 0 8px;font-size:13px;color:#7f1d1d;">
                    Alasan: <i>"{{ session('alasan') }}"</i>
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:16px;font-size:12.5px;color:#475569;margin-top:6px;">
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                        Email Permintaan Maaf terkirim ke: <b>{{ session('email_sent') }}</b>
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:6px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                        WhatsApp Permintaan Maaf dikirim ke: <b>{{ session('wa_sent') }}</b>
                    </span>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:10px;align-items:center;">
            @if(session('wa_url'))
            <a href="{{ session('wa_url') }}" target="_blank" rel="noopener noreferrer" class="btn" style="background:#25d366;border-color:#25d366;color:#ffffff;display:inline-flex;align-items:center;gap:6px;font-size:12.5px;padding:7px 14px;box-shadow:0 2px 8px rgba(37,211,102,0.3);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                Buka Chat WhatsApp Pengirim ↗
            </a>
            @endif

            @if(session('surat_preview'))
            <button type="button" class="btn btn2" onclick="bukaModalSurat()" style="font-size:12.5px;padding:7px 14px;">
                Lihat Salinan Surat Resmi
            </button>
            @endif
        </div>
    </div>
</div>
@elseif(session('ok'))
<div style="background:#dcfce7;border:1px solid #86efac;border-left:5px solid #16a34a;border-radius:8px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="12" fill="#16a34a"/><path d="M7 12.5l3.5 3.5 6-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div>
        <div style="font-weight:700;color:#15803d;font-size:14px;">Aksi Berhasil Diproses!</div>
        <div style="font-size:12px;color:#166534;margin-top:1px;">{{ session('ok') }}</div>
    </div>
</div>
@endif

<div class="scroll">
<table class="data" id="table-inbox">
    <tr>
        <th>Instansi</th>
        <th>Organisasi</th>
        <th>PIC</th>
        <th>Kontak (Email / WA)</th>
        <th>Status</th>
        <th style="width:100px;">Aksi</th>
    </tr>
    @foreach ($pengajuan as $row)
    <tr id="row-inbox-{{ $row['id'] }}">
        <td><b style="color:var(--primary);">{{ $row['instansi'] }}</b></td>
        <td style="font-size:12px;">{{ $row['organisasi'] }}</td>
        <td style="font-size:12px;">{{ $row['pic'] }}</td>
        <td style="font-size:12px;">
            <div>{{ $row['email'] }}</div>
            <div style="color:var(--text-muted);font-size:11px;">{{ $row['telp'] }}</div>
        </td>
        <td>
            @php
                $st = $row['status'];
                $bg = $st === 'Disetujui' ? 'background:#dcfce7;color:#15803d;border:1px solid #86efac;'
                    : ($st === 'Ditolak'   ? 'background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;'
                                           : 'background:#fef3c7;color:#b45309;border:1px solid #fde68a;');
            @endphp
            <span class="status-badge-active" style="{{ $bg }}">● {{ $st }}</span>
        </td>
        <td>
            <button type="button" class="btn btn-s" onclick="bukaModal({{ json_encode($row) }})">Review</button>
        </td>
    </tr>
    @endforeach
</table>
</div>

{{-- MODAL DETAIL & AKSI --}}
<div id="ov" class="lightbox" onclick="if(event.target===this) modalClose()">
<div class="lightbox-content" style="max-width:640px;width:100%;padding:0;">

    {{-- Header modal --}}
    <div style="background:var(--primary);color:#fff;padding:16px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">
        <b style="font-size:15px;">Detail Pengajuan Kemitraan</b>
        <button onclick="modalClose()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:18px;line-height:1;">✕</button>
    </div>

    <div style="padding:22px;">
        {{-- Info detail --}}
        <table class="info" style="margin-bottom:18px;">
            <tr><td style="font-weight:600;">Instansi</td><td id="d-instansi"></td></tr>
            <tr><td style="font-weight:600;">Organisasi</td><td id="d-organisasi"></td></tr>
            <tr><td style="font-weight:600;">PIC</td><td id="d-pic"></td></tr>
            <tr><td style="font-weight:600;">Email PIC</td><td id="d-email" style="color:var(--accent);font-weight:600;"></td></tr>
            <tr><td style="font-weight:600;">No. WhatsApp</td><td id="d-telp" style="font-weight:600;"></td></tr>
            <tr><td style="font-weight:600;">Tujuan Usulan</td><td id="d-tujuan" style="font-size:12px;line-height:1.5;"></td></tr>
        </table>

        {{-- Pilih Aksi --}}
        <div id="m-pilih">
            <div style="background:#f8fafc;border:1px solid var(--border-color);border-radius:8px;padding:14px;margin-bottom:16px;font-size:13px;color:var(--text-muted);line-height:1.5;">
                <b style="color:var(--primary);">Alur Notifikasi Otomatis:</b><br>
                • <b>Tolak Pengajuan:</b> Sistem akan <b>otomatis mengirimkan Surat Permintaan Maaf Resmi</b> ke Email PIC dan pesan pemberitahuan ke WhatsApp PIC.<br>
                • <b>Approve:</b> Status akan berubah menjadi Disetujui, dan pengiriman username &amp; password akun mitra akan dilakukan oleh Admin secara terpisah.
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn2" onclick="modalShow('reject')"
                        style="border-color:#ef4444;color:#ef4444;">
                    ✕ Tolak Pengajuan
                </button>
                <button type="button" class="btn" onclick="modalShow('approve')"
                        style="background:linear-gradient(90deg,#15803d,#16a34a);">
                    ✓ Approve Pengajuan
                </button>
            </div>
        </div>

        {{-- Form REJECT DENGAN TEMPLATE PERMINTAAN MAAF OTOMATIS --}}
        <div id="m-reject" style="display:none;">
            <form id="f-reject" method="POST" action="">
                @csrf
                <div class="row">
                    <label style="font-weight:600;color:#dc2626;display:flex;justify-content:space-between;align-items:center;">
                        <span>Alasan Penolakan (Masuk ke Surat Permintaan Maaf) <span style="color:#dc2626;">*</span></span>
                    </label>

                    {{-- PILIHAN CEPAT TEMPLATE KATA PERMINTAAN MAAF --}}
                    <div style="margin-bottom:8px;font-size:11.5px;color:var(--text-muted);">
                        <span>Pilih Template Alasan Cepat:</span>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
                            <button type="button" class="btn btn2 btn-s" onclick="isiAlasan('Kuota kemitraan untuk pilar program ini pada tahun anggaran berjalan telah terpenuhi.')" style="font-size:11px;padding:3px 8px;">
                                Kuota Program Penuh
                            </button>
                            <button type="button" class="btn btn2 btn-s" onclick="isiAlasan('Dokumen proposal usulan belum memenuhi standar kelengkapan administrasi dan legalitas yang dipersyaratkan.')" style="font-size:11px;padding:3px 8px;">
                                Berkas Belum Lengkap
                            </button>
                            <button type="button" class="btn btn2 btn-s" onclick="isiAlasan('Fokus program yang diajukan belum selaras dengan skala prioritas transformasi digital nasional tahun ini.')" style="font-size:11px;padding:3px 8px;">
                                Belum Sesuai Prioritas Nasional
                            </button>
                        </div>
                    </div>

                    <textarea id="reject-feedback" name="feedback" rows="3" required placeholder="Tuliskan catatan/alasan penolakan di sini..." style="border-color:#fca5a5;font-size:13px;"></textarea>
                </div>

                {{-- INFORMASI PENGIRIMAN OTOMATIS --}}
                <div style="background:#fff1f2;border:1px solid #fecdd3;border-radius:8px;padding:12px;margin:12px 0 16px;font-size:12px;color:#9f1239;line-height:1.5;">
                    <div style="font-weight:700;margin-bottom:4px;">Notifikasi Penolakan Otomatis Akan Dikirim Ke:</div>
                    • <b>Email:</b> <span id="r-email" style="text-decoration:underline;"></span> (Surat Resmi Permintaan Maaf KEMKOMDIGI)<br>
                    • <b>WhatsApp:</b> <span id="r-telp" style="text-decoration:underline;"></span> (Format Pesan Ramah &amp; Tautan Penjelasan)
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn2" onclick="modalShow('pilih')">Kembali</button>
                    <button type="submit" class="btn" style="background:#ef4444;border-color:#ef4444;color:#fff;">
                        Kirim Penolakan (Otomatis ke Email &amp; WhatsApp)
                    </button>
                </div>
            </form>
        </div>

        {{-- Form APPROVE --}}
        <div id="m-approve" style="display:none;">
            <form id="f-approve" method="POST" action="">
                @csrf
                <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:14px;margin-bottom:14px;">
                    <p style="font-size:13px;color:#166534;margin:0;line-height:1.6;">
                        Konfirmasi persetujuan kemitraan untuk <b><span id="confirm-instansi"></span></b>?<br>
                        • Status pengajuan akan berubah menjadi <b>Disetujui</b>.<br>
                        • Email dan kata sandi (kredensial login) akan dikirimkan oleh Admin secara terpisah ke email: <b><span id="confirm-email"></span></b>.
                    </p>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" class="btn btn2" onclick="modalShow('pilih')">Kembali</button>
                    <button type="submit" class="btn" style="background:linear-gradient(90deg,#15803d,#16a34a);">Konfirmasi Setujui Pengajuan</button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

{{-- MODAL PREVIEW SALINAN SURAT RESMI PERMINTAAN MAAF --}}
@if(session('surat_preview'))
<div id="modal-surat" class="lightbox" onclick="if(event.target===this) tutupModalSurat()">
    <div class="lightbox-content" style="max-width:720px;width:100%;padding:0;">
        <div style="background:var(--primary);color:#fff;padding:14px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">
            <b style="font-size:14px;">Salinan Surat Resmi Permintaan Maaf KEMKOMDIGI</b>
            <button type="button" onclick="tutupModalSurat()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:18px;">✕</button>
        </div>
        <div style="padding:20px;max-height:75vh;overflow-y:auto;background:#ffffff;">
            <pre style="white-space:pre-wrap;font-family:'Inter', Arial, sans-serif;font-size:12.5px;color:#334155;line-height:1.6;background:#f8fafc;padding:16px;border-radius:8px;border:1px solid var(--border-color);">{{ session('surat_preview') }}</pre>
            <div style="display:flex;justify-content:flex-end;margin-top:14px;">
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

    // Set action URL untuk approve dan reject (pakai nama route agar ikut prefix rahasia)
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
    document.getElementById('m-pilih').style.display   = (m === 'pilih')   ? 'block' : 'none';
    document.getElementById('m-reject').style.display  = (m === 'reject')  ? 'block' : 'none';
    document.getElementById('m-approve').style.display = (m === 'approve') ? 'block' : 'none';
}

function modalClose() {
    document.getElementById('ov').classList.remove('active');
}
</script>

@endsection
