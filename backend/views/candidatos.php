<?php
$departamento = $departamento ?? ['datos' => []];
$paisesActivos = $paisesActivos ?? [];
$listaJefes = $listaJefes ?? [];
?>
<div class="content-wrapper">

    <div class="card">

        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Filtros de búsqueda</h5>
            <div class="row pt-4 g-6">
                <div class="col-md-3">
                    <select id="UserRole" class="form-select text-capitalize">
                        <option value="">Selecciona Departamento</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="UserPlan" class="form-select text-capitalize">
                        <option value="">Selecciona Puesto</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="FilterTransaction" class="form-select text-capitalize">
                        <option value="">Selecciona Estatus</option>
                        <option value="Por evaluar">Por evaluar</option>
                        <option value="En entrevista">En entrevista</option>
                        <option value="Contratado">Contratado</option>
                        <option value="Descartado">Descartado</option>
                        <option value="Validado">Validado</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="panelIndicadoresCandidatos" class="px-4 pt-3 pb-2">
            <div class="kpi-toolbar">
                <button class="kpi-toggle-btn open" type="button" id="kpiToggleBtnCandidatos" onclick="kpiTogglePanelCandidatos()">
                    <span class="kpi-dot"></span>
                    Indicadores
                    <i class="bx bx-chevron-down kpi-chevron"></i>
                </button>
            </div>
            <div class="kpi-collapsible open" id="kpiCollapsibleCandidatos">
                <div class="kpi-collapsible-inner">
                    <div class="kpi-row-new mode-default">
                        <div class="kpi-cell tipo-total revealed" id="kpi-cell-cand-total">
                            <span class="kpi-corner-icon"><i class="bx bx-group"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-group"></i></div>
                                <span class="kpi-cell-status">Total</span>
                            </div>
                            <div class="kpi-num" id="kpi-total-candidatos">0</div>
                            <div class="kpi-lbl">Total Candidatos</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-cand-total"></div></div>
                        </div>
                        <div class="kpi-cell tipo-puesto revealed" id="kpi-cell-cand-evaluar">
                            <span class="kpi-corner-icon"><i class="bx bx-user-plus"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-user-plus"></i></div>
                                <span class="kpi-cell-status">Por evaluar</span>
                            </div>
                            <div class="kpi-num" id="kpi-por-evaluar">0</div>
                            <div class="kpi-lbl">Por evaluar</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-cand-evaluar"></div></div>
                        </div>
                        <div class="kpi-cell tipo-dep revealed" id="kpi-cell-cand-enviadas">
                            <span class="kpi-corner-icon"><i class="bx bx-send"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-send"></i></div>
                                <span class="kpi-cell-status">Enviadas</span>
                            </div>
                            <div class="kpi-num" id="kpi-postulaciones-enviadas">0</div>
                            <div class="kpi-lbl">Postulaciones enviadas</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-cand-enviadas"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-between m-4">
            <div class="col-8"></div>
            <div class="col-4 d-flex align-items-end justify-content-end gap-2">
                <button type="button" class="btn btn-primary add-new btn-action-size" data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddCandidato">
                    <i class="icon-base bx bx-plus icon-sm me-sm-2"></i>
                    <span class="d-inline-block">Agregar Candidato</span>
                </button>
            </div>
        </div>

        <div class="card-datatable table-responsive">
            <table id="tablaCandidatos" class="dt-responsive table border-top" style="width:100%">
                <thead>
                    <tr>
                        <th></th>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Puesto / Departamento</th>
                        <th>Estatus</th>
                        <th class="col-acciones-candidatos">Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Documentación del candidato -->
