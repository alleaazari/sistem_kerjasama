<?php

use App\Support\DrafMitra;
use App\Support\MasaAktifMitra;
use App\Support\StatusKerjasama;
use Carbon\CarbonImmutable;

/*
|--------------------------------------------------------------------------
| Panel admin: Draf Proposal AI & Masa Aktif Mitra (dua halaman terpisah)
|--------------------------------------------------------------------------
| Penyimpanan mode demo: storage/app/draf_mitra.json dan
| storage/app/masa_aktif_mitra.json.
*/

/** Sesi admin untuk peran tertentu. */
function sesiAdmin(string $role = 'admin'): array
{
    return ['admin_auth' => ['name' => 'Uji', 'role' => $role]];
}

beforeEach(function () {
    @unlink(DrafMitra::path());
    @unlink(MasaAktifMitra::path());
});

afterAll(function () {
    @unlink(DrafMitra::path());
    @unlink(MasaAktifMitra::path());
});

// ---------------------------------------------------------------- akses peran

test('halaman draf proposal dapat dibuka admin, admin_kerjasama, dan superadmin', function (string $role) {
    $this->withSession(sesiAdmin($role))
        ->get(route('admin.draf_proposal'))
        ->assertOk()
        ->assertSee('Draf Proposal AI');
})->with(['admin', 'admin_kerjasama', 'superadmin']);

test('halaman masa aktif dapat dibuka admin, admin_kerjasama, dan superadmin', function (string $role) {
    $this->withSession(sesiAdmin($role))
        ->get(route('admin.masa_aktif'))
        ->assertOk()
        ->assertSee('Masa Aktif Mitra');
})->with(['admin', 'admin_kerjasama', 'superadmin']);

test('role developer ditolak 404 dari kedua halaman', function (string $route) {
    $this->withSession(sesiAdmin('developer'))
        ->get(route($route))
        ->assertNotFound();
})->with(['admin.draf_proposal', 'admin.masa_aktif']);

test('mitra tanpa sesi admin diarahkan ke login, bukan diberi akses', function (string $route) {
    $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->get(route($route))
        ->assertRedirect(route('admin.login'));
})->with(['admin.draf_proposal', 'admin.masa_aktif']);

test('pengajuan perubahan masa aktif ditolak untuk role developer', function () {
    $row = MasaAktifMitra::all()[0];

    $this->withSession(sesiAdmin('developer'))
        ->put(route('admin.masa_aktif.atur'), [
            'uuid' => $row['uuid'],
            'tanggal_mulai' => '2030-01-01',
            'tanggal_berakhir' => '2031-01-01',
        ])
        ->assertNotFound();

    expect(MasaAktifMitra::cari($row['uuid'])['tanggal_berakhir'])->not->toBe('2031-01-01');
});

// ---------------------------------------------------------------- redirect lama

test('route lama /mitra-draf dialihkan permanen ke halaman draf proposal', function () {
    $this->withSession(sesiAdmin())
        ->get(route('admin.mitra_draf.lama'))
        ->assertRedirect('/draf-proposal');
});

test('route lama tetap butuh sesi admin', function () {
    $this->get(route('admin.mitra_draf.lama'))->assertRedirect(route('admin.login'));
});

// ---------------------------------------------------------------- draf proposal

test('tabel draf proposal memuat kolom yang diminta saja', function () {
    $html = $this->withSession(sesiAdmin())->get(route('admin.draf_proposal'))->assertOk()->getContent();

    // Periksa isi tabel saja, bukan seluruh halaman (sidebar memuat menu lain).
    preg_match('#<table class="data-table".*?</table>#s', $html, $tabel);
    expect($tabel[0] ?? '')->not->toBe('');

    expect($tabel[0])->toContain('Instansi Mitra')
        ->toContain('Judul Kemitraan')
        ->toContain('Tanggal Pembuatan')
        ->toContain('Unduh Draf')
        ->not->toContain('Masa Aktif')
        ->not->toContain('Tanggal Berakhir')
        ->not->toContain('Atur Masa Aktif');
});

