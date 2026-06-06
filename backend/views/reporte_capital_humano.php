<style>
/* Estilos para botones de SweetAlert */
.swal2-popup .btn-primary {
    background-color: #0047bb !important;
    border-color: #0047bb !important;
    color: #fff !important;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
}
.swal2-popup .btn-primary:hover {
    background-color: #003a99 !important;
    border-color: #003a99 !important;
}
.swal2-popup .btn-secondary {
    background-color: #6c757d !important;
    border-color: #6c757d !important;
    color: #fff !important;
    padding: 0.5rem 1.5rem;
    font-weight: 500;
}
.swal2-popup .btn-secondary:hover {
    background-color: #5a6268 !important;
    border-color: #545b62 !important;
}
/* Dark mode */
body.dark-mode .swal2-popup .btn-primary {
    background-color: #3b82f6 !important;
    border-color: #3b82f6 !important;
}
body.dark-mode .swal2-popup .btn-primary:hover {
    background-color: #2563eb !important;
    border-color: #2563eb !important;
}
body.dark-mode .swal2-popup .btn-secondary {
    background-color: #64748b !important;
    border-color: #64748b !important;
}
body.dark-mode .swal2-popup .btn-secondary:hover {
    background-color: #475569 !important;
    border-color: #475569 !important;
}
</style>

<div id="rch-landing" class="rch-reportes-personal-page">
<div class="card">
    <div class="card">
        <div class="row g-0 align-items-center overflow-visible rch-hero-row rch-hero-row--con-mascota rch-hero-block">
            <div class="col-12 col-md-8 rch-hero-text">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">HOLA, <?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars(strtoupper((string) $_SESSION['usuario_nombre']), ENT_QUOTES, 'UTF-8') : 'USUARIO'; ?> <i class="fa-solid fa-users ms-2 text-primary" aria-hidden="true"></i></h5>
                    <p class="mb-6 mb-md-0">
                        Consulta, filtra y descarga información de capital humano, incluyendo puestos, departamentos y estatus de empleados.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex flex-column justify-content-end align-items-center align-items-md-end rch-hero-mascot-col">
                <img src="/assets/img/illustrations/reportes-personal-mascota.png"
                     class="rch-hero-mascot-floating img-fluid"
                     width="400"
                     height="400"
                     alt="Reportes de Personal — ilustración">
            </div>
            <div class="row gy-3 mb-3 gx-0">
                <div class="col-12 col-md-8 col-lg-4">
                    <div class="card shadow-none bg-label-primary h-100">
                        <div class="card-body d-flex justify-content-between flex-wrap-reverse">
                            <div class="mb-0 w-100 app-academy-sm-60 d-flex flex-column justify-content-between text-center text-sm-start">
                                <div class="card-title">
                                    <h5 class="text-primary mb-2">Disponible para descarga diaria</h5>
                                    <p class="text-body w-sm-80 app-academy-xl-100">Consulta, filtra y descarga reportes de personal activo</p>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCapitalHumano">
                                    <i class="fas fa-file-excel me-2"></i>Descargar reporte
                                </button>
                            </div>
                            <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png" alt="Descarga reporte de personal">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
