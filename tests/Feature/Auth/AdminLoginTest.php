<?php

use App\Support\AdminAkses;
use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| Login panel per peran
|--------------------------------------------------------------------------
| Kredensial khusus pengujian berasal dari phpunit.xml (hash bcrypt di
| SUPERADMIN_PASSWORD_HASH / ADMIN_KERJASAMA_PASSWORD_HASH / DEVELOPER_*).
| Password plaintext hanya hidup di berkas test ini, tidak pernah di config.
*/

const SANDI_SUPERADMIN = 'superrahasia-uji-superadmin';
const SANDI_KERJASAMA = 'kerjasama-uji-admin';
const SANDI_KONTEN = 'konten-uji-admin';

beforeEach(function () {
    // Tiap test mulai dari keadaan throttle bersih.
    RateLimiter::clear('admin-login');
    app('cache')->flush();
});

// ---------------------------------------------------------------- halaman login

test('halaman login tiap peran tampil dengan judul perannya', function (string $peran, string $judul) {
    $this->get(route(AdminAkses::nama($peran).'.login'))
        ->assertOk()
        ->assertSee($judul)
        ->assertSee('name="username"', false)
        ->assertSee('name="password"', false);
})->with([
    ['superadmin', 'Masuk Super Administrator'],
    ['admin_kerjasama', 'Masuk Admin Kerja Sama'],
    ['admin_konten', 'Masuk Admin Konten'],
]);

test('login tiap peran memakai URL sendiri', function () {
    expect(AdminAkses::path('superadmin'))->not->toBe(AdminAkses::path('admin_kerjasama'))
        ->and(route('superadmin.login'))->toBe(url('/'.AdminAkses::path('superadmin').'/login'))
        ->and(route('kerjasama.login'))->toBe(url('/'.AdminAkses::path('admin_kerjasama').'/login'))
        ->and(route('konten.login'))->toBe(url('/'.AdminAkses::path('admin_konten').'/login'));
});

// ---------------------------------------------------------------- login berhasil

test('superadmin berhasil masuk dan diarahkan ke berandanya', function () {
    $this->post(route('superadmin.login.post'), [
        'username' => 'uji-superadmin',
        'password' => SANDI_SUPERADMIN,
    ])->assertRedirect(route(AdminAkses::beranda('superadmin')));

    expect(session('admin_auth.role'))->toBe('superadmin');
});

test('admin kerjasama berhasil masuk dan diarahkan ke berandanya', function () {
    $this->post(route('kerjasama.login.post'), [
        'username' => 'uji-kerjasama',
        'password' => SANDI_KERJASAMA,
    ])->assertRedirect(route(AdminAkses::beranda('admin_kerjasama')));

    expect(session('admin_auth.role'))->toBe('admin_kerjasama');
});

test('admin konten berhasil masuk dan diarahkan ke berandanya', function () {
    $this->post(route('konten.login.post'), [
        'username' => 'uji-konten',
        'password' => SANDI_KONTEN,
    ])->assertRedirect(route(AdminAkses::beranda('admin_konten')));

    expect(session('admin_auth.role'))->toBe('admin_konten');
});

test('beranda tiap peran adalah halaman pertama yang memang miliknya', function () {
    // Superadmin mendarat di dashboard ringkasan.
    expect(AdminAkses::beranda('superadmin'))->toBe('superadmin.dashboard');

    // Admin Kerja Sama tidak punya dashboard; mendarat di inbox.
    expect(AdminAkses::beranda('admin_kerjasama'))->toBe('kerjasama.inbox')
        ->and(AdminAkses::boleh('admin_kerjasama', 'dashboard'))->toBeFalse();

    // Admin Konten mendarat di daftar mitra diterima (baca-saja).
    expect(AdminAkses::beranda('admin_konten'))->toBe('konten.dashboard_konten')
        ->and(AdminAkses::boleh('admin_konten', 'dashboard'))->toBeFalse()
        ->and(AdminAkses::boleh('admin_konten', 'inbox'))->toBeFalse();
});

test('sesi diregenerasi saat login berhasil', function () {
    $sebelum = session()->getId();

    $this->post(route('superadmin.login.post'), [
        'username' => 'uji-superadmin',
        'password' => SANDI_SUPERADMIN,
    ]);

    expect(session()->getId())->not->toBe($sebelum);
});

// ---------------------------------------------------------------- login gagal

