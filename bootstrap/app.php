<?php

use App\Http\Middleware\AdminOnly;
use App\Http\Middleware\HideAdmin;
use App\Http\Middleware\MitraOnly;
use App\Http\Middleware\SecurityHeaders;
use App\Support\AdminAkses;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Satu grup per peran: prefix URL + halaman login sendiri.
            // Prefix dibaca dari .env lewat AdminAkses -> config/admin_akses.php.
            foreach (AdminAkses::semua() as $peran) {
                $grup = fn () => Route::middleware(['web', HideAdmin::class])
                    ->prefix(AdminAkses::path($peran))
                    ->name(AdminAkses::nama($peran).'.');

                // Pintu masuk: login, logout, root peran.
                $grup()->group(base_path('routes/admin_bersama.php'));

                // Halaman kerja sama dipakai bersama (middleware per kemampuan).
                $grup()->group(base_path('routes/admin_kerja.php'));

                // Halaman yang memang hanya milik peran ini.
                if ($peran === 'superadmin') {
                    $grup()->group(base_path('routes/superadmin.php'));
                }
            }

            // Tautan lama /{ADMIN_PATH}/* dialihkan ke padanan /superadmin/*.
            Route::middleware('web')->group(base_path('routes/admin_alias.php'));

            // Rute halaman pengaturan (profil, keamanan, tampilan). Tanpa ini
            // Wayfinder tidak membuat @/routes/{profile,security,appearance}
            // sehingga `npm run build` gagal. Isi berkas tidak diubah.
            Route::middleware('web')
                ->group(base_path('routes/settings.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.only' => AdminOnly::class,
            'mitra.only' => MitraOnly::class,
        ]);

        // Header keamanan dasar untuk seluruh request web.
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
