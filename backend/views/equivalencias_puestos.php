<div class="card">
    <div class="row g-0 align-items-center">
        <!-- Texto -->
        <div class="col-12 col-md-8">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3">Equivalencias de Puestos</h5>
                <p class="mb-6">
                    Gestiona y asigna equivalencias entre puestos de diferentes departamentos. 
                    Arrastra los puestos disponibles hacia la lista de equivalencias para crear relaciones.
                </p>
            </div>
        </div>

        <!-- Imagen -->
        <div class="col-12 col-md-4">
            <div class="card-body ps-md-2 pe-5 text-end">
                <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                     class="img-fluid scaleX-n1-rtl"
                     alt="Equivalencias de Puestos">
            </div>
        </div>
    </div>
</div>

<!-- SECCIÓN DRAG & DROP PARA EQUIVALENCIAS DE PUESTOS -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-exchange-alt me-2"></i>Asignar Equivalencias de Puestos
        </h5>
        <p class="text-muted small mb-0">Arrastra los puestos desde "Puestos Disponibles" hacia "Puestos Equivalentes" para crear relaciones</p>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <!-- Lista de Puestos Equivalentes (Destino) -->
            <div class="col-md-6">
                <div class="card shadow-sm border-success">
                    <div class="card-header bg-label-success d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><i class="fas fa-link me-2"></i>Puestos Equivalentes</h6>
                            <small class="text-muted">Puestos con equivalencia asignada</small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" id="btnLimpiarEquivalencias" title="Limpiar todas las equivalencias">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <div class="card-body p-2">
                        <ul id="lista-equivalentes" class="list-group list-group-flush min-height-300">
                            <li class="list-group-item text-center text-muted py-5 empty-state">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No hay equivalencias asignadas</p>
                                <small>Arrastra puestos desde la lista de disponibles</small>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer bg-light">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Total de equivalencias: <strong id="contador-equivalencias">0</strong>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Lista de Puestos Disponibles (Origen) -->
            <div class="col-md-6">
                <div class="card shadow-sm border-primary">
                    <div class="card-header bg-label-primary">
                        <h6 class="mb-0"><i class="fas fa-briefcase me-2"></i>Puestos Disponibles</h6>
                        <small class="text-muted">← Arrastra para crear equivalencia</small>
                    </div>
                    <div class="card-body p-2">
                        <ul id="lista-puestos" class="list-group list-group-flush">
                            <li class="list-group-item d-flex align-items-center py-3 cursor-move" data-puesto-id="1">
                                <div class="avatar-wrapper me-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-info">
                                            <i class="fas fa-user-tie"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Gerente de Cobranza</strong>
                                    <br><small class="text-muted">Departamento: Cobranza</small>
                                </div>
                                <i class="fas fa-grip-vertical text-muted"></i>
                            </li>
                            <li class="list-group-item d-flex align-items-center py-3 cursor-move" data-puesto-id="2">
                                <div class="avatar-wrapper me-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-success">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Gestor de Call Center</strong>
                                    <br><small class="text-muted">Departamento: Call Center</small>
                                </div>
                                <i class="fas fa-grip-vertical text-muted"></i>
                            </li>
                            <li class="list-group-item d-flex align-items-center py-3 cursor-move" data-puesto-id="3">
                                <div class="avatar-wrapper me-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-warning">
                                            <i class="fas fa-headset"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Supervisor de Atención</strong>
                                    <br><small class="text-muted">Departamento: Atención al Cliente</small>
                                </div>
                                <i class="fas fa-grip-vertical text-muted"></i>
                            </li>
                            <li class="list-group-item d-flex align-items-center py-3 cursor-move" data-puesto-id="4">
                                <div class="avatar-wrapper me-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-danger">
                                            <i class="fas fa-chart-line"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Analista de Cobranza</strong>
                                    <br><small class="text-muted">Departamento: Análisis</small>
                                </div>
                                <i class="fas fa-grip-vertical text-muted"></i>
                            </li>
                            <li class="list-group-item d-flex align-items-center py-3 cursor-move" data-puesto-id="5">
                                <div class="avatar-wrapper me-3">
                                    <div class="avatar avatar-sm">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            <i class="fas fa-users"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong>Coordinador de Equipo</strong>
                                    <br><small class="text-muted">Departamento: Operaciones</small>
                                </div>
                                <i class="fas fa-grip-vertical text-muted"></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Botón para guardar equivalencias -->
