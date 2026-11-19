<?php

use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Generator draf MoU per bidang
|--------------------------------------------------------------------------
| Template diambil dari storage/app/template_mou.json, diisi dari data
| instansi mitra, dan bidang selalu berasal dari pengajuan milik mitra
| (email session), bukan dari parameter URL.
*/

function direktoriMou(): string
{
    $temp = sys_get_temp_dir().'/kerjasama-mou-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);

    return $temp;
}

/** @param array<int, array<string, mixed>> $baris */
function tulisPengajuanMou(array $baris): void
{
    File::put(
        storage_path('app/storage_pengajuan.json'),
        json_encode($baris, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/** @return array<string, mixed> */
function sesiMitraMou(array $ganti = []): array
{
    return [
        'mitra_data' => array_merge([
            'username' => 'user1',
            'email' => 'saya@univ.ac.id',
            'instansi' => 'Universitas Uji',
            'pic' => 'Rina Wulandari',
            'jabatan_pic' => 'Kepala Humas',
            'alamat' => 'Jl. Uji No. 1, Medan',
        ], $ganti),
    ];
}

test('tiap bidang memakai template dengan arah berbeda', function (string $kode, string $penanda, string $bukanPenanda) {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => $kode]]);

    $this->withSession(sesiMitraMou())
        ->get(route('mitra.mou.draf'))
        ->assertOk()
        ->assertSee($penanda)
        ->assertDontSee($bukanPenanda);

    File::deleteDirectory($temp);
})->with([
    'VSGA' => ['VSGA', 'PENGEMBANGAN KOMPETENSI VOKASI DIGITAL', 'PENINGKATAN KAPASITAS TRANSFORMASI DIGITAL PEMERINTAHAN'],
    'GTA' => ['GTA', 'PENINGKATAN KAPASITAS TRANSFORMASI DIGITAL PEMERINTAHAN', 'PENGEMBANGAN KOMPETENSI VOKASI DIGITAL'],
]);

test('placeholder terisi data instansi dan label bidang', function () {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'VSGA']]);

    $this->withSession(sesiMitraMou())
        ->get(route('mitra.mou.draf'))
        ->assertOk()
        ->assertSee('Universitas Uji')
        ->assertSee('Rina Wulandari')
        ->assertSee('Kepala Humas')
        ->assertSee('Jl. Uji No. 1, Medan')
        ->assertSee(now()->translatedFormat('d F Y'))
        ->assertSee('Vocational School Graduate Academy (VSGA)')
        ->assertDontSee('{{nama_instansi}}')
        ->assertDontSee('{{nama_pic}}')
        ->assertDontSee('{{jabatan_pic}}')
        ->assertDontSee('{{alamat}}')
        ->assertDontSee('{{tanggal}}')
        ->assertDontSee('{{bidang}}');

    File::deleteDirectory($temp);
});

test('data mitra berisi tag HTML tidak dirender sebagai HTML', function () {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'VSGA']]);

    $this->withSession(sesiMitraMou([
        'instansi' => '<script>alert("xss")</script>',
        'pic' => '<img src=x onerror=alert(1)>',
        'alamat' => '</textarea><script>alert(2)</script>',
    ]))
        ->get(route('mitra.mou.draf'))
        ->assertOk()
        ->assertDontSee('<script>alert("xss")</script>', false)
        ->assertDontSee('<img src=x onerror=alert(1)>', false)
        ->assertDontSee('</textarea><script>alert(2)</script>', false);

    File::deleteDirectory($temp);
});

test('kiriman draf berisi script disimpan dan ditampilkan sebagai teks', function () {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'VSGA']]);

    $response = $this->withSession(sesiMitraMou())
        ->post(route('mitra.mou.draf.simpan'), [
            'aksi' => 'kirim',
            'isi' => "Draf uji <script>alert(1)</script>\n\nParagraf kedua.",
        ]);

    $response->assertRedirect();
    $daftar = session('mitra_mou');
    expect($daftar)->toBeArray()->not->toBeEmpty();

    $detail = $this->withSession(array_merge(sesiMitraMou(), ['mitra_mou' => $daftar]))
        ->get(route('mitra.mou.detail', $daftar[0]['id']));

    $detail->assertOk()
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertSee('alert(1)')
        ->assertSee('Paragraf kedua.');

    File::deleteDirectory($temp);
});

