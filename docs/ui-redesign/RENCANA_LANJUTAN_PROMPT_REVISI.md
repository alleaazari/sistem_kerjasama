# Rencana Lanjutan dan Prompt Revisi: Sistem Kerjasama (Laravel + Blade)

Dokumen ini menggantikan Prompt 1 sampai 9 pada dokumen sebelumnya. Prompt 0 (audit) sudah selesai. Isi dokumen ini disusun dari hasil audit OpenCode: stack-nya Laravel 13 + Blade + CSS statis, jadi tidak ada Tailwind dan tidak ada Vite/React di UI yang aktif.

---

## A. Yang paling penting dari hasil audit

1. **Akar masalah ada di CSS bersama.** Komentar yang tertutup lebih awal membuat satu aturan terbuang, dan 682 baris CSS terduplikasi di dua file. Ini perbaikan paling murah dan paling luas dampaknya, jadi dikerjakan pertama.
2. **Banyak "fitur" yang hanya tampilan.** Form `admin/tentang` tidak pernah tersimpan, perpanjangan tanggal di `mitra_draf` hanya mengubah layar, "AI" di `user/draf` cuma `setTimeout`. Di web pemerintah, tampilan seperti ini menyesatkan. Rekomendasinya: tampilkan sebagai nonaktif dengan label jelas, kecuali memang dikerjakan di Track B.
3. **Ada isu keamanan ringan yang ikut terlihat.** Kredensial demo `user1 / 123456` tampil dan terisi otomatis di halaman login, dan data dimasukkan ke atribut JavaScript inline (rawan pecah dan rawan injeksi). Keduanya ikut dirapikan di prompt yang relevan.
4. **Ada pekerjaan yang belum di-commit** (`landing.blade.php`, `tentang.blade.php`, `landing.css`, dan lainnya). Amankan dulu sebelum mulai.

Catatan: nomor baris dan jumlah yang disebut audit (misalnya baris 1324, 682 baris duplikat) belum saya verifikasi sendiri. Tiap prompt di bawah menyuruh OpenCode memeriksa dulu sebelum mengubah.

---

## B. Yang harus kamu lakukan sekarang (urut)

### Langkah 1. Amankan pekerjaan yang belum di-commit

Jalankan di terminal (PowerShell, di folder proyek):

```
git status
git diff composer.json
```

Lihat dulu kenapa `composer.json` berubah. Kalau ternyata bukan sengaja (misalnya efek install paket), jangan ikut di-commit. Lalu:

```
git switch -c ui/redesign
git add resources routes public
git commit -m "chore: simpan WIP landing dan tentang sebelum redesign UI"
```

Kalau `composer.json` memang perubahan yang disengaja, tambahkan juga ke `git add`. Jangan pernah `git add` file `.env`.

### Langkah 2. Ganti isi `AGENTS.md` dengan versi di bagian C

Taruh di akar proyek. Bagian "Keputusan proyek" di dalamnya berisi jawaban default atas 6 poin "perlu konfirmasi" dari audit. **Baca dan edit sesuai kenyataan**, terutama poin K1, K5, dan K6 yang hanya kamu atau pembimbing magangmu yang tahu jawabannya.

### Langkah 3. Jalankan prompt satu per satu

- Urutannya P1 sampai P9 di bagian D. Jangan digabung.
- Untuk prompt bertanda **[RENCANA DULU]**, OpenCode akan menulis rencana singkat dan menunggu kamu membalas "lanjut". Baca rencananya, baru izinkan.
- Setelah tiap prompt: jalankan `php artisan serve`, buka URL di bagian E pada lebar 360, 768, dan 1280, lalu commit:

```
git add -A
git commit -m "ui: <ringkasan prompt yang baru dijalankan>"
```

- Kalau hasilnya berantakan, `git reset --hard HEAD` mengembalikan ke commit terakhir (hanya sejak commit itu, jadi commit sering itu penting).
- Kalau sesi OpenCode terasa kacau atau panjang, mulai sesi baru. `AGENTS.md` menjaga aturannya tetap sama.

### Langkah 4. Tunjukkan hasil ke pembimbing magang

