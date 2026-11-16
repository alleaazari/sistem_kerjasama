<?php

namespace App\Http\Controllers;

use App\Support\LogAktivitas;
use App\Support\PengajuanMasuk;
use App\Support\SuratPenolakan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Inbox pengajuan kerja sama.
 *
 * Satu controller melayani kedua prefix (superadmin & admin_kerjasama) karena
 * logikanya identik; perbedaan hak akses ditentukan middleware `admin.only`
 * dari peta izin di config/admin_akses.php, bukan percabangan di sini.
 *
 * TODO-BACKEND: ganti App\Support\PengajuanMasuk dengan model Eloquent;
 * pengajuan masih memakai ID berurutan, belum UUID sebagai route key.
 */
class AdminInboxController extends Controller
{
    /** Daftar pengajuan yang masuk. */
    public function indeks(): View
    {
        return view('admin.inbox', ['pengajuan' => PengajuanMasuk::all()]);
    }

    /** Setujui satu pengajuan. */
    public function setujui(string $id): RedirectResponse
    {
        $pengajuan = PengajuanMasuk::all();
        $target = null;

        foreach ($pengajuan as &$p) {
            if ((string) ($p['id'] ?? '') === $id) {
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
        LogAktivitas::catat('inbox.approve', $id);

        return back()->with('ok', 'Pengajuan '.$target['instansi'].' DISETUJUI! Status kemitraan telah aktif. '
            .'Admin dapat mengirimkan kredensial (username & password) ke '.($target['email'] ?? '').' secara terpisah.');
    }

    /** Tolak satu pengajuan, lengkap dengan surat resmi dan WhatsApp. */
    public function tolak(Request $request, string $id): RedirectResponse
    {
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
            if ((string) ($p['id'] ?? '') === $id) {
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
        LogAktivitas::catat('inbox.reject', $id);

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
    }
}
