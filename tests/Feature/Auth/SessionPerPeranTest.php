<?php

use App\Http\Middleware\SessionPerPeran;
use App\Support\AdminAkses;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Middleware SessionPerPeran
|--------------------------------------------------------------------------
| Memastikan cookie + path sesi dipilih dari segmen pertama URL, memakai
| peta config/session_roles.php (turunan prefix peran dari .env). Diuji
| langsung terhadap middleware supaya tidak bergantung driver sesi test.
*/

test('cookie + path sesi dipilih sesuai prefix peran', function (string $peran) {
    $path = AdminAkses::path($peran);
    $nama = 'kerjasama_'.$peran.'_session';

    config([
        'session.cookie' => 'bawaan-cookie',
        'session.path' => '/',
    ]);

    $request = Request::create('/'.$path.'/login', 'GET');
    (new SessionPerPeran)->handle($request, fn () => response('ok'));

    expect(config('session.cookie'))->toBe($nama)
        ->and(config('session.path'))->toBe('/'.$path);
})->with(['superadmin', 'admin_kerjasama', 'admin_konten']);

test('halaman publik dan area mitra tidak mengubah cookie bawaan', function (string $url) {
    config([
        'session.cookie' => 'bawaan-cookie',
        'session.path' => '/',
    ]);

    (new SessionPerPeran)->handle(Request::create($url, 'GET'), fn () => response('ok'));

    expect(config('session.cookie'))->toBe('bawaan-cookie')
        ->and(config('session.path'))->toBe('/');
})->with(['/', '/mitra', '/login', '/pengajuan']);

test('prefix cocok sebagai segmen utuh, bukan awalan string', function () {
    config([
        'session.cookie' => 'bawaan-cookie',
        'session.path' => '/',
    ]);

    // "superadmin-lain" bukan prefix peran superadmin.
    (new SessionPerPeran)->handle(Request::create('/superadmin-lain/x', 'GET'), fn () => response('ok'));

    expect(config('session.cookie'))->toBe('bawaan-cookie');
});

test('nama cookie per peran unik', function () {
    $nama = collect(config('session_roles.per_peran'))->pluck('nama')->all();

    expect($nama)->toBe(array_values(array_unique($nama)))
        ->and($nama)->not->toContain(null);
});
