<?php

namespace App\Support;

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

    /** Nama akhiran route utama sebuah kemampuan (mis. `draf_proposal`). */
    public static function ruteKemampuan(string $kemampuan): string
    {
        $def = self::halaman($kemampuan);

        return (string) ($def['route'] ?? $kemampuan);
    }

    /**
     * Daftar akhiran nama route yang dilindungi sebuah kemampuan:
     * halaman utamanya + aksi tulisnya.
     *
     * @return list<string>
     */
    public static function ruteKemampuanSemua(string $kemampuan): array
    {
        /** @var list<string> $aksi */
        $aksi = (array) (self::peta('aksi')[$kemampuan] ?? []);

        return array_merge([self::ruteKemampuan($kemampuan)], $aksi);
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
     * Kredensial hanya dibaca dari .env berupa hash bcrypt.
     *
     * @return array{name: string, username: string, email: string, password_hash: string}|null
     */
    public static function akunAktif(string $peran): ?array
    {
        /** @var array<string, mixed> $akun */
        $akun = (array) (self::definisi($peran)['akun'] ?? []);

        $username = trim((string) ($akun['username'] ?? ''));
        $passwordHash = trim((string) ($akun['password_hash'] ?? ''));

        // Tanpa username dan hash, akun dianggap mati (login ditutup total).
        if ($username === '' || $passwordHash === '') {
            return null;
        }

        $nama = trim((string) ($akun['name'] ?? ''));

        return [
            'name' => $nama === '' ? self::label($peran) : $nama,
            'username' => $username,
            'email' => trim((string) ($akun['email'] ?? '')),
            'password_hash' => $passwordHash,
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
                ];
            }

            if ($item !== []) {
                $hasil[$kunci] = ['judul' => $judul, 'item' => $item];
            }
        }

        return $hasil;
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
