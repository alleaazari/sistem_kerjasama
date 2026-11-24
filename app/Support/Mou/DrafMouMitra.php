<?php

namespace App\Support\Mou;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Penyimpanan draf MoU milik mitra (mode demo: satu berkas JSON).
 *
 * Satu draf = satu dokumen MoU yang dibuat mitra dari template bidangnya.
 * Draf menyimpan: data form yang diisi, isi tiap bagian yang disunting mitra,
 * status (draf|terkirim), dan riwayat versi sederhana.
 *
 * Kepemilikan: setiap operasi WAJIB mengirim username pemilik dari session;
 * draf milik mitra lain tidak pernah dikembalikan.
 *
 * TODO-BACKEND: ganti dengan model Eloquent `draf_mou` (UUID route key,
 * kepemilikan lewat Policy) + tabel `draf_mou_versi`. Kontrak method tetap.
 */
final class DrafMouMitra
{
    public const STATUS_DRAF = 'draf';

    public const STATUS_TERKIRIM = 'terkirim';

    /** Batas panjang satu nilai bagian yang disunting mitra. */
    public const MAKS_BAGIAN = 5000;

    /** Maksimum versi riwayat yang disimpan per draf. */
    public const MAKS_VERSI = 20;

    public static function path(): string
    {
        return storage_path('app/draf_mou_mitra.json');
    }

    /**
     * Seluruh draf (semua pemilik). Hanya untuk pemakaian internal.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function semua(): array
    {
        $file = self::path();

        if (! is_file($file)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($file), true);

        if (! is_array($data)) {
            return [];
        }

        return array_values(array_filter($data, 'is_array'));
    }

    /**
     * Draf milik satu pemilik.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function untukPemilik(string $username): array
    {
        $username = self::normalisasiPemilik($username);

        return array_values(array_filter(
            self::semua(),
            static fn (array $d) => self::normalisasiPemilik((string) ($d['pemilik'] ?? '')) === $username,
        ));
    }

    /**
     * Draf satu pemilik menurut id UUID. Null bila tidak ada ATAU bukan milik
     * pemilik tersebut (anti-BOLA: mitra lain selalu 404).
     *
     * @return array<string, mixed>|null
     */
    public static function milik(string $username, string $id): ?array
    {
        foreach (self::untukPemilik($username) as $draf) {
            if (($draf['id'] ?? null) === $id) {
                return $draf;
            }
        }

        return null;
    }

    /**
     * Buat draf baru dari data form + isi tiap bagian. Mengembalikan draf.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, array<string, mixed>>  $blok
     * @return array<string, mixed>
     */
    public static function buat(
        string $username,
        string $kodeBidang,
        string $kodeTemplate,
        string $judul,
        array $data,
        array $blok,
    ): array {
        $sekarang = self::sekarang();

        $draf = [
            'id' => (string) Str::uuid(),
            'pemilik' => self::normalisasiPemilik($username),
            'kode_bidang' => $kodeBidang,
            'kode_template' => $kodeTemplate,
            'judul' => Str::limit(trim($judul), 255, ''),
            'status' => self::STATUS_DRAF,
            'data' => $data,
            'blok' => $blok,
            'dibuat_pada' => $sekarang,
            'diperbarui_pada' => $sekarang,
            'versi' => 1,
            'riwayat' => [
                ['versi' => 1, 'pada' => $sekarang, 'status' => self::STATUS_DRAF, 'aksi' => 'Dibuat'],
            ],
        ];

        $semua = self::semua();
        $semua[] = $draf;
        self::tulis($semua);

        return $draf;
    }

    /**
     * Perbarui draf milik pemilik: ganti data/blok, naikkan versi, catat riwayat.
     *
     * @param  array<string, mixed>  $ubah  kunci yang diganti (data, blok, status, judul)
     * @return array<string, mixed>|null
     */
    public static function perbarui(string $username, string $id, array $ubah): ?array
    {
        $username = self::normalisasiPemilik($username);
        $semua = self::semua();
        $hasil = null;

        foreach ($semua as $i => $draf) {
            if (($draf['id'] ?? null) !== $id || self::normalisasiPemilik((string) ($draf['pemilik'] ?? '')) !== $username) {
                continue;
            }

            $versi = max(1, (int) ($draf['versi'] ?? 1)) + 1;
            $sekarang = self::sekarang();

            $baru = array_merge($draf, $ubah, [
                'versi' => $versi,
                'diperbarui_pada' => $sekarang,
            ]);

            $riwayat = is_array($draf['riwayat'] ?? null) ? $draf['riwayat'] : [];
            $riwayat[] = [
                'versi' => $versi,
                'pada' => $sekarang,
                'status' => (string) ($baru['status'] ?? self::STATUS_DRAF),
                'aksi' => (string) ($ubah['aksi'] ?? 'Disimpan'),
            ];
            unset($ubah['aksi']);
            $baru['riwayat'] = array_slice($riwayat, -self::MAKS_VERSI);

            $semua[$i] = $baru;
            $hasil = $baru;
            break;
        }

        if ($hasil !== null) {
            self::tulis($semua);
        }

        return $hasil;
    }

    /** Hapus draf milik pemilik. True bila ada yang terhapus. */
    public static function hapus(string $username, string $id): bool
    {
        $username = self::normalisasiPemilik($username);
        $semua = self::semua();
        $sisa = [];
        $terhapus = false;

        foreach ($semua as $draf) {
            if (($draf['id'] ?? null) === $id && self::normalisasiPemilik((string) ($draf['pemilik'] ?? '')) === $username) {
                $terhapus = true;

                continue;
            }

            $sisa[] = $draf;
        }

        if ($terhapus) {
            self::tulis($sisa);
        }

        return $terhapus;
    }

    /** Bersihkan satu nilai bagian yang disunting mitra (teks biasa). */
    public static function bersihkanBagian(mixed $nilai): string
    {
        $teks = is_string($nilai) ? $nilai : '';
        $teks = strip_tags($teks);
        $teks = str_replace(["\r\n", "\r"], "\n", $teks);
        $teks = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $teks) ?? '';

        return Str::limit(trim($teks), self::MAKS_BAGIAN, '');
    }

    private static function normalisasiPemilik(string $username): string
    {
        return Str::lower(trim($username));
    }

    private static function sekarang(): string
    {
        return CarbonImmutable::now(config('app.timezone', 'UTC'))->toDateTimeString();
    }

    /** @param array<int, array<string, mixed>> $data */
    private static function tulis(array $data): void
    {
        $dir = dirname(self::path());

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        file_put_contents(
            self::path(),
            json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX,
        );
    }
}
