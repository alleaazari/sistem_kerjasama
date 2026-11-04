# PROMPT OPENCODE — Sistem Kerjasama BBLSDM Komdigi Medan

Cara pakai: salin isi blok **PROMPT AWAL** ke OpenCode (jalankan dari root proyek, **mode Plan** untuk prompt awal). Setelah OpenCode melapor dan Anda menjawab pertanyaannya, lanjutkan dengan **PROMPT LANJUTAN** satu fase per giliran. Jangan menempel beberapa fase sekaligus.

Sebelum memulai, pastikan:
- File sudah ada di tempatnya: `docs/ATURAN_PEMBARUAN.md`, `docs/LANGKAH_PEMBARUAN.md`, `docs/AUDIT_FRONTEND.md`, dan `AGENTS.md` lama **tidak diubah/ditimpa**.
- Perubahan lama (`admin/mitra_draf.blade.php`, `layouts/user.blade.php`, `user/draf.blade.php`) sudah Anda commit atau simpan.
- Anda sedang berada di branch kerja, bukan `main`.

---

## PROMPT AWAL (Fase 0B — Persiapan)

```
Kamu adalah engineer senior Laravel (Blade) yang sangat memperhatikan keamanan aplikasi web.

KONTEKS
Repo ini adalah Sistem Kerjasama BBLSDM Komdigi Medan. Audit repo sudah dibuat di docs/AUDIT_FRONTEND.md. Temuan utama: UI yang tayang adalah Blade + CSS statis (bukan React); stack React/Inertia terpasang tetapi tidak aktif; tidak ada tabel bisnis (data di JSON/session); login mitra belum mengautentikasi; akun admin plaintext di config; belum ada role/Policy/UUID. Kami akan memperbarui sistem sesuai spesifikasi baru di atas Blade yang ada.

WAJIB DIBACA SEBELUM APA PUN (berurutan)
1. AGENTS.md (sudah ada; JANGAN diubah atau ditimpa)
2. docs/ATURAN_PEMBARUAN.md (aturan mengikat; bagian 5 keamanan selalu menang)
3. docs/LANGKAH_PEMBARUAN.md (spesifikasi dan fase)
4. docs/AUDIT_FRONTEND.md (fakta kondisi repo)

TUGAS SEKARANG: HANYA FASE 0B. JANGAN mengubah kode aplikasi.
1. Jalankan `git status` dan laporkan file yang berubah. Jangan menyentuhnya.
2. Buat docs/PROGRESS.md: ringkasan audit, daftar fase, dan tabel temuan keamanan beserta fase penanganannya (ikuti bagian D di LANGKAH_PEMBARUAN.md).
3. Ringkas isi AGENTS.md yang sudah ada menjadi daftar aturan bernomor, dan tandai jika ada yang bertentangan dengan docs/ATURAN_PEMBARUAN.md.
4. Baca composer.json dan laporkan definisi script `ci:check`, `lint:check`, `types:check`, `test`, serta script package.json (`check`, `types:check`, `build`).
5. Ajukan pertanyaan konfirmasi dari ATURAN_PEMBARUAN.md bagian 4 dalam SATU daftar bernomor. Untuk setiap butir, tulis rekomendasi default-mu dan alasannya dalam satu kalimat.
6. BERHENTI dan tunggu jawaban saya.

ATURAN UTAMA (ringkasan; rincian di docs/ATURAN_PEMBARUAN.md)
- Satu fase per giliran, lalu berhenti dan tunggu "Lanjut".
- Jangan menebak. Kutip path:baris untuk setiap klaim tentang kode.
- Jangan menjalankan perintah destruktif, jangan membaca atau menampilkan .env, jangan menulis ADMIN_PATH/ADMIN_ALLOWED_IPS/password/token ke file atau laporan, jangan menambah dependency tanpa persetujuan.
- Jangan menyentuh storage/app/storage_pengajuan.json dan stack React/Inertia.
- Laporkan hanya file yang dibuat/diubah/dihapus, sesuai format bagian 9.
- Seluruh teks antarmuka dalam Bahasa Indonesia baku.
- Validasi front-end hanya untuk UX; keamanan sebenarnya di backend.
```

