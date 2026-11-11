<?php

use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Formulir Pengajuan — sinkronisasi penanda langkah
|--------------------------------------------------------------------------
| Memastikan penanda langkah memakai tombol yang bisa diklik (sinkron ke
| bagian form), tidak ada lagi onclick inline, dan field baru tersimpan.
*/

function payloadPengajuan(): array
{
    return [
        'instansi' => 'Universitas Uji',
        'jenis_instansi' => 'perguruan_tinggi',
        'organisasi' => 'Fakultas Ilmu Komputer',
        'kota' => 'Medan',
        'provinsi' => 'Sumatera Utara',
        'alamat' => 'Jl. Uji No. 1',
        'pic' => 'Budi',
        'email' => 'budi@example.com',
        'telp' => '0812000000',
        'bidang' => ['dts', 'skkni'],
        'tujuan_mitra' => 'Kolaborasi riset dan peningkatan kompetensi ASN daerah.',
        'metode_pelatihan' => 'hybrid',
        'tujuan_pelatihan' => 'Meningkatkan kompetensi ASN melalui sertifikasi SKKNI.',
        'pembiayaan' => 'cost_sharing',
    ];
}

test('penanda langkah berupa tombol dan sinkron ke id bagian form', function () {
    $response = $this->get(route('pengajuan.create'));

    $response->assertOk();
    // Tombol, bukan div, agar bisa diklik dan diakses keyboard.
    $response->assertSee('id="step-ind-1"', false);
    $response->assertSee('data-target="pjn-step-1"', false);
    $response->assertSee('data-target="pjn-step-2"', false);
    $response->assertSee('data-target="pjn-step-3"', false);
    // Setiap target benar-benar ada di halaman.
    $response->assertSee('id="pjn-step-1"', false);
    $response->assertSee('id="pjn-step-2"', false);
    $response->assertSee('id="pjn-step-3"', false);
    $response->assertSee('aria-label="Langkah 3: Tujuan dan Pembiayaan"', false);
});

test('penanda langkah berubah jadi centang hanya bila bagiannya lengkap', function () {
    $body = $this->get(route('pengajuan.create'))->getContent();

    // Aturan kelengkapan per langkah dipakai penanda (nomor, bukan centang, bila belum lengkap).
    expect($body)->toContain('PJN_STEP_RULES')
        ->and($body)->toContain("fields: ['pddikti-input', 'jenis_instansi', 'organisasi', 'kota', 'provinsi', 'alamat']")
        ->and($body)->toContain("fields: ['pic', 'email', 'telp']")
        ->and($body)->toContain("'.pjn-checkbox-group', '.pjn-pembiayaan-group'");

    // Centang hanya gaya visual untuk .done; tidak ada centang permanen di markup awal.
    expect($body)->toContain('.pjn-step.done .pjn-step-num::after');
    expect(substr_count($body, 'class="pjn-step active"'))->toBe(1);
});

test('status langkah dihitung ulang setiap isian berubah', function () {
    $body = $this->get(route('pengajuan.create'))->getContent();

    // Kedua listener isian memanggil pjnUpdateSteps (input & change).
    expect(substr_count($body, 'pjnUpdateSteps();'))->toBeGreaterThanOrEqual(3);
});

test('dropdown PDDIKTI tidak memakai onclick inline', function () {
    $body = $this->get(route('pengajuan.create'))->getContent();

    // Nama instansi dari API masuk lewat data, bukan string atribut.
    expect($body)->not->toContain('onclick="selectPT');
    expect($body)->toContain('data-idx=');
    // Tidak ada panggilan langsung ke domain Kemdikbud dari browser.
    expect($body)->not->toContain('pddikti.kemdikbud.go.id/api');
    expect($body)->toContain("fetch('/api/pddikti");
});

test('pengajuan menyimpan field baru beserta bidang dan pembiayaan', function () {
    $temp = sys_get_temp_dir().'/kerjasama-pjn-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);
    $this->app->useStoragePath($temp);

    $this->post(route('pengajuan.store'), payloadPengajuan())
        ->assertRedirect(route('pengajuan.create'));

    $file = $temp.'/app/storage_pengajuan.json';
    expect(File::exists($file))->toBeTrue();

    $data = json_decode(File::get($file), true)[0];
    expect($data['jenis_instansi'])->toBe('perguruan_tinggi')
        ->and($data['metode_pelatihan'])->toBe('hybrid')
        ->and($data['pembiayaan'])->toBe('cost_sharing')
        ->and($data['bidang'])->toBe(['dts', 'skkni'])
        ->and($data['tujuan_pelatihan'])->toContain('SKKNI')
        ->and($data['status'])->toBe('Menunggu');

    File::deleteDirectory($temp);
});

test('pengajuan menolak data yang tidak lengkap', function () {
    $temp = sys_get_temp_dir().'/kerjasama-pjn-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);
    $this->app->useStoragePath($temp);

    $payload = payloadPengajuan();
    unset($payload['tujuan_mitra'], $payload['metode_pelatihan']);

    $this->post(route('pengajuan.store'), $payload)
        ->assertSessionHasErrors(['tujuan_mitra', 'metode_pelatihan']);

    expect(File::exists($temp.'/app/storage_pengajuan.json'))->toBeFalse();

    File::deleteDirectory($temp);
});
