{{--
    Partial dokumen MoU resmi.
    Variabel: $dokumen (kode, nama, status, judul, blok).

    Semua teks dirender dengan {{ }} (ter-escape otomatis). Tidak ada {!! !!}
    dan tidak ada innerHTML: isi dokumen selalu berasal dari data tervalidasi
    server + template, bukan HTML mentah.
--}}
<article class="mou-dokumen" aria-label="Pratinjau dokumen MoU">
    @foreach ($dokumen['blok'] as $blok)
        @php $tipe = $blok['tipe'] ?? ''; @endphp

        @if ($tipe === 'sampul')
            <section class="mou-sampul">
                @foreach ($blok['paragraf'] as $baris)
                    @if (trim($baris) === '')
                        <div class="mou-sampul-jeda"></div>
                    @else
                        <p class="mou-sampul-baris">{{ $baris }}</p>
                    @endif
                @endforeach
            </section>

        @elseif ($tipe === 'pasal')
            <section class="mou-pasal">
                <h3 class="mou-judul-pasal">
                    Pasal {{ $blok['nomor'] }}<br>{{ $blok['judul'] }}
                </h3>
                @foreach ($blok['ayat'] as $ayat)
                    @foreach (preg_split('/\r\n|\r|\n/', (string) $ayat) as $barisAyat)
                        @php $barisAyat = (string) $barisAyat; $isiKosong = trim($barisAyat) === ''; @endphp
                        @if ($isiKosong)
                            <div class="mou-jeda"></div>
                        @else
                            <p class="mou-ayat">{!! \App\Support\Mou\PenandaIsi::sorot($barisAyat) !!}</p>
                        @endif
                    @endforeach
                @endforeach
                @if (($blok['catatan'] ?? '') !== '')
                    <p class="mou-catatan">{{ $blok['catatan'] }}</p>
                @endif
            </section>

        @elseif ($tipe === 'lampiran')
            <section class="mou-lampiran">
                <h3 class="mou-judul-lampiran">{{ $blok['judul'] }}</h3>
                <div class="mou-tabel-bungkus">
                    <table class="mou-tabel">
                        <thead>
                            <tr>
                                @foreach ($blok['kolom'] as $kolom)
                                    <th scope="col">{{ $kolom }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($blok['baris'] as $baris)
                                <tr>
                                    @foreach ($baris as $sel)
                                        <td>{{ $sel }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    @foreach ($blok['kolom'] as $kolom)
                                        <td class="mou-sel-kosong">[ISI]</td>
                                    @endforeach
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        @elseif ($tipe === 'tanda_tangan')
            @php
                // Pisahkan bagian PIHAK KESATU dan PIHAK KEDUA untuk tata dua kolom.
                $teks = implode("\n", array_map('strval', $blok['paragraf']));
                $pisah = preg_split('/PIHAK KEDUA/u', $teks, 2);
                $kesatu = trim((string) preg_replace('/PIHAK KESATU/u', '', (string) ($pisah[0] ?? '')));
                $kedua = trim((string) ($pisah[1] ?? ''));
            @endphp
            <section class="mou-ttd">
                <div class="mou-ttd-kolom">
                    <p class="mou-ttd-nama-pihak">PIHAK KEDUA</p>
                    @foreach (preg_split('/\r\n|\r|\n/', $kedua) as $baris)
                        @if (trim((string) $baris) !== '')
                            <p class="mou-ttd-baris">{{ $baris }}</p>
                        @endif
                    @endforeach
                </div>
                <div class="mou-ttd-kolom">
                    <p class="mou-ttd-nama-pihak">PIHAK KESATU</p>
                    @foreach (preg_split('/\r\n|\r|\n/', $kesatu) as $baris)
                        @if (trim((string) $baris) !== '')
                            <p class="mou-ttd-baris">{{ $baris }}</p>
                        @endif
                    @endforeach
                </div>
            </section>

        @else
            {{-- pembukaan / menimbang / mengingat / penutup --}}
            <section class="mou-blok mou-blok-{{ $tipe }}">
                @foreach ($blok['paragraf'] as $baris)
                    @php $baris = (string) $baris; @endphp
                    @if (trim($baris) === '')
                        <div class="mou-jeda"></div>
                    @else
                        <p class="mou-paragraf">{!! \App\Support\Mou\PenandaIsi::sorot($baris) !!}</p>
                    @endif
                @endforeach
            </section>
        @endif
    @endforeach
</article>
