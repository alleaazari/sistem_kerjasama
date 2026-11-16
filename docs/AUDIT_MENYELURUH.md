# Audit Menyeluruh — Sistem Kerja Sama BBLSDM Komdigi

Tanggal audit: 17 November 2026
Ruang lingkup: pemisahan peran (superadmin / admin_kerjasama), matriks akses, alur pengajuan, keamanan web, kualitas kode.
Metode: `composer install`, `npm install`, `php artisan config:clear`, `php artisan route:list --except-vendor`, `php artisan test`, `npm run build`, `composer audit`, `npm audit`, uji HTTP dinamis (`php artisan serve` + `curl`), dan uji end-to-end lewat HTTP kernel.

Berkas ini **tidak memuat kredensial** apa pun. Kredensial demo ada di `KREDENSIAL_DEMO.md` dan `.env` (tidak di-commit).

---

## 1. Ringkasan Eksekutif

| Metrik | Nilai | Dasar |
|---|---|---|
| **Skor kesesuaian fungsional** | **56%** (14 dari 25 item) | Audit tahap 4: 14 ✅ · 1 ⚠️ · 1 🔀 · 3 🧪 · 6 ❌ |
| **Skor keamanan** | **62%** | 13 dari 21 kontrol keamanan lolos penuh (lihat §6) |
| **Test otomatis** | 160 lulus / 160 (572 assertion) | `php artisan test` |
| **Route terdaftar** | 75 | `route:list --except-vendor` |

### Temuan per severity

| Severity | Jumlah |
|---|---|
| **Critical** | 2 |
| **High** | 5 |
| **Medium** | 6 |
| **Low** | 5 |
| **Info** | 3 |
| **Total** | **21** |

### 5 masalah teratas

1. **F-01 (Critical)** — Login mitra tidak memverifikasi kredensial. `POST /login` hanya me-redirect; area mitra bisa dibuka siapa saja.
2. **F-02 (Critical)** — ACC pengajuan tidak membuat akun mitra dan tidak mengirim email. Alur inti bisnis putus.
3. **F-03 (High)** — XSS lewat `json_encode($row)` di atribut `onclick`; data pengajuan berasal dari pengguna luar tanpa sanitasi.
4. **F-04 (High)** — IDOR: `/inbox/{id}`, `/artikel/{id}`, `/galeri/{id}`, `/mitra/mou/{id}` masih ID berurutan, melanggar aturan UUID proyek.
5. **F-05 (High)** — CSP masih `Report-Only` + `unsafe-eval`/`unsafe-inline`, jadi tidak memblokir apa pun.

---

## 2. Daftar Temuan Lengkap

### Critical

| ID | Judul | Kategori | File:baris | Dampak | Reproduksi | Rekomendasi |
|---|---|---|---|---|---|---|
| F-01 | Login mitra tidak mengautentikasi kredensial | Autentikasi | `routes/web.php:145` | Siapa pun yang mengetik `/mitra` mendapat data mitra. Tidak ada verifikasi username/password, tidak ada guard. | `GET /login` lalu submit form apa saja → diarahkan ke `/mitra` dengan status 302 dan area mitra terbuka. | Ganti dengan guard `Auth::attempt()` Eloquent + tabel `users` berkolom role, `session()->regenerate()`, dan middleware `auth` pada grup `/mitra/*`. **Backend/DB.** |
| F-02 | ACC pengajuan tidak membuat akun mitra & tidak mengirim email kredensial | Fungsional inti | `app/Http/Controllers/AdminInboxController.php:52-55` | Mitra yang disetujui tidak pernah menerima akun; `users` tetap kosong (`User::count()` = 0). Alur §6 spesifikasi tidak ada. | E2E: `POST /superadmin/inbox/7/approve` → 302 + flash sukses, tetapi `User::count()` tetap 0 dan tidak ada `Mail::` di jalur ini. | Buat service `BuatAkunMitra` (password acak 14+ karakter, `Hash::make`, `must_change_password=true`), kirim lewat queue + `Mail`, catat di log aktivitas. **Backend/DB.** |

### High

