<style>
    /* Barra título del dashboard (Estadísticas de Sabueso + Volver) — mismo lenguaje visual que las cards */
    .estad-sabueso-wrap .estad-titulo-bar {
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        border-radius: 1rem;
        padding: 0.85rem 1.25rem;
    }
    .estad-sabueso-wrap .estad-titulo-bar .estad-titulo-texto {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--bs-body-color, #212529);
    }
    .estad-sabueso-wrap .estad-titulo-bar .estad-titulo-icono {
        color: var(--bs-primary);
    }
    [data-bs-theme="dark"] .estad-sabueso-wrap .estad-titulo-bar,
    .dark-style .estad-sabueso-wrap .estad-titulo-bar {
        background: rgba(30, 34, 44, 0.65);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
    }

    /* Iconos con color en el selector de estadísticas (evitar grises) */
    #estadisticasSelectorWrap .estad-icono-sabueso { color: #0d6efd; }
    #estadisticasSelectorWrap .estad-icono-sabueso i { color: inherit; }
    #estadisticasSelectorWrap .estad-icono-otras { color: #0d9488; }
    #estadisticasSelectorWrap .estad-icono-otras i { color: inherit; }
    #estadisticasSelectorWrap .estad-icono-tableros { color: #7c3aed; }
    #estadisticasSelectorWrap .estad-icono-tableros i { color: inherit; }

    /* Liquid glass suave: compatible con tema global (sin segundo modo oscuro) */
    .estad-sabueso-wrap .estad-glass {
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        border-radius: 1rem;
    }
    [data-bs-theme="dark"] .estad-sabueso-wrap .estad-glass,
    .dark-style .estad-sabueso-wrap .estad-glass {
        background: rgba(30, 34, 44, 0.65);
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
    }
    .estad-sabueso-wrap .estad-kpi-card {
        border-radius: 1rem;
        overflow: hidden;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .estad-sabueso-wrap .estad-kpi-card:hover { transform: translateY(-2px); }
    .estad-sabueso-wrap .estad-icon-box {
        width: 2.5rem; height: 2.5rem; border-radius: 0.6rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
    }
    .estad-sabueso-wrap .estad-icon-cerrados {
        background: rgba(234, 179, 8, 0.22);
        color: #b45309;
    }
    .estad-sabueso-wrap .estad-num-big {
        font-size: 2.5rem; font-weight: 800; line-height: 1.1; letter-spacing: -0.03em;
    }
    .estad-sabueso-wrap .estad-time-icon {
        width: 3rem; height: 3rem; border-radius: 0.85rem;
        display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;
    }
    .estad-sabueso-wrap .estad-time-value { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; }
    /* Pills período */
    .estad-sabueso-wrap .estad-pill-group {
        background: rgba(var(--bs-primary-rgb), 0.08);
        border-radius: 0.65rem; padding: 0.25rem; display: inline-flex; gap: 0.25rem;
    }
    .estad-sabueso-wrap .estad-pill-group .btn {
        border: none; border-radius: 0.5rem; padding: 0.35rem 0.85rem;
        font-size: 0.8rem; font-weight: 500; background: transparent !important;
    }
    .estad-sabueso-wrap .estad-pill-group .btn.active {
        font-weight: 700;
        background-color: var(--bs-primary) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(var(--bs-primary-rgb), 0.35);
    }
    /* Lista tickets levantados: filas tipo “card” */
    .estad-sabueso-wrap .estad-period-list { padding: 0.5rem 1rem 1rem; }
    .estad-sabueso-wrap .estad-period-row {
        display: flex; align-items: center; gap: 1rem;
        padding: 0.65rem 1rem; margin-bottom: 0.5rem;
        border-radius: 0.75rem;
        background: rgba(var(--bs-primary-rgb), 0.04);
        border: 1px solid rgba(var(--bs-primary-rgb), 0.08);
        transition: background 0.15s ease, border-color 0.15s ease;
    }
    .estad-sabueso-wrap .estad-period-row:hover {
        background: rgba(var(--bs-primary-rgb), 0.08);
        border-color: rgba(var(--bs-primary-rgb), 0.15);
    }
    .estad-sabueso-wrap .estad-period-label {
        min-width: 5.5rem; font-weight: 600; font-size: 0.9rem;
    }
    .estad-sabueso-wrap .estad-mini-bar {
        flex: 1; height: 8px; border-radius: 999px;
        background: var(--bs-tertiary-bg, rgba(0,0,0,0.06));
        overflow: hidden; max-width: 100%;
    }
    .estad-sabueso-wrap .estad-mini-bar > div {
        height: 100%; border-radius: 999px;
        background: linear-gradient(90deg, var(--bs-primary), rgba(var(--bs-primary-rgb), 0.75));
        transition: width 0.9s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .estad-sabueso-wrap .estad-period-val {
        font-weight: 800; font-size: 1.1rem; min-width: 2.5rem; text-align: right;
        color: var(--bs-primary);
    }
    .estad-sabueso-wrap .estad-time-card .estad-time-icon-btn {
        border: none; padding: 0; background: transparent; cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .estad-sabueso-wrap .estad-time-card .estad-time-icon-btn:hover { transform: scale(1.04); }
    .estad-sabueso-wrap .estad-time-card .estad-time-icon-btn:focus-visible {
        outline: 2px solid rgba(13, 110, 253, 0.45); outline-offset: 2px;
    }
    #modalEstadTiemposHistorico .estad-tiempos-modal-chart-wrap {
        height: 230px;
        position: relative;
    }
    #modalEstadTiemposHistorico .table td { vertical-align: middle; font-size: 0.875rem; }
    /* Gestores: avatar + tabla */
    .estad-sabueso-wrap .estad-avatar {
        width: 1.75rem; height: 1.75rem; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.55rem; font-weight: 800; color: #fff; flex-shrink: 0;
    }
    /* Por gestor: badges Tasa y Cumplimiento más intensos (modo claro y oscuro) */
    .estad-sabueso-wrap #tablaPorGestorLectura .badge.bg-success,
    .estad-sabueso-wrap #tablaPorGestorPV .badge.bg-success {
        background-color: #0d8a3d !important;
        color: #fff !important;
    }
    .estad-sabueso-wrap #tablaPorGestorLectura .badge.bg-warning,
    .estad-sabueso-wrap #tablaPorGestorPV .badge.bg-warning {
        background-color: #e5a800 !important;
        color: #000 !important;
    }
    .estad-sabueso-wrap #tablaPorGestorLectura .badge.bg-danger {
        background-color: #c92a2a !important;
        color: #fff !important;
    }
    .estad-sabueso-wrap #tablaPorGestorPV .badge.bg-secondary {
        background-color: #495057 !important;
        color: #fff !important;
    }
    .estad-sabueso-wrap .estad-pill-gestor .btn.active {
        background-color: var(--bs-success) !important;
        color: #fff !important;
        box-shadow: 0 2px 8px rgba(25, 135, 84, 0.35);
    }
    /* Pagos y visitas: evitar scroll horizontal (tabla cabe en el 100%) */
    #wrapTablaGestorPV {
        overflow-x: hidden !important;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    #tablaPorGestorPV {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0;
    }
    #tablaPorGestorPV thead th,
    #tablaPorGestorPV tbody td {
        padding: 0.5rem 0.28rem;
        font-size: 0.82rem;
    }
    #tablaPorGestorPV thead th {
        white-space: normal;
        line-height: 1.2;
        word-break: break-word;
        font-size: 0.78rem;
        letter-spacing: 0.01em;
    }
    #tablaPorGestorPV thead th .estad-th-tip {
        font-size: 0.62rem;
        margin-left: 0.1rem !important;
        vertical-align: middle;
    }
    #tablaPorGestorPV tbody td:first-child .text-truncate {
        max-width: 100%;
    }
    #tablaPorGestorPV tbody td:not(:first-child) {
        white-space: nowrap;
    }
    .estad-sabueso-wrap .estad-global-tile {
        border-radius: 0.75rem; padding: 0.9rem 1rem;
        border: 1px solid transparent;
    }
    /* Iconos de las tarjetas Global con color en modo claro */
    .estad-sabueso-wrap .estad-global-tile.bg-primary.bg-opacity-10 .fa-eye {
        color: #0d6efd !important;
    }
    .estad-sabueso-wrap .estad-global-tile.bg-success.bg-opacity-10 .fa-paper-plane {
        color: #198754 !important;
    }
    .estad-sabueso-wrap .estad-global-tile.bg-warning.bg-opacity-10 .fa-triangle-exclamation {
        color: #d97706 !important;
    }
    .estad-sabueso-wrap .estad-global-tile[style*="111, 66, 193"] .fa-check {
        color: #6f42c1 !important;
    }
    @media (max-width: 767px) {
        .estad-sabueso-wrap .estad-num-big { font-size: 2rem; }
    }
    /* Tooltips pequeños en encabezados de tablas estadísticas */
    .estad-sabueso-wrap .estad-th-tip {
        cursor: help;
        font-size: 0.65rem;
        opacity: 0.75;
        vertical-align: middle;
    }
    .estad-sabueso-wrap .estad-th-tip:hover { opacity: 1; }
    .tooltip.estad-tooltip-custom .tooltip-inner {
        font-size: 0.72rem;
        max-width: 300px;
        text-align: left;
        line-height: 1.35;
        padding: 0.4rem 0.5rem;
    }
    /* Tabla Sabueso: misma sensación que Por gestor (filas con hover suave) */
    .estad-sabueso-wrap #tablaPorSabueso tbody tr.estad-sabueso-fila:hover {
        background: rgba(var(--bs-success-rgb), 0.06);
    }
    /*
     * Modal detalle (Swal): evitar recorte superior.
     * Por defecto Swal centra en vertical; modales anchos/altos quedan mitad fuera arriba.
     * Contenedor alineado arriba + scroll; el cuerpo del modal hace scroll interno.
     */
    .swal2-container.estad-detalle-swal-container {
        align-items: flex-start !important;
        justify-content: center;
        padding: max(0.5rem, env(safe-area-inset-top, 0px)) 0.5rem 1.25rem !important;
        overflow-y: auto !important;
        -webkit-overflow-scrolling: touch;
        box-sizing: border-box;
    }
    .swal2-popup.estad-detalle-swal {
        padding: 0;
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        margin-top: 0 !important;
        max-height: calc(100vh - max(1rem, env(safe-area-inset-top, 0px)) - 1.25rem) !important;
        display: flex !important;
        flex-direction: column !important;
    }
    .swal2-popup.estad-detalle-swal .swal2-title {
        margin: 0;
        padding: 1rem 1.25rem;
        flex-shrink: 0;
        background: linear-gradient(135deg, rgba(13,110,253,0.08) 0%, rgba(25,135,84,0.06) 100%);
        border-bottom: 1px solid rgba(0,0,0,0.08);
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f5132;
    }
    .swal2-popup.estad-detalle-swal .swal2-html-container {
        margin: 0;
        padding: 0;
        text-align: left;
        flex: 1 1 auto;
        min-height: 0;
        overflow-x: hidden !important;
        overflow-y: auto !important;
        max-height: none !important;
    }
    .swal2-popup.estad-detalle-swal .swal2-actions {
        margin: 0;
        padding: 0.75rem 1.25rem 1rem;
        flex-shrink: 0;
        background: var(--bs-tertiary-bg, #f8f9fa);
        border-top: 1px solid var(--bs-border-color, #dee2e6);
    }
    .estad-modal-detalle-wrap { padding: 0; }
    .estad-modal-detalle-leyenda {
        font-size: 0.75rem;
        line-height: 1.45;
        padding: 0.75rem 1rem;
        background: rgba(13, 110, 253, 0.06);
        border-bottom: 1px solid rgba(0,0,0,0.06);
        color: #495057;
    }
    .estad-modal-detalle-table-wrap {
        /* Caben título Swal + leyenda + toolbar + footer sin desbordar el viewport */
        max-height: min(58vh, calc(100vh - 280px));
        overflow: auto;
        margin: 0;
    }
    .estad-modal-detalle-table-wrap table {
        font-size: 0.8rem;
        margin-bottom: 0;
    }
    .estad-modal-detalle-table-wrap thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: rgba(25,135,84,0.1);
        border-bottom: 2px solid rgba(25,135,84,0.2);
        white-space: nowrap;
        font-weight: 600;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        padding: 0.5rem 0.4rem;
    }
    .estad-modal-detalle-table-wrap tbody td {
        padding: 0.45rem 0.4rem;
        vertical-align: middle;
    }
    .estad-modal-detalle-table-wrap tbody tr:hover {
        background: rgba(25,135,84,0.05);
    }
    .estad-modal-detalle-pager {
        border-top: 1px solid var(--bs-border-color, #dee2e6);
        background: linear-gradient(180deg, rgba(248,249,250,0.95) 0%, rgba(248,249,250,1) 100%);
        padding: 0.55rem 0.85rem 0.65rem;
        margin: 0;
    }
    .estad-modal-detalle-pager .estad-gestor-perpage {
        min-width: 86px;
    }
    .estad-reporte-semanal-toolbar {
        border-top: 1px solid var(--bs-border-color, #dee2e6);
        border-bottom: 1px solid var(--bs-border-color, #dee2e6);
        background: linear-gradient(180deg, rgba(248,249,250,0.9) 0%, rgba(255,255,255,1) 100%);
        padding: 0.6rem 0.85rem;
    }
    .estad-reporte-semanal-toolbar .form-select {
        min-width: 260px;
    }
    @media (max-width: 575.98px) {
        .estad-reporte-semanal-toolbar .form-select {
            min-width: 0 !important;
            width: 100%;
            max-width: none;
        }
        .estad-reporte-semanal-toolbar .d-flex.align-items-center.gap-2:has(#selSemanaReporteGlobal) {
            width: 100%;
            flex-direction: column;
            align-items: stretch !important;
        }
        .estad-reporte-semanal-toolbar .d-flex.align-items-center.gap-2:has(#selSemanaReporteGlobal) label {
            margin-bottom: 0.15rem;
        }
    }
    .estad-reporte-semanal-footer {
        border-top: 1px solid var(--bs-border-color, #dee2e6);
        background: rgba(248,249,250,0.92);
        padding: 0.55rem 0.85rem;
    }
    .estad-modal-th-tip {
        cursor: help;
        opacity: 0.7;
        font-size: 0.7rem;
        margin-left: 2px;
        vertical-align: middle;
    }
    .estad-modal-th-tip:hover { opacity: 1; }
    /* Reporte semanal: modal Bootstrap (sustituye Swal en este flujo) */
    #modalReporteSemanalGlobal .modal-dialog {
        max-width: min(1480px, 99vw);
        width: 100%;
        margin: 0.65rem auto;
        /* Altura estable: evita que el scroll “salte” al pasar de carga → datos */
        max-height: calc(100dvh - 1.25rem);
        overflow: visible;
    }
    @media (max-width: 575.98px) {
        #modalReporteSemanalGlobal .modal-dialog.modal-fullscreen-sm-down {
            margin: 0;
            max-width: 100%;
            max-height: none;
            height: 100%;
        }
        #modalReporteSemanalGlobal .modal-dialog.modal-fullscreen-sm-down .modal-content {
            max-height: 100dvh;
            border-radius: 0;
        }
        #modalReporteSemanalGlobal .modal-dialog.modal-fullscreen-sm-down .estad-reporte-semanal-modal-content .modal-header {
            border-radius: 0;
        }
        #modalReporteSemanalGlobal .modal-dialog.modal-fullscreen-sm-down #modalReporteSemanalGlobalBody {
            min-height: 0;
            flex: 1 1 auto;
            max-height: none;
        }
        #modalReporteSemanalGlobal .modal-dialog.modal-fullscreen-sm-down .estad-modal-detalle-table-wrap {
            max-height: calc(100dvh - 200px);
        }
        #modalReporteSemanalGlobal .modal-dialog.modal-fullscreen-sm-down .table-reporte-semanal-global {
            font-size: 0.74rem;
        }
        #modalReporteSemanalGlobal .modal-dialog.modal-fullscreen-sm-down .table-reporte-semanal-global th,
        #modalReporteSemanalGlobal .modal-dialog.modal-fullscreen-sm-down .table-reporte-semanal-global td {
            padding: 0.35rem 0.25rem;
            vertical-align: middle;
        }
        #modalReporteSemanalGlobal .estad-reporte-semanal-toolbar {
            flex-direction: column;
            align-items: stretch !important;
        }
    }
    #modalReporteSemanalGlobal .modal-content.estad-reporte-semanal-modal-content {
        border-radius: 1rem;
        /* visible: evita que la X del header se recorte con el borde redondeado */
        overflow: visible;
        box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        max-height: calc(100dvh - 1.25rem);
        display: flex;
        flex-direction: column;
    }
    #modalReporteSemanalGlobal .estad-reporte-semanal-modal-content .modal-header {
        flex-shrink: 0;
        position: relative;
        background: linear-gradient(135deg, rgba(13,110,253,0.08) 0%, rgba(25,135,84,0.06) 100%);
        border-bottom: 1px solid rgba(0,0,0,0.08);
        border-radius: 1rem 1rem 0 0;
        padding-top: 0.85rem !important;
        padding-right: 1rem !important;
        padding-bottom: 0.65rem !important;
        padding-left: 1rem !important;
    }
    #modalReporteSemanalGlobal .estad-reporte-semanal-modal-content .modal-header .btn-close {
        padding: 0.5rem;
        margin: -0.2rem -0.15rem -0.2rem auto;
        flex-shrink: 0;
        background-size: 0.7em;
        opacity: 0.92;
    }
    #modalReporteSemanalGlobal .estad-reporte-semanal-modal-content .modal-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f5132;
    }
    [data-bs-theme="dark"] #modalReporteSemanalGlobal .estad-reporte-semanal-modal-content .modal-title,
    .dark-style #modalReporteSemanalGlobal .estad-reporte-semanal-modal-content .modal-title {
        color: var(--bs-heading-color, #e2e8f0);
    }
    #modalReporteSemanalGlobal #modalReporteSemanalGlobalBody {
        flex: 1 1 auto;
        min-height: min(62vh, 520px);
        max-height: calc(100dvh - 7.5rem);
        overflow-x: hidden;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 0 0 1rem 1rem;
    }
    /* Estado “solo cargando”: mismo min-height que con datos → sin estiramiento brusco */
    #modalReporteSemanalGlobal #modalReporteSemanalGlobalBody.estad-rs-body-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: min(62vh, 520px);
    }
    #modalReporteSemanalGlobal .estad-rs-loading-inner {
        padding: 3rem 1.5rem;
    }
    #modalReporteSemanalGlobal .estad-modal-detalle-table-wrap {
        max-height: min(58vh, calc(100dvh - 18rem));
    }
    /* Ilocalizable: Sí/No + autc + select en una sola línea (evita badge “encima” del Auto) */
    #modalReporteSemanalGlobal .td-reporte-ilocalizable {
        vertical-align: middle;
        white-space: nowrap;
        min-width: 10.85rem;
    }
    #modalReporteSemanalGlobal .reporte-rs-ilocal-cell {
        gap: 0.2rem;
    }
    #modalReporteSemanalGlobal .reporte-rs-ilocal-left {
        gap: 0.2rem;
    }
    #modalReporteSemanalGlobal .reporte-rs-ilocal-src {
        display: inline-flex;
        align-items: center;
        gap: 0.22rem;
        flex-shrink: 0;
        line-height: 1 !important;
        padding: 0.08rem 0.4rem 0.08rem 0.35rem;
    }
    #modalReporteSemanalGlobal .reporte-rs-ilocal-src-ico {
        font-size: 0.78rem;
        line-height: 1;
        flex-shrink: 0;
    }
    #modalReporteSemanalGlobal .reporte-rs-ilocalizable-select {
        /* Pastilla al ras: sin tramo vacío entre texto y flecha (ancho ≈ texto + flecha) */
        box-sizing: border-box;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
        appearance: none !important;
        width: 3.48rem;
        min-width: 3.48rem;
        max-width: 3.48rem;
        padding-left: 0.18rem !important;
        padding-right: 0.62rem !important;
        text-align: left;
        background-origin: border-box;
        /* Flecha grande pero cerca del texto */
        background-position: right 0.48rem center;
        background-size: 1.06rem 0.82rem;
    }
    /* Tarjetas de gráfica: alto fijo para Chart.js (evita escalas raras / canvas aplastado) */
    #modalReporteSemanalGlobal .estad-rs-chart-card {
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 0.65rem;
        background: rgba(var(--bs-body-color-rgb, 33, 37, 41), 0.03);
        overflow: hidden;
        cursor: pointer;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    #modalReporteSemanalGlobal .estad-rs-chart-card:hover {
        border-color: rgba(13, 110, 253, 0.45);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
    }
    #modalReporteSemanalGlobal .estad-rs-chart-card:focus {
        outline: 2px solid rgba(13, 110, 253, 0.5);
        outline-offset: 2px;
    }
    #modalReporteSemanalGlobal .estad-rs-chart-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.35rem 0.6rem;
        font-size: 0.72rem;
        font-weight: 600;
        color: var(--bs-secondary-color);
        border-bottom: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,0.08));
        background: rgba(255, 255, 255, 0.6);
    }
    [data-bs-theme="dark"] #modalReporteSemanalGlobal .estad-rs-chart-card-head,
    .dark-style #modalReporteSemanalGlobal .estad-rs-chart-card-head {
        background: rgba(0, 0, 0, 0.2);
    }
    #modalReporteSemanalGlobal .estad-rs-chart-canvas-wrap {
        position: relative;
        width: 100%;
        height: 300px;
    }
    #modalReporteSemanalGlobal .estad-rs-chart-card.estad-rs-chart-tall .estad-rs-chart-canvas-wrap {
        height: 340px;
    }
    #modalReporteSemanalGlobal .estad-rs-chart-caption {
        font-size: 0.7rem;
        text-align: center;
        color: var(--bs-secondary-color);
        padding: 0.35rem 0.5rem 0.5rem;
    }
    /* Modal ampliar gráfica */
    #modalReporteSemanalChartZoom .modal-dialog {
        max-width: min(1100px, 96vw);
    }
    #modalReporteSemanalChartZoom .estad-rs-zoom-canvas-wrap {
        position: relative;
        width: 100%;
        height: min(72vh, 640px);
        min-height: 360px;
    }
    /* Tickets levantados 40% | Por quien levantó 60% (solo lg+) */
    .estad-sabueso-wrap .estad-row-split-40-60 {
        --estad-col-left: 40%;
        --estad-col-right: 60%;
    }
    @media (min-width: 992px) {
        .estad-sabueso-wrap .estad-row-split-40-60 > .estad-col-40 {
            flex: 0 0 var(--estad-col-left) !important;
            max-width: var(--estad-col-left) !important;
            width: var(--estad-col-left);
        }
        .estad-sabueso-wrap .estad-row-split-40-60 > .estad-col-60 {
            flex: 0 0 var(--estad-col-right) !important;
            max-width: var(--estad-col-right) !important;
            width: var(--estad-col-right);
        }
    }
