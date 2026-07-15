<?php
session_start();
// vista___SPARTA_SECRET_REDACTED__.php
date_default_timezone_set('America/Mexico_City');
$layoutVendorLite = true;

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

$fechaUltimoPagoCompleto = null;

foreach ($tabla as $fila) {
    $pendiente = safe($fila['pendiente'], 0.0);
    $aplicados = safe($fila['aplicados'], []);

    if ($pendiente <= 0 && !empty($aplicados)) {
        $lastPagoDate = null;

        foreach ($aplicados as $a) {
            if (!empty($a['no_cuenta_para_total_cuota'])) {
                continue;
            }
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

$esGestionExternaMx          = !empty($esGestionExternaMx);
$gestionExternaEtiquetaCelula = isset($gestionExternaEtiquetaCelula) ? (string) $gestionExternaEtiquetaCelula : '';

// Badge Estatus Crédito (barra principal): utilidades Bootstrap sólidas (text-bg-*) — bg-label-* usa color-mix y a veces se ve “gris”
$_ecRawSt = trim((string) ($dataEstadoCuenta['statusCredito'] ?? ''));
$_ecRawSt = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $_ecRawSt);
$_ecRawSt = preg_replace('/\s+/u', ' ', $_ecRawSt);
$_ecNormSt = mb_strtoupper($_ecRawSt, 'UTF-8');
if (str_contains($_ecNormSt, 'SALDADO')) {
    $ecEstatusCreditoBadgeClass = 'text-bg-success';
} elseif (str_contains($_ecNormSt, 'VIGENTE') || $_ecNormSt === 'ACTIVO') {
    $ecEstatusCreditoBadgeClass = 'text-bg-primary';
} else {
    $ecEstatusCreditoBadgeClass = 'text-bg-secondary';
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
       LIQUID GLASS – Estado de Cuenta (sidebar, tabla, métricas, cards)
   ========================== */
    /* Sidebar: card exterior (la que envuelve todo) y card avatar */
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
    /* Sidebar: badges IDs, barra de progreso y bloques de métricas redondeados (Liquid Glass) */
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
    .estado-cuenta-page .sidebar-cliente .ec-sidebar-metricas-row > div {
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
    /* Contenedor exterior: línea negra (borde CSS); las tres cajas internas siguen con box-shadow para curvas limpias. */
    .estado-cuenta-page .ec-resumen-pagos-metricas-wrap {
        background: rgba(248, 249, 250, 0.9) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid #000000 !important;
        box-shadow: 0 0 0 1px #000000 !important;
    }
    .estado-cuenta-page .ec-metrica-pago-item {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border: none !important;
        box-shadow: 0 0 0 1px #000000 !important;
    }
    html.dark-mode .estado-cuenta-page .ec-resumen-pagos-metricas-wrap,
    body.dark-mode .estado-cuenta-page .ec-resumen-pagos-metricas-wrap {
        background: rgba(30, 41, 59, 0.7) !important;
        border-color: #94a3b8 !important;
        box-shadow: 0 0 0 1px #94a3b8 !important;
    }
    html.dark-mode .estado-cuenta-page .ec-metrica-pago-item,
    body.dark-mode .estado-cuenta-page .ec-metrica-pago-item {
        background: rgba(51, 65, 85, 0.8) !important;
        box-shadow: 0 0 0 1px #94a3b8 !important;
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
       SIDEBAR COMPLETO - ZOOM RESPONSIVE
   ========================== */

    /* 1. CONTENEDOR PRINCIPAL */
    .sidebar-cliente .card-body {
        padding: 1rem !important;
    }

    @media (min-width: 768px) {
        .sidebar-cliente .card-body {
            padding: 1.25rem !important;
        }
    }

    /* 2. SECCIÓN AVATAR (optimizada) */
    .user-avatar-section .card {
        margin-bottom: 1rem !important;
        transition: all 0.2s ease;
    }

    .user-avatar-section .card .card-body {
        padding: 0.75rem !important;
    }

    /* Badges en línea con zoom */
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

    .gestion-externa-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .gestion-externa-badges .badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 0.35em 0.65em;
        max-width: 100%;
    }

    .gestion-externa-badges .badge-celula {
        font-weight: 500;
    }

    /* Escalado de fuentes con zoom */
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

    /* 3. MÉTRICAS (flex escalable): saldo vencido + gasto cobranza */
    .sidebar-cliente .ec-sidebar-metricas-row {
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        margin: 1rem 0;
    }

    .sidebar-cliente .ec-sidebar-metricas-row > div {
        flex: 0 1 auto;
        min-width: 0;
        max-width: 100%;
    }

    .sidebar-cliente .ec-sidebar-metricas-row h5 {
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }

    .sidebar-cliente .ec-sidebar-metricas-row span.small {
        font-size: 0.75rem;
    }

    /* ==========================
   INFORMACIÓN DEL CRÉDITO - VERSIÓN UNIFICADA (GRID)
   ========================== */

   /* Base para todos los dispositivos */
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

   /* ==========================
      RESPONSIVE - INFO COMPACT
   ========================== */

   /* Tablet */
   @media (max-width: 768px) {
       .sidebar-cliente .info-compact li {
           grid-template-columns: 1.2rem 1fr;
           gap: 0.3rem;
           margin-bottom: 0.15rem;
       }

       .sidebar-cliente .info-compact i.fa-lg {
           font-size: 0.85rem;
       }

       .sidebar-cliente .info-compact .info-label span:first-child {
           font-size: 0.8rem;
       }

       .sidebar-cliente .info-compact .info-label span:last-child {
           font-size: 0.85rem;
           padding-left: 0.75rem;
       }
   }

   /* Móviles pequeños */
   @media (max-width: 400px) {
       .sidebar-cliente .info-compact li {
           grid-template-columns: 1rem 1fr;
           gap: 0.3rem;
       }

       .sidebar-cliente .info-compact i.fa-lg {
           font-size: 0.8rem;
       }

       .sidebar-cliente .info-compact .info-label {
           flex-direction: column;
           align-items: flex-start;
           gap: 0.1rem;
       }

       .sidebar-cliente .info-compact .info-label span:last-child {
           text-align: left;
           padding-left: 0;
           width: 100%;
           font-size: 0.8rem;
       }
   }

    /* 5. BOTÓN REFERENCIAS */
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

    /* ==========================
       AJUSTES ESPECÍFICOS POR NIVEL DE ZOOM
   ========================== */

    /* Zoom ~125% */
    @media (max-width: 1600px) {
        .sidebar-cliente .card-body {
            padding: 0.9rem !important;
        }

        .badge-container-ids .badge {
            font-size: 0.7rem;
            padding: 0.35em 0.6em;
        }

        .user-avatar-section .h6 {
            font-size: 0.95rem;
        }

        .sidebar-cliente .info-compact .info-label span:first-child,
        .sidebar-cliente .info-compact .info-label span:last-child {
            font-size: 0.8rem;
        }

        .sidebar-cliente .ec-sidebar-metricas-row h5 {
            font-size: 0.9rem;
        }
    }

    /* Zoom ~150% */
    @media (max-width: 1400px) {
        .sidebar-cliente .card-body {
            padding: 0.8rem !important;
        }

        .user-avatar-section .card .card-body {
            padding: 0.6rem !important;
        }

        .badge-container-ids .badge {
            font-size: 0.65rem;
            padding: 0.3em 0.5em;
        }

        .user-avatar-section .h6 {
            font-size: 0.9rem;
        }

        .sidebar-cliente .info-compact li {
            margin-bottom: 0.4rem;
        }

        .sidebar-cliente .info-compact .info-label span:first-child,
        .sidebar-cliente .info-compact .info-label span:last-child {
            font-size: 0.75rem;
        }

        .sidebar-cliente .info-compact i.fa-lg {
            font-size: 0.85rem !important;
        }

        .sidebar-cliente .ec-sidebar-metricas-row h5 {
            font-size: 0.85rem;
        }

        .sidebar-cliente .btn-outline-primary {
            font-size: 0.8rem;
            padding: 0.5rem 1rem !important;
        }
    }

    /* Zoom ~175%+ */
    @media (max-width: 1200px) {
        .badge-container-ids {
            flex-direction: column;
            gap: 0.3rem;
        }

        .badge-container-ids .badge {
            width: 100%;
            max-width: 100%;
            text-align: center;
        }

        .sidebar-cliente .ec-sidebar-metricas-row > div {
            flex: 1 1 100%;
        }
    }

    /* ==========================
       CORRECCIÓN MÓVIL - SIDEBAR GENERAL
   ========================== */

    @media (max-width: 768px) {
        .sidebar-cliente .card-body {
            padding: 0.75rem !important;
        }

        .badge-container-ids {
            flex-direction: row;
            gap: 0.5rem;
        }

        .badge-container-ids .badge {
            flex: 1 1 calc(50% - 0.5rem);
            max-width: calc(50% - 0.5rem);
        }

        .sidebar-cliente .ec-sidebar-metricas-row {
            flex-direction: column;
            gap: 0.5rem;
        }

        .sidebar-cliente .btn-outline-primary {
            padding: 0.5rem 1rem !important;
            margin: 0.5rem 0.25rem !important;
            width: calc(100% - 0.5rem) !important;
        }
    }

    /* ==========================
       ASEGURAR VISIBILIDAD BOTONES HEADER
   ========================== */

   /* MOD: Asegurar que los botones del header sean visibles */
   .col-xl-8.col-lg-7 .d-flex.justify-content-between.align-items-center.mb-3 > .d-flex.gap-2 {
       display: flex !important;
       visibility: visible !important;
       opacity: 1 !important;
   }

   .btn-dictaminar,
   .btn-condonar,
   .btn-notas,
   .btn-aclaraciones,
   .btn-rastreo-neverpaid {
       display: flex !important;
       visibility: visible !important;
   }

   .btn-outline-secondary {
       display: inline-flex !important;
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
        padding: 0.35em 0.6em !important;
        border-radius: 10px;
        font-weight: 500;
    }

    /* Contenedor tabla: esquinas redondeadas (Liquid Glass); sin overflow:hidden para no romper el scroll */
    .estado-cuenta-page .tabla-scrollable {
        border-radius: 14px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
    }
    html.dark-mode .estado-cuenta-page .tabla-scrollable,
    body.dark-mode .estado-cuenta-page .tabla-scrollable {
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.2);
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
            scrollbar-gutter: stable both-edges;
        }

        .tabla-scrollable thead th {
            position: sticky;
            top: 0;
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
            border-radius: 8px;
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

        .sidebar-cliente .info-compact .info-label span:first-child,
        .sidebar-cliente .info-compact .info-label span:last-child {
            font-size: 0.72rem !important;
        }

        .sidebar-cliente .info-compact i.fa-lg {
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

    /* ===== Reference WOW Card ===== */
    .reference-card {
        border-radius: 14px;
        padding: 1.25rem 1.25rem 1.4rem;
        position: relative;
        border: 1px solid rgba(0,0,0,.06);
        box-shadow: 0 6px 18px rgba(0,0,0,.08);
        transition: transform .25s ease, box-shadow .25s ease;
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .reference-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 14px 34px rgba(0,0,0,.18);
    }

    /* Header */
    .reference-header {
        display: flex;
        align-items: center;
        gap: .6rem;
        font-weight: 600;
        font-size: .95rem;
        margin-bottom: .5rem;
    }

    /* Divider line */
    .reference-divider {
        border-top: 1px dashed rgba(0,0,0,.15);
        margin: .6rem 0 .8rem;
    }

    /* Info rows */
    .info-line {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: .5rem;
        font-size: .82rem;
        padding: .25rem 0;
    }

    .info-line > span {
        flex-shrink: 0;
        color: #6c757d;
    }

    .info-line strong {
        font-weight: 600;
        color: #212529;
        min-width: 0;
        flex: 1 1 0%;
        text-align: right;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    /* Badge top-right */
    .reference-badge {
        position: absolute;
        top: 12px;
        right: 14px;
        font-size: .65rem;
    }


    /* ==========================
   NOTAS - ESTILO POST-IT
   ========================== */

    .nota-card {
        background: #fff9db;
        border-left: 6px solid #f0ad4e;
        border-radius: 10px;
        padding: 14px 16px;
        box-shadow: 0 6px 14px rgba(0,0,0,.12);
        position: relative;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .nota-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,.2);
    }

    .nota-fecha {
        font-size: .7rem;
        color: #856404;
        margin-bottom: 6px;
    }

    .nota-texto {
        font-size: .85rem;
        color: #212529;
        margin-bottom: 10px;
        white-space: pre-wrap;
    }

    .nota-autor {
        font-size: .7rem;
        color: #6c757d;
        text-align: right;
    }

    /* ==========================
   BOTÓN ICONO NOTAS
   ========================== */

    .btn-notas {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #fff3cd;
        border: 1px solid #ffecb5;
        color: #f0ad4e;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(240,173,78,.35);
        transition: all .25s ease;
    }

    .btn-notas i {
        font-size: 1.1rem;
    }

    .btn-notas:hover {
        background: #ffe69c;
        color: #d39e00;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(240,173,78,.55);
    }

    /* Badge notificaciones */
    .btn-notas .badge {
        font-size: .6rem;
        padding: .35em .45em;
    }

    /* ==========================
   BOTÓN ICONO CONDONAR
   ========================== */
    .btn-condonar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #e6f4ea;
        border: 1px solid #b7dfc3;
        color: #28a745;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(40,167,69,.35);
        transition: all .25s ease;
    }

    .btn-condonar i {
        font-size: 1.1rem;
    }

    .btn-condonar:hover {
        background: #c8ead3;
        color: #1e7e34;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(40,167,69,.55);
    }

    /* Rastreo (misma forma que dictaminar, condonar, notas) */
    .btn-rastreo-neverpaid {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #fff4e6;
        border: 1px solid #ffd8a8;
        color: #e8590c;
        display: flex !important;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(232, 89, 12, 0.3);
        transition: all .25s ease;
        text-decoration: none !important;
        padding: 0;
    }
    .btn-rastreo-neverpaid i {
        font-size: 1.1rem;
    }
    .btn-rastreo-neverpaid:hover {
        background: #ffe8cc;
        color: #c2410c;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(232, 89, 12, 0.45);
    }

    /* Billete volando */
    .billete {
        position: fixed;
        font-size: 1.5rem;
        animation: volarBillete 1s ease-out forwards;
        pointer-events: none;
        z-index: 9999;
    }

    @keyframes volarBillete {
        0% {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
        100% {
            opacity: 0;
            transform: translateY(-120px) scale(.4);
        }
    }


    /* ==========================
   BOTÓN ICONO DICTAMINAR
   ========================== */
    .btn-dictaminar {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #e7f0ff;
        border: 1px solid #b6d4fe;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(13,110,253,.35);
        transition: all .25s ease;
    }

    .btn-dictaminar i {
        font-size: 1.1rem;
    }

    .btn-dictaminar:hover {
        background: #cfe2ff;
        color: #084298;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(13,110,253,.55);
    }

    /* ==========================
   BOTÓN ICONO ACLARACIONES
   ========================== */
    .btn-aclaraciones {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #e8f4fc;
        border: 1px solid #b8dce8;
        color: #0aa2c0;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 10px rgba(10, 162, 192, 0.3);
        transition: all .25s ease;
        padding: 0;
    }
    .btn-aclaraciones i {
        font-size: 1.1rem;
    }
    .btn-aclaraciones:hover {
        background: #d0ebf5;
        color: #088395;
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(10, 162, 192, 0.45);
    }

    /* ==========================
       MODAL DIRECCIONES - Asegurar que se muestre
       ========================== */
    #modalDirecciones {
        z-index: 1090 !important;
    }

    #modalDirecciones .modal-dialog {
        z-index: 1091 !important;
        margin: 1.75rem auto !important;
    }

    /* Scrim propio para Condonar / Condonación parcial (por encima de todo el layout) */
    .scrim-condonar-estado-cuenta {
        z-index: 9998 !important;
        background: rgba(0, 0, 0, 0.5) !important;
    }

    /* Alertas (SweetAlert) por encima de los modales Condonar / Condonación parcial */
    body.modal-condonar-open .swal2-container,
    body.modal-condonar-parcial-open .swal2-container {
        z-index: 10001 !important;
    }

    /* Liquid glass + scrim: modales Condonar y Condonación parcial */
    #modalCondonar .modal-content,
    #modalCondonarParcial .modal-content {
        background: rgba(255, 255, 255, 0.85) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 12px;
    }
    #modalCondonar .modal-header,
    #modalCondonarParcial .modal-header {
        background: rgba(255, 255, 255, 0.4) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 12px 12px 0 0;
    }
    #modalCondonar .modal-body,
    #modalCondonarParcial .modal-body {
        background: rgba(255, 255, 255, 0.5) !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    #modalCondonar .modal-footer,
    #modalCondonarParcial .modal-footer {
        background: rgba(255, 255, 255, 0.4) !important;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 0 0 12px 12px;
    }

    /* ==========================
       MÓVIL VERTICAL - ORDEN CORRECTO
   ========================== */
   @media (max-width: 576px) {
       .sidebar-cliente {
           order: -1 !important;
           margin-bottom: 1rem;
       }

       .col-xl-8.col-lg-7 {
           order: 1 !important;
       }

       .row {
           margin-bottom: 0.5rem;
       }

       .sidebar-cliente .card {
           margin-bottom: 0.75rem !important;
       }

       .badge-container-ids .badge {
           font-size: 0.65rem !important;
           padding: 0.35em 0.5em !important;
       }

       .col-xl-8.col-lg-7 .d-flex.justify-content-between.align-items-center.mb-3,
       .col-xl-8.col-lg-7 .ec-resumen-pagos-toolbar {
           flex-direction: column;
           align-items: flex-start;
           margin-bottom: 0.75rem !important;
       }

       .col-xl-8.col-lg-7 .d-flex.justify-content-between.align-items-center.mb-3 h5,
       .col-xl-8.col-lg-7 .ec-resumen-pagos-toolbar .ec-toolbar-titulo {
           font-size: 0.85rem;
           margin-bottom: 0.5rem;
           width: 100%;
       }

       .btn-notas, .btn-condonar, .btn-dictaminar, .btn-aclaraciones, .btn-rastreo-neverpaid {
           width: 36px !important;
           height: 36px !important;
       }

       .btn-notas i, .btn-condonar i, .btn-dictaminar i, .btn-aclaraciones i, .btn-rastreo-neverpaid i {
           font-size: 0.9rem !important;
       }

       /* Tipografía de métricas: la fila única .ec-metricas-fila-principal usa clamp/cqw arriba */
       .ec-resumen-pagos-metricas-wrap .ec-metrica-pago-item h5 {
           font-size: inherit;
       }

       .ec-resumen-pagos-metricas-wrap .ec-metrica-pago-item > div:last-child span {
           font-size: inherit;
       }

       .tabla-scrollable {
           margin-top: 0.5rem;
       }

       .cuotas-table td,
       .cuotas-table th {
           font-size: 0.6rem !important;
           padding: 0.3rem !important;
       }

       .cuotas-table .badge {
           font-size: 0.55rem !important;
           padding: 0.2em 0.3em !important;
       }
   }

    /* ==========================
       ACORDEÓN PERSONALIZADO - VERSIÓN ÚNICA
   ========================== */

   .accordion-button::after {
       display: none !important;
   }

   .accordion-flush.custom-accordion {
       border: none;
       background: transparent;
       margin-top: 1.5rem !important; /* MOD: Más margen superior */
       padding-top: 0.5rem;
       border-top: 1px solid rgba(0,0,0,0.05);
   }

   .accordion-button {
       background-color: #f8f9fa !important;
       border: 1px solid #dee2e6 !important;
       border-radius: 8px !important;
       padding: 0.75rem 1rem !important;
       font-weight: 500;
       transition: all 0.3s ease;
       box-shadow: 0 2px 4px rgba(0,0,0,0.05);
   }

   .accordion-button:not(.collapsed) {
       background-color: #e7f1ff !important;
       border-color: #b6d4fe !important;
       color: #0d6efd;
       box-shadow: 0 4px 8px rgba(13,110,253,0.1);
   }

   .accordion-button:focus {
       box-shadow: 0 0 0 3px rgba(13,110,253,0.25) !important;
       border-color: #86b7fe !important;
   }

   .accordion-arrow {
       transition: transform 0.3s ease;
       font-size: 0.85rem;
       color: #6c757d;
       margin-left: auto;
   }

   .accordion-button:not(.collapsed) .accordion-arrow {
       transform: rotate(180deg);
       color: #0d6efd;
   }

   .accordion-collapse {
       border: none !important;
   }

   .accordion-body {
       background-color: transparent;
       padding: 1rem !important; /* MOD: Padding interno */
   }

   .accordion-body .ec-sidebar-metricas-row {
       margin-top: 0.75rem !important;
       margin-bottom: 1rem !important;
   }

   .accordion-body .info-compact {
       margin: 0.75rem 0 !important;
   }

   .accordion-body .btn-outline-primary {
       margin: 1.25rem 0.75rem 0.75rem 0.75rem !important;
       padding: 0.65rem 1.5rem !important;
       width: calc(100% - 1.5rem) !important;
   }

   @media (max-width: 768px) {
       .accordion-button {
           padding: 0.6rem 0.8rem !important;
           font-size: 0.9rem;
       }

       .accordion-button i.fa-info-circle {
           font-size: 0.9rem;
       }

       .accordion-arrow {
           font-size: 0.8rem;
       }

       .accordion-body {
           padding: 0.75rem !important;
       }
   }

   @media (max-width: 576px) {
       .accordion-button {
           padding: 0.5rem 0.7rem !important;
           font-size: 0.85rem;
       }

       .accordion-button span.fw-semibold {
           font-size: 0.85rem;
       }

       .accordion-arrow {
           font-size: 0.75rem;
       }

       .accordion-body {
           padding: 0.5rem !important;
       }

       .accordion-body .btn-outline-primary {
           margin: 1rem 0.5rem 0.5rem 0.5rem !important;
           padding: 0.55rem 1.25rem !important;
           width: calc(100% - 1rem) !important;
       }
   }

   .accordion-collapse {
       transition: all 0.35s ease !important;
   }

   .accordion-body {
       animation: fadeIn 0.3s ease;
   }

   @keyframes fadeIn {
       from {
           opacity: 0;
           transform: translateY(-10px);
       }
       to {
           opacity: 1;
           transform: translateY(0);
       }
   }

    /* ==========================
       Resumen pagos — cqw mide cada .col (no el wrap); STP encaja en la 3ª columna.
   ========================== */

   /* Evita que .card recorte el borde redondeado del resumen */
   .ec-card-metricas-resumen,
   .ec-card-metricas-resumen .card-body.p-0 {
       overflow: visible;
   }

   /* Contenedor ancho: solo para --ec-metrica-gap (cqw de toda la franja). */
   .ec-resumen-pagos-metricas-wrap {
       padding: 1rem 1.25rem !important;
       border-radius: 10px;
       margin: 0 !important;
       border: 1px solid #000000;
       /* Refuerzo del trazo en esquinas (algunos navegadores recortan border+backdrop) */
       box-shadow: 0 0 0 1px #000000;
       container-type: inline-size;
       container-name: ec-metricas-pagos;
       overflow: visible;
   }

   /* Cada columna es su propio contenedor: cqw en tipografía = ancho de tarjeta (~1/3), no del panel. */
   .ec-resumen-pagos-metricas-wrap > .row > .col {
       container-type: inline-size;
       container-name: ec-metrica-col;
   }

   .ec-metrica-pago-item {
       padding: clamp(0.35rem, 1.2cqw + 0.25rem, 0.75rem) clamp(0.25rem, 1.5cqw + 0.2rem, 0.75rem);
       border-radius: 8px;
       border: none;
       box-shadow: 0 0 0 1px #000000;
       min-height: 100%;
   }

   /* Tres métricas en una fila en tablet/desktop; en celular se apilan (ver media query). */
   .ec-metricas-fila-principal {
       flex-wrap: nowrap !important;
       --ec-metrica-gap: clamp(0.2rem, 2.5cqw, 0.65rem);
   }

   .ec-metricas-fila-principal > .ec-metrica-pago-col {
       flex: 1 1 0 !important;
       min-width: 0 !important;
       max-width: none !important;
       width: auto !important;
   }

   .ec-metricas-fila-principal .ec-metrica-pago-item {
       gap: var(--ec-metrica-gap) !important;
   }

   .ec-metricas-fila-principal .avatar.flex-shrink-0 {
       flex-shrink: 0 !important;
   }

   .ec-metricas-fila-principal .avatar-initial {
       width: clamp(1.65rem, 18cqw, 2.75rem) !important;
       height: clamp(1.65rem, 18cqw, 2.75rem) !important;
       min-width: 0;
       font-size: clamp(0.65rem, 10cqw, 1.125rem) !important;
   }

   .ec-metricas-fila-principal .avatar-initial .fa {
       font-size: 1em;
   }

   .ec-metricas-fila-principal .ec-metrica-pago-text {
       min-width: 0;
       max-width: 100%;
   }

   /* Una sola línea; cqw = columna. Mins bajos si estrecho; max alto si hay espacio. */
   .ec-metricas-fila-principal .ec-metrica-pago-text h5.mb-0 {
       font-size: clamp(0.28rem, 12cqw + 0.08rem, 1.375rem);
       line-height: 1.12;
       white-space: nowrap;
       overflow: visible;
       max-width: 100%;
       text-overflow: clip;
       font-variant-numeric: tabular-nums;
   }

   .ec-metricas-fila-principal .ec-metrica-pago-text > span {
       display: block;
       font-size: clamp(0.26rem, 9cqw + 0.06rem, 0.9375rem);
       line-height: 1.1;
       white-space: nowrap;
       overflow: visible;
       max-width: 100%;
       text-overflow: clip;
   }

   .ec-metricas-fila-principal .ec-metrica-pago-col--ref .ec-metrica-pago-text h5.mb-0 {
       /* Ref. larga: mismo tope que cuota/fecha en pantalla ancha; encoge solo si la columna es angosta */
       font-size: clamp(0.2rem, 14cqw + 0.04rem, 1.125rem);
       letter-spacing: -0.04em;
       font-family: ui-monospace, "Cascadia Code", "Segoe UI", Consolas, monospace;
       text-overflow: clip;
   }

   /* Pantallas medianas-bajas: aún en una fila; max algo menor que desktop ancho */
   @media (max-width: 991.98px) and (min-width: 577px) {
       .ec-metricas-fila-principal .ec-metrica-pago-text h5.mb-0 {
           font-size: clamp(0.26rem, 13cqw + 0.06rem, 1.1875rem);
       }
       .ec-metricas-fila-principal .ec-metrica-pago-text > span {
           font-size: clamp(0.24rem, 10cqw + 0.05rem, 0.875rem);
       }
       .ec-metricas-fila-principal .ec-metrica-pago-col--ref .ec-metrica-pago-text h5.mb-0 {
           font-size: clamp(0.2rem, 15cqw + 0.03rem, 1rem);
       }
   }

   @media (max-width: 768px) {
       .ec-resumen-pagos-metricas-wrap {
           padding: 0.5rem 0.45rem !important;
       }
   }

   /* Celular: una tarjeta por fila, texto legible y referencia puede partirse */
   @media (max-width: 576px) {
       .ec-metricas-fila-principal {
           flex-wrap: wrap !important;
           row-gap: 0.5rem;
       }
       .ec-metricas-fila-principal > .ec-metrica-pago-col {
           flex: 0 0 100% !important;
           max-width: 100% !important;
           width: 100% !important;
       }
       .ec-metricas-fila-principal .ec-metrica-pago-text h5.mb-0,
       .ec-metricas-fila-principal .ec-metrica-pago-col--ref .ec-metrica-pago-text h5.mb-0 {
           font-size: 1rem !important;
           white-space: normal !important;
           overflow: visible !important;
           text-overflow: unset !important;
           max-width: none !important;
           word-break: break-word;
       }
       .ec-metricas-fila-principal .ec-metrica-pago-col--ref .ec-metrica-pago-text h5.mb-0 {
           word-break: break-all;
       }
       .ec-metricas-fila-principal .ec-metrica-pago-text > span {
           font-size: 0.8125rem !important;
           white-space: normal !important;
           overflow: visible !important;
           text-overflow: unset !important;
           max-width: none !important;
       }
       .ec-metrica-pago-item {
           min-height: 0;
       }
   }

   /* Sin container queries: ~1/3 del viewport por columna */
   @supports not (container-type: inline-size) {
       .ec-metricas-fila-principal .ec-metrica-pago-text h5.mb-0 {
           font-size: clamp(0.28rem, 3.6vw + 0.08rem, 1.375rem);
       }
       .ec-metricas-fila-principal .ec-metrica-pago-text > span {
           font-size: clamp(0.26rem, 2.8vw + 0.06rem, 0.9375rem);
       }
       .ec-metricas-fila-principal .ec-metrica-pago-col--ref .ec-metrica-pago-text h5.mb-0 {
           font-size: clamp(0.2rem, 4.2vw + 0.04rem, 1.125rem);
       }
   }

   /* ==========================
   ALINEACIÓN PERFECTA MÓVIL - INFO CRÉDITO
   ========================== */

