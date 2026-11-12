<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Penyimpanan draf proposal AI milik mitra (mode demo).
 *
 * Satu sumber: storage/app/draf_mitra.json — pola sama dengan
 * storage_pengajuan.json dan permohonan_perpanjangan.json.
 *
 * TODO-BACKEND: ganti ke model + tabel DB (draf_proposal), kolom:
 * uuid, mitra_id, instansi, judul_ks, tanggal_berakhir, buatan_pada,
 * berkas_path, berkas_nama_asli.
 */
final class DrafMitra
{
    /** Jumlah baris per halaman. */
    public const PER_HALAMAN = 10;

    /** Berkas draf belum tersedia: unduhan dimatikan. */
    public const BERKAS_SIAP = false;

    public static function path(): string
    {
        return storage_path('app/draf_mitra.json');
    }

    /**
     * Seluruh baris. Bila berkas belum ada, isi dari data seed lalu simpan.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        if (! is_file(self::path())) {
            $seed = self::seed();
            self::simpan($seed);

            return $seed;
        }

        $data = json_decode((string) file_get_contents(self::path()), true);

        return is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
    }

    /**
     * Cari satu baris berdasarkan UUID. UUID tidak berurutan dan tidak bisa
     * ditebak, sehingga aman dipakai di URL.
     *
     * @return array<string, mixed>|null
     */
    public static function cari(string $uuid): ?array
    {
        foreach (self::all() as $row) {
            if (($row['uuid'] ?? null) === $uuid) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Saring berdasarkan instansi atau judul kemitraan.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function cariKata(string $kata): array
    {
        $kata = trim($kata);
        if ($kata === '') {
            return self::all();
        }

        return array_values(array_filter(self::all(), function (array $row) use ($kata): bool {
            $cari = mb_strtolower($kata);
            $instansi = mb_strtolower((string) ($row['instansi'] ?? ''));
            $judul = mb_strtolower((string) ($row['judul_ks'] ?? ''));

            return str_contains($instansi, $cari) || str_contains($judul, $cari);
        }));
    }

    /**
     * Satu halaman hasil saring, urut dari yang terbaru.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{data: array<int, array<string, mixed>>, total: int, halaman: int, halaman_akhir: int}
     */
    public static function halaman(array $rows, int $halaman, int $perHalaman = self::PER_HALAMAN): array
    {
        $total = count($rows);
        $halamanAkhir = max(1, (int) ceil($total / $perHalaman));
        $halaman = min(max(1, $halaman), $halamanAkhir);

        return [
            'data' => array_slice($rows, ($halaman - 1) * $perHalaman, $perHalaman),
            'total' => $total,
            'halaman' => $halaman,
            'halaman_akhir' => $halamanAkhir,
        ];
    }

    /**
     * Data contoh. Tanggal relatif terhadap hari ini agar status selalu masuk akal.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function seed(): array
    {
        $hariIni = CarbonImmutable::now(config('app.timezone', 'UTC'))->startOfDay();

        return [
            [
                'uuid' => (string) Str::uuid(),
                'instansi' => 'Pemerintah Provinsi Jawa Barat',
                'judul_ks' => 'Pelatihan Literasi Digital & SPBE',
                'tanggal_berakhir' => $hariIni->addDays(200)->toDateString(),
                'buatan_pada' => $hariIni->subDays(31)->toDateString(),
            ],
            [
                'uuid' => (string) Str::uuid(),
                'instansi' => 'Universitas Indonesia',
                'judul_ks' => 'Magang DTS & Riset AI',
                'tanggal_berakhir' => $hariIni->addMonthsNoOverflow(20)->toDateString(),
                'buatan_pada' => $hariIni->subDays(38)->toDateString(),
            ],
        ];
    }

    /** @param array<int, array<string, mixed>> $rows */
    private static function simpan(array $rows): void
    {
        $dir = dirname(self::path());
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        file_put_contents(
            self::path(),
            json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