</style>
<?php
$seccionesEstadisticas = isset($seccionesEstadisticas) && is_array($seccionesEstadisticas) ? $seccionesEstadisticas : ['sabueso' => true];
?>
<div class="card mb-4" id="estadisticasSelectorWrap">
    <div class="card">
        <div class="row g-0 align-items-center">
            <div class="col-12 col-md-8">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">HOLA, <?= htmlspecialchars($_SESSION['usuario_nombre'] ?? 'Usuario'); ?></h5>
                    <p class="mb-6">
                        Consulta los tableros de estadísticas por módulo. Indicadores, tiempos y métricas actualizadas para seguimiento y análisis.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card-body ps-md-2 pe-5 text-end">
                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                         class="img-fluid scaleX-n1-rtl"
                         alt="Estadísticas">
                </div>
            </div>
            <div class="row gy-6 mb-6">
                <?php if (!empty($seccionesEstadisticas['sabueso'])): ?>
                <div class="col-lg-4">
                    <div class="card shadow-none bg-label-primary h-100">
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2">Estadísticas Sabueso</h5>
                                    <p class="text-body w-sm-80 app-academy-xl-100">Indicadores, tiempos de dictamen, tickets levantados y detalle por gestor y por Sabueso.</p>
                                </div>
                                <div class="mb-0">
                                    <button type="button" class="btn btn-primary" id="btnEntrarEstadSabueso">VER ESTADÍSTICAS SABUESO</button>
                                </div>
                            </div>
                            <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                <div class="rounded-3 d-flex align-items-center justify-content-center h-100 estad-icono-sabueso" style="min-height: 120px; min-width: 100px;">
                                    <i class="fa-solid fa-magnifying-glass-chart fa-3x scaleX-n1-rtl" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="col-lg-4">
                    <div class="card shadow-none bg-label-primary h-100">
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2">Otras estadísticas</h5>
                                    <p class="text-body app-academy-sm-60 app-academy-xl-100">Módulos de estadísticas adicionales en preparación.</p>
                                </div>
                                <div class="mb-0"><button type="button" class="btn btn-sm btn-primary" disabled>PRÓXIMAMENTE</button></div>
                            </div>
                            <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                <div class="rounded-3 d-flex align-items-center justify-content-center h-100 estad-icono-otras" style="min-height: 120px; min-width: 100px;">
                                    <i class="fa-solid fa-chart-line fa-3x scaleX-n1-rtl" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-none bg-label-primary h-100">
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2">Más tableros</h5>
                                    <p class="text-body app-academy-sm-60 app-academy-xl-100">Nuevos tableros de estadísticas se habilitarán aquí.</p>
                                </div>
                                <div class="mb-0"><button type="button" class="btn btn-sm btn-primary" disabled>PRÓXIMAMENTE</button></div>
                            </div>
                            <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                <div class="rounded-3 d-flex align-items-center justify-content-center h-100 estad-icono-tableros" style="min-height: 120px; min-width: 100px;">
                                    <i class="fa-solid fa-chart-column fa-3x scaleX-n1-rtl" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning d-none mb-3" id="estadisticasSabuesoAlert" role="alert"></div>