/* Hero Reportes de Personal — misma mascota (tamaño/posición/ángulo) que Panel Admin */
.rch-reportes-personal-page,
#rch-landing.rch-reportes-personal-page {
    overflow: visible;
}
.rch-reportes-personal-page .rch-hero-row {
    overflow: visible;
}
.rch-reportes-personal-page > .card {
    overflow: visible;
    position: relative;
}
.rch-hero-block {
    position: relative;
    z-index: 0;
    overflow: visible;
    --rch-mascot-max-w: 280px;
    --rch-mascot-max-h: min(300px, 44vh);
    --rch-mascot-translate-x: -6rem;
    --rch-mascot-translate-y: 3rem;
}
.rch-hero-text .card-body {
    padding-bottom: 1.25rem !important;
    padding-top: 2rem !important;
}
@media (min-width: 768px) {
    .rch-hero-row.rch-hero-row--con-mascota {
        align-items: stretch;
        min-height: 23rem;
        padding-bottom: 5rem;
    }
    .rch-hero-text .card-body {
        padding-top: 2rem !important;
        padding-bottom: 1.5rem !important;
        padding-right: 0.5rem;
    }
}
.rch-hero-mascot-col {
    padding-top: 1rem;
    padding-bottom: 2rem;
}
@media (min-width: 768px) {
    .rch-hero-mascot-col {
        position: relative;
        min-height: 0;
        padding: 0;
        align-self: stretch;
    }
    .rch-hero-text {
        position: relative;
        z-index: 2;
    }
}
.rch-hero-mascot-floating {
    display: block;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 28px rgba(26, 82, 168, 0.12));
}
@media (max-width: 767.98px) {
    .rch-hero-row.rch-hero-row--con-mascota {
        min-height: 15rem;
    }
    .rch-hero-text .card-body {
        text-align: center;
        padding-top: 2rem !important;
        padding-bottom: 1rem !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
    .rch-hero-mascot-col {
        align-items: center !important;
        padding-top: 0;
    }
    .rch-hero-mascot-floating {
        margin: 0 auto;
        max-width: min(58vw, 200px);
        max-height: min(32vh, 200px);
        width: auto;
        height: auto;
        object-position: bottom center;
        transform: translateY(var(--rch-mascot-translate-y, 3rem));
    }
}
@media (min-width: 768px) {
    .rch-hero-mascot-floating {
        position: relative;
        right: auto;
        bottom: auto;
        z-index: 1;
        width: auto;
        height: auto;
        max-width: var(--rch-mascot-max-w, 280px);
        max-height: var(--rch-mascot-max-h, 300px);
        margin: 0 0 0 auto;
        object-position: bottom right;
        transform: translate(var(--rch-mascot-translate-x, -6rem), var(--rch-mascot-translate-y, 3rem));
    }
}
body.dark-mode .rch-hero-mascot-floating {
    filter: drop-shadow(0 12px 32px rgba(0, 0, 0, 0.35));
}
</style>
</div><!-- /#rch-landing -->

<!-- MODAL DE REPORTE CAPITAL HUMANO - SOLO USUARIOS ACTIVOS -->
<div class="modal fade" id="modalCapitalHumano" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 92vw;">
        <div class="modal-content">
            <div class="modal-header border-bottom py-3">
                <div>
                    <h5 class="modal-title fw-semibold">
                        <i class="fas fa-file-excel text-success me-2"></i>
                        Reporte de Personal
                    </h5>
                    <small class="text-muted">Consulta y descarga información del personal activo</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body">
                <!-- FILTROS COMPACTOS -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body py-3">
                        <form id="formFiltrosUsuarios">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold mb-1">Departamento</label>
                                    <select class="form-select form-select-sm" id="filtroDepartamentoUsuario">
                                        <option value="">Todos los departamentos</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold mb-1">Puesto</label>
                                    <select class="form-select form-select-sm" id="filtroPuestoUsuario">
                                        <option value="">Todos los puestos</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold mb-1">Estatus</label>
                                    <select class="form-select form-select-sm" id="filtroEstatusUsuario">
                                        <option value="">Todos</option>
                                        <option value="Activo">Activo</option>
                                        <option value="Inactivo">Inactivo</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold mb-1">Multipuesto</label>
                                    <select class="form-select form-select-sm" id="filtroMultipuestoUsuario">
                                        <option value="">Todos</option>
                                        <option value="unico">Un solo puesto</option>
                                        <option value="multiples">Múltiples puestos</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- BOTÓN DESCARGA COMPACTO -->
                <div class="d-flex justify-content-end mb-2">
                    <button class="btn btn-success btn-sm" id="btnDescargarUsuarios">
                        <i class="fas fa-download me-1"></i>Descargar Excel
                    </button>
                </div>
                
                <!-- ===== TABLA CON ESTILO EXACTO DE ALL_GESTORES Y ALTURA INTELIGENTE ===== -->
                <div class="card-datatable table-responsive">
                    <table id="tablaUsuariosCapitalHumano" class="dt-responsive table table-hover border-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th>N° Empleado</th>
                                <th>Nombre</th>
                                <th>Departamento</th>
                                <th>Puesto</th>
                                <th>Estatus</th>
                                <th>Usuario</th>
                                <th>Jefe</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            
            <!-- FOOTER COMPACTO -->
            <div class="modal-footer py-2">
                <small class="text-muted">
                    <i class="fas fa-file-excel me-1"></i>
                    Formato Excel (.xlsx)
                </small>
                <div>
                    <button class="btn btn-outline-secondary btn-sm me-2" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================
// ANALÍTICA — REPORTE CAPITAL HUMANO - SOLO USUARIOS ACTIVOS
// ============================================

let tablaUsuarios = null;
let todosLosUsuarios = [];
let todosLosPuestos = [];
let puestosPorDepartamento = {};
let datosFiltradosUsuarios = [];

// ============================================
// 1. INICIALIZAR DATATABLE USUARIOS
// ============================================
function inicializarDataTableUsuarios() {
    if ($.fn.DataTable.isDataTable('#tablaUsuariosCapitalHumano')) {
        $('#tablaUsuariosCapitalHumano').DataTable().destroy();
    }
    
    tablaUsuarios = $('#tablaUsuariosCapitalHumano').DataTable({
        data: [],
        columns: [
            {
                className: 'control',
                orderable: false,
                searchable: false,
                data: null,
                defaultContent: ''
            },
            { data: 'numero_empleado' },
            { data: 'nombre_completo' },
            { data: 'departamento' },
            { data: 'nombre_puesto' },
            { 
                data: 'estatus',
                render: function(data) {
                    let badgeClass = '';
                    switch(data) {
                        case 'Activo': badgeClass = 'bg-success'; break;
                        case 'Inactivo': badgeClass = 'bg-secondary'; break;
                        case 'Baja': badgeClass = 'bg-danger'; break;
                        default: badgeClass = 'bg-secondary';
                    }
                    return `<span class="badge ${badgeClass}">${data || 'N/A'}</span>`;
                }
            },
            { data: 'usuario' },
            { data: 'nombre_jefe' }
        ],
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        order: [[1, 'asc']],
        ordering: true,
        responsive: {
            details: {
                display: $.fn.dataTable.Responsive.display.modal({
                    header: function(row) {
                        var data = row.data();
                        return 'Detalles de: ' + data.nombre_completo;
                    }
                }),
                renderer: $.fn.dataTable.Responsive.renderer.tableAll({
                    tableClass: 'table table-bordered'
                })
            }
        },
        language: {
            decimal: "",
            emptyTable: "No hay datos disponibles en la tabla",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            infoPostFix: "",
            thousands: ",",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando...",
            processing: "Procesando...",
            search: "Buscar:",
            zeroRecords: "No se encontraron resultados",
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            },
            aria: {
                sortAscending: ": activar para ordenar ascendente",
                sortDescending: ": activar para ordenar descendente"
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        autoWidth: false,
        deferRender: true,
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-sm');
            $('.dataTables_length select').addClass('form-select form-select-sm');
            $('.dataTables_filter input').addClass('form-control form-control-sm');
        }
    });
}

// ============================================
// 2. CARGAR USUARIOS Y CONSTRUIR MAPA DE PUESTOS
// ============================================
function cargarUsuariosCapitalHumano() {
    fetch('/analitica/getUsuariosCapitalHumano')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                const detalle = data.error ? String(data.error) : '';
                Swal.fire('Error', data.mensaje || detalle || 'No se pudieron cargar los usuarios', 'error');
                return;
            }
            
            todosLosUsuarios = data.datos || [];
            datosFiltradosUsuarios = [...todosLosUsuarios];
            
            puestosPorDepartamento = {};
            todosLosUsuarios.forEach(u => {
                const dept = u.nombre_departamento;
                const puesto = u.nombre_puesto;
                
                if (dept && puesto && dept !== 'Sin departamento' && puesto !== 'Sin puesto') {
                    if (!puestosPorDepartamento[dept]) {
                        puestosPorDepartamento[dept] = new Set();
                    }
                    puestosPorDepartamento[dept].add(puesto);
                }
            });
            
            Object.keys(puestosPorDepartamento).forEach(dept => {
                puestosPorDepartamento[dept] = Array.from(puestosPorDepartamento[dept]).sort();
            });
            
            const todosPuestosSet = new Set();
            todosLosUsuarios.forEach(u => {
                if (u.nombre_puesto && u.nombre_puesto !== 'Sin puesto') {
                    todosPuestosSet.add(u.nombre_puesto);
                }
            });
            todosLosPuestos = Array.from(todosPuestosSet).sort();
            
            const datosTabla = todosLosUsuarios.map(u => ({
                numero_empleado: u.numero_empleado || 'N/A',
                nombre_completo: [u.nombres, u.segundo_nombre, u.apellidop, u.apellidom].filter(Boolean).join(' '),
                departamento: u.nombre_departamento || 'N/A',
                nombre_puesto: u.nombre_puesto || 'N/A',
                estatus: u.estatus || 'N/A',
                usuario: u.usuario || 'N/A',
                nombre_jefe: u.nombre_jefe || 'N/A'
            }));
            
            tablaUsuarios.clear();
            tablaUsuarios.rows.add(datosTabla);
            tablaUsuarios.draw();
            
            inicializarSelectsUsuarios();
        })
        .catch(error => {
            console.error('Error al cargar usuarios:', error);
            Swal.fire('Error', error.message || 'Error al cargar usuarios', 'error');
        });
}

