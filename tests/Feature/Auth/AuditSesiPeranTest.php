<?php

use App\Support\AdminAkses;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Testing\TestResponse;

/*
|--------------------------------------------------------------------------
| Audit: sesi terpisah per peran — uji HTTP dengan cookie jar nyata
|--------------------------------------------------------------------------
| Meniru curl `-c/-b`: tiap "client" memegang jar cookie sendiri. Sebelum
| tiap request, cookie milik instance tes disetel tepat sama dengan isi jar
| (bukan menumpuk), lalu Set-Cookie dari respons diserap kembali.
|
| Driver sesi diset `file` agar sesi persist antar request (driver `array`
| bawaan tes hanya bertahan satu request). Kredensial dari phpunit.xml:
| uji-superadmin / uji-kerjasama / uji-konten.
*/

const AUDIT_SANDI_SUPER = 'superrahasia-uji-superadmin';
const AUDIT_SANDI_KERJA = 'kerjasama-uji-admin';
const AUDIT_SANDI_KONTEN = 'konten-uji-admin';

/**
 * Setel cookie instance tes tepat sejumlah jar (hapus sisa request lama),
 * lalu serap Set-Cookie respons kembali ke jar.
 */
function auditClient(): object
{
    return new class
    {
        /** @var array<string, string> */
        public array $jar = [];

        public function get(string $url): TestResponse
        {
            return $this->kirim('GET', $url);
        }

        public function post(string $url, array $data = []): TestResponse
        {
            return $this->kirim('POST', $url, $data);
        }

        private function kirim(string $method, string $url, array $data = []): TestResponse
        {
            $test = test();

            // Kirim jar apa adanya sebagai cookie request. call() memakai
            // argumen $cookies langsung (tidak lewat prepareCookiesForRequest),
            // jadi tidak ada penumpukan cookie antar request.
            $server = ['HTTP_HOST' => 'localhost', 'SERVER_NAME' => 'localhost'];
            $uri = parse_url($url, PHP_URL_PATH) ?: '/';
            $query = [];
            parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

            if ($method === 'GET') {
                $data = array_merge($query, $data);
            }

            $respons = $test->call($method, $uri.($query ? '?'.http_build_query($query) : ''), $data, $this->jar, [], $server);

            foreach ($respons->headers->getCookies() as $cookie) {
                $nama = $cookie->getName();
                $value = $cookie->getValue();

                if ($value === null || $value === '') {
                    unset($this->jar[$nama]);

                    continue;
                }

                $this->jar[$nama] = $value;
            }

            return $respons;
        }
    };
}

function auditToken(object $klien, string $urlLogin): string
{
    preg_match('/name="_token"\s+value="([^"]+)"/', $klien->get($urlLogin)->getContent(), $m);

    return $m[1] ?? '';
}

function auditLogin(object $klien, string $peran, string $username, string $sandi): TestResponse
{
    $login = AdminAkses::ruteLogin($peran);
    $token = auditToken($klien, route($login));

    return $klien->post(route($login.'.post'), [
        '_token' => $token,
        'username' => $username,
        'password' => $sandi,
    ]);
}

beforeEach(function () {
    RateLimiter::clear('admin-login');
    app('cache')->flush();
    config(['session.driver' => 'file']);
});

// ------------------------------------------------------------------ poin 1 & 5

