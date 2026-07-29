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
<div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="ops-btn-refresh" onclick="opsCargarPipeline()">
            <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
        </button>
        <div class="ops-search-wrap">
            <i class="fa-solid fa-magnifying-glass ops-search-icon"></i>
            <input type="text" autocomplete="off" class="ops-search-input" id="ops-search-id" placeholder="Buscar ID o cliente">
            <button class="ops-search-clear" type="button" id="ops-search-clear" title="Limpiar busqueda">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>
    <div id="ops-pipeline-stats" class="d-flex gap-2 flex-wrap align-items-center"></div>
</div>

<style>
    .ops-search-wrap {
        position: relative;
        width: min(360px, calc(100vw - 2rem));
    }
    .ops-search-icon {
        position: absolute;
        left: .85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c7a89;
        font-size: .9rem;
        pointer-events: none;
    }
    .ops-search-input {
        width: 100%;
        height: 2.35rem;
        border: 1px solid #d6dfeb;
        border-radius: 999px;
        background: #fff;
        color: #26364d;
        font-size: .92rem;
        padding: .45rem 2.65rem .45rem 2.35rem;
        outline: none;
        box-shadow: 0 2px 8px rgba(23, 36, 58, .06);
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .ops-search-input:focus {
        border-color: #f0a329;
        box-shadow: 0 0 0 .18rem rgba(240, 163, 41, .16), 0 2px 8px rgba(23, 36, 58, .08);
    }
    .ops-search-clear {
        position: absolute;
        right: .35rem;
        top: 50%;
        transform: translateY(-50%);
        width: 1.7rem;
        height: 1.7rem;
        border: 0;
        border-radius: 50%;
        background: #fff3df;
        color: #d58a12;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: .8rem;
        opacity: 0;
        pointer-events: none;
        transition: opacity .15s ease, background .15s ease;
    }
    .ops-search-wrap.has-value .ops-search-clear {
        opacity: 1;
        pointer-events: auto;
    }
    .ops-search-clear:hover {
        background: #ffe7bd;
    }
</style>

<!-- Kanban Board -->
<div id="ops-pipeline-board" style="display:none; grid-template-columns:repeat(4,minmax(260px,1fr)); gap:1rem; min-height:calc(100vh - 260px);"></div>

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
            'Recibido',
            'Validacion IA',
            'Revisión Recuperaciones',
            'Cierre Documentado',
            'Recepción',
        ];

        const STAGE_ICONS = {
            'Recibido':                  'fa-inbox',
            'Validacion IA':              'fa-robot',
            'Revisión Recuperaciones':   'fa-magnifying-glass',
            'Cierre Documentado':        'fa-file-circle-check',
            'Recepción':                 'fa-flag-checkered',
        };

        /** Expediente: física M1 + Repuve PDF + Factura (sin recolección). */
        const EV_TOTAL_SLOTS = 13;

        /**
         * Modal Detalle hasta Repuve PDF: sin factura (momento 3 en Recuperación).
         */
        const OPS_MODAL_RECIBIDO_SLOTS = [
            'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
            'fis_vin',
            'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
            'fis_tacometro',
            'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba',
            'doc_repuve',
        ];
        const OPS_MODAL_RECIBIDO_SLOT_TOTAL = OPS_MODAL_RECIBIDO_SLOTS.length;
        const OPS_EXPEDIENTE_TOTAL_CON_FACTURA = OPS_MODAL_RECIBIDO_SLOT_TOTAL + 1;

        let _operaciones   = [];
        let _detalleActual = null;
        let _evState       = {};
        let _activeOpId    = null;
        let _opsFiltroId   = '';
        let _opsSearchTimer = null;
        let _opsTotal       = 0;
        let _opsLimit       = 500;

        // ──────────────────────────────────────────────────────────────────
        // EVIDENCE DEFINITIONS
        // ──────────────────────────────────────────────────────────────────
        const EV_SECTIONS = [
            {
                key: 'fisica',
                label: 'Evidencia Física (Momento 1)',
                badgeClass: 'bg-label-info',
                icon: 'fa-camera',
                slots: [
                    { key: 'fis_dacion_hoja_1', label: 'Foto Dación (Hoja 1)', icon: 'fa-file-signature', accept: 'image/jpeg,image/png,application/pdf' },
                    { key: 'fis_dacion_hoja_2', label: 'Foto Dación (Hoja 2)', icon: 'fa-file-signature', accept: 'image/jpeg,image/png,application/pdf' },
                    { key: 'fis_vin', label: 'Foto NIV (VIN)', icon: 'fa-barcode', accept: 'image/jpeg,image/png' },
                    { key: 'fis_frontal', label: 'Foto frontal', icon: 'fa-camera', accept: 'image/jpeg,image/png' },
                    { key: 'fis_lateral_der', label: 'Foto lateral derecha', icon: 'fa-camera-rotate', accept: 'image/jpeg,image/png' },
                    { key: 'fis_trasera', label: 'Foto trasera', icon: 'fa-camera-retro', accept: 'image/jpeg,image/png' },
                    { key: 'fis_lateral_izq', label: 'Foto lateral izquierda', icon: 'fa-camera-rotate', accept: 'image/jpeg,image/png' },
                    { key: 'fis_tacometro', label: 'Foto tacómetro', icon: 'fa-gauge-high', accept: 'image/jpeg,image/png' },
                    { key: 'fis_video_cliente_acuerdo', label: 'Video cliente de acuerdo', icon: 'fa-user-check', accept: 'video/mp4', isVideo: true },
                    { key: 'fis_360_encendida', label: 'Video moto 360 encendida', icon: 'fa-video', accept: 'video/mp4', isVideo: true },
                    { key: 'fis_video_vuelta_prueba', label: 'Video vuelta de prueba', icon: 'fa-road', accept: 'video/mp4', isVideo: true },
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
        function opsPipelineInit() {
            opsInitBuscador();
            opsCargarPipeline();
        }

        function opsInitBuscador() {
            const input = document.getElementById('ops-search-id');
            const clear = document.getElementById('ops-search-clear');
            if (!input || input.dataset.ready === '1') return;
            const wrap = input.closest('.ops-search-wrap');
            const syncState = () => wrap?.classList.toggle('has-value', input.value.trim() !== '');
            input.dataset.ready = '1';
            input.addEventListener('input', () => {
                _opsFiltroId = input.value.trim().toLowerCase();
                syncState();
                window.clearTimeout(_opsSearchTimer);
                _opsSearchTimer = window.setTimeout(() => opsCargarPipeline(), 320);
            });
            clear?.addEventListener('click', () => {
                input.value = '';
                _opsFiltroId = '';
                syncState();
                opsCargarPipeline();
                input.focus();
            });
            syncState();
        }

        function opsFiltrarOperaciones() {
            return _operaciones;
        }

        // ──────────────────────────────────────────────────────────────────
        // CARGAR PIPELINE
        // ──────────────────────────────────────────────────────────────────
        function opsCargarPipeline() {
            document.getElementById('ops-loading').style.display = 'block';
            document.getElementById('ops-pipeline-board').style.display = 'none';

            const params = new URLSearchParams({ limit: String(_opsLimit) });
            if (_opsFiltroId) params.set('q', _opsFiltroId);

            fetch('/MotosAdjudicadas/obtenerOperaciones?' + params.toString(), { method: 'GET', headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Error al cargar el flujo.');
                    _operaciones = data.operaciones || [];
                    _opsTotal = parseInt(data.total || _operaciones.length || 0);
                    _opsLimit = parseInt(data.limit || _opsLimit || 500);
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
            ops = ops.filter(op => op.estatus !== 'cancelado' && op.estatus !== 'Retenciones');

            const groups = {};
            STAGES.forEach(s => groups[s] = []);
            ops.forEach(op => {
                let stage = op.estatus;
                if (stage === 'en_transito') stage = 'Recibido';
                else if (stage === 'cancelado' || stage === 'Retenciones') return;
                /* Misma bandeja que 2.- Recuperación (Evidencias → Aprobados): sigue en BD como Procesando IA */
                else if (stage === 'Procesando IA' || stage === 'Bloqueado IA') stage = 'Validacion IA';
                if (groups[stage]) groups[stage].push(op);
            });

            // Stats
            const statsEl = document.getElementById('ops-pipeline-stats');
            const total = _opsTotal || ops.length;
            const mostrandoTxt = total > ops.length
                ? `Mostrando <strong>${ops.length}</strong> de <strong>${total}</strong>`
                : `Total: <strong>${ops.length}</strong>`;
            statsEl.innerHTML = `<span class="badge bg-label-secondary">${mostrandoTxt}</span>`;
            if (_opsFiltroId) {
                statsEl.innerHTML += `<span class="badge bg-label-info"><i class="fa-solid fa-filter me-1"></i>${opsEsc(_opsFiltroId)}</span>`;
            }
            if (!_opsFiltroId && total > ops.length) {
                statsEl.innerHTML += `<span class="badge bg-label-warning"><i class="fa-solid fa-shield-halved me-1"></i>Vista limitada; usa el buscador</span>`;
            }
            const atrasadas = ops.filter(o => (o.dias_en_pipeline || 0) > 5).length;
            if (atrasadas > 0) {
                statsEl.innerHTML += `<span class="badge bg-label-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>${atrasadas} atrasada${atrasadas > 1 ? 's' : ''}</span>`;
            }

            const OPS_TITULO_COL_VISIBLE = {
                'Recibido':                'Evidencia',
                'Revisión Recuperaciones': 'Recuperacion',
                'Cierre Documentado':      'Cartera',
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
            const fechaEstatus = String(op.fecha_estatus_actual || op.fecha_actualizacion || op.fecha_alta || '').trim();
            const fechaRegistro = String(op.fecha_alta || '').trim();
            const niv = String(op.niv || op.moto_no_serie || op.serie || '').trim();
            const nivLinea = niv
                ? `<div class="text-muted text-truncate mt-1" title="NIV: ${opsEsc(niv)}" style="font-size:.64rem;"><i class="fa-solid fa-barcode me-1"></i>NIV: ${opsEsc(niv)}</div>`
                : '<div class="text-muted mt-1" style="font-size:.64rem;"><i class="fa-solid fa-barcode me-1"></i>NIV: Sin capturar</div>';
            const antiguedadTitle = `Tiempo en estatus ${op.estatus || ''}${fechaEstatus ? ' desde ' + fechaEstatus : ''}`;
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
                || op.estatus === 'Validacion IA'
                || op.estatus === 'Bloqueado IA'
                || op.estatus === 'Procesando IA'
                || op.estatus === 'Revisión Recuperaciones';

            if (opsEsColumnaRetenciones(op)) {
                const fechaLlamada    = String(op.ret_llamada_fecha_fmt || '').trim();
                const tieneLlamadaRet = !!fechaLlamada;
                const piePipe         = String(op.ret_registro_pipe_fmt || op.fecha_alta || '').trim();
                const statusTxt = !tieneLlamadaRet
                    ? `<span class="text-danger fw-bold" style="font-size:.7rem;">Pendiente de llamada</span>`
                    : `<span class="text-muted" style="font-size:.7rem;">${opsEsc(opsRetencionesLineaDetalle(op))}</span>`;
                const fechaPie = !tieneLlamadaRet ? (piePipe || '—') : fechaLlamada;
                return `
            <div class="card mb-1 border-start border-3 ${borderColor}" style="cursor:pointer;border-radius:.5rem;display:flex;flex-direction:column;" onclick="opsAbrirDetalle(${op.id})">
                <div class="px-2 pt-2 pb-1 flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                        <span class="badge bg-label-primary" style="font-size:.65rem;">${opsEsc(op.folio)}</span>
                        <span class="text-muted text-truncate" style="font-size:.68rem; max-width:55%;">#${opsEsc(String(op.id_credito))}</span>
                        ${canceladoBadge}
                    </div>
                    <div class="fw-semibold text-truncate" style="font-size:.78rem;">${opsEsc(op.nombre_cliente)}</div>
                    ${nivLinea}
                    <div class="mt-1">${statusTxt}</div>
                </div>
                <div class="px-2 py-1 border-top d-flex align-items-center gap-1" style="background:var(--bs-tertiary-bg,#f8f9fa);border-radius:0 0 .35rem .35rem;">
                    <i class="fa-regular fa-clock text-muted" style="font-size:.62rem;flex-shrink:0;"></i>
                    <span class="text-muted fw-semibold" style="font-size:.62rem;flex-shrink:0;">Últ. act.&nbsp;</span>
                    <span class="text-muted text-truncate" style="font-size:.65rem;">${opsEsc(fechaPie)}</span>
                </div>
            </div>`;
            }

            if (esCompacto) {
                // Card minimalista de una fila: folio | nombre | crédito | días
                return `
            <div class="card mb-2 border-start border-3 ${borderColor}" style="cursor:pointer;border-radius:.7rem;box-shadow:0 2px 8px rgba(23,36,58,.06);" onclick="opsAbrirDetalle(${op.id})">
                <div class="px-3 py-3">
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                        <span class="text-muted text-truncate" style="font-size:.72rem;">#${opsEsc(String(op.id_credito))}</span>
                        <span class="text-danger text-truncate" title="Fecha de registro / adjudicacion" style="font-size:.62rem;">Registro: ${opsEsc(fechaRegistro || 'sin fecha')}</span>
                    </div>
                    <div class="fw-medium" style="font-size:.7rem;line-height:1.14;">${opsEsc(op.nombre_cliente)}</div>
                    ${nivLinea}
                    <div class="d-flex justify-content-end mt-1">
                        <span class="badge ${agingBadge}" title="${opsEsc(antiguedadTitle)}" style="font-size:.58rem;padding:.2rem .42rem;">${dias} días en esta etapa</span>
                    </div>
                    ${op.area_actual ? '<div class="text-muted text-truncate mt-1"><span class="badge bg-label-secondary" style="font-size:.68rem;">' + opsEsc(op.area_actual) + '</span></div>' : ''}
                </div>
            </div>`;
            }

            // Card completo (Cartera / Cierre Documentado en BD, Recepción)
            return `
        <div class="card card-body p-3 mb-3 border-start border-3 ${borderColor}" style="cursor:pointer;border-radius:.7rem;box-shadow:0 2px 8px rgba(23,36,58,.06);" onclick="opsAbrirDetalle(${op.id})">
            <div class="d-flex align-items-center justify-content-between mb-2 gap-2">
                <span class="text-muted text-truncate" style="font-size:.72rem;">#${opsEsc(String(op.id_credito))}</span>
                <span class="text-danger text-truncate" title="Fecha de registro / adjudicacion" style="font-size:.62rem;">Registro: ${opsEsc(fechaRegistro || 'sin fecha')}</span>
            </div>
            <div class="fw-medium" style="font-size:.71rem;line-height:1.14;">${opsEsc(op.nombre_cliente)}</div>
            ${nivLinea}
            <div class="d-flex justify-content-end mt-1">
                <span class="badge ${agingBadge}" title="${opsEsc(antiguedadTitle)}" style="font-size:.58rem;padding:.2rem .42rem;">${dias} días en esta etapa</span>
            </div>
            ${op.area_actual ? '<small class="text-muted"><span class="badge bg-label-secondary">' + opsEsc(op.area_actual) + '</span></small>' : ''}
            <div class="mt-3">
                <div class="progress mb-1" style="height:5px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width:${evPct}%;"></div>
                </div>
                <small class="${evComplete ? 'text-muted' : 'text-muted'}" style="font-size:.72rem;">
                    ${evComplete
                ? '<i class="fa-solid fa-circle-check me-1" style="color:#7bbf5b;"></i>Evidencias completas'
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
                'Recibido':                'Actualmente se encuentra en evidencias',
                'Validacion IA':           'Validacion automatica del estado de la moto en proceso',
                'Bloqueado IA':            'Adjudicacion bloqueada por la validacion del estado de la moto',
                'Procesando IA':           'Actualmente se encuentra en Recuperacion',
                'Revisión Recuperaciones': 'Actualmente se encuentra en Recuperacion',
                'Cierre Documentado':      'Actualmente se encuentra en Cartera',
                'Recepción':               'Actualmente se encuentra en Recepción',
            };
            return MAP[est] || (est ? est.replace(/_/g, ' ') : '—');
        }

        // ──────────────────────────────────────────────────────────────────
        // ABRIR DETALLE
        // ──────────────────────────────────────────────────────────────────
        /** Dictamen + historial de llamadas solo aplican a etapa Retenciones. */
        function opsEsEtapaRetencionesDetalle(op) {
            return op && (op.estatus === 'cancelado' || op.estatus === 'Retenciones');
        }

        /** Modal de evidencias: Recibido / en tránsito (columna evidencia del pipeline). */
        function opsEsEtapaRecibidoEvidenciaDetalle(op) {
            return op && (op.estatus === 'en_transito' || op.estatus === 'Recibido');
        }

        /** Modal Recuperación: progreso expediente hasta factura (momento 3). */
        function opsEsEtapaRecuperacionDetalle(op) {
            return op && (op.estatus === 'Validacion IA' || op.estatus === 'Procesando IA' || op.estatus === 'Revisión Recuperaciones' || op.estatus === 'Bloqueado IA');
        }

        function opsAbrirDetalle(id) {
            _detalleActual = null;
            document.getElementById('det-body').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary"></div>
            </div>`;
            new bootstrap.Modal(document.getElementById('modalDetalleOperacion')).show();

            fetch(`/MotosAdjudicadas/obtenerDetalle/${id}`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(detalleResp => {
                    if (!detalleResp.success) throw new Error(detalleResp.message);
                    const op = detalleResp.detalle;
                    _detalleActual = op;
                    if (opsEsEtapaRetencionesDetalle(op)) {
                        return fetch(`/AtencionClientes/obtenerDictamen?id=${id}`, { headers: { 'Accept': 'application/json' } })
                            .then(r => r.json())
                            .catch(() => null)
                            .then(dictamenResp => {
                                const dictamen = (dictamenResp && dictamenResp.success) ? dictamenResp.dictamen : null;
                                const historialLlamadas = (dictamenResp && dictamenResp.success && Array.isArray(dictamenResp.historial_llamadas))
                                    ? dictamenResp.historial_llamadas : [];
                                opsRenderDetalle(op, dictamen, historialLlamadas);
                            });
                    }
                    opsRenderDetalle(op, null, []);
                    return null;
                })
                .catch(err => {
                    document.getElementById('det-body').innerHTML = `<div class="alert alert-danger m-3">${opsEsc(err.message)}</div>`;
                });
        }

        /** Índice de etapa actual en el pipeline (0–4) para colorear el stepper. */
        function opsPipelineStageIndex(op) {
            const est = (op && op.estatus != null) ? String(op.estatus).trim() : '';
            if (est === 'cancelado') {
                return { current: 4, rejected: true };
            }
            if (est === 'Retenciones') {
                return { current: 4, rejected: false };
            }
            if (est === 'en_transito' || est === 'Recibido') {
                return { current: 0, rejected: false };
            }
            if (est === 'Validacion IA' || est === 'Procesando IA' || est === 'Revisión Recuperaciones' || est === 'Bloqueado IA') {
                return { current: 1, rejected: false };
            }
            if (est === 'Cierre Documentado') {
                return { current: 2, rejected: false };
            }
            if (est === 'Recepción') {
                return { current: 3, rejected: false };
            }
            return { current: 0, rejected: false };
        }

        function opsHtmlStepperEtapaModal(op) {
            const labels = ['Evidencia', 'Recuperación', 'Cartera', 'Recepción', 'Retenciones'];
            const { current } = opsPipelineStageIndex(op);
            const sep = '<div style="width:24px;border-top:1.5px solid #ccc;margin-top:13px;"></div>';
            let parts = '';
            parts += '<div class="d-flex flex-column align-items-stretch">';
            parts += '<div class="d-flex justify-content-end w-100 mb-2"><span class="text-muted small fw-semibold">Etapa</span></div>';
            parts += '<div class="d-flex align-items-start">';
            for (let i = 0; i < 5; i++) {
                const num = String(i + 1);
                parts += '<div class="d-flex flex-column align-items-center">';
                if (i < current) {
                    /* Etapa ya cumplida: verde Bootstrap (bg-success) */
                    parts += '<div class="d-flex align-items-center justify-content-center fw-semibold rounded-circle bg-success text-white flex-shrink-0" style="width:28px;height:28px;">' + num + '</div>';
                    parts += '<span class="text-center mt-1 text-success fw-medium" style="font-size:10px;max-width:52px;line-height:1.2;">' + opsEsc(labels[i]) + '</span>';
                } else if (i === current) {
                    parts += '<div class="d-flex align-items-center justify-content-center fw-semibold flex-shrink-0"' +
                        ' style="width:28px;height:28px;border-radius:50%;background:#d7f5fc;color:#0d6efd;border:1.5px solid #0d6efd;">' + num + '</div>';
                    parts += '<span class="text-center mt-1 text-primary fw-medium" style="font-size:10px;max-width:52px;line-height:1.2;">' + opsEsc(labels[i]) + '</span>';
                } else {
                    parts += '<div class="d-flex align-items-center justify-content-center flex-shrink-0"' +
                        ' style="width:28px;height:28px;border-radius:50%;background:#fff;color:#aaa;border:1.5px solid #ccc;">' + num + '</div>';
                    parts += '<span class="text-center text-muted mt-1" style="font-size:10px;max-width:52px;line-height:1.2;">' + opsEsc(labels[i]) + '</span>';
                }
                parts += '</div>';
                if (i < 4) {
                    parts += sep;
                }
            }
            parts += '</div></div>';
            return parts;
        }

        function opsRenderDetalle(op, dictamen, historialLlamadas) {
            historialLlamadas = Array.isArray(historialLlamadas) ? historialLlamadas : [];
            _activeOpId = op.id;
            const textoEtapa = opsTextoEtapaUsuario(op);

            const html = `
        <div class="p-3">
            <div class="card border mb-3 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="flex-grow-1 min-w-0">
                            <div class="text-muted small">Cliente</div>
                            <div class="fs-5 fw-semibold">${opsEsc(op.nombre_cliente)}</div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                <span class="text-muted small">ID Crédito</span>
                                <span class="fw-medium">#${opsEsc(String(op.id_credito))}</span>
                                <span class="text-muted">|</span>
                                <span class="text-muted">${opsEsc(op.folio)}</span>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-1 small">
                                <span class="text-muted">NIV</span>
                                <span class="fw-medium">${opsEsc(op.niv || op.moto_no_serie || op.serie || 'Sin capturar')}</span>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-primary">${opsEsc(textoEtapa)}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            ${opsHtmlStepperEtapaModal(op)}
                        </div>
                    </div>
                </div>
            </div>
            ${opsRenderResumenInformacionOperacion(op)}
            ${opsRenderDatosExpediente(op, dictamen, historialLlamadas)}
        </div>`;

            document.getElementById('det-body').innerHTML = html;
        }

        /** Datos capturados por el gestor y metadatos de la gestión original en Legacy. */
        function opsRenderResumenInformacionOperacion(op) {
            const valor = v => String(v == null ? '' : v).trim();
            const primero = (...values) => values.map(valor).find(Boolean) || '';
            const siNo = v => {
                const raw = valor(v);
                const normalized = raw.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                if (['si', '1', 'true'].includes(normalized)) return 'Sí';
                if (['no', '0', 'false'].includes(normalized)) return 'No';
                return raw;
            };
            const resguardo = [
                primero(op.log_lugar_otro, op.log_lugar_resguardo),
                valor(op.log_ciudad),
                valor(op.log_estado)
            ].filter(Boolean).join(' / ');
            const campos = [
                ['Marca', primero(op.moto_marca, op.marca)],
                ['Modelo', primero(op.moto_modelo, op.modelo)],
                ['Año', primero(op.moto_anio, op.anio, op.ano)],
                ['Color', valor(op.moto_color)],
                ['NIV', primero(op.niv, op.moto_no_serie, op.serie)],
                ['No. motor', primero(op.moto_no_motor, op.num_motor)],
                ['Placas', primero(op.moto_placas, op.placas)],
                ['Kilometraje', valor(op.kilometraje)],
                ['Llave física', siNo(primero(op.llave_fisica, op.tiene_llave_fisica))],
                ['Tarjeta de circulación', siNo(primero(op.tarjeta_circulacion, op.tiene_tarjeta_de_circulacion_en_fisico))],
                ['Placa física', siNo(primero(op.placa_fisica, op.la_moto_tiene_placa_fisica))],
                ['Lugar de resguardo', resguardo],
                ['Responsable de resguardo', valor(op.responsable_entrega)],
                ['Teléfono de contacto', primero(op.log_telefono, op.telefono_contacto)],
                ['Dirección de resguardo', primero(op.log_direccion, op.direccion_recoleccion)]
            ];
            const hayFormulario = campos.some(([, value]) => value);
            const gestor = valor(op.ultimo_gestor_nombre) || 'Sin asignar';
            const gestion = valor(op.fecha_gestion_legacy) || valor(op.fecha_alta_fmt) || 'Sin fecha disponible';
            const fechaCaptura = valor(op.datos_moto_fecha);
            const filas = campos.map(([label, value]) => `
                <div class="col-sm-6 col-lg-4">
                    <div class="border rounded p-2 h-100" style="background:#fafbfc;">
                        <div class="text-muted text-uppercase fw-semibold" style="font-size:.61rem;letter-spacing:.04em;">${opsEsc(label)}</div>
                        <div class="small fw-medium text-break mt-1">${opsEsc(value || 'Sin capturar')}</div>
                    </div>
                </div>`).join('');

            return `
            <div class="card mb-3 shadow-sm">
                <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h6 class="mb-0 small fw-bold text-uppercase"><i class="fa-solid fa-circle-info me-2 text-primary"></i>Información de operación</h6>
                    ${fechaCaptura ? `<span class="text-muted" style="font-size:.72rem;">Formulario capturado: ${opsEsc(fechaCaptura)}</span>` : ''}
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">${opsExpKv('Gestor asignado', gestor)}</div>
                        <div class="col-md-6">${opsExpKv('Fecha y hora de gestión en Legacy', gestion)}</div>
                    </div>
                    <div class="border-top pt-3">
                        <div class="small fw-semibold text-uppercase mb-2" style="letter-spacing:.04em;"><i class="fa-solid fa-clipboard-list me-1 text-primary"></i>Formulario de la operación</div>
                        ${hayFormulario ? `<div class="row g-2">${filas}</div>` : '<div class="text-muted small">Aún no hay información de formulario capturada por el gestor.</div>'}
                    </div>
                </div>
            </div>`;
        }

        /**
         * Bloque Recibido/Evidencia: progreso de carga, validación y bitácora de veredictos.
         * No incluye historial de llamadas (solo Retenciones).
         */
        function opsRenderBloqueEvidenciaRecibido(op) {
            const r = op.resumen_evidencia_flujo || {};
            const rep = r.repuve || {};
            const repPdf = !!rep.pdf_cargado;

            const evsArr = Array.isArray(op.evidencias) ? op.evidencias : [];
            const bySlotModal = {};
            evsArr.forEach(e => {
                const sk = e.slot != null ? String(e.slot).trim() : '';
                if (sk) bySlotModal[sk] = e;
            });
            let cargadasModal = 0;
            OPS_MODAL_RECIBIDO_SLOTS.forEach(slot => {
                const row = bySlotModal[slot];
                let tieneArchivo = row && String(row.url || '').trim() !== '';
                if (slot === 'doc_repuve' && !tieneArchivo && repPdf) {
                    tieneArchivo = true;
                }
                if (tieneArchivo) cargadasModal++;
            });
            const totalModal = Math.max(1, OPS_MODAL_RECIBIDO_SLOT_TOTAL);
            const pctModal = Math.min(100, Math.round((cargadasModal / totalModal) * 100));
            const expedienteCompleto = cargadasModal >= totalModal;

            const slotsVal = parseInt(r.slots_validacion_media, 10) || 11;
            const validadas = parseInt(r.validadas_en_evidencia, 10) || 0;
            const aceptadas = parseInt(r.aceptadas, 10) || 0;
            const devueltas = parseInt(r.devueltas, 10) || 0;
            const pendVal = parseInt(r.pendientes_validacion, 10) || 0;
            const pctValidacion = slotsVal > 0 ? Math.min(100, Math.round((validadas / slotsVal) * 100)) : 0;

            const repPipe = rep.estatus_pipeline != null && rep.estatus_pipeline !== '' ? String(rep.estatus_pipeline) : '—';
            const repScore = rep.score_ia;
            const repScoreLine = (repPdf && repScore != null && repScore !== '')
                ? `<div class="text-muted mt-2" style="font-size:.75rem;">Score IA: <strong>${opsEsc(String(repScore))}</strong></div>`
                : '';

            const repuveInner = !repPdf
                ? `<p class="small text-muted mb-0 lh-base" style="max-width:42rem;">
                    En espera de carga una vez finalice la validación de las evidencias (este apartado corresponde al administrador de validaciones).
                </p>`
                : `
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="badge bg-label-success rounded-pill">PDF en expediente</span>
                                <span class="small text-muted">${opsEsc(repPipe)}</span>
                            </div>
                            ${repScoreLine}`;

            const timeline = Array.isArray(op.validaciones_evidencia_timeline) ? op.validaciones_evidencia_timeline : [];
            const lineasTimeline = timeline.length
                ? timeline.map(row => {
                    const u = row && row.nombre_usuario != null ? String(row.nombre_usuario).trim() : '';
                    const fh = row && row.fecha_fmt != null ? String(row.fecha_fmt).trim() : '';
                    const acc = row && row.accion != null ? String(row.accion).trim() : '';
                    const texto = acc.replace(/^VALIDACIÓN EVIDENCIA\s+/i, '').trim() || acc;
                    return `
                    <div class="d-flex gap-2 py-2 border-bottom border-light align-items-start">
                        <i class="fa-solid fa-check-double text-primary mt-1" style="font-size:.75rem;"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="small fw-semibold" style="white-space:pre-wrap;">${opsEsc(texto)}</div>
                            <div class="text-muted" style="font-size:.68rem;">
                                ${fh ? `<span class="me-2"><i class="fa-regular fa-clock me-1"></i>${opsEsc(fh)}</span>` : ''}
                                ${u ? `<span><i class="fa-regular fa-user me-1"></i>${opsEsc(u)}</span>` : ''}
                            </div>
                        </div>
                    </div>`;
                }).join('')
                : `<p class="text-muted small fst-italic mb-0">Aún no hay validaciones registradas en bitácora.</p>`;

            return `
        <div class="mb-3">
            ${opsHtmlBloqueEstatusDictamenModal()}
        </div>
        <div class="card mb-0 border-primary border-opacity-25">
            <div class="card-header py-2">
                <h6 class="mb-0 small fw-bold text-uppercase">
                    <i class="fa-solid fa-images me-2 text-primary"></i>Evidencia (carga del gestor)
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3 mb-md-4">
                    Progreso de carga
                </p>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="text-muted fw-semibold mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;">Archivos en expediente</div>
                        <div class="progress mb-1" style="height:8px;">
                            <div class="progress-bar ${expedienteCompleto ? 'bg-success' : 'bg-primary'}" role="progressbar" style="width:${pctModal}%;" aria-valuenow="${pctModal}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="small d-flex flex-wrap align-items-center gap-2">
                            <span><strong>${cargadasModal}</strong> / ${totalModal} slots con archivo</span>
                            ${expedienteCompleto ? '<span class="badge bg-label-success rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Expediente completo</span>' : ''}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted fw-semibold mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;">Validación en Evidencia (fotos/video) (Admin Validación)</div>
                        <div class="progress mb-1" style="height:8px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width:${pctValidacion}%;" aria-valuenow="${pctValidacion}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="small">
                            <span class="badge bg-label-success rounded-pill">${aceptadas} aceptadas</span>
                            <span class="badge bg-label-danger rounded-pill">${devueltas} devueltas</span>
                            <span class="badge bg-label-secondary rounded-pill">${pendVal} pendientes</span>
                        </div>
                        <div class="text-muted mt-1" style="font-size:.75rem;">
                            ${validadas} de ${slotsVal} ítems con veredicto (sobre los que aplica aceptar/rechazar).
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="rounded border p-3" style="background:var(--bs-tertiary-bg, #f8f9fa); border-color:rgba(20, 83, 45, 0.25) !important;">
                            <div class="text-muted fw-semibold mb-2" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;">
                                <i class="fa-solid fa-file-pdf me-1 text-success"></i>Repuve (PDF)
                            </div>
                            ${repuveInner}
                        </div>
                    </div>
                    <div class="col-12 mt-2 pt-2 border-top">
                        <div class="text-muted fw-semibold mb-2" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.06em;">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Validaciones (fecha desde bitácora)
                        </div>
                        <div class="rounded border bg-light bg-opacity-50 px-2 py-1" style="max-height:220px;overflow-y:auto;">
                            ${lineasTimeline}
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        }

        /**
         * Bloque Procesando IA / Revisión Recuperaciones: expediente completo incl. Factura (doc_factura).
         */
        function opsRenderBloqueRecuperacionFacturacion(op) {
            const r = op.resumen_evidencia_flujo || {};
            const rep = r.repuve || {};
            const repPdf = !!rep.pdf_cargado;
            const knockout = op.validacion_knockout || {};
            const knockoutEtiqueta = String(knockout.etiqueta || '').trim();
            const knockoutEstado = String(knockout.estado || '').trim();
            const knockoutClass = knockoutEstado === 'APROBADO'
                ? 'bg-label-success'
                : (knockoutEstado === 'BLOQUEADO' ? 'bg-label-danger' : 'bg-label-warning');
            const knockoutHtml = knockoutEtiqueta
                ? `<div class="mb-3"><span class="badge ${knockoutClass}"><i class="fa-solid fa-robot me-1"></i>${opsEsc(knockoutEtiqueta)}</span>${knockout.motivo ? `<span class="small text-muted ms-2">${opsEsc(String(knockout.motivo))}</span>` : ''}</div>`
                : '';

            const evsArr = Array.isArray(op.evidencias) ? op.evidencias : [];
            const bySlot = {};
            evsArr.forEach(e => {
                const sk = e.slot != null ? String(e.slot).trim() : '';
                if (sk) bySlot[sk] = e;
            });

            let cargadasDiez = 0;
            OPS_MODAL_RECIBIDO_SLOTS.forEach(slot => {
                const row = bySlot[slot];
                let tieneArchivo = row && String(row.url || '').trim() !== '';
                if (slot === 'doc_repuve' && !tieneArchivo && repPdf) {
                    tieneArchivo = true;
                }
                if (tieneArchivo) cargadasDiez++;
            });

            const totalDiez = Math.max(1, OPS_MODAL_RECIBIDO_SLOT_TOTAL);
            const diezCompleto = cargadasDiez >= totalDiez;

            const rowFactura = bySlot.doc_factura;
            const facturaCargada = rowFactura && String(rowFactura.url || '').trim() !== '';
            const cargadosOnce = cargadasDiez + (facturaCargada ? 1 : 0);
            const totExp = Math.max(1, OPS_EXPEDIENTE_TOTAL_CON_FACTURA);
            const pctOnce = Math.min(100, Math.round((cargadosOnce / totExp) * 100));
            const expedienteOnceCompleto = cargadosOnce >= totExp;

            const facturaInner = !facturaCargada
                ? `<p class="small text-muted mb-0 lh-base" style="max-width:42rem;">
                    En espera de carga de la documentación de factura
                </p>`
                : `
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-0">
                                <span class="badge bg-label-success rounded-pill">Documento en expediente</span>
                            </div>`;

            return `
        <div class="mb-3">
            ${opsHtmlBloqueEstatusDictamenModal()}
        </div>
        <div class="card mb-0 border-primary border-opacity-25">
            <div class="card-header py-2">
                <h6 class="mb-0 small fw-bold text-uppercase">
                    <i class="fa-solid fa-folder-open me-2 text-primary"></i>Expediente en Recuperación
                </h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3 mb-md-4">
                    En esta etapa se revisa el expediente completo (evidencia física, Repuve y factura).
                </p>
                ${knockoutHtml}
                <div class="row g-3">
                    <div class="col-12">
                        <div class="text-muted fw-semibold mb-1" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;">Archivos en expediente</div>
                        <div class="progress mb-1" style="height:8px;">
                            <div class="progress-bar ${expedienteOnceCompleto ? 'bg-success' : diezCompleto ? 'bg-primary' : 'bg-secondary'}" role="progressbar" style="width:${pctOnce}%;" aria-valuenow="${pctOnce}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div class="small d-flex flex-wrap align-items-center gap-2">
                            <span><strong>${cargadosOnce}</strong> / ${totExp} slots con archivo</span>
                            ${expedienteOnceCompleto ? '<span class="badge bg-label-success rounded-pill"><i class="fa-solid fa-circle-check me-1"></i>Completo</span>' : ''}
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="rounded border p-3" style="background:var(--bs-tertiary-bg, #f8f9fa); border-color:rgba(13, 110, 253, 0.25) !important;">
                            <div class="text-muted fw-semibold mb-2" style="font-size:.65rem;text-transform:uppercase;letter-spacing:.04em;">
                                <i class="fa-solid fa-file-invoice me-1 text-primary"></i>Factura
                            </div>
                            ${facturaInner}
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
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

        /** Misma badge «En tránsito» que en Retenciones (camión + bg-label-info) para todo el modal Detalle. */
        function opsHtmlEstatusDictamenBadgeModal() {
            return `<span class="badge bg-label-info"><i class="fa-solid fa-truck-fast me-1"></i>En tránsito</span>`;
        }

        function opsHtmlBloqueEstatusDictamenModal() {
            return opsExpKv('Estatus dictamen', opsHtmlEstatusDictamenBadgeModal(), true);
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
                     style="background:transparent; border-top:1px solid var(--bs-border-color); border-radius:0 0 .75rem .75rem !important;">
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

            if (opsEsEtapaRecibidoEvidenciaDetalle(op)) {
                return opsRenderBloqueEvidenciaRecibido(op);
            }

            if (opsEsEtapaRecuperacionDetalle(op)) {
                return opsRenderBloqueRecuperacionFacturacion(op);
            }

            if (!opsEsEtapaRetencionesDetalle(op)) {
                const textoExpedienteGenerico = (op.estatus === 'Cierre Documentado')
                    ? 'En espera de que Cartera publique y confirme el cierre de crédito en S2.'
                    : 'En esta etapa el detalle de expediente se consulta en el módulo correspondiente.';
                return `
        <div class="mb-3">
            ${opsHtmlBloqueEstatusDictamenModal()}
        </div>
        <div class="card mb-0">
            <div class="card-body py-3">
                <p class="text-muted small mb-0">
                    <i class="fa-regular fa-folder-open me-1"></i>
                    ${textoExpedienteGenerico}
                </p>
            </div>
        </div>`;
            }

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
