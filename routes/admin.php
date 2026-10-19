<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

// =====================================================================
// DATA DEFAULT (dipakai jika session kosong / server restart)
// =====================================================================
$defaultPengajuan = [
    ['id' => 1, 'instansi' => 'Pemerintah Provinsi Jawa Barat', 'organisasi' => 'Dinas Komunikasi dan Informatika', 'pic' => 'Rina Wulandari', 'email' => 'rina@jabarprov.go.id', 'telp' => '0812-3456-7890', 'tujuan' => 'Pengembangan layanan publik digital terpadu & SPBE.', 'status' => 'Menunggu'],
    ['id' => 2, 'instansi' => 'Universitas Indonesia', 'organisasi' => 'Direktorat Kerja Sama & Riset', 'pic' => 'Dr. Bagas Pratama', 'email' => 'bagas@ui.ac.id', 'telp' => '0813-1111-2222', 'tujuan' => 'Program magang DTS dan riset literasi digital.', 'status' => 'Disetujui'],
    ['id' => 3, 'instansi' => 'PT Telekomunikasi Nusantara', 'organisasi' => 'Divisi Hubungan Pemerintah', 'pic' => 'Maya Sari', 'email' => 'maya@telenusa.co.id', 'telp' => '0857-9999-0000', 'tujuan' => 'Kolaborasi perluasan infrastruktur jaringan sinyal.', 'status' => 'Ditolak'],
];

$defaultArtikel = [
    ['id' => 1, 'judul' => 'Penandatanganan Nota Kesepahaman Transformasi Digital Daerah', 'tanggal' => '02 Oktober 2026', 'konten' => ''],
    ['id' => 2, 'judul' => 'Kolaborasi Penguatan Literasi Digital Bersama Perguruan Tinggi', 'tanggal' => '24 September 2026', 'konten' => ''],
    ['id' => 3, 'judul' => 'Tata Cara Pengajuan Kerja Sama Instansi Tahun Anggaran 2027', 'tanggal' => '11 September 2026', 'konten' => ''],
];

$defaultGaleri = [
    ['id' => 1, 'judul' => 'Rapat koordinasi kerja sama daerah', 'tanggal' => '02 Oktober 2026', 'konten' => ''],
    ['id' => 2, 'judul' => 'Kunjungan kerja perguruan tinggi', 'tanggal' => '24 September 2026', 'konten' => ''],
    ['id' => 3, 'judul' => 'Pelatihan Digital Talent Scholarship', 'tanggal' => '18 September 2026', 'konten' => ''],
];

$defaultDrafMitra = [
    ['id' => 101, 'instansi' => 'Pemerintah Provinsi Jawa Barat', 'judul_ks' => 'Pelatihan Literasi Digital & SPBE', 'tgl_buat' => '12 Okt 2026', 'masa_aktif' => '12 Okt 2026 s/d 12 Okt 2027', 'status' => 'Aktif'],
    ['id' => 102, 'instansi' => 'Universitas Indonesia', 'judul_ks' => 'Magang DTS & Riset AI', 'tgl_buat' => '05 Okt 2026', 'masa_aktif' => '05 Okt 2026 s/d 05 Okt 2028', 'status' => 'Aktif'],
];