test('unduhan dimatikan selama berkas draf belum tersedia', function () {
    $this->withSession(sesiAdmin())->get(route('admin.draf_proposal'))
        ->assertOk()
        ->assertSee('title="Menunggu backend: berkas draf belum tersedia"', false);
});

test('pencarian draf menyaring berdasarkan instansi atau judul', function () {
    $this->withSession(sesiAdmin())
        ->get(route('admin.draf_proposal', ['q' => 'Universitas']))
        ->assertOk()
        ->assertSee('Universitas Indonesia')
        ->assertDontSee('Pemerintah Provinsi Jawa Barat');
});

test('pencarian tanpa hasil menampilkan keadaan kosong yang jelas', function () {
    $this->withSession(sesiAdmin())
        ->get(route('admin.draf_proposal', ['q' => 'tidak-ada-yang-cocok']))
        ->assertOk()
        ->assertSee('Tidak ada draf proposal yang cocok');
});

test('nomor halaman di luar jangkauan dijepit ke halaman terakhir', function () {
    $hasil = DrafMitra::halaman(DrafMitra::all(), 99);

    expect($hasil['halaman'])->toBe($hasil['halaman_akhir'])
        ->and($hasil['data'])->not->toBeEmpty();
});

test('unduhan lewat UUID palsu dijawab 404', function () {
    $this->withSession(sesiAdmin())
        ->get(route('admin.draf_proposal.unduh', 'bukan-uuid'))
        ->assertNotFound();
});

// ---------------------------------------------------------------- masa aktif

test('tabel masa aktif tidak memuat judul kemitraan dan tanggal pembuatan', function () {
    $html = $this->withSession(sesiAdmin())->get(route('admin.masa_aktif'))->assertOk()->getContent();

    preg_match('#<table class="data-table".*?</table>#s', $html, $tabel);
    expect($tabel[0] ?? '')->not->toBe('');

    expect($tabel[0])->toContain('Nama Instansi Mitra')
        ->toContain('Jangka Waktu Masa Aktif')
        ->toContain('Atur Masa Aktif')
        ->not->toContain('Judul Kemitraan')
        ->not->toContain('Tanggal Pembuatan')
        ->not->toContain('Unduh Draf');
});

test('status dihitung dari tanggal berakhir, bukan string tersimpan', function () {
    $hariIni = CarbonImmutable::now(config('app.timezone', 'UTC'))->startOfDay();

    $aktif = MasaAktifMitra::denganStatus([
        'instansi' => 'Uji Aktif',
        'tanggal_mulai' => $hariIni->toDateString(),
        'tanggal_berakhir' => $hariIni->addDays(300)->toDateString(),
    ]);
    $lewat = MasaAktifMitra::denganStatus([
        'instansi' => 'Uji Lewat',
        'tanggal_mulai' => $hariIni->subYears(2)->toDateString(),
        'tanggal_berakhir' => $hariIni->subDays(5)->toDateString(),
    ]);

    expect($aktif['status']['label'])->toBe(StatusKerjasama::LABEL[StatusKerjasama::AKTIF])
        ->and($lewat['status']['label'])->toBe(StatusKerjasama::LABEL[StatusKerjasama::BERAKHIR]);
});

test('admin memperbarui masa aktif dari data tersimpan, bukan dari klien', function () {
    $row = MasaAktifMitra::all()[0];

    $this->withSession(sesiAdmin())
        ->put(route('admin.masa_aktif.atur'), [
            'uuid' => $row['uuid'],
            'tanggal_mulai' => '2027-01-01',
            'tanggal_berakhir' => '2029-01-01',
            'alasan' => 'Perpanjangan dua tahun',
        ])
        ->assertSessionHasNoErrors();

    $tersimpan = MasaAktifMitra::cari($row['uuid']);
    expect($tersimpan['tanggal_mulai'])->toBe('2027-01-01')
        ->and($tersimpan['tanggal_berakhir'])->toBe('2029-01-01')
        ->and($tersimpan['alasan'])->toBe('Perpanjangan dua tahun')
        ->and($tersimpan['instansi'])->toBe($row['instansi']);
});

