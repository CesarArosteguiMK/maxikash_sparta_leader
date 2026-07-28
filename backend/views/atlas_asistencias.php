<?php
$atlasNow = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
$atlasStart = $atlasNow->modify('first day of this month')->format('Y-m-d');
$atlasEnd = $atlasNow->modify('last day of this month')->format('Y-m-d');
$atlasDatasetEndDate = $atlasNow->modify('last day of this month');
$atlasDatasetStart = $atlasDatasetEndDate->modify('-365 days')->format('Y-m-d');
$atlasDatasetEnd = $atlasDatasetEndDate->format('Y-m-d');
$atlasApiReady = !empty($atlas_admin_configurada);
?>

<div class="container-fluid py-3 atlas-attendance-page">
    <style>
        .atlas-attendance-page { color: #22303e; }
        .atlas-attendance-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .atlas-attendance-title { display:flex; align-items:center; gap:.65rem; margin:0; color:#173756; font-size:1.35rem; font-weight:900; }
        .atlas-attendance-title i { color:#0f766e; }
        .atlas-attendance-subtitle { margin:.2rem 0 0; color:#64748b; font-size:.86rem; font-weight:700; }
        .atlas-attendance-actions { display:flex; align-items:center; gap:.55rem; flex-wrap:wrap; }
        .atlas-attendance-filter-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#f8fafc; padding:.9rem; margin-bottom:1rem; }
        .atlas-attendance-filters { display:grid; grid-template-columns:repeat(8, minmax(9rem, 1fr)); gap:.7rem; align-items:end; }
        .atlas-attendance-filter-actions { display:flex; align-items:center; }
        .atlas-attendance-filter-actions .btn { min-height:2.35rem; width:100%; }
        .atlas-attendance-metrics { display:grid; grid-template-columns:repeat(6, minmax(8rem, 1fr)); gap:.7rem; margin-bottom:1rem; }
        .atlas-attendance-metric { border:1px solid #dbe4ef; border-left:4px solid #64748b; border-radius:.45rem; background:#fff; padding:.72rem .8rem; min-width:0; }
        .atlas-attendance-metric.is-green { border-left-color:#16a34a; }
        .atlas-attendance-metric.is-red { border-left-color:#dc2626; }
        .atlas-attendance-metric.is-amber { border-left-color:#d97706; }
        .atlas-attendance-metric.is-blue { border-left-color:#2563eb; }
        .atlas-attendance-metric-label { color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; }
        .atlas-attendance-metric-value { color:#172033; font-size:1.25rem; font-weight:900; line-height:1.1; margin-top:.2rem; }
        .atlas-attendance-table-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#fff; overflow:hidden; }
        .atlas-attendance-table-head { display:flex; align-items:center; justify-content:space-between; gap:.8rem; padding:.75rem .9rem; border-bottom:1px solid #e5e7eb; }
        .atlas-attendance-table-title { margin:0; color:#173756; font-size:.9rem; font-weight:900; }
        .atlas-attendance-table-meta { color:#64748b; font-size:.75rem; font-weight:800; }
        .atlas-attendance-scroll { overflow-x:auto; }
        .atlas-attendance-table { min-width:1280px; margin:0; }
        .atlas-attendance-table th { background:#f8fafc; color:#566a7f; font-size:.68rem; font-weight:900; text-transform:uppercase; white-space:nowrap; }
        .atlas-attendance-table td { color:#566a7f; font-size:.76rem; font-weight:700; vertical-align:middle; }
        .atlas-attendance-main { color:#22303e; font-weight:900; line-height:1.18; }
        .atlas-attendance-sub { color:#94a3b8; font-size:.68rem; font-weight:800; line-height:1.18; margin-top:.12rem; }
        .atlas-attendance-badge { display:inline-flex; align-items:center; border-radius:999px; padding:.2rem .55rem; font-size:.68rem; font-weight:900; white-space:nowrap; }
        .atlas-attendance-status-cumplida { background:#dcfce7; color:#15803d; }
        .atlas-attendance-status-no-realizada { background:#fee2e2; color:#b91c1c; }
        .atlas-attendance-status-fuera { background:#ffedd5; color:#c2410c; }
        .atlas-attendance-status-en-visita { background:#dbeafe; color:#1d4ed8; }
        .atlas-attendance-status-gestion-sin-visita { background:#fef3c7; color:#92400e; }
        .atlas-attendance-status-programada { background:#f1f5f9; color:#475569; }
        .atlas-attendance-empty { padding:2.5rem 1rem !important; text-align:center; color:#64748b !important; }
        .atlas-attendance-note { color:#64748b; font-size:.73rem; font-weight:700; margin-top:.55rem; }
        .atlas-attendance-evidence-button { position:relative; display:inline-grid; place-items:center; width:2.2rem; height:2.2rem; padding:0; border:1px solid #bfdbfe; border-radius:.4rem; background:#eff6ff; color:#1d4ed8; }
        .atlas-attendance-evidence-button:hover { background:#dbeafe; color:#1e40af; }
        .atlas-attendance-evidence-button:disabled { border-color:#e2e8f0; background:#f8fafc; color:#cbd5e1; opacity:1; }
        .atlas-attendance-evidence-count { position:absolute; top:-.35rem; right:-.35rem; min-width:1.15rem; height:1.15rem; display:grid; place-items:center; border:2px solid #fff; border-radius:999px; background:#1d4ed8; color:#fff; font-size:.58rem; font-weight:900; }
        .atlas-evidence-modal-meta { color:#64748b; font-size:.78rem; font-weight:700; }
        .atlas-evidence-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:1rem; }
        .atlas-evidence-item { min-width:0; border:1px solid #dbe4ef; border-radius:.5rem; overflow:hidden; background:#fff; }
        .atlas-evidence-media { display:grid; place-items:center; width:100%; min-height:14rem; aspect-ratio:16/10; overflow:hidden; background:#0f172a; }
        .atlas-evidence-media img,
        .atlas-evidence-media video { width:100%; height:100%; object-fit:contain; }
        .atlas-evidence-media audio { width:calc(100% - 2rem); }
        .atlas-evidence-unavailable { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.6rem; padding:1.5rem; color:#64748b; text-align:center; }
        .atlas-evidence-unavailable i { color:#d97706; font-size:1.5rem; }
        .atlas-evidence-info { padding:.75rem .85rem; border-top:1px solid #e2e8f0; }
        .atlas-evidence-name { color:#22303e; font-size:.82rem; font-weight:900; overflow-wrap:anywhere; }
        .atlas-evidence-detail { margin-top:.15rem; color:#64748b; font-size:.7rem; font-weight:700; }
        .atlas-evidence-empty { padding:3rem 1rem; color:#64748b; text-align:center; font-weight:800; }
        .atlas-attendance-page .select2-container { width:100% !important; }
        .atlas-attendance-page .select2-container .select2-selection--single { min-height:2.35rem; display:flex; align-items:center; border-color:#d9dee3; }
        .atlas-attendance-page .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left:.75rem; color:#566a7f; }
        .atlas-attendance-page .select2-container--default .select2-selection--single .select2-selection__arrow { height:2.25rem; }
        .atlas-attendance-page .dataTables_wrapper > .row:first-child,
        .atlas-attendance-page .dataTables_wrapper > .row:last-child,
        .atlas-attendance-page .dataTables_wrapper > .dt-layout-row { margin:0; padding:.85rem .9rem; align-items:center; }
        .atlas-attendance-page .dataTables_wrapper > .row:first-child,
        .atlas-attendance-page .dataTables_wrapper > .dt-layout-row:first-child { border-bottom:1px solid #e5e7eb; }
        .atlas-attendance-page .dataTables_wrapper > .row:last-child,
        .atlas-attendance-page .dataTables_wrapper > .dt-layout-row:last-child { border-top:1px solid #e5e7eb; }
        .atlas-attendance-page .dataTables_wrapper .dataTables_filter,
        .atlas-attendance-page .dataTables_wrapper .dataTables_length,
        .atlas-attendance-page .dataTables_wrapper .dt-search,
        .atlas-attendance-page .dataTables_wrapper .dt-length { color:#566a7f; font-size:.82rem; }
        .atlas-attendance-page .dataTables_wrapper .dataTables_filter input,
        .atlas-attendance-page .dataTables_wrapper .dt-search input { min-width:16rem; border-radius:.375rem; border-color:#d9dee3; }
        @media (max-width: 1399.98px) {
            .atlas-attendance-filters { grid-template-columns:repeat(3, minmax(10rem, 1fr)); }
            .atlas-attendance-metrics { grid-template-columns:repeat(3, minmax(9rem, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .atlas-attendance-filters,
            .atlas-attendance-metrics { grid-template-columns:1fr; }
            .atlas-attendance-actions,
            .atlas-attendance-actions .btn,
            .atlas-attendance-filter-actions,
            .atlas-attendance-filter-actions .btn { width:100%; }
            .atlas-attendance-filter-actions .btn { flex:1; }
            .atlas-evidence-grid { grid-template-columns:1fr; }
        }
    </style>

    <div class="atlas-attendance-head">
        <div>
            <h1 class="atlas-attendance-title">
                <i class="fa-solid fa-clipboard-user"></i>
                <span>Asistencias Atlas</span>
            </h1>
            <p class="atlas-attendance-subtitle">Visitas, ubicación, permanencia y actividad registrada desde la app.</p>
        </div>
        <div class="atlas-attendance-actions">
            <button type="button" class="btn btn-label-secondary" id="atlasAttendanceRefresh" title="Actualizar reporte">
                <i class="fa-solid fa-rotate me-2"></i>Actualizar
            </button>
            <button type="button" class="btn btn-success" id="atlasAttendanceDownload" title="Descargar archivo Excel">
                <i class="fa-solid fa-file-excel me-2"></i>Descargar Excel
            </button>
        </div>
    </div>

    <?php if (!$atlasApiReady): ?>
        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>Configura <strong>ATLAS_ADMIN_API_KEYS</strong> para consultar el reporte.</span>
        </div>
    <?php endif; ?>

    <section class="atlas-attendance-filter-panel" aria-label="Filtros de asistencias">
        <div class="atlas-attendance-filters">
            <div>
                <label class="form-label mb-1" for="atlasAttendanceStart">Fecha inicio</label>
                <input class="form-control form-control-sm" type="date" id="atlasAttendanceStart"
                       min="<?= htmlspecialchars($atlasDatasetStart, ENT_QUOTES, 'UTF-8') ?>"
                       max="<?= htmlspecialchars($atlasDatasetEnd, ENT_QUOTES, 'UTF-8') ?>"
                       value="<?= htmlspecialchars($atlasStart, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceEnd">Fecha fin</label>
                <input class="form-control form-control-sm" type="date" id="atlasAttendanceEnd"
                       min="<?= htmlspecialchars($atlasDatasetStart, ENT_QUOTES, 'UTF-8') ?>"
                       max="<?= htmlspecialchars($atlasDatasetEnd, ENT_QUOTES, 'UTF-8') ?>"
                       value="<?= htmlspecialchars($atlasEnd, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceCollaborator">Colaborador</label>
                <select class="form-select form-select-sm atlas-attendance-select" id="atlasAttendanceCollaborator">
                    <option value="">Todos</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceDistributor">Distribuidor</label>
                <select class="form-select form-select-sm atlas-attendance-select" id="atlasAttendanceDistributor">
                    <option value="">Todos</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceStatus">Estatus</label>
                <select class="form-select form-select-sm atlas-attendance-select" id="atlasAttendanceStatus">
                    <option value="">Todos</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceDivisional">Divisional</label>
                <select class="form-select form-select-sm atlas-attendance-select" id="atlasAttendanceDivisional">
                    <option value="">Todas</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasAttendanceEvidence">Evidencias</label>
                <select class="form-select form-select-sm atlas-attendance-select" id="atlasAttendanceEvidence">
                    <option value="">Todas</option>
                    <option value="con">Con evidencias</option>
                    <option value="sin">Sin evidencias</option>
                    <option value="incompletas">Incompletas</option>
                </select>
            </div>
            <div class="atlas-attendance-filter-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="atlasAttendanceClear" title="Eliminar filtros">
                    <i class="fa-solid fa-filter-circle-xmark me-2"></i>Eliminar filtros
                </button>
            </div>
        </div>
        <div class="atlas-attendance-note" id="atlasAttendancePerimeter">Perímetro operativo pendiente de consultar.</div>
    </section>

    <section class="atlas-attendance-metrics" aria-label="Resumen de asistencias">
        <article class="atlas-attendance-metric is-blue">
            <div class="atlas-attendance-metric-label">Visitas</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceTotal">0</div>
        </article>
        <article class="atlas-attendance-metric is-green">
            <div class="atlas-attendance-metric-label">Cumplidas</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceCompleted">0</div>
        </article>
        <article class="atlas-attendance-metric is-red">
            <div class="atlas-attendance-metric-label">No realizadas</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceMissed">0</div>
        </article>
        <article class="atlas-attendance-metric is-amber">
            <div class="atlas-attendance-metric-label">Fuera de ubicación</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceOutside">0</div>
        </article>
        <article class="atlas-attendance-metric is-blue">
            <div class="atlas-attendance-metric-label">Gestiones</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendanceManaged">0</div>
        </article>
        <article class="atlas-attendance-metric">
            <div class="atlas-attendance-metric-label">Pendientes</div>
            <div class="atlas-attendance-metric-value" id="atlasAttendancePending">0</div>
        </article>
    </section>

    <section class="atlas-attendance-table-panel">
        <div class="atlas-attendance-table-head">
            <h2 class="atlas-attendance-table-title">Detalle de visitas y gestiones</h2>
            <span class="atlas-attendance-table-meta" id="atlasAttendanceGenerated">Sin consultar</span>
        </div>
        <div class="atlas-attendance-scroll">
            <table id="atlasAttendanceTable" class="table table-hover atlas-attendance-table w-100">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Colaborador</th>
                        <th>Agencia / Distribuidor</th>
                        <th>Estatus</th>
                        <th>Perímetro</th>
                        <th>Llegada</th>
                        <th>Salida</th>
                        <th>Permanencia</th>
                        <th class="text-end">Gestiones</th>
                        <th class="text-end">Pendientes</th>
                        <th class="text-center">Evidencias</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody id="atlasAttendanceBody"></tbody>
            </table>
        </div>
    </section>
</div>

<div class="modal fade" id="atlasAttendanceEvidenceModal" tabindex="-1" aria-labelledby="atlasAttendanceEvidenceTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5" id="atlasAttendanceEvidenceTitle">
                        <i class="fa-solid fa-images me-2 text-primary"></i>Evidencias
                    </h2>
                    <div class="atlas-evidence-modal-meta" id="atlasAttendanceEvidenceMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="atlas-evidence-grid" id="atlasAttendanceEvidenceGrid"></div>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    const apiReady = <?= $atlasApiReady ? 'true' : 'false' ?>;
    const initialStart = <?= json_encode($atlasStart, JSON_UNESCAPED_SLASHES) ?>;
    const initialEnd = <?= json_encode($atlasEnd, JSON_UNESCAPED_SLASHES) ?>;
    const datasetStart = <?= json_encode($atlasDatasetStart, JSON_UNESCAPED_SLASHES) ?>;
    const datasetEnd = <?= json_encode($atlasDatasetEnd, JSON_UNESCAPED_SLASHES) ?>;
    const startInput = document.getElementById('atlasAttendanceStart');
    const endInput = document.getElementById('atlasAttendanceEnd');
    const collaboratorSelect = document.getElementById('atlasAttendanceCollaborator');
    const distributorSelect = document.getElementById('atlasAttendanceDistributor');
    const statusSelect = document.getElementById('atlasAttendanceStatus');
    const divisionalSelect = document.getElementById('atlasAttendanceDivisional');
    const evidenceSelect = document.getElementById('atlasAttendanceEvidence');
    const body = document.getElementById('atlasAttendanceBody');
    const downloadButton = document.getElementById('atlasAttendanceDownload');
    const evidenceModalElement = document.getElementById('atlasAttendanceEvidenceModal');
    const evidenceGrid = document.getElementById('atlasAttendanceEvidenceGrid');
    const evidenceMeta = document.getElementById('atlasAttendanceEvidenceMeta');
    let evidenceModal = null;
    let visibleRows = [];
    let attendanceTable = null;
    let generatedAt = '';
    let loading = false;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const number = (value) => Number(value || 0).toLocaleString('es-MX');
    const formatBytes = (value) => {
        const bytes = Number(value || 0);
        if (!bytes) return 'Tamaño sin dato';
        if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };

    const evidenceMedia = (evidence) => {
        if (!evidence.disponible || !evidence.id) {
            return `<div class="atlas-evidence-media atlas-evidence-unavailable">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>${escapeHtml(evidence.mensaje || 'Esta evidencia no esta disponible para consulta.')}</div>
            </div>`;
        }

        const source = `/Atlas/verEvidenciaAsistencia?id=${encodeURIComponent(evidence.id)}`;
        const mimeType = String(evidence.mime_type || '').toLowerCase();
        const kind = String(evidence.tipo || '').toLowerCase();
        if (mimeType.startsWith('image/') || kind === 'imagen' || kind === 'foto') {
            return `<div class="atlas-evidence-media">
                <img src="${source}" alt="${escapeHtml(evidence.nombre || 'Evidencia fotografica')}" loading="lazy">
            </div>`;
        }
        if (mimeType.startsWith('video/') || kind === 'video') {
            return `<div class="atlas-evidence-media">
                <video controls preload="metadata"><source src="${source}" type="${escapeHtml(mimeType || 'video/mp4')}"></video>
            </div>`;
        }
        if (mimeType.startsWith('audio/') || kind === 'audio') {
            return `<div class="atlas-evidence-media">
                <audio controls preload="metadata"><source src="${source}" type="${escapeHtml(mimeType || 'audio/mpeg')}"></audio>
            </div>`;
        }
        return `<div class="atlas-evidence-media atlas-evidence-unavailable">
            <i class="fa-solid fa-file"></i>
            <a class="btn btn-sm btn-primary" href="${source}" target="_blank" rel="noopener">
                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Abrir archivo
            </a>
        </div>`;
    };

    const showEvidenceModal = (row) => {
        const evidences = Array.isArray(row.evidencias) ? row.evidencias : [];
        evidenceMeta.textContent = [row.agencia, row.colaborador, row.fecha].filter(Boolean).join(' · ');
        evidenceGrid.innerHTML = evidences.length
            ? evidences.map((evidence) => `<article class="atlas-evidence-item">
                ${evidenceMedia(evidence)}
                <div class="atlas-evidence-info">
                    <div class="atlas-evidence-name">${escapeHtml(evidence.nombre || 'Evidencia')}</div>
                    <div class="atlas-evidence-detail">${escapeHtml([
                        evidence.tipo,
                        formatBytes(evidence.size_bytes),
                        evidence.fecha_hora ? String(evidence.fecha_hora).replace('T', ' ') : null
                    ].filter(Boolean).join(' · '))}</div>
                </div>
            </article>`).join('')
            : '<div class="atlas-evidence-empty">Este registro no tiene evidencias cargadas.</div>';

        evidenceGrid.querySelectorAll('img, video, audio').forEach((media) => {
            media.addEventListener('error', () => {
                const container = media.closest('.atlas-evidence-media');
                if (container) {
                    container.innerHTML = '<div class="atlas-evidence-unavailable"><i class="fa-solid fa-circle-exclamation"></i><div>No se pudo cargar este archivo.</div></div>';
                }
            }, { once: true });
        });

        if (evidenceModal) {
            evidenceModal.show();
        } else if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            window.jQuery(evidenceModalElement).modal('show');
        }
    };

    const datasetQuery = () => {
        const params = new URLSearchParams();
        params.set('fecha_inicio', datasetStart);
        params.set('fecha_fin', datasetEnd);
        return params;
    };

    const exportQuery = () => {
        const params = new URLSearchParams();
        if (startInput.value) params.set('fecha_inicio', startInput.value);
        if (endInput.value) params.set('fecha_fin', endInput.value);
        if (collaboratorSelect.value) params.set('gestor_persona_id', collaboratorSelect.value);
        if (distributorSelect.value) params.set('distribuidor_id', distributorSelect.value);
        if (statusSelect.value) params.set('estatus', statusSelect.value);
        if (divisionalSelect.value) params.set('divisional', divisionalSelect.value);
        if (evidenceSelect.value) params.set('evidencias', evidenceSelect.value);
        return params;
    };

    const refreshSearchableSelect = (select) => {
        if (!window.jQuery || !window.jQuery.fn.select2) return;
        const $select = window.jQuery(select);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.trigger('change.select2');
        }
    };

    const updateSelect = (select, items, valueKey, labelKey, emptyLabel = 'Todos') => {
        const selected = select.value;
        select.innerHTML = `<option value="">${escapeHtml(emptyLabel)}</option>` + items.map((item) => {
            const value = typeof item === 'object' ? item[valueKey] : item;
            const label = typeof item === 'object' ? item[labelKey] : item;
            return `<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`;
        }).join('');
        if ([...select.options].some((option) => option.value === selected)) {
            select.value = selected;
        }
        refreshSearchableSelect(select);
    };

    const initializeSearchableSelects = () => {
        if (!window.jQuery || !window.jQuery.fn.select2) return;
        const placeholders = new Map([
            [collaboratorSelect, 'Todos los colaboradores'],
            [distributorSelect, 'Todos los distribuidores'],
            [statusSelect, 'Todos los estatus'],
            [divisionalSelect, 'Todas las divisionales'],
            [evidenceSelect, 'Todas las evidencias'],
        ]);
        placeholders.forEach((placeholder, select) => {
            const $select = window.jQuery(select);
            if ($select.hasClass('select2-hidden-accessible')) return;
            $select.select2({
                width: '100%',
                allowClear: true,
                placeholder,
                minimumResultsForSearch: 0,
                language: {
                    noResults: () => 'Sin resultados',
                    searching: () => 'Buscando...',
                },
            });
        });
    };

    const statusClass = (status) => {
        const normalized = String(status || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        if (normalized === 'cumplida') return 'atlas-attendance-status-cumplida';
        if (normalized === 'no realizada') return 'atlas-attendance-status-no-realizada';
        if (normalized === 'fuera de ubicacion') return 'atlas-attendance-status-fuera';
        if (normalized === 'en visita') return 'atlas-attendance-status-en-visita';
        if (normalized === 'gestion sin visita') return 'atlas-attendance-status-gestion-sin-visita';
        return 'atlas-attendance-status-programada';
    };

    const dateRangeIsValid = () => (
        Boolean(startInput.value && endInput.value)
        && endInput.value >= startInput.value
        && startInput.value >= datasetStart
        && endInput.value <= datasetEnd
    );

    const evidenceStatus = (row) => {
        const evidences = Array.isArray(row?.evidencias) ? row.evidencias : [];
        const declaredCount = row?.total_evidencias === null || row?.total_evidencias === undefined
            ? evidences.length
            : Math.max(0, Number(row.total_evidencias) || 0);
        const hasEvidence = declaredCount > 0
            || evidences.length > 0
            || String(row?.evidencia || '').trim() !== '';
        if (!hasEvidence) return 'sin';

        const hasMissingDetails = declaredCount > evidences.length;
        const hasUnavailableEvidence = evidences.some((evidence) => (
            !evidence
            || ![true, 1, '1', 'true'].includes(evidence.disponible)
            || !evidence.id
        ));
        return hasMissingDetails || hasUnavailableEvidence ? 'incompletas' : 'con';
    };

    const rowMatchesFilters = (row) => {
        if (!row || !dateRangeIsValid()) return false;
        const rowDate = String(row.fecha || '');
        if (startInput.value && rowDate < startInput.value) return false;
        if (endInput.value && rowDate > endInput.value) return false;
        if (
            collaboratorSelect.value
            && String(row.colaborador_persona_id ?? '') !== String(collaboratorSelect.value)
        ) return false;
        if (
            distributorSelect.value
            && String(row.distribuidor_id ?? '') !== String(distributorSelect.value)
        ) return false;
        if (statusSelect.value && String(row.estatus_visita || '') !== String(statusSelect.value)) return false;
        if (divisionalSelect.value && String(row.divisional || '') !== String(divisionalSelect.value)) return false;
        if (evidenceSelect.value && evidenceStatus(row) !== evidenceSelect.value) return false;
        return true;
    };

    const attendanceDataTableFilter = (settings, _searchData, dataIndex) => {
        if (!settings.nTable || settings.nTable.id !== 'atlasAttendanceTable') return true;
        const tableRow = settings.aoData?.[dataIndex]?.nTr;
        const rowIndex = Number(tableRow?.dataset?.atlasRow);
        return rowMatchesFilters(visibleRows[rowIndex]);
    };

    const registerAttendanceFilter = () => {
        if (!window.jQuery?.fn?.dataTable) return;
        const filters = window.jQuery.fn.dataTable.ext.search;
        if (window.__atlasAttendanceDataTableFilter) {
            const previousIndex = filters.indexOf(window.__atlasAttendanceDataTableFilter);
            if (previousIndex >= 0) filters.splice(previousIndex, 1);
        }
        window.__atlasAttendanceDataTableFilter = attendanceDataTableFilter;
        filters.push(attendanceDataTableFilter);
    };

    const renderRows = (rows) => {
        visibleRows = rows;
        if (!rows.length) {
            body.innerHTML = '';
            return;
        }
        body.innerHTML = rows.map((row, rowIndex) => {
            const distance = row.distancia_metros === null || row.distancia_metros === undefined
                ? 'Sin distancia'
                : `${number(row.distancia_metros)} m`;
            const observations = row.observaciones_incumplimiento || row.observaciones || '';
            const evidences = Array.isArray(row.evidencias) ? row.evidencias : [];
            const evidenceCount = Number(row.total_evidencias ?? evidences.length);
            const evidenceButton = evidenceCount > 0
                ? `<button type="button" class="atlas-attendance-evidence-button" data-atlas-evidence-row="${rowIndex}" title="Ver ${number(evidenceCount)} evidencia(s)" aria-label="Ver evidencias">
                    <i class="fa-solid fa-eye"></i>
                    <span class="atlas-attendance-evidence-count">${number(evidenceCount)}</span>
                </button>`
                : `<button type="button" class="atlas-attendance-evidence-button" disabled title="Sin evidencias" aria-label="Sin evidencias">
                    <i class="fa-solid fa-eye-slash"></i>
                </button>`;
            const managementDetails = (row.gestiones_detalle || []).map((item) => {
                const client = item.cliente || 'Cliente sin nombre';
                const offer = item.oferta ? `Oferta #${item.oferta}` : '';
                return [client, offer].filter(Boolean).join(' · ');
            }).join(' | ');
            return `<tr data-atlas-row="${rowIndex}">
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.fecha || '')}</div>
                    <div class="atlas-attendance-sub">${escapeHtml(row.dia_visita || '')}${row.hora_gestion ? ` · Gestión ${escapeHtml(row.hora_gestion)}` : ''}</div>
                </td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.colaborador || 'Sin asignar')}</div>
                    <div class="atlas-attendance-sub">${escapeHtml([row.puesto || row.rol, row.divisional].filter(Boolean).join(' · '))}</div>
                </td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.agencia || 'Sin agencia')}</div>
                    <div class="atlas-attendance-sub">${escapeHtml(row.distribuidor || 'Sin distribuidor')}</div>
                </td>
                <td><span class="atlas-attendance-badge ${statusClass(row.estatus_visita)}">${escapeHtml(row.estatus_visita || '')}</span></td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.dentro_perimetro || 'Sin dato')}</div>
                    <div class="atlas-attendance-sub">${escapeHtml(distance)}</div>
                </td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.hora_llegada || '--:--')}</div>
                    <div class="atlas-attendance-sub">Conf. ${escapeHtml(row.hora_confirmacion_llegada || '--:--')}</div>
                </td>
                <td>
                    <div class="atlas-attendance-main">${escapeHtml(row.hora_salida || '--:--')}</div>
                    <div class="atlas-attendance-sub">Fin ${escapeHtml(row.hora_termino_visita || '--:--')}</div>
                </td>
                <td>${escapeHtml(row.tiempo_permanencia || 'Sin dato')}</td>
                <td class="text-end" style="max-width:18rem; white-space:normal;">
                    <strong>${number(row.gestiones_realizadas)}</strong>
                    ${managementDetails ? `<div class="atlas-attendance-sub">${escapeHtml(managementDetails)}</div>` : ''}
                </td>
                <td class="text-end"><strong>${row.pendientes_por_gestionar === null ? '-' : number(row.pendientes_por_gestionar)}</strong></td>
                <td class="text-center">${evidenceButton}</td>
                <td style="max-width:22rem; white-space:normal;">${escapeHtml(observations || 'Sin observaciones')}</td>
            </tr>`;
        }).join('');
    };

    const setSummary = (summary) => {
        document.getElementById('atlasAttendanceTotal').textContent = number(summary.total_visitas);
        document.getElementById('atlasAttendanceCompleted').textContent = number(summary.cumplidas);
        document.getElementById('atlasAttendanceMissed').textContent = number(summary.no_realizadas);
        document.getElementById('atlasAttendanceOutside').textContent = number(summary.fuera_ubicacion);
        document.getElementById('atlasAttendanceManaged').textContent = number(summary.gestiones_realizadas);
        document.getElementById('atlasAttendancePending').textContent = number(summary.pendientes_por_gestionar);
    };

    const currentFilteredRows = () => {
        if (!attendanceTable) return visibleRows.filter(rowMatchesFilters);
        return attendanceTable
            .rows({ search: 'applied' })
            .nodes()
            .toArray()
            .map((tableRow) => visibleRows[Number(tableRow.dataset.atlasRow)])
            .filter(Boolean);
    };

    const updateFilteredSummary = () => {
        const rows = currentFilteredRows();
        const visitRows = rows.filter((row) => row.es_visita !== false);
        setSummary({
            total_visitas: visitRows.length,
            cumplidas: visitRows.filter((row) => row.estatus_visita === 'Cumplida').length,
            no_realizadas: visitRows.filter((row) => row.estatus_visita === 'No realizada').length,
            fuera_ubicacion: rows.filter((row) => row.estatus_visita === 'Fuera de ubicación').length,
            gestiones_realizadas: rows.reduce(
                (total, row) => total + Number(row.gestiones_realizadas || 0),
                0
            ),
            pendientes_por_gestionar: visitRows.reduce(
                (total, row) => total + (
                    row.pendientes_por_gestionar === null
                    || row.pendientes_por_gestionar === undefined
                        ? 0
                        : Number(row.pendientes_por_gestionar || 0)
                ),
                0
            ),
        });
        document.getElementById('atlasAttendanceGenerated').textContent =
            `${number(rows.length)} de ${number(visibleRows.length)} registros` +
            (generatedAt ? ` · Generado ${generatedAt}` : '');
    };

    const destroyAttendanceTable = () => {
        if (!attendanceTable) return;
        window.jQuery('#atlasAttendanceTable').off('.atlasAttendance');
        attendanceTable.destroy();
        attendanceTable = null;
    };

    const initializeAttendanceTable = () => {
        if (!window.jQuery?.fn?.DataTable) {
            throw new Error('DataTables no está disponible en esta pantalla.');
        }
        destroyAttendanceTable();
        const $table = window.jQuery('#atlasAttendanceTable');
        attendanceTable = $table.DataTable({
            language: {
                emptyTable: 'No hay visitas ni gestiones para los filtros seleccionados',
                info: 'Mostrando de _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Sin registros para mostrar',
                infoFiltered: '(filtrado de _MAX_ registros)',
                zeroRecords: 'No se encontraron registros',
                lengthMenu: 'Mostrar _MENU_ registros',
                search: 'Buscar:',
                searchPlaceholder: 'Buscar en asistencias...',
                paginate: {
                    first: 'Primero',
                    last: 'Último',
                    next: 'Siguiente',
                    previous: 'Anterior',
                },
            },
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            deferRender: true,
            responsive: false,
            autoWidth: false,
            searchDelay: 100,
            order: [[0, 'desc']],
            columnDefs: [
                { targets: 10, orderable: false },
            ],
            initComplete() {
                const container = this.api().table().container();
                window.jQuery(container)
                    .find('input[type="search"]')
                    .attr('placeholder', 'Buscar en asistencias...');
            },
        });
        $table
            .off('draw.dt.atlasAttendance')
            .on('draw.dt.atlasAttendance', updateFilteredSummary);
        updateFilteredSummary();
    };

    const applyLocalFilters = () => {
        const validRange = dateRangeIsValid();
        startInput.classList.toggle('is-invalid', !validRange);
        endInput.classList.toggle('is-invalid', !validRange);
        downloadButton.disabled = loading || !apiReady || !validRange;
        if (attendanceTable) attendanceTable.draw();
    };

    const reportError = (message) => {
        destroyAttendanceTable();
        visibleRows = [];
        body.innerHTML = `<tr><td class="atlas-attendance-empty text-danger" colspan="12">${escapeHtml(message)}</td></tr>`;
        setSummary({});
        document.getElementById('atlasAttendanceGenerated').textContent = 'No disponible';
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'No se pudo cargar el reporte', text: message });
        }
    };

    const loadReport = async () => {
        if (loading || !apiReady) return;
        loading = true;
        downloadButton.disabled = true;
        document.getElementById('atlasAttendanceRefresh').disabled = true;
        destroyAttendanceTable();
        body.innerHTML = '<tr><td class="atlas-attendance-empty" colspan="12">Consultando asistencias...</td></tr>';
        try {
            const response = await fetch(`/Atlas/getReporteAsistencias?${datasetQuery().toString()}`, {
                headers: { Accept: 'application/json' }
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.mensaje || 'API-COMERCIAL devolvió un error.');
            }
            const data = payload.datos || {};
            const rows = Array.isArray(data.filas) ? data.filas : [];
            const catalogs = data.catalogos || {};
            updateSelect(
                collaboratorSelect,
                catalogs.colaboradores || [],
                'persona_id',
                'nombre',
                'Todos los colaboradores'
            );
            updateSelect(
                distributorSelect,
                catalogs.distribuidores || [],
                'id',
                'nombre',
                'Todos los distribuidores'
            );
            updateSelect(statusSelect, catalogs.estatuses || [], '', '', 'Todos los estatus');
            updateSelect(divisionalSelect, catalogs.divisionales || [], '', '', 'Todas las divisionales');
            generatedAt = String(data.generado_at || '').replace('T', ' ');
            renderRows(rows);
            initializeAttendanceTable();
            document.getElementById('atlasAttendancePerimeter').textContent =
                `Perímetro permitido: ${number(data.perimetro_metros)} m. ` +
                (data.pendientes_disponibles === false ? 'La fuente de pendientes no estuvo disponible en esta consulta.' : '');
            applyLocalFilters();
        } catch (error) {
            reportError(error.message || 'No se pudo consultar el reporte.');
        } finally {
            loading = false;
            document.getElementById('atlasAttendanceRefresh').disabled = false;
            downloadButton.disabled = !dateRangeIsValid();
        }
    };

    const initializeAttendanceModule = () => {
        if (!window.jQuery?.fn?.DataTable || !window.jQuery?.fn?.select2) {
            reportError('No se pudieron cargar los componentes de tabla y filtros.');
            downloadButton.disabled = true;
            return;
        }

        evidenceModal = typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(evidenceModalElement)
            : null;

        initializeSearchableSelects();
        registerAttendanceFilter();

        document.getElementById('atlasAttendanceRefresh').addEventListener('click', loadReport);
        document.getElementById('atlasAttendanceDownload').addEventListener('click', () => {
            if (!dateRangeIsValid()) {
                applyLocalFilters();
                return;
            }
            window.location.href = `/Atlas/descargarReporteAsistencias?${exportQuery().toString()}`;
        });
        document.getElementById('atlasAttendanceClear').addEventListener('click', () => {
            startInput.value = initialStart;
            endInput.value = initialEnd;
            [collaboratorSelect, distributorSelect, statusSelect, divisionalSelect, evidenceSelect].forEach((select) => {
                select.value = '';
                refreshSearchableSelect(select);
            });
            if (attendanceTable) attendanceTable.search('');
            applyLocalFilters();
        });
        [startInput, endInput].forEach((input) => {
            input.addEventListener('change', applyLocalFilters);
        });
        [collaboratorSelect, distributorSelect, statusSelect, divisionalSelect, evidenceSelect].forEach((select) => {
            window.jQuery(select)
                .off('change.atlasAttendance')
                .on('change.atlasAttendance', applyLocalFilters);
        });
        body.addEventListener('click', (event) => {
            const button = event.target.closest('[data-atlas-evidence-row]');
            if (!button) return;
            const row = visibleRows[Number(button.dataset.atlasEvidenceRow)];
            if (row) showEvidenceModal(row);
        });
        evidenceModalElement.addEventListener('hidden.bs.modal', () => {
            evidenceGrid.querySelectorAll('video, audio').forEach((media) => {
                media.pause();
                media.removeAttribute('src');
                media.querySelectorAll('source').forEach((source) => source.removeAttribute('src'));
                media.load();
            });
            evidenceGrid.innerHTML = '';
        });

        if (apiReady) {
            loadReport();
        } else {
            body.innerHTML = '<tr><td class="atlas-attendance-empty" colspan="12">API administrativa no configurada.</td></tr>';
            downloadButton.disabled = true;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAttendanceModule, { once: true });
    } else {
        initializeAttendanceModule();
    }
})();
</script>
