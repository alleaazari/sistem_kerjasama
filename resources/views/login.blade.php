@extends('layouts.user-public')
@section('title', 'Masuk Portal Mitra')

@section('content')
<div class="wrap" style="max-width:420px;margin:24px auto 30px;">
    <div class="box">
        <h2>Masuk Portal Mitra Komdigi</h2>
        <div class="in">
            <!-- DEMO LOGIN QUICK FILL -->
            <div class="demo-box">
                <h4>Akun Demo Mitra (Klik untuk Isi Otomatis)</h4>
                <p style="font-size:12px;color:var(--text-muted);margin-bottom:8px;">Username: <b>user1</b> | Password: <b>123456</b></p>
                <button type="button" class="demo-btn" onclick="fillDemo('user1', '123456')" style="width:100%;">Gunakan Akun Demo (user1 / 123456)</button>
            </div>

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="row">
                    <label for="username">Username / Email Instansi</label>
                    <input type="text" id="username" name="username" placeholder="Masukkan username" required value="user1">
                </div>
                <div class="row">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" required value="123456">
                </div>
                <div style="margin-top:16px;">
                    <button class="btn" style="width:100%;padding:10px;">Masuk ke Dashboard Mitra</button>
                </div>
            </form>

            <div style="margin-top:16px;text-align:center;font-size:12px;color:var(--text-muted);border-top:1px solid var(--border-color);padding-top:12px;">
                Belum mengajukan kemitraan? <a href="{{ route('pengajuan.create') }}" style="font-weight:600;">Ajukan Kerja Sama Baru</a>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(user, pass) {
    document.getElementById('username').value = user;
    document.getElementById('password').value = pass;
}
</script>
@endsection
