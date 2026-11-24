<?php

use App\Http\Controllers\AdminSuperadminController;
use App\Support\AdminAkses;
use App\Support\Mou\StrukturTemplateMou;
use App\Support\PengajuanMasuk;
use App\Support\PermohonanPerpanjangan;
use App\Support\StatusKerjasama;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel Super Administrator — halaman eksklusif
|--------------------------------------------------------------------------
| Dimuat di bootstrap/app.php dengan prefix AdminAkses::path('superadmin')
| dan awalan nama route `superadmin.` (lihat config/admin_akses.php).
|
| Halaman yang dipakai bersama Admin Kerja Sama (inbox, draf proposal, masa
| aktif) ada di routes/admin_kerja.php dan dimuat terpisah untuk tiap peran.
| Berkas ini hanya berisi yang memang milik Superadmin: dashboard ringkasan,
| pengelolaan konten portal, dan alat uji email.
|
| Middleware per halaman dibuat dari peta izin: `admin.only:<peran...>`,
| sehingga peran tanpa kemampuan otomatis 404.
|
| TODO-BACKEND: peran masih dibaca dari config + session, belum tabel `users`.
*/

/** Middleware untuk satu kemampuan menurut config/admin_akses.php. */
$hanya = static fn (string $kemampuan): string => 'admin.only:'.implode(',', AdminAkses::peranUntuk($kemampuan));

/** Kode template MoU yang ada (berkas storage/app/mou/*.json), untuk batas rute. */
$kodeTemplate = array_keys(StrukturTemplateMou::semua());
if ($kodeTemplate === []) {
    $kodeTemplate = ['__tidak_ada__'];
}

