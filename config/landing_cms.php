<?php

/*
|--------------------------------------------------------------------------
| CMS Landing Page
|--------------------------------------------------------------------------
| Satu sumber data untuk konten landing page yang bisa dikelola admin.
|
| Urutan prioritas saat membaca:
|   1. Session admin (cache instan saat admin sedang mengedit)
|   2. storage/app/landing_cms.json (persisten, semua pengunjung)
|   3. Default di config ini
|
| Admin menyimpan lewat App\Support\LandingCms sehingga setiap refresh
| halaman publik langsung menampilkan data terbaru.
*/

return [
    'artikel' => [
        [
            'id' => 1,
            'judul' => 'Penandatanganan Nota Kesepahaman Transformasi Digital Daerah',
            'tanggal' => '02 Oktober 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'id' => 2,
            'judul' => 'Kolaborasi Penguatan Literasi Digital Bersama Perguruan Tinggi',
            'tanggal' => '24 September 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'id' => 3,
            'judul' => 'Tata Cara Pengajuan Kerja Sama Instansi Tahun Anggaran 2027',
            'tanggal' => '11 September 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1200&auto=format&fit=crop',
        ],
    ],

    'galeri' => [
        [
            'id' => 1,
            'judul' => 'Rapat Koordinasi Kerja Sama Daerah',
            'tanggal' => '02 Oktober 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'id' => 2,
            'judul' => 'Kunjungan Kerja & Audiensi Mitra',
            'tanggal' => '24 September 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'id' => 3,
            'judul' => 'Pelatihan Digital Talent Scholarship',
            'tanggal' => '18 September 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'id' => 4,
            'judul' => 'Fasilitasi Sertifikasi Uji Kompetensi',
            'tanggal' => '18 September 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'id' => 5,
            'judul' => 'Penyerahan Sertifikat MoU Mitra',
            'tanggal' => '18 September 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=1200&auto=format&fit=crop',
        ],
        [
            'id' => 6,
            'judul' => 'Workshop Cybersecurity & Cloud',
            'tanggal' => '18 September 2026',
            'konten' => '',
            'gambar' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?q=80&w=1200&auto=format&fit=crop',
        ],
    ],

    'petinggi' => [
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
    ],

    'pegawai' => [
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
    ],

    'visi' => 'Mewujudkan Indonesia Digital yang Inklusif, Berdaya Saing, Berkelanjutan, dan Mendukung Transformasi Digital Nasional Berbasis SDM Unggul.',

    'misi' => [
        'Memperluas infrastruktur dan literasi digital yang inklusif.',
        'Mengembangkan kompetensi serta sertifikasi SDM digital.',
        'Membangun kolaborasi lintas instansi, kampus, dan industri.',
        'Menghadirkan layanan publik berstandar BerAKHLAK.',
    ],

    'hero_texts' => [
        'Sinergi Digital Nusantara',
        'Kolaborasi Lintas Daerah',
        'Transformasi SDM Digital',
    ],
];
