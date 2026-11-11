@extends('layouts.admin')
@section('title', 'Kelola Draf Proposal AI & Masa Aktif Mitra')

@section('content')
<div class="panel">
    <div class="panel-head">
        <h2 class="panel-title">Daftar Draf Proposal AI &amp; Masa Aktif Mitra</h2>
        <p class="panel-sub">Draf proposal yang dibuat mitra melalui generator AI, beserta status masa aktif kemitraan.</p>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="table-mitra">
            <thead>
                <tr>
                    <th scope="col">Instansi Mitra</th>
                    <th scope="col">Judul Kemitraan</th>
                    <th scope="col">Tanggal Pembuatan</th>
                    <th scope="col">Masa Aktif</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                <tr id="row-mitra-{{ $r['id'] }}">
                    <td><b class="cell-strong">{{ $r['instansi'] }}</b></td>
                    <td class="cell-muted">{{ $r['judul_ks'] }}</td>
                    <td class="cell-muted">{{ $r['tgl_buat'] }}</td>
                    <td><code id="masa-aktif-{{ $r['id'] }}">{{ $r['masa_aktif'] }}</code></td>
                    <td><span class="badge badge-success">{{ $r['status'] }}</span></td>
                    <td>
                        <div class="table-actions">
                            <button type="button" class="btn btn2 btn-s" disabled>Unduh Draf</button>
                            <button type="button" class="btn btn-s" disabled>Perpanjang</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="cell-empty">Belum ada draf proposal dari mitra.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL INTERAKTIF PERPANJANG MASA AKTIF MITRA -->
<div id="modal-extend" class="lightbox">
    <div class="lightbox-content modal-review">
        <div class="modal-head">
            <h2 class="modal-head-title">Penyetelan Masa Aktif Kemitraan</h2>
            <button type="button" class="modal-close" onclick="closeExtendModal()" aria-label="Tutup dialog">&times;</button>
        </div>
        <div class="modal-body">
            <p class="field-help field-help-top">Tetapkan periode tanggal mulai dan berakhirnya kerja sama untuk instansi mitra.</p>

            <dl class="detail-grid">
                <div class="detail-full">
                    <dt>Instansi Mitra</dt>
                    <dd id="extend-instansi-name"></dd>
                </div>
            </dl>

            <form id="form-extend" onsubmit="saveExtension(event)">
                <input type="hidden" id="extend-row-id">

                <div class="row">
                    <label for="ext-start">Tanggal mulai baru</label>
                    <input type="date" id="ext-start" required value="2026-10-12">
                </div>

                <div class="row">
                    <label for="ext-end">Tanggal berakhir baru</label>
                    <input type="date" id="ext-end" required value="2028-10-12">
                </div>

                <div class="row">
                    <label>Pilihan durasi cepat</label>
                    <div class="chip-row">
                        <button type="button" class="btn btn2 btn-s" onclick="setPresetDuration(1)">+1 Tahun</button>
                        <button type="button" class="btn btn2 btn-s" onclick="setPresetDuration(2)">+2 Tahun</button>
                        <button type="button" class="btn btn2 btn-s" onclick="setPresetDuration(3)">+3 Tahun</button>
                        <button type="button" class="btn btn2 btn-s" onclick="setPresetDuration(5)">+5 Tahun</button>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn btn2" onclick="closeExtendModal()">Batal</button>
                    <button type="submit" class="btn">Simpan Masa Aktif</button>
                </div>
            </form>
        </div>
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
