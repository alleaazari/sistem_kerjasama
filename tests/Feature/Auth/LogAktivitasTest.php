<?php

/*
|--------------------------------------------------------------------------
| Log aktivitas aksi sensitif
|--------------------------------------------------------------------------
| Setuju/tolak pengajuan dan ubah masa aktif harus tercatat: waktu, peran,
| nama akun, aksi, target, IP. Berkas JSON dibersihkan sebelum & sesudah test
| agar tidak bergantung isi yang ditinggalkan sesi lain.
*/

use App\Support\LogAktivitas;
use App\Support\MasaAktifMitra;

function sesiLog(string $peran, string $nama = 'Nama Uji'): array
{
    return ['admin_auth' => ['name' => $nama, 'role' => $peran]];
}

beforeEach(function () {
    @unlink(LogAktivitas::path());
});

afterAll(function () {
    @unlink(LogAktivitas::path());
});

test('menyetujui pengajuan mencatat log lengkap', function () {
    $this->withSession(sesiLog('superadmin', 'Sari Super'))
        ->post(route('superadmin.inbox.approve', '1'))
        ->assertSessionHasNoErrors();

    $log = LogAktivitas::all();

    expect($log)->toHaveCount(1)
        ->and($log[0]['aksi'])->toBe('inbox.approve')
        ->and($log[0]['peran'])->toBe('superadmin')
        ->and($log[0]['nama'])->toBe('Sari Super')
        ->and($log[0]['target'])->toBe('1')
        ->and($log[0]['ip'])->not->toBe('')
        ->and($log[0]['waktu'])->toMatch('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/');
});

test('menolak pengajuan mencatat log lengkap', function () {
    $this->withSession(sesiLog('superadmin', 'Budi Super'))
        ->post(route('superadmin.inbox.reject', '2'), ['feedback' => 'Dokumen administrasi belum lengkap.'])
        ->assertSessionHasNoErrors();

    $log = LogAktivitas::all();

    expect($log)->toHaveCount(1)
        ->and($log[0]['aksi'])->toBe('inbox.reject')
        ->and($log[0]['target'])->toBe('2')
        ->and($log[0]['nama'])->toBe('Budi Super');
});

test('mengubah masa aktif mencatat log dengan UUID sebagai target', function () {
    $baris = MasaAktifMitra::all()[0];

    $this->withSession(sesiLog('admin_kerjasama', 'Rina Kerjasama'))
        ->put(route('kerjasama.masa_aktif.atur'), [
            'uuid' => $baris['uuid'],
            'tanggal_mulai' => '2027-01-01',
            'tanggal_berakhir' => '2029-01-01',
            'alasan' => 'Perpanjangan dua tahun',
        ])
        ->assertSessionHasNoErrors();

    $log = LogAktivitas::all();

    expect($log)->toHaveCount(1)
        ->and($log[0]['aksi'])->toBe('masa_aktif.atur')
        ->and($log[0]['peran'])->toBe('admin_kerjasama')
        ->and($log[0]['target'])->toBe($baris['uuid']);
});

test('admin kerja sama yang memutuskan pengajuan juga tercatat di log', function () {
    $this->withSession(sesiLog('admin_kerjasama', 'Rina Kerjasama'))
        ->post(route('kerjasama.inbox.approve', '1'))
        ->assertSessionHasNoErrors();

    $this->withSession(sesiLog('admin_kerjasama', 'Rina Kerjasama'))
        ->post(route('kerjasama.inbox.reject', '3'), ['feedback' => 'Kuota tahun anggaran sudah penuh.'])
        ->assertSessionHasNoErrors();

    $log = LogAktivitas::all();

    expect($log)->toHaveCount(2)
        ->and($log[0]['aksi'])->toBe('inbox.reject')
        ->and($log[0]['peran'])->toBe('admin_kerjasama')
        ->and($log[0]['nama'])->toBe('Rina Kerjasama')
        ->and($log[0]['target'])->toBe('3')
        ->and($log[1]['aksi'])->toBe('inbox.approve');
});

