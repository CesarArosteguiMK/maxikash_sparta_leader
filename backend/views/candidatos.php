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
(function() {
    function initCandidatos() {
        if (!document.getElementById("tablaCandidatos")) return;
        var form = document.getElementById("formAgregarCandidato");
        if (form && !form._candidatosBound) {
            form._candidatosBound = true;
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                if (window._candidatoEditId) guardarCandidatoEdicion();
                else guardarCandidatoAbrirResumen();
            });
        }

        if (!document._candidatosClickBound) {
            document._candidatosClickBound = true;
            document.addEventListener("click", candidatosTableClick);
            var docPrefetchTimer = null;
            function prefetchDocumentacion(id) {
                if (!id) return;
                if (window._documentacionCache && window._documentacionCache.id === id) return;
                var sep = capHumApiUrl("CapHum/getDocumentosCandidatoList").indexOf("?") !== -1 ? "&" : "?";
                fetch(capHumApiUrl("CapHum/getDocumentosCandidatoList") + sep + "id_candidato=" + id).then(function(r){ return r.json(); }).then(function(res) {
                    if (res.success && res.datos) window._documentacionCache = { id: id, data: res.datos };
                }).catch(function(){});
            }
            document.addEventListener("mouseover", function(ev) {
                var btn = ev.target && ev.target.closest ? ev.target.closest(".btn-documentacion-candidato") : null;
                if (!btn) {
                    if (docPrefetchTimer) { clearTimeout(docPrefetchTimer); docPrefetchTimer = null; }
                    return;
                }
                var id = btn.getAttribute("data-id");
                if (!id) return;
                if (window._documentacionCache && window._documentacionCache.id === id) return;
                if (docPrefetchTimer) clearTimeout(docPrefetchTimer);
                docPrefetchTimer = setTimeout(function() {
                    docPrefetchTimer = null;
                    prefetchDocumentacion(id);
                }, 50);
            });
            document.addEventListener("mousedown", function(ev) {
                var btn = ev.target && ev.target.closest ? ev.target.closest(".btn-documentacion-candidato") : null;
                if (!btn) return;
                var id = btn.getAttribute("data-id");
                prefetchDocumentacion(id);
            });
        }

        var offcanvasEl = document.getElementById("offcanvasAddCandidato");
        if (offcanvasEl && !offcanvasEl._candidatosOffcanvasBound) {
            offcanvasEl._candidatosOffcanvasBound = true;
            offcanvasEl.addEventListener("show.bs.offcanvas", function() {
                var btnSubmit = document.getElementById("btnSubmitCandidato");
                if (!btnSubmit) return;
                if (window._candidatoEditId) {
                    btnSubmit.innerHTML = "<i class=\"bx bx-edit-alt me-1\"></i> Actualizar";
                    btnSubmit.className = "btn btn-success me-2";
                } else {
                    btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar";
                    btnSubmit.className = "btn btn-primary me-2";
                }
            });
            offcanvasEl.addEventListener("hidden.bs.offcanvas", function() {
                var form = document.getElementById("formAgregarCandidato");
                if (form) {
                    form.reset();
                    window._candidatoEditId = null;
                }
                var titulo = document.getElementById("offcanvasCandidatoTitulo");
                if (titulo) titulo.textContent = "Nuevo Candidato";
                var btnSubmit = document.getElementById("btnSubmitCandidato");
                if (btnSubmit) {
                    btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar";
                    btnSubmit.className = "btn btn-primary me-2";
                }
                var fpInput = document.getElementById("candidato_fecha_postulacion");
                if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
                var divLegion = document.getElementById("div_candidato_legion");
                var chkLegion = document.getElementById("candidato_asignar_legion");
                var selLegion = document.getElementById("candidato_id_legion");
                if (divLegion) divLegion.style.display = "none";
                if (chkLegion) chkLegion.checked = false;
                if (selLegion) selLegion.value = "";
                var selPuesto = document.getElementById("candidato_id_puesto");
                var selJefe = document.getElementById("candidato_id_posible_jefe");
                if (selPuesto) selPuesto.innerHTML = "<option value=''>Seleccione puesto</option>";
                if (selJefe) selJefe.innerHTML = "<option value=''>Seleccione departamento y puesto primero</option>";
            });
        }

        var selDepto = document.getElementById("candidato_id_departamento");
    if (selDepto) selDepto.addEventListener("change", function() {
        var idDepto = this.value;
        var selPuesto = document.getElementById("candidato_id_puesto");
        var selJefe = document.getElementById("candidato_id_posible_jefe");
        if (selPuesto) selPuesto.innerHTML = "<option value=''>Seleccione puesto</option>";
        if (selJefe) selJefe.innerHTML = "<option value=''>Seleccione departamento y puesto primero</option>";
        if (!idDepto) return;
        fetch("/CapHum/getPuestos", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json" },
            body: JSON.stringify({ id_departamento: idDepto })
        }).then(function(r){ return r.json(); }).then(function(res){
            if (res.success && res.datos) res.datos.forEach(function(p){
                var opt = document.createElement("option");
                opt.value = p.id;
                opt.textContent = p.nombre || p.puesto_nombre || "";
                selPuesto.appendChild(opt);
            });
        });
        // Cargar posibles jefes por departamento (se refina al elegir puesto)
        if (selJefe) {
            selJefe.innerHTML = "<option value=''>—</option>";
            fetch("/CapHum/getJefeDirecto", {
                method: "POST",
                headers: { "Content-Type": "application/json", "Accept": "application/json" },
                body: JSON.stringify({ id_departamento: idDepto, id_puesto: null })
            }).then(function(r){ return r.json(); }).then(function(res){
                selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>";
                if (res.success && res.datos && res.datos.length) res.datos.forEach(function(j){
                    var opt = document.createElement("option");
                    opt.value = j.id;
                    opt.textContent = (j.nombre_completo || "").trim() || "ID " + j.id;
                    selJefe.appendChild(opt);
                });
            }).catch(function(){ selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>"; });
        }
    });

    var selPuesto = document.getElementById("candidato_id_puesto");
    if (selPuesto) selPuesto.addEventListener("change", function() {
        var idPuesto = this.value;
        var selDepto = document.getElementById("candidato_id_departamento");
        var selJefe = document.getElementById("candidato_id_posible_jefe");
        if (!selJefe || !selDepto) return;
        selJefe.innerHTML = "<option value=''>—</option>";
        var idDepto = selDepto.value;
        if (!idDepto) { selJefe.innerHTML = "<option value=''>Seleccione departamento y puesto primero</option>"; return; }
        fetch("/CapHum/getJefeDirecto", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json" },
            body: JSON.stringify({ id_departamento: idDepto, id_puesto: idPuesto || null })
        }).then(function(r){ return r.json(); }).then(function(res){
            selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>";
            if (res.success && res.datos && res.datos.length) res.datos.forEach(function(j){
                var opt = document.createElement("option");
                opt.value = j.id;
                opt.textContent = (j.nombre_completo || "").trim() || "ID " + j.id;
                selJefe.appendChild(opt);
            });
        }).catch(function(){ selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>"; });
    });

    var btnAddCandidato = document.querySelector("[data-bs-target=\"#offcanvasAddCandidato\"]");
    if (btnAddCandidato) btnAddCandidato.addEventListener("click", function() {
        window._candidatoEditId = null;
        document.getElementById("offcanvasCandidatoTitulo").textContent = "Nuevo Candidato";
    });

    initFlatpickrFechaPostulacion();
    initCopiarUrlDocumentos();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initCandidatos);
    } else {
        initCandidatos();
    }
})();

