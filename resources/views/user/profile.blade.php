@extends('layouts.user')
@section('title', 'Edit Profil Instansi & PIC')

@section('content')

@if(session('ok'))
<div style="background:#dcfce7;border:1px solid #86efac;border-left:5px solid #16a34a;border-radius:8px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:12px;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="12" fill="#16a34a"/><path d="M7 12.5l3.5 3.5 6-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <div>
        <div style="font-weight:700;color:#15803d;margin-bottom:1px;">Profil berhasil disimpan!</div>
        <div style="font-size:12px;color:#166534;">{{ session('ok') }}</div>
    </div>
</div>
@endif

<div class="box">
    <h2>Edit Profil Pribadi &amp; Data Instansi Mitra</h2>
    <div class="in">
        <form method="POST" action="{{ route('mitra.profile.update') }}" enctype="multipart/form-data">
            @csrf

            <!-- PREVIEW FOTO PROFIL / LOGO SAAT INI -->
            <div style="display:flex;align-items:center;gap:20px;background:#f8fafc;padding:16px;border:1px solid var(--border-color);border-radius:var(--radius);margin-bottom:20px;">
                <div style="position:relative;">
                    <img id="avatar-preview" src="{{ $mitra['foto'] }}" alt="{{ $mitra['username'] }}" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--primary);">
                </div>
                <div>
                    <h4 style="margin-bottom:4px;color:var(--primary);">Foto Profil / Logo Instansi</h4>
                    <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px;">Foto ini akan ditampilkan pada pojok kanan atas navbar di sebelah tombol Keluar dan Username.</p>
                    <button type="button" class="btn btn2 btn-s" onclick="document.getElementById('file-logo').click()">Ganti Foto / Logo</button>
                </div>
            </div>

            <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:14px;">
                <div class="row">
                    <label for="username">Username Akun</label>
                    <input type="text" id="username" name="username" value="{{ $mitra['username'] }}" required style="background:#f1f5f9;" readonly>
                    <span style="font-size:11px;color:var(--text-muted);">Username digunakan untuk login ke portal mitra.</span>
                </div>
                <div class="row">
                    <label for="instansi">Nama Instansi / Perusahaan</label>
                    <input type="text" id="instansi" name="instansi" value="{{ $mitra['instansi'] }}" required>
                </div>
            </div>

            <div class="row">
                <label for="organisasi">Unit Kerja / Divisi / Organisasi</label>
                <input type="text" id="organisasi" name="organisasi" value="{{ $mitra['organisasi'] }}" required>
            </div>

            <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:14px;">
                <div class="row">
                    <label for="pic">Nama Penanggung Jawab (PIC)</label>
                    <input type="text" id="pic" name="pic" value="{{ $mitra['pic'] }}" required>
                </div>
                <div class="row">
                    <label for="email">Email Official Instansi</label>
                    <input type="email" id="email" name="email" value="{{ $mitra['email'] }}" required>
                </div>
            </div>

            <div class="row">
                <label for="telp">Nomor Telepon / WhatsApp PIC</label>
                <input type="text" id="telp" name="telp" value="{{ $mitra['telp'] }}" required>
            </div>

            <div class="row">
                <label for="alamat">Alamat Lengkap Kantor Instansi</label>
                <textarea id="alamat" name="alamat" rows="3" required>{{ $mitra['alamat'] }}</textarea>
            </div>

            <div class="row">
                <label>Upload File Foto / Logo Baru (Opsional)</label>
                <div class="drop" id="dz-logo" onclick="document.getElementById('file-logo').click()" style="border:1px dashed var(--border-color);padding:16px;text-align:center;background:#f8fafc;cursor:pointer;border-radius:var(--radius);">
                    <span>Klik atau seret foto/logo mitra ke sini untuk memperbarui</span>
                    <b id="logo-filename" style="display:block;margin-top:6px;color:var(--primary);"></b>
                </div>
                <input type="file" id="file-logo" name="logo" accept="image/*" class="hide" style="display:none" onchange="previewImage(this)">
            </div>

            <div style="margin-top:20px;display:flex;gap:10px;">
                <button type="submit" class="btn">Simpan Perubahan Profil</button>
                <a href="{{ route('mitra.status') }}" class="btn btn2">Kembali ke Dashboard</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    var fn = document.getElementById('logo-filename');
    if (input.files && input.files[0]) {
        fn.textContent = input.files[0].name;
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').src = e.target.result;
            var topAvatar = document.getElementById('top-user-avatar');
            if (topAvatar) topAvatar.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
