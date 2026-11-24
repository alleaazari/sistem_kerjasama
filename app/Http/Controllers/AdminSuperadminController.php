<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanPemetaanMouRequest;
use App\Http\Requests\SimpanPengaturanMouRequest;
use App\Http\Requests\SimpanTemplateBlokRequest;
use App\Support\AdminAkses;
use App\Support\BidangKerjasama;
use App\Support\LandingCms;
use App\Support\LogAktivitas;
use App\Support\MasaAktifMitra;
use App\Support\Mou\PendaftaranPlaceholder;
use App\Support\Mou\RendererDokumen;
use App\Support\Mou\StrukturTemplateMou;
use App\Support\PengajuanMasuk;
use App\Support\StatusKerjasama;
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
     * Editor template MoU berbasis blok. Hanya Superadmin (middleware rute).
     * Daftar template dibaca dari storage/app/mou/*.json; pemetaan bidang dan
     * pengaturan global memakai overlay di storage yang sama.
     */
    public function templateMou(Request $request): View
    {
        $semua = StrukturTemplateMou::semua();

        $kodeDaftar = array_keys($semua);
        sort($kodeDaftar);

        $dipilih = (string) $request->query('kode', '');
        if (! in_array($dipilih, $kodeDaftar, true)) {
            $dipilih = $kodeDaftar[0] ?? '';
        }

        abort_if($dipilih === '', 404);

        $template = $semua[$dipilih] ?? null;

        $daftar = array_map(static fn (string $kode): array => [
            'kode' => $kode,
            'nama' => $semua[$kode]['nama'] ?? '',
            'status' => (string) ($semua[$kode]['status'] ?? ''),
            'versi' => (int) ($semua[$kode]['versi'] ?? 0),
            'bidang' => self::bidangUntukTemplate($kode),
            'diperbarui_pada' => $semua[$kode]['diperbarui_pada'] ?? null,
            'diperbarui_oleh' => (string) ($semua[$kode]['diperbarui_oleh'] ?? ''),
        ], $kodeDaftar);

        $statusTerpakai = $template === null ? [] : self::bidangPakaiTemplate($dipilih);

        return view('admin.template_mou', [
            'daftar' => $daftar,
            'dipilih' => $dipilih,
            'template' => $template,
            'blokJson' => json_encode($template['blok'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'nama' => $this->teksLama('nama', (string) ($template['nama'] ?? '')),
            'catatan' => $this->teksLama('catatan', (string) ($template['catatan'] ?? '')),
            'status' => $this->teksLama('status', (string) ($template['status'] ?? 'draf')),
            'versi' => (int) ($template['versi'] ?? 0),
            'diperbaruiOleh' => (string) ($template['diperbarui_oleh'] ?? ''),
            'diperbaruiPada' => $template['diperbarui_pada'] ?? null,
            'bawaanTersedia' => StrukturTemplateMou::bawaan($dipilih) !== null,
            'placeholder' => PendaftaranPlaceholder::daftar(),
            'contoh' => $this->dataContoh(),
            'pratinjau' => $template === null ? null : RendererDokumen::render($template, $this->dataContoh()),
            'riwayat' => $this->riwayatTemplate($dipilih),

            // Pengaturan global + pemetaan (overlay).
            'pengaturan' => StrukturTemplateMou::pengaturan(),
            'pemetaan' => StrukturTemplateMou::pemetaan(),
            'daftarBidang' => BidangKerjasama::daftar(),
            'statusTerpakai' => $statusTerpakai,
        ]);
    }

    /** Simpan versi baru satu template blok. */
    public function simpanTemplateMou(SimpanTemplateBlokRequest $request, string $kode): RedirectResponse
    {
        $this->pastikanTemplate($kode);

        $template = StrukturTemplateMou::simpan(
            $kode,
            $request->template(),
            AdminAkses::namaSesi(),
        );

        LogAktivitas::catat('template_mou.update', $kode.' (versi '.$template['versi'].')');

        return redirect()
            ->route('superadmin.template_mou', ['kode' => $kode])
            ->with('ok', 'Template '.$kode.' versi '.$template['versi'].' disimpan. Generator mitra memakai versi ini untuk draf berikutnya; draf lama tidak berubah.');
    }

    /** Kembalikan template ke isi bawaan; versi tetap naik dan dicatat. */
    public function resetTemplateMou(string $kode): RedirectResponse
    {
        $this->pastikanTemplate($kode);

        $template = StrukturTemplateMou::kembalikan($kode, AdminAkses::namaSesi());

        if ($template === null) {
            return back()->with('error', 'Template '.$kode.' tidak memiliki isi bawaan untuk dikembalikan.');
        }

        LogAktivitas::catat('template_mou.reset', $kode.' (versi '.$template['versi'].')');

        return redirect()
            ->route('superadmin.template_mou', ['kode' => $kode])
            ->with('ok', 'Template '.$kode.' dikembalikan ke isi bawaan (versi '.$template['versi'].').');
    }

    /** Simpan pengaturan global: Pihak Kesatu + dasar hukum nasional. */
    public function simpanPengaturanMou(SimpanPengaturanMouRequest $request): RedirectResponse
    {
        StrukturTemplateMou::simpanPengaturan($request->nilaiBersih());

        LogAktivitas::catat('template_mou.pengaturan', 'pengaturan global');

        return redirect()
            ->route('superadmin.template_mou')
            ->with('ok', 'Pengaturan Pihak Kesatu dan dasar hukum nasional disimpan.');
    }

    /** Simpan pemetaan bidang -> template. */
    public function simpanPemetaanMou(SimpanPemetaanMouRequest $request): RedirectResponse
    {
        StrukturTemplateMou::simpanPemetaan($request->pemetaanBersih());

        LogAktivitas::catat('template_mou.pemetaan', 'pemetaan bidang');

        return redirect()
            ->route('superadmin.template_mou')
            ->with('ok', 'Pemetaan bidang ke template disimpan.');
    }

    /** Kode template harus ada, bukan nilai bebas dari URL. */
    private function pastikanTemplate(string $kode): void
    {
        abort_unless(StrukturTemplateMou::untuk($kode) !== null, 404);
    }

    /** Nilai old() yang aman untuk field teks; array dari klien diabaikan. */
    private function teksLama(string $kunci, string $bawaan): string
    {
        $nilai = old($kunci);

        return is_string($nilai) ? $nilai : $bawaan;
    }

    /**
     * Daftar bidang yang memakai sebuah template (dari pemetaan efektif).
     *
     * @return list<string>
     */
    private function bidangPakaiTemplate(string $kodeTemplate): array
    {
        $hasil = [];

        foreach (StrukturTemplateMou::pemetaan() as $bidang => $kode) {
            if ($kode === $kodeTemplate) {
                $hasil[] = (string) $bidang;
            }
        }

        return $hasil;
    }

    /** Label bidang untuk satu template (kode bidang pertama yang memakainya). */
    private static function bidangUntukTemplate(string $kodeTemplate): string
    {
        foreach (StrukturTemplateMou::pemetaan() as $bidang => $kode) {
            if ($kode === $kodeTemplate) {
                return (string) $bidang;
            }
        }

        return '';
    }

    /**
     * Data contoh untuk pratinjau editor: nilai placeholder yang masuk akal.
     * Bukan data mitra sungguhan.
     *
     * @return array<string, string>
     */
    private function dataContoh(): array
    {
        return [
            'nama_instansi' => 'Universitas Contoh Nusantara',
            'nama_pic' => 'Nama Penanggung Jawab',
            'jabatan_pic' => 'Jabatan Penanggung Jawab',
            'alamat_instansi' => 'Jl. Contoh No. 1, Kota Medan',
            'telepon_instansi' => '061-000000',
            'email_instansi' => 'contoh@instansi.ac.id',
            'pihak_kedua_penandatangan' => 'Nama Penandatangan',
            'pihak_kedua_jabatan' => 'Jabatan Penandatangan',
            'pihak_kedua_dasar_pengangkatan' => 'Keputusan Nomor 1 Tahun 2026',
            'pihak_kedua_alamat' => 'Jl. Contoh No. 1, Kota Medan',
            'nomor_surat' => '001/KS/2026',
            'nomor_dokumen' => '001/KS/2026',
            'nomor_dokumen_mitra' => '002/KS/2026',
            'tempat' => 'Medan',
            'bidang' => 'Kerja Sama Bidang Digital',
            'judul' => 'Nota Kesepakatan Kerja Sama',
            'tanggal' => '1 Januari 2026',
            'tanggal_resmi' => 'Kamis, tanggal satu, bulan Januari, tahun dua ribu dua puluh enam',
            'tahun_mulai' => '2026',
            'tahun_akhir' => '2029',
            'masa_aktif_tahun' => '3',
            'dasar_hukum_daerah' => '',
        ];
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
