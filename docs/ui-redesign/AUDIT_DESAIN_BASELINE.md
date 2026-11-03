# Audit Desain Baseline — Sistem Kerjasama BBLSDM Komdigi Medan

| Item | Keterangan |
|---|---|
| Tanggal audit | 3 November 2026 |
| Commit acuan | `2349106 docs: tambah standar desain web pemerintah ke AGENTS.md` (working tree bersih) |
| Sifat | Read-only. Tidak ada file aplikasi yang diubah. Dokumen ini satu-satunya berkas baru. |
| Cakupan | 16 view Blade + 6 file CSS + 2 layout admin/user + 1 layout publik + partial header/footer |
| Metode | Pembacaan kode, penghitungan statis (regex + skrip Node di luar repo), hitung kontras manual, verifikasi route |
| Acuan rubric | `AGENTS.md` bagian "Standar desain web pemerintah", 10 kriteria × 0/1/2 |
| Tidak diaudit | `resources/views/app.blade.php` (starter kit React/Inertia, aturan K4), isi database, kualitas file gambar, performa runtime |

## Cara membaca skor

- **2** = memenuhi standar; **1** = sebagian, ada kekurangan yang terlihat; **0** = gagal/tidak ada.
- Rubrik: K1 hierarki visual, K2 ketenangan (tidak ramai), K3 konsistensi komponen, K4 tipografi, K5 warna, K6 tata letak & spasi, K7 identitas & kepercayaan, K8 alur & microcopy, K9 aksesibilitas visual, K10 bebas AI slop.
- Target `AGENTS.md`: **≥16/20 dan tidak ada kriteria bernilai 0**. Karena itu, halaman dengan total **<10** otomatis melanggar syarat "tanpa nilai 0" — itu sendiri temuan, bukan efek pembulatan.
- Setiap skor di bawah disertai bukti `file:baris` atau angka dari Bagian 3. Angka dihitung pada commit di atas.

---

## 1. Ringkasan eksekutif

1. **Belum ada halaman yang memenuhi target 16/20.** Rata-rata 14 halaman = **8,5/20**; nilai tertinggi 14 (login admin), terendah 5 (draf mitra). Lima halaman mencapai ≥10, nol halaman mencapai ≥16.
2. **Akar masalah bukan halaman, tapi lapisan CSS bersama.** Ada 189 warna unik di 6 file CSS (212 bila CSS inline pengajuan dihitung), 31 ukuran font, 19 nilai radius, 44 nilai bayangan, 82 `!important`, dan 12 deklarasi `backdrop-filter`. Token sudah ada (`shared/tokens.css`, 18 nilai warna: 16 solid + 2 rgba) tetapi di-override ulang oleh `:root` di `user/css/style.css:4-14`.
3. **Ciri "AI slop" paling jelas: glassmorphism + glow + gradasi.** Komentar `landing.css:2` bahkan menulis "glassmorphism" sebagai tujuan desain; sidebar memakai `blur(20px) saturate(180%)` (`components.css:39-43`), badge berglow (`:198-202`), titik berdenyut (`:420-432`), footer bergradasi + watermark raksasa (`:584-603`).
4. **Kepercayaan publik paling banyak bocor di landing, tentang, dan draf.** Statistik 500+/1.2M+/99.8% tanpa sumber (`landing.blade.php:149-165`), foto Unsplash dipakai sebagai "Foto Profil" pimpinan (`admin/tentang.blade.php:36,58,80,131`), 37 kemunculan "AI" (19 di antaranya di halaman draf) untuk fitur yang hanya `setTimeout` (`user/draf.blade.php:100-178`), dan `alert()` sukses palsu di 3 tempat.
5. **Aksesibilitas dasar belum ada di seluruh area.** Nol skip link, nol `aria-expanded`, satu-satunya `role="dialog"` ada di modal wilayah landing (`landing.blade.php:288`); halaman pengajuan dan login mitra tidak punya `<h1>`; teks `#94a3b8` di atas putih hanya **2,56:1** (gagal AA).

---

## 2. Tabel skor per halaman

| Halaman | K1 | K2 | K3 | K4 | K5 | K6 | K7 | K8 | K9 | K10 | Total | Status |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Landing | 1 | 0 | 1 | 1 | 1 | 1 | 0 | 0 | 0 | 1 | **6** | jauh dari target |
| Pengajuan | 1 | 1 | 1 | 0 | 1 | 0 | 1 | 0 | 0 | 1 | **6** | jauh dari target |
| Login mitra | 0 | 2 | 1 | 1 | 1 | 2 | 1 | 1 | 1 | 1 | **11** | belum target |
| Login admin | 1 | 2 | 1 | 2 | 1 | 2 | 1 | 1 | 2 | 1 | **14** | belum target |
| Mitra — status | 1 | 1 | 1 | 1 | 1 | 1 | 0 | 0 | 1 | 1 | **8** | jauh dari target |
| Mitra — profil | 1 | 1 | 1 | 0 | 1 | 2 | 1 | 2 | 1 | 1 | **11** | belum target |
| Mitra — draf/generate | 1 | 0 | 0 | 1 | 1 | 1 | 0 | 0 | 0 | 1 | **5** | paling buruk |
| Admin — inbox | 1 | 0 | 1 | 1 | 1 | 1 | 1 | 1 | 0 | 1 | **8** | jauh dari target |
| Admin — CMS (`_cms`) | 1 | 1 | 1 | 1 | 1 | 2 | 1 | 1 | 1 | 1 | **11** | belum target |
| Admin — mitra draf | 1 | 1 | 1 | 1 | 1 | 1 | 0 | 0 | 1 | 1 | **8** | jauh dari target |
| Admin — tentang | 1 | 1 | 0 | 1 | 1 | 1 | 0 | 0 | 1 | 1 | **7** | jauh dari target |
| Layout publik | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | 1 | **10** | belum target |
| Layout admin | 1 | 0 | 1 | 1 | 1 | 1 | 1 | 1 | 0 | 1 | **8** | jauh dari target |
| Layout mitra | 1 | 0 | 0 | 1 | 1 | 0 | 1 | 1 | 0 | 1 | **6** | jauh dari target |
| Admin dashboard | — | — | — | — | — | — | — | — | — | — | **—** | halaman tidak ada |

Rata-rata 14 halaman: **119/14 = 8,5 dari 20 (42,5%)**. Halaman ≥16: **0**. Halaman ≥10: **5**.

Catatan: tidak ada route "dashboard admin". Halaman pertama area admin adalah `admin.inbox`; dashboard mitra adalah `mitra.status`. Keduanya dinilai apa adanya, tanpa mengarang halaman yang tidak ada.

### 2.1 Bukti dan alasan — Landing (6)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | `<h1>` berisi tagline "Sinergi Digital Nusantara" (`landing.blade.php:107`), bukan nama layanan/instansi; CTA utama jelas (`:112`) tetapi lima blok (hero, statistik, tentang, berita, galeri) saling bersaing. |
| K2 | 0 | Tiga `radial-gradient` + `background-attachment: fixed` (`landing.css:6-14`), kartu statistik berlapis glass (`landing.blade.php:150-163`), watermark footer (`components.css:590-603`). |
| K3 | 1 | Kartu statistik/berita/tentang seragam, tetapi memakai sistem penamaan sendiri (`.hero-ref-*`, `.stat-card`, `.news-card`) di luar komponen bersama. |
| K4 | 1 | 17 ukuran font di `landing.css`; teks isi turun ke 13–14px, di bawah standar 16px. |
| K5 | 1 | 56 warna unik di `landing.css`, di luar 18 token; aksen kuning resmi tidak dipakai. |
| K6 | 1 | Grid hero 2 kolom dan statistik 4 kolom rapi di desktop, tetapi jarak antar-bagian tidak berirama tetap. |
| K7 | 0 | Statistik 500+/1.2M+/99.8% tanpa sumber dan tanpa tanggal (`landing.blade.php:149-165`); foto Unsplash; footer menampilkan alamat Jakarta, bukan Medan (`footer-komdigi.blade.php:45-51`). |
| K8 | 0 | "Baca selengkapnya" menuju `href="#"` (`landing.blade.php:255`); kartu pimpinan memanggil `el.dataset.detail` yang tidak pernah ditulis di markup (`:356` vs `:179`). |
| K9 | 0 | `#94a3b8` pada putih 2,56:1; tidak ada skip link; `<main>` tidak ada (`layouts/user-public.blade.php:20-24`). |
| K10 | 1 | Tanpa emoji dan tanpa slider otomatis (baik), tetapi glass + tiga gradasi + glow badge tetap ciri template AI. |