// ============================================
// 3. INICIALIZAR SELECTS DE USUARIOS
// ============================================
function inicializarSelectsUsuarios() {
    const selectDept = document.getElementById('filtroDepartamentoUsuario');
    const selectPuesto = document.getElementById('filtroPuestoUsuario');
    
    if (!selectDept || !selectPuesto) return;
    
    const departamentosSet = new Set();
    todosLosUsuarios.forEach(u => {
        if (u.nombre_departamento && u.nombre_departamento !== 'Sin departamento') {
            departamentosSet.add(u.nombre_departamento);
        }
    });
    
    const departamentos = Array.from(departamentosSet).sort();
    
    selectDept.innerHTML = '<option value="">Todos los departamentos</option>';
    departamentos.forEach(dept => {
        selectDept.innerHTML += `<option value="${dept}">${dept}</option>`;
    });
    
    actualizarSelectPuestosUsuario(null);
}

// ============================================
// 4. ACTUALIZAR PUESTOS SEGÚN DEPARTAMENTO
// ============================================
function actualizarSelectPuestosUsuario(departamentoSeleccionado) {
    const selectPuesto = document.getElementById('filtroPuestoUsuario');
    if (!selectPuesto) return;
    
    selectPuesto.innerHTML = '';
    
    let puestosAMostrar = [];
    
    if (!departamentoSeleccionado) {
        puestosAMostrar = todosLosPuestos;
        selectPuesto.innerHTML = '<option value="">Todos los puestos</option>';
    } else {
        puestosAMostrar = puestosPorDepartamento[departamentoSeleccionado] || [];
        selectPuesto.innerHTML = '<option value="">Selecciona un puesto</option>';
    }
    
    puestosAMostrar.forEach(puesto => {
        selectPuesto.innerHTML += `<option value="${puesto}">${puesto}</option>`;
    });
}