function initFlatpickrFechaPostulacion() {
    var input = document.getElementById("candidato_fecha_postulacion");
    if (!input || typeof flatpickr === "undefined") return;
    if (input._flatpickr) return;
    var hoy = new Date().toISOString().slice(0, 10);
    flatpickr(input, {
        dateFormat: "Y-m-d",
        defaultDate: hoy,
        maxDate: hoy,
        allowInput: false,
        clickOpens: true,
        appendTo: document.body,
        static: false,
        locale: (typeof flatpickr !== "undefined" && flatpickr.l10ns && flatpickr.l10ns.es) ? flatpickr.l10ns.es : undefined
    });
}

function toggleLegionCandidato() {
    var div = document.getElementById("div_candidato_legion");
    var chk = document.getElementById("candidato_asignar_legion");
    div.style.display = chk && chk.checked ? "block" : "none";
    if (!chk.checked) document.getElementById("candidato_id_legion").value = "";
}

function candidatosTableClick(e) {
    var target = e.target;
    var tabla = document.getElementById("tablaCandidatos");
    if (!tabla || !tabla.contains(target)) return;
    var btn = target.closest(".btn-editar-candidato");
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        var id = btn.getAttribute("data-id");
        if (id) window.editarCandidato(parseInt(id, 10));
        return;
    }
    btn = target.closest(".btn-reenviar-candidato");
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        var id = btn.getAttribute("data-id");
        if (id) window.abrirModalReenviarPostulacion(parseInt(id, 10));
        return;
    }
    btn = target.closest(".btn-documentacion-candidato");
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        var id = btn.getAttribute("data-id");
        var nombre = btn.getAttribute("data-nombre") || "";
        var cached = (window._documentacionCache && window._documentacionCache.id === id) ? window._documentacionCache.data : null;
        if (id) abrirModalDocumentacionCandidato(parseInt(id, 10), nombre, cached);
        return;
    }
    btn = target.closest(".btn-eliminar-candidato");
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        var id = btn.getAttribute("data-id");
        if (id) window.eliminarCandidato(parseInt(id, 10));
    }
}

