<?php

use App\Support\LogAktivitas;
use App\Support\Mou\StrukturTemplateMou;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Panel Superadmin: editor template MoU berbasis blok
|--------------------------------------------------------------------------
| Template disimpan di storage/app/mou/*.json; isi bawaan di
| resources/mou-bawaan/*.json. Versi dan siapa yang mengubah dicatat ke
| storage/app/log_aktivitas.json. Hanya Superadmin yang memiliki kemampuan
| `template_mou`. Perubahan berlaku untuk draf mitra BERIKUTNYA; draf lama
| tidak berubah.
*/

function sesiEditorMou(string $role = 'superadmin', string $nama = 'Super Admin'): array
{
    return ['admin_auth' => ['name' => $nama, 'role' => $role]];
}

function direktoriEditorMou(): string
{
    $temp = sys_get_temp_dir().'/kerjasama-editor-mou-'.uniqid();
    File::makeDirectory($temp.'/app/mou', 0755, true);

    // Salin template runtime ke storage sementara.
    foreach (glob(storage_path('app/mou/*.json')) ?: [] as $file) {
        File::copy($file, $temp.'/app/mou/'.basename($file));
    }

    return $temp;
}

/** Blok minimal yang sah untuk kirim editor. */
function blokEditor(int $jumlahPasal = 2, bool $opsionalAktif = true): string
{
    $pasal = [];
    for ($i = 1; $i <= $jumlahPasal; $i++) {
        $pasal[] = [
            'nomor' => $i,
            'judul' => 'PASAL UJI '.$i,
            'ayat' => ['Ayat pertama pasal '.$i.' untuk {{nama_instansi}}.'],
            'opsional' => false,
            'aktif_bawaan' => true,
        ];
    }

    // Satu pasal opsional di akhir supaya bisa diuji di-nonaktifkan.
    $pasal[] = [
        'nomor' => count($pasal) + 1,
        'judul' => 'PERUBAHAN',
        'ayat' => ['Pasal opsional untuk uji.'],
        'opsional' => true,
        'aktif_bawaan' => $opsionalAktif,
    ];

    return json_encode([
        ['tipe' => 'sampul', 'isi' => "NOTA KESEPAKATAN\nTENTANG\n{{judul}}"],
        ['tipe' => 'pembukaan', 'isi' => 'Pada hari ini {{tanggal}} di {{tempat}}.'],
        ['tipe' => 'pasal', 'pasal' => $pasal],
        ['tipe' => 'penutup', 'isi' => 'Demikian disepakati PARA PIHAK.'],
    ], JSON_UNESCAPED_UNICODE);
}

beforeEach(function () {
    $this->app->instance('editor_mou_temp', $temp = direktoriEditorMou());
    $this->app->useStoragePath($temp);
});

afterEach(function () {
    File::deleteDirectory($this->app->make('editor_mou_temp'));
});

// ---------------------------------------------------------------------
// Daftar + editor
// ---------------------------------------------------------------------

test('superadmin dapat membuka editor template blok per template', function () {
    $this->withSession(sesiEditorMou())
        ->get(route('superadmin.template_mou'))
        ->assertOk()
        ->assertSee('Template MoU')
        ->assertSee('Nota Kesepakatan dengan Pemerintah Daerah')
        ->assertSee('Kembalikan ke bawaan')
        ->assertSee('Pemetaan bidang ke template')
        ->assertSee('Pengaturan Pihak Kesatu')
        // Menu sidebar berasal dari config, bukan tautan hardcode.
        ->assertSee(route('superadmin.template_mou'), false);
});

test('daftar menampilkan status, versi, dan bidang terpetakan', function () {
    $this->withSession(sesiEditorMou())
        ->get(route('superadmin.template_mou'))
        ->assertOk()
        ->assertSee('Final')
        ->assertSee('Draf')
        ->assertSee('Versi 2')
        // GTA dipetakan ke nota_kesepakatan_pemda.
        ->assertSee('GTA');
});