Setelah P5 (pengajuan) dan P9 (landing) selesai, itu titik yang pas untuk menunjukkan progres.

---

## C. Isi `AGENTS.md` (salin utuh)

```
# Konteks proyek
Aplikasi "Sistem Kerjasama" BBLSDM Komdigi Medan (web instansi pemerintah).
Stack: Laravel + Blade. UI aktif memakai CSS statis di public/assets/{admin,user}/css/.
TIDAK ada Tailwind di UI aktif. Starter kit React/Inertia/Vite di repo TIDAK dipakai: jangan disentuh, jangan dihapus.
Tujuan pekerjaan sekarang: perbaikan UI agar responsive, profesional, rapi, user friendly. Bukan penambahan fitur.

# Arah desain
- Terinspirasi pola portal layanan pemerintah (layanan di depan, bagian berjudul + "Lihat Semua", kartu seragam), tetapi bukan salinan situs mana pun.
- Identitas Komdigi: dominan biru, latar putih bersih, aksen kuning hangat dipakai hemat.
- Modern tapi tidak "template AI": tanpa gradasi ungu, glow, glassmorphism, emoji sebagai ikon, radius/bayangan berlebihan.
- Tidak kolot: tanpa marquee, slider otomatis, tabel berbingkai tebal, warna jenuh.
- Teks antarmuka bahasa Indonesia baku, ringkas, istilah konsisten.

# Design tokens (nilai awal, dicocokkan nanti dengan logo resmi)
--brand-900 #0A2540; --brand-700 #12418A; --brand-600 #1B5BBF; --brand-500 #2F7BE5;
--brand-100 #E6EFFB; --brand-50 #F3F7FD; --accent-500 #F2B705;
--bg #FFFFFF; --surface #F5F7FA; --text #1B2430; --text-muted #5B6675; --border #DDE3EC;
--success #1E8E5A; --warning #C77700; --danger #C62F3A; --info #1B5BBF;
--radius-sm 6px; --radius-md 10px; --radius-lg 14px.
Font: Plus Jakarta Sans (heading), Inter (body), fallback system-ui. Dimuat SEKALI saja (jangan <link> dan @import bersamaan).

# Arsitektur CSS/JS yang dituju
public/assets/shared/css/tokens.css    (variabel)
public/assets/shared/css/base.css      (reset, tipografi, fokus, utilitas)
public/assets/shared/css/components.css (tombol, kartu, badge, form, tabel, modal, alert)
public/assets/shared/js/ui.js          (modal, toast, preview file, toggle menu; satu sumber)
admin/css/style.css, user/css/style.css, user/css/landing.css hanya berisi gaya khusus area masing-masing.
Urutan muat: tokens -> base -> components -> file khusus area (agar override tetap bekerja).

# Aturan teknis
- Dilarang warna hardcode baru; pakai token. Dilarang !important baru dan inline style baru.
- Mobile-first. Breakpoint: 360, 640, 768, 1024, 1280. Tidak boleh ada scroll horizontal di halaman.
- Input font-size minimal 16px (cegah zoom iOS). Target sentuh minimal 44px. :focus-visible harus terlihat. Kontras minimal WCAG AA (4.5:1 untuk teks biasa). Hormati prefers-reduced-motion.
- Modal wajib: role="dialog", aria-modal="true", aria-labelledby, fokus terkunci, Esc menutup, scroll lock, tombol tutup punya aria-label.
- Jangan menyisipkan data ke argumen JavaScript inline (onclick="fn('{{ $x }}')"). Pakai atribut data-* atau Illuminate\Support\Js::from().
- Setiap halaman punya <main>, satu <h1>, dan skip link.
- Aset CSS/JS diberi cache-busting sederhana (misalnya ?v=filemtime).
- Jangan mengubah logika bisnis, validasi server, nama route/field, skema database, atau isi controller, kecuali prompt secara eksplisit mengizinkan.
- Jangan menambah dependensi besar tanpa meminta izin dulu.
- Jangan menyentuh file di luar cakupan prompt. Perubahan yang belum di-commit sebelum redesign sudah diamankan; tetap hati-hati pada landing.blade.php dan admin/tentang.blade.php.
- Verifikasi sebelum memperbaiki: nomor baris dan klaim dari audit harus dicek ulang di kode sebelum dipercaya.
- Setelah tiap tugas, jelaskan: file apa yang diubah/dibuat, apa yang berubah dan mengapa, cara mengujinya, dan hal yang sengaja tidak disentuh.

# Keputusan proyek (EDIT sesuai kenyataan)
K1. Fitur yang tampak belum selesai (upload foto di admin/tentang, modal profil di landing): anggap belum selesai. Perbaiki hanya bug yang jelas (error JS, teks "undefined"); jangan menambah fitur.
K2. Elemen yang tidak punya backend (perpanjangan tanggal di mitra_draf, "Unduh Draf AI", "Perpanjang Masa Aktif", generator AI di user/draf): jangan tampilkan toast/alert sukses palsu. Jadikan nonaktif dengan label "Segera hadir", kecuali dikerjakan di Track B.
K3. Target browser: versi stabil terbaru dan satu versi sebelumnya dari Chrome, Edge, Firefox, Safari iOS, Chrome Android. Perangkat terkecil: lebar 360px.
K4. Starter kit React/Inertia: jangan disentuh pada tahap UI.
K5. Angka statis (statistik landing 500+/1.2M+/99.8%, "365 Hari Tersisa", progress 85%, data pimpinan hardcoded): pindahkan ke satu file config (mis. config/tampilan_demo.php) dengan komentar jelas bahwa ini data contoh, supaya mudah diganti dengan data nyata. Jangan mengarang data baru.
K6. jabatan_pic, validasi bidang, min:30, dan route perpanjangan: BUKAN bagian tahap UI (lihat Track B).
```

