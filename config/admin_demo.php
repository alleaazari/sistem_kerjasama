<?php

/*
|--------------------------------------------------------------------------
| Konfigurasi panel lama — DIHAPUS
|--------------------------------------------------------------------------
| Sebelumnya berkas ini memuat akun plaintext (username + password) untuk
| panel admin. Kredensial sekarang HANYA dibaca dari .env sebagai hash
| bcrypt lewat config/admin_akses.php (SUPERADMIN_*, ADMIN_KERJASAMA_*,
| DEVELOPER_*). Tidak ada password yang ditulis di dalam repo.
|
| Berkas ini sengaja dipertahankan sebagai penanda agar tidak ada kode lama
| yang membacanya tanpa sadar: semua kunci di bawah bernilai kosong.
|
| TODO-BACKEND: hapus berkas ini setelah dipastikan tidak ada pemanggil
| config('admin_demo.*') tersisa, lalu ganti dengan guard Eloquent.
*/

return [
    'path' => '',
    'allowed_ips' => '',
    'accounts' => [],
];
