# LANGKAH PEMBARUAN — Sistem Kerjasama BBLSDM Komdigi Medan

Aturan dan batasan ada di `docs/ATURAN_PEMBARUAN.md` (wajib dipatuhi) dan `AGENTS.md` yang sudah ada di root. Kondisi repo saat ini ada di `docs/AUDIT_FRONTEND.md`. Kerjakan **satu fase per giliran**, lalu berhenti dan lapor sesuai format di `ATURAN_PEMBARUAN.md` bagian 9.

Strategi inti: UI yang tayang adalah **Blade + CSS statis**. Pembaruan dilakukan **di atas Blade yang ada** (bukan menulis ulang ke React), dengan memindahkan data dari JSON/session ke database, menambah peran dan otorisasi, dan menutup celah keamanan yang ditemukan audit.

---

## A. Ringkasan Spesifikasi (Sumber: dokumen "SISTEM KERJASAMA")

### A.1 Peran
Superadmin, Admin Kerjasama, User/Mitra.

### A.2 Alur Utama
1. Mitra diarahkan ke **Homepage** (informasi BBLSDM Komdigi Medan).
2. Mitra mengajukan kerjasama lewat **tombol di kanan atas Homepage**.
3. Mitra mengisi **form pengajuan**.
4. Form masuk ke **dashboard Admin Kerjasama dan Superadmin**.
5. Pengajuan dapat **disetujui atau ditolak** oleh Admin Kerjasama/Superadmin.
6. **Ditolak:** sistem mengirim pesan ke **WhatsApp atau email PIC**.
7. **Disetujui:** sistem otomatis **membuat akun** (username dan password) untuk mitra, dikirim ke **email PIC**.
8. Email berisi username, password buatan sistem, dan **link login khusus mitra** yang terpisah dari Homepage.
9. Mitra login dengan username dan password.
10. **Lupa password:** mitra meminta perbarui password; permintaan menuju **Admin Kerjasama**.
11. Mitra dapat **mengedit profil**, **melihat status dan masa aktif kerjasama**, dan **mengajukan perpanjangan** (ke dashboard Admin Kerjasama dan Superadmin).
12. Mitra dapat **membuat MoU** lalu mengirimnya ke dashboard Admin Kerjasama/Superadmin.
13. MoU dapat **digenerate lewat AI** berbasis template; mitra tinggal melengkapi informasi instansinya.

### A.3 Isi Tiap Halaman

**Homepage:** profil BBLSDM Komdigi Medan; profil petinggi dan struktur Komdigi; visi dan misi; berita dan artikel kerjasama; galeri foto kegiatan; satu tombol "Ajukan Kerjasama".

**Form Pengajuan:**
- Data Perguruan Tinggi/Instansi: nama (pencarian otomatis via API), jenis instansi (perguruan tinggi / instansi pemerintah lain), unit kerja/divisi/fakultas, kota/kabupaten, provinsi, alamat lengkap (diketik sendiri).
- Data PIC: nama lengkap, jabatan, email resmi, no. telepon/WhatsApp.
- Tujuan dan detail: bidang kerjasama yang diminati; tujuan pengajuan (alasan, diketik); metode pelatihan (tatap muka/offline, daring/online, hybrid/blended, workshop, magang/praktik, seminar/webinar); pembiayaan (APBD, APBN, Cost Sharing); tombol kirim.

**Halaman Mitra:** status dan masa aktif kerjasama; data detail dari form pengajuan (baca saja); **edit profil instansi** (terpisah dari halaman detail); generator draf MoU yang dapat dilihat dan diedit mitra.

**Halaman Superadmin:** inbox pengajuan (disetujui/ditolak/menunggu); atur template MoU; kelola profil dan visi-misi; kelola artikel/berita; kelola galeri.

**Halaman Admin Kerjasama:** inbox pengajuan (sama dengan Superadmin); dapat memperpanjang kerjasama; notifikasi permintaan password baru (lalu dikirim ke email mitra); notifikasi permintaan perpanjangan, **dalam satu tombol notifikasi yang sama** dengan permintaan ubah password.

