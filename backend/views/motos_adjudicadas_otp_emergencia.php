<div class="container-xxl flex-grow-1 container-p-y otp-legacy-page">
    <div class="otp-legacy-hero mb-4">
        <div>
            <div class="otp-legacy-kicker">Motos Adjudicadas</div>
            <h4 class="mb-1">OTP DE EMERGENCIA</h4>
            <p class="mb-0">
                Genera un c&oacute;digo de acceso compatible con MaxikashApp Legacy sin desplegar una nueva version de la app.
            </p>
        </div>
        <div class="otp-legacy-icon" aria-hidden="true">
            <i class="fa-solid fa-key"></i>
        </div>
    </div>

    <div class="row g-4 align-items-stretch">
        <div class="col-12 col-xl-5">
            <div class="card h-100 otp-legacy-card">
                <div class="card-body">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <span class="otp-legacy-badge-icon">
                            <i class="fa-solid fa-motorcycle"></i>
                        </span>
                        <div>
                            <h5 class="mb-1">Generar c&oacute;digo de acceso Legacy</h5>
                            <p class="text-muted mb-0">
                                Ingresa el ID de cr&eacute;dito. Sparta resolver&aacute; la operaci&oacute;n interna y guardar&aacute; <strong>codigo_entrega</strong>.
                            </p>
                        </div>
                    </div>

                    <label for="otpLegacyIdCredito" class="form-label fw-semibold">ID cr&eacute;dito</label>
                    <div class="input-group input-group-lg mb-3">
                        <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                        <input
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            class="form-control"
                            id="otpLegacyIdCredito"
                            placeholder="Ej. 1416538"
                            autocomplete="off"
                        >
                    </div>

                    <div class="d-grid gap-2 d-sm-flex">
                        <button type="button" class="btn btn-outline-primary flex-fill" id="otpLegacyBtnConsultar">
                            <i class="fa-solid fa-magnifying-glass me-1"></i>Consultar c&oacute;digo
                        </button>
                        <button type="button" class="btn btn-primary flex-fill" id="otpLegacyBtnGenerar">
                            <i class="fa-solid fa-key me-1"></i>Generar c&oacute;digo
                        </button>
                    </div>

                    <div class="alert alert-info mt-4 mb-0">
                        <div class="fw-semibold mb-1">Uso operativo</div>
                        El gestor dicta este c&oacute;digo al usuario de MaxikashApp Legacy. La app lo enviara en
                        <strong>codigo_entrega</strong> al endpoint Legacy de estatus.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card h-100 otp-legacy-result-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">Resultado</h5>
                            <p class="text-muted mb-0">Aqui se mostrara el c&oacute;digo activo o el c&oacute;digo generado.</p>
                        </div>
                        <span class="badge bg-label-primary">codigo_entrega</span>
                    </div>

                    <div id="otpLegacyResultado" class="otp-legacy-empty">
                        <i class="fa-regular fa-keyboard"></i>
                        <strong>Sin cr&eacute;dito consultado</strong>
                        <span>Consulta o genera un c&oacute;digo para comenzar.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.otp-legacy-page {
    --otp-navy: #253553;
    --otp-teal: #0f9b8e;
    --otp-border: #dbe6f3;
}
.otp-legacy-hero {
    background: linear-gradient(135deg, #253553 0%, #2663d9 100%);
    color: #fff;
    border-radius: 12px;
    padding: 1.5rem 1.75rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    box-shadow: 0 12px 26px rgba(37, 53, 83, .18);
}
.otp-legacy-kicker {
    font-size: .78rem;
    text-transform: uppercase;
    font-weight: 700;
    opacity: .78;
    letter-spacing: .04em;
}
.otp-legacy-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    background: rgba(255,255,255,.16);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex: 0 0 auto;
}
.otp-legacy-card,
.otp-legacy-result-card {
    border: 1px solid var(--otp-border);
    box-shadow: 0 10px 26px rgba(37, 53, 83, .06);
}
.otp-legacy-badge-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #ddfbf6;
    color: var(--otp-teal);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex: 0 0 auto;
}
.otp-legacy-empty,
.otp-legacy-codebox {
    min-height: 280px;
    border: 1px dashed var(--otp-border);
    border-radius: 12px;
    background: #f8fbff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 1.5rem;
    color: #64748b;
}
.otp-legacy-empty i {
    font-size: 2rem;
    color: #94a3b8;
    margin-bottom: .7rem;
}
.otp-legacy-codebox {
    align-items: stretch;
    text-align: left;
    color: #26364f;
}
.otp-legacy-code {
    font-size: clamp(2.8rem, 8vw, 5rem);
    line-height: 1;
    letter-spacing: .18em;
    font-weight: 800;
    text-align: center;
    color: var(--otp-navy);
    background: #fff;
    border: 1px solid var(--otp-border);
    border-radius: 14px;
    padding: 1rem .8rem;
    user-select: all;
}
.otp-legacy-meta {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: .75rem;
}
.otp-legacy-meta-item {
    border: 1px solid var(--otp-border);
    border-radius: 10px;
    padding: .7rem .8rem;
    background: #fff;
}
.otp-legacy-meta-item small {
    display: block;
    text-transform: uppercase;
    color: #64748b;
    font-weight: 700;
    font-size: .68rem;
}
.otp-legacy-meta-item strong {
    color: #26364f;
}
</style>

