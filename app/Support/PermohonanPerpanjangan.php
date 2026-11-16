<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Penyimpanan permohonan perpanjangan masa aktif kerja sama (mode demo).
 *
 * Satu sumber: storage/app/permohonan_perpanjangan.json — pola sama dengan
 * storage_pengajuan.json. Bukan session, agar permohonan dari mitra terbaca
 * oleh admin di browser/port berbeda.
 *
 * Status: 'Menunggu', 'Disetujui', 'Ditolak'.
 */
final class PermohonanPerpanjangan
{
    public const MENUNGGU = 'Menunggu';

    public const DISETUJUI = 'Disetujui';

    public const DITOLAK = 'Ditolak';

    /** Durasi yang boleh dipilih mitra maupun admin, dalam tahun. */
    public const DURASI = [1, 2, 3];

    public static function path(): string
    {
        return storage_path('app/permohonan_perpanjangan.json');
    }

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        if (! is_file(self::path())) {
            return [];
        }

        $data = json_decode((string) file_get_contents(self::path()), true);

        return is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
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

    /**
     * Permohonan terbaru milik satu mitra (bila ada).
     *
     * @return array<string, mixed>|null
     */
    public static function terbaruUntuk(string $username): ?array
    {
        foreach (self::all() as $row) {
            if (($row['username'] ?? null) === $username) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Ajukan permohonan baru. Permohonan yang masih menunggu tidak diganti;
     * pemanggil memeriksa lewat terbaruUntuk() lebih dulu.
     *
     * @return array<string, mixed>
     */
    public static function ajukan(string $username, string $instansi, int $durasi, string $catatan, ?string $selesaiLama = null): array
    {
        $row = [
            'id' => (string) Str::uuid(),
            'username' => $username,
            'instansi' => $instansi,
            'durasi' => $durasi,
            'catatan' => $catatan,
            'selesai_lama' => $selesaiLama,
            'status' => self::MENUNGGU,
            'diajukan_pada' => CarbonImmutable::now(config('app.timezone', 'UTC'))->toDateString(),
            'diputus_pada' => null,
            'mulai_baru' => null,
            'selesai_baru' => null,
            'alasan' => '',
        ];

        $rows = self::all();
        array_unshift($rows, $row);
        self::simpan($rows);

        return $row;
    }

    /** @return array<int, array<string, mixed>> */
    public static function menunggu(): array
    {
        return array_values(array_filter(
            self::all(),
            fn (array $r) => ($r['status'] ?? null) === self::MENUNGGU
        ));
    }

    public static function jumlahMenunggu(): int
    {
        return count(self::menunggu());
    }

    /** @return array<string, mixed>|null */
    public static function cari(string $id): ?array
    {
        foreach (self::all() as $row) {
            if (($row['id'] ?? null) === $id) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Setujui permohonan. Tanggal mulai baru menyambung dari tanggal berakhir
     * lama (bila masih berjalan) atau dari hari ini (bila sudah lewat).
     *
     * @return array<string, mixed>|null
     */
    public static function setujui(string $id, string $selesaiLama, int $durasi): ?array
    {
        return self::putuskan($id, [
            'status' => self::DISETUJUI,
            'mulai_baru' => self::mulaiBaru($selesaiLama)->toDateString(),
            'selesai_baru' => self::mulaiBaru($selesaiLama)->addYearsNoOverflow($durasi)->toDateString(),
        ]);
    }

    /** @return array<string, mixed>|null */
    public static function tolak(string $id, string $alasan): ?array
    {
        return self::putuskan($id, [
            'status' => self::DITOLAK,
            'alasan' => $alasan,
            'mulai_baru' => null,
            'selesai_baru' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $perubahan
     * @return array<string, mixed>|null
     */
    private static function putuskan(string $id, array $perubahan): ?array
    {
        $rows = self::all();
        $hasil = null;

        foreach ($rows as &$row) {
            if (($row['id'] ?? null) !== $id) {
                continue;
            }

            if (($row['status'] ?? null) !== self::MENUNGGU) {
                break; // sudah diputuskan: jangan timpa keputusan lama
            }

            $row = array_merge($row, $perubahan, [
                'diputus_pada' => CarbonImmutable::now(config('app.timezone', 'UTC'))->toDateString(),
            ]);
            $hasil = $row;
            break;
        }
        unset($row);

        if ($hasil !== null) {
            self::simpan($rows);
        }

        return $hasil;
    }

    /** Mulai periode baru: sambung dari tanggal berakhir lama, minimum hari ini. */
    private static function mulaiBaru(string $selesaiLama): CarbonImmutable
    {
        $zona = config('app.timezone', 'UTC');
        $hariIni = CarbonImmutable::now($zona)->startOfDay();
        $akhir = StatusKerjasama::parseTanggal($selesaiLama);

        if (! $akhir || $akhir->lessThan($hariIni)) {
            return $hariIni;
        }

        return $akhir;
    }
}
