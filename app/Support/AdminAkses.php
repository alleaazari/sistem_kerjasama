<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/**
 * Peta akses panel admin (pembacaan terketik).
 *
 * Sumber tunggal: config/admin_akses.php. Dipakai oleh perutean (prefix +
 * middleware), layout (menu + identitas), controller (nama route), dan test.
 *
 * TODO-BACKEND: peran masih dibaca dari config + session, bukan tabel `users`.
 */
final class AdminAkses
{
    /** Kunci sesi tempat peran yang sedang login disimpan. */
    public const KUNCI_SESI = 'admin_auth';

    /** @return list<string> Peran operasional (punya prefix + login sendiri). */
    public static function semua(): array
    {
        return array_keys(self::peta('peran'));
    }

    /** @return list<string> Akun internal tanpa prefix sendiri (mis. developer). */
    public static function akunLain(): array
    {
        return array_keys(self::peta('akun_lain'));
    }

    /** @return list<string> Semua nama peran yang bisa muncul di sesi. */
    public static function semuaPeran(): array
    {
        return array_merge(self::semua(), self::akunLain());
    }

    /** Peran operasional (punya prefix sendiri)? */
    public static function ada(string $peran): bool
    {
        return in_array($peran, self::semua(), true);
    }

    public static function adaPeran(string $peran): bool
    {
        return in_array($peran, self::semuaPeran(), true);
    }

    /**
     * Definisi satu peran (label, path, kemampuan, akun).
     *
     * @return array<string, mixed>
     */
    public static function definisi(string $peran): array
    {
        return self::peta(self::kunci($peran))[$peran] ?? [];
    }

    /**
     * Kunci config tempat peran tinggal: peran operasional ada di `peran`,
     * akun internal di `akun_lain`.
     */
    private static function kunci(string $peran): string
    {
        return self::ada($peran) ? 'peran' : 'akun_lain';
    }

    /**
     * @return array<string, mixed>
     */
    private static function peta(string $kunci): array
    {
        /** @var array<string, mixed> $peta */
        $peta = config('admin_akses.'.$kunci, []);

        return $peta;
    }

    /**
     * Prefix URL sebuah peran (tanpa garis miring di ujung).
     * Akun internal memakai prefix peran induknya.
     */
    public static function path(string $peran): string
    {
        $def = self::definisi($peran);

        if (isset($def['path'])) {
            return trim((string) $def['path'], '/');
        }

        return self::path(self::induk($peran));
    }

    /** Awalan nama route sebuah peran, mis. `superadmin`. */
    public static function nama(string $peran): string
    {
        $def = self::definisi($peran);

        if (isset($def['nama'])) {
            return (string) $def['nama'];
        }

        return self::nama(self::induk($peran));
    }

    public static function label(string $peran): string
    {
        $label = self::definisi($peran)['label'] ?? null;

        return is_string($label) && $label !== '' ? $label : 'Administrator';
    }

    /** Peran induk akun internal (peran operasional = dirinya sendiri). */
    public static function induk(string $peran): string
    {
        $induk = self::definisi($peran)['induk'] ?? null;

        return is_string($induk) && $induk !== '' ? $induk : 'superadmin';
    }

    /** @return list<string> */
    public static function kemampuan(string $peran): array
    {
        /** @var list<string> $daftar */
        $daftar = (array) (self::definisi($peran)['kemampuan'] ?? []);

        return $daftar;
    }

    public static function boleh(string $peran, string $kemampuan): bool
    {
        return in_array($kemampuan, self::kemampuan($peran), true);
    }

    /** @return list<string> Peran (termasuk akun internal) yang punya kemampuan ini. */
    public static function peranUntuk(string $kemampuan): array
    {
        return array_values(array_filter(
            self::semuaPeran(),
            fn (string $peran): bool => self::boleh($peran, $kemampuan)
        ));
    }

    /** @return list<string> Kemampuan yang mungkin muncul (untuk validasi). */
    public static function semuaKemampuan(): array
    {
        /** @var list<string> $daftar */
        $daftar = array_keys(self::peta('halaman'));

        return $daftar;
    }

    /** @return array<string, string> Label aksi tanpa menu (dari config). */
    public static function semuaAksi(): array
    {
        /** @var array<string, string> $aksi */
        $aksi = self::peta('aksi');

        return $aksi;
    }

    /** Label sebuah aksi untuk log aktivitas; kunci tak dikenal dikembalikan apa adanya. */
    public static function labelAksi(string $aksi): string
    {
        return self::semuaAksi()[$aksi] ?? $aksi;
    }

    /**
     * Definisi menu/route sebuah kemampuan.
     *
     * @return array<string, mixed>
     */
    public static function halaman(string $kemampuan): array
    {
        $def = self::peta('halaman')[$kemampuan] ?? [];

        return is_array($def) ? $def : [];
    }

