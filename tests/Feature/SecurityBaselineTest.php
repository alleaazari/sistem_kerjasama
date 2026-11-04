<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Fase 1 — Baseline Keamanan
|--------------------------------------------------------------------------
| Test untuk header keamanan, rate limit, dan pembatasan route test-email.
*/

test('responses carry baseline security headers', function () {
    $response = $this->get(route('landing'));

    $response->assertOk();
    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    $response->assertHeader('Content-Security-Policy-Report-Only');
});

test('mitra login is rate limited', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('login.post'));
    }

    $this->post(route('login.post'))->assertTooManyRequests();
});

test('pengajuan submission is rate limited', function () {
    // Arahkan storage ke direktori sementara agar tidak menyentuh data nyata.
    $temp = sys_get_temp_dir().'/kerjasama-test-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);
    $this->app->useStoragePath($temp);

    $payload = [
        'instansi' => 'Universitas Uji',
        'jenis_instansi' => 'perguruan_tinggi',
        'organisasi' => 'Fakultas Ilmu Komputer',
        'kota' => 'Medan',
        'provinsi' => 'Sumatera Utara',
        'alamat' => 'Jl. Uji No. 1',
        'pic' => 'Budi',
        'email' => 'budi@example.com',
        'telp' => '0812000000',
        'tujuan_mitra' => 'Kolaborasi riset',
        'metode_pelatihan' => 'daring',
        'tujuan_pelatihan' => 'Literasi digital',
        'pembiayaan' => 'APBN',
    ];

    for ($i = 0; $i < 5; $i++) {
        $this->post(route('pengajuan.store'), $payload);
    }

    $this->post(route('pengajuan.store'), $payload)->assertTooManyRequests();

    File::deleteDirectory($temp);
});

test('pddikti proxy is rate limited', function () {
    Http::fake([
        '*' => Http::response([], 200),
    ]);

    for ($i = 0; $i < 30; $i++) {
        $this->get('/api/pddikti?q=universitas');
    }

    $this->get('/api/pddikti?q=universitas')->assertTooManyRequests();
});

test('pddikti query below minimum length returns empty json', function () {
    $this->get('/api/pddikti?q=a')->assertOk()->assertExactJson([]);
});

test('test-email route is not available outside the local environment', function () {
    $response = $this->withSession(['admin_auth' => ['name' => 'Dev', 'role' => 'developer']])
        ->get(route('admin.test.email'));

    $response->assertNotFound();
});
