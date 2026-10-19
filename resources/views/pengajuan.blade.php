@extends('layouts.user-public')
@section('title', 'Pengajuan Kerja Sama')

@section('content')
<div class="pjn-hero reveal">
    <div class="pjn-hero-badge">Portal Resmi Komdigi</div>
    <h2 class="pjn-hero-title">Formulir Pengajuan Kerja Sama</h2>
    <p class="pjn-hero-sub">Isi data instansi Anda dengan lengkap. Jika disetujui, username dan password login akan dikirimkan ke email PIC yang terdaftar.</p>
    <div class="pjn-steps">
        <div class="pjn-step active" id="step-ind-1">
            <div class="pjn-step-num">1</div>
            <div class="pjn-step-label">Data Instansi</div>
        </div>
        <div class="pjn-step-line"></div>
        <div class="pjn-step" id="step-ind-2">
            <div class="pjn-step-num">2</div>
            <div class="pjn-step-label">Kontak PIC</div>
        </div>
        <div class="pjn-step-line"></div>
        <div class="pjn-step" id="step-ind-3">
            <div class="pjn-step-num">3</div>
            <div class="pjn-step-label">Tujuan & Dokumen</div>
        </div>
    </div>
</div>

@if (session('ok'))
<div class="pjn-success-banner reveal">
    <div class="pjn-success-icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="12" fill="#16a34a"/><path d="M7 12.5l3.5 3.5 6-7" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
    <div>
        <div style="font-weight:700;color:#15803d;margin-bottom:2px;">Pengajuan Berhasil Dikirim!</div>
        <div style="font-size:13px;color:#166534;">{{ session('ok') }}</div>
    </div>
</div>
@endif

