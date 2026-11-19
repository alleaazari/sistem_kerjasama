<?php

namespace App\Support;

/**
 * Daftar tetap bidang kerja sama. Satu sumber untuk form pengajuan, halaman
 * mitra, generator draf MoU, dan validasi server.
 *
 * Kode (mis. VSGA) yang disimpan di data; label hanya untuk tampilan.
 */
final class BidangKerjasama
{
    /** @return array<string, string> kode => label */
    public static function daftar(): array
    {
        $daftar = config('kerjasama.bidang', []);

        return is_array($daftar) ? $daftar : [];
    }

    /** @return array<int, string> */
    public static function kode(): array
    {
        return array_keys(self::daftar());
    }

    /** Label untuk kode tersimpan; string kosong bila kode tidak dikenal. */
    public static function label(mixed $kode): string
    {
        return self::daftar()[(string) $kode] ?? '';
    }
}
