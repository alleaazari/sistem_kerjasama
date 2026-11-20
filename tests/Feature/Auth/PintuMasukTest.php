<?php

use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| Tiga pintu masuk terpisah: mitra, admin kerja sama, superadmin
|--------------------------------------------------------------------------
| Setiap area harus dilalui login lebih dulu, dan sesi satu peran tidak
| boleh dipakai membuka area peran lain.
*/

beforeEach(function () {
    RateLimiter::clear('admin-login');
    RateLimiter::clear('mitra-login');
    app('cache')->flush();

    config([
        'admin_akses.peran.superadmin.akun.username' => '',
        'admin_akses.peran.superadmin.akun.password_hash' => '',
        'admin_akses.peran.admin_kerjasama.akun.username' => '',
        'admin_akses.peran.admin_kerjasama.akun.password_hash' => '',
    ]);
});

// ------------------------------------------------- area mitra terkunci ----

test('area mitra menolak pengunjung yang belum masuk', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with(['/mitra', '/mitra/profile', '/mitra/mou', '/mitra/profile/foto']);

test('aksi tulis di area mitra juga menolak pengunjung yang belum masuk', function (string $url) {
    $this->post($url, [])->assertRedirect(route('login'));
})->with(['/mitra/perpanjangan', '/mitra/profile', '/mitra/mou', '/mitra/profile/foto']);

test('pengunjung diarahkan kembali ke halaman yang dituju setelah masuk', function () {
    $this->get('/mitra/mou')->assertRedirect(route('login'));

    expect(session('url.intended'))->toContain('/mitra/mou');
});

test('login mitra menolak kata sandi salah', function () {
    $this->from(route('login'))
        ->post(route('login.post'), [
            'username' => config('mitra.demo_user'),
            'password' => 'salah',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('mitra_data'))->toBeNull();
});

test('login mitra berhasil dengan akun demo lalu membuka area mitra', function () {
    $this->post(route('login.post'), [
        'username' => config('mitra.demo_user'),
        'password' => config('mitra.demo_password'),
    ])->assertRedirect(route('mitra.status'));

    expect(session('mitra_data.username'))->toBe(config('mitra.demo_user'));

    $this->get('/mitra')->assertOk();
});

test('akun admin tidak bisa masuk lewat pintu login mitra', function () {
    $this->from(route('login'))
        ->post(route('login.post'), [
            'username' => 'superadmin',
            'password' => 'superadmin123',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('mitra_data'))->toBeNull();
});

test('logout mitra mengakhiri sesi dan menutup area mitra lagi', function () {
    $this->withSession(['mitra_data' => ['username' => 'user1']]);

    $this->post(route('logout'))->assertRedirect(route('landing'));

    expect(session('mitra_data'))->toBeNull();

    $this->get('/mitra')->assertRedirect(route('login'));
});

// -------------------------------------- sesi admin tidak bocor ke mitra ---

test('sesi admin tidak bisa membuka area mitra', function (string $role) {
    // Sesi admin bukan sesi mitra: tetap harus lewat pintu login mitra.
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => $role]])
        ->get('/mitra')
        ->assertRedirect(route('login'));
})->with(['superadmin', 'admin_kerjasama']);

test('sesi mitra tidak bisa membuka area admin', function () {
    $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->get(route('superadmin.inbox'))
        ->assertRedirect(route('superadmin.login'));

    $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->get(route('kerjasama.inbox'))
        ->assertRedirect(route('kerjasama.login'));
});

// ------------------------------------- tiga pintu login benar-benar beda ---

test('setiap area punya halaman login sendiri', function () {
    $this->get('/login')->assertOk()->assertSee('Masuk Mitra');
    $this->get(route('superadmin.login'))->assertOk()->assertSee('Masuk Super Administrator');
    $this->get(route('kerjasama.login'))->assertOk()->assertSee('Masuk Admin Kerja Sama');
});

test('beranda admin mengarahkan ke login sebelum masuk', function (string $url, string $login) {
    $this->get($url)->assertRedirect(route($login));
})->with([
    ['/superadmin', 'superadmin.login'],
    ['/admin-kerjasama', 'kerjasama.login'],
]);

test('setelah login admin hanya beranda perannya yang terbuka', function () {
    $this->post(route('kerjasama.login.post'), [
        'username' => 'kerjasama',
        'password' => 'kerjasama123',
    ])->assertRedirect(route('kerjasama.inbox'));

    $this->get(route('kerjasama.inbox'))->assertOk();
    $this->get(route('superadmin.dashboard'))->assertNotFound();
    $this->get('/mitra')->assertRedirect(route('login'));
});
