<?php

namespace App\Support\Mou;

/**
 * Daftar placeholder terpusat untuk seluruh template MoU.
 *
 * Satu sumber dipakai bersama oleh tiga konsumen:
 *   1. renderer  — mengganti {{nama}} dengan nilai saat draf dibuat;
 *   2. form "lengkapi data mitra" — menentukan field mana yang perlu diisi;
 *   3. editor template (Superadmin) — memvalidasi agar tidak ada placeholder
 *      yatim (dipakai template tetapi tidak terdaftar di sini).
 *
 * Sumber nilai (`sumber`):
 *   - form           : diisi mitra pada form pengajuan;
 *   - lengkapi_mitra : dilengkapi mitra pada profil instansinya;
 *   - config         : nilai tetap dari config/mou.php;
 *   - turunan        : dihitung sistem (tanggal, nomor dokumen, varian pembiayaan).
 *
 * TODO-BACKEND: saat tabel mitra/pengajuan ada, `sumber` dapat menunjuk kolom
 * database secara langsung; kontrak daftar ini tidak berubah.
 */
final class PendaftaranPlaceholder
{
    /**
     * Nama placeholder => metadata.
     *
     * @return array<string, array{sumber: string, keterangan: string, wajib: bool}>
     */
    public static function daftar(): array
    {
        return [
            'nama_instansi' => ['sumber' => 'form', 'keterangan' => 'Nama instansi mitra', 'wajib' => true],
            'nama_pic' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Nama penanggung jawab instansi mitra', 'wajib' => true],
            'jabatan_pic' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Jabatan penanggung jawab instansi mitra', 'wajib' => true],
            'alamat_instansi' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Alamat instansi mitra', 'wajib' => true],
            'telepon_instansi' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Telepon instansi mitra', 'wajib' => false],
            'email_instansi' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Surel instansi mitra', 'wajib' => false],
            'pihak_kedua_penandatangan' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Nama penandatangan pihak kedua', 'wajib' => true],
            'pihak_kedua_jabatan' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Jabatan penandatangan pihak kedua', 'wajib' => true],
            'pihak_kedua_dasar_pengangkatan' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Dasar pengangkatan penandatangan pihak kedua', 'wajib' => false],
            'pihak_kedua_alamat' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Alamat kedudukan pihak kedua', 'wajib' => false],
            'tanggal' => ['sumber' => 'turunan', 'keterangan' => 'Tanggal dokumen ditandatangani', 'wajib' => true],
            'tanggal_resmi' => ['sumber' => 'turunan', 'keterangan' => 'Tanggal dalam bentuk resmi ("Senin, tanggal ...")', 'wajib' => true],
            'tempat' => ['sumber' => 'turunan', 'keterangan' => 'Tempat penandatanganan', 'wajib' => false],
            'nomor_dokumen' => ['sumber' => 'turunan', 'keterangan' => 'Nomor dokumen Pihak Kesatu', 'wajib' => false],
            'nomor_dokumen_mitra' => ['sumber' => 'turunan', 'keterangan' => 'Nomor dokumen Pihak Kedua', 'wajib' => false],
            'nomor_surat' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Nomor surat permohonan mitra', 'wajib' => false],
            'bidang' => ['sumber' => 'turunan', 'keterangan' => 'Label bidang kerja sama', 'wajib' => true],
            'judul' => ['sumber' => 'turunan', 'keterangan' => 'Judul dokumen kerja sama', 'wajib' => true],
            'pihak_kesatu_nama_unit' => ['sumber' => 'config', 'keterangan' => 'Nama unit Pihak Kesatu', 'wajib' => true],
            'pihak_kesatu_alamat' => ['sumber' => 'config', 'keterangan' => 'Alamat Pihak Kesatu', 'wajib' => true],
            'pihak_kesatu_telepon' => ['sumber' => 'config', 'keterangan' => 'Telepon Pihak Kesatu', 'wajib' => false],
            'pihak_kesatu_email' => ['sumber' => 'config', 'keterangan' => 'Surel Pihak Kesatu', 'wajib' => false],
            'pihak_kesatu_penandatangan' => ['sumber' => 'config', 'keterangan' => 'Nama penandatangan Pihak Kesatu', 'wajib' => true],
            'pihak_kesatu_jabatan' => ['sumber' => 'config', 'keterangan' => 'Jabatan penandatangan Pihak Kesatu', 'wajib' => true],
            'pihak_kesatu_pejabat_pelaksana' => ['sumber' => 'config', 'keterangan' => 'Pejabat pelaksana Pihak Kesatu', 'wajib' => true],
            'dasar_hukum' => ['sumber' => 'config', 'keterangan' => 'Daftar dasar hukum nasional (Template A)', 'wajib' => true],
            'dasar_hukum_daerah' => ['sumber' => 'lengkapi_mitra', 'keterangan' => 'Daftar peraturan daerah mitra (Template A)', 'wajib' => false],
            'pembiayaan' => ['sumber' => 'turunan', 'keterangan' => 'Klausul pembiayaan sesuai varian terpilih', 'wajib' => true],
            'masa_aktif_tahun' => ['sumber' => 'turunan', 'keterangan' => 'Masa berlaku kerja sama (tahun)', 'wajib' => false],
            'tahun_mulai' => ['sumber' => 'turunan', 'keterangan' => 'Tahun mulai kerja sama', 'wajib' => true],
            'tahun_akhir' => ['sumber' => 'turunan', 'keterangan' => 'Tahun berakhir kerja sama', 'wajib' => true],
        ];
    }

    /** @return list<string> */
    public static function nama(): array
    {
        return array_keys(self::daftar());
    }

    public static function terdaftar(string $nama): bool
    {
        return array_key_exists($nama, self::daftar());
    }

    /**
     * Placeholder yang dipakai sebuah teks tetapi tidak terdaftar.
     * Bentuk longgar (`{{ token }}`, `{{token-rahasia}}`) ikut diperiksa agar
     * tidak ada token yang lolos validasi tetapi tidak pernah diganti.
     *
     * @return list<string>
     */
    public static function yatim(string $teks): array
    {
        preg_match_all('/\{\{\s*([^{}]*?)\s*\}\}/u', $teks, $cocok);

        $asing = array_filter(
            $cocok[1],
            static fn (string $nama): bool => ! self::terdaftar($nama),
        );

        return array_values(array_unique($asing));
    }
}
