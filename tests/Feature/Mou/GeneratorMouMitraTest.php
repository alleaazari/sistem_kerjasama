<?php

use App\Support\Mou\DrafMouMitra;
use App\Support\Mou\PenandaIsi;
use App\Support\Mou\Penomoran;
use App\Support\Mou\RendererDokumen;
use App\Support\Mou\StrukturTemplateMou;
use App\Support\PengajuanMasuk;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Generator MoU mitra + pratinjau dokumen
|--------------------------------------------------------------------------
| Bidang dipilih otomatis dari pengajuan (bukan URL). Placeholder terisi dari
| data pengajuan + form; yang kosong tampil [ISI ...]; output di-escape; draf
| hanya milik pembuatnya; terbilang/tanggal resmi benar.
*/

function direktoriGenMou(): string
{
    $temp = sys_get_temp_dir().'/kerjasama-mou-mitra-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);

    return $temp;
}

function sesiGenMou(string $email = 'rina@jabarprov.go.id', string $username = 'user1'): array
{
    return [
        'mitra_data' => [
            'username' => $username,
            'email' => $email,
            'instansi' => 'Pemerintah Provinsi Jawa Barat',
            'pic' => 'Rina Wulandari, S.STP., M.Si.',
            'jabatan_pic' => 'Kepala Dinas',
            'alamat' => 'Jl. Diponegoro No. 22, Bandung',
            'telp' => '081234567890',
        ],
    ];
}

/** Payload form lengkapi yang sah. */
function formLengkapiMou(array $ganti = []): array
{
    return array_merge([
        'nomor_surat' => '001/KS/2026',
        'pihak_kedua_penandatangan' => 'Rina Wulandari, S.STP., M.Si.',
        'pihak_kedua_jabatan' => 'Kepala Dinas Komunikasi dan Informatika',
        'pihak_kedua_dasar_pengangkatan' => 'Keputusan Gubernur Nomor 1 Tahun 2026',
        'pihak_kedua_alamat' => 'Jl. Diponegoro No. 22, Bandung',
        'telepon_instansi' => '081234567890',
        'email_instansi' => 'rina@jabarprov.go.id',
        'tempat' => 'Bandung',
        'tanggal_penandatanganan' => '2026-09-21',
        'pihak_kesatu_pejabat_pelaksana' => 'Kepala Balai Besar Pelatihan SDM Komdigi Medan',
        'dasar_hukum_daerah' => 'Peraturan Daerah Provinsi Jawa Barat Nomor 1 Tahun 2020',
        'masa_aktif_tahun' => '3',
    ], $ganti);
}

beforeEach(function () {
    $asli = storage_path('app/mou');
    $temp = direktoriGenMou();
    $this->app->useStoragePath($temp);

    // Template disalin ke storage sementara supaya loader tetap menemukannya.
    File::copyDirectory($asli, $temp.'/app/mou');

    // Tulis seed pengajuan agar pengajuan milik email uji ada.
    PengajuanMasuk::save([
        [
            'id' => 1,
            'instansi' => 'Pemerintah Provinsi Jawa Barat',
            'organisasi' => 'Dinas Komunikasi dan Informatika',
            'kota' => 'Kota Bandung',
            'provinsi' => 'Jawa Barat',
            'alamat' => 'Jl. Diponegoro No. 22, Bandung',
            'pic' => 'Rina Wulandari, S.STP., M.Si.',
            'jabatan_pic' => 'Kepala Dinas',
            'email' => 'rina@jabarprov.go.id',
            'telp' => '081234567890',
            'bidang' => 'GTA',
            'metode_pelatihan' => 'luring',
            'pembiayaan' => 'apbd',
            'status' => 'Disetujui',
        ],
    ]);
});

// ---------------------------------------------------------------------
// Penomoran
// ---------------------------------------------------------------------

test('terbilang bilangan benar untuk contoh tanggal/tahun', function (int $angka, string $harap) {
    expect(Penomoran::terbilang($angka))->toBe($harap);
})->with([
    [5, 'lima'],
    [21, 'dua puluh satu'],
    [2026, 'dua ribu dua puluh enam'],
    [11, 'sebelas'],
    [100, 'seratus'],
    [1500, 'seribu lima ratus'],
]);

test('tanggal resmi dan prefiks wilayah benar', function () {
    $d = CarbonImmutable::parse('2026-09-21');

    expect(Penomoran::tanggalResmi($d))->toBe('Senin, tanggal dua puluh satu, bulan September, tahun dua ribu dua puluh enam')
        ->and(Penomoran::tanggalRingkas($d))->toBe('21 September 2026')
        ->and(Penomoran::prefiksWilayah('Kota Bandung'))->toBe('Kota')
        ->and(Penomoran::prefiksWilayah('Kabupaten Sleman'))->toBe('Kabupaten')
        ->and(Penomoran::prefiksWilayah('Bandung'))->toBe('');
});

