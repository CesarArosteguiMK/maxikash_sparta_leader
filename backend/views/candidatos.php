<?php
$departamento = $departamento ?? ['datos' => []];
$paisesActivos = $paisesActivos ?? [];
$listaJefes = $listaJefes ?? [];
$candidatos = $candidatos ?? [];
?>
<div class="content-wrapper">
    <div class="card">
        <!-- Filtros -->
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Nuevos Candidatos</h5>
            <div class="row pt-3 g-2">
                <div class="col-md-4">
                    <select id="filterEstatus" class="form-select form-select-sm">
                        <option value="">Todos los estatus</option>
                        <option value="Por evaluar">Por evaluar</option>
                        <option value="En entrevista">En entrevista</option>
                        <option value="Contratado">Contratado</option>
                        <option value="Descartado">Descartado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-primary btn-sm" id="btnFiltrarCandidatos" onclick="getCandidatos()">
                        <i class="fa fa-filter"></i> Filtrar
                    </button>
                </div>
            </div>
        </div>

        <!-- KPIs (mismo tamaño que Gestión y Bajas) -->
        <?php
        $totalCand = count($candidatos);
        $porEvaluar = count(array_filter($candidatos, function($c) { return ($c['estatus'] ?? '') === 'Por evaluar'; }));
        $postulacionesEnviadas = count(array_filter($candidatos, function($c) { return !empty($c['postulacion_enviada']); }));
        ?>
        <div id="panelIndicadoresCandidatos" class="row m-4 mb-3">
            <div class="col-12">
                <div class="kpi-wrapper">
                    <div class="kpi-item">
                        <div class="card kpi-card kpi-candidatos-total shadow-sm h-100">
                            <div class="card-body">
                                <i class="bx bx-group kpi-icon"></i>
                                <div class="kpi-number" id="kpi-total-candidatos"><?= $totalCand ?></div>
                                <div class="kpi-label">Total Candidatos</div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-separator"><div class="line"></div></div>
                    <div class="kpi-item">
                        <div class="card kpi-card kpi-candidatos-evaluar shadow-sm h-100">
                            <div class="card-body">
                                <i class="bx bx-user-plus kpi-icon"></i>
                                <div class="kpi-number" id="kpi-por-evaluar"><?= $porEvaluar ?></div>
                                <div class="kpi-label">Por evaluar</div>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-separator"><div class="line"></div></div>
                    <div class="kpi-item">
                        <div class="card kpi-card kpi-candidatos-enviadas shadow-sm h-100">
                            <div class="card-body">
                                <i class="bx bx-send kpi-icon"></i>
                                <div class="kpi-number" id="kpi-postulaciones-enviadas"><?= $postulacionesEnviadas ?></div>
                                <div class="kpi-label">Postulaciones enviadas</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón Agregar Candidato (sin Plantilla) -->
        <div class="row justify-content-between m-4">
            <div class="col-8"></div>
            <div class="col-4 d-flex align-items-end justify-content-end">
                <button type="button" class="btn btn-primary add-new btn-action-size"
                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasAddCandidato">
                    <i class="bx bx-plus me-2"></i>
                    <span class="d-inline-block">Agregar Candidato</span>
                </button>
            </div>
        </div>

        <!-- Tabla -->
        <div class="card-datatable table-responsive">
            <table id="tablaCandidatos" class="table table-bordered table-hover border-top" style="width:100%">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Puesto / Departamento</th>
                        <th>Estatus</th>
                        <th class="col-acciones-candidatos">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidatos as $c): ?>
                        <?php
                        $nombre = trim(implode(' ', array_filter([$c['nombres'] ?? '', $c['segundo_nombre'] ?? '', $c['apellidop'] ?? '', $c['apellidom'] ?? ''])));
                        $contacto = ($c['email'] ?? '') . (isset($c['telefono']) && $c['telefono'] !== '' ? ' | ' . $c['telefono'] : '');
                        $puestoDepto = ($c['nombre_puesto'] ?? '-') . ' / ' . ($c['nombre_departamento'] ?? '-');
                        $estatus = $c['estatus'] ?? 'Por evaluar';
                        $id = (int)($c['id'] ?? 0);
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($nombre) ?></td>
                            <td><?= htmlspecialchars($contacto) ?></td>
                            <td><?= htmlspecialchars($puestoDepto) ?></td>
                            <td><?= htmlspecialchars($estatus) ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    <button type="button" class="btn btn-sm btn-primary btn-editar-candidato" data-id="<?= $id ?>" title="Editar"><i class="fa fa-edit"></i></button>
                                    <button type="button" class="btn btn-sm btn-info text-white btn-reenviar-candidato" data-id="<?= $id ?>" title="Reenviar correo"><i class="fa fa-envelope"></i></button>
                                    <button type="button" class="btn btn-sm btn-danger btn-eliminar-candidato" data-id="<?= $id ?>" title="Eliminar"><i class="fa fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
                <div id="bloqueLinkDocumentos" class="mb-3" style="display: none;">
                    <label class="form-label fw-semibold small text-uppercase text-body-secondary">Link para subir documentos</label>
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="text" class="form-control form-control-sm" id="inputUrlDocumentos" readonly placeholder="Se generará al enviar o al reenviar">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnCopiarUrlDocumentos" title="Copiar URL">
                            <i class="bx bx-copy me-1"></i> Copiar URL
                        </button>
                    </div>
                    <small class="text-muted">Comparte este enlace con el candidato para que suba sus documentos.</small>
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

