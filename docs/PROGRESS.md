# PROGRESS — Sistem Kerjasama BBLSDM Komdigi Medan

Dokumen ini dirawat OpenCode. Tujuan: sesi berikutnya dapat melanjutkan tanpa konteks percakapan.

Dokumen rujukan:
- `AGENTS.md` (root) — aturan lama, tetap berlaku, TIDAK diubah.
- `docs/ATURAN_PEMBARUAN.md` — aturan mengikat; bagian 5 (keamanan) selalu menang.
- `docs/LANGKAH_PEMBARUAN (1).md` — spesifikasi dan fase (nama file asli menyertakan `(1)`).
- `docs/AUDIT_FRONTEND.md` — fakta kondisi repo (4 November 2026).
- `docs/PROMPT_OPENCODE (1).md` — prompt kerja per fase.

> Catatan nama file: prompt merujuk `docs/LANGKAH_PEMBARUAN.md`, tetapi di repo file itu bernama
> `docs/LANGKAH_PEMBARUAN (1).md` (belum di-rename). `docs/PROGRESS.md` ini yang baru dibuat.

---

## 1. Ringkasan Audit (`docs/AUDIT_FRONTEND.md`)

- **UI yang tayang: Blade + CSS statis** (`public/assets/{shared,admin,user}/css/`), bukan React.
  Stack React/Inertia terpasang tetapi **tidak aktif**: `routes/settings.php` dan `routes/web.bawaan.php`
  tidak dimuat; `HandleInertiaRequests`/`HandleAppearance` tidak terdaftar (audit §3, §3.4).
  Hanya `/forgot-password` dan `/reset-password/{token}` (Fortify) yang merender halaman Inertia.
- **Tidak ada tabel bisnis.** Hanya 3 migration infrastruktur (`users`, `cache`, `jobs`);
  data bisnis hidup di `storage/app/storage_pengajuan.json`, session, dan array default di route (audit §6).
- **Login mitra tidak mengautentikasi:** `POST /login` hanya `redirect()->route('mitra.status')` (audit §7.1).
- **Akun admin plaintext** di `config/admin_demo.php` (audit §7.1, §8.9) — password nyata tidak ditulis di laporan ini.
- **Belum ada role/Policy/UUID:** `users` tanpa kolom role, tidak ada `app/Policies`, ID berurutan di URL
  (`/inbox/{id}`, `/artikel/{id}`, `/galeri/{id}`) (audit §6, §7.2, §8.4).
- **Upload:** logo mitra tanpa validasi; gambar disimpan base64 di session, bukan file (audit §8.1).
- **XSS Blade/JS:** `innerHTML` dari respons API & input, `onclick="fn({{ json_encode($row) }})"`,
  `addslashes(...)` di atribut, nilai cookie di blok `<script>` (audit §8.3).
- **Tidak ada throttle** pada `POST /pengajuan`, `POST /login` mitra, `GET /api/pddikti`, `POST /mitra/profile`;
  **tidak ada CAPTCHA**; **tidak ada security headers/CSP** (audit §8.5–8.7).
- **Proxy PDDIKTI** mematikan verifikasi TLS + memalsukan User-Agent (audit §8.2);
  gateway WA pakai `file_get_contents` dengan error dibungkam (audit §8.2).
- **Test lama gagal:** `tests/Feature/DashboardTest.php:6,14` (`route('dashboard')`) dan
  `tests/Feature/ExampleTest.php:4` (`route('home')`) merujuk nama route yang tidak terdaftar (audit §12.2).

---

## 2. Daftar Fase (`docs/LANGKAH_PEMBARUAN (1).md` bagian C)

| Fase | Nama | Status |
|---|---|---|
| 0B | Persiapan (tanpa mengubah kode aplikasi) | **selesai** |
| 1 | Baseline Keamanan dan Kebersihan | **selesai** |
| 2 | Fondasi Data dan Peran | belum |
| 3 | Homepage dan CMS Superadmin | belum |
| 4 | Form Pengajuan Kerjasama | belum |
| 5 | Inbox Pengajuan dan Persetujuan | belum |
| 6 | Portal Mitra | belum |
| 7 | MoU (Template, Generator AI, Penyuntingan) | belum |
| 8 | Halaman Admin Kerjasama dan Notifikasi | belum |
| 9 | Hardening dan Penutupan | belum |

---

## 3. Temuan Audit → Fase Penanganan (`docs/LANGKAH_PEMBARUAN (1).md` bagian D)

| Temuan (audit) | Fase |
|---|---|
| Login mitra tanpa autentikasi (§7.1) | 6 |
| Akun admin plaintext di config (§7.1, §8.9) | 2 |
| Tidak ada role/Policy/UUID (§6, §7.2, §8.4) | 2 |
| ID berurutan di route admin (§8.4) | 2, 3, 5 |
| Upload base64 di session, logo tanpa validasi (§8.1) | 3, 6 |
| `innerHTML`/`onclick` rawan XSS (§8.3) | 3, 4, 5, 7 |
| TLS dimatikan di proxy PDDIKTI (§8.2) | 1 |
| Tidak ada throttle di pengajuan/login mitra/pddikti/profil (§8.5) | 1 |
| Tidak ada CAPTCHA (§8.6) | 4 |
| Tidak ada security headers/CSP (§8.7) | 1, 9 |
| Field tidak tervalidasi: `jabatan_pic`, `bidang`, `pddikti_id`, `logo` (§8.8) | 4, 6 |
| `APP_DEBUG=true`, env tidak lengkap, cookie `secure` (§8.9) | 1 |
| Kredensial demo di UI (§8.9) | 1 |
| `test-email` bisa kirim ke alamat bebas (§8.4) | 1 |
| Race condition JSON tanpa locking (§8.2) | 2 |
| URL `127.0.0.1:8000` hardcoded (§8.9) | 1 |
| Logout mitra tak menginvalidasi session (§8.9) | 6 |
| Test lama gagal (§3.4, §12.2) | 1 |

---

## 4. Status Git Awal (Fase 0B)

Branch saat ini: `main`.

Berubah (modified, JANGAN disentuh — pekerjaan lama user):
- `AGENTS.md`
- `docs/ui-redesign/AUDIT_DESAIN_BASELINE.md`
- `docs/ui-redesign/LANGKAH_EKSEKUSI_DETAIL.md`
- `docs/ui-redesign/RENCANA_LANJUTAN_PROMPT_REVISI.md`
- `resources/views/admin/mitra_draf.blade.php`
- `resources/views/layouts/user.blade.php`
- `resources/views/user/draf.blade.php`

Untracked:
- `docs/ATURAN_PEMBARUAN.md`
- `docs/AUDIT_FRONTEND.md`
- `docs/LANGKAH_PEMBARUAN (1).md`
- `docs/PROMPT_OPENCODE (1).md`

Catatan: `ATURAN_PEMBARUAN.md` §8 mensyaratkan `git status` bersih atau perubahan lama sudah
di-commit/disimpan user **sebelum mulai fase yang menyentuh file-file itu**. `AGENTS.md` juga sudah
modified, padahal `ATURAN_PEMBARUAN.md` §0/§3 melarang menimpa `AGENTS.md`. Perlu keputusan user
(commit atau simpan) sebelum Fase 1.

---

## 5. Keputusan yang Sudah Diambil

- Fase 0B hanya membuat dokumen. Tidak ada kode aplikasi yang diubah.
- `AGENTS.md` dibiarkan apa adanya (tidak diubah/ditimpa).
- Fase 1: semua rekomendasi default disetujui user ("gunakan semua rekomendasi"):
  - `test-email` dibatasi ke `app()->isLocal()` (tetap ada untuk dev).
  - Test lama `DashboardTest`/`ExampleTest` dihapus (route `dashboard`/`home` tak pernah ada).
  - `SESSION_ENCRYPT=true` di `.env.example`.
  - `ADMIN_PATH=admin`, `ADMIN_ALLOWED_IPS=` placeholder di `.env.example` (tanpa nilai nyata).
  - HSTS ditunda ke Fase 9.
  - Blok kredensial demo di `admin/login.blade.php` diganti teks netral.

---

## 6. Status Fase 1 (selesai)

Ditangani:
- Security headers global (`app/Http/Middleware/SecurityHeaders.php`, didaftarkan di `bootstrap/app.php`):
  `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, `Content-Security-Policy-Report-Only`.
- Rate limiter bernama (`app/Providers/AppServiceProvider.php`): `pengajuan` (5/jam), `mitra-login` (5/menit),
  `pddikti` (30/menit), `mitra-profile` (10/menit) — dipasang di `routes/web.php`.
- Proxy PDDIKTI: hapus `withoutVerifying()`, ganti User-Agent palsu, tambah cache 6 jam.
- `.env.example`: `APP_DEBUG=false`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE/HTTP_ONLY/SAME_SITE`,
  `ADMIN_PATH`, `ADMIN_ALLOWED_IPS`, `WA_GATEWAY_URL`, `WA_GATEWAY_KEY`.
- Kredensial demo mitra dihapus dari `resources/views/login.blade.php`.
- Kredensial demo admin dihapus dari `resources/views/admin/login.blade.php`.
- URL hardcoded `127.0.0.1:8000` → `route('landing')`/`config('app.url')`.
- `GET {admin}/test-email` kini `abort_unless(app()->isLocal(), 404)`.
- Test baru `tests/Feature/SecurityBaselineTest.php` (6 test, 14 assertion, hijau).

Belum (di luar cakupan Fase 1, dicatat):
- Akun plaintext di `config/admin_demo.php` masih ada → Fase 2.
- Belum ada UUID/role/Policy → Fase 2.

### Kegagalan pengecekan (semua LAMA, bukan akibat Fase 1)
- `composer lint:check`: `app/Http/Middleware/HideAdmin.php`, `config/admin_demo.php` gagal Pint
  (file ini tidak disentuh Fase 1; file yang disentuh sudah di-`pint`-fix dan lolos).
- `composer types:check`: 15 error PHPStan (UserFactory, helper `routes/admin.php`, `routes/web.php` baris lama).
  Crash memori pada default 128M perlu `--memory-limit=1G`.
- `php artisan test`: 27 test, 13 lolos, 11 error (Auth/Settings merujuk route starter kit yang tidak aktif),
  3 skip. Sebelum Fase 1: 24 test, 7 lolos, 14 error.
- `npm run types:check` dan `npm run check`: gagal di kode React starter kit (`resources/js/**`) yang tidak aktif.

---

## 7. Menunggu Konfirmasi User

Daftar `docs/ATURAN_PEMBARUAN.md` bagian 4 butir 1–14 belum dijawab (selain rekomendasi default Fase 1).
Fase 2 (database + peran) butuh jawaban butir 2, 3, 10 minimal.

---

## 8. Masalah yang Belum Beres

1. `docs/LANGKAH_PEMBARUAN.md` tidak ada; file sebenarnya `docs/LANGKAH_PEMBARUAN (1).md`.
2. `AGENTS.md` dan 3 view berstatus modified — belum di-commit user.
3. `AGENTS.md` §7 memakai perintah `npm run types`/`npm run lint` yang **tidak ada** di `package.json`
   (yang ada: `types:check`, `check`, `check:fix`). `ATURAN_PEMBARUAN.md` §7 sudah memakai perintah benar.
4. `AGENTS.md` §1 mengasumsikan UI = Inertia+React; kenyataan UI = Blade. `AGENTS.md` §6 menyebut
   "komponen shadcn/ui dan TypeScript ketat" — tidak relevan untuk UI Blade yang tayang.

