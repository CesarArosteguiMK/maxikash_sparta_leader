<?php $puedeOverrideKanban = !empty($av_kanban_puede_override); ?>

<style>
    .avk-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .avk-head {
        border: 1px solid #dbe4ef;
        background: #f8fafc;
        border-radius: .5rem;
        padding: 1rem 1.15rem;
    }
    .avk-head-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: .5rem;
        background: #eef2ff;
        color: #3730a3;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
    }
    .avk-toolbar,
    .avk-board-wrap,
    .avk-ficha-block {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
    }
    .avk-toolbar {
        padding: .85rem;
    }
    .avk-summary {
        display: flex;
        gap: .5rem;
        overflow-x: auto;
        padding-bottom: .1rem;
    }
    .avk-summary-chip {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        padding: .55rem .7rem;
        min-width: 9.25rem;
        flex: 0 0 auto;
    }
    .avk-summary-chip strong {
        color: #1e293b;
        display: block;
        font-size: 1.05rem;
        line-height: 1;
    }
    .avk-summary-chip span {
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .avk-board-wrap {
        overflow-x: auto;
        padding: .85rem;
    }
    .avk-board {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: minmax(18rem, 20rem);
        gap: .8rem;
        min-height: 30rem;
    }
    .avk-column {
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        border-radius: .5rem;
        display: flex;
        flex-direction: column;
        min-height: 30rem;
        max-height: 72vh;
    }
    .avk-column-head {
        padding: .75rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
    }
    .avk-column-title {
        color: #1e293b;
        font-size: .88rem;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: .45rem;
    }
    .avk-count {
        border-radius: 999px;
        padding: .18rem .48rem;
        font-size: .72rem;
        font-weight: 800;
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .avk-column-body {
        padding: .7rem;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: .65rem;
    }
    .avk-card {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        padding: .65rem;
        box-shadow: 0 .35rem .8rem rgba(15, 23, 42, .05);
    }
    .avk-card-media {
        width: 100%;
        aspect-ratio: 16 / 9;
        border-radius: .45rem;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        object-fit: cover;
        display: block;
        margin-bottom: .6rem;
    }
    .avk-card-fallback {
        width: 100%;
        aspect-ratio: 16 / 9;
        border-radius: .45rem;
        border: 1px solid #e2e8f0;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: .6rem;
        font-size: 1.5rem;
    }
    .avk-card-title {
        color: #1e293b;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: .25rem;
    }
    .avk-card-sub {
        color: #64748b;
        font-size: .75rem;
        line-height: 1.35;
    }
    .avk-card-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .45rem;
        margin-top: .55rem;
    }
    .avk-card-meta {
        border: 1px solid #e2e8f0;
        border-radius: .4rem;
        padding: .4rem;
        min-width: 0;
    }
    .avk-card-meta span {
        color: #64748b;
        display: block;
        font-size: .65rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .avk-card-meta strong {
        color: #334155;
        display: block;
        font-size: .76rem;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }
    .avk-card-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: .4rem;
        margin-top: .65rem;
    }
    .avk-empty {
        padding: 1.5rem .75rem;
        text-align: center;
        color: #64748b;
        font-size: .82rem;
    }
    .avk-empty i {
        display: block;
        font-size: 1.55rem;
        opacity: .35;
        margin-bottom: .45rem;
    }
    .avk-color-purple { color: #7e22ce; }
    .avk-color-blue { color: #0369a1; }
    .avk-color-green { color: #15803d; }
    .avk-color-red { color: #b91c1c; }
    .avk-color-gray { color: #475569; }
    .avk-color-teal { color: #0f766e; }
    .avk-color-orange { color: #c2410c; }
    .avk-ficha-block {
        padding: .8rem;
        margin-bottom: .8rem;
    }
    .avk-ficha-title {
        color: #1e293b;
        font-size: .88rem;
        font-weight: 800;
        margin-bottom: .45rem;
    }
    .avk-timeline {
        display: flex;
        flex-direction: column;
        gap: .55rem;
    }
    .avk-timeline-item {
        border-left: 3px solid #cbd5e1;
        padding-left: .65rem;
    }
    .avk-timeline-item strong {
        color: #1e293b;
        display: block;
        font-size: .82rem;
    }
    .avk-timeline-item span {
        color: #64748b;
        display: block;
        font-size: .73rem;
    }
    .avk-payload {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: .4rem;
        color: #475569;
        font-size: .72rem;
        margin-top: .35rem;
        max-height: 7rem;
        overflow: auto;
        padding: .45rem;
        white-space: pre-wrap;
    }
    .avk-sla {
        border-radius: 999px;
        display: inline-flex;
        font-size: .68rem;
        font-weight: 800;
        margin-top: .45rem;
        padding: .2rem .5rem;
        text-transform: uppercase;
    }
    .avk-sla-normal { background: #dcfce7; color: #166534; }
    .avk-sla-atencion { background: #fef3c7; color: #92400e; }
    .avk-sla-vencido { background: #fee2e2; color: #991b1b; }
    @media (max-width: 576px) {
        .avk-head {
            padding: .9rem;
        }
        .avk-board {
            grid-auto-columns: minmax(17rem, 18rem);
        }
    }
</style>

<div class="avk-shell">
    <section class="avk-head d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-start gap-3">
            <span class="avk-head-icon"><i class="fa-solid fa-table-columns"></i></span>
            <div>
                <h4 class="mb-1">Kanban Operativo</h4>
                <div class="text-muted small">Bandeja central del almacenista por estatus, agencia, celula y tipo de unidad.</div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/MotosAdjudicadas/almacenVirtual" class="btn btn-label-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Almacen Virtual
            </a>
            <a href="/MotosAdjudicadas/revisionMecanica" class="btn btn-label-danger btn-sm">
                <i class="fa-solid fa-screwdriver-wrench me-1"></i>Revision
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="avk-btn-refresh">
                <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
            </button>
        </div>
    </section>

    <section class="avk-toolbar">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-3">
                <label class="form-label small fw-bold" for="avk-q">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control" id="avk-q" placeholder="Serie, marca, modelo, color">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="avk-celula">Celula</label>
                <select class="form-select form-select-sm" id="avk-celula">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-bold" for="avk-ubicacion">Agencia / ubicacion</label>
                <select class="form-select form-select-sm" id="avk-ubicacion">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="form-label small fw-bold" for="avk-tipo">Tipo unidad</label>
                <select class="form-select form-select-sm" id="avk-tipo">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-1">
                <label class="form-label small fw-bold" for="avk-limit">Limite</label>
                <select class="form-select form-select-sm" id="avk-limit">
                    <option value="20">20</option>
                    <option value="40" selected>40</option>
                    <option value="80">80</option>
                </select>
            </div>
            <div class="col-12 col-lg-1 d-grid">
                <button type="button" class="btn btn-primary btn-sm" id="avk-btn-filtrar">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </div>
        </div>
    </section>

    <section class="avk-summary" id="avk-summary"></section>

    <section class="avk-board-wrap">
        <div class="avk-board" id="avk-board">
            <div class="avk-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando Kanban...</div>
        </div>
    </section>
</div>

<div class="modal fade" id="avk-modal-ficha" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ficha y bitacora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="avk-ficha-body">
                <div class="avk-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando ficha...</div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="avk-modal-venta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="avk-form-venta">
            <div class="modal-header">
                <h5 class="modal-title">Enviar a piso de venta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_unidad" id="avk-venta-id">
                <div class="avk-ficha-block" id="avk-venta-summary"></div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="avk-destino-venta">Destino</label>
                    <select class="form-select" id="avk-destino-venta" name="destino_venta" required>
                        <option value="Pension a Max">Pension a Max</option>
                        <option value="Amigo Efectivo">Amigo Efectivo</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small fw-bold" for="avk-venta-observaciones">Observaciones</label>
                    <textarea class="form-control" id="avk-venta-observaciones" name="observaciones" rows="3" maxlength="1000"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-success" id="avk-btn-venta">
                    <i class="fa-solid fa-store me-1"></i>Enviar
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($puedeOverrideKanban): ?>
<div class="modal fade" id="avk-modal-override" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="avk-form-override">
            <div class="modal-header">
                <h5 class="modal-title">Override de supervisor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_unidad" id="avk-override-id">
                <div class="avk-ficha-block" id="avk-override-summary"></div>
                <div class="mb-3">
                    <label class="form-label small fw-bold" for="avk-override-estatus">Estatus destino</label>
                    <select class="form-select" id="avk-override-estatus" name="estatus_nuevo" required></select>
                </div>
                <div>
                    <label class="form-label small fw-bold" for="avk-override-justificacion">Justificacion</label>
                    <textarea class="form-control" id="avk-override-justificacion" name="justificacion" rows="4" maxlength="2000" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning" id="avk-btn-override">
                    <i class="fa-solid fa-shield-halved me-1"></i>Aplicar override
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
(function () {
    const canOverride = <?= $puedeOverrideKanban ? 'true' : 'false'; ?>;
    const state = {
        timer: null,
        cards: new Map(),
        columns: [],
    };

    const $ = (id) => document.getElementById(id);

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function notify(icon, title, text) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ icon, title, text });
            return;
        }
        window.alert((title ? title + '\n' : '') + (text || ''));
    }

    function params() {
        const p = new URLSearchParams();
        const q = $('avk-q')?.value.trim() || '';
        const celula = $('avk-celula')?.value || '';
        const ubicacion = $('avk-ubicacion')?.value || '';
        const tipo = $('avk-tipo')?.value || '';
        const limit = $('avk-limit')?.value || '40';
        if (q) p.set('q', q);
        if (celula) p.set('id_celula', celula);
        if (ubicacion) p.set('id_ubicacion', ubicacion);
        if (tipo) p.set('tipo_unidad', tipo);
        p.set('limit_por_columna', limit);
        return p;
    }

    function cardImage(row) {
        if (row.foto_url_publica) {
            return `<img class="avk-card-media" src="${esc(row.foto_url_publica)}" alt="Foto unidad" loading="lazy">`;
        }
        return '<div class="avk-card-fallback"><i class="fa-solid fa-motorcycle"></i></div>';
    }

    function renderCard(row) {
        state.cards.set(String(row.id_unidad), row);
        const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
        const serie = row.vin || 'Sin serie';
        const dias = Number(row.dias_almacen || 0);
        const puedeVenta = row.estatus_inventario === 'reparada';
        const sla = row.sla || { nivel: 'normal', label: 'SLA normal' };
        return `
            <article class="avk-card">
                ${cardImage(row)}
                <div class="avk-card-title">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
                <div class="avk-card-sub">${esc(moto || 'Sin datos de moto')}</div>
                <span class="avk-sla avk-sla-${esc(sla.nivel || 'normal')}">${esc(sla.label || 'SLA normal')}</span>
                <div class="avk-card-grid">
                    <div class="avk-card-meta"><span>Serie</span><strong>${esc(serie)}</strong></div>
                    <div class="avk-card-meta"><span>Color</span><strong>${esc(row.color || 'N/D')}</strong></div>
                    <div class="avk-card-meta"><span>Dias almacen</span><strong>${esc(dias)}</strong></div>
                    <div class="avk-card-meta"><span>Agencia</span><strong>${esc(row.nombre_ubicacion || 'Sin ubicacion')}</strong></div>
                </div>
                <div class="avk-card-sub mt-2">${esc(row.tipo_unidad || row.categoria || row.nombre_celula || '')}</div>
                <div class="avk-card-actions">
                    <button type="button" class="btn btn-label-primary btn-sm" data-action="ficha" data-id="${esc(row.id_unidad)}">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i>Bitacora
                    </button>
                    ${puedeVenta ? `
                        <button type="button" class="btn btn-success btn-sm" data-action="venta" data-id="${esc(row.id_unidad)}">
                            <i class="fa-solid fa-store me-1"></i>Enviar a piso
                        </button>
                    ` : ''}
                    ${canOverride ? `
                        <button type="button" class="btn btn-warning btn-sm" data-action="override" data-id="${esc(row.id_unidad)}">
                            <i class="fa-solid fa-shield-halved me-1"></i>Override
                        </button>
                    ` : ''}
                </div>
            </article>
        `;
    }

    function renderSummary(columns, total) {
        const target = $('avk-summary');
        if (!target) return;
        const chips = [
            `<div class="avk-summary-chip"><span>Total tablero</span><strong>${esc(total || 0)}</strong></div>`,
            ...columns.map((col) => `
                <div class="avk-summary-chip">
                    <span>${esc(col.titulo)}</span>
                    <strong>${esc(col.total || 0)}</strong>
                </div>
            `),
        ];
        target.innerHTML = chips.join('');
    }

    function llenarOverrideSelect(columns) {
        if (!canOverride) return;
        const select = $('avk-override-estatus');
        if (!select) return;
        select.innerHTML = '<option value="">Seleccionar</option>';
        (columns || []).forEach((col) => {
            const opt = document.createElement('option');
            opt.value = String(col.estatus || '');
            opt.textContent = String(col.titulo || col.estatus || '');
            select.appendChild(opt);
        });
    }

    function renderBoard(columns, total) {
        const board = $('avk-board');
        if (!board) return;
        state.cards.clear();
        state.columns = columns || [];
        renderSummary(state.columns, total);
        if (!state.columns.length) {
            board.innerHTML = '<div class="avk-empty"><i class="fa-solid fa-triangle-exclamation"></i>Sin columnas para mostrar.</div>';
            return;
        }

        board.innerHTML = state.columns.map((col) => {
            const rows = Array.isArray(col.rows) ? col.rows : [];
            const body = rows.length
                ? rows.map(renderCard).join('')
                : '<div class="avk-empty"><i class="fa-solid fa-inbox"></i>Sin unidades en esta columna.</div>';
            const trunc = col.truncada ? '<div class="avk-card-sub mt-2">Hay mas unidades; usa filtros para acotar.</div>' : '';
            return `
                <section class="avk-column">
                    <div class="avk-column-head">
                        <div class="avk-column-title avk-color-${esc(col.color || 'gray')}">
                            <i class="fa-solid ${esc(col.icono || 'fa-circle')}"></i>
                            ${esc(col.titulo)}
                        </div>
                        <span class="avk-count">${esc(col.total || 0)}</span>
                    </div>
                    <div class="avk-column-body">
                        ${body}
                        ${trunc}
                    </div>
                </section>
            `;
        }).join('');
    }

    async function cargarCatalogos() {
        const [celulasRes, ubicacionesRes, kanbanRes] = await Promise.all([
            fetch('/MotosAdjudicadas/inventarioCelulas', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
            fetch('/MotosAdjudicadas/inventarioUbicaciones', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
            fetch('/MotosAdjudicadas/kanbanOperativoCatalogos', { headers: { Accept: 'application/json' } }).then(r => r.json()).catch(() => null),
        ]);

        const celulaSelect = $('avk-celula');
        if (celulaSelect && celulasRes && celulasRes.success) {
            (celulasRes.datos || []).forEach((row) => {
                const opt = document.createElement('option');
                opt.value = String(row.id_celula);
                opt.textContent = row.nombre;
                celulaSelect.appendChild(opt);
            });
        }

        const ubicacionSelect = $('avk-ubicacion');
        if (ubicacionSelect && ubicacionesRes && ubicacionesRes.success) {
            (ubicacionesRes.datos || []).forEach((row) => {
                const opt = document.createElement('option');
                opt.value = String(row.id_ubicacion);
                opt.textContent = row.nombre_ubicacion;
                ubicacionSelect.appendChild(opt);
            });
        }

        const tipoSelect = $('avk-tipo');
        const tipos = kanbanRes && kanbanRes.success ? (kanbanRes.datos?.tipos_unidad || []) : [];
        const columnas = kanbanRes && kanbanRes.success ? (kanbanRes.datos?.columnas || []) : [];
        llenarOverrideSelect(columnas);
        if (tipoSelect) {
            tipos.forEach((tipo) => {
                const opt = document.createElement('option');
                opt.value = String(tipo);
                opt.textContent = String(tipo);
                tipoSelect.appendChild(opt);
            });
        }
    }

    async function cargarKanban() {
        const board = $('avk-board');
        if (board) {
            board.innerHTML = '<div class="avk-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando Kanban...</div>';
        }
        const res = await fetch('/MotosAdjudicadas/kanbanOperativoTarjetas?' + params().toString(), {
            headers: { Accept: 'application/json' },
        });
        const json = await res.json();
        if (!json.success) {
            if (board) {
                board.innerHTML = '<div class="avk-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(json.message || 'No se pudo cargar el Kanban.') + '</div>';
            }
            return;
        }
        renderBoard(json.columnas || [], json.total || 0);
    }

    function payloadPretty(raw) {
        if (!raw) return '';
        try {
            return JSON.stringify(JSON.parse(raw), null, 2);
        } catch (err) {
            return String(raw);
        }
    }

    function renderFicha(json) {
        if (!json.success) {
            return '<div class="avk-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(json.message || 'No se encontro la ficha.') + '</div>';
        }
        const u = json.unidad || {};
        const moto = [u.marca, u.modelo, u.anio].filter(Boolean).join(' ');
        const bitacora = Array.isArray(json.bitacora) ? json.bitacora : [];
        const movimientos = Array.isArray(json.movimientos) ? json.movimientos : [];
        const transiciones = Array.isArray(json.kanban_transiciones) ? json.kanban_transiciones : [];
        return `
            <div class="avk-ficha-block">
                <div class="avk-ficha-title">${esc(u.folio_unidad || ('Unidad #' + u.id_unidad))}</div>
                <div class="text-muted small">${esc(moto || 'Sin datos de moto')}</div>
                <div class="text-muted small mt-1">${esc([u.vin ? 'Serie ' + u.vin : '', u.no_motor ? 'Motor ' + u.no_motor : '', u.color ? 'Color ' + u.color : ''].filter(Boolean).join(' | '))}</div>
            </div>
            <div class="avk-ficha-block">
                <div class="avk-ficha-title">Transiciones Kanban</div>
                <div class="avk-timeline">
                    ${transiciones.length ? transiciones.map((row) => `
                        <div class="avk-timeline-item">
                            <strong>${esc([row.estatus_anterior, row.estatus_nuevo].filter(Boolean).join(' -> '))}</strong>
                            <span>${esc([row.origen_evento, row.evento_negocio, row.nombre_usuario, row.fecha_hora_fmt].filter(Boolean).join(' | '))}</span>
                            <span>${esc(row.justificacion || '')}</span>
                            ${row.payload_json ? `<pre class="avk-payload">${esc(payloadPretty(row.payload_json))}</pre>` : ''}
                        </div>
                    `).join('') : '<div class="text-muted small">Sin transiciones Kanban registradas.</div>'}
                </div>
            </div>
            <div class="avk-ficha-block">
                <div class="avk-ficha-title">Bitacora</div>
                <div class="avk-timeline">
                    ${bitacora.length ? bitacora.map((row) => `
                        <div class="avk-timeline-item">
                            <strong>${esc(row.accion || 'Movimiento')}</strong>
                            <span>${esc([row.modulo, row.nombre_usuario, row.fecha_alta_fmt].filter(Boolean).join(' | '))}</span>
                            <span>${esc(row.detalle || '')}</span>
                            ${row.payload_json ? `<pre class="avk-payload">${esc(payloadPretty(row.payload_json))}</pre>` : ''}
                        </div>
                    `).join('') : '<div class="text-muted small">Sin bitacora registrada.</div>'}
                </div>
            </div>
            <div class="avk-ficha-block">
                <div class="avk-ficha-title">Movimientos</div>
                <div class="avk-timeline">
                    ${movimientos.length ? movimientos.map((row) => `
                        <div class="avk-timeline-item">
                            <strong>${esc(row.tipo_movimiento || 'Movimiento')}</strong>
                            <span>${esc([row.estatus_anterior, row.estatus_nuevo].filter(Boolean).join(' -> '))}</span>
                            <span>${esc([row.nombre_usuario, row.fecha_movimiento_fmt].filter(Boolean).join(' | '))}</span>
                            <span>${esc(row.comentario || '')}</span>
                        </div>
                    `).join('') : '<div class="text-muted small">Sin movimientos registrados.</div>'}
                </div>
            </div>
        `;
    }

    async function abrirFicha(idUnidad) {
        const body = $('avk-ficha-body');
        if (body) {
            body.innerHTML = '<div class="avk-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando ficha...</div>';
        }
        const modalEl = $('avk-modal-ficha');
        if (window.bootstrap && window.bootstrap.Modal && modalEl) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
        try {
            const res = await fetch('/MotosAdjudicadas/inventarioFichaUnidad?id_unidad=' + encodeURIComponent(idUnidad), {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            if (body) body.innerHTML = renderFicha(json);
        } catch (err) {
            if (body) {
                body.innerHTML = '<div class="avk-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(err.message || 'No se pudo cargar la ficha.') + '</div>';
            }
        }
    }

    function abrirVenta(idUnidad) {
        const row = state.cards.get(String(idUnidad));
        if (!row) return;
        $('avk-form-venta')?.reset();
        $('avk-venta-id').value = row.id_unidad || '';
        const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
        $('avk-venta-summary').innerHTML = `
            <div class="avk-ficha-title">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
            <div class="text-muted small">${esc(moto || 'Sin datos de moto')}</div>
            <div class="text-muted small mt-1">${esc([row.vin ? 'Serie ' + row.vin : '', row.color ? 'Color ' + row.color : '', row.nombre_ubicacion || ''].filter(Boolean).join(' | '))}</div>
        `;
        const modalEl = $('avk-modal-venta');
        if (window.bootstrap && window.bootstrap.Modal && modalEl) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    function abrirOverride(idUnidad) {
        if (!canOverride) return;
        const row = state.cards.get(String(idUnidad));
        if (!row) return;
        $('avk-form-override')?.reset();
        $('avk-override-id').value = row.id_unidad || '';
        const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
        $('avk-override-summary').innerHTML = `
            <div class="avk-ficha-title">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
            <div class="text-muted small">${esc(moto || 'Sin datos de moto')}</div>
            <div class="text-muted small mt-1">${esc(['Actual: ' + (row.estatus_inventario || 'sin estatus'), row.vin ? 'Serie ' + row.vin : '', row.nombre_ubicacion || ''].filter(Boolean).join(' | '))}</div>
        `;
        const select = $('avk-override-estatus');
        if (select) {
            Array.from(select.options).forEach((opt) => {
                opt.disabled = opt.value !== '' && opt.value === row.estatus_inventario;
            });
            select.value = '';
        }
        const modalEl = $('avk-modal-override');
        if (window.bootstrap && window.bootstrap.Modal && modalEl) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    async function enviarPisoVenta(ev) {
        ev.preventDefault();
        const form = $('avk-form-venta');
        const btn = $('avk-btn-venta');
        if (!form || !btn) return;
        btn.disabled = true;
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Enviando';
        try {
            const res = await fetch('/MotosAdjudicadas/kanbanOperativoEnviarPisoVenta', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            const json = await res.json();
            if (!json.success) {
                notify('error', 'No se pudo enviar', json.message || 'La unidad no cambio de estatus.');
                return;
            }
            const modalEl = $('avk-modal-venta');
            if (window.bootstrap && window.bootstrap.Modal && modalEl) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            notify('success', 'Unidad enviada', json.message || 'Unidad lista para venta.');
            cargarKanban();
        } catch (err) {
            notify('error', 'Error inesperado', err.message || 'No se pudo contactar al servidor.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }

    async function aplicarOverride(ev) {
        ev.preventDefault();
        const form = $('avk-form-override');
        const btn = $('avk-btn-override');
        if (!form || !btn) return;
        btn.disabled = true;
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Aplicando';
        try {
            const res = await fetch('/MotosAdjudicadas/kanbanOperativoOverrideSupervisor', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            const json = await res.json();
            if (!json.success) {
                notify('error', 'Override no aplicado', json.message || 'No se pudo cambiar el estatus.');
                return;
            }
            const modalEl = $('avk-modal-override');
            if (window.bootstrap && window.bootstrap.Modal && modalEl) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            notify('success', 'Override aplicado', json.message || 'Estatus actualizado.');
            cargarKanban();
        } catch (err) {
            notify('error', 'Error inesperado', err.message || 'No se pudo contactar al servidor.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }

    function reload() {
        cargarKanban().catch((err) => {
            const board = $('avk-board');
            if (board) {
                board.innerHTML = '<div class="avk-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(err.message || 'Error inesperado.') + '</div>';
            }
        });
    }

    function init() {
        $('avk-btn-refresh')?.addEventListener('click', reload);
        $('avk-btn-filtrar')?.addEventListener('click', reload);
        $('avk-form-venta')?.addEventListener('submit', enviarPisoVenta);
        $('avk-form-override')?.addEventListener('submit', aplicarOverride);
        $('avk-board')?.addEventListener('click', (ev) => {
            const btn = ev.target.closest('[data-action]');
            if (!btn) return;
            if (btn.dataset.action === 'ficha') {
                abrirFicha(btn.dataset.id);
            } else if (btn.dataset.action === 'venta') {
                abrirVenta(btn.dataset.id);
            } else if (btn.dataset.action === 'override') {
                abrirOverride(btn.dataset.id);
            }
        });
        $('avk-q')?.addEventListener('input', () => {
            window.clearTimeout(state.timer);
            state.timer = window.setTimeout(reload, 350);
        });
        ['avk-celula', 'avk-ubicacion', 'avk-tipo', 'avk-limit'].forEach((id) => {
            $(id)?.addEventListener('change', reload);
        });

        cargarCatalogos()
            .catch(() => {})
            .finally(reload);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