function abrirModalDocumentacionCandidato(idCandidato, nombreCandidato, cachedPayload) {
    var modal = document.getElementById("modalDocumentacionCandidato");
    var label = document.getElementById("modalDocumentacionCandidatoNombre");
    var bloqueVerif = document.getElementById("modalDocumentacionCandidatoVerificacion");
    var bloqueMetricas = document.getElementById("modalDocumentacionCandidatoMetricas");
    var bloqueAccionVerificar = document.getElementById("modalDocumentacionCandidatoAccionVerificar");
    var lista = document.getElementById("modalDocumentacionCandidatoLista");
    var cargando = document.getElementById("modalDocumentacionCandidatoCargando");
    var vacio = document.getElementById("modalDocumentacionCandidatoVacio");
    if (label) label.textContent = nombreCandidato ? "Candidato: " + nombreCandidato : "";
    if (bloqueVerif) { bloqueVerif.classList.add("d-none"); bloqueVerif.innerHTML = ""; }
    if (bloqueMetricas) { bloqueMetricas.classList.add("d-none"); bloqueMetricas.innerHTML = ""; }
    if (bloqueAccionVerificar) bloqueAccionVerificar.classList.add("d-none");
    if (cachedPayload && cachedPayload.documentos) {
        if (cargando) cargando.classList.add("d-none");
        if (vacio) vacio.classList.add("d-none");
    } else {
        if (cargando) cargando.classList.remove("d-none");
        if (vacio) vacio.classList.add("d-none");
    }
    lista.innerHTML = "";
    var bsModal = modal && window.bootstrap && window.bootstrap.Modal ? new window.bootstrap.Modal(modal) : null;
    if (bsModal) bsModal.show();

    if (cachedPayload && (cachedPayload.documentos || cachedPayload.metricas)) {
        var docs = cachedPayload.documentos || [];
        var verif = cachedPayload.verificacion_expediente || null;
        var metricas = cachedPayload.metricas || null;
        renderLista(docs, verif);
        renderMetricas(metricas);
        if (bloqueAccionVerificar) bloqueAccionVerificar.classList.add("d-none");
        if (verif) {
            if (bloqueVerif) { bloqueVerif.classList.remove("d-none"); bloqueVerif.innerHTML = ""; }
            renderVerificacion(verif);
        } else if (metricas && metricas.expediente_completo && bloqueVerif) {
            bloqueVerif.classList.remove("d-none");
            bloqueVerif.innerHTML = "<div class=\"alert alert-info small mb-0\"><i class=\"fa fa-hourglass-half me-1\"></i>Verificación en proceso.</div>";
        }
    }

    function renderMetricas(m) {
        if (!bloqueMetricas || !m) return;
        bloqueMetricas.classList.remove("d-none");
        var total = m.total_documentos != null ? m.total_documentos : 0;
        var requeridos = m.documentos_requeridos != null ? m.documentos_requeridos : 11;
        var pct = m.porcentaje != null ? m.porcentaje : (requeridos > 0 ? Math.min(100, Math.round((total / requeridos) * 100)) : 0);
        var completo = m.expediente_completo === true;
        var html = "<div class=\"card border shadow-none\"><div class=\"card-header py-2 bg-light\"><strong><i class=\"fa fa-chart-pie me-1\"></i>Métricas del expediente</strong></div><div class=\"card-body py-2 small\">";
        html += "<p class=\"text-muted mb-2\">Resumen de documentación recibida (cuántos documentos obligatorios ha subido el candidato).</p>";
        html += "<div class=\"row g-2 align-items-center\">";
        html += "<div class=\"col-6 col-md-4\"><span class=\"text-muted d-block\">Documentos subidos</span><strong class=\"text-primary\">" + total + " de " + requeridos + "</strong></div>";
        html += "<div class=\"col-6 col-md-4\"><span class=\"text-muted d-block\">Avance</span><strong>" + pct + "%</strong>";
        if (pct < 100) html += " <div class=\"progress mt-1\" style=\"height:6px;\"><div class=\"progress-bar\" role=\"progressbar\" style=\"width:" + pct + "%\" aria-valuenow=\"" + pct + "\" aria-valuemin=\"0\" aria-valuemax=\"100\"></div></div>";
        html += "</div>";
        html += "<div class=\"col-6 col-md-4\"><span class=\"text-muted d-block\">Expediente completo</span><strong class=\"" + (completo ? "text-success" : "text-secondary") + "\">" + (completo ? "Sí" : "No") + "</strong></div>";
        html += "</div></div></div>";
        bloqueMetricas.innerHTML = html;
    }

    function renderVerificacion(v) {
        if (!bloqueVerif || !v) return;
        bloqueVerif.classList.remove("d-none");
        var scoreFrente = v.identificacion_frente_score != null ? Number(v.identificacion_frente_score) : null;
        var scoreReverso = v.identificacion_reverso_score != null ? Number(v.identificacion_reverso_score) : null;
        var checksOk = v.checks_ok != null ? parseInt(v.checks_ok, 10) : null;
        var checksTotales = v.checks_totales != null ? parseInt(v.checks_totales, 10) : null;
        var todoCoincide = v.todo_coincide === true;
        var alertas = Array.isArray(v.alertas) && v.alertas.length ? v.alertas : [];

        var confianzaNum = null;
        if (scoreFrente != null && scoreReverso != null) {
            confianzaNum = Math.round((scoreFrente + scoreReverso) / 2);
        } else if (scoreFrente != null) {
            confianzaNum = scoreFrente;
        } else if (scoreReverso != null) {
            confianzaNum = scoreReverso;
        } else if (checksTotales > 0 && checksOk != null) {
            confianzaNum = Math.round((checksOk / checksTotales) * 100);
        }
        var confianzaTexto = confianzaNum != null ? confianzaNum + "%" : "—";
        var confianzaClase = "text-secondary";
        if (confianzaNum != null) {
            if (confianzaNum >= 80) confianzaClase = "text-success";
            else if (confianzaNum >= 50) confianzaClase = "text-warning";
            else confianzaClase = "text-danger";
        }

        var html = "<div class=\"card border shadow-none\"><div class=\"card-header py-2 bg-light\"><strong><i class=\"fa fa-shield-alt me-1\"></i>Resultado de la verificación API</strong></div><div class=\"card-body py-2 small\">";
        html += "<div class=\"row g-2 mb-2 align-items-center\">";
        html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Confianza</span><strong class=\"fs-6 " + confianzaClase + "\">" + confianzaTexto + "</strong></div>";
        html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Frente</span><strong class=\"text-primary\">" + (scoreFrente != null ? scoreFrente + "%" : "—") + "</strong></div>";
        html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Reverso</span><strong class=\"text-primary\">" + (scoreReverso != null ? scoreReverso + "%" : "—") + "</strong></div>";
        html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Checks</span><strong>" + (checksOk != null && checksTotales != null ? checksOk + "/" + checksTotales : "—") + "</strong></div>";
        html += "<div class=\"col-6 col-md\"><span class=\"text-muted d-block\">Coinciden</span><strong class=\"" + (todoCoincide ? "text-success" : (v.todo_coincide === false ? "text-danger" : "text-secondary")) + "\">" + (v.todo_coincide === true ? "Sí" : (v.todo_coincide === false ? "No" : "—")) + "</strong></div>";
        html += "</div>";

        var lineasComparaciones = [];
        if (v.comparaciones && typeof v.comparaciones === "object") {
            var comp = v.comparaciones;
            var labels = {
                "nombre_frente_vs_reverso": "Nombre en INE = Nombre en reverso",
                "fecha_nac_curp_vs_mrz": "Fecha nac. (CURP) = Fecha en reverso",
                "nombre_id_vs_curp_pdf": "Nombre en INE = Nombre en CURP PDF",
                "curp_vs_fiscal": "CURP = Constancia fiscal",
                "nombre_vs_fiscal": "Nombre = Constancia fiscal",
                "curp_vs_nss": "CURP = NSS",
                "nombre_vs_nss": "Nombre = NSS",
                "nombre_vs_acta": "Nombre = Acta de nacimiento",
                "fecha_nac_vs_acta": "Fecha nac. = Acta",
                "curp_id_vs_documento": "CURP en INE = Otro documento"
            };
            Object.keys(comp).forEach(function(k) {
                var c = comp[k];
                if (!c || typeof c !== "object") return;
                if (c.coincide !== undefined) lineasComparaciones.push((labels[k] || k) + ": " + (c.coincide ? "✓ Coincide" : "✗ No coincide"));
                else if (c.es_reciente !== undefined) lineasComparaciones.push("CURP PDF: " + (c.es_reciente ? "Reciente" : (c.meses_antiguedad || "?") + " meses"));
            });
        }
        if (lineasComparaciones.length) {
            var btnId = "btnVerComparaciones_" + (Math.random().toString(36).slice(2, 9));
            html += "<div class=\"border-top pt-2 mt-2\"><button type=\"button\" class=\"btn btn-sm btn-outline-secondary\" id=\"" + btnId + "\" title=\"Ver comparaciones entre documentos\"><i class=\"fa fa-list-ul me-1\"></i>Ver comparaciones entre documentos</button></div>";
        }
        if (alertas.length) {
            html += "<div class=\"mt-2 pt-2 border-top\"><span class=\"text-muted d-block mb-1\"><strong>Alertas</strong></span><ul class=\"mb-0 ps-3\"><li class=\"text-warning\">" + alertas.join("</li><li class=\"text-warning\">") + "</li></ul></div>";
        }
        html += "</div></div>";
        bloqueVerif.innerHTML = html;

        if (lineasComparaciones.length && window.bootstrap && window.bootstrap.Popover) {
            var btnComp = document.getElementById(btnId);
            if (btnComp) {
                var popContent = "<div class=\"text-start small\" style=\"min-width: 280px;\"><p class=\"text-muted mb-2\"><strong>Comparaciones entre documentos</strong><br><span class=\"text-muted\">(que los datos del candidato coincidan en todos)</span></p><ul class=\"list-unstyled mb-0\">" + lineasComparaciones.map(function(l) {
                    var ok = l.indexOf("✓") !== -1 || l.indexOf("Reciente") !== -1;
                    return "<li class=\"py-1 border-bottom border-light\"><i class=\"fa fa-" + (ok ? "check-circle text-success" : "times-circle text-danger") + " me-2\"></i>" + l + "</li>";
                }).join("") + "</ul></div>";
                new window.bootstrap.Popover(btnComp, { content: popContent, html: true, trigger: "click", placement: "bottom", container: "body" });
            }
        }
    }

    function badgeVerificacion(tipoDoc, v) {
        if (!v || !tipoDoc) return "";
        var t = (tipoDoc + "").trim().toUpperCase();
        if (t.indexOf("REVERSO") !== -1) {
            var r = v.identificacion_reverso_score;
            if (r == null) return "";
            return "<span class=\"badge bg-primary ms-1\" title=\"Veracidad con el candidato\">" + r + "%</span>";
        }
        if (t === "IDENTIFICACIÓN OFICIAL" || t === "IDENTIFICACION OFICIAL") {
            var s = v.identificacion_frente_score;
            if (s == null) return "";
            return "<span class=\"badge bg-primary ms-1\" title=\"Veracidad con el candidato\">" + s + "%</span>";
        }
        var comp = v.comparaciones || {};
        if (t.indexOf("CURP") !== -1 && t.indexOf("ACTA") === -1) {
            var c1 = comp.nombre_id_vs_curp_pdf || comp.curp_id_vs_documento;
            if (c1 && c1.coincide !== undefined) return c1.coincide ? "<span class=\"badge bg-success ms-1\" title=\"Coincide con INE\">Coincide</span>" : "<span class=\"badge bg-danger ms-1\" title=\"No coincide\">No coincide</span>";
        }
        if (t.indexOf("CONSTANCIA") !== -1 || t.indexOf("FISCAL") !== -1) {
            var c2 = comp.curp_vs_fiscal || comp.nombre_vs_fiscal;
            if (c2 && c2.coincide !== undefined) return c2.coincide ? "<span class=\"badge bg-success ms-1\">Coincide</span>" : "<span class=\"badge bg-danger ms-1\">No coincide</span>";
        }
        if (t.indexOf("NSS") !== -1 || t.indexOf("SEGURIDAD SOCIAL") !== -1) {
            var c3 = comp.curp_vs_nss || comp.nombre_vs_nss;
            if (c3 && c3.coincide !== undefined) return c3.coincide ? "<span class=\"badge bg-success ms-1\">Coincide</span>" : "<span class=\"badge bg-danger ms-1\">No coincide</span>";
        }
        if (t.indexOf("ACTA") !== -1) {
            var c4 = comp.nombre_vs_acta || comp.fecha_nac_vs_acta;
            if (c4 && c4.coincide !== undefined) return c4.coincide ? "<span class=\"badge bg-success ms-1\">Coincide</span>" : "<span class=\"badge bg-danger ms-1\">No coincide</span>";
        }
        return "";
    }

    function renderLista(datos, verif) {
        if (cargando) cargando.classList.add("d-none");
        lista.innerHTML = "";
        if (!datos || datos.length === 0) {
            if (vacio) vacio.classList.remove("d-none");
            return;
        }
        if (vacio) vacio.classList.add("d-none");
        datos.forEach(function(d) {
            var item = document.createElement("div");
            item.className = "list-group-item list-group-item-action d-flex justify-content-between align-items-center";
            var fecha = d.fecha_carga ? new Date(d.fecha_carga).toLocaleDateString("es-MX") : "";
            var badge = badgeVerificacion(d.tipo_documento, verif);
            var esValidado = parseInt(d.validado || 0, 10) === 1;
            var btnValidarClase = esValidado ? "btn-success" : "btn-outline-success";
            var btnValidarIcon = esValidado ? "fa-check-circle" : "fa-check";
            var btnValidarTitle = esValidado ? "Validado — clic para retirar" : "Marcar como validado";
            if (esValidado) {
                item.style.borderLeft = "3px solid #198754";
                item.style.background = "#f0fdf4";
            }
            var btnEliminarHtml = esValidado
                ? "<span class=\"btn btn-sm btn-outline-secondary disabled\" title=\"No se puede eliminar un documento validado\"><i class=\"fa fa-trash\"></i></span>"
                : "<button type=\"button\" class=\"btn btn-sm btn-outline-danger btn-eliminar-doc-candidato\" data-id=\"" + d.id + "\" title=\"Eliminar\"><i class=\"fa fa-trash\"></i></button>";
            item.innerHTML = "<div class=\"d-flex align-items-center flex-wrap\"><div><strong>" + (d.tipo_documento || "Documento") + "</strong>" + (esValidado ? " <span class=\"badge bg-success ms-1\">Validado</span>" : "") + "<br><small class=\"text-muted\">" + (d.nombre_archivo || "") + (fecha ? " · " + fecha : "") + "</small></div>" + badge + "</div>" +
                "<div class=\"d-flex gap-1 align-items-center\">" +
                "<button type=\"button\" class=\"btn btn-sm " + btnValidarClase + " btn-validar-doc-candidato\" data-id=\"" + d.id + "\" data-validado=\"" + (esValidado ? 1 : 0) + "\" title=\"" + btnValidarTitle + "\"><i class=\"fa " + btnValidarIcon + "\"></i></button>" +
                "<a href=\"/CapHum/verDocumentoCandidato/" + d.id + "\" target=\"_blank\" class=\"btn btn-sm btn-outline-primary\" title=\"Abrir\"><i class=\"fa fa-eye\"></i></a>" +
                btnEliminarHtml +
                "</div>";
            lista.appendChild(item);
        });
        lista.querySelectorAll(".btn-validar-doc-candidato").forEach(function(btn) {
            btn.addEventListener("click", function() {
                var idDoc = parseInt(btn.getAttribute("data-id"), 10);
                var actual = parseInt(btn.getAttribute("data-validado"), 10);
                var iconEl = btn.querySelector("i");
                var iconClass = iconEl ? iconEl.className : "";
                btn.disabled = true;
                if (iconEl) { iconEl.className = "fa fa-spinner fa-spin"; }

                fetch("/CapHum/validarDocumentoCandidato", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest" },
                    body: "id=" + idDoc + "&validado=" + (actual ? 0 : 1)
                }).then(function(r){ return r.json(); }).then(function(res) {
                    btn.disabled = false;
                    if (iconEl) iconEl.className = iconClass;
                    if (res.success) {
                        cargarDocumentos();
                        if (typeof getCandidatos === "function") getCandidatos();
                        if (res.datos && res.datos.todos_validados) {
                            if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Expediente validado", text: "Todos los documentos han sido validados. El estatus del candidato cambió a Validado.", timer: 3000, showConfirmButton: false });
                        }
                    } else {
                        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo actualizar." });
                    }
                }).catch(function() {
                    btn.disabled = false;
                    if (iconEl) iconEl.className = iconClass;
                    if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." });
                });
            });
        });
        lista.querySelectorAll(".btn-eliminar-doc-candidato").forEach(function(btn) {
            btn.addEventListener("click", function() {
                var idDoc = parseInt(btn.getAttribute("data-id"), 10);
                if (typeof Swal !== "undefined") {
                    Swal.fire({ title: "¿Eliminar documento?", text: "Se quitará del expediente.", icon: "warning", showCancelButton: true, confirmButtonText: "Sí, eliminar", cancelButtonText: "Cancelar" }).then(function(r) {
                        if (r.isConfirmed) eliminarDocYRecargar(idDoc);
                    });
                } else if (confirm("¿Eliminar este documento?")) eliminarDocYRecargar(idDoc);
            });
        });
    }

    function eliminarDocYRecargar(idDoc) {
        fetch("/CapHum/eliminarDocumentoCandidato", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded", "X-Requested-With": "XMLHttpRequest" },
            body: "id=" + idDoc
        }).then(function(r){ return r.json(); }).then(function(res) {
            if (res.success) {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Eliminado", text: res.mensaje || "Documento eliminado." });
                cargarDocumentos();
            } else {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo eliminar." });
            }
        }).catch(function() {
            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." });
        });
    }

    function cargarDocumentos() {
        var sep = (typeof capHumApiUrl === "function" ? capHumApiUrl("CapHum/getDocumentosCandidatoList") : "/CapHum/getDocumentosCandidatoList").indexOf("?") !== -1 ? "&" : "?";
        var urlDoc = (typeof capHumApiUrl === "function" ? capHumApiUrl("CapHum/getDocumentosCandidatoList") : "/CapHum/getDocumentosCandidatoList") + sep + "id_candidato=" + idCandidato;
        fetch(urlDoc).then(function(r){ return r.json(); }).then(function(res) {
            var docs = (res.datos && res.datos.documentos) ? res.datos.documentos : (res.datos && Array.isArray(res.datos) ? res.datos : []);
            var verif = (res.datos && res.datos.verificacion_expediente) ? res.datos.verificacion_expediente : null;
            var metricas = (res.datos && res.datos.metricas) ? res.datos.metricas : null;
            renderLista(docs, verif);
            renderMetricas(metricas);

            if (bloqueAccionVerificar) bloqueAccionVerificar.classList.add("d-none");

            if (verif) {
                if (bloqueVerif) {
                    bloqueVerif.classList.remove("d-none");
                    bloqueVerif.innerHTML = "<div class=\"alert alert-success small mb-0\"><i class=\"fa fa-check-circle me-1\"></i>Expediente verificado automáticamente. Los resultados se muestran en cada documento.</div>";
                }
                renderVerificacion(verif);
            } else if (metricas && metricas.expediente_completo) {
                if (bloqueVerif) {
                    bloqueVerif.classList.remove("d-none");
                    bloqueVerif.innerHTML = "<div class=\"alert alert-info small mb-0\"><i class=\"fa fa-hourglass-half me-1\"></i>La verificación automática se está procesando en segundo plano. Los resultados aparecerán aquí cuando estén listos.</div>";
                }
            }
        }).catch(function() { renderLista([]); });
    }

    cargarDocumentos();
}

