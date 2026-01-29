<div class="container-xxl flex-grow-1 container-p-y">

    <!-- 🎯 ENCABEZADO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                <i class="fa fa-terminal text-primary me-2"></i>
                                Shell Segundómetro - Gestión de Reportes
                            </h4>
                            <p class="text-muted mb-0">
                                Modulo para gestionar archivos de reportes <code>mega_rpt_*.csv.zip</code>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted"><i class="fa fa-sync-alt me-1"></i>Actualización automática cada 30 s</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 📝 DESCRIPCIÓN DEL MÓDULO -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fa fa-info-circle text-primary me-2"></i>¿Qué hace este módulo?
                    </h5>
                    <p class="text-muted mb-3">
                        Esta herramienta permite copiar y gestionar archivos de reportes <code>mega_rpt_*.csv.zip</code> de forma segura y controlada, sin necesidad de utilizar comandos de terminal.
                    </p>
                    <ul class="mb-0 ps-4 ms-2">
                        <li class="mb-2"><strong>Historial:</strong> se muestran los reportes generados en los últimos 2 días (hoy y ayer), organizados por fecha. Cada archivo indica si es del <span class="text-danger">proveedor</span> o <span class="text-success">nosotros</span>.</li>
                        <li class="mb-2"><strong>Frecuencia:</strong> los reportes se generan en el servidor de forma periódica; la lista se actualiza automáticamente cada 30 segundos.</li>
                        <li class="mb-2"><strong>Copiar +1s:</strong> con un clic se copia el archivo en el servidor y el nuevo nombre incrementa +1 segundo (ej.: <code>07_31_58</code> → <code>07_31_59</code>; si es 59 segundos, pasa al minuto siguiente).</li>
                        <li class="mb-2"><strong>Descargar:</strong> se puede descargar cualquier reporte directamente desde la interfaz al equipo local.</li>
                        <li class="mb-2"><strong>Eliminar:</strong> solo los archivos propios (nosotros, owner root) pueden eliminarse; los del proveedor no muestran esta opción.</li>
                        <li class="mb-0"><strong>Ejecución:</strong> todas las operaciones se ejecutan en el servidor remoto de forma automática (listar, copiar, descargar y eliminar).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 📁 LISTA DE ARCHIVOS -->
    <div class="row">
        <div class="col-12">
            <div id="archivos-container">
                <!-- Los archivos se cargarán aquí dinámicamente -->
            </div>
        </div>
    </div>

</div>

<!-- 💫 OVERLAY DE CARGA -->
<div id="loading-overlay" style="
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 9999;
    justify-content: center;
    align-items: center;
">
    <div class="text-center">
        <div class="spinner-border text-light" style="width: 3rem; height: 3rem;" role="status">
            <span class="visually-hidden">Procesando...</span>
        </div>
        <p class="text-white mt-3 fw-semibold">Procesando operación...</p>
    </div>
</div>

<!-- 🎨 ESTILOS PERSONALIZADOS -->
<style>
    .font-monospace {
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(67, 89, 113, 0.05);
        cursor: pointer;
    }
    
    .avatar-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
    }
    
    .card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
    }
    
    .btn-success {
        transition: all 0.3s ease;
    }
    
    .btn-success:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(40, 199, 111, 0.4);
    }
    
    code {
        background-color: #f5f5f5;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.9em;
    }
    
    .alert-primary {
        border-left: 4px solid #696cff;
    }
    
    /* Forzar texto blanco en headers de archivos */
    .card-header.bg-primary h5,
    .card-header.bg-primary h5 i,
    .card-header.bg-primary * {
        color: #ffffff !important;
    }
    
    /* Badge contador de archivos - texto negro/oscuro */
    .card-header.bg-primary .badge {
        color: #2c3e50 !important;
        background-color: #ffffff !important;
        font-weight: 600;
    }
</style>
