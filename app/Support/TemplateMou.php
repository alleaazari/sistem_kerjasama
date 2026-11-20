<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Template draf MoU per bidang kerja sama (mode demo: satu berkas JSON).
 *
 * Berkas dapat disunting mentor/superadmin tanpa menyentuh kode:
 * storage/app/template_mou.json. Berkas dibuat otomatis dari seed di bawah
 * saat pertama kali dibaca.
 *
 * TODO-BACKEND: pindah ke tabel `template_mou` (kode_bidang unik, judul,
 * catatan, isi, versi, diperbarui_oleh, diperbarui_pada) beserta riwayat
 * versinya. Kontrak method dipertahankan agar panel dan generator tidak
 * berubah.
 */
final class TemplateMou
{
    /** Batas panjang draf yang boleh disimpan/dikirim mitra. */
    public const MAKS_ISI = 20000;

    /** Batas panjang satu nilai isian yang disisipkan ke placeholder. */
    public const MAKS_NILAI = 255;

    /**
     * Placeholder yang dikenal generator. Kunci = nama placeholder tanpa
     * kurung kurawal; nilai = label untuk panel Superadmin.
     *
     * @var array<string, string>
     */
    public const PLACEHOLDER = [
        'nama_instansi' => 'Nama instansi mitra',
        'nama_pic' => 'Nama penanggung jawab',
        'jabatan_pic' => 'Jabatan penanggung jawab',
        'alamat' => 'Alamat instansi',
        'tanggal' => 'Tanggal hari ini',
        'bidang' => 'Label bidang kerja sama',
    ];

    public static function path(): string
    {
        return storage_path('app/template_mou.json');
    }

    /**
     * Seluruh template, dikunci dengan kode bidang.
     *
     * @return array<string, array{kode: string, judul: string, catatan: string, isi: string, versi: int, diperbarui_oleh: string, diperbarui_pada: string|null}>
     */
    public static function semua(): array
    {
        $hasil = [];

        foreach (self::mentah() as $baris) {
            $row = self::normalisasi($baris);

            if ($row !== null) {
                $hasil[$row['kode']] = $row;
            }
        }

        return $hasil;
    }

    /** @return array{kode: string, judul: string, catatan: string, isi: string, versi: int, diperbarui_oleh: string, diperbarui_pada: string|null}|null */
    public static function untuk(string $kode): ?array
    {
        return self::semua()[$kode] ?? null;
    }

    /**
     * Simpan perubahan dari panel Superadmin. Versi naik satu setiap simpan
     * sehingga riwayat di log aktivitas bisa dirujuk kembali.
     *
     * @return array{kode: string, judul: string, catatan: string, isi: string, versi: int, diperbarui_oleh: string, diperbarui_pada: string|null}
     */
    public static function simpanPerubahan(string $kode, string $judul, string $catatan, string $isi, string $oleh): array
    {
        $baris = self::mentah();
        $sekarang = now(config('app.timezone', 'UTC'))->toDateTimeString();
        $hasil = null;

        foreach ($baris as $i => $lama) {
            if (($lama['kode'] ?? null) !== $kode) {
                continue;
            }

            $baris[$i] = array_merge($lama, [
                'kode' => $kode,
                'judul' => $judul,
                'catatan' => $catatan,
                'isi' => $isi,
                'versi' => max(1, (int) ($lama['versi'] ?? 1)) + 1,
                'diperbarui_oleh' => $oleh,
                'diperbarui_pada' => $sekarang,
            ]);
            $hasil = $baris[$i];
            break;
        }

        if ($hasil === null) {
            $hasil = [
                'kode' => $kode,
                'judul' => $judul,
                'catatan' => $catatan,
                'isi' => $isi,
                'versi' => 1,
                'diperbarui_oleh' => $oleh,
                'diperbarui_pada' => $sekarang,
            ];
            $baris[] = $hasil;
        }

        self::tulis($baris);

        $normal = self::normalisasi($hasil);

        if ($normal === null) {
            throw new RuntimeException('Template MoU tidak dapat dinormalisasi.');
        }

        return $normal;
    }

