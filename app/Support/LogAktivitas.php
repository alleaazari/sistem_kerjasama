<?php

namespace App\Support;

/**
 * Catatan aktivitas aksi sensitif panel admin (mode demo).
 *
 * Satu sumber: storage/app/log_aktivitas.json — pola sama dengan
 * storage_pengajuan.json. Ditulis dengan LOCK_EX agar dua permintaan
 * bersamaan tidak saling menimpa berkas.
 *
 * Yang dicatat: waktu, peran, nama akun, aksi, target (UUID/ID), IP.
 * Tidak pernah mencatat kata sandi, hash, atau isi surat.
 *
 * TODO-BACKEND: pindah ke tabel `log_aktivitas` (kolom: id, user_id, peran,
 * aksi, target, ip, dibuat_pada) dengan indeks pada (peran, aksi, dibuat_pada).
 */
final class LogAktivitas
{
    /** Jumlah baris per halaman di halaman Aktivitas. */
    public const PER_HALAMAN = 25;

    public static function path(): string
    {
        return storage_path('app/log_aktivitas.json');
    }

    /**
     * Catat satu aksi sensitif untuk peran yang sedang login.
     *
     * @param  string  $aksi  kunci izin (mis. `inbox.approve`)
     * @param  string  $target  UUID/ID objek yang dikenai aksi
     */
    public static function catat(string $aksi, string $target): void
    {
        $baris = [
            'waktu' => now(config('app.timezone', 'UTC'))->toDateTimeString(),
            'peran' => AdminAkses::peranSesi() ?? 'tidak dikenal',
            'nama' => AdminAkses::namaSesi(),
            'aksi' => $aksi,
            'target' => $target,
            'ip' => (string) request()->ip(),
        ];

        $rows = self::all();
        array_unshift($rows, $baris);
        self::simpan($rows);
    }

    /**
     * Seluruh catatan, terbaru lebih dulu.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        if (! is_file(self::path())) {
            return [];
        }

        $data = json_decode((string) file_get_contents(self::path()), true);

        return is_array($data) ? array_values(array_filter($data, 'is_array')) : [];
    }

    /**
     * Saring catatan. Filter kosong berarti tidak menyaring apa pun.
     *
     * @param  array{peran?: string, aksi?: string, dari?: string, sampai?: string, q?: string}  $filter
     * @return array<int, array<string, mixed>>
     */
    public static function saring(array $filter): array
    {
        $peran = trim((string) ($filter['peran'] ?? ''));
        $aksi = trim((string) ($filter['aksi'] ?? ''));
        $dari = trim((string) ($filter['dari'] ?? ''));
        $sampai = trim((string) ($filter['sampai'] ?? ''));
        $kata = mb_strtolower(trim((string) ($filter['q'] ?? '')));

        return array_values(array_filter(self::all(), function (array $row) use ($peran, $aksi, $dari, $sampai, $kata): bool {
            if ($peran !== '' && (string) ($row['peran'] ?? '') !== $peran) {
                return false;
            }

            if ($aksi !== '' && (string) ($row['aksi'] ?? '') !== $aksi) {
                return false;
            }

            // Tanggal dibandingkan sebagai string ISO (YYYY-MM-DD), aman karena
            // format waktu selalu toDateTimeString().
            $tanggal = substr((string) ($row['waktu'] ?? ''), 0, 10);

            if ($dari !== '' && $tanggal !== '' && $tanggal < $dari) {
                return false;
            }

            if ($sampai !== '' && $tanggal !== '' && $tanggal > $sampai) {
                return false;
            }

            if ($kata !== '') {
                $bahan = mb_strtolower(implode(' ', [
                    (string) ($row['nama'] ?? ''),
                    (string) ($row['peran'] ?? ''),
                    (string) ($row['aksi'] ?? ''),
                    (string) ($row['target'] ?? ''),
                    (string) ($row['ip'] ?? ''),
                ]));

                if (! str_contains($bahan, $kata)) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * Satu halaman hasil saring.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{data: array<int, array<string, mixed>>, total: int, halaman: int, halaman_akhir: int}
     */
    public static function halaman(array $rows, int $halaman, int $perHalaman = self::PER_HALAMAN): array
    {
        $total = count($rows);
        $halamanAkhir = max(1, (int) ceil($total / $perHalaman));
        $halaman = min(max(1, $halaman), $halamanAkhir);

        return [
            'data' => array_slice($rows, ($halaman - 1) * $perHalaman, $perHalaman),
            'total' => $total,
            'halaman' => $halaman,
            'halaman_akhir' => $halamanAkhir,
        ];
    }

    /** Label aksi untuk ditampilkan; diambil dari peta izin (satu sumber). */
    public static function labelAksi(string $aksi): string
    {
        return AdminAkses::labelAksi($aksi);
    }

    /** @return list<string> Kunci aksi yang bisa difilter (dari peta izin). */
    public static function daftarAksi(): array
    {
        return array_keys(AdminAkses::semuaAksi());
    }

    /** @param array<int, array<string, mixed>> $rows */
    private static function simpan(array $rows): void
    {
        $dir = dirname(self::path());
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        file_put_contents(
            self::path(),
            json_encode(array_values($rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}