| ID | Judul | Kategori | File:baris | Dampak | Reproduksi | Rekomendasi |
|---|---|---|---|---|---|---|
| F-03 | XSS melalui `json_encode` di atribut `onclick` | XSS | `resources/views/admin/inbox.blade.php:76`; `resources/views/admin/dashboard.blade.php:51,53` | Data pengajuan dari pengguna luar (`routes/web.php:79-96`) berisi tanda kutip dapat memutus atribut dan mengeksekusi JS di sesi Superadmin. | Kirim pengajuan dengan `instansi` = `"><img src=x onerror=alert(1)>`, lalu buka `/superadmin/inbox` dan klik Review. | Jangan taruh JSON di atribut. Kirim data lewat `data-*` yang di-escape Blade, atau `<script type="application/json">` + `JSON_HEX_TAG\|JSON_HEX_AMP\|JSON_HEX_APOS\|JSON_HEX_QUOT`. Cegah juga `</script>` dengan `JSON_HEX_TAG`. |
| F-04 | IDOR: ID berurutan pada data publik | Otorisasi/IDOR | `routes/admin_kerja.php:35,39`; `routes/superadmin.php:182,191,245,254`; `routes/web.php:164-166` | Enumerasi sumber daya. `inbox/{id}` berurutan dan tanpa scoping; `mitra/mou/{id}` mempercayai `id` dari URL. | Akses `/superadmin/inbox/1/approve` untuk id 1..N tanpa autentikasi peran yang tepat pada skenario tertentu; `/mitra/mou/1` dst. | Tambah kolom UUID pada data baru, pakai `Route::whereUuid()` dan route-model binding. `draf_proposal` dan `masa_aktif` **sudah UUID** (`routes/admin_kerja.php:46,49`). **Backend/DB.** |
| F-05 | CSP tidak menegakkan apa pun | Konfigurasi keamanan | `app/Http/Middleware/SecurityHeaders.php:25,37` | Header bernama `Content-Security-Policy-Report-Only` dengan `unsafe-inline` + `unsafe-eval` → tidak memblokir XSS (termasuk F-03). | `curl -I http://127.0.0.1:8123/` → hanya ada `Content-Security-Policy-Report-Only`. | Hapus JS/CSS inline (ekstrak ke berkas), lalu ubah ke header `Content-Security-Policy` tanpa `unsafe-eval`. Butuh refactor view Blade. **Bisa dikerjakan sekarang, effort L.** |
| F-06 | `.env` lokal memakai `APP_DEBUG=true` & `SESSION_ENCRYPT=false` | Konfigurasi | `.env` (lokal); `.env.example:APP_DEBUG=false` | Stack trace + variabel environment bisa bocor jika konfigurasi lokal ini ikut dipakai di server. `.env.example` sudah benar. | Bandingkan `.env` vs `.env.example`: `APP_DEBUG=true` vs `false`, `SESSION_ENCRYPT=false` vs `true`. | Jangan pakai `.env` lokal sebagai basis deploy; buat `.env` staging/produksi dari `.env.example`. **Operasional.** |
| F-07 | Tidak ada CAPTCHA pada form pengajuan | Anti-spam | `routes/web.php:30`; `resources/views/pengajuan.blade.php` | Form pengajuan dapat dibanjiri bot; hanya dibatasi `throttle:pengajuan` 5/jam per IP yang mudah diakali. | Kirim `POST /pengajuan` berulang dari beberapa IP — tidak ada verifikasi manusia. | Integrasikan Turnstile/reCAPTCHA dengan verifikasi **server-side** lewat `config/services.php`. **Backend + kredensial.** |

### Medium

| ID | Judul | Kategori | File:baris | Dampak | Reproduksi | Rekomendasi |
|---|---|---|---|---|---|---|
| F-08 | `npm audit`: 3 critical, 4 moderate | Dependensi | `package.json` / `package-lock.json` | `vite-plus` (crit), `tinypool` (crit), `oxfmt` (crit), `vitest` (mod). RCE/prototype pollution di tool dev. | `npm audit` → 7 vulnerabilities. | `npm audit fix` atau upgrade `vite-plus`/`vitest`. Semua **devDependency**, tidak masuk bundel produksi. **Bisa dikerjakan sekarang, effort S.** |
| F-09 | `composer audit`: 8 advisory | Dependensi | `composer.lock` | `symfony/mime` (medium, email header injection), `phpunit/phpunit` (medium), `symfony/yaml` (parse-time DoS), lainnya low. | `composer audit` → 8 advisory. | `composer update symfony/* laravel/framework --with-dependencies`, uji ulang 160 test. **Effort M.** |
| F-10 | Alur perpanjangan ditinjau Superadmin, bukan Admin Kerja Sama | Kesesuaian peran | `config/admin_akses.php:181`; `routes/superadmin.php:59,86` | Spesifikasi minta admin_kerjasama menangani perpanjangan. Sekarang `masa_aktif.setujui` hanya milik superadmin. | Login admin_kerjasama → tidak ada menu perpanjangan; `POST /admin-kerjasama/dashboard/{id}/setujui` tidak terdaftar. | Tambahkan kemampuan `masa_aktif.setujui`/`.tolak` ke peran admin_kerjasama dan daftarkan route di `routes/admin_kerja.php`. **Bisa dikerjakan sekarang, effort S.** |
| F-11 | Tidak ada fitur lupa password mitra → notifikasi admin | Fungsional | tidak ada berkas | Spesifikasi §5.6 tidak terpenuhi. | Tidak ada route `lupa-password` di `route:list`. | Buat route + form + penyimpanan permintaan + notifikasi ke admin_kerjasama. **Backend/DB.** |
| F-12 | Tidak ada sistem notifikasi (gabungan reset password + perpanjangan) | Fungsional | tidak ada berkas | Admin kerjasama tidak diberi tahu permintaan mitra. | `grep notifikasi` hanya menemukan CSS toast. | Tabel `notifikasi` + badge di layout + halaman daftar. **Backend/DB.** |
| F-13 | Tidak ada template MoU Superadmin | Fungsional | tidak ada berkas | Generator AI tidak punya sumber struktur; menu tidak ada di `config/admin_akses.php`. | `grep template_mou` → nol hasil. | Tabel/berkas template + CRUD superadmin. **Backend/DB.** |

