<?php

namespace App\Http\Controllers;

use App\Support\AdminAkses;
use App\Support\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman eksklusif Superadmin.
 *
 * Keduanya baca-saja: tidak ada route tulis, karena perubahannya menunggu
 * backend (tabel `users` dan tabel `log_aktivitas`). Tombol yang butuh backend
 * dirender disabled dengan alasan yang terlihat.
 *
 * TODO-BACKEND: Aktivitas -> tabel log_aktivitas dengan paginasi SQL;
 * Pengguna -> tabel users + role, aksi (ubah peran, reset sandi, nonaktifkan)
 * belum dibangun dan sengaja tidak berpura-pura berfungsi.
 */
class AdminSuperadminController extends Controller
{
    /** Log aktivitas admin (read-only, dengan filter & paginasi). */
    public function aktivitas(Request $request): View
    {
        $filter = [
            'peran' => (string) $request->query('peran', ''),
            'aksi' => (string) $request->query('aksi', ''),
            'dari' => (string) $request->query('dari', ''),
            'sampai' => (string) $request->query('sampai', ''),
            'q' => (string) $request->query('q', ''),
        ];

        // Tanggal dari URL dipakai untuk menyaring, jadi wajib berformat benar.
        $request->validate([
            'dari' => ['nullable', 'date_format:Y-m-d'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:dari'],
            'peran' => ['nullable', 'string', 'max:64'],
            'aksi' => ['nullable', 'string', 'max:64'],
            'q' => ['nullable', 'string', 'max:100'],
        ], [
            'dari.date_format' => 'Format tanggal awal tidak valid.',
            'sampai.date_format' => 'Format tanggal akhir tidak valid.',
            'sampai.after_or_equal' => 'Tanggal akhir tidak boleh lebih awal dari tanggal awal.',
            'q.max' => 'Kata pencarian maksimal 100 karakter.',
        ]);

        $hasil = LogAktivitas::halaman(LogAktivitas::saring($filter), (int) $request->query('page', 1));

        return view('admin.aktivitas', [
            'rows' => $hasil['data'],
            'total' => $hasil['total'],
            'halaman' => $hasil['halaman'],
            'halamanAkhir' => $hasil['halaman_akhir'],
            'filter' => $filter,
            'adaFilter' => array_filter($filter) !== [],
            'daftarPeran' => AdminAkses::semuaPeran(),
            'daftarAksi' => LogAktivitas::daftarAksi(),
        ]);
    }

    /** Daftar akun panel (read-only sampai backend pengguna tersedia). */
    public function pengguna(): View
    {
        $daftar = [];

        foreach (AdminAkses::semuaPeran() as $peran) {
            $akun = AdminAkses::akunAktif($peran);
            $label = AdminAkses::label($peran);

            $daftar[] = [
                'peran' => $peran,
                'label' => $label,
                'nama' => $akun['name'] ?? $label,
                'username' => $akun['username'] ?? '',
                // Kredensial hanya hash di .env; tidak pernah ditampilkan.
                'aktif' => $akun !== null,
                'path' => AdminAkses::path($peran),
            ];
        }

        return view('admin.pengguna', ['daftar' => $daftar]);
    }
}
