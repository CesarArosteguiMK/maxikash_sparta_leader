<div class="container-fluid py-4 atlas-access-page">
    <style>
        .atlas-access-page { color:#22303e; }
        .atlas-access-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
        .atlas-access-title { display:flex; align-items:center; gap:.65rem; color:#22303e; font-size:1.35rem; font-weight:800; margin:0; }
        .atlas-access-title i { color:#26344e; }
        .atlas-access-subtitle { color:#6b7280; font-size:.88rem; font-weight:600; margin:.2rem 0 0; }
        .atlas-access-actions { display:flex; align-items:center; justify-content:flex-end; gap:.5rem; flex-wrap:wrap; }
        .atlas-access-actions .btn { min-height:2.25rem; display:inline-flex; align-items:center; justify-content:center; gap:.4rem; font-weight:700; }
        .atlas-access-summary { display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:.75rem; margin-bottom:1rem; }
        .atlas-access-kpi { border:1px solid #e2e8f0; border-radius:.65rem; background:#fff; padding:.85rem .95rem; min-height:5rem; }
        .atlas-access-kpi.is-clickable { cursor:pointer; transition:border-color .15s ease, background-color .15s ease; }
        .atlas-access-kpi.is-clickable:hover { border-color:#d09f48; background:#fffaf0; }
        .atlas-access-kpi-label { color:#64748b; font-size:.72rem; font-weight:900; text-transform:uppercase; letter-spacing:.025em; }
        .atlas-access-kpi-value { color:#22303e; font-size:1.55rem; font-weight:900; line-height:1.1; margin-top:.25rem; }
        .atlas-access-kpi-split { display:grid; grid-template-columns:1fr 1fr; align-items:center; gap:.65rem; }
        .atlas-access-kpi-split-item + .atlas-access-kpi-split-item { position:relative; padding-left:.8rem; }
        .atlas-access-kpi-split-item + .atlas-access-kpi-split-item::before { content:''; position:absolute; left:0; top:.15rem; bottom:.15rem; width:1px; background:linear-gradient(180deg, transparent, #dbe4ef 18%, #dbe4ef 82%, transparent); }
        .atlas-access-tabs { border-bottom:1px solid #e2e8f0; margin-bottom:1rem; gap:.35rem; flex-wrap:wrap; }
        .atlas-access-tabs .nav-link { border:0; border-bottom:3px solid transparent; color:#64748b; font-weight:800; padding:.62rem .85rem; }
        .atlas-access-tabs .nav-link.active { color:#173756; border-bottom-color:#2563eb; background:transparent; }
        .gestion-personal-name-cell { display:flex; align-items:flex-start; gap:.85rem; min-width:280px; }
        .gestion-personal-avatar { width:46px; height:46px; min-width:46px; border-radius:50%; object-fit:cover; border:2px solid #fff; box-shadow:0 5px 16px rgba(30,41,59,.16); background:#f1f5f9; transition:transform .15s ease, box-shadow .15s ease; }
        .gestion-personal-avatar-fallback { width:46px; height:46px; min-width:46px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:.82rem; font-weight:700; letter-spacing:0; border:2px solid #fff; box-shadow:0 5px 16px rgba(30,41,59,.16); background:linear-gradient(135deg,#24324d 0%,#0d6efd 100%); transition:transform .15s ease, box-shadow .15s ease; }
        .gestion-avatar-btn { border:0; background:transparent; padding:0; border-radius:999px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer; line-height:1; width:46px; height:46px; min-width:46px; flex:0 0 auto; }
        .gestion-avatar-btn:hover .gestion-personal-avatar,
        .gestion-avatar-btn:hover .gestion-personal-avatar-fallback { transform:translateY(-1px) scale(1.03); box-shadow:0 8px 22px rgba(30,41,59,.22); }
        .gestion-foto-stage { min-height:22rem; display:flex; align-items:center; justify-content:center; background:#f8fafc; border-radius:.75rem; overflow:hidden; }
        .gestion-foto-img { max-width:100%; max-height:70vh; object-fit:contain; }
        .gestion-foto-fallback { width:10rem; height:10rem; border-radius:999px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:2.4rem; font-weight:800; background:linear-gradient(135deg,#24324d 0%,#0d6efd 100%); }
        .atlas-access-credential-card { border:1px solid #e2e8f0; border-radius:.65rem; background:#f8fafc; padding:.9rem; }
        .atlas-access-module-list { border:0; border-radius:0; overflow:visible; }
        .atlas-access-module-list .modal-perfil-modulos-agrupados { display:flex; flex-wrap:wrap; gap:1rem; align-items:stretch; border:0; border-radius:0; overflow:visible; }
        .atlas-access-module-list .modal-perfil-modulo-grupo { flex:1 1 calc(50% - .5rem); max-width:calc(50% - .5rem); min-width:0; background:#fff; border:2px solid #000 !important; border-radius:.5rem !important; overflow:hidden; }
        .atlas-access-module-list .modal-perfil-modulo-grupo.is-wide { flex-basis:100%; max-width:100%; }
        .atlas-access-module-list .modal-perfil-modulo-columns { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:0; }
        .atlas-access-module-list .modal-perfil-modulo-columns .table + .table { border-left:1px solid #e9ecef; }
        .atlas-access-module-list .modal-perfil-modulo-grupo-header { background:rgba(26,82,168,.08); border-bottom:2px solid #000; cursor:pointer; color:#1e293b; }
        .atlas-access-module-list .modal-perfil-modulo-grupo-chevron { flex-shrink:0; font-size:.75rem; transition:transform .2s ease; }
        .atlas-access-module-list .modal-perfil-modulo-grupo-header[aria-expanded="false"] .modal-perfil-modulo-grupo-chevron { transform:rotate(-90deg); }
        .atlas-access-module-list .modal-perfil-modulo-master-wrap { text-align:right; }
        .atlas-access-module-list .modal-perfil-modulo-master-label { cursor:pointer; line-height:1.2; }
        .atlas-access-module-list .modal-perfil-modulo-master-cb { flex-shrink:0; width:1.1rem; height:1.1rem; margin-top:0; cursor:pointer; }
        .atlas-access-module-list .modal-perfil-modulo-fila { transition:background-color .2s ease, border-left-color .2s ease, transform .2s ease; cursor:pointer; border-left:3px solid transparent; border-bottom:1px solid #e9ecef; }
        .atlas-access-module-list .modal-perfil-modulo-fila:hover { background:#f8fafc; border-left-color:#1a52a8; transform:translateX(2px); }
        .atlas-access-module-list .modal-perfil-modulo-fila:last-child { border-bottom:0; }
        .atlas-access-module-list .modulo-icon-box { width:40px; height:40px; border-radius:10px; background:rgba(26,82,168,.12); border:1px solid rgba(26,82,168,.25); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .atlas-access-module-list .modulo-icon-box i { color:#1a52a8; font-size:1rem; }
        .atlas-access-module-list .modal-perfil-modulo-item-cb { cursor:pointer; width:1.1em; height:1.1em; }
        .atlas-access-action-btn { width:2.1rem; height:2.1rem; display:inline-flex; align-items:center; justify-content:center; border-radius:999px; }
        .atlas-access-mobile-card { display:flex; align-items:center; justify-content:space-between; gap:1rem; border:1px solid #dbeafe; border-radius:.65rem; background:#f8fbff; padding:.85rem .95rem; margin-top:.9rem; }
        .atlas-access-mobile-title { color:#22303e; font-weight:800; line-height:1.2; }
        .atlas-access-mobile-sub { color:#64748b; font-size:.76rem; font-weight:600; margin-top:.12rem; }
        .atlas-access-reset-card { border:1px solid #fde7bd; border-radius:.65rem; background:#fffaf0; padding:.85rem .95rem; margin-top:.9rem; }
        .atlas-access-reset-title { color:#22303e; font-weight:800; line-height:1.2; margin-bottom:.65rem; }
        .atlas-access-reset-card .btn { font-weight:800; }
        .atlas-access-reset-launch { display:flex; align-items:center; justify-content:space-between; gap:1rem; border:1px solid #fde7bd; border-radius:.65rem; background:#fffaf0; padding:.85rem .95rem; margin-top:.9rem; }
        .sede-glass-badge { background:rgba(255,255,255,.7); backdrop-filter:blur(6px); -webkit-backdrop-filter:blur(6px); border:1px solid rgba(0,0,0,.06); border-radius:6px; }
        .atlas-access-permission-modal .modal-content { border:0; box-shadow:0 10px 40px rgba(0,0,0,.12); overflow:hidden; }
        .atlas-access-permission-modal .modal-header { background:#26344e; border-bottom:1px solid rgba(255,255,255,.18); padding:1rem 1.25rem; }
        .atlas-access-permission-modal .modal-title,
        .atlas-access-permission-modal .modal-title i { color:#fff; }
        #modalAtlasAccessPermisos .modal-header { background:#fff; border-bottom:1px solid #e2e8f0; }
        #modalAtlasAccessPermisos .modal-title,
        #modalAtlasAccessPermisos .modal-title i { color:#1e293b; }
        #modalAtlasAccessPermisos #atlasAccessPermisosSubtitle { color:#64748b !important; }
        #modalAtlasAccessPermisos .modal-content { height:82vh; max-height:82vh; display:flex; flex-direction:column; }
        #modalAtlasAccessPermisos .modal-header,
        #modalAtlasAccessPermisos .modal-footer { flex:0 0 auto; }
        #modalAtlasAccessPermisos .modal-body { flex:1 1 auto; min-height:0; display:flex; flex-direction:column; overflow:hidden; }
        .atlas-access-permission-modal .modal-body { padding:0; }
        .atlas-access-permission-tabs { border-bottom:2px solid #e9ecef; padding:.85rem 1.5rem 0; gap:.25rem; }
        .atlas-access-permission-tabs .nav-link { border:0; border-bottom:3px solid transparent; border-radius:0; color:#64748b; font-weight:800; padding:.7rem 1rem; }
        .atlas-access-permission-tabs .nav-link.active { color:#26344e; background:transparent; border-bottom-color:#26344e; }
        #modalAtlasAccessPermisos .atlas-access-permission-tabs { flex:0 0 auto; }
        #modalAtlasAccessPermisos .atlas-access-permission-content { flex:1 1 auto; min-height:0; }
        .atlas-access-permission-content { padding:1.25rem 1.5rem; max-height:64vh; overflow:auto; }
        #modalAtlasAccessPermisos .atlas-access-permission-content { max-height:none; }
        @media (max-width: 767.98px) {
            .atlas-access-head,
            .atlas-access-actions { align-items:stretch; flex-direction:column; }
            .atlas-access-summary { grid-template-columns:1fr; }
            .atlas-access-actions .btn { width:100%; max-width:none; }
            .atlas-access-module-list .modal-perfil-modulo-grupo { flex:1 1 100%; max-width:100%; }
            .atlas-access-module-list .modal-perfil-modulo-columns { grid-template-columns:1fr; }
            .atlas-access-module-list .modal-perfil-modulo-columns .table + .table { border-left:0; border-top:1px solid #e9ecef; }
        }
    </style>

    <div class="atlas-access-head">
        <div>
            <h1 class="atlas-access-title"><i class="fa-solid fa-user-shield"></i><span>Accesos Atlas</span></h1>
            <p class="atlas-access-subtitle">Catalogo base de usuarios de Comercial Mexico para administrar permisos de Atlas.</p>
        </div>
        <div class="atlas-access-actions">
            <button class="btn btn-label-primary" type="button" data-atlas-access-template title="Descargar plantilla completa">
                <i class="fa-solid fa-file-excel"></i><span>Plantilla completa</span>
            </button>
            <button class="btn btn-info text-white" type="button" data-atlas-access-import title="Cargar plantilla llena">
                <i class="fa-solid fa-file-arrow-up"></i><span>Cargar plantilla</span>
            </button>
            <button class="btn btn-label-secondary" type="button" data-atlas-access-refresh title="Actualizar">
                <i class="fa-solid fa-rotate"></i><span>Actualizar</span>
            </button>
            <button class="btn btn-primary" type="button" data-atlas-access-sync title="Sincronizar Comercial Mexico">
                <i class="fa-solid fa-users-gear"></i><span>Sincronizar usuarios</span>
            </button>
        </div>
    </div>

    <div class="atlas-access-summary">
        <div class="atlas-access-kpi">
            <div class="atlas-access-kpi-split">
                <div class="atlas-access-kpi-split-item">
                    <div class="atlas-access-kpi-label">Activos</div>
                    <div class="atlas-access-kpi-value" id="atlasAccessActivos">0</div>
                </div>
                <div class="atlas-access-kpi-split-item">
                    <div class="atlas-access-kpi-label">Inactivos</div>
                    <div class="atlas-access-kpi-value" id="atlasAccessInactivos">0</div>
                </div>
            </div>
        </div>
        <div class="atlas-access-kpi is-clickable" data-atlas-access-filter="operativos">
            <div class="atlas-access-kpi-label">Operativos</div>
            <div class="atlas-access-kpi-value" id="atlasAccessOperativos">0</div>
        </div>
        <div class="atlas-access-kpi is-clickable" data-atlas-access-filter="excluidos">
            <div class="atlas-access-kpi-label">Excluidos</div>
            <div class="atlas-access-kpi-value" id="atlasAccessExcluidos">0</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap mb-3">
                <div>
                    <h5 class="fw-bold mb-1" id="atlasAccessTablaTitulo"><i class="fa-solid fa-address-book me-2"></i>Operativos</h5>
                    <div class="text-muted small fw-semibold" id="atlasAccessSyncInfo">Pendiente de cargar.</div>
                </div>
                <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
                    <select id="atlasAccessFiltroVista" class="form-select form-select-sm" style="width: 12rem;">
                        <option value="todos">Todos</option>
                        <option value="operativos" selected>Operativos</option>
                        <option value="excluidos">Excluidos</option>
                    </select>
                    <button type="button" class="btn btn-label-warning btn-sm" data-atlas-access-excluir disabled>
                        <i class="fa-solid fa-user-slash me-1"></i>Excluir seleccionados
                    </button>
                    <button type="button" class="btn btn-label-success btn-sm" data-atlas-access-reintegrar disabled>
                        <i class="fa-solid fa-user-check me-1"></i>Reincorporar
                    </button>
                </div>
            </div>
            <div class="card-datatable table-responsive">
                <table id="atlasAccessTabla" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th class="text-center"><input class="form-check-input" type="checkbox" id="atlasAccessSelectAll"></th>
                            <th>Usuario</th>
                            <th>Sede</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="atlasAccessRows">
                        <tr><td colspan="4" class="text-center text-muted fw-semibold py-4">Cargando usuarios...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGestionFotoUsuario" tabindex="-1" aria-labelledby="gestionFotoVisorNombre" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-bold" id="gestionFotoVisorNombre"><i class="fa-solid fa-id-badge me-2"></i>Usuario</h5>
                        <div class="text-muted small fw-semibold" id="gestionFotoVisorSubtitulo">Foto de perfil</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="gestion-foto-stage">
                        <img id="gestionFotoVisorImg" class="gestion-foto-img d-none" src="" alt="">
                        <div id="gestionFotoVisorFallback" class="gestion-foto-fallback d-none">US</div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade atlas-access-permission-modal" id="modalAtlasAccessPermisos" tabindex="-1" aria-labelledby="atlasAccessPermisosTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <h5 class="modal-title fw-bold mb-1" id="atlasAccessPermisosTitle"><i class="fa-solid fa-user-shield me-2"></i>Accesos Atlas</h5>
                            <div class="text-white-50 small fw-semibold" id="atlasAccessPermisosSubtitle">Usuario y permisos Atlas</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="atlasAccessPermisosId">
                    <ul class="nav nav-tabs atlas-access-permission-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#atlasAccessTabCuenta" role="tab">
                                <i class="fa-solid fa-user-shield me-1"></i>Cuenta
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#atlasAccessTabModulos" role="tab">
                                <i class="fa-solid fa-map-location-dot me-1"></i>Modulos Atlas
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#atlasAccessTabEspeciales" role="tab">
                                <i class="fa-solid fa-shield-halved me-1"></i>Permisos especiales
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content atlas-access-permission-content">
                        <div class="tab-pane fade show active" id="atlasAccessTabCuenta" role="tabpanel">
                            <div class="atlas-access-credential-card">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold">Usuario</label>
                                        <input type="text" class="form-control" id="atlasAccessUsuario" readonly>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label fw-bold">Contrasena</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="atlasAccessPassword" readonly>
                                            <button type="button" class="btn btn-outline-secondary" data-atlas-access-toggle-password title="Mostrar/ocultar contrasena">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="atlas-access-mobile-card">
                                    <div>
                                        <div class="atlas-access-mobile-title"><i class="fa-solid fa-mobile-screen-button me-2"></i>Acceso móvil</div>
                                        <div class="atlas-access-mobile-sub">Permite que el usuario opere desde la app Atlas.</div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" id="atlasAccessMovil">
                                    </div>
                                </div>
                                <div class="atlas-access-reset-launch">
                                    <div>
                                        <div class="atlas-access-reset-title mb-1"><i class="fa-solid fa-rotate-left me-2"></i>Restablecer contrase&ntilde;a</div>
                                        <div class="atlas-access-mobile-sub">Actualiza la contrase&ntilde;a del usuario y deja el motivo registrado.</div>
                                    </div>
                                    <button type="button" class="btn btn-warning flex-shrink-0" data-atlas-access-open-reset>
                                        <i class="fa-solid fa-key me-1"></i>Restablecer contrase&ntilde;a
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="atlasAccessTabModulos" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <div>
                                    <h6 class="mb-1 fw-bold">Modulos Atlas</h6>
                                    <small class="text-muted">Gestiona los accesos operativos de Atlas.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" data-atlas-access-select-modules>
                                    <i class="fa-solid fa-check-double me-1"></i>Seleccionar todos
                                </button>
                            </div>
                            <div class="atlas-access-module-list" id="atlasAccessModulosSistema">
                                <div class="text-center text-muted fw-semibold py-4">Selecciona un usuario.</div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="atlasAccessTabEspeciales" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <div>
                                    <h6 class="mb-1 fw-bold">Permisos especiales</h6>
                                    <small class="text-muted">Controla permisos finos para operaciones Atlas.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" data-atlas-access-select-modules>
                                    <i class="fa-solid fa-check-double me-1"></i>Seleccionar todos
                                </button>
                            </div>
                            <div class="atlas-access-module-list" id="atlasAccessModulosEspeciales">
                                <div class="text-center text-muted fw-semibold py-4">Selecciona un usuario.</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" data-atlas-access-save-perms>
                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar
                    </button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade atlas-access-permission-modal" id="modalAtlasAccessResetPassword" tabindex="-1" aria-labelledby="atlasAccessResetTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <h5 class="modal-title fw-bold mb-1" id="atlasAccessResetTitle"><i class="fa-solid fa-key me-2"></i>Restablecer contrase&ntilde;a</h5>
                            <div class="text-white-50 small fw-semibold" id="atlasAccessResetSubtitle">Motivo y nueva contrase&ntilde;a</div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="atlas-access-permission-content">
                        <div class="atlas-access-credential-card">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-5">
                                    <label class="form-label fw-bold">Motivo</label>
                                    <select class="form-select" id="atlasAccessMotivoReset">
                                        <option value="">Selecciona motivo</option>
                                    </select>
                                </div>
                                <div class="col-12 col-lg-7">
                                    <label class="form-label fw-bold">Nueva contrase&ntilde;a</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="atlasAccessPasswordNueva" placeholder="Sugerida o capturada">
                                        <button type="button" class="btn btn-label-secondary" data-atlas-access-suggest-password>
                                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Sugerir contrase&ntilde;a
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-muted small fw-semibold mt-3">
                                La bit&aacute;cora guardar&aacute; el motivo, fecha y usuario que hizo el restablecimiento.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-end">
                    <button type="button" class="btn btn-primary" data-atlas-access-reset-password>
                        <i class="fa-solid fa-key me-1"></i>Restablecer
                    </button>
                    <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade atlas-access-permission-modal" id="modalAtlasAccessImport" tabindex="-1" aria-labelledby="atlasAccessImportTitle" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <form id="formAtlasAccessImport" enctype="multipart/form-data">
                    <div class="modal-header">
                        <div class="d-flex align-items-center w-100">
                            <div class="flex-grow-1">
                                <h5 class="modal-title fw-bold mb-1" id="atlasAccessImportTitle"><i class="fa-solid fa-file-arrow-up me-2"></i>Cargar plantilla de personal</h5>
                                <div class="text-white-50 small fw-semibold">Actualiza el catalogo usando el mismo Excel descargado.</div>
                            </div>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                    </div>
                    <div class="modal-body p-4">
                        <label class="form-label fw-bold">Archivo Excel</label>
                        <input type="file" class="form-control" name="archivo" accept=".xlsx,.xls" required>
                        <div class="text-muted small fw-semibold mt-2">
                            Se actualizan usuarios existentes por ID acceso, Persona ID o Numero empleado. Si una fila no existe, se reporta para revision.
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i>Cargar</button>
                        <button type="button" class="btn btn-label-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const AtlasAccess = {
            usuarios: [],
            filtro: '',
            vista: 'operativos',
            seleccionados: new Set(),
            detalleActual: null,
            reabrirPermisosTrasReset: false,

            init() {
                document.querySelector('[data-atlas-access-template]')?.addEventListener('click', () => this.descargarPlantilla());
                document.querySelector('[data-atlas-access-import]')?.addEventListener('click', () => this.abrirImportarPlantilla());
                document.getElementById('formAtlasAccessImport')?.addEventListener('submit', (ev) => this.importarPlantilla(ev));
                document.querySelector('[data-atlas-access-refresh]')?.addEventListener('click', () => this.cargar());
                document.querySelector('[data-atlas-access-sync]')?.addEventListener('click', () => this.sincronizar());
                document.querySelector('[data-atlas-access-excluir]')?.addEventListener('click', () => this.actualizarExclusion(true));
                document.querySelector('[data-atlas-access-reintegrar]')?.addEventListener('click', () => this.actualizarExclusion(false));
                document.querySelectorAll('[data-atlas-access-filter]').forEach(card => {
                    card.addEventListener('click', () => {
                        this.vista = card.getAttribute('data-atlas-access-filter') || 'operativos';
                        const select = document.getElementById('atlasAccessFiltroVista');
                        if (select) select.value = this.vista;
                        this.seleccionados.clear();
                        this.render();
                    });
                });
                document.getElementById('atlasAccessFiltroVista')?.addEventListener('change', (ev) => {
                    this.vista = ev.target.value || 'todos';
                    this.seleccionados.clear();
                    this.render();
                });
                document.getElementById('atlasAccessSelectAll')?.addEventListener('change', (ev) => this.toggleSeleccionTodos(ev.target.checked));
                document.getElementById('atlasAccessRows')?.addEventListener('change', (ev) => {
                    const input = ev.target && ev.target.matches('[data-atlas-access-select]') ? ev.target : null;
                    if (!input) return;
                    const id = String(input.getAttribute('data-atlas-access-select') || '');
                    if (input.checked) this.seleccionados.add(id);
                    else this.seleccionados.delete(id);
                    this.actualizarBotonesSeleccion();
                });
                document.getElementById('atlasAccessRows')?.addEventListener('click', (ev) => {
                    const btn = ev.target && ev.target.closest('[data-atlas-access-avatar]');
                    if (!btn) return;
                    this.abrirVisorFoto(btn, ev);
                });
                document.getElementById('atlasAccessRows')?.addEventListener('click', (ev) => {
                    const btn = ev.target && ev.target.closest('[data-atlas-access-config]');
                    if (!btn) return;
                    ev.preventDefault();
                    ev.stopPropagation();
                    this.abrirPermisos(btn.getAttribute('data-atlas-access-config'));
                });
                document.querySelectorAll('#atlasAccessModulosSistema, #atlasAccessModulosEspeciales').forEach(container => {
                    container.addEventListener('click', (ev) => {
                        if (ev.target && ev.target.closest('.modal-perfil-modulo-master-wrap')) {
                            ev.stopPropagation();
                        }
                    });
                    container.addEventListener('change', (ev) => {
                        const master = ev.target && ev.target.matches('[data-atlas-access-module-master]') ? ev.target : null;
                        const item = ev.target && ev.target.matches('[data-atlas-access-module]') ? ev.target : null;
                        if (master) {
                            this.toggleGrupoModulos(master);
                            return;
                        }
                        if (item) {
                            this.actualizarEstadoModulo(item);
                            this.actualizarMastersModulos();
                        }
                    });
                });
                document.querySelector('[data-atlas-access-toggle-password]')?.addEventListener('click', () => this.togglePassword());
                document.querySelector('[data-atlas-access-save-perms]')?.addEventListener('click', () => this.guardarPermisos());
                document.querySelectorAll('[data-atlas-access-select-modules]').forEach((btn) => {
                    btn.addEventListener('click', () => this.seleccionarTodosModulos());
                });
                document.querySelector('[data-atlas-access-open-reset]')?.addEventListener('click', () => this.abrirResetPassword());
                document.querySelector('[data-atlas-access-suggest-password]')?.addEventListener('click', () => this.sugerirPassword());
                document.querySelector('[data-atlas-access-reset-password]')?.addEventListener('click', () => this.restablecerPassword());
                document.getElementById('modalAtlasAccessResetPassword')?.addEventListener('hidden.bs.modal', () => this.reabrirModalPermisosSiAplica());
                this.cargar();
            },

            async getJson(url) {
                const res = await fetch(url, { credentials: 'same-origin' });
                return await res.json();
            },

            async postJson(url, payload = {}) {
                const res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                return await res.json();
            },

            cerrarLoaderGlobal() {
                if (typeof Swal !== 'undefined' && Swal && typeof Swal.close === 'function') {
                    Swal.close();
                }
            },

            descargarPlantilla() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Procesando su peticion',
                        text: 'Estamos preparando la plantilla completa de personal...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });
                    setTimeout(() => this.cerrarLoaderGlobal(), 1800);
                }
                window.location.href = '/Atlas/descargarPlantillaAccesosAtlas';
            },

            abrirImportarPlantilla() {
                const form = document.getElementById('formAtlasAccessImport');
                if (form) form.reset();
                const modal = document.getElementById('modalAtlasAccessImport');
                if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).show();
            },

            async importarPlantilla(ev) {
                ev.preventDefault();
                const form = ev.currentTarget;
                const data = new FormData(form);
                if (!data.get('archivo')) {
                    this.toast('Selecciona un archivo Excel.', 'warning');
                    return;
                }
                if (typeof showWait === 'function') {
                    showWait();
                }
                try {
                    const res = await fetch('/Atlas/importarPlantillaAccesosAtlas', {
                        method: 'POST',
                        body: data
                    });
                    const json = await res.json();
                    this.cerrarLoaderGlobal();
                    if (!json.success) {
                        this.toast(json.mensaje || 'No se pudo cargar la plantilla.', 'error');
                        return;
                    }
                    const d = json.datos || {};
                    const errores = Array.isArray(d.errores) && d.errores.length
                        ? `<div class="text-start mt-2 small">${d.errores.slice(0, 8).map(e => `<div>${this.escape(e)}</div>`).join('')}</div>`
                        : '';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Plantilla procesada',
                            html: `
                                <div class="fw-semibold">Leidos: ${d.leidos || 0}</div>
                                <div class="fw-semibold">Actualizados: ${d.actualizados || 0}</div>
                                <div class="fw-semibold">Sin cambios: ${d.sin_cambios || 0}</div>
                                <div class="fw-semibold">No encontrados: ${d.no_encontrados || 0}</div>
                                ${errores}
                            `,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        this.toast(json.mensaje || 'Plantilla procesada.', 'success');
                    }
                    const modal = document.getElementById('modalAtlasAccessImport');
                    if (modal && window.bootstrap) bootstrap.Modal.getOrCreateInstance(modal).hide();
                    await this.cargar(false);
                } catch (err) {
                    this.cerrarLoaderGlobal();
                    this.toast('No se pudo cargar la plantilla.', 'error');
                }
            },

            async cargar(mostrarLoader = true) {
                let loaderActivo = false;
                let toastPendiente = null;
                this.setLoading('Cargando catalogo...');
                if (mostrarLoader && typeof showWait === 'function') {
                    showWait();
                    loaderActivo = true;
                }
                try {
                    const res = await this.getJson('/Atlas/getAccesosAtlas');
                    if (!res.success) {
                        toastPendiente = { mensaje: res.mensaje || 'No se pudo cargar el catalogo.', tipo: 'error' };
                    }
                    const datos = res.datos || {};
                    this.usuarios = datos.usuarios || [];
                    this.render(datos.totales || {});
                } catch (err) {
                    toastPendiente = { mensaje: 'No se pudo cargar el catalogo de accesos.', tipo: 'error' };
                    this.usuarios = [];
                    this.render();
                } finally {
                    if (loaderActivo) this.cerrarLoaderGlobal();
                }
                if (toastPendiente) this.toast(toastPendiente.mensaje, toastPendiente.tipo);
            },

            async sincronizar() {
                this.setLoading('Sincronizando usuarios de Comercial Mexico...');
                try {
                    const res = await this.postJson('/Atlas/sincronizarAccesosAtlas');
                    if (!res.success) {
                        this.toast(res.mensaje || 'No se pudo sincronizar.', 'error');
                        await this.cargar();
                        return;
                    }
                    const d = res.datos || {};
                    document.getElementById('atlasAccessSyncInfo').textContent =
                        `Fuente: ${d.fuente_comercial_mexico || 0} usuarios. Activos: ${d.activos_despues || 0}.`;
                    this.toast(res.mensaje || 'Usuarios sincronizados.', 'success');
                    await this.cargar();
                } catch (err) {
                    this.toast('No se pudo sincronizar usuarios.', 'error');
                    await this.cargar();
                }
            },

            async actualizarExclusion(excluir) {
                const seleccion = this.usuariosSeleccionados();
                const ids = seleccion
                    .filter(u => excluir ? Number(u.excluido_operativo || 0) !== 1 : Number(u.excluido_operativo || 0) === 1)
                    .map(u => Number(u.id || 0))
                    .filter(Boolean);
                if (!ids.length) {
                    this.toast(excluir ? 'Selecciona usuarios operativos para excluir.' : 'Selecciona usuarios excluidos para reincorporar.', 'warning');
                    return;
                }
                let motivo = '';
                if (excluir && window.Swal) {
                    const res = await Swal.fire({
                        title: 'Excluir de operacion ATLAS',
                        input: 'text',
                        inputLabel: 'Motivo',
                        inputPlaceholder: 'Ej. Marketing no participa en operacion',
                        showCancelButton: true,
                        confirmButtonText: 'Excluir',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#26344e'
                    });
                    if (!res.isConfirmed) return;
                    motivo = res.value || '';
                }
                const res = await this.postJson('/Atlas/actualizarExclusionAccesosAtlas', {
                    ids,
                    excluir: excluir ? 1 : 0,
                    motivo
                });
                this.toast(res.mensaje || 'Actualizado.', res.success ? 'success' : 'warning');
                if (res.success) {
                    this.seleccionados.clear();
                    await this.cargar();
                }
            },

            setLoading(text) {
                this.destroyTable();
                const tbody = document.getElementById('atlasAccessRows');
                if (tbody) tbody.innerHTML = `<tr><td colspan="4" class="text-center text-muted fw-semibold py-4">${this.escape(text)}</td></tr>`;
            },

            render(totales = null) {
                this.destroyTable();
                let rows = this.usuarios.filter(u => Number(u.activo) === 1);
                if (this.vista === 'operativos') rows = rows.filter(u => Number(u.excluido_operativo || 0) !== 1);
                if (this.vista === 'excluidos') rows = rows.filter(u => Number(u.excluido_operativo || 0) === 1);

                const total = totales ? Number(totales.total || 0) : this.usuarios.length;
                const activos = totales ? Number(totales.activos || 0) : this.usuarios.filter(u => Number(u.activo) === 1).length;
                const inactivos = totales ? Number(totales.inactivos || 0) : Math.max(total - activos, 0);
                const excluidos = totales ? Number(totales.excluidos || 0) : this.usuarios.filter(u => Number(u.excluido_operativo || 0) === 1).length;
                const operativos = totales ? Number(totales.operativos || 0) : this.usuarios.filter(u => Number(u.activo) === 1 && Number(u.excluido_operativo || 0) !== 1).length;
                document.getElementById('atlasAccessActivos').textContent = activos;
                document.getElementById('atlasAccessInactivos').textContent = inactivos;
                document.getElementById('atlasAccessExcluidos').textContent = excluidos;
                document.getElementById('atlasAccessOperativos').textContent = operativos;
                this.actualizarTituloTabla(rows.length);

                const tbody = document.getElementById('atlasAccessRows');
                if (!tbody) return;
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted fw-semibold py-4">Sin usuarios para mostrar.</td></tr>';
                    this.actualizarBotonesSeleccion();
                    return;
                }
                tbody.innerHTML = rows.map((u) => {
                    const activo = Number(u.activo) === 1;
                    const excluido = Number(u.excluido_operativo || 0) === 1;
                    const checked = this.seleccionados.has(String(u.id)) ? ' checked' : '';
                    return `
                        <tr>
                            <td class="text-center"><input class="form-check-input" type="checkbox" data-atlas-access-select="${Number(u.id || 0)}"${checked}></td>
                            <td>
                                <div class="gestion-personal-name-cell">
                                    ${this.avatarPersonaHtml(u, u.nombre || 'Usuario')}
                                    <div>
                                        <div class="fw-bold text-heading">${this.escape(u.nombre || 'Sin nombre')}</div>
                                        <div class="text-muted small">#${this.escape(u.numero_empleado || u.persona_id || '')} ${this.escape(u.correo || '')}</div>
                                        <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i>${this.escape(this.formatoTelefono(u.telefono || 'Sin telefono'))}</div>
                                        ${excluido ? `<div class="text-warning small fw-semibold mt-1"><i class="fa-solid fa-user-slash me-1"></i>Excluido de operacion${u.excluido_motivo ? ': ' + this.escape(u.excluido_motivo) : ''}</div>` : ''}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 sede-glass-badge" title="${this.escape(u.pais || 'Mexico')}">
                                    <span class="text-muted fw-semibold" style="font-size:.75rem;">Sede:</span>
                                    <span class="${this.escape(this.flagPais(u.pais))}" style="font-size:1.1rem; border-radius:2px; box-shadow:0 1px 3px rgba(0,0,0,.15);"></span>
                                </small>
                                <small class="text-muted d-flex flex-column gap-1 mt-2">
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="fa fa-building"></i>
                                        Departamento: <strong class="ms-1">${this.escape(u.departamento || u.area || 'Sin departamento')}</strong>
                                    </span>
                                    <span class="d-inline-flex align-items-center gap-1">
                                        <i class="fa fa-briefcase"></i>
                                        Puesto: <strong class="ms-1">${this.escape(u.puesto || 'Sin puesto')}</strong>
                                    </span>
                                </small>
                                <hr class="my-2">
                                <small class="text-muted d-flex align-items-center gap-1">
                                    <i class="fa fa-user"></i>Jefe: <strong class="ms-1">${this.escape(u.jefe_nombre || 'Sin jefe')}</strong>
                                </small>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary atlas-access-action-btn" data-atlas-access-config="${Number(u.id || 0)}" title="Ver usuario y permisos Atlas" aria-label="Ver usuario y permisos Atlas">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');
                this.initTable();
                this.actualizarBotonesSeleccion();
            },

            toggleSeleccionTodos(checked) {
                document.querySelectorAll('[data-atlas-access-select]').forEach(input => {
                    input.checked = checked;
                    const id = String(input.getAttribute('data-atlas-access-select') || '');
                    if (checked) this.seleccionados.add(id);
                    else this.seleccionados.delete(id);
                });
                this.actualizarBotonesSeleccion();
            },

            actualizarTituloTabla(totalVisible) {
                const mapa = {
                    operativos: {
                        titulo: 'Operativos',
                        subtitulo: `${totalVisible} usuarios operativos visibles.`
                    },
                    excluidos: {
                        titulo: 'Excluidos',
                        subtitulo: `${totalVisible} usuarios excluidos visibles.`
                    },
                    todos: {
                        titulo: 'Todos',
                        subtitulo: `${totalVisible} usuarios activos visibles.`
                    }
                };
                const actual = mapa[this.vista] || mapa.operativos;
                const tituloEl = document.getElementById('atlasAccessTablaTitulo');
                const subEl = document.getElementById('atlasAccessSyncInfo');
                if (tituloEl) tituloEl.innerHTML = `<i class="fa-solid fa-address-book me-2"></i>${this.escape(actual.titulo)}`;
                if (subEl) subEl.textContent = actual.subtitulo;
            },

            actualizarBotonesSeleccion() {
                const seleccion = this.usuariosSeleccionados();
                const total = seleccion.length;
                const seleccionOperativos = seleccion.filter(u => Number(u.excluido_operativo || 0) !== 1).length;
                const seleccionExcluidos = seleccion.filter(u => Number(u.excluido_operativo || 0) === 1).length;
                const excluirBtn = document.querySelector('[data-atlas-access-excluir]');
                const reintegrarBtn = document.querySelector('[data-atlas-access-reintegrar]');
                excluirBtn?.toggleAttribute('disabled', total === 0 || seleccionOperativos !== total);
                reintegrarBtn?.toggleAttribute('disabled', total === 0 || seleccionExcluidos !== total);
                const all = document.getElementById('atlasAccessSelectAll');
                if (all) all.checked = total > 0 && total === document.querySelectorAll('[data-atlas-access-select]').length;
            },

            usuariosSeleccionados() {
                const ids = new Set(Array.from(this.seleccionados).map(String));
                return (this.usuarios || []).filter(u => ids.has(String(u.id || '')));
            },

            inicialesPersona(nombre) {
                const partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
                if (!partes.length) return 'US';
                return partes.slice(0, 2).map(parte => parte.charAt(0)).join('').toUpperCase();
            },

            avatarPersonaHtml(usuario, nombreCompleto) {
                const foto = String(usuario.foto_perfil || '').trim();
                const iniciales = this.inicialesPersona(nombreCompleto);
                const nombreEsc = this.escape(nombreCompleto || 'Usuario');
                const fotoEsc = this.escape(foto);
                const inicialesEsc = this.escape(iniciales);
                const title = foto ? 'Ver foto de ' : 'Ver avatar de ';
                if (foto) {
                    return `<button type="button" class="gestion-avatar-btn" data-atlas-access-avatar data-gestion-foto="${fotoEsc}" data-gestion-nombre="${nombreEsc}" data-gestion-iniciales="${inicialesEsc}" title="${this.escape(title + (nombreCompleto || 'usuario'))}" aria-label="${this.escape(title + (nombreCompleto || 'usuario'))}">
                        <img class="gestion-personal-avatar" src="${fotoEsc}" alt="Foto de ${nombreEsc}" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                        <span class="gestion-personal-avatar-fallback" style="display:none;">${inicialesEsc}</span>
                    </button>`;
                }
                return `<button type="button" class="gestion-avatar-btn" data-atlas-access-avatar data-gestion-foto="" data-gestion-nombre="${nombreEsc}" data-gestion-iniciales="${inicialesEsc}" title="${this.escape(title + (nombreCompleto || 'usuario'))}" aria-label="${this.escape(title + (nombreCompleto || 'usuario'))}">
                    <span class="gestion-personal-avatar-fallback">${inicialesEsc}</span>
                </button>`;
            },

            abrirVisorFoto(el, event) {
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                const modalEl = document.getElementById('modalGestionFotoUsuario');
                const nombreEl = document.getElementById('gestionFotoVisorNombre');
                const subtituloEl = document.getElementById('gestionFotoVisorSubtitulo');
                const imgEl = document.getElementById('gestionFotoVisorImg');
                const fallbackEl = document.getElementById('gestionFotoVisorFallback');
                if (!modalEl || !imgEl || !fallbackEl) return;
                const nombre = el.getAttribute('data-gestion-nombre') || 'Usuario';
                const foto = el.getAttribute('data-gestion-foto') || '';
                const iniciales = el.getAttribute('data-gestion-iniciales') || this.inicialesPersona(nombre);
                if (nombreEl) nombreEl.innerHTML = `<i class="fa-solid fa-id-badge me-2"></i>${this.escape(nombre)}`;
                fallbackEl.textContent = iniciales;
                imgEl.classList.add('d-none');
                fallbackEl.classList.add('d-none');
                if (foto) {
                    imgEl.src = foto;
                    imgEl.alt = `Foto de ${nombre}`;
                    imgEl.classList.remove('d-none');
                    if (subtituloEl) subtituloEl.textContent = 'Foto de perfil';
                } else {
                    fallbackEl.classList.remove('d-none');
                    if (subtituloEl) subtituloEl.textContent = 'Avatar generado con iniciales';
                }
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
                    jQuery(modalEl).modal('show');
                }
            },

            async abrirPermisos(id) {
                const accesoId = Number(id || 0);
                if (!accesoId) return;
                this.detalleActual = null;
                const usuarioPrevio = this.usuarios.find(u => Number(u.id || 0) === accesoId) || {};
                const nombrePrevio = usuarioPrevio.nombre || 'Usuario';
                const empleadoPrevio = usuarioPrevio.numero_empleado || usuarioPrevio.persona_id || 'Sin numero';
                const puestoPrevio = usuarioPrevio.puesto || 'Sin puesto';
                document.getElementById('atlasAccessPermisosTitle').innerHTML = `<i class="fa-solid fa-user-shield me-2"></i>${this.escape(nombrePrevio)}`;
                document.getElementById('atlasAccessPermisosSubtitle').textContent = `Empleado #${empleadoPrevio} | Puesto: ${puestoPrevio}`;
                document.getElementById('atlasAccessPermisosId').value = accesoId;
                document.getElementById('atlasAccessUsuario').value = 'Cargando...';
                document.getElementById('atlasAccessPassword').value = '';
                document.getElementById('atlasAccessPassword').type = 'password';
                const motivoReset = document.getElementById('atlasAccessMotivoReset');
                const passwordNueva = document.getElementById('atlasAccessPasswordNueva');
                if (motivoReset) motivoReset.innerHTML = '<option value="">Cargando motivos...</option>';
                if (passwordNueva) passwordNueva.value = '';
                const movilInput = document.getElementById('atlasAccessMovil');
                if (movilInput) movilInput.checked = false;
                this.setModulosLoading('Cargando permisos Atlas...');
                const modalEl = document.getElementById('modalAtlasAccessPermisos');
                let abrirModal = false;
                let loaderActivo = false;
                if (typeof showWait === 'function') {
                    showWait();
                    loaderActivo = true;
                }
                try {
                    const res = await this.getJson(`/Atlas/getAccesoAtlasDetalle?id=${encodeURIComponent(accesoId)}`);
                    if (!res.success) {
                        this.toast(res.mensaje || 'No se pudo cargar el usuario.', 'error');
                        this.setModulosLoading('No se pudieron cargar permisos.');
                        return;
                    }
                    this.detalleActual = res.datos || {};
                    const usuario = this.detalleActual.usuario || {};
                    const nombre = usuario.nombre || 'Usuario';
                    const empleado = usuario.numero_empleado || usuario.persona_id || 'Sin numero';
                    const puesto = usuario.puesto || 'Sin puesto';
                    document.getElementById('atlasAccessPermisosTitle').innerHTML = `<i class="fa-solid fa-user-shield me-2"></i>${this.escape(nombre)}`;
                    document.getElementById('atlasAccessPermisosSubtitle').textContent = `Empleado #${empleado} | Puesto: ${puesto}`;
                    document.getElementById('atlasAccessUsuario').value = usuario.user_name || 'Sin usuario';
                    document.getElementById('atlasAccessPassword').value = usuario.password || 'Sin contrasena registrada';
                    if (movilInput) movilInput.checked = Number(usuario.acceso_movil || 0) === 1;
                    this.renderMotivosReset(this.detalleActual.motivos_reset_password || []);
                    this.renderModulos(this.detalleActual.modulos || []);
                    abrirModal = true;
                } catch (err) {
                    this.toast('No se pudo cargar el detalle de accesos.', 'error');
                    this.setModulosLoading('No se pudieron cargar permisos.');
                } finally {
                    if (loaderActivo) this.cerrarLoaderGlobal();
                }
                if (abrirModal) this.mostrarModal(modalEl);
            },

            setModulosLoading(text) {
                const html = `<div class="text-center text-muted fw-semibold py-4">${this.escape(text)}</div>`;
                const sistema = document.getElementById('atlasAccessModulosSistema');
                const especiales = document.getElementById('atlasAccessModulosEspeciales');
                if (sistema) sistema.innerHTML = html;
                if (especiales) especiales.innerHTML = html;
            },

            renderModulos(modulos) {
                const sistema = document.getElementById('atlasAccessModulosSistema');
                const especiales = document.getElementById('atlasAccessModulosEspeciales');
                if (!sistema || !especiales) return;
                if (!modulos.length) {
                    this.setModulosLoading('No hay módulos Atlas configurados.');
                    return;
                }
                const renderLista = (lista, emptyText, prefijo) => {
                    if (!lista.length) {
                        return `<div class="text-center text-muted fw-semibold py-4">${this.escape(emptyText)}</div>`;
                    }
                    const grupos = lista.reduce((acc, modulo) => {
                        const grupo = modulo.pestana || 'Atlas';
                        if (!acc[grupo]) acc[grupo] = [];
                        acc[grupo].push(modulo);
                        return acc;
                    }, {});
                    return `<div class="modal-perfil-modulos-agrupados w-100">${Object.entries(grupos).map(([grupo, items], index) => {
                        const groupId = `${prefijo}-${index + 1}`;
                        const collapseId = `atlas-access-${groupId}`;
                        const masterId = `atlas-access-master-${groupId}`;
                        const allChecked = items.length > 0 && items.every(m => Number(m.asignado || 0) === 1);
                        const iconoGrupo = this.iconoModulo({ nombre: grupo, pestana: grupo });
                        const columnas = this.columnasModulos(items);
                        const wideClass = columnas.length > 1 ? ' is-wide' : '';
                        return `
                            <section class="modal-perfil-modulo-grupo${wideClass} card mb-0 shadow-sm">
                                <div class="modal-perfil-modulo-grupo-header px-3 py-2 d-flex align-items-center flex-wrap gap-2 fw-semibold modal-perfil-modulo-grupo-toggle"
                                     role="button" tabindex="0" aria-expanded="true" aria-controls="${collapseId}"
                                     data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                                    <i class="fa fa-chevron-down modal-perfil-modulo-grupo-chevron text-primary" aria-hidden="true"></i>
                                    <i class="${this.escape(iconoGrupo)} me-2 text-primary" style="flex-shrink:0;"></i>
                                    <span class="flex-grow-1 min-w-0">${this.escape(grupo)} (${items.length})</span>
                                    <div class="d-flex align-items-center gap-2 ms-auto flex-shrink-0 modal-perfil-modulo-master-wrap">
                                        <label class="form-check-label small text-secondary mb-0 modal-perfil-modulo-master-label" for="${masterId}" title="Asignar todos los modulos de ${this.escape(grupo)} a esta persona">Marcar todo</label>
                                        <input type="checkbox" class="form-check-input modal-perfil-modulo-master-cb" id="${masterId}" data-atlas-access-module-master="${this.escape(groupId)}" title="Asignar todos los modulos de ${this.escape(grupo)} a esta persona" aria-label="Marcar todo el grupo ${this.escape(grupo)}"${allChecked ? ' checked' : ''}>
                                    </div>
                                </div>
                                <div id="${collapseId}" class="collapse show modal-perfil-modulo-grupo-collapse">
                                    <div class="${columnas.length > 1 ? 'modal-perfil-modulo-columns' : ''}">
                                        ${columnas.map(columna => `
                                            <table class="table table-hover mb-0" style="font-size:.9rem;">
                                                <tbody>
                                                    ${columna.map((m) => this.renderModuloFila(m, groupId, grupo)).join('')}
                                                </tbody>
                                            </table>
                                        `).join('')}
                                    </div>
                                </div>
                            </section>
                        `;
                    }).join('')}</div>`;
                };
                const modulosSistema = modulos.filter(m => this.norm(m.pestana || '') !== 'permisos especiales');
                const modulosEspeciales = modulos.filter(m => this.norm(m.pestana || '') === 'permisos especiales');
                sistema.innerHTML = renderLista(modulosSistema, 'No hay modulos Atlas de menu.', 'sistema');
                especiales.innerHTML = renderLista(modulosEspeciales, 'No hay permisos especiales Atlas.', 'especiales');
                this.actualizarMastersModulos();
            },

            columnasModulos(items) {
                if (!Array.isArray(items) || items.length <= 4) return [items || []];
                const corte = Math.ceil(items.length / 2);
                return [items.slice(0, corte), items.slice(corte)];
            },

            renderModuloFila(modulo, groupId, grupo) {
                const checked = Number(modulo.asignado || 0) === 1;
                const moduloId = Number(modulo.id || 0);
                const nombre = modulo.nombre || 'Modulo Atlas';
                const descripcion = modulo.descripcion || '';
                const badgeClass = checked ? 'bg-primary' : 'bg-secondary';
                const badgeText = checked ? 'Asignado' : 'Asignar';
                return `
                    <tr class="modal-perfil-modulo-fila">
                        <td class="fw-medium" style="padding:.875rem .875rem .875rem 1.75rem; vertical-align:middle;">
                            <div style="display:flex; align-items:center; gap:.75rem;">
                                <div class="modulo-icon-box">
                                    <i class="${this.escape(this.iconoModulo(modulo))}" title="${this.escape(nombre)}"></i>
                                </div>
                                <span style="font-weight:600; color:#2c3e50; font-size:.95rem;">${this.escape(nombre)}</span>
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size:.75rem;">${this.escape(grupo)} &gt; ${this.escape(descripcion || nombre)}</small>
                        </td>
                        <td class="text-end" style="padding:.875rem; vertical-align:middle; width:130px;">
                            <label class="form-check mb-0" style="display:flex; align-items:center; justify-content:flex-end; gap:.5rem; cursor:pointer;">
                                <input type="checkbox" class="form-check-input modal-perfil-modulo-item-cb" value="${moduloId}" data-atlas-access-module data-atlas-access-module-group="${this.escape(groupId)}"${checked ? ' checked' : ''}>
                                <span class="form-check-label" style="cursor:pointer; user-select:none;">
                                    <span class="badge ${badgeClass} rounded-pill px-3 py-1" data-atlas-access-module-badge>${badgeText}</span>
                                </span>
                            </label>
                        </td>
                    </tr>
                `;
            },

            iconoModulo(modulo) {
                const texto = this.norm(`${modulo.nombre || ''} ${modulo.pestana || ''}`);
                if (texto.includes('ruta')) return 'fa-solid fa-route';
                if (texto.includes('sucursal')) return 'fa-solid fa-store';
                if (texto.includes('presupuesto')) return 'fa-solid fa-money-bill-trend-up';
                if (texto.includes('notificacion')) return 'fa-solid fa-bell';
                if (texto.includes('acceso') || texto.includes('permiso')) return 'fa-solid fa-user-shield';
                if (texto.includes('seguimiento')) return 'fa-solid fa-location-dot';
                if (texto.includes('catalog')) return 'fa-solid fa-list-check';
                return 'fa fa-cube';
            },

            toggleGrupoModulos(master) {
                const groupId = master.getAttribute('data-atlas-access-module-master') || '';
                document.querySelectorAll(`[data-atlas-access-module-group="${this.selectorEscape(groupId)}"]`).forEach((input) => {
                    input.checked = master.checked;
                    this.actualizarEstadoModulo(input);
                });
                this.actualizarMastersModulos();
            },

            actualizarEstadoModulo(input) {
                const row = input.closest('.modal-perfil-modulo-fila');
                const badge = row?.querySelector('[data-atlas-access-module-badge]');
                if (!badge) return;
                badge.textContent = input.checked ? 'Asignado' : 'Asignar';
                badge.classList.toggle('bg-primary', input.checked);
                badge.classList.toggle('bg-secondary', !input.checked);
            },

            actualizarMastersModulos() {
                document.querySelectorAll('[data-atlas-access-module-master]').forEach((master) => {
                    const groupId = master.getAttribute('data-atlas-access-module-master') || '';
                    const items = Array.from(document.querySelectorAll(`[data-atlas-access-module-group="${this.selectorEscape(groupId)}"]`));
                    master.checked = items.length > 0 && items.every(input => input.checked);
                    master.indeterminate = items.some(input => input.checked) && !master.checked;
                });
            },

            selectorEscape(value) {
                if (window.CSS && typeof CSS.escape === 'function') return CSS.escape(value);
                return String(value || '').replace(/["\\]/g, '\\$&');
            },

            renderMotivosReset(motivos) {
                const select = document.getElementById('atlasAccessMotivoReset');
                if (!select) return;
                const opciones = (motivos || []).map((m) => {
                    return `<option value="${Number(m.id || 0)}">${this.escape(m.nombre || 'Motivo')}</option>`;
                }).join('');
                select.innerHTML = `<option value="">Selecciona motivo</option>${opciones}`;
            },

            togglePassword() {
                const input = document.getElementById('atlasAccessPassword');
                if (!input) return;
                input.type = input.type === 'password' ? 'text' : 'password';
            },

            mostrarModal(modalEl) {
                if (!modalEl) return;
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).show();
                } else if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
                    jQuery(modalEl).modal('show');
                }
            },

            ocultarModal(modalEl) {
                if (!modalEl) return;
                if (window.bootstrap && bootstrap.Modal) {
                    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
                } else if (window.jQuery && jQuery.fn && typeof jQuery.fn.modal === 'function') {
                    jQuery(modalEl).modal('hide');
                }
            },

            abrirResetPassword() {
                const permisosModal = document.getElementById('modalAtlasAccessPermisos');
                const resetModal = document.getElementById('modalAtlasAccessResetPassword');
                if (!resetModal) return;
                const usuario = this.detalleActual?.usuario || {};
                document.getElementById('atlasAccessResetSubtitle').textContent = `${usuario.nombre || 'Usuario'} | ${usuario.puesto || 'Sin puesto'}`;
                this.reabrirPermisosTrasReset = true;
                if (permisosModal && permisosModal.classList.contains('show')) {
                    const abrirReset = () => {
                        permisosModal.removeEventListener('hidden.bs.modal', abrirReset);
                        this.mostrarModal(resetModal);
                    };
                    permisosModal.addEventListener('hidden.bs.modal', abrirReset);
                    this.ocultarModal(permisosModal);
                    return;
                }
                this.mostrarModal(resetModal);
            },

            reabrirModalPermisosSiAplica() {
                if (!this.reabrirPermisosTrasReset) return;
                this.reabrirPermisosTrasReset = false;
                this.mostrarModal(document.getElementById('modalAtlasAccessPermisos'));
            },

            sugerirPassword() {
                const input = document.getElementById('atlasAccessPasswordNueva');
                if (!input) return;
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
                let token = '';
                if (window.crypto && crypto.getRandomValues) {
                    const values = new Uint32Array(8);
                    crypto.getRandomValues(values);
                    token = Array.from(values).map(v => chars[v % chars.length]).join('');
                } else {
                    for (let i = 0; i < 8; i++) token += chars[Math.floor(Math.random() * chars.length)];
                }
                input.value = `Atlas${token}!`;
                input.focus();
                input.select();
            },

            async restablecerPassword() {
                const id = Number(document.getElementById('atlasAccessPermisosId')?.value || 0);
                const motivoId = Number(document.getElementById('atlasAccessMotivoReset')?.value || 0);
                const password = String(document.getElementById('atlasAccessPasswordNueva')?.value || '').trim();
                if (!id) return;
                if (!motivoId) {
                    this.toast('Selecciona el motivo del restablecimiento.', 'warning');
                    return;
                }
                if (password.length < 6 || password.length > 30) {
                    this.toast('La contrasena debe tener entre 6 y 30 caracteres.', 'warning');
                    return;
                }
                try {
                    const res = await this.postJson('/Atlas/restablecerPasswordAccesoAtlas', { id, motivo_id: motivoId, password });
                    this.toast(res.mensaje || 'Contrasena restablecida.', res.success ? 'success' : 'warning');
                    if (res.success) {
                        const actual = document.getElementById('atlasAccessPassword');
                        const nueva = document.getElementById('atlasAccessPasswordNueva');
                        const motivo = document.getElementById('atlasAccessMotivoReset');
                        if (actual) actual.value = password;
                        if (nueva) nueva.value = '';
                        if (motivo) motivo.value = '';
                        this.ocultarModal(document.getElementById('modalAtlasAccessResetPassword'));
                    }
                } catch (err) {
                    this.toast('No se pudo restablecer la contrasena.', 'error');
                }
            },

            seleccionarTodosModulos() {
                const checks = Array.from(document.querySelectorAll('[data-atlas-access-module]'));
                const allChecked = checks.length > 0 && checks.every(cb => cb.checked);
                checks.forEach(cb => {
                    cb.checked = !allChecked;
                    this.actualizarEstadoModulo(cb);
                });
                this.actualizarMastersModulos();
            },

            async guardarPermisos() {
                const id = Number(document.getElementById('atlasAccessPermisosId')?.value || 0);
                if (!id) return;
                const modulos = Array.from(document.querySelectorAll('[data-atlas-access-module]:checked'))
                    .map(cb => Number(cb.value || 0))
                    .filter(Boolean);
                const accesoMovil = document.getElementById('atlasAccessMovil')?.checked ? 1 : 0;
                try {
                    const res = await this.postJson('/Atlas/guardarPermisosAccesoAtlas', { id, modulos, acceso_movil: accesoMovil });
                    this.toast(res.mensaje || 'Permisos guardados.', res.success ? 'success' : 'warning');
                    if (res.success) {
                        const modalEl = document.getElementById('modalAtlasAccessPermisos');
                        if (modalEl && window.bootstrap) {
                            const inst = bootstrap.Modal.getInstance(modalEl);
                            if (inst) inst.hide();
                        }
                    }
                } catch (err) {
                    this.toast('No se pudieron guardar los permisos Atlas.', 'error');
                }
            },

            initTable() {
                if (!window.jQuery || !jQuery.fn || !jQuery.fn.DataTable) return;
                if (typeof window.configuraTabla === 'function') {
                    window.configuraTabla('#atlasAccessTabla', {
                        registrosPorPagina: 10,
                        order: [],
                        columns: Array.from({ length: 4 }, () => ({ data: null, defaultContent: '' }))
                    });
                    this.normalizarPaginacionTabla('#atlasAccessTabla');
                    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#atlasAccessTabla')) {
                        jQuery('#atlasAccessTabla').DataTable().on('draw', () => {
                            this.actualizarBotonesSeleccion();
                            this.normalizarPaginacionTabla('#atlasAccessTabla');
                        });
                    }
                    return;
                }
                jQuery('#atlasAccessTabla').DataTable({
                    pageLength: 10,
                    lengthMenu: [5, 10, 25, 50, 100],
                    order: [],
                    autoWidth: false,
                    language: {
                        emptyTable: 'Sin usuarios para mostrar',
                        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                        infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                        lengthMenu: 'Mostrar _MENU_ registros',
                        search: 'Buscar:',
                        zeroRecords: 'No se encontraron resultados',
                        paginate: { first: '«', last: '»', next: '›', previous: '‹' }
                    }
                }).on('draw', () => {
                    this.actualizarBotonesSeleccion();
                    this.normalizarPaginacionTabla('#atlasAccessTabla');
                });
                this.normalizarPaginacionTabla('#atlasAccessTabla');
            },

            normalizarPaginacionTabla(selector) {
                const wrapper = document.querySelector(`${selector}_wrapper`);
                if (!wrapper) return;
                [
                    ['.paginate_button.first, .dt-paging-button.first', '«'],
                    ['.paginate_button.previous, .dt-paging-button.previous', '‹'],
                    ['.paginate_button.next, .dt-paging-button.next', '›'],
                    ['.paginate_button.last, .dt-paging-button.last', '»']
                ].forEach(([buttonSelector, label]) => {
                    wrapper.querySelectorAll(buttonSelector).forEach((button) => {
                        const link = button.querySelector('a, button');
                        (link || button).textContent = label;
                    });
                });
            },

            destroyTable() {
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#atlasAccessTabla')) {
                    jQuery('#atlasAccessTabla').DataTable().destroy();
                }
            },

            norm(value) {
                return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
            },

            formatoTelefono(value) {
                const raw = String(value || '').trim();
                const digits = raw.replace(/\D+/g, '');
                if (digits.length === 10) return `${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6)}`;
                return raw || 'Sin telefono';
            },

            flagPais(value) {
                const pais = this.norm(value || 'Mexico');
                if (pais.includes('colombia')) return 'fi fi-co fis';
                if (pais.includes('usa') || pais.includes('estados unidos') || pais.includes('united states')) return 'fi fi-us fis';
                return 'fi fi-mx fis';
            },

            escape(value) {
                return String(value ?? '').replace(/[&<>"']/g, (m) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
                }[m]));
            },

            toast(message, icon = 'info') {
                if (window.Swal) {
                    Swal.fire({ icon, title: message, timer: 1800, showConfirmButton: false });
                    return;
                }
                console.log(message);
            }
        };

        document.addEventListener('DOMContentLoaded', () => AtlasAccess.init());
    </script>
</div>
