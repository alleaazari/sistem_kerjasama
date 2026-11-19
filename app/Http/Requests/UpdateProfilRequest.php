<?php

namespace App\Http\Requests;

use App\Support\AturanPic;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Rapikan spasi tepi agar aturan PIC menilai nilai bersih. */
    protected function prepareForValidation(): void
    {
        $bersih = [];
        foreach (['instansi', 'organisasi', 'alamat', 'pic', 'jabatan_pic', 'email', 'telp'] as $field) {
            $nilai = $this->input($field);
            if (is_string($nilai)) {
                $bersih[$field] = trim($nilai);
            }
        }

        $this->merge($bersih);
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return array_merge([
            'instansi' => 'required|string|max:255',
            'organisasi' => 'required|string|max:255',
            'alamat' => 'required|string',
        ], AturanPic::rules());
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return array_merge(AturanPic::pesan(), [
            'instansi.required' => 'Nama instansi wajib diisi.',
            'organisasi.required' => 'Unit kerja wajib diisi.',
            'alamat.required' => 'Alamat wajib diisi.',
        ]);
    }
}
