<?php

namespace App\Support;

/**
 * Penyimpanan pengajuan kerja sama (mode demo: satu berkas JSON).
 *
 * Sebelumnya fungsi global di routes/admin.php; dipindahkan ke sini supaya
 * kedua prefix peran (superadmin & admin-kerja-sama) memakai satu
 * implementasi yang sama, dan logika penyimpanan tidak tinggal di route.
 *
 * TODO-BACKEND: ganti dengan model Eloquent + tabel `pengajuan` (UUID sebagai
 * route key). Kontrak method dipertahankan agar pemanggil tidak berubah.
 */
final class PengajuanMasuk
{
    /** Data contoh saat berkas belum ada. */
    public const CONTOH = [
        ['id' => 1, 'instansi' => 'Pemerintah Provinsi Jawa Barat', 'organisasi' => 'Dinas Komunikasi dan Informatika', 'pic' => 'Rina Wulandari', 'email' => 'rina@jabarprov.go.id', 'telp' => '0812-3456-7890', 'status' => 'Menunggu'],
        ['id' => 2, 'instansi' => 'Universitas Indonesia', 'organisasi' => 'Direktorat Kerja Sama & Riset', 'pic' => 'Dr. Bagas Pratama', 'email' => 'bagas@ui.ac.id', 'telp' => '0813-1111-2222', 'status' => 'Disetujui'],
        ['id' => 3, 'instansi' => 'PT Telekomunikasi Nusantara', 'organisasi' => 'Divisi Hubungan Pemerintah', 'pic' => 'Maya Sari', 'email' => 'maya@telenusa.co.id', 'telp' => '0857-9999-0000', 'status' => 'Ditolak'],
    ];

    public static function path(): string
    {
        return storage_path('app/storage_pengajuan.json');
    }

    /**
     * Daftar pengajuan tersimpan; berkas dibuat dari data contoh bila kosong.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        $file = self::path();

        if (file_exists($file)) {
            $data = json_decode((string) file_get_contents($file), true);

            if (is_array($data) && $data !== []) {
                /** @var array<int, array<string, mixed>> $data */
                return $data;
            }
        }

        self::save(self::CONTOH);

        return self::CONTOH;
    }

    /**
     * @param  array<int, array<string, mixed>>  $data
     */
    public static function save(array $data): void
    {
        file_put_contents(
            self::path(),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * Cari satu pengajuan menurut id; null bila tidak ada.
     *
     * @return array<string, mixed>|null
     */
    public static function cari(int|string $id): ?array
    {
        foreach (self::all() as $baris) {
            if ((string) ($baris['id'] ?? '') === (string) $id) {
                return $baris;
            }
        }

        return null;
    }
}
