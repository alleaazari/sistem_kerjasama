<?php

namespace App\Http\Requests;

use App\Support\Mou\DrafMouMitra;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi simpan/kirim draf MoU mitra (edit per bagian).
 *
 * `bagian` = daftar teks bagian yang disunting mitra; `bagian.*.teks` boleh
 * memuat baris baru tetapi TIDAK memuat HTML. Panjang dan jumlah dibatasi.
 */
class SimpanDrafMouMitraRequest extends FormRequest
{
    /** Batas jumlah bagian yang boleh dikirim dalam satu simpanan. */
    public const MAKS_BAGIAN = 200;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'aksi' => ['required', 'in:simpan,kirim'],
            'judul' => ['nullable', 'string', 'max:255'],
            'bagian' => ['required', 'array', 'max:'.self::MAKS_BAGIAN],
            'bagian.*' => ['nullable', 'string', 'max:'.DrafMouMitra::MAKS_BAGIAN],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'aksi.required' => 'Pilih tindakan untuk draf MoU.',
            'aksi.in' => 'Tindakan draf MoU tidak dikenali.',
            'bagian.required' => 'Isi draf MoU tidak boleh kosong.',
            'bagian.max' => 'Terlalu banyak bagian yang dikirim.',
            'bagian.*.max' => 'Satu bagian melebihi batas '.number_format(DrafMouMitra::MAKS_BAGIAN, 0, ',', '.').' karakter.',
        ];
    }

    /**
     * Daftar bagian yang sudah dibersihkan (teks biasa, tanpa HTML).
     *
     * @return list<string>
     */
    public function bagianBersih(): array
    {
        $nilai = $this->input('bagian');

        if (! is_array($nilai)) {
            return [];
        }

        return array_values(array_map(
            static fn ($satu) => DrafMouMitra::bersihkanBagian($satu),
            array_slice($nilai, 0, self::MAKS_BAGIAN),
        ));
    }
}
