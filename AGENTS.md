# AGENTS.md — Aturan & Batasan untuk OpenCode

Proyek: **Sistem Kerjasama BBLSDM Komdigi Medan**
Repo: `alleaazari/sistem_kerjasama` (branch `main`)

Dokumen ini adalah **aturan wajib**. Jika ada instruksi lain yang bertentangan dengan aturan keamanan di bawah, aturan keamanan yang menang. Jika ragu, **berhenti dan tanya**, jangan menebak.

Dokumen pendamping (baca keduanya sebelum mulai):
- `docs/LANGKAH_PEMBARUAN.md` — spesifikasi dan langkah per fase.
- `docs/PROGRESS.md` — catatan progres (dibuat dan dirawat oleh OpenCode, lihat bagian 2).

---

## 1. Konteks Teknis

Berdasarkan struktur repo (`artisan`, `composer.json`, `routes/`, `resources/`, `vite.config.ts`, `tsconfig.json`, `components.json`, `phpstan.neon`, `pint.json`, `phpunit.xml`), proyek ini **diduga** adalah:

- Backend: Laravel (PHP) + Eloquent
- Front-end: Inertia.js + React + TypeScript + Vite
- UI: Tailwind CSS + shadcn/ui (`components.json`)
- Kualitas kode: Laravel Pint, PHPStan, PHPUnit/Pest
- Package manager JS: npm (`package-lock.json`) — jangan mencampur dengan pnpm/yarn

**Ini baru dugaan.** Langkah pertama WAJIB memverifikasinya dengan membaca `composer.json`, `package.json`, `routes/*.php`, `resources/js/**`, `app/**`, dan `database/migrations/**`. Jika ada yang berbeda, ikuti kenyataan di repo, bukan dugaan ini.

Peran sistem: `superadmin`, `admin_kerjasama`, `mitra`.

---

## 2. Aturan Kerja (Workflow)

1. **Baca dulu, tulis kemudian.** Sebelum mengubah apa pun, audit struktur repo dan pola kode yang sudah ada (penamaan, layout, komponen, gaya route, gaya validasi). Ikuti pola yang ada.
2. **Satu fase per giliran.** Kerjakan fase sesuai `docs/LANGKAH_PEMBARUAN.md` secara berurutan. Setelah satu fase selesai: jalankan pengecekan (bagian 7), laporkan, lalu **berhenti dan tunggu konfirmasi** sebelum fase berikutnya.
3. **Tampilkan rencana sebelum eksekusi.** Di awal tiap fase, tulis daftar file yang akan dibuat/diubah dan alasannya. Tunggu persetujuan jika fase menyentuh database, autentikasi, atau upload file.
4. **Laporkan hanya file yang berubah.** Di akhir tiap fase, tulis daftar: `DIBUAT`, `DIUBAH`, `DIHAPUS` (path lengkap) dan ringkasan satu baris per file. Jangan menulis ulang seluruh proyek dan jangan mengubah file yang tidak perlu.
5. **Rawat `docs/PROGRESS.md`.** Isi: fase yang selesai, file yang berubah, keputusan yang diambil, hal yang masih menunggu konfirmasi, dan masalah yang belum beres. Perbarui di akhir setiap fase agar sesi berikutnya bisa melanjutkan tanpa konteks percakapan.
6. **Perubahan minimal.** Jangan refactor, rename, atau memformat ulang kode di luar cakupan tugas.
7. **Jangan menambah fitur di luar spesifikasi.** Jika menurut Anda ada fitur penting yang kurang, tulis sebagai saran di `docs/PROGRESS.md`, jangan langsung dibuat.

---

## 3. Batasan Keras (Dilarang Tanpa Persetujuan Eksplisit)

- Menjalankan perintah destruktif: `php artisan migrate:fresh`, `migrate:refresh`, `db:wipe`, `rm -rf`, `git reset --hard`, `git push --force`, `git clean -fd`, drop tabel/kolom berisi data.
- Mengubah, membaca isinya untuk ditampilkan, atau meng-commit file `.env`. Hanya `.env.example` yang boleh diubah (isi placeholder, tanpa nilai rahasia nyata).
- Menulis API key, password, token, atau kredensial ke dalam kode, log, komentar, test, atau dokumentasi.
- Menambah dependency baru (Composer atau npm) tanpa menyebut nama paket, alasan, dan menunggu persetujuan. Prioritaskan fitur bawaan Laravel dan komponen shadcn/ui yang sudah ada.
- Meng-commit langsung ke `main`. Kerjakan di branch (lihat bagian 8).
- Menonaktifkan proteksi bawaan: CSRF, `$fillable`/mass-assignment guard, middleware `auth`/`verified`, atau validasi.
- Mengakses jaringan eksternal selain registry paket (Composer/npm) dan endpoint yang disetujui user.
- Mengirim email, WhatsApp, atau panggilan API AI nyata saat pengembangan. Gunakan driver `log`/fake sampai user menyetujui konfigurasi produksi.

