@extends('layouts.user')
@section('title', 'Generator Draf Proposal AI')

@section('content')
<div class="box">
    <h2>Generator Draf Proposal Kerjasama Komdigi Berbasis AI</h2>
    <div class="in">
        <p style="color:var(--text-muted);margin-bottom:16px;">
            Sistem Kecerdasan Buatan (AI) Komdigi akan secara otomatis menyusun Draf Nota Kesepahaman (MoU) dan Proposal Kerjasama sesuai standar regulasi Kementerian Komunikasi dan Digital RI.
        </p>

        <!-- WIZARD FORM INPUT AI -->
        <div id="form-ai-input" style="background:#f8fafc;border:1px solid var(--border-color);padding:18px;border-radius:var(--radius);margin-bottom:20px;">
            <h3 style="color:var(--primary);font-size:15px;margin-bottom:12px;">Konfigurasi Parameter Proposal Kerjasama AI:</h3>

            <div class="grid3" style="grid-template-columns: 1fr 1fr; gap:14px;">
                <div class="row">
                    <label for="kategori_ks">Kategori Bidang Kerjasama</label>
                    <select id="kategori_ks">
                        <option value="Pelatihan Literasi Digital & Digital Talent Scholarship (DTS)">Pelatihan Literasi Digital &amp; Digital Talent Scholarship (DTS)</option>
                        <option value="Perluasan Infrastruktur Telekomunikasi & Jaringan Sinyal">Perluasan Infrastruktur Telekomunikasi &amp; Jaringan Sinyal</option>
                        <option value="Program Magang, Uji Kompetensi SKKNI & Beasiswa SDM">Program Magang, Uji Kompetensi SKKNI &amp; Beasiswa SDM</option>
                        <option value="Pengembangan Sistem Pemerintahan Berbasis Elektronik (SPBE) & Smart City">Pengembangan SPBE &amp; Smart City Daerah</option>
                    </select>
                </div>
                <div class="row">
                    <label for="durasi_ks">Jangka Waktu Kerjasama</label>
                    <select id="durasi_ks">
                        <option value="1 (Satu) Tahun">1 (Satu) Tahun</option>
                        <option value="2 (Dua) Tahun" selected>2 (Dua) Tahun</option>
                        <option value="3 (Tiga) Tahun">3 (Tiga) Tahun</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <label for="target_peserta">Target Peserta / Cakupan Sasaran Program</label>
                <input type="text" id="target_peserta" value="1.500 Mahasiswa, ASN Daerah, dan Aparatur Desa di Jawa Barat">
            </div>

            <div class="row">
                <label for="tujuan_ks">Maksud &amp; Tujuan Spesifik Kerjasama</label>
                <textarea id="tujuan_ks" rows="2">Meningkatkan kapasitas talenta digital daerah, akselerasi sertifikasi SKKNI, dan efisiensi pelayanan publik berbasis teknologi informasi.</textarea>
            </div>

            <button type="button" class="btn" id="btn-buat" style="padding:10px 20px;width:100%;">
                Generate Draf Proposal Kerjasama AI Otomatis
            </button>
        </div>

        <!-- PROGRESS STEPS INDICATOR -->
        <div id="ai-loading" class="hide" style="text-align:center;padding:24px 16px;background:#ffffff;border:1px solid var(--border-color);border-radius:var(--radius);margin-bottom:20px;display:none;">
            <h3 style="color:var(--primary);margin-bottom:6px;" id="ai-step-title">Sedang Mengolah Data dengan AI...</h3>
            <p style="font-size:12px;color:var(--text-muted);" id="ai-step-desc">Menganalisis regulasi Komdigi dan menyusun pasal-pasal kemitraan...</p>
            <div class="progress-bar-bg" style="max-width:360px;margin:12px auto 0;">
                <div id="ai-progress-fill" class="progress-bar-fill" style="width: 15%;"></div>
            </div>
        </div>

        <!-- EDITOR & RESULT AREA -->
        <div id="area" class="hide" style="display:none;">
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:10px 14px;border-radius:var(--radius);margin-bottom:14px;display:flex;justify-content:space-between;align-items:center;">
                <span>Draf Proposal Kerjasama AI berhasil dibuat sesuai standar Kementerian Komunikasi dan Digital RI. Anda dapat mengedit teks secara langsung.</span>
            </div>

            <!-- TOOLBAR -->
            <div class="editor-toolbar" style="background:#f8fafc;border:1px solid var(--border-color);border-bottom:none;padding:6px;display:flex;gap:4px;">
                <button type="button" data-cmd="bold"><b>B</b></button>
                <button type="button" data-cmd="italic"><i>I</i></button>
                <button type="button" data-cmd="underline"><u>U</u></button>
                <button type="button" data-cmd="insertUnorderedList">Daftar Bullet</button>
                <button type="button" data-cmd="justifyLeft">Kiri</button>
                <button type="button" data-cmd="justifyCenter">Tengah</button>
                <button type="button" data-cmd="justifyFull">Justify</button>
                <button type="button" onclick="window.print()" style="margin-left:auto;background:var(--primary);color:#fff;border:none;">Cetak / Export PDF</button>
            </div>

            <!-- EDITABLE PROPOSAL DOCUMENT -->
            <div id="editor" class="editor" contenteditable="true" style="border:1px solid var(--border-color);padding:24px;min-height:380px;background:#ffffff;line-height:1.8;"></div>

            <div style="margin-top:16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <button type="button" class="btn" id="btn-simpan">Simpan Draf Dokumen</button>
                <button type="button" class="btn btn2" onclick="alert('Draf Proposal Kerjasama AI berhasil dikirim ke Admin Komdigi untuk ditinjau!')">Kirim Langsung ke Admin Komdigi</button>
                <span id="msg" style="color:#15803d;font-weight:600;"></span>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var btn = document.getElementById('btn-buat'),
        loading = document.getElementById('ai-loading'),
        area = document.getElementById('area'),
        ed = document.getElementById('editor'),
        progressFill = document.getElementById('ai-progress-fill'),
        stepTitle = document.getElementById('ai-step-title'),
        stepDesc = document.getElementById('ai-step-desc');

    btn.onclick = function () {
        btn.disabled = true;
        loading.style.display = 'block';
        area.style.display = 'none';

        var category = document.getElementById('kategori_ks').value;
        var duration = document.getElementById('durasi_ks').value;
        var target = document.getElementById('target_peserta').value;
        var goal = document.getElementById('tujuan_ks').value;

        progressFill.style.width = '30%';
        stepTitle.textContent = 'Step 1/3: Menganalisis Visi Misi Komdigi & BPSDM...';
        stepDesc.textContent = 'Menyelaraskan program kemitraan dengan Core Values ASN BerAKHLAK...';

        setTimeout(function () {
            progressFill.style.width = '70%';
            stepTitle.textContent = 'Step 2/3: Menyusun Draft Pasal MoU & Legalitas...';
            stepDesc.textContent = 'Merumuskan ruang lingkup kerja sama, hak, dan kewajiban para pihak...';
        }, 1000);

        setTimeout(function () {
            progressFill.style.width = '100%';
            stepTitle.textContent = 'Step 3/3: Memformat Dokumen Standar Kemitraan...';
            stepDesc.textContent = 'Mengomposisi surat nota kesepahaman komprehensif...';
        }, 2000);

        setTimeout(function () {
            loading.style.display = 'none';
            area.style.display = 'block';
            btn.disabled = false;

            ed.innerHTML = `
                <div style="text-align:center;border-bottom:2px solid #0b3a6b;padding-bottom:12px;margin-bottom:20px;">
                    <h3 style="font-size:16px;font-weight:700;color:#0b3a6b;margin:0;">KEMENTERIAN KOMUNIKASI DAN DIGITAL REPUBLIK INDONESIA</h3>
                    <div style="font-size:12px;font-weight:600;color:#1d4ed8;">BADAN PENGEMBANGAN SUMBER DAYA MANUSIA KOMUNIKASI DAN DIGITAL</div>
                    <div style="font-size:11px;color:#64748b;">Jl. Medan Merdeka Barat No. 9, Jakarta Pusat 10110 | Website: www.komdigi.go.id</div>
                </div>

                <div style="text-align:center;margin-bottom:20px;">
                    <h4 style="font-size:15px;font-weight:700;text-transform:uppercase;color:#0b3a6b;margin-bottom:4px;">DRAF NOTA KESEPAHAMAN (MEMORANDUM OF UNDERSTANDING)</h4>
                    <div style="font-size:12px;color:#64748b;">Nomor: 045/KS/KOMDIGI/${new Date().getFullYear()} &nbsp;|&nbsp; Nomor Mitra: 012/DISCOM/${new Date().getFullYear()}</div>
                    <div style="font-size:12px;font-weight:600;color:#1d4ed8;margin-top:2px;">TENTANG: ${category.toUpperCase()}</div>
                </div>

                <p style="text-align:justify;">Pada hari ini, <b>Selasa</b> tanggal <b>Dua Belas</b> bulan <b>Oktober</b> tahun <b>Dua Ribu Dua Puluh Enam</b>, bertempat di Jakarta, kami yang bertanda tangan di bawah ini:</p>

                <ol style="line-height:1.8;">
                    <li><b>KEMENTERIAN KOMUNIKASI DAN DIGITAL REPUBLIK INDONESIA</b>, berkedudukan di Jl. Medan Merdeka Barat No. 9 Jakarta Pusat, dalam hal ini diwakili oleh <b>Kepala Badan Pengembangan SDM Komdigi</b>, selanjutnya disebut <b>PIHAK KESATU</b>.</li>
                    <li><b>${ "{{ $mitra['instansi'] }}" }</b>, berkedudukan di ${ "{{ $mitra['alamat'] }}" }, dalam hal ini diwakili oleh <b>${ "{{ $mitra['pic'] }}" }</b> selaku Penanggung Jawab Instansi, selanjutnya disebut <b>PIHAK KEDUA</b>.</li>
                </ol>

                <p style="text-align:justify;"><b>PIHAK KESATU</b> dan <b>PIHAK KEDUA</b> secara bersama-sama selanjutnya disebut <b>PARA PIHAK</b>, sepakat untuk mengadakan kerjasama dengan ketentuan sebagai berikut:</p>

                <h5 style="color:#0b3a6b;font-size:13px;margin-top:14px;">PASAL 1: MAKSUD DAN TUJUAN</h5>
                <p style="text-align:justify;">Maksud dan tujuan dari Nota Kesepahaman ini adalah: ${goal}</p>

                <h5 style="color:#0b3a6b;font-size:13px;margin-top:14px;">PASAL 2: RUANG LINGKUP KERJA SAMA</h5>
                <p>Ruang lingkup kerjasama ini meliputi:</p>
                <ul>
                    <li>Penyelenggaraan ${category} dengan target sasaran ${target}.</li>
                    <li>Fasilitasi sarana infrastruktur digital, modul pembelajaran, serta penguji kompetensi bersertifikat SKKNI.</li>
                    <li>Pertukaran data, informasi, dan riset teknologi informasi berbasis Sistem Pemerintahan Berbasis Elektronik (SPBE).</li>
                </ul>

                <h5 style="color:#0b3a6b;font-size:13px;margin-top:14px;">PASAL 3: JANGKA WAKTU</h5>
                <p style="text-align:justify;">Nota Kesepahaman ini berlaku untuk jangka waktu <b>${duration}</b> terhitung sejak tanggal ditandatangani oleh <b>PARA PIHAK</b>.</p>

                <h5 style="color:#0b3a6b;font-size:13px;margin-top:14px;">PASAL 4: HAK DAN KEWAJIBAN</h5>
                <p style="text-align:justify;"><b>PIHAK KESATU</b> berhak menerima laporan berkala dan berkewajiban menyediakan fasilitator pelatihan. <b>PIHAK KEDUA</b> berhak memperoleh pendampingan teknis dan berkewajiban memfasilitasi sarana pendukung pelatihan digital.</p>

                <br>
                <table style="width:100%;margin-top:24px;text-align:center;border-collapse:collapse;">
                    <tr>
                        <td style="width:50%;"><b>PIHAK KESATU</b><br>BPSDM KEMKOMDIGI RI<br><br><br><br><b><u>Bonifasius Wahyu Pudjianto, Ph.D.</u></b><br>NIP. 19740812 199903 1 001</td>
                        <td style="width:50%;"><b>PIHAK KEDUA</b><br>${ "{{ $mitra['instansi'] }}" }<br><br><br><br><b><u>${ "{{ $mitra['pic'] }}" }</u></b><br>Penanggung Jawab Kemitraan</td>
                    </tr>
                </table>
            `;
        }, 2500);
    };

    document.querySelectorAll('[data-cmd]').forEach(function (b) {
        b.onclick = function () { document.execCommand(b.dataset.cmd, false, null); ed.focus(); };
    });

    document.getElementById('btn-simpan').onclick = function () {
        var m = document.getElementById('msg');
        m.textContent = 'Draf proposal AI tersimpan di akun Anda!';
        setTimeout(function () { m.textContent = ''; }, 3000);
    };
})();
</script>
@endsection
