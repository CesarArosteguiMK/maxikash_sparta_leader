<style>
    .ch-perfiles-page {
        color: #24324a;
    }

    .ch-perfiles-page .page-title {
        font-weight: 800;
        letter-spacing: 0;
        color: #24324a;
    }

    .ch-perfiles-toolbar {
        background: #fff;
        border: 1px solid #dce6f2;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
    }

    .ch-search-select {
        position: relative;
    }

    .ch-search-select-toggle {
        background-color: #fff;
        min-height: 42px;
    }

    .ch-search-select-menu {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        z-index: 1060;
        width: 100%;
        padding: 10px;
        background: #fff;
        border: 1px solid #dce6f2;
        border-radius: 10px;
        box-shadow: 0 12px 26px rgba(16, 42, 67, 0.16);
    }

    .ch-search-select-options {
        max-height: 260px;
        overflow-y: auto;
    }

    .ch-search-select-option {
        display: block;
        width: 100%;
        border: 0;
        background: transparent;
        color: #24324a;
        text-align: left;
        padding: 10px 12px;
        border-radius: 8px;
        font-weight: 600;
    }

    .ch-search-select-option:hover,
    .ch-search-select-option.active {
        background: #e9edf3;
    }

    .ch-perfiles-tabs {
        gap: 8px;
    }

    .ch-perfiles-tabs .nav-link {
        border: 1px solid #dce6f2;
        border-radius: 10px;
        color: #24324a !important;
        font-weight: 800;
        padding: 10px 16px;
        background: #fff !important;
        background-color: #fff !important;
        box-shadow: none;
        transition: none;
    }

    .ch-perfiles-tabs .nav-link i {
        color: inherit !important;
    }

    .ch-perfiles-tabs .nav-link.active,
    .ch-perfiles-tabs .nav-link.active:active,
    .ch-perfiles-tabs .nav-link.active:focus,
    .ch-perfiles-tabs .nav-link.active:focus-visible {
        color: #fff !important;
        background: #24324a !important;
        background-color: #24324a !important;
        border-color: #24324a !important;
        box-shadow: 0 8px 18px rgba(36, 50, 74, 0.18);
        outline: 0;
    }

    .ch-perfiles-tabs .nav-link:hover,
    .ch-perfiles-tabs .nav-link:focus,
    .ch-perfiles-tabs .nav-link:active,
    .ch-perfiles-tabs .nav-link:focus-visible {
        color: #24324a !important;
        background: #f7f9fc !important;
        background-color: #f7f9fc !important;
        border-color: #c9d7ea !important;
        outline: 0;
    }

    .ch-perfiles-tabs .nav-link.active:hover,
    .ch-perfiles-tabs .nav-link.active:focus,
    .ch-perfiles-tabs .nav-link.active:active,
    .ch-perfiles-tabs .nav-link.active:focus-visible,
    .ch-perfiles-tabs .nav-link.active i {
        color: #fff !important;
        background: #24324a !important;
        background-color: #24324a !important;
        border-color: #24324a !important;
    }

    .ch-perfiles-page .btn-primary,
    .ch-perfiles-page .btn-primary:hover,
    .ch-perfiles-page .btn-primary:focus {
        color: #fff !important;
    }

    .ch-profile-hero {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr) auto;
        gap: 18px;
        align-items: center;
        background: linear-gradient(135deg, #24324a 0%, #1f6f9f 100%);
        color: #fff;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 10px 28px rgba(31, 45, 73, 0.18);
    }

    .ch-profile-avatar {
        width: 74px;
        height: 74px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.25);
        font-size: 24px;
        font-weight: 900;
    }

    .ch-profile-name {
        margin: 0;
        color: #fff;
        font-size: 26px;
        font-weight: 900;
        letter-spacing: 0;
    }

    .ch-profile-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .ch-profile-meta span,
    .ch-soft-skill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .ch-profile-meta span {
        color: #fff;
        background: rgba(255, 255, 255, 0.16);
    }

    .ch-profile-status {
        justify-self: end;
        min-width: 150px;
        text-align: right;
    }

    .ch-profile-status .status-number {
        display: block;
        font-size: 32px;
        font-weight: 900;
        line-height: 1;
    }

    .ch-section-card {
        background: #fff;
        border: 1px solid #dce6f2;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
        height: 100%;
    }

    .ch-section-card .card-header {
        background: #fff;
        border-bottom: 1px solid #edf2f7;
        padding: 16px 18px;
    }

    .ch-section-card .card-title {
        margin: 0;
        font-size: 16px;
        color: #24324a;
        font-weight: 900;
    }

    .ch-section-card .card-body {
        padding: 18px;
    }

    .ch-skill-row {
        display: grid;
        grid-template-columns: minmax(180px, 1.2fr) minmax(150px, 1fr) 42px;
        gap: 10px;
        align-items: center;
        padding: 9px 0;
        border-bottom: 1px solid #edf2f7;
    }

    .ch-skill-row:last-child {
        border-bottom: 0;
    }

    .ch-skill-name {
        font-weight: 800;
        color: #34465d;
        font-size: 13px;
        line-height: 1.25;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ch-progress {
        height: 9px;
        border-radius: 999px;
        background: #e8edf3;
        overflow: hidden;
    }

    .ch-progress > span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #1f6f9f, #29b6a8);
    }

    .ch-soft-skill {
        color: #24324a;
        background: #edf4ff;
        border: 1px solid #d5e6fb;
        margin: 0 8px 8px 0;
    }

    .ch-kpi-list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .ch-kpi-list li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        border-bottom: 1px solid #edf2f7;
        color: #34465d;
        font-weight: 700;
    }

    .ch-kpi-list li:last-child {
        border-bottom: 0;
    }

    .ch-kpi-list i {
        color: #d9a441;
        margin-top: 3px;
    }

    .ch-metric-card {
        background: #fff;
        border: 1px solid #dce6f2;
        border-radius: 12px;
        padding: 16px;
        box-shadow: 0 8px 24px rgba(16, 42, 67, 0.08);
    }

    .ch-metric-card .metric-label {
        display: block;
        color: #62748a;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
    }

    .ch-metric-card .metric-value {
        margin-top: 6px;
        color: #24324a;
        font-size: 28px;
        font-weight: 900;
        line-height: 1;
    }

    .ch-heatmap {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .ch-heatmap th {
        color: #62748a;
        font-size: 12px;
        text-transform: uppercase;
        padding: 12px;
        border-bottom: 1px solid #edf2f7;
    }

    .ch-heatmap td {
        padding: 12px;
        border-bottom: 1px solid #edf2f7;
        color: #34465d;
        font-weight: 800;
    }

    .ch-score {
        min-width: 54px;
        display: inline-flex;
        justify-content: center;
        border-radius: 999px;
        padding: 5px 9px;
        font-weight: 900;
        font-size: 12px;
    }

    .ch-score.high {
        color: #0f7a48;
        background: #e7f8ef;
    }

    .ch-score.mid {
        color: #9a6a08;
        background: #fff3d6;
    }

    .ch-score.low {
        color: #a33a2e;
        background: #ffe7e3;
    }

    .ch-team-table {
        width: 100%;
        border-collapse: collapse;
    }

    .ch-team-table th {
        color: #62748a;
        font-size: 12px;
        text-transform: uppercase;
        padding: 12px 10px;
        border-bottom: 1px solid #edf2f7;
    }

    .ch-team-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #edf2f7;
        vertical-align: middle;
        color: #34465d;
    }

    .ch-team-name {
        font-weight: 900;
        color: #24324a;
    }

    .ch-fit-pill {
        display: inline-flex;
        min-width: 72px;
        justify-content: center;
        border-radius: 999px;
        padding: 6px 10px;
        font-size: 12px;
        font-weight: 900;
        color: #24324a;
        background: #edf4ff;
    }

    .ch-view-panel {
        display: none;
    }

    .ch-view-panel.active {
        display: block;
    }

    @media (max-width: 992px) {
        .ch-profile-hero {
            grid-template-columns: auto minmax(0, 1fr);
        }

        .ch-profile-status {
            grid-column: 1 / -1;
            justify-self: start;
            text-align: left;
        }
    }

    @media (max-width: 768px) {
        .ch-profile-hero {
            grid-template-columns: 1fr;
        }

        .ch-skill-row {
            grid-template-columns: 1fr;
            gap: 6px;
        }

        .ch-team-table-wrap,
        .ch-heatmap-wrap {
            overflow-x: auto;
        }
    }
