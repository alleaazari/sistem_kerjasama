# ATURAN PEMBARUAN — Sistem Kerjasama BBLSDM Komdigi Medan

Dokumen ini adalah **aturan wajib** untuk pembaruan sistem. Disusun berdasarkan `docs/AUDIT_FRONTEND.md` (hasil audit repo, 4 November 2026). Jika ragu, **berhenti dan tanya**, jangan menebak.

Dokumen pendamping (baca semuanya sebelum mulai):
- `docs/LANGKAH_PEMBARUAN.md` — spesifikasi dan fase pengerjaan.
- `docs/AUDIT_FRONTEND.md` — kondisi repo saat ini (rujukan fakta, lengkap dengan path:baris).
- `docs/PROGRESS.md` — catatan progres (dirawat OpenCode, lihat bagian 2).

---

## 0. Hubungan dengan `AGENTS.md` yang Sudah Ada

Repo ini **sudah memiliki `AGENTS.md`** (dirujuk oleh audit: ambang 90 hari "segera berakhir", satu sumber JS UI, larangan `style` inline, dll.). Aturan itu **tetap berlaku** dan **tidak boleh ditimpa atau dihapus**.

Urutan prioritas jika ada bentuk konflik:
1. **Aturan keamanan di dokumen ini (bagian 5)** — selalu menang.
2. `AGENTS.md` yang sudah ada — menang untuk konvensi UI, CSS, JS, dan penamaan.
3. Dokumen ini (selain bagian 5) dan `LANGKAH_PEMBARUAN.md`.

Jika dua aturan bertentangan dan Anda tidak bisa menentukan mana yang menang, **berhenti dan tanyakan**, jangan memilih sendiri.

---

## 1. Kondisi Nyata Repo (dari Audit)

Jangan berasumsi di luar fakta ini. Verifikasi ulang bila akan menyentuh bagiannya.

| Aspek | Kenyataan |
|---|---|
| Backend | PHP ^8.3, Laravel ^13 (terpasang 13.35), Fortify, Pest 5, Larastan level 7, Pint |
| **UI yang tayang** | **Blade + CSS statis di `public/assets/{shared,admin,user}/css/`.** Bukan React. |
| Stack React/Inertia | Terpasang tetapi **tidak aktif**: `routes/settings.php` dan `routes/web.bawaan.php` tidak dimuat, `HandleInertiaRequests` tidak terdaftar. Hanya `/forgot-password` dan `/reset-password/{token}` (Fortify) yang merender halaman Inertia. |
| Package manager | npm (`package-lock.json`), runner `vp` (vite-plus). Bukan pnpm/yarn. |
| Database | Hanya 3 migration infrastruktur (users, cache, jobs). **Tidak ada tabel bisnis.** Hanya model `User` dengan ID berurutan. |
| Penyimpanan data bisnis | `storage/app/storage_pengajuan.json`, session, dan array default di route. Upload gambar disimpan sebagai base64 di session. |
| Autentikasi | Dua sistem terpisah: (1) mitra: `POST /login` hanya redirect **tanpa verifikasi apa pun**; (2) admin: session kustom `admin_auth` dengan akun **plaintext** di `config/admin_demo.php`. Tidak ada role di DB, tidak ada Policy/Gate. |
| Rute | Hampir semua berupa closure di `routes/web.php` dan `routes/admin.php`. Panel admin berada di prefix rahasia `ADMIN_PATH` + middleware `HideAdmin` (IP allowlist) + `admin.only`. |
| Antrean | Tabel `jobs`/`failed_jobs` ada, tetapi belum dipakai. |

---

## 2. Aturan Kerja

