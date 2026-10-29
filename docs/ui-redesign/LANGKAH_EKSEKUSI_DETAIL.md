# Langkah Eksekusi Detail: Redesign UI Sistem Kerjasama

Folder proyek (disebut `[ROOT]` di dokumen ini):

```
D:\xampp\htdocs\PROJECT_KOMDIGI\kerjasama
```

Semua perintah dijalankan di terminal PowerShell yang sudah berada di `[ROOT]` (di VSCode: tekan `Ctrl + `` ` ``, lalu pastikan prompt-nya menunjukkan folder `kerjasama`).

---

## Tahap 0. Baca dulu hasil `git status` kamu

| Temuan | Artinya | Tindakan |
|---|---|---|
| `composer.json` berubah satu baris: `"php": "^8.3"` menjadi `"^8.4"` | Ini perubahan syarat versi PHP, tidak ada hubungannya dengan UI. `composer.lock` tidak ikut berubah, jadi file kunci dan `composer.json` jadi tidak sinkron. Kemungkinan besar efek tidak sengaja saat memasang/menjalankan sesuatu. | **Kembalikan ke semula** (Langkah 1.1). `^8.3` sudah mencakup PHP 8.4 sehingga aplikasi tetap jalan. |
| `tentang.blade.php`, `landing.blade.php`, `layouts/user-public.blade.php`, `routes/admin.php` berubah | Pekerjaan kamu yang belum disimpan ke Git | Disimpan dulu lewat commit (Langkah 1.3) |
| `public/assets/user/css/landing.css` untracked | File baru yang belum pernah di-commit | Ikut di-commit (Langkah 1.3) |

Kalau `composer.json` memang sengaja kamu ubah (misalnya server hosting wajib PHP 8.4), lewati Langkah 1.1 dan commit terpisah: `git add composer.json` lalu `git commit -m "chore: naikkan syarat PHP ke 8.4"`.

---

## Tahap 1. Amankan pekerjaan dan buat branch

### 1.1 Kembalikan `composer.json`

```
git restore composer.json
```

### 1.2 Pindah ke branch baru untuk redesign

```
git switch -c ui/redesign
```

Perubahan yang belum di-commit ikut terbawa ke branch baru. Branch `main` tidak tersentuh.

### 1.3 Commit pekerjaan yang sedang berjalan

```
git add resources routes public
git commit -m "chore: simpan WIP landing dan tentang sebelum redesign UI"
git status
```

Hasil `git status` harus: `nothing to commit, working tree clean`. Kalau masih ada file tersisa, berhenti dan periksa dulu.

### 1.4 (Opsional tapi disarankan) Backup ke GitHub

```
git push -u origin ui/redesign
```

---

## Tahap 2. Simpan dokumen rencana di dalam proyek

Tujuannya supaya OpenCode bisa membaca dokumennya dan kamu tidak kehilangan file.

### 2.1 Buat folder

```
New-Item -ItemType Directory -Path docs\ui-redesign
```

Hasilnya:

```
[ROOT]\docs\ui-redesign\
```

### 2.2 Pindahkan file yang kamu unduh dari Claude ke folder itu

```
[ROOT]\docs\ui-redesign\RENCANA_UI_SISTEM_KERJASAMA.md
[ROOT]\docs\ui-redesign\RENCANA_LANJUTAN_PROMPT_REVISI.md
[ROOT]\docs\ui-redesign\LANGKAH_EKSEKUSI_DETAIL.md
```

(Cukup seret file dari folder Downloads ke folder itu di panel Explorer VSCode.)

---

## Tahap 3. Buat atau perbarui `AGENTS.md`

File ini dibaca OpenCode setiap sesi, jadi aturan desainnya selalu ikut.

### 3.1 Cek dulu apakah sudah ada

```
Get-ChildItem AGENTS.md, CLAUDE.md -ErrorAction SilentlyContinue
```

