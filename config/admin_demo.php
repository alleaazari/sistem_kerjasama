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
        // Superadmin (akses penuh). Kredensial dari .env, hash bcrypt.
        [
            'name' => 'Super Administrator',
            'username' => (string) env('ADMIN_SUPERADMIN_USERNAME', 'superadmin'),
            'email' => (string) env('ADMIN_SUPERADMIN_EMAIL', ''),
            'password_hash' => (string) env('ADMIN_SUPERADMIN_PASSWORD_HASH', ''),
            'role' => 'superadmin',
        ],
        // Administrator kerja sama (akses penuh). Kredensial dari .env, hash bcrypt.
        [
            'name' => 'Administrator Kerja Sama',
            'username' => (string) env('ADMIN_KERJASAMA_USERNAME', 'kerjasama'),
            'email' => (string) env('ADMIN_KERJASAMA_EMAIL', ''),
            'password_hash' => (string) env('ADMIN_KERJASAMA_PASSWORD_HASH', ''),
            'role' => 'admin_kerjasama',
        ],
    ],
];