    /**
     * Nama akhiran route sebuah kemampuan atau aksi.
     * Halaman bermenu memakai `route` di config (mis. draf -> draf_proposal);
     * aksi memakai kuncinya sendiri (mis. inbox.tolak -> inbox.tolak).
     */
    public static function ruteKemampuan(string $kemampuan): string
    {
        $route = self::halaman($kemampuan)['route'] ?? null;

        return is_string($route) && $route !== '' ? $route : $kemampuan;
    }

    /** @return list<string> Urutan kemampuan untuk menu, sesuai config. */
    public static function urutanMenu(): array
    {
        return self::semuaKemampuan();
    }

    /**
     * Judul grup menu untuk sebuah section.
     */
    public static function labelSection(string $section): string
    {
        return self::sections()[$section] ?? '';
    }

    /** @return array<string, string> */
    public static function sections(): array
    {
        /** @var array<string, string> $sections */
        $sections = (array) config('admin_akses.section', []);

        return $sections;
    }

    /**
     * Peran yang boleh login di prefix sebuah peran.
     * Prefix superadmin juga menerima akun internal (developer).
     *
     * @return list<string>
     */
    public static function peranLoginDi(string $peran): array
    {
        return array_merge(
            [$peran],
            $peran === 'superadmin' ? self::akunLain() : []
        );
    }

    /**
     * Akun aktif sebuah peran, atau null bila kredensialnya belum lengkap.
     *
     * Urutan sumber: .env (hash bcrypt) lalu akun demo config/admin_akses.php
     * yang hanya berlaku di luar production. Di production akun tanpa .env
     * tetap mati, jadi login ditutup total.
     *
     * @return array{name: string, username: string, email: string, password_hash: string, password: string|null}|null
     */
    public static function akunAktif(string $peran): ?array
    {
        /** @var array<string, mixed> $akun */
        $akun = (array) (self::definisi($peran)['akun'] ?? []);

        $username = trim((string) ($akun['username'] ?? ''));
        $passwordHash = trim((string) ($akun['password_hash'] ?? ''));

        // .env belum lengkap: pakai akun demo bila diizinkan.
        if ($username === '' || $passwordHash === '') {
            return self::akunDemo($peran);
        }

        $nama = trim((string) ($akun['name'] ?? ''));

        return [
            'name' => $nama === '' ? self::label($peran) : $nama,
            'username' => $username,
            'email' => trim((string) ($akun['email'] ?? '')),
            'password_hash' => $passwordHash,
            'password' => null,
        ];
    }

    /**
     * Akun demo sebuah peran dari config/admin_akses.php.
     *
     * Hanya aktif di luar production. Kata sandinya plaintext di config karena
     * memang data dummy yang boleh dipublikasikan; perbandingan tetap memakai
     * hash_equals agar tidak bocor lewat timing.
     *
     * @return array{name: string, username: string, email: string, password_hash: string, password: string}|null
     */
    public static function akunDemo(string $peran): ?array
    {
        if (app()->isProduction()) {
            return null;
        }

        /** @var array<string, mixed> $demo */
        $demo = (array) config('admin_akses.demo.'.$peran, []);

        $username = trim((string) ($demo['username'] ?? ''));
        $sandi = (string) ($demo['password'] ?? '');

        if ($username === '' || $sandi === '') {
            return null;
        }

        $nama = trim((string) ($demo['name'] ?? ''));

        return [
            'name' => $nama === '' ? self::label($peran) : $nama,
            'username' => $username,
            'email' => trim((string) ($demo['email'] ?? '')),
            'password_hash' => '',
            'password' => $sandi,
        ];
    }

    /** Nama route halaman pertama peran setelah login. */
    public static function beranda(string $peran): string
    {
        $def = self::definisi($peran);
        $beranda = (string) ($def['beranda'] ?? 'inbox');

        return self::nama($peran).'.'.self::ruteKemampuan($beranda);
    }

    /** Peran di sesi saat ini, atau null bila belum masuk. */
    public static function peranSesi(): ?string
    {
        $auth = session(self::KUNCI_SESI);
        $peran = is_array($auth) ? ($auth['role'] ?? null) : null;

        return is_string($peran) && $peran !== '' ? $peran : null;
    }

    /** Nama lengkap pengguna di sesi (untuk sidebar/topbar). */
    public static function namaSesi(): string
    {
        $auth = session(self::KUNCI_SESI);
        $nama = is_array($auth) ? ($auth['name'] ?? null) : null;

        return is_string($nama) && $nama !== '' ? $nama : 'Administrator';
    }

    /** Awalan route peran yang sedang login (akun internal -> induknya). */
    public static function awalanRouteSesi(): string
    {
        $peran = self::peranSesi();

        return $peran === null ? self::nama('superadmin') : self::nama($peran);
    }

    /**
     * URL sebuah halaman untuk peran yang sedang login.
     * Dipakai view agar satu view bisa melayani beberapa peran.
     *
     * @param  array<string, mixed>  $parameter
     */
    public static function rute(string $kemampuan, array $parameter = []): string
    {
        return route(self::awalanRouteSesi().'.'.self::ruteKemampuan($kemampuan), $parameter);
    }