---

## 9. Saran (tidak dibuat tanpa persetujuan)

- Pertimbangkan menambahkan script `npm run types`/`lint` sebagai alias agar `AGENTS.md` §7 valid,
  atau ubah `AGENTS.md` §7 (butuh persetujuan user karena `AGENTS.md` tidak boleh diubah sepihak).

---

## 10. Fase A — Pisah Login dan Prefix per Peran (SELESAI)

Branch kerja: `feat/pisah-login-peran` (bukan `main`, tanpa push). Commit: `feat(auth): pisah login dan prefix per peran`.

### Keadaan sebelum fase (baseline, jangan dikira regresi baru)
- `php artisan test`: **91 test, 57 lolos, 34 gagal**. Semua kegagalan sudah ada sebelum fase ini:
  `routes/admin.php` masih mendaftarkan nama route `admin.*` di prefix `admin-mitrakerjasama`, sementara
  `AdminAkses`/`AdminOnly` sudah memakai `superadmin.*`/`kerjasama.*`. Akibatnya `route('admin.*')` ada
  tetapi middleware melempar 404/500 (`Route [superadmin.login] not defined`).
- `composer lint:check` gagal pada 5 file (config/admin_demo.php, routes/admin.php,
  routes/admin_bersama.php, routes/superadmin.php, tests/Feature/LandingCmsSyncTest.php).
- PHPStan 33 error, semuanya di file lama.

### Masalah akar yang ditemukan dan diperbaiki
1. `routes/admin.php` (618 baris) adalah panel lama yang **tidak pernah dimuat** (tidak ada di
   `bootstrap/app.php`) — seluruh route-nya bergaya `admin.*` dan duplikat dengan `superadmin.php`.
2. `AdminAkses` memanggil `config('admin_akses.peran.superadmin.akun')`, padahal config-nya
   `admin_akses.peran.<peran>` → kredensial tidak pernah terbaca.
3. `AdminAkses::akunAktif()` dipanggil `routes/admin_bersama.php` dan `AdminLoginController` tetapi
   **tidak ada** → fatal error 500 di `/superadmin/login`.
4. `bootstrap/app.php` hanya memuat `routes/admin.php`; `superadmin.php` & `admin_bersama.php` mati
   (karena itu prefix `.env` `SUPERADMIN_PATH`/`ADMIN_KERJASAMA_PATH` tidak berefek).
5. `Route::defaults('peran', ...)` di berkas route dievaluasi saat **boot**, sebelum ada request, sehingga
   kedua prefix mendapat peran yang sama. Peran sekarang dibaca dari nama route hasil grup
   (`AdminAkses::peranDariNamaRoute()`); `routes/admin_bersama.php` tanpa nilai peran tertanam.
6. Logika inbox/draf/masa aktif tadinya akan terduplikasi di dua berkas peran. Dipindah ke
   `routes/admin_kerja.php` (satu berkas, dimuat per prefix; middleware per kemampuan dari peta izin).
   Pembantu global `routes/admin.php` naik ke kelas: `App\Support\PengajuanMasuk` dan
   `App\Support\SuratPenolakan`; gateway WA lewat `config('services.wa_gateway.*')`.

### File

DIBUAT
- `app/Support/AdminAkses.php` — **ditulis ulang**: satu peta peran → kemampuan → route/menu
  (`semua`, `path`, `nama`, `akunAktif`, `peranDariPath`, `peranDariNamaRoute`, `menu`, `menuSesi`,
  `halaman`, `ruteKemampuan`, `pathLama`, `alias`, `beranda`, `boleh`, `peranUntuk`, `peranLoginDi`).
- `app/Support/PengajuanMasuk.php` — penyimpanan pengajuan mode demo, dipakai kedua prefix.
- `app/Support/SuratPenolakan.php` — template surat penolakan + kirim email/WhatsApp (driver log bila
  gateway kosong; nomor disamarkan di log).
- `routes/admin_bersama.php` — login/logout/root per peran (peran dibaca dari nama route).
- `routes/admin_kerja.php` — inbox, draf proposal, masa aktif; dimuat untuk tiap peran.
- `routes/admin_alias.php` — pengalihan GET `/{ADMIN_PATH}/*` → `/{SUPERADMIN_PATH}/*` (tanpa nama route).
- `resources/views/partials/ikon-admin.blade.php` — ikon menu, dipilih lewat kunci `ikon` di config.
- `tests/Feature/Auth/AdminLoginTest.php` — **baru**, 41 test: login per peran, gagal generik, tolak
  login silang peran, throttle per IP+username, regenerasi sesi, logout + regenerasi CSRF, redirect
  beranda, sesi satu peran tidak boleh membuka prefix lain, peran `admin` lama ditolak, redirect alamat
  lama, menu dinamis.

DIUBAH
- `config/admin_akses.php` — peta izin terpusat: `halaman`, `aksi`, `section`, `peran`, `akun_lain`,
  `alias`, `path_lama`. Kemampuan baru dipisah: `tentang`, `artikel`, `galeri` (dulu gabung `konten`).
- `bootstrap/app.php` — per peran: `admin_bersama.php` + `admin_kerja.php`; `superadmin.php` hanya untuk
  peran superadmin; lalu `admin_alias.php`.
- `routes/superadmin.php` — dashboard, konten, uji email; middleware per kemampuan dari peta izin.
- `app/Http/Middleware/AdminOnly.php` — argumen peran opsional + cek prefix; belum login → redirect login
  prefix; peran salah / kemampuan kurang → 404.
- `app/Http/Controllers/AdminLoginController.php` — `Hash::check` atas hash dari `.env`, pesan gagal
  tunggal untuk semua sebab, `session()->regenerate()` saat masuk, `invalidate()` + `regenerateToken()`
  saat keluar, redirect ke beranda peran.
- `app/Providers/AppServiceProvider.php` — rate limiter `admin-login` (5/menit per IP+username).
- `config/services.php` — `wa_gateway.url` / `wa_gateway.key` dari `.env`.
- `config/admin_demo.php` — kredensial plaintext dihapus; semua kunci kosong (penanda agar tidak ada
  pembaca tersisa). TODO-BACKEND: hapus setelah tidak ada pemanggil.
- `.env.example` — `SUPERADMIN_PATH`, `ADMIN_KERJASAMA_PATH`, `SUPERADMIN_*`, `ADMIN_KERJASAMA_*`,
  `DEVELOPER_*` (placeholder, `PASSWORD_HASH` berupa hash bcrypt), `WA_GATEWAY_*`.
- `phpunit.xml` — kredensial testing berupa hash bcrypt (nilai sandi hanya di berkas test).
- `resources/views/layouts/admin.blade.php` — menu + identitas dari peran login, tanpa hardcode.
- `resources/views/admin/login.blade.php` — satu view, judul/label/aksi per peran.
- `resources/views/admin/{dashboard,inbox,draf_proposal,masa_aktif,artikel,galeri,tentang,_cms}.blade.php`
  dan `resources/views/layouts/admin.blade.php` — `route('admin.*')` → nama route per peran lewat
  `AdminAkses::awalanRouteSesi()`; `_cms.blade.php` tidak lagi memakai `config('admin_demo.path')`.
- `tests/Feature/{AdminDrafMasaAktifTest,PerpanjanganTest,LandingCmsSyncTest,AreaMitraTest,SecurityBaselineTest}.php`
  — peran `admin` → `superadmin`, nama route baru. `AdminDrafMasaAktifTest` kini dijalankan dua peran.
- `resources/js/pages/dashboard.tsx`, `resources/js/pages/welcome.tsx`,
  `resources/js/components/app-sidebar.tsx` — impor `@/routes/admin` → `@/routes/superadmin` (modul
  Wayfinder lama hilang saat `routes/admin.php` dihapus).
- `app/Support/{LandingCms,PermohonanPerpanjangan}.php`, `database/factories/UserFactory.php`,
  `routes/web.php` — perbaikan tipe/docblock yang membuat PHPStan gagal (di luar cakupan awal, satu baris
  masing-masing; tanpa perubahan perilaku).

DIHAPUS
- `routes/admin.php` — panel lama duplikat & tak termuat (isi uniknya pindah ke `superadmin.php`,
  `admin_kerja.php`).

### Keamanan (bagian 5)
- [x] Kredensial hanya hash bcrypt dari `.env` / `phpunit.xml`; tidak ada plaintext di config, kode, log,
      atau dokumentasi (`config/admin_demo.php` dikosongkan; grep `admin_demo` hanya menyisakan catatan).
- [x] Login silang peran ditolak dengan pesan **identik** dengan akun tak dikenal
      (`AdminLoginTest`: "pesan penolakan silang peran tidak membocorkan keberadaan akun").
- [x] Session diregenerasi saat login, `invalidate()` + `regenerateToken()` saat logout (dua test).
- [x] Throttle `admin-login` per IP+username (dua test; satu membuktikan username lain tetap boleh).
- [x] Otorisasi peran di middleware, bukan menu tersembunyi: sesi peran lain → 404 (dua test);
      kemampuan konten (`tentang`, `artikel`, `galeri`, `dashboard`) → 404 untuk `admin_kerjasama`.
- [x] Peran `admin` lama tidak lagi dikenali (`AdminAkses::adaPeran('admin')` false; sesi lama → 404).
- [x] Nomor WhatsApp disamarkan di log; gateway hanya dipanggil bila `WA_GATEWAY_URL` + `WA_GATEWAY_KEY` terisi.
- [x] Tidak ada URL langsung ke berkas; pengalihan alamat lama hanya GET (POST ke URL lama 404/405).

### Pengecekan (dijalankan ulang setelah semua perubahan)
- `php vendor/bin/pint --test` → **passed** (sebelumnya 5 file gagal).
- `php -d memory_limit=1G vendor/bin/phpstan analyse --no-progress` → **passed, 0 error** (sebelumnya 33).
- `php artisan test --without-tty` → **130 test, 130 lolos, 0 gagal** (baseline 91 test, 57 lolos).
- `npm run build` → **berhasil**, 2280 modul.
- `npm run types:check` → **45 error, semua sudah ada sebelumnya** (baseline 48): seluruhnya di keluaran
  Wayfinder yang di-`gitignore` (`resources/js/actions/**`, `resources/js/routes/mitra`) dan tiga berkas
  starter kit yang tidak terpakai (`components/app-header.tsx`, `layouts/auth/auth-card-layout.tsx`,
  `layouts/auth/auth-split-layout.tsx` — mengimpor `home`/`dashboard` dari `@/routes` yang tidak
  diekspor). Tidak ada di berkas yang diubah fase ini.
- `npm run check` (Biome) → **gagal**, juga sudah ada sebelumnya: Biome memindai
  `public/assets/user/css/login.css` (berisi sintaks Blade `{{-- --}}`) dan `README.md`.

### Catatan untuk backend (belum dibangun di fase ini)
- Semua peran masih dari config + session, belum tabel `users` dengan kolom role. Ganti
  `AdminAkses::akunAktif()` dengan guard Eloquent tanpa mengubah kontrak route/controller.
- Data pengajuan, draf, masa aktif, perpanjangan, CMS masih berkas JSON di `storage/app` (tanpa locking).
- Belum ada UUID untuk pengajuan; route admin masih `{id}` berurutan.
- `config/admin_demo.php` bisa dihapus setelah dipastikan tidak ada pemanggil.

### Menunggu konfirmasi user
- Nilai `SUPERADMIN_USERNAME`/`PASSWORD_HASH`, `ADMIN_KERJASAMA_USERNAME`/`PASSWORD_HASH`,
  `DEVELOPER_*` untuk `.env` nyata (sengaja kosong di repo; login tertutup selama kosong).
