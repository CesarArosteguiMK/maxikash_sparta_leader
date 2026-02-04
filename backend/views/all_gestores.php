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

    #offcanvasEditPerfil .tab-content:not(.doc-example-content) {
        padding: .25rem 0;
    }

    /* Estilos para el Modal de Permisos */
    #modalEditPerfil .modal-content {
        border-radius: 1rem;
        overflow: hidden;
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
        justify-content: center;
        font-size: 10px;
        color: white;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 10;
    }
    
    /* Estilo para botones de acción con indicador */
    .btn-with-indicator {
        position: relative;
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

    .kpi-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        background: #ffffff;
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    /* Borde superior colorido */
    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--kpi-color-start), var(--kpi-color-end));
        transition: height 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 10px 10px -5px rgba(0, 0, 0, 0.1);
    }

    .kpi-card:hover::before {
        height: 6px;
    }

    /* Colores específicos para cada tipo de indicador */
    .kpi-card.tipo-departamento {
        --kpi-color-start: #4F46E5;
        --kpi-color-end: #6366F1;
    }

    .kpi-card.tipo-puesto {
        --kpi-color-start: #10B981;
        --kpi-color-end: #34D399;
    }

    .kpi-card.tipo-rol {
        --kpi-color-start: #8B5CF6;
        --kpi-color-end: #A78BFA;
    }

    .kpi-card.tipo-total {
        --kpi-color-start: #F59E0B;
        --kpi-color-end: #FBBF24;
    }

    .kpi-card.tipo-especifico {
        --kpi-color-start: #EF4444;
        --kpi-color-end: #F87171;
    }

    /* Colores para KPIs de Bajas - Tema rojo/naranja/oscuro */
    #panelIndicadoresBajas .kpi-card.tipo-total {
        --kpi-color-start: #DC2626;
        --kpi-color-end: #EF4444;
    }

    #panelIndicadoresBajas .kpi-card.tipo-departamento {
        --kpi-color-start: #EA580C;
        --kpi-color-end: #F97316;
    }

    #panelIndicadoresBajas .kpi-card.tipo-puesto {
        --kpi-color-start: #D97706;
        --kpi-color-end: #F59E0B;
    }

    #panelIndicadoresBajas .kpi-card.tipo-especifico {
        --kpi-color-start: #7C2D12;
        --kpi-color-end: #9A3412;
    }

    /* Estilos para botones de filtros rápidos */
    .btn-filtro-rapido {
        transition: all 0.3s ease;
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-width: 2px;
    }

    .btn-filtro-rapido:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }

    .btn-filtro-rapido.active {
        background-color: #0d6efd !important;
        color: white !important;
        border-color: #0d6efd !important;
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
    }

    .btn-filtro-rapido.btn-outline-success.active {
        background-color: #198754 !important;
        border-color: #198754 !important;
        box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
    }

    .kpi-card .card-body {
        position: relative;
        padding: 0.75rem 0.7rem;
        text-align: center;
    }

    .kpi-number {
        font-size: 1.3rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.3rem;
        background: linear-gradient(135deg, var(--kpi-color-start), var(--kpi-color-end));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-family: 'Segoe UI', system-ui, sans-serif;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .kpi-card:hover .kpi-number {
        transform: scale(1.1);
        filter: brightness(1.2);
    }

    /* Animación de pulso para números actualizados */
    @keyframes pulse-update {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.15);
        }
    }

    .kpi-number.updating {
        animation: pulse-update 0.5s ease-in-out;
    }

    .kpi-label {
        font-size: 0.55rem;
        color: #64748B;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .kpi-icon {
        font-size: 1.2rem;
        opacity: 0.15;
        position: absolute;
        right: 0.5rem;
        top: 0.5rem;
        color: var(--kpi-color-start);
        transition: all 0.3s ease;
    }

    .kpi-card:hover .kpi-icon {
        opacity: 0.25;
        transform: scale(1.1) rotate(5deg);
    }

    /* Patrón de fondo sutil */
    .kpi-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image: 
            radial-gradient(circle at 2px 2px, rgba(0, 0, 0, 0.02) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
        opacity: 0.5;
    }

    /* Separadores entre indicadores */
    .kpi-separator {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0 0.3rem;
    }

    .kpi-separator .line {
        width: 1px;
        height: 35px;
        background: linear-gradient(180deg, transparent, rgba(148, 163, 184, 0.4), transparent);
    }

    /* Responsive para tablets */
    @media (max-width: 991px) {
        .kpi-item {
            min-width: 110px;
            max-width: 140px;
        }

        .kpi-number {
            font-size: 1.15rem;
        }
        
        .kpi-label {
            font-size: 0.52rem;
        }
        
        .kpi-separator .line {
            height: 30px;
        }
    }

    /* Responsive para móviles - Grid simétrico 2x2 */
    @media (max-width: 767px) {
        .kpi-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            justify-items: stretch;
        }

        .kpi-item {
            min-width: unset;
            max-width: unset;
            width: 100%;
        }

        .kpi-separator {
            display: none !important;
        }

        .kpi-number {
            font-size: 1.05rem;
        }
        
        .kpi-label {
            font-size: 0.5rem;
            white-space: normal;
        }
        
        .kpi-card .card-body {
            padding: 0.6rem;
        }
    }

    /* Para móviles muy pequeños - una columna */
    @media (max-width: 480px) {
        .kpi-wrapper {
            grid-template-columns: 1fr;
        }
    }

    /* ===== TOOLTIPS ENRIQUECIDOS ===== */
    .kpi-tooltip {
        position: absolute;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #ffffff;
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        z-index: 9999;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s ease;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        min-width: 200px;
        max-width: 300px;
    }

    .kpi-tooltip.show {
        opacity: 1;
    }

    /* Flecha del tooltip removida para diseño flotante limpio */
    /*
    .kpi-tooltip::before {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 0;
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid #1e293b;
    }
    */

    .kpi-tooltip-title {
        font-weight: 700;
        margin-bottom: 0.4rem;
        color: #fbbf24;
        font-size: 0.8rem;
    }

    .kpi-tooltip-item {
        display: flex;
        justify-content: space-between;
        padding: 0.2rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .kpi-tooltip-item:last-child {
        border-bottom: none;
    }

    .kpi-tooltip-label {
        color: #cbd5e1;
    }

    .kpi-tooltip-value {
        font-weight: 600;
        color: #fff;
    }

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

</style>
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
             PANEL DE INDICADORES
        ======================== -->
        <div class="row m-4 mb-3">
            <div class="col-12">
                <div class="kpi-wrapper">
                    <!-- Indicador: Departamentos -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-departamento shadow-sm h-100" data-tipo="departamentos">
                            <div class="card-body">
                                <i class="bx bx-buildings kpi-icon"></i>
                                <div class="kpi-number" id="kpi-departamentos">0</div>
                                <div class="kpi-label">Departamentos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador -->
                    <div class="kpi-separator">
                        <div class="line"></div>
                    </div>

                    <!-- Indicador: Puestos -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-puesto shadow-sm h-100" data-tipo="puestos">
                            <div class="card-body">
                                <i class="bx bx-briefcase kpi-icon"></i>
                                <div class="kpi-number" id="kpi-puestos">0</div>
                                <div class="kpi-label">Puestos</div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador Dinámico 1 -->
                    <div class="kpi-separator" id="separator-rol1" style="display: none;">
                        <div class="line"></div>
                    </div>

                    <!-- Indicador Dinámico 1: Rol específico -->
                    <div class="kpi-item" id="kpi-rol1-container" style="display: none;">
                        <div class="card kpi-card tipo-rol shadow-sm h-100" data-tipo="rol1">
                            <div class="card-body">
                                <i class="bx bx-user-circle kpi-icon"></i>
                                <div class="kpi-number" id="kpi-rol1-numero">0</div>
                                <div class="kpi-label" id="kpi-rol1-label" title="">Rol 1</div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador Dinámico 2 -->
                    <div class="kpi-separator" id="separator-rol2" style="display: none;">
                        <div class="line"></div>
                    </div>

                    <!-- Indicador Dinámico 2: Rol específico -->
                    <div class="kpi-item" id="kpi-rol2-container" style="display: none;">
                        <div class="card kpi-card tipo-rol shadow-sm h-100" data-tipo="rol2">
                            <div class="card-body">
                                <i class="bx bx-user-check kpi-icon"></i>
                                <div class="kpi-number" id="kpi-rol2-numero">0</div>
                                <div class="kpi-label" id="kpi-rol2-label" title="">Rol 2</div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador Empleados -->
                    <div class="kpi-separator" id="separator-empleados" style="display: none;">
                        <div class="line"></div>
                    </div>

                    <!-- Indicador: Total de Empleados (Dinámico según filtros) -->
                    <div class="kpi-item" id="kpi-total-empleados-container" style="display: none;">
                        <div class="card kpi-card tipo-total shadow-sm h-100" data-tipo="total">
                            <div class="card-body">
                                <i class="bx bx-group kpi-icon"></i>
                                <div class="kpi-number" id="kpi-total-empleados">0</div>
                                <div class="kpi-label" id="kpi-total-empleados-label" title="">Total Empleados</div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador Empleados Puesto -->
                    <div class="kpi-separator" id="separator-empleados-puesto" style="display: none;">
                        <div class="line"></div>
                    </div>

                    <!-- Indicador: Empleados por Puesto -->
                    <div class="kpi-item" id="kpi-empleados-container" style="display: none;">
                        <div class="card kpi-card tipo-especifico shadow-sm h-100" data-tipo="puesto-especifico">
                            <div class="card-body">
                                <i class="bx bx-user-pin kpi-icon"></i>
                                <div class="kpi-number" id="kpi-empleados-puesto">0</div>
                                <div class="kpi-label" id="kpi-empleados-label" title="">Empleados</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: DESGLOSE DETALLADO DE INDICADORES -->
        <div class="modal fade" id="modalKpiDesglose" tabindex="-1" aria-labelledby="modalKpiTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalKpiTitle">
                            <i class="bx bx-chart me-2"></i>
                            Desglose Detallado
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" id="modalKpiContent">
                        <!-- Contenido dinámico generado por JavaScript -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Tooltip personalizado -->
        <div class="kpi-tooltip" id="kpiTooltip"></div>

        <!-- =======================
             PANEL DE INDICADORES PARA BAJAS (oculto por defecto)
        ======================== -->
        <div id="panelIndicadoresBajas" style="display: none;" class="row m-4 mb-3">
            <div class="col-12">
                <div class="kpi-wrapper">
                    <!-- Indicador: Total de Bajas -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-total shadow-sm h-100" data-tipo="total-bajas">
                            <div class="card-body">
                                <i class="bx bx-user-x kpi-icon"></i>
                                <div class="kpi-number" id="kpi-bajas-total">0</div>
                                <div class="kpi-label">Total de Bajas</div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador -->
                    <div class="kpi-separator">
                        <div class="line"></div>
                    </div>

                    <!-- Indicador: Bajas Departamentales -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-departamento shadow-sm h-100" data-tipo="depto-bajas" style="cursor: pointer;" id="kpi-bajas-departamentos">
                            <div class="card-body">
                                <i class="bx bx-buildings kpi-icon"></i>
                                <div class="kpi-number" id="kpi-bajas-depto-numero">0</div>
                                <div class="kpi-label">Bajas Departamental</div>
                                
                            </div>
                        </div>
                    </div>

                    <!-- Separador -->
                    <div class="kpi-separator">
                        <div class="line"></div>
                    </div>

                    <!-- Indicador: Bajas por Puesto -->
                    <div class="kpi-item">
                        <div class="card kpi-card tipo-puesto shadow-sm h-100" data-tipo="puesto-bajas" style="cursor: pointer;" id="kpi-bajas-puestos">
                            <div class="card-body">
                                <i class="bx bx-briefcase kpi-icon"></i>
                                <div class="kpi-number" id="kpi-bajas-puesto-numero">0</div>
                                <div class="kpi-label">Bajas por Puesto</div>
                               
                            </div>
                        </div>
                    </div>

                    <!-- Separador (para bajas del período) -->
                    <div class="kpi-separator" id="separator-bajas-periodo" style="display: none;">
                        <div class="line"></div>
                    </div>

                    <!-- Indicador: Bajas del Período (visible solo con filtro de fecha) -->
                    <div class="kpi-item" id="kpi-bajas-periodo-container" style="display: none;">
                        <div class="card kpi-card tipo-especifico shadow-sm h-100" data-tipo="periodo-bajas">
                            <div class="card-body">
                                <i class="bx bx-calendar-event kpi-icon"></i>
                                <div class="kpi-number" id="kpi-bajas-periodo-numero">0</div>
                                <div class="kpi-label" id="kpi-bajas-periodo-label" title="">Bajas del Período</div>
                            </div>
                        </div>
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
                    <i class="bx bx-download icon-sm me-sm-2"></i>
                    <span class="d-none d-sm-inline-block">Plantilla</span>
                </button>
                
                <!-- Botón Agregar Usuario -->
                <button
                  type="button"
                  class="btn btn-primary add-new btn-action-size"
                  data-bs-toggle="offcanvas"
                  data-bs-target="#offcanvasAddUser"
                >
                    <i class="icon-base bx bx-plus icon-sm me-sm-2"></i>
                    <span class="d-none d-sm-inline-block">Agregar Usuario</span>
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
                    <label class="form-label">Nombres *</label>
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
                            <option value="Incumplimiento de objetivos">Incumplimiento de objetivos</option>
                            <option value="Falta de asistencia">Falta de asistencia</option>
                            <option value="Mutuo acuerdo">Mutuo acuerdo</option>
                            <option value="Desempeño insuficiente">Desempeño insuficiente</option>
                            <option value="Falta de puntualidad">Falta de puntualidad</option>
                            <option value="Problemas disciplinarios">Problemas disciplinarios</option>
                            <option value="Conflictos internos">Conflictos internos</option>
                            <option value="Cambio de puesto">Cambio de puesto</option>
                            <option value="Traslado a otra sucursal">Traslado a otra sucursal</option>
                            <option value="Renuncia por motivos personales">Renuncia por motivos personales</option>
                            <option value="Baja por salud">Baja por salud</option>
                            <option value="Baja administrativa">Baja administrativa</option>
                            <option value="Falta de capacitación">Falta de capacitación</option>
                            <option value="Empleado duplicado">Empleado Duplicado</option>
                            <option value="Otros">Otros</option>
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
                                        <th>Archivo</th>
                                        <th>Fecha de carga</th>
                                        <th>Válido</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="cargarDocPersona_tablaArchivos">
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
                            <input type="datetime-local" class="form-control" id="fechaInicio">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label"><strong>Fecha fin</strong></label>
                            <input type="datetime-local" class="form-control" id="fechaFin">
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
            <h5 class="offcanvas-title">Editar Gestor</h5>
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
                    <label class="form-label">Nombres *</label>
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

                <div class="mb-7">
                    <label class="form-label">Contraseña *</label>
                    <input type="text" id="edit_contrasena" class="form-control">
                </div>

                <button type="button" class="btn btn-primary me-3" onclick="UpdateGestor()" > Guardar </button>
                <button type="button" class="btn btn-label-danger" data-bs-dismiss="offcanvas" > Cancelar </button>
            </form>
        </div>
    </div>

    <!-- =======================
      MODAL - GESTIÓN DE PERMISOS Y PUESTOS
 ======================== -->
    <div class="modal fade" id="modalEditPerfil" tabindex="-1" aria-labelledby="modalEditPerfilLabel" aria-hidden="true">
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
                                <div id="modulos-form"></div>
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
                                <div id="puestos-form"></div>
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
                <label class="form-label fw-semibold">Nombres *</label>
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

    console.log('📊 Indicadores actualizados:', {
      departamentos: departamentos.size,
      puestos: puestos.size,
      departamentoSeleccionado: departamentoSeleccionado || 'Ninguno',
      puestoSeleccionado: puestoSeleccionado || 'Ninguno',
      totalEmpleados: datos.length
    });
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

    console.log('👥 Roles actualizados para', departamento + ':', puestosOrdenados);
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
    
    // Posicionar el tooltip
    tooltip.style.left = rect.left + (rect.width / 2) - 100 + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 10 + 'px';
    tooltip.classList.add('show');
  }

  function ocultarTooltip() {
    const tooltip = document.getElementById('kpiTooltip');
    if (tooltip) {
      tooltip.classList.remove('show');
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
    const conteo = {};
    datos.forEach(u => {
      if (u.nombre_departamento && u.nombre_departamento !== 'Sin departamento') {
        conteo[u.nombre_departamento] = (conteo[u.nombre_departamento] || 0) + 1;
      }
    });
    
    const departamentos = Object.entries(conteo)
      .sort((a, b) => b[1] - a[1])
      .map(([dept, cantidad]) => ({ dept, cantidad }));
    
    const total = departamentos.length;
    const totalEmpleados = datos.length;
    const promedio = (totalEmpleados / total).toFixed(1);
    
    let html = `
      <div class="row mb-3">
        <div class="col-md-4">
          <div class="stat-card" data-color="indigo">
            <div class="stat-label"><i class="bx bx-buildings me-1"></i>Total Departamentos</div>
            <div class="stat-value">${total}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" data-color="indigo">
            <div class="stat-label"><i class="bx bx-group me-1"></i>Total Empleados</div>
            <div class="stat-value">${totalEmpleados}</div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="stat-card" data-color="indigo">
            <div class="stat-label"><i class="bx bx-trending-up me-1"></i>Promedio por Dept.</div>
            <div class="stat-value">${promedio}</div>
          </div>
        </div>
      </div>
      
      <h6 class="mb-3 fw-bold text-indigo"><i class="bx bx-bar-chart me-2"></i>Distribución de Empleados</h6>
      <div class="table-responsive">
        <table class="table table-hover table-sm">
          <thead class="table-header-indigo">
            <tr>
              <th>#</th>
              <th>Departamento</th>
              <th class="text-center">Empleados</th>
              <th class="text-center">% del Total</th>
              <th>Distribución</th>
            </tr>
          </thead>
          <tbody>
    `;
    
    departamentos.forEach((item, index) => {
      const porcentaje = ((item.cantidad / totalEmpleados) * 100).toFixed(1);
      html += `
        <tr>
          <td>${index + 1}</td>
          <td><strong>${item.dept}</strong></td>
          <td class="text-center"><span class="badge badge-count" style="background: linear-gradient(135deg, #4F46E5, #6366F1);">${item.cantidad}</span></td>
          <td class="text-center"><strong>${porcentaje}%</strong></td>
          <td>
            <div class="progress" style="height: 6px;">
              <div class="progress-bar progress-bar-kpi" role="progressbar" 
                   style="width: ${porcentaje}%; background: linear-gradient(90deg, #4F46E5, #6366F1);" 
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
    
    return { titulo: 'Departamentos - Análisis Completo', html };
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
    // Guardar datos globalmente para los modales
    datosBajasGlobal = datosBajas || [];

    if (!datosBajas || datosBajas.length === 0) {
      document.getElementById('kpi-bajas-total').textContent = '0';
      document.getElementById('kpi-bajas-depto-numero').textContent = '0';
      document.getElementById('kpi-bajas-puesto-numero').textContent = '0';
      ocultarIndicadorBajasPeriodo();
      return;
    }

    // 1. TOTAL DE BAJAS
    const totalBajas = datosBajas.length;
    animarNumero('kpi-bajas-total', totalBajas);

    // 2. CONTEO TOTAL DE DEPARTAMENTOS CON BAJAS
    const departamentosConBajas = obtenerTodosDepartamentosBajas(datosBajas);
    animarNumero('kpi-bajas-depto-numero', departamentosConBajas.length);

    // 3. CONTEO TOTAL DE PUESTOS CON BAJAS
    const puestosConBajas = obtenerTodosPuestosBajas(datosBajas);
    animarNumero('kpi-bajas-puesto-numero', puestosConBajas.length);

    // 4. BAJAS DEL PERÍODO (solo si hay filtro de fecha)
    if (tieneFiltroFecha && rangoFechasBajas) {
      mostrarIndicadorBajasPeriodo(totalBajas, rangoFechasBajas);
    } else {
      ocultarIndicadorBajasPeriodo();
    }

    console.log('📊 Indicadores de Bajas actualizados:', {
      totalBajas: totalBajas,
      departamentos: departamentosConBajas.length,
      puestos: puestosConBajas.length,
      tieneFiltroFecha: tieneFiltroFecha
    });
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
  function mostrarIndicadorBajasPeriodo(cantidad, rangoFechas) {
    const container = document.getElementById('kpi-bajas-periodo-container');
    const separador = document.getElementById('separator-bajas-periodo');
    const label = document.getElementById('kpi-bajas-periodo-label');

    if (container && label) {
      container.style.display = '';
      
      // Formatear las fechas para el label
      const fechaInicio = new Date(rangoFechas.inicio).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
      const fechaFin = new Date(rangoFechas.fin).toLocaleDateString('es-MX', { day: '2-digit', month: 'short' });
      const labelText = `${fechaInicio} - ${fechaFin}`;
      
      label.textContent = labelText;
      label.setAttribute('title', `Bajas del ${rangoFechas.inicio} al ${rangoFechas.fin}`);
      
      animarNumero('kpi-bajas-periodo-numero', cantidad);
      
      if (separador) {
        separador.style.display = '';
      }
    }
  }

  /**
   * OCULTAR INDICADOR DE BAJAS DEL PERÍODO
   */
  function ocultarIndicadorBajasPeriodo() {
    const container = document.getElementById('kpi-bajas-periodo-container');
    const separador = document.getElementById('separator-bajas-periodo');
    
    if (container) container.style.display = 'none';
    if (separador) separador.style.display = 'none';
  }

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
    }

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
    
    // 🔍 DEBUG: Ver una muestra de los datos crudos que llegan
    console.log('🔍 [consolidarUsuarios] Muestra de datos RAW (primeros 3):', usuarios.slice(0, 3));
    
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
        
        console.log('📊 Usuarios consolidados:', usuariosConsolidados.length, 'de', resp.datos.length, 'registros');

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
    console.log('Actualizando puestos para departamento:', departamentoSeleccionado);

    const selectPuesto = document.getElementById('UserPlan');

    if (!selectPuesto) {
      console.warn('Select UserPlan no encontrado');
      return;
    }

    // Si no hay departamento seleccionado, mostrar TODOS los puestos
    if (!departamentoSeleccionado) {
      console.log('📌 Sin departamento seleccionado, mostrando todos los puestos');

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

      console.log('Se muestran todos los puestos:', Array.from(todosPuestos));
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

    console.log('Puestos encontrados en', departamentoSeleccionado + ':', Array.from(puestosDelDepartamento));

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

    console.log('UserPlan actualizado con', puestosDelDepartamento.size, 'puestos de', departamentoSeleccionado);
  }

  /**
   * ==========================================
   * APLICAR FILTROS EN TIEMPO REAL
   * ==========================================
   * Filtra la tabla según los valores seleccionados
   */
  function aplicarFiltros() {
    console.log('🔍 Aplicando filtros...');

    // Obtener valores seleccionados
    const departamentoSeleccionado = document.getElementById('UserRole').value;
    const puestoSeleccionado = document.getElementById('UserPlan').value;
    const estatusSeleccionado = document.getElementById('FilterTransaction').value;
    const multiplePuestosSeleccionado = document.getElementById('FilterMultiplePuestos').value;

    console.log('Filtros activos:', {
      departamento: departamentoSeleccionado || 'Todos',
      puesto: puestoSeleccionado || 'Todos',
      estatus: estatusSeleccionado || 'Todos',
      multiplePuestos: multiplePuestosSeleccionado || 'Todos'
    });

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

    console.log('Resultados filtrados:', datosFiltrados.length, 'registros de', usuariosData.length);

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
          const esPrincipal = index === 0; // El primer puesto es el principal
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
        // Un solo puesto, mostrar formato tradicional
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
          ${puestosHTML}
          <hr class="my-2">
          <small class="text-muted d-flex align-items-center gap-1">
              <i class="fa fa-user"></i>Jefe: <strong class="ms-1">${p.nombre_jefe || 'Sin jefe'}</strong>
          </small>
        `.trim(),
        estatus: p.estatus,
        acciones: `
         <div class="d-flex flex-wrap gap-1" style="min-width: fit-content;">
             <button class="btn btn-sm btn-primary ${tienePuestos ? 'btn-with-indicator' : ''}" onclick="editar(${p.id})" title="${tienePuestos ? 'Editar usuario con ' + p.puestos.length + ' puestos asignados' : 'Editar usuario'}">
                 ${tienePuestos ? '<span class="indicator-multiples-puestos" title="' + p.puestos.length + ' puestos">' + p.puestos.length + '</span>' : ''}
                 <i class="fa fa-edit"></i>
             </button>
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
         </div>`
      };
    });

    // Limpiar y recargar tabla
    tabla.clear().rows.add(datosFormateados).draw();
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
    
    // 🔍 DEBUG: Ver estructura del usuario antes de cargar puestos
    console.log('🔍 [cargarPuestosUsuario] Usuario encontrado:', usuario);
    console.log('🔍 [cargarPuestosUsuario] Puestos del usuario:', usuario?.puestos);
    
    if (!usuario) return;
    
    // Si el usuario tiene múltiples puestos
    if (usuario.puestos && usuario.puestos.length > 1) {
      puestosUsuarioActual = JSON.parse(JSON.stringify(usuario.puestos)); // Copia profunda
      mostrarAlertaMultiplesPuestos(true);
      mostrarContenedorPuestos(true);
      renderizarListaPuestos();
    } else {
      // Usuario con un solo puesto
      puestosUsuarioActual = usuario.puestos ? JSON.parse(JSON.stringify(usuario.puestos)) : [{
        id_puesto: usuario.id_puesto,
        nombre_puesto: usuario.nombre_puesto,
        nombre_departamento: usuario.nombre_departamento,
        id_departamento: usuario.id_departamento
      }];
      mostrarAlertaMultiplesPuestos(false);
      mostrarContenedorPuestos(false);
    }
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
        
        // Cargar puestos del departamento
        cargarPuestosParaEditar();
        
        // Esperar y seleccionar el puesto
        setTimeout(() => {
          const selectPuesto = document.getElementById('edit_editar_puesto');
          if (selectPuesto) {
            selectPuesto.value = puesto.id_puesto;
          }
        }, 300);
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
   * Cargar departamentos en el select para agregar puesto
   */
  function cargarDepartamentosParaAgregar() {
    const select = document.getElementById('edit_nuevo_departamento');
    if (!select) return;
    
    // Limpiar opciones excepto la primera
    select.innerHTML = '<option value="">Seleccione un departamento</option>';
    
    // 🔧 FIX: Obtener departamentos únicos desde el array puestos[]
    const departamentos = new Set();
    usuariosData.forEach(u => {
      // Si el usuario tiene múltiples puestos, iterar el array puestos[]
      if (u.puestos && u.puestos.length > 0) {
        u.puestos.forEach(puesto => {
          if (puesto.nombre_departamento && puesto.nombre_departamento !== 'Sin departamento') {
            departamentos.add(JSON.stringify({
              id: puesto.id_departamento,
              nombre: puesto.nombre_departamento
            }));
          }
        });
      } else {
        // Fallback para usuarios sin consolidar
        if (u.nombre_departamento && u.nombre_departamento !== 'Sin departamento') {
          departamentos.add(JSON.stringify({
            id: u.id_departamento,
            nombre: u.nombre_departamento
          }));
        }
      }
    });
    
    // Agregar opciones
    Array.from(departamentos).forEach(deptStr => {
      const dept = JSON.parse(deptStr);
      const option = document.createElement('option');
      option.value = dept.id;
      option.textContent = dept.nombre;
      option.dataset.nombre = dept.nombre;
      select.appendChild(option);
    });
  }
  
  /**
   * Cargar puestos según departamento seleccionado
   */
  function cargarPuestosParaAgregar() {
    const selectDept = document.getElementById('edit_nuevo_departamento');
    const selectPuesto = document.getElementById('edit_nuevo_puesto');
    
    if (!selectDept || !selectPuesto) return;
    
    const departamentoId = selectDept.value;
    const departamentoNombre = selectDept.options[selectDept.selectedIndex]?.dataset.nombre;
    
    // Limpiar select de puestos
    selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';
    
    if (!departamentoId) return;
    
    // 🔧 FIX: Obtener puestos del departamento seleccionado desde el array puestos[]
    const puestos = new Set();
    usuariosData.forEach(u => {
      // Si el usuario tiene múltiples puestos, iterar el array puestos[]
      if (u.puestos && u.puestos.length > 0) {
        u.puestos.forEach(puesto => {
          if (puesto.nombre_departamento === departamentoNombre && puesto.nombre_puesto && puesto.nombre_puesto !== 'Sin puesto') {
            puestos.add(JSON.stringify({
              id: puesto.id_puesto,
              nombre: puesto.nombre_puesto
            }));
          }
        });
      } else {
        // Fallback para usuarios sin consolidar
        if (u.nombre_departamento === departamentoNombre && u.nombre_puesto && u.nombre_puesto !== 'Sin puesto') {
          puestos.add(JSON.stringify({
            id: u.id_puesto,
            nombre: u.nombre_puesto
          }));
        }
      }
    });
    
    // Agregar opciones
    Array.from(puestos).forEach(puestoStr => {
      const puesto = JSON.parse(puestoStr);
      const option = document.createElement('option');
      option.value = puesto.id;
      option.textContent = puesto.nombre;
      
      // 🔍 DEBUG: Verificar que se están creando correctamente las opciones
      console.log('📋 [cargarPuestosParaAgregar] Opción creada - value:', option.value, '(tipo:', typeof option.value, '), text:', option.textContent);
      
      selectPuesto.appendChild(option);
    });
    
    console.log('✅ [cargarPuestosParaAgregar] Total opciones agregadas:', selectPuesto.options.length - 1);
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
   * Cargar departamentos para editar puesto
   */
  function cargarDepartamentosParaEditar() {
    const select = document.getElementById('edit_editar_departamento');
    if (!select) return;
    
    // Limpiar opciones excepto la primera
    select.innerHTML = '<option value="">Seleccione un departamento</option>';
    
    // 🔧 FIX: Obtener departamentos únicos desde el array puestos[]
    const departamentos = new Set();
    usuariosData.forEach(u => {
      // Si el usuario tiene múltiples puestos, iterar el array puestos[]
      if (u.puestos && u.puestos.length > 0) {
        u.puestos.forEach(puesto => {
          if (puesto.nombre_departamento && puesto.nombre_departamento !== 'Sin departamento') {
            departamentos.add(JSON.stringify({
              id: puesto.id_departamento,
              nombre: puesto.nombre_departamento
            }));
          }
        });
      } else {
        // Fallback para usuarios sin consolidar
        if (u.nombre_departamento && u.nombre_departamento !== 'Sin departamento') {
          departamentos.add(JSON.stringify({
            id: u.id_departamento,
            nombre: u.nombre_departamento
          }));
        }
      }
    });
    
    // Agregar opciones
    Array.from(departamentos).forEach(deptStr => {
      const dept = JSON.parse(deptStr);
      const option = document.createElement('option');
      option.value = dept.id;
      option.textContent = dept.nombre;
      option.dataset.nombre = dept.nombre;
      select.appendChild(option);
    });
  }
  
  /**
   * Cargar puestos para editar según departamento
   */
  function cargarPuestosParaEditar() {
    const selectDept = document.getElementById('edit_editar_departamento');
    const selectPuesto = document.getElementById('edit_editar_puesto');
    
    if (!selectDept || !selectPuesto) return;
    
    const departamentoId = selectDept.value;
    const departamentoNombre = selectDept.options[selectDept.selectedIndex]?.dataset.nombre;
    
    // Limpiar select de puestos
    selectPuesto.innerHTML = '<option value="">Seleccione un puesto</option>';
    
    if (!departamentoId) return;
    
    // 🔧 FIX: Obtener puestos del departamento seleccionado desde el array puestos[]
    const puestos = new Set();
    usuariosData.forEach(u => {
      // Si el usuario tiene múltiples puestos, iterar el array puestos[]
      if (u.puestos && u.puestos.length > 0) {
        u.puestos.forEach(puesto => {
          if (puesto.nombre_departamento === departamentoNombre && puesto.nombre_puesto && puesto.nombre_puesto !== 'Sin puesto') {
            puestos.add(JSON.stringify({
              id: puesto.id_puesto,
              nombre: puesto.nombre_puesto
            }));
          }
        });
      } else {
        // Fallback para usuarios sin consolidar
        if (u.nombre_departamento === departamentoNombre && u.nombre_puesto && u.nombre_puesto !== 'Sin puesto') {
          puestos.add(JSON.stringify({
            id: u.id_puesto,
            nombre: u.nombre_puesto
          }));
        }
      }
    });
    
    // Agregar opciones
    Array.from(puestos).forEach(puestoStr => {
      const puesto = JSON.parse(puestoStr);
      const option = document.createElement('option');
      option.value = puesto.id;
      option.textContent = puesto.nombre;
      selectPuesto.appendChild(option);
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
    
    // 🔍 DEBUG: Ver qué valores se están capturando
    console.log('🔍 agregarNuevoPuesto() - Valores capturados:');
    console.log('  - departamentoId:', departamentoId, '(tipo:', typeof departamentoId, ')');
    console.log('  - departamentoNombre:', departamentoNombre);
    console.log('  - puestoId:', puestoId, '(tipo:', typeof puestoId, ')');
    console.log('  - puestoNombre:', puestoNombre);
    console.log('  - Select options:', selectPuesto.selectedOptions[0]);
    
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
    
    console.log('📦 Objeto nuevoPuesto creado:', nuevoPuesto);
    
    puestosUsuarioActual.push(nuevoPuesto);
    
    console.log('✅ puestosUsuarioActual después de agregar:', puestosUsuarioActual);
    
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
  function obtenerPuestosParaGuardar() {
    console.log('💾 obtenerPuestosParaGuardar() - Retornando:', puestosUsuarioActual);
    return puestosUsuarioActual;
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
        form.action = '/CapHum/descargarPlantillaGestores';
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

</script>
