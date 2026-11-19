<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Template draf MoU per bidang kerja sama (mode demo: satu berkas JSON).
 *
 * Berkas dapat disunting mentor/superadmin tanpa menyentuh kode:
 * storage/app/template_mou.json. Berkas dibuat otomatis dari seed di bawah
 * saat pertama kali dibaca.
 *
 * TODO-BACKEND: pindah ke tabel `template_mou` (kode_bidang unik, judul,
 * catatan, isi, diperbarui_oleh, diperbarui_pada) dan kelola lewat panel
 * superadmin. Kontrak method dipertahankan agar pemanggil tidak berubah.
 */
final class TemplateMou
{
    /** Batas panjang draf yang boleh disimpan/dikirim mitra. */
    public const MAKS_ISI = 20000;

    /** Batas panjang satu nilai isian yang disisipkan ke placeholder. */
    public const MAKS_NILAI = 255;

    public static function path(): string
    {
        return storage_path('app/template_mou.json');
    }

    /**
     * Seluruh template, dikunci dengan kode bidang.
     *
     * @return array<string, array{kode: string, judul: string, catatan: string, isi: string}>
     */
    public static function semua(): array
    {
        $data = is_file(self::path())
            ? json_decode((string) file_get_contents(self::path()), true)
            : null;

        if (! is_array($data) || $data === []) {
            $data = self::seed();
            self::simpan($data);
        }

        $hasil = [];

        foreach ($data as $baris) {
            if (! is_array($baris)) {
                continue;
            }

            $kode = $baris['kode'] ?? null;
            $judul = $baris['judul'] ?? null;
            $isi = $baris['isi'] ?? null;

            if (! is_string($kode) || $kode === '' || ! is_string($judul) || ! is_string($isi) || trim($isi) === '') {
                continue;
            }

            $hasil[$kode] = [
                'kode' => $kode,
                'judul' => $judul,
                'catatan' => is_string($baris['catatan'] ?? null) ? $baris['catatan'] : '',
                'isi' => $isi,
            ];
        }

        return $hasil;
    }

    /** @return array{kode: string, judul: string, catatan: string, isi: string}|null */
    public static function untuk(string $kode): ?array
    {
        return self::semua()[$kode] ?? null;
    }

    /**
     * Isi placeholder {{...}} dengan data instansi yang sudah dibersihkan.
     * Placeholder yang tidak punya nilai dibiarkan tampak agar jelas belum
     * lengkap; pemanggil selalu mengirim keenam kunci di bawah.
     *
     * @param  array{kode: string, judul: string, catatan: string, isi: string}  $template
     * @param  array<string, string>  $data
     */
    public static function render(array $template, array $data): string
    {
        $ganti = [];

        foreach ($data as $kunci => $nilai) {
            $ganti['{{'.$kunci.'}}'] = self::bersihkan($nilai);
        }

        return strtr($template['isi'], $ganti);
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
        $teks = str_replace(["\r\n", "\r"], "\n", $teks);
        $teks = strip_tags($teks);
        $teks = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $teks) ?? '';

        return Str::limit(trim($teks), self::MAKS_ISI, '');
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

    /** @param array<int, array<string, mixed>> $data */
    private static function simpan(array $data): void
    {
        $dir = dirname(self::path());

        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        file_put_contents(
            self::path(),
            json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }
}