### A.4 Persyaratan Keamanan dari Spesifikasi

| # | Ancaman | Backend | Front-end (UX saja) |
|---|---------|---------|---------------------|
| 1 | File Upload | Validasi MIME dan ekstensi, rename file, simpan di luar web root | `accept=".pdf,.docx"` |
| 2 | SQL Injection | Prepared statement, sanitasi input | Tipe input HTML5, `required` |
| 3 | XSS | Sanitasi dan escaping output, HTTP-Only cookie | Hindari injeksi HTML mentah |
| 4 | BOLA/IDOR | Validasi kepemilikan data, UUID | — |
| 5 | Spam/DoS form | Rate limiting | CAPTCHA |

---

## B. Peta Kebutuhan terhadap Kondisi Saat Ini (Ringkas dari Audit §9)

| Kebutuhan | Status | Basis yang dipakai |
|---|---|---|
| Peran Superadmin/Admin Kerjasama/Mitra | BELUM ADA | Ganti `config/admin_demo.php` + `AdminOnly` dengan `users.role` + Policy |
| Homepage | PERLU DIUBAH | `landing.blade.php` (data dari session/array default → DB) |
| Form pengajuan | ADA (perlu diubah) | `pengajuan.blade.php`, proxy `GET /api/pddikti`, tambah `jabatan_pic`, `bidang`, `pddikti_id` tervalidasi |
| Inbox, setujui/tolak | ADA (perlu diubah) | `admin/inbox.blade.php`, `routes/admin.php:275-346` → DB, UUID, Policy |
| Akun otomatis + email | BELUM ADA | Approve saat ini hanya mengubah status |
| Notifikasi penolakan | PERLU DIUBAH | Template surat + `Mail::raw` + WA ada; pindah ke queue/notification |
| Login mitra URL terpisah | BELUM ADA | `POST /login` saat ini tanpa autentikasi |
| Halaman mitra | PERLU DIUBAH | `user/status`, `user/profile`; sisa hari hardcoded `layouts/user.blade.php:178,181` |
| Generator dan template MoU | BELUM ADA | `user/draf.blade.php` hanya simulasi `setTimeout` |
| Notifikasi Admin Kerjasama | BELUM ADA | Tombol perpanjangan nonaktif `admin/mitra_draf.blade.php:33-34` |
| CMS Superadmin | PERLU DIUBAH | `admin/{tentang,artikel,galeri,_cms}` → DB, bukan session |

---

## C. Fase Pengerjaan

> **[KONFIRMASI]** = jawaban user dibutuhkan sebelum bagian itu dikerjakan (`ATURAN_PEMBARUAN.md` bagian 4). Fase 1 dan seterusnya selalu mengikuti aturan anti-kerusakan (bagian 2A).

### Fase 0B — Persiapan (tanpa mengubah kode aplikasi)
1. Baca `AGENTS.md` (yang sudah ada), `docs/ATURAN_PEMBARUAN.md`, file ini, dan `docs/AUDIT_FRONTEND.md`.
2. Jalankan `git status`; catat file yang berubah. Jangan menyentuhnya.
3. Buat `docs/PROGRESS.md`: ringkasan audit, daftar fase, daftar temuan keamanan beserta fase penanganannya (isi dari tabel bagian D).
4. Ringkas **isi `AGENTS.md` yang sudah ada** menjadi daftar aturan (agar tidak dilanggar) dan tandai jika ada yang bertentangan dengan dokumen ini.
5. Ajukan **pertanyaan konfirmasi** (`ATURAN_PEMBARUAN.md` bagian 4) dalam satu daftar bernomor, lengkap dengan rekomendasi default untuk tiap butir.
6. **Berhenti.**

