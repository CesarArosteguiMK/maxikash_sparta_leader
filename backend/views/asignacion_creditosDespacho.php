<style>
    .credit-result-box {
        display: none;
    }

    .credit-result-box.show {
        display: block;
    }

    .metric-card {
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Estilos para información del despacho tipo labels */
    .info-compact {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-compact li {
        display: grid;
        grid-template-columns: 2rem 1fr;
        align-items: start;
        gap: 0.75rem;
        padding: 0.625rem 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-compact li:last-child {
        border-bottom: none;
    }

    .info-compact i {
        font-size: 1.1rem;
        color: #696cff;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2rem;
        height: 2rem;
    }

    .info-compact .info-label {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
        min-width: 0;
    }

    .info-compact .info-label span:first-child {
        color: #697a8d;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .info-compact .info-label span:last-child {
        color: #566a7f;
        font-weight: 600;
        text-align: right;
        flex: 1;
        min-width: 0;
        word-wrap: break-word;
    }

    /* Estilos para Select con Búsqueda - Despachos */
    .select-search-wrapper {
        position: relative;
        width: 100%;
    }

    .select-search-display {
        cursor: pointer;
        padding: 0.5rem 1rem;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        background-color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: border-color 0.2s;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #697a8d;
    }

    .select-search-display:hover {
        border-color: #696cff;
    }

    .select-search-display.active {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
    }

    .select-search-arrow {
        transition: transform 0.3s;
        color: #697a8d;
    }

    .select-search-arrow.open {
        transform: rotate(180deg);
    }

    .select-search-dropdown {
        position: absolute;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        background-color: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
        z-index: 1050;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.3s ease, opacity 0.3s ease;
    }

    .select-search-dropdown.show {
        max-height: 400px;
        opacity: 1;
    }

    .select-search-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: none;
        border-bottom: 1px solid #d9dee3;
        outline: none;
        font-size: 0.9375rem;
    }

    .select-search-input:focus {
        background-color: #f8f9fa;
    }

    .select-search-options {
        max-height: 300px;
        overflow-y: auto;
    }

    .select-search-option {
        padding: 0.75rem 1rem;
        cursor: pointer;
        transition: background-color 0.2s;
        font-size: 0.9375rem;
    }

    .select-search-option:hover {
        background-color: #f5f5f9;
    }

    .select-search-option.selected {
        background-color: #696cff;
        color: #fff;
    }

    .select-search-option.no-results {
        padding: 1rem;
        text-align: center;
        color: #999;
        cursor: default;
    }

    .select-search-option.no-results:hover {
        background-color: transparent;
    }
</style>

<!-- Título de la página -->
<h4 class="mb-4">
    <i class="fa-solid fa-briefcase me-2"></i>
    Asignación de Créditos a Despachos de Cobranza
</h4>

<div class="row g-4 mb-4">
    <!-- PANEL IZQUIERDO -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fa-solid fa-user-tie me-2"></i>Datos del Despacho
                </h5>

                <div class="mb-3">
                    <label class="form-label" for="select-despacho">Seleccionar despacho</label>
                    <select id="select-despacho" class="form-select">
                        <option value="">Seleccione un despacho...</option>
                    </select>
                </div>

                <!-- Información del Despacho con diseño tipo labels -->
                <div id="info-despacho-container" style="display: none;">
                    <hr class="my-3">
                    <small class="card-text text-uppercase text-body-secondary small">Información del Despacho</small>
                    <ul class="list-unstyled my-2 py-1 info-compact">
                        <li>
                            <i class="fa fa-user fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Nombre:</span>
                                <span id="info-nombre">-</span>
                            </div>
                        </li>
                        
                        <li>
                            <i class="fa fa-briefcase fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Puesto:</span>
                                <span id="info-puesto">-</span>
                            </div>
                        </li>
                        
                        <li>
                            <i class="fa fa-phone fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Teléfono:</span>
                                <span id="info-telefono">-</span>
                            </div>
                        </li>
                        
                        <li>
                            <i class="fa fa-envelope fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Correo:</span>
                                <span id="info-correo">-</span>
                            </div>
                        </li>
                        
                        <li>
                            <i class="fa fa-map-marker-alt fa-lg text-primary"></i>
                            <div class="info-label">
                                <span class="fw-medium">Dirección:</span>
                                <span id="info-direccion">-</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <hr class="my-4">

                <h5 class="card-title mb-3">
                    <i class="fa-solid fa-comment me-2"></i>Mis comentarios
                </h5>
                
                <div class="mb-3">
                    <textarea id="comentarios-despacho" class="form-control" rows="3" placeholder="Notas internas..."></textarea>
                </div>
                
                <button class="btn btn-primary w-100" id="btn-guardar-comentarios">
                    <i class="fa-solid fa-save me-1"></i>Guardar Comentarios
                </button>

                <hr class="my-4">

                <h5 class="card-title mb-3">
                    <i class="fa-solid fa-chart-line me-2"></i>Métricas
                </h5>
                
                <div class="row g-2">
                    <div class="col-6">
                        <div class="card bg-label-primary metric-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2">
                                        <span class="avatar-initial rounded bg-label-primary">
                                            <i class="fa-solid fa-file-invoice"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="d-block">Créditos asignados</small>
                                        <h6 class="mb-0" id="metrica-creditos">0</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card bg-label-success metric-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2">
                                        <span class="avatar-initial rounded bg-label-success">
                                            <i class="fa-solid fa-dollar-sign"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="d-block">Saldo total</small>
                                        <h6 class="mb-0" id="metrica-saldo">$0.00</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card bg-label-info metric-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2">
                                        <span class="avatar-initial rounded bg-label-info">
                                            <i class="fa-solid fa-percent"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="d-block">Recuperación</small>
                                        <h6 class="mb-0" id="metrica-recuperacion">0%</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-6">
                        <div class="card bg-label-warning metric-card">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar flex-shrink-0 me-2">
                                        <span class="avatar-initial rounded bg-label-warning">
                                            <i class="fa-solid fa-clock"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <small class="d-block">Promedio mora</small>
                                        <h6 class="mb-0" id="metrica-mora">0 días</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PANEL DERECHO -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="fa-solid fa-magnifying-glass me-2"></i>Buscar y asignar crédito
                </h5>

                <div class="alert alert-info mb-4">
                    <i class="fa-solid fa-info-circle me-2"></i>
                    Busque un crédito por su ID y asígnelo al despacho seleccionado
                </div>

                <!-- Filtros -->
                <div class="row justify-content-between mb-3">
                    <div class="col-12">
                        <label class="form-label">Filtro</label>
                        <div class="input-group input-group-merge">
                            <div class="form-check form-check-inline me-3">
                                <input class="form-check-input" type="radio" name="modoBusquedaDespacho" id="modoBusquedaID" value="id" checked>
                                <label class="form-check-label" for="modoBusquedaID">ID de crédito</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulario de búsqueda -->
                <form id="formBusquedaCredito">
                    <div class="row g-3 align-items-end">
                        <!-- ID de Crédito -->
                        <div class="col-md-9" id="divIDCredito">
                            <label for="idCredito" class="form-label">ID de crédito</label>
                            <div class="input-group input-group-merge">
                                <input type="number" class="form-control" id="idCredito" name="idCredito"
                                       placeholder="Ej.: 12345">
                                <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100" id="btn-buscar-credito">
                                <i class="fa-solid fa-search me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Resultado de búsqueda -->
                <div class="card border border-primary credit-result-box mt-4" id="credit-result">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1" style="font-size: 0.875rem;">
                                <div class="d-flex align-items-center mb-1">
                                    <strong class="me-2" id="credit-id">ID Crédito: </strong>
                                    <span class="badge bg-warning" id="credit-mora">0</span>
                                </div>
                                <div class="mb-1"><strong>Nombre:</strong> <span id="credit-nombre">-</span></div>
                                <div class="mb-1"><strong>Dirección:</strong> <span id="credit-direccion" class="text-muted">-</span></div>
                                <div><strong>Saldo:</strong> <span class="text-danger fw-bold" id="credit-saldo">$0.00</span></div>
                            </div>
                            <button class="btn btn-success btn-sm ms-3" id="btn-asignar-credito">
                                <i class="fa-solid fa-check me-1"></i>Asignar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABLA DE CRÉDITOS ASIGNADOS -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa-solid fa-list me-2"></i>Créditos asignados al despacho
        </h5>
        <button class="btn btn-success btn-sm" id="btn-exportar-excel">
            <i class="fa-solid fa-file-excel me-1"></i>Exportar Excel
        </button>
    </div>
    
    <div class="card-datatable table-responsive">
        <table class="table border-top" id="tabla-creditos">
            <thead>
                <tr>
                    <th>ID Crédito</th>
                    <th>Cliente</th>
                    <th>Saldo</th>
                    <th>Días Mora</th>
                    <th>Estado</th>
                    <th>Fecha Asignación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody-creditos">
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="fa-solid fa-info-circle me-2 text-muted"></i>
                        <span class="text-muted">Seleccione un despacho para ver los créditos asignados</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Variables globales
let despachoSeleccionado = null;
let creditoEncontrado = null;
let searchableSelectDespacho;

/**
 * Clase para Select con búsqueda
 */
class SearchableSelect {
    constructor(selectElement) {
        this.select = selectElement;
        this.options = [];
        this.selectedValue = '';
        this.isOpen = false;
        
        this.createWrapper();
        this.attachEvents();
        this.loadOptions();
    }

    createWrapper() {
        // Crear wrapper
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'select-search-wrapper';
        
        // Crear display
        this.display = document.createElement('div');
        this.display.className = 'select-search-display';
        this.display.innerHTML = `
            <span>Seleccione un despacho...</span>
            <i class="fas fa-chevron-down select-search-arrow"></i>
        `;
        
        // Crear dropdown
        this.dropdown = document.createElement('div');
        this.dropdown.className = 'select-search-dropdown';
        this.dropdown.innerHTML = `
            <input type="text" class="select-search-input" placeholder="Buscar despacho...">
            <div class="select-search-options"></div>
        `;
        
        // Agregar elementos
        this.wrapper.appendChild(this.display);
        this.wrapper.appendChild(this.dropdown);
        
        // Insertar después del select y ocultar el select original
        this.select.parentNode.insertBefore(this.wrapper, this.select.nextSibling);
        this.select.style.display = 'none';
        
        // Referencias
        this.searchInput = this.dropdown.querySelector('.select-search-input');
        this.optionsContainer = this.dropdown.querySelector('.select-search-options');
        this.arrow = this.display.querySelector('.select-search-arrow');
    }

    loadOptions() {
        this.options = Array.from(this.select.options)
            .filter(opt => opt.value !== '')
            .map(opt => ({
                value: opt.value,
                text: opt.textContent
            }));
        
        this.renderOptions(this.options);
    }

    renderOptions(filteredOptions) {
        this.optionsContainer.innerHTML = '';
        
        if (filteredOptions.length === 0) {
            const noResults = document.createElement('div');
            noResults.className = 'select-search-option no-results';
            noResults.textContent = 'No se encontraron resultados';
            this.optionsContainer.appendChild(noResults);
            return;
        }
        
        filteredOptions.forEach(option => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'select-search-option';
            optionDiv.textContent = option.text;
            optionDiv.dataset.value = option.value;
            
            if (option.value === this.selectedValue) {
                optionDiv.classList.add('selected');
            }
            
            optionDiv.addEventListener('click', () => {
                this.selectOption(option);
            });
            
            this.optionsContainer.appendChild(optionDiv);
        });
    }

    selectOption(option) {
        this.selectedValue = option.value;
        this.select.value = option.value;
        this.display.querySelector('span').textContent = option.text;
        
        // Disparar evento change en el select original
        const event = new Event('change', { bubbles: true });
        this.select.dispatchEvent(event);
        
        this.close();
    }

    open() {
        this.isOpen = true;
        this.dropdown.classList.add('show');
        this.display.classList.add('active');
        this.arrow.classList.add('open');
        this.searchInput.value = '';
        this.searchInput.focus();
        this.loadOptions();
    }

    close() {
        this.isOpen = false;
        this.dropdown.classList.remove('show');
        this.display.classList.remove('active');
        this.arrow.classList.remove('open');
        this.searchInput.value = '';
    }

    attachEvents() {
        // Click en display
        this.display.addEventListener('click', (e) => {
            e.stopPropagation();
            if (this.isOpen) {
                this.close();
            } else {
                this.open();
            }
        });
        
        // Input de búsqueda
        this.searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase().trim();
            const filtered = this.options.filter(option => 
                option.text.toLowerCase().includes(searchTerm)
            );
            this.renderOptions(filtered);
        });
        
        // Evitar que el click en el dropdown lo cierre
        this.dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
        
        // Cerrar al hacer click fuera
        document.addEventListener('click', () => {
            if (this.isOpen) {
                this.close();
            }
        });
    }

    refresh() {
        this.loadOptions();
        const selectedOption = this.select.options[this.select.selectedIndex];
        if (selectedOption) {
            this.display.querySelector('span').textContent = selectedOption.text;
            this.selectedValue = selectedOption.value;
        } else {
            this.display.querySelector('span').textContent = 'Seleccione un despacho...';
            this.selectedValue = '';
        }
    }
}

