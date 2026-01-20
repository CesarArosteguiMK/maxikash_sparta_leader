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
                                <option value="CONTRATO">Validaciones</option>
                                <option value="FACTURA">Factura</option>
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

    <!-- MODAL VISOR DE DOCUMENTOS (PDFs y otros) -->
    <div class="modal fade" id="modalDocumento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-lg-down" style="max-width: 95vw; width: 95vw;">
            <div class="modal-content" style="height: 95vh; display: flex; flex-direction: column;">

                <!-- HEADER -->
                <div class="modal-header flex-shrink-0">
                    <h5 class="modal-title">Documento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body p-0 flex-grow-1" id="documentoModalBody" style="height: calc(95vh - 60px); overflow: auto; position: relative; min-height: 0;">
                    <div id="documentoWrapper" style="display: flex; align-items: center; justify-content: center; min-height: 100%; padding: 20px;">
                        <iframe
                                id="visorDocumento"
                                src=""
                                style="width:100%;height:100%;border:0;transform-origin: top left; display: none;"
                                loading="lazy">
                        </iframe>
                        <img
                                id="visorImagen"
                                src=""
                                style="max-width: 100%; max-height: 100%; object-fit: contain; display: none;"
                                alt="Documento"
                                onerror="this.style.display='none'; document.getElementById('visorDocumento').style.display='block';">
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            const form = document.getElementById('formConsulta');
            
            form.addEventListener('submit', e => {
                e.preventDefault();
        
                const id   = document.getElementById('idDocumento').value.trim();
                const tipo = document.getElementById('tipoDocumento').value;
        
                if (!id || !tipo) {
                    Swal.fire('Error', 'Datos incompletos', 'error');
                    return;
                }
        
                Swal.fire({
                    title: 'Procesando',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
        
                fetch('/estadocuenta/descargar', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, tipo })
                })
                .then(res => res.json())
                .then(data => {
                    Swal.close();
        
                    if (!data.success) {
                        Swal.fire('Error', data.mensaje, 'error');
                        return;
                    }
        
                    // Para FAD_DOC, EVIDENCIA, FACTURA, CONTRATO y otros documentos, usar el visor normal
                    if (data.tipo && data.url) {
                        const visor = document.getElementById('visorDocumento');
                        const visorImagen = document.getElementById('visorImagen');
                        const wrapper = document.getElementById('documentoWrapper');
                        
                        // Actualizar título del modal según el tipo
                        const modalTitle = document.querySelector('#modalDocumento .modal-title');
                        if (modalTitle) {
                            const tipoNombre = {
                                'FAD_DOC': 'Contrato Firmado',
                                'EVIDENCIA': 'Foto Entrega Moto',
                                'FACTURA': 'Factura',
                                'CONTRATO': 'Validaciones'
                            };
                            modalTitle.textContent = tipoNombre[data.tipo] || 'Documento';
                        }
                        
                        // Si es EVIDENCIA (foto), mostrar directamente como imagen
                        if (data.tipo === 'EVIDENCIA') {
                            // Extraer la URL real del archivo desde el viewer de Google
                            // La URL del viewer contiene la URL real codificada
                            let imageUrl = data.url;
                            
                            // Intentar extraer la URL real del parámetro url del Google Viewer
                            try {
                                const urlParams = new URL(data.url);
                                const urlParam = urlParams.searchParams.get('url');
                                if (urlParam) {
                                    imageUrl = decodeURIComponent(urlParam);
                                }
                            } catch (e) {
                                console.log('No se pudo extraer URL del viewer, usando URL directa');
                            }
                            
                            // Si tenemos la URL del archivo directamente, usarla
                            if (data.archivo && data.carpeta) {
                                imageUrl = `http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=${data.carpeta}/${data.archivo}`;
                            }
                            
                            if (visorImagen) {
                                visorImagen.src = imageUrl;
                                visorImagen.style.display = 'block';
                                if (visor) visor.style.display = 'none';
                                
                                // Manejar errores de carga de imagen
                                visorImagen.onerror = function() {
                                    console.error('Error cargando imagen:', imageUrl);
                                    // Si falla la imagen, intentar con el viewer
                                    if (visor) {
                                        visor.src = data.url;
                                        visor.style.display = 'block';
                                        visorImagen.style.display = 'none';
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'No se pudo cargar la imagen. Verifique que el archivo exista en el servidor.',
                                            icon: 'error',
                                            footer: data.archivo ? 'Archivo: ' + data.archivo : ''
                                        });
                                    }
                                };
                                
                                // Mostrar información de debug
                                if (data.archivo) {
                                    console.log('Imagen cargada:', {
                                        tipo: data.tipo,
                                        archivo: data.archivo,
                                        carpeta: data.carpeta || 'N/A',
                                        url: imageUrl
                                    });
                                }
                            }
                        } else {
                            // Para otros documentos (PDFs), usar iframe con Google Viewer
                            if (visor) {
                                visor.src = data.url;
                                visor.style.display = 'block';
                                if (visorImagen) visorImagen.style.display = 'none';
                                
                                // Aplicar zoom predeterminado de 125% después de cargar el iframe
                                visor.onload = function() {
                                    setTimeout(() => {
                                        if (typeof applyZoomDocumento === 'function') {
                                            applyZoomDocumento();
                                        }
                                    }, 500);
                                };
                                
                                // Aplicar zoom inmediatamente también
                                setTimeout(() => {
                                    if (typeof applyZoomDocumento === 'function') {
                                        applyZoomDocumento();
                                    }
                                }, 300);
                                
                                // Mostrar información de debug
                                if (data.archivo) {
                                    console.log('Documento cargado:', {
                                        tipo: data.tipo,
                                        archivo: data.archivo,
                                        carpeta: data.carpeta || 'N/A',
                                        url: data.url
                                    });
                                }
                                
                                // Manejar errores de carga del iframe
                                visor.onerror = function() {
                                    console.error('Error cargando documento:', data);
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'No se pudo cargar el documento. Verifique que el archivo exista en el servidor.',
                                        icon: 'error',
                                        footer: data.archivo ? 'Archivo: ' + data.archivo : ''
                                    });
                                };
                            }
                        }
                        
                        const modal = new bootstrap.Modal(
                            document.getElementById('modalDocumento')
                        );
                        modal.show();
                        
                        // Aplicar zoom después de que el modal se muestre completamente (solo para iframe)
                        if (data.tipo !== 'EVIDENCIA') {
                            modal._element.addEventListener('shown.bs.modal', function() {
                                setTimeout(() => {
                                    if (typeof applyZoomDocumento === 'function') {
                                        applyZoomDocumento();
                                    }
                                }, 500);
                            }, { once: true });
                        }
                        
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Respuesta del servidor inválida. Tipo: ' + (data.tipo || 'N/A'),
                            icon: 'error'
                        });
                        console.error('Respuesta inválida:', data);
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'Error de comunicación', 'error');
                });
            });

            // Variables para zoom de documentos (FAD_DOC, EVIDENCIA, etc.)
            let currentZoomDocumento = 1.25; // Zoom predeterminado 125%
            let minZoomDocumento = 0.5;
            let maxZoomDocumento = 3;

            function zoomInDocumento() {
                if (currentZoomDocumento < maxZoomDocumento) {
                    currentZoomDocumento = Math.min(currentZoomDocumento + 0.25, maxZoomDocumento);
                    applyZoomDocumento();
                }
            }

            function zoomOutDocumento() {
                if (currentZoomDocumento > minZoomDocumento) {
                    currentZoomDocumento = Math.max(currentZoomDocumento - 0.25, minZoomDocumento);
                    applyZoomDocumento();
                }
            }

            function resetZoomDocumento() {
                currentZoomDocumento = 1.20; // Resetear a 120%
                applyZoomDocumento();
            }

            function applyZoomDocumento() {
                const iframe = document.getElementById('visorDocumento');
                const wrapper = document.getElementById('documentoWrapper');
                const modalBody = document.getElementById('documentoModalBody');
                
                if (iframe && wrapper && modalBody) {
                    // Aplicar zoom usando transform scale
                    iframe.style.transform = `scale(${currentZoomDocumento})`;
                    iframe.style.transformOrigin = 'top left';
                    
                    // Ajustar el tamaño del wrapper para permitir scroll cuando hay zoom
                    if (currentZoomDocumento >= 1) {
                        // Calcular el tamaño necesario del wrapper
                        const bodyWidth = modalBody.clientWidth;
                        const bodyHeight = modalBody.clientHeight;
                        const scaledWidth = bodyWidth * currentZoomDocumento;
                        const scaledHeight = bodyHeight * currentZoomDocumento;
                        
                        wrapper.style.width = `${scaledWidth}px`;
                        wrapper.style.height = `${scaledHeight}px`;
                        wrapper.style.minWidth = `${scaledWidth}px`;
                        wrapper.style.minHeight = `${scaledHeight}px`;
                        wrapper.style.alignItems = 'flex-start';
                        wrapper.style.justifyContent = 'flex-start';
                        wrapper.style.padding = '20px';
                        
                        // Asegurar que el iframe mantenga su tamaño original pero se escale
                        iframe.style.width = `${bodyWidth}px`;
                        iframe.style.height = `${bodyHeight}px`;
                        
                        // Resetear scroll al inicio cuando se aplica zoom
                        setTimeout(() => {
                            modalBody.scrollLeft = 0;
                            modalBody.scrollTop = 0;
                        }, 100);
                    } else {
                        // Si el zoom es menor a 1, centrar
                        wrapper.style.width = '';
                        wrapper.style.height = '';
                        wrapper.style.minWidth = '';
                        wrapper.style.minHeight = '';
                        wrapper.style.alignItems = 'center';
                        wrapper.style.justifyContent = 'center';
                        wrapper.style.padding = '20px';
                        iframe.style.width = '100%';
                        iframe.style.height = '100%';
                        
                        // Centrar
                        setTimeout(() => {
                            modalBody.scrollLeft = (modalBody.scrollWidth - modalBody.clientWidth) / 2;
                            modalBody.scrollTop = (modalBody.scrollHeight - modalBody.clientHeight) / 2;
                        }, 100);
                    }
                }
            }

            // Zoom con scroll del mouse en el documento
            const documentoModal = document.getElementById('modalDocumento');
            const documentoModalBody = document.getElementById('documentoModalBody');
            
            if (documentoModal && documentoModalBody) {
                documentoModalBody.addEventListener('wheel', function(e) {
                    if (e.ctrlKey || e.metaKey) {
                        e.preventDefault();
                        if (e.deltaY < 0) {
                            zoomInDocumento();
                        } else {
                            zoomOutDocumento();
                        }
                    }
                }, { passive: false });
            }

            // Botón limpiar filtros
            const btnResetFiltros = document.getElementById('btnResetFiltros');
            if (btnResetFiltros) {
                btnResetFiltros.addEventListener('click', () => {
                    document.getElementById('idDocumento').value = '';
                    document.getElementById('tipoDocumento').value = 'CONTRATO';
                });
            }
        });
    </script>
</div>