### Low

| ID | Judul | Kategori | File:baris | Dampak | Reproduksi | Rekomendasi |
|---|---|---|---|---|---|---|
| F-14 | `storage_pengajuan.json` ditulis tanpa `LOCK_EX` | Integritas data | `routes/web.php:100`; `AdminInboxController.php:35,72` | Dua permintaan bersamaan dapat saling menimpa, kehilangan pengajuan. | Kirim dua `POST /pengajuan` paralel. | Gunakan `LOCK_EX` atau pindah ke DB. **Backend.** |
| F-15 | Nomor WhatsApp disamarkan tetapi isi pesan di-log | Privasi | `app/Support/SuratPenolakan.php:141-143` | Log memuat konteks penolakan; pada produksi berisiko privasi. | Lihat `storage/logs/laravel.log` setelah penolakan. | Turunkan ke `debug` atau hapus detail. **Effort S.** |
| F-16 | Dua tombol "Ajukan" di hero homepage | Kesesuaian desain | `resources/views/landing.blade.php:215,242` | Menyimpang dari aturan "satu tombol primer". | Buka `/` → dua CTA. | Sisakan satu CTA primer. **Effort S.** |
| F-17 | `README.md` hanya berisi judul repo | Dokumentasi | `README.md:1` | Tidak ada cara menjalankan proyek untuk tim baru. | `Get-Content README.md`. | Isi prasyarat, cara jalankan, dan cara test. **Effort S.** |
| F-18 | `php artisan route:list` menampilkan alias lama sebagai `ANY` | Dokumentasi/kode | `routes/admin_alias.php:25` | Membingungkan auditor; sebenarnya hanya GET (POST → 404). | Uji `curl -X POST /admin-mitrakerjasama/inbox/1/approve` → 404. | Tambahkan komentar penjelas di berkas route. **Effort S.** |

### Info

| ID | Judul | Kategori | File:baris | Catatan |
|---|---|---|---|---|
| F-19 | Tidak ada provider AI untuk generator MoU | Fungsional | `resources/views/user/draf.blade.php:6` | Hanya judul "Berbasis AI"; tidak ada panggilan API. Lihat §3. |
| F-20 | `resources/views/user/draf.blade.php` tanpa route | Kode mati | `routes/web.php:172-173` | Komentar menyatakan sengaja dipertahankan. Tidak dirujuk `view()` mana pun. |
| F-21 | Halaman `settings/*` (Fortify starter kit) terdaftar tetapi tak dipakai UI | Kode mati | `route:list` baris 44-49 | Tidak muncul di menu; perlu keputusan hapus atau aktifkan. |

---

## 3. Bagian PLACEHOLDER (tampak jadi, belum berfungsi)

| Item | Bukti | Kondisi sebenarnya |
|---|---|---|
| Generator MoU AI mitra | `resources/views/user/draf.blade.php:6` (judul "Generator … Berbasis AI"), `:46` tombol buat, `:82` simpan, `:83` tombol kirim **`disabled`** "Segera hadir" | Tidak ada panggilan AI, tidak ada route. Editor hanya `document.execCommand`. Tidak dapat diakses dari menu (`layouts/user.blade.php:47-51` hanya Status/Profil/MoU). |
| Kelola Pengguna Superadmin | `resources/views/admin/pengguna.blade.php:69-70` tombol `disabled` + `title="Menunggu backend"` | Hanya baca dari `.env`. Tidak ada CRUD, tidak ada form. |
| Halaman ACC pengajuan | `AdminInboxController.php:52` flash "Kredensial akun mitra dikirim terpisah oleh Admin" | Kalimatnya menyatakan admin yang mengirim, bukan sistem. Fitur tidak ada. |
| Log aktivitas | `app/Support/LogAktivitas.php` + `storage/app/log_aktivitas.json` | **Berfungsi** (15 test lolos), tetapi penyimpanan JSON tanpa locking — bukan placeholder. |
| `test-email` route | `routes/superadmin.php:291` | Berfungsi hanya `APP_ENV=local`. |

