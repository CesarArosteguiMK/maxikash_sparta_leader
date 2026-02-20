<!-- 
    Vista: gestiones_1_a_7.php
    NIVEL 2 ENTERPRISE: Auto-refresh + Animaciones + WhatsApp
-->
<style>
    /* TU CSS EXACTAMENTE IGUAL - SIN CAMBIOS */
    #tabla-reportes {
        width: 100%;
        table-layout: auto;
        min-width: 2000px;
    }

    #tabla-reportes th {
        position: relative;
        min-width: 120px;
    }

    #tabla-reportes th.resizable {
        position: relative;
    }

    #tabla-reportes .resizer {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        cursor: col-resize;
        user-select: none;
        height: 100%;
        background: transparent;
    }

    #tabla-reportes .resizer:hover {
        background: #4CAF50;
    }

    #tabla-reportes .resizing {
        border-right: 2px solid #4CAF50;
    }

    #tabla-reportes thead th {
        position: sticky;
        top: 0;
        background: #ffffff;
        z-index: 10;
        font-size: 0.75rem;
        white-space: nowrap;
        padding: 0.5rem 0.25rem;
    }

    #tabla-reportes tbody td,
    #tabla-reportes tfoot td {
        font-size: 0.8rem;
        padding: 0.4rem 0.25rem;
    }

    #tabla-reportes td:first-child {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-reportes td:last-child {
        width: 110px;
        text-align: right;
        white-space: nowrap;
    }

    #tabla-reportes small {
        white-space: normal;
        word-break: break-word;
    }

    #tabla-reportes.border-top {
        border-top: none !important;
    }

    #tabla-reportes tbody tr:last-child td {
        border-bottom: none !important;
    }

    #tabla-reportes th:nth-child(n+2):nth-child(-n+10),
    #tabla-reportes td:nth-child(n+2):nth-child(-n+10) {
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
        .form-select, .form-check-input {
            font-size: 0.875rem;
        }
    }

    #maxicash-logo {
        width: 100px;
        height: auto;
    }

    /* ✅ ESTILOS ENTERPRISE PARA ANIMACIONES */
    @keyframes highlight-pulse {
        0% { 
            background-color: #4CAF50;
            transform: scale(1);
        }
        50% { 
            background-color: #66BB6A;
            transform: scale(1.02);
        }
        100% { 
            background-color: transparent;
            transform: scale(1);
        }
    }

    .highlight-change {
        animation: highlight-pulse 1s ease;
        font-weight: bold;
        color: #2E7D32 !important;
    }

    .auto-refresh-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .refresh-status {
        background: white;
        padding: 8px 15px;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .refresh-status.active {
        border-left: 3px solid #4CAF50;
    }

    .refresh-status.paused {
        border-left: 3px solid #FF9800;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .refresh-status i.spinning {
        animation: spin 1s linear infinite;
    }

    /* Badge para WhatsApp */
    .badge-whatsapp {
        background-color: #25D366;
        color: white;
        font-size: 0.7rem;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
    }
</style>

<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Reportes de Gestiones 1 a 7</h4>
            <p class="text-muted small">Indicadores de Gestiones 1 a 7</p>
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
                    <label for="filtro-campana" class="form-label fw-bold">Campaña</label>
                    <select class="form-select" id="filtro-campana">
                        <option value="ASIGNACION_W07_1A7">ASIGNACION_W07_1A7</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Fuera de Zona</label>
                    <div class="d-flex gap-3">
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
            </div>

            <!-- ===================== -->
            <!-- TABLA PRINCIPAL -->
            <!-- ===================== -->

            <div class="card-datatable table-responsive">
                <table id="tabla-reportes" class="dt-responsive table border-top">
                    <thead>
                        <tr>
                            <th>Líderes</th>
                            <th>CURRENT</th>
                            <th>1 A 7 DÍAS</th>
                            <th>SIN GESTIÓN</th>
                            <th>EFICIENCIA</th>
                            <th>TOTAL GENERAL</th>
                            <th>CAMPO</th>
                            <th>TELEFONO</th>
                            <th>WHATSAPP</th>
                            <th>SALDO VENCIDO</th>
                            <th>Fecha Dictamen Más Antigua</th>
                            <th>Fecha Dictamen Más Reciente</th>
                            <th>Estatus Actividad Gestor</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-body">
                        <!-- JS inyectará filas -->
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td id="total-current"><strong>0</strong></td>
                            <td id="total-1a7"><strong>0</strong></td>
                            <td id="total-sin-gestion"><strong>0</strong></td>
                            <td id="total-eficiencia"><strong>0%</strong></td>
                            <td id="total-general"><strong>0</strong></td>
                            <td id="total-campo"><strong>0</strong></td>
                            <td id="total-telefono"><strong>0</strong></td>
                            <td id="total-whatsapp"><strong>0</strong></td>
                            <td id="total-saldo"><strong>$0</strong></td>
                            <td id="total-fecha-antigua"><strong>-</strong></td>
                            <td id="total-fecha-reciente"><strong>-</strong></td>
                            <td id="total-estatus"><strong>-</strong></td>
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

<!-- ✅ INDICADOR DE AUTO-REFRESH (FLOTANTE) -->
<div class="auto-refresh-indicator">
    <div class="refresh-status active" id="refresh-status">
        <i class="fa fa-sync-alt" id="refresh-icon"></i>
        <span id="refresh-text">Actualizando cada 30s</span>
    </div>
    <button class="btn btn-sm btn-outline-primary" id="toggle-refresh">
        <i class="fa fa-pause"></i> Pausar
    </button>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // ==========================================
    // ✅ VARIABLES GLOBALES
    // ==========================================
    let autoRefreshInterval = null;
    let isPaused = false;
    
    // Los datos vienen formateados desde el backend
    let datosActuales = <?php echo json_encode($gestiones ?? []); ?>;
    let totalesActuales = <?php echo json_encode($totales ?? []); ?>;
    
    let lastUpdateTime = new Date();
    const REFRESH_INTERVAL = 30000; // 30 segundos

    // ==========================================
    // ✅ FUNCIONES DE FORMATEO
    // ==========================================
    function cleanNumber(str) {
        if (str === null || str === undefined) return 0;
        if (typeof str === 'number') return str;
        
        const cleaned = String(str).replace(/[$,%]/g, '').replace(/,/g, '');
        const num = parseFloat(cleaned);
        return isNaN(num) ? 0 : num;
    }

    // ==========================================
    // ✅ RENDERIZAR TABLA CON WHATSAPP
    // ==========================================
    function renderTable(data) {
        const tbody = document.getElementById('tabla-body');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="13" class="text-center">No hay datos disponibles</td></tr>';
            return;
        }
        
        data.forEach(row => {
            const tr = document.createElement('tr');
            
            // Badge de estatus
            let badgeClass = 'badge bg-secondary';
            if (row.estatus_gestor === 'Activo') badgeClass = 'badge bg-success';
            if (row.estatus_gestor === 'Inactivo') badgeClass = 'badge bg-warning text-dark';
            
            tr.innerHTML = `
                <td>${escapeHtml(row.lider || 'SIN ASIGNAR')}</td>
                <td data-field="current" data-raw="${row.current || 0}">${formatNumber(row.current || 0)}</td>
                <td data-field="gestiones_1a7" data-raw="${row.gestiones_1a7 || 0}">${formatNumber(row.gestiones_1a7 || 0)}</td>
                <td data-field="sin_gestion" data-raw="${row.sin_gestion || 0}">${formatNumber(row.sin_gestion || 0)}</td>
                <td data-field="eficiencia" data-raw="${cleanNumber(row.eficiencia)}">${formatDecimal(row.eficiencia || 0)}%</td>
                <td data-field="total_general" data-raw="${row.total_general || 0}">${formatNumber(row.total_general || 0)}</td>
                <td data-field="campo" data-raw="${row.campo || 0}">${formatNumber(row.campo || 0)}</td>
                <td data-field="telefono" data-raw="${row.telefono || 0}">${formatNumber(row.telefono || 0)}</td>
                <td data-field="whatsapp" data-raw="${row.whatsapp || 0}">
                    ${row.whatsapp ? `<span class="badge-whatsapp">${formatNumber(row.whatsapp)}</span>` : '0'}
                </td>
                <td data-field="saldo_vencido" data-raw="${cleanNumber(row.saldo_vencido)}">${row.saldo_vencido || '$0'}</td>
                <td>${escapeHtml(row.fecha_dictamen_mas_antigua || '-')}</td>
                <td>${escapeHtml(row.fecha_dictamen_mas_reciente || '-')}</td>
                <td><span class="${badgeClass}">${escapeHtml(row.estatus_gestor || 'Sin actividad')}</span></td>
            `;
            tbody.appendChild(tr);
        });
    }

    // ==========================================
    // ✅ ACTUALIZAR TOTALES CON WHATSAPP
    // ==========================================
    function actualizarTotales(totales) {
        if (!totales) return;
        
        document.getElementById('total-current').innerHTML = `<strong>${totales.total_current || '0'}</strong>`;
        document.getElementById('total-1a7').innerHTML = `<strong>${totales.total_1a7 || '0'}</strong>`;
        document.getElementById('total-sin-gestion').innerHTML = `<strong>${totales.total_sin_gestion || '0'}</strong>`;
        document.getElementById('total-eficiencia').innerHTML = `<strong>${totales.total_eficiencia || '0%'}</strong>`;
        document.getElementById('total-general').innerHTML = `<strong>${totales.total_general || '0'}</strong>`;
        document.getElementById('total-campo').innerHTML = `<strong>${totales.total_campo || '0'}</strong>`;
        document.getElementById('total-telefono').innerHTML = `<strong>${totales.total_telefono || '0'}</strong>`;
        document.getElementById('total-whatsapp').innerHTML = `<strong>${totales.total_whatsapp || '0'}</strong>`;
        document.getElementById('total-saldo').innerHTML = `<strong>${totales.total_saldo || '$0'}</strong>`;
    }

    // ==========================================
    // ✅ ANIMAR CAMBIOS (ACTUALIZADO CON WHATSAPP)
    // ==========================================
    function animarCambio(element, nuevoValorRaw, nuevoValorFormateado, tipo = 'numero') {
        if (!element) return;
        
        element.classList.add('highlight-change');
        
        if (tipo !== 'texto') {
            const valorActual = cleanNumber(element.getAttribute('data-raw') || element.textContent);
            const valorFinal = cleanNumber(nuevoValorRaw);
            
            if (valorActual !== valorFinal && !isNaN(valorActual) && !isNaN(valorFinal)) {
                const range = valorFinal - valorActual;
                const startTime = performance.now();
                const duration = 800;
                
                const animar = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const valorIntermedio = valorActual + (range * eased);
                    
                    if (tipo === 'moneda') {
                        element.innerHTML = `<strong>$${formatNumber(valorIntermedio)}</strong>`;
                    } else if (tipo === 'porcentaje') {
                        element.innerHTML = `<strong>${formatDecimal(valorIntermedio)}%</strong>`;
                    } else {
                        element.innerHTML = `<strong>${formatNumber(Math.round(valorIntermedio))}</strong>`;
                    }
                    
                    if (progress < 1) {
                        requestAnimationFrame(animar);
                    } else {
                        if (tipo === 'whatsapp' && valorFinal > 0) {
                            element.innerHTML = `<span class="badge-whatsapp">${formatNumber(valorFinal)}</span>`;
                        } else {
                            element.innerHTML = `<strong>${nuevoValorFormateado}</strong>`;
                        }
                        element.setAttribute('data-raw', valorFinal);
                    }
                };
                
                requestAnimationFrame(animar);
            } else {
                if (tipo === 'whatsapp' && valorFinal > 0) {
                    element.innerHTML = `<span class="badge-whatsapp">${formatNumber(valorFinal)}</span>`;
                } else {
                    element.innerHTML = `<strong>${nuevoValorFormateado}</strong>`;
                }
            }
        }
        
        setTimeout(() => element.classList.remove('highlight-change'), 1000);
    }

    // Funciones helper para formato
    function formatNumber(num) {
        return new Intl.NumberFormat('es-MX').format(Math.round(num));
    }
    
    function formatDecimal(num) {
        return new Intl.NumberFormat('es-MX', { 
            minimumFractionDigits: 1, 
            maximumFractionDigits: 1 
        }).format(num);
    }

    // ==========================================
    // ✅ APLICAR ACTUALIZACIONES (CON WHATSAPP)
    // ==========================================
    function aplicarActualizaciones(data) {
        if (!data.data || !Array.isArray(data.data)) return;
        
        const filasActuales = document.querySelectorAll('#tabla-body tr');
        
        if (filasActuales.length !== data.data.length) {
            renderTable(data.data);
            if (data.totales) actualizarTotales(data.totales);
            datosActuales = data.data;
            totalesActuales = data.totales;
            return;
        }
        
        data.data.forEach((newRow, index) => {
            if (index >= filasActuales.length) return;
            
            const oldRow = datosActuales[index];
            if (!oldRow) return;
            
            const fila = filasActuales[index];
            
            // Mapeo de campos incluyendo WHATSAPP
            const campos = [
                { field: 'current', tipo: 'numero', selector: 'td[data-field="current"]' },
                { field: 'gestiones_1a7', tipo: 'numero', selector: 'td[data-field="gestiones_1a7"]' },
                { field: 'sin_gestion', tipo: 'numero', selector: 'td[data-field="sin_gestion"]' },
                { field: 'eficiencia', tipo: 'porcentaje', selector: 'td[data-field="eficiencia"]' },
                { field: 'total_general', tipo: 'numero', selector: 'td[data-field="total_general"]' },
                { field: 'campo', tipo: 'numero', selector: 'td[data-field="campo"]' },
                { field: 'telefono', tipo: 'numero', selector: 'td[data-field="telefono"]' },
                { field: 'whatsapp', tipo: 'whatsapp', selector: 'td[data-field="whatsapp"]' },
                { field: 'saldo_vencido', tipo: 'moneda', selector: 'td[data-field="saldo_vencido"]' }
            ];
            
            campos.forEach(({ field, tipo, selector }) => {
                if (oldRow[field] != newRow[field]) {
                    const celda = fila.querySelector(selector);
                    if (celda) {
                        animarCambio(celda, newRow[field], newRow[field], tipo);
                    }
                }
            });
            
            // Actualizar estatus
            if (oldRow.estatus_gestor !== newRow.estatus_gestor) {
                const estatusCell = fila.querySelector('td:last-child');
                if (estatusCell) {
                    let badgeClass = 'badge bg-secondary';
                    if (newRow.estatus_gestor === 'Activo') badgeClass = 'badge bg-success';
                    if (newRow.estatus_gestor === 'Inactivo') badgeClass = 'badge bg-warning text-dark';
                    
                    estatusCell.innerHTML = `<span class="${badgeClass}">${escapeHtml(newRow.estatus_gestor || 'Sin actividad')}</span>`;
                    estatusCell.classList.add('highlight-change');
                    setTimeout(() => estatusCell.classList.remove('highlight-change'), 1000);
                }
            }
        });
        
        // Animar totales (incluyendo whatsapp)
        if (data.totales && totalesActuales) {
            Object.keys(data.totales).forEach(key => {
                if (data.totales[key] != totalesActuales[key]) {
                    const element = document.getElementById(`total-${key.replace('total_', '')}`);
                    if (element) {
                        element.classList.add('highlight-change');
                        
                        let tipo = 'numero';
                        if (key.includes('saldo')) tipo = 'moneda';
                        if (key.includes('eficiencia')) tipo = 'porcentaje';
                        if (key.includes('whatsapp')) tipo = 'whatsapp';
                        
                        animarCambio(element, data.totales[key], data.totales[key], tipo);
                        
                        setTimeout(() => element.classList.remove('highlight-change'), 1000);
                    }
                }
            });
        }
        
        datosActuales = data.data;
        totalesActuales = data.totales;
        lastUpdateTime = new Date();
        actualizarHoraActualizacion();
    }

    // ==========================================
    // ✅ FETCH Y CONTROLES (sin cambios)
    // ==========================================
    async function actualizarDatos() {
        const refreshIcon = document.getElementById('refresh-icon');
        const refreshStatus = document.getElementById('refresh-status');
        
        refreshIcon.classList.add('spinning');
        
        try {
            const response = await fetch('/indicadores/apiGestiones1A7', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });
            
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            const data = await response.json();
            
            if (data.success && data.data) {
                aplicarActualizaciones(data);
                refreshStatus.classList.remove('paused');
            }
            
        } catch (error) {
            console.error("Error:", error);
            refreshStatus.classList.add('paused');
        } finally {
            refreshIcon.classList.remove('spinning');
        }
    }

    function enableAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        autoRefreshInterval = setInterval(actualizarDatos, REFRESH_INTERVAL);
        
        const status = document.getElementById('refresh-status');
        status.classList.remove('paused');
        status.classList.add('active');
        document.getElementById('refresh-text').textContent = 'Actualizando cada 30s';
        isPaused = false;
    }

    function disableAutoRefresh() {
        clearInterval(autoRefreshInterval);
        
        const status = document.getElementById('refresh-status');
        status.classList.remove('active');
        status.classList.add('paused');
        document.getElementById('refresh-text').textContent = 'Actualización pausada';
        isPaused = true;
    }

    function actualizarHoraActualizacion() {
        const timeStr = lastUpdateTime.toLocaleTimeString('es-MX', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('refresh-text').textContent = `Última act: ${timeStr}`;
    }

    function escapeHtml(text) {
        if (!text) return text;
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==========================================
    // ✅ INICIALIZACIÓN
    // ==========================================
    
    Swal.fire({
        title: 'Cargando reporte',
        html: 'Procesando información...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => Swal.showLoading()
    });
    
    // Inicializar columnas redimensionables
    const table = document.getElementById('tabla-reportes');
    if (table) {
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
                
                const mouseMove = (e) => {
                    const width = Math.max(50, startWidth + (e.pageX - startX));
                    th.style.width = width + 'px';
                    
                    const cells = table.querySelectorAll(`tbody td:nth-child(${index + 1}), tfoot td:nth-child(${index + 1})`);
                    cells.forEach(cell => cell.style.width = width + 'px');
                };
                
                const mouseUp = () => {
                    th.classList.remove('resizing');
                    document.removeEventListener('mousemove', mouseMove);
                    document.removeEventListener('mouseup', mouseUp);
                };
                
                document.addEventListener('mousemove', mouseMove);
                document.addEventListener('mouseup', mouseUp);
            });
        });
    }
    
    document.getElementById('toggle-refresh').addEventListener('click', () => {
        const btn = document.getElementById('toggle-refresh');
        if (isPaused) {
            enableAutoRefresh();
            btn.innerHTML = '<i class="fa fa-pause"></i> Pausar';
        } else {
            disableAutoRefresh();
            btn.innerHTML = '<i class="fa fa-play"></i> Reanudar';
        }
    });
    
    if (datosActuales && datosActuales.length > 0) {
        renderTable(datosActuales);
        actualizarTotales(totalesActuales);
        Swal.close();
        enableAutoRefresh();
    } else {
        actualizarDatos().finally(() => Swal.close());
    }
    
    window.addEventListener('beforeunload', () => {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
    });
});
</script>