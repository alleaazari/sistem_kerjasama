@extends('layouts.user')
@section('title', 'Profil Instansi')

@php
    $username = $mitra['username'] ?? 'mitra';
    $namaPj = (string) ($mitra['pic'] ?? '');
    $jabatan = (string) ($mitra['jabatan_pic'] ?? '');
    $instansi = (string) ($mitra['instansi'] ?? '');
    $unitKerja = (string) ($mitra['organisasi'] ?? '');
    $alamat = (string) ($mitra['alamat'] ?? '');
    $kota = (string) ($mitra['kota'] ?? '');
    $provinsi = (string) ($mitra['provinsi'] ?? '');
    $email = (string) ($mitra['email'] ?? '');
    $telp = (string) ($mitra['telp'] ?? '');
    $fotoPath = $mitra['foto'] ?? null;
    $fotoUrl = $fotoPath
        ? route('mitra.profile.foto') . '?v=' . rawurlencode((string) ($mitra['foto_versi'] ?? '1'))
        : null;
    $inisial = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(trim($namaPj !== '' ? $namaPj : $username), 0, 1));

    // Hanya field yang benar-benar ada isinya yang ditampilkan di modal biodata.
    $biodata = array_filter([
        'Nama penanggung jawab' => $namaPj,
        'Jabatan' => $jabatan,
        'Instansi' => $instansi,
        'Unit kerja' => $unitKerja,
        'Alamat' => $alamat,
        'Kota / kabupaten' => $kota,
        'Provinsi' => $provinsi,
        'Email' => $email,
        'Telepon / WhatsApp' => $telp,
        'Nama pengguna' => $username,
    ], fn ($nilai) => trim((string) $nilai) !== '');
@endphp