// ---------------- DASHBOARD RINGKASAN ----------------
Route::middleware($hanya('dashboard'))->group(function () {

    Route::get('/dashboard', function () {
        return view('admin.dashboard', [
            'menunggu' => array_map(function (array $r) {
                $akhir = StatusKerjasama::parseTanggal($r['selesai_lama'] ?? null);

                return $r + [
                    'selesai_lama_iso' => $akhir?->toDateString() ?? '',
                    'selesai_lama_tampil' => $akhir?->translatedFormat('d F Y') ?? 'Belum tersedia',
                ];
            }, PermohonanPerpanjangan::menunggu()),
            'riwayat' => array_slice(array_values(array_filter(
                PermohonanPerpanjangan::all(),
                fn (array $r) => ($r['status'] ?? null) !== PermohonanPerpanjangan::MENUNGGU
            )), 0, 10),
            'jumlahPengajuanBaru' => count(array_filter(
                PengajuanMasuk::all(),
                fn (array $p) => ($p['status'] ?? null) === 'Menunggu'
            )),
        ]);
    })->name('dashboard');

    Route::post('/dashboard/{id}/setujui', function (Request $request, string $id) {
        $request->validate([
            'durasi' => 'required|integer|in:1,2,3',
        ], [
            'durasi.required' => 'Pilih durasi perpanjangan sebelum menyetujui.',
            'durasi.in' => 'Durasi perpanjangan harus 1, 2, atau 3 tahun.',
        ]);

        $permohonan = PermohonanPerpanjangan::cari($id);

        if ($permohonan === null || ($permohonan['status'] ?? null) !== PermohonanPerpanjangan::MENUNGGU) {
            return back()->with('error', 'Permohonan tidak ditemukan atau sudah diputuskan sebelumnya.');
        }

        // Tanggal berakhir diambil dari data tersimpan, bukan dari input klien.
        $hasil = PermohonanPerpanjangan::setujui($id, (string) ($permohonan['selesai_lama'] ?? ''), (int) $request->input('durasi'));

        if ($hasil === null) {
            return back()->with('error', 'Permohonan tidak ditemukan atau sudah diputuskan sebelumnya.');
        }

        $format = fn (string $tgl) => CarbonImmutable::parse($tgl, config('app.timezone', 'UTC'))->translatedFormat('d F Y');

        return back()->with('ok', "Perpanjangan untuk {$hasil['instansi']} disetujui. Masa aktif baru: "
            .$format($hasil['mulai_baru']).' s/d '.$format($hasil['selesai_baru']).'.');
    })->name('dashboard.setujui');

    Route::post('/dashboard/{id}/tolak', function (Request $request, string $id) {
        $validated = $request->validate([
            'alasan' => 'required|string|min:10|max:1000',
        ], [
            'alasan.required' => 'Alasan penolakan wajib diisi agar mitra memahami keputusan.',
            'alasan.min' => 'Alasan penolakan minimal 10 karakter.',
            'alasan.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $hasil = PermohonanPerpanjangan::tolak($id, (string) $validated['alasan']);

        if ($hasil === null) {
            return back()->with('error', 'Permohonan tidak ditemukan atau sudah diputuskan sebelumnya.');
        }

        return back()->with('ok', "Permohonan perpanjangan dari {$hasil['instansi']} ditolak. Alasan akan tampil di halaman Status mitra.");
    })->name('dashboard.tolak');
});

// ---------------- PROFIL & VISI MISI ----------------
Route::middleware($hanya('tentang'))->group(function () {

    Route::get('/tentang', [AdminSuperadminController::class, 'tentang'])->name('tentang');

    Route::post('/tentang', [AdminSuperadminController::class, 'tentangUpdate'])->name('tentang.update');
});

// ---------------- ARTIKEL ----------------
Route::middleware($hanya('artikel'))->group(function () {

    Route::get('/artikel', [AdminSuperadminController::class, 'artikel'])->name('artikel');

    Route::post('/artikel', [AdminSuperadminController::class, 'artikelStore'])->name('artikel.store');

    Route::post('/artikel/{id}/delete', [AdminSuperadminController::class, 'artikelDelete'])->name('artikel.delete');

    Route::post('/artikel/{id}/edit', [AdminSuperadminController::class, 'artikelEdit'])->name('artikel.edit');
});

// ---------------- GALERI ----------------
Route::middleware($hanya('galeri'))->group(function () {

    Route::get('/galeri', [AdminSuperadminController::class, 'galeri'])->name('galeri');

    Route::post('/galeri', [AdminSuperadminController::class, 'galeriStore'])->name('galeri.store');

    Route::post('/galeri/{id}/delete', [AdminSuperadminController::class, 'galeriDelete'])->name('galeri.delete');

    Route::post('/galeri/{id}/edit', [AdminSuperadminController::class, 'galeriEdit'])->name('galeri.edit');
});

// ---------------- TEMPLATE MOU PER BIDANG ----------------
Route::middleware($hanya('template_mou'))->group(function () use ($kodeTemplate) {
    Route::get('/template-mou', [AdminSuperadminController::class, 'templateMou'])->name('template_mou');

    // Daftar lebih spesifik didaftarkan sebelum wildcard {kode}.
    Route::put('/template-mou/pengaturan', [AdminSuperadminController::class, 'simpanPengaturanMou'])
        ->middleware('throttle:admin-template-mou')
        ->name('template_mou.pengaturan');

    Route::put('/template-mou/pemetaan', [AdminSuperadminController::class, 'simpanPemetaanMou'])
        ->middleware('throttle:admin-template-mou')
        ->name('template_mou.pemetaan');

    // Kode template dibatasi berkas di storage/app/mou; kode lain 404 sebelum controller.
    Route::put('/template-mou/{kode}', [AdminSuperadminController::class, 'simpanTemplateMou'])
        ->whereIn('kode', $kodeTemplate)
        ->middleware('throttle:admin-template-mou')
        ->name('template_mou.update');

    Route::post('/template-mou/{kode}/reset', [AdminSuperadminController::class, 'resetTemplateMou'])
        ->whereIn('kode', $kodeTemplate)
        ->middleware('throttle:admin-template-mou')
        ->name('template_mou.reset');
});

// ---------------- AKTIVITAS ADMIN KERJA SAMA (baca-saja) ----------------
Route::middleware($hanya('aktivitas'))->group(function () {
    Route::get('/aktivitas', [AdminSuperadminController::class, 'aktivitas'])->name('aktivitas');
});

// ---------------- KELOLA PENGGUNA (baca-saja sampai backend ada) ----------------
Route::middleware($hanya('pengguna'))->group(function () {
    Route::get('/pengguna', [AdminSuperadminController::class, 'pengguna'])->name('pengguna');
});

// ---------------- ALAT INTERNAL (lokal saja) ----------------
Route::middleware($hanya('uji_email'))->group(function () {
    Route::get('/test-email', function (Request $request) {
        abort_unless(app()->isLocal(), 404);

        $targetEmail = (string) $request->query('to', config('mail.from.address'));
        try {
            Mail::raw(
                "Email uji coba KEMKOMDIGI Portal Kerja Sama.\nWaktu kirim: ".now()->format('d/m/Y H:i:s')." WIB\nDikirim ke: {$targetEmail}",
                function ($msg) use ($targetEmail): void {
                    $msg->to($targetEmail)->subject('[TEST] Email KEMKOMDIGI - '.now()->format('H:i:s'));
                }
            );

            return response('Email berhasil diproses untuk: '.e($targetEmail).' (driver: '.config('mail.default').')', 200)
                ->header('Content-Type', 'text/html');
        } catch (Throwable $e) {
            return response('GAGAL mengirim email.<br>'.nl2br(e($e->getMessage())), 500)
                ->header('Content-Type', 'text/html');
        }
    })->name('test_email');
});
