<?php

/*
|--------------------------------------------------------------------------
| Fondasi template MoU — SATU sumber nilai tetap
|--------------------------------------------------------------------------
| Berkas ini dipakai oleh:
|   - app/Support/Mou/StrukturTemplateMou  -> pemuatan blok template
|   - app/Support/Mou/PendaftaranPlaceholder -> daftar placeholder
|   - renderer, form "lengkapi data mitra", dan editor template
|
| Nilai di sini statis (tidak berubah tanpa keputusan mentor). Teks isi
| template berada di storage/app/mou/*.json agar dapat disunting tanpa
| menyentuh kode, mengikuti pola storage/app/template_mou.json.
|
| Tidak ada teks MoU yang ditulis di controller atau view; semuanya dibaca
| dari config ini dan dari template.
|
| Referensi penyusunan: routes/MOU/*.pdf (Nota Kesepakatan Pemda Tanjungbalai,
| MoU Kota Jambi, PKS Politeknik Caltex Riau).
*/

return [

    /*
    |----------------------------------------------------------------------
    | Pemetaan bidang kerja sama -> kode template
    |----------------------------------------------------------------------
    | Kunci = kode bidang pada config/kerjasama.php (dan slot bidang baru).
    | Nilai = nama template, dicocokkan dengan kolom `kode` pada berkas
    | template di storage/app/mou/.
    |
    | Bidang yang tidak ada di peta ini memakai template 'kerangka_umum'.
    */
    // KONFIRMASI MENTOR: pemetaan bidang ke template
    'pemetaan' => [
        'GTA' => 'nota_kesepakatan_pemda',
        'VSGA' => 'pks_beasiswa_talenta_digital',
        'bidang_3' => 'kerangka_umum',
    ],

    /*
    |----------------------------------------------------------------------
    | Identitas Pihak Kesatu (Kementerian Komunikasi dan Digital)
    |----------------------------------------------------------------------
    | Nilai tetap dari contoh dokumen (bagian korespondensi):
    | Jalan Medan Merdeka Barat No. 9, Jakarta Pusat 10110, telepon
    | 021 3810 678, surel ppplitbang@mail.komdigi.go.id.
    */
    'pihak_kesatu' => [
        'nama_unit' => 'Badan Pengembangan Sumber Daya Manusia Komunikasi dan Digital, Kementerian Komunikasi dan Digital Republik Indonesia',
        'alamat' => 'Jalan Medan Merdeka Barat Nomor 9, Jakarta Pusat 10110',
        'telepon' => '(021) 3810 678',
        'email' => 'ppplitbang@mail.komdigi.go.id',
        // Diisi mentor/superadmin; contoh dokumen memakai Pejabat Penandatangan sesuai SK pengangkatan.
        'penandatangan' => '[ISI penandatangan Pihak Kesatu]',
        'jabatan' => '[ISI penandatangan Pihak Kesatu]',
        // Pejabat pelaksana Pihak Kesatu, tetap di seluruh dokumen.
        'pejabat_pelaksana' => 'Kepala Balai Besar Pelatihan Sumber Daya Manusia Komunikasi dan Digital Medan',
    ],

    /*
    |----------------------------------------------------------------------
    | Dasar hukum nasional yang tetap (Template A)
    |----------------------------------------------------------------------
    | Diambil dari bagian "memperhatikan" contoh dokumen, dibatasi pada
    | peraturan berskala nasional. Peraturan tingkat daerah (Perda dan
    | sejenisnya) menjadi bagian data mitra, bukan daftar tetap ini.
    */
    // VERIFIKASI nomor peraturan
    'dasar_hukum_nasional' => [
        'Undang-Undang Nomor 23 Tahun 2014 tentang Pemerintahan Daerah, sebagaimana telah beberapa kali diubah terakhir dengan Undang-Undang Nomor 11 Tahun 2020 tentang Cipta Kerja',
        'Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi',
        'Peraturan Pemerintah Nomor 28 Tahun 2018 tentang Kerja Sama Daerah',
        'Peraturan Presiden Nomor 140 Tahun 2024 tentang Organisasi Kementerian',
        'Peraturan Presiden Nomor 174 Tahun 2024 tentang Kementerian Komunikasi dan Digital',
        'Peraturan Menteri Komunikasi dan Digital Nomor ... Tahun 2025 tentang Organisasi dan Tata Kerja Kementerian Komunikasi dan Digital',
        'Peraturan Menteri Dalam Negeri Nomor 22 Tahun 2020 tentang Tata Cara Kerja Sama Daerah dengan Daerah Lain dan Kerja Sama Daerah dengan Pihak Ketiga',
    ],

    /*
    |----------------------------------------------------------------------
    | Varian klausul pembiayaan
    |----------------------------------------------------------------------
    | Pasal pembiayaan memilih salah satu varian sesuai skema yang dipilih
    | mitra pada form pengajuan. Teks diambil dari Pasal Pembiayaan contoh
    | dokumen dan disamakan maknanya.
    */
    // VERIFIKASI MENTOR
    'pembiayaan_varian' => [
        'APBN' => 'Segala pembiayaan untuk pelaksanaan kerja sama ini yang bersumber dari Anggaran Pendapatan dan Belanja Negara dibebankan pada anggaran Pihak Kesatu sesuai dengan ketentuan peraturan perundang-undangan.',
        'APBD' => 'Segala pembiayaan untuk pelaksanaan kerja sama ini yang bersumber dari Anggaran Pendapatan dan Belanja Daerah dibebankan pada anggaran Pihak Kedua sesuai dengan ketentuan peraturan perundang-undangan.',
        'Cost Sharing' => 'Segala pembiayaan untuk pelaksanaan kerja sama ini dibebankan secara bersama (cost sharing) pada anggaran masing-masing Pihak sesuai tugas, tanggung jawab, dan ketentuan peraturan perundang-undangan yang berlaku.',
    ],

];