### 2.2 Bukti dan alasan — Pengajuan (6)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | Hanya `<h2>` (`pengajuan.blade.php:7`), tidak ada `<h1>`; alur form satu kolom mudah diikuti. |
| K2 | 1 | Latar putih dan satu tujuan, tetapi indikator "3 langkah" dekoratif (`:9-24`) menambah elemen tanpa fungsi. |
| K3 | 1 | Kelas `.pjn-*` konsisten secara internal, namun tidak memakai token maupun `.row`/`.box` bersama. |
| K4 | 0 | Input 13px (`:592`), petunjuk 11px `#94a3b8` (`:584`), label 12px — memicu zoom otomatis di iOS. |
| K5 | 1 | Palet biru konsisten, tetapi `#94a3b8` gagal kontras dan gradasi hero 135° menambah warna keempat (`:352`). |
| K6 | 0 | `margin: 90px 0 20px` (`:355`) bertumpuk dengan `padding-top: 92px` (`user/css/style.css:641-643`) → ±182px ruang kosong sebelum judul. |
| K7 | 1 | Badge "Portal Resmi Komdigi" dan penjelasan alur login menumbuhkan kepercayaan; tidak ada informasi kontak bantuan. |
| K8 | 0 | Indikator langkah tidak mengikuti validasi; tidak ada ringkasan error di atas form; pesan sukses memakai banner sendiri (`:27-38`), bukan pola layout. |
| K9 | 0 | Checkbox asli disembunyikan `display: none` (`:763`), fokus input `outline: none` (`:596`), tidak ada `<h1>`/skip link. |
| K10 | 1 | Tanpa emoji, tetapi gradasi + 6 `!important` inline (`:469-480`) adalah pola "template instan". |

### 2.3 Bukti dan alasan — Login mitra (11)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 0 | Hanya `<h2>` "Masuk Portal Mitra Komdigi" (`login.blade.php:7`); tidak ada `<h1>` di halaman maupun layout publik. |
| K2 | 2 | Satu kartu 420px, tanpa glass/gradasi latar, tanpa elemen bersaing. |
| K3 | 1 | Memakai `.box`, `.row`, `.btn`; tetapi `.demo-btn` hanya ada di halaman ini dan tidak ada di komponen bersama. |
| K4 | 1 | Ukuran teks wajar, namun teks bantuan 12px dan tidak ada penegasan batas 16px pada input. |
| K5 | 1 | Biru konsisten; tombol memakai gradasi + glow (`user/css/style.css:295-296`). |
| K6 | 2 | Kartu terpusat `max-width: 420px` (`login.blade.php:5`), jarak antar-elemen wajar. |
| K7 | 1 | Logo dan nama unit ada, tetapi kredensial demo `user1 / 123456` tampil permanen dan sudah terisi (`:12,20,24`) — di situs pemerintah ini merusak kredibilitas. |
| K8 | 1 | Alur masuk jelas; sayangnya `POST /login` hanya mengalihkan tanpa validasi dan halaman tidak punya blok error. |
| K9 | 1 | `<label for>` benar (`:19-24`), tetapi tidak ada skip link dan teks muted 12px berisiko kontras. |
| K10 | 1 | Tanpa emoji/glass, tetapi tombol gradasi + glow tetap ciri template. |

### 2.4 Bukti dan alasan — Login admin (14)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | `<h1>` "KEMKOMDIGI ADMIN" (`admin/login.blade.php:22`) lebih menonjol daripada `<h2>` aksi "Masuk Panel Admin" (`:30`). |
| K2 | 2 | Satu kartu `max-width: 420px`, tanpa glass, tanpa gradasi latar. |
| K3 | 1 | Konsisten memakai `.box`, `.row`, `.btn` admin; hanya `.demo-box` yang khas halaman ini. |
| K4 | 2 | Font dimuat sekali (`:8-11`), ukuran 14–16px, hierarki judul jelas. |
| K5 | 1 | Biru konsisten, tetapi kotak demo memakai hijau/biru tambahan. |
| K6 | 2 | `margin: 30px auto` + `max-width: 420px` (`:28`), proporsi tenang. |
| K7 | 1 | Logo dan nama panel ada; penamaan "KEMKOMDIGI" tidak konsisten dengan footer "KOMDIGI". |
| K8 | 1 | Tanpa pesan error yang terlihat; kredensial demo benar karena dibatasi `@if(config('app.debug'))` (`:34`). |
| K9 | 2 | Label eksplisit, urutan fokus alami, kontras teks utama memadai. |
| K10 | 1 | Tanpa glass, tetapi `.btn` gradasi tetap dipakai. |

### 2.5 Bukti dan alasan — Mitra status (8)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | `<h1>` dari layout (`layouts/user.blade.php:161`) + dua kotak `.box`; tombol aksi dan informasi bercampur di satu blok. |
| K2 | 1 | Dua kotak dan dua tabel, cukup tenang; badge status memakai titik `●` dekoratif. |
| K3 | 1 | Tabel `.info` dan `.data` dipakai konsisten, tetapi tanpa pembungkus `.scroll`. |
| K4 | 1 | Ukuran 13–14px, di bawah standar teks isi 16px. |
| K5 | 1 | Warna status hijau/oranye inline (`:68,75`) di luar token. |
| K6 | 1 | Jarak antar-kotak wajar (`:50`), tetapi tabel 5 kolom akan sempit di 360px. |
| K7 | 0 | Dua baris MoU dengan nomor dan tanggal spesifik ditulis mati di view (`:64-77`) — tampak seperti data nyata padahal contoh. |
| K8 | 0 | "Generate Draf Proposal AI" (`:45`) mengarah ke fitur palsu; "Lihat Dokumen AI"/"Edit Draf AI" (`:69,76`) juga. |
| K9 | 1 | Tabel tanpa `caption`/`scope`, tetapi struktur baris–kolom terbaca; tidak ada skip link. |
| K10 | 1 | Tanpa emoji/glass; badge `●` dan teks "AI" berulang termasuk pola template. |

### 2.6 Bukti dan alasan — Mitra profil (11)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | `<h1>` + satu form, tetapi blok foto, grid 2 kolom, dan catatan kecil bersaing perhatian. |
| K2 | 1 | Latar putih dan satu tujuan; 16 inline style menambah variasi visual. |
| K3 | 1 | Memakai `.row`/`.box`, tetapi banner sukses dibuat ulang dengan inline style (`profile.blade.php:6-14`). |
| K4 | 0 | Input 13px (`user/css/style.css:318`), teks bantuan 11–12px (`profile.blade.php:29,38`). |
| K5 | 1 | Biru konsisten; hijau banner sukses hardcoded (`:7-11`). |
| K6 | 2 | Grid 2 kolom dengan `gap: 14px` (`:34`) dan blok foto sejajar — proporsi rapi. |
| K7 | 1 | Data instansi dan PIC jelas; foto default bergantung `$mitra['foto']`. |
| K8 | 2 | Satu form, satu tombol simpan, pesan sukses eksplisit; alur mudah ditebak. |
| K9 | 1 | `label for` lengkap, tetapi tombol "Ganti Foto" memicu input tersembunyi via `onclick` (`:30`) dan tidak ada skip link. |
| K10 | 1 | Tanpa glass/gradasi, tetapi inline style dan ikon SVG inline memperbanyak variasi. |

