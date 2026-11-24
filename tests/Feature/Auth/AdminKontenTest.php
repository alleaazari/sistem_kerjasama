<?php

use App\Support\AdminAkses;
use App\Support\LandingCms;
use App\Support\MasaAktifMitra;
use App\Support\PengajuanMasuk;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;

/*
|--------------------------------------------------------------------------
| Panel Admin Konten (admin_konten)
|--------------------------------------------------------------------------
| Hanya peran admin_konten yang boleh mengakses panel ini. Peran lain
| (superadmin, admin_kerjasama, developer) ditolak 404 untuk semua method.
| Admin konten hanya melihat daftar mitra yang sudah disetujui dan mengelola
| konten portal (tentang, artikel, galeri) lewat controller yang sama dengan
| superadmin, tanpa inbox, draf, masa aktif, template MoU, aktivitas, maupun
| kelola pengguna.
|
| Seluruh penyimpanan diarahkan ke direktori sementara per test, sehingga
| berkas demo milik pengembang tidak pernah dihapus atau ditimpa.
*/

function direktoriKonten(string $nama): string
{
    return sys_get_temp_dir().'/kerjasama-konten-'.md5($nama);
}

beforeEach(function () {
    RateLimiter::clear('admin-login');
    app('cache')->flush();

    $temp = direktoriKonten($this->name());
    File::deleteDirectory($temp);
    File::makeDirectory($temp.'/app', 0755, true);
    $this->app->useStoragePath($temp);

    // Kredensial .env dikosongkan agar jalur akun demo benar-benar teruji.
    config([
        'admin_akses.peran.admin_konten.akun.username' => '',
        'admin_akses.peran.admin_konten.akun.password_hash' => '',
    ]);
});

afterEach(function () {
    File::deleteDirectory(direktoriKonten($this->name()));
});

function sesiKonten(string $role = 'admin_konten', string $nama = 'Admin Konten'): array
{
    return ['admin_auth' => ['name' => $nama, 'role' => $role]];
}

// ---------------------------------------------------------------- login fungsi

test('login admin konten memakai akun demo saat .env kosong', function () {
    $this->post(route('konten.login.post'), [
        'username' => 'konten',
        'password' => 'konten123',
    ])->assertRedirect(route(AdminAkses::beranda('admin_konten')));

    expect(session('admin_auth.role'))->toBe('admin_konten');
});

test('login admin konten memakai hash bcrypt dari .env bila diisi', function () {
    config([
        'admin_akses.peran.admin_konten.akun.username' => (string) env('ADMIN_KONTEN_USERNAME'),
        'admin_akses.peran.admin_konten.akun.password_hash' => (string) env('ADMIN_KONTEN_PASSWORD_HASH'),
    ]);

    $this->post(route('konten.login.post'), [
        'username' => 'uji-konten',
        'password' => 'konten-uji-admin',
    ])->assertRedirect(route(AdminAkses::beranda('admin_konten')));

    expect(session('admin_auth.role'))->toBe('admin_konten');
});

test('sesi diregenerasi saat admin konten login', function () {
    $sebelum = session()->getId();

    $this->post(route('konten.login.post'), [
        'username' => 'konten',
        'password' => 'konten123',
    ]);

    expect(session()->getId())->not->toBe($sebelum);
});