<div class="card mt-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Guardar Equivalencias</h6>
                <small class="text-muted">Las equivalencias se guardarán en la base de datos</small>
            </div>
            <button class="btn btn-primary" id="btnGuardarEquivalencias" disabled>
                <i class="fas fa-save me-2"></i>Guardar Equivalencias
            </button>
        </div>
    </div>
</div>

<!-- Script de funcionalidad -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // 🎯 DRAG & DROP PARA EQUIVALENCIAS
    // ==========================================
    
    const btnGuardar = document.getElementById('btnGuardarEquivalencias');
    
    // Función para actualizar contador y estado vacío
    function actualizarContadorEquivalencias() {
        const listaEquivalentes = document.getElementById('lista-equivalentes');
        const contador = document.getElementById('contador-equivalencias');
        const items = listaEquivalentes.querySelectorAll('li:not(.empty-state)');
        const emptyState = listaEquivalentes.querySelector('.empty-state');
        
        contador.textContent = items.length;
        
        // Habilitar/deshabilitar botón guardar
        btnGuardar.disabled = items.length === 0;
        
        // Mostrar/ocultar mensaje de lista vacía
        if (items.length === 0) {
            if (!emptyState) {
                listaEquivalentes.innerHTML = `
                    <li class="list-group-item text-center text-muted py-5 empty-state">
                        <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                        <p class="mb-0">No hay equivalencias asignadas</p>
                        <small>Arrastra puestos desde la lista de disponibles</small>
                    </li>
                `;
            }
        } else {
            if (emptyState) {
                emptyState.remove();
            }
        }
    }
    
    // Inicializar SortableJS en la lista de puestos disponibles (origen - clonado)
    new Sortable(document.getElementById('lista-puestos'), {
        group: {
            name: 'equivalencias',
            pull: 'clone',  // Permite clonar elementos
            put: false      // No permite recibir elementos
        },
        animation: 150,
        sort: false,        // No permite reordenar en esta lista
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag'
    });
    
    // Inicializar SortableJS en la lista de equivalentes (destino - recibe clones)
    new Sortable(document.getElementById('lista-equivalentes'), {
        group: {
            name: 'equivalencias',
            pull: true,
            put: true       // Permite recibir elementos
        },
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        
        // Filtrar el elemento empty-state para que no sea arrastrable
        filter: '.empty-state',
        
        // Evento cuando se añade un elemento
        onAdd: function(evt) {
            console.log('Equivalencia creada:', evt.item);
            actualizarContadorEquivalencias();
            
            // Agregar botón de eliminar a cada item clonado
            const btnEliminar = document.createElement('button');
            btnEliminar.className = 'btn btn-sm btn-outline-danger ms-2';
            btnEliminar.innerHTML = '<i class="fas fa-times"></i>';
            btnEliminar.title = 'Eliminar equivalencia';
            btnEliminar.onclick = function() {
                Swal.fire({
                    title: '¿Eliminar equivalencia?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        evt.item.remove();
                        actualizarContadorEquivalencias();
                    }
                });
            };
            
            // Agregar botón al item si no existe
            if (!evt.item.querySelector('.btn-outline-danger')) {
                evt.item.appendChild(btnEliminar);
            }
        },
        
        // Evento cuando se elimina un elemento
        onRemove: function(evt) {
            actualizarContadorEquivalencias();
        }
    });
    
    // Botón para limpiar todas las equivalencias
    document.getElementById('btnLimpiarEquivalencias').addEventListener('click', function() {
        const listaEquivalentes = document.getElementById('lista-equivalentes');
        const items = listaEquivalentes.querySelectorAll('li:not(.empty-state)');
        
        if (items.length === 0) {
            return;
        }
        
        Swal.fire({
            title: '¿Limpiar todas las equivalencias?',
            text: `Se eliminarán ${items.length} equivalencia(s)`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, limpiar todo',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                items.forEach(item => item.remove());
                actualizarContadorEquivalencias();
            }
        });
    });
    
    // Botón para guardar equivalencias
    btnGuardar.addEventListener('click', function() {
        const listaEquivalentes = document.getElementById('lista-equivalentes');
        const items = listaEquivalentes.querySelectorAll('li:not(.empty-state)');
        
        // Recopilar IDs de puestos equivalentes
        const equivalencias = Array.from(items).map(item => {
            return item.getAttribute('data-puesto-id');
        });
        
        console.log('Equivalencias a guardar:', equivalencias);
        
        Swal.fire({
            title: '¿Guardar equivalencias?',
            text: `Se guardarán ${equivalencias.length} equivalencia(s)`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí iría la petición AJAX para guardar en la base de datos
                // Ejemplo:
                /*
                http.request({
                    endpoint: "/CapHum/guardarEquivalencias",
                    method: "POST",
                    data: { equivalencias: equivalencias },
                    onSuccess: (resp) => {
                        if (resp.success) {
                            Swal.fire('¡Guardado!', 'Las equivalencias han sido guardadas correctamente', 'success');
                        }
                    }
                });
                */
                
                // Por ahora, solo mostramos mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado exitoso!',
                    text: `Se guardaron ${equivalencias.length} equivalencia(s) correctamente`,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        });
    });
    
    // Inicializar contador al cargar
    actualizarContadorEquivalencias();
});
</script>

