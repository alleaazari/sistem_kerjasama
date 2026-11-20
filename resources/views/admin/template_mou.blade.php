@extends('layouts.admin')
@section('title', 'Template MoU')

@section('content')
    {{-- Daftar bidang dari config: VSGA, GTA, dan bidang baru yang ditambahkan nanti. --}}
    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Template per bidang</h2>
            <p class="panel-sub">
                Generator MoU mitra memakai template sesuai bidang pengajuannya. Perubahan berlaku untuk draf
                berikutnya; draf yang sudah disimpan mitra tidak berubah.
            </p>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <caption class="sr-only">Daftar template MoU per bidang kerja sama</caption>
                <thead>
                    <tr>
                        <th>Bidang</th>
                        <th>Judul template</th>
                        <th>Versi</th>
                        <th class="col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $row)
                        <tr>
                            <td>
                                <span class="cell-strong">{{ $row['kode'] }}</span>
                                <span class="cell-faint cell-block">{{ $row['label'] !== '' ? $row['label'] : 'Label belum diisi' }}</span>
                            </td>
                            <td>
                                @if ($row['ada'])
                                    {{ $row['judul'] }}
                                @else
                                    <span class="cell-muted">Belum ada template</span>
                                @endif
                            </td>
                            <td>
                                @if ($row['ada'])
                                    <span class="badge">Versi {{ $row['versi'] }}</span>
                                @else
                                    <span class="cell-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn2 btn-s"
                                   href="{{ route('superadmin.template_mou', ['kode' => $row['kode']]) }}"
                                   @if ($row['kode'] === $dipilih) aria-current="page" @endif>
                                    {{ $row['kode'] === $dipilih ? 'Sedang disunting' : 'Sunting' }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Editor template terpilih --}}
    <div class="panel">
        <div class="panel-head">
            <div class="panel-head-row">
                <div>
                    <h2 class="panel-title">Sunting template {{ $dipilih }}</h2>
                    <p class="panel-sub">{{ $labelBidang !== '' ? $labelBidang : 'Label bidang belum diisi di config' }}</p>
                </div>
                <div>
                    @if ($versi > 0)
                        <span class="badge badge-success">Versi {{ $versi }}</span>
                    @else
                        <span class="badge badge-warning">Belum dibuat</span>
                    @endif
                </div>
            </div>
            @if ($diperbaruiPada !== null)
                <p class="cell-faint field-help-top">
                    Terakhir diubah
                    {{ \Carbon\CarbonImmutable::parse($diperbaruiPada)->translatedFormat('d F Y H:i') }}
                    oleh {{ $diperbaruiOleh !== '' ? $diperbaruiOleh : 'administrator' }}.
                </p>
            @endif
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('superadmin.template_mou.update', $dipilih) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <label for="judul">Judul template <span class="req">*</span></label>
                    <input type="text" id="judul" name="judul" value="{{ $judul }}"
                           required maxlength="255" autocomplete="off"
                           @error('judul') aria-invalid="true" @enderror>
                    @error('judul')
                        <span class="cell-faint">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <label for="catatan">Catatan penyusunan (opsional)</label>
                    <textarea id="catatan" name="catatan" rows="2" maxlength="500"
                              @error('catatan') aria-invalid="true" @enderror
                              placeholder="Contoh: bagian bertanda [ISI: ...] wajib dilengkapi mentor.">{{ $catatan }}</textarea>
                    @error('catatan')
                        <span class="cell-faint">{{ $message }}</span>
                    @enderror
                </div>

                <div class="template-grid">
                    <div>
                        <div class="row">
                            <label for="isi">Isi template <span class="req">*</span></label>
                            <p class="field-help" id="bantuan-isi">
                                Teks biasa, bukan HTML. Klik placeholder untuk menyisipkan di posisi kursor.
                                Bagian [ISI: ...] menandai hal yang belum pasti.
                            </p>
                            <div class="placeholder-row" role="group" aria-label="Daftar placeholder yang tersedia">
                                @foreach ($placeholders as $kunci => $keterangan)
                                    <button type="button" class="placeholder-chip"
                                            data-placeholder="{{ $kunci }}"
                                            aria-label="Sisipkan placeholder {{ $kunci }}: {{ $keterangan }}">
                                        &#123;&#123;{{ $kunci }}&#125;&#125;
                                    </button>
                                @endforeach
                            </div>
                            <textarea id="isi" name="isi" class="template-isi" rows="20"
                                      required maxlength="{{ \App\Support\TemplateMou::MAKS_ISI }}"
                                      aria-describedby="bantuan-isi"
                                      @error('isi') aria-invalid="true" @enderror>{{ $isi }}</textarea>
                            @error('isi')
                                <span class="cell-faint">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <div class="row">
                            <span class="template-preview-title" id="judul-pratinjau">Pratinjau</span>
                            <p class="field-help">Memakai data contoh, bukan data mitra.</p>
                            <pre id="pratinjau-template" class="mail-preview" tabindex="0"
                                 aria-labelledby="judul-pratinjau"
                                 data-contoh="{{ json_encode($contoh, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}">{{ $pratinjau }}</pre>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Simpan template</button>
                    @if ($template !== null)
                        <a class="btn btn2" href="{{ route('superadmin.template_mou', ['kode' => $dipilih]) }}">Batalkan perubahan</a>
                    @endif
                </div>
            </form>

            @if ($bawaanTersedia)
                <form method="POST" action="{{ route('superadmin.template_mou.reset', $dipilih) }}"
                      class="template-reset"
                      onsubmit="return confirm('Kembalikan template {{ $dipilih }} ke isi bawaan? Perubahan saat ini akan diganti dan tetap tercatat di riwayat.')">
                    @csrf
                    <button type="submit" class="btn btn2 btn-hapus">Kembalikan ke bawaan</button>
                    <span class="field-help">Isi saat ini diganti dengan bawaan sistem; versi baru tetap dicatat.</span>
                </form>
            @endif
        </div>
    </div>

    {{-- Riwayat dari log aktivitas: siapa, kapan, versi. --}}
    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Riwayat perubahan {{ $dipilih }}</h2>
            <p class="panel-sub">Delapan perubahan terakhir untuk bidang ini, diambil dari log aktivitas.</p>
        </div>
        @if (count($riwayat) === 0)
            <div class="panel-body">
                <p class="cell-muted">Belum ada perubahan yang tercatat untuk bidang ini.</p>
            </div>
        @else
            <div class="table-scroll">
                <table class="data-table">
                    <caption class="sr-only">Riwayat perubahan template {{ $dipilih }}</caption>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Oleh</th>
                            <th>Aksi</th>
                            <th>Rincian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $row)
                            <tr>
                                <td class="cell-muted">
                                    {{ \Carbon\CarbonImmutable::parse((string) $row['waktu'])->translatedFormat('d F Y H:i') }}
                                </td>
                                <td>
                                    {{ $row['nama'] ?? '-' }}
                                    <span class="cell-faint cell-block">{{ $row['peran'] ?? '' }}</span>
                                </td>
                                <td>{{ \App\Support\LogAktivitas::labelAksi((string) $row['aksi']) }}</td>
                                <td class="cell-muted">{{ $row['target'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- @verbatim: kurung kurawal ganda di JS bukan echo Blade. --}}
    @verbatim
    <script>
    (function () {
        var isi = document.getElementById('isi');
        var pratinjau = document.getElementById('pratinjau-template');
        if (!isi || !pratinjau) return;

        var contoh = {};
        try {
            contoh = JSON.parse(pratinjau.getAttribute('data-contoh') || '{}');
        } catch (e) {
            contoh = {};
        }

        function terapkan(teks) {
            return teks.replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, function (utuh, kunci) {
                return Object.prototype.hasOwnProperty.call(contoh, kunci) ? contoh[kunci] : utuh;
            });
        }

        function perbarui() {
            pratinjau.textContent = terapkan(isi.value);
        }

        isi.addEventListener('input', perbarui);

        Array.prototype.forEach.call(document.querySelectorAll('[data-placeholder]'), function (tombol) {
            tombol.addEventListener('click', function () {
                var token = '{' + '{' + tombol.getAttribute('data-placeholder') + '}' + '}';
                var mulai = isi.selectionStart;
                var akhir = isi.selectionEnd;

                if (typeof isi.setRangeText === 'function') {
                    isi.setRangeText(token, mulai, akhir, 'end');
                } else {
                    isi.value = isi.value.slice(0, mulai) + token + isi.value.slice(akhir);
                }

                isi.focus();
                perbarui();
            });
        });

        perbarui();
    })();
    </script>
    @endverbatim
@endsection
