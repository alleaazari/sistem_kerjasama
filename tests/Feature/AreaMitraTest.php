<?php

use App\Support\StatusKerjasama;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Audit Kepatuhan Area Mitra: R1, R3, Status, MoU, Sidebar, Profil
|--------------------------------------------------------------------------
| Semua tes memakai mekanisme session yang sudah dipakai proyek:
| identitas mitra = session('mitra_data'), identitas admin = session('admin_auth').
*/

/** Berkas uji dengan isi tertentu; tipe MIME dikosongkan agar server memeriksa isi nyata. */
function berkasUji(string $nama, string $isi): UploadedFile
{
    // Berkas dibuat di dalam proyek: finfo di Windows gagal membaca berkas
    // yang berada di direktori temp pengguna (bukan masalah kode aplikasi).
    $dir = base_path('storage/framework/testing-mou');
    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    $path = $dir.'/'.uniqid().'.'.$nama;
    file_put_contents($path, $isi);
    clearstatcache(true, $path);

    return new UploadedFile($path, $nama, null, null, true);
}

// ---------------------------------------------------------------- R1 -------
test('R1 landing tidak memuat tautan login mitra dan tetap memuat ajakan kerja sama', function () {
    $response = $this->get(route('landing'));

    $response->assertOk();
    $response->assertDontSee(route('login'), false);
    $response->assertDontSee('Masuk Mitra');
    $response->assertDontSee('Masuk Portal Mitra');

    // Tombol ajakan tetap ada (label hero di landing).
    $response->assertSee('Ajukan Kerja Sama');

    // Header masih memuat tombol pengajuan.
    $response->assertSee('Pengajuan Baru');
});

test('R1 halaman pengajuan juga tidak menawarkan tautan login', function () {
    $response = $this->get(route('pengajuan.create'));

    $response->assertOk();
    $response->assertDontSee(route('login'), false);
    $response->assertDontSee('Masuk Mitra');
});

// ---------------------------------------------------------------- B1/B2 ----
test('B1 halaman login mitra tetap dapat dibuka lewat URL langsung', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('Masuk Mitra');
});

test('B1 login memakai CSS yang sama dengan landing dan punya satu h1', function () {
    $landing = $this->get(route('landing'))->getContent();
    $login = $this->get('/login')->getContent();

    expect($landing)->toContain('tokens.css');
    expect($landing)->toContain('base.css');
    expect($login)->toContain('tokens.css');
    expect($login)->toContain('base.css');

    // Login mitra tampil sebagai kartu mandiri: tanpa navbar aplikasi atas.
    expect($login)->not->toContain('class="app-navbar"');

    // Satu h1 saja.
    expect(substr_count($login, '<h1'))->toBe(1);
});

test('B2 login: logo di samping judul, satu Lihat Portal Umum, tanpa tautan kembali', function () {
    $response = $this->get('/login');

    $response->assertOk();
    $response->assertSee('auth-logo', false);
    $response->assertSee('Lihat Portal Umum');
    $response->assertDontSee('Lihat Portal Utama');
    $response->assertDontSee('Kembali ke beranda portal');
    $response->assertSee(route('landing'), false);

    // Tepat satu tautan portal.
    expect(substr_count($response->getContent(), 'Lihat Portal Umum'))->toBe(1);
});

test('B3 login tidak mengisikan nilai otomatis, semua mode', function () {
    foreach ([true, false] as $debug) {
        config(['app.debug' => $debug]);

        $html = $this->get('/login')->getContent();

        expect($html)->not->toContain('value="'.config('mitra.demo_user').'"');
        expect($html)->not->toContain('value="'.config('mitra.demo_password').'"');
        expect($html)->toContain('autocomplete="username"');
        expect($html)->toContain('autocomplete="current-password"');
        expect($html)->toContain('data-toggle-password="password"');
        expect($html)->toContain('name="_token"');
    }
});

test('B3 galat login memakai role alert', function () {
    // Wadah galat form login: satu banner role="alert" yang menerima pesan validasi.
    $blade = file_get_contents(resource_path('views/login.blade.php'));

    expect($blade)->toContain('role="alert"');
    expect(substr_count($blade, 'role="alert"'))->toBe(1);
    expect($blade)->toContain('$errors->first()');
});

