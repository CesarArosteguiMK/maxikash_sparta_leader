<?php
$creditoInicial = isset($_GET['id_credito']) ? (int) $_GET['id_credito'] : 0;
?>

<style>
    .madj-tl-shell {
        color: #1f2937;
    }
    .madj-tl-hero {
        border-radius: .85rem;
        background: linear-gradient(120deg, #24334f 0%, #2f63d9 100%);
        color: #fff;
        box-shadow: 0 .65rem 1.6rem rgba(36, 51, 79, .18);
    }
    .madj-tl-hero small {
        color: rgba(255, 255, 255, .74);
    }
    .madj-tl-panel {
        border: 1px solid var(--bs-border-color);
        border-radius: .85rem;
        background: var(--bs-body-bg);
        box-shadow: 0 .45rem 1.2rem rgba(15, 23, 42, .05);
    }
    .madj-tl-search {
        border: 1px solid rgba(15, 157, 146, .25);
        border-radius: .8rem;
        background: linear-gradient(145deg, rgba(15, 157, 146, .08), rgba(47, 99, 217, .04));
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
    .madj-tl-stage.is-completado::before {
        background: #22c55e;
    }
    .madj-tl-stage.is-en_proceso::before {
        background: #0f9d92;
    }
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
    .madj-tl-badge.ok {
        background: #dcfce7;
        color: #166534;
    }
    .madj-tl-badge.run {
        background: #dbeafe;
        color: #1d4ed8;
    }
    .madj-tl-badge.wait {
        background: #f1f5f9;
        color: #475569;
    }
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
        min-height: 260px;
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
        .madj-tl-stage-grid {
            grid-template-columns: 1fr;
        }
        .madj-tl-detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid py-3 madj-tl-shell">
    <div class="madj-tl-hero p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="mb-1 text-white"><i class="fa-solid fa-timeline me-2"></i>Timeline por credito</h3>
                <small>Consulta asignacion, evidencias, recuperacion, cartera, tracking y recepcion de una moto adjudicada.</small>
            </div>
            <a href="/MotosAdjudicadas/reporteria" class="btn btn-light">
                <i class="fa-solid fa-arrow-left me-1"></i>Reporteria
            </a>
        </div>
    </div>

    <div class="madj-tl-search p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5 col-xl-4">
                <label for="madjTlCredito" class="form-label fw-bold">Credito</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                    <input type="text" id="madjTlCredito" class="form-control" inputmode="numeric" autocomplete="off" placeholder="Ej. 1416538" value="<?= $creditoInicial > 0 ? (int) $creditoInicial : ''; ?>">
                </div>
            </div>
            <div class="col-12 col-md-auto">
                <button type="button" class="btn btn-primary w-100" id="madjTlBtnBuscar">
                    <i class="fa-solid fa-magnifying-glass me-1"></i>Consultar
                </button>
            </div>
            <div class="col-12 col-md-auto">
                <a href="#" class="btn btn-outline-primary w-100 disabled" id="madjTlBtnPdf" aria-disabled="true">
                    <i class="fa-solid fa-file-pdf me-1"></i>Descargar PDF
                </a>
            </div>
            <div class="col-12 col-md">
                <div class="small text-muted">El PDF se genera con el credito consultado y conserva el resumen completo del proceso.</div>
            </div>
        </div>
    </div>

    <div id="madjTlLoader" class="madj-tl-panel p-4 d-none">
        <div class="d-flex align-items-center gap-2 text-muted">
            <span class="spinner-border spinner-border-sm"></span>
            <span>Consultando timeline del credito...</span>
        </div>
    </div>

    <div id="madjTlError" class="alert alert-danger d-none"></div>

    <div id="madjTlEmpty" class="madj-tl-panel madj-tl-empty">
        <i class="fa-solid fa-route"></i>
        <h5 class="fw-bold mb-1">Busca un credito para armar el expediente</h5>
        <p class="mb-0">Aqui veras todo el proceso operativo consolidado, desde la asignacion hasta la recepcion.</p>
    </div>

    <div id="madjTlContent" class="d-none">
        <div class="row g-3 mb-3" id="madjTlResumen"></div>

        <div class="madj-tl-panel p-3 mb-3">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-motorcycle me-1 text-primary"></i>Ficha de motocicleta</h5>
                    <div class="text-muted small">Datos tomados de la operacion adjudicada y validacion de unidad.</div>
                </div>
            </div>
            <div class="madj-tl-detail-grid" id="madjTlFichaMoto"></div>
        </div>

        <div class="madj-tl-panel p-3 mb-3">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-diagram-project me-1 text-primary"></i>Flujo operativo</h5>
                    <div class="text-muted small">Etapas principales del proceso de adjudicacion.</div>
                </div>
                <span class="badge bg-label-primary" id="madjTlEtapasCount">0 etapas</span>
            </div>
            <div class="madj-tl-stage-grid" id="madjTlEtapas"></div>
        </div>

        <div class="madj-tl-panel p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i>Eventos del credito</h5>
                    <div class="text-muted small">Movimientos registrados por fecha.</div>
                </div>
                <span class="badge bg-label-secondary" id="madjTlEventosCount">0 eventos</span>
            </div>
            <div class="madj-tl-event-list" id="madjTlEventos"></div>
        </div>
    </div>
</div>

<script>
(function () {
    const input = document.getElementById('madjTlCredito');
    const btnBuscar = document.getElementById('madjTlBtnBuscar');
    const btnPdf = document.getElementById('madjTlBtnPdf');
    const loader = document.getElementById('madjTlLoader');
    const errorBox = document.getElementById('madjTlError');
    const empty = document.getElementById('madjTlEmpty');
    const content = document.getElementById('madjTlContent');
    const resumen = document.getElementById('madjTlResumen');
    const fichaMoto = document.getElementById('madjTlFichaMoto');
    const etapas = document.getElementById('madjTlEtapas');
    const eventos = document.getElementById('madjTlEventos');
    const etapasCount = document.getElementById('madjTlEtapasCount');
    const eventosCount = document.getElementById('madjTlEventosCount');
    const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' });
    let creditoActual = 0;

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
        });
    }

    function showLoading(isLoading) {
        loader.classList.toggle('d-none', !isLoading);
        btnBuscar.disabled = isLoading;
        if (isLoading) {
            errorBox.classList.add('d-none');
        }
    }

    function setPdfEnabled(idCredito) {
        creditoActual = Number(idCredito || 0);
        if (creditoActual > 0) {
            btnPdf.classList.remove('disabled');
            btnPdf.setAttribute('aria-disabled', 'false');
            btnPdf.href = '/MotosAdjudicadas/timelineCreditoPdf?id_credito=' + encodeURIComponent(creditoActual);
        } else {
            btnPdf.classList.add('disabled');
            btnPdf.setAttribute('aria-disabled', 'true');
            btnPdf.href = '#';
        }
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

    function renderResumen(data) {
        const c = data.credito || {};
        const u = c.unidad || {};
        const ubi = c.ubicacion || {};
        const f = c.finanzas || {};
        const unidad = [u.marca, u.modelo, u.anio].filter(Boolean).join(' ') || 'Sin unidad registrada';
        const ubicacion = [ubi.estado, ubi.municipio].filter(Boolean).join(' / ') || 'Sin ubicacion';
        const saldo = f.saldo_capital !== null && f.saldo_capital !== undefined && f.saldo_capital !== ''
            ? money.format(Number(f.saldo_capital || 0))
            : 'Sin saldo';

        resumen.innerHTML = `
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

    function renderFichaMoto(data) {
        const c = data.credito || {};
        const u = c.unidad || {};
        const ubi = c.ubicacion || {};
        const f = c.finanzas || {};
        const contacto = c.contacto || {};

        fichaMoto.innerHTML = [
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

    function renderEtapas(items) {
        const list = Array.isArray(items) ? items : [];
        etapasCount.textContent = list.length + ' etapas';
        etapas.innerHTML = list.map(function (item) {
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

    function renderEventos(items) {
        const list = Array.isArray(items) ? items : [];
        eventosCount.textContent = list.length + ' eventos';
        if (list.length === 0) {
            eventos.innerHTML = '<div class="text-muted py-3">Sin eventos registrados.</div>';
            return;
        }
        eventos.innerHTML = list.map(function (ev) {
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

    async function buscar() {
        const idCredito = Number(String(input.value || '').replace(/\D+/g, ''));
        input.value = idCredito > 0 ? String(idCredito) : '';
        if (!idCredito) {
            errorBox.textContent = 'Indica un credito valido.';
            errorBox.classList.remove('d-none');
            return;
        }

        showLoading(true);
        setPdfEnabled(0);
        try {
            const resp = await fetch('/MotosAdjudicadas/timelineCreditoDatos?id_credito=' + encodeURIComponent(idCredito), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await resp.json();
            if (!data.success) {
                throw new Error(data.message || 'No se pudo cargar el timeline.');
            }
            empty.classList.add('d-none');
            content.classList.remove('d-none');
            renderResumen(data);
            renderFichaMoto(data);
            renderEtapas(data.etapas || []);
            renderEventos(data.eventos || []);
            setPdfEnabled(idCredito);
        } catch (err) {
            content.classList.add('d-none');
            empty.classList.remove('d-none');
            errorBox.textContent = err.message || 'No se pudo cargar el timeline.';
            errorBox.classList.remove('d-none');
        } finally {
            showLoading(false);
        }
    }

    btnBuscar.addEventListener('click', buscar);
    input.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
            ev.preventDefault();
            buscar();
        }
    });
    input.addEventListener('input', function () {
        input.value = String(input.value || '').replace(/\D+/g, '');
    });
    btnPdf.addEventListener('click', function (ev) {
        if (!creditoActual) {
            ev.preventDefault();
        }
    });

    if (Number(input.value || 0) > 0) {
        buscar();
    }
})();
</script>
