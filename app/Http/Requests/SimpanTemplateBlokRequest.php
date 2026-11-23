<?php

namespace App\Http\Requests;

use App\Support\Mou\PendaftaranPlaceholder;
use App\Support\Mou\StrukturTemplateMou;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use JsonException;

/**
 * Validasi editor template MoU berbasis blok (Superadmin).
 *
 * Editor mengirim struktur blok sebagai satu medan JSON (`blok`). Server
 * memvalidasi ULANG struktur itu di sini — bentuk blok, tipe blok, jenis ayat,
 * tag HTML, dan placeholder asing ditolak. Nilai tersimpan selalu teks biasa;
 * escaping dilakukan saat dirender (Blade + PenandaIsi), bukan di sini.
 *
 * Client (editor) tidak dipercaya untuk urutan, tipe, maupun isi: apa pun yang
 * tidak lolos struktur akan ditolak dan template lama tidak berubah.
 */
class SimpanTemplateBlokRequest extends FormRequest
{
    /** Batas jumlah pasal dan panjang teks satu blok. */
    public const MAKS_PASAL = 60;

    public const MAKS_AYAT = 60;

    public const MAKS_TEKS = 20000;

    /** Tipe blok yang boleh disunting sebagai teks bebas. */
    public const TIPE_TEKS = ['sampul', 'pembukaan', 'menimbang', 'mengingat', 'penutup', 'tanda_tangan'];

    public function authorize(): bool
    {
        return true; // otorisasi peran ditangani middleware rute (admin.only:superadmin)
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:'.implode(',', StrukturTemplateMou::STATUS)],
            'blok' => ['required', 'string', 'max:'.(self::MAKS_TEKS * 4)],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama template wajib diisi.',
            'nama.max' => 'Nama template maksimal 255 karakter.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
            'status.required' => 'Status template wajib dipilih.',
            'status.in' => 'Status template harus "final" atau "draf".',
            'blok.required' => 'Struktur template tidak boleh kosong.',
        ];
    }

    /**
     * Baca dan validasi struktur blok yang dikirim editor.
     *
     * @return array<string, mixed> template siap disimpan (tanpa kode/versi)
     */
    public function template(): array
    {
        $blok = json_decode((string) $this->input('blok'), true, 64, JSON_THROW_ON_ERROR);

        /** @var list<array<string, mixed>> $blok */
        return [
            'nama' => trim((string) $this->input('nama')),
            'catatan' => trim((string) $this->input('catatan', '')),
            'status' => (string) $this->input('status'),
            'blok' => $blok,
        ];
    }

    /**
     * Pemeriksaan lanjutan: JSON sah, bentuk blok sah, teks & placeholder bersih.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $mentah = $this->input('blok');

                if (! is_string($mentah) || $mentah === '') {
                    return; // sudah ditangkap aturan `required`
                }

                try {
                    $blok = json_decode($mentah, true, 64, JSON_THROW_ON_ERROR);
                } catch (JsonException) {
                    $validator->errors()->add('blok', 'Struktur template tidak dapat dibaca (JSON tidak sah).');

                    return;
                }

                if (! is_array($blok)) {
                    $validator->errors()->add('blok', 'Struktur template harus berupa larik blok.');

                    return;
                }

                $template = [
                    'kode' => 'sementara',
                    'nama' => 'sementara',
                    'status' => (string) $this->input('status'),
                    'versi' => 1,
                    'blok' => $blok,
                ];

                foreach (StrukturTemplateMou::periksa($template) as $masalah) {
                    $validator->errors()->add('blok', $masalah);
                }

                $this->periksaBatas($validator, $blok);
                $this->periksaTeks($validator, $blok);
            },
        ];
    }

    /**
     * Batas jumlah pasal/ayat dan panjang tiap teks.
     *
     * @param  array<mixed>  $blok
     */
    private function periksaBatas(Validator $validator, array $blok): void
    {
        foreach ($blok as $satu) {
            if (! is_array($satu)) {
                continue;
            }

            if (($satu['tipe'] ?? null) === 'pasal') {
                $pasal = (array) ($satu['pasal'] ?? []);

                if (count($pasal) > self::MAKS_PASAL) {
                    $validator->errors()->add('blok', 'Jumlah pasal melebihi batas '.self::MAKS_PASAL.'.');
                }

                foreach ($pasal as $p) {
                    if (is_array($p) && count((array) ($p['ayat'] ?? [])) > self::MAKS_AYAT) {
                        $validator->errors()->add('blok', 'Jumlah ayat satu pasal melebihi batas '.self::MAKS_AYAT.'.');
                    }
                }
            }
        }
    }

    /**
     * Tolak tag HTML dan placeholder asing di SELURUH teks blok.
     *
     * @param  array<mixed>  $blok
     */
    private function periksaTeks(Validator $validator, array $blok): void
    {
        foreach ($this->kumpulkanTeks($blok) as $teks) {
            if (strlen($teks) > self::MAKS_TEKS) {
                $validator->errors()->add('blok', 'Satu bagian teks melebihi batas '.number_format(self::MAKS_TEKS, 0, ',', '.').' karakter.');

                return;
            }

            if (preg_match('/<\s*\/?\s*[a-zA-Z][^>]*>/', $teks) === 1) {
                $validator->errors()->add('blok', 'Teks tidak boleh memuat tag HTML.');

                return;
            }

            $asing = PendaftaranPlaceholder::yatim($teks);

            if ($asing !== []) {
                $validator->errors()->add(
                    'blok',
                    'Placeholder tidak dikenal: {{'.implode('}}, {{', $asing).'}}. Gunakan placeholder dari daftar yang tersedia.',
                );

                return;
            }
        }
    }

    /**
     * Semua teks di dalam blok (rekursif) — ayat bisa berupa string atau objek
     * { teks, opsional, aktif_bawaan }; objek diperiksa dari medan `teks`.
     *
     * @param  array<mixed>  $nilai
     * @return list<string>
     */
    private function kumpulkanTeks(array $nilai): array
    {
        $keluar = [];

        foreach ($nilai as $item) {
            if (is_array($item)) {
                $keluar = array_merge($keluar, $this->kumpulkanTeks($item));
            } elseif (is_string($item)) {
                $keluar[] = $item;
            }
        }

        return $keluar;
    }
}