---

## D. Prompt revisi

Semua prompt mengikuti `AGENTS.md`. Ringkasan urutan dan risikonya:

| Prompt | Isi | Risiko | Kira-kira file |
|---|---|---|---|
| P1 | Bersihkan CSS (bug komentar, duplikasi, legacy) | Sangat rendah | 3 CSS + layout yang memuat CSS |
| P2 | Tokens, tipografi, aksesibilitas dasar, font sekali, cache-busting | Rendah | 3 CSS baru + 3 layout |
| P3 | Kerangka layout publik (header, footer, main, skip link) + partial sidebar | Rendah-sedang | 4 sampai 6 |
| P4 | Komponen bersama: flash, modal, tabel responsif, form, badge, JS bersama [RENCANA DULU] | Sedang | 3 sampai 4 baru + beberapa partial |
| P5 | Halaman pengajuan [RENCANA DULU] | Sedang | 1 (+ CSS) |
| P6 | Login publik dan admin | Rendah | 2 |
| P7 | Area admin: inbox, _cms, mitra_draf, tentang [RENCANA DULU] | Sedang-tinggi | 5 sampai 6 |
| P8 | Area user: status, profile, draf | Sedang | 3 |
| P9 | Landing (tampilan baru ala portal layanan) | Sedang | 2 sampai 3 |
| P10 | QA akhir dan poles | Rendah | bervariasi |

### P1. Bersihkan CSS (murni CSS, tanpa mengubah tampilan sengaja)

```
Tugas: bersihkan CSS tanpa mengubah markup Blade.

1. Verifikasi dulu klaim audit: (a) di public/assets/user/css/style.css sekitar baris 1324 ada komentar yang tertutup lebih awal sehingga ada teks liar dan aturan .admin-app-body terbuang; (b) baris sekitar 1325-2006 di user/style.css identik dengan baris sekitar 353-1034 di admin/style.css. Buktikan dengan diff. Tampilkan hasilnya sebelum mengubah.
2. Perbaiki komentar rusak. Catat bahwa aturan .admin-app-body yang tadinya terbuang kini aktif; cek apakah ia memengaruhi halaman mana pun dan laporkan.
3. Ekstrak blok duplikat menjadi satu file: public/assets/shared/css/components.css. Hapus blok itu dari kedua file asal. PENTING: blok itu tadinya berada di AKHIR kedua file, jadi muat components.css SETELAH style.css masing-masing agar urutan cascade tetap sama. Update <link> di layout yang memuatnya (layouts/admin, layouts/user, layouts/user-public, dan layout lain bila ada).
4. Hapus selector legacy yang terbukti tidak dipakai di Blade mana pun: .topbar, .head, .nav, .side, .foot, .admin-footer. Buktikan dengan pencarian di resources/views sebelum menghapus; jika ada yang dipakai, biarkan dan laporkan.
5. Jangan menambah gaya baru, jangan mengubah nilai warna/ukuran.

Definition of done: semua halaman tampil sama seperti sebelumnya (kecuali efek dari komentar yang diperbaiki), jumlah baris CSS turun signifikan, tidak ada aturan ganda. Laporkan jumlah baris sebelum dan sesudah.
```

