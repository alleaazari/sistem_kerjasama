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
];
