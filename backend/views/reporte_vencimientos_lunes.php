<?php
$vlSemanaNum = null;
$vlSemanaRango = '';
if (empty($vencimientos_vista_simple)) {
    $vlTz = new DateTimeZone('America/Mexico_City');
    $vlHoy = new DateTimeImmutable('now', $vlTz);
    $vlDow = (int) $vlHoy->format('N');
    $vlLunes = $vlHoy->modify('-' . ($vlDow - 1) . ' days');
    $vlDomingo = $vlLunes->modify('+6 days');
    $vlSemanaNum = (int) $vlLunes->format('W');
    $vlMeses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
    $vlDiasSem = [1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sábado', 7 => 'domingo'];
    $vlFmtDia = static function (DateTimeImmutable $d, array $meses, array $dias, bool $incluirAnio): string {
        $n = (int) $d->format('N');
        $dia = (int) $d->format('j');
        $mes = $meses[(int) $d->format('n')];
        $s = $dias[$n] . ' ' . $dia . ' de ' . $mes;
        if ($incluirAnio) {
            $s .= ' de ' . $d->format('Y');
        }
        return $s;
    };
    if ($vlLunes->format('Y') === $vlDomingo->format('Y')) {
        $vlSemanaRango = $vlFmtDia($vlLunes, $vlMeses, $vlDiasSem, false) . ' al ' . $vlFmtDia($vlDomingo, $vlMeses, $vlDiasSem, true);
    } else {
        $vlSemanaRango = $vlFmtDia($vlLunes, $vlMeses, $vlDiasSem, true) . ' al ' . $vlFmtDia($vlDomingo, $vlMeses, $vlDiasSem, true);
    }
}
$vlEsUsuarioRoot = (int)($_SESSION['usuario_id'] ?? 0) === 1;
?>
    <!-- ── Header ── -->
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0">
                <i class="fa fa-calendar-week text-primary me-2"></i>
                <?= htmlspecialchars($vencimientos_titulo_card ?? 'Primeros pagos — Lunes de Cierre', ENT_QUOTES, 'UTF-8'); ?>
            </h4>
            <?php if (!empty($vencimientos_vista_simple)): ?>
            <p class="text-muted mb-0" style="font-size:.8rem;">
                Primer vencimiento:
                <strong class="text-primary" id="lunesFecha">calculando…</strong>
            </p>
            <?php endif; ?>
        </div>
        <div class="d-flex align-items-center justify-content-end gap-2 flex-wrap">
            <?php if (!empty($vencimientos_vista_simple)): ?>
                <button type="button" id="btnDescargarExcelPrimerosPagosSemana"
                        class="btn btn-success btn-sm shadow-sm px-3 d-inline-flex align-items-center">
                    <i class="fa fa-file-excel me-2"></i>
                    <span class="fw-semibold">Descargar Excel</span>
                </button>
                <?php if (!empty($vencimientos_puede_enviar_correo_primeros_pagos)): ?>
                <button type="button" id="btnEnviarCorreo" class="btn btn-outline-primary btn-sm shadow-sm d-inline-flex align-items-center">
                    <i class="fa fa-envelope me-1"></i> Enviar correo
                </button>
                <?php endif; ?>
            <?php elseif (empty($vencimientos_vista_simple)): ?>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php if ($vlEsUsuarioRoot): ?>
                    <div class="d-flex align-items-center gap-2 flex-wrap me-1"
                         title="Guardado en el servidor. Solo envío automático por cron (CDMX: 07:40, 09:40, 11:40, 13:40, 14:40, 16:40, 18:40, 20:40, 23:50 en 24 h). Requiere agente Node o bucle PHP en esta máquina. No afecta “Enviar correo” manual.">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   id="switchAutoEnvioPrimerosPagos">
                            <label class="form-check-label text-nowrap user-select-none" for="switchAutoEnvioPrimerosPagos"
                                   style="font-size:.72rem;">Auto horario</label>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-1" style="min-width:0;">
                            <span id="estadoEnvioAuto" class="badge bg-label-secondary text-wrap text-start" style="max-width:min(100vw - 4rem, 320px);"
                                  title="Estado del envío automático (CDMX, horario 24 h: 07:40, 09:40, 11:40, 13:40, 14:40, 16:40, 18:40, 20:40, 23:50).">
                                <i class="fa fa-clock me-1"></i> Auto correo: pendiente
                            </span>
                            <span id="estadoAgenteCorreos" class="badge bg-label-secondary text-wrap text-start" style="max-width:min(100vw - 4rem, 320px);"
                                  title="Agente Node que ejecuta el cron de correos (puerto 3110 por defecto).">
                                <i class="fa fa-robot me-1"></i> Agente: …
                            </span>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($vencimientos_puede_enviar_correo_primeros_pagos)): ?>
                    <button type="button" id="btnEnviarCorreo" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-envelope me-1"></i> Enviar correo
                    </button>
                    <?php endif; ?>
                    <button type="button" id="btnExportarCSV" class="btn btn-outline-success btn-sm">
                        <i class="fa fa-file-csv me-1"></i> Exportar CSV
                    </button>
                </div>
            <?php endif; ?>
            <a href="/reporteria/PrimerosPagos" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <?php if (empty($vencimientos_vista_simple)): ?>
    <!-- ── Contexto semana / vencimiento / corte + Registros ── -->
    <div class="row g-3 mb-3 align-items-start">
        <div class="col-12 col-md-auto">
            <div class="card border shadow-sm">
                <div class="card-body py-2 px-3">
                    <div class="fw-semibold text-body">Semana <?= (int) $vlSemanaNum ?></div>
                    <p class="text-muted small mb-2"><strong>Periodo del:</strong> <?= htmlspecialchars($vlSemanaRango, ENT_QUOTES, 'UTF-8') ?></p>
                    <hr class="my-2">
                    <p class="text-muted mb-0" style="font-size:.8rem;">
                        Primer vencimiento:
                        <strong class="text-primary" id="lunesFecha">calculando…</strong>
                        &nbsp;·&nbsp;
                        Corte actual:
                        <code id="corteLabel" class="text-info">—</code>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2">
            <div class="card text-center h-100">
                <div class="card-body py-2">
                    <div class="text-muted" style="font-size:.68rem;text-transform:uppercase;letter-spacing:.5px;">
                        Registros
                    </div>
                    <div class="fw-bold text-primary" style="font-size:1.5rem;" id="statTotal">—</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($vencimientos_vista_simple)): ?>
    <!-- Resumen compacto (Semana actual): título Nacimiento + total, solo Bootstrap -->
    <div class="mb-3">
        <div class="d-inline-flex flex-column gap-2 border rounded bg-white px-4 py-3">
            <span class="fw-semibold text-body mb-0">
                <i class="fa fa-egg text-primary me-2"></i>Nacimiento
            </span>
            <div class="d-flex align-items-baseline flex-wrap gap-2 gap-sm-3">
                <span class="text-muted mb-0">Total de registros</span>
                <span class="fw-semibold text-primary fs-5 mb-0" id="statsNacimiento">—</span>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- ── Nacimiento + distribución de corte (Lunes de cierre) ── -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-md-6">
            <div class="card h-100 mb-0">
                <div class="card-header py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <span class="fw-semibold" style="font-size:.82rem;">
                        <i class="fa fa-egg text-primary me-1"></i>
                        Distribución de nacimiento
                    </span>
                    <span class="badge bg-label-warning"><i class="fa fa-globe me-1"></i>Global</span>
                </div>
                <div class="card-body py-2">
                    <div class="row row-cols-2 g-2" id="statsNacimientoTop"></div>
                    <div id="nacimientoGlobalResumen" class="mt-3 mb-0" style="display:none;">
                        <div class="d-flex rounded-pill overflow-hidden border" style="height:0.82rem;background:rgba(0,0,0,.06);border-color:rgba(0,0,0,.1) !important;" role="group" aria-label="Distribución global Current vs 1-7 días">
                            <div id="nacBarCurrent" class="d-flex align-items-center justify-content-center bg-success text-white fw-semibold flex-shrink-0 overflow-hidden"
                                 role="progressbar" style="width:0%;min-width:0;font-size:.58rem;line-height:1;padding:0 2px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span id="nacPctCurrent"></span>
                            </div>
                            <div id="nacBar17" class="d-flex align-items-center justify-content-center bg-danger text-white fw-semibold flex-shrink-0 overflow-hidden"
                                 role="progressbar" style="width:0%;min-width:0;font-size:.58rem;line-height:1;padding:0 2px;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                <span id="nacPct17"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row row-cols-2 g-2 mt-2" id="statsNacimientoRest"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="h-100" style="background:#f0f3f7;border:1px solid #d8dfe7;border-radius:.5rem;overflow:hidden;">
                <div style="background:#e9edf2;padding:.5rem .75rem;border-bottom:0;">
                    <span class="fw-semibold d-inline-flex flex-wrap align-items-center gap-1" style="font-size:.78rem;line-height:1.35;">
                        <i class="fa fa-chart-pie flex-shrink-0" style="color:#000 !important;"></i>
                        <span style="color:#000 !important;">Distribución de corte:</span>
                        <span id="distribCorteFecha" class="fw-semibold" style="color:#6b7785;">—</span>
                        <span class="text-muted">·</span>
                        <span class="text-body">Corte actual:</span>
                        <code id="distribCorteCorteLbl" class="text-info mb-0" style="font-size:.78rem;">—</code>
                    </span>
                </div>
                <div style="background:#f0f3f7;padding:.5rem;">
                    <div class="row row-cols-2 g-2" id="statsCorte">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($vencimientos_vista_simple)): ?>
    <!-- ── Seguimiento por jerarquía ── -->
    <div class="card mb-3">
        <div class="card-header py-2">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-ranking-star text-danger me-1"></i>
                Seguimiento por jerarquía
                <span class="text-muted fw-normal" style="font-size:.72rem;">
                    — peor seguimiento primero
                </span>
            </span>
        </div>
        <div class="card-body" id="statsJerarquia">
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($vencimientos_vista_simple)): ?>
    <!-- ── Filtros (solo Lunes de cierre) ── -->
    <div class="card mb-3">
        <div class="card-header py-2 d-flex align-items-center justify-content-between">
            <span class="fw-semibold" style="font-size:.82rem;">
                <i class="fa fa-sliders text-primary me-1"></i> Filtros
            </span>
            <button id="btnReset" class="btn btn-outline-secondary btn-sm" style="font-size:.72rem;">
                <i class="fa fa-rotate-left me-1"></i> Limpiar
            </button>
        </div>
        <div class="card-body py-2">
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <label class="form-label mb-0" style="font-size:.72rem;">Buscar cliente / ID crédito</label>
                    <input id="fBusq" type="text" class="form-control form-control-sm"
                           placeholder="Nombre o ID…">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">Bucket nació</label>
                    <select id="fBucketNacio" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label mb-0" style="font-size:.72rem;">Bucket corte actual</label>
                    <select id="fBucketCorte" class="form-select form-select-sm">
                        <option value="">Todos</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-0" style="font-size:.72rem;">Movimiento</label>
                    <select id="fMovimiento" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="mejoro">⬆ Mejoró</option>
                        <option value="igual">➡ Sin cambio</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Tabla principal ── -->
    <div class="card mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="tablaVencimientos"
                       class="table table-hover table-sm align-middle"
                       style="width:100%">
                    <thead class="table-light">
                    <tr>
                        <?php if (!empty($vencimientos_vista_simple) && !empty($columnas_primeros_pagos)): ?>
                            <?php foreach ($columnas_primeros_pagos as $colPp): ?>
                        <th class="text-nowrap" style="font-size:.7rem;"><?= htmlspecialchars($colPp['titulo'] ?? '', ENT_QUOTES, 'UTF-8'); ?></th>
                            <?php endforeach; ?>
                        <?php elseif (!empty($vencimientos_vista_simple)): ?>
                        <th colspan="2" class="text-muted small">Columnas (definir en Empresa::columnasPrimerosPagosMegareporte)</th>
                        <?php else: ?>
                        <th>
                            <i class="fa fa-id-card text-primary me-1"></i>
                            General
                        </th>
                        <th>
                            <i class="fa fa-sitemap text-muted me-1"></i>
                            Jerarquía
                        </th>
                        <th class="text-center">
                            <i class="fa fa-egg text-primary me-1"></i>
                            Cómo nació
                        </th>
                        <th class="text-center">
                            <i class="fa fa-chart-line text-warning me-1"></i>
                            Corte actual
                        </th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
