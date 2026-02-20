<style>
    #tabla-eficiencia {
        width: 100%;
        table-layout: auto;
        min-width: 3000px;
    }

    #tabla-eficiencia th {
        position: relative;
        min-width: 120px;
    }

    #tabla-eficiencia th.resizable {
        position: relative;
    }

    #tabla-eficiencia .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #tabla-eficiencia .resizer:hover {
        background: #4CAF50;
    }

    #tabla-eficiencia .resizing {
        border-right: 2px solid #4CAF50;
    }

    #tabla-eficiencia thead th {
        position: sticky;
        top: 0;
        background: #e5e7eb;
        z-index: 10;
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #tabla-eficiencia tbody td,
    #tabla-eficiencia tfoot td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
    }

    #tabla-eficiencia td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-eficiencia td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #tabla-eficiencia small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-eficiencia.border-top {
        border-top: none !important;
    }

    #tabla-eficiencia tbody tr:last-child td {
        border-bottom: none !important;
    }

    #tabla-eficiencia th:nth-child(n+2),
    #tabla-eficiencia td:nth-child(n+2) {
        text-align: right;
    }

    /* Responsividad para móviles */
    @media (max-width: 576px) {
        .container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        .card-body {
            padding: 1rem;
        }
        .table th, .table td {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .form-label {
            font-size: 0.875rem;
        }
        .form-select {
            font-size: 0.875rem;
        }
    }

    #maxicash-logo {
        width: 100px;
        height: auto;
    }
</style>

<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Reportes de Eficiencia 1 a 7</h4>
            <p class="text-muted small">Indicadores de Eficiencia 1 a 7</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card">
        <div class="card-body">

            <!-- ===================== -->
            <!-- FILTROS SUPERIORES -->
            <!-- ===================== -->

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="filtro-contacto" class="form-label fw-bold">Contacto</label>
                    <select class="form-select" id="filtro-contacto">
                        <option value="all">Todas</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="filtro-campana" class="form-label fw-bold">Campaña</label>
                    <select class="form-select" id="filtro-campana">
                        <option value="ASIGNACION_W07_1A7">ASIGNACION_W07_1A7</option>
                    </select>
                </div>
            </div>

            <!-- ===================== -->
            <!-- TABLA PRINCIPAL -->
            <!-- ===================== -->

            <div class="card-datatable table-responsive">
                <table id="tabla-eficiencia" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>LÍDERES</th>
                            <th>CURRENT</th>
                            <th>1 A 7 DÍAS</th>
                            <th>TOTAL GENERAL</th>
                            <th>SIN GESTIÓN</th>
                            <th>EFICIENCIA</th>
                            <th>Pagos Recibidos</th>
                            <th>Promesa de Pago</th>
                            <th>Negativa de Pago</th>
                            <th>Prestanombre</th>
                            <th>Contacto con Tercero</th>
                            <th>No Contesta la Llamada</th>
                            <th>Sin Contacto</th>
                            <th>Illocalizable</th>
                            <th>Pago No Identificado</th>
                            <th>Cambio de Domicilio</th>
                            <th>Convenio Pago Parcial</th>
                            <th>Moto Recuperada</th>
                            <th>Siniestro</th>
                            <th>Defunción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Render dinámico -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="t-current"><strong>0</strong></td>
                            <td id="t-1a7"><strong>0</strong></td>
                            <td id="t-general"><strong>0</strong></td>
                            <td id="t-sin-gestion"><strong>0</strong></td>
                            <td id="t-eficiencia"><strong>0%</strong></td>
                            <td id="t-pagos"><strong>0</strong></td>
                            <td id="t-promesa"><strong>0</strong></td>
                            <td id="t-negativa"><strong>0</strong></td>
                            <td id="t-prestanombre"><strong>0</strong></td>
                            <td id="t-tercero"><strong>0</strong></td>
                            <td id="t-no-contesta"><strong>0</strong></td>
                            <td id="t-sin-contacto"><strong>0</strong></td>
                            <td id="t-illocalizable"><strong>0</strong></td>
                            <td id="t-pago-no-id"><strong>0</strong></td>
                            <td id="t-cambio-dom"><strong>0</strong></td>
                            <td id="t-convenio"><strong>0</strong></td>
                            <td id="t-moto"><strong>0</strong></td>
                            <td id="t-siniestro"><strong>0</strong></td>
                            <td id="t-defuncion"><strong>0</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>

    <!-- Footer Logo -->
    <div class="row mt-4">
        <div class="col-12 text-end">
            <img src="/assets/img/Logotipo-Maxikash-Outline.webp" alt="Maxicash Logo" id="maxicash-logo">
        </div>
    </div>