test('superadmin menyimpan blok template dan versi dicatat di log', function () {
    $sebelum = StrukturTemplateMou::muat('nota_kesepakatan_pemda');

    $this->withSession(sesiEditorMou('superadmin', 'Super Admin'))
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Nama Baru',
            'catatan' => 'Catatan versi baru.',
            'status' => 'final',
            'blok' => blokEditor(),
        ])
        ->assertRedirect(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->assertSessionHas('ok');

    $row = StrukturTemplateMou::untuk('nota_kesepakatan_pemda');
    expect($row['nama'])->toBe('Nama Baru')
        ->and($row['catatan'])->toBe('Catatan versi baru.')
        ->and($row['versi'])->toBe($sebelum['versi'] + 1)
        ->and($row['diperbarui_oleh'])->toBe('Super Admin');

    $log = LogAktivitas::all()[0];
    expect($log['aksi'])->toBe('template_mou.update')
        ->and($log['peran'])->toBe('superadmin')
        ->and($log['target'])->toContain('nota_kesepakatan_pemda');

    // Riwayat tampil kembali di halaman editor.
    $this->withSession(sesiEditorMou())
        ->get(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->assertOk()
        ->assertSee('Menyimpan template MoU');
});

// ---------------------------------------------------------------------
// Pasal opsional
// ---------------------------------------------------------------------

test('menonaktifkan pasal opsional mengubah penomoran pasal aktif', function () {
    // Kirim template dengan pasal opsional TIDAK aktif bawaan.
    $this->withSession(sesiEditorMou())
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Uji Opsional',
            'catatan' => '',
            'status' => 'final',
            'blok' => blokEditor(2, false),
        ])
        ->assertSessionHasNoErrors();

    $template = StrukturTemplateMou::muat('nota_kesepakatan_pemda');
    $judul = StrukturTemplateMou::judulPasalAktif($template);

    // Pasal opsional tidak muncul, sisanya berurutan tanpa lubang.
    expect($judul)->toBe(['PASAL UJI 1', 'PASAL UJI 2'])
        ->and(StrukturTemplateMou::pasalAktif($template))->toHaveCount(2);
});

test('pasal opsional aktif bawaan ikut dinomori', function () {
    $this->withSession(sesiEditorMou())
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Uji Opsional Aktif',
            'catatan' => '',
            'status' => 'final',
            'blok' => blokEditor(2, true),
        ])
        ->assertSessionHasNoErrors();

    $aktif = StrukturTemplateMou::pasalAktif(StrukturTemplateMou::muat('nota_kesepakatan_pemda'));

    expect($aktif)->toHaveCount(3)
        ->and(array_column($aktif, 'nomor'))->toBe([1, 2, 3])
        ->and($aktif[2]['judul'])->toBe('PERUBAHAN');
});

// ---------------------------------------------------------------------
// Validasi server
// ---------------------------------------------------------------------

test('placeholder tidak dikenal ditolak dan template tidak berubah', function () {
    $sebelum = StrukturTemplateMou::untuk('nota_kesepakatan_pemda');

    $blok = json_decode(blokEditor(1), true);
    $blok[1]['isi'] = 'Halo {{token_rahasia}} dan {{nama_instansi}}.';

    $this->withSession(sesiEditorMou())
        ->from(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Judul Uji',
            'catatan' => '',
            'status' => 'final',
            'blok' => json_encode($blok, JSON_UNESCAPED_UNICODE),
        ])
        ->assertSessionHasErrors('blok');

    expect(StrukturTemplateMou::untuk('nota_kesepakatan_pemda'))
        ->toBe($sebelum);
});

test('tag HTML berbahaya ditolak', function () {
    $blok = json_decode(blokEditor(1), true);
    $blok[1]['isi'] = 'Isi <script>alert(1)</script> {{nama_instansi}}.';

    $this->withSession(sesiEditorMou())
        ->from(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Judul Uji',
            'catatan' => '',
            'status' => 'final',
            'blok' => json_encode($blok, JSON_UNESCAPED_UNICODE),
        ])
        ->assertSessionHasErrors('blok');
});

test('struktur blok tidak sah ditolak', function () {
    $this->withSession(sesiEditorMou())
        ->from(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Judul Uji',
            'catatan' => '',
            'status' => 'final',
            'blok' => '[{"tipe":"tidak_ada","isi":"x"}]',
        ])
        ->assertSessionHasErrors('blok');
});

test('blok bukan JSON sah ditolak', function () {
    $this->withSession(sesiEditorMou())
        ->from(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Judul Uji',
            'catatan' => '',
            'status' => 'final',
            'blok' => '{bukan json',
        ])
        ->assertSessionHasErrors('blok');
});

test('status di luar final/draf ditolak', function () {
    $this->withSession(sesiEditorMou())
        ->from(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Judul',
            'catatan' => '',
            'status' => 'selesai',
            'blok' => blokEditor(),
        ])
        ->assertSessionHasErrors('status');
});

test('nama template wajib terisi', function () {
    $this->withSession(sesiEditorMou())
        ->from(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => '',
            'catatan' => '',
            'status' => 'final',
            'blok' => blokEditor(),
        ])
        ->assertSessionHasErrors('nama');
});

