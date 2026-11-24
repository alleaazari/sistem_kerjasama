<?php

use App\Support\BidangKerjasama;
use Illuminate\Support\Facades\File;

/*
|--------------------------------------------------------------------------
| Bidang kerja sama: konsistensi label di seluruh tampilan
|--------------------------------------------------------------------------
| Satu sumber (config/kerjasama.php) dipakai form pengajuan, dashboard mitra,
| dan generator MoU. Tidak ada label bidang yang ditulis ulang di view.
|
| Catatan: berkas pengajuan ditulis pada path storage sebenarnya lalu
| dipulihkan, supaya template MoU dari storage/app/mou tetap dapat dimuat.
*/

/** @param array<int, array<string, mixed>> $baris */
function denganPengajuan(array $baris, Closure $uji): void
{
    $path = storage_path('app/storage_pengajuan.json');
    $cadangan = file_exists($path) ? (string) file_get_contents($path) : null;

    File::ensureDirectoryExists(dirname($path));
    File::put($path, json_encode($baris, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    try {
        $uji();
    } finally {
        if ($cadangan === null) {
            File::delete($path);
        } else {
            File::put($path, $cadangan);
        }
    }
}

test('label bidang identik di form, dashboard mitra, dan generator', function () {
    foreach (BidangKerjasama::aktif() as $kode => $item) {
        $pengajuan = [
            'id' => 1, 'email' => 'saya@univ.ac.id', 'instansi' => 'Universitas Saya', 'bidang' => $kode,
            'kota' => 'Kota Medan', 'provinsi' => 'Sumatera Utara', 'alamat' => 'Jl. Uji 1',
            'pic' => 'Budi', 'jabatan_pic' => 'Dekan', 'pembiayaan' => 'APBN', 'status' => 'Disetujui',
        ];

        denganPengajuan([$pengajuan], function () use ($item): void {
            // Form pengajuan: label + deskripsi + badge jenis dokumen.
            $form = $this->get(route('pengajuan.create'))->assertOk();
            $form->assertSee($item['label']);
            if ($item['jenis_dokumen'] !== '') {
                $form->assertSee($item['jenis_dokumen']);
            }

            // Dashboard mitra: label dari config yang sama.
            $this->withSession(['mitra_data' => ['username' => 'user1', 'email' => 'saya@univ.ac.id']])
                ->get(route('mitra.status'))
                ->assertOk()
                ->assertSee($item['label']);

            // Generator MoU: halaman buat memuat label bidang dari config.
            $this->withSession(['mitra_data' => ['username' => 'user1', 'email' => 'saya@univ.ac.id']])
                ->get(route('mitra.mou.buat'))
                ->assertOk()
                ->assertSee($item['label']);
        });
    }
});

test('jenis dokumen bidang tampil di dashboard mitra', function () {
    $item = BidangKerjasama::item('VSGA');

    denganPengajuan([
        ['id' => 1, 'email' => 'saya@univ.ac.id', 'instansi' => 'Universitas Saya', 'bidang' => 'VSGA'],
    ], function () use ($item): void {
        $this->withSession(['mitra_data' => ['username' => 'user1', 'email' => 'saya@univ.ac.id']])
            ->get(route('mitra.status'))
            ->assertOk()
            ->assertSee($item['jenis_dokumen']);
    });
});
