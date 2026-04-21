<style>
    .badge-app {
        color: #fff;
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 10px;
        font-weight: 700;
        margin-right: 8px;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        box-shadow: 0 0 4px rgba(0,0,0,0.15);
        display: inline-block;
    }

    /* SKY LOGIC → VERDE */
    .badge-app.sky-logic {
        background-color: #d2d755;
    }

    /* LEGACY → AZUL */
    .badge-app.legacy {
        background-color: #0047BB;
    }
</style>


<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Consulta de gestiones Campo</h4>
            <p class="text-muted small">Resultados de la búsqueda</p>
        </div>
    </div>

    <?php $r = $detalle[0] ?? []; ?>

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
                        <?php /* CAMPOS OPCIONALES - Descomentar si se requieren:
                        <th>Pagos Vencidos</th>
                        <th>Deuda Total</th>
                        */ ?>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?= $r["Nombre_cliente"] ?? "" ?></td>
                        <td><?= $r["id_credito"] ?? "" ?></td>
                        <td><?= $r["Codigo_postal_1"] ?? "" ?></td>
                        <td><?= $r["Celular"] ?? "" ?></td>
                        <td><?= $r["Referencia_stp"] ?? "" ?></td>
                        <td><?= $r["cuota"] ?? "" ?></td>
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
                               <?php
                               $esTelefono = (mb_strtoupper((string)($g['contacto'] ?? '')) === 'TELEFONO');
                               $emojiGestion = $esTelefono ? '📞' : '🛵';
                               ?>
                               <span class="badge-app <?= ($g['app'] === 'LEGACY') ? 'legacy' : 'sky-logic' ?>">
                                    <?= $g["app"] ?>
                                </span>
                                <span class="me-2" title="<?= $esTelefono ? 'Gestión telefónica' : 'Gestión campo' ?>"><?= $emojiGestion ?></span>
                                <i class="fa fa-briefcase me-1 ms-1" title="Portafolio" aria-hidden="true"></i>
                                <?= $g["nombre_cliente"] ?> —
                                <?= mb_strtoupper($g["contacto"]) ?> —
                                <?= (mb_strtoupper($g["contacto"]) == "TELEFONO")
                                        ? mb_strtoupper($g["dictamen_ccc"]) . " — "
                                        : mb_strtoupper($g["dictamen_campo"]) . " — " ?>

                                <?= $g["fecha_dispositivo"]?> —
                                <?= $g["nombre_base"] ?>
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
                                            <th>Nombre Base</th>
                                            <th>Fecha carga base</th>
                                            <th>Usuario asignado</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?= $g["team_supervisor"] ?></td>
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
                                    <?php
                                    $medioContactacion = $g["medio_contactacion_ccc"] ?: $g["medio_contactacion_campo"] ?: '—';
                                    $dictamen = $g["dictamen_ccc"] ?: $g["dictamen_campo"] ?: '—';
                                    ?>
                                    <h6 class="fw-bold">Contactación y dictamen</h6>

                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-striped">
                                            <thead class="table-light">
                                            <tr>
                                                <th>MEDIO DE CONTACTACION</th>
                                                <th>DICTAMEN</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td><?= htmlspecialchars($medioContactacion) ?></td>
                                                <td><?= htmlspecialchars($dictamen) ?></td>
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
                                            <th>Motivo negativa</th>
                                            <th>Motivo atraso</th>
                                            <th>Comentarios</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td><?= htmlspecialchars((string)($g["promesa_pago"] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string)($g["motivo_negativa"] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= htmlspecialchars((string)($g["porque_atraso_pago"] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                            <td><?= nl2br(htmlspecialchars((string)($g["comentarios_generales"] ?? ''), ENT_QUOTES, 'UTF-8')) ?></td>
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
<?php require __DIR__ . '/partials/gestiones_db_fallos_console.php'; ?>