### P2. Tokens, tipografi, aksesibilitas dasar

```
Bangun fondasi desain.

1. Buat public/assets/shared/css/tokens.css (variabel dari AGENTS.md) dan base.css (reset ringan, tipografi dasar 16px/line-height 1.6, tautan, :focus-visible yang jelas, prefers-reduced-motion yang mematikan animasi/transisi, kelas utilitas .sr-only dan .skip-link).
2. Muat tokens -> base -> components -> file khusus area di semua layout. Tambahkan cache-busting sederhana (?v=filemtime) lewat cara yang rapi, misalnya satu partial Blade untuk tag CSS/JS.
3. Font: muat Plus Jakarta Sans dan Inter SEKALI saja. Hapus duplikasi (<link> + @import). Jika memungkinkan self-host di public/assets/shared/fonts/; jika tidak, pakai satu <link> dengan preconnect dan display=swap.
4. Ganti secara bertahap warna hardcode yang jelas berpadanan dengan token (mulai dari file shared/components.css lalu admin dan user style.css). Targetkan seluruh warna biru utama dan netral. Warna yang ragu-ragu padanannya, biarkan dan daftar di laporan; jangan memaksakan 100% pada tahap ini.
5. Perbaiki: font-size input minimal 16px; target sentuh tombol/ikon minimal 44px; warna teks yang gagal kontras (#94a3b8 dan #71859c di atas putih -> pakai --text-muted atau lebih gelap); ukuran teks hint tidak di bawah 12px.
6. Kurangi !important hanya di tempat yang aman dihapus (laporkan sisanya).

Batasan: jangan ubah markup kecuali perlu untuk memuat CSS/font.
Definition of done: mengganti --brand-600 mengubah warna tombol/tautan utama di semua area; fokus keyboard terlihat di semua elemen interaktif; tidak ada zoom otomatis iOS pada input.
```

### P3. Kerangka layout publik dan sidebar

```
Rapikan kerangka layout.

1. layouts/user-public + partials/header: header (logo + nama sistem di kiri, navigasi, tombol aksi "Ajukan Kerjasama" di kanan). Di bawah 1024px jadi tombol menu dengan panel geser. Tombol menu wajib punya aria-expanded dan aria-controls; Esc menutup; klik di luar menutup; menu aktif diberi aria-current="page". Top bar tipis opsional (latar --brand-900) berisi nama instansi singkat; sembunyikan di mobile.
2. Footer 3-4 kolom: identitas dan alamat, tautan cepat, layanan, kontak, hak cipta. Data yang belum ada pakai placeholder bertanda jelas ("[ALAMAT RESMI]"). Jangan mengarang alamat atau kontak.
3. Tambahkan skip link ("Lewati ke konten"), <main id="konten">, dan pastikan tiap halaman punya satu <h1> (jika halaman belum punya, tambahkan dengan gaya yang sesuai, boleh .sr-only bila desain tidak membutuhkannya terlihat).
4. Container lebar maksimum 1200px dengan padding 16px (mobile) sampai 32px (desktop). Hapus penumpukan padding (.wrap bersarang) di layout.
5. Duplikasi sidebar: ekstrak markup sidebar admin dan user menjadi partial (partials/sidebar-admin, partials/sidebar-user) yang dipakai kedua layout masing-masing. Sidebar menjadi drawer di bawah 1024px dengan aturan aksesibilitas yang sama seperti menu header.
6. Ganti link hardcode http://127.0.0.1:8000 di layout admin dengan url()/route()/config.
7. Gambar logo: pakai file logo yang sudah ada tanpa mengubah proporsinya.

Batasan: jangan ubah route, nama route, atau logika. Jangan menyentuh isi landing.blade.php di tahap ini selain yang dibutuhkan layout.
Definition of done: rapi di 360, 768, 1280 tanpa scroll horizontal; navigasi penuh bisa dioperasikan dengan keyboard.
```

