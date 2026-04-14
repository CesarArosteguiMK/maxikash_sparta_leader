<?php $layoutVendorLite = true; ?>
<style>
    /* Card con esquinas más redondeadas */
    .estado-cuenta-card.card {
        border-radius: 1rem;
    }
    .estado-cuenta-card .card-body {
        border-radius: 0 0 1rem 1rem;
    }
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

    /* ═════════════════════════════════════════════════════════════
       IDENTIFICADOR VISUAL - BANDERA GUATEMALA
       ═════════════════════════════════════════════════════════════ */
    .bandera-lateral-guatemala {
        position: fixed;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1000;
        background: linear-gradient(135deg, #4997d0 0%, #357abd 100%);
        padding: 12px 10px;
        border-radius: 0 12px 12px 0;
        box-shadow: 4px 0 15px rgba(73, 151, 208, 0.4);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        min-width: 80px;
    }

    .bandera-lateral-guatemala:hover {
        padding-left: 14px;
        box-shadow: 6px 0 20px rgba(73, 151, 208, 0.6);
    }

    .bandera-lateral-guatemala .fi {
        font-size: 2.8rem;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    }

    .bandera-lateral-guatemala .texto-pais {
        color: white;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 1px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        text-align: center;
        line-height: 1.2;
    }

    /* Responsive: ocultar en móviles */
    @media (max-width: 768px) {
        .bandera-lateral-guatemala {
            display: none;
        }
    }

    /* Badge identificador en el título */
    .badge-guatemala {
        background: linear-gradient(135deg, #4997d0 0%, #357abd 100%);
        color: white;
        font-weight: 600;
        padding: 0.4em 0.8em;
        border-radius: 8px;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 8px rgba(73, 151, 208, 0.3);
    }
</style>

<!-- Bandera lateral izquierda - Identificador Guatemala -->
<div class="bandera-lateral-guatemala">
    <span class="fi fi-gt fis"></span>
    <span class="texto-pais">GUATEMALA</span>
</div>

<div class="container py-4">

    <!-- Título -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center gap-2">
                <h4 class="mb-0">Estado de Cuenta</h4>
                <span class="badge badge-guatemala">
                    <i class="fa-solid fa-flag me-1"></i> GUATEMALA
                </span>
            </div>
            <p class="text-muted small mb-0">En Guatemala la consulta es <strong>solo por ID de crédito</strong>.</p>
        </div>
    </div>

    <!-- Card principal -->
    <div class="card estado-cuenta-card">

        <!-- FORMULARIO -->
        <div class="card-body">
            <form method="POST" id="formBusqueda">
                <div class="row justify-content-between mb-3 g-2">
                    <div class="col-12 col-md-8">
                        <label class="form-label">Filtro</label>
                        <div class="d-flex align-items-center flex-wrap gap-3">
                            <div class="form-check form-check-inline m-0">
                                <input class="form-check-input" type="radio" name="modoBusqueda" id="modoID" value="id" checked>
                                <label class="form-check-label" for="modoID">ID de crédito</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 d-flex align-items-end justify-content-start justify-content-md-end gap-2 mt-2 mt-md-0">
                        <button id="btnCambioPais" class="btn btn-outline-info" type="button">
                            <i class="fa-solid fa-globe me-1"></i> Selecciona País
                        </button>
                        <button id="btnResetFiltros" class="btn btn-outline-secondary" type="button">Limpiar</button>
                    </div>
                </div>

                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-6">
                        <div id="divID">
                            <label for="idCredito" class="form-label">ID de crédito</label>
                            <div class="input-group input-group-merge">
                                <input type="number" class="form-control" id="idCredito" name="idCredito"
                                       value="<?= $_POST['idCredito'] ?? '' ?>"
                                       placeholder="Ej.: 12345">
                                <span class="input-group-text"><i class="fa fa-hashtag"></i></span>
                            </div>
                        </div>
                    </div>
                    <!-- Segunda columna: Fecha de corte (si tiene permiso, igual que en modo ID) -->
                    <div class="col-12 col-md-6" id="divFechaCorte" style="display: none;">
                        <label for="fechaCorte" class="form-label small mb-1">
                            <i class="fa fa-calendar-alt me-1"></i>Fecha de corte
                            <button type="button" class="btn p-0 border-0 bg-transparent text-muted ms-1 align-middle lh-1"
                                    style="cursor:pointer;"
                                    id="btnAyudaFechaCorte"
                                    aria-label="Información sobre fecha de corte"
                                    data-ayuda-fecha="<?= htmlspecialchars('La consulta mostrará datos hasta esta fecha (corte histórico). Solo se mostrarán movimientos hasta el día seleccionado, no posteriores.', ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fa fa-info-circle" style="font-size:0.85rem;" aria-hidden="true"></i>
                            </button>
                        </label>
                        <input type="text" class="form-control flatpickr-fecha-corte" id="fechaCorte" name="fechaCorte"
                               style="max-width: 10rem; cursor: pointer;"
                               placeholder="Seleccione fecha"
                               value="<?= htmlspecialchars($_POST['fechaCorte'] ?? '') ?>"
                               autocomplete="off" readonly
                               title="Clic para abrir el calendario">
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

        const btnAyudaFechaCorte = document.getElementById('btnAyudaFechaCorte');
        if (btnAyudaFechaCorte) {
            btnAyudaFechaCorte.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const txt = btnAyudaFechaCorte.getAttribute('data-ayuda-fecha') || '';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Fecha de corte',
                        text: txt,
                        confirmButtonText: 'Entendido',
                    });
                } else {
                    window.alert(txt);
                }
            });
        }

        // ═══════════════════════════════════════════════════════════
        // BOTÓN CAMBIAR PAÍS
        // ═══════════════════════════════════════════════════════════
        const btnCambioPais = document.getElementById('btnCambioPais');
        if (btnCambioPais) btnCambioPais.addEventListener('click', () => {
            Swal.fire({
                title: '<i class="fa-solid fa-globe"></i> Selecciona un País',
                html: `
                    <style>
                        .pais-card-modal {
                            background: rgba(255, 255, 255, 0.9);
                            border: 1px solid rgba(0, 0, 0, 0.1);
                            border-radius: 12px;
                            cursor: pointer;
                            transition: all 0.25s ease;
                            overflow: hidden;
                        }
                        .pais-card-modal:hover {
                            transform: translateY(-4px);
                            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
                        }
                        .pais-card-header-modal {
                            height: 100px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                        .pais-card-body-modal {
                            padding: 1rem;
                            text-align: center;
                        }
                        .pais-card-body-modal h5 {
                            color: #212529;
                        }
                        .pais-card-body-modal p {
                            color: #6c757d;
                        }

                        /* Dark Mode */
                        body.dark-mode .pais-card-modal {
                            background: rgba(40, 48, 70, 0.95);
                            border: 1px solid rgba(255, 255, 255, 0.1);
                        }
                        body.dark-mode .pais-card-modal:hover {
                            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
                        }
                        body.dark-mode .pais-card-body-modal h5 {
                            color: #e2e8f0;
                        }
                        body.dark-mode .pais-card-body-modal p {
                            color: #94a3b8;
                        }
                    </style>
                    <div class="row g-3 mt-2">
                        <div class="col-6">
                            <div class="pais-card-modal" onclick="window.location.href='/EstadoCuenta/Consulta'">
                                <div class="pais-card-header-modal" style="background: linear-gradient(135deg, #006847 0%, #ce1126 100%);">
                                    <span class="fi fi-mx fis" style="font-size: 3.5rem; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.3);"></span>
                                </div>
                                <div class="pais-card-body-modal">
                                    <h5 class="fw-bold mb-1" style="font-size: 1.1rem;">México</h5>
                                    <p class="text-muted small mb-0">Código: MX</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pais-card-modal" onclick="window.location.href='/EstadoCuenta/Guatemala'">
                                <div class="pais-card-header-modal" style="background: linear-gradient(135deg, #4997d0 0%, #357abd 100%);">
                                    <span class="fi fi-gt fis" style="font-size: 3.5rem; border-radius: 50%; box-shadow: 0 4px 15px rgba(0,0,0,0.3);"></span>
                                </div>
                                <div class="pais-card-body-modal">
                                    <h5 class="fw-bold mb-1" style="font-size: 1.1rem;">Guatemala</h5>
                                    <p class="text-muted small mb-0">Código: GT</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `,
                showConfirmButton: false,
                showCloseButton: true,
                width: '600px',
                padding: '2rem',
                customClass: {
                    closeButton: 'btn-close'
                }
            });
        });

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

        const alertaBusqueda = <?= json_encode($alertaBusqueda ?? null, JSON_UNESCAPED_UNICODE) ?>;
        if (alertaBusqueda) {
            Swal.fire(alertaBusqueda);
        }

        const btnResetFiltros = document.getElementById('btnResetFiltros');
        if (btnResetFiltros) {
            btnResetFiltros.addEventListener('click', () => {
                const idCreditoEl = document.getElementById('idCredito');
                const fechaCorteEl = document.getElementById('fechaCorte');

                if (idCreditoEl) idCreditoEl.value = '';
                if (fechaCorteEl) fechaCorteEl.value = '';
            });
        }

        const formBusqueda = document.getElementById('formBusqueda');
        if (formBusqueda) {
            formBusqueda.addEventListener('submit', function(e) {
                e.preventDefault();

                const idCreditoEl = document.getElementById('idCredito');
                const idCredito = (idCreditoEl?.value || '').trim();

                if (idCredito === '') {
                    return Swal.fire({
                        icon: 'warning',
                        title: 'Falta el ID de crédito',
                        text: 'Por favor ingresa el ID del crédito.'
                    });
                }

                Swal.fire({
                    title: 'Validando ID de crédito...',
                    text: 'Espere un momento por favor.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                formBusqueda.submit();
            });
        }
    });

</script>
