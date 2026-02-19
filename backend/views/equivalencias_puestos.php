<style>
/* ===== EQUIVALENCIA PUESTOS — ESTILOS ===== */
/* Contraste entre secciones */
.eq-card-legacy {
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border-left: 4px solid #8a8f9a;
}
.eq-card-legacy .card-header {
    border-bottom: 1px solid #b4bac2;
}
.eq-card-sparta {
    border-left: 4px solid #2196F3;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
/* Headers Legacy y Sparta: mismas dimensiones, parejos */
.eq-card-legacy .card-header,
.eq-card-sparta .card-header {
    min-height: 4.5rem;
    padding: 0.85rem 1.25rem !important;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.eq-card-legacy .card-header h6,
.eq-card-sparta .card-header h6 {
    font-weight: 700;
    font-size: 1.05rem;
    margin-bottom: 0.15rem;
}
.eq-card-legacy .card-header small,
.eq-card-sparta .card-header small {
    font-size: 0.8125rem;
    line-height: 1.3;
}
.eq-header-sparta {
    background: #E3F2FD !important;
    color: #1565C0 !important;
    border: none;
    border-bottom: 1px solid rgba(33, 150, 243, 0.25);
}
.eq-header-sparta h6 { color: #1565C0 !important; }
.eq-header-sparta small { color: #1976D2 !important; }
.eq-header-sparta .fa-shield-halved { color: #2196F3; }
/* Tarjetas de puestos Legacy: separación clara entre bloques */
#col-legacy {
    background-color: #f8f9fa;
    padding-top: 1rem;
}
.eq-legacy-row {
    transition: background-color 0.25s ease, box-shadow 0.25s ease, transform 0.2s ease;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    border: 1px solid #b4bac2;
    border-radius: 8px;
    background-color: #fff;
}
.eq-legacy-row:hover { background-color: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.eq-legacy-row + .eq-legacy-row { margin-top: 0.75rem; }
/* Contrae/expande: con clic se despliega; con mouse solo si se está arrastrando un puesto desde Sparta */
.eq-legacy-titulo {
    cursor: pointer;
    padding: 0.25rem 0;
    user-select: none;
}
.eq-legacy-chevron {
    transition: transform 0.25s ease;
    color: #8a8f9a;
    font-size: 0.75rem;
}
.eq-legacy-row.eq-legacy-expanded .eq-legacy-chevron,
body.eq-dragging-puesto .eq-legacy-row:hover .eq-legacy-chevron {
    transform: rotate(180deg);
    color: #5a5fd6;
}
.eq-legacy-drop-wrap {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease;
}
.eq-legacy-row.eq-legacy-expanded .eq-legacy-drop-wrap,
body.eq-dragging-puesto .eq-legacy-row:hover .eq-legacy-drop-wrap {
    max-height: 900px;
}
.eq-item-sparta,
.lista-equivalentes-legacy .list-group-item {
    transition: box-shadow 0.25s ease, background-color 0.25s ease, transform 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #b4bac2;
    border-radius: 6px;
}
/* Separación sutil entre puestos asignados dentro de un mismo Legacy (2 o más en un bloque) */
.lista-equivalentes-legacy .list-group-item + .list-group-item {
    margin-top: 0.4rem;
}
/* Línea de cierre bajo el último puesto Sparta en cada Legacy (evita efecto de más espacio abajo) */
.lista-equivalentes-legacy .list-group-item:last-child {
    margin-bottom: 0.35rem;
    padding-bottom: 0.35rem;
    border-bottom: 2px solid #b4bac2;
}
/* Zona de drop */
.eq-drop-zone {
    min-height: 52px;
    border: 2px dashed #b4bac2;
    border-radius: 8px;
    background-color: #fafbfc;
    transition: border-color 0.25s ease, background-color 0.25s ease;
}
.eq-drop-zone.drag-over {
    border-color: #696cff;
    background-color: rgba(105, 108, 255, 0.06);
}
/* Bloque arrastrable */
.eq-item-sparta,
.eq-item-sparta *,
.lista-equivalentes-legacy .list-group-item,
.lista-equivalentes-legacy .list-group-item * {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
}
.eq-item-sparta,
.lista-equivalentes-legacy .list-group-item {
    cursor: grab;
}
.eq-item-sparta:hover,
.lista-equivalentes-legacy .list-group-item:hover {
    background-color: #f0f2f4;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.eq-item-sparta:active,
.lista-equivalentes-legacy .list-group-item:active { cursor: grabbing; }
.eq-dragging {
    opacity: 0.92;
    box-shadow: 0 10px 24px rgba(0,0,0,0.15);
    transition: box-shadow 0.2s ease, opacity 0.2s ease;
}
.sortable-ghost {
    opacity: 0.35;
    transition: opacity 0.2s ease;
}
/* Avatar Sparta en azul claro */
#lista-sparta .avatar.bg-label-secondary { background-color: rgba(33, 150, 243, 0.15) !important; color: #1976D2 !important; }
/* Botón quitar */
.eq-remove-wrap {
    margin-left: auto;
    padding-left: 0.75rem;
    border-left: 1px solid rgba(0,0,0,0.08);
    flex-shrink: 0;
}
.eq-btn-quitar {
    width: 28px;
    height: 28px;
    padding: 0;
    border: none;
    border-radius: 50%;
    background: rgba(102, 126, 234, 0.1);
    color: #667eea;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
}
.eq-btn-quitar:hover {
    background: rgba(210, 53, 69, 0.12);
    color: #d33545;
}
.eq-btn-quitar i { font-size: 0.7rem; }
.min-h-list { min-height: 320px; }
/* Sparta: fondo opaco azul claro visible (como Legacy) */
.eq-card-sparta .card-body,
#col-sparta {
    background-color: #dce9f5 !important;
    border-radius: 0 0 0.375rem 0.375rem;
}
#lista-sparta {
    background-color: transparent !important;
}
@media (max-width: 768px) { .min-h-list { min-height: 220px; } }

/* ===== MODO OSCURO — Equivalencia puestos (Legacy y Sparta) ===== */
body.dark-mode .eq-card-legacy {
    border-left-color: #94a3b8;
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}
body.dark-mode .eq-card-legacy .card-header {
    background: rgba(51, 65, 85, 0.9) !important;
    border-bottom-color: #475569;
}
body.dark-mode .eq-card-legacy .card-header h6,
body.dark-mode .eq-card-legacy .card-header .text-primary {
    color: #e2e8f0 !important;
}
body.dark-mode .eq-card-legacy .card-header small,
body.dark-mode .eq-card-legacy .card-header .text-muted {
    color: #94a3b8 !important;
}
body.dark-mode .eq-card-legacy .card-header .fa-database {
    color: #94a3b8 !important;
}
body.dark-mode #col-legacy {
    background-color: #1e293b !important;
}
/* Filas Legacy: fondo y texto con buen contraste */
body.dark-mode .eq-legacy-row {
    background-color: #334155 !important;
    border-color: #475569 !important;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
body.dark-mode .eq-legacy-row:hover {
    background-color: #334155 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.35);
}
body.dark-mode .eq-legacy-row .card-body,
body.dark-mode .eq-legacy-row .eq-legacy-titulo,
body.dark-mode .eq-legacy-row .fw-bold,
body.dark-mode .eq-legacy-row .card-body span {
    color: #e2e8f0 !important;
}
body.dark-mode .eq-legacy-chevron {
    color: #94a3b8 !important;
}
body.dark-mode .eq-legacy-row.eq-legacy-expanded .eq-legacy-chevron,
body.dark-mode.eq-dragging-puesto .eq-legacy-row:hover .eq-legacy-chevron {
    color: #818cf8 !important;
}
/* Lista de puestos asignados dentro de cada Legacy */
body.dark-mode .lista-equivalentes-legacy {
    background-color: #1e293b !important;
    border-color: #475569 !important;
}
body.dark-mode .lista-equivalentes-legacy .list-group-item {
    background-color: #334155 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode .lista-equivalentes-legacy .list-group-item .fw-medium,
body.dark-mode .lista-equivalentes-legacy .list-group-item span:not(.avatar *) {
    color: #e2e8f0 !important;
}
body.dark-mode .lista-equivalentes-legacy .list-group-item small,
body.dark-mode .lista-equivalentes-legacy .list-group-item .text-muted {
    color: #94a3b8 !important;
}
body.dark-mode .lista-equivalentes-legacy .list-group-item:last-child {
    border-bottom-color: #475569 !important;
}
body.dark-mode .lista-equivalentes-legacy .list-group-item:hover {
    background-color: #475569 !important;
}
/* Avatar en items Legacy (icono beige/crema → tono que contraste en oscuro) */
body.dark-mode .lista-equivalentes-legacy .avatar.bg-label-secondary {
    background-color: rgba(148, 163, 184, 0.35) !important;
    color: #e2e8f0 !important;
}
/* Zona de drop */
body.dark-mode .eq-drop-zone {
    background-color: #1e293b !important;
    border-color: #475569 !important;
}
body.dark-mode .eq-drop-zone.drag-over {
    border-color: #818cf8 !important;
    background-color: rgba(129, 140, 248, 0.15) !important;
}
/* Sparta columna en modo oscuro */
body.dark-mode .eq-card-sparta .card-body,
body.dark-mode #col-sparta {
    background-color: #1e3a5f !important;
}
body.dark-mode .eq-header-sparta {
    background: rgba(30, 58, 95, 0.95) !important;
    color: #93c5fd !important;
    border-bottom-color: #3b82f6 !important;
}
body.dark-mode .eq-header-sparta h6,
body.dark-mode .eq-header-sparta small {
    color: #93c5fd !important;
}
body.dark-mode .eq-header-sparta .fa-shield-halved {
    color: #60a5fa !important;
}
body.dark-mode #lista-sparta .list-group-item {
    background-color: #334155 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
}
body.dark-mode #lista-sparta .list-group-item .fw-medium,
body.dark-mode #lista-sparta .list-group-item span:not(.avatar *) {
    color: #e2e8f0 !important;
}
body.dark-mode #lista-sparta .list-group-item small {
    color: #94a3b8 !important;
}
body.dark-mode #lista-sparta .list-group-item:hover {
    background-color: #475569 !important;
}
body.dark-mode #lista-sparta .avatar.bg-label-secondary {
    background-color: rgba(96, 165, 250, 0.25) !important;
    color: #93c5fd !important;
}
/* Loaders y empty en modo oscuro */
body.dark-mode #loader-legacy.text-muted,
body.dark-mode #empty-legacy.text-muted,
body.dark-mode #loader-sparta.text-muted,
body.dark-mode #empty-sparta.text-muted {
    color: #94a3b8 !important;
}
body.dark-mode .eq-btn-quitar {
    background: rgba(129, 140, 248, 0.2);
    color: #a5b4fc;
}
body.dark-mode .eq-btn-quitar:hover {
    background: rgba(248, 113, 113, 0.25);
    color: #fca5a5;
}
</style>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title text-primary mb-2">
            <i class="fa-solid fa-arrows-left-right-to-line me-2"></i>Equivalencia puestos
        </h5>
        <p class="text-muted mb-0">
            Asigne puestos <strong>Sparta</strong> a cada puesto <strong>Legacy</strong>. Arrastre desde la columna derecha (Sparta) y suelte sobre el puesto Legacy correspondiente. Los cambios se guardan automáticamente en <code>equivalencias_legacy_puestos</code>.
        </p>
    </div>
