@extends('layouts.user')
@section('title', 'Draf MoU')

@section('content')
    <div>
        <a href="{{ route('mitra.mou') }}" class="reader-back" data-anim="naik" data-kelompok="mou">&larr; Daftar MoU</a>
    </div>

    <p class="mitra-lead" data-anim="naik" data-kelompok="draf-mou">
        Buat draf awal MoU sesuai bidang kerja sama pengajuan Anda, lalu sunting sebelum dikirim ke admin.
    </p>

    @if ($bidang['kode'] === '')
        <div class="mitra-alert mitra-alert-warning" role="status" data-anim="naik" data-kelompok="draf-mou">
            <span>Pengajuan kerja sama Anda belum memiliki bidang kerja sama, sehingga draf MoU belum dapat dibuat. Hubungi admin kerja sama untuk melengkapinya.</span>
        </div>
    @elseif ($template === null)
        <div class="mitra-alert mitra-alert-warning" role="status" data-anim="naik" data-kelompok="draf-mou">
            <span>Template MoU untuk bidang <strong>{{ $bidang['label'] !== '' ? $bidang['label'] : $bidang['kode'] }}</strong> belum tersedia. Hubungi admin kerja sama.</span>
        </div>
    @else
        <section class="mitra-card" aria-labelledby="judul-draf-mou" data-anim="naik" data-kelompok="draf-mou">
            <h2 class="mitra-card-title" id="judul-draf-mou">Draf MoU Bidang {{ $bidang['kode'] }}</h2>
            <p class="draf-mou-bidang">Bidang: <strong>{{ $bidang['label'] }}</strong></p>

            @if ($template['catatan'] !== '')
                <p class="field-help">{{ $template['catatan'] }}</p>
            @endif

            @if ($errors->any())
                <div class="mitra-alert mitra-alert-warning" role="alert">
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('mitra.mou.draf.simpan') }}">
                @csrf

                <div class="form-grid">
                    <div class="field field-full">
                        <label for="isi">Isi draf MoU</label>
                        <textarea id="isi" name="isi" rows="24" class="draf-mou-isi"
                                  required maxlength="{{ \App\Support\TemplateMou::MAKS_ISI }}"
                                  aria-describedby="bantuan-isi{{ $errors->has('isi') ? ' galat-isi' : '' }}"
                                  @error('isi') aria-invalid="true" @enderror>{{ old('isi', $draf) }}</textarea>
                        <span class="field-help" id="bantuan-isi">
                            Sunting teks di atas. Bagian bertanda [ISI: ...] wajib dilengkapi sebelum dikirim.
                            Maksimal {{ number_format(\App\Support\TemplateMou::MAKS_ISI, 0, ',', '.') }} karakter.
                        </span>
                        @error('isi')
                            <span class="field-error" id="galat-isi">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="aksi" value="simpan" class="mitra-btn mitra-btn-secondary">Simpan draf</button>
                    <button type="submit" name="aksi" value="kirim" class="mitra-btn mitra-btn-primary">Kirim ke admin</button>
                </div>
            </form>
        </section>
    @endif
@endsection
