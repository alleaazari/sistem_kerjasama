<?php

use App\Http\Controllers\AdminMitraController;
use App\Support\AdminAkses;
use App\Support\PengajuanMasuk;
use App\Support\SuratPenolakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman kerja sama (dipakai kedua peran)
|--------------------------------------------------------------------------
| Dimuat di dalam grup prefix + nama route SETIAP peran yang punya kemampuan
| `inbox`, `draf`, atau `masa_aktif`. Isinya tidak diduplikasi per peran;
| middleware `admin.only:<peran...>` dibuat dari peta izin di
| config/admin_akses.php, jadi peran tanpa kemampuan otomatis 404.
|
| Nama route mengikuti awalan peran di grupnya (superadmin.inbox,
| kerjasama.inbox, ...).
|
| TODO-BACKEND: data masih di berkas JSON (App\Support\PengajuanMasuk,
| App\Support\DrafMitra, App\Support\MasaAktifMitra), belum tabel database.
*/

/** Middleware untuk satu kemampuan menurut config/admin_akses.php. */
$hanya = static fn (string $kemampuan): string => 'admin.only:'.implode(',', AdminAkses::peranUntuk($kemampuan));

// ---------------- INBOX PENGAJUAN ----------------
Route::middleware($hanya('inbox'))->group(function () {

    Route::get('/inbox', function () {
        return view('admin.inbox', ['pengajuan' => PengajuanMasuk::all()]);
    })->name('inbox');

    Route::post('/inbox/{id}/approve', function ($id) {
        $pengajuan = PengajuanMasuk::all();
        $target = null;

        foreach ($pengajuan as &$p) {
            if ((string) ($p['id'] ?? '') === (string) $id) {
                $p['status'] = 'Disetujui';
                $target = $p;
                break;
            }
        }
        unset($p);

        // ID tak dikenal: jangan lapor "sukses" palsu.
        if ($target === null) {
            return back()->with('error', 'Pengajuan tidak ditemukan. Mungkin sudah dihapus.');
        }

        PengajuanMasuk::save($pengajuan);

        return back()->with('ok', 'Pengajuan '.$target['instansi'].' DISETUJUI! Status kemitraan telah aktif. '
            .'Admin dapat mengirimkan kredensial (username & password) ke '.($target['email'] ?? '').' secara terpisah.');
    })->name('inbox.approve');

    Route::post('/inbox/{id}/reject', function (Request $request, $id) {
        $validated = $request->validate([
            'feedback' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'feedback.required' => 'Alasan penolakan wajib diisi.',
            'feedback.min' => 'Alasan penolakan minimal 5 karakter.',
            'feedback.max' => 'Alasan penolakan maksimal 1000 karakter.',
        ]);

        $pengajuan = PengajuanMasuk::all();
        $target = null;

        foreach ($pengajuan as &$p) {
            if ((string) ($p['id'] ?? '') === (string) $id) {
                $p['status'] = 'Ditolak';
                $p['feedback'] = $validated['feedback'];
                $target = $p;
                break;
            }
        }
        unset($p);

        if ($target === null) {
            return back()->with('error', 'Pengajuan tidak ditemukan. Mungkin sudah dihapus.');
        }

        PengajuanMasuk::save($pengajuan);

        $template = SuratPenolakan::susun(
            (string) ($target['pic'] ?? 'Bapak/Ibu PIC'),
            (string) ($target['instansi'] ?? 'Instansi Mitra'),
            (string) ($target['organisasi'] ?? 'Unit Kerja'),
            (string) $validated['feedback'],
        );

        $emailPIC = (string) ($target['email'] ?? '');
        if ($emailPIC !== '') {
            SuratPenolakan::kirimEmail($emailPIC, (string) ($target['pic'] ?? ''), $template['email_subject'], $template['email_body']);
        }

        $telpPIC = (string) ($target['telp'] ?? '');
        $wa = $telpPIC === ''
            ? ['phone' => '', 'wa_url' => '']
            : SuratPenolakan::kirimWhatsApp($telpPIC, $template['wa_text']);

        return back()->with([
            'ok' => 'Pengajuan dari '.$target['instansi'].' telah DITOLAK.',
            'reject_done' => true,
            'instansi' => $target['instansi'],
            'pic' => $target['pic'] ?? '',
            'email_sent' => $emailPIC,
            'wa_sent' => $telpPIC,
            'wa_url' => $wa['wa_url'],
            'wa_phone' => $wa['phone'],
            'alasan' => $validated['feedback'],
            'surat_preview' => $template['email_body'],
        ]);
    })->name('inbox.reject');
});

// ---------------- DRAF PROPOSAL AI ----------------
Route::middleware($hanya('draf'))->group(function () {
    Route::get('/draf-proposal', [AdminMitraController::class, 'draf'])
        ->name('draf_proposal');

    Route::get('/draf-proposal/{uuid}/unduh', [AdminMitraController::class, 'unduhDraf'])
        ->whereUuid('uuid')
        ->name('draf_proposal.unduh');
});

// ---------------- MASA AKTIF MITRA ----------------
Route::middleware($hanya('masa_aktif'))->group(function () {
    Route::get('/masa-aktif', [AdminMitraController::class, 'masaAktif'])
        ->name('masa_aktif');

    Route::put('/masa-aktif', [AdminMitraController::class, 'aturMasaAktif'])
        ->name('masa_aktif.atur');
});
