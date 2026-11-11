/*
    Satu-satunya JavaScript untuk halaman login (mitra & admin).
    Tanpa data pengguna di dalam argumen; data lewat atribut data-*.
    Catatan: berkas ini disajikan statis, jadi komentar wajib sintaks JS.
*/
(function () {
    'use strict';
    document.documentElement.classList.add('js-on');

    // Tampilkan / sembunyikan kata sandi.
    var toggles = document.querySelectorAll('[data-toggle-password]');
    Array.prototype.forEach.call(toggles, function (btn) {
        btn.addEventListener('click', function () {
            var field = document.getElementById(btn.getAttribute('data-toggle-password'));
            if (!field) { return; }
            var tampil = field.type === 'password';
            field.type = tampil ? 'text' : 'password';
            btn.textContent = tampil ? 'Sembunyikan' : 'Tampilkan';
            btn.setAttribute('aria-pressed', tampil ? 'true' : 'false');
        });
    });

    // Cegah kirim ganda: nonaktifkan tombol sekali form valid dikirim.
    var forms = document.querySelectorAll('[data-form-login]');
    Array.prototype.forEach.call(forms, function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('[data-submit]');
            if (!btn) { return; }
            btn.setAttribute('aria-disabled', 'true');
            btn.textContent = 'Memproses...';
            window.setTimeout(function () {
                btn.removeAttribute('aria-disabled');
                btn.textContent = 'Masuk';
            }, 8000);
        });
    });
})();