test('B3 kredensial demo hanya tampil sebagai teks bantuan saat app.debug true', function () {
    config(['app.debug' => false]);
    $this->get('/login')->assertDontSee('Mode pengembangan')->assertDontSee(config('mitra.demo_user'));

    config(['app.debug' => true]);
    $this->get('/login')->assertSee('Mode pengembangan');
});

// ---------------------------------------------------------------- R3 -------
test('R3 area mitra menampilkan tepat tiga item sidebar', function () {
    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])->get(route('mitra.status'));

    $response->assertOk();

    $html = $response->getContent();

    expect(substr_count($html, 'class="mitra-nav-item"'))->toBe(3);
    expect($html)->toContain('Status dan Masa Aktif');
    expect($html)->toContain('Profil Instansi');
    expect($html)->toContain('MoU');
    expect($html)->toContain('aria-current="page"');

    // Tautan fitur lama tidak lagi muncul di menu.
    expect($html)->not->toContain('Generator Draf Proposal');
    expect($html)->not->toContain('Draf Proposal AI');
});

test('R3 tanpa navbar navigasi atas, tautan portal & keluar ada di sidebar', function () {
    $html = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->get(route('mitra.status'))->getContent();

    expect($html)->not->toContain('class="app-navbar"');
    expect($html)->not->toContain('mitra-tabs');
    expect($html)->toContain('class="mitra-sidebar"');
    expect($html)->toContain('Lihat Portal Umum');
    expect($html)->toContain('action="'.route('logout').'"');
    expect($html)->toContain('name="_token"');
});

test('R3 route lama generator/draf dialihkan ke status', function () {
    $this->get('/mitra/draf')->assertRedirect(route('mitra.status'));
});

test('R3 mitra tidak dapat mengakses halaman admin', function () {
    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->get(route('admin.inbox'));

    $response->assertRedirect();
    expect($response->getTargetUrl())->toContain(route('admin.login'));
});

// ---------------------------------------------------------------- D --------
test('D profil kanan atas menaut ke halaman Profil Instansi', function () {
    $html = $this->withSession(['mitra_data' => ['username' => 'user1', 'pic' => 'Rina Wulandari']])
        ->get(route('mitra.status'))->getContent();

    expect($html)->toContain('class="mitra-user"');
    expect($html)->toContain('href="'.route('mitra.profile').'"');
    expect($html)->toContain('Rina Wulandari');
});

test('D profil instansi menampilkan biodata dan modal yang dapat diakses', function () {
    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])->get(route('mitra.profile'));

    $response->assertOk();
    $response->assertSee('Profil Instansi');
    $response->assertSee('Biodata Penanggung Jawab');
    $response->assertSee('role="dialog"', false);
    $response->assertSee('aria-modal="true"', false);
    $response->assertSee('accept="image/jpeg,image/png,image/webp"', false);
});

test('D unggah foto menolak berkas non-gambar', function () {
    Storage::fake('local');

    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->post(route('mitra.profile.foto.unggah'), [
            'foto' => berkasUji('foto.png', '<?php echo "pwned"; ?>'),
        ]);

    $response->assertSessionHasErrors('foto');
    Storage::disk('local')->assertDirectoryEmpty('foto');
});

// ---------------------------------------------------------------- E --------
test('E detail MoU menampilkan bagian Isi MoU', function () {
    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])->get(route('mitra.mou'));
    $response->assertOk();

    // Ambil MoU pertama milik mitra ini dari session demo.
    $daftar = session('mitra_mou');
    expect($daftar)->toBeArray()->not->toBeEmpty();

    $detail = $this->withSession(['mitra_data' => ['username' => 'user1'], 'mitra_mou' => $daftar])
        ->get(route('mitra.mou.detail', $daftar[0]['id']));

    $detail->assertOk();
    $detail->assertSee('Isi MoU');
    $detail->assertSee('Pasal 1');
    $detail->assertSee('Daftar MoU');
});