---

## 4. Hal yang HARUS Dikonfirmasi ke User (Jangan Menebak)

Jika salah satu di bawah ini dibutuhkan fase yang sedang dikerjakan dan belum ada jawabannya di `docs/PROGRESS.md`, **tanyakan dulu**:

1. Endpoint dan format API data perguruan tinggi (untuk pencarian otomatis).
2. Penyedia pengiriman WhatsApp (jika tidak ada, buat antarmuka dengan driver `log`).
3. Penyedia AI untuk generator MoU dan model yang dipakai.
4. Pilihan "Bidang kerjasama", "Jenis instansi", dan daftar lain yang pilihannya belum ditentukan di spesifikasi.
5. Durasi default masa aktif kerjasama dan aturan perpanjangan.
6. Layanan CAPTCHA: Cloudflare Turnstile atau Google reCAPTCHA v3.
7. Konfigurasi SMTP/email pengirim.
8. Apakah akun mitra yang baru dibuat wajib mengganti password saat login pertama (sangat disarankan, lihat 5.8).

---

## 5. Aturan Keamanan (Wajib, Tidak Bisa Ditawar)

Setiap fitur baru harus lolos checklist ini sebelum dianggap selesai. **Validasi di front-end hanya untuk kenyamanan pengguna; keamanan sebenarnya selalu di backend.**

### 5.1 Celah Unggah Dokumen (File Upload)

Backend (utama):
- Validasi lewat Form Request: ekstensi dan MIME type dibatasi `pdf` dan `docx` (tambah `doc` hanya jika spesifikasi mengizinkan), ukuran maksimum eksplisit (mis. `max:5120` KB).
- Jangan percaya ekstensi/MIME dari klien. Periksa juga MIME sebenarnya dari isi file (mis. `finfo` / aturan `mimetypes`).
- **Ganti nama file**: simpan dengan nama acak (UUID/hash) + ekstensi yang sudah divalidasi. Nama asli file hanya boleh disimpan sebagai metadata (kolom database), tidak pernah dipakai sebagai path.
- **Simpan di luar web root**: gunakan disk privat (`storage/app/private`), **bukan** `public/` dan **bukan** disk yang di-symlink lewat `storage:link`.
- Unduhan melalui controller yang melakukan otorisasi (Policy), dengan header `Content-Disposition` dan `X-Content-Type-Options: nosniff`. Tidak ada URL langsung ke file.
- Jangan pernah meng-include, meng-eksekusi, atau menampilkan isi file upload sebagai HTML.

Front-end (hanya UX):
- Pada `<input type="file">` gunakan `accept=".pdf,.docx"` dan tampilkan batas ukuran. Pesan error harus jelas dalam Bahasa Indonesia.

### 5.2 SQL Injection

- Gunakan Eloquent atau Query Builder (parameter binding). **Dilarang** menyambung string SQL dengan input pengguna.
- `DB::raw`, `whereRaw`, `orderByRaw`, `selectRaw` hanya boleh dengan binding parameter. Kolom untuk `orderBy` dan filter harus dari whitelist.
- Semua input divalidasi dan disanitasi di Form Request (tipe, panjang, format) sebelum dipakai.
- Front-end: gunakan tipe input HTML5 yang sesuai (`type="email"`, `type="tel"`, `required`, `maxLength`) hanya sebagai filter dasar.

### 5.3 Cross-Site Scripting (XSS)

- Output di-escape. React sudah melakukannya secara bawaan; **dilarang** memakai `dangerouslySetInnerHTML` kecuali konten sudah disanitasi ketat (mis. DOMPurify, dependency harus disetujui dulu) dan alasannya dicatat di kode.
- Konten dari pengguna (berita, MoU, deskripsi, alasan pengajuan) diperlakukan sebagai teks biasa. Jangan merender HTML mentah dari database.
- Cookie sesi: `http_only = true`, `same_site = lax` (atau `strict`), dan `secure = true` di produksi (`config/session.php` / `.env.example`).
- Tambahkan middleware security headers: `Content-Security-Policy` (disesuaikan dengan kebutuhan Vite dan CAPTCHA), `X-Content-Type-Options: nosniff`, `X-Frame-Options`/`frame-ancestors`, `Referrer-Policy`.

