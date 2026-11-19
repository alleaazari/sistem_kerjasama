<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilRequest;
use App\Support\BidangKerjasama;
use App\Support\DataDemo;
use App\Support\PengajuanMasuk;
use App\Support\PermohonanPerpanjangan;
use App\Support\StatusKerjasama;
use App\Support\TemplateMou;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Area mitra (mode demo).
 *
 * Identitas mitra diambil dari session('mitra_data') mengikuti pola yang
 * sudah dipakai routes/web.php. Pekerjaan backend (guard auth, tabel mitra,
 * tabel MoU, otorisasi Policy) belum dibangun — lihat catatan di laporan.
 */
class MitraController extends Controller
{
    /**
     * Ambil data mitra dari session, jatuh ke DATA DUMMY.
     *
     * @return array<string, mixed>
     */
    private function mitra(Request $request): array
    {
        return array_merge(DataDemo::mitra(), (array) $request->session()->get('mitra_data', []));
    }

    /** @param array<string, mixed> $data */
    private function simpanMitra(Request $request, array $data): void
    {
        $request->session()->put('mitra_data', $data);
    }

    /**
     * Bidang kerja sama milik mitra ini, dicari dari pengajuan dengan email
     * PIC yang sama dengan identitas session. Tidak memakai id/parameter URL.
     *
     * @param  array<string, mixed>  $mitra
     * @return array{kode: string, label: string}
     */
    private function bidang(array $mitra): array
    {
        $pengajuan = PengajuanMasuk::untukEmail((string) ($mitra['email'] ?? ''));
        $kode = $pengajuan['bidang'] ?? null;

        // Data lama bisa berupa array atau teks bebas; anggap tidak ada.
        $kode = is_string($kode) ? $kode : '';
        $label = BidangKerjasama::label($kode);

        return $kode !== '' && $label !== ''
            ? ['kode' => $kode, 'label' => $label]
            : ['kode' => '', 'label' => ''];
    }

    /** Kunci session untuk permohonan perpanjangan (demo). */
    public function status(Request $request): View
    {
        $mitra = $this->mitra($request);
        $perpanjangan = PermohonanPerpanjangan::terbaruUntuk((string) $mitra['username']);

        // Pratinjau pengembang: hanya di dalam mode debug, tidak disimpan.
        $pratinjau = null;
        if (config('app.debug')) {
            $pratinjau = $request->query('pratinjau');
            if (is_string($pratinjau) && in_array($pratinjau, ['aktif', 'segera', 'berakhir'], true)) {
                $hariIni = CarbonImmutable::now(config('app.timezone', 'UTC'))->startOfDay();
                $selesai = match ($pratinjau) {
                    'aktif' => $hariIni->addDays(200),
                    'segera' => $hariIni->addDays(30),
                    'berakhir' => $hariIni->subDays(12),
                };
                $mitra['selesai'] = $selesai->toDateString();
            } else {
                $pratinjau = null;
            }
        }

        $status = StatusKerjasama::hitung(
            $mitra['mulai'] ?? null,
            $mitra['selesai'] ?? null,
        );

        return view('user.status', [
            'mitra' => $mitra,
            'bidang' => $this->bidang($mitra),
            'status' => $status,
            'perpanjangan' => $perpanjangan,
            'pratinjau' => $pratinjau,
        ]);
    }

