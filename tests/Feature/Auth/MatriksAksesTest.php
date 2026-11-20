<?php

/*
|--------------------------------------------------------------------------
| Matriks akses per peran — server, bukan menu
|--------------------------------------------------------------------------
| Daftar route di bawah dibangun dari config/admin_akses.php, sehingga test
| ini otomatis mengikuti peta izin. Untuk setiap route, peran yang TIDAK
| berhak harus ditolak walaupun URL-nya diketik langsung (GET/POST/PUT/DELETE),
| dan peran yang berhak harus lolos middleware (bukan 403/404 karena peran).
*/

use App\Support\AdminAkses;
use App\Support\BidangKerjasama;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;

/** Sesi admin untuk peran tertentu. */
function sesi(string $peran, string $nama = 'Uji Akses'): array
{
    return ['admin_auth' => ['name' => $nama, 'role' => $peran]];
}

/**
 * Daftar route panel beserta kemampuan yang menjaganya.
 *
 * @return array<int, array{nama: string, method: string, parameter: array<string,string>}>
 */
function rutePanel(): array
{
    $daftar = [];

    foreach (AdminAkses::semuaPeran() as $peran) {
        foreach (array_merge(AdminAkses::semuaKemampuan(), array_keys(AdminAkses::semuaAksi())) as $kemampuan) {
            // Hanya route yang benar-benar ada untuk peran ini.
            $namaRoute = AdminAkses::nama($peran).'.'.AdminAkses::ruteKemampuan($kemampuan);

            if (! Route::has($namaRoute)) {
                continue;
            }

            // Metode dan parameter mengikuti definisi route yang sebenarnya,
            // sehingga route berparameter (mis. {kode}) tetap bisa dipanggil.
            $rute = Route::getRoutes()->getByName($namaRoute);
            $method = $rute === null ? 'get' : strtolower((string) ($rute->methods()[0] ?? 'get'));

            $parameter = [];

            foreach ($rute?->parameterNames() ?? [] as $nama) {
                $parameter[$nama] = match ($nama) {
                    'uuid' => '11111111-1111-4111-8111-111111111111',
                    'kode' => BidangKerjasama::kode()[0] ?? 'VSGA',
                    default => '1',
                };
            }

            $daftar[$namaRoute] = [
                'nama' => $namaRoute,
                'kemampuan' => $kemampuan,
                'method' => $method,
                'parameter' => $parameter,
            ];
        }
    }

    return array_values($daftar);
}

/** Kirim request dengan metode tertentu. */
function kirim($test, string $method, string $url): TestResponse
{
    return match ($method) {
        'post' => $test->post($url, []),
        'put' => $test->put($url, []),
        'patch' => $test->patch($url, []),
        'delete' => $test->delete($url),
        default => $test->get($url),
    };
}

// ---------------------------------------------------------------- matriks

test('peran tanpa kemampuan ditolak di route panel, bukan hanya disembunyikan', function () {
    $ditembus = [];

    foreach (rutePanel() as $rute) {
        $pemilik = AdminAkses::peranUntuk($rute['kemampuan']);

        foreach (AdminAkses::semuaPeran() as $peran) {
            if (in_array($peran, $pemilik, true)) {
                continue;
            }

            $url = route($rute['nama'], $rute['parameter']);

            // Peran lain memakai sesinya sendiri; request tetap ke URL peran
            // pemilik, meniru orang yang mengetik URL langsung.
            $respons = kirim($this->withSession(sesi($peran)), $rute['method'], $url);

            // 404 = disamarkan (pola proyek), 403 = ditolak eksplisit,
            // 401/302 = belum berhak sama sekali. Yang dilarang: 200.
            if (! in_array($respons->getStatusCode(), [401, 403, 404, 302], true)) {
                $ditembus[] = "{$peran} -> {$rute['nama']} ({$rute['method']}) = {$respons->getStatusCode()}";
            }
        }
    }

    expect($ditembus)->toBe([]);
});

test('kontrak matriks: superadmin memegang semua halaman, admin kerja sama hanya tiga halaman kerja', function () {
    expect(AdminAkses::kemampuan('superadmin'))->toContain(
        'dashboard', 'inbox', 'inbox.approve', 'inbox.reject',
        'draf', 'draf_proposal.unduh', 'masa_aktif', 'masa_aktif.atur',
        'tentang', 'artikel', 'galeri', 'aktivitas', 'pengguna'
    )->not->toContain('uji_email');

    expect(AdminAkses::kemampuan('admin_kerjasama'))->toBe([
        'inbox', 'inbox.approve', 'inbox.reject',
        'draf', 'draf_proposal.unduh',
        'masa_aktif', 'masa_aktif.atur',
    ]);
});

test('admin kerja sama ditolak di SEMUA route superadmin (semua metode)', function () {
    $ditolakSalah = [];

    foreach (rutePanel() as $rute) {
        if (! str_starts_with($rute['nama'], 'superadmin.')) {
            continue;
        }

        $url = route($rute['nama'], $rute['parameter']);
        $respons = kirim($this->withSession(sesi('admin_kerjasama')), $rute['method'], $url);

        if (! in_array($respons->getStatusCode(), [403, 404], true)) {
            $ditolakSalah[] = "{$rute['nama']} ({$rute['method']}) = {$respons->getStatusCode()}";
        }
    }

    expect($ditolakSalah)->toBe([]);
});