</div>

<script>

document.addEventListener("DOMContentLoaded", () => {
    // ==========================================
    // CONFIGURACIÓN INICIAL
    // ==========================================
    const API_ENDPOINT = '/indicadores/apiGestiones8A21'; // Sugerencia: crear este endpoint
    let datosOriginales = [];
    let datosFiltrados = [];
    
    // Elementos del DOM
    const tablaBody = document.querySelector('#tabla-8a21 tbody');
    const filtros = {
        campana: document.getElementById('filtro-campana'),
        fueraZonaFalse: document.getElementById('fuera-zona-false'),
        fueraZonaTrue: document.getElementById('fuera-zona-true'),
        contacto: document.getElementById('filtro-contacto')
    };
    
    // ==========================================
    // 1. CARGA INICIAL DE DATOS
    // ==========================================
    async function cargarDatos() {
        try {
            mostrarLoader();
            
            // Opción A: Usar datos ya inyectados por el controlador
            <?php if (isset($gestiones) && isset($totales)): ?>
            datosOriginales = <?= json_encode($gestiones) ?>;
            actualizarTotales(<?= json_encode($totales) ?>);
            renderizarTabla(datosOriginales);
            poblarFiltros(datosOriginales);
            
            // Opción B: Llamada AJAX (recomendado para actualizaciones)
            // const respuesta = await fetch(API_ENDPOINT);
            // const data = await respuesta.json();
            // if (data.success) {
            //     datosOriginales = data.data;
            //     actualizarTotales(data.totales);
            //     renderizarTabla(datosOriginales);
            //     poblarFiltros(datosOriginales);
            // }
            
            <?php else: ?>
            console.warn('No hay datos disponibles');
            mostrarMensajeSinDatos();
            <?php endif; ?>
            
        } catch (error) {
            console.error('Error cargando datos:', error);
            mostrarError('Error al cargar los datos. Intente nuevamente.');
        } finally {
            ocultarLoader();
        }
    }
    
    // ==========================================
    // 2. RENDERIZADO DE TABLA
    // ==========================================
    function renderizarTabla(datos) {
        if (!datos || datos.length === 0) {
            tablaBody.innerHTML = `
                <tr>
                    <td colspan="16" class="text-center py-4">
                        <div class="text-muted">No hay datos para mostrar</div>
                    </td>
                </tr>
            `;
            return;
        }
        
        let html = '';
        datos.forEach(item => {
            // Determinar clase CSS para el estatus
            const estatusClass = getEstatusClass(item.estatus_gestor);
            
            html += `
                <tr>
                    <td><strong>${item.lider}</strong></td>
                    <td>${formatNumber(item.current)}</td>
                    <td>${formatNumber(item.gestiones_1a7)}</td>
                    <td>${formatNumber(item.gestiones_8a14)}</td>
                    <td>${formatNumber(item.gestiones_15a21)}</td>
                    <td class="${item.sin_gestion > 0 ? 'text-danger fw-bold' : ''}">${formatNumber(item.sin_gestion)}</td>
                    <td>
                        <span class="badge ${estatusClass}">${item.estatus_gestor}</span>
                    </td>
                    <td><strong>${formatNumber(item.total_general)}</strong></td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar ${getEficienciaColor(item.eficiencia)}" 
                                 style="width: ${item.eficiencia}%;">
                                ${item.eficiencia}%
                            </div>
                        </div>
                    </td>
                    <td>${formatNumber(item.campo)}</td>
                    <td>${formatNumber(item.telefono)}</td>
                    <td>${formatNumber(item.whatsapp)}</td>
                    <td>${item.saldo_vencido}</td>
                    <td><small>${item.fecha_dictamen_mas_antigua}</small></td>
                    <td><small>${item.fecha_dictamen_mas_reciente}</small></td>
                    <td>
                        <span class="badge ${estatusClass}">${item.estatus_gestor_detalle}</span>
                    </td>
                </tr>
            `;
        });
        
        tablaBody.innerHTML = html;
        
        // Actualizar contador de registros
        actualizarContadorRegistros(datos.length);
    }
    
    // ==========================================
    // 3. FUNCIONES DE FILTRADO
    // ==========================================
    function aplicarFiltros() {
        datosFiltrados = datosOriginales.filter(item => {
            // Filtro por contacto (simulado)
            const contactoSeleccionado = filtros.contacto.value;
            if (contactoSeleccionado !== 'all') {
                // Aquí implementarías la lógica real de filtrado
                // Por ahora, filtro de ejemplo basado en campo/telefono/whatsapp
                if (contactoSeleccionado === 'campo' && item.campo === 0) return false;
                if (contactoSeleccionado === 'telefono' && item.telefono === 0) return false;
                if (contactoSeleccionado === 'whatsapp' && item.whatsapp === 0) return false;
            }
            
            // Filtro Fuera de Zona (simulado)
            const falseChecked = filtros.fueraZonaFalse.checked;
            const trueChecked = filtros.fueraZonaTrue.checked;
            
            if (!falseChecked && !trueChecked) return false; // Ninguno seleccionado
            // Si ambos están seleccionados, mostrar todos
            // Si solo uno está seleccionado, habría que filtrar según datos reales
            
            return true;
        });
        
        renderizarTabla(datosFiltrados);
        recalcularTotalesFiltrados(datosFiltrados);
    }
    
    // ==========================================
    // 4. RECÁLCULO DE TOTALES (para filtros)
    // ==========================================
    function recalcularTotalesFiltrados(datos) {
        if (!datos || datos.length === 0) {
            document.getElementById('t-current').innerHTML = '<strong>0</strong>';
            document.getElementById('t-1a7').innerHTML = '<strong>0</strong>';
            document.getElementById('t-8a14').innerHTML = '<strong>0</strong>';
            document.getElementById('t-15a21').innerHTML = '<strong>0</strong>';
            document.getElementById('t-sin').innerHTML = '<strong>0</strong>';
            document.getElementById('t-general').innerHTML = '<strong>0</strong>';
            document.getElementById('t-eficiencia').innerHTML = '<strong>0%</strong>';
            document.getElementById('t-campo').innerHTML = '<strong>0</strong>';
            document.getElementById('t-telefono').innerHTML = '<strong>0</strong>';
            document.getElementById('t-whatsapp').innerHTML = '<strong>0</strong>';
            document.getElementById('t-saldo').innerHTML = '<strong>$0</strong>';
            return;
        }
        
        // Sumar todos los valores
        const totales = {
            current: datos.reduce((sum, item) => sum + (parseInt(item.current) || 0), 0),
            gestiones_1a7: datos.reduce((sum, item) => sum + (parseInt(item.gestiones_1a7) || 0), 0),
            gestiones_8a14: datos.reduce((sum, item) => sum + (parseInt(item.gestiones_8a14) || 0), 0),
            gestiones_15a21: datos.reduce((sum, item) => sum + (parseInt(item.gestiones_15a21) || 0), 0),
            sin_gestion: datos.reduce((sum, item) => sum + (parseInt(item.sin_gestion) || 0), 0),
            total_general: datos.reduce((sum, item) => sum + (parseInt(item.total_general) || 0), 0),
            campo: datos.reduce((sum, item) => sum + (parseInt(item.campo) || 0), 0),
            telefono: datos.reduce((sum, item) => sum + (parseInt(item.telefono) || 0), 0),
            whatsapp: datos.reduce((sum, item) => sum + (parseInt(item.whatsapp) || 0), 0),
            saldo: datos.reduce((sum, item) => {
                const saldo = parseFloat(item.saldo_vencido.replace(/[$,]/g, '')) || 0;
                return sum + saldo;
            }, 0)
        };
        
        // Calcular eficiencia global
        const totalBase = totales.gestiones_8a14 + totales.gestiones_15a21;
        const eficienciaGlobal = totalBase > 0 ? ((totales.current * 100) / totalBase).toFixed(1) : 0;
        
        // Actualizar DOM
        document.getElementById('t-current').innerHTML = `<strong>${formatNumber(totales.current)}</strong>`;
        document.getElementById('t-1a7').innerHTML = `<strong>${formatNumber(totales.gestiones_1a7)}</strong>`;
        document.getElementById('t-8a14').innerHTML = `<strong>${formatNumber(totales.gestiones_8a14)}</strong>`;
        document.getElementById('t-15a21').innerHTML = `<strong>${formatNumber(totales.gestiones_15a21)}</strong>`;
        document.getElementById('t-sin').innerHTML = `<strong>${formatNumber(totales.sin_gestion)}</strong>`;
        document.getElementById('t-general').innerHTML = `<strong>${formatNumber(totales.total_general)}</strong>`;
        document.getElementById('t-eficiencia').innerHTML = `<strong>${eficienciaGlobal}%</strong>`;
        document.getElementById('t-campo').innerHTML = `<strong>${formatNumber(totales.campo)}</strong>`;
        document.getElementById('t-telefono').innerHTML = `<strong>${formatNumber(totales.telefono)}</strong>`;
        document.getElementById('t-whatsapp').innerHTML = `<strong>${formatNumber(totales.whatsapp)}</strong>`;
        document.getElementById('t-saldo').innerHTML = `<strong>${formatCurrency(totales.saldo)}</strong>`;
    }
    
    // ==========================================
    // 5. FUNCIONES AUXILIARES
    // ==========================================
    function formatNumber(num) {
        return new Intl.NumberFormat('es-MX').format(num || 0);
    }
    
    function formatCurrency(num) {
        return '$' + new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num || 0);
    }
    
    function getEstatusClass(estatus) {
        switch(estatus) {
            case 'Activo': return 'bg-success';
            case 'Inactivo': return 'bg-warning text-dark';
            case 'Sin actividad': return 'bg-secondary';
            default: return 'bg-light text-dark';
        }
    }
    
    function getEficienciaColor(eficiencia) {
        if (eficiencia >= 70) return 'bg-success';
        if (eficiencia >= 40) return 'bg-warning';
        return 'bg-danger';
    }
    
    function poblarFiltros(datos) {
        // Poblar filtro de contacto con valores únicos
        const contactos = new Set();
        datos.forEach(item => {
            if (item.campo > 0) contactos.add('campo');
            if (item.telefono > 0) contactos.add('telefono');
            if (item.whatsapp > 0) contactos.add('whatsapp');
        });
        
        const selectContacto = filtros.contacto;
        contactos.forEach(contacto => {
            const option = document.createElement('option');
            option.value = contacto;
            option.textContent = contacto.charAt(0).toUpperCase() + contacto.slice(1);
            selectContacto.appendChild(option);
        });
    }
    
    function mostrarLoader() {
        // Implementar spinner de carga
        tablaBody.innerHTML = `
            <tr>
                <td colspan="16" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando datos...</p>
                </td>
            </tr>
        `;
    }
    
    function ocultarLoader() {
        // El loader se reemplaza al renderizar
    }
    
    function mostrarError(mensaje) {
        tablaBody.innerHTML = `
            <tr>
                <td colspan="16" class="text-center py-4">
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        ${mensaje}
                    </div>
                </td>
            </tr>
        `;
    }
    
    function mostrarMensajeSinDatos() {
        tablaBody.innerHTML = `
            <tr>
                <td colspan="16" class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-inbox"></i>
                        No hay datos disponibles
                    </div>
                </td>
            </tr>
        `;
    }
    
    function actualizarContadorRegistros(total) {
        // Opcional: mostrar contador de registros
    }
    
    // ==========================================
    // 6. EVENT LISTENERS
    // ==========================================
    filtros.contacto.addEventListener('change', aplicarFiltros);
    filtros.fueraZonaFalse.addEventListener('change', aplicarFiltros);
    filtros.fueraZonaTrue.addEventListener('change', aplicarFiltros);
    
    // Botón de actualización (opcional)
    const btnActualizar = document.createElement('button');
    btnActualizar.className = 'btn btn-sm btn-outline-primary ms-2';
    btnActualizar.innerHTML = '<i class="bi bi-arrow-repeat"></i> Actualizar';
    btnActualizar.addEventListener('click', cargarDatos);
    document.querySelector('.row.g-3.mb-4').appendChild(btnActualizar);
    
    // ==========================================
    // 7. EXPORTACIÓN A EXCEL (opcional)
    // ==========================================
    function exportarAExcel() {
        const datosExportar = datosFiltrados.length ? datosFiltrados : datosOriginales;
        // Implementar lógica de exportación
    }
    
    // Botón de exportación
    const btnExportar = document.createElement('button');
    btnExportar.className = 'btn btn-sm btn-outline-success ms-2';
    btnExportar.innerHTML = '<i class="bi bi-file-excel"></i> Exportar';
    btnExportar.addEventListener('click', exportarAExcel);
    document.querySelector('.row.g-3.mb-4').appendChild(btnExportar);
    
    // ==========================================
    // INICIALIZACIÓN
    // ==========================================
    cargarDatos();
});

