/* Catalogos
 *
 */

const catEstatus_VG = {
    solicitada: "SOLICITADA",
    autorizada: "AUTORIZADA",
    entregada: "ENTREGADA",
    comprobada: "COMPROBADA",
    aceptada: "ACEPTADA",
    validada: "VALIDADA",
    finalizada: "FINALIZADA",
    cancelada: "CANCELADA",
    rechazada: "RECHAZADA"
}

/*
 * Configuraciones globales
 * Librerias:
 * Moment.js -> https://github.com/moment/moment/
 * Numeral.js -> https://github.com/adamwdraper/Numeral-js
 */
moment.locale("es-MX")
const MOMENT_FRONT = "DD/MM/YYYY"
const MOMENT_FRONT_HORA = "DD/MM/YYYY HH:mm:ss"
const MOMENT_BACK = "YYYY-MM-DD"
const MOMENT_BACK_HORA = "YYYY-MM-DD HH:mm:ss"

numeral.zeroFormat(0)
numeral.nullFormat(0)
const NUMERAL_MONEDA = "$ 0,0.00"
const NUMERAL_DECIMAL = "0,0.00"

const inputFechasRestart = {}

/*
 * Templates de mensajes de alerta
 * Librerias:
 * SweetAlert2 -> https://sweetalert2.github.io/
 */
const tipoMensaje = (mensaje, icono, config = null) => {
    let configMensaje = typeof mensaje === "object" ? { html: mensaje } : { text: mensaje }
    configMensaje.icon = icono
    if (config) Object.assign(configMensaje, config)
    return Swal.fire(configMensaje)
}

const showError = (mensaje) => tipoMensaje(mensaje, "error")
const showSuccess = (mensaje) => tipoMensaje(mensaje, "success")
const showInfo = (mensaje) => tipoMensaje(mensaje, "info")
const showWarning = (mensaje) => tipoMensaje(mensaje, "warning")
const showWait = (mensaje = null) => {
    const config = {
        title: "Procesando su petición",
        text: mensaje || "Espere un momento...",
        imageUrl: "/assets/img/wait.svg",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false
    }
    return tipoMensaje(mensaje, null, config)
}

window.loadGoogleCharts = (function() {
    let isLoaded = false;   // indica si google charts ya se cargó
    let queue = [];         // callbacks en espera

    return function(callback) {
        if (isLoaded) {
            callback();
            return;
        }

        queue.push(callback);

        if (!window.google) {
            // insertar script solo si no existe
            const script = document.createElement('script');
            script.src = 'https://www.gstatic.com/charts/loader.js';
            script.onload = function() {
                google.charts.load('current', { packages: ["orgchart"] });
                google.charts.setOnLoadCallback(() => {
                    isLoaded = true;
                    queue.forEach(cb => cb()); // ejecutar todos los callbacks en cola
                    queue = [];
                });
            };
            document.head.appendChild(script);
        }
    };
})();

window.mostrarMensajeAll = ({ tipo = 'info', titulo = null, mensaje = '' } = {}) => {
    const config = {
        icon: tipo, // 'success', 'error', 'warning', 'info'
        title: titulo || (tipo.charAt(0).toUpperCase() + tipo.slice(1)), // capitaliza tipo por defecto
        text: mensaje,
        allowOutsideClick: true,
        allowEscapeKey: true,
        showConfirmButton: true
    };

    Swal.fire(config);

    return Swal.fire(config);
};

const confirmarMovimiento = async (mensaje, titulo = "Confirmación") => {
    const config = {
        title: titulo,
        showCancelButton: true,
        confirmButtonText: "Si, continuar",
        cancelButtonText: "No",
        allowOutsideClick: false,
        allowEscapeKey: false,
        reverseButtons: true,
        keydownListenerCapture: true,
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        }
    }

    return await tipoMensaje(mensaje, "warning", config)
}

/*
 * Funcion para manejar peticiones con AJAX
 * Librerias:
 * jQuery -> https://jquery.com/
 */
