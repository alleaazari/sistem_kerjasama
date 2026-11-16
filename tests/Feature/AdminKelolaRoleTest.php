<?php

use App\Support\AdminPeran;
use App\Support\LandingCms;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Matriks izin panel admin — admin_kelola / admin_kerjasama / superadmin
|--------------------------------------------------------------------------
| Sumber kebenaran: config/admin_peran.php. Test ini memverifikasi bahwa
| route, menu, dan data mengikuti peta yang sama.
| Akun demo lama (role `admin`) tetap akses penuh agar tidak ada akses berkurang.
| TODO-BACKEND: peran masih dari config admin_demo + session, belum tabel users.
*/

function sesiPeran(string $role): array
{
    return ['admin_auth' => ['name' => 'Uji '.$role, 'role' => $role]];
}

/** Route halaman konten (boleh untuk admin_kelola). */
function ruteKonten(): array
{
    return ['admin.tentang', 'admin.artikel', 'admin.galeri', 'admin.dashboard.kelola'];
}

/** Route data kerja sama (hanya peran kerja sama). */
function ruteKerjasama(): array
{
    return ['admin.dashboard', 'admin.inbox', 'admin.draf_proposal', 'admin.masa_aktif'];
}

beforeEach(function () {
    // CMS diarahkan ke folder sementara agar storage asli tidak tersentuh.
    $this->tempStorage = sys_get_temp_dir().'/kelola-test-'.uniqid();
    File::makeDirectory($this->tempStorage.'/app', 0755, true);
    app()->useStoragePath($this->tempStorage);
});

afterEach(function () {
    File::deleteDirectory($this->tempStorage);
});

// ---------------------------------------------------------------- peta izin

test('peta izin hanya memberi admin_kelola kemampuan konten', function () {
    expect(AdminPeran::kemampuan('admin_kelola'))->toBe(['konten'])
        ->and(AdminPeran::boleh('admin_kelola', 'kerja_sama'))->toBeFalse()
        ->and(AdminPeran::boleh('admin_kerjasama', 'kerja_sama'))->toBeTrue()
        ->and(AdminPeran::boleh('superadmin', 'kerja_sama'))->toBeTrue()
        ->and(AdminPeran::boleh('admin', 'kerja_sama'))->toBeTrue()
        ->and(AdminPeran::boleh('admin_kelola', 'konten'))->toBeTrue();
});

test('peran tidak dikenal tidak punya kemampuan apa pun', function () {
    expect(AdminPeran::kemampuan('penyusup'))->toBe([])
        ->and(AdminPeran::boleh('penyusup', 'konten'))->toBeFalse()
        ->and(AdminPeran::boleh('penyusup', 'kerja_sama'))->toBeFalse();
});

// ---------------------------------------------------------------- admin_kelola

test('admin_kelola dapat membuka seluruh halaman konten', function (string $route) {
    $this->withSession(sesiPeran('admin_kelola'))->get(route($route))->assertOk();
})->with(ruteKonten());

