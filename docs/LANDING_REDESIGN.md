# Redesign Landing Page — Navy + Cream, Marquee Tim, Galeri + Visi/Misi

Status: **sudah diterapkan** di repo (branch `feat/tukar-draf-generate`).
Cakupan: **hanya halaman beranda**. Navbar (`partials/header.blade.php`), footer
(`partials/footer-komdigi.blade.php`), layout `layouts/user-public.blade.php`, dan halaman
lain **tidak diubah**.

---

## 1. File yang berubah

| Status | Path | Isi |
|---|---|---|
| DIUBAH | `resources/views/landing.blade.php` | Struktur section baru, data pegawai, marquee, galeri, visi/misi, JS reveal dua arah |
| DIUBAH | `public/assets/user/css/landing.css` | Tema navy + cream, marquee, masonry galeri, accordion visi/misi, responsive |

Tidak ada file dihapus. Tidak ada dependency baru.

---

## 2. Palet

Didefinisikan sebagai variabel lokal di `body.landing-page` (tidak menyentuh `tokens.css`,
jadi area admin/mitra tidak terpengaruh):

```css
body.landing-page {
  --lp-navy: #0B1D33;
  --lp-navy-700: #12283F;
  --lp-navy-500: #1E3E5E;
  --lp-cream: #F7F2E8;
  --lp-cream-2: #EFE7D8;
  --lp-ink: #0B1D33;
  --lp-muted: #5C6B7A;
  --lp-line: rgba(11, 29, 51, 0.12);
  --lp-line-strong: rgba(11, 29, 51, 0.22);
  background: var(--lp-cream);
}
```

Hero memakai navy; seluruh body memakai cream. Foto ditampilkan grayscale, berwarna saat hover —
kontras terjaga tanpa banyak warna aksen.

---

## 3. Urutan section (atas → bawah, sebelum footer)

1. **Hero** — latar navy, judul besar, 1 CTA "Ajukan Kerja Sama", kartu status layanan.
2. **Statistik** — 4 angka, dipisah garis vertikal tipis (bukan kartu glass).
3. **Tentang** — teks profil kiri, kartu Core Values BerAKHLAK + wilayah kerja kanan.
4. **Pimpinan & Pegawai** (pengganti "Property Catalog") — marquee ke kiri, hover nama, klik → popup profil.
5. **Galeri + Visi/Misi** (pengganti "How We Work") — masonry kegiatan kiri, accordion visi/misi kanan.
6. **Berita** — 3 kartu.
7. Footer lama tetap (di-include layout, tidak disentuh).

Navbar lama tetap, logo `logo-komdigi.jpg` tetap.

---

## 4. Marquee Pimpinan & Pegawai

### Data
`$tim = array_merge($petinggi, $pegawai)`.
`$petinggi` tetap dibaca dari `session('admin_petinggi')` (CMS Tentang). `$pegawai` dibaca dari
`session('admin_pegawai')` dengan 6 data contoh sebagai fallback — jadi admin bisa menambah
karyawan tanpa mengubah view.

### Markup
```blade
<div class="lp-marquee" data-reveal>
  <div class="lp-marquee-track">
    @foreach (array_merge($tim, $tim) as $p)   {{-- diulang 2x: translateX(-50%) menyambung mulus --}}
      <button type="button" class="lp-person"
        data-name="{{ $p['nama'] }}"
        data-role="{{ $p['jabatan'] }}"
        data-badge="{{ $p['badge'] }}"
        data-photo="{{ $p['foto'] }}"
        data-desc="{{ $p['desc'] }}"
        data-detail="{{ $p['detail'] ?? '' }}"
        onclick="openProfileFromCard(this)"
        aria-label="Buka profil {{ $p['nama'] }}">
        <img src="{{ $p['foto'] }}" alt="{{ $p['nama'] }}" loading="lazy">
        <span class="lp-person-name">{{ $p['nama'] }}</span>
      </button>
    @endforeach
  </div>
</div>
```