    /**
     * Simpan permohonan perpanjangan agar terbaca admin di Dashboard.
     * Tidak memakai session: admin membaca berkas yang sama.
     */
    public function ajukanPerpanjangan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'durasi' => 'required|integer|in:1,2,3',
            'catatan' => 'nullable|string|max:1000',
        ], [
            'durasi.required' => 'Pilih durasi perpanjangan.',
            'durasi.in' => 'Durasi perpanjangan harus 1, 2, atau 3 tahun.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ]);

        $mitra = $this->mitra($request);
        $username = (string) $mitra['username'];

        if (PermohonanPerpanjangan::terbaruUntuk($username) !== null
            && PermohonanPerpanjangan::terbaruUntuk($username)['status'] === PermohonanPerpanjangan::MENUNGGU) {
            return redirect()
                ->route('mitra.status')
                ->withErrors(['durasi' => 'Permohonan perpanjangan Anda masih menunggu peninjauan admin.']);
        }

        PermohonanPerpanjangan::ajukan(
            $username,
            (string) $mitra['instansi'],
            (int) $validated['durasi'],
            (string) ($validated['catatan'] ?? ''),
            (string) ($mitra['selesai'] ?? ''),
        );

        return redirect()
            ->route('mitra.status')
            ->with('ok', 'Permohonan perpanjangan '.$validated['durasi'].' tahun berhasil dikirim dan menunggu peninjauan admin.');
    }

    public function profil(Request $request): View
    {
        $mitra = $this->mitra($request);

        return view('user.profile', ['mitra' => $mitra, 'bidang' => $this->bidang($mitra)]);
    }

    /**
     * Unggah foto profil. Berkas disimpan di disk privat dengan nama UUID;
     * tampilan diberikan lewat route terkontrol (cek kepemilikan session).
     */
    public function unggahFoto(Request $request): RedirectResponse
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'foto.required' => 'Pilih berkas foto terlebih dahulu.',
            'foto.image' => 'Berkas harus berupa gambar.',
            'foto.mimes' => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
            'foto.uploaded' => 'Foto gagal diunggah. Pastikan ukurannya tidak melebihi 2 MB.',
        ]);

        $file = $request->file('foto');

        // Periksa MIME dari isi berkas, bukan dari nama kiriman klien.
        $mime = (string) $file->getMimeType();
        $peta = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

        if (! array_key_exists($mime, $peta)) {
            return back()->withErrors(['foto' => 'Isi berkas tidak dikenali sebagai gambar JPG, PNG, atau WEBP.']);
        }

        $mitra = $this->mitra($request);
        $lama = $mitra['foto'] ?? null;

        $namaSimpan = (string) Str::uuid().'.'.$peta[$mime];
        Storage::disk('local')->putFileAs('foto', $file, $namaSimpan);

        if (is_string($lama) && $lama !== '' && Storage::disk('local')->exists($lama)) {
            Storage::disk('local')->delete($lama);
        }

        $mitra['foto'] = 'foto/'.$namaSimpan;
        $mitra['foto_versi'] = (string) time();
        $this->simpanMitra($request, $mitra);

        return redirect()
            ->route('mitra.profile')
            ->with('ok', 'Foto profil berhasil diperbarui.');
    }

    /** Tampilkan foto milik mitra yang sedang login. */
    public function foto(Request $request): StreamedResponse
    {
        $mitra = $this->mitra($request);
        $path = $mitra['foto'] ?? null;

        if (! is_string($path) || $path === '' || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $mime = (string) Storage::disk('local')->mimeType($path);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return Storage::disk('local')->response($path, null, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Simpan profil. Field, nama field, dan aturan validasi SAMA dengan
     * versi sebelumnya (routes/web.php) agar perilaku server tidak berubah.
     */
    public function updateProfil(UpdateProfilRequest $request): RedirectResponse
    {
        $existing = $this->mitra($request);

        $updated = array_merge($existing, [
            'instansi' => $request->input('instansi'),
            'organisasi' => $request->input('organisasi'),
            'pic' => $request->input('pic'),
            'jabatan_pic' => $request->input('jabatan_pic', $existing['jabatan_pic'] ?? ''),
            'email' => $request->input('email'),
            'telp' => $request->input('telp'),
            'alamat' => $request->input('alamat'),
        ]);

        // Foto ditangani formulir terpisah (unggahFoto) dengan disk privat.
        $this->simpanMitra($request, $updated);

        return redirect()
            ->route('mitra.profile')
            ->with('ok', 'Profil instansi berhasil diperbarui.');
    }

    public function mou(Request $request): View
    {
        $mitra = $this->mitra($request);

        return view('user.mou', [
            'mitra' => $mitra,
            'daftar' => $this->daftarMou($request),
        ]);
    }

    /**
     * Generator draf MoU. Bidang selalu berasal dari pengajuan milik mitra
     * (email session), bukan dari parameter URL, sehingga mitra tidak bisa
     * memilih template bidang lain.
     */
    public function drafMou(Request $request): View
    {
        $mitra = $this->mitra($request);
        $bidang = $this->bidang($mitra);
        $template = $bidang['kode'] !== '' ? TemplateMou::untuk($bidang['kode']) : null;

        $draf = $template !== null
            ? TemplateMou::render($template, TemplateMou::data($mitra, $bidang['label']))
            : '';

        // Draf yang pernah disimpan mitra ini dimuat kembali saat halaman dibuka.
        $tersimpan = $request->session()->get('mitra_mou_draf');

        if (is_string($tersimpan) && trim($tersimpan) !== '') {
            $draf = $tersimpan;
        }

        return view('user.mou-draf', [
            'mitra' => $mitra,
            'bidang' => $bidang,
            'template' => $template,
            'draf' => $draf,
        ]);
    }

    /**
     * Simpan draf yang sudah disunting, atau kirim ke admin. Bidang tetap
     * diambil dari pengajuan mitra; input bidang dari klien diabaikan.
     */
    public function simpanDrafMou(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'aksi' => 'required|in:simpan,kirim',
            'isi' => 'required|string|max:'.TemplateMou::MAKS_ISI,
        ], [
            'aksi.required' => 'Pilih tindakan untuk draf MoU.',
            'aksi.in' => 'Tindakan draf MoU tidak dikenali.',
            'isi.required' => 'Isi draf MoU wajib diisi.',
            'isi.max' => 'Isi draf MoU maksimal '.TemplateMou::MAKS_ISI.' karakter.',
        ]);

        $mitra = $this->mitra($request);
        $bidang = $this->bidang($mitra);
        $template = $bidang['kode'] !== '' ? TemplateMou::untuk($bidang['kode']) : null;

        if ($template === null) {
            return back()
                ->withInput()
                ->withErrors(['isi' => 'Template MoU untuk bidang kerja sama Anda belum tersedia. Hubungi admin kerja sama.']);
        }

        $teks = TemplateMou::bersihkanDraf((string) $validated['isi']);

        if ($teks === '') {
            return back()
                ->withInput()
                ->withErrors(['isi' => 'Isi draf MoU tidak boleh kosong.']);
        }

        if ($validated['aksi'] === 'simpan') {
            $request->session()->put('mitra_mou_draf', $teks);

            return redirect()
                ->route('mitra.mou.draf')
                ->with('ok', 'Draf MoU disimpan. Anda dapat menyuntingnya lagi sebelum dikirim.');
        }

        $id = (string) Str::uuid();
        $daftar = $this->daftarMou($request);

        array_unshift($daftar, [
            'id' => $id,
            'pemilik' => $mitra['username'],
            'judul' => $template['judul'],
            'nomor' => 'Draf MoU '.$bidang['kode'],
            'keterangan' => 'Draf MoU dari template bidang '.$bidang['kode'].', menunggu peninjauan admin.',
            'status' => 'Menunggu Peninjauan',
            'tanggal' => CarbonImmutable::now(config('app.timezone', 'UTC'))->toDateString(),
            'nama_asli' => null,
            'berkas' => null,
            'ukuran' => 0,
            'mime' => null,
            'bidang' => $bidang['kode'],
            'isi' => [
                ['judul' => 'Isi Draf MoU', 'paragraf' => TemplateMou::paragraf($teks)],
            ],
        ]);

        $request->session()->put('mitra_mou', $daftar);
        $request->session()->forget('mitra_mou_draf');

        return redirect()
            ->route('mitra.mou.detail', $id)
            ->with('ok', 'Draf MoU berhasil dikirim dan menunggu peninjauan admin.');
    }

    /** Simpan berkas MoU di disk privat + metadata di session (demo). */
    public function kirimMou(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:1000',
            'berkas' => 'required|file|mimes:pdf,docx|max:5120',
        ], [
            'judul.required' => 'Judul atau nomor MoU wajib diisi.',
            'judul.max' => 'Judul maksimal 255 karakter.',
            'berkas.required' => 'Berkas MoU wajib diunggah.',
            'berkas.mimes' => 'Berkas harus berformat PDF atau DOCX.',
            'berkas.max' => 'Ukuran berkas maksimal 5 MB.',
            'berkas.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya tidak melebihi 5 MB.',
        ]);

        $file = $request->file('berkas');

        // Periksa MIME dari isi berkas, bukan dari nama/ekstensi kiriman klien.
        $mime = (string) $file->getMimeType();
        $peta = [
            'application/pdf' => 'pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ];

        if (! array_key_exists($mime, $peta)) {
            return back()
                ->withInput($request->only('judul', 'keterangan'))
                ->withErrors(['berkas' => 'Isi berkas tidak dikenali sebagai PDF atau DOCX yang sah.']);
        }

        $ekstensi = $peta[$mime];
        $id = (string) Str::uuid();
        $namaSimpan = $id.'.'.$ekstensi; // nama acak, bukan nama asli

        Storage::disk('local')->putFileAs('mou', $file, $namaSimpan);

        $mitra = $this->mitra($request);
        $daftar = $this->daftarMou($request);

        array_unshift($daftar, [
            'id' => $id,
            'pemilik' => $mitra['username'],
            'judul' => (string) $validated['judul'],
            'nomor' => (string) $validated['judul'],
            'keterangan' => (string) ($validated['keterangan'] ?? ''),
            'status' => 'Menunggu Peninjauan',
            'tanggal' => CarbonImmutable::now(config('app.timezone', 'UTC'))->toDateString(),
            'nama_asli' => $file->getClientOriginalName(),
            'berkas' => 'mou/'.$namaSimpan,
            'ukuran' => (int) $file->getSize(),
            'mime' => $mime,
        ]);

        $request->session()->put('mitra_mou', $daftar);

        return redirect()
            ->route('mitra.mou')
            ->with('ok', 'MoU "'.$validated['judul'].'" berhasil dikirim dan menunggu peninjauan admin.');
    }

    /** Detail satu MoU; MoU milik mitra lain dijawab 404. */
    public function detailMou(Request $request, string $id): View
    {
        $mou = $this->cariMilikSendiri($request, $id);

        return view('user.mou-detail', ['mitra' => $this->mitra($request), 'mou' => $mou]);
    }

    /**
     * Pratinjau PDF sebaris (iframe). Hanya pemilik, hanya PDF, hanya bila
     * berkas ada. Header keamanan global TIDAK dilemahkan: X-Frame-Options
     * tetap SAMEORIGIN sehingga iframe same-origin tetap boleh.
     */
    public function pratinjauMou(Request $request, string $id): StreamedResponse
    {
        $mou = $this->cariMilikSendiri($request, $id);
        $path = $mou['berkas'] ?? null;

        if (! is_string($path) || $path === '' || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        $ekstensi = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        if ($ekstensi !== 'pdf') {
            abort(404);
        }

        $mime = (string) Storage::disk('local')->mimeType($path);
        abort_unless($mime === 'application/pdf', 404);

        return Storage::disk('local')->response($path, null, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /** Unduh terkontrol: hanya pemilik, hanya status Disetujui, hanya bila berkas ada. */
    public function unduhMou(Request $request, string $id): StreamedResponse|RedirectResponse
    {
        $mou = $this->cariMilikSendiri($request, $id);

        if ($mou['status'] !== 'Disetujui' || empty($mou['berkas'])) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($mou['berkas'])) {
            abort(404);
        }

        $namaUnduh = Str::slug(pathinfo((string) $mou['nama_asli'], PATHINFO_FILENAME)) ?: 'mou';
        $ekstensi = pathinfo((string) $mou['berkas'], PATHINFO_EXTENSION);

        return Storage::disk('local')->response(
            $mou['berkas'],
            $namaUnduh.'.'.$ekstensi,
            ['X-Content-Type-Options' => 'nosniff'],
        );
    }

    /** @return array<int, array<string, mixed>> */
    /** @return array<int, array<string, mixed>> */
    private function daftarMou(Request $request): array
    {
        $sesi = $request->session()->get('mitra_mou');

        if (! is_array($sesi)) {
            $sesi = DataDemo::mou($this->mitra($request)['username']);
            $request->session()->put('mitra_mou', $sesi);
        }

        return array_values(array_filter(
            $sesi,
            fn ($row) => is_array($row) && ($row['pemilik'] ?? null) === $this->mitra($request)['username'],
        ));
    }

    /** @return array<string, mixed> */
    private function cariMilikSendiri(Request $request, string $id): array
    {
        foreach ($this->daftarMou($request) as $row) {
            if (($row['id'] ?? null) === $id) {
                return $row;
            }
        }

        abort(404);
    }
}
