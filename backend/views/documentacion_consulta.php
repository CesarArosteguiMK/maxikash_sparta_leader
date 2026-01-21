<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Documentación Clientes Entrega</h4>
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

                    <div class="form-check form-check-inline" style="display: none">
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
            <form id="formBusqueda" method="GET" onsubmit="return false;" action="javascript:void(0);">

                <div class="row g-3 align-items-end">

                    <!-- ID -->
                    <div class="col-md-6" id="divID">
                        <label for="idCredito" class="form-label">ID de crédito</label>
                        <div class="input-group input-group-merge">
                            <input type="number" class="form-control" id="idCredito" name="idCredito" value="" placeholder="Ej.: 12345">
                            <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                        </div>
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-6" id="divNombre" style="display: none;">
                        <label for="nombre" class="form-label">Nombre del Cliente</label>
                        <div class="input-group input-group-merge">
                            <input type="text" class="form-control" name="nombre" id="nombre" value="" placeholder="Nombre completo o parcial">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                        </div>
                    </div>

                    <!-- Tipo de Documento -->
                    <div class="col-md-6">
                        <label for="tipoDocumento" class="form-label">Tipo de documento</label>
                        <div class="input-group input-group-merge">
                            <select class="form-select" id="tipoDocumento" name="tipoDocumento">
                                <option value="">Selecciona un documento</option>
                                <option value="INE">INE</option>
                                <option value="FACTURA">FACTURA OK</option>
                                <option value="CONTRATO">VALIDACIONES OK</option>
                                <option value="FAD_DOC">FAD_DOC</option>
                                <option value="EVIDENCIA">EVIDENCIA</option>
                            </select>
                            <span class="input-group-text">
                                <i class="fa fa-file"></i>
                            </span>
                        </div>
                    </div>



                    <div class="col-12">
                        <button type="button" class="btn btn-outline-primary w-100" id="btnBuscar">Buscar</button>
                    </div>
                </div>

            </form>
        </div>



    </div>

    <!-- MODAL VISOR DE DOCUMENTOS (PDFs y otros) -->
    <div class="modal fade" id="modalDocumento" tabindex="-1" aria-hidden="true" style="overflow: hidden !important;">
        <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 85vw; width: 85vw; max-height: 85vh; overflow: hidden !important; overflow-x: hidden !important; overflow-y: hidden !important; margin: 7.5vh auto;">
            <div class="modal-content" style="height: 85vh; max-height: 85vh; display: flex; flex-direction: column; overflow: hidden !important; overflow-x: hidden !important; overflow-y: hidden !important; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">

                <!-- HEADER -->
                <div class="modal-header flex-shrink-0" style="background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%); border-bottom: 1px solid rgba(255,255,255,0.1); border-radius: 12px 12px 0 0; padding: 1rem 1.25rem; position: relative; overflow: hidden !important;">
                    <h5 class="modal-title text-white fw-semibold mb-0" style="font-size: 1.1rem;">
                        <i class="fa fa-file-pdf me-2"></i>Documento
                    </h5>
                    <button type="button" class="btn-close btn-close-custom" data-bs-dismiss="modal" aria-label="Cerrar" style="position: absolute; top: 50%; right: 1.25rem; transform: translateY(-50%); margin: 0;"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body p-0 flex-grow-1" id="documentoModalBody" style="height: calc(85vh - 70px); max-height: calc(85vh - 70px); overflow: hidden !important; overflow-x: hidden !important; overflow-y: hidden !important; position: relative; min-height: 0;">
                    <!-- Contenedor para PDFs (PDF.js) -->
                    <div id="documentoPdfContainer" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; display: none; overflow: auto; background-color: #525252;">
                        <div id="documentoWrapper" style="display: flex; align-items: center; justify-content: center; min-height: 100%; padding: 20px; position: relative;">
                            <div id="pdfViewerContainer" style="position: relative; display: inline-block; max-width: 100%;">
                                <canvas 
                                    id="pdfCanvas" 
                                    style="max-width: 100%; height: auto; display: block; box-shadow: 0 2px 8px rgba(0,0,0,0.3); user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; -webkit-user-drag: none; -khtml-user-drag: none; user-drag: none;"
                                    draggable="false"
                                    oncontextmenu="return false;">
                                </canvas>
                                <div class="watermark-overlay" id="pdfWatermark" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 10;"></div>
                            </div>
                        </div>
                        <!-- Controles de navegación del PDF -->
                        <div id="pdfControls" style="position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%); background-color: rgba(0,0,0,0.7); padding: 10px 20px; border-radius: 25px; z-index: 1000; display: flex; align-items: center; gap: 15px;">
                            <button id="pdfPrev" class="btn btn-sm btn-light" style="min-width: 40px;">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <span id="pdfPageInfo" style="color: white; font-size: 0.9rem; min-width: 80px; text-align: center;">
                                <span id="pdfCurrentPage">1</span> / <span id="pdfTotalPages">1</span>
                            </span>
                            <button id="pdfNext" class="btn btn-sm btn-light" style="min-width: 40px;">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                            <div style="width: 1px; height: 20px; background-color: rgba(255,255,255,0.3);"></div>
                            <button id="pdfZoomOut" class="btn btn-sm btn-light" style="min-width: 40px;">
                                <i class="fa fa-search-minus"></i>
                            </button>
                            <span id="pdfZoomLevel" style="color: white; font-size: 0.9rem; min-width: 50px; text-align: center;">100%</span>
                            <button id="pdfZoomIn" class="btn btn-sm btn-light" style="min-width: 40px;">
                                <i class="fa fa-search-plus"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Contenedor para imágenes -->
                    <div id="documentoImagenContainer" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; width: 100%; height: 100%; display: none; overflow: auto; background-color: #f8f9fa;">
                        <div style="display: flex; align-items: center; justify-content: center; min-height: 100%; width: 100%; padding: 20px;">
                            <div class="watermark-container" style="position: relative; display: inline-block; max-width: 100%;">
                                <img 
                                    id="imgDocumento" 
                                    src="" 
                                    alt="Documento"
                                    class="img-fluid"
                                    style="max-width: 100%; max-height: calc(95vh - 140px); width: auto; height: auto; object-fit: contain; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: zoom-in; display: block; margin: 0 auto; user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; -webkit-user-drag: none; -khtml-user-drag: none; user-drag: none;"
                                    draggable="false"
                                    oncontextmenu="return false;"
                                    onerror="this.style.display='none'; Swal.fire('Error', 'No se pudo cargar la imagen', 'error');">
                                <div class="watermark-overlay"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Iframe legacy (mantener para compatibilidad) -->
                    <iframe
                            id="visorDocumento"
                            src=""
                            style="width:100%;height:100%;border:0;transform-origin: top left; display: none;"
                            loading="lazy">
                    </iframe>
                </div>


            </div>
        </div>
    </div>

    <!-- MODAL VISOR DE INE (Frente y Reverso) -->
    <div class="modal fade" id="modalINE" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title">INE - Identificación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body" id="modalBodyINE" style="max-height:85vh; overflow-y: auto;">
                    <div class="row g-3 g-md-4">
                        <!-- FRENTE -->
                        <div class="col-12 col-md-6">
                            <div class="card h-100">
                                <div class="card-header text-center" style="background-color: #696cff; color: #ffffff !important;">
                                    <h6 class="mb-0" style="color: #ffffff !important; font-weight: 600; font-size: 0.95rem;">
                                        <i class="fa fa-id-card me-2"></i>Frente
                                    </h6>
                                </div>
                                <div class="card-body p-2 d-flex align-items-center justify-content-center img-container-ine" style="background-color: #f8f9fa; position: relative;">
                                    <div class="watermark-container" style="position: relative; display: inline-block;">
                                        <img 
                                            id="imgINEfrente" 
                                            src="" 
                                            alt="INE Frente" 
                                            class="img-fluid img-zoomable"
                                            style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: zoom-in; transition: transform 0.3s ease; touch-action: manipulation; display: block; margin: 0 auto; user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; -webkit-user-drag: none; -khtml-user-drag: none; user-drag: none;"
                                            draggable="false"
                                            oncontextmenu="return false;"
                                            onclick="abrirZoomINE(this.src, 'Frente')"
                                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'18\' y=\'50%25\' x=\'50%25\' text-anchor=\'middle\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
                                        <div class="watermark-overlay"></div>
                                    </div>
                                    <div class="position-absolute top-0 end-0 m-1 m-md-2">
                                        <span class="badge bg-info badge-zoom-hint">
                                            <i class="fa fa-search-plus me-1"></i><span class="d-none d-sm-inline">Click</span><span class="d-sm-none">Tap</span> para zoom
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- REVERSO -->
                        <div class="col-12 col-md-6">
                            <div class="card h-100">
                                <div class="card-header text-center" style="background-color: #8592a3; color: #ffffff !important;">
                                    <h6 class="mb-0" style="color: #ffffff !important; font-weight: 600; font-size: 0.95rem;">
                                        <i class="fa fa-id-card me-2"></i>Reverso
                                    </h6>
                                </div>
                                <div class="card-body p-2 d-flex align-items-center justify-content-center img-container-ine" style="background-color: #f8f9fa; position: relative;">
                                    <div class="watermark-container" style="position: relative; display: inline-block;">
                                        <img 
                                            id="imgINEreverso" 
                                            src="" 
                                            alt="INE Reverso" 
                                            class="img-fluid img-zoomable"
                                            style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: zoom-in; transition: transform 0.3s ease; touch-action: manipulation; display: block; margin: 0 auto; user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; -webkit-user-drag: none; -khtml-user-drag: none; user-drag: none;"
                                            draggable="false"
                                            oncontextmenu="return false;"
                                            onclick="abrirZoomINE(this.src, 'Reverso')"
                                            onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23ddd\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'18\' y=\'50%25\' x=\'50%25\' text-anchor=\'middle\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
                                        <div class="watermark-overlay"></div>
                                    </div>
                                    <div class="position-absolute top-0 end-0 m-1 m-md-2">
                                        <span class="badge bg-info badge-zoom-hint">
                                            <i class="fa fa-search-plus me-1"></i><span class="d-none d-sm-inline">Click</span><span class="d-sm-none">Tap</span> para zoom
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer flex-column flex-sm-row">
                    <small class="text-muted text-center text-sm-start mb-2 mb-sm-0">
                        <i class="fa fa-info-circle me-1"></i>
                        <span class="d-none d-sm-inline">Las imágenes se cargan directamente desde el servidor - Haz clic en las imágenes para ampliar</span>
                        <span class="d-sm-none">Toca las imágenes para ampliar</span>
                    </small>
                    <button type="button" class="btn btn-secondary w-100 w-sm-auto" data-bs-dismiss="modal">Cerrar</button>
                </div>

            </div>
        </div>
    </div>

    <!-- MODAL ZOOM PARA IMÁGENES -->
    <div class="modal fade" id="modalZoomINE" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-dark">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title text-white" id="tituloZoomINE">INE - Zoom</h5>
                    <div class="d-flex align-items-center gap-2">
                        <!-- Controles de zoom -->
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="zoomOutINE()" title="Alejar">
                                <i class="fa fa-search-minus"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="resetZoomINE()" title="Restablecer">
                                <span id="zoomLevelINE">100%</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="zoomInINE()" title="Acercar">
                                <i class="fa fa-search-plus"></i>
                            </button>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body p-0" id="zoomModalBody" style="min-height: calc(100vh - 120px); max-height: calc(100vh - 120px); background-color: rgba(0,0,0,0.9); position: relative; overflow: auto; cursor: grab;">
                    <div id="zoomWrapper" style="display: flex; align-items: center; justify-content: center; padding: 20px;">
                        <div class="watermark-container-zoom" id="zoomContainer" style="position: relative; display: inline-block;">
                            <img 
                                id="imgZoomINE" 
                                src="" 
                                alt="INE Zoom" 
                                class="img-fluid img-zoom-fullscreen"
                                style="max-width: 95vw; max-height: calc(100vh - 140px); width: auto; height: auto; object-fit: contain; cursor: grab; user-select: none; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; -webkit-user-drag: none; -khtml-user-drag: none; user-drag: none; display: block;"
                                draggable="false"
                                oncontextmenu="return false;"
                                ontouchstart="handleZoomTouchStart(event)"
                                ontouchmove="handleZoomTouchMove(event)"
                                ontouchend="handleZoomTouchEnd(event)">
                            <div class="watermark-overlay-zoom"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary flex-column flex-sm-row">
                    <small class="text-white-50 text-center text-sm-start mb-2 mb-sm-0">
                        <i class="fa fa-mouse-pointer me-1 d-none d-sm-inline"></i>
                        <i class="fa fa-hand-pointer me-1 d-sm-none"></i>
                        <span class="d-none d-sm-inline">Haz clic en la imagen o presiona ESC para cerrar</span>
                        <span class="d-sm-none">Toca la imagen para cerrar</span>
                    </small>
                    <button type="button" class="btn btn-light w-100 w-sm-auto" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Estilos para desktop */
        .img-zoomable:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2) !important;
        }

        /* Estilos responsive para contenedores de imágenes */
        .img-container-ine {
            min-height: 300px;
        }

        @media (min-width: 768px) {
            .img-container-ine {
                min-height: 400px;
            }
        }

        /* Badge responsive */
        .badge-zoom-hint {
            font-size: 0.65rem;
            padding: 0.35em 0.65em;
        }

        @media (min-width: 576px) {
            .badge-zoom-hint {
                font-size: 0.7rem;
                padding: 0.4em 0.7em;
            }
        }

        /* Modal zoom responsive */
        .img-zoom-fullscreen {
            -webkit-tap-highlight-color: transparent;
        }

        /* Asegurar que la imagen en zoom se muestre completa */
        #modalZoomINE .modal-body {
            overflow: auto !important;
        }

        #modalZoomINE .watermark-container-zoom {
            max-width: 100%;
            max-height: 100%;
        }

        #modalZoomINE .watermark-container-zoom img {
            max-width: min(95vw, 100%);
            max-height: calc(100vh - 140px);
            object-fit: contain !important;
        }

        #modalZoomINE .watermark-container-zoom {
            transform-origin: center;
            transition: none;
        }

        /* Asegurar que el modal body permita scroll cuando hay zoom */
        #modalZoomINE .modal-body {
            overflow: auto !important;
        }

        /* Cursor para indicar que se puede arrastrar */
        #modalZoomINE .modal-body:active {
            cursor: grabbing !important;
        }

        /* Controles de zoom */
        #modalZoomINE .btn-group .btn {
            min-width: 45px;
        }

        #modalZoomINE .btn-group span {
            min-width: 50px;
            display: inline-block;
            font-size: 0.875rem;
        }

        /* Mejoras para móvil */
        @media (max-width: 767.98px) {
            #modalINE .modal-dialog {
                margin: 0.5rem;
            }

            #modalINE .modal-body {
                padding: 1rem;
                max-height: 80vh;
            }

            #modalINE .card-body {
                min-height: 250px !important;
            }

            #modalINE .card-header h6 {
                font-size: 0.85rem !important;
            }

            #modalZoomINE .modal-header,
            #modalZoomINE .modal-footer {
                padding: 0.75rem;
            }

            #modalZoomINE .modal-title {
                font-size: 1rem;
            }

            #modalZoomINE .modal-body {
                padding: 1rem !important;
            }

            #modalZoomINE .watermark-container-zoom img {
                max-width: 95vw !important;
                max-height: calc(100vh - 160px) !important;
            }
        }

        /* Optimización para pantallas muy pequeñas */
        @media (max-width: 575.98px) {
            #modalINE .modal-body {
                padding: 0.75rem;
            }

            .badge-zoom-hint {
                font-size: 0.6rem;
                padding: 0.3em 0.5em;
            }
        }

        /* Overlay para ocultar el botón del visor de Google en PDFs */
        #pdfOverlay {
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 60px;
            background-color: #f8f9fa;
            z-index: 1000;
            pointer-events: auto;
            cursor: default;
        }

        /* Asegurar que el wrapper del PDF tenga posición relativa */
        #documentoPdfContainer #documentoWrapper {
            position: relative;
        }

        /* Cuando el modal de documento está abierto, asegurar que el overlay esté visible */
        #modalDocumento.show #pdfOverlay {
            display: block !important;
        }

        /* Eliminar COMPLETAMENTE el scroll del modal para Facturación OK */
        #modalDocumento {
            overflow: hidden !important;
        }

        #modalDocumento .modal-dialog {
            overflow: hidden !important;
            max-height: 85vh !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
        }

        #modalDocumento .modal-content {
            overflow: hidden !important;
            max-height: 85vh !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
        }

        #modalDocumento .modal-header {
            overflow: hidden !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
        }

        #modalDocumento .modal-body {
            overflow: hidden !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
        }

        /* Asegurar que el body del modal no tenga scroll */
        #modalDocumento.modal.show ~ * {
            overflow: hidden;
        }

        /* Cuando el modal está abierto, prevenir scroll en el body */
        body.modal-open {
            overflow: hidden !important;
            padding-right: 0 !important;
            position: fixed !important;
            width: 100% !important;
        }

        /* Prevenir scroll en html también */
        html.modal-open {
            overflow: hidden !important;
            padding-right: 0 !important;
        }

        /* Prevenir scroll en el backdrop de Bootstrap */
        .modal-backdrop {
            overflow: hidden !important;
        }

        /* Asegurar que NO haya scroll en NINGÚN elemento del modal */
        #modalDocumento * {
            overflow-x: hidden !important;
            overflow-y: hidden !important;
        }

        /* EXCEPCIÓN: Solo el embedContainer puede tener scroll */
        #modalDocumento #visorPdfEmbed {
            overflow: auto !important;
            overflow-x: auto !important;
            overflow-y: auto !important;
        }

        /* Estilo mejorado para el botón de cerrar */
        #modalDocumento .btn-close-custom {
            filter: brightness(0) invert(1) !important;
            opacity: 1 !important;
            background-color: rgba(255,255,255,0.15) !important;
            background-image: none !important;
            border-radius: 50% !important;
            width: 38px !important;
            height: 38px !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25) !important;
            border: 2px solid rgba(255,255,255,0.4) !important;
            position: absolute !important;
            top: 50% !important;
            right: 1.25rem !important;
            transform: translateY(-50%) !important;
            z-index: 10 !important;
            margin: 0 !important;
        }

        #modalDocumento .btn-close-custom::before {
            content: '×' !important;
            font-size: 30px !important;
            line-height: 1 !important;
            font-weight: 300 !important;
            color: white !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            z-index: 1 !important;
        }

        #modalDocumento .btn-close-custom:hover {
            background-color: rgba(255,255,255,0.3) !important;
            transform: translateY(-50%) scale(1.1) rotate(90deg) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4) !important;
            border-color: rgba(255,255,255,0.8) !important;
        }

        #modalDocumento .btn-close-custom:hover::before {
            transform: translate(-50%, -50%) rotate(-90deg) !important;
        }

        #modalDocumento .btn-close-custom:active {
            transform: translateY(-50%) scale(1.05) rotate(90deg) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3) !important;
        }

        /* ===== MARCA DE AGUA "SIN VALOR" REPETIDA COMO REFERENCIA ===== */
        .watermark-container {
            position: relative;
            display: inline-block;
        }

        .watermark-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
            overflow: hidden;
        }
        
        /* Para el overlay del PDF, usar overflow hidden para evitar scrolls propios y agregar franjas rojas */
        #watermarkOverlayPdf {
            overflow: hidden !important;
            background-image: 
                repeating-linear-gradient(
                    -45deg,
                    transparent,
                    transparent 120px,
                    rgba(220, 20, 20, 0.08) 120px,
                    rgba(220, 20, 20, 0.08) 240px
                );
        }

        /* Capas de marca de agua creadas por JavaScript */
        .watermark-layer {
            position: absolute;
            font-weight: bold;
            color: rgba(220, 20, 20, 0.75);
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.4);
            transform: rotate(-45deg);
            transform-origin: center;
            white-space: nowrap;
            letter-spacing: 0.4em;
            z-index: 11;
            pointer-events: none;
        }

        /* Patrón de fondo sutil */
        .watermark-overlay::before {
            display: none; /* Desactivado, usamos JavaScript */
        }

        .watermark-overlay::after {
            display: none; /* Desactivado, usamos JavaScript */
        }

        /* Marca de agua para zoom - más grande y repetida */
        .watermark-container-zoom {
            position: relative;
            display: inline-block;
        }

        .watermark-overlay-zoom {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 10;
            overflow: hidden;
        }

        /* Zoom también usa JavaScript para las capas */
        .watermark-overlay-zoom::before {
            display: none; /* Desactivado, usamos JavaScript */
        }

        .watermark-overlay-zoom::after {
            display: none; /* Desactivado, usamos JavaScript */
        }

        /* Agregar más capas usando elementos adicionales vía JavaScript si es necesario */
        .watermark-overlay {
            background-image: 
                repeating-linear-gradient(
                    -45deg,
                    transparent,
                    transparent 120px,
                    rgba(220, 20, 20, 0.06) 120px,
                    rgba(220, 20, 20, 0.06) 240px
                );
        }

        .watermark-overlay-zoom {
            background-image: 
                repeating-linear-gradient(
                    -45deg,
                    transparent,
                    transparent 180px,
                    rgba(220, 20, 20, 0.06) 180px,
                    rgba(220, 20, 20, 0.06) 360px
                );
        }
        
        /* Para el overlay del PDF, mantener las franjas rojas y evitar scrolls */
        #watermarkOverlayPdf {
            overflow: hidden !important;
            background-image: 
                repeating-linear-gradient(
                    -45deg,
                    transparent,
                    transparent 120px,
                    rgba(220, 20, 20, 0.08) 120px,
                    rgba(220, 20, 20, 0.08) 240px
                );
        }

        /* Responsive para marca de agua */
        @media (max-width: 767.98px) {
            .watermark-layer {
                font-size: 1.5rem !important;
                letter-spacing: 0.3em !important;
            }
        }

        @media (max-width: 575.98px) {
            .watermark-layer {
                font-size: 1.2rem !important;
                letter-spacing: 0.25em !important;
            }
        }
    </style>

    <script>
        let touchStartY = 0;
        let touchEndY = 0;

        // Función para crear múltiples capas de marca de agua
        function crearMarcasAgua() {
            const overlays = document.querySelectorAll('.watermark-overlay, .watermark-overlay-zoom');
            
            overlays.forEach(overlay => {
                // Limpiar elementos anteriores si existen
                const existingLayers = overlay.querySelectorAll('.watermark-layer');
                existingLayers.forEach(layer => layer.remove());
                
                // Obtener dimensiones reales del overlay
                const overlayRect = overlay.getBoundingClientRect();
                const isZoom = overlay.classList.contains('watermark-overlay-zoom');
                
                // Determinar si es un PDF en iframe ANTES de calcular dimensiones
                const isPdfOverlay = overlay.id === 'watermarkOverlayPdf';
                
                // Obtener el contenedor asociado
                let container = overlay.closest('.watermark-container, .watermark-container-zoom');
                
                // Si es overlay de PDF y no tiene contenedor cercano, buscar el embedContainer
                if (isPdfOverlay && !container) {
                    const embedContainer = document.getElementById('visorPdfEmbed');
                    if (embedContainer) {
                        container = embedContainer;
                    }
                }
                
                if (!container && !isPdfOverlay) return;
                
                const img = container ? container.querySelector('img') : null;
                const canvas = container ? (container.querySelector('canvas') || document.getElementById('pdfCanvas')) : null;
                const iframe = container ? container.querySelector('iframe') : null;
                
                const isIframePdf = iframe || isPdfOverlay;
                
                // Si hay un iframe o es overlay de PDF, usar las dimensiones REALES del overlay
                let width, height;
                if (isIframePdf) {
                    // CRÍTICO: Para PDFs con zoom, usar las dimensiones REALES del overlay, no del contenedor padre
                    // Esto asegura que la marca de agua se mantenga alineada cuando hay zoom
                    if (isPdfOverlay) {
                        // Usar las dimensiones reales del overlay (que ya tiene el tamaño correcto con zoom)
                        width = overlayRect.width || overlay.offsetWidth || overlay.clientWidth;
                        height = overlayRect.height || overlay.offsetHeight || overlay.clientHeight;
                        
                        // Si el overlay tiene estilos inline con width/height en px, usarlos
                        const overlayWidth = overlay.style.width;
                        const overlayHeight = overlay.style.height;
                        if (overlayWidth && overlayWidth.includes('px')) {
                            width = parseFloat(overlayWidth);
                        }
                        if (overlayHeight && overlayHeight.includes('px')) {
                            height = parseFloat(overlayHeight);
                        }
                    } else {
                        // Para otros casos, usar las dimensiones del contenedor padre
                        const embedContainer = document.getElementById('visorPdfEmbed');
                        if (embedContainer) {
                            const embedRect = embedContainer.getBoundingClientRect();
                            width = embedRect.width || embedContainer.clientWidth || embedContainer.offsetWidth || overlayRect.width;
                            height = embedRect.height || embedContainer.clientHeight || embedContainer.offsetHeight || overlayRect.height;
                        } else {
                            // Si no se encuentra el contenedor padre, usar el contenedor del modal
                            const modalBody = document.getElementById('documentoModalBody');
                            if (modalBody) {
                                const modalRect = modalBody.getBoundingClientRect();
                                width = modalRect.width || modalBody.clientWidth || modalBody.offsetWidth || overlayRect.width;
                                height = modalRect.height || modalBody.clientHeight || modalBody.offsetHeight || overlayRect.height;
                            } else {
                                width = overlayRect.width || window.innerWidth;
                                height = overlayRect.height || window.innerHeight;
                            }
                        }
                    }
                } else if (img) {
                    // LÓGICA ORIGINAL PARA IMÁGENES - NO TOCAR
                    width = img.offsetWidth || overlayRect.width;
                    height = img.offsetHeight || overlayRect.height;
                } else if (canvas) {
                    // LÓGICA ORIGINAL PARA CANVAS - NO TOCAR
                    width = canvas.width || canvas.offsetWidth || overlayRect.width;
                    height = canvas.height || canvas.offsetHeight || overlayRect.height;
                } else {
                    // LÓGICA ORIGINAL - NO TOCAR
                    width = overlayRect.width;
                    height = overlayRect.height;
                }
                
                if (width === 0 || height === 0) return;
                
                // Calcular número de capas según el tamaño
                const effectiveWidth = width;
                const effectiveHeight = height;
                
                // LÓGICA ORIGINAL PARA IMÁGENES - NO TOCAR
                const spacing = isZoom ? 110 : 80;
                const fontSize = isZoom ? '3.2rem' : '2rem';
                const numLayers = Math.ceil(effectiveHeight / spacing) + 3;
                
                // Para iframes y overlay del PDF, usar las dimensiones REALES del overlay cuando hay zoom
                if (isIframePdf) {
                    // Si es el overlay del PDF (watermarkOverlayPdf), usar sus dimensiones reales
                    if (isPdfOverlay) {
                        // El overlay ya tiene el tamaño correcto en píxeles (scaledWidth x scaledHeight)
                        // No mover el overlay, está en el lugar correcto dentro del watermarkContainer
                        // Solo asegurar que las capas de marca de agua usen las dimensiones correctas
                        // Las dimensiones width y height ya fueron calculadas arriba usando el tamaño real del overlay
                    } else {
                        // Para otros casos, usar el contenedor padre
                        const embedContainer = document.getElementById('visorPdfEmbed');
                        if (embedContainer) {
                            // Asegurar que el overlay esté en el contenedor correcto
                            if (overlay.parentElement !== embedContainer) {
                                embedContainer.appendChild(overlay);
                            }
                            // Asegurar dimensiones completas y posición absoluta
                            overlay.style.position = 'absolute';
                            overlay.style.top = '0';
                            overlay.style.left = '0';
                            overlay.style.right = '0';
                            overlay.style.bottom = '0';
                            overlay.style.width = '100%';
                            overlay.style.height = '100%';
                            overlay.style.zIndex = '10';
                            overlay.style.pointerEvents = 'none';
                            overlay.style.overflow = 'visible';
                        } else {
                            // Si no se encuentra el contenedor, usar el contenedor del modal
                            const modalBody = document.getElementById('documentoModalBody');
                            if (modalBody && overlay.parentElement !== modalBody) {
                                modalBody.appendChild(overlay);
                            }
                        }
                    }
                } else {
                    // LÓGICA ORIGINAL PARA IMÁGENES Y CANVAS - NO TOCAR
                    overlay.style.width = width + 'px';
                    overlay.style.height = height + 'px';
                }
                
                if (isIframePdf) {
                    // LÓGICA PARA PDFs - Patrón diagonal ordenado como en la imagen de referencia
                    // Asegurar que tenemos dimensiones válidas
                    // Si es el overlay del PDF con zoom, usar las dimensiones reales que ya fueron calculadas
                    if (width === 0 || height === 0) {
                        // Si las dimensiones son 0, intentar obtenerlas del overlay directamente
                        if (isPdfOverlay) {
                            const overlayStyle = window.getComputedStyle(overlay);
                            const overlayWidth = parseFloat(overlayStyle.width) || parseFloat(overlay.style.width) || overlayRect.width;
                            const overlayHeight = parseFloat(overlayStyle.height) || parseFloat(overlay.style.height) || overlayRect.height;
                            if (overlayWidth > 0) width = overlayWidth;
                            if (overlayHeight > 0) height = overlayHeight;
                        }
                        // Si aún son 0, usar valores por defecto
                        if (width === 0 || height === 0) {
                            width = width || 1200;
                            height = height || 800;
                        }
                    }
                    
                    // Configuración para patrón ordenado y bien espaciado
                    const fontSizeForIframe = '2.5rem'; // Tamaño de fuente apropiado
                    const layerSpacing = 120; // Espaciado vertical más amplio para orden
                    const textSpacing = 150; // Espaciado horizontal entre repeticiones
                    
                    // Calcular número de filas y columnas para cubrir todo el área
                    const numRows = Math.ceil((height * 1.5) / layerSpacing) + 2; // Filas diagonales
                    const numCols = Math.ceil((width * 1.5) / textSpacing) + 2; // Columnas
                    
                    // Crear patrón diagonal ordenado
                    // Cada capa representa una línea diagonal
                    for (let row = -2; row < numRows; row++) {
                        const layer = document.createElement('div');
                        layer.className = 'watermark-layer';
                        
                        // Calcular posición vertical
                        const topPos = (row * layerSpacing);
                        
                        // Calcular posición horizontal inicial (desplazada para crear diagonal)
                        const leftOffset = (row * (textSpacing * 0.5)) - (width * 0.3);
                        
                        // Crear texto con repeticiones bien espaciadas
                        const repetitions = numCols;
                        const textContent = 'SIN VALOR '.repeat(repetitions);
                        
                        // Calcular ancho necesario para el texto
                        const layerWidth = repetitions * textSpacing;
                        
                        layer.textContent = textContent;
                        layer.style.cssText = `
                            position: absolute;
                            font-size: ${fontSizeForIframe};
                            font-weight: bold;
                            color: rgba(220, 20, 20, 0.5);
                            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
                            transform: rotate(-45deg);
                            transform-origin: center;
                            white-space: nowrap;
                            letter-spacing: 0.5em;
                            z-index: 11;
                            top: ${topPos}px;
                            left: ${leftOffset}px;
                            width: ${layerWidth}px;
                            text-align: center;
                            pointer-events: none;
                            user-select: none;
                            line-height: 1.2;
                        `;
                        overlay.appendChild(layer);
                    }
                } else {
                    // LÓGICA ORIGINAL PARA IMÁGENES (INE, EVIDENCIA) - NO TOCAR ESTA PARTE
                    // Esta es la lógica original que funcionaba perfectamente para imágenes
                    for (let i = -2; i < numLayers; i++) {
                        const layer = document.createElement('div');
                        layer.className = 'watermark-layer';
                        layer.textContent = 'SIN VALOR '.repeat(Math.ceil(effectiveWidth / 80) + 8);
                        const layerWidth = effectiveWidth * 1.6;
                        layer.style.cssText = `
                            position: absolute;
                            font-size: ${fontSize};
                            font-weight: bold;
                            color: rgba(220, 20, 20, 0.45);
                            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.25);
                            transform: rotate(-45deg);
                            transform-origin: center;
                            white-space: nowrap;
                            letter-spacing: 0.4em;
                            z-index: 11;
                            top: ${(i * spacing)}px;
                            left: -${effectiveWidth * 0.3}px;
                            width: ${layerWidth}px;
                            text-align: center;
                            pointer-events: none;
                        `;
                        overlay.appendChild(layer);
                    }
                }
            });
        }

        // Variables para zoom dinámico
        let currentZoom = 1;
        let minZoom = 0.5;
        let maxZoom = 5;
        let isDragging = false;
        let startX = 0;
        let startY = 0;
        let scrollLeft = 0;
        let scrollTop = 0;
        let lastTouchDistance = 0;

        function abrirZoomINE(src, tipo) {
            const imgZoom = document.getElementById('imgZoomINE');
            imgZoom.src = src;
            document.getElementById('tituloZoomINE').textContent = 'INE - ' + tipo;
            
            // Desactivar descarga en imagen de zoom
            if (typeof desactivarDescargaImagen === 'function') {
                desactivarDescargaImagen(imgZoom);
            }
            
            // Resetear zoom al abrir
            currentZoom = 1;
            resetZoomINE();
            
            const modal = new bootstrap.Modal(document.getElementById('modalZoomINE'));
            modal.show();
            
            // Crear marcas de agua después de que la imagen se cargue
            imgZoom.onload = function() {
                // Desactivar descarga después de cargar
                if (typeof desactivarDescargaImagen === 'function') {
                    desactivarDescargaImagen(imgZoom);
                }
                setTimeout(() => {
                    crearMarcasAgua();
                    resetZoomINE();
                }, 200);
            };
            
            // También crear después de que el modal se muestre (por si ya estaba cargada)
            setTimeout(() => {
                crearMarcasAgua();
                resetZoomINE();
                // Asegurar protección después de que el modal se muestre
                if (typeof desactivarDescargaImagen === 'function') {
                    desactivarDescargaImagen(imgZoom);
                }
            }, 300);
            
            // Prevenir scroll del body cuando el modal está abierto
            document.body.style.overflow = 'hidden';
        }

        // Funciones de zoom
        function zoomInINE() {
            if (currentZoom < maxZoom) {
                const previousZoom = currentZoom;
                currentZoom = Math.min(currentZoom + 0.25, maxZoom);
                applyZoom(previousZoom);
            }
        }

        function zoomOutINE() {
            if (currentZoom > minZoom) {
                const previousZoom = currentZoom;
                currentZoom = Math.max(currentZoom - 0.25, minZoom);
                applyZoom(previousZoom);
            }
        }

        function resetZoomINE() {
            const img = document.getElementById('imgZoomINE');
            const modalBody = document.getElementById('zoomModalBody');
            const wrapper = document.getElementById('zoomWrapper');
            const container = document.getElementById('zoomContainer');
            
            // Resetear zoom
            currentZoom = 1;
            
            // Resetear completamente los estilos de la imagen para que se comporte como al inicio
            if (img) {
                img.style.width = '';
                img.style.height = '';
                img.style.maxWidth = '95vw';
                img.style.maxHeight = 'calc(100vh - 140px)';
            }
            
            // Resetear overlay de marca de agua
            if (container) {
                const overlay = container.querySelector('.watermark-overlay-zoom');
                if (overlay) {
                    overlay.style.width = '';
                    overlay.style.height = '';
                }
            }
            
            // Resetear wrapper
            if (wrapper) {
                wrapper.style.width = '';
                wrapper.style.height = '';
                wrapper.style.minWidth = '';
                wrapper.style.minHeight = '';
                wrapper.style.padding = '20px';
                wrapper.style.alignItems = 'center';
                wrapper.style.justifyContent = 'center';
            }
            
            // Esperar a que la imagen se ajuste naturalmente y luego centrar
            requestAnimationFrame(() => {
                setTimeout(() => {
                    if (modalBody) {
                        // Centrar el scroll
                        const scrollLeft = (modalBody.scrollWidth - modalBody.clientWidth) / 2;
                        const scrollTop = (modalBody.scrollHeight - modalBody.clientHeight) / 2;
                        modalBody.scrollLeft = Math.max(0, scrollLeft);
                        modalBody.scrollTop = Math.max(0, scrollTop);
                    }
                    // Actualizar nivel de zoom
                    const zoomLevel = document.getElementById('zoomLevelINE');
                    if (zoomLevel) {
                        zoomLevel.textContent = '100%';
                    }
                    // Recrear marcas de agua con el tamaño natural
                    crearMarcasAgua();
                }, 300);
            });
        }

        function applyZoom(previousZoom = 1) {
            const container = document.getElementById('zoomContainer');
            const zoomLevel = document.getElementById('zoomLevelINE');
            const modalBody = document.getElementById('zoomModalBody');
            const img = document.getElementById('imgZoomINE');
            const wrapper = document.getElementById('zoomWrapper');
            
            if (container && img && modalBody) {
                const naturalWidth = img.naturalWidth || img.offsetWidth;
                const naturalHeight = img.naturalHeight || img.offsetHeight;
                
                if (naturalWidth > 0 && naturalHeight > 0) {
                    const scaledWidth = naturalWidth * currentZoom;
                    const scaledHeight = naturalHeight * currentZoom;
                    
                    // Zoom por tamaño real (evita recortes por transform)
                    img.style.width = `${scaledWidth}px`;
                    img.style.height = `${scaledHeight}px`;
                    if (currentZoom > 1) {
                        img.style.maxWidth = 'none';
                        img.style.maxHeight = 'none';
                    } else {
                        img.style.maxWidth = '95vw';
                        img.style.maxHeight = 'calc(100vh - 140px)';
                    }
                    
                    // Overlay de marca de agua sigue el tamaño real
                    const overlay = container.querySelector('.watermark-overlay-zoom');
                    if (overlay) {
                        overlay.style.width = `${scaledWidth}px`;
                        overlay.style.height = `${scaledHeight}px`;
                    }
                    
                    // Wrapper más grande para permitir scroll en todas las direcciones
                    if (wrapper) {
                        const bodyWidth = modalBody.clientWidth;
                        const bodyHeight = modalBody.clientHeight;
                        
                        // Padding suficiente para permitir scroll completo en todas las direcciones
                        const padding = 100;
                        const wrapperWidth = scaledWidth + (padding * 2);
                        const wrapperHeight = scaledHeight + (padding * 2);
                        
                        wrapper.style.width = `${wrapperWidth}px`;
                        wrapper.style.height = `${wrapperHeight}px`;
                        wrapper.style.minWidth = `${wrapperWidth}px`;
                        wrapper.style.minHeight = `${wrapperHeight}px`;
                        wrapper.style.padding = `${padding}px`;
                        
                        // Cuando hay zoom, alinear arriba/izquierda
                        if (currentZoom > 1) {
                            wrapper.style.alignItems = 'flex-start';
                            wrapper.style.justifyContent = 'flex-start';
                            
                            // Si es un nuevo zoom (no estaba en zoom antes), resetear scroll a arriba
                            // Si ya estaba en zoom, mantener posición relativa
                            setTimeout(() => {
                                if (previousZoom <= 1) {
                                    // Primera vez que se hace zoom, empezar desde arriba
                                    modalBody.scrollTop = 0;
                                    modalBody.scrollLeft = 0;
                                }
                                // Si ya estaba en zoom, permitir scroll libre (no forzar posición)
                            }, 150);
                        } else {
                            // Cuando vuelve a 100%, resetear completamente para que se comporte como al inicio
                            // Remover estilos forzados y dejar que la imagen se ajuste naturalmente
                            img.style.width = '';
                            img.style.height = '';
                            img.style.maxWidth = '95vw';
                            img.style.maxHeight = 'calc(100vh - 140px)';
                            
                            // Resetear wrapper
                            wrapper.style.width = '';
                            wrapper.style.height = '';
                            wrapper.style.minWidth = '';
                            wrapper.style.minHeight = '';
                            wrapper.style.padding = '20px';
                            wrapper.style.alignItems = 'center';
                            wrapper.style.justifyContent = 'center';
                            
                            // Resetear overlay
                            if (overlay) {
                                overlay.style.width = '';
                                overlay.style.height = '';
                            }
                            
                            // Centrar cuando vuelve a zoom normal - esperar a que se ajuste naturalmente
                            requestAnimationFrame(() => {
                                setTimeout(() => {
                                    const scrollLeft = (modalBody.scrollWidth - modalBody.clientWidth) / 2;
                                    const scrollTop = (modalBody.scrollHeight - modalBody.clientHeight) / 2;
                                    modalBody.scrollLeft = Math.max(0, scrollLeft);
                                    modalBody.scrollTop = Math.max(0, scrollTop);
                                    // Recrear marcas de agua
                                    crearMarcasAgua();
                                }, 300);
                            });
                        }
                    }
                }
            }
            
            if (zoomLevel) {
                zoomLevel.textContent = Math.round(currentZoom * 100) + '%';
            }
            
            if (modalBody) {
                modalBody.style.cursor = currentZoom > 1 ? 'grab' : 'default';
                if (img) {
                    img.style.cursor = currentZoom > 1 ? 'grab' : 'default';
                }
            }
            
            setTimeout(() => {
                crearMarcasAgua();
            }, 250);
        }

        // Zoom con scroll del mouse
        document.addEventListener('wheel', function(e) {
            const modal = document.getElementById('modalZoomINE');
            if (modal && modal.classList.contains('show')) {
                const modalBody = document.getElementById('zoomModalBody');
                const imgZoom = document.getElementById('imgZoomINE');
                const container = document.getElementById('zoomContainer');
                
                // Verificar si el evento es dentro del modal
                if (modalBody && (modalBody.contains(e.target) || container && container.contains(e.target))) {
                    // Si hay zoom aplicado, permitir scroll normal
                    if (currentZoom > 1) {
                        // Si se presiona Ctrl o Cmd, hacer zoom en lugar de scroll
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            if (e.deltaY < 0) {
                                zoomInINE();
                            } else {
                                zoomOutINE();
                            }
                        }
                        // Si no hay Ctrl, permitir scroll normal para moverse por la imagen
                    } else {
                        // Si no hay zoom, hacer zoom con scroll
                        e.preventDefault();
                        if (e.deltaY < 0) {
                            zoomInINE();
                        } else {
                            zoomOutINE();
                        }
                    }
                }
            }
        }, { passive: false });

        // Manejo de touch para zoom con pinch y drag
        function handleZoomTouchStart(e) {
            if (e.touches.length === 2) {
                // Pinch zoom
                e.preventDefault();
                const touch1 = e.touches[0];
                const touch2 = e.touches[1];
                lastTouchDistance = Math.hypot(
                    touch2.clientX - touch1.clientX,
                    touch2.clientY - touch1.clientY
                );
            } else if (e.touches.length === 1 && currentZoom > 1) {
                // Drag cuando hay zoom - permitir arrastrar la imagen
                e.preventDefault();
                isDragging = true;
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                const modalBody = document.getElementById('zoomModalBody');
                scrollLeft = modalBody.scrollLeft;
                scrollTop = modalBody.scrollTop;
            }
        }

        function handleZoomTouchMove(e) {
            if (e.touches.length === 2) {
                // Pinch zoom
                e.preventDefault();
                const touch1 = e.touches[0];
                const touch2 = e.touches[1];
                const distance = Math.hypot(
                    touch2.clientX - touch1.clientX,
                    touch2.clientY - touch1.clientY
                );
                
                if (lastTouchDistance > 0) {
                    const scale = distance / lastTouchDistance;
                    currentZoom = Math.max(minZoom, Math.min(maxZoom, currentZoom * scale));
                    applyZoom();
                }
                
                lastTouchDistance = distance;
            } else if (isDragging && currentZoom > 1) {
                // Drag para mover la imagen cuando hay zoom
                e.preventDefault();
                const modalBody = document.getElementById('zoomModalBody');
                const x = e.touches[0].clientX;
                const y = e.touches[0].clientY;
                const walkX = (startX - x);
                const walkY = (startY - y);
                modalBody.scrollLeft = scrollLeft + walkX;
                modalBody.scrollTop = scrollTop + walkY;
            }
        }

        function handleZoomTouchEnd(e) {
            isDragging = false;
            lastTouchDistance = 0;
        }

        // Drag con mouse cuando hay zoom
        let isMouseDragging = false;
        let mouseStartX = 0;
        let mouseStartY = 0;
        let mouseScrollLeft = 0;
        let mouseScrollTop = 0;

        document.addEventListener('mousedown', function(e) {
            const modal = document.getElementById('modalZoomINE');
            if (modal && modal.classList.contains('show') && currentZoom > 1) {
                const modalBody = document.getElementById('zoomModalBody');
                if (modalBody && (modalBody.contains(e.target) || document.getElementById('zoomContainer').contains(e.target))) {
                    isMouseDragging = true;
                    mouseStartX = e.clientX;
                    mouseStartY = e.clientY;
                    mouseScrollLeft = modalBody.scrollLeft;
                    mouseScrollTop = modalBody.scrollTop;
                    modalBody.style.cursor = 'grabbing';
                    e.preventDefault();
                }
            }
        });

        document.addEventListener('mousemove', function(e) {
            if (isMouseDragging && currentZoom > 1) {
                const modalBody = document.getElementById('zoomModalBody');
                if (modalBody) {
                    const walkX = (mouseStartX - e.clientX);
                    const walkY = (mouseStartY - e.clientY);
                    modalBody.scrollLeft = mouseScrollLeft + walkX;
                    modalBody.scrollTop = mouseScrollTop + walkY;
                }
            }
        });

        document.addEventListener('mouseup', function() {
            if (isMouseDragging) {
                isMouseDragging = false;
                const modalBody = document.getElementById('zoomModalBody');
                if (modalBody) {
                    modalBody.style.cursor = currentZoom > 1 ? 'grab' : 'default';
                }
            }
        });
        
        // ========================================
        // PROTECCIÓN COMPLETA PARA VISOR DE PDF
        // ========================================

        // Función mejorada para desactivar descarga de imágenes y canvas
        function desactivarDescargaImagen(element) {
            if (!element) return;
            
            // Prevenir clic derecho (menú contextual)
            element.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }, true);
            
            // Prevenir arrastrar y soltar
            element.addEventListener('dragstart', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }, true);
            
            // Prevenir selección de texto/imagen
            element.addEventListener('selectstart', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }, true);
            
            // Prevenir copiar (Ctrl+C)
            element.addEventListener('copy', function(e) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }, true);
            
            // Prevenir todos los eventos de puntero que podrían usarse para copiar
            element.addEventListener('pointerdown', function(e) {
                if (e.button === 2) { // Clic derecho
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
            
            element.addEventListener('mousedown', function(e) {
                if (e.button === 2) { // Clic derecho
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
            
            // Estilos CSS para prevenir selección
            element.style.userSelect = 'none';
            element.style.webkitUserSelect = 'none';
            element.style.mozUserSelect = 'none';
            element.style.msUserSelect = 'none';
            element.style.webkitUserDrag = 'none';
            element.style.khtmlUserDrag = 'none';
            element.style.userDrag = 'none';
            element.setAttribute('draggable', 'false');
            element.setAttribute('oncontextmenu', 'return false;');
            
            // Para canvas, también prevenir toDataURL y exportación
            if (element.tagName === 'CANVAS') {
                // Envolver toDataURL para que devuelva vacío
                const originalToDataURL = element.toDataURL;
                element.toDataURL = function() {
                    console.warn('toDataURL bloqueado por seguridad');
                    return 'data:,';
                };
                
                // Bloquear getImageData si es posible
                try {
                    const ctx = element.getContext('2d');
                    if (ctx) {
                        const originalGetImageData = ctx.getImageData;
                        ctx.getImageData = function() {
                            console.warn('getImageData bloqueado por seguridad');
                            throw new Error('Operación no permitida');
                        };
                    }
                } catch (e) {
                    console.log('No se pudo bloquear getImageData');
                }
            }
        }

        // Proteger todo el contenedor del PDF
        function protegerContenedorPDF() {
            const pdfContainer = document.getElementById('documentoPdfContainer');
            const pdfWrapper = document.getElementById('documentoWrapper');
            const pdfViewerContainer = document.getElementById('pdfViewerContainer');
            const pdfCanvas = document.getElementById('pdfCanvas');
            const modalBody = document.getElementById('documentoModalBody');
            
            // Proteger todos los contenedores
            [pdfContainer, pdfWrapper, pdfViewerContainer, modalBody].forEach(container => {
                if (container) {
                    desactivarDescargaImagen(container);
                }
            });
            
            // Protección especial para el canvas
            if (pdfCanvas) {
                desactivarDescargaImagen(pdfCanvas);
                
                // Protección adicional con overlay invisible
                let overlay = pdfCanvas.parentElement.querySelector('.canvas-protection-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'canvas-protection-overlay';
                    overlay.style.cssText = `
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        z-index: 5;
                        pointer-events: none;
                    `;
                    pdfCanvas.parentElement.style.position = 'relative';
                    pdfCanvas.parentElement.appendChild(overlay);
                    
                    // Hacer que el overlay capture eventos de clic derecho
                    overlay.style.pointerEvents = 'auto';
                    desactivarDescargaImagen(overlay);
                }
            }
        }
        
        // Prevenir atajos de teclado para guardar/descargar en los modales
        function prevenirAtajosDescarga(modalId) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            
            modal.addEventListener('keydown', function(e) {
                // Prevenir Ctrl+S (Guardar)
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    return false;
                }
                // Prevenir Ctrl+P (Imprimir)
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    return false;
                }
                // Prevenir F12 (Herramientas de desarrollador)
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }
                // Prevenir Ctrl+Shift+I (Herramientas de desarrollador)
                if (e.ctrlKey && e.shiftKey && e.key === 'I') {
                    e.preventDefault();
                    return false;
                }
                // Prevenir Ctrl+Shift+J (Consola)
                if (e.ctrlKey && e.shiftKey && e.key === 'J') {
                    e.preventDefault();
                    return false;
                }
                // Prevenir Ctrl+U (Ver código fuente)
                if (e.ctrlKey && e.key === 'u') {
                    e.preventDefault();
                    return false;
                }
            });
        }
        
        // Crear marcas de agua cuando se cargan las imágenes del INE
        document.addEventListener('DOMContentLoaded', function() {
            // Observar cuando se cargan las imágenes del INE
            const imgFrente = document.getElementById('imgINEfrente');
            const imgReverso = document.getElementById('imgINEreverso');
            const imgZoom = document.getElementById('imgZoomINE');
            const imgDocumento = document.getElementById('imgDocumento');
            
            // Desactivar descarga en imágenes del INE
            [imgFrente, imgReverso].forEach(img => {
                if (img) {
                    desactivarDescargaImagen(img);
                    img.addEventListener('load', function() {
                        setTimeout(() => {
                            crearMarcasAgua();
                        }, 200);
                    });
                }
            });
            
            // Desactivar descarga en imagen de zoom del INE
            if (imgZoom) {
                desactivarDescargaImagen(imgZoom);
            }
            
            // Observar cuando se carga la imagen de zoom del INE
            if (imgZoom) {
                imgZoom.addEventListener('load', function() {
                    desactivarDescargaImagen(imgZoom);
                });
            }
            
            // Observar cuando se carga la imagen de documentos (EVIDENCIA, FAD_DOC, etc.)
            if (imgDocumento) {
                desactivarDescargaImagen(imgDocumento);
                imgDocumento.addEventListener('load', function() {
                    setTimeout(() => {
                        crearMarcasAgua();
                        desactivarDescargaImagen(imgDocumento);
                    }, 200);
                });
            }
            
            // Prevenir atajos de teclado en los modales
            prevenirAtajosDescarga('modalINE');
            prevenirAtajosDescarga('modalZoomINE');
            prevenirAtajosDescarga('modalDocumento');
            
            // Crear marcas de agua cuando se muestra el modal de documentos
            const modalDocumento = document.getElementById('modalDocumento');
            if (modalDocumento) {
                // Prevenir scroll del modal cuando se abre
                modalDocumento.addEventListener('show.bs.modal', function() {
                    // Guardar la posición actual del scroll
                    const scrollY = window.scrollY;
                    
                    // Prevenir scroll en el body y html usando position fixed
                    document.body.style.position = 'fixed';
                    document.body.style.top = `-${scrollY}px`;
                    document.body.style.width = '100%';
                    document.body.style.overflow = 'hidden';
                    document.body.style.overflowX = 'hidden';
                    document.body.style.overflowY = 'hidden';
                    document.body.style.paddingRight = '0';
                    
                    document.documentElement.style.overflow = 'hidden';
                    document.documentElement.style.overflowX = 'hidden';
                    document.documentElement.style.overflowY = 'hidden';
                    document.documentElement.style.paddingRight = '0';
                    
                    // Asegurar que el modal NO tenga scroll
                    modalDocumento.style.overflow = 'hidden';
                    modalDocumento.style.overflowX = 'hidden';
                    modalDocumento.style.overflowY = 'hidden';
                    
                    // Asegurar que el modal-dialog y modal-content no tengan scroll
                    const modalDialog = modalDocumento.querySelector('.modal-dialog');
                    const modalContent = modalDocumento.querySelector('.modal-content');
                    const modalBody = modalDocumento.querySelector('.modal-body');
                    const modalHeader = modalDocumento.querySelector('.modal-header');
                    
                    if (modalDialog) {
                        modalDialog.style.overflow = 'hidden';
                        modalDialog.style.overflowX = 'hidden';
                        modalDialog.style.overflowY = 'hidden';
                    }
                    if (modalContent) {
                        modalContent.style.overflow = 'hidden';
                        modalContent.style.overflowX = 'hidden';
                        modalContent.style.overflowY = 'hidden';
                    }
                    if (modalBody) {
                        modalBody.style.overflow = 'hidden';
                        modalBody.style.overflowX = 'hidden';
                        modalBody.style.overflowY = 'hidden';
                    }
                    if (modalHeader) {
                        modalHeader.style.overflow = 'hidden';
                        modalHeader.style.overflowX = 'hidden';
                        modalHeader.style.overflowY = 'hidden';
                    }
                    
                    // Prevenir scroll en el backdrop
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.style.overflow = 'hidden';
                        backdrop.style.overflowX = 'hidden';
                        backdrop.style.overflowY = 'hidden';
                    }
                });

                modalDocumento.addEventListener('shown.bs.modal', function() {
                    setTimeout(() => {
                        crearMarcasAgua();
                        // Asegurar que la imagen de documento tenga protección
                        const imgDoc = document.getElementById('imgDocumento');
                        if (imgDoc) {
                            desactivarDescargaImagen(imgDoc);
                        }
                        
                        // BLOQUEO COMPLETO DEL CLICK DERECHO EN EL VISOR DE PDF
                        bloquearClickDerechoPDF();
                    }, 300);
                });
                
                // Limpiar cuando se cierre el modal para evitar overlays bloqueantes
                modalDocumento.addEventListener('hidden.bs.modal', function() {
                    // Restaurar scroll del body y html cuando se cierra el modal
                    const scrollY = document.body.style.top;
                    document.body.style.position = '';
                    document.body.style.top = '';
                    document.body.style.width = '';
                    document.body.style.overflow = '';
                    document.body.style.overflowX = '';
                    document.body.style.overflowY = '';
                    document.body.style.paddingRight = '';
                    
                    document.documentElement.style.overflow = '';
                    document.documentElement.style.overflowX = '';
                    document.documentElement.style.overflowY = '';
                    document.documentElement.style.paddingRight = '';
                    
                    // Restaurar la posición del scroll si había una guardada
                    if (scrollY) {
                        window.scrollTo(0, parseInt(scrollY || '0') * -1);
                    }
                    
                    // Limpiar el embed si existe
                    const embedContainer = document.getElementById('visorPdfEmbed');
                    if (embedContainer) {
                        const embed = embedContainer.querySelector('embed');
                        if (embed) {
                            embed.src = '';
                        }
                        embedContainer.innerHTML = '';
                        embedContainer.style.display = 'none';
                    }
                    
                    // Asegurar que el body esté limpio
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                    
                    // Remover cualquier backdrop residual
                    const backdrops = document.querySelectorAll('.modal-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                });
            }
            
            // Cuando se muestra el modal de zoom del INE, asegurar protección
            const modalZoomINE = document.getElementById('modalZoomINE');
            if (modalZoomINE) {
                modalZoomINE.addEventListener('shown.bs.modal', function() {
                    setTimeout(() => {
                        const imgZoom = document.getElementById('imgZoomINE');
                        if (imgZoom) {
                            desactivarDescargaImagen(imgZoom);
                        }
                    }, 300);
                });
            }
            
            // Cuando se muestra el modal del INE, asegurar protección
            const modalINE = document.getElementById('modalINE');
            if (modalINE) {
                modalINE.addEventListener('shown.bs.modal', function() {
                    setTimeout(() => {
                        const imgFrente = document.getElementById('imgINEfrente');
                        const imgReverso = document.getElementById('imgINEreverso');
                        if (imgFrente) desactivarDescargaImagen(imgFrente);
                        if (imgReverso) desactivarDescargaImagen(imgReverso);
                    }, 300);
                });
            }
        });

        // Función para bloquear TODAS las combinaciones posibles del click derecho en el visor de PDF
        function bloquearClickDerechoPDF() {
            const embedContainer = document.getElementById('visorPdfEmbed');
            if (!embedContainer) return;
            
            // Lista de todos los elementos relacionados con el PDF que necesitan protección
            const elementosPDF = [
                embedContainer,
                embedContainer.querySelector('#pdfWrapperContrato'),
                embedContainer.querySelector('#watermarkOverlayPdf'),
                embedContainer.querySelector('#pdfProtectionOverlay'),
                embedContainer.querySelector('iframe'),
                embedContainer.querySelector('.watermark-container')
            ].filter(el => el !== null);
            
            // Agregar menuOverlay solo si existe (solo para CONTRATO)
            const menuOverlay = embedContainer.querySelector('#pdfMenuOverlay');
            if (menuOverlay) {
                elementosPDF.push(menuOverlay);
            }
            
            // Función para bloquear evento
            const bloquearEvento = function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                return false;
            };
            
            // Bloquear contextmenu (click derecho) en todos los elementos
            elementosPDF.forEach(elemento => {
                if (elemento) {
                    // Bloquear contextmenu
                    elemento.addEventListener('contextmenu', bloquearEvento, true);
                    
                    // Bloquear mousedown del botón derecho (button === 2)
                    elemento.addEventListener('mousedown', function(e) {
                        if (e.button === 2) {
                            bloquearEvento(e);
                        }
                    }, true);
                    
                    // Bloquear mouseup del botón derecho
                    elemento.addEventListener('mouseup', function(e) {
                        if (e.button === 2) {
                            bloquearEvento(e);
                        }
                    }, true);
                    
                    // Bloquear auxclick (click del botón derecho)
                    elemento.addEventListener('auxclick', function(e) {
                        if (e.button === 2) {
                            bloquearEvento(e);
                        }
                    }, true);
                }
            });
            
            // Bloquear a nivel de documento cuando el evento viene del área del PDF
            const bloquearEnDocumento = function(e) {
                const target = e.target;
                // Verificar si el target está dentro del área del PDF
                if (embedContainer && (embedContainer.contains(target) || elementosPDF.some(el => el && el.contains(target)))) {
                    bloquearEvento(e);
                }
            };
            
            // Bloquear contextmenu a nivel de documento (capture phase para máxima prioridad)
            const docContextHandler = bloquearEnDocumento;
            document.addEventListener('contextmenu', docContextHandler, { capture: true, passive: false });
            
            // Bloquear mousedown a nivel de documento
            const docMouseDownHandler = function(e) {
                if (e.button === 2) {
                    bloquearEnDocumento(e);
                }
            };
            document.addEventListener('mousedown', docMouseDownHandler, { capture: true, passive: false });
            
            // Bloquear mouseup a nivel de documento
            const docMouseUpHandler = function(e) {
                if (e.button === 2) {
                    bloquearEnDocumento(e);
                }
            };
            document.addEventListener('mouseup', docMouseUpHandler, { capture: true, passive: false });
            
            // Bloquear auxclick a nivel de documento
            const docAuxClickHandler = function(e) {
                if (e.button === 2) {
                    bloquearEnDocumento(e);
                }
            };
            document.addEventListener('auxclick', docAuxClickHandler, { capture: true, passive: false });
            
            // Bloquear a nivel de window (máxima prioridad)
            const bloquearEnWindow = function(e) {
                const target = e.target;
                if (embedContainer && (embedContainer.contains(target) || elementosPDF.some(el => el && el.contains(target)))) {
                    bloquearEvento(e);
                }
            };
            
            const winContextHandler = bloquearEnWindow;
            window.addEventListener('contextmenu', winContextHandler, { capture: true, passive: false });
            
            const winMouseDownHandler = function(e) {
                if (e.button === 2) {
                    bloquearEnWindow(e);
                }
            };
            window.addEventListener('mousedown', winMouseDownHandler, { capture: true, passive: false });
            
            const winMouseUpHandler = function(e) {
                if (e.button === 2) {
                    bloquearEnWindow(e);
                }
            };
            window.addEventListener('mouseup', winMouseUpHandler, { capture: true, passive: false });
            
            const winAuxClickHandler = function(e) {
                if (e.button === 2) {
                    bloquearEnWindow(e);
                }
            };
            window.addEventListener('auxclick', winAuxClickHandler, { capture: true, passive: false });
            
            // Limpiar listeners cuando se cierre el modal
            const modalDocumento = document.getElementById('modalDocumento');
            if (modalDocumento) {
                modalDocumento.addEventListener('hidden.bs.modal', function() {
                    document.removeEventListener('contextmenu', docContextHandler, { capture: true });
                    document.removeEventListener('mousedown', docMouseDownHandler, { capture: true });
                    document.removeEventListener('mouseup', docMouseUpHandler, { capture: true });
                    document.removeEventListener('auxclick', docAuxClickHandler, { capture: true });
                    window.removeEventListener('contextmenu', winContextHandler, { capture: true });
                    window.removeEventListener('mousedown', winMouseDownHandler, { capture: true });
                    window.removeEventListener('mouseup', winMouseUpHandler, { capture: true });
                    window.removeEventListener('auxclick', winAuxClickHandler, { capture: true });
                }, { once: true });
            }
            
            // Intentar bloquear dentro del iframe si es posible (puede fallar por CORS)
            const iframe = embedContainer.querySelector('iframe');
            if (iframe) {
                iframe.addEventListener('load', function() {
                    try {
                        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        if (iframeDoc) {
                            // Bloquear en el documento del iframe
                            iframeDoc.addEventListener('contextmenu', bloquearEvento, true);
                            iframeDoc.addEventListener('mousedown', function(e) {
                                if (e.button === 2) bloquearEvento(e);
                            }, true);
                            iframeDoc.addEventListener('mouseup', function(e) {
                                if (e.button === 2) bloquearEvento(e);
                            }, true);
                            iframeDoc.addEventListener('auxclick', function(e) {
                                if (e.button === 2) bloquearEvento(e);
                            }, true);
                            
                            // Bloquear en el body del iframe
                            if (iframeDoc.body) {
                                iframeDoc.body.addEventListener('contextmenu', bloquearEvento, true);
                                iframeDoc.body.addEventListener('mousedown', function(e) {
                                    if (e.button === 2) bloquearEvento(e);
                                }, true);
                                iframeDoc.body.addEventListener('mouseup', function(e) {
                                    if (e.button === 2) bloquearEvento(e);
                                }, true);
                                iframeDoc.body.addEventListener('auxclick', function(e) {
                                    if (e.button === 2) bloquearEvento(e);
                                }, true);
                            }
                        }
                    } catch (e) {
                        // Error de CORS - normal cuando el iframe carga contenido de otro dominio
                        // Los bloqueos externos seguirán funcionando
                    }
                });
            }
        }

        // NOTA: El sistema de zoom de iframe ha sido desactivado
        // Todos los documentos (FAD_DOC, FACTURA OK, VALIDACIONES OK) son PDFs
        // y usan SOLO el sistema de zoom de PDF.js (pdfScale, pdfZoomIn, pdfZoomOut)

        function cerrarZoomINE() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalZoomINE'));
            if (modal) {
                modal.hide();
                document.body.style.overflow = '';
            }
        }

        // Manejo de gestos táctiles para cerrar (swipe down) - solo si no hay zoom
        function handleTouchStart(e) {
            if (currentZoom <= 1) {
                touchStartY = e.touches[0].clientY;
            }
        }

        function handleTouchMove(e) {
            if (currentZoom <= 1) {
                touchEndY = e.touches[0].clientY;
            }
        }

        function handleTouchEnd(e) {
            // Si el usuario hace swipe hacia abajo significativo y no hay zoom, cerrar
            if (currentZoom <= 1 && touchEndY - touchStartY > 100) {
                cerrarZoomINE();
            }
            touchStartY = 0;
            touchEndY = 0;
        }

        // Cerrar con ESC (solo desktop)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modalZoom = bootstrap.Modal.getInstance(document.getElementById('modalZoomINE'));
                if (modalZoom) {
                    modalZoom.hide();
                    document.body.style.overflow = '';
                }
            }
        });

        // Restaurar overflow y resetear zoom cuando se cierra el modal
        document.getElementById('modalZoomINE').addEventListener('hidden.bs.modal', function() {
            document.body.style.overflow = '';
            resetZoomINE();
        });
    </script>

    <!-- PDF.js desde CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    
    <!-- CSS adicional para mayor protección -->
    <style>
        /* Protección adicional para el canvas del PDF */
        #pdfCanvas,
        #documentoPdfContainer,
        #documentoWrapper,
        #pdfViewerContainer {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            -webkit-user-drag: none !important;
            -khtml-user-drag: none !important;
            user-drag: none !important;
            -webkit-touch-callout: none !important;
        }
        
        /* Prevenir selección en todos los elementos del visor */
        #modalDocumento * {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
        }
        
        /* Overlay de protección para el canvas */
        .canvas-protection-overlay {
            cursor: default !important;
        }
    </style>
    
    <script>
        // Protección global del documento para prevenir atajos de teclado
        document.addEventListener('DOMContentLoaded', function() {
            // Prevenir atajos de teclado globales cuando hay un modal abierto
            document.addEventListener('keydown', function(e) {
                const modalDocumento = document.getElementById('modalDocumento');
                const isModalOpen = modalDocumento && modalDocumento.classList.contains('show');
                
                if (isModalOpen) {
                    // Prevenir Ctrl+S (Guardar)
                    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    // Prevenir Ctrl+P (Imprimir)
                    if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                    // Prevenir Ctrl+Shift+S (Guardar como)
                    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'S') {
                        e.preventDefault();
                        e.stopPropagation();
                        return false;
                    }
                }
            }, true);
            
            // Aplicar protección cuando se muestra el modal
            const modalDocumento = document.getElementById('modalDocumento');
            if (modalDocumento) {
                modalDocumento.addEventListener('shown.bs.modal', function() {
                    setTimeout(() => {
                        protegerContenedorPDF();
                    }, 100);
                });
            }
            
            // Navegación de páginas
            const btnPrev = document.getElementById('pdfPrev');
            const btnNext = document.getElementById('pdfNext');
            const btnZoomIn = document.getElementById('pdfZoomIn');
            const btnZoomOut = document.getElementById('pdfZoomOut');

            if (btnPrev) {
                btnPrev.addEventListener('click', async function() {
                    if (currentPage > 1) {
                        currentPage--;
                        await renderizarPaginaPDF(currentPage);
                    }
                });
            }

            if (btnNext) {
                btnNext.addEventListener('click', async function() {
                    if (currentPage < pdfTotalPages) {
                        currentPage++;
                        await renderizarPaginaPDF(currentPage);
                    }
                });
            }

            if (btnZoomIn) {
                btnZoomIn.addEventListener('click', async function() {
                    if (pdfScale < 3.0) {
                        pdfScale += 0.25;
                        await renderizarPaginaPDF(currentPage);
                    }
                });
            }

            if (btnZoomOut) {
                btnZoomOut.addEventListener('click', async function() {
                    if (pdfScale > 0.5) {
                        pdfScale -= 0.25;
                        await renderizarPaginaPDF(currentPage);
                    }
                });
            }
        });
        
        // Limpiar overlay de SweetAlert cuando se cierra cualquier modal
        document.addEventListener('DOMContentLoaded', function() {
            // Observar cuando se cierra cualquier SweetAlert
            const originalClose = Swal.close;
            Swal.close = function() {
                originalClose.call(this);
                setTimeout(() => {
                    document.body.classList.remove('swal2-shown');
                    document.body.style.overflow = '';
                    const swalOverlay = document.querySelector('.swal2-container');
                    if (swalOverlay && !swalOverlay.querySelector('.swal2-popup')) {
                        swalOverlay.remove();
                    }
                }, 100);
            };
        });
        
        // Configurar el worker de PDF.js
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }

        // Variables globales para el visor de PDF
        let pdfDoc = null;
        let currentPage = 1;
        let pdfScale = 1.0;
        let pdfTotalPages = 0;

        // Función para cargar y mostrar un PDF
        async function cargarPDFConPDFjs(url) {
            try {
                const pdfContainer = document.getElementById('documentoPdfContainer');
                const pdfCanvas = document.getElementById('pdfCanvas');
                const pdfControls = document.getElementById('pdfControls');
                
                if (!pdfContainer || !pdfCanvas) {
                    console.error('Elementos del visor PDF no encontrados');
                    return;
                }

                // Mostrar contenedor
                pdfContainer.style.display = 'block';
                if (pdfControls) {
                    pdfControls.style.display = 'flex';
                }

                // Mostrar loading
                Swal.fire({
                    title: 'Cargando PDF...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                // Cargar el PDF como blob para evitar descarga automática
                // Esto previene que el navegador intente descargar el archivo
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/pdf'
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const blob = await response.blob();
                
                // Convertir blob a ArrayBuffer para PDF.js
                const arrayBuffer = await blob.arrayBuffer();
                
                // Cargar el PDF desde el ArrayBuffer
                const loadingTask = pdfjsLib.getDocument({
                    data: arrayBuffer,
                    // Agregar parámetros para mejorar compatibilidad
                    disableAutoFetch: false,
                    disableStream: false
                });

                pdfDoc = await loadingTask.promise;
                pdfTotalPages = pdfDoc.numPages;
                currentPage = 1;

                // Actualizar información de páginas
                const currentPageSpan = document.getElementById('pdfCurrentPage');
                const totalPagesSpan = document.getElementById('pdfTotalPages');
                if (currentPageSpan) currentPageSpan.textContent = currentPage;
                if (totalPagesSpan) totalPagesSpan.textContent = pdfTotalPages;

                // Renderizar primera página
                await renderizarPaginaPDF(currentPage);

                // Cerrar loading
                Swal.close();

                // Mostrar modal de documento
                const modal = new bootstrap.Modal(document.getElementById('modalDocumento'));
                modal.show();

                // Crear marcas de agua después de renderizar
                setTimeout(() => {
                    if (typeof crearMarcasAgua === 'function') {
                        crearMarcasAgua();
                    }
                    // Desactivar descarga en el canvas
                    const pdfCanvas = document.getElementById('pdfCanvas');
                    if (pdfCanvas && typeof desactivarDescargaImagen === 'function') {
                        desactivarDescargaImagen(pdfCanvas);
                    }
                }, 300);

            } catch (error) {
                console.error('Error cargando PDF con PDF.js:', error);
                Swal.close();
                
                // Intentar cargar con iframe directamente (sin Google Viewer para evitar el botón)
                console.log('Intentando cargar PDF directamente con iframe...');
                const pdfContainer = document.getElementById('documentoPdfContainer');
                const pdfControls = document.getElementById('pdfControls');
                
                if (pdfContainer) {
                    // Ocultar controles de PDF.js
                    if (pdfControls) {
                        pdfControls.style.display = 'none';
                    }
                    
                    // Limpiar contenedor
                    pdfContainer.innerHTML = '';
                    
                    // Crear iframe directamente con el PDF
                    const iframe = document.createElement('iframe');
                    iframe.src = url;
                    iframe.style.width = '100%';
                    iframe.style.height = '100%';
                    iframe.style.border = '0';
                    iframe.setAttribute('type', 'application/pdf');
                    
                    const wrapper = document.createElement('div');
                    wrapper.style.width = '100%';
                    wrapper.style.height = '100%';
                    wrapper.style.display = 'flex';
                    wrapper.style.alignItems = 'center';
                    wrapper.style.justifyContent = 'center';
                    wrapper.style.position = 'relative';
                    wrapper.appendChild(iframe);
                    
                    pdfContainer.appendChild(wrapper);
                    pdfContainer.style.display = 'block';
                    
                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('modalDocumento'));
                    modal.show();
                } else {
                    Swal.fire({
                        title: 'Error al cargar PDF',
                        text: 'No se pudo cargar el PDF. Verifique que el archivo exista.',
                        icon: 'error',
                        didClose: () => {
                            // Limpiar overlay cuando se cierra
                            document.body.classList.remove('swal2-shown');
                            document.body.style.overflow = '';
                            const swalOverlay = document.querySelector('.swal2-container');
                            if (swalOverlay) {
                                swalOverlay.remove();
                            }
                        }
                    });
                }
            }
        }

        // Función para renderizar una página del PDF
        async function renderizarPaginaPDF(pageNum) {
            if (!pdfDoc) return;

            try {
                const pdfCanvas = document.getElementById('pdfCanvas');
                const page = await pdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: pdfScale });

                // Ajustar tamaño del canvas
                pdfCanvas.height = viewport.height;
                pdfCanvas.width = viewport.width;

                // Renderizar
                const renderContext = {
                    canvasContext: pdfCanvas.getContext('2d'),
                    viewport: viewport
                };

                await page.render(renderContext).promise;

                // Actualizar información de página
                const currentPageSpan = document.getElementById('pdfCurrentPage');
                if (currentPageSpan) currentPageSpan.textContent = pageNum;

                // Actualizar zoom
                const zoomLevel = document.getElementById('pdfZoomLevel');
                if (zoomLevel) zoomLevel.textContent = Math.round(pdfScale * 100) + '%';

                // Actualizar marcas de agua
                const watermark = document.getElementById('pdfWatermark');
                if (watermark) {
                    watermark.style.width = pdfCanvas.width + 'px';
                    watermark.style.height = pdfCanvas.height + 'px';
                }

                // Recrear marcas de agua después de que el canvas se renderice
                setTimeout(() => {
                    if (typeof crearMarcasAgua === 'function') {
                        crearMarcasAgua();
                    }
                    // Desactivar descarga en el canvas
                    if (pdfCanvas && typeof desactivarDescargaImagen === 'function') {
                        desactivarDescargaImagen(pdfCanvas);
                    }
                }, 100);

                // Recrear marcas de agua después de que el canvas se renderice
                setTimeout(() => {
                    if (typeof crearMarcasAgua === 'function') {
                        crearMarcasAgua();
                    }
                    // Asegurar protección del canvas
                    if (typeof desactivarDescargaImagen === 'function') {
                        desactivarDescargaImagen(pdfCanvas);
                    }
                }, 200);

            } catch (error) {
                console.error('Error renderizando página:', error);
            }
        }

    </script>

</div>


