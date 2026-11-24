<?php

use App\Http\Controllers\AdminSuperadminController;
use App\Support\AdminAkses;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel Admin Konten — halaman khusus admin_konten
|--------------------------------------------------------------------------
| Dimuat di bootstrap/app.php dengan prefix AdminAkses::path('admin_konten')
| dan awalan nama route `konten.` (lihat config/admin_akses.php).
|
| Pintu masuk (login, logout, root) dimuat lewat routes/admin_bersama.php
| oleh bootstrap/app.php untuk setiap peran, termasuk admin_konten. Berkas
| ini hanya berisi halaman yang memang milik admin_konten.
|
| Akses dibatasi hanya peran admin_konten melalui middleware `admin.only:<peran>`
| dari peta izin config/admin_akses.php. Halaman yang dimiliki superadmin
| dan admin_kerjasama (dashboard penuh, inbox, draf, masa aktif, template MoU,
| aktivitas, pengguna) tidak ada di sini sama sekali.
|
| Controller yang sama dengan superadmin dipakai (bukan salinan kode) untuk
| konten portal: tentang, artikel, galeri. Hanya middleware dan prefixnya yang
| berbeda.
*/

/** Middleware untuk satu kemampuan menurut config/admin_akses.php. */
$hanya = static fn (string $kemampuan): string => 'admin.only:'.implode(',', AdminAkses::peranUntuk($kemampuan));

// ---------------- DASHBOARD MITRA DITERIMA (read-only) ----------------
// Hanya peran pemilik kemampuan `dashboard_konten`. Root /{path} tetap
// ditangani routes/admin_bersama.php (beranda -> redirect ke sini).
Route::middleware($hanya('dashboard_konten'))->group(function () {
    Route::get('/dashboard', [AdminSuperadminController::class, 'dashboardKonten'])->name('dashboard_konten');
});

// ---------------- KONTEN PORTAL (controller sama dengan superadmin) ----------------
Route::middleware($hanya('tentang'))->group(function () {
    Route::get('/tentang', [AdminSuperadminController::class, 'tentang'])->name('tentang');
    Route::post('/tentang', [AdminSuperadminController::class, 'tentangUpdate'])->name('tentang.update');
});

Route::middleware($hanya('artikel'))->group(function () {
    Route::get('/artikel', [AdminSuperadminController::class, 'artikel'])->name('artikel');
    Route::post('/artikel', [AdminSuperadminController::class, 'artikelStore'])->name('artikel.store');
    Route::post('/artikel/{id}/delete', [AdminSuperadminController::class, 'artikelDelete'])->name('artikel.delete');
    Route::post('/artikel/{id}/edit', [AdminSuperadminController::class, 'artikelEdit'])->name('artikel.edit');
});

Route::middleware($hanya('galeri'))->group(function () {
    Route::get('/galeri', [AdminSuperadminController::class, 'galeri'])->name('galeri');
    Route::post('/galeri', [AdminSuperadminController::class, 'galeriStore'])->name('galeri.store');
    Route::post('/galeri/{id}/delete', [AdminSuperadminController::class, 'galeriDelete'])->name('galeri.delete');
    Route::post('/galeri/{id}/edit', [AdminSuperadminController::class, 'galeriEdit'])->name('galeri.edit');
});