// ---------------------------------------------------------------------
// Renderer
// ---------------------------------------------------------------------

test('setiap bidang menghasilkan template yang benar', function (string $kodeBidang, string $kodeTemplate) {
    $template = StrukturTemplateMou::untukBidang($kodeBidang);

    expect($template)->not->toBeNull()
        ->and($template['kode'])->toBe($kodeTemplate);
})->with([
    ['GTA', 'nota_kesepakatan_pemda'],
    ['VSGA', 'pks_beasiswa_talenta_digital'],
    ['TIDAK_DIKENAL', 'kerangka_umum'],
]);

test('renderer mengisi placeholder dari data', function () {
    // Template A: instansi + klausul pembiayaan.
    $a = StrukturTemplateMou::muat('nota_kesepakatan_pemda');
    $docA = RendererDokumen::render($a, RendererDokumen::data($a, [
        'nama_instansi' => 'Universitas Uji',
        'pembiayaan' => 'APBD',
    ]));
    $teksA = json_encode($docA, JSON_UNESCAPED_UNICODE);

    expect($teksA)->toContain('Universitas Uji')
        ->and($teksA)->toContain('Anggaran Pendapatan dan Belanja Daerah');

    // Template B: bidang muncul di menimbang huruf c.
    $b = StrukturTemplateMou::muat('pks_beasiswa_talenta_digital');
    $docB = RendererDokumen::render($b, RendererDokumen::data($b, [
        'bidang' => 'Transformasi Digital',
    ]));
    $teksB = json_encode($docB, JSON_UNESCAPED_UNICODE);

    expect($teksB)->toContain('Transformasi Digital');
});

test('placeholder tanpa nilai tampil sebagai [ISI nama_field]', function () {
    $template = StrukturTemplateMou::muat('nota_kesepakatan_pemda');
    $data = RendererDokumen::data($template, []); // kosong
    $dokumen = RendererDokumen::render($template, $data);

    $teks = json_encode($dokumen, JSON_UNESCAPED_UNICODE);

    expect($teks)->toContain('[ISI nama_instansi]')
        ->and($teks)->toContain('[ISI pihak_kedua_penandatangan]');
});

test('penanda [ISI] disorot tanpa membocorkan tag pengguna', function () {
    $html = PenandaIsi::sorot('Nama: [ISI nama_instansi] <script>alert(1)</script>');

    expect($html)->toContain('<mark class="mou-isi-kosong">[ISI nama_instansi]</mark>')
        ->and($html)->not->toContain('<script>')
        ->and($html)->toContain('&lt;script&gt;');
});

test('payload script pada isian tidak tereksekusi (ter-escape)', function () {
    // Nilai mentah dari data tidak di-escape oleh renderer (tugasnya menyusun
    // teks); escaping dilakukan saat render (Blade {{ }} dan PenandaIsi).
    $template = StrukturTemplateMou::muat('nota_kesepakatan_pemda');
    $data = RendererDokumen::data($template, [
        'nama_instansi' => '<script>alert(document.cookie)</script>',
    ]);
    $dokumen = RendererDokumen::render($template, $data);

    // Semua teks yang tampil lewat jalur sorotan harus ter-escape.
    foreach ($dokumen['blok'] as $blok) {
        foreach ((array) ($blok['paragraf'] ?? []) as $paragraf) {
            expect(PenandaIsi::sorot((string) $paragraf))->not->toContain('<script>');
        }
        foreach ((array) ($blok['ayat'] ?? []) as $ayat) {
            expect(PenandaIsi::sorot((string) $ayat))->not->toContain('<script>');
        }
    }

    expect(PenandaIsi::sorot('<img src=x onerror=alert(1)>'))->not->toContain('<img');
});

test('jumlah pasal aktif sesuai referensi dan nomor berurutan', function (string $kode, int $jumlah) {
    $template = StrukturTemplateMou::muat($kode);
    $aktif = StrukturTemplateMou::pasalAktif($template);

    expect($aktif)->toHaveCount($jumlah)
        ->and(array_column($aktif, 'nomor'))->toBe(range(1, $jumlah));
})->with([
    ['nota_kesepakatan_pemda', 14],
    ['nota_kesepakatan_pemda_pdp', 15],
    ['pks_beasiswa_talenta_digital', 15],
]);

test('template draf tidak bisa dipakai mitra untuk generate', function () {
    $template = StrukturTemplateMou::muat('kerangka_umum');

    expect(StrukturTemplateMou::dapatDipakaiMitra($template))->toBeFalse()
        ->and(StrukturTemplateMou::pesanBelumTersedia())->toBe('Template belum tersedia.');
});

