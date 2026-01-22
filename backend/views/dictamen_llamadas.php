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

<!-- MODAL REDISEÑADO -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-semibold">Dictamen de llamadas</h5>
                    <small class="text-muted">
                        Consulta, filtra y descarga reportes por rango de fechas
                    </small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <!-- BLOQUE: FILTROS -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-filter me-2"></i>Filtros de búsqueda
                        </h6>

                        <form id="formBuscarReporte" method="POST" action="/Reporteria/BuscarReporte">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha de inicio</label>
                                    <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Fecha de fin</label>
                                    <input type="date" class="form-control" id="fechaFin" name="fechaFin" required>
                                </div>

                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Buscar reportes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- BLOQUE: BÚSQUEDA EN TABLA -->
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body py-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input 
                                type="text"
                                class="form-control"
                                id="barraBusqueda"
                                placeholder="Buscar por cliente, crédito, dictamen, agente…"
                                autocomplete="off"
                            >
                            <button class="btn btn-outline-secondary" id="btnLimpiarBusqueda" type="button">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Resultados encontrados: <strong id="contadorResultados">0</strong>
                        </small>
                    </div>
                </div>

                <!-- BLOQUE: TABLA -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">

                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0" id="tablaReporte">
                                <thead class="table-light">
                                    <tr>
                                        <!-- <th> Id Dictamen </th> -->
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>Contacto</th>
                                        <th>Dictamen</th>
                                        <th>Origen</th>
                                        <th>Agente</th>
                                        <th>Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr id="sinResultados">
                                        <td colspan="14" class="text-center text-muted py-5">
                                            <i class="fas fa-search fa-2x mb-3"></i>
                                            <p>Seleccione un rango de fechas para consultar los reportes</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-file-excel me-1"></i>
                    El reporte se descargará en formato Excel
                </small>

                <div>
                    <button class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <button class="btn btn-success" id="btnDescargarReporte" disabled>
                        <i class="fas fa-download me-2"></i>Descargar reporte
                    </button>
                </div>
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
    
    // 🔥 VARIABLES PARA BÚSQUEDA EN TIEMPO REAL
    let datosCompletos = []; // Array que almacena TODOS los datos del servidor
    const barraBusqueda = document.getElementById('barraBusqueda');
    const btnLimpiar = document.getElementById('btnLimpiarBusqueda');
    const contadorResultados = document.getElementById('contadorResultados');
    
    // Variables para almacenar datos de búsqueda
    let datosBusqueda = {
        fechaInicio: '',
        fechaFin: ''
    };

    // 🔥 FUNCIÓN PARA FILTRAR LA TABLA EN TIEMPO REAL
    function filtrarTabla(termino) {
        // Convertir término a minúsculas para búsqueda insensible a mayúsculas
        const terminoLower = termino.toLowerCase();
        
        // Si el término está vacío, mostrar todos los datos
        if (terminoLower === '') {
            renderizarDatos(datosCompletos);
            return;
        }
        
        // Filtrar los datos completos
        const datosFiltrados = datosCompletos.filter(item => {
            // Buscar en todas las columnas relevantes
            return (

                //(item.id_dictamen && item.id_dictamen.toString().toLowerCase().includes(terminoLower)) ||
                (item.nombre_cliente && item.nombre_cliente.toLowerCase().includes(terminoLower)) ||
                (item.id_credito && item.id_credito.toString().toLowerCase().includes(terminoLower)) ||
                (item.resultado_contacto && item.resultado_contacto.toLowerCase().includes(terminoLower)) ||
                (item.dictamen && item.dictamen.toLowerCase().includes(terminoLower)) ||
                (item.tipo_contacto && item.tipo_contacto.toLowerCase().includes(terminoLower)) ||
                (item.motivo_no_pago && item.motivo_no_pago.toLowerCase().includes(terminoLower)) ||
                (item.tipo_motivo_no_pago && item.tipo_motivo_no_pago.toLowerCase().includes(terminoLower)) ||
                (item.plataforma && item.plataforma.toLowerCase().includes(terminoLower)) ||
                (item.fuente_ingresos && item.fuente_ingresos.toLowerCase().includes(terminoLower)) ||
                (item.comentarios && item.comentarios.toLowerCase().includes(terminoLower)) ||
                (item.fecha_registro && item.fecha_registro.toLowerCase().includes(terminoLower)) ||
                (item.usuario_id && item.usuario_id.toString().toLowerCase().includes(terminoLower))
            );
        });
        
        // Renderizar datos filtrados
        renderizarDatos(datosFiltrados);
    }

    // 🔥 FUNCIÓN PARA RENDERIZAR LOS DATOS EN LA TABLA
    function renderizarDatos(datos) {
    tbody.innerHTML = '';

    if (datos.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-search fa-2x mb-3"></i>
                    <p>No se encontraron resultados</p>
                </td>
            </tr>`;
        contadorResultados.textContent = '0';
        return;
    }

    datos.forEach(item => {
        const fila = document.createElement('tr');
        fila.innerHTML = `
            <!-- FECHA -->
            <td>
                <div class="fw-semibold">${item.fecha_registro || ''}</div>
                <small class="text-muted">${item.hora_registro || ''}</small>
            </td>

            <!-- CLIENTE -->
            <td>
                <div class="fw-semibold">${item.id_credito || ''}</div>
                <small class="text-muted">${item.nombre_cliente || 'N/A'}</small>
            </td>

            <!-- CONTACTO -->
            <td>
                <span class="badge bg-label-primary d-block mb-1">
                    ${item.tipo_contacto || '—'}
                </span>
                <small class="text-muted">
                    ${item.resultado_contacto || '—'}
                </small>
            </td>

            <!-- DICTAMEN -->
            <td>
                <span class="badge bg-label-warning d-block mb-1">
                    ${item.dictamen || '—'}
                </span>
                <small class="text-muted">
                    ${item.motivo_no_pago || '—'}
                </small>
            </td>

            <!-- ORIGEN -->
            <td>
                <span class="badge bg-label-success d-block mb-1">
                    ${item.plataforma || '—'}
                </span>
                <small class="text-muted">
                    ${item.fuente_ingresos || '—'}
                </small>
            </td>

            <!-- AGENTE -->
            <td class="text-center fw-semibold">
                ${item.usuario_id || '—'}
            </td>

            <!-- NOTAS -->
            <td class="text-muted small">
                ${item.comentarios || '—'}
            </td>
        `;
        tbody.appendChild(fila);
    });

    contadorResultados.textContent = datos.length;
}


    // 🔥 EVENTO: Escuchar cambios en la barra de búsqueda (SIN BOTÓN)
    barraBusqueda.addEventListener('input', function(e) {
        const termino = e.target.value;
        filtrarTabla(termino);
    });

    // 🔥 EVENTO: Botón para limpiar la búsqueda
    btnLimpiar.addEventListener('click', function() {
        barraBusqueda.value = '';
        filtrarTabla('');
        barraBusqueda.focus();
    });

    // Manejar envío del formulario de búsqueda
    formBuscar.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        datosBusqueda.fechaInicio = document.getElementById('fechaInicio').value;
        datosBusqueda.fechaFin = document.getElementById('fechaFin').value;
        
        if (!datosBusqueda.fechaInicio || !datosBusqueda.fechaFin) {
            alert('Por favor, seleccione ambas fechas');
            return;
        }
        
        sinResultados.innerHTML = `
            <td colspan="14" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Buscando datos...</p>
            </td>`;
        
        btnDescargar.disabled = true;
        // 🔥 LIMPIAR LA BARRA DE BÚSQUEDA AL HACER UNA NUEVA BÚSQUEDA
        barraBusqueda.value = '';
        
        try {
            const response = await fetch('/EstadoCuenta/buscarReporteDictamen', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    fechaInicio: datosBusqueda.fechaInicio,
                    fechaFin: datosBusqueda.fechaFin
                })
            });

            console.log('Respuesta recibida:', {
                status: response.status,
                statusText: response.statusText,
                redirected: response.redirected,
                url: response.url
            });

            if (response.redirected) {
                console.warn('Redirección detectada a:', response.url);
                alert('Su sesión ha expirado. Redirigiendo al login...');
                window.location.href = '/login';
                return;
            }

            if (response.status === 302) {
                const redirectUrl = response.headers.get('Location');
                console.warn('302 Found - Redirección a:', redirectUrl);
                alert('Sesión expirada. Por favor, inicie sesión nuevamente.');
                window.location.href = redirectUrl || '/login';
                return;
            }

            if (!response.ok) {
                throw new Error(`Error HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            
            if (contentType && contentType.includes('application/json')) {
                const data = await response.json();
                
                console.log('Datos JSON recibidos:', data);
                
                if (data.code === 'SESSION_EXPIRED' || 
                    data.mensaje?.includes('Sesión') || 
                    data.mensaje?.includes('sesión') ||
                    (data.success === false && data.mensaje?.includes('expir'))) {
                    
                    console.error('Error de sesión en JSON:', data.mensaje);
                    alert('Su sesión ha expirado. Redirigiendo al login...');
                    window.location.href = '/login';
                    return;
                }
                
                if (!data.success) {
                    throw new Error(data.mensaje || 'Error en la búsqueda');
                }
                
                tbody.innerHTML = '';
                
                if (!data.data || data.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="14" class="text-center text-muted py-4">
                                <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                <p>No se encontraron reportes para el rango de fechas seleccionado</p>
                            </td>
                        </tr>`;
                    btnDescargar.disabled = true;
                    datosCompletos = [];
                    contadorResultados.textContent = '0';
                    return;
                }
                
                // 🔥 GUARDAR TODOS LOS DATOS EN datosCompletos
                datosCompletos = data.data;
                
                // 🔥 RENDERIZAR LOS DATOS COMPLETOS
                renderizarDatos(datosCompletos);
                
                btnDescargar.disabled = false;
                
            } else {
                const text = await response.text();
                console.error('Respuesta no JSON recibida:', text.substring(0, 500));
                
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
                
                if (text.includes('<!DOCTYPE') || text.includes('<html')) {
                    throw new Error('El servidor devolvió una página HTML en lugar de datos JSON');
                }
                
                try {
                    const data = JSON.parse(text);
                    console.log('JSON parseado manualmente:', data);
                    
                    if (!data.success || !data.data || data.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="14" class="text-center text-muted py-4">
                                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                                    <p>No se encontraron reportes para el rango de fechas seleccionado</p>
                                </td>
                            </tr>`;
                        btnDescargar.disabled = true;
                        datosCompletos = [];
                        contadorResultados.textContent = '0';
                        return;
                    }
                    
                    // 🔥 GUARDAR TODOS LOS DATOS
                    datosCompletos = data.data;
                    renderizarDatos(datosCompletos);
                    btnDescargar.disabled = false;
                    
                } catch (jsonError) {
                    throw new Error('El servidor devolvió un formato no esperado: ' + text.substring(0, 100));
                }
            }
            
        } catch (error) {
            console.error('Error completo al cargar los datos:', error);
            
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
            datosCompletos = [];
            contadorResultados.textContent = '0';
        }
    });

    // Manejar clic en botón de descarga
    btnDescargar.addEventListener('click', function() {
        if (datosBusqueda.fechaInicio && datosBusqueda.fechaFin) {
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
    
    console.log('Página cargada. Datos iniciales:', {
        fechaInicio: fechaPasada,
        fechaFin: hoy,
        time: new Date().toISOString()
    });
});
</script>

<!-- Estilos adicionales para tabla más ancha -->
<style>
/* Modal */
.modal-xl {
    max-width: 95%;
}

/* Tabla */
#tablaReporte th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
}

#tablaReporte td {
    font-size: 0.85rem;
    vertical-align: top;
    padding: 0.5rem;
}

#tablaReporte tbody tr:hover {
    background-color: #f5f7fa;
}

/* Scroll personalizado */
.table-responsive {
    max-height: 500px;
    overflow: auto;
}

/* Texto secundario */
#tablaReporte small {
    font-size: 0.75rem;
}

/* Badges */
#tablaReporte .badge {
    font-size: 0.7rem;
    max-width: 140px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Alineación semántica correcta */
#tablaReporte td:nth-child(1),
#tablaReporte td:nth-child(6) {
    text-align: center;
    font-weight: 600;
    color: #0d6efd;
}

/* Inputs */
#barraBusqueda:focus {
    box-shadow: none;
}

#tablaReporte th,
#tablaReporte td {
    text-align: center;
}



</style>