// ---------------------------------------------------------------------
// Otorisasi
// ---------------------------------------------------------------------

test('role lain ditolak untuk GET dan seluruh aksi tulis', function (string $role) {
    $sesi = sesiEditorMou($role);

    $this->withSession($sesi)
        ->get(route('superadmin.template_mou'))
        ->assertNotFound();

    $this->withSession($sesi)
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Tidak boleh', 'catatan' => '', 'status' => 'final', 'blok' => blokEditor(),
        ])
        ->assertNotFound();

    $this->withSession($sesi)
        ->post(route('superadmin.template_mou.reset', 'nota_kesepakatan_pemda'))
        ->assertNotFound();

    $this->withSession($sesi)
        ->put(route('superadmin.template_mou.pengaturan'), [
            'pihak_kesatu' => ['nama_unit' => 'x', 'alamat' => 'x', 'penandatangan' => 'x', 'jabatan' => 'x', 'pejabat_pelaksana' => 'x'],
        ])
        ->assertNotFound();

    $this->withSession($sesi)
        ->put(route('superadmin.template_mou.pemetaan'), ['pemetaan' => ['GTA' => 'nota_kesepakatan_pemda']])
        ->assertNotFound();
})->with(['admin_kerjasama', 'developer', 'mitra']);

test('kode template di luar berkas menjadi 404', function () {
    $this->withSession(sesiEditorMou())
        ->put(route('superadmin.template_mou.update', 'XYZ'), [
            'nama' => 'Judul Uji', 'catatan' => '', 'status' => 'final', 'blok' => blokEditor(),
        ])
        ->assertNotFound();
});

// ---------------------------------------------------------------------
// Kembalikan ke bawaan
// ---------------------------------------------------------------------

