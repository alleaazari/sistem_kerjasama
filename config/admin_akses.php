<?php

/*
|--------------------------------------------------------------------------
| Peta izin panel admin — SATU sumber kebenaran
|--------------------------------------------------------------------------
| Berkas ini dipakai oleh:
|   - bootstrap/app.php        -> prefix URL per peran (dari .env)
|   - routes/*.php             -> middleware & daftar route per kemampuan
|   - app/Support/AdminAkses   -> pembacaan terketik untuk route, menu, test
|   - resources/views/layouts  -> menu & identitas peran yang login
|   - tests/Feature            -> memastikan peran lain ditolak
|
| Struktur:
|   peran.<nama>.kemampuan[]      : kemampuan yang dimiliki peran
|   halaman.<kemampuan>.route     : {nama_route_suffix, parameter route, middleware argumen}
|   halaman.<kemampuan>.section   : grup menu sidebar
|   halaman.<kemampuan>.label     : label menu (Bahasa Indonesia)
|   menu[]                        : urutan & pengelompokan menu sidebar
|   alias.<path_lama>.<suffix>    : pengalihan URL lama -> halaman Superadmin
|
| Peran operasional punya prefix URL dan halaman login sendiri; kredensial
| dibaca dari .env sebagai hash bcrypt (tidak pernah plaintext di repo).
|
| `admin` (peran lama) sudah dihapus: dipetakan ke `superadmin`.
| `developer` dipertahankan sebagai akun internal untuk /test-email.
|
| TODO-BACKEND: saat tabel `users` punya kolom role, peta ini cukup dibaca
| dari kolom tersebut; kontrak route/controller tidak berubah.
*/

