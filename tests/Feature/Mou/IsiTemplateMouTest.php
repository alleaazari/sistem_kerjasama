<?php

use App\Support\Mou\StrukturTemplateMou;

/*
|--------------------------------------------------------------------------
| Isi template MoU: daftar pasal, penomoran dinamis, gerbang draf
|--------------------------------------------------------------------------
| Template A (nota_kesepakatan_pemda), B (pks_beasiswa_talenta_digital), dan
| kerangka_umum (draf) harus memuat pasal sesuai referensi, penomoran pasal
| dihitung ulang secara dinamis saat pasal opsional dinonaktifkan, dan
| template berstatus draf tidak boleh dipakai mitra untuk generate.
*/

test('template A (Pemda Standar) memuat daftar pasal sesuai referensi', function () {
    $template = StrukturTemplateMou::muat('nota_kesepakatan_pemda');

    // Referensi 1 (Kota Jambi): 14 pasal, tanpa pasal PDP terpisah.
    expect(StrukturTemplateMou::judulPasalAktif($template))->toBe([
        'MAKSUD DAN TUJUAN',
        'OBJEK',
        'LOKASI',
        'RUANG LINGKUP',
        'TUGAS DAN TANGGUNG JAWAB',
        'PELAKSANAAN',
        'KERAHASIAAN',
        'PEMBIAYAAN',
        'JANGKA WAKTU',
        'KEADAAN KAHAR',
        'PENYELESAIAN PERSELISIHAN',
        'KORESPONDENSI DAN KOMUNIKASI',
        'PERUBAHAN',
        'PENUTUP',
    ]);
});

test('template A2 (Pemda UU PDP) memuat 15 pasal termasuk PDP', function () {
    $template = StrukturTemplateMou::muat('nota_kesepakatan_pemda_pdp');

    // Referensi 2 (Tanjungbalai): 15 pasal, dengan pasal PDP.
    expect(StrukturTemplateMou::judulPasalAktif($template))->toBe([
        'MAKSUD DAN TUJUAN',
        'OBJEK',
        'LOKASI',
        'RUANG LINGKUP',
        'TUGAS DAN TANGGUNG JAWAB',
        'PELAKSANAAN',
        'KERAHASIAAN',
        'TRANSFER DATA, INFORMASI, DAN PELINDUNGAN DATA PRIBADI',
        'PEMBIAYAAN',
        'JANGKA WAKTU',
        'KEADAAN KAHAR',
        'PENYELESAIAN PERSELISIHAN',
        'KORESPONDENSI DAN KOMUNIKASI',
        'PERUBAHAN',
        'PENUTUP',
    ]);
});

test('template B memuat 15 pasal sesuai urutan referensi', function () {
    $template = StrukturTemplateMou::muat('pks_beasiswa_talenta_digital');

    expect(StrukturTemplateMou::judulPasalAktif($template))->toBe([
        'MAKSUD DAN TUJUAN',
        'DEFINISI',
        'RUANG LINGKUP',
        'PELAKSANAAN PROGRAM',
        'JANGKA WAKTU',
        'KEWAJIBAN DAN HAK',
        'KERAHASIAAN',
        'TRANSFER DATA, INFORMASI, DAN PELINDUNGAN DATA PRIBADI',
        'PEMBIAYAAN',
        'EVALUASI',
        'KORESPONDENSI',
        'PENYELESAIAN PERSELISIHAN',
        'KEADAAN KAHAR',
        'HAK KEKAYAAN INTELEKTUAL',
        'LAIN-LAIN',
    ]);
});

test('penomoran pasal dinamis tanpa lubang saat pasal opsional dimatikan', function (string $kode) {
    $template = StrukturTemplateMou::muat($kode);

    foreach (['PERUBAHAN'] as $nonaktif) {
        $aktif = StrukturTemplateMou::pasalAktif($template, [$nonaktif]);
        $nomor = array_column($aktif, 'nomor');
        $judul = array_column($aktif, 'judul');

        // Nomor harus 1..N berurutan tanpa lubang.
        expect($nomor)->toBe(range(1, count($aktif)))
            ->and($judul)->not->toContain($nonaktif);
    }
})->with(['nota_kesepakatan_pemda', 'nota_kesepakatan_pemda_pdp', 'kerangka_umum']);

test('pasal PELINDUNGAN DATA PRIBADI aktif di template UU PDP dan bisa dimatikan', function () {
    $template = StrukturTemplateMou::muat('nota_kesepakatan_pemda_pdp');
    $judul = 'TRANSFER DATA, INFORMASI, DAN PELINDUNGAN DATA PRIBADI';

    $bawaan = StrukturTemplateMou::pasalAktif($template);
    expect(array_column($bawaan, 'judul'))->toContain($judul);
    expect(array_column($bawaan, 'nomor'))->toBe(range(1, count($bawaan)));

    $tanpa = StrukturTemplateMou::pasalAktif($template, [$judul]);
    expect(array_column($tanpa, 'judul'))->not->toContain($judul);
    expect(array_column($tanpa, 'nomor'))->toBe(range(1, count($tanpa)));
});

test('template Pemda Standar tidak memuat pasal PDP terpisah', function () {
    $template = StrukturTemplateMou::muat('nota_kesepakatan_pemda');

    expect(StrukturTemplateMou::judulPasalAktif($template))
        ->not->toContain('TRANSFER DATA, INFORMASI, DAN PELINDUNGAN DATA PRIBADI');
});

