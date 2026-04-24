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

/* ── Pestañas ── */
.ac-nav-tabs .nav-link {
    font-weight: 600;
    color: #475569;
    border-radius: 0.5rem 0.5rem 0 0;
    padding: 0.6rem 1.4rem;
}
.ac-nav-tabs .nav-link.active {
    color: #1d4ed8;
    border-bottom-color: #fff !important;
}
.ac-nav-tabs .nav-link:hover:not(.active) {
    color: #1d4ed8;
    background: #eff6ff;
}

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
    border-color: #fca5a5;
    box-shadow: 0 0 0 1px rgba(220,38,38,.15), 0 2px 8px rgba(220,38,38,.1);
}
.ac-card--cancelado:hover { box-shadow: 0 4px 18px rgba(220,38,38,.18); }

.ac-card--en-transito {
    border-color: #93c5fd;
    box-shadow: 0 0 0 1px rgba(37,99,235,.12), 0 2px 8px rgba(37,99,235,.08);
}

.ac-card-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
    padding: 0.75rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}
.ac-card--cancelado .ac-card-header {
    background: linear-gradient(135deg, #7f1d1d 0%, #dc2626 100%);
}
.ac-card--en-transito .ac-card-header {
    background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
}
.ac-card-header .ac-credito-id {
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: .3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
    flex: 1;
}
.ac-card-header .ac-credito-id small {
    font-weight: 400;
    font-size: .75rem;
    opacity: .8;
    margin-left: .35rem;
}

.ac-card-body {
    padding: 1.1rem 1.25rem;
}
.ac-detail-row {
    display: flex;
    align-items: baseline;
    gap: .5rem;
    margin-bottom: .45rem;
    font-size: .875rem;
}
.ac-detail-row .ac-lbl {
    color: #64748b;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
    min-width: 140px;
}
.ac-detail-row .ac-val {
    color: #1e293b;
    font-weight: 500;
}

.ac-card-footer {
    border-top: 1px solid #e2e8f0;
    padding: .75rem 1.25rem;
    display: flex;
    justify-content: flex-end;
    background: #f8fafc;
    gap: .5rem;
}

/* ── Botón Dictaminar ── */
.ac-btn-dictaminar {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
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
.ac-badge-transito  { background: #dbeafe; color: #1e40af; font-size: .78rem; font-weight: 700; border-radius: 20px; padding: 2px 10px; }
.ac-badge-cancelado { background: #fee2e2; color: #b91c1c; font-size: .78rem; font-weight: 700; border-radius: 20px; padding: 2px 10px; }

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
body.dark-mode .ac-nav-tabs .nav-link        { color: #94a3b8; }
body.dark-mode .ac-nav-tabs .nav-link.active { color: #60a5fa; border-bottom-color: #1e293b !important; }
body.dark-mode .ac-nav-tabs .nav-link:hover:not(.active) { background: #1e3a5f; color: #60a5fa; }
body.dark-mode .ac-card               { background: #1e293b; border-color: #334155; }
body.dark-mode .ac-card--cancelado    { border-color: #f87171; box-shadow: 0 0 0 1px rgba(248,113,113,.2); }
body.dark-mode .ac-card--en-transito  { border-color: #60a5fa; box-shadow: 0 0 0 1px rgba(96,165,250,.2); }
body.dark-mode .ac-detail-row .ac-val { color: #e2e8f0; }
body.dark-mode .ac-card-footer        { background: #0f172a; border-color: #334155; }
body.dark-mode .ac-modal-input        { background: #1e293b; border-color: #475569; color: #e2e8f0; }
body.dark-mode .ac-modal-input:focus  { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
body.dark-mode .ac-modal-label        { color: #94a3b8; }
body.dark-mode .ac-resumen-row        { border-color: #334155; }
body.dark-mode .ac-resumen-lbl        { color: #94a3b8; }
body.dark-mode .ac-resumen-val        { color: #e2e8f0; }
body.dark-mode .ac-badge-transito     { background: rgba(30,64,175,.35); color: #93c5fd; }
body.dark-mode .ac-badge-cancelado    { background: rgba(185,28,28,.35); color: #fca5a5; }
</style>

<div class="container-fluid py-4">

    <!-- Encabezado -->
    <div class="ac-header-gradient d-flex align-items-center gap-3">
        <i class="fa-solid fa-headset fa-2x"></i>
        <div>
            <h4>Atención a Clientes</h4>
            <p>Gestión de llamadas y dictámenes para operaciones en retenciones</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="card shadow-sm">
        <div class="card-body pb-0">
            <ul class="nav nav-tabs ac-nav-tabs border-0 mb-0" id="acTabNav" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-entrantes-btn"
                            data-bs-toggle="tab" data-bs-target="#tabEntrantes"
                            type="button" role="tab">
                        <i class="fa-solid fa-inbox me-1"></i>Entrantes
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

                <!-- Fila 1 -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="ac-modal-label">Llamada a</label>
                        <select class="ac-modal-input" id="ac-dict-llamada-a">
                            <option value="">— Seleccione —</option>
                            <option>Cliente (titular)</option>
                            <option>Avalista</option>
                            <option>Referencia 1</option>
                            <option>Referencia 2</option>
                            <option>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="ac-modal-label">Número</label>
                        <input type="text" class="ac-modal-input" id="ac-dict-numero"
                               placeholder="10 dígitos" maxlength="20">
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
                            <option value="">— Seleccione —</option>
                            <option>Contacto efectivo</option>
                            <option>Buzón de voz</option>
                            <option>No contesta</option>
                            <option>Número equivocado</option>
                            <option>Fuera de servicio</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="ac-modal-label">Dictamen</label>
                        <select class="ac-modal-input" id="ac-dict-dictamen">
                            <option value="">— Seleccione dictamen —</option>
                            <option>Autorizado para recolección</option>
                            <option>Cancelado, promesa de pago</option>
                            <option>Pendiente de contacto</option>
                            <option>No localizado</option>
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
    // RENDER CARD — ENTRANTE
    // ──────────────────────────────────────────────────────────────────
    function acRenderCardEntrante(item) {
        return `
        <div class="ac-card">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${acEsc(String(item.id_credito))}
                    <small>${acEsc(item.nombre_cliente)}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">
                    ${acEsc(item.folio)}
                </span>
            </div>
            <div class="ac-card-body">
                <div class="ac-detail-row">
                    <span class="ac-lbl">Adeudo original</span>
                    <span class="ac-val">${acFmt(item.saldo_capital)}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Total final</span>
                    <span class="ac-val fw-bold" style="color:#059669;">${acFmt(item.adeudo_total)}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Gestor a cargo</span>
                    <span class="ac-val">${item.gestor_nombre ? acEsc(item.gestor_nombre) : '<span class="text-muted fst-italic">Sin asignar</span>'}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Asignación realizada</span>
                    <span class="ac-val">${item.fecha_asignacion ? acEsc(item.fecha_asignacion) : '<span class="text-muted fst-italic">—</span>'}</span>
                </div>
            </div>
            <div class="ac-card-footer">
                <button type="button" class="ac-btn-dictaminar"
                        onclick="acAbrirDictaminar(${item.id})">
                    <i class="fa-solid fa-phone-volume me-1"></i>Dictaminar
                </button>
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

        return `
        <div class="ac-card ${extraClass}">
            <div class="ac-card-header">
                <span class="ac-credito-id">
                    <i class="fa-solid fa-hashtag me-1" style="opacity:.7;"></i>
                    Crédito ${acEsc(String(item.id_credito))}
                    <small>${acEsc(item.nombre_cliente)}</small>
                </span>
                <span class="badge" style="background:rgba(255,255,255,.18);color:#fff;font-size:.72rem;">
                    ${acEsc(item.folio)}
                </span>
            </div>
            <div class="ac-card-body">
                <div class="ac-detail-row">
                    <span class="ac-lbl">Estatus dictamen</span>
                    <span class="ac-val">${badgeHtml}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Dictamen</span>
                    <span class="ac-val">${item.dictamen ? acEsc(item.dictamen) : '<span class="text-muted fst-italic">—</span>'}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Adeudo original</span>
                    <span class="ac-val">${acFmt(item.saldo_capital)}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Total final</span>
                    <span class="ac-val fw-bold" style="color:#059669;">${acFmt(item.adeudo_total)}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Gestor a cargo</span>
                    <span class="ac-val">${item.gestor_nombre ? acEsc(item.gestor_nombre) : '<span class="text-muted fst-italic">Sin asignar</span>'}</span>
                </div>
                <div class="ac-detail-row">
                    <span class="ac-lbl">Fecha dictamen</span>
                    <span class="ac-val">${item.fecha_dictamen ? acEsc(item.fecha_dictamen) : '<span class="text-muted fst-italic">—</span>'}</span>
                </div>
                ${item.comentarios ? `
                <div class="ac-detail-row" style="align-items:flex-start;">
                    <span class="ac-lbl">Comentarios</span>
                    <span class="ac-val" style="white-space:pre-line;">${acEsc(item.comentarios)}</span>
                </div>` : ''}
            </div>
        </div>`;
    }

    // ──────────────────────────────────────────────────────────────────
    // ABRIR MODAL DICTAMINAR
    // ──────────────────────────────────────────────────────────────────
    window.acAbrirDictaminar = function (idOperacion) {
        document.getElementById('ac-dict-id-operacion').value = idOperacion;

        // Limpiar campos
        ['ac-dict-llamada-a', 'ac-dict-tipo-contacto', 'ac-dict-resultado',
         'ac-dict-dictamen', 'ac-dict-plataforma'].forEach(id => {
            document.getElementById(id).value = '';
        });
        document.getElementById('ac-dict-numero').value              = '';
        document.getElementById('ac-dict-persona-contactada').value   = '';
        document.getElementById('ac-dict-comentarios').value          = '';

        new bootstrap.Modal(document.getElementById('modalAcDictaminar')).show();
    };

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
        const llamadaA = document.getElementById('ac-dict-llamada-a').value;
        if (!llamadaA) {
            Swal.fire({ icon: 'warning', title: 'Campo requerido', text: 'Seleccione a quién se llamó.', confirmButtonColor: '#2563eb' });
            return;
        }

        const payload = {
            id_operacion:       idOperacion,
            llamada_a:          llamadaA,
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
                    mensajeExtra = 'La operación fue trasladada al pipeline en <strong>Recibido</strong>.';
                } else if (data.estatus_nuevo === 'cancelado') {
                    mensajeExtra = 'La operación quedó marcada como <strong>Cancelado</strong> en Retenciones.';
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Dictamen guardado',
                    html: 'El dictamen fue registrado correctamente.' + (mensajeExtra ? '<br><small class="text-muted">' + mensajeExtra + '</small>' : ''),
                    confirmButtonColor: '#2563eb',
                }).then(() => { acCargarEntrantes(); });
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
