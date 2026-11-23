<?php

namespace App\Support\Mou;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Renderer dokumen MoU dari struktur blok template.
 *
 * Keluaran = struktur terpisah (bukan HTML mentah) yang dirender Blade:
 * tiap nilai adalah teks BIASA, dan escaping dilakukan Blade ({{ }}), bukan
 * di sini. Placeholder tanpa nilai menjadi penanda `[ISI nama_field]` supaya
 * bagian yang belum lengkap mudah ditandai. Renderer TIDAK menghasilkan HTML,
 * sehingga tidak ada jalur innerHTML dari input pengguna.
 *
 * Bentuk keluaran:
 *   [
 *     'kode' => string, 'nama' => string, 'status' => string,
 *     'judul' => string,
 *     'blok' => list<blok-render>,
 *   ]
 * dengan blok-render salah satu dari:
 *   {tipe: 'sampul'|'pembukaan'|..., paragraf: list<string>}
 *   {tipe: 'pasal', nomor: int, judul: string, ayat: list<string>, catatan: string}
 *   {tipe: 'lampiran', judul: string, kolom: list<string>, baris: list<list<string>>}
 */
final class RendererDokumen
{
    /** Placeholder teks judul yang menandai draf belum tersedia. */
    public const LABEL_ISI = '[ISI %s]';

    /**
     * Data placeholder gabungan: config + data mitra + form lengkapi.
     *
     * @param  array<string, mixed>  $template
     * @param  array<string, string>  $form
     * @return array<string, string>
     */
    public static function data(array $template, array $form): array
    {
        $pengaturan = StrukturTemplateMou::pengaturan();
        $pihakKesatu = (array) ($pengaturan['pihak_kesatu'] ?? []);
        $dasarHukum = array_values(array_filter(
            (array) ($pengaturan['dasar_hukum_nasional'] ?? []),
            static fn ($b) => is_string($b) && trim($b) !== '',
        ));

        $data = [
            'pihak_kesatu_nama_unit' => (string) ($pihakKesatu['nama_unit'] ?? ''),
            'pihak_kesatu_alamat' => (string) ($pihakKesatu['alamat'] ?? ''),
            'pihak_kesatu_telepon' => (string) ($pihakKesatu['telepon'] ?? ''),
            'pihak_kesatu_email' => (string) ($pihakKesatu['email'] ?? ''),
            'pihak_kesatu_penandatangan' => (string) ($pihakKesatu['penandatangan'] ?? ''),
            'pihak_kesatu_jabatan' => (string) ($pihakKesatu['jabatan'] ?? ''),
            'pihak_kesatu_pejabat_pelaksana' => (string) ($pihakKesatu['pejabat_pelaksana'] ?? ''),
            'dasar_hukum' => implode("\n", $dasarHukum),
            'pembiayaan' => StrukturTemplateMou::klausulPembiayaan($form['pembiayaan'] ?? null),
        ];

        // Nilai dari form lengkapi mitra menang atas default config, TETAPI
        // jangan menimpa kunci yang dihitung renderer (mis. `pembiayaan` yang
        // sudah menjadi klausul utuh).
        foreach ($form as $kunci => $nilai) {
            if (in_array($kunci, ['pembiayaan'], true)) {
                continue;
            }

            $data[$kunci] = $nilai;
        }

        return $data;
    }

    /**
     * Nilai terbilang/ringkas yang diturunkan dari isian form.
     *
     * @param  array<string, string>  $form
     * @return array<string, string>
     */
    public static function turunan(array $form, ?string $kodeBidang = null): array
    {
        $tanggal = Penomoran::parse($form['tanggal_penandatanganan'] ?? null)
            ?? CarbonImmutable::now(config('app.timezone', 'UTC'));

        $tahunMulai = (int) $tanggal->format('Y');
        $masaAktif = (int) preg_replace('/\D+/', '', (string) ($form['masa_aktif_tahun'] ?? ''));

        return [
            'tanggal' => Penomoran::tanggalRingkas($tanggal),
            'tanggal_resmi' => Penomoran::tanggalResmi($tanggal),
            'tahun_mulai' => (string) $tahunMulai,
            'tahun_akhir' => $masaAktif > 0 ? (string) ($tahunMulai + $masaAktif) : '',
        ];
    }