function abrirModalReenviarPostulacion(idCandidato) {
    window._candidatoDatosEnvio = null;
    var url = capHumApiUrl("CapHum/getCandidato/" + idCandidato);
    fetch(url).then(function(r){ return r.json(); }).then(function(res){
        if (!res.success || !res.datos) {
            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "No se encontró el candidato." });
            return;
        }
        var c = res.datos;
        var nombreCompleto = [c.nombres, c.segundo_nombre, c.apellidop, c.apellidom].filter(Boolean).join(" ");
        var html = buildResumenCandidatoHTML({
            nombreCompleto: nombreCompleto || "—",
            telefono: (c.telefono ? "(" + c.telefono + ")" : "—"),
            email: c.email || "—",
            puesto: c.nombre_puesto || "—",
            departamento: c.nombre_departamento || "—"
        });
        document.getElementById("resumenPostulacionTexto").innerHTML = html;
        document.getElementById("btnEnviarPostulacion").disabled = false;
        document.getElementById("btnEnviarPostulacion").innerHTML = "<i class='bx bx-send me-2'></i> Reenviar postulación por correo";
        window._candidatoReenviarId = c.id;
        window._candidatoReenviarEmail = c.email || "";
        var modal = new bootstrap.Modal(document.getElementById("modalResumenPostulacion"));
        modal.show();
        cargarLinkDocumentosCandidato(c.id);
    }).catch(function(){
        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "No se pudo cargar el candidato." });
    });
}

