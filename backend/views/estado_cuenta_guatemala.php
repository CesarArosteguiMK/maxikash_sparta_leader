<?php
session_start();
date_default_timezone_set('America/Mexico_City');
$layoutVendorLite = true;

/* ----------------------
   Helpers locales
   ---------------------- */
function format_currency($v) {
    return 'Q' . number_format((float)$v, 2, '.', ',');
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

/** Vencimiento de la cuota en la misma semana ISO que hoy (America/Mexico_City). */
function fechaCuotaEnSemanaActual($fechaStr) {
    if ($fechaStr === null || $fechaStr === '') {
        return false;
    }
    $ts = strtotime((string) $fechaStr);
    if ($ts === false) {
        return false;
    }
    try {
        $tz = new DateTimeZone('America/Mexico_City');
        $tCuota = new DateTime('@' . $ts);
        $tCuota->setTimezone($tz);
        $tHoy = new DateTime('now', $tz);
        return $tCuota->format('o-W') === $tHoy->format('o-W');
    } catch (Throwable $e) {
        return false;
    }
}

/* Asegurar que $tabla exista */
if (!isset($tabla) || !is_array($tabla)) $tabla = [];

/* ----------------------
   Parsear datos Guatemala
   ---------------------- */
$datosCliente    = [];
$datosAdicionales = [];

/* Fila directa de registro_croop (pkey_credito, pkey_cliente, request_cliente, etc.) */
$regGuat     = $datosGuat['datos'][0] ?? [];
$pkeyCredito = $regGuat['pkey_credito'] ?? '';
$pkeyCliente = $regGuat['pkey_cliente'] ?? '';

/* Parsear request_cliente desde la fila de Guatemala */
if (!empty($regGuat['request_cliente'])) {
    $datosCliente = json_decode($regGuat['request_cliente'], true) ?? [];
}

/* Construir nombre completo — las claves del JSON Guatemala son Nombre, APP, APM */
$nombreCliente = trim(
    ($datosCliente['Nombre'] ?? '') . ' ' .
    ($datosCliente['APP']    ?? '') . ' ' .
    ($datosCliente['APM']    ?? '')
);
if (empty($nombreCliente)) {
    /* Fallback a nombre_completo de referencias (__SPARTA_SECRET_REDACTED__) si existe */
    $nombreCliente = $referencias['datos'][0]['nombre_completo'] ?? '';
}

if (!empty($regGuat['request_adicional'])) {
    $adicionales = json_decode($regGuat['request_adicional'], true) ?? [];
    foreach ($adicionales as $item) {
        $datosAdicionales[$item['nombre']] = $item['valor'];
    }
}

/* Parsear domicilio desde request_act_credito de registro_croop */
$domicilioGuat = [];
if (!empty($regGuat['request_act_credito'])) {
    $domicilioGuat = json_decode($regGuat['request_act_credito'], true) ?? [];
}

/* Dirección adicional desde request_cliente (datos al momento del registro) */
$direccionClienteGuat = [];
if (!empty($datosCliente['Ciudad']) || !empty($datosCliente['Calle_Numero'])) {
    $direccionClienteGuat = [
        'Calle_Numero'  => $datosCliente['Calle_Numero']   ?? '',
        'Ciudad'        => $datosCliente['Ciudad']          ?? '',
        'Codigo_Postal' => $datosCliente['Codigo_Postal']   ?? '',
        'FK_Estado'     => $datosCliente['FK_Estado']       ?? '',
        'FK_Delegacion' => $datosCliente['FK_Delegacion']   ?? '',
    ];
}

/* ----------- CROOP API: Saldos del contrato (Bandera=445) ----------- */
$saldoGT = [];
if (!empty($apiSaldos) && is_array($apiSaldos)) {
    foreach ($apiSaldos as $s) {
        if (isset($s['PKey']) && (string)$s['PKey'] === (string)$pkeyCredito) {
            $saldoGT = $s;
            break;
        }
    }
    if (empty($saldoGT)) $saldoGT = $apiSaldos[0] ?? [];
}

/* ----------- CROOP API: Tabla de amortización (Bandera=401) ----------- */
$amortRows   = is_array($apiAmortizacion ?? null) ? ($apiAmortizacion ?? []) : [];
$totalCuotas = count($amortRows);
$primeraFila = $amortRows[0] ?? [];
$ultimaFila  = !empty($amortRows) ? $amortRows[array_key_last($amortRows)] : [];

/* Helper: convierte "$8,674.99" → float */
$parseMontoGT = function($v) { return (float)preg_replace('/[^0-9.]/', '', $v ?? '0'); };

/* Poblar $dataEstadoCuenta — referenciado en el HTML */
$dataEstadoCuenta = [
    'statusCredito'     => $saldoGT['StatusDesc']              ?? '',
    'montoOtorgado'     => $parseMontoGT($saldoGT['ValorCredito']     ?? ''),
    'cuota'             => $parseMontoGT($saldoGT['PagoPeriodo'] ?: ($primeraFila['PagoRecibido'] ?? '')),  // ?: para fallback en 0 también
    'idExterno'         => $saldoGT['IdExterno']               ?? '',
    'fechaInicio'       => $primeraFila['FechaGeneracion']     ?? null,
    'primerVencimiento' => $primeraFila['FechaLimitePago']     ?? null,
    'ultimoVencimiento' => $ultimaFila['FechaLimitePago']      ?? null,
    'fechaLiquidacion'  => null,
    'referenciaSTP'     => $saldoGT['IdExterno']               ?? '',
    'idCredito'         => $pkeyCredito                        ?? '',
];

/* Poblar $dataOtrosDatos — referenciado en el HTML */
$dataOtrosDatos = [
    'saldoTotalVencido'   => $parseMontoGT($saldoGT['PagosVencidosMonto']  ?? ''),
    'saldoTotalVigente'   => $parseMontoGT($saldoGT['CapitalPendientePago'] ?? ''),
    'cuotasContratadas'   => $totalCuotas,
    'cuotasPagadas'       => (int)($saldoGT['PagosRealizados']             ?? 0),
    'saldoVigenteCapital' => $parseMontoGT($saldoGT['CapitalPendientePago'] ?? ''),
    'saldoParaLiquidarV2' => $parseMontoGT($saldoGT['TotalLiquidar']        ?? ''),
    'diasMoraMaximo'      => 0,
    'diasMora'            => 0,
];

$cuotasContratadas = (int)$dataOtrosDatos['cuotasContratadas'];
$cuotasPagadas     = (int)$dataOtrosDatos['cuotasPagadas'];
$cuotasFaltantes   = $cuotasContratadas - $cuotasPagadas;

$porcentajeAvance = 0;
if ($cuotasContratadas > 0) {
    $porcentajeAvance = min(100, round(($cuotasPagadas / $cuotasContratadas) * 100));
}

/* ----------- Construir $tabla desde amortización CROOP (Bandera=401) + pagos reales (Bandera=404) ----------- */
if (empty($tabla) && !empty($amortRows)) {
    // Indexar pagos reales por Periodo para cruce rápido
    $pagosPorPeriodo = [];
    $apiPagos = $apiPagos ?? [];
    foreach ($apiPagos as $pago) {
        $periodo = (int)($pago['Periodo'] ?? 0);
        if ($periodo > 0) {
            $pagosPorPeriodo[$periodo][] = $pago;
        }
    }

    $tabla    = [];
    $idxAmort = 0;
    foreach ($amortRows as $amortRow) {

        $idxAmort++;
        $periodo    = (int)($amortRow['Periodo'] ?? $idxAmort);
        $hayPago    = trim($amortRow['HayPago'] ?? '');
        $montoCargo = $parseMontoGT($amortRow['PagoRecibido'] ?? '0');

        // Pagos reales de Bandera=404 para este periodo
        $pagosReales = $pagosPorPeriodo[$periodo] ?? [];

        // Prioridad doble: "Pagado" en 401 O dentro del conteo PagosRealizados de 445 O hay pagos en 404
        $esPagado = (mb_strtolower($hayPago) === 'pagado')
                 || ($idxAmort <= $cuotasPagadas)
                 || !empty($pagosReales);

        $aplicados    = [];
        $totalPagado  = 0.0;

        if (!empty($pagosReales)) {
            // Usar datos reales del Bandera=404
            foreach ($pagosReales as $p) {
                $montoReal = (float)($p['Monto'] ?? 0);
                $totalPagado += $montoReal;
                $fechaAplicacion = isset($p['Fecha_Aplicacion'])
                    ? date('Y-m-d', strtotime($p['Fecha_Aplicacion']))
                    : null;
                $aplicados[] = [
                    'idPago'         => $p['FK_Pago'] ?? null,
                    'montoPago'      => $montoReal,
                    'aplicado'       => $montoReal,
                    'fechaRegistro'  => $fechaAplicacion,
                    'fechaPago'      => $fechaAplicacion,
                    'capital'        => (float)($p['CapitalPagado']   ?? 0),
                    'interes'        => (float)($p['InteresPagado']   ?? 0),
                    'descripcion'    => $p['Descripcion'] ?? '',
                    'es_sobrante'    => false,
                    'extemporaneos'  => 0.0,
                    'gasto_cobranza' => false,
                    'cc_invalido'    => false,
                ];
            }
        } elseif ($esPagado) {
            // No hay detalle real pero los otros indicadores dicen pagado
            $totalPagado = $montoCargo;
            $aplicados[] = [
                'montoPago'      => $montoCargo,
                'aplicado'       => $montoCargo,
                'fechaRegistro'  => $amortRow['FechaLimitePago'] ?? null,
                'fechaPago'      => $amortRow['FechaLimitePago'] ?? null,
                'es_sobrante'    => false,
                'extemporaneos'  => 0.0,
                'gasto_cobranza' => false,
                'cc_invalido'    => false,
            ];
        }

        $pendiente = round(max($montoCargo - $totalPagado, 0), 2);

        $tabla[] = [
            'cuota'       => $periodo,
            'fecha'       => $amortRow['FechaLimitePago'] ?? null,
            'monto_cargo' => $montoCargo,
            'capital'     => $parseMontoGT($amortRow['CapitalPagado']  ?? '0'),
            'interes'     => $parseMontoGT($amortRow['InteresPagado']  ?? '0'),
            'seguro'      => 0.0,
            'aplicados'   => $aplicados,
            'total_pagado'=> round($totalPagado, 2),
            'pendiente'   => $pendiente,
            'excedente'   => 0.0,
            'raw_cargo'   => $amortRow,
        ];
    }

    // Calcular fecha del último pago completo DESPUÉS de construir $tabla
    $fechaUltimoPagoCompleto = null;
    foreach ($tabla as $fila) {
        $pendiente = safe($fila['pendiente'], 0.0);
        $aplicados = safe($fila['aplicados'], []);
        if ($pendiente <= 0 && !empty($aplicados)) {
            foreach ($aplicados as $a) {
                if (!empty($a['fechaRegistro'])) {
                    $ts = strtotime($a['fechaRegistro']);
                    if ($ts && (!$fechaUltimoPagoCompleto || $ts > strtotime($fechaUltimoPagoCompleto))) {
                        $fechaUltimoPagoCompleto = $a['fechaRegistro'];
                    }
                }
            }
        }
    }
}
?>
<script>
/* ============================================================
   DEBUG CROOP API — visible en DevTools > Console
   ============================================================ */
console.group('%c[CROOP DEBUG]', 'color:#0ea5e9;font-weight:bold;font-size:13px');
console.log('Trace general:',   <?= json_encode($debugCroop ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>);
console.log('saldoGT (445):',   <?= json_encode($saldoGT ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>);
console.log('amortizacion (401) primeras 3 filas:', <?= json_encode(array_slice($amortRows ?? [], 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>);
console.log('pagos (404) primeros 3:', <?= json_encode(array_slice($apiPagos ?? [], 0, 3), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>);
console.log('dataEstadoCuenta:', <?= json_encode($dataEstadoCuenta ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>);
console.log('dataOtrosDatos:',  <?= json_encode($dataOtrosDatos ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>);
console.log('fechaUltimoPago:', <?= json_encode($fechaUltimoPagoCompleto ?? null) ?>);
console.log('tabla[0]:', <?= json_encode($tabla[0] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>);
console.groupEnd();
</script>

<style>
    /* ==========================
   GLOBAL
   ========================== */
    html, body {
        overflow-y: auto;
    }

    /* ==========================
       LIQUID GLASS
   ========================== */
    .estado-cuenta-page .sidebar-cliente > .card,
    .estado-cuenta-page .sidebar-cliente .card,
    .estado-cuenta-page .sidebar-cliente .user-avatar-section .card {
        background: rgba(255, 255, 255, 0.88) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border-radius: 14px !important;
    }
    html.dark-mode .estado-cuenta-page .sidebar-cliente > .card,
    html.dark-mode .estado-cuenta-page .sidebar-cliente .card,
    html.dark-mode .estado-cuenta-page .sidebar-cliente .user-avatar-section .card,
    body.dark-mode .estado-cuenta-page .sidebar-cliente > .card,
    body.dark-mode .estado-cuenta-page .sidebar-cliente .card,
    body.dark-mode .estado-cuenta-page .sidebar-cliente .user-avatar-section .card {
        background: rgba(30, 41, 59, 0.88) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        border-radius: 14px !important;
    }
    .estado-cuenta-page .sidebar-cliente .badge-container-ids .badge {
        border-radius: 10px !important;
    }
    .estado-cuenta-page .sidebar-cliente .user-avatar-section .progress {
        border-radius: 10px !important;
        overflow: hidden;
    }
    .estado-cuenta-page .sidebar-cliente .user-avatar-section .progress-bar {
        border-radius: 10px !important;
    }
    .estado-cuenta-page .sidebar-cliente .d-flex.justify-content-between.my-3 > div {
        border-radius: 12px !important;
    }
    .estado-cuenta-page .sidebar-cliente .btn-outline-primary {
        border-radius: 12px !important;
    }
    .estado-cuenta-page .col-xl-8 .card {
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        border-radius: 14px;
        overflow: hidden;
    }
    html.dark-mode .estado-cuenta-page .col-xl-8 .card,
    body.dark-mode .estado-cuenta-page .col-xl-8 .card {
        background: rgba(30, 41, 59, 0.9) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 14px;
    }
    .estado-cuenta-page .tabla-scrollable {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .estado-cuenta-page .tabla-scrollable thead th {
        background: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    html.dark-mode .estado-cuenta-page .tabla-scrollable,
    body.dark-mode .estado-cuenta-page .tabla-scrollable {
        background: rgba(30, 41, 59, 0.85) !important;
    }
    html.dark-mode .estado-cuenta-page .tabla-scrollable thead th,
    body.dark-mode .estado-cuenta-page .tabla-scrollable thead th {
        background: rgba(30, 41, 59, 0.95) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .estado-cuenta-page .reference-card {
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    html.dark-mode .estado-cuenta-page .reference-card,
    body.dark-mode .estado-cuenta-page .reference-card {
        background: rgba(30, 41, 59, 0.9) !important;
    }
    .estado-cuenta-page .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 {
        background: rgba(248, 249, 250, 0.9) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .estado-cuenta-page .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    html.dark-mode .estado-cuenta-page .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6,
    body.dark-mode .estado-cuenta-page .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 {
        background: rgba(30, 41, 59, 0.7) !important;
    }
    html.dark-mode .estado-cuenta-page .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div,
    body.dark-mode .estado-cuenta-page .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div {
        background: rgba(51, 65, 85, 0.8) !important;
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

    .sidebar-cliente .card-body {
        padding: 1rem !important;
    }

    @media (min-width: 768px) {
        .sidebar-cliente .card-body {
            padding: 1.25rem !important;
        }
    }

    .user-avatar-section .card {
        margin-bottom: 1rem !important;
        transition: all 0.2s ease;
    }

    .user-avatar-section .card .card-body {
        padding: 0.75rem !important;
    }

    .badge-container-ids {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-bottom: 0.75rem;
    }

    .badge-container-ids .badge {
        flex: 1;
        min-width: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-size: 0.75rem;
        padding: 0.4em 0.8em;
        transition: all 0.2s ease;
    }

    .user-avatar-section .h6 {
        font-size: 1rem;
        line-height: 1.3;
        margin-bottom: 0.5rem;
    }

    .user-avatar-section .progress {
        height: 0.5rem;
        margin-bottom: 0.75rem;
    }

    .user-avatar-section small {
        font-size: 0.8rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .sidebar-cliente .d-flex.justify-content-between.my-3 {
        flex-wrap: wrap;
        gap: 0.75rem;
        margin: 1rem 0;
    }

    .sidebar-cliente .d-flex.justify-content-between.my-3 > div {
        flex: 1 1 calc(50% - 0.75rem);
        min-width: 0;
    }

    .sidebar-cliente .d-flex.justify-content-between.my-3 h5 {
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }

    .sidebar-cliente .d-flex.justify-content-between.my-3 span.small {
        font-size: 0.75rem;
    }

    .sidebar-cliente .info-compact {
        margin: 0.75rem 0;
    }

    .sidebar-cliente .info-compact li {
        display: grid;
        grid-template-columns: auto 1fr;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.15rem;
        padding: 0.1rem 0;
    }

    .sidebar-cliente .info-compact i.fa-lg {
        grid-column: 1;
        text-align: center;
        font-size: 0.9rem;
        color: #6c757d;
        width: 1.2rem;
    }

    .sidebar-cliente .info-compact .info-label {
        grid-column: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        min-height: 1rem;
    }

    .sidebar-cliente .info-compact .info-label span:first-child {
        font-weight: 500;
        color: #495057;
        white-space: nowrap;
        font-size: 0.85rem;
    }

    .sidebar-cliente .info-compact .info-label span:last-child {
        font-weight: 600;
        color: #212529;
        text-align: right;
        padding-left: 1rem;
        white-space: nowrap;
        font-size: 0.85rem;
        min-width: max-content;
    }

    .sidebar-cliente .btn-outline-primary {
        padding: 0.6rem 1.25rem !important;
        font-size: 0.85rem;
        margin: 0.75rem 0.5rem !important;
        border-radius: 8px;
        border-width: 1.5px;
        width: calc(100% - 1rem) !important;
        box-sizing: border-box;
    }

    .sidebar-cliente .btn-outline-primary i {
        font-size: 0.95rem;
        margin-right: 0.5rem;
    }

    @media (max-width: 1600px) {
        .sidebar-cliente .card-body { padding: 0.9rem !important; }
        .badge-container-ids .badge { font-size: 0.7rem; padding: 0.35em 0.6em; }
        .user-avatar-section .h6 { font-size: 0.95rem; }
        .sidebar-cliente .info-compact .info-label span:first-child,
        .sidebar-cliente .info-compact .info-label span:last-child { font-size: 0.8rem; }
        .sidebar-cliente .d-flex.justify-content-between.my-3 h5 { font-size: 0.9rem; }
    }

    @media (max-width: 1400px) {
        .sidebar-cliente .card-body { padding: 0.8rem !important; }
        .user-avatar-section .card .card-body { padding: 0.6rem !important; }
        .badge-container-ids .badge { font-size: 0.65rem; padding: 0.3em 0.5em; }
        .user-avatar-section .h6 { font-size: 0.9rem; }
        .sidebar-cliente .info-compact li { margin-bottom: 0.4rem; }
        .sidebar-cliente .info-compact .info-label span:first-child,
        .sidebar-cliente .info-compact .info-label span:last-child { font-size: 0.75rem; }
        .sidebar-cliente .info-compact i.fa-lg { font-size: 0.85rem !important; }
        .sidebar-cliente .d-flex.justify-content-between.my-3 h5 { font-size: 0.85rem; }
        .sidebar-cliente .btn-outline-primary { font-size: 0.8rem; padding: 0.5rem 1rem !important; }
    }

    @media (max-width: 1200px) {
        .badge-container-ids { flex-direction: column; gap: 0.3rem; }
        .badge-container-ids .badge { width: 100%; max-width: 100%; text-align: center; }
        .sidebar-cliente .d-flex.justify-content-between.my-3 > div { flex: 1 1 100%; }
    }

    @media (max-width: 768px) {
        .sidebar-cliente .card-body { padding: 0.75rem !important; }
        .badge-container-ids { flex-direction: row; gap: 0.5rem; }
        .badge-container-ids .badge { flex: 1 1 calc(50% - 0.5rem); max-width: calc(50% - 0.5rem); }
        .sidebar-cliente .d-flex.justify-content-between.my-3 { flex-direction: column; gap: 0.5rem; }
        .sidebar-cliente .btn-outline-primary { padding: 0.5rem 1rem !important; margin: 0.5rem 0.25rem !important; width: calc(100% - 0.5rem) !important; }
    }

    .col-xl-8.col-lg-7 .d-flex.justify-content-between.align-items-center.mb-3 > .d-flex.gap-2 {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    .btn-dictaminar, .btn-condonar, .btn-notas {
        display: flex !important;
        visibility: visible !important;
    }

    .btn-outline-secondary { display: inline-flex !important; }

    /* ==========================
       TABLA
       ========================== */
    .cuotas-table, .cuotas-table td, .cuotas-table th { color: #000 !important; }

    .cuotas-table td, .cuotas-table th {
        font-size: 0.80rem !important;
        line-height: 1.1rem;
    }

    .cuotas-table ul li, .cuotas-table .fecha-pago, .cuotas-table .fecha-cuota {
        font-size: 0.75rem !important;
    }

    .cuotas-table .badge {
        font-size: 0.70rem !important;
        padding: 0.35em 0.6em !important;
        border-radius: 10px;
        font-weight: 500;
    }

    .estado-cuenta-page .tabla-scrollable {
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }

    @media (min-width: 992px) {
        .tabla-scrollable {
            max-height: calc(97.5vh - 240px);
            overflow-y: auto;
            overflow-x: hidden;
            position: sticky;
            top: 120px;
            z-index: 5;
            scrollbar-gutter: stable both-edges;
        }
        .tabla-scrollable thead th {
            position: sticky;
            top: 0;
            z-index: 8;
        }
    }

    @media (max-width: 991px) {
        .tabla-scrollable, .tabla-scrollable thead th { position: static !important; top: auto !important; }
        .tabla-scrollable { max-height: none; overflow-x: auto; overflow-y: visible; -webkit-overflow-scrolling: touch; }
        .cuotas-table { min-width: 780px; }
    }

    @media (max-width: 768px) {
        .cuotas-table td, .cuotas-table th { font-size: 0.65rem !important; padding: 0.35rem 0.4rem !important; }
        .cuotas-table ul li { font-size: 0.6rem !important; line-height: 0.9rem; }
        .cuotas-table .badge { font-size: 0.6rem !important; padding: 0.25em 0.4em !important; border-radius: 8px; }
    }

    @media (min-width: 1400px) and (max-height: 900px) {
        .sidebar-cliente { top: 110px; }
        .tabla-scrollable { top: 110px; max-height: calc(100vh - 220px); }
    }

    @media (min-width: 1400px) and (max-width: 1599px) and (max-height: 900px) {
        .sidebar-cliente .info-compact .info-label span:first-child,
        .sidebar-cliente .info-compact .info-label span:last-child { font-size: 0.72rem !important; }
        .sidebar-cliente .info-compact i.fa-lg { font-size: 0.85rem !important; }
    }

    @media (max-width: 991px) { .cuotas-table { min-width: 900px; } }

    .cuotas-table { table-layout: fixed; width: 100%; }

    /* ===== Reference Card ===== */
    .reference-card {
        border-radius: 14px;
        padding: 1.25rem 1.25rem 1.4rem;
        position: relative;
        border: 1px solid rgba(0,0,0,.06);
        box-shadow: 0 6px 18px rgba(0,0,0,.08);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .reference-card:hover { transform: translateY(-6px); box-shadow: 0 14px 34px rgba(0,0,0,.18); }
    .reference-header { display: flex; align-items: center; gap: .6rem; font-weight: 600; font-size: .95rem; margin-bottom: .5rem; }
    .reference-divider { border-top: 1px dashed rgba(0,0,0,.15); margin: .6rem 0 .8rem; }
    .info-line { display: flex; justify-content: space-between; font-size: .82rem; padding: .25rem 0; }
    .info-line span { color: #6c757d; }
    .info-line strong { font-weight: 600; color: #212529; }
    .reference-badge { position: absolute; top: 12px; right: 14px; font-size: .65rem; }

    /* ==========================
   NOTAS
   ========================== */
    .nota-card { background: #fff9db; border-left: 6px solid #f0ad4e; border-radius: 10px; padding: 14px 16px; box-shadow: 0 6px 14px rgba(0,0,0,.12); position: relative; transition: transform .2s ease, box-shadow .2s ease; }
    .nota-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,.2); }
    .nota-fecha { font-size: .7rem; color: #856404; margin-bottom: 6px; }
    .nota-texto { font-size: .85rem; color: #212529; margin-bottom: 10px; white-space: pre-wrap; }
    .nota-autor { font-size: .7rem; color: #6c757d; text-align: right; }

    .btn-notas { width: 42px; height: 42px; border-radius: 50%; background: #fff3cd; border: 1px solid #ffecb5; color: #f0ad4e; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(240,173,78,.35); transition: all .25s ease; }
    .btn-notas i { font-size: 1.1rem; }
    .btn-notas:hover { background: #ffe69c; color: #d39e00; transform: translateY(-2px); box-shadow: 0 8px 18px rgba(240,173,78,.55); }
    .btn-notas .badge { font-size: .6rem; padding: .35em .45em; }

    .btn-condonar { width: 42px; height: 42px; border-radius: 50%; background: #e6f4ea; border: 1px solid #b7dfc3; color: #28a745; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(40,167,69,.35); transition: all .25s ease; }
    .btn-condonar i { font-size: 1.1rem; }
    .btn-condonar:hover { background: #c8ead3; color: #1e7e34; transform: translateY(-2px); box-shadow: 0 8px 18px rgba(40,167,69,.55); }

    .billete { position: fixed; font-size: 1.5rem; animation: volarBillete 1s ease-out forwards; pointer-events: none; z-index: 9999; }
    @keyframes volarBillete { 0% { opacity: 1; transform: translateY(0) scale(1); } 100% { opacity: 0; transform: translateY(-120px) scale(.4); } }

    .btn-dictaminar { width: 42px; height: 42px; border-radius: 50%; background: #e7f0ff; border: 1px solid #b6d4fe; color: #0d6efd; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(13,110,253,.35); transition: all .25s ease; }
    .btn-dictaminar i { font-size: 1.1rem; }
    .btn-dictaminar:hover { background: #cfe2ff; color: #084298; transform: translateY(-2px); box-shadow: 0 8px 18px rgba(13,110,253,.55); }

    #modalDirecciones { z-index: 1090 !important; }
    #modalDirecciones .modal-dialog { z-index: 1091 !important; margin: 1.75rem auto !important; }
    .modal-backdrop.show { opacity: 0.5 !important; background: rgba(0, 0, 0, 0.5) !important; }
    .scrim-condonar-estado-cuenta { z-index: 9998 !important; background: rgba(0, 0, 0, 0.5) !important; }
    body.modal-condonar-open .swal2-container,
    body.modal-condonar-parcial-open .swal2-container { z-index: 10001 !important; }

    #modalCondonar .modal-content, #modalCondonarParcial .modal-content { background: rgba(255, 255, 255, 0.85) !important; backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 12px; }
    #modalCondonar .modal-header, #modalCondonarParcial .modal-header { background: rgba(255, 255, 255, 0.4) !important; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-bottom: 1px solid rgba(0, 0, 0, 0.06); border-radius: 12px 12px 0 0; }
    #modalCondonar .modal-body, #modalCondonarParcial .modal-body { background: rgba(255, 255, 255, 0.5) !important; backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }
    #modalCondonar .modal-footer, #modalCondonarParcial .modal-footer { background: rgba(255, 255, 255, 0.4) !important; backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border-top: 1px solid rgba(0, 0, 0, 0.06); border-radius: 0 0 12px 12px; }

    @media (max-width: 576px) {
        .sidebar-cliente { order: -1 !important; margin-bottom: 1rem; }
        .col-xl-8.col-lg-7 { order: 1 !important; }
        .row { margin-bottom: 0.5rem; }
        .sidebar-cliente .card { margin-bottom: 0.75rem !important; }
        .badge-container-ids .badge { font-size: 0.65rem !important; padding: 0.35em 0.5em !important; }
        .col-xl-8.col-lg-7 .d-flex.justify-content-between.align-items-center.mb-3 { flex-direction: column; align-items: flex-start; margin-bottom: 0.75rem !important; }
        .col-xl-8.col-lg-7 .d-flex.justify-content-between.align-items-center.mb-3 h5 { font-size: 0.85rem; margin-bottom: 0.5rem; width: 100%; }
        .btn-notas, .btn-condonar, .btn-dictaminar { width: 36px !important; height: 36px !important; }
        .btn-notas i, .btn-condonar i, .btn-dictaminar i { font-size: 0.9rem !important; }
        .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div { flex: 1 1 calc(50% - 0.5rem); margin-bottom: 0.5rem; gap: 0.5rem; }
        .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 h5 { font-size: 0.8rem; }
        .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 span { font-size: 0.7rem; }
        .tabla-scrollable { margin-top: 0.5rem; }
        .cuotas-table td, .cuotas-table th { font-size: 0.6rem !important; padding: 0.3rem !important; }
        .cuotas-table .badge { font-size: 0.55rem !important; padding: 0.2em 0.3em !important; }
    }

    /* ==========================
   ACORDEÓN
   ========================== */
    .accordion-button::after { display: none !important; }
    .accordion-flush.custom-accordion { border: none; background: transparent; margin-top: 1.5rem !important; padding-top: 0.5rem; border-top: 1px solid rgba(0,0,0,0.05); }
    .accordion-button { background-color: #f8f9fa !important; border: 1px solid #dee2e6 !important; border-radius: 8px !important; padding: 0.75rem 1rem !important; font-weight: 500; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .accordion-button:not(.collapsed) { background-color: #e7f1ff !important; border-color: #b6d4fe !important; color: #0d6efd; box-shadow: 0 4px 8px rgba(13,110,253,0.1); }
    .accordion-button:focus { box-shadow: 0 0 0 3px rgba(13,110,253,0.25) !important; border-color: #86b7fe !important; }
    .accordion-arrow { transition: transform 0.3s ease; font-size: 0.85rem; color: #6c757d; margin-left: auto; }
    .accordion-button:not(.collapsed) .accordion-arrow { transform: rotate(180deg); color: #0d6efd; }
    .accordion-collapse { border: none !important; }
    .accordion-body { background-color: transparent; padding: 1rem !important; }
    .accordion-body .d-flex.justify-content-between.my-3 { margin-top: 0.75rem !important; margin-bottom: 1rem !important; }
    .accordion-body .info-compact { margin: 0.75rem 0 !important; }
    .accordion-body .btn-outline-primary { margin: 1.25rem 0.75rem 0.75rem 0.75rem !important; padding: 0.65rem 1.5rem !important; width: calc(100% - 1.5rem) !important; }
    .accordion-collapse { transition: all 0.35s ease !important; }
    .accordion-body { animation: fadeIn 0.3s ease; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

    .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 { padding: 1rem 1.25rem !important; border-radius: 10px; margin: 1.5rem 0 !important; border: 1px solid #e9ecef; gap: 1rem !important; }
    .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div { padding: 0.5rem 0.75rem; border-radius: 8px; border: 1px solid #f0f0f0; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }

    @media (max-width: 768px) {
        .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 { padding: 0.75rem !important; }
        .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div { padding: 0.4rem 0.6rem; }
        .sidebar-cliente .info-compact li { display: grid !important; grid-template-columns: 1.2rem minmax(140px, 1fr) auto; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; padding: 0.2rem 0; }
        .sidebar-cliente .info-compact i.fa-lg { grid-column: 1; font-size: 0.85rem; text-align: center; width: 1.2rem; }
        .sidebar-cliente .info-compact .info-label { display: flex !important; justify-content: space-between; align-items: center; width: 100%; }
        .sidebar-cliente .info-compact .info-label span:first-child { flex: 1; text-align: left; padding-right: 1rem; overflow: hidden; text-overflow: ellipsis; font-size: 0.8rem; }
        .sidebar-cliente .info-compact .info-label span:last-child { flex-shrink: 0; text-align: right; min-width: 100px; font-family: 'SF Mono', Monaco, Consolas, 'Liberation Mono', monospace; font-size: 0.85rem; }
    }

    @media (max-width: 400px) {
        .sidebar-cliente .info-compact li { grid-template-columns: 1rem minmax(120px, 1fr) auto; gap: 0.4rem; }
        .sidebar-cliente .info-compact i.fa-lg { font-size: 0.8rem; width: 1rem; }
        .sidebar-cliente .info-compact .info-label span:first-child { font-size: 0.75rem; min-width: 120px; }
        .sidebar-cliente .info-compact .info-label span:last-child { font-size: 0.8rem; }
    }

    .sidebar-cliente .info-compact .info-label span:last-child { font-family: 'Segoe UI', 'Roboto', 'SF Mono', Monaco, Consolas, monospace; font-variant-numeric: tabular-nums; }
</style>
<style>
body:not(.dark-mode) .d-flex.justify-content-around.flex-wrap.my-6 h5,
body:not(.dark-mode) .d-flex.justify-content-between.my-3 h5 { color: #212529 !important; }
body:not(.dark-mode) .d-flex.justify-content-around.flex-wrap.my-6 span,
body:not(.dark-mode) .d-flex.justify-content-between.my-3 span.small { color: #6c757d !important; }
body:not(.dark-mode) .sidebar-cliente .info-compact .info-label span:first-child { color: #495057 !important; }
body:not(.dark-mode) .sidebar-cliente .info-compact .info-label span:last-child { color: #212529 !important; }
body:not(.dark-mode) .sidebar-cliente .info-compact i.fa-lg { color: #6c757d !important; }

html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente {
    --ec-light-bg: #ffffff; --ec-light-border: #dee2e6; --ec-text: #212529; --ec-text-muted: #6c757d;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente > .card,
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente > .card,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card { background: var(--ec-light-bg) !important; color: var(--ec-text) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card-body,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card-body { color: var(--ec-text) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section .h6,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section .h6 { color: var(--ec-text) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section small,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section small { color: var(--ec-text-muted) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section .btn-link,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section .btn-link { color: #0d6efd !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente hr,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente hr { border-color: var(--ec-light-border) !important; opacity: 1; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .d-flex.justify-content-between.my-3 h5,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .d-flex.justify-content-between.my-3 h5 { color: var(--ec-text) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .d-flex.justify-content-between.my-3 span.small,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .d-flex.justify-content-between.my-3 span.small { color: var(--ec-text-muted) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact .info-label span:first-child,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact .info-label span:first-child { color: #495057 !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact .info-label span:last-child,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact .info-label span:last-child { color: var(--ec-text) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact i.fa-lg,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact i.fa-lg { color: var(--ec-text-muted) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-body,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-body { background: var(--ec-light-bg) !important; color: var(--ec-text) !important; }
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-button:not(.collapsed),
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-button:not(.collapsed) { background: #f8f9fa !important; color: var(--ec-text) !important; }
</style>
<style>
body:not(.dark-mode) .btn-dictaminar { background: #e7f0ff !important; border: 1px solid #b6d4fe !important; color: #0d6efd !important; }
body:not(.dark-mode) .btn-dictaminar:hover { background: #cfe2ff !important; color: #084298 !important; }
body:not(.dark-mode) .btn-condonar { background: #e6f4ea !important; border: 1px solid #b7dfc3 !important; color: #28a745 !important; }
body:not(.dark-mode) .btn-condonar:hover { background: #c8ead3 !important; color: #1e7e34 !important; }
body:not(.dark-mode) .btn-notas { background: #fff3cd !important; border: 1px solid #ffecb5 !important; color: #f0ad4e !important; }
body:not(.dark-mode) .btn-notas:hover { background: #ffe69c !important; color: #d39e00 !important; }
</style>
<style>
.cuotas-table .etiqueta-pago { color: #0d6efd !important; }
.cuotas-table .etiqueta-sobrante { color: #6c757d !important; font-weight: bold; }
.cuotas-table .etiqueta-aplicado { color: #05611d !important; }
.cuotas-table .contracargo-label { color: #d35400 !important; font-weight: 700; }
.cuotas-table .contracargo-valor { color: #d35400 !important; font-weight: 600; }
.cuotas-table .pago-revertido { text-decoration: line-through; opacity: 0.5; }
html.dark-mode .cuotas-table .etiqueta-pago,
body.dark-mode .cuotas-table .etiqueta-pago { color: #7eb8ff !important; }
html.dark-mode .cuotas-table .etiqueta-sobrante,
body.dark-mode .cuotas-table .etiqueta-sobrante { color: #94a3b8 !important; font-weight: bold; }
html.dark-mode .cuotas-table .etiqueta-aplicado,
body.dark-mode .cuotas-table .etiqueta-aplicado { color: #4ade80 !important; }
html.dark-mode .cuotas-table .contracargo-label,
body.dark-mode .cuotas-table .contracargo-label { color: #fb923c !important; font-weight: 700; }
html.dark-mode .cuotas-table .contracargo-valor,
body.dark-mode .cuotas-table .contracargo-valor { color: #fb923c !important; font-weight: 600; }

.cuotas-table tr.fila-semana-actual td {
    background-color: rgba(13, 110, 253, 0.14) !important;
    box-shadow: inset 3px 0 0 rgba(13, 110, 253, 0.72);
}
html.dark-mode .cuotas-table tr.fila-semana-actual td,
body.dark-mode .cuotas-table tr.fila-semana-actual td {
    background-color: rgba(125, 184, 255, 0.2) !important;
    box-shadow: inset 3px 0 0 rgba(125, 184, 255, 0.75);
}
.cuotas-table .icono-semana-cuota { color: #0d9488 !important; font-size: 0.95em; }
html.dark-mode .cuotas-table .icono-semana-cuota,
body.dark-mode .cuotas-table .icono-semana-cuota { color: #5eead4 !important; }

body.identificador-activo { padding-top: 5px; }
.banner-pais-guatemala { background: linear-gradient(135deg, #e3f2fd 0%, #fff 100%) !important; border-left: 5px solid #4997d0 !important; border-bottom: 2px solid rgba(73, 151, 208, 0.2) !important; }
.badge-pais-guatemala { background: linear-gradient(135deg, #4997d0 0%, #357abd 100%) !important; color: white !important; font-weight: 600; padding: 0.5em 1em; border-radius: 8px; box-shadow: 0 4px 12px rgba(73, 151, 208, 0.4); font-size: 0.85rem; letter-spacing: 0.5px; }
.estado-cuenta-page.pais-guatemala .card { border-left: 3px solid transparent; }
.estado-cuenta-page.pais-guatemala .sidebar-cliente > .card { border-left-color: #4997d0; }
.estado-cuenta-page.pais-guatemala .badge-container-ids .badge.bg-label-primary { background: rgba(73, 151, 208, 0.15) !important; color: #357abd !important; }
.estado-cuenta-page.pais-guatemala .border-primary { border-color: #4997d0 !important; }
</style>

<?php
$paisCodigo = $paisData['codigo_iso'] ?? 'mx';
$paisNombre = $paisData['nombre_pais'] ?? 'México';
$paisActivo = $paisData['pais_activo'] ?? 1;
$clasePais = strtolower($paisCodigo) === 'gt' ? 'pais-guatemala' : 'pais-' . strtolower($paisCodigo);
$colorBanner = strtolower($paisCodigo) === 'gt' ? '#4997d0' : '#d32f2f';
$gradienteBanner = strtolower($paisCodigo) === 'gt'
    ? 'linear-gradient(135deg, rgba(227, 242, 253, 0.95) 0%, rgba(255, 255, 255, 0.92) 100%)'
    : 'linear-gradient(135deg, rgba(255, 235, 238, 0.95) 0%, rgba(255, 255, 255, 0.92) 100%)';
?>

<?php if (strtolower($paisCodigo) === 'gt'): ?>
<script>document.body.classList.add('identificador-activo');</script>
<?php endif; ?>

<div class="row estado-cuenta-page <?= $clasePais ?>">

  <!-- BANNER PAÍS -->
  <div class="col-12 mb-3">
    <div class="card border-0 shadow-sm banner-pais-<?= strtolower($paisCodigo) ?>" style="background: <?= $gradienteBanner ?>; backdrop-filter: blur(10px); border-radius: 16px; overflow: hidden; border-left: 5px solid <?= $colorBanner ?>;">
      <div class="card-body py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
          <div class="d-flex align-items-center gap-3">
                        <?php if (strtolower($paisCodigo) !== 'gt'): ?>
                        <div class="d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: rgba(255,255,255,0.9); border-radius: 12px; box-shadow: 0 4px 14px rgba(0,0,0,0.1); border: 2px solid <?= $colorBanner ?>;">
                            <span class="fi fi-<?= htmlspecialchars($paisCodigo) ?> fis" style="font-size: 2.5rem; line-height: 1;"></span>
                        </div>
                        <?php endif; ?>
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0" style="font-weight: 700; color: #2c3e50; font-size: 1.5rem;">Estado de Cuenta</h4>
                <?php if (strtolower($paisCodigo) === 'gt'): ?>
                                <span class="badge badge-pais-guatemala"><i class="fa-solid fa-location-dot me-1"></i> GUATEMALA</span>
                <?php elseif (strtolower($paisCodigo) === 'mx'): ?>
                <span class="badge" style="background: linear-gradient(135deg, #006847 0%, #ce1126 100%); color: white; font-weight: 600; padding: 0.5em 1em; border-radius: 8px;"><i class="fa-solid fa-flag me-1"></i> MÉXICO</span>
                <?php endif; ?>
              </div>
                            <?php if (strtolower($paisCodigo) === 'gt'): ?>
                            <p class="mb-0" style="font-size: 0.95rem; font-weight: 700; color: #2c3e50;">
                                Recuerda que estamos cobrando en Quetzales!
                            </p>
                            <?php else: ?>
              <p class="mb-0 text-muted" style="font-size: 0.95rem; font-weight: 500;">
                <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($paisNombre) ?>
              </p>
                            <?php endif; ?>
            </div>
          </div>
          <div>
            <?php if ($paisActivo == 1): ?>
              <span class="badge bg-success" style="font-size: 0.9rem; padding: 0.6em 1.2em; border-radius: 10px; box-shadow: 0 4px 10px rgba(40,167,69,0.3);">
                <i class="fa-solid fa-circle-check me-1"></i> País Activo
              </span>
            <?php else: ?>
              <span class="badge bg-warning" style="font-size: 0.9rem; padding: 0.6em 1.2em; border-radius: 10px; box-shadow: 0 4px 10px rgba(255,193,7,0.3);">
                <i class="fa-solid fa-circle-exclamation me-1"></i> País Inactivo
              </span>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SIDEBAR CLIENTE -->
  <div class="col-xl-4 col-lg-5 order-1 order-lg-0 sidebar-cliente">
    <div class="card mb-6">
        <div class="card-body">

            <!-- SECCIÓN AVATAR -->
            <div class="user-avatar-section">
                <div class="card mb-3 border border-2 border-primary rounded primary-shadow">
                    <div class="card-body">
                        <div class="badge-container-ids">
                            <span class="badge bg-label-primary">ID Crédito: <?= htmlspecialchars($pkeyCredito) ?></span>
                            <span class="badge bg-label-secondary">ID Cliente: <?= htmlspecialchars($pkeyCliente) ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="h6 mb-0"><?= htmlspecialchars($nombreCliente) ?></span>
                        </div>
                        <div class="progress mb-1" title="<?= $porcentajeAvance ?>%">
                            <div class="progress-bar bg-primary" role="progressbar"
                                 style="width: <?= $porcentajeAvance ?>%;"
                                 aria-valuenow="<?= $porcentajeAvance ?>" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <small class="d-flex align-items-center gap-2">
                            <i class="fa fa-phone text-primary"></i>
                            <?php
                            $cel = preg_replace('/\D/', '', $datosCliente["celular"] ?? $datosCliente["telefono"] ?? '');
                            if (strlen($cel) === 10) {
                                $cel = sprintf("(%s) %s-%s", substr($cel, 0, 2), substr($cel, 2, 4), substr($cel, 6, 4));
                            } elseif (strlen($cel) === 8) {
                                $cel = sprintf("%s-%s", substr($cel, 0, 4), substr($cel, 4, 4));
                            }
                            echo htmlspecialchars($cel);
                            ?>
                            <i class="fa fa-location text-primary"></i>
                            <button type="button" class="btn btn-link text-primary p-0"
                                    onclick="abrirModalDirecciones()">
                                Direcciones
                            </button>
                            <span class="text-nowrap">
                                <i class="fa fa-id-card text-primary"></i>
                                RFC: <?= htmlspecialchars(trim($datosCliente['RFC'] ?? '') ?: '—') ?>
                            </span>
                        </small>
                    </div>
                </div>
            </div>

            <!-- VERSIÓN DESKTOP -->
            <div class="d-none d-lg-block desktop-info">
                <div class="d-flex justify-content-between flex-nowrap my-3 gap-1 gap-md-1">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-label-info rounded w-px-40 h-px-40">
                                <span style="font-weight:700;font-size:1.1rem;">Q</span>
                            </div>
                        </div>
                        <div class="text-truncate">
                            <h5 class="mb-0 text-truncate"><?= htmlspecialchars($dataEstadoCuenta["statusCredito"] ?? '') ?></h5>
                            <span class="small">Estatus Crédito</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar">
                            <div class="avatar-initial bg-label-danger rounded w-px-40 h-px-40">
                                <span style="font-weight:700;font-size:1.1rem;">Q</span>
                            </div>
                        </div>
                        <div class="text-end text-truncate">
                            <h5 class="mb-0 text-truncate"><?= format_currency($dataOtrosDatos["saldoTotalVencido"] ?? 0) ?></h5>
                            <span class="small">Saldo Total Vencido</span>
                        </div>
                    </div>
                </div>

                <hr class="my-2 w-100">
                <small class="card-text text-uppercase text-body-secondary small">Información del Crédito</small>
                <ul class="list-unstyled my-1 py-1 info-compact">
                    <li>
                        <i class="fa fa-money-bill fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Monto Otorgado:</span>
                            <span>Q<?= number_format($dataEstadoCuenta["montoOtorgado"] ?? 0, 2, '.', ',') ?></span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-money-bill-wave fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Saldo Total Pagado:</span>
                            <span><?= format_currency((float)($dataEstadoCuenta["montoOtorgado"] ?? 0) - (float)($dataOtrosDatos["saldoTotalVigente"] ?? 0)) ?></span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-list-ol fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Cuotas Contratadas:</span>
                            <span><?= htmlspecialchars($dataOtrosDatos["cuotasContratadas"] ?? '') ?> cuotas</span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-check-circle fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Cuotas Pagadas:</span>
                            <span><?= htmlspecialchars($dataOtrosDatos["cuotasPagadas"] ?? '') ?> cuotas</span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-hourglass fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Cuotas Faltantes:</span>
                            <span><?= $cuotasFaltantes ?> cuotas</span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-credit-card fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Saldo Vigente Capital:</span>
                            <span><?= format_currency($dataOtrosDatos["saldoVigenteCapital"] ?? 0) ?></span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-credit-card fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Saldo para Liquidar:</span>
                            <span><?= format_currency($dataOtrosDatos["saldoParaLiquidarV2"] ?? 0) ?></span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-exclamation-triangle fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Mora Máximo:</span>
                            <span><?= htmlspecialchars($dataOtrosDatos["diasMoraMaximo"] ?? 0) ?> días</span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-clock fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Mora:</span>
                            <span><?= htmlspecialchars($dataOtrosDatos["diasMora"] ?? 0) ?> días</span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-calendar-alt fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Fecha Inicio:</span>
                            <span><?= format_date($dataEstadoCuenta["fechaInicio"] ?? null) ?></span>
                        </div>
                    </li>

                    <hr class="my-2 w-100">

                    <li>
                        <i class="fa fa-calendar-day fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Primer Vencimiento:</span>
                            <span><?= format_date($dataEstadoCuenta["primerVencimiento"] ?? null) ?></span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-calendar-check fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Último Vencimiento:</span>
                            <span><?= format_date($dataEstadoCuenta["ultimoVencimiento"] ?? null) ?></span>
                        </div>
                    </li>
                    <li>
                        <i class="fa fa-calendar-check fa-lg"></i>
                        <div class="info-label">
                            <span class="fw-medium">Fecha de Liquidación:</span>
                            <span><?= format_date($dataEstadoCuenta["fechaLiquidacion"] ?? null) ?></span>
                        </div>
                    </li>



                    <button type="button"
                            class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2"
                            data-bs-toggle="modal" data-bs-target="#modalRFC">
                        <i class="fa fa-id-card fa-lg"></i>
                        <strong>Ver referencias del cliente</strong>
                    </button>
                </ul>
            </div>

            <!-- VERSIÓN MÓVIL -->
            <div class="d-lg-none mobile-info">
                <div class="accordion accordion-flush custom-accordion" id="accordionInfoCredito">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingInfoCredito">
                            <button class="accordion-button collapsed p-3"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapseInfoCredito"
                                    aria-expanded="false"
                                    aria-controls="collapseInfoCredito">
                                <div class="d-flex align-items-center w-100">
                                    <i class="fa fa-info-circle me-2 text-primary"></i>
                                    <span class="fw-semibold flex-grow-1">Información Detallada</span>
                                    <i class="fa fa-chevron-down accordion-arrow ms-2"></i>
                                </div>
                            </button>
                        </h2>
                        <div id="collapseInfoCredito"
                             class="accordion-collapse collapse"
                             aria-labelledby="headingInfoCredito"
                             data-bs-parent="#accordionInfoCredito">
                            <div class="accordion-body p-0 pt-3">
                                <div class="d-flex justify-content-between flex-nowrap my-3 gap-1 gap-md-1">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar">
                                            <div class="avatar-initial bg-label-info rounded w-px-40 h-px-40">
                                                <span style="font-weight:700;font-size:1.1rem;">Q</span>
                                            </div>
                                        </div>
                                        <div class="text-truncate">
                                            <h5 class="mb-0 text-truncate"><?= htmlspecialchars($dataEstadoCuenta["statusCredito"] ?? '') ?></h5>
                                            <span class="small">Estatus Crédito</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar">
                                            <div class="avatar-initial bg-label-danger rounded w-px-40 h-px-40">
                                                <span style="font-weight:700;font-size:1.1rem;">Q</span>
                                            </div>
                                        </div>
                                        <div class="text-end text-truncate">
                                            <h5 class="mb-0 text-truncate"><?= format_currency($dataOtrosDatos["saldoTotalVencido"] ?? 0) ?></h5>
                                            <span class="small">Saldo Total Vencido</span>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-2 w-100">
                                <small class="card-text text-uppercase text-body-secondary small">Información del Crédito</small>
                                <ul class="list-unstyled my-1 py-1 info-compact">
                                    <li class="d-flex align-items-center mb-2">
                                        <i class="fa fa-money-bill fa-lg"></i>
                                        <span class="fw-medium mx-2">Monto Otorgado:</span>
                                        <span>Q<?= number_format($dataEstadoCuenta["montoOtorgado"] ?? 0, 2, '.', ',') ?></span>
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
                </div>
            </div>

        </div>
    </div>
  </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="col-xl-8 col-lg-7 order-0 order-lg-1" style="position: relative;">



        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0">Resumen general de pagos del cliente</h5>
            </div>

            <div class="d-flex gap-2">
                <?php if (isset($_SESSION['departamento'], $_SESSION['usuario_id']) && ($_SESSION['departamento'] == 2 || $_SESSION['usuario_id'] == 1)): ?>
                    <button type="button" class="btn btn-dictaminar position-relative"
                            data-bs-toggle="modal" data-bs-target="#modalDictamen" title="Dictaminar llamada">
                        <i class="fa fa-headset"></i>
                    </button>
                <?php endif; ?>

                <?php if (isset($_SESSION['departamento'], $_SESSION['usuario_id']) && (in_array((int)$_SESSION['departamento'], [2, 9], true) || $_SESSION['usuario_id'] == 1)): ?>
                    <button type="button" class="btn btn-condonar position-relative"
                            title="Condonar gastos de cobranza"
                            onclick="consultaGastosCondonables(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">
                        <i class="fa fa-hand-holding-usd"></i>
                    </button>
                <?php endif; ?>

                <?php if (isset($_SESSION['departamento'], $_SESSION['usuario_id']) && ($_SESSION['departamento'] == 2 || $_SESSION['usuario_id'] == 1)): ?>
                    <button type="button" class="btn btn-notas position-relative"
                            title="Notas del cliente"
                            onclick="consultaNotas(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">
                        <i class="fa fa-sticky-note"></i>
                        <span id="badgeNotas" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= htmlspecialchars($notas['datos'][0]['num'] ?? '') ?>
                        </span>
                    </button>
                <?php endif; ?>

                <a href="/EstadoCuenta/Guatemala" class="btn btn-outline-secondary d-flex align-items-center gap-1">
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
                            <span style="font-weight:700;font-size:1.1rem;">Q</span>
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
                        <h5 class="mb-0"><?= $fechaUltimoPagoCompleto ? format_date($fechaUltimoPagoCompleto) : '—' ?></h5>
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
                        <h5 class="mb-0"><?= htmlspecialchars($dataEstadoCuenta["idExterno"] ?? '') ?></h5>
                        <span>Ref. LBTR (ID Externo)</span>
                    </div>
                </div>
            </div>

            <!-- TABLA DINÁMICA -->
            <div class="table-responsive tabla-scrollable">
                <table class="table table-hover table-striped cuotas-table">
                    <colgroup>
                        <col style="width: 9%">
                        <col style="width: 18%">
                        <col style="width: 20%">
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
                    <?php
                    $dataEstadoCuenta = $dataEstadoCuenta ?? [];
                    $statusCreditoRaw = trim((string)($dataEstadoCuenta['statusCredito'] ?? ''));
                    $creditoSaldado = (mb_strtoupper($statusCreditoRaw) === 'SALDADO');
                    $totalCuotas = count($tabla);
                    $idxCuota = 0;
                    ?>
                    <?php foreach ($tabla as $fila): ?>
                        <?php
                        $idxCuota++;
                        $esUltimaCuota = ($idxCuota === $totalCuotas);
                        $cuota = safe($fila['cuota'], '—');
                        $fecha = safe($fila['fecha'], null);
                        $monto_cargo = safe($fila['monto_cargo'], 0.0);
                        $aplicados = safe($fila['aplicados'], []);
                        $total_pagado = safe($fila['total_pagado'], 0.0);
                        $pendiente = safe($fila['pendiente'], 0.0);
                        $raw_cargo = safe($fila['raw_cargo'], []);

                        $lastPagoDate = null;
                        foreach ($aplicados as $a) {
                            if (!empty($a['fechaRegistro'])) {
                                $ts = strtotime($a['fechaRegistro']);
                                if ($ts && (!$lastPagoDate || $ts > strtotime($lastPagoDate))) {
                                    $lastPagoDate = $a['fechaRegistro'];
                                }
                            }
                        }

                        $tieneContracargo = false;
                        $lastPagoDateReal = null;
                        foreach ($aplicados as $a) {
                            if (isset($a['tipo']) && $a['tipo'] === 'contracargo') { $tieneContracargo = true; continue; }
                            if (!empty($a['cc_invalido'])) continue;
                            if (!empty($a['fechaRegistro'])) {
                                $ts = strtotime($a['fechaRegistro']);
                                if ($ts && (!$lastPagoDateReal || $ts > strtotime($lastPagoDateReal))) {
                                    $lastPagoDateReal = $a['fechaRegistro'];
                                }
                            }
                        }

                        $fechaVenc = $fecha ? strtotime($fecha) : false;
                        $diasMora = 0;
                        if ($fechaVenc) {
                            if ($pendiente > 0) {
                                $diasMora = max(0, (int) floor((time() - $fechaVenc) / 86400));
                            } else {
                                if ($tieneContracargo && $lastPagoDateReal) {
                                    $diff = floor((strtotime($lastPagoDateReal) - $fechaVenc) / 86400);
                                    $diasMora = max(0, (int) $diff);
                                } elseif (isset($raw_cargo['diasMora']) && $raw_cargo['diasMora'] !== null) {
                                    $diasMora = (int) $raw_cargo['diasMora'];
                                } elseif ($lastPagoDate) {
                                    $diff = floor((strtotime($lastPagoDate) - $fechaVenc) / 86400);
                                    $diasMora = max(0, (int) $diff);
                                }
                            }
                        }

                        if ($creditoSaldado && $esUltimaCuota) {
                            $badge = '<span class="badge bg-success px-3 py-2">Crédito saldado</span>';
                        } elseif ($pendiente <= 0) {
                            $badge = '<span class="badge bg-success px-3 py-2">Pago completo</span>';
                            if ($diasMora > 0) {
                                $badge = '<span class="badge bg-danger px-3 py-2">Pago completo<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                            }
                        } elseif ($total_pagado > 0) {
                            $badge = '<span class="badge bg-warning px-3 py-2">Pago parcial<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                        } else {
                            $badge = '<span class="badge bg-secondary px-3 py-2">Sin pago<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                        }
                        $esSemanaActual = fechaCuotaEnSemanaActual($fecha ?? null);
                        ?>
                        <tr<?= $esSemanaActual ? ' class="fila-semana-actual"' : '' ?>>
                            <td><?= htmlspecialchars($cuota) ?><?php if ($esSemanaActual): ?><i class="fa fa-calendar-check icono-semana-cuota ms-1 align-middle" title="Semana actual" aria-label="Semana actual"></i><?php endif; ?></td>
                            <td class="fecha-cuota"><span class="fa fa-calendar"></span> <?= htmlspecialchars(format_date($fecha)) ?> <br> <u><?= format_currency($monto_cargo) ?></u></td>
                            <td>
                                <ul class="ps-3 mb-0">
                                    <?php if (!empty($aplicados)): ?>
                                        <?php foreach ($aplicados as $pago): ?>
                                            <?php if (isset($pago['tipo']) && $pago['tipo'] === 'contracargo'): ?>
                                            <li>
                                                <?php $etiquetaCargo = (!empty($pago['concepto_display']) && $pago['concepto_display'] === 'reembolso') ? 'Reembolso' : 'Contracargo'; ?>
                                                <span class="contracargo-label"><?= htmlspecialchars($etiquetaCargo) ?>:</span> <span class="contracargo-valor">-<?= format_currency($pago['montoPago'] ?? 0) ?></span><?php if (!empty($pago['fechaRegistro'])): ?> - <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($pago['fechaRegistro'])) ?></span><?php endif; ?>
                                            </li>
                                            <?php elseif (isset($pago['tipo']) && $pago['tipo'] === 'extemporaneos_resumen'): ?>
                                            <?php
                                            $nExt = max(1, (int) ($pago['cantidad'] ?? 1));
                                            $montoExtSum = safe($pago['montoPago'], 0.0);
                                            $fdExt = safe($pago['fechaDesde'] ?? null, null);
                                            $fhExt = safe($pago['fechaHasta'] ?? $pago['fechaRegistro'] ?? null, null);
                                            ?>
                                            <li class="small text-secondary mb-0 extemporaneos-resumen-linea" style="line-height: 1.35;">
                                                <i class="fa fa-info-circle opacity-40 me-1" style="font-size: 0.7rem;" title="Movimientos solo extemporáneos según API; no aplican a capital de la cuota."></i>
                                                <?= (int) $nExt ?> depósito<?= $nExt !== 1 ? 's' : '' ?> extemporáneo<?= $nExt !== 1 ? 's' : '' ?> · <?= format_currency($montoExtSum) ?>
                                                <?php if ($fdExt && $fhExt): ?>
                                                    · <?= htmlspecialchars(format_date($fdExt)) ?><?php if ($fdExt !== $fhExt): ?> – <?= htmlspecialchars(format_date($fhExt)) ?><?php endif; ?>
                                                <?php endif; ?>
                                            </li>
                                            <?php elseif (isset($pago['tipo']) && $pago['tipo'] === 'extemporaneo_deposito'): ?>
                                            <?php
                                            $pago_monto = safe($pago['montoPago'], 0.0);
                                            $pago_fecha = safe($pago['fechaRegistro'], $pago['fechaPago'] ?? null);
                                            $pago_aplicado = safe($pago['aplicado'], 0.0);
                                            ?>
                                            <li class="text-muted small mb-0 linea-extemporaneo-api">
                                                <i class="fa fa-info-circle opacity-40 me-1 align-text-bottom" style="font-size: 0.72rem;" title="Solo extemporáneo según API; no cuenta a capital de la cuota."></i>
                                                <span class="text-secondary">Dep. ext.</span>:
                                                <?= format_currency($pago_monto) ?> -
                                                <span class="etiqueta-aplicado">Aplicado</span>: <?= format_currency($pago_aplicado) ?> -
                                                <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($pago_fecha)) ?></span>
                                            </li>
                                            <?php else: ?>
                                            <?php
                                            $pago_monto = safe($pago['montoPago'], 0.0);
                                            $pago_fecha = safe($pago['fechaRegistro'], $pago['fechaPago'] ?? null);
                                            $extemporaneos = isset($pago['extemporaneos']) ? (float)$pago['extemporaneos'] : 0.0;
                                            $es_sobrante = !empty($pago['es_sobrante']);
                                            $pago_aplicado = safe($pago['aplicado'], 0.0);
                                            $es_gasto_cobranza = !empty($pago['gasto_cobranza']);
                                            $es_cc_invalido = !empty($pago['cc_invalido']);
                                            $pago_capital = isset($pago['capital']) ? (float)$pago['capital'] : null;
                                            $pago_interes = isset($pago['interes']) ? (float)$pago['interes'] : null;
                                            if ($es_gasto_cobranza) {
                                                $etiqueta = 'Gasto de Cobranza';
                                                $etiqueta_aplicado = 'Aplicado';
                                            } else {
                                                $etiqueta = $es_sobrante ? 'Sobrante' : 'Pago';
                                                $etiqueta_aplicado = $es_sobrante ? 'Aplicado Sobrante' : 'Aplicado';
                                            }
                                            $liClass = $es_gasto_cobranza ? 'text-danger' : ($es_cc_invalido ? 'pago-revertido' : '');
                                            ?>
                                            <li<?= $liClass ? " class=\"$liClass\"" : '' ?>>
                                                <?php if ($es_gasto_cobranza): ?>
                                                <span class="text-danger"><?= htmlspecialchars($etiqueta) ?>: <?= format_currency($pago_monto) ?> - <?= htmlspecialchars($etiqueta_aplicado) ?>: <?= format_currency($pago_aplicado) ?> - <?= htmlspecialchars(format_date($pago_fecha)) ?></span>
                                                <?php else: ?>
                                                <span><?php if ($etiqueta === 'Pago'): ?><span class="etiqueta-pago">Pago</span><?php elseif ($etiqueta === 'Sobrante'): ?><span class="etiqueta-sobrante">Sobrante</span><?php else: ?><?= htmlspecialchars($etiqueta) ?><?php endif; ?>: <?= format_currency($pago_monto) ?></span> -
                                                <span class="etiqueta-aplicado"><?= htmlspecialchars($etiqueta_aplicado) ?>: <?= format_currency($pago_aplicado) ?></span> -
                                                <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($pago_fecha)) ?></span>
                                                <?php if ($pago_capital !== null && $pago_interes !== null): ?>
                                                <br><small class="text-muted">Capital: <?= format_currency($pago_capital) ?> &nbsp;|&nbsp; Interés: <?= format_currency($pago_interes) ?></small>
                                                <?php endif; ?>
                                                <?php endif; ?>
                                            </li>
                                            <?php
                                            $hayNotasCargos = $hayNotasCargos ?? false;
                                            $notasCargoPorFecha = $notasCargoPorFecha ?? [];
                                            $esReembolsoPorFecha = $esReembolsoPorFecha ?? [];
                                            $fechaNorm = $pago_fecha ? date('Y-m-d', strtotime($pago_fecha)) : '';
                                            $totalNotaCargo = ($hayNotasCargos && $fechaNorm !== '' && isset($notasCargoPorFecha[$fechaNorm])) ? (float)$notasCargoPorFecha[$fechaNorm] : 0;
                                            $etiquetaCargoResidual = (!empty($esReembolsoPorFecha[$fechaNorm])) ? 'Reembolso' : 'Contracargo';
                                            if ($totalNotaCargo > 0): ?>
                                            <li><span class="contracargo-label"><?= htmlspecialchars($etiquetaCargoResidual) ?>:</span> <span class="contracargo-valor">-<?= format_currency($totalNotaCargo) ?></span></li>
                                            <?php endif; ?>
                                            <?php if (!$es_gasto_cobranza && $extemporaneos > 0):
                                                $gastoCobranzaPorFecha = $gastoCobranzaPorFecha ?? [];
                                                $gastoNotaDia = ($fechaNorm !== '' && isset($gastoCobranzaPorFecha[$fechaNorm]))
                                                    ? (float) $gastoCobranzaPorFecha[$fechaNorm]
                                                    : 0.0;
                                                if ($gastoNotaDia > 0.009) {
                                                    $montoGastoMostrado = min($extemporaneos, $gastoNotaDia);
                                                    $extRestanteEtiqueta = max(0, round($extemporaneos - $montoGastoMostrado, 2));
                                                } else {
                                                    $montoGastoMostrado = $extemporaneos;
                                                    $extRestanteEtiqueta = 0.0;
                                                }
                                            ?>
                                            <?php if ($gastoNotaDia > 0.009 && $montoGastoMostrado > 0.009): ?>
                                            <li class="text-danger">
                                                <span class="text-danger">Gasto cobranza: <?= format_currency($montoGastoMostrado) ?></span> -
                                                <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($pago_fecha)) ?></span>
                                            </li>
                                            <?php elseif ($gastoNotaDia <= 0.009): ?>
                                            <li class="text-danger">
                                                <span class="text-danger">Gasto cobranza: <?= format_currency($extemporaneos) ?></span> -
                                                <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($pago_fecha)) ?></span>
                                            </li>
                                            <?php endif; ?>
                                            <?php if ($gastoNotaDia > 0.009 && $extRestanteEtiqueta > 0.009): ?>
                                            <li class="text-secondary">
                                                <span class="text-secondary">Extemporáneos (contracargo u otros): <?= format_currency($extRestanteEtiqueta) ?></span> -
                                                <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($pago_fecha)) ?></span>
                                            </li>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    <?php if ($creditoSaldado && $esUltimaCuota && !empty($aplicados)): ?>
                                        <p class="mb-0 mt-2 text-success small fw-semibold">Crédito saldado — Pago total</p>
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

    <?php
    $datosRef = $referencias['datos'][0] ?? [];
    $referenciasList = [];
    for ($i = 1; $i <= 3; $i++) {
        $nombreKey = "nombre_completo_referencia{$i}";
        $telefonoKey = "telefono_referencia{$i}";
        if (!empty($datosRef[$nombreKey])) {
            $referenciasList[] = [
                'nombre'   => $datosRef[$nombreKey],
                'telefono' => $datosRef[$telefonoKey] ?? '—',
                'email'    => '',
                'tipo'     => $i === 1 ? 'Principal' : "Referencia {$i}",
                'icono'    => $i === 1 ? 'fa-user text-success' : ($i === 2 ? 'fa-user-friends text-primary' : 'fa-user-tie text-warning')
            ];
        }
    }
    /* Fallback Guatemala: si no hay referencias de __SPARTA_SECRET_REDACTED__, usar datos del cliente desde CROOP */
    if (empty($referenciasList) && !empty($datosCliente)) {
        $referenciasList[] = [
            'nombre'   => $nombreCliente ?: '—',
            'telefono' => $datosCliente['Celular'] ?? '—',
            'email'    => $datosCliente['Email']   ?? '',
            'tipo'     => 'Cliente',
            'icono'    => 'fa-user text-success',
        ];
        if (!empty($datosAdicionales['CUI'])) {
            $referenciasList[] = [
                'nombre'   => 'CUI / DPI: ' . $datosAdicionales['CUI'],
                'telefono' => $datosAdicionales['DPI'] ?? '—',
                'email'    => '',
                'tipo'     => 'Documento',
                'icono'    => 'fa-id-badge text-warning',
            ];
        }
        if (!empty($datosAdicionales['DISTRIBUIDOR'])) {
            $referenciasList[] = [
                'nombre'   => $datosAdicionales['DISTRIBUIDOR'],
                'telefono' => $datosAdicionales['SUCURSAL'] ?? '—',
                'email'    => '',
                'tipo'     => 'Distribuidor',
                'icono'    => 'fa-store text-primary',
            ];
        }
    }
    $rfcCliente = $datosRef["rfc"] ?? '—';
    ?>

    <!-- Modal RFC -->
    <div class="modal fade" id="modalRFC" tabindex="-1" aria-labelledby="modalRFCLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRFCLabel">
                        <i class="fa fa-id-card text-primary me-2"></i>Referencias del Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <?php foreach ($referenciasList as $index => $r): ?>
                            <div class="col-md-<?= 12 / max(count($referenciasList), 1) ?>">
                                <div class="reference-card">
                                    <?php if($index === 0): ?>
                                        <span class="badge bg-success reference-badge"><?= htmlspecialchars($r['tipo']) ?></span>
                                    <?php endif; ?>
                                    <div class="reference-header">
                                        <i class="fa <?= $r['icono'] ?>"></i>
                                        <?= htmlspecialchars($r['tipo']) ?>
                                    </div>
                                    <div class="reference-divider"></div>
                                    <div class="info-line">
                                        <span>Nombre: </span>
                                        <strong><?= htmlspecialchars($r['nombre']) ?></strong>
                                    </div>
                                    <div class="info-line">
                                        <span>Teléfono: </span>
                                        <strong><?= htmlspecialchars($r['telefono']) ?></strong>
                                    </div>
                                    <?php if (!empty($r['email'])): ?>
                                    <div class="info-line">
                                        <span>Email: </span>
                                        <strong><?= htmlspecialchars($r['email']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Modal Condonar -->
<div class="modal fade" id="modalCondonar" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title"><i class="fa fa-hand-holding-usd text-success me-2"></i>Condonar gastos de cobranza</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <?php
                    $hideStyle = (isset($_SESSION['departamento']) && (int)$_SESSION['departamento'] === 9) ? 'style="display:none;"' : '';
                    ?>
                    <div class="col-md-4" id="boxSeleccionados" <?= $hideStyle ?>>
                        <div class="alert alert-success py-2 mb-2">
                            <strong>Seleccionados:</strong> <span id="countCondonados">0</span>
                        </div>
                    </div>
                    <div class="col-md-4" id="boxMonto" <?= $hideStyle ?>>
                        <div class="alert alert-warning py-2 mb-2">
                            <strong>Monto a condonar:</strong> $<span id="montoCondonar">0.00</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="alert alert-danger py-2 mb-2 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold text-dark">Total gastos cobranza sin condonar:</span>
                            <span class="fw-bold text-danger">Q<span id="montoTotalSinCondonar">0.00</span></span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                        <tr>
                            <th style="width:40px;"></th>
                            <th>Semana</th>
                            <th>Periodo</th>
                            <th>Parcialidad</th>
                            <th># Cuota</th>
                            <th>Monto</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody id="tablaGastos"></tbody>
                    </table>
                </div>
                <div class="mt-3" <?= $hideStyle ?>>
                    <label class="form-label fw-semibold">Motivo de la condonación (convenio de pago) <span class="text-danger">*</span></label>
                    <textarea id="descripcionCondonacion" class="form-control" rows="3" placeholder="Describe el motivo de la condonación..."></textarea>
                </div>
            </div>
            <div class="modal-footer" <?= $hideStyle ?>>
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-success" id="btnCondonarTotal" disabled
                    onclick="confirmarCondonacion(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">
                    <i class="fa fa-check me-1"></i>Condonar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Condonación Parcial -->
<div class="modal fade" id="modalCondonarParcial" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title"><i class="fa fa-edit text-warning me-2"></i>Condonación parcial</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="condonarParcial_idGasto" value="">
                <input type="hidden" id="condonarParcial_idCredito" value="">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Monto a condonar parcialmente ($)</label>
                    <input type="number" id="condonarParcial_monto" class="form-control" min="0.01" step="0.01" placeholder="0.00">
                    <small class="text-muted">Máximo: $<span id="condonarParcial_montoMax">0.00</span></small>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-semibold">Motivo de la condonación parcial <span class="text-danger">*</span></label>
                    <textarea id="condonarParcial_motivo" class="form-control" rows="4" minlength="100"
                        placeholder="Describa la promoción o razón de la condonación a detalle (mínimo 100 caracteres, 8 palabras)..."></textarea>
                    <small class="text-muted"><span id="condonarParcial_motivoCount">0</span>/100 caracteres · Mínimo 8 palabras</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning" id="btnCondonarParcialAceptar"><i class="fa fa-check me-1"></i>Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dictamen -->
<div class="modal fade" id="modalDictamen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title"><i class="fa fa-headset text-primary me-2"></i>Dictaminar llamada</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="idCredito_dictamen" name="idCredito_dictamen"
                       value="<?= htmlspecialchars($dataEstadoCuenta['idCredito'] ?? '') ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo contacto</label>
                        <select id="tipo_contacto" class="form-select"></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Resultado</label>
                        <select id="resultado_contacto" class="form-select" disabled></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Dictamen</label>
                        <select id="dictamen" class="form-select" disabled></select>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo motivo no pago</label>
                        <select id="tipo_motivo_no_pago" class="form-select"><option value="">No aplica</option></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Motivo no pago</label>
                        <select id="motivo_no_pago" class="form-select" disabled><option value="">Seleccione motivo</option></select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Plataforma</label>
                        <select id="plataforma" class="form-select"></select>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fuente ingresos</label>
                        <input type="text" id="fuente_ingresos" class="form-control" placeholder="Ej. Sueldo, negocio propio">
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-semibold">Comentarios</label>
                    <textarea id="comentarios" rows="3" class="form-control" placeholder="Detalle de la gestión..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarDictamen()"><i class="fa fa-save me-1"></i>Guardar dictamen</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Notas -->
<div class="modal fade" id="modalNotas" tabindex="-1" aria-labelledby="modalNotasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title" id="modalNotasLabel"><i class="fa fa-sticky-note text-warning me-2"></i>Notas del Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Agregar nota</label>
                    <textarea id="notaTexto" class="form-control" rows="3" placeholder="Escribe aquí cualquier nota, acuerdo, promesa, comentario del cliente..."></textarea>
                </div>
                <input type="hidden" id="idCredito_note" name="idCredito_note" value="<?= htmlspecialchars($dataEstadoCuenta['idCredito'] ?? '') ?>">
                <div class="text-end mb-4">
                    <button class="btn btn-warning" onclick="agregarNota()"><i class="fa fa-plus me-1"></i>Agregar nota</button>
                </div>
                <hr>
                <div id="contenedorNotas" class="row g-3">
                    <div class="col-md-6">
                        <div class="nota-card">
                            <div class="nota-fecha"><i class="fa fa-clock"></i> 13/01/2026 10:32</div>
                            <p class="nota-texto">Cliente comenta que pagará el viernes por la tarde.</p>
                            <div class="nota-autor"><i class="fa fa-user"></i> Sistema</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Call Center -->
<div class="modal fade" id="modalCallCenter" tabindex="-1" aria-labelledby="modalCallCenterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCallCenterLabel"><i class="fa fa-headset me-2"></i>Call Center</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="fa fa-tools fa-3x text-warning mb-3"></i>
                <h6 class="mb-2">Funcionalidad en mantenimiento</h6>
                <p class="text-muted mb-0">Se encuentra temporalmente en mantenimiento.<br>Por favor, intenta más tarde.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Direcciones -->
<div class="modal fade" id="modalDirecciones" tabindex="-1" aria-labelledby="modalDireccionesLabel" aria-hidden="true" style="position: fixed; z-index: 1055;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDireccionesLabel">
                    <i class="fa fa-map-marker-alt me-2 text-primary"></i>Direcciones del Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-4">
                <div class="row g-3">
                    <?php if (!empty($domicilioGuat)): ?>
                    <div class="col-12">
                        <div class="card shadow-sm border">
                            <div class="card-header bg-light d-flex align-items-center gap-2">
                                <i class="fa fa-home text-success"></i>
                                <strong>Domicilio Registrado</strong>
                                <span class="badge bg-success ms-auto">Principal</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php if (!empty($domicilioGuat['Calle_Numero'])): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Calle</small>
                                        <strong><?= htmlspecialchars($domicilioGuat['Calle_Numero']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($domicilioGuat['NumExt'])): ?>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Núm. Ext.</small>
                                        <strong><?= htmlspecialchars($domicilioGuat['NumExt']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($domicilioGuat['NumInt']) && $domicilioGuat['NumInt'] !== 'N/A'): ?>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Núm. Int.</small>
                                        <strong><?= htmlspecialchars($domicilioGuat['NumInt']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($domicilioGuat['Colonia'])): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Colonia / Sector</small>
                                        <strong><?= htmlspecialchars($domicilioGuat['Colonia']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($domicilioGuat['Codigo_Postal'])): ?>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Código Postal</small>
                                        <strong><?= htmlspecialchars($domicilioGuat['Codigo_Postal']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php
                                        // Ciudad tiene formato "CIUDAD,DEPARTAMENTO,ID,ZONA"
                                        $ciudadPartes = explode(',', $domicilioGuat['Ciudad'] ?? '');
                                        $ciudadNombre = trim($ciudadPartes[0] ?? '');
                                        $deptoNombre  = trim($ciudadPartes[1] ?? '');
                                    ?>
                                    <?php if ($ciudadNombre): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Ciudad</small>
                                        <strong><?= htmlspecialchars($ciudadNombre) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($deptoNombre): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Departamento</small>
                                        <strong><?= htmlspecialchars($deptoNombre) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($domicilioGuat['Lada_Tel_Casa'])): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block"><i class="fa fa-phone me-1"></i>Tel. Casa</small>
                                        <strong><?= htmlspecialchars($domicilioGuat['Lada_Tel_Casa']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($domicilioGuat['Lada_Tel_Oficina'])): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block"><i class="fa fa-building me-1"></i>Tel. Oficina</small>
                                        <strong><?= htmlspecialchars($domicilioGuat['Lada_Tel_Oficina']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($datosCliente['Email']) || !empty($datosCliente['Celular'])): ?>
                    <div class="col-12">
                        <div class="card shadow-sm border border-info">
                            <div class="card-header bg-light d-flex align-items-center gap-2">
                                <i class="fa fa-address-card text-info"></i>
                                <strong>Datos de Contacto del Cliente</strong>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php if (!empty($datosCliente['Celular'])): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block"><i class="fa fa-mobile-alt me-1"></i>Celular</small>
                                        <strong><?= htmlspecialchars($datosCliente['Celular']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($datosCliente['Email'])): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block"><i class="fa fa-envelope me-1"></i>Email</small>
                                        <strong><?= htmlspecialchars($datosCliente['Email']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php elseif (!empty($direccionClienteGuat)): ?>
                    <!-- Fallback: dirección desde request_cliente -->
                    <div class="col-12">
                        <div class="card shadow-sm border">
                            <div class="card-header bg-light d-flex align-items-center gap-2">
                                <i class="fa fa-map-pin text-primary"></i>
                                <strong>Domicilio del Solicitante</strong>
                                <span class="badge bg-secondary ms-auto">Desde solicitud</span>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <?php if (!empty($direccionClienteGuat['Calle_Numero'])): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Calle / Número</small>
                                        <strong><?= htmlspecialchars($direccionClienteGuat['Calle_Numero']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($direccionClienteGuat['Codigo_Postal'])): ?>
                                    <div class="col-md-3">
                                        <small class="text-muted d-block">Código Postal</small>
                                        <strong><?= htmlspecialchars($direccionClienteGuat['Codigo_Postal']) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php
                                        $cpGuat = explode(',', $direccionClienteGuat['Ciudad'] ?? '');
                                        $ciudadGuat = trim($cpGuat[0] ?? '');
                                        $deptoGuat  = trim($cpGuat[1] ?? '');
                                    ?>
                                    <?php if ($ciudadGuat): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Ciudad</small>
                                        <strong><?= htmlspecialchars($ciudadGuat) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($deptoGuat): ?>
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Departamento</small>
                                        <strong><?= htmlspecialchars($deptoGuat) ?></strong>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle me-2"></i>
                            <strong>No se encontraron direcciones</strong>
                            <p class="mb-0 mt-2">No hay dirección registrada para este cliente.</p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>



<script>
    function actualizarContadorNotas() {
        const badge = document.getElementById('badgeNotas');
        if (!badge) return;
        const cards = document.querySelectorAll('#contenedorNotas .nota-card');
        if (!document.getElementById('modalNotas')?.classList.contains('show')) return;
        const total = cards.length;
        badge.textContent = total;
        badge.style.display = total > 0 ? 'inline-block' : 'none';
    }

    function agregarNota() {
        const notaInput = document.getElementById('notaTexto');
        const nota = notaInput.value.trim();
        const id_credito = document.getElementById('idCredito_note')?.value;
        if (!nota) { Swal.fire("Atención", "Escribe una nota antes de guardar", "warning"); return; }
        fetch('/EstadoCuenta/AddNote', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nota: nota, id_credito: id_credito })
        })
        .then(response => { if (!response.ok) throw new Error('Error HTTP ' + response.status); return response.json(); })
        .then(data => {
            if (!data.success) { Swal.fire("Error", data.mensaje, "error"); return; }
            const contenedor = document.getElementById('contenedorNotas');
            const fecha = new Date().toLocaleString('es-MX');
            const usuarioNota = data.data?.usuario ?? 'Operador';
            const col = document.createElement('div');
            col.className = 'col-md-6';
            col.innerHTML = `<div class="nota-card animate__animated animate__fadeInUp"><div class="nota-fecha"><i class="fa fa-clock"></i> ${fecha}</div><p class="nota-texto">${nota}</p><div class="nota-autor"><i class="fa fa-user"></i> ${usuarioNota}</div></div>`;
            contenedor.prepend(col);
            notaInput.value = '';
            if (typeof actualizarContadorNotas === 'function') actualizarContadorNotas();
            Swal.fire({ icon: 'success', title: 'Nota guardada', timer: 1200, showConfirmButton: false });
        })
        .catch(error => { console.error(error); Swal.fire("Error", "Error de conexión con el servidor", "error"); });
    }

    function actualizarResumenCondonacion() {
        let total = 0, count = 0;
        document.querySelectorAll('.chk-condona:checked').forEach(chk => { total += parseFloat(chk.dataset.monto); count++; });
        document.getElementById('countCondonados').textContent = count;
        document.getElementById('montoCondonar').textContent = total.toFixed(2);
    }

    function lanzarBillete(x, y) {
        const b = document.createElement('div');
        b.className = 'billete';
        b.innerHTML = '💸';
        b.style.left = x + 'px';
        b.style.top = y + 'px';
        document.body.appendChild(b);
        setTimeout(() => b.remove(), 1000);
    }

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('chk-condona')) {
            actualizarResumenCondonacion();
            if (e.target.checked) { const rect = e.target.getBoundingClientRect(); lanzarBillete(rect.left, rect.top); }
        }
    });

    function consultaNotas(idCredito) {
        if (!idCredito) { Swal.fire("Error", "Id de crédito inválido", "error"); return; }
        const contenedor = document.getElementById('contenedorNotas');
        contenedor.innerHTML = '<div class="col-12 text-center text-muted">Cargando notas...</div>';
        fetch('/EstadoCuenta/getNotasCredito', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ idCredito: idCredito })
        })
        .then(res => res.json())
        .then(resp => {
            if (!resp.success) { Swal.fire("Error", resp.mensaje, "error"); contenedor.innerHTML = ''; return; }
            const notas = resp.datos ?? resp.data ?? [];
            contenedor.innerHTML = '';
            if (!Array.isArray(notas) || notas.length === 0) {
                contenedor.innerHTML = '<div class="col-12 text-center text-muted">Sin notas registradas</div>';
                return;
            }
            notas.forEach(n => {
                contenedor.innerHTML += `<div class="col-md-6"><div class="nota-card animate__animated animate__fadeInUp"><div class="nota-fecha"><i class="fa fa-clock"></i> ${n.created_at}</div><p class="nota-texto">${n.nota}</p><div class="nota-autor"><i class="fa fa-user"></i> ${n.usuario ?? 'Sistema'}</div></div></div>`;
            });
        })
        .catch(err => { console.error("ERROR consultaNotas:", err); Swal.fire("Error", "Error de conexión con el servidor", "error"); contenedor.innerHTML = ''; });
        const modal = new bootstrap.Modal(document.getElementById('modalNotas'));
        modal.show();
    }

    const tipoContactoSelect      = document.getElementById('tipo_contacto');
    const resultadoContactoSelect = document.getElementById('resultado_contacto');
    const dictamenSelect          = document.getElementById('dictamen');
    const plataformaSelect        = document.getElementById('plataforma');
    const tipoMotivoSelect        = document.getElementById('tipo_motivo_no_pago');
    const motivoNoPagoSelect      = document.getElementById('motivo_no_pago');

    function cargarTiposContacto() {
        fetch('/EstadoCuenta/getTiposContacto').then(res => res.json()).then(resp => {
            if (!resp.success) return;
            tipoContactoSelect.innerHTML = '<option value="">Seleccione tipo de contacto</option>';
            resp.datos.forEach(item => { tipoContactoSelect.innerHTML += `<option value="${item.id}">${item.nombre}</option>`; });
        }).catch(err => console.error(err));
    }

    function cargarResultadosContacto(tipoContactoId) {
        resultadoContactoSelect.innerHTML = '<option value="">Cargando...</option>';
        resultadoContactoSelect.disabled = true;
        dictamenSelect.innerHTML = '<option value="">Seleccione dictamen</option>';
        dictamenSelect.disabled = true;
        fetch('/EstadoCuenta/getResultadosContacto', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo_contacto_id: tipoContactoId })
        }).then(res => res.json()).then(resp => {
            if (!resp.success) return;
            resultadoContactoSelect.innerHTML = '<option value="">Seleccione resultado</option>';
            resp.datos.forEach(item => { resultadoContactoSelect.innerHTML += `<option value="${item.id}">${item.nombre}</option>`; });
            resultadoContactoSelect.disabled = false;
        }).catch(err => console.error(err));
    }

    tipoContactoSelect.addEventListener('change', function () { if (this.value) cargarResultadosContacto(this.value); });

    function cargarDictamenes(resultadoContactoId) {
        dictamenSelect.innerHTML = '<option value="">Cargando...</option>';
        dictamenSelect.disabled = true;
        fetch('/EstadoCuenta/getDictamenes', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ resultado_contacto_id: resultadoContactoId })
        }).then(res => res.json()).then(resp => {
            if (!resp.success) return;
            dictamenSelect.innerHTML = '<option value="">Seleccione dictamen</option>';
            resp.datos.forEach(item => { dictamenSelect.innerHTML += `<option value="${item.id}">${item.nombre}</option>`; });
            dictamenSelect.disabled = false;
        }).catch(err => console.error(err));
    }

    resultadoContactoSelect.addEventListener('change', function () { if (this.value) cargarDictamenes(this.value); });

    function cargarPlataformas() {
        fetch('/EstadoCuenta/getPlataformas').then(res => res.json()).then(resp => {
            if (!resp.success) return;
            plataformaSelect.innerHTML = '<option value="">Seleccione plataforma</option>';
            resp.datos.forEach(item => { plataformaSelect.innerHTML += `<option value="${item.id}">${item.nombre}</option>`; });
        }).catch(err => console.error(err));
    }

    function cargarTiposMotivoNoPago() {
        fetch('/EstadoCuenta/getTiposMotivoNoPago').then(res => res.json()).then(resp => {
            if (!resp.success) return;
            tipoMotivoSelect.innerHTML = '<option value="">No aplica</option>';
            resp.datos.forEach(item => { tipoMotivoSelect.innerHTML += `<option value="${item.id}">${item.nombre}</option>`; });
        });
    }

    function cargarMotivosNoPagoPorTipo(tipoId) {
        motivoNoPagoSelect.innerHTML = '<option value="">Cargando...</option>';
        motivoNoPagoSelect.disabled = true;
        if (!tipoId) { motivoNoPagoSelect.innerHTML = '<option value="">Seleccione motivo</option>'; return; }
        fetch('/EstadoCuenta/getMotivosNoPagoPorTipo', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo_motivo_id: tipoId })
        }).then(res => res.json()).then(resp => {
            if (!resp.success) return;
            motivoNoPagoSelect.innerHTML = '<option value="">Seleccione motivo</option>';
            resp.datos.forEach(item => { motivoNoPagoSelect.innerHTML += `<option value="${item.id}">${item.descripcion}</option>`; });
            motivoNoPagoSelect.disabled = false;
        });
    }

    tipoMotivoSelect.addEventListener('change', function () { cargarMotivosNoPagoPorTipo(this.value); });

    function initDictamenModal() {
        cargarTiposContacto();
        cargarPlataformas();
        cargarTiposMotivoNoPago();
    }

    document.getElementById('modalDictamen').addEventListener('shown.bs.modal', initDictamenModal);

    function guardarDictamen() {
        const id_credito = document.getElementById('idCredito_dictamen')?.value;
        const tipoContacto = document.getElementById('tipo_contacto').value;
        const resultadoContacto = document.getElementById('resultado_contacto').value;
        const dictamen = document.getElementById('dictamen').value;
        const tipoMotivo = document.getElementById('tipo_motivo_no_pago').value;
        const motivoNoPago = document.getElementById('motivo_no_pago').value;
        const plataforma = document.getElementById('plataforma').value;
        const fuenteIngresos = document.getElementById('fuente_ingresos').value.trim();
        const comentarios = document.getElementById('comentarios').value.trim();

        if (!id_credito) { Swal.fire("Error", "No se detectó el crédito", "error"); return; }
        if (!tipoContacto) { Swal.fire("Atención", "Selecciona el tipo de contacto", "warning"); return; }
        if (!resultadoContacto) { Swal.fire("Atención", "Selecciona el resultado del contacto", "warning"); return; }
        if (!dictamen) { Swal.fire("Atención", "Selecciona el dictamen", "warning"); return; }
        if (tipoMotivo && !motivoNoPago) { Swal.fire("Atención", "Selecciona el motivo de no pago", "warning"); return; }
        if (!fuenteIngresos) { Swal.fire("Atención", "La fuente de ingresos son obligatorios", "warning"); return; }
        if (!comentarios) { Swal.fire("Atención", "Los comentarios son obligatorios", "warning"); return; }

        fetch('/EstadoCuenta/guardarDictamen', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_credito, tipo_contacto_id: tipoContacto, resultado_contacto_id: resultadoContacto, dictamen_id: dictamen, tipo_motivo_no_pago_id: tipoMotivo || null, motivo_no_pago_id: motivoNoPago || null, plataforma_id: plataforma || null, fuente_ingresos: fuenteIngresos, comentarios })
        })
        .then(res => { if (!res.ok) throw new Error('Error HTTP ' + res.status); return res.json(); })
        .then(resp => {
            if (!resp.success) { Swal.fire("Error", resp.mensaje, "error"); return; }
            Swal.fire({ icon: 'success', title: 'Dictamen guardado', timer: 1300, showConfirmButton: false });
            limpiarFormularioDictamen();
            bootstrap.Modal.getInstance(document.getElementById('modalDictamen')).hide();
        })
        .catch(err => { console.error(err); Swal.fire("Error", "Error de conexión con el servidor", "error"); });
    }

    function limpiarFormularioDictamen() {
        document.getElementById('tipo_contacto').value = '';
        document.getElementById('resultado_contacto').innerHTML = '';
        document.getElementById('resultado_contacto').disabled = true;
        document.getElementById('dictamen').innerHTML = '';
        document.getElementById('dictamen').disabled = true;
        document.getElementById('tipo_motivo_no_pago').value = '';
        document.getElementById('motivo_no_pago').innerHTML = '<option value="">Seleccione motivo</option>';
        document.getElementById('motivo_no_pago').disabled = true;
        document.getElementById('plataforma').value = '';
        document.getElementById('fuente_ingresos').value = '';
        document.getElementById('comentarios').value = '';
    }

    function consultaGastosCondonables(idCredito) {
        if (!idCredito) { Swal.fire("Error", "Id de crédito inválido", "error"); return; }
        const tabla = document.getElementById('tablaGastos');
        document.getElementById('countCondonados').textContent = 0;
        document.getElementById('montoCondonar').textContent = '0.00';
        document.getElementById('montoTotalSinCondonar').textContent = '0.00';
        tabla.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Cargando gastos...</td></tr>';
        fetch('/EstadoCuenta/getGastosCobranza', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ idCredito })
        })
        .then(res => res.json())
        .then(resp => {
            if (!resp.success) { Swal.fire("Error", resp.mensaje, "error"); tabla.innerHTML = ''; return; }
            const gastos = resp.datos ?? resp.data ?? [];
            tabla.innerHTML = '';
            if (!Array.isArray(gastos) || gastos.length === 0) {
                tabla.innerHTML = '<tr><td colspan="7" class="text-center text-muted">No hay gastos de cobranza</td></tr>';
                document.getElementById('montoTotalSinCondonar').textContent = '0.00';
                return;
            }
            document.getElementById('modalCondonar').dataset.idCredito = idCredito;
            gastos.forEach((g, index) => {
                const idGasto = g.id_gasto;
                const montoOrig = parseFloat(g.monto_original ?? g.monto ?? 0);
                const montoEfectivo = parseFloat(g.monto ?? 0);
                const parcialMonto = parseFloat(g.condonacion_parcial_monto ?? 0);
                const tieneParcial = parcialMonto > 0;
                const montoFaltaCondonar = tieneParcial ? (montoOrig - parcialMonto) : montoEfectivo;
                const anteriorTieneParcial = index === 0 || (parseFloat(gastos[index - 1].condonacion_parcial_monto ?? 0) > 0);
                const puedeParcial = anteriorTieneParcial;
                const montoCelda = tieneParcial ? `<span class="text-decoration-line-through text-muted">$${montoOrig.toFixed(2)}</span><br><strong>$${montoEfectivo.toFixed(2)}</strong>` : `$${montoEfectivo.toFixed(2)}`;
                const tooltipParcialTxt = tieneParcial ? 'Monto condonado: $' + parcialMonto.toFixed(2) + '\n\nMotivo:\n' + (g.condonacion_parcial_motivo || '').substring(0, 350) : '';
                const tooltipParcialEsc = tooltipParcialTxt.replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'&#10;');
                const parcialMotivo = (g.condonacion_parcial_motivo || '').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
                const iconoParcialHtml = tieneParcial ? `<span class="info-condonacion-parcial text-info" style="cursor:pointer;" title="${tooltipParcialEsc}" data-monto="${parcialMonto.toFixed(2)}" data-motivo="${(g.condonacion_parcial_motivo||'').replace(/"/g,'&quot;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;').substring(0,500)}"><i class="fa fa-info-circle"></i></span>` : '';
                tabla.innerHTML += `
                <tr data-id-gasto="${idGasto}" data-tiene-parcial="${tieneParcial?'1':'0'}" data-puede-parcial="${puedeParcial?'1':'0'}">
                    <td><input type="checkbox" class="form-check-input chk-condona" data-id="${idGasto}" data-monto="${montoFaltaCondonar.toFixed(2)}" onchange="recalcularCondonacion()"></td>
                    <td>${g.semana}</td><td>${g.periodo}</td>
                    <td>${g.parcialidad!=null&&g.parcialidad!==''?g.parcialidad:'-'}</td>
                    <td>$${parseFloat(g.cuota).toFixed(2)}</td>
                    <td>${montoCelda}</td>
                    <td>${tieneParcial?iconoParcialHtml:!puedeParcial?`<span class="text-muted small">Primero semana anterior</span>`:`<button class="btn btn-sm btn-outline-primary btn-editar-condonar-parcial d-none" data-id-gasto="${idGasto}" data-monto-original="${montoOrig.toFixed(2)}" data-condonacion-parcial-monto="${parcialMonto.toFixed(2)}" data-condonacion-parcial-motivo="${parcialMotivo}" onclick="editarGastoCobranza(this)"><i class="fa fa-edit"></i></button>`}</td>
                </tr>`;
            });
            const totalSinCondonar = gastos.reduce((acc, g) => acc + parseFloat(g.monto || 0), 0);
            document.getElementById('montoTotalSinCondonar').textContent = totalSinCondonar.toFixed(2);
            document.getElementById('btnCondonarTotal').disabled = true;
            recalcularCondonacion();
        })
        .catch(err => { console.error(err); Swal.fire("Error", "Error de conexión con el servidor", "error"); tabla.innerHTML = ''; });
        const modal = new bootstrap.Modal(document.getElementById('modalCondonar'));
        modal.show();
    }

    function recalcularCondonacion() {
        const checks = document.querySelectorAll('.chk-condona:checked');
        const btnCondonarTotal = document.getElementById('btnCondonarTotal');
        let total = 0;
        checks.forEach(chk => { total += parseFloat(chk.dataset.monto || 0); });
        document.getElementById('countCondonados').textContent = checks.length;
        document.getElementById('montoCondonar').textContent = total.toFixed(2);
        if (btnCondonarTotal) btnCondonarTotal.disabled = checks.length === 0;
        document.querySelectorAll('#tablaGastos tr[data-id-gasto]').forEach(tr => {
            const chk = tr.querySelector('.chk-condona');
            const btn = tr.querySelector('.btn-editar-condonar-parcial');
            if (!btn || tr.dataset.tieneParcial === '1') return;
            if (tr.dataset.puedeParcial !== '1') return;
            if (chk && chk.checked) btn.classList.remove('d-none');
            else btn.classList.add('d-none');
        });
    }

    document.addEventListener('click', function(e) {
        const el = e.target.closest('.info-condonacion-parcial');
        if (!el) return;
        e.preventDefault(); e.stopPropagation();
        const monto = el.getAttribute('data-monto') || '0';
        let motivo = (el.getAttribute('data-motivo') || '').replace(/&quot;/g,'"').replace(/&#39;/g,"'").replace(/&lt;/g,'<').replace(/&gt;/g,'>');
        motivo = motivo.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        Swal.fire({ title: 'Condonación parcial', html: '<p class="text-start"><strong>Monto condonado:</strong> $' + monto + '</p><p class="text-start mt-2"><strong>Motivo:</strong></p><p class="text-start text-muted small" style="white-space:pre-wrap;">' + (motivo||'—') + '</p>', icon: 'info', confirmButtonText: 'Entendido', width: '480px' });
    });

    function editarGastoCobranza(btn) {
        const idGasto = btn.getAttribute('data-id-gasto');
        const montoOriginal = parseFloat(btn.getAttribute('data-monto-original') || 0);
        const idCredito = document.getElementById('modalCondonar').dataset.idCredito || '';
        document.getElementById('condonarParcial_idGasto').value = idGasto;
        document.getElementById('condonarParcial_idCredito').value = idCredito;
        document.getElementById('condonarParcial_montoMax').textContent = montoOriginal.toFixed(2);
        document.getElementById('condonarParcial_monto').value = btn.getAttribute('data-condonacion-parcial-monto') || '';
        document.getElementById('condonarParcial_monto').max = montoOriginal;
        document.getElementById('condonarParcial_motivo').value = (btn.getAttribute('data-condonacion-parcial-motivo') || '').replace(/&quot;/g,'"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');
        document.getElementById('condonarParcial_motivoCount').textContent = document.getElementById('condonarParcial_motivo').value.length;
        new bootstrap.Modal(document.getElementById('modalCondonarParcial')).show();
    }

    document.getElementById('condonarParcial_motivo').addEventListener('input', function() {
        document.getElementById('condonarParcial_motivoCount').textContent = this.value.length;
    });

    document.getElementById('btnCondonarParcialAceptar').addEventListener('click', function() {
        const idGasto = document.getElementById('condonarParcial_idGasto').value;
        const idCredito = document.getElementById('condonarParcial_idCredito').value;
        const montoParcial = parseFloat(document.getElementById('condonarParcial_monto').value || 0);
        const motivo = document.getElementById('condonarParcial_motivo').value.trim();
        const montoMax = parseFloat(document.getElementById('condonarParcial_montoMax').textContent || 0);
        if (!idGasto || !idCredito) { Swal.fire('Error', 'Datos de gasto o crédito no disponibles.', 'error'); return; }
        if (montoParcial <= 0 && motivo.length < 100) { Swal.fire('Atención', 'Debe completar el monto y el motivo (mínimo 100 caracteres).', 'warning'); return; }
        if (montoParcial <= 0) { Swal.fire('Atención', 'Debe indicar el monto a condonar.', 'warning'); return; }
        if (motivo.length < 100) { Swal.fire('Atención', 'El motivo debe tener al menos 100 caracteres.', 'warning'); return; }
        if (montoParcial >= montoMax) { Swal.fire('Atención', 'El monto debe ser menor al monto total del gasto.', 'warning'); return; }
        if (/(.)\1{2,}/u.test(motivo)) { Swal.fire('Atención', 'El motivo no puede contener caracteres repetidos muchas veces.', 'warning'); return; }
        if (motivo.split(/\s+/).filter(Boolean).length < 8) { Swal.fire('Atención', 'El motivo debe incluir al menos 8 palabras.', 'warning'); return; }
        Swal.fire({ title: '¿Aplicar condonación parcial?', text: 'Se registrará la condonación parcial de $' + montoParcial.toFixed(2) + '. ¿Continuar?', icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, aplicar', cancelButtonText: 'Cancelar' })
        .then((result) => {
            if (!result.isConfirmed) return;
            fetch('/EstadoCuenta/guardarCondonacionParcialGasto', {
                method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ id_gastos_cobranza: idGasto, monto_parcial: montoParcial, motivo })
            }).then(res => res.json()).then(resp => {
                if (!resp.success) { Swal.fire('Error', resp.mensaje || 'Error al guardar', 'error'); return; }
                Swal.fire('Guardado', resp.mensaje || 'Condonación parcial guardada.', 'success');
                bootstrap.Modal.getInstance(document.getElementById('modalCondonarParcial')).hide();
                consultaGastosCondonables(idCredito);
            }).catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
        });
    });

    function abrirModalDirecciones() {
        const modalElement = document.getElementById('modalDirecciones');
        if (!modalElement) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo encontrar el modal de direcciones' });
            return;
        }
        // Limpiar cualquier backdrop huérfano antes de abrir
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');

        if (modalElement.parentElement !== document.body) document.body.appendChild(modalElement);

        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement, { backdrop: true, keyboard: true });
        modalInstance.show();

        // Al cerrar, limpiar estado por si acaso
        modalElement.addEventListener('hidden.bs.modal', function onHide() {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            modalElement.removeEventListener('hidden.bs.modal', onHide);
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('modalDirecciones');
        if (modalElement && modalElement.parentElement !== document.body) document.body.appendChild(modalElement);

        const mc = document.getElementById('modalCondonar');
        const mp = document.getElementById('modalCondonarParcial');
        const SCRIM_ID = 'scrim-condonar-estado-cuenta';
        const SCRIM_Z = 9998, MODAL_CONDO_Z = 9999, MODAL_PARCIAL_Z = 10000;

        function getOrCreateScrim() {
            var el = document.getElementById(SCRIM_ID);
            if (el && el.parentNode) return el;
            el = document.createElement('div');
            el.id = SCRIM_ID;
            el.setAttribute('aria-hidden', 'true');
            el.style.cssText = 'position:fixed;inset:0;z-index:' + SCRIM_Z + ';background:rgba(0,0,0,0.5);pointer-events:auto;';
            el.className = 'scrim-condonar-estado-cuenta';
            return el;
        }
        function removeScrim() { var el = document.getElementById(SCRIM_ID); if (el && el.parentNode) el.parentNode.removeChild(el); }
        function moveModalToBody(modalEl, z) {
            if (!modalEl || modalEl.parentNode === document.body) return;
            document.body.appendChild(modalEl);
            modalEl.style.setProperty('z-index', String(z), 'important');
        }

        if (mc) {
            mc.addEventListener('show.bs.modal', function () { document.body.classList.add('modal-condonar-open', 'modal-open'); var scrim = getOrCreateScrim(); if (!scrim.parentNode) document.body.appendChild(scrim); moveModalToBody(mc, MODAL_CONDO_Z); });
            mc.addEventListener('shown.bs.modal', function () { mc.style.setProperty('z-index', String(MODAL_CONDO_Z), 'important'); });
            mc.addEventListener('hidden.bs.modal', function () { document.body.classList.remove('modal-condonar-open', 'modal-condonar-parcial-open'); if (!document.body.classList.contains('modal-condonar-parcial-open')) document.body.classList.remove('modal-open'); removeScrim(); mc.style.removeProperty('z-index'); });
        }
        if (mp) {
            mp.addEventListener('show.bs.modal', function () { document.body.classList.add('modal-condonar-parcial-open', 'modal-open'); var scrim = getOrCreateScrim(); if (!scrim.parentNode) document.body.appendChild(scrim); moveModalToBody(mp, MODAL_PARCIAL_Z); if (mc && mc.parentNode !== document.body) moveModalToBody(mc, MODAL_CONDO_Z); });
            mp.addEventListener('shown.bs.modal', function () { var scrim = document.getElementById(SCRIM_ID); if (scrim) scrim.style.setProperty('z-index', String(SCRIM_Z), 'important'); mp.style.setProperty('z-index', String(MODAL_PARCIAL_Z), 'important'); });
            mp.addEventListener('hidden.bs.modal', function () { document.body.classList.remove('modal-condonar-parcial-open'); if (!document.body.classList.contains('modal-condonar-open')) { document.body.classList.remove('modal-open'); removeScrim(); } mp.style.removeProperty('z-index'); });
        }

        // Persistencia acordeón
        const accordion = document.getElementById('collapseInfoCredito');
        const accordionButton = document.querySelector('[data-bs-target="#collapseInfoCredito"]');
        if (accordion && accordionButton) {
            if (localStorage.getItem('accordionInfoCredito') === 'open') {
                new bootstrap.Collapse(accordion, { toggle: false }).show();
                accordionButton.classList.remove('collapsed');
                accordionButton.setAttribute('aria-expanded', 'true');
            }
            accordion.addEventListener('show.bs.collapse', function() { localStorage.setItem('accordionInfoCredito', 'open'); });
            accordion.addEventListener('hide.bs.collapse', function() { localStorage.setItem('accordionInfoCredito', 'closed'); });
        }
    });

    function confirmarCondonacion(id_credito) {
        const comentario = document.getElementById('descripcionCondonacion').value.trim();
        const checks = document.querySelectorAll('.chk-condona:checked');
        if (checks.length === 0) { Swal.fire("Atención", "Selecciona al menos un gasto", "warning"); return; }
        if (!comentario) { Swal.fire("Atención", "El motivo de la condonación es obligatorio", "warning"); return; }
        if (comentario.length < 100) { Swal.fire("Atención", "El motivo debe tener al menos 100 caracteres.", "warning"); return; }
        if (/(.)\1{2,}/u.test(comentario)) { Swal.fire("Atención", "El motivo no puede contener caracteres repetidos muchas veces.", "warning"); return; }
        if (comentario.split(/\s+/).filter(Boolean).length < 8) { Swal.fire("Atención", "El motivo debe incluir al menos 8 palabras.", "warning"); return; }
        const gastos = [];
        let total = 0;
        checks.forEach(chk => { gastos.push({ id_gastos_cobranza: chk.dataset.id, monto: parseFloat(chk.dataset.monto) }); total += parseFloat(chk.dataset.monto); });
        fetch('/EstadoCuenta/confirmarCondonacionGastos', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ idCredito: id_credito, comentario, total, gastos })
        })
        .then(res => res.json())
        .then(resp => {
            if (!resp.success) { Swal.fire("Error", resp.mensaje, "error"); return; }
            Swal.fire("Éxito", "Gastos condonados correctamente", "success");
            bootstrap.Modal.getInstance(document.getElementById('modalCondonar')).hide();
        })
        .catch(err => { console.error(err); Swal.fire("Error", "Error de conexión", "error"); });
    }
</script>

<script>
(function(){
    var style=document.createElement('style');
    style.textContent='.estado-cuenta-easter-wrap{position:fixed;inset:0;z-index:1058;pointer-events:none;overflow:hidden}.estado-cuenta-easter-money{position:absolute;left:0;top:0;font-size:20px;pointer-events:none;opacity:0;animation:ecMoneyBurst 1.3s ease-out forwards}.estado-cuenta-easter-toast{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:1060;background:linear-gradient(135deg,#166534 0%,#22c55e 50%,#4ade80 100%);color:#fff;padding:20px 40px;border-radius:16px;font-size:1.15rem;font-weight:700;box-shadow:0 16px 48px rgba(34,197,94,0.4);border:2px solid rgba(255,255,255,0.3);opacity:0;animation:estadoCuentaEasterIn .4s ease forwards;pointer-events:none;text-align:center}.estado-cuenta-easter-toast .estado-cuenta-easter-emoji{font-size:2rem;display:block;margin-bottom:6px}@keyframes ecMoneyBurst{0%{opacity:1;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(calc(-50% + var(--ec-tx)), calc(-50% + var(--ec-ty))) scale(0.5)}}@keyframes estadoCuentaEasterIn{0%{opacity:0;transform:translate(-50%,-50%) scale(0.8)}100%{opacity:1;transform:translate(-50%,-50%) scale(1)}}@keyframes estadoCuentaEasterOut{0%{opacity:1;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(-50%,-50%) scale(0.95)}}';
    document.head.appendChild(style);
    document.addEventListener("keydown",function(e){
        if(!e.ctrlKey||!e.shiftKey||(e.key!=="E"&&e.keyCode!==69))return;
        e.preventDefault();
        var wrap=document.createElement("div");wrap.className="estado-cuenta-easter-wrap";
        var moneyEmojis=["\uD83D\uDCB0","\uD83D\uDCB5","\uD83D\uDCB4"];
        var positions=[[0.2,0.25],[0.5,0.2],[0.75,0.3],[0.35,0.55]];
        for(var f=0;f<4;f++){
            var fw=document.createElement("div");
            fw.style.cssText="position:absolute;left:"+(positions[f][0]*100)+"%;top:"+(positions[f][1]*100)+"%;width:0;height:0;";
            var num=28+Math.floor(Math.random()*12),dist=90+Math.random()*50;
            for(var r=0;r<num;r++){
                var angle=(r/num)*Math.PI*2+Math.random()*0.4,tx=Math.cos(angle)*dist+"px",ty=Math.sin(angle)*dist+"px";
                var sp=document.createElement("span");sp.className="estado-cuenta-easter-money";sp.textContent=moneyEmojis[Math.floor(Math.random()*moneyEmojis.length)];
                sp.style.animationDelay=(f*0.15)+"s";sp.style.setProperty("--ec-tx",tx);sp.style.setProperty("--ec-ty",ty);fw.appendChild(sp);
            }
            wrap.appendChild(fw);
        }
        document.body.appendChild(wrap);
        var t=document.createElement("div");t.className="estado-cuenta-easter-toast";
        t.innerHTML='<span class="estado-cuenta-easter-emoji">\uD83D\uDCB0</span> \u00A1Cuenta al d\u00EDa, espartano!';
        document.body.appendChild(t);
        try{var a=new Audio("/assets/audio/coins.mp3");a.volume=0.45;a.play().catch(function(){});setTimeout(function(){a.pause();a.currentTime=0;},2200);}catch(e){}
        setTimeout(function(){t.style.animation="estadoCuentaEasterOut .35s ease forwards";setTimeout(function(){if(t.parentNode)t.parentNode.removeChild(t);if(wrap.parentNode)wrap.parentNode.removeChild(wrap);},350);},2800);
    });
})();
</script>
