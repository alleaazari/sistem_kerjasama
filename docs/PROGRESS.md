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
