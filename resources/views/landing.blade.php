@extends('layouts.user-public')
@section('title', 'Beranda Portal Kerja Sama')

@section('content')
@php
    $petinggi = [
        [
            'nama' => 'Meutya Viada Hafid',
            'jabatan' => 'Menteri Komunikasi dan Digital RI',
            'badge' => 'Pimpinan Kementerian',
            'foto' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=600&auto=format&fit=crop',
            'desc' => 'Memimpin pemetaan kebijakan dan strategi transformasi digital Indonesia, penguatan infrastruktur telekomunikasi nasional, serta pengembangan talenta digital yang inklusif.',
            'detail' => 'Latar Belakang & Fokus Kerja: Berfokus pada perluasan akses internet di wilayah 3T, penguatan kebersihan digital nasional, perlindungan data pribadi, serta sinergi kemitraan antara pemerintah pusat, daerah, perguruan tinggi, dan sektor swasta dalam mencetak 9 juta talenta digital Indonesia.'
        ],
        [
            'nama' => 'Bonifasius Wahyu Pudjianto, Ph.D.',
            'jabatan' => 'Kepala BPSDM Komdigi Pusat',
            'badge' => 'Badan Pengembangan SDM',
            'foto' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop',
            'desc' => 'Mengoordinasikan perumusan kebijakan teknis, penyelenggaraan pengembangan SDM komunikasi dan digital, serta beasiswa dan sertifikasi kompetensi nasional.',
            'detail' => 'Fungsi Strategis BPSDM: Mengelola program unggulan Digital Talent Scholarship (DTS), Digital Leadership Academy (DLA), beasiswa S2/S3 bidang komunikasi dan digital, serta kerja sama pelatihan terpadu bagi Aparatur Sipil Negara (ASN) dan masyarakat di seluruh Indonesia.'
        ],
        [
            'nama' => 'Dr. Christiany Juditha, S.Sos., M.A.',
            'jabatan' => 'Plt. Kepala BBLSDM Komdigi Medan',
            'badge' => 'BBLSDM Komdigi Medan',
            'foto' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=600&auto=format&fit=crop',
            'desc' => 'Mengendalikan pelaksanaan pelatihan dan uji kompetensi SDM digital di 8 Provinsi wilayah kerja Sumatera & Kalimantan Barat berbasis nilai BerAKHLAK.',
            'detail' => 'Cakupan Wilayah & Program: Membawahi 8 provinsi (Aceh, Sumut, Sumbar, Riau, Kepri, Jambi, Babel, Kalbar) dalam penyelenggaraan fasilitasi Uji Kompetensi SKKNI, Pelatihan Vocational School Graduate Academy (VSGA), Government Transformation Academy (GTA), dan Kemitraan Pemerintah Daerah.'
        ],
    ];

    $misi = [
        'Mendorong penguatan infrastruktur dan literasi digital masyarakat secara inklusif di seluruh daerah.',
        'Mengembangkan kompetensi dan sertifikasi SDM bidang komunikasi, teknologi informasi, dan digital.',
        'Membangun kolaborasi strategis dengan Pemda, Perguruan Tinggi, Industri, dan Lembaga Mitra.',
        'Menerapkan tata kelola pelayanan publik berstandar tinggi berakronim BerAKHLAK.'
    ];

    $artikel = [
        [
            'Penandatanganan Nota Kesepahaman Transformasi Digital Daerah',
            '02 Oktober 2026',
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=600&auto=format&fit=crop',
            'Kerja sama strategis antara Kemkomdigi dan Pemerintah Daerah untuk percepatan pelayanan publik berbasis AI dan Sistem Pemerintahan Berbasis Elektronik (SPBE).'
        ],
        [
            'Kolaborasi Penguatan Literasi Digital Bersama Perguruan Tinggi',
            '24 September 2026',
            'https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=600&auto=format&fit=crop',
            'BBLSDM Komdigi Medan menggelar program Digital Talent Scholarship (DTS) dan riset bersama untuk meningkatkan daya saing lulusan mahasiswa.'
        ],
        [
            'Tata Cara Pengajuan Kerja Sama Instansi Tahun Anggaran 2027',
            '11 September 2026',
            'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=600&auto=format&fit=crop',
            'Panduan teknis dan kriteria usulan kerja sama pelatihan digital, fasilitasi jaringan sinyal, dan sertifikasi SKKNI bagi instansi mitra.'
        ],
    ];

    $galeri = [
        ['Rapat Koordinasi Kerja Sama Daerah', 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=600&auto=format&fit=crop'],
        ['Kunjungan Kerja & Audiensi Mitra', 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?q=80&w=600&auto=format&fit=crop'],
        ['Pelatihan Digital Talent Scholarship', 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=600&auto=format&fit=crop'],
        ['Fasilitasi Sertifikasi Uji Kompetensi', 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=600&auto=format&fit=crop'],
        ['Penyerahan Sertifikat MoU Mitra', 'https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=600&auto=format&fit=crop'],
        ['Workshop Cybersecurity & Cloud', 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=600&auto=format&fit=crop'],
        ['Sosialisasi SPBE & Smart City', 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=600&auto=format&fit=crop'],
        ['Seminar Transformasi Digital Nasional', 'https://images.unsplash.com/photo-1475721027785-f74eccf877e2?q=80&w=600&auto=format&fit=crop'],
    ];

    // Lokasi layanan kemitraan 8 provinsi wilayah kerja BBLSDM Komdigi Medan
    // maps = kata kunci pencarian Google Maps (resmi & akurat per provinsi)
    $wilayah = [
        [
            'prov' => 'Sumatera Utara',
            'kota' => 'Kota Medan',
            'kantor' => 'BBLSDM Komdigi Medan (Kantor Induk)',
            'alamat' => 'Jl. Tombak No. 31, Sidorejo Hilir, Kec. Medan Tembung, Kota Medan, Sumatera Utara 20222',
            'telp' => '(061) 6639817 / 6639816',
            'pusat' => true,
            'maps' => 'Balai Besar Pelatihan SDM Komunikasi dan Digital Medan, Jl. Tombak No.31, Medan',
        ],
        [
            'prov' => 'Aceh',
            'kota' => 'Banda Aceh',
            'kantor' => 'Dinas Komunikasi, Informatika dan Persandian Aceh',
            'alamat' => 'Jl. Sultan Alaidin Mahmudsyah No. 14, Banda Aceh 23242',
            'telp' => '(0651) 22221',
            'pusat' => false,
            'maps' => 'Dinas Komunikasi Informatika dan Persandian Aceh, Jl. Sultan Alaidin Mahmudsyah No.14, Banda Aceh',
        ],
        [
            'prov' => 'Sumatera Barat',
            'kota' => 'Kota Padang',
            'kantor' => 'Dinas Komunikasi, Informatika dan Statistik Sumbar',
            'alamat' => 'Jl. Pramuka Raya No. 11A, Lolong Belanti, Kec. Padang Utara, Kota Padang',
            'telp' => '(0751) 89713615',
            'pusat' => false,
            'maps' => 'Dinas Komunikasi Informatika dan Statistik Sumatera Barat, Jl. Pramuka Raya No.11A, Padang',
        ],
        [
            'prov' => 'Riau',
            'kota' => 'Kota Pekanbaru',
            'kantor' => 'Dinas Komunikasi, Informatika dan Statistik Riau',
            'alamat' => 'Jl. Jenderal Sudirman No. 460, Pekanbaru, Riau 28156',
            'telp' => '(0761) 45505',
            'pusat' => false,
            'maps' => 'Dinas Komunikasi Informatika dan Statistik Provinsi Riau, Jl. Jenderal Sudirman No.460, Pekanbaru',
        ],
        [
            'prov' => 'Kepulauan Riau',
            'kota' => 'Tanjungpinang',
            'kantor' => 'Dinas Komunikasi dan Informatika Kepri',
            'alamat' => 'Pusat Pemerintahan Prov. Kepri, Gedung B2 Lt. III, Pulau Dompak, Tanjungpinang',
            'telp' => '(0771) 4575023',
            'pusat' => false,
            'maps' => 'Dinas Komunikasi dan Informatika Provinsi Kepulauan Riau, Pulau Dompak, Tanjungpinang',
        ],
        [
            'prov' => 'Jambi',
            'kota' => 'Kota Jambi',
            'kantor' => 'Dinas Komunikasi dan Informatika Jambi',
            'alamat' => 'Jl. Jend. A. Yani No. 11, Telanaipura, Kota Jambi (Komplek Perkantoran Gubernur Jambi)',
            'telp' => '(0741) 60425',
            'pusat' => false,
            'maps' => 'Dinas Komunikasi dan Informatika Provinsi Jambi, Telanaipura, Jambi',
        ],
        [
            'prov' => 'Kep. Bangka Belitung',
            'kota' => 'Pangkalpinang',
            'kantor' => 'Dinas Komunikasi dan Informatika Babel',
            'alamat' => 'Komplek Perkantoran Gubernur, Jl. Pulau Bangka, Air Itam, Pangkalpinang',
            'telp' => '(0717) 4262580',
            'pusat' => false,
            'maps' => 'Dinas Komunikasi dan Informatika Provinsi Kepulauan Bangka Belitung, Pangkalpinang',
        ],
        [
            'prov' => 'Kalimantan Barat',
            'kota' => 'Pontianak',
            'kantor' => 'Dinas Komunikasi dan Informatika Kalbar',
            'alamat' => 'Jl. Ahmad Yani, Komplek Perkantoran Gubernur, Pontianak, Kalimantan Barat',
            'telp' => '(0561) 736541',
            'pusat' => false,
            'maps' => 'Dinas Komunikasi dan Informatika Provinsi Kalimantan Barat, Pontianak',
        ],
    ];
@endphp

<!-- HERO BANNER FULLWIDTH WITH INTERACTIVE BLUR BACKGROUND -->
<div class="hero-fullwidth-wrapper reveal">
    <div class="hero-bg-image-layer"></div>
    <div class="hero-bg-overlay-dark"></div>

    <div class="wrap" style="position:relative;z-index:2;">
        <div class="hero-ref-grid">
            <!-- Left Side Content -->
            <div class="hero-ref-left">
                <div class="hero-ref-category">
                    <span style="display:inline-block;width:8px;height:8px;background:#60a5fa;border-radius:50%;"></span>
                    PORTAL RESMI KERJA SAMA STRATEGIS
                </div>
                <h1 class="hero-ref-title">Sinergi Digital Nusantara</h1>
                <p class="hero-ref-desc">
                    Portal resmi pengajuan kemitraan, kolaborasi program pelatihan digital, sertifikasi SKKNI, dan infrastruktur komunikasi Kementerian Komunikasi dan Digital RI.
                </p>
                <div class="hero-ref-actions">
                    <a href="{{ route('pengajuan.create') }}" class="hero-ref-btn-main">
                        Pengajuan Kerja Sama Baru &rarr;
                    </a>
                </div>
            </div>

            <!-- Right Side Floating Widget Card -->
            <div class="hero-ref-right">
                <div class="hero-ref-card">
                    <div class="hero-card-meta">STATUS KEMITRAAN HARI INI</div>
                    <div class="hero-card-heading">{{ date('l, d F Y') }}</div>
                    
                    <div class="hero-card-chips">
                        <span class="hero-chip active">Pelatihan Digital</span>
                        <span class="hero-chip">SKKNI</span>
                        <span class="hero-chip">Beasiswa S2/S3</span>
                    </div>

                    <button type="button" class="hero-card-user hero-card-user-btn" onclick="openWilayahModal()" title="Klik untuk melihat lokasi layanan 8 provinsi">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?q=80&w=200&auto=format&fit=crop" alt="Kepala BBLSDM Komdigi" class="hero-user-avatar">
                        <div class="hero-user-text">
                            <strong>BBLSDM Komdigi Medan</strong>
                            <span>Pelayanan Kemitraan 8 Provinsi</span>
                            <span class="hero-user-hint">Klik untuk lihat 8 lokasi layanan &rarr;</span>
                        </div>
                        <span class="hero-user-chevron">&rsaquo;</span>
                    </button>

                    <a href="{{ route('pengajuan.create') }}" class="hero-card-cta">
                        Ajukan Proposal Kerja Sama &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- STATS COUNTER -->
<div class="stats-grid reveal">
    <div class="stat-card">
        <div class="stat-num">500+</div>
        <div class="stat-label">Instansi Mitra Terdaftar</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">1.2M+</div>
        <div class="stat-label">Alumni Pelatihan Digital</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">8</div>
        <div class="stat-label">Provinsi Wilayah Kerja BBLSDM</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">99.8%</div>
        <div class="stat-label">Indeks Kepuasan Kemitraan</div>
    </div>
</div>

<!-- SECTION TENTANG: PETINGGI & VISI MISI -->
<div class="sec reveal" id="tentang">
    <div class="sec-header">
        <div class="sec-title">Profil Petinggi &amp; Struktur Komdigi</div>
        <div class="sec-subtitle">Mengenal jajaran kepemimpinan Kementerian Komunikasi dan Digital RI (Klik kartu untuk melihat foto &amp; deskripsi lengkap)</div>
    </div>

    <!-- PETINGGI CARDS (CLICK TO OPEN DETAIL MODAL) -->
    <div class="leadership-grid">
        @foreach ($petinggi as $p)
        <div class="leader-card" onclick="openProfileModal('{{ $p['nama'] }}', '{{ $p['jabatan'] }}', '{{ $p['badge'] }}', '{{ $p['foto'] }}', '{{ $p['desc'] }}', '{{ $p['detail'] }}')">
            <div class="leader-img-wrapper">
                <img src="{{ $p['foto'] }}" alt="{{ $p['nama'] }}" loading="lazy">
                <div class="leader-badge">{{ $p['badge'] }}</div>
            </div>
            <div class="leader-info">
                <div class="leader-name">{{ $p['nama'] }}</div>
                <div class="leader-role">{{ $p['jabatan'] }}</div>
                <div class="leader-desc">{{ $p['desc'] }}</div>
                <div style="margin-top:10px;font-size:12px;font-weight:600;color:var(--accent);">Lihat Profil Selengkapnya &rarr;</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- VISI MISI & CORE VALUES -->
    <div style="margin-top: 24px;" class="reveal">
        <div class="visi-misi-box">
            <div class="visi-box">
                <h3 style="font-size:18px;">Visi Kementerian Komunikasi dan Digital</h3>
                <div class="visi-text">"Mewujudkan Indonesia Digital yang Inklusif, Berdaya Saing, Berkelanjutan, dan Mendukung Transformasi Digital Nasional Berbasis SDM Unggul."</div>
            </div>

            <h4 style="margin-top:16px;margin-bottom:10px;color:var(--primary);font-size:15px;">Misi Strategis BPSDM &amp; BBLSDM Komdigi:</h4>
            <div class="misi-grid">
                @foreach ($misi as $idx => $m)
                <div class="misi-item">
                    <div class="misi-num">{{ $idx + 1 }}</div>
                    <div style="font-size:13px;color:var(--text-main);font-weight:500;">{{ $m }}</div>
                </div>
                @endforeach
            </div>

            <h4 style="margin-top:20px;margin-bottom:8px;color:var(--primary);font-size:14px;">Core Values ASN BerAKHLAK &amp; Wilayah Kerja:</h4>
            <div class="values-flex">
                <div class="value-badge">Berorientasi Pelayanan</div>
                <div class="value-badge">Akuntabel</div>
                <div class="value-badge">Kompeten</div>
                <div class="value-badge">Harmonis</div>
                <div class="value-badge">Loyal</div>
                <div class="value-badge">Adaptif</div>
                <div class="value-badge">Kolaboratif</div>
            </div>
            <p style="margin-top:12px;font-size:12px;color:var(--text-muted);background:#f1f5f9;padding:8px 12px;border-radius:4px;">
                <b>Wilayah Kerja BBLSDM Komdigi Medan:</b> Aceh, Sumatera Utara, Sumatera Barat, Riau, Kepulauan Riau, Jambi, Kepulauan Bangka Belitung, dan Kalimantan Barat.
            </p>
        </div>
    </div>
</div>

<!-- SECTION BERITA -->
<div class="sec reveal" id="berita">
    <div class="sec-header">
        <div class="sec-title">Berita &amp; Artikel Kerja Sama</div>
        <div class="sec-subtitle">Informasi terbaru mengenai program kemitraan dan transformasi digital daerah</div>
    </div>
    <div class="grid3">
        @foreach ($artikel as $i => $a)
        <div class="card">
            <div class="card-img-wrap" onclick="openLightbox('{{ $a[2] }}')">
                <img src="{{ $a[2] }}" alt="{{ $a[0] }}" loading="lazy">
            </div>
            <div class="in">
                <div class="date">{{ $a[1] }} | Komdigi News</div>
                <h3>{{ $a[0] }}</h3>
                <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px;">{{ $a[3] }}</p>
                <a href="#" class="btn btn2 btn-s">Baca Selengkapnya</a>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- SECTION GALERI -->
<div class="sec reveal" id="galeri">
    <div class="sec-header">
        <div class="sec-title">Galeri Kegiatan Komdigi</div>
        <div class="sec-subtitle">Dokumentasi foto kegiatan kerja sama, rapat koordinasi, dan pelatihan digital (Klik foto untuk memperbesar)</div>
    </div>
    <div class="grid4">
        @foreach ($galeri as $g)
        <div class="gallery-item" onclick="openLightbox('{{ $g[1] }}')">
            <img src="{{ $g[1] }}" alt="{{ $g[0] }}" loading="lazy">
            <div class="gallery-overlay">
                <span>{{ $g[0] }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- MODAL LOKASI LAYANAN 8 PROVINSI -->
<div id="wilayah-modal" class="wilayah-modal" onclick="if(event.target===this)closeWilayahModal()">
    <div class="wilayah-modal-box" role="dialog" aria-label="Lokasi layanan 8 provinsi" onclick="event.stopPropagation()">
        <button type="button" class="wilayah-modal-close" onclick="closeWilayahModal()" aria-label="Tutup">&times;</button>
        <span class="wilayah-modal-badge">BBLSDM KOMDIGI MEDAN</span>
        <h3 class="wilayah-modal-title">Lokasi Layanan Kemitraan 8 Provinsi</h3>
        <p class="wilayah-modal-desc">Kantor induk BBLSDM Komdigi di Medan plus simpul layanan Diskominfo di tiap provinsi wilayah kerja. Pilih provinsi untuk melihat alamat akurat &amp; peta Google Maps.</p>

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
    document.getElementById('wilayah-prov').textContent = w.prov + ' • ' + w.kota;
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
</script>
@endsection