function buildResumenCandidatoHTML(o) {
    var n = o.nombreCompleto || "—";
    var t = o.telefono || "—";
    var e = o.email || "—";
    var p = o.puesto || "—";
    var d = o.departamento || "—";
    return "<div class=\"resumen-row\"><span class=\"resumen-label\">Candidato</span><span class=\"resumen-value\">" + escapeHtml(n) + "</span></div>" +
        "<div class=\"resumen-row\"><span class=\"resumen-label\">Teléfono</span><span class=\"resumen-value\">" + escapeHtml(t) + "</span></div>" +
        "<div class=\"resumen-row\"><span class=\"resumen-label\">Correo</span><span class=\"resumen-value\">" + escapeHtml(e) + "</span></div>" +
        "<div class=\"resumen-row\"><span class=\"resumen-label\">Puesto</span><span class=\"resumen-value\">" + escapeHtml(p) + "</span></div>" +
        "<div class=\"resumen-row\"><span class=\"resumen-label\">Departamento</span><span class=\"resumen-value\">" + escapeHtml(d) + "</span></div>";
}
function escapeHtml(s) {
    if (!s) return "";
    var div = document.createElement("div");
    div.textContent = s;
    return div.innerHTML;
}

function cargarLinkDocumentosCandidato(idCandidato) {
    if (!idCandidato) return;
    var bloque = document.getElementById("bloqueLinkDocumentos");
    var input = document.getElementById("inputUrlDocumentos");
    if (!bloque || !input) return;
    fetch(capHumApiUrl("CapHum/getTokenDocumentosCandidato"), { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: idCandidato }) })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success && res.datos && res.datos.url) {
                input.value = res.datos.url;
                input.setAttribute("title", res.datos.url);
                bloque.style.display = "block";
            }
        })
        .catch(function(){});
}

