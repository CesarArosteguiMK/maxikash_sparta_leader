<?php

// vista___SPARTA_SECRET_REDACTED__.php
date_default_timezone_set('America/Mexico_City');

/* ----------------------
   Helpers locales
   ---------------------- */
function format_currency($v) {
    return '$' . number_format((float)$v, 2, '.', ',');
}
function format_date($d, $fallback = '—') {
    if (!$d) return $fallback;
    $ts = strtotime($d);
    if (!$ts) return $fallback;
    return date('d/m/Y', $ts);
}
function safe($v, $default = null) {
    return isset($v) ? $v : $default;
}

/* Asegurar que $tabla exista */
if (!isset($tabla) || !is_array($tabla)) $tabla = [];

$fechaUltimoPagoCompleto = null;

foreach ($tabla as $fila) {
    $pendiente = safe($fila['pendiente'], 0.0);
    $aplicados = safe($fila['aplicados'], []);

    if ($pendiente <= 0 && !empty($aplicados)) {
        $lastPagoDate = null;

        foreach ($aplicados as $a) {
            if (!empty($a['fechaRegistro'])) {
                $ts = strtotime($a['fechaRegistro']);
                if ($ts && (!$lastPagoDate || $ts > strtotime($lastPagoDate))) {
                    $lastPagoDate = $a['fechaRegistro'];
                }
            }
        }

        if ($lastPagoDate) {
            if (
                    !$fechaUltimoPagoCompleto ||
                    strtotime($lastPagoDate) > strtotime($fechaUltimoPagoCompleto)
            ) {
                $fechaUltimoPagoCompleto = $lastPagoDate;
            }
        }
    }
}


$cuotasContratadas = (int)($dataOtrosDatos["cuotasContratadas"] ?? 0);
$cuotasPagadas     = (int)($dataOtrosDatos["cuotasPagadas"] ?? 0);
$cuotasFaltantes = $cuotasContratadas - $cuotasPagadas;

$porcentajeAvance = 0;
if ($cuotasContratadas > 0) {
    $porcentajeAvance = min(100, round(($cuotasPagadas / $cuotasContratadas) * 100));
}
?>

<style>

    /* ==========================
   GLOBAL
   ========================== */
    html, body {
        overflow-y: auto;
    }

    /* ==========================
       SIDEBAR
       ========================== */
    @media (min-width: 992px) {
        .sidebar-cliente {
            position: sticky;
            top: 100px;
            height: max-content;
            z-index: 8;
        }
    }

    @media (max-width: 991px) {
        .sidebar-cliente {
            position: static !important;
        }
    }

    /* ==========================
       TABLA ESTILOS
       ========================== */
    .cuotas-table,
    .cuotas-table td,
    .cuotas-table th {
        color: #000 !important;
    }

    .cuotas-table td,
    .cuotas-table th {
        font-size: 0.80rem !important;
        line-height: 1.1rem;
    }

    .cuotas-table ul li,
    .cuotas-table .fecha-pago,
    .cuotas-table .fecha-cuota {
        font-size: 0.75rem !important;
    }

    .cuotas-table .badge {
        font-size: 0.70rem !important;
        padding: 0.35em 0.5em !important;
        border-radius: 6px;
    }

    /* ==========================
       TABLA SCROLL DESKTOP
       ========================== */
    @media (min-width: 992px) {
        .tabla-scrollable {
            max-height: calc(97.5vh - 240px);
            overflow-y: auto;
            overflow-x: hidden;
            position: sticky;
            top: 120px;
            z-index: 5;
            background: #fff;
            scrollbar-gutter: stable both-edges;
        }

        .tabla-scrollable thead th {
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 8;
        }
    }

    /* ==========================
       MOBILE / TABLET (ANTI-LAG)
       ========================== */
    @media (max-width: 991px) {

        /* quitar sticky */
        .tabla-scrollable,
        .tabla-scrollable thead th {
            position: static !important;
            top: auto !important;
        }

        .tabla-scrollable {
            max-height: none;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
        }

        .cuotas-table {
            min-width: 780px;
        }
    }

    /* ==========================
       MOBILE COMPACT
       ========================== */
    @media (max-width: 768px) {
        .cuotas-table td,
        .cuotas-table th {
            font-size: 0.65rem !important;
            padding: 0.35rem 0.4rem !important;
        }

        .cuotas-table ul li {
            font-size: 0.6rem !important;
            line-height: 0.9rem;
        }

        .cuotas-table .badge {
            font-size: 0.6rem !important;
            padding: 0.25em 0.4em !important;
        }
    }

    /* ==========================
   LAPTOP GRANDE (1536 x 864 aprox)
   ========================== */
    @media (min-width: 1400px) and (max-height: 900px) {

        .sidebar-cliente {
            top: 110px;
        }

        .tabla-scrollable {
            top: 110px;
            max-height: calc(100vh - 220px);
        }

    }

    @media (min-width: 1400px) and (max-width: 1599px) and (max-height: 900px) {

        .sidebar-cliente .info-compact,
        .sidebar-cliente .info-compact span,
        .sidebar-cliente .info-compact li {
            font-size: 0.72rem !important;
            line-height: 1.1rem;
        }

        .sidebar-cliente .info-compact i {
            font-size: 0.85rem !important;
        }

    }

    @media (max-width: 991px) {
        .cuotas-table {
            min-width: 900px;
        }
    }

    .cuotas-table {
        table-layout: fixed;
        width: 100%;
    }