1. **Baca dulu, tulis kemudian.** Ikuti pola yang sudah ada di repo.
2. **Satu fase per giliran.** Setelah fase selesai: jalankan pengecekan (bagian 7), laporkan, lalu **berhenti dan tunggu** perintah "Lanjut".
3. **Rencana sebelum eksekusi.** Di awal tiap fase, tulis daftar file yang akan dibuat/diubah dan alasannya. Tunggu persetujuan jika fase menyentuh database, autentikasi, upload file, atau data produksi.
4. **Laporkan hanya file yang berubah** (`DIBUAT`, `DIUBAH`, `DIHAPUS`, path lengkap, satu baris per file). Jangan menulis ulang seluruh proyek.
5. **Rawat `docs/PROGRESS.md`:** fase selesai, file yang berubah, keputusan, hal yang menunggu konfirmasi, masalah belum beres. Perbarui di akhir setiap fase.
6. **Perubahan minimal dan terarah.** Jangan refactor, rename, atau memformat ulang kode di luar cakupan tugas.
7. **Jangan menambah fitur di luar spesifikasi.** Saran tambahan ditulis di `docs/PROGRESS.md`.
8. **Kutip bukti.** Setiap klaim tentang kode harus menyertakan `path:baris`. Jangan mengarang isi file yang belum dibaca.

---

## 2A. Aturan Anti-Kerusakan

Tujuan: satu perubahan tidak boleh merusak bagian lain.

1. **Analisis dampak sebelum mengubah file bersama.** File berisiko (dari audit §10) hanya boleh diubah setelah mencari semua pemakainya dan menuliskan daftar terdampak di rencana fase:
   - `resources/views/layouts/{admin,user,user-public}.blade.php`
   - `resources/views/partials/{header,footer-komdigi}.blade.php`, `resources/views/components/asset.blade.php`
   - `public/assets/shared/css/{tokens,base,components}.css`, `public/assets/{admin,user}/css/style.css`, `public/assets/user/css/landing.css`
   - `routes/web.php`, `routes/admin.php`, `bootstrap/app.php`
   - `config/admin_demo.php`, `app/Models/User.php`
   - `app/Http/Middleware/{AdminOnly,HideAdmin}.php`
2. **Utamakan penambahan.** Buat controller, Form Request, view, dan partial baru. Jika harus mengubah yang lama, pertahankan nama route, nama view, dan variabel yang sudah dipakai view lain. Perubahan yang bersifat merusak harus disetujui lebih dulu.
3. **Migrasi database harus aditif**: tambah tabel/kolom baru (nullable atau punya default). Jangan mengubah atau menghapus kolom lama tanpa persetujuan. Setiap migration punya `down()` yang benar.
4. **Pertahankan halaman dan route lama** sampai penggantinya berfungsi. Jangan menghapus dan mengganti dalam fase yang sama.
5. **Satu tujuan per commit.**
6. **Build dan test tetap hijau** setelah tiap sub-langkah. Jika gagal, perbaiki atau kembalikan perubahan terakhir sebelum lanjut.
7. **Jangan memperbaiki hal di luar tugas.** Catat di `docs/PROGRESS.md`.
8. **Jangan mengubah file tooling** (`vite.config.ts`, `tsconfig.json`, `composer.json`, `package.json`, `phpstan.neon`, `pint.json`, `phpunit.xml`, `components.json`) tanpa alasan tertulis dan persetujuan.
9. **Uji regresi.** Jalankan seluruh test, bukan hanya test baru, dan uji manual alur lama yang tidak terkait.
10. **Satu sumber data.** Saat memindahkan data dari JSON/session ke database, jangan ada dua sumber kebenaran yang aktif bersamaan. Tulis rencana peralihannya lebih dulu dan minta persetujuan.

---

## 3. Batasan Keras (Dilarang Tanpa Persetujuan Eksplisit)

- Perintah destruktif: `migrate:fresh`, `migrate:refresh`, `db:wipe`, `rm -rf`, `git reset --hard`, `git push --force`, `git clean -fd`, drop tabel/kolom berisi data.
- Membaca, menampilkan, mengubah, atau meng-commit `.env`. Hanya `.env.example` yang boleh diubah (placeholder, tanpa nilai nyata).
- **Menulis nilai `ADMIN_PATH`, `ADMIN_ALLOWED_IPS`, password, API key, token, atau kredensial** ke kode, log, komentar, test, dokumentasi, atau laporan. Audit sudah sengaja menyembunyikan path admin; pertahankan itu.
- Menambah dependency Composer/npm tanpa menyebut nama paket, alasan, dan menunggu persetujuan.
- Meng-commit langsung ke `main`.
- Menonaktifkan CSRF, middleware `auth`, atau validasi.
- **Menghapus, menimpa, atau mengosongkan `storage/app/storage_pengajuan.json`** (mungkin data nyata). Perpindahan data hanya lewat perintah impor yang aman diulang (idempotent), dijalankan atas persetujuan user, dan file asli tetap disimpan sebagai cadangan.
- Mengaktifkan atau menghapus stack React/Inertia: **jangan** mendaftarkan `routes/settings.php`/`routes/web.bawaan.php`, jangan menghapus `resources/js/**`, `app/Http/Controllers/Settings/**`, atau mengubah `HandleInertiaRequests`. Nasibnya diputuskan user (lihat bagian 4).
- Mengakses jaringan selain registry paket dan endpoint yang disetujui user.
- Mengirim email, WhatsApp, atau memanggil API AI nyata saat pengembangan. Gunakan driver `log`/`array`/fake.
- Meng-commit `database/database.sqlite`, file upload, atau isi `storage/`.