// ============================================
// 5. FILTRAR USUARIOS EN TIEMPO REAL
// ============================================
function filtrarUsuarios() {
    const dept = document.getElementById('filtroDepartamentoUsuario').value;
    const puesto = document.getElementById('filtroPuestoUsuario').value;
    const estatus = document.getElementById('filtroEstatusUsuario').value;
    const multipuesto = document.getElementById('filtroMultipuestoUsuario').value;
    
    datosFiltradosUsuarios = todosLosUsuarios.filter(u => {
        if (dept && u.nombre_departamento !== dept) return false;
        if (puesto && u.nombre_puesto !== puesto) return false;
        if (estatus && u.estatus !== estatus) return false;
        if (multipuesto === 'unico') return !u.es_multipuesto;
        if (multipuesto === 'multiples') return u.es_multipuesto === true;
        return true;
    });
    
    const datosTabla = datosFiltradosUsuarios.map(u => ({
        numero_empleado: u.numero_empleado || 'N/A',
        nombre_completo: [u.nombres, u.segundo_nombre, u.apellidop, u.apellidom].filter(Boolean).join(' '),
        departamento: u.nombre_departamento || 'N/A',
        nombre_puesto: u.nombre_puesto || 'N/A',
        estatus: u.estatus || 'N/A',
        usuario: u.usuario || 'N/A',
        nombre_jefe: u.nombre_jefe || 'N/A'
    }));
    
    tablaUsuarios.clear();
    tablaUsuarios.rows.add(datosTabla);
    tablaUsuarios.draw();
}