@if ($errors->any())
<div class="pjn-error-banner reveal">
    <div style="font-weight:700;color:#dc2626;margin-bottom:6px;">Data belum lengkap, mohon periksa kembali:</div>
    <ul style="margin:0;padding-left:18px;">
        @foreach ($errors->all() as $e)
        <li style="font-size:13px;color:#7f1d1d;">{{ $e }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('pengajuan.store') }}" enctype="multipart/form-data" id="pjn-form" class="reveal" novalidate>
    @csrf

    {{-- STEP 1: DATA INSTANSI --}}
    <div class="pjn-card" id="pjn-step-1">
        <div class="pjn-card-head">
            <div class="pjn-card-head-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M3 21h18M3 7v14M21 7v14M6 11h2m-2 4h2m8-4h2m-2 4h2M9 21V11a3 3 0 016 0v10" stroke="#0b3a6b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <div class="pjn-card-title">Data Instansi / Perguruan Tinggi</div>
                <div class="pjn-card-sub">Informasi lembaga yang mengajukan kerja sama</div>
            </div>
            <span class="pjn-card-badge">Langkah 1 dari 3</span>
        </div>
        <div class="pjn-card-body">

            {{-- PDDIKTI SEARCH --}}
            <div class="pjn-field">
                <label class="pjn-label" for="instansi">
                    Nama Perguruan Tinggi / Instansi
                    <span class="pjn-required">*</span>
                    <span class="pjn-pddikti-badge">API PDDIKTI LIVE</span>
                </label>
                <div class="pjn-search-wrap" id="pddikti-wrap">
                    <div class="pjn-search-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="11" cy="11" r="7" stroke="#64748b" stroke-width="2"/><path d="m21 21-4.35-4.35" stroke="#64748b" stroke-width="2" stroke-linecap="round"/></svg>
                    </div>
                    <input
                        type="text"
                        id="pddikti-input"
                        name="instansi"
                        value="{{ old('instansi') }}"
                        placeholder="Ketik nama perguruan tinggi atau instansi..."
                        autocomplete="off"
                        required
                        class="pjn-input pjn-input-search"
                        oninput="pddiktiSearch(this.value)"
                        onfocus="if(this.value.length>=2) pddiktiSearch(this.value)"
                    >
                    <div class="pjn-search-spinner" id="pddikti-spinner" style="display:none;">
                        <div class="spinner-ring"></div>
                    </div>
                </div>
                <div class="pjn-dropdown" id="pddikti-dropdown" style="display:none;"></div>
                <div id="pddikti-selected-info" class="pjn-selected-info" style="display:none;"></div>
                <input type="hidden" id="pddikti-id" name="pddikti_id" value="">
                <p class="pjn-hint">Pencarian terhubung langsung ke API PDDIKTI Kemdikbud secara real-time. Jika instansi tidak ada di PDDIKTI (seperti dinas daerah), Anda bisa mengetikkan nama lengkap secara manual.</p>
            </div>

            <div class="pjn-grid-2">
                <div class="pjn-field">
                    <label class="pjn-label" for="jenis_instansi">Jenis Instansi <span class="pjn-required">*</span></label>
                    <select id="jenis_instansi" name="jenis_instansi" class="pjn-input" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="perguruan_tinggi" {{ old('jenis_instansi')=='perguruan_tinggi'?'selected':'' }}>Perguruan Tinggi</option>
                        <option value="pemerintah_daerah" {{ old('jenis_instansi')=='pemerintah_daerah'?'selected':'' }}>Pemerintah Daerah</option>
                        <option value="kementerian" {{ old('jenis_instansi')=='kementerian'?'selected':'' }}>Kementerian / Lembaga</option>
                        <option value="bumn" {{ old('jenis_instansi')=='bumn'?'selected':'' }}>BUMN / BUMD</option>
                        <option value="swasta" {{ old('jenis_instansi')=='swasta'?'selected':'' }}>Perusahaan Swasta</option>
                        <option value="lsm" {{ old('jenis_instansi')=='lsm'?'selected':'' }}>LSM / NGO</option>
                        <option value="lainnya" {{ old('jenis_instansi')=='lainnya'?'selected':'' }}>Lainnya</option>
                    </select>
                </div>
                <div class="pjn-field">
                    <label class="pjn-label" for="organisasi">Unit Kerja / Divisi / Fakultas <span class="pjn-required">*</span></label>
                    <input type="text" id="organisasi" name="organisasi" value="{{ old('organisasi') }}" placeholder="cth. Fakultas Teknik / Biro Kerja Sama" class="pjn-input" required>
                </div>
            </div>

            <div class="pjn-grid-2">
                <div class="pjn-field">
                    <label class="pjn-label" for="kota">Kota / Kabupaten <span class="pjn-required">*</span></label>
                    <input type="text" id="kota" name="kota" value="{{ old('kota') }}" placeholder="cth. Medan, Bandung, Surabaya" class="pjn-input" required>
                </div>
                <div class="pjn-field">
                    <label class="pjn-label" for="provinsi">Provinsi <span class="pjn-required">*</span></label>
                    <select id="provinsi" name="provinsi" class="pjn-input" required>
                        <option value="">-- Pilih Provinsi --</option>
                        <option value="Aceh" {{ old('provinsi')=='Aceh'?'selected':'' }}>Aceh</option>
                        <option value="Sumatera Utara" {{ old('provinsi')=='Sumatera Utara'?'selected':'' }}>Sumatera Utara</option>
                        <option value="Sumatera Barat" {{ old('provinsi')=='Sumatera Barat'?'selected':'' }}>Sumatera Barat</option>
                        <option value="Riau" {{ old('provinsi')=='Riau'?'selected':'' }}>Riau</option>
                        <option value="Kepulauan Riau" {{ old('provinsi')=='Kepulauan Riau'?'selected':'' }}>Kepulauan Riau</option>
                        <option value="Jambi" {{ old('provinsi')=='Jambi'?'selected':'' }}>Jambi</option>
                        <option value="Kepulauan Bangka Belitung" {{ old('provinsi')=='Kepulauan Bangka Belitung'?'selected':'' }}>Kepulauan Bangka Belitung</option>
                        <option value="Sumatera Selatan" {{ old('provinsi')=='Sumatera Selatan'?'selected':'' }}>Sumatera Selatan</option>
                        <option value="Bengkulu" {{ old('provinsi')=='Bengkulu'?'selected':'' }}>Bengkulu</option>
                        <option value="Lampung" {{ old('provinsi')=='Lampung'?'selected':'' }}>Lampung</option>
                        <option value="Banten" {{ old('provinsi')=='Banten'?'selected':'' }}>Banten</option>
                        <option value="DKI Jakarta" {{ old('provinsi')=='DKI Jakarta'?'selected':'' }}>DKI Jakarta</option>
                        <option value="Jawa Barat" {{ old('provinsi')=='Jawa Barat'?'selected':'' }}>Jawa Barat</option>
                        <option value="Jawa Tengah" {{ old('provinsi')=='Jawa Tengah'?'selected':'' }}>Jawa Tengah</option>
                        <option value="DI Yogyakarta" {{ old('provinsi')=='DI Yogyakarta'?'selected':'' }}>DI Yogyakarta</option>
                        <option value="Jawa Timur" {{ old('provinsi')=='Jawa Timur'?'selected':'' }}>Jawa Timur</option>
                        <option value="Bali" {{ old('provinsi')=='Bali'?'selected':'' }}>Bali</option>
                        <option value="Nusa Tenggara Barat" {{ old('provinsi')=='Nusa Tenggara Barat'?'selected':'' }}>Nusa Tenggara Barat</option>
                        <option value="Nusa Tenggara Timur" {{ old('provinsi')=='Nusa Tenggara Timur'?'selected':'' }}>Nusa Tenggara Timur</option>
                        <option value="Kalimantan Barat" {{ old('provinsi')=='Kalimantan Barat'?'selected':'' }}>Kalimantan Barat</option>
                        <option value="Kalimantan Tengah" {{ old('provinsi')=='Kalimantan Tengah'?'selected':'' }}>Kalimantan Tengah</option>
                        <option value="Kalimantan Selatan" {{ old('provinsi')=='Kalimantan Selatan'?'selected':'' }}>Kalimantan Selatan</option>
                        <option value="Kalimantan Timur" {{ old('provinsi')=='Kalimantan Timur'?'selected':'' }}>Kalimantan Timur</option>
                        <option value="Kalimantan Utara" {{ old('provinsi')=='Kalimantan Utara'?'selected':'' }}>Kalimantan Utara</option>
                        <option value="Sulawesi Utara" {{ old('provinsi')=='Sulawesi Utara'?'selected':'' }}>Sulawesi Utara</option>
                        <option value="Gorontalo" {{ old('provinsi')=='Gorontalo'?'selected':'' }}>Gorontalo</option>
                        <option value="Sulawesi Tengah" {{ old('provinsi')=='Sulawesi Tengah'?'selected':'' }}>Sulawesi Tengah</option>
                        <option value="Sulawesi Barat" {{ old('provinsi')=='Sulawesi Barat'?'selected':'' }}>Sulawesi Barat</option>
                        <option value="Sulawesi Selatan" {{ old('provinsi')=='Sulawesi Selatan'?'selected':'' }}>Sulawesi Selatan</option>
                        <option value="Sulawesi Tenggara" {{ old('provinsi')=='Sulawesi Tenggara'?'selected':'' }}>Sulawesi Tenggara</option>
                        <option value="Maluku" {{ old('provinsi')=='Maluku'?'selected':'' }}>Maluku</option>
                        <option value="Maluku Utara" {{ old('provinsi')=='Maluku Utara'?'selected':'' }}>Maluku Utara</option>
                        <option value="Papua Barat" {{ old('provinsi')=='Papua Barat'?'selected':'' }}>Papua Barat</option>
                        <option value="Papua" {{ old('provinsi')=='Papua'?'selected':'' }}>Papua</option>
                        <option value="Papua Tengah" {{ old('provinsi')=='Papua Tengah'?'selected':'' }}>Papua Tengah</option>
                        <option value="Papua Pegunungan" {{ old('provinsi')=='Papua Pegunungan'?'selected':'' }}>Papua Pegunungan</option>
                        <option value="Papua Selatan" {{ old('provinsi')=='Papua Selatan'?'selected':'' }}>Papua Selatan</option>
                        <option value="Papua Barat Daya" {{ old('provinsi')=='Papua Barat Daya'?'selected':'' }}>Papua Barat Daya</option>
                    </select>
                </div>
            </div>

            <div class="pjn-field">
                <label class="pjn-label" for="alamat">Alamat Lengkap Instansi <span class="pjn-required">*</span></label>
                <textarea id="alamat" name="alamat" rows="2" placeholder="Jl. ..." class="pjn-input" required>{{ old('alamat') }}</textarea>
            </div>

        </div>
    </div>

    {{-- STEP 2: KONTAK PIC --}}
    <div class="pjn-card reveal" id="pjn-step-2">
        <div class="pjn-card-head">
            <div class="pjn-card-head-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2M12 11a4 4 0 100-8 4 4 0 000 8z" stroke="#0b3a6b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <div class="pjn-card-title">Data Penanggung Jawab (PIC)</div>
                <div class="pjn-card-sub">Person In Charge yang bertanggung jawab atas pengajuan ini</div>
            </div>
            <span class="pjn-card-badge">Langkah 2 dari 3</span>
        </div>
        <div class="pjn-card-body">
            <div class="pjn-grid-2">
                <div class="pjn-field">
                    <label class="pjn-label" for="pic">Nama Lengkap PIC <span class="pjn-required">*</span></label>
                    <input type="text" id="pic" name="pic" value="{{ old('pic') }}" placeholder="cth. Dr. Budi Santoso, M.Kom." class="pjn-input" required>
                </div>
                <div class="pjn-field">
                    <label class="pjn-label" for="jabatan_pic">Jabatan PIC</label>
                    <input type="text" id="jabatan_pic" name="jabatan_pic" value="{{ old('jabatan_pic') }}" placeholder="cth. Wakil Rektor IV, Kepala Biro" class="pjn-input">
                </div>
            </div>
            <div class="pjn-grid-2">
                <div class="pjn-field">
                    <label class="pjn-label" for="email">Email Official / Resmi PIC <span class="pjn-required">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="pic@instansi.ac.id" class="pjn-input" required>
                    <p class="pjn-hint">Username & password login akan dikirim ke email ini jika disetujui.</p>
                </div>
                <div class="pjn-field">
                    <label class="pjn-label" for="telp">No. Telepon / WhatsApp PIC <span class="pjn-required">*</span></label>
                    <input type="text" id="telp" name="telp" value="{{ old('telp') }}" placeholder="08xx-xxxx-xxxx" class="pjn-input" required>
                </div>
            </div>
        </div>
    </div>

    {{-- STEP 3: TUJUAN & DOKUMEN --}}
    <div class="pjn-card reveal" id="pjn-step-3">
        <div class="pjn-card-head">
            <div class="pjn-card-head-icon">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8zM14 2v6h6M16 13H8M16 17H8M10 9H8" stroke="#0b3a6b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </div>
            <div>
                <div class="pjn-card-title">Tujuan & Detail Kerja Sama</div>
                <div class="pjn-card-sub">Deskripsikan tujuan, metode, dan rencana pembiayaan kerja sama</div>
            </div>
            <span class="pjn-card-badge">Langkah 3 dari 3</span>
        </div>
        <div class="pjn-card-body">

            <div class="pjn-field">
                <label class="pjn-label">Bidang Kerja Sama yang Diminati <span class="pjn-required">*</span></label>
                <div class="pjn-checkbox-group">
                    @php
                    $bidang_options = [
                        'dts' => 'Digital Talent Scholarship (DTS)',
                        'gta' => 'Government Transformation Academy (GTA)',
                        'vsga' => 'Vocational School Graduate Academy (VSGA)',
                        'skkni' => 'Fasilitasi Uji Kompetensi SKKNI',
                        'pelatihan' => 'Pelatihan Teknis TIK',
                        'infrastruktur' => 'Infrastruktur & Jaringan Telekomunikasi',
                        'spbe' => 'Sistem Pemerintahan Berbasis Elektronik (SPBE)',
                        'riset' => 'Riset & Inovasi Bersama',
                        'literasi' => 'Literasi Digital Masyarakat',
                        'lainnya' => 'Lainnya',
                    ];
                    $old_bidang = old('bidang', []);
                    @endphp
                    @foreach($bidang_options as $val => $label)
                    <label class="pjn-checkbox-label">
                        <input type="checkbox" name="bidang[]" value="{{ $val }}" {{ in_array($val, $old_bidang) ? 'checked' : '' }} class="pjn-checkbox">
                        <span class="pjn-checkbox-custom"></span>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            </div>

