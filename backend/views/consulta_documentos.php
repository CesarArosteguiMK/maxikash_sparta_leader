<div class="container py-3">

    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Consulta de Documentos</h4>
            <p class="text-muted small">Busca documentos por ID de crédito y tipo</p>
        </div>
    </div>

    <div class="card">

        <!-- Filtro -->
        <div class="row justify-content-between m-4">

            <div class="col-8">
                <label class="form-label">Filtro</label>
                <div class="input-group input-group-merge">
                    <div class="form-check form-check-inline me-3">
                        <input class="form-check-input" checked disabled>
                        <label class="form-check-label">ID de crédito</label>
                    </div>
                </div>
            </div>

            <div class="col-4 d-flex align-items-end justify-content-end">
                <button id="btnResetFiltros" class="btn btn-outline-secondary me-2" type="button">Limpiar</button>
            </div>
        </div>

        <!-- Formulario -->
        <div class="card-body">
            <form id="formConsulta">

                <div class="row g-3">

                    <!-- ID Crédito -->
                    <div class="col-md-6">
                        <label class="form-label">ID Crédito</label>
                        <div class="input-group input-group-merge">
                            <input type="text" class="form-control" id="idDocumento"
                                   placeholder="Ej.: 12345"
                                   pattern="\d{1,10}" maxlength="10" required>
                            <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                        </div>
                    </div>

                    <!-- Tipo de documento -->
                    <div class="col-md-6">
                        <label class="form-label">Tipo de Documento</label>
                        <div class="input-group input-group-merge">
                            <select id="tipoDocumento" class="form-select" required>
                                <option value="Contrato">Validaciones</option>
                                <option value="Factura">Factura</option>
                                <option value="FAD_DOC">Contrato Firmado</option>
                                <option value="EVIDENCIA">Foto Entrega Moto</option>
                            </select>
                            <span class="input-group-text"><i class="fa fa-file"></i></span>
                        </div>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-outline-primary w-100 mt-2" type="submit">
                            Buscar Documento
                        </button>
                    </div>

                </div>
            </form>
        </div>

    </div>
</div>