@section('content')
    <p class="mitra-lead" data-anim="naik" data-kelompok="profil">Data instansi dan penanggung jawab yang tercatat pada pengajuan kerja sama Anda.</p>

    {{-- Identitas + foto --}}
    <section class="mitra-card" aria-labelledby="judul-identitas" data-anim="naik" data-kelompok="profil">
        <h2 class="mitra-card-title" id="judul-identitas">Identitas Penanggung Jawab</h2>

        <div class="profil-head">
            <button type="button" class="profil-foto-btn" data-buka-biodata
                    aria-haspopup="dialog" aria-controls="modal-biodata"
                    aria-label="Buka biodata dan perbesar foto">
                @if ($fotoUrl)
                    <img class="profil-foto" src="{{ $fotoUrl }}" alt="Foto {{ $namaPj !== '' ? $namaPj : $username }}"
                         width="120" height="120">
                @else
                    <span class="profil-foto profil-foto-inisial" aria-hidden="true">{{ $inisial }}</span>
                @endif
            </button>

            <div class="profil-identitas">
                <h2>{{ $namaPj !== '' ? $namaPj : $username }}</h2>
                @if ($jabatan !== '')
                    <p>{{ $jabatan }}</p>
                @endif
                @if ($instansi !== '')
                    <p>{{ $instansi }}</p>
                @endif
                <span class="profil-username">Nama pengguna: <strong>{{ $username }}</strong></span>
            </div>
        </div>

        {{-- Unggah foto: form terpisah, disk privat, nama UUID, maks 2 MB. --}}
        <form class="profil-foto-form" method="POST" action="{{ route('mitra.profile.foto.unggah') }}"
              enctype="multipart/form-data" data-form-profil>
            @csrf

            <label for="foto">Ubah foto profil</label>
            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp"
                   aria-describedby="bantuan-foto"
                   @error('foto') aria-invalid="true" aria-describedby="galat-foto" @enderror>
            <span class="field-help" id="bantuan-foto">Format JPG, PNG, atau WEBP. Ukuran maksimal 2 MB. Foto tampil sebagai avatar bulat.</span>
            @error('foto')
                <span class="field-error" id="galat-foto">{{ $message }}</span>
            @enderror

            <img class="foto-pratinjau" id="foto-pratinjau" alt="Pratinjau foto yang akan diunggah" data-tampil="false">

            <div class="form-actions">
                <button type="submit" class="mitra-btn mitra-btn-primary" data-submit>Unggah Foto</button>
            </div>
        </form>
    </section>

    {{-- Ringkasan hanya baca --}}
    <section class="mitra-card" aria-labelledby="judul-ringkasan-profil" data-anim="naik" data-kelompok="profil">
        <h2 class="mitra-card-title" id="judul-ringkasan-profil">Ringkasan Data Pengajuan</h2>
        <dl class="summary-grid">
            <div class="summary-item">
                <dt>Nama instansi</dt>
                <dd>{{ $instansi !== '' ? $instansi : '—' }}</dd>
            </div>
            <div class="summary-item">
                <dt>Unit kerja / divisi</dt>
                <dd>{{ $unitKerja !== '' ? $unitKerja : '—' }}</dd>
            </div>
            <div class="summary-item">
                <dt>Nama PIC</dt>
                <dd>{{ $namaPj !== '' ? $namaPj : '—' }}</dd>
            </div>
            <div class="summary-item">
                <dt>Email resmi PIC</dt>
                <dd>{{ $email !== '' ? $email : '—' }}</dd>
            </div>
            <div class="summary-item">
                <dt>Telepon / WhatsApp PIC</dt>
                <dd>{{ $telp !== '' ? $telp : '—' }}</dd>
            </div>
            <div class="summary-item">
                <dt>Alamat kantor</dt>
                <dd>{{ $alamat !== '' ? $alamat : '—' }}</dd>
            </div>
            <div class="summary-item">
                <dt>Nama pengguna akun</dt>
                <dd>{{ $username }}</dd>
            </div>
            <div class="summary-item">
                <dt>Tanggal pengajuan</dt>
                <dd>{{ $mitra['diajukan'] ?? '—' }}</dd>
            </div>
        </dl>
    </section>

    {{-- Formulir edit (nama field & validasi tidak diubah) --}}
    <section class="mitra-card" aria-labelledby="judul-edit-profil" data-anim="naik" data-kelompok="form-profil">
        <h2 class="mitra-card-title" id="judul-edit-profil">Perbarui Data Instansi</h2>

        <form method="POST" action="{{ route('mitra.profile.update') }}" data-form-profil>
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label for="username">Nama pengguna akun</label>
                    <span class="field-static" id="username">{{ $username }}</span>
                    <span class="field-help">Nama pengguna dipakai untuk masuk dan tidak dapat diubah dari halaman ini.</span>
                </div>

                <div class="field">
                    <label for="instansi">Nama instansi / perusahaan</label>
                    <input type="text" id="instansi" name="instansi" value="{{ old('instansi', $mitra['instansi']) }}"
                           required maxlength="255" autocomplete="organization"
                           @error('instansi') aria-invalid="true" aria-describedby="galat-instansi" @enderror>
                    @error('instansi')
                        <span class="field-error" id="galat-instansi">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field field-full">
                    <label for="organisasi">Unit kerja / divisi / organisasi</label>
                    <input type="text" id="organisasi" name="organisasi" value="{{ old('organisasi', $mitra['organisasi']) }}"
                           required maxlength="255" autocomplete="organization-title"
                           @error('organisasi') aria-invalid="true" aria-describedby="galat-organisasi" @enderror>
                    @error('organisasi')
                        <span class="field-error" id="galat-organisasi">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="pic">Nama penanggung jawab (PIC)</label>
                    <input type="text" id="pic" name="pic" value="{{ old('pic', $mitra['pic']) }}"
                           required maxlength="255" autocomplete="name"
                           @error('pic') aria-invalid="true" aria-describedby="galat-pic" @enderror>
                    @error('pic')
                        <span class="field-error" id="galat-pic">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="email">Email resmi instansi</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $mitra['email']) }}"
                           required maxlength="255" autocomplete="email"
                           @error('email') aria-invalid="true" aria-describedby="galat-email" @enderror>
                    @error('email')
                        <span class="field-error" id="galat-email">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label for="telp">Nomor telepon / WhatsApp PIC</label>
                    <input type="tel" id="telp" name="telp" value="{{ old('telp', $mitra['telp']) }}"
                           required maxlength="30" autocomplete="tel"
                           @error('telp') aria-invalid="true" aria-describedby="galat-telp" @enderror>
                    @error('telp')
                        <span class="field-error" id="galat-telp">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field field-full">
                    <label for="alamat">Alamat lengkap kantor instansi</label>
                    <textarea id="alamat" name="alamat" rows="3" required
                              autocomplete="street-address"
                              @error('alamat') aria-invalid="true" aria-describedby="galat-alamat" @enderror>{{ old('alamat', $mitra['alamat']) }}</textarea>
                    @error('alamat')
                        <span class="field-error" id="galat-alamat">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="mitra-btn mitra-btn-primary" data-submit>Simpan Perubahan</button>
                <a href="{{ route('mitra.profile') }}" class="mitra-btn mitra-btn-secondary">Batalkan</a>
            </div>
        </form>
    </section>

    {{-- Modal biodata: teks diambil dari DOM apa adanya (tanpa HTML). --}}
    <div class="mitra-modal" id="modal-biodata" role="dialog" aria-modal="true"
         aria-labelledby="judul-biodata" hidden>
        <div class="mitra-modal-box mitra-modal-box-lebar">
            <div class="mitra-modal-head">
                <h2 class="mitra-modal-title" id="judul-biodata">Biodata Penanggung Jawab</h2>
                <button type="button" class="mitra-modal-close" data-tutup-biodata
                        aria-label="Tutup dialog biodata">&times;</button>
            </div>

            @if ($fotoUrl)
                <img class="biodata-foto" src="{{ $fotoUrl }}" alt="Foto {{ $namaPj !== '' ? $namaPj : $username }}"
                     width="160" height="160">
            @else
                <span class="biodata-foto profil-foto-inisial" aria-hidden="true">{{ $inisial }}</span>
            @endif

            <dl class="biodata-grid">
                @foreach ($biodata as $label => $nilai)
                    <div>
                        <dt>{{ $label }}</dt>
                        <dd>{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="form-actions">
                <button type="button" class="mitra-btn mitra-btn-secondary" data-tutup-biodata>Tutup</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/user/js/mitra-form.js') }}" defer></script>
@endpush
