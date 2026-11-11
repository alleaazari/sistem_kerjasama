<?php

use App\Support\LandingCms;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Sinkronisasi admin → landing (CMS persisten)
|--------------------------------------------------------------------------
| Pastikan perubahan admin lewat LandingCms tersimpan ke storage
| dan muncul di halaman landing tanpa perlu sesi yang sama.
*/

function setupCmsStorage(): string
{
    $temp = sys_get_temp_dir().'/cms-test-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);
    app()->useStoragePath($temp);
    return $temp;
}

test('LandingCms::save menulis ke storage dan terbaca kembali', function () {
    $temp = setupCmsStorage();
    $cms = app(LandingCms::class);

    $cms->save('visi', 'Visi baru dari admin');
    $cms->save('misi', ['Misi 1', 'Misi 2']);
    $cms->save('petinggi', [[
        'nama' => 'Dr. Uji',
        'jabatan' => 'Kepala BBLSDM',
        'badge' => 'Uji',
        'foto' => '',
        'desc' => 'Desc uji',
    ]]);

    expect(File::exists($temp.'/app/landing_cms.json'))->toBeTrue();

    $all = $cms->all();
    expect($all['visi'])->toBe('Visi baru dari admin')
        ->and($all['misi'])->toBe(['Misi 1', 'Misi 2'])
        ->and($all['petinggi'][0]['nama'])->toBe('Dr. Uji');

    File::deleteDirectory($temp);
});

test('LandingCms::all mengembalikan default config saat storage kosong', function () {
    $temp = setupCmsStorage();
    $cms = app(LandingCms::class);

    $all = $cms->all();
    expect($all['visi'])->toBe(config('landing_cms.visi'))
        ->and($all['petinggi'])->toBe(config('landing_cms.petinggi'))
        ->and($all['artikel'])->toBe(config('landing_cms.artikel'))
        ->and($all['galeri'])->toBe(config('landing_cms.galeri'));

    File::deleteDirectory($temp);
});

test('sesi admin tidak lagi dipakai untuk konten landing', function () {
    // Landing seharusnya tidak lagi bergantung session('admin_visi') dsb.
    $response = $this->get(route('landing'));
    $body = $response->getContent();

    // Data petinggi bawaan masih tampil
    expect($body)->toContain('Meutya Viada Hafid');
    // Artikel bawaan masih tampil
    expect($body)->toContain('Penandatanganan Nota Kesepahaman');

    $response->assertOk();
});

test('posting tentang admin menulis petinggi ke CMS persisten', function () {
    $temp = setupCmsStorage();
    $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'admin']]);

    $response = $this->post(route('admin.tentang.update'), [
        'p_nama' => ['Dr. Uji Baru'],
        'p_jabatan' => ['Kepala BBLSDM'],
        'p_badge' => ['Uji'],
        'p_desc' => ['Deskripsi uji'],
        'visi' => 'Visi dari admin',
    ]);

    $response->assertRedirect();

    $all = app(LandingCms::class)->all();
    expect($all['visi'])->toBe('Visi dari admin')
        ->and($all['petinggi'][0]['nama'])->toBe('Dr. Uji Baru');

    File::deleteDirectory($temp);
});

test('posting artikel admin menulis ke CMS persisten', function () {
    $temp = setupCmsStorage();
    $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'admin']]);

    $this->post(route('admin.artikel.store'), [
        'judul' => 'Artikel Uji CMS',
        'konten' => 'Konten uji',
    ])->assertRedirect();

    $all = app(LandingCms::class)->all();
    $last = end($all['artikel']);
    expect($last['judul'])->toBe('Artikel Uji CMS');

    File::deleteDirectory($temp);
});

test('posting galeri admin menulis ke CMS persisten', function () {
    $temp = setupCmsStorage();
    $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'admin']]);

    $this->post(route('admin.galeri.store'), [
        'judul' => 'Galeri Uji',
    ])->assertRedirect();

    $all = app(LandingCms::class)->all();
    $last = end($all['galeri']);
    expect($last['judul'])->toBe('Galeri Uji');

    File::deleteDirectory($temp);
});
