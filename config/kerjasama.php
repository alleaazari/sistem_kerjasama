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
    |   jenis_dokumen    : nama dokumen (mis. "Nota Kesepakatan").
    |   template         : kode template di config/mou.php / storage/app/mou.
    |   aktif            : hanya bidang aktif yang tampil & diterima server.
    |
    | Menambah bidang cukup menambah satu item; mengubah 'template' di sini
    | otomatis menyelaraskan pemetaan generator MoU.
    */
    // KONFIRMASI MENTOR: pemetaan VSGA/GTA ke template
    'bidang' => [
        'GTA' => [
            'kode' => 'GTA',
            'label' => 'Government Transformation Academy (GTA)',
            'deskripsi_singkat' => 'Peningkatan kapasitas SDM bidang komunikasi dan digital untuk pemerintah daerah',
            'jenis_dokumen' => 'Nota Kesepakatan',
            'template' => 'nota_kesepakatan_pemda',
            'aktif' => true,
        ],
        'VSGA' => [
            'kode' => 'VSGA',
            'label' => 'Vocational School Graduate Academy (VSGA)',
            'deskripsi_singkat' => 'Penyelenggaraan program beasiswa talenta digital bersama perguruan tinggi',
            'jenis_dokumen' => 'Perjanjian Kerja Sama',
            'template' => 'pks_beasiswa_talenta_digital',
            'aktif' => true,
        ],
        // TODO: bidang ketiga ditentukan mentor
        'bidang_3' => [
            'kode' => 'bidang_3',
            'label' => '',
            'deskripsi_singkat' => '',
            'jenis_dokumen' => '',
            'template' => 'kerangka_umum',
            'aktif' => false,
        ],
    ],
];