**Peringatan awal:** tiga file sudah berstatus *modified* dari pekerjaan sebelumnya (`resources/views/admin/mitra_draf.blade.php`, `resources/views/layouts/user.blade.php`, `resources/views/user/draf.blade.php`). Jangan menimpanya. Minta user meng-commit atau menyimpannya sebelum fase pertama yang menyentuhnya.

---

## 4. Hal yang HARUS Dikonfirmasi ke User

Jika dibutuhkan fase yang sedang dikerjakan dan belum terjawab di `docs/PROGRESS.md`, **tanyakan dulu**:

1. **Nasib stack React/Inertia** (audit §12.1): dibiarkan beku (disarankan), atau akan dihapus nanti? Dan apakah halaman Inertia `forgot-password`/`reset-password` Fortify tetap dipakai?
2. **Pemetaan peran lama ke baru:** `admin` dan `developer` di `config/admin_demo.php` menjadi peran apa (`admin_kerjasama`/`superadmin`)? Apakah path rahasia admin dan IP allowlist dipertahankan?
3. **Sumber kebenaran data:** apakah `storage_pengajuan.json` berisi data nyata atau demo? Apakah perlu diimpor ke database?
4. **URL login mitra:** `/mitra/login` (disarankan) atau subdomain? Apakah `/login` lama dialihkan?
5. **Fortify reset password mandiri:** spesifikasi meminta permintaan lupa password **masuk ke Admin Kerjasama**, bukan reset mandiri. Apakah `Features::resetPasswords()` dinonaktifkan?
6. **Bidang kerjasama, jenis instansi** (daftar pilihan), dan durasi default masa aktif kerjasama.
7. **Penyedia WhatsApp** (gateway `WA_GATEWAY_URL`/`WA_GATEWAY_KEY` yang ada di kode apakah dipakai produksi?) dan **SMTP**.
8. **Penyedia AI** untuk generator MoU, dan apakah label "Draf Proposal AI" diganti menjadi "MoU".
9. **CAPTCHA:** Cloudflare Turnstile atau reCAPTCHA v3.
10. **Database produksi** (SQLite hanya untuk lokal?) dan tempat penyimpanan file (disk privat lokal atau object storage).
11. **Test yang pasti gagal** (`DashboardTest`, `ExampleTest` memakai route `dashboard`/`home` yang tidak ada): dihapus atau diperbaiki?
12. **Gambar eksternal** `images.unsplash.com` di landing: diganti aset lokal?
13. **Wajib ganti password** saat login pertama untuk akun mitra baru (sangat disarankan, lihat 5.8).
14. **Klaim statistik di landing** ("500+ Mitra", "1.2M+ Alumni", "99.8% Kepuasan"): data nyata atau dihapus?

---

## 5. Aturan Keamanan (Wajib, Tidak Bisa Ditawar)

Validasi di front-end hanya untuk kenyamanan; keamanan sebenarnya selalu di backend. Karena UI memakai **Blade (bukan React)**, perlindungan XSS bawaan React tidak berlaku, jadi aturan XSS di bawah lebih ketat.

