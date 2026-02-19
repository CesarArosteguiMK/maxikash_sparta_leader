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
                            <small class="text-muted"><i class="fa fa-sync-alt me-1"></i>Actualización al abrir y en ventanas 7:31, 9:31, 11:31, 13:31, 14:31, 16:31, 18:31, 20:31, 23:50 (CDMX)</small>
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
                        <li class="mb-2"><strong>Frecuencia:</strong> la lista se actualiza al abrir la página (F5) y solo durante 2 minutos en las ventanas 7:31, 9:31, 11:31, 13:31, 14:31, 16:31, 18:31, 20:31 y 23:50 (hora CDMX), cada 30 s en esa ventana. No se ejecutan consultas SSH si está en otra pestaña o menú.</li>
                        <li class="mb-2"><strong>Copiar +1s:</strong> con un clic se copia el archivo en el servidor y el nuevo nombre incrementa +1 segundo (ej.: <code>07_31_58</code> → <code>07_31_59</code>; si es 59 segundos, pasa al minuto siguiente).</li>
                        <li class="mb-2"><strong>Descargar:</strong> se puede descargar cualquier reporte directamente desde la interfaz al equipo local.</li>
                        <li class="mb-2"><strong>Eliminar:</strong> solo los archivos propios (nosotros, owner root) pueden eliminarse; los del proveedor no muestran esta opción.</li>
                        <li class="mb-0"><strong>Ejecución:</strong> todas las operaciones se ejecutan en el servidor remoto de forma automática (listar, copiar, descargar y eliminar).</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón Truncar y Monitorear -->
    <div class="row mb-3">
        <div class="col-12">
            <button type="button" class="btn btn-truncar-segundometro" id="btnTruncarSegundometro" disabled title="Disponible solo los martes de 7:00 a 9:30 AM (CDMX)">
                <i class="fa fa-cut me-2"></i>Truncar
            </button>
            <button type="button" class="btn btn-monitorear-segundometro ms-2" id="btnMonitorearSegundometro" title="Abrir monitoreo en vivo en esta página. Usa «Minimizar» en el panel para usar Truncar y otros botones sin cortar el stream.">
                <i class="fa fa-terminal me-2"></i>Monitorear
            </button>
            <span class="ms-3 align-middle" id="wrapLinkTruncarPrueba" style="display: none;">
                <a href="#" id="linkTruncarModoPrueba" class="small text-muted" title="Habilita el botón Truncar para probar (sin restricción de horario). Recarga la página con ?truncar_test=1">Habilitar Truncar para pruebas</a>
            </span>
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

