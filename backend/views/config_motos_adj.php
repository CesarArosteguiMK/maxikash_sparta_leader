<style>
.cma-hero {
    border: 1px solid #dbeafe;
    background: #f8fafc;
    border-radius: .5rem;
    padding: 1rem 1.15rem;
}
.cma-icon {
    width: 2.6rem;
    height: 2.6rem;
    border-radius: .5rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #e0f2fe;
    color: #0369a1;
    flex-shrink: 0;
}
.cma-rule-card {
    border: 1px solid #e2e8f0;
    border-radius: .5rem;
    box-shadow: 0 .1rem .55rem rgba(15,23,42,.05);
}
.cma-rule-label {
    display: block;
    color: #64748b;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
}
.cma-preview {
    background: #ecfeff;
    color: #155e75;
    border: 1px solid #a5f3fc;
    border-radius: .5rem;
    padding: .7rem .85rem;
    font-size: .82rem;
}
body.dark-mode .cma-hero,
body.dark-mode .cma-rule-card {
    background: #17212b;
    border-color: #334155;
    color: #e2e8f0;
}
body.dark-mode .cma-rule-label { color: #94a3b8; }
body.dark-mode .cma-preview {
    background: #164e63;
    color: #cffafe;
    border-color: #0e7490;
}
</style>

<div class="container-fluid py-3" id="configMotosAdjApp">
    <div class="cma-hero mb-3">
        <div class="d-flex align-items-center gap-3">
            <span class="cma-icon"><i class="fa-solid fa-motorcycle"></i></span>
            <div>
                <h5 class="mb-1">Config Motos Adj</h5>
                <p class="text-muted mb-0 small">
                    Parametros operativos para motos adjudicadas. Este modulo queda preparado para crecer con nuevas reglas sin tocar cada pantalla.
                </p>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-7">
            <div class="card cma-rule-card">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fa-solid fa-calendar-days me-2 text-primary"></i>
                        Fechas de rutas de recoleccion
                    </h6>
                </div>
                <div class="card-body">
                    <label class="cma-rule-label mb-1" for="cmaDiasMinRuta">Anticipacion minima</label>
                    <div class="input-group" style="max-width: 320px;">
                        <button class="btn btn-outline-secondary" type="button" id="cmaBtnMenos">
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center fw-semibold" id="cmaDiasMinRuta"
                               min="0" max="365" step="1" value="2">
                        <span class="input-group-text">días</span>
                        <button class="btn btn-outline-secondary" type="button" id="cmaBtnMas">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <div class="form-text mt-2">
                        Define desde cuántos días en el futuro se puede programar una ruta. Por ejemplo: 3 días, 4 días o 7 días.
                    </div>

                    <div class="cma-preview mt-3" id="cmaPreview">
                        Calculando fecha minima...
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-label-secondary" id="cmaBtnRecargar">
                        <i class="fa-solid fa-rotate me-1"></i>Recargar
                    </button>
                    <button type="button" class="btn btn-primary" id="cmaBtnGuardar">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Guardar cambios
                    </button>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card cma-rule-card h-100">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i class="fa-solid fa-layer-group me-2 text-info"></i>
                        Preparado para nuevas reglas
                    </h6>
                </div>
                <div class="card-body small text-muted">
                    <p class="mb-2">La configuracion se guarda como claves independientes, asi podemos agregar despues:</p>
                    <ul class="mb-0">
                        <li>ventanas maximas de recoleccion,</li>
                        <li>reglas por CEDIS,</li>
                        <li>bloqueo por días no laborales,</li>
                        <li>configuracion de ETA o transportistas.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const $dias = document.getElementById('cmaDiasMinRuta');
    const $preview = document.getElementById('cmaPreview');
    const $guardar = document.getElementById('cmaBtnGuardar');

    function clampDias(v) {
        const n = parseInt(v, 10);
        if (Number.isNaN(n)) return 2;
        return Math.max(0, Math.min(365, n));
    }

    function fechaMinimaTexto(dias) {
        const d = new Date();
        d.setDate(d.getDate() + dias);
        return d.toLocaleDateString('es-MX', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }

    function actualizarPreview() {
        const dias = clampDias($dias.value);
        $dias.value = String(dias);
        $preview.innerHTML = `<b>Con ${dias} día${dias === 1 ? '' : 's'} de anticipación:</b> la ruta más cercana se podrá programar a partir de <b>${fechaMinimaTexto(dias)}</b>.`;
    }

    async function cargar() {
        try {
            const r = await fetch('/ConfigMotosAdj/obtener', { headers: { 'Accept': 'application/json' } });
            const data = await r.json();
            const dias = data?.datos?.tracking?.ruta_dias_minimos;
            $dias.value = String(clampDias(dias));
            actualizarPreview();
        } catch (e) {
            actualizarPreview();
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar la configuracion.' });
        }
    }

    async function guardar() {
        const dias = clampDias($dias.value);
        $guardar.disabled = true;
        try {
            const r = await fetch('/ConfigMotosAdj/guardar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ ruta_dias_minimos: dias }),
            });
            const data = await r.json();
            if (!data.success) throw new Error(data.mensaje || 'No se pudo guardar.');
            Swal.fire({ icon: 'success', title: 'Listo', text: 'Configuracion guardada correctamente.', timer: 1600, showConfirmButton: false });
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Error', text: e.message || 'No se pudo guardar la configuracion.' });
        } finally {
            $guardar.disabled = false;
        }
    }

    document.getElementById('cmaBtnMenos')?.addEventListener('click', () => { $dias.value = String(clampDias($dias.value) - 1); actualizarPreview(); });
    document.getElementById('cmaBtnMas')?.addEventListener('click', () => { $dias.value = String(clampDias($dias.value) + 1); actualizarPreview(); });
    document.getElementById('cmaBtnRecargar')?.addEventListener('click', cargar);
    $dias?.addEventListener('input', actualizarPreview);
    $guardar?.addEventListener('click', guardar);
    cargar();
})();
</script>
