<div class="card">
    <div class="card">
        <div class="row g-0 align-items-center">
            <div class="col-12 col-md-8">
                <div class="card-body">
                    <h5 class="card-title text-primary mb-3">Reporte de Capital Humano</h5>
                    <p class="mb-6">
                        Consulta, filtra y descarga información de capital humano, incluyendo bajas, puestos, departamentos y estatus de empleados.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card-body ps-md-2 pe-5 text-end">
                    <img src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/img/illustrations/man-with-laptop.png"
                         class="img-fluid scaleX-n1-rtl"
                         alt="Capital Humano">
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
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCapitalHumano">
                                    Descargar reporte
                                </button>
                            </div>
                            <div class="w-100 app-academy-sm-40 d-flex justify-content-center justify-content-sm-end h-px-150 mb-4 mb-sm-0">
                                <img class="img-fluid scaleX-n1-rtl" src="https://cdn-icons-png.freepik.com/512/11053/11053297.png" alt="capital humano illustration">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- MODAL REDISEÑADO con DataTables -->
<div class="modal fade" id="modalCapitalHumano" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-semibold">Reporte de Capital Humano</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <form id="formBuscarCapitalHumano" method="POST">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Fecha de inicio</label>
                                    <input type="date" class="form-control" id="fechaInicioCH" name="fechaInicioCH" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Fecha de fin</label>
                                    <input type="date" class="form-control" id="fechaFinCH" name="fechaFinCH" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Buscar bajas
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="tablaCapitalHumano" class="table table-hover table-striped mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th># Empleado</th>
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
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-file-excel me-1"></i>
                    El reporte se descargará en formato Excel (.xlsx)
                </small>
                <div>
                    <button class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                    <button class="btn btn-success" id="btnDescargarCapitalHumano" disabled>
                        <i class="fas fa-download me-2"></i>Descargar reporte
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    <?= $script ?? '' ?>
let datosCapitalHumano = [];
let datosBusquedaCH = { fechaInicio: '', fechaFin: '' };
let tablaCapitalHumano = null;
function inicializarDataTableCH() {
    if ($.fn.DataTable.isDataTable('#tablaCapitalHumano')) {
        $('#tablaCapitalHumano').DataTable().destroy();
    }
    tablaCapitalHumano = $('#tablaCapitalHumano').DataTable({
        data: [],
        columns: [
            { data: 'numero_empleado' },
            { data: 'nombre_completo' },
            { data: 'departamento' },
            { data: 'nombre_puesto' },
            { data: 'estatus' },
            { data: 'usuario' },
            { data: 'nombre_jefe' }
        ],
        pageLength: 10,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        responsive: true,
        destroy: true
    });
}
function cargarDatosCapitalHumano() {
    fetch('/Reporteria/getUsuariosCapitalHumano')
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            datosCapitalHumano = data.datos.map(u => ({
                numero_empleado: u.numero_empleado,
                nombre_completo: [u.nombres, u.segundo_nombre, u.apellidop, u.apellidom].filter(Boolean).join(' '),
                departamento: u.nombre_departamento,
                nombre_puesto: u.nombre_puesto,
                estatus: u.estatus,
                usuario: u.usuario,
                nombre_jefe: u.nombre_jefe
            }));
            tablaCapitalHumano.clear().rows.add(datosCapitalHumano).draw();
            document.getElementById('btnDescargarCapitalHumano').disabled = false;
        });
}
function filtrarBajasCapitalHumanoCH() {
    const inicio = document.getElementById('fechaInicioCH').value;
    const fin = document.getElementById('fechaFinCH').value;
    fetch('/Reporteria/getBajasCapitalHumano', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ fecha_inicio: inicio, fecha_fin: fin })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            tablaCapitalHumano.clear().draw();
            document.getElementById('btnDescargarCapitalHumano').disabled = true;
            Swal.fire('Sin resultados', data.mensaje || 'No se encontraron bajas', 'info');
            return;
        }
        const bajas = data.datos.map(u => ({
            numero_empleado: u.numero_empleado,
            nombre_completo: [u.nombres, u.segundo_nombre, u.apellidop, u.apellidom].filter(Boolean).join(' '),
            departamento: u.departamento,
            nombre_puesto: u.nombre_puesto,
            estatus: 'BAJA',
            usuario: u.user_name,
            nombre_jefe: ''
        }));
        tablaCapitalHumano.clear().rows.add(bajas).draw();
        document.getElementById('btnDescargarCapitalHumano').disabled = false;
    });
}
document.addEventListener('DOMContentLoaded', function() {
    inicializarDataTableCH();
    cargarDatosCapitalHumano();
});
document.getElementById('formBuscarCapitalHumano').onsubmit = function(e) {
    e.preventDefault();
    filtrarBajasCapitalHumanoCH();
};
document.getElementById('btnDescargarCapitalHumano').onclick = function() {
    const inicio = document.getElementById('fechaInicioCH').value;
    const fin = document.getElementById('fechaFinCH').value;
    let url = `/Reporteria/descargarBajasExcelCapitalHumano`;
    if (inicio || fin) {
        url += `?fecha_inicio=${encodeURIComponent(inicio)}&fecha_fin=${encodeURIComponent(fin)}`;
    }
    window.open(url, '_blank');
};
</script>
<style>
.modal-xl { max-width: 95%; }
#tablaCapitalHumano th { position: sticky; top: 0; background: #f8f9fa; font-size: 0.85rem; font-weight: 600; white-space: nowrap; z-index: 10; }
#tablaCapitalHumano td { font-size: 0.85rem; vertical-align: top; padding: 0.75rem; text-align: center; }
#tablaCapitalHumano tbody tr:hover { background-color: rgba(13, 110, 253, 0.05); }
.table-responsive { max-height: 500px; overflow: auto; }
.dataTables_wrapper .dataTables_paginate .paginate_button { border: 1px solid #dee2e6 !important; margin-left: 2px !important; border-radius: 0.25rem !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button.current { background: #0d6efd !important; color: white !important; border-color: #0d6efd !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button:hover { background: #e9ecef !important; color: #0d6efd !important; }
</style>