@media (max-width: 768px) {
    /* Contenedor principal ajustado */
    .sidebar-cliente .info-compact {
        margin: 0.5rem 0;
        padding: 0 0.25rem;
    }

    /* Cada item con grid de 2 columnas */
    .sidebar-cliente .info-compact li {
        display: grid !important;
        grid-template-columns: 1.2rem minmax(140px, 1fr) auto;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        padding: 0.2rem 0;
    }

    /* Icono en columna 1 */
    .sidebar-cliente .info-compact i.fa-lg {
        grid-column: 1;
        font-size: 0.85rem;
        text-align: center;
        width: 1.2rem;
    }

    /* Etiqueta (texto) en columna 2 */
    .sidebar-cliente .info-compact .info-label span:first-child {
        grid-column: 2;
        font-size: 0.8rem;
        font-weight: 500;
        color: #495057;
        white-space: nowrap;
        text-align: left;
        padding-right: 0.5rem;
        min-width: 140px;
    }

    /* Valor (número) en columna 3 - ALINEADO A LA DERECHA */
    .sidebar-cliente .info-compact .info-label span:last-child {
        grid-column: 3;
        font-size: 0.85rem;
        font-weight: 600;
        color: #212529;
        text-align: right !important;
        white-space: nowrap;
        padding-left: 0;
        min-width: max-content;
        margin-left: auto;
    }

    /* Para items monetarios (con $) */
    .sidebar-cliente .info-compact .info-label span:last-child:contains("$") {
        letter-spacing: -0.5px; /* Compactar números con $ */
    }
}

/* Móviles muy pequeños (≤ 400px) */
@media (max-width: 400px) {
    .sidebar-cliente .info-compact li {
        grid-template-columns: 1rem minmax(120px, 1fr) auto;
        gap: 0.4rem;
    }

    .sidebar-cliente .info-compact i.fa-lg {
        font-size: 0.8rem;
        width: 1rem;
    }

    .sidebar-cliente .info-compact .info-label span:first-child {
        font-size: 0.75rem;
        min-width: 120px;
    }

    .sidebar-cliente .info-compact .info-label span:last-child {
        font-size: 0.8rem;
    }
}

/* ==========================
   ALINEACIÓN ESPECÍFICA PARA VALORES MONETARIOS
   ========================== */

/* Asegurar que valores con decimales se alineen perfectamente */
.sidebar-cliente .info-compact .info-label span:last-child {
    font-family: 'Segoe UI', 'Roboto', 'SF Mono', Monaco, Consolas, monospace;
    font-variant-numeric: tabular-nums; /* Números con ancho fijo */
}

/* Para móvil, forzar alineación decimal */
@media (max-width: 768px) {
    .sidebar-cliente .info-compact .info-label {
        display: grid !important;
        grid-template-columns: 1fr auto;
        width: 100%;
        gap: 0.5rem;
    }

    /* Asegurar que la etiqueta ocupe espacio disponible */
    .sidebar-cliente .info-compact .info-label span:first-child {
        text-align: right; /* Alinear etiquetas a la derecha también */
        padding-right: 0.75rem;
    }

    /* Valores con ancho fijo para alineación perfecta */
    .sidebar-cliente .info-compact .info-label span:last-child {
        min-width: 100px;
        text-align: left; /* Cambiar a izquierda para alineación con : */
    }
}




/* ==========================
   VERSIÓN ALTERNATIVA - USANDO FLEXBOX PARA ALINEACIÓN
   ========================== */