// Cargar despachos al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarDespachos();
    
    // Event Listeners
    document.getElementById('select-despacho').addEventListener('change', function() {
        despachoSeleccionado = this.value;
        if (despachoSeleccionado) {
            cargarDatosDespacho(despachoSeleccionado);
            cargarCreditosAsignados(despachoSeleccionado);
        }
    });
    
    // Buscar crédito al enviar formulario
    document.getElementById('formBusquedaCredito').addEventListener('submit', function(e) {
        e.preventDefault();
        buscarCredito();
    });
    
    document.getElementById('btn-asignar-credito').addEventListener('click', asignarCredito);
    document.getElementById('btn-guardar-comentarios').addEventListener('click', guardarComentarios);
    document.getElementById('btn-exportar-excel').addEventListener('click', exportarExcel);
});

// Función para cargar lista de despachos
function cargarDespachos() {
    console.log('🔄 Cargando despachos...');
    fetch('/despachos/obtenerListaDespachos')
        .then(response => {
            console.log('📡 Respuesta recibida:', response);
            return response.json();
        })
        .then(data => {
            console.log('📊 Datos recibidos:', data);
            const select = document.getElementById('select-despacho');
            select.innerHTML = '<option value="">Seleccione un despacho...</option>';
            
            if (data.success && data.despachos && data.despachos.length > 0) {
                console.log(`✅ ${data.despachos.length} despachos encontrados`);
                data.despachos.forEach((despacho, index) => {
                    console.log(`   ${index + 1}. ID Persona: ${despacho.id_persona}, Nombre: ${despacho.nombre_completo}, Puesto: ${despacho.nombre_puesto} (ID: ${despacho.id_puesto})`);
                    const option = document.createElement('option');
                    option.value = despacho.id_persona; // Usamos id_persona como valor
                    option.textContent = `${despacho.nombre_completo} - ${despacho.nombre_puesto}`;
                    select.appendChild(option);
                });
                
                // Inicializar SearchableSelect después de cargar opciones
                if (!searchableSelectDespacho) {
                    searchableSelectDespacho = new SearchableSelect(select);
                } else {
                    searchableSelectDespacho.refresh();
                }
            } else {
                console.warn('⚠️ No se encontraron despachos:', data);
            }
        })
        .catch(error => {
            console.error('❌ Error al cargar despachos:', error);
            Swal.fire('Error', 'No se pudieron cargar los despachos: ' + error.message, 'error');
        });
}