// =====================================================================
// HELPER (diberi function_exists agar tidak bentrok dengan routes/web.php)
// =====================================================================
if (! function_exists('getSharedPengajuanList')) {
    function getSharedPengajuanList(array $defaultPengajuan): array
    {
        $filePath = storage_path('app/storage_pengajuan.json');
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            if (is_array($data) && count($data) > 0) {
                return $data;
            }
        }
        file_put_contents($filePath, json_encode($defaultPengajuan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $defaultPengajuan;
    }
}

if (! function_exists('saveSharedPengajuanList')) {
    function saveSharedPengajuanList(array $data): void
    {
        file_put_contents(
            storage_path('app/storage_pengajuan.json'),
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}

if (! function_exists('buatTemplatePermintaanMaaf')) {
    function buatTemplatePermintaanMaaf(string $namaPIC, string $instansi, string $organisasi, string $alasan): array
    {
        $nomorSurat = 'B-' . rand(100, 999) . '/KOMDIGI/BPSDM.03/' . date('m/Y');
        $tahun = date('Y');

        $emailSubject = "[KEMKOMDIGI] Pemberitahuan Hasil Evaluasi Pengajuan Kerja Sama - {$instansi}";

        $emailBody = <<<EOT
KEMENTERIAN KOMUNIKASI DAN DIGITAL REPUBLIK INDONESIA
BADAN PENGEMBANGAN SUMBER DAYA MANUSIA KOMUNIKASI DAN DIGITAL
Jalan Medan Merdeka Barat No. 9, Jakarta Pusat 10110 | www.komdigi.go.id
--------------------------------------------------------------------------------

Nomor    : {$nomorSurat}
Sifat    : Biasa
Lampiran : -
Perihal  : Pemberitahuan Hasil Seleksi & Permohonan Maaf Pengajuan Kerja Sama

Kepada Yth.
Bapak/Ibu {$namaPIC}
{$organisasi} - {$instansi}
Di Tempat

Dengan hormat,

Terlebih dahulu kami atas nama Kementerian Komunikasi dan Digital Republik Indonesia (KEMKOMDIGI) menyampaikan apresiasi yang setinggi-tingginya dan terima kasih yang tulus atas inisiatif, minat, dan kepercayaan Bapak/Ibu dalam mengajukan proposal kerja sama kemitraan strategis pengembangan kapasitas digital bersama kementerian kami.

Setelah melalui tahapan penelaahan dan evaluasi komprehensif oleh Tim Komite Penilai Kemitraan KEMKOMDIGI dengan mempertimbangkan ketersediaan kuota program, keselarasan kurikulum teknis, serta prioritas strategis agenda nasional tahun anggaran {$tahun}, dengan rasa penyesalan yang mendalam dan permohonan maaf kami sampaikan bahwa:

Proposal usulan kerja sama dari {$instansi} saat ini BELUM DAPAT KAMI SETUJUI / AKOMODASI untuk periode ini.

Catatan Evaluasi / Alasan Penolakan:
"{$alasan}"

Kami memohon maaf yang sebesar-besarnya atas ketidaknyamanan ini serta belum dapat memenuhi harapan Bapak/Ibu pada kesempatan kali ini. Keputusan ini semata-mata didasarkan pada keterbatasan kuota kemitraan terverifikasi dan penyesuaian alokasi fokus program tahun ini.

KEMKOMDIGI tetap membuka kesempatan seluas-luasnya bagi {$instansi} untuk mengajukan kembali proposal kemitraan pada pembukaan gelombang berikutnya setelah memperhatikan catatan evaluasi di atas.

Demikian surat pemberitahuan dan permohonan maaf ini kami sampaikan. Atas pengertian, kesabaran, dan dedikasi Bapak/Ibu dalam mendukung ekosistem digital nasional, kami haturkan terima kasih.

Hormat kami,
Sekretariat Kemitraan & Kerja Sama Digital
Kementerian Komunikasi dan Digital Republik Indonesia (KEMKOMDIGI)
Call Center : 159 | Email: kerjasama@komdigi.go.id | Portal: http://127.0.0.1:8000

EOT;

        $waText = <<<EOT
\u{1F3DB}\u{FE0F} *KEMENTERIAN KOMUNIKASI DAN DIGITAL REPUBLIK INDONESIA*
_Pemberitahuan Resmi Hasil Evaluasi Pengajuan Kemitraan_
--------------------------------------------------------

Yth. *Bapak/Ibu {$namaPIC}*
{$instansi}

Dengan hormat,
Terima kasih atas inisiatif pengajuan proposal kerja sama yang telah dikirimkan kepada KEMKOMDIGI.

Melalui pesan ini, kami menyampaikan *permohonan maaf yang sebesar-besarnya* bahwa setelah evaluasi berkas oleh Tim Verifikasi, pengajuan kerja sama *{$instansi}* saat ini *belum dapat kami setujui* untuk periode program tahun {$tahun}.

\u{1F4CC} *Alasan / Catatan Penolakan:*
_{$alasan}_

Salinan surat resmi dan penjelasan terperinci telah kami kirimkan ke email resmi Anda. Anda dipersilakan untuk mengajukan kembali proposal kemitraan pada gelombang program berikutnya.

Kami sangat mengapresiasi semangat dan kontribusi Bapak/Ibu dalam pemajuan teknologi digital Indonesia.

Salam hormat,
*Tim Layanan Kerja Sama KEMKOMDIGI RI*
\u{1F310} Website: https://komdigi.go.id | \u{1F4DE} Hotline: 159
EOT;

        return [
            'email_subject' => $emailSubject,
            'email_body'    => $emailBody,
            'wa_text'       => $waText,
        ];
    }
}

if (! function_exists('kirimNotifikasiWhatsApp')) {
    function kirimNotifikasiWhatsApp(string $nomorTelepon, string $pesan): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $nomorTelepon);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        $waUrl = 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . urlencode($pesan);

        $logDir = storage_path('logs');
        if (! file_exists($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logEntry  = '[' . date('Y-m-d H:i:s') . "] WHATSAPP TERKIRIM KE: {$cleanPhone} ({$nomorTelepon})\n";
        $logEntry .= "PESAN:\n{$pesan}\nLINK WA WEB:\n{$waUrl}\n";
        $logEntry .= "--------------------------------------------------------------------------------\n";
        @file_put_contents($logDir . '/whatsapp_terkirim.log', $logEntry, FILE_APPEND);

        $gatewayUrl = env('WA_GATEWAY_URL');
        $gatewayKey = env('WA_GATEWAY_KEY');
        if ($gatewayUrl && $gatewayKey) {
            try {
                @file_get_contents($gatewayUrl, false, stream_context_create([
                    'http' => [
                        'method'  => 'POST',
                        'header'  => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: {$gatewayKey}\r\n",
                        'content' => http_build_query(['target' => $cleanPhone, 'message' => $pesan]),
                        'timeout' => 4,
                    ],
                ]));
            } catch (\Throwable $e) {
            }
        }

        return ['phone' => $cleanPhone, 'wa_url' => $waUrl];
    }
}

if (! function_exists('kirimEmailPenolakanResmi')) {
    function kirimEmailPenolakanResmi(string $targetEmail, string $targetPIC, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($msg) use ($targetEmail, $targetPIC, $subject) {
                $msg->to($targetEmail, $targetPIC)
                    ->from(config('mail.from.address', 'kerjasama@komdigi.go.id'), 'KEMKOMDIGI - Portal Kerja Sama')
                    ->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::error('Email penolakan gagal: ' . $e->getMessage());
        }

        $logDir = storage_path('logs');
        if (! file_exists($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        $logEntry  = '[' . date('Y-m-d H:i:s') . "] EMAIL REJECT SENT TO: {$targetEmail} (PIC: {$targetPIC})\n";
        $logEntry .= "SUBJECT: {$subject}\nBODY:\n{$body}\n";
        $logEntry .= "--------------------------------------------------------------------------------\n";
        @file_put_contents($logDir . '/email_terkirim.log', $logEntry, FILE_APPEND);
    }
}

// =====================================================================
// LOGIN ADMIN (publik) - akun dummy ada di config/admin_demo.php
// =====================================================================
// Akses root path rahasia langsung lempar ke inbox (pakai nama route agar ikut prefix)
Route::get('/', function () {
    if (session()->has('admin_auth')) {
        return redirect()->route('admin.inbox');
    }
    return redirect()->route('admin.login');
});

Route::get('/login', function () {
    if (session()->has('admin_auth')) {
        return redirect()->route('admin.inbox');
    }
    return view('admin.login');
})->name('admin.login');

Route::post('/login', function (Request $request) {
    $id = trim((string) ($request->input('email') ?? $request->input('username') ?? $request->input('login') ?? ''));
    $pw = (string) $request->input('password', '');

    foreach (config('admin_demo.accounts', []) as $acc) {
        $idCocok = strcasecmp($id, $acc['email']) === 0 || strcasecmp($id, $acc['username']) === 0;
        if ($idCocok && hash_equals($acc['password'], $pw)) {
            $request->session()->regenerate();
            session(['admin_auth' => ['name' => $acc['name'], 'role' => $acc['role']]]);
            return redirect()->route('admin.inbox');
        }
    }

    return back()
        ->withInput($request->only('email', 'username', 'login'))
        ->with('login_error', 'Username atau password salah.');
})->middleware('throttle:5,1')->name('admin.login.post');

Route::post('/logout', function (Request $request) {
    session()->forget(['admin_auth', 'admin_artikel', 'admin_galeri']);
    $request->session()->regenerateToken();
    return redirect()->route('admin.login');
})->name('admin.logout');

// =====================================================================
// SEMUA HALAMAN DI BAWAH INI HANYA UNTUK ADMIN / DEVELOPER
// =====================================================================
Route::middleware('admin.only')->group(function () use (
    $defaultPengajuan, $defaultArtikel, $defaultGaleri, $defaultDrafMitra
) {

    // TEST EMAIL: khusus role developer
    Route::get('/test-email', function (Request $request) {
        $targetEmail = $request->query('to', config('mail.from.address'));
        try {
            Mail::raw(
                "Email uji coba KEMKOMDIGI Portal Kerja Sama.\nWaktu kirim: " . now()->format('d/m/Y H:i:s') . " WIB\nDikirim ke: {$targetEmail}",
                function ($msg) use ($targetEmail) {
                    $msg->to($targetEmail)->subject('[TEST] Email KEMKOMDIGI - ' . now()->format('H:i:s'));
                }
            );
            return response('Email berhasil diproses untuk: ' . e($targetEmail) . ' (driver: ' . config('mail.default') . ')', 200)
                ->header('Content-Type', 'text/html');
        } catch (\Throwable $e) {
            return response('GAGAL mengirim email.<br>' . nl2br(e($e->getMessage())), 500)
                ->header('Content-Type', 'text/html');
        }
    })->middleware('admin.only:developer')->name('admin.test.email');

    // ---------------- INBOX PENGAJUAN ----------------
    Route::get('/inbox', function () use ($defaultPengajuan) {
        return view('admin.inbox', ['pengajuan' => getSharedPengajuanList($defaultPengajuan)]);
    })->name('admin.inbox');

    Route::post('/inbox/{id}/approve', function (Request $request, $id) use ($defaultPengajuan) {
        $pengajuan = getSharedPengajuanList($defaultPengajuan);
        $targetEmail = '';
        $targetInstansi = '';

        foreach ($pengajuan as &$p) {
            if ((string) $p['id'] === (string) $id) {
                $p['status']    = 'Disetujui';
                $targetEmail    = $p['email'] ?? '';
                $targetInstansi = $p['instansi'] ?? '';
                break;
            }
        }
        unset($p);

        saveSharedPengajuanList($pengajuan);

        return back()->with('ok', "Pengajuan {$targetInstansi} DISETUJUI! Status kemitraan telah aktif. Admin dapat mengirimkan kredensial (username & password) ke {$targetEmail} secara terpisah.");
    })->name('admin.inbox.approve');

    Route::post('/inbox/{id}/reject', function (Request $request, $id) use ($defaultPengajuan) {
        $request->validate(['feedback' => 'required|string|min:5']);

        $pengajuan = getSharedPengajuanList($defaultPengajuan);
        $feedback  = $request->input('feedback');

        $targetPIC = $targetEmail = $targetInstansi = $targetOrganisasi = $targetTelp = '';

        foreach ($pengajuan as &$p) {
            if ((string) $p['id'] === (string) $id) {
                $p['status']      = 'Ditolak';
                $p['feedback']    = $feedback;
                $targetPIC        = $p['pic'] ?? 'Bapak/Ibu PIC';
                $targetEmail      = $p['email'] ?? '';
                $targetInstansi   = $p['instansi'] ?? 'Instansi Mitra';
                $targetOrganisasi = $p['organisasi'] ?? 'Unit Kerja';
                $targetTelp       = $p['telp'] ?? '';
                break;
            }
        }
        unset($p);

        saveSharedPengajuanList($pengajuan);

        $template = buatTemplatePermintaanMaaf($targetPIC, $targetInstansi, $targetOrganisasi, $feedback);

        if ($targetEmail) {
            kirimEmailPenolakanResmi($targetEmail, $targetPIC, $template['email_subject'], $template['email_body']);
        }

        $waRes = ['wa_url' => '', 'phone' => ''];
        if ($targetTelp) {
            $waRes = kirimNotifikasiWhatsApp($targetTelp, $template['wa_text']);
        }

        return back()->with([
            'ok'            => "Pengajuan dari {$targetInstansi} telah DITOLAK.",
            'reject_done'   => true,
            'instansi'      => $targetInstansi,
            'pic'           => $targetPIC,
            'email_sent'    => $targetEmail,
            'wa_sent'       => $targetTelp,
            'wa_url'        => $waRes['wa_url'],
            'wa_phone'      => $waRes['phone'],
            'alasan'        => $feedback,
            'surat_preview' => $template['email_body'],
        ]);
    })->name('admin.inbox.reject');

    // ---------------- ARTIKEL ----------------
    Route::get('/artikel', function () use ($defaultArtikel) {
        return view('admin.artikel', ['rows' => session('admin_artikel', $defaultArtikel)]);
    })->name('admin.artikel');

    Route::post('/artikel', function (Request $request) use ($defaultArtikel) {
        $request->validate(['judul' => 'required|string|max:255']);
        $rows = session('admin_artikel', $defaultArtikel);
        $rows[] = [
            'id'      => time() . rand(10, 99),
            'judul'   => $request->input('judul'),
            'konten'  => $request->input('konten', ''),
            'tanggal' => now()->translatedFormat('d F Y'),
        ];
        session(['admin_artikel' => array_values($rows)]);
        return back()->with('ok', 'Artikel "' . $request->judul . '" berhasil dipublikasikan.');
    })->name('admin.artikel.store');

    Route::post('/artikel/{id}/delete', function ($id) use ($defaultArtikel) {
        $rows = session('admin_artikel', $defaultArtikel);
        $rows = array_values(array_filter($rows, fn ($r) => (string) $r['id'] !== (string) $id));
        session(['admin_artikel' => $rows]);
        return back()->with('ok', 'Artikel berhasil dihapus.');
    })->name('admin.artikel.delete');

    Route::post('/artikel/{id}/edit', function (Request $request, $id) use ($defaultArtikel) {
        $request->validate(['judul' => 'required|string|max:255']);
        $rows = session('admin_artikel', $defaultArtikel);
        foreach ($rows as &$r) {
            if ((string) $r['id'] === (string) $id) {
                $r['judul']  = $request->input('judul');
                $r['konten'] = $request->input('konten', $r['konten'] ?? '');
                break;
            }
        }
        unset($r);
        session(['admin_artikel' => $rows]);
        return back()->with('ok', 'Artikel berhasil diperbarui.');
    })->name('admin.artikel.edit');

    // ---------------- GALERI ----------------
    Route::get('/galeri', function () use ($defaultGaleri) {
        return view('admin.galeri', ['rows' => session('admin_galeri', $defaultGaleri)]);
    })->name('admin.galeri');

    Route::post('/galeri', function (Request $request) use ($defaultGaleri) {
        $request->validate([
            'judul'  => 'required|string|max:255',
            'gambar' => 'nullable|image|max:2048',
        ]);
        $rows = session('admin_galeri', $defaultGaleri);

        $gambarBase64 = null;
        if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
            $file = $request->file('gambar');
            $gambarBase64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        $rows[] = [
            'id'      => time() . rand(10, 99),
            'judul'   => $request->input('judul'),
            'konten'  => $request->input('konten', ''),
            'tanggal' => now()->translatedFormat('d F Y'),
            'gambar'  => $gambarBase64,
        ];

        session(['admin_galeri' => array_values($rows)]);
        return back()->with('ok', 'Foto "' . $request->judul . '" berhasil dipublikasikan.');
    })->name('admin.galeri.store');

    Route::post('/galeri/{id}/delete', function ($id) use ($defaultGaleri) {
        $rows = session('admin_galeri', $defaultGaleri);
        $rows = array_values(array_filter($rows, fn ($r) => (string) $r['id'] !== (string) $id));
        session(['admin_galeri' => $rows]);
        return back()->with('ok', 'Foto galeri berhasil dihapus.');
    })->name('admin.galeri.delete');

    Route::post('/galeri/{id}/edit', function (Request $request, $id) use ($defaultGaleri) {
        $request->validate([
            'judul'  => 'required|string|max:255',
            'gambar' => 'nullable|image|max:2048',
        ]);
        $rows = session('admin_galeri', $defaultGaleri);
        foreach ($rows as &$r) {
            if ((string) $r['id'] === (string) $id) {
                $r['judul']  = $request->input('judul');
                $r['konten'] = $request->input('konten', $r['konten'] ?? '');
                if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
                    $file = $request->file('gambar');
                    $r['gambar'] = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
                }
                break;
            }
        }
        unset($r);
        session(['admin_galeri' => $rows]);
        return back()->with('ok', 'Foto galeri berhasil diperbarui.');
    })->name('admin.galeri.edit');

    // ---------------- TENTANG & MITRA DRAF ----------------
    Route::view('/tentang', 'admin.tentang')->name('admin.tentang');

    Route::post('/tentang', function (Request $request) {
        return back()->with('ok', 'Profil Petinggi & Visi Misi Komdigi berhasil diperbarui.');
    })->name('admin.tentang.update');

    Route::get('/mitra-draf', function () use ($defaultDrafMitra) {
        return view('admin.mitra_draf', ['rows' => session('admin_draf_mitra', $defaultDrafMitra)]);
    })->name('admin.mitra_draf');
});