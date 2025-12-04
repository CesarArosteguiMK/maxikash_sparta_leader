<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Consulta de Estado de Cuenta</h4>
            <p class="text-muted small">Busca por nombre o por ID de crédito</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card">

        <!-- Filtros -->
        <div class="row justify-content-between m-4">

            <div class="col-8">
                <label class="form-label">Filtro</label>
                <div class="input-group input-group-merge">

                    <div class="form-check form-check-inline me-3">
                        <input class="form-check-input" type="radio" name="modoBusqueda" id="modoID" value="id"
                            <?= (!isset($_POST['modoBusqueda']) || $_POST['modoBusqueda'] === 'id') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="modoID">ID de crédito</label>
                    </div>

                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="modoBusqueda" id="modoNombre" value="nombre"
                            <?= (isset($_POST['modoBusqueda']) && $_POST['modoBusqueda'] === 'nombre') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="modoNombre">Nombre del cliente</label>
                    </div>

                </div>
            </div>

            <div class="col-4 d-flex align-items-end justify-content-end">
                <button id="btnResetFiltros" class="btn btn-outline-secondary me-2" type="button">Limpiar</button>
            </div>
        </div>

        <!-- FORMULARIO -->
        <div class="card-body">
            <form method="POST" id="formBusqueda">

                <div class="row g-3 align-items-end">

                    <!-- ID -->
                    <div class="col-md-6" id="divID">
                        <label for="idCredito" class="form-label">ID de crédito</label>
                        <div class="input-group input-group-merge">
                            <input type="number" class="form-control" id="idCredito" name="idCredito"
                                   value="<?= $_POST['idCredito'] ?? '' ?>"
                                   placeholder="Ej.: 12345">
                            <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-6" id="divNombre">
                        <label for="nombre" class="form-label">Nombre del Cliente</label>
                        <div class="input-group input-group-merge">
                            <input type="text" class="form-control" name="nombre" id="nombre"
                                   value="<?= $_POST['nombre'] ?? '' ?>"
                                   placeholder="Nombre completo o parcial">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                        </div>
                    </div>

                    <!-- Fecha Corte oculta -->
                    <input type="hidden" name="fechaCorte" id="fechaCorte" value="<?= $fecha_actual_iso ?>">

                    <div class="col-12">
                        <button type="submit" class="btn btn-outline-primary w-100" id="btnBuscar">Buscar</button>
                    </div>
                </div>

            </form>
        </div>

        <!-- RESULTADOS -->
        <div class="card-body pt-0">

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (!empty($resultados)) : ?>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 class="mb-3">Resultados de tu búsqueda</h6>

                        <form method="POST" id="formSeleccionCredito">
                            <input type="hidden" name="idCredito" id="selectedCredito">

                            <div class="table-responsive card-datatable">
                                <table id="tablaResultados" class="table table-hover table-bordered dt-responsive nowrap w-100">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        <th>ID Crédito</th>
                                        <th>Nombre Cliente</th>
                                        <th>Registro</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($resultados as $i => $r) : ?>
                                        <tr data-credito="<?= $r['id_credito'] ?>">
                                            <td class="align-middle text-center">
                                                <div class="form-check">
                                                    <input class="form-check-input seleccionarCredito"
                                                           type="radio" name="selCredito"
                                                           value="<?= $r['id_credito'] ?>">
                                                </div>
                                            </td>
                                            <td class="align-middle"><?= $r['id_credito'] ?></td>
                                            <td class="align-middle"><?= htmlspecialchars($r['Nombre_cliente']) ?></td>
                                            <td class="align-middle"><?= $r['id_cliente'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-between mt-3">
                                <small class="text-muted">Selecciona un crédito y presiona consultar.</small>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-secondary" id="btnCancelarSeleccion">
                                        Cancelar
                                    </button>

                                    <button type="submit" class="btn btn-info" id="btnConsultarCredito" disabled>
                                        <i class="fa fa-search me-1"></i> Consultar Estado de Cuenta
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            <?php else : ?>
                <div class="text-center py-3">
                    <span class="text-muted">No hay resultados para los filtros actuales.</span>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
