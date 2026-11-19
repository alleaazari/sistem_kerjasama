/*
    Area mitra: cegah kirim ganda pada formulir (profil & MoU).
    Tanpa dialog peringatan bawaan browser, tanpa pesan sukses buatan klien.
    Catatan: berkas ini disajikan statis, jadi komentar wajib sintaks JS.
*/
(function () {
    'use strict';
    document.documentElement.classList.add('js-on');

    var forms = document.querySelectorAll('[data-form-profil], [data-form-mou]');
    Array.prototype.forEach.call(forms, function (form) {
        form.addEventListener('submit', function () {
            var tombol = form.querySelector('[data-submit]');
            if (!tombol) { return; }
            tombol.disabled = true;
            tombol.setAttribute('aria-disabled', 'true');
            tombol.textContent = 'Menyimpan...';
        });
    });

    // Nomor telepon: buang semua karakter non-angka (mengetik maupun tempel).
    var telp = document.getElementById('telp');
    if (telp) {
        telp.addEventListener('input', function () {
            var bersih = telp.value.replace(/[^0-9]/g, '');
            if (bersih !== telp.value) { telp.value = bersih; }
        });
    }

    // Tampilkan nama berkas yang dipilih (teks biasa, bukan HTML).
    var input = document.getElementById('berkas');
    var label = document.getElementById('nama-berkas');
    if (input && label) {
        input.addEventListener('change', function () {
            label.textContent = input.files && input.files.length
                ? input.files[0].name
                : '';
        });
    }
})();
