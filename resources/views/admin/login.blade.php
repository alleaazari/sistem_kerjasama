@extends('layouts.auth-public')
@section('title', $judulHalaman)

@section('content')
<div class="auth-card">
    <h1>{{ $judulHalaman }}</h1>
    <p class="auth-lead">Panel {{ $labelPeran }} Portal Kerja Sama Komdigi. Halaman ini tidak ditautkan dari portal publik.</p>

    @if (session('info'))
        <div class="auth-notice" role="status">{{ session('info') }}</div>
    @endif

    @if (session('login_error'))
        <div class="auth-alert" role="alert" id="galat-admin">{{ session('login_error') }}</div>
    @endif

    @if ($errors->any())
        <div class="auth-alert" role="alert" id="galat-admin-validasi">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $aksi }}" data-form-login>
        @csrf

        <div class="auth-field">
            <label for="u">Nama pengguna {{ Str::lower($labelPeran) }}</label>
            <div class="auth-input-wrap">
                <input type="text" id="u" name="username" value="{{ old('username') }}"
                       autocomplete="username" required autofocus maxlength="255">
            </div>
        </div>

        <div class="auth-field">
            <label for="p">Kata sandi</label>
            <div class="auth-input-wrap">
                <input type="password" id="p" name="password" class="auth-input-password"
                       autocomplete="current-password" required maxlength="255">
                <button type="button" class="auth-password-toggle auth-hide-nojs"
                        data-toggle-password="p" aria-pressed="false" aria-controls="p">
                    Tampilkan
                </button>
            </div>
        </div>

        <button type="submit" class="app-btn app-btn-primary app-btn-block auth-submit" data-submit>
            Masuk sebagai {{ $labelPeran }}
        </button>
    </form>

    <div class="auth-foot">
        <a href="{{ route('landing') }}">Lihat Portal Utama</a>
    </div>
</div>
@endsection
