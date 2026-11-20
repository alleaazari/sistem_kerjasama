<?php

use App\Support\LogAktivitas;
use App\Support\TemplateMou;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Panel Superadmin: editor template MoU
|--------------------------------------------------------------------------
| Template disimpan di storage/app/template_mou.json; versi dan siapa yang
| mengubah dicatat ke storage/app/log_aktivitas.json. Hanya Superadmin yang
| memiliki kemampuan `template_mou`.
*/

function sesiTemplateAdmin(string $role = 'superadmin', string $nama = 'Super Admin'): array
{
    return ['admin_auth' => ['name' => $nama, 'role' => $role]];
}

function direktoriTemplateAdmin(): string
{
    $temp = sys_get_temp_dir().'/kerjasama-template-admin-'.uniqid();
    File::makeDirectory($temp.'/app', 0755, true);

    return $temp;
}

beforeEach(function () {
    $this->app->instance('template_admin_temp', $temp = direktoriTemplateAdmin());
    $this->app->useStoragePath($temp);
    TemplateMou::semua(); // tulis seed ke storage sementara
});

afterEach(function () {
    File::deleteDirectory($this->app->make('template_admin_temp'));
});

test('superadmin dapat membuka editor template per bidang', function () {
    $this->withSession(sesiTemplateAdmin())
        ->get(route('superadmin.template_mou'))
        ->assertOk()
        ->assertSee('Template MoU')
        ->assertSee('VSGA')
        ->assertSee('GTA')
        ->assertSee('Kembalikan ke bawaan')
        ->assertSee('Universitas Contoh Nusantara')
        // Menu sidebar berasal dari config, bukan tautan hardcode.
        ->assertSee(route('superadmin.template_mou'), false);
});

test('superadmin menyimpan template dan versi dicatat di log', function () {
    $isi = "NOTA KESEPAHAMAN\n\n{{nama_instansi}} - {{bidang}}\n\n{{nama_pic}}, {{jabatan_pic}}, {{alamat}}, {{tanggal}}";

    $this->withSession(sesiTemplateAdmin('superadmin', 'Super Admin'))
        ->put(route('superadmin.template_mou.update', 'VSGA'), [
            'judul' => 'Judul Baru VSGA',
            'catatan' => 'Catatan versi baru.',
            'isi' => $isi,
        ])
        ->assertRedirect(route('superadmin.template_mou', ['kode' => 'VSGA']))
        ->assertSessionHas('ok');

    $row = TemplateMou::untuk('VSGA');
    expect($row)->not->toBeNull()
        ->and($row['judul'])->toBe('Judul Baru VSGA')
        ->and($row['catatan'])->toBe('Catatan versi baru.')
        ->and($row['isi'])->toBe($isi)
        ->and($row['versi'])->toBe(2)
        ->and($row['diperbarui_oleh'])->toBe('Super Admin');

    $log = LogAktivitas::all()[0];
    expect($log['aksi'])->toBe('template_mou.update')
        ->and($log['peran'])->toBe('superadmin')
        ->and($log['nama'])->toBe('Super Admin')
        ->and($log['target'])->toContain('VSGA')
        ->and($log['target'])->toContain('versi 2');

    // Riwayat tampil kembali di halaman editor.
    $this->withSession(sesiTemplateAdmin())
        ->get(route('superadmin.template_mou', ['kode' => 'VSGA']))
        ->assertOk()
        ->assertSee('Menyimpan template MoU')
        ->assertSee('Versi 2');
});

test('placeholder tidak dikenal ditolak dan template tidak berubah', function () {
    $sebelum = TemplateMou::untuk('VSGA');

    $this->withSession(sesiTemplateAdmin())
        ->from(route('superadmin.template_mou', ['kode' => 'VSGA']))
        ->put(route('superadmin.template_mou.update', 'VSGA'), [
            'judul' => 'Judul Uji',
            'catatan' => '',
            'isi' => 'Halo {{token_rahasia}} dan {{nama_instansi}}.',
        ])
        ->assertSessionHasErrors('isi');

    expect(TemplateMou::untuk('VSGA')['isi'])->toBe($sebelum['isi'])
        ->and(LogAktivitas::all())->toBe([]);
});

test('placeholder dengan spasi diterima dan tetap diganti saat render', function () {
    $isi = "Halo {{ nama_instansi }} di bidang {{bidang}}.";

    $this->withSession(sesiTemplateAdmin())
        ->put(route('superadmin.template_mou.update', 'VSGA'), [
            'judul' => 'Judul Spasi',
            'catatan' => '',
            'isi' => $isi,
        ])
        ->assertSessionHasNoErrors();

    $template = TemplateMou::untuk('VSGA');
    expect($template['isi'])->toBe($isi);

    $hasil = TemplateMou::render($template, TemplateMou::contohData('VSGA'));

    expect($hasil)->toContain('Universitas Contoh Nusantara')
        ->not->toContain('{{');
});

test('placeholder berbentuk tidak baku tetap ditolak', function (string $isi) {
    $this->withSession(sesiTemplateAdmin())
        ->from(route('superadmin.template_mou', ['kode' => 'VSGA']))
        ->put(route('superadmin.template_mou.update', 'VSGA'), [
            'judul' => 'Judul Uji',
            'catatan' => '',
            'isi' => $isi,
        ])
        ->assertSessionHasErrors('isi');
})->with([
    'tanda hubung' => ['Halo {{token-rahasia}}.'],
    'tanpa nama' => ['Halo {{}}.'],
]);

