<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $auth = $request->session()->get('admin_auth');

        // Belum masuk / sesi kedaluwarsa: arahkan ke halaman login, bukan 404.
        // Halaman login tetap berada di prefix rahasia (config admin_demo.path).
        if (! $auth) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi admin tidak ditemukan.'], 401);
            }

            return redirect()->guest(route('admin.login'))
                ->with('info', 'Sesi admin Anda berakhir. Silakan masuk kembali untuk melanjutkan.');
        }

        // Sudah masuk tetapi perannya tidak cocok: tetap disembunyikan.
        if ($roles && ! in_array($auth['role'], $roles, true)) {
            abort(404);
        }

        return $next($request);
    }
}
