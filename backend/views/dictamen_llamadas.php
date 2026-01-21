<div class="card">
    <div class="card">
        <div class="row g-0 align-items-center">
            <!-- Texto -->
            <div class="col-12 col-md-8">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">HOLA, <?= $_SESSION['usuario_nombre']; ?> </h5>
                    <p class="mb-6">
                        Permite consultar los dictámenes de llamadas y visualizar la información correspondiente a un periodo específico, como la última semana o un rango de fechas definido.
                    </p>
                </div>
            </div>

            <!-- Imagen -->
            <div class="col-12 col-md-4">
                <div class="card-body ps-md-2 pe-5 text-end">
                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                         class="img-fluid scaleX-n1-rtl"
                         alt="View Badge User">
                </div>
            </div>

            <div class="row gy-6 mb-6">
                <div class="col-lg-12">
                    <div class="card shadow-none bg-label-primary h-100">
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2">Disponible para descarga diaria</h5>
                                    <p class="text-body w-sm-80 app-academy-xl-100">El ultimo corte es: Reporte_Corte_Jueves_13_30</p>
                                </div>
                                <!-- Botón para abrir modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalReporte">
                                    Descargar Último Corte
                                </button>
                            </div>
                            <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png" alt="boy illustration">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para consulta de reporte -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-labelledby="modalReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- modal-xl para más ancho -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReporteLabel">Consulta de Dictamen de Llamadas - Reporte Completo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario de búsqueda -->
                <form id="formBuscarReporte" method="POST" action="/Reporteria/BuscarReporte">
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <label for="fechaInicio" class="form-label">Fecha de inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
                        </div>
                        <div class="col-md-5">
                            <label for="fechaFin" class="form-label">Fecha de fin</label>
                            <input type="date" class="form-control" id="fechaFin" name="fechaFin" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Tabla de resultados COMPLETA -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="tablaReporte">
                        <thead class="table-light">
                            <tr>
                                <th>ID Dictamen</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>ID Crédito</th>
                                <th>Cliente</th>
                                <th>Tipo Contacto</th>
                                <th>Resultado</th>
                                <th>Dictamen</th>
                                <th>Motivo No Pago</th>
                                <th>Tipo Motivo</th>
                                <th>Plataforma</th>
                                <th>Fuente Ingresos</th>
                                <th>Comentarios</th>
                                <th>Agente ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los resultados se cargarán aquí dinámicamente -->
                            <tr id="sinResultados">
                                <td colspan="14" class="text-center text-muted py-4">
                                    <i class="fas fa-search fa-2x mb-3"></i>
                                    <p>Ingrese las fechas y haga clic en Buscar para consultar los reportes</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <!-- Botón de descarga (inicialmente deshabilitado) -->
                <button type="button" class="btn btn-success" id="btnDescargarReporte" disabled>
                    <i class="fas fa-download me-2"></i>Descargar Reporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Script para manejar la funcionalidad del modal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formBuscar = document.getElementById('formBuscarReporte');
    const tablaReporte = document.getElementById('tablaReporte');
    const tbody = tablaReporte.querySelector('tbody');
    const btnDescargar = document.getElementById('btnDescargarReporte');
    const sinResultados = document.getElementById('sinResultados');
    
    // Variables para almacenar datos de búsqueda
    let datosBusqueda = {
        fechaInicio: '',
        fechaFin: ''
    };

    // Manejar envío del formulario de búsqueda
    formBuscar.addEventListener('submit', async function(e) {
        e.preventDefault(); // Prevenir envío tradicional
        
        // Obtener fechas
        datosBusqueda.fechaInicio = document.getElementById('fechaInicio').value;
        datosBusqueda.fechaFin = document.getElementById('fechaFin').value;
        
        // Validar fechas
        if (!datosBusqueda.fechaInicio || !datosBusqueda.fechaFin) {
            alert('Por favor, seleccione ambas fechas');
            return;
        }
        
        // Mostrar indicador de carga
        sinResultados.innerHTML = `
            <td colspan="14" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Buscando datos...</p>
            </td>`;
        
        // Deshabilitar botón de descarga
        btnDescargar.disabled = true;
        
        try {
            // DEBUG: Mostrar lo que se enviará
            console.log('Enviando petición a:', '/EstadoCuenta/buscarReporteDictamen');
            console.log('Datos:', {
                fechaInicio: datosBusqueda.fechaInicio,
                fechaFin: datosBusqueda.fechaFin
            });

            // **SOLUCIÓN: Hacer la petición AJAX real con manejo de sesión**
            const response = await fetch('/EstadoCuenta/buscarReporteDictamen', {
                method: 'POST',
                credentials: 'include', // ¡IMPORTANTE! Incluye cookies de sesión
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    fechaInicio: datosBusqueda.fechaInicio,
                    fechaFin: datosBusqueda.fechaFin
                })
            });

            // DEBUG: Ver respuesta cruda
            console.log('Respuesta recibida:', {
                status: response.status,
                statusText: response.statusText,
                redirected: response.redirected,
                url: response.url
            });

            // Manejar redirección 302 explícitamente
            if (response.redirected) {
                console.warn('Redirección detectada a:', response.url);
                alert('Su sesión ha expirado. Redirigiendo al login...');
                window.location.href = '/login';
                return;
            }

            // Verificar si el status es 302
            if (response.status === 302) {
                const redirectUrl = response.headers.get('Location');
                console.warn('302 Found - Redirección a:', redirectUrl);
                alert('Sesión expirada. Por favor, inicie sesión nuevamente.');
                window.location.href = redirectUrl || '/login';
                return;
            }

            // Verificar otros errores HTTP
            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
            }

            // Intentar parsear como JSON
            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                
                // DEBUG: Ver datos recibidos
                console.log('Datos JSON recibidos:', data);
                
                // Manejar error de sesión desde el JSON
                if (data.code === 'SESSION_EXPIRED' || 
                    data.mensaje?.includes('Sesión') || 
                    data.mensaje?.includes('sesión') ||
                    (data.success === false && data.mensaje?.includes('expir'))) {
                    
                    console.error('Error de sesión en JSON:', data.mensaje);
                    alert('Su sesión ha expirado. Redirigiendo al login...');
                    window.location.href = '/login';
                    return;
                }
                
                // Si no es exitoso por otra razón
                if (!data.success) {
                    throw new Error(data.mensaje || 'Error en la búsqueda');
                }
                
                // Limpiar tabla
                tbody.innerHTML = '';
                
                // Si no hay datos en la respuesta
                if (!data.data || data.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="14" class="text-center text-muted py-4">
                                <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                <p>No se encontraron reportes para el rango de fechas seleccionado</p>
                            </td>
                        </tr>`;
                    btnDescargar.disabled = true;
                    return;
                }
                
                // Llenar la tabla con TODOS los datos del JSON
                data.data.forEach(item => {
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${item.id_dictamen || ''}</td>
                        <td>${item.fecha_registro || ''}</td>
                        <td>${item.hora_registro || ''}</td>
                        <td>${item.id_credito || ''}</td>
                        <td>${item.nombre_cliente || 'N/A'}</td>
                        <td>${item.tipo_contacto || ''}</td>
                        <td>${item.resultado_contacto || ''}</td>
                        <td>${item.dictamen || ''}</td>
                        <td>${item.motivo_no_pago || 'N/A'}</td>
                        <td>${item.tipo_motivo_no_pago || 'N/A'}</td>
                        <td>${item.plataforma || ''}</td>
                        <td>${item.fuente_ingresos || ''}</td>
                        <td>${item.comentarios || ''}</td>
                        <td>${item.usuario_id || ''}</td>
                    `;
                    tbody.appendChild(fila);
                });
                
                // Habilitar botón de descarga
                btnDescargar.disabled = false;
                
            } else {
                // Si no es JSON, leer como texto
                const text = await response.text();
                console.error('Respuesta no JSON recibida:', text.substring(0, 500));
                
                // Verificar si es página de login/error
                if (text.includes('login') || 
                    text.includes('Login') || 
                    text.includes('Iniciar sesión') ||
                    text.includes('sign in') ||
                    text.toLowerCase().includes('sesión')) {
                    
                    console.error('Detectada página de login en respuesta HTML');
                    alert('Su sesión ha expirado. Será redirigido al login.');
                    window.location.href = '/login';
                    return;
                }
                
                // Si parece ser HTML de error
                if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                    throw new Error('El servidor devolvió una página HTML en lugar de datos JSON');
                }
                
                // Intentar parsear como JSON aunque no diga application/json
                try {
                    const data = JSON.parse(text);
                    console.log('JSON parseado manualmente:', data);
                    
                    // Si tiene éxito, procesar los datos
                    if (!data.success || !data.data || data.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="14" class="text-center text-muted py-4">
                                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                    <p>No se encontraron reportes para el rango de fechas seleccionado</p>
                                </td>
                            </tr>`;
                        btnDescargar.disabled = true;
                        return;
                    }
                    
                    // Llenar tabla con datos parseados
                    data.data.forEach(item => {
                        const fila = document.createElement('tr');
                        fila.innerHTML = `
                            <td>${item.id_dictamen || ''}</td>
                            <td>${item.fecha_registro || ''}</td>
                            <td>${item.hora_registro || ''}</td>
                            <td>${item.id_credito || ''}</td>
                            <td>${item.nombre_cliente || 'N/A'}</td>
                            <td>${item.tipo_contacto || ''}</td>
                            <td>${item.resultado_contacto || ''}</td>
                            <td>${item.dictamen || ''}</td>
                            <td>${item.motivo_no_pago || 'N/A'}</td>
                            <td>${item.tipo_motivo_no_pago || 'N/A'}</td>
                            <td>${item.plataforma || ''}</td>
                            <td>${item.fuente_ingresos || ''}</td>
                            <td>${item.comentarios || ''}</td>
                            <td>${item.usuario_id || ''}</td>
                        `;
                        tbody.appendChild(fila);
                    });
                    
                    btnDescargar.disabled = false;
                    
                } catch (jsonError) {
                    throw new Error('El servidor devolvió un formato no esperado: ' + text.substring(0, 100));
                }
            }
            
        } catch (error) {
            console.error('Error completo al cargar los datos:', error);
            
            // Mostrar mensaje de error específico
            let mensajeError = error.message;
            if (error.message.includes('Failed to fetch')) {
                mensajeError = 'Error de conexión con el servidor. Verifique su conexión a internet.';
            } else if (error.message.includes('NetworkError')) {
                mensajeError = 'Error de red. Por favor, intente nuevamente.';
            }
            
            tbody.innerHTML = `
                <tr id="sinResultados">
                    <td colspan="14" class="text-center text-danger py-4">
                        <i class="fas fa-times-circle fa-2x mb-3"></i>
                        <p>Error al cargar los datos: ${mensajeError}</p>
                        <button onclick="window.location.reload()" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fas fa-redo me-1"></i> Reintentar
                        </button>
                    </td>
                </tr>`;
            btnDescargar.disabled = true;
        }
    });

    // Manejar clic en botón de descarga
    btnDescargar.addEventListener('click', function() {
        if (datosBusqueda.fechaInicio && datosBusqueda.fechaFin) {
            // Crear URL para descarga (ajusta según tu endpoint)
            const url = `/EstadoCuenta/descargarReporteDictamen?fechaInicio=${encodeURIComponent(datosBusqueda.fechaInicio)}&fechaFin=${encodeURIComponent(datosBusqueda.fechaFin)}`;
            console.log('Descargando desde:', url);
            window.open(url, '_blank');
        } else {
            alert('Primero realice una búsqueda válida');
        }
    });

    // Inicializar fecha actual por defecto
    const hoy = new Date().toISOString().split('T')[0];
    const haceUnaSemana = new Date();
    haceUnaSemana.setDate(haceUnaSemana.getDate() - 7);
    const fechaPasada = haceUnaSemana.toISOString().split('T')[0];
    
    document.getElementById('fechaInicio').value = fechaPasada;
    document.getElementById('fechaFin').value = hoy;
    
    // Para debug: verificar si estamos en sesión
    console.log('Página cargada. Datos iniciales:', {
        fechaInicio: fechaPasada,
        fechaFin: hoy,
        time: new Date().toISOString()
    });
});
</script>

<!-- Estilos adicionales para tabla más ancha -->
<style>
#tablaReporte th {
    background-color: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.85rem;
    white-space: nowrap;
}

#tablaReporte tbody tr:hover {
    background-color: #f5f5f5;
}

#tablaReporte tbody td {
    font-size: 0.85rem;
    vertical-align: middle;
    padding: 0.5rem;
}

.modal-body {
    max-height: 70vh;
    overflow-y: auto;
}

.table-responsive {
    max-height: 500px;
    overflow-y: auto;
    overflow-x: auto;
}

/* Estilos específicos para columnas */
#tablaReporte tbody td:nth-child(1) { /* ID Dictamen */
    font-weight: 600;
    color: #0d6efd;
}

#tablaReporte tbody td:nth-child(2), /* Fecha */
#tablaReporte tbody td:nth-child(3) { /* Hora */
    font-family: monospace;
    font-size: 0.8rem;
}

#tablaReporte tbody td:nth-child(4) { /* ID Crédito */
    font-weight: 500;
}

#tablaReporte tbody td:nth-child(12) { /* Fuente Ingresos */
    max-width: 120px;
    word-wrap: break-word;
}

#tablaReporte tbody td:nth-child(13) { /* Comentarios */
    max-width: 200px;
    word-wrap: break-word;
}

/* Scroll horizontal suave */
.table-responsive::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Badges para valores específicos */
.badge-tipo-contacto {
    background-color: #e3f2fd;
    color: #1565c0;
}

.badge-plataforma {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.badge-dictamen {
    background-color: #fff3e0;
    color: #ef6c00;
}
</style>