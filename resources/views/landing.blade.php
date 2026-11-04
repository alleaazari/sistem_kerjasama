@extends('layouts.user-public')
@section('title', 'Beranda Portal Kerja Sama')
@section('body_class', 'landing-page')

@section('content')
@php
    // Ambil data petinggi dari session (jika admin sudah update) atau gunakan default
    $petinggiSession = session('admin_petinggi', []);
    $petinggi = $petinggiSession ?: [
        [
            'nama' => 'Meutya Viada Hafid',
            'jabatan' => 'Menteri Komunikasi dan Digital RI',
            'badge' => 'Pimpinan Kementerian',
            'foto' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Memimpin pemetaan kebijakan dan strategi transformasi digital Indonesia, penguatan infrastruktur telekomunikasi nasional, serta pengembangan talenta digital yang inklusif.',
        ],
        [
            'nama' => 'Bonifasius Wahyu Pudjianto, Ph.D.',
            'jabatan' => 'Kepala BPSDM Komdigi Pusat',
            'badge' => 'Badan Pengembangan SDM',
            'foto' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Mengoordinasikan perumusan kebijakan teknis, penyelenggaraan pengembangan SDM komunikasi dan digital, serta beasiswa dan sertifikasi kompetensi nasional.',
        ],
        [
            'nama' => 'Dr. Christiany Juditha, S.Sos., M.A.',
            'jabatan' => 'Plt. Kepala BBLSDM Komdigi Medan',
            'badge' => 'BBLSDM Komdigi Medan',
            'foto' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Mengendalikan pelaksanaan pelatihan dan uji kompetensi SDM digital di 8 Provinsi wilayah kerja Sumatera & Kalimantan Barat berbasis nilai BerAKHLAK.',
        ],
    ];

    // Pegawai BBLSDM Komdigi Medan (contoh — ganti lewat data admin/session)
    $pegawai = session('admin_pegawai', []) ?: [
        [
            'nama' => 'Rahmat Hidayat, S.Kom., M.T.',
            'jabatan' => 'Koordinator Penyelenggaraan Pelatihan',
            'badge' => 'BBLSDM Medan',
            'foto' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Mengoordinasikan jadwal pelatihan, penugasan widyaiswara, serta evaluasi pasca pelatihan wilayah Sumatera Utara.',
            'detail' => 'Unit: Bidang Penyelenggaraan Pelatihan. Fokus: perencanaan kelas, monitoring peserta, dan pelaporan hasil pelatihan.',
        ],
        [
            'nama' => 'Siti Nurhaliza, S.E.',
            'jabatan' => 'Analis Kerja Sama dan Kemitraan',
            'badge' => 'BBLSDM Medan',
            'foto' => 'https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Menangani penjajakan kerja sama dengan pemerintah daerah, perguruan tinggi, dan industri di 8 provinsi wilayah kerja.',
            'detail' => 'Unit: Subbagian Kerja Sama. Fokus: draf nota kesepahaman, verifikasi berkas mitra, dan pendampingan penandatanganan.',
        ],
        [
            'nama' => 'Andi Prasetyo, S.Pd.',
            'jabatan' => 'Widyaiswara Ahli Muda',
            'badge' => 'BBLSDM Medan',
            'foto' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Mengampu materi literasi digital, keamanan siber, dan transformasi layanan publik berbasis SPBE.',
            'detail' => 'Unit: Kelompok Widyaiswara. Fokus: penyusunan modul, pengajaran kelas tatap muka dan daring, serta uji kompetensi.',
        ],
        [
            'nama' => 'Maria Simanjuntak, A.Md.',
            'jabatan' => 'Pengelola Layanan Peserta',
            'badge' => 'BBLSDM Medan',
            'foto' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Menjadi titik kontak peserta pelatihan: pendaftaran, akomodasi, sertifikat, dan penanganan keluhan.',
            'detail' => 'Unit: Layanan Peserta. Fokus: verifikasi data peserta, distribusi sertifikat, dan survei kepuasan layanan.',
        ],
        [
            'nama' => 'Bayu Kurniawan, S.Kom.',
            'jabatan' => 'Pranata Komputer',
            'badge' => 'BBLSDM Medan',
            'foto' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Mengelola portal kerja sama, basis data pelatihan, dan dukungan teknis kelas daring.',
            'detail' => 'Unit: Subbagian Umum dan Teknologi Informasi. Fokus: pemeliharaan aplikasi, keamanan data, dan dukungan pengguna.',
        ],
        [
            'nama' => 'Dewi Anggraini, S.H.',
            'jabatan' => 'Analis Hukum dan Kepatuhan',
            'badge' => 'BBLSDM Medan',
            'foto' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?q=80&w=1200&auto=format&fit=crop',
            'desc' => 'Menelaah draf perjanjian kerja sama, kepatuhan dokumen, dan tata kelola arsip naskah dinas.',
            'detail' => 'Unit: Subbagian Hukum dan Tata Laksana. Fokus: reviu klausul kerja sama, legal drafting, dan dokumentasi.',
        ],
    ];

    // Satu daftar untuk marquee: pimpinan dulu, lalu pegawai
    $tim = array_merge($petinggi, $pegawai);

    // Visi Misi dari session atau default
    $visi = session('admin_visi', 'Mewujudkan Indonesia Digital yang Inklusif, Berdaya Saing, Berkelanjutan, dan Mendukung Transformasi Digital Nasional Berbasis SDM Unggul.');

    $misi = [
        'Memperluas infrastruktur dan literasi digital yang inklusif.',
        'Mengembangkan kompetensi serta sertifikasi SDM digital.',
        'Membangun kolaborasi lintas instansi, kampus, dan industri.',
        'Menghadirkan layanan publik berstandar BerAKHLAK.',
    ];

    // Artikel dari session (admin artikel) atau default
    $artikelSession = session('admin_artikel', []);
    $artikel = $artikelSession ?: [
        [
            'Penandatanganan Nota Kesepahaman Transformasi Digital Daerah',
            '02 Oktober 2026',
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1200&auto=format&fit=crop',
            'Kerja sama strategis antara Kemkomdigi dan Pemerintah Daerah untuk percepatan pelayanan publik berbasis AI dan Sistem Pemerintahan Berbasis Elektronik (SPBE).'
        ],
        [
            'Kolaborasi Penguatan Literasi Digital Bersama Perguruan Tinggi',
            '24 September 2026',
            'https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=1200&auto=format&fit=crop',
            'BBLSDM Komdigi Medan menggelar program Digital Talent Scholarship (DTS) dan riset bersama untuk meningkatkan daya saing lulusan mahasiswa.'
        ],
        [
            'Tata Cara Pengajuan Kerja Sama Instansi Tahun Anggaran 2027',
            '11 September 2026',
            'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1200&auto=format&fit=crop',
            'Panduan teknis dan kriteria usulan kerja sama pelatihan digital, fasilitasi jaringan sinyal, dan sertifikasi SKKNI bagi instansi mitra.'
        ],
    ];

    // Galeri dari session (admin galeri) atau default
    $galeriSession = session('admin_galeri', []);
    $galeri = $galeriSession ?: [
        ['Rapat Koordinasi Kerja Sama Daerah', 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1200&auto=format&fit=crop'],
        ['Kunjungan Kerja & Audiensi Mitra', 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?q=80&w=1200&auto=format&fit=crop'],
        ['Pelatihan Digital Talent Scholarship', 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=1200&auto=format&fit=crop'],
        ['Fasilitasi Sertifikasi Uji Kompetensi', 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=1200&auto=format&fit=crop'],
        ['Penyerahan Sertifikat MoU Mitra', 'https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=1200&auto=format&fit=crop'],
        ['Workshop Cybersecurity & Cloud', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1200&auto=format&fit=crop'],
    ];

    $wilayah = [
        [
            'prov' => 'Sumatera Utara', 'kota' => 'Kota Medan',
            'kantor' => 'BBLSDM Komdigi Medan (Kantor Induk)',
            'alamat' => 'Jl. Tombak No. 31, Sidorejo Hilir, Kec. Medan Tembung, Kota Medan, Sumatera Utara 20222',
            'telp' => '(061) 6639817 / 6639816',
            'pusat' => true,
            'maps' => 'Balai Besar Pelatihan SDM Komunikasi dan Digital Medan, Jl. Tombak No.31, Medan',
        ],
        ['prov' => 'Aceh', 'kota' => 'Banda Aceh', 'kantor' => 'Dinas Komunikasi, Informatika dan Persandian Aceh', 'alamat' => 'Jl. Sultan Alaidin Mahmudsyah No. 14, Banda Aceh 23242', 'telp' => '(0651) 22221', 'pusat' => false, 'maps' => 'Dinas Komunikasi Informatika dan Persandian Aceh, Jl. Sultan Alaidin Mahmudsyah No.14, Banda Aceh'],
        ['prov' => 'Sumatera Barat', 'kota' => 'Kota Padang', 'kantor' => 'Dinas Komunikasi, Informatika dan Statistik Sumbar', 'alamat' => 'Jl. Pramuka Raya No. 11A, Lolong Belanti, Kec. Padang Utara, Kota Padang', 'telp' => '(0751) 89713615', 'pusat' => false, 'maps' => 'Dinas Komunikasi Informatika dan Statistik Sumatera Barat, Jl. Pramuka Raya No.11A, Padang'],
        ['prov' => 'Riau', 'kota' => 'Kota Pekanbaru', 'kantor' => 'Dinas Komunikasi, Informatika dan Statistik Riau', 'alamat' => 'Jl. Jenderal Sudirman No. 460, Pekanbaru, Riau 28156', 'telp' => '(0761) 45505', 'pusat' => false, 'maps' => 'Dinas Komunikasi Informatika dan Statistik Provinsi Riau, Jl. Jenderal Sudirman No.460, Pekanbaru'],
        ['prov' => 'Kepulauan Riau', 'kota' => 'Tanjungpinang', 'kantor' => 'Dinas Komunikasi dan Informatika Kepri', 'alamat' => 'Pusat Pemerintahan Prov. Kepri, Gedung B2 Lt. III, Pulau Dompak, Tanjungpinang', 'telp' => '(0771) 4575023', 'pusat' => false, 'maps' => 'Dinas Komunikasi dan Informatika Provinsi Kepulauan Riau, Pulau Dompak, Tanjungpinang'],
        ['prov' => 'Jambi', 'kota' => 'Kota Jambi', 'kantor' => 'Dinas Komunikasi dan Informatika Jambi', 'alamat' => 'Jl. Jend. A. Yani No. 11, Telanaipura, Kota Jambi', 'telp' => '(0741) 60425', 'pusat' => false, 'maps' => 'Dinas Komunikasi dan Informatika Provinsi Jambi, Telanaipura, Jambi'],
        ['prov' => 'Kep. Bangka Belitung', 'kota' => 'Pangkalpinang', 'kantor' => 'Dinas Komunikasi dan Informatika Babel', 'alamat' => 'Komplek Perkantoran Gubernur, Jl. Pulau Bangka, Air Itam, Pangkalpinang', 'telp' => '(0717) 4262580', 'pusat' => false, 'maps' => 'Dinas Komunikasi dan Informatika Provinsi Kepulauan Bangka Belitung, Pangkalpinang'],
        ['prov' => 'Kalimantan Barat', 'kota' => 'Pontianak', 'kantor' => 'Dinas Komunikasi dan Informatika Kalbar', 'alamat' => 'Jl. Ahmad Yani, Komplek Perkantoran Gubernur, Pontianak, Kalimantan Barat', 'telp' => '(0561) 736541', 'pusat' => false, 'maps' => 'Dinas Komunikasi dan Informatika Provinsi Kalimantan Barat, Pontianak'],
    ];

    // Data untuk widget landing (vanilla JS): hero typewriter, visi-misi, galeri depth carousel
    $widgetHeroText = [
        'Sinergi Digital Nusantara',
        'Kolaborasi Lintas Daerah',
        'Transformasi SDM Digital',
    ];
    $widgetVmItems = array_merge(
        [['label' => 'Visi', 'text' => $visi]],
        array_map(function ($m, $i) {
            return ['label' => 'Misi ' . ($i + 1), 'text' => $m];
        }, $misi, array_keys($misi))
    );
    $widgetGallery = array_map(function ($g) {
        return ['image' => $g[1], 'alt' => $g[0]];
    }, $galeri);

    $widgetData = [
        'heroText' => $widgetHeroText,
        'vmItems' => $widgetVmItems,
        'gallery' => $widgetGallery,
    ];
@endphp

<script>
document.documentElement.classList.add('has-reveal');
window.LP_WIDGETS = @json($widgetData);
</script>

<!-- HERO -->
<section class="hero-fullwidth-wrapper" aria-label="Sambutan Portal Kerja Sama Komdigi">
    <div class="hero-bg-image-layer"></div>
    <div class="hero-bg-overlay-dark"></div>

    <div class="wrap" style="position:relative;z-index:2;">
        <div class="hero-ref-grid">
            <div class="hero-ref-left">
                <span class="hero-ref-category">Portal Kerja Sama Komdigi</span>
                <h1 class="hero-ref-title" id="heroType" aria-label="Sinergi Digital Nusantara — Portal Kerja Sama Komdigi">
                    <span id="heroTypeText" aria-hidden="true">Sinergi Digital Nusantara</span><span class="tt-cursor" aria-hidden="true"></span>
                </h1>
                <p class="hero-ref-desc">
                    Satu portal untuk pengajuan kemitraan, pelatihan digital, dan sertifikasi kompetensi SDM di wilayah kerja BBLSDM Komdigi Medan.
                </p>
                <div class="hero-ref-actions">
                    <a href="{{ route('pengajuan.create') }}" class="hero-ref-btn-main">
                        Ajukan Kerja Sama &rarr;
                    </a>
                </div>
            </div>

            <div class="hero-ref-right">
                <div class="hero-ref-card">
                    <div class="hero-card-head">
                        <div>
                            <div class="hero-card-meta">Status Layanan</div>
                            <div class="hero-card-date">{{ \Illuminate\Support\Carbon::now()->locale('id')->translatedFormat('l, d F Y') }}</div>
                        </div>
                        <span class="hero-live">Aktif</span>
                    </div>

                    <div class="hero-card-divider"></div>

                    <button type="button" class="hero-card-user hero-card-user-btn" onclick="openWilayahModal()" title="Lihat lokasi layanan 8 provinsi">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=200&auto=format&fit=crop" alt="Kepala BBLSDM Komdigi" class="hero-user-avatar">
                        <div class="hero-user-text">
                            <strong>BBLSDM Komdigi Medan</strong>
                            <span>Layanan 8 provinsi</span>
                        </div>
                        <span class="hero-user-chevron" aria-hidden="true">&rsaquo;</span>
                    </button>

                    <a href="{{ route('pengajuan.create') }}" class="hero-card-cta">
                        Mulai Pengajuan
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- STATISTIK -->
<div class="stats-grid">
    <div class="stat-card" data-reveal style="--delay:0ms">
        <div class="stat-num"><span data-count="500" data-suffix="+">500+</span></div>
        <div class="stat-label">Mitra Terdaftar</div>
    </div>
    <div class="stat-card" data-reveal style="--delay:80ms">
        <div class="stat-num"><span data-count="1.2" data-decimals="1" data-suffix="M+">1.2M+</span></div>
        <div class="stat-label">Alumni Pelatihan</div>
    </div>
    <div class="stat-card" data-reveal style="--delay:160ms">
        <div class="stat-num">8</div>
        <div class="stat-label">Provinsi Layanan</div>
    </div>
    <div class="stat-card" data-reveal style="--delay:240ms">
        <div class="stat-num"><span data-count="99.8" data-decimals="1" data-suffix="%">99.8%</span></div>
        <div class="stat-label">Kepuasan Mitra</div>
    </div>
</div>

<!-- TENTANG -->
<section class="section" id="tentang" style="margin-top:64px;">
    <div class="section-head" data-reveal>
        <span class="section-kicker">Profil</span>
        <h2 class="section-title">Rumah Talenta Digital Sumatera</h2>
    </div>

    <div class="about-intro">
        <div class="about-copy" data-reveal>
            <p>
                <strong>BBLSDM Komdigi Medan</strong> adalah unit pelaksana pengembangan sumber daya manusia
                bidang komunikasi dan digital untuk wilayah Sumatera dan Kalimantan Barat. Kami mempertemukan
                instansi pemerintah, perguruan tinggi, dan industri dalam satu jalur kerja sama yang tertata.
            </p>
            <p>
                Mulai dari pengajuan kemitraan, penyelenggaraan pelatihan, hingga sertifikasi kompetensi —
                seluruh proses tercatat, terukur, dan dapat dipantau melalui portal ini.
            </p>
        </div>

        <div class="values-card" data-reveal style="--delay:80ms">
            <div class="values-head">
                <span class="card-eyebrow">Core Values</span>
                <span class="values-note">ASN BerAKHLAK</span>
            </div>
            <div class="values-flex">
                <span class="value-badge">Berorientasi Pelayanan</span>
                <span class="value-badge">Akuntabel</span>
                <span class="value-badge">Kompeten</span>
                <span class="value-badge">Harmonis</span>
                <span class="value-badge">Loyal</span>
                <span class="value-badge">Adaptif</span>
                <span class="value-badge">Kolaboratif</span>
            </div>
            <p class="wilayah-note"><b>Wilayah kerja:</b> Aceh, Sumut, Sumbar, Riau, Kepri, Jambi, Babel, Kalbar.</p>
        </div>
    </div>
</section>

<!-- PIMPINAN & PEGAWAI (marquee) -->
<section class="section" id="tim">
    <div class="section-head" data-reveal>
        <span class="section-kicker">Pimpinan &amp; Pegawai</span>
        <h2 class="section-title">Orang di Balik Layanan</h2>
        <p class="section-desc">Arahkan kursor untuk melihat nama, klik foto untuk membuka profil lengkap.</p>
    </div>

    <div class="lp-marquee" data-reveal>
        <div class="lp-marquee-track">
            {{-- Daftar diulang dua kali agar putaran ke kiri tersambung tanpa jeda --}}
            @foreach (array_merge($tim, $tim) as $p)
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
                <span class="lp-person-info">
                    <span class="lp-person-name">{{ $p['nama'] }}</span>
                    <span class="lp-person-role">{{ $p['jabatan'] }}</span>
                </span>
            </button>
            @endforeach
        </div>
    </div>
</section>

<!-- GALERI + VISI MISI -->
<section class="section" id="galeri">
    <div class="lp-galeri-split">
        <div>
            <div class="section-head" data-reveal>
                <span class="section-kicker">Galeri</span>
                <h2 class="section-title">Jejak Kegiatan</h2>
                <p class="section-desc">Geser, putar, atau ketuk foto untuk menjelajah kegiatan.</p>
            </div>

            <div class="lp-depth-wrap" data-reveal>
                <div id="depthCarousel" class="depth-carousel" aria-label="Jejak kegiatan"></div>
                <div id="depthCaption" class="lp-depth-caption" aria-live="polite"></div>
            </div>
        </div>

        <div>
            <div class="section-head" data-reveal>
                <span class="section-kicker">Arah Kebijakan</span>
                <h2 class="section-title">Visi &amp; Misi</h2>
                <p class="section-desc">Arahkan kursor atau klik poin untuk membaca penjelasannya.</p>
            </div>

            <div class="vm-widget" data-reveal>
                <nav class="line-sidebar" aria-label="Poin Visi dan Misi">
                    <ul id="vmList" class="line-sidebar__list"></ul>
                </nav>
                <div id="vmDetail" class="vm-detail" aria-live="polite"></div>
            </div>
        </div>
    </div>
</section>

<!-- BERITA -->
<section class="section" id="berita">
    <div class="section-head" data-reveal>
        <span class="section-kicker">Berita</span>
        <h2 class="section-title">Kabar Kerja Sama</h2>
    </div>

    <div class="grid3">
        @foreach ($artikel as $a)
        <article class="news-card glass" data-reveal style="--delay:{{ $loop->index * 100 }}ms">
            <div class="card-img-wrap" role="button" tabindex="0" data-src="{{ $a[2] }}"
                onclick="openGallery(this)"
                onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();openGallery(this);}">
                <img src="{{ $a[2] }}" alt="{{ $a[0] }}" loading="lazy">
            </div>
            <div class="in">
                <time class="date">{{ $a[1] }}</time>
                <h3>{{ $a[0] }}</h3>
                <p class="excerpt">{{ \Illuminate\Support\Str::limit($a[3], 92) }}</p>
                <a href="#" class="card-link">
                    Baca selengkapnya
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
            </div>
        </article>
        @endforeach
    </div>
</section>

<!-- MODAL LOKASI LAYANAN 8 PROVINSI -->
<div id="wilayah-modal" class="wilayah-modal" onclick="if(event.target===this)closeWilayahModal()">
    <div class="wilayah-modal-box" role="dialog" aria-label="Lokasi layanan 8 provinsi" onclick="event.stopPropagation()">
        <button type="button" class="wilayah-modal-close" onclick="closeWilayahModal()" aria-label="Tutup">&times;</button>
        <span class="wilayah-modal-badge">BBLSDM KOMDIGI MEDAN</span>
        <h3 class="wilayah-modal-title">Lokasi Layanan Kemitraan 8 Provinsi</h3>
        <p class="wilayah-modal-desc">Kantor induk BBLSDM Komdigi di Medan plus simpul layanan Diskominfo di tiap provinsi wilayah kerja. Pilih provinsi untuk melihat alamat akurat & peta Google Maps.</p>

        <div class="wilayah-chips">
            @foreach ($wilayah as $i => $w)
            <button type="button" id="wilayah-chip-{{ $i }}" class="wilayah-chip{{ $i === 0 ? ' active' : '' }}" onclick="selectWilayah({{ $i }})">{{ $w['prov'] }}</button>
            @endforeach
        </div>

        <div class="wilayah-detail-grid">
            <div class="wilayah-detail-info">
                <span id="wilayah-pusat-badge" class="wilayah-pusat-badge">KANTOR INDUK</span>
                <h4 id="wilayah-kantor"></h4>
                <div id="wilayah-prov" class="wilayah-prov"></div>
                <p id="wilayah-alamat" class="wilayah-alamat"></p>
                <p id="wilayah-telp" class="wilayah-telp"></p>
                <a id="wilayah-gmaps" href="#" target="_blank" rel="noopener noreferrer" class="wilayah-gmaps-link">Buka di Google Maps &rarr;</a>
            </div>
            <div class="wilayah-map-wrapper">
                <iframe id="wilayah-map" src="" width="100%" height="280" style="border:0;display:block;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Peta lokasi layanan Komdigi"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
var WILAYAH = @json($wilayah);

function wilayahEmbedSrc(q) {
    return 'https://maps.google.com/maps?q=' + encodeURIComponent(q) + '&t=&z=15&ie=UTF8&iwloc=&output=embed';
}
function wilayahLinkHref(q) {
    return 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(q);
}
function selectWilayah(i) {
    var w = WILAYAH[i];
    if (!w) return;
    for (var j = 0; j < WILAYAH.length; j++) {
        var chip = document.getElementById('wilayah-chip-' + j);
        if (chip) chip.classList.toggle('active', j === i);
    }
    document.getElementById('wilayah-kantor').textContent = w.kantor;
    document.getElementById('wilayah-prov').textContent = w.prov + ' \u2022 ' + w.kota;
    document.getElementById('wilayah-alamat').textContent = w.alamat;
    document.getElementById('wilayah-telp').textContent = 'Telp: ' + w.telp;
    document.getElementById('wilayah-map').src = wilayahEmbedSrc(w.maps);
    document.getElementById('wilayah-gmaps').href = wilayahLinkHref(w.maps);
    var badge = document.getElementById('wilayah-pusat-badge');
    if (badge) badge.style.display = w.pusat ? 'inline-block' : 'none';
}
function openWilayahModal() {
    selectWilayah(0);
    document.getElementById('wilayah-modal').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeWilayahModal() {
    document.getElementById('wilayah-modal').classList.remove('active');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeWilayahModal();
});

// Buka detail profil dari kartu (data attribute, aman untuk nama panjang)
function openProfileFromCard(el) {
    openProfileModal(el.dataset.name, el.dataset.role, el.dataset.badge, el.dataset.photo, el.dataset.desc, el.dataset.detail);
    var detail = document.getElementById('pm-detail');
    if (detail) {
        detail.style.display = (el.dataset.detail || '').trim() ? 'block' : 'none';
    }
}

// Lightbox untuk berita & galeri
function openGallery(el) {
    if (el && el.dataset.src) openLightbox(el.dataset.src);
}

// Visi & Misi kini oleh widget LineSidebar (landing-widgets.js)
// Galeri kini oleh widget DepthCarousel (landing-widgets.js)

// Reveal dua arah + hitung angka statistik
document.addEventListener('DOMContentLoaded', function () {
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var hasIO = 'IntersectionObserver' in window;
    var revealItems = document.querySelectorAll('[data-reveal]');

    if (reduceMotion || !hasIO) {
        revealItems.forEach(function (el) { el.classList.add('is-visible'); });
    } else {
        // Masuk viewport -> tampil. Keluar viewport -> disembunyikan lagi (animasi dua arah).
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                entry.target.classList.toggle('is-visible', entry.isIntersecting);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

        revealItems.forEach(function (el) { revealObserver.observe(el); });
    }

    var counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    function animateCounter(el) {
        var target = parseFloat(el.dataset.count || '0');
        var decimals = parseInt(el.dataset.decimals || '0', 10);
        var suffix = el.dataset.suffix || '';
        var duration = 1000;
        var started = null;

        function frame(now) {
            if (!started) started = now;
            var progress = Math.min((now - started) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = (target * eased).toFixed(decimals) + suffix;
            if (progress < 1) requestAnimationFrame(frame);
        }

        if (reduceMotion) {
            el.textContent = target.toFixed(decimals) + suffix;
        } else {
            requestAnimationFrame(frame);
        }
    }

    if (!hasIO) {
        counters.forEach(animateCounter);
        return;
    }

    var counterObserver = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.4 });

    counters.forEach(function (el) { counterObserver.observe(el); });
});
</script>
@endsection
