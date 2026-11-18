<?php

namespace App\Support;

/**
 * Daftar provinsi -> kabupaten/kota Indonesia.
 *
 * Sumber data: resources/data/wilayah.json, disalin sekali dari
 * https://wilayah.id/api (provinces.json + regencies/{kode}.json, basis data
 * Kemendagri). Tidak ada panggilan jaringan saat runtime: berkas dibaca dari
 * disk supaya aman terhadap CSP dan tetap jalan tanpa internet.
 *
 * TODO-BACKEND: bila tabel wilayah resmi tersedia, cukup ganti sumber peta()
 * dengan kueri; kontrak method dipertahankan.
 */
final class Wilayah
{
    /** @var array<string, list<string>>|null */
    private static ?array $peta = null;

    /**
     * Seluruh peta provinsi -> daftar kabupaten/kota.
     *
     * @return array<string, list<string>>
     */
    public static function peta(): array
    {
        if (self::$peta !== null) {
            return self::$peta;
        }

        $path = resource_path('data/wilayah.json');
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        if (! is_array($data)) {
            return self::$peta = [];
        }

        $hasil = [];

        foreach ($data as $provinsi => $daftar) {
            if (! is_string($provinsi) || $provinsi === '' || ! is_array($daftar)) {
                continue;
            }

            $kota = array_values(array_filter($daftar, 'is_string'));

            if ($kota !== []) {
                $hasil[$provinsi] = $kota;
            }
        }

        return self::$peta = $hasil;
    }

    /** @return list<string> */
    public static function provinsi(): array
    {
        return array_keys(self::peta());
    }

    /** @return list<string> */
    public static function kabupaten(string $provinsi): array
    {
        return self::peta()[$provinsi] ?? [];
    }

    /** Kota/kabupaten benar-benar berada di provinsi itu? */
    public static function valid(?string $provinsi, ?string $kota): bool
    {
        if (! is_string($provinsi) || ! is_string($kota)) {
            return false;
        }

        return in_array($kota, self::kabupaten($provinsi), true);
    }
}