### P4. Komponen bersama [RENCANA DULU]

```
Sebelum mengubah file, tulis rencana 5-8 baris (komponen apa, ditaruh di mana, view mana yang akan memakainya) lalu tunggu saya membalas "lanjut".

Buat komponen reusable (Blade component/partial + CSS di shared/components.css + JS di shared/js/ui.js):
1. Flash message: SATU tempat saja di layout yang merender session('ok') dan galat. Hapus banner sukses ganda di admin/inbox, admin/_cms, dan user/profile.
2. Modal: role="dialog", aria-modal="true", aria-labelledby, fokus terkunci dan dikembalikan ke pemicu, Esc menutup, klik latar menutup, scroll lock, tombol tutup dengan aria-label. API JS tunggal (bukaModal/tutupModal) menggantikan fungsi buka/tutup yang diduplikasi per view. Data dikirim lewat atribut data-* atau Js::from(), tidak lewat argumen inline.
3. Toast: satu implementasi (gantikan showAdminToast yang diduplikasi).
4. previewFile: satu implementasi di ui.js (sekarang hanya ada di _cms dan dipanggil dari tentang).
5. Tabel responsif: kelas/komponen yang di bawah 768px mengubah baris menjadi kartu berlabel (atribut data-label pada td), di atas itu tabel bersih (thead/tbody, header terang, garis tipis, hover lembut). Sertakan EmptyState.
6. Badge status (draf, diproses, disetujui, ditolak, selesai) dengan warna lembut tetapi lolos kontras.
7. Field form: label di atas, teks bantuan, pesan galat dengan aria-invalid dan aria-describedby, tanda wajib konsisten. Checkbox/radio kustom harus tetap bisa difokus keyboard (sembunyikan secara visual, jangan display:none) dan punya indikator fokus.
8. Tombol: primer, sekunder, ghost, ukuran sm/md/lg, state disabled/loading.

Aturan visual: radius 6-14px, bayangan tipis, transisi 150-200ms hanya hover/fokus, ikon garis dari satu set saja.
Jangan memigrasi semua halaman sekarang; migrasi dilakukan di prompt halaman masing-masing. Tambahkan satu contoh pemakaian kecil untuk tiap komponen di komentar dokumentasi.
Jangan menambah route baru.
```

### P5. Halaman pengajuan [RENCANA DULU]

```
Tulis rencana singkat dulu, tunggu "lanjut". Lalu rapikan resources/views/pengajuan.blade.php (1.617 baris; pecah bagian yang wajar menjadi partial agar bisa dirawat). Tidak boleh mengubah isi data yang dikirim ke server atau aturan validasi server.

Masalah yang harus ditangani (verifikasi dulu di kode):
1. Jarak atas ~182px akibat .wrap-main-inner (92px) + .pjn-hero margin 90px: sisakan jarak wajar saja (sekitar 24-48px di bawah header).
2. Wizard 3 langkah palsu: jadikan wizard sungguhan (hanya satu langkah tampil pada satu waktu, indikator menandai selesai/aktif/belum, tombol Kembali/Lanjut, validasi per langkah, isian tidak hilang saat berpindah langkah). Jika field sedikit dan wizard tidak diperlukan, buang indikator langkah dan buat form satu halaman bergrup. Pilih salah satu dan jelaskan alasannya.
3. Dropdown hasil pencarian PDDIKTI: pindahkan ke dalam wrapper position: relative agar posisinya tepat di bawah input; mendukung navigasi panah/Enter/Esc; kontras dan ukuran teks layak.
4. Kode dropzone/showFileName/file-surat yang tidak punya field: JANGAN dihidupkan. Hapus kode mati tersebut jika terbukti tidak terhubung ke apa pun; jika ragu, laporkan.
5. Checkbox/radio kustom: tampilkan indikator fokus, bisa dioperasikan keyboard.
6. Hint 11px #94a3b8: naikkan ukuran dan kontrasnya sesuai token.
7. Tambahkan <h1>, gunakan field komponen dari P4 (label, bantuan, galat dengan aria-invalid/aria-describedby), input 16px, target sentuh 44px.
8. Tombol kirim dengan state loading, mencegah kirim ganda; tampilkan galat server di dekat field dan pertahankan isian (old()).
9. Jangan memperbaiki ketidaksinkronan validasi (jabatan_pic, bidang, min:30) di sini; itu Track B. Namun cantumkan di laporan field mana yang tampil "wajib" di UI tetapi tidak diwajibkan server.

Definition of done: bisa diisi tuntas dan dikirim dari lebar 360 dengan keyboard virtual; tidak ada scroll horizontal; Lighthouse Accessibility >= 90.
```