### 2.7 Bukti dan alasan — Mitra draf/generate (5)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | `<h1>` dari layout, tetapi halaman menumpuk generator, loader, toolbar, editor, dan tombol aksi tanpa prioritas. |
| K2 | 0 | Animasi progres + spinner + 38 inline style + toolbar 8 tombol kecil (`draf.blade.php:70-75`). |
| K3 | 0 | Warna dan jarak ditulis inline (`:75,79,81,84`), tidak memakai `.btn`/`.row`; toolbar tanpa kelas. |
| K4 | 1 | Teks 12–14px, campur `px` dan bobot; dokumen pratinjau memakai 13px. |
| K5 | 1 | Biru + hijau pesan + abu; tanpa token. |
| K6 | 1 | Dua kolom (form kiri, editor kanan) masuk akal di desktop, tetapi tanpa aturan 360px. |
| K7 | 0 | NIP dan nama penanda tangan dikarang di klien (`:173`), nomor surat dibuat `new Date()` (`:140`). |
| K8 | 0 | "Buat Draf dengan AI" hanya `setTimeout` (`:100-178`); "Kirim Langsung ke Admin" memunculkan `alert` sukses (`:83`); "Simpan" tidak menyimpan (`:185-189`). |
| K9 | 0 | `contenteditable` tanpa `role`/`aria-label` (`:79`), tombol toolbar hanya `<u>U</u>`, tidak ada `<h1>` halaman. |
| K10 | 1 | Tanpa emoji, tetapi "AI" 19×, spinner, dan progress bar adalah bahasa template. |

### 2.8 Bukti dan alasan — Admin inbox (8)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | `<h1>` dari layout (`layouts/admin.blade.php:193`), tabel utama jelas; banner penolakan dan modal bersaing di atasnya. |
| K2 | 0 | 69 inline style, 12 warna hardcoded, banner merah penuh (`inbox.blade.php:8-25`), tombol approve bergradasi hijau (`:128`). |
| K3 | 1 | Memakai `.btn`, `.box`, `.scroll`; tetapi varian merah/hijau ditulis inline, bukan varian komponen. |
| K4 | 1 | 12–14px, banyak bobot; cukup terbaca tetapi di bawah standar teks isi. |
| K5 | 1 | Merah/hijau/biru bercampur tanpa token status. |
| K6 | 1 | Tabel dibungkus `.scroll` (`:58`) — baik; tetapi 8 kolom di 360px tetap menuntut scroll horizontal. |
| K7 | 1 | Data pengajuan nyata dari controller; tetapi teks "KEMKOMDIGI" vs "KOMDIGI" dan salinan surat otomatis terasa berlebihan. |
| K8 | 1 | Alur review → tolak/setujui jelas; tetapi data dikirim ke JS lewat atribut `onclick` (`:87`) dan label "Approve" tidak konsisten bahasa. |
| K9 | 0 | Modal tanpa `role="dialog"`/`aria-modal`, tombol tutup `✕` tanpa `aria-label` (`:101`), tabel tanpa `<thead>`/`scope` (`:59-60`). |
| K10 | 1 | Tanpa emoji, tetapi gradasi tombol + glow + `●` status adalah pola template. |

### 2.9 Bukti dan alasan — Admin CMS `_cms` (11)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | Tombol "Tambah" + judul tabel + form; hierarki cukup, tetapi aksi hapus dan edit sejajar tanpa penekanan. |
| K2 | 1 | 30 inline style, tetapi tanpa gradasi latar; modal memakai `.lightbox` bersama. |
| K3 | 1 | Struktur tabel + modal konsisten; tombol hapus merah ditulis inline (`_cms.blade.php:63`). |
| K4 | 1 | 13–14px; label form kecil. |
| K5 | 1 | Biru + merah hapus; tanpa token status. |
| K6 | 2 | Tabel dalam `.scroll` (`:41`), form modal berjarak wajar. |
| K7 | 1 | Data artikel/galeri nyata; judul modal "Edit Item" generik. |
| K8 | 1 | Alur CRUD jelas; `confirm()` bawaan browser untuk hapus (`:61`). |
| K9 | 1 | Modal tanpa `role`/`aria-modal`, tombol `✕` tanpa `aria-label` (`:78`); tabel tanpa `<thead>`. |
| K10 | 1 | Tanpa emoji/glass, tetap terasa admin template. |

### 2.10 Bukti dan alasan — Admin mitra draf (8)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | `<h1>` + paragraf penjelas panjang + tabel; penjelasan mengalahkan data. |
| K2 | 1 | Satu tabel, tetapi 10 inline style dan badge status inline (`:31`). |
| K3 | 1 | Memakai `.data`/`.scroll`; tombol aksi bercampur `.btn` dan `.btn2`. |
| K4 | 1 | 12–14px. |
| K5 | 1 | Hijau status inline di luar token. |
| K6 | 1 | `.scroll` membungkus tabel (`:12`), 6 kolom tetap lebar. |
| K7 | 0 | "Perpanjang Masa Aktif" dan "Unduh Draf AI" tidak punya backend; aksi terlihat resmi padahal kosong. |
| K8 | 0 | "Unduh Draf AI" hanya `alert` (`:33`); "Simpan" perpanjangan hanya mengubah DOM (`:112-138`); nama instansi disisipkan ke JS tanpa escape (`:34`). |
| K9 | 1 | Struktur tabel baik (`thead`/`tbody`), tetapi tombol aksi tanpa konteks baris untuk pembaca layar. |
| K10 | 1 | Tanpa emoji/glass; badge `●` dan label "AI" berulang. |

### 2.11 Bukti dan alasan — Admin tentang (7)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | Empat blok pimpinan berurutan, tetapi tanpa navigasi bagian dan tanpa ringkasan. |
| K2 | 1 | 55 inline style dan empat kartu identik dengan foto besar. |
| K3 | 0 | Hampir seluruh tampilan ditulis inline; kelas komponen nyaris tidak dipakai. |
| K4 | 1 | 12–14px, campur bobot dan warna. |
| K5 | 1 | Biru + abu + hijau; tanpa token. |
| K6 | 1 | Grid kartu konsisten, tetapi tinggi foto dan teks tidak seragam. |
| K7 | 0 | Foto Unsplash dipakai sebagai "Foto Profil" pejabat (`:36,58,80,131`) — menyesatkan untuk halaman resmi. |
| K8 | 0 | Form memakai `onsubmit="saveProfilTentang(event)"` + `preventDefault` (`:15,224-227`) sehingga route `admin.tentang.update` yang sudah ada tidak pernah dijalankan; muncul toast sukses palsu. |
| K9 | 1 | Input berlabel, tetapi tombol tutup/ikon tanpa `aria-label`; tidak ada skip link. |
| K10 | 1 | Tanpa emoji, tetapi foto stok dan toast sukses adalah pola demo. |

### 2.12 Bukti dan alasan — Layout publik (10)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | Header tetap + satu wadah konten; tetapi `div.wrap` dipakai sebagai pengganti `<main>` (`user-public.blade.php:22-24`). |
| K2 | 1 | Header/footer tenang, tetapi footer memuat 11 tautan ke beranda yang sama. |
| K3 | 1 | Komponen header/footer dipakai semua halaman publik. |
| K4 | 1 | Font dimuat sekali (`:7-10`), tetapi ukuran dasar 14px (`user/css/style.css:25`). |
| K5 | 1 | Biru konsisten; aksen kuning tidak dipakai. |
| K6 | 1 | `padding-top: 92px` untuk navbar tetap (`style.css:641-643`) terlalu besar untuk halaman non-hero. |
| K7 | 1 | Logo, nama kementerian, alamat, telepon, email ada — tetapi alamat Jakarta dan nama "KOMDIGI" berbeda dari header "KEMKOMDIGI". |
| K8 | 1 | Navigasi jelas; "Masuk Mitra" + "Pengajuan Baru" dua CTA bersaing. |
| K9 | 1 | Tombol ikon punya `aria-label` (`:31,39`), tetapi tidak ada skip link, `<main>`, atau `aria-expanded` pada menu mobile. |
| K10 | 1 | Shell tanpa glass; tetapi memuat `landing.css` (glass) di halaman beranda. |

### 2.13 Bukti dan alasan — Layout admin (8)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | Sidebar + header + `<h1>` halaman jelas (`:191-193`). |
| K2 | 0 | Sidebar glass `blur(20px)` + bayangan 30px (`components.css:39-43`), badge glow (`:198-202`). |
| K3 | 1 | Sidebar dan kartu konsisten antar halaman admin. |
| K4 | 1 | Ukuran judul dan isi wajar; label sidebar 11–12px. |
| K5 | 1 | Biru gelap konsisten, tetapi gradient sidebar menyimpang dari token. |
| K6 | 1 | Sidebar 275px + konten fleksibel; di 360px menjadi drawer (baik). |
| K7 | 1 | Identitas "KEMKOMDIGI" + logo; tautan "Sistem & Tautan Luar" menunjuk `http://127.0.0.1:8000` (`:114`) sehingga mati di produksi. |
| K8 | 1 | Navigasi jelas; tetapi sidebar diduplikasi dari layout mitra dan status aktif hanya lewat warna. |
| K9 | 0 | Tidak ada skip link, tombol buka sidebar tanpa `aria-expanded`/`aria-controls`, backdrop `<div onclick>` tanpa keyboard (`:22`). |
| K10 | 1 | Tanpa emoji; glass + glow tetap ciri template. |

