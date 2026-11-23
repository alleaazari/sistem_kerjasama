<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi form "Lengkapi Data MoU" (area mitra).
 *
 * Field dan batas panjang dibaca dari config('mou.form_lengkapi') supaya satu
 * sumber dipakai bersama view. Nilai dari pengajuan (instansi, PIC, alamat,
 * bidang, pembiayaan) TIDAK diterima di sini: server selalu memakai data
 * pengajuan milik mitra.
 */
class LengkapiDataMouRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        $rules = [];

        foreach ((array) config('mou.form_lengkapi', []) as $nama => $meta) {
            $maks = (int) ($meta['maks'] ?? 255);
            $wajib = (bool) ($meta['wajib'] ?? false);
            $tipe = (string) ($meta['tipe'] ?? 'teks');

            $field = [$wajib ? 'required' : 'nullable', 'string', 'max:'.$maks];

            if ($tipe === 'date') {
                $field = [$wajib ? 'required' : 'nullable', 'date', 'max:'.$maks];
            }

            $rules[$nama] = $field;
        }

        $rules['email_instansi'][] = 'email:rfc';

        return $rules;
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        $pesan = [];

        foreach ((array) config('mou.form_lengkapi', []) as $nama => $meta) {
            $label = (string) ($meta['label'] ?? $nama);
            $pesan[$nama.'.required'] = $label.' wajib diisi.';
            $pesan[$nama.'.max'] = $label.' terlalu panjang.';
            $pesan[$nama.'.date'] = $label.' harus berupa tanggal yang sah.';
        }

        $pesan['email_instansi.email'] = 'Surel korespondensi harus berupa alamat surel yang sah.';

        return $pesan;
    }

    /**
     * Hanya nilai dari field resmi yang diambil (whitelist config), dipangkas,
     * dibersihkan dari tag HTML.
     *
     * @return array<string, string>
     */
    public function nilaiBersih(): array
    {
        $keluar = [];

        foreach (array_keys((array) config('mou.form_lengkapi', [])) as $nama) {
            $nilai = $this->input($nama);
            $keluar[$nama] = is_string($nilai) ? trim(strip_tags($nilai)) : '';
        }

        return $keluar;
    }
}
