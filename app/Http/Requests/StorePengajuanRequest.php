<?php

namespace App\Http\Requests;

use App\Support\AturanPic;
use App\Support\Wilayah;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePengajuanRequest extends FormRequest
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
            'jenis_instansi' => 'required|string',
            'organisasi' => 'required|string|max:255',
            'provinsi' => ['required', 'string', 'max:100', Rule::in(Wilayah::provinsi())],
            'kota' => ['required', 'string', 'max:100', function (string $attribute, mixed $value, Closure $fail): void {
                if (! Wilayah::valid($this->input('provinsi'), is_string($value) ? $value : null)) {
                    $fail('Kota / Kabupaten tidak terdaftar pada provinsi yang dipilih.');
                }
            }],
            'alamat' => 'required|string',
            'metode_pelatihan' => 'required|string',
            'pembiayaan' => 'required|string',
        ], AturanPic::rules());
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return array_merge(AturanPic::pesan(), [
            'instansi.required' => 'Nama Perguruan Tinggi / Instansi wajib diisi.',
            'jenis_instansi.required' => 'Jenis instansi wajib dipilih.',
            'organisasi.required' => 'Unit Kerja / Divisi / Fakultas wajib diisi.',
            'provinsi.required' => 'Provinsi wajib dipilih.',
            'provinsi.in' => 'Provinsi tidak dikenali.',
            'kota.required' => 'Kota / Kabupaten wajib dipilih.',
            'alamat.required' => 'Alamat lengkap instansi wajib diisi.',
            'metode_pelatihan.required' => 'Metode pelatihan wajib dipilih.',
            'pembiayaan.required' => 'Skema pembiayaan (APBD / APBN / Cost Sharing) wajib dipilih.',
        ]);
    }
}
