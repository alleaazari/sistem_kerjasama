<?php

namespace App\Http\Requests;

use App\Support\BidangKerjasama;
use App\Support\Mou\StrukturTemplateMou;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi pemetaan bidang kerja sama -> kode template (Superadmin).
 *
 * Hanya bidang dari config/kerjasama.php dan kode template yang benar-benar
 * ada yang diterima; nilai lain ditolak agar pemetaan tidak menunjuk template
 * yang tidak pernah ada.
 */
class SimpanPemetaanMouRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi peran ditangani middleware rute
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'pemetaan' => ['required', 'array'],
            'pemetaan.*' => ['nullable', 'string', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'pemetaan.required' => 'Pemetaan bidang tidak boleh kosong.',
        ];
    }

    /**
     * Pemetaan bersih: hanya bidang dari config dan kode template yang sah.
     * Kode kosong dianggap "kembali ke bawaan" (tidak dipetakan).
     *
     * @return array<string, string>
     */
    public function pemetaanBersih(): array
    {
        $bidangSah = BidangKerjasama::kode();
        $templateSah = array_keys(StrukturTemplateMou::semua());

        $bersih = [];

        foreach ((array) $this->input('pemetaan', []) as $bidang => $kode) {
            if (! in_array((string) $bidang, $bidangSah, true) || ! is_string($kode) || $kode === '') {
                continue;
            }

            if (! in_array($kode, $templateSah, true)) {
                continue;
            }

            $bersih[(string) $bidang] = $kode;
        }

        return $bersih;
    }
}
