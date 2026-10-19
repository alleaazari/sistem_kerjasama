<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Portal Kerja Sama KEMKOMDIGI</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/user/css/style.css') }}">
</head>
<body>
@include('partials.header')
<div class="wrap wrap-main{{ request()->routeIs('landing') ? '' : ' wrap-main-inner' }}">
    @yield('content')
</div>

@include('partials.footer-komdigi')

<!-- Lightbox Modal for Image Zooming -->
<div id="lightbox" class="lightbox" onclick="closeLightbox()">
    <div class="lightbox-content" onclick="event.stopPropagation()">
        <button class="lightbox-close" onclick="closeLightbox()" aria-label="Tutup">&times;</button>
        <img id="lightbox-img" src="" alt="Pratinjau Foto" style="width:100%;height:auto;max-height:85vh;object-fit:contain">
    </div>
</div>

<!-- Leadership Profile Detail Modal -->
<div id="profile-modal" class="profile-modal" onclick="closeProfileModal()">
    <div class="profile-modal-box" onclick="event.stopPropagation()">
        <button class="profile-modal-close" onclick="closeProfileModal()" aria-label="Tutup">&times;</button>
        <div class="profile-modal-img">
            <img id="pm-img" src="" alt="Foto Petinggi">
        </div>
        <div class="profile-modal-body">
            <span id="pm-badge" style="background:#0b3a6b;color:#ffffff;font-size:11px;font-weight:600;padding:3px 10px;border-radius:4px;display:inline-block;margin-bottom:8px;"></span>
            <h3 id="pm-nama" style="font-size:18px;color:#0b3a6b;margin-bottom:2px;"></h3>
            <div id="pm-jabatan" style="font-size:13px;font-weight:600;color:#1d4ed8;margin-bottom:14px;"></div>
            <div id="pm-desc" style="font-size:13px;color:#475569;line-height:1.6;margin-bottom:12px;"></div>
            <div id="pm-detail" style="font-size:12px;color:#64748b;background:#f8fafc;padding:12px;border:1px solid #e2e8f0;border-radius:4px;line-height:1.6;"></div>
        </div>
    </div>
</div>

<script>
// Lightbox functions
function openLightbox(src) {
    if (!src) return;
    document.getElementById('lightbox-img').src = src;
    document.getElementById('lightbox').classList.add('active');
}
function closeLightbox() {
    document.getElementById('lightbox').classList.remove('active');
}

// Leadership Profile Modal functions
function openProfileModal(nama, jabatan, badge, foto, desc, detail) {
    document.getElementById('pm-nama').textContent = nama;
    document.getElementById('pm-jabatan').textContent = jabatan;
    document.getElementById('pm-badge').textContent = badge;
    document.getElementById('pm-img').src = foto;
    document.getElementById('pm-desc').textContent = desc;
    document.getElementById('pm-detail').textContent = detail;
    document.getElementById('profile-modal').classList.add('active');
}
function closeProfileModal() {
    document.getElementById('profile-modal').classList.remove('active');
}

// Scroll Reveal Observer
document.addEventListener('DOMContentLoaded', function () {
    var reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });
        reveals.forEach(function (el) { observer.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add('active'); });
    }
});
</script>
</body>
</html>