test('tag HTML berbahaya ditolak', function () {
    $this->withSession(sesiTemplateAdmin())
        ->from(route('superadmin.template_mou', ['kode' => 'VSGA']))
        ->put(route('superadmin.template_mou.update', 'VSGA'), [
            'judul' => 'Judul Uji',
            'catatan' => '',
            'isi' => 'Isi <script>alert(1)</script> {{nama_instansi}}.',
        ])
        ->assertSessionHasErrors('isi');
});

test('judul dan isi template wajib terisi', function () {
    $this->withSession(sesiTemplateAdmin())
        ->from(route('superadmin.template_mou', ['kode' => 'VSGA']))
        ->put(route('superadmin.template_mou.update', 'VSGA'), [
            'judul' => '',
            'catatan' => '',
            'isi' => '',
        ])
        ->assertSessionHasErrors(['judul', 'isi']);
});

test('role lain ditolak untuk GET, PUT, dan POST', function (string $role) {
    $sesi = sesiTemplateAdmin($role);

    $this->withSession($sesi)
        ->get(route('superadmin.template_mou'))
        ->assertNotFound();

    $this->withSession($sesi)
        ->put(route('superadmin.template_mou.update', 'VSGA'), [
            'judul' => 'Tidak boleh',
            'catatan' => '',
            'isi' => 'Tidak boleh {{nama_instansi}}.',
        ])
        ->assertNotFound();

    $this->withSession($sesi)
        ->post(route('superadmin.template_mou.reset', 'VSGA'))
        ->assertNotFound();
})->with(['admin_kerjasama', 'developer', 'mitra']);

test('kode bidang di luar config menjadi 404', function () {
    $this->withSession(sesiTemplateAdmin())
        ->put(route('superadmin.template_mou.update', 'XYZ'), [
            'judul' => 'Judul Uji',
            'catatan' => '',
            'isi' => 'Isi {{nama_instansi}}.',
        ])
        ->assertNotFound();
});

test('kembalikan ke bawaan memulihkan isi dan mencatat log', function () {
    $bawaan = TemplateMou::bawaan('VSGA');
    expect($bawaan)->not->toBeNull();

    TemplateMou::simpanPerubahan('VSGA', 'Judul Sementara', '', 'Isi sementara.', 'Uji Sebelumnya');

    $this->withSession(sesiTemplateAdmin())
        ->post(route('superadmin.template_mou.reset', 'VSGA'))
        ->assertRedirect(route('superadmin.template_mou', ['kode' => 'VSGA']))
        ->assertSessionHas('ok');

    $row = TemplateMou::untuk('VSGA');
    expect($row['judul'])->toBe($bawaan['judul'])
        ->and($row['isi'])->toBe($bawaan['isi'])
        ->and($row['versi'])->toBe(3);

    expect(LogAktivitas::all()[0]['aksi'])->toBe('template_mou.reset');
});

test('bidang baru dari config tampil sebagai slot yang belum dibuat', function () {
    config(['kerjasama.bidang' => [
        'VSGA' => 'Vocational School Graduate Academy (VSGA)',
        'GTA' => 'Government Transformation Academy (GTA)',
        'JKP' => 'Jaringan Kompetensi Pemerintahan (contoh)',
    ]]);

    $this->withSession(sesiTemplateAdmin())
        ->get(route('superadmin.template_mou', ['kode' => 'JKP']))
        ->assertOk()
        ->assertSee('JKP')
        ->assertSee('Belum ada template')
        ->assertSee('Belum dibuat');
});

test('generator mitra memakai template terbaru dan draf lama tetap', function () {
    File::put(
        storage_path('app/storage_pengajuan.json'),
        json_encode([['id' => 1, 'email' => 'saya@univ.ac.id', 'bidang' => 'VSGA']], JSON_PRETTY_PRINT)
    );

    $isiBaru = "PENANDA VERSI BARU\n\n{{nama_instansi}} - {{bidang}}";

    $this->withSession(sesiTemplateAdmin())
        ->put(route('superadmin.template_mou.update', 'VSGA'), [
            'judul' => 'Judul Terbaru',
            'catatan' => '',
            'isi' => $isiBaru,
        ])
        ->assertRedirect();

    $mitra = ['mitra_data' => [
        'username' => 'user1',
        'email' => 'saya@univ.ac.id',
        'instansi' => 'Universitas Uji',
        'pic' => 'Rina Wulandari',
        'jabatan_pic' => 'Kepala Humas',
        'alamat' => 'Jl. Uji No. 1, Medan',
    ]];

    $this->withSession($mitra)
        ->get(route('mitra.mou.draf'))
        ->assertOk()
        ->assertSee('PENANDA VERSI BARU')
        ->assertSee('Universitas Uji');

    $this->withSession(array_merge($mitra, ['mitra_mou_draf' => 'DRAF LAMA TIDAK BERUBAH']))
        ->get(route('mitra.mou.draf'))
        ->assertOk()
        ->assertSee('DRAF LAMA TIDAK BERUBAH')
        ->assertDontSee('PENANDA VERSI BARU');
});