    /**
     * Kembalikan satu template ke isi bawaan. Null bila bidang itu tidak
     * punya template bawaan (mis. bidang baru yang belum ditentukan mentor).
     *
     * @return array{kode: string, judul: string, catatan: string, isi: string, versi: int, diperbarui_oleh: string, diperbarui_pada: string|null}|null
     */
    public static function kembalikan(string $kode, string $oleh): ?array
    {
        $bawaan = self::bawaan($kode);

        if ($bawaan === null) {
            return null;
        }

        return self::simpanPerubahan($kode, $bawaan['judul'], $bawaan['catatan'], $bawaan['isi'], $oleh);
    }

    /**
     * Isi bawaan sebuah bidang (dari seed), atau null bila tidak ada.
     *
     * @return array{kode: string, judul: string, catatan: string, isi: string, versi: int, diperbarui_oleh: string, diperbarui_pada: string|null}|null
     */
    public static function bawaan(string $kode): ?array
    {
        foreach (self::seed() as $baris) {
            if ($baris['kode'] === $kode) {
                return self::normalisasi($baris);
            }
        }

        return null;
    }

    /**
     * Placeholder yang dipakai tetapi tidak ada di daftar dikenal. Bentuk
     * longgar (mis. `{{ token }}` atau `{{token-rahasia}}`) ikut diperiksa
     * agar tidak ada token yang lolos validasi tetapi tidak pernah diganti.
     *
     * @return list<string>
     */
    public static function placeholderTidakDikenal(string $teks): array
    {
        preg_match_all('/\{\{\s*([^{}]*?)\s*\}\}/u', $teks, $cocok);

        $asing = array_filter(
            $cocok[1],
            static fn (string $nama): bool => ! array_key_exists($nama, self::PLACEHOLDER),
        );

        return array_values(array_unique($asing));
    }

    /** Teks memuat tag HTML (akan ditolak, bukan disimpan sebagai HTML). */
    public static function mengandungHtml(string $teks): bool
    {
        return preg_match('/<\s*\/?\s*[a-zA-Z][^>]*>/', $teks) === 1;
    }

    /**
     * Ilustrasi pratinjau di panel Superadmin. Bukan data mitra sungguhan.
     *
     * @return array<string, string>
     */
    public static function contohData(string $labelBidang): array
    {
        return self::data([
            'instansi' => 'Universitas Contoh Nusantara',
            'pic' => 'Nama Penanggung Jawab',
            'jabatan_pic' => 'Jabatan Penanggung Jawab',
            'alamat' => 'Jl. Contoh No. 1, Kota Medan',
        ], $labelBidang);
    }

    /**
     * Isi placeholder {{...}} dengan data instansi yang sudah dibersihkan.
     * Placeholder yang tidak punya nilai dibiarkan tampak agar jelas belum
     * lengkap; pemanggil selalu mengirim keenam kunci di bawah.
     *
     * @param  array{isi: string}  $template
     * @param  array<string, string>  $data
     */
    public static function render(array $template, array $data): string
    {
        $ganti = [];

        foreach ($data as $kunci => $nilai) {
            $ganti[$kunci] = self::bersihkan($nilai);
        }

        // Spasi di dalam kurung kurawal diterima, sama seperti validasi panel.
        return preg_replace_callback(
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/u',
            static fn (array $cocok): string => $ganti[$cocok[1]] ?? $cocok[0],
            $template['isi'],
        ) ?? $template['isi'];
    }

    /**
     * Nilai placeholder dari identitas mitra. Nilai kosong diganti penanda
     * [ISI: ...] supaya bagian yang belum lengkap terlihat di draf.
     *
     * @param  array<string, mixed>  $mitra
     * @return array<string, string>
     */
    public static function data(array $mitra, string $labelBidang): array
    {
        $ambil = function (string $kunci, string $cadangan) use ($mitra): string {
            $nilai = $mitra[$kunci] ?? '';

            return is_string($nilai) && trim($nilai) !== '' ? $nilai : $cadangan;
        };

        return [
            'nama_instansi' => $ambil('instansi', '[ISI: nama instansi]'),
            'nama_pic' => $ambil('pic', '[ISI: nama penanggung jawab]'),
            'jabatan_pic' => $ambil('jabatan_pic', '[ISI: jabatan penanggung jawab]'),
            'alamat' => $ambil('alamat', '[ISI: alamat instansi]'),
            'tanggal' => CarbonImmutable::now(config('app.timezone', 'UTC'))->translatedFormat('d F Y'),
            'bidang' => trim($labelBidang) !== '' ? $labelBidang : '[ISI: bidang kerja sama]',
        ];
    }

