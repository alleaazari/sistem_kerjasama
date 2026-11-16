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

## 10. Perpanjangan Masa Aktif: Keputusan Admin (selesai)

Permintaan: admin memperpanjang langsung dari dashboard, menerima/menolak permohonan
perpanjangan mitra, dan keputusan tampil sebagai notifikasi di halaman Status mitra.

DIBUAT:
- `app/Support/PermohonanPerpanjangan.php` — penyimpanan JSON `storage/app/permohonan_perpanjangan.json`
  (pola sama dengan `storage_pengajuan.json`). Status `Menunggu`/`Disetujui`/`Ditolak`.
  Periode baru menyambung dari tanggal berakhir lama; bila sudah lewat, mulai hari ini
  (keputusan user).
- `resources/views/admin/dashboard.blade.php` — antrean permohonan, modal setujui (durasi
  1/2/3 tahun) dan modal tolak (alasan wajib, 10–1000 karakter), tabel keputusan terakhir.
- `tests/Feature/PerpanjanganTest.php` — 8 test / 25 assertion.

DIUBAH:
- `routes/admin.php` — `admin.dashboard`, `admin.dashboard.setujui`, `admin.dashboard.tolak`.
  Tanggal berakhir diambil dari data tersimpan, bukan input klien; keputusan yang sudah
  ditetapkan tidak dapat ditimpa.
- `app/Http/Controllers/MitraController.php` — `ajukanPerpanjangan` menulis ke store
  (bukan session) agar terbaca admin; tolak pengajuan ganda selama masih menunggu.
- `resources/views/layouts/admin.blade.php` — menu Dashboard + badge jumlah menunggu;
  tampilkan `session('error')`.
- `resources/views/user/status.blade.php` — banner menunggu/disetujui/ditolak (alasan tampil),
  tombol "Menunggu Keputusan Admin", ajukan ulang setelah ditolak.
- `routes/web.php` — hapus `mitra_perpanjangan` dari `session()->forget()` logout (tidak lagi dipakai).
- `public/assets/admin/css/panel.css` — `.row select`/`input[type=date]` ikut gaya field.

Pengecekan:
- `php artisan test`: 61 lolos, 250 assertion.
- `pint` berkas baru/berubah (di luar `routes/admin.php`): lolos.
- `pint --test routes/admin.php`: GAGAL, tetapi sudah gagal juga di HEAD (kode lama:
  `use ($defaultPengajuan)` tak terpakai, spasi operator). Tidak diperbaiki di luar cakupan.
- PHPStan: belum dijalankan ulang untuk perubahan ini.

Catatan untuk backend:
- Penyimpanan masih JSON (mode demo). Tabel `permohonan_perpanjangan` + Policy per-mitra
  dibutuhkan saat Fase 2. `selesai_lama` saat ini disalin dari session mitra.
- Belum ada notifikasi email/WA untuk keputusan perpanjangan (hanya tampil di halaman Status).

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

## 11. Pisah Halaman: Draf Proposal AI & Masa Aktif Mitra (selesai)

Permintaan: pisah satu halaman admin menjadi dua (draf proposal dan masa aktif),
masing-masing route, controller method, view, dan item menu sendiri. Hanya
`admin`/`admin_kerjasama`/`superadmin`; route lama dialihkan; pencarian + pagination;
UUID di URL.

DIBUAT:
- `app/Support/DrafMitra.php` — penyimpanan JSON `storage/app/draf_mitra.json`, kolom
  `uuid`, `instansi`, `judul_ks`, `tanggal_berakhir`, `buatan_pada`. Seed dari data dummy lama.
- `app/Support/MasaAktifMitra.php` — `storage/app/masa_aktif_mitra.json`, kolom `uuid`,
  `instansi`, `tanggal_mulai`, `tanggal_berakhir`, `alasan`. Status dihitung via
  `StatusKerjasama::hitung()`, bukan string beku.
- `app/Http/Controllers/AdminMitraController.php` — `draf`, `unduhDraf`, `masaAktif`,
  `aturMasaAktif`.
- `resources/views/admin/draf_proposal.blade.php` — kolom Instansi, Judul, Tanggal
  Pembuatan, Aksi (Unduh Draf). Handler fetch untuk GET `.../{uuid}/unduh` dengan
  loading + pesan 403/404 + nama berkas dari `Content-Disposition`; tombol dirender
  `disabled` + tooltip "Menunggu backend" lewat `DrafMitra::berkasSiap()`.
