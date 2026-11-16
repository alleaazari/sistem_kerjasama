<?php

namespace App\Http\Controllers;

use App\Support\DrafMitra;
use App\Support\LogAktivitas;
use App\Support\MasaAktifMitra;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Panel admin: draf proposal AI dan masa aktif mitra.
 *
 * Dua fitur dipisah ke route dan view masing-masing. Data masih memakai
 * penyimpanan JSON (mode demo), lihat TODO-BACKEND di App\Support\DrafMitra
 * dan App\Support\MasaAktifMitra.
 */
class AdminMitraController extends Controller
{
    /** Halaman draf proposal AI: saring, urut terbaru, per halaman. */
    public function draf(Request $request): View
    {
        $kata = (string) $request->query('q', '');
        $halaman = (int) $request->query('page', 1);

        $hasil = DrafMitra::halaman(DrafMitra::cariKata($kata), $halaman);

        return view('admin.draf_proposal', [
            'rows' => $hasil['data'],
            'total' => $hasil['total'],
            'halaman' => $hasil['halaman'],
            'halamanAkhir' => $hasil['halaman_akhir'],
            'kata' => $kata,
            'berkasSiap' => DrafMitra::BERKAS_SIAP,
        ]);
    }

    /**
     * Unduh berkas draf secara terkontrol.
     *
     * Backend belum menyimpan berkas draf (App\Support\DrafMitra belum punya
     * kolom berkas), jadi route ini menjawab 404 saat flag mati. Kontraknya
     * sudah final: nama berkas dikirim lewat Content-Disposition.
     */
    public function unduhDraf(string $uuid): StreamedResponse
    {
        if (! DrafMitra::berkasSiap()) {
            abort(404);
        }

        $row = DrafMitra::cari($uuid);
        abort_if($row === null, 404);

        $path = $row['berkas_path'] ?? null;
        abort_unless(is_string($path) && $path !== '', 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        LogAktivitas::catat('draf_proposal.unduh', $uuid);

        $nama = (string) ($row['berkas_nama_asli'] ?? 'draf-proposal.pdf');

        return Storage::disk('local')->download($path, $nama, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /** Halaman masa aktif mitra. */
    public function masaAktif(Request $request): View
    {
        $kata = (string) $request->query('q', '');
        $halaman = (int) $request->query('page', 1);

        $hasil = MasaAktifMitra::halaman(MasaAktifMitra::cariKata($kata), $halaman);

        return view('admin.masa_aktif', [
            'rows' => array_map([MasaAktifMitra::class, 'denganStatus'], $hasil['data']),
            'total' => $hasil['total'],
            'halaman' => $hasil['halaman'],
            'halamanAkhir' => $hasil['halaman_akhir'],
            'kata' => $kata,
        ]);
    }

    /** Perbarui periode masa aktif satu mitra (UUID dikirim lewat form). */
    public function aturMasaAktif(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuid' => ['required', 'uuid'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_berakhir' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'alasan' => ['nullable', 'string', 'max:255'],
        ], [
            'uuid.required' => 'Data masa aktif yang akan diubah tidak dikenali.',
            'uuid.uuid' => 'Penanda masa aktif tidak valid.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.date_format' => 'Format tanggal mulai tidak valid.',
            'tanggal_berakhir.required' => 'Tanggal berakhir wajib diisi.',
            'tanggal_berakhir.date_format' => 'Format tanggal berakhir tidak valid.',
            'tanggal_berakhir.after_or_equal' => 'Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.',
            'alasan.max' => 'Alasan perubahan maksimal 255 karakter.',
        ]);

        $hasil = MasaAktifMitra::perbarui(
            (string) $validated['uuid'],
            (string) $validated['tanggal_mulai'],
            (string) $validated['tanggal_berakhir'],
            (string) ($validated['alasan'] ?? '')
        );

        if ($hasil === null) {
            return back()->with('error', 'Data masa aktif tidak ditemukan. Mungkin sudah dihapus.');
        }

        LogAktivitas::catat('masa_aktif.atur', (string) $hasil['uuid']);

        $format = fn (string $tgl) => CarbonImmutable::parse($tgl, config('app.timezone', 'UTC'))
            ->translatedFormat('d F Y');

        return back()->with('ok', "Masa aktif {$hasil['instansi']} diperbarui: "
            .$format($hasil['tanggal_mulai']).' s/d '.$format($hasil['tanggal_berakhir']).'.');
    }
}
