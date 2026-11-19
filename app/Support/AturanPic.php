<?php

namespace App\Support;

use App\Rules\DomainEmailDiizinkan;

/**
 * Aturan validasi data PIC yang dipakai bersama oleh form pengajuan publik
 * dan form profil area mitra.
 */
final class AturanPic
{
    /** Huruf (termasuk beraksen), spasi, titik, koma, apostrof, strip. */
    public const POLA_NAMA = "/^[\\p{L}\\s.,'\\x{2019}-]+$/u";

    /** Hanya angka, 9 sampai 15 digit. */
    public const POLA_TELEPON = '/^[0-9]{9,15}$/';

    /**
     * Aturan untuk field PIC. Kolom `pic`/`jabatan_pic` di form lain boleh
     * tidak dikirim; `jabatan_pic` nullable agar aman dipakai di keduanya.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'pic' => ['required', 'string', 'max:100', 'regex:'.self::POLA_NAMA],
            'jabatan_pic' => ['nullable', 'string', 'max:100', 'regex:'.self::POLA_NAMA],
            'email' => ['required', 'string', 'email', 'max:255', new DomainEmailDiizinkan],
            'telp' => ['required', 'string', 'regex:'.self::POLA_TELEPON],
        ];
    }

    /** @return array<string, string> */
    public static function pesan(): array
    {
        return [
            'pic.required' => 'Nama lengkap PIC wajib diisi.',
            'pic.max' => 'Nama lengkap PIC maksimal 100 karakter.',
            'pic.regex' => 'Nama lengkap PIC hanya boleh berisi huruf, spasi, titik, koma, apostrof, dan strip.',
            'jabatan_pic.max' => 'Jabatan PIC maksimal 100 karakter.',
            'jabatan_pic.regex' => 'Jabatan PIC hanya boleh berisi huruf, spasi, titik, koma, apostrof, dan strip.',
            'email.required' => 'Email resmi PIC wajib diisi.',
            'email.email' => 'Format email PIC tidak valid.',
            'telp.required' => 'Nomor telepon / WhatsApp PIC wajib diisi.',
            'telp.regex' => 'Nomor telepon / WhatsApp PIC hanya boleh berisi angka, 9 sampai 15 digit.',
        ];
    }
}
