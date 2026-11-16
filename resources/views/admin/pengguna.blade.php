@extends('layouts.admin')
@section('title', 'Kelola Pengguna')

@section('content')
<div class="panel">
    <div class="panel-head">
        <div class="panel-head-row">
            <div>
                <h2 class="panel-title">Daftar Akun Panel</h2>
                <p class="panel-sub">
                    Akun yang dapat masuk ke panel. Kata sandi tidak pernah ditampilkan karena
                    hanya tersimpan sebagai hash.
                </p>
            </div>
        </div>
    </div>

    {{-- TODO-BACKEND: aksi (tambah, ubah peran, reset sandi, nonaktifkan) menunggu
         tabel `users` + kolom role. Tombolnya sengaja dinonaktifkan. --}}
    <div class="panel-body">
        <div class="note-box">
            <p>
                <b>Pengelolaan akun belum tersedia.</b>
                Saat ini akun dibaca dari konfigurasi server. Setelah backend pengguna selesai,
                tombol di bawah akan aktif.
            </p>
        </div>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="table-pengguna">
            <caption class="sr-only">Daftar akun panel beserta peran dan statusnya</caption>
            <thead>
                <tr>
                    <th scope="col">Nama</th>
                    <th scope="col">Nama Pengguna</th>
                    <th scope="col">Peran</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($daftar as $akun)
                <tr>
                    <td><b class="cell-strong">{{ $akun['nama'] }}</b></td>
                    <td class="cell-muted">
                        @if ($akun['username'] !== '')
                            {{ $akun['username'] }}
                        @else
                            <span class="cell-faint">Belum diatur di server</span>
                        @endif
                    </td>
                    <td>{{ $akun['label'] }}</td>
                    <td>
                        @if ($akun['aktif'])
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-warning">Kredensial belum diatur</span>
                        @endif
                    </td>
                    <td>
                        <div class="table-actions">
                            {{-- TODO-BACKEND: aktifkan setelah tabel `users` ada. --}}
                            <button type="button" class="btn btn2 btn-s" disabled
                                    title="Menunggu backend: tabel pengguna belum tersedia">
                                Ubah
                            </button>
                            <button type="button" class="btn btn2 btn-s" disabled
                                    title="Menunggu backend: reset kata sandi belum tersedia">
                                Reset Sandi
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="cell-empty">Belum ada akun panel yang terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