<!-- Estilos CSS -->
<style>
/* ==========================================
   🎨 ESTILOS DRAG & DROP PARA EQUIVALENCIAS
   ========================================== */

/* Cursor para elementos arrastrables */
.cursor-move {
    cursor: move !important;
    cursor: grab !important;
    transition: all 0.2s ease;
}

.cursor-move:active {
    cursor: grabbing !important;
}

/* Efecto hover en items */
.cursor-move:hover {
    background-color: #f8f9fa !important;
    transform: translateX(3px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Elemento fantasma mientras se arrastra */
.sortable-ghost {
    opacity: 0.4 !important;
    background-color: #e3f2fd !important;
    border: 2px dashed #2196f3 !important;
}

/* Elemento seleccionado */
.sortable-chosen {
    background-color: #e8f5e9 !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
}

/* Elemento mientras se arrastra */
.sortable-drag {
    opacity: 0.8 !important;
    transform: rotate(3deg) !important;
    box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
}

/* Altura mínima para lista vacía */
.min-height-300 {
    min-height: 300px;
}

/* Estado vacío animado */
.empty-state {
    animation: fadeIn 0.5s ease;
    user-select: none !important;
    pointer-events: none !important;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Animación para nuevos items */
.list-group-item {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Grip icon en items */
.fa-grip-vertical {
    opacity: 0.3;
    transition: opacity 0.2s ease;
}

.cursor-move:hover .fa-grip-vertical {
    opacity: 0.7;
}

/* Botón eliminar en items clonados */
.list-group-item .btn-outline-danger {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    transition: all 0.2s ease;
}

.list-group-item .btn-outline-danger:hover {
    transform: scale(1.1);
}

/* Avatar en items */
.avatar-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    font-size: 1rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .min-height-300 {
        min-height: 200px;
    }
    
    .cursor-move {
        font-size: 0.9rem;
    }
}
</style>
