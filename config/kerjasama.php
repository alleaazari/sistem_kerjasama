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
    | Bidang kerja sama yang diminati
    |----------------------------------------------------------------------
    | Kunci = kode yang disimpan di data & divalidasi server.
    | Nilai = label resmi yang tampil di form, halaman mitra, dan draf MoU.
    | Menambah bidang cukup menambah satu baris di sini.
    */
    'bidang' => [
        'VSGA' => 'Vocational School Graduate Academy (VSGA)',
        'GTA' => 'Government Transformation Academy (GTA)',
        // TODO: bidang ketiga ditentukan mentor
    ],
];
