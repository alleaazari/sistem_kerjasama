<?php

use App\Support\BidangKerjasama;
use App\Support\Mou\PendaftaranPlaceholder;
use App\Support\Mou\StrukturTemplateMou;

/*
|--------------------------------------------------------------------------
| Fondasi template MoU
|--------------------------------------------------------------------------
| Config config/mou.php terbaca, pemetaan bidang -> template menunjuk
| template yang benar-benar ada, dan setiap placeholder yang dipakai
| template terdaftar di PendaftaranPlaceholder (tidak ada yang yatim).
*/

test('config mou terbaca dengan bagian wajib', function () {
    expect(config('mou.pemetaan'))->toBeArray()->not->toBeEmpty()
        ->and(config('mou.pihak_kesatu'))->toBeArray()
        ->and(config('mou.dasar_hukum_nasional'))->toBeArray()->toHaveCount(7)
        ->and(config('mou.pembiayaan_varian'))->toBeArray();

    expect(config('mou.pihak_kesatu.nama_unit'))->toContain('Komunikasi dan Digital')
        ->and(config('mou.pihak_kesatu.pejabat_pelaksana'))->toContain('Balai Besar Pelatihan')
        ->and(config('mou.pihak_kesatu.penandatangan'))->toBe('[ISI penandatangan Pihak Kesatu]');

    expect(array_keys(config('mou.pembiayaan_varian')))->toBe(['APBN', 'APBD', 'Cost Sharing']);
});

test('setiap kode bidang punya pemetaan template', function () {
    $pemetaan = config('mou.pemetaan');

    foreach (BidangKerjasama::kode() as $kodeBidang) {
        expect($pemetaan)->toHaveKey($kodeBidang)
            ->and($pemetaan[$kodeBidang])->toBeString()->not->toBe('');
    }
});

test('setiap nilai pemetaan menunjuk template yang ada', function () {
    foreach (array_unique(array_values(config('mou.pemetaan'))) as $kodeTemplate) {
        expect(StrukturTemplateMou::untuk($kodeTemplate))->not->toBeNull(
            "Template '{$kodeTemplate}' tidak ditemukan di storage/app/mou/.",
        );
    }
});

test('bidang tanpa pemetaan memakai kerangka_umum', function () {
    $template = StrukturTemplateMou::untukBidang('KODE_TAK_DIKENAL');

    expect($template)->not->toBeNull()
        ->and($template['kode'])->toBe('kerangka_umum');
});

test('setiap template punya struktur blok yang sah', function (string $kodeTemplate) {
    $template = StrukturTemplateMou::muat($kodeTemplate);
    $tipe = array_map(static fn (array $blok): string => $blok['tipe'], $template['blok']);

    expect(StrukturTemplateMou::periksa($template))->toBe([])
        ->and($tipe)->toContain('sampul', 'pembukaan', 'menimbang', 'mengingat', 'penutup', 'tanda_tangan', 'lampiran')
        ->and($tipe)->toContain('pasal')
        ->and($template['status'])->toBeIn(['final', 'draf'])
        ->and($template['versi'])->toBeInt();
})->with([
    'nota_kesepakatan_pemda',
    'nota_kesepakatan_pemda_pdp',
    'pks_beasiswa_talenta_digital',
    'kerangka_umum',
]);

test('setiap pasal punya field wajib', function (string $kodeTemplate) {
    $template = StrukturTemplateMou::muat($kodeTemplate);

    foreach ($template['blok'] as $blok) {
        if ($blok['tipe'] !== 'pasal') {
            continue;
        }

        expect($blok['pasal'])->toBeArray()->not->toBeEmpty();

        foreach ($blok['pasal'] as $pasal) {
            expect($pasal)->toHaveKeys(['nomor', 'judul', 'ayat', 'opsional', 'aktif_bawaan'])
                ->and($pasal['ayat'])->toBeArray()->not->toBeEmpty()
                ->and($pasal['opsional'])->toBeBool()
                ->and($pasal['aktif_bawaan'])->toBeBool();
        }
    }
})->with([
    'nota_kesepakatan_pemda',
    'nota_kesepakatan_pemda_pdp',
    'pks_beasiswa_talenta_digital',
    'kerangka_umum',
]);

test('setiap lampiran punya judul, tabel, kolom, dan baris', function (string $kodeTemplate) {
    $template = StrukturTemplateMou::muat($kodeTemplate);

    foreach ($template['blok'] as $blok) {
        if ($blok['tipe'] !== 'lampiran') {
            continue;
        }

        expect($blok['lampiran'])->toHaveKeys(['judul', 'tabel', 'kolom', 'baris'])
            ->and($blok['lampiran']['kolom'])->toBeArray()->not->toBeEmpty()
            ->and($blok['lampiran']['baris'])->toBeArray();
    }
})->with([
    'nota_kesepakatan_pemda',
    'nota_kesepakatan_pemda_pdp',
    'pks_beasiswa_talenta_digital',
    'kerangka_umum',
]);

test('tidak ada placeholder yatim di seluruh template', function () {
    $yatim = StrukturTemplateMou::semuaPlaceholderYatim();

    expect($yatim)->toBe([]);
});

test('setiap placeholder yang dipakai template terdaftar', function (string $kodeTemplate) {
    $template = StrukturTemplateMou::muat($kodeTemplate);

    foreach (StrukturTemplateMou::placeholderTerpakai($template) as $nama) {
        expect(PendaftaranPlaceholder::terdaftar($nama))->toBeTrue(
            "Placeholder '{$nama}' pada template '{$kodeTemplate}' tidak terdaftar.",
        );
    }
})->with([
    'nota_kesepakatan_pemda',
    'nota_kesepakatan_pemda_pdp',
    'pks_beasiswa_talenta_digital',
    'kerangka_umum',
]);

test('template memakai placeholder pihak kesatu dari config', function () {
    $template = StrukturTemplateMou::muat('nota_kesepakatan_pemda');
    $dipakai = StrukturTemplateMou::placeholderTerpakai($template);

    expect($dipakai)->toContain(
        'pihak_kesatu_nama_unit',
        'pihak_kesatu_alamat',
        'pihak_kesatu_penandatangan',
        'pihak_kesatu_jabatan',
        'pihak_kesatu_pejabat_pelaksana',
        'dasar_hukum',
        'pembiayaan',
    );
});

test('semua sumber placeholder yang dikenal sah', function () {
    $sah = ['form', 'lengkapi_mitra', 'config', 'turunan'];

    foreach (PendaftaranPlaceholder::daftar() as $nama => $meta) {
        expect($meta)->toHaveKeys(['sumber', 'keterangan', 'wajib'])
            ->and($sah)->toContain($meta['sumber'])
            ->and($meta['keterangan'])->toBeString()->not->toBe('')
            ->and($meta['wajib'])->toBeBool();
    }
});