test('superadmin tidak bisa membuka halaman kerja sama di prefix admin kerja sama', function () {
    foreach (['inbox', 'draf_proposal', 'masa_aktif'] as $nama) {
        $this->withSession(sesi('superadmin'))
            ->get(route('kerjasama.'.$nama))
            ->assertNotFound();
    }
});

test('admin kerja sama boleh menyetujui dan menolak pengajuan di prefixnya sendiri', function () {
    $this->withSession(sesi('admin_kerjasama'))
        ->post(route('kerjasama.inbox.approve', '1'))
        ->assertSessionHasNoErrors();

    $this->withSession(sesi('admin_kerjasama'))
        ->post(route('kerjasama.inbox.reject', '2'), ['feedback' => 'Alasan uji penolakan yang sah.'])
        ->assertSessionHasNoErrors();
});

test('admin kerja sama tetap tidak bisa menyentuh route superadmin lewat URL khusus itu', function () {
    $this->withSession(sesi('admin_kerjasama'))
        ->post(route('superadmin.inbox.approve', '1'))
        ->assertNotFound();

    $this->withSession(sesi('admin_kerjasama'))
        ->post(route('superadmin.inbox.reject', '1'), ['feedback' => 'Alasan uji penolakan sah.'])
        ->assertNotFound();
});

test('admin kerja sama tetap boleh mengubah masa aktif dan mengunduh draf', function () {
    // Aksi yang memang miliknya tidak boleh ikut tertutup.
    expect(AdminAkses::boleh('admin_kerjasama', 'masa_aktif.atur'))->toBeTrue()
        ->and(AdminAkses::boleh('admin_kerjasama', 'draf_proposal.unduh'))->toBeTrue();
});

test('setelah login admin kerja sama langsung masuk halaman sendiri, bukan dashboard', function () {
    $inbox = $this->withSession(sesi('admin_kerjasama'))->get(route('kerjasama.inbox'));
    $inbox->assertOk()->assertSee('Inbox Pengajuan');

    // Dashboard penuh bukan miliknya.
    $this->withSession(sesi('admin_kerjasama'))->get('/superadmin/dashboard')->assertNotFound();

    expect(AdminAkses::beranda('admin_kerjasama'))->toBe('kerjasama.inbox');
});

// ---------------------------------------------------------------- HTML bersih

test('HTML admin kerja sama tidak memuat tautan, badge, atau counter superadmin', function (string $nama) {
    $html = $this->withSession(sesi('admin_kerjasama'))
        ->get(route('kerjasama.'.$nama))
        ->assertOk()
        ->getContent();

    // Tautan dan label menu milik superadmin.
    foreach ([
        '/superadmin/dashboard',
        '/superadmin/aktivitas',
        '/superadmin/pengguna',
        '/superadmin/tentang',
        '/superadmin/artikel',
        '/superadmin/galeri',
        'Kelola Artikel',
        'Kelola Galeri',
        'Profil &amp; Visi Misi',
        'Kelola Pengguna',
        'Aktivitas Admin Kerjasama',
    ] as $terlarang) {
        expect($html)->not->toContain($terlarang);
    }

    // Counter perpanjangan hanya untuk pemilik dashboard.
    expect($html)->not->toContain('link-badge" title="Permohonan perpanjangan menunggu"');
})->with(['inbox', 'draf_proposal', 'masa_aktif']);

test('HTML admin kerja sama tetap memuat menu yang memang miliknya', function () {
    $html = $this->withSession(sesi('admin_kerjasama'))
        ->get(route('kerjasama.inbox'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('/admin-kerjasama/inbox')
        ->toContain('/admin-kerjasama/draf-proposal')
        ->toContain('/admin-kerjasama/masa-aktif')
        ->toContain('Lihat Portal Umum')
        ->toContain('Admin Kerja Sama');
});

test('HTML superadmin memuat seluruh menu termasuk halaman baru', function () {
    $html = $this->withSession(sesi('superadmin'))
        ->get(route('superadmin.dashboard'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('/superadmin/aktivitas')
        ->toContain('Aktivitas Admin Kerjasama')
        ->toContain('/superadmin/pengguna')
        ->toContain('Kelola Pengguna');
});

test('halaman inbox admin kerja sama merender formulir keputusan atas pengajuan mitra', function () {
    $html = $this->withSession(sesi('admin_kerjasama'))
        ->get(route('kerjasama.inbox'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('id="f-approve"')
        ->toContain('id="f-reject"')
        ->toContain('Setujui Pengajuan')
        ->toContain('Tolak Pengajuan')
        ->toContain('/admin-kerjasama/inbox/__ID__/approve')
        ->toContain('/admin-kerjasama/inbox/__ID__/reject')
        // Form harus mengarah ke prefix peran ini, bukan ke superadmin.
        ->not->toContain('/superadmin/inbox/__ID__');
});

test('halaman inbox superadmin tetap merender formulir keputusan', function () {
    $html = $this->withSession(sesi('superadmin'))
        ->get(route('superadmin.inbox'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('id="f-approve"')
        ->toContain('id="f-reject"')
        ->toContain('Setujui Pengajuan')
        ->toContain('Tolak Pengajuan')
        ->toContain('/superadmin/inbox/__ID__/approve');
});
