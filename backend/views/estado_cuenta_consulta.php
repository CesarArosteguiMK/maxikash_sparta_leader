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