test('tiga peran aktif bersamaan; logout satu tidak mengeluarkan yang lain', function () {
    $A = auditClient();
    $B = auditClient();
    $C = auditClient();

    auditLogin($A, 'superadmin', 'uji-superadmin', AUDIT_SANDI_SUPER)
        ->assertRedirect(route(AdminAkses::beranda('superadmin')));
    auditLogin($B, 'admin_konten', 'uji-konten', AUDIT_SANDI_KONTEN)
        ->assertRedirect(route(AdminAkses::beranda('admin_konten')));
    auditLogin($C, 'admin_kerjasama', 'uji-kerjasama', AUDIT_SANDI_KERJA)
        ->assertRedirect(route(AdminAkses::beranda('admin_kerjasama')));

    $step = [];
    for ($i = 0; $i < 3; $i++) {
        $step["A /superadmin/dashboard #$i"] = $A->get(route('superadmin.dashboard'))->status();
        $step["B /admin-konten/dashboard #$i"] = $B->get(route('konten.dashboard_konten'))->status();
        $step["C /admin-kerjasama/inbox #$i"] = $C->get(route('kerjasama.inbox'))->status();
    }

    foreach ($step as $nama => $status) {
        expect($status)->toBe(200, "$nama harus 200");
    }

    $tokenB = auditToken($B, route('konten.login'));
    $B->post(route('konten.logout'), ['_token' => $tokenB]);

    expect($B->get(route('konten.dashboard_konten'))->status())->not->toBe(200, 'B sudah logout');
    expect($A->get(route('superadmin.dashboard'))->status())->toBe(200, 'A tetap valid');
    expect($C->get(route('kerjasama.inbox'))->status())->toBe(200, 'C tetap valid');
});

// ------------------------------------------------------------------ poin 2

test('cookie satu peran tidak membuka URL peran lain (GET & mutasi)', function () {
    $A = auditClient();
    $B = auditClient();

    auditLogin($A, 'superadmin', 'uji-superadmin', AUDIT_SANDI_SUPER);
    auditLogin($B, 'admin_konten', 'uji-konten', AUDIT_SANDI_KONTEN);

    expect($A->get(route('konten.dashboard_konten'))->status())->toBe(404);
    expect($A->get(route('konten.artikel'))->status())->toBe(404);
    $tokenA = auditToken($A, route('superadmin.login'));
    expect($A->post(route('konten.artikel.store'), ['_token' => $tokenA, 'judul' => 'x'])->status())->toBe(404);

    expect($B->get(route('superadmin.dashboard'))->status())->toBe(404);
    expect($B->get(route('superadmin.inbox'))->status())->toBe(404);
    $tokenB = auditToken($B, route('konten.login'));
    expect($B->post(route('superadmin.artikel.store'), ['_token' => $tokenB, 'judul' => 'x'])->status())->toBe(404);
});

test('cookie satu peran tidak membuka prefix admin kerjasama', function () {
    $A = auditClient();
    auditLogin($A, 'superadmin', 'uji-superadmin', AUDIT_SANDI_SUPER);

    expect($A->get('/admin-kerjasama/inbox')->status())->toBe(404);
    expect($A->get('/admin-kerjasama/draf-proposal')->status())->toBe(404);
});

// ------------------------------------------------------------------ poin 4

test('request dengan token CSRF lolos (form panel)', function () {
    $B = auditClient();
    auditLogin($B, 'admin_konten', 'uji-konten', AUDIT_SANDI_KONTEN);

    $token = auditToken($B, route('konten.login'));

    $status = $B->post(route('konten.artikel.store'), [
        '_token' => $token,
        'judul' => 'Artikel Audit CSRF',
        'konten' => 'isi',
    ])->status();

    expect($status)->not->toBe(419);
});

// Catatan: penolakan request TANPA token (419) tidak dapat diuji lewat kernel
// tes karena PreventRequestForgery melewati CSRF saat runningUnitTests()
// (vendor/.../Foundation/Http/Middleware/PreventRequestForgery.php:99).
// Bukti 419 diambil dari server nyata (php artisan serve) - lihat
// docs/AUDIT_SESI_PERAN.md bagian CSRF.

// ------------------------------------------------------------------ poin 5

test('session id berganti setelah login (anti-fiksasi)', function () {
    $B = auditClient();

    $B->get(route('konten.login'));
    $idSebelum = $B->jar['kerjasama_admin_konten_session'] ?? null;

    auditLogin($B, 'admin_konten', 'uji-konten', AUDIT_SANDI_KONTEN);

    $idSesudah = $B->jar['kerjasama_admin_konten_session'] ?? null;

    expect($idSebelum)->not->toBeNull()
        ->and($idSesudah)->not->toBeNull()
        ->and($idSesudah)->not->toBe($idSebelum);
});
