<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

/**
 * Penyimpanan konten landing page yang dikelola admin.
 *
 * Satu sumber kebenaran: storage/app/landing_cms.json
 * Default: config/landing_cms.php
 * Session admin dipakai sebagai cache instan agar UI admin tetap responsif.
 */
class LandingCms
{
    /** Kunci yang dikelola lewat panel admin. */
    public const KEYS = ['artikel', 'galeri', 'petinggi', 'pegawai', 'visi', 'misi', 'hero_texts'];

    public function path(): string
    {
        return storage_path('app/landing_cms.json');
    }

    /**
     * Muat semua kunci. Urutan: session admin (cache) > file json > default config.
     */
    public function all(): array
    {
        $defaults = config('landing_cms', []);
        $stored = $this->stored();

        $result = [];
        foreach (static::KEYS as $key) {
            if (session()->has('admin_cms.'.$key)) {
                $result[$key] = session('admin_cms.'.$key);
            } elseif (isset($stored[$key]) && ! (is_array($stored[$key]) && $stored[$key] === [])) {
                $result[$key] = $stored[$key];
            } else {
                $result[$key] = $defaults[$key] ?? null;
            }
        }

        return $result;
    }

    /**
     * Simpan satu kunci ke file json + session (cache admin).
     */
    public function save(string $key, mixed $value): void
    {
        if (! in_array($key, static::KEYS, true)) {
            throw new \InvalidArgumentException("Kunci CMS tidak dikenal: {$key}");
        }

        $stored = $this->stored();
        $stored[$key] = $value;
        $this->write($stored);

        session(['admin_cms.'.$key => $value]);
    }

    /**
     * Hapus satu kunci: kembalikan ke default config.
     */
    public function reset(string $key): void
    {
        if (! in_array($key, static::KEYS, true)) {
            return;
        }

        $stored = $this->stored();
        unset($stored[$key]);
        $this->write($stored);

        session()->forget('admin_cms.'.$key);
    }

    protected function stored(): array
    {
        if (! File::exists($this->path())) {
            return [];
        }

        $data = json_decode((string) File::get($this->path()), true);

        return is_array($data) ? $data : [];
    }

    protected function write(array $data): void
    {
        $dir = dirname($this->path());
        if (! is_dir($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put($this->path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
