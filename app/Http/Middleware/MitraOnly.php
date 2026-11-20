<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penjaga area mitra.
 *
 * Area mitra belum punya guard autentikasi sungguhan (lihat catatan untuk
 * backend). Yang ditegakkan di sini adalah batas minimum yang tidak boleh
 * dilanggar: area mitra hanya terbuka bila sesi mitra sudah ada.
 *
 * Sesi panel admin TIDAK dihitung sebagai sesi mitra, sehingga membuka
 * /mitra tanpa login mitra selalu berakhir di halaman login mitra —
 * bukan langsung masuk memakai data contoh.
 *
 * Bila nanti tabel mitra + guard tersedia, cukup ganti sumber identitasnya
 * di sini; route dan controller tidak perlu berubah.
 */
class MitraOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! self::sudahMasuk($request)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi mitra tidak ditemukan.'], 401);
            }

            return redirect()->guest(route('login'))
                ->with('info', 'Silakan masuk sebagai mitra untuk membuka halaman ini.');
        }

        return $next($request);
    }

    /** Sesi mitra dianggap ada bila username-nya terisi. */
    public static function sudahMasuk(Request $request): bool
    {
        $data = $request->session()->get('mitra_data');
        $username = is_array($data) ? ($data['username'] ?? null) : null;

        return is_string($username) && trim($username) !== '';
    }
}
