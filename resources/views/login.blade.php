@extends('layouts.auth-public')
@section('title', 'Masuk Mitra')

@section('content')
<div class="auth-card">
    <h1>Masuk Mitra</h1>
    <p class="auth-lead">Gunakan nama pengguna dan kata sandi yang dikirimkan ke email PIC instansi Anda.</p>

    @if (session('info'))
        <div class="auth-notice" role="status">{{ session('info') }}</div>
    @endif

    @if (session('login_error') || $errors->any())
        <div class="auth-alert" role="alert" id="galat-masuk">{{ session('login_error') ?? $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login.post') }}" data-form-login>
        @csrf

        <div class="auth-field">
            <label for="username">Nama pengguna</label>
            <div class="auth-input-wrap">
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       autocomplete="username" required autofocus
                       @if ($errors->has('username')) aria-invalid="true" aria-describedby="galat-username" @endif>
            </div>
            @error('username')
                <span class="auth-error-text" id="galat-username">{{ $message }}</span>
            @enderror
        </div>

        <div class="auth-field">
            <label for="password">Kata sandi</label>
            <div class="auth-input-wrap">
                <input type="password" id="password" name="password" class="auth-input-password"
                       autocomplete="current-password" required
                       @if ($errors->has('password')) aria-invalid="true" aria-describedby="galat-password" @endif>
                <button type="button" class="auth-password-toggle auth-hide-nojs"
                        data-toggle-password="password" aria-pressed="false" aria-controls="password">
                    Tampilkan
                </button>
            </div>
            @error('password')
                <span class="auth-error-text" id="galat-password">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="app-btn app-btn-primary app-btn-block auth-submit" data-submit>
            Masuk
        </button>
    </form>

    <div class="auth-foot">
        <a href="{{ route('landing') }}">Lihat Portal Umum</a>
        <span>Belum mengajukan kemitraan? <a href="{{ route('pengajuan.create') }}">Ajukan Kerja Sama</a></span>
    </div>
</div>
@endsection
