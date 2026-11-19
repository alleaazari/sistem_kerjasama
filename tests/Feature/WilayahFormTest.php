<?php

use App\Support\Wilayah;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Provinsi & Kota Bertingkat (form pengajuan)
|--------------------------------------------------------------------------
| Sumber data: resources/data/wilayah.json. Server menolak kombinasi
| provinsi/kota yang tidak ada di peta, bukan hanya mengandalkan dropdown.
*/

function payloadWilayah(string $provinsi, string $kota): array
{
    return [
        'instansi' => 'Universitas Uji',
        'jenis_instansi' => 'perguruan_tinggi',
        'organisasi' => 'Fakultas Ilmu Komputer',
        'kota' => $kota,
        'provinsi' => $provinsi,
        'alamat' => 'Jl. Uji No. 1',
        'pic' => 'Budi',
        'email' => 'budi@gmail.com',
        'telp' => '0812000000',
        'bidang' => ['dts'],
        'metode_pelatihan' => 'hybrid',
        'pembiayaan' => 'apbn',
    ];
}

/** Direktori storage sementara baru (tanpa menyentuh data nyata). */
function direktoriWilayah(): string
{
    $temp = sys_get_temp_dir().'/kerjasama-wilayah-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);

    return $temp;
}

test('data wilayah memuat provinsi dan kabupaten/kota', function () {
    expect(Wilayah::provinsi())->toHaveCount(38)
        ->and(Wilayah::kabupaten('Sumatera Utara'))->toContain('Kota Medan')
        ->and(Wilayah::kabupaten('Daerah Istimewa Yogyakarta'))->toContain('Kota Yogyakarta')
        ->and(Wilayah::valid('Sumatera Utara', 'Kota Medan'))->toBeTrue()
        ->and(Wilayah::valid('Sumatera Utara', 'Kota Bandung'))->toBeFalse();
});

test('form merender dropdown provinsi dan kota dari peta wilayah', function () {
    $response = $this->get(route('pengajuan.create'));

    $response->assertOk();
    // Opsi provinsi berasal dari peta wilayah, bukan daftar hardcode terpisah.
    $response->assertSee('<option value="Sumatera Utara"', false);
    $response->assertSee('name="provinsi"', false);
    // Kota nonaktif sampai provinsi dipilih.
    $response->assertSee('name="kota" class="pjn-input" required', false);
    $response->assertSee('disabled', false);
});

test('kombinasi provinsi dan kota yang valid diterima', function () {
    $this->app->useStoragePath($temp = direktoriWilayah());

    $this->post(route('pengajuan.store'), payloadWilayah('Sumatera Utara', 'Kota Medan'))
        ->assertRedirect(route('pengajuan.create'))
        ->assertSessionHasNoErrors();

    $data = json_decode(File::get($temp.'/app/storage_pengajuan.json'), true)[0];
    expect($data['provinsi'])->toBe('Sumatera Utara')
        ->and($data['kota'])->toBe('Kota Medan');

    File::deleteDirectory($temp);
});

test('kombinasi provinsi dan kota yang tidak cocok ditolak', function () {
    $this->app->useStoragePath($temp = direktoriWilayah());

    $this->post(route('pengajuan.store'), payloadWilayah('Sumatera Utara', 'Kota Bandung'))
        ->assertSessionHasErrors(['kota' => 'Kota / Kabupaten tidak terdaftar pada provinsi yang dipilih.']);

    expect(File::exists($temp.'/app/storage_pengajuan.json'))->toBeFalse();

    File::deleteDirectory($temp);
});

test('provinsi tidak dikenal dan provinsi tanpa kota ditolak', function () {
    $this->app->useStoragePath($temp = direktoriWilayah());

    $this->post(route('pengajuan.store'), payloadWilayah('Provinsi Karangan', 'Kota Medan'))
        ->assertSessionHasErrors(['provinsi']);

    $this->post(route('pengajuan.store'), payloadWilayah('Sumatera Utara', ''))
        ->assertSessionHasErrors(['kota']);

    expect(File::exists($temp.'/app/storage_pengajuan.json'))->toBeFalse();

    File::deleteDirectory($temp);
});
