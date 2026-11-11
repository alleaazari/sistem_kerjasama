/*
    Area mitra: satu gerak masuk — fade + geser vertikal.
    Satu IntersectionObserver, satu listener scroll pasif + requestAnimationFrame.
    Tanpa JS / prefers-reduced-motion: semua elemen tampil langsung (CSS tidak
    menyembunyikan apa pun kecuali bila html.js aktif).
*/
(function () {
    'use strict';

    var akar = document.documentElement;
    akar.classList.add('js-on');

    var kurangiGerak = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (kurangiGerak || !('IntersectionObserver' in window)) { return; }

    var elemen = document.querySelectorAll('[data-anim="naik"]');
    if (!elemen.length) { return; }

    akar.classList.add('mitra-gerak');

    // --urut: jeda 70ms per elemen, maksimal 6 per kelompok (bagian terdekat).
    var hitungKelompok = {};

    Array.prototype.forEach.call(elemen, function (el) {
        var wadah = el.closest('[data-kelompok]');
        var kunci = wadah ? (wadah.getAttribute('data-kelompok') || '') : '';
        var nomor = hitungKelompok[kunci] || 0;
        hitungKelompok[kunci] = nomor + 1;
        el.style.setProperty('--urut', String(Math.min(nomor, 5)));
    });

    // Arah gulir: +1 turun, -1 naik. Dibaca juga saat elemen masuk layar,
    // karena callback IntersectionObserver bisa berjalan sebelum rAF.
    var arah = 1;
    var terakhirY = window.pageYOffset || 0;
    var menunggu = false;

    function ukurArah() {
        var y = window.pageYOffset || 0;
        if (y !== terakhirY) {
            arah = y > terakhirY ? 1 : -1;
            terakhirY = y;
        }
        menunggu = false;
        return arah;
    }

    window.addEventListener('scroll', function () {
        if (menunggu) { return; }
        menunggu = true;
        window.requestAnimationFrame(ukurArah);
    }, { passive: true });

    function terapkanArah(el) {
        // Turun: masuk dari bawah (+18px). Naik: masuk dari atas (-14px).
        el.style.setProperty('--geser', ukurArah() > 0 ? '18px' : '-14px');
    }

    var pengamat = new IntersectionObserver(function (masuk) {
        masuk.forEach(function (catatan) {
            var el = catatan.target;
            var rasio = catatan.intersectionRatio;

            if (catatan.isIntersecting && rasio >= 0.15) {
                if (!el.hasAttribute('data-masuk')) {
                    terapkanArah(el);
                    el.setAttribute('data-masuk', 'true');
                }
            } else if (rasio === 0) {
                // Lepas hanya setelah benar-benar keluar layar.
                el.removeAttribute('data-masuk');
            }
        });
    }, { threshold: [0, 0.15] });

    Array.prototype.forEach.call(elemen, function (el) { pengamat.observe(el); });
})();
