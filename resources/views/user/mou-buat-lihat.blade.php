@extends('layouts.user')
@push('styles')
    <x-asset path="assets/user/css/mou-dokumen.css" />
@endpush
@section('title', 'Draf MoU')

@section('content')
    <div>
        <a href="{{ route('mitra.mou') }}" class="reader-back">&larr; Daftar MoU</a>
    </div>

    @php
        $bagian = [];
        foreach ($dokumen['blok'] as $blok) {
            $tipe = $blok['tipe'] ?? '';
            if (array_key_exists('paragraf', $blok) && is_array($blok['paragraf'])) {
                foreach ($blok['paragraf'] as $p) {
                    $bagian[] = (string) $p;
                }
            } elseif ($tipe === 'pasal' && is_array($blok['ayat'] ?? null)) {
                foreach ($blok['ayat'] as $a) {
                    $bagian[] = (string) $a;
                }
            }
        }
    @endphp

    <section class="mitra-card" aria-labelledby="judul-draf">
        <h2 class="mitra-card-title" id="judul-draf">{{ $draf['judul'] ?: 'Draf MoU' }}</h2>
        <p class="draf-mou-bidang">
            Bidang: <strong>{{ $draf['kode_bidang'] ?? '-' }}</strong> &middot;
            Status: <strong>{{ $draf['status'] === 'terkirim' ? 'Terkirim' : 'Draf' }}</strong> &middot;
            Versi: <strong>{{ (int) ($draf['versi'] ?? 1) }}</strong>
        </p>

        @if ($errors->any())
            <div class="mitra-alert mitra-alert-warning" role="alert">
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        @if ($draf['status'] === 'terkirim')
            <div class="mitra-alert mitra-alert-success" role="status">
                <span>Draf ini sudah dikirim ke admin dan menunggu peninjauan. Menyimpan lagi akan memperbarui versi draf.</span>
            </div>
        @endif

        <form method="POST" action="{{ route('mitra.mou.buat.update', $draf['id']) }}">
            @csrf

            <div class="form-grid">
                @foreach ($bagian as $i => $teks)
                    <div class="field field-full">
                        <label for="bagian-{{ $i }}">Bagian {{ $i + 1 }}</label>
                        <textarea id="bagian-{{ $i }}" name="bagian[{{ $i }}]" rows="4"
                                  maxlength="{{ \App\Support\Mou\DrafMouMitra::MAKS_BAGIAN }}">{{ old('bagian.'.$i, $teks) }}</textarea>
                    </div>
                @endforeach
            </div>

            <div class="form-actions">
                <button type="submit" name="aksi" value="simpan" class="mitra-btn mitra-btn-secondary">Simpan perubahan</button>
                <button type="submit" name="aksi" value="kirim" class="mitra-btn mitra-btn-primary">Kirim ke admin</button>
            </div>
        </form>
    </section>

    <section class="mitra-card" aria-labelledby="judul-riwayat">
        <h3 class="mitra-card-title" id="judul-riwayat">Riwayat Versi</h3>
        <ol class="mou-riwayat">
            @foreach (array_reverse((array) ($draf['riwayat'] ?? [])) as $riwayatSatu)
                <li>
                    <span class="mou-riwayat-versi">v{{ (int) ($riwayatSatu['versi'] ?? 0) }}</span>
                    <span>{{ $riwayatSatu['aksi'] ?? 'Diperbarui' }}</span>
                    <span class="mou-riwayat-waktu">{{ $riwayatSatu['pada'] ?? '' }}</span>
                </li>
            @endforeach
        </ol>
    </section>

    <section class="mitra-card" aria-labelledby="judul-pratinjau">
        <div class="mou-aksi-cetak">
            <h3 class="mitra-card-title" id="judul-pratinjau">Pratinjau Dokumen</h3>
            <div class="mou-aksi-cetak-tombol">
                <a href="{{ route('mitra.mou.buat.cetak', $draf['id']) }}" class="mitra-btn mitra-btn-secondary" target="_blank" rel="noopener">Pratinjau Cetak</a>
                <button type="button" class="mitra-btn mitra-btn-secondary" onclick="window.print()">Cetak / Simpan PDF</button>
                <button type="button" class="mitra-btn mitra-btn-secondary" disabled
                        title="Ekspor DOCX belum tersedia." aria-disabled="true"
                        data-tooltip="Ekspor DOCX belum tersedia.">Unduh DOCX</button>
            </div>
        </div>
        @include('user.partials.mou-dokumen', ['dokumen' => $dokumen])
    </section>
@endsection
