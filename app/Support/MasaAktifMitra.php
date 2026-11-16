<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Masa aktif kerja sama per mitra (mode demo).
 *
 * Satu sumber: storage/app/masa_aktif_mitra.json — pola sama dengan
 * permohonan_perpanjangan.json. Status TIDAK disimpan sebagai string beku:
 * selalu dihitung dari tanggal_berakhir lewat StatusKerjasama::hitung().
 *
 * TODO-BACKEND: ganti ke model + tabel DB (masa_aktif_mitra), kolom:
 * uuid, mitra_id, instansi, tanggal_mulai, tanggal_berakhir, alasan,
 * diperbarui_pada. Riwayat perubahan sebaiknya dicatat di tabel tersendiri.
 */
final class MasaAktifMitra
{
    /** Jumlah baris per halaman. */
    public const PER_HALAMAN = 10;

    /** Batas panjang alasan perubahan masa aktif. */
    public const ALASAN_MAKS = 255;

    public static function path(): string
    {
        return storage_path('app/masa_aktif_mitra.json');
    }

    /** @return array<int, array<string, mixed>> */
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

    /** @return array<string, mixed>|null */
    public static function cari(string $uuid): ?array
    {
        foreach (self::all() as $row) {
            if (($row['uuid'] ?? null) === $uuid) {
                return $row;
            }
        }

        return null;
    }

    /** @return array<int, array<string, mixed>> */
    public static function cariKata(string $kata): array
    {
        $kata = trim($kata);
        if ($kata === '') {
            return self::all();
        }

        $cari = mb_strtolower($kata);

        return array_values(array_filter(
            self::all(),
            fn (array $row) => str_contains(mb_strtolower((string) ($row['instansi'] ?? '')), $cari)
        ));
    }

    /**
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
     * Perbarui periode satu mitra. Tanggal boleh maju maupun mundur.
     * Tanggal berakhir wajib >= tanggal mulai (diperiksa ulang di controller).
     *
     * @return array<string, mixed>|null
     */
    public static function perbarui(string $uuid, string $tanggalMulai, string $tanggalBerakhir, string $alasan): ?array
    {
        $rows = self::all();
        $hasil = null;

        foreach ($rows as &$row) {
            if (($row['uuid'] ?? null) !== $uuid) {
                continue;
            }

            $row['tanggal_mulai'] = $tanggalMulai;
            $row['tanggal_berakhir'] = $tanggalBerakhir;
            $row['alasan'] = $alasan;
            $row['diperbarui_pada'] = now(config('app.timezone', 'UTC'))->toDateString();
            $hasil = $row;
            break;
        }
        unset($row);

        if ($hasil !== null) {
            self::simpan($rows);
        }

        return $hasil;
    }

    /**
     * Baris + status terhitung, siap dipakai view.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public static function denganStatus(array $row): array
    {
        $status = StatusKerjasama::hitung(
            $row['tanggal_mulai'] ?? null,
            $row['tanggal_berakhir'] ?? null
        );

        return $row + ['status' => $status];
    }

    /**
     * Data contoh. Satu baris sengaja sudah lewat agar status "Berakhir" terlihat.
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
                'tanggal_mulai' => $hariIni->subMonthsNoOverflow(1)->toDateString(),
                'tanggal_berakhir' => $hariIni->addDays(200)->toDateString(),
                'alasan' => '',
            ],
            [
                'uuid' => (string) Str::uuid(),
                'instansi' => 'Universitas Indonesia',
                'tanggal_mulai' => $hariIni->subMonthsNoOverflow(14)->toDateString(),
                'tanggal_berakhir' => $hariIni->subDays(10)->toDateString(),
                'alasan' => '',
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