- `resources/views/admin/masa_aktif.blade.php` — kolom Instansi, Jangka Waktu, Status,
  Aksi (Atur Masa Aktif). Tanpa judul kemitraan, tanpa unduh, tanpa tanggal pembuatan.
- `tests/Feature/AdminDrafMasaAktifTest.php` — 26 test / 68 assertion.

DIUBAH:
- `routes/admin.php` — `admin.draf_proposal`, `admin.draf_proposal.unduh` (UUID),
  `admin.masa_aktif`, `admin.masa_aktif.atur` (PUT); middleware
  `admin.only:admin,admin_kerjasama,superadmin`; `admin.mitra_draf.lama` redirect ke
  `/draf-proposal`.
- `resources/views/layouts/admin.blade.php` — satu menu jadi dua: "Draf Proposal AI"
  dan "Masa Aktif Mitra".

DIHAPUS:
- `resources/views/admin/mitra_draf.blade.php` — digantikan dua view di atas.

Keamanan:
- [x] Otorisasi per peran di middleware (`admin.only:...`) + test role developer 404 dan
      tanpa sesi admin redirect ke login.
- [x] UUID di URL, divalidasi aturan `uuid` (`whereUuid` + validasi form).
- [x] `tanggal_berakhir >= tanggal_mulai`; `alasan` maks 255; tanggal diambil dari input
      terverifikasi, `instansi` tidak diterima dari klien.
- [x] XSS: toast memakai `textContent`, bukan `innerHTML`.

Pengecekan:
- `php artisan test`: 87 lolos, 318 assertion.
- `pint --test` berkas baru: lolos. `routes/admin.php`: GAGAL, juga gagal di HEAD
  (kode lama), tidak diperbaiki di luar cakupan.
- `phpstan` berkas baru: lolos. Repo: 27 error LAMA di berkas lain.
- `npm run build`: GAGAL, juga gagal di HEAD — `routes/settings.php` tidak terdaftar di
  `bootstrap/app.php` sehingga Wayfinder tidak membuat `@/routes/security` dan
  `@/actions/.../SecurityController`. Bukan akibat perubahan ini.
- `npm run types:check`: 30 error LAMA (berkas Inertia/settings); tidak ada dari berkas baru.

Menunggu konfirmasi / TODO-BACKEND:
- Pemetaan peran `superadmin`/`admin_kerjasama` ke tabel `users` (akun `admin` dianggap
  pengelola kerja sama untuk sementara).
- Ganti JSON ke model + tabel DB (`draf_proposal`, `masa_aktif_mitra`).
- Berkas draf belum disimpan: ubah `DrafMitra::BERKAS_SIAP` ke `true` saat backend siap.
- Konflik: alur perpanjangan lewat Dashboard (`PermohonanPerpanjangan`) vs "Atur Masa Aktif"
  di halaman ini — dua kanal yang menyentuh data berbeda. Perlu keputusan mana yang resmi.
- `routes/settings.php` belum terdaftar -> build gagal (temuan lama, perlu keputusan).

---

## 12. Pemisahan Peran `admin_kelola` (selesai)

Permintaan: peran `admin_kelola` hanya mengelola Profil & Visi Misi, Kelola Artikel,
Kelola Galeri, dan Lihat Portal Umum. Tidak boleh melihat apa pun soal kerja sama.
`admin_kerjasama` dan `superadmin` akses penuh. Akun demo lama (role `admin`) **tidak
dikurangi** aksesnya. Kredensial `admin_kelola` dibaca dari `.env`, bukan plaintext di kode.

DIBUAT:
- `config/admin_peran.php` — SATU peta izin terpusat: `kemampuan` (konten, kerja_sama,
  uji_email), `peran` (peran → daftar kemampuan), `label`, `beranda` (halaman pertama
  setelah login). Sumber tunggal untuk route, menu, dan test.
- `app/Support/AdminPeran.php` — pembaca peta: `semua()`, `kemampuan()`, `boleh()`,
  `peranUntuk()` (menghasilkan argumen `admin.only:...`), `label()`, `beranda()`.
- `resources/views/admin/dashboard.blade.php` — sebelumnya untracked, sekarang terpantau;
  punya dua bentuk: ringkas konten (`$ringkas = true`) dan dashboard kerja sama.
