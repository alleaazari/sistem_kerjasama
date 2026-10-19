{{-- Variabel: $jenis, $tambah, $labelIsi, $rows --}}

{{-- SUCCESS BANNER --}}
@if(session('ok'))
<div style="background:#dcfce7;border:1px solid #86efac;border-left:5px solid #16a34a;border-radius:8px;padding:12px 16px;margin-bottom:18px;display:flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:#15803d;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="12" fill="#16a34a"/><path d="M7 12.5l3.5 3.5 6-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    {{ session('ok') }}
</div>
@endif

{{-- TAMBAH BARU --}}
<p>
    <button type="button" class="btn" onclick="toggleForm('form-tambah')">+ {{ $tambah }}</button>
</p>

<form id="form-tambah" class="box" style="display:none;margin-bottom:18px;" method="POST"
      action="{{ route('admin.'.$jenis.'.store') }}" enctype="multipart/form-data">
    @csrf
    <h2 style="padding:14px 18px;border-bottom:1px solid var(--border-color);font-size:15px;">{{ $tambah }}</h2>
    <div class="in">
        <div class="row">
            <label>Gambar (Opsional)</label>
            <div class="drop" id="dz-tambah" onclick="document.getElementById('fi-tambah').click()"
                 style="border:1.5px dashed var(--border-color);padding:16px;text-align:center;background:#f8fafc;cursor:pointer;border-radius:8px;transition:all 0.2s;">
                <span style="color:var(--text-muted);font-size:13px;">Seret gambar ke sini atau klik untuk memilih file</span>
                <b id="nm-tambah" style="display:block;margin-top:6px;color:var(--primary);font-size:12px;"></b>
            </div>
            <input type="file" name="gambar" id="fi-tambah" accept="image/*" style="display:none" onchange="previewFile(this,'nm-tambah','prev-tambah')">
            <img id="prev-tambah" src="" alt="" style="display:none;max-height:120px;margin-top:10px;border-radius:6px;border:1px solid var(--border-color);">
        </div>
        <div class="row"><label>Judul <span style="color:#dc2626;">*</span></label><input type="text" name="judul" required placeholder="Masukkan judul..."></div>
        <div class="row"><label>{{ $labelIsi }}</label><textarea name="konten" rows="4" placeholder="Masukkan isi/keterangan..."></textarea></div>
        <div style="display:flex;gap:10px;margin-top:4px;">
            <button class="btn">Simpan & Publikasikan</button>
            <button type="button" class="btn btn2" onclick="toggleForm('form-tambah')">Batal</button>
        </div>
    </div>
</form>

{{-- DAFTAR DATA --}}
<div class="scroll">
<table class="data" id="table-cms">
    <tr>
        <th style="width:50%;">Judul</th>
        <th>Tanggal</th>
        <th style="width:140px;">Aksi</th>
    </tr>
    @forelse ($rows as $r)
    <tr id="row-cms-{{ $r['id'] }}">
        <td>
            <b style="color:var(--primary);font-size:13px;">{{ $r['judul'] }}</b>
            @if(!empty($r['konten']))
            <p style="font-size:12px;color:var(--text-muted);margin:2px 0 0;line-height:1.4;">{{ Str::limit($r['konten'], 80) }}</p>
            @endif
        </td>
        <td style="font-size:12px;color:var(--text-muted);">{{ $r['tanggal'] }}</td>
        <td>
            <button type="button" class="btn btn-s" onclick="bukaEdit('{{ $r['id'] }}','{{ addslashes($r['judul']) }}','{{ addslashes($r['konten'] ?? '') }}')"
                    style="margin-right:4px;">Edit</button>
            <form method="POST" action="{{ route('admin.'.$jenis.'.delete', $r['id']) }}" style="display:inline;"
                  onsubmit="return confirm('Hapus item ini?')">
                @csrf
                <button type="submit" class="btn btn-s" style="background:#ef4444;border-color:#ef4444;">Hapus</button>
            </form>
        </td>
    </tr>
    @empty
    <tr><td colspan="3" style="text-align:center;color:var(--text-muted);padding:20px;">Belum ada data. Klik "+ {{ $tambah }}" untuk menambahkan.</td></tr>
    @endforelse
