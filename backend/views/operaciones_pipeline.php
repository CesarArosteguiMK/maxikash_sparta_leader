<!-- Título -->
<h4 class="mb-2">
    <i class="fa-solid fa-diagram-project me-2 text-primary"></i>
    Flujo de Operaciones
</h4>

<!-- Banner contextual -->
<div class="alert alert-primary d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="fa-solid fa-circle-info flex-shrink-0"></i>
    <span class="small">
        <strong>Módulo Operaciones</strong> — Seguimiento del proceso de recuperación de motos adjudicadas en cada etapa del flujo.
    </span>
</div>

<!-- Toolbar -->
<div class="d-flex align-items-center justify-content-between gap-2 mb-3">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="ops-btn-refresh" onclick="opsCargarPipeline()">
        <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
    </button>
    <div id="ops-pipeline-stats" class="d-flex gap-2 flex-wrap align-items-center"></div>
</div>

<!-- Kanban Board -->
<div id="ops-pipeline-board" style="display:none; grid-template-columns:repeat(5,minmax(0,1fr)); gap:.75rem; min-height:calc(100vh - 260px);"></div>

<!-- Spinner de carga inicial -->
<div id="ops-loading" class="text-center py-5">
    <div class="spinner-border text-primary"></div>
    <p class="mt-2 text-muted small">Cargando flujo…</p>
</div>


<!-- ═══════════════════════════════════════════════════════════════════
     MODAL: DETALLE
     ═══════════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetalleOperacion" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-diagram-project me-2 text-primary"></i>
                    Detalle de Operación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="det-body">
                <div class="text-center py-4">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- ═══════════════════════════════════════════════════════════════════
     JAVASCRIPT
     ═══════════════════════════════════════════════════════════════════ -->