### Fase 1 — Baseline Keamanan dan Kebersihan (tanpa perubahan fitur)
Tujuan: menutup celah yang tidak mengubah alur bisnis.
1. `.env.example`: `APP_DEBUG=false`; tambahkan `ADMIN_PATH`, `ADMIN_ALLOWED_IPS`, `WA_GATEWAY_URL`, `WA_GATEWAY_KEY`, `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `SESSION_SAME_SITE` (tanpa nilai rahasia). Pertimbangkan `SESSION_ENCRYPT=true`.
2. Middleware security headers global: `nosniff`, `frame-ancestors`/`X-Frame-Options`, `Referrer-Policy`, dan **CSP Report-Only**.
3. Throttle bernama pada route yang belum punya: `POST /pengajuan`, `POST /login`, `GET /api/pddikti`, `POST /mitra/profile`.
4. Proxy PDDIKTI (`routes/web.php:161-199`): hapus `withoutVerifying()` dan User-Agent palsu, tambah timeout dan cache.
5. Hapus kredensial demo dari UI: `login.blade.php:10-14,20,24`; tampilan di `admin/login.blade.php:34-44` hanya dengan syarat debug dan tanpa password nyata.
6. Nonaktifkan `GET {admin}/test-email` di luar lingkungan lokal, atau hapus (setelah konfirmasi).
7. Ganti URL hardcoded `127.0.0.1:8000` (`layouts/admin.blade.php:114`, `routes/admin.php:106`) dengan `config('app.url')`/`route()`.
8. Selesaikan test lama yang pasti gagal (`DashboardTest`, `ExampleTest`) sesuai jawaban konfirmasi.
9. Test baru: header keamanan hadir, throttle aktif (429 setelah batas), `test-email` tidak tersedia di produksi.

### Fase 2 — Fondasi Data dan Peran
Tujuan: menggantikan JSON/session dan akun plaintext dengan database + peran.
1. Migration **aditif**: kolom `role`, `must_change_password` (dan UUID publik bila diperlukan) pada `users`; tabel `mitra`, `pengajuan` (atau gabungan sesuai desain, dengan UUID), `artikel`, `galeri`, `petinggi`, `visi_misi`, `mou_template`, `mou_draf`, `permintaan_mitra` (jenis: reset_password, perpanjangan), `dokumen`. Pakai UUID (`HasUuids`) untuk model yang ID-nya muncul di URL.
2. Model dengan `#[Fillable]` eksplisit, relasi, dan enum status pengajuan (satu sumber untuk `menunggu`/`disetujui`/`ditolak`).
3. Peran `superadmin`, `admin_kerjasama`, `mitra`; middleware peran; Policy per model. Pemetaan dari `admin`/`developer` sesuai konfirmasi.
4. Migrasikan login admin dari session `admin_auth` + `config/admin_demo.php` ke `Auth` dengan password ter-hash. **Pertahankan** prefix admin rahasia dan `HideAdmin` sebagai lapisan tambahan. Hapus akun plaintext dari config setelah penggantinya berfungsi.
5. Seeder akun Superadmin/Admin Kerjasama dengan password dari input/`.env`, bukan hardcode.
6. Perintah artisan **impor idempotent** `storage_pengajuan.json` → DB (dijalankan user setelah dicadangkan). File JSON tidak dihapus.
7. Test: peran yang salah ditolak (403/404), route butuh login, UUID dipakai di URL, impor aman diulang.

