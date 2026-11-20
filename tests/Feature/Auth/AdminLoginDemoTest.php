<?php

use App\Support\AdminAkses;
use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| Akun demo panel admin
|--------------------------------------------------------------------------
| Kredensial panel dibaca dari .env. Bila .env kosong (mis. repo baru di-clone
| di mesin lain) login akan mati total. Karena itu config/admin_akses.php
| menyimpan akun demo yang ikut ter-commit dan hanya aktif di luar production.
|
| Test ini memaksa bagian .env kosong agar jalur demo benar-benar teruji.
*/

beforeEach(function () {
    RateLimiter::clear('admin-login');
    app('cache')->flush();

    // Kosongkan kredensial .env seperti di mesin yang belum menyetelnya.
    config([
        'admin_akses.peran.superadmin.akun.username' => '',
        'admin_akses.peran.superadmin.akun.password_hash' => '',
        'admin_akses.peran.admin_kerjasama.akun.username' => '',
        'admin_akses.peran.admin_kerjasama.akun.password_hash' => '',
        'admin_akses.akun_lain.developer.akun.username' => '',
        'admin_akses.akun_lain.developer.akun.password_hash' => '',
    ]);
});

test('akun demo aktif saat .env kosong dan bukan production', function () {
    $akun = AdminAkses::akunAktif('superadmin');

    expect($akun)->not->toBeNull()
        ->and($akun['username'])->toBe('superadmin')
        ->and($akun['password'])->toBe('superadmin123');
});

test('kata sandi demo tiap peran berbeda', function () {
    $super = AdminAkses::akunAktif('superadmin');
    $kerja = AdminAkses::akunAktif('admin_kerjasama');

    expect($super['password'])->toBe('superadmin123')
        ->and($kerja['password'])->toBe('kerjasama123')
        ->and($super['password'])->not->toBe($kerja['password']);
});

test('login superadmin memakai akun demo saat .env kosong', function () {
    $this->post(route('superadmin.login.post'), [
        'username' => 'superadmin',
        'password' => 'superadmin123',
    ])->assertRedirect(route(AdminAkses::beranda('superadmin')));

    expect(session('admin_auth.role'))->toBe('superadmin');
});

test('login admin kerja sama memakai akun demo saat .env kosong', function () {
    $this->post(route('kerjasama.login.post'), [
        'username' => 'kerjasama',
        'password' => 'kerjasama123',
    ])->assertRedirect(route(AdminAkses::beranda('admin_kerjasama')));

    expect(session('admin_auth.role'))->toBe('admin_kerjasama');
});

test('kata sandi demo yang salah tetap ditolak', function () {
    $this->from(route('superadmin.login'))
        ->post(route('superadmin.login.post'), [
            'username' => 'superadmin',
            'password' => 'salah',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('akun demo tidak bisa masuk lewat prefix peran lain', function () {
    // Kata sandi Superadmin dipakai di pintu Admin Kerja Sama.
    $this->from(route('kerjasama.login'))
        ->post(route('kerjasama.login.post'), [
            'username' => 'superadmin',
            'password' => 'superadmin123',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('kata sandi Superadmin ditolak di pintu Admin Kerja Sama', function () {
    $this->from(route('kerjasama.login'))
        ->post(route('kerjasama.login.post'), [
            'username' => 'kerjasama',
            'password' => 'superadmin123',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('kata sandi Admin Kerja Sama ditolak di pintu Superadmin', function () {
    $this->from(route('superadmin.login'))
        ->post(route('superadmin.login.post'), [
            'username' => 'superadmin',
            'password' => 'kerjasama123',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('akun mitra demo ditolak di pintu panel admin', function () {
    $this->from(route('superadmin.login'))
        ->post(route('superadmin.login.post'), [
            'username' => (string) config('mitra.demo_user'),
            'password' => (string) config('mitra.demo_password'),
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('halaman login tidak menampilkan catatan pengembangan', function () {
    $this->get(route('superadmin.login'))
        ->assertOk()
        ->assertDontSee('Catatan pengembangan')
        ->assertDontSee('Akun demo')
        ->assertDontSee('123456');

    $this->get(route('kerjasama.login'))
        ->assertOk()
        ->assertDontSee('Catatan pengembangan')
        ->assertDontSee('123456');
});

test('akun demo tetap bisa dipakai walau kredensialnya tidak ditampilkan', function () {
    $this->post(route('superadmin.login.post'), [
        'username' => 'superadmin',
        'password' => 'superadmin123',
    ])->assertRedirect(route(AdminAkses::beranda('superadmin')));

    expect(session('admin_auth.role'))->toBe('superadmin');
});

test('akun demo mati total di production', function () {
    app()['env'] = 'production';

    expect(AdminAkses::akunAktif('superadmin'))->toBeNull();

    $this->post(route('superadmin.login.post'), [
        'username' => 'superadmin',
        'password' => 'superadmin123',
    ]);

    expect(session('admin_auth'))->toBeNull();
});
