<?php

/*
|--------------------------------------------------------------------------
| Peta izin panel admin — SATU sumber kebenaran
|--------------------------------------------------------------------------
| Berkas ini dipakai oleh:
|   - bootstrap/app.php        -> prefix URL per peran (dari .env)
|   - routes/*.php             -> middleware per kemampuan/aksi
|   - app/Support/AdminAkses   -> pembacaan terketik untuk route, menu, test
|   - resources/views/layouts  -> menu & identitas peran yang login
|   - tests/Feature            -> memastikan peran lain ditolak
|
| Struktur:
|   peran.<nama>.kemampuan[]      : kemampuan + aksi yang dimiliki peran
|   halaman.<kemampuan>           : halaman BERMENU (label, section, ikon, route)
|   aksi.<kemampuan>              : aksi tanpa menu (label untuk log aktivitas)
|   section.<kunci>               : judul grup menu sidebar
|   alias.<path_lama>.<suffix>    : pengalihan URL lama -> halaman Superadmin
|
| Kemampuan dan aksi memakai kunci yang sama; sebuah route dilindungi dengan
| `admin.only:<peran yang punya kunci itu>`. Aksi yang tidak dimiliki sebuah
| peran otomatis 404, bukan hanya disembunyikan dari menu.
|
| Matriks yang berlaku (lihat dokumentasi fase):
|   Superadmin      : dashboard, inbox + setuju/tolak, draf + unduh,
|                     masa aktif + atur, profil/visi misi, artikel, galeri,
|                     aktivitas, pengguna
|   Admin Kerja Sama: inbox + setuju/tolak, draf + unduh, masa aktif + atur
|                     (setelah login langsung ke inbox, tanpa dashboard)
|   Admin Konten    : dashboard data mitra diterima (baca-saja), profil/visi
|                     misi, artikel, galeri (setelah login langsung ke
|                     dashboard data mitra diterima)
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
    | Halaman bermenu. Kunci ini dipakai sebagai akhiran nama route
    | (mis. `superadmin.inbox`) dan sebagai penanda menu sidebar.
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

        'aktivitas' => [
            'route' => 'aktivitas',
            'label' => 'Aktivitas Admin Kerjasama',
            'section' => 'kerja',
            'ikon' => 'aktivitas',
        ],

        'tentang' => [
            'route' => 'tentang',
            'label' => 'Profil & Visi Misi',
            'section' => 'konten',
            'ikon' => 'profil',
        ],

        // Nama route sengaja berbeda dari `dashboard` milik Superadmin: satu
        // nama route = satu kemampuan, sehingga matriks akses tetap satu arah.
        'dashboard_konten' => [
            'route' => 'dashboard_konten',
            'label' => 'Data Mitra Diterima',
            'section' => 'kerja',
            'ikon' => 'dashboard',
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

        'template_mou' => [
            'route' => 'template_mou',
            'label' => 'Template MoU',
            'section' => 'konten',
            'ikon' => 'artikel',
        ],

        'pengguna' => [
            'route' => 'pengguna',
            'label' => 'Kelola Pengguna',
            'section' => 'sistem',
            'ikon' => 'pengguna',
        ],

        'uji_email' => [
            'route' => 'test_email',
            'label' => 'Uji Email (Lokal)',
            'section' => 'sistem',
            'ikon' => 'surel',
        ],
    ],

    /*
    | Aksi tanpa menu sendiri. Nilainya label untuk log aktivitas dan pesan
    | konfirmasi; route-nya memakai akhiran yang sama dengan kuncinya
    | (mis. `inbox.approve` -> superadmin.inbox.approve).
    */
    'aksi' => [
        'inbox.approve' => 'Menyetujui pengajuan',
        'inbox.reject' => 'Menolak pengajuan',
        'draf_proposal.unduh' => 'Mengunduh draf proposal',
        'masa_aktif.atur' => 'Mengubah masa aktif',
        'template_mou.update' => 'Menyimpan template MoU',
        'template_mou.reset' => 'Mengembalikan template MoU ke bawaan',
    ],

    /* Grup menu sidebar (urutan tampil). */
    'section' => [
        'kerja' => 'Menu Administrator',
        'konten' => 'Konten Portal',
        'sistem' => 'Sistem',
        'luar' => 'Tautan Luar',
    ],

    /*
    | Tautan untuk SEMUA peran (tanpa hak khusus). Rutenya diperiksa dengan
    | Route::has(), jadi tautan yang belum ada tidak dirender.
    */
    'tautan' => [
        [
            'route' => 'landing',
            'label' => 'Lihat Portal Umum',
            'section' => 'luar',
            'ikon' => 'portal',
            'target' => '_blank',
        ],
    ],

    /*
    | Peran operasional: punya prefix + halaman login sendiri.
    |
    | `nama`    : awalan nama route (superadmin.inbox, kerjasama.inbox)
    | `beranda` : halaman pertama setelah login
    | `akun`    : kredensial dari .env; akun mati bila ada kolom yang kosong
    */
    'peran' => [

        'superadmin' => [
            'label' => 'Super Administrator',
            'path' => env('SUPERADMIN_PATH', 'superadmin'),
            'nama' => 'superadmin',
            'beranda' => 'dashboard',
            'kemampuan' => [
                'dashboard',
                'inbox', 'inbox.approve', 'inbox.reject',
                'draf', 'draf_proposal.unduh',
                'masa_aktif', 'masa_aktif.atur',
                'aktivitas',
                'tentang', 'artikel', 'galeri',
                'template_mou',
                'pengguna',
            ],
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
            // Seperti Superadmin, peran ini mengelola pengajuan mitra:
            // menyetujui, menolak (surat resmi + WhatsApp), dan mengunduh draf.
            'kemampuan' => [
                'inbox', 'inbox.approve', 'inbox.reject',
                'draf', 'draf_proposal.unduh',
                'masa_aktif', 'masa_aktif.atur',
            ],
            'akun' => [
                'name' => (string) env('ADMIN_KERJASAMA_NAME', 'Admin Kerja Sama'),
                'username' => (string) env('ADMIN_KERJASAMA_USERNAME', ''),
                'email' => (string) env('ADMIN_KERJASAMA_EMAIL', ''),
                'password_hash' => (string) env('ADMIN_KERJASAMA_PASSWORD_HASH', ''),
            ],
        ],

        /*
        | Admin Konten: satu tingkat di bawah Admin Kerja Sama. Hanya boleh melihat
        | daftar mitra yang sudah diterima dan mengelola konten portal (tentang,
        | artikel, galeri). Tanpa inbox, draf, masa aktif, template MoU, aktivitas,
        | dan kelola pengguna.
        */
        'admin_konten' => [
            'label' => 'Admin Konten',
            'path' => env('ADMIN_KONTEN_PATH', 'admin-konten'),
            'nama' => 'konten',
            'beranda' => 'dashboard_konten',
            'kemampuan' => [
                'dashboard_konten',
                'tentang', 'artikel', 'galeri',
            ],
            'akun' => [
                'name' => (string) env('ADMIN_KONTEN_NAME', 'Admin Konten'),
                'username' => (string) env('ADMIN_KONTEN_USERNAME', ''),
                'email' => (string) env('ADMIN_KONTEN_EMAIL', ''),
                'password_hash' => (string) env('ADMIN_KONTEN_PASSWORD_HASH', ''),
            ],
        ],
    ],

    /*
    | Akun DEMO panel admin (DATA DUMMY) — dipakai HANYA bila .env belum
    | mengisi USERNAME/PASSWORD_HASH untuk peran itu DAN APP_ENV bukan
    | production. Nilainya ikut ter-commit supaya proyek bisa langsung dicoba
    | di mesin mana pun tanpa menyetel .env lebih dulu.
    |
    | Kata sandi tiap peran SENGAJA berbeda. Sebelumnya ketiganya memakai
    | password yang sama, sehingga akun Superadmin bisa dipakai masuk ke
    | panel Admin Kerja Sama. Dengan kata sandi berbeda, tiap halaman login
    | benar-benar memisahkan peran.
    |
    | Di produksi (APP_ENV=production) akun demo ini tidak pernah aktif.
    */
    'demo' => [
        'superadmin' => [
            'name' => 'Super Administrator',
            'username' => 'superadmin',
            'email' => 'superadmin@komdigi.go.id',
            'password' => 'superadmin123',
        ],
        'admin_kerjasama' => [
            'name' => 'Admin Kerja Sama',
            'username' => 'kerjasama',
            'email' => 'kerjasama@komdigi.go.id',
            'password' => 'kerjasama123',
        ],
        'admin_konten' => [
            'name' => 'Admin Konten',
            'username' => 'konten',
            'email' => 'konten@komdigi.go.id',
            'password' => 'konten123',
        ],
        'developer' => [
            'name' => 'Developer',
            'username' => 'developer',
            'email' => 'developer@komdigi.go.id',
            'password' => 'developer123',
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
        'template-mou' => 'template_mou',
        'login' => 'login',
        'test-email' => 'test_email',
    ],
];
