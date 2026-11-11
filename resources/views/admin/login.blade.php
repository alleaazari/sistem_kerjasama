@extends('layouts.auth-public')
@section('title', 'Masuk Admin')

@section('content')
<div class="auth-card">
    <h1>Masuk Admin</h1>
    <p class="auth-lead">Panel administrator Portal Kerja Sama Komdigi. Halaman ini tidak ditautkan dari portal publik.</p>

    @if (session('info'))
        <div class="auth-notice" role="status">{{ session('info') }}</div>
    @endif

    @if (session('login_error'))
        <div class="auth-alert" role="alert" id="galat-admin">{{ session('login_error') }}</div>
    @endif

    @if ($errors->any())
        <div class="auth-alert" role="alert" id="galat-admin-validasi">{{ $errors->first() }}</div>
    @endif

    @if (config('app.debug'))
        <div class="auth-debug-hint">
            <strong>Catatan pengembangan.</strong> Kredensial akun demo tidak ditampilkan di antarmuka. Lihat berkas konfigurasi server saat pengembangan.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.post') }}" data-form-login>
        @csrf

        <div class="auth-field">
            <label for="u">Nama pengguna admin</label>
            <div class="auth-input-wrap">
                <input type="text" id="u" name="username" value="{{ old('username') }}"
                       autocomplete="username" required autofocus>
            </div>
        </div>

        <div class="auth-field">
            <label for="p">Kata sandi</label>
            <div class="auth-input-wrap">
                <input type="password" id="p" name="password" class="auth-input-password"
                       autocomplete="current-password" required>
                <button type="button" class="auth-password-toggle auth-hide-nojs"
                        data-toggle-password="p" aria-pressed="false" aria-controls="p">
                    Tampilkan
                </button>
            </div>
        </div>

        <button type="submit" class="app-btn app-btn-primary app-btn-block auth-submit" data-submit>
            Masuk ke Panel Admin
        </button>
    </form>

    <div class="auth-foot">
        <a href="{{ route('landing') }}">Lihat Portal Utama</a>
    </div>
</div>
@endsection
