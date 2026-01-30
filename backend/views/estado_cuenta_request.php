<?php
session_start();
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

    /* 3. MÉTRICAS (flex escalable) */
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
       gap: 0.5rem;
       margin-bottom: 0.5rem;
       padding: 0.25rem 0;
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
       min-height: 1.5rem;
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
           gap: 0.4rem;
           margin-bottom: 0.6rem;
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
        
        .sidebar-cliente .d-flex.justify-content-between.my-3 h5 {
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
        
        .sidebar-cliente .d-flex.justify-content-between.my-3 h5 {
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
        
        .sidebar-cliente .d-flex.justify-content-between.my-3 > div {
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
        
        .sidebar-cliente .d-flex.justify-content-between.my-3 {
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
   .btn-notas {
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
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.25rem 1.4rem;
        position: relative;
        border: 1px solid rgba(0,0,0,.06);
        box-shadow: 0 6px 18px rgba(0,0,0,.08);
        transition: transform .25s ease, box-shadow .25s ease;
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
        font-size: .82rem;
        padding: .25rem 0;
    }

    .info-line span {
        color: #6c757d;
    }

    .info-line strong {
        font-weight: 600;
        color: #212529;
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
       MODAL DIRECCIONES - Asegurar que se muestre
       ========================== */
    #modalDirecciones {
        z-index: 1055 !important;
    }
    
    #modalDirecciones.show {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    
    #modalDirecciones .modal-dialog {
        z-index: 1056 !important;
        margin: 1.75rem auto !important;
    }
    
    .modal-backdrop {
        z-index: 1050 !important;
    }
    
    .modal-backdrop.show {
        opacity: 0.5 !important;
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
       
       .col-xl-8.col-lg-7 .d-flex.justify-content-between.align-items-center.mb-3 {
           flex-direction: column;
           align-items: flex-start;
           margin-bottom: 0.75rem !important;
       }
       
       .col-xl-8.col-lg-7 .d-flex.justify-content-between.align-items-center.mb-3 h5 {
           font-size: 0.85rem;
           margin-bottom: 0.5rem;
           width: 100%;
       }
       
       .btn-notas, .btn-condonar, .btn-dictaminar {
           width: 36px !important;
           height: 36px !important;
       }
       
       .btn-notas i, .btn-condonar i, .btn-dictaminar i {
           font-size: 0.9rem !important;
       }
       
       .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div {
           flex: 1 1 calc(50% - 0.5rem);
           margin-bottom: 0.5rem;
           gap: 0.5rem;
       }
       
       .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 h5 {
           font-size: 0.8rem;
       }
       
       .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 span {
           font-size: 0.7rem;
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

   .accordion-body .d-flex.justify-content-between.my-3 {
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
       PADDING MÉTRICAS HORIZONTALES (Cuota Semanal, etc.)
   ========================== */
   
   /* MOD: Añadir padding a métricas horizontales */
   .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 {
       padding: 1rem 1.25rem !important;
       background-color: #f8f9fa;
       border-radius: 10px;
       margin: 1.5rem 0 !important;
       border: 1px solid #e9ecef;
   }
   
   .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div {
       padding: 0.5rem 0.75rem;
       background: white;
       border-radius: 8px;
       border: 1px solid #f0f0f0;
       box-shadow: 0 2px 4px rgba(0,0,0,0.03);
   }
   
   .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 {
       gap: 1rem !important;
   }
   
   @media (max-width: 768px) {
       .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 {
           padding: 0.75rem !important;
       }
       
       .col-xl-8.col-lg-7 .d-flex.justify-content-around.flex-wrap.my-6 > div {
           padding: 0.4rem 0.6rem;
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

</style>

<div class="row">

  <!-- SIDEBAR CLIENTE -->
<div class="col-xl-4 col-lg-5 order-1 order-lg-0 sidebar-cliente">
    <div class="card mb-6">
        <div class="card-body">
            
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
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalDirecciones"
                                    onclick="abrirModalDirecciones()">
                                Direcciones
                            </button>
                        </small>
                    </div>
                </div>
            </div>
            
            <!-- ================================
               VERSIÓN DESKTOP (SIN ACORDEÓN)
            ================================ -->
            <div class="d-none d-lg-block desktop-info">
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
                
                <!-- INFORMACIÓN DEL CRÉDITO -->
              <hr class="my-2 w-100">
<small class="card-text text-uppercase text-body-secondary small">Información del Crédito</small>
<ul class="list-unstyled my-1 py-1 info-compact">
    <li>
        <i class="fa fa-money-bill fa-lg"></i>
        <div class="info-label">
            <span class="fw-medium">Monto Otorgado:</span>
            <span>$37,759.20</span>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Resumen general de pagos del cliente</h5>

            <div class="d-flex gap-2">
                <!-- BOTÓN DICTAMINAR -->
                <?php if (
                        isset($_SESSION['departamento'], $_SESSION['usuario_id']) &&
                        ($_SESSION['departamento'] == 2 || $_SESSION['usuario_id'] == 1)
                ): ?>
                    <button type="button"
                            class="btn btn-dictaminar position-relative"
                            data-bs-toggle="modal"
                            data-bs-target="#modalDictamen"
                            title="Dictaminar llamada">
                        <i class="fa fa-headset"></i>
                    </button>
                <?php endif; ?>

                <!-- BOTÓN CONDONAR -->
                <?php if (
                        isset($_SESSION['departamento'], $_SESSION['usuario_id']) &&
                        (in_array((int)$_SESSION['departamento'], [2, 9], true)|| $_SESSION['usuario_id'] == 1)
                ): ?>
                    <button type="button"
                            class="btn btn-condonar position-relative"
                            title="Condonar gastos de cobranza"
                            onclick="consultaGastosCondonables(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">
                        <i class="fa fa-hand-holding-usd"></i>
                    </button>
                <?php endif; ?>

                <!-- BOTÓN NOTAS (ICONO) -->
                <?php if (
                        isset($_SESSION['departamento'], $_SESSION['usuario_id']) &&
                        ($_SESSION['departamento'] == 2 || $_SESSION['usuario_id'] == 1)
                ): ?>
                    <button type="button"
                            class="btn btn-notas position-relative"
                            title="Notas del cliente"
                            onclick="consultaNotas(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">

                        <i class="fa fa-sticky-note"></i>

                        <span id="badgeNotas"
                              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= htmlspecialchars($notas['datos'][0]['num'] ?? '') ?>
        </span>
                    </button>
                <?php endif; ?>


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

    <?php
    // Preparar referencias dinámicamente
    $datosRef = $referencias['datos'][0] ?? [];
    $referenciasList = [];

    for ($i = 1; $i <= 3; $i++) {
        $nombreKey = "nombre_completo_referencia{$i}";
        $telefonoKey = "telefono_referencia{$i}";

        if (!empty($datosRef[$nombreKey])) {
            $referenciasList[] = [
                    'nombre' => $datosRef[$nombreKey],
                    'telefono' => $datosRef[$telefonoKey] ?? '—',
                    'tipo' => $i === 1 ? 'Principal' : "Referencia {$i}",
                    'icono' => $i === 1 ? 'fa-user text-success' : ($i === 2 ? 'fa-user-friends text-primary' : 'fa-user-tie text-warning')
            ];
        }
    }

    // RFC global
    $rfcCliente = $datosRef["rfc"] ?? '—';
    ?>

    <!-- Modal RFC -->
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
                                    
                                </div>
                            </div>
                        <?php endforeach; ?>

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

<div class="modal fade" id="modalCondonar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header bg-success bg-opacity-10">
                <h5 class="modal-title">
                    <i class="fa fa-hand-holding-usd text-success me-2"></i>
                    Condonar gastos de cobranza
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <!-- Resumen -->
                <div class="row mb-3">

                    <?php
                    $hideStyle = (
                            isset($_SESSION['departamento']) &&
                            (
                                    (int)$_SESSION['departamento'] === 9
                            )
                    ) ? 'style="display:none;"' : '';
                    ?>

                        <div class="col-md-4" id="boxSeleccionados" <?= $hideStyle ?>>
                            <div class="alert alert-success py-2 mb-2">
                                <strong>Seleccionados:</strong>
                                <span id="countCondonados">0</span>
                            </div>
                        </div>

                        <div class="col-md-4" id="boxMonto" <?= $hideStyle ?>>
                            <div class="alert alert-warning py-2 mb-2">
                                <strong>Monto a condonar:</strong>
                                $<span id="montoCondonar">0.00</span>
                            </div>
                        </div>


                    <div class="col-md-4">
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
                            <th># Cuota</th>
                            <th class="">Monto</th>
                            <th class=""></th>
                        </tr>
                        </thead>
                        <tbody id="tablaGastos">
                        <!-- Se llena dinámicamente -->
                        </tbody>
                    </table>
                </div>

                <!-- Motivo -->
                <div class="mt-3" <?= $hideStyle ?>>
                    <label class="form-label fw-semibold">
                        Motivo de la condonación (convenio de pago) <span class="text-danger">*</span>
                    </label>
                    <textarea
                            id="descripcionCondonacion"
                            class="form-control"
                            rows="3"
                            placeholder="Describe el motivo de la condonación..."
                    ></textarea>
                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer" <?= $hideStyle ?>>
                <button class="btn btn-secondary" data-bs-dismiss="modal" >
                    Cancelar
                </button>
                <button class="btn btn-success" onclick="confirmarCondonacion(<?= htmlspecialchars($dataEstadoCuenta["idCredito"] ?? '') ?>)">
                    <i class="fa fa-check me-1"></i>Condonar
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


                <input
                        type="hidden"
                        id="idCredito_dictamen"
                        name="idCredito_dictamen"
                        value="<?= htmlspecialchars($dataEstadoCuenta['idCredito'] ?? '') ?>"
            </div>

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

    modalDictamen.addEventListener('shown.bs.modal', function (event) {

        const button = event.relatedTarget;
        const idCredito = button.getAttribute('data-idcredito');

        // Guardar el id crédito
        document.getElementById('id_credito').value = idCredito;

        // Inicializar combos
        initDictamenModal();
    });



    const tipoContactoSelect      = document.getElementById('tipo_contacto');
    const resultadoContactoSelect = document.getElementById('resultado_contacto');
    const dictamenSelect          = document.getElementById('dictamen');
    const plataformaSelect        = document.getElementById('plataforma');

    const tipoMotivoSelect   = document.getElementById('tipo_motivo_no_pago');
    const motivoNoPagoSelect = document.getElementById('motivo_no_pago');

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


    tipoContactoSelect.addEventListener('change', function () {
        if (this.value) {
            cargarResultadosContacto(this.value);
        }
    });

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


    resultadoContactoSelect.addEventListener('change', function () {
        if (this.value) {
            cargarDictamenes(this.value);
        }
    });

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


    function initDictamenModal() {
        cargarTiposContacto();
        cargarPlataformas();
        cargarTiposMotivoNoPago(); // 👈 NUEVO
    }

    document.getElementById('modalDictamen')
        .addEventListener('shown.bs.modal', initDictamenModal);

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

    tipoMotivoSelect.addEventListener('change', function () {
        cargarMotivosNoPagoPorTipo(this.value);
    });

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
                comentarios: comentarios
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
    }



    function consultaGastosCondonables(idCredito) {

        if (!idCredito) {
            Swal.fire("Error", "Id de crédito inválido", "error");
            return;
        }

        const tabla = document.getElementById('tablaGastos');
        const countSpan = document.getElementById('countCondonados');
        const montoSpan = document.getElementById('montoCondonar');

        // Reset visual
        countSpan.textContent = 0;
        montoSpan.textContent = '0.00';
        document.getElementById('montoTotalSinCondonar').textContent = '0.00';

        tabla.innerHTML = `
        <tr>
            <td colspan="4" class="text-center text-muted">
                Cargando gastos...
            </td>
        </tr>
    `;

        fetch('/EstadoCuenta/getGastosCobranza', {
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
                    tabla.innerHTML = '';
                    return;
                }

                const gastos = resp.datos ?? resp.data ?? [];

                tabla.innerHTML = '';

                if (!Array.isArray(gastos) || gastos.length === 0) {
                    tabla.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No hay gastos de cobranza
                        </td>
                    </tr>
                `;
                    document.getElementById('montoTotalSinCondonar').textContent = '0.00';
                    return;
                }

                gastos.forEach(g => {

                    tabla.innerHTML += `
                    <tr>
                        <td>
                            <input type="checkbox"
                               class="form-check-input chk-condona"
                               data-id="${g.id_gasto}"
                               data-monto="${parseFloat(g.monto).toFixed(2)}"
                               onchange="recalcularCondonacion()">
                        </td>
                        <td>${g.semana}</td>
                        <td>${g.periodo}</td>
                        <td>${g.parcialidad != null && g.parcialidad !== '' ? g.parcialidad : '-'}</td>
                        
                        <td>$${parseFloat(g.cuota).toFixed(2)}</td>
                        <td>$${parseFloat(g.monto).toFixed(2)}</td>
                        <td>
                            <button style="display: none;" class="btn btn-sm btn-outline-primary" onclick="editarGastoCobranza(${g.id_gastos_cobranza})">  <i class="fa fa-edit"></i> </button>
                        </td>





                    </tr>
                `;
                });

                const totalSinCondonar = gastos.reduce((acc, g) => acc + parseFloat(g.monto || 0), 0);
                document.getElementById('montoTotalSinCondonar').textContent = totalSinCondonar.toFixed(2);

            })
            .catch(err => {
                console.error("ERROR consultaGastosCondonables:", err);
                Swal.fire("Error", "Error de conexión con el servidor", "error");
                tabla.innerHTML = '';
            });

        // 👉 Abrir modal SIEMPRE
        const modal = new bootstrap.Modal(
            document.getElementById('modalCondonar')
        );
        modal.show();
    }

    function recalcularCondonacion() {

        const checks = document.querySelectorAll('.chk-condona:checked');

        let total = 0;

        checks.forEach(chk => {
            total += parseFloat(chk.dataset.monto || 0);
        });

        document.getElementById('countCondonados').textContent = checks.length;
        document.getElementById('montoCondonar').textContent = total.toFixed(2);
    }

    // Función para abrir el modal de direcciones
    function abrirModalDirecciones() {
        const modalElement = document.getElementById('modalDirecciones');
        if (modalElement) {
            // Mover el modal al body si no está ahí
            if (modalElement.parentElement !== document.body) {
                document.body.appendChild(modalElement);
            }
            
            // Usar Bootstrap Modal API
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            modal.show();
        } else {
            console.error('Modal modalDirecciones no encontrado');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo encontrar el modal de direcciones'
            });
        }
    }

    // Mover el modal al body cuando se carga la página
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('modalDirecciones');
        if (modalElement && modalElement.parentElement !== document.body) {
            document.body.appendChild(modalElement);
        }
    });


    function confirmarCondonacion(id_credito) {

        const comentario = document.getElementById('descripcionCondonacion').value.trim();
        const checks = document.querySelectorAll('.chk-condona:checked');

        if (checks.length === 0) {
            Swal.fire("Atención", "Selecciona al menos un gasto", "warning");
            return;
        }

        if (!comentario) {
            Swal.fire("Atención", "El motivo de la condonación es obligatorio", "warning");
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
                total: total,
                gastos: gastos
            })
        })
            .then(res => res.json())
            .then(resp => {

                if (!resp.success) {
                    Swal.fire("Error", resp.mensaje, "error");
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

            <!-- Body -->
            <div class="modal-body py-4">
                <div class="row g-3">
                    <?php
                    // Validar que existan las direcciones antes de mostrarlas
                    $datosDirecciones = [];
                    
                    if (isset($direcciones)) {
                        if (is_array($direcciones)) {
                            // Si tiene estructura ['datos' => [...]]
                            if (isset($direcciones['datos']) && is_array($direcciones['datos'])) {
                                $datosDirecciones = $direcciones['datos'];
                            }
                            // Si es directamente un array
                            elseif (!empty($direcciones) && isset($direcciones[0])) {
                                $datosDirecciones = $direcciones;
                            }
                        }
                    }
                    
                    if (!empty($datosDirecciones)):
                        foreach ($datosDirecciones as $index => $direccion):
                            if (!is_array($direccion)) continue;
                            $domicilioCompleto = isset($direccion['Domicilio_Completo']) ? htmlspecialchars($direccion['Domicilio_Completo']) : 'No disponible';
                            $nombreCliente = isset($direccion['Nombre_cliente']) ? htmlspecialchars($direccion['Nombre_cliente']) : '';
                            $idCliente = isset($direccion['Id_cliente']) ? htmlspecialchars($direccion['Id_cliente']) : '';
                    ?>
                    <!-- Dirección <?= $index + 1 ?> -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fa fa-home text-success me-2"></i>
                                    <h6 class="mb-0">Domicilio Particular</h6>
                                </div>

                                <?php if ($nombreCliente): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block">Cliente:</small>
                                    <strong><?= $nombreCliente ?></strong>
                                </div>
                                <?php endif; ?>

                                <div class="mb-2">
                                    <small class="text-muted d-block">Dirección:</small>
                                    <p class="mb-0"><?= $domicilioCompleto ?></p>
                                </div>

                                <?php if ($idCliente): ?>
                                <div class="mb-2">
                                    <small class="text-muted">ID Cliente: <?= $idCliente ?></small>
                                </div>
                                <?php endif; ?>

                                <span class="badge bg-success">Principal</span>
                            </div>
                        </div>
                    </div>
                    <?php
                        endforeach;
                    else:
                    ?>
                    <!-- Mensaje cuando no hay direcciones -->
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fa fa-info-circle me-2"></i>
                            <strong>No se encontraron direcciones</strong>
                            <p class="mb-0 mt-2">No hay direcciones registradas para este cliente.</p>
                        </div>
                    </div>
                    <?php endif; ?>
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