</style>

<?php $puedeConfigurarPermisosPuesto = !empty($puedeConfigurarPermisosPuesto ?? false); ?>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y ch-perfiles-page">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h4 class="page-title mb-1">
                    <i class="fa-solid fa-id-card-clip me-2 text-primary"></i>Perfiles de puesto
                </h4>
                <p class="text-muted mb-0">
                    Matriz de competencias y puestos para validación de Capital Humano.
                    <span id="chMetaResumen" class="ms-1 text-muted"></span>
                </p>
            </div>
            <span class="badge rounded-pill bg-label-warning text-warning fw-bold px-3 py-2">
                <i class="fa-solid fa-pen-ruler me-1"></i>Borrador
            </span>
        </div>

        <div class="ch-perfiles-toolbar p-3 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <ul class="nav ch-perfiles-tabs" role="tablist" aria-label="Vistas de perfiles de puesto">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" type="button" data-ch-view="perfil">
                            <i class="fa-solid fa-user-tie me-2"></i>Mi perfil
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" type="button" data-ch-view="equipo">
                            <i class="fa-solid fa-table-list me-2"></i>Matriz
                        </button>
                    </li>
                    <?php if ($puedeConfigurarPermisosPuesto): ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" type="button" data-ch-view="permisos">
                                <i class="fa-solid fa-key me-2"></i>Permisos por puesto
                            </button>
                        </li>
                    <?php endif; ?>
                </ul>

                <div class="d-flex flex-wrap align-items-end gap-2">
                    <div style="min-width: 280px;">
                        <label for="chPerfilToggle" class="form-label text-muted fw-semibold mb-1">Perfil</label>
                        <div class="ch-search-select" id="chPerfilPicker">
                            <button type="button" class="form-select shadow-none text-start ch-search-select-toggle" id="chPerfilToggle" aria-expanded="false">
                                Selecciona perfil
                            </button>
                            <div class="ch-search-select-menu d-none" id="chPerfilMenu">
                                <input type="search" class="form-control shadow-none mb-2" id="chPerfilSearch" placeholder="Buscar perfil">
                                <div class="ch-search-select-options" id="chPerfilOptions"></div>
                            </div>
                        </div>
                        <select id="chPerfilSelect" class="d-none" aria-hidden="true" tabindex="-1"></select>
                    </div>
                    <button type="button" class="btn btn-outline-secondary" id="chBtnRestaurarPerfil">
                        <i class="fa-solid fa-rotate-left me-1"></i>Restablecer
                    </button>
                </div>
            </div>
        </div>

        <section class="ch-view-panel active" id="chPanelPerfil">
            <div class="ch-profile-hero mb-4">
                <div class="ch-profile-avatar" id="chPerfilIniciales">AP</div>
                <div>
                    <h2 class="ch-profile-name" id="chPerfilNombre">Analista Administración</h2>
                    <div class="ch-profile-meta">
                        <span><i class="fa-solid fa-sitemap"></i><b id="chPerfilDireccion">Administración</b></span>
                        <span><i class="fa-solid fa-layer-group"></i><b id="chPerfilArea">Administración</b></span>
                        <span><i class="fa-solid fa-building"></i><b id="chPerfilDepartamento">Administración</b></span>
                        <span><i class="fa-solid fa-ranking-star"></i>Nivel <b id="chPerfilNivel">Operativo</b></span>
                    </div>
                </div>
                <div class="ch-profile-status">
                    <small class="text-white-50 fw-bold">Campos completos</small>
                    <span class="status-number" id="chPerfilFit">86%</span>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xl-7">
                    <div class="ch-section-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa-solid fa-chart-line me-2 text-primary"></i>Competencias clave</h3>
                        </div>
                        <div class="card-body">
                            <div id="chHardSkills"></div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-5">
                    <div class="ch-section-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa-solid fa-bullseye me-2 text-primary"></i>Objetivo del puesto</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-0 fw-semibold text-body" id="chPerfilObjetivo"></p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="ch-section-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa-solid fa-user-check me-2 text-primary"></i>Soft skills</h3>
                        </div>
                        <div class="card-body" id="chSoftSkills"></div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="ch-section-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Requisitos</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="text-muted small fw-bold text-uppercase mb-1">Formación</div>
                                <div class="fw-bold text-body" id="chPerfilFormacion"></div>
                            </div>
                            <div>
                                <div class="text-muted small fw-bold text-uppercase mb-1">Rango salarial</div>
                                <div class="fw-bold text-body" id="chPerfilSueldo"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="ch-section-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa-solid fa-gauge-high me-2 text-primary"></i>KPIs críticos</h3>
                        </div>
                        <div class="card-body">
                            <ul class="ch-kpi-list" id="chPerfilKpis"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ch-view-panel" id="chPanelEquipo">
            <div class="ch-perfiles-toolbar p-3 mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="chFiltroArea" class="form-label text-muted fw-semibold mb-1">Área</label>
                        <select id="chFiltroArea" class="form-select shadow-none"></select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="chFiltroNivel" class="form-label text-muted fw-semibold mb-1">Nivel</label>
                        <select id="chFiltroNivel" class="form-select shadow-none"></select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="button" class="btn btn-primary w-100" id="chBtnAplicarEquipo">
                            <i class="fa-solid fa-filter me-1"></i>Aplicar filtros
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="ch-metric-card">
                        <span class="metric-label">Perfiles</span>
                        <div class="metric-value" id="chMetricColaboradores">0</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="ch-metric-card">
                        <span class="metric-label">Direcciones</span>
                        <div class="metric-value" id="chMetricFit">0%</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="ch-metric-card">
                        <span class="metric-label">Departamentos</span>
                        <div class="metric-value" id="chMetricBrechas">0</div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="ch-metric-card">
                        <span class="metric-label">Niveles</span>
                        <div class="metric-value" id="chMetricPlanes">0</div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-12 col-xl-5">
                    <div class="ch-section-card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa-solid fa-table-cells-large me-2 text-primary"></i>Mapa de competencias</h3>
                        </div>
                        <div class="card-body ch-heatmap-wrap">
                            <table class="ch-heatmap">
                                <thead>
                                    <tr>
                                        <th>Puesto</th>
                                        <th>Skills</th>
                                        <th>KPIs</th>
                                        <th>Competencias</th>
                                    </tr>
                                </thead>
                                <tbody id="chHeatmapBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-7">
                    <div class="ch-section-card">
                        <div class="card-header d-flex justify-content-between align-items-center gap-2">
                            <h3 class="card-title"><i class="fa-solid fa-list-check me-2 text-primary"></i>Detalle de puestos</h3>
                            <span class="badge bg-label-secondary text-secondary" id="chTeamCount">0 registros</span>
                        </div>
                        <div class="card-body ch-team-table-wrap">
                            <table class="ch-team-table">
                                <thead>
                                    <tr>
                                        <th>Puesto</th>
                                        <th>Dirección</th>
                                        <th>Departamento</th>
                                        <th>Área</th>
                                        <th>Nivel</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="chTeamBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ch-view-panel" id="chPanelPermisos">
            <div class="ch-section-card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h3 class="card-title mb-0">
                        <i class="fa-solid fa-key me-2 text-primary"></i>Permisos por puesto
                    </h3>
                    <span class="badge bg-label-secondary text-secondary" id="chPermisosResumen">Selecciona un puesto</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-12 col-lg-5">
                            <label for="chPermisosPuestoToggle" class="form-label text-muted fw-semibold mb-1">Puesto</label>
                            <div class="ch-search-select" id="chPermisosPuestoPicker">
                                <button type="button" class="form-select shadow-none text-start ch-search-select-toggle" id="chPermisosPuestoToggle" aria-expanded="false">
                                    Cargando puestos...
                                </button>
                                <div class="ch-search-select-menu d-none" id="chPermisosPuestoMenu">
                                    <input type="search" class="form-control shadow-none mb-2" id="chPermisosPuestoSearch" placeholder="Buscar puesto">
                                    <div class="ch-search-select-options" id="chPermisosPuestoOptions"></div>
                                </div>
                            </div>
                            <select id="chPermisosPuestoSelect" class="d-none" aria-hidden="true" tabindex="-1">
                                <option value="">Cargando puestos...</option>
                            </select>
                            <div class="small text-muted mt-2" id="chPermisosPuestoMeta"></div>
                        </div>
                        <div class="col-12 col-lg-7">
                            <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" id="chPermisosMarcarTodo">
                                    <i class="fa-solid fa-check-double me-1"></i>Marcar todo
                                </button>
                                <button type="button" class="btn btn-outline-warning" id="chPermisosLimpiar">
                                    <i class="fa-solid fa-eraser me-1"></i>Limpiar
                                </button>
                                <button type="button" class="btn btn-primary" id="chPermisosGuardar">
                                    <i class="fa-solid fa-floppy-disk me-1"></i>Guardar plantilla
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-search"></i></span>
                        <input type="search" class="form-control shadow-none" id="chPermisosBuscar" placeholder="Buscar modulo o permiso">
                    </div>

                    <div id="chPermisosModulos" class="row g-3">
                        <div class="col-12">
                            <div class="text-center text-muted py-4">Selecciona un puesto para configurar permisos.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
