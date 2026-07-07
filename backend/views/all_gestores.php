<style>
    #loaderTabla {
        text-align: center;
        padding: 40px;
    }

    #modulos-container table {
        width: 100%;
        table-layout: fixed;
    }

    #modulos-container td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #modulos-container td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #modulos-container small {
        white-space: normal;
        word-break: break-word;
    }
    #modulos-container table.border-top {
        border-top: none !important;
    }

    #modulos-container tbody tr:last-child td {
        border-bottom: none !important;
    }

    /* Módulos del sistema (modal perfil): bloques al estilo menú lateral */
    .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo {
        background: #fff;
    }
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo {
        border-color: #d8e0ea !important;
        box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08) !important;
        transition: box-shadow .18s ease, border-color .18s ease, transform .18s ease;
    }
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo.is-collapsed {
        border-color: #cfd8e3 !important;
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08) !important;
    }
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo.is-collapsed .modal-perfil-modulo-grupo-header {
        border-bottom: 0 !important;
        border-radius: .55rem;
    }
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo.is-open .modal-perfil-modulo-grupo-header {
        border-radius: .55rem .55rem 0 0;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo {
        background: rgba(30, 41, 59, 0.95) !important;
        border-color: #e2e8f0 !important;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo-header {
        background: rgba(51, 65, 85, 0.5) !important;
        border-bottom-color: #e2e8f0 !important;
        color: #f1f5f9 !important;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo-header .flex-grow-1.min-w-0 {
        color: #f1f5f9 !important;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila {
        border-bottom-color: #334155 !important;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila:hover {
        background-color: rgba(51, 65, 85, 0.4) !important;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td.fw-medium span {
        color: #f1f5f9 !important;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modulo-icon-box {
        background: rgba(129, 140, 248, 0.15) !important;
        border-color: rgba(129, 140, 248, 0.35) !important;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modulo-icon-box i {
        color: #93c5fd !important;
    }
    #modalEditPerfil .modal-perfil-modulo-grupo-header .modal-perfil-modulo-master-wrap {
        text-align: right;
    }
    #modalEditPerfil .modal-perfil-modulo-grupo-header .modal-perfil-modulo-master-label {
        line-height: 1.2;
    }
    #modalEditPerfil .modal-perfil-modulo-grupo-header .modal-perfil-modulo-master-cb {
        flex-shrink: 0;
        width: 1.1rem;
        height: 1.1rem;
        margin-top: 0;
        cursor: pointer;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo-header .modal-perfil-modulo-master-cb {
        filter: brightness(1.15);
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo-header .modal-perfil-modulo-master-label {
        color: #cbd5e1 !important;
    }

    /* Puestos: cabecera de tarjeta colapsable (misma rejilla 2 columnas que módulos) */
    #modalEditPerfil .modal-perfil-puesto-card-toggle:focus {
        outline: none;
    }
    #modalEditPerfil .modal-perfil-puesto-card-toggle:focus-visible {
        box-shadow: 0 0 0 2px #fff, 0 0 0 4px rgba(26, 82, 168, 0.45);
    }
    body.dark-mode #modalEditPerfil .modal-perfil-puesto-card-toggle:focus-visible {
        box-shadow: 0 0 0 2px #1e293b, 0 0 0 4px rgba(147, 197, 253, 0.4);
    }
    #modalEditPerfil .modal-perfil-puesto-card-collapse {
        background: #fff;
    }
    body.dark-mode #modalEditPerfil .modal-perfil-puesto-card-collapse {
        background: rgba(30, 41, 59, 0.95) !important;
    }

    /* Módulos del sistema (modal): máximo 2 bloques por fila; el resto baja a la siguiente fila */
    #modalEditPerfil .modal-perfil-modulos-agrupados {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-start;
    }
    /* Exactamente 2 bloques por fila (gap 1rem entre columnas) */
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo {
        flex: 1 1 calc(50% - 0.5rem);
        max-width: calc(50% - 0.5rem);
        min-width: 0;
        margin-bottom: 0 !important;
    }

    #modalEditPerfil #modulos-container td:first-child,
    #modalEditPerfil #modulos-container small,
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td,
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila span,
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila small {
        word-break: normal !important;
        overflow-wrap: anywhere;
    }

    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td:first-child {
        min-width: 0;
    }

    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td:last-child {
        width: 132px;
    }

    /* Permisos especiales (modal): dos columnas en escritorio */
    #modalEditPerfil .modal-perfil-permisos-outer {
        overflow-y: visible;
    }
    #modalEditPerfil .modal-perfil-permisos-col-card {
        border-radius: 0.5rem !important;
        border-color: #dee2e6 !important;
        background: #fff;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
    }
    #modalEditPerfil .modal-perfil-permisos-col-card:hover {
        box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08) !important;
        border-color: #ced4da !important;
    }
    #modalEditPerfil .modal-perfil-permisos-table tbody tr:last-child td {
        border-bottom: none !important;
    }
    /* Texto un poco más compacto y aire respecto al borde (table-flush-spacing anula padding lateral) */
    #modalEditPerfil .modal-perfil-permisos-table {
        font-size: 0.9rem;
    }
    #modalEditPerfil .modal-perfil-permisos-table tbody tr > td:first-child {
        padding-left: 1.25rem !important;
        padding-right: 0.75rem !important;
    }
    #modalEditPerfil .modal-perfil-permisos-table tbody tr > td:last-child {
        padding-right: 1.25rem !important;
        padding-left: 0.5rem !important;
    }
    #modalEditPerfil .modal-perfil-permisos-table td {
        vertical-align: middle;
        padding-top: 0.65rem;
        padding-bottom: 0.65rem;
    }
    #modalEditPerfil .modal-perfil-permisos-table small.text-muted.d-block {
        font-size: 0.8125rem;
        line-height: 1.35;
    }
    body.dark-mode #modalEditPerfil .modal-perfil-permisos-col-card {
        background: rgba(30, 41, 59, 0.72) !important;
        border-color: #475569 !important;
    }
    body.dark-mode #modalEditPerfil .modal-perfil-permisos-col-card:hover {
        border-color: #64748b !important;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25) !important;
    }

    /* Sesión remota: contenido alineado a la izquierda */
    #modalEditPerfil .modal-tab-sesion-remota-card {
        border-radius: 0.5rem;
    }

    /* Modal permisos: pestañas fijas arriba; solo el bloque de contenido hace scroll */
    #modalEditPerfil.modal .modal-perfil-gestor-dialog {
        max-height: min(92vh, 960px);
        margin: 1rem auto;
    }
    #modalEditPerfil.modal .modal-perfil-gestor-dialog .modal-content {
        max-height: inherit;
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }
    #modalEditPerfil.modal .modal-perfil-gestor-dialog .modal-header {
        flex-shrink: 0;
    }
    #modalEditPerfil.modal .modal-perfil-gestor-dialog .modal-perfil-gestor-body {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    #modalEditPerfil.modal .modal-perfil-gestor-dialog .modal-perfil-gestor-tabs {
        flex-shrink: 0;
        position: relative;
        z-index: 5;
        background: #fff;
    }
    #modalEditPerfil.modal .modal-perfil-gestor-dialog .modal-perfil-gestor-tab-content {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }
    body.dark-mode #modalEditPerfil.modal .modal-perfil-gestor-dialog .modal-perfil-gestor-tabs {
        background: #252525 !important;
    }

    @media (max-width: 991.98px) {
        #modalEditPerfil.modal .modal-perfil-gestor-dialog {
            width: calc(100vw - 1.5rem);
            max-width: calc(100vw - 1.5rem);
            margin: 0.75rem auto;
            max-height: calc(100vh - 1.5rem);
        }

        #modalEditPerfil.modal .modal-perfil-gestor-dialog .modal-content {
            max-height: calc(100vh - 1.5rem);
        }

        #modalEditPerfil .modal-perfil-gestor-tabs {
            display: flex !important;
            flex-wrap: nowrap !important;
            overflow-x: auto !important;
            overflow-y: hidden !important;
            gap: 0.25rem;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        #modalEditPerfil .modal-perfil-gestor-tabs .nav-item {
            flex: 0 0 auto;
        }

        #modalEditPerfil .modal-perfil-gestor-tabs .nav-link {
            white-space: nowrap !important;
            padding-left: 0.85rem !important;
            padding-right: 0.85rem !important;
        }

        #modalEditPerfil .modal-perfil-gestor-tab-content {
            padding: 1rem !important;
        }

        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo {
            flex: 1 1 100% !important;
            max-width: 100% !important;
        }

        #modalEditPerfil #tabModulos > .d-flex,
        #modalEditPerfil #tabPermisosEspeciales > .d-flex {
            align-items: flex-start !important;
        }
    }

    @media (max-width: 767.98px) {
        #modalEditPerfil .modal-header {
            padding: 0.9rem 1rem !important;
        }

        #modalEditPerfil .modal-header .d-flex {
            min-width: 0;
        }

        #modalEditPerfil .modal-title {
            font-size: 1rem;
            line-height: 1.25;
        }

        #modalEditPerfil #modalEditPerfil_subtitle {
            line-height: 1.25;
        }

        #modalEditPerfil #tabPuestos .d-flex.gap-3.flex-wrap.flex-lg-nowrap {
            flex-direction: column;
        }

        #modalEditPerfil #tabPuestos aside,
        #modalEditPerfil #tabPuestos section {
            width: 100% !important;
            max-width: 100% !important;
        }

        #modalEditPerfil .modal-perfil-modulo-grupo-header {
            align-items: flex-start !important;
        }

        #modalEditPerfil .modal-perfil-modulo-grupo-header .modal-perfil-modulo-master-wrap {
            width: 100%;
            justify-content: flex-end;
        }

        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td:first-child {
            padding-left: 0.75rem !important;
            padding-right: 0.5rem !important;
        }

        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td:last-child {
            width: 118px !important;
            padding-left: 0.35rem !important;
            padding-right: 0.75rem !important;
        }

        #modalEditPerfil .modal-perfil-modulos-agrupados .modulo-icon-box {
            width: 36px !important;
            height: 36px !important;
        }
    }

    @media (max-width: 575.98px) {
        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila,
        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila tbody,
        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td {
            display: block;
            width: 100% !important;
        }

        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td:first-child {
            padding: 0.75rem 0.75rem 0.4rem !important;
        }

        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td:last-child {
            padding: 0 0.75rem 0.75rem !important;
            text-align: left !important;
        }

        #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-fila td:last-child > .form-check {
            justify-content: flex-start !important;
        }
    }

    #offcanvasEditPerfil .tab-content:not(.doc-example-content) {
        padding: .25rem 0;
    }

    /* SweetAlert2 por encima de #modalEditPerfil / #modalKpiDesglose (z-index 99999) y #customModalOverlay (99998).
       La clase .swal-sobre-modal-perfil quedó como alias por si algún Swal usa customClass.container. */
    body .swal2-container,
    .swal2-container.swal-sobre-modal-perfil {
        z-index: 100010 !important;
    }

    .swal-force-logout-popup {
        width: min(520px, calc(100vw - 2rem)) !important;
        padding: 1.5rem 1.75rem 1.65rem !important;
        border-radius: 0.75rem !important;
    }

    .swal-force-logout-icon {
        width: 4.25rem !important;
        height: 4.25rem !important;
        margin: 0.25rem auto 0.75rem !important;
    }

    .swal-force-logout-icon .swal2-icon-content {
        font-size: 3rem !important;
    }

    .swal-force-logout-title {
        font-size: 1.85rem !important;
        line-height: 1.18 !important;
        color: #334155 !important;
        padding: 0 !important;
        margin: 0.25rem 0 1rem !important;
    }

    .swal-force-logout-html {
        margin: 0 auto !important;
        color: #64748b !important;
        font-size: 1rem !important;
        line-height: 1.45 !important;
    }

    /* Estilos para el Modal de Permisos */
    #modalEditPerfil .modal-content {
        border-radius: 1rem;
        overflow: hidden;
    }

    #modalEditPerfil .modal-header {
        background: #fff !important;
        border-bottom: 1px solid #e9ecef !important;
    }
    #modalEditPerfilLabel,
    #modalEditPerfilLabel i {
        color: #24364b !important;
    }
    #modalEditPerfil_subtitle {
        color: #64748b !important;
    }

    /* DARK MODE: Modal de Permisos */
    body.dark-mode #modalEditPerfil .modal-content {
      background: rgba(30, 41, 59, 0.92) !important;
      color: #e0e0e0 !important;
    }
    body.dark-mode #modalEditPerfil .nav-tabs-custom {
      background-color: #252525 !important;
      border-bottom-color: #3a3a3a !important;
    }
    body.dark-mode #modalEditPerfil .nav-tabs-custom .nav-link {
      color: #b8b8c1 !important;
      background-color: transparent !important;
    }
    body.dark-mode #modalEditPerfil .nav-tabs-custom .nav-link.active {
      background-color: #303030 !important;
      color: #ffffff !important;
      border-bottom-color: #6ea8fe !important;
    }
    body.dark-mode #modalEditPerfil .tab-content {
      background-color: #252525 !important;
      color: #e0e0e0 !important;
    }

    #modalEditPerfil .btn-outline-secondary:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 8px rgba(108, 117, 125, 0.25) !important;
    }

    #modalEditPerfil .nav-tabs-custom .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #6c757d;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    /* Modal Reingreso: Liquid Glass + responsive */
    #modalReingreso .modal-reingreso-glass {
        background: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border-radius: 1rem;
        border: 1px solid rgba(0, 0, 0, 0.08);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
    }
    #modalReingreso .modal-header {
        background: rgba(0, 0, 0, 0.03) !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 1rem 1rem 0 0;
    }
    #modalReingreso .modal-footer {
        background: rgba(0, 0, 0, 0.03) !important;
        border-top: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 0 0 1rem 1rem;
    }
    @media (max-width: 575.98px) {
        #modalReingreso .modal-reingreso-dialog {
            margin: 0.5rem;
            max-width: calc(100vw - 1rem);
        }
        #modalReingreso .modal-body {
            padding: 1rem;
            max-height: 60vh;
        }
        #modalReingreso .modal-footer {
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
        }
        #modalReingreso .modal-footer .btn {
            flex: 1 1 auto;
            min-width: 0;
        }
    }
    body.dark-mode #modalReingreso .modal-reingreso-glass {
        background: rgba(30, 41, 59, 0.92) !important;
        border-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
    }
    body.dark-mode #modalReingreso .modal-header {
        background: rgba(51, 65, 85, 0.6) !important;
        border-bottom-color: rgba(255, 255, 255, 0.08);
    }
    body.dark-mode #modalReingreso .modal-footer {
        background: rgba(51, 65, 85, 0.6) !important;
        border-top-color: rgba(255, 255, 255, 0.08);
    }

    #modalBajas .modal-bajas-dialog {
        max-width: min(1260px, calc(100vw - 2rem));
    }
    #modalBajas .modal-bajas-shell {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 420px;
        gap: 0.85rem;
        align-items: stretch;
        background: transparent;
        border: 0;
        box-shadow: none;
        overflow: visible;
    }
    #modalBajas .baja-main-panel,
    #modalBajas .baja-side-panel {
        background: #fff;
        border: 1px solid rgba(15, 23, 42, 0.08);
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.18);
    }
    #modalBajas .baja-main-panel {
        display: flex;
        flex-direction: column;
        min-width: 0;
        border-radius: 0.85rem;
        overflow: hidden;
    }
    #modalBajas .baja-side-panel {
        border-radius: 0.85rem;
        overflow: hidden;
        min-height: 100%;
    }
    #modalBajas .baja-subordinados-box {
        display: flex;
        flex-direction: column;
        height: 100%;
        border: 0;
        border-radius: 0;
        background: #f8fafc;
        padding: 1rem;
    }
    #modalBajas #bajaSustitutoWrap,
    #modalBajas #bajaSustitutoWrap .d-grid,
    #modalBajas #bajaSustitutoId,
    #modalBajas #bajaAplicarJefeSeleccionados,
    #modalBajas #bajaSustitutoWrap .select2,
    #modalBajas #bajaSustitutoWrap .select2-container {
        max-width: 100% !important;
        min-width: 0 !important;
    }
    #modalBajas #bajaSustitutoId {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    #modalBajas #bajaSustitutoWrap .select2-selection__rendered {
        display: block;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        padding-right: 1.75rem;
    }
    #modalBajas #bajaSustitutoWrap .select2-selection {
        overflow: hidden;
    }
    @media (max-width: 575.98px) {
        #modalBajas .modal-bajas-dialog {
            max-width: calc(100vw - 1rem);
        }
        #modalBajas .modal-bajas-shell {
            grid-template-columns: 1fr;
        }
    }
    @media (min-width: 576px) and (max-width: 991.98px) {
        #modalBajas .modal-bajas-shell {
            grid-template-columns: 1fr;
        }
    }
    body.dark-mode #modalBajas .baja-main-panel,
    body.dark-mode #modalBajas .baja-side-panel {
        background: rgba(30, 41, 59, 0.95) !important;
        border-color: #475569 !important;
    }
    body.dark-mode #modalBajas .baja-subordinados-box {
        background: rgba(30, 41, 59, 0.72) !important;
        border-color: #475569 !important;
    }

    #modalEditPerfil .nav-tabs-custom .nav-link:hover {
        color: #495057;
        background-color: #f8f9fa;
    }

    #modalEditPerfil .nav-tabs-custom .nav-link.active {
        color: #212529;
        border-bottom-color: #495057;
        background-color: transparent;
        font-weight: 600;
    }

    #modalEditPerfil .accordion-item {
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    #modalEditPerfil .accordion-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    /* Botones de acción: Plantilla y Agregar Usuario */
    .btn-action-size {
      height: 36px;
      padding: 0.375rem 0.75rem;
      font-size: 0.875rem;
      line-height: 1;
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
    }

    #historialUsuarios .control-bajas-action-btn {
      width: 38px;
      min-width: 38px;
      height: 28px;
      padding: 0;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
    }

    #historialUsuarios .control-bajas-action-btn i {
      font-size: 0.86rem;
      line-height: 1;
    }

    .gestion-personal-name-cell {
      display: flex;
      align-items: flex-start;
      gap: 0.85rem;
      min-width: 280px;
    }

    .gestion-personal-avatar {
      width: 46px;
      height: 46px;
      min-width: 46px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #fff;
      box-shadow: 0 5px 16px rgba(30, 41, 59, 0.16);
      background: #f1f5f9;
    }

    .gestion-personal-avatar-fallback {
      width: 46px;
      height: 46px;
      min-width: 46px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 0.82rem;
      font-weight: 700;
      letter-spacing: 0;
      border: 2px solid #fff;
      box-shadow: 0 5px 16px rgba(30, 41, 59, 0.16);
      background: linear-gradient(135deg, #24324d 0%, #0d6efd 100%);
    }

    .gestion-avatar-btn {
      border: 0;
      background: transparent;
      padding: 0;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      line-height: 1;
      width: 46px;
      height: 46px;
      min-width: 46px;
      flex: 0 0 auto;
    }

    .gestion-avatar-btn:hover .gestion-personal-avatar,
    .gestion-avatar-btn:hover .gestion-personal-avatar-fallback {
      transform: translateY(-1px) scale(1.03);
      box-shadow: 0 8px 22px rgba(30, 41, 59, 0.22);
    }

    .gestion-avatar-btn:focus-visible {
      outline: 3px solid rgba(37, 99, 235, 0.35);
      outline-offset: 3px;
    }

    .gestion-personal-avatar,
    .gestion-personal-avatar-fallback {
      transition: transform .15s ease, box-shadow .15s ease;
    }

    #modalGestionFotoUsuario .modal-dialog {
      max-width: min(34rem, calc(100vw - 2rem));
      margin: 1.75rem auto;
    }

    #modalGestionFotoUsuario.modal.show {
      background: rgba(15, 23, 42, 0.68);
      z-index: 100080 !important;
    }

    #modalGestionFotoUsuario .modal-content {
      border: 0;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 22px 55px rgba(15, 23, 42, 0.22);
    }

    #modalGestionFotoUsuario .modal-header {
      padding: 1rem 1.2rem;
      border-bottom: 1px solid #e5e7eb;
      background: #fff;
    }

    #modalGestionFotoUsuario .modal-title {
      color: #172033;
      font-size: 1rem;
      font-weight: 800;
      line-height: 1.25;
    }

    .gestion-foto-subtitle {
      color: #64748b;
      font-size: .76rem;
      font-weight: 700;
      margin-top: .15rem;
    }

    .gestion-foto-body {
      padding: 1.15rem;
      background: #f8fafc;
    }

    .gestion-foto-stage {
      height: min(58vh, 28rem);
      min-height: 18rem;
      border-radius: .85rem;
      background: #0f172a;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .gestion-foto-img {
      max-width: 100%;
      max-height: 100%;
      width: auto;
      height: auto;
      object-fit: contain;
      display: block;
      border-radius: 0 !important;
      clip-path: none !important;
      mask-image: none !important;
      -webkit-mask-image: none !important;
      box-shadow: none !important;
    }

    .gestion-foto-fallback {
      width: 10rem;
      height: 10rem;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 3rem;
      font-weight: 900;
      letter-spacing: 0;
      background: linear-gradient(135deg, #24324d 0%, #0d6efd 100%);
      border: 4px solid rgba(255, 255, 255, .92);
      box-shadow: 0 20px 40px rgba(0, 0, 0, .22);
    }

    .gestion-foto-footer {
      padding: .75rem 1.15rem;
      border-top: 1px solid #e5e7eb;
      background: #fff;
      display: flex;
      justify-content: flex-end;
    }

    body.dark-mode #modalGestionFotoUsuario .modal-header {
      background: #111827;
      border-color: #1f2937;
    }

    body.dark-mode #modalGestionFotoUsuario .modal-title {
      color: #e2e8f0;
    }

    body.dark-mode .gestion-foto-body {
      background: #0f172a;
    }

    body.dark-mode .gestion-foto-footer {
      background: #111827;
      border-color: #1f2937;
    }

    .gestion-personal-name-info {
      min-width: 0;
      line-height: 1.18;
    }

    .gestion-personal-employee-code {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      color: #475569;
      font-size: .78rem;
      font-weight: 700;
      line-height: 1.15;
      margin-bottom: .16rem;
    }

    .gestion-personal-employee-code .gestion-personal-code-value {
      display: inline-flex;
      align-items: center;
      min-height: 1.25rem;
      padding: .08rem .48rem;
      border: 1px solid #bfdbfe;
      border-radius: 999px;
      background: #eff6ff;
      color: #1d4ed8;
      font-size: .76rem;
      font-weight: 800;
      letter-spacing: 0;
    }

    .gestion-personal-name-main {
      color: #344054;
      font-size: .92rem;
      font-weight: 800;
      line-height: 1.18;
    }

    .gestion-personal-external-id {
      display: flex;
      align-items: center;
      gap: .32rem;
      color: #64748b;
      font-size: .76rem;
      font-weight: 600;
      line-height: 1.15;
      margin-top: .18rem;
    }

    .gestion-personal-external-id strong {
      color: #475569;
      font-weight: 800;
    }

    .gestion-personal-external-badge {
      display: inline-flex;
      align-items: center;
      min-height: 1.25rem;
      padding: .08rem .5rem;
      border-radius: 999px;
      background: #f1f5f9;
      color: #334155;
      border: 1px solid #cbd5e1;
      font-size: .72rem;
      font-weight: 800;
      line-height: 1;
      letter-spacing: 0;
    }

    .gestion-personal-reingreso-badge {
      display: inline-flex;
      align-items: center;
      min-height: 1.25rem;
      padding: .08rem .5rem;
      border-radius: 999px;
      background: #ecfdf5;
      color: #047857;
      border: 1px solid #a7f3d0;
      font-size: .72rem;
      font-weight: 800;
      line-height: 1;
      letter-spacing: 0;
    }

    .gestion-personal-username {
      margin-top: .46rem;
      color: #64748b;
      font-size: .76rem;
      font-weight: 700;
    }

    #modalAgregarUsuarioRrhh .modal-dialog {
      max-width: min(1320px, calc(100vw - 1.75rem));
      margin: .875rem auto;
    }

    #modalAgregarUsuarioRrhh.modal.show {
      z-index: 99999 !important;
      background: transparent !important;
    }

    #modalAgregarUsuarioRrhh .modal-content {
      position: relative;
      height: min(900px, calc(100vh - 1.75rem));
      max-height: calc(100vh - 1.75rem);
      border: 1px solid #dbe5f2;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 24px 70px rgba(15, 23, 42, .28);
    }

    #modalAgregarUsuarioRrhh .modal-header,
    #modalAgregarUsuarioRrhh .modal-footer {
      flex-shrink: 0;
      background: #fff;
      z-index: 2;
    }

    #modalAgregarUsuarioRrhh .modal-header {
      min-height: 86px;
      padding: 1.15rem 1.4rem;
      border-bottom-color: #e7edf6;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-title {
      display: flex;
      align-items: center;
      gap: .85rem;
      min-width: 0;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-avatar {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #0d54c9;
      background: #eaf2ff;
      flex: 0 0 52px;
      font-size: 1.35rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-subtitle {
      color: #63718a;
      font-size: .95rem;
      font-weight: 500;
      margin-top: .2rem;
    }

    #modalAgregarUsuarioRrhh .modal-body {
      display: flex;
      flex-direction: column;
      gap: .9rem;
      overflow: hidden;
      padding: 1rem 1.35rem;
      min-height: 0;
      background: #f6f9fd;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-progress {
      display: grid;
      grid-template-columns: auto minmax(160px, 1fr) auto;
      align-items: center;
      gap: .95rem;
      color: #334155;
      font-size: .86rem;
      font-weight: 700;
    }

    #modalAgregarUsuarioRrhh .rrhh-progress-track {
      height: 8px;
      border-radius: 999px;
      background: #e5e8ee;
      overflow: hidden;
    }

    #modalAgregarUsuarioRrhh .rrhh-progress-bar {
      width: 0;
      height: 100%;
      border-radius: inherit;
      background: linear-gradient(90deg, #0d6efd, #1559cc);
      transition: width .2s ease;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-info {
      display: flex;
      align-items: center;
      gap: .6rem;
      border: 1px solid #b7d4ff;
      border-radius: 8px;
      background: #eef6ff;
      color: #0d4ead;
      padding: .65rem .85rem;
      font-size: .88rem;
      font-weight: 600;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-layout {
      display: grid;
      grid-template-columns: 285px minmax(0, 1fr);
      gap: 1rem;
      min-height: 0;
      flex: 1;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-sidebar,
    #modalAgregarUsuarioRrhh .rrhh-wizard-content {
      min-height: 0;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-sidebar {
      border-right: 1px solid #dfe7f2;
      padding-right: 1rem;
      overflow-y: auto;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-content {
      overflow-y: auto;
      padding-right: .35rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps {
      display: flex;
      flex-direction: column;
      gap: .28rem;
      border: 0;
      margin: 0;
      padding: .15rem 0;
      position: relative;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps::before {
      content: "";
      position: absolute;
      left: 23px;
      top: 24px;
      bottom: 24px;
      width: 1px;
      background: #d4ddeb;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps .nav-item {
      position: relative;
      z-index: 1;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps .nav-link {
      width: 100%;
      display: grid;
      grid-template-columns: 48px minmax(0, 1fr);
      align-items: center;
      gap: .6rem;
      color: #334155;
      text-align: left;
      border: 1px solid transparent;
      border-radius: 8px;
      background: transparent;
      padding: .72rem .8rem .72rem 0;
      box-shadow: none;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps .nav-link.active {
      color: #0f3f98;
      border-color: #b9d3ff;
      background: #edf5ff;
    }

    #modalAgregarUsuarioRrhh .rrhh-step-marker {
      width: 42px;
      height: 42px;
      border: 1px solid #d5dfec;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #fff;
      color: #334155;
      font-weight: 800;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps .nav-link.active .rrhh-step-marker {
      border-color: #0d6efd;
      background: #0d6efd;
      color: #fff;
      box-shadow: 0 6px 16px rgba(13, 110, 253, .25);
    }

    #modalAgregarUsuarioRrhh .rrhh-step-marker .fa-check {
      display: none;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps .nav-link.is-complete .rrhh-step-marker {
      border-color: #21a45d;
      background: #21a45d;
      color: #fff;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps .nav-link.is-complete .rrhh-step-num {
      display: none;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-steps .nav-link.is-complete .fa-check {
      display: inline-block;
    }

    #modalAgregarUsuarioRrhh .rrhh-step-title {
      display: block;
      font-weight: 800;
      color: #24324d;
      line-height: 1.15;
    }

    #modalAgregarUsuarioRrhh .rrhh-step-state {
      display: block;
      color: #64748b;
      font-size: .78rem;
      font-weight: 600;
      margin-top: .25rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-section {
      border: 1px solid #e3e8ef;
      border-radius: 8px;
      padding: 1rem;
      background: #fff;
      box-shadow: 0 8px 18px rgba(15, 23, 42, .04);
    }

    #modalAgregarUsuarioRrhh .rrhh-section-title {
      font-size: .9rem;
      font-weight: 700;
      color: #24324d;
      margin-bottom: .85rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-password-masked {
      -webkit-text-security: disc;
      text-security: disc;
      font-family: var(--bs-font-sans-serif);
    }

    #modalAgregarUsuarioRrhh .rrhh-assignment-summary {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: .75rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-summary-label {
      color: #64748b;
      font-size: .76rem;
      font-weight: 800;
      margin-bottom: .35rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-summary-chip {
      display: inline-flex;
      max-width: 100%;
      align-items: center;
      border-radius: 999px;
      background: #eef1f5;
      color: #475569;
      padding: .25rem .65rem;
      font-size: .8rem;
      font-weight: 700;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    #modalAgregarUsuarioRrhh .rrhh-beneficiarios-status {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      border-radius: 999px;
      background: #eef1f5;
      color: #475569;
      padding: .25rem .65rem;
      font-size: .8rem;
      font-weight: 800;
    }

    #modalAgregarUsuarioRrhh .rrhh-beneficiarios-status.is-ok {
      background: #dcfce7;
      color: #166534;
    }

    #modalAgregarUsuarioRrhh .rrhh-beneficiarios-status.is-warn {
      background: #fff7d6;
      color: #9a6400;
    }

    #modalAgregarUsuarioRrhh .rrhh-beneficiarios-status.is-error {
      background: #fee2e2;
      color: #b42318;
    }

    #modalAgregarUsuarioRrhh .rrhh-sensitive-note {
      display: flex;
      gap: .5rem;
      color: #64748b;
      font-size: .82rem;
      font-weight: 600;
      margin: -.25rem 0 .85rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-card {
      border: 1px solid #dbe4f0;
      background: #f8fbff;
      border-radius: 8px;
      padding: .95rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-row {
      display: flex;
      align-items: flex-start;
      gap: .75rem;
      flex-wrap: wrap;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-field {
      flex: 0 1 350px;
      max-width: 100%;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-field .input-group {
      box-shadow: 0 4px 10px rgba(15, 23, 42, .08);
      border-radius: .55rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-field .input-group-text {
      font-weight: 700;
      color: #334155;
      background: #fff;
    }

    #modalAgregarUsuarioRrhh #rrhh_salario_sensible {
      font-weight: 700;
      color: #1f2937;
    }

    #modalAgregarUsuarioRrhh #rrhh_salario_sensible_letras {
      color: #64748b;
      font-size: .82rem;
      font-weight: 600;
      margin-top: .35rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-actions {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: .55rem;
      flex-wrap: wrap;
      flex: 1 1 100%;
      padding-top: .45rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-actions .btn {
      min-height: 2.42rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: .35rem;
      margin: 0;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-status {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      border-radius: 999px;
      padding: .2rem .55rem;
      font-size: .75rem;
      font-weight: 700;
      background: #fff3cd;
      color: #9a6700;
      white-space: nowrap;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-status.is-unlocked {
      background: #dcfce7;
      color: #166534;
    }

    #modalAgregarUsuarioRrhh .rrhh-salary-status.is-denied {
      background: #fee2e2;
      color: #991b1b;
    }

    #modalAgregarUsuarioRrhh .rrhh-repeat-row {
      border: 1px solid #edf1f6;
      border-radius: 8px;
      padding: .75rem;
      background: #f8fafc;
      margin-bottom: .65rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-remove-row {
      min-width: 38px;
    }

    #modalAgregarUsuarioRrhh .form-label {
      margin-bottom: .25rem;
      font-size: .82rem;
      font-weight: 600;
    }

    #modalAgregarUsuarioRrhh .form-control,
    #modalAgregarUsuarioRrhh .form-select {
      min-height: 38px;
    }

    #modalAgregarUsuarioRrhh .row.g-3 {
      --bs-gutter-y: .75rem;
    }

    #modalAgregarUsuarioRrhh .modal-footer {
      min-height: 72px;
      padding: .85rem 1.35rem;
      border-top-color: #e7edf6;
      gap: .65rem;
    }

    #modalAgregarUsuarioRrhh .rrhh-footer-left,
    #modalAgregarUsuarioRrhh .rrhh-footer-right {
      display: flex;
      align-items: center;
      gap: .65rem;
      flex-wrap: wrap;
    }

    #modalAgregarUsuarioRrhh .rrhh-footer-left {
      flex: 1 1 auto;
    }

    #modalAgregarUsuarioRrhh .rrhh-footer-right {
      justify-content: flex-end;
    }

    #modalAgregarUsuarioRrhh .rrhh-wizard-action {
      min-width: 168px;
      min-height: 42px;
    }

    #modalAgregarUsuarioRrhh .rrhh-loading-overlay {
      position: absolute;
      inset: 0;
      z-index: 6;
      display: none;
      align-items: center;
      justify-content: center;
      background: rgba(246, 249, 253, .92);
      backdrop-filter: blur(2px);
    }

    #modalAgregarUsuarioRrhh.is-loading-rrhh .rrhh-loading-overlay {
      display: flex;
    }

    #modalAgregarUsuarioRrhh .rrhh-loading-card {
      display: inline-flex;
      align-items: center;
      gap: .85rem;
      padding: 1rem 1.25rem;
      border: 1px solid #dbe5f2;
      border-radius: 10px;
      background: #fff;
      color: #243551;
      font-weight: 700;
      box-shadow: 0 16px 34px rgba(15, 23, 42, .14);
    }

    #modalActualizacionInfoPersona {
      padding: 0 !important;
    }

    #modalActualizacionInfoPersona.modal.show {
      z-index: 100030 !important;
      background: transparent;
    }

    #modalActualizacionInfoPersona .modal-dialog {
      max-width: min(960px, calc(100vw - 2rem));
      margin: 5.25rem auto 1rem;
    }

    #modalActualizacionInfoPersona .modal-content {
      height: min(650px, calc(100vh - 6.5rem));
      max-height: calc(100vh - 6.5rem);
      border: 1px solid #d7e0ec;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 18px 46px rgba(15, 23, 42, .20);
    }

    #modalActualizacionInfoPersona .modal-header,
    #modalActualizacionInfoPersona .modal-footer {
      flex-shrink: 0;
      background: #fff;
    }

    #modalActualizacionInfoPersona .modal-header {
      min-height: 56px;
      padding: .85rem 1rem;
      border-bottom-color: #edf1f6;
    }

    #modalActualizacionInfoPersona .modal-footer {
      min-height: 58px;
      padding: .75rem 1rem;
      border-top-color: #edf1f6;
    }

    #modalActualizacionInfoPersona .modal-body {
      display: flex;
      flex-direction: column;
      min-height: 0;
      overflow: hidden;
      padding: 0;
      background: #f6f8fb;
    }

    #modalActualizacionInfoPersona .actualizacion-info-summary {
      display: flex;
      align-items: flex-start;
      gap: .65rem;
      padding: .85rem 1rem;
      border-bottom: 1px solid #edf1f6;
      background: #fff;
      flex-shrink: 0;
    }

    #modalActualizacionInfoPersona .actualizacion-info-layout {
      display: grid;
      grid-template-columns: 310px minmax(0, 1fr);
      gap: 0;
      min-height: 0;
      flex: 1;
    }

    #modalActualizacionInfoPersona .actualizacion-info-panel {
      min-height: 0;
      background: #fff;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    #modalActualizacionInfoPersona .actualizacion-info-panel:first-child {
      border-right: 1px solid #edf1f6;
    }

    #modalActualizacionInfoPersona .actualizacion-info-panel-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      min-height: 46px;
      padding: .75rem .9rem;
      border-bottom: 1px solid #edf1f6;
      font-weight: 700;
      color: #24324d;
      flex-shrink: 0;
    }

    #modalActualizacionInfoPersona .actualizacion-info-panel-body {
      flex: 1;
      min-height: 0;
      overflow-y: auto;
      padding: .75rem;
    }

    #modalActualizacionInfoPersona .actualizacion-info-group {
      margin: .45rem .15rem .4rem;
      color: #64748b;
      font-size: .72rem;
      font-weight: 800;
      letter-spacing: .04em;
      text-transform: uppercase;
    }

    #modalActualizacionInfoPersona .actualizacion-info-check {
      display: flex;
      align-items: flex-start;
      gap: .65rem;
      border: 1px solid #e3e8ef;
      border-radius: 8px;
      padding: .62rem .7rem;
      background: #fff;
      cursor: pointer;
      transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
    }

    #modalActualizacionInfoPersona .actualizacion-info-check:hover {
      border-color: #bdd0ea;
      background: #fbfdff;
    }

    #modalActualizacionInfoPersona .actualizacion-info-check:has(input:checked) {
      border-color: #0d6efd;
      background: #f2f7ff;
      box-shadow: 0 4px 14px rgba(13, 110, 253, .12);
    }

    #modalActualizacionInfoPersona .actualizacion-info-check .form-check-input {
      margin: .12rem 0 0;
      width: 18px;
      height: 18px;
      flex: 0 0 18px;
    }

    #modalActualizacionInfoPersona .actualizacion-info-field {
      border: 1px solid #e3e8ef;
      border-radius: 8px;
      background: #fff;
      padding: .85rem;
    }

    #modalActualizacionInfoPersona .actualizacion-info-field + .actualizacion-info-field {
      margin-top: .65rem;
    }

    #modalActualizacionInfoPersona .actualizacion-info-request-card {
      display: flex;
      align-items: flex-start;
      gap: .75rem;
      border: 1px solid #dbe5f2;
      border-radius: 8px;
      background: #fff;
      padding: .85rem;
      box-shadow: 0 4px 14px rgba(15, 23, 42, .04);
    }

    #modalActualizacionInfoPersona .actualizacion-info-request-card + .actualizacion-info-request-card {
      margin-top: .65rem;
    }

    #modalActualizacionInfoPersona .actualizacion-info-request-icon {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #1f2f4f;
      background: #eef5ff;
      flex: 0 0 34px;
    }

    #modalActualizacionInfoPersona .actualizacion-info-request-meta {
      display: flex;
      flex-wrap: wrap;
      gap: .35rem;
      margin-top: .45rem;
    }

    #modalActualizacionInfoPersona .actualizacion-info-empty,
    #modalActualizacionInfoPersona .actualizacion-info-loading {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 170px;
      border: 1px dashed #c8d5e7;
      border-radius: 8px;
      color: #64748b;
      text-align: center;
      background: #f8fafc;
      padding: 1rem;
    }

    #modalActualizacionInfoPersona .actualizacion-info-skeleton {
      height: 64px;
      border-radius: 8px;
      background: linear-gradient(90deg, #f1f5f9 0%, #e8eef6 50%, #f1f5f9 100%);
      background-size: 180% 100%;
      animation: actualizacionInfoPulse 1.1s ease-in-out infinite;
    }

    @keyframes actualizacionInfoPulse {
      0% { background-position: 100% 0; }
      100% { background-position: 0 0; }
    }

    @media (max-width: 991.98px) {
      #modalActualizacionInfoPersona .modal-content {
        height: calc(100vh - 1.5rem);
        max-height: calc(100vh - 1.5rem);
      }

      #modalActualizacionInfoPersona .actualizacion-info-layout {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 767.98px) {
      #modalAgregarUsuarioRrhh .modal-dialog {
        max-width: calc(100vw - .75rem);
        margin: .375rem auto;
      }

      #modalAgregarUsuarioRrhh .modal-content {
        height: calc(100vh - .75rem);
        max-height: calc(100vh - .75rem);
      }

      #modalAgregarUsuarioRrhh .modal-header {
        min-height: 76px;
        padding: .85rem 1rem;
      }

      #modalAgregarUsuarioRrhh .rrhh-wizard-avatar {
        width: 44px;
        height: 44px;
        flex-basis: 44px;
      }

      #modalAgregarUsuarioRrhh .modal-body {
        padding: .85rem;
      }

      #modalAgregarUsuarioRrhh .rrhh-wizard-progress {
        grid-template-columns: 1fr;
        gap: .5rem;
      }

      #modalAgregarUsuarioRrhh .rrhh-wizard-layout {
        grid-template-columns: 1fr;
        gap: .75rem;
      }

      #modalAgregarUsuarioRrhh .rrhh-wizard-sidebar {
        border-right: 0;
        border-bottom: 1px solid #dfe7f2;
        padding: 0 0 .75rem;
        overflow-x: auto;
        overflow-y: hidden;
      }

      #modalAgregarUsuarioRrhh .rrhh-wizard-steps {
        flex-direction: row;
        min-width: max-content;
      }

      #modalAgregarUsuarioRrhh .rrhh-wizard-steps::before {
        display: none;
      }

      #modalAgregarUsuarioRrhh .rrhh-wizard-steps .nav-link {
        width: 190px;
        grid-template-columns: 38px minmax(0, 1fr);
        padding: .55rem .65rem .55rem 0;
      }

      #modalAgregarUsuarioRrhh .rrhh-step-marker {
        width: 34px;
        height: 34px;
      }

      #modalAgregarUsuarioRrhh .rrhh-assignment-summary {
        grid-template-columns: 1fr;
      }

      #modalAgregarUsuarioRrhh .modal-footer {
        align-items: stretch;
      }

      #modalAgregarUsuarioRrhh .rrhh-footer-left,
      #modalAgregarUsuarioRrhh .rrhh-footer-right {
        width: 100%;
        justify-content: stretch;
      }

      #modalAgregarUsuarioRrhh .rrhh-wizard-action,
      #modalAgregarUsuarioRrhh .modal-footer .btn {
        flex: 1 1 0;
      }
    }

    #rrhhModalScrim {
      position: fixed;
      inset: 0;
      z-index: 99998;
      background: rgba(5, 10, 20, 0.78);
      backdrop-filter: blur(2px);
      pointer-events: none;
    }

    .flatpickr-calendar.rrhh-flatpickr-calendar {
      z-index: 100020 !important;
      width: 335px !important;
      transform: none !important;
      transform-origin: initial !important;
    }

    .flatpickr-calendar.rrhh-flatpickr-calendar .flatpickr-months {
      height: 48px;
      padding: 6px 44px;
      align-items: center;
      box-sizing: border-box;
    }

    .flatpickr-calendar.rrhh-flatpickr-calendar .flatpickr-prev-month,
    .flatpickr-calendar.rrhh-flatpickr-calendar .flatpickr-next-month {
      top: 7px;
      width: 34px;
      height: 34px;
      padding: 8px;
      border-radius: 10px;
      background: #f3f6fb;
      color: #334155;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .flatpickr-calendar.rrhh-flatpickr-calendar .flatpickr-prev-month {
      left: 8px;
    }

    .flatpickr-calendar.rrhh-flatpickr-calendar .flatpickr-next-month {
      right: 8px;
    }

    .flatpickr-calendar.rrhh-flatpickr-calendar .flatpickr-current-month {
      left: 44px;
      width: calc(100% - 88px);
      height: 36px;
      padding: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      font-size: 1rem;
    }

    .flatpickr-calendar.rrhh-flatpickr-calendar .flatpickr-monthDropdown-months {
      width: 128px;
      min-width: 128px;
      height: 34px;
      padding: 0 .5rem;
      border: 0;
      border-radius: 10px;
      background-color: #f3f6fb;
      color: #334155;
      font-weight: 600;
      appearance: auto !important;
      -webkit-appearance: auto !important;
      -moz-appearance: auto !important;
    }

    .flatpickr-calendar.rrhh-flatpickr-calendar .numInputWrapper {
      display: none;
    }

    .rrhh-year-select {
      min-width: 86px;
      max-width: 96px;
      height: 34px;
      border: 0;
      border-radius: 8px;
      padding: 0 .55rem;
      margin-left: 0;
      background: #f3f6fb;
      color: #334155;
      font-weight: 600;
      outline: none;
    }

    #modalCredencialRrhh .modal-dialog {
      max-width: min(1040px, calc(100vw - 2rem));
    }

    #modalCredencialRrhh.modal.show {
      z-index: 100040 !important;
      background: transparent !important;
    }

    #modalCredencialRrhh.rrhh-hidden-for-photo-editor {
      display: none !important;
    }

    #modalCredencialRrhh .modal-content {
      border-radius: 12px;
      overflow: visible;
    }

    #modalCredencialRrhh .modal-header {
      align-items: center;
      min-height: 64px;
      padding: 1rem 3.25rem 1rem 1.5rem;
      position: relative;
    }

    #modalCredencialRrhh .btn-close {
      align-items: center;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
      display: flex;
      height: 1.55rem;
      justify-content: center;
      margin: 0;
      opacity: 1;
      padding: .38rem;
      position: absolute;
      right: .7rem;
      top: .7rem;
      width: 1.55rem;
      z-index: 4;
    }

    #modalCredencialRrhh .btn-close:hover {
      opacity: 1;
    }

    .rrhh-credential-toolbar {
      display: flex;
      flex-wrap: wrap;
      gap: .75rem;
      align-items: center;
      justify-content: space-between;
      border: 1px solid #e3e8ef;
      border-radius: 10px;
      padding: .75rem;
      margin-bottom: 1rem;
      background: #fff;
    }

    .rrhh-credential-toolbar .btn-check:checked + .btn {
      color: #fff;
      background: #0054a6;
      border-color: #0054a6;
      box-shadow: 0 8px 18px rgba(0, 84, 166, .18);
    }

    .rrhh-credential-notice {
      border: 1px solid rgba(0, 84, 166, .16);
      border-radius: 10px;
      padding: .75rem .9rem;
      margin-bottom: 1rem;
      background: #f3f9ff;
      color: #0d2f5f;
      font-size: .9rem;
    }

    .rrhh-credential-stage {
      background: linear-gradient(135deg, #f8fbff 0%, #eef5fb 100%);
      border-radius: 12px;
      padding: 1.25rem;
    }

    .rrhh-credential-stage .row > [class*="col-"] {
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
    }

    .rrhh-photo-dropzone {
      border: 1px dashed #b8c6d8;
      border-radius: 10px;
      padding: .55rem .75rem;
      color: #506176;
      background: #f8fafc;
      font-size: .82rem;
      min-width: 220px;
    }

    .rrhh-photo-dropzone.is-dragover,
    .rrhh-id-photo-wrap.is-dragover {
      border-color: #0054a6;
      background: #eef6ff;
    }

    .rrhh-id-card {
      width: min(100%, 340px);
      height: 542px;
      margin: 0 auto;
      border-radius: 18px;
      overflow: hidden;
      background-size: 100% 100%;
      background-position: center;
      background-repeat: no-repeat;
      border: 1px solid rgba(0, 84, 166, .14);
      box-shadow: 0 18px 40px rgba(30, 41, 59, 0.18);
      position: relative;
      color: #fff;
    }

    .rrhh-id-card::before,
    .rrhh-id-card::after {
      content: "";
      position: absolute;
      pointer-events: none;
    }

    .rrhh-id-front {
      background:
        radial-gradient(circle at 88% 16%, rgba(199, 213, 43, .38) 0 8%, transparent 9%),
        linear-gradient(142deg, transparent 0 58%, rgba(199, 213, 43, .92) 58.2% 66%, transparent 66.2%),
        linear-gradient(168deg, #ffffff 0 50%, #0054a6 50.2% 100%);
    }

    .rrhh-id-back {
      background:
        radial-gradient(circle at 82% 18%, rgba(199, 213, 43, .34) 0 10%, transparent 11%),
        linear-gradient(144deg, transparent 0 48%, rgba(199, 213, 43, .9) 48.2% 56%, transparent 56.2%),
        linear-gradient(168deg, #0054a6 0 100%);
    }

    .rrhh-id-front::before,
    .rrhh-id-front::after,
    .rrhh-id-back::before {
      display: block;
      content: "";
      position: absolute;
      pointer-events: none;
    }

    .rrhh-id-front::before {
      inset: 0 0 auto 0;
      height: 112px;
      background: linear-gradient(135deg, #fff 0%, #f8fbff 100%);
    }

    .rrhh-id-front::after {
      left: -46px;
      bottom: -58px;
      width: 180px;
      height: 180px;
      border-radius: 999px;
      border: 24px solid rgba(255, 255, 255, .16);
    }

    .rrhh-id-back::before {
      inset: 0 0 auto 0;
      height: 118px;
      background: linear-gradient(135deg, #fff 0%, #eef6ff 100%);
      clip-path: polygon(0 0, 100% 0, 100% 72%, 0 100%);
    }

    .rrhh-id-card-inner {
      position: relative;
      z-index: 1;
      height: 100%;
      padding: 0;
      display: block;
    }

    .rrhh-id-logo {
      position: absolute;
      left: 50%;
      top: 28px;
      width: 148px;
      height: auto;
      display: block;
      transform: translateX(-50%);
      object-fit: contain;
      filter: none;
    }

    .rrhh-id-photo-wrap {
      position: absolute;
      left: 50%;
      top: 142px;
      width: 146px;
      height: 176px;
      transform: translateX(-50%);
      border-radius: 12px;
      border: 6px solid #fff;
      box-shadow: 0 16px 28px rgba(13, 47, 95, .22);
      background: #f8fafc;
      overflow: hidden;
      cursor: grab;
    }

    .rrhh-id-photo-wrap::after {
      content: "Arrastra para ajustar";
      position: absolute;
      left: 8px;
      right: 8px;
      bottom: 8px;
      padding: .18rem .35rem;
      border-radius: 999px;
      color: #fff;
      background: rgba(13, 47, 95, .72);
      font-size: .66rem;
      font-weight: 700;
      text-align: center;
      opacity: 0;
      transition: opacity .15s ease;
      pointer-events: none;
    }

    .rrhh-id-photo-wrap:hover::after {
      opacity: 1;
    }

    .rrhh-id-photo-wrap:active {
      cursor: grabbing;
    }

    .rrhh-id-photo-wrap.is-contain {
      cursor: default;
      background: #fff;
    }

    .rrhh-id-photo-wrap.is-contain::after {
      display: none;
    }

    .rrhh-id-photo {
      width: 100%;
      height: 100%;
      display: block;
      object-fit: var(--rrhh-photo-fit, cover);
      object-position: var(--rrhh-photo-position, 50% 50%);
      transform: var(--rrhh-photo-transform, none);
      transform-origin: center center;
      user-select: none;
      -webkit-user-drag: none;
      border-radius: 0 !important;
      clip-path: none !important;
    }

    .rrhh-id-photo-wrap.is-contain .rrhh-id-photo {
      object-fit: contain !important;
      object-position: center center !important;
      transform: none !important;
      background: #fff;
    }

    .rrhh-id-photo-fallback {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #003d82, #0054a6);
      color: #fff;
      font-size: 2.2rem;
      font-weight: 800;
    }

    .rrhh-photo-editor-frame {
      width: min(100%, 420px);
      height: 486px;
      margin: 0 auto;
      border-radius: 18px;
      border: 1px solid #b8d3ff;
      background:
        linear-gradient(90deg, rgba(255,255,255,.55) 1px, transparent 1px),
        linear-gradient(rgba(255,255,255,.55) 1px, transparent 1px),
        #eef3f8;
      background-size: 50% 33.333%;
      overflow: hidden;
      position: relative;
      cursor: grab;
      box-shadow: inset 0 0 0 999px rgba(0, 84, 166, .04);
    }

    .rrhh-photo-editor-frame.is-credential {
      width: min(100%, 360px);
      height: auto;
      aspect-ratio: 182 / 224;
    }

    .rrhh-photo-editor-frame:active {
      cursor: grabbing;
    }

    .rrhh-photo-editor-frame::after {
      content: "";
      position: absolute;
      inset: 0;
      border: 2px solid rgba(13, 110, 253, .65);
      pointer-events: none;
    }

    .rrhh-photo-editor-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: 50% 50%;
      display: block;
      user-select: none;
      -webkit-user-drag: none;
    }

    .rrhh-photo-editor-empty {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #64748b;
      font-weight: 700;
    }

    #modalAjustarFotoCredencialRrhh .modal-dialog {
      max-width: min(980px, calc(100vw - 2rem));
    }

    .rrhh-photo-editor-layout {
      display: grid;
      grid-template-columns: minmax(280px, 340px) minmax(300px, 1fr);
      gap: 1.25rem;
      align-items: start;
    }

    .rrhh-photo-editor-panel-title {
      color: #24324d;
      font-size: .88rem;
      font-weight: 800;
      margin-bottom: .5rem;
      text-align: center;
    }

    .rrhh-photo-live-preview {
      min-height: 542px;
      display: flex;
      justify-content: center;
    }

    .rrhh-photo-editor-actions {
      display: flex;
      flex-wrap: wrap;
      gap: .5rem;
      justify-content: flex-end;
    }

    @media (max-width: 900px) {
      .rrhh-photo-editor-layout {
        grid-template-columns: 1fr;
      }

      .rrhh-photo-live-preview {
        min-height: 0;
      }
    }

    .rrhh-id-name {
      position: absolute;
      left: 3.5%;
      right: 3.5%;
      top: 354px;
      max-width: none;
      margin: 0;
      font-size: 1.14rem;
      line-height: 1.08;
      font-weight: 800;
      text-align: center;
      text-transform: uppercase;
      color: #fff;
      word-break: break-word;
      text-shadow: 0 2px 4px rgba(0, 55, 120, .3);
    }

    .rrhh-id-position {
      position: absolute;
      left: 14.1%;
      right: 14.1%;
      top: 417px;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 30px;
      margin: 0;
      padding: 0;
      border-radius: 0;
      background: transparent;
      color: #fff;
      font-weight: 800;
      text-transform: uppercase;
      font-size: .88rem;
      line-height: 1.1;
      box-shadow: none;
      text-align: center;
      text-shadow: 0 2px 4px rgba(0, 55, 120, .3);
    }

    .rrhh-id-meta {
      display: none;
    }

    .rrhh-id-meta:has(.rrhh-id-meta-item:nth-child(3):last-child) .rrhh-id-meta-item:nth-child(3) {
      grid-column: 1 / -1;
      width: calc(50% - .325rem);
      justify-self: center;
    }

    .rrhh-id-meta-item {
      border-radius: 10px;
      background: #f8fafc;
      border: 1px solid #e7edf4;
      padding: .55rem .65rem;
    }

    .rrhh-id-meta-label {
      display: block;
      color: #6c7786;
      font-size: .68rem;
      font-weight: 700;
      text-transform: uppercase;
    }

    .rrhh-id-meta-value {
      display: block;
      color: #0d2f5f;
      font-weight: 800;
      line-height: 1.2;
      word-break: break-word;
    }

    .rrhh-id-qr {
      position: absolute;
      left: 50%;
      top: 188px;
      width: 184px;
      height: 184px;
      padding: 13px;
      transform: translateX(-50%);
      background: #fff;
      border: 0;
      border-radius: 18px;
      box-shadow: 0 16px 30px rgba(15, 23, 42, 0.25);
    }

    .rrhh-id-qr::after {
      content: "";
      position: absolute;
      left: 50%;
      top: 50%;
      width: 54px;
      height: 54px;
      transform: translate(-50%, -50%);
      border-radius: 12px;
      background: #fff url('/assets/img/logo_correo.png') center / 42px 42px no-repeat;
      box-shadow: 0 2px 8px rgba(15, 23, 42, .12);
    }

    .rrhh-id-qr-img {
      width: 100%;
      height: 100%;
      display: block;
      object-fit: contain;
    }

    .rrhh-id-back-list {
      display: none;
    }

    .rrhh-id-back-row {
      display: grid;
      grid-template-columns: 30px 1fr;
      gap: .65rem;
      align-items: center;
      text-align: left;
      color: #0d2f5f;
      font-size: .9rem;
    }

    .rrhh-id-back-row i {
      width: 30px;
      height: 30px;
      border-radius: 999px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(0, 84, 166, 0.12);
      color: #0054a6;
    }

    .rrhh-id-card.is-horizontal {
      width: min(100%, 560px);
      height: 353px;
      border-radius: 16px;
    }

    .rrhh-id-card.is-horizontal .rrhh-id-card-inner {
      height: 353px;
      align-items: stretch;
    }

    .rrhh-id-front.is-horizontal::before {
      inset: 0 0 auto 0;
      width: 100%;
      height: 88px;
      clip-path: polygon(0 0, 100% 0, 100% 72%, 0 100%);
    }

    .rrhh-id-front.is-horizontal::after {
      right: -36px;
      bottom: -46px;
      width: 136px;
      height: 136px;
      border-width: 22px;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-logo {
      position: absolute;
      top: .9rem;
      right: 1.1rem;
      z-index: 3;
      width: 48px;
      height: 48px;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-card-inner {
      display: grid;
      grid-template-columns: 146px minmax(0, 1fr);
      grid-template-rows: 84px auto auto 1fr;
      column-gap: 1.35rem;
      padding: 1.15rem 1.35rem;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-photo,
    .rrhh-id-front.is-horizontal .rrhh-id-photo-wrap {
      width: 124px;
      height: 148px;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-photo-wrap {
      grid-column: 1;
      grid-row: 2 / 5;
      align-self: start;
      justify-self: center;
      margin-top: .35rem !important;
      border-radius: 14px;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-name {
      grid-column: 2;
      grid-row: 2;
      margin-top: .45rem;
      text-align: left;
      font-size: 1.26rem;
      word-break: normal;
      overflow-wrap: anywhere;
      padding-left: 0;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-position {
      grid-column: 2;
      grid-row: 3;
      justify-self: start;
      margin-left: 0;
      margin-top: .35rem;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-meta {
      grid-column: 2;
      grid-row: 4;
      margin-top: .75rem !important;
      align-self: end;
      padding-left: 0;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: .55rem;
      font-size: .78rem;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-meta-item {
      padding: .48rem .58rem;
      border-radius: 9px;
    }

    .rrhh-id-back.is-horizontal::before {
      height: 86px;
      clip-path: polygon(0 0, 100% 0, 100% 70%, 0 100%);
    }

    .rrhh-id-back.is-horizontal .rrhh-id-card-inner {
      display: grid;
      grid-template-columns: minmax(0, 1fr) 128px;
      grid-template-rows: 78px auto 1fr;
      gap: .5rem 1rem;
      padding: 1rem 1.05rem;
    }

    .rrhh-id-back.is-horizontal .rrhh-id-logo {
      grid-column: 1;
      justify-self: start;
      width: 48px;
      height: 48px;
      max-width: 48px;
      align-self: start;
    }

    .rrhh-id-back.is-horizontal .rrhh-id-qr {
      grid-column: 2;
      grid-row: 1 / 3;
      align-self: center;
      justify-self: end;
      width: 116px;
      height: 116px;
    }

    .rrhh-id-back.is-horizontal .rrhh-id-back-title {
      grid-column: 1;
      grid-row: 2;
      margin-top: .15rem !important;
      text-align: left !important;
    }

    .rrhh-id-back.is-horizontal .rrhh-id-back-list {
      grid-column: 1;
      grid-row: 3;
      margin-top: 0;
      gap: .33rem;
      align-self: start;
    }

    .rrhh-id-back.is-horizontal .rrhh-id-back-row {
      grid-template-columns: 26px minmax(0, 1fr);
      gap: .45rem;
      font-size: .82rem;
      line-height: 1.15;
    }

    .rrhh-id-back.is-horizontal .rrhh-id-back-row i {
      width: 26px;
      height: 26px;
      font-size: .78rem;
    }

    .rrhh-id-back.is-horizontal .rrhh-id-back-row div {
      min-width: 0;
      overflow-wrap: anywhere;
    }

    .rrhh-id-back-title {
      display: none;
    }

    .rrhh-id-card.is-horizontal {
      width: min(100%, 340px);
      height: 542px;
    }

    .rrhh-id-card.is-horizontal .rrhh-id-card-inner {
      height: 542px;
      display: block;
      padding: 0;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-photo-wrap,
    .rrhh-id-front.is-horizontal .rrhh-id-name,
    .rrhh-id-front.is-horizontal .rrhh-id-position,
    .rrhh-id-back.is-horizontal .rrhh-id-qr {
      grid-column: auto;
      grid-row: auto;
      justify-self: auto;
      align-self: auto;
      margin: 0 !important;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-photo-wrap {
      position: absolute;
      left: 26.75%;
      top: 21.05%;
      width: 46.5%;
      height: 34.7%;
      border-radius: 0;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-name {
      position: absolute;
      left: 3.5%;
      right: 3.5%;
      top: 66.4%;
      text-align: center;
      font-size: 1.05rem;
      padding-left: 0;
    }

    .rrhh-id-front.is-horizontal .rrhh-id-position {
      position: absolute;
      left: 14.1%;
      right: 14.1%;
      top: 75.1%;
      justify-self: auto;
    }

    .rrhh-id-back.is-horizontal .rrhh-id-qr {
      position: absolute;
      left: 22.05%;
      top: 38.55%;
      width: 55.9%;
      height: 35.05%;
    }

    /* Gafete Maxikash v2: replica visual del frente/reverso de referencia. */
    #modalCredencialRrhh .rrhh-id-card {
      width: min(100%, 340px) !important;
      height: 542px !important;
      border-radius: 0 !important;
      overflow: hidden !important;
      border: 0 !important;
      box-shadow: 0 20px 42px rgba(15, 23, 42, .18) !important;
      color: #fff !important;
      isolation: isolate;
    }

    #modalCredencialRrhh .rrhh-id-front {
      background:
        radial-gradient(circle at 3px 3px, rgba(34, 193, 224, .55) 0 2.6px, transparent 3px) left -7px bottom -10px / 18px 18px repeat,
        radial-gradient(circle at 3px 3px, rgba(20, 121, 192, .45) 0 2.6px, transparent 3px) right -8px bottom 70px / 18px 18px repeat,
        radial-gradient(circle at 50% 0, rgba(255, 255, 255, .86) 0 78px, rgba(218, 215, 212, .9) 126px, transparent 152px),
        linear-gradient(180deg, rgba(203, 200, 197, .95) 0 146px, transparent 147px),
        linear-gradient(145deg, #225aa4 0%, #00abd5 100%) !important;
    }

    #modalCredencialRrhh .rrhh-id-front::before {
      display: block !important;
      content: "" !important;
      position: absolute !important;
      left: -9% !important;
      right: -9% !important;
      top: 132px !important;
      height: 178px !important;
      background: linear-gradient(145deg, #1d6db2 0%, #138fc8 100%) !important;
      clip-path: polygon(0 0, 100% 16%, 100% 100%, 0 86%) !important;
      transform: none !important;
      z-index: 0 !important;
      opacity: 1 !important;
    }

    #modalCredencialRrhh .rrhh-id-front::after {
      display: block !important;
      content: "" !important;
      position: absolute !important;
      left: -12% !important;
      right: -12% !important;
      top: 188px !important;
      height: 96px !important;
      background: #dfe84d !important;
      clip-path: polygon(0 0, 100% 20%, 100% 78%, 0 100%) !important;
      transform: none !important;
      z-index: 1 !important;
      border: 0 !important;
      border-radius: 0 !important;
    }

    #modalCredencialRrhh .rrhh-id-back {
      background:
        radial-gradient(circle at 3px 3px, rgba(33, 181, 217, .55) 0 2.7px, transparent 3.2px) left -8px top -4px / 18px 18px repeat,
        radial-gradient(circle at 3px 3px, rgba(18, 70, 160, .55) 0 2.7px, transparent 3.2px) right -8px top -4px / 18px 18px repeat,
        linear-gradient(145deg, #16a5d6 0%, #0054a6 62%, #0d3e83 100%) !important;
    }

    #modalCredencialRrhh .rrhh-id-back::before {
      display: block !important;
      content: "" !important;
      position: absolute !important;
      left: -18% !important;
      right: -18% !important;
      bottom: 112px !important;
      height: 132px !important;
      background: #dfe84d !important;
      transform: rotate(-8deg) !important;
      z-index: 0 !important;
      opacity: 1 !important;
      clip-path: none !important;
    }

    #modalCredencialRrhh .rrhh-id-back::after {
      display: none !important;
    }

    #modalCredencialRrhh .rrhh-id-card-inner {
      position: relative !important;
      z-index: 2 !important;
      height: 100% !important;
      display: block !important;
      padding: 0 !important;
    }

    #modalCredencialRrhh .rrhh-id-logo {
      display: block !important;
      position: absolute !important;
      left: 50% !important;
      top: 28px !important;
      width: 248px !important;
      max-width: 248px !important;
      height: auto !important;
      transform: translateX(-50%) !important;
      object-fit: contain !important;
      filter:
        drop-shadow(0 0 0 #fff)
        drop-shadow(2px 0 0 #fff)
        drop-shadow(-2px 0 0 #fff)
        drop-shadow(0 2px 0 #fff)
        drop-shadow(0 -2px 0 #fff)
        drop-shadow(0 8px 16px rgba(15, 23, 42, .22)) !important;
      z-index: 5 !important;
    }

    #modalCredencialRrhh .rrhh-id-back .rrhh-id-logo {
      top: 108px !important;
      width: 282px !important;
      max-width: 282px !important;
      filter:
        drop-shadow(0 0 0 #fff)
        drop-shadow(2px 0 0 #fff)
        drop-shadow(-2px 0 0 #fff)
        drop-shadow(0 2px 0 #fff)
        drop-shadow(0 -2px 0 #fff)
        drop-shadow(0 8px 15px rgba(0, 49, 105, .28)) !important;
    }

    #modalCredencialRrhh .rrhh-id-photo-wrap {
      position: absolute !important;
      left: 50% !important;
      top: 104px !important;
      width: 182px !important;
      height: 212px !important;
      transform: translateX(-50%) !important;
      border-radius: 16px !important;
      border: 12px solid #fff !important;
      background: #fff !important;
      box-shadow: 0 12px 24px rgba(15, 23, 42, .24) !important;
      z-index: 4 !important;
      overflow: hidden !important;
    }

    #modalCredencialRrhh .rrhh-id-photo-fallback {
      background: #fff !important;
      color: transparent !important;
    }

    #modalCredencialRrhh .rrhh-id-greeting {
      position: absolute !important;
      left: 18px !important;
      right: 18px !important;
      top: 336px !important;
      color: #fff !important;
      font-size: 1.05rem !important;
      line-height: 1 !important;
      font-weight: 900 !important;
      text-align: center !important;
      text-shadow: 0 2px 7px rgba(0, 44, 93, .35) !important;
      z-index: 4 !important;
    }

    #modalCredencialRrhh .rrhh-id-name {
      position: absolute !important;
      left: 18px !important;
      right: 18px !important;
      top: 366px !important;
      color: #fff !important;
      font-size: .9rem !important;
      line-height: 1.08 !important;
      font-weight: 900 !important;
      text-align: center !important;
      text-transform: uppercase !important;
      text-shadow: 0 2px 8px rgba(0, 44, 93, .45) !important;
      z-index: 4 !important;
    }

    #modalCredencialRrhh .rrhh-id-position {
      position: absolute !important;
      left: 50px !important;
      right: 50px !important;
      top: 422px !important;
      min-height: 24px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      color: #eaf8ff !important;
      font-size: .68rem !important;
      line-height: 1.1 !important;
      font-weight: 800 !important;
      text-align: center !important;
      text-transform: uppercase !important;
      text-shadow: 0 2px 8px rgba(0, 44, 93, .45) !important;
      z-index: 4 !important;
    }

    #modalCredencialRrhh .rrhh-id-footer {
      position: absolute !important;
      left: 0 !important;
      right: 0 !important;
      bottom: 38px !important;
      color: #fff !important;
      font-size: 1.02rem !important;
      line-height: 1.18 !important;
      font-weight: 800 !important;
      text-align: center !important;
      text-shadow: 0 2px 8px rgba(0, 44, 93, .34) !important;
      z-index: 4 !important;
    }

    #modalCredencialRrhh .rrhh-id-back .rrhh-id-footer {
      bottom: 38px !important;
      font-size: 1.02rem !important;
      z-index: 4 !important;
    }

    #modalCredencialRrhh .rrhh-id-meta,
    #modalCredencialRrhh .rrhh-id-back-title,
    #modalCredencialRrhh .rrhh-id-back-list {
      display: none !important;
    }

    #modalCredencialRrhh .rrhh-id-qr {
      position: absolute !important;
      left: 50% !important;
      top: 214px !important;
      width: 190px !important;
      height: 190px !important;
      transform: translateX(-50%) !important;
      padding: 14px !important;
      background: #fff !important;
      border-radius: 20px !important;
      border: 0 !important;
      box-shadow: 0 18px 32px rgba(15, 23, 42, .24) !important;
      z-index: 3 !important;
    }

    #modalCredencialRrhh .rrhh-id-front {
      background: url('/assets/img/rrhh/gafete_v2_frente.png') center / 100% 100% no-repeat !important;
    }

    #modalCredencialRrhh .rrhh-id-back {
      background: url('/assets/img/rrhh/gafete_v2_reverso.png') center / 100% 100% no-repeat !important;
    }

    #modalCredencialRrhh .rrhh-id-front::before,
    #modalCredencialRrhh .rrhh-id-front::after,
    #modalCredencialRrhh .rrhh-id-back::before,
    #modalCredencialRrhh .rrhh-id-back::after,
    #modalCredencialRrhh .rrhh-id-logo,
    #modalCredencialRrhh .rrhh-id-greeting,
    #modalCredencialRrhh .rrhh-id-footer {
      display: none !important;
    }

    #modalCredencialRrhh .rrhh-id-photo-wrap {
      top: 84px !important;
      width: 182px !important;
      height: 224px !important;
      border: 0 !important;
      border-radius: 10px !important;
      box-shadow: none !important;
      background: transparent !important;
      overflow: hidden !important;
    }

    #modalCredencialRrhh .rrhh-id-photo-wrap::after {
      display: none !important;
    }

    #modalCredencialRrhh .rrhh-id-photo-wrap.is-contain,
    #modalCredencialRrhh .rrhh-id-photo-wrap.is-dragover {
      background: transparent !important;
      border-color: transparent !important;
    }

    #modalCredencialRrhh .rrhh-id-photo-wrap.is-contain .rrhh-id-photo {
      background: transparent !important;
      object-fit: contain !important;
      object-position: center center !important;
      transform: none !important;
    }

    #modalCredencialRrhh .rrhh-id-photo-wrap.is-cover .rrhh-id-photo {
      object-fit: cover !important;
    }

    #modalCredencialRrhh .rrhh-id-photo-fallback {
      display: none !important;
    }

    #modalCredencialRrhh .rrhh-id-photo {
      width: 100% !important;
      height: 100% !important;
      border-radius: 0 !important;
      clip-path: none !important;
      object-fit: var(--rrhh-photo-fit, cover) !important;
      object-position: var(--rrhh-photo-position, 50% 50%) !important;
      transform: var(--rrhh-photo-transform, none) !important;
      transform-origin: center center;
      background: transparent !important;
      display: block !important;
    }

    #modalCredencialRrhh .rrhh-id-name {
      top: 370px !important;
      font-size: .95rem !important;
      color: #fff !important;
    }

    #modalCredencialRrhh .rrhh-id-position {
      top: 420px !important;
      font-size: .68rem !important;
      color: #fff !important;
    }

    #modalCredencialRrhh .rrhh-id-qr {
      top: 208px !important;
      width: 190px !important;
      height: 190px !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-card {
      width: min(100%, 340px) !important;
      height: 542px !important;
      border: 0 !important;
      border-radius: 0 !important;
      overflow: hidden !important;
      box-shadow: 0 18px 32px rgba(15, 23, 42, .16) !important;
      position: relative !important;
      color: #fff !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-front {
      background: url('/assets/img/rrhh/gafete_v2_frente.png') center / 100% 100% no-repeat !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-front::before,
    #modalAjustarFotoCredencialRrhh .rrhh-id-front::after,
    #modalAjustarFotoCredencialRrhh .rrhh-id-logo,
    #modalAjustarFotoCredencialRrhh .rrhh-id-greeting,
    #modalAjustarFotoCredencialRrhh .rrhh-id-footer,
    #modalAjustarFotoCredencialRrhh .rrhh-id-meta {
      display: none !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-photo-wrap {
      position: absolute !important;
      left: 50% !important;
      top: 84px !important;
      width: 182px !important;
      height: 224px !important;
      transform: translateX(-50%) !important;
      border: 0 !important;
      border-radius: 10px !important;
      box-shadow: none !important;
      background: transparent !important;
      overflow: hidden !important;
      z-index: 4 !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-photo-wrap::after,
    #modalAjustarFotoCredencialRrhh .rrhh-id-photo-fallback {
      display: none !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-photo {
      width: 100% !important;
      height: 100% !important;
      object-fit: var(--rrhh-photo-fit, cover) !important;
      object-position: var(--rrhh-photo-position, 50% 50%) !important;
      transform: var(--rrhh-photo-transform, none) !important;
      transform-origin: center center !important;
      border-radius: 0 !important;
      clip-path: none !important;
      display: block !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-photo-wrap.is-contain .rrhh-id-photo {
      object-fit: contain !important;
      object-position: center center !important;
      transform: none !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-name {
      position: absolute !important;
      left: 18px !important;
      right: 18px !important;
      top: 370px !important;
      color: #fff !important;
      font-size: .95rem !important;
      line-height: 1.08 !important;
      font-weight: 900 !important;
      text-align: center !important;
      text-transform: uppercase !important;
      text-shadow: 0 2px 8px rgba(0, 44, 93, .45) !important;
      z-index: 4 !important;
    }

    #modalAjustarFotoCredencialRrhh .rrhh-id-position {
      position: absolute !important;
      left: 50px !important;
      right: 50px !important;
      top: 420px !important;
      min-height: 24px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      color: #fff !important;
      font-size: .68rem !important;
      line-height: 1.1 !important;
      font-weight: 800 !important;
      text-align: center !important;
      text-transform: uppercase !important;
      text-shadow: 0 2px 8px rgba(0, 44, 93, .45) !important;
      z-index: 4 !important;
    }

    #modalExpedienteRrhh .modal-dialog {
      max-width: min(1080px, calc(100vw - 5rem));
      margin: 1rem auto;
    }

    #modalExpedienteRrhh.modal.show {
      z-index: 100040 !important;
      background: transparent !important;
    }

    #modalExpedienteRrhh .modal-content {
      border-radius: 12px;
      overflow: hidden;
    }

    #modalExpedienteRrhh .modal-header {
      align-items: center;
      min-height: 64px;
      padding: 1rem 3.25rem 1rem 1.5rem;
      position: relative;
    }

    #modalExpedienteRrhh .btn-close {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(15, 23, 42, .08);
      height: 1.05rem;
      margin: 0;
      opacity: .72;
      position: absolute;
      right: 1.15rem;
      top: 1.15rem;
      width: 1.05rem;
      z-index: 4;
    }

    #modalExpedienteRrhh .btn-close:hover {
      opacity: 1;
    }

    @media (max-width: 767.98px) {
      #modalExpedienteRrhh .modal-dialog {
        max-width: calc(100vw - .75rem);
        margin: .375rem auto;
      }
    }

    .rrhh-expediente {
      background: #fff;
      color: #1f2937;
      border: 1px solid #e3e8ef;
      border-radius: 12px;
      overflow: hidden;
    }

    .rrhh-expediente-cover {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 1.25rem;
      padding: 1.35rem;
      color: #fff;
      background:
        radial-gradient(circle at 92% 8%, rgba(199, 213, 43, .45), transparent 24%),
        linear-gradient(135deg, #003d82 0%, #0054a6 74%);
    }

    .rrhh-expediente-logo {
      width: 58px;
      height: 58px;
      object-fit: contain;
      filter: drop-shadow(0 6px 12px rgba(0, 0, 0, .18));
    }

    .rrhh-expediente-title {
      font-size: 1.7rem;
      line-height: 1.1;
      font-weight: 800;
      margin: .45rem 0 .25rem;
    }

    .rrhh-expediente-subtitle {
      color: rgba(255, 255, 255, .86);
      font-size: .95rem;
    }

    .rrhh-expediente-person {
      display: grid;
      grid-template-columns: 96px minmax(0, 1fr);
      gap: 1rem;
      align-items: center;
      min-width: 330px;
      border-radius: 14px;
      padding: .9rem;
      background: rgba(255, 255, 255, .13);
      backdrop-filter: blur(2px);
    }

    .rrhh-expediente-person.no-photo {
      grid-template-columns: 1fr;
      min-width: 280px;
    }

    .rrhh-expediente-photo {
      width: 86px;
      height: 100px;
      border-radius: 8px;
      overflow: hidden;
      border: 3px solid rgba(255, 255, 255, .85);
      background: rgba(255, 255, 255, .2);
    }

    .rrhh-expediente-photo.is-contain {
      background: rgba(255, 255, 255, .96);
      cursor: default;
    }

    .rrhh-expediente-photo img {
      width: 100%;
      height: 100%;
      display: block;
      user-select: none;
      -webkit-user-drag: none;
      border-radius: 0 !important;
      clip-path: none !important;
    }

    .rrhh-expediente-photo.is-contain img {
      object-fit: contain !important;
      object-position: center center !important;
      transform: none !important;
      background: #fff;
    }

    .rrhh-expediente-photo span {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.35rem;
      color: #fff;
    }

    .rrhh-expediente-photo.is-movable {
      cursor: grab;
    }

    .rrhh-expediente-photo.is-movable:active {
      cursor: grabbing;
    }

    .rrhh-expediente-toolbar {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: .75rem;
      border: 1px solid #e3e8ef;
      border-radius: 10px;
      padding: .75rem;
      margin-bottom: 1rem;
      background: #f8fafc;
    }

    .rrhh-expediente-photo-dropzone {
      border: 1px dashed #b8c6d8;
      border-radius: 10px;
      padding: .5rem .75rem;
      color: #506176;
      background: #fff;
      font-size: .82rem;
      min-width: 220px;
    }

    .rrhh-expediente-photo-dropzone.is-dragover,
    .rrhh-expediente-photo.is-dragover {
      border-color: #0054a6;
      background: #eef6ff;
    }

    .rrhh-expediente-toolbar .btn-check:checked + .btn,
    .rrhh-expediente-body {
      padding: 1.1rem;
      background: #fff;
    }

    .rrhh-expediente-section {
      border: 1px solid #e3e8ef;
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 1rem;
      break-inside: avoid;
    }

    .rrhh-expediente-section-title {
      display: flex;
      align-items: center;
      gap: .5rem;
      padding: .7rem .85rem;
      font-weight: 800;
      color: #0d2f5f;
      background: #f6f9fc;
      border-bottom: 1px solid #e3e8ef;
    }

    .rrhh-expediente-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: .75rem;
      padding: .9rem;
    }

    .rrhh-expediente-field {
      min-height: 56px;
      border-radius: 9px;
      padding: .55rem .65rem;
      background: #fbfdff;
      border: 1px solid #edf2f7;
    }

    .rrhh-expediente-label {
      display: block;
      color: #6b7280;
      font-size: .72rem;
      font-weight: 800;
      text-transform: uppercase;
      margin-bottom: .18rem;
    }

    .rrhh-expediente-value {
      color: #1f2937;
      font-size: .94rem;
      font-weight: 700;
      line-height: 1.25;
      word-break: break-word;
    }

    .rrhh-expediente-table {
      width: 100%;
      margin: 0;
      font-size: .86rem;
    }

    .rrhh-expediente-table th {
      color: #0d2f5f;
      background: #f8fafc;
      font-size: .72rem;
      text-transform: uppercase;
    }

    .rrhh-expediente-checkbox {
      width: 18px;
      height: 18px;
      accent-color: #0054a6;
      cursor: pointer;
    }

    .rrhh-expediente-signatures {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 1.25rem;
      padding: .9rem;
    }

    .rrhh-expediente-signature {
      min-height: 112px;
      border: 1px dashed #b8c6d8;
      border-radius: 10px;
      background: #fbfdff;
      text-align: center;
      color: #64748b;
      font-size: .82rem;
      padding: .65rem;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      transition: border-color .18s ease, box-shadow .18s ease;
    }

    .rrhh-expediente-signature:hover {
      border-color: #0054a6;
      box-shadow: 0 8px 18px rgba(0, 84, 166, .12);
    }

    .rrhh-expediente-signature-img {
      max-width: 100%;
      max-height: 58px;
      object-fit: contain;
      margin: 0 auto .35rem;
      display: block;
    }

    .rrhh-expediente-signature-clear {
      align-self: center;
      margin-bottom: .35rem;
      padding: .15rem .55rem;
      border-radius: 999px;
      font-size: .72rem;
      line-height: 1.2;
    }

    .rrhh-expediente-signature-line {
      width: 100%;
      border-top: 1px solid #94a3b8;
      padding-top: .45rem;
    }

    .rrhh-expediente-signature-date {
      border: 0;
      border-bottom: 1px solid #94a3b8;
      border-radius: 0;
      background: transparent;
      text-align: center;
      font-size: .9rem;
      color: #1f2937;
      box-shadow: none;
      margin-bottom: .45rem;
    }

    .rrhh-expediente-signature-date:focus {
      outline: 0;
      box-shadow: none;
      border-bottom-color: #0054a6;
    }

    .rrhh-signature-pad {
      width: 100%;
      height: 260px;
      border: 1px dashed #b8c6d8;
      border-radius: 12px;
      background: #fff;
      touch-action: none;
      cursor: crosshair;
      display: block;
    }

    .rrhh-signature-hint {
      color: #64748b;
      font-size: .88rem;
    }
    }

    @media (max-width: 991.98px) {
      .rrhh-expediente-cover {
        grid-template-columns: 1fr;
      }

      .rrhh-expediente-person {
        min-width: 0;
      }

      .rrhh-expediente-grid {
        grid-template-columns: 1fr;
      }
    }

    @media print {
      @page {
        size: letter landscape;
        margin: 10mm;
      }
      @page rrhhCredentialPage {
        size: 54mm 85.6mm;
        margin: 0;
      }
      body.print-rrhh-credencial *,
      body.print-rrhh-expediente * {
        visibility: hidden !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh,
      body.print-rrhh-credencial #modalCredencialRrhh *,
      body.print-rrhh-expediente #modalExpedienteRrhh,
      body.print-rrhh-expediente #modalExpedienteRrhh * {
        visibility: visible !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh,
      body.print-rrhh-expediente #modalExpedienteRrhh {
        position: absolute !important;
        inset: 0 auto auto 0 !important;
        width: 100% !important;
        height: auto !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .modal-dialog,
      body.print-rrhh-expediente #modalExpedienteRrhh .modal-dialog {
        max-width: 100% !important;
        margin: 0 !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .modal-content,
      body.print-rrhh-expediente #modalExpedienteRrhh .modal-content {
        border: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .modal-header,
      body.print-rrhh-credencial #modalCredencialRrhh .modal-footer,
      body.print-rrhh-expediente #modalExpedienteRrhh .modal-header,
      body.print-rrhh-expediente #modalExpedienteRrhh .modal-footer {
        display: none !important;
      }
      .rrhh-credential-stage {
        background: #fff !important;
        padding: 0 !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .modal-body {
        padding: 0 !important;
        overflow: visible !important;
      }
      body.print-rrhh-credencial .rrhh-credential-stage .row {
        display: flex !important;
        flex-wrap: nowrap !important;
        justify-content: center !important;
        align-items: flex-start !important;
        gap: 14mm !important;
        margin: 0 !important;
      }
      body.print-rrhh-credencial .rrhh-credential-stage .row > [class*="col-"] {
        flex: 0 0 auto !important;
        width: auto !important;
        max-width: none !important;
        padding: 0 !important;
      }
      .rrhh-credential-toolbar,
      body.print-rrhh-credencial .rrhh-credential-notice,
      #modalCredencialRrhh .text-muted.mb-2,
      .rrhh-expediente-toolbar {
        display: none !important;
      }
      #modalCredencialRrhh .rrhh-id-card,
      #modalExpedienteRrhh .rrhh-expediente-section {
        break-inside: avoid;
        box-shadow: none !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card {
        width: 85.6mm !important;
        height: 54mm !important;
        min-width: 85.6mm !important;
        min-height: 54mm !important;
        border-radius: 4mm !important;
        box-shadow: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card *,
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card::before,
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card::after {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card.is-horizontal .rrhh-id-card-inner {
        height: 54mm !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card:not(.is-horizontal) {
        width: 54mm !important;
        height: 85.6mm !important;
        min-width: 54mm !important;
        min-height: 85.6mm !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card:not(.is-horizontal) .rrhh-id-card-inner {
        height: 85.6mm !important;
      }
      body.print-rrhh-credencial {
        width: 54mm !important;
        min-width: 54mm !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        background: #fff !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh {
        width: 54mm !important;
        min-width: 54mm !important;
        height: auto !important;
        overflow: visible !important;
        page: rrhhCredentialPage;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .modal-dialog,
      body.print-rrhh-credencial #modalCredencialRrhh .modal-content,
      body.print-rrhh-credencial #modalCredencialRrhh .modal-body,
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-credential-stage,
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-credential-stage .row {
        width: 54mm !important;
        max-width: 54mm !important;
        min-width: 54mm !important;
        margin: 0 !important;
        padding: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        background: #fff !important;
        box-shadow: none !important;
        overflow: visible !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-credential-stage .row {
        display: block !important;
        gap: 0 !important;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-credential-stage .row > [class*="col-"] {
        display: block !important;
        width: 54mm !important;
        max-width: 54mm !important;
        height: 85.6mm !important;
        min-height: 85.6mm !important;
        padding: 0 !important;
        margin: 0 !important;
        overflow: hidden !important;
        page: rrhhCredentialPage;
        break-after: page;
        page-break-after: always;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-credential-stage .row > [class*="col-"]:last-child {
        break-after: auto;
        page-break-after: auto;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card,
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card:not(.is-horizontal) {
        width: 340px !important;
        min-width: 340px !important;
        height: 542px !important;
        min-height: 542px !important;
        margin: 0 !important;
        border-radius: 0 !important;
        transform: scale(.598);
        transform-origin: top left;
        break-inside: avoid;
        page-break-inside: avoid;
      }
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card-inner,
      body.print-rrhh-credencial #modalCredencialRrhh .rrhh-id-card:not(.is-horizontal) .rrhh-id-card-inner {
        height: 542px !important;
      }
      #modalExpedienteRrhh .modal-body {
        padding: 0 !important;
        overflow: visible !important;
        height: auto !important;
        max-height: none !important;
      }
      #modalExpedienteRrhh .rrhh-expediente {
        border: 0 !important;
        border-radius: 0 !important;
      }
      #modalExpedienteRrhh .rrhh-expediente-cover,
      #modalExpedienteRrhh .rrhh-expediente-section-title {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
      }
    }

    /* Contador en filtros */
    .filter-counter {
        display: inline-block;
        background: rgba(99, 102, 241, 0.15);
        color: #6366f1;
        padding: 0.125rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    /* Filtro activo con feedback visual mejorado */
    .form-select.filter-active {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.15) !important;
        background-color: #f0f0ff !important;
    }

    #modalEditPerfil .accordion-button {
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    #modalEditPerfil .accordion-button:not(.collapsed) {
        background: #f8f9fa;
        color: #212529;
    }

    #modalEditPerfil .table-hover tbody tr {
        transition: all 0.2s ease;
    }

    #modalEditPerfil .table-hover tbody tr:hover {
        transform: translateX(4px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    #puestos-container::-webkit-scrollbar,
    #modulos-container::-webkit-scrollbar {
        width: 8px;
    }

    #puestos-container::-webkit-scrollbar-track,
    #modulos-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #puestos-container::-webkit-scrollbar-thumb,
    #modulos-container::-webkit-scrollbar-thumb {
        background: #adb5bd;
        border-radius: 10px;
    }

    #puestos-container::-webkit-scrollbar-thumb:hover,
    #modulos-container::-webkit-scrollbar-thumb:hover {
        background: #6c757d;
    }

    /* Campo de fecha de ingreso con Flatpickr */
    .fecha-acta-wrapper {
        position: relative;
        z-index: 1;
    }

    #add_fecha_ingreso {
        width: 100%;
        cursor: pointer;
    }

    /* Ocultar flechita desplegable del mes (solo flechas prev/next) */
    .flatpickr-calendar .flatpickr-monthDropdown-months {
        appearance: none !important;
        background-image: none !important;
        -webkit-appearance: none;
        -moz-appearance: none;
    }
    /* Calendario un poco más grande; se cierra al seleccionar fecha */
    .flatpickr-calendar {
        z-index: 99999 !important;
        position: fixed !important;
        transform: scale(1.12);
        transform-origin: top left;
    }

    .flatpickr-calendar.open {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    #modalAuscencia .flatpickr-datetime-ausencia {
        cursor: pointer;
    }

    .flatpickr-calendar.ausencia-flatpickr-calendar {
        position: absolute !important;
        transform: none !important;
        transform-origin: initial !important;
        z-index: 100020 !important;
    }

    #modalAuscencia .ausencia-empty-state {
        border: 1px dashed #cfd6e4;
        border-radius: 10px;
        background: #f8fafc;
        color: #64748b;
        padding: 22px 16px;
        text-align: center;
    }

    #modalAuscencia .ausencia-empty-state i {
        color: #94a3b8;
        font-size: 22px;
        margin-bottom: 8px;
    }

    /* Hacer más visible el círculo del día de hoy */
    .flatpickr-calendar .flatpickr-day.today {
        border-color: #696cff !important;
        border-width: 2px !important;
        font-weight: 600 !important;
        background-color: #f0f0ff !important;
    }

    .flatpickr-calendar .flatpickr-day.today:hover {
        background-color: #e0e0ff !important;
        border-color: #696cff !important;
    }

    /* Estilos para Select con Búsqueda */
    .select-search-wrapper {
        position: relative;
        width: 100%;
    }

    .select-search-wrapper .form-select {
        display: none;
    }

    .select-search-display {
        position: relative;
        width: 100%;
        padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #697a8d;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .select-search-display:hover {
        border-color: #b0b7c3;
    }

    .select-search-display::after {
        content: 'â–¼';
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.75rem;
        color: #697a8d;
        pointer-events: none;
    }

    .select-search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 1050;
        display: none;
        margin-top: 0.25rem;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1);
        max-height: 300px;
        overflow: hidden;
    }

    .select-search-dropdown.show {
        display: block;
    }

    .select-search-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: none;
        border-bottom: 1px solid #d9dee3;
        font-size: 0.9375rem;
        outline: none;
    }

    .select-search-input:focus {
        border-bottom-color: #696cff;
    }

    .select-search-options {
        max-height: 250px;
        overflow-y: auto;
    }

    .select-search-option {
        padding: 0.5rem 0.75rem;
        cursor: pointer;
        transition: background-color 0.15s ease;
    }

    .select-search-option:hover {
        background-color: #f5f5f9;
    }

    .select-search-option.selected {
        background-color: #696cff;
        color: #fff;
    }

    .select-search-option.no-results {
        padding: 1rem;
        text-align: center;
        color: #999;
        cursor: default;
    }

    .select-search-option.no-results:hover {
        background-color: transparent;
    }

    /* Estilos para igualar formato de letra con select de Jefe */
    #add_departamento_id,
    #add_id_puesto,
    #add_id_legion,
    #add_nombres,
    #add_segundo_nombre,
    #add_apellidop,
    #add_apellidom,
    #add_curp,
    #add_contrasena {
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #697a8d;
    }

    /* ===== MEJORAS UX: BADGES INTERACTIVOS ===== */
    .badge-puesto-multiple {
        transition: all 0.3s ease;
        cursor: help;
    }

    .badge-puesto-multiple:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    /* Animación fade-in para tabla */
    @keyframes fadeInRow {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Indicador de múltiples puestos en acciones - MEJORADO */
    .indicator-multiples-puestos {
        position: absolute;
        top: -8px;
        right: -8px;
        background: linear-gradient(135deg, #f59e0b, #fbbf24);
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.7rem;
        color: white;
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.5);
        animation: pulseIndicator 2s infinite;
        z-index: 10;
        border: 2px solid white;
    }

    @keyframes pulseIndicator {
        0%, 100% {
            transform: scale(1);
            box-shadow: 0 2px 8px rgba(245, 158, 11, 0.5);
        }
        50% {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.7);
        }
    }

    /* Badge para puesto PRINCIPAL */
    .badge-puesto-principal {
        background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
        color: white !important;
        font-size: 0.85rem !important;
        padding: 0.5rem 0.75rem !important;
        border-radius: 0.375rem !important;
        font-weight: 600 !important;
        border: 1px solid #1e40af !important;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.25) !important;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        transition: all 0.3s ease;
    }

    .badge-puesto-principal:hover {
        background: linear-gradient(135deg, #1d4ed8, #1e40af) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3) !important;
    }

    /* Badge para puestos SECUNDARIOS */
    .badge-puesto-secundario {
        background: linear-gradient(135deg, #10B981, #34D399) !important;
        color: white !important;
        font-size: 0.75rem !important;
        padding: 0.35rem 0.6rem !important;
        border-radius: 0.375rem !important;
        font-weight: 500 !important;
        border: 1px solid #059669 !important;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.25) !important;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        transition: all 0.3s ease;
    }

    .badge-puesto-secundario:hover {
        background: linear-gradient(135deg, #059669, #10B981) !important;
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(16, 185, 129, 0.3) !important;
    }

    /* Badge para usuario con múltiples puestos */
    .badge-multipuesto-indicator {
        background: linear-gradient(135deg, #10B981, #34D399) !important;
        color: white !important;
        font-size: 0.65rem !important;
        padding: 0.25rem 0.5rem !important;
        border-radius: 0.25rem !important;
        font-weight: 600 !important;
        letter-spacing: 0.025rem;
        animation: fadeInUp 0.5s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Contenedor de puestos colapsable */
    .puestos-collapse-container {
        max-height: 200px;
        overflow: hidden;
        transition: max-height 0.4s ease;
    }

    .puestos-collapse-container.expanded {
        max-height: 1000px;
    }

    /* Botón ver más puestos */
    .btn-ver-mas-puestos {
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        background: linear-gradient(135deg, #64748b, #94a3b8);
        border: none;
        color: white;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 0.5rem;
    }

    .btn-ver-mas-puestos:hover {
        background: linear-gradient(135deg, #475569, #64748b);
        transform: translateX(2px);
    }

    /* Departamento label pequeño */
    .departamento-label {
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    body:not(.dark-mode) #historialUsuarios .departamento-label {
        color: #5d6978 !important;
    }

    body:not(.dark-mode) #historialUsuarios .badge-puesto-principal,
    body:not(.dark-mode) #historialUsuarios .badge-puesto-secundario {
        width: fit-content;
        max-width: min(100%, 360px);
        border-radius: 7px !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        text-align: left;
    }

    body:not(.dark-mode) #historialUsuarios .badge-puesto-principal {
        background: #eff6ff !important;
        color: #1e3a8a !important;
        border: 1px solid #bfdbfe !important;
    }

    body:not(.dark-mode) #historialUsuarios .badge-puesto-secundario {
        background: #ecfdf5 !important;
        color: #065f46 !important;
        border: 1px solid #a7f3d0 !important;
    }

    body:not(.dark-mode) #historialUsuarios .sede-glass-badge {
        background: #f8fafc !important;
        color: #334155 !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }

    /* Mejorar el botón con indicador */
    .btn-with-indicator {
        position: relative;
        overflow: visible !important;
    }

    #historialUsuarios td {
        vertical-align: top;
    }

    #historialUsuarios .badge-puesto-principal,
    #historialUsuarios .badge-puesto-secundario,
    #historialUsuarios .badge-multipuesto-indicator,
    #historialUsuarios .btn-ver-mas-puestos,
    #historialUsuarios .indicator-multiples-puestos,
    #historialUsuarios .btn-with-indicator {
        animation: none !important;
        transform: none !important;
        transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease !important;
        will-change: auto !important;
    }

    #historialUsuarios .badge-puesto-principal:hover,
    #historialUsuarios .badge-puesto-secundario:hover,
    #historialUsuarios .btn-ver-mas-puestos:hover {
        transform: none !important;
    }

    #historialUsuarios .badge-puesto-principal,
    #historialUsuarios .badge-puesto-secundario {
        max-width: 100%;
        white-space: normal;
        line-height: 1.2;
    }

    #historialUsuarios .badge-puesto-principal,
    #historialUsuarios .badge-puesto-secundario,
    #historialUsuarios .badge-multipuesto-indicator {
        background-image: none !important;
    }

    #historialUsuarios .btn-with-indicator {
        overflow: visible !important;
        transform: none !important;
    }

    /* ==========================================
     * ESTILOS PARA GESTIÃ“N DE MÃšLTIPLES PUESTOS EN MODAL DE EDICIÃ“N
     * ========================================== */

    /* Item de puesto en el panel de gestión */
    .puesto-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 0.75rem;
        margin-bottom: 0.5rem;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
    }

    .puesto-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .puesto-item.principal {
        border-color: #93c5fd;
        background: rgba(147, 197, 253, 0.05);
    }

    .puesto-item-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
    }

    .puesto-item-info {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
        flex: 1;
    }

    .puesto-item-departamento {
        font-size: 0.7rem;
        color: #6b7280;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .puesto-item-nombre {
        font-size: 0.8rem;
        color: #1f2937;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .puesto-item-badge {
        font-size: 0.65rem;
        padding: 0.125rem 0.4rem;
        border-radius: 0.25rem;
        font-weight: 600;
        background: #3b82f6;
        color: white;
    }

    .puesto-item-actions {
        display: flex;
        gap: 0.375rem;
    }

    .btn-eliminar-puesto {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(220, 38, 38, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(220, 38, 38, 0.4);
        color: white;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-eliminar-puesto:hover {
        background: rgba(220, 38, 38, 0.35);
        border-color: rgba(220, 38, 38, 0.6);
        transform: scale(1.05);
    }

    .btn-eliminar-puesto:disabled {
        background: #f3f4f6;
        color: #d1d5db;
        cursor: not-allowed;
    }

    .btn-editar-puesto {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fef3c7;
        color: #d97706;
        border: none;
        border-radius: 0.25rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.8rem;
    }

    .btn-editar-puesto:hover {
        background: #f59e0b;
        color: white;
    }

    /* Modo oscuro: ítems y botones de múltiples puestos */
    body.dark-mode #edit_lista_puestos {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    body.dark-mode .puesto-item {
        background: #1e293b;
        border-color: #334155;
    }
    body.dark-mode .puesto-item:hover {
        border-color: #475569;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
    }
    body.dark-mode .puesto-item.principal {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.12);
    }
    body.dark-mode .puesto-item-departamento {
        color: #94a3b8;
    }
    body.dark-mode .puesto-item-nombre {
        color: #f1f5f9;
    }
    body.dark-mode .btn-editar-puesto {
        background: rgba(245, 158, 11, 0.25);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.4);
    }
    body.dark-mode .btn-editar-puesto:hover {
        background: #f59e0b;
        color: #0f172a;
        border-color: #f59e0b;
    }
    body.dark-mode .btn-eliminar-puesto {
        background: rgba(239, 68, 68, 0.25);
        border-color: rgba(239, 68, 68, 0.5);
        color: #fca5a5;
    }
    body.dark-mode .btn-eliminar-puesto:hover {
        background: rgba(239, 68, 68, 0.45);
        border-color: rgba(239, 68, 68, 0.7);
        color: #fff;
    }
    body.dark-mode .btn-eliminar-puesto:disabled {
        background: #334155;
        color: #475569;
        border-color: #475569;
    }
    body.dark-mode .no-puestos-message {
        color: #94a3b8;
    }

    /* Panel de agregar puesto */
    #edit_panel_agregar_puesto {
        animation: slideDown 0.3s ease;
    }

    /* Panel de editar puesto */
    #edit_panel_editar_puesto {
        animation: slideDown 0.3s ease;
        border-width: 2px !important;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Scrollbar personalizado para lista de puestos */
    #edit_lista_puestos::-webkit-scrollbar {
        width: 8px;
    }

    #edit_lista_puestos::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #edit_lista_puestos::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    #edit_lista_puestos::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* Mensaje cuando no hay puestos */
    .no-puestos-message {
        text-align: center;
        padding: 2rem;
        color: #9ca3af;
        font-size: 0.875rem;
    }

    .no-puestos-message i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
    }

    /* Mejora en filtros - feedback visual */
    .filter-active {
        border: 2px solid #10b981 !important;
        box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.25) !important;
    }

    /* ===== INDICADORES / KPIs ===== */
    .kpi-wrapper {
        display: flex;
        justify-content: center;
        align-items: stretch;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .kpi-item {
        flex: 0 1 auto;
        min-width: 120px;
        max-width: 150px;
    }

    /* =====================================================
       KPI PANEL — REDISEÃ‘O v3
       Modos: Estándar | Visión (donut) | Mini-stat
       ===================================================== */

    /* â”€â”€ Toolbar â”€â”€ */
    .kpi-toolbar { display:flex; align-items:center; gap:0.5rem; margin-bottom:0.65rem; flex-wrap:wrap; }

    .kpi-toggle-btn {
        display:inline-flex; align-items:center; gap:0.35rem;
        background:#fff; border:1px solid rgba(99,102,241,0.18); border-radius:8px;
        padding:0.38rem 0.85rem; cursor:pointer;
        font-size:0.78rem; font-weight:600; color:#6366f1;
        box-shadow:0 1px 4px rgba(99,102,241,0.07);
        transition:background 0.2s, box-shadow 0.2s;
        user-select:none; white-space:nowrap;
    }
    .kpi-toggle-btn:hover { background:rgba(99,102,241,0.06); box-shadow:0 2px 10px rgba(99,102,241,0.13); }
    .kpi-toggle-btn .kpi-chevron { font-size:1rem; transition:transform 0.35s cubic-bezier(0.4,0,0.2,1); display:flex; }
    .kpi-toggle-btn.open .kpi-chevron { transform:rotate(180deg); }
    .kpi-toggle-btn .kpi-dot { width:6px; height:6px; border-radius:50%; background:#6366f1; flex-shrink:0; }

    .kpi-toolbar-sep { width:1px; height:20px; background:rgba(99,102,241,0.12); flex-shrink:0; transition:opacity 0.28s ease, transform 0.33s cubic-bezier(0.4,0,0.2,1); }
    .kpi-toolbar-sep.kpi-sep-hidden { opacity:0; transform:scaleY(0); pointer-events:none; }

    #kpiViewControls, #kpiViewControlsB {
        display:flex; align-items:center; gap:0.5rem; flex-wrap:nowrap;
        overflow:hidden;
        max-width:700px; opacity:1; transform:translateX(0);
        transition:max-width 0.38s cubic-bezier(0.4,0,0.2,1),
                   opacity    0.28s ease,
                   transform  0.33s cubic-bezier(0.4,0,0.2,1);
    }
    #kpiViewControls.kpi-vc-hidden, #kpiViewControlsB.kpi-vc-hidden {
        max-width:0; opacity:0; transform:translateX(-22px); pointer-events:none;
    }

    .kpi-view-btn {
        display:inline-flex; align-items:center; justify-content:center; gap:0.3rem;
        min-width:32px; height:32px; background:#fff; padding:0 0.5rem;
        border:1px solid rgba(99,102,241,0.12); border-radius:7px;
        cursor:pointer; color:#6b7280; font-size:0.75rem; font-weight:600;
        transition:all 0.2s; user-select:none; position:relative;
    }
    .kpi-view-btn:hover { background:rgba(99,102,241,0.06); color:#6366f1; border-color:rgba(99,102,241,0.3); }
    .kpi-view-btn.active { background:#6366f1; border-color:#6366f1; color:white; box-shadow:0 2px 8px rgba(99,102,241,0.3); }
    .kpi-view-btn .kpi-btn-text { font-size:0.7rem; font-weight:600; white-space:nowrap; }
    .kpi-view-btn.active .kpi-btn-text { color:white; }
    .kpi-view-btn::after {
        content:attr(data-tip); position:absolute; bottom:calc(100% + 6px); left:50%; transform:translateX(-50%);
        background:#1e1b4b; color:#f4f5fb; font-size:0.65rem; font-weight:600;
        white-space:nowrap; padding:0.25rem 0.5rem; border-radius:5px;
        opacity:0; pointer-events:none; transition:opacity 0.15s; z-index:200;
    }
    .kpi-view-btn:hover::after { opacity:1; }

    .kpi-reset-btn {
        display:inline-flex; align-items:center; gap:0.3rem;
        background:transparent; border:1px solid transparent; border-radius:7px;
        padding:0.38rem 0.6rem; cursor:pointer;
        font-size:0.72rem; font-weight:600; color:#6b7280;
        transition:all 0.2s; user-select:none;
    }
    .kpi-reset-btn:hover { color:#ef4444; border-color:rgba(239,68,68,0.25); background:rgba(239,68,68,0.05); }

    /* â”€â”€ Panel colapsable â”€â”€ */
    .kpi-collapsible { display:grid; grid-template-rows:0fr; transition:grid-template-rows 0.4s cubic-bezier(0.4,0,0.2,1), opacity 0.35s ease; opacity:0; }
    .kpi-collapsible.open { grid-template-rows:1fr; opacity:1; }
    .kpi-collapsible-inner { overflow:hidden; }

    /* â”€â”€ Fila de 3 celdas â”€â”€ */
    .kpi-row-new { display:grid; grid-template-columns:repeat(3,1fr); gap:0.55rem; padding-bottom:0.2rem; }

    /* â”€â”€ Celda base â”€â”€ */
    .kpi-cell {
        background:#fff; border-radius:11px;
        border:1px solid rgba(99,102,241,0.12);
        box-shadow:0 2px 12px rgba(99,102,241,0.07),0 1px 3px rgba(0,0,0,0.035),
                   inset 3px 0 0 var(--cell-accent);
        position:relative; overflow:hidden; cursor:pointer;
        min-height:152px;
        transition:transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s, border-color 0.25s;
        opacity:0; transform:translateY(14px);
    }
    /* Misma altura base en los 3 modos (Estándar / Donut / Mini-Stat) y columna flexible */
    .kpi-row-new > .kpi-cell {
        display: flex;
        flex-direction: column;
    }
    .kpi-cell.revealed { animation:kpiCellReveal 0.45s cubic-bezier(0.34,1.3,0.64,1) forwards; }
    @keyframes kpiCellReveal {
        from { opacity:0; transform:translateY(14px) scale(0.97); }
        to   { opacity:1; transform:translateY(0) scale(1); }
    }
    .kpi-cell:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(99,102,241,0.12),0 2px 6px rgba(0,0,0,0.05),inset 3px 0 0 var(--cell-accent); border-color:rgba(99,102,241,0.28); }
    .kpi-cell::after {
        content:''; position:absolute; bottom:0; left:0; right:0; height:3px;
        border-radius:0 0 14px 14px; background:var(--cell-accent);
        transform:scaleX(0); transform-origin:left; transition:transform 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    .kpi-cell:hover::after { transform:scaleX(1); }
    .kpi-cell::before {
        content:''; position:absolute; inset:0;
        background:radial-gradient(ellipse at 30% 50%, var(--cell-glow) 0%, transparent 70%);
        opacity:0; transition:opacity 0.4s; pointer-events:none;
    }
    .kpi-cell:hover::before { opacity:1; }

    /* Colores por tipo */
    .kpi-cell.tipo-dep    { --cell-accent:#6366f1; --cell-glow:rgba(99,102,241,0.06);  --cell-icon:#6366f1; --cell-num:#4f46e5; }
    .kpi-cell.tipo-puesto { --cell-accent:#10b981; --cell-glow:rgba(16,185,129,0.06); --cell-icon:#10b981; --cell-num:#059669; }
    .kpi-cell.tipo-total  { --cell-accent:#f59e0b; --cell-glow:rgba(245,158,11,0.07);  --cell-icon:#f59e0b; --cell-num:#d97706; }

    /* â”€â”€ Elementos comunes â”€â”€ */
    .kpi-cell-top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:0.5rem; }
    /* â”€â”€ kpi-icon-wrap: círculo coloreado, estilo __SPARTA_SECRET_REDACTED__ â”€â”€ */
    .kpi-icon-wrap {
      border-radius:50%;
      display:flex; align-items:center; justify-content:center; flex-shrink:0;
      transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s;
    }
    /* Forzar que los <i> de Boxicons se dibujen (font + display) */
    .kpi-icon-wrap i {
      font-family:'boxicons' !important;
      font-style:normal;
      font-weight:400 !important;
      font-size:inherit !important;
      color:inherit !important;
      line-height:1;
      display:inline-block !important;
      text-shadow:0 0 8px currentColor;
      position:relative;
      z-index:1;
    }

    /* Colores por tipo — light mode */
    .kpi-cell.tipo-dep    .kpi-icon-wrap { background:#ede9fe; border:1px solid #c4b5fd; color:#6366f1; box-shadow:0 3px 10px rgba(99,102,241,0.28); }
    .kpi-cell.tipo-puesto .kpi-icon-wrap { background:#d1fae5; border:1px solid #6ee7b7; color:#059669; box-shadow:0 3px 10px rgba(16,185,129,0.28); }
    .kpi-cell.tipo-total  .kpi-icon-wrap { background:#fef3c7; border:1px solid #fcd34d; color:#d97706; box-shadow:0 3px 10px rgba(245,158,11,0.28); }

    /* Tamaño del icono según modo */
    .kpi-row-new.mode-default  .kpi-icon-wrap i { font-size:1.05rem !important; display:inline-block !important; }
    .kpi-row-new.mode-ministat .kpi-icon-wrap i { font-size:0.88rem !important; display:inline-block !important; }

    /* Hover */
    .kpi-cell.tipo-dep:hover    .kpi-icon-wrap { transform:scale(1.12) rotate(-4deg); box-shadow:0 6px 18px rgba(99,102,241,0.45); }
    .kpi-cell.tipo-puesto:hover .kpi-icon-wrap { transform:scale(1.12) rotate(-4deg); box-shadow:0 6px 18px rgba(16,185,129,0.45); }
    .kpi-cell.tipo-total:hover  .kpi-icon-wrap { transform:scale(1.12) rotate(-4deg); box-shadow:0 6px 18px rgba(245,158,11,0.45); }

    /* Dark mode */
    body.dark-mode .kpi-cell.tipo-dep    .kpi-icon-wrap { background:rgba(99,102,241,0.18); border-color:rgba(99,102,241,0.4); color:#a5b4fc !important; box-shadow:0 3px 10px rgba(99,102,241,0.35); }
    body.dark-mode .kpi-cell.tipo-puesto .kpi-icon-wrap { background:rgba(16,185,129,0.18); border-color:rgba(16,185,129,0.4); color:#6ee7b7 !important; box-shadow:0 3px 10px rgba(16,185,129,0.35); }
    body.dark-mode .kpi-cell.tipo-total  .kpi-icon-wrap { background:rgba(245,158,11,0.18); border-color:rgba(245,158,11,0.4); color:#fcd34d !important; box-shadow:0 3px 10px rgba(245,158,11,0.35); }
    body.dark-mode .kpi-cell.tipo-dep    .kpi-icon-wrap i { color:#a5b4fc !important; }
    body.dark-mode .kpi-cell.tipo-puesto .kpi-icon-wrap i { color:#6ee7b7 !important; }
    body.dark-mode .kpi-cell.tipo-total  .kpi-icon-wrap i { color:#fcd34d !important; }

    /* â•â•â• KPI células Bajas — paleta roja / naranja / morada â•â•â• */
    .kpi-cell.tipo-baja-total  { --cell-accent:#ef4444; --cell-glow:rgba(239,68,68,0.07);   --cell-icon:#ef4444; --cell-num:#dc2626; }
    .kpi-cell.tipo-baja-dep    { --cell-accent:#f97316; --cell-glow:rgba(249,115,22,0.07);  --cell-icon:#f97316; --cell-num:#ea580c; }
    .kpi-cell.tipo-baja-puesto { --cell-accent:#8b5cf6; --cell-glow:rgba(139,92,246,0.07); --cell-icon:#8b5cf6; --cell-num:#7c3aed; }
    .kpi-cell.tipo-baja-total  .kpi-icon-wrap { background:#fee2e2; border:1px solid #fca5a5; color:#dc2626; box-shadow:0 3px 10px rgba(239,68,68,0.28); }
    .kpi-cell.tipo-baja-dep    .kpi-icon-wrap { background:#ffedd5; border:1px solid #fdba74; color:#ea580c; box-shadow:0 3px 10px rgba(249,115,22,0.28); }
    .kpi-cell.tipo-baja-puesto .kpi-icon-wrap { background:#ede9fe; border:1px solid #c4b5fd; color:#7c3aed; box-shadow:0 3px 10px rgba(139,92,246,0.28); }
    .kpi-cell.tipo-baja-total:hover  .kpi-icon-wrap { transform:scale(1.12) rotate(-4deg); box-shadow:0 6px 18px rgba(239,68,68,0.45); }
    .kpi-cell.tipo-baja-dep:hover    .kpi-icon-wrap { transform:scale(1.12) rotate(-4deg); box-shadow:0 6px 18px rgba(249,115,22,0.45); }
    .kpi-cell.tipo-baja-puesto:hover .kpi-icon-wrap { transform:scale(1.12) rotate(-4deg); box-shadow:0 6px 18px rgba(139,92,246,0.45); }
    body.dark-mode .kpi-cell.tipo-baja-total  .kpi-icon-wrap { background:rgba(239,68,68,0.18);  border-color:rgba(239,68,68,0.4);  color:#fca5a5 !important; box-shadow:0 3px 10px rgba(239,68,68,0.35); }
    body.dark-mode .kpi-cell.tipo-baja-dep    .kpi-icon-wrap { background:rgba(249,115,22,0.18); border-color:rgba(249,115,22,0.4); color:#fdba74 !important; box-shadow:0 3px 10px rgba(249,115,22,0.35); }
    body.dark-mode .kpi-cell.tipo-baja-puesto .kpi-icon-wrap { background:rgba(139,92,246,0.18); border-color:rgba(139,92,246,0.4); color:#c4b5fd !important; box-shadow:0 3px 10px rgba(139,92,246,0.35); }
    body.dark-mode .kpi-cell.tipo-baja-total  .kpi-icon-wrap i { color:#fca5a5 !important; }
    body.dark-mode .kpi-cell.tipo-baja-dep    .kpi-icon-wrap i { color:#fdba74 !important; }
    body.dark-mode .kpi-cell.tipo-baja-puesto .kpi-icon-wrap i { color:#c4b5fd !important; }

    /* Indicadores de Bajas: tarjetas más compactas (solo panel bajas; no afecta KPI empleados) */
    #panelIndicadoresBajas #kpiRowNewB {
        gap: 0.45rem;
    }
    #panelIndicadoresBajas #kpiRowNewB .kpi-cell {
        border-radius: 9px;
        padding: 0.5rem 0.72rem 0.48rem;
        box-shadow: 0 1px 10px rgba(99, 102, 241, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04),
            inset 3px 0 0 var(--cell-accent);
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-default .kpi-cell,
    #panelIndicadoresBajas #kpiRowNewB.mode-ministat .kpi-cell,
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .kpi-cell {
        padding: 0.5rem 0.72rem 0.48rem;
    }
    #panelIndicadoresBajas #kpiRowNewB .kpi-cell-top {
        margin-bottom: 0.35rem;
    }
    #panelIndicadoresBajas #kpiRowNewB .kpi-cell-status {
        font-size: 0.55rem;
        padding: 0.12rem 0.45rem;
    }
    #panelIndicadoresBajas #kpiRowNewB .kpi-num {
        font-size: 1.12rem;
    }
    #panelIndicadoresBajas #kpiRowNewB .kpi-lbl {
        font-size: 0.66rem;
        margin-top: 0.12rem;
    }
    #panelIndicadoresBajas #kpiRowNewB .kpi-bar-track {
        margin-top: 0.42rem;
        height: 2px;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-svg-wrap,
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-svg {
        width: 56px;
        height: 56px;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-svg-wrap .donut-center-icon i {
        font-size: 0.95rem !important;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-ministat .kpi-stat-val {
        font-size: 1.02rem;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-ministat .kpi-stat-div {
        height: 22px;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-ministat .kpi-stats-grid-new {
        margin-top: 0.32rem;
    }

    /* Donut (Bajas): métrica izquierda | divisor | círculo + métrica derecha; sin badge duplicado bajo el SVG */
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas {
        width: 100%;
        align-self: stretch;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-bajas-body {
        display: flex;
        width: 100%;
        align-items: center;
        gap: 0.32rem;
        margin-top: 0.12rem;
        padding-top: 0.32rem;
        border-top: 1px solid color-mix(in srgb, var(--cell-icon) 14%, transparent);
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-bajas-col-left {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-bajas-div {
        flex-shrink: 0;
        width: 1px;
        height: 32px;
        align-self: center;
        background: linear-gradient(
            180deg,
            transparent,
            color-mix(in srgb, var(--cell-icon) 28%, transparent),
            transparent
        );
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-bajas-col-right {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 0.32rem;
        flex-shrink: 0;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-bajas-col-right .kpi-stat-item {
        padding: 0 0.1rem;
        text-align: center;
        min-width: 0;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .kpi-stat-val {
        font-size: 1.02rem;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .kpi-stat-lbl {
        font-size: 0.52rem;
        margin-top: 0.08rem;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-bajas-col-left .kpi-stat-item {
        padding: 0 0.1rem;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-ctx-line {
        width: 100%;
        font-size: 0.54rem;
        font-weight: 600;
        color: #6b7280;
        text-align: center;
        line-height: 1.25;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.25rem;
        min-height: 1.15em;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-ctx-line i {
        flex-shrink: 0;
        color: var(--cell-icon);
        opacity: 0.9;
    }
    #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-ctx-line > span {
        overflow: hidden;
        text-overflow: ellipsis;
        min-width: 0;
    }
    body.dark-mode #panelIndicadoresBajas #kpiRowNewB.mode-vision .donut-block-bajas .donut-ctx-line {
        color: #9ca3af;
    }

    /* â”€â”€ Icono decorativo esquina superior izquierda â”€â”€ */
    .kpi-corner-icon {
        position:absolute; top:4px; left:6px;
        font-size:3rem; line-height:1;
        color:var(--cell-icon); opacity:0.07;
        pointer-events:none; user-select:none; z-index:0;
        transition:opacity 0.4s ease, transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
    }
    .kpi-cell:hover .kpi-corner-icon { opacity:0.13; transform:scale(1.08) rotate(-6deg); }
    .kpi-row-new.mode-vision .kpi-corner-icon { display:none !important; }
    .kpi-cell-status {
        font-size:0.56rem; font-weight:600; letter-spacing:0.06em; text-transform:uppercase;
        color:var(--cell-icon); background:color-mix(in srgb,var(--cell-icon) 10%,transparent);
        border-radius:16px; padding:0.14rem 0.45rem; opacity:0.85;
    }
    .kpi-num { font-size:1.42rem; font-weight:700; line-height:1; color:var(--cell-num); }
    .kpi-lbl { font-size:0.66rem; font-weight:500; color:#6b7280; margin-top:0.22rem; }
    .kpi-bar-track { margin-top:0.55rem; height:2px; background:color-mix(in srgb,var(--cell-icon) 12%,transparent); border-radius:99px; overflow:hidden; }
    .kpi-bar-fill  { height:100%; width:0%; background:var(--cell-icon); border-radius:99px; transition:width 1s cubic-bezier(0.4,0,0.2,1) 0.3s; }

    /* â•â•â•â• MODO ESTÁNDAR â•â•â•â• */
    .kpi-row-new.mode-default .kpi-cell { padding:0.8rem 0.95rem 0.72rem; }
    /* Ocultar icono y círculo en modo Estándar */
    .kpi-row-new.mode-default .kpi-icon-wrap { display:none !important; }
    .kpi-row-new.mode-default .kpi-corner-icon { display:none !important; }
    .kpi-row-new.mode-default .donut-block  { display:none !important; }
    .kpi-row-new.mode-default .kpi-stats-grid-new { display:none !important; }

    /* â•â•â•â• MODO MINI-STAT â•â•â•â• */
    .kpi-row-new.mode-ministat .kpi-cell { padding:0.8rem 0.95rem 0.72rem; }
    .kpi-row-new.mode-ministat .kpi-cell-top { margin-bottom:0.5rem; }
    .kpi-row-new.mode-ministat .kpi-cell-top .kpi-cell-status { display:none; }
    /* Ocultar icono y círculo en modo Mini-Stat */
    .kpi-row-new.mode-ministat .kpi-icon-wrap { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-corner-icon { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-cell-title { font-size:0.62rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.03em; align-self:center; display:block; margin-bottom:0.15rem; }
    .kpi-row-new.mode-ministat .kpi-num      { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-lbl      { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-bar-track{ display:block !important; margin-top:auto; padding-top:0.42rem; }
    .kpi-row-new.mode-ministat .donut-block  { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-stats-grid-new { display:grid !important; }
    /* % badge below B-value */
    .kpi-ms-pct { font-size:0.56rem; font-weight:600; color:#9ca3af; margin-top:0.12rem; line-height:1.2; }
    .kpi-ms-pct.ok     { color:#10b981; }
    .kpi-ms-pct.warn   { color:#f59e0b; }
    .kpi-ms-pct.danger { color:#ef4444; }
    body.dark-mode .kpi-ms-pct        { color:#6b7280; }
    body.dark-mode .kpi-ms-pct.ok     { color:#34d399; }
    body.dark-mode .kpi-ms-pct.warn   { color:#fbbf24; }
    body.dark-mode .kpi-ms-pct.danger { color:#f87171; }
    .kpi-cell-title { display:none; }
    .kpi-stats-grid-new { display:none; grid-template-columns:1fr 1px 1fr; align-items:center; }
    .kpi-stat-item { padding:0 0.5rem; }
    .kpi-stat-item:first-child { padding-left:0; }
    /* Mini-stat empleados: total + ingresos/bajas */
    .kpi-stats-emp { display:none; align-items:center; gap:0.35rem; }
    .kpi-row-new.mode-ministat .kpi-stats-emp { display:flex !important; }
    .kpi-emp-total-wrap { display:flex; flex-direction:column; justify-content:center; flex:0 0 auto; }
    .kpi-emp-total-val { font-size:1.48rem; font-weight:700; color:var(--cell-num); line-height:1; }
    .kpi-emp-total-lbl { font-size:0.55rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.04em; }
    .kpi-emp-div { width:1px; height:30px; background:linear-gradient(180deg,transparent,rgba(99,102,241,0.18),transparent); flex-shrink:0; }
    .kpi-emp-side { display:flex; flex-direction:column; gap:0.3rem; flex:1; padding-left:0.3rem; }
    .kpi-emp-row { display:flex; align-items:center; gap:0.25rem; }
    .kpi-emp-arrow-up   { color:#10b981; font-size:0.95rem; line-height:1; display:flex; }
    .kpi-emp-arrow-down { color:#ef4444; font-size:0.95rem; line-height:1; display:flex; }
    .kpi-emp-num { font-size:0.86rem; font-weight:700; color:var(--cell-num); line-height:1; min-width:1.5ch; }
    .kpi-emp-side-lbl { font-size:0.54rem; color:#6b7280; font-weight:500; }
    .kpi-stat-ingr { color:#10b981 !important; }
    .kpi-ingr-arrow { color:#10b981; font-size:0.65rem; vertical-align:middle; display:inline-flex; align-items:center; }
    .kpi-stat-val { font-size:1.32rem; font-weight:700; color:var(--cell-num); line-height:1; display:flex; align-items:center; gap:0.1rem; }
    .kpi-stat-lbl { font-size:0.56rem; font-weight:500; color:#6b7280; margin-top:0.14rem; }
    .kpi-stat-div { width:1px; height:30px; align-self:center; background:linear-gradient(180deg,transparent,rgba(99,102,241,0.15),transparent); }
    .kpi-stats-grid-new { display:none; grid-template-columns:1fr 1px 1fr; align-items:center; margin-top:0.42rem; }
    .kpi-arrow-up   { color:#10b981; font-size:0.95rem; line-height:1; display:flex; align-items:center; }
    .kpi-arrow-down { color:#ef4444; font-size:0.95rem; line-height:1; display:flex; align-items:center; }

    /* â•â•â•â• MODO VISIÃ“N (donut) â•â•â•â• */
    .kpi-row-new.mode-vision .kpi-cell { padding:0.8rem 0.95rem 0.72rem; }
    .kpi-row-new.mode-vision .kpi-cell-top    { display:none !important; }
    .kpi-row-new.mode-vision .kpi-num         { display:none !important; }
    .kpi-row-new.mode-vision .kpi-lbl         { display:none !important; }
    .kpi-row-new.mode-vision .kpi-bar-track   { display:none !important; }
    .kpi-row-new.mode-vision .kpi-stats-grid-new { display:none !important; }
    .kpi-row-new.mode-vision .donut-block {
        display:flex !important; flex-direction:column; align-items:center; gap:0.4rem;
        flex:1; justify-content:center; min-height:0;
    }
    .donut-block { display:none; }

    .donut-header { width:100%; display:flex; align-items:center; justify-content:space-between; }
    .donut-title  { font-size:0.6rem; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; }
    .donut-svg-wrap {
        position:relative; display:inline-flex; align-items:center; justify-content:center;
        width:64px; height:64px;
    }
    .donut-svg { width:64px; height:64px; transform:rotate(-90deg); overflow:visible; }
    .donut-track { fill:none; stroke:color-mix(in srgb,var(--cell-icon) 12%,transparent); stroke-width:7; stroke-linecap:round; }
    .donut-arc {
        fill:none; stroke:var(--cell-icon); stroke-width:7; stroke-linecap:round;
        stroke-dasharray:0 226.2;
        transition:stroke-dasharray 1.1s cubic-bezier(0.4,0,0.2,1);
        filter:drop-shadow(0 0 4px color-mix(in srgb,var(--cell-icon) 40%,transparent));
    }
    .kpi-cell:hover .donut-arc {
        filter:drop-shadow(0 0 6px color-mix(in srgb,var(--cell-icon) 55%,transparent));
        stroke-width:8;
    }
    .donut-center-icon {
        position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
        font-size:1rem; color:var(--cell-icon);
        transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }
    .kpi-cell:hover .donut-center-icon { transform:scale(1.18); }
    .donut-footer { display:flex; align-items:center; justify-content:center; gap:0.35rem; width:100%; }
    .donut-stats { display:grid; grid-template-columns:1fr 1px 1fr; align-items:center; width:100%; margin-top:0.25rem; padding-top:0.28rem; border-top:1px solid color-mix(in srgb,var(--cell-icon) 12%,transparent); }
    .kpi-row-new.mode-vision .donut-stats .kpi-stat-val { font-size: 1.08rem; }
    .kpi-row-new.mode-vision .donut-stats .kpi-stat-lbl { font-size: 0.52rem; margin-top: 0.1rem; }
    .donut-trend {
        display:inline-flex; align-items:center; gap:0.15rem;
        font-size:0.6rem; font-weight:700; padding:0.14rem 0.4rem; border-radius:4px;
    }
    .donut-trend.up     { color:#059669; background:rgba(16,185,129,0.1); }
    .donut-trend.down   { color:#dc2626; background:rgba(239,68,68,0.1); }
    .donut-trend.steady { color:var(--cell-icon); background:color-mix(in srgb,var(--cell-icon) 10%,transparent); }
    .donut-trend-label  { font-size:0.58rem; color:#6b7280; font-weight:500; }

    /* â”€â”€ Tooltip del donut â”€â”€ */
    .kpi-donut-tooltip {
        position:fixed; z-index:9999; pointer-events:none;
        background:#1e1b4b; color:#f4f5fb;
        font-size:0.7rem; font-weight:600;
        padding:0.3rem 0.65rem; border-radius:7px;
        box-shadow:0 4px 16px rgba(0,0,0,0.18);
        white-space:nowrap; opacity:0; transform:translateY(4px);
        transition:opacity 0.15s, transform 0.15s;
    }
    .kpi-donut-tooltip.visible { opacity:1; transform:translateY(0); }
    .kpi-donut-tooltip::after {
        content:''; position:absolute; top:100%; left:50%; transform:translateX(-50%);
        border:5px solid transparent; border-top-color:#1e1b4b;
    }

    /* â”€â”€ Swap animation â”€â”€ */
    @keyframes kpiModeFade { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
    .kpi-cell.mode-swap { animation:kpiModeFade 0.32s ease forwards; }

    /* â”€â”€ Dark mode â”€â”€ */
    body.dark-mode .kpi-cell             { background:#1a1d2e; border-color:rgba(99,102,241,0.18); box-shadow:0 2px 14px rgba(0,0,0,0.32),0 1px 3px rgba(0,0,0,0.18),inset 3px 0 0 var(--cell-accent); }
    body.dark-mode .kpi-toggle-btn,
    body.dark-mode .kpi-view-btn         { background:#1a1d2e; }
    body.dark-mode .kpi-donut-tooltip    { background:#e8eaff; color:#0f1117; }
    body.dark-mode .kpi-donut-tooltip::after { border-top-color:#e8eaff; }
    body.dark-mode .kpi-lbl,
    body.dark-mode .kpi-stat-lbl,
    body.dark-mode .kpi-emp-total-lbl,
    body.dark-mode .kpi-emp-side-lbl,
    body.dark-mode .donut-title,
    body.dark-mode .donut-trend-label,
    body.dark-mode .kpi-cell-title       { color:#8b90b0; }
    body.dark-mode .kpi-stat-ingr        { color:#10b981 !important; }
    body.dark-mode .kpi-ingr-arrow       { color:#10b981 !important; }

    /* Celda clickeable */
    .kpi-cell { cursor: pointer; }
    .kpi-cell::after { content:''; position:absolute; inset:0; border-radius:inherit; background:transparent; transition:background 0.18s; pointer-events:none; }
    .kpi-cell:active::after { background:rgba(0,0,0,0.06); }

    /* â”€â”€ Responsive móvil â”€â”€ */
    @media (max-width: 767px) {
        .kpi-row-new { grid-template-columns:1fr 1fr; }
        .kpi-toolbar { gap:0.4rem; }
        .kpi-view-btn { padding:0 0.3rem; font-size:0.65rem; }
        .kpi-view-btn .kpi-btn-text { display:none; } /* Ocultar texto en móvil, solo íconos */
        .kpi-num { font-size:1.22rem; }
    }
    @media (max-width: 480px) {
        .kpi-row-new { grid-template-columns:1fr; }
    }

    /* â”€â”€ Conservar estilos de filtros rápidos â”€â”€ */
    .btn-filtro-rapido {
        transition: all 0.3s ease; font-size: 0.9rem;
        padding: 0.5rem 1rem; border-width: 2px;
    }
    .btn-filtro-rapido:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
    .btn-filtro-rapido.active { background-color:#0d6efd !important; color:white !important; border-color:#0d6efd !important; box-shadow:0 4px 12px rgba(13,110,253,0.3); }
    .btn-filtro-rapido.btn-outline-success.active { background-color:#198754 !important; border-color:#198754 !important; box-shadow:0 4px 12px rgba(25,135,84,0.3); }

    /* ===== MODAL DE DESGLOSE ===== */
    .stat-card {
        background: #f8fafc;
        border-left: 4px solid;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 0.375rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .stat-card[data-color="indigo"] {
        border-left-color: #4F46E5;
    }

    .stat-card[data-color="emerald"] {
        border-left-color: #10B981;
    }

    .stat-card[data-color="purple"] {
        border-left-color: #8B5CF6;
    }

    .stat-card[data-color="amber"] {
        border-left-color: #F59E0B;
    }

    .stat-card[data-color="red"] {
        border-left-color: #EF4444;
    }

    .stat-card[data-color="orange"] {
        border-left-color: #F97316;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-top: 0.25rem;
    }

    .progress-bar-kpi {
        transition: width 0.6s ease;
    }

    /* Headers de tabla con colores temáticos */
    .table-header-indigo {
        background: linear-gradient(135deg, #4F46E5, #6366F1) !important;
        color: white !important;
    }

    .table-header-emerald {
        background: linear-gradient(135deg, #10B981, #34D399) !important;
        color: white !important;
    }

    .table-header-purple {
        background: linear-gradient(135deg, #8B5CF6, #A78BFA) !important;
        color: white !important;
    }

    .table-header-amber {
        background: linear-gradient(135deg, #F59E0B, #FBBF24) !important;
        color: white !important;
    }

    .table-header-red {
        background: linear-gradient(135deg, #EF4444, #F87171) !important;
        color: white !important;
    }

    .badge-count {
        font-size: 0.85rem;
        padding: 0.35em 0.65em;
    }

    /* ============================================================
   RESPONSIVIDAD MODAL #modalEditPerfil
   Optimizado para smartphones: 360x780 â†’ 393x852
   ============================================================ */

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   BASE MÃ“VIL: aplica desde 0 hasta 575px
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@media (max-width: 575.98px) {

  /* â”€â”€ 1. DIÁLOGO: ocupa toda la pantalla como sheet nativo â”€â”€ */
  #modalEditPerfil .modal-dialog {
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    height: 100% !important;                  /* full height */
    min-height: 100% !important;
    display: flex !important;
    align-items: flex-end !important;         /* sube desde abajo (bottom-sheet) */
  }

  /* â”€â”€ 2. CONTENT: ocupa todo el ancho y altura máxima â”€â”€ */
  #modalEditPerfil .modal-content {
    border-radius: 1.25rem 1.25rem 0 0 !important;   /* esquinas solo arriba */
    width: 100% !important;
    max-height: 92vh !important;              /* deja espacio para ver contexto */
    height: auto !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
  }

  /* â”€â”€ 3. HEADER: compacto, sin desbordamiento â”€â”€ */
  #modalEditPerfil .modal-header {
    padding: 1rem 1rem 0.75rem !important;
    flex-shrink: 0 !important;
  }

  #modalEditPerfil .modal-header .d-flex {
    flex-wrap: nowrap !important;
    gap: 0.5rem !important;
    align-items: flex-start !important;
  }

  #modalEditPerfil .modal-title {
    font-size: 0.95rem !important;
    line-height: 1.3 !important;
    margin-bottom: 0.15rem !important;
  }

  #modalEditPerfil #modalEditPerfil_subtitle {
    font-size: 0.72rem !important;
    line-height: 1.2 !important;
  }

  /* Botón cerrar: tamaño táctil mínimo 44px */
  #modalEditPerfil .modal-header button[data-bs-dismiss="modal"] {
    min-width: 44px !important;
    min-height: 44px !important;
    width: 44px !important;
    height: 44px !important;
    flex-shrink: 0 !important;
    padding: 0 !important;
    border-radius: 50% !important;
  }

  /* â”€â”€ 4. BODY: scroll interno, sin overflow roto â”€â”€ */
  #modalEditPerfil .modal-body {
    overflow-y: auto !important;
    overflow-x: hidden !important;
    flex: 1 1 auto !important;
    padding: 0 !important;
    -webkit-overflow-scrolling: touch !important;  /* scroll suave iOS */
  }

  /* â”€â”€ 5. TABS: scroll horizontal si no caben, nunca wrap â”€â”€ */
  #modalEditPerfil .nav-tabs-custom {
    display: flex !important;
    flex-wrap: nowrap !important;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    -webkit-overflow-scrolling: touch !important;
    scrollbar-width: none !important;              /* ocultar scrollbar en Firefox */
    padding: 0 0.75rem !important;
    border-bottom: 2px solid #e9ecef !important;
    gap: 0 !important;
    background: #fff !important;
    position: sticky !important;
    top: 0 !important;
    z-index: 10 !important;
  }

  #modalEditPerfil .nav-tabs-custom::-webkit-scrollbar {
    display: none !important;                      /* ocultar scrollbar en Chrome/Safari */
  }

  #modalEditPerfil .nav-tabs-custom .nav-item {
    flex-shrink: 0 !important;
  }

  #modalEditPerfil .nav-tabs-custom .nav-link {
    padding: 0.6rem 0.75rem !important;
    font-size: 0.72rem !important;
    font-weight: 500 !important;
    white-space: nowrap !important;
    border-bottom-width: 3px !important;
  }

  /* Icono dentro de tab: reducir margen */
  #modalEditPerfil .nav-tabs-custom .nav-link i,
  #modalEditPerfil .nav-tabs-custom .nav-link .fa {
    font-size: 0.8rem !important;
    margin-right: 0.3rem !important;
  }

  /* â”€â”€ 6. TAB CONTENT: padding cómodo para dedos â”€â”€ */
  #modalEditPerfil .tab-content {
    padding: 1rem !important;
  }

  /* â”€â”€ 7. HEADERS DE SECCIÃ“N dentro de tabs â”€â”€ */
  #modalEditPerfil .tab-pane h6 {
    font-size: 0.85rem !important;
    margin-bottom: 0.4rem !important;
  }

  #modalEditPerfil .tab-pane small {
    font-size: 0.7rem !important;
  }

  /* â”€â”€ 8. PUESTOS CONTAINER: altura adaptada a móvil â”€â”€ */
  #modalEditPerfil #puestos-container {
    max-height: 55vh !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }

  /* â”€â”€ 9. MÃ“DULOS CONTAINER â”€â”€ */
  #modalEditPerfil #modulos-container {
    max-height: 55vh !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }

  /* Una columna en pantallas muy estrechas (anula max-width 50% de escritorio) */
  #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo {
    flex: 1 1 100% !important;
    max-width: 100% !important;
  }

  /* â”€â”€ 10. PERMISOS ESPECIALES CONTAINER â”€â”€ */
  #modalEditPerfil #permisos-especiales-container {
    max-height: 55vh !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
    margin-left: 0 !important;               /* quitar indent de desktop */
    padding-left: 0 !important;
    border-left: none !important;
  }

  /* â”€â”€ 11. Pestaña Puestos: encabezado y expandir todos (móvil) â”€â”€ */
  #modalEditPerfil #tabPuestos .d-flex.justify-content-between,
  #modalEditPerfil #tabPuestos .d-flex.justify-content-between.align-items-center.mb-4 {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.5rem !important;
  }

  #modalEditPerfil #btn-puestos-expandir-todos {
    width: 100% !important;
    justify-content: center !important;
    font-size: 0.8rem !important;
    padding: 0.5rem 0.75rem !important;
  }

  #modalEditPerfil #tabPermisosEspeciales .d-flex.justify-content-between.align-items-center.mb-4 {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.5rem !important;
  }

  #modalEditPerfil #btn-permisos-esp-expandir-todos {
    width: 100% !important;
    justify-content: center !important;
    font-size: 0.8rem !important;
    padding: 0.5rem 0.75rem !important;
  }

  /* â”€â”€ 12. TABLAS dentro de módulos/permisos â”€â”€ */
  #modalEditPerfil .table-hover tbody tr:hover {
    transform: none !important;              /* deshabilitar translateX en touch */
  }

  /* â”€â”€ 13. ACCORDION dentro de módulos â”€â”€ */
  #modalEditPerfil .accordion-button {
    font-size: 0.82rem !important;
    padding: 0.65rem 0.75rem !important;
  }

  #modalEditPerfil .accordion-body {
    padding: 0.5rem 0.75rem !important;
  }

  /* â”€â”€ 14. INDICADOR "pill" drag para bottom-sheet â”€â”€ */
  #modalEditPerfil .modal-content::before {
    content: '' !important;
    display: block !important;
    width: 40px !important;
    height: 4px !important;
    background: #dee2e6 !important;
    border-radius: 2px !important;
    margin: 0.6rem auto 0 !important;
    flex-shrink: 0 !important;
  }
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   AJUSTE FINO para los más pequeños (360px)
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@media (max-width: 375px) {

  #modalEditPerfil .modal-title {
    font-size: 0.875rem !important;
  }

  #modalEditPerfil .nav-tabs-custom .nav-link {
    padding: 0.55rem 0.6rem !important;
    font-size: 0.68rem !important;
  }

  #modalEditPerfil .tab-content {
    padding: 0.75rem !important;
  }
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   ANIMACIÃ“N DE ENTRADA: slide-up desde abajo
   (reemplaza el fade genérico de Bootstrap)
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@media (max-width: 575.98px) {

  #modalEditPerfil.modal .modal-dialog {
    transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1) !important;
    transform: translateY(100%) !important;
  }

  #modalEditPerfil.modal.show .modal-dialog {
    transform: translateY(0) !important;
  }
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Modal permisos/puestos: sin backdrop de Bootstrap; overlay propio suave; encima de todo
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
#modalEditPerfil.modal.show {
  z-index: 99999 !important;
  background: transparent !important;
}
#modalEditPerfil.modal.show .modal-dialog {
  z-index: 1;
  position: relative;
}
/* Scrim detrás de modales (KPI Desglose + Edit Perfil): modo claro = scrim visible; modo oscuro = opaco para contorno */
#customModalOverlay {
  position: fixed !important;
  inset: 0 !important;
  z-index: 99998 !important;
  pointer-events: none !important;
  transition: background-color 0.25s ease;
}
/* Modo claro: scrim más oscuro detrás del modal */
#customModalOverlay {
  background: rgba(0, 0, 0, 0.42) !important;
}
/* Modo oscuro: scrim más opaco para marcar bien el contorno del modal */
body.dark-mode #customModalOverlay {
  background: rgba(0, 0, 0, 0.88) !important;
}
body.modal-edit-perfil-open .layout-wrapper {
  position: relative;
  z-index: 1051 !important;
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Modal Gestión: verse completo en pantalla (desktop y móvil)
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
#modalEditPerfil .modal-dialog {
  max-height: 90vh;
  margin: 1.75rem auto;
  display: flex;
  flex-direction: column;
}
#modalEditPerfil .modal-content {
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
#modalEditPerfil .modal-body {
  overflow-y: auto;
  overflow-x: hidden;
  flex: 1 1 auto;
  -webkit-overflow-scrolling: touch;
}

/* â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   Modal KPI Desglose (Departamentos / Puestos / Total Empleados)
   Template: Liquid Glass — consistente con #modalReingreso
   â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
#modalKpiDesglose.modal.show {
  z-index: 99999 !important;
  background: transparent !important;
}
#modalKpiDesglose.modal.show .modal-dialog {
  z-index: 1;
  position: relative;
}
#modalKpiDesglose .modal-dialog.modal-kpi-desglose {
  max-width: 720px;
}
@media (min-width: 992px) {
  #modalKpiDesglose .modal-dialog.modal-kpi-desglose {
    max-width: 800px;
  }
}
#modalKpiDesglose .modal-kpi-desglose-glass {
  background: rgba(255, 255, 255, 0.92) !important;
  backdrop-filter: blur(14px);
  -webkit-backdrop-filter: blur(14px);
  border-radius: 1rem;
  border: 1px solid rgba(0, 0, 0, 0.08);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
#modalKpiDesglose .modal-header {
  background: rgba(0, 0, 0, 0.03) !important;
  border-bottom: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 1rem 1rem 0 0;
  flex-shrink: 0;
  padding: 1rem 1.25rem;
}
#modalKpiDesglose .modal-body {
  overflow-y: auto;
  overflow-x: hidden;
  flex: 1 1 auto;
  -webkit-overflow-scrolling: touch;
  padding: 1.25rem 1.5rem;
}
#modalKpiDesglose .modal-footer {
  background: rgba(0, 0, 0, 0.03) !important;
  border-top: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 0 0 1rem 1rem;
  flex-shrink: 0;
}
/* Dark mode */
body.dark-mode #modalKpiDesglose .modal-kpi-desglose-glass {
  background: rgba(30, 41, 59, 0.92) !important;
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
}
body.dark-mode #modalKpiDesglose .modal-header {
  background: rgba(51, 65, 85, 0.6) !important;
  border-bottom-color: rgba(255, 255, 255, 0.08);
}
body.dark-mode #modalKpiDesglose .modal-footer {
  background: rgba(51, 65, 85, 0.6) !important;
  border-top-color: rgba(255, 255, 255, 0.08);
}
@media (max-width: 575.98px) {
  #modalKpiDesglose .modal-dialog.modal-kpi-desglose {
    margin: 0.5rem;
    max-width: calc(100vw - 1rem);
  }
  #modalKpiDesglose .modal-kpi-desglose-glass {
    max-height: 95vh;
  }
  #modalKpiDesglose .modal-body {
    padding: 1rem;
  }
  #modalKpiDesglose .modal-footer {
    flex-wrap: wrap;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
  }
  #modalKpiDesglose .modal-footer .btn {
    flex: 1 1 auto;
    min-width: 0;
  }
}

/* Sede badge: Liquid Glass dark mode */
body.dark-mode .sede-glass-badge {
    background: rgba(30, 41, 59, 0.7) !important;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border-color: rgba(51, 65, 85, 0.4) !important;
}
</style>


<script>
window.miUsuarioId = <?= json_encode((int)($miUsuarioId ?? 0)) ?>;
window.puedeEditarTodos = <?= json_encode(!empty($puedeEditarTodos ?? false)) ?>;
window.puedeGestionarPermisos = <?= json_encode(!empty($puedeGestionarPermisos ?? false)) ?>;
window.puedeDescargarPlantillaGestion = <?= json_encode(!empty($puedeDescargarPlantillaGestion ?? false)) ?>;
window.puedeAgregarUsuarioGestion = <?= json_encode(!empty($puedeAgregarUsuarioGestion ?? false)) ?>;
window.puedeEditarUsuarioGestion = <?= json_encode(!empty($puedeEditarUsuarioGestion ?? false)) ?>;
window.puedeCargarDocumentoGestion = <?= json_encode(!empty($puedeCargarDocumentoGestion ?? false)) ?>;
window.puedeRegistrarAusenciaGestion = <?= json_encode(!empty($puedeRegistrarAusenciaGestion ?? false)) ?>;
window.puedeDarBajaGestion = <?= json_encode(!empty($puedeDarBajaGestion ?? false)) ?>;
window.puedeVisualizarContrasenaGestion = <?= json_encode(!empty($puedeVisualizarContrasenaGestion ?? false)) ?>;
window.puedeRegistrarPersonaGestion = <?= json_encode(!empty($puedeRegistrarPersonaGestion ?? false)) ?>;
window.permisosEdicionCobranzaGestion = <?= json_encode(($permisosEdicionCobranzaGestion ?? [])) ?>;
window.puedeActualizarInfo = <?= json_encode(!empty($puedeActualizarInfo ?? false)) ?>;
window.puedeAgregarUsuarioRrhh = <?= json_encode(!empty($puedeAgregarUsuarioRrhh ?? false)) ?>;
window.puedeEditarUsuarioRrhh = <?= json_encode(!empty($puedeEditarUsuarioRrhh ?? false)) ?>;
window.puedeVerSalarioSensibleRrhh = <?= json_encode(!empty($puedeVerSalarioSensibleRrhh ?? false)) ?>;
window.todosDepartamentosBackend = <?= json_encode(($departamento['datos'] ?? [])) ?>;
window.catalogoCompletoDeptosBackend = <?= json_encode(($catalogoCompletoDeptos['datos'] ?? [])) ?>;
<?php
    $departamentosCobranzaEditarUsuario = [];
    $idsDepartamentosCobranzaEditarUsuario = [];
    foreach (($catalogoCompletoDeptos['datos'] ?? []) as $rowDeptoCobranza) {
        $areaDeptoCobranza = strtolower(trim((string)($rowDeptoCobranza['departamento_organizacional_nombre'] ?? '')));
        if (!in_array($areaDeptoCobranza, ['cobranza', 'cobranza corporativo'], true)) {
            continue;
        }
        if (isset($rowDeptoCobranza['activo']) && (int)$rowDeptoCobranza['activo'] !== 1) {
            continue;
        }
        if (isset($rowDeptoCobranza['departamento_organizacional_activo']) && (int)$rowDeptoCobranza['departamento_organizacional_activo'] !== 1) {
            continue;
        }
        $idDeptoCobranza = (int)($rowDeptoCobranza['id'] ?? ($rowDeptoCobranza['departamento_id'] ?? 0));
        if ($idDeptoCobranza <= 0 || isset($idsDepartamentosCobranzaEditarUsuario[$idDeptoCobranza])) {
            continue;
        }
        $idsDepartamentosCobranzaEditarUsuario[$idDeptoCobranza] = true;
        $departamentosCobranzaEditarUsuario[] = $rowDeptoCobranza;
    }
?>
window.departamentosCobranzaEditarUsuarioBackend = <?= json_encode($departamentosCobranzaEditarUsuario) ?>;
window.paisesActivosBackend = <?= json_encode(($paisesActivos ?? [])) ?>;
</script>
<div class="content-wrapper">

    <!-- =======================
         CARD PRINCIPAL
    ======================== -->
    <div class="card">

        <!-- =======================
             FILTROS
        ======================== -->
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Filtros de búsqueda</h5>

            <div class="row pt-4 g-6">
                <div class="col-md-3">
                    <select id="gestionEmpresaSelect" class="form-select text-capitalize js-select-buscador" aria-label="Empresa">
                        <option value="">Todas las empresas</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <?php
                        $direccionesIniciales = [];
                        $areasIniciales = [];
                        $fuenteAreas = $catalogoCompletoDeptos['datos'] ?? ($departamento['datos'] ?? []);
                        if (is_array($fuenteAreas)) {
                            foreach ($fuenteAreas as $rowArea) {
                                $nombreDireccion = trim((string)($rowArea['direccion_nombre'] ?? ''));
                                if ($nombreDireccion !== '' && strtolower($nombreDireccion) !== 'sin dirección') {
                                    $direccionesIniciales[$nombreDireccion] = true;
                                }
                                $nombreArea = trim((string)($rowArea['departamento_organizacional_nombre'] ?? ''));
                                if ($nombreArea === '' || strtolower($nombreArea) === 'sin departamento') {
                                    continue;
                                }
                                $areasIniciales[$nombreArea] = true;
                            }
                        }
                        $direccionesIniciales = array_keys($direccionesIniciales);
                        sort($direccionesIniciales, SORT_NATURAL | SORT_FLAG_CASE);
                        $areasIniciales = array_keys($areasIniciales);
                        sort($areasIniciales, SORT_NATURAL | SORT_FLAG_CASE);
                    ?>
                    <select id="UserDireccion" class="form-select text-capitalize js-select-buscador">
                        <option value="">Selecciona Dirección</option>
                        <?php foreach ($direccionesIniciales as $nombreDireccion): ?>
                            <option value="<?= htmlspecialchars($nombreDireccion, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($nombreDireccion, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="UserArea" class="form-select text-capitalize js-select-buscador">
                        <option value="">Selecciona Área</option>
                        <?php foreach ($areasIniciales as $nombreArea): ?>
                            <option value="<?= htmlspecialchars($nombreArea, ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($nombreArea, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="UserRole" class="form-select text-capitalize js-select-buscador">
                        <option value="">Selecciona Departamento</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="UserPlan" class="form-select text-capitalize js-select-buscador">
                        <option value="">Selecciona Puesto</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="FilterMultiplePuestos" class="form-select text-capitalize js-select-buscador">
                        <option value="">Todos los usuarios</option>
                        <option value="multiples">Múltiples puestos</option>
                        <option value="unico">Un solo puesto</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- =======================
             PANEL DE INDICADORES (GESTIÃ“N) — REDISEÃ‘O v3
        ======================== -->

        <!-- Tooltip global para donuts (panel de indicadores oculto) -->
        <div class="kpi-donut-tooltip d-none" id="kpiDonutTooltip" aria-hidden="true"></div>

        <div id="panelIndicadoresGestion" class="px-4 pt-3 pb-2 d-none" aria-hidden="true">

            <!-- TOOLBAR: toggle + modos + reset -->
            <div class="kpi-toolbar">
                <button class="kpi-toggle-btn open" id="kpiToggleBtn" onclick="kpiTogglePanel()">
                    <span class="kpi-dot"></span>
                    Indicadores
                    <i class="bx bx-chevron-down kpi-chevron"></i>
                </button>

                <div class="kpi-toolbar-sep" id="kpiViewControlsSep"></div>

                <div id="kpiViewControls">
                <button class="kpi-view-btn active" id="vbtn-default"  onclick="kpiSetMode('default')"  data-tip="Vista Estándar">
                    <i class="bx bx-layout"></i>
                    <span class="kpi-btn-text">Estándar</span>
                </button>
                <button class="kpi-view-btn"        id="vbtn-vision"   onclick="kpiSetMode('vision')"   data-tip="Vista de Donut Charts">
                    <i class="bx bx-doughnut-chart"></i>
                    <span class="kpi-btn-text">Donut</span>
                </button>
                <button class="kpi-view-btn"        id="vbtn-ministat" onclick="kpiSetMode('ministat')" data-tip="Vista Mini-Stat Compacta">
                    <i class="bx bx-columns"></i>
                    <span class="kpi-btn-text">Mini-Stat</span>
                </button>

                <div class="kpi-toolbar-sep"></div>

                <button class="kpi-reset-btn" onclick="kpiResetPrefs()">
                    <i class="bx bx-rotate-left"></i>
                    Restablecer
                </button>
                </div>
            </div>

            <!-- Panel colapsable -->
            <div class="kpi-collapsible open" id="kpiCollapsible">
                <div class="kpi-collapsible-inner">
                    <div class="kpi-row-new mode-default" id="kpiRowNew">

                        <!-- â–‘â–‘ DEPARTAMENTOS â–‘â–‘ -->
                        <div class="kpi-cell tipo-dep" id="kpi-cell-dep" data-tipo="departamentos">
                            <span class="kpi-corner-icon"><i class="bx bx-buildings"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-buildings"></i></div>
                                <span class="kpi-cell-status">Activos</span>
                            </div>
                            <div class="kpi-num" id="kpi-departamentos">0</div>
                            <div class="kpi-lbl">Departamentos</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-dep"></div></div>
                            <!-- mini-stat -->
                            <span class="kpi-cell-title">Departamentos</span>
                            <div class="kpi-stats-grid-new">
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-dep-a">0</div><div class="kpi-stat-lbl">Activos</div></div>
                                <div class="kpi-stat-div"></div>
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-dep-b">0</div><div class="kpi-ms-pct" id="kpi-ms-pct-dep">—</div><div class="kpi-stat-lbl">Sin jefe</div></div>
                            </div>
                            <!-- visión donut -->
                            <div class="donut-block">
                                <div class="donut-header">
                                    <span class="donut-title">Departamentos</span>
                                    <span class="kpi-cell-status">Activos</span>
                                </div>
                                <div class="donut-svg-wrap"
                                     onmouseenter="kpiShowTooltip(event, document.getElementById('kpi-departamentos').textContent + ' departamentos activos')"
                                     onmouseleave="kpiHideTooltip()"
                                     onmousemove="kpiMoveTooltip(event)">
                                    <svg class="donut-svg" viewBox="0 0 88 88">
                                        <circle class="donut-track" cx="44" cy="44" r="36"/>
                                        <circle class="donut-arc" id="kpi-arc-dep" cx="44" cy="44" r="36"/>
                                    </svg>
                                    <div class="donut-center-icon"><i class="bx bx-buildings"></i></div>
                                </div>
                                <div class="donut-footer">
                                    <span class="donut-trend up" id="kpi-trend-dep"><i class="bx bx-trending-up"></i>—</span>
                                    <span class="donut-trend-label">más puestos</span>
                                </div>
                                <div class="donut-stats">
                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-dep-a">0</div><div class="kpi-stat-lbl">Activos</div></div>
                                    <div class="kpi-stat-div"></div>
                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-dep-b">0</div><div class="kpi-stat-lbl">Sin jefe</div></div>
                                </div>
                            </div>
                        </div>

                        <!-- â–‘â–‘ PUESTOS â–‘â–‘ -->
                        <div class="kpi-cell tipo-puesto" id="kpi-cell-puesto" data-tipo="puestos">
                            <span class="kpi-corner-icon"><i class="bx bx-briefcase"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-briefcase"></i></div>
                                <span class="kpi-cell-status">Ãšnicos</span>
                            </div>
                            <div class="kpi-num" id="kpi-puestos">0</div>
                            <div class="kpi-lbl">Puestos</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-puesto"></div></div>
                            <!-- mini-stat -->
                            <span class="kpi-cell-title">Puestos</span>
                            <div class="kpi-stats-grid-new">
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-puesto-a">0</div><div class="kpi-stat-lbl">Total</div></div>
                                <div class="kpi-stat-div"></div>
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-puesto-b">0</div><div class="kpi-ms-pct" id="kpi-ms-pct-pst">—</div><div class="kpi-stat-lbl">Compartidos</div></div>
                            </div>
                            <!-- visión donut -->
                            <div class="donut-block">
                                <div class="donut-header">
                                    <span class="donut-title">Puestos</span>
                                    <span class="kpi-cell-status">Ãšnicos</span>
                                </div>
                                <div class="donut-svg-wrap"
                                     onmouseenter="kpiShowTooltip(event, document.getElementById('kpi-puestos').textContent + ' puestos únicos')"
                                     onmouseleave="kpiHideTooltip()"
                                     onmousemove="kpiMoveTooltip(event)">
                                    <svg class="donut-svg" viewBox="0 0 88 88">
                                        <circle class="donut-track" cx="44" cy="44" r="36"/>
                                        <circle class="donut-arc" id="kpi-arc-puesto" cx="44" cy="44" r="36"/>
                                    </svg>
                                    <div class="donut-center-icon"><i class="bx bx-briefcase"></i></div>
                                </div>
                                <div class="donut-footer">
                                    <span class="donut-trend steady" id="kpi-trend-puesto"><i class="bx bx-user-pin"></i>—</span>
                                    <span class="donut-trend-label">más empleados</span>
                                </div>
                                <div class="donut-stats">
                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-pst-a">0</div><div class="kpi-stat-lbl">Total</div></div>
                                    <div class="kpi-stat-div"></div>
                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-pst-b">0</div><div class="kpi-stat-lbl">Compartidos</div></div>
                                </div>
                            </div>
                        </div>

                        <!-- â–‘â–‘ TOTAL EMPLEADOS â–‘â–‘ -->
                        <div class="kpi-cell tipo-total" id="kpi-cell-total" data-tipo="total">
                            <span class="kpi-corner-icon"><i class="bx bx-group"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-group"></i></div>
                                <span class="kpi-cell-status">Total</span>
                            </div>
                            <div class="kpi-num" id="kpi-total-empleados">0</div>
                            <div class="kpi-lbl" id="kpi-total-empleados-label">Total Empleados</div>
                            <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-total"></div></div>
                            <!-- mini-stat -->
                            <span class="kpi-cell-title">Empleados</span>
                            <div class="kpi-stats-grid-new">
                                <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-total-main">0</div><div class="kpi-stat-lbl">Total</div></div>
                                <div class="kpi-stat-div"></div>
                                <div class="kpi-stat-item">
                                    <div class="kpi-stat-val kpi-stat-ingr" id="kpi-ms-ingresos">0</div>
                                    <div class="kpi-ms-pct kpi-stat-ingr" id="kpi-ms-pct-emp">—</div>
                                    <div class="kpi-stat-lbl"><span class="kpi-ingr-arrow"><i class="bx bx-up-arrow-alt"></i></span> Ingresos</div>
                                </div>
                            </div>
                            <!-- visión donut -->
                            <div class="donut-block">
                                <div class="donut-header">
                                    <span class="donut-title">Empleados</span>
                                    <span class="kpi-cell-status">Total</span>
                                </div>
                                <div class="donut-svg-wrap"
                                     onmouseenter="kpiShowTooltip(event, document.getElementById('kpi-total-empleados').textContent + ' empleados activos')"
                                     onmouseleave="kpiHideTooltip()"
                                     onmousemove="kpiMoveTooltip(event)">
                                    <svg class="donut-svg" viewBox="0 0 88 88">
                                        <circle class="donut-track" cx="44" cy="44" r="36"/>
                                        <circle class="donut-arc" id="kpi-arc-total" cx="44" cy="44" r="36"/>
                                    </svg>
                                    <div class="donut-center-icon"><i class="bx bx-group"></i></div>
                                </div>
                                <div class="donut-footer">
                                    <span class="donut-trend up" id="kpi-trend-total"><i class="bx bx-trending-up"></i>—</span>
                                    <span class="donut-trend-label">del total activo</span>
                                </div>
                                <div class="donut-stats">
                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-emp-a">0</div><div class="kpi-stat-lbl">Total</div></div>
                                    <div class="kpi-stat-div"></div>
                                    <div class="kpi-stat-item"><div class="kpi-stat-val kpi-stat-ingr" id="kpi-dv-emp-b">0</div><div class="kpi-stat-lbl"><span class="kpi-ingr-arrow"><i class="bx bx-up-arrow-alt"></i></span> Ingresos</div></div>
                                </div>
                            </div>
                        </div>

                    </div><!-- /kpi-row-new -->
                </div>
            </div><!-- /kpi-collapsible -->

        </div><!-- /panelIndicadoresGestion -->

<!-- =============================================
     MODAL KPI — Desglose por indicador
============================================= -->
<div class="modal fade" id="modalKpiDesglose" tabindex="-1" aria-labelledby="modalKpiDesgloseLabel" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-kpi-desglose">
    <div class="modal-content modal-kpi-desglose-glass">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalKpiTitle"><i class="bx bx-bar-chart me-2"></i>Desglose</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="modalKpiContent">
        <p class="text-muted text-center py-4"><i class="bx bx-loader-alt bx-spin me-2"></i>Cargandoâ€¦</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>



        <!-- =======================
             BOTONES DE ACCIÃ“N
        ======================== -->
        <div class="row justify-content-end m-4">
            <div class="col-12 d-flex align-items-end justify-content-end gap-2 flex-wrap">
                <?php if (!empty($puedeDescargarPlantillaGestion ?? false)): ?>
                <button
                  type="button"
                  class="btn text-white btn-action-size"
                  style="background-color: #0047bb; border-color: #0047bb;"
                  onclick="descargarPlantillaGestores()"
                  title="Descargar plantilla Excel"
                >
                    <i class="fa fa-download icon-sm me-sm-2"></i>
                    <span class="d-inline-block">Plantilla</span>
                </button>
                <?php endif; ?>

                <?php if (!empty($puedeAgregarUsuarioGestion ?? false)): ?>
                <button
                  type="button"
                  class="btn btn-primary add-new btn-action-size"
                  data-bs-toggle="offcanvas"
                  data-bs-target="#offcanvasAddUser"
                >
                    <i class="fa fa-user-plus icon-sm me-sm-2"></i>
                    <span class="d-inline-block">Agregar Usuario</span>
                </button>
                <?php endif; ?>

                <?php if (!empty($puedeAgregarUsuarioRrhh ?? false)): ?>
                <button
                  type="button"
                  class="btn btn-info text-white btn-action-size"
                  data-bs-toggle="modal"
                  data-bs-target="#modalAgregarUsuarioRrhh"
                  title="Agregar usuario desde RR.HH."
                >
                    <i class="fa fa-user-tie icon-sm me-sm-2"></i>
                    <span class="d-inline-block">Agregar usuario RR.HH.</span>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($puedeAgregarUsuarioRrhh ?? false) || !empty($puedeEditarUsuarioRrhh ?? false)): ?>
        <div class="modal fade" id="modalAgregarUsuarioRrhh" tabindex="-1" aria-labelledby="modalAgregarUsuarioRrhhLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
          <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <form class="modal-content" id="formAgregarUsuarioRrhh" autocomplete="off">
              <input type="hidden" id="rrhh_edit_id_persona" name="id_persona">
              <div class="modal-header">
                <div class="rrhh-wizard-title">
                  <span class="rrhh-wizard-avatar"><i class="fa fa-user"></i></span>
                  <div class="min-w-0">
                    <h5 class="modal-title fw-bold mb-0" id="modalAgregarUsuarioRrhhLabel">Agregar usuario RR.HH.</h5>
                    <div class="rrhh-wizard-subtitle" id="rrhhWizardSubtitle">Completa la informaci&oacute;n del nuevo usuario por secciones.</div>
                  </div>
                </div>
                <div class="d-flex align-items-center gap-3 ms-auto">
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
              </div>
              <div class="modal-body">
                <ul class="nav nav-tabs mb-3" role="tablist">
                  <li class="nav-item" role="presentation"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#rrhhTabPersona" type="button" role="tab">Datos personales</button></li>
                  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rrhhTabLaboral" type="button" role="tab">Laboral</button></li>
                  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rrhhTabNomina" type="button" role="tab">Banco y créditos</button></li>
                  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rrhhTabContactos" type="button" role="tab">Contactos</button></li>
                  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rrhhTabBeneficiarios" type="button" role="tab">Beneficiarios</button></li>
                  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rrhhTabSalud" type="button" role="tab">Salud</button></li>
                  <li class="nav-item" role="presentation"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#rrhhTabObservaciones" type="button" role="tab">Observaciones</button></li>
                </ul>

                <div class="tab-content">
                  <div class="tab-pane fade show active" id="rrhhTabPersona" role="tabpanel">
                    <div class="rrhh-section shadow-sm mb-3">
                      <div class="rrhh-section-title"><i class="fa fa-id-card me-1"></i>Identificación</div>
                      <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Nombre(s) *</label><input type="text" class="form-control text-uppercase" name="persona.nombres" required></div>
                        <div class="col-md-3"><label class="form-label">Segundo nombre</label><input type="text" class="form-control text-uppercase" name="persona.segundo_nombre"></div>
                        <div class="col-md-3"><label class="form-label">Apellido paterno *</label><input type="text" class="form-control text-uppercase" name="persona.apellidop" required></div>
                        <div class="col-md-3"><label class="form-label">Apellido materno</label><input type="text" class="form-control text-uppercase" name="persona.apellidom"></div>
                        <div class="col-md-3"><label class="form-label">CURP</label><input type="text" class="form-control text-uppercase" name="persona.curp" maxlength="18"></div>
                        <div class="col-md-3"><label class="form-label">RFC</label><input type="text" class="form-control text-uppercase" name="persona.rfc" maxlength="20"></div>
                        <div class="col-md-3"><label class="form-label">NSS</label><input type="text" class="form-control" name="persona.nss" maxlength="20"></div>
                        <div class="col-md-3"><label class="form-label">Fecha de nacimiento</label><input type="text" class="form-control rrhh-date" name="persona.fecha_nacimiento" placeholder="YYYY-MM-DD" readonly></div>
                        <div class="col-md-3">
                          <label class="form-label">Sexo</label>
                          <select class="form-select" name="persona.sexo">
                            <option value="">Selecciona</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Otro">Otro</option>
                          </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Entidad federativa</label><input type="text" class="form-control text-uppercase" name="persona.entidad_federativa_rfc"></div>
                        <div class="col-md-3"><label class="form-label">Usuario</label><input type="text" class="form-control" name="persona.usuario" autocomplete="off" data-lpignore="true" data-1p-ignore="true"></div>
                        <div class="col-md-3"><label class="form-label">Contrase&ntilde;a</label><input type="text" class="form-control" name="persona.contrasena" autocomplete="off" autocapitalize="off" spellcheck="false" data-lpignore="true" data-1p-ignore="true" data-form-type="other"></div>
                      </div>
                    </div>

                    <div class="rrhh-section shadow-sm mb-3">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rrhh-section-title mb-0">Teléfonos</div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-rrhh-add="telefonos"><i class="fa fa-plus me-1"></i>Agregar</button>
                      </div>
                      <div data-rrhh-list="telefonos"></div>
                    </div>
                    <div class="rrhh-section shadow-sm mb-3">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rrhh-section-title mb-0">Correos</div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-rrhh-add="correos"><i class="fa fa-plus me-1"></i>Agregar</button>
                      </div>
                      <div data-rrhh-list="correos"></div>
                    </div>
                    <div class="rrhh-section shadow-sm">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rrhh-section-title mb-0">Domicilios</div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-rrhh-add="domicilios"><i class="fa fa-plus me-1"></i>Agregar</button>
                      </div>
                      <div data-rrhh-list="domicilios"></div>
                    </div>
                  </div>

                  <div class="tab-pane fade" id="rrhhTabLaboral" role="tabpanel">
                    <div class="rrhh-section shadow-sm">
                      <div class="rrhh-section-title"><i class="fa fa-briefcase me-1"></i>Datos laborales</div>
                      <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Registro patronal</label><input type="text" class="form-control" name="rrhh.registro_patronal"></div>
                        <div class="col-md-3"><label class="form-label">CODIGO CONTPAC</label><input type="text" class="form-control" name="persona.codigo_contpac" maxlength="40" inputmode="numeric" autocomplete="off" placeholder="Codigo CONTPAC"><input type="hidden" name="rrhh.codigo_contpaq"></div>
                        <div class="col-md-3"><label class="form-label">Fecha de ingreso</label><input type="text" class="form-control rrhh-date" name="rrhh.fecha_ingreso" placeholder="YYYY-MM-DD" readonly></div>
                        <div class="col-md-3"><label class="form-label">Fecha CONTPAC</label><input type="text" class="form-control rrhh-date" name="rrhh.fecha_contpaq" placeholder="YYYY-MM-DD" readonly></div>
                        <div class="col-md-3"><label class="form-label">Fecha IMSS alta</label><input type="text" class="form-control rrhh-date" name="rrhh.fecha_imss_alta" placeholder="YYYY-MM-DD" readonly></div>
                        <div class="col-md-3">
                          <label class="form-label">Pa&iacute;s *</label>
                          <select id="rrhh_pais_id" class="form-select js-select-buscador" name="persona.id_pais" required>
                            <option value="">Seleccione un pa&iacute;s</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Empresa</label>
                          <select id="rrhh_empresa_id" class="form-select js-select-buscador" name="rrhh.empresa_id" disabled>
                            <option value="">Seleccione una empresa</option>
                          </select>
                          <input type="hidden" id="rrhh_empresa_texto" name="rrhh.empresa_texto">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Direcci&oacute;n</label>
                          <select id="rrhh_direccion_id" class="form-select js-select-buscador" name="rrhh.direccion_id" disabled>
                            <option value="">Seleccione una direcci&oacute;n</option>
                          </select>
                          <input type="hidden" id="rrhh_direccion_texto" name="rrhh.direccion_texto">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">&Aacute;rea</label>
                          <select id="rrhh_area_id" class="form-select js-select-buscador" name="rrhh.area_id" disabled>
                            <option value="">Seleccione un &aacute;rea</option>
                          </select>
                          <input type="hidden" id="rrhh_area_texto" name="rrhh.area_texto">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Departamento</label>
                          <select id="rrhh_departamento_id" class="form-select js-select-buscador" name="rrhh.departamento_id" disabled>
                            <option value="">Seleccione un departamento</option>
                          </select>
                          <input type="hidden" id="rrhh_departamento_texto" name="rrhh.departamento_texto">
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Puesto</label>
                          <select id="rrhh_puesto_id" class="form-select js-select-buscador" name="rrhh.puesto_id" disabled>
                            <option value="">Seleccione un puesto</option>
                          </select>
                          <input type="hidden" id="rrhh_puesto_texto" name="rrhh.puesto_texto">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Jefe directo</label>
                          <select id="rrhh_jefe_id" class="form-select js-select-buscador" name="rrhh.jefe_id" disabled>
                            <option value="">Seleccione un jefe</option>
                          </select>
                          <input type="hidden" id="rrhh_jefe_directo_texto" name="rrhh.jefe_directo_texto">
                        </div>
                        <div class="col-md-4"><label class="form-label">Ubicación laboral</label><input type="text" class="form-control" name="rrhh.ubicacion_laboral"></div>
                        <div class="col-md-4"><label class="form-label">Municipio</label><input type="text" class="form-control" name="rrhh.municipio_laboral"></div>
                        <div class="col-12">
                          <div class="rrhh-salary-card" id="rrhhSalarioSensibleCard">
                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-2">
                              <div class="rrhh-section-title mb-0"><i class="fa fa-shield-halved me-1"></i>Salario</div>
                              <span class="rrhh-salary-status" id="rrhhSalarioSensibleStatus"><i class="fa fa-lock"></i>Protegido</span>
                            </div>
                            <div class="rrhh-salary-row">
                              <div class="rrhh-salary-field">
                                <label class="form-label">Salario mensual</label>
                                <div class="input-group">
                                  <span class="input-group-text">$</span>
                                  <input type="password" class="form-control" id="rrhh_salario_sensible" inputmode="decimal" autocomplete="off" placeholder="Protegido" disabled>
                                  <span class="input-group-text">MXN</span>
                                </div>
                                <div class="form-text" id="rrhh_salario_sensible_letras">Desbloquea el salario para capturarlo.</div>
                              </div>
                              <div class="rrhh-salary-actions">
                                <button type="button" class="btn btn-outline-primary" id="btnRrhhDesbloquearSalario">
                                  <i class="fa fa-key me-1"></i>Desbloquear
                                </button>
                                <button type="button" class="btn btn-primary" id="btnRrhhGuardarSalario" disabled>
                                  <i class="fa fa-save me-1"></i>Guardar salario
                                </button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="tab-pane fade" id="rrhhTabNomina" role="tabpanel">
                    <div class="rrhh-section shadow-sm mb-3">
                      <div class="rrhh-section-title"><i class="fa fa-money-bill-wave me-1"></i>Banco y créditos laborales</div>
                      <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Crédito Infonavit/Fonacot</label><input type="text" class="form-control" name="nomina.credito_infonavit_fonacot"></div>
                        <div class="col-md-3"><label class="form-label">Número de crédito</label><input type="text" class="form-control" name="nomina.no_credito"></div>
                        <div class="col-md-3"><label class="form-label">Monto a descontar</label><input type="number" step="0.01" class="form-control" name="nomina.monto_descontar"></div>
                        <div class="col-md-3">
                          <label class="form-label">Carta de no crédito</label>
                          <select class="form-select" name="rrhh.carta_no_credito">
                            <option value="">Selecciona</option>
                            <option value="Si">Sí</option>
                            <option value="No">No</option>
                          </select>
                        </div>
                        <div class="col-md-3">
                          <label class="form-label">Carta no nómina BBVA</label>
                          <select class="form-select" name="rrhh.carta_no_nomina_bbva">
                            <option value="">Selecciona</option>
                            <option value="Si">Sí</option>
                            <option value="No">No</option>
                          </select>
                        </div>
                      </div>
                    </div>
                    <div class="rrhh-section shadow-sm">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rrhh-section-title mb-0">Cuentas bancarias</div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-rrhh-add="cuentas_bancarias"><i class="fa fa-plus me-1"></i>Agregar</button>
                      </div>
                      <div data-rrhh-list="cuentas_bancarias"></div>
                    </div>
                  </div>

                  <div class="tab-pane fade" id="rrhhTabContactos" role="tabpanel">
                    <div class="rrhh-section shadow-sm">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rrhh-section-title mb-0">Contactos de emergencia</div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-rrhh-add="contactos_emergencia"><i class="fa fa-plus me-1"></i>Agregar</button>
                      </div>
                      <div data-rrhh-list="contactos_emergencia"></div>
                    </div>
                  </div>

                  <div class="tab-pane fade" id="rrhhTabBeneficiarios" role="tabpanel">
                    <div class="rrhh-section shadow-sm">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="rrhh-section-title mb-0">Beneficiarios por fallecimiento</div>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-rrhh-add="beneficiarios"><i class="fa fa-plus me-1"></i>Agregar</button>
                      </div>
                      <div data-rrhh-list="beneficiarios"></div>
                    </div>
                  </div>

                  <div class="tab-pane fade" id="rrhhTabSalud" role="tabpanel">
                    <div class="rrhh-section shadow-sm mb-3">
                      <div class="rrhh-section-title"><i class="fa fa-notes-medical me-1"></i>Informaci&oacute;n m&eacute;dica</div>
                      <div class="row g-3">
                        <div class="col-md-3">
                          <label class="form-label">Tipo de sangre</label>
                          <select class="form-select" name="rrhh.tipo_sangre">
                            <option value="">Selecciona</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="Desconocido">Desconocido</option>
                          </select>
                        </div>
                        <div class="col-md-9">
                          <label class="form-label">Alergias</label>
                          <textarea class="form-control" name="rrhh.alergias" rows="2" placeholder="Medicamentos, alimentos u otras alergias relevantes"></textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Enfermedades cr&oacute;nicas</label>
                          <textarea class="form-control" name="rrhh.enfermedades_cronicas" rows="3" placeholder="Diabetes, hipertensi&oacute;n, asma u otros padecimientos"></textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Enfermedades hereditarias</label>
                          <textarea class="form-control" name="rrhh.enfermedades_hereditarias" rows="3" placeholder="Antecedentes familiares relevantes"></textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Medicamentos actuales</label>
                          <textarea class="form-control" name="rrhh.medicamentos_actuales" rows="3" placeholder="Tratamientos o medicamentos de uso continuo"></textarea>
                        </div>
                        <div class="col-md-6">
                          <label class="form-label">Discapacidad o condici&oacute;n m&eacute;dica</label>
                          <textarea class="form-control" name="rrhh.discapacidad_condicion" rows="3" placeholder="Condiciones que RR.HH. deba considerar"></textarea>
                        </div>
                        <div class="col-md-12">
                          <label class="form-label">Observaciones m&eacute;dicas</label>
                          <textarea class="form-control" name="rrhh.observaciones_medicas" rows="3" placeholder="Indicaciones, restricciones o notas adicionales"></textarea>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="tab-pane fade" id="rrhhTabObservaciones" role="tabpanel">
                    <div class="rrhh-section shadow-sm">
                      <div class="rrhh-section-title"><i class="fa fa-clipboard-list me-1"></i>Observaciones</div>
                      <textarea class="form-control" name="observaciones" rows="6" placeholder="Notas internas de RR.HH."></textarea>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary d-none" id="btnGenerarExpedienteRrhh"><i class="fa fa-folder-open me-1"></i>Generar expediente</button>
                <button type="button" class="btn btn-outline-primary d-none" id="btnGenerarCredencialRrhh"><i class="fa fa-id-card me-1"></i>Generar credencial</button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarUsuarioRrhh"><i class="fa fa-save me-1"></i>Guardar usuario RR.HH.</button>
              </div>
            </form>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($puedeActualizarInfo ?? false)): ?>
        <div class="modal fade" id="modalActualizacionInfoPersona" tabindex="-1" aria-labelledby="modalActualizacionInfoPersonaLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
          <div class="modal-dialog modal-dialog-scrollable">
            <form class="modal-content" id="formActualizacionInfoPersona" autocomplete="off">
              <input type="hidden" id="actualizacion_info_id_persona" name="id_persona">
              <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalActualizacionInfoPersonaLabel">
                  <i class="fa fa-arrows-rotate me-2"></i>Actualizar informaci&oacute;n
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <div class="actualizacion-info-summary">
                  <i class="fa fa-circle-info text-primary mt-1"></i>
                  <div class="min-w-0">
                    <div class="fw-bold text-dark" id="actualizacionInfoPersonaNombre">Cargando informaci&oacute;n actual...</div>
                    <small class="text-muted">Selecciona los campos que el gestor debe actualizar en MaxikashApp.</small>
                  </div>
                </div>
                <div class="actualizacion-info-layout">
                  <section class="actualizacion-info-panel">
                    <div class="actualizacion-info-panel-head">
                      <span><i class="fa fa-list-check me-1"></i>Campos</span>
                      <small class="text-muted" id="actualizacionInfoContador">0 seleccionados</small>
                    </div>
                    <div class="actualizacion-info-panel-body">
                      <div id="actualizacionInfoChecklist" class="d-grid gap-2"></div>
                    </div>
                  </section>
                  <section class="actualizacion-info-panel">
                    <div class="actualizacion-info-panel-head">
                      <span><i class="fa fa-paper-plane me-1"></i>Solicitud a enviar</span>
                    </div>
                    <div class="actualizacion-info-panel-body">
                      <div id="actualizacionInfoCampos">
                        <div class="actualizacion-info-empty">Selecciona campos para crear la solicitud.</div>
                      </div>
                    </div>
                  </section>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary" id="btnGuardarActualizacionInfo">
                  <i class="fa fa-paper-plane me-1"></i>Guardar solicitud
                </button>
              </div>
            </form>
          </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($puedeEditarUsuarioRrhh ?? false)): ?>
        <div class="modal fade" id="modalExpedienteRrhh" tabindex="-1" aria-labelledby="modalExpedienteRrhhLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalExpedienteRrhhLabel">
                  <i class="fa fa-folder-open me-2"></i>Expediente RR.HH.
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <div class="rrhh-expediente-toolbar">
                  <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="rrhhExpedienteIncluirFoto" checked>
                    <label class="form-check-label fw-semibold" for="rrhhExpedienteIncluirFoto">Incluir foto en expediente</label>
                  </div>
                  <div class="d-flex flex-wrap align-items-center gap-2">
                    <input type="file" class="d-none" id="rrhhExpedienteFotoInput" accept="image/*">
                    <div class="rrhh-expediente-photo-dropzone" id="rrhhExpedienteFotoDropzone">
                      Arrastra una foto aqu&iacute; o usa el bot&oacute;n.
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnCambiarFotoExpedienteRrhh">
                      <i class="fa fa-camera me-1"></i>Cambiar foto
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnAjustarFotoExpedienteRrhh">
                      <i class="fa fa-crop-alt me-1"></i>Ajustar foto
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" id="btnAbrirImportacionDocsRrhh">
                      <i class="fa fa-file-import me-1"></i>Importar documentos
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btnLimpiarTodoExpedienteRrhh">
                      <i class="fa fa-trash me-1"></i>Limpiar todo
                    </button>
                  </div>
                </div>
                <div id="rrhhExpedienteContenido"></div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" id="btnVolverRrhhDesdeExpediente">
                  <i class="fa fa-arrow-left me-1"></i>Volver a RR.HH.
                </button>
                <button type="button" class="btn btn-primary" id="btnImprimirExpedienteRrhh">
                  <i class="fa fa-print me-1"></i>Imprimir expediente
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>

        <style>
          .rrhh-import-person-separator > td {
            border-top: 2px solid #263653 !important;
          }
        </style>

        <div class="modal fade" id="modalImportarDocumentosRrhh" tabindex="-1" aria-labelledby="modalImportarDocumentosRrhhLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalImportarDocumentosRrhhLabel">
                  <i class="fa fa-file-import me-2"></i>Importar documentos RR.HH.
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <input type="file" class="d-none" id="rrhhImportDocsInputArchivos" accept=".pdf,.fad,.zip,application/pdf,application/zip" multiple>
                <input type="file" class="d-none" id="rrhhImportDocsInputCarpeta" webkitdirectory directory multiple>
                <div class="d-flex flex-wrap gap-2 align-items-center mb-3">
                  <button type="button" class="btn btn-outline-primary" id="btnRrhhImportSeleccionarArchivos">
                    <i class="fa fa-file-archive me-1"></i>Elegir ZIP/PDF/FAD
                  </button>
                  <button type="button" class="btn btn-outline-primary" id="btnRrhhImportSeleccionarCarpeta">
                    <i class="fa fa-folder-open me-1"></i>Elegir carpeta
                  </button>
                  <button type="button" class="btn btn-success" id="btnRrhhImportImportar" disabled>
                    <i class="fa fa-cloud-arrow-up me-1"></i>Importar listos
                  </button>
                  <button type="button" class="btn btn-outline-secondary" id="btnRrhhImportLimpiar">
                    <i class="fa fa-eraser me-1"></i>Limpiar
                  </button>
                </div>
                <div class="small text-muted mb-1" id="rrhhImportDocsSeleccionResumen">No se han seleccionado archivos.</div>
                <div class="small text-muted mb-3">
                  La carpeta se procesa automaticamente por tandas para evitar rechazos del servidor. Si eliges un ZIP muy grande, descomprimelo y usa Elegir carpeta.
                </div>
                <div id="rrhhImportDocsResumen" class="d-flex flex-wrap gap-2 mb-3"></div>
                <div class="table-responsive" style="max-height: 52vh;">
                  <table class="table table-sm table-striped align-middle">
                    <thead class="table-dark sticky-top">
                      <tr>
                        <th>Estado</th>
                        <th>Persona detectada</th>
                        <th>Tipo de documento</th>
                        <th>Archivo</th>
                        <th>Detalle</th>
                        <th class="text-center">Acciones</th>
                      </tr>
                    </thead>
                    <tbody id="rrhhImportDocsTabla">
                      <tr><td colspan="6" class="text-center text-muted py-4">Selecciona archivos o una carpeta para analizarlos automaticamente.</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="modalRrhhImportPreview" tabindex="-1" aria-labelledby="modalRrhhImportPreviewLabel" aria-hidden="true">
          <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="modalRrhhImportPreviewLabel">Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body p-0">
                <iframe id="rrhhImportPreviewFrame" title="Vista previa documento" style="width: 100%; height: 78vh; border: 0;"></iframe>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="modalFirmaExpedienteRrhh" tabindex="-1" aria-labelledby="modalFirmaExpedienteRrhhLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalFirmaExpedienteRrhhLabel">
                  <i class="fa fa-pen me-2"></i>Capturar firma
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <div class="rrhh-signature-hint mb-2">Firma dentro del recuadro. Puedes repetirla antes de aceptarla.</div>
                <canvas id="rrhhFirmaExpedienteCanvas" class="rrhh-signature-pad"></canvas>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="btnRepetirFirmaExpedienteRrhh">
                  <i class="fa fa-undo me-1"></i>Repetir
                </button>
                <button type="button" class="btn btn-primary" id="btnAceptarFirmaExpedienteRrhh">
                  <i class="fa fa-check me-1"></i>Aceptar firma
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="modalCredencialRrhh" tabindex="-1" aria-labelledby="modalCredencialRrhhLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalCredencialRrhhLabel">
                  <i class="fa fa-id-card me-2"></i>Credencial RR.HH.
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <div class="rrhh-credential-toolbar">
                  <div>
                    <div class="fw-bold text-dark mb-1">Dise&ntilde;o de credencial</div>
                    <div class="small text-muted">Gafete Maxikash v2 vertical, preparado en tama&ntilde;o CR80.</div>
                  </div>
                  <div class="d-flex flex-wrap align-items-end gap-2">
                    <input type="file" class="d-none" id="rrhhCredencialFotoInput" accept="image/*">
                    <div class="rrhh-photo-dropzone" id="rrhhCredencialFotoDropzone">
                      Arrastra una foto aqu&iacute; o usa el bot&oacute;n.
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnCambiarFotoCredencialRrhh">
                      <i class="fa fa-camera me-1"></i>Cambiar foto
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnAjustarFotoCredencialRrhh">
                      <i class="fa fa-crop-alt me-1"></i>Ajustar foto
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btnFotoCompletaCredencialRrhh">
                      <i class="fa fa-expand me-1"></i>Foto completa
                    </button>
                  </div>
                </div>
                <div class="rrhh-credential-notice">
                  <i class="fa fa-info-circle me-1"></i>
                  Esta credencial es personal e intransferible. En caso de extrav&iacute;o, favor de reportarlo a Capital Humano.
                </div>
                <div class="rrhh-credential-stage">
                  <div class="row g-4 align-items-stretch">
                    <div class="col-lg-6">
                      <div class="text-center fw-bold text-muted mb-2">Frente</div>
                      <div id="rrhhCredencialFrente"></div>
                    </div>
                    <div class="col-lg-6">
                      <div class="text-center fw-bold text-muted mb-2">Reverso</div>
                      <div id="rrhhCredencialReverso"></div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" id="btnVolverRrhhDesdeCredencial">
                  <i class="fa fa-arrow-left me-1"></i>Volver a RR.HH.
                </button>
                <button type="button" class="btn btn-primary" id="btnImprimirCredencialRrhh">
                  <i class="fa fa-print me-1"></i>Imprimir credencial
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
            </div>
          </div>
        </div>

        <div class="modal fade" id="modalAjustarFotoCredencialRrhh" tabindex="-1" aria-labelledby="modalAjustarFotoCredencialRrhhLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalAjustarFotoCredencialRrhhLabel">
                  <i class="fa fa-crop-alt me-2"></i>Ajustar foto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body">
                <div class="text-muted mb-3">Mueve la imagen dentro del marco. Usa la rueda del mouse para acercar o alejar.</div>
                <div class="rrhh-photo-editor-layout">
                  <div>
                    <div class="rrhh-photo-editor-panel-title">Vista en credencial</div>
                    <div class="rrhh-photo-live-preview" id="rrhhCredencialFotoLivePreview"></div>
                  </div>
                  <div>
                    <div class="rrhh-photo-editor-panel-title">Ajuste de imagen</div>
                    <div class="rrhh-photo-editor-frame" id="rrhhCredencialFotoEditorFrame">
                      <div class="rrhh-photo-editor-empty">Selecciona una foto</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <div class="rrhh-photo-editor-actions w-100">
                  <button type="button" class="btn btn-outline-secondary me-auto" data-bs-dismiss="modal">Cerrar</button>
                  <button type="button" class="btn btn-outline-warning" id="btnRestaurarAjusteFotoCredencialRrhh">
                    <i class="fa fa-undo me-1"></i>Restaurar
                  </button>
                  <button type="button" class="btn btn-outline-primary" id="btnEditorFotoCompletaCredencialRrhh">
                    <i class="fa fa-expand me-1"></i>Foto completa
                  </button>
                  <button type="button" class="btn btn-primary" id="btnAplicarAjusteFotoCredencialRrhh">
                    <i class="fa fa-crop-alt me-1"></i>Aplicar recorte
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <div class="modal fade" id="modalGestionFotoUsuario" tabindex="-1" aria-labelledby="modalGestionFotoUsuarioLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <div>
                  <h5 class="modal-title mb-0" id="modalGestionFotoUsuarioLabel">
                    <i class="fa fa-image me-2"></i><span id="gestionFotoVisorNombre">Foto de usuario</span>
                  </h5>
                  <div class="gestion-foto-subtitle" id="gestionFotoVisorSubtitulo">Foto de perfil</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>
              <div class="modal-body gestion-foto-body">
                <div class="gestion-foto-stage">
                  <img id="gestionFotoVisorImg" class="gestion-foto-img d-none" src="" alt="">
                  <div id="gestionFotoVisorFallback" class="gestion-foto-fallback d-none">US</div>
                </div>
              </div>
              <div class="gestion-foto-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                  <i class="fa fa-xmark me-1"></i>Cerrar
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- =======================
             FILTRO DE FECHAS PARA BAJAS (oculto por defecto)
        ======================== -->
        <div id="filtroFechaBajas" style="display: none;" class="row m-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <!-- Selector de Fechas Manual (ancho acotado: evita input a todo el ancho de la pantalla) -->
                        <div class="row align-items-end g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    <i class="fa fa-calendar-alt me-2"></i>Rango de Fechas Personalizado
                                </label>
                                <div class="ch-bajas-fp-rango" style="max-width: 19.5rem; width: 100%;">
                                    <input
                                        type="text"
                                        id="flatpickr-range-bajas"
                                        class="form-control form-control-sm"
                                        placeholder="Selecciona un rango de fechas personalizado"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Filtros Rápidos -->
                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold mb-2">
                                    <i class="fa fa-bolt me-2"></i>Filtros Rápidos
                                </label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-filtro-rapido"
                                        data-periodo="ultimo-mes"
                                    >
                                        <i class="fa fa-calendar-day me-1"></i>Ãšltimo Mes
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-filtro-rapido"
                                        data-periodo="ultimos-3-meses"
                                    >
                                        <i class="fa fa-calendar-week me-1"></i>Ãšltimos 3 Meses
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-filtro-rapido"
                                        data-periodo="ultimos-6-meses"
                                    >
                                        <i class="fa fa-calendar me-1"></i>Ãšltimos 6 Meses
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-filtro-rapido"
                                        data-periodo="ano-actual"
                                    >
                                        <i class="fa fa-calendar-alt me-1"></i>Año Actual
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-success btn-filtro-rapido"
                                        data-periodo="todo"
                                    >
                                        <i class="fa fa-infinity me-1"></i>Todo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- =======================
                             PANEL DE INDICADORES DE BAJAS (reposicionado)
                        ======================== -->
                        <div class="mt-3 mb-1">
                            <!-- Tooltip global para donuts (Bajas) -->
                            <div class="kpi-donut-tooltip" id="kpiDonutTooltipB"></div>

                            <div id="panelIndicadoresBajas" style="display: none;">

                                <!-- TOOLBAR: toggle + modos + reset -->
                                <div class="kpi-toolbar">
                                    <button class="kpi-toggle-btn open" id="kpiToggleBtnB" onclick="kpiTogglePanelB()">
                                        <span class="kpi-dot" style="background:#ef4444;"></span>
                                        Indicadores de Bajas
                                        <i class="bx bx-chevron-down kpi-chevron"></i>
                                    </button>

                                    <div class="kpi-toolbar-sep" id="kpiViewControlsSepB"></div>

                                    <div id="kpiViewControlsB">
                                        <button class="kpi-view-btn active" id="vbtn-default-b"  onclick="kpiSetModeB('default')"  data-tip="Vista Estándar">
                                            <i class="bx bx-layout"></i>
                                            <span class="kpi-btn-text">Estándar</span>
                                        </button>
                                        <button class="kpi-view-btn" id="vbtn-vision-b" onclick="kpiSetModeB('vision')" data-tip="Vista de Donut Charts">
                                            <i class="bx bx-doughnut-chart"></i>
                                            <span class="kpi-btn-text">Donut</span>
                                        </button>
                                        <button class="kpi-view-btn" id="vbtn-ministat-b" onclick="kpiSetModeB('ministat')" data-tip="Vista Mini-Stat Compacta">
                                            <i class="bx bx-columns"></i>
                                            <span class="kpi-btn-text">Mini-Stat</span>
                                        </button>
                                        <div class="kpi-toolbar-sep"></div>
                                        <button class="kpi-reset-btn" onclick="kpiResetPrefsB()">
                                            <i class="bx bx-rotate-left"></i>
                                            Restablecer
                                        </button>
                                    </div>
                                </div>

                                <!-- Panel colapsable -->
                                <div class="kpi-collapsible open" id="kpiCollapsibleB">
                                    <div class="kpi-collapsible-inner">
                                        <div class="kpi-row-new mode-default" id="kpiRowNewB">

                                            <!-- â–‘â–‘ TOTAL BAJAS â–‘â–‘ -->
                                            <div class="kpi-cell tipo-baja-total" id="kpi-cell-b-total" data-tipo="bajas-total">
                                                <span class="kpi-corner-icon"><i class="bx bx-user-x"></i></span>
                                                <div class="kpi-cell-top">
                                                    <div class="kpi-icon-wrap"><i class="bx bx-user-x"></i></div>
                                                    <span class="kpi-cell-status">Total</span>
                                                </div>
                                                <div class="kpi-num" id="kpi-b-total">0</div>
                                                <div class="kpi-lbl">Total Bajas</div>
                                                <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-b-total"></div></div>
                                                <span class="kpi-cell-title">Total Bajas</span>
                                                <div class="kpi-stats-grid-new">
                                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-b-total-a">0</div><div class="kpi-stat-lbl">Total</div></div>
                                                    <div class="kpi-stat-div"></div>
                                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-b-total-b">0</div><div class="kpi-ms-pct" id="kpi-ms-pct-b-total">—</div><div class="kpi-stat-lbl" id="kpi-ms-b-total-lbl">Este mes</div></div>
                                                </div>
                                                <div class="donut-block donut-block-bajas">
                                                    <div class="donut-header">
                                                        <span class="donut-title">Total Bajas</span>
                                                        <span class="kpi-cell-status">Total</span>
                                                    </div>
                                                    <div class="donut-bajas-body">
                                                        <div class="donut-bajas-col-left">
                                                            <div class="kpi-stat-item">
                                                                <div class="kpi-stat-val" id="kpi-dv-b-total-a">0</div>
                                                                <div class="kpi-stat-lbl">Total</div>
                                                            </div>
                                                        </div>
                                                        <div class="kpi-stat-div donut-bajas-div"></div>
                                                        <div class="donut-bajas-col-right">
                                                            <div class="donut-svg-wrap"
                                                                 onmouseenter="kpiShowTooltipB(event, document.getElementById('kpi-b-total').textContent + ' bajas registradas')"
                                                                 onmouseleave="kpiHideTooltipB()"
                                                                 onmousemove="kpiMoveTooltipB(event)">
                                                                <svg class="donut-svg" viewBox="0 0 88 88">
                                                                    <circle class="donut-track" cx="44" cy="44" r="36"/>
                                                                    <circle class="donut-arc" id="kpi-arc-b-total" cx="44" cy="44" r="36"/>
                                                                </svg>
                                                                <div class="donut-center-icon"><i class="bx bx-user-x"></i></div>
                                                            </div>
                                                            <div class="kpi-stat-item">
                                                                <div class="kpi-stat-val" id="kpi-dv-b-total-b">0</div>
                                                                <div class="kpi-stat-lbl" id="kpi-dv-b-total-lbl">Este mes</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- â–‘â–‘ DEPARTAMENTOS AFECTADOS â–‘â–‘ -->
                                            <div class="kpi-cell tipo-baja-dep" id="kpi-cell-b-dep" data-tipo="bajas-dep">
                                                <span class="kpi-corner-icon"><i class="bx bx-buildings"></i></span>
                                                <div class="kpi-cell-top">
                                                    <div class="kpi-icon-wrap"><i class="bx bx-buildings"></i></div>
                                                    <span class="kpi-cell-status">Afectados</span>
                                                </div>
                                                <div class="kpi-num" id="kpi-b-dep">0</div>
                                                <div class="kpi-lbl">Departamentos</div>
                                                <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-b-dep"></div></div>
                                                <span class="kpi-cell-title">Departamentos</span>
                                                <div class="kpi-stats-grid-new">
                                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-b-dep-a">0</div><div class="kpi-stat-lbl">Afectados</div></div>
                                                    <div class="kpi-stat-div"></div>
                                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-b-dep-b">0</div><div class="kpi-ms-pct" id="kpi-ms-pct-b-dep">—</div><div class="kpi-stat-lbl">Top bajas</div></div>
                                                </div>
                                                <div class="donut-block donut-block-bajas">
                                                    <div class="donut-header">
                                                        <span class="donut-title">Departamentos</span>
                                                        <span class="kpi-cell-status">Afectados</span>
                                                    </div>
                                                    <div class="donut-ctx-line" id="kpi-ctx-b-dep"></div>
                                                    <div class="donut-bajas-body">
                                                        <div class="donut-bajas-col-left">
                                                            <div class="kpi-stat-item">
                                                                <div class="kpi-stat-val" id="kpi-dv-b-dep-a">0</div>
                                                                <div class="kpi-stat-lbl">Afectados</div>
                                                            </div>
                                                        </div>
                                                        <div class="kpi-stat-div donut-bajas-div"></div>
                                                        <div class="donut-bajas-col-right">
                                                            <div class="donut-svg-wrap"
                                                                 onmouseenter="kpiShowTooltipB(event, document.getElementById('kpi-b-dep').textContent + ' departamentos con bajas')"
                                                                 onmouseleave="kpiHideTooltipB()"
                                                                 onmousemove="kpiMoveTooltipB(event)">
                                                                <svg class="donut-svg" viewBox="0 0 88 88">
                                                                    <circle class="donut-track" cx="44" cy="44" r="36"/>
                                                                    <circle class="donut-arc" id="kpi-arc-b-dep" cx="44" cy="44" r="36"/>
                                                                </svg>
                                                                <div class="donut-center-icon"><i class="bx bx-buildings"></i></div>
                                                            </div>
                                                            <div class="kpi-stat-item">
                                                                <div class="kpi-stat-val" id="kpi-dv-b-dep-b">0</div>
                                                                <div class="kpi-stat-lbl">Top bajas</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- â–‘â–‘ PUESTOS AFECTADOS â–‘â–‘ -->
                                            <div class="kpi-cell tipo-baja-puesto" id="kpi-cell-b-puesto" data-tipo="bajas-puesto">
                                                <span class="kpi-corner-icon"><i class="bx bx-briefcase"></i></span>
                                                <div class="kpi-cell-top">
                                                    <div class="kpi-icon-wrap"><i class="bx bx-briefcase"></i></div>
                                                    <span class="kpi-cell-status">Afectados</span>
                                                </div>
                                                <div class="kpi-num" id="kpi-b-puesto">0</div>
                                                <div class="kpi-lbl">Puestos</div>
                                                <div class="kpi-bar-track"><div class="kpi-bar-fill" id="kpi-bar-b-puesto"></div></div>
                                                <span class="kpi-cell-title">Puestos</span>
                                                <div class="kpi-stats-grid-new">
                                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-b-puesto-a">0</div><div class="kpi-stat-lbl">Afectados</div></div>
                                                    <div class="kpi-stat-div"></div>
                                                    <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-ms-b-puesto-b">0</div><div class="kpi-ms-pct" id="kpi-ms-pct-b-puesto">—</div><div class="kpi-stat-lbl">Top bajas</div></div>
                                                </div>
                                                <div class="donut-block donut-block-bajas">
                                                    <div class="donut-header">
                                                        <span class="donut-title">Puestos</span>
                                                        <span class="kpi-cell-status">Afectados</span>
                                                    </div>
                                                    <div class="donut-ctx-line" id="kpi-ctx-b-puesto"></div>
                                                    <div class="donut-bajas-body">
                                                        <div class="donut-bajas-col-left">
                                                            <div class="kpi-stat-item">
                                                                <div class="kpi-stat-val" id="kpi-dv-b-puesto-a">0</div>
                                                                <div class="kpi-stat-lbl">Afectados</div>
                                                            </div>
                                                        </div>
                                                        <div class="kpi-stat-div donut-bajas-div"></div>
                                                        <div class="donut-bajas-col-right">
                                                            <div class="donut-svg-wrap"
                                                                 onmouseenter="kpiShowTooltipB(event, document.getElementById('kpi-b-puesto').textContent + ' puestos con bajas')"
                                                                 onmouseleave="kpiHideTooltipB()"
                                                                 onmousemove="kpiMoveTooltipB(event)">
                                                                <svg class="donut-svg" viewBox="0 0 88 88">
                                                                    <circle class="donut-track" cx="44" cy="44" r="36"/>
                                                                    <circle class="donut-arc" id="kpi-arc-b-puesto" cx="44" cy="44" r="36"/>
                                                                </svg>
                                                                <div class="donut-center-icon"><i class="bx bx-briefcase"></i></div>
                                                            </div>
                                                            <div class="kpi-stat-item">
                                                                <div class="kpi-stat-val" id="kpi-dv-b-puesto-b">0</div>
                                                                <div class="kpi-stat-lbl">Top bajas</div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div><!-- /kpi-row-new -->
                                    </div>
                                </div><!-- /kpi-collapsible -->

                            </div><!-- /panelIndicadoresBajas -->
                        </div><!-- /panel-bajas-wrapper -->

                        <div class="mb-5"></div>

                        <!-- Botones de Acción -->
                        <div class="row g-3">
                            <div class="col-12 d-flex gap-2 justify-content-end">
                                <button
                                    type="button"
                                    id="btnLimpiarFiltroBajas"
                                    class="btn text-white"
                                    style="background-color: #d2d755; border-color: #d2d755;"
                                >
                                    <i class="fa fa-times me-2"></i>Limpiar Filtro
                                </button>
                                <button
                                    type="button"
                                    id="btnDescargarBajas"
                                    class="btn text-white"
                                    style="background-color: #0047bb; border-color: #0047bb;"
                                >
                                    <i class="fa fa-download me-2"></i>Descargar Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- =======================
             TABLA
        ======================== -->
        <div class="card-datatable table-responsive">
            <table id="historialUsuarios" class="dt-responsive table border-top">
                <thead>
                <tr>
                    <th></th> <!-- control responsive -->
                    <th>Nombre</th>
                    <th>Departamento</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>

    <!-- =======================
         OFFCANVAS - AGREGAR
    ======================== -->
    <div class="offcanvas offcanvas-end" id="offcanvasAddUser" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Registrar Nuevo Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-6">
            <form id="addNewUserForm" onsubmit="return false">

                <div class="mb-3">
                    <label class="form-label d-block">Tipo de registro *</label>
                    <div class="btn-group w-100" role="group" aria-label="Tipo de registro">
                        <?php if (!empty($puedeRegistrarPersonaGestion ?? false)): ?>
                        <input type="radio" class="btn-check" name="add_tipo_registro" id="add_tipo_persona" value="persona" autocomplete="off" checked>
                        <label class="btn btn-outline-primary" for="add_tipo_persona">
                            <i class="fa fa-user me-1"></i>Persona
                        </label>
                        <?php endif; ?>
                        <input type="radio" class="btn-check" name="add_tipo_registro" id="add_tipo_vacante" value="vacante" autocomplete="off" <?= empty($puedeRegistrarPersonaGestion ?? false) ? 'checked' : '' ?>>
                        <label class="btn btn-outline-warning" for="add_tipo_vacante">
                            <i class="fa fa-briefcase me-1"></i>Vacante
                        </label>
                    </div>
                </div>

                <div class="col-md-5 d-none">
                    <div class="mb-2">
                        <label class="form-label">Número de Empleado *</label>
                        <input type="text" id="add_num_telefono" class="form-control phone-mask">
                    </div>
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">Nombre *</label>
                    <input type="text" id="add_nombres" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">Segundo Nombre (Opcional)</label>
                    <input type="text" id="add_segundo_nombre" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="add_apellidop" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="add_apellidom" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">CURP *</label>
                    <input type="text" id="add_curp" class="form-control" maxlength="18" placeholder="18 caracteres" autocomplete="off" required style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase().replace(/[^A-ZÃ‘0-9]/g, '')" onblur="this.value = this.value.trim()">
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="add_telefono" class="form-control phone-mask" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onblur="validarTelefono('add_telefono')" maxlength="10">
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">País (Sede) *</label>
                    <select id="add_id_pais" class="form-select js-select-buscador">
                        <option value="">Seleccione un país</option>
                        <?php foreach (($paisesActivos ?? []) as $pais): ?>
                            <option value="<?= htmlspecialchars($pais['id']) ?>" data-iso="<?= htmlspecialchars($pais['codigo_iso']) ?>">
                                <?= htmlspecialchars($pais['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2 add-persona-only" id="div_add_estado" style="display:none;">
    <label class="form-label" id="label_add_estado">Estado *</label>
    <select id="add_id_div_nivel1" class="form-select js-select-buscador" disabled>
        <option value="">Seleccione un estado</option>
    </select>
</div>

<div class="mb-2 add-persona-only" id="div_add_municipio" style="display:none;">
    <label class="form-label" id="label_add_municipio">Municipio *</label>
    <select id="add_id_div_nivel2" class="form-select js-select-buscador" disabled>
        <option value="">Seleccione un municipio</option>
    </select>
</div>

<div class="mb-2 add-persona-only" id="div_add_colonia" style="display:none;">
    <label class="form-label">Colonia</label>
    <select id="add_id_div_nivel3" class="form-select js-select-buscador" disabled>
        <option value="">Seleccione una colonia</option>
    </select>
</div>
<div class="mb-2 add-persona-only" id="div_add_calle" style="display:none;">
    <label class="form-label">Calle</label>
    <select id="add_id_div_nivel4" class="form-select js-select-buscador" disabled>
        <option value="">Seleccione una calle</option>
    </select>
</div>
<div class="mb-2 add-persona-only" id="div_add_calle_texto" style="display:none;">
    <label class="form-label">Calle</label>
    <input type="text" id="add_domicilio_calle_texto" class="form-control" maxlength="180">
</div>
<div class="row mb-2 add-persona-only" id="div_add_num_extint" style="display:none;">
    <div class="col-md-6">
        <label class="form-label">No. exterior *</label>
        <input type="text" id="add_domicilio_num_exterior" class="form-control" maxlength="32">
    </div>
    <div class="col-md-6">
        <label class="form-label">No. interior (opcional)</label>
        <input type="text" id="add_domicilio_num_interior" class="form-control" maxlength="32">
    </div>
</div>
<div class="mb-2 add-persona-only" id="div_add_cp" style="display:none;">
    <label class="form-label">Código postal</label>
    <input type="text" id="add_codigo_postal" class="form-control" maxlength="10" readonly>
</div>

                <div class="mb-2">
                    <label class="form-label">Departamento *</label>
                    <select id="add_departamento_id" class="form-select js-select-buscador">
                        <option value="">Seleccione un departamento</option>
                        <?php foreach ($departamento['datos'] ?? [] as $dep): ?>
                            <option value="<?= htmlspecialchars($dep['id']) ?>" data-pais="<?= htmlspecialchars($dep['id_pais'] ?? '') ?>">
                                <?= htmlspecialchars($dep['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Puesto *</label>
                    <select id="add_id_puesto" class="form-select js-select-buscador" disabled>
                        <option value="">Seleccione un puesto</option>
                    </select>
                </div>

                <div class="mb-2 add-persona-only d-none" id="add_vacante_wrap">
                    <label class="form-label">Vacante disponible</label>
                    <select id="add_vacante_existente_id" class="form-select js-select-buscador" disabled>
                        <option value="">Sin vacante seleccionada</option>
                    </select>
                    <div class="form-text">Opcional. Si eliges una vacante, quedara ocupada por este usuario.</div>
                </div>

                <div class="mb-6">
                    <label class="form-label">Jefe *</label>
                    <select id="add_id_jefe" class="form-select js-select-buscador" disabled>
                        <option value="">Seleccione un jefe</option>
                    </select>
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">Fecha de ingreso <span class="text-danger">*</span></label>
                    <div class="fecha-acta-wrapper" id="fecha_acta_wrapper">
                        <input type="text" id="add_fecha_ingreso" class="form-control" placeholder="YYYY-MM-DD">
                    </div>
                </div>

                <div class="mb-3 add-persona-only">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="add_asignar_legion" onchange="toggleSelectLegion()">
                        <label class="form-check-label" for="add_asignar_legion">
                            Asignar legión
                        </label>
                    </div>
                </div>

                <div class="mb-3 add-persona-only">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="add_es_externo">
                        <label class="form-check-label" for="add_es_externo">
                            Externo
                        </label>
                    </div>
                </div>

                <div class="mb-2 add-persona-only" id="div_select_legion" style="display: none;">
                    <label class="form-label">Legión *</label>
                    <select id="add_id_legion" class="form-select js-select-buscador">
                        <option value="">Seleccione una legión</option>
                        <option value="1">Sabueso</option>
                        <option value="2">Heraldo</option>
                        <option value="3">Centinela</option>
                        <option value="4">Senturiones</option>
                        <option value="5">Espartano</option>
                    </select>
                </div>

                <div class="mb-2 add-persona-only">
                    <label class="form-label">Usuario *</label>
                    <input type="text" id="add_usuario" class="form-control" maxlength="10" oninput="this.value = this.value.replace(/[^A-Za-z0-9]/g, '').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-7 add-persona-only">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="add_contrasena" class="form-control" maxlength="15" oninput="this.value = this.value.replace(/^\s+/, '').replace(/\s{2,}/g, ' ')" onblur="this.value = this.value.trim()">
                </div>

                <button type="button" class="btn btn-primary me-3" onclick="guardarGestor()">Guardar</button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>

            </form>
        </div>
    </div>

    <!-- =======================
           OFFCANVAS -
      ======================== -->
    <!-- Modal RFC -->
    <div class="modal fade" id="modalBajas" tabindex="-1" aria-labelledby="modalRFCLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-bajas-dialog">
            <div class="modal-content modal-bajas-shell">
                <div class="baja-main-panel">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalRFCLabel">Registro de Baja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p id="gestor"><strong>Gestor:</strong> </p>

                    <div class="mb-3" style="display: none;">
                        <label for="id" class="form-label"><strong>Id</strong></label>
                    </div>

                    <div class="mb-3">
                        <label for="motivoBaja" class="form-label"><strong>Motivo de baja: </strong></label>
                        <select class="form-select" id="motivoBaja">
                            <option value="">-- Selecciona un motivo --</option>
                            <option value="Renuncia voluntaria">Renuncia voluntaria</option>
                            <option value="Falta de probidad">Falta de probidad</option>
                            <option value="Faltas injustificadas">Faltas injustificadas</option>
                            <option value="Bajo rendimiento">Bajo rendimiento</option>
                            <option value="Cambio de área">Cambio de área</option>
                            <option value="Conciliación">Conciliación</option>
                        </select>
                    </div>

                    <!-- ðŸ†• Motivo de baja -->
                    <div class="mb-3">
                        <label for="motivoBajaDescripcion" class="form-label"><strong>Descripción de la baja:</strong></label>
                        <textarea class="form-control" id="motivoBajaDescripcion" rows="3" placeholder="Escribe el motivo..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Documento de baja (PDF):</strong></label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input
                                type="file"
                                id="archivoPDF"
                                class="form-control d-none"
                                accept=".pdf"
                                multiple
                            />
                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                onclick="seleccionarArchivoBajaModal()"
                            >
                                <i class="fa fa-paperclip me-2"></i>Elegir archivos
                            </button>
                            <span id="bajaModal_nombreArchivo" class="text-muted small">No se ha seleccionado ningún archivo</span>
                        </div>
                        <small class="text-muted">Puedes subir múltiples archivos PDF.</small>
                    </div>
                    <div id="listaArchivos" class="mt-2" style="display: none;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                    <!-- ðŸ†• Botón para confirmar baja -->
                    <button type="button" class="btn btn-danger" onclick="confirmarBaja()">
                        Confirmar Baja
                    </button>
                </div>
                </div>

                <div class="baja-side-panel">
                    <div class="baja-subordinados-box">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                            <div>
                                <div class="fw-semibold">Destino de los subordinados</div>
                                <div class="small text-muted">Define que pasara con el equipo</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span id="bajaSubordinadosCount" class="badge bg-warning-subtle text-warning-emphasis rounded-pill">0</span>
                                <button type="button" class="btn-close btn-sm" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                        </div>

                        <div id="bajaReasignacionWrap" style="display: none;">
                            <div class="d-grid gap-2">
                                <label class="border rounded-3 p-3 bg-body d-flex gap-2 align-items-start mb-0">
                                    <input class="form-check-input mt-1" type="radio" name="bajaModoReasignacion" id="bajaModoVacante" value="vacante" checked>
                                    <span>
                                        <span class="fw-semibold d-block">Vacante</span>
                                        <span class="small text-muted">Quedan sin jefe asignado temporalmente.</span>
                                    </span>
                                </label>
                                <label class="border rounded-3 p-3 bg-body d-flex gap-2 align-items-start mb-0">
                                    <input class="form-check-input mt-1" type="radio" name="bajaModoReasignacion" id="bajaModoSustituto" value="sustituto">
                                    <span>
                                        <span class="fw-semibold d-block">Sustituto</span>
                                        <span class="small text-muted">Pasaran al jefe sustituto elegido.</span>
                                    </span>
                                </label>
                            </div>

                            <div id="bajaVacanteResumen" class="alert alert-warning bg-warning-subtle border border-warning-subtle text-warning-emphasis small mt-3 mb-0" role="alert">
                                <div class="fw-semibold">Se registrara como vacante</div>
                                <div id="bajaVacantesMismoPuesto" class="mt-2"></div>
                            </div>

                            <div id="bajaSubordinadosDetalleWrap" class="mt-3 d-none">
                                <div class="card border bg-body shadow-sm">
                                    <div class="card-header bg-body py-2 d-flex align-items-center justify-content-between gap-2">
                                        <div class="fw-semibold small">Personas a reasignar</div>
                                        <input type="search" id="bajaBuscarSubordinado" class="form-control form-control-sm w-50" placeholder="Buscar...">
                                    </div>
                                    <div class="card-body p-0">
                                        <div id="bajaSubordinadosLista" class="list-group list-group-flush small overflow-auto" style="max-height: 220px;">
                                            Selecciona una persona para cargar sus subordinados.
                                        </div>
                                    </div>
                                </div>

                                <div id="bajaSustitutoWrap" class="mt-3" style="display: none;">
                                    <label for="bajaSustitutoId" class="form-label"><strong>Jefe destino:</strong></label>
                                    <div class="d-grid gap-2 w-100 overflow-hidden">
                                        <select id="bajaSustitutoId" class="form-select form-select-lg js-select-buscador">
                                            <option value="">Cargando personas...</option>
                                        </select>
                                        <button type="button" id="bajaAplicarJefeSeleccionados" class="btn btn-outline-primary">
                                            Aplicar
                                        </button>
                                    </div>
                                    <div id="bajaResumenAsignacionesJefe" class="mt-2"></div>
                                    <small class="text-muted">Selecciona personas, elige un jefe destino y aplica la asignacion.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================
         MODAL - REGISTRO DE REINGRESO
    ======================== -->
    <div class="modal fade" id="modalReingreso" tabindex="-1" aria-labelledby="modalReingresoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-reingreso-dialog">
            <div class="modal-content modal-reingreso-glass">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalReingresoLabel">Registro de Reingreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p id="reingreso_gestor"><strong>Gestor:</strong> </p>
                    <input type="hidden" id="reingreso_id_persona" value="">

                    <div class="mb-3">
                        <label for="motivoReingreso" class="form-label"><strong>Motivo del reingreso:</strong></label>
                        <select class="form-select" id="motivoReingreso" data-motivos-rh-actualizados="1">
                            <option value="">-- Selecciona un motivo --</option>
                            <option value="ILOCALIZABLE">ILOCALIZABLE</option>
                            <option value="LOCALIZABLE EN DOMICILIO PRINCIPAL">LOCALIZABLE EN DOMICILIO PRINCIPAL</option>
                            <option value="LOCALIZABLE EN DOMICILIO ALTERNO (FIRMA, BURO Y FRACTURA)">LOCALIZABLE EN DOMICILIO ALTERNO (FIRMA, BURO Y FRACTURA)</option>
                            <option value="LOCALIZABLE EN DOMICILIO DE RIESGO">LOCALIZABLE EN DOMICILIO DE RIESGO</option>
                            <option value="GESTIÃ“N INCOMPLETA">GESTIÃ“N INCOMPLETA</option>
                            <option value="DUAL || ZONIFICACIÃ“N">DUAL || ZONIFICACIÃ“N</option>
                            <option value="FALTA VISITAR DOMICILIOS ALTERNOS">FALTA VISITAR DOMICILIOS ALTERNOS</option>
                            <option value="FALTA INTENSIDAD DE GESTIÃ“N">FALTA INTENSIDAD DE GESTIÃ“N</option>
                            <option value="GESTIONADA VÍA TELEFÃ“NICA">GESTIONADA VÍA TELEFÃ“NICA</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="reingreso_descripcion" class="form-label"><strong>Descripción del reingreso:</strong></label>
                        <textarea class="form-control" id="reingreso_descripcion" rows="3" placeholder="Escribe la descripción..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Documento de reingreso (PDF):</strong></label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input type="file" id="archivoPDFReingreso" class="form-control d-none" accept=".pdf" multiple onchange="typeof agregarArchivosReingreso === 'function' && agregarArchivosReingreso(this)" />
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('archivoPDFReingreso').click()">
                                <i class="fa fa-paperclip me-2"></i>Elegir archivos
                            </button>
                            <span id="reingreso_nombreArchivo" class="text-muted small">No se ha seleccionado ningún archivo</span>
                        </div>
                        <small class="text-muted">Puedes subir múltiples archivos PDF.</small>
                    </div>
                    <div id="listaArchivosReingreso" class="mt-2" style="display: none;"></div>
                    <script>
                        (function () {
                            var sel = document.getElementById('motivoReingreso');
                            if (!sel || sel.getAttribute('data-motivos-rh-renderizados') === '1') return;
                            var motivos = [
                                '',
                                'Reingreso por solicitud del Subdirector - Director',
                                'Motivos personales/familiares',
                                'Recontratación por reestructura / recorte previo',
                                'Reingreso por buen desempeño previo',
                                'No procedio la baja'
                            ];
                            sel.innerHTML = '';
                            motivos.forEach(function (motivo, index) {
                                var option = document.createElement('option');
                                option.value = motivo;
                                option.textContent = index === 0 ? '-- Selecciona un motivo --' : motivo;
                                sel.appendChild(option);
                            });
                            sel.setAttribute('data-motivos-rh-renderizados', '1');
                        })();
                    </script>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-success" onclick="confirmarReingreso()">
                        <i class="fa fa-user-check me-1"></i>Confirmar Reingreso
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================
         MODAL - CARGAR DOCUMENTO BAJA
    ======================== -->
    <div class="modal fade" id="modalCargarDocumentoBaja" tabindex="-1" aria-labelledby="modalCargarDocLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCargarDocLabel">Cargar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p id="cargarDoc_nombrePersona" class="mb-3"><strong>Persona:</strong></p>
                    <input type="hidden" id="cargarDoc_registroBaja" value="">

                    <div class="mb-3">
                        <label for="cargarDoc_tipoDocumento" class="form-label"><strong>Tipo de Documento: </strong></label>
                        <select class="form-select" id="cargarDoc_tipoDocumento">
                            <option value="Documento Baja">Documento Baja</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Archivo:</strong></label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input
                                type="file"
                                id="cargarDoc_archivo"
                                class="form-control d-none"
                                onchange="agregarArchivoLista(this)"
                                accept=".pdf"
                                multiple
                            />
                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                onclick="seleccionarArchivoDocumento()"
                            >
                                <i class="fa fa-paperclip me-2"></i>Elegir archivos
                            </button>
                            <span id="cargarDoc_nombreArchivo" class="text-muted small">No se ha seleccionado ningún archivo</span>
                        </div>
                        <small class="text-muted">Puedes subir múltiples archivos PDF.</small>
                    </div>

                    <!-- Lista de archivos nuevos seleccionados (antes de subir) - ARRIBA DE LA TABLA -->
                    <div id="cargarDoc_listaArchivos" class="mb-4" style="display: none;">
                        <h6 class="mb-3"><strong>Archivos Nuevos Seleccionados</strong></h6>
                        <!-- Los archivos nuevos se agregarán aquí dinámicamente -->
                    </div>

                    <!-- Tabla de archivos subidos -->
                    <div class="mt-4">
                        <h6 class="mb-3"><strong>Archivos Subidos</strong></h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Documento</th>
                                        <th>Archivo</th>
                                        <th>Fecha de carga</th>
                                        <th>Válido</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cargarDoc_tablaArchivos">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay archivos subidos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="subirDocumentoBaja()">
                        <i class="fa fa-upload me-2"></i>Subir Archivo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================
         MODAL - CARGAR DOCUMENTO PERSONA (GESTIÃ“N)
    ======================== -->
    <div class="modal fade" id="modalCargarDocumentoPersona" tabindex="-1" aria-labelledby="modalCargarDocPersonaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCargarDocPersonaLabel">Cargar Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <p id="cargarDocPersona_nombrePersona" class="mb-3"><strong>Persona:</strong></p>
                    <input type="hidden" id="cargarDocPersona_idPersona" value="">

                    <div class="mb-3">
                        <label for="cargarDocPersona_tipoDocumento" class="form-label"><strong>Tipo de Documento: </strong></label>
                        <select class="form-select" id="cargarDocPersona_tipoDocumento">
                            <option value="">Seleccione un tipo de documento</option>
                            <?php
                            $modulosSesionDocumentoRrhh = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
                            $usuarioSesionDocumentoRrhh = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
                            $modulosDocumentoRrhhSelect = [
                                8 => 155,
                                9 => 156,
                                10 => 157,
                                11 => 158,
                                12 => 159,
                                13 => 160,
                                14 => 161,
                                15 => 162,
                                16 => 163,
                                17 => 164,
                                18 => 165,
                                22 => 166,
                                23 => 167,
                                24 => 168,
                                25 => 169,
                                27 => 170,
                                28 => 171,
                                29 => 172,
                                30 => 173,
                                31 => 174,
                                32 => 175,
                                33 => 176,
                                34 => 177,
                                35 => 178,
                                36 => 179,
                                37 => 184,
                                38 => 185,
                            ];
                            $tiposDocumentoRrhhSelect = [
                                12 => 'Acta de Nacimiento',
                                29 => 'Archivo .FAD',
                                27 => 'Carta de compromiso del Gestor',
                                13 => 'Certificado de Estudios',
                                11 => 'Comprobante de Domicilio',
                                28 => 'Contrato firmado',
                                8 => 'CURP',
                                15 => 'Documento baja',
                                16 => 'Documento reingreso',
                                25 => 'Estado de cuenta',
                                24 => 'Hoja de retencion FONACOT o INFONAVIT',
                                9 => 'Identificación Oficial (INE)',
                                31 => 'Llave vector',
                                32 => 'Prueba centavo',
                                14 => 'Referencias Laborales',
                                10 => 'RFC',
                                33 => 'Semanas cotizadas IMSS (segundos patrones)',
                                17 => 'Solicitud interna',
                                18 => 'CV o solicitud de trabajo',
                                30 => 'Validacion SAT',
                                22 => 'Constancia de Situacion Fiscal',
                                23 => 'Numero de Seguridad Social',
                                34 => 'Documento incapacidad',
                                35 => 'Documento permiso',
                                36 => 'Documento falta',
                                37 => 'Finiquito',
                                38 => 'Comprobante de pago finiquito',
                            ];
                            foreach ($tiposDocumentoRrhhSelect as $idDocumentoRrhh => $nombreDocumentoRrhh):
                                $idDocumentoRrhh = (int) $idDocumentoRrhh;
                                $idModuloNuevoDocumentoRrhh = (int) ($modulosDocumentoRrhhSelect[$idDocumentoRrhh] ?? 0);
                                $tienePermisoDocumentoRrhh = $usuarioSesionDocumentoRrhh === 1
                                    || ($idModuloNuevoDocumentoRrhh > 0 && in_array($idModuloNuevoDocumentoRrhh, $modulosSesionDocumentoRrhh, true))
                                    || in_array(3000 + $idDocumentoRrhh, $modulosSesionDocumentoRrhh, true)
                                    || ($idDocumentoRrhh === 27 && in_array(144, $modulosSesionDocumentoRrhh, true));
                                if (!$tienePermisoDocumentoRrhh) {
                                    continue;
                                }
                            ?>
                                <option value="<?= htmlspecialchars($nombreDocumentoRrhh, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($nombreDocumentoRrhh, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Archivo:</strong></label>
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <input
                                type="file"
                                id="cargarDocPersona_archivo"
                                class="form-control d-none"
                                onchange="agregarArchivoListaPersona(this)"
                                accept=".pdf,.fad"
                            />
                            <button
                                type="button"
                                class="btn btn-outline-primary"
                                onclick="seleccionarArchivoDocumentoPersona()"
                            >
                                <i class="fa fa-paperclip me-2"></i>Elegir archivos
                            </button>
                            <span id="cargarDocPersona_nombreArchivo" class="text-muted small">No se ha seleccionado ningún archivo</span>
                        </div>
                        <small class="text-muted">Puedes subir PDF. Para documentos generados por FAD también se permite archivo .FAD.</small>
                    </div>

                    <!-- Lista de archivos nuevos seleccionados (antes de subir) - ARRIBA DE LA TABLA -->
                    <div id="cargarDocPersona_listaArchivos" class="mb-4" style="display: none;">
                        <h6 class="mb-3"><strong>Archivos Nuevos Seleccionados</strong></h6>
                        <!-- Los archivos nuevos se agregarán aquí dinámicamente -->
                    </div>

                    <!-- Tabla de archivos subidos -->
                    <div class="mt-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <h6 class="mb-0"><strong>Archivos Subidos</strong></h6>
                            <div class="d-flex flex-wrap gap-2" id="accionesDocsPersona" style="display: none;">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnDescargarDocsPersona" onclick="abrirMenuDescargaDocumentosPersona()" disabled>
                                    <i class="fa fa-download me-1"></i>Descargar
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="width: 44px; display: none;" class="text-center col-seleccion-docs-persona">
                                            <input type="checkbox" class="form-check-input" id="checkTodosDocsPersona" onchange="toggleSeleccionDocumentosPersona(this.checked)">
                                        </th>
                                        <th style="width: 120px;">Formato</th>
                                        <th>Documento</th>
                                        <th>Contexto</th>
                                        <th>Archivo</th>
                                        <th>Fecha de carga</th>
                                        <th>Válido</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cargarDocPersona_tablaArchivos">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No hay archivos subidos</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="subirDocumentoPersona()">
                        <i class="fa fa-upload me-2"></i>Subir Archivo
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAuscencia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Registro de Ausencias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">



                    <!-- ================== DATOS DEL GESTOR ================== -->
                    <p id="gestor_ausencia" class="mb-3"><strong>Gestor:</strong></p>
                    <input type="hidden" id="edit_id_ausencia">

                    <hr>

                    <!-- ================== FORMULARIO AUSENCIA ================== -->
                    <div class="row g-3">

                        <input type="hidden" id="id_ausencia">

                        <div class="col-md-4">
                            <label class="form-label"><strong>Razón de ausencia</strong></label>
                            <select class="form-select" id="razonAusencia">
                                <option value="">-- Selecciona --</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Fecha inicio</strong></label>
                            <input type="text" class="form-control flatpickr-datetime-ausencia" id="fechaInicio" placeholder="Seleccione fecha inicio" autocomplete="off" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Fecha fin</strong></label>
                            <input type="text" class="form-control flatpickr-datetime-ausencia" id="fechaFin" placeholder="Seleccione fecha fin" autocomplete="off" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label"><strong>Descripción</strong></label>
                            <textarea class="form-control" id="descripcionAusencia" rows="2"></textarea>
                        </div>

                    </div>

                    <div class="mt-3 d-flex flex-wrap gap-2">
                        <button class="btn btn-primary"  id="btnGuardarAusencia" onclick="guardarAusencia()">
                            Registrar ausencia
                        </button>
                        <button class="btn btn-outline-primary" type="button" id="btnAdjuntarDocumentoAusencia" onclick="abrirCargaDocumentoAusencia()">
                            <i class="fa fa-paperclip me-1"></i>Adjuntar documento
                        </button>
                        <input
                            type="file"
                            id="archivoDocumentoAusencia"
                            class="d-none"
                            accept=".pdf,application/pdf"
                            onchange="prepararDocumentoAusenciaSeleccionado(this)"
                        >
                        <span id="estadoDocumentoAusencia" class="small text-muted align-self-center"></span>
                    </div>

                    <div id="previewDocumentoAusencia" class="mt-3 d-none border rounded p-3 bg-light">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <div class="fw-semibold" id="previewDocumentoAusenciaTipo">Documento de ausencia</div>
                                <div class="small text-muted" id="previewDocumentoAusenciaNombre">Sin archivo seleccionado</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="verDocumentoAusenciaSeleccionado()">
                                    <i class="fa fa-eye me-1"></i>Ver
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="limpiarDocumentoAusenciaSeleccionado()">
                                    <i class="fa fa-times me-1"></i>Quitar
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-2"><strong>Historial de ausencias</strong></h6>

                    <div id="ausenciaHistorialEmpty" class="ausencia-empty-state">
                        <i class="fa fa-calendar-check d-block"></i>
                        <div class="fw-semibold">Sin ausencias registradas.</div>
                        <div class="small">Cuando registres una ausencia con sus documentos adjuntos, aparecerá aquí.</div>
                    </div>

                    <div id="ausenciaHistorialContenido" class="d-none">
                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-bordered table-striped">
                                <thead class="table-light">
                                <tr>
                                    <th>Razón</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Descripción</th>
                                    <th>Documento</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody id="tablaAusencias"></tbody>
                            </table>
                        </div>

                        <div id="listaDocumentosAusencia" class="list-group small"></div>
                    </div>

                </div>


                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDocumentoAusenciaPreview" tabindex="-1" aria-labelledby="modalDocumentoAusenciaPreviewLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header">
                    <h5 class="modal-title d-flex align-items-center gap-2" id="modalDocumentoAusenciaPreviewLabel">
                        <i class="fa fa-file-pdf text-danger"></i>
                        <span id="modalDocumentoAusenciaPreviewTitulo">Documento de ausencia</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-0 bg-light">
                    <iframe
                        id="modalDocumentoAusenciaPreviewFrame"
                        title="Vista previa documento de ausencia"
                        src="about:blank"
                        style="display:block;width:100%;height:min(78vh,780px);border:0;background:#fff;"
                    ></iframe>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- =======================
        OFFCANVAS - EDITAR
   ======================== -->
    <div class="offcanvas offcanvas-end" id="offcanvasEditUser">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvasEditUserTitle">Editar Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-6">
            <form id="editNewUserForm" onsubmit="return false">

                <div class="mb-2" style="display: none">
                    <label class="form-label">Id Empleado *</label>
                    <input type="text" id="edit_id" class="form-control phone-mask" disabled>
                </div>

                <div class="mb-2">
                    <label class="form-label">Número de Empleado *</label>
                    <input type="text" id="edit_num_empleado" class="form-control phone-mask" disabled>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" id="edit_nombres" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Segundo Nombre (Opcional)</label>
                    <input type="text" id="edit_segundo_nombre" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="edit_apellidop" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="edit_apellidom" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">CURP (opcional)</label>
                    <input type="text" id="edit_curp" class="form-control" maxlength="18" placeholder="18 caracteres" autocomplete="off" style="text-transform: uppercase;" oninput="this.value = this.value.toUpperCase().replace(/[^A-ZÃ‘0-9]/g, '')" onblur="this.value = this.value.trim()">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="edit_telefono" class="form-control phone-mask" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onblur="validarTelefono('edit_telefono')" maxlength="10">
                </div>

                <div class="mb-2">
                    <label class="form-label">Correo (opcional)</label>
                    <input type="email" id="edit_correo" class="form-control" maxlength="160" placeholder="correo@dominio.com" autocomplete="off" oninput="this.value = this.value.trim().toLowerCase()">
                </div>

                <div class="mb-2" id="div_edit_estado" style="display:none;">
    <label class="form-label" id="label_edit_estado">Estado</label>
    <select id="edit_id_div_nivel1" class="form-select js-select-buscador">
        <option value="">Seleccione un estado</option>
    </select>
</div>

<div class="mb-2" id="div_edit_municipio" style="display:none;">
    <label class="form-label" id="label_edit_municipio">Municipio</label>
    <select id="edit_id_div_nivel2" class="form-select js-select-buscador">
        <option value="">Seleccione un municipio</option>
    </select>
</div>

<div class="mb-2" id="div_edit_colonia" style="display:none;">
    <label class="form-label">Colonia</label>
    <select id="edit_id_div_nivel3" class="form-select js-select-buscador" disabled>
        <option value="">Seleccione una colonia</option>
    </select>
</div>
<div class="mb-2" id="div_edit_calle" style="display:none;">
    <label class="form-label">Calle</label>
    <select id="edit_id_div_nivel4" class="form-select js-select-buscador" disabled>
        <option value="">Seleccione una calle</option>
    </select>
</div>
<div class="mb-2" id="div_edit_calle_texto" style="display:none;">
    <label class="form-label">Calle</label>
    <input type="text" id="edit_domicilio_calle_texto" class="form-control" maxlength="180">
</div>
<div class="row mb-2" id="div_edit_num_extint" style="display:none;">
    <div class="col-md-6">
        <label class="form-label">No. exterior *</label>
        <input type="text" id="edit_domicilio_num_exterior" class="form-control" maxlength="32">
    </div>
    <div class="col-md-6">
        <label class="form-label">No. interior (opcional)</label>
        <input type="text" id="edit_domicilio_num_interior" class="form-control" maxlength="32">
    </div>
</div>
<div class="mb-2" id="div_edit_cp" style="display:none;">
    <label class="form-label">Código postal</label>
    <input type="text" id="edit_codigo_postal" class="form-control" maxlength="10" readonly>
</div>

                <!-- Alerta informativa de múltiples puestos -->
                <div class="alert alert-info d-none mb-3" id="edit_alerta_multiples_puestos" style="background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border: none; color: white;">
                    <div class="d-flex align-items-center">
                        <i class="fa fa-info-circle me-2" style="font-size: 1.5rem;"></i>
                        <div>
                            <strong>Usuario con múltiples puestos</strong>
                            <p class="mb-0 small">Este usuario tiene asignados varios puestos. Gestiona, agrega o elimina puestos a continuación.</p>
                        </div>
                    </div>
                </div>

                <!-- Contenedor de puestos múltiples - MEJORADO CON GESTIÃ“N COMPLETA -->
                <div class="mb-3 d-none" id="edit_contenedor_multiples_puestos">
                    <label class="form-label fw-semibold">
                        <i class="fa fa-layer-group me-1"></i>Gestión de Puestos Asignados
                    </label>

                    <!-- Lista de puestos con opciones de gestión -->
                    <div id="edit_lista_puestos" class="border rounded p-3 mb-2" style="background: #f8f9fa; max-height: 400px; overflow-y: auto;">
                        <!-- Los puestos se agregarán dinámicamente aquí con este formato:
                        <div class="puesto-item" data-puesto-id="X">
                            <input type="radio" name="puesto_principal" />
                            <span>Departamento - Puesto</span>
                            <button onclick="eliminarPuesto(X)">Ã—</button>
                        </div>
                        -->
                    </div>

                    <!-- Botón para agregar nuevo puesto -->
                    <div class="d-flex gap-2 align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="mostrarAgregarPuesto()">
                            <i class="fa fa-plus-circle me-1"></i>Agregar Puesto
                        </button>
                        <small class="text-muted">
                            <i class="fa fa-info-circle me-1"></i>
                            El primer puesto es el principal
                        </small>
                    </div>

                    <!-- Panel para agregar nuevo puesto (oculto por defecto) -->
                    <div id="edit_panel_agregar_puesto" class="mt-3 p-3 border rounded d-none" style="background: #fff;">
                        <h6 class="mb-3">
                            <i class="fa fa-plus me-1"></i>Agregar Nuevo Puesto
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Departamento</label>
                                <select id="edit_nuevo_departamento" class="form-select form-select-sm js-select-buscador" onchange="cargarPuestosParaAgregar()">
                                    <option value="">Seleccione un departamento</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Puesto</label>
                                <select id="edit_nuevo_puesto" class="form-select form-select-sm js-select-buscador">
                                    <option value="">Seleccione un puesto</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-success" onclick="agregarNuevoPuesto()">
                                <i class="fa fa-check me-1"></i>Agregar
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelarAgregarPuesto()">
                                <i class="fa fa-times me-1"></i>Cancelar
                            </button>
                        </div>
                    </div>

                    <!-- Panel para editar puesto existente (oculto por defecto) -->
                    <div id="edit_panel_editar_puesto" class="mt-3 p-3 border rounded d-none" style="background: #fffbea; border-color: #fbbf24 !important;">
                        <h6 class="mb-3">
                            <i class="fa fa-edit me-1"></i>Editar Puesto
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Departamento</label>
                                <select id="edit_editar_departamento" class="form-select form-select-sm js-select-buscador" onchange="cargarPuestosParaEditar()">
                                    <option value="">Seleccione un departamento</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Puesto</label>
                                <select id="edit_editar_puesto" class="form-select form-select-sm js-select-buscador">
                                    <option value="">Seleccione un puesto</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-warning" onclick="guardarEdicionPuesto()">
                                <i class="fa fa-save me-1"></i>Guardar Cambios
                            </button>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="cancelarEditarPuesto()">
                                <i class="fa fa-times me-1"></i>Cancelar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Departamento <span id="edit_label_principal" class="badge bg-primary" style="display: none;">Principal</span></label>
                    <select id="edit_departamento_id" class="form-select js-select-buscador">
                        <option value="">Seleccione un departamento</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Puesto <span id="edit_label_puesto_principal" class="badge bg-primary" style="display: none;">Principal</span></label>
                    <select id="edit_id_puesto" class="form-select js-select-buscador">
                        <option value="">Seleccione un puesto</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="form-label">Jefe *</label>
                    <select id="edit_id_jefe" class="form-select js-select-buscador">
                        <option value="">Seleccione un jefe</option>
                    </select>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="edit_asignar_legion" onchange="toggleSelectLegionEdit()">
                        <label class="form-check-label" for="edit_asignar_legion">
                            Asignar legión
                        </label>
                    </div>
                </div>

                <div class="mb-2" id="edit_div_select_legion" style="display: none;">
                    <label class="form-label">Legión *</label>
                    <select id="edit_id_legion" class="form-select js-select-buscador">
                        <option value="">Seleccione una legión</option>
                        <option value="1">Sabueso</option>
                        <option value="2">Heraldo</option>
                        <option value="3">Centinela</option>
                        <option value="4">Senturiones</option>
                        <option value="5">Espartano</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Usuario *</label>
                    <input type="text" id="edit_usuario" class="form-control" readonly oninput="this.value = this.value.replace(/[^A-Za-zÁÃ‰ÍÃ“ÃšáéíóúÃ‘ñ\s]/g, '').toUpperCase()" style="text-transform: uppercase;">
                </div>

                <div class="mb-7" id="edit_row_contrasena">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="edit_contrasena" class="form-control">
                </div>

                <button type="button" id="edit_btn_guardar" class="btn btn-primary me-3" onclick="UpdateGestor()" > Guardar </button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas" > Cancelar </button>
            </form>
        </div>
    </div>

    <!-- =======================
      MODAL - GESTIÃ“N DE PERMISOS Y PUESTOS
 ======================== -->
    <div class="modal fade" id="modalEditPerfil" tabindex="-1" aria-labelledby="modalEditPerfilLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable modal-perfil-gestor-dialog">
            <div class="modal-content" style="border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
                <div class="modal-header" style="background:#0f2747;border-bottom:1px solid rgba(255,255,255,.2);padding:1rem 1.25rem;">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <h5 class="modal-title fw-bold mb-1 text-white" id="modalEditPerfilLabel">
                                <i class="fa fa-user-shield me-2 text-white"></i>Administrar puestos y módulos del usuario
                            </h5>
                            <p class="mb-0 small text-white-50" id="modalEditPerfil_subtitle">Nombre completo / Área / Empresa</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                </div>

                <div class="modal-body p-0 modal-perfil-gestor-body">
                    <!-- Tabs (fijas; el scroll es solo en .modal-perfil-gestor-tab-content) -->
                    <ul class="nav nav-tabs nav-tabs-custom px-4 pt-3 modal-perfil-gestor-tabs" role="tablist" style="border-bottom: 2px solid #e9ecef;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tabModulos-tab" data-bs-toggle="tab" data-bs-target="#tabModulos" type="button" role="tab">
                                <i class="fa fa-shield-alt me-2" style="color: #6c757d;"></i>Módulos del Sistema
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabPuestos-tab" data-bs-toggle="tab" data-bs-target="#tabPuestos" type="button" role="tab">
                                <i class="fa fa-briefcase me-2" style="color: #6c757d;"></i>Acceso a Puestos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabPermisosEspeciales-tab" data-bs-toggle="tab" data-bs-target="#tabPermisosEspeciales" type="button" role="tab">
                                <i class="fa fa-key me-2" style="color: #6c757d;"></i>Permisos especiales
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tabSesionRemota-tab" data-bs-toggle="tab" data-bs-target="#tabSesionRemota" type="button" role="tab" aria-controls="tabSesionRemota">
                                <i class="fa fa-sign-out me-2" style="color: #6c757d;"></i>Sesión remota
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content p-4 modal-perfil-gestor-tab-content">
                        <!-- TAB MÃ“DULOS -->
                        <div class="tab-pane fade show active" id="tabModulos" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                                <div>
                                    <h6 class="mb-1 fw-bold">Módulos del Sistema</h6>
                                    <small class="text-muted">Gestiona los accesos a los diferentes módulos</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" id="btn-modulos-expandir-todos" onclick="expandirTodosModulosSistema()">
                                    <i class="fa fa-expand me-1"></i>Expandir todos
                                </button>
                            </div>

                            <div id="modulos-container" style="overflow-y: visible;">
                                <div id="modal-edit-perfil-modulos-form"></div>
                            </div>
                        </div>

                        <!-- TAB PUESTOS -->
                        <div class="tab-pane fade" id="tabPuestos" role="tabpanel">
                            <input type="hidden" id="edit_perfil_id">
                            <div id="puestos-container" class="d-flex flex-column gap-3">
                                <div class="d-flex gap-3 flex-wrap flex-lg-nowrap">
                                    <aside class="border rounded bg-white p-2 flex-shrink-0" style="width:270px;max-width:100%;">
                                        <div class="small text-muted fw-semibold mb-2">Países</div>
                                        <div id="perfilPuestosPaisList" class="d-flex flex-column gap-1"></div>
                                    </aside>
                                    <section class="flex-grow-1 border rounded bg-white p-2">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <div class="input-group input-group-sm flex-grow-1" style="min-width:220px;">
                                                <span class="input-group-text"><i class="fa fa-search"></i></span>
                                                <input type="text" id="perfilPuestosBuscar" class="form-control" placeholder="Buscar puesto...">
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-puestos-expandir-todos" onclick="expandirTodosPuestos()">
                                                <i class="fa fa-expand me-1"></i>Expandir todo
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-puestos-contraer-todos" onclick="contraerTodosPuestos()">
                                                <i class="fa fa-compress me-1"></i>Contraer todo
                                            </button>
                                        </div>
                                        <div id="perfilAccesoDirectoPaisRow" class="d-flex align-items-center justify-content-between bg-light rounded px-3 py-2 mb-2 border">
                                            <div class="small fw-semibold text-secondary">Acceso directo al país</div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" id="perfilAccesoDirectoPaisSwitch">
                                            </div>
                                        </div>
                                        <div id="modal-edit-perfil-puestos-form" class="d-flex flex-column gap-2"></div>
                                    </section>
                                </div>
                            </div>
                        </div>

                        <!-- TAB PERMISOS ESPECIALES -->
                        <div class="tab-pane fade" id="tabPermisosEspeciales" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                                <div>
                                    <h6 class="mb-1 fw-bold">Permisos especiales</h6>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary flex-shrink-0" id="btn-permisos-esp-expandir-todos" onclick="expandirTodosPermisosEspeciales()">
                                    <i class="fa fa-expand me-1"></i>Desplegar todas las pestañas
                                </button>
                            </div>
                            <div id="permisos-especiales-container" class="modal-perfil-permisos-outer">
                                <div id="modal-edit-perfil-permisos-especiales-form"></div>
                            </div>

                            <!-- â”€â”€ Herramienta admin: Eliminar convenios del crédito (temporalmente oculta) â”€â”€
                            <div class="mt-4">
                                <div class="card border shadow-sm">
                                    <div class="card-header py-2 px-3 d-flex align-items-center gap-2" style="background:#f8f9fa;">
                                        <i class="fa fa-trash-alt text-danger"></i>
                                        <span class="fw-semibold small">Eliminar convenios del crédito</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <p class="text-muted small mb-3">Elimina <strong>todos los convenios y su amortización</strong> para un crédito. Ãšsalo cuando el crédito tenga convenios cancelados que bloquean la generación de uno nuevo.</p>
                                        <div class="d-flex gap-2 align-items-end flex-wrap">
                                            <div class="flex-grow-1" style="max-width:240px;">
                                                <label class="form-label small mb-1 fw-medium">ID Crédito</label>
                                                <input type="number" id="adminReactivarIdCredito" class="form-control form-control-sm" placeholder="Ej. 123456" min="1">
                                            </div>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="adminReactivarProductoConvenio()">
                                                <i class="fa fa-trash-alt me-1"></i>Eliminar convenios
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            -->
                        </div>

                        <!-- TAB SESIÃ“N REMOTA (force_logout en persona) -->
                        <div class="tab-pane fade" id="tabSesionRemota" role="tabpanel" aria-labelledby="tabSesionRemota-tab">
                            <div class="row justify-content-start">
                                <div class="col-12 col-md-10 col-lg-6 col-xl-5">
                                    <div class="card border-danger-subtle shadow-sm modal-tab-sesion-remota-card">
                                        <div class="card-body p-4">
                                            <div class="d-flex align-items-start gap-3 mb-3">
                                                <span class="badge bg-danger-subtle text-danger-emphasis">
                                                    <i class="fa fa-shield-alt me-1"></i>Seguridad de sesión
                                                </span>
                                            </div>
                                            <h6 class="fw-semibold mb-2">Forzar cierre de sesión</h6>
                                            <p class="text-muted small mb-3">La sesión activa de este usuario (incluido usted si abre su propio perfil) se cerrará en cuanto el sistema la valide. Después deberá iniciar sesión nuevamente.</p>
                                            <div class="alert alert-light border small mb-3" role="alert">
                                                <i class="fa fa-info-circle me-2 text-primary"></i>Use esta acción cuando necesite cerrar una sesión remota por seguridad.
                                            </div>
                                            <button type="button" class="btn btn-danger w-100" id="btnForzarLogoutUsuarioPerfil" onclick="forzarCierreSesionUsuarioPerfil()">
                                                <i class="fa fa-sign-out-alt me-2"></i>Forzar cierre ahora
                                            </button>
                                            <p class="small mb-0 mt-3" id="forceLogoutPerfilEstado"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between align-items-center border-top">
                    <div class="small text-muted">
                        <i class="fa fa-circle-check text-success me-1"></i>
                        Los cambios se guardan automáticamente al asignar o quitar.
                        <span class="d-none" id="perfilPuestosSeleccionadosTotal">0</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- =======================
      OFFCANVAS - EDITAR PERFIL PERMISOS (LEGACY - MANTENER POR COMPATIBILIDAD)
 ======================== -->
    <div class="offcanvas offcanvas-end" id="offcanvasEditPerfil">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-semibold">
                <i class="bi bi-person-gear me-1"></i> Editar Perfil
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-4">

            <div class="mb-3">
                <label class="form-label fw-semibold">Nombre *</label>
                <input type="text" id="edit_perfil_nombres" class="form-control" readonly>
            </div>

            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPerfil">
                        <i class="bi bi-person me-1"></i> Acceso a Puestos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabAccesos">
                        <i class="bi bi-shield-lock me-1"></i> Módulos
                    </button>
                </li>
            </ul>

            <form onsubmit="return false">
                <div class="tab-content">

                    <!-- PERFIL -->
                    <div class="tab-pane fade show active" id="tabPerfil">
                        <input type="hidden" id="edit_perfil_id">

                        <label class="form-label fw-semibold mb-2">
                            Puestos asignados
                        </label>

                        <div id="puestos-container" class="border rounded p-2">
                            <div id="puestos-form"></div>
                        </div>
                    </div>

                    <!-- ACCESOS -->
                    <div class="tab-pane fade" id="tabAccesos">

                        <label class="form-label fw-semibold mb-2">
                            Selecciona los accesos
                        </label>

                        <div id="modulos-container" class="border rounded p-2">
                            <div id="modulos-form"></div>
                        </div>

                    </div>

                </div>
            </form>
        </div>
    </div>


</div>

<!-- =========================
     JS
========================== -->

<script>
  /**
   * ==========================================
   * FUNCIÃ“N VALIDAR TELÃ‰FONO
   * ==========================================
   * Valida que el número no tenga patrones repetitivos
   */
  function validarTelefono(fieldId) {
    const input = document.getElementById(fieldId);
    const telefono = input.value;

    // Solo validar si tiene 10 dígitos
    if (telefono.length !== 10) {
      return;
    }

    // Verificar patrones repetitivos
    // Ejemplo: 3333333333, 1111111111
    const todosIguales = /^(\d)\1{9}$/.test(telefono);

    // Ejemplo: 1212121212, 4242424242
    const patron2Digitos = /^(\d{2})\1{4}$/.test(telefono);

    if (todosIguales || patron2Digitos) {
      Swal.fire({
        icon: 'error',
        title: 'Número de teléfono no válido',
        text: 'El número ingresado no es válido. Por favor, ingrese un número telefónico correcto.',
        confirmButtonText: 'Entendido'
      });
      input.value = '';
      input.focus();
    }
  }

  /**
   * ==========================================
   * ACTUALIZAR INDICADORES (KPIs)
   * ==========================================
   * Calcula y muestra los indicadores clave:
   * - Total de Departamentos
   * - Total de Puestos
   * - Lógica condicional según filtros:
   *   1. Sin filtros: Total de Empleados (global)
   *   2. Con departamento: Roles dinámicos + Total Empleados del departamento
   *   3. Con puesto: Roles dinámicos + Empleados en ese puesto
   */
  function actualizarIndicadores(datos) {
    if (!datos || datos.length === 0) {
      document.getElementById('kpi-departamentos').textContent = '0';
      document.getElementById('kpi-puestos').textContent = '0';
      ocultarIndicadorEmpleados();
      ocultarIndicadorTotalEmpleados();
      ocultarIndicadoresRoles();

      // Limpiar también el mini-stat de sin jefe
      if (typeof kpiUpdateValues === 'function') {
        kpiUpdateValues({ depSinJefe: 0 });
      }

      return;
    }

    // DEPARTAMENTOS: Contar únicos (excluyendo "Sin departamento")
    const departamentos = new Set();
    datos.forEach(persona => {
      if (persona.nombre_departamento && persona.nombre_departamento !== 'Sin departamento') {
        departamentos.add(persona.nombre_departamento);
      }
    });

    // PUESTOS: Contar únicos (excluyendo "Sin puesto")
    const puestos = new Set();
    datos.forEach(persona => {
      if (persona.nombre_puesto && persona.nombre_puesto !== 'Sin puesto') {
        puestos.add(persona.nombre_puesto);
      }
    });

    // PUESTOS COMPARTIDOS: puestos con más de 1 empleado asignado
    const puestoConteo = {};
    datos.forEach(persona => {
      const p = persona.nombre_puesto;
      if (p && p !== 'Sin puesto') puestoConteo[p] = (puestoConteo[p] || 0) + 1;
    });
    const puestosCompartidos = Object.values(puestoConteo).filter(c => c > 1).length;

    // EMPLEADOS SIN JEFE: Contar aquellos sin jefe asignado (comparación case-insensitive)
    const empleadosSinJefe = datos.filter(persona => {
      if (!persona.nombre_jefe) return true;
      const jefe = persona.nombre_jefe.trim();
      if (jefe === '') return true;
      return jefe.toLowerCase() === 'sin jefe';
    }).length;

    // INGRESOS DEL MES: fecha_ingreso del mes actual; fallback a fecha_registro si no existe
    const ahora = new Date();
    const mesActual  = ahora.getMonth();   // 0-based
    const anioActual = ahora.getFullYear();
    const ingresosEsteMes = datos.filter(persona => {
      // Prioridad 1: fecha_ingreso (la que el usuario llenó al registrar al empleado)
      const fechaPrimaria = persona.fecha_ingreso || null;
      // Prioridad 2: fecha_registro (cuándo fue dado de alta en el sistema)
      const fechaFallback = persona.fecha_registro || null;
      const fechaUsar = fechaPrimaria || fechaFallback;
      if (!fechaUsar) return false;
      const d = new Date(fechaUsar);
      return !isNaN(d) && d.getMonth() === mesActual && d.getFullYear() === anioActual;
    }).length;

    // BAJAS DEL MES: del array global de bajas (datosBajasGlobal)
    // Se usa la cantidad total del mes actual si está disponible
    const bajasEsteMes = (typeof datosBajasGlobal !== 'undefined' ? datosBajasGlobal : []).filter(baja => {
      const fechaCampo = baja.fecha_baja || baja.fecha_ingreso || baja.fecha_registro || null;
      if (!fechaCampo) return false;
      const d = new Date(fechaCampo);
      return d.getMonth() === mesActual && d.getFullYear() === anioActual;
    }).length;

    // Obtener filtros actuales
    const direccionSeleccionada = document.getElementById('UserDireccion')?.value || '';
    const areaSeleccionada = document.getElementById('UserArea')?.value || '';
    const departamentoSeleccionado = document.getElementById('UserRole').value;
    const puestoSeleccionado = document.getElementById('UserPlan').value;

    // ==========================================
    // LÃ“GICA CONDICIONAL SEGÃšN FILTROS
    // ==========================================

    // CASO 1: Sin filtros seleccionados
    if (!direccionSeleccionada && !areaSeleccionada && !departamentoSeleccionado && !puestoSeleccionado) {
      // Mostrar: Departamentos, Puestos, Total de Empleados (global)
      ocultarIndicadoresRoles();
      ocultarIndicadorEmpleados();
      mostrarIndicadorTotalEmpleados(datos.length, 'Total Empleados');
    }
    // CASO 2: Solo departamento seleccionado (sin puesto)
    else if ((direccionSeleccionada || areaSeleccionada || departamentoSeleccionado) && !puestoSeleccionado) {
      // Mostrar: Departamentos, Puestos, Roles Dinámicos, Total Empleados del Departamento
      actualizarIndicadoresRoles(datos, departamentoSeleccionado);
      ocultarIndicadorEmpleados();

      if (departamentoSeleccionado) {
        const empleadosDepartamento = datos.filter(p => p.nombre_departamento === departamentoSeleccionado).length;
        mostrarIndicadorTotalEmpleados(empleadosDepartamento, 'Empleados en ' + departamentoSeleccionado);
      } else {
        const etiquetaContexto = areaSeleccionada || direccionSeleccionada;
        mostrarIndicadorTotalEmpleados(datos.length, 'Empleados en ' + etiquetaContexto);
      }
    }
    // CASO 3: Puesto seleccionado (con o sin departamento)
    else if (puestoSeleccionado) {
      // Mostrar: Departamentos, Puestos, Empleados en Puesto
      // OCULTAR roles dinámicos para evitar redundancia
      ocultarIndicadoresRoles();
      ocultarIndicadorTotalEmpleados();

      const empleadosEnPuesto = datos.filter(persona =>
        persona.nombre_puesto === puestoSeleccionado
      ).length;
      mostrarIndicadorEmpleados(puestoSeleccionado, empleadosEnPuesto);
    }

    // Actualizar los números en el DOM con animación
    animarNumero('kpi-departamentos', departamentos.size);
    animarNumero('kpi-puestos', puestos.size);

    // INTEGRACIÃ“N CON SISTEMA KPI v3: Actualizar el nuevo panel con todos los datos
    if (typeof kpiUpdateValues === 'function') {
      // El total de empleados es simplemente la cantidad de datos que se pasaron (ya filtrados)
      const totalEmpleados = datos.length;

      // TOP DEP POR PUESTOS DISTINTOS
      const puestosPorDep = {};
      datos.forEach(function(p) {
        const dep = p.nombre_departamento, pst = p.nombre_puesto;
        if (dep && pst && pst !== 'Sin puesto') {
          if (!puestosPorDep[dep]) puestosPorDep[dep] = new Set();
          puestosPorDep[dep].add(pst);
        }
      });
      let topDepNombre = '—', topDepCnt = 0;
      Object.keys(puestosPorDep).forEach(function(dep) {
        if (puestosPorDep[dep].size > topDepCnt) { topDepCnt = puestosPorDep[dep].size; topDepNombre = dep; }
      });

      // TOP PUESTO POR CANTIDAD DE EMPLEADOS
      let topPuestoNombre = '—', topPuestoCnt = 0;
      Object.keys(puestoConteo).forEach(function(pst) {
        if (puestoConteo[pst] > topPuestoCnt) { topPuestoCnt = puestoConteo[pst]; topPuestoNombre = pst; }
      });

      // % INGRESOS DEL MES respecto al total de empleados
      const pctIngresos = totalEmpleados > 0 ? Math.round((ingresosEsteMes / totalEmpleados) * 100) : 0;

      const datosKPI = {
        dep: departamentos.size,
        puesto: puestos.size,
        total: totalEmpleados,
        depSinJefe: empleadosSinJefe,
        puestoCompartidos: puestosCompartidos,
        ingresosEsteMes: ingresosEsteMes,
        bajasEsteMes: bajasEsteMes,
        topDepNombre: topDepNombre,
        topPuestoNombre: topPuestoNombre,
        pctIngresos: pctIngresos,
        arcDep: Math.min(100, (departamentos.size / 20) * 100),
        arcPuesto: Math.min(100, (puestos.size / 50) * 100),
        arcTotal: Math.min(100, (totalEmpleados / 200) * 100)
      };
      kpiUpdateValues(datosKPI);
    }
  }

  /**
   * ==========================================
   * ACTUALIZAR INDICADORES DE ROLES
   * ==========================================
   * Analiza los puestos del departamento seleccionado
   * y muestra los 2 más relevantes como indicadores
   */
  function actualizarIndicadoresRoles(datos, departamento) {
    // Filtrar solo personas del departamento seleccionado
    const empleadosDepartamento = datos.filter(persona =>
      persona.nombre_departamento === departamento
    );

    if (empleadosDepartamento.length === 0) {
      ocultarIndicadoresRoles();
      return;
    }

    // Contar empleados por puesto en este departamento
    const puestosCounts = {};
    empleadosDepartamento.forEach(persona => {
      const puesto = persona.nombre_puesto;
      if (puesto && puesto !== 'Sin puesto') {
        puestosCounts[puesto] = (puestosCounts[puesto] || 0) + 1;
      }
    });

    // Ordenar puestos por cantidad (descendente) y obtener los 2 primeros
    const puestosOrdenados = Object.entries(puestosCounts)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 2);

    // Mostrar indicadores según lo encontrado
    if (puestosOrdenados.length >= 1) {
      mostrarIndicadorRol1(puestosOrdenados[0][0], puestosOrdenados[0][1]);
    } else {
      ocultarIndicadorRol1();
    }

    if (puestosOrdenados.length >= 2) {
      mostrarIndicadorRol2(puestosOrdenados[1][0], puestosOrdenados[1][1]);
    } else {
      ocultarIndicadorRol2();
    }
  }

  /**
   * ==========================================
   * MOSTRAR INDICADOR ROL 1
   * ==========================================
   */
  function mostrarIndicadorRol1(nombrePuesto, cantidad) {
    const container = document.getElementById('kpi-rol1-container');
    const label = document.getElementById('kpi-rol1-label');
    const separador = document.getElementById('separator-rol1');

    if (container && label) {
      container.style.display = '';

      // Añadir tooltip con nombre completo
      label.setAttribute('title', nombrePuesto);

      // Truncar nombre si es muy largo
      let labelText = nombrePuesto;
      if (labelText.length > 18) {
        labelText = nombrePuesto.substring(0, 15) + '...';
      }
      label.textContent = labelText;

      animarNumero('kpi-rol1-numero', cantidad);

      // INTEGRACIÃ“N CON SISTEMA KPI v3
      if (typeof kpiUpdateValues === 'function') {
        const datosKPI = {
          rol1: cantidad,
          rol1Label: labelText,
          arcRol1: Math.min(100, (cantidad / 50) * 100)
        };
        kpiUpdateValues(datosKPI);
      }

      if (separador) {
        separador.style.display = '';
      }
    }
  }

  /**
   * ==========================================
   * MOSTRAR INDICADOR ROL 2
   * ==========================================
   */
  function mostrarIndicadorRol2(nombrePuesto, cantidad) {
    const container = document.getElementById('kpi-rol2-container');
    const label = document.getElementById('kpi-rol2-label');
    const separador = document.getElementById('separator-rol2');

    if (container && label) {
      container.style.display = '';

      // Añadir tooltip con nombre completo
      label.setAttribute('title', nombrePuesto);

      // Truncar nombre si es muy largo
      let labelText = nombrePuesto;
      if (labelText.length > 18) {
        labelText = nombrePuesto.substring(0, 15) + '...';
      }
      label.textContent = labelText;

      animarNumero('kpi-rol2-numero', cantidad);

      // INTEGRACIÃ“N CON SISTEMA KPI v3
      if (typeof kpiUpdateValues === 'function') {
        const datosKPI = {
          rol2: cantidad,
          rol2Label: labelText,
          arcRol2: Math.min(100, (cantidad / 50) * 100)
        };
        kpiUpdateValues(datosKPI);
      }

      if (separador) {
        separador.style.display = '';
      }
    }
  }

  /**
   * ==========================================
   * OCULTAR INDICADOR ROL 1
   * ==========================================
   */
  function ocultarIndicadorRol1() {
    const container = document.getElementById('kpi-rol1-container');
    const separador = document.getElementById('separator-rol1');

    if (container) container.style.display = 'none';
    if (separador) separador.style.display = 'none';

    // INTEGRACIÃ“N CON SISTEMA KPI v3: Limpiar valores
    if (typeof kpiUpdateValues === 'function') {
      const datosKPI = {
        rol1: undefined,
        rol1Label: '',
        arcRol1: 0
      };
      kpiUpdateValues(datosKPI);
    }
  }

  /**
   * ==========================================
   * OCULTAR INDICADOR ROL 2
   * ==========================================
   */
  function ocultarIndicadorRol2() {
    const container = document.getElementById('kpi-rol2-container');
    const separador = document.getElementById('separator-rol2');

    if (container) container.style.display = 'none';
    if (separador) separador.style.display = 'none';

    // INTEGRACIÃ“N CON SISTEMA KPI v3: Limpiar valores
    if (typeof kpiUpdateValues === 'function') {
      const datosKPI = {
        rol2: undefined,
        rol2Label: '',
        arcRol2: 0
      };
      kpiUpdateValues(datosKPI);
    }
  }

  /**
   * ==========================================
   * OCULTAR AMBOS INDICADORES DE ROLES
   * ==========================================
   */
  function ocultarIndicadoresRoles() {
    ocultarIndicadorRol1();
    ocultarIndicadorRol2();
  }

  /**
   * ==========================================
   * MOSTRAR INDICADOR DE TOTAL EMPLEADOS
   * ==========================================
   */
  function mostrarIndicadorTotalEmpleados(cantidad, labelTexto) {
    const container = document.getElementById('kpi-total-empleados-container');
    const label = document.getElementById('kpi-total-empleados-label');
    const separador = document.getElementById('separator-empleados');

    if (container && label) {
      container.style.display = '';

      // Añadir tooltip con texto completo
      label.setAttribute('title', labelTexto);

      // Truncar si es muy largo
      let textoFinal = labelTexto;
      if (labelTexto.length > 22) {
        textoFinal = labelTexto.substring(0, 19) + '...';
      }
      label.textContent = textoFinal;

      animarNumero('kpi-total-empleados', cantidad);

      // INTEGRACIÃ“N CON SISTEMA KPI v3: Actualizar también el nuevo panel
      if (typeof kpiUpdateValues === 'function') {
        // Solo actualizar el total, los otros valores se mantienen
        const datosKPI = {
          total: cantidad,
          arcTotal: Math.min(100, (cantidad / 200) * 100)
        };
        kpiUpdateValues(datosKPI);
      }

      // Mostrar separador
      if (separador) {
        separador.style.display = '';
      }
    }
  }

  /**
   * ==========================================
   * OCULTAR INDICADOR DE TOTAL EMPLEADOS
   * ==========================================
   */
  function ocultarIndicadorTotalEmpleados() {
    const container = document.getElementById('kpi-total-empleados-container');
    const separador = document.getElementById('separator-empleados');

    if (container) container.style.display = 'none';
    if (separador) separador.style.display = 'none';

    // INTEGRACIÃ“N CON SISTEMA KPI v3: Limpiar valores
    if (typeof kpiUpdateValues === 'function') {
      const datosKPI = {
        total: 0,
        arcTotal: 0
      };
      kpiUpdateValues(datosKPI);
    }
  }

  /**
   * ==========================================
   * MOSTRAR INDICADOR DE EMPLEADOS
   * ==========================================
   */
  function mostrarIndicadorEmpleados(nombrePuesto, cantidad) {
    const container = document.getElementById('kpi-empleados-container');
    const label = document.getElementById('kpi-empleados-label');
    const separador = document.getElementById('separator-empleados-puesto');

    if (container) {
      container.style.display = '';

      // Añadir tooltip con nombre completo
      label.setAttribute('title', 'Empleados en ' + nombrePuesto);

      // Truncar nombre del puesto si es muy largo
      let labelText = 'Empleados en ' + nombrePuesto;
      if (labelText.length > 25) {
        labelText = 'Empleados: ' + nombrePuesto.substring(0, 15) + '...';
      }
      label.textContent = labelText;

      animarNumero('kpi-empleados-puesto', cantidad);

      // Mostrar separador en desktop
      if (separador) {
        separador.style.display = '';
      }
    }
  }

  /**
   * ==========================================
   * OCULTAR INDICADOR DE EMPLEADOS
   * ==========================================
   */
  function ocultarIndicadorEmpleados() {
    const container = document.getElementById('kpi-empleados-container');
    const separador = document.getElementById('separator-empleados-puesto');

    if (container) {
      container.style.display = 'none';
    }

    if (separador) {
      separador.style.display = 'none';
    }
  }

  /**
   * ==========================================
   * ANIMAR NÃšMEROS DE KPIs
   * ==========================================
   * Crea una animación suave al actualizar los números
   */
  function animarNumero(elementId, valorFinal) {
    const elemento = document.getElementById(elementId);
    if (!elemento) return;

    // Agregar clase de animación de pulso
    elemento.classList.add('updating');
    setTimeout(() => {
      elemento.classList.remove('updating');
    }, 500);

    const valorActual = parseInt(elemento.textContent) || 0;
    const diferencia = valorFinal - valorActual;

    // Si no hay cambio, solo actualizar sin animación
    if (diferencia === 0) {
      elemento.textContent = valorFinal;
      return;
    }

    const duracion = 500; // milisegundos
    const pasos = 20;
    const incremento = diferencia / pasos;
    const intervalo = duracion / pasos;

    let contador = 0;
    const timer = setInterval(() => {
      contador++;
      const nuevoValor = Math.round(valorActual + (incremento * contador));
      elemento.textContent = nuevoValor;

      if (contador >= pasos) {
        elemento.textContent = valorFinal;
        clearInterval(timer);
      }
    }, intervalo);
  }

  /**
   * ==========================================
   * TOOLTIPS ENRIQUECIDOS
   * ==========================================
   * Muestra información adicional al pasar el mouse
   */
  function mostrarTooltip(elemento, tipo) {
    const tooltip = document.getElementById('kpiTooltip');
    if (!tooltip) return;

    const rect = elemento.getBoundingClientRect();

    let contenido = generarContenidoTooltip(tipo);
    tooltip.innerHTML = contenido;
    tooltip.style.visibility = 'hidden';
    tooltip.classList.add('show');

    // Posicionar respecto al viewport (tooltip debe estar en document.body). Centrar arriba de la tarjeta.
    requestAnimationFrame(function() {
      const w = tooltip.offsetWidth;
      const h = tooltip.offsetHeight;
      const cardCenterX = rect.left + (rect.width / 2);
      let left = cardCenterX - (w / 2);
      const top = rect.top - h - 10;
      const padding = 8;
      left = Math.max(padding, Math.min(left, window.innerWidth - w - padding));
      tooltip.style.left = left + 'px';
      tooltip.style.top = Math.max(padding, top) + 'px';
      tooltip.style.visibility = '';
    });
  }

  function escapePlantillaGestoresHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char] || char;
    });
  }

  function cargarLibreriaQrPlantillaGestores() {
    if (window.QRCode && (typeof window.QRCode.toCanvas === 'function' || typeof window.QRCode.toDataURL === 'function')) {
      return Promise.resolve();
    }
    const cargarScript = src => new Promise((resolve, reject) => {
      const existente = document.querySelector(`script[data-plantilla-qrcode="${src}"]`);
      if (existente) {
        existente.addEventListener('load', resolve, { once: true });
        existente.addEventListener('error', reject, { once: true });
        return;
      }
      const script = document.createElement('script');
      script.src = src;
      script.async = true;
      script.dataset.plantillaQrcode = src;
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
    return cargarScript('/assets/vendor/libs/qrcode/qrcode.js')
      .catch(() => cargarScript('/public/assets/vendor/libs/qrcode/qrcode.js'));
  }

  function inyectarEstilosPlantillaGestoresRrhh() {
    if (document.getElementById('plantilla-gestores-modal-styles')) return;
    const style = document.createElement('style');
    style.id = 'plantilla-gestores-modal-styles';
    style.textContent = `
      #modalPlantillaGestoresRrhh .modal-content {
        border: 0;
        border-radius: 10px;
        box-shadow: 0 18px 55px rgba(15, 23, 42, .22);
      }
      #modalPlantillaGestoresRrhh .modal-dialog {
        max-width: min(720px, calc(100vw - 1rem));
      }
      #modalPlantillaGestoresRrhh .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: .9rem 1.25rem;
      }
      #modalPlantillaGestoresRrhh .modal-body {
        background: #fff;
        padding: .9rem 1.25rem;
      }
      #modalPlantillaGestoresRrhh .modal-footer {
        border-top: 1px solid #e5e7eb;
        background: #fff;
        padding: .9rem 1.25rem;
      }
      #modalPlantillaGestoresRrhh .plantilla-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 11px;
        overflow: hidden;
        box-shadow: none;
      }
      #modalPlantillaGestoresRrhh .plantilla-resumen {
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fafafa;
        color: #475569;
        padding: .56rem .75rem;
        font-size: .86rem;
      }
      #modalPlantillaGestoresRrhh .plantilla-toolbar {
        background: #fff;
        padding: .15rem 0 .55rem;
      }
      #modalPlantillaGestoresRrhh .plantilla-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: .95rem;
        align-items: start;
      }
      #modalPlantillaGestoresRrhh .plantilla-columna {
        display: contents;
      }
      #modalPlantillaGestoresRrhh .plantilla-card-header {
        min-height: 44px;
        padding: .72rem 1rem .58rem;
        background: #fff;
        border-bottom: 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
      }
      #modalPlantillaGestoresRrhh .plantilla-card-title {
        display: flex;
        align-items: center;
        gap: .55rem;
        color: #111827;
        font-weight: 800;
        font-size: .94rem;
      }
      #modalPlantillaGestoresRrhh .plantilla-card-title i {
        color: #142641;
        font-size: .9rem;
        width: 16px;
        text-align: center;
      }
      #modalPlantillaGestoresRrhh .plantilla-grupo-contador {
        color: #8b8b8b;
        font-weight: 700;
        margin-left: .2rem;
      }
      #modalPlantillaGestoresRrhh .plantilla-toggle-grupo {
        border: 0;
        background: transparent;
        color: #079669;
        font-size: .76rem;
        font-weight: 700;
        padding: .15rem .25rem;
        white-space: nowrap;
      }
      #modalPlantillaGestoresRrhh .plantilla-card-body {
        padding: .1rem 1rem 1rem;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 1.2rem;
        row-gap: .72rem;
      }
      #modalPlantillaGestoresRrhh .plantilla-item {
        display: grid;
        grid-template-columns: 18px minmax(0, 1fr);
        align-items: start;
        gap: .5rem;
        min-height: 34px;
        padding: 0;
        border: 1px solid transparent;
        border-radius: 0;
        background: #fff;
        margin: 0;
        transition: background-color .12s ease, border-color .12s ease;
      }
      #modalPlantillaGestoresRrhh .plantilla-item:hover {
        background: transparent;
        border-color: transparent;
      }
      #modalPlantillaGestoresRrhh .plantilla-item:last-child {
        border-bottom: 0;
      }
      #modalPlantillaGestoresRrhh .plantilla-item-icon {
        display: none;
      }
      #modalPlantillaGestoresRrhh .plantilla-item-title {
        color: #1f2937;
        font-weight: 800;
        line-height: 1.08;
        font-size: .83rem;
        display: block;
      }
      #modalPlantillaGestoresRrhh .plantilla-item-sub {
        color: #7a7a7a;
        font-size: .72rem;
        line-height: 1.08;
        margin-top: .08rem;
        display: block;
      }
      #modalPlantillaGestoresRrhh .plantilla-columna-check,
      #modalPlantillaGestoresRrhh #plantilla-marcar-todas {
        appearance: none;
        -webkit-appearance: none;
        width: 16px;
        height: 16px;
        border: 2px solid #aeb7c5;
        border-radius: 4px;
        background: #fff;
        display: inline-grid;
        place-content: center;
        cursor: pointer;
        margin: 1px 0 0;
      }
      #modalPlantillaGestoresRrhh .plantilla-columna-check:checked,
      #modalPlantillaGestoresRrhh #plantilla-marcar-todas:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
        box-shadow: none;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 16 16' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M3.5 8.2L6.6 11.2L12.8 4.8' stroke='white' stroke-width='2.1' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: center;
        background-size: 13px 13px;
      }
      #modalPlantillaGestoresRrhh .plantilla-columna-check:disabled {
        cursor: not-allowed;
        opacity: .45;
      }
      #modalPlantillaGestoresRrhh .plantilla-item.is-disabled {
        opacity: .68;
        background: transparent;
      }
      #modalPlantillaGestoresRrhh .plantilla-protegido {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: #fff4dc;
        color: #b7791f;
        font-weight: 800;
        font-size: .65rem;
        padding: .04rem .34rem;
        margin-left: .25rem;
        vertical-align: 1px;
      }
      #modalPlantillaGestoresRrhh .badge.bg-label-primary {
        background: #142641 !important;
        color: #fff !important;
        border: 1px solid #142641;
        border-radius: 999px;
        padding: .48rem .82rem;
        font-weight: 800;
      }
      @media (max-width: 991px) {
        #modalPlantillaGestoresRrhh .plantilla-card-body {
          grid-template-columns: 1fr;
        }
      }
    `;
    document.head.appendChild(style);
  }

  const columnasPlantillaGestoresRrhh = [
    { key: 'numero_empleado', label: 'No. empleado', grupo: 'Identidad', icon: 'fa-id-card', desc: 'Numero interno.', checked: true },
    { key: 'codigo_contpac', label: 'Codigo CONTPAQ', grupo: 'Identidad', icon: 'fa-barcode', desc: 'Codigo operativo.' },
    { key: 'nombre_completo', label: 'Nombre completo', grupo: 'Identidad', icon: 'fa-user', desc: 'Nombre en una columna.', checked: true },
    { key: 'nombres', label: 'Nombre(s)', grupo: 'Identidad', icon: 'fa-user', desc: 'Primer nombre.' },
    { key: 'segundo_nombre', label: 'Segundo nombre', grupo: 'Identidad', icon: 'fa-user-tag', desc: 'Segundo nombre.' },
    { key: 'apellidop', label: 'Apellido paterno', grupo: 'Identidad', icon: 'fa-user-tag', desc: 'Paterno.' },
    { key: 'apellidom', label: 'Apellido materno', grupo: 'Identidad', icon: 'fa-user-tag', desc: 'Materno.' },
    { key: 'usuario', label: 'Usuario', grupo: 'Identidad', icon: 'fa-user-circle', desc: 'Acceso.' },
    { key: 'correo', label: 'Correo', grupo: 'Contacto', icon: 'fa-envelope', desc: 'Correo principal.' },
    { key: 'telefonos', label: 'Telefonos', grupo: 'Contacto', icon: 'fa-phone', desc: 'Telefonos activos.' },
    { key: 'domicilio', label: 'Domicilio', grupo: 'Contacto', icon: 'fa-home', desc: 'Domicilio principal.' },
    { key: 'codigo_postal', label: 'Codigo postal', grupo: 'Contacto', icon: 'fa-map-marker-alt', desc: 'Codigo postal.' },
    { key: 'curp', label: 'CURP', grupo: 'Documentos', icon: 'fa-id-card', desc: 'Clave poblacional.' },
    { key: 'rfc', label: 'RFC', grupo: 'Documentos', icon: 'fa-file-invoice', desc: 'Registro fiscal.' },
    { key: 'nss', label: 'NSS', grupo: 'Documentos', icon: 'fa-shield-alt', desc: 'Seguro social.' },
    { key: 'fecha_nacimiento', label: 'Fecha nacimiento', grupo: 'Documentos', icon: 'fa-calendar', desc: 'Nacimiento.' },
    { key: 'sexo', label: 'Sexo', grupo: 'Documentos', icon: 'fa-venus-mars', desc: 'Sexo registrado.' },
    { key: 'fecha_ingreso', label: 'Fecha ingreso', grupo: 'Laboral', icon: 'fa-calendar-check', desc: 'Ingreso.' },
    { key: 'fecha_registro', label: 'Fecha registro', grupo: 'Laboral', icon: 'fa-calendar-plus', desc: 'Alta sistema.' },
    { key: 'registro_patronal', label: 'Registro patronal', grupo: 'Laboral', icon: 'fa-briefcase', desc: 'Registro asignado.' },
    { key: 'codigo_contpaq_rrhh', label: 'Codigo CONTPAQ RRHH', grupo: 'Laboral', icon: 'fa-barcode', desc: 'Codigo laboral.' },
    { key: 'fecha_contpaq', label: 'Fecha CONTPAQ', grupo: 'Laboral', icon: 'fa-calendar-day', desc: 'Fecha CONTPAQ.' },
    { key: 'fecha_imss_alta', label: 'Fecha IMSS alta', grupo: 'Laboral', icon: 'fa-calendar-check', desc: 'Alta IMSS.' },
    { key: 'direccion_organizacional', label: 'Direccion', grupo: 'Asignacion', icon: 'fa-sitemap', desc: 'Direccion org.' },
    { key: 'area_texto', label: 'Area', grupo: 'Asignacion', icon: 'fa-layer-group', desc: 'Area asignada.' },
    { key: 'nombre_departamento', label: 'Departamento', grupo: 'Asignacion', icon: 'fa-building', desc: 'Departamento.', checked: true },
    { key: 'nombre_puesto', label: 'Puesto', grupo: 'Asignacion', icon: 'fa-briefcase', desc: 'Puesto actual.', checked: true },
    { key: 'nombre_jefe', label: 'Jefe inmediato', grupo: 'Asignacion', icon: 'fa-user-tie', desc: 'Jefe directo.', checked: true },
    { key: 'ubicacion_laboral', label: 'Ubicacion laboral', grupo: 'Asignacion', icon: 'fa-map', desc: 'Ubicacion.' },
    { key: 'municipio_laboral', label: 'Municipio laboral', grupo: 'Asignacion', icon: 'fa-map-signs', desc: 'Municipio.' },
    { key: 'nombre_pais', label: 'Pais', grupo: 'Asignacion', icon: 'fa-flag', desc: 'Pais.' },
    { key: 'estatus', label: 'Estatus', grupo: 'Asignacion', icon: 'fa-check-circle', desc: 'Estatus actual.', checked: true },
    { key: 'salario_sensible', label: 'Salario sensible', grupo: 'Sensible', icon: 'fa-lock', desc: 'Protegido.', sensible: true }
  ];

  function renderColumnasPlantillaGestoresRrhh() {
    const grupos = {};
    columnasPlantillaGestoresRrhh.forEach(col => {
      if (!grupos[col.grupo]) grupos[col.grupo] = [];
      grupos[col.grupo].push(col);
    });
    const ordenGrupos = ['Identidad', 'Contacto', 'Documentos', 'Laboral', 'Asignacion', 'Sensible'];
    const renderGrupo = grupo => !grupos[grupo] ? '' : `
      <div class="plantilla-card">
        <div class="plantilla-card-header">
          <div class="plantilla-card-title">
            <i class="fa ${grupo === 'Sensible' ? 'fa-lock' : grupo === 'Asignacion' ? 'fa-sitemap' : grupo === 'Laboral' ? 'fa-briefcase' : grupo === 'Contacto' ? 'fa-envelope' : grupo === 'Documentos' ? 'fa-file-alt' : 'fa-user'}"></i>
            <span>${escapePlantillaGestoresHtml(grupo)} <span class="plantilla-grupo-contador" data-grupo="${escapePlantillaGestoresHtml(grupo)}">(0/${grupos[grupo].filter(col => !(col.sensible && !window.puedeVerSalarioSensibleRrhh)).length})</span></span>
          </div>
          <button type="button" class="plantilla-toggle-grupo" data-grupo="${escapePlantillaGestoresHtml(grupo)}">Marcar todo</button>
        </div>
        <div class="plantilla-card-body">
          ${grupos[grupo].map(col => {
            const disabled = col.sensible && !window.puedeVerSalarioSensibleRrhh;
            const title = disabled ? 'Requiere permiso especial de salario.' : '';
            return `
              <label class="plantilla-item ${disabled ? 'is-disabled' : ''}" title="${escapePlantillaGestoresHtml(title)}">
                <input type="checkbox" class="plantilla-columna-check" data-grupo="${escapePlantillaGestoresHtml(grupo)}" value="${escapePlantillaGestoresHtml(col.key)}" ${col.checked && !disabled ? 'checked' : ''} ${disabled ? 'disabled' : ''}>
                <span>
                  <span class="plantilla-item-title">${escapePlantillaGestoresHtml(col.label)}${col.sensible ? '<span class="plantilla-protegido">Protegido</span>' : ''}</span>
                  <span class="plantilla-item-sub">${escapePlantillaGestoresHtml(col.desc || 'Columna disponible para la plantilla.')}</span>
                </span>
              </label>
            `;
          }).join('')}
        </div>
      </div>
    `;
    return ordenGrupos.map(renderGrupo).join('');
  }

  async function pedirColumnasPlantillaGestoresRrhh(mensajeFiltros) {
    return new Promise(resolve => {
      inyectarEstilosPlantillaGestoresRrhh();
      const existente = document.getElementById('modalPlantillaGestoresRrhh');
      if (existente) existente.remove();

      const modal = document.createElement('div');
      modal.className = 'modal fade';
      modal.id = 'modalPlantillaGestoresRrhh';
      modal.tabIndex = -1;
      modal.setAttribute('aria-hidden', 'true');
      modal.innerHTML = `
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header align-items-start">
              <div class="d-flex align-items-center gap-3">
                <span style="width:40px;height:40px;border-radius:10px;background:#142641;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:1.05rem;border:1px solid #142641;">
                  <i class="fa fa-file-excel"></i>
                </span>
                <div>
                  <h5 class="modal-title fw-bold mb-0" style="color:#1f2937;">Descargar plantilla de gestores</h5>
                  <div class="text-muted small">Elige las columnas que tendra el Excel.</div>
                </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <div class="plantilla-resumen mb-2">
                ${mensajeFiltros}
              </div>
              <div class="plantilla-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <label class="d-flex align-items-center gap-2 fw-bold mb-0" style="color:#26334d;">
                  <input type="checkbox" id="plantilla-marcar-todas" class="form-check-input m-0">
                  Marcar todos
                </label>
                <span class="badge bg-label-primary" id="plantilla-columnas-contador">0 columnas</span>
              </div>
              <div id="plantilla-columnas-error" class="alert alert-danger py-2 px-3 d-none mb-3">
                Selecciona al menos una columna.
              </div>
              <div class="plantilla-grid">
                ${renderColumnasPlantillaGestoresRrhh()}
              </div>
            </div>
            <div class="modal-footer d-flex justify-content-between align-items-center">
              <div class="text-muted small" id="plantilla-columnas-total-pie">0 de 0 columnas seleccionadas</div>
              <div class="d-flex align-items-center gap-2">
              <button type="button" class="btn btn-label-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-success px-4" id="btnConfirmarPlantillaGestores">
                <i class="fa fa-download me-2"></i>Si, descargar
              </button>
              </div>
            </div>
          </div>
        </div>
      `;
      document.body.appendChild(modal);

      const instancia = window.bootstrap && bootstrap.Modal
        ? new bootstrap.Modal(modal, { backdrop: 'static', keyboard: true })
        : null;
      let resuelto = false;
      const marcarTodas = modal.querySelector('#plantilla-marcar-todas');
      const checks = Array.from(modal.querySelectorAll('.plantilla-columna-check:not(:disabled)'));
      const error = modal.querySelector('#plantilla-columnas-error');
      const contador = modal.querySelector('#plantilla-columnas-contador');
      const contadorPie = modal.querySelector('#plantilla-columnas-total-pie');
      const btnConfirmar = modal.querySelector('#btnConfirmarPlantillaGestores');
      const togglesGrupo = Array.from(modal.querySelectorAll('.plantilla-toggle-grupo'));
      const syncMaster = () => {
        const total = checks.length;
        const seleccionadas = checks.filter(ch => ch.checked).length;
        if (marcarTodas) {
          marcarTodas.checked = total > 0 && seleccionadas === total;
          marcarTodas.indeterminate = seleccionadas > 0 && seleccionadas < total;
        }
        if (contador) contador.textContent = `${seleccionadas} columna${seleccionadas === 1 ? '' : 's'}`;
        if (contadorPie) contadorPie.textContent = `${seleccionadas} de ${total} columna${total === 1 ? '' : 's'} seleccionada${seleccionadas === 1 ? '' : 's'}`;
        togglesGrupo.forEach(btn => {
          const grupo = btn.dataset.grupo || '';
          const checksGrupo = checks.filter(ch => (ch.dataset.grupo || '') === grupo);
          const seleccionadasGrupo = checksGrupo.filter(ch => ch.checked).length;
          const totalGrupo = checksGrupo.length;
          const contadorGrupo = Array.from(modal.querySelectorAll('.plantilla-grupo-contador')).find(el => (el.dataset.grupo || '') === grupo);
          if (contadorGrupo) contadorGrupo.textContent = `(${seleccionadasGrupo}/${totalGrupo})`;
          btn.textContent = totalGrupo > 0 && seleccionadasGrupo === totalGrupo ? 'Desmarcar todo' : 'Marcar todo';
        });
        if (error && seleccionadas > 0) error.classList.add('d-none');
      };

      if (marcarTodas) {
        marcarTodas.addEventListener('change', () => {
          checks.forEach(ch => { ch.checked = marcarTodas.checked; });
          syncMaster();
        });
      }
      checks.forEach(ch => ch.addEventListener('change', syncMaster));
      togglesGrupo.forEach(btn => {
        btn.addEventListener('click', () => {
          const grupo = btn.dataset.grupo || '';
          const checksGrupo = checks.filter(ch => (ch.dataset.grupo || '') === grupo);
          const todosMarcados = checksGrupo.length > 0 && checksGrupo.every(ch => ch.checked);
          checksGrupo.forEach(ch => { ch.checked = !todosMarcados; });
          syncMaster();
        });
      });
      if (btnConfirmar) {
        btnConfirmar.addEventListener('click', () => {
          const seleccionadas = checks.filter(ch => ch.checked).map(ch => ch.value);
          if (!seleccionadas.length) {
            if (error) error.classList.remove('d-none');
            return;
          }
          resuelto = true;
          resolve(seleccionadas);
          if (instancia) instancia.hide();
          else modal.remove();
        });
      }
      modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
        if (!resuelto) resolve(null);
      }, { once: true });
      syncMaster();

      if (instancia) instancia.show();
      else {
        modal.classList.add('show');
        modal.style.display = 'block';
      }
    });
  }

  async function pedirTotpPlantillaGestoresRrhh(datosTotp) {
    const setup = !!datosTotp?.setup;
    const secret = datosTotp?.secret || '';
    const otpauthUrl = datosTotp?.otpauth_url || '';
    const cuenta = datosTotp?.cuenta || '';
    const html = setup
      ? `<div class="text-start">
          <p class="mb-2">Escanea este QR en Google Authenticator y captura el codigo generado.</p>
          <div class="d-flex justify-content-center my-2" id="rrhh-plantilla-totp-qr-wrap"><canvas id="rrhh-plantilla-totp-qr" width="210" height="210"></canvas></div>
          <div class="small text-muted">Cuenta: ${escapePlantillaGestoresHtml(cuenta)}${secret ? '<br>Clave: <code>' + escapePlantillaGestoresHtml(secret) + '</code>' : ''}</div>
          <div class="small text-danger d-none mt-2" id="rrhh-plantilla-totp-qr-error">No se pudo pintar el QR. Usa la clave manual.</div>
        </div>`
      : '<div class="text-start"><p class="mb-0">Escribe el codigo de 6 digitos de Google Authenticator para descargar la plantilla.</p></div>';

    const result = await Swal.fire({
      title: 'Segundo paso requerido',
      html,
      input: 'text',
      inputPlaceholder: 'Codigo de 6 digitos',
      inputAttributes: { maxlength: 6, inputmode: 'numeric', autocomplete: 'one-time-code' },
      showCancelButton: true,
      confirmButtonText: 'Verificar',
      cancelButtonText: 'Cancelar',
      didOpen: async () => {
        setTimeout(() => {
          const input = Swal.getInput ? Swal.getInput() : document.querySelector('.swal2-input');
          if (input) {
            input.disabled = false;
            input.readOnly = false;
            input.focus();
            input.select?.();
          }
        }, 80);
        if (setup && otpauthUrl) {
          try {
            await cargarLibreriaQrPlantillaGestores();
            const canvas = document.getElementById('rrhh-plantilla-totp-qr');
            if (canvas && window.QRCode && typeof window.QRCode.toCanvas === 'function') {
              canvas.style.pointerEvents = 'none';
              await window.QRCode.toCanvas(canvas, otpauthUrl, { width: 210, margin: 2, errorCorrectionLevel: 'M' });
            } else if (window.QRCode && typeof window.QRCode.toDataURL === 'function') {
              const dataUrl = await window.QRCode.toDataURL(otpauthUrl, { width: 210, margin: 2, errorCorrectionLevel: 'M' });
              const wrap = document.getElementById('rrhh-plantilla-totp-qr-wrap');
              if (wrap) {
                wrap.innerHTML = `<img src="${dataUrl}" width="210" height="210" alt="QR Google Authenticator">`;
              }
            } else {
              throw new Error('QRCode no expone toCanvas ni toDataURL.');
            }
          } catch (error) {
            console.warn('No se pudo pintar QR de plantilla gestores:', error);
            document.getElementById('rrhh-plantilla-totp-qr-error')?.classList.remove('d-none');
          }
        }
      },
      preConfirm: value => {
        const codigo = String(value || '').replace(/\D+/g, '');
        if (codigo.length !== 6) {
          Swal.showValidationMessage('Captura los 6 digitos de Google Authenticator.');
          return false;
        }
        return codigo;
      }
    });
    return result.isConfirmed ? result.value : '';
  }

  async function postPlantillaGestoresRrhh(endpoint, payload, datosTotp = null) {
    const body = Object.assign({}, payload);
    if (datosTotp && datosTotp.codigo) body.totp_code = datosTotp.codigo;
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(body)
    });
    const data = await response.json();
    if (data.success && data.datos && data.datos.requiere_totp) {
      const codigo = await pedirTotpPlantillaGestoresRrhh(data.datos);
      if (!codigo) return null;
      return postPlantillaGestoresRrhh(endpoint, payload, { codigo });
    }
    return data;
  }

  async function descargarPlantillaGestores() {
    const direccion = document.getElementById('UserDireccion')?.value || '';
    const area = document.getElementById('UserArea')?.value || '';
    const departamento = document.getElementById('UserRole')?.value || '';
    const puesto = document.getElementById('UserPlan')?.value || '';
    const estatus = document.getElementById('FilterTransaction')?.value || '';
    let detallesFiltros = [];
    if (direccion) detallesFiltros.push(`Direccion: <strong>${escapePlantillaGestoresHtml(direccion)}</strong>`);
    if (area) detallesFiltros.push(`Area: <strong>${escapePlantillaGestoresHtml(area)}</strong>`);
    if (departamento) detallesFiltros.push(`Departamento: <strong>${escapePlantillaGestoresHtml(departamento)}</strong>`);
    if (puesto) detallesFiltros.push(`Puesto: <strong>${escapePlantillaGestoresHtml(puesto)}</strong>`);
    if (estatus) detallesFiltros.push(`Estatus: <strong>${escapePlantillaGestoresHtml(estatus)}</strong>`);
    const mensajeFiltros = detallesFiltros.length
      ? 'Se descargara un archivo Excel filtrado por:<br>' + detallesFiltros.join('<br>')
      : 'Se descargara un archivo Excel con <strong>TODOS los gestores</strong> del sistema.';

    const columnas = await pedirColumnasPlantillaGestoresRrhh(mensajeFiltros);
    if (!columnas) return;

    try {
      const auth = await postPlantillaGestoresRrhh('/CapHum/autorizarDescargaPlantillaGestoresRrhh', { columnas });
      if (!auth) return;
      if (!auth.success) {
        Swal.fire({ icon: 'error', title: 'No se pudo autorizar', text: auth.mensaje || 'Revisa tus permisos.' });
        return;
      }
      const downloadToken = auth.datos && auth.datos.download_token ? String(auth.datos.download_token) : '';
      if (!downloadToken) {
        Swal.fire({ icon: 'error', title: 'No se pudo autorizar', text: 'No se genero el token seguro de descarga.' });
        return;
      }

      Swal.fire({
        title: 'Generando archivo Excel...',
        html: '<p style="margin-top: 1rem;">Por favor espera...</p>',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
      });

      const form = document.createElement('form');
      form.method = 'POST';
      form.action = '/Reporteria/descargarPlantillaGestores';
      form.style.display = 'none';

      const agregarInput = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
      };
      if (departamento) agregarInput('departamento', departamento);
      if (puesto) agregarInput('puesto', puesto);
      if (estatus) agregarInput('estatus', estatus);
      agregarInput('plantilla_token', downloadToken);
      columnas.forEach(columna => agregarInput('columnas[]', columna));

      document.body.appendChild(form);
      form.submit();

      setTimeout(() => {
        Swal.close();
        Swal.fire({
          icon: 'success',
          title: 'Descarga iniciada',
          text: 'El archivo se esta descargando.',
          timer: 3000,
          showConfirmButton: false
        });
        form.remove();
      }, 3000);
    } catch (error) {
      Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo iniciar la descarga.' });
    }
  }

  function ocultarTooltip() {
    const tooltip = document.getElementById('kpiTooltip');
    if (tooltip) {
      tooltip.classList.remove('show');
      tooltip.style.visibility = '';
    }
  }

  function generarContenidoTooltip(tipo) {
    const datos = usuariosData || [];

    switch(tipo) {
      case 'departamentos':
        const depts = [...new Set(datos.filter(u => u.nombre_departamento && u.nombre_departamento !== 'Sin departamento').map(u => u.nombre_departamento))];
        const topDept = obtenerTopDepartamento(datos);
        return `
          <div class="kpi-tooltip-title">ðŸ“Š Departamentos</div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Total:</span>
            <span class="kpi-tooltip-value">${depts.length}</span>
          </div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Mayor:</span>
            <span class="kpi-tooltip-value">${topDept.nombre}</span>
          </div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Empleados:</span>
            <span class="kpi-tooltip-value">${topDept.cantidad}</span>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.7rem; color: #94a3b8; text-align: center;">Click para ver desglose</div>
        `;

      case 'puestos':
        const puestos = [...new Set(datos.filter(u => u.nombre_puesto && u.nombre_puesto !== 'Sin puesto').map(u => u.nombre_puesto))];
        const topPuesto = obtenerTopPuesto(datos);
        return `
          <div class="kpi-tooltip-title">ðŸ’¼ Puestos</div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Total:</span>
            <span class="kpi-tooltip-value">${puestos.length}</span>
          </div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Más común:</span>
            <span class="kpi-tooltip-value">${topPuesto.nombre}</span>
          </div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Personas:</span>
            <span class="kpi-tooltip-value">${topPuesto.cantidad}</span>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.7rem; color: #94a3b8; text-align: center;">Click para ver desglose</div>
        `;

      case 'total':
        const departamentoActual = document.getElementById('UserRole')?.value || '';
        return `
          <div class="kpi-tooltip-title">ðŸ‘¥ Total Empleados</div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Contexto:</span>
            <span class="kpi-tooltip-value">${departamentoActual ? 'Departamento' : 'Global'}</span>
          </div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Activos:</span>
            <span class="kpi-tooltip-value">${datos.filter(u => u.estatus === 'Activo').length}</span>
          </div>
          <div class="kpi-tooltip-item">
            <span class="kpi-tooltip-label">Inactivos:</span>
            <span class="kpi-tooltip-value">${datos.filter(u => u.estatus !== 'Activo').length}</span>
          </div>
          <div style="margin-top: 0.5rem; font-size: 0.7rem; color: #94a3b8; text-align: center;">Click para ver listado</div>
        `;

      default:
        return `
          <div class="kpi-tooltip-title">ðŸ“ˆ Estadísticas</div>
          <div style="margin-top: 0.5rem; font-size: 0.7rem; color: #94a3b8; text-align: center;">Click para más detalles</div>
        `;
    }
  }

  function obtenerTopDepartamento(datos) {
    const conteo = {};
    datos.forEach(u => {
      if (u.nombre_departamento && u.nombre_departamento !== 'Sin departamento') {
        conteo[u.nombre_departamento] = (conteo[u.nombre_departamento] || 0) + 1;
      }
    });
    const top = Object.entries(conteo).sort((a, b) => b[1] - a[1])[0];
    return top ? { nombre: top[0], cantidad: top[1] } : { nombre: 'N/A', cantidad: 0 };
  }

  function obtenerTopPuesto(datos) {
    const conteo = {};
    datos.forEach(u => {
      if (u.nombre_puesto && u.nombre_puesto !== 'Sin puesto') {
        conteo[u.nombre_puesto] = (conteo[u.nombre_puesto] || 0) + 1;
      }
    });
    const top = Object.entries(conteo).sort((a, b) => b[1] - a[1])[0];
    return top ? { nombre: top[0], cantidad: top[1] } : { nombre: 'N/A', cantidad: 0 };
  }

  /**
   * ==========================================
   * MODAL DE DESGLOSE DETALLADO
   * ==========================================
   * Muestra información completa al hacer click
   */
  function abrirModalDesglose(tipo) {
    const modal = new bootstrap.Modal(document.getElementById('modalKpiDesglose'));
    const modalTitle = document.getElementById('modalKpiTitle');
    const modalContent = document.getElementById('modalKpiContent');

    // Configurar íconos según tipo
    const iconos = {
      'departamentos': 'bx-buildings',
      'puestos': 'bx-briefcase',
      'rol1': 'bx-user-circle',
      'rol2': 'bx-user-check',
      'total': 'bx-group',
      'puesto-especifico': 'bx-user-pin'
    };

    const colores = {
      'departamentos': 'indigo',
      'puestos': 'emerald',
      'rol1': 'purple',
      'rol2': 'purple',
      'total': 'amber',
      'puesto-especifico': 'red'
    };

    const icono = iconos[tipo] || 'bx-chart';
    const color = colores[tipo] || 'indigo';

    // Generar contenido según tipo
    const contenido = generarContenidoModal(tipo, color);
    modalTitle.innerHTML = `<i class="bx ${icono} me-2"></i>${contenido.titulo}`;
    modalContent.innerHTML = contenido.html;

    modal.show();
  }

  function generarContenidoModal(tipo, color) {
    const datos = usuariosData || [];

    switch(tipo) {
      case 'departamentos':
        return generarModalDepartamentos(datos);
      case 'puestos':
        return generarModalPuestos(datos);
      case 'total':
        return generarModalTotalEmpleados(datos);
      case 'rol1':
      case 'rol2':
        return generarModalRol(tipo, datos);
      case 'puesto-especifico':
        return generarModalPuestoEspecifico(datos);
      default:
        return { titulo: 'Desglose', html: '<p>No hay datos disponibles</p>' };
    }
  }

  function generarModalDepartamentos(datos) {
    // Puestos distintos por departamento
    const depPuestosMap = {};
    const depEmpleadosMap = {};
    datos.forEach(u => {
      const dept = u.nombre_departamento;
      const puesto = u.nombre_puesto;
      if (dept && dept !== 'Sin departamento') {
        if (!depPuestosMap[dept])   depPuestosMap[dept]   = new Set();
        if (!depEmpleadosMap[dept]) depEmpleadosMap[dept] = 0;
        depEmpleadosMap[dept]++;
        if (puesto && puesto !== 'Sin puesto') depPuestosMap[dept].add(puesto);
      }
    });

    // Total de puestos distintos en toda la organización
    const allPuestos = new Set();
    datos.forEach(u => { if (u.nombre_puesto && u.nombre_puesto !== 'Sin puesto') allPuestos.add(u.nombre_puesto); });
    const totalPuestosOrg = allPuestos.size;

    const departamentos = Object.keys(depPuestosMap)
      .map(dept => ({
        dept,
        puestos:   depPuestosMap[dept].size,
        empleados: depEmpleadosMap[dept] || 0
      }))
      .sort((a, b) => b.puestos - a.puestos);

    const totalDeps      = departamentos.length;
    const totalEmpleados = datos.filter(u => u.nombre_departamento && u.nombre_departamento !== 'Sin departamento').length;
    const maxPuestos     = departamentos.length ? departamentos[0].puestos : 1;

    let html = `
      <div class="row mb-3">
        <div class="col-md-4">
          <div class="stat-card" data-color="indigo">
            <div class="stat-label"><i class="bx bx-buildings me-1"></i>Departamentos</div>
            <div class="stat-value">${totalDeps}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" data-color="indigo">
            <div class="stat-label"><i class="bx bx-briefcase me-1"></i>Puestos únicos</div>
            <div class="stat-value">${totalPuestosOrg}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" data-color="indigo">
            <div class="stat-label"><i class="bx bx-group me-1"></i>Total Empleados</div>
            <div class="stat-value">${totalEmpleados}</div>
          </div>
        </div>
      </div>

      <h6 class="mb-3 fw-bold"><i class="bx bx-bar-chart me-2"></i>Puestos por Departamento</h6>
      <div class="table-responsive">
        <table class="table table-hover table-sm align-middle">
          <thead class="table-header-indigo">
            <tr>
              <th class="text-center" style="width:2rem">#</th>
              <th>Departamento</th>
              <th class="text-center" style="width:6rem">Puestos</th>
              <th class="text-center" style="width:5rem">% del total</th>
              <th style="min-width:120px">Ocupación</th>
            </tr>
          </thead>
          <tbody>
    `;

    departamentos.forEach((item, index) => {
      const pct      = totalPuestosOrg > 0 ? ((item.puestos / totalPuestosOrg) * 100).toFixed(1) : '0.0';
      const barWidth = maxPuestos  > 0 ? ((item.puestos / maxPuestos) * 100).toFixed(1)  : '0';
      const badgeCol = index === 0 ? '#4F46E5' : '#6366F1';
      html += `
        <tr>
          <td class="text-center text-muted" style="font-size:.75rem">${index + 1}</td>
          <td>
            <strong>${item.dept}</strong>
            <div style="font-size:.68rem;color:#94a3b8;margin-top:1px">${item.empleados} empleado${item.empleados !== 1?'s':''}</div>
          </td>
          <td class="text-center">
            <span class="badge badge-count" style="background:linear-gradient(135deg,${badgeCol},#818CF8)">${item.puestos}</span>
          </td>
          <td class="text-center fw-bold">${pct}%</td>
          <td>
            <div class="progress" style="height:8px;border-radius:4px;background:#e2e8f0">
              <div class="progress-bar progress-bar-kpi" role="progressbar"
                   style="width:${barWidth}%;background:linear-gradient(90deg,#4F46E5,#818CF8);border-radius:4px;"
                   aria-valuenow="${barWidth}" aria-valuemin="0" aria-valuemax="100">
              </div>
            </div>
          </td>
        </tr>
      `;
    });

    html += `</tbody></table></div>`;

    return { titulo: 'Departamentos — Puestos por área', html };
  }

  function generarModalPuestos(datos) {
    const conteo = {};
    datos.forEach(u => {
      if (u.nombre_puesto && u.nombre_puesto !== 'Sin puesto') {
        conteo[u.nombre_puesto] = (conteo[u.nombre_puesto] || 0) + 1;
      }
    });

    const puestos = Object.entries(conteo)
      .sort((a, b) => b[1] - a[1])
      .map(([puesto, cantidad]) => ({ puesto, cantidad }));

    const total = puestos.length;
    const totalEmpleados = datos.length;

    let html = `
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="stat-card" data-color="emerald">
            <div class="stat-label"><i class="bx bx-briefcase me-1"></i>Total Puestos</div>
            <div class="stat-value">${total}</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="stat-card" data-color="emerald">
            <div class="stat-label"><i class="bx bx-group me-1"></i>Total Empleados</div>
            <div class="stat-value">${totalEmpleados}</div>
          </div>
        </div>
      </div>

      <h6 class="mb-3 fw-bold"><i class="bx bx-bar-chart me-2"></i>Distribución por Puesto</h6>
      <div class="table-responsive">
        <table class="table table-hover table-sm">
          <thead class="table-header-emerald">
            <tr>
              <th>#</th>
              <th>Puesto</th>
              <th class="text-center">Personas</th>
              <th class="text-center">% del Total</th>
              <th>Barra</th>
            </tr>
          </thead>
          <tbody>
    `;

    puestos.forEach((item, index) => {
      const porcentaje = ((item.cantidad / totalEmpleados) * 100).toFixed(1);
      html += `
        <tr>
          <td>${index + 1}</td>
          <td><strong>${item.puesto}</strong></td>
          <td class="text-center"><span class="badge badge-count" style="background: linear-gradient(135deg, #10B981, #34D399);">${item.cantidad}</span></td>
          <td class="text-center"><strong>${porcentaje}%</strong></td>
          <td>
            <div class="progress" style="height: 6px;">
              <div class="progress-bar progress-bar-kpi" role="progressbar"
                   style="width: ${porcentaje}%; background: linear-gradient(90deg, #10B981, #34D399);"></div>
            </div>
          </td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    return { titulo: 'Puestos - Análisis Completo', html };
  }

  function generarModalTotalEmpleados(datos) {
    const activos = datos.filter(u => u.estatus === 'Activo').length;
    const inactivos = datos.length - activos;
    const porcentajeActivos = ((activos / datos.length) * 100).toFixed(1);

    let html = `
      <div class="row mb-3">
        <div class="col-md-4">
          <div class="stat-card" data-color="amber">
            <div class="stat-label"><i class="bx bx-user me-1"></i>Total Empleados</div>
            <div class="stat-value">${datos.length}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" data-color="emerald">
            <div class="stat-label"><i class="bx bx-check-circle me-1"></i>Activos</div>
            <div class="stat-value">${activos}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" data-color="red">
            <div class="stat-label"><i class="bx bx-x-circle me-1"></i>Inactivos</div>
            <div class="stat-value">${inactivos}</div>
          </div>
        </div>
      </div>

      <h6 class="mb-3 fw-bold"><i class="bx bx-list-ul me-2"></i>Listado de Empleados</h6>
      <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
        <table class="table table-sm table-hover">
          <thead class="table-header-amber" style="position: sticky; top: 0; z-index: 10;">
            <tr>
              <th>Nombre</th>
              <th>Departamento</th>
              <th>Puesto</th>
              <th class="text-center">Estatus</th>
            </tr>
          </thead>
          <tbody>
    `;

    datos.forEach(u => {
      const badgeClass = u.estatus === 'Activo' ? 'bg-success' : 'bg-secondary';
      const nombreCompleto = [u.nombres, u.segundo_nombre, u.apellidop, u.apellidom].filter(x => x).join(' ') || 'N/A';
      html += `
        <tr>
          <td><strong>${nombreCompleto}</strong></td>
          <td>${u.nombre_departamento || 'N/A'}</td>
          <td>${u.nombre_puesto || 'N/A'}</td>
          <td class="text-center"><span class="badge ${badgeClass}">${u.estatus || 'N/A'}</span></td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    return { titulo: 'Total de Empleados', html };
  }

  function generarModalRol(tipo, datos) {
    const label = document.getElementById(`kpi-${tipo}-label`).textContent;
    const empleadosRol = datos.filter(u => u.nombre_puesto === label);

    let html = `
      <div class="row mb-3">
        <div class="col-md-12">
          <div class="stat-card" data-color="purple">
            <div class="stat-label"><i class="bx bx-user-circle me-1"></i>Personas en ${label}</div>
            <div class="stat-value">${empleadosRol.length}</div>
          </div>
        </div>
      </div>

      <h6 class="mb-3 fw-bold"><i class="bx bx-list-ul me-2"></i>Listado de ${label}</h6>
      <div class="table-responsive">
        <table class="table table-hover table-sm">
          <thead class="table-header-purple">
            <tr>
              <th>Nombre</th>
              <th>Departamento</th>
              <th class="text-center">Estatus</th>
            </tr>
          </thead>
          <tbody>
    `;

    empleadosRol.forEach(u => {
      const badgeClass = u.estatus === 'Activo' ? 'bg-success' : 'bg-secondary';
      const nombreCompleto = [u.nombres, u.segundo_nombre, u.apellidop, u.apellidom].filter(x => x).join(' ') || 'N/A';
      html += `
        <tr>
          <td><strong>${nombreCompleto}</strong></td>
          <td>${u.nombre_departamento || 'N/A'}</td>
          <td class="text-center"><span class="badge ${badgeClass}">${u.estatus || 'N/A'}</span></td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    return { titulo: `${label} - Desglose`, html };
  }

  function generarModalPuestoEspecifico(datos) {
    const puestoSeleccionado = document.getElementById('UserPlan')?.value || '';
    const empleadosPuesto = datos.filter(u => u.nombre_puesto === puestoSeleccionado);

    let html = `
      <div class="row mb-3">
        <div class="col-md-12">
          <div class="stat-card" data-color="red">
            <div class="stat-label"><i class="bx bx-user-pin me-1"></i>Empleados en ${puestoSeleccionado}</div>
            <div class="stat-value">${empleadosPuesto.length}</div>
          </div>
        </div>
      </div>

      <h6 class="mb-3 fw-bold"><i class="bx bx-list-ul me-2"></i>Listado Completo</h6>
      <div class="table-responsive">
        <table class="table table-hover table-sm">
          <thead class="table-header-red">
            <tr>
              <th>Nombre</th>
              <th>Departamento</th>
              <th class="text-center">Estatus</th>
            </tr>
          </thead>
          <tbody>
    `;

    empleadosPuesto.forEach(u => {
      const badgeClass = u.estatus === 'Activo' ? 'bg-success' : 'bg-secondary';
      const nombreCompleto = [u.nombres, u.segundo_nombre, u.apellidop, u.apellidom].filter(x => x).join(' ') || 'N/A';
      html += `
        <tr>
          <td><strong>${nombreCompleto}</strong></td>
          <td>${u.nombre_departamento || 'N/A'}</td>
          <td class="text-center"><span class="badge ${badgeClass}">${u.estatus || 'N/A'}</span></td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    return { titulo: `${puestoSeleccionado} - Empleados`, html };
  }

  /**
   * ==========================================
   * EVENTOS PARA TOOLTIPS Y MODALES
   * ==========================================
   */
  document.addEventListener('DOMContentLoaded', function() {
    // Mover el tooltip a body para que position:fixed sea siempre respecto al viewport
    const tooltipEl = document.getElementById('kpiTooltip');
    if (tooltipEl && tooltipEl.parentNode !== document.body) {
      document.body.appendChild(tooltipEl);
    }

    // Agregar eventos a todas las tarjetas KPI
    document.querySelectorAll('.kpi-card').forEach(card => {
      const tipo = card.getAttribute('data-tipo');

      // Evento hover para tooltip
      card.addEventListener('mouseenter', function() {
        mostrarTooltip(this, tipo);
      });

      card.addEventListener('mouseleave', function() {
        ocultarTooltip();
      });

      // Evento click para modal
      card.addEventListener('click', function() {
        ocultarTooltip();
        if (tipo) {
          abrirModalDesglose(tipo);
        }
      });
    });

    // Overlay suave compartido para modales sin backdrop de Bootstrap (KPI Desglose + Edit Perfil)
    var customOverlayRefCount = 0;
    function mostrarOverlaySuave() {
      customOverlayRefCount++;
      var el = document.getElementById('customModalOverlay');
      if (!el) {
        el = document.createElement('div');
        el.id = 'customModalOverlay';
        el.setAttribute('aria-hidden', 'true');
        document.body.appendChild(el);
      }
      el.style.display = 'block';
    }
    function ocultarOverlaySuave() {
      customOverlayRefCount--;
      if (customOverlayRefCount <= 0) {
        customOverlayRefCount = 0;
        var el = document.getElementById('customModalOverlay');
        if (el) el.style.display = 'none';
      }
    }

    // Mover modal a body para que quede por encima del navbar (mismo stacking context que el layout)
    function asegurarModalEnBody(modalEl) {
      if (modalEl && modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
      }
    }

    // Modal Gestión de Permisos/Puestos: sin backdrop Bootstrap; overlay suave; encima de todo
    (function() {
      var modalEl = document.getElementById('modalEditPerfil');
      if (!modalEl) return;
      modalEl.addEventListener('show.bs.modal', function() {
        asegurarModalEnBody(modalEl);
        document.body.classList.add('modal-edit-perfil-open');
        mostrarOverlaySuave();
      });
      modalEl.addEventListener('shown.bs.modal', function() {
        modalEl.style.setProperty('z-index', '99999', 'important');
      });
      modalEl.addEventListener('hidden.bs.modal', function() {
        document.body.classList.remove('modal-edit-perfil-open');
        modalEl.style.removeProperty('z-index');
        ocultarOverlaySuave();
      });
    })();

    // Modal KPI Desglose: mismo overlay suave; encima de todo
    (function() {
      var modalKpi = document.getElementById('modalKpiDesglose');
      if (!modalKpi) return;
      modalKpi.addEventListener('show.bs.modal', function() {
        asegurarModalEnBody(modalKpi);
        document.body.classList.add('modal-kpi-desglose-open');
        mostrarOverlaySuave();
      });
      modalKpi.addEventListener('shown.bs.modal', function() {
        modalKpi.style.setProperty('z-index', '99999', 'important');
      });
      modalKpi.addEventListener('hidden.bs.modal', function() {
        document.body.classList.remove('modal-kpi-desglose-open');
        modalKpi.style.removeProperty('z-index');
        ocultarOverlaySuave();
      });
    })();
  });

  /**
   * ==========================================
   * FUNCIONES PARA KPIs DE BAJAS
   * ==========================================
   */

  // Variable global para almacenar datos de bajas
  let datosBajasGlobal = [];

  /**
   * ACTUALIZAR INDICADORES DE BAJAS
   * Calcula y muestra los 4 indicadores del módulo de Bajas
   */
  function actualizarIndicadoresBajas(datosBajas, tieneFiltroFecha = false) {
    datosBajasGlobal = datosBajas || [];

    if (!datosBajas || datosBajas.length === 0) {
      if (typeof kpiUpdateValuesBajas === 'function') {
        kpiUpdateValuesBajas({ total: 0, dep: 0, puesto: 0, bajasRef: 0, refLabel: 'Este mes',
          topDepNombre: '—', topDepBajas: 0, topPuestoNombre: '—', topPuestoBajas: 0,
          arcTotal: 0, arcDep: 0, arcPuesto: 0 });
      }
      return;
    }

    const totalBajas = datosBajas.length;

    // â”€â”€ DEPARTAMENTOS AFECTADOS â”€â”€
    const depConteo = {};
    datosBajas.forEach(baja => {
      const d = baja.departamento;
      if (d && d !== 'N/A' && d !== 'Sin departamento') depConteo[d] = (depConteo[d] || 0) + 1;
    });
    const depAfectados   = Object.keys(depConteo).length;
    const topDepEntry    = Object.entries(depConteo).sort((a, b) => b[1] - a[1])[0] || ['—', 0];
    const topDepNombre   = topDepEntry[0];
    const topDepBajas    = topDepEntry[1];

    // â”€â”€ PUESTOS AFECTADOS â”€â”€
    const puestoConteo = {};
    datosBajas.forEach(baja => {
      const p = baja.nombre_puesto;
      if (p && p !== 'N/A' && p !== 'Sin puesto') puestoConteo[p] = (puestoConteo[p] || 0) + 1;
    });
    const puestosAfectados = Object.keys(puestoConteo).length;
    const topPuestoEntry   = Object.entries(puestoConteo).sort((a, b) => b[1] - a[1])[0] || ['—', 0];
    const topPuestoNombre  = topPuestoEntry[0];
    const topPuestoBajas   = topPuestoEntry[1];

    // â”€â”€ BAJAS DEL MES / PERÍODO â”€â”€
    const ahora = new Date();
    const mesActual = ahora.getMonth(), anioActual = ahora.getFullYear();
    let bajasRef = 0, refLabel = 'Este mes';
    if (tieneFiltroFecha && rangoFechasBajas) {
      bajasRef = totalBajas;
      try {
        const fi = new Date(rangoFechasBajas.inicio + 'T00:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
        const ff = new Date(rangoFechasBajas.fin   + 'T00:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
        refLabel = fi + '–' + ff;
      } catch(e) { refLabel = 'Período'; }
    } else {
      bajasRef = datosBajas.filter(b => {
        const f = new Date(b.fecha_baja || b.fecha_ingreso || b.fecha_registro || null);
        return !isNaN(f) && f.getMonth() === mesActual && f.getFullYear() === anioActual;
      }).length;
    }

    // â”€â”€ PORCENTAJES PARA BARRAS / DONUTS â”€â”€
    const arcTotal  = Math.min(100, totalBajas);
    const arcDep    = Math.min(100, (depAfectados  / 20) * 100);
    const arcPuesto = Math.min(100, (puestosAfectados / 50) * 100);

    if (typeof kpiUpdateValuesBajas === 'function') {
      kpiUpdateValuesBajas({ total: totalBajas, dep: depAfectados, puesto: puestosAfectados,
        bajasRef, refLabel, topDepNombre, topDepBajas, topPuestoNombre, topPuestoBajas,
        arcTotal, arcDep, arcPuesto });
    }
  }

  /**
   * OBTENER TODOS LOS DEPARTAMENTOS CON BAJAS
   */
  function obtenerTodosDepartamentosBajas(datos) {
    const departamentos = new Set();
    datos.forEach(baja => {
      const depto = baja.departamento;
      if (depto && depto !== 'N/A' && depto !== 'Sin departamento') {
        departamentos.add(depto);
      }
    });
    return Array.from(departamentos);
  }

  /**
   * OBTENER TODOS LOS PUESTOS CON BAJAS
   */
  function obtenerTodosPuestosBajas(datos) {
    const puestos = new Set();
    datos.forEach(baja => {
      const puesto = baja.nombre_puesto;
      if (puesto && puesto !== 'N/A' && puesto !== 'Sin puesto') {
        puestos.add(puesto);
      }
    });
    return Array.from(puestos);
  }

  /**
   * OBTENER DEPARTAMENTO CON MÁS BAJAS
   */
  function obtenerTopDepartamentoBajas(datos) {
    const conteo = {};
    datos.forEach(baja => {
      const depto = baja.departamento;
      if (depto && depto !== 'N/A' && depto !== 'Sin departamento') {
        conteo[depto] = (conteo[depto] || 0) + 1;
      }
    });

    const entries = Object.entries(conteo);
    if (entries.length === 0) {
      return { nombre: 'N/A', cantidad: 0 };
    }

    const [nombre, cantidad] = entries.sort((a, b) => b[1] - a[1])[0];
    return { nombre, cantidad };
  }

  /**
   * OBTENER PUESTO CON MÁS BAJAS
   */
  function obtenerTopPuestoBajas(datos) {
    const conteo = {};
    datos.forEach(baja => {
      const puesto = baja.nombre_puesto;
      if (puesto && puesto !== 'N/A' && puesto !== 'Sin puesto') {
        conteo[puesto] = (conteo[puesto] || 0) + 1;
      }
    });

    const entries = Object.entries(conteo);
    if (entries.length === 0) {
      return { nombre: 'N/A', cantidad: 0 };
    }

    const [nombre, cantidad] = entries.sort((a, b) => b[1] - a[1])[0];
    return { nombre, cantidad };
  }

  /**
   * MOSTRAR INDICADOR DE BAJAS DEL PERÍODO
   */
  // Compatibilidad: estas funciones ya no manejan el DOM directamente;
  // la lógica de período está integrada en actualizarIndicadoresBajas â†’ kpiUpdateValuesBajas.
  function mostrarIndicadorBajasPeriodo(cantidad, rangoFechas) { /* integrado en kpiUpdateValuesBajas */ }
  function ocultarIndicadorBajasPeriodo() { /* integrado en kpiUpdateValuesBajas */ }

  /**
   * ACTUALIZAR LABEL DE KPI (con truncado de texto)
   */
  function actualizarLabelKPI(elementId, texto, maxLength = 20) {
    const elemento = document.getElementById(elementId);
    if (!elemento) return;

    elemento.setAttribute('title', texto);

    let textoMostrado = texto;
    if (texto.length > maxLength) {
      textoMostrado = texto.substring(0, maxLength - 3) + '...';
    }
    elemento.textContent = textoMostrado;
  }

  /**
   * GENERAR MODAL DE BAJAS DEPARTAMENTALES
   * Muestra el TOP de departamentos con más bajas
   */
  function generarModalBajasDepartamentales(datos) {
    const conteo = {};
    datos.forEach(baja => {
      const depto = baja.departamento;
      if (depto && depto !== 'N/A' && depto !== 'Sin departamento') {
        conteo[depto] = (conteo[depto] || 0) + 1;
      }
    });

    const departamentos = Object.entries(conteo)
      .sort((a, b) => b[1] - a[1])
      .map(([dept, cantidad]) => ({ dept, cantidad }));

    const total = departamentos.length;
    const totalBajas = datos.length;
    const promedio = total > 0 ? (totalBajas / total).toFixed(1) : '0';

    let html = `
      <div class="row mb-3">
        <div class="col-md-4">
          <div class="stat-card" data-color="red">
            <div class="stat-label"><i class="bx bx-buildings me-1"></i>Departamentos Afectados</div>
            <div class="stat-value">${total}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" data-color="red">
            <div class="stat-label"><i class="bx bx-user-x me-1"></i>Total Bajas</div>
            <div class="stat-value">${totalBajas}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" data-color="red">
            <div class="stat-label"><i class="bx bx-trending-up me-1"></i>Promedio por Dept.</div>
            <div class="stat-value">${promedio}</div>
          </div>
        </div>
      </div>

      <h6 class="mb-3 fw-bold text-danger"><i class="bx bx-bar-chart me-2"></i>Top Departamentos con más Bajas</h6>
      <div class="table-responsive">
        <table class="table table-hover table-sm">
          <thead class="table-header-red">
            <tr>
              <th>#</th>
              <th>Departamento</th>
              <th class="text-center">Bajas</th>
              <th class="text-center">% del Total</th>
              <th>Distribución</th>
            </tr>
          </thead>
          <tbody>
    `;

    departamentos.forEach((item, index) => {
      const porcentaje = ((item.cantidad / totalBajas) * 100).toFixed(1);
      html += `
        <tr>
          <td>${index + 1}</td>
          <td><strong>${item.dept}</strong></td>
          <td class="text-center"><span class="badge badge-count" style="background: linear-gradient(135deg, #DC2626, #EF4444);">${item.cantidad}</span></td>
          <td class="text-center"><strong>${porcentaje}%</strong></td>
          <td>
            <div class="progress" style="height: 6px;">
              <div class="progress-bar progress-bar-kpi" role="progressbar"
                   style="width: ${porcentaje}%; background: linear-gradient(90deg, #DC2626, #EF4444);"
                   aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
          </td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    return { titulo: 'ðŸ¢ Bajas Departamentales - Análisis Completo', html };
  }

  /**
   * GENERAR MODAL DE BAJAS POR PUESTO
   * Muestra el TOP de puestos con más bajas
   */
  function generarModalBajasPorPuesto(datos) {
    const conteo = {};
    datos.forEach(baja => {
      const puesto = baja.nombre_puesto;
      if (puesto && puesto !== 'N/A' && puesto !== 'Sin puesto') {
        conteo[puesto] = (conteo[puesto] || 0) + 1;
      }
    });

    const puestos = Object.entries(conteo)
      .sort((a, b) => b[1] - a[1])
      .map(([puesto, cantidad]) => ({ puesto, cantidad }));

    const total = puestos.length;
    const totalBajas = datos.length;

    let html = `
      <div class="row mb-3">
        <div class="col-md-6">
          <div class="stat-card" data-color="orange">
            <div class="stat-label"><i class="bx bx-briefcase me-1"></i>Puestos Afectados</div>
            <div class="stat-value">${total}</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="stat-card" data-color="orange">
            <div class="stat-label"><i class="bx bx-user-x me-1"></i>Total Bajas</div>
            <div class="stat-value">${totalBajas}</div>
          </div>
        </div>
      </div>

      <h6 class="mb-3 fw-bold" style="color: #D97706;"><i class="bx bx-bar-chart me-2"></i>Top Puestos con más Bajas</h6>
      <div class="table-responsive">
        <table class="table table-hover table-sm">
          <thead style="background: linear-gradient(135deg, #D97706, #F59E0B); color: white;">
            <tr>
              <th>#</th>
              <th>Puesto</th>
              <th class="text-center">Bajas</th>
              <th class="text-center">% del Total</th>
              <th>Distribución</th>
            </tr>
          </thead>
          <tbody>
    `;

    puestos.forEach((item, index) => {
      const porcentaje = ((item.cantidad / totalBajas) * 100).toFixed(1);
      html += `
        <tr>
          <td>${index + 1}</td>
          <td><strong>${item.puesto}</strong></td>
          <td class="text-center"><span class="badge badge-count" style="background: linear-gradient(135deg, #D97706, #F59E0B);">${item.cantidad}</span></td>
          <td class="text-center"><strong>${porcentaje}%</strong></td>
          <td>
            <div class="progress" style="height: 6px;">
              <div class="progress-bar progress-bar-kpi" role="progressbar"
                   style="width: ${porcentaje}%; background: linear-gradient(90deg, #D97706, #F59E0B);"></div>
            </div>
          </td>
        </tr>
      `;
    });

    html += `
          </tbody>
        </table>
      </div>
    `;

    return { titulo: 'ðŸ’¼ Bajas por Puesto - Análisis Completo', html };
  }

  /**
   * ABRIR MODAL DE BAJAS
   */
  function abrirModalBajas(tipo) {
    if (!datosBajasGlobal || datosBajasGlobal.length === 0) {
      Swal.fire({
        icon: 'info',
        title: 'Sin datos',
        text: 'No hay información de bajas disponible',
        confirmButtonText: 'Aceptar'
      });
      return;
    }

    let contenidoModal;
    if (tipo === 'departamentos') {
      contenidoModal = generarModalBajasDepartamentales(datosBajasGlobal);
    } else if (tipo === 'puestos') {
      contenidoModal = generarModalBajasPorPuesto(datosBajasGlobal);
    } else if (tipo === 'total') {
      const totalB = datosBajasGlobal.length;
      const ahora  = new Date();
      const bajasHoy = datosBajasGlobal.filter(b => {
        const f = new Date(b.fecha_baja || b.fecha_ingreso || b.fecha_registro || null);
        return !isNaN(f) && f.getMonth() === ahora.getMonth() && f.getFullYear() === ahora.getFullYear();
      }).length;
      contenidoModal = {
        titulo: `<i class="bx bx-user-x me-2"></i>Total de Bajas — Resumen`,
        html: `
          <div class="row mb-3">
            <div class="col-md-4"><div class="stat-card" data-color="red"><div class="stat-label"><i class="bx bx-user-x me-1"></i>Total Bajas</div><div class="stat-value">${totalB}</div></div></div>
            <div class="col-md-4"><div class="stat-card" data-color="red"><div class="stat-label"><i class="bx bx-calendar me-1"></i>Este mes</div><div class="stat-value">${bajasHoy}</div></div></div>
            <div class="col-md-4"><div class="stat-card" data-color="red"><div class="stat-label"><i class="bx bx-trending-down me-1"></i>% del mes</div><div class="stat-value">${totalB > 0 ? ((bajasHoy/totalB)*100).toFixed(1) : 0}%</div></div></div>
          </div>
          <p class="text-muted small text-center mt-3"><i class="bx bx-info-circle me-1"></i>Haz clic en <strong>Departamentos</strong> o <strong>Puestos</strong> para ver el desglose detallado.</p>`
      };
    }

    if (!contenidoModal) return;

    // Actualizar el modal
    document.getElementById('modalKpiTitle').innerHTML = contenidoModal.titulo;
    document.getElementById('modalKpiContent').innerHTML = contenidoModal.html;

    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById('modalKpiDesglose'));
    modal.show();
  }

  /**
   * ==========================================
   * LLENAR FILTROS DINÁMICAMENTE
   * ==========================================
   * UserDireccion = Dirección
   * UserArea = Área
   * UserRole = Departamento
   * UserPlan = Puesto
   * Datos provenientes de: /CapHum/getUsuarios
   */

  // Variable global para almacenar todos los usuarios
  let usuariosData = [];
  let actualizandoFiltrosGestion = false;
  const mapaDepartamentoMetaGestion = new Map();
  document.querySelectorAll('#kpi-cell-puesto .kpi-cell-status').forEach(el => {
    el.textContent = '\u00danicos';
  });

  function normalizarValorFiltro(valor) {
    return String(valor || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim()
      .toLowerCase();
  }

  function obtenerPuestosUsuario(persona) {
    if (persona && Array.isArray(persona.puestos) && persona.puestos.length) {
      return persona.puestos;
    }
    if (!persona) return [];
    return [{
      id_puesto: persona.id_puesto,
      nombre_puesto: persona.nombre_puesto,
      nombre_departamento: persona.nombre_departamento,
      id_departamento: persona.id_departamento,
      id_area: persona.id_area,
      nombre_area: persona.nombre_area,
      id_direccion: persona.id_direccion,
      nombre_direccion: persona.nombre_direccion,
      id_empresa: persona.id_empresa || 1,
      nombre_empresa: persona.nombre_empresa || 'MaxiKash'
    }];
  }

  function getEmpresaGestionSeleccionadaVista() {
    return String(document.getElementById('gestionEmpresaSelect')?.value || '');
  }

  function rowCoincideEmpresaGestion(row) {
    const empresaId = getEmpresaGestionSeleccionadaVista();
    if (!empresaId) return true;
    return String(row?.id_empresa || 1) === empresaId;
  }

  function usuarioPerteneceEmpresaGestion(persona) {
    const empresaId = getEmpresaGestionSeleccionadaVista();
    if (!empresaId) return true;
    if (String(persona?.id_empresa || 1) === empresaId) return true;
    return obtenerPuestosUsuario(persona).some(puesto => String(puesto?.id_empresa || 1) === empresaId);
  }

  function filtrarUsuariosPorEmpresaGestionVista(usuarios) {
    return (usuarios || []).filter(usuarioPerteneceEmpresaGestion);
  }

  function obtenerMetaDepartamentoPorPuesto(puesto) {
    if (!puesto) return null;
    const idDepto = puesto.id_departamento != null ? String(puesto.id_departamento) : '';
    if (!idDepto || !mapaDepartamentoMetaGestion.has(idDepto)) return null;
    return mapaDepartamentoMetaGestion.get(idDepto);
  }

  function obtenerNombreAreaPorPuesto(puesto) {
    if (!puesto) return '';
    const nombreAreaPuesto = String(puesto.nombre_area || '').trim();
    if (nombreAreaPuesto) return nombreAreaPuesto;
    const meta = obtenerMetaDepartamentoPorPuesto(puesto);
    if (meta && meta.nombreArea) {
      return meta.nombreArea;
    }
    return '';
  }

  function obtenerNombreDireccionPorPuesto(puesto) {
    if (!puesto) return '';
    const nombreDireccionPuesto = String(puesto.nombre_direccion || '').trim();
    if (nombreDireccionPuesto) return nombreDireccionPuesto;
    const meta = obtenerMetaDepartamentoPorPuesto(puesto);
    if (meta && meta.nombreDireccion) {
      return meta.nombreDireccion;
    }
    return '';
  }

  function inicializarMapaAreasGestion() {
    mapaDepartamentoMetaGestion.clear();
    const catalogo = Array.isArray(window.catalogoCompletoDeptosBackend) ? window.catalogoCompletoDeptosBackend : [];
    const rows = catalogo.length > 0 ? catalogo : (Array.isArray(window.todosDepartamentosBackend) ? window.todosDepartamentosBackend : []);
    rows.forEach(row => {
      if (!row) return;
      if (!rowCoincideEmpresaGestion(row)) return;
      const idDepto = row.id != null ? String(row.id) : '';
      const nombreArea = String(row.departamento_organizacional_nombre || '').trim();
      const nombreDireccion = String(row.direccion_nombre || '').trim();
      if (!idDepto) return;
      mapaDepartamentoMetaGestion.set(idDepto, {
        nombreArea: (!nombreArea || nombreArea.toLowerCase() === 'sin departamento') ? '' : nombreArea,
        nombreDireccion: (!nombreDireccion || nombreDireccion.toLowerCase() === 'sin dirección') ? '' : nombreDireccion
      });
    });
  }

  function usuarioTieneDireccion(persona, direccion) {
    const dir = normalizarValorFiltro(direccion);
    if (!dir) return true;
    return obtenerPuestosUsuario(persona).some(p => normalizarValorFiltro(obtenerNombreDireccionPorPuesto(p)) === dir);
  }

  function usuarioTieneArea(persona, area) {
    const a = normalizarValorFiltro(area);
    if (!a) return true;
    return obtenerPuestosUsuario(persona).some(p => normalizarValorFiltro(obtenerNombreAreaPorPuesto(p)) === a);
  }

  function usuarioTieneDepartamento(persona, departamento) {
    const dep = normalizarValorFiltro(departamento);
    if (!dep) return true;
    return obtenerPuestosUsuario(persona).some(p => normalizarValorFiltro(p.nombre_departamento) === dep);
  }

  function usuarioTienePuesto(persona, puesto, departamento) {
    const pst = normalizarValorFiltro(puesto);
    const dep = normalizarValorFiltro(departamento);
    if (!pst) return true;
    return obtenerPuestosUsuario(persona).some(p => {
      const coincidePuesto = normalizarValorFiltro(p.nombre_puesto) === pst;
      const coincideDepartamento = !dep || normalizarValorFiltro(p.nombre_departamento) === dep;
      return coincidePuesto && coincideDepartamento;
    });
  }

  function usuarioCumpleFiltrosBase(persona, direccion, area, departamento, puesto) {
    if (!usuarioTieneDireccion(persona, direccion)) {
      return false;
    }

    if (!usuarioTieneArea(persona, area)) {
      return false;
    }

    if (!usuarioTieneDepartamento(persona, departamento)) {
      return false;
    }

    if (!usuarioTienePuesto(persona, puesto, departamento)) {
      return false;
    }

    return true;
  }

  function usuarioCumpleTipoPuesto(persona, tipoPuesto) {
    const tienePuestos = persona && persona.puestos && persona.puestos.length > 1;
    if (tipoPuesto === 'multiples') return tienePuestos;
    if (tipoPuesto === 'unico') return !tienePuestos;
    return true;
  }

  function ordenarValoresFiltro(valores) {
    return Array.from(valores).sort((a, b) => String(a).localeCompare(String(b), 'es', { sensitivity: 'base' }));
  }

  function actualizarOpcionesSelectFiltro(selectId, valores, placeholder) {
    const select = document.getElementById(selectId);
    if (!select) return '';

    const valorActual = select.value;
    const valoresOrdenados = ordenarValoresFiltro(valores);

    select.innerHTML = '';
    const optionDefault = document.createElement('option');
    optionDefault.value = '';
    optionDefault.textContent = placeholder;
    select.appendChild(optionDefault);

    valoresOrdenados.forEach(valor => {
      const option = document.createElement('option');
      option.value = valor;
      option.textContent = valor;
      select.appendChild(option);
    });

    select.value = valoresOrdenados.includes(valorActual) ? valorActual : '';

    if (typeof window.refreshSelectBuscador === 'function') {
      window.refreshSelectBuscador(selectId);
    } else if (typeof window.jQuery !== 'undefined' && window.jQuery.fn.select2) {
      window.jQuery(select).trigger('change.select2');
    }

    return select.value;
  }

  function obtenerDepartamentosDisponibles(tipoPuesto, puestoSeleccionado, areaSeleccionada, direccionSeleccionada) {
    const departamentos = new Set();

    usuariosData.forEach(persona => {
      if (!usuarioCumpleTipoPuesto(persona, tipoPuesto)) return;
      if (direccionSeleccionada && !usuarioTieneDireccion(persona, direccionSeleccionada)) return;
      if (areaSeleccionada && !usuarioTieneArea(persona, areaSeleccionada)) return;
      if (puestoSeleccionado && !usuarioTienePuesto(persona, puestoSeleccionado, '')) return;

      obtenerPuestosUsuario(persona).forEach(puesto => {
        if (puesto.nombre_departamento && puesto.nombre_departamento !== 'Sin departamento') {
          departamentos.add(puesto.nombre_departamento);
        }
      });
    });

    return departamentos;
  }

  function obtenerAreasDisponibles(tipoPuesto, direccionSeleccionada, departamentoSeleccionado, puestoSeleccionado) {
    const areas = new Set();

    usuariosData.forEach(persona => {
      if (!usuarioCumpleTipoPuesto(persona, tipoPuesto)) return;
      if (direccionSeleccionada && !usuarioTieneDireccion(persona, direccionSeleccionada)) return;
      if (departamentoSeleccionado && !usuarioTieneDepartamento(persona, departamentoSeleccionado)) return;
      if (puestoSeleccionado && !usuarioTienePuesto(persona, puestoSeleccionado, departamentoSeleccionado || '')) return;

      obtenerPuestosUsuario(persona).forEach(puesto => {
        const nombreArea = obtenerNombreAreaPorPuesto(puesto);
        if (nombreArea && nombreArea !== 'Sin área') {
          areas.add(nombreArea);
        }
      });
    });

    return areas;
  }

  function obtenerDireccionesDisponibles(tipoPuesto, areaSeleccionada, departamentoSeleccionado, puestoSeleccionado) {
    const direcciones = new Set();

    usuariosData.forEach(persona => {
      if (!usuarioCumpleTipoPuesto(persona, tipoPuesto)) return;
      if (areaSeleccionada && !usuarioTieneArea(persona, areaSeleccionada)) return;
      if (departamentoSeleccionado && !usuarioTieneDepartamento(persona, departamentoSeleccionado)) return;
      if (puestoSeleccionado && !usuarioTienePuesto(persona, puestoSeleccionado, departamentoSeleccionado || '')) return;

      obtenerPuestosUsuario(persona).forEach(puesto => {
        const nombreDireccion = obtenerNombreDireccionPorPuesto(puesto);
        if (nombreDireccion && nombreDireccion !== 'Sin dirección') {
          direcciones.add(nombreDireccion);
        }
      });
    });

    return direcciones;
  }

  function obtenerTodasAreasDeUsuarios() {
    const areas = new Set();
    usuariosData.forEach(persona => {
      obtenerPuestosUsuario(persona).forEach(puesto => {
        const nombreArea = obtenerNombreAreaPorPuesto(puesto);
        if (nombreArea && nombreArea !== 'Sin área') {
          areas.add(nombreArea);
        }
      });
    });
    return areas;
  }

  function obtenerTodasAreasGlobales() {
    const areas = new Set();
    const rows = Array.isArray(window.catalogoCompletoDeptosBackend)
      ? window.catalogoCompletoDeptosBackend
      : (Array.isArray(window.todosDepartamentosBackend) ? window.todosDepartamentosBackend : []);

    rows.forEach(row => {
      if (!row) return;
      if (!rowCoincideEmpresaGestion(row)) return;
      const nombreArea = String(row.departamento_organizacional_nombre || '').trim();
      if (nombreArea && nombreArea.toLowerCase() !== 'sin departamento') {
        areas.add(nombreArea);
      }
    });

    if (areas.size === 0) {
      return obtenerTodasAreasDeUsuarios();
    }
    return areas;
  }

  function obtenerTodasDireccionesGlobales() {
    const direcciones = new Set();
    const rows = Array.isArray(window.catalogoCompletoDeptosBackend)
      ? window.catalogoCompletoDeptosBackend
      : (Array.isArray(window.todosDepartamentosBackend) ? window.todosDepartamentosBackend : []);

    rows.forEach(row => {
      if (!row) return;
      if (!rowCoincideEmpresaGestion(row)) return;
      const nombreDireccion = String(row.direccion_nombre || '').trim();
      if (nombreDireccion && nombreDireccion.toLowerCase() !== 'sin dirección') {
        direcciones.add(nombreDireccion);
      }
    });

    if (direcciones.size === 0) {
      usuariosData.forEach(persona => {
        obtenerPuestosUsuario(persona).forEach(puesto => {
          const nombreDireccion = obtenerNombreDireccionPorPuesto(puesto);
          if (nombreDireccion && nombreDireccion !== 'Sin dirección') {
            direcciones.add(nombreDireccion);
          }
        });
      });
    }

    return direcciones;
  }

  function obtenerPuestosDisponibles(tipoPuesto, departamentoSeleccionado, areaSeleccionada, direccionSeleccionada) {
    const puestos = new Set();

    usuariosData.forEach(persona => {
      if (!usuarioCumpleTipoPuesto(persona, tipoPuesto)) return;
      if (direccionSeleccionada && !usuarioTieneDireccion(persona, direccionSeleccionada)) return;
      if (areaSeleccionada && !usuarioTieneArea(persona, areaSeleccionada)) return;
      if (departamentoSeleccionado && !usuarioTieneDepartamento(persona, departamentoSeleccionado)) return;

      obtenerPuestosUsuario(persona).forEach(puesto => {
        const coincideDepartamento = !departamentoSeleccionado ||
          normalizarValorFiltro(puesto.nombre_departamento) === normalizarValorFiltro(departamentoSeleccionado);

        if (coincideDepartamento && puesto.nombre_puesto && puesto.nombre_puesto !== 'Sin puesto') {
          puestos.add(puesto.nombre_puesto);
        }
      });
    });

    return puestos;
  }

  function actualizarFiltroMultiplePuestos(datosBase, autoSeleccionar = true) {
    const selectMultiple = document.getElementById('FilterMultiplePuestos');
    if (!selectMultiple) return '';

    const datos = Array.isArray(datosBase) ? datosBase : usuariosData;
    const usuariosMultiples = datos.filter(u => u.puestos && u.puestos.length > 1).length;
    const usuariosUnicos = datos.length - usuariosMultiples;
    const options = selectMultiple.querySelectorAll('option');

    if (options[1]) {
      options[1].textContent = `Múltiples puestos (${usuariosMultiples})`;
      options[1].disabled = usuariosMultiples === 0;
      options[1].textContent = `M\u00faltiples puestos (${usuariosMultiples})`;
    }

    if (options[2]) {
      options[2].textContent = `Ãšnico puesto (${usuariosUnicos})`;
      options[2].disabled = usuariosUnicos === 0;
      options[2].textContent = `\u00danico puesto (${usuariosUnicos})`;
    }

    let valorActual = selectMultiple.value;
    const valorInvalido =
      (valorActual === 'multiples' && usuariosMultiples === 0) ||
      (valorActual === 'unico' && usuariosUnicos === 0);

    if (valorInvalido) {
      selectMultiple.value = '';
      valorActual = '';
    }

    if (autoSeleccionar && !valorActual) {
      if (usuariosMultiples === 0 && usuariosUnicos > 0) {
        selectMultiple.value = 'unico';
      } else if (usuariosUnicos === 0 && usuariosMultiples > 0) {
        selectMultiple.value = 'multiples';
      }
    }

    if (typeof window.jQuery !== 'undefined' && window.jQuery.fn.select2) {
      window.jQuery(selectMultiple).trigger('change.select2');
    }

    return selectMultiple.value;
  }

  function actualizarOpcionesFiltrosGestion(origen = '') {
    if (actualizandoFiltrosGestion) return;
    actualizandoFiltrosGestion = true;

    const selectDireccion = document.getElementById('UserDireccion');
    const selectArea = document.getElementById('UserArea');
    const selectDepartamento = document.getElementById('UserRole');
    const selectPuesto = document.getElementById('UserPlan');
    const selectMultiple = document.getElementById('FilterMultiplePuestos');

    const autoTipo = origen !== 'FilterMultiplePuestos';
    let tipoPuesto = selectMultiple?.value || '';
    let direccion = selectDireccion?.value || '';
    let area = selectArea?.value || '';
    let departamento = selectDepartamento?.value || '';
    let puesto = selectPuesto?.value || '';

    direccion = actualizarOpcionesSelectFiltro(
      'UserDireccion',
      obtenerDireccionesDisponibles(tipoPuesto, area, departamento, puesto).size > 0
        ? obtenerDireccionesDisponibles(tipoPuesto, area, departamento, puesto)
        : obtenerTodasDireccionesGlobales(),
      'Selecciona Dirección'
    );

    area = actualizarOpcionesSelectFiltro(
      'UserArea',
      obtenerAreasDisponibles(tipoPuesto, direccion, departamento, puesto).size > 0
        ? obtenerAreasDisponibles(tipoPuesto, direccion, departamento, puesto)
        : obtenerTodasAreasGlobales(),
      'Selecciona Área'
    );

    departamento = actualizarOpcionesSelectFiltro(
      'UserRole',
      obtenerDepartamentosDisponibles(tipoPuesto, puesto, area, direccion),
      'Selecciona Departamento'
    );

    puesto = actualizarOpcionesSelectFiltro(
      'UserPlan',
      obtenerPuestosDisponibles(tipoPuesto, departamento, area, direccion),
      'Selecciona Puesto'
    );

    const datosBase = usuariosData.filter(persona =>
      usuarioCumpleFiltrosBase(persona, direccion, area, departamento, puesto)
    );
    tipoPuesto = actualizarFiltroMultiplePuestos(datosBase, autoTipo);

    if (autoTipo) {
      direccion = actualizarOpcionesSelectFiltro(
        'UserDireccion',
        obtenerDireccionesDisponibles(tipoPuesto, area, departamento, puesto).size > 0
          ? obtenerDireccionesDisponibles(tipoPuesto, area, departamento, puesto)
          : obtenerTodasDireccionesGlobales(),
        'Selecciona Dirección'
      );

      area = actualizarOpcionesSelectFiltro(
        'UserArea',
        obtenerAreasDisponibles(tipoPuesto, direccion, departamento, puesto).size > 0
          ? obtenerAreasDisponibles(tipoPuesto, direccion, departamento, puesto)
          : obtenerTodasAreasGlobales(),
        'Selecciona Área'
      );

      departamento = actualizarOpcionesSelectFiltro(
        'UserRole',
        obtenerDepartamentosDisponibles(tipoPuesto, puesto, area, direccion),
        'Selecciona Departamento'
      );

      actualizarOpcionesSelectFiltro(
        'UserPlan',
        obtenerPuestosDisponibles(tipoPuesto, departamento, area, direccion),
        'Selecciona Puesto'
      );
    }

    ['gestionEmpresaSelect', 'UserDireccion', 'UserArea', 'UserRole', 'UserPlan', 'FilterMultiplePuestos'].forEach(id => {
      const filtro = document.getElementById(id);
      if (filtro) aplicarFeedbackVisualFiltro(filtro);
    });

    actualizandoFiltrosGestion = false;
  }

  function manejarCambioFiltroGestion(event) {
    if (actualizandoFiltrosGestion) return;
    const filtro = event.currentTarget;
    if (!filtro) return;

    if (filtro.id === 'gestionEmpresaSelect') {
      const base = Array.isArray(window.usuariosDataCompleta) && window.usuariosDataCompleta.length
        ? window.usuariosDataCompleta
        : usuariosData;
      usuariosData = filtrarUsuariosPorEmpresaGestionVista(base);
      window.usuariosData = usuariosData;
      ['UserDireccion', 'UserArea', 'UserRole', 'UserPlan', 'FilterMultiplePuestos'].forEach(id => {
        const child = document.getElementById(id);
        if (child) child.value = '';
      });
      inicializarMapaAreasGestion();
      actualizarOpcionesFiltrosGestion('gestionEmpresaSelect');
      aplicarFiltros();
      return;
    }

    actualizarOpcionesFiltrosGestion(filtro.id);
    aplicarFiltros();
  }

  function vincularEventosFiltrosGestion() {
    const filtros = ['gestionEmpresaSelect', 'UserDireccion', 'UserArea', 'UserRole', 'UserPlan', 'FilterMultiplePuestos'];

    filtros.forEach(id => {
      const filtro = document.getElementById(id);
      if (!filtro) return;

      if (typeof window.jQuery !== 'undefined') {
        window.jQuery(filtro)
          .off('change.gestionPersonalFiltros')
          .on('change.gestionPersonalFiltros', manejarCambioFiltroGestion);
      } else {
        filtro.onchange = manejarCambioFiltroGestion;
      }
    });
  }

  /**
   * ==========================================
   * CONSOLIDAR USUARIOS CON MÃšLTIPLES PUESTOS
   * ==========================================
   * Agrupa registros duplicados de usuarios que tienen múltiples puestos
   * y retorna un array con usuarios únicos
   */
  function consolidarUsuarios(usuarios) {
    const usuariosMap = new Map();

    usuarios.forEach(usuario => {
      const id = usuario.id;

      if (!usuariosMap.has(id)) {
        // Primera vez que vemos este usuario, crear estructura
        usuariosMap.set(id, {
          ...usuario,
          puestos: usuario.id_puesto ? [{
            id_puesto: usuario.id_puesto,
            nombre_puesto: usuario.nombre_puesto,
            nombre_departamento: usuario.nombre_departamento,
            id_departamento: usuario.id_departamento,
            id_area: usuario.id_area,
            nombre_area: usuario.nombre_area,
            id_direccion: usuario.id_direccion,
            nombre_direccion: usuario.nombre_direccion,
            id_empresa: usuario.id_empresa || 1,
            nombre_empresa: usuario.nombre_empresa || 'MaxiKash'
          }] : [],
          // Guardar el nombre del puesto y departamento originales para compatibilidad
          nombre_puesto_principal: usuario.nombre_puesto,
          nombre_departamento_principal: usuario.nombre_departamento
        });
      } else {
        // Ya existe, agregar nuevo puesto si no está duplicado
        const usuarioExistente = usuariosMap.get(id);
        if (!usuario.id_puesto) {
          return;
        }
        const puestoExiste = usuarioExistente.puestos.some(p =>
          p.id_puesto === usuario.id_puesto &&
          p.nombre_departamento === usuario.nombre_departamento
        );

        if (!puestoExiste) {
          usuarioExistente.puestos.push({
            id_puesto: usuario.id_puesto,
            nombre_puesto: usuario.nombre_puesto,
            nombre_departamento: usuario.nombre_departamento,
            id_departamento: usuario.id_departamento,
            id_area: usuario.id_area,
            nombre_area: usuario.nombre_area,
            id_direccion: usuario.id_direccion,
            nombre_direccion: usuario.nombre_direccion,
            id_empresa: usuario.id_empresa || 1,
            nombre_empresa: usuario.nombre_empresa || 'MaxiKash'
          });
        }
      }
    });

    return Array.from(usuariosMap.values());
  }

  function esVistaBajasActual() {
    const path = (window.location && window.location.pathname ? window.location.pathname : '').toLowerCase();
    return path.includes('/caphum/bajas');
  }

  function llenarFiltros() {
    // En Control de Bajas la tabla usa otro esquema de columnas/datos.
    // Evita inyectar dataset de Gestión para no romper DataTables.
    if (esVistaBajasActual()) {
      return;
    }

    // Llamar a la API para obtener los usuarios/gestores
    http.request({
      endpoint: "/CapHum/getUsuarios",
      onSuccess: (resp) => {

        if (!resp.success || !resp.datos || resp.datos.length === 0) {
          console.warn(' No hay datos disponibles');
          return;
        }

        // Consolidar usuarios con múltiples puestos
        const usuariosConsolidados = consolidarUsuarios(resp.datos);

        if (typeof renderEmpresasGestion === 'function') {
          renderEmpresasGestion(usuariosConsolidados);
        }

        // Guardar copia completa y trabajar con la empresa seleccionada.
        window.usuariosDataCompleta = usuariosConsolidados;
        usuariosData = filtrarUsuariosPorEmpresaGestionVista(usuariosConsolidados);
        window.usuariosData = usuariosData;
        inicializarMapaAreasGestion();

        // Pintar la primera carga con el mismo renderer compacto que usan los filtros.
        actualizarTabla(usuariosData);

        // ==========================================
        // ACTUALIZAR INDICADORES (KPIs)
        // ==========================================
        actualizarIndicadores(usuariosData);
        const areas = obtenerTodasAreasGlobales();
        const departamentos = new Set();
        const puestos = new Set();
        const estatus = new Set();

        // CONJUNTOS para almacenar valores únicos (evita duplicados)

        // Iterar los datos y extraer valores únicos
        usuariosData.forEach(persona => {
          // DEPARTAMENTO
          if (persona.nombre_departamento && persona.nombre_departamento !== 'Sin departamento') {
            departamentos.add(persona.nombre_departamento);
          }

          // PUESTO
          if (persona.nombre_puesto && persona.nombre_puesto !== 'Sin puesto') {
            puestos.add(persona.nombre_puesto);
          }

          // ESTATUS
          if (persona.estatus) {
            estatus.add(persona.estatus);
          }
        });

        //  FORZAR "Inactivo" en los estatus

        estatus.add('Activo');

        // Área se mantiene desde backend (HTML inicial) para no romper Select2.

        // ==========================================
        // LLENAR SELECT DEPARTAMENTO (UserRole)
        // ==========================================
        const selectDepartamento = document.getElementById('UserRole');
        if (selectDepartamento) {
          const opciones = selectDepartamento.querySelectorAll('option');
          opciones.forEach((opt, index) => {
            if (index > 0) opt.remove();
          });

          departamentos.forEach(dep => {
            const option = document.createElement('option');
            option.value = dep;
            option.textContent = dep;
            selectDepartamento.appendChild(option);
          });

          //  Agregar listener para ACTUALIZAR PUESTOS cuando cambia departamento
          window.jQuery('#UserRole').on('change', function(e) {
            // La cascada oficial se maneja en vincularEventosFiltrosGestion()
            // para respetar Dirección -> Área -> Departamento -> Puesto.
            aplicarFeedbackVisualFiltro(this);
            aplicarFiltros();
          });
        }

        // ==========================================
        // LLENAR SELECT PUESTO (UserPlan) - INICIAL
        // ==========================================
        const selectPuesto = document.getElementById('UserPlan');
        if (selectPuesto) {
          const opciones = selectPuesto.querySelectorAll('option');
          opciones.forEach((opt, index) => {
            if (index > 0) opt.remove();
          });

          // Mostrar TODOS los puestos al inicio (sin filtro)
          puestos.forEach(puesto => {
            const option = document.createElement('option');
            option.value = puesto;
            option.textContent = puesto;
            selectPuesto.appendChild(option);
          });

          // Agregar listener para filtrar en tiempo real
          window.jQuery('#UserPlan').on('change', function(e) {
            aplicarFeedbackVisualFiltro(this);
            aplicarFiltros();
          });
        }

        // ==========================================
        // LLENAR SELECT ESTATUS (FilterTransaction)
        // ==========================================
        const selectEstatus = document.getElementById('FilterTransaction');
        if (selectEstatus) {
          const opciones = selectEstatus.querySelectorAll('option');
          opciones.forEach((opt, index) => {
            if (index > 0) opt.remove();
          });

          estatus.forEach(est => {
            const option = document.createElement('option');
            option.value = est;
            option.textContent = est;
            selectEstatus.appendChild(option);
          });

          //  Agregar listener para filtrar en tiempo real
          window.jQuery('#FilterTransaction').on('change', function(e) {
            aplicarFeedbackVisualFiltro(this);
            aplicarFiltros();
          });
        }

        // ==========================================
        // INICIALIZAR FILTRO DE MÃšLTIPLES PUESTOS
        // ==========================================
        const selectMultiplePuestos = document.getElementById('FilterMultiplePuestos');
        if (selectMultiplePuestos) {
          // Agregar listener para filtrar en tiempo real
          window.jQuery('#FilterMultiplePuestos').on('change', function(e) {
            this.dataset.autoContexto = '0';
            aplicarFeedbackVisualFiltro(this);
            aplicarFiltros();
          });
        }
        vincularEventosFiltrosGestion();
        actualizarOpcionesFiltrosGestion('init');

        if (typeof window.refreshSelectBuscador === 'function') {
          window.refreshSelectBuscador('UserDireccion');
          window.refreshSelectBuscador('UserArea');
          window.refreshSelectBuscador('UserRole');
          window.refreshSelectBuscador('UserPlan');
          window.refreshSelectBuscador('FilterMultiplePuestos');
        }
      },
      onError: (err) => {
        console.error(' Error al cargar datos:', err);
      }
    });
  }

  /**
   * ==========================================
   * FILTRAR USUARIOS MULTIPUESTO
   * ==========================================
   * Función para filtrar usuarios con múltiples puestos al hacer click en el KPI
   */
  function filtrarMultipuestos() {
    const selectMultiple = document.getElementById('FilterMultiplePuestos');
    if (selectMultiple) {
      const optionMultiples = selectMultiple.querySelector('option[value="multiples"]');
      if (optionMultiples && optionMultiples.disabled) return;

      selectMultiple.value = 'multiples';
      aplicarFeedbackVisualFiltro(selectMultiple);
      actualizarOpcionesFiltrosGestion('FilterMultiplePuestos');
      aplicarFiltros();

      // Scroll suave hacia la tabla
      setTimeout(() => {
        document.getElementById('historialUsuarios')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }, 300);
    }
  }

  /**
   * ==========================================
   * EXPANDIR PUESTOS
   * ==========================================
   * Muestra todos los puestos de un usuario en un modal
   */
  function expandirPuestos(userId) {
    const usuario = usuariosData.find(u => u.id === userId);
    if (!usuario || !usuario.puestos) return;

    let puestosHTML = '<div class="d-flex flex-column gap-2">';
    usuario.puestos.forEach((puesto, index) => {
      const esPrincipal = index === 0;
      const iconoPuestoHtml = esPrincipal ? '<i class="fa fa-star"></i>' : '<i class="fa fa-thumbtack"></i>';
      const claseBadge = esPrincipal
        ? 'badge text-white badge-puesto-principal'
        : 'badge bg-success badge-puesto-secundario';

      puestosHTML += `
        <div class="d-flex flex-column" style="gap: 0.25rem;">
          <small class="departamento-label">
            <i class="fa fa-building"></i>${nombreDepartamentoSeguro(puesto.nombre_departamento)}
          </small>
          <span class="${claseBadge}" style="${esPrincipal ? 'background-color: var(--bs-blue); ' : ''}width: 100%;">
            <span style="font-size: 0.9rem;">${iconoPuestoHtml}</span>
            <i class="fa fa-briefcase"></i>
            ${puesto.nombre_puesto}
            ${esPrincipal ? '<small class="ms-1" style="opacity: 0.8;">(Principal)</small>' : ''}
          </span>
        </div>
      `;
    });
    puestosHTML += '</div>';

    const nombreCompleto = [usuario.nombres, usuario.segundo_nombre, usuario.apellidop, usuario.apellidom].filter(x => x).join(' ');

    Swal.fire({
      title: `<i class="fa fa-layer-group me-2"></i>${usuario.puestos.length} Puestos Asignados`,
      html: `
        <div class="text-start">
          <p class="mb-3"><strong><i class="fa fa-user me-2"></i>${nombreCompleto}</strong></p>
          <hr>
          ${puestosHTML}
        </div>
      `,
      width: '600px',
      showCloseButton: true,
      showConfirmButton: false,
      customClass: {
        popup: 'swal-wide'
      }
    });
  }

  /**
   * ==========================================
   * ACTUALIZAR PUESTOS SEGÃšN DEPARTAMENTO
   * ==========================================
   * Cuando el usuario selecciona un departamento,
   * este filtro muestra solo los puestos de ese departamento
   */
  function actualizarPuestosSegunDepartamento(departamentoSeleccionado) {
    const selectPuesto = document.getElementById('UserPlan');

    if (!selectPuesto) {
      return;
    }

    // Si no hay departamento seleccionado, mostrar TODOS los puestos
    if (!departamentoSeleccionado) {

      // Extraer todos los puestos únicos
      const todosPuestos = new Set();
      usuariosData.forEach(persona => {
        obtenerPuestosUsuario(persona).forEach(puesto => {
          if (puesto.nombre_puesto && puesto.nombre_puesto !== 'Sin puesto') {
            todosPuestos.add(puesto.nombre_puesto);
          }
        });
      });

      // Limpiar opciones previas (excepto la primera)
      const opciones = selectPuesto.querySelectorAll('option');
      opciones.forEach((opt, index) => {
        if (index > 0) opt.remove();
      });

      // Agregar todos los puestos
      todosPuestos.forEach(puesto => {
        const option = document.createElement('option');
        option.value = puesto;
        option.textContent = puesto;
        selectPuesto.appendChild(option);
      });

      // Resetear el select
      selectPuesto.value = '';
      if (typeof window.refreshSelectBuscador === 'function') {
        window.refreshSelectBuscador('UserPlan');
      }
      return;
    }

    // Extraer SOLO los puestos del departamento seleccionado
    const puestosDelDepartamento = new Set();
    usuariosData.forEach(persona => {
      obtenerPuestosUsuario(persona).forEach(puesto => {
        if (normalizarValorFiltro(puesto.nombre_departamento) === normalizarValorFiltro(departamentoSeleccionado) &&
            puesto.nombre_puesto &&
            puesto.nombre_puesto !== 'Sin puesto') {
          puestosDelDepartamento.add(puesto.nombre_puesto);
        }
      });
    });

    // Limpiar opciones previas (excepto la primera)
    const opciones = selectPuesto.querySelectorAll('option');
    opciones.forEach((opt, index) => {
      if (index > 0) opt.remove();
    });

    // Agregar nuevos puestos
    puestosDelDepartamento.forEach(puesto => {
      const option = document.createElement('option');
      option.value = puesto;
      option.textContent = puesto;
      selectPuesto.appendChild(option);
    });

    // Resetear el select de puestos
    selectPuesto.value = '';
    if (typeof window.refreshSelectBuscador === 'function') {
      window.refreshSelectBuscador('UserPlan');
    }
  }

  /**
   * ==========================================
   * APLICAR FILTROS EN TIEMPO REAL
   * ==========================================
   * Filtra la tabla según los valores seleccionados
   */
  function aplicarFiltros() {
    // Obtener valores seleccionados
    const direccionSeleccionada = document.getElementById('UserDireccion')?.value || '';
    const areaSeleccionada = document.getElementById('UserArea')?.value || '';
    const departamentoSeleccionado = document.getElementById('UserRole')?.value || '';
    const puestoSeleccionado = document.getElementById('UserPlan')?.value || '';
    let multiplePuestosSeleccionado = document.getElementById('FilterMultiplePuestos')?.value || '';

      const datosBase = usuariosData.filter(persona =>
        usuarioCumpleFiltrosBase(persona, direccionSeleccionada, areaSeleccionada, departamentoSeleccionado, puestoSeleccionado)
      );

    // Filtrar datos
    const datosFiltrados = datosBase.filter(persona => {
      // Filtro MÃšLTIPLES PUESTOS
      if (multiplePuestosSeleccionado) {
        if (!usuarioCumpleTipoPuesto(persona, multiplePuestosSeleccionado)) {
          return false;
        }
      }

      return true;
    });

    // Actualizar tabla con datos filtrados
    actualizarTabla(datosFiltrados);

    // Actualizar indicadores con datos filtrados
    try {
      actualizarIndicadores(datosFiltrados);
    } catch (error) {
      console.error('Error al actualizar indicadores de filtros:', error);
    }
  }

  /**
   * ==========================================
   * ACTUALIZAR TABLA CON DATOS FILTRADOS
   * ==========================================
   */
  function escapeAttr(value) {
    return String(value || '')
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function inicialesPersona(nombre) {
    const partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
    if (!partes.length) return 'US';
    return partes.slice(0, 2).map(parte => parte.charAt(0)).join('').toUpperCase();
  }

  function avatarPersonaHtml(persona, nombreCompleto) {
    const foto = String(persona.foto_perfil || '').trim();
    const iniciales = inicialesPersona(nombreCompleto);
    const nombreEsc = escapeAttr(nombreCompleto || 'Usuario');
    const fotoEsc = escapeAttr(foto);
    const inicialesEsc = escapeAttr(iniciales);
    const title = foto ? 'Ver foto de ' : 'Ver avatar de ';
    if (foto) {
      return `<button type="button" class="gestion-avatar-btn" data-gestion-foto="${fotoEsc}" data-gestion-nombre="${nombreEsc}" data-gestion-iniciales="${inicialesEsc}" title="${escapeAttr(title + (nombreCompleto || 'usuario'))}" aria-label="${escapeAttr(title + (nombreCompleto || 'usuario'))}" onclick="abrirVisorFotoGestion(this, event)">
        <img class="gestion-personal-avatar" src="${fotoEsc}" alt="Foto de ${nombreEsc}" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
        <span class="gestion-personal-avatar-fallback" style="display:none;">${inicialesEsc}</span>
      </button>`;
    }
    return `<button type="button" class="gestion-avatar-btn" data-gestion-foto="" data-gestion-nombre="${nombreEsc}" data-gestion-iniciales="${inicialesEsc}" title="${escapeAttr(title + (nombreCompleto || 'usuario'))}" aria-label="${escapeAttr(title + (nombreCompleto || 'usuario'))}" onclick="abrirVisorFotoGestion(this, event)">
      <span class="gestion-personal-avatar-fallback">${inicialesEsc}</span>
    </button>`;
  }

  window.abrirVisorFotoGestion = function (el, event) {
    if (event) {
      event.preventDefault();
      event.stopPropagation();
    }
    const modalEl = document.getElementById('modalGestionFotoUsuario');
    const nombreEl = document.getElementById('gestionFotoVisorNombre');
    const subtituloEl = document.getElementById('gestionFotoVisorSubtitulo');
    const imgEl = document.getElementById('gestionFotoVisorImg');
    const fallbackEl = document.getElementById('gestionFotoVisorFallback');
    if (!modalEl || !imgEl || !fallbackEl) return;

    if (modalEl.parentNode !== document.body) {
      document.body.appendChild(modalEl);
    }

    const nombre = (el && el.getAttribute('data-gestion-nombre')) || 'Usuario';
    const foto = (el && el.getAttribute('data-gestion-foto')) || '';
    const iniciales = (el && el.getAttribute('data-gestion-iniciales')) || inicialesPersona(nombre);

    if (nombreEl) nombreEl.textContent = nombre;
    fallbackEl.textContent = iniciales;
    imgEl.onerror = function () {
      imgEl.classList.add('d-none');
      imgEl.removeAttribute('src');
      if (subtituloEl) subtituloEl.textContent = 'Avatar generado con iniciales';
      fallbackEl.classList.remove('d-none');
    };

    if (foto) {
      fallbackEl.classList.add('d-none');
      if (subtituloEl) subtituloEl.textContent = 'Foto de perfil';
      imgEl.alt = 'Foto de ' + nombre;
      imgEl.src = foto;
      imgEl.classList.remove('d-none');
    } else {
      if (subtituloEl) subtituloEl.textContent = 'Avatar generado con iniciales';
      imgEl.classList.add('d-none');
      imgEl.removeAttribute('src');
      fallbackEl.classList.remove('d-none');
    }

    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });
    modalEl.style.setProperty('z-index', '100080', 'important');
    bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: false, keyboard: true }).show();
  };

  document.addEventListener('click', function (event) {
    const modalEl = document.getElementById('modalGestionFotoUsuario');
    if (modalEl && event.target === modalEl) {
      bootstrap.Modal.getInstance(modalEl)?.hide();
    }
  });

  document.addEventListener('hidden.bs.modal', function (event) {
    if (event.target && event.target.id === 'modalGestionFotoUsuario') {
      event.target.style.removeProperty('z-index');
      const imgEl = document.getElementById('gestionFotoVisorImg');
      if (imgEl) {
        imgEl.classList.add('d-none');
        imgEl.removeAttribute('src');
      }
    }
  });

  function normalizarDepartamentoEtiqueta(valor) {
    return String(valor || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim()
      .toLowerCase();
  }

  function usuarioExentoEtiquetaExterno(usuario) {
    if (!usuario) return false;
    const nombreCompleto = [
      usuario.nombres,
      usuario.segundo_nombre,
      usuario.apellidop,
      usuario.apellidom
    ].filter(Boolean).join(' ');
    const nombreNormalizado = normalizarDepartamentoEtiqueta(nombreCompleto);
    return nombreNormalizado.includes('hector') && nombreNormalizado.includes('ruiz');
  }

  function esDepartamentoExterno(nombreDepartamento) {
    return normalizarDepartamentoEtiqueta(nombreDepartamento) === 'despachos cobranza';
  }

  function esUsuarioExternoGestion(usuario) {
    return ['1', 'true', 'si', 'sí'].includes(String(usuario?.es_externo || '').trim().toLowerCase());
  }

  function etiquetaExternoPorUsuario(usuario) {
    return esUsuarioExternoGestion(usuario)
      ? '<span class="gestion-personal-external-badge" title="Usuario externo: no forma parte de la plantilla interna">Externo</span>'
      : '';
  }

  function esUsuarioReingresoGestion(usuario) {
    return ['1', 'true', 'si', 'sí'].includes(String(usuario?.tiene_reingreso || '').trim().toLowerCase());
  }

  function etiquetaReingresoPorUsuario(usuario) {
    return esUsuarioReingresoGestion(usuario)
      ? '<span class="gestion-personal-reingreso-badge" title="Usuario con historial de reingreso">Reingreso</span>'
      : '';
  }

  function nombreDepartamentoSeguro(nombreDepartamento, fallback = 'Sin departamento') {
    return nombreDepartamento || fallback;
  }

  function actualizarTabla(datos) {
    // Si estamos usando DataTables
    const tabla = $('#historialUsuarios').DataTable();

    if (!tabla) {
      console.warn(' DataTable no inicializado');
      return;
    }

    // Mapear datos con soporte para múltiples puestos - VISUALIZACIÃ“N MEJORADA
    const datosFormateados = datos.map(p => {
      const nombreCompleto = [p.nombres, p.segundo_nombre, p.apellidop, p.apellidom].filter(x => x).join(' ');
      const tienePuestos = p.puestos && p.puestos.length > 1;
      const vistaMultiplesActiva = document.getElementById('FilterMultiplePuestos')?.value === 'multiples';
      const etiquetaExternoPersona = etiquetaExternoPorUsuario(p);
      const etiquetaReingresoPersona = etiquetaReingresoPorUsuario(p);
      const codigoContpacPersona = String(p.codigo_contpac || '').trim();
      const codigoContpacHTML = codigoContpacPersona
        ? `<span class="gestion-personal-code-value">${escapeAttr(codigoContpacPersona)}</span>`
        : '<span class="gestion-personal-code-value">Sin id</span>';
      const externalIdPersona = String(p.numero_empleado || '').trim();
      const puestosPersonaTexto = tienePuestos
        ? p.puestos.map(puesto => puesto.nombre_puesto || '').filter(Boolean).join(' | ')
        : (p.nombre_puesto || '');

      // Generar badges para múltiples puestos con JERARQUÍA VISUAL
      let puestosHTML = '';
      if (tienePuestos && vistaMultiplesActiva) {
        const puestoPrincipal = p.puestos[0] || {};
        puestosHTML = `
          <div class="d-flex flex-column align-items-start gap-1" style="max-width: 360px;">
            <small class="departamento-label mb-0">
              <i class="fa fa-building"></i>${nombreDepartamentoSeguro(puestoPrincipal.nombre_departamento || p.nombre_departamento)}
            </small>
            <span class="badge text-white d-inline-flex align-items-center gap-2 text-wrap text-start px-2 py-2 shadow-sm"
                  style="background-color: var(--bs-blue);"
                  title="Puesto principal: ${puestoPrincipal.nombre_puesto || p.nombre_puesto || 'Sin puesto'}">
              <i class="fa fa-briefcase"></i>
              <span>${puestoPrincipal.nombre_puesto || p.nombre_puesto || 'Sin puesto'}</span>
              <small class="fw-bold opacity-75">(Principal)</small>
            </span>
            <button class="btn btn-sm btn-outline-secondary py-0 px-2 fw-semibold" onclick="expandirPuestos(${p.id})" title="Ver todos los puestos">
              <i class="fa fa-layer-group me-1"></i>${p.puestos.length} puestos asignados
            </button>
          </div>
        `;
      } else if (tienePuestos) {
        const totalPuestos = p.puestos.length;
        const mostrarDirecto = totalPuestos <= 3;
        const puestosVisible = mostrarDirecto ? p.puestos : p.puestos.slice(0, 2);

        puestosHTML = '<div class="d-flex flex-column gap-2">';

        puestosVisible.forEach((puesto, index) => {
          const esPrincipal = index === 0;
          const iconoPuestoHtml = esPrincipal ? '<i class="fa fa-star"></i>' : '<i class="fa fa-thumbtack"></i>';
          const claseBadge = esPrincipal
            ? 'badge text-white badge-puesto-principal'
            : 'badge bg-success badge-puesto-secundario';

          puestosHTML += `
            <div class="d-flex flex-column" style="gap: 0.25rem;">
              <small class="departamento-label">
                <i class="fa fa-building"></i>${nombreDepartamentoSeguro(puesto.nombre_departamento)}
              </small>
              <span class="${claseBadge}"
                    style="${esPrincipal ? 'background-color: var(--bs-blue);' : ''}"
                    title="${esPrincipal ? 'Puesto Principal' : 'Puesto Secundario'}: ${puesto.nombre_puesto}">
                <span style="font-size: 0.9rem;">${iconoPuestoHtml}</span>
                <i class="fa fa-briefcase"></i>
                ${puesto.nombre_puesto}
                ${esPrincipal ? '<small class="ms-1" style="opacity: 0.8;">(Principal)</small>' : ''}
              </span>
            </div>
          `;
        });

        // Botón "ver más" si hay más de 3 puestos
        if (!mostrarDirecto) {
          const puestosRestantes = totalPuestos - 2;
          puestosHTML += `
            <button class="btn-ver-mas-puestos" onclick="expandirPuestos(${p.id})"
                    title="Ver ${puestosRestantes} puesto(s) más">
              <i class="fa fa-plus-circle me-1"></i>Ver ${puestosRestantes} más
            </button>
          `;
        }

        puestosHTML += '</div>';
      } else {
        puestosHTML = `
          <small class="text-muted d-flex align-items-center gap-1">
            <i class="fa fa-building"></i>
            ${nombreDepartamentoSeguro(p.nombre_departamento)}
          </small>
          <small class="text-muted d-flex align-items-center gap-1">
            <i class="fa fa-briefcase"></i>
            ${p.nombre_puesto || 'Sin puesto'}
          </small>
        `;
      }

      const codigoIsoPais = p.codigo_iso_pais || 'xx';
      const nombrePais = p.nombre_pais || 'Sin país';
      const sedeHTML = `
        <small class="d-inline-flex align-items-center gap-1 mt-1 px-2 py-1 sede-glass-badge" title="${nombrePais}"
               style="background: rgba(255,255,255,0.7); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); border: 1px solid rgba(0,0,0,0.06); border-radius: 6px;">
          <span class="text-muted fw-semibold" style="font-size: 0.75rem;">Sede:</span>
          <span class="fi fi-${codigoIsoPais} fis" style="font-size: 1.1rem; border-radius: 2px; box-shadow: 0 1px 3px rgba(0,0,0,0.15);"></span>
        </small>
      `;

      return {
        nombre: `
          <div class="gestion-personal-name-cell">
            ${avatarPersonaHtml(p, nombreCompleto)}
            <div class="gestion-personal-name-info">
              <div class="gestion-personal-employee-code">
                <span>No. empleado:</span>
                ${codigoContpacHTML}
                ${etiquetaExternoPersona}
                ${etiquetaReingresoPersona}
              </div>
              <div class="gestion-personal-name-main text-uppercase">
                ${nombreCompleto}
              </div>
              <div class="gestion-personal-external-id">
                <span>External id:</span>
                <strong>${escapeAttr(externalIdPersona || 'Sin id')}</strong>
              </div>
              <small class="gestion-personal-username d-flex align-items-center gap-1">
                <i class="fa fa-key"></i>
                ${p.usuario}
              </small>
              ${tienePuestos ? `<span class="badge bg-success badge-multipuesto-indicator mt-1"><i class="fa fa-layer-group me-1"></i>${p.puestos.length} Puestos Asignados</span>` : ''}
            </div>
          </div>
        `.trim(),
        departamento: `
          ${sedeHTML}
          ${puestosHTML}
          <hr class="my-2">
          <small class="text-muted d-flex align-items-center gap-1">
              <i class="fa fa-user"></i>Jefe: <strong class="ms-1">${p.nombre_jefe && p.nombre_jefe !== 'Sin jefe' ? p.nombre_jefe : (p.nombre_vacante_jefe || 'Sin jefe')}</strong>
          </small>
        `.trim(),
        acciones: (() => {
          const puedeEditar = !!window.puedeEditarTodos;
          const miUsuarioId = Number(window.miUsuarioId || 0);
          const mostrarEditar = puedeEditar;
          const mostrarVisualizar = !puedeEditar || miUsuarioId === 1;
          const puedePermisos = window.puedeGestionarPermisos;
          const puedeEditarRrhh = !!window.puedeEditarUsuarioRrhh;
          const puedeActualizarInfo = !!window.puedeActualizarInfo;
          const puedeCargarDocumento = !!window.puedeCargarDocumentoGestion;
          const puedeRegistrarAusencia = !!window.puedeRegistrarAusenciaGestion;
          const puedeDarBaja = !!window.puedeDarBajaGestion;
          return `
         <div class="d-flex flex-column align-items-start gap-1" style="min-width: fit-content;">
           <div class="d-flex flex-wrap gap-1">
              ${mostrarEditar
                ? `<button class="btn btn-sm btn-primary ${tienePuestos ? 'btn-with-indicator' : ''}" onclick="editar(${p.id})" title="${tienePuestos ? 'Editar usuario con ' + p.puestos.length + ' puestos asignados' : 'Editar usuario'}">
                  ${tienePuestos ? '<span class="indicator-multiples-puestos" title="' + p.puestos.length + ' puestos">' + p.puestos.length + '</span>' : ''}
                  <i class="fa fa-edit"></i>
              </button>`
                : ''
              }
              ${mostrarVisualizar
                ? `<button class="btn btn-sm btn-outline-secondary" onclick="visualizar(${p.id})" title="Visualizar">
                  <i class="fa fa-eye"></i>
              </button>`
                : ''
              }
               ${puedeCargarDocumento ? `<button class="btn btn-sm btn-info" onclick="cargarDocumentoPersona(this)" data-id-persona="${p.id}" data-nombre="${nombreCompleto.replace(/"/g, '&quot;')}" data-puesto="${escapeAttr(puestosPersonaTexto)}" title="Cargar documento">
                   <i class="fa fa-file"></i>
               </button>` : ''}
              ${puedeActualizarInfo ? `<button class="btn btn-sm btn-success" onclick="abrirActualizacionInfoPersona(${p.id})" title="Actualizar informaci&oacute;n" aria-label="Actualizar informaci&oacute;n">
                  <i class="fa fa-arrows-rotate"></i>
              </button>` : ''}
              ${puedeRegistrarAusencia ? `<button class="btn btn-sm btn-warning" onclick="registra_ausencia(${p.id})" title="Ausencias">
                  <i class="fa fa-person-circle-minus"></i>
              </button>` : ''}
             ${puedeDarBaja ? `<button class="btn btn-sm btn-danger" onclick="baja_gestor(${p.id})" title="Dar de baja">
                 <i class="fa fa-user-slash"></i>
             </button>` : ''}
             ${puedePermisos ? `<button class="btn btn-sm" style="background-color: #D2D755; color: white;" onclick="edit_perfil(${p.id})" title="${tienePuestos ? 'Permisos (Gestionar múltiples puestos)' : 'Permisos'}">
                 <i class="fa fa-lock" style="color: #007bff;"></i>
             </button>` : ''}
           </div>
              ${puedeEditarRrhh ? `<button class="btn btn-sm btn-info text-white d-inline-flex align-items-center justify-content-center gap-1 px-3" onclick="abrirEditarRrhh(${p.id})" title="Editar RR.HH.">
                  <i class="fa fa-user-pen"></i><span>Editar RR.HH.</span>
              </button>` : ''}
         </div>`;
        })()
      };
    });

    // Limpiar y recargar tabla
    tabla.clear().rows.add(datosFormateados).draw();
    if (tabla.columns && typeof tabla.columns.adjust === 'function') {
      tabla.columns.adjust();
    }
    if (tabla.responsive && typeof tabla.responsive.recalc === 'function') {
      tabla.responsive.recalc();
    }
    if (typeof window.precargarActualizacionInfoVisible === 'function') {
      window.precargarActualizacionInfoVisible(datos);
    }
  }

  /**
   * Eliminar usuario por completo del sistema (persona y datos relacionados).
   */
  function eliminarPersonaCompleto(idPersona, nombreMostrar) {
    if (!idPersona) return;
    const nombre = (nombreMostrar || 'este usuario').toString().replace(/</g, '');
    Swal.fire({
      title: '¿Eliminar del sistema?',
      html: 'Se eliminará por completo a <strong>' + nombre + '</strong>. No se podrá deshacer.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc3545',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
      if (!result.isConfirmed) return;
      fetch('/CapHum/eliminarPersonaCompleto', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idPersona: idPersona })
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            Swal.fire('Listo', data.mensaje || 'Usuario eliminado del sistema.', 'success');
            if (typeof llenarFiltros === 'function') llenarFiltros();
          } else {
            Swal.fire('Error', data.mensaje || 'No se pudo eliminar.', 'error');
          }
        })
        .catch(() => Swal.fire('Error', 'Error de conexión.', 'error'));
    });
  }

  /**
   * ==========================================
   * OBTENER COLOR PARA BADGE DE DEPARTAMENTO
   * ==========================================
   */
  function obtenerColorDepartamento(departamento) {
    const colores = {
      'Dirección Cobranza': 'linear-gradient(135deg, #6366f1, #8b5cf6)',
      'Sabuesos': 'linear-gradient(135deg, #10b981, #34d399)',
      'Auditoria Cobranza': 'linear-gradient(135deg, #f59e0b, #fbbf24)',
      'Call Center': 'linear-gradient(135deg, #3b82f6, #60a5fa)',
      'Recursos Humanos': 'linear-gradient(135deg, #ec4899, #f472b6)',
      'Sistemas': 'linear-gradient(135deg, #8b5cf6, #a78bfa)',
      'Contabilidad': 'linear-gradient(135deg, #14b8a6, #2dd4bf)'
    };

    return colores[departamento] || 'linear-gradient(135deg, #64748b, #94a3b8)';
  }

  /**
   * ==========================================
   * FEEDBACK VISUAL EN FILTROS
   * ==========================================
   * Añade clase visual cuando un filtro está activo
   */
  function aplicarFeedbackVisualFiltro(selectElement) {
    if (selectElement.value) {
      selectElement.classList.add('filter-active');
    } else {
      selectElement.classList.remove('filter-active');
    }
  }

  /**
   * ==========================================
   * INICIALIZAR FEEDBACK VISUAL EN FILTROS
   * ==========================================
   */
  function inicializarFeedbackFiltros() {
    const filtros = ['gestionEmpresaSelect', 'UserDireccion', 'UserArea', 'UserRole', 'UserPlan', 'FilterMultiplePuestos'];
    filtros.forEach(id => {
      const filtro = document.getElementById(id);
      if (filtro && filtro.value) {
        filtro.classList.add('filter-active');
      }
    });
  }

  // ==========================================
  // EJECUTAR AL CARGAR LA PÁGINA
  // ==========================================
  document.addEventListener('DOMContentLoaded', () => {
    if (esVistaBajasActual()) return;

    // Esperar a que DataTable esté listo
    setTimeout(() => {
      llenarFiltros();
      inicializarFeedbackFiltros();
    }, 800);
  });

  /**
   * ==========================================
   * SELECT CON BÃšSQUEDA EN TIEMPO REAL
   * ==========================================
   * Convierte un select normal en un select con búsqueda
   */
  class SearchableSelect {
    constructor(selectElement) {
      this.select = selectElement;
      this.options = [];
      this.selectedValue = '';
      this.isOpen = false;

      this.init();
    }

    init() {
      // Crear el wrapper
      this.wrapper = document.createElement('div');
      this.wrapper.className = 'select-search-wrapper';

      // Insertar wrapper antes del select
      this.select.parentNode.insertBefore(this.wrapper, this.select);

      // Mover el select dentro del wrapper
      this.wrapper.appendChild(this.select);

      // Crear el display
      this.display = document.createElement('div');
      this.display.className = 'select-search-display';
      this.display.textContent = this.select.options[this.select.selectedIndex]?.text || 'Seleccione una opción';
      this.wrapper.appendChild(this.display);

      // Crear el dropdown
      this.dropdown = document.createElement('div');
      this.dropdown.className = 'select-search-dropdown';
      this.wrapper.appendChild(this.dropdown);

      // Crear el input de búsqueda
      this.searchInput = document.createElement('input');
      this.searchInput.type = 'text';
      this.searchInput.className = 'select-search-input';
      this.searchInput.placeholder = 'Buscar...';
      this.dropdown.appendChild(this.searchInput);

      // Crear el contenedor de opciones
      this.optionsContainer = document.createElement('div');
      this.optionsContainer.className = 'select-search-options';
      this.dropdown.appendChild(this.optionsContainer);

      // Cargar opciones iniciales
      this.loadOptions();

      // Eventos
      this.attachEvents();
    }

    loadOptions() {
      this.options = [];
      Array.from(this.select.options).forEach(option => {
        if (option.value !== '') {
          this.options.push({
            value: option.value,
            text: option.text
          });
        }
      });
      this.renderOptions(this.options);
    }

    renderOptions(filteredOptions) {
      this.optionsContainer.innerHTML = '';

      if (filteredOptions.length === 0) {
        const noResults = document.createElement('div');
        noResults.className = 'select-search-option no-results';
        noResults.textContent = 'No se encontraron resultados';
        this.optionsContainer.appendChild(noResults);
        return;
      }

      filteredOptions.forEach(option => {
        const optionDiv = document.createElement('div');
        optionDiv.className = 'select-search-option';
        optionDiv.textContent = option.text;
        optionDiv.dataset.value = option.value;

        if (option.value === this.selectedValue) {
          optionDiv.classList.add('selected');
        }

        optionDiv.addEventListener('click', () => {
          this.selectOption(option);
        });

        this.optionsContainer.appendChild(optionDiv);
      });
    }

    selectOption(option) {
      this.selectedValue = option.value;
      this.select.value = option.value;
      this.display.textContent = option.text;

      // Disparar evento change en el select original
      const event = new Event('change', { bubbles: true });
      this.select.dispatchEvent(event);

      this.close();
    }

    open() {
      this.isOpen = true;
      this.dropdown.classList.add('show');
      this.searchInput.value = '';
      this.searchInput.focus();
      this.loadOptions();
    }

    close() {
      this.isOpen = false;
      this.dropdown.classList.remove('show');
      this.searchInput.value = '';
    }

    attachEvents() {
      // Click en display
      this.display.addEventListener('click', (e) => {
        e.stopPropagation();
        if (this.isOpen) {
          this.close();
        } else {
          this.open();
        }
      });

      // Input de búsqueda
      this.searchInput.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase().trim();
        const filtered = this.options.filter(option =>
          option.text.toLowerCase().includes(searchTerm)
        );
        this.renderOptions(filtered);
      });

      // Evitar que el click en el dropdown lo cierre
      this.dropdown.addEventListener('click', (e) => {
        e.stopPropagation();
      });

      // Cerrar al hacer click fuera
      document.addEventListener('click', () => {
        if (this.isOpen) {
          this.close();
        }
      });

      // Observer para detectar cambios en el select original
      const observer = new MutationObserver(() => {
        this.loadOptions();
        // Actualizar el display si cambió el valor seleccionado
        const selectedOption = this.select.options[this.select.selectedIndex];
        if (selectedOption) {
          this.display.textContent = selectedOption.text;
          this.selectedValue = selectedOption.value;
        }
      });

      observer.observe(this.select, {
        childList: true,
        subtree: true
      });
    }

    // Método para actualizar las opciones externamente
    refresh() {
      this.loadOptions();
      const selectedOption = this.select.options[this.select.selectedIndex];
      if (selectedOption) {
        this.display.textContent = selectedOption.text;
        this.selectedValue = selectedOption.value;
      } else {
        this.display.textContent = 'Seleccione una opción';
        this.selectedValue = '';
      }
    }
  }

  /**
   * ==========================================
   * INICIALIZAR SELECTS CON BÃšSQUEDA
   * ==========================================
   */
  // Nota: Los selects de esta vista usan Select2 vía .js-select-buscador.
  // Se desactiva la inicialización de SearchableSelect aquí para evitar
  // renderizado duplicado del combo (doble control visible).

  /**
   * ==========================================
   * GESTIÃ“N DE MÃšLTIPLES PUESTOS - FUNCIONES
   * ==========================================
   */

  // Variable global para almacenar los puestos del usuario actual
  let puestosUsuarioActual = [];
  let usuarioEditandoId = null;
  let todosLosDepartamentos = [];
  let todosLosPuestos = [];
  window.puestosUsuarioModificados = false;
  window.puestosEliminadosUsuario = [];
  window.puestoPrincipalOriginalUsuario = null;

  /**
   * Cargar puestos del usuario en el panel de edición
   */
  function cargarPuestosUsuario(usuarioId) {
    usuarioEditandoId = usuarioId;
    window.puestosUsuarioModificados = false;
    window.puestosEliminadosUsuario = [];
    window.puestoPrincipalOriginalUsuario = null;
    const usuario = usuariosData.find(u => u.id === usuarioId);

    if (!usuario) return;

    if (usuario.puestos && usuario.puestos.length > 0) {
      puestosUsuarioActual = JSON.parse(JSON.stringify(usuario.puestos)).filter(p => p && p.id_puesto);
    } else {
      puestosUsuarioActual = (usuario.id_puesto && usuario.id_departamento) ? [{
        id_puesto: usuario.id_puesto,
        nombre_puesto: usuario.nombre_puesto || 'Sin puesto',
        nombre_departamento: usuario.nombre_departamento || 'Sin departamento',
        id_departamento: usuario.id_departamento,
        id_area: usuario.id_area,
        nombre_area: usuario.nombre_area
      }] : [];
    }
    window.puestoPrincipalOriginalUsuario = puestosUsuarioActual.length > 0
      ? puestosUsuarioActual[0].id_puesto
      : null;
    mostrarAlertaMultiplesPuestos(puestosUsuarioActual.length > 1);
    mostrarContenedorPuestos(true);
    renderizarListaPuestos();
  }

  /**
   * Mostrar/ocultar alerta de múltiples puestos
   */
  function mostrarAlertaMultiplesPuestos(mostrar) {
    const alerta = document.getElementById('edit_alerta_multiples_puestos');
    if (alerta) {
      if (mostrar) {
        alerta.classList.remove('d-none');
      } else {
        alerta.classList.add('d-none');
      }
    }
  }

  /**
   * Mostrar/ocultar contenedor de gestión de puestos
   */
  function mostrarContenedorPuestos(mostrar) {
    const contenedor = document.getElementById('edit_contenedor_multiples_puestos');
    if (contenedor) {
      if (mostrar) {
        contenedor.classList.remove('d-none');
      } else {
        contenedor.classList.add('d-none');
      }
    }
  }

  /**
   * Renderizar la lista de puestos con opciones de gestión
   */
  function renderizarListaPuestos() {
    const listaPuestos = document.getElementById('edit_lista_puestos');
    if (!listaPuestos) return;

    listaPuestos.innerHTML = '';

    if (puestosUsuarioActual.length === 0) {
      listaPuestos.innerHTML = `
        <div class="no-puestos-message">
          <i class="fa fa-inbox"></i>
          <p class="mb-0">No hay puestos asignados</p>
        </div>
      `;
      return;
    }

    puestosUsuarioActual.forEach((puesto, index) => {
      const esPrincipal = index === 0;
      const puedeEliminar = puestosUsuarioActual.length > 0;

      const puestoItem = document.createElement('div');
      puestoItem.className = 'puesto-item mb-2';
      puestoItem.dataset.puestoIndex = index;

      puestoItem.innerHTML = `
        <div class="d-flex align-items-center justify-content-between w-100">
          <div class="flex-grow-1">
            <span class="puesto-item-badge">
              ${esPrincipal ? '<i class="fa fa-star"></i>' : ''}
              ${puesto.nombre_puesto}
            </span>
            <div class="mt-1">
              <small style="opacity: 0.9;">${puesto.nombre_departamento}</small>
            </div>
          </div>
          <div class="d-flex gap-1 ms-auto" style="margin-left: 1rem;">
            <button type="button" class="btn-editar-puesto" onclick="editarPuesto(${index})" title="Editar puesto">
              <i class="fa fa-pencil"></i>
            </button>
            <button type="button" class="btn-eliminar-puesto" onclick="confirmarEliminarPuesto(${index})" ${!puedeEliminar ? 'disabled' : ''} title="${puedeEliminar ? 'Eliminar puesto' : 'No puedes eliminar el último puesto'}">
              <i class="fa fa-trash"></i>
            </button>
          </div>
        </div>
      `;

      listaPuestos.appendChild(puestoItem);
    });
  }

  /**
   * Editar un puesto existente
   */
  function editarPuesto(index) {
    const puesto = puestosUsuarioActual[index];
    if (!puesto) return;

    // Guardar el índice que estamos editando
    window.puestoEditandoIndex = index;

    // Abrir panel de edición
    const panel = document.getElementById('edit_panel_editar_puesto');
    if (!panel) return;

    panel.classList.remove('d-none');

    // Cargar datos actuales
    cargarDepartamentosParaEditar();

    // Esperar a que se carguen los departamentos y luego seleccionar
    setTimeout(() => {
      const selectDept = document.getElementById('edit_editar_departamento');
      if (selectDept) {
        // Buscar el option por el nombre del departamento
        for (let i = 0; i < selectDept.options.length; i++) {
          if (selectDept.options[i].dataset.nombre === puesto.nombre_departamento) {
            selectDept.value = selectDept.options[i].value;
            break;
          }
        }
        // Cargar puestos del departamento desde backend y preseleccionar el puesto actual
        cargarPuestosParaEditar(puesto.id_puesto);
      }
    }, 300);
  }

  /**
   * Confirmar eliminación de puesto
   */
  function confirmarEliminarPuesto(index) {
    const puesto = puestosUsuarioActual[index];
    const esPrincipal = index === 0;
    const quedaraSinPuesto = puestosUsuarioActual.length === 1;

    Swal.fire({
      title: '¿Eliminar este puesto?',
      html: `
        <div class="text-start">
          <p><strong>Departamento:</strong> ${puesto.nombre_departamento}</p>
          <p><strong>Puesto:</strong> ${puesto.nombre_puesto}</p>
          ${quedaraSinPuesto ? '<p class="text-danger mt-3"><i class="fa fa-exclamation-triangle me-1"></i><strong>La persona quedara sin puesto asignado.</strong></p>' : ''}
          ${esPrincipal && !quedaraSinPuesto ? '<p class="text-danger mt-3"><i class="fa fa-exclamation-triangle me-1"></i><strong>Este es el puesto principal.</strong> Al eliminarlo, el siguiente puesto se convertira en principal.</p>' : ''}
        </div>
      `,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        eliminarPuesto(index);
      }
    });
  }

  /**
   * Eliminar puesto
   */
  function eliminarPuesto(index) {
    const puestoEliminado = puestosUsuarioActual.splice(index, 1)[0];
    const eliminoPrincipal = index === 0;
    window.puestosUsuarioModificados = true;
    if (puestoEliminado && puestoEliminado.id_puesto) {
      window.puestosEliminadosUsuario = Array.isArray(window.puestosEliminadosUsuario)
        ? window.puestosEliminadosUsuario
        : [];
      window.puestosEliminadosUsuario.push({
        id_puesto: puestoEliminado.id_puesto,
        id_departamento: puestoEliminado.id_departamento || puestoEliminado.departamento_id || '',
        era_principal: eliminoPrincipal
      });
    }

    if (eliminoPrincipal && puestosUsuarioActual.length > 0) {
      actualizarPuestoPrincipalDesdeLista(puestosUsuarioActual[0]);
    } else if (puestosUsuarioActual.length === 0) {
      limpiarPuestoPrincipalDesdeLista();
    }

    // Si queda solo un puesto, ocultar el panel de gestión
    mostrarAlertaMultiplesPuestos(puestosUsuarioActual.length > 1);
    mostrarContenedorPuestos(true);
    renderizarListaPuestos();

    if (typeof Swal !== 'undefined') {
      Swal.fire({
        icon: 'success',
        title: 'Puesto eliminado de la lista',
        text: 'Guarda los cambios para aplicarlo al usuario.',
        timer: 1800,
        showConfirmButton: false
      });
    }
  }

  function actualizarPuestoPrincipalDesdeLista(puesto) {
    if (!puesto) return;

    const selDepto = document.getElementById('edit_departamento_id');
    const selPuesto = document.getElementById('edit_id_puesto');
    let idDepartamento = puesto.id_departamento || puesto.departamento_id || '';
    const idPuesto = puesto.id_puesto || puesto.puesto_id || '';

    if (!idDepartamento && selDepto && puesto.nombre_departamento) {
      for (let i = 0; i < selDepto.options.length; i++) {
        if ((selDepto.options[i].textContent || '').trim() === String(puesto.nombre_departamento).trim()) {
          idDepartamento = selDepto.options[i].value;
          break;
        }
      }
    }

    if (!idDepartamento || !idPuesto) return;

    if (selDepto) {
      selDepto.value = String(idDepartamento);
      if (typeof window.refreshSelectBuscador === 'function') {
        window.refreshSelectBuscador('edit_departamento_id');
      }
    }

    if (typeof cargarPuestosCombo === 'function') {
      cargarPuestosCombo(idDepartamento, idPuesto);
    } else if (selPuesto) {
      selPuesto.value = String(idPuesto);
      if (typeof window.refreshSelectBuscador === 'function') {
        window.refreshSelectBuscador('edit_id_puesto');
      }
    }

    if (typeof cargarComboJefeDirecto === 'function') {
      cargarComboJefeDirecto(idDepartamento, null, idPuesto);
    }
  }

  function limpiarPuestoPrincipalDesdeLista() {
    const selDepto = document.getElementById('edit_departamento_id');
    const selPuesto = document.getElementById('edit_id_puesto');

    if (selDepto) {
      selDepto.value = '';
      if (typeof window.refreshSelectBuscador === 'function') {
        window.refreshSelectBuscador('edit_departamento_id');
      }
    }

    if (selPuesto) {
      selPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';
      selPuesto.value = '';
      if (typeof window.refreshSelectBuscador === 'function') {
        window.refreshSelectBuscador('edit_id_puesto');
      }
    }
  }

  /**
   * Mostrar panel para agregar nuevo puesto
   */
  function mostrarAgregarPuesto() {
    const panel = document.getElementById('edit_panel_agregar_puesto');
    if (!panel) return;

    panel.classList.remove('d-none');
    cargarDepartamentosParaAgregar();

    // Limpiar selects
    document.getElementById('edit_nuevo_departamento').value = '';
    document.getElementById('edit_nuevo_puesto').value = '';
  }

  /**
   * Cancelar agregar puesto
   */
  function cancelarAgregarPuesto() {
    const panel = document.getElementById('edit_panel_agregar_puesto');
    if (!panel) return;

    panel.classList.add('d-none');

    // Limpiar selects
    document.getElementById('edit_nuevo_departamento').value = '';
    document.getElementById('edit_nuevo_puesto').value = '';
    document.getElementById('edit_nuevo_jefe').value = '';
  }

  /**
   * Cancelar editar puesto
   */
  function cancelarEditarPuesto() {
    const panel = document.getElementById('edit_panel_editar_puesto');
    if (!panel) return;

    panel.classList.add('d-none');

    // Limpiar selects
    document.getElementById('edit_editar_departamento').value = '';
    document.getElementById('edit_editar_puesto').value = '';
    document.getElementById('edit_editar_jefe').value = '';
    window.puestoEditandoIndex = null;
  }

  /**
   * Cargar departamentos en el select para agregar puesto.
   * Solo departamentos a los que el usuario en sesión tiene acceso (privilegios_departamento).
   */
  function obtenerDepartamentosCobranzaEditarUsuario() {
    const departamentosPermitidos = Array.isArray(window.todosDepartamentosBackend)
      ? window.todosDepartamentosBackend
      : [];
    const catalogoCobranza = Array.isArray(window.departamentosCobranzaEditarUsuarioBackend)
      ? window.departamentosCobranzaEditarUsuarioBackend
      : [];
    const idsPermitidos = new Set();

    departamentosPermitidos.forEach(dep => {
      if (!dep) return;
      const id = dep.id || dep.departamento_id;
      if (id) {
        idsPermitidos.add(String(id));
      }
    });

    return catalogoCobranza.filter(dep => {
      const id = dep.id || dep.departamento_id;
      return id && idsPermitidos.has(String(id));
    });
  }

  function llenarSelectDepartamentosCobranza(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    if (typeof window.jQuery !== 'undefined') {
      const $select = window.jQuery(select);
      if ($select.hasClass('select2-hidden-accessible')) {
        $select.select2('destroy');
      }
    }

    select.value = '';
    select.innerHTML = '<option value="">Seleccione un departamento</option>';
    const agregados = new Map();

    obtenerDepartamentosCobranzaEditarUsuario().forEach(d => {
      const id = d.id || d.departamento_id;
      const nombre = d.nombre || d.departamento_nombre || '';
      if (id && nombre) {
        agregados.set(Number(id), nombre);
      }
    });

    Array.from(agregados.entries()).sort((a, b) => a[1].localeCompare(b[1], 'es', { sensitivity: 'base' })).forEach(([id, nombre]) => {
      const option = document.createElement('option');
      option.value = id;
      option.textContent = nombre;
      option.dataset.nombre = nombre;
      select.appendChild(option);
    });

    if (window.refreshSelectBuscador) window.refreshSelectBuscador(selectId);
  }

  function cargarDepartamentosParaAgregar() {
    llenarSelectDepartamentosCobranza('edit_nuevo_departamento');
  }

  /**
   * Cargar puestos según departamento seleccionado (desde backend: todos los puestos del departamento).
   */
  function cargarPuestosParaAgregar() {
    const selectDept = document.getElementById('edit_nuevo_departamento');
    const selectPuesto = document.getElementById('edit_nuevo_puesto');

    if (!selectDept || !selectPuesto) return;

    const departamentoId = selectDept.value;

    // Limpiar select de puestos
    selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';

    if (!departamentoId) return;

    fetch('/CapHum/getPuestosParaGestor', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_departamento: departamentoId })
    })
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.datos)) return;
        data.datos.forEach(p => {
          const option = document.createElement('option');
          option.value = p.id;
          option.textContent = p.nombre || p.puesto_nombre || '';
          selectPuesto.appendChild(option);
        });
        if (window.refreshSelectBuscador) window.refreshSelectBuscador('edit_nuevo_puesto');
      })
      .catch(() => {
        const departamentoNombre = selectDept.options[selectDept.selectedIndex]?.dataset.nombre;
        const puestos = new Set();
        usuariosData.forEach(u => {
          if (u.puestos && u.puestos.length > 0) {
            u.puestos.forEach(puesto => {
              if (puesto.nombre_departamento === departamentoNombre && puesto.nombre_puesto && puesto.nombre_puesto !== 'Sin puesto') {
                puestos.add(JSON.stringify({ id: puesto.id_puesto, nombre: puesto.nombre_puesto }));
              }
            });
          } else if (u.nombre_departamento === departamentoNombre && u.nombre_puesto && u.nombre_puesto !== 'Sin puesto') {
            puestos.add(JSON.stringify({ id: u.id_puesto, nombre: u.nombre_puesto }));
          }
        });
        Array.from(puestos).forEach(puestoStr => {
          const puesto = JSON.parse(puestoStr);
          const option = document.createElement('option');
          option.value = puesto.id;
          option.textContent = puesto.nombre;
          selectPuesto.appendChild(option);
        });
        if (window.refreshSelectBuscador) window.refreshSelectBuscador('edit_nuevo_puesto');
      });
  }

  /**
   * Cargar jefes según departamento seleccionado para agregar
   */
  function cargarJefesParaAgregar() {
    const selectDept = document.getElementById('edit_nuevo_departamento');
    const selectJefe = document.getElementById('edit_nuevo_jefe');

    if (!selectDept || !selectJefe) return;

    const departamentoId = selectDept.value;
    const departamentoNombre = selectDept.options[selectDept.selectedIndex]?.dataset.nombre;

    // Limpiar select de jefes
    selectJefe.innerHTML = '<option value="">Seleccione un jefe</option>';

    if (!departamentoId) return;

    // Obtener jefes del mismo departamento
    const jefes = [];
    usuariosData.forEach(u => {
      if (u.nombre_departamento === departamentoNombre && u.id !== usuarioEditandoId) {
        jefes.push({
          id: u.id,
          nombre: `${u.nombres} ${u.apellidop || ''} ${u.apellidom || ''}`.trim()
        });
      }
    });

    // Ordenar por nombre
    jefes.sort((a, b) => a.nombre.localeCompare(b.nombre));

    // Agregar opciones
    jefes.forEach(jefe => {
      const option = document.createElement('option');
      option.value = jefe.id;
      option.textContent = jefe.nombre;
      selectJefe.appendChild(option);
    });
  }

  /**
   * Cargar departamentos para editar puesto.
   * Solo departamentos a los que el usuario en sesión tiene acceso (privilegios_departamento).
   */
  function cargarDepartamentosParaEditar() {
    llenarSelectDepartamentosCobranza('edit_editar_departamento');
  }

  /**
   * Cargar puestos para editar según departamento (desde backend: todos los puestos del departamento).
   * @param {string|number|null} puestoIdToSelect - Si se pasa, tras cargar se selecciona este id en el select.
   */
  function cargarPuestosParaEditar(puestoIdToSelect) {
    const selectDept = document.getElementById('edit_editar_departamento');
    const selectPuesto = document.getElementById('edit_editar_puesto');

    if (!selectDept || !selectPuesto) return;

    const departamentoId = selectDept.value;

    // Limpiar select de puestos
    selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';

    if (!departamentoId) return;

    // Cargar todos los puestos del departamento desde el backend (no solo los que tienen usuarios asignados)
    fetch('/CapHum/getPuestosParaGestor', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_departamento: departamentoId })
    })
      .then(res => res.json())
      .then(data => {
        if (!data.success || !Array.isArray(data.datos)) {
          return;
        }
        data.datos.forEach(p => {
          const option = document.createElement('option');
          option.value = p.id;
          option.textContent = p.nombre || p.puesto_nombre || '';
          selectPuesto.appendChild(option);
        });
        if (puestoIdToSelect != null && puestoIdToSelect !== '') {
          selectPuesto.value = String(puestoIdToSelect);
        }
        if (window.refreshSelectBuscador) window.refreshSelectBuscador('edit_editar_puesto');
      })
      .catch(() => {
        // Fallback: puestos que aparecen en usuariosData para este departamento
        const departamentoNombre = selectDept.options[selectDept.selectedIndex]?.dataset.nombre;
        const puestos = new Set();
        usuariosData.forEach(u => {
          if (u.puestos && u.puestos.length > 0) {
            u.puestos.forEach(puesto => {
              if (puesto.nombre_departamento === departamentoNombre && puesto.nombre_puesto && puesto.nombre_puesto !== 'Sin puesto') {
                puestos.add(JSON.stringify({ id: puesto.id_puesto, nombre: puesto.nombre_puesto }));
              }
            });
          } else if (u.nombre_departamento === departamentoNombre && u.nombre_puesto && u.nombre_puesto !== 'Sin puesto') {
            puestos.add(JSON.stringify({ id: u.id_puesto, nombre: u.nombre_puesto }));
          }
        });
        Array.from(puestos).forEach(puestoStr => {
          const puesto = JSON.parse(puestoStr);
          const option = document.createElement('option');
          option.value = puesto.id;
          option.textContent = puesto.nombre;
          selectPuesto.appendChild(option);
        });
        if (puestoIdToSelect != null && puestoIdToSelect !== '') {
          selectPuesto.value = String(puestoIdToSelect);
        }
        if (window.refreshSelectBuscador) window.refreshSelectBuscador('edit_editar_puesto');
      });
  }

  /**
   * Cargar jefes para editar según departamento
   */
  function cargarJefesParaEditar() {
    const selectDept = document.getElementById('edit_editar_departamento');
    const selectJefe = document.getElementById('edit_editar_jefe');

    if (!selectDept || !selectJefe) return;

    const departamentoId = selectDept.value;
    const departamentoNombre = selectDept.options[selectDept.selectedIndex]?.dataset.nombre;

    // Limpiar select de jefes
    selectJefe.innerHTML = '<option value="">Seleccione un jefe</option>';

    if (!departamentoId) return;

    // Obtener jefes del mismo departamento
    const jefes = [];
    usuariosData.forEach(u => {
      if (u.nombre_departamento === departamentoNombre && u.id !== usuarioEditandoId) {
        jefes.push({
          id: u.id,
          nombre: `${u.nombres} ${u.apellidop || ''} ${u.apellidom || ''}`.trim()
        });
      }
    });

    // Ordenar por nombre
    jefes.sort((a, b) => a.nombre.localeCompare(b.nombre));

    // Agregar opciones
    jefes.forEach(jefe => {
      const option = document.createElement('option');
      option.value = jefe.id;
      option.textContent = jefe.nombre;
      selectJefe.appendChild(option);
    });
  }

  /**
   * Guardar cambios de edición de puesto
   */
  function guardarEdicionPuesto() {
    const index = window.puestoEditandoIndex;
    if (index === null || index === undefined) return true;

    const selectDept = document.getElementById('edit_editar_departamento');
    const selectPuesto = document.getElementById('edit_editar_puesto');

    if (!selectDept || !selectPuesto) return false;

    const departamentoId = selectDept.value;
    const departamentoNombre = selectDept.options[selectDept.selectedIndex]?.dataset.nombre;
    const puestoId = selectPuesto.value;
    const puestoNombre = selectPuesto.options[selectPuesto.selectedIndex]?.text;

    // Validar
    if (!departamentoId || !puestoId) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos Incompletos',
        text: 'Selecciona un departamento y un puesto',
        confirmButtonColor: '#6366f1'
      });
      return false;
    }

    // Verificar si ya existe otro puesto con los mismos datos (excepto el que estamos editando)
    const yaExiste = puestosUsuarioActual.some((p, i) =>
      i !== index && p.id_puesto == puestoId && p.id_departamento == departamentoId
    );

    if (yaExiste) {
      Swal.fire({
        icon: 'info',
        title: 'Puesto Ya Asignado',
        text: 'Este puesto ya está asignado al usuario',
        confirmButtonColor: '#6366f1'
      });
      return false;
    }

    // Actualizar el puesto
    puestosUsuarioActual[index] = {
      id_puesto: puestoId,
      nombre_puesto: puestoNombre,
      nombre_departamento: departamentoNombre,
      id_departamento: departamentoId
    };
    window.puestosUsuarioModificados = true;
    if (index === 0) {
      actualizarPuestoPrincipalDesdeLista(puestosUsuarioActual[0]);
    }

    // Re-renderizar
    renderizarListaPuestos();
    cancelarEditarPuesto();
    return true;
  }

  /**
   * Agregar nuevo puesto a la lista
   */
  function agregarNuevoPuesto() {
    const selectDept = document.getElementById('edit_nuevo_departamento');
    const selectPuesto = document.getElementById('edit_nuevo_puesto');

    if (!selectDept || !selectPuesto) return;

    const departamentoId = selectDept.value;
    const departamentoNombre = selectDept.options[selectDept.selectedIndex]?.dataset.nombre;
    const puestoId = selectPuesto.value;
    const puestoNombre = selectPuesto.options[selectPuesto.selectedIndex]?.text;

    // Validar
    if (!departamentoId || !puestoId) {
      Swal.fire({
        icon: 'warning',
        title: 'Campos Incompletos',
        text: 'Selecciona un departamento y un puesto',
        confirmButtonColor: '#6366f1'
      });
      return;
    }

    // Verificar si ya existe
    const yaExiste = puestosUsuarioActual.some(p =>
      p.id_puesto == puestoId && p.id_departamento == departamentoId
    );

    if (yaExiste) {
      Swal.fire({
        icon: 'info',
        title: 'Puesto Ya Asignado',
        text: 'Este puesto ya está asignado al usuario',
        confirmButtonColor: '#6366f1'
      });
      return;
    }

    // Agregar nuevo puesto
    const nuevoPuesto = {
      id_puesto: puestoId,
      nombre_puesto: puestoNombre,
      nombre_departamento: departamentoNombre,
      id_departamento: departamentoId
    };

    puestosUsuarioActual.push(nuevoPuesto);
    window.puestosUsuarioModificados = true;
    if (puestosUsuarioActual.length === 1) {
      actualizarPuestoPrincipalDesdeLista(nuevoPuesto);
    }

    // Si ahora tiene más de 1 puesto, mostrar panel
    if (puestosUsuarioActual.length > 1) {
      mostrarAlertaMultiplesPuestos(true);
      mostrarContenedorPuestos(true);
    }

    // Re-renderizar
    renderizarListaPuestos();
    cancelarAgregarPuesto();
  }

  /**
   * Obtener los puestos actuales para guardar
   */
  function sincronizarPrincipalConListaPuestos() {
    const selPuesto = document.getElementById('edit_id_puesto');
    const selDepto = document.getElementById('edit_departamento_id');
    if (!selPuesto || !selDepto) return;
    const idPuesto = selPuesto.value;
    const idDepto = selDepto.value;
    const nombrePuesto = selPuesto.options[selPuesto.selectedIndex]?.textContent || '';
    const nombreDepto = selDepto.options[selDepto.selectedIndex]?.textContent || '';
    if (!idPuesto || !idDepto) return;
    const principal = { id_puesto: idPuesto, id_departamento: idDepto, nombre_puesto: nombrePuesto, nombre_departamento: nombreDepto };
    const principalOriginal = window.puestoPrincipalOriginalUsuario || '';
    const otros = (puestosUsuarioActual || []).slice(1).filter(p =>
      String(p.id_puesto) !== String(idPuesto)
      && (!principalOriginal || String(p.id_puesto) !== String(principalOriginal))
    );
    puestosUsuarioActual = [principal, ...otros];
    window.puestosUsuarioModificados = true;
    if (typeof renderizarListaPuestos === 'function') renderizarListaPuestos();
  }

  function obtenerPuestosParaGuardar() {
    const listaDesdeEstado = (puestosUsuarioActual || [])
      .filter(p => p && p.id_puesto)
      .map(p => ({
        id_puesto: p.id_puesto,
        id_departamento: p.id_departamento || p.departamento_id || ''
      }));

    const normalizarListaPuestos = (lista) => {
      const vistos = new Set();
      return (lista || []).filter(p => {
        const id = String(p.id_puesto);
        if (!id) return false;
        if (vistos.has(id)) return false;
        vistos.add(id);
        return true;
      });
    };

    const principalId = document.getElementById('edit_id_puesto') && document.getElementById('edit_id_puesto').value;
    const principalDepto = document.getElementById('edit_departamento_id') && document.getElementById('edit_departamento_id').value;
    const principal = principalId ? [{ id_puesto: principalId, id_departamento: principalDepto || '' }] : [];
    const principalOriginal = window.puestoPrincipalOriginalUsuario || '';
    const otros = (puestosUsuarioActual || []).slice(1)
      .filter(p =>
        String(p.id_puesto) !== String(principalId)
        && (!principalOriginal || String(p.id_puesto) !== String(principalOriginal))
      )
      .map(p => ({
        id_puesto: p.id_puesto,
        id_departamento: p.id_departamento || p.departamento_id || ''
      }));
    const lista = principal.length ? [...principal, ...otros] : otros;
    return normalizarListaPuestos(lista.length ? lista : listaDesdeEstado);
  }

  /**
   * ==========================================
   * FUNCIÃ“N DESCARGAR PLANTILLA GESTORES
   * ==========================================
   */
  function descargarPlantillaGestoresAnterior() {
    // Obtener filtros activos
    const direccion = document.getElementById('UserDireccion')?.value || '';
    const area = document.getElementById('UserArea')?.value || '';
    const departamento = document.getElementById('UserRole')?.value || '';
    const puesto = document.getElementById('UserPlan')?.value || '';
    const estatus = document.getElementById('FilterTransaction')?.value || '';

    // Generar mensaje dinámico según filtros
    let mensajeFiltros = 'Se descargará un archivo Excel con ';
    let detallesFiltros = [];

    if (direccion) detallesFiltros.push(`Dirección: <strong>${direccion}</strong>`);
    if (area) detallesFiltros.push(`Área: <strong>${area}</strong>`);
    if (departamento) detallesFiltros.push(`Departamento: <strong>${departamento}</strong>`);
    if (puesto) detallesFiltros.push(`Puesto: <strong>${puesto}</strong>`);
    if (estatus) detallesFiltros.push(`Estatus: <strong>${estatus}</strong>`);

    if (detallesFiltros.length > 0) {
      mensajeFiltros = 'Se descargará un archivo Excel filtrado por:<br><br>' + detallesFiltros.join('<br>');
    } else {
      mensajeFiltros = 'Se descargará un archivo Excel con <strong>TODOS los gestores</strong> del sistema.';
    }

    Swal.fire({
      html: `
        <div style="text-align: center;">
          <i class="bx bx-file-blank" style="font-size: 4rem; color: #0047bb;"></i>
          <h4 style="margin-top: 1rem; margin-bottom: 1rem;">Descargar Plantilla de Gestores</h4>
          <p style="color: #697a8d; margin-bottom: 1.5rem;">
            ${mensajeFiltros}
          </p>
          <div style="background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem;">
            <small style="color: #6c757d;">
              <i class="bx bx-info-circle me-1"></i>
              Incluye: número de empleado, nombres, departamento, puesto, jefe y estatus.
            </small>
          </div>
          <p style="margin-top: 1rem;"><strong>¿Deseas continuar con la descarga?</strong></p>
        </div>
      `,
      showCancelButton: true,
      confirmButtonText: '<i class="bx bx-download me-2"></i>Sí, descargar',
      cancelButtonText: 'Cancelar',
      buttonsStyling: false,
      customClass: {
        actions: 'gap-3',
        confirmButton: 'btn btn-primary px-4',
        cancelButton: 'btn btn-label-danger px-4'
      },
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Generando archivo Excel...',
          html: '<p style="margin-top: 1rem;">Por favor espera...</p>',
          allowOutsideClick: false,
          showConfirmButton: false,
          didOpen: () => Swal.showLoading()
        });

        // Crear formulario para descarga con filtros
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '/Reporteria/descargarPlantillaGestores';
        form.style.display = 'none';

        // Agregar filtros como inputs hidden
        if (departamento) {
          const inputDep = document.createElement('input');
          inputDep.type = 'hidden';
          inputDep.name = 'departamento';
          inputDep.value = departamento;
          form.appendChild(inputDep);
        }

        if (puesto) {
          const inputPuesto = document.createElement('input');
          inputPuesto.type = 'hidden';
          inputPuesto.name = 'puesto';
          inputPuesto.value = puesto;
          form.appendChild(inputPuesto);
        }

        if (estatus) {
          const inputEstatus = document.createElement('input');
          inputEstatus.type = 'hidden';
          inputEstatus.name = 'estatus';
          inputEstatus.value = estatus;
          form.appendChild(inputEstatus);
        }

        document.body.appendChild(form);
        form.submit();

        // Cerrar después de 3 segundos
        setTimeout(() => {
          Swal.close();
          Swal.fire({
            icon: 'success',
            title: '¡Descarga iniciada!',
            text: 'El archivo se está descargando.',
            timer: 3000,
            showConfirmButton: false
          });
        }, 3000);
      }
    });
  }

  /**
 * ==========================================
 * CASCADA GEOGRÁFICA: País â†’ Estado â†’ Municipio
 * ==========================================
 */

// Etiquetas dinámicas por país (agrega más según necesites)
const labelsPorPais = {
    1: { nivel1: 'Estado',      nivel2: 'Alcaldía / Municipio' }, // México
    2: { nivel1: 'Departamento', nivel2: 'Municipio' },           // Guatemala
    3: { nivel1: 'Departamento', nivel2: 'Municipio' },           // Colombia
};

/**
 * Cargar estados al cambiar país (formulario AGREGAR)
 * Nota: los selects con Select2 disparan change vía jQuery; addEventListener nativo
 * no siempre recibe el evento. Usar jQuery.on para la cascada geográfica.
 */
function resetPuestoJefeAddPorDepartamento() {
    const puesto = document.getElementById('add_id_puesto');
    const jefe = document.getElementById('add_id_jefe');
    if (puesto) {
        puesto.innerHTML = '<option value="">Seleccione un puesto</option>';
        puesto.disabled = true;
    }
    if (jefe) {
        jefe.innerHTML = '<option value="">Seleccione un jefe</option>';
        jefe.disabled = true;
    }
    if (typeof resetVacantesAsignablesAdd === 'function') {
        resetVacantesAsignablesAdd();
    }
    if (typeof window.refreshSelectBuscador === 'function') {
        window.refreshSelectBuscador('add_id_puesto');
        window.refreshSelectBuscador('add_id_jefe');
    }
}

function renderDepartamentosAddPorPais(idPais, codigoIsoPais) {
    const select = document.getElementById('add_departamento_id');
    if (!select) return;

    const departamentos = Array.isArray(window.todosDepartamentosBackend)
        ? window.todosDepartamentosBackend
        : [];
    const paisFiltro = String(idPais || '');
    const isoFiltro = String(codigoIsoPais || '').toLowerCase();

    select.innerHTML = '<option value="">Seleccione un departamento</option>';

    departamentos
        .filter(function (dep) {
            if (!paisFiltro && !isoFiltro) return true;

            const depIso = String(dep.codigo_iso_pais || '').toLowerCase();
            if (isoFiltro && depIso) {
                return depIso === isoFiltro;
            }

            const depPais = String(dep.id_pais || '');
            if (paisFiltro === '1' && depPais === '') return true;
            return depPais === paisFiltro;
        })
        .forEach(function (dep) {
            const id = dep.id || dep.departamento_id;
            const nombre = dep.nombre || dep.departamento_nombre || '';
            if (!id || !nombre) return;
            const option = document.createElement('option');
            option.value = id;
            option.textContent = nombre;
            option.dataset.pais = dep.id_pais || '';
            select.appendChild(option);
        });

    if (paisFiltro && select.options.length === 1) {
        const option = document.createElement('option');
        option.value = '';
        option.textContent = 'No hay departamentos disponibles para este pais';
        select.appendChild(option);
    }

    select.value = '';
    resetPuestoJefeAddPorDepartamento();
    if (typeof window.refreshSelectBuscador === 'function') {
        window.refreshSelectBuscador('add_departamento_id');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.jQuery === 'undefined') {
        return;
    }
    var $ = window.jQuery;
    precargarCatalogoMexicoGestion();

    $('#add_id_pais').on('change', function () {
        var idPais = this.value;
        var optPais = this.options[this.selectedIndex];
        var codigoIsoPais = optPais ? (optPais.dataset.iso || '') : '';
        resetCascadaAdd();
        renderDepartamentosAddPorPais(idPais, codigoIsoPais);
        actualizarLabels(idPais, 'add');

        if (!idPais) return;

        document.getElementById('div_add_estado').style.display = '';
        document.getElementById('add_id_div_nivel1').disabled = true;
        cargarEstados(idPais, 'add_id_div_nivel1', function () {
            document.getElementById('div_add_estado').style.display = '';
            document.getElementById('add_id_div_nivel1').disabled = false;
        });
    });

    $('#add_id_div_nivel1').on('change', function () {
        var idEstado = this.value;

        var selMun = document.getElementById('add_id_div_nivel2');
        selMun.innerHTML = '<option value="">Seleccione...</option>';
        selMun.disabled = true;
        document.getElementById('div_add_municipio').style.display = 'none';
        resetDomicilioNivel3EnAdelante('add');

        if (!idEstado) return;

        document.getElementById('div_add_municipio').style.display = '';
        selMun.disabled = true;
        cargarMunicipios(idEstado, 'add_id_div_nivel2', function () {
            document.getElementById('div_add_municipio').style.display = '';
            document.getElementById('add_id_div_nivel2').disabled = false;
        });
    });

    $('#add_id_div_nivel2').on('change', function () {
        resetDomicilioNivel3EnAdelante('add');
        var idMun = this.value;
        if (!idMun) return;
        cargarColonias(idMun, 'add', function (tiene) {
            if (!tiene) return;
            document.getElementById('div_add_colonia').style.display = '';
            document.getElementById('add_id_div_nivel3').disabled = false;
        });
    });

    $('#add_id_div_nivel3').on('change', function () {
        onColoniaChange('add');
    });

    $('#add_id_div_nivel4').on('change', function () {
        var txt = document.getElementById('add_domicilio_calle_texto');
        var opt = this.options[this.selectedIndex];
        if (txt && opt && opt.value) {
            txt.value = (opt.textContent || '').trim();
        }
    });

    // ===================================
    // EVENTO LISTENER PARA EDITAR USUARIO
    // ===================================
    $('#edit_id_div_nivel1').on('change', function () {
        var idEstado = this.value;

        var selMun = document.getElementById('edit_id_div_nivel2');
        selMun.innerHTML = '<option value="">Seleccione...</option>';
        selMun.disabled = true;
        document.getElementById('div_edit_municipio').style.display = 'none';
        resetDomicilioNivel3EnAdelante('edit');

        if (!idEstado) return;

        cargarMunicipios(idEstado, 'edit_id_div_nivel2', function () {
            document.getElementById('div_edit_municipio').style.display = '';
            document.getElementById('edit_id_div_nivel2').disabled = false;
        });
    });

    $('#edit_id_div_nivel2').on('change', function () {
        resetDomicilioNivel3EnAdelante('edit');
        var idMun = this.value;
        if (!idMun) return;
        cargarColonias(idMun, 'edit', function (tiene) {
            if (!tiene) return;
            document.getElementById('div_edit_colonia').style.display = '';
            document.getElementById('edit_id_div_nivel3').disabled = false;
        });
    });

    $('#edit_id_div_nivel3').on('change', function () {
        onColoniaChange('edit');
    });

    $('#edit_id_div_nivel4').on('change', function () {
        var txt = document.getElementById('edit_domicilio_calle_texto');
        var opt = this.options[this.selectedIndex];
        if (txt && opt && opt.value) {
            txt.value = (opt.textContent || '').trim();
        }
    });
});

/**
 * Resetear cascada del formulario agregar
 */
function resetCascadaAdd() {
    const selEstado = document.getElementById('add_id_div_nivel1');
    const selMun    = document.getElementById('add_id_div_nivel2');

    selEstado.innerHTML = '<option value="">Seleccione...</option>';
    selEstado.disabled  = true;
    selMun.innerHTML    = '<option value="">Seleccione...</option>';
    selMun.disabled     = true;

    document.getElementById('div_add_estado').style.display    = 'none';
    document.getElementById('div_add_municipio').style.display = 'none';
    resetDomicilioNivel3EnAdelante('add');

    if (window.refreshSelectBuscador) {
        window.refreshSelectBuscador('add_id_div_nivel1');
        window.refreshSelectBuscador('add_id_div_nivel2');
    }
}

function ocultarBloquesDomicilio(prefix) {
    ['colonia', 'calle', 'calle_texto', 'num_extint', 'cp'].forEach(function (k) {
        const el = document.getElementById('div_' + prefix + '_' + k);
        if (el) el.style.display = 'none';
    });
}

/**
 * Select2 con búsqueda en selects del offcanvas (domicilio, puesto, jefe, etc.).
 * jQuery + select2 deben existir (cargan en el layout después de este bloque).
 */
(function () {
    window.getDropdownParentSelectorForSelectId = function (selectId) {
        if (!selectId) return '#offcanvasEditUser';
        if (selectId.indexOf('add_') === 0) return '#offcanvasAddUser';
        if (selectId.indexOf('rrhh_') === 0) return '#modalAgregarUsuarioRrhh';
        if (selectId === 'bajaSustitutoId') return '#modalBajas';
        if (selectId === 'gestionEmpresaSelect' || selectId === 'UserDireccion' || selectId === 'UserArea' || selectId === 'UserRole' || selectId === 'UserPlan' || selectId === 'FilterMultiplePuestos') {
            return null;
        }
        return '#offcanvasEditUser';
    };

    window.refreshSelectBuscador = function (selectId) {
        var el = document.getElementById(selectId);
        if (!el || !el.classList.contains('js-select-buscador')) return;
        if (typeof window.jQuery === 'undefined' || !window.jQuery.fn.select2) return;
        var $ = window.jQuery;
        var $el = $('#' + selectId);
        var prev = el.value;
        if ($el.hasClass('select2-hidden-accessible')) {
            $el.select2('destroy');
        }
        // Select2 lee el estado disabled del <select>; forzar sincronía con el DOM nativo.
        $el.prop('disabled', !!el.disabled);
        var parentSel = window.getDropdownParentSelectorForSelectId(selectId);
        var $parent = parentSel ? $(parentSel) : $(document.body);
        if (parentSel && !$parent.length) {
            $parent = $(document.body);
        }
        $el.select2({
            width: '100%',
            dropdownParent: $parent,
            minimumResultsForSearch: 0,
            language: {
                noResults: function () { return 'Sin resultados'; },
                searching: function () { return 'Buscando...'; }
            }
        });
        $el.prop('disabled', !!el.disabled);
        if (prev) {
            $el.val(prev).trigger('change.select2');
        }
    };

    window.destruirSelectsBuscadorOffcanvas = function (offcanvasEl) {
        if (typeof window.jQuery === 'undefined') return;
        window.jQuery(offcanvasEl).find('select.js-select-buscador').each(function () {
            var $s = window.jQuery(this);
            if ($s.hasClass('select2-hidden-accessible')) {
                $s.select2('destroy');
            }
        });
    };

    window.refrescarSelectsBuscadorOffcanvas = function (offcanvasId) {
        var root = document.getElementById(offcanvasId);
        if (!root) return;
        root.querySelectorAll('select.js-select-buscador').forEach(function (sel) {
            if (sel.id) {
                window.refreshSelectBuscador(sel.id);
            }
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        var editOc = document.getElementById('offcanvasEditUser');
        var addOc = document.getElementById('offcanvasAddUser');
        function configurarOffcanvasAddPersistente() {
            if (!addOc || !window.bootstrap || !bootstrap.Offcanvas) return;
            return bootstrap.Offcanvas.getInstance(addOc) || bootstrap.Offcanvas.getOrCreateInstance(addOc, {
                backdrop: true,
                keyboard: true
            });
        }
        if (editOc) {
            editOc.addEventListener('shown.bs.offcanvas', function () {
                if (typeof window.refrescarSelectsBuscadorOffcanvas === 'function') {
                    window.refrescarSelectsBuscadorOffcanvas('offcanvasEditUser');
                }
            });
            editOc.addEventListener('hidden.bs.offcanvas', function () {
                if (typeof window.destruirSelectsBuscadorOffcanvas === 'function') {
                    window.destruirSelectsBuscadorOffcanvas(this);
                }
            });
        }
        if (addOc) {
            configurarOffcanvasAddPersistente();
            addOc.addEventListener('shown.bs.offcanvas', function () {
                configurarOffcanvasAddPersistente();
                if (typeof window.refrescarSelectsBuscadorOffcanvas === 'function') {
                    window.refrescarSelectsBuscadorOffcanvas('offcanvasAddUser');
                }
            });
            addOc.addEventListener('hidden.bs.offcanvas', function () {
                if (typeof window.destruirSelectsBuscadorOffcanvas === 'function') {
                    window.destruirSelectsBuscadorOffcanvas(this);
                }
            });
        }
    });
})();

function limpiarCamposDomicilioTexto(prefix) {
    const cl = document.getElementById(prefix + '_domicilio_calle_texto');
    const ne = document.getElementById(prefix + '_domicilio_num_exterior');
    const ni = document.getElementById(prefix + '_domicilio_num_interior');
    const cp = document.getElementById(prefix + '_codigo_postal');
    if (cl) cl.value = '';
    if (ne) ne.value = '';
    if (ni) ni.value = '';
    if (cp) cp.value = '';
}

function resetDomicilioNivel3EnAdelante(prefix) {
    const sel3 = document.getElementById(prefix + '_id_div_nivel3');
    const sel4 = document.getElementById(prefix + '_id_div_nivel4');
    if (sel3) {
        sel3.innerHTML = '<option value="">Seleccione...</option>';
        sel3.value = '';
        sel3.disabled = true;
    }
    if (sel4) {
        sel4.innerHTML = '<option value="">Seleccione...</option>';
        sel4.value = '';
        sel4.disabled = true;
    }
    if (window.refreshSelectBuscador) {
        if (sel3 && sel3.id) window.refreshSelectBuscador(sel3.id);
        if (sel4 && sel4.id) window.refreshSelectBuscador(sel4.id);
    }
    ocultarBloquesDomicilio(prefix);
    limpiarCamposDomicilioTexto(prefix);
}

function aplicarCpDesdeColoniaSelect(prefix) {
    const sel = document.getElementById(prefix + '_id_div_nivel3');
    const cpIn = document.getElementById(prefix + '_codigo_postal');
    if (!sel || !cpIn) return;
    const opt = sel.options[sel.selectedIndex];
    const v = opt && opt.dataset && opt.dataset.codigoPostal ? opt.dataset.codigoPostal : '';
    cpIn.value = v || '';
}

function onColoniaChange(prefix) {
    const sel = document.getElementById(prefix + '_id_div_nivel3');
    const calleSel = document.getElementById(prefix + '_id_div_nivel4');
    const calleTxt = document.getElementById(prefix + '_domicilio_calle_texto');
    if (!sel || !calleSel) return;
    const idCol = sel.value;
    calleSel.innerHTML = '<option value="">Seleccione...</option>';
    calleSel.disabled = true;
    document.getElementById('div_' + prefix + '_calle').style.display = 'none';
    document.getElementById('div_' + prefix + '_calle_texto').style.display = 'none';
    document.getElementById('div_' + prefix + '_num_extint').style.display = 'none';
    document.getElementById('div_' + prefix + '_cp').style.display = 'none';
    if (!idCol) {
        limpiarCamposDomicilioTexto(prefix);
        return;
    }
    aplicarCpDesdeColoniaSelect(prefix);
    document.getElementById('div_' + prefix + '_cp').style.display = '';
    document.getElementById('div_' + prefix + '_calle_texto').style.display = '';
    if (calleTxt && !calleTxt.value) calleTxt.value = '';
    cargarCalles(idCol, prefix, function (tieneCalles) {
        document.getElementById('div_' + prefix + '_calle').style.display = tieneCalles ? '' : 'none';
        calleSel.disabled = !tieneCalles;
        if (!tieneCalles && calleTxt) {
            calleTxt.focus();
        }
        document.getElementById('div_' + prefix + '_num_extint').style.display = '';
    });
}

function cargarColonias(idMunicipio, prefix, onSuccess) {
    const selectId = prefix + '_id_div_nivel3';
    const select = document.getElementById(selectId);
    if (!select) {
        if (onSuccess) onSuccess(false);
        return;
    }
    select.innerHTML = '<option value="">Cargando...</option>';
    select.disabled = true;

    fetch('/CapHum/getColonias', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_municipio: idMunicipio })
    })
    .then(r => r.json())
    .then(data => {
        select.innerHTML = '<option value="">Seleccione...</option>';
        const tiene = data.success && data.datos && data.datos.length > 0;
        if (tiene) {
            data.datos.forEach(function (c) {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.nombre;
                const cp = (c.codigo_postal || c.codigo_interno || '').toString().trim();
                if (cp) opt.setAttribute('data-codigo-postal', cp);
                select.appendChild(opt);
            });
        }
        document.getElementById('div_' + prefix + '_colonia').style.display = tiene ? '' : 'none';
        select.disabled = !tiene;
        if (onSuccess) onSuccess(tiene);
        if (window.refreshSelectBuscador) {
            window.refreshSelectBuscador(selectId);
        }
    })
    .catch(function () {
        select.innerHTML = '<option value="">Error al cargar</option>';
        document.getElementById('div_' + prefix + '_colonia').style.display = 'none';
        if (onSuccess) onSuccess(false);
    });
}

function cargarCalles(idColonia, prefix, onSuccess) {
    const selectId = prefix + '_id_div_nivel4';
    const select = document.getElementById(selectId);
    if (!select) {
        if (onSuccess) onSuccess(false);
        return;
    }
    select.innerHTML = '<option value="">Cargando...</option>';
    select.disabled = true;

    fetch('/CapHum/getCalles', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_colonia: idColonia })
    })
    .then(r => r.json())
    .then(data => {
        select.innerHTML = '<option value="">Seleccione...</option>';
        const tiene = data.success && data.datos && data.datos.length > 0;
        if (tiene) {
            data.datos.forEach(function (c) {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.nombre;
                select.appendChild(opt);
            });
        }
        if (onSuccess) onSuccess(tiene);
        if (window.refreshSelectBuscador) {
            window.refreshSelectBuscador(selectId);
        }
    })
    .catch(function () {
        select.innerHTML = '<option value="">Error al cargar</option>';
        if (onSuccess) onSuccess(false);
    });
}

/**
 * Actualizar etiquetas según país
 */
function actualizarLabels(idPais, prefix) {
    const labels = labelsPorPais[idPais] || { nivel1: 'Estado', nivel2: 'Municipio' };
    const labelN1 = document.getElementById(`label_${prefix}_estado`);
    const labelN2 = document.getElementById(`label_${prefix}_municipio`);
    if (labelN1) labelN1.textContent = labels.nivel1 + ' *';
    if (labelN2) labelN2.textContent = labels.nivel2 + ' *';
}

/**
 * Cargar estados via fetch
 */
const cacheEstadosPorPais = window.cacheEstadosPorPais || (window.cacheEstadosPorPais = {});
const cacheMunicipiosPorEstado = window.cacheMunicipiosPorEstado || (window.cacheMunicipiosPorEstado = {});
window.catalogoMexicoGestion = window.catalogoMexicoGestion || null;
window.catalogoMexicoGestionPromise = window.catalogoMexicoGestionPromise || null;

function precargarCatalogoMexicoGestion() {
    if (window.catalogoMexicoGestion) {
        return Promise.resolve(window.catalogoMexicoGestion);
    }
    if (window.catalogoMexicoGestionPromise) {
        return window.catalogoMexicoGestionPromise;
    }

    window.catalogoMexicoGestionPromise = fetch('/CapHum/getEstadosMunicipiosMexico', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success || !data.datos) {
            throw new Error(data.mensaje || 'No se pudo precargar el catalogo de Mexico');
        }

        window.catalogoMexicoGestion = data.datos;
        cacheEstadosPorPais[1] = data.datos.estados || [];
        Object.entries(data.datos.municipios_por_estado || {}).forEach(([idEstado, municipios]) => {
            cacheMunicipiosPorEstado[idEstado] = municipios || [];
        });
        return window.catalogoMexicoGestion;
    })
    .catch(error => {
        console.warn('No se pudo precargar el catalogo de Mexico', error);
        window.catalogoMexicoGestionPromise = null;
        return null;
    });

    return window.catalogoMexicoGestionPromise;
}

function llenarSelectDivision(select, items, emptyLabel) {
    select.innerHTML = '<option value="">Seleccione...</option>';
    if (!items || !items.length) {
        select.innerHTML = `<option value="">${emptyLabel}</option>`;
        return;
    }

    items.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.nombre;
        select.appendChild(opt);
    });
}

function cargarEstados(idPais, selectId, onSuccess) {
    const select = document.getElementById(selectId);
    if (String(idPais) === '1' && !cacheEstadosPorPais[idPais]) {
        select.innerHTML = '<option value="">Cargando...</option>';
        precargarCatalogoMexicoGestion().then(() => cargarEstados(idPais, selectId, onSuccess));
        return;
    }

    if (cacheEstadosPorPais[idPais]) {
        llenarSelectDivision(select, cacheEstadosPorPais[idPais], 'Sin estados');
        if (onSuccess) onSuccess();
        if (window.refreshSelectBuscador) window.refreshSelectBuscador(selectId);
        return;
    }

    select.innerHTML = '<option value="">Cargando...</option>';

    fetch('/CapHum/getEstados', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_pais: idPais })
    })
    .then(r => r.json())
    .then(data => {
        const items = data.success && data.datos ? data.datos : [];
        cacheEstadosPorPais[idPais] = items;
        llenarSelectDivision(select, items, 'Sin estados');
        if (onSuccess) onSuccess();
        if (window.refreshSelectBuscador) {
            window.refreshSelectBuscador(selectId);
        }
    })
    .catch(() => {
        select.innerHTML = '<option value="">Error al cargar</option>';
    });
}

/**
 * Cargar municipios via fetch
 */
function cargarMunicipios(idEstado, selectId, onSuccess) {
    const select = document.getElementById(selectId);
    if (!cacheMunicipiosPorEstado[idEstado] && window.catalogoMexicoGestionPromise) {
        select.innerHTML = '<option value="">Cargando...</option>';
        window.catalogoMexicoGestionPromise.then(() => cargarMunicipios(idEstado, selectId, onSuccess));
        return;
    }

    if (cacheMunicipiosPorEstado[idEstado]) {
        llenarSelectDivision(select, cacheMunicipiosPorEstado[idEstado], 'Sin alcaldias / municipios');
        if (onSuccess) onSuccess();
        if (window.refreshSelectBuscador) window.refreshSelectBuscador(selectId);
        return;
    }

    select.innerHTML = '<option value="">Cargando...</option>';

    fetch('/CapHum/getMunicipios', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_estado: idEstado })
    })
    .then(r => r.json())
    .then(data => {
        const items = data.success && data.datos ? data.datos : [];
        cacheMunicipiosPorEstado[idEstado] = items;
        llenarSelectDivision(select, items, 'Sin alcaldias / municipios');
        if (onSuccess) onSuccess();
        if (window.refreshSelectBuscador) {
            window.refreshSelectBuscador(selectId);
        }
    })
    .catch(() => {
        select.innerHTML = '<option value="">Error al cargar</option>';
    });
}

/**
 * Precargar cascada en el offcanvas EDITAR
 * (llamar desde la función editar() después de poblar el formulario)
 */
function precargarCascadaEdit(idPais, idEstado, idMunicipio, idColonia, idCalle, calleTexto, numExt, numInt, codigoPostal) {
    idColonia = (idColonia !== undefined && idColonia !== null && String(idColonia) !== '') ? String(idColonia) : '';
    idCalle = (idCalle !== undefined && idCalle !== null && String(idCalle) !== '') ? String(idCalle) : '';
    calleTexto = (calleTexto || '').toString().trim();
    numExt = numExt || '';
    numInt = numInt || '';
    codigoPostal = (codigoPostal || '').toString().trim();

    if (!idPais) {
        document.getElementById('div_edit_estado').style.display    = 'none';
        document.getElementById('div_edit_municipio').style.display = 'none';
        resetDomicilioNivel3EnAdelante('edit');
        return;
    }

    actualizarLabels(idPais, 'edit');
    resetDomicilioNivel3EnAdelante('edit');

    cargarEstados(idPais, 'edit_id_div_nivel1', function () {
        document.getElementById('div_edit_estado').style.display = '';

        if (!idEstado) {
            return;
        }
        document.getElementById('edit_id_div_nivel1').value = idEstado;

        cargarMunicipios(idEstado, 'edit_id_div_nivel2', function () {
            document.getElementById('div_edit_municipio').style.display = '';
            if (!idMunicipio) {
                document.getElementById('div_edit_calle_texto').style.display = calleTexto ? '' : 'none';
                document.getElementById('edit_domicilio_calle_texto').value = calleTexto;
                document.getElementById('edit_domicilio_num_exterior').value = numExt;
                document.getElementById('edit_domicilio_num_interior').value = numInt;
                document.getElementById('edit_codigo_postal').value = codigoPostal;
                return;
            }
            document.getElementById('edit_id_div_nivel2').value = idMunicipio;

            cargarColonias(idMunicipio, 'edit', function (tieneColonias) {
                if (!tieneColonias) {
                    document.getElementById('div_edit_calle_texto').style.display = calleTexto ? '' : 'none';
                    document.getElementById('edit_domicilio_calle_texto').value = calleTexto;
                    document.getElementById('edit_domicilio_num_exterior').value = numExt;
                    document.getElementById('edit_domicilio_num_interior').value = numInt;
                    document.getElementById('edit_codigo_postal').value = codigoPostal;
                    return;
                }
                document.getElementById('div_edit_colonia').style.display = '';
                document.getElementById('edit_id_div_nivel3').disabled = false;
                if (idColonia) {
                    document.getElementById('edit_id_div_nivel3').value = idColonia;
                }
                aplicarCpDesdeColoniaSelect('edit');
                const idColSel = document.getElementById('edit_id_div_nivel3').value;
                if (!idColSel) {
                    document.getElementById('div_edit_calle_texto').style.display = calleTexto ? '' : 'none';
                    document.getElementById('edit_domicilio_calle_texto').value = calleTexto;
                    document.getElementById('edit_domicilio_num_exterior').value = numExt;
                    document.getElementById('edit_domicilio_num_interior').value = numInt;
                    document.getElementById('edit_codigo_postal').value = codigoPostal;
                    return;
                }
                document.getElementById('div_edit_cp').style.display = '';
                cargarCalles(idColSel, 'edit', function (tieneCalles) {
                    document.getElementById('div_edit_calle').style.display = tieneCalles ? '' : 'none';
                    const selCalle = document.getElementById('edit_id_div_nivel4');
                    if (selCalle) selCalle.disabled = !tieneCalles;
                    var textoFinal = calleTexto;
                    if (tieneCalles && selCalle) {
                        if (idCalle) {
                            selCalle.value = idCalle;
                            const opt = selCalle.options[selCalle.selectedIndex];
                            if (opt && opt.value) {
                                textoFinal = (opt.textContent || '').trim();
                            }
                        } else if (calleTexto) {
                            for (var ci = 0; ci < selCalle.options.length; ci++) {
                                var tn = (selCalle.options[ci].textContent || '').trim();
                                if (tn === calleTexto || tn.toUpperCase() === calleTexto.toUpperCase()) {
                                    selCalle.selectedIndex = ci;
                                    break;
                                }
                            }
                        }
                    }
                    document.getElementById('div_edit_calle_texto').style.display = '';
                    document.getElementById('edit_domicilio_calle_texto').value = textoFinal || calleTexto;
                    document.getElementById('div_edit_num_extint').style.display = '';
                    document.getElementById('edit_domicilio_num_exterior').value = numExt;
                    document.getElementById('edit_domicilio_num_interior').value = numInt;
                    if (codigoPostal) {
                        document.getElementById('edit_codigo_postal').value = codigoPostal;
                    } else {
                        aplicarCpDesdeColoniaSelect('edit');
                    }
                });
            });
        });
    });
}

/* ============================================================
   KPI PANEL v3 — Estándar | Visión | Mini-stat
   ============================================================ */
(function() {
    var CIRC         = 2 * Math.PI * 36; // 226.19
    var STORAGE_MODE = 'kpi_view_mode_gestores';
    var STORAGE_OPEN = 'kpi_panel_open_gestores';
    var DEFAULT_MODE = 'default';

    var kpiCurrentMode = localStorage.getItem(STORAGE_MODE) || DEFAULT_MODE;
    var kpiPanelOpen   = localStorage.getItem(STORAGE_OPEN) !== 'false';

    /* â”€â”€ Contador animado â”€â”€ */
    function kpiAnimateCounter(el, target, dur, delay) {
        if (!el) return;
        dur   = dur   || 900;
        delay = delay || 0;
        setTimeout(function() {
            var start = performance.now();
            var ease  = function(t) { return t < 0.5 ? 2*t*t : -1+(4-2*t)*t; };
            (function tick(now) {
                var p = Math.min((now - start) / dur, 1);
                el.textContent = Math.round(target * ease(p));
                if (p < 1) requestAnimationFrame(tick);
                else el.textContent = target;
            })(performance.now());
        }, delay);
    }

    /* â”€â”€ Barra â”€â”€ */
    function kpiAnimateBar(id, pct, delay) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.width = '0%';
        setTimeout(function() { el.style.width = pct + '%'; }, (delay || 0) + 80);
    }

    /* â”€â”€ Donut â”€â”€ */
    function kpiAnimateDonut(arcId, pct, delay) {
        var el = document.getElementById(arcId);
        if (!el) return;
        el.style.strokeDasharray = '0 ' + CIRC;
        setTimeout(function() {
            var filled = (pct / 100) * CIRC;
            el.style.strokeDasharray = filled + ' ' + (CIRC - filled);
        }, (delay || 0) + 100);
    }

    function kpiResetDonuts() {
        ['dep','puesto','total'].forEach(function(k) {
            var el = document.getElementById('kpi-arc-' + k);
            if (el) el.style.strokeDasharray = '0 ' + CIRC;
        });
    }

    /* â”€â”€ Tooltip donut â”€â”€ */
    var _kpiTooltip = null;
    function _getTooltip() {
        if (!_kpiTooltip) _kpiTooltip = document.getElementById('kpiDonutTooltip');
        return _kpiTooltip;
    }
    window.kpiShowTooltip = function(e, text) {
        var t = _getTooltip(); if (!t) return;
        t.textContent = text;
        t.classList.add('visible');
        window.kpiMoveTooltip(e);
    };
    window.kpiMoveTooltip = function(e) {
        var t = _getTooltip(); if (!t) return;
        t.style.left = (e.clientX - t.offsetWidth / 2) + 'px';
        t.style.top  = (e.clientY - t.offsetHeight - 14) + 'px';
    };
    window.kpiHideTooltip = function() {
        var t = _getTooltip(); if (t) t.classList.remove('visible');
    };

    /* â”€â”€ Revelar celdas â”€â”€ */
    function kpiRevealCells() {
        document.querySelectorAll('#kpiRowNew .kpi-cell').forEach(function(c, i) {
            c.style.opacity   = '0';
            c.style.transform = 'translateY(14px)';
            c.classList.remove('revealed', 'mode-swap');
            setTimeout(function() { c.classList.add('revealed'); }, i * 80);
        });
        kpiAnimateBar('kpi-bar-dep',    75,  80);
        kpiAnimateBar('kpi-bar-puesto', 55, 160);
        kpiAnimateBar('kpi-bar-total',  90, 240);
        if (kpiCurrentMode === 'vision') {
            kpiAnimateDonut('kpi-arc-dep',    75,  80);
            kpiAnimateDonut('kpi-arc-puesto', 55, 160);
            kpiAnimateDonut('kpi-arc-total',  90, 240);
        }
    }

    /* â”€â”€ Aplicar modo â”€â”€ */
    function kpiApplyMode(mode, animate) {
        var row = document.getElementById('kpiRowNew');
        if (!row) return;
        row.classList.remove('mode-default', 'mode-vision', 'mode-ministat');
        row.classList.add('mode-' + mode);

        ['default','vision','ministat'].forEach(function(m) {
            var btn = document.getElementById('vbtn-' + m);
            if (btn) btn.classList.toggle('active', m === mode);
        });

        document.querySelectorAll('#kpiRowNew .kpi-cell-title').forEach(function(el) {
            el.style.display = mode === 'ministat' ? 'block' : 'none';
        });

        if (animate) {
            document.querySelectorAll('#kpiRowNew .kpi-cell').forEach(function(c, i) {
                c.classList.remove('mode-swap');
                void c.offsetWidth;
                c.style.animationDelay = (i * 0.06) + 's';
                c.classList.add('mode-swap');
            });
            if (mode === 'default') {
                kpiAnimateBar('kpi-bar-dep',    75,  60);
                kpiAnimateBar('kpi-bar-puesto', 55, 120);
                kpiAnimateBar('kpi-bar-total',  90, 180);
            }
            if (mode === 'vision') {
                kpiResetDonuts();
                setTimeout(function() {
                    kpiAnimateDonut('kpi-arc-dep',    75,  60);
                    kpiAnimateDonut('kpi-arc-puesto', 55, 130);
                    kpiAnimateDonut('kpi-arc-total',  90, 200);
                }, 120);
            }
        }
    }

    /* â”€â”€ Público â”€â”€ */
    window.kpiSetMode = function(mode) {
        kpiCurrentMode = mode;
        localStorage.setItem(STORAGE_MODE, mode);
        kpiApplyMode(mode, true);
    };

    window.kpiTogglePanel = function() {
        kpiPanelOpen = !kpiPanelOpen;
        localStorage.setItem(STORAGE_OPEN, kpiPanelOpen);
        var panel    = document.getElementById('kpiCollapsible');
        var btn      = document.getElementById('kpiToggleBtn');
        var controls = document.getElementById('kpiViewControls');
        var sep      = document.getElementById('kpiViewControlsSep');
        if (panel)    panel.classList.toggle('open', kpiPanelOpen);
        if (btn)      btn.classList.toggle('open',   kpiPanelOpen);
        if (controls) controls.classList.toggle('kpi-vc-hidden', !kpiPanelOpen);
        if (sep)      sep.classList.toggle('kpi-sep-hidden',     !kpiPanelOpen);
        if (kpiPanelOpen) setTimeout(kpiRevealCells, 60);
    };

    window.kpiResetPrefs = function() {
        localStorage.removeItem(STORAGE_MODE);
        localStorage.removeItem(STORAGE_OPEN);
        location.reload();
    };

    /* â”€â”€ Actualizar valores (llamar desde el código existente) â”€â”€
       Reemplaza las asignaciones directas a kpi-departamentos,
       kpi-puestos y kpi-total-empleados con esta función.

       Ejemplo de uso:
         kpiUpdateValues({ dep: 12, puesto: 28, total: 147,
                           arcDep: 75, arcPuesto: 55, arcTotal: 90 });
    â”€â”€ */
    window.kpiUpdateValues = function(data) {
        var numDep    = document.getElementById('kpi-departamentos');
        var numPuesto = document.getElementById('kpi-puestos');
        var numTotal  = document.getElementById('kpi-total-empleados');
        var numRol1   = document.getElementById('kpi-rol1-numero');
        var numRol2   = document.getElementById('kpi-rol2-numero');

        if (numDep    && data.dep    !== undefined) kpiAnimateCounter(numDep,    data.dep,    600,  0);
        if (numPuesto && data.puesto !== undefined) kpiAnimateCounter(numPuesto, data.puesto, 600, 60);
        if (numTotal  && data.total  !== undefined) kpiAnimateCounter(numTotal,  data.total,  700, 120);
        if (numRol1   && data.rol1   !== undefined) kpiAnimateCounter(numRol1,  data.rol1,  600, 180);
        if (numRol2   && data.rol2   !== undefined) kpiAnimateCounter(numRol2,  data.rol2,  600, 240);

        // Actualizar labels de roles si se proporcionan
        if (data.rol1Label && document.getElementById('kpi-rol1-label')) {
            document.getElementById('kpi-rol1-label').textContent = data.rol1Label;
        }
        if (data.rol2Label && document.getElementById('kpi-rol2-label')) {
            document.getElementById('kpi-rol2-label').textContent = data.rol2Label;
        }

        // Reflejar en mini-stat
        var msDa  = document.getElementById('kpi-ms-dep-a');
        var msDb  = document.getElementById('kpi-ms-dep-b');
        var msPa  = document.getElementById('kpi-ms-puesto-a');
        var msPb  = document.getElementById('kpi-ms-puesto-b');
        var msMain   = document.getElementById('kpi-ms-total-main');
        var msIngr   = document.getElementById('kpi-ms-ingresos');
        if (msDa   && data.dep               !== undefined) msDa.textContent  = data.dep;
        if (msDb   && data.depSinJefe        !== undefined) msDb.textContent  = data.depSinJefe;
        if (msPa   && data.puesto            !== undefined) msPa.textContent  = data.puesto;
        if (msPb   && data.puestoCompartidos !== undefined) msPb.textContent  = data.puestoCompartidos;
        if (msMain && data.total             !== undefined) msMain.textContent = data.total;
        if (msIngr && data.ingresosEsteMes   !== undefined) msIngr.textContent = data.ingresosEsteMes;

        // % badges con semáforo
        var pctDepEl = document.getElementById('kpi-ms-pct-dep');
        var pctPstEl = document.getElementById('kpi-ms-pct-pst');
        var pctEmpEl = document.getElementById('kpi-ms-pct-emp');
        if (pctDepEl && data.dep !== undefined && data.depSinJefe !== undefined) {
            var _pDep = data.dep > 0 ? Math.round((data.depSinJefe / data.dep) * 100) : 0;
            pctDepEl.textContent = _pDep + '% sin jefe';
            pctDepEl.className = 'kpi-ms-pct ' + (data.depSinJefe === 0 ? 'ok' : data.depSinJefe <= 2 ? 'warn' : 'danger');
        }
        if (pctPstEl && data.puesto !== undefined && data.puestoCompartidos !== undefined) {
            var _pPst = data.puesto > 0 ? Math.round((data.puestoCompartidos / data.puesto) * 100) : 0;
            pctPstEl.textContent = _pPst + '% compartidos';
            pctPstEl.className = 'kpi-ms-pct';
        }
        if (pctEmpEl && data.pctIngresos !== undefined) {
            pctEmpEl.textContent = data.pctIngresos + '% del total';
            pctEmpEl.className = 'kpi-ms-pct kpi-stat-ingr';
        }

        // Reflejar en donut-stats
        var dvDepA = document.getElementById('kpi-dv-dep-a');
        var dvDepB = document.getElementById('kpi-dv-dep-b');
        var dvPstA = document.getElementById('kpi-dv-pst-a');
        var dvPstB = document.getElementById('kpi-dv-pst-b');
        var dvEmpA = document.getElementById('kpi-dv-emp-a');
        var dvEmpB = document.getElementById('kpi-dv-emp-b');
        if (dvDepA && data.dep               !== undefined) dvDepA.textContent = data.dep;
        if (dvDepB && data.depSinJefe        !== undefined) dvDepB.textContent = data.depSinJefe;
        if (dvPstA && data.puesto            !== undefined) dvPstA.textContent = data.puesto;
        if (dvPstB && data.puestoCompartidos !== undefined) dvPstB.textContent = data.puestoCompartidos;
        if (dvEmpA && data.total             !== undefined) dvEmpA.textContent = data.total;
        if (dvEmpB && data.ingresosEsteMes   !== undefined) dvEmpB.textContent = data.ingresosEsteMes;

        // Actualizar badges de tendencia en donuts
        var _trunc = function(s, n) { return s && s.length > n ? s.substring(0, n - 1) + 'â€¦' : (s || '—'); };
        var trendDep    = document.getElementById('kpi-trend-dep');
        var trendPuesto = document.getElementById('kpi-trend-puesto');
        var trendTotal  = document.getElementById('kpi-trend-total');
        if (trendDep && data.topDepNombre !== undefined) {
            trendDep.innerHTML = '<i class="bx bx-buildings"></i>' + _trunc(data.topDepNombre, 16);
            trendDep.title = data.topDepNombre;
        }
        if (trendPuesto && data.topPuestoNombre !== undefined) {
            trendPuesto.innerHTML = '<i class="bx bx-user-pin"></i>' + _trunc(data.topPuestoNombre, 16);
            trendPuesto.title = data.topPuestoNombre;
        }
        if (trendTotal && data.pctIngresos !== undefined) {
            trendTotal.innerHTML = '<i class="bx bx-trending-up"></i>' + data.pctIngresos + '%';
        }

        var bDep    = data.arcDep    !== undefined ? data.arcDep    : 75;
        var bPuesto = data.arcPuesto !== undefined ? data.arcPuesto : 55;
        var bTotal  = data.arcTotal  !== undefined ? data.arcTotal  : 90;
        var bRol1   = data.arcRol1   !== undefined ? data.arcRol1   : 65;
        var bRol2   = data.arcRol2   !== undefined ? data.arcRol2   : 45;

        kpiAnimateBar('kpi-bar-dep',    bDep,     0);
        kpiAnimateBar('kpi-bar-puesto', bPuesto, 60);
        kpiAnimateBar('kpi-bar-total',  bTotal,  120);

        if (kpiCurrentMode === 'vision') {
            kpiResetDonuts();
            setTimeout(function() {
                kpiAnimateDonut('kpi-arc-dep',    bDep,     0);
                kpiAnimateDonut('kpi-arc-puesto', bPuesto, 70);
                kpiAnimateDonut('kpi-arc-total',  bTotal,  140);
                if (data.rol1 !== undefined) kpiAnimateDonut('kpi-arc-rol1', bRol1, 210);
                if (data.rol2 !== undefined) kpiAnimateDonut('kpi-arc-rol2', bRol2, 280);
            }, 100);
        }
    };

    /* â”€â”€ Init â”€â”€ */
    document.addEventListener('DOMContentLoaded', function() {
        var panel    = document.getElementById('kpiCollapsible');
        var btn      = document.getElementById('kpiToggleBtn');
        var controls = document.getElementById('kpiViewControls');
        var sep      = document.getElementById('kpiViewControlsSep');
        if (panel) panel.classList.toggle('open', kpiPanelOpen);
        if (btn)   btn.classList.toggle('open',   kpiPanelOpen);
        if (controls) controls.classList.toggle('kpi-vc-hidden', !kpiPanelOpen);
        if (sep)      sep.classList.toggle('kpi-sep-hidden',     !kpiPanelOpen);
        kpiApplyMode(kpiCurrentMode, false);
        if (kpiPanelOpen) setTimeout(kpiRevealCells, 120);

        // Conectar celdas al modal de desglose existente
        document.querySelectorAll('#kpiRowNew .kpi-cell').forEach(function(cell) {
            cell.addEventListener('click', function(e) {
                // Ignorar clicks en botones internos
                if (e.target.closest('button, a, input, select')) return;
                var tipo = this.getAttribute('data-tipo');
                if (tipo && typeof abrirModalDesglose === 'function') abrirModalDesglose(tipo);
            });
        });
    });

})(); /* fin KPI panel v3 */

/* ============================================================
   KPI PANEL BAJAS v3 — Espejo del panel de Gestión para Bajas
   IDs sufijados con "B"; localStorage: kpi_*_bajas
   ============================================================ */
(function() {
    var CIRC         = 2 * Math.PI * 36;
    var STORAGE_MODE = 'kpi_view_mode_bajas';
    var STORAGE_OPEN = 'kpi_panel_open_bajas';
    var DEFAULT_MODE = 'default';

    var kpiCurrentModeB = localStorage.getItem(STORAGE_MODE) || DEFAULT_MODE;
    var kpiPanelOpenB   = localStorage.getItem(STORAGE_OPEN) !== 'false';

    function kpiAnimateCounterB(el, target, dur, delay) {
        if (!el) return;
        dur = dur || 900; delay = delay || 0;
        setTimeout(function() {
            var start = performance.now();
            var ease  = function(t) { return t < 0.5 ? 2*t*t : -1+(4-2*t)*t; };
            (function tick(now) {
                var p = Math.min((now - start) / dur, 1);
                el.textContent = Math.round(target * ease(p));
                if (p < 1) requestAnimationFrame(tick);
                else el.textContent = target;
            })(performance.now());
        }, delay);
    }

    function kpiAnimateBarB(id, pct, delay) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.width = '0%';
        setTimeout(function() { el.style.width = pct + '%'; }, (delay || 0) + 80);
    }

    function kpiAnimateDonutB(arcId, pct, delay) {
        var el = document.getElementById(arcId);
        if (!el) return;
        el.style.strokeDasharray = '0 ' + CIRC;
        setTimeout(function() {
            var filled = (pct / 100) * CIRC;
            el.style.strokeDasharray = filled + ' ' + (CIRC - filled);
        }, (delay || 0) + 100);
    }

    function kpiResetDonutsB() {
        ['b-total','b-dep','b-puesto'].forEach(function(k) {
            var el = document.getElementById('kpi-arc-' + k);
            if (el) el.style.strokeDasharray = '0 ' + CIRC;
        });
    }

    var _kpiTooltipB = null;
    function _getTooltipB() {
        if (!_kpiTooltipB) _kpiTooltipB = document.getElementById('kpiDonutTooltipB');
        return _kpiTooltipB;
    }
    window.kpiShowTooltipB = function(e, text) {
        var t = _getTooltipB(); if (!t) return;
        t.textContent = text; t.classList.add('visible');
        window.kpiMoveTooltipB(e);
    };
    window.kpiMoveTooltipB = function(e) {
        var t = _getTooltipB(); if (!t) return;
        t.style.left = (e.clientX - t.offsetWidth / 2) + 'px';
        t.style.top  = (e.clientY - t.offsetHeight - 14) + 'px';
    };
    window.kpiHideTooltipB = function() {
        var t = _getTooltipB(); if (t) t.classList.remove('visible');
    };

    function kpiRevealCellsB() {
        document.querySelectorAll('#kpiRowNewB .kpi-cell').forEach(function(c, i) {
            c.style.opacity   = '0';
            c.style.transform = 'translateY(14px)';
            c.classList.remove('revealed', 'mode-swap');
            setTimeout(function() { c.classList.add('revealed'); }, i * 80);
        });
        kpiAnimateBarB('kpi-bar-b-total',  80,   80);
        kpiAnimateBarB('kpi-bar-b-dep',    60,  160);
        kpiAnimateBarB('kpi-bar-b-puesto', 45,  240);
        if (kpiCurrentModeB === 'vision') {
            kpiAnimateDonutB('kpi-arc-b-total',  80,  80);
            kpiAnimateDonutB('kpi-arc-b-dep',    60, 160);
            kpiAnimateDonutB('kpi-arc-b-puesto', 45, 240);
        }
    }

    function kpiApplyModeB(mode, animate) {
        var row = document.getElementById('kpiRowNewB');
        if (!row) return;
        row.classList.remove('mode-default', 'mode-vision', 'mode-ministat');
        row.classList.add('mode-' + mode);
        ['default','vision','ministat'].forEach(function(m) {
            var btn = document.getElementById('vbtn-' + m + '-b');
            if (btn) btn.classList.toggle('active', m === mode);
        });
        document.querySelectorAll('#kpiRowNewB .kpi-cell-title').forEach(function(el) {
            el.style.display = mode === 'ministat' ? 'block' : 'none';
        });
        if (animate) {
            document.querySelectorAll('#kpiRowNewB .kpi-cell').forEach(function(c, i) {
                c.classList.remove('mode-swap'); void c.offsetWidth;
                c.style.animationDelay = (i * 0.06) + 's';
                c.classList.add('mode-swap');
            });
            if (mode === 'vision') {
                kpiResetDonutsB();
                setTimeout(function() {
                    kpiAnimateDonutB('kpi-arc-b-total',  80,  60);
                    kpiAnimateDonutB('kpi-arc-b-dep',    60, 130);
                    kpiAnimateDonutB('kpi-arc-b-puesto', 45, 200);
                }, 120);
            }
        }
    }

    window.kpiSetModeB = function(mode) {
        kpiCurrentModeB = mode;
        localStorage.setItem(STORAGE_MODE, mode);
        kpiApplyModeB(mode, true);
    };

    window.kpiTogglePanelB = function() {
        kpiPanelOpenB = !kpiPanelOpenB;
        localStorage.setItem(STORAGE_OPEN, kpiPanelOpenB);
        var panel    = document.getElementById('kpiCollapsibleB');
        var btn      = document.getElementById('kpiToggleBtnB');
        var controls = document.getElementById('kpiViewControlsB');
        var sep      = document.getElementById('kpiViewControlsSepB');
        if (panel)    panel.classList.toggle('open', kpiPanelOpenB);
        if (btn)      btn.classList.toggle('open',   kpiPanelOpenB);
        if (controls) controls.classList.toggle('kpi-vc-hidden', !kpiPanelOpenB);
        if (sep)      sep.classList.toggle('kpi-sep-hidden',     !kpiPanelOpenB);
        if (kpiPanelOpenB) setTimeout(kpiRevealCellsB, 60);
    };

    window.kpiResetPrefsB = function() {
        localStorage.removeItem(STORAGE_MODE);
        localStorage.removeItem(STORAGE_OPEN);
        location.reload();
    };

    window.kpiUpdateValuesBajas = function(data) {
        var _trunc = function(s, n) { return s && s.length > n ? s.substring(0, n-1) + '\u2026' : (s || '\u2014'); };

        // Números principales
        var elTotal  = document.getElementById('kpi-b-total');
        var elDep    = document.getElementById('kpi-b-dep');
        var elPuesto = document.getElementById('kpi-b-puesto');
        if (elTotal  && data.total  !== undefined) kpiAnimateCounterB(elTotal,  data.total,  600,  0);
        if (elDep    && data.dep    !== undefined) kpiAnimateCounterB(elDep,    data.dep,    600,  60);
        if (elPuesto && data.puesto !== undefined) kpiAnimateCounterB(elPuesto, data.puesto, 600, 120);

        // Mini-stat: Total Bajas
        var msTA = document.getElementById('kpi-ms-b-total-a');
        var msTB = document.getElementById('kpi-ms-b-total-b');
        var msTL = document.getElementById('kpi-ms-b-total-lbl');
        var msPT = document.getElementById('kpi-ms-pct-b-total');
        if (msTA && data.total    !== undefined) msTA.textContent = data.total;
        if (msTB && data.bajasRef !== undefined) msTB.textContent = data.bajasRef;
        if (msTL && data.refLabel !== undefined) msTL.textContent = data.refLabel;
        if (msPT && data.total !== undefined && data.bajasRef !== undefined) {
            var _p = data.total > 0 ? ((data.bajasRef / data.total) * 100).toFixed(1) : 0;
            msPT.textContent = _p + '%';
            msPT.className   = 'kpi-ms-pct ' + (_p > 30 ? 'danger' : _p > 10 ? 'warn' : 'ok');
        }

        // Mini-stat: Departamentos
        var msDA = document.getElementById('kpi-ms-b-dep-a');
        var msDB = document.getElementById('kpi-ms-b-dep-b');
        var msPD = document.getElementById('kpi-ms-pct-b-dep');
        if (msDA && data.dep        !== undefined) msDA.textContent = data.dep;
        if (msDB && data.topDepBajas !== undefined) msDB.textContent = data.topDepBajas;
        if (msPD && data.topDepNombre !== undefined) {
            msPD.textContent = _trunc(data.topDepNombre, 14);
            msPD.className   = 'kpi-ms-pct';
            msPD.title       = data.topDepNombre;
        }

        // Mini-stat: Puestos
        var msPA = document.getElementById('kpi-ms-b-puesto-a');
        var msPB = document.getElementById('kpi-ms-b-puesto-b');
        var msPP = document.getElementById('kpi-ms-pct-b-puesto');
        if (msPA && data.puesto         !== undefined) msPA.textContent = data.puesto;
        if (msPB && data.topPuestoBajas  !== undefined) msPB.textContent = data.topPuestoBajas;
        if (msPP && data.topPuestoNombre !== undefined) {
            msPP.textContent = _trunc(data.topPuestoNombre, 14);
            msPP.className   = 'kpi-ms-pct';
            msPP.title       = data.topPuestoNombre;
        }

        // Donut-stats: Total
        var dvTA = document.getElementById('kpi-dv-b-total-a');
        var dvTB = document.getElementById('kpi-dv-b-total-b');
        var dvTL = document.getElementById('kpi-dv-b-total-lbl');
        if (dvTA && data.total    !== undefined) dvTA.textContent = data.total;
        if (dvTB && data.bajasRef !== undefined) dvTB.textContent = data.bajasRef;
        if (dvTL && data.refLabel !== undefined) dvTL.textContent = data.refLabel;

        // Donut-stats: Departamentos
        var dvDA = document.getElementById('kpi-dv-b-dep-a');
        var dvDB = document.getElementById('kpi-dv-b-dep-b');
        if (dvDA && data.dep         !== undefined) dvDA.textContent = data.dep;
        if (dvDB && data.topDepBajas  !== undefined) dvDB.textContent = data.topDepBajas;

        // Donut-stats: Puestos
        var dvPA = document.getElementById('kpi-dv-b-puesto-a');
        var dvPB = document.getElementById('kpi-dv-b-puesto-b');
        if (dvPA && data.puesto         !== undefined) dvPA.textContent = data.puesto;
        if (dvPB && data.topPuestoBajas  !== undefined) dvPB.textContent = data.topPuestoBajas;

        // Línea de contexto bajo el encabezado (dep/puesto): nombre con más bajas
        var ctxDep    = document.getElementById('kpi-ctx-b-dep');
        var ctxPuesto = document.getElementById('kpi-ctx-b-puesto');
        if (ctxDep && data.topDepNombre !== undefined) {
            ctxDep.innerHTML =
                '<i class="bx bx-buildings"></i><span>' + _trunc(data.topDepNombre, 26) + '</span>';
            ctxDep.title = data.topDepNombre;
        }
        if (ctxPuesto && data.topPuestoNombre !== undefined) {
            ctxPuesto.innerHTML =
                '<i class="bx bx-briefcase"></i><span>' + _trunc(data.topPuestoNombre, 26) + '</span>';
            ctxPuesto.title = data.topPuestoNombre;
        }

        // Barras
        var bTotal  = data.arcTotal  !== undefined ? data.arcTotal  : 80;
        var bDep    = data.arcDep    !== undefined ? data.arcDep    : 60;
        var bPuesto = data.arcPuesto !== undefined ? data.arcPuesto : 45;
        kpiAnimateBarB('kpi-bar-b-total',  bTotal,   0);
        kpiAnimateBarB('kpi-bar-b-dep',    bDep,    60);
        kpiAnimateBarB('kpi-bar-b-puesto', bPuesto, 120);

        // Donuts (solo si está en modo vision)
        if (kpiCurrentModeB === 'vision') {
            kpiResetDonutsB();
            setTimeout(function() {
                kpiAnimateDonutB('kpi-arc-b-total',  bTotal,    0);
                kpiAnimateDonutB('kpi-arc-b-dep',    bDep,    70);
                kpiAnimateDonutB('kpi-arc-b-puesto', bPuesto, 140);
            }, 100);
        }
    };

    window.kpiRevealCellsB = kpiRevealCellsB;

    /* â”€â”€ Init â”€â”€ */
    document.addEventListener('DOMContentLoaded', function() {
        // Restaurar estado open/closed del panel (idéntico al panel de Gestión)
        var panel    = document.getElementById('kpiCollapsibleB');
        var btn      = document.getElementById('kpiToggleBtnB');
        var controls = document.getElementById('kpiViewControlsB');
        var sep      = document.getElementById('kpiViewControlsSepB');
        if (panel)    panel.classList.toggle('open', kpiPanelOpenB);
        if (btn)      btn.classList.toggle('open',   kpiPanelOpenB);
        if (controls) controls.classList.toggle('kpi-vc-hidden', !kpiPanelOpenB);
        if (sep)      sep.classList.toggle('kpi-sep-hidden',     !kpiPanelOpenB);
        kpiApplyModeB(kpiCurrentModeB, false);
        // NOTA: kpiRevealCellsB se llama desde inicializarBajas() DESPUÃ‰S de que
        // el panel sea visible, porque las transiciones CSS no corren con display:none

        // Conectar celdas al modal de desglose de Bajas
        document.querySelectorAll('#kpiRowNewB .kpi-cell').forEach(function(cell) {
            cell.addEventListener('click', function(e) {
                if (e.target.closest('button, a, input, select')) return;
                var tipo = this.getAttribute('data-tipo');
                if (!tipo) return;
                if (tipo === 'bajas-dep')    { if (typeof abrirModalBajas === 'function') abrirModalBajas('departamentos'); }
                else if (tipo === 'bajas-puesto') { if (typeof abrirModalBajas === 'function') abrirModalBajas('puestos'); }
                else if (tipo === 'bajas-total')  { if (typeof abrirModalBajas === 'function') abrirModalBajas('total'); }
            });
        });
    });

})(); /* fin KPI panel Bajas v3 */

(function () {
  const modal = document.getElementById('modalAgregarUsuarioRrhh');
  const form = document.getElementById('formAgregarUsuarioRrhh');
  if (!modal || !form) return;
  const modalOptionsRrhh = { backdrop: 'static', keyboard: false };
  if (window.bootstrap?.Modal) {
    bootstrap.Modal.getInstance(modal)?.dispose();
    bootstrap.Modal.getOrCreateInstance(modal, modalOptionsRrhh);
  }

  const tituloModalRrhh = document.getElementById('modalAgregarUsuarioRrhhLabel');
  const subtituloModalRrhh = document.getElementById('rrhhWizardSubtitle');
  const inputEditIdRrhh = document.getElementById('rrhh_edit_id_persona');
  const btnGuardarRrhh = document.getElementById('btnGuardarUsuarioRrhh');
  const cardSalarioSensibleRrhh = document.getElementById('rrhhSalarioSensibleCard');
  const inputSalarioSensibleRrhh = document.getElementById('rrhh_salario_sensible');
  const textoSalarioSensibleRrhh = document.getElementById('rrhh_salario_sensible_letras');
  const btnDesbloquearSalarioRrhh = document.getElementById('btnRrhhDesbloquearSalario');
  const btnGuardarSalarioRrhh = document.getElementById('btnRrhhGuardarSalario');
  const estadoSalarioSensibleRrhh = document.getElementById('rrhhSalarioSensibleStatus');
  const btnGenerarExpedienteRrhh = document.getElementById('btnGenerarExpedienteRrhh');
  const btnGenerarCredencialRrhh = document.getElementById('btnGenerarCredencialRrhh');
  const modalExpedienteRrhh = document.getElementById('modalExpedienteRrhh');
  const contenedorExpedienteRrhh = document.getElementById('rrhhExpedienteContenido');
  const btnVolverRrhhDesdeExpediente = document.getElementById('btnVolverRrhhDesdeExpediente');
  const btnImprimirExpedienteRrhh = document.getElementById('btnImprimirExpedienteRrhh');
  const modalFirmaExpedienteRrhh = document.getElementById('modalFirmaExpedienteRrhh');
  const canvasFirmaExpedienteRrhh = document.getElementById('rrhhFirmaExpedienteCanvas');
  const btnRepetirFirmaExpedienteRrhh = document.getElementById('btnRepetirFirmaExpedienteRrhh');
  const btnAceptarFirmaExpedienteRrhh = document.getElementById('btnAceptarFirmaExpedienteRrhh');
  const btnLimpiarTodoExpedienteRrhh = document.getElementById('btnLimpiarTodoExpedienteRrhh');
  const inputIncluirFotoExpedienteRrhh = document.getElementById('rrhhExpedienteIncluirFoto');
  const inputFotoExpedienteRrhh = document.getElementById('rrhhExpedienteFotoInput');
  const dropzoneFotoExpedienteRrhh = document.getElementById('rrhhExpedienteFotoDropzone');
  const btnCambiarFotoExpedienteRrhh = document.getElementById('btnCambiarFotoExpedienteRrhh');
  const btnAjustarFotoExpedienteRrhh = document.getElementById('btnAjustarFotoExpedienteRrhh');
  const btnAbrirImportacionDocsRrhh = document.getElementById('btnAbrirImportacionDocsRrhh');
  const modalImportarDocumentosRrhh = document.getElementById('modalImportarDocumentosRrhh');
  const inputRrhhImportArchivos = document.getElementById('rrhhImportDocsInputArchivos');
  const inputRrhhImportCarpeta = document.getElementById('rrhhImportDocsInputCarpeta');
  const btnRrhhImportSeleccionarArchivos = document.getElementById('btnRrhhImportSeleccionarArchivos');
  const btnRrhhImportSeleccionarCarpeta = document.getElementById('btnRrhhImportSeleccionarCarpeta');
  const btnRrhhImportImportar = document.getElementById('btnRrhhImportImportar');
  const btnRrhhImportLimpiar = document.getElementById('btnRrhhImportLimpiar');
  const rrhhImportDocsSeleccionResumen = document.getElementById('rrhhImportDocsSeleccionResumen');
  const rrhhImportDocsResumen = document.getElementById('rrhhImportDocsResumen');
  const rrhhImportDocsTabla = document.getElementById('rrhhImportDocsTabla');
  const modalRrhhImportPreview = document.getElementById('modalRrhhImportPreview');
  const rrhhImportPreviewFrame = document.getElementById('rrhhImportPreviewFrame');
  const modalRrhhImportPreviewLabel = document.getElementById('modalRrhhImportPreviewLabel');
  const modalCredencialRrhh = document.getElementById('modalCredencialRrhh');
  const contenedorCredencialFrente = document.getElementById('rrhhCredencialFrente');
  const contenedorCredencialReverso = document.getElementById('rrhhCredencialReverso');
  const btnVolverRrhhDesdeCredencial = document.getElementById('btnVolverRrhhDesdeCredencial');
  const btnImprimirCredencialRrhh = document.getElementById('btnImprimirCredencialRrhh');
  const btnCambiarFotoCredencialRrhh = document.getElementById('btnCambiarFotoCredencialRrhh');
  const btnAjustarFotoCredencialRrhh = document.getElementById('btnAjustarFotoCredencialRrhh');
  const btnFotoCompletaCredencialRrhh = document.getElementById('btnFotoCompletaCredencialRrhh');
  const inputFotoCredencialRrhh = document.getElementById('rrhhCredencialFotoInput');
  const dropzoneFotoCredencialRrhh = document.getElementById('rrhhCredencialFotoDropzone');
  const modalAjustarFotoCredencialRrhh = document.getElementById('modalAjustarFotoCredencialRrhh');
  const frameEditorFotoCredencialRrhh = document.getElementById('rrhhCredencialFotoEditorFrame');
  const livePreviewFotoCredencialRrhh = document.getElementById('rrhhCredencialFotoLivePreview');
  const btnEditorFotoCompletaCredencialRrhh = document.getElementById('btnEditorFotoCompletaCredencialRrhh');
  const btnAplicarAjusteFotoCredencialRrhh = document.getElementById('btnAplicarAjusteFotoCredencialRrhh');
  const btnRestaurarAjusteFotoCredencialRrhh = document.getElementById('btnRestaurarAjusteFotoCredencialRrhh');
  const logoCredencialRrhh = '/assets/img/logo_nombre.svg';
  const logoExpedienteRrhh = '/assets/img/logo_correo.png';
  const firmasExpedienteRrhh = {};
  let abriendoExpedienteRrhh = false;
  let volverDesdeExpedienteRrhh = false;
  let firmaActivaExpedienteRrhh = '';
  let firmaDibujandoRrhh = false;
  let firmaTieneTrazosRrhh = false;
  let incluirFotoExpedienteRrhh = true;
  let fotoTemporalExpedienteRrhh = '';
  let fotoFitExpedienteRrhh = 'cover';
  let fotoPosXExpedienteRrhh = 50;
  let fotoPosYExpedienteRrhh = 50;
  let fotoScaleExpedienteRrhh = 1;
  let fotoDragExpedienteRrhh = null;
  let fechaRecepcionExpedienteRrhh = '';
  let rrhhImportDocsFiles = [];
  let rrhhImportDocsAnalisis = null;
  let rrhhImportPreviewUrl = '';
  const RRHH_IMPORT_DOCS_MAX_FILES = 10;
  const RRHH_IMPORT_DOCS_MAX_BYTES = 30 * 1024 * 1024;
  const RRHH_IMPORT_DOCS_MAX_ZIP_BYTES = 30 * 1024 * 1024;
  let abriendoCredencialRrhh = false;
  let volverDesdeCredencialRrhh = false;
  let orientacionCredencialRrhh = 'vertical';
  let fotoTemporalCredencialRrhh = '';
  let fotoFitCredencialRrhh = 'cover';
  let fotoPosXCredencialRrhh = 50;
  let fotoPosYCredencialRrhh = 50;
  let fotoScaleCredencialRrhh = 1;
  let posicionFotoCredencialRrhh = 'center center';
  let fotoDragCredencialRrhh = null;
  let fotoEditorDragCredencialRrhh = null;
  let fotoEditorContextoRrhh = 'credencial';
  let fotoEditorEstadoInicialRrhh = null;
  const rrhhWizardSteps = [
    { target: '#rrhhTabPersona', title: 'Datos personales' },
    { target: '#rrhhTabLaboral', title: 'Laboral' },
    { target: '#rrhhTabNomina', title: 'Banco y cr&eacute;ditos' },
    { target: '#rrhhTabContactos', title: 'Contactos' },
    { target: '#rrhhTabBeneficiarios', title: 'Beneficiarios' },
    { target: '#rrhhTabSalud', title: 'Salud' },
    { target: '#rrhhTabObservaciones', title: 'Observaciones' }
  ];

  const templates = {
    telefonos: [
      { key: 'numero', label: 'Número', type: 'text', col: 'col-md-5' },
      { key: 'tipo', label: 'Tipo', type: 'select', col: 'col-md-5', options: ['Personal', 'Laboral', 'Casa', 'WhatsApp', 'Emergencia', 'Otro'] }
    ],
    correos: [
      { key: 'correo', label: 'Correo', type: 'email', col: 'col-md-5' },
      { key: 'tipo', label: 'Tipo', type: 'select', col: 'col-md-5', options: ['Personal', 'Laboral', 'Institucional', 'Nómina', 'Otro'] }
    ],
    domicilios: [
      { key: 'domicilio_texto', label: 'Domicilio', type: 'text', col: 'col-md-6' },
      { key: 'codigo_postal', label: 'Código postal', type: 'text', col: 'col-md-2' },
      { key: 'tipo', label: 'Tipo', type: 'select', col: 'col-md-3', options: ['Actual', 'Particular', 'Rentado', 'Familiar', 'Fiscal', 'Laboral'] }
    ],
    cuentas_bancarias: [
      { key: 'id_banco', label: 'ID banco', type: 'number', col: 'col-md-2' },
      { key: 'nombre_banco', label: 'Banco', type: 'text', col: 'col-md-3' },
      { key: 'numero_cuenta', label: 'Número de cuenta', type: 'text', col: 'col-md-3' },
      { key: 'clabe', label: 'CLABE interbancaria', type: 'text', col: 'col-md-3' }
    ],
    contactos_emergencia: [
      { key: 'nombre_contacto', label: 'Nombre', type: 'text', col: 'col-md-5' },
      { key: 'parentesco', label: 'Parentesco', type: 'text', col: 'col-md-3' },
      { key: 'numero', label: 'Teléfono', type: 'text', col: 'col-md-3' }
    ],
    beneficiarios: [
      { key: 'nombre_beneficiario', label: 'Nombre', type: 'text', col: 'col-md-4' },
      { key: 'parentesco', label: 'Parentesco', type: 'text', col: 'col-md-2' },
      { key: 'numero', label: 'Teléfono', type: 'text', col: 'col-md-2' },
      { key: 'porcentaje', label: 'Porcentaje', type: 'number', col: 'col-md-2', step: '0.01' }
    ]
  };

  function ensureRrhhLoadingOverlay() {
    let overlay = modal.querySelector('.rrhh-loading-overlay');
    if (overlay) return overlay;
    overlay = document.createElement('div');
    overlay.className = 'rrhh-loading-overlay';
    overlay.setAttribute('aria-live', 'polite');
    overlay.innerHTML = '<div class="rrhh-loading-card">' +
      '<span class="spinner-border spinner-border-sm text-primary" role="status" aria-hidden="true"></span>' +
      '<span data-rrhh-loading-text>Cargando datos RR.HH...</span>' +
    '</div>';
    modal.querySelector('.modal-content')?.appendChild(overlay);
    return overlay;
  }

  function setRrhhModalLoading(loading, text = 'Cargando datos RR.HH...') {
    const overlay = ensureRrhhLoadingOverlay();
    const label = overlay?.querySelector('[data-rrhh-loading-text]');
    if (label) label.textContent = text;
    modal.classList.toggle('is-loading-rrhh', !!loading);
  }

  function setModoRrhh(modo, idPersona) {
    const esEdicion = modo === 'editar';
    form.dataset.mode = esEdicion ? 'editar' : 'crear';
    if (inputEditIdRrhh) inputEditIdRrhh.value = esEdicion ? String(idPersona || '') : '';
    resetSalarioSensibleRrhh(esEdicion);
    aplicarVisibilidadSalarioSensibleRrhh(!!window.puedeVerSalarioSensibleRrhh);
    if (tituloModalRrhh) {
      tituloModalRrhh.textContent = esEdicion ? 'Editar usuario RR.HH.' : 'Agregar usuario RR.HH.';
    }
    if (subtituloModalRrhh) {
      subtituloModalRrhh.innerHTML = esEdicion
        ? 'Actualiza la informaci&oacute;n del colaborador por secciones.'
        : 'Completa la informaci&oacute;n del nuevo usuario por secciones.';
    }
    if (btnGuardarRrhh) {
      btnGuardarRrhh.innerHTML = esEdicion
        ? '<i class="fa fa-save me-1"></i>Guardar cambios RR.HH.'
        : '<i class="fa fa-save me-1"></i>Guardar usuario RR.HH.';
    }
    [btnGenerarExpedienteRrhh, document.getElementById('btnGenerarCredencialRrhh')].forEach(btn => {
      if (btn) btn.classList.toggle('d-none', !esEdicion);
    });
    actualizarRrhhWizard();
  }

  function setEstadoSalarioSensibleRrhh(texto, modo = 'locked') {
    if (!estadoSalarioSensibleRrhh) return;
    estadoSalarioSensibleRrhh.classList.toggle('is-unlocked', modo === 'unlocked');
    estadoSalarioSensibleRrhh.classList.toggle('is-denied', modo === 'denied');
    const icon = modo === 'unlocked' ? 'fa-unlock' : (modo === 'denied' ? 'fa-ban' : 'fa-lock');
    estadoSalarioSensibleRrhh.innerHTML = `<i class="fa ${icon}"></i>${escapeRrhhHtml(texto)}`;
  }

  function aplicarVisibilidadSalarioSensibleRrhh(puedeGestionar) {
    const visible = !!puedeGestionar;
    window.puedeVerSalarioSensibleRrhh = visible;
    const wrapper = cardSalarioSensibleRrhh?.closest('.col-12') || cardSalarioSensibleRrhh;
    if (wrapper) {
      wrapper.classList.toggle('d-none', !visible);
      wrapper.setAttribute('aria-hidden', visible ? 'false' : 'true');
    }
    if (!visible) {
      if (inputSalarioSensibleRrhh) {
        inputSalarioSensibleRrhh.value = '';
        inputSalarioSensibleRrhh.type = 'password';
        inputSalarioSensibleRrhh.disabled = true;
      }
      if (btnDesbloquearSalarioRrhh) btnDesbloquearSalarioRrhh.disabled = true;
      if (btnGuardarSalarioRrhh) btnGuardarSalarioRrhh.disabled = true;
    }
  }

  function limpiarSalarioRrhh(value) {
    const text = String(value ?? '').replace(/[^\d.]/g, '');
    const parts = text.split('.');
    const entero = (parts.shift() || '').replace(/^0+(?=\d)/, '');
    const decimal = parts.join('').replace(/\D/g, '').slice(0, 2);
    return decimal ? `${entero || '0'}.${decimal}` : entero;
  }

  function formatearSalarioRrhh(value) {
    const limpio = limpiarSalarioRrhh(value);
    if (!limpio) return '';
    const [enteroRaw, decimalRaw = ''] = limpio.split('.');
    const entero = Number(enteroRaw || 0).toLocaleString('es-MX');
    return decimalRaw ? `${entero}.${decimalRaw}` : entero;
  }

  function numeroEnteroALetrasRrhh(numero) {
    const unidades = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve'];
    const especiales = ['diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciseis', 'diecisiete', 'dieciocho', 'diecinueve'];
    const decenas = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    const centenas = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    const menorMil = (n) => {
      if (n === 0) return '';
      if (n === 100) return 'cien';
      if (n < 10) return unidades[n];
      if (n < 20) return especiales[n - 10];
      if (n === 20) return 'veinte';
      if (n < 30) return 'veinti' + unidades[n - 20];
      if (n < 100) {
        const d = Math.floor(n / 10);
        const u = n % 10;
        return decenas[d] + (u ? ' y ' + unidades[u] : '');
      }
      const c = Math.floor(n / 100);
      const r = n % 100;
      return centenas[c] + (r ? ' ' + menorMil(r) : '');
    };

    const n = Math.max(0, Math.floor(Number(numero) || 0));
    if (n === 0) return 'cero';
    if (n < 1000) return menorMil(n);
    if (n < 1000000) {
      const miles = Math.floor(n / 1000);
      const resto = n % 1000;
      const textoMiles = miles === 1 ? 'mil' : `${menorMil(miles)} mil`;
      return textoMiles + (resto ? ' ' + menorMil(resto) : '');
    }
    const millones = Math.floor(n / 1000000);
    const resto = n % 1000000;
    const textoMillones = millones === 1 ? 'un millon' : `${numeroEnteroALetrasRrhh(millones)} millones`;
    return textoMillones + (resto ? ' ' + numeroEnteroALetrasRrhh(resto) : '');
  }

  function capitalizarPrimeraRrhh(texto) {
    const limpio = String(texto || '').trim();
    return limpio ? limpio.charAt(0).toUpperCase() + limpio.slice(1) : '';
  }

  function salarioALetrasRrhh(numero) {
    const n = Math.max(0, Math.floor(Number(numero) || 0));
    if (n <= 0) return '';
    return `${capitalizarPrimeraRrhh(numeroEnteroALetrasRrhh(n))} pesos`;
  }

  function actualizarSalarioLetrasRrhh() {
    if (!textoSalarioSensibleRrhh || !inputSalarioSensibleRrhh) return;
    if (inputSalarioSensibleRrhh.disabled || inputSalarioSensibleRrhh.type === 'password') {
      textoSalarioSensibleRrhh.textContent = 'Desbloquea el salario para capturarlo.';
      return;
    }
    const valor = limpiarSalarioRrhh(inputSalarioSensibleRrhh.value);
    const numero = Math.floor(Number(valor || 0));
    textoSalarioSensibleRrhh.textContent = numero > 0
      ? salarioALetrasRrhh(numero)
      : 'Captura el salario mensual en numeros.';
  }

  function resetSalarioSensibleRrhh(esEdicion = false, tieneSalario = false) {
    if (inputSalarioSensibleRrhh) {
      inputSalarioSensibleRrhh.value = esEdicion && tieneSalario ? '********' : '';
      inputSalarioSensibleRrhh.type = 'password';
      inputSalarioSensibleRrhh.disabled = true;
      inputSalarioSensibleRrhh.placeholder = esEdicion ? 'Protegido' : 'Disponible al editar';
    }
    actualizarSalarioLetrasRrhh();
    if (btnDesbloquearSalarioRrhh) btnDesbloquearSalarioRrhh.disabled = !esEdicion;
    if (btnGuardarSalarioRrhh) btnGuardarSalarioRrhh.disabled = true;
    setEstadoSalarioSensibleRrhh(esEdicion ? (tieneSalario ? 'Guardado' : 'Sin capturar') : 'Solo al editar', 'locked');
  }

  function aplicarEstadoSalarioSensibleRrhh(meta = {}) {
    const puedeGestionar = Object.prototype.hasOwnProperty.call(meta, 'puede_gestionar')
      ? !!meta.puede_gestionar
      : !!window.puedeVerSalarioSensibleRrhh;
    aplicarVisibilidadSalarioSensibleRrhh(puedeGestionar);
    if (!puedeGestionar) return;
    resetSalarioSensibleRrhh(form.dataset.mode === 'editar', !!meta.tiene_salario);
  }

  function idPersonaRrhhActual() {
    return Number(inputEditIdRrhh?.value || 0);
  }

  function cargarLibreriaQrRrhhLocal() {
    if (window.QRCode && typeof window.QRCode.toCanvas === 'function') {
      return Promise.resolve();
    }
    return new Promise((resolve, reject) => {
      const existente = document.querySelector('script[data-rrhh-qrcode="1"]');
      if (existente) {
        existente.addEventListener('load', resolve, { once: true });
        existente.addEventListener('error', reject, { once: true });
        return;
      }
      const script = document.createElement('script');
      script.src = '/assets/vendor/libs/qrcode/qrcode.js';
      script.async = true;
      script.dataset.rrhhQrcode = '1';
      script.onload = resolve;
      script.onerror = reject;
      document.head.appendChild(script);
    });
  }

  async function pedirTotpSalarioSensibleRrhh(datosTotp) {
    const setup = !!datosTotp?.setup;
    const secret = datosTotp?.secret || '';
    const otpauthUrl = datosTotp?.otpauth_url || '';
    const cuenta = datosTotp?.cuenta || '';
    const html = setup
      ? `<div class="text-start">
          <p class="mb-2">Escanea este QR en Google Authenticator y captura el codigo generado.</p>
          <div class="d-flex justify-content-center my-2"><canvas id="rrhh-salario-totp-qr" width="210" height="210"></canvas></div>
          <div class="small text-muted">Cuenta: ${escapeRrhhHtml(cuenta)}${secret ? '<br>Clave: <code>' + escapeRrhhHtml(secret) + '</code>' : ''}</div>
          <div class="small text-danger d-none mt-2" id="rrhh-salario-totp-qr-error">No se pudo pintar el QR. Usa la clave manual.</div>
        </div>`
      : '<div class="text-start"><p class="mb-0">Escribe el codigo de 6 digitos de Google Authenticator para desbloquear salario.</p></div>';

    const result = await Swal.fire({
      title: 'Segundo paso requerido',
      html,
      input: 'text',
      inputPlaceholder: 'Codigo de 6 digitos',
      inputAttributes: { maxlength: 6, inputmode: 'numeric', autocomplete: 'one-time-code' },
      showCancelButton: true,
      confirmButtonText: 'Verificar',
      cancelButtonText: 'Cancelar',
      didOpen: async () => {
        document.querySelectorAll('.modal.show').forEach(modal => {
          const instancia = window.bootstrap && bootstrap.Modal ? bootstrap.Modal.getInstance(modal) : null;
          if (instancia && instancia._focustrap && typeof instancia._focustrap.deactivate === 'function') {
            instancia._focustrap.deactivate();
          }
        });
        setTimeout(() => {
          const input = Swal.getInput ? Swal.getInput() : document.querySelector('.swal2-input');
          if (input) {
            input.disabled = false;
            input.readOnly = false;
            input.focus();
            input.select?.();
          }
        }, 80);
        if (setup && otpauthUrl) {
          try {
            await cargarLibreriaQrRrhhLocal();
            const canvas = document.getElementById('rrhh-salario-totp-qr');
            if (canvas && window.QRCode && typeof window.QRCode.toCanvas === 'function') {
              canvas.style.pointerEvents = 'none';
              await window.QRCode.toCanvas(canvas, otpauthUrl, { width: 210, margin: 2, errorCorrectionLevel: 'M' });
            }
          } catch (error) {
            document.getElementById('rrhh-salario-totp-qr-error')?.classList.remove('d-none');
          }
        }
      },
      preConfirm: value => {
        const codigo = String(value || '').replace(/\D+/g, '');
        if (codigo.length !== 6) {
          Swal.showValidationMessage('Captura los 6 digitos de Google Authenticator.');
          return false;
        }
        return codigo;
      }
    });
    return result.isConfirmed ? result.value : '';
  }

  async function postSalarioSensibleRrhh(endpoint, payload, datosTotp = null) {
    const body = Object.assign({}, payload);
    if (datosTotp && datosTotp.codigo) body.totp_code = datosTotp.codigo;
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(body)
    });
    const data = await response.json();
    if (data.success && data.datos && data.datos.requiere_totp) {
      const codigo = await pedirTotpSalarioSensibleRrhh(data.datos);
      if (!codigo) return null;
      return postSalarioSensibleRrhh(endpoint, payload, { codigo });
    }
    return data;
  }

  async function desbloquearSalarioSensibleRrhh() {
    const idPersona = idPersonaRrhhActual();
    if (!idPersona) {
      Swal.fire('Salario protegido', 'Primero guarda el usuario y vuelve a editarlo para capturar salario.', 'info');
      return;
    }
    if (btnDesbloquearSalarioRrhh) btnDesbloquearSalarioRrhh.disabled = true;
    try {
      const data = await postSalarioSensibleRrhh('/CapHum/leerSalarioPersonaRrhh', { id_persona: idPersona });
      if (!data) return;
      if (!data.success) {
        setEstadoSalarioSensibleRrhh('Sin permiso', 'denied');
        throw new Error(data.mensaje || 'No se pudo desbloquear el salario.');
      }
      if (inputSalarioSensibleRrhh) {
        inputSalarioSensibleRrhh.type = 'text';
        inputSalarioSensibleRrhh.disabled = false;
        inputSalarioSensibleRrhh.value = formatearSalarioRrhh(data.datos?.salario || '');
        inputSalarioSensibleRrhh.placeholder = '0.00';
        actualizarSalarioLetrasRrhh();
        inputSalarioSensibleRrhh.focus();
      }
      if (btnGuardarSalarioRrhh) btnGuardarSalarioRrhh.disabled = false;
      setEstadoSalarioSensibleRrhh('Desbloqueado', 'unlocked');
    } catch (error) {
      Swal.fire('Salario protegido', error.message || 'No se pudo desbloquear el salario.', 'error');
    } finally {
      if (btnDesbloquearSalarioRrhh) btnDesbloquearSalarioRrhh.disabled = false;
    }
  }

  async function guardarSalarioSensibleRrhh() {
    const idPersona = idPersonaRrhhActual();
    if (!idPersona || !inputSalarioSensibleRrhh || inputSalarioSensibleRrhh.disabled) return;
    if (btnGuardarSalarioRrhh) btnGuardarSalarioRrhh.disabled = true;
    try {
      const data = await postSalarioSensibleRrhh('/CapHum/guardarSalarioPersonaRrhh', {
        id_persona: idPersona,
        salario: limpiarSalarioRrhh(inputSalarioSensibleRrhh.value || '')
      });
      if (!data) return;
      if (!data.success) throw new Error(data.mensaje || 'No se pudo guardar el salario.');
      if (data.datos && data.datos.tiene_salario === false) {
        resetSalarioSensibleRrhh(true, false);
      } else {
        inputSalarioSensibleRrhh.value = formatearSalarioRrhh(data.datos?.salario || '');
        actualizarSalarioLetrasRrhh();
        setEstadoSalarioSensibleRrhh('Guardado', 'unlocked');
      }
      Swal.fire('Guardado', 'Salario protegido actualizado correctamente.', 'success');
    } catch (error) {
      Swal.fire('Error', error.message || 'No se pudo guardar el salario.', 'error');
    } finally {
      if (btnGuardarSalarioRrhh && inputSalarioSensibleRrhh && !inputSalarioSensibleRrhh.disabled) {
        btnGuardarSalarioRrhh.disabled = false;
      }
    }
  }

  btnDesbloquearSalarioRrhh?.addEventListener('click', desbloquearSalarioSensibleRrhh);
  btnGuardarSalarioRrhh?.addEventListener('click', guardarSalarioSensibleRrhh);
  inputSalarioSensibleRrhh?.addEventListener('input', () => {
    const formateado = formatearSalarioRrhh(inputSalarioSensibleRrhh.value);
    if (inputSalarioSensibleRrhh.value !== formateado) {
      inputSalarioSensibleRrhh.value = formateado;
    }
    actualizarSalarioLetrasRrhh();
  });

  function escapeRrhhAttr(value) {
    return String(value ?? '')
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function inputHtml(field, values) {
    const value = values && values[field.key] != null ? String(values[field.key]) : '';
    if (field.type === 'select') {
      const opts = (field.options || []).map(opt => {
        const selected = String(opt) === value ? ' selected' : '';
        return `<option value="${escapeRrhhAttr(opt)}"${selected}>${escapeRrhhHtml(opt)}</option>`;
      }).join('');
      return `<select class="form-select" data-field="${field.key}">${opts}</select>`;
    }
    const step = field.step ? ` step="${field.step}"` : '';
    const attrValue = value ? ` value="${escapeRrhhAttr(value)}"` : '';
    return `<input type="${field.type}" class="form-control" data-field="${field.key}"${step}${attrValue}>`;
  }

  function addRow(type, values = {}) {
    const list = form.querySelector(`[data-rrhh-list="${type}"]`);
    if (!list || !templates[type]) return;
    const row = document.createElement('div');
    row.className = 'rrhh-repeat-row';
    row.innerHTML = `<div class="row g-2 align-items-end">
      ${templates[type].map(field => `<div class="${field.col}"><label class="form-label">${field.label}</label>${inputHtml(field, values)}</div>`).join('')}
      <div class="col-md-1 d-grid"><button type="button" class="btn btn-outline-danger rrhh-remove-row" title="Quitar"><i class="fa fa-trash"></i></button></div>
    </div>`;
    list.appendChild(row);
  }

  function resetRepeats() {
    Object.keys(templates).forEach(type => {
      const list = form.querySelector(`[data-rrhh-list="${type}"]`);
      if (list) list.innerHTML = '';
      addRow(type);
    });
  }

  function setNested(target, path, value) {
    const parts = path.split('.');
    let cursor = target;
    while (parts.length > 1) {
      const part = parts.shift();
      cursor[part] = cursor[part] || {};
      cursor = cursor[part];
    }
    cursor[parts[0]] = value;
  }

  function collectRepeats(type) {
    return Array.from(form.querySelectorAll(`[data-rrhh-list="${type}"] .rrhh-repeat-row`))
      .map(row => {
        const item = {};
        row.querySelectorAll('[data-field]').forEach(input => item[input.dataset.field] = input.value.trim());
        return item;
      })
      .filter(item => Object.values(item).some(value => String(value || '').trim() !== ''));
  }

  function collectPayload() {
    const payload = {
      persona: {},
      rrhh: {},
      nomina: {},
      telefonos: collectRepeats('telefonos'),
      correos: collectRepeats('correos'),
      domicilios: collectRepeats('domicilios'),
      cuentas_bancarias: collectRepeats('cuentas_bancarias'),
      contactos_emergencia: collectRepeats('contactos_emergencia'),
      beneficiarios: collectRepeats('beneficiarios'),
      observaciones: ''
    };

    form.querySelectorAll('[name]').forEach(input => {
      if (!input.name) return;
      const value = input.value.trim();
      if (input.name === 'observaciones') {
        payload.observaciones = value;
      } else {
        setNested(payload, input.name, value);
      }
    });

    if (!payload.rrhh.direccion_organizacional && payload.rrhh.direccion_texto) {
      payload.rrhh.direccion_organizacional = payload.rrhh.direccion_texto;
    }

    return payload;
  }

  function initRrhhWizardMarkup() {
    if (form.dataset.wizardReady === '1') return;
    form.setAttribute('novalidate', 'novalidate');

    const body = modal.querySelector('.modal-body');
    const nav = body?.querySelector('.nav-tabs[role="tablist"]');
    const tabContent = body?.querySelector('.tab-content');
    if (!body || !nav || !tabContent) return;

    if (!document.getElementById('rrhhWizardStepText')) {
      const progress = document.createElement('div');
      progress.className = 'rrhh-wizard-progress';
      progress.innerHTML = '<span id="rrhhWizardStepText">Paso 1 de 7</span>' +
        '<div class="rrhh-progress-track" aria-hidden="true"><div class="rrhh-progress-bar" id="rrhhWizardProgressBar"></div></div>' +
        '<span id="rrhhWizardPercent">0% completado</span>';
      body.insertBefore(progress, nav);

      const info = document.createElement('div');
      info.className = 'rrhh-wizard-info';
      info.innerHTML = '<i class="fa fa-circle-info"></i><span>Los campos marcados con * son obligatorios.</span>';
      body.insertBefore(info, nav);
    }

    const layout = document.createElement('div');
    layout.className = 'rrhh-wizard-layout';
    const sidebar = document.createElement('aside');
    sidebar.className = 'rrhh-wizard-sidebar';
    sidebar.setAttribute('aria-label', 'Secciones de captura RR.HH.');
    const content = document.createElement('div');
    content.className = 'rrhh-wizard-content';

    nav.classList.remove('mb-3');
    nav.classList.add('rrhh-wizard-steps');
    Array.from(nav.querySelectorAll('.nav-link')).forEach((btn, index) => {
      const step = rrhhWizardSteps[index] || { title: btn.textContent.trim() || `Paso ${index + 1}` };
      btn.dataset.rrhhStep = String(index);
      btn.innerHTML = '<span class="rrhh-step-marker"><span class="rrhh-step-num">' + (index + 1) + '</span><i class="fa fa-check"></i></span>' +
        '<span><span class="rrhh-step-title">' + step.title + '</span><small class="rrhh-step-state">Pendiente</small></span>';
    });

    body.insertBefore(layout, nav);
    sidebar.appendChild(nav);
    content.appendChild(tabContent);
    layout.appendChild(sidebar);
    layout.appendChild(content);

    const footer = modal.querySelector('.modal-footer');
    if (footer && !document.getElementById('btnRrhhWizardNext')) {
      const left = document.createElement('div');
      left.className = 'rrhh-footer-left';
      const right = document.createElement('div');
      right.className = 'rrhh-footer-right';

      const btnExpediente = document.getElementById('btnGenerarExpedienteRrhh');
      const btnCredencial = document.getElementById('btnGenerarCredencialRrhh');
      const btnCerrar = footer.querySelector('[data-bs-dismiss="modal"]');
      const btnSiguiente = document.createElement('button');
      btnSiguiente.type = 'button';
      btnSiguiente.className = 'btn btn-primary rrhh-wizard-action';
      btnSiguiente.id = 'btnRrhhWizardNext';
      btnSiguiente.innerHTML = 'Siguiente <i class="fa fa-arrow-right ms-1"></i>';

      if (btnExpediente) left.appendChild(btnExpediente);
      if (btnCredencial) left.appendChild(btnCredencial);
      if (btnCerrar) {
        btnCerrar.className = 'btn btn-outline-secondary rrhh-wizard-action';
        btnCerrar.textContent = 'Cancelar';
        left.appendChild(btnCerrar);
      }
      right.appendChild(btnSiguiente);
      if (btnGuardarRrhh) {
        btnGuardarRrhh.classList.add('rrhh-wizard-action');
        right.appendChild(btnGuardarRrhh);
      }
      footer.replaceChildren(left, right);

      btnSiguiente.addEventListener('click', () => navegarRrhhPaso(obtenerPasoActivoRrhh() + 1, true));
    }

    nav.addEventListener('shown.bs.tab', actualizarRrhhWizard);
    asegurarResumenLaboralRrhh();
    asegurarBeneficiariosStatusRrhh();
    asegurarNotaSaludRrhh();
    asegurarPasswordToggleRrhh();
    form.dataset.wizardReady = '1';
    actualizarRrhhWizard();
  }

  function obtenerBotonesPasoRrhh() {
    return Array.from(form.querySelectorAll('[data-rrhh-step]'));
  }

  function obtenerPasoActivoRrhh() {
    const botones = obtenerBotonesPasoRrhh();
    const index = botones.findIndex(btn => btn.classList.contains('active'));
    return index >= 0 ? index : 0;
  }

  function paneRrhhPorPaso(index) {
    const step = rrhhWizardSteps[index];
    return step ? form.querySelector(step.target) : null;
  }

  function valorControlRrhh(control) {
    if (!control || control.disabled) return '';
    if (control.type === 'checkbox' || control.type === 'radio') return control.checked ? control.value : '';
    return String(control.value || '').trim();
  }

  function controlesRequeridosRrhh(pane) {
    return Array.from(pane?.querySelectorAll('[required]') || []).filter(control => !control.disabled);
  }

  function paneTieneDatosRrhh(pane) {
    return Array.from(pane?.querySelectorAll('input, select, textarea') || [])
      .some(control => control.name && valorControlRrhh(control) !== '');
  }

  function controlCuentaParaProgresoRrhh(control) {
    if (!control || control.disabled) return false;
    if (control.closest('.rrhh-repeat-row')) return false;
    if (control.type === 'hidden' || control.type === 'button' || control.type === 'submit') return false;
    if (!control.name) return false;
    return true;
  }

  function calcularProgresoCapturaRrhh() {
    let total = 0;
    let llenos = 0;

    Array.from(form.querySelectorAll('input, select, textarea')).forEach(control => {
      if (!controlCuentaParaProgresoRrhh(control)) return;
      total += 1;
      if (valorControlRrhh(control) !== '') llenos += 1;
    });

    form.querySelectorAll('.rrhh-repeat-row').forEach(row => {
      const fields = Array.from(row.querySelectorAll('[data-field]')).filter(control => !control.disabled);
      if (!fields.length) return;
      const rowConDatoReal = fields.some(control => {
        if (control.tagName === 'SELECT') return false;
        return valorControlRrhh(control) !== '';
      });
      if (!rowConDatoReal) return;
      fields.forEach(control => {
        total += 1;
        if (valorControlRrhh(control) !== '') llenos += 1;
      });
    });

    if (total <= 0 || llenos <= 0) return 0;
    return Math.max(0, Math.min(100, Math.round((llenos / total) * 100)));
  }

  function marcarControlRrhh(control, invalido) {
    if (!control) return;
    control.classList.toggle('is-invalid', !!invalido);
    if (!invalido) control.classList.remove('is-invalid');
  }

  function totalBeneficiariosRrhh() {
    return collectRepeats('beneficiarios').reduce((total, item) => {
      const value = Number(String(item.porcentaje || '').replace(',', '.'));
      return total + (Number.isFinite(value) ? value : 0);
    }, 0);
  }

  function validarRrhhPaso(index, mostrarMensaje = true) {
    const pane = paneRrhhPorPaso(index);
    if (!pane) return true;
    const invalidos = [];

    controlesRequeridosRrhh(pane).forEach(control => {
      const invalido = valorControlRrhh(control) === '';
      marcarControlRrhh(control, invalido);
      if (invalido) invalidos.push({ control, mensaje: 'Completa los campos obligatorios de esta secci\u00f3n.' });
    });

    const curp = pane.querySelector('[name="persona.curp"]');
    if (curp && valorControlRrhh(curp) && !/^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d$/i.test(valorControlRrhh(curp))) {
      marcarControlRrhh(curp, true);
      invalidos.push({ control: curp, mensaje: 'El formato de CURP no es v\u00e1lido.' });
    }

    const nss = pane.querySelector('[name="persona.nss"]');
    if (nss && valorControlRrhh(nss) && !/^\d+$/.test(valorControlRrhh(nss))) {
      marcarControlRrhh(nss, true);
      invalidos.push({ control: nss, mensaje: 'El NSS debe contener solo n\u00fameros.' });
    }

    pane.querySelectorAll('[data-field="correo"]').forEach(control => {
      const value = valorControlRrhh(control);
      if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
        marcarControlRrhh(control, true);
        invalidos.push({ control, mensaje: 'Revisa el formato del correo electr\u00f3nico.' });
      }
    });

    pane.querySelectorAll('[data-field="numero"]').forEach(control => {
      const value = valorControlRrhh(control).replace(/\D/g, '');
      if (value && value.length < 8) {
        marcarControlRrhh(control, true);
        invalidos.push({ control, mensaje: 'Revisa la longitud del tel\u00e9fono.' });
      }
    });

    pane.querySelectorAll('[data-field="clabe"]').forEach(control => {
      const value = valorControlRrhh(control).replace(/\s/g, '');
      if (value && !/^\d{18}$/.test(value)) {
        marcarControlRrhh(control, true);
        invalidos.push({ control, mensaje: 'La CLABE interbancaria debe tener 18 d\u00edgitos.' });
      }
    });

    if (rrhhWizardSteps[index]?.target === '#rrhhTabBeneficiarios' && totalBeneficiariosRrhh() > 100) {
      invalidos.push({ control: pane.querySelector('[data-field="porcentaje"]'), mensaje: 'El porcentaje total de beneficiarios no puede superar 100%.' });
    }

    if (!invalidos.length) return true;
    if (mostrarMensaje) {
      const primero = invalidos[0];
      Swal.fire('Faltan datos', primero.mensaje, 'warning');
      setTimeout(() => primero.control?.focus?.(), 180);
    }
    return false;
  }

  function validarRrhhFormularioCompleto() {
    for (let i = 0; i < rrhhWizardSteps.length; i++) {
      if (!validarRrhhPaso(i, false)) {
        navegarRrhhPaso(i, false);
        Swal.fire('Faltan datos', 'Revisa los campos marcados antes de guardar.', 'warning');
        return false;
      }
    }
    return true;
  }

  function navegarRrhhPaso(index, validarActual = false) {
    const botones = obtenerBotonesPasoRrhh();
    const actual = obtenerPasoActivoRrhh();
    const destino = Math.max(0, Math.min(botones.length - 1, index));
    if (validarActual && destino > actual && !validarRrhhPaso(actual, true)) return;
    const btn = botones[destino];
    if (!btn) return;
    if (window.bootstrap?.Tab) bootstrap.Tab.getOrCreateInstance(btn).show();
    else btn.click();
    setTimeout(actualizarRrhhWizard, 40);
  }

  function actualizarRrhhWizard() {
    initRrhhWizardMarkup();
    const botones = obtenerBotonesPasoRrhh();
    const activo = obtenerPasoActivoRrhh();
    const total = botones.length || rrhhWizardSteps.length;
    const porcentaje = calcularProgresoCapturaRrhh();
    const pasoTexto = document.getElementById('rrhhWizardStepText');
    const porcentajeTexto = document.getElementById('rrhhWizardPercent');
    const barra = document.getElementById('rrhhWizardProgressBar');
    if (pasoTexto) pasoTexto.textContent = `Paso ${activo + 1} de ${total}`;
    if (porcentajeTexto) porcentajeTexto.textContent = `${porcentaje}% completado`;
    if (barra) barra.style.width = `${porcentaje}%`;

    botones.forEach((btn, index) => {
      const pane = paneRrhhPorPaso(index);
      const requeridos = controlesRequeridosRrhh(pane);
      const completos = requeridos.length
        ? requeridos.every(control => valorControlRrhh(control) !== '')
        : paneTieneDatosRrhh(pane);
      btn.classList.toggle('is-complete', completos);
      const state = btn.querySelector('.rrhh-step-state');
      if (state) state.textContent = index === activo && !completos ? 'En progreso' : (completos ? 'Completado' : 'Pendiente');
    });

    const btnSiguiente = document.getElementById('btnRrhhWizardNext');
    const esEdicion = form.dataset.mode === 'editar';
    if (btnSiguiente) btnSiguiente.classList.toggle('d-none', esEdicion || activo >= total - 1);
    if (btnGuardarRrhh) btnGuardarRrhh.classList.toggle('d-none', !esEdicion && activo < total - 1);
    actualizarResumenLaboralRrhh();
    actualizarBeneficiariosStatusRrhh();
  }

  function asegurarResumenLaboralRrhh() {
    const pane = form.querySelector('#rrhhTabLaboral');
    if (!pane || pane.querySelector('#rrhhAssignmentSummary')) return;
    const summary = document.createElement('div');
    summary.className = 'rrhh-section shadow-sm mt-3';
    summary.id = 'rrhhAssignmentSummary';
    summary.innerHTML = '<div class="rrhh-section-title"><i class="fa fa-sitemap me-1"></i>Resumen de asignaci&oacute;n</div>' +
      '<div class="rrhh-assignment-summary">' +
        '<div><div class="rrhh-summary-label">&Aacute;rea</div><span class="rrhh-summary-chip" data-rrhh-summary="area">Sin asignar</span></div>' +
        '<div><div class="rrhh-summary-label">Departamento</div><span class="rrhh-summary-chip" data-rrhh-summary="departamento">Sin asignar</span></div>' +
        '<div><div class="rrhh-summary-label">Puesto</div><span class="rrhh-summary-chip" data-rrhh-summary="puesto">Sin asignar</span></div>' +
        '<div><div class="rrhh-summary-label">Jefe directo</div><span class="rrhh-summary-chip" data-rrhh-summary="jefe">Sin asignar</span></div>' +
      '</div>';
    pane.appendChild(summary);
  }

  function setResumenLaboralChipRrhh(key, value) {
    const chip = form.querySelector(`[data-rrhh-summary="${key}"]`);
    if (!chip) return;
    chip.textContent = value || 'Sin asignar';
    chip.title = chip.textContent;
  }

  function actualizarResumenLaboralRrhh() {
    setResumenLaboralChipRrhh('area', rrhhSelectText('rrhh_area_id'));
    setResumenLaboralChipRrhh('departamento', rrhhSelectText('rrhh_departamento_id'));
    setResumenLaboralChipRrhh('puesto', rrhhSelectText('rrhh_puesto_id'));
    setResumenLaboralChipRrhh('jefe', rrhhSelectText('rrhh_jefe_id'));
  }

  function asegurarBeneficiariosStatusRrhh() {
    const header = form.querySelector('#rrhhTabBeneficiarios .rrhh-section .d-flex');
    if (!header || header.querySelector('#rrhhBeneficiariosTotal')) return;
    const status = document.createElement('span');
    status.id = 'rrhhBeneficiariosTotal';
    status.className = 'rrhh-beneficiarios-status';
    status.textContent = 'Total asignado: 0%';
    const btn = header.querySelector('button');
    if (btn) header.insertBefore(status, btn);
    else header.appendChild(status);
  }

  function actualizarBeneficiariosStatusRrhh() {
    const status = document.getElementById('rrhhBeneficiariosTotal');
    if (!status) return;
    const total = totalBeneficiariosRrhh();
    const entero = Math.round(total * 100) / 100;
    status.textContent = `Total asignado: ${entero}%`;
    status.classList.toggle('is-ok', entero === 100);
    status.classList.toggle('is-warn', entero > 0 && entero < 100);
    status.classList.toggle('is-error', entero > 100);
  }

  function asegurarNotaSaludRrhh() {
    const pane = form.querySelector('#rrhhTabSalud .rrhh-section');
    if (!pane || pane.querySelector('.rrhh-sensitive-note')) return;
    const title = pane.querySelector('.rrhh-section-title');
    const note = document.createElement('div');
    note.className = 'rrhh-sensitive-note';
    note.innerHTML = '<i class="fa fa-shield-halved mt-1"></i><span>Esta informaci&oacute;n debe tratarse como confidencial y visible solo para personal autorizado.</span>';
    if (title) title.insertAdjacentElement('afterend', note);
    else pane.insertBefore(note, pane.firstChild);
  }

  function asegurarPasswordToggleRrhh() {
    const input = form.querySelector('[name="persona.contrasena"]');
    if (!input || input.dataset.passwordToggleReady === '1') return;
    input.type = 'text';
    input.autocomplete = 'off';
    input.setAttribute('readonly', 'readonly');
    input.setAttribute('inputmode', 'text');
    input.setAttribute('data-lpignore', 'true');
    input.setAttribute('data-1p-ignore', 'true');
    input.setAttribute('data-form-type', 'other');
    input.classList.add('rrhh-password-masked');
    const wrap = document.createElement('div');
    wrap.className = 'input-group';
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-outline-secondary';
    btn.title = 'Mostrar u ocultar contrase\u00f1a';
    btn.innerHTML = '<i class="fa fa-eye"></i>';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    wrap.appendChild(btn);
    input.dataset.passwordToggleReady = '1';
    input.addEventListener('focus', function () {
      input.removeAttribute('readonly');
    }, { once: true });
    btn.addEventListener('click', function () {
      const masked = input.classList.toggle('rrhh-password-masked');
      btn.innerHTML = masked ? '<i class="fa fa-eye"></i>' : '<i class="fa fa-eye-slash"></i>';
    });
  }

  function initRrhhDatepickers() {
    if (typeof flatpickr === 'undefined') return;
    const hoy = new Date();
    const anioActual = hoy.getFullYear();
    const fechaReferenciaNacimiento = `${anioActual - 30}-01-01`;
    const anioMinLaboral = anioActual - 20;
    const anioMaxLaboral = anioActual + 5;

    function prepararSelectorAnioNacimiento(instance, minYear = anioActual - 100, maxYear = anioActual) {
      const calendar = instance?.calendarContainer;
      if (!calendar || calendar.querySelector('.rrhh-year-select')) return;
      calendar.classList.add('rrhh-flatpickr-calendar');
      const currentMonth = calendar.querySelector('.flatpickr-current-month');
      if (!currentMonth) return;
      const select = document.createElement('select');
      select.className = 'rrhh-year-select';
      select.setAttribute('aria-label', 'Año de nacimiento');
      const selectedYear = instance.currentYear || maxYear;
      for (let year = maxYear; year >= minYear; year--) {
        const option = document.createElement('option');
        option.value = String(year);
        option.textContent = String(year);
        if (year === selectedYear) option.selected = true;
        select.appendChild(option);
      }
      select.addEventListener('change', function () {
        const year = Number(this.value);
        if (!Number.isFinite(year)) return;
        instance.changeYear(Math.max(minYear, Math.min(maxYear, year)));
      });
      currentMonth.appendChild(select);
    }

    function sincronizarSelectorAnioNacimiento(instance) {
      const select = instance?.calendarContainer?.querySelector('.rrhh-year-select');
      if (select && String(select.value) !== String(instance.currentYear)) {
        select.value = String(instance.currentYear);
      }
    }

    form.querySelectorAll('.rrhh-date').forEach(input => {
      if (input._flatpickr) return;
      const esNacimiento = input.name === 'persona.fecha_nacimiento';
      const opciones = {
        dateFormat: 'Y-m-d',
        allowInput: false,
        clickOpens: true,
        appendTo: document.body,
        monthSelectorType: 'dropdown',
        locale: (flatpickr.l10ns && flatpickr.l10ns.es) ? flatpickr.l10ns.es : undefined,
        onOpen: function (_, __, instance) {
          instance.calendarContainer.classList.add('rrhh-flatpickr-calendar');
          if (esNacimiento) {
            if (!input.value && !instance.selectedDates.length) {
              instance.jumpToDate(fechaReferenciaNacimiento, false);
            }
            prepararSelectorAnioNacimiento(instance, anioActual - 100, anioActual);
          } else {
            prepararSelectorAnioNacimiento(instance, anioMinLaboral, anioMaxLaboral);
          }
          sincronizarSelectorAnioNacimiento(instance);
        },
        onMonthChange: function (_, __, instance) {
          sincronizarSelectorAnioNacimiento(instance);
        },
        onYearChange: function (_, __, instance) {
          sincronizarSelectorAnioNacimiento(instance);
        }
      };
      if (esNacimiento) {
        opciones.maxDate = 'today';
        opciones.minDate = `${anioActual - 100}-01-01`;
      }
      flatpickr(input, opciones);
    });
  }

  function pintarRepeats(data) {
    Object.keys(templates).forEach(type => {
      const list = form.querySelector(`[data-rrhh-list="${type}"]`);
      if (list) list.innerHTML = '';
      const rows = Array.isArray(data?.[type]) ? data[type] : [];
      if (!rows.length) {
        addRow(type);
        return;
      }
      rows.forEach(row => addRow(type, row));
    });
  }

  function rrhhSelectText(selectId) {
    const select = document.getElementById(selectId);
    if (!select || !select.value) return '';
    const option = select.options[select.selectedIndex];
    return (option?.dataset.nombre || option?.textContent || '').trim();
  }

  function setRrhhHiddenText(selectId, inputId) {
    const input = document.getElementById(inputId);
    if (input) input.value = rrhhSelectText(selectId);
  }

  function setFormValueByName(name, value) {
    const input = form.querySelector(`[name="${name}"]`);
    if (!input) return;
    input.value = value == null ? '' : String(value);
  }

  function setSelectValue(selectId, value) {
    const select = document.getElementById(selectId);
    if (!select) return;
    select.value = value == null ? '' : String(value);
    refreshRrhhSelect(selectId);
  }

  function ensureSelectOptionRrhh(selectId, value, label) {
    const select = document.getElementById(selectId);
    const val = value == null ? '' : String(value);
    if (!select || !val) return;
    if (Array.from(select.options).some(option => String(option.value) === val)) return;
    const option = document.createElement('option');
    option.value = val;
    option.textContent = String(label || val);
    option.dataset.nombre = option.textContent;
    select.appendChild(option);
    select.disabled = false;
  }

  function depDireccionIdRrhh(dep) {
    return dep?.id_direccion || dep?.direccion_id || dep?.id_direccion_organizacion || dep?.id_direccion_organizacional || '';
  }

  function depDireccionNombreRrhh(dep) {
    return dep?.nombre_direccion || dep?.direccion_nombre || dep?.direccion_organizacional || dep?.direccion || '';
  }

  function depEmpresaIdRrhh(dep) {
    return dep?.id_empresa || dep?.empresa_id || '';
  }

  function depEmpresaNombreRrhh(dep) {
    return dep?.nombre_empresa || dep?.empresa_nombre || dep?.nombre_comercial_empresa || '';
  }

  function resolverEmpresaIdRrhh(data) {
    const empresaGuardada = data?.rrhh?.empresa_id || data?.rrhh?.id_empresa || '';
    const direccionGuardada = resolverDireccionIdRrhh(data);
    const departamentoGuardado = data?.rrhh?.departamento_id || '';
    const areaGuardada = data?.rrhh?.area_id || '';
    const deps = rrhhDepartamentosBackend();

    if (empresaGuardada && deps.some(dep => String(depEmpresaIdRrhh(dep)) === String(empresaGuardada))) {
      return empresaGuardada;
    }

    const depDesdeDepartamento = deps.find(dep => String(dep.id || dep.departamento_id || '') === String(departamentoGuardado));
    if (depDesdeDepartamento && depEmpresaIdRrhh(depDesdeDepartamento)) {
      return depEmpresaIdRrhh(depDesdeDepartamento);
    }

    const depDesdeArea = deps.find(dep => String(dep.id_departamento_organizacional || dep.departamento_organizacional_id || '') === String(areaGuardada));
    if (depDesdeArea && depEmpresaIdRrhh(depDesdeArea)) {
      return depEmpresaIdRrhh(depDesdeArea);
    }

    const depDesdeDireccion = deps.find(dep => String(depDireccionIdRrhh(dep) || '') === String(direccionGuardada));
    return depDesdeDireccion ? depEmpresaIdRrhh(depDesdeDireccion) : empresaGuardada;
  }

  function resolverDireccionIdRrhh(data) {
    const direccionGuardada = data?.rrhh?.direccion_id || '';
    const departamentoGuardado = data?.rrhh?.departamento_id || '';
    const areaGuardada = data?.rrhh?.area_id || '';
    const deps = rrhhDepartamentosBackend();

    if (direccionGuardada && deps.some(dep => String(depDireccionIdRrhh(dep)) === String(direccionGuardada))) {
      return direccionGuardada;
    }

    const depDesdeDepartamento = deps.find(dep => String(dep.id || dep.departamento_id || '') === String(departamentoGuardado));
    if (depDesdeDepartamento && depDireccionIdRrhh(depDesdeDepartamento)) {
      return depDireccionIdRrhh(depDesdeDepartamento);
    }

    const depDesdeArea = deps.find(dep => String(dep.id_departamento_organizacional || dep.departamento_organizacional_id || '') === String(areaGuardada));
    return depDesdeArea ? depDireccionIdRrhh(depDesdeArea) : direccionGuardada;
  }

  function resolverAreaIdRrhh(data) {
    const areaGuardada = data?.rrhh?.area_id || '';
    const departamentoGuardado = data?.rrhh?.departamento_id || '';
    const deps = rrhhDepartamentosBackend();

    if (areaGuardada && deps.some(dep => String(dep.id_departamento_organizacional || dep.departamento_organizacional_id || '') === String(areaGuardada))) {
      return areaGuardada;
    }

    const depDesdeDepartamento = deps.find(dep => String(dep.id || dep.departamento_id || '') === String(departamentoGuardado));
    if (depDesdeDepartamento) {
      return depDesdeDepartamento.id_departamento_organizacional || depDesdeDepartamento.departamento_organizacional_id || '';
    }

    const depDesdeAreaVieja = deps.find(dep => String(dep.id || dep.departamento_id || '') === String(areaGuardada));
    if (depDesdeAreaVieja) {
      return depDesdeAreaVieja.id_departamento_organizacional || depDesdeAreaVieja.departamento_organizacional_id || '';
    }

    return areaGuardada;
  }

  function resolverDepartamentoIdRrhh(data) {
    const departamentoGuardado = data?.rrhh?.departamento_id || '';
    const areaGuardada = data?.rrhh?.area_id || '';
    const deps = rrhhDepartamentosBackend();

    if (departamentoGuardado && deps.some(dep => String(dep.id || dep.departamento_id || '') === String(departamentoGuardado))) {
      return departamentoGuardado;
    }

    const depDesdeAreaVieja = deps.find(dep => String(dep.id || dep.departamento_id || '') === String(areaGuardada));
    return depDesdeAreaVieja ? (depDesdeAreaVieja.id || depDesdeAreaVieja.departamento_id || '') : departamentoGuardado;
  }

  async function precargarSelectsLaboralesRrhh(data) {
    const rrhh = data?.rrhh || {};
    const empresaId = resolverEmpresaIdRrhh(data);
    const direccionId = resolverDireccionIdRrhh(data);
    const areaId = resolverAreaIdRrhh(data);
    const departamentoId = resolverDepartamentoIdRrhh(data);
    const puestoId = rrhh.puesto_id || '';
    const jefeId = rrhh.jefe_id || '';

    form.dataset.rrhhPreloading = '1';
    try {
      fillRrhhPaises();
      setSelectValue('rrhh_pais_id', data?.persona?.id_pais || '');

      fillRrhhEmpresas();
      ensureSelectOptionRrhh('rrhh_empresa_id', empresaId, rrhh.empresa_texto || rrhh.nombre_empresa);
      setSelectValue('rrhh_empresa_id', empresaId);

      fillRrhhDirecciones();
      ensureSelectOptionRrhh('rrhh_direccion_id', direccionId, rrhh.direccion_texto || rrhh.direccion_organizacional);
      setSelectValue('rrhh_direccion_id', direccionId);

      fillRrhhAreas();
      ensureSelectOptionRrhh('rrhh_area_id', areaId, rrhh.area_texto);
      setSelectValue('rrhh_area_id', areaId);

      fillRrhhDepartamentos(departamentoId);
      ensureSelectOptionRrhh('rrhh_departamento_id', departamentoId, rrhh.departamento_texto);
      setSelectValue('rrhh_departamento_id', departamentoId);

      await fillRrhhPuestos(puestoId, rrhh.puesto_texto);
      ensureSelectOptionRrhh('rrhh_puesto_id', puestoId, rrhh.puesto_texto);
      setSelectValue('rrhh_puesto_id', puestoId);

      await fillRrhhJefes(jefeId, rrhh.jefe_directo_texto);
      ensureSelectOptionRrhh('rrhh_jefe_id', jefeId, rrhh.jefe_directo_texto);
      setSelectValue('rrhh_jefe_id', jefeId);

      setRrhhHiddenText('rrhh_direccion_id', 'rrhh_direccion_texto');
      setRrhhHiddenText('rrhh_empresa_id', 'rrhh_empresa_texto');
      setRrhhHiddenText('rrhh_area_id', 'rrhh_area_texto');
      setRrhhHiddenText('rrhh_departamento_id', 'rrhh_departamento_texto');
      setRrhhHiddenText('rrhh_puesto_id', 'rrhh_puesto_texto');
      setRrhhHiddenText('rrhh_jefe_id', 'rrhh_jefe_directo_texto');
    } finally {
      delete form.dataset.rrhhPreloading;
    }
  }

  async function cargarDatosEnModalRrhh(data) {
    delete form.dataset.rrhhDataLoaded;
    form.reset();
    pintarRepeats(data || {});

    Object.entries(data?.persona || {}).forEach(([key, value]) => setFormValueByName(`persona.${key}`, value));
    Object.entries(data?.rrhh || {}).forEach(([key, value]) => setFormValueByName(`rrhh.${key}`, value));
    Object.entries(data?.nomina || {}).forEach(([key, value]) => setFormValueByName(`nomina.${key}`, value));
    setFormValueByName('observaciones', data?.observaciones || '');
    aplicarEstadoSalarioSensibleRrhh(data?.salario_sensible || {});

    initRrhhDatepickers();
    await precargarSelectsLaboralesRrhh(data || {});
    initRrhhWizardMarkup();
    actualizarRrhhWizard();
    form.dataset.rrhhDataLoaded = '1';
  }

  function prepararCargaEditarRrhh(idPersona) {
    limpiarPruebasExpedienteRrhh(false);
    delete form.dataset.rrhhDataLoaded;
    form.querySelectorAll('.rrhh-date').forEach(input => {
      if (input._flatpickr) input._flatpickr.clear();
    });
    form.reset();
    resetRepeats();
    setModoRrhh('editar', idPersona);
    form.querySelector('[data-bs-target="#rrhhTabPersona"]')?.click();
    initRrhhWizardMarkup();
    actualizarRrhhWizard();
  }

  window.abrirEditarRrhh = async function (idPersona) {
    if (!idPersona) return;
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modal, modalOptionsRrhh);
    const loadToken = `${idPersona}:${Date.now()}`;
    form.dataset.rrhhLoadToken = loadToken;
    prepararCargaEditarRrhh(idPersona);
    setRrhhModalLoading(true, 'Cargando datos RR.HH...');
    modalInstance.show();

    try {
      const response = await fetch('/CapHum/obtenerUsuarioRrhh', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ id_persona: idPersona })
      });
      const data = await response.json();
      if (!data.success) {
        throw new Error(data.mensaje || 'No se pudo cargar la informacion RR.HH.');
      }
      if (form.dataset.rrhhLoadToken !== loadToken) return;
      await cargarDatosEnModalRrhh(data.datos || {});
      setRrhhModalLoading(false);
    } catch (error) {
      if (form.dataset.rrhhLoadToken === loadToken) {
        setRrhhModalLoading(false);
        modalInstance.hide();
      }
      Swal.fire('Error', error.message || 'No se pudo cargar la informacion RR.HH.', 'error');
    }
  };

  function refreshRrhhSelect(selectId) {
    if (typeof window.refreshSelectBuscador === 'function') {
      window.refreshSelectBuscador(selectId);
    }
  }

  function ordenarCamposLaboralesRrhh() {
    const puesto = document.getElementById('rrhh_puesto_id')?.closest('[class*="col-md-"]');
    const jefe = document.getElementById('rrhh_jefe_id')?.closest('[class*="col-md-"]');
    if (puesto && jefe && jefe.previousElementSibling !== puesto) {
      puesto.after(jefe);
    }
  }

  function escapeRrhhHtml(value) {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
  }

  function rrhhImportDocsFormData(options = {}) {
    const fd = new FormData();
    const batchId = String(rrhhImportDocsAnalisis?.batch_id || '').trim();
    const usarBatch = options.usarBatch !== false;
    const cacheLote = options.cacheLote !== false;
    const files = Array.isArray(options.files) ? options.files : rrhhImportDocsFiles;
    const sourceOffset = Number(options.sourceOffset || 0);
    const manualesGlobales = options.manualesGlobales || null;
    fd.append('cache_lote', cacheLote ? '1' : '0');
    if (batchId && usarBatch) {
      fd.append('batch_id', batchId);
    } else if (files.length) {
      files.forEach(file => {
        fd.append('archivos[]', file, file.name);
        fd.append('rutas_relativas[]', file.webkitRelativePath || file.name);
      });
    }
    if (manualesGlobales instanceof Map) {
      files.forEach((file, localIndex) => {
        const globalIndex = Number(file.__rrhhGlobalIndex ?? (sourceOffset + localIndex));
        const idDocumento = Number(manualesGlobales.get(globalIndex) || 0);
        if (idDocumento > 0) {
          fd.append(`documentos_manual[${localIndex}]`, idDocumento);
        }
      });
    } else {
      (rrhhImportDocsAnalisis?.items || []).forEach(item => {
        if (item.documento_manual && Number(item.id_documento || 0) > 0) {
          const localIndex = Math.max(0, Number(item.source_index || 0) - sourceOffset);
          fd.append(`documentos_manual[${localIndex}]`, Number(item.id_documento || 0));
        }
      });
    }
    return fd;
  }

  function rrhhImportDocsResumenVacio() {
    return {
      total: 0,
      listo: 0,
      importado: 0,
      persona_no_encontrada: 0,
      persona_ambigua: 0,
      persona_no_coincide: 0,
      documento_no_reconocido: 0,
      ya_existe: 0,
      duplicado_lote: 0,
      omitido: 0,
      error: 0,
      documento_sin_permiso: 0
    };
  }

  function rrhhImportDocsSumarResumen(destino, fuente) {
    const out = destino || rrhhImportDocsResumenVacio();
    Object.keys(rrhhImportDocsResumenVacio()).forEach(key => {
      out[key] = Number(out[key] || 0) + Number(fuente?.[key] || 0);
    });
    return out;
  }

  function rrhhImportDocsNormalizarItemsBatch(items, sourceOffset) {
    return Array.from(items || []).map(item => ({
      ...item,
      source_index: Number(item.source_index || 0) + Number(sourceOffset || 0)
    }));
  }

  function rrhhImportDocsManualesGlobales() {
    const manuales = new Map();
    (rrhhImportDocsAnalisis?.items || []).forEach(item => {
      const sourceIndex = Number(item.source_index || 0);
      const idDocumento = Number(item.id_documento || 0);
      if (item.documento_manual && idDocumento > 0) {
        manuales.set(sourceIndex, idDocumento);
      }
    });
    return manuales;
  }

  function rrhhImportDocsEsZip(file) {
    const texto = [
      file?.name || '',
      file?.webkitRelativePath || '',
      file?.type || ''
    ].join(' ');
    return /\.zip\b/i.test(texto) || /zip/i.test(String(file?.type || ''));
  }

  function rrhhImportDocsCrearBatches(files) {
    const batches = [];
    let actual = [];
    let bytes = 0;
    Array.from(files || []).forEach((file, index) => {
      file.__rrhhGlobalIndex = index;
      const size = Number(file.size || 0);
      const cerrar = actual.length > 0
        && (actual.length >= RRHH_IMPORT_DOCS_MAX_FILES || (bytes + size) > RRHH_IMPORT_DOCS_MAX_BYTES);
      if (cerrar) {
        batches.push({ files: actual, sourceOffset: Number(actual[0].__rrhhGlobalIndex || 0), bytes });
        actual = [];
        bytes = 0;
      }
      actual.push(file);
      bytes += size;
    });
    if (actual.length) {
      batches.push({ files: actual, sourceOffset: Number(actual[0].__rrhhGlobalIndex || 0), bytes });
    }
    return batches;
  }

  function rrhhImportDocsTieneZipGrande(files) {
    return Array.from(files || []).some(file => rrhhImportDocsEsZip(file) && Number(file.size || 0) > RRHH_IMPORT_DOCS_MAX_ZIP_BYTES);
  }

  function rrhhImportDocsPrimerArchivoGrande(files) {
    return Array.from(files || []).find(file => !rrhhImportDocsEsZip(file) && Number(file.size || 0) > RRHH_IMPORT_DOCS_MAX_BYTES) || null;
  }

  function rrhhImportDocsSetFiles(fileList) {
    rrhhImportDocsFiles = Array.from(fileList || []);
    rrhhImportDocsAnalisis = null;
    rrhhImportDocsRenderResumen(null);
    rrhhImportDocsRenderTabla([]);
    const total = rrhhImportDocsFiles.length;
    const peso = rrhhImportDocsFiles.reduce((sum, file) => sum + (file.size || 0), 0);
    const pesoMb = (peso / 1024 / 1024).toFixed(1);
    const limiteMb = Math.floor(RRHH_IMPORT_DOCS_MAX_BYTES / 1024 / 1024);
    if (rrhhImportDocsTieneZipGrande(rrhhImportDocsFiles)) {
      if (rrhhImportDocsSeleccionResumen) {
        rrhhImportDocsSeleccionResumen.textContent = 'El ZIP supera el tamano permitido para una sola carga. Descomprime el archivo y usa Elegir carpeta.';
      }
      if (btnRrhhImportImportar) btnRrhhImportImportar.disabled = true;
      Swal.fire(
        'ZIP demasiado grande',
        'Este ZIP supera el tamano permitido para una sola carga. Descomprime el archivo y usa "Elegir carpeta"; el sistema lo analizara por lotes automaticamente.',
        'warning'
      );
      return;
    }
    const archivoGrande = rrhhImportDocsPrimerArchivoGrande(rrhhImportDocsFiles);
    if (archivoGrande) {
      if (rrhhImportDocsSeleccionResumen) {
        rrhhImportDocsSeleccionResumen.textContent = `El archivo ${archivoGrande.name} pesa mas de ${limiteMb} MB.`;
      }
      if (btnRrhhImportImportar) btnRrhhImportImportar.disabled = true;
      Swal.fire('Archivo demasiado grande', `El archivo "${archivoGrande.name}" supera ${limiteMb} MB.`, 'warning');
      return;
    }
    if (rrhhImportDocsSeleccionResumen) {
      const batches = rrhhImportDocsCrearBatches(rrhhImportDocsFiles);
      rrhhImportDocsSeleccionResumen.textContent = total
        ? `${total} archivo(s) seleccionado(s), ${pesoMb} MB${batches.length > 1 ? `. Se procesaran en ${batches.length} lotes.` : '.'}`
        : 'No se han seleccionado archivos.';
    }
    if (btnRrhhImportImportar) btnRrhhImportImportar.disabled = true;
    if (total > 0) {
      setTimeout(rrhhImportDocsAnalizar, 0);
    }
  }

  function rrhhImportDocsLimpiarSeleccion() {
    rrhhImportDocsSetFiles([]);
    if (inputRrhhImportArchivos) inputRrhhImportArchivos.value = '';
    if (inputRrhhImportCarpeta) inputRrhhImportCarpeta.value = '';
  }

  function rrhhImportDocsBadge(estado, item = null) {
    if (item && item.documento_otros_automatico) {
      return '<span class="badge bg-secondary">Sin tipo</span>';
    }
    const mapa = {
      listo: ['bg-success', 'Listo'],
      importado: ['bg-primary', 'Importado'],
      persona_no_encontrada: ['bg-danger', 'Sin persona'],
      persona_ambigua: ['bg-warning text-dark', 'Ambiguo'],
      documento_no_reconocido: ['bg-secondary', 'Sin tipo'],
      ya_existe: ['bg-info text-dark', 'Ya existe'],
      duplicado_lote: ['bg-warning text-dark', 'Duplicado'],
      error: ['bg-danger', 'Error']
    };
    const cfg = mapa[estado] || ['bg-secondary', estado || 'Pendiente'];
    return `<span class="badge ${cfg[0]}">${escapeRrhhHtml(cfg[1])}</span>`;
  }

  function rrhhImportDocsRenderResumen(resumen) {
    if (!rrhhImportDocsResumen) return;
    if (!resumen) {
      rrhhImportDocsResumen.innerHTML = '';
      return;
    }
    const chips = [
      ['Total', resumen.total || 0, 'bg-dark'],
      ['Listos', resumen.listo || 0, 'bg-success'],
      ['Importados', resumen.importado || 0, 'bg-primary'],
      ['Ambiguos', resumen.persona_ambigua || 0, 'bg-warning text-dark'],
      ['Sin persona', resumen.persona_no_encontrada || 0, 'bg-danger'],
      ['Sin tipo', resumen.documento_no_reconocido || 0, 'bg-secondary'],
      ['Ya existe', resumen.ya_existe || 0, 'bg-info text-dark'],
      ['Duplicados', resumen.duplicado_lote || 0, 'bg-warning text-dark'],
      ['Errores', resumen.error || 0, 'bg-danger']
    ];
    rrhhImportDocsResumen.innerHTML = chips
      .filter(([, valor], index) => index === 0 || Number(valor) > 0)
      .map(([label, valor, cls]) => `<span class="badge ${cls}">${escapeRrhhHtml(label)}: ${escapeRrhhHtml(valor)}</span>`)
      .join('');
  }

  function rrhhImportDocsRenderTabla(items) {
    if (!rrhhImportDocsTabla) return;
    if (!items || items.length === 0) {
      rrhhImportDocsTabla.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Selecciona archivos o una carpeta para analizarlos automaticamente.</td></tr>';
      return;
    }
    const catalogo = Array.isArray(rrhhImportDocsAnalisis?.catalogo) ? rrhhImportDocsAnalisis.catalogo : [];
    let personaAnterior = '';
    rrhhImportDocsTabla.innerHTML = items.map(item => {
      const nombrePersona = escapeRrhhHtml(item.persona || '');
      const numeroEmpleado = escapeRrhhHtml(item.numero_empleado || '');
      const carpetaPersona = escapeRrhhHtml(item.carpeta_persona || 'N/A');
      const personaKey = item.id_persona ? `id:${item.id_persona}` : `folder:${item.carpeta_persona || ''}`;
      const separarPersona = personaAnterior !== '' && personaKey !== personaAnterior;
      personaAnterior = personaKey;
      const persona = item.persona
        ? `${nombrePersona} ${numeroEmpleado ? `(No. ${numeroEmpleado})` : ''}<br><small class="text-muted">Score ${escapeRrhhHtml(item.score_persona || 0)}%</small>`
        : `<span class="text-muted">${carpetaPersona}</span>`;
      const idDocumento = Number(item.id_documento || 0);
      const opciones = [
        '<option value="">Seleccione tipo</option>',
        ...catalogo.map(doc => {
          const selected = Number(doc.id || 0) === idDocumento ? 'selected' : '';
          return `<option value="${Number(doc.id || 0)}" ${selected}>${escapeRrhhHtml(doc.nombre || '')}</option>`;
        })
      ].join('');
      const tipoDocumento = `
        <select class="form-select form-select-sm" data-rrhh-import-doc-type="${Number(item.source_index || 0)}">
          ${opciones}
        </select>
        ${item.documento_manual ? '<div class="text-primary small mt-1">Seleccion manual</div>' : ''}
      `;
      const detalle = item.razon || '';
      return `<tr class="${separarPersona ? 'rrhh-import-person-separator' : ''}">
        <td>${rrhhImportDocsBadge(item.estado, item)}</td>
        <td>${persona}</td>
        <td>${tipoDocumento}</td>
        <td><span class="text-break">${escapeRrhhHtml(item.ruta || item.archivo || '')}</span></td>
        <td>${escapeRrhhHtml(detalle)}</td>
        <td class="text-center">
          <button type="button" class="btn btn-sm btn-outline-primary" data-rrhh-import-preview="${Number(item.source_index || 0)}" title="Abrir documento">
            <i class="fa fa-eye"></i>
          </button>
        </td>
      </tr>`;
    }).join('');
  }

  function rrhhImportDocsSetLoading(loading, texto) {
    [btnRrhhImportImportar, btnRrhhImportSeleccionarArchivos, btnRrhhImportSeleccionarCarpeta].forEach(btn => {
      if (btn) btn.disabled = !!loading;
    });
    if (btnRrhhImportImportar && !loading) {
      const listos = rrhhImportDocsAnalisis?.resumen?.listo || 0;
      btnRrhhImportImportar.disabled = listos <= 0;
    }
    if (rrhhImportDocsSeleccionResumen && texto) {
      rrhhImportDocsSeleccionResumen.textContent = texto;
    }
  }

  async function rrhhImportDocsEnviar(endpoint, options = {}) {
    let res;
    try {
      res = await fetch(endpoint, {
        method: 'POST',
        body: rrhhImportDocsFormData(options),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        cache: 'no-store'
      });
    } catch (err) {
      throw new Error('No se pudo conectar con el servidor. La carga se procesa por lotes; vuelve a seleccionar la carpeta si el navegador libero los archivos.');
    }
    const contentType = res.headers.get('content-type') || '';
    if (!contentType.includes('application/json')) {
      const texto = await res.text();
      throw new Error(texto ? texto.slice(0, 250) : 'Respuesta no JSON del servidor.');
    }
    const json = await res.json();
    if (!json.success) {
      const error = new Error(json.mensaje || 'La operacion no se completo.');
      error.codigo = json.codigo || json?.datos?.codigo || '';
      throw error;
    }
    return json.datos || {};
  }

  async function rrhhImportDocsAbrirDocumento(sourceIndex) {
    try {
      const batchId = String(rrhhImportDocsAnalisis?.batch_id || '').trim();
      const globalIndex = Number(sourceIndex || 0);
      const file = rrhhImportDocsFiles[globalIndex] || null;
      if (!batchId && !file) {
        throw new Error('No se encontro el archivo seleccionado en la carga actual.');
      }
      const fd = batchId
        ? rrhhImportDocsFormData()
        : rrhhImportDocsFormData({ files: file ? [file] : [], sourceOffset: globalIndex, usarBatch: false });
      fd.append('source_index', batchId ? globalIndex : 0);
      const res = await fetch('/caphum/previsualizarImportacionDocumentosRrhh', {
        method: 'POST',
        body: fd,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });
      if (!res.ok) {
        const texto = await res.text();
        throw new Error(texto || 'No se pudo abrir el documento.');
      }
      const blob = await res.blob();
      if (rrhhImportPreviewUrl) {
        URL.revokeObjectURL(rrhhImportPreviewUrl);
        rrhhImportPreviewUrl = '';
      }
      const url = URL.createObjectURL(blob);
      rrhhImportPreviewUrl = url;
      if (rrhhImportPreviewFrame) {
        rrhhImportPreviewFrame.src = url;
      }
      const item = (rrhhImportDocsAnalisis?.items || []).find(row => Number(row.source_index || 0) === Number(sourceIndex || 0));
      if (modalRrhhImportPreviewLabel) {
        modalRrhhImportPreviewLabel.textContent = item?.archivo || item?.ruta || 'Documento';
      }
      if (modalRrhhImportPreview) {
        bootstrap.Modal.getOrCreateInstance(modalRrhhImportPreview)?.show();
      }
    } catch (error) {
      Swal.fire('Abrir documento', error.message || 'No se pudo abrir el documento.', 'error');
    }
  }

  async function rrhhImportDocsAnalizar() {
    if (!rrhhImportDocsFiles.length) return;
    try {
      const batches = rrhhImportDocsCrearBatches(rrhhImportDocsFiles);
      rrhhImportDocsSetLoading(true, batches.length > 1 ? `Analizando lote 1 de ${batches.length}...` : 'Analizando documentos...');
      const combinado = {
        items: [],
        resumen: rrhhImportDocsResumenVacio(),
        catalogo: [],
        batch_id: ''
      };
      for (let i = 0; i < batches.length; i++) {
        const batch = batches[i];
        if (batches.length > 1) {
          rrhhImportDocsSetLoading(true, `Analizando lote ${i + 1} de ${batches.length} (${batch.files.length} archivo(s))...`);
        }
        const parcial = await rrhhImportDocsEnviar('/caphum/analizarImportacionDocumentosRrhh', {
          files: batch.files,
          sourceOffset: batch.sourceOffset,
          usarBatch: false,
          cacheLote: false
        });
        if (!combinado.catalogo.length && Array.isArray(parcial.catalogo)) {
          combinado.catalogo = parcial.catalogo;
        }
        if (batches.length === 1 && parcial.batch_id) {
          combinado.batch_id = parcial.batch_id;
        }
        combinado.items.push(...rrhhImportDocsNormalizarItemsBatch(parcial.items || [], batch.sourceOffset));
        rrhhImportDocsSumarResumen(combinado.resumen, parcial.resumen || {});
      }
      rrhhImportDocsAnalisis = combinado;
      rrhhImportDocsRenderResumen(rrhhImportDocsAnalisis.resumen);
      rrhhImportDocsRenderTabla(rrhhImportDocsAnalisis.items || []);
      const listos = rrhhImportDocsAnalisis?.resumen?.listo || 0;
      if (rrhhImportDocsSeleccionResumen) {
        rrhhImportDocsSeleccionResumen.textContent = `Analisis listo. ${listos} documento(s) pueden importarse${batches.length > 1 ? ` en ${batches.length} lotes` : ''}.`;
      }
      if (btnRrhhImportImportar) btnRrhhImportImportar.disabled = listos <= 0;
    } catch (error) {
      Swal.fire('Importar documentos', error.message || 'No se pudo analizar la selección.', 'error');
    } finally {
      rrhhImportDocsSetLoading(false);
    }
  }

  async function rrhhImportDocsImportar() {
    const listos = rrhhImportDocsAnalisis?.resumen?.listo || 0;
    if (!listos) return;
    const confirm = await Swal.fire({
      icon: 'question',
      title: 'Importar documentos',
      text: `Se importarán ${listos} documento(s) listos. Los ambiguos, duplicados o ya existentes se omitirán.`,
      showCancelButton: true,
      confirmButtonText: 'Importar',
      cancelButtonText: 'Cancelar'
    });
    if (!confirm.isConfirmed) return;

    try {
      const batches = rrhhImportDocsCrearBatches(rrhhImportDocsFiles);
      const manualesGlobales = rrhhImportDocsManualesGlobales();
      rrhhImportDocsSetLoading(true, batches.length > 1 ? `Importando lote 1 de ${batches.length}...` : 'Importando documentos...');
      let resultado;
      if (batches.length === 1 && String(rrhhImportDocsAnalisis?.batch_id || '').trim()) {
        try {
          resultado = await rrhhImportDocsEnviar('/caphum/importarDocumentosRrhh');
        } catch (error) {
          if (error.codigo !== 'lote_temporal_no_disponible' || !rrhhImportDocsFiles.length) {
            throw error;
          }
          rrhhImportDocsSetLoading(true, 'Reintentando con los archivos seleccionados...');
          resultado = await rrhhImportDocsEnviar('/caphum/importarDocumentosRrhh', { usarBatch: false });
        }
      } else {
        resultado = {
          items: [],
          resumen: rrhhImportDocsResumenVacio(),
          importados: 0,
          batch_id: ''
        };
        for (let i = 0; i < batches.length; i++) {
          const batch = batches[i];
          if (batches.length > 1) {
            rrhhImportDocsSetLoading(true, `Importando lote ${i + 1} de ${batches.length} (${batch.files.length} archivo(s))...`);
          }
          const parcial = await rrhhImportDocsEnviar('/caphum/importarDocumentosRrhh', {
            files: batch.files,
            sourceOffset: batch.sourceOffset,
            usarBatch: false,
            manualesGlobales
          });
          resultado.items.push(...rrhhImportDocsNormalizarItemsBatch(parcial.items || [], batch.sourceOffset));
          rrhhImportDocsSumarResumen(resultado.resumen, parcial.resumen || {});
          resultado.importados += Number(parcial.importados || 0);
        }
      }
      rrhhImportDocsAnalisis = resultado;
      rrhhImportDocsRenderResumen(rrhhImportDocsAnalisis.resumen);
      rrhhImportDocsRenderTabla(rrhhImportDocsAnalisis.items || []);
      if (rrhhImportDocsSeleccionResumen) {
        rrhhImportDocsSeleccionResumen.textContent = `Importación finalizada. ${rrhhImportDocsAnalisis.importados || 0} documento(s) importado(s).`;
      }
      Swal.fire('Importación finalizada', `${rrhhImportDocsAnalisis.importados || 0} documento(s) importado(s).`, 'success');
    } catch (error) {
      Swal.fire('Importar documentos', error.message || 'No se pudo importar la selección.', 'error');
    } finally {
      rrhhImportDocsSetLoading(false);
    }
  }

  function optionHtml(value, label, extraAttrs) {
    const attrs = extraAttrs ? ` ${extraAttrs}` : '';
    const safeValue = escapeRrhhHtml(value);
    const safeLabel = escapeRrhhHtml(label);
    return `<option value="${safeValue}" data-nombre="${safeLabel}"${attrs}>${safeLabel}</option>`;
  }

  function nombreSegmentoOperativoVisibleRrhh(nombre) {
    const original = String(nombre || '').replace(/\s+/g, ' ').trim();
    if (!original) return '';

    const reglasDivisionOperativa = [
      /\s+\(?\d+\s*[-_]\s*\d+\)?$/i,
      /\s+\(?\d+\s+a\s+\d+\)?$/i,
      /\s+\(?\d+\s*al\s*\d+\)?$/i,
      /\s+(?:tramo|bucket|rango)\s+\d+\s*[-_]\s*\d+$/i,
      /\s+(?:tramo|bucket|rango)\s+\d+\s+a\s+\d+$/i
    ];

    let visible = original;
    reglasDivisionOperativa.some(regla => {
      const candidato = visible.replace(regla, '').trim();
      if (candidato && candidato !== visible) {
        visible = candidato;
        return true;
      }
      return false;
    });

    return visible || original;
  }

  function prepararOpcionesDepartamentosRrhh(departamentos, seleccionado = '') {
    const grupos = new Map();
    (Array.isArray(departamentos) ? departamentos : []).forEach(departamento => {
      const id = departamento.id || departamento.departamento_id || '';
      const nombreOriginal = departamento.nombre || departamento.departamento_nombre || '';
      const nombreVisible = nombreSegmentoOperativoVisibleRrhh(nombreOriginal);
      if (!id || !nombreVisible) return;

      const key = normalizarValorFiltro(nombreVisible);
      const actual = grupos.get(key);
      const item = { id: String(id), nombre: nombreVisible };
      if (!actual || String(id) === String(seleccionado)) grupos.set(key, item);
    });

    return Array.from(grupos.values()).sort((a, b) => a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' }));
  }

  function prepararOpcionesPuestosRrhh(puestos, seleccionado = '') {
    const grupos = new Map();
    (Array.isArray(puestos) ? puestos : []).forEach(puesto => {
      const id = puesto.id || puesto.id_puesto || puesto.puesto_id || '';
      const nombreOriginal = puesto.nombre || puesto.nombre_puesto || puesto.puesto_nombre || puesto.puesto || '';
      const nombreVisible = nombreSegmentoOperativoVisibleRrhh(nombreOriginal);
      if (!id || !nombreVisible) return;

      const key = normalizarValorFiltro(nombreVisible);
      const actual = grupos.get(key);
      const item = { id: String(id), nombre: nombreVisible };
      if (!actual || String(id) === String(seleccionado)) grupos.set(key, item);
    });

    return Array.from(grupos.values()).sort((a, b) => a.nombre.localeCompare(b.nombre, 'es', { sensitivity: 'base' }));
  }

  function rrhhDepartamentosBackend() {
    return Array.isArray(window.todosDepartamentosBackend) ? window.todosDepartamentosBackend : [];
  }

  function rrhhPaisesBackend() {
    return Array.isArray(window.paisesActivosBackend) ? window.paisesActivosBackend : [];
  }

  function fillRrhhPaises() {
    const select = document.getElementById('rrhh_pais_id');
    if (!select) return;
    const current = select.value || '';
    select.innerHTML = '<option value="">Seleccione un pa&iacute;s</option>' +
      rrhhPaisesBackend()
        .map(pais => ({
          id: pais.id,
          nombre: pais.nombre || '',
          iso: String(pais.codigo_iso || '').toLowerCase()
        }))
        .filter(pais => pais.id && pais.nombre)
        .map(pais => optionHtml(pais.id, pais.nombre, ` data-iso="${escapeRrhhHtml(pais.iso)}"`))
        .join('');
    if (current && Array.from(select.options).some(option => String(option.value) === String(current))) {
      select.value = current;
    }
    refreshRrhhSelect('rrhh_pais_id');
  }

  function depPertenecePais(dep, idPais) {
    if (!idPais) return false;
    const selectPais = document.getElementById('rrhh_pais_id');
    const optionPais = selectPais?.selectedOptions?.[0] || null;
    const nombrePais = normalizarValorFiltro(optionPais?.textContent || '');
    const isoPais = String(optionPais?.dataset?.iso || '').toLowerCase();
    const depPais = String(dep.id_pais || dep.pais_id || '');
    const depNombrePais = normalizarValorFiltro(dep.nombre_pais || dep.pais || '');
    const depIsoPais = String(dep.codigo_iso_pais || dep.codigo_iso || '').toLowerCase();

    if (depPais && depPais === String(idPais)) return true;
    if (isoPais && depIsoPais && depIsoPais === isoPais) return true;
    if (nombrePais && depNombrePais && depNombrePais === nombrePais) return true;
    return false;
  }

  function fillRrhhEmpresas() {
    const selectEmpresa = document.getElementById('rrhh_empresa_id');
    if (!selectEmpresa) return;

    const idPais = document.getElementById('rrhh_pais_id')?.value || '';
    const empresas = new Map();

    rrhhDepartamentosBackend()
      .filter(dep => depPertenecePais(dep, idPais))
      .forEach(dep => {
        const idEmpresa = depEmpresaIdRrhh(dep);
        const nombreEmpresa = depEmpresaNombreRrhh(dep);
        if (idEmpresa && nombreEmpresa) {
          empresas.set(String(idEmpresa), nombreEmpresa);
        }
      });

    const current = selectEmpresa.value;
    selectEmpresa.disabled = !idPais || empresas.size === 0;
    selectEmpresa.innerHTML = '<option value="">Seleccione una empresa</option>' +
      Array.from(empresas.entries())
        .sort((a, b) => a[1].localeCompare(b[1], 'es', { sensitivity: 'base' }))
        .map(([id, nombre]) => optionHtml(id, nombre))
        .join('');

    if (current && empresas.has(String(current))) {
      selectEmpresa.value = current;
    } else if (empresas.size === 1) {
      selectEmpresa.value = Array.from(empresas.keys())[0];
    } else {
      selectEmpresa.value = '';
    }

    setRrhhHiddenText('rrhh_empresa_id', 'rrhh_empresa_texto');
    refreshRrhhSelect('rrhh_empresa_id');
    if (form.dataset.rrhhPreloading !== '1') {
      fillRrhhDirecciones();
    }
  }

  function fillRrhhDirecciones() {
    const selectDireccion = document.getElementById('rrhh_direccion_id');
    if (!selectDireccion) return;

    const idPais = document.getElementById('rrhh_pais_id')?.value || '';
    const idEmpresa = document.getElementById('rrhh_empresa_id')?.value || '';
    const direcciones = new Map();

    rrhhDepartamentosBackend()
      .filter(dep => depPertenecePais(dep, idPais))
      .filter(dep => String(depEmpresaIdRrhh(dep) || '') === String(idEmpresa || ''))
      .forEach(dep => {
        const idDireccion = depDireccionIdRrhh(dep);
        const nombreDireccion = depDireccionNombreRrhh(dep);
        if (idDireccion && nombreDireccion && nombreDireccion !== 'Sin dirección') {
          direcciones.set(String(idDireccion), nombreDireccion);
        }
      });

    const current = selectDireccion.value;
    selectDireccion.disabled = !idPais || !idEmpresa || direcciones.size === 0;
    selectDireccion.innerHTML = '<option value="">Seleccione una direcci&oacute;n</option>' +
      Array.from(direcciones.entries())
        .sort((a, b) => a[1].localeCompare(b[1], 'es', { sensitivity: 'base' }))
        .map(([id, nombre]) => optionHtml(id, nombre))
        .join('');

    if (current && direcciones.has(String(current))) {
      selectDireccion.value = current;
    } else if (direcciones.size === 1) {
      selectDireccion.value = Array.from(direcciones.keys())[0];
    } else {
      selectDireccion.value = '';
    }

    setRrhhHiddenText('rrhh_direccion_id', 'rrhh_direccion_texto');
    refreshRrhhSelect('rrhh_direccion_id');
    if (form.dataset.rrhhPreloading !== '1') {
      fillRrhhAreas();
    }
  }

  function fillRrhhDepartamentos(seleccionadoForzado = '') {
    const selectArea = document.getElementById('rrhh_area_id');
    const select = document.getElementById('rrhh_departamento_id');
    if (!select) return;
    const current = seleccionadoForzado || select.value;
    const idArea = selectArea?.value || '';
    const idEmpresa = document.getElementById('rrhh_empresa_id')?.value || '';
    const departamentos = [];

    rrhhDepartamentosBackend()
      .filter(dep => depPertenecePais(dep, document.getElementById('rrhh_pais_id')?.value || ''))
      .filter(dep => String(depEmpresaIdRrhh(dep) || '') === String(idEmpresa || ''))
      .filter(dep => String(dep.id_departamento_organizacional || dep.departamento_organizacional_id || '') === String(idArea))
      .forEach(dep => {
        const idDepartamento = dep.id || dep.departamento_id || '';
        const nombreDepartamento = dep.nombre || dep.departamento_nombre || '';
        if (idDepartamento && nombreDepartamento && nombreDepartamento !== 'Sin departamento') {
          departamentos.push({ id: idDepartamento, nombre: nombreDepartamento });
        }
      });
    const opciones = prepararOpcionesDepartamentosRrhh(departamentos, current);

    select.innerHTML = '<option value="">Seleccione un departamento</option>' +
      opciones
        .map(departamento => optionHtml(departamento.id, departamento.nombre))
        .join('');

    select.disabled = !idArea || opciones.length === 0;
    if (current && opciones.some(departamento => String(departamento.id) === String(current))) {
      select.value = current;
    } else if (opciones.length === 1) {
      select.value = opciones[0].id;
    } else {
      select.value = '';
    }
    setRrhhHiddenText('rrhh_departamento_id', 'rrhh_departamento_texto');
    refreshRrhhSelect('rrhh_departamento_id');
    if (form.dataset.rrhhPreloading !== '1') {
      fillRrhhPuestos();
      fillRrhhJefes();
    }
  }

  function fillRrhhAreas() {
    const selectArea = document.getElementById('rrhh_area_id');
    if (!selectArea) return;

    const idPais = document.getElementById('rrhh_pais_id')?.value || '';
    const idEmpresa = document.getElementById('rrhh_empresa_id')?.value || '';
    const idDireccion = document.getElementById('rrhh_direccion_id')?.value || '';
    const areas = new Map();

    rrhhDepartamentosBackend()
      .filter(dep => depPertenecePais(dep, idPais))
      .filter(dep => String(depEmpresaIdRrhh(dep) || '') === String(idEmpresa || ''))
      .filter(dep => String(depDireccionIdRrhh(dep) || '') === String(idDireccion || ''))
      .forEach(dep => {
        const idArea = dep.id_departamento_organizacional || dep.departamento_organizacional_id || '';
        const nombreArea = dep.departamento_organizacional_nombre || dep.nombre_departamento_organizacional || '';
        if (idArea && nombreArea && nombreArea !== 'Sin departamento') {
          areas.set(String(idArea), nombreArea);
        }
      });

    const current = selectArea.value;
    selectArea.disabled = !idPais || !idEmpresa || !idDireccion || areas.size === 0;
    selectArea.innerHTML = '<option value="">Seleccione un &aacute;rea</option>' +
      Array.from(areas.entries())
        .sort((a, b) => a[1].localeCompare(b[1], 'es', { sensitivity: 'base' }))
        .map(([id, nombre]) => optionHtml(id, nombre))
        .join('');
    if (current && areas.has(String(current))) {
      selectArea.value = current;
    } else {
      selectArea.value = '';
    }
    setRrhhHiddenText('rrhh_area_id', 'rrhh_area_texto');
    refreshRrhhSelect('rrhh_area_id');
    if (form.dataset.rrhhPreloading !== '1') {
      fillRrhhDepartamentos();
    }
  }

  function fillRrhhPuestosFallback(idDepartamento, seleccionadoForzado = '') {
    const select = document.getElementById('rrhh_puesto_id');
    if (!select) return;
    const puestos = new Map();
    const seleccionado = seleccionadoForzado || select.value || '';
    usuariosData.forEach(usuario => {
      const lista = Array.isArray(usuario.puestos) ? usuario.puestos : [];
      lista.forEach(puesto => {
        if (String(puesto.id_departamento || '') === String(idDepartamento || '') && puesto.id_puesto && puesto.nombre_puesto && puesto.nombre_puesto !== 'Sin puesto') {
          puestos.set(String(puesto.id_puesto), { id: puesto.id_puesto, nombre: puesto.nombre_puesto });
        }
      });
      if (String(usuario.id_departamento || '') === String(idDepartamento || '') && usuario.id_puesto && usuario.nombre_puesto && usuario.nombre_puesto !== 'Sin puesto') {
        puestos.set(String(usuario.id_puesto), { id: usuario.id_puesto, nombre: usuario.nombre_puesto });
      }
    });
    const opciones = prepararOpcionesPuestosRrhh(Array.from(puestos.values()), seleccionado);
    select.innerHTML = '<option value="">Seleccione un puesto</option>' +
      opciones
        .map(puesto => optionHtml(puesto.id, puesto.nombre))
        .join('');
    if (seleccionado && opciones.some(puesto => String(puesto.id) === String(seleccionado))) {
      select.value = seleccionado;
    }
    select.disabled = !idDepartamento || opciones.length === 0;
    setRrhhHiddenText('rrhh_puesto_id', 'rrhh_puesto_texto');
    refreshRrhhSelect('rrhh_puesto_id');
  }

  async function fillRrhhPuestos(seleccionadoForzado = '', etiquetaSeleccionado = '') {
    const selectDepartamento = document.getElementById('rrhh_departamento_id');
    const selectPuesto = document.getElementById('rrhh_puesto_id');
    if (!selectDepartamento || !selectPuesto) return;
    const idDepartamento = selectDepartamento.value;

    selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';
    selectPuesto.disabled = true;
    setRrhhHiddenText('rrhh_puesto_id', 'rrhh_puesto_texto');
    refreshRrhhSelect('rrhh_puesto_id');
    if (!idDepartamento) return;

    if (seleccionadoForzado) {
      ensureSelectOptionRrhh('rrhh_puesto_id', seleccionadoForzado, etiquetaSeleccionado);
      setSelectValue('rrhh_puesto_id', seleccionadoForzado);
      setRrhhHiddenText('rrhh_puesto_id', 'rrhh_puesto_texto');
    }

    try {
      const response = await fetch('/CapHum/getPuestosParaGestor', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ id_departamento: idDepartamento })
      });
      const data = await response.json();
      if (!data.success || !Array.isArray(data.datos)) {
        fillRrhhPuestosFallback(idDepartamento, seleccionadoForzado);
        return;
      }
      const seleccionado = seleccionadoForzado || selectPuesto.value || '';
      const opciones = prepararOpcionesPuestosRrhh(
        data.datos.map(puesto => ({ id: puesto.id, nombre: puesto.nombre || puesto.puesto_nombre || '' })),
        seleccionado
      );
      selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>' +
        opciones
          .map(puesto => optionHtml(puesto.id, puesto.nombre))
          .join('');
      if (seleccionado && opciones.some(puesto => String(puesto.id) === String(seleccionado))) {
        selectPuesto.value = seleccionado;
      }
      selectPuesto.disabled = selectPuesto.options.length <= 1;
      setRrhhHiddenText('rrhh_puesto_id', 'rrhh_puesto_texto');
      refreshRrhhSelect('rrhh_puesto_id');
    } catch (error) {
      fillRrhhPuestosFallback(idDepartamento, seleccionadoForzado);
    }
  }

  function usuarioNombreCompleto(usuario) {
    return [usuario.nombres, usuario.segundo_nombre, usuario.apellidop, usuario.apellidom]
      .filter(Boolean)
      .join(' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function usuarioEnDepartamento(usuario, idDepartamento) {
    if (!idDepartamento) return true;
    if (String(usuario.id_departamento || '') === String(idDepartamento)) return true;
    return Array.isArray(usuario.puestos) && usuario.puestos.some(puesto => String(puesto.id_departamento || '') === String(idDepartamento));
  }

  function fillRrhhJefesFallback(idDepartamento, idPuesto) {
    const selectJefe = document.getElementById('rrhh_jefe_id');
    if (!selectJefe) return;
    const opciones = [];
    const vistos = new Set();

    usuariosData
      .filter(usuario => String(usuario.estatus || '').toLowerCase() !== 'baja')
      .filter(usuario => usuarioEnDepartamento(usuario, idDepartamento))
      .filter(usuario => {
        if (!idPuesto) return true;
        if (String(usuario.id_puesto || '') === String(idPuesto)) return true;
        return Array.isArray(usuario.puestos) && usuario.puestos.some(puesto => String(puesto.id_puesto || '') === String(idPuesto));
      })
      .forEach(usuario => {
        const id = usuario.id;
        const nombre = usuarioNombreCompleto(usuario);
        if (!id || !nombre || vistos.has(String(id))) return;
        vistos.add(String(id));
        const puesto = usuario.nombre_puesto && usuario.nombre_puesto !== 'Sin puesto' ? ` - ${usuario.nombre_puesto}` : '';
        opciones.push({ id, nombre, label: `${nombre}${puesto}` });
      });

    selectJefe.innerHTML = '<option value="">Seleccione un jefe</option>' +
      opciones.sort((a, b) => a.nombre.localeCompare(b.nombre))
        .map(jefe => optionHtml(jefe.id, jefe.label))
        .join('');
    selectJefe.disabled = !idDepartamento || !idPuesto || opciones.length === 0;
    setRrhhHiddenText('rrhh_jefe_id', 'rrhh_jefe_directo_texto');
    refreshRrhhSelect('rrhh_jefe_id');
  }

  async function fillRrhhJefes(seleccionadoForzado = '', etiquetaSeleccionado = '') {
    const selectDepartamento = document.getElementById('rrhh_departamento_id');
    const selectPuesto = document.getElementById('rrhh_puesto_id');
    const selectJefe = document.getElementById('rrhh_jefe_id');
    if (!selectJefe) return;
    const idDepartamento = selectDepartamento?.value || '';
    const idPuesto = selectPuesto?.value || '';

    selectJefe.innerHTML = '<option value="">Seleccione un jefe</option>';
    selectJefe.disabled = true;
    setRrhhHiddenText('rrhh_jefe_id', 'rrhh_jefe_directo_texto');
    refreshRrhhSelect('rrhh_jefe_id');
    if (!idDepartamento || !idPuesto) return;

    if (seleccionadoForzado) {
      ensureSelectOptionRrhh('rrhh_jefe_id', seleccionadoForzado, etiquetaSeleccionado);
      setSelectValue('rrhh_jefe_id', seleccionadoForzado);
      setRrhhHiddenText('rrhh_jefe_id', 'rrhh_jefe_directo_texto');
    }

    try {
      const response = await fetch('/CapHum/getJefeDirecto', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ id_departamento: idDepartamento, id_puesto: idPuesto })
      });
      const data = await response.json();
      if (!data.success || !Array.isArray(data.datos)) {
        fillRrhhJefesFallback(idDepartamento, idPuesto);
        return;
      }
      const opciones = data.datos
        .map(jefe => ({
          id: jefe.id,
          nombre: jefe.nombre_completo || jefe.nombre || '',
          puesto: jefe.nombre_puesto || jefe.puesto || ''
        }))
        .filter(jefe => jefe.id && jefe.nombre);

      selectJefe.innerHTML = '<option value="">Seleccione un jefe</option>' +
        opciones
          .sort((a, b) => a.nombre.localeCompare(b.nombre))
          .map(jefe => optionHtml(jefe.id, `${jefe.nombre}${jefe.puesto ? ' - ' + jefe.puesto : ''}`))
          .join('');
      const seleccionado = seleccionadoForzado || selectJefe.value || '';
      if (seleccionado && Array.from(selectJefe.options).some(option => String(option.value) === String(seleccionado))) {
        selectJefe.value = seleccionado;
      }
      selectJefe.disabled = opciones.length === 0;
      setRrhhHiddenText('rrhh_jefe_id', 'rrhh_jefe_directo_texto');
      refreshRrhhSelect('rrhh_jefe_id');
    } catch (error) {
      fillRrhhJefesFallback(idDepartamento, idPuesto);
    }
  }

  function initRrhhLaboralSelects() {
    ordenarCamposLaboralesRrhh();
    fillRrhhPaises();
    fillRrhhEmpresas();
    fillRrhhJefes();
    vincularEventosSelect2Rrhh();
  }

  function vincularEventosSelect2Rrhh() {
    if (typeof window.jQuery === 'undefined') return;
    const $ = window.jQuery;
    $('#rrhh_pais_id').off('change.rrhhLaboral').on('change.rrhhLaboral', function () {
      fillRrhhEmpresas();
    });
    $('#rrhh_empresa_id').off('change.rrhhLaboral').on('change.rrhhLaboral', function () {
      setRrhhHiddenText('rrhh_empresa_id', 'rrhh_empresa_texto');
      fillRrhhDirecciones();
    });
    $('#rrhh_direccion_id').off('change.rrhhLaboral').on('change.rrhhLaboral', function () {
      setRrhhHiddenText('rrhh_direccion_id', 'rrhh_direccion_texto');
      fillRrhhAreas();
    });
    $('#rrhh_area_id').off('change.rrhhLaboral').on('change.rrhhLaboral', function () {
      setRrhhHiddenText('rrhh_area_id', 'rrhh_area_texto');
      fillRrhhDepartamentos();
    });
    $('#rrhh_departamento_id').off('change.rrhhLaboral').on('change.rrhhLaboral', function () {
      setRrhhHiddenText('rrhh_departamento_id', 'rrhh_departamento_texto');
      fillRrhhPuestos();
      fillRrhhJefes();
    });
    $('#rrhh_puesto_id').off('change.rrhhLaboral').on('change.rrhhLaboral', function () {
      setRrhhHiddenText('rrhh_puesto_id', 'rrhh_puesto_texto');
      fillRrhhJefes();
    });
    $('#rrhh_jefe_id').off('change.rrhhLaboral').on('change.rrhhLaboral', function () {
      setRrhhHiddenText('rrhh_jefe_id', 'rrhh_jefe_directo_texto');
    });
  }

  function showRrhhScrim() {
    let scrim = document.getElementById('rrhhModalScrim');
    if (!scrim) {
      scrim = document.createElement('div');
      scrim.id = 'rrhhModalScrim';
      scrim.setAttribute('aria-hidden', 'true');
      document.body.appendChild(scrim);
    }
    scrim.style.display = 'block';
  }

  function hideRrhhScrim() {
    const scrim = document.getElementById('rrhhModalScrim');
    if (scrim) scrim.style.display = 'none';
  }

  function resetRrhhFormCompleto() {
    limpiarPruebasExpedienteRrhh(false);
    delete form.dataset.rrhhDataLoaded;
    form.querySelectorAll('.rrhh-date').forEach(input => {
      if (input._flatpickr) input._flatpickr.clear();
    });
    form.reset();
    resetRepeats();
    setModoRrhh('crear');
    initRrhhLaboralSelects();
    form.querySelector('[data-bs-target="#rrhhTabPersona"]')?.click();
    actualizarRrhhWizard();
  }

  function obtenerUsuarioActualRrhh() {
    const idPersona = inputEditIdRrhh?.value || '';
    if (!idPersona || !Array.isArray(usuariosData)) return null;
    return usuariosData.find(usuario => String(usuario.id) === String(idPersona)) || null;
  }

  function valorRrhh(name, fallback = '') {
    const input = form.querySelector(`[name="${name}"]`);
    const value = input ? String(input.value || '').trim() : '';
    return value || fallback || '';
  }

  function nombreCompletoCredencial(payload, usuario) {
    const desdePayload = [
      payload?.persona?.nombres,
      payload?.persona?.segundo_nombre,
      payload?.persona?.apellidop,
      payload?.persona?.apellidom
    ].filter(Boolean).join(' ').replace(/\s+/g, ' ').trim();
    if (desdePayload) return desdePayload;
    return usuarioNombreCompleto(usuario || {}) || 'Colaborador Maxikash';
  }

  function inicialesCredencial(nombre) {
    const partes = String(nombre || '').trim().split(/\s+/).filter(Boolean);
    if (!partes.length) return 'MX';
    return partes.slice(0, 2).map(parte => parte.charAt(0)).join('').toUpperCase();
  }

  function puestoGeneralCredencial(puesto) {
    let texto = String(puesto || '').replace(/\s+/g, ' ').trim();
    if (!texto || texto === 'Sin puesto') return 'Colaborador';
    texto = texto
      .replace(/\b(jr|sr|junior|senior)\b/ig, '')
      .replace(/\b(prueba|temporal)\b/ig, '')
      .replace(/\b\d+\s*-\s*\d+\b/g, '')
      .replace(/\b\d+\+\b/g, '')
      .replace(/\s{2,}/g, ' ')
      .replace(/\s+-\s+$/g, '')
      .trim();
    return texto || 'Colaborador';
  }

  function fotoCredencialHtml(usuario, nombre) {
    const foto = String(fotoTemporalCredencialRrhh || usuario?.foto_perfil || '').trim();
    const fallback = `<span class="rrhh-id-photo-fallback"${foto ? ' style="display:none;"' : ''}>${escapeRrhhHtml(inicialesCredencial(nombre))}</span>`;
    const fitSeguro = fotoFitCredencialRrhh === 'contain' ? 'contain' : 'cover';
    const posX = fitSeguro === 'contain' ? 50 : fotoPosXCredencialRrhh;
    const posY = fitSeguro === 'contain' ? 50 : fotoPosYCredencialRrhh;
    const scale = fitSeguro === 'contain' ? 1 : fotoScaleCredencialRrhh;
    const posicion = `${posX}% ${posY}%`;
    const transformacion = fitSeguro === 'contain' ? 'none' : `translate(${50 - posX}%, ${50 - posY}%) scale(${scale})`;
    const img = foto
      ? `<img class="rrhh-id-photo" src="${escapeRrhhAttr(foto)}" alt="Foto de ${escapeRrhhAttr(nombre)}" style="--rrhh-photo-fit:${escapeRrhhAttr(fitSeguro)}; --rrhh-photo-position:${escapeRrhhAttr(posicion)}; --rrhh-photo-transform:${escapeRrhhAttr(transformacion)};" draggable="false" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
      : '';
    return `${img}${fallback}`;
  }

  function claseFotoCredencialWrap() {
    return fotoFitCredencialRrhh === 'contain' ? 'is-contain' : 'is-cover';
  }

  function fotoCredencialSrcActual(usuario = obtenerUsuarioActualRrhh()) {
    return String(fotoTemporalCredencialRrhh || usuario?.foto_perfil || '').trim();
  }

  function textoValidoCredencial(value) {
    const texto = String(value || '').replace(/\s+/g, ' ').trim();
    if (!texto || texto === '-') return '';
    const normalizado = texto
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase();
    if (normalizado.startsWith('seleccione') || normalizado.startsWith('--selecciona') || normalizado.startsWith('no hay')) return '';
    return texto;
  }

  function nombrePaisPorIdRrhh(idPais) {
    const id = String(idPais || '').trim();
    if (!id) return '';
    const pais = rrhhPaisesBackend().find(item => String(item.id || '') === id);
    return pais ? textoValidoCredencial(pais.nombre) : '';
  }

  function resolverPaisCredencial(usuario, payload) {
    return textoValidoCredencial(rrhhSelectText('rrhh_pais_id'))
      || textoValidoCredencial(usuario?.nombre_pais || usuario?.pais)
      || nombrePaisPorIdRrhh(payload?.persona?.id_pais || usuario?.id_pais || usuario?.pais_id)
      || '';
  }

  function qrCredencialHtml(seed) {
    const data = encodeURIComponent(String(seed || 'MAXIKASH'));
    const src = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&margin=8&color=0054a6&bgcolor=ffffff&data=${data}`;
    return `<div class="rrhh-id-qr" aria-label="C&oacute;digo QR de validaci&oacute;n interna">
      <img class="rrhh-id-qr-img" src="${src}" alt="QR de validaci&oacute;n interna">
    </div>`;
  }

  function textoQrCredencial(value, max = 90) {
    const texto = String(value || '')
      .replace(/[|\r\n\t]+/g, ' ')
      .replace(/\s{2,}/g, ' ')
      .trim();
    return max > 0 && texto.length > max ? `${texto.slice(0, max - 1)}...` : texto;
  }

  function datoCredencial(label, value) {
    if (!String(value || '').trim() || String(value || '').trim() === '-') return '';
    return `<div class="rrhh-id-meta-item">
      <span class="rrhh-id-meta-label">${escapeRrhhHtml(label)}</span>
      <span class="rrhh-id-meta-value">${escapeRrhhHtml(value)}</span>
    </div>`;
  }

  function datoReversoCredencial(icono, label, value) {
    const limpio = String(value || '').trim();
    if (!limpio || limpio === '-') return '';
    return `<div class="rrhh-id-back-row"><i class="fa ${icono}"></i><div><strong>${escapeRrhhHtml(label)}</strong><br>${escapeRrhhHtml(limpio)}</div></div>`;
  }

  function limpiarDatoExpediente(value) {
    const limpio = String(value ?? '').replace(/\s+/g, ' ').trim();
    return limpio && limpio !== '-' ? limpio : '';
  }

  function campoExpediente(label, value) {
    const limpio = limpiarDatoExpediente(value);
    if (!limpio) return '';
    return `<div class="rrhh-expediente-field">
      <span class="rrhh-expediente-label">${escapeRrhhHtml(label)}</span>
      <span class="rrhh-expediente-value">${escapeRrhhHtml(limpio)}</span>
    </div>`;
  }

  function campoExpedienteConDefault(label, value, fallback = 'N/A') {
    return campoExpediente(label, limpiarDatoExpediente(value) || fallback);
  }

  function seccionExpediente(icono, titulo, campos) {
    const html = campos.filter(Boolean).join('');
    if (!html) return '';
    return `<section class="rrhh-expediente-section">
      <div class="rrhh-expediente-section-title"><i class="fa ${icono}"></i>${escapeRrhhHtml(titulo)}</div>
      <div class="rrhh-expediente-grid">${html}</div>
    </section>`;
  }

  function tablaExpediente(icono, titulo, columnas, filas) {
    const limpias = (filas || []).filter(fila => Object.values(fila || {}).some(limpiarDatoExpediente));
    if (!limpias.length) return '';
    return `<section class="rrhh-expediente-section">
      <div class="rrhh-expediente-section-title"><i class="fa ${icono}"></i>${escapeRrhhHtml(titulo)}</div>
      <div class="table-responsive">
        <table class="table rrhh-expediente-table">
          <thead><tr>${columnas.map(col => `<th>${escapeRrhhHtml(col.label)}</th>`).join('')}</tr></thead>
          <tbody>
            ${limpias.map(fila => `<tr>${columnas.map(col => `<td>${escapeRrhhHtml(limpiarDatoExpediente(fila[col.key]) || '-')}</td>`).join('')}</tr>`).join('')}
          </tbody>
        </table>
      </div>
    </section>`;
  }

  function fotoExpedienteHtml(usuario, nombre) {
    if (!incluirFotoExpedienteRrhh) return '';
    const foto = String(fotoTemporalExpedienteRrhh || usuario?.foto_perfil || '').trim();
    if (foto) {
      const fitSeguro = fotoFitExpedienteRrhh === 'contain' ? 'contain' : 'cover';
      const posX = fitSeguro === 'contain' ? 50 : fotoPosXExpedienteRrhh;
      const posY = fitSeguro === 'contain' ? 50 : fotoPosYExpedienteRrhh;
      const scale = fitSeguro === 'contain' ? 1 : fotoScaleExpedienteRrhh;
      return `<img class="rrhh-expediente-photo-img" src="${escapeRrhhAttr(foto)}" alt="Foto de ${escapeRrhhAttr(nombre)}" style="object-fit:${escapeRrhhAttr(fitSeguro)}; object-position:${posX}% ${posY}%; transform:scale(${scale});" draggable="false" onerror="this.outerHTML='<span>${escapeRrhhHtml(inicialesCredencial(nombre))}</span>'">`;
    }
    return `<span>${escapeRrhhHtml(inicialesCredencial(nombre))}</span>`;
  }

  function fotoExpedienteSrcActual(usuario = obtenerUsuarioActualRrhh()) {
    return String(fotoTemporalExpedienteRrhh || usuario?.foto_perfil || '').trim();
  }

  function claseFotoExpedienteWrap() {
    return fotoFitExpedienteRrhh === 'contain' ? 'is-contain' : 'is-movable';
  }

  function valorSelectLaboralExpediente(selectId, fallback) {
    return rrhhSelectText(selectId) || fallback || '';
  }

  function firmaExpedienteHtml(clave, etiqueta) {
    const firma = firmasExpedienteRrhh[clave] || '';
    return `<div class="rrhh-expediente-signature" data-rrhh-signature="${escapeRrhhAttr(clave)}" role="button" tabindex="0" title="Haz clic para capturar la firma">
      ${firma ? `<img class="rrhh-expediente-signature-img" src="${escapeRrhhAttr(firma)}" alt="Firma ${escapeRrhhAttr(etiqueta)}"><button type="button" class="btn btn-outline-danger btn-sm rrhh-expediente-signature-clear" data-rrhh-clear-signature="${escapeRrhhAttr(clave)}"><i class="fa fa-times me-1"></i>Quitar firma</button>` : '<div class="text-muted small mb-2"><i class="fa fa-pen me-1"></i>Firmar aqu&iacute;</div>'}
      <div class="rrhh-expediente-signature-line">${escapeRrhhHtml(etiqueta)}</div>
    </div>`;
  }

  function fechaRecepcionExpedienteHtml() {
    return `<div class="rrhh-expediente-signature">
      <input type="text" class="form-control rrhh-expediente-signature-date" value="${escapeRrhhAttr(fechaRecepcionExpedienteRrhh)}" placeholder="dd/mm/aaaa" aria-label="Fecha de recepci&oacute;n" readonly>
      <div class="rrhh-expediente-signature-line">Fecha de recepci&oacute;n</div>
    </div>`;
  }

  function initFechaRecepcionExpediente() {
    const input = contenedorExpedienteRrhh?.querySelector('.rrhh-expediente-signature-date');
    if (!input) return;
    if (typeof flatpickr === 'undefined') {
      input.readOnly = false;
      input.addEventListener('input', function () {
        fechaRecepcionExpedienteRrhh = this.value.trim();
      });
      return;
    }
    if (input._flatpickr) return;
    const anioActual = new Date().getFullYear();
    const prepararAnioRecepcion = function (instance) {
      const calendar = instance?.calendarContainer;
      if (!calendar || calendar.querySelector('.rrhh-year-select')) return;
      const currentMonth = calendar.querySelector('.flatpickr-current-month');
      if (!currentMonth) return;
      const select = document.createElement('select');
      select.className = 'rrhh-year-select';
      select.setAttribute('aria-label', 'Año');
      for (let year = anioActual + 5; year >= anioActual - 5; year--) {
        const option = document.createElement('option');
        option.value = String(year);
        option.textContent = String(year);
        if (year === instance.currentYear) option.selected = true;
        select.appendChild(option);
      }
      select.addEventListener('change', function () {
        const year = Number(this.value);
        if (Number.isFinite(year)) instance.changeYear(year);
      });
      currentMonth.appendChild(select);
    };
    const sincronizarAnioRecepcion = function (instance) {
      const select = instance?.calendarContainer?.querySelector('.rrhh-year-select');
      if (select && String(select.value) !== String(instance.currentYear)) {
        select.value = String(instance.currentYear);
      }
    };
    flatpickr(input, {
      dateFormat: 'd/m/Y',
      allowInput: false,
      clickOpens: true,
      appendTo: document.body,
      monthSelectorType: 'dropdown',
      locale: (flatpickr.l10ns && flatpickr.l10ns.es) ? flatpickr.l10ns.es : undefined,
      onOpen: function (_, __, instance) {
        instance.calendarContainer.classList.add('rrhh-flatpickr-calendar');
        prepararAnioRecepcion(instance);
        sincronizarAnioRecepcion(instance);
      },
      onMonthChange: function (_, __, instance) {
        sincronizarAnioRecepcion(instance);
      },
      onYearChange: function (_, __, instance) {
        sincronizarAnioRecepcion(instance);
      },
      onChange: function (_, dateStr) {
        fechaRecepcionExpedienteRrhh = dateStr || '';
      }
    });
  }

  function ajustarCanvasFirmaExpediente() {
    if (!canvasFirmaExpedienteRrhh) return;
    const rect = canvasFirmaExpedienteRrhh.getBoundingClientRect();
    const ratio = window.devicePixelRatio || 1;
    canvasFirmaExpedienteRrhh.width = Math.max(1, Math.floor(rect.width * ratio));
    canvasFirmaExpedienteRrhh.height = Math.max(1, Math.floor(rect.height * ratio));
    const ctx = canvasFirmaExpedienteRrhh.getContext('2d');
    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    ctx.lineWidth = 2.4;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#0d2f5f';
    ctx.clearRect(0, 0, rect.width, rect.height);
  }

  function limpiarCanvasFirmaExpediente() {
    if (!canvasFirmaExpedienteRrhh) return;
    const ctx = canvasFirmaExpedienteRrhh.getContext('2d');
    const rect = canvasFirmaExpedienteRrhh.getBoundingClientRect();
    ctx.clearRect(0, 0, rect.width, rect.height);
    firmaTieneTrazosRrhh = false;
  }

  function puntoCanvasFirma(event) {
    const rect = canvasFirmaExpedienteRrhh.getBoundingClientRect();
    return {
      x: event.clientX - rect.left,
      y: event.clientY - rect.top
    };
  }

  function cargarFotoExpedienteDesdeArchivo(file) {
    if (!file) return;
    if (!file.type || !file.type.startsWith('image/')) {
      Swal.fire('Foto no v&aacute;lida', 'Selecciona una imagen para el expediente.', 'warning');
      return;
    }
    if (fotoTemporalExpedienteRrhh && fotoTemporalExpedienteRrhh.startsWith('blob:')) {
      URL.revokeObjectURL(fotoTemporalExpedienteRrhh);
    }
    fotoTemporalExpedienteRrhh = URL.createObjectURL(file);
    incluirFotoExpedienteRrhh = true;
    fotoFitExpedienteRrhh = 'cover';
    fotoPosXExpedienteRrhh = 50;
    fotoPosYExpedienteRrhh = 50;
    fotoScaleExpedienteRrhh = 1;
    if (inputIncluirFotoExpedienteRrhh) inputIncluirFotoExpedienteRrhh.checked = true;
    pintarExpedienteRrhh();
    abrirEditorFotoRrhh('expediente');
  }

  function limpiarPruebasExpedienteRrhh(repaint = true) {
    if (fotoTemporalExpedienteRrhh && fotoTemporalExpedienteRrhh.startsWith('blob:')) {
      URL.revokeObjectURL(fotoTemporalExpedienteRrhh);
    }
    if (fotoTemporalCredencialRrhh && fotoTemporalCredencialRrhh.startsWith('blob:')) {
      URL.revokeObjectURL(fotoTemporalCredencialRrhh);
    }
    fotoTemporalExpedienteRrhh = '';
    fotoTemporalCredencialRrhh = '';
    incluirFotoExpedienteRrhh = true;
    fotoFitExpedienteRrhh = 'cover';
    fotoPosXExpedienteRrhh = 50;
    fotoPosYExpedienteRrhh = 50;
    fotoScaleExpedienteRrhh = 1;
    fotoDragExpedienteRrhh = null;
    fotoFitCredencialRrhh = 'cover';
    fotoPosXCredencialRrhh = 50;
    fotoPosYCredencialRrhh = 50;
    fotoScaleCredencialRrhh = 1;
    posicionFotoCredencialRrhh = 'center center';
    fotoDragCredencialRrhh = null;
    fotoEditorDragCredencialRrhh = null;
    Object.keys(firmasExpedienteRrhh).forEach(clave => delete firmasExpedienteRrhh[clave]);
    if (inputIncluirFotoExpedienteRrhh) inputIncluirFotoExpedienteRrhh.checked = true;
    if (inputFotoExpedienteRrhh) inputFotoExpedienteRrhh.value = '';
    if (inputFotoCredencialRrhh) inputFotoCredencialRrhh.value = '';
    fechaRecepcionExpedienteRrhh = '';
    limpiarCanvasFirmaExpediente();
    if (repaint) {
      pintarExpedienteRrhh();
      pintarCredencialRrhh();
    }
  }

  function aplicarPosicionFotoExpediente() {
    contenedorExpedienteRrhh?.querySelectorAll('.rrhh-expediente-photo-img').forEach(img => {
      img.style.objectFit = fotoFitExpedienteRrhh;
      img.style.objectPosition = `${fotoPosXExpedienteRrhh}% ${fotoPosYExpedienteRrhh}%`;
      img.style.transform = `scale(${fotoScaleExpedienteRrhh})`;
    });
    actualizarPreviewEditorFotoCredencial();
  }

  function pintarExpedienteRrhh() {
    if (!contenedorExpedienteRrhh) return false;
    const payload = collectPayload();
    const usuario = obtenerUsuarioActualRrhh();
    const idPersona = inputEditIdRrhh?.value || usuario?.id || '';
    const nombre = nombreCompletoCredencial(payload, usuario);
    const numeroEmpleado = usuario?.numero_empleado || payload?.persona?.numero_empleado || idPersona || '';
    const puesto = valorRrhh('rrhh.puesto_texto', usuario?.nombre_puesto || usuario?.nombre_puesto_principal || '');
    const puestoGeneral = puestoGeneralCredencial(puesto);
    const area = valorSelectLaboralExpediente('rrhh_area_id', usuario?.nombre_departamento_organizacional || '');
    const departamento = valorSelectLaboralExpediente('rrhh_departamento_id', usuario?.nombre_departamento || usuario?.nombre_departamento_principal || '');
    const pais = valorSelectLaboralExpediente('rrhh_pais_id', usuario?.nombre_pais || '');
    const jefe = valorSelectLaboralExpediente('rrhh_jefe_id', valorRrhh('rrhh.jefe_directo_texto', usuario?.jefe_nombre || ''));
    const telefonoPrincipal = collectRepeats('telefonos')[0]?.numero || usuario?.telefono || usuario?.celular || '';
    const correoPrincipal = collectRepeats('correos')[0]?.correo || usuario?.correo || usuario?.email || '';
    const fechaGeneracion = new Date().toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: '2-digit' });
    const checklist = [
      'Identificación oficial',
      'CURP',
      'RFC / Constancia fiscal',
      'NSS',
      'Comprobante de domicilio',
      'Contrato laboral',
      'Cuenta bancaria / CLABE',
      'Alta IMSS',
      'Políticas y acuse interno',
      'Credencial de empleado'
    ];

    contenedorExpedienteRrhh.innerHTML = `
      <article class="rrhh-expediente">
        <header class="rrhh-expediente-cover">
          <div>
            <img class="rrhh-expediente-logo" src="${logoExpedienteRrhh}" alt="Maxikash">
            <div class="rrhh-expediente-title">Expediente de colaborador</div>
            <div class="rrhh-expediente-subtitle">Capital Humano Â· Generado el ${escapeRrhhHtml(fechaGeneracion)}</div>
          </div>
          <div class="rrhh-expediente-person${incluirFotoExpedienteRrhh ? '' : ' no-photo'}">
            ${incluirFotoExpedienteRrhh ? `<div class="rrhh-expediente-photo ${claseFotoExpedienteWrap()}">${fotoExpedienteHtml(usuario, nombre)}</div>` : ''}
            <div>
              <div class="fw-bold text-uppercase" style="font-size:1.05rem;line-height:1.15;">${escapeRrhhHtml(nombre)}</div>
              <div class="mt-1">${escapeRrhhHtml(puestoGeneral)}</div>
              ${numeroEmpleado ? `<div class="small mt-2">No. empleado: <strong>${escapeRrhhHtml(numeroEmpleado)}</strong></div>` : ''}
            </div>
          </div>
        </header>
        <div class="rrhh-expediente-body">
          ${seccionExpediente('fa-id-card', 'Identificación', [
            campoExpediente('ID interno', idPersona),
            campoExpediente('No. empleado', numeroEmpleado),
            campoExpediente('Nombre completo', nombre),
            campoExpediente('CURP', payload?.persona?.curp || usuario?.curp),
            campoExpediente('RFC', payload?.persona?.rfc || usuario?.rfc),
            campoExpediente('NSS', payload?.persona?.nss || usuario?.nss),
            campoExpediente('Fecha de nacimiento', payload?.persona?.fecha_nacimiento || usuario?.fecha_nacimiento),
            campoExpediente('Sexo', payload?.persona?.sexo || usuario?.sexo),
            campoExpediente('Entidad federativa', payload?.persona?.entidad_federativa || usuario?.entidad_federativa)
          ])}
          ${seccionExpediente('fa-briefcase', 'Datos laborales', [
            campoExpediente('País', pais),
            campoExpediente('Área', area),
            campoExpediente('Departamento', departamento),
            campoExpediente('Puesto', puesto),
            campoExpediente('Jefe directo', jefe),
            campoExpediente('Fecha de ingreso', payload?.rrhh?.fecha_ingreso || usuario?.fecha_ingreso),
            campoExpediente('Fecha CONTPAC', payload?.rrhh?.fecha_contpac),
            campoExpediente('Fecha IMSS alta', payload?.rrhh?.fecha_imss_alta),
            campoExpediente('Registro patronal', payload?.rrhh?.registro_patronal),
            campoExpediente('Código CONTPAC', payload?.rrhh?.codigo_contpac),
            campoExpediente('Dirección organizacional', payload?.rrhh?.direccion_texto),
            campoExpediente('Ubicación laboral', payload?.rrhh?.ubicacion_laboral),
            campoExpediente('Municipio', payload?.rrhh?.municipio)
          ])}
          ${seccionExpediente('fa-address-book', 'Contacto principal', [
            campoExpediente('Teléfono', telefonoPrincipal),
            campoExpediente('Correo electrónico', correoPrincipal)
          ])}
          ${seccionExpediente('fa-notes-medical', 'Información médica', [
            campoExpedienteConDefault('Tipo de sangre', payload?.rrhh?.tipo_sangre),
            campoExpedienteConDefault('Alergias', payload?.rrhh?.alergias),
            campoExpedienteConDefault('Enfermedades crónicas', payload?.rrhh?.enfermedades_cronicas),
            campoExpedienteConDefault('Enfermedades hereditarias', payload?.rrhh?.enfermedades_hereditarias),
            campoExpedienteConDefault('Medicamentos actuales', payload?.rrhh?.medicamentos_actuales),
            campoExpedienteConDefault('Discapacidad o condición médica', payload?.rrhh?.discapacidad_condicion),
            campoExpedienteConDefault('Observaciones médicas', payload?.rrhh?.observaciones_medicas)
          ])}
          ${tablaExpediente('fa-phone', 'Teléfonos', [
            { key: 'numero', label: 'Número' },
            { key: 'tipo', label: 'Tipo' }
          ], payload.telefonos)}
          ${tablaExpediente('fa-envelope', 'Correos', [
            { key: 'correo', label: 'Correo' },
            { key: 'tipo', label: 'Tipo' }
          ], payload.correos)}
          ${tablaExpediente('fa-home', 'Domicilios', [
            { key: 'domicilio_texto', label: 'Domicilio' },
            { key: 'codigo_postal', label: 'C.P.' },
            { key: 'tipo', label: 'Tipo' }
          ], payload.domicilios)}
          ${tablaExpediente('fa-university', 'Banco y cuentas', [
            { key: 'nombre_banco', label: 'Banco' },
            { key: 'numero_cuenta', label: 'Cuenta' },
            { key: 'clabe', label: 'CLABE' }
          ], payload.cuentas_bancarias)}
          ${tablaExpediente('fa-exclamation-triangle', 'Contactos de emergencia', [
            { key: 'nombre_contacto', label: 'Nombre' },
            { key: 'parentesco', label: 'Parentesco' },
            { key: 'numero', label: 'Teléfono' }
          ], payload.contactos_emergencia)}
          ${tablaExpediente('fa-users', 'Beneficiarios', [
            { key: 'nombre_beneficiario', label: 'Nombre' },
            { key: 'parentesco', label: 'Parentesco' },
            { key: 'numero', label: 'Teléfono' },
            { key: 'porcentaje', label: 'Porcentaje' }
          ], payload.beneficiarios)}
          ${payload.observaciones ? seccionExpediente('fa-clipboard-list', 'Observaciones internas', [
            campoExpediente('Notas', payload.observaciones)
          ]) : ''}
          <section class="rrhh-expediente-section">
            <div class="rrhh-expediente-section-title"><i class="fa fa-tasks"></i>Checklist documental</div>
            <div class="table-responsive">
              <table class="table rrhh-expediente-table">
                <thead><tr><th>Documento</th><th>Recibido</th><th>Observaciones</th></tr></thead>
                <tbody>
                  ${checklist.map((doc, index) => `<tr><td>${escapeRrhhHtml(doc)}</td><td><input type="checkbox" class="rrhh-expediente-checkbox" id="rrhhChecklistExpediente${index}"></td><td></td></tr>`).join('')}
                </tbody>
              </table>
            </div>
          </section>
          <section class="rrhh-expediente-section">
            <div class="rrhh-expediente-section-title"><i class="fa fa-pencil-alt"></i>Firmas y validaci&oacute;n</div>
            <div class="rrhh-expediente-signatures">
              ${firmaExpedienteHtml('colaborador', 'Colaborador')}
              ${firmaExpedienteHtml('capital_humano', 'Capital Humano')}
              ${fechaRecepcionExpedienteHtml()}
            </div>
          </section>
        </div>
      </article>`;
    initFechaRecepcionExpediente();
    return true;
  }

  function pintarCredencialRrhh() {
    if (!contenedorCredencialFrente || !contenedorCredencialReverso) return false;
    const payload = collectPayload();
    const usuario = obtenerUsuarioActualRrhh();
    const claseOrientacion = orientacionCredencialRrhh === 'horizontal' ? ' is-horizontal' : '';
    const idPersona = inputEditIdRrhh?.value || usuario?.id || '';
    const nombre = nombreCompletoCredencial(payload, usuario);
    const puestoBase = valorRrhh('rrhh.puesto_texto', usuario?.nombre_puesto || usuario?.nombre_puesto_principal || '');
    const puestoGeneral = puestoGeneralCredencial(puestoBase);
    const numeroEmpleado = usuario?.numero_empleado || payload?.persona?.numero_empleado || idPersona || '-';
    const area = valorRrhh('rrhh.area_texto', usuario?.nombre_departamento_organizacional || '');
    const departamento = valorRrhh('rrhh.departamento_texto', usuario?.nombre_departamento || usuario?.nombre_departamento_principal || '');
    const pais = resolverPaisCredencial(usuario, payload);
    const rfc = payload?.persona?.rfc || usuario?.rfc || '';
    const curp = payload?.persona?.curp || usuario?.curp || '';
    const nss = payload?.persona?.nss || usuario?.nss || '';
    const estatusCredencial = textoValidoCredencial(usuario?.estatus) || 'Activo';
    const contactoEmergencia = Array.isArray(payload?.contactos_emergencia)
      ? payload.contactos_emergencia.find(item => textoValidoCredencial(item?.nombre_contacto) || textoValidoCredencial(item?.numero))
      : null;
    const contactoQr = contactoEmergencia
      ? [
          textoQrCredencial(contactoEmergencia.nombre_contacto, 45),
          textoQrCredencial(contactoEmergencia.parentesco, 25),
          textoQrCredencial(contactoEmergencia.numero, 25)
        ].filter(Boolean).join(' / ')
      : '';
    const qrDatos = [
      ['MAXIKASH', 'RRHH'],
      ['ID', idPersona],
      ['EMP', numeroEmpleado],
      ['NOMBRE', nombre],
      ['ESTATUS', estatusCredencial],
      ['PUESTO', puestoGeneral],
      ['DEPTO', departamento],
      ['SANGRE', payload?.rrhh?.tipo_sangre],
      ['ALERGIAS', payload?.rrhh?.alergias],
      ['ENF_CRONICAS', payload?.rrhh?.enfermedades_cronicas],
      ['MEDICAMENTOS', payload?.rrhh?.medicamentos_actuales],
      ['CONTACTO_EMERG', contactoQr]
    ];
    const qrSeed = qrDatos
      .map(([label, value]) => Array.isArray(value)
        ? value.map(item => textoQrCredencial(item, 60)).filter(Boolean).join('|')
        : `${label}:${textoQrCredencial(value)}`)
      .filter(item => item && !item.endsWith(':'))
      .join('|');

    contenedorCredencialFrente.innerHTML = `
      <div class="rrhh-id-card rrhh-id-front${claseOrientacion}">
        <div class="rrhh-id-card-inner">
          <img class="rrhh-id-logo mt-1 mb-4" src="${logoCredencialRrhh}" alt="Maxikash">
          <div class="rrhh-id-photo-wrap ${claseFotoCredencialWrap()} mt-4">${fotoCredencialHtml(usuario, nombre)}</div>
          <div class="rrhh-id-greeting">&iexcl;Hola! yo soy:</div>
          <div class="rrhh-id-name">${escapeRrhhHtml(nombre)}</div>
          <div class="rrhh-id-position">${escapeRrhhHtml(puestoGeneral)}</div>
          <div class="rrhh-id-footer">@__SPARTA_SECRET_REDACTED__<br>www.__SPARTA_SECRET_REDACTED__.mx</div>
          <div class="rrhh-id-meta mt-4">
            ${datoCredencial('RFC', rfc)}
            ${datoCredencial('Puesto', puestoGeneral)}
            ${datoCredencial('Departamento', departamento)}
            ${datoCredencial('Sede', pais)}
          </div>
        </div>
      </div>`;

    contenedorCredencialReverso.innerHTML = `
      <div class="rrhh-id-card rrhh-id-back${claseOrientacion}">
        <div class="rrhh-id-card-inner">
          <img class="rrhh-id-logo mt-1 mb-5" src="${logoCredencialRrhh}" alt="Maxikash">
          ${qrCredencialHtml(qrSeed)}
          <div class="rrhh-id-footer">@__SPARTA_SECRET_REDACTED__<br>www.__SPARTA_SECRET_REDACTED__.mx</div>
          <div class="rrhh-id-back-title fw-bold text-uppercase text-center mt-3">Validaci&oacute;n interna</div>
          <div class="rrhh-id-back-list">
            ${datoReversoCredencial('fa-id-badge', 'ID interno', idPersona)}
            ${datoReversoCredencial('fa-user', 'No. empleado', numeroEmpleado)}
            ${datoReversoCredencial('fa-sitemap', 'Departamento', departamento)}
            ${datoReversoCredencial('fa-map-marker-alt', 'Sede', pais)}
            ${datoReversoCredencial('fa-address-card', 'RFC', rfc)}
          </div>
        </div>
      </div>`;
    actualizarBotonesFotoCredencialRrhh();
    return true;
  }

  form.addEventListener('click', function (event) {
    const addBtn = event.target.closest('[data-rrhh-add]');
    if (addBtn) {
      addRow(addBtn.dataset.rrhhAdd);
      actualizarRrhhWizard();
    }

    const removeBtn = event.target.closest('.rrhh-remove-row');
    if (removeBtn) {
      const row = removeBtn.closest('.rrhh-repeat-row');
      const list = row?.parentElement;
      if (list && list.children.length > 1) row.remove();
      else row?.querySelectorAll('input, select').forEach(input => {
        if (input.tagName === 'SELECT') input.selectedIndex = 0;
        else input.value = '';
      });
      actualizarRrhhWizard();
    }
  });

  form.addEventListener('change', function (event) {
    const target = event.target;
    if (!target) return;
    if (target.id === 'rrhh_pais_id') {
      fillRrhhEmpresas();
    } else if (target.id === 'rrhh_empresa_id') {
      setRrhhHiddenText('rrhh_empresa_id', 'rrhh_empresa_texto');
      fillRrhhDirecciones();
    } else if (target.id === 'rrhh_direccion_id') {
      setRrhhHiddenText('rrhh_direccion_id', 'rrhh_direccion_texto');
      fillRrhhAreas();
    } else if (target.id === 'rrhh_departamento_id') {
      setRrhhHiddenText('rrhh_departamento_id', 'rrhh_departamento_texto');
      fillRrhhPuestos();
      fillRrhhJefes();
    } else if (target.id === 'rrhh_area_id') {
      setRrhhHiddenText('rrhh_area_id', 'rrhh_area_texto');
      fillRrhhDepartamentos();
    } else if (target.id === 'rrhh_puesto_id') {
      setRrhhHiddenText('rrhh_puesto_id', 'rrhh_puesto_texto');
      fillRrhhJefes();
    } else if (target.id === 'rrhh_jefe_id') {
      setRrhhHiddenText('rrhh_jefe_id', 'rrhh_jefe_directo_texto');
    }
    marcarControlRrhh(target, false);
    actualizarRrhhWizard();
  });

  form.addEventListener('input', function (event) {
    const target = event.target;
    if (target) marcarControlRrhh(target, false);
    actualizarRrhhWizard();
  });

  modal.addEventListener('shown.bs.modal', function () {
    if (modal.parentNode !== document.body) {
      document.body.appendChild(modal);
    }
    modal.style.setProperty('z-index', '99999', 'important');
    showRrhhScrim();
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });
    if (!form.dataset.initialized) {
      resetRepeats();
      form.dataset.initialized = '1';
    }
    initRrhhDatepickers();
    if (!(form.dataset.mode === 'editar' && form.dataset.rrhhDataLoaded === '1')) {
      initRrhhLaboralSelects();
    }
    initRrhhWizardMarkup();
    actualizarRrhhWizard();
  });

  modal.addEventListener('show.bs.modal', function () {
    if (modal.parentNode !== document.body) {
      document.body.appendChild(modal);
    }
    if (!inputEditIdRrhh?.value) {
      setModoRrhh('crear');
    }
    showRrhhScrim();
    initRrhhWizardMarkup();
  });

  modal.addEventListener('hidden.bs.modal', function () {
    modal.style.removeProperty('z-index');
    setRrhhModalLoading(false);
    delete form.dataset.rrhhLoadToken;
    hideRrhhScrim();
    if (abriendoExpedienteRrhh) {
      abriendoExpedienteRrhh = false;
      bootstrap.Modal.getOrCreateInstance(modalExpedienteRrhh, { backdrop: false })?.show();
      return;
    }
    if (abriendoCredencialRrhh) {
      abriendoCredencialRrhh = false;
      bootstrap.Modal.getOrCreateInstance(modalCredencialRrhh, { backdrop: false })?.show();
      return;
    }
    resetRrhhFormCompleto();
  });

  btnGenerarExpedienteRrhh?.addEventListener('click', function () {
    if (!inputEditIdRrhh?.value) {
      Swal.fire('Expediente RR.HH.', 'Primero guarda o selecciona un usuario para generar su expediente.', 'info');
      return;
    }
    if (!pintarExpedienteRrhh()) {
      Swal.fire('Expediente RR.HH.', 'No se pudo preparar la vista del expediente.', 'error');
      return;
    }
    abriendoExpedienteRrhh = true;
    bootstrap.Modal.getInstance(modal)?.hide();
  });

  btnVolverRrhhDesdeExpediente?.addEventListener('click', function () {
    volverDesdeExpedienteRrhh = true;
    bootstrap.Modal.getInstance(modalExpedienteRrhh)?.hide();
  });

  btnImprimirExpedienteRrhh?.addEventListener('click', function () {
    const modalBody = modalExpedienteRrhh?.querySelector('.modal-body');
    if (modalBody) modalBody.scrollTop = 0;
    window.scrollTo(0, 0);
    document.body.classList.add('print-rrhh-expediente');
    window.print();
    setTimeout(() => document.body.classList.remove('print-rrhh-expediente'), 300);
  });

  contenedorExpedienteRrhh?.addEventListener('click', function (event) {
    const clearBtn = event.target.closest('[data-rrhh-clear-signature]');
    if (clearBtn) {
      event.preventDefault();
      event.stopPropagation();
      const clave = clearBtn.dataset.rrhhClearSignature || '';
      if (clave) {
        delete firmasExpedienteRrhh[clave];
        pintarExpedienteRrhh();
      }
      return;
    }
    const firma = event.target.closest('[data-rrhh-signature]');
    if (!firma) return;
    firmaActivaExpedienteRrhh = firma.dataset.rrhhSignature || '';
    bootstrap.Modal.getOrCreateInstance(modalFirmaExpedienteRrhh, { backdrop: false })?.show();
  });

  contenedorExpedienteRrhh?.addEventListener('keydown', function (event) {
    if (event.key !== 'Enter' && event.key !== ' ') return;
    const firma = event.target.closest('[data-rrhh-signature]');
    if (!firma) return;
    event.preventDefault();
    firmaActivaExpedienteRrhh = firma.dataset.rrhhSignature || '';
    bootstrap.Modal.getOrCreateInstance(modalFirmaExpedienteRrhh, { backdrop: false })?.show();
  });

  modalFirmaExpedienteRrhh?.addEventListener('shown.bs.modal', function () {
    if (modalFirmaExpedienteRrhh.parentNode !== document.body) {
      document.body.appendChild(modalFirmaExpedienteRrhh);
    }
    modalFirmaExpedienteRrhh.style.setProperty('z-index', '100060', 'important');
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });
    setTimeout(() => {
      ajustarCanvasFirmaExpediente();
      limpiarCanvasFirmaExpediente();
    }, 40);
  });

  modalFirmaExpedienteRrhh?.addEventListener('hidden.bs.modal', function () {
    modalFirmaExpedienteRrhh.style.removeProperty('z-index');
    firmaActivaExpedienteRrhh = '';
    firmaDibujandoRrhh = false;
  });

  canvasFirmaExpedienteRrhh?.addEventListener('pointerdown', function (event) {
    event.preventDefault();
    firmaDibujandoRrhh = true;
    const ctx = canvasFirmaExpedienteRrhh.getContext('2d');
    const punto = puntoCanvasFirma(event);
    ctx.beginPath();
    ctx.moveTo(punto.x, punto.y);
    canvasFirmaExpedienteRrhh.setPointerCapture?.(event.pointerId);
  });

  canvasFirmaExpedienteRrhh?.addEventListener('pointermove', function (event) {
    if (!firmaDibujandoRrhh) return;
    event.preventDefault();
    const ctx = canvasFirmaExpedienteRrhh.getContext('2d');
    const punto = puntoCanvasFirma(event);
    ctx.lineTo(punto.x, punto.y);
    ctx.stroke();
    firmaTieneTrazosRrhh = true;
  });

  ['pointerup', 'pointercancel', 'pointerleave'].forEach(nombreEvento => {
    canvasFirmaExpedienteRrhh?.addEventListener(nombreEvento, function () {
      firmaDibujandoRrhh = false;
    });
  });

  btnRepetirFirmaExpedienteRrhh?.addEventListener('click', limpiarCanvasFirmaExpediente);

  btnAceptarFirmaExpedienteRrhh?.addEventListener('click', function () {
    if (!firmaActivaExpedienteRrhh || !canvasFirmaExpedienteRrhh) return;
    if (!firmaTieneTrazosRrhh) {
      Swal.fire('Firma vac&iacute;a', 'Dibuja la firma antes de aceptarla.', 'info');
      return;
    }
    firmasExpedienteRrhh[firmaActivaExpedienteRrhh] = canvasFirmaExpedienteRrhh.toDataURL('image/png');
    const target = contenedorExpedienteRrhh?.querySelector(`[data-rrhh-signature="${firmaActivaExpedienteRrhh}"]`);
    const etiqueta = target?.querySelector('.rrhh-expediente-signature-line')?.textContent?.trim() || 'Firma';
    if (target) {
      target.innerHTML = `<img class="rrhh-expediente-signature-img" src="${firmasExpedienteRrhh[firmaActivaExpedienteRrhh]}" alt="Firma ${escapeRrhhAttr(etiqueta)}"><div class="rrhh-expediente-signature-line">${escapeRrhhHtml(etiqueta)}</div>`;
    }
    bootstrap.Modal.getInstance(modalFirmaExpedienteRrhh)?.hide();
  });

  btnLimpiarTodoExpedienteRrhh?.addEventListener('click', function () {
    limpiarPruebasExpedienteRrhh(true);
  });

  inputIncluirFotoExpedienteRrhh?.addEventListener('change', function () {
    incluirFotoExpedienteRrhh = this.checked;
    pintarExpedienteRrhh();
  });

  btnCambiarFotoExpedienteRrhh?.addEventListener('click', function () {
    inputFotoExpedienteRrhh?.click();
  });

  btnAjustarFotoExpedienteRrhh?.addEventListener('click', function () {
    fotoFitExpedienteRrhh = 'cover';
    abrirEditorFotoRrhh('expediente');
  });

  btnAbrirImportacionDocsRrhh?.addEventListener('click', function () {
    bootstrap.Modal.getOrCreateInstance(modalImportarDocumentosRrhh, { backdrop: false })?.show();
  });

  btnRrhhImportSeleccionarArchivos?.addEventListener('click', function () {
    inputRrhhImportArchivos?.click();
  });

  btnRrhhImportSeleccionarCarpeta?.addEventListener('click', function () {
    inputRrhhImportCarpeta?.click();
  });

  inputRrhhImportArchivos?.addEventListener('change', function () {
    rrhhImportDocsSetFiles(this.files);
  });

  inputRrhhImportCarpeta?.addEventListener('change', function () {
    rrhhImportDocsSetFiles(this.files);
  });

  btnRrhhImportImportar?.addEventListener('click', rrhhImportDocsImportar);

  btnRrhhImportLimpiar?.addEventListener('click', function () {
    rrhhImportDocsLimpiarSeleccion();
  });

  rrhhImportDocsTabla?.addEventListener('change', function (event) {
    const select = event.target.closest('[data-rrhh-import-doc-type]');
    if (!select || !rrhhImportDocsAnalisis) return;
    const sourceIndex = Number(select.getAttribute('data-rrhh-import-doc-type') || 0);
    const item = (rrhhImportDocsAnalisis.items || []).find(row => Number(row.source_index || 0) === sourceIndex);
    if (!item) return;
    item.id_documento = Number(select.value || 0) || null;
    item.documento_manual = Number(select.value || 0) > 0;
    rrhhImportDocsAnalizar();
  });

  rrhhImportDocsTabla?.addEventListener('click', function (event) {
    const btn = event.target.closest('[data-rrhh-import-preview]');
    if (!btn) return;
    rrhhImportDocsAbrirDocumento(btn.getAttribute('data-rrhh-import-preview'));
  });

  modalRrhhImportPreview?.addEventListener('hidden.bs.modal', function () {
    if (rrhhImportPreviewFrame) {
      rrhhImportPreviewFrame.removeAttribute('src');
    }
    if (rrhhImportPreviewUrl) {
      URL.revokeObjectURL(rrhhImportPreviewUrl);
      rrhhImportPreviewUrl = '';
    }
    modalRrhhImportPreview.style.removeProperty('z-index');
  });

  modalRrhhImportPreview?.addEventListener('shown.bs.modal', function () {
    modalRrhhImportPreview.style.setProperty('z-index', '100070', 'important');
  });

  modalImportarDocumentosRrhh?.addEventListener('shown.bs.modal', function () {
    if (modalImportarDocumentosRrhh.parentNode !== document.body) {
      document.body.appendChild(modalImportarDocumentosRrhh);
    }
    modalImportarDocumentosRrhh.style.setProperty('z-index', '100055', 'important');
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });
  });

  modalImportarDocumentosRrhh?.addEventListener('hidden.bs.modal', function () {
    modalImportarDocumentosRrhh.style.removeProperty('z-index');
    rrhhImportDocsLimpiarSeleccion();
  });

  inputFotoExpedienteRrhh?.addEventListener('change', function () {
    cargarFotoExpedienteDesdeArchivo(this.files && this.files[0] ? this.files[0] : null);
    this.value = '';
  });

  [dropzoneFotoExpedienteRrhh, contenedorExpedienteRrhh].forEach(target => {
    target?.addEventListener('dragover', function (event) {
      const destino = event.target.closest('.rrhh-expediente-photo-dropzone, .rrhh-expediente-photo');
      if (!destino) return;
      event.preventDefault();
      destino.classList.add('is-dragover');
    });
    target?.addEventListener('dragleave', function (event) {
      event.target.closest('.rrhh-expediente-photo-dropzone, .rrhh-expediente-photo')?.classList.remove('is-dragover');
    });
    target?.addEventListener('drop', function (event) {
      const destino = event.target.closest('.rrhh-expediente-photo-dropzone, .rrhh-expediente-photo');
      if (!destino) return;
      event.preventDefault();
      destino.classList.remove('is-dragover');
      cargarFotoExpedienteDesdeArchivo(event.dataTransfer?.files?.[0] || null);
    });
  });

  contenedorExpedienteRrhh?.addEventListener('pointerdown', function (event) {
    const img = event.target.closest('.rrhh-expediente-photo-img');
    if (!img || fotoFitExpedienteRrhh !== 'cover') return;
    const wrap = img.closest('.rrhh-expediente-photo');
    if (!wrap) return;
    event.preventDefault();
    fotoDragExpedienteRrhh = {
      pointerId: event.pointerId,
      startX: event.clientX,
      startY: event.clientY,
      baseX: fotoPosXExpedienteRrhh,
      baseY: fotoPosYExpedienteRrhh,
      width: Math.max(1, wrap.clientWidth),
      height: Math.max(1, wrap.clientHeight)
    };
    img.setPointerCapture?.(event.pointerId);
  });

  contenedorExpedienteRrhh?.addEventListener('pointermove', function (event) {
    if (!fotoDragExpedienteRrhh || fotoDragExpedienteRrhh.pointerId !== event.pointerId) return;
    const dx = ((event.clientX - fotoDragExpedienteRrhh.startX) / fotoDragExpedienteRrhh.width) * 80;
    const dy = ((event.clientY - fotoDragExpedienteRrhh.startY) / fotoDragExpedienteRrhh.height) * 80;
    fotoPosXExpedienteRrhh = Math.max(0, Math.min(100, fotoDragExpedienteRrhh.baseX + dx));
    fotoPosYExpedienteRrhh = Math.max(0, Math.min(100, fotoDragExpedienteRrhh.baseY + dy));
    aplicarPosicionFotoExpediente();
  });

  ['pointerup', 'pointercancel'].forEach(nombreEvento => {
    contenedorExpedienteRrhh?.addEventListener(nombreEvento, function () {
      fotoDragExpedienteRrhh = null;
    });
  });

  const btnCredencialRrhhActivo = btnGenerarCredencialRrhh ? btnGenerarCredencialRrhh.cloneNode(true) : null;
  if (btnGenerarCredencialRrhh && btnCredencialRrhhActivo) {
    btnGenerarCredencialRrhh.replaceWith(btnCredencialRrhhActivo);
    btnCredencialRrhhActivo.addEventListener('click', function () {
      if (!inputEditIdRrhh?.value) {
        Swal.fire('Credencial RR.HH.', 'Primero guarda o selecciona un usuario para generar su credencial.', 'info');
        return;
      }
      if (!pintarCredencialRrhh()) {
        Swal.fire('Credencial RR.HH.', 'No se pudo preparar la vista de credencial.', 'error');
        return;
      }
      abriendoCredencialRrhh = true;
      bootstrap.Modal.getInstance(modal)?.hide();
    });
  }

  btnVolverRrhhDesdeCredencial?.addEventListener('click', function () {
    volverDesdeCredencialRrhh = true;
    bootstrap.Modal.getInstance(modalCredencialRrhh)?.hide();
  });

  btnImprimirCredencialRrhh?.addEventListener('click', function () {
    const modalBody = modalCredencialRrhh?.querySelector('.modal-body');
    if (modalBody) modalBody.scrollTop = 0;
    window.scrollTo(0, 0);
    document.body.classList.add('print-rrhh-credencial');
    window.print();
    setTimeout(() => document.body.classList.remove('print-rrhh-credencial'), 300);
  });

  modalExpedienteRrhh?.addEventListener('show.bs.modal', function () {
    if (modalExpedienteRrhh.parentNode !== document.body) {
      document.body.appendChild(modalExpedienteRrhh);
    }
    showRrhhScrim();
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });
    modalExpedienteRrhh.style.setProperty('z-index', '100040', 'important');
  });

  modalExpedienteRrhh?.addEventListener('shown.bs.modal', function () {
    showRrhhScrim();
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });
  });

  modalExpedienteRrhh?.addEventListener('hidden.bs.modal', function () {
    modalExpedienteRrhh.style.removeProperty('z-index');
    document.body.classList.remove('print-rrhh-expediente');
    hideRrhhScrim();
    if (volverDesdeExpedienteRrhh) {
      volverDesdeExpedienteRrhh = false;
      bootstrap.Modal.getOrCreateInstance(modal, modalOptionsRrhh)?.show();
      return;
    }
    resetRrhhFormCompleto();
  });

  document.querySelectorAll('input[name="rrhhCredencialOrientacion"]').forEach(input => {
    input.addEventListener('change', function () {
      orientacionCredencialRrhh = this.value === 'horizontal' ? 'horizontal' : 'vertical';
      pintarCredencialRrhh();
    });
  });

  btnCambiarFotoCredencialRrhh?.addEventListener('click', function () {
    inputFotoCredencialRrhh?.click();
  });

  inputFotoCredencialRrhh?.addEventListener('change', function () {
    const file = this.files && this.files[0] ? this.files[0] : null;
    if (!file) return;
    if (!file.type || !file.type.startsWith('image/')) {
      Swal.fire('Foto no válida', 'Selecciona una imagen para la credencial.', 'warning');
      this.value = '';
      return;
    }
    if (fotoTemporalCredencialRrhh && fotoTemporalCredencialRrhh.startsWith('blob:')) {
      URL.revokeObjectURL(fotoTemporalCredencialRrhh);
    }
    fotoTemporalCredencialRrhh = URL.createObjectURL(file);
    fotoFitCredencialRrhh = 'cover';
    fotoPosXCredencialRrhh = 50;
    fotoPosYCredencialRrhh = 50;
    fotoScaleCredencialRrhh = 1;
    posicionFotoCredencialRrhh = 'center center';
    pintarCredencialRrhh();
    abrirEditorFotoRrhh('credencial');
  });

  modalCredencialRrhh?.addEventListener('click', function (event) {
    const btnPos = event.target.closest('[data-rrhh-photo-pos]');
    if (!btnPos) return;
    const pos = btnPos.dataset.rrhhPhotoPos;
    posicionFotoCredencialRrhh = pos === 'top' ? 'center top' : (pos === 'bottom' ? 'center bottom' : 'center center');
    pintarCredencialRrhh();
  });

  function cargarFotoCredencialDesdeDrop(file) {
    if (!file) return;
    if (!file.type || !file.type.startsWith('image/')) {
      Swal.fire('Foto no válida', 'Selecciona una imagen para la credencial.', 'warning');
      return;
    }
    if (fotoTemporalCredencialRrhh && fotoTemporalCredencialRrhh.startsWith('blob:')) {
      URL.revokeObjectURL(fotoTemporalCredencialRrhh);
    }
    fotoTemporalCredencialRrhh = URL.createObjectURL(file);
    fotoFitCredencialRrhh = 'cover';
    fotoPosXCredencialRrhh = 50;
    fotoPosYCredencialRrhh = 50;
    fotoScaleCredencialRrhh = 1;
    posicionFotoCredencialRrhh = 'center center';
    pintarCredencialRrhh();
    abrirEditorFotoRrhh('credencial');
  }

  function aplicarPosicionFotoCredencial() {
    modalCredencialRrhh?.querySelectorAll('.rrhh-id-photo').forEach(img => {
      const fitSeguro = fotoFitCredencialRrhh === 'contain' ? 'contain' : 'cover';
      const posicion = fitSeguro === 'contain' ? '50% 50%' : `${fotoPosXCredencialRrhh}% ${fotoPosYCredencialRrhh}%`;
      const transformacion = fitSeguro === 'contain' ? 'none' : `translate(${50 - fotoPosXCredencialRrhh}%, ${50 - fotoPosYCredencialRrhh}%) scale(${fotoScaleCredencialRrhh})`;
      img.style.setProperty('--rrhh-photo-fit', fitSeguro);
      img.style.setProperty('--rrhh-photo-position', posicion);
      img.style.setProperty('--rrhh-photo-transform', transformacion);
      img.style.setProperty('object-fit', fitSeguro, 'important');
      img.style.setProperty('object-position', posicion, 'important');
      img.style.setProperty('transform', transformacion, 'important');
      const wrap = img.closest('.rrhh-id-photo-wrap');
      wrap?.classList.toggle('is-cover', fitSeguro === 'cover');
      wrap?.classList.toggle('is-contain', fitSeguro !== 'cover');
    });
    actualizarPreviewEditorFotoCredencial();
  }

  function actualizarBotonesFotoCredencialRrhh() {
    btnFotoCompletaCredencialRrhh?.classList.toggle('active', fotoFitCredencialRrhh === 'contain');
    btnAjustarFotoCredencialRrhh?.classList.toggle('active', fotoFitCredencialRrhh !== 'contain');
  }

  function fotoEditorSrcActual() {
    return fotoEditorContextoRrhh === 'expediente' ? fotoExpedienteSrcActual() : fotoCredencialSrcActual();
  }

  function estadoFotoEditorActual() {
    if (fotoEditorContextoRrhh === 'expediente') {
      return {
        fit: fotoFitExpedienteRrhh,
        x: fotoPosXExpedienteRrhh,
        y: fotoPosYExpedienteRrhh,
        scale: fotoScaleExpedienteRrhh
      };
    }
    return {
      fit: fotoFitCredencialRrhh,
      x: fotoPosXCredencialRrhh,
      y: fotoPosYCredencialRrhh,
      scale: fotoScaleCredencialRrhh
    };
  }

  function guardarEstadoInicialEditorFotoRrhh() {
    fotoEditorEstadoInicialRrhh = { ...estadoFotoEditorActual() };
  }

  function aplicarEstadoEditorFotoRrhh(estado) {
    if (!estado) return;
    if (fotoEditorContextoRrhh === 'expediente') {
      fotoFitExpedienteRrhh = estado.fit === 'contain' ? 'contain' : 'cover';
      fotoPosXExpedienteRrhh = Number.isFinite(Number(estado.x)) ? Number(estado.x) : 50;
      fotoPosYExpedienteRrhh = Number.isFinite(Number(estado.y)) ? Number(estado.y) : 50;
      fotoScaleExpedienteRrhh = Number.isFinite(Number(estado.scale)) ? Number(estado.scale) : 1;
      pintarExpedienteRrhh();
      actualizarPreviewEditorFotoCredencial();
      return;
    }
    fotoFitCredencialRrhh = estado.fit === 'contain' ? 'contain' : 'cover';
    fotoPosXCredencialRrhh = Number.isFinite(Number(estado.x)) ? Number(estado.x) : 50;
    fotoPosYCredencialRrhh = Number.isFinite(Number(estado.y)) ? Number(estado.y) : 50;
    fotoScaleCredencialRrhh = Number.isFinite(Number(estado.scale)) ? Number(estado.scale) : 1;
    actualizarEditorFotoEnVivo();
  }

  function actualizarVistaVivaEditorCredencial() {
    if (!livePreviewFotoCredencialRrhh) return;
    if (fotoEditorContextoRrhh !== 'credencial') {
      livePreviewFotoCredencialRrhh.innerHTML = '<div class="rrhh-photo-editor-empty">Vista disponible para credencial</div>';
      return;
    }
    pintarCredencialRrhh();
    livePreviewFotoCredencialRrhh.innerHTML = contenedorCredencialFrente?.innerHTML || '';
  }

  function actualizarEditorFotoEnVivo() {
    actualizarPreviewEditorFotoCredencial();
    actualizarVistaVivaEditorCredencial();
  }

  function abrirEditorFotoRrhh(contexto = 'credencial') {
    fotoEditorContextoRrhh = contexto === 'expediente' ? 'expediente' : 'credencial';
    frameEditorFotoCredencialRrhh?.classList.toggle('is-credential', fotoEditorContextoRrhh === 'credencial');
    guardarEstadoInicialEditorFotoRrhh();
    const src = fotoEditorSrcActual();
    if (!src) {
      if (fotoEditorContextoRrhh === 'expediente') inputFotoExpedienteRrhh?.click();
      else inputFotoCredencialRrhh?.click();
      return;
    }
    actualizarEditorFotoEnVivo();
    bootstrap.Modal.getOrCreateInstance(modalAjustarFotoCredencialRrhh, { backdrop: false })?.show();
  }

  function actualizarPreviewEditorFotoCredencial() {
    if (!frameEditorFotoCredencialRrhh) return;
    const src = fotoEditorSrcActual();
    if (!src) {
      frameEditorFotoCredencialRrhh.innerHTML = '<div class="rrhh-photo-editor-empty">Selecciona una foto</div>';
      return;
    }
    const estado = estadoFotoEditorActual();
    const fitSeguro = estado.fit === 'contain' ? 'contain' : 'cover';
    const posX = fitSeguro === 'contain' ? 50 : estado.x;
    const posY = fitSeguro === 'contain' ? 50 : estado.y;
    const scale = fitSeguro === 'contain' ? 1 : estado.scale;
    const transformacion = fitSeguro === 'contain' ? 'none' : `translate(${50 - posX}%, ${50 - posY}%) scale(${scale})`;
    const imgActual = frameEditorFotoCredencialRrhh.querySelector('.rrhh-photo-editor-img');
    if (imgActual && imgActual.getAttribute('src') === src) {
      imgActual.style.objectFit = fitSeguro;
      imgActual.style.objectPosition = `${posX}% ${posY}%`;
      imgActual.style.transform = transformacion;
      return;
    }
    frameEditorFotoCredencialRrhh.innerHTML = `<img class="rrhh-photo-editor-img" src="${escapeRrhhAttr(src)}" alt="Ajuste de foto" style="object-fit:${fitSeguro}; object-position:${posX}% ${posY}%; transform:${transformacion};" draggable="false">`;
  }

  function ponerFotoCompletaCredencial() {
    ponerFotoCompletaContextoRrhh('credencial');
  }

  function ponerFotoCompletaContextoRrhh(contexto = fotoEditorContextoRrhh) {
    if (contexto === 'expediente') {
      fotoFitExpedienteRrhh = 'contain';
      fotoPosXExpedienteRrhh = 50;
      fotoPosYExpedienteRrhh = 50;
      fotoScaleExpedienteRrhh = 1;
      pintarExpedienteRrhh();
      actualizarPreviewEditorFotoCredencial();
      return;
    }
    fotoFitCredencialRrhh = 'contain';
    fotoPosXCredencialRrhh = 50;
    fotoPosYCredencialRrhh = 50;
    fotoScaleCredencialRrhh = 1;
    posicionFotoCredencialRrhh = 'center center';
    actualizarBotonesFotoCredencialRrhh();
    pintarCredencialRrhh();
  }

  function aplicarRecorteFotoContextoRrhh() {
    if (fotoEditorContextoRrhh === 'expediente') {
      fotoFitExpedienteRrhh = 'cover';
      pintarExpedienteRrhh();
      return;
    }
    fotoFitCredencialRrhh = 'cover';
    actualizarBotonesFotoCredencialRrhh();
    pintarCredencialRrhh();
  }

  [dropzoneFotoCredencialRrhh, modalCredencialRrhh].forEach(target => {
    target?.addEventListener('dragover', function (event) {
      const destino = event.target.closest('.rrhh-photo-dropzone, .rrhh-id-photo-wrap');
      if (!destino) return;
      event.preventDefault();
      destino.classList.add('is-dragover');
    });
    target?.addEventListener('dragleave', function (event) {
      event.target.closest('.rrhh-photo-dropzone, .rrhh-id-photo-wrap')?.classList.remove('is-dragover');
    });
    target?.addEventListener('drop', function (event) {
      const destino = event.target.closest('.rrhh-photo-dropzone, .rrhh-id-photo-wrap');
      if (!destino) return;
      event.preventDefault();
      destino.classList.remove('is-dragover');
      cargarFotoCredencialDesdeDrop(event.dataTransfer?.files?.[0] || null);
    });
  });

  modalCredencialRrhh?.addEventListener('pointerdown', function (event) {
    const img = event.target.closest('.rrhh-id-photo');
    if (!img || fotoFitCredencialRrhh !== 'cover') return;
    const wrap = img.closest('.rrhh-id-photo-wrap');
    if (!wrap) return;
    event.preventDefault();
    fotoDragCredencialRrhh = {
      pointerId: event.pointerId,
      startX: event.clientX,
      startY: event.clientY,
      baseX: fotoPosXCredencialRrhh,
      baseY: fotoPosYCredencialRrhh,
      width: Math.max(1, wrap.clientWidth),
      height: Math.max(1, wrap.clientHeight)
    };
    img.setPointerCapture?.(event.pointerId);
  });

  modalCredencialRrhh?.addEventListener('pointermove', function (event) {
    if (!fotoDragCredencialRrhh || fotoDragCredencialRrhh.pointerId !== event.pointerId) return;
    const dx = ((event.clientX - fotoDragCredencialRrhh.startX) / fotoDragCredencialRrhh.width) * 80;
    const dy = ((event.clientY - fotoDragCredencialRrhh.startY) / fotoDragCredencialRrhh.height) * 80;
    fotoPosXCredencialRrhh = Math.max(0, Math.min(100, fotoDragCredencialRrhh.baseX - dx));
    fotoPosYCredencialRrhh = Math.max(0, Math.min(100, fotoDragCredencialRrhh.baseY - dy));
    aplicarPosicionFotoCredencial();
  });

  ['pointerup', 'pointercancel'].forEach(nombreEvento => {
    modalCredencialRrhh?.addEventListener(nombreEvento, function () {
      fotoDragCredencialRrhh = null;
    });
  });

  modalCredencialRrhh?.addEventListener('click', function (event) {
    const btnFit = event.target.closest('[data-rrhh-photo-fit]');
    if (!btnFit) return;
  });

  modalCredencialRrhh?.addEventListener('click', function (event) {
    const foto = event.target.closest('.rrhh-id-photo-wrap');
    if (!foto || !modalCredencialRrhh.contains(foto)) return;
    if (event.target.closest('button, a, input, select, textarea')) return;
    event.preventDefault();
    fotoFitCredencialRrhh = 'cover';
    actualizarBotonesFotoCredencialRrhh();
    pintarCredencialRrhh();
    abrirEditorFotoRrhh('credencial');
  });

  btnAjustarFotoCredencialRrhh?.addEventListener('click', function () {
    fotoFitCredencialRrhh = 'cover';
    actualizarBotonesFotoCredencialRrhh();
    pintarCredencialRrhh();
    abrirEditorFotoRrhh('credencial');
  });

  btnFotoCompletaCredencialRrhh?.addEventListener('click', ponerFotoCompletaCredencial);

  btnEditorFotoCompletaCredencialRrhh?.addEventListener('click', function () {
    ponerFotoCompletaContextoRrhh(fotoEditorContextoRrhh);
    actualizarEditorFotoEnVivo();
  });

  btnAplicarAjusteFotoCredencialRrhh?.addEventListener('click', function () {
    aplicarRecorteFotoContextoRrhh();
    bootstrap.Modal.getInstance(modalAjustarFotoCredencialRrhh)?.hide();
  });

  btnRestaurarAjusteFotoCredencialRrhh?.addEventListener('click', function () {
    aplicarEstadoEditorFotoRrhh(fotoEditorEstadoInicialRrhh);
  });

  modalAjustarFotoCredencialRrhh?.addEventListener('show.bs.modal', function () {
    if (modalAjustarFotoCredencialRrhh.parentNode !== document.body) {
      document.body.appendChild(modalAjustarFotoCredencialRrhh);
    }
    modalCredencialRrhh?.classList.add('rrhh-hidden-for-photo-editor');
    showRrhhScrim();
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
    modalAjustarFotoCredencialRrhh.style.setProperty('z-index', '100060', 'important');
    actualizarEditorFotoEnVivo();
  });

  modalAjustarFotoCredencialRrhh?.addEventListener('hidden.bs.modal', function () {
    modalAjustarFotoCredencialRrhh.style.removeProperty('z-index');
    modalCredencialRrhh?.classList.remove('rrhh-hidden-for-photo-editor');
    fotoEditorDragCredencialRrhh = null;
    if (modalCredencialRrhh?.classList.contains('show')) {
      showRrhhScrim();
    }
  });

  frameEditorFotoCredencialRrhh?.addEventListener('pointerdown', function (event) {
    const img = event.target.closest('.rrhh-photo-editor-img');
    if (!img || estadoFotoEditorActual().fit === 'contain') return;
    event.preventDefault();
    const estado = estadoFotoEditorActual();
    fotoEditorDragCredencialRrhh = {
      pointerId: event.pointerId,
      startX: event.clientX,
      startY: event.clientY,
      baseX: estado.x,
      baseY: estado.y,
      width: Math.max(1, frameEditorFotoCredencialRrhh.clientWidth),
      height: Math.max(1, frameEditorFotoCredencialRrhh.clientHeight)
    };
    img.setPointerCapture?.(event.pointerId);
  });

  frameEditorFotoCredencialRrhh?.addEventListener('pointermove', function (event) {
    if (!fotoEditorDragCredencialRrhh || fotoEditorDragCredencialRrhh.pointerId !== event.pointerId) return;
    const dx = ((event.clientX - fotoEditorDragCredencialRrhh.startX) / fotoEditorDragCredencialRrhh.width) * 85;
    const dy = ((event.clientY - fotoEditorDragCredencialRrhh.startY) / fotoEditorDragCredencialRrhh.height) * 85;
    if (fotoEditorContextoRrhh === 'expediente') {
      fotoPosXExpedienteRrhh = Math.max(0, Math.min(100, fotoEditorDragCredencialRrhh.baseX - dx));
      fotoPosYExpedienteRrhh = Math.max(0, Math.min(100, fotoEditorDragCredencialRrhh.baseY - dy));
    } else {
      fotoPosXCredencialRrhh = Math.max(0, Math.min(100, fotoEditorDragCredencialRrhh.baseX - dx));
      fotoPosYCredencialRrhh = Math.max(0, Math.min(100, fotoEditorDragCredencialRrhh.baseY - dy));
    }
    actualizarEditorFotoEnVivo();
  });

  ['pointerup', 'pointercancel', 'pointerleave'].forEach(nombreEvento => {
    frameEditorFotoCredencialRrhh?.addEventListener(nombreEvento, function () {
      fotoEditorDragCredencialRrhh = null;
    });
  });

  frameEditorFotoCredencialRrhh?.addEventListener('wheel', function (event) {
    if (!fotoEditorSrcActual() || estadoFotoEditorActual().fit === 'contain') return;
    event.preventDefault();
    const delta = event.deltaY < 0 ? 0.08 : -0.08;
    if (fotoEditorContextoRrhh === 'expediente') {
      fotoScaleExpedienteRrhh = Math.max(1, Math.min(2.5, fotoScaleExpedienteRrhh + delta));
    } else {
      fotoScaleCredencialRrhh = Math.max(1, Math.min(2.5, fotoScaleCredencialRrhh + delta));
    }
    actualizarEditorFotoEnVivo();
  }, { passive: false });

  modalCredencialRrhh?.addEventListener('show.bs.modal', function () {
    if (modalCredencialRrhh.parentNode !== document.body) {
      document.body.appendChild(modalCredencialRrhh);
    }
    showRrhhScrim();
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });
    modalCredencialRrhh.style.setProperty('z-index', '100040', 'important');
  });

  modalCredencialRrhh?.addEventListener('shown.bs.modal', function () {
    showRrhhScrim();
    document.querySelectorAll('.modal-backdrop').forEach(function (backdrop) {
      backdrop.remove();
    });
  });

  modalCredencialRrhh?.addEventListener('hidden.bs.modal', function () {
    modalCredencialRrhh.style.removeProperty('z-index');
    document.body.classList.remove('print-rrhh-credencial');
    hideRrhhScrim();
    if (volverDesdeCredencialRrhh) {
      volverDesdeCredencialRrhh = false;
      bootstrap.Modal.getOrCreateInstance(modal, modalOptionsRrhh)?.show();
      return;
    }
    if (fotoTemporalCredencialRrhh && fotoTemporalCredencialRrhh.startsWith('blob:')) {
      URL.revokeObjectURL(fotoTemporalCredencialRrhh);
    }
    fotoTemporalCredencialRrhh = '';
    fotoFitCredencialRrhh = 'cover';
    fotoPosXCredencialRrhh = 50;
    fotoPosYCredencialRrhh = 50;
    fotoScaleCredencialRrhh = 1;
    posicionFotoCredencialRrhh = 'center center';
    fotoDragCredencialRrhh = null;
    fotoEditorDragCredencialRrhh = null;
    resetRrhhFormCompleto();
  });

  function htmlEscapeRrhhLegacy(value) {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
  }

  function swalUsuarioGuardadoRrhh(data, esEdicion) {
    const legacy = data && data.datos && data.datos.legacy_sync ? data.datos.legacy_sync : null;
    if (!legacy) {
      return Swal.fire({
        icon: 'success',
        title: esEdicion ? 'Usuario actualizado' : 'Usuario registrado',
        text: data.mensaje || 'Usuario RR.HH. guardado correctamente.',
        confirmButtonText: 'Aceptar'
      });
    }

    const resultado = String(legacy.resultado || '').toLowerCase();
    const mensaje = legacy.mensaje || '';
    const numeroEmpleadoLegacy = (data.datos && data.datos.numero_empleado) || legacy.external_id || '';
    const creadoLegacy = !!(legacy.detalle && legacy.detalle.usuario_legacy_creado);
    const reactivadoLegacy = !!(legacy.detalle && legacy.detalle.usuario_legacy_reactivado);

    if (!['actualizado', 'sin_cambios'].includes(resultado)) {
      return Swal.fire({
        icon: 'success',
        title: esEdicion ? 'Usuario actualizado' : 'Usuario registrado',
        text: data.mensaje || 'Usuario RR.HH. guardado correctamente.',
        confirmButtonText: 'Aceptar'
      });
    }

    let icon = 'success';
    let title = esEdicion ? 'Usuario actualizado' : 'Usuario registrado';
    let estado = 'Sincronizado en Legacy';
    let estadoDetalle = 'Los cambios ya quedaron aplicados en Legacy.';
    let badgeClass = 'bg-success';
    let panelClass = 'border-success bg-label-success';
    let iconClass = 'fa-circle-check text-success';

    if (resultado === 'sin_cambios') {
      estado = 'Legacy ya estaba sincronizado';
      estadoDetalle = 'No hubo diferencias que actualizar en Legacy.';
      badgeClass = 'bg-info';
      panelClass = 'border-info bg-label-info';
      iconClass = 'fa-circle-info text-info';
    } else if (resultado === 'actualizado') {
      if (creadoLegacy) {
        estado = 'Creado y sincronizado en Legacy';
        estadoDetalle = 'El usuario ya existe en Legacy con usuario, contraseña, rol y jerarquía actualizados.';
      } else if (reactivadoLegacy) {
        estado = 'Reactivado y sincronizado en Legacy';
        estadoDetalle = 'El usuario estaba dado de baja en Legacy y fue reactivado.';
      } else {
        estado = 'Actualizado en Legacy';
        estadoDetalle = 'Usuario, contraseña, rol y jerarquía fueron sincronizados con Legacy.';
      }
    }

    return Swal.fire({
      icon,
      title,
      html:
        '<div class="text-start">' +
          '<div class="mb-2">' + htmlEscapeRrhhLegacy(data.mensaje || 'Datos RR.HH. actualizados correctamente.') + '</div>' +
          '<div class="border rounded-3 p-3 ' + panelClass + '">' +
            '<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-1">' +
              '<div class="fw-bold"><i class="fa ' + iconClass + ' me-1"></i>Sincronización Legacy</div>' +
              '<span class="badge ' + badgeClass + '">' + htmlEscapeRrhhLegacy(estado) + '</span>' +
            '</div>' +
            '<div class="small mb-2">' + estadoDetalle + '</div>' +
            '<div class="small text-muted">' +
              (resultado ? '<span class="me-2">Resultado: <strong>' + htmlEscapeRrhhLegacy(resultado) + '</strong></span>' : '') +
              (numeroEmpleadoLegacy ? '<span>No. empleado: <strong>' + htmlEscapeRrhhLegacy(numeroEmpleadoLegacy) + '</strong></span>' : '') +
            '</div>' +
          '</div>' +
          (mensaje ? '<div class="small text-muted mt-2">' + htmlEscapeRrhhLegacy(mensaje) + '</div>' : '') +
        '</div>',
      confirmButtonText: 'Aceptar'
    });
  }

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    if (!validarRrhhFormularioCompleto()) return;
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;

    try {
      const endpoint = form.dataset.mode === 'editar'
        ? '/CapHum/actualizarUsuarioRrhh'
        : '/CapHum/registrarUsuarioRrhh';

      Swal.fire({
        title: form.dataset.mode === 'editar' ? 'Actualizando usuario...' : 'Guardando usuario...',
        html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p style="margin-top: 1rem;">Guardando usuario...</p>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
      });

      const response = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(collectPayload())
      });
      const data = await response.json();

      if (!data.success) {
        throw new Error(data.mensaje || data.error || 'No se pudo registrar el usuario.');
      }

      await swalUsuarioGuardadoRrhh(data, form.dataset.mode === 'editar');
      bootstrap.Modal.getInstance(modal)?.hide();
      if (typeof llenarFiltros === 'function') llenarFiltros();
    } catch (error) {
      Swal.fire('Error', error.message || 'Error al registrar usuario RR.HH.', 'error');
    } finally {
      submitBtn.disabled = false;
    }
  });
})();

(function () {
  const modal = document.getElementById('modalActualizacionInfoPersona');
  const form = document.getElementById('formActualizacionInfoPersona');
  if (!modal || !form) return;

  const inputIdPersona = document.getElementById('actualizacion_info_id_persona');
  const nombrePersona = document.getElementById('actualizacionInfoPersonaNombre');
  const checklist = document.getElementById('actualizacionInfoChecklist');
  const camposWrap = document.getElementById('actualizacionInfoCampos');
  const btnGuardar = document.getElementById('btnGuardarActualizacionInfo');
  const contador = document.getElementById('actualizacionInfoContador');
  let actuales = {};
  let actualizacionInfoCargando = false;
  const actualizacionInfoCache = new Map();
  let actualizacionInfoPrefetchTimer = null;

  const camposActualizacion = [
    { campo: 'telefono_principal', etiqueta: 'Teléfono principal', tipo: 'text', grupo: 'Contacto' },
    { campo: 'telefono_secundario', etiqueta: 'Teléfono secundario', tipo: 'text', grupo: 'Contacto' },
    { campo: 'correo', etiqueta: 'Correo electrónico', tipo: 'email', grupo: 'Contacto' },
    { campo: 'contacto_emergencia', etiqueta: 'Contacto de emergencia', tipo: 'textarea', grupo: 'Contacto' },
    { campo: 'codigo_postal', etiqueta: 'Código postal', tipo: 'text', grupo: 'Dirección', servicio: 'sepomex_cp' },
    { campo: 'domicilio', etiqueta: 'Domicilio completo', tipo: 'textarea', grupo: 'Dirección' },
    { campo: 'calle_avenida', etiqueta: 'Calle o avenida', tipo: 'text', grupo: 'Dirección', servicio: 'catalogo_calles' },
    { campo: 'numero_exterior', etiqueta: 'Número exterior', tipo: 'text', grupo: 'Dirección' },
    { campo: 'numero_interior', etiqueta: 'Número interior', tipo: 'text', grupo: 'Dirección' },
    { campo: 'colonia', etiqueta: 'Colonia', tipo: 'select', grupo: 'Dirección', servicio: 'catalogo_colonias' },
    { campo: 'municipio', etiqueta: 'Municipio', tipo: 'select', grupo: 'Dirección', servicio: 'catalogo_municipios' },
    { campo: 'estado', etiqueta: 'Estado', tipo: 'select', grupo: 'Dirección', servicio: 'catalogo_estados' },
  ];

  function escapeActualizacion(value) {
    const div = document.createElement('div');
    div.textContent = String(value ?? '');
    return div.innerHTML;
  }

  function valorActual(campo) {
    const value = actuales && actuales[campo] != null ? String(actuales[campo]).trim() : '';
    if (!value && actualizacionInfoCargando) return 'Cargando...';
    return value || 'N/A';
  }

  function personaListaActualizacion(idPersona) {
    const id = String(idPersona || '');
    const lista = Array.isArray(window.usuariosData)
      ? window.usuariosData
      : (typeof usuariosData !== 'undefined' && Array.isArray(usuariosData) ? usuariosData : []);
    return lista.find(item => String(item.id || '') === id) || null;
  }

  function nombreListaActualizacion(idPersona) {
    const persona = personaListaActualizacion(idPersona);
    if (!persona) return 'Cargando información actual...';
    const nombre = [persona.nombres, persona.segundo_nombre, persona.apellidop, persona.apellidom]
      .filter(Boolean)
      .join(' ')
      .trim();
    const numero = persona.numero_empleado ? `# ${persona.numero_empleado} Â· ` : '';
    return `${numero}${nombre || persona.nombre_completo || 'Trabajador'}`;
  }

  function textoActualizacion(value) {
    return String(value ?? '').trim();
  }

  function actualesDesdeLista(idPersona) {
    const persona = personaListaActualizacion(idPersona);
    if (!persona) return {};

    const domicilio = textoActualizacion(persona.domicilio_calle_texto || persona.domicilio || '');
    return {
      telefono_principal: textoActualizacion(persona.telefono_uno || persona.telefono || ''),
      telefono_secundario: textoActualizacion(persona.telefono_dos || ''),
      correo: textoActualizacion(persona.correo || ''),
      codigo_postal: textoActualizacion(persona.codigo_postal || ''),
      domicilio,
      calle_avenida: domicilio,
      numero_exterior: textoActualizacion(persona.domicilio_num_exterior || ''),
      numero_interior: textoActualizacion(persona.domicilio_num_interior || ''),
      colonia: textoActualizacion(persona.colonia || ''),
      municipio: textoActualizacion(persona.municipio || ''),
      estado: textoActualizacion(persona.estado || '')
    };
  }

  function resetActualizacionScroll() {
    modal.querySelectorAll('.actualizacion-info-panel-body').forEach(panel => {
      panel.scrollTop = 0;
    });
  }

  function showActualizacionInfoScrim() {
    let scrim = document.getElementById('rrhhModalScrim');
    if (!scrim) {
      scrim = document.createElement('div');
      scrim.id = 'rrhhModalScrim';
      scrim.setAttribute('aria-hidden', 'true');
      document.body.appendChild(scrim);
    }
    scrim.style.display = 'block';
  }

  function hideActualizacionInfoScrim() {
    const scrim = document.getElementById('rrhhModalScrim');
    if (scrim) scrim.style.display = 'none';
  }

  function pintarChecklist() {
    let grupoActual = '';
    checklist.innerHTML = camposActualizacion.map((item) => {
      const grupo = item.grupo || 'General';
      const header = grupo !== grupoActual
        ? `<div class="actualizacion-info-group">${escapeActualizacion(grupo)}</div>`
        : '';
      grupoActual = grupo;
      return `${header}<label class="actualizacion-info-check">
        <input class="form-check-input actualizacion-info-check-input" type="checkbox" value="${escapeActualizacion(item.campo)}" id="actInfo_${escapeActualizacion(item.campo)}">
        <div class="min-w-0">
          <span class="fw-semibold d-block text-truncate">${escapeActualizacion(item.etiqueta)}</span>
          <small class="text-muted">${escapeActualizacion(grupo)}</small>
        </div>
      </label>`;
    }).join('');
    resetActualizacionScroll();
  }

  function pintarCamposSeleccionados() {
    const seleccionados = Array.from(form.querySelectorAll('.actualizacion-info-check-input:checked'))
      .map(input => camposActualizacion.find(item => item.campo === input.value))
      .filter(Boolean);

    if (contador) {
      contador.textContent = `${seleccionados.length} seleccionado${seleccionados.length === 1 ? '' : 's'}`;
    }

    if (!seleccionados.length) {
      camposWrap.innerHTML = '<div class="actualizacion-info-empty">Selecciona campos para crear la solicitud.</div>';
      return;
    }

    camposWrap.innerHTML = seleccionados.map(item => `
      <div class="actualizacion-info-request-card">
        <input type="hidden" class="actualizacion-info-new" data-campo="${escapeActualizacion(item.campo)}" value="">
        <div class="actualizacion-info-request-icon">
          <i class="fa ${item.grupo === 'Dirección' ? 'fa-map-location-dot' : (item.grupo === 'Salud' ? 'fa-heart-pulse' : 'fa-address-card')}"></i>
        </div>
        <div class="min-w-0 flex-grow-1">
          <div class="d-flex align-items-start justify-content-between gap-2">
            <div class="fw-bold text-dark">${escapeActualizacion(item.etiqueta)}</div>
            <span class="badge bg-light text-muted border">${escapeActualizacion(item.grupo)}</span>
          </div>
          <div class="text-muted small mt-1">
            Se enviará este campo al gestor para que capture el valor actualizado en MaxikashApp.
          </div>
          <div class="actualizacion-info-request-meta">
            <span class="badge bg-primary-subtle text-primary border">Tipo: ${escapeActualizacion(item.tipo || 'text')}</span>
            ${item.servicio ? `<span class="badge bg-success-subtle text-success border">Catálogo: ${escapeActualizacion(item.servicio)}</span>` : ''}
          </div>
        </div>
      </div>
    `).join('');
  }

  async function fetchDatosActualizacion(idPersona) {
    const cacheKey = String(idPersona);
    let data = actualizacionInfoCache.get(cacheKey);
    if (!data) {
      data = fetch('/CapHum/obtenerDatosActualizacionInfoPersona', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ id_persona: idPersona })
      }).then(response => response.json());
      actualizacionInfoCache.set(cacheKey, data);
    }
    data = await data;
    if (!data.success) {
      actualizacionInfoCache.delete(cacheKey);
      throw new Error(data.mensaje || 'No se pudo cargar la información actual.');
    }
    actualizacionInfoCache.set(cacheKey, data);

    return data;
  }

  function aplicarDatosActualizacion(data) {
    actualizacionInfoCargando = false;
    actuales = data.datos?.actuales || actuales || {};
    const persona = data.datos?.persona || {};
    const numeroEmpleado = persona.numero_empleado ? `# ${persona.numero_empleado} Â· ` : '';
    nombrePersona.textContent = `${numeroEmpleado}${persona.nombre || 'Trabajador'}`;
    pintarCamposSeleccionados();
    resetActualizacionScroll();
  }

  async function cargarDatosActualizacion(idPersona) {
    aplicarDatosActualizacion(await fetchDatosActualizacion(idPersona));
  }

  window.precargarActualizacionInfoVisible = function (datos) {
    if (!window.puedeActualizarInfo) return;

    const lista = Array.isArray(datos) && datos.length
      ? datos
      : (Array.isArray(window.usuariosData) ? window.usuariosData : []);

    const ids = [];
    lista.forEach(item => {
      const id = String(item?.id || '');
      if (id && !actualizacionInfoCache.has(id) && !ids.includes(id)) {
        ids.push(id);
      }
    });

    if (!ids.length) return;

    clearTimeout(actualizacionInfoPrefetchTimer);
    actualizacionInfoPrefetchTimer = setTimeout(() => {
      const primeros = ids.slice(0, 12);
      const lote = fetch('/CapHum/obtenerDatosActualizacionInfoPersonas', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ ids_persona: primeros })
      }).then(response => response.json());

      primeros.forEach(id => {
        const key = String(id);
        const itemPromise = lote.then(data => {
          if (!data.success) {
            throw new Error(data.mensaje || 'No se pudo precargar la informacion.');
          }
          const item = data.datos?.personas?.[key];
          if (!item) {
            throw new Error('No se encontro la persona precargada.');
          }
          return { success: true, datos: item };
        });

        actualizacionInfoCache.set(key, itemPromise);
        itemPromise
          .then(data => {
            if (actualizacionInfoCache.get(key) === itemPromise) {
              actualizacionInfoCache.set(key, data);
            }
          })
          .catch(() => {
            if (actualizacionInfoCache.get(key) === itemPromise) {
              actualizacionInfoCache.delete(key);
            }
          });
      });
    }, 250);
  };

  window.abrirActualizacionInfoPersona = async function (idPersona) {
    if (!idPersona) return;
    if (modal.parentNode !== document.body) {
      document.body.appendChild(modal);
    }
    showActualizacionInfoScrim();
    const cacheKey = String(idPersona);
    const cacheHit = actualizacionInfoCache.get(cacheKey);
    const cacheListo = cacheHit && typeof cacheHit.then !== 'function' && cacheHit.success;
    actualizacionInfoCargando = !cacheListo;
    actuales = actualesDesdeLista(idPersona);
    inputIdPersona.value = String(idPersona);
    if (nombrePersona) nombrePersona.textContent = nombreListaActualizacion(idPersona);
    if (contador) contador.textContent = '0 seleccionados';
    pintarChecklist();
    pintarCamposSeleccionados();

    bootstrap.Modal.getOrCreateInstance(modal).show();
    resetActualizacionScroll();

    try {
      if (cacheListo) {
        aplicarDatosActualizacion(cacheHit);
      } else {
        await cargarDatosActualizacion(idPersona);
      }
    } catch (error) {
      actualizacionInfoCargando = false;
      pintarCamposSeleccionados();
      Swal.fire('Error', error.message || 'No se pudo cargar la información actual.', 'error');
    }
  };

  modal.addEventListener('show.bs.modal', showActualizacionInfoScrim);
  modal.addEventListener('shown.bs.modal', showActualizacionInfoScrim);
  modal.addEventListener('hidden.bs.modal', hideActualizacionInfoScrim);

  function cerrarModalActualizacionInfo() {
    return new Promise((resolve) => {
      const instance = bootstrap.Modal.getInstance(modal);
      if (!instance || !modal.classList.contains('show')) {
        hideActualizacionInfoScrim();
        resolve();
        return;
      }

      modal.addEventListener('hidden.bs.modal', function onHidden() {
        resolve();
      }, { once: true });
      instance.hide();
    });
  }

  checklist?.addEventListener('change', function (event) {
    if (event.target && event.target.classList.contains('actualizacion-info-check-input')) {
      pintarCamposSeleccionados();
    }
  });

  form.addEventListener('submit', async function (event) {
    event.preventDefault();
    const idPersona = parseInt(inputIdPersona.value || '0', 10);
    const seleccionados = Array.from(form.querySelectorAll('.actualizacion-info-check-input:checked'))
      .map(input => camposActualizacion.find(item => item.campo === input.value))
      .filter(Boolean);

    if (!idPersona || !seleccionados.length) {
      Swal.fire('Atención', 'Selecciona al menos un campo para actualizar.', 'warning');
      return;
    }

    const campos = [];
    for (const item of seleccionados) {
      const input = form.querySelector(`.actualizacion-info-new[data-campo="${item.campo}"]`);
      const valorNuevo = input ? input.value.trim() : '';
      campos.push({
        campo: item.campo,
        etiqueta: item.etiqueta,
        tipo: item.tipo || 'text',
        grupo: item.grupo || 'General',
        servicio_catalogo: item.servicio || '',
        valor_anterior: valorActual(item.campo),
        valor_nuevo: valorNuevo
      });
    }

    btnGuardar.disabled = true;
    const htmlOriginal = btnGuardar.innerHTML;
    btnGuardar.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Guardando...';

    try {
      const response = await fetch('/CapHum/guardarActualizacionInfoPersona', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
          id_persona: idPersona,
          campos,
          observaciones: ''
        })
      });
      const data = await response.json();
      if (!data.success) {
        throw new Error(data.mensaje || 'No se pudo guardar la solicitud.');
      }

      const pushInfo = data.datos && data.datos.push_notificacion ? data.datos.push_notificacion : null;
      actualizacionInfoCache.delete(String(idPersona));
      btnGuardar.disabled = false;
      btnGuardar.innerHTML = htmlOriginal;
      await cerrarModalActualizacionInfo();
      if (pushInfo && !pushInfo.omitida && pushInfo.success === false) {
        await Swal.fire({
          icon: 'warning',
          title: 'Solicitud guardada',
          text: (data.mensaje || 'Solicitud guardada correctamente.') + ' No se pudo enviar la notificación móvil.'
        });
      } else {
        await Swal.fire('Listo', data.mensaje || 'Solicitud guardada correctamente.', 'success');
      }
    } catch (error) {
      Swal.fire('Error', error.message || 'Error al guardar la solicitud.', 'error');
    } finally {
      btnGuardar.disabled = false;
      btnGuardar.innerHTML = htmlOriginal;
    }
  });
})();

</script>
