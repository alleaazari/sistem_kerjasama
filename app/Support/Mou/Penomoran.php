<?php

namespace App\Support\Mou;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Penomoran & terbilang Bahasa Indonesia untuk dokumen MoU.
 *
 * Dipakai renderer untuk mengubah tanggal menjadi bentuk resmi dokumen
 * ("Pada hari ini Senin, tanggal dua puluh satu, bulan September, tahun dua
 * ribu dua puluh enam") tanpa library tambahan.
 */
final class Penomoran
{
    /** @var array<int, string> */
    private const HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /** @var array<int, string> */
    private const BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /** @var array<int, string> */
    private const SATUAN = [
        0 => 'nol', 1 => 'satu', 2 => 'dua', 3 => 'tiga', 4 => 'empat',
        5 => 'lima', 6 => 'enam', 7 => 'tujuh', 8 => 'delapan', 9 => 'sembilan',
        10 => 'sepuluh', 11 => 'sebelas',
    ];

    /**
     * Terbilang bilangan bulat positif Bahasa Indonesia.
     * 21 -> "dua puluh satu", 2026 -> "dua ribu dua puluh enam".
     */
    public static function terbilang(int $angka): string
    {
        $angka = abs($angka);

        if ($angka < 12) {
            return self::SATUAN[$angka];
        }

        if ($angka < 20) {
            return self::terbilang($angka - 10).' belas';
        }

        if ($angka < 100) {
            return self::terbilang(intdiv($angka, 10)).' puluh'.self::sisa($angka % 10);
        }

        if ($angka < 200) {
            return 'seratus'.self::sisa($angka % 100);
        }

        if ($angka < 1000) {
            return self::terbilang(intdiv($angka, 100)).' ratus'.self::sisa($angka % 100);
        }

        if ($angka < 2000) {
            return 'seribu'.self::sisa($angka % 1000);
        }

        if ($angka < 1000000) {
            return self::terbilang(intdiv($angka, 1000)).' ribu'.self::sisa($angka % 1000);
        }

        if ($angka < 1000000000) {
            return self::terbilang(intdiv($angka, 1000000)).' juta'.self::sisa($angka % 1000000);
        }

        if ($angka < 1000000000000) {
            return self::terbilang(intdiv($angka, 1000000000)).' miliar'.self::sisa($angka % 1000000000);
        }

        return self::terbilang(intdiv($angka, 1000000000000)).' triliun'.self::sisa($angka % 1000000000000);
    }

    /** Nama hari Bahasa Indonesia ("Senin"). */
    public static function hari(DateTimeInterface $tanggal): string
    {
        return self::HARI[(int) $tanggal->format('N')];
    }

    /** Nama bulan Bahasa Indonesia ("September"). */
    public static function bulan(int $bulan): string
    {
        return self::BULAN[$bulan] ?? '';
    }

    /**
     * Tanggal resmi dokumen: "Senin, tanggal dua puluh satu, bulan September,
     * tahun dua ribu dua puluh enam".
     */
    public static function tanggalResmi(DateTimeInterface $tanggal): string
    {
        return sprintf(
            '%s, tanggal %s, bulan %s, tahun %s',
            self::hari($tanggal),
            self::terbilang((int) $tanggal->format('j')),
            self::bulan((int) $tanggal->format('n')),
            self::terbilang((int) $tanggal->format('Y')),
        );
    }

    /** Tanggal ringkas: "21 September 2026". */
    public static function tanggalRingkas(DateTimeInterface $tanggal): string
    {
        return sprintf(
            '%d %s %d',
            (int) $tanggal->format('j'),
            self::bulan((int) $tanggal->format('n')),
            (int) $tanggal->format('Y'),
        );
    }

    /**
     * Prefiks wilayah dari nama kota/provinsi: "Kota" untuk kota, "Kabupaten"
     * untuk kabupaten, selain itu kosong.
     */
    public static function prefiksWilayah(string $wilayah): string
    {
        $wilayah = trim($wilayah);
        $rendah = mb_strtolower($wilayah);

        if (str_starts_with($rendah, 'kota')) {
            return 'Kota';
        }

        if (str_starts_with($rendah, 'kabupaten') || str_starts_with($rendah, 'kab.')) {
            return 'Kabupaten';
        }

        return '';
    }

    /** Nama wilayah tanpa prefiks ("Kota Medan" -> "Medan"). */
    public static function namaWilayah(string $wilayah): string
    {
        $wilayah = trim($wilayah);

        return trim((string) preg_replace('/^(kota|kabupaten|kab\.)\s+/i', '', $wilayah));
    }

    private static function sisa(int $angka): string
    {
        return $angka > 0 ? ' '.self::terbilang($angka) : '';
    }

    /** Titik masuk aman dari string tanggal ISO; null bila tak sah. */
    public static function parse(?string $tanggal): ?CarbonImmutable
    {
        if (! is_string($tanggal) || trim($tanggal) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($tanggal, config('app.timezone', 'UTC'));
        } catch (\Throwable) {
            return null;
        }
    }
}
