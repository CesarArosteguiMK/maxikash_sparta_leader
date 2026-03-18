<?php
$funciones = isset($funciones) ? $funciones : [];
$tiposEstadisticas = isset($tiposEstadisticas) ? $tiposEstadisticas : [];
$panelesAdmin = isset($panelesAdmin) ? $panelesAdmin : [];
?>
<style>
.ctp-tab-pane { display: none; }
.ctp-tab-pane.active { display: block; }
.ctp-nav-tabs .nav-link { font-weight: 600; }
.ctp-nav-tabs .nav-link.active { border-bottom-color: #fff !important; }
/* Panel Estadísticas = identidad propia, como otro módulo */
#ctp-panel-estadisticas {
    background: linear-gradient(180deg, #f1f8f4 0%, #fff 100%);
    border: 1px solid #c8e6c9;
    border-radius: 12px;
    padding: 1.25rem;
    margin-top: 0.5rem;
}
#ctp-panel-estadisticas .ctp-panel-estadisticas-titulo {
    font-size: 1rem;
    font-weight: 600;
    color: #1B5E20;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #81c784;
}
#ctp-panel-estadisticas .ctp-card-tipos { border-left-color: #43A047; }
#ctp-panel-estadisticas .ctp-header-sparta { background: #E8F5E9 !important; color: #2E7D32 !important; border-bottom-color: rgba(46, 125, 50, 0.2) !important; }
#ctp-panel-estadisticas .ctp-header-sparta h6 { color: #2E7D32 !important; }
#ctp-panel-estadisticas .ctp-header-sparta small { color: #388E3C !important; }
#ctp-panel-estadisticas #col-ctp-estadisticas { background-color: #fafdfb; }
#ctp-panel-estadisticas #col-ctp-sparta-est { background-color: #e8f5e9 !important; }
#ctp-panel-estadisticas #col-ctp-sparta-est .card-body { background-color: #e8f5e9 !important; }
</style>
<style>
/* ===== TICKET POR PUESTO — Arrastrar y soltar (como Equivalencia) ===== */
.ctp-card-tipos {
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border-left: 4px solid #8a8f9a;
}
.ctp-card-sparta {
    border-left: 4px solid #2196F3;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.ctp-header-sparta {
    background: #E3F2FD !important;
    color: #1565C0 !important;
    border: none;
    border-bottom: 1px solid rgba(33, 150, 243, 0.25);
}
.ctp-header-sparta h6 { color: #1565C0 !important; }
.ctp-header-sparta small { color: #1976D2 !important; }
#col-ctp-tipos { background-color: #f8f9fa; padding-top: 1rem; }
.ctp-tipo-row {
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    border: 1px solid #b4bac2;
    border-radius: 8px;
    background-color: #fff;
    margin-bottom: 0.75rem;
}
.ctp-tipo-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.ctp-tipo-titulo {
    cursor: pointer;
    padding: 0.35rem 0;
    user-select: none;
}
.ctp-tipo-chevron {
    transition: transform 0.25s ease;
    color: #8a8f9a;
    font-size: 0.75rem;
}
.ctp-tipo-row.ctp-tipo-expanded .ctp-tipo-chevron,
body.ctp-dragging-puesto .ctp-tipo-row:hover .ctp-tipo-chevron {
    transform: rotate(180deg);
    color: #5a5fd6;
}
.ctp-drop-wrap {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.35s ease;
}
.ctp-tipo-row.ctp-tipo-expanded .ctp-drop-wrap,
body.ctp-dragging-puesto .ctp-tipo-row:hover .ctp-drop-wrap {
    max-height: 900px;
}
.ctp-drop-zone {
    min-height: 52px;
    border: 2px dashed #b4bac2;
    border-radius: 8px;
    background-color: #fafbfc;
    transition: border-color 0.25s ease, background-color 0.25s ease;
    list-style: none;
    padding: 0.5rem;
    margin: 0.5rem 0 0 0;
}
.ctp-drop-zone.drag-over {
    border-color: #696cff;
    background-color: rgba(105, 108, 255, 0.06);
}
.ctp-item-sparta,
.ctp-drop-zone .list-group-item {
    transition: box-shadow 0.25s ease, background-color 0.25s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    border: 1px solid #b4bac2;
    border-radius: 6px;
    cursor: grab;
}
.ctp-item-sparta:hover,
.ctp-drop-zone .list-group-item:hover {
    background-color: #f0f2f4;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
}
.ctp-item-sparta:active,
.ctp-drop-zone .list-group-item:active { cursor: grabbing; }
.ctp-usuario-hidden { display: none !important; }
.ctp-puesto-hidden { display: none !important; }
.ctp-dragging {
    opacity: 0.95;
    box-shadow: 0 12px 28px rgba(0,0,0,0.2);
    border-radius: 8px;
    cursor: grabbing !important;
    z-index: 9999;
}
.sortable-ghost { opacity: 0.4; }
.ctp-remove-wrap {
    margin-left: auto;
    padding-left: 0.75rem;
    border-left: 1px solid rgba(0,0,0,0.08);
    flex-shrink: 0;
}
.ctp-btn-quitar {
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
.ctp-btn-quitar:hover {
    background: rgba(210, 53, 69, 0.12);
    color: #d33545;
}
.ctp-min-h { min-height: 320px; }
#col-ctp-sparta .card-body,
#col-ctp-sparta {
    background-color: #dce9f5 !important;
    border-radius: 0 0 0.375rem 0.375rem;
}
#lista-ctp-sparta { background-color: transparent !important; }
@media (max-width: 768px) { .ctp-min-h { min-height: 220px; } }
</style>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title text-primary mb-2">
            <i class="fa-solid fa-list-check me-2"></i>Asignación por puestos
        </h5>
        <p class="text-muted mb-0">
            Elija la opción que desea configurar. Arrastre puestos desde la columna derecha y suelte en el tipo correspondiente. Se guarda automáticamente.
        </p>
        <ul class="nav nav-tabs ctp-nav-tabs mt-3" role="tablist">
            <li class="nav-item"><a class="nav-link active" href="#" data-ctp-tab="ticket" role="tab"><i class="fa-solid fa-ticket me-1"></i>Ticket por puesto</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-ctp-tab="estadisticas" role="tab"><i class="fa-solid fa-chart-column me-1"></i>Estadísticas por puesto</a></li>
            <li class="nav-item"><a class="nav-link" href="#" data-ctp-tab="panelusuario" role="tab"><i class="fa-solid fa-user-shield me-1"></i>Panel por usuario</a></li>
        </ul>
    </div>
</div>

<div id="ctp-panel-ticket" class="ctp-tab-pane active">
    <div class="row g-4">
    <!-- Columna izquierda: Tipos de ticket -->
    <div class="col-lg-6">
        <div class="card ctp-card-tipos shadow-sm h-100">
            <div class="card-header bg-label-primary">
                <h6 class="mb-0 text-primary">
                    <i class="fa-solid fa-tags me-2"></i>Tipos de ticket
                </h6>
                <small class="text-muted">Suelte aquí los puestos que podrán levantar este tipo. Se guarda automáticamente al asignar o quitar.</small>
            </div>
            <div class="card-body overflow-auto ctp-min-h" id="col-ctp-tipos">
                <div id="loader-ctp-tipos" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i>
                    <p class="mb-0">Cargando...</p>
                </div>
                <div id="lista-ctp-tipos" class="d-none"></div>
            </div>
        </div>
    </div>

    <!-- Columna derecha: Puestos Sparta -->
    <div class="col-lg-6">
        <div class="card ctp-card-sparta shadow-sm h-100">
            <div class="card-header ctp-header-sparta">
                <h6 class="mb-0">
                    <i class="fa-solid fa-shield-halved me-2"></i>Puestos Sparta
                </h6>
                <small>Arrastre un puesto hacia un tipo de ticket. Puede buscar por puesto o departamento. Cada puesto solo puede aparecer una vez por módulo.</small>
            </div>
            <div class="card-body overflow-auto ctp-min-h" id="col-ctp-sparta">
                <div id="loader-ctp-sparta" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2"></i>
                    <p class="mb-0">Cargando puestos...</p>
                </div>
                <div class="mb-3 d-none" id="wrap-ctp-buscar-puesto">
                    <input type="text" class="form-control form-control-sm" id="ctp-buscar-puesto" placeholder="Buscar por puesto o departamento..." autocomplete="off">
                </div>
                <ul id="lista-ctp-sparta" class="list-group list-group-flush d-none"></ul>
                <div id="empty-ctp-sparta" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 opacity-50"></i>
                    <p class="mb-0">No hay puestos.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div id="ctp-panel-estadisticas" class="ctp-tab-pane">
    <p class="ctp-panel-estadisticas-titulo"><i class="fa-solid fa-chart-pie me-2"></i>Tableros de estadísticas por puesto</p>
    <p class="text-muted small mb-3">Define qué puestos pueden ver cada tablero de estadísticas. Es independiente de la configuración de tickets.</p>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card ctp-card-tipos shadow-sm h-100">
                <div class="card-header bg-label-primary">
                    <h6 class="mb-0 text-success"><i class="fa-solid fa-chart-column me-2"></i>Tableros disponibles</h6>
                    <small class="text-muted">Suelte aquí los puestos que tendrán acceso a este tablero.</small>
                </div>
                <div class="card-body overflow-auto ctp-min-h" id="col-ctp-estadisticas">
                    <div id="lista-ctp-estadisticas" class="d-none"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card ctp-card-sparta shadow-sm h-100">
                <div class="card-header ctp-header-sparta">
                    <h6 class="mb-0"><i class="fa-solid fa-shield-halved me-2"></i>Puestos Sparta</h6>
                    <small>Arrastre un puesto hacia el tablero que desee asignar. Puede buscar por puesto o departamento.</small>
                </div>
                <div class="card-body overflow-auto ctp-min-h" id="col-ctp-sparta-est">
                    <div class="mb-3 d-none" id="wrap-ctp-buscar-puesto-est">
                        <input type="text" class="form-control form-control-sm" id="ctp-buscar-puesto-est" placeholder="Buscar por puesto o departamento..." autocomplete="off">
                    </div>
                    <ul id="lista-ctp-sparta-estadisticas" class="list-group list-group-flush d-none"></ul>
                    <div id="empty-ctp-sparta-est" class="text-center py-5 text-muted d-none"><i class="fa-solid fa-inbox fa-2x mb-2 opacity-50"></i><p class="mb-0">No hay puestos.</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="ctp-panel-panelusuario" class="ctp-tab-pane">
    <p class="ctp-panel-estadisticas-titulo" style="border-bottom-color: #5e35b1; color: #4527a0;"><i class="fa-solid fa-user-shield me-2"></i>Paneles admin por usuario</p>
    <p class="text-muted small mb-3">Asigne usuarios a cada panel. Arrastre un usuario desde la columna derecha y suelte en el panel correspondiente. Se guarda automáticamente.</p>
    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card ctp-card-tipos shadow-sm h-100" style="border-left-color: #7e57c2;">
                <div class="card-header bg-label-primary">
                    <h6 class="mb-0" style="color: #5e35b1;"><i class="fa-solid fa-table-cells me-2"></i>Paneles admin</h6>
                    <small class="text-muted">Suelte aquí los usuarios que podrán ver este panel.</small>
                </div>
                <div class="card-body overflow-auto ctp-min-h" id="col-ctp-paneles">
                    <div id="loader-ctp-paneles" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando paneles...</div>
                    <div id="lista-ctp-paneles" class="d-none"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card ctp-card-sparta shadow-sm h-100" style="border-left-color: #5e35b1;">
                <div class="card-header ctp-header-sparta" style="background: #EDE7F6 !important; color: #4527a0 !important;">
                    <h6 class="mb-0"><i class="fa-solid fa-users me-2"></i>Usuarios</h6>
                    <small>Arrastre un usuario hacia el panel que desee asignar. Puede buscar por nombre o usuario.</small>
                </div>
                <div class="card-body overflow-auto ctp-min-h">
                    <div class="mb-3">
                        <input type="text" class="form-control form-control-sm" id="ctp-buscar-usuario" placeholder="Buscar por nombre o usuario..." autocomplete="off">
                    </div>
                    <div id="loader-ctp-usuarios" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando usuarios...</div>
                    <ul id="lista-ctp-usuarios" class="list-group list-group-flush d-none"></ul>
                    <div id="empty-ctp-usuarios" class="text-center py-5 text-muted d-none"><i class="fa-solid fa-inbox fa-2x mb-2 opacity-50"></i><p class="mb-0">No hay usuarios.</p></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
function initConfigTicketPuesto() {
    var rootGuard = document.getElementById('ctp-panel-ticket');
    if (!rootGuard) return;
    if (rootGuard.getAttribute('data-ctp-init') === '1') return;
    rootGuard.setAttribute('data-ctp-init', '1');
    const GROUP_CTP = 'ctp';
    const GROUP_CEP = 'cep';
    const GROUP_CPU = 'cpu';
    var puestos = [];
    var funciones = <?php echo json_encode($funciones); ?>;
    var tiposEstadisticas = <?php echo json_encode($tiposEstadisticas); ?>;
    var panelesAdmin = <?php echo json_encode($panelesAdmin); ?>;
    var usuariosPanel = [];
    var sortableTipos = [];
    var sortableEstadisticas = [];
    var sortablePaneles = [];

    function activarPestaña(t) {
        t = t || 'ticket';
        if (t !== 'ticket' && t !== 'estadisticas' && t !== 'panelusuario') t = 'ticket';
        document.querySelectorAll('.ctp-tab-pane').forEach(function(p) { p.classList.remove('active'); });
        document.querySelectorAll('.ctp-nav-tabs .nav-link').forEach(function(n) { n.classList.remove('active'); });
        var panel = document.getElementById('ctp-panel-' + t);
        if (panel) panel.classList.add('active');
        var link = document.querySelector('.ctp-nav-tabs [data-ctp-tab="' + t + '"]');
        if (link) link.classList.add('active');
        if (t === 'panelusuario' && window.ctpFiltrarUsuariosPanel) {
            window.ctpFiltrarUsuariosPanel();
        }
        if (t === 'ticket') ctpFiltrarPuestosTicket();
        if (t === 'estadisticas') ctpFiltrarPuestosEstadisticas();
        var nuevoHash = (t === 'estadisticas' ? 'estadisticas' : (t === 'panelusuario' ? 'panel-usuario' : ''));
        if (window.location.hash.replace('#', '') !== nuevoHash) {
            if (nuevoHash) {
                window.history.replaceState(null, '', window.location.pathname + '#' + nuevoHash);
            } else {
                window.history.replaceState(null, '', window.location.pathname + window.location.search);
            }
        }
    }

    document.querySelectorAll('[data-ctp-tab]').forEach(function(a) {
        a.addEventListener('click', function(e) {
            e.preventDefault();
            var t = e.currentTarget.getAttribute('data-ctp-tab');
            activarPestaña(t);
        });
    });

    window.addEventListener('hashchange', function() {
        var tab = (window.location.hash || '').replace('#', '').toLowerCase();
        if (tab === 'estadisticas') activarPestaña('estadisticas');
        else if (tab === 'panel-usuario') activarPestaña('panelusuario');
        else activarPestaña('ticket');
    });

    /* Al cargar o F5: siempre mostrar la primera pestaña (Ticket por puesto) y quitar el hash de la URL */
    activarPestaña('ticket');

    function ctpNormalizarTexto(valor) {
        var txt = (valor || '').toString().toLowerCase().replace(/\s+/g, ' ').trim();
        try { txt = txt.normalize('NFD').replace(/[\u0300-\u036f]/g, ''); } catch (e) {}
        return txt;
    }

    function ctpFiltrarUsuariosPanel() {
        var input = document.getElementById('ctp-buscar-usuario');
        var lista = document.getElementById('lista-ctp-usuarios');
        if (!input || !lista) return;

        var term = ctpNormalizarTexto(input.value);
        var items = lista.querySelectorAll('li');
        items.forEach(function(li) {
            var nombre = ctpNormalizarTexto(li.getAttribute('data-persona-nombre'));
            var user = ctpNormalizarTexto(li.getAttribute('data-persona-user'));
            var texto = ctpNormalizarTexto(li.textContent);
            var match = term === '' || texto.indexOf(term) !== -1 || nombre.indexOf(term) !== -1 || user.indexOf(term) !== -1;
            li.classList.toggle('ctp-usuario-hidden', !match);
        });
    }

    window.ctpFiltrarUsuariosPanel = ctpFiltrarUsuariosPanel;

    function ctpFiltrarPuestosTicket() {
        var input = document.getElementById('ctp-buscar-puesto');
        var lista = document.getElementById('lista-ctp-sparta');
        if (!input || !lista) return;
        var term = ctpNormalizarTexto(input.value);
        var items = lista.querySelectorAll('li[data-puesto-id]');
        items.forEach(function(li) {
            var nombre = ctpNormalizarTexto(li.getAttribute('data-puesto-nombre'));
            var depto = ctpNormalizarTexto(li.getAttribute('data-departamento'));
            var texto = ctpNormalizarTexto(li.textContent);
            var match = term === '' || nombre.indexOf(term) !== -1 || depto.indexOf(term) !== -1 || texto.indexOf(term) !== -1;
            li.classList.toggle('ctp-puesto-hidden', !match);
        });
    }

    function ctpFiltrarPuestosEstadisticas() {
        var input = document.getElementById('ctp-buscar-puesto-est');
        var lista = document.getElementById('lista-ctp-sparta-estadisticas');
        if (!input || !lista) return;
        var term = ctpNormalizarTexto(input.value);
        var items = lista.querySelectorAll('li[data-puesto-id]');
        items.forEach(function(li) {
            var nombre = ctpNormalizarTexto(li.getAttribute('data-puesto-nombre'));
            var depto = ctpNormalizarTexto(li.getAttribute('data-departamento'));
            var texto = ctpNormalizarTexto(li.textContent);
            var match = term === '' || nombre.indexOf(term) !== -1 || depto.indexOf(term) !== -1 || texto.indexOf(term) !== -1;
            li.classList.toggle('ctp-puesto-hidden', !match);
        });
    }

    document.addEventListener('input', function(e) {
        if (e.target && e.target.id === 'ctp-buscar-usuario') ctpFiltrarUsuariosPanel();
        if (e.target && e.target.id === 'ctp-buscar-puesto') ctpFiltrarPuestosTicket();
        if (e.target && e.target.id === 'ctp-buscar-puesto-est') ctpFiltrarPuestosEstadisticas();
    });
    document.addEventListener('keyup', function(e) {
        if (e.target && e.target.id === 'ctp-buscar-usuario') ctpFiltrarUsuariosPanel();
        if (e.target && e.target.id === 'ctp-buscar-puesto') ctpFiltrarPuestosTicket();
        if (e.target && e.target.id === 'ctp-buscar-puesto-est') ctpFiltrarPuestosEstadisticas();
    });

    if (typeof $ !== 'undefined') {
        $(document).off('input.ctpSearch keyup.ctpSearch', '#ctp-buscar-usuario');
        $(document).on('input.ctpSearch keyup.ctpSearch', '#ctp-buscar-usuario', function() {
            ctpFiltrarUsuariosPanel();
        });
        $(document).off('input.ctpSearchPuesto keyup.ctpSearchPuesto', '#ctp-buscar-puesto');
        $(document).on('input.ctpSearchPuesto keyup.ctpSearchPuesto', '#ctp-buscar-puesto', function() {
            ctpFiltrarPuestosTicket();
        });
        $(document).off('input.ctpSearchPuestoEst keyup.ctpSearchPuestoEst', '#ctp-buscar-puesto-est');
        $(document).on('input.ctpSearchPuestoEst keyup.ctpSearchPuestoEst', '#ctp-buscar-puesto-est', function() {
            ctpFiltrarPuestosEstadisticas();
        });
    }

    document.getElementById('col-ctp-tipos').addEventListener('click', function(e) {
        var tit = e.target.closest('.ctp-tipo-titulo');
        if (!tit) return;
        var row = tit.closest('.ctp-tipo-row');
        if (row) row.classList.toggle('ctp-tipo-expanded');
    });
    document.getElementById('col-ctp-estadisticas').addEventListener('click', function(e) {
        var tit = e.target.closest('.ctp-tipo-titulo');
        if (!tit) return;
        var row = tit.closest('.ctp-tipo-row');
        if (row) row.classList.toggle('ctp-tipo-expanded');
    });
    var colPaneles = document.getElementById('col-ctp-paneles');
    if (colPaneles) {
        colPaneles.addEventListener('click', function(e) {
            var tit = e.target.closest('.ctp-tipo-titulo');
            if (!tit) return;
            var row = tit.closest('.ctp-tipo-row');
            if (row) row.classList.toggle('ctp-tipo-expanded');
        });
    }

    function esc(s) {
        if (s == null) return '';
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function crearLiSparta(s) {
        var li = document.createElement('li');
        li.className = 'list-group-item d-flex align-items-center py-2 ctp-item-sparta';
        li.setAttribute('data-puesto-id', s.id);
        li.setAttribute('data-puesto-nombre', s.nombre || '');
        li.setAttribute('data-departamento', s.departamento_nombre || '');
        li.innerHTML =
            '<span class="avatar avatar-sm me-2 rounded bg-label-secondary"><i class="fa-solid fa-user-tie"></i></span>' +
            '<div class="flex-grow-1">' +
            '  <span class="fw-medium">' + esc(s.nombre) + '</span>' +
            (s.departamento_nombre ? '<br><small class="text-muted">' + esc(s.departamento_nombre) + '</small>' : '') +
            '</div>' +
            '<i class="fa-solid fa-grip-vertical text-muted"></i>';
        return li;
    }

    function renderColumnaTipos() {
        var cont = document.getElementById('lista-ctp-tipos');
        var loader = document.getElementById('loader-ctp-tipos');
        loader.classList.add('d-none');
        cont.classList.remove('d-none');
        cont.innerHTML = '';
        Object.keys(funciones).forEach(function(funcionKey) {
            var info = funciones[funcionKey];
            var row = document.createElement('div');
            row.className = 'card ctp-tipo-row';
            row.setAttribute('data-funcion', funcionKey);
            row.innerHTML =
                '<div class="card-body py-2">' +
                '  <div class="d-flex align-items-center justify-content-between ctp-tipo-titulo" title="Clic para desplegar. Al arrastrar, pase el mouse para desplegar.">' +
                '    <span class="fw-bold">' + esc(info.label) + '</span>' +
                '    <span><i class="fa-solid ' + esc(info.icon) + ' me-1 text-muted"></i><i class="fa-solid fa-chevron-down ctp-tipo-chevron"></i></span>' +
                '  </div>' +
                '  <div class="ctp-drop-wrap">' +
                '    <ul class="list-group list-group-flush ctp-drop-zone border rounded p-1 mt-2" data-funcion="' + esc(funcionKey) + '"></ul>' +
                '  </div>' +
                '</div>';
            cont.appendChild(row);
        });
        initSortableTipos();
    }

    function renderColumnaSparta() {
        var ul = document.getElementById('lista-ctp-sparta');
        var empty = document.getElementById('empty-ctp-sparta');
        var loader = document.getElementById('loader-ctp-sparta');
        var wrapBuscar = document.getElementById('wrap-ctp-buscar-puesto');
        loader.classList.add('d-none');
        ul.classList.remove('d-none');
        ul.innerHTML = '';
        if (!puestos.length) {
            if (wrapBuscar) wrapBuscar.classList.add('d-none');
            empty.classList.remove('d-none');
            return;
        }
        empty.classList.add('d-none');
        if (wrapBuscar) wrapBuscar.classList.remove('d-none');
        puestos.forEach(function(s) {
            ul.appendChild(crearLiSparta(s));
        });
        initSortableSparta();
        ctpFiltrarPuestosTicket();
    }

    function initSortableSparta() {
        var el = document.getElementById('lista-ctp-sparta');
        if (!el) return;
        if (el._sortable) { el._sortable.destroy(); el._sortable = null; }
        el._sortable = new Sortable(el, {
            group: { name: GROUP_CTP, pull: 'clone', put: true },
            sort: false,
            animation: 220,
            forceFallback: true,
            fallbackOnBody: true,
            ghostClass: 'sortable-ghost',
            chosenClass: 'ctp-dragging',
            dragClass: 'ctp-dragging',
            filter: '.ctp-remove-wrap',
            preventOnFilter: true,
            onStart: function() { document.body.classList.add('ctp-dragging-puesto'); },
            onEnd: function() { document.body.classList.remove('ctp-dragging-puesto'); }
        });
    }

    function initSortableTipos() {
        sortableTipos.forEach(function(s) { if (s && s.destroy) s.destroy(); });
        sortableTipos = [];
        document.querySelectorAll('#ctp-panel-ticket .ctp-drop-zone').forEach(function(ul) {
            var sortable = new Sortable(ul, {
                group: { name: GROUP_CTP, put: true, pull: true },
                animation: 220,
                forceFallback: true,
                fallbackOnBody: true,
                ghostClass: 'sortable-ghost',
                filter: '.ctp-remove-wrap',
                preventOnFilter: true,
                onAdd: function(evt) {
                    var item = evt.item;
                    var funcion = ul.getAttribute('data-funcion');
                    var idPuesto = item.getAttribute('data-puesto-id');
                    if (!idPuesto) return;
                    var yaEnLista = Array.prototype.slice.call(ul.querySelectorAll('[data-puesto-id]')).some(function(li) { return li !== item && li.getAttribute('data-puesto-id') === idPuesto; });
                    if (yaEnLista) {
                        item.remove();
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Puesto duplicado', text: 'Ese puesto ya está asignado a este módulo. Solo puede aparecer una vez por módulo.' });
                        return;
                    }
                    addRemoveButton(item, funcion);
                    guardarAuto();
                },
                onRemove: function() { guardarAuto(); }
            });
            sortableTipos.push(sortable);
        });
    }

    function addRemoveButton(item, funcion) {
        if (item.querySelector('.ctp-remove-wrap')) return;
        var wrap = document.createElement('div');
        wrap.className = 'ctp-remove-wrap';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ctp-btn-quitar';
        btn.title = 'Quitar';
        btn.setAttribute('aria-label', 'Quitar');
        btn.innerHTML = '<i class="fa-solid fa-times"></i>';
        btn.onclick = function(e) {
            e.stopPropagation();
            item.remove();
            guardarAuto();
        };
        wrap.appendChild(btn);
        item.classList.add('d-flex', 'align-items-center');
        item.appendChild(wrap);
    }

    function recogerConfig() {
        var pares = [];
        document.querySelectorAll('#ctp-panel-ticket .ctp-drop-zone').forEach(function(ul) {
            var funcion = ul.getAttribute('data-funcion');
            ul.querySelectorAll('[data-puesto-id]').forEach(function(item) {
                var idPuesto = item.getAttribute('data-puesto-id');
                if (idPuesto && funcion) pares.push({ id_puesto: parseInt(idPuesto, 10), funcion_ticket: funcion });
            });
        });
        return pares;
    }

    function aplicarConfigGuardada(config) {
        var vistos = {};
        (config || []).forEach(function(d) {
            var key = (d.id_puesto || '') + '-' + (d.funcion_ticket || '');
            if (vistos[key]) return;
            vistos[key] = true;
            var ul = document.querySelector('#ctp-panel-ticket .ctp-drop-zone[data-funcion="' + d.funcion_ticket + '"]');
            if (!ul) return;
            var s = puestos.find(function(p) { return String(p.id) === String(d.id_puesto); });
            if (!s) return;
            var li = crearLiSparta(s);
            addRemoveButton(li, d.funcion_ticket);
            ul.appendChild(li);
        });
        initSortableTipos();
    }

    var guardarAutoTimer = null;
    function guardarAuto() {
        var pares = recogerConfig();
        if (guardarAutoTimer) clearTimeout(guardarAutoTimer);
        guardarAutoTimer = setTimeout(function() {
            guardarAutoTimer = null;
            if (typeof http === 'undefined') return;
            http.request({
                endpoint: '/configticketpuesto/guardar',
                method: 'POST',
                data: JSON.stringify({ config: pares }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(resp) {
                    if (resp && resp.success && typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Guardado', text: 'Configuración actualizada.', timer: 1500, showConfirmButton: false });
                    }
                }
            });
        }, 300);
    }

    function renderColumnaTiposEstadisticas() {
        var cont = document.getElementById('lista-ctp-estadisticas');
        cont.classList.remove('d-none');
        cont.innerHTML = '';
        if (!Object.keys(tiposEstadisticas || {}).length) return;
        Object.keys(tiposEstadisticas).forEach(function(tipoKey) {
            var info = tiposEstadisticas[tipoKey];
            var row = document.createElement('div');
            row.className = 'card ctp-tipo-row';
            row.setAttribute('data-tipo', tipoKey);
            row.innerHTML = '<div class="card-body py-2">' +
                '<div class="d-flex align-items-center justify-content-between ctp-tipo-titulo" title="Clic para desplegar. Al arrastrar, pase el mouse para desplegar.">' +
                '<span class="fw-bold">' + esc(info.label) + '</span>' +
                '<span><i class="fa-solid ' + esc(info.icon) + ' me-1 text-muted"></i><i class="fa-solid fa-chevron-down ctp-tipo-chevron"></i></span></div>' +
                '<div class="ctp-drop-wrap">' +
                '<ul class="list-group list-group-flush ctp-drop-zone cep-drop-zone border rounded p-1 mt-2" data-tipo="' + esc(tipoKey) + '"></ul>' +
                '</div></div>';
            cont.appendChild(row);
        });
        initSortableEstadisticas();
    }

    function initSortableSpartaEstadisticas() {
        var el = document.getElementById('lista-ctp-sparta-estadisticas');
        if (!el) return;
        if (el._sortable) { el._sortable.destroy(); el._sortable = null; }
        el._sortable = new Sortable(el, {
            group: { name: GROUP_CEP, pull: 'clone', put: true },
            sort: false,
            animation: 220,
            forceFallback: true,
            fallbackOnBody: true,
            ghostClass: 'sortable-ghost',
            chosenClass: 'ctp-dragging',
            filter: '.ctp-remove-wrap',
            onStart: function() { document.body.classList.add('ctp-dragging-puesto'); },
            onEnd: function() { document.body.classList.remove('ctp-dragging-puesto'); }
        });
    }

    function initSortableEstadisticas() {
        sortableEstadisticas.forEach(function(s) { if (s && s.destroy) s.destroy(); });
        sortableEstadisticas = [];
        document.querySelectorAll('.cep-drop-zone').forEach(function(ul) {
            var sortable = new Sortable(ul, {
                group: { name: GROUP_CEP, put: true, pull: true },
                animation: 220,
                forceFallback: true,
                ghostClass: 'sortable-ghost',
                filter: '.ctp-remove-wrap',
                onAdd: function(evt) {
                    var item = evt.item;
                    var tipo = ul.getAttribute('data-tipo');
                    var idPuesto = item.getAttribute('data-puesto-id');
                    if (!idPuesto) return;
                    var yaEnLista = Array.prototype.slice.call(ul.querySelectorAll('[data-puesto-id]')).some(function(li) { return li !== item && li.getAttribute('data-puesto-id') === idPuesto; });
                    if (yaEnLista) {
                        item.remove();
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Puesto duplicado', text: 'Ese puesto ya está asignado a este tipo. Solo puede aparecer una vez por tipo.' });
                        return;
                    }
                    addRemoveButtonEstadisticas(item, tipo);
                    guardarAutoEstadisticas();
                },
                onRemove: function() { guardarAutoEstadisticas(); }
            });
            sortableEstadisticas.push(sortable);
        });
    }

    function addRemoveButtonEstadisticas(item, tipo) {
        if (item.querySelector('.ctp-remove-wrap')) return;
        var wrap = document.createElement('div');
        wrap.className = 'ctp-remove-wrap';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ctp-btn-quitar';
        btn.title = 'Quitar';
        btn.innerHTML = '<i class="fa-solid fa-times"></i>';
        btn.onclick = function(e) { e.stopPropagation(); item.remove(); guardarAutoEstadisticas(); };
        wrap.appendChild(btn);
        item.classList.add('d-flex', 'align-items-center');
        item.appendChild(wrap);
    }

    function recogerConfigEstadisticas() {
        var pares = [];
        document.querySelectorAll('.cep-drop-zone').forEach(function(ul) {
            var tipo = ul.getAttribute('data-tipo');
            ul.querySelectorAll('[data-puesto-id]').forEach(function(item) {
                var idPuesto = item.getAttribute('data-puesto-id');
                if (idPuesto && tipo) pares.push({ id_puesto: parseInt(idPuesto, 10), tipo_estadistica: tipo });
            });
        });
        return pares;
    }

    function aplicarConfigEstadisticasGuardada(config) {
        var vistos = {};
        (config || []).forEach(function(d) {
            var key = (d.id_puesto || '') + '-' + (d.tipo_estadistica || '');
            if (vistos[key]) return;
            vistos[key] = true;
            var ul = document.querySelector('.cep-drop-zone[data-tipo="' + d.tipo_estadistica + '"]');
            if (!ul) return;
            var s = puestos.find(function(p) { return String(p.id) === String(d.id_puesto); });
            if (!s) return;
            var li = crearLiSparta(s);
            addRemoveButtonEstadisticas(li, d.tipo_estadistica);
            ul.appendChild(li);
        });
        initSortableEstadisticas();
    }

    var guardarAutoEstadisticasTimer = null;
    function guardarAutoEstadisticas() {
        var pares = recogerConfigEstadisticas();
        if (guardarAutoEstadisticasTimer) clearTimeout(guardarAutoEstadisticasTimer);
        guardarAutoEstadisticasTimer = setTimeout(function() {
            guardarAutoEstadisticasTimer = null;
            if (typeof http === 'undefined') return;
            http.request({
                endpoint: '/configticketpuesto/guardarEstadisticas',
                method: 'POST',
                data: JSON.stringify({ config: pares }),
                contentType: 'application/json',
                processData: false,
                showLoader: false,
                onSuccess: function(resp) {
                    if (resp && resp.success && typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Guardado', text: 'Estadísticas actualizadas.', timer: 1500, showConfirmButton: false });
                }
            });
        }, 300);
    }

    function crearLiUsuario(u) {
        var li = document.createElement('li');
        li.className = 'list-group-item d-flex align-items-center py-2 ctp-item-sparta ctp-item-usuario';
        li.setAttribute('data-persona-id', u.id);
        li.setAttribute('data-persona-nombre', (u.nombre || ''));
        li.setAttribute('data-persona-user', (u.user_name || ''));
        li.innerHTML = '<span class="avatar avatar-sm me-2 rounded bg-label-secondary"><i class="fa-solid fa-user"></i></span>' +
            '<div class="flex-grow-1"><span class="fw-medium">' + esc(u.nombre || '') + '</span>' +
            (u.user_name ? '<br><small class="text-muted">' + esc(u.user_name) + '</small>' : '') + '</div>' +
            '<i class="fa-solid fa-grip-vertical text-muted"></i>';
        return li;
    }

    function renderColumnaPaneles() {
        var cont = document.getElementById('lista-ctp-paneles');
        cont.classList.remove('d-none');
        cont.innerHTML = '';
        if (!Object.keys(panelesAdmin || {}).length) return;
        Object.keys(panelesAdmin).forEach(function(clave) {
            var info = panelesAdmin[clave];
            var row = document.createElement('div');
            row.className = 'card ctp-tipo-row';
            row.setAttribute('data-clave', clave);
            row.innerHTML = '<div class="card-body py-2">' +
                '<div class="d-flex align-items-center justify-content-between ctp-tipo-titulo" title="Clic para desplegar. Al arrastrar, pase el mouse para desplegar.">' +
                '<span class="fw-bold">' + esc(info.label) + '</span>' +
                '<span><i class="' + esc(info.icon) + ' me-1 text-muted"></i><i class="fa-solid fa-chevron-down ctp-tipo-chevron"></i></span></div>' +
                '<div class="ctp-drop-wrap">' +
                '<ul class="list-group list-group-flush cpu-drop-zone border rounded p-1 mt-2" data-clave-panel="' + esc(clave) + '"></ul>' +
                '</div></div>';
            cont.appendChild(row);
        });
        initSortablePaneles();
    }

    function initSortableUsuariosPanel() {
        var el = document.getElementById('lista-ctp-usuarios');
        if (!el) return;
        if (el._sortable) { el._sortable.destroy(); el._sortable = null; }
        el._sortable = new Sortable(el, {
            group: { name: GROUP_CPU, pull: 'clone', put: true },
            sort: false,
            animation: 220,
            forceFallback: true,
            fallbackOnBody: true,
            ghostClass: 'sortable-ghost',
            chosenClass: 'ctp-dragging',
            dragClass: 'ctp-dragging',
            filter: '.ctp-remove-wrap',
            onStart: function() { document.body.classList.add('ctp-dragging-puesto'); },
            onEnd: function() { document.body.classList.remove('ctp-dragging-puesto'); }
        });
    }

    function initSortablePaneles() {
        sortablePaneles.forEach(function(s) { if (s && s.destroy) s.destroy(); });
        sortablePaneles = [];
        document.querySelectorAll('.cpu-drop-zone').forEach(function(ul) {
            var sortable = new Sortable(ul, {
                group: { name: GROUP_CPU, put: true, pull: true },
                animation: 220,
                forceFallback: true,
                ghostClass: 'sortable-ghost',
                filter: '.ctp-remove-wrap',
                onAdd: function(evt) {
                    try {
                        var item = evt.item;
                        var clave = ul.getAttribute('data-clave-panel');
                        var idPersona = item.getAttribute('data-persona-id');
                        if (!item || !clave || !idPersona) return;
                        var yaEnLista = Array.prototype.slice.call(ul.querySelectorAll('[data-persona-id]')).some(function(li) { return li !== item && li.getAttribute('data-persona-id') === idPersona; });
                        if (yaEnLista) {
                            item.remove();
                            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Usuario duplicado', text: 'Ese usuario ya está asignado a este panel. Solo puede aparecer una vez por panel.' });
                            return;
                        }
                        setTimeout(function() {
                            try {
                                addRemoveButtonPanelUsuario(item, clave);
                                guardarAutoPanelUsuario();
                            } catch (err) { console.warn('Panel usuario onAdd:', err); }
                        }, 0);
                    } catch (err) { console.warn('Panel usuario onAdd:', err); }
                },
                onRemove: function() {
                    try { guardarAutoPanelUsuario(); } catch (err) { console.warn('Panel usuario onRemove:', err); }
                }
            });
            sortablePaneles.push(sortable);
        });
    }

    function addRemoveButtonPanelUsuario(item, clave) {
        if (item.querySelector('.ctp-remove-wrap')) return;
        var wrap = document.createElement('div');
        wrap.className = 'ctp-remove-wrap';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ctp-btn-quitar';
        btn.title = 'Quitar';
        btn.innerHTML = '<i class="fa-solid fa-times"></i>';
        btn.onclick = function(e) { e.stopPropagation(); item.remove(); guardarAutoPanelUsuario(); };
        wrap.appendChild(btn);
        item.classList.add('d-flex', 'align-items-center');
        item.appendChild(wrap);
    }

    function recogerConfigPanelUsuario() {
        var pares = [];
        document.querySelectorAll('.cpu-drop-zone').forEach(function(ul) {
            var clave = ul.getAttribute('data-clave-panel');
            ul.querySelectorAll('[data-persona-id]').forEach(function(item) {
                var idPersona = item.getAttribute('data-persona-id');
                if (idPersona && clave) pares.push({ clave_panel: clave, id_persona: parseInt(idPersona, 10) });
            });
        });
        return pares;
    }

    function aplicarConfigPanelUsuarioGuardada(config) {
        var vistos = {};
        (config || []).forEach(function(d) {
            var key = (d.clave_panel || '') + '-' + (d.id_persona || '');
            if (vistos[key]) return;
            vistos[key] = true;
            var ul = document.querySelector('.cpu-drop-zone[data-clave-panel="' + d.clave_panel + '"]');
            if (!ul) return;
            var u = usuariosPanel.find(function(p) { return String(p.id) === String(d.id_persona); });
            if (!u) return;
            var li = crearLiUsuario(u);
            addRemoveButtonPanelUsuario(li, d.clave_panel);
            ul.appendChild(li);
        });
        initSortablePaneles();
    }

    var guardarAutoPanelUsuarioTimer = null;
    function guardarAutoPanelUsuario() {
        var pares = recogerConfigPanelUsuario();
        if (guardarAutoPanelUsuarioTimer) clearTimeout(guardarAutoPanelUsuarioTimer);
        guardarAutoPanelUsuarioTimer = setTimeout(function() {
            guardarAutoPanelUsuarioTimer = null;
            try {
                if (typeof http === 'undefined') return;
                http.request({
                    endpoint: '/configticketpuesto/guardarPanelUsuario',
                    method: 'POST',
                    data: JSON.stringify({ config: pares }),
                    contentType: 'application/json',
                    processData: false,
                    showLoader: false,
                    onSuccess: function(resp) {
                        if (resp && resp.success && typeof Swal !== 'undefined') Swal.fire({ icon: 'success', title: 'Guardado', text: 'Paneles actualizados.', timer: 1500, showConfirmButton: false });
                    },
                    onError: function() {
                        if (typeof Swal !== 'undefined') Swal.fire({ icon: 'warning', title: 'Aviso', text: 'No se pudo guardar. Compruebe que la tabla config_panel_usuario exista en la base de datos.' });
                    }
                });
            } catch (err) { console.warn('guardarAutoPanelUsuario:', err); }
        }, 300);
    }

    Promise.all([
        fetch('/configticketpuesto/getPuestosSparta').then(function(r) { return r.json(); }),
        fetch('/configticketpuesto/getConfig').then(function(r) { return r.json(); }),
        fetch('/configticketpuesto/getConfigEstadisticas').then(function(r) { return r.json(); }),
        fetch('/configticketpuesto/getUsuariosPanel').then(function(r) { return r.json().then(function(d) { return d; }).catch(function() { return { success: false, datos: [] }; }); }),
        fetch('/configticketpuesto/getConfigPanelUsuario').then(function(r) { return r.json().then(function(d) { return d; }).catch(function() { return { success: false, datos: [] }; }); })
    ]).then(function(results) {
        var resPuestos = results[0], resConfig = results[1], resEst = results[2], resUsuarios = results[3], resConfigPanel = results[4];
        if (resPuestos && resPuestos.success && resPuestos.datos) puestos = resPuestos.datos;
        document.getElementById('loader-ctp-tipos').classList.add('d-none');
        renderColumnaTipos();
        renderColumnaSparta();
        var config = (resConfig && resConfig.success && resConfig.datos) ? resConfig.datos : [];
        if (config.length) aplicarConfigGuardada(config);
        renderColumnaTiposEstadisticas();
        var ulEst = document.getElementById('lista-ctp-sparta-estadisticas');
        var emptyEst = document.getElementById('empty-ctp-sparta-est');
        var wrapBuscarEst = document.getElementById('wrap-ctp-buscar-puesto-est');
        if (ulEst && puestos.length) {
            ulEst.classList.remove('d-none');
            if (emptyEst) emptyEst.classList.add('d-none');
            if (wrapBuscarEst) wrapBuscarEst.classList.remove('d-none');
            ulEst.innerHTML = '';
            puestos.forEach(function(s) { ulEst.appendChild(crearLiSparta(s)); });
            initSortableSpartaEstadisticas();
            ctpFiltrarPuestosEstadisticas();
        } else {
            if (wrapBuscarEst) wrapBuscarEst.classList.add('d-none');
            if (emptyEst) emptyEst.classList.remove('d-none');
        }
        var configEst = (resEst && resEst.success && resEst.datos) ? resEst.datos : [];
        if (configEst.length) aplicarConfigEstadisticasGuardada(configEst);
        if (resUsuarios && resUsuarios.success && resUsuarios.datos) usuariosPanel = resUsuarios.datos;
        var loaderPaneles = document.getElementById('loader-ctp-paneles');
        var loaderUsuarios = document.getElementById('loader-ctp-usuarios');
        if (loaderPaneles) loaderPaneles.classList.add('d-none');
        renderColumnaPaneles();
        var ulUsu = document.getElementById('lista-ctp-usuarios');
        var emptyUsu = document.getElementById('empty-ctp-usuarios');
        if (loaderUsuarios) loaderUsuarios.classList.add('d-none');
        if (ulUsu && usuariosPanel.length) {
            ulUsu.classList.remove('d-none');
            if (emptyUsu) emptyUsu.classList.add('d-none');
            ulUsu.innerHTML = '';
            usuariosPanel.forEach(function(u) { ulUsu.appendChild(crearLiUsuario(u)); });
            initSortableUsuariosPanel();
            if (window.ctpFiltrarUsuariosPanel) window.ctpFiltrarUsuariosPanel();
        } else if (emptyUsu) emptyUsu.classList.remove('d-none');
        var configPanel = (resConfigPanel && resConfigPanel.success && resConfigPanel.datos) ? resConfigPanel.datos : [];
        if (configPanel.length) aplicarConfigPanelUsuarioGuardada(configPanel);
    }).catch(function() {
        document.getElementById('loader-ctp-tipos').classList.add('d-none');
        document.getElementById('loader-ctp-sparta').classList.add('d-none');
        document.getElementById('lista-ctp-tipos').classList.remove('d-none');
        document.getElementById('empty-ctp-sparta').classList.remove('d-none');
        var lp = document.getElementById('loader-ctp-paneles');
        var lu = document.getElementById('loader-ctp-usuarios');
        if (lp) lp.classList.add('d-none');
        if (lu) lu.classList.add('d-none');
        renderColumnaPaneles();
        var emptyUsu = document.getElementById('empty-ctp-usuarios');
        if (emptyUsu) emptyUsu.classList.remove('d-none');
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initConfigTicketPuesto);
} else {
    initConfigTicketPuesto();
}
})();
</script>
