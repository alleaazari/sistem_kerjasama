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
public/assets/shared/css/tokens.css     (variabel)
public/assets/shared/css/base.css       (reset, tipografi, fokus, utilitas)
public/assets/shared/css/components.css (tombol, kartu, badge, form, tabel, modal, alert)
public/assets/shared/js/ui.js           (modal, toast, preview file, toggle menu; satu sumber)
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
- Jangan menyentuh file di luar cakupan prompt. Jangan mengubah composer.json, composer.lock, package.json, dan .env.
- Verifikasi sebelum memperbaiki: nomor baris dan klaim dari audit harus dicek ulang di kode sebelum dipercaya.
- Setelah tiap tugas, jelaskan: file apa yang diubah/dibuat, apa yang berubah dan mengapa, cara mengujinya, dan hal yang sengaja tidak disentuh.

# Keputusan proyek (EDIT sesuai kenyataan)
K1. Fitur yang tampak belum selesai (upload foto di admin/tentang, modal profil di landing): anggap belum selesai. Perbaiki hanya bug yang jelas (error JS, teks "undefined"); jangan menambah fitur.
K2. Elemen yang tidak punya backend (perpanjangan tanggal di mitra_draf, "Unduh Draf AI", "Perpanjang Masa Aktif", generator AI di user/draf): jangan tampilkan toast/alert sukses palsu. Jadikan nonaktif dengan label "Segera hadir", kecuali dikerjakan di Track B.
K3. Target browser: versi stabil terbaru dan satu versi sebelumnya dari Chrome, Edge, Firefox, Safari iOS, Chrome Android. Perangkat terkecil: lebar 360px.
K4. Starter kit React/Inertia: jangan disentuh pada tahap UI.
K5. Angka statis (statistik landing 500+/1.2M+/99.8%, "365 Hari Tersisa", progress 85%, data pimpinan hardcoded): pindahkan ke satu file config (mis. config/tampilan_demo.php) dengan komentar jelas bahwa ini data contoh, supaya mudah diganti dengan data nyata. Jangan mengarang data baru.
K6. jabatan_pic, validasi bidang, min:30, dan route perpanjangan: BUKAN bagian tahap UI (lihat Track B).
