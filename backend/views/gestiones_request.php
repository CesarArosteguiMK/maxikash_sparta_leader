<body>

<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Consulta de gestiones Sky</h4>
            <p class="text-muted small">Resultados de la búsqueda</p>
        </div>
    </div>

    <?php
    // Primer registro para el resumen
    $r = $gestiones[0];
    ?>

    <!-- CARD GLOBAL -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Resumen general del cliente</h5>
                <a href="/gestiones/seguimiento/" class="btn btn-outline-secondary">Nueva consulta</a>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                    <tr>
                        <th>Nombre Cliente</th>
                        <th>ID Crédito</th>
                        <th>CP</th>
                        <th>Teléfono</th>
                        <th>Cuenta CLABE</th>
                        <th>Pago Semanal</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?= $r["nombre_completo_cliente"] ?></td>
                        <td><?= $r["id_credito"] ?></td>
                        <td><?= $r["cp"] ?></td>
                        <td><?= $r["telefono_celular"] ?></td>
                        <td><?= $r["cuenta_clabe"] ?></td>
                        <td><?= $r["pago_semanal"] ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>


    <!-- ACORDEONES -->
    <div class="card shadow-sm">
        <div class="card-body">

            <h5 class="mb-3">Detalle por cliente</h5>

            <div class="accordion" id="accordionClientes">

                <?php $i = 1; foreach ($gestiones as $g): ?>

                    <div class="accordion-item mb-2 border">

                        <h2 class="accordion-header" id="heading<?= $i ?>">
                            <button class="accordion-button collapsed" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse<?= $i ?>">
                                <?= $g["nombre_cliente"] ?> —
                                <?= $g["dictamen_ccc"] ?> —
                                <?= $g["fecha_dispositivo"] ?> —
                                <?= $g["nombre_base"] ?> —
                                <?= ($g["medio_contactacion_ccc"] == "0") ? "CAMPO" : "TELEFONICO" ?>
                            </button>
                        </h2>

                        <div id="collapse<?= $i ?>"
                             class="accordion-collapse collapse"
                             data-bs-parent="#accordionClientes">

                            <div class="accordion-body">

                                <!-- IDENTIFICACIÓN -->
                                <h6 class="fw-bold">Identificación y asignación</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Supervisor</th>
                                            <th>ID Base</th>
                                            <th>Nombre Base</th>
                                            <th>Fecha Carga</th>
                                            <th>Usuario asignado</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?= $g["team_supervisor"] ?></td>
                                            <td><?= $g["id_base"] ?></td>
                                            <td><?= $g["nombre_base"] ?></td>
                                            <td><?= $g["fecha_carga_base"] ?></td>
                                            <td><?= $g["usuario_asignado"] ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>


                                <!-- CONTACTACIÓN -->
                                <?php if (
                                    $g["medio_contactacion_ccc"] ||
                                    $g["medio_contactacion_campo"] ||
                                    $g["dictamen_campo"] ||
                                    $g["dictamen_ccc"]
                                ): ?>

                                    <h6 class="fw-bold">Contactación y dictamen</h6>

                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-light">
                                            <tr>
                                                <?php if ($g["medio_contactacion_ccc"]): ?><th>Medio CCC</th><?php endif; ?>
                                                <?php if ($g["dictamen_ccc"]): ?><th>Dictamen CCC</th><?php endif; ?>
                                                <?php if ($g["medio_contactacion_campo"]): ?><th>Medio Campo</th><?php endif; ?>
                                                <?php if ($g["dictamen_campo"]): ?><th>Dictamen Campo</th><?php endif; ?>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <?php if ($g["medio_contactacion_ccc"]): ?><td><?= $g["medio_contactacion_ccc"] ?></td><?php endif; ?>
                                                <?php if ($g["dictamen_ccc"]): ?><td><?= $g["dictamen_ccc"] ?></td><?php endif; ?>
                                                <?php if ($g["medio_contactacion_campo"]): ?><td><?= $g["medio_contactacion_campo"] ?></td><?php endif; ?>
                                                <?php if ($g["dictamen_campo"]): ?><td><?= $g["dictamen_campo"] ?></td><?php endif; ?>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                <?php endif; ?>


                                <!-- PROMESAS -->
                                <h6 class="fw-bold">Promesas y comentarios</h6>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-striped">
                                        <thead class="table-light">
                                        <tr>
                                            <th>Promesa pago</th>
                                            <th>Motivo atraso</th>
                                            <th>Comentarios</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?= $g["promesa_pago"] ?></td>
                                            <td><?= $g["porque_atraso_pago"] ?></td>
                                            <td><?= $g["comentarios_generales"] ?></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- MULTIMEDIA -->
                                <h6 class="fw-bold">Documentos y multimedia</h6>

                                <?php if (!empty($g["images"])): ?>
                                    <p class="small">
                                        Imagen:
                                        <a href="<?= $g["images"] ?>" target="_blank">
                                            <?= $g["images"] ?>
                                        </a>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($g["ubicacion_usuario"])): ?>
                                    <p class="small">
                                        Ubicación:
                                        <a href="https://www.google.com/maps?q=<?= $g["ubicacion_usuario"] ?>" target="_blank">
                                            Ver en mapa
                                        </a>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </div>

                    </div>

                    <?php $i++; endforeach; ?>

            </div>

        </div>
    </div>


</div>

</body>
