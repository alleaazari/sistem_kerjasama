<?php

use App\Support\UnitKerja;
use Illuminate\Support\Facades\File;
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

// ---------------------------------------------------------------------------
// Form: dropdown unit kerja bertingkat
// ---------------------------------------------------------------------------

function payloadUnit(array $ganti = []): array
{
    return array_merge([
        'instansi' => 'Universitas Uji',
        'jenis_instansi' => 'perguruan_tinggi',
        'organisasi' => 'Fakultas Teknik',
        'provinsi' => 'Sumatera Utara',
        'kota' => 'Kota Medan',
        'alamat' => 'Jl. Uji No. 1',
        'pic' => 'Budi Santoso',
        'jabatan_pic' => 'Dekan',
        'email' => 'budi@gmail.com',
        'telp' => '081234567890',
        'bidang' => 'VSGA',
        'metode_pelatihan' => 'hybrid',
        'pembiayaan' => 'apbn',
    ], $ganti);
}

test('field unit kerja dirender sebagai dropdown nonaktif dengan opsi isi sendiri', function () {
    $body = $this->get(route('pengajuan.create'))->assertOk()->getContent();

    // <select id="organisasi"> nonaktif sampai instansi dipilih.
    expect($body)->toContain('id="organisasi"')
        ->toContain('-- Pilih Instansi Terlebih Dahulu --')
        ->toContain('id="organisasi-custom"')
        ->toContain('id="organisasi-kembali"');

    // Placeholder awal + atribut disabled ada pada elemen select.
    expect(preg_match('/<select id="organisasi"[^>]*\bdisabled\b/', $body))->toBe(1);
});

test('nilai unit kerja dari dropdown tersimpan apa adanya', function () {
    $this->app->useStoragePath($temp = sys_get_temp_dir().'/kerjasama-unit-'.uniqid());
    File::makeDirectory($temp.'/app', 0755, true);

    $this->post(route('pengajuan.store'), payloadUnit(['organisasi' => 'Biro Kerja Sama / Humas']))
        ->assertRedirect(route('pengajuan.create'))
        ->assertSessionHasNoErrors();

    $data = json_decode((string) file_get_contents($temp.'/app/storage_pengajuan.json'), true);
    expect($data[0]['organisasi'])->toBe('Biro Kerja Sama / Humas');

    File::deleteDirectory($temp);
});

test('nilai unit kerja hasil isi sendiri tersimpan apa adanya', function () {
    $this->app->useStoragePath($temp = sys_get_temp_dir().'/kerjasama-unit-'.uniqid());
    File::makeDirectory($temp.'/app', 0755, true);

    $this->post(route('pengajuan.store'), payloadUnit(['organisasi' => 'Divisi Kerja Sama Khusus']))
        ->assertRedirect(route('pengajuan.create'))
        ->assertSessionHasNoErrors();

    $data = json_decode((string) file_get_contents($temp.'/app/storage_pengajuan.json'), true);
    expect($data[0]['organisasi'])->toBe('Divisi Kerja Sama Khusus');

    File::deleteDirectory($temp);
});

test('unit kerja kosong ditolak server', function () {
    $this->postJson(route('pengajuan.store'), payloadUnit(['organisasi' => '']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['organisasi']);
});
