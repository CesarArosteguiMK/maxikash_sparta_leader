<?php
$departamento = $departamento ?? ['datos' => []];
?>
<div class="content-wrapper">
    <div class="card">
        <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="card-title mb-0">Candidatos</h5>
            <div class="d-flex align-items-center gap-2">
                <select id="filterEstatus" class="form-select form-select-sm" style="max-width: 180px;">
                    <option value="">Todos los estatus</option>
                    <option value="Por evaluar">Por evaluar</option>
                    <option value="En entrevista">En entrevista</option>
                    <option value="Contratado">Contratado</option>
                    <option value="Descartado">Descartado</option>
                </select>
                <button type="button" class="btn btn-primary btn-action-size" id="btnFiltrarCandidatos" onclick="getCandidatos()">
                    <i class="fa fa-filter"></i>
                    <span class="d-inline-block">Filtrar</span>
                </button>
                <button type="button" class="btn btn-primary btn-action-size" data-bs-toggle="modal" data-bs-target="#modalAgregarCandidato">
                    <i class="fa fa-plus"></i>
                    <span class="d-inline-block">Agregar candidato</span>
                </button>
            </div>
        </div>
        <div class="card-body">
            <table id="tablaCandidatos" class="table table-bordered table-hover dt-responsive border-top" style="width:100%">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Puesto / Departamento interés</th>
                        <th>Estatus</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Agregar candidato -->
<div class="modal fade" id="modalAgregarCandidato" tabindex="-1" aria-labelledby="modalAgregarCandidatoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarCandidatoLabel">Agregar candidato</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formAgregarCandidato">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label">Nombres <span class="text-danger">*</span></label>
                        <input type="text" name="nombres" id="candidato_nombres" class="form-control" required maxlength="100" placeholder="Ej. Juan Carlos">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Segundo nombre</label>
                        <input type="text" name="segundo_nombre" id="candidato_segundo_nombre" class="form-control" maxlength="100">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Apellido paterno <span class="text-danger">*</span></label>
                        <input type="text" name="apellidop" id="candidato_apellidop" class="form-control" required maxlength="100">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Apellido materno</label>
                        <input type="text" name="apellidom" id="candidato_apellidom" class="form-control" maxlength="100">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Correo</label>
                        <input type="email" name="email" id="candidato_email" class="form-control" maxlength="150">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="telefono" id="candidato_telefono" class="form-control" maxlength="20">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Departamento de interés</label>
                        <select name="id_departamento" id="candidato_id_departamento" class="form-select">
                            <option value="">Seleccione un departamento</option>
                            <?php foreach ($departamento['datos'] as $d): ?>
                                <option value="<?= (int)($d['id'] ?? 0) ?>"><?= htmlspecialchars($d['nombre'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Puesto de interés</label>
                        <select name="id_puesto" id="candidato_id_puesto" class="form-select">
                            <option value="">Seleccione un puesto</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Notas</label>
                        <textarea name="notas" id="candidato_notas" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.btn-action-size { height: 36px; padding: 0.375rem 0.75rem; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.375rem; }
#tablaCandidatos thead th { background-color: rgba(105, 108, 255, 0.1); font-weight: 600; }
</style>

<script>
(function(){
    if (typeof $ !== "undefined" && $.fn.DataTable) {
        $("#filterEstatus").on("change", function() { getCandidatos(); });
        if (!$("#tablaCandidatos").length) return;
        if ($.fn.DataTable.isDataTable("#tablaCandidatos")) return;
        $("#tablaCandidatos").DataTable({
            responsive: true,
            order: [[0, "asc"]],
            language: { url: "/assets/vendor/libs/datatables-bs5/i18n/es-ES.mjs" },
            columnDefs: [ { orderable: false, targets: 4 } ]
        });
    }
})();
</script>
