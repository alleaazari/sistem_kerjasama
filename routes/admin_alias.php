<?php

use App\Support\AdminAkses;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pengalihan alamat lama /{ADMIN_PATH}/* -> /superadmin/*
|--------------------------------------------------------------------------
| Bookmark dan tautan lama tetap jalan. Hanya GET; aksi tulis (POST/PUT)
| tidak dialihkan agar tidak ada perubahan data lewat URL usang.
*/

$pathLama = AdminAkses::pathLama();
$pathBaru = AdminAkses::path('superadmin');

if ($pathLama === '' || $pathLama === $pathBaru) {
    return;
}

foreach (AdminAkses::alias() as $suffix => $tujuan) {
    $urlLama = '/'.$pathLama.($suffix === '' ? '' : '/'.$suffix);
    $urlBaru = '/'.$pathBaru.'/'.$tujuan;

    Route::redirect($urlLama, $urlBaru);
}