const consultaServidor = (
    url,
    datos,
    fncOK,
    { metodo = "POST", tipoEsperado = "JSON", procesar = null, tipoContenido = null } = {}
) => {
    showWait()

    const configuracion = {
        url,
        data: datos,
        type: metodo,
        headers: { "Front-Request": "true" },
        success: (respuesta, textStatus, jqXHR) => {
            if (typeof respuesta === "string" && respuesta !== "") {
                let tipoContenido = jqXHR.getResponseHeader("Content-Type")
                try {
                    switch (tipoEsperado) {
                        case "JSON":
                            respuesta = JSON.parse(respuesta)
                            break
                        case "blob":
                            respuesta = new Blob([respuesta], { type: tipoContenido })
                            break
                        default:
                            console.warn("Tipo de respuesta no manejado:", tipoEsperado)
                            break
                    }
                } catch (e) {
                    console.error("Error al procesar la respuesta del servidor:", e)
                    Swal.close()
                    return {
                        success: false,
                        mensaje: "Error al procesar la respuesta del servidor."
                    }
                }
            }

            Swal.close()
            fncOK(respuesta)
        },
        error: () => {
            showError("El servidor responde con un error.\nIntente más tarde.")
        }
    }

    if (tipoContenido != null) configuracion.contentType = tipoContenido
    if (procesar != null) configuracion.processData = procesar

    $.ajax(configuracion)
}

/*
 * Funciones para configruación y uso de tablas
 * Librerias:
 * DataTables -> https://datatables.net/
 */
const configuraTabla = (
    selector,
    { regXvista = true, buscar = true, footerInfo = true, paginacion = true, ordenar = true } = {}
) => {
    const configuracion = {
        lengthMenu: [
            [10, 40, -1],
            [10, 40, "Todos"]
        ],
        order: [],
        autoWidth: false,
        language: {
            emptyTable: "No hay datos disponibles",
            info: "Mostrando de _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Sin registros para mostrar",
            zeroRecords: "No se encontraron registros",
            lengthMenu: "Mostrar _MENU_ registros",
            search: "Buscar:"
        },
        columnDefs: [
            {
                className: "control",
                orderable: false,
                searchable: false,
                responsivePriority: 2,
                targets: 0,
                render: function (data, type, full, meta) {
                    return ""
                }
            }
        ],
        responsive: {
            details: {
                type: "inline",
                target: "tr"
            }
        }
    }

    configuracion.lengthChange = regXvista
    configuracion.searching = buscar
    configuracion.info = footerInfo
    configuracion.paging = paginacion
    configuracion.ordering = ordenar

    $(selector).DataTable(configuracion)
}

const actualizaDatosTabla = (selector, datos, mantenerPagina = false) => {
    let paginaActual = null
    const tabla = $(selector).DataTable()

    if (mantenerPagina) paginaActual = tabla.page.info().page

    tabla.clear()
    if (Array.isArray(datos)) {
        datos.forEach((item) => {
            if (Array.isArray(item)) tabla.row.add(item).draw(false)
            else tabla.row.add(Object.values(item)).draw(false)
        })
    }

    if (paginaActual !== null) tabla.page(paginaActual).draw(false)
    tabla.draw()
}

const buscarEnTabla = (selector, columna, texto) => {
    const tabla = $(selector).DataTable()
    return tabla
        .rows()
        .data()
        .toArray()
        .filter((dato) => dato[columna] == texto)
}

/*
 * Configruación para inputs de tipo fecha
 * Librerias:
 * Date Range Picker -> https://github.com/dangrossman/daterangepicker
 */
