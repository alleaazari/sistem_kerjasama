<?php

/*
|--------------------------------------------------------------------------
| Peta izin panel admin — SATU sumber kebenaran
|--------------------------------------------------------------------------
| Dipakai oleh: routes/admin.php (middleware admin.only:...), layout admin
| (menu + label peran), dan test. Peran tidak dikenal = tanpa kemampuan.
|
| TODO-BACKEND: peran masih dari config/admin_demo.php + session. Saat tabel
| `users` punya kolom role, peta ini cukup dibaca dari kolom tersebut.
*/

return [

    /** Kemampuan yang bisa dimiliki sebuah peran. */
    'kemampuan' => [
        // Lihat & ubah konten publik (Profil & Visi Misi, Artikel, Galeri).
        'konten',
        // Lihat & putuskan data kerja sama (pengajuan, perpanjangan, draf, masa aktif).
        'kerja_sama',
        // Kirim email uji dari panel (hanya di lingkungan lokal).
        'uji_email',
    ],

    /** Label peran untuk sidebar & topbar. */
    'label' => [
        'admin_kelola' => 'Pengelola Konten',
        'admin_kerjasama' => 'Administrator Kerja Sama',
        'superadmin' => 'Super Administrator',
        'admin' => 'Administrator',
        'developer' => 'Developer',
    ],

    /**
     * peran => daftar kemampuan.
     * `admin` adalah akun demo lama: tetap akses penuh agar tidak ada akses yang berkurang.
     */
    'peran' => [
        'admin_kelola' => ['konten'],
        'admin_kerjasama' => ['konten', 'kerja_sama'],
        'superadmin' => ['konten', 'kerja_sama'],
        'admin' => ['konten', 'kerja_sama'],
        'developer' => ['uji_email'],
    ],

    /** Halaman pertama tiap peran setelah login. */
    'beranda' => [
        'admin_kelola' => 'admin.dashboard.kelola',
        'admin_kerjasama' => 'admin.inbox',
        'superadmin' => 'admin.inbox',
        'admin' => 'admin.inbox',
        'developer' => 'admin.inbox',
    ],
];