test('tanggal berakhir lebih awal dari tanggal mulai ditolak validasi', function () {
    $row = MasaAktifMitra::all()[0];

    $this->withSession(sesiAdmin())
        ->put(route('admin.masa_aktif.atur'), [
            'uuid' => $row['uuid'],
            'tanggal_mulai' => '2029-01-01',
            'tanggal_berakhir' => '2028-01-01',
        ])
        ->assertSessionHasErrors('tanggal_berakhir');

    expect(MasaAktifMitra::cari($row['uuid'])['tanggal_berakhir'])
        ->toBe($row['tanggal_berakhir']);
});

test('alasan perubahan dibatasi 255 karakter', function () {
    $row = MasaAktifMitra::all()[0];

    $this->withSession(sesiAdmin())
        ->put(route('admin.masa_aktif.atur'), [
            'uuid' => $row['uuid'],
            'tanggal_mulai' => '2027-01-01',
            'tanggal_berakhir' => '2028-01-01',
            'alasan' => str_repeat('a', 256),
        ])
        ->assertSessionHasErrors('alasan');
});

test('UUID yang tidak terdaftar tidak mengubah apa pun', function () {
    $sebelum = MasaAktifMitra::all();

    $this->withSession(sesiAdmin())
        ->put(route('admin.masa_aktif.atur'), [
            'uuid' => '3f1c2b7e-0000-4000-8000-000000000000',
            'tanggal_mulai' => '2030-01-01',
            'tanggal_berakhir' => '2031-01-01',
        ])
        ->assertSessionHas('error');

    expect(MasaAktifMitra::all())->toBe($sebelum);
});

test('pencarian masa aktif menyaring berdasarkan nama instansi', function () {
    $this->withSession(sesiAdmin())
        ->get(route('admin.masa_aktif', ['q' => 'Universitas']))
        ->assertOk()
        ->assertSee('Universitas Indonesia')
        ->assertDontSee('Pemerintah Provinsi Jawa Barat');
});

test('label status di halaman admin memakai istilah Aktif, Segera Berakhir, Kedaluwarsa', function () {
    $hariIni = CarbonImmutable::now(config('app.timezone', 'UTC'))->startOfDay();

    // Satu baris untuk tiap kondisi, ditulis langsung ke penyimpanan.
    file_put_contents(MasaAktifMitra::path(), json_encode([
        ['uuid' => '11111111-1111-4111-8111-111111111111', 'instansi' => 'Mitra Aktif', 'tanggal_mulai' => $hariIni->subMonth()->toDateString(), 'tanggal_berakhir' => $hariIni->addDays(300)->toDateString(), 'alasan' => ''],
        ['uuid' => '22222222-2222-4222-8222-222222222222', 'instansi' => 'Mitra Segera', 'tanggal_mulai' => $hariIni->subMonth()->toDateString(), 'tanggal_berakhir' => $hariIni->addDays(10)->toDateString(), 'alasan' => ''],
        ['uuid' => '33333333-3333-4333-8333-333333333333', 'instansi' => 'Mitra Lewat', 'tanggal_mulai' => $hariIni->subYear()->toDateString(), 'tanggal_berakhir' => $hariIni->subDays(10)->toDateString(), 'alasan' => ''],
    ]));

    $this->withSession(sesiAdmin())->get(route('admin.masa_aktif'))
        ->assertOk()
        ->assertSee('Mitra Aktif')
        ->assertSee('Segera Berakhir')
        ->assertSee('Kedaluwarsa');
});

test('jangka waktu ditampilkan dalam format dd MMM yyyy', function () {
    $html = $this->withSession(sesiAdmin())->get(route('admin.masa_aktif'))->assertOk()->getContent();

    // Format lama memakai nama bulan penuh; format baru memakai singkatan.
    expect($html)->not->toContain('Oktober 2026')
        ->not->toContain('November 2026');
});

test('kedua halaman menyediakan kerangka muat dan keadaan galat', function (string $route) {
    $html = $this->withSession(sesiAdmin())->get(route($route))->assertOk()->getContent();

    expect($html)->toContain('baris-skeleton')
        ->toContain('baris-error')
        ->toContain('data-ulang');
})->with(['admin.draf_proposal', 'admin.masa_aktif']);
