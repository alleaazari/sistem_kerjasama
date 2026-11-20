<?php

namespace App\Http\Requests;

use App\Support\TemplateMou;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validasi editor template MoU (Superadmin).
 *
 * Teks template disimpan sebagai teks biasa: tag HTML dan placeholder asing
 * ditolak di sini, sedangkan escaping tetap dilakukan saat dirender.
 */
class SimpanTemplateMouRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'isi' => ['required', 'string', 'max:'.TemplateMou::MAKS_ISI],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'judul.required' => 'Judul template wajib diisi.',
            'judul.max' => 'Judul template maksimal 255 karakter.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
            'isi.required' => 'Isi template wajib diisi.',
            'isi.max' => 'Isi template maksimal '.number_format(TemplateMou::MAKS_ISI, 0, ',', '.').' karakter.',
        ];
    }

    /**
     * Aturan isi teks: tolak tag HTML dan placeholder di luar daftar dikenal.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                foreach (['judul', 'catatan', 'isi'] as $field) {
                    if ($validator->errors()->has($field)) {
                        continue;
                    }

                    $nilai = $this->input($field);

                    if (! is_string($nilai) || $nilai === '') {
                        continue;
                    }

                    if (TemplateMou::mengandungHtml($nilai)) {
                        $validator->errors()->add($field, 'Teks '.$field.' tidak boleh memuat tag HTML.');
                    }

                    $asing = TemplateMou::placeholderTidakDikenal($nilai);

                    if ($asing !== []) {
                        $validator->errors()->add(
                            $field,
                            'Placeholder tidak dikenal: {{'.implode('}}, {{', $asing).'}}. Gunakan placeholder dari daftar yang tersedia.',
                        );
                    }
                }
            },
        ];
    }
}