// ============================================
// DESCARGAR EXCEL - VERSIÓN FINAL (MISMA VENTANA, SIN CANCELAR)
// ============================================
function descargarExcelUsuarios() {
    const departamento = document.getElementById('filtroDepartamentoUsuario').value || '';
    const puesto = document.getElementById('filtroPuestoUsuario').value || '';
    const estatus = document.getElementById('filtroEstatusUsuario').value || '';
    const multipuesto = document.getElementById('filtroMultipuestoUsuario').value || '';
    
    let mensajeFiltros = 'Se descargará un archivo Excel con ';
    let detallesFiltros = [];
    let nombreArchivo = 'Plantilla_Gestores';
    
    if (departamento) {
        detallesFiltros.push(`Departamento: <strong>${departamento}</strong>`);
        nombreArchivo += '_' + departamento.replace(/ /g, '_');
    }
    if (puesto) {
        detallesFiltros.push(`Puesto: <strong>${puesto}</strong>`);
        nombreArchivo += '_' + puesto.replace(/ /g, '_');
    }
    if (estatus) {
        detallesFiltros.push(`Estatus: <strong>${estatus}</strong>`);
        nombreArchivo += '_' + estatus;
    }
    if (multipuesto === 'multiples') {
        detallesFiltros.push(`<strong>Múltiples puestos</strong>`);
        nombreArchivo += '_MultiplesPuestos';
    } else if (multipuesto === 'unico') {
        detallesFiltros.push(`<strong>Un solo puesto</strong>`);
        nombreArchivo += '_UnPuesto';
    }
    
    if (detallesFiltros.length > 0) {
        mensajeFiltros = 'Se descargará un archivo Excel filtrado por:<br><br>' + detallesFiltros.join('<br>');
    } else {
        mensajeFiltros = 'Se descargará un archivo Excel con <strong>TODOS los gestores</strong> del sistema.';
    }
    
    const ahora = new Date();
    const año = ahora.getFullYear();
    const mes = String(ahora.getMonth() + 1).padStart(2, '0');
    const dia = String(ahora.getDate()).padStart(2, '0');
    const horas = String(ahora.getHours()).padStart(2, '0');
    const minutos = String(ahora.getMinutes()).padStart(2, '0');
    const segundos = String(ahora.getSeconds()).padStart(2, '0');
    nombreArchivo += `_${año}-${mes}-${dia}_${horas}${minutos}${segundos}`;
    
    // MODAL DE CONFIRMACIÓN - SOLO PREGUNTA UNA VEZ
    Swal.fire({
        html: `
            <div style="text-align: center;">
                <i class="fas fa-file-excel" style="font-size: 4rem; color: #0047bb; margin-bottom: 1rem;"></i>
                <h4 style="margin-top: 1rem; margin-bottom: 1rem; font-weight: 600;">Descargar Reporte de Personal</h4>
                <p style="color: #697a8d; margin-bottom: 1.5rem; font-size: 0.95rem;">
                    ${mensajeFiltros}
                </p>
                <div style="background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: left;">
                    <small style="color: #6c757d; display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fas fa-info-circle" style="color: #0d6efd;"></i>
                        Incluye: número de empleado, nombres, departamento, puesto, jefe y estatus.
                    </small>
                </div>
                <p style="margin-top: 0.5rem; font-weight: 500;"><strong>¿Deseas continuar con la descarga?</strong></p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-download me-2"></i>Sí, descargar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-primary',
            cancelButton: 'btn btn-secondary'
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed) {
            // ✅ USUARIO CONFIRMÓ - INICIAR DESCARGA DIRECTA
            const url = '/analitica/descargarUsuariosExcelCapitalHumano?' + new URLSearchParams({
                ...(departamento && { departamento }),
                ...(puesto && { puesto }),
                ...(estatus && { estatus }),
                ...(multipuesto && { multipuesto })
            }).toString();
            
            // Descarga directa usando window.location
            window.location.href = url;
            
            // Mostrar mensaje de éxito después de un breve momento
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: '¡Descarga iniciada!',
                    text: 'El archivo Excel se está descargando.',
                    timer: 2500,
                    showConfirmButton: false,
                    timerProgressBar: true
                });
            }, 500);
            
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // 🚫 USUARIO CANCELÓ - MENSAJE AMIGABLE
            Swal.fire({
                icon: 'info',
                title: 'Descarga cancelada',
                text: 'Has cancelado la descarga del reporte.',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '#6c757d',
                timer: 2500,
                timerProgressBar: true
            });
        }
    });
}
// ============================================
// 7. INICIALIZAR TODO
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    inicializarDataTableUsuarios();
    cargarUsuariosCapitalHumano();
    
    const selectDept = document.getElementById('filtroDepartamentoUsuario');
    const selectPuesto = document.getElementById('filtroPuestoUsuario');
    const selectEstatus = document.getElementById('filtroEstatusUsuario');
    const selectMultipuesto = document.getElementById('filtroMultipuestoUsuario');
    
    if (selectDept) {
        selectDept.addEventListener('change', function(e) {
            actualizarSelectPuestosUsuario(e.target.value);
            filtrarUsuarios();
        });
    }
    
    if (selectPuesto) selectPuesto.addEventListener('change', filtrarUsuarios);
    if (selectEstatus) selectEstatus.addEventListener('change', filtrarUsuarios);
    if (selectMultipuesto) selectMultipuesto.addEventListener('change', filtrarUsuarios);
    
    document.getElementById('btnDescargarUsuarios')?.addEventListener('click', descargarExcelUsuarios);
    document.getElementById('formFiltrosUsuarios')?.addEventListener('submit', e => e.preventDefault());
});
// ============================================
// FORMATEAR USUARIO PARA TABLA - CON MÚLTIPLES PUESTOS
// ============================================
function formatearUsuario(u) {
    const nombreCompleto = [u.nombres, u.segundo_nombre, u.apellidop, u.apellidom].filter(Boolean).join(' ');
    const tienePuestos = u.puestos && u.puestos.length > 1;
    
    // Generar HTML para puestos
    let puestosHTML = '';
    if (tienePuestos) {
        const totalPuestos = u.puestos.length;
        const mostrarDirecto = totalPuestos <= 3;
        const puestosVisible = mostrarDirecto ? u.puestos : u.puestos.slice(0, 2);
        
        puestosHTML = '<div class="d-flex flex-column gap-2">';
        
        puestosVisible.forEach((puesto, index) => {
            const esPrincipal = index === 0;
            const claseBadge = esPrincipal ? 'badge-puesto-principal' : 'badge-puesto-secundario';
            
            puestosHTML += `
                <div class="d-flex flex-column" style="gap: 0.25rem;">
                    <small class="departamento-label">
                        <i class="fa fa-building"></i>${puesto.nombre_departamento}
                    </small>
                    <span class="badge ${claseBadge}" style="width: 100%; text-align: left;"
                          title="${esPrincipal ? 'Puesto Principal' : 'Puesto Secundario'}: ${puesto.nombre_puesto}">
                        <i class="fa fa-briefcase me-1"></i>
                        ${puesto.nombre_puesto}
                        ${esPrincipal ? '<small class="ms-1" style="opacity: 0.9;">(Principal)</small>' : ''}
                    </span>
                </div>
            `;
        });
        
        // Botón "ver más" si hay más de 3 puestos
        if (!mostrarDirecto) {
            const puestosRestantes = totalPuestos - 2;
            puestosHTML += `
                <button class="btn-ver-mas-puestos" onclick="expandirPuestos(${u.id})" 
                        title="Ver ${puestosRestantes} puesto(s) más">
                    <i class="fa fa-plus-circle me-1"></i>Ver ${puestosRestantes} más
                </button>
            `;
        }
        
        puestosHTML += '</div>';
    } else {
        // Un solo puesto
        puestosHTML = `
            <small class="text-muted d-flex align-items-center gap-1">
                <i class="fa fa-building"></i>
                ${u.nombre_departamento || 'N/A'}
            </small>
            <small class="text-muted d-flex align-items-center gap-1">
                <i class="fa fa-briefcase"></i>
                ${u.nombre_puesto || 'N/A'}
            </small>
        `;
    }
    
    return {
        numero_empleado: u.numero_empleado || 'N/A',
        nombre_completo: nombreCompleto,
        departamento: puestosHTML, // ✅ AQUÍ VA TODO EL HTML DE PUESTOS
        nombre_puesto: u.nombre_puesto || 'N/A', // Para compatibilidad
        estatus: u.estatus || 'N/A',
        usuario: u.usuario || 'N/A',
        nombre_jefe: u.nombre_jefe || 'N/A',
        id: u.id,
        tiene_puestos: tienePuestos,
        total_puestos: u.puestos?.length || 1
    };
}

// ============================================
// EXPANDIR PUESTOS - MODAL CON TODOS LOS PUESTOS
// ============================================
function expandirPuestos(userId) {
    const usuario = todosLosUsuarios.find(u => u.id === userId);
    if (!usuario || !usuario.puestos) return;
    
    let puestosHTML = '<div class="d-flex flex-column gap-3">';
    usuario.puestos.forEach((puesto, index) => {
        const esPrincipal = index === 0;
        const claseBadge = esPrincipal ? 'badge-puesto-principal' : 'badge-puesto-secundario';
        
        puestosHTML += `
            <div class="d-flex flex-column">
                <small class="departamento-label" style="font-size: 0.75rem; color: #6c757d;">
                    <i class="fa fa-building me-1"></i>${puesto.nombre_departamento}
                </small>
                <span class="badge ${claseBadge}" style="width: 100%; font-size: 0.9rem; padding: 0.6rem 1rem;">
                    <i class="fa fa-briefcase me-2"></i>
                    ${puesto.nombre_puesto}
                    ${esPrincipal ? '<span class="ms-2 badge bg-light text-dark">Principal</span>' : ''}
                </span>
            </div>
        `;
    });
    puestosHTML += '</div>';
    
    const nombreCompleto = [usuario.nombres, usuario.segundo_nombre, usuario.apellidop, usuario.apellidom].filter(Boolean).join(' ');
    
    Swal.fire({
        title: `<i class="fa fa-layer-group me-2" style="color: #f59e0b;"></i>${usuario.puestos.length} Puestos Asignados`,
        html: `
            <div class="text-start">
                <div class="mb-4 p-3 bg-light rounded">
                    <strong><i class="fa fa-user me-2"></i>${nombreCompleto}</strong>
                    <br>
                    <small class="text-muted">#${usuario.numero_empleado} | ${usuario.usuario}</small>
                </div>
                ${puestosHTML}
            </div>
        `,
        width: '600px',
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            popup: 'swal-wide'
        }
    });
}

// ============================================
// CARGAR USUARIOS - AHORA CON CONSOLIDACIÓN DE PUESTOS
// ============================================
function cargarUsuariosCapitalHumano() {
    fetch('/analitica/getUsuariosCapitalHumano')
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                const detalle = data.error ? String(data.error) : '';
                Swal.fire('Error', data.mensaje || detalle || 'No se pudieron cargar los usuarios', 'error');
                return;
            }
            
            // CONSOLIDAR USUARIOS CON MÚLTIPLES PUESTOS
            const usuariosMap = new Map();
            
            data.datos.forEach(usuario => {
                const id = usuario.id;
                
                if (!usuariosMap.has(id)) {
                    usuariosMap.set(id, {
                        ...usuario,
                        puestos: [{
                            id_puesto: usuario.id_puesto,
                            nombre_puesto: usuario.nombre_puesto,
                            nombre_departamento: usuario.nombre_departamento,
                            id_departamento: usuario.id_departamento
                        }]
                    });
                } else {
                    const usuarioExistente = usuariosMap.get(id);
                    const puestoExiste = usuarioExistente.puestos.some(p => 
                        p.id_puesto === usuario.id_puesto && 
                        p.nombre_departamento === usuario.nombre_departamento
                    );
                    
                    if (!puestoExiste) {
                        usuarioExistente.puestos.push({
                            id_puesto: usuario.id_puesto,
                            nombre_puesto: usuario.nombre_puesto,
                            nombre_departamento: usuario.nombre_departamento,
                            id_departamento: usuario.id_departamento
                        });
                    }
                }
            });
            
            todosLosUsuarios = Array.from(usuariosMap.values());
            datosFiltradosUsuarios = [...todosLosUsuarios];
            
            // Mapa de puestos por departamento (para filtros)
            puestosPorDepartamento = {};
            todosLosUsuarios.forEach(u => {
                u.puestos.forEach(puesto => {
                    const dept = puesto.nombre_departamento;
                    const nombrePuesto = puesto.nombre_puesto;
                    
                    if (dept && nombrePuesto && dept !== 'Sin departamento' && nombrePuesto !== 'Sin puesto') {
                        if (!puestosPorDepartamento[dept]) {
                            puestosPorDepartamento[dept] = new Set();
                        }
                        puestosPorDepartamento[dept].add(nombrePuesto);
                    }
                });
            });
            
            Object.keys(puestosPorDepartamento).forEach(dept => {
                puestosPorDepartamento[dept] = Array.from(puestosPorDepartamento[dept]).sort();
            });
            
            // Todos los puestos únicos
            const todosPuestosSet = new Set();
            todosLosUsuarios.forEach(u => {
                u.puestos.forEach(puesto => {
                    if (puesto.nombre_puesto && puesto.nombre_puesto !== 'Sin puesto') {
                        todosPuestosSet.add(puesto.nombre_puesto);
                    }
                });
            });
            todosLosPuestos = Array.from(todosPuestosSet).sort();
            
            // Formatear datos para DataTable
            const datosTabla = todosLosUsuarios.map(u => formatearUsuario(u));
            
            tablaUsuarios.clear();
            tablaUsuarios.rows.add(datosTabla);
            tablaUsuarios.draw();
            
            inicializarSelectsUsuarios();
        })
        .catch(error => {
            console.error('Error al cargar usuarios:', error);
            Swal.fire('Error', error.message || 'Error al cargar usuarios', 'error');
        });
}

// ============================================
// FILTRAR USUARIOS - AHORA RESPETA MÚLTIPLES PUESTOS
// ============================================
function filtrarUsuarios() {
    const dept = document.getElementById('filtroDepartamentoUsuario').value;
    const puesto = document.getElementById('filtroPuestoUsuario').value;
    const estatus = document.getElementById('filtroEstatusUsuario').value;
    const multipuesto = document.getElementById('filtroMultipuestoUsuario').value;
    
    datosFiltradosUsuarios = todosLosUsuarios.filter(u => {
        // Filtro DEPARTAMENTO - el usuario tiene AL MENOS UN puesto en ese departamento
        if (dept) {
            const tieneDept = u.puestos.some(p => p.nombre_departamento === dept);
            if (!tieneDept) return false;
        }
        
        // Filtro PUESTO - el usuario tiene AL MENOS UN puesto con ese nombre
        if (puesto) {
            const tienePuesto = u.puestos.some(p => p.nombre_puesto === puesto);
            if (!tienePuesto) return false;
        }
        
        // Filtro ESTATUS
        if (estatus && u.estatus !== estatus) return false;
        
        // Filtro MULTIPUESTO
        if (multipuesto === 'unico' && u.puestos.length > 1) return false;
        if (multipuesto === 'multiples' && u.puestos.length === 1) return false;
        
        return true;
    });
    
    const datosTabla = datosFiltradosUsuarios.map(u => formatearUsuario(u));
    tablaUsuarios.clear();
    tablaUsuarios.rows.add(datosTabla);
    tablaUsuarios.draw();
}

// ============================================
// ACTUALIZAR PUESTOS SEGÚN DEPARTAMENTO - MEJORADO
// ============================================
function actualizarSelectPuestosUsuario(departamentoSeleccionado) {
    const selectPuesto = document.getElementById('filtroPuestoUsuario');
    if (!selectPuesto) return;
    
    selectPuesto.innerHTML = '';
    
    let puestosAMostrar = [];
    
    if (!departamentoSeleccionado) {
        // TODOS los puestos del sistema
        puestosAMostrar = todosLosPuestos;
        selectPuesto.innerHTML = '<option value="">Todos los puestos</option>';
    } else {
        // SOLO puestos de ESE departamento
        puestosAMostrar = puestosPorDepartamento[departamentoSeleccionado] || [];
        selectPuesto.innerHTML = '<option value="">Selecciona un puesto</option>';
    }
    
    puestosAMostrar.forEach(puesto => {
        selectPuesto.innerHTML += `<option value="${puesto}">${puesto}</option>`;
    });
}
</script>

<style>
/* ===== ESTILOS DE DATATABLE - IGUAL A ALL_GESTORES ===== */
.card-datatable {
    padding: 1.5rem;
}

.table {
    width: 100% !important;
    margin-bottom: 0 !important;
}

.table thead th {
    background: #f8f9fa;
    font-size: 0.85rem;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    padding: 1rem 0.75rem;
    white-space: nowrap;
}

.table tbody td {
    font-size: 0.85rem;
    vertical-align: middle;
    padding: 0.75rem;
    border-bottom: 1px solid #e9ecef;
}

.table tbody tr:hover {
    background-color: rgba(13, 110, 253, 0.05);
    transition: all 0.2s ease;
}

.dataTables_paginate {
    margin-top: 1rem;
    display: flex;
    justify-content: flex-end;
}

.pagination {
    margin: 0;
    display: flex;
    gap: 0.25rem;
}

.pagination .paginate_button {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    color: #0d6efd;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pagination .paginate_button:hover {
    background: #e9ecef;
    border-color: #dee2e6;
}

.pagination .paginate_button.current {
    background: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.pagination .paginate_button.disabled {
    color: #6c757d;
    pointer-events: none;
    background: #e9ecef;
}

.dataTables_length {
    margin-bottom: 1rem;
}

.dataTables_length select {
    margin: 0 0.5rem;
    padding: 0.375rem 1.75rem 0.375rem 0.75rem;
}

.dataTables_filter {
    margin-bottom: 1rem;
    text-align: right;
}

.dataTables_filter input {
    margin-left: 0.5rem;
    padding: 0.375rem 0.75rem;
    border: 1px solid #d9dee3;
    border-radius: 0.375rem;
}

.dataTables_filter input:focus {
    border-color: #0d6efd;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.dataTables_info {
    margin-top: 1rem;
    font-size: 0.85rem;
    color: #6c757d;
}

table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control,
table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control {
    position: relative;
    padding-left: 30px;
    cursor: pointer;
}

table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before,
table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control:before {
    top: 50%;
    left: 5px;
    height: 1em;
    width: 1em;
    margin-top: -9px;
    display: block;
    position: absolute;
    color: white;
    border: 0.15em solid white;
    border-radius: 1em;
    box-shadow: 0 0 0.2em #444;
    box-sizing: content-box;
    text-align: center;
    text-indent: 0 !important;
    font-family: 'Courier New', Courier, monospace;
    line-height: 1em;
    content: '+';
    background-color: #0d6efd;
}

@media (max-width: 767px) {
    .dataTables_length,
    .dataTables_filter {
        text-align: left;
    }
    
    .dataTables_filter input {
        width: 100%;
        margin-left: 0;
    }
    
    .dataTables_paginate {
        justify-content: center;
    }
}

/* ===== ESTILOS PARA MÚLTIPLES PUESTOS ===== */
.badge-puesto-principal {
    background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
    color: white !important;
    font-size: 0.8rem !important;
    padding: 0.5rem 0.75rem !important;
    border-radius: 0.375rem !important;
    font-weight: 600 !important;
    border: 1px solid #1e40af !important;
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2) !important;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    transition: all 0.3s ease;
}

.badge-puesto-secundario {
    background: linear-gradient(135deg, #10B981, #34D399) !important;
    color: white !important;
    font-size: 0.75rem !important;
    padding: 0.4rem 0.6rem !important;
    border-radius: 0.375rem !important;
    font-weight: 500 !important;
    border: 1px solid #059669 !important;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2) !important;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.3s ease;
}

.badge-multipuesto-indicator {
    background: linear-gradient(135deg, #f59e0b, #fbbf24) !important;
    color: white !important;
    font-size: 0.65rem !important;
    padding: 0.25rem 0.5rem !important;
    border-radius: 0.25rem !important;
    font-weight: 600 !important;
    letter-spacing: 0.025rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.departamento-label {
    font-size: 0.65rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 0.15rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-ver-mas-puestos {
    font-size: 0.7rem;
    padding: 0.25rem 0.75rem;
    background: linear-gradient(135deg, #64748b, #94a3b8);
    border: none;
    color: white;
    border-radius: 2rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    border: 1px solid rgba(255,255,255,0.2);
}

.btn-ver-mas-puestos:hover {
    background: linear-gradient(135deg, #475569, #64748b);
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Animación para el indicador */
@keyframes pulseIndicator {
    0%, 100% { transform: scale(1); box-shadow: 0 2px 8px rgba(245, 158, 11, 0.5); }
    50% { transform: scale(1.1); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.7); }
}

.indicator-multiples-puestos {
    position: absolute;
    top: -8px;
    right: -8px;
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.65rem;
    color: white;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.5);
    animation: pulseIndicator 2s infinite;
    z-index: 10;
    border: 2px solid white;
}

.btn-with-indicator {
    position: relative;
    overflow: visible !important;
}

/* Modal de puestos - SweetAlert personalizado */
.swal-wide {
    width: 600px !important;
}

.swal-wide .departamento-label {
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.swal-wide .badge {
    margin-bottom: 0.5rem;
    text-align: left;
}

</style>
