<?php

namespace App\Support;

/**
 * Daftar tetap bidang kerja sama. Satu sumber untuk form pengajuan, halaman
 * mitra, generator draf MoU, dan validasi server.
 *
 * Tiap item config('kerjasama.bidang') berisi: kode, label, deskripsi_singkat,
 * jenis_dokumen, template, aktif. Hanya bidang dengan aktif=true yang tampil
 * di form dan diterima server; bidang nonaktif disimpan agar pemetaan template
 * (mis. slot bidang ketiga) tetap dapat ditinjau tanpa tampil ke pengguna.
 */
final class BidangKerjasama
{
    /**
     * Seluruh bidang (termasuk nonaktif) sebagai map kode => item ternormalisasi.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function daftar(): array
    {
        $mentah = config('kerjasama.bidang', []);
        $mentah = is_array($mentah) ? $mentah : [];

        $hasil = [];

        foreach ($mentah as $kunci => $item) {
            $item = is_array($item) ? $item : ['label' => $item];
            $kode = (string) ($item['kode'] ?? $kunci);

            if ($kode === '') {
                continue;
            }

            $hasil[$kode] = [
                'kode' => $kode,
                'label' => (string) ($item['label'] ?? ''),
                'deskripsi_singkat' => (string) ($item['deskripsi_singkat'] ?? ''),
                'sasaran_mitra' => (string) ($item['sasaran_mitra'] ?? ''),
                'jenis_dokumen' => (string) ($item['jenis_dokumen'] ?? ''),
                'template' => (string) ($item['template'] ?? ''),
                'aktif' => (bool) ($item['aktif'] ?? false),
            ];
        }

        return $hasil;
    }

    /**
     * Bidang yang aktif saja, siap dipakai form & validasi.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function aktif(): array
    {
        return array_filter(self::daftar(), static fn (array $item): bool => $item['aktif'] === true);
    }

    /** @return array<int, string> kode bidang aktif */
    public static function kode(): array
    {
        return array_keys(self::aktif());
    }

    /**
     * Item lengkap satu bidang; null bila kode tidak dikenal.
     *
     * @return array<string, mixed>|null
     */
    public static function item(mixed $kode): ?array
    {
        if (! is_string($kode) && ! is_int($kode)) {
            return null;
        }

        return self::daftar()[(string) $kode] ?? null;
    }

    /** Label untuk kode tersimpan; string kosong bila kode tidak dikenal. */
    public static function label(mixed $kode): string
    {
        return (string) (self::item($kode)['label'] ?? '');
    }

    /** Nama jenis dokumen untuk kode tersimpan; string kosong bila tak dikenal. */
    public static function jenisDokumen(mixed $kode): string
    {
        return (string) (self::item($kode)['jenis_dokumen'] ?? '');
    }

    /** Kode template MoU untuk kode bidang; string kosong bila tak dikenal. */
    public static function template(mixed $kode): string
    {
        return (string) (self::item($kode)['template'] ?? '');
    }

    /**
     * Pemetaan bidang -> kode template untuk generator MoU, diturunkan dari
     * config('kerjasama.bidang') supaya tidak ada duplikasi dengan config/mou.php.
     *
     * @return array<string, string>
     */
    public static function pemetaanTemplate(): array
    {
        $hasil = [];

        foreach (self::daftar() as $kode => $item) {
            if ($item['template'] !== '') {
                $hasil[$kode] = $item['template'];
            }
        }

        return $hasil;
    }
}
