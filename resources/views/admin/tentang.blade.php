@extends('layouts.admin')
@section('title', 'Kelola Profil Petinggi & Visi Misi Komdigi')

@section('content')
<div class="box">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 18px;background:#f8fafc;border-bottom:1px solid var(--border-color);">
        <h2 style="font-size:15px;color:var(--primary);margin:0;">Kelola Profil Petinggi Komdigi & BBLSDM Medan</h2>
        <button type="button" class="btn btn-s" onclick="openAddLeaderModal()">+ Tambah Profil Petinggi Baru</button>
    </div>
    <div class="in">
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:16px;">
            Anda dapat mengedit daftar profil petinggi di bawah ini atau menambahkan profil petinggi baru yang akan langsung ditampilkan pada halaman utama Portal Publik.
        </p>

        <form method="POST" action="{{ route('admin.tentang.update') }}" onsubmit="saveProfilTentang(event)">
            @csrf
            
            <!-- LIST CONTAINER PROFIL PETINGGI -->
            <div id="leaders-container" class="grid3" style="grid-template-columns: 1fr; gap:16px;">
                <div class="leader-item-card" style="background:#f8fafc;padding:14px;border:1px solid var(--border-color);border-radius:var(--radius);position:relative;">
                    <button type="button" onclick="removeLeaderCard(this)" style="position:absolute;top:10px;right:10px;background:#ef4444;color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">Hapus</button>
                    <h4 style="color:var(--primary);margin-bottom:8px;">1. Menteri Komunikasi dan Digital RI</h4>
                    <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="row"><label>Nama Lengkap & Gelar</label><input type="text" name="p_nama[]" value="Meutya Viada Hafid" required></div>
                        <div class="row"><label>Jabatan</label><input type="text" name="p_jabatan[]" value="Menteri Komunikasi dan Digital RI" required></div>
                    </div>
                    <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="row"><label>Kategori Badge</label><input type="text" name="p_badge[]" value="Pimpinan Kementerian" required></div>
                        <div class="row"><label>Foto Profil (PNG/JPG)</label>
                            <div class="drop" onclick="document.getElementById('fi-p1').click()"
                                 style="border:1.5px dashed var(--border-color);padding:16px;text-align:center;background:#f8fafc;cursor:pointer;border-radius:8px;transition:all 0.2s;">
                                <span style="color:var(--text-muted);font-size:13px;">Seret foto ke sini atau klik untuk memilih</span>
                                <b id="nm-p1" style="display:block;margin-top:6px;color:var(--primary);font-size:12px;"></b>
                            </div>
                            <input type="file" name="p_foto[]" id="fi-p1" accept="image/png,image/jpeg,image/jpg" style="display:none" onchange="previewFile(this,'nm-p1','prev-p1')">
                            <img id="prev-p1" src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=600&auto=format&fit=crop" alt="Preview" style="display:block;max-height:100px;margin-top:10px;border-radius:6px;border:1px solid var(--border-color);">
                        </div>
                    </div>
                    <div class="row"><label>Deskripsi Tugas & Arahan</label><textarea name="p_desc[]" rows="2" required>Memimpin transformasi digital Indonesia, penguatan infrastruktur telekomunikasi nasional, serta pengembangan talenta digital.</textarea></div>
                </div>

                <div class="leader-item-card" style="background:#f8fafc;padding:14px;border:1px solid var(--border-color);border-radius:var(--radius);position:relative;">
                    <button type="button" onclick="removeLeaderCard(this)" style="position:absolute;top:10px;right:10px;background:#ef4444;color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">Hapus</button>
                    <h4 style="color:var(--primary);margin-bottom:8px;">2. Kepala BPSDM Komdigi Pusat</h4>
                    <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="row"><label>Nama Lengkap & Gelar</label><input type="text" name="p_nama[]" value="Bonifasius Wahyu Pudjianto, Ph.D." required></div>
                        <div class="row"><label>Jabatan</label><input type="text" name="p_jabatan[]" value="Kepala BPSDM Komdigi Pusat" required></div>
                    </div>
                    <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="row"><label>Kategori Badge</label><input type="text" name="p_badge[]" value="Badan Pengembangan SDM" required></div>
                        <div class="row"><label>Foto Profil (PNG/JPG)</label>
                            <div class="drop" onclick="document.getElementById('fi-p2').click()"
                                 style="border:1.5px dashed var(--border-color);padding:16px;text-align:center;background:#f8fafc;cursor:pointer;border-radius:8px;transition:all 0.2s;">
                                <span style="color:var(--text-muted);font-size:13px;">Seret foto ke sini atau klik untuk memilih</span>
                                <b id="nm-p2" style="display:block;margin-top:6px;color:var(--primary);font-size:12px;"></b>
                            </div>
                            <input type="file" name="p_foto[]" id="fi-p2" accept="image/png,image/jpeg,image/jpg" style="display:none" onchange="previewFile(this,'nm-p2','prev-p2')">
                            <img id="prev-p2" src="https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop" alt="Preview" style="display:block;max-height:100px;margin-top:10px;border-radius:6px;border:1px solid var(--border-color);">
                        </div>
                    </div>
                    <div class="row"><label>Deskripsi Tugas & Arahan</label><textarea name="p_desc[]" rows="2" required>Mengoordinasikan perumusan kebijakan teknis, penyelenggaraan pengembangan SDM komunikasi dan digital, serta beasiswa dan sertifikasi kompetensi nasional.</textarea></div>
                </div>

                <div class="leader-item-card" style="background:#f8fafc;padding:14px;border:1px solid var(--border-color);border-radius:var(--radius);position:relative;">
                    <button type="button" onclick="removeLeaderCard(this)" style="position:absolute;top:10px;right:10px;background:#ef4444;color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">Hapus</button>
                    <h4 style="color:var(--primary);margin-bottom:8px;">3. Plt. Kepala BBLSDM Komdigi Medan</h4>
                    <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="row"><label>Nama Lengkap & Gelar</label><input type="text" name="p_nama[]" value="Dr. Christiany Juditha, S.Sos., M.A." required></div>
                        <div class="row"><label>Jabatan</label><input type="text" name="p_jabatan[]" value="Plt. Kepala BBLSDM Komdigi Medan" required></div>
                    </div>
                    <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                        <div class="row"><label>Kategori Badge</label><input type="text" name="p_badge[]" value="BBLSDM Komdigi Medan" required></div>
                        <div class="row"><label>Foto Profil (PNG/JPG)</label>
                            <div class="drop" onclick="document.getElementById('fi-p3').click()"
                                 style="border:1.5px dashed var(--border-color);padding:16px;text-align:center;background:#f8fafc;cursor:pointer;border-radius:8px;transition:all 0.2s;">
                                <span style="color:var(--text-muted);font-size:13px;">Seret foto ke sini atau klik untuk memilih</span>
                                <b id="nm-p3" style="display:block;margin-top:6px;color:var(--primary);font-size:12px;"></b>
                            </div>
                            <input type="file" name="p_foto[]" id="fi-p3" accept="image/png,image/jpeg,image/jpg" style="display:none" onchange="previewFile(this,'nm-p3','prev-p3')">
                            <img id="prev-p3" src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=600&auto=format&fit=crop" alt="Preview" style="display:block;max-height:100px;margin-top:10px;border-radius:6px;border:1px solid var(--border-color);">
                        </div>
                    </div>
                    <div class="row"><label>Deskripsi Tugas & Arahan</label><textarea name="p_desc[]" rows="2" required>Mengendalikan pelaksanaan pelatihan dan uji kompetensi SDM digital di 8 Provinsi wilayah kerja Sumatera & Kalimantan Barat berbasis nilai BerAKHLAK.</textarea></div>
                </div>
            </div>

            <div style="margin-top:16px;">
                <button type="button" class="btn btn2" onclick="openAddLeaderModal()">+ Tambah Profil Petinggi Lainnya</button>
            </div>

            <h3 style="color:var(--primary);margin-top:24px;margin-bottom:10px;">Kelola Visi & Misi Komdigi</h3>
            <div class="row">
                <label>Visi Kementerian Komdigi</label>
                <textarea name="visi" rows="2" required>Mewujudkan Indonesia Digital yang Inklusif, Berdaya Saing, Berkelanjutan, dan Mendukung Transformasi Digital Nasional Berbasis SDM Unggul.</textarea>
            </div>

            <button type="submit" class="btn">Simpan Perubahan Profil & Visi Misi</button>
        </form>
    </div>