<!-- Panel Monitorear: iframe con stream en la misma página; minimizar deja usar otros botones sin cortar el stream -->
<div id="panelMonitorear" style="display:none; position:fixed; top:80px; right:20px; width:520px; max-width:95vw; height:420px; max-height:75vh; z-index:9000; background:#1e1e1e; border:1px solid #444; border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,0.4); flex-direction:column; overflow:hidden;">
    <div id="panelMonitorearHeader" style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; background:#2d2d2d; cursor:move; user-select:none; border-bottom:1px solid #444;">
        <span style="color:#fff; font-weight:600;"><i class="fa fa-terminal me-2"></i>Monitorear</span>
        <span>
            <button type="button" id="panelMonitorearMinimizar" style="background:transparent; border:none; color:#aaa; cursor:pointer; padding:4px 8px;" title="Minimizar (el stream sigue; podrás usar Truncar y demás botones)"><i class="fa fa-window-minimize"></i></button>
            <button type="button" id="panelMonitorearCerrar" style="background:transparent; border:none; color:#aaa; cursor:pointer; padding:4px 8px;" title="Cerrar"><i class="fa fa-times"></i></button>
        </span>
    </div>
    <div id="panelMonitorearBody" style="flex:1; min-height:0; display:flex; flex-direction:column;">
        <iframe id="panelMonitorearIframe" style="flex:1; width:100%; height:100%; min-height:0; border:none; background:#1e1e1e;"></iframe>
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
    
    /* Botón Truncar: neon leve deshabilitado, neon más vivo habilitado */
    .btn-truncar-segundometro {
        font-weight: 600;
        transition: background-color 0.25s ease, box-shadow 0.25s ease, color 0.25s ease, border-color 0.25s ease;
    }
    .btn-truncar-segundometro:disabled {
        background: rgba(0, 230, 230, 0.12);
        border: 1px solid rgba(0, 200, 220, 0.4);
        color: rgba(0, 120, 140, 0.85);
        box-shadow: 0 0 12px rgba(0, 230, 230, 0.15);
    }
    .btn-truncar-segundometro:disabled .fa-cut {
        color: rgba(0, 150, 170, 0.9);
    }
    .btn-truncar-segundometro:not(:disabled) {
        background: linear-gradient(135deg, #00c9d4 0%, #00a8b8 100%);
        border: 1px solid rgba(0, 200, 220, 0.8);
        color: #fff;
        box-shadow: 0 0 16px rgba(0, 200, 220, 0.4);
    }
    .btn-truncar-segundometro:not(:disabled):hover {
        background: linear-gradient(135deg, #00d4e0 0%, #00b8c8 100%);
        color: #fff;
        box-shadow: 0 0 20px rgba(0, 220, 230, 0.5);
    }
    .btn-truncar-segundometro:not(:disabled) .fa-cut {
        color: #fff;
    }

    /* Botón Monitorear: tono elegante y suave (ámbar/arena), mismo tamaño que Truncar */
    .btn-monitorear-segundometro {
        font-weight: 600;
        padding: 0.5rem 1rem;
        transition: background-color 0.25s ease, box-shadow 0.25s ease, color 0.25s ease, border-color 0.25s ease;
        background: linear-gradient(135deg, #c4b5a0 0%, #a89888 100%);
        border: 1px solid rgba(168, 152, 136, 0.6);
        color: #3d3630;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }
    .btn-monitorear-segundometro:hover {
        background: linear-gradient(135deg, #d4c8b8 0%, #b8a898 100%);
        color: #2d2824;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }
    .btn-monitorear-segundometro .fa-terminal {
        color: #4a423a;
    }

    /* Modo oscuro: Truncar y Monitorear con estilo consistente (relleno sólido, buen contraste) */
    body.dark-mode .btn-truncar-segundometro:disabled {
        background: rgba(0, 180, 200, 0.2);
        border: 1px solid rgba(0, 200, 220, 0.5);
        color: rgba(160, 230, 240, 0.9);
        box-shadow: 0 0 12px rgba(0, 200, 220, 0.2);
    }
    body.dark-mode .btn-truncar-segundometro:disabled .fa-cut {
        color: rgba(160, 230, 240, 0.95);
    }
    body.dark-mode .btn-truncar-segundometro:not(:disabled) {
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        border: 1px solid rgba(20, 184, 166, 0.6);
        color: #fff;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }
    body.dark-mode .btn-truncar-segundometro:not(:disabled):hover {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        color: #fff;
        box-shadow: 0 0 18px rgba(20, 184, 166, 0.4);
    }
    body.dark-mode .btn-truncar-segundometro:not(:disabled) .fa-cut {
        color: #fff;
    }
    /* Monitorear: conservar colores del modo claro en modo oscuro (anular reglas globales de dark-mode.css) */
    body.dark-mode .btn-monitorear-segundometro {
        background: linear-gradient(135deg, #c4b5a0 0%, #a89888 100%) !important;
        background-image: linear-gradient(135deg, #c4b5a0 0%, #a89888 100%) !important;
        border: 1px solid rgba(168, 152, 136, 0.7) !important;
        color: #3d3630 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25) !important;
        filter: none !important;
    }
    body.dark-mode .btn-monitorear-segundometro:hover {
        background: linear-gradient(135deg, #d4c8b8 0%, #b8a898 100%) !important;
        background-image: linear-gradient(135deg, #d4c8b8 0%, #b8a898 100%) !important;
        color: #2d2824 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
        filter: none !important;
    }
    body.dark-mode .btn-monitorear-segundometro .fa-terminal {
        color: #4a423a !important;
    }

    /* Badge contador de archivos - texto negro/oscuro */
    .card-header.bg-primary .badge {
        color: #2c3e50 !important;
        background-color: #ffffff !important;
        font-weight: 600;
    }
</style>
