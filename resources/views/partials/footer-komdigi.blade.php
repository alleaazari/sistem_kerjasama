{{-- Footer resmi KOMDIGI — dipakai di semua halaman publik & dashboard --}}
<footer class="komdigi-footer">
    <div class="komdigi-footer-inner">
        <div class="kgf-top">
            <div class="kgf-brand">
                <div class="kgf-logo-row">
                    <img src="{{ asset('assets/user/images/logo-komdigi.jpg') }}" alt="Logo KOMDIGI" class="kgf-logo">
                    <span class="kgf-brand-name">KOMDIGI</span>
                </div>
                <p class="kgf-tagline">Kementerian Komunikasi dan Digital<br>Republik Indonesia</p>
            </div>

            <nav class="kgf-col" aria-label="Unit Eselon I">
                <ul>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">Inspektorat Jenderal</a></li>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">Ditjen Infrastruktur Digital</a></li>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">Ditjen Ekosistem Digital</a></li>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">Ditjen Teknologi Pemerintah Digital</a></li>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">Ditjen Pengawasan Ruang Digital</a></li>
                </ul>
            </nav>

            <nav class="kgf-col" aria-label="Badan dan Unit Pendukung">
                <ul>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">Ditjen Komunikasi Publik dan Media</a></li>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">BPSDM Komdigi</a></li>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">BAKTI Komdigi</a></li>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">Museum Penerangan</a></li>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">Monumen Pers Nasional</a></li>
                </ul>
            </nav>

            <nav class="kgf-col" aria-label="Bantuan">
                <ul>
                    <li><a href="https://komdigi.go.id" target="_blank" rel="noopener noreferrer">FAQ</a></li>
                    <li><a href="{{ route('landing') }}">Tautan</a></li>
                    <li><a href="mailto:pelayanan@mail.komdigi.go.id">Hubungi Kami</a></li>
                </ul>
            </nav>
        </div>

        <div class="kgf-divider"></div>

        <div class="kgf-bottom">
            <a class="kgf-contact" href="https://maps.google.com/?q=Jl.+Medan+Merdeka+Barat+No.+9,+Jakarta+Pusat+10110" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="1"/><path d="M9 22v-4h6v4"/><line x1="8" y1="6" x2="8" y2="6"/><line x1="12" y1="6" x2="12" y2="6"/><line x1="16" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="8" y2="10"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="16" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="8" y2="14"/><line x1="12" y1="14" x2="12" y2="14"/><line x1="16" y1="14" x2="16" y2="14"/></svg>
                <span>Jl. Medan Merdeka Barat No. 9,<br>Jakarta Pusat 10110</span>
            </a>
            <a class="kgf-contact" href="tel:+62213504024">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <span>(021) 3504024</span>
            </a>
            <a class="kgf-contact" href="mailto:pelayanan@mail.komdigi.go.id">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                <span>pelayanan@mail.komdigi.go.id</span>
            </a>
            <div class="kgf-socials">
                <a href="https://www.facebook.com/kemkominfo" target="_blank" rel="noopener noreferrer" title="Facebook Komdigi" aria-label="Facebook Komdigi"><svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                <a href="https://www.instagram.com/kemkominfo/" target="_blank" rel="noopener noreferrer" title="Instagram Komdigi" aria-label="Instagram Komdigi"><svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg></a>
                <a href="https://www.youtube.com/@kemkominfo" target="_blank" rel="noopener noreferrer" title="YouTube Komdigi" aria-label="YouTube Komdigi"><svg viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a>
                <a href="https://x.com/kemkominfo" target="_blank" rel="noopener noreferrer" title="X Komdigi" aria-label="X Komdigi"><svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a>
                <a href="https://www.tiktok.com/@kemkominfo" target="_blank" rel="noopener noreferrer" title="TikTok Komdigi" aria-label="TikTok Komdigi"><svg viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64c.29 0 .56.04.82.1v-3.6a6.47 6.47 0 0 0-1.07-.09A6.36 6.36 0 0 0 2.85 15.5a6.36 6.36 0 0 0 10.86 4.49A6.32 6.32 0 0 0 15.5 15.5V9.07a8.28 8.28 0 0 0 4.84 1.56V7.18a4.83 4.83 0 0 1-.75-.49z"/></svg></a>
            </div>
        </div>

        <div class="kgf-copy">Hak Cipta &copy; {{ date('Y') }} Kementerian Komunikasi dan Digital RI. All rights reserved.</div>
    </div>
</footer>
<button type="button" id="kgfToTop" class="kgf-to-top" aria-label="Kembali ke atas" title="Kembali ke atas">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
</button>
<script>
(function () {
    var btn = document.getElementById('kgfToTop');
    if (!btn) return;
    function onScroll() {
        if (window.scrollY > 400) { btn.classList.add('show'); }
        else { btn.classList.remove('show'); }
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
})();
</script>
