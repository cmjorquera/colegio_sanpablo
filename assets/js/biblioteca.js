(function () {
    function initializeLibrary() {
        if (window.bootstrap && window.bootstrap.Carousel) {
            document.querySelectorAll('[data-library-carousel]').forEach(function (gallery) {
                window.bootstrap.Carousel.getOrCreateInstance(gallery, { interval: false, ride: false, touch: true });
                gallery.addEventListener('slid.bs.carousel', function (event) {
                    gallery.querySelectorAll('.carousel-item a').forEach(function (link) {
                        link.tabIndex = link.closest('.carousel-item').classList.contains('active') ? 0 : -1;
                    });
                    gallery.querySelector('[data-library-gallery-status]').textContent =
                        'Imagen ' + (event.to + 1) + ' de ' + gallery.querySelectorAll('.carousel-item').length;
                });
            });
        }
        var sections = Array.from(document.querySelectorAll('[data-library-section]'));
        var links = Array.from(document.querySelectorAll('[data-library-link]'));
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
            '#header-principal a[href^="/biblioteca#"], #spHeaderMobileNav a[href^="/biblioteca#"]'
        )));
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
                    target.scrollIntoView({
                        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                        block: 'start'
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
        updateActive();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeLibrary, { once: true });
    } else {
        initializeLibrary();
    }
})();