/* Si grid no funciona bien, probar con flexbox */
@media (max-width: 768px) {
    .sidebar-cliente .info-compact .info-label {
        display: flex !important;
        justify-content: space-between;
        align-items: center;
        width: 100%;
    }

    .sidebar-cliente .info-compact .info-label span:first-child {
        flex: 1;
        text-align: left;
        padding-right: 1rem;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-cliente .info-compact .info-label span:last-child {
        flex-shrink: 0;
        text-align: right;
        min-width: 100px;
        font-family: 'SF Mono', Monaco, Consolas, 'Liberation Mono', monospace;
    }
}

</style>
<style>
/* Restaurar colores de métricas en modo claro */
body:not(.dark-mode) .ec-resumen-pagos-metricas-wrap .ec-metrica-pago-item h5,
body:not(.dark-mode) .ec-sidebar-metricas-row h5 {
    color: #212529 !important;
}
body:not(.dark-mode) .ec-resumen-pagos-metricas-wrap .ec-metrica-pago-item > div:last-child span,
body:not(.dark-mode) .ec-sidebar-metricas-row span.small {
    color: #6c757d !important;
}
body:not(.dark-mode) .sidebar-cliente .info-compact .info-label span:first-child {
    color: #495057 !important;
}
body:not(.dark-mode) .sidebar-cliente .info-compact .info-label span:last-child {
    color: #212529 !important;
}
body:not(.dark-mode) .sidebar-cliente .info-compact i.fa-lg {
    color: #6c757d !important;
}

/* ========== ESTADO DE CUENTA - MODO CLARO (sidebar completo visible) ========== */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente {
    --ec-light-bg: #ffffff;
    --ec-light-border: #dee2e6;
    --ec-text: #212529;
    --ec-text-muted: #6c757d;
}
/* Cards del sidebar: fondo sólido y texto oscuro */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente > .card,
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente > .card,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card {
    background: var(--ec-light-bg) !important;
    color: var(--ec-text) !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card-body,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card-body {
    color: var(--ec-text) !important;
}
/* Sección avatar: nombre, RFC, teléfono, badges */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section .h6,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section .h6 {
    color: var(--ec-text) !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section small,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section small {
    color: var(--ec-text-muted) !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section .btn-link,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .user-avatar-section .btn-link {
    color: #0d6efd !important;
}
/* Separadores y líneas (hr) visibles en modo claro */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente hr,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente hr {
    border-color: var(--ec-light-border) !important;
    opacity: 1;
}
/* Saldo Total Vencido (sidebar): texto y etiquetas */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .ec-sidebar-metricas-row h5,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .ec-sidebar-metricas-row h5 {
    color: var(--ec-text) !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .ec-sidebar-metricas-row span.small,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .ec-sidebar-metricas-row span.small {
    color: var(--ec-text-muted) !important;
}
/* Iconos de los bloques (Estatus / Saldo): fondo sólido y icono blanco para buen contraste */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .avatar-initial.bg-label-info,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .avatar-initial.bg-label-info {
    background-color: #0dcaf0 !important;
    color: #fff !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .avatar-initial.bg-label-info .fa,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .avatar-initial.bg-label-info i {
    color: #fff !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .avatar-initial.bg-label-danger,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .avatar-initial.bg-label-danger {
    background-color: #dc3545 !important;
    color: #fff !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .avatar-initial.bg-label-danger .fa,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .avatar-initial.bg-label-danger i {
    color: #fff !important;
}
/* Título "Información del Crédito" */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card-text.text-body-secondary,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .card-text.text-body-secondary {
    color: var(--ec-text-muted) !important;
}
/* Lista info-compact: labels y valores */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact .info-label span:first-child,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact .info-label span:first-child {
    color: #495057 !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact .info-label span:last-child,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact .info-label span:last-child {
    color: var(--ec-text) !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact i.fa-lg,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .info-compact i.fa-lg {
    color: var(--ec-text-muted) !important;
}
/* Acordeón móvil: mismo contenido, mismo estilo en modo claro */
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-body,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-body {
    background: var(--ec-light-bg) !important;
    color: var(--ec-text) !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-body .fw-medium,
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-body span,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-body .fw-medium,
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-body span {
    color: var(--ec-text) !important;
}
html:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-button:not(.collapsed),
body:not(.dark-mode) .estado-cuenta-page .sidebar-cliente .accordion-button:not(.collapsed) {
    background: #f8f9fa !important;
    color: var(--ec-text) !important;
}
</style>
<style>
/* Restaurar colores de botones de acción en modo claro */
body:not(.dark-mode) .btn-dictaminar {
    background: #e7f0ff !important;
    border: 1px solid #b6d4fe !important;
    color: #0d6efd !important;
}
body:not(.dark-mode) .btn-dictaminar:hover {
    background: #cfe2ff !important;
    color: #084298 !important;
}
body:not(.dark-mode) .btn-condonar {
    background: #e6f4ea !important;
    border: 1px solid #b7dfc3 !important;
    color: #28a745 !important;
}
body:not(.dark-mode) .btn-condonar:hover {
    background: #c8ead3 !important;
    color: #1e7e34 !important;
}
body:not(.dark-mode) .btn-notas {
    background: #fff3cd !important;
    border: 1px solid #ffecb5 !important;
    color: #f0ad4e !important;
}
body:not(.dark-mode) .btn-notas:hover {
    background: #ffe69c !important;
    color: #d39e00 !important;
}
body:not(.dark-mode) .btn-rastreo-neverpaid {
    background: #fff4e6 !important;
    border: 1px solid #ffd8a8 !important;
    color: #e8590c !important;
}
body:not(.dark-mode) .btn-rastreo-neverpaid:hover {
    background: #ffe8cc !important;
    color: #c2410c !important;
}
body:not(.dark-mode) .btn-aclaraciones {
    background: #e8f4fc !important;
    border: 1px solid #b8dce8 !important;
    color: #0aa2c0 !important;
}
body:not(.dark-mode) .btn-aclaraciones:hover {
    background: #d0ebf5 !important;
    color: #088395 !important;
}
html.dark-mode .btn-rastreo-neverpaid,
body.dark-mode .btn-rastreo-neverpaid {
    background: rgba(251, 146, 60, 0.18) !important;
    border: 1px solid rgba(251, 146, 60, 0.4) !important;
    color: #fdba74 !important;
}
html.dark-mode .btn-rastreo-neverpaid:hover,
body.dark-mode .btn-rastreo-neverpaid:hover {
    background: rgba(251, 146, 60, 0.28) !important;
    color: #fed7aa !important;
}
</style>
<style>
/* Pagos del Cliente: Pago, Sobrante, Aplicado, Contracargo (modo claro y oscuro) */
.cuotas-table .etiqueta-pago { color: #0d6efd !important; }
.cuotas-table .etiqueta-sobrante { color: #6c757d !important; font-weight: bold; }
.cuotas-table .etiqueta-aplicado { color: #05611d !important; }
.cuotas-table .contracargo-label { color: #d35400 !important; font-weight: 700; }
.cuotas-table .contracargo-valor { color: #d35400 !important; font-weight: 600; }
.cuotas-table .pago-revertido { text-decoration: line-through; opacity: 0.5; }
/* Modo oscuro: colores visibles sobre fondo oscuro */
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

/* ── Anticipo a Capital ── */
.cuotas-table .fila-anticipo-capital td { background-color: #e0f7f4 !important; }
.cuotas-table .badge-anticipo { background-color: #0d9488 !important; color: #fff !important; font-size: .72rem; }
.cuotas-table .etiqueta-anticipo-pago { color: #0d9488 !important; font-weight: 600; }
.cuotas-table .etiqueta-anticipo-aplicado { color: #065f46 !important; font-weight: 600; }
/* Modo oscuro */
html.dark-mode .cuotas-table .fila-anticipo-capital td,
body.dark-mode  .cuotas-table .fila-anticipo-capital td { background-color: #134e4a !important; }
html.dark-mode .cuotas-table .etiqueta-anticipo-pago,
body.dark-mode  .cuotas-table .etiqueta-anticipo-pago { color: #2dd4bf !important; }
html.dark-mode .cuotas-table .etiqueta-anticipo-aplicado,
body.dark-mode  .cuotas-table .etiqueta-anticipo-aplicado { color: #5eead4 !important; }

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

/* Badge Recalculada: debajo del # cuota, sin invadir la columna del monto */
.cuotas-table .cuota-num-wrap { display: inline-block; max-width: 5.5rem; vertical-align: top; }
.cuotas-table .cuota-num-wrap .badge-recalculada { display: inline-block; margin-top: 0.25rem; margin-left: 0; max-width: 100%; white-space: normal; line-height: 1.15; font-size: 0.65rem; font-weight: 600; padding: 0.2rem 0.35rem; }
</style>

<div class="row estado-cuenta-page">

  <!-- SIDEBAR CLIENTE -->
<div class="col-xl-4 col-lg-5 order-1 order-lg-0 sidebar-cliente">
    <div class="card mb-6">
        <div class="card-body">

            <?php if ($esGestionExternaMx): ?>
            <div class="gestion-externa-badges mb-3">
                <span class="badge bg-label-warning text-dark">Gestión Externa</span>
                <?php if ($gestionExternaEtiquetaCelula !== ''): ?>
                <span class="badge bg-label-secondary badge-celula"><?= htmlspecialchars($gestionExternaEtiquetaCelula) ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- SECCIÓN AVATAR/DATOS BÁSICOS (SIEMPRE VISIBLE) -->
            <div class="user-avatar-section">
                <div class="card mb-3 border border-2 border-primary rounded primary-shadow">
                    <div class="card-body">
                        <!-- Badges IDs -->
                        <div class="badge-container-ids">
                            <span class="badge bg-label-primary">ID Crédito: <?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?></span>
                            <span class="badge bg-label-secondary">ID Cliente: <?= htmlspecialchars($dataCliente["idCliente"] ?? '') ?></span>
                        </div>

                        <!-- Nombre -->
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="h6 mb-0"> <?= htmlspecialchars($dataCliente["nombreCliente"] ?? '') ?></span>
                        </div>

                        <!-- Barra de progreso -->
                        <div class="progress mb-1" title="<?= $porcentajeAvance ?>%">
                            <div class="progress-bar bg-primary"
                                 role="progressbar"
                                 style="width: <?= $porcentajeAvance ?>%;"
                                 aria-valuenow="<?= $porcentajeAvance ?>"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>

                        <!-- Teléfono y dirección -->
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
                            <button type="button"
                                    class="btn btn-link text-primary p-0"
                                    onclick="abrirModalDirecciones()">
                                Direcciones
                            </button>
                            <span class="text-nowrap">
                                <i class="fa fa-id-card text-primary"></i>
                                RFC: <span id="ec-rfc-inline" class="text-nowrap">…</span>
                            </span>
                        </small>
                    </div>
                </div>
            </div>

            <!-- ================================
               VERSIÓN DESKTOP (SIN ACORDEÓN)
            ================================ -->
            <div class="d-none d-lg-block desktop-info">
                <!-- MÉTRICAS (saldo vencido + pendiente GC; estatus en barra principal) -->
                <div class="d-flex flex-nowrap my-3 gap-2 gap-md-3 ec-sidebar-metricas-row">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="avatar flex-shrink-0">
                            <div class="avatar-initial bg-label-warning rounded w-px-40 h-px-40">
                                <i class="fa fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        <div class="text-start text-truncate">
                            <h5 class="mb-0 text-truncate">
                                <?= format_currency($dataOtrosDatos["saldoTotalPendienteGastoCobranza"] ?? 0) ?>
                            </h5>
                            <span class="small">Saldo GC pendiente</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <div class="avatar flex-shrink-0">
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



                <!-- INFORMACIÓN DEL CRÉDITO -->
              <hr class="my-2 w-100">
<small class="card-text text-uppercase text-body-secondary small">Información del Crédito</small>
<ul class="list-unstyled my-1 py-1 info-compact">
    <li>
        <i class="fa fa-money-bill fa-lg"></i>
        <div class="info-label">
            <span class="fw-medium">Monto Otorgado:</span>
            <span>$<?= number_format($dataEstadoCuenta["montoOtorgado"] ?? 0, 2, '.', ',') ?></span>
        </div>
    </li>

    <li>
        <i class="fa fa-money-bill-wave fa-lg"></i>
        <div class="info-label">
            <span class="fw-medium">Saldo Total Pagado:</span>
            <span><?= format_currency(isset($saldoTotalPagadoDesdeTabla) ? (float) $saldoTotalPagadoDesdeTabla : ((float)($dataEstadoCuenta["montoOtorgado"] ?? 0) - (float)($dataOtrosDatos["saldoTotalVigente"] ?? 0))) ?></span>
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

    <br>

    <button type="button"
            class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-2 py-2"
            data-bs-toggle="modal" data-bs-target="#modalRFC">
        <i class="fa fa-id-card fa-lg"></i>
        <strong>Ver referencias del cliente</strong>
    </button>
</ul>
            </div>

            <!-- ================================
               VERSIÓN MÓVIL (CON ACORDEÓN)
            ================================ -->
            <div class="d-lg-none mobile-info">
                <div class="accordion accordion-flush custom-accordion" id="accordionInfoCredito">
                    <div class="accordion-item">
                        <!-- HEADER DEL ACORDEÓN -->
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

                        <!-- CONTENIDO DEL ACORDEÓN -->
                        <div id="collapseInfoCredito"
                             class="accordion-collapse collapse"
                             aria-labelledby="headingInfoCredito"
                             data-bs-parent="#accordionInfoCredito">
                            <div class="accordion-body p-0 pt-3">

                                <!-- MÉTRICAS (saldo vencido + pendiente GC; estatus en barra principal) -->
                                <div class="d-flex flex-nowrap my-3 gap-2 gap-md-3 ec-sidebar-metricas-row">
                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                        <div class="avatar flex-shrink-0">
                                            <div class="avatar-initial bg-label-warning rounded w-px-40 h-px-40">
                                                <i class="fa fa-file-invoice-dollar"></i>
                                            </div>
                                        </div>
                                        <div class="text-start text-truncate">
                                            <h5 class="mb-0 text-truncate">
                                                <?= format_currency($dataOtrosDatos["saldoTotalPendienteGastoCobranza"] ?? 0) ?>
                                            </h5>
                                            <span class="small">Saldo GC pendiente</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                        <div class="avatar flex-shrink-0">
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

                                <!-- INFORMACIÓN DEL CRÉDITO -->
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
                </div>
            </div>

        </div>
    </div>
</div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="col-xl-8 col-lg-7 order-0 order-lg-1">
        <div class="d-flex flex-column flex-sm-row flex-wrap justify-content-between align-items-start align-items-sm-center gap-2 gap-sm-3 mb-3 ec-resumen-pagos-toolbar">
            <h5 class="mb-0 flex-shrink-0 ec-toolbar-titulo">Resumen general de pagos del cliente</h5>

            <div class="d-flex flex-wrap gap-2 align-items-center w-100 w-sm-auto justify-content-start justify-content-sm-end flex-shrink-0">
                <span class="align-middle rounded-3 px-3 py-2 fw-semibold fs-6 <?= htmlspecialchars($ecEstatusCreditoBadgeClass) ?> shadow-sm text-nowrap me-auto"
                      title="Estatus del crédito">Estatus Crédito: <?= htmlspecialchars($dataEstadoCuenta["statusCredito"] ?? '—') ?></span>
                <?php if (!empty($tienePermisoAclaracionesGc) && !empty($dataEstadoCuenta['idCredito'])): ?>
                <button type="button"
                        class="btn btn-aclaraciones position-relative"
                        title="Aclaraciones"
                        data-bs-toggle="modal"
                        data-bs-target="#modalAclaracionesGc">
                    <i class="fa fa-balance-scale" aria-hidden="true"></i>
                </button>
                <?php endif; ?>

                <?php if (!empty($tienePermisoDictaminarLlamada) && !empty($dataEstadoCuenta['idCredito'])): ?>
                <button type="button"
                        class="btn btn-dictaminar position-relative"
                        data-bs-toggle="modal"
                        data-bs-target="#modalDictamen"
                        title="Dictaminar llamada">
                    <i class="fa fa-headset"></i>
                </button>
                <?php endif; ?>

                <?php if (!empty($tienePermisoCondonarGastosCobranza) && !empty($dataEstadoCuenta['idCredito'])): ?>
                <button type="button"
                        class="btn btn-condonar position-relative"
                        title="<?= $esGestionExternaMx ? 'No disponible: gestión externa' : 'Condonar Gastos Cobranza' ?>"
                        onclick="consultaGastosCondonables(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">
                    <i class="fa fa-hand-holding-usd"></i>
                </button>
                <?php endif; ?>

                <?php if (!empty($tienePermisoNotasCliente) && !empty($dataEstadoCuenta['idCredito'])): ?>
                <button type="button"
                        class="btn btn-notas position-relative"
                        title="Notas del cliente"
                        onclick="consultaNotas(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">
                    <i class="fa fa-sticky-note"></i>
                    <span id="badgeNotas"
                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary">
                        …
                    </span>
                </button>
                <?php endif; ?>

                <?php if (!empty($tienePermisoRastreoNeverPaid) && !empty($dataEstadoCuenta['idCredito'])): ?>
                    <button type="button"
                            class="btn btn-rastreo-neverpaid position-relative"
                            title="Rastreo"
                            onclick='abrirRastreoNeverPaidModalEc(<?= json_encode((string)($dataEstadoCuenta['idCredito'] ?? ''), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE) ?>)'>
                        <i class="fa fa-id-card" aria-hidden="true"></i>
                    </button>
                <?php endif; ?>

                <a href="/EstadoCuenta/Consulta" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                    <i class="fa fa-search"></i>
                    <span>Nueva consulta</span>
                </a>
            </div>
        </div>


        <div class="card mb-6 ec-card-metricas-resumen">
            <div class="card-body p-0">
                <div class="ec-resumen-pagos-metricas-wrap">
                    <div class="row ec-metricas-fila-principal g-2">
                        <div class="col ec-metrica-pago-col">
                            <div class="d-flex align-items-center ec-metrica-pago-item">
                                <div class="avatar flex-shrink-0">
                                    <div class="avatar-initial bg-label-success rounded w-px-40 h-px-40">
                                        <i class="fa fa-dollar-sign"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-grow-1 ec-metrica-pago-text">
                                    <h5 class="mb-0 ec-fit-dynamic"><?= format_currency($dataEstadoCuenta["cuota"] ?? 0) ?></h5>
                                    <span>Cuota Semanal</span>
                                </div>
                            </div>
                        </div>
                        <div class="col ec-metrica-pago-col">
                            <div class="d-flex align-items-center ec-metrica-pago-item">
                                <div class="avatar flex-shrink-0">
                                    <div class="avatar-initial bg-label-primary rounded w-px-40 h-px-40">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-grow-1 ec-metrica-pago-text">
                                    <h5 class="mb-0 ec-fit-dynamic">
                                        <?= $fechaUltimoPagoCompleto
                                                ? format_date($fechaUltimoPagoCompleto)
                                                : '—'
                                        ?>
                                    </h5>
                                    <span>Último Pago</span>
                                </div>
                            </div>
                        </div>
                        <div class="col ec-metrica-pago-col ec-metrica-pago-col--ref">
                            <div class="d-flex align-items-center ec-metrica-pago-item">
                                <div class="avatar flex-shrink-0">
                                    <div class="avatar-initial bg-label-facebook rounded w-px-40 h-px-40">
                                        <i class="fa fa-id-card"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-grow-1 ec-metrica-pago-text">
                                    <h5 class="mb-0 ec-fit-dynamic ec-fit-ref"><?= htmlspecialchars($dataEstadoCuenta["referenciaSTP"] ?? '') ?></h5>
                                    <span>Referencia STP</span>
                                </div>
                            </div>
                        </div>
                    </div>
                        <?php if (!empty($resultadoCruce['saldo_favor']) && $resultadoCruce['saldo_favor'] > 0): ?>
                        <div class="row g-2 mt-2">
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-3 ec-metrica-pago-item">
                                <div class="avatar flex-shrink-0">
                                    <div class="avatar-initial bg-label-success rounded w-px-40 h-px-40">
                                        <i class="fa fa-piggy-bank"></i>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-grow-1">
                                    <h5 class="mb-0"><?= format_currency($resultadoCruce['saldo_favor']) ?></h5>
                                    <span>Saldo a favor gastos</span>
                                </div>
                            </div>
                        </div>
                        </div>
                        <?php endif; ?>
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
                    <?php
                    $dataEstadoCuenta = $dataEstadoCuenta ?? [];
                    $statusCreditoRaw = trim((string)($dataEstadoCuenta['statusCredito'] ?? ''));
                    $creditoSaldado = (mb_strtoupper($statusCreditoRaw) === 'SALDADO');
                    // Solo contar cuotas regulares (no anticipo) para detectar la última
                    $totalCuotas = 0;
                    foreach ($tabla as $_tf) { if (($_tf['tipo'] ?? '') !== 'anticipo') $totalCuotas++; }
                    $idxCuota = 0;
                    ?>
                    <?php foreach ($tabla as $fila): ?>
                        <?php if (($fila['tipo'] ?? '') === 'anticipo'): ?>
                        <tr class="fila-anticipo-capital">
                            <td>
                                <span class="badge badge-anticipo px-2 py-1">
                                    <i class="fa fa-arrow-circle-down me-1"></i>Anticipo
                                </span>
                            </td>
                            <td class="fecha-cuota">
                                <span class="fa fa-calendar"></span>
                                <?= htmlspecialchars(format_date($fila['fecha'])) ?>
                                <br><u><?= format_currency($fila['monto_cargo']) ?></u>
                            </td>
                            <td>
                                <ul class="ps-3 mb-0">
                                <?php if (!empty($fila['aplicados'])): ?>
                                    <?php foreach ($fila['aplicados'] as $_ap): ?>
                                    <li class="anticipo-linea">
                                        <span class="etiqueta-anticipo-pago">Anticipo Capital: <?= format_currency($_ap['montoPago'] ?? 0) ?></span> -
                                        <span class="etiqueta-anticipo-aplicado">Aplicado a anticipo capital: <?= format_currency($_ap['aplicado'] ?? 0) ?></span> -
                                        <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($_ap['fechaRegistro'] ?? '')) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <li class="text-muted"><em>Sin pago registrado</em></li>
                                <?php endif; ?>
                                </ul>
                            </td>
                            <td><?= format_currency($fila['total_pagado']) ?></td>
                            <td><span class="badge badge-anticipo px-2 py-1">Anticipo capital</span></td>
                        </tr>
                        <?php continue; ?>
                        <?php endif; ?>
                        <?php
                        $idxCuota++;
                        $esUltimaCuota = ($idxCuota === $totalCuotas);
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
                            if (!empty($a['no_cuenta_para_total_cuota'])) {
                                continue;
                            }
                            if (!empty($a['fechaRegistro'])) {
                                $ts = strtotime($a['fechaRegistro']);
                                if ($ts && (!$lastPagoDate || $ts > strtotime($lastPagoDate))) {
                                    $lastPagoDate = $a['fechaRegistro'];
                                }
                            }
                        }

                        // Detectar si esta cuota tiene contracargo (afecta cálculo de mora)
                        $tieneContracargo = false;
                        $lastPagoDateReal = null;
                        foreach ($aplicados as $a) {
                            if (isset($a['tipo']) && $a['tipo'] === 'contracargo') {
                                $tieneContracargo = true;
                                continue;
                            }
                            if (!empty($a['cc_invalido'])) continue;
                            if (!empty($a['no_cuenta_para_total_cuota'])) continue;
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

                        // Solo movimientos extemporáneos (API) visibles, sin aplicación a capital de la cuota
                        $filaRegistroApiExt = false;
                        if ($total_pagado <= 0.009 && $pendiente > 0.009 && !empty($aplicados)) {
                            $tieneLineaExtApi = false;
                            $tieneAplicacionACapitalCuota = false;
                            foreach ($aplicados as $a) {
                                $t = (string) ($a['tipo'] ?? '');
                                if ($t === 'extemporaneo_deposito' || $t === 'extemporaneos_resumen') {
                                    $tieneLineaExtApi = true;
                                }
                                if ($t === 'contracargo') {
                                    continue;
                                }
                                if (!empty($a['cc_invalido'])) {
                                    continue;
                                }
                                if (!empty($a['no_cuenta_para_total_cuota'])) {
                                    continue;
                                }
                                if ((float) ($a['aplicado'] ?? 0) > 0.009) {
                                    $tieneAplicacionACapitalCuota = true;
                                    break;
                                }
                            }
                            $filaRegistroApiExt = $tieneLineaExtApi && !$tieneAplicacionACapitalCuota;
                        }

                        // Construir badge
                        if ($creditoSaldado && $esUltimaCuota) {
                            $badge = '<span class="badge bg-success px-3 py-2">Crédito saldado</span>';
                        } elseif ($pendiente <= 0) {
                            // pago completo
                            $badge = '<span class="badge bg-success px-3 py-2">Pago completo</span>';
                            if ($diasMora > 0) {
                                $badge = '<span class="badge bg-danger px-3 py-2">Pago completo<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                            }
                        } elseif ($filaRegistroApiExt) {
                            $badge = '<span class="badge bg-info text-dark px-3 py-2">Registro API: ext.</span>';
                        } elseif ($total_pagado > 0) {
                            $badge = '<span class="badge bg-warning px-3 py-2">Pago parcial<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                        } else {
                            $badge = '<span class="badge bg-secondary px-3 py-2">Sin pago<br>' . htmlspecialchars($diasMora) . ' día' . ($diasMora>1?'s':'') . ' de mora</span>';
                        }
                        $esSemanaActual = fechaCuotaEnSemanaActual($fecha ?? null);
                        ?>
                        <tr<?= $esSemanaActual ? ' class="fila-semana-actual"' : '' ?>>
                            <td class="align-top">
                                <div class="cuota-num-wrap">
                                    <span class="fw-medium"><?= htmlspecialchars((string)$cuota) ?></span><?php if ($esSemanaActual): ?><i class="fa fa-calendar-check icono-semana-cuota ms-1 align-middle" title="Semana actual" aria-label="Semana actual"></i><?php endif; ?>
                                    <?php if (!empty($fila['recalculada'])): ?>
                                    <div><span class="badge bg-info text-dark badge-recalculada" title="Plazo o interés recalculado tras anticipo a capital">Recalculada</span></div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="fecha-cuota"><span class="fa fa-calendar"></span> <?= htmlspecialchars(format_date($fecha)) ?> <br> <u><?= format_currency($monto_cargo) ?></u></td>

                            <td>
                                <ul class="ps-3 mb-0">
                                    <?php if (!empty($aplicados)): ?>
                                        <?php foreach ($aplicados as $pago): ?>
                                            <?php if (isset($pago['tipo']) && $pago['tipo'] === 'contracargo'): ?>
                                            <li>
                                                <?php
                                                $cdP = $pago['concepto_display'] ?? 'contracargo';
                                                if ($cdP === 'reembolso') {
                                                    $etiquetaCargo = 'Reembolso';
                                                } elseif ($cdP === 'nccc') {
                                                    $etiquetaCargo = 'Nota de cargo crédito';
                                                } else {
                                                    $etiquetaCargo = 'Contracargo';
                                                }
                                                ?>
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
                                            ?>
                                            <li class="text-muted small mb-0 linea-extemporaneo-api">
                                                <i class="fa fa-info-circle opacity-40 me-1 align-text-bottom" style="font-size: 0.72rem;" title="Solo extemporáneo según API; no cuenta a capital de la cuota."></i>
                                                <span class="text-secondary">Dep. ext.</span>:
                                                <?= format_currency($pago_monto) ?> -
                                                <span class="etiqueta-aplicado">Aplicado</span>: <?= format_currency(0.0) ?> -
                                                <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($pago_fecha)) ?></span>
                                            </li>
                                            <?php elseif (isset($pago['tipo']) && $pago['tipo'] === 'nota_credito'): ?>
                                            <?php
                                            $montoNc = safe($pago['montoPago'], 0.0);
                                            $aplicadoNc = safe($pago['aplicado'], 0.0);
                                            $fechaNc = safe($pago['fechaRegistro'], null);
                                            $subtipoNc = (string) ($pago['subtipo'] ?? '');
                                            if ($subtipoNc === 'nota_credito_capital') {
                                                $etiquetaNc = 'Nota crédito (Capital)';
                                            } elseif ($subtipoNc === 'nota_credito_interes') {
                                                $etiquetaNc = 'Nota crédito (Interés)';
                                            } else {
                                                $etiquetaNc = 'Nota crédito';
                                            }
                                            ?>
                                            <li class="text-primary">
                                                <span class="fw-semibold"><?= htmlspecialchars($etiquetaNc) ?>:</span> <?= format_currency($montoNc) ?> -
                                                <span class="etiqueta-aplicado">Aplicado</span>: <?= format_currency($aplicadoNc) ?> -
                                                <span class="text-muted fecha-pago"><?= htmlspecialchars(format_date($fechaNc)) ?></span>
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
                                                <?php endif; ?>
                                            </li>
                                            <?php
                                            // Notas de cargo residuales (no procesadas como contracargo)
                                            $hayNotasCargos = $hayNotasCargos ?? false;
                                            $notasCargoPorFecha = $notasCargoPorFecha ?? [];
                                            $esReembolsoPorFecha = $esReembolsoPorFecha ?? [];
                                            $fechaNorm = $pago_fecha ? date('Y-m-d', strtotime($pago_fecha)) : '';
                                            $totalNotaCargo = ($hayNotasCargos && $fechaNorm !== '' && isset($notasCargoPorFecha[$fechaNorm])) ? (float)$notasCargoPorFecha[$fechaNorm] : 0;
                                            $tipoDisplayCargoPorFecha = $tipoDisplayCargoPorFecha ?? [];
                                            $tdRes = $tipoDisplayCargoPorFecha[$fechaNorm] ?? 'contracargo';
                                            if (!empty($esReembolsoPorFecha[$fechaNorm])) {
                                                $etiquetaCargoResidual = 'Reembolso';
                                            } elseif ($tdRes === 'nccc') {
                                                $etiquetaCargoResidual = 'Nota de cargo crédito';
                                            } else {
                                                $etiquetaCargoResidual = 'Contracargo';
                                            }
                                            if ($totalNotaCargo > 0 && empty($pago['no_cuenta_para_total_cuota'])): ?>
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
                                    <?php else: ?>
                                        <!-- Sin pagos -->
                                    <?php endif; ?>
                                    <?php if ($creditoSaldado && $esUltimaCuota && !empty($aplicados)): ?>
                                        <?php $saldoFavorLinea = round((float)($saldoFavorEstadoCuenta ?? 0), 2); ?>
                                        <p class="mb-0 mt-2 text-success small fw-semibold">Crédito saldado — Pago total</p>
                                        <hr class="my-1" style="border-top:1px solid rgba(108,117,125,.45); opacity:1;">
                                        <p class="mb-0 text-primary small fw-semibold">Saldo a favor: <?= format_currency($saldoFavorLinea) ?></p>
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

    <!-- Modal RFC (contenido: getComplementosEstadoCuenta) -->
    <div class="modal fade" id="modalRFC" tabindex="-1" aria-labelledby="modalRFCLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRFCLabel">
                        <i class="fa fa-id-card text-primary me-2"></i>
                        Referencias del Cliente
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <div id="ec-modal-refs-body" class="row g-4">
                        <div class="col-12 text-center text-muted py-4">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            Cargando referencias…
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>

            </div>
        </div>
    </div>


</div>

<?php if (!empty($tienePermisoRastreoNeverPaid)): ?>
<?php require __DIR__ . '/partials/sabueso_rastreo_estado_cuenta_bundle.php'; ?>
<script>
window.abrirRastreoNeverPaidModalEc = function (idCredito) {
    var id = (idCredito != null && String(idCredito).trim() !== '') ? String(idCredito).trim() : '';
    if (!id) return;
    function intentar() {
        var input = document.getElementById('inputConsultaIdCredito');
        if (input) input.value = id;
        if (typeof window.ejecutarConsultaCreditoIr === 'function') {
            window.ejecutarConsultaCreditoIr();
            return true;
        }
        return false;
    }
    if (intentar()) return;
    var n = 0;
    var t = setInterval(function () {
        n++;
        if (intentar() || n >= 80) clearInterval(t);
    }, 50);
};
</script>
<?php endif; ?>
<?php
$__ecJsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_UNESCAPED_UNICODE;
if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
    $__ecJsonFlags |= JSON_INVALID_UTF8_SUBSTITUTE;
}
$__gastosJson = json_encode($gastosCobranzaPreload ?? [], $__ecJsonFlags);
$__histJson = json_encode($historialGastosPreload ?? [], $__ecJsonFlags);
if ($__gastosJson === false) {
    $__gastosJson = '[]';
}
if ($__histJson === false) {
    $__histJson = '[]';
}
$__dictamenContactoPreload = $dictamenContactoPreload ?? ['id_credito' => 0, 'opciones' => []];
$__dictamenContactoJson = json_encode($__dictamenContactoPreload, $__ecJsonFlags);
if ($__dictamenContactoJson === false) {
    $__dictamenContactoJson = '{"id_credito":0,"opciones":[]}';
}
?>
<script type="application/json" id="ec-gastos-cobranza-preload"><?= $__gastosJson ?></script>
<script type="application/json" id="ec-historial-gastos-preload"><?= $__histJson ?></script>
<script type="application/json" id="ec-dictamen-contacto-preload"><?= $__dictamenContactoJson ?></script>
<?php
$__ecCrucePayload = isset($ecCrucePayload) && is_array($ecCrucePayload) ? $ecCrucePayload : null;
$__ecCruceJson = false;
if ($__ecCrucePayload !== null && (int) ($__ecCrucePayload['idCredito'] ?? 0) > 0) {
    $__ecCruceJson = json_encode($__ecCrucePayload, $__ecJsonFlags);
    if ($__ecCruceJson === false) {
        $__ecCrucePayloadMin = [
            'idCredito'  => (int) $__ecCrucePayload['idCredito'],
            'fechaCorte' => (string) ($__ecCrucePayload['fechaCorte'] ?? date('Y-m-d')),
        ];
        $__ecCruceJson = json_encode($__ecCrucePayloadMin, $__ecJsonFlags);
    }
}
?>
<?php if ($__ecCruceJson !== false && $__ecCruceJson !== ''): ?>
<script type="application/json" id="ec-cruce-payload"><?= $__ecCruceJson ?></script>
<script>
(function () {
    var el = document.getElementById('ec-cruce-payload');
    if (!el) return;
    var raw = el.textContent;
    if (!raw) return;
    var payload;
    try {
        payload = JSON.parse(raw);
    } catch (e) {
        return;
    }
    if (!payload || !payload.idCredito) return;
    function enviar() {
        fetch('/EstadoCuenta/procesarCruceGastos', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            credentials: 'same-origin',
            keepalive: true
        }).catch(function () {});
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { setTimeout(enviar, 0); });
    } else {
        setTimeout(enviar, 0);
    }
})();
</script>
<?php endif; ?>
<?php
$__ecIdComplementos = (int) (($dataEstadoCuenta ?? [])['idCredito'] ?? 0);
?>
<?php if ($__ecIdComplementos > 0): ?>
<script>
(function () {
    var idCredito = <?= json_encode($__ecIdComplementos) ?>;
    function ecEsc(t) {
        if (t == null || t === '') return '';
        var d = document.createElement('div');
        d.textContent = String(t);
        return d.innerHTML;
    }
    function aplicarComplementos(resp) {
        if (!resp || !resp.success) {
            var msg = (resp && resp.mensaje) ? resp.mensaje : 'No se pudieron cargar los datos del cliente.';
            var refs = document.getElementById('ec-modal-refs-body');
            if (refs) refs.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">' + ecEsc(msg) + '</div></div>';
            var dir = document.getElementById('ec-modal-direcciones-body');
            if (dir) dir.innerHTML = '<div class="col-12"><div class="alert alert-warning mb-0">' + ecEsc(msg) + '</div></div>';
            var rfc = document.getElementById('ec-rfc-inline');
            if (rfc) rfc.textContent = '—';
            var bd = document.getElementById('badgeNotas');
            if (bd) {
                bd.textContent = '0';
                bd.classList.remove('bg-secondary');
                bd.classList.add('bg-danger');
            }
            return;
        }
        var refPayload = resp.referencias || {};
        var datosRef = (refPayload.datos && refPayload.datos[0]) ? refPayload.datos[0] : {};
        var rfcEl = document.getElementById('ec-rfc-inline');
        if (rfcEl) {
            var rfc = String(datosRef.rfc || '').trim();
            rfcEl.textContent = rfc ? rfc : '—';
        }
        var numNotas = '';
        var np = resp.notas;
        if (np && np.datos && np.datos[0] && np.datos[0].num != null) numNotas = String(np.datos[0].num);
        var badge = document.getElementById('badgeNotas');
        if (badge) {
            badge.textContent = numNotas !== '' ? numNotas : '0';
            badge.classList.remove('bg-secondary');
            badge.classList.add('bg-danger');
        }
        var list = [];
        for (var i = 1; i <= 3; i++) {
            var nk = 'nombre_completo_referencia' + i;
            var tk = 'telefono_referencia' + i;
            if (datosRef[nk] && String(datosRef[nk]).trim()) {
                list.push({
                    nombre: datosRef[nk],
                    telefono: datosRef[tk] || '—',
                    tipo: i === 1 ? 'Principal' : ('Referencia ' + i),
                    icono: i === 1 ? 'fa-user text-success' : (i === 2 ? 'fa-user-friends text-primary' : 'fa-user-tie text-warning')
                });
            }
        }
        var refsBody = document.getElementById('ec-modal-refs-body');
        if (refsBody) {
            if (list.length === 0) {
                refsBody.innerHTML = '<div class="col-12"><p class="text-muted text-center mb-0">Sin referencias registradas.</p></div>';
            } else {
                var colw = Math.max(1, Math.floor(12 / list.length));
                var html = '';
                list.forEach(function (r, index) {
                    html += '<div class="col-md-' + colw + ' min-w-0"><div class="reference-card">';
                    if (index === 0) html += '<span class="badge bg-success reference-badge">' + ecEsc(r.tipo) + '</span>';
                    html += '<div class="reference-header"><i class="fa ' + r.icono + '"></i> ' + ecEsc(r.tipo) + '</div>';
                    html += '<div class="reference-divider"></div>';
                    html += '<div class="info-line"><span>Nombre: </span><strong>' + ecEsc(r.nombre) + '</strong></div>';
                    html += '<div class="info-line"><span>Teléfono: </span><strong>' + ecEsc(r.telefono) + '</strong></div>';
                    html += '</div></div>';
                });
                refsBody.innerHTML = html;
            }
        }
        var datosDir = [];
        var dresp = resp.direcciones;
        if (dresp && Array.isArray(dresp.datos)) datosDir = dresp.datos;
        var dirBody = document.getElementById('ec-modal-direcciones-body');
        if (dirBody) {
            if (!datosDir.length) {
                dirBody.innerHTML = '<div class="col-12"><div class="alert alert-info text-center"><i class="fa fa-info-circle me-2"></i><strong>No se encontraron direcciones</strong><p class="mb-0 mt-2">No hay direcciones registradas para este cliente.</p></div></div>';
            } else {
                var dh = '';
                datosDir.forEach(function (direccion) {
                    if (!direccion || typeof direccion !== 'object') return;
                    var domicilio = direccion.Domicilio_Completo != null ? String(direccion.Domicilio_Completo) : 'No disponible';
                    var nom = direccion.Nombre_cliente != null ? String(direccion.Nombre_cliente) : '';
                    var idCl = direccion.Id_cliente != null ? String(direccion.Id_cliente) : '';
                    dh += '<div class="col-md-6"><div class="card h-100 shadow-sm border"><div class="card-body">';
                    dh += '<div class="d-flex align-items-center mb-2"><i class="fa fa-home text-success me-2"></i><h6 class="mb-0">Domicilio Particular</h6></div>';
                    if (nom) dh += '<div class="mb-2"><small class="text-muted d-block">Cliente:</small><strong>' + ecEsc(nom) + '</strong></div>';
                    dh += '<div class="mb-2"><small class="text-muted d-block">Dirección:</small><p class="mb-0">' + ecEsc(domicilio) + '</p></div>';
                    if (idCl) dh += '<div class="mb-2"><small class="text-muted">ID Cliente: ' + ecEsc(idCl) + '</small></div>';
                    dh += '<span class="badge bg-success">Principal</span></div></div></div>';
                });
                dirBody.innerHTML = dh || '<div class="col-12"><div class="alert alert-info text-center">No hay direcciones.</div></div>';
            }
        }
    }
    function cargarComplementos() {
        fetch('/EstadoCuenta/getComplementosEstadoCuenta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ idCredito: idCredito }),
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(aplicarComplementos)
            .catch(function () {
                aplicarComplementos({ success: false, mensaje: 'Error de conexión al cargar datos del cliente.' });
            });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { setTimeout(cargarComplementos, 0); });
    } else {
        setTimeout(cargarComplementos, 0);
    }
})();
</script>
<?php endif; ?>

<?php
$ecCondonarDept9 = isset($_SESSION['departamento']) && (int)$_SESSION['departamento'] === 9;
$ecCondonarHideStyle = $ecCondonarDept9 ? 'style="display:none;"' : '';
?>

<div class="modal fade" id="modalCondonar" tabindex="-1" aria-hidden="true"
     data-bs-backdrop="false" data-bs-keyboard="true"
     data-condonar-dept9="<?= $ecCondonarDept9 ? '1' : '0' ?>">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title">
                    <i class="fa fa-hand-holding-usd text-success me-2"></i>
                    Resumen Gastos Cobranza
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">


                <!-- PESTAÑAS -->
                <ul class="nav nav-tabs mb-3" id="tabsCondonar" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active"
                                id="tab-gastos-btn"
                                data-bs-toggle="tab"
                                data-bs-target="#tab-gastos"
                                type="button" role="tab">
                            <i class="fa fa-hand-holding-usd me-1"></i>
                            Gastos Pendientes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link"
        id="tab-historial-btn"
        data-bs-toggle="tab"
        data-bs-target="#tab-historial"
        type="button" role="tab">
    <i class="fa fa-history me-1"></i>
    Historial
</button>
                    </li>
                </ul>

                <div class="tab-content" id="tabsCondonarContent">

                    <!-- TAB 1: GASTOS PENDIENTES -->
                    <div class="tab-pane fade show active" id="tab-gastos" role="tabpanel">

                        <!-- Resumen (cajas ocultas si el valor es 0; ver syncVisibilidadResumenCondonacion) -->
                        <div class="row mb-3" id="filaResumenCondonacion">
                            <div class="col-md-4<?= $ecCondonarDept9 ? '' : ' d-none' ?>" id="boxSeleccionados" <?= $ecCondonarDept9 ? $ecCondonarHideStyle : '' ?>>
                                <div class="alert alert-success py-2 mb-2">
                                    <strong>Seleccionados:</strong>
                                    <span id="countCondonados">0</span>
                                </div>
                            </div>
                            <div class="col-md-4<?= $ecCondonarDept9 ? '' : ' d-none' ?>" id="boxMonto" <?= $ecCondonarDept9 ? $ecCondonarHideStyle : '' ?>>
                                <div class="alert alert-warning py-2 mb-2">
                                    <strong>Monto a condonar:</strong>
                                    $<span id="montoCondonar">0.00</span>
                                </div>
                            </div>
                            <div class="col-md-4 d-none" id="boxTotalSinCondonarCol">
                                <div class="alert alert-danger py-2 mb-2 d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold text-dark">
                                        Total gastos cobranza sin condonar:
                                    </span>
                                    <span class="fw-bold text-danger">
                                        $<span id="montoTotalSinCondonar">0.00</span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th style="width:40px;"></th>
                                    <th>Semana</th>
                                    <th>Periodo</th>
                                    <th>Parcialidad</th>
                                    <th>CUOTA SEMANAL</th>
                                    <th>Monto</th>
                                    <th></th>
                                </tr>
                                </thead>
                                <tbody id="tablaGastos">
                                <!-- Se llena dinámicamente -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Motivo (solo visible si hay monto a condonar > 0; dept. 9 sigue oculto por reglas de negocio) -->
                        <div class="mt-3<?= $ecCondonarDept9 ? '' : ' d-none' ?>" id="wrapMotivoCondonacionTotal" <?= $ecCondonarDept9 ? $ecCondonarHideStyle : '' ?>>
                            <label class="form-label fw-semibold" for="idMotivoCondonacionCobranza">
                                Motivo de la condonación (convenio de pago) <span class="text-danger">*</span>
                            </label>
                            <select id="idMotivoCondonacionCobranza" class="form-select mb-3" autocomplete="off">
                                <?php
                                $ecCatMotivos = isset($catalogoMotivosCondonacion) && is_array($catalogoMotivosCondonacion) ? $catalogoMotivosCondonacion : [];
                                echo '<option value="" selected>Seleccione una opción</option>' . "\n";
                                foreach ($ecCatMotivos as $cm) {
                                    $oid = (int) ($cm['id'] ?? 0);
                                    if ($oid < 1) {
                                        continue;
                                    }
                                    $otxt = htmlspecialchars((string) ($cm['motivo'] ?? ''), ENT_QUOTES, 'UTF-8');
                                    echo '<option value="' . (int) $oid . '">' . $otxt . "</option>\n";
                                }
                                if (count($ecCatMotivos) === 0) {
                                    echo '<option value="1">Campaña Call Center</option>' . "\n";
                                }
                                ?>
                            </select>
                            <label class="form-label fw-semibold" for="descripcionCondonacion">
                                Detalle del motivo condonación <span class="text-danger">*</span>
                            </label>
                            <textarea id="descripcionCondonacion" class="form-control" rows="3"
                                placeholder="Describe el motivo de la condonación (mínimo 25 caracteres)..."></textarea>
                        </div>

                    </div>

                    <!-- TAB 2: HISTORIAL -->
                    <div class="tab-pane fade" id="tab-historial" role="tabpanel">
                        <div id="contenedorHistorial">
                            <div class="text-center text-muted py-4">
                                <i class="fa fa-history fa-2x mb-2 d-block"></i>
                                <p>Haz clic en la pestaña para cargar el historial.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer" <?= $ecCondonarHideStyle ?>>
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button class="btn btn-success" id="btnCondonarTotal" disabled
                    onclick="confirmarCondonacion(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">
                    <i class="fa fa-check me-1"></i>Condonar
                </button>
            </div>

        </div>
    </div>
</div>


<!-- Modal Condonación parcial (Editar) -->
<div class="modal fade" id="modalCondonarParcial" tabindex="-1" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title">
                    <i class="fa fa-edit text-warning me-2"></i>
                    Condonación parcial
                </h5>
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
                    <textarea id="condonarParcial_motivo" class="form-control" rows="4" minlength="25"
                        placeholder="Describa el motivo de la condonación parcial (mínimo 25 caracteres)..."></textarea>
                    <small class="text-muted"><span id="condonarParcial_motivoCount">0</span> caracteres · Mínimo 25</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-warning" id="btnCondonarParcialAceptar">
                    <i class="fa fa-check me-1"></i>Aceptar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDictamen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg">

            <!-- HEADER -->
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title">
                    <i class="fa fa-headset text-primary me-2"></i>
                    Dictaminar llamada
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <input type="hidden"
                       id="idCredito_dictamen"
                       name="idCredito_dictamen"
                       value="<?= htmlspecialchars($dataEstadoCuenta['idCredito'] ?? '') ?>">

                <div class="row g-3 align-items-end">
                    <div class="col-md-4" id="dictamen_llamada_wrap_selector">
                        <label class="form-label fw-semibold" for="dictamen_llamada_a">Llamada a</label>
                        <select id="dictamen_llamada_a" class="form-select">
                            <option value="">Seleccione número o contacto</option>
                        </select>
                    </div>
                    <div class="col-md-4" id="dictamen_llamada_wrap_numero">
                        <label class="form-label fw-semibold" for="dictamen_llamada_numero">Número</label>
                        <input type="text" id="dictamen_llamada_numero" class="form-control bg-light" readonly autocomplete="off">
                    </div>
                    <div class="col-md-4" id="dictamen_llamada_wrap_persona">
                        <label class="form-label fw-semibold" for="dictamen_llamada_persona">Persona contactada</label>
                        <input type="text" id="dictamen_llamada_persona" class="form-control bg-light" readonly autocomplete="off">
                    </div>
                </div>
                <div id="dictamen_llamada_otros_captura" class="row g-3 mt-1 d-none">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="dictamen_otro_telefono">Nuevo teléfono</label>
                        <input type="text" id="dictamen_otro_telefono" class="form-control" placeholder="10 dígitos o formato habitual" autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="dictamen_otro_parentesco">Parentesco o relación</label>
                        <input type="text" id="dictamen_otro_parentesco" class="form-control" placeholder="Ej. Tío, vecino, referencia">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold" for="dictamen_otro_nombre">Nombre completo del contacto</label>
                        <input type="text" id="dictamen_otro_nombre" class="form-control" placeholder="Nombre completo del contacto">
                    </div>
                </div>

                <hr class="my-4 text-secondary">

                <!-- FILA 1 -->
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

                <!-- FILA 2 -->
                <div class="row g-3 mt-1">
                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tipo motivo no pago</label>
                            <select id="tipo_motivo_no_pago" class="form-select">
                                <option value="">No aplica</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Motivo no pago</label>
                            <select id="motivo_no_pago" class="form-select" disabled>
                                <option value="">Seleccione motivo</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Plataforma</label>
                            <select id="plataforma" class="form-select"></select>
                        </div>
                    </div>



                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fuente ingresos</label>
                        <input type="text" id="fuente_ingresos" class="form-control"
                               placeholder="Ej. Sueldo, negocio propio">
                    </div>
                </div>



                <!-- COMENTARIOS -->
                <div class="mt-3">
                    <label class="form-label fw-semibold">Comentarios</label>
                    <textarea id="comentarios" rows="3"
                              class="form-control"
                              placeholder="Detalle de la gestión..."></textarea>
                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-primary" onclick="guardarDictamen()">
                    <i class="fa fa-save me-1"></i>Guardar dictamen
                </button>
            </div>

        </div>
    </div>
</div>

<?php if (!empty($tienePermisoAclaracionesGc)): ?>
<?php require __DIR__ . '/partials/estado_cuenta_modal_aclaraciones_gc.php'; ?>
<?php endif; ?>

<div class="modal fade" id="modalNotas" tabindex="-1" aria-labelledby="modalNotasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header bg-warning bg-opacity-10">
                <h5 class="modal-title" id="modalNotasLabel">
                    <i class="fa fa-sticky-note text-warning me-2"></i>
                    Notas del Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">



                <!-- Nueva nota -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Agregar nota</label>
                    <textarea id="notaTexto"
                              class="form-control"
                              rows="3"
                              placeholder="Escribe aquí cualquier nota, acuerdo, promesa, comentario del cliente..."></textarea>


                </div>

                <div class="mb-3">
                <input
                        type="hidden"
                        id="idCredito_note"
                        name="idCredito_note"
                        value="<?= htmlspecialchars($dataEstadoCuenta['idCredito'] ?? '') ?>"
                </div>



                <div class="text-end mb-4">
                    <button class="btn btn-warning" onclick="agregarNota()">
                        <i class="fa fa-plus me-1"></i>Agregar nota
                    </button>
                </div>

                <hr>

                <!-- Listado de notas -->
                <div id="contenedorNotas" class="row g-3">

                    <!-- Nota ejemplo -->
                    <div class="col-md-6">
                        <div class="nota-card">
                            <div class="nota-fecha">
                                <i class="fa fa-clock"></i> 13/01/2026 10:32
                            </div>
                            <p class="nota-texto">
                                Cliente comenta que pagará el viernes por la tarde.
                            </p>
                            <div class="nota-autor">
                                <i class="fa fa-user"></i> Sistema
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalCallCenter" tabindex="-1" aria-labelledby="modalCallCenterLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalCallCenterLabel">
                    <i class="fa fa-headset me-2"></i>
                    Call Center
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body text-center py-4">
                <i class="fa fa-tools fa-3x text-warning mb-3"></i>
                <h6 class="mb-2">Funcionalidad en mantenimiento</h6>
                <p class="text-muted mb-0">
                    Se encuentra temporalmente en mantenimiento.
                    <br>
                    Por favor, intenta más tarde.
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    function ecParsePreloadJsonScript(id) {
        var el = document.getElementById(id);
        if (!el) return [];
        try {
            return JSON.parse(el.textContent);
        } catch (e) {
            return [];
        }
    }
    const GASTOS_COBRANZA_PRELOAD = ecParsePreloadJsonScript('ec-gastos-cobranza-preload');
    const HISTORIAL_GASTOS_PRELOAD = ecParsePreloadJsonScript('ec-historial-gastos-preload');

    function ecParsePreloadDictamenContacto() {
        var el = document.getElementById('ec-dictamen-contacto-preload');
        if (!el) {
            return { id_credito: 0, opciones: [] };
        }
        try {
            var o = JSON.parse(el.textContent);
            if (o && typeof o === 'object' && Array.isArray(o.opciones)) {
                return o;
            }
        } catch (e) { /* vacío */ }
        return { id_credito: 0, opciones: [] };
    }

    var EC_DICTAMEN_CONTACTO_PRELOAD = ecParsePreloadDictamenContacto();

    const EC_GESTION_EXTERNA_BLOQUEA_GASTOS = <?= json_encode($esGestionExternaMx) ?>;
    const EC_MSJ_GESTION_EXTERNA_GASTOS = <?= json_encode('Esta opción no está disponible. El crédito está siendo gestionado de forma externa.') ?>;

    function actualizarContadorNotas() {

        const badge = document.getElementById('badgeNotas');
        if (!badge) return;

        const cards = document.querySelectorAll('#contenedorNotas .nota-card');

        // Solo recalcular si el usuario YA interactuó
        if (!document.getElementById('modalNotas')?.classList.contains('show')) {
            return;
        }

        const total = cards.length;

        badge.textContent = total;
        badge.style.display = total > 0 ? 'inline-block' : 'none';
    }


    function agregarNota() {

        const notaInput = document.getElementById('notaTexto');
        const nota = notaInput.value.trim();
        const id_credito = document.getElementById('idCredito_note')?.value;


        if (!nota) {
            Swal.fire("Atención", "Escribe una nota antes de guardar", "warning");
            return;
        }

        fetch('/EstadoCuenta/AddNote', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                nota: nota,
                id_credito: id_credito // 🔴 DEBE EXISTIR
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {

                if (!data.success) {
                    Swal.fire("Error", data.mensaje, "error");
                    return;
                }

                // 👉 Insertar nota visualmente
                const contenedor = document.getElementById('contenedorNotas');
                const fecha = new Date().toLocaleString('es-MX');
                const usuarioNota = data.data?.usuario ?? 'Operador';

                const col = document.createElement('div');
                col.className = 'col-md-6';

                col.innerHTML = `
            <div class="nota-card animate__animated animate__fadeInUp">
                <div class="nota-fecha">
                    <i class="fa fa-clock"></i> ${fecha}
                </div>
                <p class="nota-texto">${nota}</p>
                <div class="nota-autor">
                    <i class="fa fa-user"></i> ${usuarioNota ?? 'Operador'}
                </div>
            </div>
        `;

                contenedor.prepend(col);

                // Limpiar input
                notaInput.value = '';

                // Actualizar contador si existe
                if (typeof actualizarContadorNotas === 'function') {
                    actualizarContadorNotas();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Nota guardada',
                    timer: 1200,
                    showConfirmButton: false
                });

            })
            .catch(error => {
                console.error(error);
                Swal.fire("Error", "Error de conexión con el servidor", "error");
            });
    }



    function actualizarResumenCondonacion() {
        let total = 0;
        let count = 0;

        document.querySelectorAll('.chk-condona:checked').forEach(chk => {
            total += parseFloat(chk.dataset.monto);
            count++;
        });

        document.getElementById('countCondonados').textContent = count;
        document.getElementById('montoCondonar').textContent = total.toFixed(2);
        if (typeof syncVisibilidadResumenCondonacion === 'function') {
            syncVisibilidadResumenCondonacion();
        }
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

            if (e.target.checked) {
                const rect = e.target.getBoundingClientRect();
                lanzarBillete(rect.left, rect.top);
            }
        }
    });


    function consultaNotas(idCredito) {

        if (!idCredito) {
            Swal.fire("Error", "Id de crédito inválido", "error");
            return;
        }

        const contenedor = document.getElementById('contenedorNotas');
        contenedor.innerHTML = `
        <div class="col-12 text-center text-muted">
            Cargando notas...
        </div>
    `;

        fetch('/EstadoCuenta/getNotasCredito', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idCredito: idCredito
            })
        })
            .then(res => res.json())
            .then(resp => {

                if (!resp.success) {
                    Swal.fire("Error", resp.mensaje, "error");
                    contenedor.innerHTML = '';
                    return;
                }

                const notas = resp.datos ?? resp.data ?? [];

                contenedor.innerHTML = '';

                if (!Array.isArray(notas) || notas.length === 0) {
                    contenedor.innerHTML = `
                <div class="col-12 text-center text-muted">
                    Sin notas registradas
                </div>
            `;
                    return;
                }

                notas.forEach(n => {

                    contenedor.innerHTML += `
                <div class="col-md-6">
                    <div class="nota-card animate__animated animate__fadeInUp">
                        <div class="nota-fecha">
                            <i class="fa fa-clock"></i> ${n.created_at}
                        </div>
                        <p class="nota-texto">
                            ${n.nota}
                        </p>
                        <div class="nota-autor">
                            <i class="fa fa-user"></i> ${n.usuario ?? 'Sistema'}
                        </div>
                    </div>
                </div>
            `;
                });

            })
            .catch(err => {
                console.error("ERROR consultaNotas:", err);
                Swal.fire("Error", "Error de conexión con el servidor", "error");
                contenedor.innerHTML = '';
            });

        // 👉 Abrir modal SIEMPRE (aunque esté cargando)
        const modal = new bootstrap.Modal(
            document.getElementById('modalNotas')
        );
        modal.show();
    }



    const modalDictamen = document.getElementById('modalDictamen');

    const tipoContactoSelect      = document.getElementById('tipo_contacto');
    const resultadoContactoSelect = document.getElementById('resultado_contacto');
    const dictamenSelect          = document.getElementById('dictamen');
    const plataformaSelect        = document.getElementById('plataforma');

    const tipoMotivoSelect   = document.getElementById('tipo_motivo_no_pago');
    const motivoNoPagoSelect = document.getElementById('motivo_no_pago');

    const dictamenFormOk = tipoContactoSelect && resultadoContactoSelect && dictamenSelect
        && plataformaSelect && tipoMotivoSelect && motivoNoPagoSelect;

    if (modalDictamen) {
        modalDictamen.addEventListener('shown.bs.modal', function (event) {
            const button = event.relatedTarget;
            const idCredito = button ? button.getAttribute('data-idcredito') : null;
            const hidCred = document.getElementById('idCredito_dictamen');
            if (hidCred && idCredito) {
                hidCred.value = idCredito;
            }
            cargarOpcionesContactoDictamenLlamada();
            if (dictamenFormOk) {
                initDictamenModal();
            }
        });
    }

    function cargarTiposContacto() {
        fetch('/EstadoCuenta/getTiposContacto')
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) {
                    console.error(resp.mensaje);
                    return;
                }

                tipoContactoSelect.innerHTML =
                    '<option value="">Seleccione tipo de contacto</option>';

                resp.datos.forEach(item => {
                    tipoContactoSelect.innerHTML +=
                        `<option value="${item.id}">${item.nombre}</option>`;
                });
            })
            .catch(err => console.error(err));
    }

    function cargarResultadosContacto(tipoContactoId) {

        resultadoContactoSelect.innerHTML =
            '<option value="">Cargando...</option>';
        resultadoContactoSelect.disabled = true;

        dictamenSelect.innerHTML =
            '<option value="">Seleccione dictamen</option>';
        dictamenSelect.disabled = true;

        fetch('/EstadoCuenta/getResultadosContacto', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo_contacto_id: tipoContactoId })
        })
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) {
                    console.error(resp.mensaje);
                    return;
                }

                resultadoContactoSelect.innerHTML =
                    '<option value="">Seleccione resultado</option>';

                resp.datos.forEach(item => {
                    resultadoContactoSelect.innerHTML +=
                        `<option value="${item.id}">${item.nombre}</option>`;
                });

                resultadoContactoSelect.disabled = false;
            })
            .catch(err => console.error(err));
    }


    function cargarDictamenes(resultadoContactoId) {

        dictamenSelect.innerHTML =
            '<option value="">Cargando...</option>';
        dictamenSelect.disabled = true;

        fetch('/EstadoCuenta/getDictamenes', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ resultado_contacto_id: resultadoContactoId })
        })
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) {
                    console.error(resp.mensaje);
                    return;
                }

                dictamenSelect.innerHTML =
                    '<option value="">Seleccione dictamen</option>';

                resp.datos.forEach(item => {
                    dictamenSelect.innerHTML +=
                        `<option value="${item.id}">${item.nombre}</option>`;
                });

                dictamenSelect.disabled = false;
            })
            .catch(err => console.error(err));
    }

    if (dictamenFormOk) {
    tipoContactoSelect.addEventListener('change', function () {
        if (this.value) {
            cargarResultadosContacto(this.value);
        }
    });

    resultadoContactoSelect.addEventListener('change', function () {
        if (this.value) {
            cargarDictamenes(this.value);
        }
    });
    }

    function cargarMotivosNoPago() {
        fetch('/EstadoCuenta/getMotivosNoPago')
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) {
                    console.error(resp.mensaje);
                    return;
                }

                motivoNoPagoSelect.innerHTML =
                    '<option value="">No aplica</option>';

                resp.datos.forEach(item => {
                    motivoNoPagoSelect.innerHTML +=
                        `<option value="${item.id}">${item.descripcion}</option>`;
                });
            })
            .catch(err => console.error(err));
    }


    function cargarPlataformas() {
        fetch('/EstadoCuenta/getPlataformas')
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) {
                    console.error(resp.mensaje);
                    return;
                }

                plataformaSelect.innerHTML =
                    '<option value="">Seleccione plataforma</option>';

                resp.datos.forEach(item => {
                    plataformaSelect.innerHTML +=
                        `<option value="${item.id}">${item.nombre}</option>`;
                });
            })
            .catch(err => console.error(err));
    }


    window._dictamenLlamadaOpcionesMap = window._dictamenLlamadaOpcionesMap || {};

    function actualizarVistaDictamenLlamadaContacto() {
        const sel = document.getElementById('dictamen_llamada_a');
        const cap = document.getElementById('dictamen_llamada_otros_captura');
        const num = document.getElementById('dictamen_llamada_numero');
        const nom = document.getElementById('dictamen_llamada_persona');
        const wrapSel = document.getElementById('dictamen_llamada_wrap_selector');
        const wrapNum = document.getElementById('dictamen_llamada_wrap_numero');
        const wrapPer = document.getElementById('dictamen_llamada_wrap_persona');
        if (!sel || !cap || !num || !nom) return;
        const v = sel.value;
        const esOtros = v === 'otros';
        if (wrapNum) wrapNum.classList.toggle('d-none', esOtros);
        if (wrapPer) wrapPer.classList.toggle('d-none', esOtros);
        if (wrapSel) {
            wrapSel.classList.toggle('col-md-4', !esOtros);
            wrapSel.classList.toggle('col-12', esOtros);
        }
        if (esOtros) {
            cap.classList.remove('d-none');
            num.value = '';
            nom.value = '';
            return;
        }
        cap.classList.add('d-none');
        const o = window._dictamenLlamadaOpcionesMap[v];
        if (o) {
            num.value = o.telefono || '';
            nom.value = o.nombre || '';
        } else {
            num.value = '';
            nom.value = '';
        }
    }

    function aplicarOpcionesContactoDictamenDesdePreload(sel, idCredito) {
        const pre = EC_DICTAMEN_CONTACTO_PRELOAD;
        if (!pre || String(pre.id_credito) !== String(idCredito) || !Array.isArray(pre.opciones) || pre.opciones.length === 0) {
            return false;
        }
        window._dictamenLlamadaOpcionesMap = {};
        sel.innerHTML = '<option value="">Seleccione número o contacto</option>';
        pre.opciones.forEach(function (item) {
            const v = item.value;
            window._dictamenLlamadaOpcionesMap[v] = {
                telefono: item.telefono || '',
                nombre: item.nombre || '',
                parentesco: item.parentesco || ''
            };
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = item.label || v;
            sel.appendChild(opt);
        });
        sel.disabled = false;
        actualizarVistaDictamenLlamadaContacto();
        return true;
    }

    function refetchDictamenContactoPreload(idCredito) {
        if (!idCredito) return;
        fetch('/EstadoCuenta/getOpcionesContactoDictamenLlamada', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_credito: idCredito })
        })
            .then(function (res) { return res.json(); })
            .then(function (resp) {
                if (resp.success && Array.isArray(resp.datos)) {
                    EC_DICTAMEN_CONTACTO_PRELOAD.id_credito = parseInt(idCredito, 10) || idCredito;
                    EC_DICTAMEN_CONTACTO_PRELOAD.opciones = resp.datos;
                }
            })
            .catch(function () { /* ignorar */ });
    }

    function cargarOpcionesContactoDictamenLlamada() {
        const sel = document.getElementById('dictamen_llamada_a');
        if (!sel) return;
        const idCredito = document.getElementById('idCredito_dictamen')?.value;
        if (!idCredito) {
            sel.innerHTML = '<option value="">Seleccione número o contacto</option>';
            window._dictamenLlamadaOpcionesMap = {};
            actualizarVistaDictamenLlamadaContacto();
            return;
        }
        if (aplicarOpcionesContactoDictamenDesdePreload(sel, idCredito)) {
            return;
        }
        sel.innerHTML = '<option value="">Cargando contactos...</option>';
        sel.disabled = true;
        fetch('/EstadoCuenta/getOpcionesContactoDictamenLlamada', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_credito: idCredito })
        })
            .then(res => res.json())
            .then(resp => {
                sel.disabled = false;
                window._dictamenLlamadaOpcionesMap = {};
                sel.innerHTML = '<option value="">Seleccione número o contacto</option>';
                if (!resp.success || !Array.isArray(resp.datos)) {
                    console.error(resp.mensaje || 'Sin opciones de contacto');
                    actualizarVistaDictamenLlamadaContacto();
                    return;
                }
                resp.datos.forEach(item => {
                    const v = item.value;
                    window._dictamenLlamadaOpcionesMap[v] = {
                        telefono: item.telefono || '',
                        nombre: item.nombre || '',
                        parentesco: item.parentesco || ''
                    };
                    const opt = document.createElement('option');
                    opt.value = v;
                    opt.textContent = item.label || v;
                    sel.appendChild(opt);
                });
                EC_DICTAMEN_CONTACTO_PRELOAD.id_credito = parseInt(idCredito, 10) || idCredito;
                EC_DICTAMEN_CONTACTO_PRELOAD.opciones = resp.datos;
                actualizarVistaDictamenLlamadaContacto();
            })
            .catch(err => {
                console.error(err);
                sel.disabled = false;
                sel.innerHTML = '<option value="">Seleccione número o contacto</option>';
                window._dictamenLlamadaOpcionesMap = {};
            });
    }

    const dictamenLlamadaASel = document.getElementById('dictamen_llamada_a');
    if (dictamenLlamadaASel) {
        dictamenLlamadaASel.addEventListener('change', actualizarVistaDictamenLlamadaContacto);
    }

    function precargarCatalogosDictamenModalDesdeServidor() {
        fetch('/EstadoCuenta/getCatalogosDictamenModal')
            .then(function (res) { return res.json(); })
            .then(function (resp) {
                if (resp.success && resp.datos) {
                    window.EC_DICTAMEN_CATALOGOS = resp.datos;
                }
            })
            .catch(function () { /* silencioso */ });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', precargarCatalogosDictamenModalDesdeServidor);
    } else {
        precargarCatalogosDictamenModalDesdeServidor();
    }

    function aplicarCatalogosDictamenModalDesdeCache() {
        const d = window.EC_DICTAMEN_CATALOGOS;
        if (!d || !tipoContactoSelect || !plataformaSelect || !tipoMotivoSelect) {
            return false;
        }
        if (Array.isArray(d.tipos_contacto)) {
            tipoContactoSelect.innerHTML = '<option value="">Seleccione tipo de contacto</option>';
            d.tipos_contacto.forEach(function (item) {
                tipoContactoSelect.innerHTML += '<option value="' + item.id + '">' + item.nombre + '</option>';
            });
        }
        if (Array.isArray(d.plataformas)) {
            plataformaSelect.innerHTML = '<option value="">Seleccione plataforma</option>';
            d.plataformas.forEach(function (item) {
                plataformaSelect.innerHTML += '<option value="' + item.id + '">' + item.nombre + '</option>';
            });
        }
        if (Array.isArray(d.tipos_motivo_no_pago)) {
            tipoMotivoSelect.innerHTML = '<option value="">No aplica</option>';
            d.tipos_motivo_no_pago.forEach(function (item) {
                tipoMotivoSelect.innerHTML += '<option value="' + item.id + '">' + item.nombre + '</option>';
            });
        }
        return true;
    }

    function initDictamenModal() {
        if (!dictamenFormOk) return;
        if (window.EC_DICTAMEN_CATALOGOS && aplicarCatalogosDictamenModalDesdeCache()) {
            return;
        }
        fetch('/EstadoCuenta/getCatalogosDictamenModal')
            .then(function (res) { return res.json(); })
            .then(function (resp) {
                if (resp.success && resp.datos) {
                    window.EC_DICTAMEN_CATALOGOS = resp.datos;
                    aplicarCatalogosDictamenModalDesdeCache();
                } else {
                    cargarTiposContacto();
                    cargarPlataformas();
                    cargarTiposMotivoNoPago();
                }
            })
            .catch(function () {
                cargarTiposContacto();
                cargarPlataformas();
                cargarTiposMotivoNoPago();
            });
    }

    function cargarTiposMotivoNoPago() {
        fetch('/EstadoCuenta/getTiposMotivoNoPago')
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) return;

                tipoMotivoSelect.innerHTML =
                    '<option value="">No aplica</option>';

                resp.datos.forEach(item => {
                    tipoMotivoSelect.innerHTML +=
                        `<option value="${item.id}">${item.nombre}</option>`;
                });
            });
    }

    function cargarMotivosNoPagoPorTipo(tipoId) {

        motivoNoPagoSelect.innerHTML =
            '<option value="">Cargando...</option>';
        motivoNoPagoSelect.disabled = true;

        if (!tipoId) {
            motivoNoPagoSelect.innerHTML =
                '<option value="">Seleccione motivo</option>';
            return;
        }

        fetch('/EstadoCuenta/getMotivosNoPagoPorTipo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo_motivo_id: tipoId })
        })
            .then(res => res.json())
            .then(resp => {
                if (!resp.success) return;

                motivoNoPagoSelect.innerHTML =
                    '<option value="">Seleccione motivo</option>';

                resp.datos.forEach(item => {
                    motivoNoPagoSelect.innerHTML +=
                        `<option value="${item.id}">${item.descripcion}</option>`;
                });

                motivoNoPagoSelect.disabled = false;
            });
    }

    if (dictamenFormOk) {
    tipoMotivoSelect.addEventListener('change', function () {
        cargarMotivosNoPagoPorTipo(this.value);
    });
    }

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

        // 🔴 VALIDACIONES
        if (!id_credito) {
            Swal.fire("Error", "No se detectó el crédito", "error");
            return;
        }

        if (!tipoContacto) {
            Swal.fire("Atención", "Selecciona el tipo de contacto", "warning");
            return;
        }

        if (!resultadoContacto) {
            Swal.fire("Atención", "Selecciona el resultado del contacto", "warning");
            return;
        }

        if (!dictamen) {
            Swal.fire("Atención", "Selecciona el dictamen", "warning");
            return;
        }

        // 👉 Motivo no pago SOLO si aplica
        if (tipoMotivo && !motivoNoPago) {
            Swal.fire("Atención", "Selecciona el motivo de no pago", "warning");
            return;
        }

        if (!fuenteIngresos) {
            Swal.fire("Atención", "La fuente de ingresos son obligatorios", "warning");
            return;
        }

        if (!comentarios) {
            Swal.fire("Atención", "Los comentarios son obligatorios", "warning");
            return;
        }

        const selLlamada = document.getElementById('dictamen_llamada_a');
        if (!selLlamada || !selLlamada.value) {
            Swal.fire("Atención", "Seleccione a qué número o persona correspondió la llamada", "warning");
            return;
        }
        const origenLlamada = selLlamada.value;
        const mapLlamada = window._dictamenLlamadaOpcionesMap[origenLlamada] || {};
        let payloadLlamada = {
            llamada_origen: origenLlamada,
            llamada_telefono: (document.getElementById('dictamen_llamada_numero')?.value || '').trim(),
            llamada_nombre_persona: (document.getElementById('dictamen_llamada_persona')?.value || '').trim(),
            llamada_parentesco: (mapLlamada.parentesco || '').trim()
        };
        if (origenLlamada === 'otros') {
            const xt = (document.getElementById('dictamen_otro_telefono')?.value || '').trim();
            const xp = (document.getElementById('dictamen_otro_parentesco')?.value || '').trim();
            const xn = (document.getElementById('dictamen_otro_nombre')?.value || '').trim();
            if (!xt || !xp || !xn) {
                Swal.fire("Atención", "Complete teléfono, parentesco y nombre completo del contacto", "warning");
                return;
            }
            payloadLlamada.contacto_extra_telefono = xt;
            payloadLlamada.contacto_extra_parentesco = xp;
            payloadLlamada.contacto_extra_nombre = xn;
            payloadLlamada.llamada_telefono = '';
            payloadLlamada.llamada_nombre_persona = '';
            payloadLlamada.llamada_parentesco = '';
        }

        // 🔄 ENVÍO
        fetch('/EstadoCuenta/guardarDictamen', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_credito: id_credito,
                tipo_contacto_id: tipoContacto,
                resultado_contacto_id: resultadoContacto,
                dictamen_id: dictamen,
                tipo_motivo_no_pago_id: tipoMotivo || null,
                motivo_no_pago_id: motivoNoPago || null,
                plataforma_id: plataforma || null,
                fuente_ingresos: fuenteIngresos,
                comentarios: comentarios,
                ...payloadLlamada
            })
        })
            .then(res => {
                if (!res.ok) throw new Error('Error HTTP ' + res.status);
                return res.json();
            })
            .then(resp => {

                if (!resp.success) {
                    Swal.fire("Error", resp.mensaje, "error");
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Dictamen guardado',
                    timer: 1300,
                    showConfirmButton: false
                });

                const idCredRefetch = document.getElementById('idCredito_dictamen')?.value;
                if (idCredRefetch) {
                    refetchDictamenContactoPreload(idCredRefetch);
                }

                // 🔄 Limpiar formulario
                limpiarFormularioDictamen();

                // ❌ Cerrar modal
                const modal = bootstrap.Modal.getInstance(
                    document.getElementById('modalDictamen')
                );
                modal.hide();

            })
            .catch(err => {
                console.error(err);
                Swal.fire("Error", "Error de conexión con el servidor", "error");
            });
    }

    function limpiarFormularioDictamen() {

        document.getElementById('tipo_contacto').value = '';
        document.getElementById('resultado_contacto').innerHTML = '';
        document.getElementById('resultado_contacto').disabled = true;

        document.getElementById('dictamen').innerHTML = '';
        document.getElementById('dictamen').disabled = true;

        document.getElementById('tipo_motivo_no_pago').value = '';
        document.getElementById('motivo_no_pago').innerHTML =
            '<option value="">Seleccione motivo</option>';
        document.getElementById('motivo_no_pago').disabled = true;

        document.getElementById('plataforma').value = '';
        document.getElementById('fuente_ingresos').value = '';
        document.getElementById('comentarios').value = '';

        const sL = document.getElementById('dictamen_llamada_a');
        if (sL) {
            sL.value = '';
        }
        const nL = document.getElementById('dictamen_llamada_numero');
        const pL = document.getElementById('dictamen_llamada_persona');
        if (nL) nL.value = '';
        if (pL) pL.value = '';
        const capO = document.getElementById('dictamen_llamada_otros_captura');
        if (capO) capO.classList.add('d-none');
        const oT = document.getElementById('dictamen_otro_telefono');
        const oP = document.getElementById('dictamen_otro_parentesco');
        const oN = document.getElementById('dictamen_otro_nombre');
        if (oT) oT.value = '';
        if (oP) oP.value = '';
        if (oN) oN.value = '';
        if (typeof actualizarVistaDictamenLlamadaContacto === 'function') {
            actualizarVistaDictamenLlamadaContacto();
        }
    }



