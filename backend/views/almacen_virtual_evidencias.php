<link rel="stylesheet" href="/assets/css/almacen-virtual-dark.css?v=20260716">

<style>
    .ave-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .ave-head {
        border: 1px solid #dbe4ef;
        background: #f8fafc;
        border-radius: .5rem;
        padding: 1rem 1.15rem;
    }
    .ave-head-icon {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: .5rem;
        background: #fff7ed;
        color: #c2410c;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.15rem;
    }
    .ave-sync-status {
        color: #64748b;
        font-size: .74rem;
        min-height: 1rem;
        margin-top: .25rem;
    }
    .ave-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .ave-kpi {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
        padding: .85rem;
        min-height: 5.3rem;
    }
    .ave-kpi-label {
        color: #64748b;
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
    }
    .ave-kpi-value {
        color: #1e293b;
        font-size: 1.55rem;
        font-weight: 800;
        line-height: 1.1;
        margin-top: .35rem;
    }
    .ave-toolbar,
    .ave-table-wrap,
    .ave-modal-summary {
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: .5rem;
    }
    .ave-toolbar {
        padding: .85rem;
    }
    .ave-table-wrap {
        overflow: hidden;
    }
    .ave-table {
        margin-bottom: 0;
    }
    .ave-table th {
        white-space: nowrap;
    }
    .ave-table td {
        vertical-align: middle;
    }
    .ave-datatable-controls {
        padding: .85rem 1rem .35rem;
        background: #fff;
    }
    .ave-datatable-controls .dataTables_length label {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        color: #697a8d;
        font-size: .875rem;
        margin: 0;
    }
    .ave-datatable-controls .dataTables_length select {
        width: auto;
        min-width: 4.75rem;
    }
    .ave-datatable-footer {
        border-top: 1px solid #d9dee3;
        padding: .75rem 1rem;
        background: #fff;
    }
    .ave-datatable-footer .dataTables_info {
        color: #697a8d;
        font-size: .875rem;
        padding-top: .45rem;
    }
    .ave-datatable-footer .pagination {
        justify-content: flex-end;
        margin: 0;
        gap: .25rem;
    }
    .ave-datatable-footer .page-link {
        min-width: 2rem;
        text-align: center;
        border-radius: .375rem;
    }
    .ave-unit-main {
        color: #1e293b;
        font-weight: 800;
    }
    .ave-unit-sub {
        color: #64748b;
        font-size: .74rem;
    }
    .ave-status {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border-radius: 999px;
        padding: .24rem .58rem;
        font-size: .72rem;
        font-weight: 800;
        white-space: nowrap;
    }
    .ave-status-pendiente_evidencias { background: #fef3c7; color: #92400e; }
    .ave-status-pendiente_entrega_cedis { background: #dbeafe; color: #1d4ed8; }
    .ave-status-incidencia_evidencias { background: #fee2e2; color: #991b1b; }
    .ave-status-pendiente_recepcion { background: #dcfce7; color: #15803d; }
    .ave-status-recolectada,
    .ave-status-recolectado,
    .ave-status-completado,
    .ave-status-completada { background: #dcfce7; color: #15803d; }
    .ave-status-default { background: #eef2ff; color: #3730a3; }
    .ave-empty {
        padding: 2.25rem 1rem;
        text-align: center;
        color: #64748b;
    }
    .ave-empty i {
        display: block;
        font-size: 2rem;
        opacity: .35;
        margin-bottom: .65rem;
    }
    .ave-modal-summary {
        background: #f8fafc;
        padding: .85rem;
    }
    .ave-code-box {
        border: 1px dashed #f59e0b;
        background: #fffbeb;
        border-radius: .5rem;
        padding: .75rem;
    }
    .ave-code-value {
        color: #92400e;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: 0;
    }
    .ave-evidence-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }
    .ave-file-card {
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        padding: .75rem;
        background: #fff;
    }
    .ave-form-section {
        border: 1px solid #e2e8f0;
        border-radius: .5rem;
        background: #fff;
        padding: .85rem;
    }
    .ave-section-title {
        color: #1e293b;
        font-size: .82rem;
        font-weight: 800;
        margin-bottom: .7rem;
        text-transform: uppercase;
    }
    .ave-required-dot {
        color: #dc2626;
        font-weight: 900;
    }
    .ave-category-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .5rem;
    }
    .ave-category-option {
        border: 1px solid #e2e8f0;
        border-radius: .45rem;
        padding: .55rem .65rem;
        min-height: 3.1rem;
        display: flex;
        align-items: center;
        gap: .5rem;
        background: #fff;
    }
    .ave-category-option input {
        flex-shrink: 0;
    }
    .ave-file-card.optional {
        background: #f8fafc;
    }
    .ave-file-hint {
        color: #64748b;
        font-size: .72rem;
        margin-top: .25rem;
    }
    @media (max-width: 992px) {
        .ave-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (max-width: 576px) {
        .ave-kpi-grid,
        .ave-evidence-grid,
        .ave-category-grid {
            grid-template-columns: 1fr;
        }
        .ave-head {
            padding: .9rem;
        }
        .ave-datatable-controls .dataTables_length label {
            width: 100%;
            justify-content: space-between;
        }
        .ave-datatable-footer .dataTables_info,
        .ave-datatable-footer .pagination {
            justify-content: center;
            text-align: center;
        }
    }
</style>

<div class="ave-shell">
    <section class="ave-head d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div class="d-flex align-items-start gap-3">
            <span class="ave-head-icon"><i class="fa-solid fa-camera"></i></span>
            <div>
                <h4 class="mb-1">Evidencias y Codigo</h4>
                <div class="text-muted small">Validacion previa a recepcion: 4 angulos, VIN/NIV y codigo unico de verificacion.</div>
                <div class="ave-sync-status" id="ave-sync-status"></div>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="/MotosAdjudicadas/almacenVirtual" class="btn btn-label-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i>Almacen Virtual
            </a>
            <a href="/MotosAdjudicadas/recepcionAlmacen" class="btn btn-label-success btn-sm">
                <i class="fa-solid fa-clipboard-check me-1"></i>Recepcion
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" id="ave-btn-refresh">
                <i class="fa-solid fa-rotate-right me-1"></i>Actualizar
            </button>
        </div>
    </section>

    <section class="ave-kpi-grid">
        <div class="ave-kpi">
            <div class="ave-kpi-label">Pendientes</div>
            <div class="ave-kpi-value" id="ave-kpi-pendientes">0</div>
        </div>
        <div class="ave-kpi">
            <div class="ave-kpi-label">Incidencias</div>
            <div class="ave-kpi-value" id="ave-kpi-incidencias">0</div>
        </div>
        <div class="ave-kpi">
            <div class="ave-kpi-label">Listas recepcion</div>
            <div class="ave-kpi-value" id="ave-kpi-listas">0</div>
        </div>
        <div class="ave-kpi">
            <div class="ave-kpi-label">Abiertas</div>
            <div class="ave-kpi-value" id="ave-kpi-abiertas">0</div>
        </div>
    </section>

    <section class="ave-toolbar">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label small fw-bold" for="ave-q">Buscar</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" class="form-control" id="ave-q" placeholder="Folio, VIN, motor, placa">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-bold" for="ave-celula">Celula</label>
                <select class="form-select form-select-sm" id="ave-celula">
                    <option value="">Todas</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small fw-bold" for="ave-estatus">Estatus</label>
                <select class="form-select form-select-sm" id="ave-estatus">
                    <option value="abiertas" selected>Abiertas</option>
                    <option value="pendiente_evidencias">Pendiente evidencias</option>
                    <option value="incidencia_evidencias">Incidencia evidencias</option>
                    <option value="pendiente_recepcion">Listas recepcion</option>
                </select>
            </div>
            <div class="col-12 col-lg-2 d-grid">
                <button type="button" class="btn btn-primary btn-sm" id="ave-btn-filtrar">
                    <i class="fa-solid fa-filter me-1"></i>Filtrar
                </button>
            </div>
        </div>
    </section>

    <section class="ave-table-wrap">
        <div class="ave-datatable-controls row mx-0 align-items-center">
            <div class="col-sm-12 col-md-6">
                <div class="dataTables_length">
                    <label>
                        Mostrar
                        <select id="ave-limit" class="form-select form-select-sm">
                            <option value="8" selected>8</option>
                            <option value="16">16</option>
                            <option value="32">32</option>
                            <option value="64">64</option>
                        </select>
                        registros
                    </label>
                </div>
            </div>
        </div>
        <div class="card-datatable table-responsive">
            <table class="dt-responsive table border-top ave-table">
                <thead>
                    <tr>
                        <th>Unidad</th>
                        <th>Celula</th>
                        <th>Identificacion</th>
                        <th>Recoleccion</th>
                        <th>Evidencias</th>
                        <th>Codigo</th>
                        <th>Estatus</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody id="ave-unidades-body">
                    <tr>
                        <td colspan="8" class="ave-empty">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                            Cargando unidades...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="ave-datatable-footer row mx-0 align-items-center">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" id="ave-pager-info">Mostrando 0 a 0 de 0 unidades</div>
            </div>
            <div class="col-sm-12 col-md-7">
                <div class="dataTables_paginate paging_simple_numbers">
                    <ul class="pagination" id="ave-pagination"></ul>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="ave-modal-evidencias" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <form class="modal-content" id="ave-form-evidencias" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title">Validar evidencias y codigo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_unidad" id="ave-id-unidad">
                <div class="ave-modal-summary mb-3" id="ave-modal-summary"></div>
                <div class="row g-3">
                    <div class="col-12 col-lg-5">
                        <div class="ave-form-section mb-3">
                            <div class="ave-section-title">Verificacion</div>
                            <div class="ave-code-box mb-3">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div>
                                        <div class="text-muted small fw-bold text-uppercase">Codigo vigente</div>
                                        <div class="ave-code-value" id="ave-codigo-actual">Sin generar</div>
                                        <div class="ave-unit-sub" id="ave-codigo-expira"></div>
                                    </div>
                                    <button type="button" class="btn btn-warning btn-sm" id="ave-btn-generar-codigo">
                                        <i class="fa-solid fa-key me-1"></i>Generar
                                    </button>
                                </div>
                            </div>
                            <label class="form-label small fw-bold" for="ave-codigo-input">Codigo de verificacion <span class="ave-required-dot">*</span></label>
                            <input type="text" class="form-control mb-3" name="codigo_verificacion" id="ave-codigo-input" autocomplete="off" required>
                            <label class="form-label small fw-bold" for="ave-vin">VIN/NIV <span class="ave-required-dot">*</span></label>
                            <input type="text" class="form-control" name="vin" id="ave-vin" maxlength="17" autocomplete="off" required>
                        </div>

                        <div class="ave-form-section mb-3">
                            <div class="ave-section-title">Fisicos y documentos</div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Tiene llave fisica <span class="ave-required-dot">*</span></label>
                                    <select class="form-select" name="tiene_llave_fisica" id="ave-llave" required>
                                        <option value="">Seleccionar</option>
                                        <option value="si">Si</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Tiene tarjeta de circulacion <span class="ave-required-dot">*</span></label>
                                    <select class="form-select" name="tiene_tarjeta_circulacion" id="ave-tarjeta" required>
                                        <option value="">Seleccionar</option>
                                        <option value="si">Si</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">La moto tiene placa fisica <span class="ave-required-dot">*</span></label>
                                    <select class="form-select" name="tiene_placa_fisica" id="ave-placa-fisica" required>
                                        <option value="">Seleccionar</option>
                                        <option value="si">Si</option>
                                        <option value="no">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="ave-form-section">
                            <div class="ave-section-title">Comentarios</div>
                            <label class="form-label small fw-bold" for="ave-comentarios-generales">Comentarios generales <span class="ave-required-dot">*</span></label>
                            <textarea class="form-control mb-3" name="comentarios_generales" id="ave-comentarios-generales" rows="4" maxlength="1500" required></textarea>
                            <label class="form-label small fw-bold" for="ave-observaciones">Observaciones internas</label>
                            <textarea class="form-control" name="observaciones" id="ave-observaciones" rows="3" maxlength="1000"></textarea>
                            <div class="mt-3" id="ave-evidencias-actuales"></div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="ave-form-section mb-3">
                            <div class="ave-section-title">Ficha tecnica</div>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold" for="ave-tipo-unidad">Tipo de moto <span class="ave-required-dot">*</span></label>
                                    <select class="form-select" name="tipo_unidad" id="ave-tipo-unidad" required>
                                        <option value="">Seleccionar</option>
                                        <option value="2_ruedas">2 ruedas</option>
                                        <option value="3_ruedas">3 ruedas</option>
                                        <option value="cuatrimoto">Cuatrimoto</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label small fw-bold" for="ave-tipo-motor">Tipo de motor <span class="ave-required-dot">*</span></label>
                                    <select class="form-select" name="tipo_motor" id="ave-tipo-motor" required>
                                        <option value="">Seleccionar</option>
                                        <option value="combustion">Combustion</option>
                                        <option value="electrica">Electrica</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Categoria <span class="ave-required-dot">*</span></label>
                                    <div class="ave-category-grid">
                                        <label class="ave-category-option"><input class="form-check-input" type="radio" name="categoria" value="naked" required> Naked</label>
                                        <label class="ave-category-option"><input class="form-check-input" type="radio" name="categoria" value="deportivas"> Deportivas</label>
                                        <label class="ave-category-option"><input class="form-check-input" type="radio" name="categoria" value="doble_proposito"> Doble proposito</label>
                                        <label class="ave-category-option"><input class="form-check-input" type="radio" name="categoria" value="cross_enduro"> Cross/Enduro</label>
                                        <label class="ave-category-option"><input class="form-check-input" type="radio" name="categoria" value="custom"> Custom</label>
                                        <label class="ave-category-option"><input class="form-check-input" type="radio" name="categoria" value="scrambler"> Scrambler</label>
                                        <label class="ave-category-option"><input class="form-check-input" type="radio" name="categoria" value="scooter"> Scooter</label>
                                        <label class="ave-category-option"><input class="form-check-input" type="radio" name="categoria" value="touring"> Touring</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 ave-combustion-fields d-none">
                                    <label class="form-label small fw-bold" for="ave-combustion-tipo">Combustion <span class="ave-required-dot">*</span></label>
                                    <select class="form-select" name="tipo_motor_combustion" id="ave-combustion-tipo">
                                        <option value="">Seleccionar</option>
                                        <option value="carburador">Carburador</option>
                                        <option value="full_inyeccion">Full inyeccion</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 ave-combustion-fields d-none">
                                    <label class="form-label small fw-bold" for="ave-cilindraje">Cilindraje <span class="ave-required-dot">*</span></label>
                                    <select class="form-select" name="cilindraje" id="ave-cilindraje">
                                        <option value="">Seleccionar</option>
                                        <option value="50_cc">50 CC</option>
                                        <option value="100_cc">100 CC</option>
                                        <option value="110_cc">110 CC</option>
                                        <option value="125_cc">125 CC</option>
                                        <option value="150_cc">150 CC</option>
                                        <option value="160_cc">160 CC</option>
                                        <option value="170_cc">170 CC</option>
                                        <option value="180_cc">180 CC</option>
                                        <option value="200_cc">200 CC</option>
                                        <option value="210_cc">210 CC</option>
                                        <option value="220_cc">220 CC</option>
                                        <option value="250_cc">250 CC</option>
                                        <option value="300_cc">300 CC</option>
                                        <option value="400_cc">400 CC</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-12 ave-cilindraje-otro d-none">
                                    <label class="form-label small fw-bold" for="ave-cilindraje-otro">Cilindraje otro</label>
                                    <input type="text" class="form-control" name="cilindraje_otro" id="ave-cilindraje-otro" maxlength="50">
                                </div>
                                <div class="col-12 col-md-6 ave-electrica-fields d-none">
                                    <label class="form-label small fw-bold" for="ave-potencia">Potencia <span class="ave-required-dot">*</span></label>
                                    <select class="form-select" name="potencia" id="ave-potencia">
                                        <option value="">Seleccionar</option>
                                        <option value="5_kw">5 KW</option>
                                        <option value="8_kw">8 KW</option>
                                        <option value="9_kw">9 KW</option>
                                        <option value="10_kw">10 KW</option>
                                        <option value="11_kw">11 KW</option>
                                        <option value="12_kw">12 KW</option>
                                        <option value="13_kw">13 KW</option>
                                        <option value="15_kw">15 KW</option>
                                        <option value="otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 ave-potencia-otro d-none">
                                    <label class="form-label small fw-bold" for="ave-potencia-otro">Potencia otra</label>
                                    <input type="text" class="form-control" name="potencia_otro" id="ave-potencia-otro" maxlength="50">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold" for="ave-otro-descripcion">Otro</label>
                                    <input type="text" class="form-control" name="otro_descripcion" id="ave-otro-descripcion" maxlength="255">
                                </div>
                            </div>
                        </div>

                        <div class="ave-form-section">
                            <div class="ave-section-title">Evidencias</div>
                            <div class="ave-evidence-grid">
                                <div class="ave-file-card optional">
                                    <label class="form-label small fw-bold" for="ev_foto_dacion_hoja_1">Dacion hoja 1</label>
                                    <input type="file" class="form-control" name="ev_foto_dacion_hoja_1" id="ev_foto_dacion_hoja_1" accept="image/*">
                                    <div class="ave-file-hint">Opcional</div>
                                </div>
                                <div class="ave-file-card optional">
                                    <label class="form-label small fw-bold" for="ev_foto_dacion_hoja_2">Dacion hoja 2</label>
                                    <input type="file" class="form-control" name="ev_foto_dacion_hoja_2" id="ev_foto_dacion_hoja_2" accept="image/*">
                                    <div class="ave-file-hint">Opcional</div>
                                </div>
                                <div class="ave-file-card">
                                    <label class="form-label small fw-bold" for="ev_foto_tacometro">Foto tacometro <span class="ave-required-dot">*</span></label>
                                    <input type="file" class="form-control" name="ev_foto_tacometro" id="ev_foto_tacometro" accept="image/*">
                                </div>
                                <div class="ave-file-card">
                                    <label class="form-label small fw-bold" for="ev_foto_vin">Foto VIN/NIV <span class="ave-required-dot">*</span></label>
                                    <input type="file" class="form-control" name="ev_foto_vin" id="ev_foto_vin" accept="image/*">
                                </div>
                                <div class="ave-file-card">
                                    <label class="form-label small fw-bold" for="ev_foto_frontal">Foto frontal <span class="ave-required-dot">*</span></label>
                                    <input type="file" class="form-control" name="ev_foto_frontal" id="ev_foto_frontal" accept="image/*">
                                </div>
                                <div class="ave-file-card">
                                    <label class="form-label small fw-bold" for="ev_foto_trasera">Foto trasera <span class="ave-required-dot">*</span></label>
                                    <input type="file" class="form-control" name="ev_foto_trasera" id="ev_foto_trasera" accept="image/*">
                                </div>
                                <div class="ave-file-card">
                                    <label class="form-label small fw-bold" for="ev_foto_lateral_derecha">Foto lateral derecha <span class="ave-required-dot">*</span></label>
                                    <input type="file" class="form-control" name="ev_foto_lateral_derecha" id="ev_foto_lateral_derecha" accept="image/*">
                                </div>
                                <div class="ave-file-card">
                                    <label class="form-label small fw-bold" for="ev_foto_lateral_izquierda">Foto lateral izquierda <span class="ave-required-dot">*</span></label>
                                    <input type="file" class="form-control" name="ev_foto_lateral_izquierda" id="ev_foto_lateral_izquierda" accept="image/*">
                                </div>
                                <div class="ave-file-card">
                                    <label class="form-label small fw-bold" for="ev_video_360_encendida">Video 360 encendida <span class="ave-required-dot">*</span></label>
                                    <input type="file" class="form-control" name="ev_video_360_encendida" id="ev_video_360_encendida" accept="video/mp4,video/quicktime,video/webm">
                                </div>
                                <div class="ave-file-card optional">
                                    <label class="form-label small fw-bold" for="ev_foto_checklist">Foto checklist</label>
                                    <input type="file" class="form-control" name="ev_foto_checklist" id="ev_foto_checklist" accept="image/*">
                                    <div class="ave-file-hint">Opcional</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning" id="ave-btn-validar">
                    <i class="fa-solid fa-shield-check me-1"></i>Validar evidencias
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const state = {
        page: 1,
        limit: 8,
        pages: 1,
        total: 0,
        timer: null,
        rows: new Map(),
        currentId: 0,
    };

    const $ = (id) => document.getElementById(id);

    function esc(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function notify(icon, title, text) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({ icon, title, text });
            return;
        }
        window.alert((title ? title + '\n' : '') + (text || ''));
    }

    function statusLabel(value) {
        const map = {
            pendiente_evidencias: 'Pendiente evidencias',
            incidencia_evidencias: 'Incidencia evidencias',
            pendiente_recepcion: 'Lista recepcion',
            recolectada: 'Recolectada',
            recolectado: 'Recolectada',
            completado: 'Recolectada',
            completada: 'Recolectada',
        };
        return map[value] || value || 'Sin estatus';
    }

    function statusHtml(value) {
        const key = String(value || 'default').replace(/[^a-z0-9_]/gi, '_');
        const safe = ['pendiente_evidencias','incidencia_evidencias','pendiente_recepcion','recolectada','recolectado','completado','completada'].includes(key)
            ? 'ave-status-' + key
            : 'ave-status-default';
        return '<span class="ave-status ' + safe + '"><i class="fa-solid fa-circle"></i>' + esc(statusLabel(value)) + '</span>';
    }

    function setSiNo(id, value) {
        const el = $(id);
        if (!el) return;
        if (value === 1 || value === '1' || value === true || String(value).toLowerCase() === 'si') {
            el.value = 'si';
        } else if (value === 0 || value === '0' || value === false || String(value).toLowerCase() === 'no') {
            el.value = 'no';
        } else {
            el.value = '';
        }
    }

    function setRadio(name, value) {
        document.querySelectorAll('input[type="radio"][name="' + name + '"]').forEach((input) => {
            input.checked = String(input.value) === String(value || '');
        });
    }

    function setSelectValue(id, value) {
        const el = $(id);
        if (!el) return;
        const raw = String(value || '');
        const exact = Array.from(el.options).some((opt) => opt.value === raw);
        el.value = exact ? raw : '';
    }

    function syncMotorFields() {
        const tipo = $('ave-tipo-motor')?.value || '';
        const cilindraje = $('ave-cilindraje')?.value || '';
        const potencia = $('ave-potencia')?.value || '';
        document.querySelectorAll('.ave-combustion-fields').forEach((el) => el.classList.toggle('d-none', tipo !== 'combustion'));
        document.querySelectorAll('.ave-electrica-fields').forEach((el) => el.classList.toggle('d-none', tipo !== 'electrica'));
        document.querySelectorAll('.ave-cilindraje-otro').forEach((el) => el.classList.toggle('d-none', !(tipo === 'combustion' && cilindraje === 'otro')));
        document.querySelectorAll('.ave-potencia-otro').forEach((el) => el.classList.toggle('d-none', !(tipo === 'electrica' && potencia === 'otro')));

        ['ave-combustion-tipo', 'ave-cilindraje'].forEach((id) => {
            const el = $(id);
            if (el) el.required = tipo === 'combustion';
        });
        const potenciaEl = $('ave-potencia');
        if (potenciaEl) potenciaEl.required = tipo === 'electrica';
        const cilOtro = $('ave-cilindraje-otro');
        if (cilOtro) cilOtro.required = tipo === 'combustion' && cilindraje === 'otro';
        const potOtro = $('ave-potencia-otro');
        if (potOtro) potOtro.required = tipo === 'electrica' && potencia === 'otro';
    }

    function parseOtro(value) {
        const raw = String(value || '');
        return raw.toLowerCase().startsWith('otro:') ? raw.slice(5).trim() : '';
    }

    function hydrateFichaTecnica(unidad) {
        if (!unidad) return;
        setSiNo('ave-llave', unidad.tiene_llave_fisica);
        setSiNo('ave-tarjeta', unidad.tiene_tarjeta_circulacion);
        setSiNo('ave-placa-fisica', unidad.tiene_placa_fisica);
        setSelectValue('ave-tipo-unidad', unidad.tipo_unidad === 'moto' ? '' : unidad.tipo_unidad);
        setRadio('categoria', unidad.categoria || '');
        setSelectValue('ave-tipo-motor', unidad.tipo_motor);
        setSelectValue('ave-combustion-tipo', unidad.tipo_motor_combustion);
        if (String(unidad.cilindraje || '').toLowerCase().startsWith('otro:')) {
            setSelectValue('ave-cilindraje', 'otro');
            $('ave-cilindraje-otro').value = parseOtro(unidad.cilindraje);
        } else {
            setSelectValue('ave-cilindraje', unidad.cilindraje);
        }
        if (String(unidad.potencia || '').toLowerCase().startsWith('otro:')) {
            setSelectValue('ave-potencia', 'otro');
            $('ave-potencia-otro').value = parseOtro(unidad.potencia);
        } else {
            setSelectValue('ave-potencia', unidad.potencia);
        }
        $('ave-otro-descripcion').value = unidad.otro_descripcion || '';
        $('ave-comentarios-generales').value = unidad.comentarios_generales || '';
        syncMotorFields();
    }

    function rangeInfo(total, page, limit, label) {
        const start = total > 0 ? ((page - 1) * limit) + 1 : 0;
        const end = total > 0 ? Math.min(page * limit, total) : 0;
        return 'Mostrando ' + start + ' a ' + end + ' de ' + total + ' ' + label;
    }

    function pageNumbers(current, pages) {
        const totalPages = Math.max(1, Number(pages || 1));
        const activePage = Math.max(1, Math.min(Number(current || 1), totalPages));
        if (totalPages <= 7) {
            return Array.from({ length: totalPages }, (_, index) => index + 1);
        }

        const numbers = [1];
        const start = Math.max(2, activePage - 1);
        const end = Math.min(totalPages - 1, activePage + 1);
        if (start > 2) numbers.push('ellipsis-start');
        for (let page = start; page <= end; page++) numbers.push(page);
        if (end < totalPages - 1) numbers.push('ellipsis-end');
        numbers.push(totalPages);
        return numbers;
    }

    function renderPagination() {
        const target = $('ave-pagination');
        if (!target) return;
        const current = Math.max(1, Number(state.page || 1));
        const totalPages = Math.max(1, Number(state.pages || 1));
        const item = (label, targetPage, disabled, active, extraClass) => {
            const classes = ['paginate_button', 'page-item'];
            if (extraClass) classes.push(extraClass);
            if (disabled) classes.push('disabled');
            if (active) classes.push('active');
            const attrs = disabled ? 'tabindex="-1" aria-disabled="true"' : 'href="#" data-page="' + esc(targetPage) + '"';
            return '<li class="' + classes.join(' ') + '"><a class="page-link" ' + attrs + '>' + label + '</a></li>';
        };
        target.innerHTML = [
            item('Anterior', Math.max(1, current - 1), current <= 1, false, 'previous'),
            ...pageNumbers(current, totalPages).map((value) => typeof value === 'string' ? item('...', current, true, false, '') : item(String(value), value, false, value === current, '')),
            item('Siguiente', Math.min(totalPages, current + 1), current >= totalPages, false, 'next'),
        ].join('');
    }

    function params() {
        const p = new URLSearchParams();
        p.set('page', String(state.page));
        p.set('limit', String(state.limit));
        const q = $('ave-q')?.value.trim() || '';
        const celula = $('ave-celula')?.value || '';
        const estatus = $('ave-estatus')?.value || 'abiertas';
        if (q) p.set('q', q);
        if (celula) p.set('id_celula', celula);
        if (estatus) p.set('estatus', estatus);
        return p;
    }

    async function cargarResumen() {
        const res = await fetch('/MotosAdjudicadas/evidenciasCodigoResumen', { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success || !json.datos) return;
        const d = json.datos;
        $('ave-kpi-pendientes').textContent = d.pendientes || 0;
        $('ave-kpi-incidencias').textContent = d.incidencias || 0;
        $('ave-kpi-listas').textContent = d.listas_recepcion || 0;
        $('ave-kpi-abiertas').textContent = d.total_abiertas || 0;
    }

    async function cargarCatalogos() {
        const res = await fetch('/MotosAdjudicadas/inventarioCelulas', { headers: { Accept: 'application/json' } });
        const json = await res.json().catch(() => null);
        const celulaSelect = $('ave-celula');
        if (!celulaSelect || !json || !json.success) return;
        (json.datos || []).forEach((row) => {
            const opt = document.createElement('option');
            opt.value = String(row.id_celula);
            opt.textContent = row.nombre;
            celulaSelect.appendChild(opt);
        });
    }

    function renderRows(rows) {
        const body = $('ave-unidades-body');
        if (!body) return;
        state.rows.clear();
        if (!rows || rows.length === 0) {
            body.innerHTML = '<tr><td colspan="8" class="ave-empty"><i class="fa-solid fa-camera"></i>Sin unidades para evidencias con los filtros seleccionados.</td></tr>';
            return;
        }

        body.innerHTML = rows.map((row) => {
            state.rows.set(String(row.id_unidad), row);
            const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
            const ids = [
                row.vin ? 'VIN ' + row.vin : '',
                row.no_motor ? 'Motor ' + row.no_motor : '',
                row.placas ? 'Placa ' + row.placas : '',
            ].filter(Boolean).join(' | ');
            const ruta = row.tracking_id_ruta ? ('Ruta #' + row.tracking_id_ruta) : '';
            const cedis = row.tracking_cedis_destino_nombre || '';
            const trackingMeta = [ruta, cedis, row.tracking_fecha_finalizacion_fmt].filter(Boolean).join(' | ');
            const totalEv = Number(row.evidencias_requeridas || 5);
            const valEv = Number(row.evidencias_validadas || 0);
            const recEv = Number(row.evidencias_recibidas || 0);
            const codigo = row.codigo_verificacion ? `${row.codigo_verificacion} (${row.codigo_estatus || ''})` : 'Sin codigo';
            const puedeValidar = ['pendiente_evidencias', 'incidencia_evidencias'].includes(String(row.estatus_inventario || ''));
            const accion = puedeValidar
                ? '<button type="button" class="btn btn-warning btn-sm" data-action="validar" data-id="' + esc(row.id_unidad) + '"><i class="fa-solid fa-camera me-1"></i>Validar</button>'
                : '<button type="button" class="btn btn-label-success btn-sm" disabled><i class="fa-solid fa-check me-1"></i>Lista</button>';

            return `
                <tr>
                    <td>
                        <div class="ave-unit-main">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
                        <div class="ave-unit-sub">${esc(moto || 'Sin datos de moto')}</div>
                    </td>
                    <td>${esc(row.nombre_celula || '')}</td>
                    <td>
                        <div>${esc(ids || 'Sin identificadores')}</div>
                        ${row.id_credito ? `<div class="ave-unit-sub">Credito historico: ${esc(row.id_credito)}</div>` : ''}
                    </td>
                    <td>
                        <div>${row.tracking_estatus_recoleccion ? statusHtml(row.tracking_estatus_recoleccion) : '<span class="text-muted small">Sin tracking</span>'}</div>
                        <div class="ave-unit-sub">${esc(trackingMeta || row.tracking_nombre_ruta || '')}</div>
                    </td>
                    <td>
                        <div>${esc(valEv + '/' + totalEv + ' validadas')}</div>
                        <div class="ave-unit-sub">${esc(recEv + '/' + totalEv + ' recibidas')}</div>
                    </td>
                    <td>
                        <div>${esc(codigo)}</div>
                        <div class="ave-unit-sub">${esc(row.codigo_expiracion_fmt || '')}</div>
                    </td>
                    <td>${statusHtml(row.estatus_inventario)}</td>
                    <td>${accion}</td>
                </tr>
            `;
        }).join('');
    }

    async function cargarUnidades() {
        const body = $('ave-unidades-body');
        if (body) {
            body.innerHTML = '<tr><td colspan="8" class="ave-empty"><i class="fa-solid fa-spinner fa-spin"></i>Cargando unidades...</td></tr>';
        }
        const res = await fetch('/MotosAdjudicadas/evidenciasCodigoUnidades?' + params().toString(), { headers: { Accept: 'application/json' } });
        const json = await res.json();
        if (!json.success) {
            if (body) body.innerHTML = '<tr><td colspan="8" class="ave-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(json.message || 'No se pudieron cargar las unidades.') + '</td></tr>';
            return;
        }
        state.total = Number(json.total || 0);
        state.pages = Number(json.pages || 1);
        state.page = Number(json.page || 1);
        state.limit = Number(json.limit || state.limit);
        renderRows(json.rows || []);
        const info = $('ave-pager-info');
        if (info) info.textContent = rangeInfo(state.total, state.page, state.limit, 'unidades');
        renderPagination();
    }

    async function sincronizarRecolectadas(silent = true) {
        const status = $('ave-sync-status');
        if (status) status.textContent = 'Sincronizando recolectadas de Tracking...';
        try {
            const res = await fetch('/MotosAdjudicadas/inventarioSincronizarRecolectadas?limit=250', {
                method: 'POST',
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            const creadas = Number(json.creadas || 0);
            if (status) {
                status.textContent = creadas > 0
                    ? `${creadas} prealta(s) esperando entrega fisica en CEDIS.`
                    : 'Sin recolectadas nuevas por migrar.';
            }
            if (!silent && creadas > 0) {
                notify('success', 'Sincronizacion lista', `${creadas} prealta(s) preparadas desde Tracking.`);
            } else if (!silent && !json.success) {
                notify('warning', 'Sin sincronizacion', json.message || 'No se pudo sincronizar Tracking.');
            }
            return json;
        } catch (err) {
            if (status) status.textContent = 'No se pudo sincronizar recolectadas automaticamente.';
            if (!silent) notify('error', 'Error de sincronizacion', err.message || 'No se pudo contactar al servidor.');
            return null;
        }
    }

    async function abrirModal(idUnidad) {
        const row = state.rows.get(String(idUnidad));
        if (!row) return;
        state.currentId = Number(idUnidad || 0);
        $('ave-form-evidencias')?.reset();
        $('ave-id-unidad').value = row.id_unidad || '';
        $('ave-vin').value = row.vin || '';
        syncMotorFields();
        $('ave-codigo-actual').textContent = row.codigo_verificacion || 'Sin generar';
        $('ave-codigo-expira').textContent = row.codigo_expiracion_fmt ? 'Expira: ' + row.codigo_expiracion_fmt : '';
        $('ave-codigo-input').value = row.codigo_verificacion || '';

        const moto = [row.marca, row.modelo, row.anio].filter(Boolean).join(' ');
        $('ave-modal-summary').innerHTML = `
            <div class="ave-unit-main">${esc(row.folio_unidad || ('Unidad #' + row.id_unidad))}</div>
            <div class="text-muted small">${esc(moto || 'Sin datos de moto')}</div>
            <div class="ave-unit-sub mt-1">${esc([row.tracking_id_ruta ? ('Ruta #' + row.tracking_id_ruta) : '', row.tracking_cedis_destino_nombre, row.tracking_fecha_finalizacion_fmt].filter(Boolean).join(' | '))}</div>
        `;
        $('ave-evidencias-actuales').innerHTML = '<div class="text-muted small">Cargando evidencias actuales...</div>';
        fetch('/MotosAdjudicadas/inventarioFichaUnidad?id_unidad=' + encodeURIComponent(String(idUnidad)), { headers: { Accept: 'application/json' } })
            .then(r => r.json())
            .then((json) => {
                if (!json.success) return;
                hydrateFichaTecnica(json.unidad || {});
                const evidencias = json.evidencias || [];
                $('ave-evidencias-actuales').innerHTML = evidencias.length
                    ? '<div class="small fw-bold mb-1">Evidencias actuales</div>' + evidencias.map((ev) => {
                        const link = ev.url_publica ? `<a href="${esc(ev.url_publica)}" target="_blank" rel="noopener">ver</a>` : '';
                        return `<div class="ave-unit-sub">${esc(ev.titulo_slot || ev.slot)} - ${esc(ev.estatus || '')} ${link}</div>`;
                    }).join('')
                    : '<div class="text-muted small">Sin evidencias cargadas.</div>';
            })
            .catch(() => {});

        const modalEl = $('ave-modal-evidencias');
        if (window.bootstrap && window.bootstrap.Modal && modalEl) {
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }
    }

    async function generarCodigo() {
        if (!state.currentId) return;
        const btn = $('ave-btn-generar-codigo');
        const oldHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Generando';
        }
        try {
            const form = new FormData();
            form.append('id_unidad', String(state.currentId));
            const res = await fetch('/MotosAdjudicadas/evidenciasCodigoGenerar', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: form,
            });
            const json = await res.json();
            if (!json.success) {
                notify('warning', 'Codigo no generado', json.message || 'No se pudo generar el codigo.');
                return;
            }
            $('ave-codigo-actual').textContent = json.codigo || '';
            $('ave-codigo-input').value = json.codigo || '';
            $('ave-codigo-expira').textContent = json.fecha_expiracion ? 'Expira: ' + json.fecha_expiracion : '';
        } catch (err) {
            notify('error', 'Error inesperado', err.message || 'No se pudo contactar al servidor.');
        } finally {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = oldHtml;
            }
        }
    }

    async function validarEvidencias(ev) {
        ev.preventDefault();
        const form = $('ave-form-evidencias');
        const btn = $('ave-btn-validar');
        if (!form || !btn) return;
        btn.disabled = true;
        const oldHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Guardando';
        try {
            const res = await fetch('/MotosAdjudicadas/evidenciasCodigoValidar', {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });
            const json = await res.json();
            if (!json.success) {
                notify('error', 'Validacion no guardada', json.message || 'No se pudo validar evidencias.');
                return;
            }
            const modalEl = $('ave-modal-evidencias');
            if (window.bootstrap && window.bootstrap.Modal && modalEl) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }
            notify(json.resultado === 'validada' ? 'success' : 'warning', 'Validacion guardada', json.message || 'Evidencias actualizadas.');
            reloadAll(false);
        } catch (err) {
            notify('error', 'Error inesperado', err.message || 'No se pudo contactar al servidor.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }

    function reloadAll(resetPage) {
        if (resetPage) state.page = 1;
        cargarResumen().catch(() => {});
        cargarUnidades().catch((err) => {
            const body = $('ave-unidades-body');
            if (body) {
                body.innerHTML = '<tr><td colspan="8" class="ave-empty"><i class="fa-solid fa-triangle-exclamation"></i>' + esc(err.message || 'Error inesperado.') + '</td></tr>';
            }
        });
    }

    function init() {
        $('ave-btn-refresh')?.addEventListener('click', () => {
            sincronizarRecolectadas(false).finally(() => reloadAll(false));
        });
        $('ave-btn-filtrar')?.addEventListener('click', () => reloadAll(true));
        $('ave-btn-generar-codigo')?.addEventListener('click', generarCodigo);
        $('ave-form-evidencias')?.addEventListener('submit', validarEvidencias);
        $('ave-limit')?.addEventListener('change', () => {
            state.limit = Number($('ave-limit')?.value || 8) || 8;
            reloadAll(true);
        });
        $('ave-pagination')?.addEventListener('click', (ev) => {
            const link = ev.target.closest('[data-page]');
            if (!link) return;
            ev.preventDefault();
            const nextPage = Number(link.dataset.page || 1);
            if (nextPage && nextPage !== state.page) {
                state.page = nextPage;
                cargarUnidades();
            }
        });
        $('ave-unidades-body')?.addEventListener('click', (ev) => {
            const btn = ev.target.closest('[data-action="validar"]');
            if (!btn) return;
            abrirModal(btn.dataset.id);
        });
        $('ave-q')?.addEventListener('input', () => {
            window.clearTimeout(state.timer);
            state.timer = window.setTimeout(() => reloadAll(true), 350);
        });
        ['ave-celula', 'ave-estatus'].forEach((id) => {
            $(id)?.addEventListener('change', () => reloadAll(true));
        });
        ['ave-tipo-motor', 'ave-cilindraje', 'ave-potencia'].forEach((id) => {
            $(id)?.addEventListener('change', syncMotorFields);
        });

        cargarCatalogos()
            .catch(() => {})
            .finally(() => {
                sincronizarRecolectadas(true).finally(() => reloadAll(true));
            });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
