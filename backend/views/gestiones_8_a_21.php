<!-- 
    Vista: gestiones_8_a_21.php
    Diseño intacto, con lógica JS para jerarquía y drill-down
-->
<style>
    #tabla-8a21 {
        width: 100%;
        table-layout: auto;
        min-width: 2500px;
    }

    #tabla-8a21 th {
        position: relative;
        min-width: 120px;
    }

    #tabla-8a21 th.resizable {
        position: relative;
    }

    #tabla-8a21 .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #tabla-8a21 .resizer:hover {
        background: #4CAF50;
    }

    #tabla-8a21 .resizing {
        border-right: 2px solid #4CAF50;
    }

    #tabla-8a21 thead th {
        position: sticky;
        top: 0;
        background: #e5e7eb;
        z-index: 10;
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #tabla-8a21 tbody td,
    #tabla-8a21 tfoot td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
    }

    #tabla-8a21 td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-8a21 td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #tabla-8a21 small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-8a21.border-top {
        border-top: none !important;
    }

    #tabla-8a21 tbody tr:last-child td {
        border-bottom: none !important;
    }

    #tabla-8a21 th:nth-child(n+2),
    #tabla-8a21 td:nth-child(n+2) {
        text-align: right;
    }

    /* Estilos para jerarquía */
    .nivel-0 {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    
    .nivel-1 {
        padding-left: 25px !important;
        background-color: #ffffff;
    }
    
    .nivel-2 {
        padding-left: 50px !important;
        background-color: #f8f9fa;
    }
    
    .nivel-3 {
        padding-left: 75px !important;
        background-color: #ffffff;
    }
    
    .toggle-icon {
        cursor: pointer;
        margin-right: 8px;
        font-size: 1.1rem;
        display: inline-block;
        width: 20px;
        text-align: center;
        user-select: none;
    }
    
    .toggle-icon:hover {
        color: #4CAF50;
    }
    
    .nodo-nombre {
        cursor: pointer;
    }
    
    .nodo-nombre:hover {
        text-decoration: underline;
        color: #4CAF50;
    }
    
    .tr-territorial,
    .tr-jefe-plaza,
    .tr-gestor {
        transition: all 0.2s ease;
    }
    
    .tr-territorial:hover,
    .tr-jefe-plaza:hover,
    .tr-gestor:hover {
        background-color: #f0f7ff !important;
    }
    
    /* Badges de estatus */
    .badge.bg-success {
        background-color: #28a745 !important;
    }
    
    .badge.bg-warning {
        background-color: #ffc107 !important;
    }
    
    .badge.bg-secondary {
        background-color: #6c757d !important;
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
        .form-select, .form-check-input {
            font-size: 0.875rem;
        }
        
        .nivel-1 {
            padding-left: 15px !important;
        }
        .nivel-2 {
            padding-left: 30px !important;
        }
        .nivel-3 {
            padding-left: 45px !important;
        }
    }

    #maxicash-logo {
        width: 100px;
        height: auto;
    }

    #filtro-contacto {
        max-width: 180px;
        font-size: 0.92rem;
    }
</style>

<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Reportes de Gestiones 8 a 21</h4>
            <p class="text-muted small">Indicadores de Gestiones 8 a 21</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card">
        <div class="card-body">

            <!-- ===================== -->
            <!-- FILTROS SUPERIORES -->
            <!-- ===================== -->

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="filtro-campana" class="form-label fw-bold">Campaña</label>
                    <select class="form-select" id="filtro-campana">
                        <option>Selección múltiple</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold">Fuera de Zona</label>
                    <div class="d-flex flex-column gap-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="false" id="fuera-zona-false">
                            <label class="form-check-label" for="fuera-zona-false">
                                False
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="true" id="fuera-zona-true" checked>
                            <label class="form-check-label" for="fuera-zona-true">
                                True
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label for="filtro-contacto" class="form-label fw-bold">Contacto</label>
                    <select class="form-select" id="filtro-contacto" style="max-width:260px; font-size:0.92rem;">
                        <option value="all">Todas</option>
                    </select>
                </div>
            </div>

            <!-- ===================== -->
            <!-- TABLA PRINCIPAL -->
            <!-- ===================== -->

            <div class="card-datatable table-responsive">
                <table id="tabla-8a21" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>Líderes / Territorial / Jefe Plaza / Gestor</th>
                            <th>CURRENT</th>
                            <th>1 A 7 DÍAS</th>
                            <th>8 A 14 DÍAS</th>
                            <th>15 A 21 DÍAS</th>
                            <th>SIN GESTIÓN</th>
                            <th>Estatus Actividad</th>
                            <th>TOTAL GENERAL</th>
                            <th>EFICIENCIA</th>
                            <th>CAMPO</th>
                            <th>TELEFONO</th>
                            <th>WHATSAPP</th>
                            <th>SALDO VENCIDO</th>
                            <th>Fecha Dictamen Más Antigua</th>
                            <th>Fecha Dictamen Más Reciente</th>
                            <th>Estatus (Detalle)</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body">
                        <!-- JS inyectará filas -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="t-current"><strong>0</strong></td>
                            <td id="t-1a7"><strong>0</strong></td>
                            <td id="t-8a14"><strong>0</strong></td>
                            <td id="t-15a21"><strong>0</strong></td>
                            <td id="t-sin"><strong>0</strong></td>
                            <td><strong>-</strong></td>
                            <td id="t-general"><strong>0</strong></td>
                            <td id="t-eficiencia"><strong>0%</strong></td>
                            <td id="t-campo"><strong>0</strong></td>
                            <td id="t-telefono"><strong>0</strong></td>
                            <td id="t-whatsapp"><strong>0</strong></td>
                            <td id="t-saldo"><strong>$0</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>-</strong></td>
                            <td><strong>-</strong></td>
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

