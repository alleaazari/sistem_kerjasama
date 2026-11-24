<?php

namespace App\Support;

/**
 * Sumber data dropdown "Unit Kerja / Divisi / Fakultas".
 *
 * Dua kelompok fallback statis:
 *   - perguruanTinggi(): unit akademik standar sebuah kampus.
 *   - pemerintah(): unit kerja pemerintah daerah / kementerian.
 *
 * Data statis dipakai karena API PDDikti publik tidak menyediakan daftar
 * fakultas/prodi per perguruan tinggi secara andal (endpoint hit_mhs_token
 * hanya pencarian teks). Endpoint /api/pddikti/unit-kerja mencoba lookup
 * PDDikti lebih dulu, lalu jatuh ke daftar di kelas ini.
 *
 * TODO-BACKEND: bila tersedia tabel/endpoint resmi prodi per kode PT, ganti
 * sumber perguruanTinggi() dengan kueri; kontrak method dipertahankan.
 */
final class UnitKerja
{
    /** Nilai khusus: user memilih mengetik sendiri. */
    public const ISIAN_SENDIRI = '__custom__';

    /** Label opsi "isi sendiri" untuk dropdown. */
    public const LABEL_ISIAN_SENDIRI = 'Isi sendiri';

    /**
     * Unit akademik standar perguruan tinggi (fallback). Urutan:
     * pimpinan/kerjasama dulu, lalu fakultas umum.
     *
     * @return list<string>
     */
    public static function perguruanTinggi(): array
    {
        return [
            'Rektorat / Pimpinan Institusi',
            'Biro Kerja Sama / Humas',
            'Lembaga Penelitian dan Pengabdian kepada Masyarakat (LPPM)',
            'Lembaga Pengembangan Pembelajaran dan Penjaminan Mutu',
            'Direktorat Akademik / Administrasi Akademik',
            'Fakultas Teknik',
            'Fakultas Ilmu Komputer / Teknologi Informasi',
            'Fakultas Ekonomi dan Bisnis',
            'Fakultas Ilmu Sosial dan Ilmu Politik',
            'Fakultas Keguruan dan Ilmu Pendidikan',
            'Fakultas Sains dan Matematika',
            'Fakultas Hukum',
            'Fakultas Kedokteran',
            'Sekolah Pascasarjana',
        ];
    }

    /**
     * Unit kerja pemerintahan umum (fallback) untuk mitra non-kampus.
     *
     * @return list<string>
     */
    public static function pemerintah(): array
    {
        return [
            'Dinas Komunikasi dan Informatika',
            'Bagian Kerja Sama',
            'Badan Kepegawaian dan Pengembangan SDM',
            'Sekretariat Daerah',
            'Badan Perencanaan Pembangunan Daerah (BAPPEDA)',
            'Dinas Pendidikan',
            'Dinas Pemberdayaan Masyarakat dan Desa',
            'Inspektorat',
        ];
    }

    /**
     * Daftar unit kerja cadangan sesuai jenis instansi.
     *
     * @return list<string>
     */
    public static function fallback(string $jenisInstansi): array
    {
        return match ($jenisInstansi) {
            'pemerintah_daerah', 'kementerian' => self::pemerintah(),
            default => self::perguruanTinggi(),
        };
    }
}
