<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Surat pemberitahuan penolakan pengajuan kerja sama.
 *
 * Sebelumnya fungsi global di routes/admin.php; dipindahkan ke sini supaya
 * template dan pengiriman tidak tinggal di berkas route dan bisa dipakai
 * kedua prefix peran.
 *
 * Catatan keamanan:
 *  - Tidak ada kredensial yang ditulis ke log.
 *  - Gateway WhatsApp hanya dipanggil bila WA_GATEWAY_URL & WA_GATEWAY_KEY
 *    terisi; selain itu pengiriman dicatat sebagai log lokal (driver log).
 */
final class SuratPenolakan
{
    /**
     * Susun isi surat resmi (email) dan pesan WhatsApp.
     *
     * @return array{email_subject: string, email_body: string, wa_text: string}
     */
    public static function susun(string $namaPIC, string $instansi, string $organisasi, string $alasan): array
    {
        $nomorSurat = 'B-'.random_int(100, 999).'/KOMDIGI/BPSDM.03/'.date('m/Y');
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
Call Center : 159 | Email: kerjasama@komdigi.go.id | Portal: {{ config('app.url') }}

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
            'email_body' => $emailBody,
            'wa_text' => $waText,
        ];
    }

    /**
     * Kirim pesan penolakan ke WhatsApp PIC.
     *
     * @return array{phone: string, wa_url: string}
     */
    public static function kirimWhatsApp(string $nomorTelepon, string $pesan): array
    {
        $cleanPhone = (string) preg_replace('/[^0-9]/', '', $nomorTelepon);

        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62'.substr($cleanPhone, 1);
        }

        $waUrl = 'https://api.whatsapp.com/send?phone='.$cleanPhone.'&text='.urlencode($pesan);

        $gatewayUrl = (string) config('services.wa_gateway.url', '');
        $gatewayKey = (string) config('services.wa_gateway.key', '');

        if ($gatewayUrl !== '' && $gatewayKey !== '') {
            try {
                file_get_contents($gatewayUrl, false, stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => "Content-type: application/x-www-form-urlencoded\r\nAuthorization: {$gatewayKey}\r\n",
                        'content' => http_build_query(['target' => $cleanPhone, 'message' => $pesan]),
                        'timeout' => 4,
                    ],
                ]));
            } catch (Throwable $e) {
                Log::warning('Gateway WhatsApp gagal dihubungi: '.$e->getMessage());
            }
        }

        // Catat isi pesan tanpa data pribadi berlebih; nomor disamarkan.
        Log::info('Notifikasi WhatsApp penolakan disiapkan.', [
            'phone' => substr($cleanPhone, 0, 6).'****',
        ]);

        return ['phone' => $cleanPhone, 'wa_url' => $waUrl];
    }

    /** Kirim surat penolakan resmi ke email PIC. */
    public static function kirimEmail(string $targetEmail, string $targetPIC, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($msg) use ($targetEmail, $targetPIC, $subject): void {
                $msg->to($targetEmail, $targetPIC)
                    ->from((string) config('mail.from.address', 'kerjasama@komdigi.go.id'), 'KEMKOMDIGI - Portal Kerja Sama')
                    ->subject($subject);
            });
        } catch (Throwable $e) {
            Log::error('Email penolakan gagal: '.$e->getMessage());
        }
    }
}