- `tests/Feature/AdminKelolaRoleTest.php` — 54 test / 107 assertion (matriks peran × route,
  method mutasi, menu, dashboard, login `.env`).

DIUBAH:
- `config/admin_demo.php` — akun ketiga `admin_kelola` dari `ADMIN_KELOLA_USERNAME`/
  `ADMIN_KELOLA_EMAIL`/`ADMIN_KELOLA_PASSWORD_HASH`. Akun tidak aktif bila email atau
  hash kosong.
- `routes/admin.php` — tiap grup memakai `AdminPeran::peranUntuk('<kemampuan>')`, bukan
  daftar peran tulis tangan:
  - `kerja_sama` → dashboard penuh, inbox, approve/reject, draf-proposal, masa-aktif;
  - `konten` → `/admin-kelola` (dashboard ringkas), tentang, artikel, galeri;
  - `uji_email` → `/test-email` (developer, hanya lokal).
  Login memakai `Hash::check` untuk akun ber-`password_hash`; akun demo lama tetap
  `hash_equals`. Redirect setelah login memakai `AdminPeran::beranda($role)`.
- `resources/views/layouts/admin.blade.php` — menu, nama, dan label peran dirender dari
  peta izin + sesi. Untuk `admin_kelola`: tidak ada Inbox, Draf Proposal AI, Masa Aktif,
  maupun badge/counter kerja sama. Label grup berubah jadi "Menu Pengelola Konten".
- `.env.example` — placeholder `ADMIN_KELOLA_USERNAME`, `ADMIN_KELOLA_EMAIL`,
  `ADMIN_KELOLA_PASSWORD_HASH` (kosong).

Matriks akhir (peran × menu):

| Menu | admin_kelola | admin_kerjasama | superadmin | admin (demo lama) | developer |
|---|---|---|---|---|---|
| Admin Kelola Komdigi (`/admin-kelola`) | ✅ | ✅ | ✅ | ✅ | ❌ 404 |
| Profil & Visi Misi | ✅ | ✅ | ✅ | ✅ | ❌ 404 |
| Kelola Artikel | ✅ | ✅ | ✅ | ✅ | ❌ 404 |
| Kelola Galeri | ✅ | ✅ | ✅ | ✅ | ❌ 404 |
| Lihat Portal Umum | ✅ | ✅ | ✅ | ✅ | ✅ |
| Dashboard penuh | ❌ 404 | ✅ | ✅ | ✅ | ❌ 404 |
| Inbox Pengajuan + approve/reject | ❌ 404 | ✅ | ✅ | ✅ | ❌ 404 |
| Draf Proposal AI + unduh | ❌ 404 | ✅ | ✅ | ✅ | ❌ 404 |
| Masa Aktif Mitra + atur | ❌ 404 | ✅ | ✅ | ✅ | ❌ 404 |
| Uji email (`/test-email`) | ❌ 404 | ❌ 404 | ❌ 404 | ❌ 404 | ✅ (lokal) |

Keputusan:
- `admin_kelola` **diberi** Dashboard ringkas di URL sendiri `/admin-kelola`
  (route `admin.dashboard.kelola`, judul halaman "Admin Kelola Komdigi"). Angka yang
  dikirim hanya jumlah artikel, galeri, pejabat, dan status visi. Route dashboard kerja
  sama menolak `admin_kelola` 404, jadi data perpanjangan/pengajuan **tidak di-query
  dan tidak dikirim** ke view-nya.
- Akun env tanpa hash/email = akun mati; password kosong tidak bisa lolos hanya karena
  hash kosong.
- Dashboard kerja sama dan dashboard ringkas berada di route terpisah, bukan satu route
  dengan percabangan, agar guard-nya murni middleware dan test mudah dibaca.

Keamanan:
- [x] Otorisasi di sisi server lewat middleware `AdminOnly` (logika tidak diubah), bukan
      hanya menyembunyikan menu. Test 404 untuk GET dan POST/PUT: `dashboard.setujui`,
      `dashboard.tolak`, `inbox.approve`, `inbox.reject`, `draf_proposal.unduh`,
      `masa_aktif.atur`, `mitra_draf.lama`.
- [x] Tidak ada endpoint kerja sama di luar `routes/admin.php` (sudah diperiksa: route
      mitra di `routes/web.php` hanya untuk sesi mitra), jadi tidak ada JSON/unduh yang
      bocor.
- [x] Password `admin_kelola` hanya hash bcrypt di `.env`; tidak ada password plaintext
      baru di kode/config.
