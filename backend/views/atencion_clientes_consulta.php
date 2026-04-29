<style>
/* ══════════════════════════════════════════
   ATENCIÓN A CLIENTES — ESTILOS
══════════════════════════════════════════ */
.ac-header-gradient {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    border-radius: 1rem;
    padding: 1.5rem 2rem;
    color: #fff;
    margin-bottom: 1.5rem;
}
.ac-header-gradient h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: #fff; }
.ac-header-gradient p  { margin: 0; font-size: 0.9rem; opacity: 0.85; color: #fff; }
.ac-header-gradient i  { color: #fff; }


/* ── Cards ── */
.ac-card {
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    background: #fff;
    margin-bottom: 1.25rem;
    overflow: hidden;
    box-shadow: 0 1px 6px rgba(0,0,0,.06);
    transition: box-shadow .2s;
}
.ac-card:hover { box-shadow: 0 4px 18px rgba(30,58,95,.12); }

.ac-card--cancelado {
    border-color: #e5e7eb;
    box-shadow: 0 0 0 1px rgba(220,38,38,.15), 0 2px 8px rgba(220,38,38,.1);
}
.ac-card--cancelado:hover { box-shadow: 0 4px 18px rgba(220,38,38,.18); }

.ac-card--en-transito {
    border-color: #e5e7eb;
    box-shadow: 0 0 0 1px rgba(37,99,235,.12), 0 2px 8px rgba(37,99,235,.08);
}

.ac-card-header {
    background: #fff;
    border-bottom: 1px solid #eef2f7;
    padding: .62rem 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.ac-card--cancelado .ac-card-header {
    background: #fff;
}
.ac-card--en-transito .ac-card-header {
    background: #fff;
}
.ac-card-header .ac-credito-id {
    color: #1f2937;
    font-weight: 700;
    font-size: .92rem;
    letter-spacing: .1px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1;
}
.ac-card-header .ac-credito-id small {
    font-weight: 600;
    font-size: .7rem;
    color: #6b7280;
    opacity: 1;
    margin-left: .35rem;
}

.ac-card-body {
    padding: .62rem .9rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .8rem;
}
.ae-list-grid {
    flex: 1;
    display: grid;
    grid-template-columns: minmax(210px, 1fr) minmax(320px, 1.35fr) minmax(210px, 1fr);
    gap: .25rem 1rem;
}
.ae-list-cell {
    display: flex;
    flex-direction: column;
    gap: .1rem;
    min-width: 0;
}
.ae-list-cell .ac-lbl {
    color: #6b7280;
    font-size: .68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    line-height: 1.1;
}
.ae-list-cell .ac-val {
    color: #1f2937;
    font-weight: 600;
    font-size: .82rem;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ae-estatus-dict .ac-val {
    white-space: normal;
    overflow: visible;
    text-overflow: unset;
}
.ae-main-meta {
    display: flex;
    flex-direction: column;
    gap: .28rem;
}
.ae-main-folio {
    font-size: .78rem;
    font-weight: 700;
    color: #b45309;
}
.ae-main-credito {
    font-size: .84rem;
    font-weight: 700;
    color: #1f2937;
}
.ae-list-nombre .ac-val { text-transform: uppercase; }
.ae-list-muted {
    color: #9ca3af;
    font-style: italic;
    font-weight: 500;
}
.ae-list-action {
    min-width: 142px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ac-detail-row {
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: .2rem;
    font-size: .82rem;
}
.ac-detail-row .ac-lbl {
    color: #6b7280;
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
    min-width: 128px;
}
.ac-detail-row .ac-val {
    color: #1f2937;
    font-weight: 600;
}

.ac-card-footer {
    border-top: 1px solid #eef2f7;
    padding: .55rem .85rem;
    display: flex;
    justify-content: flex-end;
    background: #fff;
    gap: .5rem;
}

/* ── Botón Dictaminar ── */
.ac-btn-dictaminar {
    background: #2563eb;
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: .85rem;
    padding: .45rem 1.4rem;
    border-radius: 2rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    transition: opacity .2s, transform .15s;
}
.ac-btn-dictaminar:hover  { opacity: .9; transform: translateY(-1px); }
.ac-btn-dictaminar:active { transform: translateY(0); }

/* ── Badges de dictamen ── */
.ac-badge-transito       { background: #dbeafe; color: #1e40af; font-size: .78rem; font-weight: 700; border-radius: 20px; padding: 2px 10px; }
.ac-badge-cancelado      { background: #fee2e2; color: #b91c1c; font-size: .78rem; font-weight: 700; border-radius: 20px; padding: 2px 10px; }
.ac-badge-pendiente      { background: #fef3c7; color: #92400e; font-size: .78rem; font-weight: 700; border-radius: 20px; padding: 2px 10px; }
.ac-badge-ultimo-intento { background: #fde8e8; color: #991b1b; font-size: .78rem; font-weight: 700; border-radius: 20px; padding: 2px 10px; }

/* ── Modal Dictaminar ── */
.ac-modal-sep {
    border: none;
    border-top: 2px solid #f59e0b;
    opacity: 1;
    margin: 1rem 0;
}
.ac-modal-label {
    font-size: .8rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: .03em;
    margin-bottom: .25rem;
}
.ac-modal-input {
    border: 1px solid #cbd5e1;
    border-radius: .45rem;
    font-size: .875rem;
    padding: .45rem .75rem;
    width: 100%;
    color: #1e293b;
    transition: border-color .15s, box-shadow .15s;
}
.ac-modal-input:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37,99,235,.12);
}
select.ac-modal-input { cursor: pointer; }
textarea.ac-modal-input { resize: vertical; min-height: 80px; }

/* ── Modal resumen dictamen (sólo lectura) ── */
.ac-resumen-row {
    display: flex;
    align-items: flex-start;
    gap: .5rem;
    padding: .35rem 0;
    border-bottom: 1px solid #f1f5f9;
    font-size: .875rem;
}
.ac-resumen-row:last-child { border-bottom: none; }
.ac-resumen-lbl {
    color: #64748b;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    min-width: 155px;
    flex-shrink: 0;
}
.ac-resumen-val { color: #1e293b; font-weight: 500; }

/* ── Dark mode ── */
body.dark-mode .ac-card               { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-card--cancelado    { border-color: #f87171; box-shadow: 0 0 0 1px rgba(248,113,113,.2); }
body.dark-mode .ac-card--en-transito  { border-color: #60a5fa; box-shadow: 0 0 0 1px rgba(96,165,250,.2); }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-header        { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-card-header .ac-credito-id { color: #e2e8f0; }
body.dark-mode .ac-card-header .ac-credito-id small { color: #94a3b8; }
body.dark-mode .ac-card-footer        { background: #111827; border-color: #1f2937; }
body.dark-mode .ac-modal-input        { background: #1e293b; border-color: #475569; color: #e2e8f0; }
body.dark-mode .ac-modal-input:focus  { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
body.dark-mode .ac-modal-label        { color: #94a3b8; }
body.dark-mode .ac-resumen-row        { border-color: #334155; }
body.dark-mode .ac-resumen-lbl        { color: #94a3b8; }
body.dark-mode .ac-resumen-val        { color: #e2e8f0; }
body.dark-mode .ac-badge-transito        { background: rgba(30,64,175,.35); color: #93c5fd; }
body.dark-mode .ac-badge-cancelado       { background: rgba(185,28,28,.35); color: #fca5a5; }
body.dark-mode .ac-badge-pendiente       { background: rgba(146,64,14,.35); color: #fcd34d; }
body.dark-mode .ac-badge-ultimo-intento  { background: rgba(153,27,27,.35); color: #fca5a5; }
body.dark-mode .ae-list-cell .ac-lbl { color: #94a3b8; }
body.dark-mode .ae-list-cell .ac-val { color: #e2e8f0; }
body.dark-mode .ae-list-muted { color: #64748b; }
body.dark-mode .ae-main-folio { color: #fcd34d; }
body.dark-mode .ae-main-credito { color: #e2e8f0; }

@media (max-width: 991.98px) {
    .ae-list-grid {
        grid-template-columns: repeat(2, minmax(220px, 1fr));
    }
}
@media (max-width: 767.98px) {
    .ac-card-body {
        flex-direction: column;
        align-items: stretch;
    }
    .ae-list-grid {
        grid-template-columns: 1fr;
    }
    .ae-list-action {
        justify-content: flex-end;
        margin-top: .2rem;
    }
}
</style>

<div class="container-fluid py-4">

    <!-- Encabezado -->
    <div class="ac-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-headset fa-2x"></i>
        <div>
            <h4>Retenciones</h4>
            <p>Gestión de llamadas y dictámenes para operaciones en retenciones</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-pills flex-column flex-md-row mb-3 gap-md-0 gap-2 border-0" id="acTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-entrantes-btn"
                            data-bs-toggle="tab" data-bs-target="#tabEntrantes"
                            type="button" role="tab">
                        <i class="fa-solid fa-inbox me-1"></i>Bandeja de entrada
                        <span class="badge bg-label-primary ms-1" id="ac-badge-entrantes" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-dictaminados-btn"
                            data-bs-toggle="tab" data-bs-target="#tabDictaminados"
                            type="button" role="tab">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Dictaminados
                        <span class="badge bg-label-secondary ms-1" id="ac-badge-dictaminados" style="display:none;"></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-pendientes-btn"
                            data-bs-toggle="tab" data-bs-target="#tabPendientes"
                            type="button" role="tab">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i>Pendientes
                        <span class="badge bg-label-warning ms-1" id="ac-badge-pendientes" style="display:none;"></span>
                    </button>
                </li>
            </ul>
        </div>

        <div class="tab-content p-3" id="acTabContent">

            <!-- ── TAB ENTRANTES ── -->
            <div class="tab-pane fade show active" id="tabEntrantes" role="tabpanel">
                <div id="ac-loader-entrantes" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ac-lista-entrantes"></div>
            </div>

            <!-- ── TAB DICTAMINADOS ── -->
            <div class="tab-pane fade" id="tabDictaminados" role="tabpanel">
                <div id="ac-loader-dictaminados" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ac-lista-dictaminados"></div>
            </div>

            <!-- ── TAB PENDIENTES ── -->
            <div class="tab-pane fade" id="tabPendientes" role="tabpanel">
                <div id="ac-loader-pendientes" class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm me-2"></div>Cargando...
                </div>
                <div id="ac-lista-pendientes"></div>
            </div>

        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════
     MODAL: DICTAMINAR LLAMADA
════════════════════════════════════════════ -->
<div class="modal fade" id="modalAcDictaminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#1e3a5f 0%,#2563eb 100%);border-radius:.4rem .4rem 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fa-solid fa-phone-volume me-2"></i>Dictaminar llamada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="ac-dict-id-operacion">
                <input type="hidden" id="ac-dict-id-credito">

                <!-- Fila 1 -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="ac-modal-label">Llamada a</label>
                        <select class="ac-modal-input" id="ac-dict-llamada-a" disabled>
                            <option value="">Cargando contactos…</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="ac-modal-label">Número</label>
                        <input type="text" class="ac-modal-input" id="ac-dict-numero"
                               placeholder="Se llena al elegir contacto" maxlength="20" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="ac-modal-label">Persona contactada</label>
                        <input type="text" class="ac-modal-input" id="ac-dict-persona-contactada"
                               placeholder="Nombre completo" maxlength="200">
                    </div>
                </div>

                <hr class="ac-modal-sep">

                <!-- Fila 2 -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="ac-modal-label">Tipo contacto</label>
                        <select class="ac-modal-input" id="ac-dict-tipo-contacto">
                            <option value="">— Seleccione —</option>
                            <option>Contacto</option>
                            <option>Sin contacto</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="ac-modal-label">Resultado</label>
                        <select class="ac-modal-input" id="ac-dict-resultado">
                            <option value="">— Seleccione tipo primero —</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="ac-modal-label">Dictamen</label>
                        <select class="ac-modal-input" id="ac-dict-dictamen">
                            <option value="">— Seleccione resultado primero —</option>
                        </select>
                    </div>
                </div>

                <!-- Fila 3 -->
                <div class="row g-3 mt-0">
                    <div class="col-md-4">
                        <label class="ac-modal-label">Plataforma</label>
                        <select class="ac-modal-input" id="ac-dict-plataforma">
                            <option value="">— Seleccione —</option>
                            <option>Teléfono</option>
                            <option>WhatsApp</option>
                            <option>Correo electrónico</option>
                            <option>Visita domiciliaria</option>
                            <option>Otro</option>
                        </select>
                    </div>
                </div>

                <!-- Comentarios -->
                <div class="mt-3">
                    <label class="ac-modal-label">Comentarios</label>
                    <textarea class="ac-modal-input" id="ac-dict-comentarios"
                              placeholder="Detalle de la gestión..."></textarea>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;">
                <button type="button" class="btn fw-bold"
                        style="background:#fbbf24;color:#78350f;border:none;border-radius:2rem;padding:.45rem 1.4rem;"
                        data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i>Cancelar
                </button>
                <button type="button" class="btn fw-bold" id="ac-btn-guardar-dictamen"
                        style="background:linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 100%);color:#fff;border:none;border-radius:2rem;padding:.45rem 1.6rem;">
                    <i class="fa-solid fa-check me-1"></i>Guardar dictamen
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════
     MODAL: RESUMEN DEL DICTAMEN (sólo lectura)
════════════════════════════════════════════ -->
<div class="modal fade" id="modalAcResumenDictamen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header" style="background:linear-gradient(135deg,#7f1d1d 0%,#dc2626 100%);border-radius:.4rem .4rem 0 0;">
                <h5 class="modal-title text-white fw-bold">
                    <i class="fa-solid fa-file-circle-check me-2"></i>Resumen del dictamen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="ac-resumen-body">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm"></div>
                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;">
                <button type="button" class="btn fw-bold"
                        style="background:#e2e8f0;color:#475569;border:none;border-radius:2rem;padding:.45rem 1.4rem;"
                        data-bs-dismiss="modal">
                    <i class="fa-solid fa-xmark me-1"></i>Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    // ──────────────────────────────────────────────────────────────────
    // ESTADO LOCAL
    // ──────────────────────────────────────────────────────────────────
    let _acEntrMap    = {};   // id → item completo de entrantes
    let _acPendMap    = {};   // id → item completo de pendientes
    let _acItemActual = null; // item del modal abierto
    let _acItemSource = 'entrante'; // 'entrante' | 'pendiente'

    const AC_MAX_INTENTOS = 4;

    // ──────────────────────────────────────────────────────────────────
    // HELPERS
    // ──────────────────────────────────────────────────────────────────
    function acEsc(s) {
        if (s == null) return '';
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function acFmt(v) {
        if (v == null || v === '') return '—';
        const n = parseFloat(v);
        if (isNaN(n)) return acEsc(v);
        return n.toLocaleString('es-MX', { style: 'currency', currency: 'MXN' });
    }

    // ──────────────────────────────────────────────────────────────────
    // INIT
    // ──────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        acCargarEntrantes();

        document.getElementById('tab-dictaminados-btn').addEventListener('shown.bs.tab', function () {
            acCargarDictaminados();
        });

        document.getElementById('tab-pendientes-btn').addEventListener('shown.bs.tab', function () {
            acCargarPendientes();
        });

        document.getElementById('ac-btn-guardar-dictamen')
            .addEventListener('click', acGuardarDictamen);
    });

    // ──────────────────────────────────────────────────────────────────
    // CARGAR ENTRANTES
    // ──────────────────────────────────────────────────────────────────
    function acCargarEntrantes() {
        const loader = document.getElementById('ac-loader-entrantes');
        const lista  = document.getElementById('ac-lista-entrantes');

        loader.style.display = 'block';
        lista.innerHTML      = '';

        fetch('/AtencionClientes/obtenerEntrantes', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al cargar');

                const badge = document.getElementById('ac-badge-entrantes');
                if (data.datos.length > 0) {
                    badge.textContent    = data.datos.length;
                    badge.style.display  = '';
                } else {
                    badge.style.display  = 'none';
                }

                if (data.datos.length === 0) {
                    lista.innerHTML = acSinDatos('No hay operaciones en Retenciones en este momento.');
                    return;
                }

                _acEntrMap = {};
                data.datos.forEach(item => { _acEntrMap[item.id] = item; });
                lista.innerHTML = data.datos.map(acRenderCardEntrante).join('');
            })
            .catch(err => {
                lista.innerHTML = `<div class="alert alert-danger">${acEsc(err.message)}</div>`;
            })
            .finally(() => { loader.style.display = 'none'; });
    }

    // ──────────────────────────────────────────────────────────────────
    // CARGAR DICTAMINADOS
    // ──────────────────────────────────────────────────────────────────
    function acCargarDictaminados() {
        const loader = document.getElementById('ac-loader-dictaminados');
        const lista  = document.getElementById('ac-lista-dictaminados');

        loader.style.display = 'block';
        lista.innerHTML      = '';

        fetch('/AtencionClientes/obtenerDictaminados', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al cargar');

                const badge = document.getElementById('ac-badge-dictaminados');
                if (data.datos.length > 0) {
                    badge.textContent   = data.datos.length;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }

                if (data.datos.length === 0) {
                    lista.innerHTML = acSinDatos('No hay operaciones dictaminadas.');
                    return;
                }

                lista.innerHTML = data.datos.map(acRenderCardDictaminado).join('');
            })
            .catch(err => {
                lista.innerHTML = `<div class="alert alert-danger">${acEsc(err.message)}</div>`;
            })
            .finally(() => { loader.style.display = 'none'; });
    }

    // ──────────────────────────────────────────────────────────────────
    // CARGAR PENDIENTES
    // ──────────────────────────────────────────────────────────────────
    function acCargarPendientes() {
        const loader = document.getElementById('ac-loader-pendientes');
        const lista  = document.getElementById('ac-lista-pendientes');

        loader.style.display = 'block';
        lista.innerHTML      = '';

        fetch('/AtencionClientes/obtenerPendientes', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al cargar');

                const badge = document.getElementById('ac-badge-pendientes');
                if (data.datos.length > 0) {
                    badge.textContent   = data.datos.length;
                    badge.style.display = '';
                } else {
                    badge.style.display = 'none';
                }

                if (data.datos.length === 0) {
                    lista.innerHTML = acSinDatos('No hay operaciones pendientes en este momento.');
                    return;
                }

                _acPendMap = {};
                data.datos.forEach(item => { _acPendMap[item.id] = item; });
                lista.innerHTML = data.datos.map(acRenderCardPendiente).join('');
            })
            .catch(err => {
                lista.innerHTML = `<div class="alert alert-danger">${acEsc(err.message)}</div>`;
            })
            .finally(() => { loader.style.display = 'none'; });
    }

    // ──────────────────────────────────────────────────────────────────
    // RENDER CARD — ENTRANTE
    // ──────────────────────────────────────────────────────────────────
    function acRenderCardEntrante(item) {
        const g  = item.gestor_nombre
            ? acEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">Sin asignar</span>';
        const fa = item.fecha_asignacion
            ? acEsc(item.fecha_asignacion)
            : '<span class="ae-list-muted">—</span>';
        const nombreCliente = item.nombre_cliente
            ? acEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? acEsc(item.folio) : '—';
        const idOp = parseInt(item.id, 10) || 0;

        return `
        <div class="ac-card">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${acEsc(String(item.id_credito))}</span>
                    </div>
                    <div class="ae-list-cell ae-list-gestor">
                        <span class="ac-lbl">Gestor a cargo</span>
                        <span class="ac-val">${g}</span>
                    </div>
                    <div class="ae-list-cell ae-list-asig">
                        <span class="ac-lbl">Asignación realizada</span>
                        <span class="ac-val">${fa}</span>
                    </div>
                    <div class="ae-list-cell ae-list-nombre">
                        <span class="ac-lbl">Nombre</span>
                        <span class="ac-val">${nombreCliente}</span>
                    </div>
                </div>
                <div class="ae-list-action">
                    <button type="button" class="ac-btn-dictaminar"
                            onclick="acAbrirDictaminar(${idOp})" ${idOp ? '' : 'disabled'}>
                        <i class="fa-solid fa-phone-volume me-1"></i>Dictaminar
                    </button>
                </div>
            </div>
        </div>`;
    }

    // ──────────────────────────────────────────────────────────────────
    // RENDER CARD — DICTAMINADO
    // ──────────────────────────────────────────────────────────────────
    function acRenderCardDictaminado(item) {
        const esTransito  = item.estatus === 'en_transito';
        const esCancelado = item.estatus === 'cancelado';
        const extraClass  = esTransito ? 'ac-card--en-transito' : (esCancelado ? 'ac-card--cancelado' : '');
        const badgeHtml   = esTransito
            ? `<span class="ac-badge-transito"><i class="fa-solid fa-truck-fast me-1"></i>En tránsito</span>`
            : `<span class="ac-badge-cancelado"><i class="fa-solid fa-ban me-1"></i>Cancelado</span>`;

        const dictTxt = item.dictamen
            ? acEsc(item.dictamen)
            : '<span class="ae-list-muted">—</span>';
        const fechaD = item.fecha_dictamen
            ? acEsc(item.fecha_dictamen)
            : '<span class="ae-list-muted">—</span>';
        const g = item.gestor_nombre
            ? acEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">Sin asignar</span>';
        const nombreCliente = item.nombre_cliente
            ? acEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio = item.folio ? acEsc(item.folio) : '—';

        return `
        <div class="ac-card ${extraClass}">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${acEsc(String(item.id_credito))}</span>
                    </div>
                    <div class="ae-list-cell ae-list-gestor">
                        <span class="ac-lbl">Gestor a cargo</span>
                        <span class="ac-val">${g}</span>
                    </div>
                    <div class="ae-list-cell ae-list-asig">
                        <span class="ac-lbl">Fecha dictamen</span>
                        <span class="ac-val">${fechaD}</span>
                    </div>
                    <div class="ae-list-cell ae-list-nombre">
                        <span class="ac-lbl">Nombre</span>
                        <span class="ac-val">${nombreCliente}</span>
                    </div>
                    <div class="ae-list-cell ae-estatus-dict">
                        <span class="ac-lbl">Estatus dictamen</span>
                        <span class="ac-val">${badgeHtml}</span>
                    </div>
                    <div class="ae-list-cell">
                        <span class="ac-lbl">Dictamen</span>
                        <span class="ac-val">${dictTxt}</span>
                    </div>
                    ${item.comentarios ? `
                    <div class="ae-list-cell" style="grid-column: 1 / -1;">
                        <span class="ac-lbl">Comentarios</span>
                        <span class="ac-val" style="white-space:pre-line;">${acEsc(item.comentarios)}</span>
                    </div>` : ''}
                </div>
            </div>
        </div>`;
    }

    // ──────────────────────────────────────────────────────────────────
    // RENDER CARD — PENDIENTE
    // ──────────────────────────────────────────────────────────────────
    function acRenderCardPendiente(item) {
        const intentos    = parseInt(item.intentos_realizados || 0, 10);
        const esUltimo    = intentos === AC_MAX_INTENTOS - 1;
        const sinIntentos = intentos >= AC_MAX_INTENTOS;

        let badgeHtml;
        if (sinIntentos) {
            badgeHtml = `<span class="ac-badge-cancelado"><i class="fa-solid fa-ban me-1"></i>Sin intentos disponibles</span>`;
        } else if (esUltimo) {
            badgeHtml = `<span class="ac-badge-ultimo-intento"><i class="fa-solid fa-triangle-exclamation me-1"></i>Último intento (${intentos + 1}&nbsp;/&nbsp;${AC_MAX_INTENTOS})</span>`;
        } else {
            badgeHtml = `<span class="ac-badge-pendiente"><i class="fa-solid fa-clock me-1"></i>Intento ${intentos + 1}&nbsp;/&nbsp;${AC_MAX_INTENTOS}</span>`;
        }

        const g  = item.gestor_nombre
            ? acEsc(item.gestor_nombre)
            : '<span class="ae-list-muted">Sin asignar</span>';
        const nombreCliente = item.nombre_cliente
            ? acEsc(item.nombre_cliente)
            : '<span class="ae-list-muted">Sin nombre</span>';
        const folio  = item.folio ? acEsc(item.folio) : '—';
        const fechaU = item.fecha_ultimo_intento
            ? acEsc(item.fecha_ultimo_intento)
            : '<span class="ae-list-muted">—</span>';
        const idOp   = parseInt(item.id, 10) || 0;

        return `
        <div class="ac-card">
            <div class="ac-card-body">
                <div class="ae-list-grid">
                    <div class="ae-list-cell ae-main-meta">
                        <span class="ae-main-folio">${folio}</span>
                        <span class="ae-main-credito"># Crédito ${acEsc(String(item.id_credito))}</span>
                    </div>
                    <div class="ae-list-cell ae-list-gestor">
                        <span class="ac-lbl">Gestor a cargo</span>
                        <span class="ac-val">${g}</span>
                    </div>
                    <div class="ae-list-cell ae-list-asig">
                        <span class="ac-lbl">Último intento</span>
                        <span class="ac-val">${fechaU}</span>
                    </div>
                    <div class="ae-list-cell ae-list-nombre">
                        <span class="ac-lbl">Nombre</span>
                        <span class="ac-val">${nombreCliente}</span>
                    </div>
                    <div class="ae-list-cell" style="grid-column: span 2;">
                        <span class="ac-lbl">Intentos realizados</span>
                        <span class="ac-val">${badgeHtml}</span>
                    </div>
                </div>
                <div class="ae-list-action">
                    <button type="button" class="ac-btn-dictaminar"
                            onclick="acAbrirDictaminar(${idOp}, 'pendiente')" ${idOp ? '' : 'disabled'}>
                        <i class="fa-solid fa-phone-volume me-1"></i>Dictaminar
                    </button>
                </div>
            </div>
        </div>`;
    }

    // ──────────────────────────────────────────────────────────────────
    // ABRIR MODAL DICTAMINAR
    // ──────────────────────────────────────────────────────────────────
    // Opciones de resultado según tipo-contacto
    const AC_RESULTADO_OPTS = {
        'Contacto':     ['Contacto efectivo'],
        'Sin contacto': ['Buzón de voz', 'No contesta', 'Fuera de servicio', 'Número equivocado'],
    };

    // Opciones de dictamen según resultado (dinámico para Contacto efectivo)
    const AC_DICTAMEN_OPTS_STATIC = {
        'Buzón de voz':       ['Pendiente de contacto', 'No localizado'],
        'No contesta':        ['Pendiente de contacto', 'No localizado'],
        'Fuera de servicio':  ['Pendiente de contacto', 'No localizado'],
        'Número equivocado':  ['Pendiente de contacto', 'No localizado'],
    };

    function acGetDictamenOpts(resultado) {
        if (resultado !== 'Contacto efectivo') {
            return AC_DICTAMEN_OPTS_STATIC[resultado] || [];
        }
        const intentos = (_acItemActual && _acItemActual.intentos_realizados != null)
            ? parseInt(_acItemActual.intentos_realizados, 10) : 0;
        const base = ['Autorizado para recolección', 'Cancelado, promesa de pago'];
        if (intentos >= AC_MAX_INTENTOS) {
            base.push('Cancelamiento total (sin intentos)');
        } else if (intentos === AC_MAX_INTENTOS - 1) {
            base.push('Pendiente, último intento');
        } else {
            const quedan = AC_MAX_INTENTOS - intentos - 1;
            base.push('Pendiente, nuevo intento (quedan ' + quedan + ')');
        }
        return base;
    }

    function acRebuildSelect(selectId, opciones, placeholder) {
        const sel = document.getElementById(selectId);
        sel.innerHTML = `<option value="">${placeholder}</option>`;
        opciones.forEach(o => {
            const opt = document.createElement('option');
            opt.value = opt.textContent = o;
            sel.appendChild(opt);
        });
        sel.value = '';
    }

    // Mapa: value → {telefono, nombre} de los contactos cargados dinámicamente
    let _acContactosMap = {};

    window.acAbrirDictaminar = function (idOperacion, source) {
        source        = source || 'entrante';
        _acItemSource = source;
        _acItemActual = source === 'pendiente'
            ? (_acPendMap[idOperacion] || null)
            : (_acEntrMap[idOperacion] || null);
        const idCredito = _acItemActual ? (_acItemActual.id_credito || 0) : 0;

        document.getElementById('ac-dict-id-operacion').value = idOperacion;
        document.getElementById('ac-dict-id-credito').value   = idCredito;

        // Limpiar
        const selLlamada = document.getElementById('ac-dict-llamada-a');
        selLlamada.innerHTML = '<option value="">Cargando contactos…</option>';
        selLlamada.disabled  = true;
        document.getElementById('ac-dict-numero').value             = '';
        document.getElementById('ac-dict-persona-contactada').value = '';
        document.getElementById('ac-dict-comentarios').value        = '';
        document.getElementById('ac-dict-tipo-contacto').value      = '';
        document.getElementById('ac-dict-plataforma').value         = '';
        acRebuildSelect('ac-dict-resultado', [], '— Seleccione tipo primero —');
        acRebuildSelect('ac-dict-dictamen',  [], '— Seleccione resultado primero —');

        new bootstrap.Modal(document.getElementById('modalAcDictaminar')).show();

        // Cargar contactos desde el mismo endpoint que Estado de Cuenta
        _acContactosMap = {};
        fetch('/EstadoCuenta/getOpcionesContactoDictamenLlamada', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_credito: idCredito })
        })
            .then(r => r.json())
            .then(resp => {
                selLlamada.innerHTML = '<option value="">— Seleccione —</option>';
                if (resp.success && Array.isArray(resp.datos) && resp.datos.length) {
                    resp.datos.forEach(op => {
                        _acContactosMap[op.value] = op;
                        const opt = document.createElement('option');
                        opt.value       = op.value;
                        opt.textContent = op.label + (op.telefono ? ' — ' + op.telefono : '');
                        selLlamada.appendChild(opt);
                    });
                } else {
                    // Fallback genérico si el endpoint no devuelve datos
                    [['cliente', 'Cliente (titular)'], ['referencia_1', 'Referencia 1'],
                     ['referencia_2', 'Referencia 2'], ['otro', 'Otro']].forEach(([v, l]) => {
                        const o = document.createElement('option');
                        o.value = v; o.textContent = l;
                        selLlamada.appendChild(o);
                    });
                }
                selLlamada.disabled = false;
            })
            .catch(() => {
                selLlamada.innerHTML = '<option value="">— No se pudo cargar —</option>';
                selLlamada.disabled  = false;
            });
    };

    // ── Auto-fill al seleccionar "Llamada a" ──────────────────────────
    document.getElementById('ac-dict-llamada-a').addEventListener('change', function () {
        const contacto = _acContactosMap[this.value];
        if (contacto) {
            document.getElementById('ac-dict-numero').value
                = contacto.telefono || '';
            document.getElementById('ac-dict-persona-contactada').value
                = contacto.nombre   || '';
        } else {
            document.getElementById('ac-dict-numero').value             = '';
            document.getElementById('ac-dict-persona-contactada').value = '';
        }
    });

    // ── Cascada: Tipo contacto → Resultado ───────────────────────────
    document.getElementById('ac-dict-tipo-contacto').addEventListener('change', function () {
        const opciones = AC_RESULTADO_OPTS[this.value] || [];
        acRebuildSelect('ac-dict-resultado', opciones,
            opciones.length ? '— Seleccione resultado —' : '— Seleccione tipo primero —');
        acRebuildSelect('ac-dict-dictamen', [], '— Seleccione resultado primero —');
    });

    // ── Cascada: Resultado → Dictamen ────────────────────────────────
    document.getElementById('ac-dict-resultado').addEventListener('change', function () {
        const opciones = acGetDictamenOpts(this.value);
        acRebuildSelect('ac-dict-dictamen', opciones,
            opciones.length ? '— Seleccione dictamen —' : '— Seleccione resultado primero —');
    });

    // ──────────────────────────────────────────────────────────────────
    // GUARDAR DICTAMEN
    // ──────────────────────────────────────────────────────────────────
    function acGuardarDictamen() {
        const idOperacion = parseInt(document.getElementById('ac-dict-id-operacion').value, 10);
        if (!idOperacion) return;

        const dictamen = document.getElementById('ac-dict-dictamen').value;
        if (!dictamen) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Seleccione un dictamen.', confirmButtonColor: '#2563eb' });
            return;
        }
        const selLlamada = document.getElementById('ac-dict-llamada-a');
        const llamadaA   = selLlamada.value;
        // Guardar el label visible (ej. "Cliente (titular)"), no el value interno
        const llamadaALabel = selLlamada.selectedOptions[0]?.textContent?.split(' — ')[0].trim() || llamadaA;
        if (!llamadaA) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Seleccione a quién se llamó.', confirmButtonColor: '#2563eb' });
            return;
        }

        const payload = {
            id_operacion:       idOperacion,
            source:             _acItemSource,
            llamada_a:          llamadaALabel,
            numero:             document.getElementById('ac-dict-numero').value,
            persona_contactada: document.getElementById('ac-dict-persona-contactada').value,
            tipo_contacto:      document.getElementById('ac-dict-tipo-contacto').value,
            resultado:          document.getElementById('ac-dict-resultado').value,
            dictamen:           dictamen,
            plataforma:         document.getElementById('ac-dict-plataforma').value,
            comentarios:        document.getElementById('ac-dict-comentarios').value,
        };

        const btn = document.getElementById('ac-btn-guardar-dictamen');
        btn.disabled    = true;
        btn.innerHTML   = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando…';

        fetch('/AtencionClientes/dictaminar', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body:    JSON.stringify(payload),
        })
            .then(r => r.json())
            .then(data => {
                if (!data.success) throw new Error(data.message || 'Error al guardar.');

                bootstrap.Modal.getInstance(document.getElementById('modalAcDictaminar')).hide();

                let mensajeExtra = '';
                if (data.estatus_nuevo === 'en_transito') {
                    mensajeExtra = 'La operación fue trasladada a <strong>Recibido</strong>.';
                } else if (data.estatus_nuevo === 'cancelado') {
                    mensajeExtra = 'La operación quedó marcada como <strong>Cancelado</strong> en Retenciones.';
                } else if (data.estatus_nuevo === 'pendiente') {
                    mensajeExtra = 'La operación fue movida a la pestaña de <strong>Pendientes</strong>.';
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Dictamen guardado',
                    html: 'El dictamen fue registrado correctamente.' + (mensajeExtra ? '<br><small class="text-muted">' + mensajeExtra + '</small>' : ''),
                    confirmButtonColor: '#2563eb',
                }).then(() => {
                    acCargarEntrantes();
                    acCargarPendientes();
                });
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#2563eb' });
            })
            .finally(() => {
                btn.disabled  = false;
                btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Guardar dictamen';
            });
    }

    // ──────────────────────────────────────────────────────────────────
    // ESTADO VACÍO
    // ──────────────────────────────────────────────────────────────────
    function acSinDatos(msg) {
        return `<div class="text-center py-5 text-muted">
            <i class="fa-regular fa-folder-open fa-2x mb-2 d-block"></i>
            <span style="font-size:.9rem;">${acEsc(msg)}</span>
        </div>`;
    }

})();
</script>