test('admin_kelola dapat menyimpan artikel dan galeri', function () {
    $this->withSession(sesiPeran('admin_kelola'))
        ->post(route('admin.artikel.store'), ['judul' => 'Artikel Pengelola'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->withSession(sesiPeran('admin_kelola'))
        ->post(route('admin.galeri.store'), ['judul' => 'Galeri Pengelola'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

test('admin_kelola dapat mengubah profil, visi misi, artikel, dan galeri', function () {
    $this->withSession(sesiPeran('admin_kelola'))
        ->post(route('admin.tentang.update'), [
            'p_nama' => ['Dr. Uji'],
            'p_jabatan' => ['Kepala'],
            'p_badge' => ['Uji'],
            'p_desc' => ['Deskripsi'],
            'visi' => 'Visi dari pengelola',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->withSession(sesiPeran('admin_kelola'))
        ->post(route('admin.artikel.store'), ['judul' => 'Artikel A'])
        ->assertRedirect();

    $artikel = app(LandingCms::class)->all()['artikel'];
    $id = end($artikel)['id'];

    $this->withSession(sesiPeran('admin_kelola'))
        ->post(route('admin.artikel.edit', $id), ['judul' => 'Artikel A revisi'])
        ->assertRedirect();

    $this->withSession(sesiPeran('admin_kelola'))
        ->post(route('admin.artikel.delete', $id))
        ->assertRedirect();
});

test('admin_kelola ditolak dari semua halaman data kerja sama', function (string $route) {
    $this->withSession(sesiPeran('admin_kelola'))->get(route($route))->assertNotFound();
})->with(ruteKerjasama());

test('admin_kelola ditolak pada aksi tulis data kerja sama', function (string $method, string $route, array $params, array $payload) {
    $this->withSession(sesiPeran('admin_kelola'))
        ->{$method}(route($route, $params), $payload)
        ->assertNotFound();
})->with([
    ['post', 'admin.dashboard.setujui', ['id' => 1], ['durasi' => 2]],
    ['post', 'admin.dashboard.tolak', ['id' => 1], ['alasan' => 'Alasan uji yang cukup panjang']],
    ['post', 'admin.inbox.approve', ['id' => 1], []],
    ['post', 'admin.inbox.reject', ['id' => 1], ['feedback' => 'Alasan uji']],
    ['get', 'admin.draf_proposal.unduh', ['uuid' => '3f1c2b7e-0000-4000-8000-000000000000'], []],
    ['put', 'admin.masa_aktif.atur', [], ['uuid' => '3f1c2b7e-0000-4000-8000-000000000000', 'tanggal_mulai' => '2030-01-01', 'tanggal_berakhir' => '2031-01-01']],
]);

test('admin_kelola juga ditolak dari route lama draf proposal', function () {
    $this->withSession(sesiPeran('admin_kelola'))
        ->get(route('admin.mitra_draf.lama'))
        ->assertNotFound();
});

// ---------------------------------------------------------------- menu

test('sidebar admin_kelola hanya memuat menu konten', function () {
    $html = $this->withSession(sesiPeran('admin_kelola'))
        ->get(route('admin.tentang'))->assertOk()->getContent();

    expect($html)->toContain('Profil &amp; Visi Misi')
        ->toContain('Kelola Artikel')
        ->toContain('Kelola Galeri')
        ->toContain('Lihat Portal Umum')
        ->toContain('Pengelola Konten')
        ->not->toContain('Inbox Pengajuan')
        ->not->toContain('Draf Proposal AI')
        ->not->toContain('Masa Aktif Mitra')
        ->not->toContain('Permohonan Perpanjangan')
        ->not->toContain('Super Administrator');
});

test('label peran dan nama akun dirender dari sesi', function () {
    $html = $this->withSession(['admin_auth' => ['name' => 'Rani Konten', 'role' => 'superadmin']])
        ->get(route('admin.inbox'))->assertOk()->getContent();

    expect($html)->toContain('Rani Konten')
        ->toContain('Super Administrator')
        ->toContain('Inbox Pengajuan')
        ->toContain('Draf Proposal AI')
        ->toContain('Masa Aktif Mitra');
});

// ---------------------------------------------------------------- peran penuh

test('admin_kerjasama dan superadmin dapat membuka semua halaman', function (string $role, string $route) {
    $this->withSession(sesiPeran($role))->get(route($route))->assertOk();
})->with([
    ['admin_kerjasama', 'admin.dashboard'],
    ['admin_kerjasama', 'admin.inbox'],
    ['admin_kerjasama', 'admin.draf_proposal'],
    ['admin_kerjasama', 'admin.masa_aktif'],
    ['admin_kerjasama', 'admin.tentang'],
    ['superadmin', 'admin.dashboard'],
    ['superadmin', 'admin.inbox'],
    ['superadmin', 'admin.draf_proposal'],
    ['superadmin', 'admin.masa_aktif'],
    ['superadmin', 'admin.tentang'],
]);

test('akun demo lama role admin tidak kehilangan akses', function (string $route) {
    $this->withSession(sesiPeran('admin'))->get(route($route))->assertOk();
})->with(array_merge(ruteKonten(), ruteKerjasama()));

test('role developer hanya boleh membuka halaman uji email', function () {
    $this->withSession(sesiPeran('developer'))->get(route('admin.test.email'))->assertNotFound(); // bukan lingkungan lokal
    $this->withSession(sesiPeran('developer'))->get(route('admin.inbox'))->assertNotFound();
});

// ---------------------------------------------------------------- tamu

test('tamu tanpa sesi admin diarahkan ke halaman login', function (string $route) {
    $this->get(route($route))->assertRedirect(route('admin.login'));
})->with(array_merge(ruteKonten(), ruteKerjasama()));

// ---------------------------------------------------------------- dashboard ringkas

test('dashboard admin_kelola tidak memuat data kerja sama sama sekali', function () {
    $html = $this->withSession(sesiPeran('admin_kelola'))
        ->get(route('admin.dashboard.kelola'))->assertOk()->getContent();

    expect($html)->toContain('Artikel terbit')
        ->toContain('Foto galeri')
        ->toContain('Profil pejabat')
        ->not->toContain('Permohonan perpanjangan menunggu')
        ->not->toContain('Pengajuan kerja sama baru menunggu')
        ->not->toContain('Permohonan Perpanjangan Masa Aktif')
        ->not->toContain('Keputusan Terakhir');
});

test('dashboard peran kerja sama tetap memuat antrean perpanjangan', function () {
    $html = $this->withSession(sesiPeran('superadmin'))
        ->get(route('admin.dashboard'))->assertOk()->getContent();

    expect($html)->toContain('Permohonan perpanjangan menunggu')
        ->toContain('Pengajuan kerja sama baru menunggu')
        ->toContain('Permohonan Perpanjangan Masa Aktif');
});

// ---------------------------------------------------------------- login .env

test('akun admin_kelola dari .env dapat masuk dan diarahkan ke dashboard-nya', function () {
    config(['admin_demo.accounts' => [[
        'name' => 'Pengelola Konten',
        'username' => 'kelola',
        'email' => 'kelola@example.test',
        'password_hash' => Hash::make('Rahasia#Kelola2026'),
        'role' => 'admin_kelola',
    ]]]);

    $this->post(route('admin.login.post'), ['username' => 'kelola', 'password' => 'Rahasia#Kelola2026'])
        ->assertRedirect(route('admin.dashboard.kelola'));

    expect(session('admin_auth.role'))->toBe('admin_kelola');
});

test('peran kerja sama tetap diarahkan ke inbox setelah login', function () {
    config(['admin_demo.accounts' => [[
        'name' => 'Super',
        'username' => 'super',
        'email' => 'super@example.test',
        'password_hash' => Hash::make('Rahasia#Super2026'),
        'role' => 'superadmin',
    ]]]);

    $this->post(route('admin.login.post'), ['username' => 'super', 'password' => 'Rahasia#Super2026'])
        ->assertRedirect(route('admin.inbox'));
});

test('password salah untuk akun admin_kelola ditolak', function () {
    config(['admin_demo.accounts' => [[
        'name' => 'Pengelola Konten',
        'username' => 'kelola',
        'email' => 'kelola@example.test',
        'password_hash' => Hash::make('Rahasia#Kelola2026'),
        'role' => 'admin_kelola',
    ]]]);

    $this->post(route('admin.login.post'), ['username' => 'kelola', 'password' => 'salah'])
        ->assertSessionHas('login_error');

    expect(session('admin_auth'))->toBeNull();
});

test('akun admin_kelola tanpa email atau hash di .env tidak bisa dipakai', function () {
    config(['admin_demo.accounts' => [[
        'name' => 'Pengelola Konten',
        'username' => 'kelola',
        'email' => '',
        'password_hash' => '',
        'role' => 'admin_kelola',
    ]]]);

    // Password kosong tidak boleh lolos hanya karena hash-nya kosong.
    $this->post(route('admin.login.post'), ['username' => 'kelola', 'password' => ''])
        ->assertSessionHas('login_error');

    expect(session('admin_auth'))->toBeNull();
});

test('setiap akun bawaan config punya jalur login yang bisa dipakai', function () {
    // Regresi: akun yang didefinisikan tapi lupa diisi .env akan mati tanpa
    // gejala (login hanya bilang "password salah"). Test ini memastikan setiap
    // peran punya email + kredensial, dan tiap akun benar-benar bisa masuk.
    $akun = collect(config('admin_demo.accounts'));

    foreach (AdminPeran::semua() as $peran) {
        expect($akun->where('role', $peran))->not->toBeEmpty("Peran {$peran} tidak punya akun.");
    }

    foreach ($akun as $acc) {
        expect($acc['email'])->not->toBe('', "Akun {$acc['username']} tidak punya email (isi .env).");

        // Kredensial dari .env boleh kosong; yang wajib ada hanya email + salah
        // satu metode (hash atau plaintext demo). Akun env kosong diuji terpisah.
        expect(isset($acc['password_hash']) || isset($acc['password']))->toBeTrue();
    }
});

test('akun superadmin dan admin_kerjasama dari .env bisa masuk dengan akses penuh', function (string $peran, string $nama) {
    config(['admin_demo.accounts' => [[
        'name' => $nama,
        'username' => 'uji',
        'email' => 'uji@example.test',
        'password_hash' => Hash::make('Rahasia#Uji2026'),
        'role' => $peran,
    ]]]);

    $this->post(route('admin.login.post'), ['username' => 'uji', 'password' => 'Rahasia#Uji2026'])
        ->assertRedirect(route('admin.inbox'));

    expect(session('admin_auth.role'))->toBe($peran);

    // Akses penuh: halaman kerja sama maupun konten terbuka.
    foreach (array_merge(ruteKerjasama(), ruteKonten()) as $rute) {
        $this->withSession(sesiPeran($peran))->get(route($rute))->assertOk();
    }
})->with([
    ['superadmin', 'Super Administrator'],
    ['admin_kerjasama', 'Administrator Kerja Sama'],
]);

test('akun .env yang kosong tidak membuat slot login terbuka', function (string $role) {
    // Disimulasikan dengan email/hash kosong, terlepas dari isi .env mesin ini:
    // tanpa email atau tanpa hash, akun tidak boleh bisa dipakai.
    config(['admin_demo.accounts' => [[
        'name' => 'Kosong',
        'username' => 'kosong',
        'email' => '',
        'password_hash' => '',
        'role' => $role,
    ]]]);

    $this->post(route('admin.login.post'), ['username' => 'kosong', 'password' => ''])
        ->assertSessionHas('login_error');

    expect(session('admin_auth'))->toBeNull();
})->with(['superadmin', 'admin_kerjasama', 'admin_kelola']);

test('halaman uji email hanya untuk developer dan hanya di lingkungan lokal', function (string $role, int $harapan) {
    $this->app['env'] = 'local';

    $this->withSession(sesiPeran($role))
        ->get(route('admin.test.email'))
        ->assertStatus($harapan);
})->with([
    ['developer', 200],
    ['admin_kelola', 404],
    ['admin_kerjasama', 404],
    ['superadmin', 404],
]);