- [x] Akses lama (`admin`, `admin_kerjasama`, `superadmin`, `developer`) tidak berkurang.
- [x] Tamu (tanpa sesi) diarahkan ke halaman login untuk semua route admin.

Pengecekan:
- `php artisan test`: 145 lolos, 440 assertion.
- `pint --test` (semua berkas yang diubah/dibuat): lolos.
- `phpstan` berkas baru: lolos. Repo: 24 error LAMA di berkas lain, tidak ada dari
  perubahan ini.
- `npm run build`: lolos (9.5 s).
- `npm run types:check`: 18 error LAMA di `resources/js/routes/**` hasil generate
  Wayfinder (`Type '"query"' ...`), bukan dari perubahan ini.
- `npm run lint`: **tidak ada** script ini; yang tersedia `check` / `check:fix`.

Log URL admin_kelola: `/admin-kelola` (prefix `config('admin_demo.path')`, default
`admin`), route `admin.dashboard.kelola`, judul "Admin Kelola Komdigi".

Catatan untuk backend:
- TODO-BACKEND: pemetaan peran ke tabel `users`. Sampai itu ada, peran dibaca dari
  `config/admin_demo.php` + session, dan peta izin dari `config/admin_peran.php`.
- Akun demo `admin`/`developer` masih menyimpan password plaintext di config — sebaiknya
  dipindah ke hash seperti `admin_kelola`.

Menunggu konfirmasi user:
- Apakah akun `admin_kelola` wajib ganti password saat login pertama (AGENTS.md §5.8) —
  belum diterapkan.

### 12.1 Verifikasi runtime (bukti)

Dijalankan lewat test sementara (sudah dihapus). Semua kode status diambil dari
respons nyata aplikasi.

`admin_kelola` — GET (semua 404):

```
GET admin.dashboard               => 404
GET admin.inbox                   => 404
GET admin.draf_proposal           => 404
GET admin.masa_aktif              => 404
GET admin.mitra_draf.lama         => 404
GET admin.draf_proposal.unduh     => 404
```

`admin_kelola` — mutasi (semua 404; DELETE 405 karena route-nya memang tidak ada):

```
POST   admin.dashboard.setujui => 404
POST   admin.dashboard.tolak   => 404
POST   admin.inbox.approve     => 404
POST   admin.inbox.reject      => 404
PUT    admin.masa_aktif.atur   => 404
DELETE admin.masa_aktif.atur   => 405
```

`admin_kelola` — halaman miliknya (semua 200): `admin.dashboard.kelola`,
`admin.tentang`, `admin.artikel`, `admin.galeri`.

HTML `admin_kelola`: tidak memuat "Inbox Pengajuan", "Draf Proposal AI",
"Masa Aktif Mitra", "Permohonan Perpanjangan", "Pengajuan Kerja Sama",
"Super Administrator", `link-badge`, maupun `/inbox`. Tautan admin di HTML hanya:
`/admin-kelola`, `/tentang`, `/artikel`, `/galeri`.

Login nyata (hash bcrypt, akun uji di config):

```
superadmin      -> 302 /inbox, 7 halaman 200 (dashboard, inbox, draf, masa aktif,
                   tentang, artikel, galeri, admin-kelola)
admin_kerjasama -> 302 /inbox, semua menu tampil, semua 200
admin_kelola    -> 302 /admin-kelola, 4 halaman 200, menu kerja sama tidak tampil
developer       -> GET /test-email 200 (hanya di env local)
superadmin/admin_kerjasama/admin_kelola -> GET /test-email 404
```

Tamu: semua route admin -> 302 ke `/admin-mitrakerjasama/login`;
`GET` JSON -> 401 `{"message":"Sesi admin tidak ditemukan."}`.

### 12.2 Temuan verifikasi