<style>
/* Offcanvas candidato siempre visible por encima del layout */
#offcanvasAddCandidato.offcanvas { z-index: 1060 !important; }
.offcanvas-backdrop { z-index: 1055 !important; }
/* Botón Guardar/Actualizar con color visible en modo claro y oscuro */
#offcanvasAddCandidato #btnSubmitCandidato.btn-primary { background-color: #696cff !important; border-color: #696cff !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-primary:hover { background-color: #5f61e6 !important; border-color: #5f61e6 !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-primary { background-color: #6366f1 !important; border-color: #6366f1 !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-primary:hover { background-color: #818cf8 !important; border-color: #818cf8 !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-success { background-color: #71dd37 !important; border-color: #71dd37 !important; color: #fff !important; }
#offcanvasAddCandidato #btnSubmitCandidato.btn-success:hover { background-color: #85e34b !important; border-color: #85e34b !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-success { background-color: #22c55e !important; border-color: #22c55e !important; color: #fff !important; }
body.dark-mode #offcanvasAddCandidato #btnSubmitCandidato.btn-success:hover { background-color: #4ade80 !important; border-color: #4ade80 !important; color: #fff !important; }
/* Cancelar: mantener estilo secundario visible en modo oscuro */
body.dark-mode #offcanvasAddCandidato .btn-outline-secondary { border-color: rgba(148, 163, 184, 0.5) !important; color: #94a3b8 !important; }
body.dark-mode #offcanvasAddCandidato .btn-outline-secondary:hover { background-color: rgba(71, 85, 105, 0.5) !important; border-color: #64748b !important; color: #e2e8f0 !important; }
/* Botones del formulario candidato */
#offcanvasAddCandidato #btnSubmitCandidato { min-width: 120px; font-weight: 600; }
#offcanvasAddCandidato .btn-outline-secondary:hover { background-color: #6c757d; color: #fff; }
.btn-action-size { height: 36px; padding: 0.375rem 0.75rem; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.375rem; }
/* KPIs mismo tamaño que Gestión y Bajas */
#panelIndicadoresCandidatos .kpi-wrapper { display: flex; justify-content: center; align-items: stretch; flex-wrap: wrap; gap: 0; }
#panelIndicadoresCandidatos .kpi-item { flex: 0 1 auto; min-width: 120px; max-width: 150px; }
#panelIndicadoresCandidatos .kpi-card { border-radius: 0.5rem; border: none; position: relative; overflow: hidden; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06); }
#panelIndicadoresCandidatos .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(90deg, var(--kpi-color-start), var(--kpi-color-end)); }
#panelIndicadoresCandidatos .kpi-card .card-body { padding: 0.75rem 0.7rem; text-align: center; position: relative; }
#panelIndicadoresCandidatos .kpi-number { font-size: 1.3rem; font-weight: 700; line-height: 1; margin-bottom: 0.3rem; background: linear-gradient(135deg, var(--kpi-color-start), var(--kpi-color-end)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
#panelIndicadoresCandidatos .kpi-label { font-size: 0.55rem; color: #64748B; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; }
#panelIndicadoresCandidatos .kpi-icon { font-size: 1.2rem; opacity: 0.15; position: absolute; right: 0.5rem; top: 0.5rem; color: var(--kpi-color-start); }
#panelIndicadoresCandidatos .kpi-candidatos-total { --kpi-color-start: #4F46E5; --kpi-color-end: #6366F1; }
#panelIndicadoresCandidatos .kpi-candidatos-evaluar { --kpi-color-start: #10B981; --kpi-color-end: #34D399; }
#panelIndicadoresCandidatos .kpi-candidatos-enviadas { --kpi-color-start: #F59E0B; --kpi-color-end: #FBBF24; }
#panelIndicadoresCandidatos .kpi-separator { display: flex; align-items: center; justify-content: center; padding: 0 0.3rem; }
#panelIndicadoresCandidatos .kpi-separator .line { width: 1px; height: 35px; background: linear-gradient(180deg, transparent, rgba(148, 163, 184, 0.4), transparent); }
@media (max-width: 991px) {
    #panelIndicadoresCandidatos .kpi-item { min-width: 110px; max-width: 140px; }
    #panelIndicadoresCandidatos .kpi-number { font-size: 1.15rem; }
    #panelIndicadoresCandidatos .kpi-label { font-size: 0.52rem; }
    #panelIndicadoresCandidatos .kpi-separator .line { height: 30px; }
}
@media (max-width: 767px) {
    #panelIndicadoresCandidatos .kpi-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; justify-items: stretch; }
    #panelIndicadoresCandidatos .kpi-item { min-width: unset; max-width: unset; width: 100%; }
    #panelIndicadoresCandidatos .kpi-separator { display: none !important; }
    #panelIndicadoresCandidatos .kpi-number { font-size: 1.05rem; }
    #panelIndicadoresCandidatos .kpi-label { font-size: 0.5rem; white-space: normal; }
    #panelIndicadoresCandidatos .kpi-card .card-body { padding: 0.6rem; }
}
@media (max-width: 480px) {
    #panelIndicadoresCandidatos .kpi-wrapper { grid-template-columns: 1fr; }
}
/* Modo oscuro - KPIs Candidatos */
body.dark-mode #panelIndicadoresCandidatos .kpi-card { background: rgba(30, 41, 59, 0.6); box-shadow: 0 4px 6px rgba(0,0,0,0.2); }
body.dark-mode #panelIndicadoresCandidatos .kpi-label { color: #94a3b8; }
body.dark-mode #panelIndicadoresCandidatos .kpi-separator .line { background: rgba(148, 163, 184, 0.3); }
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
/* Calendario Flatpickr - mismo que Gestión */
.fecha-postulacion-wrapper.fecha-acta-wrapper { position: relative; z-index: 1; }
#candidato_fecha_postulacion { width: 100%; cursor: pointer; }
.flatpickr-calendar .flatpickr-monthDropdown-months { appearance: none !important; background-image: none !important; -webkit-appearance: none; -moz-appearance: none; }
.flatpickr-calendar { z-index: 99999 !important; position: fixed !important; transform: scale(1.12); transform-origin: top left; }
.flatpickr-calendar.open { display: block !important; visibility: visible !important; opacity: 1 !important; }
.flatpickr-calendar .flatpickr-day.today { border-color: #696cff !important; border-width: 2px !important; font-weight: 600 !important; background-color: #f0f0ff !important; }
.flatpickr-calendar .flatpickr-day.today:hover { background-color: #e0e0ff !important; border-color: #696cff !important; }
#tablaCandidatos thead th { background-color: rgba(105, 108, 255, 0.1); font-weight: 600; }
#tablaCandidatos th.col-acciones-candidatos { min-width: 220px; }
#tablaCandidatos .btn-accion-candidato { white-space: nowrap; }
</style>

<script>
(function() {
    function initCandidatos() {
        if (!document.getElementById("tablaCandidatos")) return;
        var $ = window.jQuery || window.$;
        if ($ && $.fn.DataTable && !$.fn.DataTable.isDataTable("#tablaCandidatos")) {
            $("#tablaCandidatos").DataTable({
                responsive: false,
                order: [[0, "asc"]],
                columnDefs: [ { orderable: false, targets: 4 } ],
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros)",
                    paginate: { first: "Primera", last: "Última", next: "Siguiente", previous: "Anterior" },
                    zeroRecords: "No hay registros"
                }
            });
        }
        getCandidatos();

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
            selJefe.innerHTML = "<option value=''>Cargando...</option>";
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
        selJefe.innerHTML = "<option value=''>Cargando...</option>";
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

    var filterEstatus = document.getElementById("filterEstatus");
    if (filterEstatus) filterEstatus.addEventListener("change", function() { getCandidatos(); });

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

function getCandidatos() {
    var estatus = (document.getElementById("filterEstatus") && document.getElementById("filterEstatus").value) || "";
    var params = estatus ? "?estatus=" + encodeURIComponent(estatus) : "";
    fetch("/CapHum/getCandidatos" + params).then(function(r){ return r.json(); }).then(function(res){
        if (!res.success || !res.datos) return;
        var $ = window.jQuery || window.$;
        if (!$ || !$.fn.DataTable) return;
        var tabla = $("#tablaCandidatos").DataTable();
        if (!tabla) return;
        tabla.clear();
        res.datos.forEach(function(c){
            var nombre = [c.nombres, c.segundo_nombre, c.apellidop, c.apellidom].filter(Boolean).join(" ");
            var contacto = (c.email || "") + (c.telefono ? " | " + c.telefono : "");
            var puestoDepto = (c.nombre_puesto || "-") + " / " + (c.nombre_departamento || "-");
            var id = (c.id || 0).toString();
            var acciones = "<div class=\"d-flex flex-wrap gap-1 align-items-center\">" +
                "<button type=\"button\" class=\"btn btn-sm btn-primary btn-editar-candidato\" data-id=\"" + id + "\" title=\"Editar\"><i class=\"fa fa-edit\"></i></button>" +
                "<button type=\"button\" class=\"btn btn-sm btn-info text-white btn-reenviar-candidato\" data-id=\"" + id + "\" title=\"Reenviar correo\"><i class=\"fa fa-envelope\"></i></button>" +
                "<button type=\"button\" class=\"btn btn-sm btn-danger btn-eliminar-candidato\" data-id=\"" + id + "\" title=\"Eliminar\"><i class=\"fa fa-trash\"></i></button>" +
                "</div>";
            tabla.row.add([nombre, contacto, puestoDepto, c.estatus || "Por evaluar", acciones]);
        });
        tabla.draw();
        actualizarKPIsCandidatos(res.datos);
    });
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
    btn = target.closest(".btn-eliminar-candidato");
    if (btn) {
        e.preventDefault();
        e.stopPropagation();
        var id = btn.getAttribute("data-id");
        if (id) window.eliminarCandidato(parseInt(id, 10));
    }
}

function actualizarKPIsCandidatos(datos) {
    var total = (datos && datos.length) || 0;
    var porEvaluar = (datos && datos.filter(function(c){ return (c.estatus || "") === "Por evaluar"; }).length) || 0;
    var enviadas = (datos && datos.filter(function(c){ return c.postulacion_enviada == 1; }).length) || 0;
    document.getElementById("kpi-total-candidatos").textContent = total;
    document.getElementById("kpi-por-evaluar").textContent = porEvaluar;
    document.getElementById("kpi-postulaciones-enviadas").textContent = enviadas;
}

function abrirModalReenviarPostulacion(idCandidato) {
    window._candidatoDatosEnvio = null;
    fetch("/CapHum/getCandidato/" + idCandidato).then(function(r){ return r.json(); }).then(function(res){
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
    fetch("/CapHum/getTokenDocumentosCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: idCandidato }) })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success && res.datos && res.datos.url) {
                input.value = res.datos.url;
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
    btn.addEventListener("click", function() {
        var url = input.value;
        if (!url) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(function() {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Copiado", text: "URL copiada al portapapeles.", timer: 1500, showConfirmButton: false });
                else alert("URL copiada.");
            }).catch(function() { fallbackCopiar(url); });
        } else {
            fallbackCopiar(url);
        }
    });
    function fallbackCopiar(texto) {
        input.select();
        input.setSelectionRange(0, 99999);
        try {
            document.execCommand("copy");
            if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Copiado", text: "URL copiada al portapapeles.", timer: 1500, showConfirmButton: false });
            else alert("URL copiada.");
        } catch (e) {}
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
    fetch("/CapHum/getCandidato/" + id).then(function(r){ return r.json(); }).then(function(res){
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
        selJefe.innerHTML = "<option value=''>Cargando...</option>";

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

    window._candidatoDatosEnvio = data;
    window._candidatoReenviarId = null;
    window._candidatoReenviarEmail = null;

    var offcanvas = document.getElementById("offcanvasAddCandidato");
    if (offcanvas && typeof bootstrap !== "undefined") bootstrap.Offcanvas.getInstance(offcanvas).hide();
    var modal = new bootstrap.Modal(document.getElementById("modalResumenPostulacion"));
    modal.show();
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

    if (window._candidatoReenviarId && window._candidatoReenviarEmail) {
        fetch("/CapHum/enviarPostulacionCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: window._candidatoReenviarId, email: window._candidatoReenviarEmail }) })
        .then(function(r){ return r.json(); })
        .then(function(res){
            window._candidatoReenviarId = null;
            window._candidatoReenviarEmail = null;
            btn.disabled = false;
            btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";
            if (res.success) {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Correo de postulación reenviado correctamente." });
                getCandidatos();
                setTimeout(function() { bootstrap.Modal.getInstance(document.getElementById("modalResumenPostulacion")).hide(); }, 1500);
            } else {
                if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: res.mensaje || "No se pudo enviar el correo." });
            }
        })
        .catch(function(){
            window._candidatoReenviarId = null;
            window._candidatoReenviarEmail = null;
            btn.disabled = false;
            btn.innerHTML = "<i class='bx bx-send me-2'></i> Enviar postulación al candidato";
            if (typeof Swal !== "undefined") Swal.fire({ icon: "error", title: "Error", text: "Error de conexión." });
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
    fetch("/CapHum/guardarCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify(data) })
    .then(function(r){ return r.json(); })
    .then(function(res){
        if (res.success) {
            window._candidatoDatosEnvio = null;
            fetch("/CapHum/enviarPostulacionCandidato", { method: "POST", headers: { "Content-Type": "application/json", "Accept": "application/json" }, body: JSON.stringify({ id: res.datos && res.datos.id, email: data.email }) })
            .then(function(r2){ return r2.json(); })
            .then(function() {});
            btn.innerHTML = "<i class=\"bx bx-check me-2\"></i> Enviada postulación";
            if (typeof Swal !== "undefined") Swal.fire({ icon: "success", title: "Listo", text: "Candidato registrado y postulación enviada por correo." });
            cargarLinkDocumentosCandidato(res.datos && res.datos.id);
            if (form) form.reset();
            var fpInput = document.getElementById("candidato_fecha_postulacion");
            if (fpInput && fpInput._flatpickr) fpInput._flatpickr.setDate(new Date(), true);
            getCandidatos();
            setTimeout(function() { bootstrap.Modal.getInstance(document.getElementById("modalResumenPostulacion")).hide(); }, 1500);
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
