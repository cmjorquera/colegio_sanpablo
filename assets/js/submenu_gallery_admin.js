(function () {
    'use strict';
    var busy = false;
    var openMenu = null;
    function dialog(options) { return Swal.fire(Object.assign({ target: editor() }, options)); }
    function editor() { return document.getElementById('modalSubmenu'); }
    function id() { return document.getElementById('modalSubmenuId').value; }
    function notify(message, icon) {
        if (window.Swal) return dialog({ title: 'Galería', text: message, icon: icon || 'error' });
        return Promise.resolve(window.adminNotify({ title: 'Galería', msg: message, type: 'warning' }));
    }
    function render(media) { window.submenuRenderMedia(JSON.stringify(media)); }
    function closeMenu() {
        if (!openMenu) return;
        var menu = openMenu;
        menu.instance.hide();
        menu.parent.appendChild(menu.element);
        openMenu = null;
    }
    function lock(value) {
        busy = value;
        var gallery = document.getElementById('modalPaginaMediaActual');
        if (gallery && gallery._sortableInstance) gallery._sortableInstance.option('disabled', value);
        editor().querySelectorAll('.submenu-media-gallery button, button[type="submit"]').forEach(function (button) {
            if (value) { button.dataset.galleryWasDisabled = button.disabled ? '1' : '0'; button.disabled = true; }
            else if (button.dataset.galleryWasDisabled != null) {
                button.disabled = button.dataset.galleryWasDisabled === '1'; delete button.dataset.galleryWasDisabled;
            }
        });
    }
    function request(action, values, fileInput) {
        var submenuId = id();
        if (!/^[1-9]\d*$/.test(submenuId)) return Promise.reject(new Error('Guarda primero el submenú.'));
        var data = new FormData();
        data.append('accion', action); data.append('id_sub_menu', submenuId);
        Object.keys(values || {}).forEach(function (key) {
            if (Array.isArray(values[key])) values[key].forEach(function (value) { data.append(key + '[]', value); });
            else data.append(key, values[key]);
        });
        if (fileInput) data.append('pagina_galeria_imagen', fileInput.files[0]);
        var panel = new URLSearchParams(location.search).get('panel') === 'submenus' ? 'submenus' : 'menus';
        return fetch('admin.php?panel=' + panel, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                return response.json().catch(function () { throw new Error('El servidor no devolvió JSON. Comprueba tu sesión y vuelve a intentarlo.'); })
                    .then(function (result) {
                        if (!response.ok || !result.ok || !Array.isArray(result.media)) throw new Error(result.message || 'No se pudo actualizar la galería.');
                        return result;
                    });
            });
    }
    function operate(action, values, fileInput, onSuccess, previous) {
        lock(true);
        return request(action, values, fileInput).then(function (result) {
            render(result.media);
            if (onSuccess) onSuccess();
        }).catch(function (error) {
            if (previous) render(previous);
            return notify(error.message);
        }).finally(function () { lock(false); });
    }
    window.submenuGalleryAdmin = {
        isBusy: function () { return busy; },
        dispose: function (container) {
            closeMenu();
            container.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (button) {
                var instance = bootstrap.Dropdown.getInstance(button); if (instance) instance.dispose();
            });
        },
        initialize: function (container) {
            container.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function (button) {
                bootstrap.Dropdown.getOrCreateInstance(button, { popperConfig: { strategy: 'fixed' } });
            });
            if (typeof Sortable === 'undefined') { notify('No se pudo cargar el componente de arrastre.'); return; }
            var previous;
            container._sortableInstance = Sortable.create(container, {
                animation: 180, draggable: '.submenu-gallery-card', handle: '.submenu-gallery-drag',
                filter: 'input,button,.submenu-gallery-menu', preventOnFilter: false,
                forceFallback: true, fallbackOnBody: true, disabled: busy,
                ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
                onChoose: function () { previous = (window.submenuCurrentMedia || []).slice(); closeMenu(); },
                onEnd: function (event) {
                    if (event.oldIndex === event.newIndex || busy) return;
                    var ids = Array.from(container.querySelectorAll('.submenu-gallery-card')).map(function (card) { return card.dataset.id; });
                    operate('reorder_submenu_media', { items: ids }, null, function () {
                        dialog({ title: 'Orden guardado', text: 'El orden de la galería fue actualizado.', icon: 'success', timer: 1600, showConfirmButton: false });
                    }, previous);
                }
            });
        }
    };
    document.addEventListener('shown.bs.dropdown', function (event) {
        var button = event.target;
        if (!button.matches('#modalSubmenu .submenu-gallery-menu .dropdown-toggle')) return;
        closeMenu();
        var parent = button.parentElement, element = parent.querySelector('.dropdown-menu');
        var card = button.closest('.submenu-gallery-card');
        element.querySelectorAll('button').forEach(function (action) { action.dataset.mediaId = card.dataset.id; });
        openMenu = { parent: parent, element: element, instance: bootstrap.Dropdown.getInstance(button) };
        element.classList.add('submenu-gallery-actions');
        editor().appendChild(element);
        openMenu.instance.update();
    });
    document.addEventListener('hidden.bs.dropdown', function (event) {
        if (openMenu && event.target.parentElement === openMenu.parent) {
            openMenu.parent.appendChild(openMenu.element); openMenu = null;
        }
    });
    window.addEventListener('keydown', function (event) {
        if (!openMenu || !openMenu.element.contains(event.target)) return;
        if (!['Escape', 'ArrowDown', 'ArrowUp'].includes(event.key)) return;
        event.preventDefault(); event.stopPropagation();
        if (event.key === 'Escape') { var toggle = openMenu.parent.querySelector('button'); closeMenu(); toggle.focus(); }
        else {
            var actions = Array.from(openMenu.element.querySelectorAll('button:not(:disabled)'));
            var current = actions.indexOf(document.activeElement);
            actions[(current + (event.key === 'ArrowDown' ? 1 : actions.length - 1) + actions.length) % actions.length].focus();
        }
    }, true);
    document.addEventListener('hide.bs.offcanvas', function (event) {
        if (event.target !== editor()) return;
        if (busy) event.preventDefault(); else closeMenu();
    });
    document.addEventListener('click', function (event) {
        var action = event.target.closest('.js-submenu-gallery-add,.js-submenu-gallery-delete,.js-submenu-gallery-toggle');
        if (!action || !editor().contains(action)) return;
        event.preventDefault();
        if (busy || action.disabled) return;
        var card = action.closest('.submenu-gallery-card');
        var mediaId = action.dataset.mediaId || (card && card.dataset.id);
        closeMenu();
        if (action.matches('.js-submenu-gallery-add')) {
            var gallery = action.closest('.submenu-media-gallery');
            var file = gallery.querySelector('[name="pagina_galeria_imagen"]'), title = gallery.querySelector('[name="pagina_galeria_titulo"]');
            if (!file.files.length) { notify('Selecciona una imagen para agregar.'); return; }
            operate('add_submenu_media', { pagina_galeria_titulo: title.value }, file, function () {
                file.value = ''; title.value = '';
                document.getElementById('modalPaginaGaleriaNuevaPreview').innerHTML = '<span>Sin imagen seleccionada</span>';
                dialog({ title: 'Galería', text: 'Imagen agregada correctamente.', icon: 'success', timer: 1600, showConfirmButton: false });
            });
        } else if (action.matches('.js-submenu-gallery-delete')) {
            lock(true);
            dialog({ title: 'Eliminar imagen', text: 'Se eliminará esta imagen de la galería.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar imagen', cancelButtonText: 'Cancelar' })
                .then(function (result) {
                    if (!result.isConfirmed) return;
                    return request('delete_submenu_media', { id_media: mediaId }).then(function (result) {
                        render(result.media);
                        return dialog({ title: 'Galería', text: 'Imagen eliminada correctamente.', icon: 'success', timer: 1600, showConfirmButton: false });
                    });
                }).catch(function (error) { return notify(error.message); }).finally(function () { lock(false); });
        } else {
            operate('toggle_submenu_media', { id_media: mediaId });
        }
    });
})();
