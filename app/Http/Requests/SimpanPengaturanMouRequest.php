<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi pengaturan global template MoU (Superadmin): identitas Pihak Kesatu
 * dan daftar dasar hukum nasional. Disimpan sebagai overlay di atas config.
 */
class SimpanPengaturanMouRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi peran ditangani middleware rute
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'pihak_kesatu.nama_unit' => ['required', 'string', 'max:255'],
            'pihak_kesatu.alamat' => ['required', 'string', 'max:255'],
            'pihak_kesatu.telepon' => ['nullable', 'string', 'max:60'],
            'pihak_kesatu.email' => ['nullable', 'string', 'email:rfc', 'max:150'],
            'pihak_kesatu.penandatangan' => ['required', 'string', 'max:150'],
            'pihak_kesatu.jabatan' => ['required', 'string', 'max:150'],
            'pihak_kesatu.pejabat_pelaksana' => ['required', 'string', 'max:200'],
            'dasar_hukum_nasional' => ['nullable', 'string', 'max:4000'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'pihak_kesatu.nama_unit.required' => 'Nama unit Pihak Kesatu wajib diisi.',
            'pihak_kesatu.alamat.required' => 'Alamat Pihak Kesatu wajib diisi.',
            'pihak_kesatu.penandatangan.required' => 'Nama penandatangan Pihak Kesatu wajib diisi.',
            'pihak_kesatu.jabatan.required' => 'Jabatan penandatangan Pihak Kesatu wajib diisi.',
            'pihak_kesatu.pejabat_pelaksana.required' => 'Pejabat pelaksana Pihak Kesatu wajib diisi.',
            'pihak_kesatu.email.email' => 'Surel Pihak Kesatu harus berupa alamat surel yang sah.',
            'dasar_hukum_nasional.max' => 'Daftar dasar hukum terlalu panjang.',
        ];
    }

    /**
     * Nilai bersih: tiap baris dasar hukum dipangkas, tag HTML dibuang.
     *
     * @return array<string, mixed>
     */
    public function nilaiBersih(): array
    {
        $pihakKesatu = [];

        foreach (['nama_unit', 'alamat', 'telepon', 'email', 'penandatangan', 'jabatan', 'pejabat_pelaksana'] as $kunci) {
            $nilai = $this->input('pihak_kesatu.'.$kunci);
            $pihakKesatu[$kunci] = is_string($nilai) ? trim(strip_tags($nilai)) : '';
        }

        $dasar = [];

        foreach (preg_split('/\r\n|\r|\n/', (string) $this->input('dasar_hukum_nasional', '')) ?: [] as $baris) {
            $baris = trim(strip_tags((string) $baris));

            if ($baris !== '') {
                $dasar[] = $baris;
            }
        }

        return [
            'pihak_kesatu' => $pihakKesatu,
            'dasar_hukum_nasional' => $dasar,
        ];
    }
}