### 2.14 Bukti dan alasan — Layout mitra (6)

| K | Skor | Bukti & alasan |
|---|---|---|
| K1 | 1 | Sidebar + header + `<h1>`; tetapi kartu status besar mengambil perhatian dari konten halaman. |
| K2 | 0 | Glass sidebar + `pulse-dot` berdenyut (`:147`) + badge "Aktif" (`:51`). |
| K3 | 0 | Markup sidebar menduplikasi layout admin dan memakai kelas `admin-app-body`/`admin-sidebar` di area mitra (`:17,24`). |
| K4 | 1 | Ukuran teks 12–14px; nama instansi 18px. |
| K5 | 1 | Biru konsisten dengan area user, tetapi berbeda dari token. |
| K6 | 0 | Kartu status dan progress bar ditulis inline (`:168-187`) dengan lebar tetap 85%; sidebar `100vh` sticky tanpa penyesuaian 360px. |
| K7 | 0 | "365 Hari Tersisa" dan progress 85% ditulis mati (`:178,181`) tanpa kaitan data. |
| K8 | 0 | "Perpanjang Masa Aktif" hanya `alert()` sukses (`:186`). |
| K9 | 0 | Tidak ada skip link/`aria-expanded`; status hanya dibedakan warna. |
| K10 | 1 | Tanpa emoji; glass + glow + gradient tombol. |

---

## 3. Metrik kuantitatif (Langkah 1)

### 3.1 Metrik per file CSS

| File | Baris | Warna unik | Ukuran font | Radius | Bayangan | linear | radial | backdrop-filter | keyframes | transition | `!important` |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `shared/tokens.css` | 102 | 18 | 8 | 4 | 2 | 0 | 0 | 0 | 0 | 0 | 0 |
| `shared/base.css` | 124 | 0 | — | — | — | 0 | 0 | 0 | 0 | — | 7 |
| `shared/components.css` | 679 | 61 | 12 | 9 | 12 | 4 | 2 | 2 | 1 | 10 | 0 |
| `admin/css/style.css` | 254 | 11 | — | — | — | 0 | 0 | 1 | 2 | 3 | 27 |
| `user/css/style.css` | 1224 | 87 | 22 | 16 | 22 | 8 | 1 | 6 | 1 | 27 | 45 |
| `user/css/landing.css` | 825 | 56 | 17 | 7 | 10 | 2 | 4 | 3 | 1 | 11 | 3 |
| **Total** | **3208** | **189** (212 termasuk CSS inline pengajuan) | **31** | **19** | **44** | **14** | **7** | **12** | **5** | **51** | **82** |

Catatan: kolom warna/font/radius/bayangan adalah **nilai unik per file**; angka pada baris Total adalah **gabungan unik**, bukan penjumlahan kolom (nilai yang sama dipakai di beberapa file). `backdrop-filter` dihitung tanpa prefiks `-webkit-` (total baris yang menyebut properti ini: 24). `tokens.css` mendefinisikan 18 nilai warna (16 solid + 2 rgba), 4 radius (3 utama + alias `--radius: 8px`), dan 2 bayangan; `user/css/style.css:4-14` menimpa `:root` dengan 9 variabel lain (`--primary`, `--accent`, `--radius`, …) sehingga token resmi tidak pernah menjadi sumber tunggal.

### 3.2 Sepuluh warna terbanyak (6 file CSS + CSS inline pengajuan)

| Warna | Kemunculan | Catatan |
|---|---|---|
| `#ffffff` | 79 | Netral, wajar |
| `#1d4ed8` | 18 | Biru aksen sekunder, bukan token (`--brand-*`) |
| `#fff` | 17 | Duplikat penulisan `#ffffff` |
| `#0b3a6b` | 17 | Biru utama area user, bukan token |
| `#38bdf8` | 10 | Biru terang (cyan) untuk badge/glow |
| `#cbd5e1` | 9 | Border, kontras rendah terhadap putih |
| `#2563eb` | 8 | Varian biru lain |
| `#f1f5f9` | 7 | Latar abu |
| `rgba(255,255,255,0.08)` | 7 | Border transparan pada sidebar |
| `#64748b` | 7 | Muted, 4,76:1 (lolos tipis) |

Kesimpulan: netral (`#fff`, `#f1f5f9`) wajar; masalahnya **enam biru berbeda** (`#0b3a6b`, `#12418A`, `#1B5BBF`, `#2563eb`, `#1d4ed8`, `#38bdf8`) yang semuanya dipakai untuk peran serupa. Merah bahaya `#ef4444` (6×) juga ditulis langsung tanpa token `--danger`.

### 3.3 Tipografi

- **31 ukuran font unik**, termasuk `9px`, `10px`, `10.5px`, `11px`, `11px !important`, `11.5px`.
- **5 nilai `font-weight`** (400, 500, 600, 700, 800) — standar menyarankan maksimal 3 per area.
- **4 deklarasi `font-family`**; selebihnya mewarisi.
- Font dimuat **sekali per layout** (`layouts/user-public.blade.php:8-10`, `layouts/admin.blade.php:9-11`, `admin/login.blade.php:9-11`) — sesuai aturan proyek; tidak ada `@import` ganda.
- Ukuran dasar `body` di area user = **14px** (`user/css/style.css:25`), form input = **13px** (`:318`; `pengajuan.blade.php:592`) → melanggar batas 16px untuk mencegah zoom iOS.

### 3.4 Radius dan bayangan

- **19 nilai `border-radius`**: 0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, `50%`, `999px`, `9999px`, `var(--radius-sm)`, `var(--radius)`, `12px !important` — padahal token hanya menyediakan 3 radius utama (`--radius-sm` 6px, `--radius-md` 10px, `--radius-lg` 14px) plus satu alias lama `--radius: 8px`.
- **44 nilai `box-shadow` unik**. Beberapa contoh yang menyimpang dari standar "bayangan tipis": `0 0 0 0 rgba(34,197,94,0.7)` beranimasi (`components.css:425-431`), `0 0 8px rgba(56,189,248,0.35)` (`:201`), `4px 0 30px rgba(2,10,26,0.35)` (`:43`), `0 5px 18px rgba(22,83,206,0.4)` saat hover (`user/css/style.css:296`).

### 3.5 Efek visual

| Efek | Jumlah | Lokasi utama |
|---|---|---|
| `linear-gradient` | 14 | `.btn` (`user/css/style.css:295-296`), sidebar (`components.css:39`), footer (`:584`), hero pengajuan (`pengajuan.blade.php:352`), approve inbox (`inbox.blade.php:128`) |
| `radial-gradient` | 7 | Latar landing (`landing.css:8-11`), mask footer (`components.css:600-601`), watermark |
| `conic-gradient` | 0 | — |
| `backdrop-filter` | 12 (24 dengan prefiks) | Sidebar admin/mitra, glass landing, navbar |
| `@keyframes` | 5 deklarasi (4 nama) | `pulseGreen` (`components.css:428`), `modalPopIn` (`user/css/style.css:243`, `admin/css/style.css:205`), `slideInDown` (`admin/css/style.css:250`), `lpHeroRise` (`landing.css:195`) |
| Deklarasi `animation` | 6 | Termasuk titik status berdenyut terus-menerus |
| `transition` | 51 | Hampir semua elemen interaktif |
| `prefers-reduced-motion` | 2 | `base.css:114`, `landing.css:812` — hanya menutup sebagian animasi |

### 3.6 Metrik markup Blade (16 view)

