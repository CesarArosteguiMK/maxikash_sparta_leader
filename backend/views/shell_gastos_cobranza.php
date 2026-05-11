<?php
$gc_shell_modo_cartera = !empty($gc_shell_modo_cartera);
$gc_shell_modulo_badge = isset($gc_shell_modulo_badge) ? (string) $gc_shell_modulo_badge : 'Módulo 31';
$gc_shell_ec_worker_label = isset($gc_shell_ec_worker_label) ? (string) $gc_shell_ec_worker_label : 'EC Worker';
$gc_shell_ec_modal_titulo = isset($gc_shell_ec_modal_titulo) ? (string) $gc_shell_ec_modal_titulo : 'EC Worker / Excel enriquecido';
$gc_shell_ec_btn_ejecutar = isset($gc_shell_ec_btn_ejecutar) ? (string) $gc_shell_ec_btn_ejecutar : 'Ejecutar corrida del agente';
$gc_shell_ec_footer_hint = isset($gc_shell_ec_footer_hint) ? (string) $gc_shell_ec_footer_hint
    : 'La corrida puede tardar varios minutos. En modo Worker estándar, al finalizar bien se dispara en automático la lista negra con el mismo Excel para dejar reflejados esos valores en verificación semana.';
$gc_shell_ec_salida_label = isset($gc_shell_ec_salida_label) ? (string) $gc_shell_ec_salida_label : 'Salida EC worker / enrich';
?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 gc-shell-hero-card">
                <div class="card-body py-4">
                    <div class="row align-items-center g-4">
                        <div class="<?= $gc_shell_modo_cartera ? 'col-12' : 'col-lg-8' ?>">
                            <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-1">
                                <h4 class="mb-0 fw-semibold text-heading gc-shell-hero-title">
                                    <i class="fa fa-file-invoice-dollar text-primary me-2"></i>
                                    <?= htmlspecialchars(isset($tituloShell) ? $tituloShell : 'Gastos Cobranza', ENT_QUOTES, 'UTF-8') ?>
                                </h4>
                                <?php if (!$gc_shell_modo_cartera): ?>
                                <span id="gastosCobranzaEstadoBadge" class="badge bg-label-secondary">Comprobando…</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($gc_shell_modo_cartera): ?>
                            <p class="small text-muted mb-0 mt-1" style="max-width:42rem;">
                                Consulta reportes de cartera, revisa movimientos y concilia pagos.
                            </p>
                            <?php else: ?>
                            <div id="gastosCobranzaDetalle" class="small text-muted mt-1" style="min-height:1.25em"></div>
                            <?php endif; ?>
                            <div id="gastosCobranzaEjecucionBanner" class="alert alert-primary d-none py-2 px-3 mt-2 mb-0 small d-flex align-items-center gap-2<?= $gc_shell_modo_cartera ? ' gc-cartera-sin-banner-ejec' : '' ?>" role="status" aria-live="polite">
                                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                                <span class="gc-ejec-text fw-semibold">Operación en curso…</span>
                            </div>
                        </div>
                        <?php if (!$gc_shell_modo_cartera): ?>
                        <div class="col-lg-4 d-flex justify-content-lg-end">
                            <div class="gc-shell-module-card">
                                <div class="gc-shell-module-icon" aria-hidden="true">
                                    <i class="fa fa-shield-alt"></i>
                                </div>
                                <div class="gc-shell-module-text">
                                    <span class="gc-shell-module-label">Control de acceso</span>
                                    <span class="gc-shell-module-name"><?= htmlspecialchars($gc_shell_modulo_badge, ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    $gcRepSem = isset($reportes_esta_semana) ? (string) $reportes_esta_semana : '—';
    $gcUltRep = isset($ultimo_reporte) ? (string) $ultimo_reporte : '—';
    $gcRepAuto = isset($reporte_automatico) ? (string) $reporte_automatico : '—';
    $gcRepAutoColor = ($gcRepAuto === 'Activo') ? '#2ecc8b' : (($gcRepAuto === 'Inactivo') ? '#e74c3c' : '#6b7a90');
    $gcRepAutoClase = ($gcRepAuto === 'Activo') ? 'text-success' : (($gcRepAuto === 'Inactivo') ? 'text-danger' : 'text-muted');
    ?>
    <div style="background:#fff; border:0.5px solid #dde3ec; border-radius:12px; padding:16px 22px; margin-bottom:12px; display:flex; gap:0; flex-wrap:wrap;">
        <div style="flex:1; padding-right:20px; <?= $gc_shell_modo_cartera ? '' : 'border-right:0.5px solid #eef1f5;' ?>">
            <div style="font-size:12px; color:#6b7a90; margin-bottom:4px;">Reportes esta semana</div>
            <div style="font-size:15px; font-weight:700; color:#2ecc8b;"><?= htmlspecialchars($gcRepSem, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div style="flex:1; padding:0 20px; <?= $gc_shell_modo_cartera ? '' : 'border-right:0.5px solid #eef1f5;' ?>">
            <div style="font-size:12px; color:#6b7a90; margin-bottom:4px;">Último reporte</div>
            <div style="font-size:15px; font-weight:700; color:#1a3a5c;"><?= htmlspecialchars($gcUltRep, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php if (!$gc_shell_modo_cartera): ?>
        <div style="flex:1; padding-left:20px; min-width:12rem;">
            <div style="font-size:12px; color:#6b7a90; margin-bottom:4px;">Reporte automático</div>
            <div class="form-check form-switch mb-0 gc-auto-run-switch d-flex align-items-center gap-2 ps-0">
                <input class="form-check-input flex-shrink-0 ms-0" type="checkbox" role="switch" id="switchGcAutoRunReporte" autocomplete="off" disabled
                    title="Si está activo, el agente Node dispara el reporte en la ventana horaria CDMX configurada (por defecto ~10:00). Requiere agente en línea. La preferencia se guarda en el servidor del agente.">
                <span id="gcAutoRunEstadoTexto" class="small fw-semibold <?= htmlspecialchars($gcRepAutoClase, ENT_QUOTES, 'UTF-8') ?>" style="color:<?= htmlspecialchars($gcRepAutoColor, ENT_QUOTES, 'UTF-8') ?>;"><?= htmlspecialchars($gcRepAuto, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="mt-2 pt-2" style="border-top:0.5px solid #eef1f5;">
                <div class="small mb-1" style="font-size:11px; color:#6b7a90;">Días CDMX (auto)</div>
                <div class="d-flex flex-wrap gap-2 align-items-center" role="group" aria-label="Días de ejecución del reporte automático">
                    <?php
                    $gcDiasLabels = [0 => 'Lun', 1 => 'Mar', 2 => 'Mié', 3 => 'Jue', 4 => 'Vie', 5 => 'Sáb', 6 => 'Dom'];
                    foreach ($gcDiasLabels as $wd => $lab) {
                        $wid = (int) $wd;
                        echo '<div class="form-check form-switch m-0 d-flex align-items-center gap-1">';
                        echo '<input class="form-check-input ms-0 flex-shrink-0 gc-auto-run-dia-cb" type="checkbox" role="switch" autocomplete="off" disabled '
                            . 'id="gcAutoRunDia' . $wid . '" data-gc-wd="' . $wid . '" title="Incluir ' . htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') . '">';
                        echo '<label class="form-check-label small mb-0 user-select-none" for="gcAutoRunDia' . $wid . '" style="font-size:11px; color:#4a5f7a;">'
                            . htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') . '</label>';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div id="shellGastosCobranzaAccionesBar" class="card shadow-sm border-0<?= $gc_shell_modo_cartera ? ' gc-shell-cartera-acciones' : '' ?>">
                <div class="card-body py-3">
                    <div class="sg-agent-head mb-3">
                        <?php if ($gc_shell_modo_cartera): ?>
                        <span class="sg-agent-title">
                            <span class="sg-agent-dot" aria-hidden="true"></span>
                            Herramienta
                        </span>
                        <span class="sg-agent-title gc-cartera-titulo-actividad-head">
                            <span class="sg-agent-dot" aria-hidden="true"></span>
                            Actividad
                        </span>
                        <?php else: ?>
                        <span class="sg-agent-title">
                            <span class="sg-agent-dot" aria-hidden="true"></span>
                            Herramientas del agente
                        </span>
                        <?php endif; ?>
                    </div>
                    <div class="sg-agent-actions">
                        <?php if ($gc_shell_modo_cartera): ?>
                        <div class="gc-cartera-herramienta-cuerpo w-100">
                            <div class="gc-cartera-herramienta-col-accion">
                                <div class="sg-agent-row-top">
                                    <div class="sg-btn-wrap">
                                        <button type="button" class="sg-tip-btn sg-btn-violet" id="btnGcAbrirModalEcWorker" data-bs-toggle="modal" data-bs-target="#modalGcEcWorker">
                                            <span class="sg-tip-btn-face">
                                                <i class="fa fa-rocket" aria-hidden="true"></i>
                                                <span class="sg-btn-label"><?= htmlspecialchars($gc_shell_ec_worker_label, ENT_QUOTES, 'UTF-8') ?></span>
                                                <span class="sg-tooltip-icon" data-tip="Sube el Excel acordado y concilia pagos en Sparta (mismo proceso técnico que el worker GC). Puede tardar varios minutos."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="gc-cartera-actividad w-100 min-w-0">
                                <div class="gc-cartera-actividad-header mb-2">
                                    <span class="badge rounded-pill bg-label-secondary gc-cartera-act-pill" id="gcCarteraActividadEstado">En espera</span>
                                </div>
                                <div id="gcCarteraActividadList" class="gc-cartera-actividad-list small" role="log" aria-live="polite" aria-relevant="additions"></div>
                                <?php /* Mismo id que el log del modal en modo GC completo: JS rellena vía traerLog para leer % de avance del worker. */ ?>
                                <textarea id="gastosCobranzaLogPanel" class="d-none" readonly tabindex="-1" aria-hidden="true"></textarea>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="sg-agent-row-top">
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-violet" id="btnGcAbrirModalEcWorker" data-bs-toggle="modal" data-bs-target="#modalGcEcWorker">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-rocket" aria-hidden="true"></i>
                                        <span class="sg-btn-label">EC Worker</span>
                                        <span class="sg-tooltip-icon" data-tip="El Worker es el proceso que actualiza Gastos Cobranza en la base de datos una vez que Cartera ha confirmado que el criterio quedó aplicado en el S2. Cuando el Worker finaliza correctamente, el sistema ejecuta de forma automática la carga a lista negra usando el mismo Excel."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                    </span>
                                </button>
                            </div>
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-warn" id="btnGcAbrirModalListaNegra" data-bs-toggle="modal" data-bs-target="#modalGcListaNegra">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-database" aria-hidden="true"></i>
                                        <span class="sg-btn-label">Lista negra</span>
                                        <span class="sg-tooltip-icon" data-tip="(Opcional.) Si ya usó el Worker, la lista negra suele haberse cargado sola al terminar. Abra este modal solo para ajustar parámetros o para subir un Excel a verificación semana sin pasar antes por el Worker."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                    </span>
                                </button>
                            </div>
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-cyan" id="btnGcAbrirModalDescargo" data-bs-toggle="modal" data-bs-target="#modalGcDescargo">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-file-export" aria-hidden="true"></i>
                                        <span class="sg-btn-label">Descargo GC</span>
                                        <span class="sg-tooltip-icon" data-tip="(opcional). Con Ejecutar agente el reporte unificado ya incluye el descargo; use esto solo si necesita el Excel aparte o una corrida manual."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                    </span>
                                </button>
                            </div>
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-slate" id="btnConfigCorreosGastosCobranza">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-users-gear" aria-hidden="true"></i>
                                        <span class="sg-btn-label">Administrar correos</span>
                                        <span class="sg-tooltip-icon" data-tip="Destinatarios cuando el agente envía el Excel del reporte por correo. Esta lista se relee en cada envío (no hace falta reiniciar el agente). Solo si no hay ningún correo activo guardado aquí, se usan correos de la variable GASTOS_GC_REPORTE_MAIL_TO del .env del agente (si está definida) o los valores por defecto del sistema."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <div class="sg-agent-row-bottom">
                            <button type="button" class="sg-tip-btn sg-btn-green sg-tip-btn-run" id="btnGcAbrirModalLog" data-bs-toggle="modal" data-bs-target="#modalGcAgenteLog">
                                <span class="sg-tip-btn-face">
                                    <i class="fa fa-scroll" aria-hidden="true"></i>
                                    <span class="sg-btn-label">Ver log del agente</span>
                                    <span class="sg-tooltip-icon" data-tip="Log en área de texto: puede seleccionar y copiar. Con «Auto cada 4 s» y durante Worker/lista negra el scroll solo baja si ya estabas al final; al recargar la página sí se muestra el final."><i class="fa fa-info-circle" aria-hidden="true"></i></span>
                                </span>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$gc_shell_modo_cartera): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <h6 class="mb-0 fw-semibold"><i class="fa fa-gears text-primary me-2"></i>Procesos Gastos Cobranza</h6>
                        <span class="text-muted small" style="cursor:help" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="false"
                            title="Proceso 1: insertar_moras_martes.php · Proceso 2: detectar_gdc_liquidados.php · Proceso 3: eliminar_gastos_despachos.php">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <span class="visually-hidden">Información de scripts de procesos</span>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnGcProcesoInsertarMoraMartes" disabled title="Proceso 1: insertar_moras_martes.php">
                            <i class="fa fa-play me-1"></i>Insertar moras martes
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnGcProcesoDetectarLiquidados" disabled title="Proceso 2: detectar_gdc_liquidados.php">
                            <i class="fa fa-play me-1"></i>Detectar GC liquidados
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnGcProcesoEliminarDespachos" disabled title="Proceso 3: eliminar_gastos_despachos.php">
                            <i class="fa fa-play me-1"></i>Eliminar gastos despachos
                        </button>
                    </div>
                    <p id="gcProcesosCronjobsHint" class="small text-warning mb-0 mt-2 d-none" role="status" aria-live="polite"></p>
                    <div id="gcCronjobsSalidaWrap" class="d-none">
                        <label class="form-label small fw-semibold mb-1">Consola de salida (stdout/stderr)</label>
                        <pre id="gcCronjobsSalida"
                            class="bg-light border rounded p-2 small mb-0"
                            style="max-height:260px;overflow:auto;white-space:pre-wrap;"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="modal fade" id="modalGcEcWorker" tabindex="-1" aria-labelledby="modalGcEcWorkerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold" id="modalGcEcWorkerLabel" style="color:#1a3a5c;">
                        <i class="fa fa-rocket text-secondary me-2" aria-hidden="true"></i><?= htmlspecialchars($gc_shell_ec_modal_titulo, ENT_QUOTES, 'UTF-8') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 <?= $gc_shell_modo_cartera ? '' : 'col-md-6 col-lg-5' ?>">
                            <label class="form-label small mb-1 text-muted" for="ecLauncherFile">Excel (.xlsx) — requerido</label>
                            <input type="file" class="form-control form-control-sm" id="ecLauncherFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" autocomplete="off">
                        </div>
                        <?php if (!$gc_shell_modo_cartera): ?>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small mb-1 text-muted" for="ecLauncherFecha">Fecha corte S2</label>
                            <input type="date" class="form-control form-control-sm" id="ecLauncherFecha">
                        </div>
                        <div class="col-12 col-md-3 col-lg-3">
                            <label class="form-label small mb-1 text-muted" for="ecLauncherCol">Columna ID en Excel</label>
                            <input type="text" class="form-control form-control-sm" id="ecLauncherCol" value="ID CREDITO" placeholder="ID CREDITO">
                        </div>
                        <div class="col-6 col-md-3 col-lg-2">
                            <label class="form-label small mb-1 text-nowrap text-muted" for="ecLauncherOmitir" title="Saltar primeros créditos (N)">Omitir N</label>
                            <input type="number" class="form-control form-control-sm" id="ecLauncherOmitir" value="0" min="0" title="Saltar primeros créditos: ignorar los primeros N IDs del Excel" style="max-width: 4.25rem">
                        </div>
                        <div class="col-12">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="ecLauncherEnrich" autocomplete="off">
                                <label class="form-check-label small d-inline-flex align-items-center flex-wrap gap-1" for="ecLauncherEnrich">
                                    Excel enriquecido (+ Chat)
                                    <span class="text-muted" style="cursor: help" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="false" title="Sin marcar: modo Worker (S2, base de datos y Chat). Actualiza Gastos Cobranza cuando Cartera confirmó la aplicación en S2; al terminar bien, corre sola la lista negra con este Excel. Marcada: solo flujo «Excel enriquecido» (enrich completo en servidor, misma auditoría que el proceso enrich)."><i class="fa fa-info-circle" aria-hidden="true"></i><span class="visually-hidden"> Ayuda modos Worker / enriquecido</span></span>
                                </label>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div id="ecErroresReintentoBanner" class="alert alert-warning d-none mt-3 mb-0 py-2 small" role="alert">
                        Tras la segunda pasada automática aún hubo créditos con error.
                        <a id="ecErroresReintentoLink" class="alert-link fw-semibold" href="#">Descargar CSV (id, tipo de error y detalle)</a>
                        para revisión manual.
                    </div>
                    <div id="ecLauncherSalidaWrap" class="d-none mt-3">
                        <label class="form-label small fw-semibold"><?= htmlspecialchars($gc_shell_ec_salida_label, ENT_QUOTES, 'UTF-8') ?></label>
                        <pre id="ecLauncherSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:260px;overflow:auto;white-space:pre-wrap;"></pre>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-wrap align-items-center gap-2">
                    <span class="small text-muted text-start flex-grow-1 me-auto" style="min-width: 12rem;"><?= htmlspecialchars($gc_shell_ec_footer_hint, ENT_QUOTES, 'UTF-8') ?></span>
                    <button type="button" class="btn btn-primary" id="btnEcLauncherEjecutar" disabled>
                        <i class="fa fa-play me-2" aria-hidden="true"></i><?= htmlspecialchars($gc_shell_ec_btn_ejecutar, ENT_QUOTES, 'UTF-8') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$gc_shell_modo_cartera): ?>
    <div class="modal fade" id="modalGcListaNegra" tabindex="-1" aria-labelledby="modalGcListaNegraLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold mb-0" id="modalGcListaNegraLabel" style="color:#1a3a5c;"><i class="fa fa-database text-secondary me-2"></i>Carga manual a lista negra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3 d-flex flex-wrap align-items-baseline gap-2">
                        <span>Carga verificación semana (Excel → BD). Tabla <code>cobranza_gc_verificacion_semana</code>.</span>
                        <span class="text-muted flex-shrink-0" tabindex="0" style="cursor:help" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-html="false" data-bs-custom-class="gc-tooltip-carga-verif" title="Carga verificación semana (Excel → BD). Tabla cobranza_gc_verificacion_semana. SALDO APLICABLE A GC → monto_aplicar. Inicio de semana se calcula solo en el agente: martes que abre la semana operativa según hoy (hora Ciudad de México), igual que en la pantalla de estado de cuenta. Si el mismo id_credito ya tiene fila en esa semana con estatus 3, no se duplica: pasa a estatus 2 y se actualiza celula si viene COMENTARIOS en el Excel. Los nuevos se insertan con el estatus que elijas abajo (por defecto 2). En esta carga, tipo_reporte debe quedar NULL en MySQL. Si el Excel tiene título o filas vacías encima de los encabezados, deje en auto el campo «Fila de encabezados» o indique la fila donde está id_credito (1 = primera fila del libro). Misma lógica cuando el Worker dispara la carga automática (detección automática de fila de títulos).">
                            <i class="fa fa-info-circle" aria-hidden="true"></i>
                            <span class="visually-hidden"> Ayuda detallada de la carga a lista negra</span>
                        </span>
                    </p>
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-lg-5">
                            <label class="form-label small mb-1 text-muted" for="cargaVerifFile">Excel (.xlsx) — requerido</label>
                            <input type="file" class="form-control form-control-sm" id="cargaVerifFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" autocomplete="off">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1 text-muted">Estatus (filas nuevas)</label>
                            <select class="form-select form-select-sm" id="cargaVerifEstatus" title="Solo aplica a INSERT; las que ya estaban en 3 pasan a 2">
                                <option value="2" selected>2</option>
                                <option value="1">1</option>
                                <option value="0">0</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1 text-muted">Fila encabezados (Excel)</label>
                            <input type="number" class="form-control form-control-sm" id="cargaVerifHeaderRow" min="1" max="200" placeholder="Auto" title="Número de fila donde están id_credito, etc. (1 = primera). Vacío = el script la detecta solo.">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1 text-muted">Mensaje lote (opcional)</label>
                            <input type="text" class="form-control form-control-sm" id="cargaVerifMensaje" placeholder="Vacío = mensaje automático en el script" maxlength="500">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="cargaVerifDryRun">
                                <label class="form-check-label small" for="cargaVerifDryRun">Solo simular (dry-run, no inserta en BD)</label>
                            </div>
                        </div>
                    </div>
                    <div id="cargaVerifSalidaWrap" class="d-none mt-3">
                        <label class="form-label small fw-semibold">Salida carga verificación</label>
                        <pre id="cargaVerifSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:260px;overflow:auto;white-space:pre-wrap;"></pre>
                    </div>
                </div>
                <div class="modal-footer d-flex flex-wrap align-items-center gap-2">
                    <span class="small text-muted text-start flex-grow-1 me-auto">Python en el agente: <code>openpyxl</code>, <code>mysql-connector-python</code>.</span>
                    <button type="button" class="btn btn-primary" id="btnCargaVerifEjecutar" disabled>
                        <i class="fa fa-cogs me-2" aria-hidden="true"></i>Cargar lista negra vía agente
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalGcDescargo" tabindex="-1" aria-labelledby="modalGcDescargoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered gc-modal-shell-dialog">
            <div class="position-relative w-100 gc-modal-shell-wrap">
                <button type="button" class="gc-shell-gc-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span class="gc-shell-gc-modal-close-x" aria-hidden="true">&times;</span>
                </button>
                <div class="modal-content border-0 shadow gc-modal-shell-content">
                    <div class="modal-header border-0 pb-2 pt-3 px-4">
                        <h5 class="modal-title fw-semibold mb-0" id="modalGcDescargoLabel" style="color:#1a3a5c;"><i class="fa fa-file-export text-info me-2"></i>Descargo cobranza GC (estatus 3)</h5>
                    </div>
                    <div class="modal-body pt-0 px-4 pb-4 gc-modal-shell-body-scroll">
                        <p class="small text-muted mb-3">Lee <code>cobranza_gc_verificacion_semana</code> con <code>estatus = 3</code>.</p>
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
    </div>

    <div class="modal fade" id="modalGcAgenteLog" tabindex="-1" aria-labelledby="modalGcAgenteLogLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered gc-modal-shell-dialog">
            <div class="position-relative w-100 gc-modal-shell-wrap">
                <button type="button" class="gc-shell-gc-modal-close" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span class="gc-shell-gc-modal-close-x" aria-hidden="true">&times;</span>
                </button>
                <div class="modal-content border-0 shadow gc-modal-shell-content">
                    <div class="modal-header border-0 pb-0 align-items-center flex-wrap gap-2 pe-4">
                        <h5 class="modal-title me-auto mb-0" id="modalGcAgenteLogLabel"><i class="fa fa-scroll text-secondary me-2"></i>Log del agente</h5>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" id="chkGastosCobranzaLogAuto" checked>
                            <label class="form-check-label small" for="chkGastosCobranzaLogAuto">Auto cada 4 s</label>
                        </div>
                    </div>
                    <div class="modal-body pt-2 gc-modal-shell-body-scroll gc-modal-log-body-tall">
                        <div id="shellGastosCobranzaLogToolbar" class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-green" id="btnGastosCobranzaLogAhora"
                                    title="Actualiza el texto del log desde el agente. Si ya desplazaste el panel, el scroll no se mueve salvo que estuvieras al final.">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-download" aria-hidden="true"></i>
                                        <span class="sg-btn-label">Traer log ahora</span>
                                    </span>
                                </button>
                            </div>
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-cyan" id="btnGastosCobranzaLogCopiar" title="Copiar todo el contenido visible del log al portapapeles">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-copy" aria-hidden="true"></i>
                                        <span class="sg-btn-label">Copiar log</span>
                                    </span>
                                </button>
                            </div>
                            <div class="sg-btn-wrap">
                                <button type="button" class="sg-tip-btn sg-btn-warn" id="btnGastosCobranzaLogVaciar" title="Borra el historial del archivo de log en el agente (solo la bitácora en disco)">
                                    <span class="sg-tip-btn-face">
                                        <i class="fa fa-eraser" aria-hidden="true"></i>
                                        <span class="sg-btn-label">Vaciar log</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                        <p class="small text-muted mb-2 d-none d-md-block">Log en área de texto: puede seleccionar y copiar. Con «Auto cada 4 s» y durante Worker/lista negra el scroll <strong>solo</strong> baja si ya estabas al final; al recargar la página sí se muestra el final.</p>
                        <textarea id="gastosCobranzaLogPanel" class="bg-dark text-light border-0 rounded p-3 small mb-0 font-monospace w-100" rows="16" readonly style="max-height:min(52vh,420px);resize:vertical;white-space:pre;overflow:auto;">—</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="card-title mb-0"><i class="fa fa-table text-success me-2"></i>Reportes en carpeta <code>reporte/</code></h5>
                            <p id="gastosCobranzaSemanaActualHint" class="small text-muted mb-0 mt-1">Semana actual (lun–dom, Ciudad de México): —</p>
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div id="shellGastosCobranzaReporteToolbar" class="d-flex flex-wrap align-items-center gap-2">
                                <?php if (!$gc_shell_modo_cartera): ?>
                                <div class="sg-btn-wrap">
                                    <button type="button" class="sg-tip-btn sg-btn-green" id="btnGastosCobranzaEjecutar" disabled>
                                        <span class="sg-tip-btn-face">
                                            <i class="fa fa-play" aria-hidden="true"></i>
                                            <span class="sg-btn-label">Ejecutar agente</span>
                                        </span>
                                    </button>
                                </div>
                                <?php endif; ?>
                                <div class="sg-btn-wrap">
                                    <button type="button" class="sg-tip-btn sg-btn-warn" id="btnGastosCobranzaHistoricoReportes" title="Ver reportes de semanas anteriores (los archivos no se borran)">
                                        <span class="sg-tip-btn-face">
                                            <i class="fa fa-history" aria-hidden="true"></i>
                                            <span class="sg-btn-label">Histórico</span>
                                        </span>
                                    </button>
                                </div>
                                <div class="sg-btn-wrap">
                                    <button type="button" class="sg-tip-btn sg-btn-cyan" id="btnGastosCobranzaListarReportes">
                                        <span class="sg-tip-btn-face">
                                            <i class="fa fa-sync-alt" aria-hidden="true"></i>
                                            <span class="sg-btn-label">Actualizar lista</span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="gastosCobranzaSalidaWrap" class="d-none mb-3">
                        <label class="form-label small fw-semibold mb-1">Salida de la última ejecución (reporte)</label>
                        <pre id="gastosCobranzaSalida" class="bg-light border rounded p-2 small mb-0" style="max-height:220px;overflow:auto;white-space:pre-wrap;"></pre>
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
                                <tr><td colspan="6" class="text-muted small">Cargando…</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalHistoricoReportesGc" tabindex="-1" aria-labelledby="modalHistoricoReportesGcLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalHistoricoReportesGcLabel"><i class="fa fa-history me-2"></i>Histórico de reportes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-2">Archivos guardados en el agente; la tabla principal solo muestra la <strong>semana en curso</strong> (lunes a domingo, Ciudad de México). Los <code>reporte_cobranza_*.xlsx</code> de semanas ya cerradas se mueven solos a <code>reporte/historico/&lt;carpeta por semana&gt;/</code> al listar.</p>
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="selHistoricoSemanaGc">Semana a consultar</label>
                            <select class="form-select form-select-sm" id="selHistoricoSemanaGc">
                                <option value="">— Elija una semana —</option>
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Archivo</th>
                                    <th class="text-end">Tamaño</th>
                                    <th>Modificado (UTC)</th>
                                    <th>Día</th>
                                    <th>Estado</th>
                                    <th class="text-center" width="200">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="gastosCobranzaTablaReportesHistorico">
                                <tr><td colspan="6" class="text-muted small">Seleccione una semana.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<style>
    .container-p-y .row.mb-4:last-of-type {
        margin-bottom: 1rem !important;
    }
    /* Shell hero: encabezado y módulo */
    .gc-shell-hero-card {
        background: linear-gradient(135deg, #fcfdff 0%, #f6f8fc 100%);
        border: 1px solid rgba(67, 89, 113, 0.08) !important;
    }
    .gc-shell-hero-title {
        letter-spacing: -0.02em;
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
    html.dark-mode .gc-shell-hero-card,
    body.dark-mode .gc-shell-hero-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.96) 0%, rgba(15, 23, 42, 0.99) 100%) !important;
        border-color: rgba(148, 163, 184, 0.22) !important;
    }
    html.dark-mode .gc-shell-hero-title,
    body.dark-mode .gc-shell-hero-title {
        color: #e2e8f0 !important;
    }
    html.dark-mode .gc-shell-module-card,
    body.dark-mode .gc-shell-module-card {
        background: rgba(30, 41, 59, 0.95) !important;
        border-color: rgba(148, 163, 184, 0.28) !important;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.35);
    }
    html.dark-mode .gc-shell-module-icon,
    body.dark-mode .gc-shell-module-icon {
        background: linear-gradient(145deg, rgba(129, 140, 248, 0.35), rgba(99, 102, 241, 0.12)) !important;
        color: #c7d2fe !important;
    }
    html.dark-mode .gc-shell-module-label,
    body.dark-mode .gc-shell-module-label {
        color: #cbd5e1 !important;
    }
    html.dark-mode .gc-shell-module-name,
    body.dark-mode .gc-shell-module-name {
        color: #f8fafc !important;
    }
    .gc-auto-run-wrap {
        min-width: 170px;
    }
    .gc-auto-run-switch .form-check-label {
        cursor: pointer;
        user-select: none;
    }
    .gc-auto-run-switch #gcAutoRunEstadoTexto {
        min-width: 64px;
    }
    html.dark-mode .gc-auto-run-switch .form-check-label,
    body.dark-mode .gc-auto-run-switch .form-check-label {
        color: #cbd5e1;
    }
    html.dark-mode .gc-auto-run-switch #gcAutoRunEstadoTexto.text-success,
    body.dark-mode .gc-auto-run-switch #gcAutoRunEstadoTexto.text-success {
        color: #4ade80 !important;
    }
    html.dark-mode .gc-auto-run-switch #gcAutoRunEstadoTexto.text-danger,
    body.dark-mode .gc-auto-run-switch #gcAutoRunEstadoTexto.text-danger {
        color: #f87171 !important;
    }

    /* Barra de acciones alineada con Shell Segundómetro (sg-tip-btn) */
    #shellGastosCobranzaAccionesBar .sg-agent-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    #shellGastosCobranzaAccionesBar .sg-agent-title {
        font-size: 0.76rem;
        font-weight: 600;
        color: #64748b;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
    }
    #shellGastosCobranzaAccionesBar .sg-agent-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #22c55e;
    }
    #shellGastosCobranzaAccionesBar .sg-agent-actions,
    #shellGastosCobranzaReporteToolbar {
        width: min(860px, 100%);
    }
    /* Cartera: la tarjeta Herramienta usa todo el ancho; evita hueco a la derecha del bloque de acciones */
    #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .sg-agent-actions {
        width: 100%;
        max-width: 100%;
    }
    #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-herramienta-cuerpo {
        display: grid;
        grid-template-columns: auto minmax(0, 1fr);
        gap: 1rem 1.35rem;
        align-items: start;
    }
    #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-herramienta-col-accion {
        align-self: start;
    }
    #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-herramienta-col-accion .sg-agent-row-top {
        grid-template-columns: minmax(0, max-content);
        justify-items: start;
    }
    #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-herramienta-col-accion .sg-tip-btn {
        width: auto;
        min-width: 14.5rem;
    }
    #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-titulo-actividad-head {
        margin-left: auto;
    }
    #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-actividad {
        padding-left: 1.15rem;
        margin-left: 0.15rem;
        border-left: 1px solid rgba(67, 89, 113, 0.1);
    }
    #shellGastosCobranzaReporteToolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        gap: 8px;
    }
    #shellGastosCobranzaAccionesBar .sg-agent-row-top {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(156px, 1fr));
        gap: 8px;
        align-items: stretch;
    }
    #shellGastosCobranzaAccionesBar .sg-agent-row-bottom {
        margin-top: 8px;
        display: flex;
        width: 100%;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-wrap,
    #shellGastosCobranzaReporteToolbar .sg-btn-wrap,
    #shellGastosCobranzaLogToolbar .sg-btn-wrap {
        min-width: 0;
    }
    #shellGastosCobranzaReporteToolbar .sg-btn-wrap {
        flex: 1 1 auto;
        max-width: 100%;
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn,
    #shellGastosCobranzaReporteToolbar .sg-tip-btn,
    #shellGastosCobranzaLogToolbar .sg-tip-btn {
        position: relative;
        width: 100%;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: 1px solid;
        background: #fff;
        padding: 0 0.9rem;
        font-size: 0.79rem;
        font-weight: 500;
        line-height: 1;
        transition: all 0.16s ease;
        white-space: nowrap;
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn-face,
    #shellGastosCobranzaReporteToolbar .sg-tip-btn-face,
    #shellGastosCobranzaLogToolbar .sg-tip-btn-face {
        position: relative;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        width: 100%;
        min-width: 0;
        padding-right: 1.35rem;
    }
    #shellGastosCobranzaLogToolbar .sg-tip-btn-face {
        padding-right: 0.65rem;
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn-face > i,
    #shellGastosCobranzaReporteToolbar .sg-tip-btn-face > i,
    #shellGastosCobranzaLogToolbar .sg-tip-btn-face > i {
        font-size: 0.8rem;
        line-height: 1;
    }
    #shellGastosCobranzaAccionesBar .sg-tooltip-icon {
        position: absolute;
        right: 6px;
        top: 50%;
        transform: translateY(-50%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: help;
        opacity: 0.55;
        flex-shrink: 0;
        line-height: 1;
    }
    #shellGastosCobranzaAccionesBar .sg-tooltip-icon i {
        font-size: 0.72rem;
        line-height: 1;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-label,
    #shellGastosCobranzaReporteToolbar .sg-btn-label,
    #shellGastosCobranzaLogToolbar .sg-btn-label {
        flex: 1;
        text-align: left;
    }
    #shellGastosCobranzaReporteToolbar .sg-btn-label,
    #shellGastosCobranzaLogToolbar .sg-btn-label {
        text-align: center;
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn:hover,
    #shellGastosCobranzaReporteToolbar .sg-tip-btn:hover,
    #shellGastosCobranzaLogToolbar .sg-tip-btn:hover {
        transform: translateY(-1px);
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn:active,
    #shellGastosCobranzaReporteToolbar .sg-tip-btn:active,
    #shellGastosCobranzaLogToolbar .sg-tip-btn:active {
        transform: scale(0.98);
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn:disabled,
    #shellGastosCobranzaReporteToolbar .sg-tip-btn:disabled,
    #shellGastosCobranzaLogToolbar .sg-tip-btn:disabled {
        opacity: 0.9;
        cursor: not-allowed;
        transform: none;
        pointer-events: none;
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn:disabled .sg-tooltip-icon {
        pointer-events: auto;
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn:disabled .sg-tip-btn-face,
    #shellGastosCobranzaReporteToolbar .sg-tip-btn:disabled .sg-tip-btn-face,
    #shellGastosCobranzaLogToolbar .sg-tip-btn:disabled .sg-tip-btn-face {
        opacity: 0.55;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-danger,
    #shellGastosCobranzaReporteToolbar .sg-btn-danger,
    #shellGastosCobranzaLogToolbar .sg-btn-danger {
        background: #fff5f5;
        border-color: #fca5a5;
        color: #dc2626;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-danger:hover,
    #shellGastosCobranzaReporteToolbar .sg-btn-danger:hover,
    #shellGastosCobranzaLogToolbar .sg-btn-danger:hover {
        background: #fee2e2;
        border-color: #f87171;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-warn,
    #shellGastosCobranzaReporteToolbar .sg-btn-warn,
    #shellGastosCobranzaLogToolbar .sg-btn-warn {
        background: #fffbeb;
        border-color: #fcd34d;
        color: #b45309;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-warn:hover,
    #shellGastosCobranzaReporteToolbar .sg-btn-warn:hover,
    #shellGastosCobranzaLogToolbar .sg-btn-warn:hover {
        background: #fef3c7;
        border-color: #fbbf24;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-cyan,
    #shellGastosCobranzaReporteToolbar .sg-btn-cyan,
    #shellGastosCobranzaLogToolbar .sg-btn-cyan {
        background: #f0fdfa;
        border-color: #5eead4;
        color: #0d9488;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-cyan:hover,
    #shellGastosCobranzaReporteToolbar .sg-btn-cyan:hover,
    #shellGastosCobranzaLogToolbar .sg-btn-cyan:hover {
        background: #ccfbf1;
        border-color: #2dd4bf;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-green,
    #shellGastosCobranzaReporteToolbar .sg-btn-green,
    #shellGastosCobranzaLogToolbar .sg-btn-green {
        background: #f0fdf4;
        border-color: #86efac;
        color: #16a34a;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-green:hover,
    #shellGastosCobranzaReporteToolbar .sg-btn-green:hover,
    #shellGastosCobranzaLogToolbar .sg-btn-green:hover {
        background: #dcfce7;
        border-color: #4ade80;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-violet {
        background: #f5f3ff;
        border-color: #c4b5fd;
        color: #5b21b6;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-violet:hover {
        background: #ede9fe;
        border-color: #a78bfa;
        color: #4c1d95;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-slate {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
    }
    #shellGastosCobranzaAccionesBar .sg-btn-slate:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #334155;
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn-run {
        min-height: 38px;
        height: 38px;
        width: 100%;
        flex: 1 1 auto;
        font-size: 0.82rem;
        display: flex;
        justify-content: center;
    }
    #shellGastosCobranzaAccionesBar .sg-tip-btn-run .sg-btn-label {
        flex: 0 1 auto;
        text-align: center;
    }
    #shellGastosCobranzaAccionesBar .sg-tooltip-icon::after {
        content: attr(data-tip);
        position: absolute;
        left: 50%;
        bottom: calc(100% + 8px);
        transform: translateX(-50%) translateY(3px);
        font-size: 0.72rem;
        line-height: 1.35;
        background: #020617;
        color: #f8fafc;
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.45);
        padding: 0.45rem 0.65rem;
        border-radius: 8px;
        white-space: normal;
        max-width: min(460px, 95vw);
        min-width: min(280px, 92vw);
        text-align: left;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.16s ease, transform 0.16s ease;
        z-index: 1080;
    }
    #shellGastosCobranzaAccionesBar .sg-tooltip-icon::before {
        content: '';
        position: absolute;
        left: 50%;
        bottom: calc(100% + 2px);
        transform: translateX(-50%);
        border: 4px solid transparent;
        border-top-color: #020617;
        opacity: 0;
        transition: opacity 0.16s ease;
        z-index: 1080;
    }
    #shellGastosCobranzaAccionesBar .sg-tooltip-icon:hover::after,
    #shellGastosCobranzaAccionesBar .sg-tooltip-icon:hover::before {
        opacity: 1;
    }
    #shellGastosCobranzaAccionesBar .sg-tooltip-icon:hover::after {
        transform: translateX(-50%) translateY(0);
    }
    /* Modales Shell Gastos Cobranza (log y descargo): cierre flotante + sin recorte horizontal. EC Worker y lista negra usan modal Bootstrap estándar. */
    #modalGcAgenteLog.modal,
    #modalGcDescargo.modal {
        overflow-x: visible !important;
    }
    #modalGcAgenteLog .gc-modal-shell-dialog,
    #modalGcDescargo .gc-modal-shell-dialog {
        position: relative;
    }
    #modalGcAgenteLog .gc-modal-shell-wrap,
    #modalGcDescargo .gc-modal-shell-wrap {
        overflow: visible;
        pointer-events: none;
    }
    #modalGcAgenteLog .gc-modal-shell-wrap .modal-content,
    #modalGcDescargo .gc-modal-shell-wrap .modal-content {
        pointer-events: auto;
    }
    #modalGcAgenteLog .gc-modal-shell-content,
    #modalGcDescargo .gc-modal-shell-content {
        overflow: visible;
        border-radius: 0.5rem;
    }
    #modalGcDescargo .gc-modal-shell-body-scroll {
        max-height: min(78vh, 680px);
        overflow-y: auto;
        overflow-x: hidden;
    }
    .tooltip.gc-tooltip-carga-verif {
        --bs-tooltip-max-width: min(38rem, 94vw);
    }
    .tooltip.gc-tooltip-carga-verif .tooltip-inner {
        text-align: left;
        line-height: 1.45;
    }
    /* Zona Excel tipo “Seleccionar archivo” + botón corrida ancho (EC Worker / lista negra) */
    .gc-excel-file-zone {
        border-radius: 12px;
        border: 1px dashed #b0bec5;
        background: linear-gradient(180deg, #fafbfd 0%, #f4f6f9 100%);
        transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
    }
    .gc-excel-file-zone.gc-excel-zone-filled {
        border-style: solid;
        border-color: #cfd8e3;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }
    .gc-excel-file-zone.gc-excel-zone-shell-blocked {
        opacity: 0.55;
        pointer-events: none;
    }
    .gc-excel-file-zone:focus-within {
        border-color: #94a3b8;
        box-shadow: 0 0 0 0.2rem rgba(26, 58, 92, 0.12);
    }
    .gc-excel-file-face {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        min-height: 2.5rem;
        padding: 0.5rem 0.9rem;
        cursor: pointer;
        border-radius: 11px;
        user-select: none;
    }
    .gc-excel-file-face:hover {
        background: rgba(26, 58, 92, 0.04);
    }
    .gc-excel-file-face-text {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    /* EC Worker (modal estándar): misma escala visual que form-control-sm de la fila */
    #modalGcEcWorker .gc-excel-file-zone {
        border-radius: 0.375rem;
        background: #fff;
        border-color: #ced4da;
    }
    #modalGcEcWorker .gc-excel-file-zone.gc-excel-zone-empty {
        background: #fff;
        background-image: none;
    }
    #modalGcEcWorker .gc-excel-file-face {
        min-height: 31px;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        gap: 0.4rem;
        font-size: 0.875rem;
        line-height: 1.5;
    }
    #modalGcEcWorker .gc-excel-file-face .fa-paperclip {
        font-size: 0.8125rem;
        opacity: 0.85;
    }
    #modalGcEcWorker .gc-excel-file-zone:focus-within {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    html.dark-mode #modalGcEcWorker .gc-excel-file-zone,
    body.dark-mode #modalGcEcWorker .gc-excel-file-zone {
        background: rgba(15, 23, 42, 0.55);
        border-color: #64748b;
    }
    html.dark-mode #modalGcEcWorker .gc-excel-file-zone.gc-excel-zone-filled,
    body.dark-mode #modalGcEcWorker .gc-excel-file-zone.gc-excel-zone-filled {
        background: rgba(15, 23, 42, 0.65);
    }
    .gc-excel-run-btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.55rem 1rem;
        border: 1px solid #c5ced9;
        background: #f1f4f8;
        color: #1a3a5c;
        min-height: 2.55rem;
    }
    .gc-excel-run-btn-compact {
        font-size: 0.875rem;
        font-weight: 600;
        padding: 0.4rem 0.75rem;
        min-height: 2.35rem;
        line-height: 1.25;
        white-space: nowrap;
        width: auto;
        align-self: flex-start;
    }
    .gc-excel-run-btn:hover:not(:disabled) {
        background: #e8ecf2;
        border-color: #aebccf;
        color: #142d45;
    }
    .gc-excel-run-btn:disabled {
        opacity: 0.55;
        cursor: not-allowed;
    }
    .gc-excel-run-btn-secondary {
        background: #f8f9fb;
        color: #334155;
    }
    .gc-excel-run-btn-secondary:hover:not(:disabled) {
        background: #eef1f6;
        color: #1e293b;
    }
    html.dark-mode .gc-excel-file-zone,
    body.dark-mode .gc-excel-file-zone {
        background: rgba(30, 41, 59, 0.35);
        border-color: #64748b;
    }
    html.dark-mode .gc-excel-file-zone.gc-excel-zone-filled,
    body.dark-mode .gc-excel-file-zone.gc-excel-zone-filled {
        background: rgba(15, 23, 42, 0.55);
        border-color: #94a3b8;
    }
    html.dark-mode .gc-excel-run-btn,
    body.dark-mode .gc-excel-run-btn {
        background: rgba(30, 41, 59, 0.6);
        border-color: #64748b;
        color: #e2e8f0;
    }
    #modalGcAgenteLog .gc-modal-shell-body-scroll.gc-modal-log-body-tall {
        max-height: min(72vh, 620px);
        overflow-y: auto;
        overflow-x: hidden;
    }
    #modalGcAgenteLog .gc-shell-gc-modal-close,
    #modalGcDescargo .gc-shell-gc-modal-close {
        position: absolute;
        top: -12px;
        right: -12px;
        z-index: 20;
        width: 36px;
        height: 36px;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e2e6ed;
        border-radius: 10px;
        background-color: #f4f6f9;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.12), 0 0 0 1px rgba(255, 255, 255, 0.6) inset;
        color: #8b95a5;
        font-size: 1.35rem;
        line-height: 1;
        font-weight: 300;
        cursor: pointer;
        pointer-events: auto;
        -webkit-appearance: none;
        appearance: none;
    }
    #modalGcAgenteLog .gc-shell-gc-modal-close:hover,
    #modalGcDescargo .gc-shell-gc-modal-close:hover {
        background-color: #eceff4;
        color: #5c6b7d;
        border-color: #d5dbe6;
    }
    #modalGcAgenteLog .gc-shell-gc-modal-close:focus,
    #modalGcDescargo .gc-shell-gc-modal-close:focus {
        outline: none;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.14), 0 0 0 2px rgba(105, 108, 255, 0.35);
    }
    #modalGcAgenteLog .gc-shell-gc-modal-close-x,
    #modalGcDescargo .gc-shell-gc-modal-close-x {
        display: block;
        margin-top: -3px;
        font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
    }
    html.dark-mode #modalGcAgenteLog .gc-shell-gc-modal-close,
    html.dark-mode #modalGcDescargo .gc-shell-gc-modal-close,
    body.dark-mode #modalGcAgenteLog .gc-shell-gc-modal-close,
    body.dark-mode #modalGcDescargo .gc-shell-gc-modal-close {
        background-color: #3d4a5c;
        border-color: rgba(148, 163, 184, 0.35);
        color: #cbd5e1;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.06) inset;
    }
    html.dark-mode #modalGcAgenteLog .gc-shell-gc-modal-close:hover,
    html.dark-mode #modalGcDescargo .gc-shell-gc-modal-close:hover,
    body.dark-mode #modalGcAgenteLog .gc-shell-gc-modal-close:hover,
    body.dark-mode #modalGcDescargo .gc-shell-gc-modal-close:hover {
        background-color: #4b5c73;
        color: #f1f5f9;
        border-color: rgba(148, 163, 184, 0.5);
    }
    @media (max-width: 991.98px) {
        #shellGastosCobranzaAccionesBar .sg-agent-actions {
            width: 100%;
        }
    }
    @media (max-width: 767.98px) {
        #shellGastosCobranzaAccionesBar .sg-agent-row-top {
            grid-template-columns: 1fr;
        }
        #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-herramienta-cuerpo {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-actividad {
            border-left: none;
            margin-left: 0;
            padding-left: 0;
            padding-top: 1rem;
            margin-top: 0.15rem;
            border-top: 1px solid rgba(67, 89, 113, 0.1);
        }
        #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-herramienta-col-accion .sg-tip-btn {
            width: 100%;
        }
        #shellGastosCobranzaAccionesBar .sg-tip-btn,
        #shellGastosCobranzaReporteToolbar .sg-tip-btn,
        #shellGastosCobranzaLogToolbar .sg-tip-btn {
            height: 36px;
        }
        #shellGastosCobranzaAccionesBar .sg-tip-btn-run {
            width: 100%;
        }
    }
    html.dark-mode #shellGastosCobranzaAccionesBar,
    body.dark-mode #shellGastosCobranzaAccionesBar {
        background: rgba(30, 41, 59, 0.92);
        border-color: rgba(148, 163, 184, 0.18) !important;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-agent-title,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-agent-title {
        color: #cbd5e1;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-actividad,
    body.dark-mode #shellGastosCobranzaAccionesBar.gc-shell-cartera-acciones .gc-cartera-actividad {
        border-left-color: rgba(148, 163, 184, 0.22);
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-tip-btn,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-tip-btn,
    html.dark-mode #shellGastosCobranzaLogToolbar .sg-tip-btn,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-tip-btn,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-tip-btn,
    body.dark-mode #shellGastosCobranzaLogToolbar .sg-tip-btn {
        background: rgba(30, 41, 59, 0.9);
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.06) inset;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-danger,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-danger,
    html.dark-mode #shellGastosCobranzaLogToolbar .sg-btn-danger,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-danger,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-danger,
    body.dark-mode #shellGastosCobranzaLogToolbar .sg-btn-danger {
        background: rgba(127, 29, 29, 0.5);
        border-color: rgba(248, 113, 113, 0.55);
        color: #fecaca;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-danger:hover,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-danger:hover,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-danger:hover,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-danger:hover {
        background: rgba(153, 27, 27, 0.62);
        border-color: #f87171;
        color: #fef2f2;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-warn,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-warn,
    html.dark-mode #shellGastosCobranzaLogToolbar .sg-btn-warn,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-warn,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-warn,
    body.dark-mode #shellGastosCobranzaLogToolbar .sg-btn-warn {
        background: rgba(120, 53, 15, 0.45);
        border-color: rgba(251, 191, 36, 0.5);
        color: #fde68a;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-warn:hover,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-warn:hover,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-warn:hover,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-warn:hover {
        background: rgba(146, 64, 14, 0.58);
        border-color: #fbbf24;
        color: #fffbeb;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-cyan,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-cyan,
    html.dark-mode #shellGastosCobranzaLogToolbar .sg-btn-cyan,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-cyan,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-cyan,
    body.dark-mode #shellGastosCobranzaLogToolbar .sg-btn-cyan {
        background: rgba(17, 94, 89, 0.42);
        border-color: rgba(45, 212, 191, 0.45);
        color: #99f6e4;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-cyan:hover,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-cyan:hover,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-cyan:hover,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-cyan:hover {
        background: rgba(19, 78, 74, 0.55);
        border-color: #2dd4bf;
        color: #ccfbf1;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-green,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-green,
    html.dark-mode #shellGastosCobranzaLogToolbar .sg-btn-green,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-green,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-green,
    body.dark-mode #shellGastosCobranzaLogToolbar .sg-btn-green {
        background: rgba(20, 83, 45, 0.48);
        border-color: rgba(74, 222, 128, 0.5);
        color: #bbf7d0;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-green:hover,
    html.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-green:hover,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-green:hover,
    body.dark-mode #shellGastosCobranzaReporteToolbar .sg-btn-green:hover {
        background: rgba(22, 101, 52, 0.6);
        border-color: #4ade80;
        color: #f0fdf4;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-violet,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-violet {
        background: rgba(76, 29, 149, 0.45);
        border-color: rgba(167, 139, 250, 0.5);
        color: #e9d5ff;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-violet:hover,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-violet:hover {
        background: rgba(91, 33, 182, 0.55);
        border-color: #c4b5fd;
        color: #f5f3ff;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-slate,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-slate {
        background: rgba(51, 65, 85, 0.65);
        border-color: rgba(148, 163, 184, 0.45);
        color: #e2e8f0;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-slate:hover,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-btn-slate:hover {
        background: rgba(71, 85, 105, 0.75);
        border-color: rgba(203, 213, 225, 0.55);
        color: #f8fafc;
    }
    html.dark-mode #shellGastosCobranzaAccionesBar .sg-tip-btn:disabled,
    body.dark-mode #shellGastosCobranzaAccionesBar .sg-tip-btn:disabled {
        opacity: 1;
    }

    .card.gc-card-accent-descargo.border-0 {
        border-top: 0 !important;
        border-right: 0 !important;
        border-bottom: 0 !important;
        border-left: 4px solid #3abaf4 !important;
    }
    .gc-card-accent-descargo-inner {
        border-left: 3px solid rgba(58, 186, 244, 0.55);
        padding-left: 1rem !important;
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

    /* Cartera — columna derecha: solo estado (el título Actividad va en sg-agent-head con el mismo estilo que Herramienta) */
    .gc-cartera-actividad-header {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
        gap: 0.45rem;
    }

    /* Cartera — panel de actividad (progreso legible, sin ruido técnico) */
    .gc-cartera-actividad-list {
        max-height: 11.5rem;
        width: min(100%, 31rem);
        margin-left: auto;
        overflow-y: auto;
        border-radius: 0.5rem;
        background: linear-gradient(180deg, #fafbfd 0%, #f4f6f9 100%);
        border: 1px solid rgba(67, 89, 113, 0.08);
        padding: 0.5rem 0.65rem;
    }
    .gc-cartera-act-line {
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
        padding: 0.35rem 0.25rem;
        border-radius: 0.35rem;
        margin-bottom: 0.15rem;
        border-left: 3px solid #cbd5e1;
        background: rgba(255, 255, 255, 0.65);
    }
    .gc-cartera-act-line:last-child { margin-bottom: 0; }
    .gc-cartera-act-line--info { border-left-color: #94a3b8; background: rgba(148, 163, 184, 0.08); }
    .gc-cartera-act-line--run { border-left-color: #696cff; background: rgba(105, 108, 255, 0.06); }
    .gc-cartera-act-line--ok { border-left-color: #2ecc8b; background: rgba(46, 204, 139, 0.07); }
    .gc-cartera-act-line--warn { border-left-color: #f5a524; background: rgba(245, 165, 36, 0.08); }
    .gc-cartera-act-line--err { border-left-color: #e74c3c; background: rgba(231, 76, 60, 0.06); }
    .gc-cartera-act-time {
        flex-shrink: 0;
        font-size: 0.65rem;
        opacity: 0.75;
        color: #697a8d;
        min-width: 4.5rem;
    }
    .gc-cartera-act-msg { flex: 1; min-width: 0; color: #3a4a5c; line-height: 1.35; }
    .gc-cartera-act-pill { font-weight: 600; font-size: 0.65rem; letter-spacing: 0.02em; }

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
    var gcShellModoCartera = <?= $gc_shell_modo_cartera ? 'true' : 'false' ?>;
    var badge = document.getElementById('gastosCobranzaEstadoBadge');
    var detalle = document.getElementById('gastosCobranzaDetalle');
    var btnRun = document.getElementById('btnGastosCobranzaEjecutar');
    var btnGcProcesoInsertarMoraMartes = document.getElementById('btnGcProcesoInsertarMoraMartes');
    var btnGcProcesoDetectarLiquidados = document.getElementById('btnGcProcesoDetectarLiquidados');
    var btnGcProcesoEliminarDespachos = document.getElementById('btnGcProcesoEliminarDespachos');
    var btnEcLauncher = document.getElementById('btnEcLauncherEjecutar');
    var btnAbrirModalEcWorker = document.getElementById('btnGcAbrirModalEcWorker');
    var btnAbrirModalListaNegra = document.getElementById('btnGcAbrirModalListaNegra');
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
    var gcCronjobsSalidaWrap = document.getElementById('gcCronjobsSalidaWrap');
    var gcCronjobsSalida = document.getElementById('gcCronjobsSalida');
    var logPanel = document.getElementById('gastosCobranzaLogPanel');
    var chkLog = document.getElementById('chkGastosCobranzaLogAuto');
    var btnLog = document.getElementById('btnGastosCobranzaLogAhora');
    var btnLogCopiar = document.getElementById('btnGastosCobranzaLogCopiar');
    var btnLogVaciar = document.getElementById('btnGastosCobranzaLogVaciar');
    var btnListarRep = document.getElementById('btnGastosCobranzaListarReportes');
    var btnHistoricoRep = document.getElementById('btnGastosCobranzaHistoricoReportes');
    var tbodyRep = document.getElementById('gastosCobranzaTablaReportes');
    var tbodyRepHist = document.getElementById('gastosCobranzaTablaReportesHistorico');
    var selHistoricoSemana = document.getElementById('selHistoricoSemanaGc');
    var hintSemanaActual = document.getElementById('gastosCobranzaSemanaActualHint');
    /** Última lista completa del agente (sin filtrar por semana). */
    var gcCacheArchivosReporte = [];
    var ivEstado = null;
    var ivLog = null;
    var ivRep = null;
    /** Log más frecuente mientras corre EC launcher (worker/enrich). */
    var ivLogEcWorker = null;
    /** Agente alcanzable y con EC listo (misma condición que antes para habilitar subida EC). */
    var gcAgenteOnline = false;
    /** El agente reporta proceso EC en curso (otra pestaña u otro usuario): mismo bloqueo en UI. */
    var gcAgenteReportaEcOcupado = false;
    /** El agente reporta ejecución de proceso cronjob GC en curso (otra sesión). */
    var gcAgenteReportaCronjobsOcupado = false;
    /** Scripts disponibles reportados por /health para los 3 procesos. */
    var gcCronjobsScriptsDisponibles = {
        insertar_mora_martes: false,
        detectar_gdc_liquidados: false,
        eliminar_gastos_despachos: false
    };
    /** reporte | worker | enrich | lista_negra | descargo — bloquea el resto del shell mientras corre. */
    var gcShellOperacionEnCurso = null;
    /** Último estado de scripts en agente (para re-habilitar botones al terminar operación shell). */
    var gcUltimoScriptCarga = false;
    var gcUltimoScriptDescargo = false;
    /** Evita reejecutar el mismo día (CDMX) tras un reporte real exitoso. */
    var LS_REPORTE_OK_YMD = 'gastosCobranza_reporteRealOkYmd';

    var gcCarteraActividadList = document.getElementById('gcCarteraActividadList');
    var gcCarteraActividadEstado = document.getElementById('gcCarteraActividadEstado');
    var gcCarteraActividadVacio = document.getElementById('gcCarteraActividadVacio');
    /** Evita repetir el mismo aviso de “ocupado remoto” en cada poll. */
    var gcCarteraUltimoAvisoRemoto = '';
    /** Máx. mensajes de “sin conexión / error al verificar” por racha offline (poll cada ~15 s). */
    var gcCarteraAvisosConexionMax = 2;
    var gcCarteraAvisosConexionEnRacha = 0;

    function gcCarteraResetAvisosConexion() {
        gcCarteraAvisosConexionEnRacha = 0;
    }

    /**
     * Mensaje de fallo de conexión/verificación: como mucho gcCarteraAvisosConexionMax veces hasta volver en línea.
     */
    function gcCarteraPushConexionLimitado(mensaje, claseLinea) {
        if (!gcShellModoCartera) return;
        if (gcCarteraAvisosConexionEnRacha >= gcCarteraAvisosConexionMax) return;
        gcCarteraAvisosConexionEnRacha++;
        gcCarteraActividadPush(mensaje, claseLinea || 'gc-cartera-act-line--warn');
    }

    /** Último % leído del log del worker (Conciliar pagos); se resetea al terminar la operación. */
    var gcCarteraWorkerUltimoPct = -1;

    function gcCarteraSincronizarProgresoWorkerDesdeLog() {
        if (!gcShellModoCartera || !logPanel || gcShellOperacionEnCurso !== 'worker') return;
        var t = logPanel.value || '';
        var re = /\[ec-webhook-worker\] Avance:\s*(\d+)\/(\d+)\s*\((\d+)%\)/g;
        var m;
        var last = null;
        while ((m = re.exec(t)) !== null) {
            last = m;
        }
        if (!last) return;
        var n = parseInt(last[1], 10);
        var tot = parseInt(last[2], 10);
        var pct = parseInt(last[3], 10);
        if (isNaN(pct) || isNaN(tot) || tot <= 0) return;
        gcCarteraWorkerUltimoPct = pct;
        var sub = pct + '% · ' + n + '/' + tot;
        gcCarteraActividadEstadoSet('En curso · ' + sub, 'bg-label-warning');
    }

    function gcCarteraEsc(s) {
        return String(s || '')
            .split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;');
    }

    function gcCarteraFmtHora() {
        try {
            var d = new Date();
            var h = d.getHours();
            var m = d.getMinutes();
            var s = d.getSeconds();
            return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
        } catch (e) {
            return '—';
        }
    }

    function gcCarteraActividadPush(mensaje, claseLinea) {
        if (!gcShellModoCartera || !gcCarteraActividadList) return;
        if (gcCarteraActividadVacio) gcCarteraActividadVacio.classList.add('d-none');
        var cls = claseLinea || 'gc-cartera-act-line--info';
        var row = document.createElement('div');
        row.className = 'gc-cartera-act-line ' + cls;
        row.innerHTML = '<span class="gc-cartera-act-time">' + gcCarteraFmtHora() + '</span>' +
            '<span class="gc-cartera-act-msg">' + gcCarteraEsc(mensaje) + '</span>';
        gcCarteraActividadList.appendChild(row);
        while (gcCarteraActividadList.children.length > 28) {
            gcCarteraActividadList.removeChild(gcCarteraActividadList.firstChild);
        }
        try {
            gcCarteraActividadList.scrollTop = gcCarteraActividadList.scrollHeight;
        } catch (e2) { /* ignorar */ }
    }

    function gcCarteraActividadEstadoSet(texto, pillClass) {
        if (!gcCarteraActividadEstado) return;
        gcCarteraActividadEstado.textContent = texto || 'En espera';
        gcCarteraActividadEstado.className = 'badge rounded-pill gc-cartera-act-pill ' + (pillClass || 'bg-label-secondary');
    }

    function gcCarteraMapOperacionHumana(tipo) {
        var map = {
            reporte: 'Generando reporte de cobranza…',
            worker: 'Conciliando pagos en Sparta…',
            enrich: 'Procesando Excel enriquecido…',
            lista_negra: 'Aplicando resultados en verificación semana…',
            descargo: 'Generando descargo…',
            insertar_mora_martes: 'Ejecutando proceso de moras…',
            detectar_gdc_liquidados: 'Detectando créditos liquidados…',
            eliminar_gastos_despachos: 'Procesando gastos despachos…'
        };
        return map[tipo] || 'Proceso en curso…';
    }

    function gcOcultarHintProcesosCronjobs() {
        var el = document.getElementById('gcProcesosCronjobsHint');
        if (!el) return;
        el.classList.add('d-none');
        el.textContent = '';
    }

    /**
     * /health puede no incluir cronjobs_gc.scripts (agente antiguo): no dejar los botones bloqueados.
     * Si los tres vienen en false, el agente no ve los .php (carpeta equivocada); ver GASTOS_GC_CRONJOBS_DIR en .env del agente.
     */
    function gcAplicarScriptsCronjobsDesdeAgente(agente) {
        agente = agente || {};
        var cg = agente.cronjobs_gc;
        if (!cg || !Object.prototype.hasOwnProperty.call(cg, 'scripts')) {
            gcCronjobsScriptsDisponibles = {
                insertar_mora_martes: true,
                detectar_gdc_liquidados: true,
                eliminar_gastos_despachos: true
            };
            gcOcultarHintProcesosCronjobs();
            return;
        }
        var rawScripts = cg.scripts;
        if (rawScripts === null || typeof rawScripts !== 'object') {
            gcCronjobsScriptsDisponibles = {
                insertar_mora_martes: true,
                detectar_gdc_liquidados: true,
                eliminar_gastos_despachos: true
            };
            gcOcultarHintProcesosCronjobs();
            return;
        }
        var s1 = !!rawScripts.insertar_mora_martes;
        var s2 = !!rawScripts.detectar_gdc_liquidados;
        var s3 = !!rawScripts.eliminar_gastos_despachos;
        gcCronjobsScriptsDisponibles = {
            insertar_mora_martes: s1,
            detectar_gdc_liquidados: s2,
            eliminar_gastos_despachos: s3
        };
        var keys = Object.keys(rawScripts);
        var allFalse = !s1 && !s2 && !s3;
        var el = document.getElementById('gcProcesosCronjobsHint');
        if (el && allFalse && keys.length > 0) {
            var dirHint = typeof cg.dir === 'string' && cg.dir ? cg.dir : '';
            el.textContent =
                'El agente no localiza los PHP de los procesos 1–3 en su carpeta de cronjobs.' +
                (dirHint ? ' Carpeta que revisa el agente: ' + dirHint + '.' : '') +
                ' Defina GASTOS_GC_CRONJOBS_DIR en el .env del agente (ruta absoluta a la carpeta cronjobs del backend) y reinicie Node.';
            el.classList.remove('d-none');
        } else {
            gcOcultarHintProcesosCronjobs();
        }
    }

    function gcMarcarCronjobsScriptsNoDisponibles() {
        gcCronjobsScriptsDisponibles = {
            insertar_mora_martes: false,
            detectar_gdc_liquidados: false,
            eliminar_gastos_despachos: false
        };
        gcOcultarHintProcesosCronjobs();
    }

    var ejecucionBanner = document.getElementById('gastosCobranzaEjecucionBanner');
    var switchGcAutoRun = document.getElementById('switchGcAutoRunReporte');
    var txtGcAutoRunEstado = document.getElementById('gcAutoRunEstadoTexto');
    /** Evita POST al sincronizar el switch desde /health */
    var gcAutoRunProgrammatic = false;
    var gcAutoRunPersistTimer = null;

    function gcCollectDiasChecked() {
        var diasArr = [];
        document.querySelectorAll('.gc-auto-run-dia-cb').forEach(function (el) {
            if (!el.checked) return;
            var w = parseInt(el.getAttribute('data-gc-wd'), 10);
            if (!isNaN(w)) diasArr.push(w);
        });
        diasArr.sort(function (a, b) { return a - b; });
        return diasArr;
    }

    async function gcPersistPreferenciaAutoRunYdias() {
        if (gcShellModoCartera || !switchGcAutoRun || !gcAgenteOnline) return;
        if (gcAutoRunProgrammatic) return;
        var deseado = switchGcAutoRun.checked;
        var diasArr = gcCollectDiasChecked();
        if (diasArr.length === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Días CDMX', text: 'Seleccione al menos un día de la semana.', confirmButtonColor: '#696cff' });
            } else {
                alert('Seleccione al menos un día de la semana.');
            }
            await refrescarEstado({ silencioso: true });
            return;
        }
        var diasInputs = document.querySelectorAll('.gc-auto-run-dia-cb');
        switchGcAutoRun.disabled = true;
        diasInputs.forEach(function (el) { el.disabled = true; });
        try {
            var r = await fetch('/gastoscobranza/configurarautorunreporte', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: JSON.stringify({ enabled: deseado, dias: diasArr }),
            });
            var d = await r.json().catch(function () { return {}; });
            if (!d.success) {
                await refrescarEstado({ silencioso: true });
                var msg = d.mensaje || 'No se pudo actualizar. ¿Agente actualizado (ruta /auto-run-reporte)?';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Reporte automático', text: msg, confirmButtonColor: '#696cff' });
                } else {
                    alert(msg);
                }
            } else {
                await refrescarEstado({ silencioso: true });
                if (d.auto_run_runtime_solo_memoria && typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Reporte automático',
                        text: 'El cambio quedó guardado solo en memoria del agente Node (no se pudo escribir en disco). Sigue válido hasta reiniciar el agente; para persistencia, permita escritura en gastos-cobranza-agent/data/ o en la carpeta temporal del sistema.',
                        confirmButtonColor: '#696cff',
                    });
                }
            }
        } catch (err) {
            await refrescarEstado({ silencioso: true });
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Red', text: String(err.message || err), confirmButtonColor: '#696cff' });
            } else {
                alert(String(err.message || err));
            }
        } finally {
            if (gcAgenteOnline && switchGcAutoRun) switchGcAutoRun.disabled = false;
            if (gcAgenteOnline) {
                document.querySelectorAll('.gc-auto-run-dia-cb').forEach(function (el) { el.disabled = false; });
            }
        }
    }

    function gcSchedulePersistAutoRun() {
        if (gcAutoRunPersistTimer) clearTimeout(gcAutoRunPersistTimer);
        gcAutoRunPersistTimer = setTimeout(function () {
            gcAutoRunPersistTimer = null;
            gcPersistPreferenciaAutoRunYdias();
        }, 450);
    }

    function actualizarTextoAutoRun(on) {
        if (!txtGcAutoRunEstado) return;
        txtGcAutoRunEstado.textContent = on ? 'Activo' : 'Inactivo';
        txtGcAutoRunEstado.classList.remove('text-success', 'text-danger');
        txtGcAutoRunEstado.classList.add(on ? 'text-success' : 'text-danger');
    }

    function aplicarAutoRunDesdeAgente(agente) {
        if (!switchGcAutoRun) return;
        var diasInputs = document.querySelectorAll('.gc-auto-run-dia-cb');
        if (!gcAgenteOnline || !agente || !agente.auto_run_cdmx) {
            switchGcAutoRun.disabled = true;
            diasInputs.forEach(function (el) { el.disabled = true; });
            actualizarTextoAutoRun(!!switchGcAutoRun.checked);
            return;
        }
        switchGcAutoRun.disabled = false;
        diasInputs.forEach(function (el) { el.disabled = false; });
        var arc = agente.auto_run_cdmx;
        var on = !!arc.enabled;
        var dlist = arc.dias;
        gcAutoRunProgrammatic = true;
        switchGcAutoRun.checked = on;
        diasInputs.forEach(function (el) {
            var w = parseInt(el.getAttribute('data-gc-wd'), 10);
            if (isNaN(w)) return;
            if (!dlist || !dlist.length) {
                el.checked = true;
            } else {
                el.checked = false;
                for (var i = 0; i < dlist.length; i++) {
                    if (parseInt(dlist[i], 10) === w) {
                        el.checked = true;
                        break;
                    }
                }
            }
        });
        gcAutoRunProgrammatic = false;
        actualizarTextoAutoRun(on);
    }

    function actualizarBannerEjecucion() {
        if (gcShellModoCartera) return;
        if (!ejecucionBanner) return;
        var span = ejecucionBanner.querySelector('.gc-ejec-text');
        if (!gcShellOperacionEnCurso) {
            ejecucionBanner.classList.add('d-none');
            return;
        }
        var txt = {
            reporte: 'Se está ejecutando el reporte de cobranza…',
            worker: 'Se está ejecutando el Worker EC…',
            enrich: 'Se está ejecutando el Excel enriquecido…',
            lista_negra: 'Se está ejecutando la carga a lista negra…',
            descargo: 'Se está generando el descargo estatus 3…',
            insertar_mora_martes: 'Se está ejecutando insertar moras martes…',
            detectar_gdc_liquidados: 'Se está ejecutando detectar GDC liquidados…',
            eliminar_gastos_despachos: 'Se está ejecutando eliminar gastos despachos…'
        };
        if (span) span.textContent = txt[gcShellOperacionEnCurso] || 'Operación en curso…';
        ejecucionBanner.classList.remove('d-none');
    }

    function iniciarOperacionShell(tipo) {
        gcShellOperacionEnCurso = tipo;
        if (tipo === 'worker') {
            gcCarteraWorkerUltimoPct = -1;
        }
        actualizarBannerEjecucion();
        if (gcShellModoCartera) {
            gcCarteraActividadPush(gcCarteraMapOperacionHumana(tipo), 'gc-cartera-act-line--run');
            gcCarteraActividadEstadoSet('En curso', 'bg-label-warning');
        }
        aplicarEstadoBotonesShellCompleto();
    }

    function finalizarOperacionShell() {
        gcShellOperacionEnCurso = null;
        gcCarteraWorkerUltimoPct = -1;
        actualizarBannerEjecucion();
        if (gcShellModoCartera) {
            gcCarteraActividadEstadoSet('En espera', 'bg-label-secondary');
        }
        aplicarEstadoBotonesShellCompleto();
    }

    /** codigo_salida puede venir como número o string (p. ej. según capas PHP); evita que lista negra no se dispare en servidor. */
    function normalizarCodigoSalida(data) {
        if (!data || data.codigo_salida === undefined || data.codigo_salida === null) return -1;
        var n = parseInt(String(data.codigo_salida), 10);
        return isNaN(n) ? -1 : n;
    }

    function normalizarTraceIdGc(raw) {
        var t = String(raw || '').trim();
        return /^[A-Za-z0-9._:-]{6,80}$/.test(t) ? t : '';
    }

    function generarTraceIdGc(prefijo) {
        prefijo = String(prefijo || 'gc');
        var base = Date.now().toString(36);
        var rnd = Math.random().toString(36).slice(2, 8);
        return normalizarTraceIdGc(prefijo + '-' + base + '-' + rnd) || ('gc-' + base + '-' + rnd);
    }

    function gcEcLauncherTieneExcel() {
        try {
            return !!(ecFile && ecFile.files && ecFile.files.length > 0);
        } catch (e) {
            return false;
        }
    }

    function gcListaNegraTieneExcel() {
        try {
            return !!(cargaVerifFile && cargaVerifFile.files && cargaVerifFile.files.length > 0);
        } catch (e2) {
            return false;
        }
    }

    /** Texto «Seleccionar archivo» / nombre y clases relleno en las zonas Excel de los modales. */
    function gcPintarZonaExcelCliente() {
        var face = document.getElementById('ecLauncherFileFace');
        var zone = document.getElementById('ecLauncherFileZone');
        if (face && ecFile && zone) {
            var ok = gcEcLauncherTieneExcel();
            face.textContent = ok ? (ecFile.files[0].name || 'Archivo seleccionado') : 'Seleccionar archivo';
            zone.classList.toggle('gc-excel-zone-filled', ok);
            zone.classList.toggle('gc-excel-zone-empty', !ok);
        }
        var face2 = document.getElementById('cargaVerifFileFace');
        var zone2 = document.getElementById('cargaVerifFileZone');
        if (face2 && cargaVerifFile && zone2) {
            var ok2 = gcListaNegraTieneExcel();
            face2.textContent = ok2 ? (cargaVerifFile.files[0].name || 'Archivo seleccionado') : 'Seleccionar archivo';
            zone2.classList.toggle('gc-excel-zone-filled', ok2);
            zone2.classList.toggle('gc-excel-zone-empty', !ok2);
        }
    }

    function aplicarEstadoBotonesShellCompleto() {
        var shellBloq = !!gcShellOperacionEnCurso;
        var agenteOcupadoGlobal = gcAgenteReportaEcOcupado || gcAgenteReportaCronjobsOcupado;
        var puedeEc = gcAgenteOnline && !agenteOcupadoGlobal && !shellBloq;
        /** No abrir modales de Worker/Lista negra si esta pestaña corre algo o el agente tiene EC o carga lista negra (cualquier origen). */
        var puedeAbrirModalEcLista = gcAgenteOnline && !shellBloq && !agenteOcupadoGlobal;
        if (btnAbrirModalEcWorker) btnAbrirModalEcWorker.disabled = !puedeAbrirModalEcLista;
        if (btnAbrirModalListaNegra) btnAbrirModalListaNegra.disabled = !puedeAbrirModalEcLista;
        if (btnEcLauncher) btnEcLauncher.disabled = !puedeEc || !gcEcLauncherTieneExcel();
        try {
            document.querySelectorAll('.btn-gc-worker-reporte').forEach(function (b) {
                b.disabled = !puedeEc;
            });
            document.querySelectorAll('.btn-gc-lista-negra-reporte').forEach(function (b) {
                b.disabled = !puedeEc;
            });
        } catch (e) { /* ignorar */ }

        var puedeReporte = gcAgenteOnline && !shellBloq && !agenteOcupadoGlobal;
        if (btnRun) btnRun.disabled = !puedeReporte;

        var puedeCronjobGc = gcAgenteOnline && !shellBloq && !agenteOcupadoGlobal;
        if (btnGcProcesoInsertarMoraMartes) {
            btnGcProcesoInsertarMoraMartes.disabled =
                !puedeCronjobGc || !gcCronjobsScriptsDisponibles.insertar_mora_martes;
        }
        if (btnGcProcesoDetectarLiquidados) {
            btnGcProcesoDetectarLiquidados.disabled =
                !puedeCronjobGc || !gcCronjobsScriptsDisponibles.detectar_gdc_liquidados;
        }
        if (btnGcProcesoEliminarDespachos) {
            btnGcProcesoEliminarDespachos.disabled =
                !puedeCronjobGc || !gcCronjobsScriptsDisponibles.eliminar_gastos_despachos;
        }

        if (btnListarRep) btnListarRep.disabled = shellBloq;
        if (btnHistoricoRep) btnHistoricoRep.disabled = shellBloq;
        if (ecFile) ecFile.disabled = shellBloq;
        if (ecFecha) ecFecha.disabled = shellBloq;
        if (ecCol) ecCol.disabled = shellBloq;
        if (ecOmitir) ecOmitir.disabled = shellBloq;
        if (ecEnrich) ecEnrich.disabled = shellBloq;
        if (cargaVerifFile) cargaVerifFile.disabled = shellBloq;
        if (cargaVerifEstatus) cargaVerifEstatus.disabled = shellBloq;
        if (cargaVerifMensaje) cargaVerifMensaje.disabled = shellBloq;
        var cargaVerifHeaderRow = document.getElementById('cargaVerifHeaderRow');
        if (cargaVerifHeaderRow) cargaVerifHeaderRow.disabled = shellBloq;
        if (cargaVerifDry) cargaVerifDry.disabled = shellBloq;
        if (chkDescargoSinActualizarGuia) chkDescargoSinActualizarGuia.disabled = shellBloq;

        if (shellBloq) {
            if (btnCargaVerif) btnCargaVerif.disabled = true;
            if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
        } else {
            if (btnCargaVerif) btnCargaVerif.disabled = !gcUltimoScriptCarga || !gcListaNegraTieneExcel();
            if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = !gcUltimoScriptDescargo;
        }
        var zEc = document.getElementById('ecLauncherFileZone');
        var zCv = document.getElementById('cargaVerifFileZone');
        if (zEc) zEc.classList.toggle('gc-excel-zone-shell-blocked', shellBloq);
        if (zCv) zCv.classList.toggle('gc-excel-zone-shell-blocked', shellBloq);
        gcPintarZonaExcelCliente();
        try {
            document.querySelectorAll('a.gc-rep-btn-descargar').forEach(function (a) {
                if (shellBloq) {
                    a.classList.add('disabled');
                    a.setAttribute('tabindex', '-1');
                } else {
                    a.classList.remove('disabled');
                    a.removeAttribute('tabindex');
                }
            });
        } catch (eDl) { /* ignorar */ }
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
                var nom = String(a.nombre || '').replace(/\\/g, '/');
                var base = gcNombreBaseArchivoListado(nom).toLowerCase();
                if (base !== esperado) return false;
                /* Igual que reporte_cobranza.py: solo bloquea si existe en la raíz de reporte/.
                   Copias en historico/ u otras subcarpetas no deben impedir regenerar tras borrar el oficial. */
                return nom.indexOf('/') < 0;
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

    function gcPad2(n) {
        return String(n).padStart(2, '0');
    }

    /** Nombre de archivo sin carpeta (p. ej. historico/30mar2026_a_5abr2026/archivo.xlsx). */
    function gcNombreBaseArchivoListado(nom) {
        var s = String(nom || '').replace(/\\/g, '/');
        var i = s.lastIndexOf('/');
        return i >= 0 ? s.slice(i + 1) : s;
    }

    /** { y, m, d } calendario Ciudad de México desde ISO de modificación del agente. */
    function gcFechaModificadoCdmxYmd(iso) {
        if (!iso) return null;
        var d = new Date(iso);
        if (isNaN(d.getTime())) return null;
        try {
            var parts = new Intl.DateTimeFormat('en-CA', {
                timeZone: 'America/Mexico_City',
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            }).formatToParts(d);
            var y = '';
            var m = '';
            var day = '';
            parts.forEach(function (p) {
                if (p.type === 'year') y = p.value;
                if (p.type === 'month') m = p.value;
                if (p.type === 'day') day = p.value;
            });
            if (!y || !m || !day) return null;
            return { y: parseInt(y, 10), m: parseInt(m, 10), d: parseInt(day, 10) };
        } catch (e3) {
            return null;
        }
    }

    /** Fecha en nombre reporte_cobranza_DD-MM-YYYY.xlsx → { y, m, d } (calendario CDMX del reporte). */
    function gcParseNombreReporteCobranza(nom) {
        var base = gcNombreBaseArchivoListado(nom);
        var m = /^reporte_cobranza_(\d{2})-(\d{2})-(\d{4})\.xlsx$/i.exec(base);
        if (!m) return null;
        var dd = parseInt(m[1], 10);
        var mm = parseInt(m[2], 10);
        var yy = parseInt(m[3], 10);
        if (mm < 1 || mm > 12 || dd < 1 || dd > 31) return null;
        return { y: yy, m: mm, d: dd };
    }

    function gcAddDaysYmd(y, m, d, delta) {
        var t = Date.UTC(y, m - 1, d) + delta * 86400000;
        var x = new Date(t);
        return { y: x.getUTCFullYear(), m: x.getUTCMonth() + 1, d: x.getUTCDate() };
    }

    /**
     * 0 = lunes … 6 = domingo para fecha civil gregoriana (y, m, d).
     * Sin depender de Intl: evita fallback erróneo a lunes=0 (p. ej. reporte 01-04-2026
     * quedaba con clave 2026-04-01 y salía del listado de la semana 30 mar – 5 abr).
     */
    function gcCdmxWeekdayMon0(y, m, d) {
        var t = [0, 3, 2, 5, 0, 3, 5, 1, 4, 6, 2, 4];
        var Y = m < 3 ? y - 1 : y;
        var wSun0 = (Y + Math.floor(Y / 4) - Math.floor(Y / 100) + Math.floor(Y / 400) + t[m - 1] + d) % 7;
        return (wSun0 + 6) % 7;
    }

    function gcLunesSemanaCdmx(y, m, d) {
        var k = gcCdmxWeekdayMon0(y, m, d);
        return gcAddDaysYmd(y, m, d, -k);
    }

    function gcClaveLunesYmd(L) {
        return L.y + '-' + gcPad2(L.m) + '-' + gcPad2(L.d);
    }

    function gcHoyCdmxYmd() {
        var s = fechaCalendarioCdmxYmd();
        var p = s.split('-');
        return { y: parseInt(p[0], 10), m: parseInt(p[1], 10), d: parseInt(p[2], 10) };
    }

    function gcClaveSemanaActualCdmx() {
        var h = gcHoyCdmxYmd();
        return gcClaveLunesYmd(gcLunesSemanaCdmx(h.y, h.m, h.d));
    }

    /** Lunes de la semana calendario anterior a la actual (CDMX). */
    function gcClaveSemanaPasadaCdmx() {
        var actual = gcClaveSemanaActualCdmx();
        var p = actual.split('-').map(function (x) { return parseInt(x, 10); });
        var prev = gcAddDaysYmd(p[0], p[1], p[2], -7);
        return gcClaveLunesYmd(prev);
    }

    function gcFechaReferenciaSemanaArchivo(a) {
        var pn = gcParseNombreReporteCobranza(a.nombre);
        if (pn) return pn;
        var base = gcNombreBaseArchivoListado(a.nombre);
        if (/^reporte_cobranza_/i.test(base)) {
            var fm = gcFechaModificadoCdmxYmd(a.modificado);
            if (fm) return fm;
        }
        return gcFechaModificadoCdmxYmd(a.modificado);
    }

    function gcClaveLunesSemanaDeArchivo(a) {
        var f = gcFechaReferenciaSemanaArchivo(a);
        if (!f) return gcClaveSemanaActualCdmx();
        return gcClaveLunesYmd(gcLunesSemanaCdmx(f.y, f.m, f.d));
    }

    function gcEtiquetaRangoSemana(claveLunes) {
        var p = claveLunes.split('-').map(function (x) { return parseInt(x, 10); });
        if (p.length !== 3 || p.some(isNaN)) return claveLunes;
        var L = { y: p[0], m: p[1], d: p[2] };
        var D = gcAddDaysYmd(L.y, L.m, L.d, 6);
        try {
            var opt = { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'UTC' };
            var s1 = new Date(Date.UTC(L.y, L.m - 1, L.d)).toLocaleDateString('es-MX', opt);
            var s2 = new Date(Date.UTC(D.y, D.m - 1, D.d)).toLocaleDateString('es-MX', opt);
            return s1 + ' – ' + s2;
        } catch (e4) {
            return claveLunes;
        }
    }

    function gcActualizarHintSemanaActual() {
        if (!hintSemanaActual) return;
        var cl = gcClaveSemanaActualCdmx();
        hintSemanaActual.textContent = 'Semana actual (lun–dom, Ciudad de México): ' + gcEtiquetaRangoSemana(cl);
    }

    function escCeldaReporte(s) {
        return String(s)
            .split('&').join('&amp;').split('<').join('&lt;').split('>').join('&gt;').split('"').join('&quot;');
    }

    function esExcelReporteCobranza(nom) {
        return /^reporte_cobranza_/i.test(gcNombreBaseArchivoListado(nom));
    }

    /**
     * HTML de filas de la tabla de reportes.
     * @param {{ soloDescargar?: boolean }} [opts] si soloDescargar, solo enlace de descarga (p. ej. modal Histórico).
     */
    function htmlFilasTablaReportes(list, opts) {
        opts = opts || {};
        var soloDesc = !!opts.soloDescargar;
        return list.map(function (a) {
            var nom = String(a.nombre || '');
            var safe = escCeldaReporte(nom);
            var href = '/gastoscobranza/descargarReporte?nombre=' + encodeURIComponent(nom);
            var estRaw = (a.estado != null && String(a.estado).trim() !== '') ? String(a.estado).trim() : '';
            var estHtml = estRaw ? escCeldaReporte(estRaw) : '';
            var estTip = (a.estadoDetalle != null && String(a.estadoDetalle).trim() !== '')
                ? escCeldaReporte(String(a.estadoDetalle).trim()) : '';
            var estTdAttr = estTip ? ' title="' + estTip + '"' : '';
            var estSubCls = claseEstadoReporteCarpeta(estRaw);
            if (estRaw && !estSubCls) estSubCls = 'gc-rep-est-otro';
            var estInner = estRaw
                ? '<span class="gc-rep-estado ' + estSubCls + '">' + estHtml + '</span>'
                : '<span class="gc-rep-estado gc-rep-est-vacio">—</span>';
            var diaSem = escCeldaReporte(diaSemanaModificadoCdmx(a.modificado, a.diaSemanaModificado));
            var esRep = esExcelReporteCobranza(nom);
            var btnWorker = !soloDesc && esRep
                ? '<button type="button" class="btn btn-sm btn-gc-worker-reporte gc-rep-btn-worker ms-1" data-nombre-enc="' +
                    encodeURIComponent(nom) + '" title="Worker S2: procesa este reporte con el agente (misma lógica que el modal EC Worker). Actualiza Gastos Cobranza en BD cuando Cartera confirmó el criterio en S2; si termina bien, la lista negra corre sola con el mismo Excel.">' +
                    '<i class="fa fa-cogs" aria-hidden="true"></i><span class="visually-hidden"> Worker S2</span></button>'
                : '';
            var btnListaNegra = !soloDesc && esRep
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
    }

    function gcFiltrarArchivosPorClaveLunes(list, claveLunes) {
        return list.filter(function (a) {
            return gcClaveLunesSemanaDeArchivo(a) === claveLunes;
        });
    }

    function gcPintarTablaPrincipalDesdeCache() {
        if (!tbodyRep) return;
        gcActualizarHintSemanaActual();
        var actual = gcClaveSemanaActualCdmx();
        var filtrados = gcFiltrarArchivosPorClaveLunes(gcCacheArchivosReporte, actual);
        if (!gcCacheArchivosReporte.length) {
            tbodyRep.innerHTML = gcShellModoCartera
                ? '<tr><td colspan="6" class="text-muted small">Aún no hay reportes en la carpeta de esta semana. Cuando el área correspondiente genere un archivo, podrá descargarlo aquí.</td></tr>'
                : '<tr><td colspan="6" class="text-muted small">Aún no hay archivos .xlsx en <code>reporte/</code>. Ejecuta el reporte para generar uno.</td></tr>';
        } else if (!filtrados.length) {
            tbodyRep.innerHTML = gcShellModoCartera
                ? '<tr><td colspan="6" class="text-muted small">No hay archivos en la semana en curso (lun–dom, Ciudad de México). Los de semanas anteriores siguen en el servidor: use <strong>Histórico</strong>.</td></tr>'
                : '<tr><td colspan="6" class="text-muted small">No hay archivos en la semana en curso (lun–dom, Ciudad de México). Los reportes de semanas anteriores siguen en el servidor: use <strong>Histórico</strong>.</td></tr>';
        } else {
            tbodyRep.innerHTML = htmlFilasTablaReportes(filtrados, { soloDescargar: gcShellModoCartera });
        }
        aplicarEstadoBotonesShellCompleto();
    }

    /** true si la ruta del listado está bajo reporte/historico/… */
    function gcArchivoListadoEnHistorico(nom) {
        var s = String(nom || '').replace(/\\/g, '/');
        return s.indexOf('historico/') !== -1;
    }

    /**
     * Rellena el desplegable de semanas del modal Histórico.
     * Incluye la semana actual si hay .xlsx ya archivados en historico/ (p. ej. movidos por error).
     * @returns {string[]} claves de lunes ordenadas de más reciente a más antigua
     */
    function gcPoblarSelectSemanasHistoricas() {
        if (!selHistoricoSemana) return [];
        var actual = gcClaveSemanaActualCdmx();
        var seen = {};
        gcCacheArchivosReporte.forEach(function (a) {
            var c = gcClaveLunesSemanaDeArchivo(a);
            if (!c) return;
            var enHist = gcArchivoListadoEnHistorico(a.nombre);
            if (enHist) {
                seen[c] = true;
            } else if (c !== actual) {
                seen[c] = true;
            }
        });
        var claves = Object.keys(seen).sort().reverse();
        var prev = selHistoricoSemana.value;
        selHistoricoSemana.innerHTML = '<option value="">— Elija una semana —</option>';
        claves.forEach(function (c) {
            var opt = document.createElement('option');
            opt.value = c;
            opt.textContent = gcEtiquetaRangoSemana(c) + ' (' + c + ')';
            selHistoricoSemana.appendChild(opt);
        });
        if (prev && claves.indexOf(prev) >= 0) {
            selHistoricoSemana.value = prev;
        }
        return claves;
    }

    function gcPintarTablaHistorico() {
        if (!tbodyRepHist || !selHistoricoSemana) return;
        var c = selHistoricoSemana.value;
        if (!c) {
            tbodyRepHist.innerHTML = '<tr><td colspan="6" class="text-muted small">Seleccione una semana.</td></tr>';
            return;
        }
        var filtrados = gcFiltrarArchivosPorClaveLunes(gcCacheArchivosReporte, c);
        if (!filtrados.length) {
            tbodyRepHist.innerHTML = '<tr><td colspan="6" class="text-muted small">Sin archivos en esa semana.</td></tr>';
            return;
        }
        tbodyRepHist.innerHTML = htmlFilasTablaReportes(filtrados, { soloDescargar: true });
        aplicarEstadoBotonesShellCompleto();
    }

    function gcAbrirModalHistoricoReportes() {
        var el = document.getElementById('modalHistoricoReportesGc');
        if (!el) return;
        var claves = gcPoblarSelectSemanasHistoricas();
        if (selHistoricoSemana && claves.length > 0) {
            selHistoricoSemana.value = claves[0];
        }
        gcPintarTablaHistorico();
        try {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                bootstrap.Modal.getOrCreateInstance(el).show();
            }
        } catch (eModal) { /* ignorar */ }
    }

    async function traerListaReportes() {
        try {
            var r = await fetch('/gastoscobranza/listarReportes', {
                method: 'GET',
                headers: { 'Front-Request': 'true' }
            });
            var data = await r.json();
            if (!data.success || !data.archivos) {
                gcCacheArchivosReporte = [];
                if (tbodyRep) {
                    tbodyRep.innerHTML = '<tr><td colspan="6" class="text-warning small">' +
                        (data.mensaje || 'No se pudo listar reportes.') + '</td></tr>';
                }
                aplicarEstadoBotonesShellCompleto();
                return;
            }
            gcCacheArchivosReporte = data.archivos;
            gcPoblarSelectSemanasHistoricas();
            gcPintarTablaPrincipalDesdeCache();
        } catch (e) {
            gcCacheArchivosReporte = [];
            if (tbodyRep) {
                tbodyRep.innerHTML = '<tr><td colspan="6" class="text-danger small">' + String(e.message || e) + '</td></tr>';
            }
            aplicarEstadoBotonesShellCompleto();
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
        if (!logPanel) return;
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
                logPanel.value = data.contenido.length ? data.contenido : '(log vacío todavía)';
            } else {
                logPanel.value = data.mensaje || 'No se pudo leer el log (¿agente caído?).';
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
            gcCarteraSincronizarProgresoWorkerDesdeLog();
        } catch (e) {
            logPanel.value = String(e.message || e);
        }
    }

    function copiarLogAlPortapapeles() {
        if (!logPanel) return;
        var t = logPanel.value || '';
        function feedbackCopiado() {
            if (!btnLogCopiar) return;
            var prev = btnLogCopiar.innerHTML;
            btnLogCopiar.innerHTML = '<i class="fa fa-check me-1"></i>Copiado';
            setTimeout(function () { btnLogCopiar.innerHTML = prev; }, 2000);
        }
        function copiarSeleccionManual() {
            try {
                logPanel.focus();
                logPanel.select();
                logPanel.setSelectionRange(0, t.length);
                if (document.execCommand('copy')) {
                    feedbackCopiado();
                } else {
                    alertar('Copiar log', 'Seleccione el texto en el área del log y use Ctrl+C.', 'info');
                }
            } catch (e) {
                alertar('Copiar log', 'Seleccione el texto en el área del log y use Ctrl+C.', 'info');
            }
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(t).then(feedbackCopiado).catch(copiarSeleccionManual);
        } else {
            copiarSeleccionManual();
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
        if (!logPanel) return;
        if (ivLogEcWorker) clearInterval(ivLogEcWorker);
        traerLog(380, { scrollBottom: 'auto' });
        ivLogEcWorker = setInterval(function () {
            traerLog(380, { scrollBottom: 'auto' });
        }, 2500);
    }

    function detenerLogRapidoEcWorker() {
        if (ivLogEcWorker) {
            clearInterval(ivLogEcWorker);
            ivLogEcWorker = null;
        }
    }

    /** Cierra el modal EC Worker / Excel enriquecido al arrancar la corrida (banner + log siguen visibles). */
    function cerrarModalEcWorker() {
        try {
            var mEl = document.getElementById('modalGcEcWorker');
            if (!mEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
            var inst = bootstrap.Modal.getInstance(mEl);
            if (inst) inst.hide();
        } catch (eCerr) { /* ignorar */ }
    }

    /** Cierra el modal de lista negra al invocar la carga vía agente (todos los orígenes). */
    function cerrarModalListaNegra() {
        try {
            var mEl = document.getElementById('modalGcListaNegra');
            if (!mEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
            var inst = bootstrap.Modal.getInstance(mEl);
            if (inst) inst.hide();
        } catch (eLn) { /* ignorar */ }
    }

    function construirPayloadCargaVerificacion(nombreArchivo, cargaOpts) {
        cargaOpts = cargaOpts || {};
        var traceId = normalizarTraceIdGc(cargaOpts.traceId) || generarTraceIdGc('ln');
        var payload = {
            archivo: nombreArchivo,
            dryRun: !!(cargaVerifDry && cargaVerifDry.checked),
            estatus: cargaVerifEstatus ? parseInt(cargaVerifEstatus.value, 10) : 2,
            tipoReporteNulo: true,
            megaPhpDefaults: true,
            traceId: traceId
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
        cerrarModalListaNegra();
        cargaOpts = cargaOpts || {};
        if (!normalizarTraceIdGc(cargaOpts.traceId)) {
            cargaOpts.traceId = generarTraceIdGc('ln');
        }
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
        cargaOpts = cargaOpts || {};
        var traceId = normalizarTraceIdGc(payloadEc && payloadEc.traceId) || generarTraceIdGc('wln');
        if (payloadEc) payloadEc.traceId = traceId;
        if (!normalizarTraceIdGc(cargaOpts.traceId)) {
            cargaOpts.traceId = traceId;
        }
        iniciarOperacionShell(payloadEc.tipo === 'enrich' ? 'enrich' : 'worker');
        comenzarLogRapidoEcWorker();
        cerrarModalEcWorker();
        try {
            var r2;
            try {
                r2 = await fetch('/gastoscobranza/ejecutarEcLauncher', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                    body: JSON.stringify(payloadEc)
                });
            } catch (eFetch) {
                if (gcShellModoCartera) {
                    gcCarteraActividadPush('No hubo conexión con el servidor. Intente de nuevo.', 'gc-cartera-act-line--err');
                }
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
                if (gcShellModoCartera) {
                    gcCarteraActividadPush('Respuesta no válida del servidor (¿sesión vencida?).', 'gc-cartera-act-line--err');
                }
                alertar('Error',
                    'El servidor no devolvió JSON en worker/enrich. Suele ser sesión caducada o error PHP. ' +
                    'Lista negra NO se ejecutó. Inicio de respuesta: ' +
                    String(raw).slice(0, 200).replace(/\s+/g, ' '), 'error');
                return;
            }
            var ok = !!data.success;
            var msg = data.mensaje || (ok ? 'Proceso EC terminado.' : 'Error en worker / enrich.');
            var codigoSalida = normalizarCodigoSalida(data);
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
                if (gcShellModoCartera) {
                    gcCarteraActividadPush('Conciliación aplicada. Actualizando verificación semana…', 'gc-cartera-act-line--run');
                }
                await traerListaReportes();
                var nombreExcelListaNegra = (data.archivo && String(data.archivo).trim())
                    ? String(data.archivo).trim()
                    : payloadEc.nombre;
                var dataCarga;
                try {
                    dataCarga = await invocarCargaVerificacionAgente(nombreExcelListaNegra, cargaOpts);
                } catch (eCarga) {
                    if (gcShellModoCartera) {
                        gcCarteraActividadPush('Conciliación lista, pero no se pudo aplicar verificación semana (red o servidor).', 'gc-cartera-act-line--warn');
                    }
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
                    if (gcShellModoCartera) {
                        gcCarteraActividadPush(
                            codigoSalida === 0
                                ? 'Verificación semana actualizada correctamente.'
                                : 'Verificación semana actualizada con advertencias (revise el aviso en pantalla).',
                            codigoSalida === 0 ? 'gc-cartera-act-line--ok' : 'gc-cartera-act-line--warn'
                        );
                    }
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
                    if (gcShellModoCartera) {
                        gcCarteraActividadPush('La conciliación terminó, pero falló el paso de verificación semana.', 'gc-cartera-act-line--warn');
                    }
                    alertar('Worker ok — Lista negra FALLÓ',
                        'El worker terminó (código ' + codigoSalida + ') pero la carga a lista negra tuvo un error: ' +
                        (dataCarga.mensaje || 'sin detalle') +
                        '.\nArchivo enviado a carga: «' + nombreExcelListaNegra + '».' +
                        '\nPuedes intentarla manualmente con el botón morado en la tabla de reportes.' +
                        lineaEstadoReporteRespuesta(dataCarga.estado_reporte),
                        'warning');
                }
            } else if (esWorker) {
                /* Worker falló antes de terminar → lista negra no se intentó */
                if (gcShellModoCartera) {
                    gcCarteraActividadPush('La conciliación no pudo completarse. Revise el mensaje en pantalla.', 'gc-cartera-act-line--err');
                }
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
            finalizarOperacionShell();
        }
    }

    async function refrescarEstado(opts) {
        opts = opts || {};
        var sil = !!opts.silencioso;
        if (!sil) {
            if (badge) {
                badge.className = 'badge bg-label-warning';
                badge.textContent = 'Comprobando…';
            }
            if (detalle) detalle.textContent = '';
            gcOcultarHintProcesosCronjobs();
            if (btnRun) btnRun.disabled = true;
            if (btnGcProcesoInsertarMoraMartes) btnGcProcesoInsertarMoraMartes.disabled = true;
            if (btnGcProcesoDetectarLiquidados) btnGcProcesoDetectarLiquidados.disabled = true;
            if (btnGcProcesoEliminarDespachos) btnGcProcesoEliminarDespachos.disabled = true;
            if (btnEcLauncher) btnEcLauncher.disabled = true;
            if (btnAbrirModalEcWorker) btnAbrirModalEcWorker.disabled = true;
            if (btnAbrirModalListaNegra) btnAbrirModalListaNegra.disabled = true;
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
                gcAgenteReportaCronjobsOcupado = false;
                gcUltimoScriptCarga = false;
                gcUltimoScriptDescargo = false;
                gcMarcarCronjobsScriptsNoDisponibles();
                if (badge) {
                    badge.className = 'badge bg-label-danger';
                    badge.textContent = 'Error';
                }
                if (detalle) detalle.textContent = data.mensaje || 'Error';
                if (gcShellModoCartera) {
                    gcCarteraPushConexionLimitado('No se pudo verificar el servicio. Intente de nuevo en unos segundos.', 'gc-cartera-act-line--err');
                    gcCarteraActividadEstadoSet('Sin servicio', 'bg-label-danger');
                }
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
                aplicarAutoRunDesdeAgente(null);
                aplicarEstadoBotonesShellCompleto();
                return;
            }
            if (!data.agente_configurado) {
                gcAgenteOnline = false;
                gcAgenteReportaEcOcupado = false;
                gcAgenteReportaCronjobsOcupado = false;
                gcUltimoScriptCarga = false;
                gcUltimoScriptDescargo = false;
                gcMarcarCronjobsScriptsNoDisponibles();
                if (badge) {
                    badge.className = 'badge bg-label-secondary';
                    badge.textContent = 'INI desactivado';
                }
                if (detalle) detalle.innerHTML = data.detalle || '';
                gcCacheArchivosReporte = [];
                if (tbodyRep) {
                    tbodyRep.innerHTML = gcShellModoCartera
                        ? '<tr><td colspan="6" class="text-muted small">El servicio de conciliación no está disponible en este momento. Si el inconveniente continúa, contacte a sistemas.</td></tr>'
                        : '<tr><td colspan="6" class="text-muted small">Habilite el agente en <code>config.ini</code> para listar reportes.</td></tr>';
                }
                if (tbodyRepHist) {
                    tbodyRepHist.innerHTML = '<tr><td colspan="6" class="text-muted small">—</td></tr>';
                }
                gcActualizarHintSemanaActual();
                if (gcShellModoCartera) {
                    gcCarteraPushConexionLimitado('Servicio no disponible. Contacte a sistemas si necesita acceso.', 'gc-cartera-act-line--warn');
                    gcCarteraActividadEstadoSet('No disponible', 'bg-label-secondary');
                }
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
                aplicarAutoRunDesdeAgente(null);
                aplicarEstadoBotonesShellCompleto();
                return;
            }
            if (data.agente_online) {
                gcAgenteOnline = true;
                gcCarteraResetAvisosConexion();
                if (badge) {
                    badge.className = 'badge bg-label-success';
                    badge.textContent = 'Agente en línea';
                }
                var a = data.agente || {};
                gcUltimoScriptCarga = !!a.script_carga_verificacion_semana;
                gcUltimoScriptDescargo = !!a.script_descargo_estatus3;
                gcAgenteReportaEcOcupado = !!(a.ec_launcher_ocupado || a.carga_verificacion_semana_ocupado);
                gcAgenteReportaCronjobsOcupado = !!(a.cronjobs_gc_ocupado || (a.cronjobs_gc && a.cronjobs_gc.busy));
                gcAplicarScriptsCronjobsDesdeAgente(a);
                if (gcShellModoCartera) {
                    if (gcShellOperacionEnCurso) {
                        if (gcShellOperacionEnCurso === 'worker') {
                            gcCarteraSincronizarProgresoWorkerDesdeLog();
                            if (gcCarteraWorkerUltimoPct < 0) {
                                gcCarteraActividadEstadoSet('En curso', 'bg-label-warning');
                            }
                        } else {
                            gcCarteraActividadEstadoSet('En curso', 'bg-label-warning');
                        }
                    } else if (gcAgenteReportaEcOcupado) {
                        gcCarteraActividadEstadoSet('Ocupado', 'bg-label-warning');
                        if (gcCarteraUltimoAvisoRemoto !== 'remoto_ec') {
                            gcCarteraActividadPush('Hay conciliación o carga a lista negra en curso (otra ventana o usuario). Espere un momento.', 'gc-cartera-act-line--warn');
                            gcCarteraUltimoAvisoRemoto = 'remoto_ec';
                        }
                    } else {
                        gcCarteraUltimoAvisoRemoto = '';
                        gcCarteraActividadEstadoSet('En espera', 'bg-label-secondary');
                    }
                } else if (detalle) {
                    if (gcAgenteReportaEcOcupado) {
                        detalle.innerHTML = '<span class="text-warning"><i class="fa fa-spinner fa-spin me-1" aria-hidden="true"></i><strong>Worker EC o lista negra en ejecución</strong> — espere a que termine antes de lanzar otro.</span>';
                    } else if (gcAgenteReportaCronjobsOcupado) {
                        detalle.innerHTML = '<span class="text-warning"><i class="fa fa-spinner fa-spin me-1" aria-hidden="true"></i><strong>Proceso GC en ejecución</strong> — espere a que termine antes de lanzar otro.</span>';
                    } else {
                        detalle.textContent = '';
                    }
                }
                aplicarAutoRunDesdeAgente(a);
                aplicarEstadoBotonesShellCompleto();
                if (!sil) {
                    traerLog(400, { scrollBottom: true });
                    traerListaReportes();
                }
            } else {
                gcAgenteOnline = false;
                gcAgenteReportaEcOcupado = false;
                gcAgenteReportaCronjobsOcupado = false;
                gcUltimoScriptCarga = false;
                gcUltimoScriptDescargo = false;
                gcMarcarCronjobsScriptsNoDisponibles();
                if (badge) {
                    badge.className = 'badge bg-label-danger';
                    badge.textContent = 'Sin conexión';
                }
                if (detalle) detalle.textContent = data.detalle || '';
                if (logPanel) {
                    logPanel.value = 'Levante el agente (npm start en gastos-cobranza-agent, puerto 3120).';
                }
                gcCacheArchivosReporte = [];
                if (tbodyRep) {
                    tbodyRep.innerHTML = gcShellModoCartera
                        ? '<tr><td colspan="6" class="text-muted small">Sin conexión con el servicio. Intente más tarde o verifique su red.</td></tr>'
                        : '<tr><td colspan="6" class="text-muted small">Agente fuera de línea — sin listado.</td></tr>';
                }
                if (tbodyRepHist) {
                    tbodyRepHist.innerHTML = '<tr><td colspan="6" class="text-muted small">—</td></tr>';
                }
                gcActualizarHintSemanaActual();
                if (gcShellModoCartera) {
                    gcCarteraPushConexionLimitado('Sin conexión con el servicio de conciliación.', 'gc-cartera-act-line--warn');
                    gcCarteraActividadEstadoSet('Sin conexión', 'bg-label-danger');
                }
                if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
                aplicarAutoRunDesdeAgente(null);
                aplicarEstadoBotonesShellCompleto();
            }
        } catch (e) {
            gcAgenteOnline = false;
            gcAgenteReportaEcOcupado = false;
            gcAgenteReportaCronjobsOcupado = false;
            gcUltimoScriptCarga = false;
            gcUltimoScriptDescargo = false;
            gcMarcarCronjobsScriptsNoDisponibles();
            if (badge) {
                badge.className = 'badge bg-label-danger';
                badge.textContent = 'Error red';
            }
            if (detalle) detalle.textContent = String(e.message || e);
            if (gcShellModoCartera) {
                gcCarteraPushConexionLimitado('Error de red al consultar el servicio.', 'gc-cartera-act-line--err');
                gcCarteraActividadEstadoSet('Error', 'bg-label-danger');
            }
            if (btnDescargoEstatus3) btnDescargoEstatus3.disabled = true;
            aplicarAutoRunDesdeAgente(null);
            aplicarEstadoBotonesShellCompleto();
        }
    }

    function setDescargoDescargando(enCurso) {
        if (descargoSpinner) descargoSpinner.classList.toggle('d-none', !enCurso);
    }

    async function ejecutarDescargoEstatus3Flujo() {
        iniciarOperacionShell('descargo');
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
            finalizarOperacionShell();
            refrescarEstado();
        }
    }

    function gcEcFechaCorteParaWorker() {
        if (gcShellModoCartera) return fechaCalendarioCdmxYmd();
        if (ecFecha && ecFecha.value) return ecFecha.value;
        return fechaCalendarioCdmxYmd();
    }

    function gcEcTipoWorkerOEnrich() {
        if (gcShellModoCartera) return 'worker';
        return (ecEnrich && ecEnrich.checked) ? 'enrich' : 'worker';
    }

    document.getElementById('btnConfigCorreosGastosCobranza')?.addEventListener('click', async function () {
        var parseEmails = function (raw) {
            return (raw || '').split(/[,\s;]+/).map(function (v) { return v.trim().toLowerCase(); }).filter(Boolean);
        };
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var actuales = [];
        try {
            var r = await fetch('/gastoscobranza/getDestinatariosCorreo', { method: 'POST', headers: { 'Front-Request': 'true' } });
            var d = await r.json();
            if (!d || !d.success) throw new Error(d && d.mensaje ? d.mensaje : 'No se pudo cargar la configuración.');
            var arr = Array.isArray(d.datos && d.datos.destinatarios) ? d.datos.destinatarios : [];
            actuales = arr.map(function (v) {
                return (typeof v === 'string') ? { email: String(v || '').trim(), activo: true } : v;
            }).map(function (v) {
                return { email: String(v && v.email ? v.email : '').trim().toLowerCase(), activo: v && v.activo !== false };
            }).filter(function (v) { return v.email; });
        } catch (e) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Correos', text: e && e.message ? e.message : 'No se pudo cargar la lista.' });
            } else {
                window.alert('Correos: ' + (e && e.message ? e.message : ''));
            }
            return;
        }

        if (typeof Swal === 'undefined') {
            var txt = window.prompt('Correos Gastos Cobranza (separados por coma):', actuales.map(function (v) { return v.email; }).join(', ')) || '';
            var lista = [];
            try { lista = parseEmails(txt); } catch (e2) { return; }
            if (!lista.length) return;
            var invalidos = lista.filter(function (e) { return !emailRegex.test(e); });
            if (invalidos.length) return;
            await fetch('/gastoscobranza/setDestinatariosCorreo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: JSON.stringify({ destinatarios: lista.map(function (email) { return { email: email, activo: true }; }) })
            });
            return;
        }

        var res = await Swal.fire({
            title: 'Administrar correos',
            html: '<p class="text-muted small mb-3 mt-0 text-center">(Gastos de cobranza)</p>' +
                '<div class="text-start">' +
                '<label class="form-label mb-2 fw-semibold">Destinatarios</label>' +
                '<div id="swal-gc-correos-admin-lista" class="rounded-3 border bg-light p-2 overflow-auto"></div>' +
                '<div class="input-group input-group-sm mt-3">' +
                '<input id="swal-gc-correos-admin-new" class="form-control" placeholder="nuevo@dominio.com">' +
                '<button id="swal-gc-correos-admin-add" type="button" class="btn btn-primary">' +
                '<i class="fa fa-plus me-1"></i>Agregar</button></div>' +
                '<div id="swal-gc-correos-admin-preview" class="small mt-2"></div></div>',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            didOpen: function () {
                var listaEl = document.getElementById('swal-gc-correos-admin-lista');
                var inNew = document.getElementById('swal-gc-correos-admin-new');
                var btnAdd = document.getElementById('swal-gc-correos-admin-add');
                var pv = document.getElementById('swal-gc-correos-admin-preview');
                var items = actuales.slice();
                var render = function () {
                    if (!listaEl) return;
                    if (!items.length) {
                        listaEl.innerHTML = '<div class="text-muted small py-2 px-1">Sin correos. Agrega uno para comenzar.</div>';
                    } else {
                        listaEl.innerHTML = items.map(function (it, ix) {
                            return '<div class="d-flex align-items-center gap-2 py-2 px-2 mb-1 rounded-2 border bg-white">' +
                                '<div class="form-check form-switch m-0 flex-grow-1 d-flex align-items-center gap-2">' +
                                '<input class="form-check-input me-1" type="checkbox" role="switch" id="gcmail_' + ix + '" ' +
                                (it.activo ? 'checked' : '') + ' data-ix="' + ix + '">' +
                                '<label class="form-check-label small fw-medium ' + (it.activo ? '' : 'text-muted') + '" for="gcmail_' + ix + '">' + it.email + '</label>' +
                                '<span class="badge ' + (it.activo ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary') + ' ms-2">' +
                                (it.activo ? 'Activo' : 'Inactivo') + '</span></div>' +
                                '<button type="button" class="btn btn-sm btn-outline-danger" data-del="' + ix + '" title="Quitar">' +
                                '<i class="fa fa-trash"></i></button></div>';
                        }).join('');
                    }
                    refreshPreview();
                };
                var refreshPreview = function () {
                    if (!pv) return;
                    var total = items.length;
                    var activos = items.filter(function (x) { return x.activo; }).length;
                    if (!total) {
                        pv.innerHTML = '<span class="text-warning fw-semibold">Debes agregar al menos un correo.</span>';
                        return;
                    }
                    if (!activos) {
                        pv.innerHTML = '<span class="text-warning fw-semibold">Activa al menos un correo para envío.</span>';
                        return;
                    }
                    pv.innerHTML = '<span class="badge bg-success-subtle text-success">Activos: ' + activos + '</span> ' +
                        '<span class="badge bg-primary-subtle text-primary ms-1">Total: ' + total + '</span>';
                };
                listaEl.addEventListener('click', function (ev) {
                    var btnDel = ev.target && ev.target.closest ? ev.target.closest('[data-del]') : null;
                    var del = btnDel && btnDel.getAttribute ? btnDel.getAttribute('data-del') : null;
                    if (del == null) return;
                    var i = parseInt(del, 10);
                    if (!isFinite(i)) return;
                    items.splice(i, 1);
                    render();
                });
                listaEl.addEventListener('change', function (ev) {
                    var t = ev.target;
                    if (!t || !t.getAttribute) return;
                    var ix = parseInt(t.getAttribute('data-ix'), 10);
                    if (!isFinite(ix) || !items[ix]) return;
                    items[ix].activo = !!t.checked;
                    render();
                });
                btnAdd.addEventListener('click', function () {
                    var email = String(inNew && inNew.value ? inNew.value : '').trim().toLowerCase();
                    if (!email) return;
                    if (!emailRegex.test(email)) {
                        if (pv) pv.innerHTML = '<span class="text-danger">Correo inválido: ' + email + '</span>';
                        return;
                    }
                    if (items.some(function (x) { return x.email === email; })) {
                        if (pv) pv.innerHTML = '<span class="text-warning">Ese correo ya existe.</span>';
                        return;
                    }
                    items.push({ email: email, activo: true });
                    if (inNew) inNew.value = '';
                    render();
                });
                inNew.addEventListener('keydown', function (ev) {
                    if (ev.key === 'Enter') {
                        ev.preventDefault();
                        btnAdd.click();
                    }
                });
                window.__gcMailItemsRef = function () { return items; };
                render();
            },
            preConfirm: function () {
                var itemsFn = window.__gcMailItemsRef;
                var items = (typeof itemsFn === 'function') ? itemsFn() : [];
                if (!Array.isArray(items) || !items.length) {
                    Swal.showValidationMessage('Debes agregar al menos un correo.');
                    return false;
                }
                var invalidos = items.map(function (x) { return x.email; }).filter(function (e) { return !emailRegex.test(e); });
                if (invalidos.length) {
                    Swal.showValidationMessage('Corrige correos inválidos: ' + invalidos.join(', '));
                    return false;
                }
                var activos = items.filter(function (x) { return x.activo; });
                if (!activos.length) {
                    Swal.showValidationMessage('Activa al menos un correo.');
                    return false;
                }
                return { destinatarios: items };
            }
        });
        if (!res.isConfirmed || !res.value) return;

        try {
            var r2 = await fetch('/gastoscobranza/setDestinatariosCorreo', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: JSON.stringify({ destinatarios: res.value.destinatarios })
            });
            var d2 = await r2.json();
            if (!d2 || !d2.success) throw new Error(d2 && d2.mensaje ? d2.mensaje : 'No se pudo guardar.');
            Swal.fire({ icon: 'success', title: 'Guardado', text: 'Destinatarios actualizados correctamente.' });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e && e.message ? e.message : 'No se pudo guardar la lista.' });
        } finally {
            try { delete window.__gcMailItemsRef; } catch (e3) {}
        }
    });

    async function ejecutarEcLauncherFlujo() {
        if (!ecFile || !ecFile.files || !ecFile.files[0]) {
            alertar('Falta archivo', 'Seleccione un Excel .xlsx.', 'warning');
            return;
        }
        if (!gcShellModoCartera && (!ecFecha || !ecFecha.value)) {
            alertar('Fecha', 'Indique la fecha de corte.', 'warning');
            return;
        }
        btnEcLauncher.disabled = true;
        if (gcShellModoCartera) {
            gcCarteraActividadPush('Subiendo archivo…', 'gc-cartera-act-line--info');
        }
        if (ecOutWrap) ecOutWrap.classList.add('d-none');
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
                if (gcShellModoCartera) {
                    gcCarteraActividadPush('No se pudo subir el archivo. Revise el formato o el tamaño.', 'gc-cartera-act-line--err');
                }
                alertar('Subida', ju.mensaje || 'No se pudo subir el archivo.', 'error');
                refrescarEstado();
                return;
            }
            if (gcShellModoCartera) {
                gcCarteraActividadPush('Archivo recibido. Iniciando conciliación…', 'gc-cartera-act-line--info');
            }
            var payloadEc = {
                nombre: ju.nombre,
                tipo: gcEcTipoWorkerOEnrich(),
                fechaCorte: gcEcFechaCorteParaWorker(),
                column: ecCol ? ecCol.value.trim() || 'ID CREDITO' : 'ID CREDITO',
                omitir: ecOmitir ? parseInt(ecOmitir.value, 10) || 0 : 0,
                soloColumnas: false,
                traceId: generarTraceIdGc('wln')
            };
            await ejecutarPayloadEcYListaNegra(payloadEc, {});
        } catch (e) {
            if (gcShellModoCartera) {
                gcCarteraActividadPush('Error al procesar la solicitud.', 'gc-cartera-act-line--err');
            }
            alertar('Error', String(e.message || e), 'error');
        }
        refrescarEstado();
    }

    async function ejecutarCargaVerificacionFlujo() {
        if (!cargaVerifFile || !cargaVerifFile.files || !cargaVerifFile.files[0]) {
            alertar('Falta archivo', 'Seleccione un Excel .xlsx.', 'warning');
            return;
        }
        iniciarOperacionShell('lista_negra');
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
                finalizarOperacionShell();
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
        } finally {
            finalizarOperacionShell();
        }
        refrescarEstado();
    }

    async function ejecutarProcesoCronjobGc(proceso, tituloVisual) {
        if (!proceso) return;
        iniciarOperacionShell(proceso);
        comenzarLogRapidoEcWorker();
        if (gcCronjobsSalidaWrap) gcCronjobsSalidaWrap.classList.add('d-none');
        if (gcCronjobsSalida) gcCronjobsSalida.textContent = '';
        try {
            var r = await fetch('/gastoscobranza/ejecutarProcesoCronjobGc', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Front-Request': 'true' },
                body: JSON.stringify({ proceso: proceso })
            });
            var data = await r.json().catch(function () { return {}; });
            var ok = !!data.success;
            if (gcCronjobsSalida && (data.stdout || data.stderr || data.mensaje)) {
                gcCronjobsSalida.textContent =
                    (data.stdout || '') +
                    (data.stderr ? '\n--- stderr ---\n' + data.stderr : '') +
                    (data.mensaje ? '\n\n' + data.mensaje : '');
                if (gcCronjobsSalidaWrap) gcCronjobsSalidaWrap.classList.remove('d-none');
            }
            var msg = data.mensaje || (ok ? 'Proceso finalizado correctamente.' : 'El proceso terminó con error.');
            if (data.codigo_salida !== undefined && data.codigo_salida !== null) {
                msg += '\nCódigo de salida: ' + data.codigo_salida;
            }
            alertar(ok ? (tituloVisual + ' listo') : (tituloVisual + ' con errores'), msg, ok ? 'success' : 'error');
            await traerLog(400, { scrollBottom: true });
        } catch (e) {
            alertar('Error', String(e.message || e), 'error');
        } finally {
            detenerLogRapidoEcWorker();
            finalizarOperacionShell();
            refrescarEstado({ silencioso: true });
        }
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
        var regenerarReporte = false;
        if (yaExisteArchivoHoy) {
            var acc = await dialogoReporteYaGeneradoHoy();
            if (acc !== 'forzar') {
                return;
            }
            regenerarReporte = true;
            try {
                localStorage.removeItem(LS_REPORTE_OK_YMD);
            } catch (eLs) { /* ignorar */ }
        }

        iniciarOperacionShell('reporte');
        if (btnRun) btnRun.disabled = true;
        if (outWrap) outWrap.classList.add('d-none');
        try {
            var r;
            try {
                r = await fetch('/gastoscobranza/ejecutarReporte', {
                    method: 'POST',
                    headers: {
                        'Front-Request': 'true',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(regenerarReporte ? { regenerar_reporte: true } : {})
                });
            } catch (eFetch) {
                /*
                 * "Failed to fetch": el navegador perdió la conexión con PHP (timeout de proxy/nginx,
                 * cierre de red, etc.). El reporte puede haber terminado igual en el agente.
                 */
                var m0 = String(eFetch.message || eFetch);
                var extra0 = (m0 === 'Failed to fetch' || /networkerror|load failed|aborted/i.test(m0))
                    ? '\n\nEl reporte tarda muchos minutos: a veces un proxy o el servidor web corta la espera antes de que PHP devuelva la respuesta, aunque el agente siga y genere el Excel. Pulse «Listar reportes» o abra el log del agente para comprobar si ya quedó listo.'
                    : '';
                alertar('No se recibió respuesta del servidor', m0 + extra0, 'warning');
                await traerListaReportes();
                await traerLog(400, { scrollBottom: true });
                return;
            }
            var rawRep;
            try {
                rawRep = await r.text();
            } catch (eBody) {
                var m1 = String(eBody.message || eBody);
                var extra1 = (m1 === 'Failed to fetch' || /networkerror|load failed/i.test(m1))
                    ? '\n\nLa conexión se cortó al leer la respuesta (tiempos largos). Revise la tabla de reportes y el log del agente por si el proceso ya terminó.'
                    : '';
                alertar('Respuesta incompleta', m1 + extra1, 'warning');
                await traerListaReportes();
                await traerLog(400, { scrollBottom: true });
                return;
            }
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
                    if (outPre) outPre.textContent = (data.stdout || '') + (data.stderr ? '\n--- stderr ---\n' + data.stderr : '');
                    if (outWrap) outWrap.classList.remove('d-none');
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
            var m = String(e.message || e);
            var extra = (m === 'Failed to fetch' || /networkerror|load failed/i.test(m))
                ? '\n\nSi el agente ya terminó, use «Listar reportes» o el log para verificar.'
                : '';
            alertar('Error', m + extra, 'error');
            try {
                await traerListaReportes();
                await traerLog(400, { scrollBottom: true });
            } catch (ePost) { /* ignorar */ }
        } finally {
            finalizarOperacionShell();
            refrescarEstado();
        }
    }

    /**
     * Carga verificación semana (lista negra) usando un Excel ya en reporte/ (mismo nombre de fila).
     * Respeta dry-run, estatus, headerRow y mensaje del formulario en el modal «Lista negra».
     */
    async function ejecutarListaNegraDesdeReporte(nombreArchivo) {
        iniciarOperacionShell('lista_negra');
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
            finalizarOperacionShell();
            refrescarEstado();
        }
    }

    async function ejecutarWorkerDesdeReporte(nombreArchivo) {
        if (!gcShellModoCartera && (!ecFecha || !ecFecha.value)) {
            alertar('Fecha de corte', 'Indique la fecha de corte S2 en el modal «EC Worker / Excel enriquecido» (la misma que usaría al subir el Excel).', 'warning');
            try {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var mEc = document.getElementById('modalGcEcWorker');
                    if (mEc) bootstrap.Modal.getOrCreateInstance(mEc).show();
                }
            } catch (eM) { /* ignorar */ }
            if (ecFecha) ecFecha.focus();
            return;
        }
        if (ecOutWrap) ecOutWrap.classList.add('d-none');
        if (ecErroresReintentoBanner) ecErroresReintentoBanner.classList.add('d-none');
        try {
            var payloadEcW = {
                nombre: nombreArchivo,
                tipo: 'worker',
                fechaCorte: gcEcFechaCorteParaWorker(),
                column: ecCol ? ecCol.value.trim() || 'ID CREDITO' : 'ID CREDITO',
                omitir: ecOmitir ? parseInt(ecOmitir.value, 10) || 0 : 0,
                soloColumnas: false,
                origenCarpeta: 'reporte',
                traceId: generarTraceIdGc('wln')
            };
            await ejecutarPayloadEcYListaNegra(payloadEcW, { origenCarpeta: 'reporte' });
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
        ivLog = null;
        if (chkLog) {
            ivLog = setInterval(function () {
                if (document.hidden || !chkLog.checked) return;
                if (ivLogEcWorker) return;
                traerLog(400, { scrollBottom: 'auto' });
            }, 4000);
        }
        if (ivRep) clearInterval(ivRep);
        ivRep = setInterval(function () {
            if (document.hidden) return;
            traerListaReportes();
        }, 12000);
    }

    if (chkLog) chkLog.addEventListener('change', function () {
        if (chkLog.checked) traerLog(400, { scrollBottom: true });
    });
    if (btnLog) btnLog.addEventListener('click', function () {
        traerLog(400, { scrollBottom: true });
    });
    var modalGcAgenteLog = document.getElementById('modalGcAgenteLog');
    if (modalGcAgenteLog) {
        modalGcAgenteLog.addEventListener('shown.bs.modal', function () {
            traerLog(400, { scrollBottom: true });
        });
    }
    if (btnLogCopiar) btnLogCopiar.addEventListener('click', copiarLogAlPortapapeles);
    if (btnLogVaciar) btnLogVaciar.addEventListener('click', vaciarLogAgente);
    function manejarClickAccionesTablaReporte(ev, root) {
        if (!root) return;
        var btnW = ev.target.closest('.btn-gc-worker-reporte');
        if (btnW && root.contains(btnW)) {
            var encW = btnW.getAttribute('data-nombre-enc');
            if (encW) ejecutarWorkerDesdeReporte(decodeURIComponent(encW));
            return;
        }
        var btnN = ev.target.closest('.btn-gc-lista-negra-reporte');
        if (btnN && root.contains(btnN)) {
            var encN = btnN.getAttribute('data-nombre-enc');
            if (encN) ejecutarListaNegraDesdeReporte(decodeURIComponent(encN));
        }
    }
    if (tbodyRep) {
        tbodyRep.addEventListener('click', function (ev) {
            manejarClickAccionesTablaReporte(ev, tbodyRep);
        });
    }
    if (tbodyRepHist) {
        tbodyRepHist.addEventListener('click', function (ev) {
            manejarClickAccionesTablaReporte(ev, tbodyRepHist);
        });
    }
    if (btnHistoricoRep) {
        btnHistoricoRep.addEventListener('click', function () {
            if (!gcCacheArchivosReporte.length) {
                traerListaReportes().then(function () {
                    gcAbrirModalHistoricoReportes();
                });
            } else {
                gcAbrirModalHistoricoReportes();
            }
        });
    }
    if (selHistoricoSemana) {
        selHistoricoSemana.addEventListener('change', gcPintarTablaHistorico);
    }
    if (btnListarRep) btnListarRep.addEventListener('click', traerListaReportes);
    if (btnRun) btnRun.addEventListener('click', ejecutar);
    if (ecFecha) ecFecha.value = fechaCalendarioCdmxYmd();
    if (ecFile) {
        ecFile.addEventListener('change', function () {
            gcPintarZonaExcelCliente();
            aplicarEstadoBotonesShellCompleto();
        });
    }
    if (cargaVerifFile) {
        cargaVerifFile.addEventListener('change', function () {
            gcPintarZonaExcelCliente();
            aplicarEstadoBotonesShellCompleto();
        });
    }
    gcPintarZonaExcelCliente();
    if (btnGcProcesoInsertarMoraMartes) {
        btnGcProcesoInsertarMoraMartes.addEventListener('click', function () {
            ejecutarProcesoCronjobGc('insertar_mora_martes', 'Insertar moras martes');
        });
    }
    if (btnGcProcesoDetectarLiquidados) {
        btnGcProcesoDetectarLiquidados.addEventListener('click', function () {
            ejecutarProcesoCronjobGc('detectar_gdc_liquidados', 'Detectar GDC liquidados');
        });
    }
    if (btnGcProcesoEliminarDespachos) {
        btnGcProcesoEliminarDespachos.addEventListener('click', function () {
            ejecutarProcesoCronjobGc('eliminar_gastos_despachos', 'Eliminar gastos despachos');
        });
    }
    if (btnEcLauncher) btnEcLauncher.addEventListener('click', ejecutarEcLauncherFlujo);
    if (btnCargaVerif) btnCargaVerif.addEventListener('click', ejecutarCargaVerificacionFlujo);
    if (btnDescargoEstatus3) btnDescargoEstatus3.addEventListener('click', ejecutarDescargoEstatus3Flujo);
    try {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            document.querySelectorAll('.container-xxl [data-bs-toggle="tooltip"]').forEach(function (el) {
                try {
                    new bootstrap.Tooltip(el);
                } catch (eTip) { /* ignorar */ }
            });
        }
    } catch (eBt) { /* ignorar */ }
    if (switchGcAutoRun) actualizarTextoAutoRun(!!switchGcAutoRun.checked);
    if (switchGcAutoRun) {
        switchGcAutoRun.addEventListener('change', async function () {
            if (gcAutoRunProgrammatic) return;
            actualizarTextoAutoRun(!!switchGcAutoRun.checked);
            await gcPersistPreferenciaAutoRunYdias();
        });
    }
    document.querySelectorAll('.gc-auto-run-dia-cb').forEach(function (el) {
        el.addEventListener('change', function () {
            if (gcAutoRunProgrammatic) return;
            gcSchedulePersistAutoRun();
        });
    });
    refrescarEstado();
    programarPoll();
})();
</script>
