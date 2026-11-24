<?php

namespace App\Http\Controllers;

use App\Support\AdminAkses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Login panel admin: satu halaman per peran, kredensial dari .env.
 *
 * Peran yang dilayani dibaca dari nama route (mis. `kerjasama.login` ->
 * admin_kerjasama), bukan dari argumen yang ditanam saat route didaftarkan:
 * berkas route dimuat sekali saat boot, jauh sebelum ada request, sehingga
 * nilai yang dihitung di sana akan sama untuk semua prefix.
 *
 * Aturan penting:
 *  - Kredensial tidak pernah ditulis di kode, hanya hash bcrypt dari .env.
 *  - Akun peran lain tidak bisa masuk di sini; pesannya tetap generik supaya
 *    keberadaan akun tidak bocor.
 *  - Tiap panel punya cookie sesi sendiri (SessionPerPeran), jadi login di
 *    satu panel tidak menyentuh sesi panel lain.
 *  - Session diregenerasi saat login dan diinvalidasi saat logout (per panel).
 *  - Throttle per IP + username dipasang di route (limiter `admin-login`).
 *
 * TODO-BACKEND: setelah tabel `users` ada, ganti pembacaan config dengan
 * guard Eloquent + kolom role, tanpa mengubah kontrak route/controller ini.
 */
class AdminLoginController extends Controller
{
    /** Pesan tunggal untuk semua kegagalan login. */
    private const PESAN_GAGAL = 'Nama pengguna atau kata sandi tidak sesuai.';

    /**
     * Root prefix peran: teruskan ke beranda bila sesi cocok,
     * selain itu tampilkan form login peran itu.
     */
    public function beranda(Request $request): RedirectResponse
    {
        $peran = $this->peran($request);

        if ($this->sesiCocok($peran)) {
            return redirect()->route(AdminAkses::beranda((string) AdminAkses::peranSesi()));
        }

        return redirect()->route(AdminAkses::nama($peran).'.login');
    }

    public function tampilkan(Request $request): View|RedirectResponse
    {
        $peran = $this->peran($request);

        // Sudah masuk dengan peran yang sah untuk prefix ini: langsung ke beranda.
        if ($this->sesiCocok($peran)) {
            return redirect()->route(AdminAkses::beranda((string) AdminAkses::peranSesi()));
        }

        return view('admin.login', [
            'peran' => $peran,
            'labelPeran' => AdminAkses::label($peran),
            'judulHalaman' => 'Masuk '.AdminAkses::label($peran),
            'aksi' => route(AdminAkses::nama($peran).'.login.post'),
        ]);
    }

    public function masuk(Request $request): RedirectResponse
    {
        $peran = $this->peran($request);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'Nama pengguna wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $akun = $this->cariAkunAktif($peran, (string) $data['username']);

        // Satu pesan generik untuk semua kegagalan: akun tidak ada, password
        // salah, atau akun milik peran lain. Tidak membocorkan apa pun.
        if ($akun === null || ! $this->sandiCocok($akun, (string) $data['password'])) {
            return back()
                ->withInput($request->only('username'))
                ->with('login_error', self::PESAN_GAGAL);
        }

        $request->session()->regenerate();
        session([AdminAkses::KUNCI_SESI => [
            'name' => $akun['name'],
            'role' => $akun['role'],
        ]]);

        return redirect()->intended(route(AdminAkses::beranda($akun['role'])));
    }

    public function keluar(Request $request): RedirectResponse
    {
        // Sesi yang diinvalidasi adalah sesi panel ini saja (cookie terpisah
        // per peran), jadi logout di satu panel tidak mengeluarkan peran lain.
        // Peran diambil dari sesi panel ini; bila sesi sudah hilang, pakai peran
        // prefix tempat logout dipanggil supaya pengguna tidak dilempar ke panel
        // peran lain.
        $peran = AdminAkses::peranSesi() ?? $this->peran($request);

        session()->forget(['admin_auth', 'admin_artikel', 'admin_galeri']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route(AdminAkses::ruteLogin($peran));
    }

    /**
     * Peran yang dilayani request ini, dibaca dari nama route grupnya.
     * Nama route selalu berawalan awalan peran (superadmin.*, kerjasama.*).
     */
    private function peran(Request $request): string
    {
        $namaRoute = $request->route()?->getName();
        $peran = is_string($namaRoute) ? AdminAkses::peranDariNamaRoute($namaRoute) : null;

        abort_if($peran === null, 404);

        return $peran;
    }

    /**
     * Sesi yang ada sudah memenuhi syarat untuk masuk ke prefix ini.
     */
    private function sesiCocok(string $peran): bool
    {
        $sesi = AdminAkses::peranSesi();

        if ($sesi === null) {
            return false;
        }

        return in_array($sesi, AdminAkses::peranLoginDi($peran), true);
    }

    /**
     * Cocokkan kata sandi: hash bcrypt untuk akun .env, hash_equals untuk demo.
     *
     * @param  array{password_hash: string, password: string|null}  $akun
     */
    private function sandiCocok(array $akun, string $sandi): bool
    {
        if ($akun['password_hash'] !== '') {
            return Hash::check($sandi, $akun['password_hash']);
        }

        $demo = (string) ($akun['password'] ?? '');

        return $demo !== '' && hash_equals($demo, $sandi);
    }

    /**
     * Cari akun yang cocok di prefix ini saja.
     *
     * @return array{name: string, username: string, email: string, password_hash: string, password: string|null, role: string}|null
     */
    private function cariAkunAktif(string $peran, string $username): ?array
    {
        $dicari = Str::lower(trim($username));

        foreach (AdminAkses::peranLoginDi($peran) as $kandidat) {
            $akun = AdminAkses::akunAktif($kandidat);

            if ($akun === null) {
                continue;
            }

            $cocokUsername = Str::lower(trim($akun['username'])) === $dicari;
            $cocokEmail = $akun['email'] !== '' && Str::lower(trim($akun['email'])) === $dicari;

            if ($cocokUsername || $cocokEmail) {
                return [
                    'name' => $akun['name'],
                    'username' => $akun['username'],
                    'email' => $akun['email'],
                    'password_hash' => $akun['password_hash'],
                    'password' => $akun['password'] ?? null,
                    'role' => $kandidat,
                ];
            }
        }

        return null;
    }
}