function consultaGastosCondonables(idCredito) {

    if (!idCredito) {
        Swal.fire("Error", "Id de crédito inválido", "error");
        return;
    }

    if (typeof EC_GESTION_EXTERNA_BLOQUEA_GASTOS !== 'undefined' && EC_GESTION_EXTERNA_BLOQUEA_GASTOS) {
        Swal.fire({
            icon: 'info',
            title: 'No disponible',
            text: typeof EC_MSJ_GESTION_EXTERNA_GASTOS !== 'undefined' ? EC_MSJ_GESTION_EXTERNA_GASTOS : 'Esta opción no está disponible. El crédito está siendo gestionado de forma externa.'
        });
        return;
    }

    idCreditoCondonar = idCredito;
    historialCargado  = false;

    const smOpen = document.getElementById('idMotivoCondonacionCobranza');
    if (smOpen) smOpen.value = '';
    const desOpen = document.getElementById('descripcionCondonacion');
    if (desOpen) desOpen.value = '';

    const tabla     = document.getElementById('tablaGastos');
    const countSpan = document.getElementById('countCondonados');
    const montoSpan = document.getElementById('montoCondonar');

    // Reset visual
    countSpan.textContent = 0;
    montoSpan.textContent = '0.00';
    document.getElementById('montoTotalSinCondonar').textContent = '0.00';
    if (typeof syncVisibilidadResumenCondonacion === 'function') {
        syncVisibilidadResumenCondonacion();
    }

    // 👉 Abrir modal SIEMPRE (igual que antes)
    const modal = new bootstrap.Modal(document.getElementById('modalCondonar'));
    document.getElementById('modalCondonarParcial').addEventListener('hidden.bs.modal', function () {
        document.body.classList.remove('modal-condonar-parcial-open');
    });
    modal.show();

    // ── Intentar con datos preloaded desde PHP ──
    if (typeof GASTOS_COBRANZA_PRELOAD !== 'undefined' && Array.isArray(GASTOS_COBRANZA_PRELOAD) && GASTOS_COBRANZA_PRELOAD.length > 0) {
        _pintarTablaGastos(GASTOS_COBRANZA_PRELOAD, idCredito);
        return;
    }

    // ── Fallback: fetch normal ──
    _fetchGastosCobranza(idCredito);
}




