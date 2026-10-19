@extends('layouts.admin')
@section('title', 'Kelola Draf Proposal AI & Masa Aktif Mitra')

@section('content')
<div class="box">
    <h2>Daftar Draf Proposal AI &amp; Masa Aktif Mitra Terdaftar</h2>
    <div class="in">
        <p style="color:var(--text-muted);margin-bottom:14px;">
            Halaman ini menampilkan seluruh draf proposal yang dibuat oleh mitra menggunakan AI Generator serta pengesahan dan penyetelan masa aktif kemitraan.
        </p>

        <div class="scroll">
            <table class="data" id="table-mitra">
                <thead>
                    <tr>
                        <th>Instansi Mitra</th>
                        <th>Judul Kemitraan (AI Proposal)</th>
                        <th>Tanggal Pembuatan</th>
                        <th>Masa Aktif Kerjasama</th>
                        <th>Status</th>
                        <th>Aksi Admin</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $r)
                    <tr id="row-mitra-{{ $r['id'] }}">
                        <td><b>{{ $r['instansi'] }}</b></td>
                        <td>{{ $r['judul_ks'] }}</td>
                        <td>{{ $r['tgl_buat'] }}</td>
                        <td><code id="masa-aktif-{{ $r['id'] }}">{{ $r['masa_aktif'] }}</code></td>
                        <td><span class="status-badge-active" style="background:#dcfce7;color:#15803d;border:1px solid #86efac;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600;">● {{ $r['status'] }}</span></td>
                        <td>
                            <button type="button" class="btn btn2 btn-s" onclick="alert('Draf proposal AI mitra {{ $r['instansi'] }} siap diunduh!')">Unduh Draf AI</button>
                            <button type="button" class="btn btn-s" onclick="openExtendModal({{ $r['id'] }}, '{{ $r['instansi'] }}', '{{ $r['masa_aktif'] }}')">Perpanjang Masa Aktif</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL INTERAKTIF PERPANJANG MASA AKTIF MITRA -->
<div id="modal-extend" class="lightbox">
    <div class="lightbox-content" style="max-width:540px;width:100%;padding:24px;">
        <button class="lightbox-close" onclick="closeExtendModal()" aria-label="Tutup">&times;</button>
        <h3 style="font-size:16px;color:var(--primary);margin-bottom:6px;">Form Penyetelan Masa Aktif Kemitraan</h3>
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">Setel periode tanggal mulai dan tanggal berakhirnya kerja sama untuk instansi mitra:</p>

        <div style="background:#f8fafc;padding:12px;border:1px solid var(--border-color);border-radius:4px;margin-bottom:16px;">
            <div style="font-size:12px;color:var(--text-muted);">Instansi Mitra:</div>
            <div style="font-size:15px;font-weight:700;color:var(--primary);" id="extend-instansi-name"></div>
        </div>

        <form id="form-extend" onsubmit="saveExtension(event)">
            <input type="hidden" id="extend-row-id">
            
            <div class="row">
                <label for="ext-start">Tanggal Mulai Baru</label>
                <input type="date" id="ext-start" required value="2026-10-12">
            </div>

            <div class="row">
                <label for="ext-end">Tanggal Berakhir Baru (Masa Aktif)</label>
                <input type="date" id="ext-end" required value="2028-10-12">
            </div>

            <div class="row">
                <label>Pilihan Durasi Cepat:</label>
                <div style="display:flex;gap:8px;margin-top:4px;">
                    <button type="button" class="btn btn2 btn-s" onclick="setPresetDuration(1)">+1 Tahun</button>
                    <button type="button" class="btn btn2 btn-s" onclick="setPresetDuration(2)">+2 Tahun</button>
                    <button type="button" class="btn btn2 btn-s" onclick="setPresetDuration(3)">+3 Tahun</button>
                    <button type="button" class="btn btn2 btn-s" onclick="setPresetDuration(5)">+5 Tahun</button>
                </div>
            </div>

            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn2" onclick="closeExtendModal()">Batal</button>
                <button type="submit" class="btn">Simpan Masa Aktif Baru</button>
            </div>
        </form>
    </div>
</div>

<script>
var currentEditId = null;

function openExtendModal(id, instansi, currentPeriod) {
    currentEditId = id;
    document.getElementById('extend-row-id').value = id;
    document.getElementById('extend-instansi-name').textContent = instansi;
    document.getElementById('modal-extend').classList.add('active');
}

function closeExtendModal() {
    document.getElementById('modal-extend').classList.remove('active');
}

function setPresetDuration(years) {
    var startDateInput = document.getElementById('ext-start');
    var endDateInput = document.getElementById('ext-end');
    
    var startDate = new Date(startDateInput.value || Date.now());
    var endDate = new Date(startDate);
    endDate.setFullYear(endDate.getFullYear() + years);
    
    endDateInput.value = endDate.toISOString().split('T')[0];
}

function saveExtension(e) {
    e.preventDefault();
    var id = document.getElementById('extend-row-id').value;
    var startVal = document.getElementById('ext-start').value;
    var endVal = document.getElementById('ext-end').value;
    
    // Format dates to Indonesian display
    var startDateObj = new Date(startVal);
    var endDateObj = new Date(endVal);
    
    var options = { day: 'numeric', month: 'short', year: 'numeric' };
    var startFormatted = startDateObj.toLocaleDateString('id-ID', options);
    var endFormatted = endDateObj.toLocaleDateString('id-ID', options);
    
    var newPeriodText = startFormatted + ' s/d ' + endFormatted;
    
    var targetCell = document.getElementById('masa-aktif-' + id);
    if (targetCell) {
        targetCell.textContent = newPeriodText;
        targetCell.style.transition = 'all 0.4s ease';
        targetCell.style.backgroundColor = '#bbf7d0';
        setTimeout(function() { targetCell.style.backgroundColor = 'transparent'; }, 1500);
    }
    
    closeExtendModal();
    showAdminToast('Masa aktif kemitraan berhasil diperbarui menjadi: ' + newPeriodText);
}

function showAdminToast(msg) {
    var toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = '<span>' + msg + '</span>';
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.transition = 'opacity 0.4s ease';
        toast.style.opacity = '0';
        setTimeout(function() { toast.remove(); }, 400);
    }, 3000);
}
</script>
@endsection