<!-- Sin spinner aquí: http.request usa showLoader:false para no duplicar con Swal "Procesando..." -->
<div id="estadisticasSabuesoCargando" class="estad-glass mb-3" style="display: none;">
    <div class="p-4 text-center text-muted">
        <p class="mb-0">Cargando estadísticas…</p>
    </div>
</div>

<div id="estadisticasSabuesoContenido" class="estad-sabueso-wrap" style="display: none;">

    <div class="estad-titulo-bar mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="estad-titulo-texto d-flex align-items-center gap-2">
            <i class="fa-solid fa-chart-pie estad-titulo-icono" aria-hidden="true"></i>
            Estadísticas de Sabueso
        </span>
        <?php if (!empty($estadisticasMostrarBotonVolver)): ?>
        <button type="button" class="btn btn-sm btn-outline-primary" id="btnEstadisticasVolver" title="Ver otras áreas de estadísticas">
            <i class="fa-solid fa-arrow-left me-1"></i>Volver
        </button>
        <?php endif; ?>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 estad-glass estad-kpi-card" title="Tickets en flujo: no cerrados ni eliminados (mismo criterio que al cerrar o borrar un ticket).">
                <div class="card-body position-relative pt-4 pb-3">
                    <div class="position-absolute top-0 end-0 mt-3 me-3 estad-icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div class="text-muted small mb-1">Tickets activos</div>
                    <div class="estad-num-big text-body" id="statTotalActivos">0</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-primary rounded-pill" id="barActivos" style="width: 0%"></div>
                        </div>
                        <span class="small fw-bold text-primary text-nowrap" id="pctActivos">0%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 estad-glass estad-kpi-card" title="De los tickets en flujo, cuántos tienen dictamen enviado al gestor.">
                <div class="card-body position-relative pt-4 pb-3">
                    <div class="position-absolute top-0 end-0 mt-3 me-3 estad-icon-box bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div class="text-muted small mb-1">Con dictamen enviado</div>
                    <div class="estad-num-big text-body" id="statDictamenEnviado">0</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-success rounded-pill" id="barEnviado" style="width: 0%"></div>
                        </div>
                        <span class="small fw-bold text-success text-nowrap" id="pctEnviado">0%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 estad-glass estad-kpi-card" title="De los tickets en flujo con dictamen enviado, cuántos el gestor ya abrió.">
                <div class="card-body position-relative pt-4 pb-3">
                    <div class="position-absolute top-0 end-0 mt-3 me-3 estad-icon-box text-white" style="background: rgba(111, 66, 193, 0.35);">
                        <i class="fa-solid fa-eye"></i>
                    </div>
                    <div class="text-muted small mb-1">Dictamen ya visto</div>
                    <div class="estad-num-big text-body" id="statDictamenVisto">0</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                            <div class="progress-bar rounded-pill" id="barVisto" style="width: 0%; background-color: #6f42c1;"></div>
                        </div>
                        <span class="small fw-bold text-nowrap" id="pctVisto" style="color: #6f42c1;">0%</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card h-100 border-0 estad-glass estad-kpi-card" title="Cantidad histórica de tickets cerrados. La barra muestra 100% como referencia visual (no es un % sobre activos).">
                <div class="card-body position-relative pt-4 pb-3">
                    <div class="position-absolute top-0 end-0 mt-3 me-3 estad-icon-box estad-icon-cerrados" aria-hidden="true">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div class="text-muted small mb-1">Tickets cerrados</div>
                    <div class="estad-num-big text-body" id="statTicketsCerrados">0</div>
                    <div class="d-flex align-items-center gap-2 mt-3">
                        <div class="progress flex-grow-1 rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-warning rounded-pill" id="barCerrados" style="width: 0%"></div>
                        </div>
                        <span class="small fw-bold text-warning text-nowrap" id="pctCerrados">0%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 estad-glass h-100 estad-time-card">
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <button type="button" class="estad-time-icon estad-time-icon-btn" style="background: rgba(245, 158, 11, 0.18); color: #f59e0b;" id="btnHistSabuesoIcon" title="Ver histórico por semana (promedios y variación)">
                        <i class="fa fa-hourglass-half" aria-hidden="true"></i><span class="visually-hidden">Histórico semanal Sabueso</span>
                    </button>
                    <div class="flex-grow-1 min-w-0">
                        <div class="text-muted small">Tiempo hasta enviar dictamen (equipo Sabueso) — <span class="fw-semibold text-warning">semana actual</span></div>
                        <div class="estad-time-value text-warning" id="statTiempoSabuesoValor">—</div>
                        <div class="text-muted small mt-1" id="statTiempoSabuesoSub">Desde primera asignación hasta envío al gestor</div>
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 mt-2 text-warning" id="btnHistSabuesoLink">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Ver semanas anteriores y % vs semana previa
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 estad-glass h-100 estad-time-card">
                <div class="card-body d-flex align-items-center gap-3 py-4">
                    <button type="button" class="estad-time-icon estad-time-icon-btn" style="background: rgba(25, 135, 84, 0.18); color: #198754;" id="btnHistGestorIcon" title="Ver histórico por semana (promedios y variación)">
                        <i class="fa fa-bolt" aria-hidden="true"></i><span class="visually-hidden">Histórico semanal gestor</span>
                    </button>
                    <div class="flex-grow-1 min-w-0">
                        <div class="text-muted small">Tiempo del gestor en abrir el dictamen — <span class="fw-semibold text-success">semana actual</span></div>
                        <div class="estad-time-value text-success" id="statTiempoGestorValor">—</div>
                        <div class="text-muted small mt-1" id="statTiempoGestorSub">Desde envío hasta visto por gestor</div>
                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 mt-2 text-success" id="btnHistGestorLink">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Ver semanas anteriores y % vs semana previa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3 estad-row-split-40-60">
        <!-- Tickets levantados: 40% ancho (variable --estad-col-left) -->
        <div class="col-12 estad-col-40">
            <div class="card border-0 estad-glass h-100 overflow-hidden">
                <div class="card-header border-0 d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 bg-transparent">
                    <div>
                        <div class="fw-semibold"><i class="fa-solid fa-calendar-days me-1 text-primary"></i>Tickets levantados</div>
                        <div class="text-muted small">Días = semana actual (lun–dom). Semanas / meses / año = histórico por fecha de creación (incluye <strong>tickets cerrados</strong>; no cuenta eliminados). El total por día es creación del ticket, no envío de dictamen.</div>
                    </div>
                    <div class="estad-pill-group" id="grpFiltroPeriodo" role="group" aria-label="Agrupar conteos">
                        <button type="button" class="btn active" data-key="por_dia">Días</button>
                        <button type="button" class="btn" data-key="por_semana">Semanas</button>
                        <button type="button" class="btn" data-key="por_mes">Meses</button>
                        <button type="button" class="btn" data-key="por_anio">Año</button>
                    </div>
                </div>
                <div class="px-2 pb-1 small text-muted d-none" id="estadPeriodBreadcrumb"></div>
                <div class="estad-period-list" id="estadPeriodList"></div>
            </div>
        </div>
        <!-- Por quien levantó el ticket: 60% ancho (variable --estad-col-right) -->
        <div class="col-12 estad-col-60">
            <div class="card border-0 estad-glass h-100 overflow-hidden">
                <div class="card-header border-0 py-3 bg-transparent d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <div class="fw-semibold"><i class="fa-solid fa-users me-1 text-success"></i>Por quien levantó el ticket</div>
                    </div>
                    <div class="estad-pill-group estad-pill-gestor flex-wrap" id="grpResumenGestor" role="group">
                        <button type="button" class="btn active" data-tab="global">Global</button>
                        <button type="button" class="btn" data-tab="gestor">Por gestor (levantó)</button>
                        <button type="button" class="btn" data-tab="sabueso">Por Sabueso (dictaminó)</button>
                    </div>
                </div>
                <div class="card-body pt-0" id="panelResumenGlobal">
                    <!-- Guía solo en vista Global -->
                    <div class="w-100 mb-3 p-2 rounded-2 border" style="background: rgba(13,110,253,0.06); border-color: rgba(13,110,253,0.15) !important; font-size: 0.8rem;">
                        <strong class="text-primary"><i class="fa-solid fa-compass me-1"></i>Cómo leer esto (rápido)</strong>
                        <ul class="mb-0 mt-1 ps-3">
                            <li><strong>Sin leer / Tasa</strong> en <em>tarjetas</em> abajo = vista <strong>Global</strong> (todos los tickets).</li>
                            <li><strong>Sin leer / Tasa por persona</strong> = pulse el botón <strong>Por gestor (levantó)</strong> y verá una <em>tabla</em> con una fila por gestor (columnas con esos nombres).</li>
                            <li><strong>Quién abrió y quién no</strong> = en esa tabla, haga <strong>clic en el nombre</strong>; en el popup, columna <strong>¿Abrió?</strong> (Sí/No).</li>
                            <li><strong>Resultado visita / pago</strong> = en el mismo popup, columnas <strong>Resultado DS</strong> y <strong>% efect.</strong> (si sale «Sin dictamen sistema», aún no se ha generado el dictamen automático para ese ticket).</li>
                        </ul>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="estad-global-tile bg-primary bg-opacity-10">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-eye me-1"></i>Lectura prom.</div>
                                <div class="fw-bold text-primary" id="tileTiempoLectura">—</div>
                                <div class="text-muted" style="font-size: 0.7rem;" id="tileTiempoLecturaSub">n=0</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="estad-global-tile bg-success bg-opacity-10">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-paper-plane me-1"></i>Envío prom.</div>
                                <div class="fw-bold text-success" id="tileTiempoEnvio">—</div>
                                <div class="text-muted" style="font-size: 0.7rem;" id="tileTiempoEnvioSub">n=0</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="estad-global-tile bg-warning bg-opacity-10">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Sin leer</div>
                                <div class="fw-bold text-warning" id="tileSinLeer">0</div>
                                <div class="text-muted" style="font-size: 0.7rem;">Enviado, no visto</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="estad-global-tile" style="background: rgba(111, 66, 193, 0.1);">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-check me-1"></i>Tasa lectura</div>
                                <div class="fw-bold" style="color: #6f42c1;" id="tileTasa">—</div>
                                <div class="text-muted" style="font-size: 0.7rem;" id="tileTasaSub">—</div>
                            </div>
                        </div>
                        <div class="col-12 mt-2">
                            <div class="rounded-2 p-2" style="background: rgba(25, 135, 84, 0.06); border: 1px solid rgba(25, 135, 84, 0.12);">
                                <div class="text-muted small mb-1"><i class="fa-solid fa-clipboard-check me-1"></i>Panorama cumplimiento (dictamen sistema)</div>
                                <div class="d-flex flex-wrap align-items-center gap-3 small">
                                    <span><strong id="tileCumplimientoPct">—</strong> % efect. prom.</span>
                                    <span class="text-muted" id="tileCumplimientoMuestra">Muestra: 0</span>
                                    <span class="text-muted" id="tileCumplimientoPorResultado" style="font-size:0.7rem">—</span>
                                </div>
                                <div class="text-muted mt-1" style="font-size:0.65rem" id="tileCumplimientoLeyenda">—</div>
                            </div>
                            <div class="d-flex justify-content-end mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnReporteSemanalGlobal">
                                    <i class="fa-solid fa-calendar-week me-1"></i>Reporte semanal
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-0 d-none" id="panelResumenGestor">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                        <span class="text-muted small mb-0">Vista de la tabla por gestor:</span>
                        <div class="estad-pill-group estad-pill-gestor" id="grpVistaGestorTabla" role="group" aria-label="Vista tabla por gestor">
                            <button type="button" class="btn active" data-vista-gestor="lectura" title="Tiempos de lectura, sin leer y tasa de apertura">Lectura y tasa</button>
                            <button type="button" class="btn" data-vista-gestor="pagos_visitas" title="Pagos en ventana, visitas de campo y cumplimiento DS">Pagos y visitas</button>
                        </div>
                    </div>
                    <!-- Vista lectura: sin columna Cumplimiento -->
                    <div class="table-responsive rounded-2 border gestor-tabla-wrap" id="wrapTablaGestorLectura" style="max-height: 320px; border-color: rgba(var(--bs-success-rgb), 0.15) !important;">
                        <table class="table table-sm table-hover mb-0 align-middle" id="tablaPorGestorLectura">
                            <thead class="small text-uppercase" style="font-size: 0.6rem; background: rgba(var(--bs-success-rgb), 0.07);">
                                <tr class="text-muted">
                                    <th class="py-2 ps-3">
                                        Levantó
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Gestor que creó el ticket. Clic en fila: detalle por ticket."></i>
                                    </th>
                                    <th class="text-center py-2">Tickets</th>
                                    <th class="text-center py-2">T. lectura</th>
                                    <th class="text-center py-2">T.envío</th>
                                    <th class="text-center py-2">Sin leer</th>
                                    <th class="text-center py-2 pe-3">Tasa</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPorGestorLectura"></tbody>
                        </table>
                    </div>
                    <!-- Vista pagos/visitas: Cumplimiento aquí -->
                    <div class="rounded-2 border gestor-tabla-wrap d-none" id="wrapTablaGestorPV" style="max-height: 320px; border-color: rgba(var(--bs-success-rgb), 0.15) !important;">
                        <table class="table table-sm table-hover mb-0 align-middle" id="tablaPorGestorPV">
                            <thead class="small text-uppercase" style="background: rgba(var(--bs-success-rgb), 0.07);">
                                <tr class="text-muted">
                                    <th class="py-2 ps-3">
                                        Levantó
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Gestor que creó el ticket. Clic en fila: por ticket pago, visita y cumplimiento."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Tickets
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="Total con dictamen enviado en el detalle."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Pagaron
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="Tickets donde hubo pago en estado de cuenta en la ventana que evalúa el dictamen sistema (Sí en columna Pago del detalle)."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Visitaron
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="Tickets con visita de campo registrada (GPS/direcciones) según dictamen sistema."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Prórroga
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="Tickets a los que se otorgó prórroga (+12 h) al menos una vez según dictamen sistema."></i>
                                    </th>
                                    <th class="text-center py-2 pe-3">
                                        Cumplimiento
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" title="% efectividad promedio de los tickets con dictamen sistema evaluado."></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPorGestorPV"></tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-0 mt-2" id="leyendaGestorTabla"><i class="fa fa-search me-1"></i>Clic en una fila: en <strong>Pagos y visitas</strong> verá por ticket Pagaron, Visitaron, si tuvo <strong>prórroga</strong>, cumplimiento y % efect.</p>
                </div>
                <!-- Sabueso: hermano de panelResumenGestor (no anidado) para que no quede oculto por d-none del padre -->
                <div class="card-body pt-0 d-none" id="panelResumenSabueso">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <span class="text-muted small mb-0">Dictámenes enviados: <strong>hoy</strong> · <strong>semana (lun→hoy)</strong> · <strong>mes</strong> · <strong>año</strong></span>
                        <div class="estad-pill-group estad-pill-gestor" id="grpFiltroPeriodoSabueso" role="group">
                            <button type="button" class="btn active" data-key="por_dia">Días</button>
                            <button type="button" class="btn" data-key="por_semana">Semanas</button>
                            <button type="button" class="btn" data-key="por_mes">Meses</button>
                            <button type="button" class="btn" data-key="por_anio">Año</button>
                        </div>
                    </div>
                    <div class="table-responsive rounded-2 border" style="max-height: 320px; border-color: rgba(var(--bs-success-rgb), 0.15) !important;">
                        <table class="table table-sm table-hover mb-0 align-middle" id="tablaPorSabueso">
                            <thead class="small text-uppercase" style="font-size: 0.6rem; background: rgba(var(--bs-success-rgb), 0.07);">
                                <tr class="text-muted">
                                    <th class="py-2 ps-3">
                                        Sabueso (autor)
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Quien figura como autor al enviar el dictamen al gestor (dictamen.id_persona). No es solo quien tenía el ticket asignado al final."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Dictaminó
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Número de tickets en los que esta persona envió el dictamen (un conteo por ticket)."></i>
                                    </th>
                                    <th class="text-center py-2">
                                        Espera 1ª asign.
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="En esos tickets: tiempo promedio desde que se creó el ticket hasta la primera asignación en Sabueso (cualquier persona). NO es el tiempo que esta persona tardó en asignar a otro (no guardamos quién hizo clic en asignar)."></i>
                                    </th>
                                    <th class="text-center py-2 pe-3">
                                        Asignado → envío
                                        <i class="fa fa-question-circle estad-th-tip ms-1" data-bs-toggle="tooltip" data-bs-custom-class="estad-tooltip-custom" data-bs-placement="top" title="Solo cuando consta que el ticket le quedó asignado a él antes del envío: tiempo desde esa asignación hasta enviar el dictamen. Si no estaba asignado a él, sale —."></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tbodyPorSabueso"></tbody>
                        </table>
                    </div>
                    <p class="text-muted small mb-1 mt-2"><i class="fa fa-search me-1"></i>Clic en una fila: detalle por ticket (cola, asignado→envío, si el gestor vio).</p>
                    <p class="text-muted small mb-0" style="font-size: 0.7rem;"><strong>ID 797 / Sandra:</strong> «Espera 1ª asign.» = tiempo del <em>ticket</em> hasta su primera asignación. «Asignado → envío» = tiempo de esa persona con el ticket asignado hasta enviar.</p>
                    <div class="alert alert-light border mt-2 mb-0 py-2 px-3" style="font-size: 0.72rem;">
                        <strong>— en tiempos:</strong> no hay dato para calcular (ej. no estaba asignado a esa persona antes de enviar).<br>
                        <strong>«Sin dictamen sistema» en el popup del gestor:</strong> ese ticket aún no tiene generado el dictamen automático; se genera en Panel Admin con el botón del dictamen del sistema.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs operativos: debajo del bloque principal, antes del detalle; estilo glass -->
    <div class="card border-0 estad-glass mb-3 overflow-hidden">
        <div class="card-header border-0 py-3 bg-transparent">
            <div class="fw-semibold"><i class="fa fa-tachometer me-2 text-primary"></i>Indicadores operativos</div>
            <div class="text-muted small">Tiempos de cola, reasignaciones y cumplimiento</div>
        </div>
        <div class="card-body pt-0">
            <div class="row g-3">
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(var(--bs-primary-rgb), 0.08); border: 1px solid rgba(var(--bs-primary-rgb), 0.12);">
                        <div class="text-muted small mb-1">Creación → 1ª asignación</div>
                        <div class="fs-5 fw-bold text-primary" id="kpiPrimeraAsignacion">—</div>
                        <div class="text-muted" style="font-size:0.7rem" id="kpiPrimeraAsignacionSub">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(13, 110, 253, 0.06); border: 1px solid rgba(13, 110, 253, 0.1);">
                        <div class="text-muted small mb-1">Reasignaciones antes de enviar</div>
                        <div class="fs-5 fw-bold text-body" id="kpiReasignaciones">—</div>
                        <div class="text-muted" style="font-size:0.65rem">Promedio por ticket</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(255, 193, 7, 0.12); border: 1px solid rgba(255, 193, 7, 0.25);">
                        <div class="text-muted small mb-1">Borradores sin enviar</div>
                        <div class="fs-5 fw-bold text-warning" id="kpiBorradores">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(25, 135, 84, 0.1); border: 1px solid rgba(25, 135, 84, 0.2);">
                        <div class="text-muted small mb-1">Visto dentro de 12 h</div>
                        <div class="fs-5 fw-bold text-success" id="kpi12h">—</div>
                        <div class="text-muted" style="font-size:0.7rem" id="kpi12hSub">—</div>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl">
                    <div class="rounded-3 p-3 h-100" style="background: rgba(220, 53, 69, 0.08); border: 1px solid rgba(220, 53, 69, 0.15);">
                        <div class="text-muted small mb-1">Cola lenta (&gt;24 h)</div>
                        <div class="fs-5 fw-bold text-danger" id="kpiCola24h">—</div>
                        <div class="text-muted" style="font-size:0.65rem">Sin asignar o 1ª asignación tardía</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 estad-glass">
        <div class="card-header border-0 py-3 bg-transparent d-flex flex-wrap align-items-start justify-content-between gap-2">
            <div>
                <div class="fw-semibold"><i class="fa-solid fa-list me-2"></i>Detalle reciente (dictamen enviado)</div>
                <div class="text-muted small">Folio, quién levantó (ID del gestor debajo), asignado a Sabueso, fechas; % efectividad y medidas = cumplimiento dictamen sistema</div>
            </div>
            <button type="button" class="btn btn-primary btn-sm btn-descargar-estad-detalle" title="Descargar en Excel (hasta 2000 registros)">
                <i class="fa fa-download me-1"></i>Descargar Excel
            </button>
        </div>
        <div class="card-datatable table-responsive px-2 pb-2">
            <table class="table table-hover mb-0 dt-responsive border-top" id="tablaDetalleTimings" style="width:100%">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Folio</th>
                        <th title="Gestor que creó el ticket; el ID (persona) va debajo con icono">Quién levantó</th>
                        <th title="Asignado en Sabueso al momento del dictamen (no es el gestor que levantó)">Asignado a</th>
                        <th>Levantado</th>
                        <th>Dictamen enviado</th>
                        <th>Visto por gestor</th>
                        <th class="text-nowrap" title="% según resultado del dictamen = reglas de cumplimiento (visita/pago/direcciones)">% efectividad</th>
                        <th class="pe-3" title="Acciones sugeridas según cumplimiento">Medidas preventivas</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