// Reemplaza la función _pintarTablaGastos completa con esta versión corregida
function _pintarTablaGastos(gastos, idCredito) {

    const round2 = v => Math.round((v + Number.EPSILON) * 100) / 100;
    const tabla = document.getElementById('tablaGastos');

    document.getElementById('modalCondonar').dataset.idCredito = idCredito;
    idCreditoCondonar = idCredito;

    tabla.innerHTML = '';

    if (!Array.isArray(gastos) || gastos.length === 0) {
        tabla.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    No hay Gastos Cobranza
                </td>
            </tr>`;
        document.getElementById('montoTotalSinCondonar').textContent = '0.00';
        recalcularCondonacion();
        return;
    }

    gastos.forEach((g, index) => {
        const idGasto          = g.id_gasto;
        const montoOrig        = parseFloat(g.monto_original ?? g.monto ?? 0);
        const montoPendiente   = parseFloat(g.monto ?? 0);
        const montoPagado      = parseFloat(g.monto_parcial_pagado ?? 0);
        const parcialMonto     = parseFloat(g.condonacion_parcial_monto ?? 0);
        const parcialMotivo    = (g.condonacion_parcial_motivo || '').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const tieneParcial     = parcialMonto > 0;
        const tienePagoParcial = g.tiene_pago_parcial === true || (parseInt(g.estatus_pago || 0) === 1 && montoPagado > 0);
        const montoFaltaCondonar = tieneParcial ? (montoOrig - parcialMonto) : montoPendiente;
        const anteriorTieneParcial = index === 0 || (parseFloat(gastos[index - 1].condonacion_parcial_monto ?? 0) > 0);
        const puedeParcial     = anteriorTieneParcial;

        const pct = montoOrig > 0 ? Math.min(100, Math.round((montoPagado / montoOrig) * 100)) : 0;

        let montoCelda;

        if (tienePagoParcial) {
            // Parcialidad con pago parcial: mostrar original tachado, pagado y lo que resta
            montoCelda = `
                <div class="d-flex flex-column gap-1" style="min-width:180px;">
                    <div class="d-flex justify-content-between" style="font-size:0.78rem;">
                        <span class="text-muted">Total:</span>
                        <strong><s>$${montoOrig.toFixed(2)}</s></strong>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar bg-success" style="width:${pct}%"></div>
                    </div>
                    <div class="d-flex justify-content-between" style="font-size:0.78rem;">
                        <span class="text-success fw-semibold">✔ Pagado: $${montoPagado.toFixed(2)}</span>
                        <span class="text-danger fw-semibold">Resta: $${montoPendiente.toFixed(2)}</span>
                    </div>
                </div>`;
        } else if (tieneParcial) {
            montoCelda = `
                <span class="text-decoration-line-through text-muted">$${montoOrig.toFixed(2)}</span>
                <br><strong>$${montoPendiente.toFixed(2)}</strong>`;
        } else {
            montoCelda = `$${montoPendiente.toFixed(2)}`;
        }

        const tooltipParcialTxt = tieneParcial
            ? (() => {
                const motivo      = (g.condonacion_parcial_motivo || '').trim();
                const motivoCorto = motivo.length > 350 ? motivo.substring(0, 350) + '...' : motivo;
                return 'Monto condonado: $' + parcialMonto.toFixed(2) + '\n\nMotivo:\n' + motivoCorto;
            })()
            : '';
        const tooltipParcialEsc = tooltipParcialTxt
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '&#10;');
        const iconoParcialHtml = tieneParcial
            ? '<span class="info-condonacion-parcial text-info" style="cursor:pointer;" title="' + tooltipParcialEsc + '" data-monto="' + parcialMonto.toFixed(2) + '" data-motivo="' + (g.condonacion_parcial_motivo || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;').substring(0, 500) + '"><i class="fa fa-info-circle" aria-hidden="true"></i></span>'
            : '';

        tabla.innerHTML += `
            <tr data-id-gasto="${idGasto}" data-tiene-parcial="${tieneParcial ? '1' : '0'}" data-puede-parcial="${puedeParcial ? '1' : '0'}">
                <td>
                    <input type="checkbox"
                       class="form-check-input chk-condona"
                       data-id="${idGasto}"
                       data-monto="${montoFaltaCondonar.toFixed(2)}"
                       onchange="recalcularCondonacion()">
                </td>
                <td>${g.semana}</td>
                <td>${g.periodo}</td>
                <td>${g.parcialidad}</td>
                <td>$${parseFloat(g.cuota).toFixed(2)}</td>
                <td>${montoCelda}</td>
                <td>
                    ${tieneParcial ? iconoParcialHtml : !puedeParcial ? `<span class="text-muted small" title="Primero debe aplicar condonación parcial o total a la semana anterior.">Primero semana anterior</span>` : `
                    <button class="btn btn-sm btn-outline-primary btn-editar-condonar-parcial d-none"
                        data-id-gasto="${idGasto}"
                        data-monto-original="${montoOrig.toFixed(2)}"
                        data-monto-pendiente="${montoPendiente.toFixed(2)}"
                        data-condonacion-parcial-monto="${parcialMonto.toFixed(2)}"
                        data-condonacion-parcial-motivo="${parcialMotivo}"
                        title="Editar"
                        onclick="editarGastoCobranza(this)">
                        <i class="fa fa-edit"></i>
                    </button>
                    `}
                </td>
            </tr>`;
    });

    // Total = suma de montos pendientes (ya calculados correctamente en el backend)
    const totalSinCondonar = gastos.reduce((acc, g) => acc + parseFloat(g.monto || 0), 0);
    document.getElementById('montoTotalSinCondonar').textContent = totalSinCondonar.toFixed(2);

    const btnCondonarTotal = document.getElementById('btnCondonarTotal');
    if (btnCondonarTotal) btnCondonarTotal.disabled = true;

    recalcularCondonacion();
}




function _fetchGastosCobranza(idCredito) {
    const tabla = document.getElementById('tablaGastos');

    tabla.innerHTML = `
        <tr>
            <td colspan="7" class="text-center text-muted">
                Cargando gastos...
            </td>
        </tr>`;

    fetch('/EstadoCuenta/getGastosCobranza', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ idCredito })
    })
    .then(res => res.json())
    .then(resp => {
        if (!resp.success) {
            Swal.fire("Error", resp.mensaje, "error");
            tabla.innerHTML = '';
            return;
        }
        _pintarTablaGastos(resp.datos ?? resp.data ?? [], idCredito);
    })
    .catch(err => {
        console.error("ERROR consultaGastosCondonables:", err);
        Swal.fire("Error", "Error de conexión con el servidor", "error");
        tabla.innerHTML = '';
    });
}

/**
 * Oculta cajas de resumen cuando el valor es 0 y el bloque de motivo hasta que haya monto a condonar.
 * Departamento 9: no altera cajas/motivo ya ocultos por PHP; solo ajusta total sin condonar y la fila.
 */
function syncVisibilidadResumenCondonacion() {
    const modal = document.getElementById('modalCondonar');
    if (!modal) return;
    const dept9 = modal.getAttribute('data-condonar-dept9') === '1';

    const elCount = document.getElementById('countCondonados');
    const elMonto = document.getElementById('montoCondonar');
    const elTotalSin = document.getElementById('montoTotalSinCondonar');
    const count = parseInt(String((elCount && elCount.textContent) || '0'), 10) || 0;
    const montoSel = parseFloat(String((elMonto && elMonto.textContent) || '0')) || 0;
    const totalSin = parseFloat(String((elTotalSin && elTotalSin.textContent) || '0')) || 0;

    const boxTotal = document.getElementById('boxTotalSinCondonarCol');
    if (boxTotal) {
        boxTotal.classList.toggle('d-none', totalSin < 0.005);
    }

    if (!dept9) {
        const boxSel = document.getElementById('boxSeleccionados');
        const boxMo = document.getElementById('boxMonto');
        if (boxSel) boxSel.classList.toggle('d-none', count === 0);
        if (boxMo) boxMo.classList.toggle('d-none', montoSel < 0.005);
        const wrapMot = document.getElementById('wrapMotivoCondonacionTotal');
        const showMot = montoSel >= 0.005;
        if (wrapMot) {
            wrapMot.classList.toggle('d-none', !showMot);
            if (!showMot) {
                const ta = document.getElementById('descripcionCondonacion');
                if (ta) ta.value = '';
                const sm = document.getElementById('idMotivoCondonacionCobranza');
                if (sm) sm.value = '';
            }
        }
    }

    const fila = document.getElementById('filaResumenCondonacion');
    if (fila) {
        const anySel = !dept9 && (count > 0 || montoSel >= 0.005);
        const anyTotal = totalSin >= 0.005;
        const showFila = dept9 ? anyTotal : (anySel || anyTotal);
        fila.classList.toggle('d-none', !showFila);
    }
}

    function recalcularCondonacion() {

        const checks = document.querySelectorAll('.chk-condona:checked');
        const btnCondonarTotal = document.getElementById('btnCondonarTotal');

        let total = 0;

        checks.forEach(chk => {
            total += parseFloat(chk.dataset.monto || 0);
        });

        document.getElementById('countCondonados').textContent = checks.length;
        document.getElementById('montoCondonar').textContent = total.toFixed(2);

        if (btnCondonarTotal) {
            btnCondonarTotal.disabled = checks.length === 0;
        }

        document.querySelectorAll('#tablaGastos tr[data-id-gasto]').forEach(tr => {
            const chk = tr.querySelector('.chk-condona');
            const btn = tr.querySelector('.btn-editar-condonar-parcial');
            if (!btn || tr.dataset.tieneParcial === '1') return;
            if (tr.dataset.puedeParcial !== '1') return;
            if (chk && chk.checked) btn.classList.remove('d-none');
            else btn.classList.add('d-none');
        });

        syncVisibilidadResumenCondonacion();
    }

    document.addEventListener('click', function(e) {
        const el = e.target.closest('.info-condonacion-parcial');
        if (!el) return;
        e.preventDefault();
        e.stopPropagation();
        const monto = el.getAttribute('data-monto') || '0';
        let motivo = (el.getAttribute('data-motivo') || '').replace(/&quot;/g, '"').replace(/&#39;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>');
        motivo = motivo.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        Swal.fire({
            title: 'Condonación parcial',
            html: '<p class="text-start"><strong>Monto condonado:</strong> $' + monto + '</p><p class="text-start mt-2"><strong>Motivo:</strong></p><p class="text-start text-muted small" style="white-space:pre-wrap;">' + (motivo || '—') + '</p>',
            icon: 'info',
            confirmButtonText: 'Entendido',
            width: '480px'
        });
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
        document.getElementById('condonarParcial_motivo').value = (btn.getAttribute('data-condonacion-parcial-motivo') || '').replace(/&quot;/g, '"').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
        document.getElementById('condonarParcial_motivoCount').textContent = document.getElementById('condonarParcial_motivo').value.length;
        const modalParcial = new bootstrap.Modal(document.getElementById('modalCondonarParcial'));
        modalParcial.show();
    }

    const elCondParcialMotivo = document.getElementById('condonarParcial_motivo');
    if (elCondParcialMotivo) {
        elCondParcialMotivo.addEventListener('input', function() {
            const cnt = document.getElementById('condonarParcial_motivoCount');
            if (cnt) cnt.textContent = this.value.length;
        });
    }

    const btnCondParcialAceptar = document.getElementById('btnCondonarParcialAceptar');
    if (btnCondParcialAceptar) {
        btnCondParcialAceptar.addEventListener('click', function() {
        const idGasto = document.getElementById('condonarParcial_idGasto').value;
        const idCredito = document.getElementById('condonarParcial_idCredito').value;
        const montoParcial = parseFloat(document.getElementById('condonarParcial_monto').value || 0);
        const motivo = document.getElementById('condonarParcial_motivo').value.trim();
        const montoMax = parseFloat(document.getElementById('condonarParcial_montoMax').textContent || 0);

        if (!idGasto || !idCredito) {
            Swal.fire('Error', 'Datos de gasto o crédito no disponibles.', 'error');
            return;
        }

        // Ambos son obligatorios para poder dar Aceptar
        const faltaMonto = montoParcial <= 0;
        const faltaMotivo = motivo.length < 25;
        if (faltaMonto && faltaMotivo) {
            Swal.fire('Atención', 'Debe completar el monto a condonar y el motivo de condonación (mínimo 25 caracteres) para continuar.', 'warning');
            return;
        }
        if (faltaMonto) {
            Swal.fire('Atención', 'Debe indicar el monto a condonar (mayor a 0 y menor al monto total del gasto).', 'warning');
            return;
        }
        if (faltaMotivo) {
            Swal.fire('Atención', 'Debe completar el motivo de condonación con al menos 25 caracteres.', 'warning');
            return;
        }
        if (montoParcial >= montoMax) {
            Swal.fire('Atención', 'El monto a condonar debe ser menor al monto total del gasto.', 'warning');
            return;
        }

        Swal.fire({
            title: '¿Aplicar condonación parcial?',
            text: 'Se registrará la condonación parcial de $' + montoParcial.toFixed(2) + '. ¿Continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, aplicar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) return;

            fetch('/EstadoCuenta/guardarCondonacionParcialGasto', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    id_gastos_cobranza: idGasto,
                    monto_parcial: montoParcial,
                    motivo: motivo
                })
            })
                .then(res => res.json())
                .then(resp => {
                    if (!resp.success) {
                        Swal.fire('Error', resp.mensaje || 'Error al guardar', 'error');
                        return;
                    }
                    Swal.fire('Guardado', resp.mensaje || 'Condonación parcial guardada.', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('modalCondonarParcial')).hide();
                    consultaGastosCondonables(idCredito);
                })
                .catch(() => Swal.fire('Error', 'Error de conexión', 'error'));
        });
    });
    }

    // Función para abrir el modal de direcciones
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

        modalElement.addEventListener('hidden.bs.modal', function onHide() {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
            modalElement.removeEventListener('hidden.bs.modal', onHide);
        });
    }

    // Mover el modal al body cuando se carga la página
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('modalDirecciones');
        if (modalElement && modalElement.parentElement !== document.body) {
            document.body.appendChild(modalElement);
        }
        // Scrim propio: crear capa fija por encima de todo el layout; modales se mueven a body
        const mc = document.getElementById('modalCondonar');
        const mp = document.getElementById('modalCondonarParcial');
        const SCRIM_ID = 'scrim-condonar-estado-cuenta';
        const SCRIM_Z = 9998;
        const MODAL_CONDO_Z = 9999;
        const MODAL_PARCIAL_Z = 10000;

          // 👇 AGREGAR AQUÍ ADENTRO, ANTES DEL CIERRE

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
        function removeScrim() {
            var el = document.getElementById(SCRIM_ID);
            if (el && el.parentNode) el.parentNode.removeChild(el);
        }
        function moveModalToBody(modalEl, z) {
            if (!modalEl || modalEl.parentNode === document.body) return;
            document.body.appendChild(modalEl);
            modalEl.style.setProperty('z-index', String(z), 'important');
            var p = modalEl.parentElement;
            while (p && p !== document.body) {
                p.style.setProperty('z-index', String(z), 'important');
                p = p.parentElement;
            }
        }

        if (mc) {
            mc.addEventListener('show.bs.modal', function () {
                document.body.classList.add('modal-condonar-open', 'modal-open');
                var scrim = getOrCreateScrim();
                if (!scrim.parentNode) document.body.appendChild(scrim);
                moveModalToBody(mc, MODAL_CONDO_Z);
            });
            mc.addEventListener('shown.bs.modal', function () {
                mc.style.setProperty('z-index', String(MODAL_CONDO_Z), 'important');
                var p = mc.parentElement;
                while (p && p !== document.body) {
                    p.style.setProperty('z-index', String(MODAL_CONDO_Z), 'important');
                    p = p.parentElement;
                }
            });
            mc.addEventListener('hidden.bs.modal', function () {
                document.body.classList.remove('modal-condonar-open', 'modal-condonar-parcial-open');
                if (!document.body.classList.contains('modal-condonar-parcial-open')) document.body.classList.remove('modal-open');
                removeScrim();
                mc.style.removeProperty('z-index');
                var p = mc.parentElement;
                while (p && p !== document.body) {
                    p.style.removeProperty('z-index');
                    p = p.parentElement;
                }
            });
        }
        if (mp) {
            mp.addEventListener('show.bs.modal', function () {
                document.body.classList.add('modal-condonar-parcial-open', 'modal-open');
                var scrim = getOrCreateScrim();
                if (!scrim.parentNode) document.body.appendChild(scrim);
                moveModalToBody(mp, MODAL_PARCIAL_Z);
                if (mc && mc.parentNode !== document.body) moveModalToBody(mc, MODAL_CONDO_Z);
            });
            mp.addEventListener('shown.bs.modal', function () {
                var scrim = document.getElementById(SCRIM_ID);
                if (scrim) scrim.style.setProperty('z-index', String(SCRIM_Z), 'important');
                if (mc) {
                    mc.style.setProperty('z-index', String(MODAL_CONDO_Z), 'important');
                    var p = mc.parentElement;
                    while (p && p !== document.body) {
                        p.style.setProperty('z-index', String(MODAL_CONDO_Z), 'important');
                        p = p.parentElement;
                    }
                }
                mp.style.setProperty('z-index', String(MODAL_PARCIAL_Z), 'important');
                var q = mp.parentElement;
                while (q && q !== document.body) {
                    q.style.setProperty('z-index', String(MODAL_PARCIAL_Z), 'important');
                    q = q.parentElement;
                }
            });
            mp.addEventListener('hidden.bs.modal', function () {
                document.body.classList.remove('modal-condonar-parcial-open');
                if (!document.body.classList.contains('modal-condonar-open')) {
                    document.body.classList.remove('modal-open');
                    removeScrim();
                }
                mp.style.removeProperty('z-index');
                var q = mp.parentElement;
                while (q && q !== document.body) {
                    q.style.removeProperty('z-index');
                    q = q.parentElement;
                }
            });
        }

      document.addEventListener('click', function(e) {
    if (e.target.closest('#tab-historial-btn')) {
        const idCredito = idCreditoCondonar;        if (idCredito) cargarHistorialGastos();
        const footer = document.querySelector('#modalCondonar .modal-footer');
        if (footer) footer.style.display = 'none';
    }
    if (e.target.closest('#tab-gastos-btn')) {
        const footer = document.querySelector('#modalCondonar .modal-footer');
        if (footer) footer.style.display = '';
    }
});
    });


    function confirmarCondonacion(id_credito) {

        const comentario = document.getElementById('descripcionCondonacion').value.trim();
        const selMot = document.getElementById('idMotivoCondonacionCobranza');
        const checks = document.querySelectorAll('.chk-condona:checked');

        if (checks.length === 0) {
            Swal.fire("Atención", "Selecciona al menos un gasto", "warning");
            return;
        }

        const rawMot = selMot ? String(selMot.value).trim() : '';
        if (rawMot === '') {
            Swal.fire('Atención', 'Seleccione una opción en motivo de la condonación (convenio de pago).', 'warning');
            return;
        }
        const idMotivoCondonacion = parseInt(rawMot, 10);
        if (!Number.isFinite(idMotivoCondonacion) || idMotivoCondonacion < 1) {
            Swal.fire('Atención', 'Seleccione un motivo de condonación válido.', 'warning');
            return;
        }

        if (!comentario) {
            Swal.fire("Atención", "El motivo de la condonación es obligatorio", "warning");
            return;
        }
        if (comentario.length < 25) {
            Swal.fire("Atención", "El motivo debe tener al menos 25 caracteres.", "warning");
            return;
        }

        const gastos = [];
        let total = 0;

        checks.forEach(chk => {
            const id = chk.dataset.id;
            const monto = parseFloat(chk.dataset.monto);

            gastos.push({
                id_gastos_cobranza: id,
                monto: monto
            });

            total += monto;
        });

        fetch('/EstadoCuenta/confirmarCondonacionGastos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                idCredito: id_credito, // o como lo tengas
                comentario: comentario,
                id_motivo_condonacion: idMotivoCondonacion,
                total: total,
                gastos: gastos
            })
        })
            .then(res => res.json())
            .then(resp => {

                if (!resp.success) {
                    var _em = resp.mensaje || 'Error';
                    if (resp.error) {
                        _em += ' — ' + resp.error;
                    }
                    Swal.fire("Error", _em, "error");
                    return;
                }

                Swal.fire("Éxito", "Gastos condonados correctamente", "success");

                bootstrap.Modal
                    .getInstance(document.getElementById('modalCondonar'))
                    .hide();

            })
            .catch(err => {
                console.error(err);
                Swal.fire("Error", "Error de conexión", "error");
            });
    }

/* ==========================
   PERSISTENCIA ACORDEÓN INFO CRÉDITO
   ========================== */

document.addEventListener('DOMContentLoaded', function() {
    const accordion = document.getElementById('collapseInfoCredito');
    const accordionButton = document.querySelector('[data-bs-target="#collapseInfoCredito"]');
    if (!accordion || !accordionButton) {
        return;
    }

    // Recuperar estado del localStorage
    const accordionState = localStorage.getItem('accordionInfoCredito');

    // Si estaba abierto, abrirlo
    if (accordionState === 'open') {
        // Usar Bootstrap para abrir
        const bsCollapse = new bootstrap.Collapse(accordion, {
            toggle: false
        });
        bsCollapse.show();

        // Actualizar clases del botón
        accordionButton.classList.remove('collapsed');
        accordionButton.setAttribute('aria-expanded', 'true');
    }

    // Escuchar eventos de cambio
    accordion.addEventListener('show.bs.collapse', function() {
        localStorage.setItem('accordionInfoCredito', 'open');
    });

    accordion.addEventListener('hide.bs.collapse', function() {
        localStorage.setItem('accordionInfoCredito', 'closed');
    });

    // También manejar clics directos en el botón
    accordionButton.addEventListener('click', function() {
        setTimeout(() => {
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            localStorage.setItem('accordionInfoCredito', isExpanded ? 'open' : 'closed');
        }, 100);
    });
});






</script>

<!-- Modal Direcciones - Movido al final para evitar problemas de z-index -->
<div class="modal fade" id="modalDirecciones" tabindex="-1" aria-labelledby="modalDireccionesLabel" aria-hidden="true" style="position: fixed; z-index: 1055;">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="modalDireccionesLabel">
                    <i class="fa fa-map-marker-alt me-2 text-primary"></i>
                    Direcciones del Cliente
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <!-- Body (contenido: getComplementosEstadoCuenta) -->
            <div class="modal-body py-4">
                <div id="ec-modal-direcciones-body" class="row g-3">
                    <div class="col-12 text-center text-muted py-4">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Cargando direcciones…
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

<script>
    // Asegurar que el modal esté en el body al cargar
    (function() {
        const modalElement = document.getElementById('modalDirecciones');
        if (modalElement && modalElement.parentElement !== document.body) {
            document.body.appendChild(modalElement);
        }
    })();
</script>

<script>
    let idCreditoCondonar = null;
let historialCargado = false;

(function(){
    var style=document.createElement('style');
    style.textContent='.estado-cuenta-easter-wrap{position:fixed;inset:0;z-index:1058;pointer-events:none;overflow:hidden}.estado-cuenta-easter-money{position:absolute;left:0;top:0;font-size:20px;pointer-events:none;opacity:0;animation:ecMoneyBurst 1.3s ease-out forwards}.estado-cuenta-easter-toast{position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);z-index:1060;background:linear-gradient(135deg,#166534 0%,#22c55e 50%,#4ade80 100%);color:#fff;padding:20px 40px;border-radius:16px;font-size:1.15rem;font-weight:700;box-shadow:0 16px 48px rgba(34,197,94,0.4);border:2px solid rgba(255,255,255,0.3);opacity:0;animation:estadoCuentaEasterIn .4s ease forwards;pointer-events:none;text-align:center}.estado-cuenta-easter-toast .estado-cuenta-easter-emoji{font-size:2rem;display:block;margin-bottom:6px}@keyframes ecMoneyBurst{0%{opacity:1;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(calc(-50% + var(--ec-tx)), calc(-50% + var(--ec-ty))) scale(0.5)}}@keyframes estadoCuentaEasterIn{0%{opacity:0;transform:translate(-50%,-50%) scale(0.8)}100%{opacity:1;transform:translate(-50%,-50%) scale(1)}}@keyframes estadoCuentaEasterOut{0%{opacity:1;transform:translate(-50%,-50%) scale(1)}100%{opacity:0;transform:translate(-50%,-50%) scale(0.95)}}';
    document.head.appendChild(style);
    document.addEventListener("keydown",function(e){
        if(!e.ctrlKey||!e.shiftKey||(e.key!=="E"&&e.keyCode!==69))return;
        e.preventDefault();
        var wrap=document.createElement("div");
        wrap.className="estado-cuenta-easter-wrap";
        var moneyEmojis=["\uD83D\uDCB0","\uD83D\uDCB5","\uD83D\uDCB4"];
        var positions=[[0.2,0.25],[0.5,0.2],[0.75,0.3],[0.35,0.55]];
        for(var f=0;f<4;f++){
            var fw=document.createElement("div");
            fw.style.cssText="position:absolute;left:"+(positions[f][0]*100)+"%;top:"+(positions[f][1]*100)+"%;width:0;height:0;";
            var num=28+Math.floor(Math.random()*12);
            var dist=90+Math.random()*50;
            for(var r=0;r<num;r++){
                var angle=(r/num)*Math.PI*2+Math.random()*0.4;
                var tx=Math.cos(angle)*dist+"px";
                var ty=Math.sin(angle)*dist+"px";
                var sp=document.createElement("span");
                sp.className="estado-cuenta-easter-money";
                sp.textContent=moneyEmojis[Math.floor(Math.random()*moneyEmojis.length)];
                sp.style.animationDelay=(f*0.15)+"s";
                sp.style.setProperty("--ec-tx",tx);
                sp.style.setProperty("--ec-ty",ty);
                fw.appendChild(sp);
            }
            wrap.appendChild(fw);
        }
        document.body.appendChild(wrap);
        var t=document.createElement("div");
        t.className="estado-cuenta-easter-toast";
        t.innerHTML='<span class="estado-cuenta-easter-emoji">\uD83D\uDCB0</span> \u00A1Cuenta al d\u00EDa, espartano!';
        document.body.appendChild(t);
        try{var a=new Audio("/assets/audio/coins.mp3");a.volume=0.45;a.play().catch(function(){});setTimeout(function(){a.pause();a.currentTime=0;},2200);}catch(e){}
        setTimeout(function(){t.style.animation="estadoCuentaEasterOut .35s ease forwards";setTimeout(function(){if(t.parentNode)t.parentNode.removeChild(t);if(wrap.parentNode)wrap.parentNode.removeChild(wrap);},350);},2800);
    });
})();


// ===== HISTORIAL GASTOS COBRANZA =====

function cargarHistorialGastos() {
    if (historialCargado) return;

    // ── Intentar con datos preloaded desde PHP ──
    if (typeof HISTORIAL_GASTOS_PRELOAD !== 'undefined' && Array.isArray(HISTORIAL_GASTOS_PRELOAD) && HISTORIAL_GASTOS_PRELOAD.length > 0) {
        _pintarHistorial(HISTORIAL_GASTOS_PRELOAD);
        historialCargado = true;
        return;
    }

    // ── Fallback: fetch normal ──
    _fetchHistorialGastos();
}

function _pintarHistorial(datos) {
    const contenedor = document.getElementById('contenedorHistorial');

    if (!datos || datos.length === 0) {
        contenedor.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                <p>No hay gastos registrados en el historial.</p>
            </div>`;
        return;
    }

    let html = `
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Semana</th>
                    <th>Periodo</th>
                    <th class="text-center">Parcialidad</th>
                    <th class="text-end">Monto Original</th>
                    <th class="text-center">Tipo</th>
                    <th class="text-end">Monto Final</th>
                    <th>Motivo</th>
                    <th class="text-center">Fecha</th>
                </tr>
            </thead>
            <tbody>`;

    datos.forEach(g => {
        const esCondonado        = parseInt(g.condonado) === 1;
        const montoOriginal      = parseFloat(g.monto_original || 0);
        const condonacionParcial = parseFloat(g.condonacion_parcial_monto || 0);
        const montoParcialPagado = parseFloat(g.monto_parcial_pagado || 0);
        const parcialidad        = g.parcialidad || '—';
        const fechaPago          = g.fecha_pago || '—';
        const motivoCell         = (g.motivo_resumen != null && String(g.motivo_resumen).trim() !== '') ? String(g.motivo_resumen) : '—';

        const esPagoParcialCompletado = !esCondonado
            && montoParcialPagado > 0
            && montoParcialPagado < montoOriginal;

        if (esCondonado) {
            html += `
                <tr>
                    <td>${g.semana}</td>
                    <td><small class="text-muted">${g.periodo}</small></td>
                    <td class="text-center">${parcialidad}</td>
                    <td class="text-end">$${montoOriginal.toFixed(2)}</td>
                    <td class="text-center">
                        <span class="badge bg-warning text-dark px-2 py-1" style="min-width:80px;">Condonado</span>
                    </td>
                    <td class="text-end"><span class="text-muted">$0.00</span></td>
                    <td class="small">${motivoCell}</td>
                    <td class="text-center"><small>${g.fecha_condonacion || '—'}</small></td>
                </tr>`;

        } else if (esPagoParcialCompletado) {
            // Solo mostrar "+ …" si queda efectivo tras el parcial; no confundir condonación parcial con segundo abono.
            const restoEfectivoTrasParcialYCond = Math.max(
                0,
                parseFloat((montoOriginal - montoParcialPagado - condonacionParcial).toFixed(2)),
            );
            const htmlMontoFinalParcial =
                '<span class="text-success fw-bold">$' + montoParcialPagado.toFixed(2) + '</span>' +
                (restoEfectivoTrasParcialYCond > 0.009
                    ? '<div class="small text-muted lh-sm mt-1">+ $' + restoEfectivoTrasParcialYCond.toFixed(2) + '</div>'
                    : '');
            const htmlMontoOriginalParcial = '<span class="text-muted text-decoration-line-through" style="font-size:0.9em;">$' +
                montoOriginal.toFixed(2) + '</span>' +
                '<div class="text-dark small fw-bold">$' + montoParcialPagado.toFixed(2) + '</div>';
            html += `
                <tr>
                    <td>${g.semana}</td>
                    <td><small class="text-muted">${g.periodo}</small></td>
                    <td class="text-center">${parcialidad}</td>
                    <td class="text-end">${htmlMontoOriginalParcial}</td>
                    <td class="text-center">
                        <div class="d-inline-flex flex-column align-items-center gap-1">
                            <span class="badge bg-success px-2 py-1" style="min-width:7.5rem;">Completado</span>
                            <div class="bg-secondary opacity-25 rounded-pill" style="height:1px;width:3.25rem;" role="presentation" aria-hidden="true"></div>
                        </div>
                    </td>
                    <td class="text-end">${htmlMontoFinalParcial}</td>
                    <td class="small">${motivoCell}</td>
                    <td class="text-center"><small>${fechaPago}</small></td>
                </tr>`;

        } else {
            const montoPagado = parseFloat((montoOriginal - condonacionParcial).toFixed(2));
            const htmlMontoOriginal = condonacionParcial > 0
                ? `<span class="text-muted text-decoration-line-through" style="font-size:0.9em;">$${montoOriginal.toFixed(2)}</span>
                   <div class="text-dark small fw-bold">$${montoPagado.toFixed(2)}</div>`
                : `$${montoOriginal.toFixed(2)}`;

            html += `
                <tr>
                    <td>${g.semana}</td>
                    <td><small class="text-muted">${g.periodo}</small></td>
                    <td class="text-center">${parcialidad}</td>
                    <td class="text-end">${htmlMontoOriginal}</td>
                    <td class="text-center">
                        <span class="badge bg-success px-2 py-1" style="min-width:80px;">Pagado</span>
                    </td>
                    <td class="text-end"><span class="text-success fw-bold">$${montoPagado.toFixed(2)}</span></td>
                    <td class="small">${motivoCell}</td>
                    <td class="text-center"><small>${fechaPago}</small></td>
                </tr>`;
        }
    });

    html += `</tbody></table></div>`;
    contenedor.innerHTML = html;
}