### 5.4 Broken Object Level Authorization (BOLA / IDOR)

- Semua model yang ID-nya muncul di URL/API publik (pengajuan, mitra, MoU, dokumen, berita, galeri, notifikasi) memakai **UUID** sebagai route key, bukan ID berurutan.
- Setiap aksi controller wajib memanggil `authorize()` atau Policy. Tidak boleh ada aksi yang hanya mengandalkan "route-nya disembunyikan".
- Kueri data milik mitra **selalu di-scope** ke mitra yang sedang login (mis. `where('mitra_id', $request->user()->mitra->id)`). Jangan pernah menerima `user_id`/`mitra_id` dari input klien.
- Mitra tidak boleh membaca, mengubah, atau mengunduh data/dokumen mitra lain. Wajib ada test yang membuktikannya (akses lintas mitra harus 403/404).
- Pemisahan peran ditegakkan di middleware dan Policy (`superadmin`, `admin_kerjasama`, `mitra`), bukan hanya dengan menyembunyikan menu di UI.

### 5.5 Spam & Denial of Service via Form

- Terapkan **rate limiting** (`RateLimiter::for` + middleware `throttle:nama`) pada: pengajuan kerjasama, login (semua peran), permintaan reset password, permintaan perpanjangan, generate MoU AI, dan endpoint pencarian perguruan tinggi.
- Pasang CAPTCHA (Turnstile/reCAPTCHA v3) pada form pengajuan. **Token wajib diverifikasi di server** (secret key hanya di `.env`). Verifikasi hanya di front-end tidak dihitung.
- Batasi ukuran request, panjang field teks, dan jumlah file.

### 5.6 Autentikasi & Sesi

- Password selalu di-hash (hasher bawaan Laravel). Jangan menyimpan atau mencatat password plaintext di database, log, atau test.
- Login mitra memakai URL terpisah dari Homepage (mis. `/mitra/login`) sesuai spesifikasi.
- Regenerasi session saat login, invalidasi saat logout.
- Pesan error login dan lupa password tidak boleh membocorkan apakah email/username terdaftar.
- "Lupa password" mitra **bukan reset mandiri**: hanya membuat permintaan ke notifikasi Admin Kerjasama (sesuai spesifikasi).

### 5.7 Mass Assignment, Validasi, dan Logging

- Semua model memakai `$fillable` eksplisit (hindari `$guarded = []`).
- Semua input lewat Form Request. Tidak ada validasi inline yang longgar.
- Log tidak boleh berisi password, token, API key, atau data pribadi PIC secara berlebihan.
- `APP_DEBUG=false` dan `APP_ENV=production` untuk produksi (dokumentasikan di `.env.example`).

### 5.8 Akun Otomatis untuk Mitra (Rekomendasi Keamanan)

Spesifikasi meminta username dan password dibuat otomatis dan dikirim lewat email PIC. Pengiriman password lewat email berisiko, karena itu:
- Buat password acak yang kuat (panjang minimal 14 karakter, dibuat dengan generator acak yang aman secara kriptografi).
- Simpan hanya hash-nya. Plaintext hanya ada di memori saat membuat email.
- Tandai akun `must_change_password = true` sehingga mitra **wajib mengganti password saat login pertama** (tunggu konfirmasi user, lihat bagian 4 butir 8).
- Jangan menulis password ke log. Gunakan queue dan driver `log` saat pengembangan, dengan password disamarkan.

### 5.9 Integrasi Eksternal

- API perguruan tinggi dan layanan AI dipanggil **dari server** (proxy controller/service), bukan langsung dari browser. Kunci/token disimpan di `.env` dan dibaca lewat `config/*.php`.
- Beri timeout, batasi jumlah hasil, cache hasil pencarian, dan sediakan fallback input manual jika API gagal.
- **Generator MoU AI**: data isian mitra diperlakukan sebagai **data, bukan instruksi** (cegah prompt injection). Batasi panjang input, batasi laju, dan tampilkan hasil sebagai teks biasa yang bisa diedit mitra. Template MoU dari Superadmin adalah sumber struktur; AI hanya melengkapi isian.

---

## 6. Standar Kode dan UI