<script>
(function () {
    'use strict';

    const input = document.getElementById('otpLegacyIdCredito');
    const btnConsultar = document.getElementById('otpLegacyBtnConsultar');
    const btnGenerar = document.getElementById('otpLegacyBtnGenerar');
    const resultado = document.getElementById('otpLegacyResultado');

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, ch => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[ch]));
    }

    function idCredito() {
        return String(input.value || '').replace(/\D+/g, '').trim();
    }

    function setLoading(isLoading) {
        btnConsultar.disabled = isLoading;
        btnGenerar.disabled = isLoading;
        input.disabled = isLoading;
    }

    function notify(icon, title, text) {
        if (window.Swal) {
            return Swal.fire({ icon, title, text, confirmButtonText: 'Entendido' });
        }
        alert(`${title}\n${text || ''}`);
        return Promise.resolve();
    }

    async function fetchJson(url, options = {}) {
        const resp = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                ...(options.body ? { 'Content-Type': 'application/json' } : {})
            },
            ...options
        });
        const text = await resp.text();
        let data = null;
        try {
            data = text ? JSON.parse(text) : {};
        } catch (err) {
            data = { success: false, message: text || 'Respuesta invalida del servidor.' };
        }
        if (!resp.ok && data && data.success !== true) {
            data.http_status = resp.status;
        }
        return data;
    }

    function renderCodigo(data, modo = 'activo') {
        const codigo = String(data.codigo_entrega || '').trim();
        const detalle = data.detalle || {};
        const operacion = data.operacion || detalle.operacion || {};
        const credito = data.id_credito || operacion.id_credito || idCredito();
        const cliente = operacion.nombre_cliente || detalle.nombre_cliente || 'No disponible';
        const estatus = detalle.estatus || data.estatus || 'No disponible';
        const httpCode = data.http_code || detalle.http_code || '-';
        const label = modo === 'generado' ? 'Codigo generado' : 'Codigo activo';

        resultado.className = 'otp-legacy-codebox';
        resultado.innerHTML = `
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <span class="badge bg-label-success">${esc(label)}</span>
                <button type="button" class="btn btn-sm btn-outline-primary" id="otpLegacyBtnCopiar">
                    <i class="fa-regular fa-copy me-1"></i>Copiar
                </button>
            </div>
            <div class="otp-legacy-code mb-3">${esc(codigo || '------')}</div>
            <div class="otp-legacy-meta mb-3">
                <div class="otp-legacy-meta-item">
                    <small>ID credito</small>
                    <strong>#${esc(credito)}</strong>
                </div>
                <div class="otp-legacy-meta-item">
                    <small>Cliente</small>
                    <strong>${esc(cliente)}</strong>
                </div>
                <div class="otp-legacy-meta-item">
                    <small>Estatus Legacy</small>
                    <strong>${esc(estatus)}</strong>
                </div>
                <div class="otp-legacy-meta-item">
                    <small>HTTP servicio</small>
                    <strong>${esc(httpCode)}</strong>
                </div>
            </div>
            <div class="alert alert-warning mb-0">
                Comparte este codigo solo con el operador autorizado. MaxikashApp lo validara como <strong>codigo_entrega</strong>.
            </div>
        `;

        const copyBtn = document.getElementById('otpLegacyBtnCopiar');
        if (copyBtn && codigo) {
            copyBtn.addEventListener('click', async function () {
                try {
                    await navigator.clipboard.writeText(codigo);
                    notify('success', 'Codigo copiado', 'El codigo Legacy se copio al portapapeles.');
                } catch (err) {
                    notify('info', 'Codigo Legacy', codigo);
                }
            });
        }
    }

    function renderConsulta(data) {
        const codigo = String(data.codigo_entrega || '').trim();
        if (codigo) {
            renderCodigo(data, 'activo');
            return;
        }

        resultado.className = 'otp-legacy-empty';
        resultado.innerHTML = `
            <i class="fa-solid fa-circle-info"></i>
            <strong>Credito encontrado</strong>
            <span>No hay codigo_entrega activo para este credito.</span>
            <button type="button" class="btn btn-primary mt-3" id="otpLegacyBtnGenerarInline">
                <i class="fa-solid fa-key me-1"></i>Generar codigo de acceso Legacy
            </button>
        `;
        const btnInline = document.getElementById('otpLegacyBtnGenerarInline');
        if (btnInline) {
            btnInline.addEventListener('click', () => generar(false));
        }
    }

    async function consultar() {
        const id = idCredito();
        if (!id) {
            notify('warning', 'Credito requerido', 'Ingresa el ID de credito para consultar.');
            input.focus();
            return;
        }
        setLoading(true);
        try {
            const data = await fetchJson(`/MotosAdjudicadas/consultarCodigoAccesoLegacy?id_credito=${encodeURIComponent(id)}`);
            if (!data.success) {
                notify('error', 'No se pudo consultar', data.message || 'Credito no encontrado o servicio no disponible.');
                return;
            }
            renderConsulta(data);
        } finally {
            setLoading(false);
        }
    }

    async function generar(regenerar) {
        const id = idCredito();
        if (!id) {
            notify('warning', 'Credito requerido', 'Ingresa el ID de credito para generar el codigo.');
            input.focus();
            return;
        }
        setLoading(true);
        try {
            const data = await fetchJson('/MotosAdjudicadas/generarCodigoAccesoLegacy', {
                method: 'POST',
                body: JSON.stringify({ id_credito: id, regenerar: !!regenerar })
            });

            if (data.success && data.requiere_confirmacion_regenerar) {
                renderCodigo(data, 'activo');
                if (window.Swal) {
                    const confirm = await Swal.fire({
                        icon: 'question',
                        title: 'Ya existe un codigo activo',
                        html: `<div style="font-size:2rem;font-weight:800;letter-spacing:.18em;">${esc(data.codigo_entrega)}</div><p class="mt-2 mb-0">Quieres regenerarlo?</p>`,
                        showCancelButton: true,
                        confirmButtonText: 'Si, regenerar',
                        cancelButtonText: 'Conservar actual'
                    });
                    if (confirm.isConfirmed) {
                        await generar(true);
                    }
                }
                return;
            }

            if (!data.success) {
                notify('error', 'No se pudo generar', data.message || 'El servicio Legacy no permitio guardar el codigo.');
                return;
            }

            renderCodigo(data, 'generado');
            notify('success', 'Codigo generado', 'El codigo ya esta disponible para MaxikashApp Legacy.');
        } finally {
            setLoading(false);
        }
    }

    input.addEventListener('input', function () {
        this.value = this.value.replace(/\D+/g, '').slice(0, 12);
    });
    input.addEventListener('keydown', function (ev) {
        if (ev.key === 'Enter') {
            ev.preventDefault();
            consultar();
        }
    });
    btnConsultar.addEventListener('click', consultar);
    btnGenerar.addEventListener('click', () => generar(false));
})();
</script>
