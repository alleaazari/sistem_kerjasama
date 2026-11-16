<?php

namespace App\Support;

/**
 * Peta izin panel admin.
 *
 * Sumber tunggal: config/admin_peran.php. Dipakai oleh route (middleware),
 * layout (menu & label), dan test agar tidak ada daftar peran yang terduplikasi.
 */
final class AdminPeran
{
    /**
     * Peran yang boleh masuk panel admin sama sekali.
     *
     * @return list<string>
     */
    public static function semua(): array
    {
        /** @var array<string, list<string>> $peta */
        $peta = (array) config('admin_peran.peran', []);

        return array_keys($peta);
    }

    /**
     * Kemampuan yang dimiliki sebuah peran.
     *
     * @return list<string>
     */
    public static function kemampuan(string $peran): array
    {
        /** @var list<string> $daftar */
        $daftar = (array) config('admin_peran.peran.'.$peran, []);

        return $daftar;
    }

    public static function boleh(string $peran, string $kemampuan): bool
    {
        return in_array($kemampuan, self::kemampuan($peran), true);
    }

    /**
     * Daftar peran pemilik sebuah kemampuan, untuk argumen middleware
     * `admin.only:...` sehingga daftarnya tidak ditulis ulang di route.
     *
     * @return list<string>
     */
    public static function peranUntuk(string $kemampuan): array
    {
        return array_values(array_filter(
            self::semua(),
            fn (string $peran) => self::boleh($peran, $kemampuan)
        ));
    }

    /** Label peran untuk tampilan. */
    public static function label(string $peran): string
    {
        return (string) config('admin_peran.label.'.$peran, 'Administrator');
    }

    /** Nama route halaman pertama setelah login. */
    public static function beranda(string $peran): string
    {
        return (string) config('admin_peran.beranda.'.$peran, 'admin.inbox');
    }
}