test('password salah ditolak dengan pesan generik', function (string $peran) {
    $username = match ($peran) {
        'superadmin' => 'uji-superadmin',
        'admin_kerjasama' => 'uji-kerjasama',
        default => 'uji-konten',
    };

    $respons = $this->from(route(AdminAkses::nama($peran).'.login'))
        ->post(route(AdminAkses::nama($peran).'.login.post'), [
            'username' => $username,
            'password' => 'kata-sandi-keliru',
        ]);

    $respons->assertRedirect(route(AdminAkses::nama($peran).'.login'))
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
})->with(['superadmin', 'admin_kerjasama', 'admin_konten']);

test('username tidak dikenal ditolak dengan pesan yang sama', function () {
    $this->post(route('superadmin.login.post'), [
        'username' => 'tidak-pernah-ada',
        'password' => SANDI_SUPERADMIN,
    ])->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

// ---------------------------------------------------------------- login silang peran

test('akun superadmin ditolak di halaman admin kerjasama', function () {
    $this->from(route('kerjasama.login'))
        ->post(route('kerjasama.login.post'), [
            'username' => 'uji-superadmin',
            'password' => SANDI_SUPERADMIN,
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('akun admin kerjasama ditolak di halaman superadmin', function () {
    $this->from(route('superadmin.login'))
        ->post(route('superadmin.login.post'), [
            'username' => 'uji-kerjasama',
            'password' => SANDI_KERJASAMA,
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('akun admin konten ditolak di halaman superadmin', function () {
    $this->from(route('superadmin.login'))
        ->post(route('superadmin.login.post'), [
            'username' => 'uji-konten',
            'password' => SANDI_KONTEN,
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('akun superadmin ditolak di halaman admin konten', function () {
    $this->from(route('konten.login'))
        ->post(route('konten.login.post'), [
            'username' => 'uji-superadmin',
            'password' => SANDI_SUPERADMIN,
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('pesan penolakan silang peran tidak membocorkan keberadaan akun', function () {
    $pesanSilang = $this->from(route('kerjasama.login'))
        ->post(route('kerjasama.login.post'), ['username' => 'uji-superadmin', 'password' => SANDI_SUPERADMIN])
        ->getSession()->get('login_error');

    $pesanTakAda = $this->from(route('kerjasama.login'))
        ->post(route('kerjasama.login.post'), ['username' => 'akun-yang-tidak-ada', 'password' => 'apa-saja'])
        ->getSession()->get('login_error');

    expect($pesanSilang)->toBe($pesanTakAda);
});

// ---------------------------------------------------------------- throttle

test('login dibatasi 5 percobaan per menit untuk IP + username yang sama', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('superadmin.login.post'), [
            'username' => 'uji-superadmin',
            'password' => 'salah-'.$i,
        ]);
    }

    $this->post(route('superadmin.login.post'), [
        'username' => 'uji-superadmin',
        'password' => SANDI_SUPERADMIN,
    ])->assertTooManyRequests();
});

test('throttle dihitung per username, bukan hanya per IP', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('superadmin.login.post'), ['username' => 'uji-superadmin', 'password' => 'salah']);
    }

    // Username lain dari IP yang sama masih boleh mencoba.
    $this->post(route('kerjasama.login.post'), [
        'username' => 'uji-kerjasama',
        'password' => SANDI_KERJASAMA,
    ])->assertRedirect(route(AdminAkses::beranda('admin_kerjasama')));
});

// ---------------------------------------------------------------- logout

test('logout menginvalidasi sesi dan kembali ke login peran itu', function (string $peran) {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => $peran]])
        ->post(route(AdminAkses::nama($peran).'.logout'))
        ->assertRedirect(route(AdminAkses::nama($peran).'.login'));

    expect(session('admin_auth'))->toBeNull();
})->with(['superadmin', 'admin_kerjasama', 'admin_konten']);

test('logout memakai halaman login peran yang sedang dipakai, bukan peran lain', function () {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'admin_kerjasama']])
        ->post(route('kerjasama.logout'))
        ->assertRedirect(route('kerjasama.login'));
});

test('token CSRF diregenerasi setelah logout', function () {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'superadmin']]);
    $token = session()->token();

    $this->post(route('superadmin.logout'));

    expect(session()->token())->not->toBe($token);
});

// ---------------------------------------------------------------- pemisahan prefix

test('sesi satu peran tidak boleh membuka prefix peran lain', function () {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'admin_kerjasama']])
        ->get(route('superadmin.inbox'))
        ->assertNotFound();
});

test('sesi superadmin tidak boleh membuka prefix admin kerjasama', function () {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'superadmin']])
        ->get(route('kerjasama.inbox'))
        ->assertNotFound();
});

