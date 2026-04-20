document.addEventListener('DOMContentLoaded', function () {
    var footer = document.getElementById('footer-principal');
    if (!footer) {
        return;
    }

    footer.querySelectorAll('.footer-menu-toggle').forEach(function (toggleButton) {
        toggleButton.addEventListener('click', function () {
            var parentItem = toggleButton.closest('.footer-menu-item');
            var targetId = toggleButton.getAttribute('data-target');
            if (!parentItem || !targetId) {
                return;
            }

            var submenu = footer.querySelector('#' + CSS.escape(targetId));
            if (!submenu) {
                return;
            }

            var isOpen = parentItem.classList.contains('is-open');

            footer.querySelectorAll('.footer-menu-item.is-open').forEach(function (openItem) {
                if (openItem === parentItem) {
                    return;
                }

                openItem.classList.remove('is-open');

                var openButton = openItem.querySelector('.footer-menu-toggle');
                var openTargetId = openButton ? openButton.getAttribute('data-target') : '';
                var openSubmenu = openTargetId ? footer.querySelector('#' + CSS.escape(openTargetId)) : null;

                if (openButton) {
                    openButton.setAttribute('aria-expanded', 'false');
                }

                if (openSubmenu) {
                    openSubmenu.hidden = true;
                }
            });

            parentItem.classList.toggle('is-open', !isOpen);
            toggleButton.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
            submenu.hidden = isOpen;
        });
    });
});