---

## PROMPT LANJUTAN (salin sesuai fase)

Ganti `N` dan nama fase. Isi jawaban konfirmasi di bagian yang disediakan.

```
Lanjut ke Fase N — <nama fase> sesuai docs/LANGKAH_PEMBARUAN.md.

Jawaban konfirmasi (nomor mengikuti daftar yang kamu ajukan):
<tulis jawabanmu, atau "gunakan rekomendasi default-mu">

Ingat:
- Tulis rencana lebih dulu: daftar file yang akan dibuat/diubah, daftar file terdampak (grep pemakai untuk file bersama), dan risiko. Tunggu persetujuan saya bila fase menyentuh database, autentikasi, atau upload file.
- Patuhi AGENTS.md dan docs/ATURAN_PEMBARUAN.md, terutama bagian 2A (anti-kerusakan), 3 (batasan keras), dan 5 (keamanan).
- Utamakan penambahan file baru; pertahankan nama route, nama view, dan variabel yang dipakai file lain. Migrasi database harus aditif.
- Tambahkan test Pest untuk setiap fitur berisiko (otorisasi, upload, rate limit, XSS).
- Di akhir: jalankan pengecekan bagian 7 dan bedakan kegagalan lama vs baru, perbarui docs/PROGRESS.md, laporkan sesuai format bagian 9 (termasuk bagian "Temuan audit yang ditangani"), lalu BERHENTI.
```

Daftar fase:

| N | Nama fase |
|---|-----------|
| 1 | Baseline Keamanan dan Kebersihan |
| 2 | Fondasi Data dan Peran |
| 3 | Homepage dan CMS Superadmin |
| 4 | Form Pengajuan Kerjasama |
| 5 | Inbox Pengajuan dan Persetujuan |
| 6 | Portal Mitra |
| 7 | MoU (Template, Generator AI, Penyuntingan) |
| 8 | Halaman Admin Kerjasama dan Notifikasi |
| 9 | Hardening dan Penutupan |

Catatan: Fase 2 (database dan peran) adalah fase paling berisiko. Pastikan database lokal dan `storage_pengajuan.json` sudah Anda cadangkan sebelum memberi persetujuan.

---

## PROMPT PERBAIKAN (jika hasil fase bermasalah)

```
Ada masalah pada hasil Fase N:
<jelaskan masalah; sertakan pesan error atau perilaku yang salah>

Perbaiki dengan perubahan sesepele mungkin dan jangan mengubah file di luar yang diperlukan. Jelaskan akar masalahnya (dengan path:baris), tampilkan hanya file yang berubah, jalankan ulang pengecekan terkait, dan perbarui docs/PROGRESS.md. Jika perbaikan butuh perubahan di file bersama, tulis analisis dampaknya dulu dan tunggu persetujuan saya.
```

## PROMPT TINJAUAN KEAMANAN (read-only, setelah fase 1, 3, 4, 5, 6, 7)

```
Lakukan tinjauan keamanan READ-ONLY atas fitur yang baru dibuat di Fase N. Jangan mengubah kode.
Periksa satu per satu: unggah file, SQL injection, XSS (Blade: {!! !!}, innerHTML, onclick berisi data, nilai di dalam <script>), BOLA/IDOR (UUID, Policy, scoping), rate limiting/CAPTCHA, autentikasi dan sesi, mass assignment, kebocoran data di log.
Untuk tiap butir tulis LOLOS / GAGAL / TIDAK RELEVAN beserta bukti (path:baris atau nama test). Untuk yang GAGAL, usulkan perbaikan tetapi jangan menerapkannya sebelum saya setuju.
```

## PROMPT PEMULIHAN (jika sesi OpenCode terputus atau konteks hilang)

```
Sesi sebelumnya terputus. Baca AGENTS.md, docs/ATURAN_PEMBARUAN.md, docs/LANGKAH_PEMBARUAN.md, dan docs/PROGRESS.md. Jalankan `git status` dan `git log --oneline -15`. Lalu laporkan: fase terakhir yang selesai, file yang berubah tetapi belum di-commit, dan fase berikutnya. Jangan mengubah apa pun; tunggu instruksi saya.
```
