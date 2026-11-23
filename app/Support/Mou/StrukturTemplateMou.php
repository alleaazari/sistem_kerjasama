<?php

namespace App\Support\Mou;

use RuntimeException;

/**
 * Pemuatan dan validasi struktur template MoU (blok berurutan).
 *
 * Struktur satu template (berkas JSON di storage/app/mou/):
 *   kode    : string  — pencocokan dengan config('mou.pemetaan')
 *   nama    : string  — label tampilan
 *   status  : 'final' | 'draf'
 *   versi   : int
 *   blok    : list<blok> berurutan, tiap blok salah satu bentuk:
 *       { tipe: sampul|pembukaan|menimbang|mengingat|penutup|tanda_tangan, isi: string }
 *       { tipe: pasal, pasal: list<{ nomor, judul, ayat[], opsional: bool, aktif_bawaan: bool }> }
 *       { tipe: lampiran, lampiran: { judul, tabel, kolom[], baris[][] } }
 *
 * Semua teks blok memuat placeholder {{nama}} sesuai PendaftaranPlaceholder.
 *
 * TODO-BACKEND: pindah ke tabel `mou_template` (kode unik, nama, status,
 * versi) + tabel blok terurut dan riwayat versi. Kontrak method dipertahankan.
 */
final class StrukturTemplateMou
{
    /** Urutan tipe blok yang sah pada satu template. */
    public const TIPE_BLOK = ['sampul', 'pembukaan', 'menimbang', 'mengingat', 'pasal', 'penutup', 'tanda_tangan', 'lampiran'];

    /** Status template yang sah. */
    public const STATUS = ['final', 'draf'];

    public static function direktori(): string
    {
        return storage_path('app/mou');
    }

    public static function path(string $kode): string
    {
        return self::direktori().'/'.$kode.'.json';
    }

    /**
     * Seluruh template, dikunci dengan kode template.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function semua(): array
    {
        $hasil = [];
        $berkas = glob(self::direktori().'/*.json') ?: [];

        foreach ($berkas as $file) {
            $data = json_decode((string) file_get_contents($file), true);

            if (! is_array($data)) {
                continue;
            }

            $kode = $data['kode'] ?? null;

            if (is_string($kode) && $kode !== '') {

                $hasil[$kode] = $data;
            }
        }

        return $hasil;
    }

    /** @return array<string, mixed>|null */
    public static function untuk(string $kode): ?array
    {
        return self::semua()[$kode] ?? null;
    }

    /**
     * Template untuk sebuah kode bidang, memakai config('mou.pemetaan').
     * Bidang yang tidak dipetakan memakai 'kerangka_umum'; bila itu pun tidak
     * ada, kembalikan null.
     *
     * @return array<string, mixed>|null
     */
    public static function untukBidang(string $kodeBidang): ?array
    {
        $peta = config('mou.pemetaan', []);
        $kodeTemplate = is_array($peta) && isset($peta[$kodeBidang]) && is_string($peta[$kodeBidang])
            ? $peta[$kodeBidang]
            : 'kerangka_umum';

        return self::untuk($kodeTemplate);
    }

    /**
     * Seluruh placeholder yang dipakai struktur template (rekursif).
     *
     * @param  array<string, mixed>  $template
     * @return list<string>
     */
    public static function placeholderTerpakai(array $template): array
    {
        $teks = [];
        self::kumpulkanTeks($template, $teks);

        $nama = [];
        foreach ($teks as $satu) {
            preg_match_all('/\{\{\s*([^{}]*?)\s*\}\}/u', $satu, $cocok);
            foreach ($cocok[1] as $n) {
                $nama[$n] = true;
            }
        }

        return array_keys($nama);
    }

    /**
     * Placeholder yatim: dipakai template tetapi tidak terdaftar di
     * PendaftaranPlaceholder.
     *
     * @param  array<string, mixed>  $template
     * @return list<string>
     */
    public static function placeholderYatim(array $template): array
    {
        $asing = array_filter(
            self::placeholderTerpakai($template),
            static fn (string $nama): bool => ! PendaftaranPlaceholder::terdaftar($nama),
        );

        return array_values($asing);
    }

    /**
     * Semua placeholder yatim di seluruh template, dikunci kode template.
     *
     * @return array<string, list<string>>
     */
    public static function semuaPlaceholderYatim(): array
    {
        $hasil = [];

        foreach (self::semua() as $kode => $template) {
            $yatim = self::placeholderYatim($template);

            if ($yatim !== []) {
                $hasil[$kode] = $yatim;
            }
        }

        return $hasil;
    }

    /**
     * Periksa bentuk struktur template. Mengembalikan daftar pesan masalah;
     * daftar kosong berarti struktur sah.
     *
     * @param  array<string, mixed>  $template
     * @return list<string>
     */
    public static function periksa(array $template): array
    {
        $masalah = [];

        foreach (['kode', 'nama', 'status', 'versi', 'blok'] as $wajib) {
            if (! array_key_exists($wajib, $template)) {
                $masalah[] = "Field '{$wajib}' wajib ada.";
            }
        }

        if (isset($template['status']) && ! in_array($template['status'], self::STATUS, true)) {
            $masalah[] = 'Status harus salah satu dari: '.implode(', ', self::STATUS).'.';
        }

        if (! isset($template['blok']) || ! is_array($template['blok'])) {
            $masalah[] = "Field 'blok' harus berupa larik.";
        } else {
            foreach ($template['blok'] as $i => $blok) {
                if (! is_array($blok) || ! isset($blok['tipe'])) {
                    $masalah[] = "Blok ke-{$i} tidak memiliki 'tipe'.";

                    continue;
                }

                if (! in_array($blok['tipe'], self::TIPE_BLOK, true)) {
                    $masalah[] = "Blok ke-{$i} memakai tipe tak dikenal: {$blok['tipe']}.";
                }
            }
        }

        return $masalah;
    }

    /**
     * Muat template dan pastikan strukturnya sah; lempar bila tidak.
     *
     * @return array<string, mixed>
     */
    public static function muat(string $kode): array
    {
        $template = self::untuk($kode);

        if ($template === null) {
            throw new RuntimeException("Template MoU '{$kode}' tidak ditemukan.");
        }

        $masalah = self::periksa($template);

        if ($masalah !== []) {
            throw new RuntimeException("Struktur template '{$kode}' tidak sah: ".implode(' ', $masalah));
        }

        return $template;
    }

    /**
     * Kumpulkan semua string di dalam struktur (rekursif) untuk pemindaian
     * placeholder.
     *
     * @param  array<mixed>  $nilai
     * @param  list<string>  $keluar
     */
    private static function kumpulkanTeks(array $nilai, array &$keluar): void
    {
        foreach ($nilai as $item) {
            if (is_array($item)) {
                self::kumpulkanTeks($item, $keluar);
            } elseif (is_string($item)) {
                $keluar[] = $item;
            }
        }
    }
}