### Animasi (lambat, ke kiri, ulang terus)
```css
.lp-marquee-track {
  display: flex;
  width: max-content;
  animation: lpMarquee 70s linear infinite;
}
.lp-marquee:hover .lp-marquee-track,
.lp-marquee:focus-within .lp-marquee-track { animation-play-state: paused; }

@keyframes lpMarquee {
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}
```
Jarak antar kartu memakai `margin-right: 18px` (bukan `gap`) supaya `-50%` tepat di titik sambung.
`prefers-reduced-motion` mematikan animasi dan menumpuk kartu.

### Hover & klik
- Hover: nama muncul dari gradasi bawah kartu + foto berubah warna.
- Klik: memanggil `openProfileFromCard(this)` yang sudah ada di layout → modal `#profile-modal`
  menampilkan nama, jabatan, badge, foto, deskripsi, dan detail.
- Kartu berupa `<button>` sehingga bisa diakses keyboard (Tab + Enter).

---

## 5. Galeri + Visi/Misi

### Galeri (masonry)
```blade
<div class="lp-masonry">
  @foreach ($galeri as $i => $g)
  <div class="gallery-item glass{{ $i === 0 ? ' tall' : ($i === 3 ? ' wide' : '') }}"
       role="button" tabindex="0" data-src="{{ $g[1] }}" data-reveal style="--delay:{{ $i * 70 }}ms"
       onclick="openGallery(this)"
       onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openGallery(this);}">
    <img src="{{ $g[1] }}" alt="{{ $g[0] }}" loading="lazy">
    <div class="gallery-overlay"><span>{{ $g[0] }}</span></div>
  </div>
  @endforeach
</div>
```
Hover menampilkan nama kegiatan (`gallery-overlay`), klik membuka lightbox yang sudah ada.

### Visi/Misi (judul saja, isi terbuka saat discroll)
```blade
<div class="lp-vm-item" data-vm>
  <button type="button" class="lp-vm-title" aria-expanded="false" onclick="toggleVm(this)">
    <span>Visi</span><span class="lp-vm-mark" aria-hidden="true"></span>
  </button>
  <div class="lp-vm-body"><p>{{ $visi }}</p></div>
</div>
```
```css
.lp-vm-body { max-height: 0; opacity: 0; overflow: hidden;
  transition: max-height .6s cubic-bezier(.16,1,.3,1), opacity .5s ease, margin-top .5s ease; }
.lp-vm-item.is-open .lp-vm-body { max-height: 340px; opacity: 1; margin-top: 12px; }
```

Pengamat mengamati **tombol judul** (tinggi tetap), bukan seluruh item, supaya panel tidak
berkedip saat terbuka lalu menambah tinggi elemen:

```js
var vmObserver = new IntersectionObserver(function (entries) {
  entries.forEach(function (entry) {
    var item = entry.target.closest('.lp-vm-item');
    if (!item || item.dataset.manual === '1') return;
    setVm(item, entry.isIntersecting);
  });
}, { threshold: 0.6 });

vmItems.forEach(function (el) {
  var btn = el.querySelector('.lp-vm-title');
  if (btn) vmObserver.observe(btn);
});
```
Klik manual menandai `data-manual="1"` agar observer berhenti mengatur item tersebut.

---

## 6. Animasi scroll dua arah

Berbeda dari versi lama yang `unobserve` setelah tampil, sekarang kelas `is-visible`
di-toggle terus-menerus: masuk viewport → muncul, keluar viewport → memudar lagi.

```js
var revealObserver = new IntersectionObserver(function (entries) {
  entries.forEach(function (entry) {
    entry.target.classList.toggle('is-visible', entry.isIntersecting);
  });
}, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
revealItems.forEach(function (el) { revealObserver.observe(el); });
```

Aturan `prefers-reduced-motion` memaksa semua elemen langsung tampil.

