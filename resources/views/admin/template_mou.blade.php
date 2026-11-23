@extends('layouts.admin')
@section('title', 'Template MoU')

@php
    $statusLabel = fn (string $s): string => $s === 'final' ? 'Final' : 'Draf';
@endphp

@section('content')
    <x-asset path="assets/user/css/mou-dokumen.css" />

    {{-- Daftar template (blok) yang tersedia di storage/app/mou. --}}
    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Daftar template MoU</h2>
            <p class="panel-sub">
                Template berbasis blok. Generator MoU mitra memakai template sesuai bidang pengajuannya. Perubahan
                berlaku untuk draf berikutnya; draf mitra yang sudah disimpan tidak berubah.
            </p>
        </div>
        <div class="table-scroll">
            <table class="data-table">
                <caption class="sr-only">Daftar template MoU</caption>
                <thead>
                    <tr>
                        <th>Nama / kode</th>
                        <th>Bidang terpetakan</th>
                        <th>Status</th>
                        <th>Versi</th>
                        <th>Terakhir diubah</th>
                        <th class="col-aksi">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftar as $row)
                        <tr>
                            <td>
                                <span class="cell-strong">{{ $row['nama'] !== '' ? $row['nama'] : $row['kode'] }}</span>
                                <span class="cell-faint cell-block">{{ $row['kode'] }}</span>
                            </td>
                            <td>
                                @if ($row['bidang'] !== '')
                                    <span class="cell-strong">{{ $row['bidang'] }}</span>
                                @else
                                    <span class="cell-muted">Belum dipetakan</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $row['status'] === 'final' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $statusLabel($row['status']) }}
                                </span>
                            </td>
                            <td><span class="badge">Versi {{ $row['versi'] }}</span></td>
                            <td class="cell-muted">
                                @if ($row['diperbarui_pada'])
                                    {{ \Carbon\CarbonImmutable::parse($row['diperbarui_pada'])->translatedFormat('d F Y H:i') }}
                                    <span class="cell-faint cell-block">{{ $row['diperbarui_oleh'] !== '' ? $row['diperbarui_oleh'] : 'administrator' }}</span>
                                @else
                                    -
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

    {{-- Editor blok template terpilih. --}}
    <div class="panel">
        <div class="panel-head">
            <div class="panel-head-row">
                <div>
                    <h2 class="panel-title">Sunting template {{ $dipilih }}</h2>
                    <p class="panel-sub">
                        Status saat ini:
                        <strong>{{ $statusLabel((string) ($template['status'] ?? 'draf')) }}</strong>.
                        Hanya template <strong>final</strong> yang dapat dipakai generator mitra.
                    </p>
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
            @if (count($statusTerpakai) > 0)
                <p class="cell-faint field-help-top">
                    Dipakai oleh bidang: <strong>{{ implode(', ', $statusTerpakai) }}</strong>.
                    Mengubah template ini memengaruhi draf <em>berikutnya</em> untuk bidang tersebut.
                </p>
            @endif
        </div>
        <div class="panel-body">
            @if ($template === null)
                <p class="cell-muted">Template {{ $dipilih }} tidak ditemukan.</p>
            @else
                <form method="POST" action="{{ route('superadmin.template_mou.update', $dipilih) }}"
                      id="form-template-mou">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <label for="nama">Nama template <span class="req">*</span></label>
                        <input type="text" id="nama" name="nama" value="{{ $nama }}"
                               required maxlength="255" autocomplete="off"
                               @error('nama') aria-invalid="true" @enderror>
                        @error('nama') <span class="cell-faint">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <label for="status">Status <span class="req">*</span></label>
                        <select id="status" name="status" required @error('status') aria-invalid="true" @enderror>
                            <option value="draf" @selected($status === 'draf')>Draf (belum dapat dipakai mitra)</option>
                            <option value="final" @selected($status === 'final')>Final (dapat dipakai generator)</option>
                        </select>
                        @error('status') <span class="cell-faint">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <label for="catatan">Catatan penyusunan (opsional)</label>
                        <textarea id="catatan" name="catatan" rows="2" maxlength="500"
                                  @error('catatan') aria-invalid="true" @enderror>{{ $catatan }}</textarea>
                        @error('catatan') <span class="cell-faint">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <span class="template-preview-title">Placeholder tersedia</span>
                        <p class="field-help">
                            Klik untuk menyalin. Hanya placeholder terdaftar yang diterima server.
                        </p>
                        <div class="placeholder-row" role="group" aria-label="Daftar placeholder tersedia">
                            @foreach ($placeholder as $kunci => $meta)
                                <button type="button" class="placeholder-chip"
                                        data-token="{{ $kunci }}"
                                        title="{{ $meta['keterangan'] ?? '' }}"
                                        aria-label="Salin placeholder {{ $kunci }}">
                                    &#123;&#123;{{ $kunci }}&#125;&#125;
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <input type="hidden" id="blok" name="blok"
                           value="{{ json_encode($template['blok'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                    @error('blok') <span class="cell-faint">{{ $message }}</span> @enderror

                    <div id="editor-blok" data-blok="{{ $blokJson }}"></div>

                    <div class="form-actions">
                        <button type="submit" class="btn">Simpan template</button>
                        <a class="btn btn2" href="{{ route('superadmin.template_mou', ['kode' => $dipilih]) }}">Batalkan perubahan</a>
                        <span class="field-help-top cell-faint" id="status-editor" role="status" aria-live="polite"></span>
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
            @endif
        </div>
    </div>

    {{-- Pratinjau dokumen memakai renderer yang sama dengan generator mitra. --}}
    @if ($pratinjau !== null)
        <div class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Pratinjau dokumen</h2>
                <p class="panel-sub">Memakai data contoh dan renderer yang sama dengan generator mitra.</p>
            </div>
            <div class="panel-body">
                @include('user.partials.mou-dokumen', ['dokumen' => $pratinjau])
            </div>
        </div>
    @endif

    {{-- Pengaturan global: Pihak Kesatu + dasar hukum nasional. --}}
    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Pengaturan Pihak Kesatu &amp; dasar hukum</h2>
            <p class="panel-sub">Nilai ini dipakai seluruh template. Disimpan sebagai penimpa di atas config.</p>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('superadmin.template_mou.pengaturan') }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <label for="pk-nama">Nama unit Pihak Kesatu <span class="req">*</span></label>
                    <input type="text" id="pk-nama" name="pihak_kesatu[nama_unit]" required maxlength="255"
                           value="{{ old('pihak_kesatu.nama_unit', $pengaturan['pihak_kesatu']['nama_unit'] ?? '') }}">
                    @error('pihak_kesatu.nama_unit') <span class="cell-faint">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <label for="pk-alamat">Alamat Pihak Kesatu <span class="req">*</span></label>
                    <input type="text" id="pk-alamat" name="pihak_kesatu[alamat]" required maxlength="255"
                           value="{{ old('pihak_kesatu.alamat', $pengaturan['pihak_kesatu']['alamat'] ?? '') }}">
                    @error('pihak_kesatu.alamat') <span class="cell-faint">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <label for="pk-telepon">Telepon Pihak Kesatu</label>
                    <input type="text" id="pk-telepon" name="pihak_kesatu[telepon]" maxlength="60"
                           value="{{ old('pihak_kesatu.telepon', $pengaturan['pihak_kesatu']['telepon'] ?? '') }}">
                    @error('pihak_kesatu.telepon') <span class="cell-faint">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <label for="pk-email">Surel Pihak Kesatu</label>
                    <input type="email" id="pk-email" name="pihak_kesatu[email]" maxlength="150"
                           value="{{ old('pihak_kesatu.email', $pengaturan['pihak_kesatu']['email'] ?? '') }}">
                    @error('pihak_kesatu.email') <span class="cell-faint">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <label for="pk-penandatangan">Nama penandatangan Pihak Kesatu <span class="req">*</span></label>
                    <input type="text" id="pk-penandatangan" name="pihak_kesatu[penandatangan]" required maxlength="150"
                           value="{{ old('pihak_kesatu.penandatangan', $pengaturan['pihak_kesatu']['penandatangan'] ?? '') }}">
                    @error('pihak_kesatu.penandatangan') <span class="cell-faint">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <label for="pk-jabatan">Jabatan penandatangan Pihak Kesatu <span class="req">*</span></label>
                    <input type="text" id="pk-jabatan" name="pihak_kesatu[jabatan]" required maxlength="150"
                           value="{{ old('pihak_kesatu.jabatan', $pengaturan['pihak_kesatu']['jabatan'] ?? '') }}">
                    @error('pihak_kesatu.jabatan') <span class="cell-faint">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <label for="pk-pelaksana">Pejabat pelaksana Pihak Kesatu <span class="req">*</span></label>
                    <input type="text" id="pk-pelaksana" name="pihak_kesatu[pejabat_pelaksana]" required maxlength="200"
                           value="{{ old('pihak_kesatu.pejabat_pelaksana', $pengaturan['pihak_kesatu']['pejabat_pelaksana'] ?? '') }}">
                    @error('pihak_kesatu.pejabat_pelaksana') <span class="cell-faint">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                    <label for="dasar-hukum">Dasar hukum nasional (satu per baris)</label>
                    <textarea id="dasar-hukum" name="dasar_hukum_nasional" rows="7" maxlength="4000">{{ old('dasar_hukum_nasional', implode("\n", $pengaturan['dasar_hukum_nasional'] ?? [])) }}</textarea>
                    @error('dasar_hukum_nasional') <span class="cell-faint">{{ $message }}</span> @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Simpan pengaturan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Pemetaan bidang -> template. --}}
    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Pemetaan bidang ke template</h2>
            <p class="panel-sub">Bidang yang tidak dipetakan memakai template <strong>kerangka_umum</strong>.</p>
        </div>
        <div class="panel-body">
            <form method="POST" action="{{ route('superadmin.template_mou.pemetaan') }}">
                @csrf
                @method('PUT')

                <div class="row" style="max-width:520px;">
                    @foreach ($daftarBidang as $kodeBidang => $labelBidang)
                        <div class="row">
                            <label for="map-{{ $kodeBidang }}">{{ $labelBidang }} ({{ $kodeBidang }})</label>
                            <select id="map-{{ $kodeBidang }}" name="pemetaan[{{ $kodeBidang }}]">
                                <option value="">— kerangka_umum (bawaan) —</option>
                                @foreach ($daftar as $tmpl)
                                    <option value="{{ $tmpl['kode'] }}"
                                        @selected(($pemetaan[$kodeBidang] ?? '') === $tmpl['kode'])>
                                        {{ $tmpl['nama'] !== '' ? $tmpl['nama'] : $tmpl['kode'] }}
                                        ({{ $statusLabel($tmpl['status']) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn">Simpan pemetaan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Riwayat dari log aktivitas. --}}
    <div class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Riwayat perubahan {{ $dipilih }}</h2>
            <p class="panel-sub">Delapan perubahan terakhir untuk template ini, diambil dari log aktivitas.</p>
        </div>
        @if (count($riwayat) === 0)
            <div class="panel-body">
                <p class="cell-muted">Belum ada perubahan yang tercatat untuk template ini.</p>
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

    @verbatim
    <script>
    (function () {
        var editor = document.getElementById('editor-blok');
        var medanBlok = document.getElementById('blok');
        var status = document.getElementById('status-editor');
        if (!editor || !medanBlok) return;

        var blok = [];
        try {
            blok = JSON.parse(editor.getAttribute('data-blok') || '[]');
        } catch (e) {
            blok = [];
        }
        if (!Array.isArray(blok)) blok = [];

        var TIPE_TEKS = ['sampul', 'pembukaan', 'menimbang', 'mengingat', 'penutup', 'tanda_tangan'];
        var LABEL = {
            sampul: 'Sampul', pembukaan: 'Pembukaan', menimbang: 'Menimbang',
            mengingat: 'Mengingat', penutup: 'Penutup', tanda_tangan: 'Tanda tangan'
        };

        function simpan() {
            medanBlok.value = JSON.stringify(blok);
        }

        function beriTahu() {
            status.textContent = 'Perubahan belum disimpan.';
        }

        function el(tag, kelas, teks) {
            var n = document.createElement(tag);
            if (kelas) n.className = kelas;
            if (teks != null) n.textContent = teks;
            return n;
        }

        function tombol(label, aksi, kelas) {
            var b = el('button', 'btn ' + (kelas || 'btn2') + ' btn-s', label);
            b.type = 'button';
            b.addEventListener('click', aksi);
            return b;
        }

        function render() {
            editor.textContent = '';

            blok.forEach(function (b, i) {
                if (!b || typeof b !== 'object') return;
                var tipe = b.tipe || '';

                if (tipe === 'pasal') {
                    renderPasal(b, i);
                } else if (tipe === 'lampiran') {
                    renderLampiran(b, i);
                } else if (TIPE_TEKS.indexOf(tipe) !== -1) {
                    renderBlokTeks(b, i);
                } else {
                    editor.appendChild(el('p', 'cell-muted', 'Blok tak dikenal: ' + tipe));
                }
            });

            simpan();
        }

        function kartu(judul, isi) {
            var box = el('div', 'panel');
            var head = el('div', 'panel-head');
            head.appendChild(el('span', 'template-preview-title', judul));
            box.appendChild(head);
            var body = el('div', 'panel-body');
            body.appendChild(isi);
            box.appendChild(body);
            return box;
        }

        function textarea(nilai, onInput, baris) {
            var t = el('textarea', 'template-isi');
            t.rows = baris || 4;
            t.value = nilai || '';
            t.addEventListener('input', function () { onInput(t.value); beriTahu(); });
            return t;
        }

        // ---------- blok teks ----------
        function renderBlokTeks(b, i) {
            var isi = el('div');
            isi.appendChild(textarea(b.isi, function (v) { b.isi = v; }, 6));

            var aksi = el('div', 'form-actions');
            aksi.appendChild(tombol('↑', function () { pindah(i, -1); }));
            aksi.appendChild(tombol('↓', function () { pindah(i, 1); }));
            isi.appendChild(aksi);

            editor.appendChild(kartu(LABEL[b.tipe] || b.tipe, isi));
        }

        // ---------- pasal ----------
        function renderPasal(b, i) {
            var pasal = Array.isArray(b.pasal) ? b.pasal : [];
            b.pasal = pasal;

            var isi = el('div');

            pasal.forEach(function (p, j) {
                var kotak = el('div', 'panel');

                var head = el('div', 'panel-head');
                var barisJudul = el('div', 'panel-head-row');
                var inputJudul = el('input');
                inputJudul.type = 'text';
                inputJudul.maxLength = 255;
                inputJudul.value = p.judul || '';
                inputJudul.addEventListener('input', function () { p.judul = inputJudul.value; beriTahu(); });
                var kiri = el('div');
                kiri.appendChild(el('span', 'cell-faint', 'Judul pasal'));
                kiri.appendChild(inputJudul);
                barisJudul.appendChild(kiri);
                head.appendChild(barisJudul);
                kotak.appendChild(head);

                var body = el('div', 'panel-body');

                // Flag opsional + aktif bawaan.
                var flag = el('div', 'row');
                var labOps = el('label');
                var cbOps = el('input');
                cbOps.type = 'checkbox';
                cbOps.checked = !!p.opsional;
                cbOps.addEventListener('change', function () { p.opsional = cbOps.checked; beriTahu(); });
                labOps.appendChild(cbOps);
                labOps.appendChild(document.createTextNode(' Pasal opsional'));
                flag.appendChild(labOps);

                if (cbOps.checked) {
                    var labAktif = el('label');
                    var cbAktif = el('input');
                    cbAktif.type = 'checkbox';
                    cbAktif.checked = p.aktif_bawaan !== false;
                    cbAktif.addEventListener('change', function () { p.aktif_bawaan = cbAktif.checked; beriTahu(); });
                    labAktif.appendChild(cbAktif);
                    labAktif.appendChild(document.createTextNode(' Aktif bawaan (dipakai mitra)'));
                    flag.appendChild(labAktif);
                }
                body.appendChild(flag);

                // Ayat.
                var ayat = Array.isArray(p.ayat) ? p.ayat : [];
                p.ayat = ayat;
                ayat.forEach(function (a, k) {
                    var teks = typeof a === 'string' ? a : (a && a.teks) || '';
                    var opsionalAyat = typeof a === 'object' && a !== null && a.opsional;

                    var blokAyat = el('div', 'row');
                    blokAyat.appendChild(el('span', 'cell-faint', 'Ayat ' + (k + 1) + (opsionalAyat ? ' (opsional)' : '')));
                    blokAyat.appendChild(textarea(teks, function (v) {
                        if (opsionalAyat) { a.teks = v; } else { p.ayat[k] = v; }
                        beriTahu();
                    }, 3));

                    var aksiAyat = el('div', 'form-actions');
                    aksiAyat.appendChild(tombol('Hapus ayat', function () {
                        p.ayat.splice(k, 1); beriTahu(); render();
                    }, 'btn-hapus'));
                    blokAyat.appendChild(aksiAyat);
                    body.appendChild(blokAyat);
                });

                var aksiPasal = el('div', 'form-actions');
                aksiPasal.appendChild(tombol('Tambah ayat', function () {
                    p.ayat.push(''); beriTahu(); render();
                }));
                aksiPasal.appendChild(tombol('Pasal ke atas', function () { pindahPasal(pasal, j, -1); }));
                aksiPasal.appendChild(tombol('Pasal ke bawah', function () { pindahPasal(pasal, j, 1); }));
                aksiPasal.appendChild(tombol('Hapus pasal', function () {
                    pasal.splice(j, 1); beriTahu(); render();
                }, 'btn-hapus'));
                body.appendChild(aksiPasal);

                kotak.appendChild(body);
                isi.appendChild(kotak);
            });

            var aksi = el('div', 'form-actions');
            aksi.appendChild(tombol('Tambah pasal', function () {
                pasal.push({ nomor: pasal.length + 1, judul: '', ayat: [''], opsional: false, aktif_bawaan: true });
                beriTahu(); render();
            }));
            aksi.appendChild(tombol('↑', function () { pindah(i, -1); }));
            aksi.appendChild(tombol('↓', function () { pindah(i, 1); }));
            isi.appendChild(aksi);

            editor.appendChild(kartu('Blok pasal', isi));
        }

        function pindahPasal(pasal, j, arah) {
            var t = j + arah;
            if (t < 0 || t >= pasal.length) return;
            var tmp = pasal[j];
            pasal[j] = pasal[t];
            pasal[t] = tmp;
            beriTahu(); render();
        }

        // ---------- lampiran ----------
        function renderLampiran(b, i) {
            var lamp = b.lampiran || (b.lampiran = { judul: '', kolom: [], baris: [] });

            var isi = el('div');

            var barisJudul = el('div', 'row');
            barisJudul.appendChild(el('span', 'cell-faint', 'Judul lampiran'));
            var inputJudul = el('input');
            inputJudul.type = 'text';
            inputJudul.value = lamp.judul || '';
            inputJudul.addEventListener('input', function () { lamp.judul = inputJudul.value; beriTahu(); });
            barisJudul.appendChild(inputJudul);
            isi.appendChild(barisJudul);

            var barisKolom = el('div', 'row');
            barisKolom.appendChild(el('span', 'cell-faint', 'Kolom tabel (satu per baris)'));
            barisKolom.appendChild(textarea((lamp.kolom || []).join('\n'), function (v) {
                lamp.kolom = v.split(/\r?\n/).filter(function (x) { return x.trim() !== ''; });
                beriTahu();
            }, 6));
            isi.appendChild(barisKolom);

            var aksi = el('div', 'form-actions');
            aksi.appendChild(tombol('↑', function () { pindah(i, -1); }));
            aksi.appendChild(tombol('↓', function () { pindah(i, 1); }));
            isi.appendChild(aksi);

            editor.appendChild(kartu('Lampiran', isi));
        }

        function pindah(i, arah) {
            var t = i + arah;
            if (t < 0 || t >= blok.length) return;
            var tmp = blok[i];
            blok[i] = blok[t];
            blok[t] = tmp;
            beriTahu(); render();
        }

        render();
    })();
    </script>
    @endverbatim
@endsection
