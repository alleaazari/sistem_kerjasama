<?php

namespace App\Support\Mou;

/**
 * Sorotan penanda `[ISI ...]` pada teks dokumen.
 *
 * SATU-SATUNYA tempat yang menghasilkan HTML pada pratinjau dokumen. Aturan:
 *  1. seluruh teks di-escape lebih dulu (htmlspecialchars), sehingga tag dari
 *     input pengguna TIDAK PERNAH lolos;
 *  2. hanya penanda `[ISI ...]` yang sudah ada di teks yang dibungkus <mark>
 *     dengan kelas tetap.
 *
 * Dengan begitu output `{!! !!}` di Blade hanya memuat HTML yang kita buat
 * sendiri; tidak ada jalur innerHTML dari input mentah.
 */
final class PenandaIsi
{
    /** Pola penanda isi yang belum dilengkapi. */
    private const POLA = '/\[ISI[^\]]*\]/u';

    public static function sorot(string $teks): string
    {
        $aman = htmlspecialchars($teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return preg_replace(
            self::POLA,
            '<mark class="mou-isi-kosong">$0</mark>',
            $aman,
        ) ?? $aman;
    }
}