| Metrik | Jumlah | Sebaran terbesar |
|---|---|---|
| Atribut `style="..."` inline | **279** | `admin/inbox` 69, `admin/tentang` 55, `user/draf` 38, `admin/_cms` 30, `user/profile` 16, `layouts/user` 12, `landing` 11, `pengajuan` 10, `admin/mitra_draf` 10, `login` 7, `admin/login` 7, `layouts/user-public` 6, `layouts/admin` 4, `user/status` 4, header 0, footer 0 |
| Warna hardcoded di atribut (bukan token) | **300** | `inbox`, `tentang`, `_cms`, `draf` |
| Handler inline `on*=` | **89** | `onclick`, `onsubmit`, `onkeydown` |
| `!important` inline | **6** | Semua di `pengajuan.blade.php:469-480` |
| Emoji piktografis (U+1F300–U+1FAFF) | **0** | Baik — tidak ada emoji sebagai ikon |
| Simbol dekoratif literal | **6** | `✕` 4 (`inbox:101,125,206`, `_cms:78`), `✓` 1 (`inbox:129`), `↗` 1 (`inbox:36`) |
| Simbol lewat entitas HTML | **16** | `&times;` 7 (tombol tutup), `&rarr;` 3, `&bull;` 3, `●` 3 |
| `<h1>` per halaman | 4 sumber | `landing:107`, `admin/login:22`, dua layout via `@yield('title')`; **pengajuan & login mitra tanpa `<h1>`** |
| `<main>` | 2 | Hanya di layout admin (`:191`) dan user (`:159`) |
| Skip link | **0 markup** | Gaya `.skip-link` sudah disiapkan di `base.css:86-108`, tetapi tidak dipakai satu halaman pun |
| `aria-expanded` / `aria-controls` | **0** | Menu mobile & sidebar |
| `role="dialog"` | **1** | `landing.blade.php:288` (tanpa `aria-modal`/`aria-labelledby`) |
| `aria-live` | **0** | Flash message tidak diumumkan |
| `<table>` | 7 | 3 dibungkus `.scroll` (admin), 4 tidak (user + modal) |
| `thead`/`scope` | 2 `thead` | `inbox` & `_cms` tanpa `thead`, tidak ada `scope="col"` di mana pun |

### 3.7 Breakpoint

Dipakai: **1024, 992 (×2), 960, 768 (×4), 640, 620, 480 (×2)**. Tidak ada **360** dan **1280** sesuai standar proyek; jumlah total media query = 13 untuk 3208 baris CSS. Ini menjelaskan mengapa beberapa halaman terlihat baik di 768–1280 tetapi berantakan di 360.

### 3.8 Ikon

- **48 SVG inline**, tanpa pustaka ikon (tidak ada Font Awesome/Lucide/Heroicons).
- **5 nilai `stroke-width`**: 2 (×32), 2.2 (×11), 1.8 (×3), 2.4 (×1), 2.5 (×2) — ketebalan garis tidak seragam.
- Footer memakai **5 ikon merek berisi (filled)** di samping 40+ ikon garis → dua gaya bercampur.
- Ukuran dipatok atribut `width`/`height` (18, 20, 22, 24) tanpa kelas ukuran.

### 3.9 Varian tombol dan kartu

- **17 selektor tombol**: `.btn`, `.btn2`, `.btn-s`, `.demo-btn`, `.social-btn`, `.nav-btn-outline`, `.nav-btn-cyan`, `.hero-ref-btn-main`, `.hero-ref-btn-sub`, `.hero-card-user-btn`, `.hero-card-cta`, `.mobile-menu-btn`, `.mobile-nav-btn-outline`, `.mobile-nav-btn-pill`, `.sidebar-toggle-btn`, `.sidebar-close-btn`, `.profile-logout-btn` — ditambah `.pjn-btn-submit`, `.wilayah-gmaps-link`, dan override inline `.btn` menjadi hijau/merah/WhatsApp (`inbox.blade.php:124,128`, `_cms.blade.php:63`).
- **±15 wadah kartu**: `.box`, `.card`, `.pjn-card`, `.hero-ref-card`, `.stat-card`, `.leader-card`, `.news-card`, `.about-card`, `.values-card`, `.gallery-item`, `.sidebar-profile-card`, `.status-card`, `.leader-item-card`, `.wilayah-modal-box`, `.lightbox-content`.
- Dari 17 tombol, hanya **satu** yang benar-benar dipakai sebagai aksi primer di tiap halaman (`.btn` atau `.hero-ref-btn-main`) — sisanya variasi yang tumpang tindih.

### 3.10 Kontras (dihitung manual pada latar putih)

| Warna | Rasio | Status | Dipakai di |
|---|---|---|---|
| `#94a3b8` | **2,56:1** | Gagal AA | `pengajuan.blade.php:583-584` (hint 11px) |
| `#71859c` | **3,79:1** | Gagal AA | teks sekunder area publik |
| `#64748b` | 4,76:1 | Lolos tipis | `--text-muted` area user |
| `#5B6675` | 5,83:1 | Lolos | token `--text-muted` |

Rasio dihitung untuk teks biasa (bukan teks besar). Warna di atas gradasi/transparan tidak dihitung karena bergantung latar aktual.

### 3.11 Bahasa dan klaim

- **37 kemunculan "AI"**: `user/draf` 19, `admin/mitra_draf` 6, `layouts/admin` 4, `layouts/user` 4, `user/status` 3, `landing` 1.
- **7 kemunculan frasa "8 provinsi"** (antara lain `landing.blade.php:288`), ditambah statistik "8 / Provinsi Layanan" (`:159-160`) — angka tanpa tautan sumber.
- Angka statis lain: `500+`, `1.2M+`, `99.8%`, `365 Hari Tersisa`, `width: 85%`, dua baris MoU di `user/status.blade.php:64-77`, data pimpinan di `landing.blade.php:24-90`.
- Klise yang **tidak** ditemukan (baik): "solusi masa depan", "inovasi tanpa batas", "terdepan", "terbaik", "lorem ipsum" — nol.
- Klise yang **ditemukan**: "Sinergi Digital Nusantara" (`landing:107`), "Portal Resmi Komdigi" (`pengajuan:6`), "Transformasi Digital" (`layouts/user:185`).

### 3.12 Tombol primer terlihat per halaman

| Halaman | Tombol primer | Catatan |
|---|---|---|
| Landing | "Ajukan Kerja Sama" (`:112-114`) | 1 primer + beberapa tautan sekunder |
| Pengajuan | "Kirim Pengajuan" | 1 primer, wajar |
| Login mitra | "Masuk ke Dashboard Mitra" (`:27`) | 1 primer + 1 tombol demo setara (`:13`) |
| Login admin | "Masuk" | 1 primer + 2 tombol demo (`:35-40`) |
| Mitra status | "Edit Profil Instansi" (`:44`) + "Generate Draf Proposal AI" (`:45`) | **2 primer bersaing** |
| Mitra profil | "Simpan" | 1 primer |
| Mitra draf | "Buat Draf dengan AI" + "Simpan" + "Kirim Langsung" (`:82-83`) | **3 aksi setara** |
| Admin inbox | "Review" per baris (`:87`) + "Approve" (`:127`) | 2 primer dalam satu alur |
| Admin CMS | "Tambah …" | 1 primer |
| Admin mitra draf | "Unduh Draf AI" + "Perpanjang" (`:33-34`) | 2 aksi setara, keduanya belum berfungsi |
| Admin tentang | "Simpan" | 1 primer (tetapi tidak menyimpan) |

---

## 4. Temuan

### 4.1 Ciri "ramai"

1. **Enam biru untuk satu peran.** `#0b3a6b` (user), `#12418A`/`#1B5BBF` (token), `#1d4ed8` (aksen user), `#2563eb` dan `#38bdf8` (glow/cyan). Tidak ada satu sumber warna.
2. **Tiga sistem token hidup bersama.** `shared/tokens.css` (18 nilai warna), override `:root` di `user/css/style.css:4-14` (9 variabel), dan variabel lokal `admin/css/style.css`. Halaman memakai campuran ketiganya.
3. **Semua kartu diberi border + bayangan + radius berbeda.** 44 nilai bayangan dan 19 nilai radius membuat tiap kotak terasa dari sistem berbeda.
4. **17 varian tombol, 15 wadah kartu.** Perbedaan antar-varian sering hanya 2px padding atau 1px border.
5. **Animasi di hampir semua elemen.** 51 `transition` + 6 `animation` + `reveal` pada kartu; hanya 2 blok `prefers-reduced-motion` yang menutupi sebagian.
6. **Footer ramai tetapi tidak informatif.** 11 tautan menuju beranda `komdigi.go.id` yang sama (`footer-komdigi.blade.php:15-35`), ditambah watermark logo 640px (`components.css:590-603`).
7. **Indikator dekoratif.** "3 langkah" di pengajuan (`pengajuan.blade.php:9-24`) tidak terhubung ke validasi; badge "Aktif"/"AI"/"Portal Resmi" berulang.

