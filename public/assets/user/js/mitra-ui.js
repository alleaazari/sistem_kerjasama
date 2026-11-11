/*
    Area mitra: laci navigasi (<1024px), pratinjau foto profil, modal biodata.
    Tanpa data di atribut inline; teks diambil dari DOM (textContent), bukan HTML.
    Tanpa JS: halaman tetap terbaca penuh (laci hanya tersembunyi di layar kecil).
*/
(function () {
    'use strict';
    document.documentElement.classList.add('js-on');

    /* ---- Utilitas fokus ------------------------------------------------- */
    function fokusable(wadah) {
        return Array.prototype.filter.call(
            wadah.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])'),
            function (el) { return el.offsetParent !== null || el === document.activeElement; }
        );
    }

    function kurungFokus(event, wadah, tutup) {
        if (event.key === 'Escape') {
            event.preventDefault();
            tutup();
            return;
        }
        if (event.key !== 'Tab') { return; }

        var daftar = fokusable(wadah);
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
    }

    /* ---- 1. Laci navigasi ---------------------------------------------- */
    (function laci() {
        var panel = document.getElementById('laci-mitra');
        var tombol = document.querySelector('[data-buka-laci]');
        var scrim = document.querySelector('[data-tutup-laci]');
        if (!panel || !tombol) { return; }

        var fokusTerakhir = null;

        function terbuka() {
            return panel.getAttribute('data-terbuka') === 'true';
        }

        function buka() {
            fokusTerakhir = document.activeElement;
            panel.setAttribute('data-terbuka', 'true');
            document.body.setAttribute('data-laci-terbuka', 'true');
            if (scrim) { scrim.hidden = false; }
            tombol.setAttribute('aria-expanded', 'true');

            var daftar = fokusable(panel);
            if (daftar.length) { daftar[0].focus(); }
        }

        function tutup() {
            if (!terbuka()) { return; }
            panel.removeAttribute('data-terbuka');
            document.body.removeAttribute('data-laci-terbuka');
            if (scrim) { scrim.hidden = true; }
            tombol.setAttribute('aria-expanded', 'false');
            tombol.focus();
        }

        tombol.addEventListener('click', function () {
            if (terbuka()) { tutup(); } else { buka(); }
        });

        if (scrim) { scrim.addEventListener('click', tutup); }

        // Klik di luar panel menutup laci.
        document.addEventListener('mousedown', function (event) {
            if (!terbuka()) { return; }
            if (!panel.contains(event.target) && !tombol.contains(event.target)) { tutup(); }
        });

        document.addEventListener('keydown', function (event) {
            if (!terbuka()) { return; }
            kurungFokus(event, panel, tutup);
        });

        // Layar dilebarkan: kembalikan keadaan normal.
        var lebar = window.matchMedia('(min-width: 1024px)');
        if (lebar.addEventListener) {
            lebar.addEventListener('change', function (e) {
                if (e.matches) {
                    panel.removeAttribute('data-terbuka');
                    document.body.removeAttribute('data-laci-terbuka');
                    if (scrim) { scrim.hidden = true; }
                    tombol.setAttribute('aria-expanded', 'false');
                }
            });
        }

        // Fokus kembali ke pemicu bila laci ditutup lewat Esc/klik luar.
        panel.addEventListener('focusout', function () {
            if (terbuka() && !panel.contains(document.activeElement) && document.activeElement !== document.body) {
                if (fokusTerakhir && typeof fokusTerakhir.focus === 'function') { fokusTerakhir.focus(); }
            }
        });
    })();

    /* ---- 2. Pratinjau foto profil -------------------------------------- */
    (function pratinjauFoto() {
        var input = document.getElementById('foto');
        var gambar = document.getElementById('foto-pratinjau');
        if (!input || !gambar || !window.FileReader) { return; }

        input.addEventListener('change', function () {
            var berkas = input.files && input.files[0];
            if (!berkas || berkas.type.indexOf('image/') !== 0) {
                gambar.removeAttribute('src');
                gambar.setAttribute('data-tampil', 'false');
                return;
            }
            var pembaca = new FileReader();
            pembaca.onload = function () {
                gambar.src = String(pembaca.result);
                gambar.setAttribute('data-tampil', 'true');
            };
            pembaca.readAsDataURL(berkas);
        });
    })();

    /* ---- 3. Modal biodata ---------------------------------------------- */
    (function biodata() {
        var modal = document.getElementById('modal-biodata');
        if (!modal) { return; }

        var pemicu = document.querySelector('[data-buka-biodata]');
        var terakhirFokus = null;
        var terbukaSekarang = false;

        function buka() {
            terakhirFokus = document.activeElement;
            modal.hidden = false;
            terbukaSekarang = true;
            document.body.style.overflow = 'hidden';

            var daftar = fokusable(modal);
            if (daftar.length) { daftar[0].focus(); }
        }

        function tutup() {
            if (!terbukaSekarang) { return; }
            modal.hidden = true;
            terbukaSekarang = false;
            document.body.style.overflow = '';
            if (terakhirFokus && typeof terakhirFokus.focus === 'function') {
                terakhirFokus.focus();
            } else if (pemicu) {
                pemicu.focus();
            }
        }

        if (pemicu) {
            pemicu.addEventListener('click', function (event) {
                event.preventDefault();
                buka();
            });
        }

        Array.prototype.forEach.call(modal.querySelectorAll('[data-tutup-biodata]'), function (el) {
            el.addEventListener('click', tutup);
        });

        modal.addEventListener('mousedown', function (event) {
            if (event.target === modal) { tutup(); }
        });

        document.addEventListener('keydown', function (event) {
            if (!terbukaSekarang) { return; }
            kurungFokus(event, modal, tutup);
        });
    })();
})();
