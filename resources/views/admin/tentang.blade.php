@extends('layouts.admin')
@section('title', 'Kelola Profil Petinggi & Visi Misi Komdigi')

@section('content')
@php($petinggi = $petinggi ?? [])
@php($visi = $visi ?? '')

<div class="panel">
    <div class="panel-head panel-head-row">
        <div>
            <h2 class="panel-title">Profil Petinggi Komdigi dan BBLSDM Medan</h2>
            <p class="panel-sub">Data ini tampil pada halaman utama Portal Publik dan tersinkron otomatis saat disimpan.</p>
        </div>
        <button type="button" class="btn btn-s" onclick="openAddLeaderModal()">Tambah Profil Petinggi</button>
    </div>

    <form method="POST" action="{{ route('admin.tentang.update') }}" onsubmit="saveProfilTentang(event)" class="panel-body" id="form-tentang" enctype="multipart/form-data">
        @csrf

        <div id="leaders-container" class="leader-list">
            @forelse ($petinggi as $i => $p)
            <div class="leader-item-card" data-idx="{{ $i }}">
                <div class="leader-card-head">
                    <h3 class="leader-card-title">{{ $i + 1 }}. {{ $p['jabatan'] ?? $p['nama'] }}</h3>
                    <button type="button" class="btn btn-s btn-hapus" onclick="removeLeaderCard(this)">Hapus</button>
                </div>
                <div class="leader-grid">
                    <div class="row">
                        <label for="p-foto-url-{{ $i }}">URL foto (opsional, dipakai jika tidak ada unggahan baru)</label>
                        <input type="text" id="p-foto-url-{{ $i }}" name="p_foto_url[]" value="{{ $p['foto'] ?? '' }}" placeholder="https://... atau data:base64">
                    </div>
                    <div class="row"><label>Nama lengkap dan gelar</label><input type="text" name="p_nama[]" value="{{ $p['nama'] }}" required></div>
                    <div class="row"><label>Jabatan</label><input type="text" name="p_jabatan[]" value="{{ $p['jabatan'] }}" required></div>
                    <div class="row"><label>Kategori badge</label><input type="text" name="p_badge[]" value="{{ $p['badge'] }}" required></div>
                    <div class="row">
                        <label for="fi-p{{ $i }}">Ganti foto profil (PNG atau JPG)</label>
                        <div class="drop" onclick="document.getElementById('fi-p{{ $i }}').click()">
                            <span>Seret foto ke sini atau klik untuk memilih</span>
                            <b id="nm-p{{ $i }}"></b>
                        </div>
                        <input type="file" name="p_foto[]" id="fi-p{{ $i }}" accept="image/png,image/jpeg" class="sr-only"
                               onchange="previewFile(this,'nm-p{{ $i }}','prev-p{{ $i }}')">
                        <img id="prev-p{{ $i }}" class="drop-preview" alt="Pratinjau foto {{ $p['nama'] }}"
                             src="{{ $p['foto'] ?? asset('assets/shared/img/placeholder.png') }}">
                    </div>
                </div>
                <div class="row">
                    <label>Deskripsi tugas dan arahan</label>
                    <textarea name="p_desc[]" rows="2" required>{{ $p['desc'] ?? '' }}</textarea>
                </div>
            </div>
            @empty
            <p class="panel-sub">Belum ada profil petinggi. Tambahkan melalui tombol di atas.</p>
            @endforelse
        </div>

        <p class="panel-toolbar-inline">
            <button type="button" class="btn btn2" onclick="openAddLeaderModal()">Tambah Profil Petinggi Lainnya</button>
        </p>

        <h3 class="panel-section-title">Visi Komdigi</h3>
        <div class="row">
            <label for="visi">Visi Kementerian Komdigi</label>
            <textarea id="visi" name="visi" rows="3" required>{{ $visi }}</textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn">Simpan dan sinkronkan ke landing</button>
        </div>
    </form>
</div>

