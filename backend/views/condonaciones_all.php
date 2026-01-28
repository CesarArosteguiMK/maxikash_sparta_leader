<style>
    /* ===== CARDS DE CONDONACIONES ===== */
    .hover-shadow {
        transition: all 0.3s ease;
    }

    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    /* ===== BADGES DE ESTADO ===== */
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.75rem;
        font-weight: 500;
    }

    /* ===== FORMULARIOS ===== */
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #566a7f;
    }

    .form-control:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
    }

    /* ===== PANEL DE DETALLE ===== */
    #detalleCondonacion .card {
        position: sticky;
        top: 20px;
    }

    /* ===== ANIMACIONES ===== */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card {
        animation: fadeIn 0.3s ease;
    }

    /* ===== BOTONES ===== */
    .btn-sm {
        padding: 0.4rem 1rem;
        font-size: 0.875rem;
    }

    .btn i {
        margin-right: 0.25rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991px) {
        #detalleCondonacion .card {
            position: relative;
            top: 0;
            margin-top: 1rem;
        }
    }

    /* ===== TEXTO TRUNCADO ===== */
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* ===== ICONOS GRANDES ===== */
    .bx-file-blank {
        opacity: 0.3;
    }

    /* ===== ESTADOS VISUALES ===== */
    .card-title {
        color: #566a7f;
        font-weight: 600;
    }

    .text-muted {
        font-size: 0.9rem;
    }

    /* ===== SEPARADORES ===== */
    .card-body hr {
        margin: 1rem 0;
        opacity: 0.1;
    }

    /* ===== ESPACIADO ===== */
    .py-5 {
        padding-top: 3rem !important;
        padding-bottom: 3rem !important;
    }

    /* ===== FILTROS Y BÚSQUEDA ===== */
    .filter-container {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
    }

    .filter-container .form-select,
    .filter-container .form-control {
        font-size: 0.875rem;
    }

    /* ===== ESTADÍSTICAS ===== */
    .stats-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }

    .stats-card.pendiente {
        border-left-color: #ffc107;
    }

    .stats-card.aprobada {
        border-left-color: #28a745;
    }

    .stats-card.rechazada {
        border-left-color: #dc3545;
    }

    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 0.5rem;
    }

    .stats-label {
        font-size: 0.875rem;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== HISTORIAL ===== */
    .timeline-item {
        position: relative;
        padding-left: 2rem;
        padding-bottom: 1.5rem;
        border-left: 2px solid #e0e0e0;
    }

    .timeline-item:last-child {
        border-left: none;
        padding-bottom: 0;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #696cff;
        border: 2px solid #fff;
    }

    .timeline-date {
        font-size: 0.75rem;
        color: #999;
        margin-bottom: 0.25rem;
    }

    .timeline-content {
        font-size: 0.875rem;
    }

    /* ===== TOAST PERSONALIZADO ===== */
    .toast-custom {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 250px;
    }

    /* ===== LOADING SPINNER ===== */
    .spinner-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 200px;
    }

    .spinner-border {
        width: 3rem;
        height: 3rem;
        color: #696cff;
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-state i {
        font-size: 4rem;
        color: #d0d0d0;
        margin-bottom: 1rem;
    }

    .empty-state p {
        color: #999;
        font-size: 1rem;
    }
</style>

<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        
        <!-- Header con título y botón -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">
                    <i class="bx bx-money me-2"></i>Gestión de Condonaciones
                </h4>
                <p class="text-muted mb-0">Administra las solicitudes de condonación de deudas</p>
            </div>
            <button class="btn btn-primary" onclick="mostrarFormularioNuevaCondonacion()">
                <i class="bx bx-plus"></i> Nueva Condonación
            </button>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="filter-container">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label mb-1">Estado</label>
                    <select class="form-select form-select-sm" id="filtroEstado" onchange="filtrarCondonaciones()">
                        <option value="">Todos</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="aprobada">Aprobadas</option>
                        <option value="rechazada">Rechazadas</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label mb-1">Buscar colaborador</label>
                    <input type="text" class="form-control form-control-sm" id="buscarColaborador" 
                           placeholder="Nombre del colaborador..." onkeyup="filtrarCondonaciones()">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Ordenar por</label>
                    <select class="form-select form-select-sm" id="ordenar" onchange="filtrarCondonaciones()">
                        <option value="fecha_desc">Fecha (más reciente)</option>
                        <option value="fecha_asc">Fecha (más antigua)</option>
                        <option value="monto_desc">Monto (mayor)</option>
                        <option value="monto_asc">Monto (menor)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card stats-card pendiente h-100">
                    <div class="card-body">
                        <div class="stats-number text-warning" id="statPendientes">0</div>
                        <div class="stats-label">Pendientes</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card aprobada h-100">
                    <div class="card-body">
                        <div class="stats-number text-success" id="statAprobadas">0</div>
                        <div class="stats-label">Aprobadas</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card rechazada h-100">
                    <div class="card-body">
                        <div class="stats-number text-danger" id="statRechazadas">0</div>
                        <div class="stats-label">Rechazadas</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card h-100">
                    <div class="card-body">
                        <div class="stats-number text-primary" id="statMontoTotal">$0</div>
                        <div class="stats-label">Monto Total</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="row">
            <!-- Panel Principal: Lista de Condonaciones -->
            <div class="col-lg-8">
                <div id="listaCondonaciones">
                    <!-- Spinner de carga -->
                    <div class="spinner-container">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Panel Derecho: Detalle de Condonación -->
            <div class="col-lg-4">
                <div id="detalleCondonacion">
                    <div class="card">
                        <div class="card-body empty-state">
                            <i class="bx bx-file-blank"></i>
                            <p>Selecciona una condonación para ver los detalles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Función auxiliar para mostrar notificaciones
function showToast(mensaje, tipo = 'info') {
    // Implementación simple de toast
    const colores = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };

    const toast = document.createElement('div');
    toast.className = 'toast-custom alert alert-' + (tipo === 'error' ? 'danger' : tipo);
    toast.innerHTML = mensaje;
    toast.style.backgroundColor = colores[tipo] || colores.info;
    toast.style.color = '#fff';
    toast.style.padding = '1rem';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)';
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Función de filtrado (se implementará en el controlador)
function filtrarCondonaciones() {
    // La lógica de filtrado se manejará en el JavaScript del controlador
    getCondonaciones();
}
</script>
