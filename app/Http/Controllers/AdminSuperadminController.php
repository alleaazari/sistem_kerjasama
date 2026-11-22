<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanTemplateMouRequest;
use App\Support\AdminAkses;
use App\Support\BidangKerjasama;
use App\Support\LandingCms;
use App\Support\LogAktivitas;
use App\Support\MasaAktifMitra;
use App\Support\PengajuanMasuk;
use App\Support\StatusKerjasama;
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

    /**
     * Dashboard admin konten: daftar mitra yang sudah disetujui, baca-saja.
     *
     * Hanya kolom yang boleh dilihat peran ini yang dikirim ke view. Kontak
     * PIC, pengajuan yang masih menunggu/ditolak, dan aksi keputusan tidak
     * pernah masuk ke data view.
     */
    public function dashboardKonten(): View
    {
        // Peta masa aktif per instansi, dari data tersimpan (bukan input klien).
        $masaAktif = [];

        foreach (MasaAktifMitra::all() as $baris) {
            $instansi = trim((string) ($baris['instansi'] ?? ''));

            if ($instansi !== '') {
                $masaAktif[$instansi] = $baris;
            }
        }

        $mitraDiterima = [];

        foreach (PengajuanMasuk::all() as $pengajuan) {
            if (($pengajuan['status'] ?? null) !== 'Disetujui') {
                continue;
            }

            $instansi = trim((string) ($pengajuan['instansi'] ?? ''));
            $baris = $masaAktif[$instansi] ?? null;
            $status = StatusKerjasama::hitung(
                $baris['tanggal_mulai'] ?? null,
                $baris['tanggal_berakhir'] ?? null
            );
            $tersedia = $status['mulai'] !== null && $status['selesai'] !== null;
            $kodeBidang = (string) ($pengajuan['bidang'] ?? '');

            $mitraDiterima[] = [
                'instansi' => $instansi,
                'bidang' => BidangKerjasama::label($kodeBidang) ?: $kodeBidang,
                // Status pengajuan: seluruh baris di sini sudah disetujui.
                'status' => (string) $pengajuan['status'],
                // Kondisi masa aktif; hanya keadaan mendesak yang diberi warna.
                'masa' => $tersedia ? $status['label'] : 'Belum tersedia',
                'kelas_masa' => match (true) {
                    ! $tersedia => '',
                    $status['kondisi'] === StatusKerjasama::SEGERA => 'badge badge-warning',
                    $status['kondisi'] === StatusKerjasama::BERAKHIR => 'badge badge-danger',
                    default => '',
                },
                'mulai' => $status['mulai'],
                'selesai' => $status['selesai'],
                'keterangan' => $status['keterangan'],
            ];
        }

        return view('admin.dashboard_konten', ['mitraDiterima' => $mitraDiterima]);
    }

    // ---------------- KONTEN PORTAL (Superadmin & Admin Konten) ----------------
    // Satu implementasi untuk kedua peran; yang berbeda hanya prefix URL dan
    // middleware di routes/superadmin.php dan routes/admin_konten.php.

    public function tentang(): View
    {
        $cms = app(LandingCms::class);
        $data = $cms->all();

        return view('admin.tentang', [
            'petinggi' => $data['petinggi'],
            'visi' => $data['visi'],
        ]);
    }

    public function artikel(): View
    {
        $rows = app(LandingCms::class)->all()['artikel'];

        return view('admin.artikel', ['rows' => $rows]);
    }

    public function galeri(): View
    {
        $rows = app(LandingCms::class)->all()['galeri'];

        return view('admin.galeri', ['rows' => $rows]);
    }

    public function tentangUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'p_nama.*' => 'required|string|max:255',
            'p_jabatan.*' => 'required|string|max:255',
            'p_badge.*' => 'required|string|max:255',
            'p_foto.*' => 'nullable|image|max:2048',
            'p_foto_url.*' => 'nullable|string|max:1000',
            'p_desc.*' => 'required|string',
            'visi' => 'required|string',
        ]);

        $cms = app(LandingCms::class);
        $current = $cms->all()['petinggi'];
        $petinggi = [];
        $count = count($request->input('p_nama', []));

        for ($i = 0; $i < $count; $i++) {
            $foto = $request->input('p_foto_url')[$i] ?? ($current[$i]['foto'] ?? '');
            if ($request->hasFile('p_foto') && isset($request->file('p_foto')[$i]) && $request->file('p_foto')[$i]->isValid()) {
                $file = $request->file('p_foto')[$i];
                $foto = 'data:'.$file->getMimeType().';base64,'.base64_encode((string) file_get_contents($file->getRealPath()));
            }

            $petinggi[] = [
                'nama' => $request->input('p_nama')[$i],
                'jabatan' => $request->input('p_jabatan')[$i],
                'badge' => $request->input('p_badge')[$i],
                'foto' => $foto,
                'desc' => $request->input('p_desc')[$i],
            ];
        }

        $cms->save('petinggi', $petinggi);
        $cms->save('visi', $request->input('visi'));

        return back()->with('ok', 'Profil Petinggi & Visi Misi Komdigi berhasil diperbarui.');
    }

    public function artikelStore(Request $request): RedirectResponse
    {
        $request->validate(['judul' => 'required|string|max:255']);
        $cms = app(LandingCms::class);
        $rows = $cms->all()['artikel'];
        $rows[] = [
            'id' => time().rand(10, 99),
            'judul' => $request->input('judul'),
            'konten' => $request->input('konten', ''),
            'tanggal' => now()->translatedFormat('d F Y'),
            'gambar' => $request->input('gambar', ''),
        ];
        $cms->save('artikel', array_values($rows));

        return back()->with('ok', 'Artikel "'.$request->judul.'" berhasil dipublikasikan.');
    }

    public function artikelDelete(string $id): RedirectResponse
    {
        $cms = app(LandingCms::class);
        $rows = $cms->all()['artikel'];
        $rows = array_values(array_filter($rows, fn ($r) => (string) $r['id'] !== (string) $id));
        $cms->save('artikel', $rows);

        return back()->with('ok', 'Artikel berhasil dihapus.');
    }

    public function artikelEdit(Request $request, string $id): RedirectResponse
    {
        $request->validate(['judul' => 'required|string|max:255']);
        $cms = app(LandingCms::class);
        $rows = $cms->all()['artikel'];
        foreach ($rows as &$r) {
            if ((string) $r['id'] === (string) $id) {
                $r['judul'] = $request->input('judul');
                $r['konten'] = $request->input('konten', $r['konten'] ?? '');
                break;
            }
        }
        unset($r);
        $cms->save('artikel', $rows);

        return back()->with('ok', 'Artikel berhasil diperbarui.');
    }

    public function galeriStore(Request $request): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'gambar' => 'nullable|image|max:2048',
        ]);
        $cms = app(LandingCms::class);
        $rows = $cms->all()['galeri'];

        $gambar = $request->input('gambar_url');
        if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
            $file = $request->file('gambar');
            $gambar = 'data:'.$file->getMimeType().';base64,'.base64_encode((string) file_get_contents($file->getRealPath()));
        }

        $rows[] = [
            'id' => time().rand(10, 99),
            'judul' => $request->input('judul'),
            'konten' => $request->input('konten', ''),
            'tanggal' => now()->translatedFormat('d F Y'),
            'gambar' => $gambar,
        ];

        $cms->save('galeri', array_values($rows));

        return back()->with('ok', 'Foto "'.$request->judul.'" berhasil dipublikasikan.');
    }

    public function galeriDelete(string $id): RedirectResponse
    {
        $cms = app(LandingCms::class);
        $rows = $cms->all()['galeri'];
        $rows = array_values(array_filter($rows, fn ($r) => (string) $r['id'] !== (string) $id));
        $cms->save('galeri', $rows);

        return back()->with('ok', 'Foto galeri berhasil dihapus.');
    }

    public function galeriEdit(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'gambar' => 'nullable|image|max:2048',
        ]);
        $cms = app(LandingCms::class);
        $rows = $cms->all()['galeri'];
        foreach ($rows as &$r) {
            if ((string) $r['id'] === (string) $id) {
                $r['judul'] = $request->input('judul');
                $r['konten'] = $request->input('konten', $r['konten'] ?? '');
                if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
                    $file = $request->file('gambar');
                    $r['gambar'] = 'data:'.$file->getMimeType().';base64,'.base64_encode((string) file_get_contents($file->getRealPath()));
                }
                break;
            }
        }
        unset($r);
        $cms->save('galeri', $rows);

        return back()->with('ok', 'Foto galeri diperbarui.');
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
