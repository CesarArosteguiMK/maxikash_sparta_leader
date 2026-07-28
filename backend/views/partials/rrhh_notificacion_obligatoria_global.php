<div class="modal fade" id="rrhhDocumentoObligatorioModal" tabindex="-1" aria-hidden="true">
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
                <button type="button" class="btn-close btn-close-white ms-auto" aria-label="Cerrar"
                        data-rrhh-cerrar></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert d-flex gap-2" data-rrhh-aviso>
                    <i class="fa-solid mt-1" data-rrhh-aviso-icono></i>
                    <div data-rrhh-aviso-texto></div>
                </div>
                <p class="mb-3" data-rrhh-mensaje></p>
                <div class="card bg-body-tertiary border-0 mb-3">
                    <div class="card-body">
                        <h6><i class="fa-solid fa-circle-check text-success me-2"></i>Cómo preparar el documento</h6>
                        <ul class="mb-3 ps-3" data-rrhh-instrucciones></ul>
                        <div class="d-flex flex-wrap gap-2" data-rrhh-enlaces></div>
                    </div>
                </div>
                <form data-rrhh-form>
                    <input type="hidden" name="id_campania" data-rrhh-campania>
                    <label class="form-label fw-semibold" for="rrhhDocumentoPdf">Constancia en PDF</label>
                    <input class="form-control form-control-lg" id="rrhhDocumentoPdf" type="file"
                           name="archivo" accept=".pdf,application/pdf" required>
                    <div class="form-text">Tamaño máximo: 15 MB. Se guardará como <strong data-rrhh-nombre></strong>.</div>
                    <div class="alert alert-danger mt-3 mb-0 d-none" data-rrhh-error></div>
                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-primary btn-lg w-100" type="submit" data-rrhh-enviar>
                            <i class="fa-solid fa-cloud-arrow-up me-2"></i>Entregar documento
                        </button>
                        <button class="btn btn-outline-secondary" type="button" data-rrhh-cerrar>
                            Recordarme después
                        </button>
                    </div>
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
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement, {backdrop: true, keyboard: true});
    const form = modalElement.querySelector('[data-rrhh-form]');
    const errorBox = modalElement.querySelector('[data-rrhh-error]');
    const submit = modalElement.querySelector('[data-rrhh-enviar]');
    const botonesCerrar = Array.from(modalElement.querySelectorAll('[data-rrhh-cerrar]'));
    const aviso = modalElement.querySelector('[data-rrhh-aviso]');
    const avisoIcono = modalElement.querySelector('[data-rrhh-aviso-icono]');
    const avisoTexto = modalElement.querySelector('[data-rrhh-aviso-texto]');
    const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    })[char]);
    let permitidoCerrar = false;
    let bloqueoObligatorio = true;

    const formatearFecha = value => {
        const partes = String(value || '').split('-');
        return partes.length === 3 ? `${partes[2]}/${partes[1]}/${partes[0]}` : String(value || '');
    };

    const mostrarError = (message) => {
        errorBox.textContent = message;
        errorBox.classList.remove('d-none');
    };
    const ocultarError = () => {
        errorBox.textContent = '';
        errorBox.classList.add('d-none');
    };
    const aplicar = (campania) => {
        bloqueoObligatorio = Number(campania.bloqueo_obligatorio || 0) === 1;
        permitidoCerrar = !bloqueoObligatorio;
        botonesCerrar.forEach(boton => boton.classList.toggle('d-none', bloqueoObligatorio));
        aviso.classList.toggle('alert-danger', bloqueoObligatorio);
        aviso.classList.toggle('alert-warning', !bloqueoObligatorio);
        avisoIcono.className = `fa-solid mt-1 ${bloqueoObligatorio ? 'fa-lock' : 'fa-clock'}`;
        if (bloqueoObligatorio) {
            avisoTexto.innerHTML = '<strong>La entrega ya es obligatoria.</strong> '
                + 'No podrás cerrar este aviso hasta que el PDF quede guardado correctamente.';
        } else {
            const fechaLimite = formatearFecha(campania.fecha_limite);
            avisoTexto.innerHTML = '<strong>Puedes cerrar este aviso por ahora.</strong> '
                + (fechaLimite
                    ? `A partir del ${escapeHtml(fechaLimite)} deberás entregar el PDF para continuar usando Sparta.`
                    : 'Cuando venza el plazo deberás entregar el PDF para continuar usando Sparta.');
        }
        modalElement.querySelector('[data-rrhh-titulo]').textContent = campania.titulo || 'Documento obligatorio';
        modalElement.querySelector('[data-rrhh-mensaje]').textContent = campania.mensaje || '';
        const instrucciones = Array.isArray(campania.instrucciones) ? campania.instrucciones : [];
        modalElement.querySelector('[data-rrhh-instrucciones]').innerHTML = instrucciones.length
            ? instrucciones.map(texto => `<li class="mb-1">${escapeHtml(texto)}</li>`).join('')
            : '<li>Prepara el documento completo, vigente y legible en formato PDF.</li>';
        const enlaces = Array.isArray(campania.enlaces) ? campania.enlaces : [];
        modalElement.querySelector('[data-rrhh-enlaces]').innerHTML = enlaces.map(enlace =>
            `<a class="btn btn-outline-primary" href="${escapeHtml(enlace.url)}" target="_blank" rel="noopener noreferrer">
                <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>${escapeHtml(enlace.label || 'Abrir enlace')}
            </a>`
        ).join('');
        modalElement.querySelector('[data-rrhh-campania]').value = campania.id;
        modalElement.querySelector('[data-rrhh-nombre]').textContent = campania.nombre_documento || campania.titulo;
        form.reset();
        modalElement.querySelector('[data-rrhh-campania]').value = campania.id;
        ocultarError();
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
    botonesCerrar.forEach(boton => boton.addEventListener('click', () => {
        if (permitidoCerrar) modal.hide();
    }));
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
            permitidoCerrar = !bloqueoObligatorio;
            mostrarError(error.message || 'No se pudo completar la entrega.');
        } finally {
            submit.disabled = false;
            submit.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-2"></i>Entregar documento';
        }
    });
    document.addEventListener('DOMContentLoaded', consultar, {once: true});
})();
</script>