</div>

<!-- Histórico semanal: tiempos dictamen (Sabueso / gestor), tabla + gráfica -->
<div class="modal fade" id="modalEstadTiemposHistorico" tabindex="-1" aria-labelledby="modalEstadTiemposHistoricoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-2 py-md-3">
                <div>
                    <h5 class="modal-title mb-0" id="modalEstadTiemposHistoricoLabel">
                        <i class="fa-solid fa-chart-line me-2 text-primary"></i>Tiempos dictamen · histórico semanal
                    </h5>
                    <p class="text-muted small mb-0 mt-1" id="modalEstadTiemposHistoricoSub"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="modalEstadTiemposHistoricoSinDatos" class="alert alert-light border small d-none mb-0" role="status"></div>
                <div id="modalEstadTiemposHistoricoContenido">
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered align-middle mb-0" id="modalEstadTiemposHistoricoTable">
                            <thead class="table-light" id="modalEstadTiemposHistoricoThead"></thead>
                            <tbody id="modalEstadTiemposHistoricoTbody"></tbody>
                        </table>
                    </div>
                    <p class="small text-muted mb-3" id="modalEstadTiemposIntroGraficas"></p>
                    <div class="estad-tiempos-modal-graf sabueso mb-4" id="estadTiemposModalBloqueGrafSab">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill" style="background:#d97706;color:#fff;">Sabueso</span>
                            <span class="small fw-semibold">Tiempo hasta enviar dictamen (equipo Sabueso)</span>
                            <span class="text-muted small">Semana más reciente e histórico por semana de envío</span>
                        </div>
                        <div id="estadTiemposHistoricoChartEmptySab" class="alert alert-light border small py-2 mb-0 d-none" role="status"></div>
                        <div class="estad-tiempos-modal-chart-wrap border rounded bg-body-secondary bg-opacity-10" id="estadTiemposHistoricoWrapSab">
                            <canvas id="estadTiemposHistoricoChartSabueso" aria-label="Histórico Sabueso minutos por semana"></canvas>
                        </div>
                    </div>
                    <div class="estad-tiempos-modal-graf gestor" id="estadTiemposModalBloqueGrafGest">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge rounded-pill bg-success">Gestor</span>
                            <span class="small fw-semibold">Tiempo del gestor en abrir el dictamen</span>
                            <span class="text-muted small">Semana más reciente e histórico por semana de envío</span>
                        </div>
                        <div id="estadTiemposHistoricoChartEmptyGest" class="alert alert-light border small py-2 mb-0 d-none" role="status"></div>
                        <div class="estad-tiempos-modal-chart-wrap border rounded bg-body-secondary bg-opacity-10" id="estadTiemposHistoricoWrapGest">
                            <canvas id="estadTiemposHistoricoChartGestor" aria-label="Histórico gestor minutos por semana"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reporte semanal: modal Bootstrap (contenido lo inyecta abrirReporteSemanalGlobal en Sabueso.php) -->
<div class="modal fade" id="modalReporteSemanalGlobal" tabindex="-1" aria-labelledby="modalReporteSemanalGlobalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-sm-down modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content estad-reporte-semanal-modal-content">
            <div class="modal-header">
                <h5 class="modal-title mb-0" id="modalReporteSemanalGlobalLabel">
                    <i class="fa-solid fa-calendar-week me-2 text-primary"></i>Reporte semanal · Por quien levantó
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" id="modalReporteSemanalGlobalBody"></div>
        </div>
    </div>
</div>

<!-- Ampliar una gráfica del reporte semanal (clic en tarjeta) -->
<div class="modal fade" id="modalReporteSemanalChartZoom" tabindex="-1" aria-labelledby="modalReporteSemanalChartZoomLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title fs-6 mb-0" id="modalReporteSemanalChartZoomLabel">Gráfica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body pt-2 pb-3">
                <p class="text-muted small mb-2"><i class="fa-solid fa-expand me-1"></i>Vista ampliada. Cierre con la X o haciendo clic fuera.</p>
                <div class="estad-rs-zoom-canvas-wrap border rounded bg-body-secondary bg-opacity-10">
                    <canvas id="canvasReporteSemanalChartZoom"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