test('mitra hanya memakai bidang miliknya, parameter URL diabaikan', function () {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([
        ['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'VSGA'],
        ['id' => 2, 'email' => 'lain@univ.ac.id', 'bidang' => 'GTA'],
    ]);

    $this->withSession(sesiMitraMou())
        ->get(route('mitra.mou.draf', ['bidang' => 'GTA', 'kode' => 'GTA', 'email' => 'lain@univ.ac.id']))
        ->assertOk()
        ->assertSee('PENGEMBANGAN KOMPETENSI VOKASI DIGITAL')
        ->assertDontSee('PENINGKATAN KAPASITAS TRANSFORMASI DIGITAL PEMERINTAHAN');

    File::deleteDirectory($temp);
});

test('kirim draf tidak bisa memakai template bidang lain', function () {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'VSGA']]);

    $this->withSession(sesiMitraMou())
        ->post(route('mitra.mou.draf.simpan'), [
            'aksi' => 'kirim',
            'bidang' => 'GTA',
            'kode' => 'GTA',
            'isi' => 'Isi draf uji yang cukup panjang.',
        ])
        ->assertRedirect();

    $row = session('mitra_mou')[0];
    expect($row['bidang'])->toBe('VSGA')
        ->and($row['judul'])->toContain('Vokasi')
        ->and($row['judul'])->not->toContain('Transformasi Digital Pemerintahan');

    File::deleteDirectory($temp);
});

test('bidang tanpa template menampilkan pesan jelas tanpa formulir', function (mixed $bidang) {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => $bidang]]);

    $response = $this->withSession(sesiMitraMou())->get(route('mitra.mou.draf'));

    $response->assertOk()
        ->assertDontSee('name="isi"', false)
        ->assertSee('Hubungi admin kerja sama');

    $this->withSession(sesiMitraMou())
        ->post(route('mitra.mou.draf.simpan'), ['aksi' => 'kirim', 'isi' => 'Isi apa saja.'])
        ->assertSessionHasErrors('isi');

    File::deleteDirectory($temp);
})->with([
    'belum ada bidang' => [null],
    'kode tidak dikenal' => ['XYZ'],
]);

test('simpan draf bisa dibuka kembali sebelum dikirim', function () {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'VSGA']]);

    $teks = "Draf suntingan saya.\n\n[ISI: bagian kedua]";

    $this->withSession(sesiMitraMou())
        ->post(route('mitra.mou.draf.simpan'), ['aksi' => 'simpan', 'isi' => $teks])
        ->assertRedirect(route('mitra.mou.draf'));

    expect(session('mitra_mou_draf'))->toBe($teks);

    $this->withSession(sesiMitraMou())
        ->get(route('mitra.mou.draf'))
        ->assertOk()
        ->assertSee('Draf suntingan saya.');

    File::deleteDirectory($temp);
});

test('generate draf dibatasi per menit', function () {
    $this->app->useStoragePath($temp = direktoriMou());
    tulisPengajuanMou([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'VSGA']]);

    for ($i = 0; $i < 10; $i++) {
        $this->withSession(sesiMitraMou())
            ->post(route('mitra.mou.draf.simpan'), ['aksi' => 'simpan', 'isi' => 'Draf uji '.$i])
            ->assertRedirect(route('mitra.mou.draf'));
    }

    $this->withSession(sesiMitraMou())
        ->post(route('mitra.mou.draf.simpan'), ['aksi' => 'simpan', 'isi' => 'Draf uji terakhir'])
        ->assertStatus(429);

    File::deleteDirectory($temp);
});
