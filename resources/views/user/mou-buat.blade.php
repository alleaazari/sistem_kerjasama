@extends('layouts.user')
@push('styles')
    <x-asset path="assets/user/css/mou-dokumen.css" />
@endpush
@section('title', 'Buat MoU')

@section('content')
    <div>
        <a href="{{ route('mitra.mou') }}" class="reader-back">&larr; Daftar MoU</a>
    </div>

    <p class="mitra-lead">
        Buat MoU sesuai bidang kerja sama pengajuan Anda. Bidang dan template dipilih otomatis oleh sistem.
    </p>

    @if ($pesan !== null)
        <div class="mitra-alert mitra-alert-warning" role="status">
            <span>{{ $pesan }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="mitra-alert mitra-alert-warning" role="alert">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    @if ($template !== null)
        <section class="mitra-card" aria-labelledby="judul-buat-mou">
            <h2 class="mitra-card-title" id="judul-buat-mou">Buat MoU Bidang {{ $bidang['kode'] }}</h2>
            <p class="draf-mou-bidang">Template terpakai: <strong>{{ $template['nama'] }}</strong> ({{ $template['status'] }})</p>
        </section>

        {{-- Data pengajuan: terisi otomatis & hanya baca. --}}
        <section class="mitra-card" aria-labelledby="judul-data-pengajuan">
            <h3 class="mitra-card-title" id="judul-data-pengajuan">Data Pengajuan (otomatis)</h3>
            <p class="field-help">Data ini berasal dari pengajuan Anda dan tidak dapat diubah di sini.</p>
            <dl class="mou-ringkas">
                <dt>Instansi</dt><dd>{{ $pengajuan['instansi'] ?? $mitra['instansi'] ?? '-' }}</dd>
                <dt>Kota / Provinsi</dt><dd>{{ trim(($pengajuan['kota'] ?? '').' / '.($pengajuan['provinsi'] ?? ''), ' /') ?: '-' }}</dd>
                <dt>Alamat</dt><dd>{{ $pengajuan['alamat'] ?? $mitra['alamat'] ?? '-' }}</dd>
                <dt>PIC</dt><dd>{{ $pengajuan['pic'] ?? $mitra['pic'] ?? '-' }}</dd>
                <dt>Bidang</dt><dd>{{ $bidang['label'] }}</dd>
                <dt>Metode Pelatihan</dt><dd>{{ $pengajuan['metode_pelatihan'] ?? '-' }}</dd>
                <dt>Pembiayaan</dt><dd>{{ $pengajuan['pembiayaan'] ?? '-' }}</dd>
            </dl>
        </section>

        <form method="POST" action="{{ route('mitra.mou.buat.simpan') }}">
            @csrf
            <section class="mitra-card" aria-labelledby="judul-lengkapi">
                <h3 class="mitra-card-title" id="judul-lengkapi">Lengkapi Data MoU</h3>
                <p class="field-help">Bagian bertanda [ISI ...] pada pratinjau akan tersorot hingga dilengkapi.</p>

                <div class="form-grid">
                    @foreach ($fieldForm as $nama => $meta)
                        @php
                            $label = $meta['label'] ?? $nama;
                            $tipe = $meta['tipe'] ?? 'teks';
                            $maks = (int) ($meta['maks'] ?? 255);
                            $wajib = (bool) ($meta['wajib'] ?? false);
                            $penuh = $tipe === 'teks';
                        @endphp
                        <div class="field {{ $penuh ? 'field-full' : '' }}">
                            <label for="f-{{ $nama }}">{{ $label }}{{ $wajib ? ' *' : '' }}</label>

                            @if ($penuh)
                                <textarea id="f-{{ $nama }}" name="{{ $nama }}" rows="4" maxlength="{{ $maks }}"
                                          @if ($wajib) required @endif
                                          @error($nama) aria-invalid="true" @enderror>{{ old($nama, $form[$nama] ?? '') }}</textarea>
                            @else
                                <input id="f-{{ $nama }}" name="{{ $nama }}" type="{{ $tipe === 'date' ? 'date' : 'text' }}"
                                       maxlength="{{ $maks }}" @if ($wajib) required @endif
                                       value="{{ old($nama, $form[$nama] ?? '') }}"
                                       @error($nama) aria-invalid="true" @enderror>
                            @endif

                            @error($nama)
                                <span class="field-error">{{ $message }}</span>
                            @enderror
                        </div>
                    @endforeach
                </div>

                <div class="form-actions">
                    <button type="submit" class="mitra-btn mitra-btn-primary">Simpan sebagai draf</button>
                    <button type="button" class="mitra-btn mitra-btn-secondary" data-toggle-pratinjau aria-expanded="true" aria-controls="pratinjau-mou">
                        Sembunyikan pratinjau
                    </button>
                </div>
            </section>
        </form>

        <section class="mitra-card" id="pratinjau-mou" aria-labelledby="judul-pratinjau">
            <div class="mou-aksi-cetak">
                <h3 class="mitra-card-title" id="judul-pratinjau">Pratinjau Dokumen</h3>
                <div class="mou-aksi-cetak-tombol">
                    <button type="button" class="mitra-btn mitra-btn-secondary" onclick="window.print()">Cetak / Simpan PDF</button>
                    <button type="button" class="mitra-btn mitra-btn-secondary" disabled
                            title="Ekspor DOCX belum tersedia." aria-disabled="true"
                            data-tooltip="Ekspor DOCX belum tersedia.">Unduh DOCX</button>
                </div>
            </div>

            @if ($dokumen !== null)
                @include('user.partials.mou-dokumen', ['dokumen' => $dokumen])
            @else
                <p class="field-help">Pratinjau muncul setelah bidang dan template tersedia.</p>
            @endif
        </section>
    @endif
@endsection

@push('scripts')
    <x-asset path="assets/user/js/mou-generator.js" type="js" />
@endpush