function initCopiarUrlDocumentos() {
    var btn = document.getElementById("btnCopiarUrlDocumentos");
    var input = document.getElementById("inputUrlDocumentos");
    if (!btn || !input) return;
    if (btn._copiarBound) return;
    btn._copiarBound = true;

    function showToastUrl(msg) {
        var t = document.getElementById("toastUrlDocumentos");
        if (!t) return;
        t.textContent = msg;
        t.classList.add("show");
        setTimeout(function() { t.classList.remove("show"); }, 2200);
    }

    btn.addEventListener("click", function() {
        var url = input.value;
        if (!url) { showToastUrl("⚠ Ingresa una URL primero"); return; }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                showToastUrl("✓  URL copiada al portapapeles");
            }).catch(function() {
                input.select();
                input.setSelectionRange(0, 99999);
                try {
                    document.execCommand("copy");
                    showToastUrl("✓  URL copiada al portapapeles");
                } catch (e) {
                    if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Copiado", text: "URL copiada.", timer: 1500, showConfirmButton: false });
                }
            });
        } else {
            input.select();
            input.setSelectionRange(0, 99999);
            try {
                document.execCommand("copy");
                showToastUrl("✓  URL copiada al portapapeles");
            } catch (e) {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Copiado", text: "URL copiada.", timer: 1500, showConfirmButton: false });
            }
        }
    });

    var btnAbrir = document.getElementById("btnAbrirUrlDocumentos");
    if (btnAbrir && !btnAbrir._abrirBound) {
        btnAbrir._abrirBound = true;
        btnAbrir.addEventListener("click", function() {
            var url = input.value;
            if (!url) return;
            if (!/^https?:\/\//i.test(url)) url = "https://" + url;
            window.open(url, "_blank", "noopener,noreferrer");
        });
    }
}

function editarCandidato(id) {
    window._candidatoEditId = id;
    var titulo = document.getElementById("offcanvasCandidatoTitulo");
    if (titulo) titulo.textContent = "Editar Candidato";
    var btnSubmit = document.getElementById("btnSubmitCandidato");
    if (btnSubmit) {
        btnSubmit.innerHTML = "<i class=\"bx bx-edit-alt me-1\"></i> Actualizar";
        btnSubmit.className = "btn btn-success me-2";
    }
    fetch(capHumApiUrl("CapHum/getCandidato/" + id)).then(function(r){ return r.json(); }).then(function(res){
        if (!res.success || !res.datos) {
            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "No se encontró el candidato." });
            return;
        }
        var c = res.datos;
        var form = document.getElementById("formAgregarCandidato");
        if (!form) return;
        if (form.nombres) form.nombres.value = c.nombres || "";
        if (form.segundo_nombre) form.segundo_nombre.value = c.segundo_nombre || "";
        if (form.apellidop) form.apellidop.value = c.apellidop || "";
        if (form.apellidom) form.apellidom.value = c.apellidom || "";
        if (form.telefono) form.telefono.value = c.telefono || "";
        if (form.email) form.email.value = c.email || "";
        if (form.id_pais) form.id_pais.value = c.id_pais || "";
        if (form.id_departamento) form.id_departamento.value = c.id_departamento || "";
        if (form.usuario) form.usuario.value = c.usuario || "";
        if (form.contrasena) form.contrasena.value = c.contrasena || "";
        var fpInput = document.getElementById("candidato_fecha_postulacion");
        if (fpInput && c.fecha_postulacion) fpInput.value = c.fecha_postulacion;
        var chkLegion = document.getElementById("candidato_asignar_legion");
        var divLegion = document.getElementById("div_candidato_legion");
        var selLegion = document.getElementById("candidato_id_legion");
        if (c.id_legion) {
            if (chkLegion) chkLegion.checked = true;
            if (divLegion) divLegion.style.display = "block";
            if (selLegion) selLegion.value = c.id_legion;
        } else {
            if (chkLegion) chkLegion.checked = false;
            if (divLegion) divLegion.style.display = "none";
            if (selLegion) selLegion.value = "";
        }
        var selPuesto = document.getElementById("candidato_id_puesto");
        var selJefe = document.getElementById("candidato_id_posible_jefe");
        selPuesto.innerHTML = "<option value=''>Seleccione puesto</option>";
        selJefe.innerHTML = "<option value=''>—</option>";

        setTimeout(function() { abrirOffcanvasCandidato(); }, 0);

        if (!c.id_departamento) {
            selJefe.innerHTML = "<option value=''>Seleccione departamento y puesto primero</option>";
            return;
        }
        fetch("/CapHum/getPuestos", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id_departamento: c.id_departamento }) })
        .then(function(r){ return r.json(); })
        .then(function(rPuestos){
            if (rPuestos.success && rPuestos.datos) rPuestos.datos.forEach(function(p){
                var opt = document.createElement("option");
                opt.value = p.id;
                opt.textContent = p.nombre || "";
                selPuesto.appendChild(opt);
            });
            selPuesto.value = c.id_puesto || "";
            return fetch("/CapHum/getJefeDirecto", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id_departamento: c.id_departamento, id_puesto: c.id_puesto || null }) });
        })
        .then(function(r){ return r.json(); })
        .then(function(rJefes){
            if (!selJefe) return;
            selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>";
            if (rJefes && rJefes.success && rJefes.datos) rJefes.datos.forEach(function(j){
                var opt = document.createElement("option");
                opt.value = j.id;
                opt.textContent = (j.nombre_completo || "").trim() || "ID " + j.id;
                selJefe.appendChild(opt);
            });
            selJefe.value = c.id_posible_jefe || "";
        })
        .catch(function(){
            if (selJefe) selJefe.innerHTML = "<option value=''>Seleccione posible jefe</option>";
        });
    }).catch(function(){
        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "No se pudo cargar el candidato." });
    });
}