<script>
    (function () {

        // ──────────────────────────────────────────────────────────────────
        // CONSTANTES
        // ──────────────────────────────────────────────────────────────────
        const STAGES = [
            'Retenciones',
            'Recibido',
            'Revisión Recuperaciones',
            'Cierre Documentado',
            'Recepción',
        ];

        const STAGE_ICONS = {
            'Recibido':                  'fa-inbox',
            'Revisión Recuperaciones':   'fa-magnifying-glass',
            'Retenciones':               'fa-hand',
            'Cierre Documentado':        'fa-file-circle-check',
            'Recepción':                 'fa-flag-checkered',
        };

        const EV_TOTAL_SLOTS = 11;

        let _operaciones   = [];
        let _detalleActual = null;
        let _evState       = {};
        let _activeOpId    = null;

        // ──────────────────────────────────────────────────────────────────
        // EVIDENCE DEFINITIONS
        // ──────────────────────────────────────────────────────────────────
        const EV_SECTIONS = [
            {
                key: 'recoleccion',
                label: 'Evidencia de Recolección (Final)',
                badgeClass: 'bg-label-warning',
                icon: 'fa-camera-retro',
                slots: [
                    { key: 'rec_tacometro', label: 'Tacómetro Rec.',  icon: 'fa-gauge-high',   accept: 'image/jpeg,image/png' },
                    { key: 'rec_serie',     label: 'No. Serie Rec.',  icon: 'fa-hashtag',       accept: 'image/jpeg,image/png' },
                    { key: 'rec_frontal',   label: 'Frontal Rec.',    icon: 'fa-camera',        accept: 'image/jpeg,image/png' },
                    { key: 'rec_lateral',   label: 'Lateral Rec.',    icon: 'fa-camera-rotate', accept: 'image/jpeg,image/png' },
                ],
            },
            {
                key: 'fisica',
                label: 'Evidencia Física (Momento 1)',
                badgeClass: 'bg-label-info',
                icon: 'fa-camera',
                slots: [
                    { key: 'fis_vin',       label: 'Serie VIN',       icon: 'fa-barcode',       accept: 'image/jpeg,image/png' },
                    { key: 'fis_tacometro', label: 'Tacómetro',       icon: 'fa-gauge-high',    accept: 'image/jpeg,image/png' },
                    { key: 'fis_frontal',   label: 'Vista Frontal',   icon: 'fa-camera',        accept: 'image/jpeg,image/png' },
                    { key: 'fis_lateral',   label: 'Vista Lateral',   icon: 'fa-camera-rotate', accept: 'image/jpeg,image/png' },
                    { key: 'fis_360',       label: 'Inspección 360',  icon: 'fa-video',         accept: 'video/mp4', isVideo: true },
                ],
            },
        ];

        const EV_DOCS = [
            {
                key: 'repuve',  label: 'Momento 2: Repuve',
                badgeClass: 'bg-label-success', icon: 'fa-file-pdf',
                slotKey: 'doc_repuve',  slotLabel: 'Subir Repuve',
                accept: 'application/pdf',
            },
            {
                key: 'factura', label: 'Momento 3: Factura',
                badgeClass: 'bg-label-primary', icon: 'fa-file-invoice',
                slotKey: 'doc_factura', slotLabel: 'Subir Factura',
                accept: 'application/pdf,image/jpeg,image/png',
            },
        ];

        // ──────────────────────────────────────────────────────────────────
        // INIT
        // ──────────────────────────────────────────────────────────────────
        function opsPipelineInit() { opsCargarPipeline(); }

        // ──────────────────────────────────────────────────────────────────
        // CARGAR PIPELINE
        // ──────────────────────────────────────────────────────────────────
        function opsCargarPipeline() {
            document.getElementById('ops-loading').style.display = 'block';
            document.getElementById('ops-pipeline-board').style.display = 'none';

            fetch('/MotosAdjudicadas/obtenerOperaciones', { method: 'GET', headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Error al cargar el flujo.');
                    _operaciones = data.operaciones || [];
                    opsRenderPipeline(_operaciones);
                })
                .catch(err => {
                    Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'No se pudo cargar el flujo.', confirmButtonColor: '#696cff' });
                })
                .finally(() => {
                    document.getElementById('ops-loading').style.display = 'none';
                    document.getElementById('ops-pipeline-board').style.display = 'grid';
                });
        }

        // ──────────────────────────────────────────────────────────────────
        // RENDER PIPELINE
        // ──────────────────────────────────────────────────────────────────
        function opsRenderPipeline(ops) {
            const board = document.getElementById('ops-pipeline-board');
            board.innerHTML = '';

            const groups = {};
            STAGES.forEach(s => groups[s] = []);
            ops.forEach(op => {
                let stage = op.estatus;
                if (stage === 'en_transito') stage = 'Recibido';
                else if (stage === 'cancelado') stage = 'Retenciones';
                if (groups[stage]) groups[stage].push(op);
            });

            // Stats
            const statsEl = document.getElementById('ops-pipeline-stats');
            statsEl.innerHTML = `<span class="badge bg-label-secondary">Total: <strong>${ops.length}</strong></span>`;
            const atrasadas = ops.filter(o => (o.dias_en_pipeline || 0) > 5).length;
            if (atrasadas > 0) {
                statsEl.innerHTML += `<span class="badge bg-label-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>${atrasadas} atrasada${atrasadas > 1 ? 's' : ''}</span>`;
            }

            const OPS_TITULO_COL_VISIBLE = {
                'Revisión Recuperaciones': 'Recuperacion',
                'Recepción':               'Recepcion',
            };

            STAGES.forEach(stage => {
                const cards = groups[stage];
                const tituloCol = OPS_TITULO_COL_VISIBLE[stage] || stage;

                const col = document.createElement('div');
                col.className = 'card';
                col.style.cssText = 'min-width:0; overflow:hidden;';

                col.innerHTML = `
                <div class="card-header d-flex align-items-center justify-content-between py-2">
                    <h6 class="mb-0 text-uppercase fw-bold" style="font-size:.8rem; letter-spacing:.5px;">
                        <i class="fa-solid ${STAGE_ICONS[stage]} me-2 text-primary"></i>${opsEsc(tituloCol)}
                    </h6>
                    <span class="badge bg-primary rounded-pill">${cards.length}</span>
                </div>
                <div class="card-body p-2 overflow-auto" style="max-height:calc(100vh - 320px); scrollbar-width:thin;" id="ops-col-${opsSlug(stage)}">
                    ${cards.length === 0
                    ? `<div class="text-center py-4 text-muted"><i class="fa-regular fa-folder-open d-block mb-2" style="font-size:1.5rem; opacity:.4;"></i><small>Sin operaciones</small></div>`
                    : cards.map(opsRenderCard).join('')}
                </div>`;
                board.appendChild(col);
            });
        }

        function opsEsColumnaRetenciones(op) {
            return op.estatus === 'cancelado' || op.estatus === 'Retenciones';
        }

        function opsRetencionesLineaDetalle(op) {
            const tipo = String(op.ret_llamada_tipo_contacto || '').trim();
            const res  = String(op.ret_llamada_resultado     || '').trim();
            const dict = String(op.ret_llamada_dictamen      || '').trim();
            const parts = [tipo, res, dict].filter(Boolean);
            let line = parts.join(' · ');
            if (dict === 'Pendiente de contacto')   line += ' — Requiere reintento';
            else if (dict === 'No localizado')       line += ' — Sin reintento programado';
            return line || 'Contacto registrado';
        }

        // ──────────────────────────────────────────────────────────────────
        // RENDER CARD
        // ──────────────────────────────────────────────────────────────────
        function opsRenderCard(op) {
            const dias = parseInt(op.dias_en_pipeline || 0);
            let agingBadge = 'bg-label-primary';
            if (dias > 5)      agingBadge = 'bg-label-danger';
            else if (dias > 2) agingBadge = 'bg-label-warning';

            const evCount    = parseInt(op.evidencias_count || 0);
            const evPct      = EV_TOTAL_SLOTS > 0 ? Math.round((evCount / EV_TOTAL_SLOTS) * 100) : 0;
            const evComplete = evCount >= EV_TOTAL_SLOTS;

            const esCancelado = op.estatus === 'cancelado';
            const esTransito  = op.estatus === 'en_transito';

            // Borde izquierdo según estado
            let borderColor = 'border-primary';
            if (esCancelado) borderColor = 'border-danger';
            else if (esTransito) borderColor = 'border-info';

            const transitoBadge  = esTransito  ? `<span class="badge bg-label-info mt-1"><i class="fa-solid fa-truck-fast me-1"></i>En tránsito</span>` : '';
            const canceladoBadge = esCancelado ? `<span class="badge bg-label-danger mt-1"><i class="fa-solid fa-ban me-1"></i>Cancelado</span>` : '';

            // Columnas que usan card compacto (una sola línea densa)
            const esCompacto = opsEsColumnaRetenciones(op)
                || op.estatus === 'en_transito'
                || op.estatus === 'Recibido'
                || op.estatus === 'Revisión Recuperaciones';

            if (opsEsColumnaRetenciones(op)) {
                const fechaLlamada    = String(op.ret_llamada_fecha_fmt || '').trim();
                const tieneLlamadaRet = !!fechaLlamada;
                const piePipe         = String(op.ret_registro_pipe_fmt || op.fecha_alta || '').trim();
                const statusTxt = !tieneLlamadaRet
                    ? `<span class="text-danger fw-bold" style="font-size:.7rem;">Pendiente de llamada</span>`
                    : `<span class="text-muted" style="font-size:.7rem;">${opsEsc(opsRetencionesLineaDetalle(op))}</span>`;
                const footTxt = `<span class="text-muted" style="font-size:.68rem;">${opsEsc(!tieneLlamadaRet ? (piePipe || '—') : fechaLlamada)}</span>`;
                return `
            <div class="card mb-1 border-start border-3 ${borderColor}" style="cursor:pointer;border-radius:.5rem;" onclick="opsAbrirDetalle(${op.id})">
                <div class="px-2 py-1">
                    <div class="d-flex align-items-center justify-content-between gap-1">
                        <span class="badge bg-label-primary" style="font-size:.65rem;">${opsEsc(op.folio)}</span>
                        <span class="text-muted text-truncate" style="font-size:.68rem; max-width:55%;">#${opsEsc(String(op.id_credito))}</span>
                        ${canceladoBadge}
                    </div>
                    <div class="fw-semibold text-truncate" style="font-size:.78rem;">${opsEsc(op.nombre_cliente)}</div>
                    <div class="d-flex align-items-center justify-content-between gap-1 mt-1 pt-1 border-top">
                        ${statusTxt}${footTxt}
                    </div>
                </div>
            </div>`;
            }

            if (esCompacto) {
                // Card minimalista de una fila: folio | nombre | crédito | días
                return `
            <div class="card mb-1 border-start border-3 ${borderColor}" style="cursor:pointer;border-radius:.5rem;" onclick="opsAbrirDetalle(${op.id})">
                <div class="px-2 py-1">
                    <div class="d-flex align-items-center justify-content-between gap-1">
                        <span class="badge bg-label-primary" style="font-size:.65rem;">${opsEsc(op.folio)}</span>
                        <span class="badge ${agingBadge}" style="font-size:.65rem;">${dias}d</span>
                    </div>
                    <div class="fw-semibold text-truncate" style="font-size:.78rem;">${opsEsc(op.nombre_cliente)}</div>
                    <div class="text-muted text-truncate" style="font-size:.7rem;">#${opsEsc(String(op.id_credito))}${op.area_actual ? '&nbsp;<span class="badge bg-label-secondary" style="font-size:.6rem;">' + opsEsc(op.area_actual) + '</span>' : ''}</div>
                </div>
            </div>`;
            }

            // Card completo (Cierre Documentado, Recepción)
            return `
        <div class="card card-body p-2 mb-2 border-start border-3 ${borderColor}" style="cursor:pointer;" onclick="opsAbrirDetalle(${op.id})">
            <div class="d-flex align-items-center justify-content-between mb-1 gap-1">
                <span class="badge bg-label-primary">${opsEsc(op.folio)}</span>
                <span class="badge ${agingBadge}">${dias}d</span>
            </div>
            <div class="fw-semibold small text-truncate">${opsEsc(op.nombre_cliente)}</div>
            <small class="text-muted">#${opsEsc(String(op.id_credito))}${op.area_actual ? ' &nbsp;<span class="badge bg-label-secondary">' + opsEsc(op.area_actual) + '</span>' : ''}</small>
            <div class="mt-2">
                <div class="progress mb-1" style="height:5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width:${evPct}%;"></div>
                </div>
                <small class="${evComplete ? 'text-success' : 'text-muted'}">
                    ${evComplete
                ? '<i class="fa-solid fa-circle-check me-1"></i>Evidencias completas'
                : `<i class="fa-solid fa-image me-1"></i>${evCount}/${EV_TOTAL_SLOTS} evidencias`}
                </small>
            </div>
            ${canceladoBadge}${transitoBadge}
        </div>`;
        }

        // ──────────────────────────────────────────────────────────────────
        // TEXTO ETAPA USUARIO
        // ──────────────────────────────────────────────────────────────────
        function opsTextoEtapaUsuario(op) {
            const est = (op.estatus || '').toString().trim();
            const MAP = {
                'Retenciones':             'Actualmente se encuentra en Retenciones',
                'cancelado':               'Actualmente se encuentra en Retenciones',
                'en_transito':             'Actualmente se encuentra en evidencias',
                'Revisión Recuperaciones': 'Actualmente se encuentra en Recuperacion',
                'Cierre Documentado':      'Actualmente se encuentra en Cierre Documentado',
                'Recepción':               'Actualmente se encuentra en Recepción',
            };
            return MAP[est] || (est ? est.replace(/_/g, ' ') : '—');
        }

        // ──────────────────────────────────────────────────────────────────
        // ABRIR DETALLE
        // ──────────────────────────────────────────────────────────────────
        function opsAbrirDetalle(id) {
            _detalleActual = null;
            document.getElementById('det-body').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
            </div>`;
            new bootstrap.Modal(document.getElementById('modalDetalleOperacion')).show();

            const fetchDetalle  = fetch(`/MotosAdjudicadas/obtenerDetalle/${id}`,          { headers: { 'Accept': 'application/json' } }).then(r => r.json());
            const fetchDictamen = fetch(`/AtencionClientes/obtenerDictamen?id=${id}`,       { headers: { 'Accept': 'application/json' } }).then(r => r.json()).catch(() => null);

            Promise.all([fetchDetalle, fetchDictamen])
                .then(([detalleResp, dictamenResp]) => {
                    if (!detalleResp.success) throw new Error(detalleResp.message);
                    _detalleActual = detalleResp.detalle;
                    const dictamen          = (dictamenResp && dictamenResp.success) ? dictamenResp.dictamen : null;
                    const historialLlamadas = (dictamenResp && dictamenResp.success && Array.isArray(dictamenResp.historial_llamadas))
                        ? dictamenResp.historial_llamadas : [];
                    opsRenderDetalle(detalleResp.detalle, dictamen, historialLlamadas);
                })
                .catch(err => {
                    document.getElementById('det-body').innerHTML = `<div class="alert alert-danger m-3">${opsEsc(err.message)}</div>`;
                });
        }

        function opsRenderDetalle(op, dictamen, historialLlamadas) {
            historialLlamadas = Array.isArray(historialLlamadas) ? historialLlamadas : [];
            _activeOpId = op.id;
            const textoEtapa = opsTextoEtapaUsuario(op);

            const html = `
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 p-3 mb-3 rounded border">
            <div>
                <div class="text-muted" style="font-size:.65rem; text-transform:uppercase; letter-spacing:.5px;">Cliente</div>
                <div class="fw-bold fs-5">${opsEsc(op.nombre_cliente)}</div>
                <span class="badge bg-primary mt-1">${opsEsc(textoEtapa)}</span>
            </div>
            <div class="text-end">
                <div class="text-muted" style="font-size:.65rem; text-transform:uppercase; letter-spacing:.5px;">ID Crédito</div>
                <div class="fw-black text-primary fs-4">#${opsEsc(String(op.id_credito))}</div>
                <div class="text-muted small">${opsEsc(op.folio)}</div>
            </div>
        </div>
        ${opsRenderDatosExpediente(op, dictamen, historialLlamadas)}`;

            document.getElementById('det-body').innerHTML = html;
        }

        // ──────────────────────────────────────────────────────────────────
        // KV HELPER
        // ──────────────────────────────────────────────────────────────────
        function opsExpKv(lbl, content, rawHtml) {
            const empty = content === null || content === undefined || content === '';
            const inner = empty
                ? '<span class="text-muted fst-italic">—</span>'
                : (rawHtml ? content : '<span class="fw-semibold small">' + opsEsc(String(content)) + '</span>');
            return `<div>
            <div class="text-muted fw-semibold mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;">${opsEsc(lbl)}</div>
            ${inner}
        </div>`;
        }

        // ──────────────────────────────────────────────────────────────────
        // TABLA HISTORIAL LLAMADAS
        // ──────────────────────────────────────────────────────────────────
        const LLAMADA_LABELS = ['1ra llamada', '2da llamada', '3ra llamada'];

        const LLAMADA_DICTAMEN_BADGE = {
            'Localizado':              'bg-label-success',
            'No localizado':           'bg-label-danger',
            'Pendiente de contacto':   'bg-label-warning',
            'Comprometido a entregar': 'bg-label-info',
            'Negativa de entrega':     'bg-label-danger',
        };

        function opsRenderTablaHistorialLlamadas(rows) {
            if (!rows || !rows.length) return '';

            const ICONS = [
                'fa-phone',
                'fa-phone-flip',
                'fa-headset',
            ];

            const cards = rows.map((row, idx) => {
                const label    = LLAMADA_LABELS[idx] || `Llamada ${idx + 1}`;
                const icon     = ICONS[idx] || 'fa-phone';
                const dictRaw  = row && row.dictamen       != null ? String(row.dictamen).trim()       : '';
                const comRaw   = row && row.comentarios    != null ? String(row.comentarios).trim()    : '';
                const usrRaw   = row && row.registrado_nombre != null ? String(row.registrado_nombre).trim() : '';
                const fhRaw    = row && row.fecha_alta_fmt != null ? String(row.fecha_alta_fmt)        : '';

                const dictBadgeClass = LLAMADA_DICTAMEN_BADGE[dictRaw] || 'bg-label-secondary';

                const dictHtml = dictRaw
                    ? `<span class="badge ${dictBadgeClass} rounded-pill">${opsEsc(dictRaw)}</span>`
                    : `<span class="text-muted fst-italic small">Sin dictamen</span>`;

                const comHtml = comRaw
                    ? `<p class="mb-0 small lh-base" style="white-space:pre-wrap;color:var(--bs-body-color);">${opsEsc(comRaw)}</p>`
                    : `<p class="mb-0 small fst-italic text-muted">Sin comentarios registrados.</p>`;

                const isLast = idx === rows.length - 1;
                /* conector vertical entre tarjetas (excepto la última) */
                const connector = !isLast ? `
                <div class="d-flex justify-content-center" style="margin:-2px 0; position:relative; z-index:0; pointer-events:none;">
                    <div style="width:2px; height:18px; background:var(--bs-border-color);"></div>
                </div>` : '';

                return `
            <div class="card border shadow-none mb-0" style="border-radius:.75rem !important;">
                <div class="card-header d-flex align-items-center gap-2 py-2 px-3"
                     style="background:var(--bs-tertiary-bg, #f8f9fa); border-bottom:1px solid var(--bs-border-color); border-radius:.75rem .75rem 0 0 !important;">
                    <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center"
                          style="width:26px;height:26px;font-size:.72rem;flex-shrink:0;">${idx + 1}</span>
                    <i class="fa-solid ${icon} text-primary" style="font-size:.85rem;"></i>
                    <span class="fw-bold text-uppercase" style="font-size:.72rem;letter-spacing:.5px;">${opsEsc(label)}</span>
                    <div class="ms-auto">${dictHtml}</div>
                </div>
                <div class="card-body px-3 py-2">
                    <p class="text-muted fw-semibold mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;">Comentarios</p>
                    ${comHtml}
                </div>
                ${(usrRaw || fhRaw) ? `
                <div class="card-footer d-flex align-items-center justify-content-between gap-2 py-2 px-3"
                     style="background:transparent; border-top:1px dashed var(--bs-border-color); border-radius:0 0 .75rem .75rem !important;">
                    <span class="small text-muted d-flex align-items-center gap-1">
                        <i class="fa-regular fa-user" style="font-size:.75rem;"></i>${opsEsc(usrRaw || '—')}
                    </span>
                    <span class="small text-muted d-flex align-items-center gap-1">
                        <i class="fa-regular fa-clock" style="font-size:.75rem;"></i>${opsEsc(fhRaw || '—')}
                    </span>
                </div>` : ''}
            </div>
            ${connector}`;
            }).join('');

            return `<div class="col-12 mt-3 pt-3 border-top">
            <p class="text-muted fw-semibold mb-2" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;">
                <i class="fa-solid fa-timeline me-1"></i>Historial de llamadas
            </p>
            <div class="d-flex flex-column gap-0">${cards}</div>
        </div>`;
        }

        // ──────────────────────────────────────────────────────────────────
        // DATOS EXPEDIENTE
        // ──────────────────────────────────────────────────────────────────
        function opsRenderDatosExpediente(op, d, historialLlamadas) {
            historialLlamadas = Array.isArray(historialLlamadas) ? historialLlamadas : [];

            let estatusBadge;
            if (!d) {
                estatusBadge = `<span class="badge bg-label-warning"><i class="fa-solid fa-clock me-1"></i>Pendiente de gestión</span>`;
            } else if (op.estatus === 'cancelado') {
                estatusBadge = `<span class="badge bg-label-danger"><i class="fa-solid fa-ban me-1"></i>Cancelado</span>`;
            } else {
                estatusBadge = `<span class="badge bg-label-info"><i class="fa-solid fa-truck-fast me-1"></i>En tránsito</span>`;
            }

            const tieneHistorial = historialLlamadas.length > 0;
            let resumenCols = '';
            if (tieneHistorial) {
                resumenCols = `
                <div class="col-sm-6">${opsExpKv('Estatus dictamen', estatusBadge, true)}</div>`;
            } else {
                const comHtml = d && d.comentarios
                    ? `<span class="small fw-semibold" style="white-space:pre-line;">${opsEsc(d.comentarios)}</span>`
                    : null;
                resumenCols = `
                <div class="col-sm-6 col-lg-4">${opsExpKv('Estatus dictamen', estatusBadge, true)}</div>
                <div class="col-sm-6 col-lg-4">${opsExpKv('Dictamen', d && d.dictamen ? d.dictamen : null)}</div>
                <div class="col-sm-6 col-lg-4">${opsExpKv('Fecha dictamen', d && d.fecha_alta_fmt ? d.fecha_alta_fmt : null)}</div>
                <div class="col-12 mt-2">${opsExpKv('Comentarios', comHtml, true)}</div>`;
            }

            const bloqueHistorial = tieneHistorial ? opsRenderTablaHistorialLlamadas(historialLlamadas) : '';

            return `
        <div class="card mb-0">
            <div class="card-header py-2">
                <h6 class="mb-0 small fw-bold text-uppercase">
                    <i class="fa-solid fa-headset me-2 text-primary"></i>Atención a clientes informa
                </h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    ${resumenCols}
                    ${bloqueHistorial}
                </div>
            </div>
        </div>`;
        }

        // ──────────────────────────────────────────────────────────────────
        // EVIDENCE VIEWER
        // ──────────────────────────────────────────────────────────────────
        function opsSlotView(key, opId) {
            const ev = _evState[`${opId}_${key}`];
            if (!ev || !ev.src) return;
            const img = document.getElementById('ops-av-img');
            const vid = document.getElementById('ops-av-vid');
            const pdf = document.getElementById('ops-av-pdf');
            if (!img) return;
            img.style.display = 'none';
            vid.style.display = 'none';
            if (pdf) { pdf.style.display = 'none'; pdf.src = ''; }
            if (ev.type === 'video') {
                vid.src = ev.src; vid.style.display = 'block';
            } else if (ev.type === 'pdf') {
                if (pdf) { pdf.src = ev.src; pdf.style.display = 'block'; }
                else window.open(ev.src, '_blank');
            } else {
                img.src = ev.src; img.style.display = 'block';
            }
        }

        // ──────────────────────────────────────────────────────────────────
        // OBSERVACIONES
        // ──────────────────────────────────────────────────────────────────
        function opsAgregarObservacion(idOperacion, etapa, area) {
            const texto = (document.getElementById('det-obs-input')?.value || '').trim();
            if (!texto) return;
            fetch('/MotosAdjudicadas/agregarObservacion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_operacion: idOperacion, etapa, area, texto }),
            })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#696cff' });
                        return;
                    }
                    const lista = document.getElementById('det-obs-list');
                    if (lista) {
                        const nueva = `<div class="d-flex gap-2 mb-2 align-items-start small">
                    <i class="fa-regular fa-comment mt-1 text-primary flex-shrink-0"></i>
                    <div>
                        <div>${opsEsc(texto)}</div>
                        <div class="text-muted" style="font-size:.68rem;">${opsEsc(etapa)} · ${opsEsc(data.fecha || '')}</div>
                    </div>
                </div>`;
                        lista.insertAdjacentHTML('beforeend', nueva);
                        lista.scrollTop = lista.scrollHeight;
                    }
                    document.getElementById('det-obs-input').value = '';
                })
                .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#696cff' }));
        }

        // ──────────────────────────────────────────────────────────────────
        // HELPERS
        // ──────────────────────────────────────────────────────────────────
        function opsEsc(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
        function opsSlug(str) {
            return str.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        }

        // ──────────────────────────────────────────────────────────────────
        // EXPONER GLOBALES
        // ──────────────────────────────────────────────────────────────────
        window.opsCargarPipeline     = opsCargarPipeline;
        window.opsAbrirDetalle       = opsAbrirDetalle;
        window.opsAgregarObservacion = opsAgregarObservacion;
        window.opsSlotView           = opsSlotView;

        document.addEventListener('DOMContentLoaded', opsPipelineInit);
        if (document.readyState !== 'loading') opsPipelineInit();

    })();
</script>
