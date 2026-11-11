<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * DATA DUMMY TERPUSAT — mode demo.
 *
 * Berkas ini satu-satunya tempat data contoh hidup. Semua tanggal dihitung
 * RELATIF terhadap hari ini agar pratinjau status selalu masuk akal.
 * Tidak ada data instansi nyata di sini.
 */
final class DataDemo
{
    /** Identitas mitra yang dipakai bila session belum berisi data.
     *
     * @return array<string, mixed>
     */
    public static function mitra(): array
    {
        $hariIni = CarbonImmutable::now(config('app.timezone', 'UTC'))->startOfDay();

        return [
            'username' => 'user1',
            'instansi' => 'Pemerintah Provinsi Jawa Barat (DATA DUMMY)',
            'organisasi' => 'Dinas Komunikasi dan Informatika',
            'pic' => 'Rina Wulandari, S.STP., M.Si.',
            'email' => 'rina@jabarprov.go.id',
            'telp' => '081234567890',
            'alamat' => 'Jl. Diponegoro No. 22, Bandung, Jawa Barat',
            'foto' => null,
            'status' => 'AKTIF',
            // Disimpan sebagai tanggal ISO agar bisa dihitung, bukan string tampilan.
            'mulai' => $hariIni->subMonthsNoOverflow(10)->toDateString(),
            'selesai' => $hariIni->addDays(200)->toDateString(),
            'diajukan' => $hariIni->subMonthsNoOverflow(11)->translatedFormat('d F Y'),
        ];
    }

    /**
     * MoU contoh (DATA DUMMY) — hanya muncul bila session belum punya data.
     * Status mengikuti tiga nilai yang dipakai UI.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function mou(string $pemilik): array
    {
        $hariIni = CarbonImmutable::now(config('app.timezone', 'UTC'))->startOfDay();

        return [
            [
                'id' => (string) Str::uuid(),
                'pemilik' => $pemilik,
                'judul' => 'Nota Kesepahaman Literasi Digital & SPBE (DATA DUMMY)',
                'nomor' => '045/KS/KOMDIGI/2026',
                'keterangan' => 'Contoh dokumen yang sudah ditinjau dan disetujui admin.',
                'status' => 'Disetujui',
                'tanggal' => $hariIni->subDays(40)->toDateString(),
                'nama_asli' => 'mou-literasi-digital.pdf',
                'berkas' => null,
                'ukuran' => 0,
                'mime' => null,
                // Isi templat (DATA DUMMY): kerangka pasal dengan placeholder [ ].
                'isi' => self::isiTemplat(),
            ],
            [
                'id' => (string) Str::uuid(),
                'pemilik' => $pemilik,
                'judul' => 'Fasilitasi Uji Kompetensi SKKNI Digital Talent (DATA DUMMY)',
                'nomor' => '088/KS/BBLSDM/2026',
                'keterangan' => 'Contoh dokumen yang masih menunggu peninjauan admin.',
                'status' => 'Menunggu Peninjauan',
                'tanggal' => $hariIni->subDays(6)->toDateString(),
                'nama_asli' => 'mou-skkni-dts.pdf',
                'berkas' => null,
                'ukuran' => 0,
                'mime' => null,
                'isi' => null,
            ],
        ];
    }

    public const STATUS_MOU = ['Menunggu Peninjauan', 'Disetujui', 'Perlu Revisi'];

    /**
     * Kerangka isi MoU contoh (DATA DUMMY). Hanya struktur pasal dan
     * placeholder [ ... ] — tanpa fakta hukum, angka, atau nama karangan.
     *
     * @return array<int, array{judul: string, paragraf: array<int, string>}>
     */
    public static function isiTemplat(): array
    {
        return [
            [
                'judul' => 'Pasal 1 — Maksud dan Tujuan',
                'paragraf' => [
                    'Para Pihak sepakat untuk melaksanakan kerja sama dalam bidang [isi bidang kerja sama] sesuai dengan tugas dan fungsi masing-masing Pihak.',
                    'Tujuan kerja sama ini adalah [tujuan yang disepakati Para Pihak].',
                ],
            ],
            [
                'judul' => 'Pasal 2 — Ruang Lingkup',
                'paragraf' => [
                    'Ruang lingkup kerja sama meliputi: [rincian kegiatan], [rincian kegiatan], dan [rincian kegiatan].',
                    'Rincian teknis setiap kegiatan dituangkan dalam dokumen tersendiri yang merupakan bagian tidak terpisahkan dari kesepakatan ini.',
                ],
            ],
            [
                'judul' => 'Pasal 3 — Jangka Waktu',
                'paragraf' => [
                    'Kerja sama ini berlaku untuk jangka waktu [jumlah] tahun sejak tanggal [tanggal mulai] sampai dengan [tanggal berakhir].',
                    'Perpanjangan jangka waktu disepakati Para Pihak sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.',
                ],
            ],
            [
                'judul' => 'Pasal 4 — Hak dan Kewajiban',
                'paragraf' => [
                    'Hak dan kewajiban Para Pihak meliputi: [rincian hak], [rincian kewajiban], serta [rincian lain yang disepakati].',
                    'Setiap Pihak bertanggung jawab atas pelaksanaan bagiannya masing-masing sesuai kesepakatan tertulis.',
                ],
            ],
            [
                'judul' => 'Pasal 5 — Penutup',
                'paragraf' => [
                    'Hal-hal yang belum diatur dalam kesepakatan ini akan ditetapkan kemudian oleh Para Pihak melalui kesepakatan tertulis.',
                    'Kesepakatan ini dibuat dan ditandatangani oleh Para Pihak pada tanggal [tanggal penandatanganan].',
                ],
            ],
        ];
    }
}