### 4.2 Ciri "AI slop"

1. **Glassmorphism eksplisit.** Komentar `landing.css:2` menulis tujuan desain "glassmorphism"; `.glass` memakai `blur(18px) saturate(150%)` (`:29-37`), sidebar `blur(20px) saturate(180%)` (`components.css:39-43`).
2. **Glow dan denyut.** `box-shadow: 0 0 8px rgba(56,189,248,.35)` pada badge (`components.css:198-202`), `pulse-dot` tak berhenti (`:420-432`), glow tombol saat hover (`user/css/style.css:295-297`).
3. **Gradasi dekoratif.** Tombol `linear-gradient(90deg, …)` (`user/css/style.css:295`), hero 135° (`pengajuan.blade.php:352`), footer 180° (`components.css:584`), approve hijau (`inbox.blade.php:128`), latar landing 3 radial (`landing.css:8-11`).
4. **Foto stok sebagai wajah instansi.** Empat potret Unsplash dipakai sebagai foto pimpinan (`admin/tentang.blade.php:36,58,80,131`), satu foto Unsplash berblur di hero (`user/css/style.css:661-671`).
5. **Statistik bulat tanpa sumber.** 500+, 1.2M+, 99.8%, 8 provinsi, 365 hari, 85%.
6. **Fitur "AI" yang hanya animasi.** `setTimeout` + progress bar (`user/draf.blade.php:100-178`), `alert('…siap diunduh!')` (`admin/mitra_draf.blade.php:33`).
7. **Emoji: tidak ada.** Ini satu hal yang sudah benar. Namun masih ada 22 simbol dekoratif: 6 literal (`✕` 4, `✓` 1, `↗` 1) dan 16 entitas (`&times;` 7, `&rarr;` 3, `&bull;` 3, `●` 3). Tujuh `&times;` pada tombol tutup sebaiknya menjadi SVG ber-`aria-label`.

### 4.3 Yang sudah baik — pertahankan

1. **Font dimuat sekali per layout** (`user-public:7-10`, `admin:9-11`, `admin/login:9-11`) tanpa `@import` ganda.
2. **`tokens.css` sudah ada** dengan 18 nilai warna, 4 radius, 2 bayangan, dan skala spasi; tinggal dijadikan satu-satunya sumber.
3. **`base.css` sudah punya `:focus-visible` dan `prefers-reduced-motion`** (`:114`).
4. **Tabel admin dibungkus `.scroll`** (`components.css:503-507`; `inbox:58`, `_cms:41`, `mitra_draf:12`) — pola responsif yang benar.
5. **`aria-label` pada tombol ikon**: menu mobile (`header:26`), tutup sidebar (`layouts/admin:35`, `layouts/user:34`), lightbox (`user-public:31`), kembali ke atas (`footer:69`), 5 tautan sosial (`footer:58-62`).
6. **`<label for>` konsisten** di form login (`login:19-24`) dan profil (`profile:36`).
7. **Modal wilayah landing sudah `role="dialog"` + tutup dengan Esc** (`landing:288,350-352`) — jadikan pola untuk semua modal.
8. **Kredensial demo admin dibatasi `@if(config('app.debug'))`** (`admin/login:34`) — pola benar yang belum diterapkan di login mitra.
9. **Identitas footer lengkap**: logo, nama kementerian, alamat, telepon, email, media sosial.
10. **Tidak ada marquee, slider otomatis, pop-up saat muat, atau emoji.** Empat pantangan terbesar sudah terhindar.
11. **Struktur semantik dasar benar** di tabel `mitra_draf` dan `user/status` (`thead`/`tbody`).
12. **Flash message memakai `session('ok')` + `$errors`** di kedua layout (`admin:196-209`, `user:190-205`).

### 4.4 Inkonsistensi antararea

| Aspek | Kondisi | Bukti |
|---|---|---|
| Token | Token resmi di-override `:root` lain | `user/css/style.css:4-14` vs `shared/tokens.css` |
| Tombol | `.btn` gradasi di area user, solid di admin; varian hijau/merah lewat inline | `user/css/style.css:295`, `inbox:124,128`, `_cms:63` |
| Radius | 19 nilai untuk 3 token | Bagian 3.4 |
| Bayangan | 44 nilai unik | Bagian 3.4 |
| Tipografi | 31 ukuran, dasar 14px, input 13px | Bagian 3.3 |
| Form | `.row` sama nama, tinggi/label beda antararea | `user/css/style.css:318` vs `admin/css/style.css` |
| Tabel | Admin pakai `.scroll`, area user tidak | `status:8,53`, `draf:171` |
| Sidebar | Markup diduplikasi; kelas `admin-*` dipakai di area mitra | `layouts/user.blade.php:17,24` vs `layouts/admin.blade.php:18,25` |
| Nama instansi | "KEMKOMDIGI" (header/nav/judul) vs "KOMDIGI" (footer) vs "BBLSDM Komdigi Medan" | `header:9`, `footer:8`, `landing:133` |
| Alamat | Footer Jakarta, modal wilayah Medan | `footer:45-51`, `landing:80` |
| Flash | 4 pola: layout admin, layout user, `profile:6-14`, `pengajuan:27-38` | — |
| Tautan internal | Sidebar admin menunjuk `http://127.0.0.1:8000` | `layouts/admin.blade.php:114` |
| Bahasa aksi | "Approve", "Review", "Justify", "Export PDF" bercampur Indonesia | `inbox:87,129`, `draf:74-75` |
| Komponen duplikat | `.toast-notification` disalin ke area user (`user/css/style.css:332`) padahal hanya dipakai di admin; animasi `slideInDown` hanya didefinisikan di `admin/css/style.css:250` | `mitra_draf:142`, `tentang:231` |

### 4.5 Alur dan microcopy

1. **Login mitra tidak punya jalur gagal.** `POST /login` hanya mengalihkan ke `mitra.status`; `login.blade.php` tidak menampilkan blok error, sementara kredensial `user1/123456` sudah terisi dan selalu tampil (`:12,20,24`) — berbeda dari login admin yang aman karena `APP_DEBUG`.
2. **Tiga aksi sukses palsu.** "Perpanjang Masa Aktif" (`layouts/user:186`), "Unduh Draf AI" (`admin/mitra_draf:33`), "Kirim Langsung ke Admin" (`user/draf:83`) memunculkan `alert` tanpa permintaan jaringan.
3. **Dua form yang tidak menyimpan.** `admin/tentang` memanggil `preventDefault` (`:224-227`) walau route `admin.tentang.update` ada; perpanjangan di `mitra_draf` hanya mengubah DOM (`:112-138`).
4. **Satu halaman yang tidak punya judul.** Pengajuan hanya `<h2>` (`:7`); login mitra juga (`login:7`). Pembaca layar dan tab browser kehilangan konteks.
5. **Data contoh yang menyamar sebagai data nyata.** MoU `045/KS/KOMDIGI/2026` dan `088/KS/BBLSDM/2026` (`status:64-77`), nomor surat `new Date()` (`draf:140`), NIP dan tanda tangan (`draf:173`), "365 Hari Tersisa" (`layouts/user:178`).
6. **Tautan mati dan nilai `undefined`.** "Baca selengkapnya" `href="#"` (`landing:255`); `el.dataset.detail` tidak ada di kartu (`landing:356` vs `:179`) sehingga kolom detail modal kosong.
7. **Label tombol tidak seragam.** "Approve Pengajuan" vs "Setujui", "Review" vs "Tinjau", "Justify" vs "Rata kiri-kanan", "Export PDF" vs "Cetak".
8. **Tidak ada umpan balik untuk aksi lambat.** Tidak ada status loading pada form simpan; pengguna tidak tahu apakah klik berhasil.

### 4.6 Catatan untuk backend (bukan bagian pekerjaan UI)