{{-- Dialog tambah petinggi --}}
<div id="modal-add-leader" class="lightbox" onclick="if(event.target===this) closeAddLeaderModal()">
    <div class="lightbox-content modal-review">
        <div class="modal-head">
            <h2 class="modal-head-title">Tambah Profil Petinggi Baru</h2>
            <button type="button" class="modal-close" onclick="closeAddLeaderModal()" aria-label="Tutup dialog">&times;</button>
        </div>
        <form id="form-new-leader" onsubmit="submitNewLeader(event)" class="modal-body">
            <div class="row">
                <label for="nl-nama">Nama lengkap dan gelar</label>
                <input type="text" id="nl-nama" placeholder="Contoh: Dr. Ir. Ahmad Sudirman, M.T." required>
            </div>
            <div class="row">
                <label for="nl-jabatan">Jabatan resmi</label>
                <input type="text" id="nl-jabatan" placeholder="Contoh: Direktur Jenderal Aplikasi Informatika" required>
            </div>
            <div class="leader-grid">
                <div class="row">
                    <label for="nl-badge">Badge organisasi</label>
                    <input type="text" id="nl-badge" placeholder="Contoh: Ditjen Aptika" required>
                </div>
                <div class="row">
                    <label for="nl-foto-file">Foto profil (PNG atau JPG)</label>
                    <div class="drop" onclick="document.getElementById('nl-foto-file').click()">
                        <span>Seret foto ke sini atau klik untuk memilih</span>
                        <b id="nm-nl-foto"></b>
                    </div>
                    <input type="file" id="nl-foto-file" name="nl_foto" accept="image/png,image/jpeg" class="sr-only"
                           onchange="previewFile(this,'nm-nl-foto','prev-nl-foto')">
                    <img id="prev-nl-foto" class="drop-preview" alt="Pratinjau foto petinggi baru"
                         src="">
                </div>
            </div>
            <div class="row">
                <label for="nl-desc">Deskripsi tugas atau bio ringkas</label>
                <textarea id="nl-desc" rows="2" placeholder="Tuliskan arahan dan tanggung jawab jabatan" required></textarea>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn2" onclick="closeAddLeaderModal()">Batal</button>
                <button type="submit" class="btn">Tambahkan ke daftar</button>
            </div>
        </form>
    </div>
</div>

<script>
var LEADER_DATA = @json($petinggi);
var VISI_DATA = @json($visi);

function openAddLeaderModal() {
    document.getElementById('modal-add-leader').classList.add('active');
}

function closeAddLeaderModal() {
    document.getElementById('modal-add-leader').classList.remove('active');
}

function previewFile(input, nmId, prevId) {
    var nm = document.getElementById(nmId);
    var prev = document.getElementById(prevId);
    if (input.files && input.files[0]) {
        nm.textContent = input.files[0].name;
        var reader = new FileReader();
        reader.onload = function (e) { prev.src = e.target.result; };
        reader.readAsDataURL(input.files[0]);
    }
}

