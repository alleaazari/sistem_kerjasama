<?php

return [
    /*
    |----------------------------------------------------------------------
    | Ambang "Segera Berakhir"
    |----------------------------------------------------------------------
    | Sisa hari 0 sampai nilai ini (inklusif) dianggap "Segera Berakhir".
    | Lebih dari nilai ini = Aktif. Tanggal berakhir yang sudah lewat =
    | Berakhir. Dipakai App\Support\StatusKerjasama.
    */
    'ambang_segera_berakhir_hari' => 90,

    /*
    |----------------------------------------------------------------------
    | Akun demo (DATA DUMMY)
    |----------------------------------------------------------------------
    | Hanya untuk peninjauan lokal. Ditampilkan di halaman login HANYA bila
    | config('app.debug') bernilai true, dan tidak pernah diisikan otomatis
    | ke dalam input. Autentikasi mitra sungguhan belum dibangun (lihat
    | "catatan untuk backend" pada laporan).
    */
    'demo_user' => 'user1',
    'demo_password' => '123456',
];