test('E MoU milik mitra lain tidak dapat dilihat maupun diunduh', function () {
    Storage::fake('local');

    $milikOrangLain = [
        'id' => (string) Str::uuid(),
        'pemilik' => 'mitra-lain',
        'judul' => 'MoU Rahasia Mitra Lain',
        'nomor' => '999/KS/LAIN/2026',
        'keterangan' => '',
        'status' => 'Disetujui',
        'tanggal' => now()->toDateString(),
        'nama_asli' => 'rahasia.pdf',
        'berkas' => 'mou/rahasia.pdf',
        'ukuran' => 10,
        'mime' => 'application/pdf',
    ];

    $sesi = ['mitra_data' => ['username' => 'user1'], 'mitra_mou' => [$milikOrangLain]];

    $this->withSession($sesi)->get(route('mitra.mou.detail', $milikOrangLain['id']))->assertNotFound();
    $this->withSession($sesi)->get(route('mitra.mou.unduh', $milikOrangLain['id']))->assertNotFound();
    $this->withSession($sesi)->get(route('mitra.mou.berkas', $milikOrangLain['id']))->assertNotFound();
});

// ----------------------------------------------- MoU: keamanan unggahan ---
test('MoU menolak berkas PHP berkedok PDF', function () {
    Storage::fake('local');

    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->post(route('mitra.mou.kirim'), [
            'judul' => 'MoU Uji PHP',
            'berkas' => berkasUji('mou.pdf', '<?php echo "pwned"; ?>'),
        ]);

    $response->assertSessionHasErrors('berkas');
    Storage::disk('local')->assertDirectoryEmpty('mou');
});

test('MoU menolak PDF palsu walau ekstensinya benar', function () {
    Storage::fake('local');

    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->post(route('mitra.mou.kirim'), [
            'judul' => 'MoU Uji MIME',
            'berkas' => berkasUji('dokumen.pdf', "<?php\necho 'pwned';\n"),
        ]);

    $response->assertSessionHasErrors('berkas');
});

test('MoU menolak berkas lebih dari 5 MB', function () {
    Storage::fake('local');

    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->post(route('mitra.mou.kirim'), [
            'judul' => 'MoU Terlalu Besar',
            'berkas' => UploadedFile::fake()->create('mou-besar.pdf', 6144, 'application/pdf'),
        ]);

    $response->assertSessionHasErrors('berkas');
});

// ------------------------------------------------- Status & Masa Aktif -----
test('status: tepat 90 hari lagi = Segera Berakhir', function () {
    $hariIni = now()->startOfDay();
    $hasil = StatusKerjasama::hitung(
        $hariIni->copy()->subYear()->toDateString(),
        $hariIni->copy()->addDays(90)->toDateString(),
    );

    expect($hasil['kondisi'])->toBe(StatusKerjasama::SEGERA);
    expect($hasil['label'])->toBe('Segera Berakhir');
    expect($hasil['keterangan'])->toBe('Berakhir dalam 90 hari');
});

test('status: 91 hari lagi = Aktif', function () {
    $hariIni = now()->startOfDay();
    $hasil = StatusKerjasama::hitung(
        $hariIni->copy()->subYear()->toDateString(),
        $hariIni->copy()->addDays(91)->toDateString(),
    );

    expect($hasil['kondisi'])->toBe(StatusKerjasama::AKTIF);
    expect($hasil['label'])->toBe('Aktif');
    expect($hasil['keterangan'])->toBe('91 hari tersisa');
});

test('status: kemarin = Berakhir', function () {
    $hariIni = now()->startOfDay();
    $hasil = StatusKerjasama::hitung(
        $hariIni->copy()->subYear()->toDateString(),
        $hariIni->copy()->subDay()->toDateString(),
    );

    expect($hasil['kondisi'])->toBe(StatusKerjasama::BERAKHIR);
    expect($hasil['label'])->toBe('Berakhir');
    expect($hasil['keterangan'])->toBe('Berakhir 1 hari yang lalu');
});