</div>

<!-- MODAL TAMBAH PETINGGI BARU -->
<div id="modal-add-leader" class="lightbox">
    <div class="lightbox-content" style="max-width:540px;width:100%;padding:24px;">
        <button class="lightbox-close" onclick="closeAddLeaderModal()" aria-label="Tutup">&times;</button>
        <h3 style="font-size:16px;color:var(--primary);margin-bottom:6px;">Form Tambah Profil Petinggi Baru</h3>
        <p style="font-size:12px;color:var(--text-muted);margin-bottom:14px;">Masukkan data profil pejabat / petinggi Komdigi baru di bawah ini:</p>

        <form id="form-new-leader" onsubmit="submitNewLeader(event)">
            <div class="row">
                <label for="nl-nama">Nama Lengkap & Gelar</label>
                <input type="text" id="nl-nama" placeholder="Contoh: Dr. Ir. H. Ahmad Sudirman, M.T." required>
            </div>
            <div class="row">
                <label for="nl-jabatan">Jabatan Resmi</label>
                <input type="text" id="nl-jabatan" placeholder="Contoh: Direktur Jenderal Aplikasi Informatika" required>
            </div>
            <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                <div class="row">
                    <label for="nl-badge">Badge Organisasi</label>
                    <input type="text" id="nl-badge" placeholder="Contoh: Ditjen Aptika" required>
                </div>
                <div class="row">
                    <label for="nl-foto">Foto Profil (PNG/JPG)</label>
                    <div class="drop" onclick="document.getElementById('nl-foto-file').click()"
                         style="border:1.5px dashed var(--border-color);padding:16px;text-align:center;background:#f8fafc;cursor:pointer;border-radius:8px;transition:all 0.2s;">
                        <span style="color:var(--text-muted);font-size:13px;">Seret foto ke sini atau klik untuk memilih</span>
                        <b id="nm-nl-foto" style="display:block;margin-top:6px;color:var(--primary);font-size:12px;"></b>
                    </div>
                    <input type="file" id="nl-foto-file" name="nl_foto" accept="image/png,image/jpeg,image/jpg" style="display:none" onchange="previewFile(this,'nm-nl-foto','prev-nl-foto')">
                    <img id="prev-nl-foto" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=600&auto=format&fit=crop" alt="Preview" style="display:block;max-height:100px;margin-top:10px;border-radius:6px;border:1px solid var(--border-color);">
                </div>
            </div>
            <div class="row">
                <label for="nl-desc">Deskripsi Tugas / Bio Ringkas</label>
                <textarea id="nl-desc" rows="2" placeholder="Tuliskan arahan dan tanggung jawab jabatan..." required></textarea>
            </div>

            <div style="margin-top:16px;display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" class="btn btn2" onclick="closeAddLeaderModal()">Batal</button>
                <button type="submit" class="btn">Tambahkan ke Daftar Petinggi</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddLeaderModal() {
    document.getElementById('modal-add-leader').classList.add('active');
}
function closeAddLeaderModal() {
    document.getElementById('modal-add-leader').classList.remove('active');
}