</style>

<div class="row">

    <!-- SIDEBAR CLIENTE -->
    <div class="col-xl-4 col-lg-5 order-1 order-md-0 sidebar-cliente">
        <div class="card mb-6">
            <div class="card-body">

                <div class="user-avatar-section">
                    <div class="card mb-3 border border-2 border-primary rounded primary-shadow">
                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-start">
                                <span class="badge bg-label-primary">ID Crédito: <?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?> </span><span class="badge bg-label-secondary">ID Cliente: <?= htmlspecialchars($dataCliente["idCliente"] ?? '') ?> </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="h6 mb-0"> <?= htmlspecialchars($dataCliente["nombreCliente"] ?? '') ?></span>
                            </div>

                            <div class="progress mb-1" title="<?= $porcentajeAvance ?>%">
                                <div
                                        class="progress-bar bg-primary"
                                        role="progressbar"
                                        style="width: <?= $porcentajeAvance ?>%;"
                                        aria-valuenow="<?= $porcentajeAvance ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                </div>
                            </div>

                            <small class="d-flex align-items-center gap-2">
                                <i class="fa fa-phone text-primary"></i>
                                <?php
                                $cel = preg_replace('/\D/', '', $dataCliente["celular"] ?? '');
                                if (strlen($cel) === 10) {
                                    $cel = sprintf(
                                            "(%s) %s-%s",
                                            substr($cel, 0, 2),
                                            substr($cel, 2, 4),
                                            substr($cel, 6, 4)
                                    );
                                }
                                echo htmlspecialchars($cel);
                                ?>

                                <i class="fa fa-location text-primary"></i>

                                <a href="#"
                                   class="text-primary text-decoration-underline"
                                   data-bs-toggle="modal"
                                   data-bs-target="#modalDirecciones">
                                    Direcciones
                                </a>
                            </small>




                        </div>
                    </div>
                </div>

                <!-- MÉTRICAS -->
                <div class="d-flex justify-content-between flex-nowrap my-3 gap-1 gap-md-1">

                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-label-info rounded w-px-40 h-px-40">
                                <i class="fa fa-dollar"></i>
                            </div>
                        </div>
                        <div class="text-truncate">
                            <h5 class="mb-0 text-truncate">
                                <?= htmlspecialchars($dataEstadoCuenta["statusCredito"] ?? '') ?>
                            </h5>
                            <span class="small">Estatus Crédito</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-label-danger rounded w-px-40 h-px-40">
                                <i class="fa fa-dollar"></i>
                            </div>
                        </div>
                        <div class="text-end text-truncate">
                            <h5 class="mb-0 text-truncate">
                                <?= format_currency($dataOtrosDatos["saldoTotalVencido"] ?? 0) ?>
                            </h5>
                            <span class="small">Saldo Total Vencido</span>
                        </div>
                    </div>

                </div>

                <!-- DATOS E INFO
                <hr class="my-2 w-100">
                <small class="card-text text-uppercase text-body-secondary small">Identificación del Cliente</small>
                <ul class="list-unstyled my-1 info-compact">
                    <li class="d-flex align-items-center mb-4">
                        <i class="fa fa-id-card fa-lg"></i>
                        <span class="fw-medium mx-2">RFC:</span>
                        <span><?= htmlspecialchars($dataCliente["rfc"] ?? '') ?></span>
                    </li>
                </ul>-->

                <hr class="my-2 w-100">
                <small class="card-text text-uppercase text-body-secondary small">Información del Crédito</small>
                <ul class="list-unstyled my-1 py-1 info-compact">
                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-money-bill fa-lg"></i>
                        <span class="fw-medium mx-2">Monto Otorgado:</span>
                        <span>$37,759.20</span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-list-ol fa-lg"></i>
                        <span class="fw-medium mx-2">Cuotas Contratadas:</span>
                        <span><?= htmlspecialchars($dataOtrosDatos["cuotasContratadas"] ?? '') ?> cuotas</span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-check-circle fa-lg"></i>
                        <span class="fw-medium mx-2">Cuotas Pagadas:</span>
                        <span><?= htmlspecialchars($dataOtrosDatos["cuotasPagadas"] ?? '') ?> cuotas</span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-hourglass fa-lg"></i>
                        <span class="fw-medium mx-2">Cuotas Faltantes:</span>
                        <span><?= $cuotasFaltantes ?> cuotas</span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-credit-card fa-lg"></i>
                        <span class="fw-medium mx-2">Saldo para Liquidar:</span>
                        <span><?= format_currency($dataOtrosDatos["saldoParaLiquidarV2"] ?? 0) ?></span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-exclamation-triangle fa-lg"></i>
                        <span class="fw-medium mx-2">Mora Máximo:</span>
                        <span><?= htmlspecialchars($dataOtrosDatos["diasMoraMaximo"] ?? 0) ?> días</span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-clock fa-lg"></i>
                        <span class="fw-medium mx-2">Mora:</span>
                        <span><?= htmlspecialchars($dataOtrosDatos["diasMora"] ?? 0) ?> días</span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-calendar-alt fa-lg"></i>
                        <span class="fw-medium mx-2">Fecha Inicio:</span>
                        <span><?= format_date($dataEstadoCuenta["fechaInicio"] ?? null) ?></span>
                    </li>

                    <hr class="my-2 w-100">

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-calendar-day fa-lg"></i>
                        <span class="fw-medium mx-2">Primer Vencimiento:</span>
                        <span><?= format_date($dataEstadoCuenta["primerVencimiento"] ?? null) ?></span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-calendar-check fa-lg"></i>
                        <span class="fw-medium mx-2">Último Vencimiento:</span>
                        <span><?= format_date($dataEstadoCuenta["ultimoVencimiento"] ?? null) ?></span>
                    </li>

                    <li class="d-flex align-items-center mb-2">
                        <i class="fa fa-calendar-check fa-lg"></i>
                        <span class="fw-medium mx-2">Fecha de Liquidación:</span>
                        <span><?= format_date($dataEstadoCuenta["fechaLiquidacion"] ?? null) ?></span>
                    </li>

                    <br>

                    <button type="button"
                            class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2"
                            data-bs-toggle="modal" data-bs-target="#modalRFC">

                        <i class="fa fa-id-card fa-lg"></i>
                        <strong>Ver referencias del cliente</strong>

                    </button>


                </ul>

            </div>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="col-xl-8 col-lg-7 order-0 order-md-1">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Resumen general de pagos del cliente</h5>

            <div class="d-flex gap-2">
                <a href="/estadocuenta/consulta" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                    <i class="fa fa-headset"></i>
                    <span>Call Center</span>
                </a>

                <a href="/estadocuenta/consulta" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                    <i class="fa fa-search"></i>
                    <span>Nueva consulta</span>
                </a>
            </div>
        </div>


        <div class="card mb-6">
            <div class="d-flex justify-content-around flex-wrap my-6 gap-0 gap-md-3">

                <div class="d-flex align-items-center me-5 gap-4">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-success rounded w-px-40 h-px-40">
                            <i class="fa fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= format_currency($dataEstadoCuenta["cuota"] ?? 0) ?></h5>
                        <span>Cuota Semanal</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-primary rounded w-px-40 h-px-40">
                            <i class="fa fa-calendar"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-0">
                            <?= $fechaUltimoPagoCompleto
                                    ? format_date($fechaUltimoPagoCompleto)
                                    : '—'
                            ?>
                        </h5>
                        <span>Último Pago</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-4">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-facebook rounded w-px-40 h-px-40">
                            <i class="fa fa-id-card"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-0"><?= htmlspecialchars($dataEstadoCuenta["referenciaSTP"] ?? '') ?></h5>
                        <span>Referencia STP</span>
                    </div>
                </div>
            </div>

            <!-- TABLA DINÁMICA -->
            <div class="table-responsive tabla-scrollable">
                <table class="table table-hover table-striped cuotas-table">
                    <colgroup>
                        <col style="width: 9%">
                        <col style="width: 18%">
                        <col style="width: 20"> <!-- 👈 PAGOS DEL CLIENTE (énfasis) -->
                        <col style="width: 12%">
                        <col style="width: 18%">
                    </colgroup>

                    <thead class="border-top">
                    <tr>
                        <th class="text-nowrap text-center">Cuota</th>
                        <th class="text-nowrap text-center">Fecha</th>
                        <th class="text-nowrap text-center">Pagos del Cliente</th>
                        <th class="text-nowrap text-center">Aplicado</th>
                        <th class="text-nowrap text-center">Estatus</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($tabla as $fila): ?>
                        <?php
                        // Datos de la fila
                        $cuota = safe($fila['cuota'], '—');
                        $fecha = safe($fila['fecha'], null);
                        $monto_cargo = safe($fila['monto_cargo'], 0.0);
                        $aplicados = safe($fila['aplicados'], []);
                        $total_pagado = safe($fila['total_pagado'], 0.0);
                        $pendiente = safe($fila['pendiente'], 0.0);
                        $raw_cargo = safe($fila['raw_cargo'], []);

                        // Calcular fecha de último pago aplicado (si existe)
                        $lastPagoDate = null;
                        foreach ($aplicados as $a) {
                            if (!empty($a['fechaRegistro'])) {
                                $ts = strtotime($a['fechaRegistro']);
                                if ($ts && (!$lastPagoDate || $ts > strtotime($lastPagoDate))) {
                                    $lastPagoDate = $a['fechaRegistro'];
                                }
                            }
                        }

                        // Calcular días de mora:
                        //  - usar raw_cargo['diasMora'] si existe
                        //  - si no existe: si hay pagos, usar diff entre lastPagoDate y fechaVenc
                        //  - si no hay pagos, usar diff entre hoy y fechaVenc
                        $diasMora = null;
                        if (isset($raw_cargo['diasMora']) && $raw_cargo['diasMora'] !== null) {
                            $diasMora = (int)$raw_cargo['diasMora'];
                        } else {
                            $fechaVenc = $fecha ? strtotime($fecha) : false;
                            if ($fechaVenc) {
                                if ($lastPagoDate) {
                                    $diff = floor((strtotime($lastPagoDate) - $fechaVenc) / 86400);
                                    $diasMora = max(0, $diff);
                                } else {
                                    $diff = floor((time() - $fechaVenc) / 86400);
                                    $diasMora = max(0, $diff);
                                }
                            } else {
                                $diasMora = 0;
                            }
                        }

                        // Construir badge
                        if ($pendiente <= 0) {
                            // pago completo
                            $badge = '<span class="badge bg-success px-3 py-2">Pago completo</span>';
                            if ($diasMora > 0) {
                                $badge = '<span class="badge bg-danger px-3 py-2">Pago completo<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                            }
                        } elseif ($total_pagado > 0) {
                            $badge = '<span class="badge bg-warning px-3 py-2">Pago parcial<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                        } else {
                            $badge = '<span class="badge bg-secondary px-3 py-2">Sin pago<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                        }
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($cuota) ?></td>
                            <td class="fecha-cuota"><span class="fa fa-calendar"></span> <?= htmlspecialchars(format_date($fecha)) ?> <br> <u><?= format_currency($monto_cargo) ?></u></td>

                            <td>
                                <ul class="ps-3 mb-0">
                                    <?php if (!empty($aplicados)): ?>
                                        <?php foreach ($aplicados as $pago): ?>
                                            <?php
                                            $pago_monto = safe($pago['montoPago'], 0.0);
                                            $pago_aplicado = safe($pago['aplicado'], 0.0);
                                            $pago_fecha = safe($pago['fechaRegistro'], $pago['fechaPago'] ?? null);
                                            ?>
                                            <li>
                                                <span class="text-primary">Pago: <?= format_currency($pago_monto) ?></span> -
                                                <span style="color:#05611d;">Aplicado: <?= format_currency($pago_aplicado) ?></span> -
                                                <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($pago_fecha)) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <!-- Sin pagos -->
                                    <?php endif; ?>
                                </ul>
                            </td>
                            <td><?= format_currency($total_pagado) ?></td>
                            <td class="dias-mora"><?= $badge ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Modal RFC -->
    <div class="modal fade" id="modalRFC" tabindex="-1" aria-labelledby="modalRFCLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRFCLabel">Referencias del cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Contenido dinámico: p. ej. RFC -->
                    <p><strong>RFC:</strong> <?= htmlspecialchars($dataCliente["rfc"] ?? '—') ?></p>
                    <!-- agrega aquí lo que necesites -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>
