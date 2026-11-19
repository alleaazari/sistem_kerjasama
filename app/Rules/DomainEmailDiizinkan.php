<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * Domain email PIC harus ada di config('kerjasama.email_domains').
 *
 * Format email diperiksa aturan `email` terpisah; aturan ini hanya soal
 * domain, dicocokkan persis (bukan "mengandung") agar domain tiruan seperti
 * "gmail.com.evil.com" tidak lolos.
 */
class DomainEmailDiizinkan implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = Str::lower(Str::afterLast((string) $value, '@'));

        /** @var array<int, string> $daftar */
        $daftar = (array) config('kerjasama.email_domains', []);

        foreach ($daftar as $izin) {
            $izin = Str::lower(trim((string) $izin));

            if ($izin === '') {
                continue;
            }

            $cocok = str_starts_with($izin, '.')
                ? $domain !== ltrim($izin, '.') && str_ends_with($domain, $izin)
                : $domain === $izin;

            if ($cocok) {
                return;
            }
        }

        $fail('Domain email tidak diizinkan. Gunakan Gmail, Yahoo, atau email kampus (.ac.id).');
    }
}
