<?php

use App\Support\AdminAkses;

/*
|--------------------------------------------------------------------------
| Sesi terpisah per peran
|--------------------------------------------------------------------------
| Setiap panel peran memakai cookie sesi sendiri + session.path sendiri,
| sehingga beberapa panel bisa login bersamaan di browser yang sama tanpa
| saling menimpa. Pemetaan mengikuti prefix peran (config/admin_akses.php)
| yang dibaca dari .env, bukan hardcode di middleware.
|
| Laravel menurunkan cookie XSRF dari session.path, jadi cookie CSRF ikut
| terpisah per panel secara otomatis.
|
| `null` pada `nama` berarti pakai setelan bawaan config/session.php (dipakai
| halaman publik & area mitra).
|
| TODO-BACKEND: bila peran pindah ke tabel `users`, peta ini cukup menjadi
| kolom `session_cookie`; kontrak middleware tidak berubah.
*/

$peta = [];

foreach (AdminAkses::semua() as $peran) {
    $peta[] = [
        'path' => AdminAkses::path($peran),
        'nama' => 'kerjasama_'.$peran.'_session',
    ];
}

return [

    /*
    | Cookie sesi milik tiap prefix peran. Urutan tidak penting: prefix dicocokkan
    | per segmen URL pertama, bukan sebagai awalan string.
    */
    'per_peran' => $peta,

    /*
    | Cookie sesi bawaan untuk area mitra dan halaman publik. null = pakai
    | SESSION_COOKIE / session.cookie dari config/session.php apa adanya.
    */
    'bawaan' => [
        'nama' => null,
        'path' => null,
    ],

];
