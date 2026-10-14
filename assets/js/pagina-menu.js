(function () {
    function initializeMenuPage() {
        var page = document.querySelector('[data-menu-page-url]');
        if (!page) return;
        // Institutional tokens only; all layout rules remain in the shared CSS.
        (page.dataset.menuTheme || '').split(';').forEach(function (property) {
            var colon = property.indexOf(':');
            var name = property.slice(0, colon).trim();
            if (colon > 0 && /^--sp-db-(?:primary|secondary|tertiary|quaternary|accent-background|accent-foreground)$/.test(name)) {
                page.style.setProperty(name, property.slice(colon + 1).trim());
            }
        });
        var heroImage = document.querySelector('[data-menu-hero-image]');
        if (heroImage) {
            function restoreHeroImage() {
                var fallback = heroImage.dataset.fallback;
                if (fallback && heroImage.getAttribute('src') !== fallback) heroImage.src = fallback;
                else heroImage.hidden = true;
            }
            heroImage.addEventListener('error', restoreHeroImage);
            if (heroImage.complete && !heroImage.naturalWidth) restoreHeroImage();
        }
        var heroVideo = document.querySelector('[data-menu-hero-video]');
        var videoToggle = document.querySelector('[data-menu-video-toggle]');
        if (heroVideo && videoToggle) {
            function updateVideoToggle() {
                var action = heroVideo.paused ? 'Reproducir' : 'Pausar';
                videoToggle.textContent = action + ' video';
                videoToggle.setAttribute('aria-label', action + ' video de cabecera');
            }
            function restoreVideoFallback() { heroVideo.hidden = true; videoToggle.hidden = true; }
            heroVideo.addEventListener('error', restoreVideoFallback);
            if (heroVideo.error) restoreVideoFallback();
            heroVideo.addEventListener('play', updateVideoToggle);
            heroVideo.addEventListener('pause', updateVideoToggle);
            videoToggle.addEventListener('click', function () {
                if (heroVideo.paused) heroVideo.play().catch(restoreVideoFallback);
                else heroVideo.pause();
            });
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                heroVideo.autoplay = false; heroVideo.pause();
            }
            updateVideoToggle();
        }
        if (window.bootstrap && window.bootstrap.Carousel) {
            document.querySelectorAll('[data-menu-carousel]').forEach(function (gallery) {
                window.bootstrap.Carousel.getOrCreateInstance(gallery, { interval: false, ride: false, touch: true });
                gallery.addEventListener('slid.bs.carousel', function (event) {
                    gallery.querySelectorAll('.carousel-item a').forEach(function (link) {
                        link.tabIndex = link.closest('.carousel-item').classList.contains('active') ? 0 : -1;
                    });
                    gallery.querySelector('[data-menu-gallery-status]').textContent =
                        'Imagen ' + (event.to + 1) + ' de ' + gallery.querySelectorAll('.carousel-item').length;
                });
            });
        }
        var sections = Array.from(document.querySelectorAll('[data-menu-section]'));
        var links = Array.from(document.querySelectorAll('[data-menu-link]'));
        if (!sections.length) return;

        function setActive(id) {
            links.forEach(function (link) {
                if (link.hash === '#' + id) {
                    link.setAttribute('aria-current', 'location');
                } else {
                    link.removeAttribute('aria-current');
                }
            });
        }

        var navigationLinks = links.concat(Array.from(document.querySelectorAll(
            '#header-principal a[href], #spHeaderMobileNav a[href]'
        )).filter(function (link) {
            var url = new URL(link.href);
            return url.origin === location.origin && url.pathname === page.dataset.menuPageUrl && url.hash;
        }));
        navigationLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                var target = document.getElementById(link.hash.slice(1));
                if (!target) return;
                event.preventDefault();
                var mobileIndex = link.closest('details');
                if (mobileIndex) mobileIndex.open = false;
                window.history.pushState(null, '', link.getAttribute('href'));
                setActive(target.id);
                function navigateToSection() {
                    var heading = target.querySelector('h2');
                    if (heading) {
                        heading.setAttribute('tabindex', '-1');
                        heading.focus({ preventScroll: true });
                    }
                    window.scrollTo({
                        top: target.getBoundingClientRect().top + window.scrollY - parseFloat(getComputedStyle(target).scrollMarginTop || '0'),
                        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                    });
                }
                var offcanvas = link.closest('.offcanvas');
                if (offcanvas && offcanvas.classList.contains('show') && window.bootstrap && window.bootstrap.Offcanvas) {
                    offcanvas.addEventListener('hidden.bs.offcanvas', navigateToSection, { once: true });
                    window.bootstrap.Offcanvas.getOrCreateInstance(offcanvas).hide();
                } else {
                    navigateToSection();
                }
            });
        });

        var scheduled = false;
        function updateActive() {
            scheduled = false;
            var offset = window.innerWidth < 992 ? 170 : 132;
            var active = sections[0];
            sections.forEach(function (section) {
                if (section.getBoundingClientRect().top <= offset) active = section;
            });
            setActive(active.id);
        }
        function scheduleUpdate() {
            if (!scheduled) {
                scheduled = true;
                window.requestAnimationFrame(updateActive);
            }
        }
        window.addEventListener('scroll', scheduleUpdate, { passive: true });
        window.addEventListener('resize', scheduleUpdate);
        window.addEventListener('hashchange', scheduleUpdate);
        // The numeric identity keeps historical slug-ID fragments usable after renaming.
        function resolveHistoricalAnchor() {
            var anchor = location.hash.slice(1);
            if (!anchor || document.getElementById(anchor)) return;
            var match = anchor.match(/-(\d+)$/);
            var target = match ? sections.find(function (section) { return section.dataset.submenuId === match[1]; }) : null;
            if (target) {
                window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - parseFloat(getComputedStyle(target).scrollMarginTop || '0'), behavior: 'auto' });
                setActive(target.id);
            }
        }
        window.addEventListener('hashchange', resolveHistoricalAnchor);
        window.addEventListener('load', resolveHistoricalAnchor, { once: true });
        window.addEventListener('popstate', scheduleUpdate);
        resolveHistoricalAnchor();
        updateActive();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeMenuPage, { once: true });
    } else {
        initializeMenuPage();
    }
})();