function _fetchHistorialGastos() {
    const idCredito  = idCreditoCondonar;
    const contenedor = document.getElementById('contenedorHistorial');

    if (!idCredito) {
        contenedor.innerHTML = '<p class="text-muted text-center py-3">No se encontró el ID de crédito.</p>';
        return;
    }

    contenedor.innerHTML = `
        <div class="text-center text-muted py-4">
            <i class="fa fa-spinner fa-spin fa-2x mb-2 d-block"></i>
            <p>Cargando historial...</p>
        </div>`;

    fetch('/EstadoCuenta/getHistorialGastosCobranza', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ idCredito })
    })
    .then(res => res.json())
    .then(resp => {
        if (!resp.success) {
            contenedor.innerHTML = '<p class="text-danger text-center py-3">Error al cargar el historial.</p>';
            return;
        }
        _pintarHistorial(resp.datos ?? []);
        historialCargado = true;
    })
    .catch(() => {
        contenedor.innerHTML = '<p class="text-danger text-center py-3">Error de conexión.</p>';
    });
}

// Reset historial al cerrar modal
document.getElementById('modalCondonar').addEventListener('hidden.bs.modal', function () {
    historialCargado = false;
    document.getElementById('contenedorHistorial').innerHTML = `
        <div class="text-center text-muted py-4">
            <i class="fa fa-history fa-2x mb-2 d-block"></i>
            <p>Haz clic en la pestaña para cargar el historial.</p>
        </div>`;
    const tabGastosBtn = document.getElementById('tab-gastos-btn');
    if (tabGastosBtn) tabGastosBtn.click();
    document.getElementById('descripcionCondonacion').value = ''; // Limpiar motivo condonación al cerrar modal
    const smC = document.getElementById('idMotivoCondonacionCobranza');
    if (smC) smC.value = '';
});