<!-- ========================================== -->
<!-- LÓGICA JS COMPLETA CON JERARQUÍA -->
<!-- ========================================== -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    // ==========================================
    // ✅ MOSTRAR LOADER
    // ==========================================
    Swal.fire({
        title: 'Obteniendo datos del reporte',
        html: 'Por favor espera mientras se procesan los datos...<br><small class="text-muted">Esto puede tardar unos segundos debido al volumen de información</small>',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // ==========================================
    // FUNCIONALIDAD DE REDIMENSIONAR COLUMNAS
    // ==========================================
    const table = document.getElementById('tabla-8a21');
    const headers = table.querySelectorAll('thead th');
    
    headers.forEach((th, index) => {
        th.classList.add('resizable');
        
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

    // ==========================================
    // DATOS INYECTADOS DESDE EL CONTROLADOR
    // ==========================================
    const datosIniciales = <?php echo json_encode($gestiones ?? []); ?>;
    const totalesIniciales = <?php echo json_encode($totales ?? []); ?>;

    // ==========================================
    // FUNCIONES DE FORMATEO
    // ==========================================
    function formatNumber(num) {
        return new Intl.NumberFormat('es-MX').format(num || 0);
    }
    
    function formatDecimal(num) {
        return new Intl.NumberFormat('es-MX', { 
            minimumFractionDigits: 1, 
            maximumFractionDigits: 1 
        }).format(num || 0);
    }
    
    function formatCurrency(num) {
        if (typeof num === 'string' && num.includes('$')) return num;
        const valor = parseFloat(num) || 0;
        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2
        }).format(valor);
    }
    
    function escapeHtml(text) {
        if (!text) return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function getBadgeClass(estatus) {
        if (estatus === 'Activo') return 'badge bg-success';
        if (estatus === 'Inactivo') return 'badge bg-warning text-dark';
        return 'badge bg-secondary';
    }

    // ==========================================
    // ESTADO DE EXPANSIÓN
    // ==========================================
    const expandedState = new Set();

    // ==========================================
    // FUNCIÓN PARA RENDERIZAR JERARQUÍA COMPLETA
    // ==========================================
    function renderJerarquia() {
        const tbody = document.getElementById('tabla-body');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (!datosIniciales || datosIniciales.length === 0) {
            tbody.innerHTML = '<tr><td colspan="16" class="text-center">No hay datos disponibles</td></tr>';
            return;
        }
        
        // Renderizar cada líder y su jerarquía
        datosIniciales.forEach((lider, index) => {
            renderNodo(lider, 0, tbody, `lider-${index}`);
        });
    }

    // ==========================================
    // FUNCIÓN RECURSIVA PARA RENDERIZAR NODOS
    // ==========================================
    function renderNodo(nodo, nivel, contenedor, path) {
        if (!nodo) return;
        
        const tr = document.createElement('tr');
        tr.dataset.path = path;
        tr.dataset.tipo = nodo.tipo || 'desconocido';
        tr.dataset.nivel = nivel;
        
        // Determinar clase según nivel
        tr.className = `tr-${nodo.tipo || 'nodo'}`;
        
        // Construir nombre con indentación e ícono
        let nombreHtml = '';
        
        // Agregar espacios de indentación
        for (let i = 0; i < nivel; i++) {
            nombreHtml += '<span style="display:inline-block; width:25px;"></span>';
        }
        
        // Agregar ícono de expandir/colapsar si tiene hijos
        const tieneHijos = nodo.hijos && nodo.hijos.length > 0;
        const estaExpandido = expandedState.has(path);
        
        if (tieneHijos) {
            nombreHtml += `<span class="toggle-icon" data-path="${path}" data-expand="true">${estaExpandido ? '▼' : '►'}</span>`;
        } else {
            nombreHtml += '<span style="display:inline-block; width:20px;"></span>';
        }
        
        // Nombre del nodo
        nombreHtml += `<span class="nodo-nombre" data-path="${path}" data-toggle="true">${escapeHtml(nodo.nombre || 'SIN NOMBRE')}</span>`;
        
        // Determinar badge class para estatus
        const badgeClass = getBadgeClass(nodo.estatus);
        
        // Construir fila completa
        tr.innerHTML = `
            <td>${nombreHtml}</td>
            <td>${formatNumber(nodo.current)}</td>
            <td>${formatNumber(nodo.gestiones_1a7)}</td>
            <td>${formatNumber(nodo.gestiones_8a14)}</td>
            <td>${formatNumber(nodo.gestiones_15a21)}</td>
            <td>${formatNumber(nodo.sin_gestion)}</td>
            <td><span class="${badgeClass}">${escapeHtml(nodo.estatus || 'Sin actividad')}</span></td>
            <td>${formatNumber(nodo.total_general)}</td>
            <td>${formatDecimal(nodo.eficiencia)}%</td>
            <td>${formatNumber(nodo.campo)}</td>
            <td>${formatNumber(nodo.telefono)}</td>
            <td>${formatNumber(nodo.whatsapp)}</td>
            <td>${formatCurrency(nodo.saldo_vencido)}</td>
            <td>${escapeHtml(nodo.fecha_antigua ? new Date(nodo.fecha_antigua).toLocaleString('es-MX') : '-')}</td>
            <td>${escapeHtml(nodo.fecha_reciente ? new Date(nodo.fecha_reciente).toLocaleString('es-MX') : '-')}</td>
            <td><span class="${badgeClass}">${escapeHtml(nodo.estatus || 'Sin actividad')}</span></td>
        `;
        
        contenedor.appendChild(tr);
        
        // Si está expandido, renderizar hijos
        if (tieneHijos && estaExpandido) {
            nodo.hijos.forEach((hijo, index) => {
                renderNodo(hijo, nivel + 1, contenedor, `${path}-hijo-${index}`);
            });
        }
    }

    // ==========================================
    // MANEJADOR DE CLICKS EN ÍCONOS Y NOMBRES
    // ==========================================
    function handleToggleClick(path) {
        if (expandedState.has(path)) {
            expandedState.delete(path); // Colapsar
        } else {
            expandedState.add(path); // Expandir
        }
        renderJerarquia(); // Re-renderizar
        actualizarTotales(totalesIniciales); // Mantener totales
    }

    // Delegación de eventos para los clics
    document.getElementById('tabla-body').addEventListener('click', (e) => {
        const toggleIcon = e.target.closest('.toggle-icon');
        const nodoNombre = e.target.closest('.nodo-nombre');
        
        if (toggleIcon) {
            const path = toggleIcon.dataset.path;
            if (path) handleToggleClick(path);
        } else if (nodoNombre) {
            const path = nodoNombre.dataset.path;
            if (path) handleToggleClick(path);
        }
    });

    // ==========================================
    // FUNCIÓN PARA ACTUALIZAR TOTALES DEL FOOTER
    // ==========================================
    function actualizarTotales(totales) {
        if (!totales) return;
        
        document.getElementById('t-current').innerHTML = `<strong>${totales.total_current || '0'}</strong>`;
        document.getElementById('t-1a7').innerHTML = `<strong>${totales.total_1a7 || '0'}</strong>`;
        document.getElementById('t-8a14').innerHTML = `<strong>${totales.total_8a14 || '0'}</strong>`;
        document.getElementById('t-15a21').innerHTML = `<strong>${totales.total_15a21 || '0'}</strong>`;
        document.getElementById('t-sin').innerHTML = `<strong>${totales.total_sin_gestion || '0'}</strong>`;
        document.getElementById('t-general').innerHTML = `<strong>${totales.total_general || '0'}</strong>`;
        document.getElementById('t-eficiencia').innerHTML = `<strong>${totales.total_eficiencia || '0%'}</strong>`;
        document.getElementById('t-campo').innerHTML = `<strong>${totales.total_campo || '0'}</strong>`;
        document.getElementById('t-telefono').innerHTML = `<strong>${totales.total_telefono || '0'}</strong>`;
        document.getElementById('t-whatsapp').innerHTML = `<strong>${totales.total_whatsapp || '0'}</strong>`;
        document.getElementById('t-saldo').innerHTML = `<strong>${totales.total_saldo || '$0'}</strong>`;
    }

    // ==========================================
    // FUNCIONES PARA FILTROS (PLACEHOLDER)
    // ==========================================
    function aplicarFiltros() {
        // Aquí iría la lógica para filtrar los datos actuales
    }

    // Inicializar eventos de filtros
    document.getElementById('filtro-campana')?.addEventListener('change', aplicarFiltros);
    document.getElementById('filtro-contacto')?.addEventListener('change', aplicarFiltros);
    document.getElementById('fuera-zona-false')?.addEventListener('change', aplicarFiltros);
    document.getElementById('fuera-zona-true')?.addEventListener('change', aplicarFiltros);

    // ==========================================
    // ✅ CARGA INICIAL
    // ==========================================
    if (datosIniciales && datosIniciales.length > 0) {
        renderJerarquia();
        actualizarTotales(totalesIniciales);
        Swal.close();
    } else {
        document.getElementById('tabla-body').innerHTML = 
            '<tr><td colspan="16" class="text-center">No hay datos disponibles</td></tr>';
        Swal.close();
    }
});
</script>