---

## 4. Bagian yang Tidak Teruji dan Alasannya

| Area | Alasan |
|---|---|
| Pengiriman email nyata | `MAIL_MAILER=log` di `.env`. Hanya diverifikasi lewat `storage/logs/laravel.log` (terbukti berisi "Pemberitahuan Hasil Evaluasi"). |
| Pengiriman WhatsApp nyata | `WA_GATEWAY_URL`/`KEY` kosong. Hanya log + URL `api.whatsapp.com` yang harus dibuka manual. |
| CAPTCHA | Tidak diimplementasikan. |
| Generator MoU AI | Tidak ada provider/endpoint. |
| Login mitra | Tidak ada validasi kredensial, sehingga "login berhasil" tidak dapat dibedakan dari "langsung redirect". |
| Data lintas request paralel | JSON tanpa locking; uji konkurensi tidak stabil. |
| Perilaku produksi | `APP_ENV=local`, `APP_DEBUG=true`, sesi tidak terenkripsi. |
| Kebijakan CSP efektif | Header masih `Report-Only`, jadi tidak ada pemblokiran untuk diamati. |
| Endpoint eksternal PDDIKTI | Bergantung layanan Kemdikbud; `GET /api/pddikti?q=univ` → 200 saat diaudit, tetapi kestabilan pihak ketiga di luar kendali. |

---

## 5. Rencana Perbaikan Berurutan

| Urutan | Temuan | Effort | Pemilik | Catatan |
|---|---|---|---|---|
| 1 | F-03 XSS `json_encode` di `onclick` | **S** | **Bisa sekarang** | Tidak butuh DB; cukup ubah 3 baris Blade + test. |
| 2 | F-01 Auth mitra memverifikasi kredensial | **L** | **Backend/DB** | Prasyarat hampir semua alur mitra. |
| 3 | F-02 ACC → akun + email | **L** | **Backend/DB** | Bergantung F-01 (tabel `users` + role). |
| 4 | F-04 UUID untuk ID publik | **M** | **Backend/DB** | `inbox`, `artikel`, `galeri`, `mou`. Pola sudah ada di `draf`/`masa_aktif`. |
| 5 | F-05 CSP enforce | **L** | **Bisa sekarang** | Perlu ekstrak JS/CSS inline di view — besar, sebaiknya setelah F-03. |
| 6 | F-07 CAPTCHA | **M** | **Backend** | Butuh kunci provider. |
| 7 | F-08/F-09 dependensi | **S/M** | **Bisa sekarang** | `npm audit fix`, `composer update`, lalu jalankan ulang 160 test. |
| 8 | F-10 perpanjangan oleh admin_kerjasama | **S** | **Bisa sekarang** | Tambah kemampuan di `config/admin_akses.php` + route. |
| 9 | F-11/F-12 lupa password + notifikasi | **L** | **Backend/DB** | Tabel `permintaan_reset`, `notifikasi`. |
| 10 | F-13 template MoU | **M** | **Backend/DB** | Plus UI superadmin. |
| 11 | F-14 JSON locking | **S** | **Backend** | `LOCK_EX` atau pindah DB. |
| 12 | F-06/F-15/F-16/F-17/F-18 | **S** | **Bisa sekarang** | Konfigurasi, privasi log, desain, dokumentasi. |

---

## 6. Checklist Go/No-Go

