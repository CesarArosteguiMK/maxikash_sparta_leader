<div class="modal fade" id="rrhhDocumentoObligatorioModal" tabindex="-1"
     data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white p-4">
                <div class="d-flex gap-3 align-items-center">
                    <span class="rounded-circle bg-white text-primary d-inline-flex align-items-center justify-content-center"
                          style="width:48px;height:48px"><i class="fa-solid fa-file-shield fa-lg"></i></span>
                    <div>
                        <div class="small text-white-50 text-uppercase fw-bold">Comunicado importante</div>
                        <h5 class="modal-title mb-0" data-rrhh-titulo>Documento obligatorio</h5>
                    </div>
                </div>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning d-flex gap-2">
                    <i class="fa-solid fa-lock mt-1"></i>
                    <div><strong>Debes completar esta entrega para continuar usando Sparta.</strong>
                        El aviso se cerrará automáticamente cuando el PDF quede guardado.</div>
                </div>
                <p class="mb-3" data-rrhh-mensaje></p>
                <div class="card bg-body-tertiary border-0 mb-3">
                    <div class="card-body">
                        <h6><i class="fa-solid fa-circle-check text-success me-2"></i>Antes de comenzar</h6>
                        <p class="mb-2">Ten a la mano tu <strong>CURP, NSS y correo electrónico personal</strong>.</p>
                        <a class="btn btn-outline-primary" data-rrhh-url target="_blank" rel="noopener noreferrer">
                            <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Abrir portal oficial del IMSS
                        </a>
                    </div>
                </div>
                <ol class="ps-3 mb-4">
                    <li class="mb-1">Llena el formulario del IMSS y selecciona <strong>Reporte detallado</strong>.</li>
                    <li class="mb-1">Abre el correo que envía el IMSS y descarga la constancia.</li>
                    <li>Regresa a esta ventana y carga el archivo en formato PDF.</li>
                </ol>
                <form data-rrhh-form>
                    <input type="hidden" name="id_campania" data-rrhh-campania>
                    <label class="form-label fw-semibold" for="rrhhDocumentoPdf">Constancia en PDF</label>
                    <input class="form-control form-control-lg" id="rrhhDocumentoPdf" type="file"
                           name="archivo" accept=".pdf,application/pdf" required>
                    <div class="form-text">Tamaño máximo: 15 MB. Se guardará como <strong data-rrhh-nombre></strong>.</div>
                    <div class="alert alert-danger mt-3 mb-0 d-none" data-rrhh-error></div>
                    <button class="btn btn-primary btn-lg w-100 mt-4" type="submit" data-rrhh-enviar>
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i>Entregar documento
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(() => {
    'use strict';
    const modalElement = document.getElementById('rrhhDocumentoObligatorioModal');
    if (!modalElement || !window.bootstrap || !window.fetch) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {backdrop: 'static', keyboard: false});
    const form = modalElement.querySelector('[data-rrhh-form]');
    const errorBox = modalElement.querySelector('[data-rrhh-error]');
    const submit = modalElement.querySelector('[data-rrhh-enviar]');
    let permitidoCerrar = false;

    const mostrarError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    };
    const ocultarError = () => {
        errorBox.textContent = '';
        errorBox.classList.add('d-none');
    };
    const aplicar = (campania) => {
        modalElement.querySelector('[data-rrhh-titulo]').textContent = campania.titulo || 'Documento obligatorio';
        modalElement.querySelector('[data-rrhh-mensaje]').textContent = campania.mensaje || '';
        modalElement.querySelector('[data-rrhh-url]').href = campania.url_descarga;
        modalElement.querySelector('[data-rrhh-campania]').value = campania.id;
        modalElement.querySelector('[data-rrhh-nombre]').textContent = campania.nombre_documento || campania.titulo;
        form.reset();
        modalElement.querySelector('[data-rrhh-campania]').value = campania.id;
        ocultarError();
        permitidoCerrar = false;
        modal.show();
    };
    const consultar = async () => {
        try {
            const response = await fetch('/caphum/getNotificacionDocumentalObligatoriaPendiente', {
                credentials: 'same-origin', cache: 'no-store'
            });
            const result = await response.json();
            if (!response.ok || !result.success) return;
            if (result.datos) aplicar(result.datos);
            else {
                permitidoCerrar = true;
                modal.hide();
            }
        } catch (_error) {
            // Si no se puede consultar el servidor, no se bloquea la navegación sin certeza.
        }
    };

    modalElement.addEventListener('hide.bs.modal', event => {
        if (!permitidoCerrar) event.preventDefault();
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        ocultarError();
        const archivo = form.querySelector('[name="archivo"]').files[0];
        if (!archivo) return mostrarError('Selecciona la constancia en formato PDF.');
        if (archivo.type !== 'application/pdf' && !archivo.name.toLowerCase().endsWith('.pdf')) {
            return mostrarError('El archivo seleccionado no es un PDF.');
        }
        if (archivo.size <= 0 || archivo.size > 15 * 1024 * 1024) {
            return mostrarError('El PDF debe pesar como máximo 15 MB.');
        }
        submit.disabled = true;
        submit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        try {
            const response = await fetch('/caphum/subirNotificacionDocumentalObligatoria', {
                method: 'POST', credentials: 'same-origin', body: new FormData(form)
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.mensaje || 'No se pudo guardar el documento.');
            permitidoCerrar = true;
            modal.hide();
            if (window.Swal) {
                await Swal.fire({icon: 'success', title: 'Documento recibido', text: result.mensaje, allowOutsideClick: false});
            }
            await consultar();
        } catch (error) {
            permitidoCerrar = false;
            mostrarError(error.message || 'No se pudo completar la entrega.');
        } finally {
            submit.disabled = false;
            submit.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-2"></i>Entregar documento';
        }
    });
    document.addEventListener('DOMContentLoaded', consultar, {once: true});
})();
</script>