const setInputFechas = (
    selector,
    {
        iniF = null,
        finF = null,
        iniD = null,
        finD = null,
        minF = null,
        maxF = null,
        minD = null,
        maxD = null,
        rango = false,
        enModal = false
    } = {}
) => {
    const ini = iniF ? moment(iniF, MOMENT_FRONT) : moment().add(iniD, "days")
    let fin = finF ? moment(finF, MOMENT_FRONT) : moment().add(finD, "days")
    const min = minF ? moment(minF, MOMENT_FRONT) : moment().add(minD, "days")
    const max = maxF ? moment(maxF, MOMENT_FRONT) : moment().add(maxD, "days")
    if (!rango) fin = null

    const config = {
        locale: {
            format: MOMENT_FRONT,
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            separator: " ➝ "
        },
        linkedCalendars: false,
        showDropdowns: true,
        singleDatePicker: !rango,
        autoApply: true,
        // minYear: 2025,
        minDate: moment("01/01/2025", MOMENT_FRONT),
        // maxYear: moment().add(1, "years").year(),
        maxDate: moment().add(1, "year").endOf("year"),
        startDate: ini,
        endDate: fin
    }

    if (minF !== null || minD !== null) config.minDate = min
    if (maxF !== null || maxD !== null) config.maxDate = max
    if (enModal) config.parentEl = $(selector).closest(".modal-content")[0]

    $(selector).daterangepicker(config)
    inputFechasRestart[selector] = {
        inicio: config.startDate,
        fin: config.endDate
    }
}

const getInputFechas = (selector, rango = false, paraBack = true) => {
    const fecha = $(selector).data("daterangepicker")
    if (!fecha) return null
    const formato = paraBack ? MOMENT_BACK : MOMENT_FRONT
    const inicio = moment(fecha.startDate).format(formato)
    if (!rango) return inicio

    const fin = moment(fecha.endDate).format(formato)
    return { inicio, fin }
}

const updateInputFechas = (
    selector,
    {
        iniF = null,
        finF = null,
        iniD = null,
        finD = null,
        minF = null,
        maxF = null,
        minD = null,
        maxD = null
    } = {}
) => {
    const fecha = $(selector).data("daterangepicker")
    if (!fecha) return

    const ini = iniF ? moment(iniF, MOMENT_FRONT) : moment().add(iniD, "days")
    const fin = finF ? moment(finF, MOMENT_FRONT) : moment().add(finD, "days")
    const min = minF ? moment(minF, MOMENT_FRONT) : moment().add(minD, "days")
    const max = maxF ? moment(maxF, MOMENT_FRONT) : moment().add(maxD, "days")

    if (minF !== null || minD !== null) fecha.minDate = min
    if (maxF !== null || maxD !== null) fecha.maxDate = max

    fecha.setStartDate(iniF !== null || iniD !== null ? ini : inputFechasRestart[selector].inicio)
    if (fecha.singleDatePicker) fecha.setEndDate(fecha.startDate)
    else fecha.setEndDate(finF !== null || finD !== null ? fin : inputFechasRestart[selector].fin)

    inputFechasRestart[selector] = {
        inicio: fecha.startDate,
        fin: fecha.endDate
    }
}

/*
 * Configruación para inputs de tipo moneda
 * Librerias:
 * Numeral.js -> https://github.com/adamwdraper/Numeral-js
 * cleave-zen -> https://github.com/nosir/cleave-zen
 */
const setInputMoneda = (selector, { negativo = false } = {}) => {
    $(selector).each((index, input) => {
        registerCursorTracker({
            input
        })
    })

    $(selector).on("input blur", function () {
        $(this).val(
            formatNumeral($(this).val(), {
                numeralThousandsGroupStyle: "thousand"
            })
        )
    })
}

/*
 * Configruación para validaciones
 * Librerias:
 * Form-Validation -> https://formvalidation.io/
 */
