<?php

use App\Http\Controllers\AdminInboxController;
use App\Http\Controllers\AdminMitraController;
use App\Support\AdminAkses;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman kerja sama (dipakai kedua peran)
|--------------------------------------------------------------------------
| Dimuat di dalam grup prefix + nama route SETIAP peran. Isinya tidak
| diduplikasi per peran: satu controller, satu berkas route. Middleware tiap
| route dibuat dari peta izin config/admin_akses.php, sehingga peran yang
| tidak punya kemampuan/aksi itu otomatis 404 — bukan sekadar menu disembunyikan.
|
| Nama route mengikuti awalan peran di grupnya (superadmin.inbox,
| kerjasama.inbox, ...).
|
| TODO-BACKEND: data masih di berkas JSON (App\Support\PengajuanMasuk,
| App\Support\DrafMitra, App\Support\MasaAktifMitra), belum tabel database.
*/

/** Middleware untuk satu kemampuan/aksi menurut config/admin_akses.php. */
$hanya = static fn (string $kemampuan): string => 'admin.only:'.implode(',', AdminAkses::peranUntuk($kemampuan));

// ---------------- INBOX PENGAJUAN ----------------
Route::middleware($hanya('inbox'))->group(function () {
    Route::get('/inbox', [AdminInboxController::class, 'indeks'])->name('inbox');
});

// Lihat data pengajuan dan setujui/tolak dipisah: Admin Kerja Sama hanya
// boleh melihat, keputusan tetap milik Superadmin.
Route::middleware($hanya('inbox.approve'))->group(function () {
    Route::post('/inbox/{id}/approve', [AdminInboxController::class, 'setujui'])->name('inbox.approve');
});

Route::middleware($hanya('inbox.reject'))->group(function () {
    Route::post('/inbox/{id}/reject', [AdminInboxController::class, 'tolak'])->name('inbox.reject');
});

// ---------------- DRAF PROPOSAL AI ----------------
Route::middleware($hanya('draf'))->group(function () {
    Route::get('/draf-proposal', [AdminMitraController::class, 'draf'])->name('draf_proposal');
});

Route::middleware($hanya('draf_proposal.unduh'))->group(function () {
    Route::get('/draf-proposal/{uuid}/unduh', [AdminMitraController::class, 'unduhDraf'])
        ->whereUuid('uuid')
        ->name('draf_proposal.unduh');
});

// ---------------- MASA AKTIF MITRA ----------------
Route::middleware($hanya('masa_aktif'))->group(function () {
    Route::get('/masa-aktif', [AdminMitraController::class, 'masaAktif'])->name('masa_aktif');
});

Route::middleware($hanya('masa_aktif.atur'))->group(function () {
    Route::put('/masa-aktif', [AdminMitraController::class, 'aturMasaAktif'])->name('masa_aktif.atur');
});
