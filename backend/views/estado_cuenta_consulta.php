<style>
    #listaResultados .list-group-item {
        background-color: #fff;       /* fondo blanco sólido */
        border-bottom: 1px solid #dee2e6; /* separación tipo Bootstrap */
        cursor: pointer;
    }

    #listaResultados .list-group-item:hover {
        background-color: #e9ecef;    /* hover suave */
    }

    #listaResultados strong {
        font-weight: 600;             /* resaltar coincidencias */
    }

    /* Mismo formato que otros calendarios del proyecto (flatpickr) */
    .flatpickr-calendar .flatpickr-monthDropdown-months {
        appearance: none !important;
        background-image: none !important;
        -webkit-appearance: none;
        -moz-appearance: none;
    }
    .flatpickr-calendar {
        z-index: 99999 !important;
        position: fixed !important;
        transform: scale(1.12);
        transform-origin: top left;
    }
    .flatpickr-calendar.open {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
    }
    .flatpickr-calendar .flatpickr-day.today {
        border-color: #696cff !important;
        border-width: 2px !important;
        font-weight: 600 !important;
        background-color: #f0f0ff !important;
    }
    .flatpickr-calendar .flatpickr-day.today:hover {
        background-color: #e0e0ff !important;
        border-color: #696cff !important;
    }
</style>


<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">Estado de Cuenta</h4>
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

                    <div class="form-check form-check-inline">
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
            <form method="POST" id="formBusqueda">

                <div class="row g-3 align-items-end">

                    <!-- ID -->
                    <div class="col-md-6" id="divID">
                        <label for="idCredito" class="form-label">ID de crédito</label>
                        <div class="input-group input-group-merge">
                            <input type="number" class="form-control" id="idCredito" name="idCredito"
                                   value="<?= $_POST['idCredito'] ?? '' ?>"
                                   placeholder="Ej.: 12345">
                            <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                        </div>
                    </div>

                    <!-- Fecha de corte personalizada (solo si tiene permiso 23) -->
                    <div class="col-auto" id="divFechaCorte" style="display: none;">
                        <label for="fechaCorte" class="form-label small mb-1">
                            <i class="fa fa-calendar-alt me-1"></i>Fecha de corte
                            <i class="fa fa-info-circle text-muted ms-1" style="cursor: help; font-size: 0.85rem;"
                               title="La consulta mostrará datos hasta esta fecha (corte histórico). Solo se mostrarán movimientos hasta el día seleccionado, no posteriores."></i>
                        </label>
                        <input type="text" class="form-control flatpickr-fecha-corte" id="fechaCorte" name="fechaCorte"
                               style="max-width: 10rem; cursor: pointer;"
                               placeholder="Seleccione fecha"
                               value="<?= htmlspecialchars($_POST['fechaCorte'] ?? '') ?>"
                               autocomplete="off" readonly
                               title="Clic para abrir el calendario">
                    </div>

                    <!-- Nombre -->
                    <div class="col-md-6 position-relative" id="divNombre" style="display: none;">
                        <label for="nombre" class="form-label">Nombre del Cliente</label>
                        <div class="input-group input-group-merge">
                            <input type="text" class="form-control" name="nombre" id="nombre"
                                   placeholder="Nombre completo o parcial" autocomplete="off"
                                   oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').toUpperCase()"
                                   style="text-transform: uppercase;">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                        </div>

                        <!-- DROPDOWN PROFESIONAL -->
                        <div id="listaResultados" class="list-group position-absolute shadow-sm"
                             style="top:100%; left:0; right:0; z-index:1000; display:none; max-height:200px; overflow-y:auto; border:1px solid #ced4da; border-radius:.25rem;">
                        </div>
                        <input type="hidden" name="idCreditoLista" id="idCreditoLista">
                    </div>



                    <div class="col-12">
                        <button type="submit" class="btn btn-outline-primary w-100" id="btnBuscar">Buscar</button>
                    </div>
                </div>

            </form>
        </div>



    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Calendario fecha de corte: mismo formato que otros calendarios del proyecto (flatpickr)
        const fechaCorteInput = document.getElementById('fechaCorte');
        if (fechaCorteInput && typeof flatpickr !== 'undefined') {
            const hoy = new Date();
            hoy.setHours(23, 59, 59, 999);
            flatpickr(fechaCorteInput, {
                dateFormat: 'Y-m-d',
                maxDate: hoy,
                allowInput: false,
                clickOpens: true,
                appendTo: document.body,
                locale: 'es',
                disableMobile: true
            });
        }

        const input = document.getElementById('nombre');
        const lista = document.getElementById('listaResultados');

        let debounce = null;
        let controller = null;

        input.addEventListener('keyup', () => {
            const termino = input.value.trim();
            clearTimeout(debounce);

            if (termino.length < 3) {
                lista.innerHTML = '';
                lista.style.display = 'none';
                return;
            }

            debounce = setTimeout(() => {
                // AbortController seguro
                let signal = undefined;
                if (window.AbortController) {
                    if (controller) controller.abort();
                    controller = new AbortController();
                    signal = controller.signal;
                }



                // ✅ Enviar POST con objeto compatible con $_POST
                fetch('/EstadoCuenta/getclientesEstadoCuentaNombre', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ nombre: termino }),
                    signal
                })
                    .then(res => {
                        if (!res.ok) throw new Error('HTTP error ' + res.status);
                        return res.json();
                    })
                    .then(resp => {
                        lista.innerHTML = '';

                        if (!resp.resultado || !resp.datos || resp.datos.length === 0) {
                            lista.style.display = 'none';
                            return;
                        }

                        resp.datos.forEach(item => {
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'list-group-item list-group-item-action';

                            // Resaltar coincidencia
                            const regex = new RegExp(`(${termino})`, 'gi');
                            btn.innerHTML = item.nombre_completo.replace(regex, '<strong>$1</strong>');

                            btn.onclick = () => {
                                input.value = item.nombre_completo;

                                // 🔴 AQUÍ SE GUARDA EL ID REAL
                                document.getElementById('idCreditoLista').value = item.id;

                                lista.innerHTML = '';
                                lista.style.display = 'none';
                            };

                            lista.appendChild(btn);
                        });

                        lista.style.display = 'block';
                        lista.style.maxHeight = '200px';
                        lista.style.overflowY = 'auto';
                    })
                    .catch(err => {
                        if (err && err.name !== 'AbortError') {
                            console.error('Autocomplete error:', err);
                        }
                    });

            }, 250);
        });

        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', e => {
            if (!input.contains(e.target) && !lista.contains(e.target)) {
                lista.innerHTML = '';
                lista.style.display = 'none';
            }
        });
    });

</script>







