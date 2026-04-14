<style>
/* ══════════════════════════════════════════
   CIERRE DE CRÉDITO — ESTILOS GLOBALES
══════════════════════════════════════════ */
.cc-header-gradient {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.cc-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.cc-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.85; color: #fff; }
.cc-header-gradient i  { color: #fff; }

/* ── Pestañas ── */
.cc-nav-tabs .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.6rem 1.4rem;
}
.cc-nav-tabs .nav-link.active {
    color: #1d4ed8;
    border-bottom-color: #fff !important;
}
.cc-nav-tabs .nav-link:hover:not(.active) {
    color: #1d4ed8;
    background: #eff6ff;
}

/* ── Tabla (En Proceso) ── */
.cc-table th {
    background: #f1f5f9;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
    font-weight: 700;
}
.cc-table td { font-size: 0.88rem; vertical-align: middle; }

/* ── Badge estatus ── */
.badge-en-proceso       { background: #fef08a; color: #713f12; font-weight: 700; }
.badge-env-finalizado   { background: #bbf7d0; color: #14532d; font-weight: 700; }

/* ─────────────────────────────────────────
   CARDS DE CONVENIOS SALDADOS
───────────────────────────────────────── */
.cc-conv-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.cc-conv-card:hover { box-shadow: 0 4px 18px rgba(30,58,95,.12); }

/* Cabecera de la card */
.cc-conv-card-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.cc-conv-card-header .cc-credito-id {
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: .3px;
}
.cc-conv-card-header .cc-credito-id small {
    font-weight: 400;
    font-size: .75rem;
    opacity: .8;
    margin-left: .35rem;
}

/* Barra de progreso dentro del header */
.cc-progress-wrap {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex: 1;
    max-width: 340px;
}
.cc-progress-wrap .cc-prog-label {
    color: rgba(255,255,255,.7);
    font-size: .72rem;
    white-space: nowrap;
    min-width: 10px;
    text-align: center;
}
.cc-progress-wrap .progress {
    flex: 1;
    height: 8px;
    border-radius: 20px;
    background: rgba(255,255,255,.25);
}
.cc-progress-wrap .progress-bar {
    background: #4ade80;
    border-radius: 20px;
    transition: width .6s ease;
}

/* Cuerpo de la card */
.cc-conv-card-body {
    padding: 1.1rem 1.25rem;
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}

/* Columna derecha: detalles */
.cc-conv-details { flex: 1; min-width: 0; }
.cc-conv-details .cc-detail-row {
    display: flex;
    align-items: baseline;
    gap: .5rem;
    margin-bottom: .45rem;
    font-size: .875rem;
}
.cc-conv-details .cc-detail-row .cc-lbl {
    color: #64748b;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
    min-width: 130px;
}
.cc-conv-details .cc-detail-row .cc-val {
    color: #1e293b;
    font-weight: 500;
}

/* Resumen de aplicación incrustado */
.cc-resumen-aplicacion {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    padding: .6rem .9rem;
    margin: .5rem 0 .6rem 0;
    font-size: .82rem;
}
.cc-resumen-aplicacion .cc-res-title {
    font-weight: 700;
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #1d4ed8;
    margin-bottom: .35rem;
}
.cc-resumen-aplicacion .cc-res-row {
    display: flex;
    justify-content: space-between;
    color: #475569;
    padding: .1rem 0;
}
.cc-resumen-aplicacion .cc-res-row.total {
    border-top: 1px solid #e2e8f0;
    margin-top: .25rem;
    padding-top: .3rem;
    font-weight: 700;
    color: #1e293b;
}

/* Validación */
.cc-validacion-box {
    display: flex;
    align-items: center;
    gap: .5rem;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: .45rem;
    padding: .4rem .75rem;
    margin-top: .6rem;
    font-size: .82rem;
}
.cc-validacion-box i { color: #d97706; font-size: .85rem; }
.cc-validacion-box .cc-val-user { font-weight: 700; color: #92400e; }
body.dark-mode .cc-validacion-box {
    background: rgba(180, 83, 9, 0.15);
    border-color: rgba(217, 119, 6, 0.4);
}
body.dark-mode .cc-validacion-box i { color: #fbbf24; }
body.dark-mode .cc-validacion-box .cc-val-user { color: #fcd34d; }

/* Footer de la card con botón confirmar */
.cc-conv-card-footer {
    border-top: 1px solid #e2e8f0;
    padding: .75rem 1.25rem;
    display: flex;
    justify-content: flex-end;
    background: #f8fafc;
}
.cc-btn-confirmar {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: .85rem;
    padding: .45rem 1.4rem;
    border-radius: 2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s, transform .15s;
}
.cc-btn-confirmar:hover { opacity: .9; transform: translateY(-1px); }
.cc-btn-confirmar:active { transform: translateY(0); }
.cc-btn-confirmar:disabled { opacity: .5; cursor: not-allowed; }

/* ── Dark mode ── */
body.dark-mode .cc-nav-tabs .nav-link { color: #94a3b8; }
body.dark-mode .cc-nav-tabs .nav-link.active { color: #60a5fa; border-bottom-color: #1e293b !important; }
body.dark-mode .cc-nav-tabs .nav-link:hover:not(.active) { background: #1e3a5f; color: #60a5fa; }
body.dark-mode .cc-table th { background: #1e293b; color: #94a3b8; }
body.dark-mode .cc-conv-card { background: #1e293b; border-color: #334155; }
body.dark-mode .cc-conv-details .cc-detail-row .cc-val { color: #e2e8f0; }
body.dark-mode .cc-resumen-aplicacion { background: #0f172a; border-color: #334155; }
body.dark-mode .cc-conv-card-footer { background: #0f172a; border-color: #334155; }

/* ── Checklist de documentos (tab En Proceso) ── */
.cc-doccheck-wrap {
    background: #f8fafc;
    border-radius: .5rem;
    padding: .5rem .75rem;
    border: 1px solid #e2e8f0;
}
.cc-doccheck-title {
    font-size: .72rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: .4rem;
}
.cc-doccheck-items { display: flex; flex-wrap: wrap; gap: .4rem; }
.cc-doc-ok, .cc-doc-missing, .cc-doc-partial {
    display: inline-flex;
    align-items: center;
    font-size: .75rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}
.cc-doc-ok      { background: #dcfce7; color: #15803d; }
.cc-doc-missing { background: #fee2e2; color: #b91c1c; }
.cc-doc-partial { background: #fef9c3; color: #854d0e; }
body.dark-mode .cc-doccheck-wrap  { background: #0f172a; border-color: #334155; }
body.dark-mode .cc-doccheck-title { color: #94a3b8; }
body.dark-mode .cc-doc-ok      { background: rgba(21,128,61,.2);  color: #4ade80; }
body.dark-mode .cc-doc-missing { background: rgba(185,28,28,.2); color: #f87171; }
body.dark-mode .cc-doc-partial { background: rgba(133,77,14,.2); color: #fbbf24; }

/* ── Panel lateral derecho (Tab En Proceso) ── */
.cc-ep-wrapper {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    margin-bottom: 1.25rem;
}
.cc-side-panel {
    width: 0;
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    flex-shrink: 0;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: none;
    border-radius: 0 .75rem .75rem 0;
    box-shadow: 3px 2px 10px rgba(0,0,0,.06);
    transition: width .32s cubic-bezier(.4,0,.2,1), max-height .32s cubic-bezier(.4,0,.2,1), opacity .25s ease, padding .32s ease;
    padding: 0;
    display: flex;
    flex-direction: column;
}
.cc-side-panel.open {
    width: 540px;
    max-height: 80vh;
    overflow-y: auto;
    opacity: 1;
    padding: 1.1rem 1.25rem;
}
.cc-conv-card.cc-has-panel {
    border-radius: .75rem 0 0 .75rem;
    border-right-color: #bfdbfe;
}
.cc-side-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .85rem;
    padding-bottom: .6rem;
    border-bottom: 1px solid #e2e8f0;
    flex-shrink: 0;
}
.cc-side-panel-header .cc-sp-title {
    font-weight: 700;
    font-size: .82rem;
    color: #1d4ed8;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.cc-side-panel-close {
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    padding: 0 .25rem;
    font-size: .9rem;
    line-height: 1;
    transition: color .15s;
}
.cc-side-panel-close:hover { color: #dc2626; }
body.dark-mode .cc-side-panel {
    background: #0f172a;
    border-color: #334155;
    box-shadow: 3px 2px 10px rgba(0,0,0,.25);
}
body.dark-mode .cc-side-panel-header { border-color: #334155; }
body.dark-mode .cc-side-panel-header .cc-sp-title { color: #60a5fa; }

/* Tabla de amortización dentro del acordeón */
.cc-amort-table { width:100%; border-collapse:collapse; font-size:.8rem; }
.cc-amort-table th {
    background:#e8f0fe; color:#1e40af; font-weight:700;
    font-size:.72rem; text-transform:uppercase; letter-spacing:.03em;
    padding:.4rem .6rem; text-align:left; border-bottom:2px solid #bfdbfe;
}
.cc-amort-table td { padding:.4rem .6rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.cc-amort-table tr:last-child td { border-bottom:none; }
.cc-amort-table tr.pagada td { background: rgba(220,252,231,.35); }
.cc-amort-table tr.pendiente td { background: rgba(254,242,242,.4); }
body.dark-mode .cc-amort-table th { background:#1e3a5f; color:#93c5fd; border-color:#1e40af; }
body.dark-mode .cc-amort-table td { border-color:#1e293b; }
body.dark-mode .cc-amort-table tr.pagada td  { background: rgba(21,128,61,.12); }
body.dark-mode .cc-amort-table tr.pendiente td { background: rgba(185,28,28,.1); }

/* Botón descartar rojo visible */
.cc-btn-descartar {
    background: #dc2626;
    border: none;
    color: #fff;
    border-radius: .4rem;
    padding: .3rem .75rem;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .35rem;
    transition: background .15s, transform .1s;
    white-space: nowrap;
    flex-shrink: 0;
}
.cc-btn-descartar:hover  { background: #b91c1c; transform: translateY(-1px); }
.cc-btn-descartar:active { transform: translateY(0); }

/* ── Panel de detalle (child row DataTables) ── */
/* Panel de detalle (child row DataTables) */
.cc-conv-detail-inner { padding: 1rem 1.25rem; max-height: 520px; overflow-y: auto; }
</style>

<!-- ══════════════════════════════════════
     ENCABEZADO
══════════════════════════════════════ -->
<div class="cc-header-gradient">
    <div class="d-flex align-items-center gap-3">
        <i class="fa-solid fa-file-circle-check fa-2x opacity-90"></i>
        <div>
            <h4>Cierre de Crédito</h4>
            <p>Seguimiento al proceso de cierre final de créditos.</p>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════
     PESTAÑAS
══════════════════════════════════════ -->
<div class="card shadow-sm">
    <div class="card-body pb-0">
        <!-- Pestañas -->
        <ul class="nav nav-tabs cc-nav-tabs border-0 mb-0" id="ccTabs" role="tablist">
                <!-- Pestaña 0: Convenios (todos) -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-convenios-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-convenios"
                            type="button" role="tab"
                            aria-controls="tab-convenios" aria-selected="false">
                        <i class="fa-solid fa-handshake me-1 text-primary"></i>Convenios
                        <span class="badge bg-secondary ms-1" id="badge-convenios">0</span>
                    </button>
                </li>
                <!-- Pestaña 1: Enviados Finalizados (activa por defecto) -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-env-finalizado-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-env-finalizado"
                            type="button" role="tab"
                            aria-controls="tab-env-finalizado" aria-selected="true">
                        <i class="fa-solid fa-circle-check me-1 text-success"></i>Enviados Finalizados
                        <span class="badge bg-success ms-1" id="badge-env-finalizado">0</span>
                    </button>
                </li>
                <!-- Pestaña 2: En Proceso -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-en-proceso-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-en-proceso"
                            type="button" role="tab"
                            aria-controls="tab-en-proceso" aria-selected="false">
                        <i class="fa-solid fa-hourglass-half me-1 text-warning"></i>En Proceso
                        <span class="badge bg-warning text-dark ms-1" id="badge-en-proceso">0</span>
                    </button>
                </li>
                <!-- Pestaña 3: Historial -->
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-historial-btn"
                            data-bs-toggle="tab" data-bs-target="#tab-historial"
                            type="button" role="tab"
                            aria-controls="tab-historial" aria-selected="false">
                        <i class="fa-solid fa-clock-rotate-left me-1 text-info"></i>Historial
                    </button>
                </li>
            </ul>
    </div>
</div>

<!-- Barra de búsqueda (fuera del card de pestañas) -->
<div id="cc-search-bar" class="mt-8 d-flex justify-content-end" style="display:none;">
    <div class="input-group input-group-sm" style="max-width:380px;">
        <span class="input-group-text bg-transparent border-end-0">
            <i class="fa-solid fa-magnifying-glass text-muted" style="font-size:.8rem;"></i>
        </span>
        <input type="text" id="cc-input-buscar"
               class="form-control form-control-sm border-start-0"
               placeholder="Buscar crédito, cliente, producto..."
               autocomplete="off">
        <button type="button" class="btn btn-sm btn-outline-secondary border-start-0"
                id="cc-btn-limpiar-busqueda" title="Limpiar" style="display:none;">
            <i class="fa-solid fa-times"></i>
        </button>
    </div>
</div>

<!-- ══════════════════════════════════════
     CONTENIDO DE PESTAÑAS
══════════════════════════════════════ -->
<div class="tab-content mt-3" id="ccTabContent">

    <!-- ══ PESTAÑA 0: CONVENIOS (TODOS) ══ -->
    <div class="tab-pane fade" id="tab-convenios" role="tabpanel">
        <div id="loader-convenios" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            Cargando convenios...
        </div>
        <div id="wrap-convenios" class="card d-none">
            <div class="card-datatable table-responsive">
                <table id="tablaConveniosTodos" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th></th><!-- control responsive -->
                            <th>Crédito / Cliente</th>
                            <th>Producto</th>
                            <th>Total</th>
                            <th>Fecha acuerdo</th>
                            <th>Avance</th>
                            <th>Estatus</th>
                            <th>Docs</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <div id="empty-convenios" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            Sin convenios registrados.
        </div>
    </div>

    <!-- ══ PESTAÑA 1: ENVIADOS FINALIZADOS ══ -->
    <div class="tab-pane fade show active" id="tab-env-finalizado" role="tabpanel">

        <div id="loader-env-finalizado" class="text-center py-5 text-muted">
            <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            Cargando convenios...
        </div>
        <div id="wrap-env-finalizado" class="d-none">
            <!-- Las cards se inyectan aquí dinámicamente -->
        </div>
        <div id="empty-env-finalizado" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
            Sin convenios saldados por el momento.
        </div>
        <div id="empty-busqueda" class="text-center py-5 text-muted d-none">
            <i class="fa-solid fa-search fa-2x mb-2 d-block opacity-50"></i>
            Sin resultados para la búsqueda.
        </div>
    </div>

    <!-- ══ PESTAÑA 2: EN PROCESO ══ -->
    <div class="tab-pane fade" id="tab-en-proceso" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body">
                <div id="loader-en-proceso" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    Cargando registros...
                </div>
                <div id="wrap-ep-cards" class="d-none">
                    <!-- Cards renderizadas por JS -->
                </div>
                <div id="empty-en-proceso" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    Sin registros en proceso de validación.
                </div>
                <div id="empty-busqueda-ep" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-search fa-2x mb-2 d-block opacity-50"></i>
                    Sin resultados para la búsqueda.
                </div>
            </div>
        </div>
    </div>

    <!-- ══ PESTAÑA 3: HISTORIAL ══ -->
    <div class="tab-pane fade" id="tab-historial" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div id="loader-historial" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-spinner fa-spin fa-2x mb-2 d-block"></i>
                    Cargando historial...
                </div>
                <div id="wrap-historial" class="d-none">
                    <!-- Tabla inyectada por JS -->
                </div>
                <div id="empty-historial" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-50"></i>
                    Sin movimientos registrados todavía.
                </div>
                <div id="empty-busqueda-hist" class="text-center py-5 text-muted d-none">
                    <i class="fa-solid fa-search fa-2x mb-2 d-block opacity-50"></i>
                    Sin resultados para la búsqueda.
                </div>
            </div>
        </div>
    </div>

</div>

<script>
/* ══════════════════════════════════════════
   CIERRE DE CRÉDITO — JS
══════════════════════════════════════════ */
(function () {
    'use strict';

    /* ── Helpers ── */
    function fmt(n) {
        const v = parseFloat(n) || 0;
        return v.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
    }
    function fmtFecha(val) {
        if (!val) return '—';
        const d = new Date(val.replace(' ', 'T'));
        if (isNaN(d)) return val;
        return d.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }
    function badgeEstatus(estatus) {
        if (estatus === 'en_proceso')
            return '<span class="badge badge-en-proceso rounded-pill px-3">En Proceso</span>';
        return `<span class="badge bg-secondary">${estatus}</span>`;
    }
    function esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function convenioEstatusBadge(estatus) {
        const map = {
            'completado': { bg: '#bbf7d0', color: '#14532d', icon: 'fa-circle-check',   label: 'Completado' },
            'activo':     { bg: '#dbeafe', color: '#1e40af', icon: 'fa-hourglass-half', label: 'Activo'     },
            'cancelado':  { bg: '#fee2e2', color: '#b91c1c', icon: 'fa-ban',            label: 'Cancelado'  },
        };
        const cfg = map[estatus] || { bg: '#f1f5f9', color: '#475569', icon: 'fa-circle', label: esc(estatus || '—') };
        return `<span class="badge rounded-pill" style="background:${cfg.bg};color:${cfg.color};font-size:.75rem;font-weight:700;">
                    <i class="fa-solid ${cfg.icon} me-1"></i>${cfg.label}
                </span>`;
    }

    /* ══════════════════════════════════
       RENDER: CARDS ENVIADOS FINALIZADOS
    ══════════════════════════════════ */
    let _allRows     = [];
    let _allRowsEp   = [];
    let _allRowsHist = [];
    let _allRowsConv = [];
    let _validador   = '—';

    function renderCards(rows, validador) {
        _allRows   = rows;
        _validador = validador;

        document.getElementById('loader-env-finalizado').classList.add('d-none');
        const badge = document.getElementById('badge-env-finalizado');
        badge.textContent = rows.length;

        if (!rows.length) {
            document.getElementById('empty-env-finalizado').classList.remove('d-none');
            return;
        }

        // Mostrar barra de búsqueda
        document.getElementById('cc-search-bar').style.display = '';

        _pintarCards(rows);
    }

    function _pintarCards(rows) {
        const wrap         = document.getElementById('wrap-env-finalizado');
        const emptyNormal  = document.getElementById('empty-env-finalizado');
        const emptySearch  = document.getElementById('empty-busqueda');

        emptyNormal.classList.add('d-none');
        emptySearch.classList.add('d-none');

        if (!rows.length) {
            wrap.classList.add('d-none');
            wrap.innerHTML = '';
            emptySearch.classList.remove('d-none');
            return;
        }

        wrap.classList.remove('d-none');
        wrap.innerHTML = rows.map(r => buildCard(r, _validador)).join('');
    }

    /* ── Detecta qué pestaña está activa ── */
    function _tabActiva() {
        if (document.getElementById('tab-convenios').classList.contains('show'))   return 'conv';
        if (document.getElementById('tab-en-proceso').classList.contains('show'))  return 'ep';
        if (document.getElementById('tab-historial').classList.contains('show'))   return 'hist';
        return 'ef';
    }

    /* ── Filtro en tiempo real (aplica en la pestaña activa) ── */
    function ccFiltrar(termino) {
        const t = termino.trim().toLowerCase();
        const btnLimpiar = document.getElementById('cc-btn-limpiar-busqueda');
        btnLimpiar.style.display = t ? '' : 'none';

        const tab = _tabActiva();

        if (tab === 'ep') {
            _pintarEnProceso(!t ? _allRowsEp : _allRowsEp.filter(r =>
                String(r.id_credito      || '').toLowerCase().includes(t) ||
                String(r.nombre_cliente  || '').toLowerCase().includes(t) ||
                String(r.nombre_producto || '').toLowerCase().includes(t)
            ));
            return;
        }

        if (tab === 'hist') {
            _pintarHistorial(!t ? _allRowsHist : _allRowsHist.filter(r =>
                String(r.id_credito     || '').toLowerCase().includes(t) ||
                String(r.nombre_cliente || '').toLowerCase().includes(t)
            ));
            return;
        }

        if (tab === 'conv') {
            if (_tablaConv) { _tablaConv.search(t).draw(); }
            return;
        }

        // Tab 1: Enviados Finalizados
        _pintarCards(!t ? _allRows : _allRows.filter(r =>
            String(r.id_credito      || '').toLowerCase().includes(t) ||
            String(r.nombre_cliente  || '').toLowerCase().includes(t) ||
            String(r.nombre_producto || '').toLowerCase().includes(t) ||
            String(r.nombre_despacho || '').toLowerCase().includes(t)
        ));
    }

    document.getElementById('cc-input-buscar')
        .addEventListener('input', function () { ccFiltrar(this.value); });

    document.getElementById('cc-btn-limpiar-busqueda')
        .addEventListener('click', function () {
            const input = document.getElementById('cc-input-buscar');
            input.value = '';
            ccFiltrar('');
            input.focus();
        });

    function buildCard(r, validador) {
        const semanas      = parseInt(r.numero_semanas) || 1;
        const pagadas      = parseInt(r.cuotas_pagadas) || 0;
        const pct          = Math.min(100, Math.round((pagadas / semanas) * 100));

        const totalPagar   = parseFloat(r.total_a_pagar)   || 0;
        const adicional    = parseFloat(r.monto_adicional) || 0;
        const descuento    = parseFloat(r.descuento_monto) || 0;
        const totalInicial = totalPagar - adicional;

        // ── Resumen de aplicación (solo si tiene adicionales) ──
        let resumenHtml = '';
        if (adicional > 0) {
            resumenHtml = `
            <div class="cc-resumen-aplicacion">
                <div class="cc-res-title"><i class="fa-solid fa-list-check me-1"></i>Resumen de adicionales</div>
                <div class="cc-res-row"><span>Descuento (${esc(r.porcentaje_descuento)}%)</span><span class="text-success">- ${fmt(descuento)}</span></div>
                <div class="cc-res-row"><span>Total inicial</span><span>${fmt(totalInicial)}</span></div>
                <div class="cc-res-row"><span>Adicionales</span><span>${fmt(adicional)}</span></div>
                <div class="cc-res-row total"><span>Total final</span><span>${fmt(totalPagar)}</span></div>
            </div>`;
        }

        const despacho = r.nombre_despacho ? esc(r.nombre_despacho) : '<span class="text-muted fst-italic">Sin asignación</span>';

        return `
        <div class="cc-conv-card" id="cc-card-${r.id}">

            <!-- Cabecera: ID Crédito + barra de progreso -->
            <div class="cc-conv-card-header">
                <span class="cc-credito-id">
                    Crédito: ${esc(r.id_credito)}
                    <small>${esc(r.nombre_cliente)}</small>
                </span>
                <span style="color:#fff; font-size:.82rem; font-weight:600; white-space:nowrap;">
                    ${pagadas} pagos de ${semanas}${pagadas >= semanas ? ' <i class=\'bi bi-check-circle-fill\' style=\'color:#4ade80\'></i>' : ''}
                </span>
            </div>

            <!-- Cuerpo: detalles -->
            <div class="cc-conv-card-body">
                <div class="cc-conv-details">

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Producto elegido</span>
                        <span class="cc-val">${esc(r.nombre_producto)}</span>
                        <span style="background:#e0edff; color:#1d4ed8; font-size:.75rem; font-weight:700;
                                      padding:2px 8px; border-radius:20px; margin-left:.35rem; white-space:nowrap;">
                            ${parseFloat(r.porcentaje_descuento) || 0}%
                        </span>
                    </div>

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Nombre del cliente</span>
                        <span class="cc-val">${esc(r.nombre_cliente)}</span>
                    </div>

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Total final</span>
                        <span class="cc-val fw-bold text-success">${fmt(totalPagar)}</span>
                    </div>

                    ${resumenHtml}

                    <div class="cc-detail-row">
                        <span class="cc-lbl">Despacho a cargo</span>
                        <span class="cc-val">${despacho}</span>
                    </div>

                    <!-- Fecha de finalización -->
                    ${r.fecha_modifica ? `
                    <div class="cc-detail-row" style="margin-top:.35rem;">
                        <span class="cc-lbl">Finalizado el</span>
                        <span class="cc-val">${new Date(r.fecha_modifica).toLocaleString('es-MX', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false})}</span>
                    </div>` : ''}

                    <!-- Validación: usuario de sesión actual -->
                    <div class="cc-validacion-box">
                        <i class="fa-solid fa-user-shield"></i>
                        <span>Validación:</span>
                        <span class="cc-val-user">${esc(validador)}</span>
                    </div>

                    <!-- Documentos adjuntos -->
                    ${(() => {
                        const pdfOk  = !!(r.pdf_adjunto && r.pdf_adjunto !== '');
                        const compSub = parseInt(r.comprobantes_subidos) || 0;
                        if (!pdfOk && compSub === 0) return '';
                        let items = '';
                        if (pdfOk)
                            items += `<a href="${esc(r.pdf_adjunto)}" target="_blank"
                                         class="btn btn-sm btn-outline-secondary"
                                         style="font-size:.78rem;">
                                         <i class="fa-solid fa-file-pdf me-1"></i>PDF convenio
                                      </a>`;
                        if (compSub > 0)
                            items += `<span class="cc-doc-ok" style="padding:3px 10px;font-size:.78rem;font-weight:700;"
                                           title="${compSub} comprobante${compSub !== 1 ? 's' : ''} subido${compSub !== 1 ? 's' : ''}">
                                           <i class="fa-solid fa-receipt me-1"></i>${compSub} comprobante${compSub !== 1 ? 's' : ''}
                                      </span>`;
                        return `<div class="cc-doccheck-wrap mt-2">
                                    <div class="cc-doccheck-title"><i class="fa-solid fa-paperclip me-1"></i>Documentos adjuntos</div>
                                    <div class="cc-doccheck-items" style="display:flex;gap:.4rem;flex-wrap:wrap;">${items}</div>
                                </div>`;
                    })()}

                </div>
            </div>

            <!-- Footer: botón confirmar -->
            <div class="cc-conv-card-footer">
                <button class="cc-btn-confirmar" onclick="ccConfirmar(${r.id}, '${esc(r.id_credito)}', '${esc(r.nombre_cliente)}')"
                        id="cc-btn-${r.id}">
                    <i class="fa-solid fa-check-circle"></i>
                    Confirmar cierre
                </button>
            </div>

            <!-- Panel lateral derecho (oculto por defecto) -->
            <div class="cc-side-panel" id="cc-acc-body-${r.id}">
                <div class="cc-side-panel-header">
                    <span class="cc-sp-title"><i class="fa-solid fa-table-list me-1"></i>Detalle del cierre</span>
                    <button class="cc-side-panel-close" onclick="ccToggleDetalle(${r.id})" title="Cerrar panel">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="cc-acc-loader-${r.id}" class="text-center py-3 text-muted" style="display:none;">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando detalle...
                </div>
                <div id="cc-acc-content-${r.id}" style="overflow-y:auto;flex:1;"></div>
            </div>

        </div>`;
    }

    /* ══════════════════════════════════
       RENDER: CARDS EN PROCESO
    ══════════════════════════════════ */
    function renderEnProceso(rows) {
        _allRowsEp = rows;
        document.getElementById('loader-en-proceso').classList.add('d-none');
        document.getElementById('badge-en-proceso').textContent = rows.length;
        document.getElementById('cc-search-bar').style.display = '';

        if (!rows || rows.length === 0) {
            document.getElementById('empty-en-proceso').classList.remove('d-none');
            return;
        }

        const t = document.getElementById('cc-input-buscar').value.trim().toLowerCase();
        _pintarEnProceso(t ? rows.filter(r =>
            String(r.id_credito      || '').toLowerCase().includes(t) ||
            String(r.nombre_cliente  || '').toLowerCase().includes(t) ||
            String(r.nombre_producto || '').toLowerCase().includes(t)
        ) : rows);
    }

    function _pintarEnProceso(rows) {
        const wrap        = document.getElementById('wrap-ep-cards');
        const emptyNormal = document.getElementById('empty-en-proceso');
        const emptySearch = document.getElementById('empty-busqueda-ep');
        emptyNormal.classList.add('d-none');
        if (emptySearch) emptySearch.classList.add('d-none');

        if (!rows.length) {
            wrap.classList.add('d-none');
            wrap.innerHTML = '';
            if (emptySearch) emptySearch.classList.remove('d-none');
            return;
        }

        wrap.innerHTML = rows.map(r => buildEnProcesoCard(r)).join('');
        wrap.classList.remove('d-none');
    }

    function buildEnProcesoCard(r) {
        const fmtN   = (n) => parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        const pdfOk  = !!(r.pdf_adjunto && r.pdf_adjunto !== '');
        const numCon = parseInt(r.comprobantes_subidos) || 0;
        const numTot = parseInt(r.comprobantes_total)   || 0;
        const compOk = numTot > 0 && numCon === numTot;
        const todoListo = pdfOk && compOk;

        const pdfBadge = pdfOk
            ? `<span class="cc-doc-ok"><i class="fa-solid fa-file-pdf me-1"></i>PDF Convenio</span>`
            : `<span class="cc-doc-missing"><i class="fa-solid fa-file-pdf me-1"></i>Sin PDF convenio</span>`;

        let compBadge;
        if (numTot === 0) {
            compBadge = `<span class="cc-doc-missing"><i class="fa-solid fa-receipt me-1"></i>Sin comprobantes</span>`;
        } else if (compOk) {
            compBadge = `<span class="cc-doc-ok"><i class="fa-solid fa-receipt me-1"></i>Comprobantes ${numCon}/${numTot}</span>`;
        } else {
            compBadge = `<span class="cc-doc-partial"><i class="fa-solid fa-receipt me-1"></i>Comprobantes ${numCon}/${numTot}</span>`;
        }

        const estadoBadge = todoListo
            ? `<span class="badge" style="background:rgba(255,255,255,.2);color:#fff;font-size:.7rem;"><i class="fa-solid fa-circle-check me-1"></i>Listo</span>`
            : `<span class="badge" style="background:rgba(250,200,0,.3);color:#fff;font-size:.7rem;"><i class="fa-solid fa-triangle-exclamation me-1"></i>Docs adjuntos</span>`;

        const pdfLink = pdfOk
            ? `<a href="${esc(r.pdf_adjunto)}" target="_blank" class="btn btn-sm btn-outline-secondary" style="font-size:.78rem;"><i class="fa-solid fa-eye me-1"></i>Ver PDF</a>`
            : '';

        return `
        <div class="cc-ep-wrapper" id="cc-ep-wrapper-${r.id}">

            <!-- Card principal -->
            <div class="cc-conv-card" id="cc-ep-card-${r.id}" style="min-width:300px;width:420px;max-width:100%;">

                <!-- Cabecera -->
                <div class="cc-conv-card-header">
                    <span class="cc-credito-id">#${esc(r.id_credito)} <small>${esc(r.nombre_cliente)}</small></span>
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        ${estadoBadge}
                        <button class="cc-btn-descartar" onclick="ccDescartar(${r.id})"
                                title="Descartar — regresar a Enviados Finalizados">
                            <i class="fa-solid fa-rotate-left"></i>Descartar
                        </button>
                    </div>
                </div>

                <!-- Resumen rápido -->
                <div class="cc-conv-card-body">
                    <div class="cc-conv-details">
                        <div class="cc-detail-row">
                            <span class="cc-lbl">Producto</span>
                            <span class="cc-val">${esc(r.nombre_producto)}</span>
                            <span style="background:#e0edff;color:#1d4ed8;font-size:.75rem;font-weight:700;
                                          padding:2px 8px;border-radius:20px;margin-left:.35rem;white-space:nowrap;">
                                ${parseFloat(r.porcentaje_descuento) || 0}%
                            </span>
                        </div>
                        <div class="cc-detail-row">
                            <span class="cc-lbl">Total pagado</span>
                            <span class="cc-val fw-bold text-success">${fmtN(r.total_a_pagar)}</span>
                        </div>
                        <div class="cc-detail-row">
                            <span class="cc-lbl">Registrado por</span>
                            <span class="cc-val">${esc(r.usuario_alta)}</span>
                        </div>
                        <div class="cc-detail-row">
                            <span class="cc-lbl">Fecha envío</span>
                            <span class="cc-val">${fmtFecha(r.fecha_alta)}</span>
                        </div>
                        <div class="cc-doccheck-wrap mt-2">
                            <div class="cc-doccheck-title"><i class="fa-solid fa-paperclip me-1"></i>Documentos adjuntos</div>
                            <div class="cc-doccheck-items">${pdfBadge}${compBadge}</div>
                        </div>
                        ${r.fecha_envio_cartera ? `
                        <div style="display:flex;align-items:center;gap:.4rem;margin-top:.5rem;background:#fef9c3;border:1px solid #fde68a;border-radius:.4rem;padding:.3rem .65rem;font-size:.78rem;color:#854d0e;">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <span>Envío previo fallido — correo no notificado</span>
                        </div>` : ''}
                    </div>
                </div>

                <!-- Footer -->
                <div class="cc-conv-card-footer" style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
                    ${pdfLink}
                    <button class="btn btn-sm btn-outline-primary" id="cc-acc-btn-${r.id}"
                            style="font-size:.78rem;" onclick="ccToggleDetalle(${r.id})">
                        <i class="fa-solid fa-table-list me-1"></i>Ver detalle
                    </button>
                    <a href="/CierreCredito/descargarExcelCierre?id=${r.id}"
                       class="btn btn-sm btn-outline-success" style="font-size:.78rem;">
                        <i class="fa-solid fa-file-excel me-1"></i>Excel
                    </a>
                    <button class="cc-btn-confirmar"
                            style="background:linear-gradient(135deg,#059669,#10b981);flex:1;min-width:120px;"
                            onclick="ccEnviarACartera(${r.id})" id="cc-ep-btn-${r.id}">
                        <i class="fa-solid fa-paper-plane"></i>
                        Enviar a cartera
                    </button>
                </div>

            </div>

            <!-- Panel lateral derecho (oculto por defecto) -->
            <div class="cc-side-panel" id="cc-acc-body-${r.id}">
                <div class="cc-side-panel-header">
                    <span class="cc-sp-title"><i class="fa-solid fa-table-list me-1"></i>Detalle del cierre</span>
                    <button class="cc-side-panel-close" onclick="ccToggleDetalle(${r.id})" title="Cerrar panel">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div id="cc-acc-loader-${r.id}" class="text-center py-3 text-muted" style="display:none;">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando detalle...
                </div>
                <div id="cc-acc-content-${r.id}" style="overflow-y:auto;flex:1;"></div>
            </div>

        </div>`;
    }

    /* ══════════════════════════════════
       RENDER: CARDS CONVENIOS (Tab 0)
    ══════════════════════════════════ */
    /* ══════════════════════════════════
       RENDER: TABLA CONVENIOS (Tab 0)
    ══════════════════════════════════ */
    let _tablaConv = null;

    function _initTablaConv() {
        if (_tablaConv) return;
        _tablaConv = $('#tablaConveniosTodos').DataTable({
            data: [],
            columns: [
                { data: null, orderable: false, searchable: false, className: 'control', defaultContent: '' },
                {
                    data: null,
                    render: function(d, t, r) {
                        if (t === 'filter' || t === 'sort')
                            return String(r.id_credito || '') + ' ' + String(r.nombre_cliente || '');
                        return `<span class="fw-bold" style="color:#4F46E5;display:block;">#${esc(r.id_credito)}</span>` +
                               `<span class="text-muted" style="font-size:.78rem;">${esc(r.nombre_cliente)}</span>`;
                    }
                },
                {
                    data: null,
                    render: function(d, t, r) {
                        if (t === 'filter' || t === 'sort') return String(r.nombre_producto || '');
                        const pct = parseFloat(r.porcentaje_descuento) || 0;
                        return `<span>${esc(r.nombre_producto)}</span><br>` +
                               `<span style="background:#e0edff;color:#1d4ed8;font-size:.7rem;font-weight:700;padding:1px 7px;border-radius:20px;">${pct}%</span>`;
                    }
                },
                {
                    data: 'total_a_pagar',
                    className: 'text-end',
                    render: function(d) { return `<strong class="text-success">${fmt(d)}</strong>`; }
                },
                { data: 'fecha_acuerdo', render: function(d) { return esc(d || '—'); } },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(d, t, r) {
                        const pagadas = parseInt(r.cuotas_pagadas) || 0;
                        const semanas = parseInt(r.numero_semanas) || parseInt(r.num_semanas_amort) || 0;
                        if (!semanas) return '<span class="text-muted">—</span>';
                        const done = pagadas >= semanas
                            ? ' <i class="fa-solid fa-circle-check text-success" style="font-size:.72rem;"></i>' : '';
                        return `<span style="font-size:.83rem;white-space:nowrap;">${pagadas}/${semanas}${done}</span>`;
                    }
                },
                {
                    data: 'estatus',
                    render: function(d) { return convenioEstatusBadge(d); }
                },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(d, t, r) {
                        const pdfOk  = !!(r.pdf_adjunto && r.pdf_adjunto !== '');
                        const compSub = parseInt(r.comprobantes_subidos) || 0;
                        if (!pdfOk && compSub === 0) return '<span class="text-muted">—</span>';
                        let html = '<div style="display:flex;gap:.3rem;flex-wrap:wrap;">';
                        if (pdfOk)
                            html += `<a href="${esc(r.pdf_adjunto)}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary"
                                        style="font-size:.72rem;padding:.2rem .55rem;"
                                        title="Ver PDF del convenio">
                                        <i class="fa-solid fa-file-pdf me-1"></i>PDF
                                     </a>`;
                        if (compSub > 0)
                            html += `<span class="cc-doc-ok" style="padding:2px 9px;font-size:.75rem;font-weight:700;"
                                          title="${compSub} comprobante${compSub !== 1 ? 's' : ''} subido${compSub !== 1 ? 's' : ''}">
                                          <i class="fa-solid fa-receipt me-1"></i>${compSub}
                                     </span>`;
                        html += '</div>';
                        return html;
                    }
                },
                {
                    data: null, orderable: false, searchable: false,
                    render: function(d, t, r) {
                        return `<button class="btn btn-sm btn-outline-primary" style="font-size:.72rem;white-space:nowrap;"` +
                               ` onclick="ccToggleDetalleConv(this,${r.id})"` +
                               ` title="Ver amortización y documentos">` +
                               `<i class="fa-solid fa-chevron-down" style="font-size:.65rem;"></i> Ver detalle` +
                               `</button>`;
                    }
                }
            ],
            pageLength: 15,
            lengthMenu: [10, 15, 25, 50, 100],
            order: [[1, 'asc']],
            responsive: { details: { type: 'column', target: 0 } },
            language: {
                emptyTable:   'Sin convenios registrados',
                infoEmpty:    'Sin registros',
                info:         'Mostrando _START_ a _END_ de _TOTAL_ convenios',
                infoFiltered: '(filtrado de _MAX_ totales)',
                lengthMenu:   'Mostrar _MENU_ registros',
                zeroRecords:  'Sin resultados para la búsqueda',
                paginate: { first: '«', last: '»', next: '›', previous: '‹' }
            },
            dom: '<"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            autoWidth: false,
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            }
        });
    }

    function renderConvenios(rows) {
        _allRowsConv = rows;
        document.getElementById('loader-convenios').classList.add('d-none');
        document.getElementById('badge-convenios').textContent = rows.length;
        document.getElementById('cc-search-bar').style.display = '';

        if (!rows || rows.length === 0) {
            document.getElementById('empty-convenios').classList.remove('d-none');
            return;
        }

        document.getElementById('wrap-convenios').classList.remove('d-none');
        _initTablaConv();
        _tablaConv.clear().rows.add(rows).draw();
    }

    /* kept so ccFiltrar can call it; delegates to DataTables search */
    function _pintarConvenios(rows) {
        if (_tablaConv) {
            _tablaConv.clear().rows.add(rows).draw();
        }
    }

    /* legacy alias */
    function buildConvenioCard(r) { return r; }
    function buildConvenioRow(r)  { return r; }

    /* ══════════════════════════════════
       ACORDÉON DE DETALLE (Tab 2)
    ══════════════════════════════════ */
    const _detalleCache    = {};
    const _detalleConvCache = {};

    window.ccToggleDetalle = function(id) {
        const btn    = document.getElementById(`cc-acc-btn-${id}`);
        const panel  = document.getElementById(`cc-acc-body-${id}`);
        const card   = document.getElementById(`cc-ep-card-${id}`);
        const loader = document.getElementById(`cc-acc-loader-${id}`);
        const content = document.getElementById(`cc-acc-content-${id}`);
        const isOpen = panel.classList.contains('open');

        if (isOpen) {
            panel.classList.remove('open');
            card.classList.remove('cc-has-panel');
            btn.innerHTML = '<i class="fa-solid fa-table-list me-1"></i>Ver detalle';
            return;
        }

        panel.classList.add('open');
        card.classList.add('cc-has-panel');
        btn.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Cerrar';

        // Ya cargado previamente
        if (_detalleCache[id]) {
            content.innerHTML = buildDetalleHtml(_detalleCache[id]);
            return;
        }

        loader.style.display = '';
        content.innerHTML = '';

        fetch('/CierreCredito/getDetalleCierre', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(res => {
            loader.style.display = 'none';
            if (!res.success) throw new Error(res.mensaje);
            _detalleCache[id] = res.datos;
            content.innerHTML = buildDetalleHtml(res.datos);
        })
        .catch(err => {
            loader.style.display = 'none';
            content.innerHTML = `<div class="alert alert-danger py-2">Error: ${esc(err.message)}</div>`;
        });
    };

    function buildDetalleHtml(d) {
        const fmtN = (n) => parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        const conv = d.convenio || {};
        const amort = d.amortizacion || [];

        // ── Resumen financiero ──
        const resumenHtml = `
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:.75rem;margin-bottom:1rem;">
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Adeudo original</div>
                <div style="font-size:1rem;font-weight:700;color:#dc2626;">${fmtN(conv.adeudo_total_original)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Descuento (${esc(conv.porcentaje_descuento)}%)</div>
                <div style="font-size:1rem;font-weight:700;color:#16a34a;">- ${fmtN(conv.descuento_monto)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Total a pagar</div>
                <div style="font-size:1rem;font-weight:700;color:#1d4ed8;">${fmtN(conv.total_a_pagar)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Pago semanal</div>
                <div style="font-size:1rem;font-weight:700;color:#475569;">${fmtN(conv.pago_semanal)}</div>
            </div>
        </div>`;

        // ── Tabla de amortización ──
        let filasAmort = '';
        if (amort.length === 0) {
            filasAmort = `<tr><td colspan="7" class="text-center text-muted py-3">Sin filas de amortización registradas.</td></tr>`;
        } else {
            filasAmort = amort.map(a => {
                const pagada = (a.estatus_pago === 'pagado');
                const cls    = pagada ? 'pagada' : 'pendiente';
                const icon   = pagada
                    ? `<i class="fa-solid fa-circle-check text-success"></i>`
                    : `<i class="fa-regular fa-clock text-warning"></i>`;
                const compIcon = a.comprobante_path
                    ? `<i class="fa-solid fa-paperclip text-success" title="Comprobante subido"></i>`
                    : `<i class="fa-solid fa-minus text-muted"></i>`;
                return `
                <tr class="${cls}">
                    <td class="text-center fw-bold">${esc(a.numero_semana)}</td>
                    <td>${esc(a.fecha_pago || '—')}</td>
                    <td class="text-end">${fmtN(a.pago_semanal)}</td>
                    <td class="text-end">${fmtN(a.capital)}</td>
                    <td class="text-end">${fmtN(a.saldo_restante)}</td>
                    <td class="text-center">${icon} ${pagada ? 'Pagado' : 'Pendiente'}</td>
                    <td class="text-center">${compIcon}</td>
                </tr>`;
            }).join('');
        }

        const tablaAmort = `
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#1d4ed8;margin-bottom:.5rem;">
            <i class="fa-solid fa-table-list me-1"></i>Tabla de amortización
        </div>
        <div style="overflow-x:auto;">
            <table class="cc-amort-table">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Fecha pago</th>
                        <th class="text-end">Pago</th>
                        <th class="text-end">Capital</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-center">Comprobante</th>
                    </tr>
                </thead>
                <tbody>${filasAmort}</tbody>
            </table>
        </div>`;

        return resumenHtml + tablaAmort;
    }

    /* ══════════════════════════════════
       TOGGLE Y DETALLE — TAB CONVENIOS (Tab 0)
    ══════════════════════════════════ */
    window.ccToggleDetalleConv = function(btn, id) {
        if (!_tablaConv) return;
        const tr  = $(btn).closest('tr');
        const row = _tablaConv.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            btn.innerHTML = '<i class="fa-solid fa-chevron-down" style="font-size:.65rem;"></i> Ver detalle';
            btn.classList.replace('btn-primary', 'btn-outline-primary');
            return;
        }

        btn.innerHTML = '<i class="fa-solid fa-chevron-up" style="font-size:.65rem;"></i> Cerrar';
        btn.classList.replace('btn-outline-primary', 'btn-primary');
        tr.addClass('shown');

        if (_detalleConvCache[id]) {
            row.child(`<div class="cc-conv-detail-inner">${buildDetalleConvHtml(_detalleConvCache[id])}</div>`).show();
            return;
        }

        row.child('<div class="text-center py-3 text-muted"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando detalle...</div>').show();

        fetch('/CierreCredito/getDetalleConvenio', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) throw new Error(res.mensaje);
            _detalleConvCache[id] = res.datos;
            row.child(`<div class="cc-conv-detail-inner">${buildDetalleConvHtml(res.datos)}</div>`).show();
        })
        .catch(err => {
            row.child(`<div class="alert alert-danger m-2 py-2">Error: ${esc(err.message)}</div>`).show();
        });
    };

    function buildDetalleConvHtml(d) {
        const fmtN = (n) => parseFloat(n || 0).toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
        const conv  = d.convenio     || {};
        const amort = d.amortizacion || [];

        // ── Resumen financiero ──
        const resumenHtml = `
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:.75rem;margin-bottom:1rem;">
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Adeudo original</div>
                <div style="font-size:1rem;font-weight:700;color:#dc2626;">${fmtN(conv.adeudo_total_original)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Descuento (${esc(conv.porcentaje_descuento)}%)</div>
                <div style="font-size:1rem;font-weight:700;color:#16a34a;">- ${fmtN(conv.descuento_monto)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Total a pagar</div>
                <div style="font-size:1rem;font-weight:700;color:#1d4ed8;">${fmtN(conv.total_a_pagar)}</div>
            </div>
            <div class="cc-resumen-aplicacion" style="margin:0;">
                <div class="cc-res-title">Pago semanal</div>
                <div style="font-size:1rem;font-weight:700;color:#475569;">${fmtN(conv.pago_semanal)}</div>
            </div>
        </div>`;

        // ── Tabla de amortización con documentos ──
        let filasAmort = '';
        if (amort.length === 0) {
            filasAmort = `<tr><td colspan="5" class="text-center text-muted py-3">Sin amortización registrada.</td></tr>`;
        } else {
            filasAmort = amort.map(a => {
                const pagada = (a.estatus_pago === 'pagado');
                const cls    = pagada ? 'pagada' : 'pendiente';
                const icon   = pagada
                    ? `<i class="fa-solid fa-circle-check text-success"></i>`
                    : `<i class="fa-regular fa-clock text-warning"></i>`;
                const compHtml = a.comprobante_path
                    ? `<a href="${esc(a.comprobante_path)}" target="_blank"
                              style="font-size:.72rem;padding:2px 8px;border-radius:4px;
                                     background:#dcfce7;color:#15803d;text-decoration:none;white-space:nowrap;">
                           <i class="fa-solid fa-paperclip me-1"></i>Ver ticket
                       </a>`
                    : `<span style="color:#94a3b8;font-size:.75rem;">—</span>`;

                return `
                <tr class="${cls}">
                    <td class="text-center fw-bold">${esc(a.numero_semana)}</td>
                    <td>${esc(a.fecha_pago || '—')}</td>
                    <td class="text-end">${fmtN(a.pago_semanal)}</td>
                    <td class="text-center">${icon} ${pagada ? 'Pagado' : 'Pendiente'}</td>
                    <td class="text-center">${compHtml}</td>
                </tr>`;
            }).join('');
        }

        const tablaAmort = `
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#1d4ed8;margin-bottom:.5rem;">
            <i class="fa-solid fa-table-list me-1"></i>Amortización y tickets de pago
        </div>
        <div style="overflow-x:auto;">
            <table class="cc-amort-table">
                <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th>Fecha pago</th>
                        <th class="text-end">Pago</th>
                        <th class="text-center">Estatus</th>
                        <th class="text-center">Comprobante</th>
                    </tr>
                </thead>
                <tbody>${filasAmort}</tbody>
            </table>
        </div>`;

        return resumenHtml + tablaAmort;
    }

    /* ══════════════════════════════════
       CARGA DE DATOS
    ══════════════════════════════════ */

    // Convenios — lazy: solo al activar la pestaña
    let conveniosCargado = false;
    function cargarConvenios() {
        if (conveniosCargado) return;
        conveniosCargado = true;
        document.getElementById('loader-convenios').classList.remove('d-none');
        fetch('/CierreCredito/getAllConvenios', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderConvenios(res.datos);
            })
            .catch(err => {
                document.getElementById('loader-convenios').innerHTML =
                    `<div class="alert alert-danger m-3">Error al cargar: ${err.message}</div>`;
            });
    }

    // Enviados Finalizados — carga inicial (pestaña activa)
    function cargarEnviadoFinalizado() {
        fetch('/CierreCredito/getEnviadoFinalizado', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderCards(res.datos, res.validador || '—');
            })
            .catch(err => {
                document.getElementById('loader-env-finalizado').innerHTML =
                    `<div class="alert alert-danger m-3">Error al cargar: ${err.message}</div>`;
            });
    }

    // En Proceso — lazy: solo al activar la pestaña
    let enProcesoCargado = false;
    function cargarEnProceso() {
        if (enProcesoCargado) return;
        enProcesoCargado = true;
        fetch('/CierreCredito/getEnProceso', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderEnProceso(res.datos);
            })
            .catch(err => {
                document.getElementById('loader-en-proceso').innerHTML =
                    `<div class="alert alert-danger">Error al cargar: ${err.message}</div>`;
            });
    }

    // (tab-en-proceso-btn listener is registered after cargarHistorial below)

    /* ══════════════════════════════════
       HISTORIAL — carga y render
    ══════════════════════════════════ */
    let historialCargado = false;

    function cargarHistorial() {
        historialCargado = true;
        document.getElementById('loader-historial').classList.remove('d-none');
        document.getElementById('wrap-historial').classList.add('d-none');
        document.getElementById('empty-historial').classList.add('d-none');
        fetch('/CierreCredito/getHistorial', { method: 'POST' })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                renderHistorial(res.datos);
            })
            .catch(err => {
                document.getElementById('loader-historial').innerHTML =
                    `<div class="alert alert-danger m-3">Error al cargar: ${err.message}</div>`;
            });
    }

    // Refresca silenciosamente solo si la pestaña ya fue visitada
    function refrescarHistorial() {
        if (!historialCargado) return;
        cargarHistorial();
    }

    function renderHistorial(rows) {
        _allRowsHist = rows;
        document.getElementById('loader-historial').classList.add('d-none');
        document.getElementById('cc-search-bar').style.display = '';

        if (!rows || rows.length === 0) {
            document.getElementById('empty-historial').classList.remove('d-none');
            return;
        }

        const t = document.getElementById('cc-input-buscar').value.trim().toLowerCase();
        _pintarHistorial(t ? rows.filter(r =>
            String(r.id_credito     || '').toLowerCase().includes(t) ||
            String(r.nombre_cliente || '').toLowerCase().includes(t)
        ) : rows);
    }

    function _pintarHistorial(rows) {
        const wrap        = document.getElementById('wrap-historial');
        const emptyHist   = document.getElementById('empty-historial');
        const emptySearch = document.getElementById('empty-busqueda-hist');
        emptyHist.classList.add('d-none');
        if (emptySearch) emptySearch.classList.add('d-none');

        if (!rows.length) {
            wrap.classList.add('d-none');
            wrap.innerHTML = '';
            if (emptySearch) emptySearch.classList.remove('d-none');
            return;
        }

        const etiqueta = (r) => {
            if (r.estatus === 'enviado_cartera' && r.email_destino_cartera) {
                return `<span class="badge rounded-pill" style="background:#dcfce7;color:#15803d;font-size:.78rem;">
                            <i class="fa-solid fa-circle-check me-1"></i>Enviado — correo notificado
                        </span>`;
            }
            if (r.estatus === 'enviado_cartera') {
                return `<span class="badge rounded-pill" style="background:#fef9c3;color:#854d0e;font-size:.78rem;">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i>Limite de envios rebasado — sin correo notificado
                        </span>`;
            }
            if (r.estatus === 'en_cola') {
                return `<div style="display:inline-flex;flex-direction:column;gap:5px;align-items:flex-start;">
                            <span class="badge rounded-pill" style="background:#fef9c3;color:#854d0e;font-size:.78rem;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Limite de envios rebasado — sin correo notificado
                            </span>
                            <button onclick="ccMarcarListoEnvio(${r.id})"
                                style="font-size:.73rem;padding:2px 9px;background:#fffbeb;border:1px solid #fcd34d;color:#92400e;border-radius:6px;white-space:nowrap;cursor:pointer;">
                                <i class="fa-solid fa-check me-1"></i>Marcar listo para reenvío
                            </button>
                        </div>`;
            }
            if (r.estatus === 'listo_envio') {
                return `<div style="display:inline-flex;flex-direction:column;gap:5px;align-items:flex-start;">
                            <span class="badge rounded-pill" style="background:#fef9c3;color:#854d0e;font-size:.78rem;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Limite de envios rebasado — sin correo notificado
                            </span>
                            <button onclick="ccReenviarACartera(${r.id})" id="cc-hist-btn-${r.id}"
                                style="font-size:.73rem;padding:2px 9px;background:linear-gradient(135deg,#059669,#10b981);border:none;color:#fff;border-radius:6px;white-space:nowrap;cursor:pointer;">
                                <i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera
                            </button>
                        </div>`;
            }
            if (r.estatus === 'descartado') {
                return `<span class="badge rounded-pill" style="background:#ffedd5;color:#9a3412;font-size:.78rem;">
                            <i class="fa-solid fa-rotate-left me-1"></i>Devuelto a revisión
                        </span>`;
            }
            // en_proceso u otro
            return `<span class="badge rounded-pill" style="background:#dbeafe;color:#1e40af;font-size:.78rem;">
                        <i class="fa-solid fa-hourglass-half me-1"></i>En Proceso
                    </span>`;
        };

        const fechaMovimiento = (r) => {
            if (r.estatus === 'enviado_cartera') return fmtFecha(r.fecha_envio_cartera);
            if (r.estatus === 'descartado')      return fmtFecha(r.fecha_actualizacion);
            if (r.estatus === 'en_cola')         return fmtFecha(r.fecha_actualizacion);
            if (r.estatus === 'listo_envio')     return fmtFecha(r.fecha_actualizacion);
            return fmtFecha(r.fecha_alta);
        };

        /* Columna Acción — comentada, disponible para funciones futuras
        const accionCol = (r) => {
            if (r.estatus === 'en_cola') {
                return `<button onclick="ccMarcarListoEnvio(${r.id})"
                    style="font-size:.75rem;padding:3px 10px;background:#fffbeb;border:1px solid #fcd34d;color:#92400e;border-radius:6px;white-space:nowrap;">
                    <i class="fa-solid fa-check me-1"></i>Marcar listo
                </button>`;
            }
            if (r.estatus === 'listo_envio') {
                return `<button onclick="ccReenviarACartera(${r.id})" id="cc-hist-btn-${r.id}"
                    style="font-size:.75rem;padding:3px 10px;background:linear-gradient(135deg,#059669,#10b981);border:none;color:#fff;border-radius:6px;white-space:nowrap;">
                    <i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera
                </button>`;
            }
            return '';
        };
        */

        const quienMovio = (r) => {
            if (r.estatus === 'en_proceso') return esc(r.usuario_alta);
            return esc(r.usuario_actualizacion || r.usuario_alta);
        };

        const filas = rows.map(r => `
            <tr>
                <td class="ps-3" style="font-weight:600;color:#1e293b;white-space:nowrap;">
                    #${esc(r.id_credito)}
                </td>
                <td style="font-size:.88rem;">${esc(r.nombre_cliente)}</td>
                <td>${etiqueta(r)}</td>
                <td style="font-size:.83rem;color:#475569;">${quienMovio(r)}</td>
                <td style="font-size:.83rem;color:#475569;white-space:nowrap;">${fechaMovimiento(r)}</td>
            </tr>`).join('');

        wrap.innerHTML = `
        <div class="table-responsive">
            <table class="table table-hover mb-0 cc-table">
                <thead>
                    <tr>
                        <th class="ps-3">Crédito</th>
                        <th>Cliente</th>
                        <th>Estatus</th>
                        <th>Realizado por</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>${filas}</tbody>
            </table>
        </div>`;
        wrap.classList.remove('d-none');
    }

    document.getElementById('tab-historial-btn')
        .addEventListener('shown.bs.tab', cargarHistorial);

    document.getElementById('tab-convenios-btn')
        .addEventListener('shown.bs.tab', function () {
            cargarConvenios();
            if (_allRowsConv.length) {
                const t = document.getElementById('cc-input-buscar').value;
                if (t.trim()) ccFiltrar(t);
            }
        });

    /* ══════════════════════════════════
       MARCAR LISTO (en_cola → listo_envio)
    ══════════════════════════════════ */
    window.ccMarcarListoEnvio = function(idRegistro) {
        Swal.fire({
            title: '¿Marcar como listo para reenvío?',
            html: 'Confirma que el límite de envíos ya se restableció y este correo puede ser enviado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i>Sí, marcar listo',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch('/CierreCredito/marcarListoEnvio', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idRegistro}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({ icon: 'success', title: 'Listo', text: res.mensaje, timer: 1800, showConfirmButton: false });
                refrescarHistorial();
            })
            .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message }));
        });
    };

    /* ══════════════════════════════════
       REENVIAR A CARTERA (listo_envio → enviado_cartera / en_cola)
    ══════════════════════════════════ */
    window.ccReenviarACartera = function(idRegistro) {
        Swal.fire({
            title: '¿Reenviar a cartera?',
            html: 'Se intentará enviar el correo de notificación nuevamente.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i>Sí, reenviar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;
            const btn = document.getElementById(`cc-hist-btn-${idRegistro}`);
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Enviando...'; }
            fetch('/CierreCredito/reenviarACartera', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idRegistro}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                const emailOk  = !res.email_error;
                const emailMsg = res.email_error
                    ? `<div class="alert alert-warning mt-2 mb-0 py-1 px-2" style="font-size:.8rem;text-align:left;">
                           <i class="fa-solid fa-triangle-exclamation me-1"></i><strong>Error SMTP:</strong><br>${esc(res.email_error)}
                       </div>`
                    : '';
                Swal.fire({
                    title: emailOk ? '¡Enviado!' : 'En cola',
                    html: `<span>${res.mensaje}</span>${emailMsg}`,
                    icon: emailOk ? 'success' : 'warning',
                    timer: emailOk ? 2000 : undefined,
                    showConfirmButton: !emailOk
                });
                refrescarHistorial();
            })
            .catch(err => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane me-1"></i>Enviar a cartera'; }
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    document.getElementById('tab-en-proceso-btn')
        .addEventListener('shown.bs.tab', function () {
            cargarEnProceso();
            // Re-aplica filtro si los datos ya estaban cargados (segunda visita)
            if (_allRowsEp.length) {
                const t = document.getElementById('cc-input-buscar').value;
                if (t.trim()) ccFiltrar(t);
            }
        });

    document.getElementById('tab-env-finalizado-btn')
        .addEventListener('shown.bs.tab', function () {
            if (_allRows.length) {
                const t = document.getElementById('cc-input-buscar').value;
                if (t.trim()) ccFiltrar(t);
            }
        });

    // Carga inicial
    cargarEnviadoFinalizado();

    /* ══════════════════════════════════
       CONFIRMAR CIERRE (Tab 1 → crea registro en proceso)
    ══════════════════════════════════ */
    window.ccConfirmar = function(idRegistro, idCredito, nombreCliente) {
        Swal.fire({
            title: '¿Confirmar cierre?',
            html: `Se enviará el crédito <strong>${idCredito}</strong> al proceso de validación.<br>
                   <small class="text-muted">La gestora validadora revisará los documentos antes de enviarlo a cartera.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: '<i class="fa-solid fa-check me-1"></i>Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById(`cc-btn-${idRegistro}`);
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Enviando...'; }

            const params = new URLSearchParams({
                id_credito:     idCredito,
                nombre_cliente: nombreCliente || '',
                estatus:        'en_proceso'
            });
            fetch('/CierreCredito/crear', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                Swal.fire({
                    title: '¡Confirmado!',
                    text: `Crédito ${idCredito} enviado al proceso de validación.`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
                // Eliminar de _allRows y repintar para que no reaparezca en búsquedas
                _allRows = _allRows.filter(row => String(row.id) !== String(idRegistro));
                _pintarCards(_allRows);
                // Actualizar badge Tab 1
                const badge1 = document.getElementById('badge-env-finalizado');
                if (badge1) badge1.textContent = Math.max(0, (parseInt(badge1.textContent) || 1) - 1);
                // Incrementar badge tab 2 y resetear flag de carga lazy
                const badge2 = document.getElementById('badge-en-proceso');
                if (badge2) badge2.textContent = (parseInt(badge2.textContent) || 0) + 1;
                enProcesoCargado = false;
                refrescarHistorial();
            })
            .catch(err => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Confirmar cierre'; }
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    /* ══════════════════════════════════
       ENVIAR A CARTERA (Tab 2)
    ══════════════════════════════════ */
    window.ccEnviarACartera = function(idRegistro) {
        Swal.fire({
            title: '¿Enviar a cartera?',
            html: 'Se marcará este cierre como <strong>enviado a cartera</strong>.<br><small class="text-muted">El área de cartera actualizará el sistema.</small>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: '<i class="fa-solid fa-paper-plane me-1"></i>Sí, enviar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            const btn = document.getElementById(`cc-ep-btn-${idRegistro}`);
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Enviando...'; }

            fetch('/CierreCredito/enviarACartera', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idRegistro}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);

                const emailOk  = !res.email_error;
                const emailMsg = res.email_error
                    ? `<div class="alert alert-warning mt-2 mb-0 py-1 px-2" style="font-size:.8rem;text-align:left;">
                           <i class="fa-solid fa-triangle-exclamation me-1"></i><strong>Error de correo SMTP:</strong><br>${esc(res.email_error)}
                       </div>`
                    : '';

                Swal.fire({
                    title: emailOk ? '¡Enviado!' : 'Error de envío',
                    html: `<span>${res.mensaje}</span>${emailMsg}`,
                    icon: emailOk ? 'success' : 'warning',
                    timer: emailOk ? 2000 : undefined,
                    showConfirmButton: !emailOk
                });

                if (emailOk) {
                    const wrapper = document.getElementById(`cc-ep-wrapper-${idRegistro}`);
                    if (wrapper) { wrapper.remove(); }
                    const badge = document.getElementById('badge-en-proceso');
                    if (badge) badge.textContent = Math.max(0, (parseInt(badge.textContent) || 1) - 1);
                    // Si no quedan cards, mostrar estado vacío
                    const wrap2 = document.getElementById('wrap-ep-cards');
                    if (wrap2 && wrap2.querySelectorAll('[id^="cc-ep-card-"]').length === 0) {
                        wrap2.classList.add('d-none');
                        document.getElementById('empty-en-proceso').classList.remove('d-none');
                    }
                    refrescarHistorial();
                } else {
                    // El registro regresó a en_proceso — recargar tab para mostrar badge de envío fallido
                    cargarEnProceso();
                }
            })
            .catch(err => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Enviar a cartera'; }
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

    /* ══════════════════════════════════
       DESCARTAR (Tab 2 → regresa a Tab 1)
    ══════════════════════════════════ */
    window.ccDescartar = function(idRegistro) {
        Swal.fire({
            title: '¿Descartar registro?',
            html: 'El convenio regresará a <strong>Enviados Finalizados</strong>.<br><small class="text-muted">Se podrá confirmar nuevamente desde allí.</small>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: '<i class="fa-solid fa-rotate-left me-1"></i>Sí, descartar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch('/CierreCredito/descartar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${idRegistro}`
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) throw new Error(res.mensaje);
                // Eliminar card de Tab 2
                const wrapper = document.getElementById(`cc-ep-wrapper-${idRegistro}`);
                if (wrapper) { wrapper.remove(); }
                const badge = document.getElementById('badge-en-proceso');
                if (badge) badge.textContent = Math.max(0, (parseInt(badge.textContent) || 1) - 1);
                const wrap2 = document.getElementById('wrap-ep-cards');
                if (wrap2 && wrap2.querySelectorAll('[id^="cc-ep-card-"]').length === 0) {
                    wrap2.classList.add('d-none');
                    document.getElementById('empty-en-proceso').classList.remove('d-none');
                }
                // Refrescar Tab 1 para que reaparezca el convenio
                document.getElementById('loader-env-finalizado').classList.remove('d-none');
                document.getElementById('wrap-env-finalizado').classList.add('d-none');
                document.getElementById('empty-env-finalizado').classList.add('d-none');
                document.getElementById('empty-busqueda').classList.add('d-none');
                document.getElementById('cc-search-bar').style.display = 'none';
                document.getElementById('cc-input-buscar').value = '';
                cargarEnviadoFinalizado();
                refrescarHistorial();
                Swal.fire({ title: '¡Descartado!', text: 'El convenio regresó a Enviados Finalizados.', icon: 'success', timer: 2000, showConfirmButton: false });
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message });
            });
        });
    };

})();
</script>