<div class="modal fade" id="modalDocumentacionCandidato" tabindex="-1" aria-labelledby="modalDocumentacionCandidatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width: 85%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDocumentacionCandidatoLabel"><i class="fa fa-folder-open me-2"></i>Documentación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="modalDocumentacionCandidatoNombre"></p>
                <div id="modalDocumentacionCandidatoMetricas" class="mb-3 d-none"></div>
                <div id="modalDocumentacionCandidatoVerificacion" class="mb-3 d-none"></div>
                <div id="modalDocumentacionCandidatoAccionVerificar" class="mb-3 d-none"></div>
                <div id="modalDocumentacionCandidatoCargando" class="text-center py-4 text-muted">Cargando…</div>
                <div id="modalDocumentacionCandidatoVacio" class="text-center py-4 text-muted d-none">No hay documentos subidos.</div>
                <div id="modalDocumentacionCandidatoLista" class="list-group"></div>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas Agregar Candidato -->
<div class="offcanvas offcanvas-end" id="offcanvasAddCandidato" tabindex="-1">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvasCandidatoTitulo">Nuevo Candidato</h5>
    </div>
    <div class="offcanvas-body p-4">
        <form id="formAgregarCandidato">
            <div class="mb-2">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" name="nombres" id="candidato_nombres" class="form-control" required maxlength="100" placeholder="Ej. Juan" style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()">
            </div>
            <div class="mb-2">
                <label class="form-label">Segundo nombre (opcional)</label>
                <input type="text" name="segundo_nombre" id="candidato_segundo_nombre" class="form-control" maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()">
            </div>
            <div class="mb-2">
                <label class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                <input type="text" name="apellidop" id="candidato_apellidop" class="form-control" required maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()">
            </div>
            <div class="mb-2">
                <label class="form-label">Apellido materno <span class="text-danger">*</span></label>
                <input type="text" name="apellidom" id="candidato_apellidom" class="form-control" required maxlength="100" style="text-transform: uppercase;" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()">
            </div>
            <div class="mb-2">
                <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                <input type="text" name="telefono" id="candidato_telefono" class="form-control" required maxlength="20" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            <div class="mb-2">
                <label class="form-label">Correo <span class="text-danger">*</span></label>
                <input type="email" name="email" id="candidato_email" class="form-control" required maxlength="150">
            </div>
            <div class="mb-2">
                <label class="form-label">País <span class="text-danger">*</span></label>
                <select name="id_pais" id="candidato_id_pais" class="form-select" required>
                    <option value="">Seleccione un país</option>
                    <?php foreach ($paisesActivos as $p): ?>
                        <option value="<?= (int)($p['id'] ?? 0) ?>"><?= htmlspecialchars($p['nombre'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Departamento al que aplica <span class="text-danger">*</span></label>
                <select name="id_departamento" id="candidato_id_departamento" class="form-select" required>
                    <option value="">Seleccione departamento</option>
                    <?php foreach ($departamento['datos'] as $d): ?>
                        <option value="<?= (int)($d['id'] ?? 0) ?>"><?= htmlspecialchars($d['nombre'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Puesto solicitado <span class="text-danger">*</span></label>
                <select name="id_puesto" id="candidato_id_puesto" class="form-select" required>
                    <option value="">Seleccione puesto</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Posible jefe <span class="text-danger">*</span></label>
                <select name="id_posible_jefe" id="candidato_id_posible_jefe" class="form-select" required>
                    <option value="">Seleccione departamento y puesto primero</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Fecha de postulación <span class="text-danger">*</span></label>
                <div class="fecha-acta-wrapper fecha-postulacion-wrapper">
                    <input type="text" name="fecha_postulacion" id="candidato_fecha_postulacion" class="form-control" placeholder="YYYY-MM-DD" required autocomplete="off" readonly>
                </div>
            </div>
            <div class="mb-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="candidato_asignar_legion" onchange="toggleLegionCandidato()">
                    <label class="form-check-label" for="candidato_asignar_legion">Asignar legión</label>
                </div>
            </div>
            <div class="mb-2" id="div_candidato_legion" style="display:none;">
                <label class="form-label">Legión <span class="text-danger">*</span></label>
                <select name="id_legion" id="candidato_id_legion" class="form-select">
                    <option value="">Seleccione legión</option>
                    <option value="1">Sabueso</option>
                    <option value="2">Heraldo</option>
                    <option value="3">Centinela</option>
                    <option value="4">Senturiones</option>
                    <option value="5">Espartano</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="form-label">Usuario <span class="text-danger">*</span></label>
                <input type="text" name="usuario" id="candidato_usuario" class="form-control" required maxlength="50" placeholder="Ej. lazaro.mendez" oninput="this.value = this.value.replace(/[^A-Za-z0-9_.\-]/g, '');">
            </div>
            <div class="mb-4">
                <label class="form-label">Contraseña <span class="text-danger">*</span></label>
                <input type="text" name="contrasena" id="candidato_contrasena" class="form-control" required maxlength="255">
            </div>
            <button type="submit" class="btn btn-primary me-2" id="btnSubmitCandidato"><i class="bx bx-save me-1"></i> Guardar</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas"><i class="bx bx-x me-1"></i> Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal Resumen y Enviar postulación -->
<div class="modal fade" id="modalResumenPostulacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-resumen-candidato">
        <div class="modal-content resumen-candidato-modal-content">
            <div class="modal-header resumen-candidato-modal-header">
                <h5 class="modal-title d-flex align-items-center gap-2">
                    <i class="bx bx-user-detail text-primary"></i>
                    Resumen del candidato
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body resumen-candidato-modal-body">
                <div id="resumenPostulacionTexto" class="resumen-candidato-card mb-4"></div>
                <div id="bloqueLinkDocumentos" class="mb-3 link-documentos-block" style="display: none;">
                    <div class="link-documentos-card">
                        <div class="link-documentos-label">Enlace para que el candidato suba sus documentos</div>
                        <div class="link-documentos-url-box">
                            <span class="link-documentos-url-icon" aria-hidden="true">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                            </span>
                            <input type="text" class="link-documentos-url-input" id="inputUrlDocumentos" readonly placeholder="Se generará al enviar o al reenviar" title="">
                        </div>
                        <div class="link-documentos-actions">
                            <button type="button" class="link-documentos-btn link-documentos-btn-copy" id="btnCopiarUrlDocumentos" title="Copiar URL">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                Copiar URL
                            </button>
                            <button type="button" class="link-documentos-btn link-documentos-btn-open" id="btnAbrirUrlDocumentos" title="Abrir en nueva pestaña">
                                <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                Abrir URL
                            </button>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end justify-content-md-center pt-2">
                    <button type="button" class="btn btn-primary btn-enviar-postulacion px-4 py-2" id="btnEnviarPostulacion" onclick="enviarPostulacionAlCandidato()">
                        <i class="bx bx-send me-2"></i> Enviar postulación al candidato
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="link-documentos-toast" id="toastUrlDocumentos" role="status" aria-live="polite">✓ URL copiada al portapapeles</div>

<style>
.btn-action-size { height: 36px; padding: 0.375rem 0.75rem; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.375rem; }
#tablaCandidatos thead th { background-color: rgba(105, 108, 255, 0.1); font-weight: 600; }
/* Offcanvas candidato siempre visible por encima del layout */
#offcanvasAddCandidato.offcanvas { z-index: 1060 !important; }
.offcanvas-backdrop { z-index: 1055 !important; }
/* Scrim borroso detrás del modal (Documentación, Resumen postulación, etc.) */
.modal-backdrop { backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
.modal-backdrop.show { backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
/* Botón Guardar/Actualizar con color visible en modo claro y oscuro */
#offcanvasAddCandidato #btnSubmitCandidato.btn-primary { background-color: #696cff !important; border-color: #696cff !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-primary:hover { background-color: #5f61e6 !important; border-color: #5f61e6 !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-primary { background-color: #6366f1 !important; border-color: #6366f1 !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-primary:hover { background-color: #818cf8 !important; border-color: #818cf8 !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-success { background-color: #71dd37 !important; border-color: #71dd37 !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-success:hover { background-color: #85e34b !important; border-color: #85e34b !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-success { background-color: #22c55e !important; border-color: #22c55e !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-success:hover { background-color: #4ade80 !important; border-color: #4ade80 !important; color: #fff !important; }

/* Bloque enlace documentos: estilo tipo card oscuro (referencia URL component) */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
.link-documentos-block { font-family: 'Inter', 'Public Sans', system-ui, sans-serif; }
.link-documentos-card {
    background: #1a1b26;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 28px 24px;
    width: 100%;
    box-shadow: 0 0 0 1px rgba(255,255,255,0.04), 0 12px 40px rgba(0, 0, 0, 0.35);
}
body:not(.dark-mode) .link-documentos-card {
    background: #f0f2f5;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
}
.link-documentos-label {
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.45);
    margin-bottom: 12px;
}
body:not(.dark-mode) .link-documentos-label { color: rgba(0, 0, 0, 0.5); }
.link-documentos-url-box {
    display: flex;
    align-items: center;
    background: #252633;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 14px 18px;
    gap: 10px;
    margin-bottom: 20px;
    transition: border-color 0.2s, background-color 0.2s;
}
.link-documentos-url-box:focus-within {
    border-color: rgba(139, 92, 246, 0.45);
    background: #2a2b38;
}
body:not(.dark-mode) .link-documentos-url-box {
    background: #fff;
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
body:not(.dark-mode) .link-documentos-url-box:focus-within {
    border-color: rgba(139, 92, 246, 0.35);
    box-shadow: 0 0 0 2px rgba(139, 92, 246, 0.1);
}
.link-documentos-url-icon { color: rgba(255,255,255,0.4); flex-shrink: 0; }
body:not(.dark-mode) .link-documentos-url-icon { color: rgba(0, 0, 0, 0.4); }
.link-documentos-url-input {
    background: none;
    border: none;
    outline: none;
    color: rgba(255, 255, 255, 0.9);
    font-family: ui-monospace, 'SF Mono', 'Cascadia Code', monospace;
    font-size: 13.5px;
    font-weight: 400;
    letter-spacing: 0.01em;
    width: 100%;
    text-overflow: ellipsis;
    overflow: hidden;
}
.link-documentos-url-input::placeholder { color: rgba(255, 255, 255, 0.3); }
body:not(.dark-mode) .link-documentos-url-input { color: rgba(0, 0, 0, 0.85); }
body:not(.dark-mode) .link-documentos-url-input::placeholder { color: rgba(0, 0, 0, 0.4); }
.link-documentos-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.link-documentos-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 13px 18px;
    border-radius: 12px;
    font-family: inherit;
    font-size: 13.5px;
    font-weight: 500;
    letter-spacing: 0.01em;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.link-documentos-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 0.2s;
    background: white;
}
.link-documentos-btn:hover::before { opacity: 0.05; }
.link-documentos-btn:active::before { opacity: 0.1; }
.link-documentos-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
.link-documentos-btn-copy {
    background: rgba(139, 92, 246, 0.12);
    color: #a78bfa;
    border: 1px solid rgba(139, 92, 246, 0.2);
}
.link-documentos-btn-copy:hover {
    background: rgba(139, 92, 246, 0.18);
    border-color: rgba(139, 92, 246, 0.4);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(139, 92, 246, 0.15);
}
.link-documentos-btn-open {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: #fff;
    border: 1px solid rgba(139, 92, 246, 0.3);
}
.link-documentos-btn-open:hover {
    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
    transform: translateY(-1px);
    box-shadow: 0 6px 24px rgba(124, 58, 237, 0.35);
}
.link-documentos-toast {
    position: fixed;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%) translateY(12px);
    background: #1e1e2e;
    border: 1px solid rgba(139, 92, 246, 0.25);
    color: #a78bfa;
    font-size: 13px;
    font-weight: 500;
    padding: 10px 20px;
    border-radius: 30px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    opacity: 0;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none;
    white-space: nowrap;
    z-index: 9999;
}
.link-documentos-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* Cancelar: mantener estilo secundario visible en modo oscuro */
body.dark-mode #offcanvasAddCandidato .btn-outline-secondary { border-color: rgba(148, 163, 184, 0.5) !important; color: #94a3b8 !important; }
body.dark-mode #offcanvasAddCandidato .btn-outline-secondary:hover { background-color: rgba(71, 85, 105, 0.5) !important; border-color: #64748b !important; color: #e2e8f0 !important; }
/* Botones del formulario candidato */
#offcanvasAddCandidato #btnSubmitCandidato { min-width: 120px; font-weight: 600; }
#offcanvasAddCandidato .btn-outline-secondary:hover { background-color: #6c757d; color: #fff; }
.btn-action-size { height: 36px; padding: 0.375rem 0.75rem; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.375rem; }
/* Panel de indicadores Candidatos — mismos estilos que Gestión (kpi-toolbar, kpi-cell, kpi-num, kpi-lbl) */
#panelIndicadoresCandidatos .kpi-toolbar { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.65rem; flex-wrap:wrap; }
#panelIndicadoresCandidatos .kpi-toggle-btn {
    display:inline-flex; align-items:center; gap:0.35rem;
    background:#fff; border:1px solid rgba(99,102,241,0.18); border-radius:8px;
    padding:0.38rem 0.85rem; cursor:pointer;
    font-size:0.78rem; font-weight:600; color:#6366f1;
    box-shadow:0 1px 4px rgba(99,102,241,0.07);
    transition:background 0.2s, box-shadow 0.2s;
    user-select:none; white-space:nowrap;
}
#panelIndicadoresCandidatos .kpi-toggle-btn:hover { background:rgba(99,102,241,0.06); box-shadow:0 2px 10px rgba(99,102,241,0.13); }
#panelIndicadoresCandidatos .kpi-toggle-btn .kpi-chevron { font-size:1rem; transition:transform 0.35s cubic-bezier(0.4,0,0.2,1); display:flex; }
#panelIndicadoresCandidatos .kpi-toggle-btn.open .kpi-chevron { transform:rotate(180deg); }
#panelIndicadoresCandidatos .kpi-toggle-btn .kpi-dot { width:6px; height:6px; border-radius:50%; background:#6366f1; flex-shrink:0; }
#panelIndicadoresCandidatos .kpi-collapsible { display:grid; grid-template-rows:0fr; transition:grid-template-rows 0.4s cubic-bezier(0.4,0,0.2,1), opacity 0.35s ease; opacity:0; }
#panelIndicadoresCandidatos .kpi-collapsible.open { grid-template-rows:1fr; opacity:1; }
#panelIndicadoresCandidatos .kpi-collapsible-inner { overflow:hidden; }
#panelIndicadoresCandidatos .kpi-row-new { display:grid; grid-template-columns:repeat(3,1fr); gap:0.85rem; padding-bottom:0.25rem; }
#panelIndicadoresCandidatos .kpi-cell {
    background:#fff; border-radius:14px;
    border:1px solid rgba(99,102,241,0.12);
    box-shadow:0 2px 16px rgba(99,102,241,0.08),0 1px 4px rgba(0,0,0,0.04), inset 4px 0 0 var(--cell-accent);
    position:relative; overflow:hidden; cursor:pointer;
    min-height:190px;
    transition:transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s, border-color 0.25s;
}
#panelIndicadoresCandidatos .kpi-cell.revealed { animation:kpiCellRevealCand 0.45s cubic-bezier(0.34,1.3,0.64,1) forwards; }
@keyframes kpiCellRevealCand {
    from { opacity:0; transform:translateY(14px) scale(0.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
#panelIndicadoresCandidatos .kpi-cell:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(99,102,241,0.13),0 2px 8px rgba(0,0,0,0.06),inset 4px 0 0 var(--cell-accent); border-color:rgba(99,102,241,0.28); }
#panelIndicadoresCandidatos .kpi-cell.tipo-dep    { --cell-accent:#6366f1; --cell-glow:rgba(99,102,241,0.06);  --cell-icon:#6366f1; --cell-num:#4f46e5; }
#panelIndicadoresCandidatos .kpi-cell.tipo-puesto { --cell-accent:#10b981; --cell-glow:rgba(16,185,129,0.06); --cell-icon:#10b981; --cell-num:#059669; }
#panelIndicadoresCandidatos .kpi-cell.tipo-total  { --cell-accent:#f59e0b; --cell-glow:rgba(245,158,11,0.07);  --cell-icon:#f59e0b; --cell-num:#d97706; }
#panelIndicadoresCandidatos .kpi-cell-top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:0.85rem; }
#panelIndicadoresCandidatos .kpi-icon-wrap {
    border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;
    transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
}
#panelIndicadoresCandidatos .kpi-icon-wrap i { font-family:'boxicons' !important; font-style:normal; font-weight:400 !important; font-size:inherit !important; color:inherit !important; line-height:1; display:inline-block !important; }
#panelIndicadoresCandidatos .kpi-cell.tipo-dep    .kpi-icon-wrap { background:#ede9fe; border:1px solid #c4b5fd; color:#6366f1; box-shadow:0 3px 10px rgba(99,102,241,0.28); }
#panelIndicadoresCandidatos .kpi-cell.tipo-puesto .kpi-icon-wrap { background:#d1fae5; border:1px solid #6ee7b7; color:#059669; box-shadow:0 3px 10px rgba(16,185,129,0.28); }
#panelIndicadoresCandidatos .kpi-cell.tipo-total  .kpi-icon-wrap { background:#fef3c7; border:1px solid #fcd34d; color:#d97706; box-shadow:0 3px 10px rgba(245,158,11,0.28); }
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-icon-wrap { display:none !important; }
#panelIndicadoresCandidatos .kpi-corner-icon {
    position:absolute; top:6px; left:8px;
    font-size:4.2rem; line-height:1;
    color:var(--cell-icon); opacity:0.07;
    pointer-events:none; user-select:none; z-index:0;
    transition:opacity 0.4s ease, transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
}
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-corner-icon { display:none !important; }
#panelIndicadoresCandidatos .kpi-cell-status {
    font-size:0.62rem; font-weight:600; letter-spacing:0.06em; text-transform:uppercase;
    color:var(--cell-icon); background:color-mix(in srgb,var(--cell-icon) 10%,transparent);
    border-radius:20px; padding:0.18rem 0.55rem; opacity:0.85;
}
#panelIndicadoresCandidatos .kpi-num { font-size:1.85rem; font-weight:700; line-height:1; color:var(--cell-num); }
#panelIndicadoresCandidatos .kpi-lbl { font-size:0.73rem; font-weight:500; color:#6b7280; margin-top:0.3rem; }
#panelIndicadoresCandidatos .kpi-bar-track { margin-top:0.9rem; height:3px; background:color-mix(in srgb,var(--cell-icon) 12%,transparent); border-radius:99px; overflow:hidden; }
#panelIndicadoresCandidatos .kpi-bar-fill  { height:100%; width:0%; background:var(--cell-icon); border-radius:99px; transition:width 1s cubic-bezier(0.4,0,0.2,1) 0.3s; }
#panelIndicadoresCandidatos .kpi-row-new.mode-default .kpi-cell { padding:1.15rem 1.25rem 1.05rem; }
@media (max-width: 767px) {
    #panelIndicadoresCandidatos .kpi-row-new { grid-template-columns:1fr 1fr; }
    #panelIndicadoresCandidatos .kpi-num { font-size:1.5rem; }
}
@media (max-width: 480px) {
    #panelIndicadoresCandidatos .kpi-row-new { grid-template-columns:1fr; }
}
body.dark-mode #panelIndicadoresCandidatos .kpi-cell { background:#1a1d2e; border-color:rgba(99,102,241,0.18); box-shadow:0 2px 16px rgba(0,0,0,0.35),0 1px 4px rgba(0,0,0,0.2),inset 4px 0 0 var(--cell-accent); }
body.dark-mode #panelIndicadoresCandidatos .kpi-toggle-btn { background:#1a1d2e; }
body.dark-mode #panelIndicadoresCandidatos .kpi-lbl { color:#8b90b0; }
/* Modo oscuro - Offcanvas Nuevo Candidato */
body.dark-mode #offcanvasAddCandidato { background: #1e293b; border-left: 1px solid rgba(148, 163, 184, 0.2); }
body.dark-mode #offcanvasAddCandidato .offcanvas-header { border-bottom-color: rgba(148, 163, 184, 0.2); }
body.dark-mode #offcanvasAddCandidato .offcanvas-title { color: #f1f5f9; }
body.dark-mode #offcanvasAddCandidato .form-label { color: #cbd5e1; }
body.dark-mode #offcanvasAddCandidato .form-control,
body.dark-mode #offcanvasAddCandidato .form-select { background: #334155; border-color: rgba(148, 163, 184, 0.3); color: #f1f5f9; }
body.dark-mode #offcanvasAddCandidato .form-control::placeholder { color: #94a3b8; }
body.dark-mode #offcanvasAddCandidato .form-control:focus,
body.dark-mode #offcanvasAddCandidato .form-select:focus { background: #475569; border-color: #6366f1; color: #f1f5f9; }
body.dark-mode #offcanvasAddCandidato .form-check-label { color: #cbd5e1; }
body.dark-mode #offcanvasAddCandidato .text-danger { color: #f87171 !important; }
/* Modo oscuro - Modal Resumen Postulación */
body.dark-mode #modalResumenPostulacion .modal-content { background: #1e293b; border: 1px solid rgba(148, 163, 184, 0.2); }
body.dark-mode #modalResumenPostulacion .modal-header { border-bottom-color: rgba(148, 163, 184, 0.2); }
body.dark-mode #modalResumenPostulacion .modal-title { color: #f1f5f9; }
body.dark-mode #modalResumenPostulacion .modal-body { color: #e2e8f0; }
body.dark-mode #modalResumenPostulacion .btn-close { filter: none; }

/* Modal Resumen Candidato - mejora visual (modo claro y oscuro) */
.modal-resumen-candidato .modal-content { border-radius: 14px; overflow: visible; box-shadow: 0 20px 50px rgba(0,0,0,0.15); }
.modal-resumen-candidato .resumen-candidato-modal-header { position: relative; padding: 1rem 1.25rem; padding-right: 2.75rem; border-bottom-width: 1px; }
.modal-resumen-candidato .resumen-candidato-modal-header .btn-close { position: absolute; top: -0.6rem; right: -0.6rem; margin: 0; width: 1.25rem; height: 1.25rem; border-radius: 6px; background-color: #fff; border: 1px solid rgba(0,0,0,0.12); box-shadow: 0 2px 6px rgba(0,0,0,0.1); opacity: 0.95; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; }
.modal-resumen-candidato .resumen-candidato-modal-header .btn-close:hover { opacity: 1; background-color: #f1f3f5; }
/* Modo oscuro: botón X gris con icono blanco, sin recorte */
body.dark-mode .modal-resumen-candidato .resumen-candidato-modal-header .btn-close { background-color: #475569 !important; border-color: rgba(148, 163, 184, 0.45) !important; box-shadow: 0 2px 8px rgba(0,0,0,0.35); color: #f1f5f9; opacity: 1; --bs-btn-close-color: #f1f5f9; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23f1f5f9'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") !important; }
body.dark-mode .modal-resumen-candidato .resumen-candidato-modal-header .btn-close:hover { background-color: #64748b !important; color: #fff; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23ffffff'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") !important; }
.modal-resumen-candidato .resumen-candidato-modal-body { padding: 1.25rem 1.5rem; border-radius: 0 0 14px 14px; overflow: hidden; }
.resumen-candidato-card { border-radius: 12px; padding: 1rem 1.25rem; background: rgba(105, 108, 255, 0.06); border: 1px solid rgba(105, 108, 255, 0.15); }
.resumen-candidato-card .resumen-row { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.5rem; padding: 0.5rem 0; border-bottom: 1px solid rgba(0,0,0,0.06); }
.resumen-candidato-card .resumen-row:last-child { border-bottom: none; }
.resumen-candidato-card .resumen-label { font-size: 0.8rem; font-weight: 500; color: #6c757d; min-width: 100px; }
.resumen-candidato-card .resumen-value { font-size: 0.95rem; font-weight: 600; color: #212529; }
.btn-enviar-postulacion { font-weight: 600; border-radius: 10px; min-height: 44px; transition: transform 0.15s ease, box-shadow 0.15s ease; }
.btn-enviar-postulacion:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(105, 108, 255, 0.35); }
body.dark-mode .resumen-candidato-card { background: rgba(99, 102, 241, 0.12); border-color: rgba(148, 163, 184, 0.2); }
body.dark-mode .resumen-candidato-card .resumen-row { border-bottom-color: rgba(148, 163, 184, 0.15); }
body.dark-mode .resumen-candidato-card .resumen-label { color: #94a3b8; }
body.dark-mode .resumen-candidato-card .resumen-value { color: #f1f5f9; }
body.dark-mode .btn-enviar-postulacion:hover { box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4); }
@media (max-width: 576px) {
    .modal-resumen-candidato .resumen-candidato-modal-body .d-flex { justify-content: stretch !important; }
    .btn-enviar-postulacion { width: 100%; justify-content: center; }
}
/* Calendario Flatpickr - solo hasta hoy; modo claro legible */
.fecha-postulacion-wrapper.fecha-acta-wrapper { position: relative; z-index: 1; }
#candidato_fecha_postulacion { width: 100%; cursor: pointer; }
.flatpickr-calendar .flatpickr-monthDropdown-months { appearance: none !important; background-image: none !important; -webkit-appearance: none; -moz-appearance: none; }
.flatpickr-calendar { z-index: 99999 !important; position: fixed !important; transform: scale(1.12); transform-origin: top left; background: #fff !important; border: 1px solid rgba(0,0,0,0.12) !important; box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important; }
.flatpickr-calendar.open { display: block !important; visibility: visible !important; opacity: 1 !important; }
body:not(.dark-mode) .flatpickr-calendar { background: #fff !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day { color: #374151 !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day.flatpickr-disabled { color: #9ca3af !important; background: #f3f4f6 !important; cursor: not-allowed !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day.selected { background: #696cff !important; border-color: #696cff !important; color: #fff !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day.today { border-color: #696cff !important; border-width: 2px !important; font-weight: 600 !important; background: #eef2ff !important; color: #4338ca !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day.today:hover { background: #c7d2fe !important; border-color: #696cff !important; color: #3730a3 !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-day:hover:not(.flatpickr-disabled) { background: #f3f4f6 !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-current-month { color: #111827 !important; }
body:not(.dark-mode) .flatpickr-calendar .flatpickr-weekdays { color: #6b7280 !important; }
.flatpickr-calendar .flatpickr-day.today { border-color: #696cff !important; border-width: 2px !important; font-weight: 600 !important; background-color: #f0f0ff !important; }
.flatpickr-calendar .flatpickr-day.today:hover { background-color: #e0e0ff !important; border-color: #696cff !important; }
#tablaCandidatos thead th { background-color: rgba(105, 108, 255, 0.1); font-weight: 600; }
#tablaCandidatos th.col-acciones-candidatos { min-width: 220px; }
#tablaCandidatos .btn-accion-candidato { white-space: nowrap; }
</style>

<script>
window.puedeGestionarCandidatos = <?= json_encode(!empty($puedeGestionarCandidatos ?? false)) ?>;
</script>
