<?php

return [
    'path'        => env('ADMIN_PATH', 'admin'),
'allowed_ips' => env('ADMIN_ALLOWED_IPS', ''),
    'accounts' => [
        [
            'name'     => 'Admin Kerja Sama',
            'username' => 'admin',
            'email'    => 'admin@komdigi.test',
            'password' => 'Admin#2026',
            'role'     => 'admin',
        ],
        [
            'name'     => 'Developer',
            'username' => 'developer',
            'email'    => 'developer@komdigi.test',
            'password' => 'Dev#2026',
            'role'     => 'developer',
        ],
    ],
];