// Función para cargar datos del despacho seleccionado
function cargarDatosDespacho(idPersona) {
    fetch(`/despachos/obtenerDatosDespacho/${idPersona}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar el contenedor de información
                document.getElementById('info-despacho-container').style.display = 'block';
                
                // Llenar los labels con la información
                document.getElementById('info-nombre').textContent = data.datos.nombre_completo || '-';
                document.getElementById('info-puesto').textContent = data.datos.puesto || '-';
                document.getElementById('info-telefono').textContent = data.datos.telefono || '-';
                document.getElementById('info-correo').textContent = data.datos.correo || '-';
                document.getElementById('info-direccion').textContent = data.datos.direccion || '-';
                
                // Cargar comentarios si existen
                document.getElementById('comentarios-despacho').value = data.comentarios || '';
                
                // Cargar métricas
                document.getElementById('metrica-creditos').textContent = data.metricas.creditos_asignados || 0;
                document.getElementById('metrica-saldo').textContent = formatearMoneda(data.metricas.saldo_total || 0);
                document.getElementById('metrica-recuperacion').textContent = (data.metricas.recuperacion || 0) + '%';
                document.getElementById('metrica-mora').textContent = (data.metricas.promedio_mora || 0) + ' días';
            }
        })
        .catch(error => {
            console.error('Error al cargar datos del despacho:', error);
        });
}

// Función para buscar crédito
function buscarCredito() {
    const idCredito = document.getElementById('idCredito').value.trim();
    
    if (!idCredito) {
        Swal.fire('Advertencia', 'Ingrese un ID de crédito', 'warning');
        return;
    }
    
    fetch('/despachos/buscarCredito', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            tipo: 'id_credito',
            valor: idCredito
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.credito) {
            creditoEncontrado = data.credito;
            document.getElementById('credit-id').textContent = `ID CREDITO ${creditoEncontrado.id_credito}`;
            document.getElementById('credit-nombre').textContent = creditoEncontrado.nombre_cliente;
            document.getElementById('credit-direccion').textContent = creditoEncontrado.direccion || 'Sin dirección';
            document.getElementById('credit-saldo').textContent = formatearMoneda(creditoEncontrado.saldo_actual || 0);
            document.getElementById('credit-mora').textContent = `${creditoEncontrado.dias_mora || 0} días`;
            document.getElementById('credit-result').style.display = 'block';
        } else {
            Swal.fire('No encontrado', 'No se encontró el crédito', 'info');
            limpiarBusqueda();
        }
    })
    .catch(error => {
        console.error('Error al buscar crédito:', error);
        Swal.fire('Error', 'Error al buscar el crédito', 'error');
    });
}

// Función para limpiar búsqueda
function limpiarBusqueda() {
    document.getElementById('idCredito').value = '';
    document.getElementById('credit-result').style.display = 'none';
    creditoEncontrado = null;
}

// Función para asignar crédito al despacho
function asignarCredito() {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }
    
    if (!creditoEncontrado) {
        Swal.fire('Advertencia', 'Busque un crédito primero', 'warning');
        return;
    }
    
    Swal.fire({
        title: '¿Confirmar asignación?',
        text: `¿Desea asignar el crédito ${creditoEncontrado.id_credito} al despacho seleccionado?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, asignar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/despachos/asignarCredito', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_despacho: despachoSeleccionado,
                    id_credito: creditoEncontrado.id_credito
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', 'Crédito asignado correctamente', 'success');
                    limpiarBusqueda();
                    cargarCreditosAsignados(despachoSeleccionado);
                    cargarDatosDespacho(despachoSeleccionado); // Actualizar métricas
                } else {
                    Swal.fire('Error', data.message || 'No se pudo asignar el crédito', 'error');
                }
            })
            .catch(error => {
                console.error('Error al asignar crédito:', error);
                Swal.fire('Error', 'Error al asignar el crédito', 'error');
            });
        }
    });
}

