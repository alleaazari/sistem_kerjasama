<?php

return [
    'path' => env('ADMIN_PATH', 'admin'),
    'allowed_ips' => env('ADMIN_ALLOWED_IPS', ''),
    'accounts' => [
        [
            'name' => 'Admin Kerja Sama',
            'username' => 'admin',
            'email' => 'admin@komdigi.test',
            'password' => 'admin#2026',
            'role' => 'admin',
        ],
        [
            'name' => 'Developer',
            'username' => 'developer',
            'email' => 'developer@komdigi.test',
            'password' => 'Dev#2026',
            'role' => 'developer',
        ],
        // Admin pengelola konten (Profil & Visi Misi, Artikel, Galeri).
        // Kredensial dari .env; akun tidak aktif bila email/hash kosong.
        [
            'name' => 'Admin Pengelola Konten',
            'username' => (string) env('ADMIN_KELOLA_USERNAME', 'kelola'),
            'email' => (string) env('ADMIN_KELOLA_EMAIL', ''),
            'password_hash' => (string) env('ADMIN_KELOLA_PASSWORD_HASH', ''),
            'role' => 'admin_kelola',
        ],
    ],
];
