<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memilih cookie sesi berdasarkan prefix peran di segmen URL pertama.
 *
 * Tujuan: tiap panel peran (superadmin, admin kerjasama, admin konten) memakai
 * cookie + path sesi sendiri, sehingga beberapa panel bisa login bersamaan di
 * browser yang sama tanpa saling menimpa. Laravel menurunkan cookie XSRF dari
 * session.path, jadi CSRF ikut terpisah otomatis.
 *
 * WAJIB berjalan SEBELUM StartSession (lihat prepend di bootstrap/app.php)
 * supaya nama cookie yang sudah diganti benar-benar dibaca saat sesi dimulai.
 * EncryptCookies tidak terpengaruh: ia bekerja per cookie sesuai nama yang
 * diminta StartSession.
 *
 * Prefix dicocokkan sebagai segmen utuh (`/superadmin` bukan awalan dari
 * `/superadmin-lain`), dan dibaca dari config/session_roles.php yang isinya
 * diturunkan dari config/admin_akses.php (.env). Tidak ada nama cookie yang
 * di-hardcode di sini.
 */
class SessionPerPeran
{
    public function handle(Request $request, Closure $next): Response
    {
        $segmen = (string) $request->segment(1);

        foreach ((array) config('session_roles.per_peran', []) as $peta) {
            $path = trim((string) ($peta['path'] ?? ''), '/');
            $nama = (string) ($peta['nama'] ?? '');

            if ($path === '' || $nama === '' || $segmen !== $path) {
                continue;
            }

            config([
                'session.cookie' => $nama,
                'session.path' => '/'.$path,
            ]);

            break;
        }

        return $next($request);
    }
}