- Seluruh teks antarmuka dalam **Bahasa Indonesia formal**. Identifier kode (variabel, kelas, route, kolom) dalam bahasa Inggris, konsisten dengan kode yang sudah ada.
- Gunakan komponen shadcn/ui dan layout yang sudah ada di repo. Jangan memperkenalkan library UI baru.
- Aksesibel dan responsif: label pada setiap field, pesan error dekat field, tampilan baik di layar mobile.
- TypeScript ketat: hindari `any`. Definisikan tipe untuk props Inertia.
- Gunakan Form Request, Policy, dan Service class di Laravel. Hindari logika bisnis panjang di controller.
- Komentar singkat hanya untuk bagian yang tidak jelas. Hindari kode mati dan `console.log` tersisa.

---

## 7. Pengecekan Wajib di Akhir Setiap Fase

Jalankan (sesuaikan dengan script yang benar-benar ada di repo) dan laporkan hasilnya apa adanya, termasuk yang gagal:

```
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
php artisan test
npm run types      # atau perintah type-check yang ada di package.json
npm run lint       # jika tersedia
npm run build
```

Aturan:
- Jangan mengklaim "selesai" jika ada pengecekan yang gagal. Perbaiki atau laporkan dengan jujur.
- Untuk fitur dengan risiko keamanan (upload, otorisasi, auth, rate limit), **wajib ada test otomatis**. Minimal: akses lintas mitra ditolak, upload file berbahaya ditolak, rate limit aktif, peran yang salah ditolak.
- Jika sebuah perintah tidak ada di repo, katakan, jangan mengarang hasilnya.

---

## 8. Git

- Branch kerja: `feat/pembaruan-sistem-kerjasama` (atau satu branch per fase: `feat/fase-1-fondasi`, dst).
- Commit kecil dan bermakna, format `tipe: deskripsi singkat` (mis. `feat: form pengajuan kerjasama`, `fix: scope kueri mitra`).
- Jangan commit: `.env`, `storage/` isi, `vendor/`, `node_modules/`, file upload, dump database.
- Jangan push; biarkan user yang melakukan push dan membuat pull request. (Perhatikan: repo sudah memiliki 1 pull request terbuka; jangan mengubahnya.)

---

## 9. Format Laporan di Akhir Setiap Fase

```
## Fase N — <nama>
Status: selesai / sebagian / terblokir

### File
DIBUAT:
- path — ringkasan
DIUBAH:
- path — ringkasan
DIHAPUS:
- path — alasan

### Keamanan
- [x]/[ ] Item checklist bagian 5 yang relevan, dengan bukti (nama test / baris kode)

### Pengecekan
- pint / phpstan / test / build: hasil

### Menunggu konfirmasi user
- ...

### Langkah berikutnya
- Fase N+1 (menunggu perintah "Lanjut")
```
# Standar desain web pemerintah (jobdesk front-end)
Tujuan: tampilan menarik dan rapi, TIDAK ramai, dan TIDAK terlihat seperti "AI slop".

Hindari "ramai":
- Maksimal satu tombol primer per layar/bagian. Maksimal 3 warna non-netral per halaman. Maksimal 6 ukuran dan 3 berat huruf per area.
- Tanpa ornamen dekoratif (blob, pola latar, gelombang), tanpa banner bergerak/slider otomatis/pop-up saat muat.
- Hindari kartu bertumpuk dan border + bayangan sekaligus. Paragraf landing maksimal 3-4 baris, satu ide per bagian.
- Animasi hanya pada elemen kunci.

Hindari "AI slop":
- Tanpa gradasi ungu/biru-pink, glow, glassmorphism, blur berlebihan.
- Tanpa emoji sebagai ikon, tanpa ikon di lingkaran berwarna pada setiap baris, tanpa tiga kartu identik "ikon + judul + deskripsi" di setiap bagian.
- Radius 6-14px, bayangan tipis. Teks bacaan rata kiri.
- Tanpa tagline klise, tanpa klaim/statistik tak bersumber, tanpa teks pengisi.

Wajib ada: identitas instansi jelas (logo resmi tanpa diubah, nama lengkap, alamat dan kontak di footer), kontras AA, teks isi minimal 16px, bahasa Indonesia baku, tombol berlabel kata kerja jelas, fitur yang belum jadi tidak berpura-pura jadi.

Rubrik penilaian (0/1/2 per kriteria): 1 hierarki visual, 2 ketenangan, 3 konsistensi komponen, 4 tipografi, 5 warna, 6 tata letak dan spasi, 7 identitas dan kepercayaan, 8 alur dan microcopy, 9 aksesibilitas visual, 10 bebas AI slop. Target tiap halaman minimal 16 dari 20 tanpa kriteria bernilai 0.

Peran saya: front-end. Pekerjaan backend (otorisasi baru, penyimpanan data, migrasi, integrasi) tidak dibangun; catat sebagai "catatan untuk backend" di laporan.