### Fase 3 — Homepage dan CMS Superadmin
1. `landing.blade.php` membaca konten dari DB (profil BBLSDM, petinggi dan struktur, visi-misi, berita, galeri); fallback default dipindahkan ke seeder, bukan hardcode di view.
2. Pengelolaan Superadmin untuk artikel, galeri, petinggi, visi-misi, dan profil: pindah dari session ke DB; ID di URL memakai UUID; hanya `superadmin` (Policy).
3. Upload gambar mengikuti `ATURAN_PEMBARUAN.md` 5.1: file di disk privat dengan nama acak, route media aman, tidak ada base64 di session. Perbaiki bug foto "Tambah Petinggi" (`nl_foto` diabaikan, audit §8.1).
4. Perbaiki XSS di `admin/_cms.blade.php:58`, `admin/tentang.blade.php:183-203,232`, `admin/mitra_draf.blade.php:143`: pakai `data-*` + `textContent`.
5. Putuskan klaim statistik dan gambar Unsplash sesuai konfirmasi.
6. Tombol "Ajukan Kerjasama" di kanan atas tetap satu.
7. Test: hanya Superadmin yang bisa CRUD; upload non-gambar ditolak; payload `<script>` di judul/konten tampil ter-escape.

### Fase 4 — Form Pengajuan Kerjasama
1. Perbarui `pengajuan.blade.php` agar sesuai A.3: tambahkan `jabatan_pic`, `bidang` (daftar sesuai konfirmasi), `pddikti_id`; semua tervalidasi lewat Form Request (`StorePengajuanRequest`). Hapus `enctype` yang tidak perlu dan `novalidate` yang mematikan validasi dasar browser bila memungkinkan.
2. Pindahkan logika `POST /pengajuan` dari closure ke controller; simpan ke DB dengan status `menunggu` dan UUID (tidak lagi menulis JSON).
3. Perbaiki XSS di JS pencarian PT: `pengajuan.blade.php:1211-1221,1252,1379,1405` → bangun DOM dengan `createElement`/`textContent`; hapus `onclick` berbasis konkatenasi string.
4. CAPTCHA **[KONFIRMASI]**: widget di front-end, verifikasi token di server.
5. Rate limit endpoint kirim dan endpoint pencarian PT.
6. Test: validasi menolak input buruk, throttle aktif, token CAPTCHA tidak valid ditolak, payload XSS tersimpan sebagai teks.

### Fase 5 — Inbox Pengajuan dan Persetujuan
1. `admin/inbox.blade.php` membaca dari DB; ID di URL UUID; hanya `superadmin` dan `admin_kerjasama`. Perbaiki `onclick="bukaModal({{ json_encode($row) }})"` (`inbox.blade.php:87`) → `data-*`/JSON aman.
2. Transisi status hanya dari `menunggu`; ID tak dikenal menghasilkan 404 (bukan "sukses").
3. **Tolak:** simpan alasan; kirim email dan WhatsApp lewat queue (Notification/Mailable + service WhatsApp dengan HTTP client). Template surat dipindahkan dari `routes/admin.php:62-204` ke class/view terpisah; hapus log manual `file_put_contents(@)`.
4. **Setujui:** dalam satu transaksi buat akun mitra (username unik, password acak kuat, `must_change_password`), set masa aktif **[KONFIRMASI durasi]**, kirim email ke PIC berisi username, password, dan link login mitra.
5. Pastikan tidak ada password plaintext di log, DB, atau tampilan admin.
6. Test: mitra tidak bisa mengakses inbox; persetujuan membuat tepat satu akun; persetujuan ganda ditolak; email terkirim (mailer fake) dan password tidak muncul di log.

### Fase 6 — Portal Mitra
1. **Login mitra** di URL terpisah **[KONFIRMASI `/mitra/login`]**: autentikasi sungguhan, throttle, regenerasi session, pesan error umum. `/login` lama dialihkan. Logout menginvalidasi session.
2. Paksa ganti password pada login pertama bila `must_change_password`.
3. Halaman **Status & masa aktif**: dihitung dari DB; hapus nilai hardcoded `layouts/user.blade.php:178,181`; ambang "segera berakhir" 90 hari sesuai `AGENTS.md`, diambil dari konfigurasi.
4. Halaman **Detail data mitra** (baca saja) dan halaman **Edit profil instansi** (terpisah), scoping ke mitra yang login; logo lewat upload aman (validasi `image|mimes|max`, nama acak, disk privat).
5. **Ajukan perpanjangan:** membuat `permintaan_mitra` jenis `perpanjangan` (aktifkan tombol yang nonaktif `layouts/user.blade.php:186`), masuk ke notifikasi admin.
6. **Lupa password:** membuat `permintaan_mitra` jenis `reset_password` (bukan reset mandiri); throttle; respons tidak membocorkan akun. Nonaktifkan reset mandiri Fortify sesuai konfirmasi.
7. Test BOLA: mitra A tidak bisa membaca/mengubah data mitra B; semua URL memakai UUID; logout menginvalidasi session.