// Ajuste dinámico real: baja fuente hasta que el texto quepa (sin puntos suspensivos).
function ecAjustarTextoMetricas() {
    const esMovil = window.matchMedia('(max-width: 576px)').matches;
    const nodos = document.querySelectorAll('.ec-metricas-fila-principal .ec-metrica-pago-text h5.ec-fit-dynamic');

    nodos.forEach((el) => {
        if (esMovil) {
            el.style.removeProperty('font-size');
            return;
        }

        if (!el.clientWidth || el.clientWidth <= 0) return;

        const maxPx = parseFloat(getComputedStyle(el).fontSize) || 16;
        const minPx = el.classList.contains('ec-fit-ref') ? 6 : 8;
        let low = minPx;
        let high = maxPx;
        let best = minPx;

        const cabe = () => el.scrollWidth <= el.clientWidth + 1;

        el.style.fontSize = `${high}px`;
        if (cabe()) return;

        while ((high - low) > 0.25) {
            const mid = (low + high) / 2;
            el.style.fontSize = `${mid}px`;
            if (cabe()) {
                best = mid;
                low = mid;
            } else {
                high = mid;
            }
        }

        el.style.fontSize = `${best.toFixed(2)}px`;
    });
}

(function ecInitAjusteMetricas() {
    let timer = null;
    const ejecutar = () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(ecAjustarTextoMetricas, 40);
    };

    document.addEventListener('DOMContentLoaded', ejecutar);
    window.addEventListener('load', ejecutar);
    window.addEventListener('resize', ejecutar);
    window.addEventListener('orientationchange', ejecutar);

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(ejecutar).catch(() => {});
    }

    const fila = document.querySelector('.ec-metricas-fila-principal');
    if (window.ResizeObserver && fila) {
        const ro = new ResizeObserver(ejecutar);
        ro.observe(fila);
    }
})();
//abonos_efectivo_total
</script>
