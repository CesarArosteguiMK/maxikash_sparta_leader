<?php
$atlasNow = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
$atlasToday = $atlasNow->format('Y-m-d');
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
        .atlas-attendance-filters { display:flex; flex-direction:column; gap:.7rem; }
        .atlas-attendance-filter-primary { display:grid; grid-template-columns:minmax(18rem, .8fr) minmax(36rem, 1.4fr); gap:.7rem; align-items:end; }
        .atlas-attendance-filter-dates { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.7rem; align-items:end; }
        .atlas-attendance-filter-main { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.7rem; align-items:end; }
        .atlas-attendance-filter-secondary { display:grid; grid-template-columns:repeat(3, minmax(12rem, 1fr)) minmax(11rem, max-content); gap:.7rem; align-items:end; max-width:72rem; }
        .atlas-attendance-filter-actions { display:flex; align-items:center; }
        .atlas-attendance-filter-actions .btn { min-height:2.35rem; width:100%; }
        .atlas-attendance-table-panel { border:1px solid #dbe4ef; border-radius:.5rem; background:#fff; overflow:hidden; }
        .atlas-attendance-table-head { display:flex; align-items:center; justify-content:space-between; gap:.8rem; padding:.75rem .9rem; border-bottom:1px solid #e5e7eb; }
        .atlas-attendance-table-title { margin:0; color:#173756; font-size:.9rem; font-weight:900; }
        .atlas-attendance-table-meta { color:#64748b; font-size:.75rem; font-weight:800; }
        .atlas-attendance-scroll { overflow-x:auto; }
        .atlas-attendance-table { min-width:880px; margin:0; table-layout:fixed; }
        .atlas-attendance-table th { border:0; border-bottom:1px solid #eef2f7; background:#fff; color:#64748b; font-size:.7rem; font-weight:900; letter-spacing:0; padding:.9rem 1rem; text-transform:uppercase; white-space:nowrap; }
        .atlas-attendance-table td { border:0; border-bottom:1px solid #eef2f7; color:#566a7f; font-size:.78rem; font-weight:700; padding:1rem; vertical-align:middle; }
        .atlas-attendance-table tbody tr:hover { background:#f8fafc; }
        .atlas-attendance-table th:nth-child(1) { width:17%; }
        .atlas-attendance-table th:nth-child(2) { width:28%; }
        .atlas-attendance-table th:nth-child(3) { width:20%; }
        .atlas-attendance-table th:nth-child(4) { width:25%; }
        .atlas-attendance-table th:nth-child(5) { width:10%; }
        .atlas-attendance-main { color:#22303e; font-weight:900; line-height:1.18; }
        .atlas-attendance-sub { color:#94a3b8; font-size:.68rem; font-weight:800; line-height:1.18; margin-top:.12rem; }
        .atlas-attendance-cell { display:flex; align-items:flex-start; gap:.65rem; min-width:0; }
        .atlas-attendance-cell-content { min-width:0; }
        .atlas-attendance-cell-icon { flex:0 0 1.9rem; width:1.9rem; height:1.9rem; display:grid; place-items:center; border-radius:.4rem; font-size:.78rem; }
        .atlas-attendance-cell-icon.is-date { background:#eff6ff; color:#2563eb; }
        .atlas-attendance-cell-icon.is-collaborator { background:#ecfdf5; color:#0f766e; }
        .atlas-attendance-cell-icon.is-visits { background:#f0fdf4; color:#16a34a; }
        .atlas-agency-visits-cell { min-width:0; }
        .atlas-agency-visits-summary { color:#64748b; font-size:.68rem; font-weight:800; line-height:1.2; margin-top:.15rem; }
        .atlas-attendance-branch-summary { display:grid; gap:.22rem; min-width:0; }
        .atlas-attendance-branch-line { display:flex; align-items:baseline; justify-content:space-between; gap:.65rem; color:#64748b; font-size:.68rem; font-weight:800; line-height:1.2; }
        .atlas-attendance-branch-line strong { color:#22303e; font-size:.78rem; font-variant-numeric:tabular-nums; }
        .atlas-attendance-branch-line.is-missed strong { color:#b45309; }
        .atlas-attendance-badge { display:inline-flex; align-items:center; gap:.3rem; border-radius:999px; padding:.24rem .58rem; font-size:.68rem; font-weight:900; white-space:nowrap; }
        .atlas-attendance-status-cumplida { background:#dcfce7; color:#15803d; }
        .atlas-attendance-status-no-realizada { background:#fee2e2; color:#b91c1c; }
        .atlas-attendance-status-fuera { background:#ffedd5; color:#c2410c; }
        .atlas-attendance-status-mixta { border:1px solid #bae6fd; background:linear-gradient(90deg, #dbeafe 0%, #ccfbf1 100%); color:#0f5d78; }
        .atlas-attendance-status-en-visita { background:#dbeafe; color:#1d4ed8; }
        .atlas-attendance-status-gestion-sin-visita { background:#f1f5f9; color:#64748b; }
        .atlas-attendance-status-programada { background:#f1f5f9; color:#475569; }
        .atlas-attendance-empty { padding:2.5rem 1rem !important; text-align:center; color:#64748b !important; }
        .atlas-attendance-detail-button { display:inline-grid; place-items:center; width:2.2rem; height:2.2rem; padding:0; border:1px solid #bfdbfe; border-radius:.4rem; background:#eff6ff; color:#1d4ed8; }
        .atlas-attendance-detail-button:hover { background:#dbeafe; color:#1e40af; }
        .atlas-attendance-detail-button:focus-visible { outline:2px solid #2563eb; outline-offset:2px; }
        .atlas-evidence-modal-meta { color:#64748b; font-size:.78rem; font-weight:700; }
        .atlas-attendance-detail-summary { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:.75rem; margin-bottom:1.1rem; }
        .atlas-attendance-detail-stat { --atlas-stat-accent:#2563eb; --atlas-stat-soft:#eff6ff; --atlas-stat-border:#bfdbfe; position:relative; min-width:0; min-height:6.5rem; overflow:hidden; padding:.8rem .9rem .75rem 1rem; border:1px solid var(--atlas-stat-border); border-radius:.5rem; background:#fff; box-shadow:0 2px 8px rgba(15, 23, 42, .05); }
        .atlas-attendance-detail-stat::before { content:""; position:absolute; inset:0 auto 0 0; width:4px; background:var(--atlas-stat-accent); }
        .atlas-attendance-detail-stat.is-teal { --atlas-stat-accent:#0f8f83; --atlas-stat-soft:#ecfdf5; --atlas-stat-border:#a7e3dc; }
        .atlas-attendance-detail-stat.is-blue { --atlas-stat-accent:#2563eb; --atlas-stat-soft:#eff6ff; --atlas-stat-border:#bfdbfe; }
        .atlas-attendance-detail-stat.is-indigo { --atlas-stat-accent:#4f46e5; --atlas-stat-soft:#eef2ff; --atlas-stat-border:#c7d2fe; }
        .atlas-attendance-detail-stat.is-rose { --atlas-stat-accent:#e11d48; --atlas-stat-soft:#fff1f2; --atlas-stat-border:#fecdd3; }
        .atlas-attendance-detail-stat-label { display:flex; align-items:center; gap:.45rem; color:#64748b; font-size:.68rem; font-weight:900; text-transform:none; }
        .atlas-attendance-detail-stat-icon { flex:0 0 1.65rem; width:1.65rem; height:1.65rem; display:grid; place-items:center; border-radius:.38rem; background:var(--atlas-stat-soft); color:var(--atlas-stat-accent); font-size:.72rem; }
        .atlas-attendance-detail-stat-value { margin:.35rem 0 0 2.1rem; color:var(--atlas-stat-accent); font-size:1.35rem; font-weight:900; line-height:1.05; }
        .atlas-attendance-detail-stat-sub { margin:.28rem 0 0 2.1rem; color:#64748b; font-size:.68rem; font-weight:800; line-height:1.25; }
        .atlas-attendance-coverage { margin-bottom:1.1rem; padding:1rem; border:1px solid #cfe3f7; border-radius:.5rem; background:#fff; box-shadow:0 2px 8px rgba(15, 23, 42, .05); }
        .atlas-attendance-coverage-head { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:.85rem; }
        .atlas-attendance-coverage-heading { display:flex; align-items:center; gap:.7rem; min-width:0; }
        .atlas-attendance-coverage-icon { flex:0 0 2.25rem; width:2.25rem; height:2.25rem; display:grid; place-items:center; border-radius:.5rem; background:#e8f1ff; color:#2563eb; }
        .atlas-attendance-coverage-title { margin:0; color:#22303e; font-size:.9rem; font-weight:900; }
        .atlas-attendance-coverage-period { margin-top:.1rem; color:#64748b; font-size:.68rem; font-weight:750; }
        .atlas-attendance-coverage-month { width:min(13rem, 100%); }
        .atlas-attendance-coverage-main { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-bottom:.45rem; }
        .atlas-attendance-coverage-label { color:#64748b; font-size:.7rem; font-weight:850; }
        .atlas-attendance-coverage-value { color:#1d4ed8; font-size:1.55rem; font-weight:900; line-height:1; font-variant-numeric:tabular-nums; }
        .atlas-attendance-coverage-track { height:.55rem; overflow:hidden; border-radius:999px; background:#e2e8f0; }
        .atlas-attendance-coverage-fill { height:100%; border-radius:inherit; background:#2563eb; transition:width .2s ease; }
        .atlas-attendance-coverage-stats { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.65rem; }
        .atlas-attendance-coverage-stat { min-height:4rem; padding:.65rem .75rem; border:1px solid #e2e8f0; border-radius:.45rem; background:#f8fafc; }
        .atlas-attendance-coverage-stat.is-overdue { border-color:#fecaca; background:#fff7f7; }
        .atlas-attendance-coverage-stat-value { color:#1d4ed8; font-size:1.05rem; font-weight:900; line-height:1.1; font-variant-numeric:tabular-nums; }
        .atlas-attendance-coverage-stat.is-overdue .atlas-attendance-coverage-stat-value { color:#dc2626; }
        .atlas-attendance-coverage-stat-label { margin-top:.28rem; color:#64748b; font-size:.7rem; font-weight:750; }
        .atlas-attendance-coverage-loading { min-height:8.1rem; display:grid; place-items:center; color:#64748b; font-size:.78rem; font-weight:750; }
        .atlas-attendance-detail-section { margin-top:1.1rem; padding-top:1rem; border-top:1px solid #e2e8f0; }
        .atlas-attendance-detail-section.is-first { margin-top:0; padding-top:0; border-top:0; }
        .atlas-attendance-detail-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; flex-wrap:wrap; margin-bottom:.65rem; }
        .atlas-attendance-detail-title { margin:0; color:#22303e; font-size:.9rem; font-weight:900; }
        .atlas-attendance-detail-meta { color:#64748b; font-size:.72rem; font-weight:800; }
        .atlas-attendance-detail-tools { display:flex; align-items:flex-end; justify-content:flex-end; gap:.65rem; flex-wrap:wrap; }
        .atlas-attendance-detail-filter { min-width:12rem; }
        .atlas-attendance-detail-filter .form-label { color:#64748b; font-size:.65rem; font-weight:850; }
        .atlas-attendance-detail-table-wrap { border:1px solid #dbe4ef; border-radius:.45rem; overflow:auto; background:#fff; }
        .atlas-attendance-detail-table { min-width:1580px; margin:0; font-size:.75rem; }
        .atlas-attendance-detail-table thead th { border:0; border-bottom:1px solid #dbe4ef; background:#eef4fb; color:#566a7f; font-size:.65rem; font-weight:900; letter-spacing:0; padding:.65rem .75rem; text-transform:uppercase; white-space:nowrap; }
        .atlas-attendance-detail-table tbody td { border-color:#edf2f7; color:#566a7f; font-weight:750; padding:.65rem .75rem; vertical-align:middle; }
        .atlas-attendance-detail-table tbody tr:hover { background:#f8fafc; }
        .atlas-attendance-credit-metric { min-width:7.6rem; display:grid; justify-items:end; gap:.3rem; }
        .atlas-attendance-credit-value { color:#22303e; font-size:.92rem; font-weight:900; line-height:1; font-variant-numeric:tabular-nums; }
        .atlas-attendance-credit-tags { display:flex; justify-content:flex-end; flex-wrap:wrap; gap:.2rem; }
        .atlas-attendance-credit-tag { display:inline-flex; align-items:center; gap:.2rem; padding:.18rem .38rem; border-radius:.35rem; background:#f1f5f9; color:#64748b; font-size:.61rem; font-weight:850; line-height:1.15; white-space:nowrap; }
        .atlas-attendance-credit-tag.is-managed { background:#eef2ff; color:#4f46e5; }
        .atlas-attendance-credit-tag.is-overdue { background:#edf2f7; color:#475569; }
        .atlas-attendance-credit-tag.is-sold { background:#ecfdf5; color:#047857; }
        .atlas-attendance-credit-tag.is-visit { background:#ecfeff; color:#0f766e; }
        .atlas-attendance-credit-loading { min-width:7.6rem; color:#64748b; font-size:.68rem; font-weight:800; white-space:nowrap; }
        .atlas-attendance-credit-unavailable { min-width:7.6rem; color:#94a3b8; font-size:.66rem; font-weight:800; white-space:nowrap; }
        .atlas-attendance-image-action { min-width:6.8rem; white-space:nowrap; }
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
        .atlas-evidence-routes { margin-top:1.1rem; padding-top:1rem; border-top:1px solid #e2e8f0; }
        .atlas-evidence-routes-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.75rem; flex-wrap:wrap; }
        .atlas-evidence-routes-title { margin:0; color:#22303e; font-size:.9rem; font-weight:900; }
        .atlas-evidence-routes-meta { color:#64748b; font-size:.72rem; font-weight:800; }
        .atlas-evidence-route-list { display:grid; gap:.65rem; }
        .atlas-evidence-route-item { border:1px solid #dbe4ef; border-radius:.45rem; background:#f8fafc; padding:.7rem .8rem; }
        .atlas-evidence-route-head { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; }
        .atlas-evidence-route-name { color:#22303e; font-size:.8rem; font-weight:900; line-height:1.25; }
        .atlas-evidence-route-badge { display:inline-flex; align-items:center; border-radius:999px; padding:.17rem .5rem; background:#e0f2fe; color:#0369a1; font-size:.65rem; font-weight:900; white-space:nowrap; }
        .atlas-evidence-route-detail { margin-top:.25rem; color:#64748b; font-size:.7rem; font-weight:700; }
        .atlas-evidence-route-actions { display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; margin-top:.55rem; }
        .atlas-evidence-route-map-state { margin-top:.65rem; }
        .atlas-evidence-route-map-media { width:100%; min-height:13rem; aspect-ratio:640/430; border:1px solid #dbe4ef; border-radius:.45rem; overflow:hidden; background:#e2e8f0; display:grid; place-items:center; }
        .atlas-evidence-route-map-media img { width:100%; height:100%; object-fit:contain; display:block; }
        .atlas-evidence-route-map-footer { margin-top:.45rem; display:flex; justify-content:flex-end; }
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
            .atlas-attendance-filter-primary { grid-template-columns:1fr; }
            .atlas-attendance-filter-secondary { max-width:none; }
        }
        @media (max-width: 991.98px) {
            .atlas-attendance-detail-summary { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .atlas-attendance-coverage-stats { grid-template-columns:repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767.98px) {
            .atlas-attendance-filter-dates,
            .atlas-attendance-filter-main,
            .atlas-attendance-filter-secondary { grid-template-columns:1fr; }
            .atlas-attendance-actions,
            .atlas-attendance-actions .btn,
            .atlas-attendance-filter-actions,
            .atlas-attendance-filter-actions .btn { width:100%; }
            .atlas-attendance-filter-actions .btn { flex:1; }
            .atlas-evidence-grid { grid-template-columns:1fr; }
            .atlas-attendance-coverage-month,
            .atlas-attendance-detail-tools,
            .atlas-attendance-detail-filter { width:100%; }
            .atlas-attendance-detail-tools { justify-content:stretch; }
        }
        @media (max-width: 575.98px) {
            .atlas-attendance-detail-summary { grid-template-columns:1fr; }
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
            <span>El servicio para reportes se encuentra en mantenimiento, en breves desplegamos y podras volver a utilizarlo con normalidad..</span>
        </div>
    <?php endif; ?>

    <section class="atlas-attendance-filter-panel" aria-label="Filtros de asistencias">
        <div class="atlas-attendance-filters">
            <div class="atlas-attendance-filter-primary">
                <div class="atlas-attendance-filter-dates">
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
                </div>
                <div class="atlas-attendance-filter-main">
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
                </div>
            </div>
            <div class="atlas-attendance-filter-secondary">
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
                        <option value="incompletas">Incompletas</option>
                        <option value="sin">Sin evidencias</option>
                    </select>
                </div>
                <div>
                    <label class="form-label mb-1" for="atlasAttendanceRoutes">Rutas</label>
                    <select class="form-select form-select-sm atlas-attendance-select" id="atlasAttendanceRoutes">
                        <option value="">Todas</option>
                        <option value="con">Con rutas</option>
                        <option value="sin">Sin rutas</option>
                    </select>
                </div>
                <div class="atlas-attendance-filter-actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="atlasAttendanceClear" title="Eliminar filtros">
                        <i class="fa-solid fa-filter-circle-xmark me-2"></i>Eliminar filtros
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="atlas-attendance-table-panel">
        <div class="atlas-attendance-table-head">
            <h2 class="atlas-attendance-table-title">Colaboradores</h2>
            <span class="atlas-attendance-table-meta" id="atlasAttendanceGenerated">Sin consultar</span>
        </div>
        <div class="atlas-attendance-scroll">
            <table id="atlasAttendanceTable" class="table table-hover atlas-attendance-table w-100">
                <thead>
                    <tr>
                        <th title="Fecha más reciente con actividad dentro del periodo filtrado">Fecha</th>
                        <th>Colaborador</th>
                        <th title="Resumen calculado con los estados de sus visitas dentro del periodo">Estatus del colaborador</th>
                        <th>Sucursales</th>
                        <th class="text-center">Detalles</th>
                    </tr>
                </thead>
                <tbody id="atlasAttendanceBody"></tbody>
            </table>
        </div>
    </section>
</div>

<div class="modal fade" id="atlasAttendanceDownloadModal" tabindex="-1"
     aria-labelledby="atlasAttendanceDownloadTitle" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <form class="modal-content" id="atlasAttendanceDownloadForm">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5" id="atlasAttendanceDownloadTitle">
                        <i class="fa-solid fa-file-excel me-2 text-success"></i>Descargar asistencias
                    </h2>
                    <div class="atlas-evidence-modal-meta">El archivo se agrupará por colaborador.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold" for="atlasAttendanceDownloadStart">Fecha inicial</label>
                        <input class="form-control" type="date" id="atlasAttendanceDownloadStart"
                               min="<?= htmlspecialchars($atlasDatasetStart, ENT_QUOTES, 'UTF-8') ?>"
                               max="<?= htmlspecialchars($atlasDatasetEnd, ENT_QUOTES, 'UTF-8') ?>"
                               required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label fw-bold" for="atlasAttendanceDownloadEnd">Fecha final</label>
                        <input class="form-control" type="date" id="atlasAttendanceDownloadEnd"
                               min="<?= htmlspecialchars($atlasDatasetStart, ENT_QUOTES, 'UTF-8') ?>"
                               max="<?= htmlspecialchars($atlasDatasetEnd, ENT_QUOTES, 'UTF-8') ?>"
                               required>
                    </div>
                    <div class="col-12">
                        <div class="alert alert-info mb-0 py-2" role="status">
                            Un día cuenta como asistencia cuando existe al menos un check-in. Los días sin asistencia son días con visitas programadas sin ningún check-in.
                        </div>
                        <div class="invalid-feedback d-block mt-2 d-none" id="atlasAttendanceDownloadError">
                            Selecciona un rango de fechas válido.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-end">
                <button type="submit" class="btn btn-success" id="atlasAttendanceDownloadConfirm">
                    <i class="fa-solid fa-download me-2"></i>Descargar archivo
                </button>
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="atlasAttendanceEvidenceModal" tabindex="-1" aria-labelledby="atlasAttendanceEvidenceTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title fs-5" id="atlasAttendanceEvidenceTitle">
                        <i class="fa-solid fa-clipboard-user me-2 text-primary"></i>Detalle de asistencias
                    </h2>
                    <div class="atlas-evidence-modal-meta" id="atlasAttendanceEvidenceMeta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="atlas-attendance-detail-summary" id="atlasAttendanceDetailSummary"></div>

                <section class="atlas-attendance-coverage" aria-label="Cobertura mensual de sucursales">
                    <div class="atlas-attendance-coverage-head">
                        <div class="atlas-attendance-coverage-heading">
                            <span class="atlas-attendance-coverage-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <div>
                                <h3 class="atlas-attendance-coverage-title">Cobertura de sucursales</h3>
                                <div class="atlas-attendance-coverage-period" id="atlasAttendanceCoveragePeriod"></div>
                            </div>
                        </div>
                        <select class="form-select form-select-sm atlas-attendance-coverage-month"
                                id="atlasAttendanceCoverageMonth"
                                aria-label="Mes de cobertura"></select>
                    </div>
                    <div id="atlasAttendanceCoverageState"></div>
                </section>

                <section class="atlas-attendance-detail-section is-first" aria-label="Actividad registrada">
                    <div class="atlas-attendance-detail-head">
                        <h3 class="atlas-attendance-detail-title">
                            <i class="fa-solid fa-location-dot me-2 text-primary"></i>Actividad registrada
                        </h3>
                        <div class="atlas-attendance-detail-tools">
                            <div class="atlas-attendance-detail-filter">
                                <label class="form-label mb-1" for="atlasAttendanceDetailStatus">Estatus</label>
                                <select class="form-select form-select-sm" id="atlasAttendanceDetailStatus">
                                    <option value="">Todos los estatus</option>
                                </select>
                            </div>
                            <span class="atlas-attendance-detail-meta" id="atlasAttendanceDetailMeta"></span>
                        </div>
                    </div>
                    <div id="atlasAttendanceCreditMetricsNotice"></div>
                    <div id="atlasAttendanceDetailState"></div>
                </section>

                <section class="atlas-attendance-detail-section" aria-label="Evidencias">
                    <div class="atlas-attendance-detail-head">
                        <h3 class="atlas-attendance-detail-title">
                            <i class="fa-solid fa-images me-2 text-primary"></i>Evidencias
                        </h3>
                        <span class="atlas-attendance-detail-meta" id="atlasAttendanceEvidenceSectionMeta"></span>
                    </div>
                    <div class="atlas-evidence-grid" id="atlasAttendanceEvidenceGrid"></div>
                </section>

                <section class="atlas-evidence-routes" aria-label="Rutas Spartan del usuario seleccionado">
                    <div class="atlas-evidence-routes-head">
                        <h3 class="atlas-evidence-routes-title">
                            <i class="fa-solid fa-route me-2 text-primary"></i>Rutas generadas por el usuario
                        </h3>
                        <span class="atlas-evidence-routes-meta" id="atlasAttendanceRoutesMeta"></span>
                    </div>
                    <div id="atlasAttendanceRoutesState"></div>
                </section>
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
    const today = <?= json_encode($atlasToday, JSON_UNESCAPED_SLASHES) ?>;
    const startInput = document.getElementById('atlasAttendanceStart');
    const endInput = document.getElementById('atlasAttendanceEnd');
    const collaboratorSelect = document.getElementById('atlasAttendanceCollaborator');
    const distributorSelect = document.getElementById('atlasAttendanceDistributor');
    const statusSelect = document.getElementById('atlasAttendanceStatus');
    const divisionalSelect = document.getElementById('atlasAttendanceDivisional');
    const evidenceSelect = document.getElementById('atlasAttendanceEvidence');
    const routeSelect = document.getElementById('atlasAttendanceRoutes');
    const body = document.getElementById('atlasAttendanceBody');
    const downloadButton = document.getElementById('atlasAttendanceDownload');
    const downloadModalElement = document.getElementById('atlasAttendanceDownloadModal');
    const downloadForm = document.getElementById('atlasAttendanceDownloadForm');
    const downloadStartInput = document.getElementById('atlasAttendanceDownloadStart');
    const downloadEndInput = document.getElementById('atlasAttendanceDownloadEnd');
    const downloadError = document.getElementById('atlasAttendanceDownloadError');
    const downloadConfirmButton = document.getElementById('atlasAttendanceDownloadConfirm');
    const evidenceModalElement = document.getElementById('atlasAttendanceEvidenceModal');
    const evidenceGrid = document.getElementById('atlasAttendanceEvidenceGrid');
    const evidenceMeta = document.getElementById('atlasAttendanceEvidenceMeta');
    const detailSummary = document.getElementById('atlasAttendanceDetailSummary');
    const detailMeta = document.getElementById('atlasAttendanceDetailMeta');
    const creditMetricsNotice = document.getElementById('atlasAttendanceCreditMetricsNotice');
    const detailState = document.getElementById('atlasAttendanceDetailState');
    const detailStatusSelect = document.getElementById('atlasAttendanceDetailStatus');
    const coverageMonthSelect = document.getElementById('atlasAttendanceCoverageMonth');
    const coveragePeriod = document.getElementById('atlasAttendanceCoveragePeriod');
    const coverageState = document.getElementById('atlasAttendanceCoverageState');
    const evidenceSectionMeta = document.getElementById('atlasAttendanceEvidenceSectionMeta');
    const routesMeta = document.getElementById('atlasAttendanceRoutesMeta');
    const routesState = document.getElementById('atlasAttendanceRoutesState');
    let downloadModal = null;
    let evidenceModal = null;
    let visibleRows = [];
    let visibleGroups = [];
    let attendanceTable = null;
    let generatedAt = '';
    let perimeterMeters = null;
    let loading = false;
    let loadingAlertOpen = false;
    let activeDetailGroup = null;
    let coverageAbortController = null;
    let creditMetricsAbortController = null;
    let activeCreditMetrics = new Map();
    let creditMetricsLoading = false;
    let creditMetricsLoaded = false;
    let creditMetricsError = '';
    let lastDetailTrigger = null;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const number = (value) => Number(value || 0).toLocaleString('es-MX');
    const showAttendanceLoading = () => {
        if (typeof Swal === 'undefined') return;
        loadingAlertOpen = true;
        Swal.fire({
            title: 'Cargando asistencias...',
            html: '<span style="font-size:.875rem;color:#64748b;">Consultando visitas y gestiones registradas</span>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading(),
        });
    };
    const hideAttendanceLoading = () => {
        if (!loadingAlertOpen || typeof Swal === 'undefined') return;
        loadingAlertOpen = false;
        if (Swal.isVisible()) Swal.close();
    };
    const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
    const isTransientNetworkError = (error) => {
        const message = String(error?.message || error || '').toLowerCase();
        return error instanceof TypeError
            || message.includes('failed to fetch')
            || message.includes('network')
            || message.includes('load failed')
            || message.includes('err_network_changed');
    };
    const fetchJsonWithNetworkRetry = async (url, options = {}, retries = 2) => {
        let lastError = null;
        for (let attempt = 0; attempt <= retries; attempt++) {
            try {
                const response = await fetch(url, {
                    cache: 'no-store',
                    ...options,
                });
                const text = await response.text();
                let payload = null;
                if (text.trim() !== '') {
                    try {
                        payload = JSON.parse(text);
                    } catch (parseError) {
                        payload = null;
                    }
                }
                return { response, payload };
            } catch (error) {
                lastError = error;
                if (attempt >= retries || !isTransientNetworkError(error)) {
                    throw error;
                }
                await wait(600 * (attempt + 1));
            }
        }
        throw lastError;
    };
    const formatBytes = (value) => {
        const bytes = Number(value || 0);
        if (!bytes) return 'Tamaño sin dato';
        if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
        return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
    };

    const agencyVisitKey = (row, index = -1) => {
        const fk = String(row?.fk_sucursal ?? '').trim();
        if (fk) return `fk_sucursal:${fk}`;
        const routeBranch = String(row?.ruta_sucursal_id ?? '').trim();
        if (routeBranch) return `ruta_sucursal_id:${routeBranch}`;
        const agency = String(row?.agencia ?? '').trim().toLowerCase();
        const distributor = String(row?.distribuidor ?? '').trim().toLowerCase();
        const named = [agency, distributor].filter(Boolean).join('|');
        return named ? `agencia:${named}` : `fila:${index}`;
    };

    const collaboratorKey = (row) => {
        const personaId = String(row?.colaborador_persona_id ?? row?.gestor_persona_id ?? '').trim();
        if (personaId) return `persona:${personaId}`;
        const name = String(row?.colaborador || '').trim().toLocaleLowerCase('es-MX');
        return name ? `nombre:${name}` : 'sin-asignar';
    };

    const visitHourEntry = (row) => {
        const date = String(row?.fecha || '').trim();
        const start = String(row?.hora_llegada || row?.hora_confirmacion_llegada || row?.hora_gestion || '').trim();
        const end = String(row?.hora_salida || row?.hora_termino_visita || '').trim();
        const parts = [];
        if (date) parts.push(date);
        if (start && end) parts.push(`${start}-${end}`);
        else if (start) parts.push(start);
        else if (end) parts.push(`Salida ${end}`);
        else parts.push('Hora no disponible');
        return {
            date,
            start,
            end,
            label: parts.join(' '),
            hasTime: Boolean(start || end),
        };
    };

    const rowEvidenceCount = (row) => {
        const evidences = Array.isArray(row?.evidencias) ? row.evidencias : [];
        return Math.max(Math.max(0, Number(row?.total_evidencias) || 0), evidences.length);
    };

    const rowImageEvidence = (row) => {
        const availableValues = [true, 1, '1', 'true'];
        return (Array.isArray(row?.evidencias) ? row.evidencias : []).find((evidence) => {
            if (!evidence || !evidence.id || !availableValues.includes(evidence.disponible)) return false;
            const mimeType = String(evidence.mime_type || '').trim().toLowerCase();
            const kind = String(evidence.tipo || '').trim().toLowerCase();
            const name = String(evidence.nombre || '').trim().toLowerCase();
            return mimeType.startsWith('image/')
                || ['imagen', 'foto', 'fotografia'].includes(kind)
                || /\.(jpe?g|png|webp|gif|heic|heif)$/.test(name);
        }) || null;
    };

    const rowImageActionHtml = (row) => {
        const image = rowImageEvidence(row);
        if (!image) {
            return `<button type="button" class="btn btn-sm btn-label-secondary atlas-attendance-image-action" disabled>
                <i class="fa-regular fa-image me-1"></i>Sin imagen
            </button>`;
        }
        const source = `/Atlas/verEvidenciaAsistencia?id=${encodeURIComponent(image.id)}`;
        return `<a class="btn btn-sm btn-label-primary atlas-attendance-image-action"
                   href="${escapeHtml(source)}" target="_blank" rel="noopener"
                   title="Abrir fotografía de la visita">
            <i class="fa-solid fa-image me-1"></i>Ver imagen
        </a>`;
    };

    const collaboratorGroups = (rows) => {
        const groups = new Map();
        rows.forEach((row) => {
            const key = collaboratorKey(row);
            const group = groups.get(key) || { key, rows: [] };
            group.rows.push(row);
            groups.set(key, group);
        });

        return [...groups.values()].map((group) => {
            group.rows.sort((left, right) => {
                const leftKey = `${left?.fecha || ''} ${left?.hora_llegada || left?.hora_gestion || ''}`;
                const rightKey = `${right?.fecha || ''} ${right?.hora_llegada || right?.hora_gestion || ''}`;
                return rightKey.localeCompare(leftKey, 'es');
            });
            const representative = group.rows[0] || {};
            const dates = [...new Set(group.rows.map((row) => String(row?.fecha || '').trim()).filter(Boolean))]
                .sort((left, right) => right.localeCompare(left, 'es'));
            return {
                ...group,
                representative,
                personaId: representative.colaborador_persona_id ?? representative.gestor_persona_id ?? '',
                name: representative.colaborador || 'Sin asignar',
                position: representative.puesto || representative.rol || '',
                divisional: representative.divisional || '',
                dates,
                latestDate: dates[0] || '',
                earliestDate: dates[dates.length - 1] || '',
            };
        }).sort((left, right) => (
            right.latestDate.localeCompare(left.latestDate, 'es')
            || left.name.localeCompare(right.name, 'es')
        ));
    };

    const groupVisitStats = (group) => {
        const visitRows = (group?.rows || []).filter((row) => row?.es_visita !== false);
        const entries = visitRows.map(visitHourEntry);
        return {
            visitRows,
            total: visitRows.length,
            withTime: entries.filter((entry) => entry.hasTime).length,
            withoutTime: entries.filter((entry) => !entry.hasTime).length,
        };
    };

    const rowHasCheckIn = (row) => (
        row?.es_visita !== false
        && [row?.hora_llegada, row?.hora_confirmacion_llegada, row?.checkin_at]
            .some((value) => String(value ?? '').trim() !== '')
    );

    const groupBranchStats = (group) => {
        const scheduled = new Set();
        const visited = new Set();
        const scheduledWithoutCheckIn = new Set();
        (group?.rows || []).forEach((row, index) => {
            if (row?.es_visita === false) return;
            const branchKey = agencyVisitKey(row, index);
            const visitDate = String(row?.fecha || '').trim();
            scheduled.add(branchKey);
            if (rowHasCheckIn(row)) {
                visited.add(branchKey);
                return;
            }
            if (visitDate && visitDate < today) {
                scheduledWithoutCheckIn.add(branchKey);
            }
        });

        return {
            visited: visited.size,
            missing: [...scheduled].filter((branchKey) => !visited.has(branchKey)).length,
            scheduledWithoutCheckIn: scheduledWithoutCheckIn.size,
        };
    };

    const normalizedStatus = (value) => String(value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .trim()
        .toLowerCase();

    const statusIconClass = (value) => {
        const status = normalizedStatus(value);
        if (status === 'en visita') return 'fa-location-dot';
        if (status === 'cumplida' || status === 'con actividad') return 'fa-circle-check';
        if (status === 'actividad mixta') return 'fa-shuffle';
        if (status === 'no realizada' || status === 'fuera de ubicacion' || status === 'requiere revision') {
            return 'fa-triangle-exclamation';
        }
        if (status === 'gestion sin visita') return 'fa-clipboard-check';
        if (status === 'programada' || status === 'programado') return 'fa-clock';
        return 'fa-circle-minus';
    };

    const collaboratorActivityStatus = (group) => {
        const statuses = (group?.rows || []).map((row) => normalizedStatus(row?.estatus_visita)).filter(Boolean);
        const has = (...values) => statuses.some((status) => values.includes(status));
        const counts = new Map();
        (group?.rows || []).forEach((row) => {
            const label = String(row?.estatus_visita || 'Sin estatus').trim();
            counts.set(label, (counts.get(label) || 0) + 1);
        });
        const title = [...counts.entries()].map(([label, count]) => `${count} ${label}`).join(' | ');
        if (has('en visita')) return { label: 'En visita', className: 'atlas-attendance-status-en-visita', iconClass: 'fa-location-dot', title };
        if (has('cumplida') && has('no realizada', 'fuera de ubicacion')) {
            return { label: 'Actividad mixta', className: 'atlas-attendance-status-mixta', iconClass: 'fa-shuffle', title };
        }
        if (has('cumplida')) return { label: 'Con actividad', className: 'atlas-attendance-status-cumplida', iconClass: 'fa-circle-check', title };
        if (has('no realizada', 'fuera de ubicacion')) {
            return { label: 'Requiere revisión', className: 'atlas-attendance-status-fuera', iconClass: 'fa-triangle-exclamation', title };
        }
        if (has('gestion sin visita')) {
            return { label: 'Gestión sin visita', className: 'atlas-attendance-status-gestion-sin-visita', iconClass: 'fa-clipboard-check', title };
        }
        if (has('programada')) return { label: 'Programado', className: 'atlas-attendance-status-programada', iconClass: 'fa-clock', title };
        return { label: 'Sin visitas', className: 'atlas-attendance-status-programada', iconClass: 'fa-circle-minus', title };
    };

    const groupPeriodText = (group) => {
        if (!group?.latestDate) return 'Sin fecha registrada';
        if (!group.earliestDate || group.earliestDate === group.latestDate) return group.latestDate;
        return `${group.earliestDate} a ${group.latestDate}`;
    };

    const coverageMonthLabel = (month) => {
        const match = String(month || '').match(/^(\d{4})-(\d{2})$/);
        if (!match) return String(month || 'Mes sin identificar');
        const date = new Date(Date.UTC(Number(match[1]), Number(match[2]) - 1, 1));
        const label = new Intl.DateTimeFormat('es-MX', {
            month: 'long',
            timeZone: 'UTC',
            year: 'numeric',
        }).format(date);
        return label.charAt(0).toUpperCase() + label.slice(1);
    };

    const setupDetailStatusFilter = (group) => {
        const statuses = [...new Set((group?.rows || [])
            .map((row) => String(row?.estatus_visita || '').trim())
            .filter(Boolean))]
            .sort((left, right) => left.localeCompare(right, 'es'));
        detailStatusSelect.innerHTML = '<option value="">Todos los estatus</option>' + statuses
            .map((status) => `<option value="${escapeHtml(status)}">${escapeHtml(status)}</option>`)
            .join('');
        detailStatusSelect.value = '';
    };

    const creditMetricKey = (branchId, month) => {
        const branch = String(branchId ?? '').trim();
        const period = String(month ?? '').trim();
        return branch && /^\d{4}-\d{2}$/.test(period) ? `${branch}|${period}` : '';
    };

    const creditMetricForRow = (row) => {
        const branchId = row?.fk_sucursal ?? row?.ruta_sucursal_id;
        const month = String(row?.fecha || '').slice(0, 7);
        const key = creditMetricKey(branchId, month);
        return key ? activeCreditMetrics.get(key) || null : null;
    };

    const renderCreditMetricsNotice = () => {
        if (creditMetricsError) {
            creditMetricsNotice.innerHTML = `<div class="alert alert-info py-2 mb-3">
                <i class="fa-solid fa-circle-info me-2"></i>${escapeHtml(creditMetricsError)}
            </div>`;
            return;
        }
        if (creditMetricsLoaded && activeCreditMetrics.size === 0) {
            creditMetricsNotice.innerHTML = `<div class="alert alert-info py-2 mb-3">
                <i class="fa-solid fa-circle-info me-2"></i>No hay un resumen de créditos disponible para las sucursales de este periodo.
            </div>`;
            return;
        }
        creditMetricsNotice.innerHTML = '';
    };

    const creditMetricCellsHtml = (row) => {
        if (creditMetricsLoading) {
            const loadingCell = `<td class="text-end"><span class="atlas-attendance-credit-loading">
                <span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Consultando
            </span></td>`;
            return loadingCell.repeat(3);
        }

        const metric = creditMetricForRow(row);
        if (!metric) {
            const unavailableCell = '<td class="text-end"><span class="atlas-attendance-credit-unavailable">No disponible</span></td>';
            return unavailableCell.repeat(3);
        }

        const managed = Math.max(0, Number(metric.creditos_gestionados) || 0);
        const dictated = Math.max(0, Number(metric.creditos_dictaminados) || 0);
        const pending = Math.max(0, Number(metric.creditos_pendientes) || 0);
        const overdue = Math.max(0, Number(metric.creditos_rezagados) || 0);
        const sold = Math.max(0, Number(metric.creditos_vendidos) || 0);
        const total = Math.max(0, Number(metric.total_creditos) || 0);
        const visitManagements = Math.max(0, Number(row?.gestiones_realizadas) || 0);

        return `
            <td class="text-end">
                <div class="atlas-attendance-credit-metric" title="Créditos atendidos o dictaminados en el mes según las mismas reglas de la app móvil.">
                    <div class="atlas-attendance-credit-value">${number(managed)}</div>
                    <div class="atlas-attendance-credit-tags">
                        <span class="atlas-attendance-credit-tag is-managed"><i class="fa-solid fa-clipboard-check"></i>${number(dictated)} dictaminados</span>
                        ${visitManagements > 0 ? `<span class="atlas-attendance-credit-tag is-visit">${number(visitManagements)} en esta visita</span>` : ''}
                    </div>
                </div>
            </td>
            <td class="text-end">
                <div class="atlas-attendance-credit-metric" title="Créditos pendientes del mes. Los créditos rezagados se muestran por separado.">
                    <div class="atlas-attendance-credit-value">${number(pending)}</div>
                    <div class="atlas-attendance-credit-tags">
                        <span class="atlas-attendance-credit-tag is-overdue"><i class="fa-solid fa-clock-rotate-left"></i>${number(overdue)} rezagados</span>
                    </div>
                </div>
            </td>
            <td class="text-end">
                <div class="atlas-attendance-credit-metric" title="Créditos únicos entre pendientes, rezagados, dictaminados y vendidos. Un mismo crédito no se cuenta dos veces.">
                    <div class="atlas-attendance-credit-value">${number(total)}</div>
                    <div class="atlas-attendance-credit-tags">
                        <span class="atlas-attendance-credit-tag is-sold"><i class="fa-solid fa-circle-check"></i>${number(sold)} vendidos</span>
                    </div>
                </div>
            </td>`;
    };

    const renderDetailActivity = () => {
        const rows = Array.isArray(activeDetailGroup?.rows) ? activeDetailGroup.rows : [];
        const status = detailStatusSelect.value;
        const filteredRows = status
            ? rows.filter((row) => String(row?.estatus_visita || '') === status)
            : rows;
        detailMeta.textContent = [
            status ? `${number(filteredRows.length)} de ${number(rows.length)} registros` : `${number(rows.length)} registro${rows.length === 1 ? '' : 's'}`,
            perimeterMeters === null ? null : `Perímetro permitido: ${number(perimeterMeters)} m`,
        ].filter(Boolean).join(' · ');
        renderCreditMetricsNotice();
        detailState.innerHTML = attendanceDetailTableHtml(filteredRows);
    };

    const setupCoverageMonths = (group) => {
        const months = [...new Set(visibleRows
            .filter((row) => collaboratorKey(row) === group?.key)
            .map((row) => String(row?.fecha || '').slice(0, 7))
            .filter((month) => /^\d{4}-\d{2}$/.test(month)))]
            .sort((left, right) => right.localeCompare(left, 'es'));
        const preferredMonth = String(group?.latestDate || endInput.value || '').slice(0, 7);
        if (/^\d{4}-\d{2}$/.test(preferredMonth) && !months.includes(preferredMonth)) {
            months.unshift(preferredMonth);
        }
        coverageMonthSelect.innerHTML = months
            .map((month) => `<option value="${month}">${escapeHtml(coverageMonthLabel(month))}</option>`)
            .join('');
        coverageMonthSelect.value = months.includes(preferredMonth) ? preferredMonth : (months[0] || '');
        coverageMonthSelect.disabled = months.length <= 1;
    };

    const renderCoverageSummary = (data) => {
        coveragePeriod.textContent = coverageMonthLabel(data?.mes || coverageMonthSelect.value);
        coverageState.innerHTML = `
            <div class="atlas-attendance-coverage-stats">
                <div class="atlas-attendance-coverage-stat">
                    <div class="atlas-attendance-coverage-stat-value">${number(data?.agencias_visitadas)}</div>
                    <div class="atlas-attendance-coverage-stat-label">Sucursales visitadas</div>
                </div>
                <div class="atlas-attendance-coverage-stat">
                    <div class="atlas-attendance-coverage-stat-value">${number(data?.agencias_pendientes)}</div>
                    <div class="atlas-attendance-coverage-stat-label">Sucursales faltantes por visitar</div>
                </div>
                <div class="atlas-attendance-coverage-stat is-overdue">
                    <div class="atlas-attendance-coverage-stat-value">${number(data?.agencias_agendadas_sin_checkin)}</div>
                    <div class="atlas-attendance-coverage-stat-label">Sucursales agendadas sin check-in</div>
                </div>
            </div>`;
    };

    const loadCoverageSummary = async () => {
        const group = activeDetailGroup;
        const month = coverageMonthSelect.value;
        if (!group || !month) {
            coveragePeriod.textContent = '';
            coverageState.innerHTML = '<div class="alert alert-info mb-0 py-2">No hay un mes disponible para calcular la cobertura.</div>';
            return;
        }

        const representative = group.representative || {};
        const personaId = String(group.personaId || '').trim();
        const externalId = String(representative.numero_empleado || '').trim();
        if (!personaId && !externalId) {
            coveragePeriod.textContent = coverageMonthLabel(month);
            coverageState.innerHTML = '<div class="alert alert-info mb-0 py-2">No pudimos identificar al colaborador para preparar su cobertura mensual.</div>';
            return;
        }

        coverageAbortController?.abort();
        coverageAbortController = new AbortController();
        const requestGroupKey = group.key;
        coveragePeriod.textContent = coverageMonthLabel(month);
        coverageState.innerHTML = '<div class="atlas-attendance-coverage-loading"><span><i class="fa-solid fa-spinner fa-spin me-2"></i>Consultando cobertura mensual...</span></div>';
        const params = new URLSearchParams({ mes: month });
        if (personaId) params.set('gestor_persona_id', personaId);
        if (externalId) params.set('external_id', externalId);

        try {
            const response = await fetch(`/Atlas/getResumenCoberturaAsistencia?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                signal: coverageAbortController.signal,
            });
            const payload = await response.json();
            if (!response.ok || !payload?.success) {
                throw new Error(payload?.mensaje || 'No se pudo consultar la cobertura mensual.');
            }
            if (activeDetailGroup?.key !== requestGroupKey || coverageMonthSelect.value !== month) return;
            renderCoverageSummary(payload.datos || {});
        } catch (error) {
            if (error?.name === 'AbortError') return;
            if (activeDetailGroup?.key !== requestGroupKey) return;
            coverageState.innerHTML = '<div class="alert alert-info mb-0 py-2">No pudimos preparar la cobertura mensual. El detalle de asistencias sigue disponible.</div>';
        }
    };

    const loadAttendanceCreditMetrics = async (group) => {
        const personaId = String(group?.personaId || '').trim();
        const dateStart = String(startInput.value || group?.earliestDate || '').trim();
        const dateEnd = String(endInput.value || group?.latestDate || '').trim();
        const requestGroupKey = group?.key;

        creditMetricsAbortController?.abort();
        creditMetricsAbortController = null;
        activeCreditMetrics = new Map();
        creditMetricsLoaded = false;
        creditMetricsError = '';

        if (!personaId || !dateStart || !dateEnd) {
            creditMetricsLoading = false;
            creditMetricsLoaded = true;
            creditMetricsError = 'No pudimos identificar al colaborador o el periodo para consultar los estados de sus créditos.';
            renderDetailActivity();
            return;
        }

        const controller = new AbortController();
        creditMetricsAbortController = controller;
        creditMetricsLoading = true;
        renderDetailActivity();

        const params = new URLSearchParams({
            fecha_inicio: dateStart,
            fecha_fin: dateEnd,
            gestor_persona_id: personaId,
        });

        try {
            const response = await fetch(`/Atlas/getCreditosSucursalesAsistencia?${params.toString()}`, {
                headers: { Accept: 'application/json' },
                signal: controller.signal,
            });
            const payload = await response.json();
            if (!response.ok || !payload?.success) {
                throw new Error(payload?.mensaje || 'No se pudieron consultar los estados de créditos.');
            }
            if (activeDetailGroup?.key !== requestGroupKey) return;

            const records = Array.isArray(payload?.datos?.registros) ? payload.datos.registros : [];
            activeCreditMetrics = new Map(records
                .map((record) => [creditMetricKey(record?.fk_sucursal, record?.mes), record])
                .filter(([key]) => key));
            creditMetricsLoading = false;
            creditMetricsLoaded = true;
            creditMetricsError = '';
            renderDetailActivity();
        } catch (error) {
            if (error?.name === 'AbortError' || activeDetailGroup?.key !== requestGroupKey) return;
            activeCreditMetrics = new Map();
            creditMetricsLoading = false;
            creditMetricsLoaded = true;
            creditMetricsError = 'No pudimos consultar los estados de créditos de la app en este momento. El detalle de asistencias sigue disponible.';
            renderDetailActivity();
        } finally {
            if (creditMetricsAbortController === controller) {
                creditMetricsAbortController = null;
            }
        }
    };

    const groupEvidenceEntries = (group) => (group?.rows || []).flatMap((row) => (
        (Array.isArray(row?.evidencias) ? row.evidencias : []).map((evidence) => ({ evidence, row }))
    ));

    const attendanceDetailTableHtml = (rows) => {
        if (!rows.length) {
            return '<div class="atlas-evidence-empty py-3">Este colaborador no tiene asistencias para los filtros seleccionados.</div>';
        }
        return `<div class="atlas-attendance-detail-table-wrap">
            <table class="table table-hover atlas-attendance-detail-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Agencia / Distribuidor</th>
                        <th>Estatus</th>
                        <th>Llegada</th>
                        <th>Salida</th>
                        <th>Permanencia</th>
                        <th>Perímetro</th>
                        <th class="text-end" title="Créditos gestionados o dictaminados según la app móvil">Gestiones realizadas <i class="fa-solid fa-circle-info ms-1"></i></th>
                        <th class="text-end" title="Pendientes del mes; los rezagados aparecen desglosados">Pendientes <i class="fa-solid fa-circle-info ms-1"></i></th>
                        <th class="text-end" title="Créditos únicos entre todos los estados de la app">Totales <i class="fa-solid fa-circle-info ms-1"></i></th>
                        <th class="text-center">Evidencias</th>
                        <th>Observaciones</th>
                        <th class="text-center">Imagen</th>
                    </tr>
                </thead>
                <tbody>
                    ${rows.map((row) => {
                        const distance = row?.distancia_metros === null || row?.distancia_metros === undefined
                            ? 'Sin distancia'
                            : `${number(row.distancia_metros)} m`;
                        const observations = row?.observaciones_incumplimiento || row?.observaciones || 'Sin observaciones';
                        const location = [row?.latitud, row?.longitud]
                            .filter((value) => value !== null && value !== undefined && String(value).trim() !== '')
                            .join(', ');
                        return `<tr>
                            <td>
                                <div class="atlas-attendance-main">${escapeHtml(row?.fecha || 'Sin fecha')}</div>
                                <div class="atlas-attendance-sub">${escapeHtml(row?.dia_visita || '')}</div>
                            </td>
                            <td>
                                <div class="atlas-attendance-main">${escapeHtml(row?.agencia || 'Sin agencia')}</div>
                                <div class="atlas-attendance-sub">${escapeHtml(row?.distribuidor || 'Sin distribuidor')}</div>
                            </td>
                            <td><span class="atlas-attendance-badge ${statusClass(row?.estatus_visita)}"><i class="fa-solid ${statusIconClass(row?.estatus_visita)}"></i>${escapeHtml(row?.estatus_visita || 'Sin estatus')}</span></td>
                            <td>
                                <div class="atlas-attendance-main">${escapeHtml(row?.hora_llegada || row?.hora_gestion || '--:--')}</div>
                                <div class="atlas-attendance-sub">Conf. ${escapeHtml(row?.hora_confirmacion_llegada || '--:--')}</div>
                            </td>
                            <td>
                                <div class="atlas-attendance-main">${escapeHtml(row?.hora_salida || '--:--')}</div>
                                <div class="atlas-attendance-sub">Fin ${escapeHtml(row?.hora_termino_visita || '--:--')}</div>
                            </td>
                            <td>${escapeHtml(row?.tiempo_permanencia || 'Sin dato')}</td>
                            <td title="${escapeHtml(location ? `Coordenadas: ${location}` : 'Coordenadas no disponibles')}">
                                <div class="atlas-attendance-main">${escapeHtml(row?.dentro_perimetro || 'Sin dato')}</div>
                                <div class="atlas-attendance-sub">${escapeHtml(distance)}</div>
                            </td>
                            ${creditMetricCellsHtml(row)}
                            <td class="text-center"><strong>${number(rowEvidenceCount(row))}</strong></td>
                            <td style="max-width:18rem; white-space:normal;">${escapeHtml(observations)}</td>
                            <td class="text-center">${rowImageActionHtml(row)}</td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>`;
    };

    const evidenceMedia = (evidence) => {
        const item = evidence || {};
        if (!item.disponible || !item.id) {
            return `<div class="atlas-evidence-media atlas-evidence-unavailable">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>${escapeHtml(item.mensaje || 'Esta evidencia no esta disponible para consulta.')}</div>
            </div>`;
        }

        const source = `/Atlas/verEvidenciaAsistencia?id=${encodeURIComponent(item.id)}`;
        const mimeType = String(item.mime_type || '').toLowerCase();
        const kind = String(item.tipo || '').toLowerCase();
        if (mimeType.startsWith('image/') || kind === 'imagen' || kind === 'foto') {
            return `<div class="atlas-evidence-media">
                <img src="${source}" alt="${escapeHtml(item.nombre || 'Evidencia fotografica')}" loading="lazy">
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

    const routeField = (route, keys, fallback = '') => {
        for (const key of keys) {
            const value = route?.[key];
            if (value !== null && value !== undefined && String(value).trim() !== '') return value;
        }
        return fallback;
    };

    const routesMaintenanceMessage = 'El servicio de consulta de rutas de usuarios se encuentra en mantenimiento, en breves se desplegara y podras ver de nuevo las rutas';
    const routeMapUrl = (routeId) => `/Atlas/verMapaRutaAsistencia?ruta_id=${encodeURIComponent(routeId)}`;

    const loadRouteMapPreview = (button) => {
        const routeId = String(button?.dataset?.atlasRouteMapId || '').trim();
        const card = button?.closest('.atlas-evidence-route-item');
        const state = card?.querySelector('[data-atlas-route-map-state]');
        if (!routeId || !state || state.dataset.loaded === '1' || state.dataset.loading === '1') return;

        const url = routeMapUrl(routeId);
        const originalHtml = button.innerHTML;
        state.dataset.loading = '1';
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mapa';
        state.innerHTML = `
            <div class="atlas-evidence-route-map-media">
                <span class="spinner-border spinner-border-sm text-primary"></span>
            </div>`;

        const img = new Image();
        img.alt = 'Mapa de ruta';
        img.loading = 'lazy';
        img.addEventListener('load', () => {
            state.dataset.loaded = '1';
            delete state.dataset.loading;
            button.disabled = false;
            button.innerHTML = originalHtml;

            const media = document.createElement('div');
            media.className = 'atlas-evidence-route-map-media';
            media.replaceChildren(img);

            const footer = document.createElement('div');
            footer.className = 'atlas-evidence-route-map-footer';

            const link = document.createElement('a');
            link.className = 'btn btn-sm btn-outline-primary';
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener';
            link.innerHTML = '<i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Abrir mapa';
            footer.appendChild(link);

            state.replaceChildren(media, footer);
        }, { once: true });
        img.addEventListener('error', () => {
            delete state.dataset.loading;
            button.disabled = false;
            button.innerHTML = originalHtml;
            state.innerHTML = `<div class="alert alert-info mb-0 py-2">${routesMaintenanceMessage}</div>`;
        }, { once: true });
        img.src = url;
    };

    const renderSpartanRoutes = (routes) => {
        if (!Array.isArray(routes) || !routes.length) {
            routesState.innerHTML = '<div class="atlas-evidence-empty py-3">Sin rutas generadas para este usuario en el periodo filtrado.</div>';
            return;
        }
        routesState.innerHTML = `<div class="atlas-evidence-route-list">${routes.map((route) => {
            const routeId = String(routeField(route, ['id', 'ruta_id'], '') || '').trim();
            const name = routeField(route, ['nombre_ruta', 'nombre'], routeId ? `Ruta #${routeId}` : 'Ruta sin nombre');
            const status = routeField(route, ['estatus', 'estado'], 'Sin estatus');
            const start = routeField(route, ['fecha_inicio', 'fecha_ruta'], '');
            const end = routeField(route, ['fecha_fin'], start);
            const visits = Number(routeField(route, ['total_visitas', 'total_sucursales'], 0));
            const branchList = Array.isArray(route.sucursales)
                ? route.sucursales
                    .map((branch) => String(branch?.sucursal || branch?.agencia || branch?.fk_sucursal || '').trim())
                    .filter(Boolean)
                    .slice(0, 6)
                    .join(' | ')
                : '';
            return `<article class="atlas-evidence-route-item">
                <div class="atlas-evidence-route-head">
                    <div>
                        <div class="atlas-evidence-route-name">${escapeHtml(name)}</div>
                        <div class="atlas-evidence-route-detail">${escapeHtml([start && end ? `${start} a ${end}` : start, visits ? `${number(visits)} visita(s)` : null].filter(Boolean).join(' · '))}</div>
                    </div>
                    <span class="atlas-evidence-route-badge">${escapeHtml(status)}</span>
                </div>
                ${branchList ? `<div class="atlas-evidence-route-detail">${escapeHtml(branchList)}</div>` : ''}
                ${routeId ? `<div class="atlas-evidence-route-actions">
                    <button type="button" class="btn btn-sm btn-label-primary" data-atlas-route-map-id="${escapeHtml(routeId)}">
                        <i class="fa-solid fa-map-location-dot me-1"></i>Ver mapa
                    </button>
                </div>
                <div class="atlas-evidence-route-map-state" data-atlas-route-map-state></div>` : ''}
            </article>`;
        }).join('')}</div>`;
    };

    const loadSpartanRoutes = async (row) => {
        const personaId = collaboratorSelect.value || row?.colaborador_persona_id || row?.gestor_persona_id || '';
        const params = new URLSearchParams();
        if (personaId) params.set('gestor_persona_id', personaId);
        if (startInput.value) params.set('fecha_inicio', startInput.value);
        if (endInput.value) params.set('fecha_fin', endInput.value);
        params.set('limit', '100');
        routesMeta.textContent = [row?.colaborador || 'Usuario seleccionado', startInput.value && endInput.value ? `${startInput.value} a ${endInput.value}` : 'Periodo actual'].filter(Boolean).join(' · ');
        routesState.innerHTML = '<div class="atlas-evidence-empty py-3"><span class="spinner-border spinner-border-sm me-2"></span>Consultando rutas en Spartan...</div>';
        try {
            const headers = { Accept: 'application/json' };
            const response = await fetch(`/Atlas/getRutasUsuarioSpartan?${params.toString()}`, { headers });
            const payload = await response.json();
            const data = payload?.datos || {};
            if (!response.ok || !payload?.success) {
                const status = Number(payload?.status || response.status || 0);
                if ([204, 404].includes(status) && Array.isArray(data.rutas) && Number(data.total || 0) === 0) {
                    renderSpartanRoutes([]);
                    return;
                }
                throw new Error(payload?.mensaje || 'No se pudieron consultar rutas Spartan.');
            }
            renderSpartanRoutes(data.rutas || []);
        } catch (error) {
            routesState.innerHTML = `<div class="alert alert-info mb-0 py-2">${routesMaintenanceMessage}</div>`;
        }
    };

    const showEvidenceModal = (group) => {
        const rows = Array.isArray(group?.rows) ? group.rows : [];
        const representative = group?.representative || rows[0] || {};
        activeDetailGroup = group;
        creditMetricsAbortController?.abort();
        creditMetricsAbortController = null;
        activeCreditMetrics = new Map();
        creditMetricsLoading = true;
        creditMetricsLoaded = false;
        creditMetricsError = '';
        const visits = groupVisitStats(group);
        const evidences = groupEvidenceEntries(group);
        const agencyCount = new Set(rows.map((row, index) => agencyVisitKey(row, index))).size;
        const managementCount = rows.reduce(
            (total, row) => total + Number(row?.gestiones_realizadas || 0),
            0
        );
        const evidenceCount = rows.reduce((total, row) => total + rowEvidenceCount(row), 0);
        const status = collaboratorActivityStatus(group);

        evidenceMeta.textContent = [
            group?.name || 'Colaborador',
            [group?.position, group?.divisional].filter(Boolean).join(' · '),
            groupPeriodText(group),
        ].filter(Boolean).join(' · ');
        detailSummary.innerHTML = `
            <div class="atlas-attendance-detail-stat is-teal">
                <div class="atlas-attendance-detail-stat-label">
                    <span class="atlas-attendance-detail-stat-icon"><i class="fa-solid fa-route"></i></span>
                    <span>Visitas</span>
                </div>
                <div class="atlas-attendance-detail-stat-value">${number(visits.total)}</div>
                <div class="atlas-attendance-detail-stat-sub">${number(visits.withTime)} con horario · ${number(visits.withoutTime)} sin horario</div>
            </div>
            <div class="atlas-attendance-detail-stat is-blue">
                <div class="atlas-attendance-detail-stat-label">
                    <span class="atlas-attendance-detail-stat-icon"><i class="fa-solid fa-building"></i></span>
                    <span>Agencias</span>
                </div>
                <div class="atlas-attendance-detail-stat-value">${number(agencyCount)}</div>
                <div class="atlas-attendance-detail-stat-sub">Dentro del periodo filtrado</div>
            </div>
            <div class="atlas-attendance-detail-stat is-indigo">
                <div class="atlas-attendance-detail-stat-label">
                    <span class="atlas-attendance-detail-stat-icon"><i class="fa-solid fa-clipboard-check"></i></span>
                    <span>Gestiones realizadas</span>
                </div>
                <div class="atlas-attendance-detail-stat-value">${number(managementCount)}</div>
                <div class="atlas-attendance-detail-stat-sub">${escapeHtml(status.label)}</div>
            </div>
            <div class="atlas-attendance-detail-stat is-rose">
                <div class="atlas-attendance-detail-stat-label">
                    <span class="atlas-attendance-detail-stat-icon"><i class="fa-solid fa-camera"></i></span>
                    <span>Evidencias</span>
                </div>
                <div class="atlas-attendance-detail-stat-value">${number(evidenceCount)}</div>
                <div class="atlas-attendance-detail-stat-sub">Archivos reportados</div>
            </div>`;

        setupDetailStatusFilter(group);
        renderDetailActivity();
        void loadAttendanceCreditMetrics(group);
        setupCoverageMonths(group);
        void loadCoverageSummary();

        evidenceSectionMeta.textContent = `${number(evidenceCount)} evidencia${evidenceCount === 1 ? '' : 's'}`;
        evidenceGrid.innerHTML = evidences.length
            ? evidences.map(({ evidence, row }) => `<article class="atlas-evidence-item">
                ${evidenceMedia(evidence)}
                <div class="atlas-evidence-info">
                    <div class="atlas-evidence-name">${escapeHtml(evidence?.nombre || 'Evidencia')}</div>
                    <div class="atlas-evidence-detail">${escapeHtml([
                        row?.agencia,
                        row?.fecha,
                        evidence?.tipo,
                        formatBytes(evidence?.size_bytes),
                        evidence?.fecha_hora ? String(evidence.fecha_hora).replace('T', ' ') : null
                    ].filter(Boolean).join(' · '))}</div>
                </div>
            </article>`).join('')
            : `<div class="atlas-evidence-empty">${
                evidenceCount
                    ? 'Hay evidencias reportadas, pero sus archivos no están disponibles para consulta.'
                    : 'Este colaborador no tiene evidencias cargadas en el periodo filtrado.'
            }</div>`;

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
        loadSpartanRoutes({
            ...representative,
            colaborador: group?.name || representative.colaborador,
            colaborador_persona_id: group?.personaId || representative.colaborador_persona_id,
        });
    };

    const datasetQuery = () => {
        const params = new URLSearchParams();
        params.set('fecha_inicio', datasetStart);
        params.set('fecha_fin', datasetEnd);
        return params;
    };

    const exportQuery = (rangeStart, rangeEnd) => {
        const params = new URLSearchParams();
        if (rangeStart) params.set('fecha_inicio', rangeStart);
        if (rangeEnd) params.set('fecha_fin', rangeEnd);
        if (collaboratorSelect.value) params.set('gestor_persona_id', collaboratorSelect.value);
        if (distributorSelect.value) params.set('distribuidor_id', distributorSelect.value);
        if (statusSelect.value) params.set('estatus', statusSelect.value);
        if (divisionalSelect.value) params.set('divisional', divisionalSelect.value);
        if (evidenceSelect.value) params.set('evidencias', evidenceSelect.value);
        if (routeSelect.value) params.set('rutas', routeSelect.value);
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
            [routeSelect, 'Todas las rutas'],
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

    const downloadDateRangeIsValid = () => (
        Boolean(downloadStartInput.value && downloadEndInput.value)
        && downloadEndInput.value >= downloadStartInput.value
        && downloadStartInput.value >= datasetStart
        && downloadEndInput.value <= datasetEnd
    );

    const validateDownloadRange = () => {
        const valid = downloadDateRangeIsValid();
        downloadStartInput.classList.toggle('is-invalid', !valid);
        downloadEndInput.classList.toggle('is-invalid', !valid);
        downloadError.classList.toggle('d-none', valid);
        downloadConfirmButton.disabled = !valid;
        return valid;
    };

    const openDownloadModal = () => {
        downloadStartInput.value = dateRangeIsValid() ? startInput.value : initialStart;
        downloadEndInput.value = dateRangeIsValid() ? endInput.value : initialEnd;
        validateDownloadRange();
        downloadModal?.show();
    };

    const evidenceStatus = (row) => {
        const evidences = Array.isArray(row?.evidencias) ? row.evidencias : [];
        const declaredCount = row?.total_evidencias === null || row?.total_evidencias === undefined
            ? evidences.length
            : Math.max(0, Number(row.total_evidencias) || 0);
        if (declaredCount === 0 && evidences.length === 0) return 'sin';

        const hasMissingDetails = evidences.length === 0 || declaredCount > evidences.length;
        const hasUnavailableEvidence = evidences.some((evidence) => (
            !evidence
            || ![true, 1, '1', 'true'].includes(evidence.disponible)
            || !evidence.id
        ));
        return hasMissingDetails || hasUnavailableEvidence ? 'incompletas' : 'con';
    };

    const hasRouteValue = (value) => {
        const route = String(value ?? '').trim();
        return route !== '' && route !== '0';
    };

    const routeStatus = (row) => {
        if (Array.isArray(row?.rutas)) return row.rutas.length > 0 ? 'con' : 'sin';
        for (const field of ['total_rutas', 'rutas_total', 'rutas_generadas']) {
            if (row?.[field] !== null && row?.[field] !== undefined && String(row[field]).trim() !== '') {
                return Number(row[field]) > 0 ? 'con' : 'sin';
            }
        }
        return hasRouteValue(row?.ruta_sucursal_id) ? 'con' : 'sin';
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
        if (routeSelect.value && routeStatus(row) !== routeSelect.value) return false;
        return true;
    };

    const renderRows = (groups) => {
        visibleGroups = groups;
        if (!groups.length) {
            body.innerHTML = '';
            return;
        }
        body.innerHTML = groups.map((group, groupIndex) => {
            const branches = groupBranchStats(group);
            const status = collaboratorActivityStatus(group);
            const dateDetail = group.latestDate && group.earliestDate !== group.latestDate
                ? `Desde ${group.earliestDate}`
                : (group.representative?.dia_visita || 'Actividad más reciente');
            const detailTitle = `Ver asistencias de ${group.name}`;
            return `<tr data-atlas-group="${groupIndex}">
                <td>
                    <div class="atlas-attendance-cell">
                        <span class="atlas-attendance-cell-icon is-date"><i class="fa-solid fa-calendar-day"></i></span>
                        <div class="atlas-attendance-cell-content">
                            <div class="atlas-attendance-main">${escapeHtml(group.latestDate || 'Sin fecha')}</div>
                            <div class="atlas-attendance-sub">${escapeHtml(dateDetail)}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="atlas-attendance-cell">
                        <span class="atlas-attendance-cell-icon is-collaborator"><i class="fa-solid fa-user-tie"></i></span>
                        <div class="atlas-attendance-cell-content">
                            <div class="atlas-attendance-main">${escapeHtml(group.name)}</div>
                            <div class="atlas-attendance-sub">${escapeHtml([group.position, group.divisional].filter(Boolean).join(' · '))}</div>
                        </div>
                    </div>
                </td>
                <td title="${escapeHtml(status.title || 'Sin estados registrados')}">
                    <span class="atlas-attendance-badge ${status.className}"><i class="fa-solid ${status.iconClass}"></i>${escapeHtml(status.label)}</span>
                </td>
                <td title="${escapeHtml(`${branches.visited} visitadas | ${branches.missing} faltantes | ${branches.scheduledWithoutCheckIn} agendadas sin check-in`)}">
                    <div class="atlas-attendance-cell">
                        <span class="atlas-attendance-cell-icon is-visits"><i class="fa-solid fa-route"></i></span>
                        <div class="atlas-attendance-cell-content atlas-attendance-branch-summary">
                            <div class="atlas-attendance-branch-line"><span>Visitadas</span><strong>${number(branches.visited)}</strong></div>
                            <div class="atlas-attendance-branch-line"><span>Faltantes</span><strong>${number(branches.missing)}</strong></div>
                            <div class="atlas-attendance-branch-line is-missed"><span>Agendadas sin check-in</span><strong>${number(branches.scheduledWithoutCheckIn)}</strong></div>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="atlas-attendance-detail-button" data-atlas-detail-group="${groupIndex}"
                            title="${escapeHtml(detailTitle)}" aria-label="${escapeHtml(detailTitle)}">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </td>
            </tr>`;
        }).join('');
    };

    const currentFilteredRows = () => visibleRows.filter(rowMatchesFilters);

    const updateTableMeta = () => {
        const shownGroups = attendanceTable
            ? attendanceTable.rows({ search: 'applied' }).count()
            : visibleGroups.length;
        const filteredRecords = currentFilteredRows().length;
        document.getElementById('atlasAttendanceGenerated').textContent =
            `${number(shownGroups)} colaborador${shownGroups === 1 ? '' : 'es'} · ${number(filteredRecords)} registro${filteredRecords === 1 ? '' : 's'}` +
            (generatedAt ? ` · Generado ${generatedAt}` : '');
    };

    const destroyAttendanceTable = () => {
        if (!attendanceTable) return;
        window.jQuery('#atlasAttendanceTable').off('.atlasAttendance');
        attendanceTable.destroy();
        attendanceTable = null;
    };

    const initializeAttendanceTable = (searchValue = '') => {
        if (!window.jQuery?.fn?.DataTable) {
            throw new Error('DataTables no está disponible en esta pantalla.');
        }
        destroyAttendanceTable();
        const $table = window.jQuery('#atlasAttendanceTable');
        attendanceTable = $table.DataTable({
            language: {
                emptyTable: 'No hay colaboradores para los filtros seleccionados',
                info: 'Mostrando de _START_ a _END_ de _TOTAL_ colaboradores',
                infoEmpty: 'Sin colaboradores para mostrar',
                infoFiltered: '(filtrado de _MAX_ colaboradores)',
                zeroRecords: 'No se encontraron colaboradores',
                lengthMenu: 'Mostrar _MENU_ colaboradores',
                search: 'Buscar:',
                searchPlaceholder: 'Buscar colaborador...',
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
                { targets: 4, orderable: false, searchable: false },
            ],
            initComplete() {
                const container = this.api().table().container();
                window.jQuery(container)
                    .find('input[type="search"]')
                    .attr('placeholder', 'Buscar colaborador...');
            },
        });
        $table
            .off('draw.dt.atlasAttendance')
            .on('draw.dt.atlasAttendance', updateTableMeta);
        if (searchValue) {
            attendanceTable.search(searchValue).draw();
        } else {
            updateTableMeta();
        }
    };

    const renderFilteredTable = () => {
        const searchValue = attendanceTable ? attendanceTable.search() : '';
        destroyAttendanceTable();
        const rows = currentFilteredRows();
        renderRows(collaboratorGroups(rows));
        initializeAttendanceTable(searchValue);
    };

    const applyLocalFilters = () => {
        const validRange = dateRangeIsValid();
        startInput.classList.toggle('is-invalid', !validRange);
        endInput.classList.toggle('is-invalid', !validRange);
        downloadButton.disabled = loading || !apiReady;
        if (validRange && !loading) renderFilteredTable();
    };

    const reportError = (message) => {
        destroyAttendanceTable();
        visibleRows = [];
        visibleGroups = [];
        body.innerHTML = `<tr><td class="atlas-attendance-empty text-danger" colspan="5">${escapeHtml(message)}</td></tr>`;
        document.getElementById('atlasAttendanceGenerated').textContent = 'No disponible';
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'No se pudo cargar el reporte', text: message });
        }
    };

    const loadReport = async () => {
        if (loading || !apiReady) return;
        loading = true;
        showAttendanceLoading();
        downloadButton.disabled = true;
        document.getElementById('atlasAttendanceRefresh').disabled = true;
        destroyAttendanceTable();
        body.innerHTML = '<tr><td class="atlas-attendance-empty" colspan="5">Consultando asistencias...</td></tr>';
        try {
            const { response, payload } = await fetchJsonWithNetworkRetry(`/Atlas/getReporteAsistencias?${datasetQuery().toString()}`, {
                headers: { Accept: 'application/json' }
            });
            if (!response.ok || !payload?.success) {
                throw new Error(payload?.mensaje || 'No pudimos cargar el reporte en este momento. Intenta de nuevo mas tarde o avisanos para revisarlo.');
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
            const statusOptions = [...new Set([
                ...(catalogs.estatuses || []),
                'En visita',
            ].map((status) => String(status || '').trim()).filter(Boolean))]
                .sort((left, right) => left.localeCompare(right, 'es'));
            updateSelect(statusSelect, statusOptions, '', '', 'Todos los estatus');
            updateSelect(divisionalSelect, catalogs.divisionales || [], '', '', 'Todas las divisionales');
            generatedAt = String(data.generado_at || '').replace('T', ' ');
            perimeterMeters = data.perimetro_metros === null || data.perimetro_metros === undefined
                ? null
                : Number(data.perimetro_metros);
            visibleRows = rows;
            renderFilteredTable();
        } catch (error) {
            hideAttendanceLoading();
            const message = isTransientNetworkError(error)
                ? 'La conexion cambio mientras se consultaban las asistencias. Revisa la red y vuelve a intentar.'
                : (error.message || 'No se pudo consultar el reporte.');
            reportError(message);
        } finally {
            hideAttendanceLoading();
            loading = false;
            document.getElementById('atlasAttendanceRefresh').disabled = false;
            downloadButton.disabled = !apiReady;
        }
    };

    const initializeAttendanceModule = () => {
        if (!window.jQuery?.fn?.DataTable || !window.jQuery?.fn?.select2) {
            reportError('No se pudieron cargar los componentes de tabla y filtros.');
            downloadButton.disabled = true;
            return;
        }

        downloadModal = typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(downloadModalElement)
            : null;
        evidenceModal = typeof bootstrap !== 'undefined'
            ? bootstrap.Modal.getOrCreateInstance(evidenceModalElement)
            : null;

        initializeSearchableSelects();

        document.getElementById('atlasAttendanceRefresh').addEventListener('click', loadReport);
        document.getElementById('atlasAttendanceDownload').addEventListener('click', openDownloadModal);
        downloadForm.addEventListener('submit', (event) => {
            event.preventDefault();
            if (!validateDownloadRange()) return;
            const query = exportQuery(downloadStartInput.value, downloadEndInput.value);
            downloadModal?.hide();
            window.location.href = `/Atlas/descargarReporteAsistencias?${query.toString()}`;
        });
        [downloadStartInput, downloadEndInput].forEach((input) => {
            input.addEventListener('change', validateDownloadRange);
        });
        document.getElementById('atlasAttendanceClear').addEventListener('click', () => {
            startInput.value = initialStart;
            endInput.value = initialEnd;
            [collaboratorSelect, distributorSelect, statusSelect, divisionalSelect, evidenceSelect, routeSelect].forEach((select) => {
                select.value = '';
                refreshSearchableSelect(select);
            });
            if (attendanceTable) attendanceTable.search('');
            applyLocalFilters();
        });
        [startInput, endInput].forEach((input) => {
            input.addEventListener('change', applyLocalFilters);
        });
        [collaboratorSelect, distributorSelect, statusSelect, divisionalSelect, evidenceSelect, routeSelect].forEach((select) => {
            window.jQuery(select)
                .off('change.atlasAttendance')
                .on('change.atlasAttendance', applyLocalFilters);
        });
        detailStatusSelect.addEventListener('change', renderDetailActivity);
        coverageMonthSelect.addEventListener('change', loadCoverageSummary);
        body.addEventListener('click', (event) => {
            const button = event.target.closest('[data-atlas-detail-group]');
            if (!button) return;
            const group = visibleGroups[Number(button.dataset.atlasDetailGroup)];
            if (group) {
                lastDetailTrigger = button;
                showEvidenceModal(group);
            }
        });
        routesState.addEventListener('click', (event) => {
            const button = event.target.closest('[data-atlas-route-map-id]');
            if (!button) return;
            loadRouteMapPreview(button);
        });
        evidenceModalElement.addEventListener('hide.bs.modal', () => {
            const focusedElement = document.activeElement;
            if (focusedElement instanceof HTMLElement && evidenceModalElement.contains(focusedElement)) {
                focusedElement.blur();
            }
        });
        evidenceModalElement.addEventListener('hidden.bs.modal', () => {
            coverageAbortController?.abort();
            coverageAbortController = null;
            creditMetricsAbortController?.abort();
            creditMetricsAbortController = null;
            activeDetailGroup = null;
            activeCreditMetrics = new Map();
            creditMetricsLoading = false;
            creditMetricsLoaded = false;
            creditMetricsError = '';
            evidenceGrid.querySelectorAll('video, audio').forEach((media) => {
                media.pause();
                media.removeAttribute('src');
                media.querySelectorAll('source').forEach((source) => source.removeAttribute('src'));
                media.load();
            });
            evidenceGrid.innerHTML = '';
            detailSummary.innerHTML = '';
            detailMeta.textContent = '';
            creditMetricsNotice.innerHTML = '';
            detailState.innerHTML = '';
            detailStatusSelect.innerHTML = '<option value="">Todos los estatus</option>';
            coverageMonthSelect.innerHTML = '';
            coveragePeriod.textContent = '';
            coverageState.innerHTML = '';
            evidenceSectionMeta.textContent = '';
            routesState.innerHTML = '';
            routesMeta.textContent = '';
            if (lastDetailTrigger instanceof HTMLElement && document.contains(lastDetailTrigger)) {
                lastDetailTrigger.focus({ preventScroll: true });
            }
            lastDetailTrigger = null;
        });

        if (apiReady) {
            loadReport();
        } else {
            body.innerHTML = '<tr><td class="atlas-attendance-empty" colspan="5">El reporte no está disponible por ahora. Si necesitas consultarlo, avísanos y lo revisamos.</td></tr>';
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