function submitNewLeader(e) {
    e.preventDefault();
    var nama = document.getElementById('nl-nama').value;
    var jabatan = document.getElementById('nl-jabatan').value;
    var badge = document.getElementById('nl-badge').value;
    var desc = document.getElementById('nl-desc').value;
    var fotoInput = document.getElementById('nl-foto-file');

    function appendCard(fotoUrl) {
        var container = document.getElementById('leaders-container');
        var nomor = container.children.length;
        var kartu = document.createElement('div');
        kartu.className = 'leader-item-card';

        var head = document.createElement('div');
        head.className = 'leader-card-head';
        var judul = document.createElement('h3');
        judul.className = 'leader-card-title';
        judul.textContent = (nomor + 1) + '. ' + jabatan;
        var hapus = document.createElement('button');
        hapus.type = 'button';
        hapus.className = 'btn btn-s btn-hapus';
        hapus.textContent = 'Hapus';
        hapus.addEventListener('click', function () { removeLeaderCard(hapus); });
        head.appendChild(judul);
        head.appendChild(hapus);
        kartu.appendChild(head);

        var grid = document.createElement('div');
        grid.className = 'leader-grid';

        var urlRow = document.createElement('div');
        urlRow.className = 'row';
        var urlLabel = document.createElement('label');
        urlLabel.textContent = 'URL foto (opsional, dipakai jika tidak ada unggahan baru)';
        var urlInput = document.createElement('input');
        urlInput.type = 'text';
        urlInput.name = 'p_foto_url[]';
        urlInput.value = fotoUrl || '';
        urlInput.placeholder = 'https://... atau data:base64';
        urlRow.appendChild(urlLabel);
        urlRow.appendChild(urlInput);
        grid.appendChild(urlRow);

        [['Nama lengkap dan gelar', 'p_nama[]', nama],
         ['Jabatan', 'p_jabatan[]', jabatan],
         ['Kategori badge', 'p_badge[]', badge]].forEach(function (f) {
            var baris = document.createElement('div');
            baris.className = 'row';
            var label = document.createElement('label');
            label.textContent = f[0];
            var input = document.createElement('input');
            input.type = 'text';
            input.name = f[1];
            input.value = f[2];
            input.required = true;
            baris.appendChild(label);
            baris.appendChild(input);
            grid.appendChild(baris);
        });

        var idFoto = 'fi-p' + nomor;
        var barisFoto = document.createElement('div');
        barisFoto.className = 'row';
        var labelFoto = document.createElement('label');
        labelFoto.textContent = 'Ganti foto profil (PNG atau JPG)';
        labelFoto.setAttribute('for', idFoto);

        var drop = document.createElement('div');
        drop.className = 'drop';
        var dropText = document.createElement('span');
        dropText.textContent = 'Seret foto ke sini atau klik untuk memilih';
        var dropName = document.createElement('b');
        drop.appendChild(dropText);
        drop.appendChild(dropName);

        var inputFoto = document.createElement('input');
        inputFoto.type = 'file';
        inputFoto.name = 'p_foto[]';
        inputFoto.id = idFoto;
        inputFoto.accept = 'image/png,image/jpeg';
        inputFoto.className = 'sr-only';

        var pratinjau = document.createElement('img');
        pratinjau.className = 'drop-preview';
        pratinjau.alt = 'Pratinjau foto ' + nama;
        pratinjau.src = fotoUrl || '';
        pratinjau.id = 'prev-p' + nomor;

        drop.addEventListener('click', function () { inputFoto.click(); });
        inputFoto.addEventListener('change', function () {
            previewFile(inputFoto, 'nm-p' + nomor, 'prev-p' + nomor);
        });
        dropName.id = 'nm-p' + nomor;

        barisFoto.appendChild(labelFoto);
        barisFoto.appendChild(drop);
        barisFoto.appendChild(inputFoto);
        barisFoto.appendChild(pratinjau);
        grid.appendChild(barisFoto);

        kartu.appendChild(grid);

        var barisDesc = document.createElement('div');
        barisDesc.className = 'row';
        var labelDesc = document.createElement('label');
        labelDesc.textContent = 'Deskripsi tugas dan arahan';
        var areaDesc = document.createElement('textarea');
        areaDesc.name = 'p_desc[]';
        areaDesc.rows = 2;
        areaDesc.required = true;
        areaDesc.value = desc;
        barisDesc.appendChild(labelDesc);
        barisDesc.appendChild(areaDesc);
        kartu.appendChild(barisDesc);

        container.appendChild(kartu);
        closeAddLeaderModal();
        document.getElementById('form-new-leader').reset();
        document.getElementById('prev-nl-foto').src = '';
        document.getElementById('nm-nl-foto').textContent = '';
        showAdminToast('Profil petinggi baru ditambahkan.');
    }

    if (fotoInput.files && fotoInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function (ev) { appendCard(ev.target.result); };
        reader.readAsDataURL(fotoInput.files[0]);
        return;
    }

    appendCard('');
}

function removeLeaderCard(btn) {
    if (!confirm('Hapus kartu profil petinggi ini? Data ini juga dihapus dari landing page.')) { return; }
    var card = btn.closest('.leader-item-card');
    if (card) { card.remove(); }
}

function saveProfilTentang(e) {
    // Biarkan form terkirim ke server agar data tersimpan dan sinkron ke landing.
    showAdminToast('Menyimpan profil…');
}

function showAdminToast(msg) {
    var existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();
    var toast = document.createElement('div');
    toast.className = 'toast-notification';
    var span = document.createElement('span');
    span.textContent = msg;
    toast.appendChild(span);
    document.body.appendChild(toast);
    setTimeout(function () {
        toast.style.transition = 'opacity 0.4s ease';
        toast.style.opacity = '0';
        setTimeout(function () { toast.remove(); }, 400);
    }, 2600);
}
</script>
@endsection