// ---------------------------------------------------------------------
// HTTP: halaman & kepemilikan
// ---------------------------------------------------------------------

test('halaman Buat MoU memilih template sesuai bidang pengajuan', function () {
    $this->withSession(sesiGenMou())
        ->get('/mitra/mou/buat')
        ->assertOk()
        ->assertSee('Buat MoU Bidang GTA')
        ->assertSee('Nota Kesepakatan dengan Pemerintah Daerah');
});

test('mitra tanpa bidang pengajuan tidak dapat membuat MoU', function () {
    $this->withSession(sesiGenMou('tak-ada@contoh.test', 'user9'))
        ->get('/mitra/mou/buat')
        ->assertOk()
        ->assertSee('belum memiliki bidang kerja sama');
});

test('mitra dapat menyimpan draf, lalu melihat dan mengirimnya', function () {
    $this->withSession(sesiGenMou())
        ->post('/mitra/mou/buat', formLengkapiMou())
        ->assertRedirect();

    $draf = DrafMouMitra::untukPemilik('user1');
    expect($draf)->toHaveCount(1);
    $id = $draf[0]['id'];

    $this->withSession(sesiGenMou())
        ->get('/mitra/mou/buat/'.$id)
        ->assertOk()
        ->assertSee('Riwayat Versi');

    $this->withSession(sesiGenMou())
        ->post('/mitra/mou/buat/'.$id, ['aksi' => 'kirim', 'bagian' => ['Bagian baru']])
        ->assertRedirect('/mitra/mou');

    expect(DrafMouMitra::milik('user1', $id)['status'])->toBe('terkirim');
});

test('mitra lain ditolak 404 saat membuka draf bukan miliknya', function () {
    $this->withSession(sesiGenMou())->post('/mitra/mou/buat', formLengkapiMou());
    $id = DrafMouMitra::untukPemilik('user1')[0]['id'];

    $this->withSession(sesiGenMou('lain@contoh.test', 'user2'))
        ->get('/mitra/mou/buat/'.$id)
        ->assertNotFound();

    $this->withSession(sesiGenMou('lain@contoh.test', 'user2'))
        ->get('/mitra/mou/buat/'.$id.'/cetak')
        ->assertNotFound();
});

test('input form panjang berlebih ditolak validasi', function () {
    $this->withSession(sesiGenMou())
        ->post('/mitra/mou/buat', formLengkapiMou(['tempat' => str_repeat('x', 400)]))
        ->assertSessionHasErrors('tempat');
});

test('data pengajuan tidak dapat ditimpa lewat input klien', function () {
    $this->withSession(sesiGenMou())
        ->post('/mitra/mou/buat', formLengkapiMou([
            'nama_instansi' => 'INSTANSI PALSU',
            'bidang' => 'VSGA',
        ]));

    $id = DrafMouMitra::untukPemilik('user1')[0]['id'];
    $draf = DrafMouMitra::milik('user1', $id);
    $teks = json_encode($draf['blok'], JSON_UNESCAPED_UNICODE);

    expect($teks)->toContain('Pemerintah Provinsi Jawa Barat')
        ->and($teks)->not->toContain('INSTANSI PALSU');
});

test('riwayat versi bertambah pada setiap simpanan', function () {
    $this->withSession(sesiGenMou())->post('/mitra/mou/buat', formLengkapiMou());
    $id = DrafMouMitra::untukPemilik('user1')[0]['id'];

    $this->withSession(sesiGenMou())->post('/mitra/mou/buat/'.$id, ['aksi' => 'simpan', 'bagian' => ['A']]);
    $this->withSession(sesiGenMou())->post('/mitra/mou/buat/'.$id, ['aksi' => 'simpan', 'bagian' => ['B']]);

    $draf = DrafMouMitra::milik('user1', $id);
    expect($draf['versi'])->toBe(3)
        ->and(count($draf['riwayat']))->toBe(3);
});

test('suntingan mitra tidak menyimpan tag HTML', function () {
    $this->withSession(sesiGenMou())->post('/mitra/mou/buat', formLengkapiMou());
    $id = DrafMouMitra::untukPemilik('user1')[0]['id'];

    $this->withSession(sesiGenMou())
        ->post('/mitra/mou/buat/'.$id, ['aksi' => 'simpan', 'bagian' => ['<script>alert(1)</script>aman']]);

    $draf = DrafMouMitra::milik('user1', $id);
    $teks = json_encode($draf['blok'], JSON_UNESCAPED_UNICODE);

    expect($teks)->not->toContain('<script>')
        ->and($teks)->toContain('aman');
});