### 5.1 Unggah File
- Form Request: batasi ekstensi dan MIME (`pdf`, `docx` untuk dokumen; `jpg`, `jpeg`, `png`, `webp` untuk gambar), ukuran maksimum eksplisit. Periksa MIME dari isi file, bukan dari klien.
- **Ganti nama file** menjadi nama acak (UUID/hash) + ekstensi tervalidasi. Nama asli hanya sebagai metadata di database.
- **Simpan di luar web root**: disk privat (`storage/app/private`). Jangan di `public/`. Jangan menjalankan `storage:link` untuk file sensitif.
- Unduhan/tampilan lewat controller dengan otorisasi (Policy), header `Content-Disposition` dan `X-Content-Type-Options: nosniff`. Untuk gambar publik homepage (galeri, foto petinggi, berita), sajikan lewat route media khusus yang membaca dari disk privat dan hanya melayani file bertipe gambar.
- Hentikan pola **base64 di session** untuk file. Simpan sebagai file + catat path di database.
- Pastikan route framework `/storage/{path}` (disk `local`, `serve: true`) tidak membuka file privat tanpa otorisasi yang sah. Verifikasi dan laporkan.
- Front-end: `accept=".pdf,.docx"` atau tipe gambar, untuk UX saja. Hapus `enctype="multipart/form-data"` dari form yang tidak punya input file (`pengajuan.blade.php:50`).

### 5.2 SQL Injection
- Eloquent/Query Builder dengan binding. Dilarang menyambung string SQL. `DB::raw`/`whereRaw`/`orderByRaw` hanya dengan binding; kolom `orderBy` dari whitelist. Saat ini tidak ada query raw (audit §8.2); pertahankan.
- Semua input lewat Form Request (tipe, panjang, format). Front-end: `type="email"`, `type="tel"`, `required`, `maxlength` sebagai filter dasar.