- `POST /login` belum memvalidasi kredensial dan tidak mengembalikan error.
- `admin.tentang.update` menyimpan ke session, bukan database.
- Tidak ada route untuk perpanjangan masa aktif mitra.
- Tidak ada route dashboard admin.
- Generator draf dan pengiriman draf ke admin belum punya endpoint.
- Statistik landing, data pimpinan, dan baris MoU belum terhubung ke database/seed.
- `jabatan_pic`, validasi bidang, `min:30`, dan route perpanjangan masuk Track B (K6).

---

## 5. Rekomendasi

Diurutkan menurut **dampak visual dibagi risiko perubahan**. Prompt mengacu pada `docs/ui-redesign/RENCANA_LANJUTAN_PROMPT_REVISI.md` (P1–P10); **M** = keputusan manual, **Track B** = backend.

### R1. Hapus glassmorphism, glow, gradasi dekoratif
- **Masalah:** 12 deklarasi `backdrop-filter`, 5 keyframes, 44 bayangan; ciri paling kuat "AI slop" (Bagian 4.2).
- **Perbaikan:** ganti sidebar jadi biru solid `--brand-900`, hapus `backdrop-filter`, `pulse-dot`, glow badge, gradasi pada tombol/footer/approve. Sisakan gradasi hanya bila menutupi foto (hero) dan itupun opsional.
- **File:** `shared/components.css:39-43,198-202,420-432,584-603,517-518`; `user/css/style.css:210-211,295-297,496-508,782-783,879-880,1062-1063`; `landing.css:29-37,124-134,367-368`; `admin/css/style.css:179-180`; `inbox.blade.php:128`.
- **Prompt:** P1 lalu P3. **Dampak:** sangat tinggi. **Risiko:** rendah (murni visual).

### R2. Satukan token warna jadi satu sumber
- **Masalah:** 189–212 warna unik, enam biru, override `:root` (`user/css/style.css:4-14`).
- **Perbaikan:** hapus blok `:root` area user, ganti semua hardcoded ke `var(--brand-*)`/`var(--text*)`; pertahankan maksimal 3 warna non-netral per halaman.
- **File:** `user/css/style.css`, `admin/css/style.css`, `landing.css`, `components.css`, semua inline color di Blade.
- **Prompt:** P2. **Dampak:** sangat tinggi. **Risiko:** sedang (bisa mengubah tampilan tak sengaja; lakukan per area).

### R3. Perbaiki tipografi dan kontras
- **Masalah:** 31 ukuran font; dasar 14px; input 13px; `#94a3b8` 2,56:1.
- **Perbaikan:** skala 5 ukuran (14/16/18/24/32), teks isi minimal 16px, input 16px, ganti `#94a3b8` → `var(--text-muted)`, naikkan hint ke 14px.
- **File:** `shared/base.css`, `user/css/style.css:25,318`, `admin/css/style.css`, `pengajuan.blade.php:583-584,592`.
- **Prompt:** P2. **Dampak:** tinggi. **Risiko:** rendah.

### R4. Hentikan fitur yang berpura-pura jadi
- **Masalah:** tiga `alert` sukses, dua form tanpa simpan, generator `setTimeout`, NIP karangan.
- **Perbaikan:** nonaktifkan dengan label "Segera hadir" + `disabled`/`aria-disabled`, hapus `alert` sukses, hapus data karangan dari pratinjau. Jangan hapus tombol tanpa izin; ubah statusnya.
- **File:** `layouts/user.blade.php:178-186`, `admin/mitra_draf.blade.php:33-34,112-138`, `user/draf.blade.php:83,100-189`, `admin/tentang.blade.php:15,224-227`, `user/status.blade.php:44-45,69,76`.
- **Prompt:** P7, P8 (+K1/K2). **Dampak:** tinggi (kepercayaan). **Risiko:** rendah–sedang (perlu keputusan fitur mana yang "segera hadir").

### R5. Satukan varian tombol
- **Masalah:** 17 selektor + override inline hijau/merah/WhatsApp.
- **Perbaikan:** tiga varian resmi (primer, sekunder, bahaya) + satu ukuran kecil; hapus varian dekoratif; tombol bahaya lewat kelas, bukan inline.
- **File:** `shared/components.css`, `user/css/style.css:295-301`, `admin/css/style.css`, `inbox:124,128`, `_cms:63`.
- **Prompt:** P4. **Dampak:** tinggi. **Risiko:** rendah.

### R6. Satu modal bersama yang aksesibel + buang argumen JS inline
- **Masalah:** hanya 1 `role="dialog"`; `✕` tanpa `aria-label`; data masuk ke `onclick` (`inbox:87`, `mitra_draf:34`, `landing:181`).
- **Perbaikan:** komponen modal di `shared/js/ui.js` + markup bersama: `role="dialog"`, `aria-modal="true"`, `aria-labelledby`, fokus terkunci, Esc, scroll lock, tombol tutup ber-`aria-label`. Data lewat `data-*` + `addEventListener`, bukan string JS.
- **File:** `layouts/user-public:29-51`, `admin/inbox:95-131`, `admin/_cms:74-90`, `landing:288`, `shared/js/ui.js`.
- **Prompt:** P4. **Dampak:** tinggi. **Risiko:** sedang (menyentuh JS).

### R7. Tabel responsif dan semantik
- **Masalah:** 4 tabel tanpa `.scroll`; `inbox` dan `_cms` tanpa `thead`; nol `scope`; 8 kolom di 360px.
- **Perbaikan:** bungkus semua tabel dengan `.scroll`, tambahkan `thead`/`scope`, sembunyikan kolom sekunder di <640px, atau ubah jadi kartu bertumpuk untuk 360px.
- **File:** `user/status.blade.php:8,53`, `user/draf.blade.php:171`, `admin/inbox.blade.php:59`, `admin/_cms.blade.php:42`, `components.css:503-507`.
- **Prompt:** P4, P7, P8. **Dampak:** tinggi di mobile. **Risiko:** rendah.

### R8. Satu pola flash message
- **Masalah:** empat pola berbeda; tidak ada `aria-live`.
- **Perbaikan:** satu partial `alert` (sukses/gagal/info) dengan `role="status"`/`aria-live="polite"`, dipakai keempat layout/halaman.
- **File:** `layouts/admin:196-209`, `layouts/user:190-205`, `user/profile:6-14`, `pengajuan:27-38`.
- **Prompt:** P4. **Dampak:** sedang-tinggi. **Risiko:** rendah.

### R9. Rombak halaman pengajuan
- **Masalah:** 182px ruang kosong, input 13px, indikator 3 langkah palsu, 663 baris CSS + 604 baris JS inline, tanpa `<h1>`.
- **Perbaikan:** pindahkan CSS/JS ke aset, satu `<h1>`, satu kolom maksimal 720px, indikator langkah mengikuti validasi atau dihapus, label 16px, error ringkas di atas tombol kirim.
- **File:** `pengajuan.blade.php` (seluruh), `user/css/style.css:641-643`.
- **Prompt:** P5. **Dampak:** tinggi. **Risiko:** tinggi (halaman terbesar; perlu rencana dulu).

### R10. Skip link, `<main>`, `aria-expanded`
- **Masalah:** nol skip link, `<main>` hanya di dua layout dashboard, menu mobile tanpa `aria-expanded`.
- **Perbaikan:** tambah skip link di awal `<body>` (gaya `.skip-link` di `base.css:86-108` sudah tersedia), ubah `div.wrap` jadi `<main id="konten">`, tambah `aria-expanded`/`aria-controls` pada tombol menu dan sidebar, backdrop jadi `<button>`.
- **File:** `layouts/user-public.blade.php:20-24`, `partials/header.blade.php:26`, `layouts/admin.blade.php:22,35`, `layouts/user.blade.php:21,34,130`.
- **Prompt:** P3. **Dampak:** sedang (kepatuhan). **Risiko:** rendah.

### R11. Landing: statistik, tautan mati, "undefined"
- **Masalah:** angka tanpa sumber, `href="#"`, `data-detail` tidak ada.
- **Perbaikan:** pindahkan angka ke `config/tampilan_demo.php` dengan komentar "data contoh" (K5), tambahkan keterangan sumber/tanggal, ganti tautan berita jadi nonaktif "Segera hadir" atau hapus, tambahkan `data-detail` atau hapus parameter.
- **File:** `landing.blade.php:107,149-165,179-182,255,355-356`; config baru.
- **Prompt:** P9 (+M untuk sumber angka). **Dampak:** tinggi (kepercayaan). **Risiko:** rendah.