function abrirOffcanvasCandidato() {
    var el = document.getElementById("offcanvasAddCandidato");
    if (!el) return;
    if (el.parentNode !== document.body) {
        document.body.appendChild(el);
    }
    if (typeof bootstrap !== "undefined" && bootstrap.Offcanvas) {
        var inst = bootstrap.Offcanvas.getOrCreateInstance(el);
        if (inst) inst.show();
    } else {
        el.classList.add("show");
        el.setAttribute("aria-hidden", "false");
        var back = document.createElement("div");
        back.className = "offcanvas-backdrop fade show";
        back.style.cssText = "position:fixed;top:0;left:0;z-index:1040;width:100vw;height:100vh;background:#000;opacity:0.5;";
        back.setAttribute("data-bs-dismiss", "offcanvas");
        document.body.appendChild(back);
    }
}

function guardarCandidatoEdicion() {
    var form = document.getElementById("formAgregarCandidato");
    if (!form || !form.checkValidity()) { form.reportValidity(); return; }
    var id = window._candidatoEditId;
    if (!id) return;
    var data = buildCandidatoPayloadFromForm();
    data.id = id;

    fetch("/CapHum/actualizarCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify(data) })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (res.success) {
            if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Candidato actualizado correctamente." });
            window._candidatoEditId = null;
            document.getElementById("offcanvasCandidatoTitulo").textContent = "Nuevo Candidato";
            var btnSubmit = document.getElementById("btnSubmitCandidato");
            if (btnSubmit) {
                btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar";
                btnSubmit.className = "btn btn-primary me-2";
            }
            form.reset();
            var fpInput = document.getElementById("candidato_fecha_postulacion");
            if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
            var inst = bootstrap.Offcanvas.getInstance(document.getElementById("offcanvasAddCandidato"));
            if (inst) inst.hide();
            getCandidatos();
        } else {
            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo actualizar." });
        }
    })
    .catch(function(){
        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." });
    });
}

function guardarCandidatoAbrirResumen() {
    var form = document.getElementById("formAgregarCandidato");
    if (!form || !form.checkValidity()) { form.reportValidity(); return; }

    var data = buildCandidatoPayloadFromForm();
    if (!data.nombres || !data.apellidop) {
        if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Faltan datos", text: "Nombre y apellido paterno son obligatorios." });
        return;
    }

    var btnSubmit = document.getElementById("btnSubmitCandidato");
    if (btnSubmit) { btnSubmit.disabled = true; }

    fetch(capHumApiUrl("CapHum/guardarCandidato"), { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify(data) })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar"; }
        if (!res.success) {
            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || res.error || "No se pudo guardar." });
            return;
        }
        var idCand = res.datos && res.datos.id;
        if (!idCand) return;

        window._candidatoNuevoId = idCand;
        window._candidatoNuevoEmail = data.email || "";
        window._candidatoDatosEnvio = null;
        window._candidatoReenviarId = null;
        window._candidatoReenviarEmail = null;

        var nombreCompleto = [data.nombres, data.segundo_nombre, data.apellidop, data.apellidom].filter(Boolean).join(" ");
        var puestoTexto = (document.getElementById("candidato_id_puesto") && document.getElementById("candidato_id_puesto").selectedIndex >= 0) ? document.getElementById("candidato_id_puesto").options[document.getElementById("candidato_id_puesto").selectedIndex].text : "—";
        var deptoTexto = (document.getElementById("candidato_id_departamento") && document.getElementById("candidato_id_departamento").selectedIndex >= 0) ? document.getElementById("candidato_id_departamento").options[document.getElementById("candidato_id_departamento").selectedIndex].text : "—";
        var html = buildResumenCandidatoHTML({
            nombreCompleto: nombreCompleto || "—",
            telefono: (data.telefono ? "(" + data.telefono + ")" : "—"),
            email: data.email || "—",
            puesto: puestoTexto,
            departamento: deptoTexto
        });
        document.getElementById("resumenPostulacionTexto").innerHTML = html;
        document.getElementById("btnEnviarPostulacion").disabled = false;
        document.getElementById("btnEnviarPostulacion").innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";

        var bloqueLink = document.getElementById("bloqueLinkDocumentos");
        var inputUrl = document.getElementById("inputUrlDocumentos");
        if (bloqueLink) bloqueLink.style.display = "none";
        if (inputUrl) inputUrl.value = "";

        cargarLinkDocumentosCandidato(idCand);

        var offcanvas = document.getElementById("offcanvasAddCandidato");
        if (offcanvas && typeof bootstrap !== "undefined") bootstrap.Offcanvas.getInstance(offcanvas).hide();
        var modal = new bootstrap.Modal(document.getElementById("modalResumenPostulacion"));
        modal.show();

        if (form) form.reset();
        var fpInput = document.getElementById("candidato_fecha_postulacion");
        if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
        getCandidatos();
    })
    .catch(function(){
        if (typeof Swal !== "undefined") Swal.close();
        if (btnSubmit) { btnSubmit.disabled = false; btnSubmit.innerHTML = "<i class=\"bx bx-save me-1\"></i> Guardar"; }
        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." });
    });
}

function buildCandidatoPayloadFromForm() {
    var form = document.getElementById("formAgregarCandidato");
    if (!form) return {};
    return {
        nombres: (form.nombres && form.nombres.value.trim()) || "",
        segundo_nombre: (form.segundo_nombre && form.segundo_nombre.value.trim()) || "",
        apellidop: (form.apellidop && form.apellidop.value.trim()) || "",
        apellidom: (form.apellidom && form.apellidom.value.trim()) || "",
        email: (form.email && form.email.value.trim()) || "",
        telefono: (form.telefono && form.telefono.value.trim()) || "",
        id_pais: (form.id_pais && form.id_pais.value) || null,
        id_departamento: (form.id_departamento && form.id_departamento.value) || null,
        id_puesto: (form.id_puesto && form.id_puesto.value) || null,
        id_posible_jefe: (form.id_posible_jefe && form.id_posible_jefe.value) || null,
        fecha_postulacion: (form.fecha_postulacion && form.fecha_postulacion.value) || null,
        id_legion: document.getElementById("candidato_asignar_legion") && document.getElementById("candidato_asignar_legion").checked && document.getElementById("candidato_id_legion") && document.getElementById("candidato_id_legion").value ? document.getElementById("candidato_id_legion").value : null,
        usuario: (form.usuario && form.usuario.value.trim()) || "",
        contrasena: (form.contrasena && form.contrasena.value.trim()) || "",
        estatus: "Por evaluar",
        notas: null,
        postulacion_enviada: 1
    };
}