    /**
     * Satu nilai isian: tanpa tag HTML, tanpa karakter kendali, satu baris.
     * Output tetap di-escape saat dirender (Blade {{ }}).
     */
    public static function bersihkan(mixed $nilai): string
    {
        $teks = is_string($nilai) ? $nilai : '';

        $teks = strip_tags($teks);
        $teks = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $teks) ?? '';
        $teks = preg_replace('/\s+/u', ' ', $teks) ?? '';

        return Str::limit(trim($teks), self::MAKS_NILAI, '');
    }

    /**
     * Teks draf yang disunting mitra: tag HTML dibuang, baris dipertahankan,
     * panjang dibatasi. Disimpan sebagai teks biasa.
     */
    public static function bersihkanDraf(string $teks): string
    {
        return Str::limit(strip_tags(self::rapikanTeks($teks)), self::MAKS_ISI, '');
    }

    /**
     * Rapikan teks biasa tanpa membuang tag: baris dipertahankan, karakter
     * kendali dibuang. Validasi panel menolak tag HTML sebelum tahap ini.
     */
    public static function rapikanTeks(string $teks): string
    {
        $teks = str_replace(["\r\n", "\r"], "\n", $teks);
        $teks = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $teks) ?? '';

        return trim($teks);
    }

    /**
     * Pecah draf menjadi paragraf untuk halaman detail MoU.
     *
     * @return array<int, string>
     */
    public static function paragraf(string $teks): array
    {
        $bagian = preg_split('/\n\s*\n/u', trim($teks)) ?: [];

        return array_values(array_filter(
            array_map('trim', $bagian),
            fn (string $paragraf) => $paragraf !== '',
        ));
    }

    /**
     * Isi awal tiap bidang. Berbeda arah sesuai program; bagian yang belum
     * pasti ditandai [ISI: ...] agar dilengkapi mentor/superadmin.
     *
     * TODO: tambahkan template bidang ketiga di sini setelah kode bidangnya
     * ditentukan mentor (samakan dengan config/kerjasama.php).
     *
     * @return array<int, array{kode: string, judul: string, catatan: string, isi: string}>
     */
    private static function seed(): array
    {
        $catatan = 'Lengkapi semua bagian [ISI: ...] bersama mentor atau superadmin. Draf ini bukan dokumen resmi sampai diperiksa dan ditandatangani.';

        return [
            [
                'kode' => 'VSGA',
                'judul' => 'Draf Nota Kesepahaman Kerja Sama Pengembangan Kompetensi Vokasi Digital (VSGA)',
                'catatan' => $catatan,
                'isi' => <<<'TXT'
NOTA KESEPAHAMAN
KERJA SAMA PENGEMBANGAN KOMPETENSI VOKASI DIGITAL

ANTARA
KEMENTERIAN KOMUNIKASI DAN DIGITAL REPUBLIK INDONESIA
DAN
{{nama_instansi}}

Nomor: [ISI: nomor dokumen dari Kementerian Komunikasi dan Digital]
Tanggal: {{tanggal}}

Para Pihak:
1. Kementerian Komunikasi dan Digital Republik Indonesia, dalam hal ini diwakili oleh [ISI: nama dan jabatan pejabat penandatangan].
2. {{nama_instansi}}, dalam hal ini diwakili oleh {{nama_pic}} selaku {{jabatan_pic}}, berkedudukan di {{alamat}}.

Pasal 1 — Maksud dan Tujuan
(1) Para Pihak sepakat melaksanakan kerja sama dalam bidang {{bidang}}.
(2) Kerja sama bertujuan mengembangkan kompetensi vokasi digital peserta didik dan tenaga pengajar melalui pelatihan, praktik kerja, serta sertifikasi kompetensi yang relevan dengan kebutuhan industri.
(3) [ISI: tujuan khusus dan indikator keberhasilan yang disepakati Para Pihak].

Pasal 2 — Ruang Lingkup
Ruang lingkup kerja sama meliputi:
a. penyelenggaraan pelatihan vokasi digital sesuai kurikulum yang disepakati;
b. fasilitasi praktik kerja, magang, atau proyek nyata bagi peserta;
c. persiapan dan pelaksanaan uji kompetensi atau sertifikasi;
d. [ISI: kegiatan lain, misalnya pelatihan tenaga pengajar atau pengembangan modul].

Pasal 3 — Pelaksanaan
(1) Rincian jadwal, jumlah peserta, lokasi, dan kurikulum dituangkan dalam rencana kerja yang disepakati Para Pihak.
(2) [ISI: mekanisme koordinasi, penanggung jawab dari masing-masing Pihak, dan jangka waktu pelaksanaan].
(3) Perubahan atas rencana kerja disepakati secara tertulis.

Pasal 4 — Pembiayaan
(1) [ISI: sumber pembiayaan dan pembagian beban biaya masing-masing Pihak].
(2) Pembiayaan di luar kesepakatan ini ditanggung oleh Pihak yang mengajukan.

Pasal 5 — Hak dan Kewajiban
(1) Setiap Pihak berhak memperoleh informasi dan hasil kerja sama sesuai kesepakatan.
(2) Setiap Pihak wajib menjaga kerahasiaan data peserta dan mematuhi peraturan perundang-undangan yang berlaku.
(3) [ISI: kewajiban lain yang disepakati].

Pasal 6 — Masa Berlaku
(1) Kesepahaman ini berlaku sejak tanggal ditandatangani sampai dengan [ISI: tanggal berakhir].
(2) Kesepahaman dapat diperpanjang atas kesepakatan tertulis Para Pihak.

Pasal 7 — Penutup
Kesepahaman ini dibuat dalam rangka kerja sama yang saling menguntungkan dan tidak menimbulkan hubungan kerja, agensi, atau perwakilan di antara Para Pihak.

Draf awal ini disusun dari template bidang VSGA. Bagian bertanda [ISI: ...] wajib dilengkapi dan diverifikasi mentor atau superadmin sebelum ditandatangani.
TXT,
            ],
            [
                'kode' => 'GTA',
                'judul' => 'Draf Nota Kesepahaman Kerja Sama Peningkatan Kapasitas Transformasi Digital Pemerintahan (GTA)',
                'catatan' => $catatan,
                'isi' => <<<'TXT'
NOTA KESEPAHAMAN
KERJA SAMA PENINGKATAN KAPASITAS TRANSFORMASI DIGITAL PEMERINTAHAN

ANTARA
KEMENTERIAN KOMUNIKASI DAN DIGITAL REPUBLIK INDONESIA
DAN
{{nama_instansi}}

Nomor: [ISI: nomor dokumen dari Kementerian Komunikasi dan Digital]
Tanggal: {{tanggal}}

Para Pihak:
1. Kementerian Komunikasi dan Digital Republik Indonesia, dalam hal ini diwakili oleh [ISI: nama dan jabatan pejabat penandatangan].
2. {{nama_instansi}}, dalam hal ini diwakili oleh {{nama_pic}} selaku {{jabatan_pic}}, berkedudukan di {{alamat}}.

Pasal 1 — Maksud dan Tujuan
(1) Para Pihak sepakat melaksanakan kerja sama dalam bidang {{bidang}}.
(2) Kerja sama bertujuan meningkatkan kapasitas aparatur dan tata kelola pemerintahan digital melalui pembelajaran, pendampingan, serta berbagi praktik baik transformasi digital.
(3) [ISI: tujuan khusus dan indikator keberhasilan yang disepakati Para Pihak].

Pasal 2 — Ruang Lingkup
Ruang lingkup kerja sama meliputi:
a. penyelenggaraan pelatihan dan pembelajaran bidang transformasi digital pemerintahan;
b. fasilitasi sertifikasi atau pengakuan kompetensi bagi peserta yang memenuhi ketentuan;
c. pendampingan penyusunan rencana dan evaluasi program transformasi digital;
d. [ISI: kegiatan lain, misalnya studi banding, lokakarya, atau kolaborasi antarinstansi].

Pasal 3 — Pelaksanaan
(1) Rincian kurikulum, jadwal, jumlah peserta, dan lokasi dituangkan dalam rencana kerja yang disepakati Para Pihak.
(2) [ISI: mekanisme koordinasi, penanggung jawab dari masing-masing Pihak, dan jangka waktu pelaksanaan].
(3) Perubahan atas rencana kerja disepakati secara tertulis.

Pasal 4 — Pembiayaan
(1) [ISI: sumber pembiayaan dan pembagian beban biaya masing-masing Pihak].
(2) Pembiayaan di luar kesepakatan ini ditanggung oleh Pihak yang mengajukan.

Pasal 5 — Hak dan Kewajiban
(1) Setiap Pihak berhak memperoleh informasi dan hasil kerja sama sesuai kesepakatan.
(2) Setiap Pihak wajib menjaga kerahasiaan data dan mematuhi peraturan perundang-undangan yang berlaku.
(3) [ISI: kewajiban lain yang disepakati].

Pasal 6 — Masa Berlaku
(1) Kesepahaman ini berlaku sejak tanggal ditandatangani sampai dengan [ISI: tanggal berakhir].
(2) Kesepahaman dapat diperpanjang atas kesepakatan tertulis Para Pihak.

Pasal 7 — Penutup
Kesepahaman ini dibuat dalam rangka kerja sama yang saling menguntungkan dan tidak menimbulkan hubungan kerja, agensi, atau perwakilan di antara Para Pihak.

Draf awal ini disusun dari template bidang GTA. Bagian bertanda [ISI: ...] wajib dilengkapi dan diverifikasi mentor atau superadmin sebelum ditandatangani.
TXT,
            ],
        ];
    }

    /**
     * Baris mentah di berkas; seed ditulis bila berkas belum ada/kosong.
     *
     * @return array<int, array<string, mixed>>
     */
    private static function mentah(): array
    {
        $data = is_file(self::path())
            ? json_decode((string) file_get_contents(self::path()), true)
            : null;

        if (! is_array($data) || $data === []) {
            $data = self::seed();
            self::tulis($data);
        }

        /** @var array<int, array<string, mixed>> $hasil */
        $hasil = array_values(array_filter($data, 'is_array'));

        return $hasil;
    }

    /**
     * Bentuk baku satu baris; null bila bukan template yang sah.
     *
     * @return array{kode: string, judul: string, catatan: string, isi: string, versi: int, diperbarui_oleh: string, diperbarui_pada: string|null}|null
     */
    private static function normalisasi(mixed $baris): ?array
    {
        if (! is_array($baris)) {
            return null;
        }

        $kode = $baris['kode'] ?? null;
        $judul = $baris['judul'] ?? null;
        $isi = $baris['isi'] ?? null;

        if (! is_string($kode) || $kode === '' || ! is_string($judul) || ! is_string($isi) || trim($isi) === '') {
            return null;
        }

        $pada = $baris['diperbarui_pada'] ?? null;

        return [
            'kode' => $kode,
            'judul' => $judul,
            'catatan' => is_string($baris['catatan'] ?? null) ? $baris['catatan'] : '',
            'isi' => $isi,
            'versi' => max(1, (int) ($baris['versi'] ?? 1)),
            'diperbarui_oleh' => is_string($baris['diperbarui_oleh'] ?? null) ? $baris['diperbarui_oleh'] : '',
            'diperbarui_pada' => is_string($pada) && $pada !== '' ? $pada : null,
        ];
    }

    /** @param array<int, array<string, mixed>> $data */
    private static function tulis(array $data): void
    {
        $dir = dirname(self::path());

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        file_put_contents(
            self::path(),
            json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}