### R12. Identitas: nama, alamat, foto
- **Masalah:** tiga penamaan instansi, alamat Jakarta vs Medan, foto stok pejabat.
- **Perbaikan:** satu nama resmi di seluruh UI, alamat/kontak BBLSDM Medan di footer, ganti foto stok dengan placeholder netral atau foto resmi bila tersedia; jangan mengarang.
- **File:** `partials/header.blade.php:9`, `partials/footer-komdigi.blade.php:8,45-55`, `layouts/*` judul, `admin/tentang.blade.php:36,58,80,131`.
- **Prompt:** P3, P9 + **M** (konfirmasi nama/alamat/foto). **Dampak:** tinggi. **Risiko:** rendah, tetapi butuh data resmi.

### R13. Bersihkan inline style dan CSS mati
- **Masalah:** 279 inline style, 300 warna hardcoded, ±15 kelompok selektor mati (`.foot-*`, `.social-btn`, `.banner`, `.cols`, `.card`, `.misi-*`, `.sec*`, `.leader-desc`, `.hero-ref-container`), `.hide` dipakai tetapi tidak didefinisikan.
- **Perbaikan:** pindahkan gaya berulang ke komponen, hapus selektor mati, definisikan `.hide`.
- **File:** `admin/inbox`, `admin/tentang`, `user/draf`, `admin/_cms`, `components.css`, `landing.css`, `user/css/style.css`.
- **Prompt:** P1, P2. **Dampak:** sedang (perawatan). **Risiko:** rendah bila diverifikasi per file.

### R14. Normalisasi radius, bayangan, dan spasi
- **Masalah:** 19 radius, 44 bayangan, jarak tidak berirama (90px + 92px).
- **Perbaikan:** pakai hanya 3 radius token dan 2 bayangan (kartu, modal), skala spasi 4/8/12/16/24/32/48, hapus `margin: 90px`.
- **File:** semua CSS + `pengajuan.blade.php:355`, `user/css/style.css:641-643`.
- **Prompt:** P2. **Dampak:** sedang. **Risiko:** rendah.

### R15. Login mitra
- **Masalah:** tanpa `<h1>`, kredensial demo permanen dan terisi, tanpa blok error, 2 CTA setara.
- **Perbaikan:** `<h1>`, sembunyikan akun demo di balik `APP_DEBUG` seperti login admin, kosongkan `value`, tambah blok error, satu CTA primer.
- **File:** `login.blade.php:7,12,20,24,27`, `routes/web.php` (catatan backend).
- **Prompt:** P6. **Dampak:** sedang. **Risiko:** rendah.

---

## 6. Keterbatasan

1. **Audit berbasis kode, bukan render.** Tidak ada tangkapan layar; proporsi, keseimbangan ruang, dan "rasa keseluruhan" tidak dapat dinilai secara visual.
2. **Tidak menjalankan aplikasi.** Tidak ada pengukuran LCP/CLS, waktu muat font, atau perilaku nyata di 360px.
3. **Angka bersifat heuristik.** Dihitung dengan regex/skrip di luar repo; warna di komentar atau `rgba()` transparan dapat terhitung, dan nilai yang sama dengan penulisan berbeda (`#fff` vs `#ffffff`) bisa terpisah. Selisih ±5% mungkin terjadi.
4. **Kontras dihitung pada latar solid.** Tidak memperhitungkan teks di atas gradasi, glass, atau gambar.
5. **Kualitas aset gambar tidak dinilai.** Resolusi, hak pakai, dan ketajaman logo/foto tidak diperiksa.
6. **Tidak diuji dengan pembaca layar nyata.** Temuan aksesibilitas berasal dari struktur DOM dan atribut, bukan uji NVDA/VoiceOver.
7. **`app.blade.php` dikecualikan** (starter kit, aturan K4).
8. **Tidak memeriksa database/seed.** Tidak dapat memastikan apakah data MoU, statistik, dan pimpinan berasal dari data nyata.
9. **Skor bersifat penilaian.** Rubrik 0/1/2 mengandung pertimbangan; penilai lain dapat berbeda 1 poin per kriteria.
10. **Halaman admin dashboard tidak ada**, sehingga tidak dinilai dan tidak diberi skor.

---

## 7. Perlu konfirmasi

1. **Nama resmi untuk UI:** "KEMKOMDIGI", "KOMDIGI", atau "Kementerian Komunikasi dan Digital"? Ketiganya muncul (`header:9`, `footer:8`, `landing:133`).
2. **Alamat dan kontak resmi:** footer memakai Jakarta (`footer:45-55`), modal wilayah memakai Medan (`landing:80`). Mana yang benar untuk portal BBLSDM Medan?
3. **Cakupan "8 provinsi"** (`landing:159-160,288`): apakah angka ini benar dan boleh ditampilkan? Sumbernya apa?
4. **Statistik 500+/1.2M+/99.8%:** dipertahankan sebagai data contoh berlabel (K5), diganti data nyata, atau dihapus?
5. **Foto pimpinan:** apakah akan disediakan foto resmi? Sementara ini dipakai foto Unsplash.
6. **Login mitra:** apakah memang belum ada validasi kredensial? Apakah akun demo harus disembunyikan seperti login admin?
7. **Fitur draf/AI:** "Segera hadir" (K2) atau akan dikerjakan di Track B?
8. **Dashboard admin:** apakah perlu halaman terpisah, atau `admin.inbox` sudah dianggap beranda?
9. **Logo dan warna resmi:** apakah ada SVG/PNG resolusi tinggi dan kode warna brand final untuk mencocokkan token?
10. **`admin.tentang.update`:** apakah penyimpanan ke session ini final, atau menunggu backend?
11. **Data MoU di `user/status.blade.php:64-77`:** contoh atau nyata?
12. **Target browser instansi:** apakah masih ada pengguna Edge/IE mode atau browser lama yang harus didukung?

---

## Lampiran A — Berkas yang diperiksa

**CSS (6):** `shared/tokens.css`, `shared/base.css`, `shared/components.css`, `admin/css/style.css`, `user/css/style.css`, `user/css/landing.css`.

**View (16):** `landing.blade.php`, `pengajuan.blade.php`, `login.blade.php`, `admin/login.blade.php`, `user/status.blade.php`, `user/profile.blade.php`, `user/draf.blade.php`, `admin/inbox.blade.php`, `admin/_cms.blade.php` (+ `artikel`, `galeri`), `admin/mitra_draf.blade.php`, `admin/tentang.blade.php`, `layouts/admin.blade.php`, `layouts/user.blade.php`, `layouts/user-public.blade.php`, `partials/header.blade.php`, `partials/footer-komdigi.blade.php`.

**Route:** `routes/web.php`, `routes/admin.php`.

**Dokumen acuan:** `AGENTS.md`, `docs/ui-redesign/RENCANA_LANJUTAN_PROMPT_REVISI.md`, `docs/ui-redesign/LANGKAH_EKSEKUSI_DETAIL.md`.

## Lampiran B — Cara memverifikasi ulang

```
# jumlah inline style per file
Get-ChildItem -Recurse resources/views -Filter *.blade.php |
  ForEach-Object { "$($_.Name): " + ([regex]::Matches((Get-Content $_.FullName -Raw), 'style="').Count) }

# jumlah !important di CSS
Get-ChildItem public/assets -Recurse -Filter *.css |
  Select-String -Pattern '!important' | Measure-Object

# warna unik
Get-ChildItem public/assets -Recurse -Filter *.css |
  Select-String -Pattern '#[0-9a-fA-F]{3,8}|rgba?\([^)]+\)' -AllMatches |
  ForEach-Object { $_.Matches.Value.ToLower() } | Sort-Object -Unique
```

Untuk penilaian visual, buka tiap halaman pada lebar **360, 768, 1280** dan bandingkan dengan Bagian 2 dan 4. Skor pada dokumen ini adalah baseline; setelah P1–P10 dijalankan, audit yang sama dapat diulang untuk mengukur kenaikan.
