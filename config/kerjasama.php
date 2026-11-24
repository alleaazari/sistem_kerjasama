<?php

return [
    /*
    |----------------------------------------------------------------------
    | Domain email PIC yang diizinkan
    |----------------------------------------------------------------------
    | Dicocokkan PERSIS dengan domain email (setelah huruf kecil), sehingga
    | "gmail.com.evil.com" atau "evilgmail.com" ditolak. Entri yang diawali
    | titik dicocokkan sebagai akhiran domain, mis. ".ac.id" menerima
    | "pic@univ.ac.id" tetapi bukan "ac.id".
    |
    | CATATAN: .go.id belum diizinkan, konfirmasi mentor untuk instansi pemerintah
    */
    'email_domains' => [
        'gmail.com',
        'yahoo.com',
        'yahoo.co.id',
        '.ac.id',
    ],

    /*
    |----------------------------------------------------------------------
    | Bidang kerja sama yang diminati — SATU sumber
    |----------------------------------------------------------------------
    | Dipakai oleh form pengajuan, halaman mitra, validasi server, dan
    | generator MoU. config/mou.php menurunkan pemetaan bidang -> template
    | dari daftar ini (lihat kunci 'template'), jadi tidak ada duplikasi.
    |
    | Tiap item:
    |   kode             : disimpan di data & divalidasi server.
    |   label            : nama resmi untuk tampilan.
    |   deskripsi_singkat: ringkasan satu baris untuk kartu pilihan.
    |   sasaran_mitra    : target mitra + format dokumen (kartu pilihan).
    |   jenis_dokumen    : nama dokumen (mis. "Nota Kesepakatan").
    |   template         : kode template di config/mou.php / storage/app/mou.
    |   aktif            : hanya bidang aktif yang tampil & diterima server.
    |
    | Menambah bidang cukup menambah satu item; mengubah 'template' di sini
    | otomatis menyelaraskan pemetaan generator MoU.
    */
    // KONFIRMASI MENTOR: pemetaan badan kerja sama ke template
    'bidang' => [
        'GTA' => [
            'kode' => 'GTA',
            'label' => 'Government Transformation Academy (GTA) — Pemerintah Daerah',
            'deskripsi_singkat' => 'Peningkatan kapasitas SDM bidang komunikasi dan digital untuk aparatur pemerintahan, mahasiswa, dan masyarakat umum.',
            'sasaran_mitra' => 'Pemerintah Daerah (provinsi/kabupaten/kota) — format Nota Kesepakatan standar, 14 pasal.',
            'jenis_dokumen' => 'Nota Kesepakatan',
            'template' => 'nota_kesepakatan_pemda',
            'aktif' => true,
        ],
        'PEMDA_PDP' => [
            'kode' => 'PEMDA_PDP',
            'label' => 'Pemerintah Daerah — Format UU PDP',
            'deskripsi_singkat' => 'Peningkatan kapasitas SDM bidang komunikasi dan digital dengan pertukaran/pemrosesan Data Pribadi terstruktur.',
            'sasaran_mitra' => 'Pemerintah Daerah (provinsi/kabupaten/kota) yang memerlukan klausul Transfer Data dan Pelindungan Data Pribadi (UU No. 27 Tahun 2022) — format Nota Kesepakatan 15 pasal.',
            'jenis_dokumen' => 'Nota Kesepakatan',
            'template' => 'nota_kesepakatan_pemda_pdp',
            'aktif' => true,
        ],
        'VSGA' => [
            'kode' => 'VSGA',
            'label' => 'Vocational School Graduate Academy (VSGA) — Perguruan Tinggi',
            'deskripsi_singkat' => 'Penyelenggaraan program beasiswa talenta digital (Digital Talent Scholarship) bersama perguruan tinggi.',
            'sasaran_mitra' => 'Perguruan tinggi/institusi pendidikan tinggi (negeri atau swasta) — format Perjanjian Kerja Sama operasional 15 pasal + Lampiran Rencana Aksi.',
            'jenis_dokumen' => 'Perjanjian Kerja Sama',
            'template' => 'pks_beasiswa_talenta_digital',
            'aktif' => true,
        ],
    ],
];
