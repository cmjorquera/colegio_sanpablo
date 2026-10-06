(function () {
    'use strict';
    var modal = document.getElementById('modalMenu');
    if (!modal) return;
    var form = document.getElementById('formModalMenu'), current = {}, objectUrls = [];
    function field(id) { return document.getElementById(id); }
    function releaseUrls() { objectUrls.forEach(URL.revokeObjectURL); objectUrls = []; }
    function localPreview(input) {
        if (!input.files.length) return '';
        var url = URL.createObjectURL(input.files[0]); objectUrls.push(url); return url;
    }
    function preview(target, source, type, empty) {
        target.replaceChildren();
        if (!source) { var text = document.createElement('span'); text.textContent = empty; target.appendChild(text); return; }
        var media = document.createElement(type === 'embed' ? 'iframe' : type);
        if (type === 'img') media.alt = 'Vista previa de la cabecera';
        if (type === 'video') { media.controls = true; media.preload = 'metadata'; }
        if (type === 'embed') { media.title = 'Vista previa del video de cabecera'; media.allowFullscreen = true; }
        media.src = source;
        media.addEventListener('error', function () { preview(target, '', type, 'No se pudo cargar la vista previa'); }, { once: true });
        target.appendChild(media);
    }
    function embed(url) {
        try {
            var parsed = new URL(url);
            if (!['https:', 'http:'].includes(parsed.protocol)) return '';
            var match;
            if (['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'www.youtu.be'].includes(parsed.hostname)) {
                match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([A-Za-z0-9_-]{6,})/);
                return match ? 'https://www.youtube.com/embed/' + match[1] : '';
            }
            if (['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'].includes(parsed.hostname)) {
                match = url.match(/vimeo\.com\/(\d+)/); return match ? 'https://player.vimeo.com/video/' + match[1] : '';
            }
        } catch (error) {}
        return '';
    }
    function refresh() {
        releaseUrls();
        var imageInput = field('menuHeaderImage'), videoInput = field('menuHeaderVideoFile');
        var imageDeleted = form.elements.delete_menu_imagen_hero.checked;
        var videoDeleted = form.elements.delete_menu_hero_video.checked;
        imageInput.disabled = imageDeleted || form.elements.menu_hero_tipo.value !== 'imagen';
        videoInput.disabled = videoDeleted || form.elements.menu_hero_tipo.value !== 'video';
        field('menuHeaderVideoUrl').disabled = videoDeleted || form.elements.menu_hero_tipo.value !== 'video';
        preview(field('menuHeaderImagePreview'), imageDeleted ? '' : localPreview(imageInput) || current.imagenHero, 'img', imageDeleted ? 'La imagen se eliminará al guardar' : 'Sin imagen de cabecera');
        var typedUrl = field('menuHeaderVideoUrl').value.trim();
        var file = localPreview(videoInput) || (typedUrl === (current.heroVideoUrl || '') ? current.heroVideoArchivo : '');
        preview(field('menuHeaderVideoPreview'), videoDeleted ? '' : file || embed(typedUrl), file ? 'video' : 'embed', videoDeleted ? 'El video se eliminará al guardar' : 'Sin video de cabecera');
    }
    function setMode(type) {
        form.elements.menu_hero_tipo.value = type === 'video' ? 'video' : 'imagen';
        modal.querySelectorAll('[data-menu-header-panel]').forEach(function (panel) {
            var active = panel.dataset.menuHeaderPanel === form.elements.menu_hero_tipo.value;
            panel.hidden = !active;
            panel.querySelectorAll('input').forEach(function (input) { input.disabled = !active; });
        });
        refresh();
    }
    window.abrirModalMenu = function (button) {
        form.reset(); releaseUrls(); current = button ? button.dataset : {};
        field('modalMenuLabel').textContent = button ? 'Editar menú' : 'Nuevo menú';
        field('modalMenuId').value = current.id || '0';
        field('modalMenuNombre').value = current.nombre || '';
        field('modalMenuUrl').value = current.url || '';
        field('modalMenuIcono').value = current.icono || '';
        field('modalMenuEstado').checked = !button || current.estado === '1';
        field('menuHeaderVideoUrl').value = current.heroVideoUrl || '';
        field('menuHeaderImageCurrent').textContent = current.imagenHero ? 'Actual: ' + current.imagenHero : '';
        field('menuHeaderVideoCurrent').textContent = current.heroVideoArchivo || current.heroVideoUrl ? 'Actual: ' + (current.heroVideoArchivo || current.heroVideoUrl) : '';
        setMode(current.heroTipo || 'imagen');
        bootstrap.Tab.getOrCreateInstance(field('menuTabDatosButton')).show();
        bootstrap.Modal.getOrCreateInstance(modal).show();
    };
    form.addEventListener('change', function (event) {
        if (event.target.name === 'menu_hero_tipo') setMode(event.target.value);
        else if (event.target.closest('.menu-header-editor')) refresh();
    });
    field('menuHeaderVideoUrl').addEventListener('blur', refresh);
    form.addEventListener('invalid', function (event) {
        var pane = event.target.closest('.tab-pane');
        if (pane) bootstrap.Tab.getOrCreateInstance(modal.querySelector('[data-bs-target="#' + pane.id + '"]')).show();
    }, true);
    modal.addEventListener('hidden.bs.modal', function () {
        releaseUrls();
        modal.querySelectorAll('.submenu-media-preview').forEach(function (target) { target.replaceChildren(); });
    });
})();