test('sesi peran lain tidak menghalangi halaman login atau perpindahan peran', function () {
    // Cookie sesi terpisah per peran: sesi admin_konten tidak lagi menutup
    // halaman login superadmin. Tamu panel superadmin selalu melihat form.
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'admin_konten']])
        ->get(route('superadmin.login'))
        ->assertOk();

    // Login dengan kredensial superadmin yang sah tetap berhasil; sesi cookie
    // panel superadmin ini yang dipakai, bukan sesi admin_konten.
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'admin_konten']])
        ->post(route('superadmin.login.post'), [
            'username' => 'uji-superadmin',
            'password' => SANDI_SUPERADMIN,
        ])
        ->assertRedirect(route(AdminAkses::beranda('superadmin')));

    expect(session('admin_auth.role'))->toBe('superadmin');
});

test('halaman konten hanya untuk superadmin, admin kerjasama ditolak 404', function (string $nama) {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => 'admin_kerjasama']])
        ->get(route('superadmin.'.$nama))
        ->assertNotFound();
})->with(['tentang', 'artikel', 'galeri', 'dashboard']);

test('halaman kerja sama terbuka untuk kedua peran di prefix masing-masing', function (string $peran) {
    $this->withSession(['admin_auth' => ['name' => 'Uji', 'role' => $peran]])
        ->get(route(AdminAkses::nama($peran).'.inbox'))
        ->assertOk()
        ->assertSee('Inbox Pengajuan');
})->with(['superadmin', 'admin_kerjasama']);

test('peran lama admin tidak lagi diakui', function () {
    expect(AdminAkses::adaPeran('admin'))->toBeFalse()
        ->and(AdminAkses::ada('admin'))->toBeFalse();

    $this->withSession(['admin_auth' => ['name' => 'Admin Lama', 'role' => 'admin']])
        ->get(route('superadmin.inbox'))
        ->assertNotFound();
});

// ---------------------------------------------------------------- redirect lama

test('setiap alamat lama /{ADMIN_PATH}/* dialihkan ke padanan superadmin', function (string $suffix, string $tujuan) {
    $pathLama = AdminAkses::pathLama();
    $urlLama = '/'.$pathLama.($suffix === '' ? '' : '/'.$suffix);

    $this->get($urlLama)->assertRedirect('/'.AdminAkses::path('superadmin').'/'.$tujuan);
})->with([
    ['', 'dashboard'],
    ['inbox', 'inbox'],
    ['dashboard', 'dashboard'],
    ['draf-proposal', 'draf_proposal'],
    ['mitra-draf', 'draf_proposal'],
    ['masa-aktif', 'masa_aktif'],
    ['tentang', 'tentang'],
    ['artikel', 'artikel'],
    ['galeri', 'galeri'],
    ['login', 'login'],
    ['test-email', 'test_email'],
]);

test('alamat lama tidak mengalihkan aksi tulis', function () {
    $pathLama = AdminAkses::pathLama();

    // POST ke URL lama tidak dialihkan; 405/404 sah, yang penting bukan redirect.
    $respons = $this->post('/'.$pathLama.'/inbox/1/approve');

    expect($respons->getStatusCode())->toBeIn([404, 405]);
});

// ---------------------------------------------------------------- menu & identitas

test('menu sidebar dirender dari peran login, bukan nama peran hardcode', function () {
    $htmlSuper = $this->withSession(['admin_auth' => ['name' => 'Super Uji', 'role' => 'superadmin']])
        ->get(route('superadmin.dashboard'))->assertOk()->getContent();

    // Superadmin melihat menu konten.
    expect($htmlSuper)->toContain('Kelola Artikel')
        ->toContain('Super Administrator')
        ->toContain('Super Uji');

    $htmlKerja = $this->withSession(['admin_auth' => ['name' => 'Admin Uji', 'role' => 'admin_kerjasama']])
        ->get(route('kerjasama.inbox'))->assertOk()->getContent();

    // Admin Kerja Sama tidak melihat menu konten maupun dashboard, dan label
    // perannya dinamis (bukan "Admin Utama Kerja Sama" hardcode).
    expect($htmlKerja)->not->toContain('Kelola Artikel')
        ->not->toContain('Kelola Galeri')
        ->not->toContain('Profil &amp; Visi Misi')
        ->toContain('Admin Uji')
        ->toContain('Admin Kerja Sama')
        ->not->toContain('Admin Utama Kerja Sama');
});
