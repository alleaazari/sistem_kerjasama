<?php

use App\Support\PermohonanPerpanjangan as P;

/*
|--------------------------------------------------------------------------
| Perpanjangan masa aktif: permohonan mitra → keputusan admin
|--------------------------------------------------------------------------
| Penyimpanan mode demo: storage/app/permohonan_perpanjangan.json.
| Setiap tes mereset berkas agar tidak saling mempengaruhi.
*/

beforeEach(function () {
    file_put_contents(P::path(), '[]');
});

afterAll(function () {
    file_put_contents(P::path(), '[]');
});

test('mitra mengajukan perpanjangan dan admin melihatnya di antrean', function () {
    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->post(route('mitra.status.perpanjang'), ['durasi' => 2, 'catatan' => 'Perlu lanjut pelatihan']);

    $response->assertRedirect(route('mitra.status'));
    $response->assertSessionHasNoErrors();

    expect(P::jumlahMenunggu())->toBe(1);

    $html = $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'superadmin']])
        ->get(route('superadmin.dashboard'))->getContent();

    expect($html)->toContain('Permohonan Perpanjangan Masa Aktif');
    expect($html)->toContain('Perlu lanjut pelatihan');
});

test('permohonan kedua tidak menumpuk selama yang pertama belum diputuskan', function () {
    $sesi = ['mitra_data' => ['username' => 'user1']];

    $this->withSession($sesi)->post(route('mitra.status.perpanjang'), ['durasi' => 1]);
    $kedua = $this->withSession($sesi)->post(route('mitra.status.perpanjang'), ['durasi' => 2]);

    $kedua->assertSessionHasErrors('durasi');
    expect(P::jumlahMenunggu())->toBe(1);
});

test('admin menyetujui: periode baru menyambung dari tanggal berakhir lama', function () {
    $id = P::ajukan('user1', 'PT Uji', 2, '', '2026-12-31')['id'];

    $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'superadmin']])
        ->post(route('superadmin.dashboard.setujui', $id), ['durasi' => 2])
        ->assertSessionHasNoErrors();

    $row = P::cari($id);
    expect($row['status'])->toBe(P::DISETUJUI)
        ->and($row['mulai_baru'])->toBe('2026-12-31')
        ->and($row['selesai_baru'])->toBe('2028-12-31');
});

test('admin menyetujui: masa aktif yang sudah lewat mulai dari hari ini', function () {
    $id = P::ajukan('user1', 'PT Uji', 1, '', '2020-01-01')['id'];

    $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'superadmin']])
        ->post(route('superadmin.dashboard.setujui', $id), ['durasi' => 1]);

    expect(P::cari($id)['mulai_baru'])->toBe(now(config('app.timezone'))->toDateString());
});

test('keputusan yang sudah ditetapkan tidak dapat ditimpa', function () {
    $id = P::ajukan('user1', 'PT Uji', 1, '', '2026-12-31')['id'];
    P::setujui($id, '2026-12-31', 1);

    $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'superadmin']])
        ->post(route('superadmin.dashboard.tolak', $id), ['alasan' => 'Alasan baru yang tidak boleh dipakai.'])
        ->assertSessionHas('error');

    expect(P::cari($id)['status'])->toBe(P::DISETUJUI);
});

test('admin menolak: alasan wajib dan tampil di halaman Status mitra', function () {
    $id = P::ajukan('user1', 'PT Uji', 3, '', '2027-06-30')['id'];

    // Alasan terlalu pendek ditolak validasi.
    $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'superadmin']])
        ->post(route('superadmin.dashboard.tolak', $id), ['alasan' => 'tidak'])
        ->assertSessionHasErrors('alasan');

    expect(P::cari($id)['status'])->toBe(P::MENUNGGU);

    $this->withSession(['admin_auth' => ['name' => 'Admin', 'role' => 'superadmin']])
        ->post(route('superadmin.dashboard.tolak', $id), ['alasan' => 'Berkas pendukung belum lengkap.'])
        ->assertSessionHasNoErrors();

    $html = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->get(route('mitra.status'))->getContent();

    expect($html)->toContain('ditolak');
    expect($html)->toContain('Berkas pendukung belum lengkap.');
    expect($html)->toContain('Perpanjang Masa Aktif');
});

test('mitra lain tidak melihat keputusan milik mitra lain', function () {
    $id = P::ajukan('user1', 'PT Uji', 2, '', '2026-12-31')['id'];
    P::tolak($id, 'Alasan khusus mitra pertama.');

    $html = $this->withSession(['mitra_data' => ['username' => 'mitra-lain']])
        ->get(route('mitra.status'))->getContent();

    expect($html)->not->toContain('Alasan khusus mitra pertama.');
});

test('dashboard admin tidak dapat dibuka tanpa sesi admin', function () {
    $this->get(route('superadmin.dashboard'))->assertRedirect();
});