- Apakah akun `developer` tetap dipertahankan hanya untuk `/test-email` lokal.
- Kebijakan `admin_kerjasama` tidak boleh menolak pengajuan (surat resmi + WhatsApp) — saat ini dibatasi
  ke `superadmin`; mohon dikonfirmasi apakah itu yang diinginkan.

### Langkah berikutnya
- Fase B (menunggu perintah "Lanjut"), setelah keputusan backend tabel `users` + role.

## 11. Fase B — Matriks Akses, Log Aktivitas, Halaman Superadmin (SELESAI)

Branch kerja: `feat/pisah-login-peran` (bukan `main`, tanpa push).

### Matriks yang berlaku (ditegakkan SERVER, bukan menu)

| Kemampuan | superadmin | admin_kerjasama |
|---|---|---|
| `dashboard` | ya | tidak (login langsung ke Inbox) |
| `inbox` (lihat) | ya | ya |
| `inbox.approve` (setuju) | ya | **tidak, 404** |
| `inbox.reject` (tolak + surat & WA) | ya | **tidak, 404** |
| `draf` / `draf_proposal.unduh` | ya | ya |
| `masa_aktif` / `masa_aktif.atur` | ya | ya |
| `tentang`, `artikel`, `galeri` | ya | tidak |
| `aktivitas` | ya | tidak |
| `pengguna` | ya | tidak |
| `uji_email` | hanya akun `developer` | tidak |

### Perubahan inti

1. **Peta izin memisahkan halaman dan aksi.** `config/admin_akses.php` kini punya
   `halaman` (bermenu, punya `route`/`label`/`ikon`) dan `aksi` (tanpa menu, kuncinya
   sama dengan akhiran nama route: `inbox.approve`, `inbox.reject`, `draf_proposal.unduh`,
   `masa_aktif.atur`). `peran.<nama>.kemampuan[]` memuat keduanya, sehingga
   `admin.only:<peran...>` dibentuk otomatis dari peta. Aksi tanpa peran pemilik → 404.
2. **Inbox pindah dari closure route ke controller.** `AdminInboxController` melayani
   kedua prefix (satu implementasi), memanggil `LogAktivitas::catat()` setelah aksi
   benar-benar terjadi (bukan saat ditolak).
3. **Log aktivitas.** `app/Support/LogAktivitas.php`, berkas
   `storage/app/log_aktivitas.json`, tulis dengan `LOCK_EX`. Kolom: waktu, peran, nama,
   aksi, target, IP. Tidak pernah mencatat sandi/hash/isi surat.
   TODO-BACKEND tercatat di kelas: pindah ke tabel `log_aktivitas`.
4. **Menu dirender dari peta.** Layout tidak lagi memuat blok sidebar hardcode
   (`Lihat Portal Umum` dulu ditulis manual dengan SVG inline). Tautan luar sekarang
   berasal dari `admin_akses.tautan[]`; badge/counter dashboard hanya muncul untuk peran
   yang punya kemampuan `dashboard`, sehingga admin_kerjasama tidak menerima markup
   milik superadmin sama sekali.
5. **Halaman baru superadmin.** `/superadmin/aktivitas` (filter peran/aksi/tanggal,
   pencarian, paginasi, state kosong, read-only) dan `/superadmin/pengguna`
   (daftar akun dari peta izin, tanpa hash, tombol aksi `disabled` + tooltip
   "Menunggu backend").
6. **Form keputusan inbox tidak dirender** untuk peran tanpa `inbox.approve`/`inbox.reject`;
   JS-nya diberi guard agar tidak error saat elemen tidak ada.

### File

DIBUAT
- `app/Support/LogAktivitas.php` — catat/saring/paginasi log, label aksi dari peta izin.
- `app/Http/Controllers/AdminInboxController.php` — indeks/setujui/tolak, satu controller dua prefix.
- `app/Http/Controllers/AdminSuperadminController.php` — `aktivitas()` dan `pengguna()` (read-only).
- `resources/views/admin/aktivitas.blade.php` — tabel log + filter + paginasi + state kosong.
- `resources/views/admin/pengguna.blade.php` — daftar akun, tanpa hash, aksi disabled.
- `tests/Feature/Auth/MatriksAksesTest.php` — 14 test matriks peran × route × method.
- `tests/Feature/Auth/LogAktivitasTest.php` — 14 test log + halaman.

DIUBAH
- `config/admin_akses.php` — `halaman` vs `aksi`, matriks peran, `tautan[]`, section `sistem`/`luar`.
- `app/Support/AdminAkses.php` — `ruteKemampuan()` menerima aksi; `menu()` mengikutkan `tautan[]`;
  `semuaAksi()`/`labelAksi()`; `ruteKemampuanSemua()` (tidak terpakai) dihapus.
- `routes/admin_kerja.php` — route per aksi, memakai `AdminInboxController`.
- `routes/superadmin.php` — tambah `/aktivitas` dan `/pengguna`.
- `resources/views/layouts/admin.blade.php` — sidebar sepenuhnya dari peta izin.
- `resources/views/partials/ikon-admin.blade.php` — ikon `aktivitas`, `pengguna`, `portal`.
- `resources/views/admin/inbox.blade.php` — form keputusan bersyarat + guard JS.
- `public/assets/admin/css/panel.css` — `.filter-grid`, `.pagination-nav`, `.btn[disabled]`.
- `app/Http/Controllers/AdminMitraController.php` — log pada `draf_proposal.unduh` & `masa_aktif.atur`.

### Keamanan (bagian 5)
- [x] Otorisasi di server: setiap route panel memakai `admin.only:<peran dari peta izin>`;
      test membuktikan semua route superadmin menolak admin_kerjasama (semua metode).
- [x] Tidak ada duplikasi logika halaman kerja sama: satu controller, satu berkas route.
- [x] Log tidak memuat sandi/hash (`LogAktivitasTest`: "log tidak pernah memuat kata sandi atau hash").
- [x] Halaman pengguna tidak pernah menampilkan hash (test memeriksa `$2y$` dan `password_hash`).
- [x] Filter tanggal divalidasi `date_format:Y-m-d` agar input salah tidak masuk kueri.
- [x] Aksi yang ditolak middleware / target tidak ditemukan tidak menghasilkan log palsu.

### Pengecekan
- `php artisan test` → **158 test, 158 lolos, 559 assertion** (sebelum tahap ini 130).
- `php vendor/bin/pint --test` → passed.
- `php -d memory_limit=1G vendor/bin/phpstan analyse` → passed, 0 error.
- `npm run build` → berhasil, 2280 modul.
- `npm run types:check` → tidak berubah dari catatan fase A (45 error lama di keluaran
  Wayfinder gitignored + 3 berkas starter kit tak terpakai).

### Catatan untuk backend
- `LogAktivitas` → tabel `log_aktivitas` (indeks `(peran, aksi, dibuat_pada)`); paginasi SQL.
- `pengguna` → tabel `users` + kolom role; tombol Ubah/Reset Sandi masih disabled.
- Pengajuan masih `{id}` berurutan dan JSON tanpa locking; belum UUID sebagai route key.
- `AdminOnly` memakai 404 (bukan 403) untuk peran salah — konsisten dengan pola proyek.

### Menunggu konfirmasi
- Apakah `admin_kerjasama` memang tidak boleh menolak pengajuan (saat ini tidak boleh,
  sesuai matriks). Bila perlu, tambahkan `inbox.reject` ke kemampuan peran itu — cukup
  satu baris di config, tanpa mengubah route/controller.

### Langkah berikutnya
- Menunggu perintah "Lanjut" untuk tahap berikutnya.

## 11. Fase B — Matriks Akses, Log Aktivitas, Halaman Superadmin (SELESAI)

Branch kerja: `feat/pisah-login-peran` (bukan `main`, tanpa push).

### Matriks yang berlaku (ditegakkan SERVER, bukan menu)

| Kemampuan | superadmin | admin_kerjasama |
|---|---|---|
| `dashboard` | ya | tidak (login langsung ke Inbox) |
| `inbox` (lihat) | ya | ya |
| `inbox.approve` (setuju) | ya | **tidak, 404** |
| `inbox.reject` (tolak + surat & WA) | ya | **tidak, 404** |
| `draf` / `draf_proposal.unduh` | ya | ya |
| `masa_aktif` / `masa_aktif.atur` | ya | ya |
| `tentang`, `artikel`, `galeri` | ya | tidak |
| `aktivitas` | ya | tidak |
| `pengguna` | ya | tidak |
| `uji_email` | hanya akun `developer` | tidak |

### Perubahan inti

1. **Peta izin memisahkan halaman dan aksi.** `config/admin_akses.php` kini punya
   `halaman` (bermenu, punya `route`/`label`/`ikon`) dan `aksi` (tanpa menu, kuncinya
   sama dengan akhiran nama route: `inbox.approve`, `inbox.reject`, `draf_proposal.unduh`,
   `masa_aktif.atur`). `peran.<nama>.kemampuan[]` memuat keduanya, sehingga
   `admin.only:<peran...>` dibentuk otomatis dari peta. Aksi tanpa peran pemilik → 404.
2. **Inbox pindah dari closure route ke controller.** `AdminInboxController` melayani
   kedua prefix (satu implementasi), memanggil `LogAktivitas::catat()` setelah aksi
   benar-benar terjadi (bukan saat ditolak).
3. **Log aktivitas.** `app/Support/LogAktivitas.php`, berkas
   `storage/app/log_aktivitas.json`, tulis dengan `LOCK_EX`. Kolom: waktu, peran, nama,
   aksi, target, IP. Tidak pernah mencatat sandi/hash/isi surat.
   TODO-BACKEND tercatat di kelas: pindah ke tabel `log_aktivitas`.
4. **Menu dirender dari peta.** Layout tidak lagi memuat blok sidebar hardcode
   (`Lihat Portal Umum` dulu ditulis manual dengan SVG inline). Tautan luar sekarang
   berasal dari `admin_akses.tautan[]`; badge/counter dashboard hanya muncul untuk peran
   yang punya kemampuan `dashboard`, sehingga admin_kerjasama tidak menerima markup
   milik superadmin sama sekali.
5. **Halaman baru superadmin.** `/superadmin/aktivitas` (filter peran/aksi/tanggal,
   pencarian, paginasi, state kosong, read-only) dan `/superadmin/pengguna`
   (daftar akun dari peta izin, tanpa hash, tombol aksi `disabled` + tooltip
   "Menunggu backend").
6. **Form keputusan inbox tidak dirender** untuk peran tanpa `inbox.approve`/`inbox.reject`;
   JS-nya diberi guard agar tidak error saat elemen tidak ada.

### File

DIBUAT
- `app/Support/LogAktivitas.php` — catat/saring/paginasi log, label aksi dari peta izin.
- `app/Http/Controllers/AdminInboxController.php` — indeks/setujui/tolak, satu controller dua prefix.
- `app/Http/Controllers/AdminSuperadminController.php` — `aktivitas()` dan `pengguna()` (read-only).
- `resources/views/admin/aktivitas.blade.php` — tabel log + filter + paginasi + state kosong.
- `resources/views/admin/pengguna.blade.php` — daftar akun, tanpa hash, aksi disabled.
- `tests/Feature/Auth/MatriksAksesTest.php` — 14 test matriks peran × route × method.
- `tests/Feature/Auth/LogAktivitasTest.php` — 14 test log + halaman.