### 5.3 XSS (Blade dan JavaScript)
- Di Blade gunakan `{{ }}` (ter-escape). **Dilarang `{!! !!}`** untuk data pengguna. Jika terpaksa, konten harus disanitasi di server dan alasannya dicatat.
- **Dilarang** menyisipkan data pengguna/API lewat `innerHTML`, `outerHTML`, `insertAdjacentHTML`, `document.write`, atau `eval`. Gunakan `textContent`, `createElement`, dan atribut `data-*`.
- **Dilarang** membangun handler `onclick="...('{{ data }}')"` atau `onclick="fn({{ json_encode($row) }})"` dengan data pengguna. Gunakan `data-*` + `addEventListener`. Jika data harus dikirim ke JS, pakai `@json(...)` di elemen `<script type="application/json">` atau atribut `data-*`, dan baca dengan `JSON.parse`.
- Jangan menyisipkan nilai cookie/input ke dalam blok `<script>` (lihat `resources/views/app.blade.php:10`); gunakan atribut `data-*` atau nilai yang di-whitelist.
- Cookie sesi: `http_only=true`, `same_site=lax`, `secure=true` di produksi (tambahkan `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `SESSION_SAME_SITE` ke `.env.example`), dan pertimbangkan `SESSION_ENCRYPT=true`.
- Security headers lewat middleware global: `X-Content-Type-Options: nosniff`, `X-Frame-Options`/`frame-ancestors`, `Referrer-Policy`, HSTS (produksi, atas konfirmasi), dan `Content-Security-Policy`. Karena banyak `<script>` dan `style` inline, **mulai dengan `Content-Security-Policy-Report-Only`**, lalu jadikan enforce di fase terakhir setelah JS/CSS inline dipindahkan ke `public/assets/**` atau diberi nonce.

### 5.4 BOLA / IDOR
- Semua model yang ID-nya muncul di URL (pengajuan, mitra, artikel, galeri, petinggi, MoU, dokumen, notifikasi) memakai **UUID** sebagai route key, bukan ID berurutan (saat ini `/inbox/{id}`, `/artikel/{id}`, `/galeri/{id}` memakai ID berurutan).
- Setiap aksi controller memanggil `authorize()`/Policy. Tidak ada aksi yang hanya mengandalkan "route-nya tersembunyi".
- Data mitra **selalu di-scope** ke mitra yang login. Jangan menerima `user_id`/`mitra_id` dari input klien. Mitra A tidak boleh membaca, mengubah, atau mengunduh data mitra B; wajib ada test (403/404).
- ID yang tidak ditemukan harus menghasilkan 404, bukan "sukses" (saat ini reject memberi status `ok` untuk ID tak dikenal).
- Pemisahan peran ditegakkan di middleware dan Policy (`superadmin`, `admin_kerjasama`, `mitra`), bukan hanya dengan menyembunyikan menu.
- `HideAdmin` (path rahasia + IP allowlist) boleh dipertahankan sebagai lapisan tambahan, **bukan pengganti** otorisasi. Pastikan konfigurasi *trusted proxy* benar bila aplikasi berada di balik proxy.

### 5.5 Spam, DoS, dan Rate Limiting
- `throttle` bernama (via `RateLimiter::for`) pada: `POST /pengajuan`, login mitra, login admin (sudah `throttle:5,1`), permintaan lupa password, permintaan perpanjangan, generate MoU AI, `GET /api/pddikti`, dan `POST /mitra/profile`.
- CAPTCHA (Turnstile/reCAPTCHA v3) pada form pengajuan. **Token wajib diverifikasi di server**; secret hanya di `.env`.
- Batasi ukuran request, panjang field, dan jumlah file.

### 5.6 Autentikasi dan Sesi
- Satu mekanisme autentikasi berbasis Laravel (`Auth`) dengan kolom `role`, menggantikan akun plaintext di `config/admin_demo.php` dan login mitra tanpa verifikasi. Password selalu di-hash.
- `POST /login` mitra wajib benar-benar mengautentikasi, meregenerasi session, dan **logout menginvalidasi session** (saat ini hanya `forget('mitra_data')`).
- Pesan error login/lupa password tidak membocorkan keberadaan akun.
- Hapus kredensial demo yang tampil di UI (`resources/views/login.blade.php:10-14,20,24`, `admin/login.blade.php:34-44`) dan password default pada atribut `value`.
- Lupa password mitra = **permintaan ke notifikasi Admin Kerjasama**, bukan reset mandiri (setelah konfirmasi, bagian 4 butir 5).
- Route `GET /test-email` dihapus atau dinonaktifkan di produksi (bisa mengirim email ke alamat sembarang).

### 5.7 Mass Assignment, Validasi, Logging
- Model memakai daftar `fillable` eksplisit (di repo ini memakai atribut `#[Fillable([...])]`; ikuti pola yang sama untuk model baru).
- Validasi baru lewat **Form Request**, bukan inline di closure. Saat menyentuh closure lama, pindahkan ke controller + Form Request.
- Field yang dikirim tetapi belum divalidasi (`jabatan_pic`, `bidang[]`, `pddikti_id`, `logo`) wajib divalidasi atau dihapus.
- Log tidak boleh berisi password, token, atau data pribadi berlebihan. Hentikan `file_put_contents(..., @)` ke `storage/logs/*.log` yang membungkam error; gunakan `Log::` bawaan Laravel.
- `.env.example`: `APP_DEBUG=false`, dan tambahkan variabel yang dipakai kode tetapi belum terdaftar (`ADMIN_PATH`, `ADMIN_ALLOWED_IPS`, `WA_GATEWAY_URL`, `WA_GATEWAY_KEY`) **tanpa nilai nyata**.

### 5.8 Akun Otomatis untuk Mitra
- Password acak yang kuat (minimal 14 karakter, generator aman kriptografi). Simpan hanya hash. Plaintext hanya ada di memori saat membuat email.
- `must_change_password = true` agar mitra wajib mengganti password saat login pertama (setelah konfirmasi).
- Jangan menulis password ke log. Kirim lewat queue; saat pengembangan gunakan mailer `log`/`array` dengan password disamarkan.
- Pembuatan akun dan perubahan status pengajuan dalam satu transaksi database; persetujuan ganda harus ditolak.

### 5.9 Integrasi Eksternal
- Proxy PDDIKTI (`routes/web.php:161-199`): **aktifkan kembali verifikasi TLS** (hapus `withoutVerifying()`), jangan memalsukan User-Agent, tambahkan timeout, cache, throttle, batas hasil, dan fallback input manual.
- Gateway WhatsApp: ganti `file_get_contents`/`stream_context_create` dengan HTTP client Laravel, TLS terverifikasi, timeout, error tidak dibungkam, lewat queue.
- **Generator MoU AI**: dipanggil dari server, API key di `.env`. Data isian mitra diperlakukan sebagai **data, bukan instruksi** (cegah prompt injection), dibatasi panjangnya, dibatasi laju, dan hasilnya ditampilkan sebagai teks biasa yang dapat diedit. Template dari Superadmin adalah sumber struktur.
- Jangan mengandalkan `http://127.0.0.1:8000` hardcoded (`layouts/admin.blade.php:114`, `routes/admin.php:106`); gunakan `config('app.url')`/`route()`.

---

## 6. Standar Kode dan UI

Ikuti `AGENTS.md` yang sudah ada. Ringkasan dari audit:
- Teks UI **Bahasa Indonesia baku**. Hilangkan sisa istilah Inggris pada UI baru ("Review", "Approve", "Step", dll.).
- Penamaan: view `snake_case` (`user.status`), route `dotted.lowercase` (`mitra.profile.update`), controller `PascalCase`+`Controller`, field form `snake_case`, kelas CSS `kebab-case`.
- **Tidak ada `style` inline** pada kode baru; CSS di `public/assets/**` dengan urutan muat tokens → base → area → components. Gunakan komponen `x-asset` yang sudah ada untuk cache-busting.
- JS UI baru mengikuti aturan "satu sumber JS" di `AGENTS.md` (`public/assets/shared/js/ui.js` belum ada; buat jika aturan itu mengharuskan, dan catat di `PROGRESS.md`).
- Struktur Blade: `@extends` layout + `@section('title')` + `@section('content')`. Jangan menggandakan layout baru; pertimbangkan ekstraksi bersama hanya atas persetujuan (audit §12.14).
- Logika bisnis di controller/service/action, bukan di closure route. Route baru memakai controller.
- Format tanggal dan status seragam (satu helper/enum); jangan menambah format keempat. Status pengajuan memakai satu enum (`menunggu`, `disetujui`, `ditolak`).
- Aksesibel dan responsif: label di setiap field, pesan error dekat field, nyaman di layar mobile.

---

## 7. Pengecekan Wajib di Akhir Setiap Fase

Baca dulu definisi `composer ci:check` di `composer.json`, lalu jalankan dan laporkan apa adanya (termasuk yang gagal):

```
composer lint:check
composer types:check
composer test
npm run types:check
npm run check
```

- `npm run build` hanya dijalankan jika aset yang dibangun Vite berubah (UI tayang memakai CSS statis, jadi biasanya tidak perlu).
- Jangan mengklaim "selesai" jika ada pengecekan yang gagal. Bedakan kegagalan **lama** (mis. `DashboardTest`, `ExampleTest`) dari kegagalan **baru** akibat perubahan Anda.
- Fitur berisiko wajib punya test otomatis (Pest): akses lintas mitra ditolak, peran salah ditolak, upload berbahaya ditolak, rate limit aktif, payload XSS tampil ter-escape, persetujuan ganda ditolak.
- Jika sebuah perintah tidak ada atau gagal dijalankan, katakan, jangan mengarang hasilnya.

---

## 8. Git

- Branch: `feat/pembaruan-sistem-kerjasama` (atau satu branch per fase: `feat/fase-1-...`).
- Sebelum mulai, `git status` harus bersih atau perubahan lama sudah di-commit/disimpan oleh user.
- Commit kecil, format `tipe: deskripsi` (mis. `feat: tabel pengajuan dan mitra`, `fix: verifikasi TLS proxy pddikti`).
- Jangan commit: `.env`, `storage/`, `vendor/`, `node_modules/`, `database/database.sqlite`, file upload.
- Jangan push. User yang push dan membuat pull request (repo memiliki 1 PR terbuka; jangan mengubahnya).

---

## 9. Format Laporan Akhir Fase

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

### Temuan audit yang ditangani
- audit §x.y — status (selesai / sebagian / ditunda) + bukti

### Keamanan
- [x]/[ ] item bagian 5 yang relevan, dengan bukti (nama test / path:baris)

### Pengecekan
- lint / types / test / types-js / check: hasil (bedakan gagal lama vs baru)

### Menunggu konfirmasi user
- ...

### Langkah berikutnya
- Fase N+1 (menunggu perintah "Lanjut")
```
