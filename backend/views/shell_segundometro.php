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

    <!-- 📊 INFORMACIÓN DEL SISTEMA -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mb-3 mx-auto">
                        <div class="avatar-initial bg-label-info rounded">
                            <i class="fa fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="mb-1">Historial</h5>
                    <p class="text-muted mb-0">Últimos 2 días</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mb-3 mx-auto">
                        <div class="avatar-initial bg-label-success rounded">
                            <i class="fa fa-clock fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="mb-1">Frecuencia</h5>
                    <p class="text-muted mb-0">Cada 2 horas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="avatar avatar-lg mb-3 mx-auto">
                        <div class="avatar-initial bg-label-warning rounded">
                            <i class="fa fa-copy fa-2x"></i>
                        </div>
                    </div>
                    <h5 class="mb-1">Operación</h5>
                    <p class="text-muted mb-0">Copiar +1 segundo</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 📝 INSTRUCCIONES -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-primary d-flex align-items-start" role="alert">
                <i class="fa fa-info-circle fa-2x me-3 mt-1"></i>
                <div>
                    <h5 class="alert-heading mb-2">¿Cómo funciona?</h5>
                    <p class="mb-2">
                        Esta herramienta te permite copiar archivos de reportes de forma segura sin necesidad de usar comandos de terminal.
                    </p>
                    <ul class="mb-0">
                        <li><strong>Visualiza</strong> todos los reportes de los últimos 2 días organizados por fecha</li>
                        <li><strong>Copia archivos</strong> con un simple clic en el botón "Copiar +1s"</li>
                        <li>El archivo copiado tendrá <strong>+1 segundo</strong> en su nombre (ej: <code>07_31_58</code> → <code>07_31_59</code>)</li>
                        <li>Se ejecuta automáticamente el comando <code>sudo cp</code> en el servidor</li>
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