DIUBAH
- `config/admin_akses.php` — `halaman` vs `aksi`, matriks peran, `tautan[]`, section `sistem`/`luar`.
- `app/Support/AdminAkses.php` — `ruteKemampuan()` menerima aksi; `menu()` mengikutkan `tautan[]`;
  `semuaAksi()`/`labelAksi()`; `ruteKemampuanSemua()` (tidak terpakai) dihapus.
- `routes/admin_kerja.php` — route per aksi, memakai `AdminInboxController`.
- `routes/superadmin.php` — tambah `/aktivitas` dan `/pengguna`.
- `resources/views/layouts/admin.blade.php` — sidebar sepenuhnya dari peta izin.
- `resources/views/partials/ikon-admin.blade.php` — ikon `aktivitas`, `pengguna`, `portal`.
- `resources/views/admin/inbox.blade.php` — form keputusan bersyarat + guard JS.
- `public/assets/admin/css/panel.css` — `.filter-grid`, `.pagination-nav`, `.btn[disabled]`.
- `app/Http/Controllers/AdminMitraController.php` — log pada `draf_proposal.unduh` & `masa_aktif.atur`.

### Keamanan (bagian 5)
- [x] Otorisasi di server: setiap route panel memakai `admin.only:<peran dari peta izin>`;
      test membuktikan semua route superadmin menolak admin_kerjasama (semua metode).
- [x] Tidak ada duplikasi logika halaman kerja sama: satu controller, satu berkas route.
- [x] Log tidak memuat sandi/hash (`LogAktivitasTest`: "log tidak pernah memuat kata sandi atau hash").
- [x] Halaman pengguna tidak pernah menampilkan hash (test memeriksa `$2y$` dan `password_hash`).
- [x] Filter tanggal divalidasi `date_format:Y-m-d` agar input salah tidak masuk kueri.
- [x] Aksi yang ditolak middleware / target tidak ditemukan tidak menghasilkan log palsu.

### Pengecekan
- `php artisan test` → **158 test, 158 lolos, 559 assertion** (sebelum tahap ini 130).
- `php vendor/bin/pint --test` → passed.
- `php -d memory_limit=1G vendor/bin/phpstan analyse` → passed, 0 error.
- `npm run build` → berhasil, 2280 modul.
- `npm run types:check` → tidak berubah dari catatan fase A (45 error lama di keluaran
  Wayfinder gitignored + 3 berkas starter kit tak terpakai).

### Catatan untuk backend
- `LogAktivitas` → tabel `log_aktivitas` (indeks `(peran, aksi, dibuat_pada)`); paginasi SQL.
- `pengguna` → tabel `users` + kolom role; tombol Ubah/Reset Sandi masih disabled.
- Pengajuan masih `{id}` berurutan dan JSON tanpa locking; belum UUID sebagai route key.
- `AdminOnly` memakai 404 (bukan 403) untuk peran salah — konsisten dengan pola proyek.

### Menunggu konfirmasi
- Apakah `admin_kerjasama` memang tidak boleh menolak pengajuan (saat ini tidak boleh,
  sesuai matriks). Bila perlu, tambahkan `inbox.reject` ke kemampuan peran itu — cukup
  satu baris di config, tanpa mengubah route/controller.

### Langkah berikutnya
- Menunggu perintah "Lanjut" untuk tahap berikutnya.

## 12. Fase C — Admin Kerja Sama Memutuskan Pengajuan (SELESAI)

Perubahan keputusan user: **Admin Kerja Sama kini boleh menyetujui, menolak, dan
mengunduh draf** — sama seperti Superadmin. Sebelumnya fase B membatasinya
(hanya lihat) dan keputusan itu dibatalkan di sini.

### Matriks terbaru

| Kemampuan | superadmin | admin_kerjasama |
|---|---|---|
| `dashboard` | ya | tidak (login langsung ke Inbox) |
| `inbox` (lihat) | ya | ya |
| `inbox.approve` | ya | **ya** |
| `inbox.reject` (surat resmi + WhatsApp) | ya | **ya** |
| `draf` / `draf_proposal.unduh` | ya | ya |
| `masa_aktif` / `masa_aktif.atur` | ya | ya |
| `tentang`, `artikel`, `galeri`, `aktivitas`, `pengguna` | ya | **tidak (404)** |

Perbedaan yang tetap berlaku: Admin Kerja Sama tidak punya dashboard, tidak
mengelola konten portal, tidak melihat log aktivitas, dan tidak mengelola pengguna.
Akses ke route `/superadmin/*` tetap ditolak 404 walau perannya kini punya aksi yang sama.

### File

DIUBAH
- `config/admin_akses.php` — kemampuan `admin_kerjasama` ditambah `inbox.approve`,
  `inbox.reject`; komentar matriks diperbarui.
- `tests/Feature/Auth/MatriksAksesTest.php` — kontrak matriks baru; test formulir
  keputusan kini memastikan form mengarah ke `/admin-kerjasama/inbox/…`, bukan superadmin;
  tambah test bahwa aksi superadmin lewat URL superadmin tetap 404.
- `tests/Feature/Auth/LogAktivitasTest.php` — tambah test log tercatat saat
  Admin Kerja Sama menyetujui/menolak; test "ditolak middleware" memakai route superadmin.

Tidak ada perubahan pada controller, route, atau view: `AdminInboxController` dan
`admin_kerja.php` memang sudah satu implementasi untuk dua prefix sejak fase B.

### Pengecekan
- `php artisan test` → **160 test, 160 lolos**.
- `pint --test` → passed; `phpstan` → 0 error; `npm run build` → berhasil.

### Catatan keamanan
Menolak pengajuan mengirim email resmi dan pesan WhatsApp. Kini kedua peran dapat
melakukannya, dan setiap keputusan tercatat di log aktivitas (peran, nama, aksi,
target, IP) sehingga jejak siapa memutuskan tetap ada.

## 13. Fase D — Akun Demo Panel Admin (SELESAI)

### Masalah
Kredensial panel dibaca dari `.env`, sedangkan `.env` ada di `.gitignore`. Di
mesin lain (mis. laptop rekan) `.env` baru berisi placeholder kosong, sehingga
`AdminAkses::akunAktif()` mengembalikan `null` dan login admin **mati total**
dengan pesan "Nama pengguna atau kata sandi tidak sesuai." Password juga tidak
"terlihat" di VS Code karena yang disimpan hanya hash bcrypt — memang tidak bisa
dibalik menjadi plaintext.

### Keputusan
Menambah akun demo yang **ikut ter-commit** di `config/admin_akses.php`
(`demo`). Urutannya: nilai `.env` menang; bila `.env` kosong dan
`APP_ENV` bukan `production`, akun demo dipakai. Di production akun demo
tidak pernah aktif.

Kata sandi demo disimpan plaintext di config (memang data dummy yang
dipublikasikan agar bisa dipakai siapa pun) dan dibandingkan dengan
`hash_equals`. Akun `.env` tetap memakai `Hash::check` (bcrypt).

### Kredensial demo
| Peran | URL login | Username | Password |
|---|---|---|---|
| Superadmin | `/superadmin/login` | `superadmin` | `123456` |
| Admin Kerja Sama | `/admin-kerjasama/login` | `kerjasama` | `123456` |
| Developer | `/superadmin/login` | `developer` | `123456` |

Kredensial demo tetap berfungsi, tetapi **tidak lagi ditampilkan di halaman
login**. Blok "Catatan pengembangan" / "Akun demo" sudah dihapus dari semua
halaman login agar tampilan bersih. Rujukan kredensial: tabel di atas,
`config/admin_akses.php`, dan `config/mitra.php`.

### File
DIBUAT
- `tests/Feature/Auth/AdminLoginDemoTest.php` — 7 test: demo aktif saat `.env`
  kosong, login sukses per peran, sandi salah ditolak, silang peran ditolak,
  kredensial tampil di halaman login, dan demo mati total di production.

DIUBAH
- `config/admin_akses.php` — blok `demo` (3 akun) + komentar urutan sumber.
- `app/Support/AdminAkses.php` — `akunAktif()` jatuh ke `akunDemo()`;
  tambah `akunDemo()`, `kredensialDemo()`; `akunAktif()` kini mengembalikan
  kunci `password` (null untuk akun `.env`).
- `app/Http/Controllers/AdminLoginController.php` — `sandiCocok()` memilih
  `Hash::check` atau `hash_equals`; mengirim `demo` ke view login.
- `resources/views/admin/login.blade.php` — menampilkan kredensial demo.
- `resources/views/login.blade.php` — menampilkan kredensial mitra demo.
- `.env.example` — dokumentasi bahwa kosong = pakai akun demo.
- `tests/Feature/AreaMitraTest.php` — teks bantuan login mitra disesuaikan.

### Pengecekan
- `php artisan test` → **167 test, 167 lolos** (590 assertion).
- `pint --test` → passed; `phpstan` → 0 error.
- Verifikasi HTTP nyata dengan `.env` dikosongkan: superadmin/123456 →
  sesi `superadmin`; kerjasama/123456 → sesi `admin_kerjasama`; sandi salah dan
  silang peran → ditolak dengan pesan generik.

### Catatan keamanan
Akun demo hanya hidup di luar production. Sebelum deploy: set
`APP_ENV=production`, isi `SUPERADMIN_PASSWORD_HASH` dan
`ADMIN_KERJASAMA_PASSWORD_HASH` dengan bcrypt, dan set
`ADMIN_ALLOWED_IPS` bila panel perlu dibatasi per IP.

### Masih terbuka (belum dikerjakan, butuh keputusan)
- Area mitra (`/mitra/*`) belum memakai guard/tabel mitra sungguhan. Pembatas
  `mitra.only` hanya memeriksa sesi; lihat "Catatan untuk backend" di fase E.
- Kata sandi demo (`123456`, `superadmin123`, dst.) sengaja lemah karena data
  dummy; jangan dipakai di server yang dapat diakses publik.

## 14. Fase E — Tiga Pintu Masuk Benar-Benar Terpisah (SELESAI)

### Masalah yang dilaporkan
Halaman Superadmin dan Admin Kerja Sama terasa "langsung masuk" tanpa login,
padahal sebelumnya ada halaman login. Setelah ditelusuri, ada dua sebab berbeda:

1. **Area mitra memang tanpa pembatas.** Route `/mitra*` tidak punya middleware
   apa pun, sehingga `/mitra` menjawab `200` untuk siapa pun. Ini yang paling
   terasa sebagai "langsung masuk", karena `MitraController` mengisi data dari
   `DataDemo` sehingga halaman tampak berisi padahal belum login.
2. **Pintu login mitra tidak memeriksa apa pun.** `POST /login` hanya
   `redirect()->route('mitra.status')` — siapa pun bisa "masuk" tanpa kredensial.

Panel admin sendiri **sudah** terlindungi (`admin.only` → 302 ke login). Yang
belum adalah mitra, dan pintu mitra yang tidak memverifikasi kredensial.

### Keputusan
Tiga pintu masuk dipisah tegas: `/login` (mitra), `/admin-kerjasama/login`,
`/superadmin/login`. Tiap area hanya terbuka setelah login di pintunya sendiri.

- `POST /login` kini memverifikasi `mitra.demo_user` / `mitra.demo_password`,
  memakai `hash_equals`, regenerasi sesi, dan mengisi `mitra_data.username`.
- Route `/mitra*` dibungkus middleware baru `mitra.only` (alias di
  `bootstrap/app.php`). Belum masuk → redirect ke `/login` dengan `intended`.