---

## 7. Verifikasi

- `php artisan view:clear` + `php artisan view:cache` → sukses (Blade valid).
- Render view tanpa HTTP: `view('landing')->render()` → 62.565 byte, memuat
  `lp-marquee-track`, `lp-vm-item`, `lp-masonry`, `openProfileFromCard`, `komdigi-footer`,
  dan `logo-komdigi.jpg`. Kartu orang = 18 (9 orang × 2 untuk loop).
- `landing.css`: 134 blok `{` / 134 `}` (seimbang).
- Tidak ada perubahan pada footer, navbar, layout, atau halaman lain.
- Belum dijalankan: `npm run build` (tidak relevan — landing memakai CSS statis di `public/`),
  `php artisan test` (tidak ada test yang menyentuh view landing).

---

## 8. Revisi tampilan (putaran 2)

Perubahan pada putaran ini, semuanya di dua file yang sama:

1. **Kartu orang lebih rapi** — nama + jabatan dalam satu blok `.lp-person-info`.
   Nama selalu tampil (tidak lagi disembunyikan), jabatan muncul saat hover/fokus.
   Tinggi kartu pakai `aspect-ratio: 4 / 5` + `object-position: center 22%` supaya wajah
   tidak terpotong dan ukuran tetap proporsional di semua layar.
2. **Marquee melebar penuh** — `.lp-marquee` memakai `width: 100vw` +
   `margin-left: calc(50% - 50vw)` sehingga deretan foto mengalir sampai tepi layar,
   tidak lagi menumpuk di kolom tengah.
3. **Galeri memuat semua foto utuh** — `.lp-masonry` diganti dari CSS Grid ke
   CSS multi-column (`columns: 2`), `break-inside: avoid`, `img { height: auto }`.
   Semua resolusi/rasio foto tampil tanpa terpotong; 2 kolom di laptop & tablet,
   1 kolom di HP.
4. **Visi/Misi terjabar saat discroll** — satu `IntersectionObserver` mengamati
   kontainer `.lp-vm`; begitu blok masuk layar, `setVmAll(true)` membuka semua poin
   berurutan (jeda 70 ms), dan `setVmAll(false)` menutupnya lagi saat keluar layar.
   Animasi tinggi memakai `grid-template-rows: 0fr → 1fr` (bukan `max-height`)
   agar tidak ada batas tinggi yang terpotong.
5. **Popup profil responsif** — di layar ≥768px foto mengisi 44% lebar kartu
   (`object-fit: cover`), di HP kartu menumpuk vertikal dengan foto 240px.
6. **Skala lebar** — `.wrap` 1200px + `padding: 0 clamp(16px, 3vw, 40px)`,
   baris galeri dan kartu memakai `minmax(0, 1fr)` supaya tidak ada kolom yang
   meluber keluar layar.

Verifikasi: `view('landing')->render()` 65.785 byte; `GET /` via `php artisan serve`
→ 200, 73.128 byte. `landing.css` 154 `{` / 154 `}` seimbang.

## 9. Yang perlu dikonfirmasi

1. **Data pegawai** saat ini contoh (6 orang). Perlu jalur CMS: kolom `admin_pegawai`
   (`nama`, `jabatan`, `badge`, `foto`, `desc`, `detail`) di halaman admin Tentang,
   atau sumber database saat Fase 2 selesai.
2. **Foto** masih memakai URL Unsplash. Ganti dengan foto asli BBLSDM (simpan di
   `public/assets/user/images/tim/` atau disk privat + controller unduhan sesuai aturan keamanan).
3. **Durasi marquee** 70 detik per putaran (55 detik di layar kecil). Bisa dipercepat/diperlambat
   lewat `animation: lpMarquee 70s linear infinite`.
4. **Ambang buka visi/misi** `threshold: 0.6` — jika terasa terlalu cepat/lambat, ubah angka itu.
