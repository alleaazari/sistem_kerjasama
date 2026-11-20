<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanTemplateMouRequest;
use App\Support\AdminAkses;
use App\Support\BidangKerjasama;
use App\Support\LogAktivitas;
use App\Support\TemplateMou;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman eksklusif Superadmin.
 *
 * Aktivitas dan Pengguna baca-saja: tidak ada route tulis, karena
 * perubahannya menunggu backend (tabel `users` dan tabel `log_aktivitas`).
 * Tombol yang butuh backend dirender disabled dengan alasan yang terlihat.
 * Template MoU dapat disunting dan versinya dicatat ke log aktivitas.
 *
 * TODO-BACKEND: Aktivitas -> tabel log_aktivitas dengan paginasi SQL;
 * Pengguna -> tabel users + role, aksi (ubah peran, reset sandi, nonaktifkan)
 * belum dibangun dan sengaja tidak berpura-pura berfungsi; Template MoU ->
 * tabel `template_mou` dengan riwayat versi penuh (saat ini JSON + log).
 */
class AdminSuperadminController extends Controller
{
    /**
     * Editor template MoU per bidang. Hanya Superadmin (middleware rute).
     * Daftar bidang dibaca dari config; template yang belum ada tetap tampil
     * supaya slot bidang baru bisa diisi tanpa mengubah kode.
     */
    public function templateMou(Request $request): View
    {
        $kodeDaftar = BidangKerjasama::kode();
        $semua = TemplateMou::semua();

        $dipilih = (string) $request->query('kode', '');
        if (! in_array($dipilih, $kodeDaftar, true)) {
            $dipilih = $kodeDaftar[0] ?? '';
        }

        abort_if($dipilih === '', 404);

        $template = $semua[$dipilih] ?? null;
        $label = BidangKerjasama::label($dipilih);
        $judul = $this->teksLama('judul', (string) ($template['judul'] ?? ''));
        $catatan = $this->teksLama('catatan', (string) ($template['catatan'] ?? ''));
        $isi = $this->teksLama('isi', (string) ($template['isi'] ?? ''));

        $daftar = array_map(static fn (string $kode): array => [
            'kode' => $kode,
            'label' => BidangKerjasama::label($kode),
            'judul' => $semua[$kode]['judul'] ?? '',
            'versi' => $semua[$kode]['versi'] ?? 0,
            'ada' => isset($semua[$kode]),
        ], $kodeDaftar);

        $contoh = TemplateMou::contohData($label);

        return view('admin.template_mou', [
            'daftar' => $daftar,
            'dipilih' => $dipilih,
            'labelBidang' => $label,
            'template' => $template,
            'judul' => $judul,
            'catatan' => $catatan,
            'isi' => $isi,
            'versi' => $template['versi'] ?? 0,
            'diperbaruiOleh' => $template['diperbarui_oleh'] ?? '',
            'diperbaruiPada' => $template['diperbarui_pada'] ?? null,
            'bawaanTersedia' => TemplateMou::bawaan($dipilih) !== null,
            'placeholders' => TemplateMou::PLACEHOLDER,
            'contoh' => $contoh,
            'pratinjau' => TemplateMou::render(['isi' => $isi], $contoh),
            'riwayat' => $this->riwayatTemplate($dipilih),
        ]);
    }

    /** Simpan versi baru template yang dipilih. */
    public function simpanTemplateMou(SimpanTemplateMouRequest $request, string $kode): RedirectResponse
    {
        $this->pastikanBidang($kode);
        $validated = $request->validated();

        $template = TemplateMou::simpanPerubahan(
            $kode,
            trim((string) $validated['judul']),
            trim((string) ($validated['catatan'] ?? '')),
            TemplateMou::rapikanTeks((string) $validated['isi']),
            AdminAkses::namaSesi(),
        );

        LogAktivitas::catat('template_mou.update', $kode.' (versi '.$template['versi'].')');

        return redirect()
            ->route('superadmin.template_mou', ['kode' => $kode])
            ->with('ok', 'Template '.$kode.' versi '.$template['versi'].' disimpan. Generator mitra memakai versi ini untuk draf berikutnya.');
    }

    /** Kembalikan template ke isi bawaan; versi tetap naik dan dicatat. */
    public function resetTemplateMou(string $kode): RedirectResponse
    {
        $this->pastikanBidang($kode);

        $template = TemplateMou::kembalikan($kode, AdminAkses::namaSesi());

        if ($template === null) {
            return back()->with('error', 'Bidang '.$kode.' tidak memiliki template bawaan untuk dikembalikan.');
        }

        LogAktivitas::catat('template_mou.reset', $kode.' (versi '.$template['versi'].')');

        return redirect()
            ->route('superadmin.template_mou', ['kode' => $kode])
            ->with('ok', 'Template '.$kode.' dikembalikan ke isi bawaan (versi '.$template['versi'].').');
    }

    /** Kode bidang harus ada di config, bukan nilai bebas dari URL. */
    private function pastikanBidang(string $kode): void
    {
        abort_unless(in_array($kode, BidangKerjasama::kode(), true), 404);
    }

    /** Nilai old() yang aman untuk field teks; array dari klien diabaikan. */
    private function teksLama(string $kunci, string $bawaan): string
    {
        $nilai = old($kunci);

        return is_string($nilai) ? $nilai : $bawaan;
    }

    /**
     * Riwayat perubahan template satu bidang, terbaru lebih dulu.
     *
     * @return array<int, array<string, mixed>>
     */
    private function riwayatTemplate(string $kode): array
    {
        $rows = array_filter(LogAktivitas::all(), function (array $row) use ($kode): bool {
            $aksi = (string) ($row['aksi'] ?? '');

            return in_array($aksi, ['template_mou.update', 'template_mou.reset'], true)
                && str_starts_with((string) ($row['target'] ?? ''), $kode.' ');
        });

        return array_slice(array_values($rows), 0, 8);
    }

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
