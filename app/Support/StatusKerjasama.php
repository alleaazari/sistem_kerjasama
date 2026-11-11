<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * Perhitungan status masa aktif kerja sama.
 *
 * Fungsi murni: tidak menyentuh database, session, maupun request.
 * Semua perhitungan dilakukan per TANGGAL (bukan jam) memakai zona waktu
 * aplikasi, sehingga hasilnya stabil sepanjang hari.
 */
final class StatusKerjasama
{
    public const AKTIF = 'aktif';

    public const SEGERA = 'segera';

    public const BERAKHIR = 'berakhir';

    /** Label kondisi untuk ditampilkan apa adanya (bukan hanya warna). */
    public const LABEL = [
        self::AKTIF => 'Aktif',
        self::SEGERA => 'Segera Berakhir',
        self::BERAKHIR => 'Berakhir',
    ];

    public static function ambangHari(): int
    {
        return (int) config('mitra.ambang_segera_berakhir_hari', 90);
    }

    /**
     * Membaca tanggal bebas dari string tampilan lama (mis. "12 Okt 2026")
     * maupun ISO ("2026-10-12"). Mengembalikan null bila tidak terbaca.
     */
    public static function parseTanggal(?string $nilai): ?CarbonImmutable
    {
        if ($nilai === null || trim($nilai) === '') {
            return null;
        }

        $nilai = trim($nilai);
        $zona = config('app.timezone', 'UTC');

        foreach (['Y-m-d', 'd M Y', 'd F Y', 'd/m/Y'] as $format) {
            try {
                $hasil = CarbonImmutable::createFromFormat($format, $nilai, $zona);
            } catch (\Throwable) {
                $hasil = null;
            }

            if ($hasil instanceof CarbonImmutable) {
                return $hasil->startOfDay();
            }
        }

        try {
            return CarbonImmutable::parse($nilai, $zona)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Ringkasan masa aktif.
     *
     * @return array{
     *     kondisi: string,
     *     label: string,
     *     mulai: ?CarbonImmutable,
     *     selesai: ?CarbonImmutable,
     *     sisa_hari: int,
     *     total_hari: int,
     *     persen: int,
     *     keterangan: string
     * }
     */
    public static function hitung(?string $mulai, ?string $selesai, ?CarbonImmutable $hariIni = null): array
    {
        $zona = config('app.timezone', 'UTC');
        $hariIni = ($hariIni ?? CarbonImmutable::now($zona))->setTimezone($zona)->startOfDay();
        $awal = self::parseTanggal($mulai);
        $akhir = self::parseTanggal($selesai);

        // Data tanggal tidak lengkap: jangan menebak kondisi.
        if (! $awal || ! $akhir) {
            return [
                'kondisi' => self::AKTIF,
                'label' => 'Belum tersedia',
                'mulai' => $awal,
                'selesai' => $akhir,
                'sisa_hari' => 0,
                'total_hari' => 0,
                'persen' => 0,
                'keterangan' => 'Tanggal periode kerja sama belum tersedia.',
            ];
        }

        $totalHari = max(1, (int) $awal->diffInDays($akhir, false));
        $sisaHari = (int) $hariIni->diffInDays($akhir, false);
        $ambang = self::ambangHari();

        if ($sisaHari < 0) {
            $kondisi = self::BERAKHIR;
            $keterangan = 'Berakhir '.abs($sisaHari).' hari yang lalu';
        } elseif ($sisaHari <= $ambang) {
            $kondisi = self::SEGERA;
            $keterangan = $sisaHari === 0
                ? 'Berakhir hari ini'
                : 'Berakhir dalam '.$sisaHari.' hari';
        } else {
            $kondisi = self::AKTIF;
            $keterangan = $sisaHari.' hari tersisa';
        }

        $terpakai = max(0, (int) $awal->diffInDays($hariIni, false));
        $persen = (int) round(min(100, max(0, ($terpakai / $totalHari) * 100)));

        return [
            'kondisi' => $kondisi,
            'label' => self::LABEL[$kondisi],
            'mulai' => $awal,
            'selesai' => $akhir,
            'sisa_hari' => $sisaHari,
            'total_hari' => $totalHari,
            'persen' => $persen,
            'keterangan' => $keterangan,
        ];
    }
}
