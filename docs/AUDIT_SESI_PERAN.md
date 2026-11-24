# Audit — Sesi Terpisah per Peran

Tanggal: 2026-11-24
Branch: `fix/sesi-terpisah-per-peran`
Cakupan: `SessionPerPeran`, config sesi, login/logout panel, CSRF, RBAC.

Ringkasan: isolasi sesi per peran **terbukti bekerja**. Satu temuan Medium
(config `secure` bergantung `.env`, tidak otomatis di produksi) diperbaiki.
Tidak ada temuan Critical/High.

---

## 1. Skenario tiga peran aktif bersamaan + logout

Bukti: `tests/Feature/Auth/AuditSesiPeranTest.php::tiga peran aktif bersamaan`
(HTTP penuh, tiga cookie jar, driver sesi `file`).

| Langkah | Status |
|---|---|
| A login superadmin | 302 (ke beranda) |
| B login admin konten | 302 (ke beranda) |
| C login admin kerjasama | 302 (ke beranda) |
| A /superadmin/dashboard #0..#2 | 200, 200, 200 |
| B /admin-konten/dashboard #0..#2 | 200, 200, 200 |
| C /admin-kerjasama/inbox #0..#2 | 200, 200, 200 |
| Logout B | 302 (ke login konten) |
| B ulang /admin-konten/dashboard | bukan 200 (terkunci) |
| A /superadmin/dashboard sesudah | 200 (tetap valid) |
| C /admin-kerjasama/inbox sesudah | 200 (tetap valid) |

## 2. Isolasi lintas peran (GET & mutasi)

Bukti: `AuditSesiPeranTest.php::cookie satu peran tidak membuka URL peran lain`.

| Cookie peran | Route tujuan | Method | Hasil |
|---|---|---|---|
| superadmin | /admin-konten/dashboard | GET | 404 |
| superadmin | /admin-konten/artikel | GET | 404 |
| superadmin | /admin-konten/artikel (store) | POST | 404 |
| superadmin | /admin-kerjasama/inbox | GET | 404 |
| admin_konten | /superadmin/dashboard | GET | 404 |
| admin_konten | /superadmin/inbox | GET | 404 |
| admin_konten | /superadmin/artikel (store) | POST | 404 |

Ditegakkan `app/Http/Middleware/AdminOnly.php:49-59` (`abort(404)` bila prefix
peran ≠ prefix URL, dan bila kemampuan tak dimiliki).

## 3. Header Set-Cookie tiap panel

Bukti: `php artisan serve` + `curl -D -` (nilai cookie disunting).

| URL | Cookie sesi | Path | HttpOnly | SameSite |
|---|---|---|---|---|
| /superadmin/login | kerjasama_superadmin_session | /superadmin | ya | Lax |
| /admin-kerjasama/login | kerjasama_admin_kerjasama_session | /admin-kerjasama | ya | Lax |
| /admin-konten/login | kerjasama_admin_konten_session | /admin-konten | ya | Lax |
| / (publik) | kerjasama-session | / | ya | Lax |
| /mitra | kerjasama-session | / | ya | Lax |

`XSRF-TOKEN` mengikuti path yang sama per panel. **Tidak ada** cookie sesi
panel admin dengan `path=/`. Path dipilih `app/Http/Middleware/SessionPerPeran.php:41-44`
dari `config/session_roles.php` (turunan prefix `.env`, tanpa hardcode).
Urutan middleware benar: `SessionPerPeran` sebelum `EncryptCookies` +
`StartSession` (`bootstrap/app.php:70`, `prependToGroup('web', ...)`).

## 4. CSRF

Bukti server nyata (`php artisan serve`, login admin konten demo):

| Request | Hasil |
|---|---|
| POST /admin-konten/artikel TANPA token | **419** |
| POST /admin-konten/artikel DENGAN token `_token` | 302 (lolos) |

Di kernel tes, penolakan tanpa token tidak dapat diuji karena
`PreventRequestForgery` melewati CSRF saat `runningUnitTests()`
(`vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/PreventRequestForgery.php:99`).
Karena itu bukti 419 diambil dari server nyata. Test kernel menguji jalur
"dengan token lolos" (`AuditSesiPeranTest.php`).

