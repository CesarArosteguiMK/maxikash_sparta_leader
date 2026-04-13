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
    #modalEditPerfil .modal-perfil-modulo-grupo-header .modal-perfil-modulo-master-cb {
        flex-shrink: 0;
        width: 1.1rem;
        height: 1.1rem;
        margin-top: 0.15rem;
        cursor: pointer;
    }
    body.dark-mode .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo-header .modal-perfil-modulo-master-cb {
        filter: brightness(1.15);
    }

    /* Módulos del sistema (modal): máximo 2 bloques por fila; el resto baja a la siguiente fila */
    #modalEditPerfil .modal-perfil-modulos-agrupados {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: stretch;
    }
    /* Exactamente 2 bloques por fila (gap 1rem entre columnas) */
    #modalEditPerfil .modal-perfil-modulos-agrupados .modal-perfil-modulo-grupo {
        flex: 1 1 calc(50% - 0.5rem);
        max-width: calc(50% - 0.5rem);
        min-width: 0;
        margin-bottom: 0 !important;
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

    #offcanvasEditPerfil .tab-content:not(.doc-example-content) {
        padding: .25rem 0;
    }

    /* SweetAlert2 por encima de #modalEditPerfil / #modalKpiDesglose (z-index 99999) y #customModalOverlay (99998).
       La clase .swal-sobre-modal-perfil quedó como alias por si algún Swal usa customClass.container. */
    body .swal2-container,
    .swal2-container.swal-sobre-modal-perfil {
        z-index: 100010 !important;
    }

    /* Estilos para el Modal de Permisos */
    #modalEditPerfil .modal-content {
        border-radius: 1rem;
        overflow: hidden;
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
        content: '▼';
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

    /* Mejorar el botón con indicador */
    .btn-with-indicator {
        position: relative;
        overflow: visible !important;
    }

    /* ==========================================
     * ESTILOS PARA GESTIÓN DE MÚLTIPLES PUESTOS EN MODAL DE EDICIÓN
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
       KPI PANEL — REDISEÑO v3
       Modos: Estándar | Visión (donut) | Mini-stat
       ===================================================== */

    /* ── Toolbar ── */
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

    /* ── Panel colapsable ── */
    .kpi-collapsible { display:grid; grid-template-rows:0fr; transition:grid-template-rows 0.4s cubic-bezier(0.4,0,0.2,1), opacity 0.35s ease; opacity:0; }
    .kpi-collapsible.open { grid-template-rows:1fr; opacity:1; }
    .kpi-collapsible-inner { overflow:hidden; }

    /* ── Fila de 3 celdas ── */
    .kpi-row-new { display:grid; grid-template-columns:repeat(3,1fr); gap:0.85rem; padding-bottom:0.25rem; }

    /* ── Celda base ── */
    .kpi-cell {
        background:#fff; border-radius:14px;
        border:1px solid rgba(99,102,241,0.12);
        box-shadow:0 2px 16px rgba(99,102,241,0.08),0 1px 4px rgba(0,0,0,0.04),
                   inset 4px 0 0 var(--cell-accent);
        position:relative; overflow:hidden; cursor:pointer;
        min-height:190px;
        transition:transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s, border-color 0.25s;
        opacity:0; transform:translateY(14px);
    }
    .kpi-cell.revealed { animation:kpiCellReveal 0.45s cubic-bezier(0.34,1.3,0.64,1) forwards; }
    @keyframes kpiCellReveal {
        from { opacity:0; transform:translateY(14px) scale(0.97); }
        to   { opacity:1; transform:translateY(0) scale(1); }
    }
    .kpi-cell:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(99,102,241,0.13),0 2px 8px rgba(0,0,0,0.06),inset 4px 0 0 var(--cell-accent); border-color:rgba(99,102,241,0.28); }
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

    /* ── Elementos comunes ── */
    .kpi-cell-top { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:0.85rem; }
    /* ── kpi-icon-wrap: círculo coloreado, estilo __SPARTA_SECRET_REDACTED__ ── */
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
    .kpi-row-new.mode-default  .kpi-icon-wrap i { font-size:1.25rem !important; display:inline-block !important; }
    .kpi-row-new.mode-ministat .kpi-icon-wrap i { font-size:1rem   !important; display:inline-block !important; }

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

    /* ═══ KPI células Bajas — paleta roja / naranja / morada ═══ */
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

    /* ── Icono decorativo esquina superior izquierda ── */
    .kpi-corner-icon {
        position:absolute; top:6px; left:8px;
        font-size:4.2rem; line-height:1;
        color:var(--cell-icon); opacity:0.07;
        pointer-events:none; user-select:none; z-index:0;
        transition:opacity 0.4s ease, transform 0.4s cubic-bezier(0.34,1.56,0.64,1);
    }
    .kpi-cell:hover .kpi-corner-icon { opacity:0.13; transform:scale(1.08) rotate(-6deg); }
    .kpi-row-new.mode-vision .kpi-corner-icon { display:none !important; }
    .kpi-cell-status {
        font-size:0.62rem; font-weight:600; letter-spacing:0.06em; text-transform:uppercase;
        color:var(--cell-icon); background:color-mix(in srgb,var(--cell-icon) 10%,transparent);
        border-radius:20px; padding:0.18rem 0.55rem; opacity:0.85;
    }
    .kpi-num { font-size:1.85rem; font-weight:700; line-height:1; color:var(--cell-num); }
    .kpi-lbl { font-size:0.73rem; font-weight:500; color:#6b7280; margin-top:0.3rem; }
    .kpi-bar-track { margin-top:0.9rem; height:3px; background:color-mix(in srgb,var(--cell-icon) 12%,transparent); border-radius:99px; overflow:hidden; }
    .kpi-bar-fill  { height:100%; width:0%; background:var(--cell-icon); border-radius:99px; transition:width 1s cubic-bezier(0.4,0,0.2,1) 0.3s; }

    /* ════ MODO ESTÁNDAR ════ */
    .kpi-row-new.mode-default .kpi-cell { padding:1.15rem 1.25rem 1.05rem; }
    /* Ocultar icono y círculo en modo Estándar */
    .kpi-row-new.mode-default .kpi-icon-wrap { display:none !important; }
    .kpi-row-new.mode-default .kpi-corner-icon { display:none !important; }
    .kpi-row-new.mode-default .donut-block  { display:none !important; }
    .kpi-row-new.mode-default .kpi-stats-grid-new { display:none !important; }

    /* ════ MODO MINI-STAT ════ */
    .kpi-row-new.mode-ministat .kpi-cell { padding:1.15rem 1.25rem 1.05rem; }
    .kpi-row-new.mode-ministat .kpi-cell-top { margin-bottom:0.85rem; }
    .kpi-row-new.mode-ministat .kpi-cell-top .kpi-cell-status { display:none; }
    /* Ocultar icono y círculo en modo Mini-Stat */
    .kpi-row-new.mode-ministat .kpi-icon-wrap { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-corner-icon { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-cell-title { font-size:0.7rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.03em; align-self:center; display:block; margin-bottom:0.2rem; }
    .kpi-row-new.mode-ministat .kpi-num      { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-lbl      { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-bar-track{ display:block !important; margin-top:auto; padding-top:0.55rem; }
    .kpi-row-new.mode-ministat .donut-block  { display:none !important; }
    .kpi-row-new.mode-ministat .kpi-stats-grid-new { display:grid !important; }
    /* % badge below B-value */
    .kpi-ms-pct { font-size:0.62rem; font-weight:600; color:#9ca3af; margin-top:0.18rem; line-height:1.2; }
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
    .kpi-stats-emp { display:none; align-items:center; gap:0.5rem; }
    .kpi-row-new.mode-ministat .kpi-stats-emp { display:flex !important; }
    .kpi-emp-total-wrap { display:flex; flex-direction:column; justify-content:center; flex:0 0 auto; }
    .kpi-emp-total-val { font-size:2rem; font-weight:700; color:var(--cell-num); line-height:1; }
    .kpi-emp-total-lbl { font-size:0.6rem; font-weight:600; color:#6b7280; text-transform:uppercase; letter-spacing:0.04em; }
    .kpi-emp-div { width:1px; height:40px; background:linear-gradient(180deg,transparent,rgba(99,102,241,0.18),transparent); flex-shrink:0; }
    .kpi-emp-side { display:flex; flex-direction:column; gap:0.3rem; flex:1; padding-left:0.3rem; }
    .kpi-emp-row { display:flex; align-items:center; gap:0.25rem; }
    .kpi-emp-arrow-up   { color:#10b981; font-size:0.95rem; line-height:1; display:flex; }
    .kpi-emp-arrow-down { color:#ef4444; font-size:0.95rem; line-height:1; display:flex; }
    .kpi-emp-num { font-size:1rem; font-weight:700; color:var(--cell-num); line-height:1; min-width:1.5ch; }
    .kpi-emp-side-lbl { font-size:0.6rem; color:#6b7280; font-weight:500; }
    .kpi-stat-ingr { color:#10b981 !important; }
    .kpi-ingr-arrow { color:#10b981; font-size:0.75rem; vertical-align:middle; display:inline-flex; align-items:center; }
    .kpi-stat-val { font-size:1.85rem; font-weight:700; color:var(--cell-num); line-height:1; display:flex; align-items:center; gap:0.1rem; }
    .kpi-stat-lbl { font-size:0.62rem; font-weight:500; color:#6b7280; margin-top:0.2rem; }
    .kpi-stat-div { width:1px; height:40px; align-self:center; background:linear-gradient(180deg,transparent,rgba(99,102,241,0.15),transparent); }
    .kpi-stats-grid-new { display:none; grid-template-columns:1fr 1px 1fr; align-items:center; margin-top:0.65rem; }
    .kpi-arrow-up   { color:#10b981; font-size:1.1rem; line-height:1; display:flex; align-items:center; }
    .kpi-arrow-down { color:#ef4444; font-size:1.1rem; line-height:1; display:flex; align-items:center; }

    /* ════ MODO VISIÓN (donut) ════ */
    .kpi-row-new.mode-vision .kpi-cell { padding:1.1rem 1.25rem 1rem; min-height:unset; }
    .kpi-row-new.mode-vision .kpi-cell-top    { display:none !important; }
    .kpi-row-new.mode-vision .kpi-num         { display:none !important; }
    .kpi-row-new.mode-vision .kpi-lbl         { display:none !important; }
    .kpi-row-new.mode-vision .kpi-bar-track   { display:none !important; }
    .kpi-row-new.mode-vision .kpi-stats-grid-new { display:none !important; }
    .kpi-row-new.mode-vision .donut-block { display:flex !important; flex-direction:column; align-items:center; gap:0.65rem; }
    .donut-block { display:none; }

    .donut-header { width:100%; display:flex; align-items:center; justify-content:space-between; }
    .donut-title  { font-size:0.7rem; font-weight:600; text-transform:uppercase; letter-spacing:0.07em; color:#6b7280; }
    .donut-svg-wrap {
        position:relative; display:inline-flex; align-items:center; justify-content:center;
        width:96px; height:96px;
    }
    .donut-svg { width:96px; height:96px; transform:rotate(-90deg); overflow:visible; }
    .donut-track { fill:none; stroke:color-mix(in srgb,var(--cell-icon) 12%,transparent); stroke-width:8; stroke-linecap:round; }
    .donut-arc {
        fill:none; stroke:var(--cell-icon); stroke-width:8; stroke-linecap:round;
        stroke-dasharray:0 226.2;
        transition:stroke-dasharray 1.1s cubic-bezier(0.4,0,0.2,1);
        filter:drop-shadow(0 0 4px color-mix(in srgb,var(--cell-icon) 40%,transparent));
    }
    .kpi-cell:hover .donut-arc {
        filter:drop-shadow(0 0 8px color-mix(in srgb,var(--cell-icon) 60%,transparent));
        stroke-width:9;
    }
    .donut-center-icon {
        position:absolute; inset:0; display:flex; align-items:center; justify-content:center;
        font-size:1.35rem; color:var(--cell-icon);
        transition:transform 0.3s cubic-bezier(0.34,1.56,0.64,1);
    }
    .kpi-cell:hover .donut-center-icon { transform:scale(1.18); }
    .donut-footer { display:flex; align-items:center; justify-content:center; gap:0.35rem; width:100%; }
    .donut-stats { display:grid; grid-template-columns:1fr 1px 1fr; align-items:center; width:100%; margin-top:0.35rem; padding-top:0.35rem; border-top:1px solid color-mix(in srgb,var(--cell-icon) 12%,transparent); }
    .donut-trend {
        display:inline-flex; align-items:center; gap:0.2rem;
        font-size:0.7rem; font-weight:700; padding:0.2rem 0.5rem; border-radius:5px;
    }
    .donut-trend.up     { color:#059669; background:rgba(16,185,129,0.1); }
    .donut-trend.down   { color:#dc2626; background:rgba(239,68,68,0.1); }
    .donut-trend.steady { color:var(--cell-icon); background:color-mix(in srgb,var(--cell-icon) 10%,transparent); }
    .donut-trend-label  { font-size:0.68rem; color:#6b7280; font-weight:500; }

    /* ── Tooltip del donut ── */
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

    /* ── Swap animation ── */
    @keyframes kpiModeFade { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }
    .kpi-cell.mode-swap { animation:kpiModeFade 0.32s ease forwards; }

    /* ── Dark mode ── */
    body.dark-mode .kpi-cell             { background:#1a1d2e; border-color:rgba(99,102,241,0.18); box-shadow:0 2px 16px rgba(0,0,0,0.35),0 1px 4px rgba(0,0,0,0.2),inset 4px 0 0 var(--cell-accent); }
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

    /* ── Responsive móvil ── */
    @media (max-width: 767px) {
        .kpi-row-new { grid-template-columns:1fr 1fr; }
        .kpi-toolbar { gap:0.4rem; }
        .kpi-view-btn { padding:0 0.3rem; font-size:0.65rem; }
        .kpi-view-btn .kpi-btn-text { display:none; } /* Ocultar texto en móvil, solo íconos */
        .kpi-num { font-size:1.5rem; }
    }
    @media (max-width: 480px) {
        .kpi-row-new { grid-template-columns:1fr; }
    }

    /* ── Conservar estilos de filtros rápidos ── */
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
   Optimizado para smartphones: 360x780 → 393x852
   ============================================================ */

/* ─────────────────────────────────────────────
   BASE MÓVIL: aplica desde 0 hasta 575px
   ───────────────────────────────────────────── */
@media (max-width: 575.98px) {

  /* ── 1. DIÁLOGO: ocupa toda la pantalla como sheet nativo ── */
  #modalEditPerfil .modal-dialog {
    margin: 0 !important;
    max-width: 100% !important;
    width: 100% !important;
    height: 100% !important;                  /* full height */
    min-height: 100% !important;
    display: flex !important;
    align-items: flex-end !important;         /* sube desde abajo (bottom-sheet) */
  }

  /* ── 2. CONTENT: ocupa todo el ancho y altura máxima ── */
  #modalEditPerfil .modal-content {
    border-radius: 1.25rem 1.25rem 0 0 !important;   /* esquinas solo arriba */
    width: 100% !important;
    max-height: 92vh !important;              /* deja espacio para ver contexto */
    height: auto !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
  }

  /* ── 3. HEADER: compacto, sin desbordamiento ── */
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

  /* ── 4. BODY: scroll interno, sin overflow roto ── */
  #modalEditPerfil .modal-body {
    overflow-y: auto !important;
    overflow-x: hidden !important;
    flex: 1 1 auto !important;
    padding: 0 !important;
    -webkit-overflow-scrolling: touch !important;  /* scroll suave iOS */
  }

  /* ── 5. TABS: scroll horizontal si no caben, nunca wrap ── */
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

  /* ── 6. TAB CONTENT: padding cómodo para dedos ── */
  #modalEditPerfil .tab-content {
    padding: 1rem !important;
  }

  /* ── 7. HEADERS DE SECCIÓN dentro de tabs ── */
  #modalEditPerfil .tab-pane h6 {
    font-size: 0.85rem !important;
    margin-bottom: 0.4rem !important;
  }

  #modalEditPerfil .tab-pane small {
    font-size: 0.7rem !important;
  }

  /* ── 8. PUESTOS CONTAINER: altura adaptada a móvil ── */
  #modalEditPerfil #puestos-container {
    max-height: 55vh !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
  }

  /* ── 9. MÓDULOS CONTAINER ── */
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

  /* ── 10. PERMISOS ESPECIALES CONTAINER ── */
  #modalEditPerfil #permisos-especiales-container {
    max-height: 55vh !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
    margin-left: 0 !important;               /* quitar indent de desktop */
    padding-left: 0 !important;
    border-left: none !important;
  }

  /* ── 11. BOTÓN EXPANDIR DEPARTAMENTOS ── */
  #modalEditPerfil #tabPuestos .d-flex.justify-content-between {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 0.5rem !important;
  }

  #modalEditPerfil #tabPuestos button[onclick="expandirTodosPuestos()"] {
    width: 100% !important;
    justify-content: center !important;
    font-size: 0.8rem !important;
    padding: 0.5rem 0.75rem !important;
  }

  /* ── 12. TABLAS dentro de módulos/permisos ── */
  #modalEditPerfil .table-hover tbody tr:hover {
    transform: none !important;              /* deshabilitar translateX en touch */
  }

  /* ── 13. ACCORDION dentro de módulos ── */
  #modalEditPerfil .accordion-button {
    font-size: 0.82rem !important;
    padding: 0.65rem 0.75rem !important;
  }

  #modalEditPerfil .accordion-body {
    padding: 0.5rem 0.75rem !important;
  }

  /* ── 14. INDICADOR "pill" drag para bottom-sheet ── */
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

/* ─────────────────────────────────────────────
   AJUSTE FINO para los más pequeños (360px)
   ───────────────────────────────────────────── */
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

/* ─────────────────────────────────────────────
   ANIMACIÓN DE ENTRADA: slide-up desde abajo
   (reemplaza el fade genérico de Bootstrap)
   ───────────────────────────────────────────── */
@media (max-width: 575.98px) {

  #modalEditPerfil.modal .modal-dialog {
    transition: transform 0.35s cubic-bezier(0.32, 0.72, 0, 1) !important;
    transform: translateY(100%) !important;
  }

  #modalEditPerfil.modal.show .modal-dialog {
    transform: translateY(0) !important;
  }
}

/* ─────────────────────────────────────────────
   Modal permisos/puestos: sin backdrop de Bootstrap; overlay propio suave; encima de todo
   ───────────────────────────────────────────── */
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

/* ─────────────────────────────────────────────
   Modal Gestión: verse completo en pantalla (desktop y móvil)
   ───────────────────────────────────────────── */
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

/* ─────────────────────────────────────────────
   Modal KPI Desglose (Departamentos / Puestos / Total Empleados)
   Template: Liquid Glass — consistente con #modalReingreso
   ───────────────────────────────────────────── */
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
window.todosDepartamentosBackend = <?= json_encode(($departamento['datos'] ?? [])) ?>;
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
                    <select id="UserRole" class="form-select text-capitalize">
                        <option value="">Selecciona Departamento</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="UserPlan" class="form-select text-capitalize">
                        <option value="">Selecciona Puesto</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="FilterTransaction" class="form-select text-capitalize">
                        <option value="">Selecciona Estatus</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select id="FilterMultiplePuestos" class="form-select text-capitalize">
                        <option value="">Todos los usuarios</option>
                        <option value="multiples">Múltiples puestos</option>
                        <option value="unico">Un solo puesto</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- =======================
             PANEL DE INDICADORES (GESTIÓN) — REDISEÑO v3
        ======================== -->

        <!-- Tooltip global para donuts -->
        <div class="kpi-donut-tooltip" id="kpiDonutTooltip"></div>

        <div id="panelIndicadoresGestion" class="px-4 pt-3 pb-2">

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

                        <!-- ░░ DEPARTAMENTOS ░░ -->
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

                        <!-- ░░ PUESTOS ░░ -->
                        <div class="kpi-cell tipo-puesto" id="kpi-cell-puesto" data-tipo="puestos">
                            <span class="kpi-corner-icon"><i class="bx bx-briefcase"></i></span>
                            <div class="kpi-cell-top">
                                <div class="kpi-icon-wrap"><i class="bx bx-briefcase"></i></div>
                                <span class="kpi-cell-status">Únicos</span>
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
                                    <span class="kpi-cell-status">Únicos</span>
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

                        <!-- ░░ TOTAL EMPLEADOS ░░ -->
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
        <p class="text-muted text-center py-4"><i class="bx bx-loader-alt bx-spin me-2"></i>Cargando…</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>



        <!-- =======================
             BOTONES DE ACCIÓN
        ======================== -->
        <div class="row justify-content-between m-4">
            <div class="col-8"></div>

            <div class="col-4 d-flex align-items-end justify-content-end gap-2">
                <!-- Botón Descargar Plantilla -->
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

                <!-- Botón Agregar Usuario -->
                <button
                  type="button"
                  class="btn btn-primary add-new btn-action-size"
                  data-bs-toggle="offcanvas"
                  data-bs-target="#offcanvasAddUser"
                >
                    <i class="fa fa-user-plus icon-sm me-sm-2"></i>
                    <span class="d-inline-block">Agregar Usuario</span>
                </button>
            </div>
        </div>

        <!-- =======================
             FILTRO DE FECHAS PARA BAJAS (oculto por defecto)
        ======================== -->
        <div id="filtroFechaBajas" style="display: none;" class="row m-4">
            <div class="col-12">
                <div class="card border">
                    <div class="card-body">
                        <!-- Selector de Fechas Manual -->
                        <div class="row align-items-end g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">
                                    <i class="fa fa-calendar-alt me-2"></i>Rango de Fechas Personalizado
                                </label>
                                <input
                                    type="text"
                                    id="flatpickr-range-bajas"
                                    class="form-control"
                                    placeholder="Selecciona un rango de fechas personalizado"
                                />
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
                                        <i class="fa fa-calendar-day me-1"></i>Último Mes
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-filtro-rapido"
                                        data-periodo="ultimos-3-meses"
                                    >
                                        <i class="fa fa-calendar-week me-1"></i>Últimos 3 Meses
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-filtro-rapido"
                                        data-periodo="ultimos-6-meses"
                                    >
                                        <i class="fa fa-calendar me-1"></i>Últimos 6 Meses
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

                                            <!-- ░░ TOTAL BAJAS ░░ -->
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
                                                <div class="donut-block">
                                                    <div class="donut-header">
                                                        <span class="donut-title">Total Bajas</span>
                                                        <span class="kpi-cell-status">Total</span>
                                                    </div>
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
                                                    <div class="donut-footer">
                                                        <span class="donut-trend down" id="kpi-trend-b-total"><i class="bx bx-trending-down"></i>—</span>
                                                        <span class="donut-trend-label" id="kpi-trend-b-total-lbl">este mes</span>
                                                    </div>
                                                    <div class="donut-stats">
                                                        <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-b-total-a">0</div><div class="kpi-stat-lbl">Total</div></div>
                                                        <div class="kpi-stat-div"></div>
                                                        <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-b-total-b">0</div><div class="kpi-stat-lbl" id="kpi-dv-b-total-lbl">Este mes</div></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ░░ DEPARTAMENTOS AFECTADOS ░░ -->
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
                                                <div class="donut-block">
                                                    <div class="donut-header">
                                                        <span class="donut-title">Departamentos</span>
                                                        <span class="kpi-cell-status">Afectados</span>
                                                    </div>
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
                                                    <div class="donut-footer">
                                                        <span class="donut-trend down" id="kpi-trend-b-dep"><i class="bx bx-buildings"></i>—</span>
                                                        <span class="donut-trend-label">con más bajas</span>
                                                    </div>
                                                    <div class="donut-stats">
                                                        <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-b-dep-a">0</div><div class="kpi-stat-lbl">Afectados</div></div>
                                                        <div class="kpi-stat-div"></div>
                                                        <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-b-dep-b">0</div><div class="kpi-stat-lbl">Top bajas</div></div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- ░░ PUESTOS AFECTADOS ░░ -->
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
                                                <div class="donut-block">
                                                    <div class="donut-header">
                                                        <span class="donut-title">Puestos</span>
                                                        <span class="kpi-cell-status">Afectados</span>
                                                    </div>
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
                                                    <div class="donut-footer">
                                                        <span class="donut-trend down" id="kpi-trend-b-puesto"><i class="bx bx-briefcase"></i>—</span>
                                                        <span class="donut-trend-label">con más bajas</span>
                                                    </div>
                                                    <div class="donut-stats">
                                                        <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-b-puesto-a">0</div><div class="kpi-stat-lbl">Afectados</div></div>
                                                        <div class="kpi-stat-div"></div>
                                                        <div class="kpi-stat-item"><div class="kpi-stat-val" id="kpi-dv-b-puesto-b">0</div><div class="kpi-stat-lbl">Top bajas</div></div>
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
                    <th>Estatus</th>
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
    <div class="offcanvas offcanvas-end" id="offcanvasAddUser">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title">Registrar Nuevo Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-6">
            <form id="addNewUserForm" onsubmit="return false">

                <div class="col-md-5 d-none">
                    <div class="mb-2">
                        <label class="form-label">Número de Empleado *</label>
                        <input type="text" id="add_num_telefono" class="form-control phone-mask">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" id="add_nombres" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Segundo Nombre (Opcional)</label>
                    <input type="text" id="add_segundo_nombre" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="add_apellidop" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="add_apellidom" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="add_telefono" class="form-control phone-mask" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onblur="validarTelefono('add_telefono')" maxlength="10">
                </div>

                <div class="mb-2">
                    <label class="form-label">País (Sede) *</label>
                    <select id="add_id_pais" class="form-select">
                        <option value="">Seleccione un país</option>
                        <?php foreach (($paisesActivos ?? []) as $pais): ?>
                            <option value="<?= htmlspecialchars($pais['id']) ?>" data-iso="<?= htmlspecialchars($pais['codigo_iso']) ?>">
                                <?= htmlspecialchars($pais['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2" id="div_add_estado" style="display:none;">
    <label class="form-label" id="label_add_estado">Estado *</label>
    <select id="add_id_div_nivel1" class="form-select" disabled>
        <option value="">Seleccione un estado</option>
    </select>
</div>

<div class="mb-2" id="div_add_municipio" style="display:none;">
    <label class="form-label" id="label_add_municipio">Municipio *</label>
    <select id="add_id_div_nivel2" class="form-select" disabled>
        <option value="">Seleccione un municipio</option>
    </select>
</div>

                <div class="mb-2">
                    <label class="form-label">Departamento *</label>
                    <select id="add_departamento_id" class="form-select">
                        <option value="">Seleccione un departamento</option>
                        <?php foreach ($departamento['datos'] as $dep): ?>
                            <option value="<?= htmlspecialchars($dep['id']) ?>">
                                <?= htmlspecialchars($dep['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Puesto *</label>
                    <select id="add_id_puesto" class="form-select" disabled>
                        <option value="">Seleccione un puesto</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="form-label">Jefe *</label>
                    <select id="add_id_jefe" class="form-select" disabled>
                        <option value="">Seleccione un jefe</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Fecha de ingreso <span class="text-danger">*</span></label>
                    <div class="fecha-acta-wrapper" id="fecha_acta_wrapper">
                        <input type="text" id="add_fecha_ingreso" class="form-control" placeholder="YYYY-MM-DD">
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="add_asignar_legion" onchange="toggleSelectLegion()">
                        <label class="form-check-label" for="add_asignar_legion">
                            Asignar legión
                        </label>
                    </div>
                </div>

                <div class="mb-2" id="div_select_legion" style="display: none;">
                    <label class="form-label">Legión *</label>
                    <select id="add_id_legion" class="form-select">
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
                    <input type="text" id="add_usuario" class="form-control" maxlength="10" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-7">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="add_contrasena" class="form-control" maxlength="15" oninput="this.value = this.value.replace(/^\s+/, '').replace(/\s{2,}/g, ' ')" onblur="this.value = this.value.trim()">
                </div>

                <button class="btn btn-primary me-3" onclick="guardarGestor()">Guardar</button>
                <button class="btn btn-label-danger" data-bs-dismiss="offcanvas">Cancelar</button>

            </form>
        </div>
    </div>

    <!-- =======================
           OFFCANVAS -
      ======================== -->
    <!-- Modal RFC -->
    <div class="modal fade" id="modalBajas" tabindex="-1" aria-labelledby="modalRFCLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
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

                    <!-- 🆕 Motivo de baja -->
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

                    <!-- 🆕 Botón para confirmar baja -->
                    <button type="button" class="btn btn-danger" onclick="confirmarBaja()">
                        Confirmar Baja
                    </button>
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
                        <select class="form-select" id="motivoReingreso">
                            <option value="">-- Selecciona un motivo --</option>
                            <option value="ILOCALIZABLE">ILOCALIZABLE</option>
                            <option value="LOCALIZABLE EN DOMICILIO PRINCIPAL">LOCALIZABLE EN DOMICILIO PRINCIPAL</option>
                            <option value="LOCALIZABLE EN DOMICILIO ALTERNO (FIRMA, BURO Y FRACTURA)">LOCALIZABLE EN DOMICILIO ALTERNO (FIRMA, BURO Y FRACTURA)</option>
                            <option value="LOCALIZABLE EN DOMICILIO DE RIESGO">LOCALIZABLE EN DOMICILIO DE RIESGO</option>
                            <option value="GESTIÓN INCOMPLETA">GESTIÓN INCOMPLETA</option>
                            <option value="DUAL || ZONIFICACIÓN">DUAL || ZONIFICACIÓN</option>
                            <option value="FALTA VISITAR DOMICILIOS ALTERNOS">FALTA VISITAR DOMICILIOS ALTERNOS</option>
                            <option value="FALTA INTENSIDAD DE GESTIÓN">FALTA INTENSIDAD DE GESTIÓN</option>
                            <option value="GESTIONADA VÍA TELEFÓNICA">GESTIONADA VÍA TELEFÓNICA</option>
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
         MODAL - CARGAR DOCUMENTO PERSONA (GESTIÓN)
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
                            <option value="Acta de Nacimiento">Acta de Nacimiento</option>
                            <option value="Certificado de Estudios">Certificado de Estudios</option>
                            <option value="Comprobante de Domicilio">Comprobante de Domicilio</option>
                            <option value="CURP">CURP</option>
                            <option value="Documento baja">Documento baja</option>
                            <option value="Documento reingreso">Documento reingreso</option>
                            <option value="Identificación Oficial (INE)">Identificación Oficial (INE)</option>
                            <option value="Referencias Laborales">Referencias Laborales</option>
                            <option value="RFC">RFC</option>
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
                                accept=".pdf"
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
                        <small class="text-muted">Puedes subir múltiples archivos PDF.</small>
                    </div>

                    <!-- Lista de archivos nuevos seleccionados (antes de subir) - ARRIBA DE LA TABLA -->
                    <div id="cargarDocPersona_listaArchivos" class="mb-4" style="display: none;">
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
                                        <th>Contexto</th>
                                        <th>Archivo</th>
                                        <th>Fecha de carga</th>
                                        <th>Válido</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cargarDocPersona_tablaArchivos">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No hay archivos subidos</td>
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
                            <label class="form-label"><strong>Fecha y hora inicio</strong></label>
                            <input type="text" class="form-control flatpickr-datetime-ausencia" id="fechaInicio" placeholder="Seleccione fecha y hora" autocomplete="off" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Fecha y hora fin</strong></label>
                            <input type="text" class="form-control flatpickr-datetime-ausencia" id="fechaFin" placeholder="Seleccione fecha y hora" autocomplete="off" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label"><strong>Descripción</strong></label>
                            <textarea class="form-control" id="descripcionAusencia" rows="2"></textarea>
                        </div>

                    </div>

                    <div class="mt-3">
                        <button class="btn btn-primary"  id="btnGuardarAusencia" onclick="guardarAusencia()">
                            Registrar ausencia
                        </button>
                    </div>

                    <hr>

                    <!-- ================== TABLA DE AUSENCIAS ================== -->
                    <h6 class="mb-2"><strong>Historial de ausencias</strong></h6>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead class="table-dark">
                            <tr>
                                <th>Razón</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Descripción</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody id="tablaAusencias">
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Sin registros
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>
    </div>

    <!-- =======================
        OFFCANVAS - EDITAR
   ======================== -->
    <div class="offcanvas offcanvas-end" id="offcanvasEditUser">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvasEditUserTitle">Editar Gestor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-6">
            <form id="editNewUserForm" onsubmit="return false">

                <div class="mb-2" style="display: none">
                    <label class="form-label">Id Empleado *</label>
                    <input ype="text" id="edit_id" class="form-control phone-mask"disabled>
                </div>

                <div class="mb-2">
                    <label class="form-label">Número de Empleado *</label>
                    <input ype="text" id="edit_num_empleado" class="form-control phone-mask"disabled>
                </div>

                <div class="mb-2">
                    <label class="form-label">Nombre *</label>
                    <input type="text" id="edit_nombres" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Segundo Nombre (Opcional)</label>
                    <input type="text" id="edit_segundo_nombre" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Paterno *</label>
                    <input type="text" id="edit_apellidop" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Apellido Materno *</label>
                    <input type="text" id="edit_apellidom" class="form-control" oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ').toUpperCase()" onblur="this.value = this.value.trim()" style="text-transform: uppercase;">
                </div>

                <div class="mb-2">
                    <label class="form-label">Teléfono *</label>
                    <input type="text" id="edit_telefono" class="form-control phone-mask" oninput="this.value = this.value.replace(/[^0-9]/g, '')" onblur="validarTelefono('edit_telefono')" maxlength="10">
                </div>

                <div class="mb-2" id="div_edit_estado" style="display:none;">
    <label class="form-label" id="label_edit_estado">Estado</label>
    <select id="edit_id_div_nivel1" class="form-select">
        <option value="">Seleccione un estado</option>
    </select>
</div>

<div class="mb-2" id="div_edit_municipio" style="display:none;">
    <label class="form-label" id="label_edit_municipio">Municipio</label>
    <select id="edit_id_div_nivel2" class="form-select">
        <option value="">Seleccione un municipio</option>
    </select>
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

                <!-- Contenedor de puestos múltiples - MEJORADO CON GESTIÓN COMPLETA -->
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
                            <button onclick="eliminarPuesto(X)">×</button>
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
                                <select id="edit_nuevo_departamento" class="form-select form-select-sm" onchange="cargarPuestosParaAgregar()">
                                    <option value="">Seleccione un departamento</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Puesto</label>
                                <select id="edit_nuevo_puesto" class="form-select form-select-sm">
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
                                <select id="edit_editar_departamento" class="form-select form-select-sm" onchange="cargarPuestosParaEditar()">
                                    <option value="">Seleccione un departamento</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Puesto</label>
                                <select id="edit_editar_puesto" class="form-select form-select-sm">
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
                    <select id="edit_departamento_id" class="form-select">
                        <option value="">Seleccione un departamento</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Puesto <span id="edit_label_puesto_principal" class="badge bg-primary" style="display: none;">Principal</span></label>
                    <select id="edit_id_puesto" class="form-select">
                        <option value="">Seleccione un puesto</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="form-label">Jefe *</label>
                    <select id="edit_id_jefe" class="form-select">
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
                    <select id="edit_id_legion" class="form-select">
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
                    <input type="text" id="edit_usuario" class="form-control" readonly oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()" style="text-transform: uppercase;">
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
      MODAL - GESTIÓN DE PERMISOS Y PUESTOS
 ======================== -->
    <div class="modal fade" id="modalEditPerfil" tabindex="-1" aria-labelledby="modalEditPerfilLabel" aria-hidden="true" data-bs-backdrop="false" data-bs-keyboard="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content" style="border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.12);">
                <div class="modal-header" style="background: #f8f9fa; border-bottom: 2px solid #e9ecef; padding: 1.5rem;">
                    <div class="d-flex align-items-center w-100">
                        <div class="flex-grow-1">
                            <h5 class="modal-title fw-bold mb-1" id="modalEditPerfilLabel" style="color: #2c3e50;">
                                <i class="fa fa-user-shield me-2" style="color: #495057;"></i>Gestión de Permisos y Accesos
                            </h5>
                            <p class="text-muted mb-0 small" id="modalEditPerfil_subtitle">Administrar puestos y módulos del usuario</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle p-2" data-bs-dismiss="modal" aria-label="Cerrar" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 2px solid #6c757d; transition: all 0.3s ease; box-shadow: 0 2px 4px rgba(108, 117, 125, 0.15);" onmouseover="this.style.backgroundColor='#6c757d'; this.querySelector('i').style.color='#fff';" onmouseout="this.style.backgroundColor='transparent'; this.querySelector('i').style.color='#6c757d';">
                            <i class="fa fa-times" style="font-size: 1.1rem; color: #6c757d; transition: color 0.3s ease;"></i>
                        </button>
                    </div>
                </div>

                <div class="modal-body p-0">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom px-4 pt-3" role="tablist" style="border-bottom: 2px solid #e9ecef;">
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

                    <div class="tab-content p-4">
                        <!-- TAB MÓDULOS -->
                        <div class="tab-pane fade show active" id="tabModulos" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-1 fw-bold">Módulos del Sistema</h6>
                                    <small class="text-muted">Gestiona los accesos a los diferentes módulos</small>
                                </div>
                            </div>

                            <div id="modulos-container" style="overflow-y: visible;">
                                <div id="modal-edit-perfil-modulos-form"></div>
                            </div>
                        </div>

                        <!-- TAB PUESTOS -->
                        <div class="tab-pane fade" id="tabPuestos" role="tabpanel">
                            <input type="hidden" id="edit_perfil_id">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-1 fw-bold">Puestos Disponibles</h6>
                                    <small class="text-muted">Selecciona los puestos a los que tendrá acceso este usuario</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="expandirTodosPuestos()">
                                    <i class="fa fa-expand me-1"></i>Expandir Departamentos
                                </button>
                            </div>

                            <div id="puestos-container" style="max-height: 500px; overflow-y: auto;">
                                <div id="modal-edit-perfil-puestos-form"></div>
                            </div>
                        </div>

                        <!-- TAB PERMISOS ESPECIALES -->
                        <div class="tab-pane fade" id="tabPermisosEspeciales" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h6 class="mb-1 fw-bold">Permisos especiales</h6>
                                    <small class="text-muted">Asigna permisos adicionales para acciones específicas</small>
                                </div>
                            </div>
                            <div id="permisos-especiales-container" class="modal-perfil-permisos-outer">
                                <div id="modal-edit-perfil-permisos-especiales-form"></div>
                            </div>

                            <!-- ── Herramienta admin: Eliminar convenios del crédito (temporalmente oculta) ──
                            <div class="mt-4">
                                <div class="card border shadow-sm">
                                    <div class="card-header py-2 px-3 d-flex align-items-center gap-2" style="background:#f8f9fa;">
                                        <i class="fa fa-trash-alt text-danger"></i>
                                        <span class="fw-semibold small">Eliminar convenios del crédito</span>
                                    </div>
                                    <div class="card-body p-3">
                                        <p class="text-muted small mb-3">Elimina <strong>todos los convenios y su amortización</strong> para un crédito. Úsalo cuando el crédito tenga convenios cancelados que bloquean la generación de uno nuevo.</p>
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

                        <!-- TAB SESIÓN REMOTA (force_logout en persona) -->
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
   * FUNCIÓN VALIDAR TELÉFONO
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
    const departamentoSeleccionado = document.getElementById('UserRole').value;
    const puestoSeleccionado = document.getElementById('UserPlan').value;

    // ==========================================
    // LÓGICA CONDICIONAL SEGÚN FILTROS
    // ==========================================

    // CASO 1: Sin filtros seleccionados
    if (!departamentoSeleccionado && !puestoSeleccionado) {
      // Mostrar: Departamentos, Puestos, Total de Empleados (global)
      ocultarIndicadoresRoles();
      ocultarIndicadorEmpleados();
      mostrarIndicadorTotalEmpleados(datos.length, 'Total Empleados');
    }
    // CASO 2: Solo departamento seleccionado (sin puesto)
    else if (departamentoSeleccionado && !puestoSeleccionado) {
      // Mostrar: Departamentos, Puestos, Roles Dinámicos, Total Empleados del Departamento
      actualizarIndicadoresRoles(datos, departamentoSeleccionado);
      ocultarIndicadorEmpleados();

      const empleadosDepartamento = datos.filter(p => p.nombre_departamento === departamentoSeleccionado).length;
      mostrarIndicadorTotalEmpleados(empleadosDepartamento, 'Empleados en ' + departamentoSeleccionado);
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

    // INTEGRACIÓN CON SISTEMA KPI v3: Actualizar el nuevo panel con todos los datos
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

      // INTEGRACIÓN CON SISTEMA KPI v3
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

      // INTEGRACIÓN CON SISTEMA KPI v3
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

    // INTEGRACIÓN CON SISTEMA KPI v3: Limpiar valores
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

    // INTEGRACIÓN CON SISTEMA KPI v3: Limpiar valores
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

      // INTEGRACIÓN CON SISTEMA KPI v3: Actualizar también el nuevo panel
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

    // INTEGRACIÓN CON SISTEMA KPI v3: Limpiar valores
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
   * ANIMAR NÚMEROS DE KPIs
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
          <div class="kpi-tooltip-title">📊 Departamentos</div>
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
          <div class="kpi-tooltip-title">💼 Puestos</div>
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
          <div class="kpi-tooltip-title">👥 Total Empleados</div>
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
          <div class="kpi-tooltip-title">📈 Estadísticas</div>
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

    // ── DEPARTAMENTOS AFECTADOS ──
    const depConteo = {};
    datosBajas.forEach(baja => {
      const d = baja.departamento;
      if (d && d !== 'N/A' && d !== 'Sin departamento') depConteo[d] = (depConteo[d] || 0) + 1;
    });
    const depAfectados   = Object.keys(depConteo).length;
    const topDepEntry    = Object.entries(depConteo).sort((a, b) => b[1] - a[1])[0] || ['—', 0];
    const topDepNombre   = topDepEntry[0];
    const topDepBajas    = topDepEntry[1];

    // ── PUESTOS AFECTADOS ──
    const puestoConteo = {};
    datosBajas.forEach(baja => {
      const p = baja.nombre_puesto;
      if (p && p !== 'N/A' && p !== 'Sin puesto') puestoConteo[p] = (puestoConteo[p] || 0) + 1;
    });
    const puestosAfectados = Object.keys(puestoConteo).length;
    const topPuestoEntry   = Object.entries(puestoConteo).sort((a, b) => b[1] - a[1])[0] || ['—', 0];
    const topPuestoNombre  = topPuestoEntry[0];
    const topPuestoBajas   = topPuestoEntry[1];

    // ── BAJAS DEL MES / PERÍODO ──
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

    // ── PORCENTAJES PARA BARRAS / DONUTS ──
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
  // la lógica de período está integrada en actualizarIndicadoresBajas → kpiUpdateValuesBajas.
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

    return { titulo: '🏢 Bajas Departamentales - Análisis Completo', html };
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

    return { titulo: '💼 Bajas por Puesto - Análisis Completo', html };
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
   * UserRole = Departamento
   * UserPlan = Puesto
   * FilterTransaction = Estatus
   *
   * Datos provenientes de: /CapHum/getUsuarios
   */

  // Variable global para almacenar todos los usuarios
  let usuariosData = [];

  /**
   * ==========================================
   * CONSOLIDAR USUARIOS CON MÚLTIPLES PUESTOS
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
          puestos: [{
            id_puesto: usuario.id_puesto,
            nombre_puesto: usuario.nombre_puesto,
            nombre_departamento: usuario.nombre_departamento,
            id_departamento: usuario.id_departamento
          }],
          // Guardar el nombre del puesto y departamento originales para compatibilidad
          nombre_puesto_principal: usuario.nombre_puesto,
          nombre_departamento_principal: usuario.nombre_departamento
        });
      } else {
        // Ya existe, agregar nuevo puesto si no está duplicado
        const usuarioExistente = usuariosMap.get(id);
        const puestoExiste = usuarioExistente.puestos.some(p =>
          p.id_puesto === usuario.id_puesto &&
          p.nombre_departamento === usuario.nombre_departamento
        );

        if (!puestoExiste) {
          usuarioExistente.puestos.push({
            id_puesto: usuario.id_puesto,
            nombre_puesto: usuario.nombre_puesto,
            nombre_departamento: usuario.nombre_departamento,
            id_departamento: usuario.id_departamento
          });
        }
      }
    });

    return Array.from(usuariosMap.values());
  }

  function llenarFiltros() {
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

        // Guardar los datos consolidados globalmente
        usuariosData = usuariosConsolidados;

        // ==========================================
        // ACTUALIZAR INDICADORES (KPIs)
        // ==========================================
        actualizarIndicadores(usuariosConsolidados);

        // CONJUNTOS para almacenar valores únicos (evita duplicados)
        const departamentos = new Set();
        const puestos = new Set();
        const estatus = new Set();

        // Iterar los datos y extraer valores únicos
        resp.datos.forEach(persona => {
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
          selectDepartamento.addEventListener('change', (e) => {
            actualizarPuestosSegunDepartamento(e.target.value);
            // Agregar feedback visual
            aplicarFeedbackVisualFiltro(e.target);
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
          selectPuesto.addEventListener('change', (e) => {
            aplicarFeedbackVisualFiltro(e.target);
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
          selectEstatus.addEventListener('change', (e) => {
            aplicarFeedbackVisualFiltro(e.target);
            aplicarFiltros();
          });
        }

        // ==========================================
        // INICIALIZAR FILTRO DE MÚLTIPLES PUESTOS
        // ==========================================
        const selectMultiplePuestos = document.getElementById('FilterMultiplePuestos');
        if (selectMultiplePuestos) {
          // Agregar listener para filtrar en tiempo real
          selectMultiplePuestos.addEventListener('change', (e) => {
            aplicarFeedbackVisualFiltro(e.target);
            aplicarFiltros();
          });
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
      selectMultiple.value = 'multiples';
      aplicarFeedbackVisualFiltro(selectMultiple);
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
      const iconoPuesto = esPrincipal ? '⭐' : '📎';
      const claseBadge = esPrincipal ? 'badge-puesto-principal' : 'badge-puesto-secundario';

      puestosHTML += `
        <div class="d-flex flex-column" style="gap: 0.25rem;">
          <small class="departamento-label">
            <i class="fa fa-building"></i>${puesto.nombre_departamento}
          </small>
          <span class="badge ${claseBadge}" style="width: 100%;">
            <span style="font-size: 0.9rem;">${iconoPuesto}</span>
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
   * ACTUALIZAR PUESTOS SEGÚN DEPARTAMENTO
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
        if (persona.nombre_puesto && persona.nombre_puesto !== 'Sin puesto') {
          todosPuestos.add(persona.nombre_puesto);
        }
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
      return;
    }

    // Extraer SOLO los puestos del departamento seleccionado
    const puestosDelDepartamento = new Set();
    usuariosData.forEach(persona => {
      if (persona.nombre_departamento === departamentoSeleccionado &&
          persona.nombre_puesto &&
          persona.nombre_puesto !== 'Sin puesto') {
        puestosDelDepartamento.add(persona.nombre_puesto);
      }
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
  }

  /**
   * ==========================================
   * APLICAR FILTROS EN TIEMPO REAL
   * ==========================================
   * Filtra la tabla según los valores seleccionados
   */
  function aplicarFiltros() {
    // Obtener valores seleccionados
    const departamentoSeleccionado = document.getElementById('UserRole').value;
    const puestoSeleccionado = document.getElementById('UserPlan').value;
    const estatusSeleccionado = document.getElementById('FilterTransaction').value;
    const multiplePuestosSeleccionado = document.getElementById('FilterMultiplePuestos').value;

    // Filtrar datos
    const datosFiltrados = usuariosData.filter(persona => {
      // Filtro DEPARTAMENTO
      if (departamentoSeleccionado && persona.nombre_departamento !== departamentoSeleccionado) {
        return false;
      }

      // Filtro PUESTO
      if (puestoSeleccionado && persona.nombre_puesto !== puestoSeleccionado) {
        return false;
      }

      // Filtro ESTATUS
      if (estatusSeleccionado && persona.estatus !== estatusSeleccionado) {
        return false;
      }

      // Filtro MÚLTIPLES PUESTOS
      if (multiplePuestosSeleccionado) {
        const tienePuestos = persona.puestos && persona.puestos.length > 1;
        if (multiplePuestosSeleccionado === 'multiples' && !tienePuestos) {
          return false;
        }
        if (multiplePuestosSeleccionado === 'unico' && tienePuestos) {
          return false;
        }
      }

      return true;
    });

    // Actualizar contadores en el filtro de múltiples puestos
    const usuariosMultiples = usuariosData.filter(u => u.puestos && u.puestos.length > 1).length;
    const usuariosUnicos = usuariosData.length - usuariosMultiples;

    // Actualizar el texto del select con los contadores
    const selectMultiple = document.getElementById('FilterMultiplePuestos');
    if (selectMultiple) {
      const options = selectMultiple.querySelectorAll('option');
      if (options[1]) options[1].textContent = `Múltiples puestos (${usuariosMultiples})`;
      if (options[2]) options[2].textContent = `Único puesto (${usuariosUnicos})`;
    }

    // Actualizar indicadores con datos filtrados
    actualizarIndicadores(datosFiltrados);

    // Actualizar tabla con datos filtrados
    actualizarTabla(datosFiltrados);
  }

  /**
   * ==========================================
   * ACTUALIZAR TABLA CON DATOS FILTRADOS
   * ==========================================
   */
  function actualizarTabla(datos) {
    // Si estamos usando DataTables
    const tabla = $('#historialUsuarios').DataTable();

    if (!tabla) {
      console.warn(' DataTable no inicializado');
      return;
    }

    // Mapear datos con soporte para múltiples puestos - VISUALIZACIÓN MEJORADA
    const datosFormateados = datos.map(p => {
      const nombreCompleto = [p.nombres, p.segundo_nombre, p.apellidop, p.apellidom].filter(x => x).join(' ');
      const tienePuestos = p.puestos && p.puestos.length > 1;

      // Generar badges para múltiples puestos con JERARQUÍA VISUAL
      let puestosHTML = '';
      if (tienePuestos) {
        const totalPuestos = p.puestos.length;
        const mostrarDirecto = totalPuestos <= 3;
        const puestosVisible = mostrarDirecto ? p.puestos : p.puestos.slice(0, 2);

        puestosHTML = '<div class="d-flex flex-column gap-2">';

        puestosVisible.forEach((puesto, index) => {
          const esPrincipal = index === 0;
          const colorBadge = obtenerColorDepartamento(puesto.nombre_departamento);
          const iconoPuesto = esPrincipal ? '⭐' : '📎';
          const claseBadge = esPrincipal ? 'badge-puesto-principal' : 'badge-puesto-secundario';

          puestosHTML += `
            <div class="d-flex flex-column" style="gap: 0.25rem;">
              <small class="departamento-label">
                <i class="fa fa-building"></i>${puesto.nombre_departamento}
              </small>
              <span class="badge ${claseBadge}"
                    title="${esPrincipal ? 'Puesto Principal' : 'Puesto Secundario'}: ${puesto.nombre_puesto}">
                <span style="font-size: 0.9rem;">${iconoPuesto}</span>
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
            ${p.nombre_departamento || 'Sin departamento'}
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
          <div class="fw-semibold">
             # ${p.numero_empleado}
          </div>
          <div class="fw-semibold">
              ${nombreCompleto}
          </div>
          <small class="text-muted d-flex align-items-center gap-1">
              <i class="fa fa-key"></i>
              ${p.usuario}
          </small>
          ${tienePuestos ? `<span class="badge badge-multipuesto-indicator mt-1"><i class="fa fa-layer-group me-1"></i>${p.puestos.length} Puestos Asignados</span>` : ''}
        `.trim(),
        departamento: `
          ${sedeHTML}
          ${puestosHTML}
          <hr class="my-2">
          <small class="text-muted d-flex align-items-center gap-1">
              <i class="fa fa-user"></i>Jefe: <strong class="ms-1">${p.nombre_jefe || 'Sin jefe'}</strong>
          </small>
        `.trim(),
        estatus: p.estatus,
        acciones: (() => {
          const puedeEditar = window.puedeEditarTodos;
          return `
         <div class="d-flex flex-wrap gap-1" style="min-width: fit-content;">
             ${puedeEditar
               ? `<button class="btn btn-sm btn-primary ${tienePuestos ? 'btn-with-indicator' : ''}" onclick="editar(${p.id})" title="${tienePuestos ? 'Editar usuario con ' + p.puestos.length + ' puestos asignados' : 'Editar usuario'}">
                 ${tienePuestos ? '<span class="indicator-multiples-puestos" title="' + p.puestos.length + ' puestos">' + p.puestos.length + '</span>' : ''}
                 <i class="fa fa-edit"></i>
             </button>`
               : `<button class="btn btn-sm btn-outline-secondary" onclick="visualizar(${p.id})" title="Visualizar">
                 <i class="fa fa-eye"></i>
             </button>`
             }
             <button class="btn btn-sm btn-info" onclick="cargarDocumentoPersona(this)" data-id-persona="${p.id}" data-nombre="${nombreCompleto.replace(/"/g, '&quot;')}" title="Cargar documento">
                 <i class="fa fa-file"></i>
             </button>
             <button class="btn btn-sm btn-warning" onclick="registra_ausencia(${p.id})" title="Ausencias">
                 <i class="fa fa-person-circle-minus"></i>
             </button>
             <button class="btn btn-sm btn-danger" onclick="baja_gestor(${p.id})" title="Dar de baja">
                 <i class="fa fa-user-slash"></i>
             </button>
             <button class="btn btn-sm" style="background-color: #D2D755; color: white;" onclick="edit_perfil(${p.id})" title="${tienePuestos ? 'Permisos (Gestionar múltiples puestos)' : 'Permisos'}">
                 <i class="fa fa-lock" style="color: #007bff;"></i>
             </button>
         </div>`;
        })()
      };
    });

    // Limpiar y recargar tabla
    tabla.clear().rows.add(datosFormateados).draw();
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
    const filtros = ['UserRole', 'UserPlan', 'FilterTransaction', 'FilterMultiplePuestos'];
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
    // Esperar a que DataTable esté listo
    setTimeout(() => {
      llenarFiltros();
      inicializarFeedbackFiltros();
    }, 800);
  });

  /**
   * ==========================================
   * SELECT CON BÚSQUEDA EN TIEMPO REAL
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
   * INICIALIZAR SELECTS CON BÚSQUEDA
   * ==========================================
   */
  let searchableSelectAddJefe;
  let searchableSelectEditJefe;

  // Inicializar después de que el DOM esté listo
  document.addEventListener('DOMContentLoaded', () => {
    // Esperar un poco para asegurar que los selects están en el DOM
    setTimeout(() => {
      // Inicializar select de "Agregar Usuario"
      const addJefeSelect = document.getElementById('add_id_jefe');
      if (addJefeSelect) {
        searchableSelectAddJefe = new SearchableSelect(addJefeSelect);

      }

      // Inicializar select de "Editar Usuario"
      const editJefeSelect = document.getElementById('edit_id_jefe');
      if (editJefeSelect) {
        searchableSelectEditJefe = new SearchableSelect(editJefeSelect);

      }
    }, 500);
  });

  /**
   * ==========================================
   * GESTIÓN DE MÚLTIPLES PUESTOS - FUNCIONES
   * ==========================================
   */

  // Variable global para almacenar los puestos del usuario actual
  let puestosUsuarioActual = [];
  let usuarioEditandoId = null;
  let todosLosDepartamentos = [];
  let todosLosPuestos = [];

  /**
   * Cargar puestos del usuario en el panel de edición
   */
  function cargarPuestosUsuario(usuarioId) {
    usuarioEditandoId = usuarioId;
    const usuario = usuariosData.find(u => u.id === usuarioId);

    if (!usuario) return;

    if (usuario.puestos && usuario.puestos.length > 0) {
      puestosUsuarioActual = JSON.parse(JSON.stringify(usuario.puestos));
    } else {
      puestosUsuarioActual = (usuario.id_puesto && usuario.id_departamento) ? [{
        id_puesto: usuario.id_puesto,
        nombre_puesto: usuario.nombre_puesto || 'Sin puesto',
        nombre_departamento: usuario.nombre_departamento || 'Sin departamento',
        id_departamento: usuario.id_departamento
      }] : [];
    }
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
      const puedeEliminar = puestosUsuarioActual.length > 1;

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

    Swal.fire({
      title: '¿Eliminar este puesto?',
      html: `
        <div class="text-start">
          <p><strong>Departamento:</strong> ${puesto.nombre_departamento}</p>
          <p><strong>Puesto:</strong> ${puesto.nombre_puesto}</p>
          ${esPrincipal ? '<p class="text-danger mt-3"><i class="fa fa-exclamation-triangle me-1"></i><strong>Este es el puesto principal.</strong> Al eliminarlo, el siguiente puesto se convertirá en principal.</p>' : ''}
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

    // Si queda solo un puesto, ocultar el panel de gestión
    if (puestosUsuarioActual.length === 1) {
      mostrarAlertaMultiplesPuestos(false);
      mostrarContenedorPuestos(false);
    } else {
      renderizarListaPuestos();
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
  function cargarDepartamentosParaAgregar() {
    const select = document.getElementById('edit_nuevo_departamento');
    if (!select) return;

    select.innerHTML = '<option value="">Seleccione un departamento</option>';
    const agregados = new Map();

    if (window.todosDepartamentosBackend && Array.isArray(window.todosDepartamentosBackend)) {
      window.todosDepartamentosBackend.forEach(d => {
        const id = d.id || d.departamento_id;
        const nombre = d.nombre || d.departamento_nombre || '';
        if (id && nombre) {
          agregados.set(Number(id), nombre);
        }
      });
    }
    Array.from(agregados.entries()).sort((a, b) => a[1].localeCompare(b[1])).forEach(([id, nombre]) => {
      const option = document.createElement('option');
      option.value = id;
      option.textContent = nombre;
      option.dataset.nombre = nombre;
      select.appendChild(option);
    });
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
    const select = document.getElementById('edit_editar_departamento');
    if (!select) return;

    select.innerHTML = '<option value="">Seleccione un departamento</option>';
    const agregados = new Map();

    if (window.todosDepartamentosBackend && Array.isArray(window.todosDepartamentosBackend)) {
      window.todosDepartamentosBackend.forEach(d => {
        const id = d.id || d.departamento_id;
        const nombre = d.nombre || d.departamento_nombre || '';
        if (id && nombre) {
          agregados.set(Number(id), nombre);
        }
      });
    }
    Array.from(agregados.entries()).sort((a, b) => a[1].localeCompare(b[1])).forEach(([id, nombre]) => {
      const option = document.createElement('option');
      option.value = id;
      option.textContent = nombre;
      option.dataset.nombre = nombre;
      select.appendChild(option);
    });
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
    if (index === null || index === undefined) return;

    const selectDept = document.getElementById('edit_editar_departamento');
    const selectPuesto = document.getElementById('edit_editar_puesto');

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
      return;
    }

    // Actualizar el puesto
    puestosUsuarioActual[index] = {
      id_puesto: puestoId,
      nombre_puesto: puestoNombre,
      nombre_departamento: departamentoNombre,
      id_departamento: departamentoId
    };

    // Re-renderizar
    renderizarListaPuestos();
    cancelarEditarPuesto();
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
    const otros = (puestosUsuarioActual || []).filter(p => String(p.id_puesto) !== String(idPuesto));
    puestosUsuarioActual = [principal, ...otros];
    if (typeof renderizarListaPuestos === 'function') renderizarListaPuestos();
  }

  function obtenerPuestosParaGuardar() {
    const principalId = document.getElementById('edit_id_puesto') && document.getElementById('edit_id_puesto').value;
    const principal = principalId ? [{ id_puesto: principalId }] : [];
    const otros = (puestosUsuarioActual || [])
      .filter(p => String(p.id_puesto) !== String(principalId))
      .map(p => ({ id_puesto: p.id_puesto }));
    const lista = principal.length ? [...principal, ...otros] : otros;
    return lista;
  }

  /**
   * ==========================================
   * FUNCIÓN DESCARGAR PLANTILLA GESTORES
   * ==========================================
   */
  function descargarPlantillaGestores() {
    // Obtener filtros activos
    const departamento = document.getElementById('UserRole').value || '';
    const puesto = document.getElementById('UserPlan').value || '';
    const estatus = document.getElementById('FilterTransaction').value || '';

    // Generar mensaje dinámico según filtros
    let mensajeFiltros = 'Se descargará un archivo Excel con ';
    let detallesFiltros = [];

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
      confirmButtonColor: '#0047bb',
      cancelButtonColor: '#a1acb8',
      reverseButtons: true
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Generando archivo Excel...',
          html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div><p style="margin-top: 1rem;">Por favor espera...</p>',
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
 * CASCADA GEOGRÁFICA: País → Estado → Municipio
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
 */
document.addEventListener('DOMContentLoaded', function () {

    const selectPaisAdd = document.getElementById('add_id_pais');
    if (selectPaisAdd) {
        selectPaisAdd.addEventListener('change', function () {
            const idPais = this.value;
            resetCascadaAdd();

            if (!idPais) return;

            // Actualizar etiquetas según país
            actualizarLabels(idPais, 'add');

            // Cargar estados
            cargarEstados(idPais, 'add_id_div_nivel1', function () {
                document.getElementById('div_add_estado').style.display = '';
                document.getElementById('add_id_div_nivel1').disabled = false;
            });
        });
    }

    const selectEstadoAdd = document.getElementById('add_id_div_nivel1');
    if (selectEstadoAdd) {
        selectEstadoAdd.addEventListener('change', function () {
            const idEstado = this.value;

            // Resetear municipio
            const selMun = document.getElementById('add_id_div_nivel2');
            selMun.innerHTML = '<option value="">Seleccione...</option>';
            selMun.disabled = true;
            document.getElementById('div_add_municipio').style.display = 'none';

            if (!idEstado) return;

            cargarMunicipios(idEstado, 'add_id_div_nivel2', function () {
                document.getElementById('div_add_municipio').style.display = '';
                document.getElementById('add_id_div_nivel2').disabled = false;
            });
        });
    }

    // ===================================
    // EVENTO LISTENER PARA EDITAR USUARIO
    // ===================================
    const selectEstadoEdit = document.getElementById('edit_id_div_nivel1');
    if (selectEstadoEdit) {
        selectEstadoEdit.addEventListener('change', function () {
            const idEstado = this.value;

            // Resetear municipio
            const selMun = document.getElementById('edit_id_div_nivel2');
            selMun.innerHTML = '<option value="">Seleccione...</option>';
            selMun.disabled = true;
            document.getElementById('div_edit_municipio').style.display = 'none';

            if (!idEstado) return;

            cargarMunicipios(idEstado, 'edit_id_div_nivel2', function () {
                document.getElementById('div_edit_municipio').style.display = '';
                document.getElementById('edit_id_div_nivel2').disabled = false;
            });
        });
    }
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
function cargarEstados(idPais, selectId, onSuccess) {
    const select = document.getElementById(selectId);
    select.innerHTML = '<option value="">Cargando...</option>';

    fetch('/CapHum/getEstados', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_pais: idPais })
    })
    .then(r => r.json())
    .then(data => {
        select.innerHTML = '<option value="">Seleccione...</option>';
        if (data.success && data.datos) {
            data.datos.forEach(e => {
                const opt = document.createElement('option');
                opt.value       = e.id;
                opt.textContent = e.nombre;
                select.appendChild(opt);
            });
        }
        if (onSuccess) onSuccess();
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
    select.innerHTML = '<option value="">Cargando...</option>';

    fetch('/CapHum/getMunicipios', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_estado: idEstado })
    })
    .then(r => r.json())
    .then(data => {
        select.innerHTML = '<option value="">Seleccione...</option>';
        if (data.success && data.datos) {
            data.datos.forEach(m => {
                const opt = document.createElement('option');
                opt.value       = m.id;
                opt.textContent = m.nombre;
                select.appendChild(opt);
            });
        }
        if (onSuccess) onSuccess();
    })
    .catch(() => {
        select.innerHTML = '<option value="">Error al cargar</option>';
    });
}

/**
 * Precargar cascada en el offcanvas EDITAR
 * (llamar desde la función editar() después de poblar el formulario)
 */
function precargarCascadaEdit(idPais, idEstado, idMunicipio) {
    if (!idPais) {
        document.getElementById('div_edit_estado').style.display    = 'none';
        document.getElementById('div_edit_municipio').style.display = 'none';
        return;
    }

    actualizarLabels(idPais, 'edit');

    cargarEstados(idPais, 'edit_id_div_nivel1', function () {
        document.getElementById('div_edit_estado').style.display = '';

        if (idEstado) {
            document.getElementById('edit_id_div_nivel1').value = idEstado;

            cargarMunicipios(idEstado, 'edit_id_div_nivel2', function () {
                document.getElementById('div_edit_municipio').style.display = '';
                if (idMunicipio) {
                    document.getElementById('edit_id_div_nivel2').value = idMunicipio;
                }
            });
        }
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

    /* ── Contador animado ── */
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

    /* ── Barra ── */
    function kpiAnimateBar(id, pct, delay) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.width = '0%';
        setTimeout(function() { el.style.width = pct + '%'; }, (delay || 0) + 80);
    }

    /* ── Donut ── */
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

    /* ── Tooltip donut ── */
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

    /* ── Revelar celdas ── */
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

    /* ── Aplicar modo ── */
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

    /* ── Público ── */
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

    /* ── Actualizar valores (llamar desde el código existente) ──
       Reemplaza las asignaciones directas a kpi-departamentos,
       kpi-puestos y kpi-total-empleados con esta función.

       Ejemplo de uso:
         kpiUpdateValues({ dep: 12, puesto: 28, total: 147,
                           arcDep: 75, arcPuesto: 55, arcTotal: 90 });
    ── */
    window.kpiUpdateValues = function(data) {
        var numDep    = document.getElementById('kpi-departamentos');
        var numPuesto = document.getElementById('kpi-puestos');
        var numTotal  = document.getElementById('kpi-total-empleados');
        var numRol1   = document.getElementById('kpi-rol1-numero');
        var numRol2   = document.getElementById('kpi-rol2-numero');

        if (numDep    && data.dep    !== undefined) kpiAnimateCounter(numDep,    data.dep,    600,  0);
        if (numPuesto && data.puesto !== undefined) kpiAnimateCounter(numPuesto, data.puesto, 600, 60);
        if (numTotal  && data.total  !== undefined) kpiAnimateCounter(numTotal,  data.total,  700, 120);
        if (numRol1   && data.rol1   !== undefined) kpiAnimateCounter(numRol1,   data.rol1,   600, 180);
        if (numRol2   && data.rol2   !== undefined) kpiAnimateCounter(numRol2,   data.rol2,   600, 240);

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
        var _trunc = function(s, n) { return s && s.length > n ? s.substring(0, n - 1) + '…' : (s || '—'); };
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

    /* ── Init ── */
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
        if (elTotal  && data.total  !== undefined) kpiAnimateCounterB(elTotal,  data.total,  600,   0);
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

        // Tendencias en donuts
        var trendTotal  = document.getElementById('kpi-trend-b-total');
        var trendTotalL = document.getElementById('kpi-trend-b-total-lbl');
        var trendDep    = document.getElementById('kpi-trend-b-dep');
        var trendPuesto = document.getElementById('kpi-trend-b-puesto');
        if (trendTotal  && data.bajasRef !== undefined) {
            trendTotal.innerHTML = '<i class="bx bx-trending-down"></i>' + data.bajasRef;
        }
        if (trendTotalL && data.refLabel !== undefined) trendTotalL.textContent = data.refLabel;
        if (trendDep    && data.topDepNombre    !== undefined) {
            trendDep.innerHTML = '<i class="bx bx-buildings"></i>' + _trunc(data.topDepNombre, 16);
            trendDep.title = data.topDepNombre;
        }
        if (trendPuesto && data.topPuestoNombre !== undefined) {
            trendPuesto.innerHTML = '<i class="bx bx-briefcase"></i>' + _trunc(data.topPuestoNombre, 16);
            trendPuesto.title = data.topPuestoNombre;
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

    /* ── Init ── */
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
        // NOTA: kpiRevealCellsB se llama desde inicializarBajas() DESPUÉS de que
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

</script>