// Función para cargar créditos asignados
function cargarCreditosAsignados(idPersona) {
    fetch(`/despachos/obtenerCreditosAsignados/${idPersona}`)
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('tbody-creditos');
            tbody.innerHTML = '';
            
            if (data.success && data.creditos && data.creditos.length > 0) {
                data.creditos.forEach(credito => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${credito.id_credito}</td>
                        <td>${credito.nombre_cliente}</td>
                        <td>${formatearMoneda(credito.saldo)}</td>
                        <td><span class="badge bg-warning">${credito.dias_mora}</span></td>
                        <td><span class="badge bg-label-success">${credito.estado}</span></td>
                        <td>${credito.fecha_asignacion}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="desasignarCredito('${credito.id_credito}')" title="Desasignar crédito">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fa-solid fa-inbox me-2 text-muted"></i>
                            <span class="text-muted">No hay créditos asignados a este despacho</span>
                        </td>
                    </tr>
                `;
            }
        })
        .catch(error => {
            console.error('Error al cargar créditos asignados:', error);
        });
}

// Función para desasignar crédito
function desasignarCredito(idCredito) {
    Swal.fire({
        title: '¿Confirmar desasignación?',
        text: `¿Desea desasignar el crédito ${idCredito}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, desasignar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/despachos/desasignarCredito', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_credito: idCredito
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Éxito', 'Crédito desasignado correctamente', 'success');
                    cargarCreditosAsignados(despachoSeleccionado);
                    cargarDatosDespacho(despachoSeleccionado); // Actualizar métricas
                } else {
                    Swal.fire('Error', data.message || 'No se pudo desasignar el crédito', 'error');
                }
            })
            .catch(error => {
                console.error('Error al desasignar crédito:', error);
                Swal.fire('Error', 'Error al desasignar el crédito', 'error');
            });
        }
    });
}

// Función para guardar comentarios
function guardarComentarios() {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }
    
    const comentarios = document.getElementById('comentarios-despacho').value;
    
    fetch('/despachos/guardarComentarios', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id_despacho: despachoSeleccionado,
            comentarios: comentarios
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', 'Comentarios guardados correctamente', 'success');
        } else {
            Swal.fire('Error', 'No se pudieron guardar los comentarios', 'error');
        }
    })
    .catch(error => {
        console.error('Error al guardar comentarios:', error);
        Swal.fire('Error', 'Error al guardar los comentarios', 'error');
    });
}

// Función para exportar a Excel
function exportarExcel() {
    if (!despachoSeleccionado) {
        Swal.fire('Advertencia', 'Seleccione un despacho primero', 'warning');
        return;
    }
    
    window.location.href = `/despachos/exportarExcel/${despachoSeleccionado}`;
}

// Función auxiliar para formatear moneda
function formatearMoneda(valor) {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(valor);
}
</script>

<?= $script ?? '' ?>