</div>

<div class="row g-4">
    <!-- Columna Legacy (izquierda) -->
    <div class="col-lg-6">
        <div class="card eq-card-legacy shadow-sm h-100">
            <div class="card-header bg-label-primary">
                <h6 class="mb-0 text-primary">
                    <i class="fa-solid fa-database me-2"></i>Legacy
                </h6>
                <small class="text-muted">Puestos del sistema legacy. Suelte aquí los puestos Sparta equivalentes.</small>
            </div>
            <div class="card-body overflow-auto min-h-list" id="col-legacy">
                <div id="loader-legacy" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i>
                    <p class="mb-0">Cargando puestos Legacy...</p>
                </div>
                <div id="lista-legacy" class="d-none"></div>
                <div id="empty-legacy" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">No hay puestos Legacy.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna Sparta (derecha) -->
    <div class="col-lg-6">
        <div class="card eq-card-sparta shadow-sm h-100">
            <div class="card-header eq-header-sparta">
                <h6 class="mb-0">
                    <i class="fa-solid fa-shield-halved me-2"></i>Sparta
                </h6>
                <small>Arrastre un puesto hacia Legacy; desaparecerá aquí y quedará asignado a un solo Legacy.</small>
            </div>
            <div class="card-body overflow-auto min-h-list" id="col-sparta">
                <div id="loader-sparta" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i>
                    <p class="mb-0">Cargando puestos Sparta...</p>
                </div>
                <ul id="lista-sparta" class="list-group list-group-flush d-none"></ul>
                <div id="empty-sparta" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">No hay puestos Sparta.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const GROUP_NAME = 'eq';
    let legacy = [];
    let sparta = [];
    let sortableLegacy = [];

    /* Clic en título Legacy: desplegar/contraer (sin arrastrar solo se abre con clic) */
    document.getElementById('col-legacy').addEventListener('click', function(e) {
        var tit = e.target.closest('.eq-legacy-titulo');
        if (!tit) return;
        var row = tit.closest('.eq-legacy-row');
        if (row) row.classList.toggle('eq-legacy-expanded');
    });

    function esc(s) {
        if (s == null) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderColumnaLegacy() {
        const cont = document.getElementById('lista-legacy');
        const empty = document.getElementById('empty-legacy');
        const loader = document.getElementById('loader-legacy');
        loader.classList.add('d-none');
        cont.classList.remove('d-none');
        cont.innerHTML = '';
        if (!legacy.length) {
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');
        legacy.forEach(function(l) {
            const row = document.createElement('div');
            row.className = 'card eq-legacy-row mb-2';
            row.setAttribute('data-id-legacy', l.id);
            row.innerHTML =
                '<div class="card-body py-2">' +
                '  <div class="d-flex align-items-center justify-content-between eq-legacy-titulo" title="Pase el mouse para desplegar">' +
                '    <span class="fw-bold">' + esc(l.nombre) + '</span>' +
                '    <span class="d-flex align-items-center gap-1">' +
                '' +
                '      <i class="fa-solid fa-chevron-down eq-legacy-chevron"></i>' +
                '    </span>' +
                '  </div>' +
                '  <div class="eq-legacy-drop-wrap">' +
                '    <ul class="list-group list-group-flush lista-equivalentes-legacy border rounded eq-drop-zone p-1 mt-2" data-id-legacy="' + l.id + '"></ul>' +
                '  </div>' +
                '</div>';
            cont.appendChild(row);
        });
        initSortableLegacy();
    }

    function crearLiSparta(s) {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex align-items-center py-2 eq-item-sparta';
        li.setAttribute('data-puesto-id', s.id);
        li.setAttribute('data-puesto-nombre', s.nombre || '');
        li.innerHTML =
            '<span class="avatar avatar-sm me-2 rounded bg-label-secondary">' +
            '  <i class="fa-solid fa-user-tie"></i>' +
            '</span>' +
            '<div class="flex-grow-1">' +
            '  <span class="fw-medium">' + esc(s.nombre) + '</span>' +
            (s.departamento_nombre ? '<br><small class="text-muted">' + esc(s.departamento_nombre) + '</small>' : '') +
            '</div>' +
            '<i class="fa-solid fa-grip-vertical text-muted"></i>';
        return li;
    }

    function renderColumnaSparta(idsAsignados) {
        const ul = document.getElementById('lista-sparta');
        const empty = document.getElementById('empty-sparta');
        const loader = document.getElementById('loader-sparta');
        loader.classList.add('d-none');
        ul.classList.remove('d-none');
        ul.innerHTML = '';
        var set = (idsAsignados instanceof Set) ? idsAsignados : new Set([].concat(idsAsignados || []).map(String));
        var disponibles = sparta.filter(function(s) { return !set.has(String(s.id)); });
        if (!disponibles.length) {
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');
        disponibles.forEach(function(s) {
            ul.appendChild(crearLiSparta(s));
        });
        initSortableSparta();
    }

    function initSortableSparta() {
        const el = document.getElementById('lista-sparta');
        if (!el) return;
        if (el._sortable) { el._sortable.destroy(); el._sortable = null; }
        el._sortable = new Sortable(el, {
            group: { name: GROUP_NAME, pull: true, put: true },
            sort: false,
            animation: 220,
            forceFallback: true,
            fallbackOnBody: true,
            ghostClass: 'sortable-ghost',
            chosenClass: 'eq-dragging',
            dragClass: 'eq-dragging',
            filter: '.eq-remove-wrap',
            preventOnFilter: true,
            onStart: function() { document.body.classList.add('eq-dragging-puesto'); },
            onEnd: function() { document.body.classList.remove('eq-dragging-puesto'); },
            onRemove: function() { guardarAuto(); }
        });
    }

    function initSortableLegacy() {
        sortableLegacy.forEach(function(s) { if (s && s.destroy) s.destroy(); });
        sortableLegacy = [];
        document.querySelectorAll('.lista-equivalentes-legacy').forEach(function(ul) {
            const sortable = new Sortable(ul, {
                group: { name: GROUP_NAME, put: true, pull: false },
                animation: 220,
                forceFallback: true,
                fallbackOnBody: true,
                ghostClass: 'sortable-ghost',
                filter: '.eq-remove-wrap',
                preventOnFilter: true,
                onAdd: function(evt) {
                    var item = evt.item;
                    var idLegacy = ul.getAttribute('data-id-legacy');
                    var idPuesto = item.getAttribute('data-puesto-id');
                    if (!idPuesto) return;
                    addRemoveButton(item, idLegacy);
                    guardarAuto();
                },
                onRemove: function() { guardarAuto(); }
            });
            sortableLegacy.push(sortable);
        });
    }

    function devolverASparta(item) {
        var id = item.getAttribute('data-puesto-id');
        if (!id) return;
        var s = sparta.find(function(p) { return String(p.id) === String(id); });
        if (!s) return;
        var listLegacy = item.closest('.lista-equivalentes-legacy');
        item.remove();
        var ul = document.getElementById('lista-sparta');
        var empty = document.getElementById('empty-sparta');
        if (empty && empty.classList.contains('d-none') === false) empty.classList.add('d-none');
        var newLi = crearLiSparta(s);
        var insertIndex = sparta.findIndex(function(p) { return String(p.id) === String(s.id); });
        if (insertIndex < 0) insertIndex = sparta.length;
        var inserted = false;
        for (var i = 0; i < ul.children.length; i++) {
            var child = ul.children[i];
            var pid = child.getAttribute('data-puesto-id');
            var idx = sparta.findIndex(function(p) { return String(p.id) === String(pid); });
            if (idx > insertIndex) {
                ul.insertBefore(newLi, child);
                inserted = true;
                break;
            }
        }
        if (!inserted) ul.appendChild(newLi);
        guardarAuto();
    }

    function addRemoveButton(item, idLegacy) {
        if (item.querySelector('.eq-remove-wrap')) return;
        var wrap = document.createElement('div');
        wrap.className = 'eq-remove-wrap';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'eq-btn-quitar';
        btn.title = 'Quitar equivalencia';
        btn.setAttribute('aria-label', 'Quitar');
        btn.innerHTML = '<i class="fa-solid fa-times"></i>';
        btn.onclick = function(e) { e.stopPropagation(); devolverASparta(item); };
        wrap.appendChild(btn);
        item.classList.add('d-flex', 'align-items-center');
        item.appendChild(wrap);
    }

    function crearLiLegacy(sp) {
        var li = document.createElement('li');
        li.className = 'list-group-item d-flex align-items-center py-2 eq-item-sparta';
        li.setAttribute('data-puesto-id', sp.id);
        li.setAttribute('data-puesto-nombre', sp.nombre || '');
        li.innerHTML =
            '<span class="avatar avatar-sm me-2 rounded bg-label-secondary"><i class="fa-solid fa-user-tie"></i></span>' +
            '<div class="flex-grow-1"><span class="fw-medium">' + esc(sp.nombre) + '</span>' +
            (sp.departamento_nombre ? '<br><small class="text-muted">' + esc(sp.departamento_nombre) + '</small>' : '') +
            '</div>';
        return li;
    }

    function aplicarEquivalenciasGuardadas(equivalencias) {
        equivalencias.forEach(function(eq) {
            var ul = document.querySelector('.lista-equivalentes-legacy[data-id-legacy="' + eq.id_puesto_legacy + '"]');
            if (!ul) return;
            var sp = sparta.find(function(s) { return String(s.id) === String(eq.id_puesto); });
            if (!sp) return;
            var li = crearLiLegacy(sp);
            addRemoveButton(li, eq.id_puesto_legacy);
            ul.appendChild(li);
        });
        initSortableLegacy();
    }

    function recogerEquivalencias() {
        const pares = [];
        document.querySelectorAll('.lista-equivalentes-legacy').forEach(function(ul) {
            const idLegacy = ul.getAttribute('data-id-legacy');
            ul.querySelectorAll('[data-puesto-id]').forEach(function(item) {
                const idPuesto = item.getAttribute('data-puesto-id');
                if (idPuesto && idLegacy) pares.push({ id_puesto: idPuesto, id_puesto_legacy: idLegacy });
            });
        });
        return pares;
    }

    var guardarAutoTimer = null;
    function guardarAuto() {
        const pares = recogerEquivalencias();
        if (guardarAutoTimer) clearTimeout(guardarAutoTimer);
        guardarAutoTimer = setTimeout(function() {
            guardarAutoTimer = null;
            http.request({
                endpoint: '/equivalencias/guardarEquivalencias',
                method: 'POST',
                data: JSON.stringify({ equivalencias: pares }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(resp) {
                    if (resp && resp.success) {
                        Swal.fire({ icon: 'success', title: 'Guardado', text: 'Equivalencias actualizadas.', timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: (resp && resp.mensaje) || 'No se pudieron guardar.' });
                    }
                },
                onError: function() {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.' });
                }
            });
        }, 300);
    }

    Promise.all([
        fetch('/equivalencias/getPuestosLegacy').then(function(r) { return r.json(); }),
        fetch('/equivalencias/getPuestosSparta').then(function(r) { return r.json(); }),
        fetch('/equivalencias/getEquivalencias').then(function(r) { return r.json(); })
    ]).then(function(results) {
        var resLegacy = results[0], resSparta = results[1], resEq = results[2];
        if (resLegacy && resLegacy.success && resLegacy.datos) legacy = resLegacy.datos;
        if (resSparta && resSparta.success && resSparta.datos) sparta = resSparta.datos;
        var equivalencias = (resEq && resEq.success && resEq.datos) ? resEq.datos : [];
        var idsAsignados = new Set(equivalencias.map(function(eq) { return String(eq.id_puesto); }));
        renderColumnaLegacy();
        renderColumnaSparta(idsAsignados);
        if (equivalencias.length) aplicarEquivalenciasGuardadas(equivalencias);
    }).catch(function() {
        document.getElementById('loader-legacy').classList.add('d-none');
        document.getElementById('loader-sparta').classList.add('d-none');
        document.getElementById('lista-legacy').classList.remove('d-none');
        document.getElementById('lista-sparta').classList.remove('d-none');
        document.getElementById('empty-legacy').classList.remove('d-none');
        document.getElementById('empty-sparta').classList.remove('d-none');
    });
});
</script>
