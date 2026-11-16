<?php

use App\Http\Controllers\AdminLoginController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Pintu masuk panel: login & logout per peran
|--------------------------------------------------------------------------
| Berkas ini dimuat SEKALI untuk setiap peran di bootstrap/app.php, di dalam
| grup prefix + awalan nama route perannya. Controller membaca peran dari
| nama route hasil grup itu, jadi tidak ada nilai peran yang ditanam di sini
| (berkas route dimuat saat boot, sebelum ada request).
|
|   /{path}/login    -> form login milik peran itu (judul & aksi sendiri)
|   /{path}/logout   -> invalidasi sesi, kembali ke login peran itu
|   /{path}          -> beranda perannya (atau login bila belum masuk)
*/

Route::get('/login', [AdminLoginController::class, 'tampilkan'])
    ->name('login');

// Throttle per IP + username (lihat RateLimiter 'admin-login').
Route::post('/login', [AdminLoginController::class, 'masuk'])
    ->middleware('throttle:admin-login')
    ->name('login.post');

Route::post('/logout', [AdminLoginController::class, 'keluar'])
    ->name('logout');

// Root prefix = beranda peran. Didaftarkan paling akhir agar tidak menutupi
// halaman lain; hanya menjawab tepat pada '/{path}'.
Route::get('/', [AdminLoginController::class, 'beranda'])
    ->name('beranda');
