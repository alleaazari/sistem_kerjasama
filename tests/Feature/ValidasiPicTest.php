<?php

use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Validasi Data PIC — pengajuan & profil mitra
|--------------------------------------------------------------------------
| Server menolak: nama mengandung angka, telepon mengandung huruf, email
| berdomain di luar daftar, dan domain tiruan. Gelar panjang tetap lolos.
*/

const GELAR_PANJANG = 'Prof. Dr. Ir. Budi Santoso, M.Kom., M.T.';

function direktoriPic(): string
{
    $temp = sys_get_temp_dir().'/kerjasama-pic-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);

    return $temp;
}

/** @return array<string, mixed> */
function payloadPic(): array
{
    return [
        'instansi' => 'Universitas Uji',
        'jenis_instansi' => 'perguruan_tinggi',
        'organisasi' => 'Fakultas Ilmu Komputer',
        'kota' => 'Kota Medan',
        'provinsi' => 'Sumatera Utara',
        'alamat' => 'Jl. Uji No. 1',
        'pic' => GELAR_PANJANG,
        'jabatan_pic' => 'Wakil Rektor IV, Kepala Biro',
        'email' => 'budi@gmail.com',
        'telp' => '081234567890',
        'bidang' => ['dts'],
        'metode_pelatihan' => 'hybrid',
        'pembiayaan' => 'apbn',
    ];
}

test('data PIC valid diterima dan gelar panjang lolos', function () {
    $this->app->useStoragePath($temp = direktoriPic());

    $this->post(route('pengajuan.store'), payloadPic())
        ->assertRedirect(route('pengajuan.create'))
        ->assertSessionHasNoErrors();

    $data = json_decode(File::get($temp.'/app/storage_pengajuan.json'), true)[0];
    expect($data['pic'])->toBe(GELAR_PANJANG)
        ->and($data['telp'])->toBe('081234567890');

    File::deleteDirectory($temp);
});

test('nama PIC mengandung angka ditolak', function () {
    $this->app->useStoragePath($temp = direktoriPic());

    $payload = payloadPic();
    $payload['pic'] = 'Budi Santoso 2';

    $this->post(route('pengajuan.store'), $payload)->assertSessionHasErrors(['pic']);

    expect(File::exists($temp.'/app/storage_pengajuan.json'))->toBeFalse();

    File::deleteDirectory($temp);
});

test('jabatan PIC mengandung angka ditolak', function () {
    $this->app->useStoragePath($temp = direktoriPic());

    $payload = payloadPic();
    $payload['jabatan_pic'] = 'Kepala Biro 1';

    $this->post(route('pengajuan.store'), $payload)->assertSessionHasErrors(['jabatan_pic']);

    File::deleteDirectory($temp);
});

test('telepon PIC mengandung huruf ditolak', function () {
    $this->app->useStoragePath($temp = direktoriPic());

    $payload = payloadPic();
    $payload['telp'] = '0812abc7890';

    $this->post(route('pengajuan.store'), $payload)->assertSessionHasErrors(['telp']);

    File::deleteDirectory($temp);
});

test('telepon PIC di luar 9 sampai 15 digit ditolak', function (string $telp) {
    $this->app->useStoragePath($temp = direktoriPic());

    $payload = payloadPic();
    $payload['telp'] = $telp;

    $this->post(route('pengajuan.store'), $payload)->assertSessionHasErrors(['telp']);

    File::deleteDirectory($temp);
})->with(['terlalu pendek' => ['08123456'], 'terlalu panjang' => ['0812345678901234'], 'berstrip' => ['0812-3456-7890']]);

test('telepon PIC dirapikan spasinya sebelum divalidasi', function () {
    $this->app->useStoragePath($temp = direktoriPic());

    $payload = payloadPic();
    $payload['telp'] = '  081234567890  ';

    $this->post(route('pengajuan.store'), $payload)->assertSessionHasNoErrors();

    $data = json_decode(File::get($temp.'/app/storage_pengajuan.json'), true)[0];
    expect($data['telp'])->toBe('081234567890');

    File::deleteDirectory($temp);
});

test('domain email diizinkan diterima', function (string $email) {
    $this->app->useStoragePath($temp = direktoriPic());

    $payload = payloadPic();
    $payload['email'] = $email;

    $this->post(route('pengajuan.store'), $payload)
        ->assertRedirect(route('pengajuan.create'))
        ->assertSessionHasNoErrors();

    File::deleteDirectory($temp);
})->with([
    'gmail huruf besar' => ['Budi@GMAIL.COM'],
    'yahoo.com' => ['budi@yahoo.com'],
    'yahoo.co.id' => ['budi@yahoo.co.id'],
    'kampus .ac.id' => ['budi@univ.ac.id'],
]);

test('domain email tidak diizinkan ditolak', function (string $email) {
    $this->app->useStoragePath($temp = direktoriPic());

    $payload = payloadPic();
    $payload['email'] = $email;

    $this->post(route('pengajuan.store'), $payload)
        ->assertSessionHasErrors(['email' => 'Domain email tidak diizinkan. Gunakan Gmail, Yahoo, atau email kampus (.ac.id).']);

    expect(File::exists($temp.'/app/storage_pengajuan.json'))->toBeFalse();

    File::deleteDirectory($temp);
})->with([
    'example.com' => ['budi@example.com'],
    'go.id belum diizinkan' => ['budi@kemkomdigi.go.id'],
    'mirip ac.id' => ['budi@ac.id'],
]);

test('domain email tiruan ditolak', function (string $email) {
    $this->app->useStoragePath($temp = direktoriPic());

    $payload = payloadPic();
    $payload['email'] = $email;

    $this->post(route('pengajuan.store'), $payload)->assertSessionHasErrors(['email']);

    File::deleteDirectory($temp);
})->with([
    'subdomain tiruan' => ['budi@gmail.com.evil.com'],
    'awalan tempel' => ['budi@evilgmail.com'],
    'akhiran tempel' => ['budi@univ.ac.id.evil.com'],
    'tanpa titik' => ['budi@yahoo.co'],
]);

test('profil mitra memakai aturan PIC yang sama', function () {
    $payload = [
        'instansi' => 'Universitas Uji',
        'organisasi' => 'Fakultas Ilmu Komputer',
        'alamat' => 'Jl. Uji No. 1',
        'pic' => 'Rina Wulandari, S.STP., M.Si.',
        'jabatan_pic' => 'Kepala Dinas',
        'email' => 'rina@jabarprov.ac.id',
        'telp' => '081234567890',
    ];

    $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->post(route('mitra.profile.update'), $payload)
        ->assertSessionHasNoErrors();

    $salah = array_merge($payload, ['pic' => 'Rina 2', 'email' => 'rina@evilgmail.com']);

    $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->post(route('mitra.profile.update'), $salah)
        ->assertSessionHasErrors(['pic', 'email']);
});

test('halaman pengajuan menandai field PIC untuk validasi klien', function () {
    $body = $this->get(route('pengajuan.create'))->getContent();

    expect($body)->toContain('type="tel" id="telp"')
        ->and($body)->toContain('inputmode="numeric"')
        ->and($body)->toContain('maxlength="100"')
        ->and($body)->toContain('var POLA_TELEPON = /^[0-9]{9,15}$/')
        ->and($body)->toContain("replace(/[^0-9]/g, '')")
        ->and($body)->toContain('DOMAIN_EMAIL');
});