function submitNewLeader(e) {
    e.preventDefault();
    var nama = document.getElementById('nl-nama').value;
    var jabatan = document.getElementById('nl-jabatan').value;
    var badge = document.getElementById('nl-badge').value;
    var fotoInput = document.getElementById('nl-foto-file');
    var desc = document.getElementById('nl-desc').value;

    var container = document.getElementById('leaders-container');
    var count = container.children.length + 1;

    // Read file as base64 for preview in form
    var fotoBase64 = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=600&auto=format&fit=crop';
    if (fotoInput.files && fotoInput.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            fotoBase64 = e.target.result;
            appendLeaderCard();
        };
        reader.readAsDataURL(fotoInput.files[0]);
    } else {
        appendLeaderCard();
    }

    function appendLeaderCard() {
        var newCard = document.createElement('div');
        newCard.className = 'leader-item-card';
        newCard.style.cssText = 'background:#f8fafc;padding:14px;border:1px solid var(--border-color);border-radius:var(--radius);position:relative;animation:modalZoomIn 0.3s ease;';
        newCard.innerHTML = `
            <button type="button" onclick="removeLeaderCard(this)" style="position:absolute;top:10px;right:10px;background:#ef4444;color:#fff;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">Hapus</button>
            <h4 style="color:var(--primary);margin-bottom:8px;">${count}. ${nama}</h4>
            <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                <div class="row"><label>Nama Lengkap & Gelar</label><input type="text" name="p_nama[]" value="${nama}" required></div>
                <div class="row"><label>Jabatan</label><input type="text" name="p_jabatan[]" value="${jabatan}" required></div>
            </div>
            <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:10px;">
                <div class="row"><label>Kategori Badge</label><input type="text" name="p_badge[]" value="${badge}" required></div>
                <div class="row"><label>Foto Profil (PNG/JPG)</label>
                    <div class="drop" onclick="document.getElementById('fi-p${count}').click()"
                         style="border:1.5px dashed var(--border-color);padding:16px;text-align:center;background:#f8fafc;cursor:pointer;border-radius:8px;transition:all 0.2s;">
                        <span style="color:var(--text-muted);font-size:13px;">Seret foto ke sini atau klik untuk memilih</span>
                        <b id="nm-p${count}" style="display:block;margin-top:6px;color:var(--primary);font-size:12px;"></b>
                    </div>
                    <input type="file" name="p_foto[]" id="fi-p${count}" accept="image/png,image/jpeg,image/jpg" style="display:none" onchange="previewFile(this,'nm-p${count}','prev-p${count}')">
                    <img id="prev-p${count}" src="${fotoBase64}" alt="Preview" style="display:block;max-height:100px;margin-top:10px;border-radius:6px;border:1px solid var(--border-color);">
                </div>
            </div>
            <div class="row"><label>Deskripsi Tugas & Arahan</label><textarea name="p_desc[]" rows="2" required>${desc}</textarea></div>
        `;

        container.appendChild(newCard);
        closeAddLeaderModal();
        document.getElementById('form-new-leader').reset();
        document.getElementById('prev-nl-foto').src = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=600&auto=format&fit=crop';
        document.getElementById('nm-nl-foto').textContent = '';
        showAdminToast('Profil petinggi baru (' + nama + ') berhasil ditambahkan!');
    }
}

function removeLeaderCard(btn) {
    if (confirm('Apakah Anda yakin ingin menghapus kartu profil petinggi ini?')) {
        var card = btn.closest('.leader-item-card');
        card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        card.style.opacity = '0';
        card.style.transform = 'scale(0.9)';
        setTimeout(function() { card.remove(); }, 300);
    }
}

function saveProfilTentang(e) {
    e.preventDefault();
    showAdminToast('Seluruh data profil petinggi & Visi Misi Komdigi berhasil disimpan!');
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