### Fase 7 — MoU (Template, Generator AI, Penyuntingan)
1. Superadmin: halaman kelola template MoU (placeholder jelas, mis. `{{nama_instansi}}`).
2. Mitra: ganti simulasi "Draf Proposal AI" (`user/draf.blade.php`) menjadi **Generator MoU** sungguhan berbasis template; mitra melengkapi informasi instansi, melihat dan mengedit draf, menyimpan, lalu **mengirim** ke dashboard admin.
3. `MouGeneratorService` dengan antarmuka dan driver **[KONFIRMASI penyedia AI]**. Selama pengembangan, driver lokal yang mengisi template tanpa panggilan eksternal.
4. Perbaiki XSS `user/draf.blade.php:131-177` (interpolasi ke `innerHTML`): render sebagai teks/`textarea`.
5. Mitigasi prompt injection, batas panjang input, throttle.
6. Test: mitra hanya mengakses MoU miliknya; mitra tidak bisa mengubah template; input berlebih ditolak; payload XSS ter-escape.

### Fase 8 — Halaman Admin Kerjasama dan Notifikasi
1. `admin/mitra_draf.blade.php`: aktifkan aksi (perpanjang, unduh draf) yang saat ini dinonaktifkan; tentukan nasib modal `openExtendModal()` yang tak terjangkau (audit §12.12).
2. **Satu tombol notifikasi** yang menampung dua jenis: permintaan password baru dan permintaan perpanjangan, dengan label jenis.
3. Aksi: **kirim password baru** ke email mitra (acak kuat, hanya hash tersimpan, wajib ganti saat login berikutnya) dan **perpanjang kerjasama** (ubah masa aktif); Admin Kerjasama juga dapat memperpanjang tanpa permintaan mitra.
4. MoU yang dikirim mitra tampil untuk Admin Kerjasama dan Superadmin.
5. Test: otorisasi per peran, satu notifikasi memuat dua jenis, perpanjangan mengubah masa aktif dengan benar.

### Fase 9 — Hardening dan Penutupan
1. Jadikan CSP **enforce**: pindahkan JS/CSS inline ke `public/assets/**` atau beri nonce; hapus `style` inline sesuai `AGENTS.md`.
2. Telusuri pola berisiko: `{!! !!}`, `innerHTML`, `onclick` dengan data, `withoutVerifying`, `file_put_contents(@)`, ID berurutan di URL, base64 di session, route tanpa middleware/Policy.
3. Jalankan seluruh pengecekan (`ATURAN_PEMBARUAN.md` bagian 7) dan tinjau ulang checklist bagian 5 untuk setiap fitur dengan bukti.
4. Perbarui `README.md` (cara menjalankan, variabel environment, peran, alur), `.env.example`.
5. Tulis sisa risiko dan saran di `docs/PROGRESS.md`.

---

## D. Temuan Audit → Fase Penanganan

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

## E. Definisi Selesai (Per Fase)

- Semua butir fase dikerjakan, atau yang tertunda dicatat beserta alasannya.
- Checklist keamanan yang relevan terpenuhi dengan bukti (test/path:baris).
- Lint, type-check, dan seluruh test lolos (atau kegagalannya dilaporkan jujur dan dibedakan: lama vs baru).
- `docs/PROGRESS.md` diperbarui.
- Laporan mengikuti format `ATURAN_PEMBARUAN.md` bagian 9, lalu OpenCode berhenti menunggu konfirmasi.