return [

    /*
    | Prefix panel lama. Hanya dipakai untuk mengalihkan tautan lama ke
    | padanan /superadmin/*, supaya bookmark tetap jalan.
    */
    'path_lama' => env('ADMIN_PATH', 'admin-mitrakerjasama'),

    /* Daftar IP yang boleh membuka panel. Kosong = semua IP boleh. */
    'allowed_ips' => env('ADMIN_ALLOWED_IPS', ''),

    /*
    | Kemampuan yang bisa dimiliki sebuah peran. Kunci ini dipakai sebagai
    | akhiran nama route (mis. `superadmin.inbox`) dan sebagai penanda menu.
    */
    'halaman' => [

        'dashboard' => [
            'route' => 'dashboard',
            'label' => 'Dashboard',
            'section' => 'kerja',
            'ikon' => 'dashboard',
        ],

        'inbox' => [
            'route' => 'inbox',
            'label' => 'Inbox Pengajuan',
            'section' => 'kerja',
            'ikon' => 'inbox',
        ],

        'draf' => [
            'route' => 'draf_proposal',
            'label' => 'Draf Proposal AI',
            'section' => 'kerja',
            'ikon' => 'draf',
            'badge' => 'AI',
        ],

        'masa_aktif' => [
            'route' => 'masa_aktif',
            'label' => 'Masa Aktif Mitra',
            'section' => 'kerja',
            'ikon' => 'kalender',
        ],

        'tentang' => [
            'route' => 'tentang',
            'label' => 'Profil & Visi Misi',
            'section' => 'konten',
            'ikon' => 'profil',
        ],

        'artikel' => [
            'route' => 'artikel',
            'label' => 'Kelola Artikel',
            'section' => 'konten',
            'ikon' => 'artikel',
        ],

        'galeri' => [
            'route' => 'galeri',
            'label' => 'Kelola Galeri',
            'section' => 'konten',
            'ikon' => 'galeri',
        ],

        'uji_email' => [
            'route' => 'test_email',
            'label' => 'Uji Email (Lokal)',
            'section' => 'sistem',
            'ikon' => 'surel',
        ],
    ],

    /*
    | Akhiran nama route tambahan sebuah kemampuan (aksi tulis). Dipakai
    | middleware aksi yang tidak punya menu sendiri.
    |
    | contoh: 'inbox' => ['inbox.approve', 'inbox.reject']
    */
    'aksi' => [
        'dashboard' => ['dashboard.setujui', 'dashboard.tolak'],
    ],

    /*
    | Grup menu sidebar (urutan tampil).
    */
    'section' => [
        'kerja' => 'Menu Administrator',
        'konten' => 'Konten Portal',
        'sistem' => 'Sistem & Tautan Luar',
    ],

    /*
    | Peran operasional: punya prefix + halaman login sendiri.
    |
    | `nama`    : awalan nama route (superadmin.inbox, kerjasama.inbox)
    | `beranda` : kemampuan yang jadi halaman pertama setelah login
    | `akun`    : kredensial dari .env; akun mati bila ada kolom yang kosong
    */
    'peran' => [

        'superadmin' => [
            'label' => 'Super Administrator',
            'path' => env('SUPERADMIN_PATH', 'superadmin'),
            'nama' => 'superadmin',
            'beranda' => 'dashboard',
            'kemampuan' => ['dashboard', 'inbox', 'draf', 'masa_aktif', 'tentang', 'artikel', 'galeri'],
            'akun' => [
                'name' => (string) env('SUPERADMIN_NAME', 'Super Administrator'),
                'username' => (string) env('SUPERADMIN_USERNAME', ''),
                'email' => (string) env('SUPERADMIN_EMAIL', ''),
                'password_hash' => (string) env('SUPERADMIN_PASSWORD_HASH', ''),
            ],
        ],

        'admin_kerjasama' => [
            'label' => 'Admin Kerja Sama',
            'path' => env('ADMIN_KERJASAMA_PATH', 'admin-kerjasama'),
            'nama' => 'kerjasama',
            'beranda' => 'inbox',
            // Tiga halaman kerja sama. Dashboard & konten bukan miliknya.
            'kemampuan' => ['inbox', 'draf', 'masa_aktif'],
            'akun' => [
                'name' => (string) env('ADMIN_KERJASAMA_NAME', 'Admin Kerja Sama'),
                'username' => (string) env('ADMIN_KERJASAMA_USERNAME', ''),
                'email' => (string) env('ADMIN_KERJASAMA_EMAIL', ''),
                'password_hash' => (string) env('ADMIN_KERJASAMA_PASSWORD_HASH', ''),
            ],
        ],
    ],

    /*
    | Akun internal tanpa prefix sendiri. Login lewat prefix `superadmin`,
    | tetapi hanya mendapat kemampuannya sendiri (bukan akses penuh).
    */
    'akun_lain' => [

        'developer' => [
            'label' => 'Developer',
            'induk' => 'superadmin',
            'beranda' => 'uji_email',
            'kemampuan' => ['uji_email'],
            'akun' => [
                'name' => (string) env('DEVELOPER_NAME', 'Developer'),
                'username' => (string) env('DEVELOPER_USERNAME', ''),
                'email' => (string) env('DEVELOPER_EMAIL', ''),
                'password_hash' => (string) env('DEVELOPER_PASSWORD_HASH', ''),
            ],
        ],
    ],

    /*
    | Alamat lama /{path_lama}/<suffix> dialihkan ke halaman Superadmin.
    | Hanya GET; aksi tulis tidak dialihkan agar tidak ada perubahan data
    | lewat URL usang.
    */
    'alias' => [
        '' => 'dashboard',
        'inbox' => 'inbox',
        'dashboard' => 'dashboard',
        'draf-proposal' => 'draf_proposal',
        'mitra-draf' => 'draf_proposal',
        'masa-aktif' => 'masa_aktif',
        'tentang' => 'tentang',
        'artikel' => 'artikel',
        'galeri' => 'galeri',
        'login' => 'login',
        'test-email' => 'test_email',
    ],
];
