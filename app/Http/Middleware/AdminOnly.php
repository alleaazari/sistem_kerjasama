<?php

namespace App\Http\Middleware;

use App\Support\AdminAkses;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses panel admin berdasarkan peran.
 *
 * Dipakai dua cara:
 *  - `admin.only` tanpa argumen: cukup peran apa pun yang benar untuk prefix.
 *  - `admin.only:superadmin,admin_kerjasama`: peran harus salah satu dari itu.
 *
 * Aturan:
 *  - Belum masuk -> redirect ke login prefix yang sedang dibuka.
 *  - Sesi milik peran lain (mis. admin_kerjasama membuka prefix superadmin)
 *    -> 404, bukan 403, supaya halaman lain seolah tidak ada (pola proyek).
 *  - Sesi benar untuk prefix tetapi halaman bukan milik perannya -> 404.
 *
 * Pemetaan peran -> prefix dibaca dari config/admin_akses.php lewat
 * AdminAkses, bukan ditebak dari URL mentah, supaya konsisten dengan menu
 * dan test.
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $peran = AdminAkses::peranSesi();

        // Belum masuk / sesi kedaluwarsa: arahkan ke login prefix yang sedang
        // dibuka, supaya pengguna tidak dilempar ke panel peran lain.
        if ($peran === null) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi admin tidak ditemukan.'], 401);
            }

            return redirect()->guest(route($this->ruteLoginUntuk($request)))
                ->with('info', 'Sesi panel Anda berakhir. Silakan masuk kembali untuk melanjutkan.');
        }

        // Peran hanya boleh masuk lewat prefix peran yang sama.
        if (! AdminAkses::adaPeran($peran)) {
            abort(404);
        }

        $prefixPeran = AdminAkses::peranDariPath((string) $request->segment(1));
        $induk = AdminAkses::ada($peran) ? $peran : AdminAkses::induk($peran);

        if ($prefixPeran === null || $prefixPeran !== $induk) {
            abort(404);
        }

        // Peran sudah benar untuk prefix, tetapi halaman ini bukan miliknya.
        if ($roles !== [] && ! in_array($peran, $roles, true)) {
            abort(404);
        }

        return $next($request);
    }

    /**
     * Nama route login yang sesuai dengan prefix pada URL saat ini.
     * Contoh: /admin-kerjasama/login -> kerjasama.login
     */
    private function ruteLoginUntuk(Request $request): string
    {
        $peran = AdminAkses::peranDariPath((string) $request->segment(1));

        // Prefix lama / tak dikenal: jatuh ke panel superadmin.
        return AdminAkses::ruteLogin($peran ?? 'superadmin');
    }
}
