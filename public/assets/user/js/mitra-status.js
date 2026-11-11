/*
    Area mitra: modal perpanjangan.
    Tanpa argumen berisi data; semua data lewat atribut data-*.
    Tanpa JS, halaman tetap utuh & terbaca (modal hanya tersembunyi).
    Catatan: berkas ini disajikan statis, jadi komentar wajib sintaks JS.
*/
(function () {
    'use strict';
    document.documentElement.classList.add('js-on');

    // ---- Modal perpanjangan ---------------------------------------------
    var modal = document.getElementById('form-perpanjangan');
    if (!modal) { return; }

    var pemicu = null;
    var terakhirFokus = null;
    var terbuka = false;

    function fokusable() {
        return Array.prototype.filter.call(
            modal.querySelectorAll('button, [href], input, textarea, select'),
            function (el) { return !el.disabled && el.offsetParent !== null; }
        );
    }

    function buka(event) {
        if (event) { event.preventDefault(); }
        terakhirFokus = document.activeElement;
        pemicu = document.activeElement;
        modal.hidden = false;
        terbuka = true;
        document.body.style.overflow = 'hidden';

        var daftar = fokusable();
        var pertama = modal.querySelector('input[name="durasi"]:checked')
            || (daftar.length ? daftar[0] : modal.querySelector('.mitra-modal-box'));
        if (pertama) { pertama.focus(); }
    }

    function tutup() {
        if (!terbuka) { return; }
        modal.hidden = true;
        terbuka = false;
        document.body.style.overflow = '';
        if (terakhirFokus && typeof terakhirFokus.focus === 'function') {
            terakhirFokus.focus();
        } else if (pemicu) {
            pemicu.focus();
        }
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-buka-perpanjangan]'), function (el) {
        el.addEventListener('click', buka);
    });

    Array.prototype.forEach.call(modal.querySelectorAll('[data-tutup-perpanjangan]'), function (el) {
        el.addEventListener('click', tutup);
    });

    // Klik latar gelap menutup dialog.
    modal.addEventListener('mousedown', function (event) {
        if (event.target === modal) { tutup(); }
    });

    // Esc menutup, Tab dikurung di dalam dialog.
    document.addEventListener('keydown', function (event) {
        if (!terbuka) { return; }

        if (event.key === 'Escape') {
            event.preventDefault();
            tutup();
            return;
        }

        if (event.key !== 'Tab') { return; }

        var daftar = fokusable();
        if (!daftar.length) { return; }

        var pertama = daftar[0];
        var terakhir = daftar[daftar.length - 1];

        if (event.shiftKey && document.activeElement === pertama) {
            event.preventDefault();
            terakhir.focus();
        } else if (!event.shiftKey && document.activeElement === terakhir) {
            event.preventDefault();
            pertama.focus();
        }
    });

    // Cegah kirim ganda.
    var form = modal.querySelector('[data-form-perpanjangan]');
    if (form) {
        form.addEventListener('submit', function () {
            var tombol = form.querySelector('[data-submit]');
            if (!tombol) { return; }
            tombol.setAttribute('aria-disabled', 'true');
            tombol.disabled = true;
            tombol.textContent = 'Mengirim...';
        });
    }
})();
