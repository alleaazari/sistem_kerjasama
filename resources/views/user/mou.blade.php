@extends('layouts.user')
@section('title', 'MoU')

@php
    $badgeClass = function (string $status) {
        return match ($status) {
            'Disetujui' => 'status-badge-ok',
            'Perlu Revisi' => 'status-badge-danger',
            default => 'status-badge-warn',
        };
    };
@endphp

@section('content')
    <p class="mitra-lead" data-anim="naik" data-kelompok="mou">Lihat MoU instansi Anda dan kirim dokumen MoU baru untuk ditinjau admin.</p>

    <section class="mitra-card" aria-labelledby="judul-daftar-mou" data-anim="naik" data-kelompok="mou">
        <h2 class="mitra-card-title" id="judul-daftar-mou">MoU Anda</h2>

        @if (count($daftar) === 0)
            <div class="empty-state">
                <p>Belum ada MoU yang dikirim. Gunakan formulir di bawah untuk mengirim dokumen MoU pertama Anda.</p>
            </div>
        @else
            {{-- Desktop: tabel. --}}
            <div class="table-wrap">
                <table class="mitra-table">
                    <caption class="sr-only">Daftar MoU milik instansi Anda</caption>
                    <thead>
                        <tr>
                            <th scope="col">Judul / Nomor</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($daftar as $row)
                            <tr>
                                <td>
                                    <span class="mou-judul">{{ $row['judul'] }}</span>
                                    <span class="mou-nomor">{{ $row['nomor'] }}</span>
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row['tanggal'])->translatedFormat('d F Y') }}</td>
                                <td>
                                    <span class="status-badge {{ $badgeClass($row['status']) }}">{{ $row['status'] }}</span>
                                </td>
                                <td>
                                    <div class="mou-actions">
                                        <a href="{{ route('mitra.mou.detail', $row['id']) }}" class="mitra-btn mitra-btn-secondary">Lihat</a>
                                        @if ($row['status'] === 'Disetujui' && ! empty($row['berkas']))
                                            <a href="{{ route('mitra.mou.unduh', $row['id']) }}" class="mitra-btn mitra-btn-secondary">Unduh</a>
                                        @else
                                            <span class="field-help">Unduh tersedia setelah disetujui.</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- HP: kartu berlabel, tanpa scroll horizontal. --}}
            <ul class="mou-cards">
                @foreach ($daftar as $row)
                    <li class="mou-card">
                        <dl>
                            <dt>Judul</dt>
                            <dd>{{ $row['judul'] }}</dd>
                            <dt>Nomor</dt>
                            <dd>{{ $row['nomor'] }}</dd>
                            <dt>Tanggal</dt>
                            <dd>{{ \Illuminate\Support\Carbon::parse($row['tanggal'])->translatedFormat('d F Y') }}</dd>
                            <dt>Status</dt>
                            <dd><span class="status-badge {{ $badgeClass($row['status']) }}">{{ $row['status'] }}</span></dd>
                        </dl>
                        <div class="mou-actions">
                            <a href="{{ route('mitra.mou.detail', $row['id']) }}" class="mitra-btn mitra-btn-secondary">Lihat</a>
                            @if ($row['status'] === 'Disetujui' && ! empty($row['berkas']))
                                <a href="{{ route('mitra.mou.unduh', $row['id']) }}" class="mitra-btn mitra-btn-secondary">Unduh</a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="mitra-card" aria-labelledby="judul-kirim-mou" data-anim="naik" data-kelompok="form-mou">
        <h2 class="mitra-card-title" id="judul-kirim-mou">Kirim MoU</h2>

        <form method="POST" action="{{ route('mitra.mou.kirim') }}" enctype="multipart/form-data" data-form-mou>
            @csrf

            <div class="form-grid">
                <div class="field field-full">
                    <label for="judul">Judul / nomor MoU</label>
                    <input type="text" id="judul" name="judul" value="{{ old('judul') }}"
                           required maxlength="255" autocomplete="off"
                           aria-describedby="bantuan-judul"
                           @error('judul') aria-invalid="true" aria-describedby="galat-judul" @enderror>
                    <span class="field-help" id="bantuan-judul">Contoh: Nota Kesepahaman Literasi Digital 045/KS/KOMDIGI/2026.</span>
                    @error('judul')
                        <span class="field-error" id="galat-judul">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field field-full">
                    <label for="keterangan">Keterangan (opsional)</label>
                    <textarea id="keterangan" name="keterangan" rows="3" maxlength="1000"
                              aria-describedby="bantuan-keterangan"
                              @error('keterangan') aria-invalid="true" aria-describedby="galat-keterangan" @enderror>{{ old('keterangan') }}</textarea>
                    <span class="field-help" id="bantuan-keterangan">Ringkasan isi atau nomor surat pengantar, maksimal 1000 karakter.</span>
                    @error('keterangan')
                        <span class="field-error" id="galat-keterangan">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field field-full">
                    <label for="berkas">Berkas MoU</label>
                    <input type="file" id="berkas" name="berkas" accept=".pdf,.docx" required
                           aria-describedby="bantuan-berkas"
                           @error('berkas') aria-invalid="true" aria-describedby="galat-berkas" @enderror>
                    <span class="field-help" id="bantuan-berkas">Format PDF atau DOCX, ukuran maksimal 5 MB.</span>
                    <span class="field-help" id="nama-berkas"></span>
                    @error('berkas')
                        <span class="field-error" id="galat-berkas">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="mitra-btn mitra-btn-primary" data-submit>Kirim MoU</button>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
<script src="{{ asset('assets/user/js/mitra-form.js') }}" defer></script>
@endpush
