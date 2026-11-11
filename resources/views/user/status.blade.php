@extends('layouts.user')
@section('title', 'Status dan Masa Aktif')

@php
    $kondisi = $status['kondisi'];
    $perluPerpanjang = in_array($kondisi, ['segera', 'berakhir'], true);
    $sudahDiajukan = ! empty($perpanjangan);
    $ambang = \App\Support\StatusKerjasama::ambangHari();
@endphp

@section('content')
    <p class="mitra-lead" data-anim="naik" data-kelompok="kepala">
        Ringkasan masa berlaku kerja sama instansi Anda beserta pengajuan perpanjangan.
    </p>

    {{-- Pengumuman: hanya pada kondisi Segera Berakhir / Berakhir. --}}
    @if ($kondisi === 'segera')
        <div class="mitra-alert mitra-alert-warning" role="status" data-anim="naik" data-kelompok="kepala">
            <span>
                Masa berlaku kerja sama instansi Anda berakhir pada <strong>{{ $status['selesai']->translatedFormat('d F Y') }}</strong>
                ({{ $status['keterangan'] }}). Ajukan perpanjangan sebelum tanggal tersebut.
                <a href="#form-perpanjangan" data-buka-perpanjangan>Ajukan perpanjangan</a>
            </span>
        </div>
    @elseif ($kondisi === 'berakhir')
        <div class="mitra-alert mitra-alert-danger" role="alert" data-anim="naik" data-kelompok="kepala">
            <span>
                Masa berlaku kerja sama instansi Anda telah berakhir pada <strong>{{ $status['selesai']->translatedFormat('d F Y') }}</strong>
                ({{ $status['keterangan'] }}). Ajukan perpanjangan untuk mengaktifkan kembali kemitraan.
                <a href="#form-perpanjangan" data-buka-perpanjangan>Ajukan perpanjangan</a>
            </span>
        </div>
    @endif

    <section class="mitra-card status-panel" data-kondisi="{{ $kondisi }}" aria-labelledby="judul-status"
             data-anim="naik" data-kelompok="status">
        <div class="status-panel-top">
            <div>
                <p class="status-eyebrow" id="judul-status">Status Kemitraan Komdigi</p>
                <h2 class="status-instansi">{{ $mitra['instansi'] }}</h2>
            </div>
            <span class="status-badge">{{ $status['label'] }}</span>
        </div>

        <div class="status-meta">
            <span>Masa berlaku: <b>{{ $status['mulai']?->translatedFormat('d F Y') ?? '—' }}</b> s/d <b>{{ $status['selesai']?->translatedFormat('d F Y') ?? '—' }}</b></span>
            <span class="status-remain">{{ $status['keterangan'] }}</span>
        </div>

        <p class="status-foot-note">
            Status ditetapkan dari sisa hari sampai tanggal berakhir. Sisa {{ $ambang }} hari atau kurang
            ditandai “Segera Berakhir”; tanggal yang sudah lewat ditandai “Berakhir”.
        </p>

        <div class="status-foot">
            <p class="status-foot-note">
                Periode kerja sama dihitung per tanggal sesuai zona waktu aplikasi. Data tanggal berasal dari pengajuan yang disetujui.
            </p>

            @if ($sudahDiajukan)
                <button type="button" class="mitra-btn mitra-btn-secondary" disabled
                        aria-describedby="ket-perpanjangan">
                    Perpanjangan Diajukan
                </button>
            @elseif ($perluPerpanjang)
                <button type="button" class="mitra-btn mitra-btn-primary" data-buka-perpanjangan>
                    Perpanjang Masa Aktif
                </button>
            @else
                <button type="button" class="mitra-btn mitra-btn-secondary" data-buka-perpanjangan>
                    Perpanjang Masa Aktif
                </button>
            @endif
        </div>

        @if ($sudahDiajukan)
            <p class="status-foot-note" id="ket-perpanjangan">
                Permohonan perpanjangan {{ $perpanjangan['durasi'] }} tahun diajukan pada {{ $perpanjangan['diajukan_pada'] }} dan sedang menunggu peninjauan admin.
            </p>
        @endif
    </section>

    <section class="mitra-card" aria-labelledby="judul-ringkas" data-anim="naik" data-kelompok="ringkas">
        <h2 class="mitra-card-title" id="judul-ringkas">Ringkasan Kemitraan</h2>
        <dl class="summary-grid">
            <div class="summary-item">
                <dt>Nama instansi</dt>
                <dd>{{ $mitra['instansi'] }}</dd>
            </div>
            <div class="summary-item">
                <dt>Unit organisasi</dt>
                <dd>{{ $mitra['organisasi'] }}</dd>
            </div>
            <div class="summary-item">
                <dt>Penanggung jawab (PIC)</dt>
                <dd>{{ $mitra['pic'] }}</dd>
            </div>
            <div class="summary-item">
                <dt>Email resmi</dt>
                <dd>{{ $mitra['email'] }}</dd>
            </div>
            <div class="summary-item">
                <dt>Telepon / WhatsApp</dt>
                <dd>{{ $mitra['telp'] }}</dd>
            </div>
            <div class="summary-item">
                <dt>Alamat instansi</dt>
                <dd>{{ $mitra['alamat'] }}</dd>
            </div>
        </dl>
    </section>

    {{-- Modal perpanjangan. Form memakai POST + CSRF ke route mitra.status.perpanjang. --}}
    <div class="mitra-modal" id="form-perpanjangan" role="dialog" aria-modal="true"
         aria-labelledby="judul-perpanjangan" hidden>
        <div class="mitra-modal-box">
            <div class="mitra-modal-head">
                <h2 class="mitra-modal-title" id="judul-perpanjangan">Ajukan Perpanjangan Masa Aktif</h2>
                <button type="button" class="mitra-modal-close" data-tutup-perpanjangan
                        aria-label="Tutup dialog perpanjangan">&times;</button>
            </div>

            <form method="POST" action="{{ route('mitra.status.perpanjang') }}" data-form-perpanjangan>
                @csrf

                <fieldset class="radiogroup">
                    <legend>Durasi perpanjangan</legend>
                    <label class="radio-row" for="durasi-1">
                        <input type="radio" id="durasi-1" name="durasi" value="1" checked required>
                        <span>1 tahun</span>
                    </label>
                    <label class="radio-row" for="durasi-2">
                        <input type="radio" id="durasi-2" name="durasi" value="2">
                        <span>2 tahun</span>
                    </label>
                    <label class="radio-row" for="durasi-3">
                        <input type="radio" id="durasi-3" name="durasi" value="3">
                        <span>3 tahun</span>
                    </label>
                </fieldset>

                <div class="field">
                    <label for="catatan">Catatan (opsional)</label>
                    <textarea id="catatan" name="catatan" rows="3" maxlength="1000"
                              aria-describedby="bantuan-catatan">{{ old('catatan') }}</textarea>
                    <span class="field-help" id="bantuan-catatan">Dasar atau pertimbangan perpanjangan, maksimal 1000 karakter.</span>
                    @error('catatan')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="mitra-btn mitra-btn-primary" data-submit>Kirim Permohonan</button>
                    <button type="button" class="mitra-btn mitra-btn-secondary" data-tutup-perpanjangan>Batalkan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('assets/user/js/mitra-status.js') }}" defer></script>
@endpush
