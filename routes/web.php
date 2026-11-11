<?php

use App\Http\Controllers\MitraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

// Default data mitra (dipakai jika belum ada session / belum ada database)
// DATA DUMMY terpusat ada di App\Support\DataDemo (dipakai MitraController).
$mitraDefault = [
    'username' => 'user1',
    'instansi' => 'Pemerintah Provinsi Jawa Barat',
    'organisasi' => 'Dinas Komunikasi dan Informatika (Diskominfo)',
    'pic' => 'Rina Wulandari, S.STP., M.Si.',
    'email' => 'rina@jabarprov.go.id',
    'telp' => '0812-3456-7890',
    'alamat' => 'Jl. Diponegoro No. 22, Bandung, Jawa Barat',
    'foto' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=200&auto=format&fit=crop',
    'status' => 'AKTIF',
    'mulai' => '12 Okt 2026',
    'selesai' => '12 Okt 2027',
];

// Halaman publik
Route::view('/', 'landing')->name('landing');
Route::view('/pengajuan', 'pengajuan')->name('pengajuan.create');
Route::post('/pengajuan', function (Request $request) {
    $request->validate([
        'instansi' => 'required|string|max:255',
        'jenis_instansi' => 'required|string',
        'organisasi' => 'required|string|max:255',
        'kota' => 'required|string|max:100',
        'provinsi' => 'required|string|max:100',
        'alamat' => 'required|string',
        'pic' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'telp' => 'required|string|max:30',
        'tujuan_mitra' => 'required|string',
        'metode_pelatihan' => 'required|string',
        'tujuan_pelatihan' => 'required|string',
        'pembiayaan' => 'required|string',
    ], [
        'instansi.required' => 'Nama Perguruan Tinggi / Instansi wajib diisi.',
        'jenis_instansi.required' => 'Jenis instansi wajib dipilih.',
        'organisasi.required' => 'Unit Kerja / Divisi / Fakultas wajib diisi.',
        'kota.required' => 'Kota / Kabupaten wajib diisi.',
        'provinsi.required' => 'Provinsi wajib dipilih.',
        'alamat.required' => 'Alamat lengkap instansi wajib diisi.',
        'pic.required' => 'Nama lengkap PIC wajib diisi.',
        'email.required' => 'Email resmi PIC wajib diisi.',
        'email.email' => 'Format email PIC tidak valid.',
        'telp.required' => 'Nomor telepon / WhatsApp PIC wajib diisi.',
        'tujuan_mitra.required' => 'Tujuan pengajuan dari mitra wajib diisi.',
        'metode_pelatihan.required' => 'Metode pelatihan wajib dipilih.',
        'tujuan_pelatihan.required' => 'Tujuan pelatihan wajib diisi.',
        'pembiayaan.required' => 'Skema pembiayaan (APBD / APBN / Cost Sharing) wajib dipilih.',
    ]);

    // Simpan ke storage_pengajuan.json agar admin di port 8001 langsung melihatnya
    $filePath = storage_path('app/storage_pengajuan.json');
    $list = file_exists($filePath) ? json_decode(file_get_contents($filePath), true) : [];
    if (! is_array($list)) {
        $list = [];
    }

    // Buat ID baru
    $newId = 1;
    foreach ($list as $item) {
        if (isset($item['id']) && $item['id'] >= $newId) {
            $newId = $item['id'] + 1;
        }
    }

    $tujuanUtama = $request->input('tujuan_mitra', $request->input('tujuan_pelatihan', 'Pengembangan Talenta & Transformasi Digital'));

    $newItem = [
        'id' => $newId,
        'instansi' => $request->input('instansi'),
        'organisasi' => $request->input('organisasi'),
        'pic' => $request->input('pic'),
        'email' => $request->input('email'),
        'telp' => $request->input('telp'),
        'tujuan' => $tujuanUtama,
        'tujuan_mitra' => $request->input('tujuan_mitra'),
        'tujuan_pelatihan' => $request->input('tujuan_pelatihan'),
        'metode_pelatihan' => $request->input('metode_pelatihan'),
        'pembiayaan' => $request->input('pembiayaan'),
        'bidang' => $request->input('bidang', []),
        'jenis_instansi' => $request->input('jenis_instansi'),
        'kota' => $request->input('kota'),
        'provinsi' => $request->input('provinsi'),
        'alamat' => $request->input('alamat'),
        'status' => 'Menunggu',
        'tanggal' => date('d F Y'),
        'feedback' => '',
    ];

    array_unshift($list, $newItem);
    file_put_contents($filePath, json_encode($list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    return redirect()->route('pengajuan.create')
        ->with('ok', 'Pengajuan kerja sama dari '.$request->instansi.' berhasil dikirim! Tim KEMKOMDIGI akan memverifikasi berkas dan menghubungi PIC ('.$request->pic.') melalui Email ('.$request->email.') dan WhatsApp ('.$request->telp.').');
})->middleware('throttle:pengajuan')->name('pengajuan.store');

// Login mitra: tetap dapat diakses lewat URL langsung (/login). Tidak ada
// tautan ke halaman ini dari homepage (lihat R1 pada laporan).
Route::view('/login', 'login')->name('login');
Route::post('/login', fn () => redirect()->route('mitra.status'))->middleware('throttle:mitra-login')->name('login.post');
Route::post('/logout', function () {
    session()->forget(['mitra_data', 'mitra_mou', 'mitra_perpanjangan']);

    return redirect()->route('landing');
})->name('logout');

// Area mitra: tepat tiga fitur (Status, Profil, MoU).
Route::get('/mitra', [MitraController::class, 'status'])->name('mitra.status');
Route::post('/mitra/perpanjangan', [MitraController::class, 'ajukanPerpanjangan'])
    ->middleware('throttle:mitra-profile')->name('mitra.status.perpanjang');

Route::get('/mitra/profile', [MitraController::class, 'profil'])->name('mitra.profile');
Route::post('/mitra/profile', [MitraController::class, 'updateProfil'])
    ->middleware('throttle:mitra-profile')->name('mitra.profile.update');

Route::get('/mitra/mou', [MitraController::class, 'mou'])->name('mitra.mou');
Route::post('/mitra/mou', [MitraController::class, 'kirimMou'])
    ->middleware('throttle:mitra-profile')->name('mitra.mou.kirim');
Route::get('/mitra/mou/{id}', [MitraController::class, 'detailMou'])->name('mitra.mou.detail');
Route::get('/mitra/mou/{id}/berkas', [MitraController::class, 'pratinjauMou'])->name('mitra.mou.berkas');
Route::get('/mitra/mou/{id}/unduh', [MitraController::class, 'unduhMou'])->name('mitra.mou.unduh');
Route::post('/mitra/profile/foto', [MitraController::class, 'unggahFoto'])
    ->middleware('throttle:mitra-profile')->name('mitra.profile.foto.unggah');
Route::get('/mitra/profile/foto', [MitraController::class, 'foto'])->name('mitra.profile.foto');

// Fitur lama yang dikeluarkan dari menu & area mitra: dialihkan ke Status.
// Berkas view (user/draf.blade.php) sengaja TIDAK dihapus.
Route::redirect('/mitra/draf', '/mitra')->name('mitra.draf');

// API Proxy PDDIKTI Real-Time
Route::get('/api/pddikti', function (Request $request) {
    $q = trim((string) $request->query('q', ''));
    if (strlen($q) < 2) {
        return response()->json([]);
    }

    $cacheKey = 'pddikti:'.md5(Str::lower($q));

    $results = Cache::remember($cacheKey, now()->addHours(6), function () use ($q) {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => config('app.name').' Kerja Sama ('.config('app.url').')',
                    'Accept' => 'application/json, text/plain, */*',
                ])
                ->get('https://api-frontend.kemdikbud.go.id/hit_mhs_token/univ/'.urlencode($q));

            if (! $response->successful()) {
                return [];
            }

            $data = $response->json();
            if (! is_array($data)) {
                return [];
            }

            $mapped = [];
            foreach (array_slice($data, 0, 12) as $item) {
                $mapped[] = [
                    'id' => $item['id'] ?? $item['kode_pt'] ?? '',
                    'nama' => $item['nama'] ?? $item['nama_pt'] ?? $item['text'] ?? '',
                    'kota' => $item['kota'] ?? $item['kabkota'] ?? '',
                    'provinsi' => $item['provinsi'] ?? $item['nm_prov'] ?? '',
                    'jenis' => $item['jenis'] ?? $item['bentuk_pt'] ?? 'Perguruan Tinggi',
                ];
            }

            return $mapped;
        } catch (Throwable $th) {
            // Fallback gracefully if endpoint unreachable
            return [];
        }
    });

    if (count($results) > 0) {
        return response()->json(['source' => 'API PDDIKTI Kemdikbud Live', 'data' => $results]);
    }

    return response()->json(['source' => 'Fallback API', 'data' => []]);
})->middleware('throttle:pddikti');
