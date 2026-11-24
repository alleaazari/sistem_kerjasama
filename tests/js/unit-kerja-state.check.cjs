// Self-check logika state dropdown Unit Kerja (tanpa dependensi).
// `node tests/js/unit-kerja-state.check.cjs`
//
// Fungsi diambil LANGSUNG dari resources/views/pengajuan.blade.php lalu
// dijalankan dengan stub DOM minimal, untuk membuktikan transisi state:
//   A. pilih kampus  -> daftar unit terisi, opsi "Isi sendiri" paling bawah
//   B. isi sendiri   -> mode manual, nilai final = teks input
//   C. ganti instansi -> data lama tidak tertinggal

const fs = require('fs');
const path = require('path');

const blade = fs.readFileSync(
    path.join(__dirname, '../../resources/views/pengajuan.blade.php'),
    'utf8'
);

function ambil(nama, akhirPenanda) {
    const mulai = blade.indexOf('function ' + nama + '(');
    const akhir = blade.indexOf(akhirPenanda, mulai);
    if (mulai === -1 || akhir === -1) {
        throw new Error('tidak menemukan fungsi ' + nama);
    }
    return blade.slice(mulai, akhir);
}

// ---- Stub DOM minimal ------------------------------------------------------
function buatOption(value, text) {
    return { value, textContent: text, selected: false };
}

function buatSelect() {
    return {
        options: [],
        value: '',
        disabled: false,
        hidden: false,
        required: false,
        _attrs: {},
        getAttribute(k) { return Object.prototype.hasOwnProperty.call(this._attrs, k) ? this._attrs[k] : null; },
        setAttribute(k, v) { this._attrs[k] = String(v); },
        appendChild(o) { this.options.push(o); },
        remove(i) { this.options.splice(i, 1); },
        focus() {},
    };
}

function buatInput() {
    return { value: '', hidden: false, required: false, focus() {}, addEventListener() {} };
}

const els = {
    organisasi: buatSelect(),
    'organisasi-custom': buatInput(),
    'organisasi-kembali': { hidden: true, addEventListener() {} },
    'organisasi-hint': { textContent: '' },
};

global.document = {
    getElementById(id) { return els[id] || null; },
    createElement(tag) { return { tag, value: '', textContent: '' }; },
};

global.Event = function (nama) { this.type = nama; };

// Muat fungsi dari Blade.
const sumber =
    ambil('unitModeManual', '// Isi dropdown unit.') +
    ambil('isiDropdownUnit', '// Kosongkan + nonaktifkan dropdown') +
    ambil('resetDropdownUnit', '// Muat daftar unit dari endpoint') +
    'var UNIT_ISIAN_SENDIRI = "__custom__";\nvar UNIT_LABEL_KOSONG = "-- Pilih Instansi Terlebih Dahulu --";\n' +
    ambil('nilaiUnitKerja', '// Real-time input error clearing');

const api = new Function(sumber + '\nreturn { unitModeManual, isiDropdownUnit, resetDropdownUnit, nilaiUnitKerja };')();
const { isiDropdownUnit, resetDropdownUnit, nilaiUnitKerja } = api;

let gagal = 0;
function cek(nama, kondisi) {
    if (!kondisi) { gagal++; console.error('GAGAL: ' + nama); }
}

const sel = els.organisasi;

// A. Pilih kampus: dropdown terisi, "Isi sendiri" paling bawah.
isiDropdownUnit(['Fakultas Teknik', 'Fakultas Ilmu Komputer'], '');
cek('A: select aktif', sel.disabled === false);
cek('A: ada opsi fakultas', sel.options.some(o => o.value === 'Fakultas Teknik'));
cek('A: opsi terakhir = Isi sendiri', sel.options[sel.options.length - 1].value === '__custom__');

// B. Pilih "Isi sendiri": mode manual, nilai final dari input.
sel.value = '__custom__';
els['organisasi-custom'].value = '  Divisi Khusus  ';
const b = nilaiUnitKerja();
cek('B: nilai final = teks manual (trim)', b.nilai === 'Divisi Khusus');
cek('B: elemen yang divalidasi = input manual', b.el === els['organisasi-custom']);

// Nilai dari dropdown.
sel.value = 'Fakultas Teknik';
cek('B: nilai dari dropdown', nilaiUnitKerja().nilai === 'Fakultas Teknik');

// C. Ganti instansi: reset -> nonaktif, kosong, input manual bersih.
els['organisasi-custom'].value = 'sisa lama';
resetDropdownUnit();
cek('C: select nonaktif', sel.disabled === true);
cek('C: hanya opsi placeholder', sel.options.length === 1 && sel.options[0].value === '');
cek('C: input manual dikosongkan', els['organisasi-custom'].value === '');
cek('C: nilai final kosong', nilaiUnitKerja().nilai === '');

// C2. Isi ulang setelah reset tidak mewarisi pilihan lama.
isiDropdownUnit(['Biro Kerja Sama'], '');
cek('C2: opsi baru terpilih kosong', sel.value === '');
cek('C2: bukan "Isi sendiri"', sel.options[sel.options.length - 1].value === '__custom__');

if (gagal === 0) {
    console.log('OK: state unit kerja (A/B/C) sesuai');
} else {
    console.error(gagal + ' pemeriksaan gagal');
    process.exit(1);
}
