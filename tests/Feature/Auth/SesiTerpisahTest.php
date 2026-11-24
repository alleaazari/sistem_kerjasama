<?php

use App\Support\AdminAkses;
use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| Sesi terpisah per peran
|--------------------------------------------------------------------------
| Tiap panel peran memakai cookie sesi + session.path sendiri, sehingga
| beberapa panel bisa login bersamaan tanpa saling menimpa. Middleware
| SessionPerPeran memilih cookie dari segmen pertama URL.
|
| Uji di sini memakai HTTP penuh () supaya cookie yang benar-benar
| di-set server ikut terverifikasi, bukan hanya isi session store.
*/

const SANDI_SUPER = 'superrahasia-uji-superadmin';
const SANDI_KERJA = 'kerjasama-uji-admin';
const SANDI_KONTEN = 'konten-uji-admin';

beforeEach(function () {
    RateLimiter::clear('admin-login');
    app('cache')->flush();
});

// ---------------------------------------------------------------- nama cookie

test('tiap prefix peran memakai nama cookie + path sendiri saat Set-Cookie', function (string $peran, string $nama) {
    $path = AdminAkses::path($peran);

    $respons = $this->get('/'.$path.'/login');

    $cookie = collect($respons->headers->getCookies())
        ->first(fn ($c) => $c->getName() === $nama);

    expect($cookie)->not->toBeNull("Cookie {$nama} tidak di-set untuk /{$path}/login")
        ->and($cookie->getPath())->toBe('/'.$path);
})->with([
    ['superadmin', 'kerjasama_superadmin_session'],
    ['admin_kerjasama', 'kerjasama_admin_kerjasama_session'],
    ['admin_konten', 'kerjasama_admin_konten_session'],
]);

test('halaman publik memakai cookie sesi bawaan, bukan cookie peran', function () {
    $namaBawaan = config('session.cookie');

    $respons = $this->get('/');

    $nama = collect($respons->headers->getCookies())->map->getName()->all();

    expect($nama)->toContain($namaBawaan)
        ->and($nama)->not->toContain('kerjasama_superadmin_session');
});

test('cookie sesi HttpOnly dan SameSite Lax', function () {
    $respons = $this->get('/'.AdminAkses::path('superadmin').'/login');

    $cookie = collect($respons->headers->getCookies())
        ->first(fn ($c) => $c->getName() === 'kerjasama_superadmin_session');

    expect($cookie->isHttpOnly())->toBeTrue()
        ->and($cookie->getSameSite())->toBe('lax');
});

// ---------------------------------------------------------------- login & akses

test('login tiap panel berhasil dan mengakses berandanya', function (string $peran, string $username, string $sandi) {
    $this->post(route(AdminAkses::nama($peran).'.login.post'), [
        'username' => $username,
        'password' => $sandi,
    ])->assertRedirect(route(AdminAkses::beranda($peran)));

    $this->get(route(AdminAkses::beranda($peran)))->assertOk();
})->with([
    ['superadmin', 'uji-superadmin', SANDI_SUPER],
    ['admin_kerjasama', 'uji-kerjasama', SANDI_KERJA],
    ['admin_konten', 'uji-konten', SANDI_KONTEN],
]);

// ---------------------------------------------------------------- isolasi sesi

test('login di satu panel tidak mengubah sesi panel lain', function () {
    // Login panel superadmin dulu, catat peran di sesinya.
    $this->post(route('superadmin.login.post'), [
        'username' => 'uji-superadmin',
        'password' => SANDI_SUPER,
    ])->assertRedirect(route(AdminAkses::beranda('superadmin')));

    expect(session('admin_auth.role'))->toBe('superadmin');

    // Login panel admin konten pada request terpisah: sesi konten yang ditulis,
    // sesi superadmin di request lain tidak tersentuh.
    $this->flushSession();

    $this->post(route('konten.login.post'), [
        'username' => 'uji-konten',
        'password' => SANDI_KONTEN,
    ])->assertRedirect(route(AdminAkses::beranda('admin_konten')));

    expect(session('admin_auth.role'))->toBe('admin_konten');
});

// ---------------------------------------------------------------- RBAC tetap

test('cookie superadmin tidak bisa membuka panel admin konten', function () {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'superadmin']])
        ->get(route('konten.dashboard_konten'))
        ->assertNotFound();
});

test('cookie admin konten tidak bisa membuka panel superadmin', function () {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'admin_konten']])
        ->get(route('superadmin.dashboard'))
        ->assertNotFound();
});

// ---------------------------------------------------------------- tamu = 200

test('halaman login tiap panel tampil 200 untuk tamu', function (string $nama) {
    $this->get(route($nama))->assertOk();
})->with(['superadmin.login', 'kerjasama.login', 'konten.login']);

// ---------------------------------------------------------------- logout terpisah

test('logout satu panel tidak mengeluarkan panel lain', function () {
    // Sesi superadmin masih hidup.
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'superadmin']]);
    expect(session('admin_auth.role'))->toBe('superadmin');

    // Logout panel admin konten (prefix berbeda).
    $this->flushSession();
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'admin_konten']])
        ->post(route('konten.logout'))
        ->assertRedirect(route('konten.login'));

    expect(session('admin_auth'))->toBeNull();
});
