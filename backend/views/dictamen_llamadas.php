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

            <div class="row gy-3 mb-3">
                <div class="col-12 col-md-8 col-lg-4">
                    <div class="card shadow-none bg-label-primary h-100">
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2">Disponible para descarga diaria</h5>
                                    <p class="text-body w-sm-80 app-academy-xl-100">Consulta, filtra y descarga reportes por rango de fechas</p>
                                </div>
                                <!-- Botón para abrir modal -->
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalReporte">
                                    Descargar dictamen
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

<!-- MODAL REDISEÑADO con DataTables -->
<div class="modal fade" id="modalReporte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-semibold">Dictamen de llamadas</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <!-- BLOQUE: FILTROS -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
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

                <!-- BLOQUE: TABLA con DataTables -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tablaReporte" class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Crédito / Cliente</th>
                                        <th>Contacto</th>
                                        <th>Motivo</th>
                                        <th>Origen / Notas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán dinámicamente -->
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
                    El reporte se descargará en formato Excel (.xlsx)
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

<!-- Script DataTables y funcionalidad -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const formBuscar = document.getElementById('formBuscarReporte');
    const btnDescargar = document.getElementById('btnDescargarReporte');
    let datosCompletos = [];
    let datosBusqueda = { fechaInicio: '', fechaFin: '' };
    
    // 1️⃣ FUNCIÓN PARA OBTENER ÚLTIMO DÍA DEL MES
    function obtenerUltimoDiaMes(fecha = new Date()) {
        const año = fecha.getFullYear();
        const mes = fecha.getMonth() + 1; // getMonth() devuelve 0-11
        return new Date(año, mes, 0).getDate(); // Día 0 del mes siguiente = último día actual
    }
    
    // 2️⃣ FUNCIÓN PARA FORMATO YYYY-MM-DD
    function formatoFechaInput(fecha) {
        const año = fecha.getFullYear();
        const mes = String(fecha.getMonth() + 1).padStart(2, '0');
        const dia = String(fecha.getDate()).padStart(2, '0');
        return `${año}-${mes}-${dia}`;
    }
    
    // 3️⃣ CONFIGURAR LIMITES DE FECHAS
    function configurarLimitesFechas() {
        const hoy = new Date();
        const fechaInicioInput = document.getElementById('fechaInicio');
        const fechaFinInput = document.getElementById('fechaFin');
        
        // Obtener último día del mes actual
        const ultimoDiaMes = obtenerUltimoDiaMes(hoy);
        const ultimoDiaMesFecha = new Date(hoy.getFullYear(), hoy.getMonth(), ultimoDiaMes);
        
        // Configurar límite máximo (último día del mes actual)
        const maxFecha = formatoFechaInput(ultimoDiaMesFecha);
        fechaInicioInput.max = maxFecha;
        fechaFinInput.max = maxFecha;
        
        // Configurar fecha mínima (opcional, puedes ajustar según necesidad)
        const fechaMinima = new Date(2020, 0, 1); // 1 de enero 2020
        fechaInicioInput.min = formatoFechaInput(fechaMinima);
        fechaFinInput.min = formatoFechaInput(fechaMinima);
        
        console.log('Límites configurados:', {
            maxFecha,
            ultimoDiaMes,
            mesActual: hoy.getMonth() + 1
        });
    }
    
    // 4️⃣ VALIDAR FECHAS ANTES DE BUSCAR
    function validarFechas(fechaInicio, fechaFin) {
        const inicio = new Date(fechaInicio);
        const fin = new Date(fechaFin);
        const hoy = new Date();
        const ultimoDiaMes = obtenerUltimoDiaMes(hoy);
        const ultimoDiaMesFecha = new Date(hoy.getFullYear(), hoy.getMonth(), ultimoDiaMes);
        
        // Validación 1: Fecha inicio no mayor que fecha fin
        if (inicio > fin) {
            Swal.fire({
                icon: 'error',
                title: 'Fechas inválidas',
                html: `
                    <div class="text-start">
                        <p>La <strong>fecha de inicio</strong> no puede ser mayor a la <strong>fecha de fin</strong>.</p>
                        <div class="alert alert-warning mt-2 p-2">
                            <i class="fas fa-calendar me-1"></i>
                            <strong>Fecha inicio:</strong> ${fechaInicio}<br>
                            <i class="fas fa-calendar-check me-1"></i>
                            <strong>Fecha fin:</strong> ${fechaFin}
                        </div>
                    </div>
                `,
                confirmButtonText: 'Corregir fechas',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
        
        // Validación 2: No permitir fechas futuras (después del último día del mes actual)
        if (inicio > ultimoDiaMesFecha || fin > ultimoDiaMesFecha) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha fuera de rango',
                html: `
                    <div class="text-start">
                        <p>No se pueden seleccionar fechas después del <strong>${ultimoDiaMes} de ${hoy.toLocaleDateString('es-ES', { month: 'long' })}</strong>.</p>
                        <div class="alert alert-info mt-2 p-2">
                            <i class="fas fa-info-circle me-1"></i>
                            El sistema solo permite consultar reportes hasta el último día del mes actual.
                        </div>
                    </div>
                `,
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
        
        // Validación 3: Fecha no mayor a hoy (opcional, si quieres evitar fechas futuras)
        const hoySinHora = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());
        if (inicio > hoySinHora || fin > hoySinHora) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha futura',
                text: 'No se pueden seleccionar fechas futuras.',
                confirmButtonText: 'Corregir',
                confirmButtonColor: '#3085d6'
            });
            return false;
        }
        
        return true;
    }
    
    // 5️⃣ INICIALIZAR DATATABLE (sin cambios)
    let tablaReporte = null;
    
    function inicializarDataTable() {
    if ($.fn.DataTable.isDataTable('#tablaReporte')) {
        $('#tablaReporte').DataTable().destroy();
    }
    
    tablaReporte = $('#tablaReporte').DataTable({
        data: [],
        columns: [
            { 
                data: 'fecha',
                className: 'text-center',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="me-2">
                                    <i class="fa fa-calendar text-muted"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-semibold">${row.fecha_registro || 'N/A'}</div>
                                    <small class="text-muted">
                                        <i class="fa fa-clock-o me-1"></i>
                                        ${row.hora_registro || 'Sin hora'}
                                    </small>
                                </div>
                            </div>
                        `;
                    }
                    return row.fecha_registro || '';
                }
            },
            { 
                data: 'credito_cliente',
                className: 'text-center',
                render: function(data, type, row) {
                    if (type === 'display') {
                        return `
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="me-2">
                                    <i class="fa fa-id-card text-muted"></i>
                                </div>
                                <div class="text-start">
                                    <div class="fw-semibold">
                                        <i class="fa fa-hashtag me-1 fa-sm text-muted"></i>
                                        ${row.id_credito || '—'}
                                    </div>
                                    <small class="text-muted">
                                        <i class="fa fa-user me-1"></i>
                                        ${row.nombre_cliente || 'N/A'}
                                    </small>
                                </div>
                            </div>
                        `;
                    }
                    return row.id_credito || '';
                }
            },
            { 
                data: 'contacto',
                className: 'text-center',
                render: function(data, type, row) {
                    // Determinar icono según tipo de contacto
                    let iconoContacto = 'fa-phone';
                    
                    if (row.tipo_contacto?.toLowerCase().includes('correo') || 
                        row.tipo_contacto?.toLowerCase().includes('email')) {
                        iconoContacto = 'fa-envelope';
                    } else if (row.tipo_contacto?.toLowerCase().includes('whatsapp') || 
                               row.tipo_contacto?.toLowerCase().includes('mensaje')) {
                        iconoContacto = 'fa-comments';
                    } else if (row.tipo_contacto?.toLowerCase().includes('personal')) {
                        iconoContacto = 'fa-user';
                    }
                    
                    // Determinar icono según dictamen
                    let iconoDictamen = 'fa-question-circle';
                    let dictamenText = row.dictamen || '';
                    
                    if (dictamenText.toLowerCase().includes('pago') || 
                        dictamenText.toLowerCase().includes('positivo') ||
                        dictamenText.toLowerCase().includes('favorable')) {
                        iconoDictamen = 'fa-check-circle';
                    } else if (dictamenText.toLowerCase().includes('no pago') || 
                               dictamenText.toLowerCase().includes('negativo') ||
                               dictamenText.toLowerCase().includes('desfavorable')) {
                        iconoDictamen = 'fa-times-circle';
                    } else if (dictamenText.toLowerCase().includes('pendiente') || 
                               dictamenText.toLowerCase().includes('espera')) {
                        iconoDictamen = 'fa-clock-o';
                    }
                    
                    if (type === 'display') {
                        return `
                            <div class="d-flex align-items-start">
                                <div class="me-2">
                                    <i class="fa ${iconoContacto} text-muted"></i>
                                </div>
                                <div class="text-start">
                                    <span class="badge bg-info mb-1">${row.tipo_contacto || '—'}</span>
                                    <div class="small mb-1">
                                        <i class="fa fa-comment me-1 text-muted"></i>
                                        ${row.resultado_contacto || '—'}
                                    </div>
                                    <div class="fw-semibold">
                                        <i class="fa ${iconoDictamen} text-muted me-1"></i>
                                        ${dictamenText}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    return row.tipo_contacto || '';
                }
            },
            { 
                data: 'motivo',
                className: 'text-center',
                render: function(data, type, row) {
                    // Determinar icono según motivo
                    let iconoMotivo = 'fa-exclamation-circle';
                    let motivoText = row.motivo_no_pago || '';
                    
                    if (motivoText.toLowerCase().includes('económico') || 
                        motivoText.toLowerCase().includes('dinero') ||
                        motivoText.toLowerCase().includes('financiero')) {
                        iconoMotivo = 'fa-money';
                    } else if (motivoText.toLowerCase().includes('olvido') || 
                               motivoText.toLowerCase().includes('descuido')) {
                        iconoMotivo = 'fa-bell-slash';
                    } else if (motivoText.toLowerCase().includes('laboral') || 
                               motivoText.toLowerCase().includes('empleo') ||
                               motivoText.toLowerCase().includes('trabajo')) {
                        iconoMotivo = 'fa-briefcase';
                    } else if (motivoText.toLowerCase().includes('salud') ||
                               motivoText.toLowerCase().includes('enferm')) {
                        iconoMotivo = 'fa-heartbeat';
                    } else if (motivoText.toLowerCase().includes('familiar')) {
                        iconoMotivo = 'fa-users';
                    }
                    
                    if (type === 'display') {
                        return `
                            <div class="d-flex align-items-start">
                                <div class="me-2">
                                    <i class="fa ${iconoMotivo} text-muted"></i>
                                </div>
                                <div class="text-start">
                                    <div class="mb-1">${motivoText || 'N/A'}</div>
                                    <small class="text-muted">
                                        <i class="fa fa-tag me-1"></i>
                                        ${row.tipo_motivo_no_pago || ''}
                                    </small>
                                </div>
                            </div>
                        `;
                    }
                    return motivoText;
                }
            },
            { 
                data: 'origen_notas',
                className: 'text-center',
                render: function(data, type, row) {
                    // Determinar icono según plataforma
                    let iconoPlataforma = 'fa-desktop';
                    let plataformaText = row.plataforma || '';
                    
                    if (plataformaText.toLowerCase().includes('móvil') || 
                        plataformaText.toLowerCase().includes('mobile') ||
                        plataformaText.toLowerCase().includes('celular')) {
                        iconoPlataforma = 'fa-mobile';
                    } else if (plataformaText.toLowerCase().includes('tablet')) {
                        iconoPlataforma = 'fa-tablet';
                    } else if (plataformaText.toLowerCase().includes('web') ||
                               plataformaText.toLowerCase().includes('online')) {
                        iconoPlataforma = 'fa-globe';
                    } else if (plataformaText.toLowerCase().includes('teléfono') ||
                               plataformaText.toLowerCase().includes('telefono')) {
                        iconoPlataforma = 'fa-phone';
                    }
                    
                    // Determinar icono para fuente de ingresos
                    let iconoIngresos = 'fa-money';
                    let ingresosText = row.fuente_ingresos || '';
                    
                    if (ingresosText.toLowerCase().includes('negocio') ||
                        ingresosText.toLowerCase().includes('emprendimiento')) {
                        iconoIngresos = 'fa-briefcase';
                    } else if (ingresosText.toLowerCase().includes('empleo') ||
                               ingresosText.toLowerCase().includes('trabajo')) {
                        iconoIngresos = 'fa-building';
                    } else if (ingresosText.toLowerCase().includes('independiente') ||
                               ingresosText.toLowerCase().includes('freelance')) {
                        iconoIngresos = 'fa-user-md';
                    }
                    
                    if (type === 'display') {
                        return `
                            <div class="d-flex align-items-start">
                                <div class="me-2">
                                    <i class="fa ${iconoPlataforma} text-muted"></i>
                                </div>
                                <div class="text-start">
                                    <span class="badge bg-secondary mb-1">${plataformaText || '—'}</span>
                                    <div class="small mb-1">
                                        <i class="fa ${iconoIngresos} me-1 text-muted"></i>
                                        ${ingresosText || 'No especificado'}
                                    </div>
                                    <small class="text-muted">
                                        <i class="fa fa-sticky-note me-1"></i>
                                        ${row.comentarios || 'Sin comentarios'}
                                    </small>
                                </div>
                            </div>
                        `;
                    }
                    return plataformaText;
                }
            }
        ],
        pageLength: 10,
        pagingType: 'full_numbers',
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json',
            paginate: {
                first: '«',
                previous: '‹',
                next: '›',
                last: '»'
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        responsive: true,
        destroy: true
    });
}
    
    // 6️⃣ FUNCIÓN PARA CARGAR DATOS (sin cambios)
    function cargarDatosReporte() {
        if (!datosBusqueda.fechaInicio || !datosBusqueda.fechaFin) {
            return;
        }
        
        // Mostrar loading
        tablaReporte.clear().draw();
        
        http.request({
            endpoint: "/EstadoCuenta/buscarReporteDictamen",
            method: "POST",
            data: {
                fechaInicio: datosBusqueda.fechaInicio,
                fechaFin: datosBusqueda.fechaFin
            },
            onSuccess: (resp) => {
                console.log('Datos recibidos:', resp);
                
                if (resp.code === 'SESSION_EXPIRED' || 
                    resp.mensaje?.includes('Sesión') || 
                    resp.mensaje?.includes('sesión') ||
                    (resp.success === false && resp.mensaje?.includes('expir'))) {
                    
                    console.error('Error de sesión:', resp.mensaje);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sesión expirada',
                        text: 'Por favor, inicie sesión nuevamente',
                        confirmButtonText: 'Ir al login'
                    }).then(() => {
                        window.location.href = '/login';
                    });
                    return;
                }
                
                if (!resp.success || !resp.data || resp.data.length === 0) {
                    tablaReporte.clear().draw();
                    btnDescargar.disabled = true;
                    
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin resultados',
                        text: 'No se encontraron reportes para el rango de fechas seleccionado',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }
                
                // Guardar datos completos
                datosCompletos = resp.data;
                
                // Actualizar DataTable
                const datosFormateados = resp.data.map(item => ({
                    ...item,
                    fecha: item.fecha_registro,
                    credito_cliente: item.id_credito,
                    contacto: item.tipo_contacto,
                    motivo: item.motivo_no_pago,
                    origen_notas: item.plataforma
                }));
                
                tablaReporte.clear().rows.add(datosFormateados).draw();
                btnDescargar.disabled = false;
                
                // Mostrar éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Búsqueda exitosa',
                    text: `Se encontraron ${resp.data.length} registros`,
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            onError: (error) => {
                console.error('Error al cargar datos:', error);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'Error al cargar los datos. Intente nuevamente.',
                    confirmButtonText: 'Reintentar'
                }).then(() => {
                    cargarDatosReporte();
                });
            }
        });
    }
    
    // 7️⃣ MANEJAR ENVÍO DEL FORMULARIO (CON VALIDACIONES)
    formBuscar.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        datosBusqueda.fechaInicio = document.getElementById('fechaInicio').value;
        datosBusqueda.fechaFin = document.getElementById('fechaFin').value;
        
        // Validar que se hayan seleccionado ambas fechas
        if (!datosBusqueda.fechaInicio || !datosBusqueda.fechaFin) {
            Swal.fire({
                icon: 'warning',
                title: 'Fechas requeridas',
                text: 'Por favor, seleccione ambas fechas',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }
        
        // Validar fechas
        if (!validarFechas(datosBusqueda.fechaInicio, datosBusqueda.fechaFin)) {
            return;
        }
        
        // Inicializar DataTable si no está inicializado
        if (!tablaReporte) {
            inicializarDataTable();
        }
        
        // Mostrar loading
        Swal.fire({
            title: 'Buscando reportes...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Cargar datos
        cargarDatosReporte();
    });
    
    // 8️⃣ MANEJAR DESCARGA (sin cambios)
    btnDescargar.addEventListener('click', function() {
        if (datosBusqueda.fechaInicio && datosBusqueda.fechaFin) {
            const url = `/EstadoCuenta/descargarReporteDictamen?fechaInicio=${encodeURIComponent(datosBusqueda.fechaInicio)}&fechaFin=${encodeURIComponent(datosBusqueda.fechaFin)}`;
            console.log('Descargando desde:', url);
            window.open(url, '_blank');
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Búsqueda requerida',
                text: 'Primero realice una búsqueda válida',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
    
    // 9️⃣ VALIDACIÓN EN TIEMPO REAL DE FECHAS
    function configurarValidacionTiempoReal() {
        const fechaInicioInput = document.getElementById('fechaInicio');
        const fechaFinInput = document.getElementById('fechaFin');
        
        // Cuando cambia fecha de inicio, ajustar fecha fin mínima
        fechaInicioInput.addEventListener('change', function() {
            if (this.value) {
                fechaFinInput.min = this.value;
                
                // Si fecha fin es menor que fecha inicio, resetear
                if (fechaFinInput.value && fechaFinInput.value < this.value) {
                    fechaFinInput.value = '';
                    Swal.fire({
                        icon: 'info',
                        title: 'Fecha ajustada',
                        text: 'La fecha de fin se ha ajustado para no ser menor que la fecha de inicio',
                        timer: 10500,
                        showConfirmButton: true
                    });
                }
            }
        });
        
        // Cuando cambia fecha fin, validar
        fechaFinInput.addEventListener('change', function() {
            if (fechaInicioInput.value && this.value) {
                if (this.value < fechaInicioInput.value) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Fecha inválida',
                        text: 'La fecha de fin no puede ser menor que la fecha de inicio',
                        confirmButtonText: 'Corregir'
                    }).then(() => {
                        this.value = '';
                        this.focus();
                    });
                }
            }
        });
    }
    
    // 🔟 INICIALIZACIÓN AL ABRIR MODAL
    $('#modalReporte').on('shown.bs.modal', function () {
        // Configurar límites de fechas
        configurarLimitesFechas();
        
        // Configurar validación en tiempo real
        configurarValidacionTiempoReal();
        
        // Inicializar DataTable la primera vez
        if (!tablaReporte) {
            inicializarDataTable();
        }
        
        // Establecer fechas por defecto (últimos 7 días)
        const hoy = new Date();
        const haceUnaSemana = new Date();
        haceUnaSemana.setDate(haceUnaSemana.getDate() - 7);
        
        // Ajustar para no pasar del último día del mes
        const ultimoDiaMes = obtenerUltimoDiaMes(hoy);
        const ultimoDiaMesFecha = new Date(hoy.getFullYear(), hoy.getMonth(), ultimoDiaMes);
        
        // Si hoy es después del último día del mes (por seguridad)
        const fechaFin = hoy <= ultimoDiaMesFecha ? hoy : ultimoDiaMesFecha;
        
        document.getElementById('fechaInicio').value = formatoFechaInput(haceUnaSemana);
        document.getElementById('fechaFin').value = formatoFechaInput(fechaFin);
        
        console.log('Fechas por defecto configuradas:', {
            inicio: formatoFechaInput(haceUnaSemana),
            fin: formatoFechaInput(fechaFin),
            ultimoDiaMes,
            mesActual: hoy.getMonth() + 1
        });
    });
    
    // 1️⃣1️⃣ LIMPIAR AL CERRAR MODAL
    $('#modalReporte').on('hidden.bs.modal', function () {
        // Limpiar búsqueda pero mantener DataTable configurado
        btnDescargar.disabled = true;
        if (tablaReporte) {
            tablaReporte.clear().draw();
        }
    });
});
</script>

