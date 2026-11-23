// Toggle pratinjau dokumen pada halaman "Buat MoU".
// Tanpa innerHTML: hanya menyembunyikan/menampilkan elemen yang sudah dirender server.
(function () {
    'use strict';

    var toggle = document.querySelector('[data-toggle-pratinjau]');
    var pratinjau = document.getElementById('pratinjau-mou');

    if (!toggle || !pratinjau) {
        return;
    }

    toggle.addEventListener('click', function () {
        var tersembunyi = pratinjau.hasAttribute('hidden');

        if (tersembunyi) {
            pratinjau.removeAttribute('hidden');
            toggle.textContent = 'Sembunyikan pratinjau';
            toggle.setAttribute('aria-expanded', 'true');
        } else {
            pratinjau.setAttribute('hidden', '');
            toggle.textContent = 'Tampilkan pratinjau';
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
})();