test('kembalikan ke bawaan memulihkan isi dan mencatat log', function () {
    $bawaan = StrukturTemplateMou::bawaan('nota_kesepakatan_pemda');
    expect($bawaan)->not->toBeNull();

    // Ubah dulu supaya ada beda.
    StrukturTemplateMou::simpan('nota_kesepakatan_pemda', [
        'nama' => 'Sementara', 'catatan' => '', 'status' => 'draf', 'blok' => [],
    ], 'Uji Sebelumnya');

    $this->withSession(sesiEditorMou())
        ->post(route('superadmin.template_mou.reset', 'nota_kesepakatan_pemda'))
        ->assertRedirect(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->assertSessionHas('ok');

    $row = StrukturTemplateMou::untuk('nota_kesepakatan_pemda');
    expect($row['nama'])->toBe($bawaan['nama'])
        ->and($row['status'])->toBe($bawaan['status'])
        ->and($row['blok'])->toBe($bawaan['blok']);

    expect(LogAktivitas::all()[0]['aksi'])->toBe('template_mou.reset');
});

// ---------------------------------------------------------------------
// Pengaturan global + pemetaan
// ---------------------------------------------------------------------

test('pengaturan pihak kesatu dan dasar hukum tersimpan sebagai penimpa', function () {
    $this->withSession(sesiEditorMou())
        ->put(route('superadmin.template_mou.pengaturan'), [
            'pihak_kesatu' => [
                'nama_unit' => 'Unit Uji Kesatu',
                'alamat' => 'Jl. Uji 1',
                'telepon' => '021-000',
                'email' => 'uji@komdigi.go.id',
                'penandatangan' => 'Nama Penandatangan',
                'jabatan' => 'Jabatan',
                'pejabat_pelaksana' => 'Pejabat Pelaksana Uji',
            ],
            'dasar_hukum_nasional' => "Peraturan A\nPeraturan B",
        ])
        ->assertRedirect(route('superadmin.template_mou'))
        ->assertSessionHas('ok');

    $pengaturan = StrukturTemplateMou::pengaturan();
    expect($pengaturan['pihak_kesatu']['nama_unit'])->toBe('Unit Uji Kesatu')
        ->and($pengaturan['dasar_hukum_nasional'])->toBe(['Peraturan A', 'Peraturan B']);

    expect(LogAktivitas::all()[0]['aksi'])->toBe('template_mou.pengaturan');
});

test('pemetaan bidang tersimpan dan hanya menerima kode template yang sah', function () {
    $this->withSession(sesiEditorMou())
        ->put(route('superadmin.template_mou.pemetaan'), [
            'pemetaan' => [
                'GTA' => 'kerangka_umum',
                'VSGA' => 'TIDAK_ADA',
            ],
        ])
        ->assertRedirect(route('superadmin.template_mou'))
        ->assertSessionHas('ok');

    $peta = StrukturTemplateMou::pemetaan();
    expect($peta['GTA'])->toBe('kerangka_umum')
        // Kode template yang tidak ada diabaikan, tetap nilai config.
        ->and($peta['VSGA'])->toBe('pks_beasiswa_talenta_digital');

    expect(LogAktivitas::all()[0]['aksi'])->toBe('template_mou.pemetaan');
});

// ---------------------------------------------------------------------
// Gerbang status + draf lama
// ---------------------------------------------------------------------

test('finalisasi bidang 3 memakai template kerangka_umum menjadi dapat dipakai mitra', function () {
    $sebelum = StrukturTemplateMou::muat('kerangka_umum');
    expect(StrukturTemplateMou::dapatDipakaiMitra($sebelum))->toBeFalse();

    $this->withSession(sesiEditorMou())
        ->put(route('superadmin.template_mou.update', 'kerangka_umum'), [
            'nama' => 'Kerangka Bidang 3 Final',
            'catatan' => '',
            'status' => 'final',
            'blok' => blokEditor(2),
        ])
        ->assertSessionHasNoErrors();

    expect(StrukturTemplateMou::dapatGenerate('kerangka_umum'))->toBeTrue()
        ->and(StrukturTemplateMou::dapatDipakaiMitra(StrukturTemplateMou::untukBidang('bidang_3')))->toBeTrue();
});

test('template draf tidak dapat dipakai mitra', function () {
    // kerangka_umum bawaan berstatus draf.
    $template = StrukturTemplateMou::untukBidang('bidang_3');

    expect(StrukturTemplateMou::dapatDipakaiMitra($template))->toBeFalse()
        ->and(StrukturTemplateMou::pesanBelumTersedia())->toBe('Template belum tersedia.');
});

test('perubahan template berlaku untuk draf mitra berikutnya, draf lama tidak berubah', function () {
    File::put(
        storage_path('app/storage_pengajuan.json'),
        json_encode([[
            'id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'GTA',
            'instansi' => 'Universitas Uji', 'kota' => 'Kota Medan', 'provinsi' => 'Sumatera Utara',
            'alamat' => 'Jl. Uji', 'pic' => 'Rina', 'jabatan_pic' => 'Kepala', 'telp' => '0812',
            'metode_pelatihan' => 'luring', 'pembiayaan' => 'APBN', 'status' => 'Disetujui',
        ]], JSON_PRETTY_PRINT)
    );

    $blok = json_decode(blokEditor(1), true);
    $blok[1]['isi'] = 'PENANDA VERSI BARU di {{nama_instansi}}.';

    $this->withSession(sesiEditorMou())
        ->put(route('superadmin.template_mou.update', 'nota_kesepakatan_pemda'), [
            'nama' => 'Versi Terbaru',
            'catatan' => '',
            'status' => 'final',
            'blok' => json_encode($blok, JSON_UNESCAPED_UNICODE),
        ])
        ->assertSessionHasNoErrors();

    // Draf mitra baru memakai template terbaru.
    $this->withSession(sesiEditorMou())
        ->get(route('superadmin.template_mou', ['kode' => 'nota_kesepakatan_pemda']))
        ->assertOk()
        ->assertSee('PENANDA VERSI BARU');

    // Draf mitra lama yang sudah tersimpan tetap seperti apa adanya.
    $drafLama = [
        'id' => 'draf-lama-1', 'pemilik' => 'user1', 'kode_bidang' => 'GTA',
        'kode_template' => 'nota_kesepakatan_pemda', 'judul' => 'Draf Lama',
        'status' => 'draf', 'data' => [], 'versi' => 1, 'riwayat' => [],
        'blok' => [['tipe' => 'penutup', 'paragraf' => ['DRAF LAMA TIDAK BERUBAH']]],
    ];
    File::put(storage_path('app/draf_mou_mitra.json'), json_encode([$drafLama], JSON_PRETTY_PRINT));

    $mitra = ['mitra_data' => [
        'username' => 'user1', 'email' => 'saya@univ.ac.id', 'instansi' => 'Universitas Uji',
        'pic' => 'Rina', 'jabatan_pic' => 'Kepala', 'alamat' => 'Jl. Uji',
    ]];

    $this->withSession($mitra)
        ->get('/mitra/mou/buat/draf-lama-1')
        ->assertOk()
        ->assertSee('DRAF LAMA TIDAK BERUBAH')
        ->assertDontSee('PENANDA VERSI BARU');
});
