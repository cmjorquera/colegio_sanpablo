// ─── Toast ───────────────────────────────────────────────────────────────────

function showToast(type, titulo, mensaje) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const iconos = {
        success: 'fa-circle-check',
        error:   'fa-circle-xmark',
        warning: 'fa-triangle-exclamation',
        info:    'fa-circle-info',
    };

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fa-solid ${iconos[type] ?? iconos.info}"></i>
        <div class="toast-body">
            <strong>${escHtml(titulo)}</strong>
            <span>${escHtml(String(mensaje))}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fa-solid fa-xmark"></i>
        </button>`;

    container.appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('toast-visible'));

    setTimeout(() => {
        toast.classList.remove('toast-visible');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ─── Modals ──────────────────────────────────────────────────────────────────

function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.classList.remove('modal-open');
        document.body.style.overflow = '';
    }
}

// ─── Off-canvas ───────────────────────────────────────────────────────────────

function openOC(titulo, html) {
    document.getElementById('oc-titulo').textContent = titulo;
    document.getElementById('oc-body').innerHTML     = html;
    document.getElementById('oc-panel').classList.add('oc-open');
    document.getElementById('oc-overlay').classList.add('oc-overlay-open');
    document.body.style.overflow = 'hidden';
}

function closeOC() {
    document.getElementById('oc-panel').classList.remove('oc-open');
    document.getElementById('oc-overlay').classList.remove('oc-overlay-open');
    document.body.style.overflow = '';
}

// ─── Dropdown ────────────────────────────────────────────────────────────────

function toggleDD(btn) {
    const menu = btn.querySelector('.dropdown-menu') || btn.parentElement?.querySelector('.dropdown-menu');
    if (!menu) return;
    menu.classList.toggle('dd-open');

    const cerrar = (e) => {
        if (!btn.parentElement.contains(e.target)) {
            menu.classList.remove('dd-open');
            document.removeEventListener('click', cerrar);
        }
    };
    setTimeout(() => document.addEventListener('click', cerrar), 0);
}

// ─── Accordion ───────────────────────────────────────────────────────────────

function initAcc() {
    document.querySelectorAll('.acc-header').forEach(header => {
        header.addEventListener('click', function () {
            const item    = this.closest('.acc-item');
            const content = item.querySelector('.acc-body');
            const isOpen  = item.classList.contains('acc-open');

            document.querySelectorAll('.acc-item.acc-open').forEach(el => {
                el.classList.remove('acc-open');
                el.querySelector('.acc-body').style.maxHeight = null;
            });

            if (!isOpen) {
                item.classList.add('acc-open');
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
}

// ─── Tabs ────────────────────────────────────────────────────────────────────

function initTabs(btn, targetId) {
    const container = btn.closest('.tabs');
    if (!container) return;

    container.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    container.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    btn.classList.add('active');
    const target = document.getElementById(targetId);
    if (target) target.classList.add('active');
}

// ─── Utilidades ──────────────────────────────────────────────────────────────

function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Aplica el tema guardado y actualiza el icono del topbar.
function applyTheme() {
    const theme = localStorage.getItem('seduc_theme') || 'light';
    document.documentElement.dataset.theme = theme;
    const icon = document.getElementById('theme-icon');
    if (icon) {
        icon.classList.toggle('fa-sun', theme === 'dark');
        icon.classList.toggle('fa-moon', theme !== 'dark');
    }
}

// Alterna modo oscuro/claro y guarda la preferencia.
function toggleTheme() {
    const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
    localStorage.setItem('seduc_theme', next);
    applyTheme();
    drawSparklines();
}

// Muestra skeleton loaders en el contenedor indicado.
function showSkeleton(containerId, filas = 5, columnas = 4) {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = `<div class="sk-wrap">${Array.from({ length: filas }).map(() => `<div class="sk-row" style="grid-template-columns:repeat(${columnas},1fr)">${Array.from({ length: columnas }).map(() => '<div class="skeleton"></div>').join('')}</div>`).join('')}</div>`;
}

// Limpia el skeleton del contenedor indicado.
function hideSkeleton(containerId) {
    const el = document.getElementById(containerId);
    if (el) el.querySelector('.sk-wrap')?.remove();
}

// Muestra un estado vacío reutilizable.
function showEmptyState(containerId, titulo = 'Sin resultados', subtitulo = 'No hay registros para mostrar.') {
    const el = document.getElementById(containerId);
    if (!el) return;
    el.innerHTML = `<div class="empty-state">
        <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><line x1="20" y1="20" x2="16.5" y2="16.5"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
        <h3>${escHtml(titulo)}</h3><p>${escHtml(subtitulo)}</p>
    </div>`;
}

const seducNotifications = [
    { type: 'success', icon: 'fa-circle-check', title: 'Backup completado', desc: 'El respaldo diario terminó sin errores.', time: 'Hace 4 min' },
    { type: 'warning', icon: 'fa-triangle-exclamation', title: 'Uso de disco alto', desc: 'El servidor llegó al 82% de capacidad.', time: 'Hace 18 min' },
    { type: 'info', icon: 'fa-user-plus', title: 'Nuevo usuario', desc: 'Se creó una cuenta docente en el sistema.', time: 'Hace 35 min' },
    { type: 'error', icon: 'fa-shield-halved', title: 'Intento fallido', desc: 'Tres accesos fueron rechazados por seguridad.', time: 'Hace 1 h' },
    { type: 'info', icon: 'fa-calendar', title: 'Evento próximo', desc: 'Consejo regional programado para mañana.', time: 'Hace 2 h' },
];

// Renderiza el dropdown de notificaciones del topbar.
function renderNotifications() {
    const list = document.getElementById('notification-list');
    if (!list) return;

    list.innerHTML = seducNotifications.map((n, index) => `
        <div class="notification-item">
            <div class="notification-ic toast-${n.type}"><i class="fa-solid ${n.icon}"></i></div>
            <div style="flex:1">
                <div class="notification-title">${escHtml(n.title)}</div>
                <div class="notification-desc">${escHtml(n.desc)}</div>
                <div class="notification-time">${escHtml(n.time)}</div>
            </div>
            <button class="notification-x" onclick="dismissNotification(event, ${index})"><i class="fa-solid fa-xmark"></i></button>
        </div>`).join('') || '<div class="empty-state"><h3>Sin notificaciones</h3><p>Todo está al día.</p></div>';
}

// Descarta una notificación del listado.
function dismissNotification(event, index) {
    event.stopPropagation();
    seducNotifications.splice(index, 1);
    renderNotifications();
    if (!seducNotifications.length) document.getElementById('notification-dot')?.classList.add('hidden');
}

// Marca todas las notificaciones como leídas.
function markAllRead(event) {
    event.stopPropagation();
    document.getElementById('notification-dot')?.classList.add('hidden');
    showToast('success', 'Listo', 'Notificaciones marcadas como leídas.');
}

// Dibuja mini gráficos de línea en canvas sin dependencias.
function drawSparklines() {
    document.querySelectorAll('canvas.sparkline').forEach(canvas => {
        const values = (canvas.dataset.values || '').split(',').map(Number).filter(n => !Number.isNaN(n));
        const ctx = canvas.getContext('2d');
        const ratio = window.devicePixelRatio || 1;
        const w = canvas.width = Math.max(1, canvas.offsetWidth * ratio);
        const h = canvas.height = Math.max(1, canvas.offsetHeight * ratio);
        ctx.clearRect(0, 0, w, h);
        if (values.length < 2) return;

        const min = Math.min(...values);
        const max = Math.max(...values);
        const pad = 4 * ratio;
        ctx.strokeStyle = getComputedStyle(document.documentElement).getPropertyValue('--color-primary').trim();
        ctx.lineWidth = 2 * ratio;
        ctx.beginPath();
        values.forEach((value, index) => {
            const x = pad + index * (w - pad * 2) / (values.length - 1);
            const y = h - pad - ((value - min) / (max - min || 1)) * (h - pad * 2);
            index ? ctx.lineTo(x, y) : ctx.moveTo(x, y);
        });
        ctx.stroke();
    });
}

// Inicializa upload drag and drop con preview y progreso simulado.
function initDragUpload(dropId = 'dropzone', inputId = 'file-input', previewId = 'file-preview') {
    const drop = document.getElementById(dropId);
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    if (!drop || !input || !preview) return;

    const addFiles = files => Array.from(files).forEach(file => {
        const card = document.createElement('div');
        card.className = 'file-card';
        card.innerHTML = `${file.type.startsWith('image/') ? '<img alt="Preview">' : '<div class="skeleton" style="height:86px;margin-bottom:8px"></div>'}
            <div class="file-name">${escHtml(file.name)}</div>
            <div class="progress mt-3"><div class="progress-bar" style="width:0%"></div></div>`;
        preview.appendChild(card);
        if (file.type.startsWith('image/')) card.querySelector('img').src = URL.createObjectURL(file);

        let progress = 0;
        const bar = card.querySelector('.progress-bar');
        const timer = setInterval(() => {
            progress += 20;
            bar.style.width = `${progress}%`;
            if (progress >= 100) {
                clearInterval(timer);
                showToast('success', 'Archivo listo', file.name);
            }
        }, 180);
    });

    drop.onclick = () => input.click();
    input.onchange = e => addFiles(e.target.files);
    ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => {
        e.preventDefault();
        drop.classList.add('drag');
    }));
    ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => {
        e.preventDefault();
        drop.classList.remove('drag');
    }));
    drop.addEventListener('drop', e => addFiles(e.dataTransfer.files));
}

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    renderNotifications();
    drawSparklines();
    document.querySelectorAll('.sidebar-nav a').forEach(link => {
        link.addEventListener('click', event => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || link.target) return;
            event.preventDefault();
            showSkeleton('page-loader-target', 6, 4);
            const content = document.querySelector('.content');
            if (content && !document.getElementById('page-loader-target')) {
                content.insertAdjacentHTML('afterbegin', '<div id="page-loader-target"></div>');
                showSkeleton('page-loader-target', 6, 4);
            }
            setTimeout(() => { window.location.href = link.href; }, 800);
        });
    });
});
