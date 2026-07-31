<?php
$atlasNow = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
$atlasStart = '';
$atlasEnd = $atlasNow->format('Y-m-d');
$atlasApiReady = !empty($atlas_admin_configurada);
?>

<div class="container-fluid py-3 atlas-expedientes-page">
    <style>
        .atlas-expedientes-page { color:#22303e; }
        .atlas-expedientes-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .atlas-expedientes-title { display:flex; align-items:center; gap:.65rem; margin:0; color:#173756; font-size:1.35rem; font-weight:900; }
        .atlas-expedientes-title i { color:#2563eb; }
        .atlas-expedientes-subtitle { margin:.2rem 0 0; color:#64748b; font-size:.86rem; font-weight:700; }
        .atlas-expedientes-filter-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#f8fafc; padding:.9rem; margin-bottom:1rem; }
        .atlas-expedientes-filters { display:grid; grid-template-columns:repeat(4, minmax(10rem, 1fr)) auto; gap:.7rem; align-items:end; }
        .atlas-expedientes-filter-actions { display:flex; align-items:center; gap:.45rem; }
        .atlas-expedientes-filter-actions .btn { min-height:2.35rem; white-space:nowrap; }
        .atlas-expedientes-metrics { display:grid; grid-template-columns:repeat(5, minmax(8rem, 1fr)); gap:.7rem; margin-bottom:1rem; }
        .atlas-expedientes-metric { border:1px solid #dbe4ef; border-left:4px solid #64748b; border-radius:.45rem; background:#fff; padding:.72rem .8rem; min-width:0; }
        .atlas-expedientes-metric.is-blue { border-left-color:#2563eb; }
        .atlas-expedientes-metric.is-green { border-left-color:#16a34a; }
        .atlas-expedientes-metric.is-red { border-left-color:#dc2626; }
        .atlas-expedientes-metric.is-amber { border-left-color:#d97706; }
        .atlas-expedientes-metric-label { color:#64748b; font-size:.68rem; font-weight:900; text-transform:uppercase; }
        .atlas-expedientes-metric-value { color:#172033; font-size:1.25rem; font-weight:900; line-height:1.1; margin-top:.2rem; }
        .atlas-expedientes-table-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#fff; overflow:hidden; }
        .atlas-expedientes-table-head { display:flex; align-items:center; justify-content:space-between; gap:.8rem; padding:.75rem .9rem; border-bottom:1px solid #e5e7eb; }
        .atlas-expedientes-table-title { margin:0; color:#173756; font-size:.9rem; font-weight:900; }
        .atlas-expedientes-table-meta { color:#64748b; font-size:.75rem; font-weight:800; }
        .atlas-expedientes-scroll { overflow-x:auto; }
        .atlas-expedientes-table { min-width:1240px; margin:0; }
        .atlas-expedientes-table th { background:#f8fafc; color:#566a7f; font-size:.68rem; font-weight:900; text-transform:uppercase; white-space:nowrap; }
        .atlas-expedientes-table td { color:#566a7f; font-size:.76rem; font-weight:700; vertical-align:middle; }
        .atlas-expedientes-main { color:#22303e; font-weight:900; line-height:1.2; }
        .atlas-expedientes-sub { color:#94a3b8; font-size:.68rem; font-weight:800; line-height:1.2; margin-top:.12rem; }
        .atlas-expedientes-status { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.25rem .6rem; font-size:.68rem; font-weight:900; white-space:nowrap; }
        .atlas-expedientes-status.is-pending { background:#eef2f7; color:#475569; }
        .atlas-expedientes-status.is-delivered { background:#dcfce7; color:#15803d; }
        .atlas-expedientes-status.is-not-delivered { background:#fee2e2; color:#b91c1c; }
        .atlas-expedientes-status.is-incident { background:#fef3c7; color:#92400e; }
        .atlas-expedientes-actions { display:flex; align-items:center; justify-content:flex-end; gap:.35rem; min-width:10.5rem; }
        .atlas-expedientes-icon-btn { display:inline-grid; place-items:center; width:2.1rem; height:2.1rem; padding:0; border-radius:.4rem; }
        .atlas-expedientes-icon-btn:disabled { opacity:.42; }
        .atlas-expedientes-page .select2-container { width:100% !important; }
        .atlas-expedientes-page .select2-container .select2-selection--single { min-height:2.35rem; display:flex; align-items:center; border-color:#d9dee3; }
        .atlas-expedientes-page .select2-container--default .select2-selection--single .select2-selection__rendered { padding-left:.75rem; color:#566a7f; }
        .atlas-expedientes-page .select2-container--default .select2-selection--single .select2-selection__arrow { height:2.25rem; }
        .atlas-expedientes-page .dataTables_wrapper > .row:first-child,
        .atlas-expedientes-page .dataTables_wrapper > .row:last-child,
        .atlas-expedientes-page .dataTables_wrapper > .dt-layout-row { margin:0; padding:.85rem .9rem; align-items:center; }
        .atlas-expedientes-page .dataTables_wrapper > .row:first-child,
        .atlas-expedientes-page .dataTables_wrapper > .dt-layout-row:first-child { border-bottom:1px solid #e5e7eb; }
        .atlas-expedientes-page .dataTables_wrapper > .row:last-child,
        .atlas-expedientes-page .dataTables_wrapper > .dt-layout-row:last-child { border-top:1px solid #e5e7eb; }
        .atlas-expedientes-page .dataTables_wrapper .dataTables_filter,
        .atlas-expedientes-page .dataTables_wrapper .dataTables_length,
        .atlas-expedientes-page .dataTables_wrapper .dt-search,
        .atlas-expedientes-page .dataTables_wrapper .dt-length { color:#566a7f; font-size:.82rem; }
        .atlas-expedientes-page .dataTables_wrapper .dataTables_filter input,
        .atlas-expedientes-page .dataTables_wrapper .dt-search input { min-width:16rem; border-radius:.375rem; border-color:#d9dee3; }
        .atlas-expedientes-timeline { position:relative; display:grid; gap:.75rem; }
        .atlas-expedientes-event { border:1px solid #e2e8f0; border-left:4px solid #2563eb; border-radius:.45rem; padding:.8rem .9rem; background:#fff; }
        .atlas-expedientes-event-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; flex-wrap:wrap; }
        .atlas-expedientes-event-title { color:#22303e; font-size:.84rem; font-weight:900; }
        .atlas-expedientes-event-meta { color:#64748b; font-size:.7rem; font-weight:700; }
        .atlas-expedientes-event-text { margin-top:.45rem; color:#566a7f; font-size:.77rem; overflow-wrap:anywhere; }
        .atlas-expedientes-detail-grid { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.7rem; margin-bottom:1rem; }
        .atlas-expedientes-detail-item { border-bottom:1px solid #e2e8f0; padding:.4rem 0 .65rem; min-width:0; }
        .atlas-expedientes-detail-label { color:#94a3b8; font-size:.65rem; font-weight:900; text-transform:uppercase; }
        .atlas-expedientes-detail-value { color:#22303e; font-size:.8rem; font-weight:900; margin-top:.15rem; overflow-wrap:anywhere; }
        .atlas-expedientes-evidence-section { margin-bottom:1.25rem; padding-bottom:1.25rem; border-bottom:1px solid #e2e8f0; }
        .atlas-expedientes-evidence-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.75rem; }
        .atlas-expedientes-evidence-title { display:flex; align-items:center; gap:.5rem; margin:0; color:#22303e; font-size:.9rem; font-weight:900; }
        .atlas-expedientes-evidence-title i { color:#2563eb; }
        .atlas-expedientes-evidence-count { display:inline-flex; align-items:center; justify-content:center; min-width:1.75rem; height:1.5rem; padding:0 .45rem; border-radius:999px; background:#e0e7ff; color:#3730a3; font-size:.68rem; font-weight:900; }
        .atlas-expedientes-evidence-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(14rem, 1fr)); gap:.75rem; }
        .atlas-expedientes-evidence-item { min-width:0; overflow:hidden; border:1px solid #dbe4ef; border-radius:.45rem; background:#fff; }
        .atlas-expedientes-evidence-media { position:relative; display:grid; place-items:center; width:100%; aspect-ratio:4/3; overflow:hidden; background:#f1f5f9; color:#64748b; text-decoration:none; }
        .atlas-expedientes-evidence-media img { width:100%; height:100%; object-fit:contain; transition:transform .18s ease; }
        .atlas-expedientes-evidence-media:hover img { transform:scale(1.02); }
        .atlas-expedientes-evidence-fallback { position:absolute; inset:0; display:none; flex-direction:column; align-items:center; justify-content:center; gap:.45rem; padding:1rem; color:#64748b; text-align:center; font-size:.74rem; font-weight:800; }
        .atlas-expedientes-evidence-fallback i { color:#d97706; font-size:1.4rem; }
        .atlas-expedientes-evidence-media.is-unavailable img { display:none; }
        .atlas-expedientes-evidence-media.is-unavailable .atlas-expedientes-evidence-fallback { display:flex; }
        .atlas-expedientes-evidence-body { padding:.7rem .75rem .75rem; }
        .atlas-expedientes-evidence-name { color:#22303e; font-size:.8rem; font-weight:900; line-height:1.25; overflow-wrap:anywhere; }
        .atlas-expedientes-evidence-meta { display:flex; align-items:flex-start; gap:.4rem; margin-top:.35rem; color:#64748b; font-size:.68rem; font-weight:700; line-height:1.25; }
        .atlas-expedientes-evidence-meta i { width:.8rem; margin-top:.08rem; color:#94a3b8; text-align:center; }
        .atlas-expedientes-evidence-open { margin-top:.65rem; }
        .atlas-expedientes-evidence-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.5rem; min-height:8rem; padding:1.25rem; border:1px dashed #cbd5e1; border-radius:.45rem; background:#f8fafc; color:#64748b; text-align:center; font-size:.78rem; font-weight:800; }
        .atlas-expedientes-evidence-empty i { color:#94a3b8; font-size:1.5rem; }
        .atlas-expedientes-scope { display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; margin-top:.45rem; }
        .atlas-expedientes-scope-badge { display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; padding:.25rem .6rem; background:#dbeafe; color:#1d4ed8; font-size:.7rem; font-weight:900; }
        .atlas-expedientes-scope-copy { color:#64748b; font-size:.75rem; font-weight:800; }
        .atlas-expedientes-import-help { display:grid; gap:.55rem; padding:.75rem .85rem; border:1px solid #bfdbfe; border-radius:.45rem; background:#eff6ff; color:#1e3a5f; font-size:.78rem; font-weight:700; }
        .atlas-expedientes-import-help strong { color:#1d4ed8; }
        @media (max-width: 1399.98px) {
            .atlas-expedientes-filters { grid-template-columns:repeat(3, minmax(10rem, 1fr)); }
            .atlas-expedientes-metrics { grid-template-columns:repeat(3, minmax(9rem, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .atlas-expedientes-filters,
            .atlas-expedientes-metrics,
            .atlas-expedientes-detail-grid,
            .atlas-expedientes-evidence-grid { grid-template-columns:1fr; }
            .atlas-expedientes-filter-actions,
            .atlas-expedientes-filter-actions .btn { width:100%; }
            .atlas-expedientes-filter-actions .btn { flex:1; }
        }
    </style>

    <div class="atlas-expedientes-head">
        <div>
            <h1 class="atlas-expedientes-title">
                <i class="fa-solid fa-folder-open"></i>
                <span>Expedientes</span>
            </h1>
            <p class="atlas-expedientes-subtitle">Control de entrega e incidencias por cr&eacute;dito, cliente y sucursal.</p>
            <div class="atlas-expedientes-scope">
                <span class="atlas-expedientes-scope-badge"><i class="fa-solid fa-bolt"></i>S2Credit</span>
                <span class="atlas-expedientes-scope-copy">Hist&oacute;rico de activaciones desde el inicio de Maxikash.</span>
            </div>
        </div>
        <button type="button" class="btn btn-primary btn-sm" id="atlasExpedientesImportOpen"
                <?= $atlasApiReady ? '' : 'disabled' ?>>
            <i class="fa-solid fa-file-arrow-up me-2"></i>Cargar layout
        </button>
    </div>

    <?php if (!$atlasApiReady): ?>
        <div class="alert alert-info d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-circle-info"></i>
            <span>El servicio de Expedientes no est&aacute; disponible por el momento. Contacta a soporte para revisarlo.</span>
        </div>
    <?php endif; ?>

    <section class="atlas-expedientes-filter-panel" aria-label="Filtros de expedientes">
        <div class="atlas-expedientes-filters">
            <div>
                <label class="form-label mb-1" for="atlasExpedientesStart">Activaci&oacute;n desde (opcional)</label>
                <input class="form-control form-control-sm" type="date" id="atlasExpedientesStart"
                       value="<?= htmlspecialchars($atlasStart, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label class="form-label mb-1" for="atlasExpedientesEnd">Activaci&oacute;n hasta</label>
                <input class="form-control form-control-sm" type="date" id="atlasExpedientesEnd"
                       value="<?= htmlspecialchars($atlasEnd, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div>
                <label class="form-label mb-1" for="atlasExpedientesStatus">Estatus</label>
                <select class="form-select form-select-sm atlas-expedientes-select" id="atlasExpedientesStatus">
                    <option value="">Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="entregado">Recolectado</option>
                    <option value="no_entregado">No recolectado</option>
                    <option value="incidencia">Incidencia</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" for="atlasExpedientesBranch">Sucursal</label>
                <select class="form-select form-select-sm atlas-expedientes-select" id="atlasExpedientesBranch">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="atlas-expedientes-filter-actions">
                <button type="button" class="btn btn-outline-secondary btn-sm" id="atlasExpedientesClear" title="Eliminar filtros">
                    <i class="fa-solid fa-filter-circle-xmark me-2"></i>Eliminar filtros
                </button>
                <button type="button" class="btn btn-label-secondary btn-sm atlas-expedientes-icon-btn" id="atlasExpedientesRefresh" title="Actualizar" aria-label="Actualizar">
                    <i class="fa-solid fa-rotate"></i>
                </button>
            </div>
        </div>
    </section>

    <section class="atlas-expedientes-metrics" aria-label="Resumen de expedientes">
        <article class="atlas-expedientes-metric is-blue">
            <div class="atlas-expedientes-metric-label">Expedientes</div>
            <div class="atlas-expedientes-metric-value" id="atlasExpedientesTotal">0</div>
        </article>
        <article class="atlas-expedientes-metric">
            <div class="atlas-expedientes-metric-label">Pendientes</div>
            <div class="atlas-expedientes-metric-value" id="atlasExpedientesPending">0</div>
        </article>
        <article class="atlas-expedientes-metric is-green">
            <div class="atlas-expedientes-metric-label">Recolectados</div>
            <div class="atlas-expedientes-metric-value" id="atlasExpedientesDelivered">0</div>
        </article>
        <article class="atlas-expedientes-metric is-red">
            <div class="atlas-expedientes-metric-label">No recolectados</div>
            <div class="atlas-expedientes-metric-value" id="atlasExpedientesNotDelivered">0</div>
        </article>
        <article class="atlas-expedientes-metric is-amber">
            <div class="atlas-expedientes-metric-label">Incidencias</div>
            <div class="atlas-expedientes-metric-value" id="atlasExpedientesIncidents">0</div>
        </article>
    </section>

    <section class="atlas-expedientes-table-panel">
        <div class="atlas-expedientes-table-head">
            <h2 class="atlas-expedientes-table-title">Cr&eacute;ditos activos en S2Credit</h2>
            <span class="atlas-expedientes-table-meta" id="atlasExpedientesMeta">Sin consultar</span>
        </div>
        <div class="atlas-expedientes-scroll">
            <table id="atlasExpedientesTable" class="table table-hover atlas-expedientes-table w-100">
                <thead>
                    <tr>
                        <th>Cr&eacute;dito</th>
                        <th>Cliente</th>
                        <th>Sucursal</th>
                        <th>Etapa</th>
                        <th>Monto</th>
                        <th>Estatus expediente</th>
                        <th>Activaci&oacute;n S2</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>

<div class="modal fade" id="atlasExpedientesMovementModal" tabindex="-1" aria-labelledby="atlasExpedientesMovementTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="atlasExpedientesMovementForm">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5" id="atlasExpedientesMovementTitle">Registrar movimiento</h2>
                    <div class="text-muted small fw-semibold" id="atlasExpedientesMovementMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="atlasExpedientesMovementCredit">
                <input type="hidden" id="atlasExpedientesMovementAction">
                <div class="mb-3">
                    <label class="form-label" for="atlasExpedientesMovementReason">Motivo *</label>
                    <input type="text" class="form-control" id="atlasExpedientesMovementReason" maxlength="500" minlength="5" required>
                    <div class="invalid-feedback">Captura un motivo de al menos 5 caracteres.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="atlasExpedientesMovementComment">Comentario</label>
                    <textarea class="form-control" id="atlasExpedientesMovementComment" rows="3" maxlength="5000"></textarea>
                </div>
                <div>
                    <label class="form-label" for="atlasExpedientesMovementEvidence">URL de evidencia</label>
                    <input type="url" class="form-control" id="atlasExpedientesMovementEvidence" maxlength="2048" placeholder="https://">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="atlasExpedientesMovementSubmit">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Guardar movimiento
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="atlasExpedientesDetailModal" tabindex="-1" aria-labelledby="atlasExpedientesDetailTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5" id="atlasExpedientesDetailTitle">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Detalle del expediente
                    </h2>
                    <div class="text-muted small fw-semibold" id="atlasExpedientesDetailMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="atlasExpedientesDetailContent"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="atlasExpedientesImportModal" tabindex="-1" aria-labelledby="atlasExpedientesImportTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" id="atlasExpedientesImportForm">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5" id="atlasExpedientesImportTitle">
                        <i class="fa-solid fa-file-excel me-2 text-primary"></i>Cargar layout de expedientes
                    </h2>
                    <div class="text-muted small fw-semibold">Se validar&aacute;n todos los registros antes de aplicar cambios.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="atlas-expedientes-import-help mb-3">
                    <div><strong>Columnas requeridas:</strong> ID cr&eacute;dito y Estatus expediente.</div>
                    <div><strong>Estados permitidos:</strong> Expediente recolectado, Expediente no recolectado e Incidencia.</div>
                    <div>Para No recolectado e Incidencia, agrega una columna Motivo, Incidencia u Observaciones.</div>
                </div>
                <label class="form-label fw-bold" for="atlasExpedientesImportFile">Archivo Excel</label>
                <input class="form-control" type="file" id="atlasExpedientesImportFile" name="archivo"
                       accept=".xlsx,.xls" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="atlasExpedientesImportSubmit">
                    <i class="fa-solid fa-cloud-arrow-up me-2"></i>Procesar layout
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(() => {
    const apiReady = <?= $atlasApiReady ? 'true' : 'false' ?>;
    const initialStart = <?= json_encode($atlasStart, JSON_UNESCAPED_SLASHES) ?>;
    const initialEnd = <?= json_encode($atlasEnd, JSON_UNESCAPED_SLASHES) ?>;
    const startInput = document.getElementById('atlasExpedientesStart');
    const endInput = document.getElementById('atlasExpedientesEnd');
    const statusSelect = document.getElementById('atlasExpedientesStatus');
    const branchSelect = document.getElementById('atlasExpedientesBranch');
    const refreshButton = document.getElementById('atlasExpedientesRefresh');
    const importOpenButton = document.getElementById('atlasExpedientesImportOpen');
    const importForm = document.getElementById('atlasExpedientesImportForm');
    const importSubmitButton = document.getElementById('atlasExpedientesImportSubmit');
    const movementForm = document.getElementById('atlasExpedientesMovementForm');
    const movementModalElement = document.getElementById('atlasExpedientesMovementModal');
    const detailModalElement = document.getElementById('atlasExpedientesDetailModal');
    const importModalElement = document.getElementById('atlasExpedientesImportModal');
    let table = null;
    let movementModal = null;
    let detailModal = null;
    let importModal = null;
    let saving = false;
    let lastError = '';
    let loadingRequests = 0;
    let loadingAlertOpen = false;
    let pendingTableError = '';

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const number = (value) => new Intl.NumberFormat('es-MX').format(Number(value || 0));
    const money = (value) => new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN',
        maximumFractionDigits: 2,
    }).format(Number(value || 0));

    const dateTime = (value) => {
        if (!value) return 'Sin movimientos';
        const parsed = new Date(value);
        if (Number.isNaN(parsed.getTime())) return String(value).replace('T', ' ');
        return new Intl.DateTimeFormat('es-MX', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
        }).format(parsed);
    };

    const statusDefinition = (status) => {
        const definitions = {
            pendiente: { label: 'Pendiente', css: 'is-pending', icon: 'fa-clock' },
            entregado: { label: 'Recolectado', css: 'is-delivered', icon: 'fa-check' },
            no_entregado: { label: 'No recolectado', css: 'is-not-delivered', icon: 'fa-xmark' },
            incidencia: { label: 'Incidencia', css: 'is-incident', icon: 'fa-triangle-exclamation' },
        };
        return definitions[String(status || '')] || definitions.pendiente;
    };

    const statusBadge = (status) => {
        const item = statusDefinition(status);
        return `<span class="atlas-expedientes-status ${item.css}"><i class="fa-solid ${item.icon}"></i>${item.label}</span>`;
    };

    const safeEvidenceLink = (value) => {
        const url = String(value || '').trim();
        if (!/^https?:\/\//i.test(url)) return '';
        return `<a class="btn btn-sm btn-label-primary mt-2" href="${escapeHtml(url)}" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-paperclip me-2"></i>Ver evidencia</a>`;
    };

    const requestId = () => {
        if (window.crypto?.randomUUID) return window.crypto.randomUUID();
        return `exp-${Date.now()}-${Math.random().toString(16).slice(2)}`;
    };

    const showError = (message) => {
        const text = String(message || 'No se pudo completar la operacion.');
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Operacion no disponible', text });
        }
    };

    const showSuccess = (message) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Expediente actualizado',
                text: String(message || 'El movimiento se guardo correctamente.'),
                timer: 1800,
                showConfirmButton: false,
            });
        }
    };

    const showExpedientesLoading = () => {
        loadingRequests++;
        if (loadingRequests > 1) return;
        loadingAlertOpen = true;
        if (typeof showWait === 'function') {
            showWait('Consultando expedientes...');
            return;
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Procesando su petici\u00f3n',
                text: 'Consultando expedientes...',
                imageUrl: '/assets/img/wait.svg',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
            });
        }
    };

    const hideExpedientesLoading = () => {
        loadingRequests = Math.max(0, loadingRequests - 1);
        if (loadingRequests > 0) return;
        if (loadingAlertOpen && typeof Swal !== 'undefined' && Swal.isVisible()) {
            Swal.close();
        }
        loadingAlertOpen = false;
        if (pendingTableError) {
            const message = pendingTableError;
            pendingTableError = '';
            showError(message);
        }
    };

    const dateRangeIsValid = () => {
        if (!startInput.value || !endInput.value) return true;
        return endInput.value >= startInput.value;
    };

    const validateDateRange = (showMessage = false) => {
        const valid = dateRangeIsValid();
        startInput.classList.toggle('is-invalid', !valid);
        endInput.classList.toggle('is-invalid', !valid);
        if (!valid && showMessage) showError('La fecha final no puede ser anterior a la fecha inicial.');
        return valid;
    };

    const setSummary = (summary = {}) => {
        document.getElementById('atlasExpedientesTotal').textContent = number(summary.total);
        document.getElementById('atlasExpedientesPending').textContent = number(summary.pendientes);
        document.getElementById('atlasExpedientesDelivered').textContent = number(summary.entregados);
        document.getElementById('atlasExpedientesNotDelivered').textContent = number(summary.no_entregados);
        document.getElementById('atlasExpedientesIncidents').textContent = number(summary.incidencias);
    };

    const initializeSelects = () => {
        const placeholders = new Map([
            [statusSelect, 'Todos los estatus'],
            [branchSelect, 'Todas las sucursales'],
        ]);
        placeholders.forEach((placeholder, select) => {
            window.jQuery(select).select2({
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

    const updateSelect = (select, items, valueKey, labelKey, emptyLabel) => {
        const selected = String(select.value || '');
        const options = [`<option value="">${escapeHtml(emptyLabel)}</option>`];
        (items || []).forEach((item) => {
            const value = valueKey ? item?.[valueKey] : item;
            const label = labelKey ? item?.[labelKey] : item;
            if (value === null || value === undefined || String(value) === '') return;
            options.push(`<option value="${escapeHtml(value)}">${escapeHtml(label)}</option>`);
        });
        select.innerHTML = options.join('');
        if ([...select.options].some((option) => option.value === selected)) select.value = selected;
        window.jQuery(select).trigger('change.select2');
    };

    const updateCatalogs = (catalogs = {}) => {
        updateSelect(branchSelect, catalogs.sucursales || [], 'fk_sucursal', 'nombre', 'Todas las sucursales');
    };

    const rowActions = (row) => {
        const creditId = Number(row.credito_id || 0);
        const current = String(row.estatus || 'pendiente');
        const action = (name, title, icon, css) => `
            <button type="button" class="btn btn-sm ${css} atlas-expedientes-icon-btn"
                    data-exp-action="${name}" data-exp-credit="${creditId}" title="${title}" aria-label="${title}"
                    ${current === name ? 'disabled' : ''}>
                <i class="fa-solid ${icon}"></i>
            </button>`;
        return `<div class="atlas-expedientes-actions">
            ${action('entregado', 'Marcar recolectado', 'fa-check', 'btn-label-success')}
            ${action('no_entregado', 'Marcar no recolectado', 'fa-xmark', 'btn-label-danger')}
            ${action('incidencia', 'Registrar incidencia', 'fa-triangle-exclamation', 'btn-label-warning')}
            <button type="button" class="btn btn-sm btn-label-primary atlas-expedientes-icon-btn"
                    data-exp-action="detalle" data-exp-credit="${creditId}" title="Ver expediente" aria-label="Ver expediente">
                <i class="fa-solid fa-eye"></i>
            </button>
        </div>`;
    };

    const loadDataTable = () => {
        table = window.jQuery('#atlasExpedientesTable').DataTable({
            processing: false,
            serverSide: true,
            responsive: false,
            autoWidth: false,
            ordering: false,
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            searchDelay: 300,
            ajax: async (request, callback) => {
                if (!apiReady || !validateDateRange(false)) {
                    callback({ draw: request.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
                    return;
                }
                const params = new URLSearchParams({
                    page: String(Math.floor(request.start / request.length) + 1),
                    page_size: String(request.length),
                });
                if (startInput.value) params.set('fecha_inicio', startInput.value);
                if (endInput.value) params.set('fecha_fin', endInput.value);
                if (statusSelect.value) params.set('estatus', statusSelect.value);
                if (branchSelect.value) params.set('fk_sucursal', branchSelect.value);
                if (request.search?.value) params.set('search', request.search.value);
                refreshButton.disabled = true;
                showExpedientesLoading();
                try {
                    const response = await fetch(`/Atlas/getExpedientes?${params.toString()}`, {
                        headers: { Accept: 'application/json' },
                    });
                    const payload = await response.json();
                    if (!response.ok || !payload.success) {
                        throw new Error(payload.mensaje || 'API-COMERCIAL devolvio un error.');
                    }
                    const data = payload.datos || {};
                    const pagination = data.paginacion || {};
                    updateCatalogs(data.catalogos || {});
                    setSummary(data.resumen || {});
                    const period = data.periodo || {};
                    const scope = period.fecha_inicio
                        ? `${period.fecha_inicio} a ${period.fecha_fin || 'hoy'}`
                        : `Hist\u00f3rico hasta ${period.fecha_fin || 'hoy'}`;
                    document.getElementById('atlasExpedientesMeta').textContent =
                        `${number(pagination.total_filtrados)} expediente(s) \u00b7 ${scope}`;
                    lastError = '';
                    pendingTableError = '';
                    callback({
                        draw: request.draw,
                        data: Array.isArray(data.filas) ? data.filas : [],
                        recordsTotal: Number(pagination.total_registros || 0),
                        recordsFiltered: Number(pagination.total_filtrados || 0),
                    });
                } catch (error) {
                    setSummary({});
                    document.getElementById('atlasExpedientesMeta').textContent = 'No disponible';
                    callback({ draw: request.draw, data: [], recordsTotal: 0, recordsFiltered: 0 });
                    const message = error.message || 'No se pudieron consultar los expedientes.';
                    if (message !== lastError) {
                        lastError = message;
                        pendingTableError = message;
                    }
                } finally {
                    refreshButton.disabled = false;
                    hideExpedientesLoading();
                }
            },
            columns: [
                {
                    data: null,
                    render: (_data, type, row) => {
                        if (type !== 'display') return row.numero_credito || row.credito_id;
                        return `<div class="atlas-expedientes-main">#${escapeHtml(row.numero_credito || row.credito_id)}</div>
                            <div class="atlas-expedientes-sub">ID oferta ${escapeHtml(row.credito_id)}</div>`;
                    },
                },
                {
                    data: null,
                    render: (_data, type, row) => {
                        if (type !== 'display') return row.cliente_nombre;
                        return `<div class="atlas-expedientes-main">${escapeHtml(row.cliente_nombre || 'Cliente sin nombre')}</div>
                            <div class="atlas-expedientes-sub">ID cliente ${escapeHtml(row.cliente_id || 'Sin dato')}</div>`;
                    },
                },
                {
                    data: null,
                    render: (_data, type, row) => {
                        if (type !== 'display') return row.sucursal;
                        return `<div class="atlas-expedientes-main">${escapeHtml(row.sucursal || 'Sin sucursal')}</div>
                            <div class="atlas-expedientes-sub">Sucursal ${escapeHtml(row.fk_sucursal || 'Sin dato')}</div>`;
                    },
                },
                {
                    data: null,
                    render: (_data, type, row) => {
                        if (type !== 'display') return row.etapa_credito;
                        return `<div class="atlas-expedientes-main">${escapeHtml(row.etapa_credito || 'Sin etapa')}</div>
                            <div class="atlas-expedientes-sub">${escapeHtml(row.oferta_estatus || '')}</div>`;
                    },
                },
                {
                    data: 'monto_financiar',
                    className: 'text-end',
                    render: (value, type) => type === 'display' ? `<strong>${money(value)}</strong>` : Number(value || 0),
                },
                {
                    data: 'estatus',
                    render: (value, type) => type === 'display' ? statusBadge(value) : value,
                },
                {
                    data: 'fecha_activacion_s2',
                    render: (value, type, row) => {
                        if (type !== 'display') return value || '';
                        return `<div class="atlas-expedientes-main">${escapeHtml(dateTime(value))}</div>
                            <div class="atlas-expedientes-sub">Actualizaci&oacute;n ${escapeHtml(dateTime(row.fecha_estado))}</div>`;
                    },
                },
                {
                    data: null,
                    className: 'text-end',
                    searchable: false,
                    render: (_data, type, row) => type === 'display' ? rowActions(row) : '',
                },
            ],
            language: {
                emptyTable: 'No hay expedientes para los filtros seleccionados',
                info: 'Mostrando de _START_ a _END_ de _TOTAL_ expedientes',
                infoEmpty: 'Sin expedientes para mostrar',
                infoFiltered: '',
                zeroRecords: 'No se encontraron expedientes',
                lengthMenu: 'Mostrar _MENU_ expedientes',
                search: 'Buscar:',
                searchPlaceholder: 'Credito, cliente o sucursal...',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior',
                },
            },
        });
    };

    const currentRow = (creditId) => {
        if (!table) return null;
        const rows = table.rows().data().toArray();
        return rows.find((row) => Number(row.credito_id) === Number(creditId)) || null;
    };

    const postMovement = async (creditId, action, values = {}) => {
        if (saving) return;
        saving = true;
        const submitButton = document.getElementById('atlasExpedientesMovementSubmit');
        submitButton.disabled = true;
        try {
            const response = await fetch('/Atlas/registrarMovimientoExpediente', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    credito_id: Number(creditId),
                    accion: action,
                    motivo: values.motivo || null,
                    comentario: values.comentario || null,
                    evidencia_url: values.evidencia_url || null,
                    request_id: requestId(),
                }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.mensaje || 'No se pudo guardar el movimiento.');
            }
            if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
            movementModal?.hide();
            showSuccess(payload.mensaje);
            table.ajax.reload(null, false);
        } catch (error) {
            showError(error.message || 'No se pudo guardar el movimiento.');
        } finally {
            saving = false;
            submitButton.disabled = false;
        }
    };

    const markDelivered = async (row) => {
        const label = `#${row.numero_credito || row.credito_id}`;
        const confirmed = typeof Swal === 'undefined'
            ? window.confirm(`Marcar el credito ${label} como recolectado?`)
            : await Swal.fire({
                icon: 'question',
                title: 'Confirmar recolecci\u00f3n',
                html: `El expediente del cr&eacute;dito <strong>${escapeHtml(label)}</strong> quedar&aacute; registrado como recolectado.`,
                showCancelButton: true,
                confirmButtonText: 'Confirmar recolecci\u00f3n',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#16a34a',
            }).then((result) => Boolean(result.isConfirmed));
        if (confirmed) await postMovement(row.credito_id, 'entregado');
    };

    const openMovement = (row, action) => {
        const definitions = {
            no_entregado: {
                title: 'Registrar no recolectado',
                placeholder: 'Ej. El expediente no estaba disponible',
            },
            incidencia: {
                title: 'Registrar incidencia',
                placeholder: 'Ej. Factura con motocicleta diferente o nombre incorrecto',
            },
        };
        const definition = definitions[action];
        if (!definition) return;
        movementForm.reset();
        movementForm.classList.remove('was-validated');
        document.getElementById('atlasExpedientesMovementCredit').value = row.credito_id;
        document.getElementById('atlasExpedientesMovementAction').value = action;
        document.getElementById('atlasExpedientesMovementTitle').textContent = definition.title;
        document.getElementById('atlasExpedientesMovementMeta').textContent =
            `Credito #${row.numero_credito || row.credito_id} - ${row.cliente_nombre || 'Cliente sin nombre'}`;
        document.getElementById('atlasExpedientesMovementReason').placeholder = definition.placeholder;
        movementModal?.show();
    };

    const detailItem = (label, value) => `
        <div class="atlas-expedientes-detail-item">
            <div class="atlas-expedientes-detail-label">${escapeHtml(label)}</div>
            <div class="atlas-expedientes-detail-value">${escapeHtml(value ?? 'Sin dato')}</div>
        </div>`;

    const positiveInteger = (value) => {
        const parsed = Number(value);
        return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
    };

    const evidenceChannelLabel = (value) => {
        const label = String(value || '').trim().replace(/[_-]+/g, ' ');
        return label ? label.charAt(0).toUpperCase() + label.slice(1) : 'Canal sin dato';
    };

    const renderEvidenceGallery = (data, requestedCreditId) => {
        const evidences = Array.isArray(data.evidencias)
            ? data.evidencias.filter((evidence) => evidence && typeof evidence === 'object')
            : [];
        const declaredTotal = Number(data.total_evidencias);
        const total = Number.isInteger(declaredTotal) && declaredTotal >= 0
            ? Math.max(declaredTotal, evidences.length)
            : evidences.length;
        const creditId = positiveInteger(data.expediente?.credito_id) || positiveInteger(requestedCreditId);
        const cards = evidences.map((evidence, index) => {
            const context = evidence.contexto && typeof evidence.contexto === 'object'
                ? evidence.contexto
                : {};
            const evidenceId = positiveInteger(evidence.id);
            const available = [true, 1, '1', 'true'].includes(evidence.disponible);
            const mimeType = String(evidence.mime_type || '').toLowerCase();
            const evidenceType = String(evidence.tipo || '').toLowerCase();
            const isPhoto = mimeType.startsWith('image/')
                || ['imagen', 'image', 'foto'].includes(evidenceType);
            const source = creditId && evidenceId && available && isPhoto
                ? `/Atlas/verEvidenciaExpediente?credito_id=${creditId}&id=${evidenceId}`
                : '';
            const name = String(evidence.nombre || `Evidencia ${index + 1}`);
            const evidenceDate = context.fecha_gestion
                || evidence.fecha_gestion
                || evidence.fecha_hora
                || '';
            const gestor = String(context.gestor || evidence.gestor || 'Gestor sin dato');
            const channel = evidenceChannelLabel(context.canal_gestion || evidence.canal_gestion);
            const comment = String(context.comentario || evidence.comentario || '').trim();
            const fallback = `
                <div class="atlas-expedientes-evidence-fallback">
                    <i class="fa-solid fa-image"></i>
                    <span>${source ? 'No se pudo cargar la vista previa.' : 'Evidencia no disponible.'}</span>
                </div>`;
            const media = source
                ? `<a class="atlas-expedientes-evidence-media" href="${source}" target="_blank"
                        rel="noopener noreferrer" aria-label="Abrir ${escapeHtml(name)} en tama&ntilde;o completo">
                        <img src="${source}" alt="${escapeHtml(name)}" loading="lazy" decoding="async"
                             data-exp-evidence-image>
                        ${fallback}
                    </a>`
                : `<div class="atlas-expedientes-evidence-media is-unavailable">${fallback}</div>`;

            return `<article class="atlas-expedientes-evidence-item">
                ${media}
                <div class="atlas-expedientes-evidence-body">
                    <div class="atlas-expedientes-evidence-name">${escapeHtml(name)}</div>
                    <div class="atlas-expedientes-evidence-meta">
                        <i class="fa-regular fa-calendar"></i>
                        <span>${escapeHtml(evidenceDate ? dateTime(evidenceDate) : 'Fecha sin dato')}</span>
                    </div>
                    <div class="atlas-expedientes-evidence-meta">
                        <i class="fa-solid fa-user"></i>
                        <span>${escapeHtml(gestor)}</span>
                    </div>
                    <div class="atlas-expedientes-evidence-meta">
                        <i class="fa-solid fa-route"></i>
                        <span>${escapeHtml(channel)}</span>
                    </div>
                    ${comment ? `<div class="atlas-expedientes-evidence-meta">
                        <i class="fa-regular fa-comment"></i>
                        <span>${escapeHtml(comment)}</span>
                    </div>` : ''}
                    ${source ? `<a class="btn btn-sm btn-label-primary atlas-expedientes-evidence-open"
                            href="${source}" target="_blank" rel="noopener noreferrer">
                            <i class="fa-solid fa-up-right-from-square me-2"></i>Abrir imagen
                        </a>` : ''}
                </div>
            </article>`;
        }).join('');

        return `<section class="atlas-expedientes-evidence-section" aria-label="Evidencias fotograficas">
            <div class="atlas-expedientes-evidence-head">
                <h3 class="atlas-expedientes-evidence-title">
                    <i class="fa-solid fa-images"></i>Evidencias fotogr&aacute;ficas
                </h3>
                <span class="atlas-expedientes-evidence-count">${number(total)}</span>
            </div>
            ${cards
                ? `<div class="atlas-expedientes-evidence-grid">${cards}</div>`
                : `<div class="atlas-expedientes-evidence-empty">
                    <i class="fa-solid fa-image"></i>
                    <span>Este expediente no tiene evidencias fotogr&aacute;ficas registradas.</span>
                </div>`}
        </section>`;
    };

    const bindEvidenceImageFallbacks = (container) => {
        container.querySelectorAll('[data-exp-evidence-image]').forEach((image) => {
            image.addEventListener('error', () => {
                image.closest('.atlas-expedientes-evidence-media')?.classList.add('is-unavailable');
            }, { once: true });
        });
    };

    const renderDetail = (data, requestedCreditId) => {
        const expediente = data.expediente || {};
        const history = Array.isArray(data.bitacora) ? data.bitacora : [];
        const events = history.length
            ? history.map((event) => `
                <article class="atlas-expedientes-event">
                    <div class="atlas-expedientes-event-head">
                        <div>
                            <div class="atlas-expedientes-event-title">
                                ${statusBadge(event.estatus_anterior)}
                                <i class="fa-solid fa-arrow-right mx-2 text-muted"></i>
                                ${statusBadge(event.estatus_nuevo)}
                            </div>
                            <div class="atlas-expedientes-event-meta mt-2">
                                ${escapeHtml(event.usuario_nombre || 'Usuario Sparta')} - ${escapeHtml(dateTime(event.fecha_evento))}
                            </div>
                            <div class="atlas-expedientes-event-meta mt-1">
                                ${escapeHtml(event.cliente_nombre || 'Cliente sin nombre')} - ${escapeHtml(event.sucursal || 'Sin sucursal')}
                                ${event.etapa_credito ? ` - ${escapeHtml(event.etapa_credito)}` : ''}
                            </div>
                        </div>
                        <span class="badge bg-label-secondary">Movimiento #${escapeHtml(event.id)}</span>
                    </div>
                    ${event.motivo ? `<div class="atlas-expedientes-event-text"><strong>Motivo:</strong> ${escapeHtml(event.motivo)}</div>` : ''}
                    ${event.comentario ? `<div class="atlas-expedientes-event-text"><strong>Comentario:</strong> ${escapeHtml(event.comentario)}</div>` : ''}
                    ${safeEvidenceLink(event.evidencia_url)}
                </article>`).join('')
            : '<div class="alert alert-secondary mb-0">El expediente aun no tiene movimientos registrados.</div>';

        const detailContent = document.getElementById('atlasExpedientesDetailContent');
        document.getElementById('atlasExpedientesDetailMeta').textContent =
            `Credito #${expediente.numero_credito || expediente.credito_id || ''}`;
        detailContent.innerHTML = `
            <div class="atlas-expedientes-detail-grid">
                ${detailItem('ID credito', expediente.credito_id)}
                ${detailItem('ID cliente', expediente.cliente_id)}
                ${detailItem('Cliente', expediente.cliente_nombre)}
                ${detailItem('Sucursal', expediente.sucursal)}
                ${detailItem('ID sucursal', expediente.fk_sucursal)}
                ${detailItem('Etapa del credito', expediente.etapa_credito)}
                ${detailItem('Monto a financiar', money(expediente.monto_financiar))}
                ${detailItem('Alta del credito', dateTime(expediente.fecha_credito))}
                ${detailItem('Activacion S2Credit', dateTime(expediente.fecha_activacion_s2))}
                <div class="atlas-expedientes-detail-item">
                    <div class="atlas-expedientes-detail-label">Estatus actual</div>
                    <div class="atlas-expedientes-detail-value">${statusBadge(expediente.estatus)}</div>
                </div>
            </div>
            ${renderEvidenceGallery(data, requestedCreditId)}
            <h3 class="h6 fw-bold mb-3">Bitacora de movimientos</h3>
            <div class="atlas-expedientes-timeline">${events}</div>`;
        bindEvidenceImageFallbacks(detailContent);
    };

    const openDetail = async (creditId) => {
        document.getElementById('atlasExpedientesDetailMeta').textContent = `Credito #${creditId}`;
        document.getElementById('atlasExpedientesDetailContent').innerHTML =
            '<div class="py-5 text-center text-muted fw-semibold"><span class="spinner-border spinner-border-sm me-2"></span>Consultando expediente...</div>';
        detailModal?.show();
        try {
            const response = await fetch(`/Atlas/getExpedienteDetalle?credito_id=${encodeURIComponent(creditId)}`, {
                headers: { Accept: 'application/json' },
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.mensaje || 'No se pudo consultar el expediente.');
            }
            renderDetail(payload.datos || {}, creditId);
        } catch (error) {
            document.getElementById('atlasExpedientesDetailContent').innerHTML =
                `<div class="alert alert-danger mb-0">${escapeHtml(error.message || 'No se pudo consultar el expediente.')}</div>`;
        }
    };

    const uploadLayout = async (event) => {
        event.preventDefault();
        const fileInput = document.getElementById('atlasExpedientesImportFile');
        if (!fileInput.files?.length) {
            fileInput.classList.add('is-invalid');
            return;
        }

        fileInput.classList.remove('is-invalid');
        importSubmitButton.disabled = true;
        const originalContent = importSubmitButton.innerHTML;
        importSubmitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando';
        try {
            const response = await fetch('/Atlas/importarExpedientes', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(importForm),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.mensaje || 'No se pudo procesar el layout.');
            }

            const summary = payload.datos || {};
            if (document.activeElement instanceof HTMLElement) document.activeElement.blur();
            importModal?.hide();
            importForm.reset();
            table?.ajax.reload(null, false);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Layout aplicado',
                    html: `<strong>${number(summary.actualizados)}</strong> actualizado(s) &middot; `
                        + `<strong>${number(summary.sin_cambios)}</strong> sin cambios &middot; `
                        + `<strong>${number(summary.ya_procesados)}</strong> ya procesado(s)`,
                });
            }
        } catch (error) {
            showError(error.message || 'No se pudo procesar el layout.');
        } finally {
            importSubmitButton.disabled = false;
            importSubmitButton.innerHTML = originalContent;
        }
    };

    const reloadFromStart = () => {
        if (!validateDateRange(false) || !table) return;
        table.ajax.reload(null, true);
    };

    const initialize = () => {
        if (!window.jQuery?.fn?.DataTable || !window.jQuery?.fn?.select2) {
            showError('No se pudieron cargar los componentes de tabla y filtros.');
            return;
        }
        [movementModalElement, detailModalElement, importModalElement].forEach((modalElement) => {
            if (modalElement && modalElement.parentElement !== document.body) {
                document.body.appendChild(modalElement);
            }
        });
        movementModal = typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(movementModalElement)
            : null;
        detailModal = typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(detailModalElement)
            : null;
        importModal = typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(importModalElement)
            : null;
        initializeSelects();
        loadDataTable();

        window.jQuery([statusSelect, branchSelect])
            .off('change.atlasExpedientes')
            .on('change.atlasExpedientes', reloadFromStart);
        [startInput, endInput].forEach((input) => input.addEventListener('change', reloadFromStart));
        refreshButton.addEventListener('click', () => {
            if (validateDateRange(true)) table?.ajax.reload(null, false);
        });
        document.getElementById('atlasExpedientesClear').addEventListener('click', () => {
            startInput.value = initialStart;
            endInput.value = initialEnd;
            [statusSelect, branchSelect].forEach((select) => {
                window.jQuery(select).val('').trigger('change.select2');
            });
            table.search('');
            reloadFromStart();
        });
        importOpenButton.disabled = !apiReady;
        importOpenButton.addEventListener('click', () => {
            importForm.reset();
            document.getElementById('atlasExpedientesImportFile').classList.remove('is-invalid');
            importModal?.show();
        });
        importForm.addEventListener('submit', uploadLayout);

        window.jQuery('#atlasExpedientesTable').on('click', '[data-exp-action]', async function () {
            const action = String(this.dataset.expAction || '');
            const creditId = Number(this.dataset.expCredit || 0);
            if (!creditId) return;
            if (action === 'detalle') {
                await openDetail(creditId);
                return;
            }
            const row = currentRow(creditId);
            if (!row) return;
            if (action === 'entregado') {
                await markDelivered(row);
                return;
            }
            openMovement(row, action);
        });

        movementForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const reason = document.getElementById('atlasExpedientesMovementReason').value.trim();
            if (reason.length < 5) {
                movementForm.classList.add('was-validated');
                document.getElementById('atlasExpedientesMovementReason').focus();
                return;
            }
            await postMovement(
                document.getElementById('atlasExpedientesMovementCredit').value,
                document.getElementById('atlasExpedientesMovementAction').value,
                {
                    motivo: reason,
                    comentario: document.getElementById('atlasExpedientesMovementComment').value.trim(),
                    evidencia_url: document.getElementById('atlasExpedientesMovementEvidence').value.trim(),
                }
            );
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
})();
</script>