- Sesi admin tidak dihitung sebagai sesi mitra, dan sebaliknya.
- Kata sandi demo admin **dibedakan per peran** (`superadmin123`,
  `kerjasama123`, `developer123`). Sebelumnya ketiganya `123456`, sehingga
  akun Superadmin bisa dipakai masuk ke pintu Admin Kerja Sama.
- `POST /logout` mitra kini menginvalidasi sesi dan meregenerasi token CSRF.

### Kredensial demo (berlaku di luar production)
| Peran | URL login | Username | Password |
|---|---|---|---|
| Mitra | `/login` | `user1` | `123456` |
| Superadmin | `/superadmin/login` | `superadmin` | `superadmin123` |
| Admin Kerja Sama | `/admin-kerjasama/login` | `kerjasama` | `kerjasama123` |
| Developer | `/superadmin/login` | `developer` | `developer123` |

### File
DIBUAT
- `app/Http/Middleware/MitraOnly.php` — penjaga area mitra.
- `tests/Feature/Auth/PintuMasukTest.php` — 20 test: area mitra terkunci,
  aksi tulis terkunci, `intended`, sandi salah, silang pintu, logout, dan
  sesi admin tidak membuka area mitra.

DIUBAH
- `routes/web.php` — `POST /login` memverifikasi kredensial; grup `mitra.only`
  membungkus seluruh route mitra; logout menginvalidasi sesi.
- `bootstrap/app.php` — alias middleware `mitra.only`.
- `config/admin_akses.php` — kata sandi demo per peran dibedakan.
- `resources/views/login.blade.php` — menampilkan `login_error` (satu alert).
- `tests/Feature/Auth/AdminLoginDemoTest.php` — mengikuti kata sandi baru;
  tambah test kata sandi tiap peran berbeda dan silang pintu.
- `tests/Feature/AreaMitraTest.php` — route `/mitra/draf` diuji dengan sesi.
- `tests/Feature/PerpanjanganTest.php` — sesi admin di-flush sebelum membuka
  halaman mitra (sesi tidak boleh tercampur dalam satu test).

### Pengecekan
- `php artisan test` → **192 test, 192 lolos** (663 assertion).
- `pint --test` → passed; `phpstan` → 0 error; `npm run build` → berhasil.
- Verifikasi HTTP nyata: tanpa login keempat area → 302 ke login masing-masing;
  login benar → beranda peran; keempat kombinasi silang pintu → ditolak.

### Catatan untuk backend
`mitra.only` hanya memeriksa `mitra_data.username` di sesi. Ini **bukan** guard
sungguhan: belum ada tabel mitra, hash kata sandi, atau Policy kepemilikan.
Saat backend dibangun: ganti sumber identitas di middleware ini dengan guard
`mitra`, pindahkan kredensial ke tabel `mitra` (kolom `password` + `must_change_password`),
dan tambahkan Policy agar mitra hanya membaca datanya sendiri.

## 15. Template MoU per Bidang (SELESAI)

### Ringkasan
Halaman `/superadmin/template-mou` khusus Superadmin: daftar template tiap
bidang dari `config/kerjasama.php` (VSGA, GTA, dan slot bidang baru), editor
teks dengan chip placeholder, pratinjau memakai data contoh, tombol
"Kembalikan ke bawaan" berkonfirmasi, serta riwayat perubahan (siapa, kapan,
versi) dari log aktivitas. Admin Kerja Sama, Developer, dan Mitra ditolak 404
untuk GET, PUT, maupun POST.

### Keputusan
- Penyimpanan tetap `storage/app/template_mou.json` (mode demo) dengan tambahan
  `versi`, `diperbarui_oleh`, `diperbarui_pada`. Setiap simpan/reset menaikkan versi.
- Riwayat memakai `LogAktivitas` (`template_mou.update`, `template_mou.reset`);
  isi template tidak dicatat ke log, hanya kode bidang + versi.
- Isi template adalah teks biasa. Form Request menolak tag HTML dan placeholder
  di luar daftar dikenal; render mitra dan pratinjau tetap di-escape Blade.
- Generator mitra membaca template terbaru saat halaman dibuka. Draf yang sudah
  disimpan di sesi mitra tidak diubah, sesuai permintaan.
- Bidang ketiga belum ditentukan mentor; halaman otomatis menampilkan slot baru
  ("Belum ada template") begitu kodenya ditambahkan ke config.

### File
DIBUAT
- `app/Http/Requests/SimpanTemplateMouRequest.php` — validasi judul/catatan/isi,
  tolak tag HTML dan placeholder asing.
- `resources/views/admin/template_mou.blade.php` — daftar bidang, editor,
  chip placeholder, pratinjau contoh, riwayat.
- `tests/Feature/TemplateMouAdminTest.php` — 12 test: simpan sukses + log,
  placeholder asing, HTML, field wajib, role lain (GET/PUT/POST), kode asing,
  reset, slot bidang baru, generator memakai versi terbaru.

DIUBAH
- `app/Support/TemplateMou.php` — metadata versi, `simpanPerubahan`,
  `kembalikan`, `bawaan`, daftar placeholder, deteksi HTML, pratinjau.
- `app/Http/Controllers/AdminSuperadminController.php` — aksi editor + riwayat.
- `routes/superadmin.php` — GET/PUT/POST dengan kode bidang dibatasi config.
- `config/admin_akses.php` — kemampuan `template_mou`, aksi
  `template_mou.update`/`reset`, entri menu Konten.
- `public/assets/admin/css/panel.css` — grid editor, chip, pratinjau, reset.
- `tests/Feature/Auth/MatriksAksesTest.php` — parameter route diambil dari
  definisi route, sehingga route ber-`{kode}` ikut teruji.

### Pengecekan
- `php artisan test` → 256 test, 256 lolos (929 assertion).
- `pint --test` → passed; `phpstan` (memory 1G) → 0 error; `npm run build` → berhasil.
- `npm run types:check` → gagal, tetapi hanya pada berkas Wayfinder yang sudah
  ada sebelumnya (`resources/js/actions/...`, `resources/js/routes/...`); tidak
  ada perubahan TypeScript pada fase ini.

### Catatan untuk backend
Belum ada tabel `template_mou` maupun tabel riwayat versi; riwayat penuh hanya
tersimpan di `log_aktivitas.json` dan halaman menampilkan 8 perubahan terakhir.
Saat backend dibangun: tabel template (per bidang) + tabel versi (isi tiap
versi), Policy/gerbang khusus Superadmin, dan audit trail SQL.

## 16. Fondasi Template MoU Berbasis Blok (SELESAI)

### Ringkasan
Fondasi baru untuk generator MoU: config nilai tetap, struktur template berblok
(sampul → pembukaan → menimbang → mengingat → pasal[] → penutup → tanda_tangan
→ lampiran), dan daftar placeholder terpusat. Terpisah dari `TemplateMou` lama
(flat text) — fondasi ini belum dipakai view/controller mana pun.

### Sumber referensi
`docs/referensi-mou/REFERENSI_STRUKTUR_MOU.md` **tidak ada** di repo (folder
kosong). Referensi nyata = 3 PDF scan di `routes/MOU/` (tanpa text layer),
di-OCR (easyocr, ini alat luar repo) untuk mengekstrak struktur:
- `MoU Dengan Tanjungbalai.pdf` → Nota Kesepakatan Pemda (Pasal 1–15).
- `Kota Jambi MoU.pdf` → variasi Nota Kesepakatan Pemda.
- `Politeknik Caltex Riau (PKS).pdf` → PKS Beasiswa Talenta Digital (DTS), 15 pasal + lampiran rencana aksi.

### Keputusan
- Nilai tetap di `config/mou.php`: `pemetaan` (bidang → template, tanda
  `// KONFIRMASI MENTOR`), `pihak_kesatu` (nama unit, alamat, telepon, email
  dari bagian korespondensi contoh; penandatangan/jabatan default
  `[ISI penandatangan Pihak Kesatu]`; pejabat pelaksana Kepala Balai Besar
  Pelatihan SDM Komdigi Medan), `dasar_hukum_nasional` (7 peraturan, tanda
  `// VERIFIKASI nomor peraturan`), `pembiayaan_varian` (APBN/APBD/Cost Sharing,
  tanda `// VERIFIKASI MENTOR`).
- Struktur template disimpan sebagai **JSON di `storage/app/mou/*.json`**
  (sesuai permintaan) dan **di-commit**: pola proyek umumnya men-*generate*
  storage, tetapi template ini aset konten yang harus ada sejak clone pertama.
  `storage/app/.gitignore` dikecualikan khusus untuk `mou/*.json`.
- Daftar placeholder tunggal di `app/Support/Mou/PendaftaranPlaceholder.php`
  (nama, sumber: form|lengkapi_mitra|config|turunan, keterangan, wajib) dipakai
  renderer, form lengkapi data, dan editor.
- Loader/validator `app/Support/Mou/StrukturTemplateMou.php`: pemetaan bidang,
  validasi blok, deteksi placeholder yatim. TODO-BACKEND: pindah ke tabel
  `mou_template`.

### File
DIBUAT
- `config/mou.php` — pemetaan, pihak_kesatu, dasar_hukum_nasional, pembiayaan_varian.
- `app/Support/Mou/PendaftaranPlaceholder.php` — daftar placeholder terpusat.
- `app/Support/Mou/StrukturTemplateMou.php` — loader, validasi blok, pemetaan bidang, cek yatim.
- `storage/app/mou/nota_kesepakatan_pemda.json` — template GTA (Nota Kesepakatan Pemda, 14 pasal).
- `storage/app/mou/pks_beasiswa_talenta_digital.json` — template VSGA (PKS Beasiswa Talenta Digital, 15 pasal).
- `storage/app/mou/kerangka_umum.json` — template bidang_3 (kerangka netral, 10 pasal, status draf).
- `tests/Feature/Mou/FondasiTemplateMouTest.php` — 19 test.
DIUBAH
- `storage/app/.gitignore` — kecualikan `mou/*.json` agar template ter-commit.

### Keamanan
- [x] Placeholder asing (yatim) terdeteksi: `StrukturTemplateMou::placeholderYatim`
      + test "tidak ada placeholder yatim di seluruh template".
- [x] Placeholder terpusat satu sumber: test "setiap placeholder yang dipakai
      template terdaftar".
- [x] Tidak ada teks MoU di controller/view; semua dari config + template.

### Pengecekan
- `php artisan test` → 278 test, 277 lolos (1599 assertion). 1 gagal
  `LandingCmsSyncTest.php:68` (**pra-eksisting**, bukan dari perubahan ini;
  terbukti gagal juga saat perubahan ini tidak diterapkan).
- `pint --test` (file baru) → passed; `phpstan` (file baru) → 0 error.
- `npm run build` → berhasil. `npm run types:check` tidak dijalankan (tanpa
  perubahan TypeScript).

### Menunggu konfirmasi user
- `config/mou.php` `pemetaan`: `bidang_3` masih placeholder (kode bidang ketiga
  belum ditentukan mentor; belum ada di `config/kerjasama.php`).
- Nomor peraturan `dasar_hukum_nasional` (7 butir) perlu verifikasi mentor.
- Teks `pembiayaan_varian` perlu verifikasi mentor.
- `pihak_kesatu.penandatangan`/`jabatan` default masih `[ISI ...]`.

### Langkah berikutnya
- Fase berikut: renderer blok → HTML/dokumen, form "lengkapi data mitra", dan
  editor template berbasis blok. Menunggu perintah "Lanjut".

## 17. Isi Template MoU A, B, dan Kerangka Bidang 3 (SELESAI)