## 5. Session fixation & logout

- Fixation: `AuditSesiPeranTest.php::session id berganti setelah login` —
  id sesi sebelum ≠ sesudah login (dari `AdminLoginController::masuk`
  `$request->session()->regenerate()`).
- Logout per peran: `SessionPerPeran` memisahkan cookie, sehingga
  `invalidate()` di `AdminLoginController::keluar` hanya menghancurkan sesi
  panel itu. Terbukti di skenario §1 (logout B tidak mengeluarkan A & C).

## 6. Tidak ada status peran global

`grep` `Cache::put|Cache::forever|Cache::remember|admin_auth` di `app/` +
`config/` hanya menemukan `AdminAkses::KUNCI_SESI = 'admin_auth'`
(`app/Support/AdminAkses.php:19`) — kunci sesi per pengguna, bukan global.
Tidak ada penyimpanan peran di cache/file/DB lintas pengguna.

## 7. Regresi RBAC

`php artisan test` seluruh suite: **335 lolos**. Matriks peran × route lama
(`AdminKontenTest`, `MatriksAksesTest`, `AdminLoginTest`, `PintuMasukTest`)
tetap hijau. Tidak ada pelonggaran: `admin.only` tetap `abort(404)` lintas prefix.

## 8. Konfigurasi produksi

| Item | Nilai | Catatan |
|---|---|---|
| SESSION_SECURE_COOKIE | `.env.example:36` = false (dev) | + komentar: produksi wajib true |
| session.secure default | otomatis `true` saat `APP_ENV=production` | `config/session.php:172` |
| SESSION_DRIVER | `database` (`.env.example`), `file` (.env lokal) | aman untuk produksi |
| APP_DEBUG | `false` (`.env.example:5`) | sesuai |
| SESSION_HTTP_ONLY | `true` | sesuai |
| SESSION_SAME_SITE | `lax` | sesuai |

## 9. Pengecekan

- `php artisan test` → **335 test, 335 lolos**.
- `pint --test` (berkas diubah) → passed. `phpstan analyse` → 0 error.
- `npm run build` → berhasil.
- `npm run types:check` → **48 error, LAMA** (bukan akibat audit ini):
  Wayfinder pre-existing di `resources/js/actions/.../RedirectController.ts`,
  `resources/js/routes/mitra/index.ts`, `app-header.tsx`.

## 10. Temuan

| ID | Severity | file:baris | Dampak | Perbaikan |
|---|---|---|---|---|
| A1 | Medium | `config/session.php:172` (lama: `env('SESSION_SECURE_COOKIE')`) | Di produksi tanpa `.env`, cookie sesi bisa terkirim via HTTP (tidak Secure) | Default otomatis `true` saat `APP_ENV=production` — **sudah diperbaiki** |
| A2 | Low | `.env.example:36` | Tak ada penanda produksi wajib `true` | Tambah komentar panduan — **sudah diperbaiki** |
| A3 | Low | `SessionPerPeran.php:37` | Prefix peka huruf besar/kecil; `/SuperAdmin` memakai cookie bawaan (tetap aman, hanya tak terpisah) | Diterima: URL peran selalu huruf kecil, tidak dieksploitasi |
| A4 | Info | `AdminLoginController.php:109` | `forget(['admin_auth','admin_artikel','admin_galeri'])` menyertakan kunci usang | Tidak berbahaya (kunci tak terpakai), tidak diubah |

Critical: 0. High: 0. Medium: 1 (A1, diperbaiki). Low/Info: sisanya.

## Catatan untuk backend

- Saat peran pindah ke tabel `users`, `config/session_roles.php` cukup jadi
  kolom `session_cookie`; middleware tidak berubah.
- `SESSION_ENCRYPT=true` (`.env.example:33`) disarankan agar payload sesi di
  storage tidak plaintext; `.env` lokal memakai `false` (dev).
