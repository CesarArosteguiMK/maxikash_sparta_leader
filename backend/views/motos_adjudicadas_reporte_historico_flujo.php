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
    .madj-flow-board {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(310px, 1fr);
        gap: .9rem;
        overflow-x: auto;
        padding-bottom: .65rem;
    }
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
</style>

<div class="container-fluid py-3 madj-flow-page" id="madjFlowApp">
    <div class="madj-flow-hero p-4 mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 text-white"><i class="fa-solid fa-diagram-project me-2"></i>Historico por etapas</h3>
                <p class="mb-0" style="color:rgba(255,255,255,.76)">Reporte historico de todos los creditos que han entrado a motos adjudicadas, agrupados por etapa operativa.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/MotosAdjudicadas/reporteria" class="btn btn-light">
                    <i class="fa-solid fa-arrow-left me-1"></i>Reporteria
                </a>
                <button type="button" class="btn btn-outline-light" id="madjFlowXlsx">
                    <i class="fa-solid fa-file-excel me-1"></i>XLSX
                </button>
                <button type="button" class="btn btn-outline-light" id="madjFlowPdf">
                    <i class="fa-solid fa-file-pdf me-1"></i>PDF
                </button>
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
                <label class="form-label fw-semibold" for="madjFlowEtapa">Etapa</label>
                <select class="form-select" id="madjFlowEtapa"><option value="">Todas</option></select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold" for="madjFlowEstado">Estado</label>
                <select class="form-select" id="madjFlowEstado"><option value="">Todos</option></select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" for="madjFlowQ">Buscar</label>
                <input type="search" class="form-control" id="madjFlowQ" placeholder="Credito, cliente, VIN, folio...">
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="button" class="btn btn-primary w-100" id="madjFlowBuscar" title="Buscar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" id="madjFlowLimpiar" title="Limpiar">
                    <i class="fa-solid fa-eraser"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3" id="madjFlowMetrics"></div>

    <div class="alert alert-warning d-none" id="madjFlowTrackingAviso">
        <i class="fa-solid fa-triangle-exclamation me-1"></i>
        Tracking de recoleccion aun no esta disponible en este ambiente; el historico se muestra con las demas etapas.
    </div>

    <div class="madj-flow-card p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-layer-group me-1 text-primary"></i>Pipeline historico</h5>
                <div class="text-muted small">Cada credito aparece en su etapa mas avanzada registrada.</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span>Mostrar</span>
                <select class="form-select form-select-sm" id="madjFlowLimit" style="width: 95px;">
                    <option value="300">300</option>
                    <option value="800" selected>800</option>
                    <option value="1500">1500</option>
                    <option value="3000">3000</option>
                </select>
                <span class="badge bg-label-primary" id="madjFlowTotal">0 creditos</span>
            </div>
        </div>
        <div id="madjFlowLoader" class="text-muted py-4">
            <span class="spinner-border spinner-border-sm me-2"></span>Cargando historico...
        </div>
        <div id="madjFlowError" class="alert alert-danger d-none"></div>
        <div class="madj-flow-board d-none" id="madjFlowBoard"></div>
    </div>
</div>

<script>
(function () {
    const state = { etapas: [], rows: [], catalogsLoaded: false };
    const fmt = new Intl.NumberFormat('es-MX');

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
        const etapas = data.etapas || [];
        const principal = [
            { label: 'Total historico', value: resumen.total || 0, icon: 'fa-motorcycle' },
            { label: 'Etapas operativas', value: etapas.length || 0, icon: 'fa-layer-group' },
            { label: 'Mayor concentracion', value: Math.max.apply(null, etapas.map(e => Number(e.total || 0)).concat([0])), icon: 'fa-chart-simple' },
            { label: 'Limite consultado', value: resumen.limit || 0, icon: 'fa-filter' },
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

    function renderBoard(data) {
        const board = document.getElementById('madjFlowBoard');
        const etapas = data.etapas || [];
        state.etapas = etapas;
        state.rows = [];
        document.getElementById('madjFlowTotal').textContent = `${fmt.format((data.resumen || {}).total || 0)} creditos`;
        document.getElementById('madjFlowTrackingAviso').classList.toggle('d-none', !!((data.resumen || {}).tracking_disponible));

        board.innerHTML = etapas.map(etapa => {
            const creditos = etapa.creditos || [];
            creditos.forEach(row => state.rows.push(row));
            const body = creditos.length ? creditos.map(row => `
                <article class="madj-flow-item">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <span class="madj-flow-folio">${esc(row.folio || ('ADJ-' + row.id_operacion))}</span>
                        <a class="btn btn-sm btn-icon btn-outline-primary" href="/MotosAdjudicadas/timelineCredito?id_credito=${encodeURIComponent(row.id_credito)}" title="Ver timeline">
                            <i class="fa-solid fa-timeline"></i>
                        </a>
                    </div>
                    <div class="madj-flow-title">#${esc(row.id_credito)} - ${esc(row.nombre_cliente)}</div>
                    <div class="madj-flow-muted mt-1"><i class="fa-solid fa-location-dot me-1"></i>${esc(row.estado || 'SIN ESTADO')} / ${esc(row.municipio || 'SIN MUNICIPIO')}</div>
                    <div class="madj-flow-muted"><i class="fa-solid fa-motorcycle me-1"></i>${esc(row.unidad || 'Sin unidad')}</div>
                    <div class="madj-flow-muted"><i class="fa-regular fa-clock me-1"></i>${esc(row.fecha_etapa_fmt || fecha(row.fecha_etapa))}</div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge bg-label-secondary">${esc(row.estatus || 'Sin estatus')}</span>
                        ${Number(row.evidencias_total || 0) ? `<span class="badge bg-label-info">${fmt.format(row.evidencias_total)} evid.</span>` : ''}
                        ${Number(row.tracking_total || 0) ? `<span class="badge bg-label-success">${fmt.format(row.tracking_total)} ruta</span>` : ''}
                    </div>
                </article>
            `).join('') : '<div class="madj-flow-empty">Sin creditos en esta etapa con los filtros actuales.</div>';

            return `
                <section class="madj-flow-stage">
                    <div class="madj-flow-stage-head">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="madj-flow-icon"><i class="fa-solid ${esc(etapa.icon || 'fa-circle-dot')}"></i></span>
                                    <strong>${esc(etapa.titulo || '')}</strong>
                                </div>
                                <div class="madj-flow-muted">${esc(etapa.descripcion || '')}</div>
                            </div>
                            <span class="badge bg-label-primary">${fmt.format(etapa.total || 0)}</span>
                        </div>
                    </div>
                    <div class="madj-flow-stage-list">${body}</div>
                </section>
            `;
        }).join('');
    }

    async function cargar() {
        const qs = new URLSearchParams();
        ['Desde', 'Hasta', 'Etapa', 'Estado', 'Q', 'Limit'].forEach(key => {
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
            llenarSelect('madjFlowEtapa', (data.catalogos || {}).etapas || [], 'titulo');
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
        ['Desde', 'Hasta', 'Etapa', 'Estado', 'Q', 'Limit'].forEach(key => {
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
    document.getElementById('madjFlowXlsx').addEventListener('click', () => descargar('/MotosAdjudicadas/reporteHistoricoFlujoExcel'));
    document.getElementById('madjFlowPdf').addEventListener('click', () => descargar('/MotosAdjudicadas/reporteHistoricoFlujoPdf'));
    document.getElementById('madjFlowLimpiar').addEventListener('click', () => {
        ['Desde', 'Hasta', 'Etapa', 'Estado', 'Q'].forEach(key => {
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