    /**
     * Render satu template menjadi dokumen terstruktur.
     *
     * @param  array<string, mixed>  $template
     * @param  array<string, string>  $data  nilai placeholder (sudah digabung config+form+turunan)
     * @param  list<string>  $pasalNonaktif
     * @return array<string, mixed>
     */
    public static function render(array $template, array $data, array $pasalNonaktif = []): array
    {
        $blok = [];
        $pasalAktif = StrukturTemplateMou::pasalAktif($template, $pasalNonaktif);
        $judul = (string) ($template['nama'] ?? '');

        foreach ($template['blok'] ?? [] as $satu) {
            if (! is_array($satu)) {
                continue;
            }

            $tipe = (string) ($satu['tipe'] ?? '');

            if ($tipe === 'pasal') {
                continue; // ditangani setelah loop, memakai pasalAktif
            }

            if ($tipe === 'lampiran') {
                $blok[] = self::renderLampiran((array) ($satu['lampiran'] ?? []), $data);

                continue;
            }

            $isi = self::gantiPlaceholder((string) ($satu['isi'] ?? ''), $data);

            if ($tipe === 'sampul') {
                // Judul dokumen diambil dari baris TENTANG pada sampul.
                $judul = self::barisSetelahTentang($isi) ?: $judul;
            }

            $blok[] = [
                'tipe' => $tipe,
                'paragraf' => self::baris($isi),
            ];
        }

        // Sisipkan blok pasal pada posisi asalnya (setelah 'mengingat').
        $blok = self::sisipkanPasal($blok, $pasalAktif, $data);

        return [
            'kode' => (string) ($template['kode'] ?? ''),
            'nama' => (string) ($template['nama'] ?? ''),
            'status' => (string) ($template['status'] ?? ''),
            'judul' => $judul,
            'blok' => $blok,
        ];
    }

    /**
     * Ganti placeholder {{nama}} dengan nilai ter-escape-aman (teks biasa).
     * Placeholder tanpa nilai → `[ISI nama]`.
     *
     * @param  array<string, string>  $data
     */
    public static function gantiPlaceholder(string $teks, array $data): string
    {
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/u',
            static function (array $cocok) use ($data): string {
                $nama = $cocok[1];
                $nilai = $data[$nama] ?? '';

                if (trim($nilai) === '') {
                    return sprintf(self::LABEL_ISI, $nama);
                }

                return trim($nilai);
            },
            $teks,
        ) ?? $teks;
    }

    /**
     * @param  array<string, mixed>  $lampiran
     * @param  array<string, string>  $data
     * @return array<string, mixed>
     */
    private static function renderLampiran(array $lampiran, array $data): array
    {
        $kolom = array_map(
            static fn ($k) => self::gantiPlaceholder((string) $k, $data),
            (array) ($lampiran['kolom'] ?? []),
        );

        $baris = [];
        foreach ((array) ($lampiran['baris'] ?? []) as $barisSatu) {
            if (! is_array($barisSatu)) {
                continue;
            }

            $baris[] = array_map(
                static fn ($sel) => self::gantiPlaceholder((string) $sel, $data),
                $barisSatu,
            );
        }

        return [
            'tipe' => 'lampiran',
            'judul' => self::gantiPlaceholder((string) ($lampiran['judul'] ?? ''), $data),
            'kolom' => $kolom,
            'baris' => $baris,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $blok
     * @param  list<array<string, mixed>>  $pasalAktif
     * @param  array<string, string>  $data
     * @return list<array<string, mixed>>
     */
    private static function sisipkanPasal(array $blok, array $pasalAktif, array $data): array
    {
        $pasalBlok = [];
        foreach ($pasalAktif as $pasal) {
            $ayat = array_map(
                static fn ($a) => self::gantiPlaceholder((string) $a, $data),
                (array) ($pasal['ayat'] ?? []),
            );
            $catatan = (string) ($pasal['catatan'] ?? '');

            $pasalBlok[] = [
                'tipe' => 'pasal',
                'nomor' => (int) ($pasal['nomor'] ?? 0),
                'judul' => (string) ($pasal['judul'] ?? ''),
                'ayat' => $ayat,
                'catatan' => self::gantiPlaceholder($catatan, $data),
            ];
        }

        if ($pasalBlok === []) {
            return $blok;
        }

        // Sisipkan setelah blok terakhir bertipe 'mengingat' (atau 'menimbang').
        $posisi = null;
        foreach ($blok as $i => $satu) {
            if (in_array($satu['tipe'] ?? '', ['mengingat', 'menimbang'], true)) {
                $posisi = $i;
            }
        }

        if ($posisi === null) {
            return array_merge($blok, $pasalBlok);
        }

        return array_merge(
            array_slice($blok, 0, $posisi + 1),
            $pasalBlok,
            array_slice($blok, $posisi + 1),
        );
    }

    /**
     * Ambil baris setelah baris persis "TENTANG" pada teks sampul (judul
     * dokumen), bila ada; jika tidak, kembalikan string kosong.
     */
    private static function barisSetelahTentang(string $sampul): string
    {
        $baris = self::baris($sampul);

        foreach ($baris as $i => $satu) {
            if (Str::upper(trim($satu)) === 'TENTANG') {
                return trim($baris[$i + 1] ?? '');
            }
        }

        return '';
    }

    /**
     * Pecah teks menjadi daftar baris (baris kosong dipertahankan sebagai '').
     *
     * @return list<string>
     */
    private static function baris(string $teks): array
    {
        return array_map('strval', preg_split('/\r\n|\r|\n/', $teks) ?: []);
    }
}