<!-- New fields added -->
<div class="pjn-field">
    <label class="pjn-label" for="tujuan_mitra">Tujuan Pengajuan dari Mitra (alasan) <span class="pjn-required">*</span></label>
    <textarea id="tujuan_mitra" name="tujuan_mitra" rows="4" placeholder="Jelaskan alasan dan latar belakang pengajuan kerjasama..." class="pjn-input" required>{{ old('tujuan_mitra') }}</textarea>
    <p class="pjn-hint">Berikan alasan yang jelas, minimal 30 karakter.</p>
</div>

<div class="pjn-field">
    <label class="pjn-label" for="metode_pelatihan">Metode Pelatihan <span class="pjn-required">*</span></label>
    <select id="metode_pelatihan" name="metode_pelatihan" class="pjn-input" required>
        <option value="">-- Pilih Metode --</option>
        <option value="offline" {{ old('metode_pelatihan')=='offline'?'selected':'' }}>Tatap Muka / Offline</option>
        <option value="online" {{ old('metode_pelatihan')=='online'?'selected':'' }}>Daring / Online</option>
        <option value="hybrid" {{ old('metode_pelatihan')=='hybrid'?'selected':'' }}>Hybrid / Blended</option>
        <option value="workshop" {{ old('metode_pelatihan')=='workshop'?'selected':'' }}>Workshop</option>
        <option value="magang" {{ old('metode_pelatihan')=='magang'?'selected':'' }}>Magang / Praktik</option>
        <option value="seminar" {{ old('metode_pelatihan')=='seminar'?'selected':'' }}>Seminar / Webinar</option>
    </select>
</div>

<div class="pjn-field">
    <label class="pjn-label" for="tujuan_pelatihan">Tujuan Pelatihan <span class="pjn-required">*</span></label>
    <textarea id="tujuan_pelatihan" name="tujuan_pelatihan" rows="4" placeholder="Contoh: meningkatkan kompetensi ASN, sertifikasi SKKNI, literasi digital..." class="pjn-input" required>{{ old('tujuan_pelatihan') }}</textarea>
    <p class="pjn-hint">Sebutkan tujuan spesifik pelatihan, minimal 30 karakter.</p>
</div>

<div class="pjn-field">
    <label class="pjn-label" for="pembiayaan">Pembiayaan <span class="pjn-required">*</span></label>
    <div class="pjn-pembiayaan-group">
        <label class="pjn-checkbox-label pjn-pembiayaan-item">
            <input type="radio" name="pembiayaan" value="apbd" {{ old('pembiayaan')=='apbd'?'checked':'' }} class="pjn-checkbox" required>
            <span class="pjn-checkbox-custom"></span>
            <span>APBD</span>
        </label>
        <label class="pjn-checkbox-label pjn-pembiayaan-item">
            <input type="radio" name="pembiayaan" value="apbn" {{ old('pembiayaan')=='apbn'?'checked':'' }} class="pjn-checkbox" required>
            <span class="pjn-checkbox-custom"></span>
            <span>APBN</span>
        </label>
        <label class="pjn-checkbox-label pjn-pembiayaan-item">
            <input type="radio" name="pembiayaan" value="cost_sharing" {{ old('pembiayaan')=='cost_sharing'?'checked':'' }} class="pjn-checkbox" required>
            <span class="pjn-checkbox-custom"></span>
            <span>Cost Sharing</span>
        </label>
        <span class="pjn-info-trigger" tabindex="0" title="Penjelasan Skema Pembiayaan">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span class="pjn-tooltip-box">
                <span class="pjn-tooltip-header"> Penjelasan Skema Pembiayaan</span>
                <ul class="pjn-tooltip-list">
                    <li>
                        <strong>1. APBD (Anggaran Daerah)</strong>
                        <span>Seluruh pembiayaan kegiatan bersumber dari APBD Pemerintah Daerah Mitra.</span>
                    </li>
                    <li>
                        <strong>2. APBN (Anggaran Negara)</strong>
                        <span>Pembiayaan ditanggung oleh APBN Kementerian Komunikasi dan Digital (Kemkomdigi).</span>
                    </li>
                    <li>
                        <strong>3. Cost Sharing (Berbagi Biaya)</strong>
                        <span>Pembiayaan ditanggung bersama secara proporsional antara Kemkomdigi dan Mitra (misal: Komdigi menyediakan narasumber, Mitra menyediakan tempat & konsumsi).</span>
                    </li>
                </ul>
            </span>
        </span>
    </div>
    <p class="pjn-hint">Arahkan kursor atau klik icon <b>(?)</b> di samping tombol Cost Sharing untuk melihat penjelasan lengkap APBD, APBN, & Cost Sharing.</p>
</div>


        </div>
    </div>

    {{-- SUBMIT AREA --}}
    <div class="pjn-submit-area reveal">
        <div class="pjn-submit-info">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#1d4ed8" stroke-width="2"/><path d="M12 8v4M12 16h.01" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round"/></svg>
            <span>Data yang Anda kirim akan diverifikasi oleh tim Komdigi. Proses verifikasi memerlukan 3-7 hari kerja.</span>
        </div>
        <div class="pjn-submit-btns">
            <a href="{{ route('landing') }}" class="btn pjn-btn-back">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M5 12l7 7M5 12l7-7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Kembali ke Beranda
            </a>
            <button type="submit" class="btn pjn-btn-submit" id="btn-submit">
                <span id="btn-text">Kirim Pengajuan Kerja Sama</span>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </div>
</form>

<style>
/* ========== PENGAJUAN PAGE STYLES ========== */
.pjn-hero {
    background: linear-gradient(135deg, #0b3a6b 0%, #1d4ed8 100%);
    border-radius: 14px;
    padding: 40px 36px 30px;
    margin: 90px 0 20px;
    color: #fff;
    position: relative;
    overflow: hidden;
}
.pjn-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.05);
    border-radius: 50%;
}
.pjn-hero::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -30px;
    width: 280px; height: 280px;
    background: rgba(255,255,255,0.04);
    border-radius: 50%;
}
.pjn-hero-badge {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.3);
    color: #bfdbfe;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 12px;
}
.pjn-hero-title {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    margin-bottom: 8px;
    position: relative;
    z-index: 1;
}
.pjn-hero-sub {
    font-size: 14px;
    color: #bfdbfe;
    max-width: 580px;
    margin-bottom: 28px;
    position: relative;
    z-index: 1;
}
.pjn-steps {
    display: flex;
    align-items: center;
    gap: 0;
    position: relative;
    z-index: 1;
}
.pjn-step {
    display: flex;
    align-items: center;
    gap: 8px;
    opacity: 0.55;
    transition: opacity 0.3s;
}
.pjn-step.active { opacity: 1; }
.pjn-step-num {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.4);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: #fff;
    transition: all 0.3s;
}
.pjn-step.active .pjn-step-num {
    background: #fff;
    color: #0b3a6b;
    border-color: #fff;
}
.pjn-step-label { font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.9); }
.pjn-step-line {
    flex: 1;
    height: 2px;
    background: rgba(255,255,255,0.2);
    margin: 0 14px;
    min-width: 40px;
}

/* SUCCESS / ERROR BANNERS */
.pjn-success-banner {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-left: 5px solid #16a34a;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 18px;
}
.pjn-success-icon { flex-shrink: 0; margin-top: 2px; }
.pjn-error-banner {
    background: #fef2f2;
    border: 1px solid #fca5a5;
    border-left: 5px solid #dc2626;
    border-radius: 10px;
    padding: 16px 20px;
    margin-bottom: 18px;
}

/* REAL-TIME VALIDATION & FIELD ERROR HIGHLIGHT */
.pjn-input.is-invalid, 
select.pjn-input.is-invalid, 
textarea.pjn-input.is-invalid {
    border-color: #ef4444 !important;
    background-color: #fff1f2 !important;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.22) !important;
    animation: fieldShake 0.35s ease;
}