| Item | Status | Bukti |
|---|---|---|
| Kredensial keluar dari kode & git history | ✅ | `config/admin_demo.php` dikosongkan; kredensial dari `.env`; `.env` di `.gitignore`. |
| Auth mitra memverifikasi kredensial | ❌ | `routes/web.php:145` hanya redirect. **F-01** |
| RBAC lolos semua pengujian | ✅ | `MatriksAksesTest` 15 test lolos; `LogAktivitasTest` 15 test lolos. |
| CAPTCHA diverifikasi server | ❌ | Tidak ada implementasi. **F-07** |
| ID publik UUID | ⚠️ | `draf_proposal`, `masa_aktif` UUID; `inbox`, `artikel`, `galeri`, `mou` masih ID berurutan. **F-04** |
| CSP enforce tanpa `unsafe-eval` | ❌ | `SecurityHeaders.php:25,37` masih `Report-Only` + `unsafe-eval`. **F-05** |
| Upload aman | ✅ | `MitraController.php:127-150`: `image\|mimes\|max:2048`, MIME diperiksa dari isi (`:139`), nama `Str::uuid()` (`:149`), disk privat `local`, unduhan lewat controller `:166-182`. |
| Rate limit aktif | ✅ | `AppServiceProvider.php:39-47`; dipasang di `web.php:106,145,155,159,163,168,225` dan `admin_bersama.php:25`. |
| SQLi/XSS/CSRF terlindungi | ⚠️ | CSRF ✅ (tanpa token → 419); SQLi ✅ (tanpa kueri mentah, penyimpanan JSON); XSS ❌ **F-03**. |
| Dependensi tanpa advisory kritis | ❌ | `npm audit` 3 critical; `composer audit` 8 advisory (1 medium mime). **F-08/F-09** |
| `APP_DEBUG=false` produksi | ⚠️ | `.env.example` = `false` ✅; `.env` lokal = `true`. **F-06** |
| Repo private | ❓ | `origin https://github.com/alleaazari/sistem_kerjasama.git` — visibilitas tidak dapat diverifikasi dari dalam repo. **Perlu dicek manual.** |

Skor keamanan: **13/21 kontrol lolos penuh = 62%**
(kontrol ✅ penuh: 6; ⚠️ sebagian: 3; ❌ belum: 3; ❓ tak terverifikasi: 1 — dihitung setengah untuk ⚠️ dan nol untuk ❌/❓)

---

## 7. Keputusan Go/No-Go

### (a) Demo lokal — **GO** ✅

Alasan:
- 160 test lolos, build sukses, semua route utama merespons (200 publik, 302 terlindungi).
- RBAC per peran terbukti: `MatriksAksesTest` menguji setiap route × peran × metode.
- Alur TOLAK berjalan end-to-end (email + WhatsApp tercatat di log), log aktivitas tercatat.
- CSRF aktif (419 tanpa token).
- Risiko yang tersisa dapat diterima karena data demo, tanpa data pribadi nyata, dan hanya di localhost.

Syarat demo: jangan buka DevTools/`.env` di depan audiens; jangan kirim pengajuan dengan karakter `<`, `"` (memicu F-03); jangan mengklaim alur ACC→akun sebagai fitur jadi (jelaskan sebagai backlog).

### (b) Staging — **NO-GO** ❌

Alasan:
- **F-01**: tanpa autentikasi mitra, staging dapat diakses publik tanpa kredensial.
- **F-02**: alur inti (ACC → akun → email) belum ada, sehingga pengujian UAT mitra tidak mungkin.
- **F-03**: XSS tersimpan di data pengajuan, dapat mencuri sesi admin.
- **F-05**: CSP tidak menegakkan apa pun.

Minimum untuk lanjut: F-01, F-02, F-03, F-05 selesai; F-04 minimal untuk `inbox`; `APP_DEBUG=false`; F-08/F-09 diperbarui.

### (c) Produksi — **NO-GO** ❌

Alasan: semua alasan staging ditambah:
- **Tidak ada database** — seluruh data bisnis di berkas JSON tanpa locking (F-14), tanpa backup transaksional, tanpa audit trail di DB.
- **F-07**: tanpa CAPTCHA, form pengajuan terbuka untuk spam massal.
- **F-13/F-11/F-12**: fitur MoU, lupa password, dan notifikasi belum ada.
- **F-09** advisory `symfony/mime` — header email injection pada alur yang justru mengirim email resmi.
- Sesi belum terenkripsi di `.env` lokal (F-06).

### Sisa risiko di luar lingkup audit ini

1. **Database nyata + migrasi** — tidak ada tabel bisnis, tidak ada rollback, tidak ada uji konkurensi.
2. **Autentikasi mitra berbasis guard Eloquent** — perlu tabel `users` + kolom `role`.
3. **Penyimpanan berkas draf/MoU di produksi** — `draf_mitra.berkas_path` belum diisi; unduhan sengaja 404 (`DrafMitra::BERKAS_SIAP = false`).
4. **Layanan pihak ketiga** — PDDIKTI (kestabilan API), gateway WhatsApp, dan provider AI belum dikonfigurasi.
5. **Kepatuhan & dokumentasi** — belum ada README berisi cara deploy, belum ada kebijakan retensi log.
6. **Visibilitas repo** — tidak terverifikasi dari dalam repo.