### P6. Login publik dan login admin

```
Rapikan resources/views/login.blade.php dan resources/views/admin/login.blade.php.

1. Kredensial demo (user1 / 123456) jangan tampil dan jangan terisi otomatis di lingkungan non-debug. Tampilkan hanya jika config('app.debug') true, dan jangan prefilled; jika perlu bantuan demo, tampilkan sebagai teks kecil terpisah, bukan nilai input.
2. Tambahkan autocomplete yang benar (username, current-password), label terhubung, tombol tampilkan/sembunyikan password yang aksesibel, state galat dengan aria-live/aria-describedby, state loading pada tombol.
3. Hilangkan .wrap bersarang; tambahkan <h1>; kartu login di tengah dengan lebar maksimum ~420px, rapi di 360px.
4. Gunakan komponen dari P4.

Batasan: jangan ubah logika autentikasi atau nama field.
```

### P7. Area admin [RENCANA DULU]

```
Tulis rencana dulu, tunggu "lanjut". Cakupan: admin/inbox, admin/_cms, admin/mitra_draf, admin/tentang. Ikuti keputusan K1, K2, K5 di AGENTS.md.

Untuk SEMUA view tadi:
- Ganti tabel min-width:600px dengan komponen tabel responsif P4 (thead/tbody, kartu di <768px, EmptyState).
- Ganti modal ad-hoc dengan komponen modal P4. Hapus argumen JS inline: bukaModal({{ json_encode($row) }}), openExtendModal(id, '{{ $r['instansi'] }}', ...), bukaEdit(addslashes) -> pakai data-* atau Js::from() sehingga apostrof, kutip, dan newline tidak memecah skrip.
- Hapus banner sukses ganda; kurangi inline style (inbox punya sekitar 69) dengan memindahkannya ke kelas.
- Pada error validasi server, pertahankan input pengguna (old()) dan buka kembali modal yang relevan jika memungkinkan tanpa mengubah controller; jika butuh perubahan controller, laporkan saja.

Per halaman:
- admin/inbox: perbaiki struktur tabel dan empty state; tombol approve/reject jelas dan butuh konfirmasi (modal konfirmasi), bukan langsung.
- admin/mitra_draf: sesuai K2, "Perpanjang" dan "Unduh Draf AI" dijadikan nonaktif berlabel "Segera hadir"; hapus toast sukses palsu dan alert().
- admin/tentang: (a) form saveProfilTentang saat ini memanggil preventDefault dan hanya menampilkan toast, sehingga data tidak tersimpan. Verifikasi dulu apakah route admin.tentang.update benar-benar ada dan bagaimana ia menyimpan. Jika route-nya ada dan berfungsi, hapus preventDefault agar form terkirim; jika tidak jelas, JANGAN mengubah perilaku, laporkan dengan label "perlu konfirmasi". (b) previewFile sekarang dari ui.js, hubungkan preview kartu baru ke input filenya jika bisa dilakukan murni di sisi tampilan. (c) data pimpinan hardcoded: jangan diubah sumbernya (K5/K6), tandai dengan komentar. (d) hapus inline grid-template-columns yang mengalahkan breakpoint; pakai kelas grid responsif.

Batasan: tidak mengubah route, controller, nama field, atau struktur session. Jangan menyentuh perubahan WIP lain selain yang dibutuhkan di file-file di atas.
Definition of done: tidak ada scroll horizontal di 360; semua modal lolos aturan aksesibilitas; tidak ada lagi alert()/toast yang berpura-pura sukses.
```