<!-- Estilos para DataTables -->
<style>
/* Modal */
.modal-xl {
    max-width: 95%;
}

/* DataTables customization */
#tablaReporte_wrapper .dataTables_filter {
    float: right;
    margin-bottom: 1rem;
}

#tablaReporte_wrapper .dataTables_filter input {
    margin-left: 0.5rem;
    display: inline-block;
    width: auto;
}

#tablaReporte_wrapper .dataTables_length {
    float: left;
}

#tablaReporte_wrapper .dataTables_info {
    padding-top: 0.85em;
    white-space: nowrap;
}

#tablaReporte_wrapper .dataTables_paginate {
    margin-top: 0.5rem;
}

/* Tabla estilos */
#tablaReporte th {
    position: sticky;
    top: 0;
    background: #f8f9fa;
    font-size: 0.85rem;
    font-weight: 600;
    white-space: nowrap;
    z-index: 10;
}

#tablaReporte td {
    font-size: 0.85rem;
    vertical-align: top;
    padding: 0.75rem;
}

#tablaReporte tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
}

/* Scroll personalizado */
.table-responsive {
    max-height: 500px;
    overflow: auto;
}

/* Badges */
#tablaReporte .badge {
    font-size: 0.7rem;
    padding: 0.25em 0.6em;
    max-width: 140px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Alineación */
#tablaReporte td {
    text-align: center;
}

/* Paginación personalizada */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border: 1px solid #dee2e6 !important;
    margin-left: 2px !important;
    border-radius: 0.25rem !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #0d6efd !important;
    color: white !important;
    border-color: #0d6efd !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #e9ecef !important;
    color: #0d6efd !important;
}
</style>