</table>
</div>

{{-- MODAL EDIT --}}
<div id="modal-edit" class="lightbox" onclick="if(event.target===this) tutupEdit()">
    <div class="lightbox-content" style="max-width:540px;width:100%;padding:0;">
        <div style="background:var(--primary);color:#fff;padding:14px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">
            <b style="font-size:15px;">Edit Item</b>
            <button onclick="tutupEdit()" style="background:none;border:none;color:#fff;cursor:pointer;font-size:18px;padding:0;">✕</button>
        </div>
        <form id="form-edit" method="POST" action="" enctype="multipart/form-data" style="padding:20px;">
            @csrf
            <div class="row"><label>Judul <span style="color:#dc2626;">*</span></label><input type="text" id="edit-judul" name="judul" required></div>
            <div class="row"><label>{{ $labelIsi }}</label><textarea id="edit-konten" name="konten" rows="4"></textarea></div>
            <div class="row">
                <label>Ganti Gambar (Opsional)</label>
                <div class="drop" onclick="document.getElementById('fi-edit').click()"
                     style="border:1.5px dashed var(--border-color);padding:12px;text-align:center;background:#f8fafc;cursor:pointer;border-radius:8px;font-size:13px;color:var(--text-muted);">
                    Klik untuk pilih gambar baru
                    <b id="nm-edit" style="display:block;margin-top:4px;color:var(--primary);font-size:12px;"></b>
                </div>
                <input type="file" id="fi-edit" name="gambar" accept="image/*" style="display:none" onchange="previewFile(this,'nm-edit','prev-edit')">
                <img id="prev-edit" src="" alt="" style="display:none;max-height:100px;margin-top:8px;border-radius:6px;border:1px solid var(--border-color);">
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:6px;">
                <button type="button" class="btn btn2" onclick="tutupEdit()">Batal</button>
                <button type="submit" class="btn">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
var jenis = '{{ $jenis }}';

function toggleForm(id) {
    var el = document.getElementById(id);
    el.style.display = (el.style.display === 'none' || el.style.display === '') ? 'block' : 'none';
}

function previewFile(input, nmId, prevId) {
    var nm   = document.getElementById(nmId);
    var prev = document.getElementById(prevId);
    if (input.files && input.files[0]) {
        nm.textContent = input.files[0].name;
        var reader = new FileReader();
        reader.onload = function(e) {
            prev.src = e.target.result;
            prev.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function bukaEdit(id, judul, konten) {
    document.getElementById('edit-judul').value  = judul;
    document.getElementById('edit-konten').value = konten;
    document.getElementById('nm-edit').textContent  = '';
    document.getElementById('prev-edit').style.display = 'none';
    document.getElementById('fi-edit').value = '';

    // Set form action ke route edit (pakai prefix rahasia agar tidak hardcoded /admin)
    var adminBase = "{{ url(config('admin_demo.path')) }}";
    document.getElementById('form-edit').action = adminBase + '/' + jenis + '/' + id + '/edit';

    document.getElementById('modal-edit').classList.add('active');
}

function tutupEdit() {
    document.getElementById('modal-edit').classList.remove('active');
}

// Drag & drop tambah
(function () {
    var dz = document.getElementById('dz-tambah');
    if (!dz) return;
    var fi = document.getElementById('fi-tambah');
    dz.ondragover  = function(e) { e.preventDefault(); dz.style.borderColor='var(--accent)'; };
    dz.ondragleave = function()  { dz.style.borderColor=''; };
    dz.ondrop      = function(e) {
        e.preventDefault(); dz.style.borderColor='';
        if (e.dataTransfer.files[0]) {
            fi.files = e.dataTransfer.files;
            fi.dispatchEvent(new Event('change'));
        }
    };
})();
</script>
