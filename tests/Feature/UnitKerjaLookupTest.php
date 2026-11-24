<?php

use App\Support\UnitKerja;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Lookup Unit Kerja / Divisi / Fakultas (dropdown bertingkat)
|--------------------------------------------------------------------------
| Endpoint /api/pddikti/unit-kerja menyediakan daftar unit sesuai jenis
| instansi, selalu diakhiri opsi "Isi sendiri", dan jatuh ke fallback statis
| saat PDDikti tidak tersedia.
*/

test('instansi kampus tanpa kode PT memakai fallback unit akademik', function () {
    Http::fake();

    $this->getJson('/api/pddikti/unit-kerja?jenis=perguruan_tinggi')
        ->assertOk()
        ->assertJsonPath('source', 'Data fallback')
        ->assertJsonPath('nilai_isian_sendiri', UnitKerja::ISIAN_SENDIRI)
        ->assertJsonPath('isian_sendiri', UnitKerja::LABEL_ISIAN_SENDIRI)
        ->assertJsonCount(14, 'data')
        ->assertJsonFragment(['data' => UnitKerja::perguruanTinggi()]);

    // Tidak ada panggilan jaringan bila kode PT kosong.
    Http::assertNothingSent();
});

test('instansi pemerintah daerah memakai fallback unit pemerintahan', function () {
    Http::fake();

    $this->getJson('/api/pddikti/unit-kerja?jenis=pemerintah_daerah')
        ->assertOk()
        ->assertJsonPath('source', 'Data fallback')
        ->assertJsonFragment(['data' => UnitKerja::pemerintah()]);

    Http::assertNothingSent();
});

test('kementerian juga memakai daftar unit pemerintahan', function () {
    Http::fake();

    $this->getJson('/api/pddikti/unit-kerja?jenis=kementerian')
        ->assertOk()
        ->assertJsonFragment(['data' => UnitKerja::pemerintah()]);
});

test('lookup PDDikti dipakai bila mengembalikan daftar prodi', function () {
    Http::fake([
        'api-frontend.kemdikbud.go.id/*' => Http::response([
            ['nama_prodi' => 'Teknik Informatika'],
            ['nama_prodi' => 'Sistem Informasi'],
            ['nama_prodi' => 'Teknik Informatika'],
        ], 200),
    ]);

    $this->getJson('/api/pddikti/unit-kerja?id=001&jenis=perguruan_tinggi')
        ->assertOk()
        ->assertJsonPath('source', 'API PDDIKTI Kemdikbud Live')
        ->assertJsonPath('data', ['Sistem Informasi', 'Teknik Informatika']);
});

test('PDDIKTI gagal jatuh ke fallback tanpa error', function () {
    Http::fake([
        'api-frontend.kemdikbud.go.id/*' => Http::response('down', 500),
    ]);

    $this->getJson('/api/pddikti/unit-kerja?id=001&jenis=perguruan_tinggi')
        ->assertOk()
        ->assertJsonPath('source', 'Data fallback')
        ->assertJsonFragment(['data' => UnitKerja::perguruanTinggi()]);
});

test('PDDIKTI timeout pun tetap mengembalikan fallback', function () {
    Http::fake(function () {
        throw new RuntimeException('timeout');
    });

    $this->getJson('/api/pddikti/unit-kerja?id=001&jenis=perguruan_tinggi')
        ->assertOk()
        ->assertJsonPath('source', 'Data fallback');
});

test('setiap daftar fallback tidak kosong dan memuat unit kunci', function () {
    expect(UnitKerja::perguruanTinggi())->toContain('Biro Kerja Sama / Humas')
        ->and(UnitKerja::perguruanTinggi())->toContain('Fakultas Teknik')
        ->and(UnitKerja::pemerintah())->toContain('Dinas Komunikasi dan Informatika')
        ->and(UnitKerja::pemerintah())->toContain('Sekretariat Daerah');
});

test('endpoint unit kerja dibatasi rate limit pddikti', function () {
    Http::fake();
    $this->withMiddleware();

    // Limiter pddikti 30/menit per IP: panggilan ke-31 harus 429.
    for ($i = 0; $i < 30; $i++) {
        $this->getJson('/api/pddikti/unit-kerja?jenis=perguruan_tinggi')->assertOk();
    }

    $this->getJson('/api/pddikti/unit-kerja?jenis=perguruan_tinggi')->assertTooManyRequests();
});
