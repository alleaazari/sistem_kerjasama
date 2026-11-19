<?php

use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Bidang kerja sama pada dashboard mitra
|--------------------------------------------------------------------------
| Sumber data: pengajuan dengan email PIC yang sama dengan session mitra.
| Tidak ada id/email dari URL. Data lama atau tidak dikenal tampil "-".
*/

function direktoriStatusBidang(): string
{
    $temp = sys_get_temp_dir().'/kerjasama-status-bidang-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);

    return $temp;
}

/** @param array<int, array<string, mixed>> $baris */
function tulisPengajuanStatus(array $baris): void
{
    File::put(
        storage_path('app/storage_pengajuan.json'),
        json_encode($baris, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

test('mitra melihat bidang dari pengajuan miliknya sendiri', function () {
    $this->app->useStoragePath($temp = direktoriStatusBidang());

    tulisPengajuanStatus([
        ['id' => 1, 'email' => 'saya@univ.ac.id', 'instansi' => 'Universitas Saya', 'bidang' => 'VSGA'],
    ]);

    $this->withSession(['mitra_data' => ['username' => 'user1', 'email' => 'saya@univ.ac.id']])
        ->get(route('mitra.status'))
        ->assertOk()
        ->assertSee('Bidang: VSGA')
        ->assertSee('Vocational School Graduate Academy (VSGA)');

    File::deleteDirectory($temp);
});

test('mitra tidak melihat bidang milik mitra lain walau lewat parameter URL', function () {
    $this->app->useStoragePath($temp = direktoriStatusBidang());

    tulisPengajuanStatus([
        ['id' => 1, 'email' => 'saya@univ.ac.id', 'instansi' => 'Universitas Saya', 'bidang' => 'VSGA'],
        ['id' => 2, 'email' => 'lain@univ.ac.id', 'instansi' => 'Universitas Lain', 'bidang' => 'GTA'],
    ]);

    $response = $this->withSession(['mitra_data' => ['username' => 'user1', 'email' => 'saya@univ.ac.id']])
        ->get(route('mitra.status', ['bidang' => 'GTA', 'id' => 2, 'email' => 'lain@univ.ac.id']));

    $response->assertOk()
        ->assertSee('Bidang: VSGA')
        ->assertDontSee('Bidang: GTA')
        ->assertDontSee('Government Transformation Academy (GTA)');

    File::deleteDirectory($temp);
});

test('bidang lama berbentuk array atau teks bebas tampil sebagai strip', function (mixed $bidang) {
    $this->app->useStoragePath($temp = direktoriStatusBidang());

    tulisPengajuanStatus([
        ['id' => 1, 'email' => 'saya@univ.ac.id', 'instansi' => 'Universitas Saya', 'bidang' => $bidang],
    ]);

    $this->withSession(['mitra_data' => ['username' => 'user1', 'email' => 'saya@univ.ac.id']])
        ->get(route('mitra.status'))
        ->assertOk()
        ->assertSee('Bidang: -');

    File::deleteDirectory($temp);
})->with([
    'array lama' => [['dts', 'skkni']],
    'teks bebas lama' => ['dts'],
    'kosong' => [null],
]);

test('pengajuan milik mitra tidak ditemukan tampil sebagai strip tanpa error', function () {
    $this->app->useStoragePath($temp = direktoriStatusBidang());

    tulisPengajuanStatus([
        ['id' => 2, 'email' => 'lain@univ.ac.id', 'instansi' => 'Universitas Lain', 'bidang' => 'GTA'],
    ]);

    $this->withSession(['mitra_data' => ['username' => 'user1', 'email' => 'saya@univ.ac.id']])
        ->get(route('mitra.status'))
        ->assertOk()
        ->assertSee('Bidang: -')
        ->assertDontSee('Government Transformation Academy (GTA)');

    File::deleteDirectory($temp);
});
