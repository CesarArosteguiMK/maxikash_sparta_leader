<style>
    /* ===== PAÍSES: Liquid Glass ===== */
    .pais-card {
        background: rgba(255, 255, 255, 0.88) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.45);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .pais-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12) !important;
    }
    .pais-card-header {
        position: relative;
        overflow: hidden;
    }
    .pais-card-header::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 50%, rgba(255,255,255,0.15) 100%);
        pointer-events: none;
    }

    .pais-card .card-body {
        background: rgba(255, 255, 255, 0.6) !important;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    /* Tarjeta "Nuevo País" */
    .pais-card-new {
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 2px dashed rgba(105, 108, 255, 0.3) !important;
        transition: all 0.3s ease;
    }
    .pais-card-new:hover {
        border-color: rgba(105, 108, 255, 0.6) !important;
        background: rgba(255, 255, 255, 0.85) !important;
        box-shadow: 0 8px 28px rgba(105, 108, 255, 0.12) !important;
        transform: translateY(-3px);
    }

    /* Modal Nuevo País: Liquid Glass */
    #modalNuevoPais .modal-content {
        background: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 1rem;
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(255,255,255,0.3) inset;
    }

    .preview-bandera-container {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(255,255,255,0.4) inset;
    }
    .preview-bandera-container:hover {
        transform: scale(1.05);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    }

    /* Badges Activo/Inactivo en tarjetas de país (legibles en modo claro y oscuro) */
    .badge-glass-success {
        background: rgba(22, 163, 74, 0.9) !important;
        color: #fff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }
    .badge-glass-secondary {
        background: rgba(100, 116, 139, 0.9) !important;
        color: #fff !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
    }

    /* ===== DARK MODE ===== */
    body.dark-mode .pais-card {
        background: rgba(30, 41, 59, 0.85) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-color: rgba(51, 65, 85, 0.6) !important;
    }
    body.dark-mode .pais-card:hover {
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35) !important;
    }
    body.dark-mode .pais-card .card-body {
        background: rgba(30, 41, 59, 0.7) !important;
        color: #f1f5f9 !important;
    }
    body.dark-mode .pais-card .card-body h5 {
        color: #f1f5f9 !important;
    }

    body.dark-mode .pais-card-new {
        background: rgba(30, 41, 59, 0.6) !important;
        border-color: rgba(105, 108, 255, 0.3) !important;
    }
    body.dark-mode .pais-card-new:hover {
        background: rgba(30, 41, 59, 0.8) !important;
        border-color: rgba(105, 108, 255, 0.5) !important;
    }

    body.dark-mode #modalNuevoPais .modal-content {
        background: rgba(30, 41, 59, 0.92) !important;
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        border-color: rgba(51, 65, 85, 0.5);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(51,65,85,0.3) inset;
    }
    body.dark-mode .preview-bandera-container {
        background: rgba(51, 65, 85, 0.5);
        border-color: rgba(71, 85, 105, 0.5);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(51,65,85,0.3) inset;
    }
    body.dark-mode .badge-glass-success {
        background: rgba(34, 197, 94, 0.95) !important;
        color: #fff !important;
        border-color: rgba(255, 255, 255, 0.35) !important;
    }
    body.dark-mode .badge-glass-secondary {
        background: rgba(71, 85, 105, 0.95) !important;
        color: #e2e8f0 !important;
        border-color: rgba(255, 255, 255, 0.2) !important;
    }
</style>

<div class="d-flex align-items-center mb-4">
    <i class="fa-solid fa-globe me-3" style="font-size: 2rem; opacity: 0.85;" aria-hidden="true"></i>
    <div>
        <h4 class="mb-0">Países</h4>
        <p class="text-muted mb-0">Gestiona los países donde opera la empresa. Activa o desactiva según corresponda.</p>
    </div>
</div>

<div id="paisesCards" class="row g-4">
    <div class="text-center py-5">
        <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
        <p class="text-muted mt-2">Cargando países...</p>
    </div>
</div>

<!-- Modal Nuevo País: Liquid Glass -->
<div class="modal fade" id="modalNuevoPais" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="modal-content">
            <div class="modal-body p-4">
                <button type="button" class="btn-close position-absolute" style="top: 1rem; right: 1rem; z-index: 10; padding: 0.5rem; background-color: rgba(76, 78, 100, 0.08); border-radius: 0.25rem;" data-bs-dismiss="modal" aria-label="Close"></button>

                <div class="text-center mb-4">
                    <div class="preview-bandera-container" id="previewBandera">
                        <i class="fa-solid fa-globe fa-3x text-muted"></i>
                    </div>
                    <h5 class="fw-bold" id="previewNombrePais">Escribe el nombre del país</h5>
                </div>

                <form onsubmit="event.preventDefault(); guardarNuevoPais();">
                    <div class="mb-3">
                        <label class="form-label" for="inputNuevoPais">Nombre del País</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-earth-americas"></i></span>
                            <input type="text" id="inputNuevoPais" class="form-control" placeholder="Ej. Perú, Honduras, España..."
                                   maxlength="100"
                                   oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s]/g, '').replace(/^\s+/, '').replace(/\s{2,}/g, ' ')"
                                   onblur="this.value = this.value.trim()">
                        </div>
                        <div class="invalid-feedback" id="errorNuevoPais" style="display: none;"></div>
                        <small class="text-muted mt-1 d-block">La bandera se detecta automáticamente al escribir el nombre.</small>
                    </div>

                    <div class="text-center">
                        <button type="submit" class="btn btn-primary me-2" id="btnGuardarPais">
                            <i class="fa fa-save me-2"></i>Guardar
                        </button>
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
