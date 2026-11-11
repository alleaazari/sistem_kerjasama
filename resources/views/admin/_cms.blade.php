{{-- Variabel: $jenis, $tambah, $labelIsi, $rows --}}

@if (session('ok'))
    <div class="note-box note-box-success" role="status">{{ session('ok') }}</div>
@endif

<p class="panel-toolbar">
    <button type="button" class="btn" onclick="toggleForm('form-tambah')">+ {{ $tambah }}</button>
</p>

<form id="form-tambah" class="panel" hidden method="POST"
      action="{{ route('admin.'.$jenis.'.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="panel-head">
        <h2 class="panel-title">{{ $tambah }}</h2>
    </div>
    <div class="panel-body">
        <div class="row">
            <label for="fi-tambah">Gambar (opsional)</label>
            <div class="drop" id="dz-tambah" onclick="document.getElementById('fi-tambah').click()">
                <span>Seret gambar ke sini atau klik untuk memilih berkas</span>
                <b id="nm-tambah"></b>
            </div>
            <input type="file" name="gambar" id="fi-tambah" accept="image/*" class="sr-only"
                   onchange="previewFile(this,'nm-tambah','prev-tambah')">
            <img id="prev-tambah" src="" alt="" class="drop-preview" hidden>
        </div>
        <div class="row">
            <label for="tambah-judul">Judul <span class="req">*</span></label>
            <input type="text" id="tambah-judul" name="judul" required placeholder="Masukkan judul">
        </div>
        <div class="row">
            <label for="tambah-konten">{{ $labelIsi }}</label>
            <textarea id="tambah-konten" name="konten" rows="4" placeholder="Masukkan isi atau keterangan"></textarea>
        </div>
        <div class="form-actions">
            <button class="btn">Simpan dan publikasikan</button>
            <button type="button" class="btn btn2" onclick="toggleForm('form-tambah')">Batal</button>
        </div>
    </div>
</form>

<div class="panel">
    <div class="table-scroll">
        <table class="data-table" id="table-cms">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Tanggal</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                    <tr id="row-cms-{{ $r['id'] }}">
                        <td>
                            <span class="cell-strong">{{ $r['judul'] }}</span>
                            @if (! empty($r['konten']))
                                <span class="cell-muted cell-block">{{ Str::limit($r['konten'], 80) }}</span>
                            @endif
                        </td>
                        <td class="cell-muted">{{ $r['tanggal'] }}</td>
                        <td>
                            <div class="table-actions">
                                <button type="button" class="btn btn-s"
                                        onclick="bukaEdit('{{ $r['id'] }}','{{ addslashes($r['judul']) }}','{{ addslashes($r['konten'] ?? '') }}')">Edit</button>
                                <form method="POST" action="{{ route('admin.'.$jenis.'.delete', $r['id']) }}"
                                      onsubmit="return confirm('Hapus item ini?')">
                                    @csrf
                                    <button type="submit" class="btn btn-s btn-hapus">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="cell-empty">Belum ada data. Klik "{{ $tambah }}" untuk menambahkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Dialog edit --}}
<div id="modal-edit" class="lightbox" onclick="if(event.target===this) tutupEdit()">
    <div class="lightbox-content modal-review">
        <div class="modal-head">
            <h2 class="modal-head-title">Edit Item</h2>
            <button type="button" class="modal-close" onclick="tutupEdit()" aria-label="Tutup dialog">&times;</button>
        </div>
        <form id="form-edit" method="POST" action="" enctype="multipart/form-data" class="modal-body">
            @csrf
            <div class="row">
                <label for="edit-judul">Judul <span class="req">*</span></label>
                <input type="text" id="edit-judul" name="judul" required>
            </div>
            <div class="row">
                <label for="edit-konten">{{ $labelIsi }}</label>
                <textarea id="edit-konten" name="konten" rows="4"></textarea>
            </div>
            <div class="row">
                <label for="fi-edit">Ganti gambar (opsional)</label>
                <div class="drop" onclick="document.getElementById('fi-edit').click()">
                    <span>Klik untuk memilih gambar baru</span>
                    <b id="nm-edit"></b>
                </div>
                <input type="file" id="fi-edit" name="gambar" accept="image/*" class="sr-only"
                       onchange="previewFile(this,'nm-edit','prev-edit')">
                <img id="prev-edit" src="" alt="" class="drop-preview" hidden>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn2" onclick="tutupEdit()">Batal</button>
                <button type="submit" class="btn">Simpan perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
var jenis = '{{ $jenis }}';

function toggleForm(id) {
    var el = document.getElementById(id);
    el.hidden = !el.hidden;
}

function previewFile(input, nmId, prevId) {
    var nm = document.getElementById(nmId);
    var prev = document.getElementById(prevId);
    if (input.files && input.files[0]) {
        nm.textContent = input.files[0].name;
        var reader = new FileReader();
        reader.onload = function (e) {
            prev.src = e.target.result;
            prev.hidden = false;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function bukaEdit(id, judul, konten) {
    document.getElementById('edit-judul').value = judul;
    document.getElementById('edit-konten').value = konten;
    document.getElementById('nm-edit').textContent = '';
    document.getElementById('prev-edit').hidden = true;
    document.getElementById('fi-edit').value = '';

    // Prefix admin diambil dari konfigurasi agar tidak ditulis di klien.
    var adminBase = "{{ url(config('admin_demo.path')) }}";
    document.getElementById('form-edit').action = adminBase + '/' + jenis + '/' + id + '/edit';

    document.getElementById('modal-edit').classList.add('active');
}

function tutupEdit() {
    document.getElementById('modal-edit').classList.remove('active');
}

// Seret dan lepas untuk formulir tambah.
(function () {
    var dz = document.getElementById('dz-tambah');
    if (!dz) return;
    var fi = document.getElementById('fi-tambah');
    dz.ondragover = function (e) { e.preventDefault(); dz.classList.add('is-over'); };
    dz.ondragleave = function () { dz.classList.remove('is-over'); };
    dz.ondrop = function (e) {
        e.preventDefault();
        dz.classList.remove('is-over');
        if (e.dataTransfer.files[0]) {
            fi.files = e.dataTransfer.files;
            fi.dispatchEvent(new Event('change'));
        }
    };
})();
</script>
