@extends('layouts.admin')
@section('title', 'Draf Proposal AI')

@section('content')
<div class="panel">
    <div class="panel-head">
        <div class="panel-head-row">
            <div>
                <h2 class="panel-title">Draf Proposal AI</h2>
                <p class="panel-sub">Draf usulan kemitraan yang dibuat mitra, beserta berkasnya.</p>
            </div>
        </div>
    </div>

    {{-- Pencarian --}}
    <div class="panel-body">
        <form method="GET" action="{{ route('admin.draf_proposal') }}" class="panel-toolbar" role="search">
            <div class="row">
                <label for="q">Cari instansi atau judul kemitraan</label>
                <input type="text" id="q" name="q" value="{{ $kata }}" maxlength="100"
                       placeholder="Contoh: Universitas Indonesia" autocomplete="off">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-s">Cari</button>
                @if ($kata !== '')
                    <a href="{{ route('admin.draf_proposal') }}" class="btn btn2 btn-s">Hapus pencarian</a>
                @endif
            </div>
        </form>
    </div>

    <div class="table-scroll">
        <table class="data-table" id="table-draf">
            <caption class="sr-only">Daftar draf proposal kemitraan dari mitra</caption>
            <thead>
                <tr>
                    <th scope="col">Instansi Mitra</th>
                    <th scope="col">Judul Kemitraan</th>
                    <th scope="col">Tanggal Pembuatan</th>
                    <th scope="col" class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $r)
                <tr>
                    <td><b class="cell-strong">{{ $r['instansi'] }}</b></td>
                    <td class="cell-muted">{{ $r['judul_ks'] }}</td>
                    <td class="cell-muted">{{ \Illuminate\Support\Carbon::parse($r['buatan_pada'])->translatedFormat('d F Y') }}</td>
                    <td>
                        <div class="table-actions">
                            @if ($berkasSiap)
                                <a href="{{ route('admin.draf_proposal.unduh', $r['uuid']) }}"
                                   class="btn btn2 btn-s" data-unduh="{{ $r['uuid'] }}">Unduh Draf</a>
                            @else
                                {{-- Berkas draf belum disimpan backend: tombol sengaja dimatikan. --}}
                                <button type="button" class="btn btn2 btn-s" disabled
                                        title="Menunggu backend: berkas draf belum tersedia">Unduh Draf</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="cell-empty">
                        @if ($kata !== '')
                            Tidak ada draf proposal yang cocok dengan pencarian &ldquo;{{ $kata }}&rdquo;.
                            <br><a href="{{ route('admin.draf_proposal') }}">Tampilkan semua draf</a>
                        @else
                            Belum ada draf proposal dari mitra. Draf akan muncul di sini setelah mitra membuatnya.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($halamanAkhir > 1)
    <div class="panel-body">
        <nav class="pagination-nav" aria-label="Navigasi halaman draf proposal">
            <p class="cell-muted" role="status">
                Menampilkan halaman {{ $halaman }} dari {{ $halamanAkhir }} &middot; {{ $total }} draf
            </p>
            <div class="table-actions">
                @if ($halaman > 1)
                    <a class="btn btn2 btn-s" rel="prev"
                       href="{{ route('admin.draf_proposal', array_filter(['q' => $kata, 'page' => $halaman - 1])) }}">Sebelumnya</a>
                @endif
                @if ($halaman < $halamanAkhir)
                    <a class="btn btn2 btn-s" rel="next"
                       href="{{ route('admin.draf_proposal', array_filter(['q' => $kata, 'page' => $halaman + 1])) }}">Berikutnya</a>
                @endif
            </div>
        </nav>
    </div>
    @endif
</div>

<script>
// Unduhan draf lewat fetch agar kegagalan (403/404) tampil jelas, bukan
// halaman error mentah. Nama berkas diambil dari header Content-Disposition.
document.querySelectorAll('[data-unduh]').forEach(function (tautan) {
    tautan.addEventListener('click', async function (e) {
        e.preventDefault();

        var teksAsli = tautan.textContent;
        tautan.textContent = 'Mengunduh...';
        tautan.setAttribute('aria-busy', 'true');

        try {
            var res = await fetch(tautan.href, {
                headers: { 'Accept': 'application/octet-stream' },
                credentials: 'same-origin',
            });

            if (!res.ok) {
                throw new Error(res.status === 403
                    ? 'Anda tidak berhak mengunduh draf ini.'
                    : 'Berkas draf tidak ditemukan.');
            }

            var blob = await res.blob();
            var nama = namaBerkas(res.headers.get('Content-Disposition')) || 'draf-proposal';

            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = nama;
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        } catch (err) {
            showToast(err.message || 'Unduhan gagal. Coba lagi.');
        } finally {
            tautan.textContent = teksAsli;
            tautan.removeAttribute('aria-busy');
        }
    });
});

function namaBerkas(disposisi) {
    if (!disposisi) return '';
    var cocok = /filename\*=UTF-8''([^;]+)|filename="?([^";]+)"?/i.exec(disposisi);
    if (!cocok) return '';
    return decodeURIComponent(cocok[1] || cocok[2] || '');
}

function showToast(msg) {
    var toast = document.createElement('div');
    toast.className = 'toast-notification';
    var span = document.createElement('span');
    span.textContent = msg; // teksContent: tidak ada HTML dari server
    toast.appendChild(span);
    document.body.appendChild(toast);
    setTimeout(function () {
        toast.style.transition = 'opacity 0.4s ease';
        toast.style.opacity = '0';
        setTimeout(function () { toast.remove(); }, 400);
    }, 3000);
}
</script>
@endsection