test('aksi yang ditolak middleware tidak meninggalkan log', function () {
    // Admin Kerja Sama menembak halaman superadmin: ditolak sebelum controller
    // jalan, jadi tidak boleh ada catatan log.
    $this->withSession(sesiLog('admin_kerjasama'))
        ->post(route('superadmin.inbox.approve', '1'))
        ->assertNotFound();

    expect(LogAktivitas::all())->toBe([]);
});

test('permintaan gagal (target tidak ada) tidak dicatat sebagai aksi', function () {
    $this->withSession(sesiLog('superadmin'))
        ->post(route('superadmin.inbox.approve', '99999'))
        ->assertSessionHas('error');

    expect(LogAktivitas::all())->toBe([]);
});

test('log tidak pernah memuat kata sandi atau hash', function () {
    $this->withSession(sesiLog('superadmin'))
        ->post(route('superadmin.inbox.approve', '1'));

    $isi = (string) file_get_contents(LogAktivitas::path());

    expect($isi)->not->toContain('password')
        ->not->toContain('$2y$')
        ->not->toContain('superrahasia');
});

test('catatan terbaru muncul lebih dulu', function () {
    $this->withSession(sesiLog('superadmin'))->post(route('superadmin.inbox.approve', '1'));
    $this->withSession(sesiLog('superadmin'))->post(route('superadmin.inbox.approve', '2'));

    expect(LogAktivitas::all()[0]['target'])->toBe('2');
});

// ---------------------------------------------------------------- halaman

test('halaman aktivitas hanya bisa dibuka superadmin', function () {
    $this->withSession(sesiLog('superadmin'))->get(route('superadmin.aktivitas'))->assertOk();
    $this->withSession(sesiLog('admin_kerjasama'))->get(route('superadmin.aktivitas'))->assertNotFound();
});

test('halaman aktivitas menampilkan log yang tercatat', function () {
    $this->withSession(sesiLog('superadmin', 'Nama Tampil'))
        ->post(route('superadmin.inbox.approve', '1'));

    $this->withSession(sesiLog('superadmin'))
        ->get(route('superadmin.aktivitas'))
        ->assertOk()
        ->assertSee('Nama Tampil')
        ->assertSee('Menyetujui pengajuan')
        ->assertSee('Super Administrator');
});

test('filter peran dan aksi menyaring tabel', function () {
    LogAktivitas::catat('inbox.approve', '7');

    $this->withSession(sesiLog('superadmin'))
        ->get(route('superadmin.aktivitas', ['aksi' => 'masa_aktif.atur']))
        ->assertOk()
        ->assertSee('Tidak ada aktivitas yang cocok dengan filter');
});

test('tanggal filter tidak valid ditolak validasi', function () {
    $this->withSession(sesiLog('superadmin'))
        ->get(route('superadmin.aktivitas', ['dari' => '17-11-2026']))
        ->assertSessionHasErrors('dari');
});

test('halaman aktivitas menampilkan keadaan kosong', function () {
    $this->withSession(sesiLog('superadmin'))
        ->get(route('superadmin.aktivitas'))
        ->assertOk()
        ->assertSee('Belum ada aktivitas tercatat');
});

test('halaman pengguna hanya untuk superadmin dan tidak menampilkan hash', function () {
    $html = $this->withSession(sesiLog('superadmin'))
        ->get(route('superadmin.pengguna'))
        ->assertOk()
        ->assertSee('Kelola Pengguna')
        ->getContent();

    expect($html)->not->toContain('$2y$')
        ->not->toContain('password_hash');

    $this->withSession(sesiLog('admin_kerjasama'))->get(route('superadmin.pengguna'))->assertNotFound();
});

test('tombol pengguna yang butuh backend dinonaktifkan dengan alasan', function () {
    $html = $this->withSession(sesiLog('superadmin'))
        ->get(route('superadmin.pengguna'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('disabled')
        ->toContain('Menunggu backend');
});
