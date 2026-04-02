<?php
$urlAgenteIni = isset($gastosCobranzaAgenteUrl) ? trim((string) $gastosCobranzaAgenteUrl) : '';
$puertoAgenteMostrar = '3120';
if ($urlAgenteIni !== '') {
    $pu = parse_url($urlAgenteIni, PHP_URL_PORT);
    if ($pu !== null && $pu !== false && (int) $pu > 0) {
        $puertoAgenteMostrar = (string) (int) $pu;
    }
}
?>
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 gc-shell-hero-card">
                <div class="card-body py-4">
                    <div class="row align-items-center g-4">
                        <div class="col-lg-8">
                            <h4 class="mb-2 fw-semibold text-heading gc-shell-hero-title">
                                <i class="fa fa-file-invoice-dollar text-primary me-2"></i>
                                <?= htmlspecialchars(isset($tituloShell) ? $tituloShell : 'Shell Gastos Cobranza', ENT_QUOTES, 'UTF-8') ?>
                            </h4>
                            <p class="gc-shell-hero-lead text-muted mb-3 mb-lg-2">
                                Punto de trabajo para el servicio local de cobranza: generación de reportes, integración con S2 y tareas de verificación.
                                El registro de actividad del agente aparece en el panel inferior de esta página.
                            </p>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="gc-shell-chip"><i class="fa fa-code-branch me-1 opacity-75"></i><code class="small">reporte_cobranza.py</code></span>
                                <span class="gc-shell-chip gc-shell-chip-muted"><i class="fa fa-flask me-1 opacity-75"></i>Respaldo en modo prueba si no hay script</span>
                                <span class="gc-shell-chip gc-shell-chip-accent"><i class="fa fa-ethernet me-1 opacity-75"></i>Puerto <?= htmlspecialchars($puertoAgenteMostrar, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                        <div class="col-lg-4 d-flex justify-content-lg-end">
                            <div class="gc-shell-module-card">
                                <div class="gc-shell-module-icon" aria-hidden="true">
                                    <i class="fa fa-shield-alt"></i>
                                </div>
                                <div class="gc-shell-module-text">
                                    <span class="gc-shell-module-label">Control de acceso</span>
                                    <span class="gc-shell-module-name">Módulo 31</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row flex-md-wrap align-items-md-start justify-content-md-between gap-3 mb-3">
                        <div class="flex-grow-1 min-w-0">
                            <h5 class="card-title mb-1"><i class="fa fa-plug text-primary me-2"></i>Estado del agente</h5>
                            <p class="small text-muted mb-0">Comprueba si el agente local responde y, cuando esté en línea, puedes lanzar la ejecución desde aquí.</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 flex-shrink-0">
                            <span id="gastosCobranzaEstadoBadge" class="badge bg-label-secondary">Comprobando…</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnGastosCobranzaRefrescar">
                                <i class="fa fa-sync-alt me-1"></i>Actualizar
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" id="btnGastosCobranzaEjecutar" disabled>
                                <i class="fa fa-play me-1"></i>Ejecutar (agente)
                            </button>
                        </div>
                    </div>
                    <p class="small mb-0" id="gastosCobranzaDetalle">—</p>
                    <div id="gastosCobranzaSalidaWrap" class="d-none mt-3">
                        <label class="form-label small fw-semibold">Salida de la última ejecución</label>
                        <pre id="gastosCobranzaSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:220px;overflow:auto;white-space:pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-8">
            <div class="card shadow-sm border-0 h-100 gc-card-accent-ec-worker">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-rocket gc-ec-worker-title-icon me-2" aria-hidden="true"></i>EC Worker / Excel enriquecido</h5>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Excel (.xlsx)</label>
                            <input type="file" class="form-control form-control-sm" id="ecLauncherFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Fecha corte S2</label>
                            <input type="date" class="form-control form-control-sm" id="ecLauncherFecha">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Columna ID en Excel</label>
                            <input type="text" class="form-control form-control-sm" id="ecLauncherCol" value="ID CREDITO" placeholder="ID CREDITO">
                        </div>
                        <div class="col-12 col-md-auto flex-grow-0">
                            <label class="form-label small mb-1 text-nowrap" title="Saltar primeros créditos (N)">Omitir N</label>
                            <input type="number" class="form-control form-control-sm" id="ecLauncherOmitir" value="0" min="0" title="Saltar primeros créditos: ignorar los primeros N IDs del Excel" style="max-width: 4.25rem">
                        </div>
                        <div class="col-12">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="ecLauncherEnrich" autocomplete="off">
                                <label class="form-check-label small" for="ecLauncherEnrich">Excel enriquecido (+ Chat)</label>
                            </div>
                            <p class="small text-muted mb-0 mt-1">Sin marcar: <strong>Worker</strong> (S2 + BD + Chat; al terminar la corrida, lista negra automática). Marcada: <strong>enriquecido completo</strong> (mismo criterio de BD y auditoría que el flujo enrich del servidor).</p>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary btn-sm" id="btnEcLauncherEjecutar" disabled>
                                <i class="fa fa-cloud-upload-alt me-1"></i>Subir Excel y ejecutar vía agente
                            </button>
                            <span class="small text-muted ms-2">Puede tardar mucho. Con el Worker, al terminar la corrida la lista negra se actualiza sola con el mismo Excel.</span>
                        </div>
                    </div>
                    <div id="ecErroresReintentoBanner" class="alert alert-warning d-none mt-3 mb-0 py-2 small" role="alert">
                        Tras la segunda pasada automática aún hubo créditos con error.
                        <a id="ecErroresReintentoLink" class="alert-link fw-semibold" href="#">Descargar CSV (id, tipo de error y detalle)</a>
                        para revisión manual.
                    </div>
                    <div id="ecLauncherSalidaWrap" class="d-none mt-3">
                        <label class="form-label small fw-semibold">Salida EC worker / enrich</label>
                        <pre id="ecLauncherSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:260px;overflow:auto;white-space:pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 h-100 gc-card-accent-descargo">
                <div class="card-body">
                    <h5 class="card-title"><i class="fa fa-file-export text-info me-2"></i>Descargo cobranza GC (estatus 3)</h5>
                    <div class="small text-muted mb-3">
                        <div class="mb-2">
                            Lee <code>cobranza_gc_verificacion_semana</code> con <code>estatus = 3</code>.
                            <strong>Descargar</strong> ejecuta el script y, si hay filas nuevas, baja el Excel directo.
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="chkDescargoSinActualizarGuia" autocomplete="off">
                        <label class="form-check-label small" for="chkDescargoSinActualizarGuia">Solo prueba: generar Excel pero <strong>no</strong> escribir <code>guia_descargo.json</code> (déjalo desmarcado para corrida real)</label>
                    </div>
                    <div class="d-grid d-sm-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-primary" id="btnDescargoEstatus3" disabled>
                            <i class="fa fa-download me-1"></i>Descargar
                        </button>
                        <span id="descargoEstatus3Spinner" class="text-primary d-none" role="status" aria-live="polite" aria-label="Descargando">
                            <i class="fa fa-spinner fa-spin fa-lg" aria-hidden="true"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 border-secondary">
                <div class="card-body py-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="chkMostrarCargaVerifManual" autocomplete="off">
                        <label class="form-check-label small" for="chkMostrarCargaVerifManual">
                            <strong>Carga manual a lista negra</strong> (opcional). El <strong>Worker</strong> ya aplica la lista negra al terminar el lote; abre esto solo para ajustar parámetros o para subir un Excel <em>sin</em> pasar por el Worker.
                        </label>
                    </div>
                </div>
                <div id="wrapCargaVerifManual" class="d-none border-top">
                    <div class="card-body pt-3">
                        <h6 class="mb-2"><i class="fa fa-database text-secondary me-2"></i>Carga verificación semana (Excel → BD)</h6>
                        <p class="small text-muted mb-3">
                            Tabla <code>cobranza_gc_verificacion_semana</code>. <strong>SALDO APLICABLE A GC</strong> → <code>monto_aplicar</code>.
                            <strong>Inicio de semana</strong> se calcula solo en el agente: martes que abre la semana operativa según <em>hoy</em> (hora Ciudad de México), igual que en la pantalla de estado de cuenta.
                            Si el mismo <code>id_credito</code> ya tiene fila en esa semana con <strong>estatus 3</strong>, no se duplica: pasa a <strong>estatus 2</strong> y se actualiza <code>celula</code> si viene COMENTARIOS en el Excel. Los <strong>nuevos</strong> se insertan con el estatus que elijas abajo (por defecto 2). En esta carga, <code>tipo_reporte</code> debe quedar <strong>NULL</strong> en MySQL.
                            Si el Excel tiene <strong>título o filas vacías</strong> encima de los encabezados, deje en auto el campo «Fila de encabezados» o indique la fila donde está <code>id_credito</code> (1 = primera fila del libro).
                            Misma lógica cuando el Worker dispara la carga automática (detección automática de fila de títulos).
                        </p>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small mb-1">Excel (.xlsx)</label>
                                <input type="file" class="form-control form-control-sm" id="cargaVerifFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Estatus (filas nuevas)</label>
                                <select class="form-select form-select-sm" id="cargaVerifEstatus" title="Solo aplica a INSERT; las que ya estaban en 3 pasan a 2">
                                    <option value="2" selected>2</option>
                                    <option value="1">1</option>
                                    <option value="0">0</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small mb-1">Fila encabezados (Excel)</label>
                                <input type="number" class="form-control form-control-sm" id="cargaVerifHeaderRow" min="1" max="200" placeholder="Auto" title="Número de fila donde están id_credito, etc. (1 = primera). Vacío = el script la detecta solo.">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1">Mensaje lote (opcional)</label>
                                <input type="text" class="form-control form-control-sm" id="cargaVerifMensaje" placeholder="Vacío = mensaje automático en el script" maxlength="500">
                            </div>
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="cargaVerifDryRun">
                                    <label class="form-check-label small" for="cargaVerifDryRun">Solo simular (dry-run, no inserta en BD)</label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="button" class="btn btn-secondary btn-sm" id="btnCargaVerifEjecutar" disabled>
                                    <i class="fa fa-upload me-1"></i>Subir Excel y cargar vía agente
                                </button>
                                <span class="small text-muted ms-2">Python: <code>pandas</code>, <code>mysql-connector-python</code> en el agente.</span>
                            </div>
                        </div>
                        <div id="cargaVerifSalidaWrap" class="d-none mt-3">
                            <label class="form-label small fw-semibold">Salida carga verificación</label>
                            <pre id="cargaVerifSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:260px;overflow:auto;white-space:pre-wrap;"></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="card-title mb-0"><i class="fa fa-table text-success me-2"></i>Reportes en carpeta <code>reporte/</code></h5>
                        <button type="button" class="btn btn-sm btn-outline-success" id="btnGastosCobranzaListarReportes">
                            <i class="fa fa-sync-alt me-1"></i>Actualizar lista
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Archivo</th>
                                    <th class="text-end">Tamaño</th>
                                    <th>Modificado (UTC)</th>
                                    <th title="Día de la semana según la hora de modificación del archivo (calendario Ciudad de México)">Día</th>
                                    <th title="Pasa el cursor sobre la celda para ver el texto completo">Estado</th>
                                    <th class="text-center" width="200">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="gastosCobranzaTablaReportes">
                                <tr><td colspan="7" class="text-muted small">Cargando…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h5 class="card-title mb-0"><i class="fa fa-scroll text-secondary me-2"></i>Log del agente (casi en tiempo real)</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="chkGastosCobranzaLogAuto" checked>
                            <label class="form-check-label small" for="chkGastosCobranzaLogAuto">Auto cada 4 s</label>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnGastosCobranzaLogAhora"
                            title="Pide al agente las últimas líneas del archivo de log en disco y las muestra aquí; baja el scroll al final para ver lo más reciente.">
                            <i class="fa fa-download me-1"></i>Traer log ahora
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" id="btnGastosCobranzaLogVaciar" title="Borra el historial del archivo de log en el agente (solo la bitácora en disco)">
                            <i class="fa fa-eraser me-1"></i>Vaciar log
                        </button>
                        <span class="small text-muted mb-0 d-none d-lg-inline">Últimas ~400 líneas del archivo de log en el agente. Al cargar o al pulsar, el panel baja al <strong>final</strong>. Con «Auto cada 4 s» solo baja el scroll si ya estabas abajo (para no interrumpir si lees más arriba).</span>
                    </div>
                    <pre id="gastosCobranzaLogPanel" class="bg-dark text-light border-0 rounded p-3 small mb-0 font-monospace" style="max-height:320px;overflow:auto;white-space:pre-wrap;">—</pre>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
    .container-p-y .row.mb-4:last-of-type {
        margin-bottom: 1rem !important;
    }
    /*
     * Franja lateral: Bootstrap .border-0 pone border:0 !important en toda la tarjeta y deja invisible un solo border-left.
     * Forzamos borde izquierdo con selector más específico y anulamos los otros lados.
     */
    .card.gc-card-accent-ec-worker.border-0 {
        border-top: 0 !important;
        border-right: 0 !important;
        border-bottom: 0 !important;
        border-left: 4px solid #7b61ff !important;
    }
    .gc-ec-worker-title-icon {
        color: #7b61ff;
    }

    /* Shell hero: encabezado y módulo */
    .gc-shell-hero-card {
        background: linear-gradient(135deg, #fcfdff 0%, #f6f8fc 100%);
        border: 1px solid rgba(67, 89, 113, 0.08) !important;
    }
    .gc-shell-hero-title {
        letter-spacing: -0.02em;
    }
    .gc-shell-hero-lead {
        font-size: 0.9375rem;
        line-height: 1.55;
        max-width: 42rem;
    }
    .gc-shell-chip {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 500;
        padding: 0.35rem 0.65rem;
        border-radius: 999px;
        background: rgba(105, 108, 255, 0.1);
        color: #566a7f;
        border: 1px solid rgba(105, 108, 255, 0.15);
    }
    .gc-shell-chip code {
        background: transparent;
        color: #4a5a6b;
        padding: 0;
    }
    .gc-shell-chip-muted {
        background: rgba(67, 89, 113, 0.06);
        border-color: rgba(67, 89, 113, 0.1);
    }
    .gc-shell-chip-accent {
        background: rgba(3, 195, 236, 0.08);
        border-color: rgba(3, 195, 236, 0.2);
        color: #2d6a7a;
    }
    .gc-shell-module-card {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        max-width: 16rem;
        padding: 0.85rem 1rem;
        border-radius: 0.65rem;
        background: #fff;
        border: 1px solid rgba(67, 89, 113, 0.1);
        box-shadow: 0 2px 8px rgba(67, 89, 113, 0.06);
    }
    .gc-shell-module-icon {
        flex-shrink: 0;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(145deg, rgba(105, 108, 255, 0.18), rgba(105, 108, 255, 0.06));
        color: #696cff;
        font-size: 1.1rem;
    }
    .gc-shell-module-text {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        min-width: 0;
    }
    .gc-shell-module-label {
        font-size: 0.65rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #a1acb8;
    }
    .gc-shell-module-name {
        font-size: 0.95rem;
        font-weight: 600;
        color: #566a7f;
        line-height: 1.2;
    }

    .card.gc-card-accent-descargo.border-0 {
        border-top: 0 !important;
        border-right: 0 !important;
        border-bottom: 0 !important;
        border-left: 4px solid #3abaf4 !important;
    }
    /* Estados en tabla reporte/: tonos suaves, legibles en Sneat */
    .gc-rep-estado {
        display: inline-block;
        font-size: 0.7rem;
        font-weight: 500;
        padding: 0.2em 0.55em;
        border-radius: 0.35rem;
        line-height: 1.35;
        border: 1px solid transparent;
    }
    .gc-rep-est-generado { background: #e8f4ec; color: #2d4a38; border-color: rgba(45, 74, 56, 0.14); }
    .gc-rep-est-proc { background: #e9eef9; color: #314e7a; border-color: rgba(49, 78, 122, 0.12); }
    .gc-rep-est-aplic { background: #f0ebf7; color: #4a3a68; border-color: rgba(74, 58, 104, 0.12); }
    .gc-rep-est-worker { background: #faf3e8; color: #6b4e2e; border-color: rgba(107, 78, 46, 0.14); }
    .gc-rep-est-worker-listo { background: #e8f2fc; color: #2d5a87; border-color: rgba(45, 90, 135, 0.14); }
    .gc-rep-est-negra { background: #eef1f4; color: #3d4551; border-color: rgba(61, 69, 81, 0.12); }
    .gc-rep-est-vacio { background: transparent; color: #a0a8b0; border: none; font-weight: 400; }
    .gc-rep-est-otro { background: #f3f4f6; color: #4b5563; border-color: rgba(75, 85, 99, 0.12); }

    /* Acciones reporte_cobranza: neo-ligero (tonos suaves, pill, sombra mínima) */
    .gc-rep-acciones-cell .btn,
    .gc-rep-acciones-cell a.btn {
        border-radius: 999px;
        padding: 0.32rem 0.62rem;
        line-height: 1;
        border-width: 1px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
    }
    .gc-rep-acciones-cell a.btn.gc-rep-btn-descargar {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #64748b;
    }
    .gc-rep-acciones-cell a.btn.gc-rep-btn-descargar:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #475569;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    }
    .gc-rep-acciones-cell button.gc-rep-btn-worker {
        background: #fffbf5;
        border-color: #f0e6dc;
        color: #9a8478;
    }
    .gc-rep-acciones-cell button.gc-rep-btn-worker:hover {
        background: #fff5eb;
        border-color: #e8d5c4;
        color: #7d6c62;
        box-shadow: 0 1px 3px rgba(120, 90, 70, 0.06);
    }
    .gc-rep-acciones-cell button.gc-rep-btn-lista-negra {
        background: #faf8ff;
        border-color: #e8e4f3;
        color: #8b7eb8;
    }
    .gc-rep-acciones-cell button.gc-rep-btn-lista-negra:hover {
        background: #f3f0fa;
        border-color: #dcd6ec;
        color: #6f6499;
        box-shadow: 0 1px 3px rgba(90, 80, 130, 0.06);
    }
</style>

<script>
(function () {
    var badge = document.getElementById('gastosCobranzaEstadoBadge');
    var detalle = document.getElementById('gastosCobranzaDetalle');
    var btnRef = document.getElementById('btnGastosCobranzaRefrescar');
    var btnRun = document.getElementById('btnGastosCobranzaEjecutar');
    var btnEcLauncher = document.getElementById('btnEcLauncherEjecutar');
    var btnCargaVerif = document.getElementById('btnCargaVerifEjecutar');
    var btnDescargoEstatus3 = document.getElementById('btnDescargoEstatus3');
    var chkDescargoSinActualizarGuia = document.getElementById('chkDescargoSinActualizarGuia');
    var descargoSpinner = document.getElementById('descargoEstatus3Spinner');
    var cargaVerifFile = document.getElementById('cargaVerifFile');
    var cargaVerifEstatus = document.getElementById('cargaVerifEstatus');
    var cargaVerifMensaje = document.getElementById('cargaVerifMensaje');
    var cargaVerifDry = document.getElementById('cargaVerifDryRun');
    var cargaVerifOutWrap = document.getElementById('cargaVerifSalidaWrap');
    var cargaVerifOutPre = document.getElementById('cargaVerifSalida');
    var chkMostrarCargaVerifManual = document.getElementById('chkMostrarCargaVerifManual');
    var wrapCargaVerifManual = document.getElementById('wrapCargaVerifManual');
    var ecFile = document.getElementById('ecLauncherFile');
    var ecFecha = document.getElementById('ecLauncherFecha');
    var ecEnrich = document.getElementById('ecLauncherEnrich');
    var ecCol = document.getElementById('ecLauncherCol');
    var ecOmitir = document.getElementById('ecLauncherOmitir');
    var ecOutWrap = document.getElementById('ecLauncherSalidaWrap');
    var ecOutPre = document.getElementById('ecLauncherSalida');
    var ecErroresReintentoBanner = document.getElementById('ecErroresReintentoBanner');
    var ecErroresReintentoLink = document.getElementById('ecErroresReintentoLink');
    var outWrap = document.getElementById('gastosCobranzaSalidaWrap');
    var outPre = document.getElementById('gastosCobranzaSalida');
    var logPanel = document.getElementById('gastosCobranzaLogPanel');
    var chkLog = document.getElementById('chkGastosCobranzaLogAuto');
    var btnLog = document.getElementById('btnGastosCobranzaLogAhora');
    var btnLogVaciar = document.getElementById('btnGastosCobranzaLogVaciar');
    var btnListarRep = document.getElementById('btnGastosCobranzaListarReportes');
    var tbodyRep = document.getElementById('gastosCobranzaTablaReportes');
    var ivEstado = null;
    var ivLog = null;
    var ivRep = null;
    /** Log más frecuente mientras corre EC launcher (worker/enrich). */
    var ivLogEcWorker = null;
    /** Agente alcanzable y con EC listo (misma condición que antes para habilitar subida EC). */
    var gcAgenteOnline = false;
    /** Worker o enrich vía ec-launcher en curso (esta pestaña): desactiva subida EC y engranajes en tabla. */
    var gcEcJobEnCurso = false;
    /** El agente reporta proceso EC en curso (otra pestaña u otro usuario): mismo bloqueo en UI. */
    var gcAgenteReportaEcOcupado = false;
    /** Evita reejecutar el mismo día (CDMX) tras un reporte real exitoso. */
    var LS_REPORTE_OK_YMD = 'gastosCobranza_reporteRealOkYmd';

    function aplicarEstadoBotonesEcWorker() {
        var puedeLanzarEc = gcAgenteOnline && !gcEcJobEnCurso && !gcAgenteReportaEcOcupado;
        if (btnEcLauncher) {
            btnEcLauncher.disabled = !puedeLanzarEc;
        }
        try {
            document.querySelectorAll('.btn-gc-worker-reporte').forEach(function (b) {
                b.disabled = !puedeLanzarEc;
            });
        } catch (e) { /* ignorar */ }
    }

    async function conBloqueoWorkerEc(asyncFn) {
        gcEcJobEnCurso = true;
        aplicarEstadoBotonesEcWorker();
        try {
            return await asyncFn();
        } finally {
            gcEcJobEnCurso = false;
            aplicarEstadoBotonesEcWorker();
        }
    }

    /** El servidor ya manda las últimas N líneas; el panel debe bajar el scroll para mostrar lo más reciente. */
    function logPanelEstaCercaDelFinal() {
        if (!logPanel) return true;
        var umb = 120;
        try {
            return logPanel.scrollHeight - logPanel.scrollTop - logPanel.clientHeight <= umb;
        } catch (e) {
            return true;
        }
    }

    function scrollLogPanelAlFinal() {
        if (!logPanel) return;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                try {
                    logPanel.scrollTop = logPanel.scrollHeight;
                } catch (e2) { /* ignorar */ }
            });
        });
    }

    function fechaCalendarioCdmxYmd() {
        return new Intl.DateTimeFormat('en-CA', {
            timeZone: 'America/Mexico_City',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).format(new Date());
    }

    /** Nombre de archivo que genera reporte_cobranza.py hoy (CDMX). */
    function nombreArchivoReporteCobranzaHoyCdmx() {
        var ymd = fechaCalendarioCdmxYmd().split('-');
        return 'reporte_cobranza_' + ymd[2] + '-' + ymd[1] + '-' + ymd[0] + '.xlsx';
    }

    async function hayReporteCobranzaHoyEnServidor() {
        try {
            var r = await fetch('/gastoscobranza/listarReportes', {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (!data.success || !Array.isArray(data.archivos)) {
                return false;
            }
            var esperado = nombreArchivoReporteCobranzaHoyCdmx().toLowerCase();
            return data.archivos.some(function (a) {
                return String(a.nombre || '').toLowerCase() === esperado;
            });
        } catch (e) {
            return false;
        }
    }

    /**
     * Aviso si el reporte del día ya existe (misma máquina u otra sesión).
     * @returns {Promise<string>} 'forzar' si el usuario elige generar igual; 'cancelar' si no debe ejecutar.
     */
    function dialogoReporteYaGeneradoHoy() {
        var html = '<p class="mb-2">Este reporte <strong>ya se generó hoy</strong> (calendario Ciudad de México). '
            + 'No hace falta volver a ejecutarlo, salvo que tu área pida otra corrida.</p>'
            + '<p class="mb-0">Puedes <strong>descargar el Excel</strong> en la tabla '
            + '<em>«Reportes en carpeta reporte/»</em> más abajo.</p>';
        if (typeof Swal !== 'undefined') {
            return Swal.fire({
                icon: 'info',
                title: 'Reporte ya generado hoy',
                html: '<div class="text-start small">' + html + '</div>',
                confirmButtonText: 'Entendido',
                showDenyButton: true,
                denyButtonText: 'Generar de todas formas',
            }).then(function (r) {
                return r.isDenied ? 'forzar' : 'cancelar';
            });
        }
        window.alert('Este reporte ya se generó hoy (Ciudad de México). Descarga el Excel en la tabla de reportes más abajo.');
        return Promise.resolve('cancelar');
    }

    function claseEstadoReporteCarpeta(estRaw) {
        var m = {
            'Generado': 'gc-rep-est-generado',
            'Proc. cartera': 'gc-rep-est-proc',
            'Aplic. cartera': 'gc-rep-est-aplic',
            'En Worker': 'gc-rep-est-worker',
            'Worker listo': 'gc-rep-est-worker-listo',
            'Lista negra': 'gc-rep-est-negra',
        };
        return m[estRaw] || '';
    }

    /** Texto extra en alertas cuando el agente devolvió estado_reporte (columna Estado de la tabla). */
    function lineaEstadoReporteRespuesta(er) {
        if (!er || !er.corto) return '';
        var d = er.detalle ? String(er.detalle).trim() : '';
        return '\n\nEstado en la tabla de reportes: «' + er.corto + '»' + (d ? ' — ' + d : '') + '.';
    }

    function formatoBytes(n) {
        n = Number(n) || 0;
        if (n < 1024) return n + ' B';
        var u = ['KB', 'MB', 'GB'];
        var i = -1;
        do { n /= 1024; i++; } while (n >= 1024 && i < u.length - 1);
        return n.toFixed(i === 0 ? 0 : 2) + ' ' + u[i];
    }

    /**
     * Día de la semana en Ciudad de México según la fecha de modificación (ISO del agente).
     * Si el agente envía diaSemanaModificado, se usa tal cual.
     */
    function diaSemanaModificadoCdmx(iso, precalculado) {
        var t = (precalculado != null && String(precalculado).trim() !== '') ? String(precalculado).trim() : '';
        if (t) return t;
        if (!iso) return '—';
        var d = new Date(iso);
        if (isNaN(d.getTime())) return '—';
        try {
            var s = new Intl.DateTimeFormat('es-MX', {
                timeZone: 'America/Mexico_City',
                weekday: 'long'
            }).format(d);
            return s.charAt(0).toUpperCase() + s.slice(1);
        } catch (e2) {
            return '—';
        }
    }

    async function traerListaReportes() {
        try {
            var r = await fetch('/gastoscobranza/listarReportes', {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (!data.success || !data.archivos) {
                tbodyRep.innerHTML = '<tr><td colspan="7" class="text-warning small">' +
                    (data.mensaje || 'No se pudo listar reportes.') + '</td></tr>';
                aplicarEstadoBotonesEcWorker();
                return;
            }
            var list = data.archivos;
            if (!list.length) {
                tbodyRep.innerHTML = '<tr><td colspan="7" class="text-muted small">Aún no hay archivos .xlsx en <code>reporte/</code>. Ejecuta el reporte para generar uno.</td></tr>';
                aplicarEstadoBotonesEcWorker();
                return;
            }
            function escCelda(s) {
                return String(s)
                    .split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;').split('"').join('&quot;');
            }
            function esExcelReporteCobranza(nom) {
                return /^reporte_cobranza_/i.test(String(nom || ''));
            }
            tbodyRep.innerHTML = list.map(function (a) {
                var nom = String(a.nombre || '');
                var safe = escCelda(nom);
                var href = '/gastoscobranza/descargarReporte?nombre=' + encodeURIComponent(nom);
                var estRaw = (a.estado != null && String(a.estado).trim() !== '') ? String(a.estado).trim() : '';
                var estHtml = estRaw ? escCelda(estRaw) : '';
                var estTip = (a.estadoDetalle != null && String(a.estadoDetalle).trim() !== '')
                    ? escCelda(String(a.estadoDetalle).trim()) : '';
                var estTdAttr = estTip ? ' title="' + estTip + '"' : '';
                var estSubCls = claseEstadoReporteCarpeta(estRaw);
                if (estRaw && !estSubCls) estSubCls = 'gc-rep-est-otro';
                var estInner = estRaw
                    ? '<span class="gc-rep-estado ' + estSubCls + '">' + estHtml + '</span>'
                    : '<span class="gc-rep-estado gc-rep-est-vacio">—</span>';
                var diaSem = escCelda(diaSemanaModificadoCdmx(a.modificado, a.diaSemanaModificado));
                var esRep = esExcelReporteCobranza(nom);
                var btnWorker = esRep
                    ? '<button type="button" class="btn btn-sm btn-gc-worker-reporte gc-rep-btn-worker ms-1" data-nombre-enc="' +
                        encodeURIComponent(nom) + '" title="Worker S2: lanza el proceso de cobranza con este Excel sin subirlo de nuevo. Al terminar ok, la lista negra se actualiza automáticamente.">' +
                        '<i class="fa fa-cogs" aria-hidden="true"></i><span class="visually-hidden"> Worker S2</span></button>'
                    : '';
                var btnListaNegra = esRep
                    ? '<button type="button" class="btn btn-sm btn-gc-lista-negra-reporte gc-rep-btn-lista-negra ms-1" data-nombre-enc="' +
                        encodeURIComponent(nom) + '" title="Lista negra: carga este reporte directo a verificación semana (sin pasar por Worker). Usa estatus, fila encabezados y mensaje del panel «Carga manual».">' +
                        '<i class="fa fa-ban" aria-hidden="true"></i><span class="visually-hidden"> Lista negra</span></button>'
                    : '';
                return '<tr>' +
                    '<td class="font-monospace small fw-bold text-body">' + safe + '</td>' +
                    '<td class="text-end small">' + formatoBytes(a.bytes) + '</td>' +
                    '<td class="small">' + (a.modificado || '') + '</td>' +
                    '<td class="small text-nowrap">' + diaSem + '</td>' +
                    '<td class="small text-nowrap"' + estTdAttr + '>' + estInner + '</td>' +
                    '<td class="text-center text-nowrap gc-rep-acciones-cell">' +
                    '<a class="btn btn-sm gc-rep-btn-descargar" href="' + href + '" title="Descargar"><i class="fa fa-download"></i></a>' +
                    btnWorker +
                    btnListaNegra +
                    '</td>' +
                    '</tr>';
            }).join('');
            aplicarEstadoBotonesEcWorker();
        } catch (e) {
            tbodyRep.innerHTML = '<tr><td colspan="7" class="text-danger small">' + String(e.message || e) + '</td></tr>';
            aplicarEstadoBotonesEcWorker();
        }
    }

    function alertar(titulo, texto, icono) {
        if (typeof Swal !== 'undefined') {
            Swal.fire(titulo, texto, icono || 'info');
        } else {
            window.alert(titulo + ': ' + texto);
        }
    }

    /**
     * @param {number} [lineas] hasta 400 (tope del servidor)
     * @param {{ scrollBottom?: boolean|'auto' }} [opts] true = siempre al final; false = no mover scroll;
     *   'auto' o ausente = bajar solo si el usuario ya estaba cerca del final (útil con «Auto cada 4 s»)
     */
    async function traerLog(lineas, opts) {
        opts = opts || {};
        var estabaCercaDelFinal = logPanelEstaCercaDelFinal();
        var n = typeof lineas === 'number' && lineas > 0 ? Math.min(400, lineas) : 400;
        try {
            var r = await fetch('/gastoscobranza/logAgente?lines=' + n, {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (data.success && typeof data.contenido === 'string') {
                logPanel.textContent = data.contenido.length ? data.contenido : '(log vacío todavía)';
            } else {
                logPanel.textContent = data.mensaje || 'No se pudo leer el log (¿agente caído?).';
            }
            var bajar;
            if (opts.scrollBottom === true) {
                bajar = true;
            } else if (opts.scrollBottom === false) {
                bajar = false;
            } else {
                bajar = estabaCercaDelFinal;
            }
            if (bajar) {
                scrollLogPanelAlFinal();
            }
        } catch (e) {
            logPanel.textContent = String(e.message || e);
        }
    }

    async function vaciarLogAgente() {
        if (typeof Swal !== 'undefined') {
            var c = await Swal.fire({
                icon: 'warning',
                title: 'Vaciar log del agente',
                html: '<p class="mb-0 small text-start">Se borrará el historial del archivo de log en la máquina donde corre el agente. No afecta reportes Excel ni la base de datos.</p>',
                showCancelButton: true,
                confirmButtonText: 'Sí, vaciar',
                cancelButtonText: 'Cancelar'
            });
            if (!c.isConfirmed) return;
        } else if (!window.confirm('¿Vaciar el log del agente en disco?')) {
            return;
        }
        try {
            var r = await fetch('/gastoscobranza/vaciarLogAgente', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: '{}'
            });
            var data = await r.json();
            if (data.success) {
                alertar('Listo', data.mensaje || 'Log vaciado.', 'success');
                traerLog(400, { scrollBottom: true });
            } else {
                alertar('No se pudo vaciar', data.mensaje || 'Error', 'warning');
            }
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
    }

    function comenzarLogRapidoEcWorker() {
        if (ivLogEcWorker) clearInterval(ivLogEcWorker);
        traerLog(380, { scrollBottom: true });
        ivLogEcWorker = setInterval(function () {
            traerLog(380, { scrollBottom: true });
        }, 2500);
    }

    function detenerLogRapidoEcWorker() {
        if (ivLogEcWorker) {
            clearInterval(ivLogEcWorker);
            ivLogEcWorker = null;
        }
    }

    function construirPayloadCargaVerificacion(nombreArchivo, cargaOpts) {
        cargaOpts = cargaOpts || {};
        var payload = {
            archivo: nombreArchivo,
            dryRun: !!(cargaVerifDry && cargaVerifDry.checked),
            estatus: cargaVerifEstatus ? parseInt(cargaVerifEstatus.value, 10) : 2,
            tipoReporteNulo: true,
            megaPhpDefaults: true
        };
        if (cargaOpts.origenCarpeta === 'reporte') {
            payload.origenCarpeta = 'reporte';
        }
        if (cargaVerifMensaje && cargaVerifMensaje.value.trim()) {
            payload.mensaje = cargaVerifMensaje.value.trim();
        }
        var cargaVerifHeaderRow = document.getElementById('cargaVerifHeaderRow');
        if (cargaVerifHeaderRow && cargaVerifHeaderRow.value !== '' && cargaVerifHeaderRow.value != null) {
            var hrn = parseInt(cargaVerifHeaderRow.value, 10);
            if (hrn >= 1 && hrn <= 200) {
                payload.headerRow = hrn;
            }
        }
        return payload;
    }

    async function invocarCargaVerificacionAgente(nombreArchivo, cargaOpts) {
        var r2 = await fetch('/gastoscobranza/ejecutarCargaVerificacionSemana', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
            body: JSON.stringify(construirPayloadCargaVerificacion(nombreArchivo, cargaOpts))
        });
        return r2.json();
    }

    /**
     * Worker o enrich vía agente; si es worker y termina (cód. 0 o 2), carga lista negra con el mismo Excel.
     * cargaOpts: {} para archivo en ec-uploads; { origenCarpeta: 'reporte' } para Excel ya en reporte/.
     */
    async function ejecutarPayloadEcYListaNegra(payloadEc, cargaOpts) {
        comenzarLogRapidoEcWorker();
        try {
            var r2;
            try {
                r2 = await fetch('/gastoscobranza/ejecutarEcLauncher', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                    body: JSON.stringify(payloadEc)
                });
            } catch (eFetch) {
                alertar('Error de red',
                    'No se pudo conectar al servidor para ejecutar el worker. Lista negra NO se ejecutó. Detalle: ' +
                    String(eFetch.message || eFetch), 'error');
                return;
            }
            var raw = await r2.text();
            var data;
            try {
                data = JSON.parse(raw);
            } catch (eParse) {
                alertar('Error',
                    'El servidor no devolvió JSON en worker/enrich. Suele ser sesión caducada o error PHP. ' +
                    'Lista negra NO se ejecutó. Inicio de respuesta: ' +
                    String(raw).slice(0, 200).replace(/\s+/g, ' '), 'error');
                return;
            }
            var ok = !!data.success;
            var msg = data.mensaje || (ok ? 'Proceso EC terminado.' : 'Error en worker / enrich.');
            var codigoSalida = (typeof data.codigo_salida === 'number') ? data.codigo_salida : -1;
            var esWorker = payloadEc.tipo === 'worker';
            var workerLlegoAlFin = esWorker && (codigoSalida === 0 || codigoSalida === 2);
            if (data.stdout || data.stderr) {
                ecOutPre.textContent = (data.stdout || '') + (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                ecOutWrap.classList.remove('d-none');
            }
            if (data.errores_reintento_csv && ecErroresReintentoBanner && ecErroresReintentoLink) {
                ecErroresReintentoLink.href = '/gastoscobranza/descargarErroresReintento?nombre=' +
                    encodeURIComponent(String(data.errores_reintento_csv));
                ecErroresReintentoBanner.classList.remove('d-none');
            }
            if (workerLlegoAlFin) {
                /* Worker llegó al final (0 = todo ok, 2 = ok con errores parciales) → cargar lista negra automático */
                await traerListaReportes();
                var dataCarga;
                try {
                    dataCarga = await invocarCargaVerificacionAgente(payloadEc.nombre, cargaOpts);
                } catch (eCarga) {
                    alertar('Worker ok — lista negra falló',
                        'El worker terminó (código ' + codigoSalida + ') pero no se pudo contactar al servidor para la lista negra. ' +
                        'Puedes cargarla manualmente con el botón morado en la tabla. Detalle: ' +
                        String(eCarga.message || eCarga) +
                        lineaEstadoReporteRespuesta(data.estado_reporte),
                        'warning');
                    await traerLog(380, { scrollBottom: true });
                    await traerListaReportes();
                    return;
                }
                var okC = !!dataCarga.success;
                if (dataCarga.stdout || dataCarga.stderr) {
                    var bloqueCarga = '\n\n--- Lista negra (automático, mismo Excel) ---\n' +
                        (dataCarga.stdout || '') +
                        (dataCarga.stderr ? '\n--- stderr ---\n' + dataCarga.stderr : '');
                    ecOutPre.textContent = (ecOutPre.textContent || '') + bloqueCarga;
                    ecOutWrap.classList.remove('d-none');
                    if (cargaVerifOutPre) {
                        cargaVerifOutPre.textContent = (dataCarga.stdout || '') +
                            (dataCarga.stderr ? '\n--- stderr ---\n' + dataCarga.stderr : '');
                    }
                    if (cargaVerifOutWrap) cargaVerifOutWrap.classList.remove('d-none');
                }
                if (okC) {
                    if (codigoSalida === 0) {
                        alertar('¡Todo listo!',
                            'Worker completado correctamente.\nLista negra actualizada con el mismo Excel.' +
                            lineaEstadoReporteRespuesta(dataCarga.estado_reporte),
                            'success');
                    } else {
                        alertar('Worker ok con errores parciales — Lista negra actualizada',
                            'El worker terminó (código ' + codigoSalida + '): uno o más créditos con error. ' +
                            'La lista negra se cargó igual con el mismo Excel.\n' +
                            'Revisa el log y el CSV de reintento si aparece.' +
                            lineaEstadoReporteRespuesta(dataCarga.estado_reporte),
                            'warning');
                    }
                } else {
                    alertar('Worker ok — Lista negra FALLÓ',
                        'El worker terminó (código ' + codigoSalida + ') pero la carga a lista negra tuvo un error: ' +
                        (dataCarga.mensaje || 'sin detalle') +
                        '.\nPuedes intentarla manualmente con el botón morado en la tabla de reportes.' +
                        lineaEstadoReporteRespuesta(dataCarga.estado_reporte),
                        'warning');
                }
            } else if (esWorker) {
                /* Worker falló antes de terminar → lista negra no se intentó */
                alertar('Worker falló — Lista negra NO ejecutada',
                    'El worker no llegó al final (código ' + codigoSalida + '). ' +
                    'La lista negra NO se ejecutó para no insertar datos de un lote incompleto.\n\n' +
                    (msg || '') +
                    lineaEstadoReporteRespuesta(data.estado_reporte),
                    'error');
            } else if (ok) {
                alertar('EC listo', msg + lineaEstadoReporteRespuesta(data.estado_reporte), 'success');
            } else {
                alertar('EC con errores', msg + lineaEstadoReporteRespuesta(data.estado_reporte), 'error');
            }
            await traerLog(380, { scrollBottom: true });
            await traerListaReportes();
        } finally {
            detenerLogRapidoEcWorker();
        }
    }

    async function refrescarEstado(opts) {
        opts = opts || {};
        var sil = !!opts.silencioso;
        if (!sil) {
            badge.className = 'badge bg-label-warning';
            badge.textContent = 'Comprobando…';
            detalle.textContent = '—';
            btnRun.disabled = true;
            if (btnEcLauncher) btnEcLauncher.disabled = true;
            if (btnCargaVerif) btnCargaVerif.disabled = true;
            if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
        }
        try {
            var r = await fetch('/gastoscobranza/estadoAgente', {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (!data.success) {
                gcAgenteOnline = false;
                gcAgenteReportaEcOcupado = false;
                badge.className = 'badge bg-label-danger';
                badge.textContent = 'Error';
                detalle.textContent = data.mensaje || 'Error';
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
                aplicarEstadoBotonesEcWorker();
                return;
            }
            if (!data.agente_configurado) {
                gcAgenteOnline = false;
                gcAgenteReportaEcOcupado = false;
                badge.className = 'badge bg-label-secondary';
                badge.textContent = 'INI desactivado';
                detalle.innerHTML = data.detalle || '';
                tbodyRep.innerHTML = '<tr><td colspan="7" class="text-muted small">Habilite el agente en <code>config.ini</code> para listar reportes.</td></tr>';
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
                aplicarEstadoBotonesEcWorker();
                return;
            }
            if (data.agente_online) {
                gcAgenteOnline = true;
                badge.className = 'badge bg-label-success';
                badge.textContent = 'Agente en línea';
                var a = data.agente || {};
                gcAgenteReportaEcOcupado = !!a.ec_launcher_ocupado;
                if (gcAgenteReportaEcOcupado) {
                    detalle.innerHTML = '<span class="text-warning"><i class="fa fa-spinner fa-spin me-1" aria-hidden="true"></i><strong>Worker/EC en ejecución</strong> — espere a que termine antes de lanzar otro.</span>';
                } else {
                    detalle.textContent = '';
                }
                btnRun.disabled = false;
                if (btnCargaVerif) btnCargaVerif.disabled = !a.script_carga_verificacion_semana;
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = !a.script_descargo_estatus3;
                aplicarEstadoBotonesEcWorker();
                if (!sil) {
                    traerLog(400, { scrollBottom: true });
                    traerListaReportes();
                }
            } else {
                gcAgenteOnline = false;
                gcAgenteReportaEcOcupado = false;
                badge.className = 'badge bg-label-danger';
                badge.textContent = 'Sin conexión';
                detalle.textContent = data.detalle || '';
                logPanel.textContent = 'Levante el agente (npm start en gastos-cobranza-agent, puerto 3120).';
                tbodyRep.innerHTML = '<tr><td colspan="7" class="text-muted small">Agente fuera de línea — sin listado.</td></tr>';
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
                aplicarEstadoBotonesEcWorker();
            }
        } catch (e) {
            gcAgenteOnline = false;
            gcAgenteReportaEcOcupado = false;
            badge.className = 'badge bg-label-danger';
            badge.textContent = 'Error red';
            detalle.textContent = String(e.message || e);
            if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
            aplicarEstadoBotonesEcWorker();
        }
    }

    function setDescargoDescargando(enCurso) {
        if (descargoSpinner) descargoSpinner.classList.toggle('d-none', !enCurso);
    }

    async function ejecutarDescargoEstatus3Flujo() {
        if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
        setDescargoDescargando(true);
        try {
            var r = await fetch('/gastoscobranza/descargoEstatus3EjecutarYDescargar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: JSON.stringify({
                    megaPhpDefaults: true,
                    desdeCero: false,
                    sinActualizarGuia: !!(chkDescargoSinActualizarGuia && chkDescargoSinActualizarGuia.checked),
                }),
            });
            var ct = (r.headers.get('content-type') || '').toLowerCase();
            if (ct.indexOf('application/json') !== -1) {
                var data = await r.json();
                await traerLog(400, { scrollBottom: true });
                if (data.sin_excel) {
                    alertar('Descargo', data.mensaje || 'No se generó Excel en esta corrida.', 'info');
                } else if (data.success) {
                    alertar('Descargo', data.mensaje || 'Listo.', 'success');
                } else {
                    var det = data.mensaje || 'Error en el descargo.';
                    if (data.stderr) det += '\n\n' + String(data.stderr).slice(-2000);
                    alertar('Descargo con errores', det, 'error');
                }
                return;
            }
            if (!r.ok) {
                await traerLog(400, { scrollBottom: true });
                alertar('Descargo', 'Respuesta HTTP ' + r.status, 'error');
                return;
            }
            var blob = await r.blob();
            var urlObj = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = urlObj;
            a.download = 'descargo_estatus3.xlsx';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(urlObj);
            await traerLog(400, { scrollBottom: true });
            alertar('Listo', 'Se descargó descargo_estatus3.xlsx', 'success');
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        } finally {
            setDescargoDescargando(false);
            refrescarEstado();
        }
    }

    async function ejecutarEcLauncherFlujo() {
        if (!ecFile || !ecFile.files || !ecFile.files[0]) {
            alertar('Falta archivo', 'Seleccione un Excel .xlsx.', 'warning');
            return;
        }
        if (!ecFecha || !ecFecha.value) {
            alertar('Fecha', 'Indique la fecha de corte.', 'warning');
            return;
        }
        btnEcLauncher.disabled = true;
        ecOutWrap.classList.add('d-none');
        if (ecErroresReintentoBanner) ecErroresReintentoBanner.classList.add('d-none');
        try {
            var fd = new FormData();
            fd.append('archivo', ecFile.files[0]);
            var up = await fetch('/gastoscobranza/subirExcelEc', {
                method: 'POST',
                body: fd,
                headers: { 'Front-Request': 'true' }
            });
            var ju = await up.json();
            if (!ju.success || !ju.nombre) {
                alertar('Subida', ju.mensaje || 'No se pudo subir el archivo.', 'error');
                refrescarEstado();
                return;
            }
            var payloadEc = {
                nombre: ju.nombre,
                tipo: ecEnrich && ecEnrich.checked ? 'enrich' : 'worker',
                fechaCorte: ecFecha.value,
                column: ecCol ? ecCol.value.trim() || 'ID CREDITO' : 'ID CREDITO',
                omitir: ecOmitir ? parseInt(ecOmitir.value, 10) || 0 : 0,
                soloColumnas: false
            };
            await ejecutarPayloadEcYListaNegra(payloadEc, {});
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
        refrescarEstado();
    }

    async function ejecutarCargaVerificacionFlujo() {
        if (!cargaVerifFile || !cargaVerifFile.files || !cargaVerifFile.files[0]) {
            alertar('Falta archivo', 'Seleccione un Excel .xlsx.', 'warning');
            return;
        }
        if (btnCargaVerif) btnCargaVerif.disabled = true;
        if (cargaVerifOutWrap) cargaVerifOutWrap.classList.add('d-none');
        try {
            var fd = new FormData();
            fd.append('archivo', cargaVerifFile.files[0]);
            var up = await fetch('/gastoscobranza/subirExcelEc', {
                method: 'POST',
                body: fd,
                headers: { 'Front-Request': 'true' }
            });
            var ju = await up.json();
            if (!ju.success || !ju.nombre) {
                alertar('Subida', ju.mensaje || 'No se pudo subir el archivo.', 'error');
                refrescarEstado();
                return;
            }
            var data = await invocarCargaVerificacionAgente(ju.nombre);
            var ok = !!data.success;
            var msg = data.mensaje || (ok ? 'Carga terminada.' : 'Error en la carga.');
            if (data.stdout || data.stderr) {
                if (cargaVerifOutPre) {
                    cargaVerifOutPre.textContent = (data.stdout || '') + (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                }
                if (cargaVerifOutWrap) cargaVerifOutWrap.classList.remove('d-none');
            }
            alertar(ok ? 'Carga listo' : 'Carga con errores', msg, ok ? 'success' : 'error');
            await traerLog(400, { scrollBottom: true });
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
        refrescarEstado();
    }

    async function ejecutar() {
        var hoyCdmx = fechaCalendarioCdmxYmd();
        var yaMarcadoLocal = false;
        try {
            yaMarcadoLocal = localStorage.getItem(LS_REPORTE_OK_YMD) === hoyCdmx;
        } catch (e) { /* localStorage no disponible */ }

        var yaExisteArchivoHoy = yaMarcadoLocal;
        if (!yaExisteArchivoHoy) {
            yaExisteArchivoHoy = await hayReporteCobranzaHoyEnServidor();
        }
        if (yaExisteArchivoHoy) {
            var acc = await dialogoReporteYaGeneradoHoy();
            if (acc !== 'forzar') {
                return;
            }
        }

        btnRun.disabled = true;
        outWrap.classList.add('d-none');
        try {
            var r = await fetch('/gastoscobranza/ejecutarReporte', {
                method: 'POST',
                headers: { 'Front-Request': 'true' }
            });
            var rawRep = await r.text();
            var data;
            try {
                data = JSON.parse(rawRep);
            } catch (eJson) {
                alertar('Error',
                    'El servidor no devolvió JSON al generar el reporte. Lo habitual es que PHP cortara por tiempo de espera (ya se extendió el límite en el controlador; revise php.ini si persiste) o que la sesión haya expirado. Inicio de respuesta: ' +
                    String(rawRep).slice(0, 200).replace(/\s+/g, ' '), 'error');
            }
            if (data) {
                var ok = !!data.success;
                var msg = data.mensaje || (ok ? 'Proceso terminado.' : 'Falló la ejecución.');
                if (data.demo) {
                    msg = 'Modo prueba: ' + msg;
                }
                if (data.stdout || data.stderr) {
                    outPre.textContent = (data.stdout || '') + (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                    outWrap.classList.remove('d-none');
                }
                alertar(ok ? 'Listo' : 'Atención', msg, ok ? 'success' : 'warning');
                if (ok && !data.demo) {
                    try {
                        localStorage.setItem(LS_REPORTE_OK_YMD, fechaCalendarioCdmxYmd());
                    } catch (e2) { /* ignorar */ }
                }
                await traerLog(400, { scrollBottom: true });
                await traerListaReportes();
            }
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        } finally {
            refrescarEstado();
        }
    }

    /**
     * Carga verificación semana (lista negra) usando un Excel ya en reporte/ (mismo nombre de fila).
     * Respeta dry-run, estatus, headerRow y mensaje del panel manual (checkboxs/inputs existen aunque el panel esté colapsado).
     */
    async function ejecutarListaNegraDesdeReporte(nombreArchivo) {
        comenzarLogRapidoEcWorker();
        if (cargaVerifOutWrap) cargaVerifOutWrap.classList.add('d-none');
        try {
            var data = await invocarCargaVerificacionAgente(nombreArchivo, { origenCarpeta: 'reporte' });
            var ok = !!data.success;
            var msg = data.mensaje || (ok ? 'Carga a lista negra terminada.' : 'Error en la carga.');
            if (data.stdout || data.stderr) {
                if (cargaVerifOutPre) {
                    cargaVerifOutPre.textContent = (data.stdout || '') +
                        (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                }
                if (cargaVerifOutWrap) cargaVerifOutWrap.classList.remove('d-none');
            }
            alertar(ok ? 'Lista negra' : 'Lista negra con errores',
                msg + lineaEstadoReporteRespuesta(data.estado_reporte),
                ok ? 'success' : 'error');
            await traerLog(380, { scrollBottom: true });
            await traerListaReportes();
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        } finally {
            detenerLogRapidoEcWorker();
            refrescarEstado();
        }
    }

    async function ejecutarWorkerDesdeReporte(nombreArchivo) {
        if (!ecFecha || !ecFecha.value) {
            alertar('Fecha de corte', 'Indique la fecha de corte S2 en la sección «EC Worker / Excel enriquecido» (la misma que usaría al subir el Excel).', 'warning');
            if (ecFecha) ecFecha.focus();
            return;
        }
        ecOutWrap.classList.add('d-none');
        if (ecErroresReintentoBanner) ecErroresReintentoBanner.classList.add('d-none');
        try {
            await conBloqueoWorkerEc(function () {
                var payloadEc = {
                    nombre: nombreArchivo,
                    tipo: 'worker',
                    fechaCorte: ecFecha.value,
                    column: ecCol ? ecCol.value.trim() || 'ID CREDITO' : 'ID CREDITO',
                    omitir: ecOmitir ? parseInt(ecOmitir.value, 10) || 0 : 0,
                    soloColumnas: false,
                    origenCarpeta: 'reporte'
                };
                return ejecutarPayloadEcYListaNegra(payloadEc, { origenCarpeta: 'reporte' });
            });
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        }
        refrescarEstado();
    }

    function programarPoll() {
        if (ivEstado) clearInterval(ivEstado);
        ivEstado = setInterval(function () {
            if (document.hidden) return;
            refrescarEstado({ silencioso: true });
        }, 15000);
        if (ivLog) clearInterval(ivLog);
        ivLog = setInterval(function () {
            if (document.hidden || !chkLog.checked) return;
            if (ivLogEcWorker) return;
            traerLog(400, { scrollBottom: 'auto' });
        }, 4000);
        if (ivRep) clearInterval(ivRep);
        ivRep = setInterval(function () {
            if (document.hidden) return;
            traerListaReportes();
        }, 12000);
    }

    chkLog.addEventListener('change', function () {
        if (chkLog.checked) traerLog(400, { scrollBottom: true });
    });
    if (btnLog) btnLog.addEventListener('click', function () {
        traerLog(400, { scrollBottom: true });
    });
    if (btnLogVaciar) btnLogVaciar.addEventListener('click', vaciarLogAgente);
    if (tbodyRep) {
        tbodyRep.addEventListener('click', function (ev) {
            var btnW = ev.target.closest('.btn-gc-worker-reporte');
            if (btnW && tbodyRep.contains(btnW)) {
                var encW = btnW.getAttribute('data-nombre-enc');
                if (encW) ejecutarWorkerDesdeReporte(decodeURIComponent(encW));
                return;
            }
            var btnN = ev.target.closest('.btn-gc-lista-negra-reporte');
            if (btnN && tbodyRep.contains(btnN)) {
                var encN = btnN.getAttribute('data-nombre-enc');
                if (encN) ejecutarListaNegraDesdeReporte(decodeURIComponent(encN));
            }
        });
    }
    btnListarRep.addEventListener('click', traerListaReportes);
    btnRef.addEventListener('click', refrescarEstado);
    btnRun.addEventListener('click', ejecutar);
    if (ecFecha) ecFecha.value = fechaCalendarioCdmxYmd();
    if (btnEcLauncher) btnEcLauncher.addEventListener('click', ejecutarEcLauncherFlujo);
    if (btnCargaVerif) btnCargaVerif.addEventListener('click', ejecutarCargaVerificacionFlujo);
    if (btnDescargoEstatus3) btnDescargoEstatus3.addEventListener('click', ejecutarDescargoEstatus3Flujo);
    if (chkMostrarCargaVerifManual && wrapCargaVerifManual) {
        chkMostrarCargaVerifManual.addEventListener('change', function () {
            wrapCargaVerifManual.classList.toggle('d-none', !chkMostrarCargaVerifManual.checked);
        });
    }
    refrescarEstado();
    programarPoll();
})();
</script>
