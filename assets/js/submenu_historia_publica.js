(function () {
    document.querySelectorAll('[data-history-carousel]').forEach(function (root) {
        var section = root.closest('.historia-visual'), cards = Array.from(root.children);
        var prev = section.querySelector('[data-history-prev]'), next = section.querySelector('[data-history-next]');
        var status = section.querySelector('[data-history-status]'), page = 0, startX = 0;
        function draw() {
            var size = matchMedia('(max-width:767px)').matches ? 1 : 2;
            var pages = Math.ceil(cards.length / size);
            page = Math.max(0, Math.min(page, pages - 1));
            cards.forEach(function (card, i) { card.classList.toggle('is-visible', i >= page * size && i < (page + 1) * size); });
            if (status) status.textContent = (page + 1) + ' / ' + pages;
            if (prev) prev.disabled = page === 0;
            if (next) next.disabled = page === pages - 1;
        }
        if (prev) prev.addEventListener('click', function () { page--; draw(); });
        if (next) next.addEventListener('click', function () { page++; draw(); });
        root.addEventListener('touchstart', function (event) { startX = event.touches[0].clientX; }, { passive: true });
        root.addEventListener('touchend', function (event) { var distance = event.changedTouches[0].clientX - startX; if (Math.abs(distance) > 50) { page += distance < 0 ? 1 : -1; draw(); } }, { passive: true });
        window.addEventListener('resize', draw);
        draw();
    });
})();