Proyek Laravel modern kadang sudah membawa `AGENTS.md` sendiri.

- **Kalau belum ada:** buat file baru di `[ROOT]\AGENTS.md` (di VSCode: klik kanan area kosong di Explorer, New File, ketik `AGENTS.md`).
- **Kalau sudah ada:** JANGAN dihapus atau ditimpa. Buka, gulir ke paling bawah, beri baris kosong, lalu tambahkan judul `# === Redesign UI (tambahan) ===` diikuti isi di bawah.

### 3.2 Isi file

Buka `docs\ui-redesign\RENCANA_LANJUTAN_PROMPT_REVISI.md`, cari **bagian C**, salin isi kotak kode di dalamnya (tanpa tanda ``` di awal dan akhir), tempel ke `AGENTS.md`.

### 3.3 Edit bagian "Keputusan proyek"

Di dalam `AGENTS.md`, cari baris `K1` sampai `K6`. Baca satu per satu dan sesuaikan:

- **K1** (fitur yang belum selesai di landing dan tentang): apakah memang belum selesai? Kalau fitur itu sudah final, ubah kalimatnya.
- **K5** (angka statis seperti statistik dan "365 Hari Tersisa"): tanyakan ke pembimbing magang apakah ada data aslinya.
- **K6** (validasi `jabatan_pic`, dan seterusnya): biarkan sebagai Track B kecuali pembimbing minta lain.

### 3.4 Commit

```
git add AGENTS.md docs
git commit -m "docs: tambah AGENTS.md dan dokumen rencana redesign UI"
```

---

## Tahap 4. Peta file: apa yang diubah dan apa yang akan dibuat

Kamu **tidak perlu** membuat file-file di bawah secara manual. OpenCode yang membuatnya lewat prompt. Tabel ini supaya kamu tahu apa yang seharusnya terjadi, dan bisa mendeteksi kalau OpenCode menyentuh file yang salah.

Nama file baru bisa sedikit berbeda dari tabel (OpenCode kadang memilih nama lain). Itu wajar selama lokasinya masuk akal.

| Prompt | File yang DIUBAH (sudah ada) | File yang DIBUAT (baru) |
|---|---|---|
| **P1** CSS bersih | `public\assets\user\css\style.css`, `public\assets\admin\css\style.css`, `resources\views\layouts\admin.blade.php`, `layouts\user.blade.php`, `layouts\user-public.blade.php` (hanya baris `<link>` CSS) | `public\assets\shared\css\components.css` |
| **P2** Tokens dan dasar | Ketiga layout di atas, tiga file CSS lama | `public\assets\shared\css\tokens.css`, `public\assets\shared\css\base.css`, satu partial untuk tag CSS/JS di `resources\views\partials\` |
| **P3** Kerangka layout | `resources\views\partials\header.blade.php`, ketiga layout | `resources\views\partials\sidebar-admin.blade.php`, `partials\sidebar-user.blade.php`, mungkin `partials\footer.blade.php` |
| **P4** Komponen bersama | CSS shared (menambah isi) | `public\assets\shared\js\ui.js`, komponen Blade di `resources\views\components\` (misalnya `modal.blade.php`, `flash.blade.php`, `badge.blade.php`, `field.blade.php`) |
| **P5** Pengajuan | `resources\views\pengajuan.blade.php` | mungkin folder `resources\views\pengajuan\` berisi pecahan partial |
| **P6** Login | `resources\views\login.blade.php`, `resources\views\admin\login.blade.php` | (tidak ada) |
| **P7** Admin | `resources\views\admin\inbox.blade.php`, `admin\_cms.blade.php`, `admin\mitra_draf.blade.php`, `admin\tentang.blade.php` | (mungkin partial modal admin) |
| **P8** User | `resources\views\user\status.blade.php`, `user\profile.blade.php`, `user\draf.blade.php`, `layouts\user.blade.php` | `config\tampilan_demo.php` (bila belum dibuat) |
| **P9** Landing | `resources\views\landing.blade.php`, `public\assets\user\css\landing.css` | `config\tampilan_demo.php` (bila belum dibuat di P8) |
| **P10** QA | `README.md` dan perbaikan kecil | (tidak ada) |

### File yang TIDAK boleh berubah selama tahap UI

Kalau `git diff --stat` menunjukkan salah satunya berubah, kembalikan dengan `git restore <nama file>`:

- `composer.json`, `composer.lock`, `package.json`, `.env`
- `routes\web.php`, `routes\admin.php` (kecuali Track B)
- Folder `app\` (controller dan model)
- Folder `database\` (migrasi)
- Folder `resources\js\` dan file starter kit React/Inertia

### Yang perlu kamu siapkan manual (kalau ada)

- **Logo resmi Komdigi:** simpan di `public\assets\shared\img\logo-komdigi.svg` (atau `.png`). Beri tahu OpenCode path-nya di prompt P3 bila berbeda dari logo yang sekarang dipakai. Jangan mengubah bentuk atau warna logo.
- **Font self-host (hanya jika jaringan kantor membatasi internet):** unduh file `.woff2` Plus Jakarta Sans dan Inter, simpan di `public\assets\shared\fonts\`. Kalau tidak, biarkan OpenCode memakai satu tag `<link>` Google Fonts.
- **Foto kegiatan asli** untuk landing: simpan di `public\assets\shared\img\kegiatan\`, bila sudah ada.

---

## Tahap 5. Siapkan lingkungan kerja

Buka **dua terminal** di VSCode (ikon `+` di panel terminal), keduanya di `[ROOT]`.

**Terminal 1: server aplikasi**

```
php artisan serve
```

Buka `http://localhost:8000`. Kalau aplikasi memakai database, nyalakan juga MySQL lewat XAMPP Control Panel.

**Terminal 2: OpenCode**

```
opencode
```

Hal yang perlu kamu cek di OpenCode:

- Biasanya ada dua mode, **Plan** (hanya membaca) dan **Build** (boleh mengubah file), dan bisa dipindah dengan tombol `Tab`. Kalau versimu berbeda, cek petunjuk di bagian bawah layar OpenCode atau ketik `/help`.
- Untuk semua prompt P1 sampai P10, pakai mode **Build**.
- Cara memakai prompt: buka `RENCANA_LANJUTAN_PROMPT_REVISI.md`, bagian D, salin **hanya isi kotak kode** prompt yang dimaksud, tempel ke OpenCode, tekan Enter.

---

## Tahap 6. Rutinitas tiap prompt (ulangi untuk P1 sampai P10)

Lakukan persis urutan ini, satu prompt per putaran.

### 6.1 Sebelum mulai

```
git status
```

Harus bersih. Kalau tidak, commit atau batalkan dulu sisa pekerjaan sebelumnya.

### 6.2 Jalankan prompt

Tempel prompt ke OpenCode. Untuk P4, P5, dan P7, OpenCode akan menulis rencana dulu dan menunggu. Baca rencananya: apakah file yang akan disentuh sesuai tabel Tahap 4? Kalau ya, balas `lanjut`. Kalau ada yang aneh, tulis koreksi, atau tempel rencananya ke Claude untuk di-review.

### 6.3 Periksa apa yang berubah

Setelah OpenCode selesai:

```
git status
git diff --stat
```

Cocokkan dengan tabel Tahap 4. Kalau ada file di daftar "TIDAK boleh berubah", kembalikan:

```
git restore routes/web.php
```

(ganti dengan nama file yang bersangkutan)

### 6.4 Uji di browser

1. Buka halaman terkait, tekan `Ctrl + F5` (muat ulang tanpa cache).
2. Tekan `F12`, lalu `Ctrl + Shift + M` (mode perangkat), uji lebar **360**, **768**, dan **1280**.
3. Coba tekan `Tab` berulang: fokus harus terlihat dan urutannya masuk akal.
4. Coba fungsi utamanya (kirim form, buka modal, login).
5. Lihat tab Console di DevTools: tidak boleh ada tulisan merah baru.

Daftar URL ada di `RENCANA_LANJUTAN_PROMPT_REVISI.md` bagian E.

### 6.5 Kalau hasilnya bagus: commit

```
git status
git add -A
git commit -m "ui: P1 bersihkan CSS dan hilangkan duplikasi"
```

Ganti pesan commit sesuai prompt (P2, P3, dan seterusnya). Cek `git status` sebelum `git add -A` untuk memastikan tidak ada `.env` atau `composer.json` di daftar.

### 6.6 Kalau hasilnya buruk: batalkan

Batalkan perubahan di file yang sudah ada:

```
git restore .
```

File baru yang dibuat OpenCode tidak ikut terhapus. Lihat dulu apa yang akan dihapus:

```
git clean -nd
```

Kalau daftarnya memang hanya file buatan OpenCode, hapus dengan:

```
git clean -fd
```

Ini aman karena pekerjaan sebelumnya sudah ter-commit. Lalu jalankan ulang prompt dengan tambahan instruksi koreksi.

---

## Tahap 7. Urutan, waktu, dan titik berhenti

| Urutan | Prompt | Titik berhenti yang disarankan |
|---|---|---|
| 1 | P1 CSS bersih | Setelah ini semua halaman harus tampil sama seperti sebelumnya. Kalau ada yang berubah, itu efek perbaikan komentar CSS; periksa. |
| 2 | P2 Tokens dan aksesibilitas | Cek fokus keyboard dan warna tombol utama. |
| 3 | P3 Kerangka layout | Cek menu mobile dan sidebar. |
| 4 | P4 Komponen bersama [RENCANA DULU] | Baca rencana dengan teliti. Ini fondasi prompt berikutnya. |
| 5 | P5 Pengajuan [RENCANA DULU] | **Boleh tunjukkan ke pembimbing.** Ini halaman paling berantakan dan paling terlihat perubahannya. |
| 6 | P6 Login | Cek kredensial demo tidak tampil. |
| 7 | P7 Admin [RENCANA DULU] | Uji tiap modal dan tiap tabel di HP. |
| 8 | P8 User | Uji semua halaman setelah login mitra. |
| 9 | P9 Landing | **Titik tunjuk utama ke pembimbing.** |
| 10 | P10 QA | Terakhir. |

Jangan loncat urutan. P4 sampai P9 bergantung pada P1 sampai P3.

---

## Tahap 8. Setelah semua selesai

```
git switch main
git merge ui/redesign
```

Atau, kalau pembimbing mau mereview dulu, push branch dan buat Pull Request di GitHub dari `ui/redesign` ke `main`.

---

## Tahap 9. Kalau ada masalah

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| Tampilan tidak berubah setelah OpenCode selesai | Cache browser atau cache view Laravel | `Ctrl + F5`; lalu `php artisan view:clear` |
| CSS tidak termuat (halaman polos) | Path file CSS salah atau file belum dibuat | Buka DevTools, tab Network, cari file berwarna merah (404), cek path-nya di `public\assets\...` |
| Halaman error 500 setelah ubah Blade | Salah sintaks Blade atau komponen yang tidak ditemukan | Baca pesan error di layar; atau `php artisan view:clear`, lalu `storage\logs\laravel.log` |
| OpenCode mengubah file di luar rencana | Prompt terlalu longgar | `git restore <file>`; tambahkan di prompt: "Hanya ubah file X, Y, Z" |
| OpenCode lupa aturan di tengah sesi | Sesi terlalu panjang | Keluar, buka sesi baru; `AGENTS.md` akan dibaca ulang |
| Peringatan `LF will be replaced by CRLF` | Pengaturan baris baru di Windows | Abaikan, tidak berbahaya |