### P8. Area user

```
Rapikan resources/views/user/status.blade.php, user/profile.blade.php, user/draf.blade.php dan layouts/user.

1. Hapus inline grid-template-columns yang mengalahkan breakpoint (.grid3 + 1fr 1fr); pakai kelas grid responsif satu kolom di mobile.
2. user/status: tabel pakai komponen responsif P4 (kartu di mobile atau kolom kunci sticky). Angka hardcoded ("365 Hari Tersisa", width:85%) dipindah ke config sesuai K5 dengan komentar "data contoh". Tombol "Perpanjang Masa Aktif" sesuai K2: nonaktif berlabel "Segera hadir", bukan alert().
3. user/profile: hapus banner sukses ganda; form pakai komponen field.
4. user/draf: generator "AI" hanyalah setTimeout 2,5 detik. Sesuai K2, tampilkan jelas sebagai prototipe (label "Prototipe" atau nonaktif); jangan menyajikannya seolah AI sungguhan. Perbaiki: editor contenteditable diberi label dan role="textbox" aria-multiline="true" bila tetap dipakai; cegah generate saat target/tujuan kosong dengan pesan galat; definisikan kelas .hide atau ganti dengan atribut hidden; jangan menambah fitur baru.
5. Gunakan <h1>, <main>, dan semua aturan di AGENTS.md.

Batasan: tidak mengubah route/controller/nama field.
```

### P9. Landing (tampilan portal layanan)

```
Rancang ulang tampilan resources/views/landing.blade.php (+ public/assets/user/css/landing.css). Ini file yang sebelumnya berisi pekerjaan WIP; baca seluruhnya lebih dulu dan petakan bagian yang SUDAH ADA ke susunan di bawah. Jangan mengarang konten baru; bagian yang tidak punya konten nyata cukup dilewati atau diberi placeholder bertanda.

Susunan yang dituju (pola portal layanan, bukan salinan):
1. Hero ringkas (tidak setinggi layar): judul fungsional (mis. "Ajukan dan pantau kerjasama dengan BBLSDM Komdigi Medan"), satu kalimat penjelas, tombol primer "Ajukan Kerjasama" dan sekunder "Cek Status". Latar --brand-50 atau foto dengan lapisan biru tua tipis.
2. Baris statistik 3-4 angka. Nilainya dari config (K5), bukan hardcode di view.
3. Layanan/jalur kerjasama: grid kartu seragam (4/2/1 kolom), isi: ikon, judul, satu kalimat, panah.
4. Alur kerjasama 4 langkah (horizontal di desktop, vertikal di mobile), hanya jika alurnya memang ada di konten/pengajuan; jangan mengarang langkah.
5. Kabar/mitra/dokumentasi: kartu seragam, tiap bagian punya judul, satu kalimat, tautan "Lihat Semua" di kanan judul.
Jarak antar bagian konsisten, latar selang-seling putih dan --brand-50.

Perbaikan khusus dari audit:
- "Baca selengkapnya" yang href="#": jadikan tombol yang membuka modal profil (komponen modal P4) atau tautan nyata.
- Modal profil menampilkan "undefined" karena openProfileFromCard membaca dataset.detail yang tidak ada: tambahkan data-detail pada kartu atau ubah pembacanya; sertakan teks cadangan bila kosong.
- Gambar Unsplash: beri width/height, alt, loading="lazy", dan fallback warna latar; catat di laporan bila sebaiknya diganti foto kegiatan sungguhan (dan jangan mengunduh atau menyimpan gambar baru).
- Teks tanggal 10,5px warna #71859c: naikkan ukuran dan kontras.
- prefers-reduced-motion tetap dihormati.

Batasan: jangan mengubah route atau data dari controller. Tanpa teks pengisi generik. Tidak ada gradasi mencolok, glow, atau emoji sebagai ikon.
Definition of done: rapi di 360/768/1280; Lighthouse Accessibility >= 90 dan tanpa pergeseran layout besar (CLS) dari gambar.
```