function enviarPostulacionAlCandidato() {
    var btn = document.getElementById("btnEnviarPostulacion");
    if (btn.disabled) return;
    btn.disabled = true;
    btn.innerHTML = "<i class='bx bx-loader-alt bx-spin me-2'></i> Enviando...";

    if (window._candidatoReenviarId) {
        var urlReenviar = capHumApiUrl("CapHum/enviarPostulacionCandidato");
        fetch(urlReenviar, { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: window._candidatoReenviarId, email: window._candidatoReenviarEmail || "" }) })
        .then(function(r){
            return r.text().then(function(text) {
                var res;
                try { res = text ? JSON.parse(text) : {}; } catch (e) { res = null; }
                return { ok: r.ok, status: r.status, res: res, raw: text };
            });
        })
        .then(function(o){
            var res = o.res;
            window._candidatoReenviarId = null;
            window._candidatoReenviarEmail = null;
            btn.disabled = false;
            btn.innerHTML = "<i class='bx bx-send me-2'></i> Reenviar postulación por correo";
            if (!res && !o.ok) {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "El servidor respondió con " + o.status + ". Compruebe la consola (F12) o que la URL sea correcta." });
                if (console && console.error) console.error("Reenviar correo: respuesta no JSON", o.status, o.raw ? o.raw.substring(0, 300) : "");
                return;
            }
            if (res && res.success) {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Correo de postulación reenviado correctamente." });
                getCandidatos();
                setTimeout(function() { bootstrap.Modal.getInstance(document.getElementById("modalResumenPostulacion")).hide(); }, 1500);
            } else {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: (res && res.mensaje) ? res.mensaje : "No se pudo enviar el correo." });
            }
        })
        .catch(function(err){
            btn.disabled = false;
            btn.innerHTML = "<i class='bx bx-send me-2'></i> Reenviar postulación por correo";
            var msg = (err && err.message) ? err.message : "Error de conexión.";
            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: msg });
            if (console && console.error) console.error("Reenviar correo:", urlReenviar, err);
        });
        return;
    }

    if (window._candidatoNuevoId) {
        var idCand = window._candidatoNuevoId;
        var email = window._candidatoNuevoEmail || "";
        fetch(capHumApiUrl("CapHum/enviarPostulacionCandidato"), { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: idCand, email: email }) })
        .then(function(r){ return r.json(); })
        .then(function(resMail){
            window._candidatoNuevoId = null;
            window._candidatoNuevoEmail = null;
            btn.disabled = false;
            if (resMail && resMail.success) {
                btn.innerHTML = "<i class=\"bx bx-check me-2\"></i> Enviada postulación";
                if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Correo enviado. El enlace para subir documentos está en el correo y arriba." });
            } else {
                btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";
                if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Correo no enviado", text: (resMail && resMail.mensaje) ? resMail.mensaje : "Configure [mail] en backend/config/config.ini para SMTP. Use el enlace de arriba para compartir con el candidato." });
            }
            getCandidatos();
            setTimeout(function() { bootstrap.Modal.getInstance(document.getElementById("modalResumenPostulacion")).hide(); }, 2500);
        })
        .catch(function(){
            btn.disabled = false;
            btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";
            if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Error", text: "El correo no se pudo enviar. Use el enlace de arriba para compartir con el candidato." });
        });
        return;
    }

    var data = window._candidatoDatosEnvio || buildCandidatoPayloadFromForm();
    if (!data.nombres || !data.apellidop) {
        btn.disabled = false;
        btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";
        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Nombre y apellido paterno son obligatorios." });
        return;
    }

    var form = document.getElementById("formAgregarCandidato");
    fetch(capHumApiUrl("CapHum/guardarCandidato"), { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify(data) })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (res.success) {
            window._candidatoDatosEnvio = null;
            var idCand = res.datos && res.datos.id;
            cargarLinkDocumentosCandidato(idCand);
            fetch(capHumApiUrl("CapHum/enviarPostulacionCandidato"), { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: idCand, email: data.email }) })
            .then(function(r2){ return r2.json(); })
            .then(function(resMail){
                btn.disabled = false;
                btn.innerHTML = "<i class=\"bx bx-send me-2\"></i> Enviar postulación al candidato";
                if (resMail && resMail.success) {
                    btn.innerHTML = "<i class=\"bx bx-check me-2\"></i> Enviada postulación";
                    if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Candidato registrado y correo enviado. El enlace para subir documentos está en el correo y arriba." });
                } else {
                    if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Candidato guardado", text: (resMail && resMail.mensaje) ? "El correo no se envió: " + resMail.mensaje + ". Configure [mail] en backend/config/config.ini para SMTP o revise que mail() funcione." : "El correo no se pudo enviar. Use el enlace de arriba para compartir con el candidato." });
                }
                getCandidatos();
                setTimeout(function() { bootstrap.Modal.getInstance(document.getElementById("modalResumenPostulacion")).hide(); }, 2500);
            })
            .catch(function(){
                btn.disabled = false;
                btn.innerHTML = "<i class=\"bx bx-send me-2\"></i> Enviar postulación al candidato";
                if (typeof Swal !== "undefined") Swal.fire({ icon: "warning", title: "Candidato guardado", text: "El correo no se pudo enviar (error de conexión). Use el enlace de arriba para compartir con el candidato." });
                getCandidatos();
            });
            if (form) form.reset();
            var fpInput = document.getElementById("candidato_fecha_postulacion");
            if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
        } else {
            btn.disabled = false;
            btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";
            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || res.error || "No se pudo guardar." });
        }
    })
    .catch(function(){
        btn.disabled = false;
        btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";
        if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." });
    });
}

function eliminarCandidato(id) {
    if (typeof Swal === "undefined") { if (confirm("¿Eliminar candidato?")) fetch("/CapHum/eliminarCandidato", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: id }) }).then(function(r){ return r.json(); }).then(function(d){ if (d.success) getCandidatos(); }); return; }
    Swal.fire({ title: "¿Eliminar?", text: "Se eliminará el candidato.", icon: "warning", showCancelButton: true }).then(function(r){ if (r.isConfirmed) fetch("/CapHum/eliminarCandidato", { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify({ id: id }) }).then(function(res){ return res.json(); }).then(function(d){ if (d.success) { Swal.fire({ icon: "success", text: d.mensaje }); getCandidatos(); } else Swal.fire({ icon: "error", text: d.mensaje || d.error }); }); });
}
</script>