test('status: halaman menulis label, sisa hari, dan tidak memakai bar progres', function () {
    config(['app.debug' => true]);

    $response = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->get(route('mitra.status', ['pratinjau' => 'segera']));

    $response->assertOk();
    $response->assertSee('Segera Berakhir');
    $response->assertSee('role="status"', false);
    $response->assertSee('Perpanjang Masa Aktif');

    $html = $response->getContent();
    expect($html)->not->toContain('role="progressbar"');
    expect($html)->not->toContain('365 Hari');
    expect($html)->not->toContain('status-bar');
});

test('status: berakhir memakai role alert, bukan status', function () {
    config(['app.debug' => true]);

    $html = $this->withSession(['mitra_data' => ['username' => 'user1']])
        ->get(route('mitra.status', ['pratinjau' => 'berakhir']))->getContent();

    expect($html)->toContain('role="alert"');
    expect($html)->toContain('Berakhir');
});

test('JS statis tidak memuat sintaks Blade', function () {
    // Berkas .js disajikan langsung dari public/, jadi komentar {{-- --}} akan
    // menjadi SyntaxError dan seluruh skrip tidak berjalan.
    $berkas = glob(public_path('assets/user/js/*.js'));

    expect($berkas)->not->toBeEmpty();

    foreach ($berkas as $path) {
        $isi = file_get_contents($path);
        expect($isi)->not->toContain('{{--', basename($path));
        expect($isi)->not->toContain('--}}', basename($path));
        expect($isi)->not->toContain('{{ ', basename($path));
    }
});

// ---------------------------------------------------------------- G --------
test('G landing: semua frasa hero jadi bayangan + huruf <span> sejak awal', function () {
    $html = $this->get(route('landing'))->getContent();

    // Tidak ada lagi node teks tunggal yang ditulis ulang (penyebab bentuk berubah).
    expect($html)->not->toContain('heroTypeText');
    expect($html)->not->toContain('id="heroTypeText"');

    // Bayangan + lapisan terlihat dalam satu sel grid.
    expect(substr_count($html, 'hero-type-ghost'))->toBe(3);
    expect(substr_count($html, 'hero-type-live'))->toBe(3);
    expect(substr_count($html, 'hero-type-huruf'))->toBeGreaterThan(30);
    expect($html)->toContain('hero-type-stack');

    // Kursor tidak menambah lebar, teks lengkap tersedia untuk pembaca layar.
    expect($html)->toContain('class="sr-only"');
    expect($html)->toContain('Sinergi Digital Nusantara');

    // JS tidak lagi menulis ulang isi teks hero.
    $js = file_get_contents(public_path('assets/user/js/landing-widgets.js'));
    expect($js)->not->toContain('heroTypeText');
    expect($js)->toContain('hero-type-live');
    expect($js)->toContain('data-tampil');
    expect($js)->toContain('document.fonts.ready');
});

test('G CSS hero: transisi hanya opacity pada pembungkus & huruf', function () {
    $css = file_get_contents(public_path('assets/user/css/landing-widgets.css'));
    $awal = strpos($css, '.landing-page .hero-type {');
    $akhir = strpos($css, '2. LINE SIDEBAR');
    expect($awal)->not->toBeFalse();
    $blok = substr($css, $awal, $akhir - $awal);

    expect($blok)->toContain('.hero-type-ghost');
    expect($blok)->toContain('visibility: hidden');
    expect($blok)->toContain('position: absolute');
    expect($blok)->toContain('grid-area: 1 / 1');

    // Tidak ada animasi/transisi bentuk.
    expect($blok)->not->toContain('letter-spacing:');
    expect($blok)->not->toContain('scale(');
    expect($blok)->not->toContain('animation: ttBlink 1.1s steps(1) infinite; width');
    expect(substr_count($blok, 'transition: opacity'))->toBeGreaterThanOrEqual(1);
});

test('pratinjau pengembang diabaikan saat app.debug false', function () {
    config(['app.debug' => false]);

    $response = $this->withSession(['mitra_data' => [
        'username' => 'user1',
        'mulai' => now()->subYear()->toDateString(),
        'selesai' => now()->addDays(200)->toDateString(),
    ]])->get(route('mitra.status', ['pratinjau' => 'berakhir']));

    $response->assertOk();
    expect($response->getContent())->not->toContain('role="alert"');
});