### P10. QA akhir dan poles

```
Periksa seluruh halaman di URL berikut pada lebar 360, 640, 768, 1024, 1280: landing, pengajuan, login mitra, user/status, user/profile, user/draf, admin login, admin dashboard, inbox, CMS, mitra draf, tentang.

Periksa: tidak ada scroll horizontal atau teks terpotong; navigasi keyboard (Tab logis, fokus terlihat, modal/drawer mengunci fokus, Esc menutup); kontras AA; setiap input punya label; satu h1 per halaman; <main> dan skip link ada; gambar punya alt; tidak ada alert()/toast palsu; tidak ada warna hardcode di luar tokens.css; tidak ada @import font ganda.
Jalankan php artisan route:list untuk memastikan jumlah route sama seperti sebelum redesign (30), dan jalankan pengecekan sintaks Blade (mis. php artisan view:cache lalu view:clear).
Perbaiki yang berisiko rendah; yang berisiko tinggi cukup dilaporkan.
Perbarui README: cara menjalankan, struktur CSS/JS (shared/ dan area), cara mengganti warna brand di tokens.css, daftar komponen.
Output: tabel temuan (halaman, masalah, tingkat, status) dan daftar hal yang masih perlu data/keputusan manual (logo resmi, alamat resmi, foto kegiatan, sumber data statistik).
```

---

## E. URL untuk diuji setelah tiap prompt

Jalankan `php artisan serve`, lalu buka (sesuaikan dengan route sebenarnya):

- `http://localhost:8000/` (landing)
- `/pengajuan`
- halaman login mitra
- halaman user: status, profil, draf (setelah login)
- `/admin-mitrakerjasama` (login admin), lalu dashboard, inbox, CMS, mitra draf, tentang

Cek dengan DevTools: tombol perangkat (Ctrl+Shift+M) pada lebar 360, 768, 1280.

---

## F. Track B (terpisah dari UI, kerjakan hanya setelah ada keputusan)

Ini menyentuh controller/route/database, jadi dipisah supaya tahap UI tetap aman. Jalankan di branch lain (`feat/sinkron-validasi`).

```
Konteks: Track B, BUKAN perbaikan UI. Boleh mengubah routes/web.php, routes/admin.php, controller/form request, dan menambah test, tetapi tanpa migrasi database kecuali saya setujui.

Kerjakan hanya poin yang saya centang:
[ ] Validasi server dan UI sinkron: bidang wajib diperiksa server; min:30 sesuai teks hint; jabatan_pic divalidasi dan disimpan (jika butuh kolom baru, usulkan migrasinya dulu dan tunggu persetujuan).
[ ] Route POST untuk perpanjangan tanggal di mitra_draf (bagaimana datanya disimpan? usulkan dulu).
[ ] admin/tentang menyimpan data pimpinan dari sumber yang disepakati (session atau DB), bukan hardcode.
[ ] Ganti data statis (statistik, masa aktif) dengan data nyata dari sumber yang saya tentukan.

Untuk tiap poin: tulis rencana dan tunggu persetujuan, tambahkan feature test untuk aturan validasi, dan jelaskan perubahan.
```

---

## G. Checklist akhir

- [ ] Tampil rapi di 360px, 768px, dan 1280px
- [ ] Logo resmi Komdigi dipakai tanpa diubah
- [ ] Alamat, kontak, dan nama instansi di footer sudah data resmi
- [ ] Tidak ada kredensial demo tampil di mode produksi
- [ ] Tidak ada tombol/toast yang berpura-pura berhasil
- [ ] Semua modal bisa ditutup dengan Esc dan dioperasikan keyboard
- [ ] Warna brand hanya didefinisikan di `tokens.css`
- [ ] Jumlah route tetap 30 dan semua fitur lama tetap jalan
- [ ] Semua perubahan ter-commit per tahap di branch `ui/redesign`
