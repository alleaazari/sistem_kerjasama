/* ==========================================================================
   Landing widgets — port vanilla (tanpa dependency) dari komponen React Bits:
   1. TextType      -> judul hero "Sinergi Digital Nusantara"
   2. LineSidebar   -> daftar Visi & Misi (hover proximity + klik buka detail)
   3. DepthCarousel -> galeri "Jejak Kegiatan" (drag/wheel/kunci panah/autoplay)
   Data dibaca dari window.LP_WIDGETS (di-embed dari Blade).
   ========================================================================== */
(function () {
    'use strict';

    var W = window.LP_WIDGETS || {};
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function clamp(v, min, max) { return Math.min(Math.max(v, min), max); }
    /* Ekuivalen gsap "power3.out" = easeOutCubic */
    function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

    /* ================= 1. TEXT TYPE (judul hero) ================= */
    (function textType() {
        var box = document.getElementById('heroType');
        var el = document.getElementById('heroTypeText');
        if (!box || !el) return;

        var phrases = (W.heroText && W.heroText.length) ? W.heroText : ['Sinergi Digital Nusantara'];
        var cursor = box.querySelector('.tt-cursor');

        if (reduceMotion || phrases.length === 1) {
            /* ponytail: 1 frase / reduced-motion -> teks statis tanpa mesin ketik */
            el.textContent = phrases[0];
            if (cursor) cursor.style.display = 'none';
            return;
        }

        var idx = 0, chars = 0, deleting = false, busy = false;

        function tick() {
            if (busy) return;
            var phrase = phrases[idx];
            if (!deleting) {
                chars++;
                el.textContent = phrase.slice(0, chars);
                if (chars >= phrase.length) {
                    busy = true;
                    window.setTimeout(function () {
                        busy = false;
                        deleting = true;
                        tick();
                    }, 2200);
                } else {
                    window.setTimeout(tick, 45 + Math.random() * 60);
                }
            } else {
                chars--;
                el.textContent = phrase.slice(0, chars);
                if (chars <= 0) {
                    deleting = false;
                    idx = (idx + 1) % phrases.length;
                    window.setTimeout(tick, 480);
                } else {
                    window.setTimeout(tick, 26);
                }
            }
        }

        var started = false;
        function start() { if (!started) { started = true; tick(); } }

        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (e.isIntersecting) { start(); io.disconnect(); }
                });
            }, { threshold: 0.3 });
            io.observe(box);
        } else {
            start();
        }
    })();

    /* ================= 2. LINE SIDEBAR (Visi & Misi) ================= */
    (function lineSidebar() {
        var list = document.getElementById('vmList');
        var detail = document.getElementById('vmDetail');
        if (!list || !detail) return;

        var data = W.vmItems || [];
        if (!data.length) return;

        var RADIUS = 100, SMOOTHING = 100;
        var active = 0;
        var itemEls = [], targets = [0, 0, 0, 0, 0], currents = [0, 0, 0, 0, 0];
        var rafId = null, last = 0;

        data.forEach(function (item, i) {
            var li = document.createElement('li');
            li.className = 'line-sidebar__item';
            li.tabIndex = 0;

            var marker = document.createElement('span');
            marker.className = 'line-sidebar__marker';
            marker.setAttribute('aria-hidden', 'true');

            var label = document.createElement('span');
            label.className = 'line-sidebar__label';

            var index = document.createElement('span');
            index.className = 'line-sidebar__index';
            index.textContent = String(i + 1).padStart(2, '0');

            var text = document.createElement('span');
            text.className = 'line-sidebar__text';
            text.textContent = item.label;

            label.appendChild(index);
            label.appendChild(text);
            li.appendChild(marker);
            li.appendChild(label);

            function select() {
                active = i;
                itemEls.forEach(function (el2, j) {
                    el2.classList.toggle('is-active', j === i);
                    if (j === i) el2.setAttribute('aria-current', 'true');
                    else el2.removeAttribute('aria-current');
                    if (reduceMotion) el2.style.setProperty('--effect', j === i ? '1' : '0');
                });
                detail.textContent = item.text;
                detail.classList.remove('vm-detail-swap');
                void detail.offsetWidth; /* retrigger animasi */
                detail.classList.add('vm-detail-swap');
            }

            li.addEventListener('click', select);
            li.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); select(); }
            });

            list.appendChild(li);
            itemEls.push(li);
            targets.push(0);
            currents.push(0);

            if (i === 0) {
                li.classList.add('is-active');
                li.setAttribute('aria-current', 'true');
                if (reduceMotion) li.style.setProperty('--effect', '1');
            }
        });

        detail.textContent = data[0].text;
        detail.classList.add('vm-detail-swap');

        var smooth = function (p) { return p * p * (3 - 2 * p); };

        function frame(now) {
            var dt = Math.min((now - last) / 1000, 0.05);
            last = now;
            var tau = Math.max(SMOOTHING, 1) / 1000;
            var k = 1 - Math.exp(-dt / tau);
            var moving = false;

            for (var i = 0; i < itemEls.length; i++) {
                var target = Math.max(targets[i] || 0, active === i ? 1 : 0);
                var cur = currents[i] || 0;
                var next = cur + (target - cur) * k;
                if (Math.abs(target - next) < 0.0015) next = target;
                else moving = true;
                currents[i] = next;
                itemEls[i].style.setProperty('--effect', next.toFixed(4));
            }

            rafId = moving ? requestAnimationFrame(frame) : null;
        }

        function startLoop() {
            if (rafId != null) cancelAnimationFrame(rafId);
            last = performance.now();
            rafId = requestAnimationFrame(frame);
        }

        function onMove(e) {
            var rect = list.getBoundingClientRect();
            var y = e.clientY - rect.top;
            for (var i = 0; i < itemEls.length; i++) {
                var el = itemEls[i];
                var center = el.offsetTop + el.offsetHeight / 2;
                var p = Math.max(0, 1 - Math.abs(y - center) / RADIUS);
                targets[i] = smooth(p);
                if (reduceMotion) el.style.setProperty('--effect', targets[i].toFixed(4));
            }
            if (!reduceMotion) startLoop();
        }

        function onLeave() {
            for (var i = 0; i < targets.length; i++) targets[i] = 0;
            if (reduceMotion) {
                itemEls.forEach(function (el, i) { el.style.setProperty('--effect', active === i ? '1' : '0'); });
            } else {
                startLoop();
            }
        }

        list.addEventListener('pointermove', onMove);
        list.addEventListener('pointerleave', onLeave);

        window.addEventListener('beforeunload', function () {
            if (rafId != null) cancelAnimationFrame(rafId);
        });
    })();

    /* ================= 3. DEPTH CAROUSEL (Jejak Kegiatan) ================= */
    (function depthCarousel() {
        var root = document.getElementById('depthCarousel');
        if (!root) return;

        var caption = document.getElementById('depthCaption');

        var items = (W.gallery || []).map(function (it) {
            return typeof it === 'string' ? { image: it, alt: '' } : it;
        });
        var n = items.length;
        if (!n) return;

        var cfg = {
            cardWidth: 300,
            cardHeight: 380,
            depth: 220,
            spread: 90,
            tilt: 22,
            tiltDirection: 'right',
            visibleCards: 4,
            falloff: 0.2,
            blur: 6,
            duration: 700,
            loop: true,
            autoplay: true,
            autoplayDelay: 3200
        };

        /* --- Bangun DOM kartu --- */
        var stage = document.createElement('div');
        stage.className = 'depth-carousel__stage';
        root.appendChild(stage);

        var cards = [], overlays = [];
        items.forEach(function (it, i) {
            var c = document.createElement('div');
            c.className = 'depth-carousel__card';
            c.style.width = cfg.cardWidth + 'px';
            c.style.height = cfg.cardHeight + 'px';
            c.style.borderRadius = '14px';
            c.setAttribute('aria-roledescription', 'slide');
            c.setAttribute('aria-label', (i + 1) + ' dari ' + n);

            var img = document.createElement('img');
            img.className = 'depth-carousel__img';
            img.src = it.image;
            img.alt = it.alt || '';
            img.loading = 'lazy';
            img.draggable = false;

            var ov = document.createElement('span');
            ov.className = 'depth-carousel__tint';
            ov.style.background = '#0B1D33';

            c.appendChild(img);
            c.appendChild(ov);
            c.addEventListener('click', function () { onCardClick(i); });
            stage.appendChild(c);
            cards.push(c);
            overlays.push(ov);
        });

        /* --- Navigasi: panah + dot --- */
        var activeIdx = 0;
        var dotsWrap = null, dots = [];

        if (n > 1) {
            var prev = document.createElement('button');
            prev.type = 'button';
            prev.className = 'depth-carousel__arrow depth-carousel__arrow--prev';
            prev.setAttribute('aria-label', 'Foto sebelumnya');
            prev.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

            var next = document.createElement('button');
            next.type = 'button';
            next.className = 'depth-carousel__arrow depth-carousel__arrow--next';
            next.setAttribute('aria-label', 'Foto berikutnya');
            next.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

            dotsWrap = document.createElement('div');
            dotsWrap.className = 'depth-carousel__dots';
            dotsWrap.setAttribute('role', 'tablist');
            dotsWrap.setAttribute('aria-label', 'Daftar foto');
            items.forEach(function (_, i) {
                var d = document.createElement('button');
                d.type = 'button';
                d.setAttribute('role', 'tab');
                d.setAttribute('aria-label', 'Foto ' + (i + 1));
                d.className = 'depth-carousel__dot' + (i === 0 ? ' is-active' : '');
                d.addEventListener('click', function () { setFocus(i, true); });
                dotsWrap.appendChild(d);
                dots.push(d);
            });

            prev.addEventListener('click', function () { navigateBy(-1); });
            next.addEventListener('click', function () { navigateBy(1); });
            root.appendChild(prev);
            root.appendChild(next);
            root.appendChild(dotsWrap);
        }

        /* --- State + layout (rumus sama persis dengan versi React) --- */
        var pos = 0;
        var scale = 1;
        var tweenRaf = null;
        var drag = null;
        var wheelTimer = null;
        var autoTimer = null;

        function norm(v) {
            if (cfg.loop && n > 1) return ((v % n) + n) % n;
            return clamp(v, 0, n - 1);
        }

        function layout(p) {
            var dir = cfg.tiltDirection === 'left' ? -1 : 1;
            for (var i = 0; i < n; i++) {
                var el = cards[i];
                var d = i - p;
                if (cfg.loop && n > 1) {
                    d = ((d % n) + n) % n;
                    if (d > n / 2) d -= n;
                }
                var back = Math.max(0, d);
                var az = Math.abs(d);
                var shown = az <= cfg.visibleCards + 0.5;

                var tz = -cfg.depth * d;
                var tx = dir * cfg.spread * d;
                var ry = dir * cfg.tilt * clamp(d, 0, 1);

                var opacity = d < 0 ? Math.max(0, 1 + d) : 1;
                if (!shown) opacity = 0;

                var brightness = Math.max(0.15, 1 - back * cfg.falloff);
                var blurPx = cfg.blur > 0 ? Math.min(cfg.blur, (back / Math.max(1, cfg.visibleCards)) * cfg.blur) : 0;
                var zi = Math.round(2000 - d * 20);

                el.style.transform =
                    'translate(-50%, -50%) scale(' + scale + ') translateX(' + tx.toFixed(2) + 'px) translateZ(' + tz.toFixed(2) + 'px) rotateY(' + ry.toFixed(3) + 'deg)';
                el.style.opacity = opacity.toFixed(3);
                el.style.filter = 'brightness(' + brightness.toFixed(3) + ') blur(' + blurPx.toFixed(2) + 'px)';
                el.style.zIndex = String(zi);
                el.style.pointerEvents = shown && opacity > 0.05 ? 'auto' : 'none';
                el.setAttribute('aria-hidden', az > 0 ? 'true' : 'false');

                var ov = overlays[i];
                ov.style.opacity = clamp(back * cfg.falloff * 1.25, 0, 0.86).toFixed(3);
            }
        }

        function setActive(idx) {
            activeIdx = norm(idx);
            dots.forEach(function (dot, i) {
                dot.classList.toggle('is-active', i === activeIdx);
                dot.setAttribute('aria-selected', i === activeIdx ? 'true' : 'false');
            });
            if (caption) {
                caption.textContent = items[activeIdx] ? items[activeIdx].alt : '';
            }
        }

        function tweenTo(target, animate) {
            if (tweenRaf) cancelAnimationFrame(tweenRaf);
            var from = pos;
            var t0 = performance.now();
            var dur = animate && !reduceMotion ? cfg.duration : 0;

            function step(now) {
                var t = dur ? clamp((now - t0) / dur, 0, 1) : 1;
                pos = from + (target - from) * easeOutCubic(t);
                layout(pos);
                if (t < 1) {
                    tweenRaf = requestAnimationFrame(step);
                } else {
                    tweenRaf = null;
                    pos = norm(pos);
                    layout(pos);
                }
            }
            tweenRaf = requestAnimationFrame(step);
        }

        function setFocus(rawIndex, animate) {
            var idx = norm(rawIndex);
            var delta = idx - norm(pos);
            if (cfg.loop && n > 1) {
                delta = ((delta % n) + n) % n;
                if (delta > n / 2) delta -= n;
            }
            setActive(idx);
            tweenTo(pos + delta, animate !== false);
        }

        function navigateBy(step) { setFocus(activeIdx + step, true); }

        function onCardClick(index) {
            if (drag && drag.moved) return;
            if (index === activeIdx && typeof window.openLightbox === 'function') {
                /* Foto fokus bisa dibuka di lightbox */
                window.openLightbox(items[index].image);
            } else {
                setFocus(index, true);
            }
        }

        /* --- Skala responsif (ResizeObserver, sama dengan versi React) --- */
        function fit() {
            var w = root.clientWidth;
            var needed = cfg.cardWidth + Math.abs(cfg.spread) * 2 + 120;
            scale = clamp(w / needed, 0.4, 1);
            layout(pos);
        }

        if ('ResizeObserver' in window) {
            var ro = new ResizeObserver(function () { fit(); });
            ro.observe(root);
        } else {
            window.addEventListener('resize', fit);
        }

        /* --- Wheel (cuma aktif jika > 1 foto) --- */
        if (n > 1) {
            root.addEventListener('wheel', function (e) {
                if (n < 2) return;
                e.preventDefault();
                if (tweenRaf) cancelAnimationFrame(tweenRaf);
                var raw = Math.abs(e.deltaX) > Math.abs(e.deltaY) ? e.deltaX : e.deltaY;
                var delta = e.deltaMode === 1 ? raw * 24 : raw;
                var step = clamp(delta / (cfg.cardWidth * 0.9), -0.6, 0.6);
                pos += step;
                layout(pos);
                if (wheelTimer) clearTimeout(wheelTimer);
                wheelTimer = window.setTimeout(function () {
                    setFocus(Math.round(pos), true);
                    wheelTimer = null;
                }, 130);
            }, { passive: false });
        }

        /* --- Drag pointer --- */
        root.addEventListener('pointerdown', function (e) {
            if (n < 2) return;
            if (tweenRaf) cancelAnimationFrame(tweenRaf);
            drag = {
                x: e.clientX,
                startPos: pos,
                lastX: e.clientX,
                lastT: performance.now(),
                v: 0,
                moved: false,
                id: e.pointerId
            };
        });

        root.addEventListener('pointermove', function (e) {
            if (!drag) return;
            var stepPx = Math.max(cfg.cardWidth * 0.55 * scale, 40);
            var dx = e.clientX - drag.x;
            if (!drag.moved && Math.abs(dx) > 4) {
                drag.moved = true;
                root.setPointerCapture(drag.id);
            }
            if (!drag.moved) return;
            var now = performance.now();
            var dt = Math.max(now - drag.lastT, 1);
            drag.v = (e.clientX - drag.lastX) / dt;
            drag.lastX = e.clientX;
            drag.lastT = now;
            pos = drag.startPos - dx / stepPx;
            layout(pos);
        });

        function endDrag() {
            if (!drag) return;
            var d = drag;
            drag = null;
            if (!d.moved) return;
            var stepPx = Math.max(cfg.cardWidth * 0.55 * scale, 40);
            var projected = pos - (d.v * 180) / stepPx;
            setFocus(Math.round(projected), true);
        }
        root.addEventListener('pointerup', endDrag);
        root.addEventListener('pointercancel', endDrag);

        /* --- Keyboard --- */
        root.setAttribute('tabindex', '0');
        root.setAttribute('role', 'group');
        root.setAttribute('aria-roledescription', 'carousel');
        root.setAttribute('aria-label', 'Jejak kegiatan');
        root.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { e.preventDefault(); navigateBy(-1); }
            else if (e.key === 'ArrowRight') { e.preventDefault(); navigateBy(1); }
        });

        /* --- Autoplay (jeda saat hover/fokus) --- */
        if (cfg.autoplay && n > 1 && !reduceMotion) {
            var hovered = false, focused = false;
            root.addEventListener('mouseenter', function () { hovered = true; });
            root.addEventListener('mouseleave', function () { hovered = false; });
            root.addEventListener('focusin', function () { focused = true; });
            root.addEventListener('focusout', function () { focused = false; });
            autoTimer = window.setInterval(function () {
                if (!hovered && !focused) navigateBy(1);
            }, Math.max(cfg.autoplayDelay, 600));
            window.addEventListener('beforeunload', function () {
                if (autoTimer) clearInterval(autoTimer);
                if (tweenRaf) cancelAnimationFrame(tweenRaf);
                if (wheelTimer) clearTimeout(wheelTimer);
            });
        }

        /* --- Init --- */
        fit();
        setActive(0);
    })();
})();