    /**
     * Nama route aktif untuk keperluan penanda menu (`routeIs`).
     */
    public static function polaRute(string $kemampuan): string
    {
        return self::awalanRouteSesi().'.'.self::ruteKemampuan($kemampuan);
    }

    /**
     * Menu sidebar untuk peran yang sedang login.
     *
     * @return array<string, array{judul: string, item: list<array<string, mixed>>}>
     */
    public static function menuSesi(): array
    {
        $peran = self::peranSesi();

        return $peran === null ? [] : self::menu($peran);
    }

    /**
     * Menu sidebar sebuah peran, dikelompokkan per section sesuai config.
     *
     * Hanya kemampuan yang dimiliki peran yang muncul, sehingga tautan,
     * badge, dan counter milik peran lain tidak pernah dirender.
     *
     * @return array<string, array{judul: string, item: list<array<string, mixed>>}>
     */
    public static function menu(string $peran): array
    {
        $hasil = [];

        foreach (self::sections() as $kunci => $judul) {
            $item = [];

            foreach (self::kemampuan($peran) as $kemampuan) {
                $def = self::halaman($kemampuan);

                if ((string) ($def['section'] ?? '') !== $kunci) {
                    continue;
                }

                $item[] = [
                    'kemampuan' => $kemampuan,
                    'label' => (string) ($def['label'] ?? $kemampuan),
                    'ikon' => (string) ($def['ikon'] ?? ''),
                    'badge' => (string) ($def['badge'] ?? ''),
                    'url' => route(self::nama($peran).'.'.self::ruteKemampuan($kemampuan)),
                    'aktif' => self::nama($peran).'.'.self::ruteKemampuan($kemampuan),
                    'luar' => false,
                ];
            }

            foreach (self::tautan() as $def) {
                if ((string) ($def['section'] ?? '') !== $kunci) {
                    continue;
                }

                $namaRute = (string) ($def['route'] ?? '');
                if ($namaRute === '' || ! Route::has($namaRute)) {
                    continue;
                }

                $item[] = [
                    'kemampuan' => '',
                    'label' => (string) ($def['label'] ?? $namaRute),
                    'ikon' => (string) ($def['ikon'] ?? ''),
                    'badge' => '',
                    'url' => route($namaRute),
                    'aktif' => '',
                    'luar' => true,
                ];
            }

            if ($item !== []) {
                $hasil[$kunci] = ['judul' => $judul, 'item' => $item];
            }
        }

        return $hasil;
    }

    /**
     * Tautan luar untuk semua peran (mis. portal umum).
     *
     * @return array<int, array<string, mixed>>
     */
    public static function tautan(): array
    {
        /** @var array<int, array<string, mixed>> $daftar */
        $daftar = array_values(array_filter(
            (array) config('admin_akses.tautan', []),
            'is_array'
        ));

        return $daftar;
    }

    /** Label peran di sesi, untuk sidebar & topbar. */
    public static function labelSesi(): string
    {
        $peran = self::peranSesi();

        return $peran === null ? 'Administrator' : self::label($peran);
    }

    /** Nama route login untuk sebuah peran (dipakai middleware & logout). */
    public static function ruteLogin(string $peran): string
    {
        return self::nama($peran).'.login';
    }

    /** Nama route logout untuk sebuah peran. */
    public static function ruteLogout(string $peran): string
    {
        return self::nama($peran).'.logout';
    }

    /** @return array<string, string> Peta URL lama -> akhiran nama route baru. */
    public static function alias(): array
    {
        /** @var array<string, string> $alias */
        $alias = (array) config('admin_akses.alias', []);

        return $alias;
    }

    /** Prefix panel lama (untuk pengalihan tautan usang). */
    public static function pathLama(): string
    {
        return trim((string) config('admin_akses.path_lama', 'admin-mitrakerjasama'), '/');
    }

    /**
     * Peran pemilik prefix URL tertentu.
     * Akun internal dihitung sebagai peran induknya.
     */
    public static function peranDariPath(string $path): ?string
    {
        $path = trim($path, '/');

        if ($path === '') {
            return null;
        }

        foreach (self::semuaPeran() as $peran) {
            if (self::path($peran) === $path) {
                return self::ada($peran) ? $peran : self::induk($peran);
            }
        }

        return null;
    }

    /**
     * Peran pemilik sebuah nama route (mis. `kerjasama.inbox` -> admin_kerjasama).
     */
    public static function peranDariNamaRoute(string $namaRoute): ?string
    {
        $awalan = str_contains($namaRoute, '.') ? explode('.', $namaRoute)[0] : '';

        if ($awalan === '') {
            return null;
        }

        foreach (self::semuaPeran() as $peran) {
            if (self::nama($peran) === $awalan) {
                return self::ada($peran) ? $peran : self::induk($peran);
            }
        }

        return null;
    }
}
