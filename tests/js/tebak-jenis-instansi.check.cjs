// Self-check tebakJenisInstansi: `node tests/js/tebak-jenis-instansi.check.js`
// Fungsi diambil LANGSUNG dari resources/views/pengajuan.blade.php agar tidak
// ada duplikasi logika yang bisa menyimpang dari kode sebenarnya.

const fs = require('fs');
const path = require('path');

const blade = fs.readFileSync(
    path.join(__dirname, '../../resources/views/pengajuan.blade.php'),
    'utf8'
);

// Ambil blok dari deklarasi JENIS_INSTANSI sampai akhir tebakJenisInstansi.
const mulai = blade.indexOf('var JENIS_INSTANSI = {');
const akhir = blade.indexOf('function petakanJenisPddikti');
if (mulai === -1 || akhir === -1 || akhir <= mulai) {
    console.error('GAGAL: tidak menemukan fungsi di pengajuan.blade.php');
    process.exit(1);
}

const sumber = blade.slice(mulai, akhir);
const muat = new Function(sumber + '\nreturn { JENIS_INSTANSI: JENIS_INSTANSI, tebakJenisInstansi: tebakJenisInstansi };');
const { tebakJenisInstansi } = muat();

const kasus = [
    ['Universitas Indonesia', 'perguruan_tinggi'],
    ['Institut Teknologi Bandung', 'perguruan_tinggi'],
    ['Politeknik Negeri Medan', 'perguruan_tinggi'],
    ['Sekolah Tinggi Akuntansi Negara', 'perguruan_tinggi'],
    ['Universitas Islam Negeri Sunan Kalijaga', 'perguruan_tinggi'],
    ['Dinas Komunikasi dan Informatika Kota Medan', 'pemerintah_daerah'],
    ['Pemerintah Kota Tanjungbalai', 'pemerintah_daerah'],
    ['Pemprov Sumatera Utara', 'pemerintah_daerah'],
    ['Kementerian Komunikasi dan Digital', 'kementerian'],
    ['Badan Pengembangan SDM Komdigi', ''], // ambigu tanpa "Kementerian"
    ['PT Telekomunikasi Nusantara', ''],
    ['Toko Bangunan Jaya', ''],
    ['', ''],
];

let gagal = 0;
for (const [nama, harap] of kasus) {
    const hasil = tebakJenisInstansi(nama);
    if (hasil !== harap) {
        gagal++;
        console.error(`GAGAL: "${nama}" => "${hasil}" (harap "${harap}")`);
    }
}

if (gagal === 0) {
    console.log(`OK: ${kasus.length} kasus lolos`);
} else {
    console.error(`${gagal} kasus gagal`);
    process.exit(1);
}