window.CH_PERFILES_PUESTOS_DATA = <?= $perfilesPuestosJson ?? '{"meta":{},"perfiles":[]}' ?>;
(() => {
    const sourceData = window.CH_PERFILES_PUESTOS_DATA || { meta: {}, perfiles: [] };

    function scoreForArray(items) {
        if (!Array.isArray(items) || !items.length) return 0;
        const total = items.reduce((sum, item) => sum + (Number(item.valor || item.score || 0) || 0), 0);
        return Math.round(total / items.length);
    }

    function completeness(perfil) {
        const checks = [
            perfil.nombre,
            perfil.direccion,
            perfil.area,
            perfil.departamento,
            perfil.nivel,
            perfil.sueldo,
            perfil.formacion,
            perfil.objetivo,
            Array.isArray(perfil.hard) && perfil.hard.length,
            Array.isArray(perfil.soft) && perfil.soft.length,
            Array.isArray(perfil.kpis) && perfil.kpis.length,
            Array.isArray(perfil.competencias) && perfil.competencias.length
        ];
        return Math.round((checks.filter(Boolean).length / checks.length) * 100);
    }

    function normalizarPerfiles(rows) {
        return (Array.isArray(rows) ? rows : []).map((perfil, index) => {
            const hard = Array.isArray(perfil.hard) ? perfil.hard.map((item) => [
                item.nombre || item.name || String(item || ''),
                Number(item.valor || item.score || 78)
            ]).filter((item) => item[0]) : [];
            const competencias = Array.isArray(perfil.competencias) ? perfil.competencias.map((item) => [
                item.nombre || item.name || String(item || ''),
                Number(item.valor || item.score || 78)
            ]).filter((item) => item[0]) : [];
            return {
                key: perfil.id || `perfil-${index}`,
                nombre: perfil.nombre || 'Puesto sin nombre',
                direccion: perfil.direccion || 'Sin dirección',
                area: perfil.area || 'Sin área',
                departamento: perfil.departamento || 'Sin departamento',
                nivel: perfil.nivel || 'Sin nivel',
                fit: completeness(perfil),
                objetivo: perfil.objetivo || 'Pendiente de validar.',
                formacion: perfil.formacion || 'Pendiente de validar.',
                sueldo: perfil.sueldo || 'Pendiente de validar.',
                hard: hard.length ? hard : competencias.slice(0, 4),
                soft: Array.isArray(perfil.soft) && perfil.soft.length ? perfil.soft : ['Pendiente de validar'],
                kpis: Array.isArray(perfil.kpis) && perfil.kpis.length ? perfil.kpis : ['Pendiente de validar'],
                competencias,
                herramientas: Array.isArray(perfil.herramientas) ? perfil.herramientas : []
            };
        });
    }

    const perfilesFallback = [
        {
            key: 'analista-administracion',
            nombre: 'Analista Administración',
            direccion: 'Administración',
            area: 'Administración',
            departamento: 'Administración',
            nivel: 'Operativo',
            fit: 86,
            objetivo: 'Asegurar el control documental y operativo de los procesos administrativos, manteniendo información confiable para la toma de decisiones.',
            formacion: 'Licenciatura en Administración, Contabilidad o carrera afín.',
            sueldo: '$14,000 - $18,000 MXN',
            hard: [
                ['Control documental', 92],
                ['Excel y reportería', 86],
                ['Seguimiento operativo', 80],
                ['Análisis de datos', 74]
            ],
            soft: ['Orden', 'Comunicación', 'Atención al detalle', 'Trabajo colaborativo'],
            kpis: ['Expedientes completos', 'Tiempos de respuesta', 'Incidencias administrativas', 'Calidad de reportes']
        },
        {
            key: 'gerente-tesoreria',
            nombre: 'Gerente Tesorería',
            direccion: 'Finanzas',
            area: 'Tesorería',
            departamento: 'Tesorería',
            nivel: 'Gerencial',
            fit: 91,
            objetivo: 'Gestionar flujo de efectivo, pagos y controles financieros para garantizar continuidad operativa y trazabilidad.',
            formacion: 'Licenciatura en Finanzas, Contabilidad o Economía.',
            sueldo: '$38,000 - $52,000 MXN',
            hard: [
                ['Flujo de efectivo', 95],
                ['Control bancario', 88],
                ['Negociación financiera', 84],
                ['Riesgo financiero', 78]
            ],
            soft: ['Liderazgo', 'Criterio financiero', 'Negociación', 'Confidencialidad'],
            kpis: ['Disponibilidad de efectivo', 'Pagos en tiempo', 'Conciliaciones correctas', 'Alertas de riesgo']
        },
        {
            key: 'coordinador-contabilidad',
            nombre: 'Coordinador Contabilidad',
            direccion: 'Finanzas',
            area: 'Contabilidad',
            departamento: 'Contabilidad',
            nivel: 'Coordinación',
            fit: 82,
            objetivo: 'Coordinar registros contables, cierres y soportes fiscales con control y oportunidad.',
            formacion: 'Licenciatura en Contabilidad con experiencia en cierres mensuales.',
            sueldo: '$24,000 - $32,000 MXN',
            hard: [
                ['Normativa contable', 90],
                ['Cierre mensual', 84],
                ['Impuestos', 78],
                ['Auditoria interna', 72]
            ],
            soft: ['Disciplina', 'Comunicación', 'Análisis', 'Responsabilidad'],
            kpis: ['Cierre contable', 'Errores de registro', 'Cumplimiento fiscal', 'Soportes auditables']
        },
        {
            key: 'director-operaciones',
            nombre: 'Director Operaciones',
            direccion: 'Operaciones',
            area: 'Atención al cliente',
            departamento: 'Atención al cliente',
            nivel: 'Dirección',
            fit: 94,
            objetivo: 'Dirigir la operación de servicio, productividad y calidad, alineando equipos con los objetivos de negocio.',
            formacion: 'Licenciatura concluida, deseable maestría o especialidad en operaciones.',
            sueldo: '$65,000 - $85,000 MXN',
            hard: [
                ['Estrategia operativa', 96],
                ['Gestión de indicadores', 92],
                ['Liderazgo de equipos', 90],
                ['Mejora continua', 86]
            ],
            soft: ['Visión estratégica', 'Liderazgo', 'Toma de decisiones', 'Negociación'],
            kpis: ['Nivel de servicio', 'Productividad', 'Calidad operativa', 'Rotación del equipo']
        }
    ];

    let perfiles = normalizarPerfiles(sourceData.perfiles);
    if (!perfiles.length) {
        perfiles = perfilesFallback;
    }

    const equipoBaseFallback = [
        { nombre: 'Rodrigo Marín Velázquez', puesto: 'Analista Administración', area: 'Administración', nivel: 'Operativo', fit: 86, brechas: 1, planes: 1, scores: [88, 72, 92] },
        { nombre: 'Diana Laura Torres', puesto: 'Gerente Tesorería', area: 'Tesorería', nivel: 'Gerencial', fit: 91, brechas: 0, planes: 2, scores: [86, 88, 91] },
        { nombre: 'Luis Alberto Hernández', puesto: 'Coordinador Contabilidad', area: 'Contabilidad', nivel: 'Coordinación', fit: 82, brechas: 2, planes: 1, scores: [80, 76, 85] },
        { nombre: 'Sandra Yunueth García', puesto: 'Director Operaciones', area: 'Atención al cliente', nivel: 'Dirección', fit: 94, brechas: 0, planes: 3, scores: [90, 96, 92] },
        { nombre: 'Karla Mendoza Ruiz', puesto: 'Analista Administración', area: 'Administración', nivel: 'Operativo', fit: 78, brechas: 2, planes: 1, scores: [74, 70, 82] },
        { nombre: '__SPARTA_SECRET_REDACTED__ Valdez Martínez', puesto: 'Gerente Tesorería', area: 'Tesorería', nivel: 'Gerencial', fit: 88, brechas: 1, planes: 2, scores: [84, 88, 86] }
    ];

    let equipoBase = perfiles.map((perfil) => ({
        key: perfil.key,
        nombre: perfil.nombre,
        direccion: perfil.direccion,
        departamento: perfil.departamento,
        area: perfil.area,
        nivel: perfil.nivel,
        fit: perfil.fit,
        brechas: [
            perfil.objetivo,
            perfil.formacion,
            perfil.sueldo,
            perfil.hard && perfil.hard.length,
            perfil.kpis && perfil.kpis.length
        ].filter(Boolean).length < 5 ? 1 : 0,
        planes: perfil.herramientas ? perfil.herramientas.length : 0,
        scores: [
            scoreForArray(perfil.hard),
            Math.min(95, 70 + (perfil.kpis ? perfil.kpis.length * 4 : 0)),
            scoreForArray(perfil.competencias)
        ].map((value) => value || 72)
    }));
    if (!equipoBase.length) {
        equipoBase = equipoBaseFallback;
    }

    const $ = (id) => document.getElementById(id);
    let permisosConfigLoaded = false;
    let permisosPuestos = [];
    let permisosModulos = [];
    let permisosSeleccionados = new Set();
    let permisosFiltro = '';
    let permisosPuestoFiltro = '';
    let perfilFiltro = '';

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function scoreClass(score) {
        if (score >= 86) return 'high';
        if (score >= 76) return 'mid';
        return 'low';
    }

    function initials(name) {
        return String(name || 'PP')
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map((part) => part.charAt(0))
            .join('')
            .toUpperCase() || 'PP';
    }

    function resumirSkill(texto) {
        let skill = String(texto || '').trim();
        skill = skill.replace(/\([^)]*\)/g, ' ');
        skill = skill.replace(/^(dominio|comprensión|conocimiento|manejo|experiencia|capacidad)\s+(avanzado|avanzada|intermedio|intermedia|básico|básica)?\s*(de|del|en)?\s*/i, '');
        skill = skill.replace(/^(técnica|sólida|corporativa)\s+/i, '');
        skill = skill.replace(/\s+/g, ' ').trim();
        const corte = skill.match(/^(.{1,54})(?:[,.;:]|\spara\s|\scon\s|\smediante\s)/i);
        if (corte && corte[1].length >= 18) {
            skill = corte[1].trim();
        }
        if (skill.length > 58) {
            skill = skill.slice(0, 55).trim() + '...';
        }
        return skill || 'Competencia';
    }

    function renderPerfil(key) {
        const perfil = perfiles.find((item) => item.key === key) || perfiles[0];
        $('chPerfilIniciales').textContent = initials(perfil.nombre);
        $('chPerfilNombre').textContent = perfil.nombre;
        $('chPerfilDireccion').textContent = perfil.direccion;
        $('chPerfilArea').textContent = perfil.area;
        $('chPerfilDepartamento').textContent = perfil.departamento;
        $('chPerfilNivel').textContent = perfil.nivel;
        $('chPerfilFit').textContent = `${perfil.fit}%`;
        $('chPerfilObjetivo').textContent = perfil.objetivo;
        $('chPerfilFormacion').textContent = perfil.formacion;
        $('chPerfilSueldo').textContent = perfil.sueldo;

        $('chHardSkills').innerHTML = perfil.hard.slice(0, 5).map(([skill, value]) => `
            <div class="ch-skill-row">
                <span class="ch-skill-name" title="${esc(skill)}">${esc(resumirSkill(skill))}</span>
                <div class="ch-progress" aria-label="${esc(skill)} ${value}%"><span style="width:${value}%"></span></div>
                <strong>${value}%</strong>
            </div>
        `).join('');

        $('chSoftSkills').innerHTML = perfil.soft.map((skill) => `
            <span class="ch-soft-skill"><i class="fa-solid fa-check"></i>${esc(skill)}</span>
        `).join('');

        $('chPerfilKpis').innerHTML = perfil.kpis.map((kpi) => `
            <li><i class="fa-solid fa-circle-check"></i><span>${esc(kpi)}</span></li>
        `).join('');
    }

    function equipoFiltrado() {
        const area = $('chFiltroArea').value;
        const nivel = $('chFiltroNivel').value;
        return equipoBase.filter((item) => {
            const okArea = area === '' || item.area === area;
            const okNivel = nivel === '' || item.nivel === nivel;
            return okArea && okNivel;
        });
    }

    function renderEquipo() {
        const data = equipoFiltrado();
        const direcciones = new Set(data.map((item) => item.direccion).filter(Boolean)).size;
        const departamentos = new Set(data.map((item) => item.departamento).filter(Boolean)).size;
        const niveles = new Set(data.map((item) => item.nivel).filter(Boolean)).size;

        $('chMetricColaboradores').textContent = data.length;
        $('chMetricFit').textContent = direcciones;
        $('chMetricBrechas').textContent = departamentos;
        $('chMetricPlanes').textContent = niveles;
        $('chTeamCount').textContent = `${data.length} registros`;

        $('chHeatmapBody').innerHTML = data.slice(0, 35).map((item) => `
            <tr>
                <td>${esc(item.nombre)}</td>
                <td><span class="ch-score ${scoreClass(item.scores[0])}">${item.scores[0]}%</span></td>
                <td><span class="ch-score ${scoreClass(item.scores[1])}">${item.scores[1]}%</span></td>
                <td><span class="ch-score ${scoreClass(item.scores[2])}">${item.scores[2]}%</span></td>
            </tr>
        `).join('') || '<tr><td colspan="4" class="text-center text-muted py-4">Sin registros</td></tr>';

        $('chTeamBody').innerHTML = data.map((item) => `
            <tr>
                <td>
                    <div class="ch-team-name">${esc(item.nombre)}</div>
                </td>
                <td>${esc(item.direccion || 'Sin dirección')}</td>
                <td>${esc(item.departamento)}</td>
                <td>${esc(item.area)}</td>
                <td><span class="ch-fit-pill">${esc(item.nivel)}</span></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary ch-open-profile" data-perfil-key="${esc(item.key)}" title="Ver perfil">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="6" class="text-center text-muted py-4">Sin registros</td></tr>';

        document.querySelectorAll('.ch-open-profile').forEach((btn) => {
            btn.addEventListener('click', () => {
                const key = btn.getAttribute('data-perfil-key');
                seleccionarPerfil(key);
                const tabPerfil = document.querySelector('[data-ch-view="perfil"]');
                if (tabPerfil) tabPerfil.click();
            });
        });
    }

    function chToast(icon, title, text) {
        if (window.Swal) {
            Swal.fire({ icon, title, text, confirmButtonColor: '#243653' });
            return;
        }
        alert([title, text].filter(Boolean).join('\n'));
    }

    function getPermisosPuestoActual() {
        const select = $('chPermisosPuestoSelect');
        return select ? Number(select.value || 0) : 0;
    }

    function normalizarTexto(value) {
        return String(value ?? '')
            .replace(/Ã¡/g, 'á')
            .replace(/Ã©/g, 'é')
            .replace(/Ã­/g, 'í')
            .replace(/Ã³/g, 'ó')
            .replace(/Ãº/g, 'ú')
            .replace(/Ã¼/g, 'ü')
            .replace(/Ã±/g, 'ñ')
            .replace(/Ã/g, 'Á')
            .replace(/Ã‰/g, 'É')
            .replace(/Ã/g, 'Í')
            .replace(/Ã“/g, 'Ó')
            .replace(/Ãš/g, 'Ú')
            .replace(/Ãœ/g, 'Ü')
            .replace(/Ã‘/g, 'Ñ')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    function etiquetaPerfil(perfil) {
        return `${perfil.nombre || 'Perfil'} - ${perfil.departamento || 'Sin departamento'}`;
    }

    function actualizarPerfilToggle() {
        const toggle = $('chPerfilToggle');
        const select = $('chPerfilSelect');
        if (!toggle || !select) return;

        const perfil = perfiles.find((item) => item.key === select.value) || perfiles[0];
        toggle.textContent = perfil ? etiquetaPerfil(perfil) : 'Selecciona perfil';
    }

    function renderPerfilOptions() {
        const options = $('chPerfilOptions');
        const select = $('chPerfilSelect');
        if (!options || !select) return;

        const filtro = normalizarTexto(perfilFiltro);
        const perfilesFiltrados = perfiles.filter((perfil) => {
            const texto = normalizarTexto(`${etiquetaPerfil(perfil)} ${perfil.direccion || ''} ${perfil.area || ''}`);
            return filtro === '' || texto.includes(filtro);
        });

        options.innerHTML = perfilesFiltrados.map((perfil) => {
            const active = perfil.key === select.value ? 'active' : '';
            return `
                <button type="button" class="ch-search-select-option ${active}" data-perfil-key="${esc(perfil.key)}">
                    <span class="d-block">${esc(etiquetaPerfil(perfil))}</span>
                    <small class="text-muted">${esc(perfil.direccion || 'Sin direccion')} / ${esc(perfil.area || 'Sin area')}</small>
                </button>
            `;
        }).join('') || '<div class="text-muted text-center py-3">Sin coincidencias</div>';
    }

    function setPerfilMenu(open) {
        const menu = $('chPerfilMenu');
        const toggle = $('chPerfilToggle');
        const search = $('chPerfilSearch');
        if (!menu || !toggle) return;

        menu.classList.toggle('d-none', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            perfilFiltro = '';
            if (search) search.value = '';
            renderPerfilOptions();
            window.setTimeout(() => search && search.focus(), 0);
        }
    }

    function seleccionarPerfil(key) {
        const select = $('chPerfilSelect');
        if (!select) return;

        select.value = key || (perfiles[0] ? perfiles[0].key : '');
        actualizarPerfilToggle();
        renderPerfilOptions();
        setPerfilMenu(false);
        renderPerfil(select.value);
    }

    function etiquetaPermisosPuesto(puesto) {
        return `${puesto.nombre || 'Puesto'} - ${puesto.departamento || 'Sin departamento'}`;
    }

    function actualizarPermisosPuestoToggle() {
        const toggle = $('chPermisosPuestoToggle');
        if (!toggle) return;

        const idPuesto = getPermisosPuestoActual();
        const puesto = permisosPuestos.find((item) => Number(item.id) === idPuesto);
        toggle.textContent = puesto ? etiquetaPermisosPuesto(puesto) : (permisosPuestos.length ? 'Selecciona un puesto' : 'Cargando puestos...');
    }

    function renderPermisosPuestoOptions() {
        const options = $('chPermisosPuestoOptions');
        if (!options) return;

        const selectedId = getPermisosPuestoActual();
        const filtro = normalizarTexto(permisosPuestoFiltro);
        const puestosFiltrados = permisosPuestos.filter((puesto) => {
            const texto = normalizarTexto(`${etiquetaPermisosPuesto(puesto)} ${puesto.direccion || ''} ${puesto.area || ''}`);
            return filtro === '' || texto.includes(filtro);
        });

        if (!permisosPuestos.length) {
            options.innerHTML = '<div class="text-muted text-center py-3">Cargando puestos...</div>';
            return;
        }

        const placeholder = `
            <button type="button" class="ch-search-select-option ${selectedId ? '' : 'active'}" data-id="">
                Selecciona un puesto
            </button>
        `;

        const filas = puestosFiltrados.map((puesto) => {
            const id = Number(puesto.id);
            const active = id === selectedId ? 'active' : '';
            return `
                <button type="button" class="ch-search-select-option ${active}" data-id="${esc(id)}">
                    <span class="d-block">${esc(etiquetaPermisosPuesto(puesto))}</span>
                    <small class="text-muted">${esc(puesto.direccion || 'Sin direccion')} / ${esc(puesto.area || 'Sin area')}</small>
                </button>
            `;
        }).join('');

        options.innerHTML = placeholder + (filas || '<div class="text-muted text-center py-3">Sin coincidencias</div>');
    }

    function setPermisosPuestoMenu(open) {
        const menu = $('chPermisosPuestoMenu');
        const toggle = $('chPermisosPuestoToggle');
        const search = $('chPermisosPuestoSearch');
        if (!menu || !toggle) return;

        menu.classList.toggle('d-none', !open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (open) {
            permisosPuestoFiltro = '';
            if (search) search.value = '';
            renderPermisosPuestoOptions();
            window.setTimeout(() => search && search.focus(), 0);
        }
    }

    function seleccionarPermisosPuesto(idPuesto) {
        const select = $('chPermisosPuestoSelect');
        if (!select) return;

        select.value = idPuesto ? String(idPuesto) : '';
        actualizarPermisosPuestoToggle();
        renderPermisosPuestoOptions();
        setPermisosPuestoMenu(false);
        cargarPermisosPuesto(Number(idPuesto || 0));
    }

    async function cargarPermisosPuestoConfig() {
        if (permisosConfigLoaded) {
            return;
        }

        $('chPermisosModulos').innerHTML = '<div class="col-12"><div class="text-center text-muted py-4"><i class="fa-solid fa-spinner fa-spin me-2"></i>Cargando permisos...</div></div>';

        try {
            const response = await fetch('/caphum/getPermisosPuestoConfig', { headers: { Accept: 'application/json' } });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.mensaje || 'No se pudo cargar la configuracion.');
            }

            const data = json.datos || {};
            permisosPuestos = Array.isArray(data.puestos) ? data.puestos : [];
            permisosModulos = Array.isArray(data.modulos) ? data.modulos : [];
            permisosSeleccionados = new Set((data.seleccionados || []).map((id) => Number(id)));
            permisosConfigLoaded = true;
            renderPermisosPuestoSelect();
            renderPermisosModulos();
        } catch (error) {
            $('chPermisosPuestoSelect').innerHTML = '<option value="">No se pudieron cargar puestos</option>';
            const toggle = $('chPermisosPuestoToggle');
            if (toggle) toggle.textContent = 'No se pudieron cargar puestos';
            const options = $('chPermisosPuestoOptions');
            if (options) options.innerHTML = '<div class="text-muted text-center py-3">No se pudieron cargar puestos.</div>';
            $('chPermisosModulos').innerHTML = `<div class="col-12"><div class="alert alert-danger mb-0">${esc(error.message || 'Error al cargar permisos.')}</div></div>`;
        }
    }

    function renderPermisosPuestoSelect() {
        const select = $('chPermisosPuestoSelect');
        if (!select) return;

        select.innerHTML = '<option value="">Selecciona un puesto</option>' + permisosPuestos.map((puesto) => {
            const etiqueta = `${puesto.nombre || 'Puesto'} - ${puesto.departamento || 'Sin departamento'}`;
            return `<option value="${esc(puesto.id)}">${esc(etiqueta)}</option>`;
        }).join('');
        actualizarPermisosPuestoToggle();
        renderPermisosPuestoOptions();
        renderPermisosMeta();
    }

    async function cargarPermisosPuesto(idPuesto) {
        if (!idPuesto) {
            permisosSeleccionados = new Set();
            renderPermisosMeta();
            renderPermisosModulos();
            return;
        }

        $('chPermisosResumen').textContent = 'Cargando...';
        try {
            const response = await fetch('/caphum/getPermisosPuesto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ id_puesto: idPuesto })
            });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.mensaje || 'No se pudieron cargar los permisos del puesto.');
            }
            permisosSeleccionados = new Set(((json.datos || {}).seleccionados || []).map((id) => Number(id)));
            renderPermisosMeta();
            renderPermisosModulos();
        } catch (error) {
            chToast('error', 'No se pudo cargar', error.message || 'Error al cargar permisos del puesto.');
            renderPermisosMeta();
            renderPermisosModulos();
        }
    }

    function renderPermisosMeta() {
        const idPuesto = getPermisosPuestoActual();
        const puesto = permisosPuestos.find((item) => Number(item.id) === idPuesto);
        const total = permisosSeleccionados.size;

        $('chPermisosResumen').textContent = idPuesto ? `${total} permisos seleccionados` : 'Selecciona un puesto';
        $('chPermisosPuestoMeta').textContent = puesto
            ? `${puesto.direccion || 'Sin direccion'} / ${puesto.area || 'Sin area'} / ${puesto.departamento || 'Sin departamento'}`
            : '';
    }

    function renderPermisosModulos() {
        const container = $('chPermisosModulos');
        if (!container) return;

        const idPuesto = getPermisosPuestoActual();
        if (!idPuesto) {
            container.innerHTML = '<div class="col-12"><div class="text-center text-muted py-4 border rounded">Selecciona un puesto para configurar permisos.</div></div>';
            return;
        }

        const filtro = normalizarTexto(permisosFiltro);
        const modulos = permisosModulos.filter((modulo) => {
            const texto = normalizarTexto(`${modulo.modulo_nombre || ''} ${modulo.pestana || ''} ${modulo.descripcion || ''}`);
            return filtro === '' || texto.includes(filtro);
        });

        if (!modulos.length) {
            container.innerHTML = '<div class="col-12"><div class="text-center text-muted py-4 border rounded">No se encontraron permisos con ese filtro.</div></div>';
            return;
        }

        container.innerHTML = modulos.map((modulo) => {
            const id = Number(modulo.id);
            const checked = permisosSeleccionados.has(id) ? 'checked' : '';
            return `
                <div class="col-12 col-md-6 col-xl-4">
                    <label class="border rounded p-3 h-100 d-flex gap-3 align-items-start bg-white">
                        <input type="checkbox" class="form-check-input mt-1 ch-perm-check" value="${esc(id)}" ${checked}>
                        <span class="d-block">
                            <span class="fw-bold d-block">${esc(modulo.pestana || modulo.modulo_nombre || 'Permiso')}</span>
                            <span class="badge bg-label-secondary text-secondary my-2">${esc(modulo.modulo_nombre || 'Modulo')}</span>
                            <small class="text-muted d-block">${esc(modulo.descripcion || 'Sin descripcion')}</small>
                        </span>
                    </label>
                </div>
            `;
        }).join('');

        document.querySelectorAll('.ch-perm-check').forEach((check) => {
            check.addEventListener('change', () => {
                const id = Number(check.value);
                if (check.checked) {
                    permisosSeleccionados.add(id);
                } else {
                    permisosSeleccionados.delete(id);
                }
                renderPermisosMeta();
            });
        });
    }

    async function guardarPermisosPuesto() {
        const idPuesto = getPermisosPuestoActual();
        if (!idPuesto) {
            chToast('warning', 'Selecciona un puesto', 'Primero selecciona el puesto que recibira esta plantilla.');
            return;
        }

        const btn = $('chPermisosGuardar');
        const original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Guardando...';

        try {
            const response = await fetch('/caphum/guardarPermisosPuesto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ id_puesto: idPuesto, modulos: [...permisosSeleccionados] })
            });
            const json = await response.json();
            if (!json.success) {
                throw new Error(json.mensaje || 'No se pudo guardar la plantilla.');
            }
            permisosSeleccionados = new Set(((json.datos || {}).seleccionados || [...permisosSeleccionados]).map((id) => Number(id)));
            renderPermisosMeta();
            renderPermisosModulos();
            chToast('success', 'Listo', 'Plantilla de permisos guardada.');
        } catch (error) {
            chToast('error', 'No se pudo guardar', error.message || 'Error al guardar permisos.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = original;
        }
    }

    function initSelects() {
        const meta = sourceData.meta || {};
        if ($('chMetaResumen')) {
            const total = meta.perfiles || perfiles.length;
            const areas = meta.areas || new Set(perfiles.map((item) => item.area)).size;
            const departamentos = meta.departamentos || new Set(perfiles.map((item) => item.departamento)).size;
            $('chMetaResumen').textContent = `(${total} perfiles, ${areas} áreas, ${departamentos} departamentos)`;
        }

        $('chPerfilSelect').innerHTML = perfiles.map((perfil) => `
            <option value="${esc(perfil.key)}">${esc(perfil.nombre)} · ${esc(perfil.departamento)}</option>
        `).join('');

        actualizarPerfilToggle();
        renderPerfilOptions();

        const areas = [...new Set(equipoBase.map((item) => item.area))].sort();
        const niveles = [...new Set(equipoBase.map((item) => item.nivel))].sort();
        $('chFiltroArea').innerHTML = '<option value="">Todas las áreas</option>' + areas.map((area) => `<option value="${esc(area)}">${esc(area)}</option>`).join('');
        $('chFiltroNivel').innerHTML = '<option value="">Todos los niveles</option>' + niveles.map((nivel) => `<option value="${esc(nivel)}">${esc(nivel)}</option>`).join('');
    }

    function bindEvents() {
        document.querySelectorAll('[data-ch-view]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const view = btn.getAttribute('data-ch-view');
                document.querySelectorAll('[data-ch-view]').forEach((item) => item.classList.toggle('active', item === btn));
                $('chPanelPerfil').classList.toggle('active', view === 'perfil');
                $('chPanelEquipo').classList.toggle('active', view === 'equipo');
                $('chPanelPermisos').classList.toggle('active', view === 'permisos');
                if (view === 'permisos') {
                    cargarPermisosPuestoConfig();
                }
            });
        });

        $('chPerfilSelect').addEventListener('change', (event) => {
            actualizarPerfilToggle();
            renderPerfilOptions();
            renderPerfil(event.target.value);
        });
        const perfilToggle = $('chPerfilToggle');
        if (perfilToggle) {
            perfilToggle.addEventListener('click', () => {
                const menu = $('chPerfilMenu');
                setPerfilMenu(menu ? menu.classList.contains('d-none') : true);
            });
        }
        const perfilSearch = $('chPerfilSearch');
        if (perfilSearch) {
            perfilSearch.addEventListener('input', (event) => {
                perfilFiltro = event.target.value || '';
                renderPerfilOptions();
            });
            perfilSearch.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setPerfilMenu(false);
            });
        }
        const perfilOptions = $('chPerfilOptions');
        if (perfilOptions) {
            perfilOptions.addEventListener('click', (event) => {
                const option = event.target.closest('[data-perfil-key]');
                if (!option) return;
                seleccionarPerfil(option.getAttribute('data-perfil-key'));
            });
        }
        document.addEventListener('click', (event) => {
            const picker = $('chPerfilPicker');
            if (picker && !picker.contains(event.target)) {
                setPerfilMenu(false);
            }
        });
        $('chBtnRestaurarPerfil').addEventListener('click', () => {
            seleccionarPerfil(perfiles[0].key);
        });
        $('chBtnAplicarEquipo').addEventListener('click', renderEquipo);
        $('chFiltroArea').addEventListener('change', renderEquipo);
        $('chFiltroNivel').addEventListener('change', renderEquipo);

        const puestoSelect = $('chPermisosPuestoSelect');
        if (puestoSelect) {
            puestoSelect.addEventListener('change', (event) => {
                actualizarPermisosPuestoToggle();
                renderPermisosPuestoOptions();
                cargarPermisosPuesto(Number(event.target.value || 0));
            });
        }
        const puestoToggle = $('chPermisosPuestoToggle');
        if (puestoToggle) {
            puestoToggle.addEventListener('click', () => {
                const menu = $('chPermisosPuestoMenu');
                setPermisosPuestoMenu(menu ? menu.classList.contains('d-none') : true);
            });
        }
        const puestoSearch = $('chPermisosPuestoSearch');
        if (puestoSearch) {
            puestoSearch.addEventListener('input', (event) => {
                permisosPuestoFiltro = event.target.value || '';
                renderPermisosPuestoOptions();
            });
            puestoSearch.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setPermisosPuestoMenu(false);
            });
        }
        const puestoOptions = $('chPermisosPuestoOptions');
        if (puestoOptions) {
            puestoOptions.addEventListener('click', (event) => {
                const option = event.target.closest('[data-id]');
                if (!option) return;
                seleccionarPermisosPuesto(Number(option.getAttribute('data-id') || 0));
            });
        }
        document.addEventListener('click', (event) => {
            const picker = $('chPermisosPuestoPicker');
            if (picker && !picker.contains(event.target)) {
                setPermisosPuestoMenu(false);
            }
        });
        $('chPermisosBuscar').addEventListener('input', (event) => {
            permisosFiltro = event.target.value || '';
            renderPermisosModulos();
        });
        $('chPermisosMarcarTodo').addEventListener('click', () => {
            if (!getPermisosPuestoActual()) return;
            permisosModulos.forEach((modulo) => permisosSeleccionados.add(Number(modulo.id)));
            renderPermisosMeta();
            renderPermisosModulos();
        });
        $('chPermisosLimpiar').addEventListener('click', () => {
            if (!getPermisosPuestoActual()) return;
            permisosSeleccionados = new Set();
            renderPermisosMeta();
            renderPermisosModulos();
        });
        $('chPermisosGuardar').addEventListener('click', guardarPermisosPuesto);
    }

    initSelects();
    bindEvents();
    renderPerfil(perfiles[0].key);
    renderEquipo();
})();
</script>