.pjn-group-invalid {
    border: 1.5px dashed #ef4444 !important;
    background-color: #fff1f2 !important;
    border-radius: 8px;
    padding: 10px 14px;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15) !important;
    animation: fieldShake 0.35s ease;
}

.pjn-field-error {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #dc2626;
    margin-top: 6px;
    animation: fadeInDown 0.25s ease;
}

.pjn-toast-alert {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: #991b1b;
    color: #ffffff;
    padding: 14px 20px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 600;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    z-index: 99999;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideUpToast 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fieldShake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-5px); }
    40%, 80% { transform: translateX(5px); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideUpToast {
    from { opacity: 0; transform: translateY(40px); }
    to { opacity: 1; transform: translateY(0); }
}

/* CARDS */
.pjn-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    margin-bottom: 18px;
    box-shadow: 0 2px 10px rgba(11,58,107,0.05);
    overflow: hidden;
    transition: box-shadow 0.3s ease;
}
.pjn-card:hover {
    box-shadow: 0 6px 24px rgba(11,58,107,0.09);
}
.pjn-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.pjn-card-head-icon {
    width: 40px; height: 40px;
    background: #eff6ff;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.pjn-card-title { font-size: 15px; font-weight: 700; color: #0b3a6b; }
.pjn-card-sub { font-size: 12px; color: #64748b; margin-top: 1px; }
.pjn-card-badge {
    margin-left: auto;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    border: 1px solid #bfdbfe;
    white-space: nowrap;
}
.pjn-card-body { padding: 22px; }

/* FORM FIELDS */
.pjn-field { margin-bottom: 18px; }
.pjn-field:last-child { margin-bottom: 0; }
.pjn-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 6px;
}
.pjn-required { color: #ef4444; margin-left: 2px; }
.pjn-muted { color: #94a3b8; font-weight: 400; font-size: 12px; }
.pjn-hint { font-size: 11px; color: #94a3b8; margin-top: 5px; margin-bottom: 0; }

.pjn-input {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    font-family: inherit;
    font-size: 13px;
    color: #1e293b;
    background: #fff;
    transition: border-color 0.2s, box-shadow 0.2s;
    outline: none;
}
.pjn-input:focus {
    border-color: #1d4ed8;
    box-shadow: 0 0 0 3px rgba(29,78,216,0.12);
}
.pjn-input::placeholder { color: #94a3b8; }
textarea.pjn-input { resize: vertical; min-height: 90px; }

/* GRID */
.pjn-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

/* PDDIKTI SEARCH */
.pjn-pddikti-badge {
    display: inline-flex;
    align-items: center;
    background: #eff6ff;
    color: #1d4ed8;
    font-size: 10px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
    border: 1px solid #bfdbfe;
    margin-left: 6px;
    letter-spacing: 0.5px;
    vertical-align: middle;
}
.pjn-search-wrap {
    position: relative;
}
.pjn-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    z-index: 1;
}
.pjn-input-search { padding-left: 38px; padding-right: 40px; }
.pjn-search-spinner {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
}
.spinner-ring {
    width: 16px; height: 16px;
    border: 2px solid #e2e8f0;
    border-top-color: #1d4ed8;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.pjn-dropdown {
    position: absolute;
    width: 100%;
    max-height: 280px;
    overflow-y: auto;
    background: #fff;
    border: 1.5px solid #bfdbfe;
    border-top: none;
    border-radius: 0 0 10px 10px;
    box-shadow: 0 10px 30px rgba(11,58,107,0.12);
    z-index: 5000;
    margin-top: -2px;
}
.pjn-dropdown-item {
    padding: 11px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s;
}
.pjn-dropdown-item:last-child { border-bottom: none; }
.pjn-dropdown-item:hover, .pjn-dropdown-item.focused { background: #eff6ff; }
.pjn-dropdown-item-name {
    font-size: 13px;
    font-weight: 600;
    color: #0b3a6b;
}
.pjn-dropdown-item-meta {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}
.pjn-dropdown-empty {
    padding: 16px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
}
.pjn-dropdown-source {
    padding: 6px 12px;
    background: #f8fafc;
    font-size: 10px;
    color: #94a3b8;
    border-top: 1px solid #f1f5f9;
    text-align: right;
}

.pjn-selected-info {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 10px 14px;
    margin-top: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: #1e40af;
    font-weight: 600;
    animation: fadeInUp 0.3s ease;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* INPUT WITH ICON */
.pjn-input-icon-wrap {
    position: relative;
}
.pjn-input-icon-wrap svg {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}
.pjn-input-has-icon { padding-left: 36px; }

/* CHECKBOXES */
.pjn-checkbox-group {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.pjn-checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 13px;
    color: #1e293b;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
    position: relative;
}
.pjn-checkbox-label:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #0b3a6b;
    transform: translateY(-2px);
    box-shadow: 0 3px 8px rgba(11, 58, 107, 0.1);
}
.pjn-checkbox-label:active {
    transform: translateY(1px);
    box-shadow: none;
}
.pjn-checkbox { display: none; }
.pjn-checkbox-custom {
    width: 18px; height: 18px;
    border: 2px solid #cbd5e1;
    border-radius: 4px;
    flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
    background: #fff;
}
.pjn-checkbox:checked ~ .pjn-checkbox-custom {
    background: #1d4ed8;
    border-color: #1d4ed8;
}
.pjn-checkbox:checked ~ .pjn-checkbox-custom::after {
    content: '';
    width: 10px; height: 6px;
    border-left: 2px solid #fff;
    border-bottom: 2px solid #fff;
    transform: rotate(-45deg) translate(1px, -1px);
    display: block;
}
.pjn-checkbox-label:has(.pjn-checkbox:checked) {
    background: #eff6ff;
    border-color: #1d4ed8;
    color: #0b3a6b;
    font-weight: 600;
}

/* PEMBIAYAAN INFO TOOLTIP */
.pjn-pembiayaan-group {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.pjn-pembiayaan-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    position: relative;
    padding: 10px 14px;
}
.pjn-info-trigger {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    color: #475569;
    background: #e2e8f0;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-left: 4px;
    flex-shrink: 0;
}
.pjn-info-trigger:hover, .pjn-info-trigger:focus {
    color: #1d4ed8;
    background: #dbeafe;
    outline: none;
}
.pjn-tooltip-box {
    position: absolute;
    bottom: calc(100% + 10px);
    right: 0;
    left: auto;
    transform: translateY(6px);
    width: 320px;
    padding: 14px 16px;
    background: #0f172a;
    color: #ffffff;
    font-size: 12px;
    font-weight: 400;
    line-height: 1.45;
    border-radius: 12px;
    box-shadow: 0 14px 35px -5px rgba(15, 23, 42, 0.45);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 999;
}
.pjn-tooltip-box::after {
    content: '';
    position: absolute;
    top: 100%;
    right: 6px;
    border-width: 6px;
    border-style: solid;
    border-color: #0f172a transparent transparent transparent;
}
.pjn-tooltip-header {
    display: block;
    color: #60a5fa;
    font-size: 13px;
    font-weight: 700;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
}
.pjn-tooltip-list {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.pjn-tooltip-list li {
    font-size: 11.5px;
    line-height: 1.4;
    color: #cbd5e1;
}
.pjn-tooltip-list li strong {
    display: block;
    color: #f8fafc;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 2px;
}
.pjn-info-trigger:hover .pjn-tooltip-box,
.pjn-info-trigger:focus .pjn-tooltip-box,
.pjn-info-trigger:focus-within .pjn-tooltip-box {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

/* DROPZONE */
.pjn-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 32px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
    background: #f8fafc;
}
.pjn-dropzone:hover, .pjn-dropzone.drag-over {
    border-color: #1d4ed8;
    background: #eff6ff;
}
.pjn-dz-icon { margin-bottom: 10px; }
.pjn-dz-text { font-size: 14px; font-weight: 600; color: #475569; }
.pjn-dz-hint { font-size: 12px; color: #94a3b8; margin-top: 4px; }
.pjn-dz-filename {
    margin-top: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #1d4ed8;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 6px 12px;
    display: inline-block;
}

/* SUBMIT AREA */
.pjn-submit-area {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 36px;
    box-shadow: 0 2px 10px rgba(11,58,107,0.05);
}
.pjn-submit-info {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 12px;
    color: #1e40af;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 10px 14px;
    margin-bottom: 18px;
    line-height: 1.6;
}
.pjn-submit-info svg { flex-shrink: 0; margin-top: 1px; }
.pjn-submit-btns {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}
.pjn-btn-submit {
    background: linear-gradient(90deg, #0d3878 0%, #1653ce 100%);
    border: none;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 13px 32px;
    font-size: 15px;
    border-radius: 8px;
    font-weight: 700;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    box-shadow: 0 4px 14px rgba(22, 83, 206, 0.3);
    cursor: pointer;
}
.pjn-btn-submit:hover {
    background: linear-gradient(90deg, #092a5c 0%, #1245b0 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(22, 83, 206, 0.42);
}
.pjn-btn-submit:active { transform: translateY(1px); box-shadow: 0 2px 6px rgba(22, 83, 206, 0.2); }

.pjn-btn-back {
    background: #ffffff;
    color: #0c3575;
    border: 1.5px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 22px;
    font-size: 14px;
    border-radius: 8px;
    font-weight: 600;
    transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
    text-decoration: none;
}
.pjn-btn-back:hover {
    background: #f1f5f9;
    border-color: #0c3575;
    color: #0c3575;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(11, 58, 107, 0.12);
}
.pjn-btn-back:active {
    transform: translateY(1px);
    box-shadow: none;
}

@media (max-width: 768px) {
    .pjn-hero { padding: 28px 20px 22px; }
    .pjn-hero-title { font-size: 20px; }
    .pjn-grid-2 { grid-template-columns: 1fr; }
    .pjn-checkbox-group { grid-template-columns: 1fr; }
    .pjn-card-head { flex-wrap: wrap; }
    .pjn-card-badge { margin-left: 0; }
    .pjn-submit-btns { flex-direction: column; }
    .pjn-step-label { display: none; }
}
</style>

<script>
/* =================== PDDIKTI AUTOCOMPLETE =================== */

// Large curated local fallback dataset (all major PT in Indonesia)
var pddiktiLocalData = [
    {id:'001',nama:'Universitas Indonesia',kota:'Depok',provinsi:'Jawa Barat',jenis:'Universitas Negeri'},
    {id:'002',nama:'Institut Teknologi Bandung',kota:'Bandung',provinsi:'Jawa Barat',jenis:'Institut Negeri'},
    {id:'003',nama:'Universitas Gadjah Mada',kota:'Yogyakarta',provinsi:'DI Yogyakarta',jenis:'Universitas Negeri'},
    {id:'004',nama:'Institut Pertanian Bogor',kota:'Bogor',provinsi:'Jawa Barat',jenis:'Institut Negeri'},
    {id:'005',nama:'Universitas Diponegoro',kota:'Semarang',provinsi:'Jawa Tengah',jenis:'Universitas Negeri'},
    {id:'006',nama:'Universitas Brawijaya',kota:'Malang',provinsi:'Jawa Timur',jenis:'Universitas Negeri'},
    {id:'007',nama:'Universitas Airlangga',kota:'Surabaya',provinsi:'Jawa Timur',jenis:'Universitas Negeri'},
    {id:'008',nama:'Universitas Padjadjaran',kota:'Sumedang',provinsi:'Jawa Barat',jenis:'Universitas Negeri'},
    {id:'009',nama:'Universitas Hasanuddin',kota:'Makassar',provinsi:'Sulawesi Selatan',jenis:'Universitas Negeri'},
    {id:'010',nama:'Universitas Sumatera Utara',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Universitas Negeri'},
    {id:'011',nama:'Universitas Andalas',kota:'Padang',provinsi:'Sumatera Barat',jenis:'Universitas Negeri'},
    {id:'012',nama:'Universitas Sriwijaya',kota:'Palembang',provinsi:'Sumatera Selatan',jenis:'Universitas Negeri'},
    {id:'013',nama:'Universitas Riau',kota:'Pekanbaru',provinsi:'Riau',jenis:'Universitas Negeri'},
    {id:'014',nama:'Universitas Lampung',kota:'Bandar Lampung',provinsi:'Lampung',jenis:'Universitas Negeri'},
    {id:'015',nama:'Universitas Syiah Kuala',kota:'Banda Aceh',provinsi:'Aceh',jenis:'Universitas Negeri'},
    {id:'016',nama:'Institut Agama Islam Negeri Medan',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Institut Negeri'},
    {id:'017',nama:'Universitas Negeri Medan',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Universitas Negeri'},
    {id:'018',nama:'Politeknik Negeri Medan',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Politeknik Negeri'},
    {id:'019',nama:'Universitas HKBP Nommensen',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Universitas Swasta'},
    {id:'020',nama:'Universitas Muhammadiyah Sumatera Utara',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Universitas Swasta'},
    {id:'021',nama:'Universitas Prima Indonesia',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Universitas Swasta'},
    {id:'022',nama:'Universitas Medan Area',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Universitas Swasta'},
    {id:'023',nama:'Institut Teknologi Del',kota:'Laguboti',provinsi:'Sumatera Utara',jenis:'Institut Swasta'},
    {id:'024',nama:'Universitas Negeri Jakarta',kota:'Jakarta',provinsi:'DKI Jakarta',jenis:'Universitas Negeri'},
    {id:'025',nama:'Universitas Pendidikan Indonesia',kota:'Bandung',provinsi:'Jawa Barat',jenis:'Universitas Negeri'},
    {id:'026',nama:'Universitas Negeri Surabaya',kota:'Surabaya',provinsi:'Jawa Timur',jenis:'Universitas Negeri'},
    {id:'027',nama:'Universitas Negeri Yogyakarta',kota:'Yogyakarta',provinsi:'DI Yogyakarta',jenis:'Universitas Negeri'},
    {id:'028',nama:'Universitas Negeri Semarang',kota:'Semarang',provinsi:'Jawa Tengah',jenis:'Universitas Negeri'},
    {id:'029',nama:'Universitas Negeri Padang',kota:'Padang',provinsi:'Sumatera Barat',jenis:'Universitas Negeri'},
    {id:'030',nama:'Universitas Negeri Makassar',kota:'Makassar',provinsi:'Sulawesi Selatan',jenis:'Universitas Negeri'},
    {id:'031',nama:'Institut Teknologi Sepuluh Nopember',kota:'Surabaya',provinsi:'Jawa Timur',jenis:'Institut Negeri'},
    {id:'032',nama:'Institut Teknologi Medan',kota:'Medan',provinsi:'Sumatera Utara',jenis:'Institut Swasta'},
    {id:'033',nama:'Universitas Bina Nusantara',kota:'Jakarta',provinsi:'DKI Jakarta',jenis:'Universitas Swasta'},
    {id:'034',nama:'Universitas Pelita Harapan',kota:'Tangerang',provinsi:'Banten',jenis:'Universitas Swasta'},
    {id:'035',nama:'Universitas Atma Jaya Yogyakarta',kota:'Yogyakarta',provinsi:'DI Yogyakarta',jenis:'Universitas Swasta'},
    {id:'036',nama:'Universitas Muhammadiyah Yogyakarta',kota:'Yogyakarta',provinsi:'DI Yogyakarta',jenis:'Universitas Swasta'},
    {id:'037',nama:'Universitas Islam Indonesia',kota:'Yogyakarta',provinsi:'DI Yogyakarta',jenis:'Universitas Swasta'},
    {id:'038',nama:'Universitas Trisakti',kota:'Jakarta',provinsi:'DKI Jakarta',jenis:'Universitas Swasta'},
    {id:'039',nama:'Universitas Mercu Buana',kota:'Jakarta',provinsi:'DKI Jakarta',jenis:'Universitas Swasta'},
    {id:'040',nama:'Universitas Tarumanagara',kota:'Jakarta',provinsi:'DKI Jakarta',jenis:'Universitas Swasta'},
    {id:'041',nama:'Universitas Telkom',kota:'Bandung',provinsi:'Jawa Barat',jenis:'Universitas Swasta'},
    {id:'042',nama:'Universitas Maranatha',kota:'Bandung',provinsi:'Jawa Barat',jenis:'Universitas Swasta'},
    {id:'043',nama:'Universitas Parahyangan',kota:'Bandung',provinsi:'Jawa Barat',jenis:'Universitas Swasta'},
    {id:'044',nama:'Universitas Pasundan',kota:'Bandung',provinsi:'Jawa Barat',jenis:'Universitas Swasta'},
    {id:'045',nama:'Universitas Sanata Dharma',kota:'Yogyakarta',provinsi:'DI Yogyakarta',jenis:'Universitas Swasta'},
    {id:'046',nama:'Universitas Udayana',kota:'Denpasar',provinsi:'Bali',jenis:'Universitas Negeri'},
    {id:'047',nama:'Universitas Mataram',kota:'Mataram',provinsi:'Nusa Tenggara Barat',jenis:'Universitas Negeri'},
    {id:'048',nama:'Universitas Tanjungpura',kota:'Pontianak',provinsi:'Kalimantan Barat',jenis:'Universitas Negeri'},
    {id:'049',nama:'Universitas Lambung Mangkurat',kota:'Banjarmasin',provinsi:'Kalimantan Selatan',jenis:'Universitas Negeri'},
    {id:'050',nama:'Universitas Mulawarman',kota:'Samarinda',provinsi:'Kalimantan Timur',jenis:'Universitas Negeri'},
    {id:'051',nama:'Universitas Sam Ratulangi',kota:'Manado',provinsi:'Sulawesi Utara',jenis:'Universitas Negeri'},
    {id:'052',nama:'Universitas Pattimura',kota:'Ambon',provinsi:'Maluku',jenis:'Universitas Negeri'},
    {id:'053',nama:'Universitas Cenderawasih',kota:'Jayapura',provinsi:'Papua',jenis:'Universitas Negeri'},
    {id:'054',nama:'Universitas Tadulako',kota:'Palu',provinsi:'Sulawesi Tengah',jenis:'Universitas Negeri'},
    {id:'055',nama:'Universitas Haluoleo',kota:'Kendari',provinsi:'Sulawesi Tenggara',jenis:'Universitas Negeri'},
    {id:'056',nama:'Universitas Bangka Belitung',kota:'Pangkalpinang',provinsi:'Kepulauan Bangka Belitung',jenis:'Universitas Negeri'},
    {id:'057',nama:'Universitas Maritim Raja Ali Haji',kota:'Tanjungpinang',provinsi:'Kepulauan Riau',jenis:'Universitas Negeri'},
    {id:'058',nama:'Universitas Jambi',kota:'Jambi',provinsi:'Jambi',jenis:'Universitas Negeri'},
    {id:'059',nama:'Universitas Bengkulu',kota:'Bengkulu',provinsi:'Bengkulu',jenis:'Universitas Negeri'},
    {id:'060',nama:'Politeknik Negeri Jakarta',kota:'Depok',provinsi:'Jawa Barat',jenis:'Politeknik Negeri'},
    {id:'061',nama:'Politeknik Elektronika Negeri Surabaya',kota:'Surabaya',provinsi:'Jawa Timur',jenis:'Politeknik Negeri'},
    {id:'062',nama:'Universitas Islam Negeri Sunan Kalijaga',kota:'Yogyakarta',provinsi:'DI Yogyakarta',jenis:'Universitas Negeri'},
    {id:'063',nama:'Universitas Islam Negeri Syarif Hidayatullah',kota:'Tangerang Selatan',provinsi:'Banten',jenis:'Universitas Negeri'},
    {id:'064',nama:'Universitas Islam Negeri Sunan Ampel',kota:'Surabaya',provinsi:'Jawa Timur',jenis:'Universitas Negeri'},
    {id:'065',nama:'Universitas Islam Negeri Alauddin',kota:'Makassar',provinsi:'Sulawesi Selatan',jenis:'Universitas Negeri'},
    {id:'066',nama:'Institut Teknologi Sumatera',kota:'Lampung Selatan',provinsi:'Lampung',jenis:'Institut Negeri'},
    {id:'067',nama:'Institut Teknologi Kalimantan',kota:'Balikpapan',provinsi:'Kalimantan Timur',jenis:'Institut Negeri'},
    {id:'068',nama:'Sekolah Tinggi Akuntansi Negara',kota:'Tangerang Selatan',provinsi:'Banten',jenis:'Sekolah Tinggi Negeri'},
    {id:'069',nama:'Universitas Muhammadiyah Malang',kota:'Malang',provinsi:'Jawa Timur',jenis:'Universitas Swasta'},
    {id:'070',nama:'Universitas Muhammadiyah Jakarta',kota:'Jakarta',provinsi:'DKI Jakarta',jenis:'Universitas Swasta'},
    {id:'071',nama:'Universitas Muhammadiyah Makassar',kota:'Makassar',provinsi:'Sulawesi Selatan',jenis:'Universitas Swasta'},
    {id:'072',nama:'Universitas Dian Nuswantoro',kota:'Semarang',provinsi:'Jawa Tengah',jenis:'Universitas Swasta'},
    {id:'073',nama:'Universitas Soedirman',kota:'Purwokerto',provinsi:'Jawa Tengah',jenis:'Universitas Negeri'},
    {id:'074',nama:'Universitas Sebelas Maret',kota:'Surakarta',provinsi:'Jawa Tengah',jenis:'Universitas Negeri'},
    {id:'075',nama:'Universitas Jenderal Soedirman',kota:'Purwokerto',provinsi:'Jawa Tengah',jenis:'Universitas Negeri'},
];

var searchTimeout = null; // timeout handler for debounce
var currentQuery = '';

function pddiktiSearch(val) {
    var q = val.trim();
    currentQuery = q;
    clearTimeout(searchTimeout);

    var dropdown = document.getElementById('pddikti-dropdown');
    var spinner = document.getElementById('pddikti-spinner');
    var selectedInfo = document.getElementById('pddikti-selected-info');
    var hiddenId = document.getElementById('pddikti-id');

    // Clear selection if user types again
    if (selectedInfo.style.display !== 'none') {
        selectedInfo.style.display = 'none';
        hiddenId.value = '';
    }

    if (q.length < 2) {
        dropdown.style.display = 'none';
        return;
    }

    spinner.style.display = 'block';
    dropdown.style.display = 'none';

    searchTimeout = setTimeout(function() {
        // Try PDDIKTI API first, then fallback to local
        tryPddiktiApi(q, function(results) {
            spinner.style.display = 'none';
            if (currentQuery !== q) return;
            renderDropdown(results, q, 'sumber: PDDIKTI Kemdikbud');
        }, function() {
            // Fallback to local data
            spinner.style.display = 'none';
            if (currentQuery !== q) return;
            var results = searchLocal(q);
            renderDropdown(results, q, 'sumber: Data Lokal (PDDIKTI tidak tersedia)');
        });
    }, 150);
}

function tryPddiktiApi(q, onSuccess, onFail) {
    // 1. Try Laravel Server Backend API Proxy (No CORS issue)
    fetch('/api/pddikti?q=' + encodeURIComponent(q), { signal: AbortSignal.timeout(4000) })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res && res.data && res.data.length > 0) {
                onSuccess(res.data);
                return;
            }
            // 2. Direct external API fallback
            tryDirectEndpoints(q, onSuccess, onFail);
        })
        .catch(function() {
            tryDirectEndpoints(q, onSuccess, onFail);
        });
}

function tryDirectEndpoints(q, onSuccess, onFail) {
    var endpoints = [
        'https://api-frontend.kemdikbud.go.id/hit_mhs_token/univ?keyword=' + encodeURIComponent(q),
        'https://pddikti.kemdikbud.go.id/api/pencarian/pt/' + encodeURIComponent(q),
    ];
    var tried = 0;
    function tryNext() {
        if (tried >= endpoints.length) { onFail(); return; }
        var url = endpoints[tried++];
        fetch(url, { signal: AbortSignal.timeout(3500) })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                var mapped = [];
                if (Array.isArray(data)) {
                    mapped = data.slice(0,10).map(function(d) {
                        return {
                            id: d.id || d.kode_pt || '',
                            nama: d.nama || d.nama_pt || d.text || '',
                            kota: d.kota || d.kabkota || '',
                            provinsi: d.provinsi || d.nm_prov || '',
                            jenis: d.jenis || d.bentuk_pt || 'Perguruan Tinggi'
                        };
                    }).filter(function(d) { return d.nama; });
                } else if (data && data.data && Array.isArray(data.data)) {
                    mapped = data.data.slice(0,10).map(function(d) {
                        return { id: d.id || '', nama: d.nama || '', kota: d.kota || '', provinsi: d.provinsi || '', jenis: d.jenis || '' };
                    }).filter(function(d) { return d.nama; });
                }

                if (mapped.length > 0) {
                    onSuccess(mapped);
                } else {
                    tryNext();
                }
            })
            .catch(function() { tryNext(); });
    }
    tryNext();
}

function searchLocal(q) {
    var lower = q.toLowerCase();
    return pddiktiLocalData.filter(function(d) {
        return d.nama.toLowerCase().indexOf(lower) !== -1 ||
               d.kota.toLowerCase().indexOf(lower) !== -1;
    }).slice(0, 12);
}

function renderDropdown(results, q, source) {
    var dropdown = document.getElementById('pddikti-dropdown');
    var html = '';

    if (results.length === 0) {
        html = '<div class="pjn-dropdown-empty">Tidak ditemukan. Silakan ketik nama instansi secara manual.</div>';
    } else {
        results.forEach(function(item, idx) {
            var nameHighlighted = highlightMatch(item.nama, q);
            var meta = [item.jenis, item.kota, item.provinsi].filter(Boolean).join(' &bull; ');
            html += '<div class="pjn-dropdown-item" tabindex="-1" onclick="selectPT(\'' +
                escStr(item.id) + '\', \'' + escStr(item.nama) + '\', \'' + escStr(item.kota) + '\', \'' + escStr(item.provinsi) + '\', \'' + escStr(item.jenis) + '\')">' +
                '<div class="pjn-dropdown-item-name">' + nameHighlighted + '</div>' +
                '<div class="pjn-dropdown-item-meta">' + meta + '</div>' +
                '</div>';
        });
        html += '<div class="pjn-dropdown-source">' + source + '</div>';
    }
    dropdown.innerHTML = html;
    dropdown.style.display = 'block';
}

function highlightMatch(str, q) {
    var i = str.toLowerCase().indexOf(q.toLowerCase());
    if (i === -1) return escHtml(str);
    return escHtml(str.substring(0, i)) +
        '<mark style="background:#fef08a;color:#713f12;border-radius:2px;padding:0 1px;">' + escHtml(str.substring(i, i + q.length)) + '</mark>' +
        escHtml(str.substring(i + q.length));
}

function selectPT(id, nama, kota, provinsi, jenis) {
    document.getElementById('pddikti-input').value = nama;
    document.getElementById('pddikti-id').value = id;
    document.getElementById('pddikti-dropdown').style.display = 'none';

    // Auto-fill kota & provinsi if empty
    var kotaField = document.getElementById('kota');
    var provField = document.getElementById('provinsi');
    if (kotaField && !kotaField.value && kota) kotaField.value = kota;
    if (provField && !provField.value) {
        for (var i = 0; i < provField.options.length; i++) {
            if (provField.options[i].value.toLowerCase().indexOf(provinsi.toLowerCase()) !== -1 ||
                provinsi.toLowerCase().indexOf(provField.options[i].value.toLowerCase()) !== -1) {
                provField.selectedIndex = i; break;
            }
        }
    }

    var info = document.getElementById('pddikti-selected-info');
    info.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#1d4ed8" stroke-width="2"/><path d="M9 12l2 2 4-4" stroke="#1d4ed8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span>Terverifikasi: <b>' + escHtml(nama) + '</b>' + (kota ? ' &mdash; ' + escHtml(kota) : '') + '</span>';
    info.style.display = 'flex';
}

function escStr(s) {
    return (s || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}
function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
}

// Close dropdown on outside click
document.addEventListener('click', function(e) {
    var wrap = document.getElementById('pddikti-wrap');
    var dd = document.getElementById('pddikti-dropdown');
    if (wrap && !wrap.contains(e.target)) {
        if (dd) dd.style.display = 'none';
    }
});

/* =================== DROPZONE =================== */
function showFileName(input) {
    var fn = document.getElementById('pjn-file-name');
    if (input.files && input.files[0]) {
        fn.textContent = input.files[0].name;
        fn.style.display = 'inline-block';
    }
}
var dz = document.getElementById('pjn-dropzone');
if (dz) {
    dz.addEventListener('dragover', function(e) { e.preventDefault(); dz.classList.add('drag-over'); });
    dz.addEventListener('dragleave', function() { dz.classList.remove('drag-over'); });
    dz.addEventListener('drop', function(e) {
        e.preventDefault();
        dz.classList.remove('drag-over');
        var fi = document.getElementById('file-surat');
        if (fi && e.dataTransfer.files.length) {
            fi.files = e.dataTransfer.files;
            showFileName(fi);
        }
    });
}

/* =================== FORM VALIDATION & AUTO-DRAFT =================== */
var DRAFT_STORAGE_KEY = 'kerjasama_mitra_draft_v1';

// Auto-save form inputs to localStorage
function saveDraft() {
    try {
        var form = document.getElementById('pjn-form');
        if (!form) return;
        var data = {};
        
        // Text, email, select, textarea
        var inputs = form.querySelectorAll('input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]), select, textarea');
        inputs.forEach(function(inp) {
            if (inp.name) data[inp.name] = inp.value;
        });

        // Checkboxes
        var checks = form.querySelectorAll('input[type="checkbox"][name="bidang[]"]:checked');
        data['bidang'] = [];
        checks.forEach(function(cb) {
            data['bidang'].push(cb.value);
        });

        // Radio
        var selectedRadio = form.querySelector('input[type="radio"][name="pembiayaan"]:checked');
        if (selectedRadio) data['pembiayaan'] = selectedRadio.value;

        localStorage.setItem(DRAFT_STORAGE_KEY, JSON.stringify(data));
    } catch (e) {
        console.warn('Draft save error:', e);
    }
}

// Restore draft from localStorage
function restoreDraft() {
    try {
        @if(session('ok'))
        localStorage.removeItem(DRAFT_STORAGE_KEY);
        return;
        @endif

        var saved = localStorage.getItem(DRAFT_STORAGE_KEY);
        if (!saved) return;
        var data = JSON.parse(saved);
        if (!data || typeof data !== 'object') return;

        var form = document.getElementById('pjn-form');
        if (!form) return;

        Object.keys(data).forEach(function(key) {
            if (key === 'bidang' && Array.isArray(data[key])) {
                data[key].forEach(function(val) {
                    var cb = form.querySelector('input[type="checkbox"][name="bidang[]"][value="' + val + '"]');
                    if (cb) cb.checked = true;
                });
            } else if (key === 'pembiayaan') {
                var rad = form.querySelector('input[type="radio"][name="pembiayaan"][value="' + data[key] + '"]');
                if (rad) rad.checked = true;
            } else {
                var el = form.querySelector('[name="' + key + '"]');
                if (el && !el.value) {
                    el.value = data[key];
                }
            }
        });
    } catch (e) {
        console.warn('Draft restore error:', e);
    }
}

// Show field error message
function setFieldError(fieldEl, groupEl, msg) {
    if (fieldEl) fieldEl.classList.add('is-invalid');
    if (groupEl) groupEl.classList.add('pjn-group-invalid');

    var parent = groupEl || fieldEl.closest('.pjn-field') || fieldEl.parentElement;
    if (!parent) return;

    var existingErr = parent.querySelector('.pjn-field-error');
    if (!existingErr) {
        var errDiv = document.createElement('div');
        errDiv.className = 'pjn-field-error';
        errDiv.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#dc2626" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="#dc2626" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="#dc2626" stroke-width="2" stroke-linecap="round"/></svg><span>' + msg + '</span>';
        parent.appendChild(errDiv);
    } else {
        existingErr.querySelector('span').textContent = msg;
    }
}

// Clear field error
function clearFieldError(fieldEl, groupEl) {
    if (fieldEl) fieldEl.classList.remove('is-invalid');
    if (groupEl) groupEl.classList.remove('pjn-group-invalid');

    var parent = groupEl || (fieldEl ? (fieldEl.closest('.pjn-field') || fieldEl.parentElement) : null);
    if (parent) {
        var existingErr = parent.querySelector('.pjn-field-error');
        if (existingErr) existingErr.remove();
    }
}

// Show floating toast alert
function showValidationToast(msg) {
    var existingToast = document.querySelector('.pjn-toast-alert');
    if (existingToast) existingToast.remove();

    var toast = document.createElement('div');
    toast.className = 'pjn-toast-alert';
    toast.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" style="flex-shrink:0;"><circle cx="12" cy="12" r="10" stroke="#fff" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="#fff" stroke-width="2" stroke-linecap="round"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg><div>' + msg + '</div>';
    
    toast.onclick = function() { toast.remove(); };
    document.body.appendChild(toast);

    setTimeout(function() {
        if (toast.parentNode) {
            toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(function() { toast.remove(); }, 400);
        }
    }, 5500);
}

// Validate entire form
function validatePengajuanForm() {
    var form = document.getElementById('pjn-form');
    if (!form) return true;

    var errors = [];
    var firstErrorEl = null;

    // Helper to register error
    function reportErr(el, groupEl, msg) {
        setFieldError(el, groupEl, msg);
        errors.push({ el: el, groupEl: groupEl, msg: msg });
        if (!firstErrorEl) firstErrorEl = el || groupEl;
    }

    // 1. Instansi
    var instansi = document.getElementById('pddikti-input');
    if (!instansi || !instansi.value.trim()) {
        reportErr(instansi, null, 'Nama Perguruan Tinggi / Instansi wajib diisi');
    } else {
        clearFieldError(instansi, null);
    }

    // 2. Jenis Instansi
    var jenisInstansi = document.getElementById('jenis_instansi');
    if (!jenisInstansi || !jenisInstansi.value) {
        reportErr(jenisInstansi, null, 'Jenis Instansi wajib dipilih');
    } else {
        clearFieldError(jenisInstansi, null);
    }

    // 3. Organisasi / Fakultas
    var org = document.getElementById('organisasi');
    if (!org || !org.value.trim()) {
        reportErr(org, null, 'Unit Kerja / Divisi / Fakultas wajib diisi');
    } else {
        clearFieldError(org, null);
    }

    // 4. Kota
    var kota = document.getElementById('kota');
    if (!kota || !kota.value.trim()) {
        reportErr(kota, null, 'Kota / Kabupaten wajib diisi');
    } else {
        clearFieldError(kota, null);
    }

    // 5. Provinsi
    var prov = document.getElementById('provinsi');
    if (!prov || !prov.value) {
        reportErr(prov, null, 'Provinsi wajib dipilih');
    } else {
        clearFieldError(prov, null);
    }

    // 6. Alamat
    var alamat = document.getElementById('alamat');
    if (!alamat || !alamat.value.trim()) {
        reportErr(alamat, null, 'Alamat lengkap instansi wajib diisi');
    } else {
        clearFieldError(alamat, null);
    }

    // 7. PIC
    var pic = document.getElementById('pic');
    if (!pic || !pic.value.trim()) {
        reportErr(pic, null, 'Nama lengkap PIC wajib diisi');
    } else {
        clearFieldError(pic, null);
    }

    // 8. Email PIC
    var email = document.getElementById('email');
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !email.value.trim()) {
        reportErr(email, null, 'Email resmi PIC wajib diisi');
    } else if (!emailRegex.test(email.value.trim())) {
        reportErr(email, null, 'Format email tidak valid (contoh: pic@instansi.ac.id)');
    } else {
        clearFieldError(email, null);
    }

    // 9. Telepon / WA
    var telp = document.getElementById('telp');
    if (!telp || !telp.value.trim()) {
        reportErr(telp, null, 'Nomor telepon / WhatsApp PIC wajib diisi');
    } else {
        clearFieldError(telp, null);
    }

    // 10. Bidang Kerja Sama (Checkbox min 1)
    var bidangChecked = form.querySelectorAll('input[type="checkbox"][name="bidang[]"]:checked');
    var bidangGroup = form.querySelector('.pjn-checkbox-group');
    if (bidangChecked.length === 0) {
        reportErr(null, bidangGroup, 'Pilih minimal salah satu bidang kerja sama yang diminati');
    } else {
        clearFieldError(null, bidangGroup);
    }

    // 11. Tujuan Pengajuan Mitra
    var tujuanMitra = document.getElementById('tujuan_mitra');
    if (!tujuanMitra || !tujuanMitra.value.trim()) {
        reportErr(tujuanMitra, null, 'Tujuan pengajuan dari mitra wajib diisi');
    } else {
        clearFieldError(tujuanMitra, null);
    }

    // 12. Metode Pelatihan
    var metode = document.getElementById('metode_pelatihan');
    if (!metode || !metode.value) {
        reportErr(metode, null, 'Metode pelatihan wajib dipilih');
    } else {
        clearFieldError(metode, null);
    }

    // 13. Tujuan Pelatihan
    var tujuanPelatihan = document.getElementById('tujuan_pelatihan');
    if (!tujuanPelatihan || !tujuanPelatihan.value.trim()) {
        reportErr(tujuanPelatihan, null, 'Tujuan pelatihan wajib diisi');
    } else {
        clearFieldError(tujuanPelatihan, null);
    }

    // 14. Pembiayaan (Radio)
    var pembiayaanSelected = form.querySelector('input[type="radio"][name="pembiayaan"]:checked');
    var pembiayaanGroup = form.querySelector('.pjn-pembiayaan-group');
    if (!pembiayaanSelected) {
        reportErr(null, pembiayaanGroup, 'Skema pembiayaan (APBD / APBN / Cost Sharing) wajib dipilih');
    } else {
        clearFieldError(null, pembiayaanGroup);
    }

    if (errors.length > 0) {
        // Scroll smoothly to first error element
        if (firstErrorEl) {
            firstErrorEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (typeof firstErrorEl.focus === 'function') {
                setTimeout(function() { firstErrorEl.focus(); }, 400);
            }
        }
        showValidationToast('Mohon lengkapi ' + errors.length + ' data yang bertanda merah sebelum mengirim pengajuan.');
        return false;
    }

    return true;
}

// Real-time input error clearing & draft auto-save
document.addEventListener('DOMContentLoaded', function() {
    restoreDraft();

    var form = document.getElementById('pjn-form');
    if (!form) return;

    form.addEventListener('input', function(e) {
        var el = e.target;
        if (el && el.value && el.value.trim().length > 0) {
            clearFieldError(el, null);
        }
        saveDraft();
    });

    form.addEventListener('change', function(e) {
        var el = e.target;
        if (el) {
            if (el.type === 'checkbox' && el.name === 'bidang[]') {
                var bidangGroup = form.querySelector('.pjn-checkbox-group');
                var anyChecked = form.querySelectorAll('input[type="checkbox"][name="bidang[]"]:checked').length > 0;
                if (anyChecked) clearFieldError(null, bidangGroup);
            } else if (el.type === 'radio' && el.name === 'pembiayaan') {
                var pembiayaanGroup = form.querySelector('.pjn-pembiayaan-group');
                clearFieldError(null, pembiayaanGroup);
            } else if (el.value) {
                clearFieldError(el, null);
            }
        }
        saveDraft();
    });

    // Form submit listener
    form.addEventListener('submit', function(e) {
        if (!validatePengajuanForm()) {
            e.preventDefault();
            return false;
        }

        var btn = document.getElementById('btn-submit');
        var txt = document.getElementById('btn-text');
        if (btn) {
            btn.disabled = true;
            if (txt) txt.textContent = 'Mengirim Pengajuan...';
            btn.style.opacity = '0.85';
            btn.style.pointerEvents = 'none';
        }
    });
});
</script>
@endsection
