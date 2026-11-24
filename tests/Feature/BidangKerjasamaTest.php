<?php

use App\Support\BidangKerjasama;
use App\Support\Mou\StrukturTemplateMou;
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

test('config memuat tiga bidang aktif sesuai tiga referensi', function () {
    $aktif = BidangKerjasama::aktif();

    expect($aktif)->toHaveCount(3)
        ->and(array_keys($aktif))->toBe(['GTA', 'PEMDA_PDP', 'VSGA'])
        ->and(BidangKerjasama::label('VSGA'))->toContain('VSGA')
        ->and(BidangKerjasama::label('GTA'))->toContain('GTA');

    // Tiap bidang aktif memetakan ke template yang benar-benar ada.
    foreach ($aktif as $item) {
        expect($item['template'])->not->toBe('')
            ->and(StrukturTemplateMou::untuk($item['template']))->not->toBeNull();
    }
});

test('setiap bidang memetakan 1-ke-1 ke template berbeda', function () {
    $template = [];
    foreach (BidangKerjasama::aktif() as $kode => $item) {
        $template[$kode] = $item['template'];
    }

    // Tiga bidang, tiga template, tidak ada yang bertabrakan.
    expect(array_values($template))->toBe([
        'nota_kesepakatan_pemda',
        'nota_kesepakatan_pemda_pdp',
        'pks_beasiswa_talenta_digital',
    ])->and(count(array_unique($template)))->toBe(3);
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

test('pilihan bidang di form sama dengan config aktif', function () {
    $response = $this->get(route('pengajuan.create'))->assertOk();

    foreach (BidangKerjasama::aktif() as $kode => $item) {
        $response->assertSee('name="bidang" value="'.$kode.'"', false);
        $response->assertSee($item['label']);
        if ($item['jenis_dokumen'] !== '') {
            $response->assertSee($item['jenis_dokumen']);
        }
        if ($item['deskripsi_singkat'] !== '') {
            $response->assertSee($item['deskripsi_singkat']);
        }
    }

    // Slot nonaktif tidak dirender sebagai kartu pilihan.
    foreach (BidangKerjasama::daftar() as $kode => $item) {
        if ($item['aktif'] === true) {
            continue;
        }
        $response->assertDontSee('name="bidang" value="'.$kode.'"', false);
    }

    // Pilihan lama (teks bebas) tidak boleh tersisa.
    $response->assertDontSee('value="dts"', false);
    $response->assertDontSee('name="bidang[]"', false);
    $response->assertDontSee('Fasilitasi Uji Kompetensi SKKNI');
});

test('hanya kode bidang aktif yang diterima server', function () {
    $nonaktif = null;
    foreach (BidangKerjasama::daftar() as $kode => $item) {
        if ($item['aktif'] === false) {
            $nonaktif = $kode;
            break;
        }
    }

    // Slot nonaktif bila ada: tolak 422.
    if ($nonaktif !== null) {
        $this->postJson(route('pengajuan.store'), payloadBidang(['bidang' => $nonaktif]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['bidang']);
    }

    // Kode asing juga ditolak.
    $this->postJson(route('pengajuan.store'), payloadBidang(['bidang' => 'KODE_ASING']))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['bidang']);
});

test('pemetaan template kerjasama sama dengan mou', function () {
    $dariKerjasama = BidangKerjasama::pemetaanTemplate();
    $dariMou = config('mou.pemetaan');

    // Setiap bidang dengan template di kerjasama.php harus sama persis di mou.php.
    foreach ($dariKerjasama as $kode => $template) {
        expect($dariMou)->toHaveKey($kode)
            ->and($dariMou[$kode])->toBe($template);
    }

    // Tidak ada bidang di mou.php yang bertentangan dengan kerjasama.php.
    foreach ($dariMou as $kode => $template) {
        if (array_key_exists($kode, $dariKerjasama)) {
            expect($template)->toBe($dariKerjasama[$kode]);
        }
    }
});