| ID | Severity | Lokasi | Dampak | Status |
|---|---|---|---|---|
| V1 | Tinggi | `routes/admin.php` (grup `test-email`) | `/test-email` mustahil diakses developer karena middleware `admin.only:kerja_sama` dan `admin.only:developer` bertumpuk -> selalu 404 | **Diperbaiki**: grup `uji_email` dipisah, test-email 200 untuk developer |
| V2 | Sedang | `config/admin_demo.php` | `admin_kerjasama` dan `superadmin` tidak punya akun, jadi tidak bisa login sungguhan; hanya bisa diuji lewat `withSession` | **Diperbaiki**: dua akun baru dari `.env` (`ADMIN_SUPERADMIN_*`, `ADMIN_KERJASAMA_*`), hash bcrypt, mati bila kosong |
| V3 | Rendah | `config/admin_demo.php:11,18` | Password plaintext `admin#2026`, `Dev#2026` untuk akun demo lama | **Dibiarkan** sesuai instruksi "jangan sentuh akun lama"; catatan untuk backend |
| V4 | Rendah | `config/mitra.php:24` | `demo_password => '123456'` plaintext di config (sudah ada sebelum pekerjaan ini, commit `b5a1529`) | Belum diperbaiki: milik alur mitra, di luar cakupan fase |
| V5 | Rendah | `app/Http/Middleware/HideAdmin.php`, `tests/Feature/LandingCmsSyncTest.php` | `pint --test` gagal (line ending, spasi operator, blank line) | Sudah ada sebelum fase ini; file tidak disentuh |
| V6 | Rendah | `public/assets/user/css/login.css:1` | `npm run check` gagal: file `.css` berisi komentar Blade `{{-- --}}` sehingga parser CSS error | Sudah ada sebelum fase ini |
| V7 | Info | `resources/js/routes/**` | `npm run types:check` 18 error dari generator Wayfinder (`Type '"query"' ...`) | Sudah ada sebelum fase ini |

Tidak ada password plaintext **baru**: hash `admin_kelola`/`superadmin`/
`admin_kerjasama` hanya dibaca dari `.env`; `.env.example` hanya berisi placeholder
kosong.

### 12.3 Tidak bisa login ke panel admin (diperbaiki)

Gejala: membuka `/admin-mitrakerjasama/login` lalu memasukkan kredensial
`superadmin` / `kerjasama` / `kelola` selalu gagal ("Username atau password salah").

Sebab: `.env` mesin ini belum punya `ADMIN_KELOLA_*`, `ADMIN_SUPERADMIN_*`,
maupun `ADMIN_KERJASAMA_*`. Config membacanya dengan default string kosong, dan
login sengaja melewati akun yang email/hash-nya kosong (fitur keamanan). Jadi
ketiga akun **mati** — hanya akun demo lama (`admin`, `developer`) yang hidup.
Tidak ada error di log; ini kegagalan senyap.

Perbaikan:
- `.env` (tidak di-commit, sesuai AGENTS.md §3) diisi tiga akun dengan hash bcrypt:
  `superadmin@komdigi.test`, `kerjasama@komdigi.test`, `kelola@komdigi.test`.
  Password-nya diberikan ke user lewat chat, tidak ditulis di repo.
- `tests/Feature/AdminKelolaRoleTest.php`:
  - test "akun .env yang kosong tidak membuat slot login terbuka" tidak lagi
    membaca config asli (yang bergantung isi `.env`), melainkan menyuntikkan akun
    kosong secara eksplisit — supaya hasil test tidak berubah antara mesin ber-`.env`
    kosong dan terisi.
  - test baru "setiap akun bawaan config punya jalur login yang bisa dipakai":
    setiap peran di `config/admin_peran.php` wajib punya akun, dan setiap akun wajib
    punya email terisi sehingga tidak mati senyap lagi.

Hasil verifikasi login (bcrypt, dari `.env`):

```
superadmin      -> 302 /inbox, 8 halaman 200 (dashboard, inbox, draf, masa aktif,
                   tentang, artikel, galeri, admin-kelola)
admin_kerjasama -> 302 /inbox, 8 halaman 200
admin_kelola    -> 302 /admin-kelola, dashboard/inbox/draf/masa-aktif 404,
                   tentang/artikel/galeri/admin-kelola 200
login via email juga berhasil untuk ketiga akun
password salah  -> tetap ditolak, pesan generik
akun env kosong -> tetap ditolak
```

Catatan operasional:
- `throttle:5,1` pada `POST /login` membatasi 5 percobaan per menit per IP.
  Saat menguji beberapa akun berturut-turut, percobaan ke-6 akan dibalas 429
  (bukan karena password salah).
- Setelah mengubah `.env`, jalankan `php artisan config:clear` bila config sempat
  di-cache.
- Ganti password dengan:
  `php -r "echo password_hash('PASSWORD_BARU', PASSWORD_BCRYPT);"` lalu tempel ke
  `ADMIN_*_PASSWORD_HASH`.