test('kata sandi demo admin konten yang salah ditolak', function () {
    $this->from(route('konten.login'))
        ->post(route('konten.login.post'), [
            'username' => 'konten',
            'password' => 'salah',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('login admin konten dibatasi 5 percobaan per menit', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->post(route('konten.login.post'), [
            'username' => 'konten',
            'password' => 'salah-'.$i,
        ]);
    }

    $this->post(route('konten.login.post'), [
        'username' => 'konten',
        'password' => 'konten123',
    ])->assertTooManyRequests();
});

test('logout admin konten menginvalidasi sesi dan kembali ke login konten', function () {
    $this->withSession(sesiKonten())
        ->post(route('konten.logout'))
        ->assertRedirect(route('konten.login'));

    expect(session('admin_auth'))->toBeNull();
});

// ---------------------------------------------------------------- login silang peran

test('login silang peran ditolak di pintu admin konten', function () {
    $this->from(route('konten.login'))
        ->post(route('konten.login.post'), [
            'username' => 'superadmin',
            'password' => 'superadmin123',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

test('akun admin konten tidak boleh masuk lewat pintu superadmin', function () {
    $this->from(route('superadmin.login'))
        ->post(route('superadmin.login.post'), [
            'username' => 'konten',
            'password' => 'konten123',
        ])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');

    expect(session('admin_auth'))->toBeNull();
});

// ---------------------------------------------------------------- matriks akses

test('peran lain ditolak di semua route admin konten (GET, POST)', function (string $role) {
    $rute = [
        'konten.dashboard_konten' => ['method' => 'get', 'parameter' => []],
        'konten.tentang' => ['method' => 'get', 'parameter' => []],
        'konten.tentang.update' => ['method' => 'post', 'parameter' => []],
        'konten.artikel' => ['method' => 'get', 'parameter' => []],
        'konten.artikel.store' => ['method' => 'post', 'parameter' => []],
        'konten.artikel.delete' => ['method' => 'post', 'parameter' => ['id' => '1']],
        'konten.artikel.edit' => ['method' => 'post', 'parameter' => ['id' => '1']],
        'konten.galeri' => ['method' => 'get', 'parameter' => []],
        'konten.galeri.store' => ['method' => 'post', 'parameter' => []],
        'konten.galeri.delete' => ['method' => 'post', 'parameter' => ['id' => '1']],
        'konten.galeri.edit' => ['method' => 'post', 'parameter' => ['id' => '1']],
    ];

    foreach ($rute as $nama => $def) {
        $url = route($nama, $def['parameter']);

        $this->withSession(sesiKonten($role))
            ->{$def['method']}($url, [])
            ->assertNotFound("{$role} boleh {$nama} ({$def['method']})");
    }
})->with(['superadmin', 'admin_kerjasama', 'developer']);

test('sesi peran lain tidak menghalangi login di pintu admin konten', function () {
    // Sesi panel lain (cookie terpisah) tidak menyentuh tamu di pintu konten:
    // halaman login tetap tampil, dan kredensial superadmin tetap ditolak
    // dengan pesan generik (bukan 404).
    $this->withSession(sesiKonten('superadmin'))
        ->get(route('konten.login'))
        ->assertOk();

    $this->withSession(sesiKonten('superadmin'))
        ->from(route('konten.login'))
        ->post(route('konten.login.post'), ['username' => 'uji-superadmin', 'password' => 'apa-saja'])
        ->assertSessionHas('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');
});

test('admin konten boleh membuka semua halaman miliknya', function (string $nama) {
    $this->withSession(sesiKonten())
        ->get(route('konten.'.$nama))
        ->assertOk();
})->with(['dashboard_konten', 'tentang', 'artikel', 'galeri']);

test('aksi tulis konten portal tidak ditutup untuk admin konten', function (string $nama, array $parameter) {
    $respons = $this->withSession(sesiKonten())->post(route('konten.'.$nama, $parameter), []);

    // 302 = validasi/redirect balik; yang dilarang adalah 404 (route tertutup).
    expect($respons->status())->not->toBe(404);
})->with([
    ['tentang.update', []],
    ['artikel.store', []],
    ['artikel.edit', ['id' => '1']],
    ['artikel.delete', ['id' => '1']],
    ['galeri.store', []],
    ['galeri.edit', ['id' => '1']],
    ['galeri.delete', ['id' => '1']],
]);

test('halaman terlarang tidak terdaftar di prefix admin konten', function (string $path) {
    $this->withSession(sesiKonten())
        ->get('/'.AdminAkses::path('admin_konten').'/'.$path)
        ->assertNotFound();
})->with(['inbox', 'draf-proposal', 'masa-aktif', 'template-mou', 'aktivitas', 'pengguna']);

// ---------------------------------------------------------------- dashboard read-only

test('beranda admin konten mengarah ke dashboard data mitra diterima', function () {
    $this->withSession(sesiKonten())
        ->get('/'.AdminAkses::path('admin_konten'))
        ->assertRedirect(route('konten.dashboard_konten'));
});

test('dashboard admin konten hanya menampilkan mitra yang disetujui, tanpa data PIC', function () {
    PengajuanMasuk::save([
        ['id' => 1, 'instansi' => 'Universitas Indonesia', 'pic' => 'Rina Wulandari', 'email' => 'rina@ui.ac.id', 'telp' => '08123456789', 'status' => 'Disetujui', 'bidang' => 'GTA'],
        ['id' => 2, 'instansi' => 'PT Telkom', 'pic' => 'Budi Santoso', 'email' => 'budi@telkom.co.id', 'telp' => '081311112222', 'status' => 'Menunggu', 'bidang' => 'VSGA'],
        ['id' => 3, 'instansi' => 'CV Mandiri', 'pic' => 'Maya Sari', 'email' => 'maya@mandiri.co.id', 'telp' => '085799990000', 'status' => 'Ditolak', 'bidang' => 'VSGA'],
    ]);

    file_put_contents(MasaAktifMitra::path(), json_encode([
        [
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'instansi' => 'Universitas Indonesia',
            'tanggal_mulai' => '2026-01-01',
            'tanggal_berakhir' => '2030-12-31',
            'alasan' => '',
        ],
    ], JSON_PRETTY_PRINT));

    $html = $this->withSession(sesiKonten())
        ->get(route('konten.dashboard_konten'))
        ->assertOk()
        ->getContent();

    // Hanya mitra disetujui, lengkap dengan bidang, status, dan masa aktif.
    expect($html)->toContain('Universitas Indonesia')
        ->toContain('GTA')
        ->toContain('Disetujui')
        ->toContain('Aktif')
        ->toContain('2030')
        ->not->toContain('PT Telkom')
        ->not->toContain('CV Mandiri')
        ->not->toContain('Menunggu')
        ->not->toContain('Ditolak');

    // Data kontak PIC sensitif tidak ada di HTML.
    expect($html)->not->toContain('rina@ui.ac.id')
        ->not->toContain('08123456789')
        ->not->toContain('Rina Wulandari')
        ->not->toContain('Budi Santoso')
        ->not->toContain('081311112222');

    // Tidak ada tombol aksi.
    expect($html)->not->toContain('Setujui')
        ->not->toContain('Tolak')
        ->not->toContain('Atur Masa Aktif');
});

// ---------------------------------------------------------------- konten portal

test('admin konten mengelola artikel lewat controller yang sama dengan superadmin', function () {
    $cms = app(LandingCms::class);
    $jumlahAwal = count($cms->all()['artikel']);

    $this->withSession(sesiKonten())
        ->post(route('konten.artikel.store'), [
            'judul' => 'Artikel Uji Admin Konten',
            'konten' => 'Isi artikel uji dari panel admin konten.',
        ])
        ->assertRedirect();

    $artikel = collect($cms->all()['artikel'])
        ->firstWhere('judul', 'Artikel Uji Admin Konten');

    expect($artikel)->not->toBeNull()
        ->and($cms->all()['artikel'])->toHaveCount($jumlahAwal + 1);

    $this->withSession(sesiKonten())
        ->post(route('konten.artikel.delete', $artikel['id']))
        ->assertRedirect();

    expect($cms->all()['artikel'])->toHaveCount($jumlahAwal);
});

test('artikel yang ditulis admin konten tampil di portal umum', function () {
    $judul = 'Artikel Uji Koneksi Portal';

    $this->withSession(sesiKonten())
        ->post(route('konten.artikel.store'), [
            'judul' => $judul,
            'konten' => 'Isi artikel uji koneksi portal.',
        ])
        ->assertRedirect();

    // Pengunjung anonim: bukti konten tersimpan di berkas CMS, bukan hanya sesi admin.
    $this->flushSession();

    $this->get(route('landing'))
        ->assertOk()
        ->assertSee($judul);
});

test('visi misi yang disimpan admin konten tampil di portal umum', function () {
    $visi = 'Visi uji koneksi portal dari admin konten.';

    $this->withSession(sesiKonten())
        ->post(route('konten.tentang.update'), [
            'p_nama' => ['Dr. Uji Konten'],
            'p_jabatan' => ['Kepala BBLSDM'],
            'p_badge' => ['Uji'],
            'p_desc' => ['Deskripsi uji'],
            'visi' => $visi,
        ])
        ->assertRedirect();

    $this->flushSession();

    $this->get(route('landing'))
        ->assertOk()
        ->assertSee($visi);
});

test('galeri yang disimpan admin konten tampil di portal umum', function () {
    $judul = 'Galeri Uji Koneksi Portal';

    $this->withSession(sesiKonten())
        ->post(route('konten.galeri.store'), [
            'judul' => $judul,
            'konten' => 'Keterangan galeri uji.',
        ])
        ->assertRedirect();

    $this->flushSession();

    $this->get(route('landing'))
        ->assertOk()
        ->assertSee($judul);
});

// ---------------------------------------------------------------- HTML bersih

test('HTML admin konten tidak memuat tautan terlarang', function (string $nama) {
    $html = $this->withSession(sesiKonten())
        ->get(route('konten.'.$nama))
        ->assertOk()
        ->getContent();

    // Tautan dan label milik superadmin / admin kerja sama.
    foreach ([
        '/superadmin/dashboard',
        '/superadmin/inbox',
        '/superadmin/aktivitas',
        '/superadmin/pengguna',
        '/superadmin/template-mou',
        '/admin-kerjasama/inbox',
        '/admin-kerjasama/draf-proposal',
        '/admin-kerjasama/masa-aktif',
        'Inbox Pengajuan',
        'Draf Proposal AI',
        'Masa Aktif Mitra',
        'Template MoU',
        'Aktivitas Admin Kerjasama',
        'Kelola Pengguna',
    ] as $terlarang) {
        expect($html)->not->toContain($terlarang);
    }

    // Menu yang memang milik admin konten tetap ada.
    expect($html)->toContain('/admin-konten/tentang')
        ->toContain('/admin-konten/artikel')
        ->toContain('/admin-konten/galeri')
        ->toContain('Admin Konten');
})->with(['dashboard_konten', 'tentang', 'artikel', 'galeri']);

test('menu admin konten memuat data mitra diterima dan portal umum', function () {
    $html = $this->withSession(sesiKonten())
        ->get(route('konten.dashboard_konten'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('/admin-konten/dashboard')
        ->toContain('Data Mitra Diterima')
        ->toContain('Lihat Portal Umum');
});
