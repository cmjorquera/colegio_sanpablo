(function () {
    var STORAGE_KEY = 'calendario_eventos_home_view';
    var MONTHS = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    function parseDate(value) {
        if (!value || !/^\d{4}-\d{2}-\d{2}/.test(value)) {
            return null;
        }
        var parts = value.substring(0, 10).split('-').map(function (part) { return parseInt(part, 10); });
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function dateKey(date) {
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
    }

    function formatDate(value) {
        var date = parseDate(value);
        return date ? pad(date.getDate()) + '-' + pad(date.getMonth() + 1) + '-' + date.getFullYear() : 'Sin fecha';
    }

    function formatTime(eventItem) {
        var start = (eventItem.hora_inicio || '').substring(0, 5);
        var end = (eventItem.hora_termino || '').substring(0, 5);
        if (start && end) {
            return start + ' - ' + end;
        }
        return start || end || 'Sin hora';
    }

    function safeColor(value, fallback) {
        return /^#(?:[0-9a-fA-F]{3}){1,2}$/.test(String(value || '').trim()) ? value : fallback;
    }

    function eventStatus(eventItem) {
        var estado = String(eventItem.estado || '').toLowerCase().trim();
        if (estado === 'cancelado') {
            return 'cancelado';
        }
        if (parseInt(eventItem.visible, 10) === 0 || estado === 'oculto') {
            return 'oculto';
        }
        if (estado === 'borrador') {
            return 'borrador';
        }
        return 'publicado';
    }

    function statusLabel(status) {
        return {
            publicado: 'Publicado',
            borrador: 'Borrador',
            oculto: 'Oculto',
            cancelado: 'Cancelado'
        }[status] || 'Publicado';
    }

    function notifyDenied(message) {
        if (window.adminNotifyDenied) {
            window.adminNotifyDenied(message);
            return;
        }
        if (window.Swal) {
            Swal.fire({ icon: 'warning', title: 'Permiso requerido', text: message, confirmButtonColor: '#26384f' });
            return;
        }
        alert(message);
    }

    function clearNode(node) {
        while (node.firstChild) {
            node.removeChild(node.firstChild);
        }
    }

    function makeEl(tag, className, text) {
        var el = document.createElement(tag);
        if (className) {
            el.className = className;
        }
        if (text !== undefined) {
            el.textContent = text;
        }
        return el;
    }

    function initModule(module) {
        var jsonNode = module.querySelector('[data-admin-events-json]');
        var holidaysNode = module.querySelector('[data-admin-holidays-json]');
        var events = [];
        var holidays = [];
        var currentMonth;
        var canEdit = module.getAttribute('data-can-edit') === '1';
        var deniedMessage = module.getAttribute('data-denied-message') || 'No tienes permiso para editar.';

        try {
            events = JSON.parse(jsonNode ? jsonNode.textContent : '[]');
        } catch (error) {
            events = [];
        }

        try {
            holidays = JSON.parse(holidaysNode ? holidaysNode.textContent : '[]');
        } catch (error) {
            holidays = [];
        }

        events = events
            .map(function (eventItem) {
                eventItem._date = parseDate(eventItem.fecha_inicio);
                eventItem._status = eventStatus(eventItem);
                return eventItem;
            })
            .filter(function (eventItem) { return !!eventItem._date; })
            .sort(function (a, b) {
                return a._date - b._date || String(a.hora_inicio || '').localeCompare(String(b.hora_inicio || ''));
            });

        // Los feriados nacionales/institucionales vienen de la tabla calendario. Nunca
        // se mezclan con la tabla de eventos ni con la carga masiva de eventos.
        holidays = holidays
            .map(function (holiday) {
                holiday._date = parseDate(holiday.fecha);
                return holiday;
            })
            .filter(function (holiday) { return !!holiday._date; })
            .sort(function (a, b) { return a._date - b._date; });

        currentMonth = events.length ? new Date(events[0]._date.getFullYear(), events[0]._date.getMonth(), 1) : new Date();

        var buttons = Array.prototype.slice.call(module.querySelectorAll('[data-event-view-button]'));
        var panels = Array.prototype.slice.call(module.querySelectorAll('[data-event-view-panel]'));
        var title = module.querySelector('[data-calendar-title]');
        var grid = module.querySelector('[data-calendar-grid]');
        var list = module.querySelector('[data-calendar-list]');
        var count = module.querySelector('[data-calendar-count]');
        var prev = module.querySelector('[data-calendar-prev]');
        var next = module.querySelector('[data-calendar-next]');

        function setView(view) {
            if (view !== 'calendario') {
                view = 'tabla';
            }
            buttons.forEach(function (button) {
                button.classList.toggle('is-active', button.getAttribute('data-event-view-button') === view);
            });
            panels.forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-event-view-panel') !== view;
            });
            try {
                localStorage.setItem(STORAGE_KEY, view);
            } catch (error) {}
        }

        function eventsForMonth() {
            return events.filter(function (eventItem) {
                return eventItem._date.getFullYear() === currentMonth.getFullYear()
                    && eventItem._date.getMonth() === currentMonth.getMonth();
            });
        }

        function holidaysForMonth() {
            return holidays.filter(function (holiday) {
                return holiday._date.getFullYear() === currentMonth.getFullYear()
                    && holiday._date.getMonth() === currentMonth.getMonth();
            });
        }

        function renderDayLabels(cell, dayEvents, dayHolidays) {
            var shown = 0;
            var total = dayHolidays.length + dayEvents.length;

            dayHolidays.slice(0, 3).forEach(function (holiday) {
                var label = makeEl('span', 'admin-events-day-label is-feriado');
                label.style.setProperty('--event-color', safeColor(holiday.color, '#C6005A'));
                label.textContent = holiday.nombre_feriado || 'Feriado';
                cell.appendChild(label);
                shown += 1;
            });
            dayEvents.slice(0, Math.max(0, 3 - shown)).forEach(function (eventItem) {
                var label = makeEl('span', 'admin-events-day-label is-' + eventItem._status);
                label.style.setProperty('--event-color', safeColor(eventItem.color, '#2563eb'));
                label.textContent = eventItem.titulo || 'Evento';
                cell.appendChild(label);
                shown += 1;
            });
            if (total > shown) {
                cell.appendChild(makeEl('span', 'admin-events-day-more', '+' + (total - shown) + ' más'));
            }
        }

        function buildEventCard(eventItem) {
            var card = makeEl('article', 'admin-events-list-item is-' + eventItem._status);
            var top = makeEl('div', 'admin-events-list-item__top');
            var heading = makeEl('strong', null, eventItem.titulo || 'Evento sin título');
            var badge = makeEl('span', 'admin-events-status is-' + eventItem._status, statusLabel(eventItem._status));
            top.appendChild(heading);
            top.appendChild(badge);

            var meta = makeEl('div', 'admin-events-list-item__meta');
            meta.appendChild(makeEl('span', null, formatDate(eventItem.fecha_inicio)));
            meta.appendChild(makeEl('span', null, formatTime(eventItem)));
            meta.appendChild(makeEl('span', null, eventItem.ubicacion || 'Sin ubicación'));

            var actions = makeEl('div', 'admin-events-list-item__actions');
            var review = makeEl('a', 'admin-events-action', 'Revisar');
            review.href = eventItem.detalle_url || ('evento_detalle.php?id_evento=' + eventItem.id_evento);
            review.target = '_blank';
            review.rel = 'noopener';
            actions.appendChild(review);

            if (canEdit) {
                var edit = makeEl('a', 'admin-events-action', 'Editar');
                edit.href = eventItem.editar_url || '#';
                actions.appendChild(edit);
            } else {
                var disabledEdit = makeEl('button', 'admin-events-action is-disabled', 'Editar');
                disabledEdit.type = 'button';
                disabledEdit.setAttribute('data-admin-denied', deniedMessage);
                disabledEdit.addEventListener('click', function () {
                    notifyDenied(deniedMessage);
                });
                actions.appendChild(disabledEdit);
            }

            card.appendChild(top);
            card.appendChild(meta);
            card.appendChild(actions);
            return card;
        }

        function buildHolidayCard(holiday) {
            var card = makeEl('article', 'admin-events-list-item is-feriado');
            card.style.setProperty('--feriado-color', safeColor(holiday.color, '#C6005A'));

            var top = makeEl('div', 'admin-events-list-item__top');
            var heading = makeEl('strong', null, holiday.nombre_feriado || 'Feriado');
            var badge = makeEl('span', 'admin-events-status is-feriado', 'Feriado');
            top.appendChild(heading);
            top.appendChild(badge);

            var meta = makeEl('div', 'admin-events-list-item__meta');
            meta.appendChild(makeEl('span', null, formatDate(holiday.fecha)));
            if (holiday.nombre_dia_semana) {
                meta.appendChild(makeEl('span', null, holiday.nombre_dia_semana));
            }
            meta.appendChild(makeEl('span', null, holiday.tipo || 'feriado'));

            var actions = makeEl('div', 'admin-events-list-item__actions');
            var review = makeEl('a', 'admin-events-action', 'Revisar feriado');
            review.href = holiday.detalle_url || ('feriado_detalle.php?id_calendario=' + holiday.id_calendario);
            review.target = '_blank';
            review.rel = 'noopener';
            actions.appendChild(review);

            card.appendChild(top);
            card.appendChild(meta);
            card.appendChild(actions);
            return card;
        }

        function renderList(monthEvents, monthHolidays) {
            clearNode(list);
            var countLabel = monthEvents.length + (monthEvents.length === 1 ? ' evento' : ' eventos');
            if (monthHolidays.length) {
                countLabel += ' · ' + monthHolidays.length + (monthHolidays.length === 1 ? ' feriado' : ' feriados');
            }
            count.textContent = countLabel;

            if (!monthEvents.length && !monthHolidays.length) {
                var empty = makeEl('div', 'admin-events-empty');
                empty.appendChild(makeEl('strong', null, 'Sin eventos ni feriados en este mes'));
                empty.appendChild(makeEl('span', null, 'Usa los botones del calendario para revisar otros meses.'));
                list.appendChild(empty);
                return;
            }

            monthHolidays
                .map(function (holiday) { return { date: holiday._date, node: buildHolidayCard(holiday) }; })
                .concat(monthEvents.map(function (eventItem) { return { date: eventItem._date, node: buildEventCard(eventItem) }; }))
                .sort(function (a, b) { return a.date - b.date; })
                .forEach(function (entry) { list.appendChild(entry.node); });
        }

        function renderCalendar() {
            var year = currentMonth.getFullYear();
            var month = currentMonth.getMonth();
            var monthEvents = eventsForMonth();
            var monthHolidays = holidaysForMonth();
            var byDay = {};
            var holidayByDay = {};

            monthEvents.forEach(function (eventItem) {
                var key = dateKey(eventItem._date);
                if (!byDay[key]) {
                    byDay[key] = [];
                }
                byDay[key].push(eventItem);
            });

            monthHolidays.forEach(function (holiday) {
                var key = dateKey(holiday._date);
                if (!holidayByDay[key]) {
                    holidayByDay[key] = [];
                }
                holidayByDay[key].push(holiday);
            });

            title.textContent = MONTHS[month] + ' ' + year;
            clearNode(grid);

            var first = new Date(year, month, 1);
            var startOffset = (first.getDay() + 6) % 7;
            var cursor = new Date(year, month, 1 - startOffset);

            for (var i = 0; i < 42; i += 1) {
                var cellDate = new Date(cursor.getFullYear(), cursor.getMonth(), cursor.getDate());
                var key = dateKey(cellDate);
                var cell = makeEl('div', 'admin-events-day');
                var number = makeEl('span', 'admin-events-day__number', String(cellDate.getDate()));
                cell.appendChild(number);

                if (cellDate.getMonth() !== month) {
                    cell.classList.add('is-outside');
                }
                if (key === dateKey(new Date())) {
                    cell.classList.add('is-today');
                }
                if (holidayByDay[key]) {
                    cell.classList.add('has-feriado');
                }
                renderDayLabels(cell, byDay[key] || [], holidayByDay[key] || []);
                grid.appendChild(cell);
                cursor.setDate(cursor.getDate() + 1);
            }

            renderList(monthEvents, monthHolidays);
        }

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                setView(button.getAttribute('data-event-view-button'));
            });
        });

        if (prev) {
            prev.addEventListener('click', function () {
                currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() - 1, 1);
                renderCalendar();
            });
        }
        if (next) {
            next.addEventListener('click', function () {
                currentMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 1);
                renderCalendar();
            });
        }

        renderCalendar();
        var stored = 'tabla';
        try {
            stored = localStorage.getItem(STORAGE_KEY) || 'tabla';
        } catch (error) {}
        setView(stored);
    }

    function init() {
        document.querySelectorAll('[data-admin-events-module]').forEach(initModule);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