test('huruf c Pasal 4 (RUANG LINGKUP) opsional dan mengikuti ayat lain', function (string $kode) {
    $template = StrukturTemplateMou::muat($kode);
    $pasal = null;

    foreach (StrukturTemplateMou::pasalAktif($template) as $p) {
        if ($p['judul'] === 'RUANG LINGKUP') {
            $pasal = $p;
        }
    }

    expect($pasal)->not->toBeNull();

    // Ayat string yang berakhir "dan" adalah awalan huruf a-b; huruf c opsional.
    $teksAyat = implode("\n", $pasal['ayat']);
    expect($teksAyat)->toContain('kegiatan lain yang disepakati oleh PARA PIHAK');
})->with(['nota_kesepakatan_pemda', 'nota_kesepakatan_pemda_pdp', 'kerangka_umum']);

test('template final boleh dipakai mitra, template draf tidak', function () {
    expect(StrukturTemplateMou::dapatGenerate('nota_kesepakatan_pemda'))->toBeTrue()
        ->and(StrukturTemplateMou::dapatGenerate('nota_kesepakatan_pemda_pdp'))->toBeTrue()
        ->and(StrukturTemplateMou::dapatGenerate('pks_beasiswa_talenta_digital'))->toBeTrue()
        ->and(StrukturTemplateMou::dapatGenerate('kerangka_umum'))->toBeFalse();

    $draf = StrukturTemplateMou::muat('kerangka_umum');
    expect($draf['status'])->toBe('draf')
        ->and(StrukturTemplateMou::dapatDipakaiMitra($draf))->toBeFalse()
        ->and(StrukturTemplateMou::pesanBelumTersedia())->toBe('Template belum tersedia.');
});

test('bidang bidang_3 memakai kerangka draf dan ditolak generate', function () {
    $template = StrukturTemplateMou::untukBidang('bidang_3');

    expect($template)->not->toBeNull()
        ->and($template['kode'])->toBe('kerangka_umum')
        ->and(StrukturTemplateMou::dapatDipakaiMitra($template))->toBeFalse();
});

test('setiap template punya blok lampiran dengan kolom sesuai referensi', function () {
    $a = StrukturTemplateMou::muat('nota_kesepakatan_pemda');
    $b = StrukturTemplateMou::muat('pks_beasiswa_talenta_digital');

    $lampiranA = collect($a['blok'])->firstWhere('tipe', 'lampiran')['lampiran'];
    $lampiranB = collect($b['blok'])->firstWhere('tipe', 'lampiran')['lampiran'];

    expect($lampiranA['kolom'])->toHaveCount(10)
        ->and($lampiranB['kolom'])->toHaveCount(9)
        ->and($lampiranB['judul'])->toContain('{{tahun_mulai}}')->toContain('{{tahun_akhir}}');
});

test('klausul pembiayaan memilih varian config, bawaan bila kosong', function () {
    expect(StrukturTemplateMou::klausulPembiayaan('APBN'))->toContain('Anggaran Pendapatan dan Belanja Negara')
        ->and(StrukturTemplateMou::klausulPembiayaan('APBD'))->toContain('Anggaran Pendapatan dan Belanja Daerah')
        ->and(StrukturTemplateMou::klausulPembiayaan('Cost Sharing'))->toContain('cost sharing')
        ->and(StrukturTemplateMou::klausulPembiayaan(''))->toBe(config('mou.pembiayaan_bawaan'))
        ->and(StrukturTemplateMou::klausulPembiayaan('TAK DIKENAL'))->toBe(config('mou.pembiayaan_bawaan'));
});

test('template B menandai VERIFIKASI pada pasal sumber hlm. 4-7', function () {
    $template = StrukturTemplateMou::muat('pks_beasiswa_talenta_digital');

    foreach ($template['blok'] as $blok) {
        if ($blok['tipe'] !== 'pasal') {
            continue;
        }

        foreach ($blok['pasal'] as $pasal) {
            if (in_array($pasal['judul'], ['KERAHASIAAN', 'TRANSFER DATA, INFORMASI, DAN PELINDUNGAN DATA PRIBADI'], true)) {
                expect($pasal)->toHaveKey('// VERIFIKASI');
            }
        }
    }
});

test('template kerangka_umum memakai penanda [ISI ...] dan tanah memuat TODO', function () {
    $template = StrukturTemplateMou::muat('kerangka_umum');
    $teks = json_encode($template, JSON_UNESCAPED_UNICODE);

    expect($template['status'])->toBe('draf')
        ->and($teks)->toContain('[ISI');

    // catatan TODO tersimpan di pasal (bukan ayat), jadi tidak tampil sebagai isi.
    $catatan = collect($template['blok'])
        ->where('tipe', 'pasal')
        ->pluck('pasal')
        ->flatten(1)
        ->pluck('catatan')
        ->filter();

    expect($catatan)->not->toBeEmpty();
});

test('tidak ada kata AI di isi template', function (string $kode) {
    $template = StrukturTemplateMou::muat($kode);
    $teks = json_encode($template, JSON_UNESCAPED_UNICODE);

    expect(preg_match('/\bAI\b/', $teks))->toBe(0);
})->with(['nota_kesepakatan_pemda', 'nota_kesepakatan_pemda_pdp', 'pks_beasiswa_talenta_digital', 'kerangka_umum']);