document.addEventListener("DOMContentLoaded", () => {
    // ==========================================
    // FUNCIONALIDAD DE REDIMENSIONAR COLUMNAS
    // ==========================================
    const table = document.getElementById('tabla-eficiencia');
    const headers = table.querySelectorAll('thead th');
    
    headers.forEach((th, index) => {
        th.classList.add('resizable');
        
        // Crear el elemento resizer
        const resizer = document.createElement('div');
        resizer.classList.add('resizer');
        th.appendChild(resizer);
        
        let startX, startWidth;
        
        resizer.addEventListener('mousedown', (e) => {
            startX = e.pageX;
            startWidth = th.offsetWidth;
            
            th.classList.add('resizing');
            
            const mouseMoveHandler = (e) => {
                const width = startWidth + (e.pageX - startX);
                th.style.width = width + 'px';
                
                // Aplicar el mismo ancho a todas las celdas de la columna
                const cells = table.querySelectorAll(`tbody td:nth-child(${index + 1}), tfoot td:nth-child(${index + 1})`);
                cells.forEach(cell => {
                    cell.style.width = width + 'px';
                });
            };
            
            const mouseUpHandler = () => {
                th.classList.remove('resizing');
                document.removeEventListener('mousemove', mouseMoveHandler);
                document.removeEventListener('mouseup', mouseUpHandler);
            };
            
            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
        });
    });

    // Aquí podrás:
    // 1. Cargar datos vía fetch()
    // 2. Filtrar según selects
    // 3. Recalcular totales dinámicamente
    // 4. Renderizar tabla dinámicamente

    // Ejemplo básico: puedes integrar con los datos pasados desde el controlador
    // const data = [/* datos del modelo */];
    // renderTable(data);
});
</script>