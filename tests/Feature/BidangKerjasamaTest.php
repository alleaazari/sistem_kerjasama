<?php

use App\Support\BidangKerjasama;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Bidang kerja sama dibatasi daftar config
|--------------------------------------------------------------------------
| Hanya kode di config('kerjasama.bidang') yang diterima server, dan
| pilihan di form pengajuan persis sama dengan daftar tersebut.
*/

function payloadBidang(array $ganti = []): array
{
    return array_merge([
        'instansi' => 'Universitas Uji',
        'jenis_instansi' => 'perguruan_tinggi',
        'organisasi' => 'Fakultas Ilmu Komputer',
        'kota' => 'Kota Medan',
        'provinsi' => 'Sumatera Utara',
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

function direktoriBidang(): string
{
    $temp = sys_get_temp_dir().'/kerjasama-bidang-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);

    return $temp;
}

test('config memuat bidang VSGA dan GTA', function () {
    expect(BidangKerjasama::kode())->toContain('VSGA', 'GTA')
        ->and(BidangKerjasama::label('VSGA'))->toContain('VSGA')
        ->and(BidangKerjasama::label('GTA'))->toContain('GTA');
});

test('bidang valid diterima dan disimpan sebagai kode', function () {
    $this->app->useStoragePath($temp = direktoriBidang());

    $this->post(route('pengajuan.store'), payloadBidang(['bidang' => 'GTA']))
        ->assertRedirect(route('pengajuan.create'))
        ->assertSessionHasNoErrors();

    $data = json_decode(File::get($temp.'/app/storage_pengajuan.json'), true)[0];
    expect($data['bidang'])->toBe('GTA');

    File::deleteDirectory($temp);
});

test('bidang di luar daftar ditolak 422', function (mixed $bidang) {
    $this->postJson(route('pengajuan.store'), payloadBidang(['bidang' => $bidang]))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['bidang']);
})->with([
    'kode lama' => ['dts'],
    'tulisan bebas' => ['Pelatihan Teknis TIK'],
    'huruf kecil' => ['vsga'],
    'array' => [['VSGA', 'GTA']],
]);

test('bidang wajib dipilih', function () {
    $payload = payloadBidang();
    unset($payload['bidang']);

    $this->post(route('pengajuan.store'), $payload)
        ->assertSessionHasErrors(['bidang']);
});

test('pilihan bidang di form sama dengan config', function () {
    $response = $this->get(route('pengajuan.create'))->assertOk();

    foreach (BidangKerjasama::daftar() as $kode => $label) {
        $response->assertSee('name="bidang" value="'.$kode.'"', false);
        $response->assertSee($label);
    }

    // Pilihan lama (teks bebas) tidak boleh tersisa.
    $response->assertDontSee('value="dts"', false);
    $response->assertDontSee('name="bidang[]"', false);
    $response->assertDontSee('Fasilitasi Uji Kompetensi SKKNI');
});
