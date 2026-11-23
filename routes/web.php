<?php

use App\Http\Controllers\MitraController;
use App\Http\Requests\StorePengajuanRequest;
use App\Support\LandingCms;
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
Route::post('/pengajuan', function (StorePengajuanRequest $request) {
    // Simpan ke storage_pengajuan.json agar admin di port 8001 langsung melihatnya
    $filePath = storage_path('app/storage_pengajuan.json');
    $list = file_exists($filePath) ? json_decode((string) file_get_contents($filePath), true) : [];
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

    $newItem = [
        'id' => $newId,
        'instansi' => $request->input('instansi'),
        'organisasi' => $request->input('organisasi'),
        'pic' => $request->input('pic'),
        'email' => $request->input('email'),
        'telp' => $request->input('telp'),
        'metode_pelatihan' => $request->input('metode_pelatihan'),
        'pembiayaan' => $request->input('pembiayaan'),
        'bidang' => $request->input('bidang'),
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

// Halaman detail artikel (publik)
Route::get('/berita/{slug}', function (string $slug) {
    $cmsAll = app(LandingCms::class)->all();
    $artikelCms = $cmsAll['artikel'] ?? [];

    if (empty($artikelCms)) {
        // fallback default
        $artikelCms = config('landing_cms.artikel', []);
    }

    $ditemukan = null;
    foreach ($artikelCms as $a) {
        if (Str::slug($a['judul'] ?? '') === $slug) {
            $ditemukan = $a;
            break;
        }
    }

    abort_unless($ditemukan !== null, 404);

    // Buat daftar artikel (untuk navigasi "artikel lain")
    $daftar = array_map(fn ($a) => [
        'slug' => Str::slug($a['judul'] ?? ''),
        'judul' => $a['judul'] ?? '',
        'tanggal' => $a['tanggal'] ?? '',
        'gambar' => $a['gambar'] ?? '',
    ], $artikelCms);

    return view('berita-detail', [
        'artikel' => $ditemukan,
        'daftar' => $daftar,
    ]);
})->name('berita.detail');

// Login mitra: tetap dapat diakses lewat URL langsung (/login). Tidak ada
// tautan ke halaman ini dari homepage (lihat R1 pada laporan).
Route::view('/login', 'login')->name('login');
Route::post('/login', function (Request $request) {
    $data = $request->validate([
        'username' => ['required', 'string', 'max:255'],
        'password' => ['required', 'string', 'max:255'],
    ], [
        'username.required' => 'Nama pengguna wajib diisi.',
        'password.required' => 'Kata sandi wajib diisi.',
    ]);

    // Mode demo: satu akun mitra dari config/mitra.php. Akun peran panel
    // (admin) tidak dilayani di pintu ini; pintunya terpisah.
    $cocok = Str::lower(trim($data['username'])) === Str::lower(trim((string) config('mitra.demo_user')))
        && hash_equals((string) config('mitra.demo_password'), (string) $data['password']);

    if (! $cocok) {
        return back()
            ->withInput($request->only('username'))
            ->with('login_error', 'Nama pengguna atau kata sandi tidak sesuai.');
    }

    $request->session()->regenerate();
    $request->session()->put('mitra_data', ['username' => (string) config('mitra.demo_user')]);

    return redirect()->intended(route('mitra.status'));
})->middleware('throttle:mitra-login')->name('login.post');
Route::post('/logout', function (Request $request) {
    $request->session()->forget(['mitra_data', 'mitra_mou']);
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('landing');
})->name('logout');

// Area mitra: tepat tiga fitur (Status, Profil, MoU). Seluruh route di sini
// dijaga `mitra.only`: belum masuk -> login mitra, sesi admin -> 404.
Route::middleware('mitra.only')->group(function () {
    Route::get('/mitra', [MitraController::class, 'status'])->name('mitra.status');
    Route::post('/mitra/perpanjangan', [MitraController::class, 'ajukanPerpanjangan'])
        ->middleware('throttle:mitra-profile')->name('mitra.status.perpanjang');

    Route::get('/mitra/profile', [MitraController::class, 'profil'])->name('mitra.profile');
    Route::post('/mitra/profile', [MitraController::class, 'updateProfil'])
        ->middleware('throttle:mitra-profile')->name('mitra.profile.update');

    Route::get('/mitra/mou', [MitraController::class, 'mou'])->name('mitra.mou');
    Route::post('/mitra/mou', [MitraController::class, 'kirimMou'])
        ->middleware('throttle:mitra-profile')->name('mitra.mou.kirim');
    Route::get('/mitra/mou/draf', [MitraController::class, 'drafMou'])->name('mitra.mou.draf');
    Route::post('/mitra/mou/draf', [MitraController::class, 'simpanDrafMou'])
        ->middleware('throttle:mou-draf')->name('mitra.mou.draf.simpan');

    // Generator MoU berbasis blok: "Buat MoU" -> lengkapi data -> sunting/kirim.
    Route::get('/mitra/mou/buat', [MitraController::class, 'buatMou'])->name('mitra.mou.buat');
    Route::post('/mitra/mou/buat', [MitraController::class, 'simpanMouBaru'])
        ->middleware('throttle:mou-generate')->name('mitra.mou.buat.simpan');
    Route::get('/mitra/mou/buat/{id}', [MitraController::class, 'lihatMou'])->name('mitra.mou.buat.lihat');
    Route::post('/mitra/mou/buat/{id}', [MitraController::class, 'simpanMou'])
        ->middleware('throttle:mou-generate')->name('mitra.mou.buat.update');
    Route::get('/mitra/mou/buat/{id}/cetak', [MitraController::class, 'pratinjauMouMitra'])->name('mitra.mou.buat.cetak');
    Route::get('/mitra/mou/{id}', [MitraController::class, 'detailMou'])->name('mitra.mou.detail');
    Route::get('/mitra/mou/{id}/berkas', [MitraController::class, 'pratinjauMou'])->name('mitra.mou.berkas');
    Route::get('/mitra/mou/{id}/unduh', [MitraController::class, 'unduhMou'])->name('mitra.mou.unduh');
    Route::post('/mitra/profile/foto', [MitraController::class, 'unggahFoto'])
        ->middleware('throttle:mitra-profile')->name('mitra.profile.foto.unggah');
    Route::get('/mitra/profile/foto', [MitraController::class, 'foto'])->name('mitra.profile.foto');

    // Fitur lama yang dikeluarkan dari menu & area mitra: dialihkan ke Status.
    // Berkas view (user/draf.blade.php) sengaja TIDAK dihapus.
    Route::redirect('/mitra/draf', '/mitra')->name('mitra.draf');
});

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
