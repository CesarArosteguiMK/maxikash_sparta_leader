<style>
    .madj-flow-page {
        --madj-navy: #24334f;
        --madj-blue: #2f63d9;
        --madj-teal: #0f9d92;
        --madj-border: var(--bs-border-color);
    }
    .madj-flow-hero {
        border-radius: .85rem;
        background: linear-gradient(120deg, #24334f 0%, #2f63d9 100%);
        color: #fff;
        box-shadow: 0 .65rem 1.6rem rgba(36, 51, 79, .18);
    }
    .madj-flow-card {
        border: 1px solid var(--madj-border);
        border-radius: .75rem;
        background: var(--bs-body-bg);
        box-shadow: 0 .45rem 1.1rem rgba(15, 23, 42, .05);
    }
    .madj-flow-metric {
        border: 1px solid var(--madj-border);
        border-radius: .75rem;
        padding: .85rem;
        min-height: 86px;
        background: rgba(248, 250, 252, .72);
    }
    .madj-flow-metric .label {
        color: #64748b;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .madj-flow-metric .value {
        color: #0f172a;
        font-size: 1.35rem;
        font-weight: 900;
        line-height: 1.15;
    }
    .madj-flow-list { display: flex; flex-direction: column; gap: .65rem; }
    .madj-flow-stage {
        min-height: 620px;
        border: 1px solid var(--madj-border);
        border-radius: .8rem;
        background: rgba(248, 250, 252, .58);
        display: flex;
        flex-direction: column;
    }
    .madj-flow-stage-head {
        padding: .9rem .9rem .75rem;
        border-bottom: 1px solid var(--madj-border);
        background: rgba(255, 255, 255, .68);
        border-radius: .8rem .8rem 0 0;
    }
    .madj-flow-icon {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(15, 157, 146, .12);
        color: #0f766e;
    }
    .madj-flow-stage-list {
        padding: .75rem;
        overflow-y: auto;
        max-height: 590px;
    }
    .madj-flow-item {
        border: 1px solid var(--madj-border);
        border-left: 4px solid var(--madj-teal);
        border-radius: .72rem;
        background: var(--bs-body-bg);
        padding: .75rem;
        margin-bottom: .65rem;
        box-shadow: 0 .3rem .8rem rgba(15, 23, 42, .04);
    }
    .madj-flow-list-row {
        display: grid;
        grid-template-columns: minmax(190px, 1.35fr) minmax(160px, 1fr) minmax(150px, .9fr) minmax(135px, .75fr) auto;
        gap: 1rem;
        align-items: center;
    }
    .madj-flow-list-label { color: #64748b; font-size: .64rem; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
    .madj-flow-list-value { color: var(--madj-navy); font-size: .79rem; font-weight: 700; overflow-wrap: anywhere; }
    .madj-flow-folio {
        display: inline-flex;
        background: rgba(245, 158, 11, .12);
        color: #b45309;
        border-radius: 999px;
        padding: .14rem .48rem;
        font-weight: 800;
        font-size: .7rem;
    }
    .madj-flow-title {
        color: var(--madj-navy);
        font-weight: 900;
        font-size: .85rem;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }
    .madj-flow-muted {
        color: #64748b;
        font-size: .74rem;
        line-height: 1.22;
    }
    .madj-flow-empty {
        min-height: 140px;
        border: 1px dashed var(--madj-border);
        border-radius: .75rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1rem;
    }
    html.dark-mode .madj-flow-card,
    body.dark-mode .madj-flow-card,
    html.dark-mode .madj-flow-item,
    body.dark-mode .madj-flow-item {
        background: #111827;
        border-color: #243246;
    }
    html.dark-mode .madj-flow-stage,
    body.dark-mode .madj-flow-stage,
    html.dark-mode .madj-flow-metric,
    body.dark-mode .madj-flow-metric {
        background: rgba(30, 41, 59, .62);
        border-color: #243246;
    }
    html.dark-mode .madj-flow-stage-head,
    body.dark-mode .madj-flow-stage-head {
        background: rgba(15, 23, 42, .68);
        border-color: #243246;
    }
    html.dark-mode .madj-flow-title,
    body.dark-mode .madj-flow-title,
    html.dark-mode .madj-flow-metric .value,
    body.dark-mode .madj-flow-metric .value {
        color: #e5edf7;
    }
    .madj-flow-timeline-btn {
        white-space: nowrap;
    }
    .madj-flow-timeline-modal .modal-content,
    .madj-flow-timeline-modal .modal-body {
        background: #f5f7fb;
    }
    .madj-flow-timeline-modal .modal-header {
        background: #fff;
        border-bottom: 1px solid var(--bs-border-color);
    }
    .madj-tl-shell {
        color: #1f2937;
    }
    .madj-tl-panel {
        border: 1px solid var(--bs-border-color);
        border-radius: .85rem;
        background: var(--bs-body-bg);
        box-shadow: 0 .45rem 1.2rem rgba(15, 23, 42, .05);
    }
    .madj-tl-metric {
        min-height: 92px;
        border: 1px solid var(--bs-border-color);
        border-radius: .7rem;
        background: rgba(248, 250, 252, .72);
        padding: .85rem;
    }
    .madj-tl-metric .label {
        color: #64748b;
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
    }
    .madj-tl-metric .value {
        color: #0f172a;
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .madj-tl-stage-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(150px, 1fr));
        gap: .75rem;
    }
    .madj-tl-stage {
        position: relative;
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        padding: .9rem;
        background: rgba(248, 250, 252, .82);
        min-height: 150px;
    }
    .madj-tl-stage::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        border-radius: .75rem 0 0 .75rem;
        background: #cbd5e1;
    }
    .madj-tl-stage.is-completado::before { background: #22c55e; }
    .madj-tl-stage.is-en_proceso::before { background: #0f9d92; }
    .madj-tl-stage-icon {
        width: 34px;
        height: 34px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #e2e8f0;
        color: #475569;
        margin-bottom: .55rem;
    }
    .madj-tl-stage.is-completado .madj-tl-stage-icon {
        background: #dcfce7;
        color: #166534;
    }
    .madj-tl-stage.is-en_proceso .madj-tl-stage-icon {
        background: #ccfbf1;
        color: #0f766e;
    }
    .madj-tl-stage h6 {
        color: #0f172a;
        font-weight: 800;
        margin-bottom: .35rem;
    }
    .madj-tl-stage p {
        color: #64748b;
        font-size: .75rem;
        margin-bottom: .6rem;
    }
    .madj-tl-badge {
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .68rem;
        font-weight: 800;
        padding: .25rem .5rem;
    }
    .madj-tl-badge.ok { background: #dcfce7; color: #166534; }
    .madj-tl-badge.run { background: #dbeafe; color: #1d4ed8; }
    .madj-tl-badge.wait { background: #f1f5f9; color: #475569; }
    .madj-tl-event-list {
        position: relative;
        padding-left: 1.2rem;
    }
    .madj-tl-event-list::before {
        content: "";
        position: absolute;
        left: .42rem;
        top: .2rem;
        bottom: .2rem;
        width: 2px;
        background: #dbe3ef;
    }
    .madj-tl-event {
        position: relative;
        border: 1px solid var(--bs-border-color);
        border-radius: .75rem;
        padding: .8rem .9rem;
        margin-bottom: .7rem;
        background: var(--bs-body-bg);
    }
    .madj-tl-event::before {
        content: "";
        position: absolute;
        left: -1.07rem;
        top: 1rem;
        width: .65rem;
        height: .65rem;
        border-radius: 999px;
        background: #0f9d92;
        box-shadow: 0 0 0 4px rgba(15, 157, 146, .12);
    }
    .madj-tl-event h6 {
        font-size: .95rem;
        font-weight: 800;
        margin: 0;
        color: #0f172a;
    }
    .madj-tl-event .desc {
        color: #475569;
        margin-top: .25rem;
    }
    .madj-tl-link {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        margin-top: .45rem;
        border-radius: 999px;
        padding: .28rem .55rem;
        background: rgba(15, 157, 146, .1);
        color: #0f766e;
        font-size: .72rem;
        font-weight: 800;
        text-decoration: none;
    }
    .madj-tl-link:hover {
        color: #0f766e;
        background: rgba(15, 157, 146, .18);
    }
    .madj-tl-source {
        color: #64748b;
        background: #f1f5f9;
        border-radius: 999px;
        font-size: .68rem;
        font-weight: 800;
        padding: .22rem .5rem;
    }
    .madj-tl-detail-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(160px, 1fr));
        gap: .7rem;
    }
    .madj-tl-detail-item {
        border: 1px solid var(--bs-border-color);
        border-radius: .65rem;
        padding: .75rem;
        background: rgba(248, 250, 252, .62);
        min-height: 72px;
    }
    .madj-tl-detail-item .label {
        color: #64748b;
        font-size: .66rem;
        font-weight: 800;
        letter-spacing: .02em;
        text-transform: uppercase;
        margin-bottom: .2rem;
    }
    .madj-tl-detail-item .value {
        color: #0f172a;
        font-size: .86rem;
        font-weight: 800;
        overflow-wrap: anywhere;
    }
    .madj-tl-empty {
        min-height: 220px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        text-align: center;
    }
    .madj-tl-empty i {
        font-size: 2.5rem;
        color: #cbd5e1;
        margin-bottom: .8rem;
    }
    .dark-style .madj-tl-metric,
    .dark-style .madj-tl-stage {
        background: rgba(30, 41, 59, .62);
    }
    .dark-style .madj-tl-metric .value,
    .dark-style .madj-tl-stage h6,
    .dark-style .madj-tl-event h6 {
        color: #e5e7eb;
    }
    .dark-style .madj-tl-event {
        background: rgba(15, 23, 42, .72);
    }
    .dark-style .madj-tl-detail-item {
        background: rgba(30, 41, 59, .62);
    }
    .dark-style .madj-tl-detail-item .value {
        color: #e5e7eb;
    }
    .dark-style .madj-tl-source {
        background: rgba(148, 163, 184, .16);
        color: #cbd5e1;
    }
    @media (max-width: 1400px) {
        .madj-tl-stage-grid {
            grid-template-columns: repeat(3, minmax(150px, 1fr));
        }
    }
    @media (max-width: 768px) {
        .madj-tl-stage-grid,
        .madj-tl-detail-grid {
            grid-template-columns: 1fr;
        }
        .madj-flow-list-row { grid-template-columns: 1fr; gap: .45rem; }
    }
</style>

<div class="container-fluid py-3 madj-flow-page" id="madjFlowApp">
    <div class="madj-flow-hero p-4 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 text-white"><i class="fa-solid fa-clock-rotate-left me-2"></i>Historico adjudicadas</h3>
                <p class="mb-0" style="color:rgba(255,255,255,.76)">Tickets que ya concluyeron su flujo: recepción confirmada o cierre/cancelación registrada.</p>
            </div>
        </div>
    </div>

    <div class="madj-flow-card p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label fw-semibold" for="madjFlowDesde">Desde</label>
                <input type="date" class="form-control" id="madjFlowDesde">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" for="madjFlowHasta">Hasta</label>
                <input type="date" class="form-control" id="madjFlowHasta">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" for="madjFlowEstado">Estado de resguardo</label>
                <select class="form-select" id="madjFlowEstado"><option value="">Todos</option></select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="madjFlowQ">Buscar</label>
                <input type="search" class="form-control" id="madjFlowQ" placeholder="Credito, cliente, VIN, folio...">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="button" class="btn btn-primary flex-fill" id="madjFlowBuscar">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Buscar
                </button>
                <button type="button" class="btn btn-outline-secondary flex-fill" id="madjFlowLimpiar">
                    <i class="fa-solid fa-eraser me-1"></i>Limpiar
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3" id="madjFlowMetrics"></div>

    <div class="madj-flow-card p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-list me-1 text-primary"></i>Tickets concluidos</h5>
                <div class="text-muted small">Listado de operaciones finalizadas; no incluye tickets que siguen dentro del flujo operativo.</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span>Mostrar</span>
                <select class="form-select form-select-sm" id="madjFlowLimit" style="width: 95px;">
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                </select>
                <span class="badge bg-label-primary" id="madjFlowTotal">0 tickets</span>
            </div>
        </div>
        <div id="madjFlowLoader" class="text-muted py-4">
            <span class="spinner-border spinner-border-sm me-2"></span>Cargando historico...
        </div>
        <div id="madjFlowError" class="alert alert-danger d-none"></div>
        <div class="madj-flow-list d-none" id="madjFlowBoard"></div>
    </div>
</div>

<div class="modal fade madj-flow-timeline-modal" id="madjFlowTimelineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0 fw-bold" style="color:#24334f;">
                        <i class="fa-solid fa-timeline me-2"></i>Timeline por credito
                    </h5>
                    <div class="text-muted small">Asignacion, evidencias, recuperacion, cartera, tracking y recepcion.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid py-3 madj-tl-shell">
                    <div id="madjFlowTlLoader" class="madj-tl-panel p-4 d-none">
                        <div class="d-flex align-items-center gap-2 text-muted">
                            <span class="spinner-border spinner-border-sm"></span>
                            <span>Consultando timeline del credito...</span>
                        </div>
                    </div>

                    <div id="madjFlowTlError" class="alert alert-danger d-none"></div>

                    <div id="madjFlowTlContent" class="d-none">
                        <div class="row g-3 mb-3" id="madjFlowTlResumen"></div>

                        <div class="madj-tl-panel p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-motorcycle me-1 text-primary"></i>Ficha de motocicleta</h5>
                                    <div class="text-muted small">Datos tomados de la operacion adjudicada y validacion de unidad.</div>
                                </div>
                            </div>
                            <div class="madj-tl-detail-grid" id="madjFlowTlFichaMoto"></div>
                        </div>

                        <div class="madj-tl-panel p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-diagram-project me-1 text-primary"></i>Flujo operativo</h5>
                                    <div class="text-muted small">Etapas principales del proceso de adjudicacion.</div>
                                </div>
                                <span class="badge bg-label-primary" id="madjFlowTlEtapasCount">0 etapas</span>
                            </div>
                            <div class="madj-tl-stage-grid" id="madjFlowTlEtapas"></div>
                        </div>

                        <div class="madj-tl-panel p-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                                <div>
                                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i>Eventos del credito</h5>
                                    <div class="text-muted small">Movimientos registrados por fecha.</div>
                                </div>
                                <span class="badge bg-label-secondary" id="madjFlowTlEventosCount">0 eventos</span>
                            </div>
                            <div class="madj-tl-event-list" id="madjFlowTlEventos"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const state = { etapas: [], rows: [], catalogsLoaded: false };
    const fmt = new Intl.NumberFormat('es-MX');
    const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    const timelineModalEl = document.getElementById('madjFlowTimelineModal');
    const timelineModal = window.bootstrap?.Modal && timelineModalEl
        ? (window.bootstrap.Modal.getOrCreateInstance
            ? window.bootstrap.Modal.getOrCreateInstance(timelineModalEl)
            : new window.bootstrap.Modal(timelineModalEl))
        : null;
    const tlPdf = document.getElementById('madjFlowTlPdf');
    const tlLoader = document.getElementById('madjFlowTlLoader');
    const tlError = document.getElementById('madjFlowTlError');
    const tlContent = document.getElementById('madjFlowTlContent');
    const tlResumen = document.getElementById('madjFlowTlResumen');
    const tlFichaMoto = document.getElementById('madjFlowTlFichaMoto');
    const tlEtapas = document.getElementById('madjFlowTlEtapas');
    const tlEventos = document.getElementById('madjFlowTlEventos');
    const tlEtapasCount = document.getElementById('madjFlowTlEtapasCount');
    const tlEventosCount = document.getElementById('madjFlowTlEventosCount');
    let timelineRequestSeq = 0;

    function esc(value) {
        return cleanText(value).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    function cleanText(value) {
        return String(value ?? '')
            .replaceAll('Ã¡', 'a').replaceAll('Ã©', 'e').replaceAll('Ã­', 'i').replaceAll('Ã³', 'o').replaceAll('Ãº', 'u')
            .replaceAll('Ã', 'A').replaceAll('Ã‰', 'E').replaceAll('Ã', 'I').replaceAll('Ã“', 'O').replaceAll('Ãš', 'U')
            .replaceAll('Ã±', 'n').replaceAll('Ã‘', 'N').replaceAll('Â', '').replaceAll('â€”', '-').replaceAll('â€“', '-')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function fecha(value) {
        if (!value) return 'Sin fecha';
        const d = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(d.getTime())) return value;
        return d.toLocaleDateString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + d.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });
    }

    function llenarSelect(id, items, labelKey) {
        const el = document.getElementById(id);
        const actual = el.value;
        el.innerHTML = '<option value="">Todos</option>' + (items || []).map(item => {
            const label = item[labelKey || 'label'] || item.titulo || '';
            const value = item.key || label;
            const total = Number(item.total || 0);
            return `<option value="${esc(value)}">${esc(label)}${total ? ' (' + fmt.format(total) + ')' : ''}</option>`;
        }).join('');
        el.value = actual;
    }

    function renderMetrics(data) {
        const resumen = data.resumen || {};
        const principal = [
            { label: 'Tickets concluidos', value: resumen.total || 0, icon: 'fa-circle-check' },
            { label: 'Recepción confirmada', value: resumen.recepcion_confirmada || 0, icon: 'fa-warehouse' },
            { label: 'Cancelados / cerrados', value: resumen.cancelados_o_cerrados || 0, icon: 'fa-ban' },
            { label: 'Límite consultado', value: resumen.limit || 0, icon: 'fa-filter' },
        ];
        document.getElementById('madjFlowMetrics').innerHTML = principal.map(item => `
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="madj-flow-metric">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="label">${esc(item.label)}</div>
                            <div class="value">${fmt.format(item.value)}</div>
                        </div>
                        <span class="madj-flow-icon"><i class="fa-solid ${esc(item.icon)}"></i></span>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function mostrarTimelineModal() {
        if (!timelineModalEl) return;
        if (timelineModal) {
            timelineModal.show();
            return;
        }
        if (window.jQuery && typeof window.jQuery(timelineModalEl).modal === 'function') {
            window.jQuery(timelineModalEl).modal('show');
            return;
        }
        timelineModalEl.classList.add('show');
        timelineModalEl.style.display = 'block';
        timelineModalEl.removeAttribute('aria-hidden');
        timelineModalEl.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
    }

    function cerrarTimelineModalManual() {
        if (!timelineModalEl || timelineModal) return;
        if (window.jQuery && typeof window.jQuery(timelineModalEl).modal === 'function') return;
        timelineModalEl.classList.remove('show');
        timelineModalEl.style.display = 'none';
        timelineModalEl.setAttribute('aria-hidden', 'true');
        timelineModalEl.removeAttribute('aria-modal');
        document.body.classList.remove('modal-open');
    }

    function setTimelinePdf(idCredito) {
        const id = Number(idCredito || 0);
        if (!tlPdf) return;
        if (id > 0) {
            tlPdf.classList.remove('disabled');
            tlPdf.setAttribute('aria-disabled', 'false');
            tlPdf.href = '/MotosAdjudicadas/timelineCreditoPdf?id_credito=' + encodeURIComponent(id);
        } else {
            tlPdf.classList.add('disabled');
            tlPdf.setAttribute('aria-disabled', 'true');
            tlPdf.href = '#';
        }
    }

    function showTimelineLoading(isLoading) {
        tlLoader.classList.toggle('d-none', !isLoading);
        if (isLoading) {
            tlError.classList.add('d-none');
            tlContent.classList.add('d-none');
            setTimelinePdf(0);
        }
    }

    function showTimelineError(message) {
        tlContent.classList.add('d-none');
        tlError.textContent = message || 'No se pudo cargar el timeline.';
        tlError.classList.remove('d-none');
    }

    function stageIcon(key) {
        const icons = {
            asignacion_gestor: 'fa-user-check',
            evidencias: 'fa-camera',
            recuperacion: 'fa-motorcycle',
            envio_cartera: 'fa-folder-open',
            tracking_recoleccion: 'fa-route',
            recepcion: 'fa-warehouse',
        };
        return icons[key] || 'fa-circle-dot';
    }

    function badgeClass(estado) {
        if (estado === 'completado') return 'ok';
        if (estado === 'en_proceso') return 'run';
        return 'wait';
    }

    function badgeText(estado) {
        if (estado === 'completado') return 'Completado';
        if (estado === 'en_proceso') return 'En proceso';
        return 'Pendiente';
    }

    function hrefEvidencia(url) {
        const raw = String(url || '').trim();
        if (!raw) return '';
        if (/^https?:\/\//i.test(raw) || raw.startsWith('/')) return raw;
        return '/' + raw.replace(/^\/+/, '');
    }

    function detailItem(label, value) {
        const clean = value !== null && value !== undefined && String(value).trim() !== '' ? String(value) : 'No disponible';
        return `
            <div class="madj-tl-detail-item">
                <div class="label">${esc(label)}</div>
                <div class="value">${esc(clean)}</div>
            </div>
        `;
    }

    function renderTimelineResumen(data) {
        const c = data.credito || {};
        const u = c.unidad || {};
        const ubi = c.ubicacion || {};
        const f = c.finanzas || {};
        const unidad = [u.marca, u.modelo, u.anio].filter(Boolean).join(' ') || 'Sin unidad registrada';
        const ubicacion = [ubi.estado, ubi.municipio].filter(Boolean).join(' / ') || 'Sin ubicacion';
        const saldo = f.saldo_capital !== null && f.saldo_capital !== undefined && f.saldo_capital !== ''
            ? money.format(Number(f.saldo_capital || 0))
            : 'Sin saldo';

        tlResumen.innerHTML = `
            <div class="col-12 col-md-6 col-xl-3">
                <div class="madj-tl-metric">
                    <div class="label">Credito / Operacion</div>
                    <div class="value">#${esc(c.id_credito || '')}</div>
                    <div class="text-muted small">${esc(c.folio || 'Sin folio')}</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="madj-tl-metric">
                    <div class="label">Cliente</div>
                    <div class="value">${esc(c.nombre_cliente || 'Sin cliente')}</div>
                    <div class="text-muted small">${esc(c.estatus || 'Sin estatus')}</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="madj-tl-metric">
                    <div class="label">Ubicacion</div>
                    <div class="value">${esc(ubicacion)}</div>
                    <div class="text-muted small text-truncate">${esc(ubi.direccion || 'Sin direccion')}</div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="madj-tl-metric">
                    <div class="label">Unidad / Saldo</div>
                    <div class="value">${esc(unidad)}</div>
                    <div class="text-muted small">${esc(saldo)}</div>
                </div>
            </div>
        `;
    }

    function renderTimelineFichaMoto(data) {
        const c = data.credito || {};
        const u = c.unidad || {};
        const ubi = c.ubicacion || {};
        const f = c.finanzas || {};
        const contacto = c.contacto || {};

        tlFichaMoto.innerHTML = [
            detailItem('Marca', u.marca),
            detailItem('Modelo', u.modelo),
            detailItem('Anio', u.anio),
            detailItem('Color', u.color),
            detailItem('VIN / Serie', u.vin),
            detailItem('Motor', u.motor),
            detailItem('Placas', u.placas),
            detailItem('Factura marca', u.factura_marca),
            detailItem('Factura modelo', u.factura_modelo),
            detailItem('Factura serie', u.factura_serie),
            detailItem('Factura motor', u.factura_motor),
            detailItem('Responsable entrega', contacto.responsable_entrega),
            detailItem('Telefono contacto', contacto.telefono_contacto),
            detailItem('Direccion recoleccion', ubi.direccion_recoleccion || ubi.direccion),
            detailItem('Lugar resguardo', [ubi.lugar_resguardo, ubi.lugar_otro].filter(Boolean).join(' / ')),
            detailItem('Responsable resguardo', ubi.responsable_resguardo),
            detailItem('Telefono resguardo', ubi.telefono_resguardo),
            detailItem('Dias mora', f.dias_mora),
            detailItem('Saldo capital', f.saldo_capital !== null && f.saldo_capital !== undefined && f.saldo_capital !== '' ? money.format(Number(f.saldo_capital || 0)) : ''),
            detailItem('Adeudo total', f.adeudo_total !== null && f.adeudo_total !== undefined && f.adeudo_total !== '' ? money.format(Number(f.adeudo_total || 0)) : ''),
        ].join('');
    }

    function renderTimelineEtapas(items) {
        const list = Array.isArray(items) ? items : [];
        tlEtapasCount.textContent = list.length + ' etapas';
        tlEtapas.innerHTML = list.map(function (item) {
            const estado = item.estado || 'pendiente';
            const cls = badgeClass(estado);
            return `
                <article class="madj-tl-stage is-${esc(estado)}">
                    <span class="madj-tl-stage-icon"><i class="fa-solid ${stageIcon(item.key)}"></i></span>
                    <h6>${esc(item.titulo || '')}</h6>
                    <p>${esc(item.descripcion || '')}</p>
                    <div class="d-flex flex-column gap-1">
                        <span class="madj-tl-badge ${cls}"><i class="fa-solid fa-circle-check"></i>${badgeText(estado)}</span>
                        <span class="text-muted small">${esc(item.fecha_fmt || '')}</span>
                    </div>
                </article>
            `;
        }).join('');
    }

    function renderTimelineEventos(items) {
        const list = Array.isArray(items) ? items : [];
        tlEventosCount.textContent = list.length + ' eventos';
        if (list.length === 0) {
            tlEventos.innerHTML = '<div class="text-muted py-3">Sin eventos registrados.</div>';
            return;
        }
        tlEventos.innerHTML = list.map(function (ev) {
            const evidenciaHref = hrefEvidencia(ev.evidencia_url);
            const evidenciaLink = evidenciaHref
                ? `<div><a class="madj-tl-link" href="${esc(evidenciaHref)}" target="_blank" rel="noopener"><i class="fa-solid fa-link"></i>${esc(ev.evidencia_titulo || 'Abrir evidencia')}</a></div>`
                : '';
            return `
                <article class="madj-tl-event">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2">
                        <div>
                            <h6>${esc(ev.titulo || '')}</h6>
                            <div class="desc">${esc(ev.descripcion || '')}</div>
                            ${evidenciaLink}
                        </div>
                        <span class="madj-tl-source">${esc(ev.origen_label || ev.origen || '')}</span>
                    </div>
                    <div class="text-muted small mt-2"><i class="fa-regular fa-clock me-1"></i>${esc(ev.fecha_fmt || 'Sin fecha')}</div>
                </article>
            `;
        }).join('');
    }

    function renderTimeline(data) {
        renderTimelineResumen(data);
        renderTimelineFichaMoto(data);
        renderTimelineEtapas(data.etapas || []);
        renderTimelineEventos(data.eventos || []);
        tlError.classList.add('d-none');
        tlContent.classList.remove('d-none');
    }

    async function abrirTimelineCredito(idCredito) {
        const id = Number(String(idCredito || '').replace(/\D+/g, ''));
        if (!id) return;
        mostrarTimelineModal();
        const seq = ++timelineRequestSeq;
        showTimelineLoading(true);
        try {
            const resp = await fetch('/MotosAdjudicadas/timelineCreditoDatos?id_credito=' + encodeURIComponent(id), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await resp.json();
            if (!data.success) {
                throw new Error(data.message || 'No se pudo cargar el timeline.');
            }
            if (seq !== timelineRequestSeq) return;
            renderTimeline(data);
            setTimelinePdf(id);
        } catch (err) {
            if (seq !== timelineRequestSeq) return;
            showTimelineError(err.message || 'No se pudo cargar el timeline.');
        } finally {
            if (seq === timelineRequestSeq) showTimelineLoading(false);
        }
    }

    function renderBoard(data) {
        const board = document.getElementById('madjFlowBoard');
        const rows = Array.isArray(data.rows) ? data.rows : [];
        state.rows = rows;
        document.getElementById('madjFlowTotal').textContent = `${fmt.format((data.resumen || {}).total || 0)} tickets`;
        if (rows.length === 0) {
            board.innerHTML = '<div class="madj-flow-empty">No hay tickets con flujo concluido para los filtros actuales.</div>';
            return;
        }
        board.innerHTML = rows.map(row => `
            <article class="madj-flow-item">
                <div class="madj-flow-list-row">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="madj-flow-folio">${esc(row.folio || ('ADJ-' + row.id_operacion))}</span>
                            <span class="badge ${row.tipo_cierre === 'Recepción confirmada' ? 'bg-label-success' : 'bg-label-secondary'}">${esc(row.tipo_cierre || 'Cerrado')}</span>
                        </div>
                        <div class="madj-flow-title">#${esc(row.id_credito)} - ${esc(row.nombre_cliente)}</div>
                        <div class="madj-flow-muted mt-1"><i class="fa-solid fa-motorcycle me-1"></i>${esc(row.unidad || 'Sin unidad')}</div>
                    </div>
                    <div>
                        <div class="madj-flow-list-label">NIV / VIN</div>
                        <div class="madj-flow-list-value">${esc(row.vin || 'Sin capturar')}</div>
                    </div>
                    <div>
                        <div class="madj-flow-list-label">Gestor</div>
                        <div class="madj-flow-list-value">${esc(row.gestor_nombre || 'Sin asignar')}</div>
                    </div>
                    <div>
                        <div class="madj-flow-list-label">Fecha de cierre</div>
                        <div class="madj-flow-list-value">${esc(row.fecha_cierre_fmt || 'Sin fecha')}</div>
                        <div class="madj-flow-muted mt-1"><i class="fa-solid fa-location-dot me-1"></i>${esc(row.estado || 'SIN ESTADO')}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary madj-flow-timeline-btn" data-id-credito="${esc(row.id_credito)}" title="Ver detalle del ticket">
                        <i class="fa-solid fa-timeline me-1"></i>Ver detalle
                    </button>
                </div>
            </article>
        `).join('');
    }

    async function cargar() {
        const qs = new URLSearchParams();
        ['Desde', 'Hasta', 'Estado', 'Q', 'Limit'].forEach(key => {
            const el = document.getElementById('madjFlow' + key);
            if (el && el.value) qs.set(key.toLowerCase(), el.value);
        });
        const loader = document.getElementById('madjFlowLoader');
        const error = document.getElementById('madjFlowError');
        const board = document.getElementById('madjFlowBoard');
        loader.classList.remove('d-none');
        error.classList.add('d-none');
        board.classList.add('d-none');
        const resp = await fetch('/MotosAdjudicadas/reporteHistoricoFlujoDatos?' + qs.toString(), { headers: { 'Accept': 'application/json' } });
        const json = await resp.json();
        if (!json.success) throw new Error(json.message || 'No se pudo cargar el historico.');
        const data = json.datos || {};
        if (!state.catalogsLoaded) {
            llenarSelect('madjFlowEstado', (data.catalogos || {}).estados || [], 'label');
            state.catalogsLoaded = true;
        }
        renderMetrics(data);
        renderBoard(data);
        loader.classList.add('d-none');
        board.classList.remove('d-none');
    }

    function queryActual() {
        const qs = new URLSearchParams();
        ['Desde', 'Hasta', 'Estado', 'Q', 'Limit'].forEach(key => {
            const el = document.getElementById('madjFlow' + key);
            if (el && el.value) qs.set(key.toLowerCase(), el.value);
        });
        return qs.toString();
    }

    function descargar(endpoint) {
        const qs = queryActual();
        window.location.href = endpoint + (qs ? '?' + qs : '');
    }

    document.getElementById('madjFlowBuscar').addEventListener('click', () => cargar().catch(mostrarError));
    document.getElementById('madjFlowLimit').addEventListener('change', () => cargar().catch(mostrarError));
    document.getElementById('madjFlowBoard').addEventListener('click', ev => {
        const btn = ev.target.closest('.madj-flow-timeline-btn');
        if (!btn) return;
        abrirTimelineCredito(btn.dataset.idCredito || '');
    });
    timelineModalEl?.querySelectorAll('[data-bs-dismiss="modal"], [data-dismiss="modal"]').forEach(btn => {
        btn.addEventListener('click', cerrarTimelineModalManual);
    });
    tlPdf?.addEventListener('click', ev => {
        if (tlPdf.classList.contains('disabled')) {
            ev.preventDefault();
        }
    });
    document.getElementById('madjFlowLimpiar').addEventListener('click', () => {
        ['Desde', 'Hasta', 'Estado', 'Q'].forEach(key => {
            const el = document.getElementById('madjFlow' + key);
            if (el) el.value = '';
        });
        cargar().catch(mostrarError);
    });
    document.getElementById('madjFlowQ').addEventListener('keydown', ev => {
        if (ev.key === 'Enter') cargar().catch(mostrarError);
    });

    function mostrarError(err) {
        document.getElementById('madjFlowLoader').classList.add('d-none');
        const error = document.getElementById('madjFlowError');
        error.textContent = err.message || 'No se pudo cargar el historico.';
        error.classList.remove('d-none');
    }

    cargar().catch(mostrarError);
})();
</script>