const setValidacionModal = (
    selector,
    campos,
    btnVal,
    accionVal,
    btnCancel,
    { accionCancel = null, limpiar = true } = {}
) => {
    const camposFV = {}

    Object.keys(campos).forEach((campo) => {
        camposFV[campo] = {
            validators: campos[campo]
        }
    })

    const validador = FormValidation.formValidation($(selector)[0], {
        fields: camposFV,
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            submitButton: new FormValidation.plugins.SubmitButton(),
            defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
            bootstrap5: new FormValidation.plugins.Bootstrap5({
                defaultMessageContainer: false
            }),
            message: new FormValidation.plugins.Message({
                container: (field, element) => {
                    return $(element).closest(".form-group").find(".fv-message")[0]
                }
            })
        }
    })

    $(`${selector} ${btnVal}`).on("click", () => {
        validador.validate().then((validacion) => {
            if (validacion === "Valid") {
                if (accionVal) accionVal()
            } else showError("Debe corregir los errores marcados antes de continuar.")
        })
    })

    const cancelar = btnCancel ? `, ${selector} ${btnCancel}` : ""
    $(`${selector} .btn-close ${cancelar}`).on("click", () => {
        if (accionCancel) accionCancel()
        resetValidacion(validador, limpiar)
    })

    return validador
}

const resetValidacion = (validador, reset) => {
    validador.resetForm(reset)
    Object.keys(validador.elements).forEach((element) => {
        const elemento = validador.elements[element]
        if ($(elemento).hasClass("select2-hidden-accessible")) {
            $(elemento).val(null).trigger("change")
        } else if (
            $(elemento).prop("tagName").toLowerCase() === "select" &&
            $(elemento).find("option:disabled[value='']").length > 0
        ) {
            $(elemento).val("").trigger("change")
        } else if ($(elemento).data("daterangepicker")) {
            const fechasIniciales = inputFechasRestart["#" + $(elemento).attr("id")]
            if (fechasIniciales) {
                $(elemento).data("daterangepicker").setStartDate(fechasIniciales.inicio)
                $(elemento).data("daterangepicker").setEndDate(fechasIniciales.fin)
            }
        }
    })
}

/*
 * Función para los selectores de empresa, region y sucursal
 */
const setSelectEmpresaRegionSucursal = (
    empresa,
    region,
    sucursal,
    { empChange = null, regChange = null, sucChange = null } = {}
) => {
    const selEmpresa = $(empresa)
    const selRegion = $(region)
    const selSucursal = $(sucursal)

    selEmpresa.on("change", function () {
        const empresaId = $(this).find("option:selected").val()

        selRegion.prop("disabled", !empresaId)
        selRegion.val("")
        selRegion.find("option").each(function () {
            const regionEmpresaId = $(this).attr("data-empresa")
            if (regionEmpresaId !== empresaId) $(this).hide()
            else $(this).show()
        })

        selSucursal.prop("disabled", true)
        selSucursal.val("")

        if (empChange) empChange(empresaId)
    })

    selRegion.on("change", function () {
        const empresaId = $(this).find("option:selected").attr("data-empresa")
        const regionId = $(this).find("option:selected").val()

        selSucursal.prop("disabled", !regionId)
        selSucursal.val("")
        selSucursal.find("option").each(function () {
            const sucursalRegionId = $(this).attr("data-region")
            const sucursalEmpresaId = $(this).attr("data-empresa")
            if (sucursalRegionId !== regionId || sucursalEmpresaId !== empresaId) $(this).hide()
            else $(this).show()
        })

        if (regChange) regChange(empresaId, regionId)
    })

    selSucursal.on("change", function () {
        const empresaId = $(this).find("option:selected").attr("data-empresa")
        const regionId = $(this).find("option:selected").attr("data-region")
        const sucursalId = $(this).find("option:selected").val()

        if (sucChange) sucChange(empresaId, regionId, sucursalId)
    })
}

/*
 * Funcion para forzar las mayusculas en un input o textarea
 * Se debe agregar la clase "mayusculas" al input o textarea
 */
$(document).on("input", ".mayusculas", function () {
    $(this).val($(this).val().toUpperCase())
})

/*
 * Funcion para forzar el uso solo numeros en un input
 * Se debe agregar la clase "solo_numeros" al input
 */
$(document).on("input", ".solo_numeros", function () {
    $(this).val($(this).val().replace(/\D/g, ""))
})