### Ringkasan
Tiga template diisi dengan **redaksi referensi apa adanya** (dari OCR PDF di
`routes/MOU/`), ditambah **penomoran pasal dinamis**, mekanisme varian
pembiayaan, dan gerbang status `draf`. Template A = Nota Kesepakatan Pemda
(Tanjungbalai), Template B = PKS Beasiswa Talenta Digital (Politeknik Caltex
Riau), `kerangka_umum` = kerangka Template A berstatus `draf` dengan penanda
`[ISI ...]` untuk isi khusus bidang ke-3.

### Keputusan
- **Penomoran pasal dinamis** via `StrukturTemplateMou::pasalAktif()`: nomor
  pasal dihitung ulang berurutan dari pasal aktif (pasal opsional yang dimatikan
  tidak meninggalkan lubang nomor). Judul pasal asal tetap disimpan di field
  `nomor` untuk jejak, nomor tampil dihitung saat render.
- **Klausul opsional per-ayat**: item `ayat` boleh berupa objek
  `{ teks, opsional, aktif_bawaan }` — dipakai untuk Pasal 4 huruf c ("kegiatan
  lain yang disepakati") pada Template A dan kerangka_umum. `ayatAktif()`
  menyaringnya.
- **Template A** 15 pasal: MAKSUD DAN TUJUAN, OBJEK, LOKASI, RUANG LINGKUP,
  TUGAS DAN TANGGUNG JAWAB, PELAKSANAAN, KERAHASIAAN, TRANSFER DATA…PELINDUNGAN
  DATA PRIBADI (opsional, aktif bawaan), PEMBIAYAAN, JANGKA WAKTU, KEADAAN
  KAHAR, PENYELESAIAN PERSELISIHAN, KORESPONDENSI DAN KOMUNIKASI, PERUBAHAN
  (opsional, aktif bawaan `false`), PENUTUP. Mengingat = `{{dasar_hukum}}`
  (nasional dari config) + `{{dasar_hukum_daerah}}` (data mitra). Lampiran
  Rencana Kerja **10 kolom**.
- **Template B** 15 pasal persis urutan referensi (Maksud dan Tujuan, Definisi,
  Ruang Lingkup, Pelaksanaan Program, Jangka Waktu, Kewajiban dan Hak,
  Kerahasiaan, Transfer Data dan Pelindungan Data Pribadi, Pembiayaan, Evaluasi,
  Korespondensi, Penyelesaian Perselisihan, Keadaan Kahar, HKI, Lain-lain).
  Pasal 7 dan 8 ditandai `// VERIFIKASI` (dokumen sumber hlm. 4-7). Lampiran
  Rencana Aksi **9 kolom**, judul memuat `{{tahun_mulai}}` s.d. `{{tahun_akhir}}`.
- **Pembiayaan**: `StrukturTemplateMou::klausulPembiayaan($kode)` memilih varian
  `config('mou.pembiayaan_varian')` (APBN/APBD/Cost Sharing); kosong/tak dikenal
  → `config('mou.pembiayaan_bawaan')` (redaksi bawaan dokumen).
- **Gerbang draf**: `dapatDipakaiMitra()`/`dapatGenerate()` hanya `true` untuk
  status `final`; template `draf` ditolak dengan `pesanBelumTersedia()`
  ("Template belum tersedia."). `kerangka_umum` berstatus `draf` sehingga bidang
  `bidang_3` belum dapat di-generate mitra.
- Placeholder baru: `dasar_hukum_daerah` (lengkapi_mitra), `tahun_mulai` dan
  `tahun_akhir` (turunan).
- Komentar `// TODO: menunggu referensi bidang ke-3` disimpan di field
  `catatan` pasal (bukan `ayat`) agar tidak tampil sebagai isi dokumen.

### File
DIUBAH
- `storage/app/mou/nota_kesepakatan_pemda.json` — redaksi referensi, 15 pasal, lampiran 10 kolom (versi 2).
- `storage/app/mou/pks_beasiswa_talenta_digital.json` — redaksi referensi, 15 pasal, VERIFIKASI Pasal 7-8, lampiran 9 kolom (versi 2).
- `storage/app/mou/kerangka_umum.json` — kerangka Template A, status draf, `[ISI ...]`, catatan TODO (versi 2).
- `app/Support/Mou/StrukturTemplateMou.php` — `pasalAktif`, `judulPasalAktif`, `ayatAktif`, `dapatDipakaiMitra`, `dapatGenerate`, `pesanBelumTersedia`, `klausulPembiayaan`.
- `app/Support/Mou/PendaftaranPlaceholder.php` — `dasar_hukum_daerah`, `tahun_mulai`, `tahun_akhir`.
- `config/mou.php` — `pembiayaan_bawaan`.
DIBUAT
- `tests/Feature/Mou/IsiTemplateMouTest.php` — 16 test (daftar pasal, penomoran dinamis, gerbang draf, varian pembiayaan, VERIFIKASI, tanpa kata "AI").

### Keamanan
- [x] Tidak ada kata "AI" di isi template: test "tidak ada kata AI di isi template".
- [x] Template draf tidak dapat dipakai mitra: test "template final boleh dipakai
      mitra, template draf tidak" + "bidang bidang_3 memakai kerangka draf dan
      ditolak generate".
- [x] Placeholder tetap terdaftar (tidak yatim): test lama `FondasiTemplateMouTest`
      masih lolos.

### Pengecekan
- `php artisan test` → 294 test, 293 lolos (1715 assertion). 1 gagal
  `LandingCmsSyncTest.php:68` (**pra-eksisting**, tidak terkait perubahan ini).
- `pint --test` (file baru) → passed; `phpstan` (file baru) → 0 error.
- `npm run build` → berhasil. `npm run types:check` tidak dijalankan (tanpa
  perubahan TypeScript).

### Menunggu konfirmasi user
- Nomor peraturan `dasar_hukum_nasional` (7 butir) perlu verifikasi mentor.
- Teks `pembiayaan_varian`/`pembiayaan_bawaan` perlu verifikasi mentor.
- `bidang_3` masih placeholder (kode bidang ketiga belum ditentukan).
- `pihak_kesatu.penandatangan`/`jabatan` default masih `[ISI ...]`.
- Kolom Lampiran Template A (10) disusun gabungan (referensi Tanjungbalai hlm. 7-8
  tidak terbaca), perlu verifikasi mentor.

### Langkah berikutnya
- Fase berikut: renderer blok → HTML/dokumen, form "lengkapi data mitra", dan
  editor template berbasis blok. Menunggu perintah "Lanjut".

---

## 18. Generator MoU Mitra + Pratinjau Dokumen (SELESAI)

Alur `Buat MoU` di area mitra. Bidang & template dipilih otomatis dari pengajuan
(tidak ada pemilihan bidang dari URL), data pengajuan terisi otomatis, mitra
melengkapi placeholder, dokumen dirender sebagai pratinjau resmi, tiap bagian
bisa disunting, disimpan sebagai draf milik mitra, lalu dikirim ke admin.

### Keputusan
- **Jalur lama dipertahankan, diturunkan peran.** `/mitra/mou/draf` (satu textarea
  `TemplateMou`) tetap ada sebagai tautan sekunder ("draf teks bebas"); generator
  berbasis blok jadi jalur utama dari tombol "Buat MoU".
- **Placeholder pihak kedua ditambahkan** dan dipakai template:
  `{{pihak_kedua_penandatangan}}`, `{{pihak_kedua_jabatan}}`,
  `{{pihak_kedua_dasar_pengangkatan}}`, `{{pihak_kedua_alamat}}` — menggantikan
  `{{nama_pic}}`/`{{jabatan_pic}}`/`{{alamat_instansi}}` di blok pembukaan ketiga
  template. `nama_pic` tetap dipakai sebagai nilai turunan (diisi dari
  `pihak_kedua_penandatangan`) agar `PendaftaranPlaceholder` tetap konsisten.
- **Renderer menghasilkan teks biasa, bukan HTML.** Escaping dilakukan Blade
  (`{{ }}`) dan `PenandaIsi::sorot()`. `PenandaIsi` adalah SATU-SATUNYA jalur yang
  menghasilkan markup (`<mark class="mou-isi-kosong">`), dan ia meng-escape dulu
  seluruh teks sebelum menyorot penanda `[ISI ...]`.
- **Placeholder kosong → `[ISI nama_field]`** (disorot), dihasilkan renderer.
- **DOCX**: tombol nonaktif + tooltip + `// TODO-BACKEND: ekspor DOCX`
  (`composer.json` tidak memuat `phpoffice/phpword`; tidak menambah dependency).
- **Kepemilikan**: draf disimpan per `username` mitra di
  `storage/app/draf_mou_mitra.json`, id UUID; akses mitra lain → 404.
- **Data pengajuan tidak dapat ditimpa klien**: form hanya menerima field
  whitelist (`config('mou.form_lengkapi')`); `nama_instansi`, `alamat_instansi`,
  `pembiayaan`, `bidang` selalu diambil server dari pengajuan/profil.

### File
DIBUAT
- `app/Support/Mou/Penomoran.php` — `terbilang`, `hari`, `bulan`, `tanggalResmi`, `tanggalRingkas`, `prefiksWilayah`, `namaWilayah`, `parse`.
- `app/Support/Mou/RendererDokumen.php` — `data`, `turunan`, `render`, `gantiPlaceholder` (blok → dokumen terstruktur).
- `app/Support/Mou/DrafMouMitra.php` — `buat`/`perbarui`/`milik`/`untukPemilik`/`hapus`/`bersihkanBagian`, riwayat versi.
- `app/Support/Mou/PenandaIsi.php` — `sorot()` (escape dulu, lalu bungkus `[ISI ...]`).
- `app/Http/Requests/LengkapiDataMouRequest.php` — validasi form lengkapi (rules dibangun dari config), `nilaiBersih()`.
- `app/Http/Requests/SimpanDrafMouMitraRequest.php` — validasi simpan/kirim edit per bagian, `bagianBersih()`.
- `resources/views/user/mou-buat.blade.php` — form lengkapi data + pratinjau.
- `resources/views/user/mou-buat-lihat.blade.php` — sunting per bagian + riwayat versi.
- `resources/views/user/mou-cetak.blade.php` — halaman cetak mandiri.
- `resources/views/user/partials/mou-dokumen.blade.php` — partial dokumen resmi.
- `public/assets/user/css/mou-dokumen.css` — gaya dokumen + `@media print`.
- `public/assets/user/js/mou-generator.js` — toggle pratinjau (tanpa `innerHTML`).
- `tests/Feature/Mou/GeneratorMouMitraTest.php` — 25 test.
DIUBAH
- `app/Http/Controllers/MitraController.php` — `buatMou`, `simpanMouBaru`, `lihatMou`, `simpanMou`, `pratinjauMouMitra` + helper (`templateMitra`, `dataAwalForm`, `gabungNilai`, `daftarDasarHukumDaerah`, `terapkanBagian`).
- `routes/web.php` — `mitra.mou.buat`, `.buat.simpan`, `.buat.lihat`, `.buat.update`, `.buat.cetak` (throttle `mou-generate`).
- `app/Providers/AppServiceProvider.php` — limiter `mou-generate` (10/menit per akun+IP).
- `app/Support/Mou/StrukturTemplateMou.php` — `pasalAktif` menyertakan `catatan`.
- `app/Support/Mou/PendaftaranPlaceholder.php` — placeholder pihak kedua + `nomor_surat`.
- `config/mou.php` — `form_lengkapi` (satu sumber view + validasi).
- `resources/views/layouts/user.blade.php` — tambah `@stack('styles')`.
- `resources/views/user/mou.blade.php` — tombol "Buat MoU" (jalur utama) + tautan sekunder draf lama.
- `storage/app/mou/{nota_kesepakatan_pemda,pks_beasiswa_talenta_digital,kerangka_umum}.json` — placeholder pihak kedua + alamat/dasar pengangkatan di pembukaan.

### Keamanan
- [x] Tiap bidang → template benar: test "setiap bidang menghasilkan template yang benar"
  (GTA→nota_kesepakatan_pemda, VSGA→pks_beasiswa_talenta_digital, tak dikenal→kerangka_umum).
- [x] Placeholder terisi: test "renderer mengisi placeholder dari data".
- [x] `[ISI]` untuk data kosong: test "placeholder tanpa nilai tampil sebagai [ISI nama_field]".
- [x] Payload `<script>` tidak tereksekusi: test "payload script pada isian tidak tereksekusi
  (ter-escape)" + "penanda [ISI] disorot tanpa membocorkan tag pengguna".
- [x] Mitra lain ditolak (404): test "mitra lain ditolak 404 saat membuka draf bukan miliknya"
  (termasuk halaman cetak).
- [x] Terbilang benar: test "terbilang bilangan benar untuk contoh tanggal/tahun" (5, 21, 2026,
  11, 100, 1500) + "tanggal resmi dan prefiks wilayah benar".
- [x] Validasi panjang: test "input form panjang berlebih ditolak validasi".
- [x] Data pengajuan tidak dapat ditimpa klien: test "data pengajuan tidak dapat ditimpa lewat
  input klien".
- [x] Suntingan mitra tanpa HTML: test "suntingan mitra tidak menyimpan tag HTML".
- [x] Riwayat versi: test "riwayat versi bertambah pada setiap simpanan".
- [x] Tanpa `innerHTML` dari input pengguna: `PenandaIsi` escape dulu;
  `mou-generator.js` hanya menyembunyikan/menampilkan elemen.
- [x] Rate limit generate/simpan: limiter `mou-generate` di `AppServiceProvider` + middleware
  route.

### Pengecekan
- `php artisan test` → 319 test, 318 lolos (1889 assertion). 1 gagal
  `LandingCmsSyncTest.php:68` (**pra-eksisting**, terbukti tidak terkait perubahan ini).
- `pint --test` → passed. `phpstan analyse app --memory-limit=1G` → 0 error.
- `npm run build` → berhasil.
- `npm run types:check` tidak dijalankan (tanpa perubahan TypeScript).

### Catatan untuk backend
- Pindahkan template dari `storage/app/mou/*.json` ke tabel `mou_template`.
- Pindahkan draf mitra dari `storage/app/draf_mou_mitra.json` ke tabel `draf_mou`
  (+ `draf_mou_versi`), UUID route key, kepemilikan lewat Policy (saat ini cek
  manual di `DrafMouMitra::milik`).
- **Ekspor DOCX** belum dibangun (`// TODO-BACKEND: ekspor DOCX`) — perlu library
  DOCX yang disetujui.
- Identitas mitra masih dari session `mitra_data` (mode demo), bukan guard auth.

### Menunggu konfirmasi user
- Placeholder pihak kedua baru (`pihak_kedua_dasar_pengangkatan`) mengubah redaksi
  pembukaan ketiga template; perlu verifikasi mentor apakah sesuai format resmi.
- Redaksi `{{pihak_kedua_alamat}}` vs `{{alamat_instansi}}` sudah disatukan ke
  placeholder baru.
- `pihak_kesatu.penandatangan`/`jabatan` default masih `[ISI ...]`.
- Kolom Lampiran Template A (10) & `bidang_3` masih menunggu (dari §17).

### Langkah berikutnya
- Editor template berbasis blok untuk Superadmin. Menunggu perintah "Lanjut".

---

## 19. Editor Template MoU Superadmin Sesuai Referensi (SELESAI)

Halaman `/{SUPERADMIN_PATH}/template-mou` dialihkan dari editor teks datar
(`TemplateMou`) ke **editor berbasis blok** yang menyunting
`storage/app/mou/*.json` — struktur yang sama dipakai generator mitra.

### Keputusan
- **Halaman ini menyunting template blok.** `TemplateMou` (teks datar) tetap ada
  dan tetap dipakai jalur draf mitra lama `/mitra/mou/draf`; tidak dihapus.
  `SimpanTemplateMouRequest` (tidak lagi terpakai) dihapus.
- **Operasi tulis ditambahkan ke `StrukturTemplateMou`** (layer yang sama dengan
  pembaca): `simpan`, `kembalikan`, `hapus`, `bawaan`, `pemetaan`, `simpanPemetaan`,
  `pengaturan`, `simpanPengaturan`. Berkas JSON tetap sumbernya; `config/mou.php`
  menjadi nilai awal.
- **"Kembalikan ke bawaan"** memakai aset `resources/mou-bawaan/{kode}.json`
  (snapshot isi template, di-commit) — bukan DB/seed kode.
- **Pemetaan bidang→template** disimpan sebagai overlay
  `storage/app/mou/_pemetaan.json` (menimpa `config('mou.pemetaan')`). Dropdown di
  halaman ini menyimpan pemetaan; kode template tak dikenal ditolak.
- **Pengaturan Pihak Kesatu + dasar hukum nasional** disimpan sebagai overlay
  `storage/app/mou/_pengaturan.json` (menimpa `config('mou.pihak_kesatu')` dan
  `config('mou.dasar_hukum_nasional')`). `RendererDokumen::data()` membaca
  `StrukturTemplateMou::pengaturan()`, dan kini mengisi `{{dasar_hukum}}`
  (sebelumnya tidak pernah terisi → selalu `[ISI dasar_hukum]`).
- **Editor mengirim struktur blok sebagai satu medan JSON (`blok`)**; server
  memvalidasi ULANG tiap blok (tipe, bentuk pasal/ayat, jumlah, panjang, tag HTML,
  placeholder asing). Client tidak dipercaya untuk urutan/tipe/isi.
- **Pratinjau memakai `RendererDokumen::render()`** + data contoh — renderer yang
  sama dengan generator mitra, tidak ada jalur `innerHTML`.

### File
DIBUAT
- `resources/mou-bawaan/{nota_kesepakatan_pemda,pks_beasiswa_talenta_digital,kerangka_umum}.json` — isi bawaan ("kembalikan ke bawaan").
- `app/Http/Requests/SimpanTemplateBlokRequest.php` — validasi struktur blok + placeholder + HTML + batas.
- `app/Http/Requests/SimpanPengaturanMouRequest.php` — validasi Pihak Kesatu + dasar hukum nasional.
- `app/Http/Requests/SimpanPemetaanMouRequest.php` — validasi pemetaan bidang→template.
DIUBAH
- `app/Support/Mou/StrukturTemplateMou.php` — operasi tulis, overlay pemetaan/pengaturan, `bawaan`.
- `app/Support/Mou/RendererDokumen.php` — baca `pengaturan()`; isi `{{dasar_hukum}}`.
- `app/Http/Controllers/AdminSuperadminController.php` — `templateMou` (blok), `simpanTemplateMou`, `resetTemplateMou`, `simpanPengaturanMou`, `simpanPemetaanMou`.
- `routes/superadmin.php` — `whereIn` kode template, rute `template_mou.pengaturan`/`template_mou.pemetaan`, throttle.
- `app/Providers/AppServiceProvider.php` — limiter `admin-template-mou` (20/menit per akun+IP).
- `config/admin_akses.php` — label aksi `template_mou.pengaturan`/`template_mou.pemetaan`.
- `resources/views/admin/template_mou.blade.php` — ditulis ulang menjadi editor blok + pratinjau + pengaturan + pemetaan.
- `tests/Feature/TemplateMouAdminTest.php` — 21 test (editor blok, pasal opsional, validasi, otorisasi, finalisasi bidang 3, draf lama).
DIHAPUS
- `app/Http/Requests/SimpanTemplateMouRequest.php` — tidak terpakai setelah halaman dialihkan ke editor blok.

### Keamanan
- [x] Role lain ditolak (404) untuk GET + PUT + POST: test "role lain ditolak untuk GET dan seluruh aksi tulis" (superadmin-only via middleware `admin.only:superadmin`).
- [x] Placeholder tak dikenal ditolak: test "placeholder tidak dikenal ditolak dan template tidak berubah".
- [x] HTML berbahaya ditolak: test "tag HTML berbahaya ditolak".
- [x] Struktur blok/JSON tak sah ditolak: test "struktur blok tidak sah ditolak", "blok bukan JSON sah ditolak".
- [x] Status di luar final/draf ditolak: test "status di luar final/draf ditolak".
- [x] Panjang & jumlah dibatasi: `SimpanTemplateBlokRequest` (MAKS_PASAL/MAKS_AYAT/MAKS_TEKS).
- [x] CSRF: semua form `@csrf` + `@method`.
- [x] Rate limit: limiter `admin-template-mou` pada seluruh rute tulis.
- [x] Hanya final dapat dipakai mitra: test "finalisasi bidang 3 ... menjadi dapat dipakai mitra", "template draf tidak dapat dipakai mitra".
- [x] Perubahan tidak mengubah draf lama: test "perubahan template berlaku untuk draf mitra berikutnya, draf lama tidak berubah".
- [x] Versi + log tercatat: test "superadmin menyimpan blok template dan versi dicatat di log", "kembalikan ke bawaan memulihkan isi dan mencatat log", "pengaturan ... tersimpan", "pemetaan ... tersimpan".

### Pengecekan
- `php artisan test` → 325 test, 324 lolos (1917 assertion). 1 gagal
  `LandingCmsSyncTest.php:68` (**pra-eksisting**).
- `pint --test` → passed. `phpstan analyse app --memory-limit=1G` → 0 error.
- `npm run build` → berhasil.

### Catatan untuk backend
- Pindahkan template, pemetaan, dan pengaturan ke tabel (`mou_template`,
  `mou_pemetaan`, `mou_pengaturan`) dengan riwayat versi + kolom `diperbarui_oleh`.
- `resources/mou-bawaan/*.json` menjadi basis data seed bawaan saat migrasi.

### Menunggu konfirmasi user
- Isi bawaan (`resources/mou-bawaan/*.json`) adalah salinan isi saat ini; perlu
  ditinjau mentor bila redaksi template berubah lagi.
- `pihak_kesatu.penandatangan`/`jabatan` masih `[ISI ...]` sampai diisi lewat
  halaman Pengaturan.
- Kolom Lampiran Template A (10) & `bidang_3` masih menunggu (dari §17).

### Langkah berikutnya
- Menunggu perintah pengguna.

---

## Tambahan — Peran Admin Konten (`admin_konten`)

Status: selesai (menunggu konfirmasi user)

### Ringkasan
- Peran internal baru satu tingkat di bawah `admin_kerjasama`. Prefix dari
  `ADMIN_KONTEN_PATH` (default `admin-konten`), halaman login sendiri
  `/{ADMIN_KONTEN_PATH}/login`, kredensial `.env` berupa hash bcrypt
  (`ADMIN_KONTEN_USERNAME`, `ADMIN_KONTEN_PASSWORD_HASH`) yang wajib berbeda
  dari peran lain. Tanpa `.env`, akun demo hanya aktif di luar production.
- Kemampuan: `dashboard_konten` (daftar mitra disetujui, baca-saja),
  `tentang`, `artikel`, `galeri`. Terlarang dan tidak didaftarkan di prefix
  ini: inbox, draf proposal, masa aktif (aksi), template MoU, aktivitas,
  kelola pengguna.
- Konten portal memakai `AdminSuperadminController` yang sama dengan
  Superadmin; closure duplikat di `routes/superadmin.php` dihapus sehingga
  hanya ada satu implementasi.
- Sesi yang sudah login sebagai peran lain tidak bisa membuka halaman login
  maupun login ulang di prefix peran lain (404, pola penyamaran panel).
- Konten portal benar-benar terhubung ke landing page: artikel, galeri, dan
  visi/profil yang disimpan admin konten ditulis ke `storage/app/landing_cms.json`
  oleh `LandingCms`, lalu dibaca halaman `/` tanpa sesi admin. Diuji dengan
  pengunjung anonim (`flushSession()` lalu `GET /`).

### File
DIBUAT
- `routes/admin_konten.php` — dashboard read-only + rute konten portal
  (tentang/artikel/galeri) di prefix admin konten.
- `resources/views/admin/dashboard_konten.blade.php` — tabel mitra diterima
  (instansi, bidang, status `Disetujui`, masa aktif + kondisi), tanpa aksi dan
  tanpa kontak PIC.
- `tests/Feature/Auth/AdminKontenTest.php` — matriks peran × route × method,
  dashboard hanya mitra disetujui, HTML tanpa tautan terlarang, login demo &
  hash `.env`, throttle, regenerasi sesi, logout, CRUD artikel, dan koneksi
  konten ke landing page.

DIUBAH
- `config/admin_akses.php` — peran `admin_konten`, halaman
  `dashboard_konten` (nama route `dashboard_konten` agar tidak bertabrakan
  dengan `dashboard` milik Superadmin), komentar matriks peran.
- `bootstrap/app.php` — `routes/admin_konten.php` hanya dimuat untuk peran
  ini; `routes/admin_kerja.php` hanya untuk peran yang punya inbox/draf/masa
  aktif, sehingga rute terlarang tidak pernah terdaftar untuk admin konten.
- `app/Http/Controllers/AdminSuperadminController.php` — `dashboardKonten()`
  dan method CMS bersama (tentang/artikel/galeri) untuk dua prefix.
- `routes/superadmin.php` — CMS memakai controller, bukan closure salinan.
- `app/Http/Controllers/AdminLoginController.php` — tolak sesi peran lain di
  halaman login (404) agar sesi tidak berpindah peran tanpa logout.
- `public/assets/admin/css/panel.css` — kelas `.cell-sub` untuk keterangan
  kecil di bawah nilai sel.
- `.env.example` — `ADMIN_KONTEN_*` + komentar kredensial demo yang akurat.
- `phpunit.xml` — kredensial uji `ADMIN_KONTEN_*` (hash bcrypt, bukan
  plaintext di config).
- `tests/Feature/Auth/AdminLoginTest.php` — admin konten pada dataset login,
  logout, dan uji silang peran.
- `tests/Feature/Auth/MatriksAksesTest.php` — kontrak kemampuan admin konten.
- `tests/Feature/LandingCmsSyncTest.php` — test "sesi admin tidak lagi dipakai
  untuk konten landing" memakai storage sementara agar tidak gagal hanya
  karena berkas CMS di mesin pengembang sudah disunting.

### Keamanan
- [x] Login terpisah + throttle `admin-login` (5/menit per IP + username).
- [x] Sesi diregenerasi saat login; logout menginvalidasi sesi + token.
- [x] Login silang peran ditolak: kredensial peran lain gagal di pintu ini,
      dan sesi peran lain mendapat 404 (tidak bisa berpindah peran).
- [x] Otorisasi server untuk semua method lewat `admin.only:<kemampuan>`;
      halaman terlarang tidak terdaftar di prefix admin konten.
- [x] Dashboard memfilter status `Disetujui` saja; nama/email/telepon PIC
      tidak pernah dikirim ke view (diuji di HTML).
- [x] Menu dirender dari kemampuan peran; item terlarang tidak muncul.
- [x] Test tidak lagi menyentuh berkas demo pengembang: penyimpanan diarahkan
      ke direktori sementara per test (`useStoragePath`).
- [ ] TODO-BACKEND: peran masih dibaca dari config + session, belum kolom
      role di tabel `users` dan Policy.

### Pengecekan
- `php artisan test` → **305 test, 305 lolos** (1148 assertion).
- `pint --test` untuk semua berkas yang diubah → passed. Repo-wide masih
  gagal pada `tests/Feature/TemplateMouAdminTest.php` (`single_quote`) yang
  tidak disentuh.
- `phpstan analyse` → 0 error. `npm run build` → berhasil.

### Catatan untuk backend
- `dashboard_konten` masih membaca `PengajuanMasuk` dan `MasaAktifMitra`
  (JSON demo). Saat tabel DB tersedia: scope kueri ke status disetujui,
  batasi kolom yang dipilih (jangan pernah mengambil PIC untuk peran ini),
  dan ganti pengecekan peran config/session dengan kolom role + Policy.
- Aksi tulis konten portal belum dicatat ke `LogAktivitas`; saat audit trail
  SQL dibangun, catat perubahan tentang/artikel/galeri beserta perannya.

---

## Bidang kerja sama sesuai referensi MoU (24 November 2026)

Branch: `feat/template-mou-referensi`. Commit: `feat(form): pilihan bidang sesuai referensi mou`.

### Perubahan
- `config/kerjasama.php` 'bidang' kini menyimpan item lengkap: `kode`, `label`,
  `deskripsi_singkat`, `jenis_dokumen`, `template`, `aktif`. GTA & VSGA aktif;
  slot ke-3 (`bidang_3`) nonaktif dengan komentar TODO.
- `app/Support/BidangKerjasama.php`: accessor baru `aktif()`, `item()`,
  `jenisDokumen()`, `template()`, `pemetaanTemplate()`. `daftar()` mengembalikan
  item ternormalisasi (bukan lagi kode => label). `kode()` hanya bidang aktif.
- `config/mou.php` 'pemetaan' **diturunkan** dari `config/kerjasama.php`
  (satu sumber; tidak mungkin bertentangan).
- Form pengajuan: kartu radio tunggal menampilkan label, deskripsi, badge jenis
  dokumen; `aktif=false` tidak dirender. CSS `.pjn-bidang-*` ditambahkan.
- Dashboard mitra menampilkan label + jenis dokumen. Generator/editor/draf
  membaca sumber yang sama.
- Test: `tests/Feature/BidangKerjasamaKonsistenTest.php` (baru) + penyesuaian
  `BidangKerjasamaTest` / `PengajuanFormTest`.

### Menunggu konfirmasi
- Bidang ke-3 (masih `aktif=false`) dan pemetaan VSGA/GTA ke template.
- Apakah `PROPOSAL`/`KERANGKA` perlu jadi jenis dokumen tambahan.

### Catatan untuk backend
- Saat bidang pindah ke tabel DB: buat tabel `bidang` (kolom kode, label,
  deskripsi, jenis_dokumen, template, aktif) dan relasi ke pengajuan; validasi
  server cukup cek `aktif=true` + kode ada.
- Seed bidang dari `config/kerjasama.php`; jangan hardcode label di view.

---

## Tiga Pilihan Bidang Kerja Sama (24 November 2026)

Branch: `feat/template-mou-referensi`. Commit: `feat(form): tiga pilihan bidang kerja sama`.

### Masalah
Form baru menampilkan 2 pilihan (GTA, VSGA) padahal ada 3 template referensi;
dua referensi Pemda (Jambi + Tanjungbalai) tergabung jadi satu pilihan
`nota_kesepakatan_pemda`, dan slot bidang ke-3 dinonaktifkan.

### Perubahan
Tiga pilihan kini terhubung **1-ke-1** ke tiga template berbeda:

| Kode | Kategori | Dokumen | Template |
|---|---|---|---|
| `GTA` | Pemda Standar (ref 1: Kota Jambi) | Nota Kesepakatan | `nota_kesepakatan_pemda` (14 pasal) |
| `PEMDA_PDP` | Pemda Format UU PDP (ref 2: Tanjungbalai) | Nota Kesepakatan | `nota_kesepakatan_pemda_pdp` (15 pasal, pasal PDP tetap) |
| `VSGA` | Perguruan Tinggi PKS DTS (ref 3: Politeknik Caltex Riau) | Perjanjian Kerja Sama | `pks_beasiswa_talenta_digital` (15 pasal) |

- `nota_kesepakatan_pemda.json`: pasal "TRANSFER DATA…PELINDUNGAN DATA PRIBADI"
  `aktif_bawaan` → `false` (14 pasal, tanpa PDP); pasal "PERUBAHAN" → `true`
  agar jumlah pasal sesuai referensi (14/15). Nama ditambah "(Standar)".
- **Baru** `nota_kesepakatan_pemda_pdp.json` (storage + resources/mou-bawaan):
  salinan Pemda dengan pasal PDP aktif (15 pasal). Nama "(Format UU PDP)".
- `config/kerjasama.php`: slot `bidang_3` dihapus, tambah `PEMDA_PDP`; tiap item
  dapat field baru `sasaran_mitra` (target mitra + format dokumen); label GTA
  diberi penanda "Pemerintah Daerah", VSGA "Perguruan Tinggi".
- `BidangKerjasama::daftar()` menormalisasi `sasaran_mitra`.
- Form: kartu pilihan kini juga menampilkan baris "Sasaran: …" (CSS
  `.pjn-bidang-target`); hint, indentation, ikon, dan tanda hubung dirapikan.
- `config/mou.php` `pemetaan` otomatis ikut (satu sumber) — sudah memetakan
  `PEMDA_PDP` → `nota_kesepakatan_pemda_pdp`.

### Keamanan / otorisasi
- Tidak ada perubahan pada validasi server: `StorePengajuanRequest` tetap
  `Rule::in(BidangKerjasama::kode())`, jadi hanya 3 kode aktif yang diterima;
  kode lain (mis. `bidang_3` lama, `KODE_ASING`) ditolak 422.
- Data pengajuan lama `GTA`/`VSGA` tetap dikenali (kode tidak diubah).

### Pengecekan
- `php artisan test` → **386 test, 386 lolos, 2360 assertion** (sebelumnya 375).
- `pint --test` → passed; `phpstan` (memory 1G) → 0 error.
- Verifikasi HTTP: `/pengajuan` → 200, 3 radio `bidang` (GTA, PEMDA_PDP, VSGA),
  tiap kartu menampilkan nama, badge jenis dokumen, deskripsi, dan sasaran mitra.
- Verifikasi pemetaan: GTA→nota_kesepakatan_pemda (14), PEMDA_PDP→…_pdp (15),
  VSGA→pks_beasiswa_talenta_digital (15).

### Menunggu konfirmasi
- Label final tiap kategori (GTA/VSGA tetap dipakai sebagai kode; sebutan
  "Pemda Standar / Pemda UU PDP / Perguruan Tinggi DTS" perlu persetujuan mentor).
- Apakah `kerangka_umum` (draf) tetap diperlukan sebagai slot bidang ke-4 kelak.

### Catatan untuk backend
- Tiga kode bidang → tiga `template` kini berbeda; saat pindah ke DB, tambah
  kolom `sasaran_mitra` di tabel `bidang`.
- `PEMDA_PDP` memakai template terpisah (bukan toggle runtime); bila nanti ada
  lebih banyak varian, pertimbangkan kolom `pasal_nonaktif` per bidang.
