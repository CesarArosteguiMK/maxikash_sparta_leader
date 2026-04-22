/*
 * Configuraciones globales JON
 */
const HTTP_CONFIG = {
    baseURL: "",

    apis: {
        interna: {
            baseURL: "",
            auth: null
        },

        externa: {
            baseURL: "https://api.externa.com/v1",
            auth: {
                type: "bearer",
                getToken: () => localStorage.getItem("api_token"),
                refresh: async () => {
                    const resp = await fetch("/auth/refresh-token")
                    const data = await resp.json()
                    if (!data?.token) throw new Error("No token")
                    localStorage.setItem("api_token", data.token)
                    return data.token
                }
            }
        }
    },

    timeout: 30000,
    retry: 1
}

const http = (() => {

    const buildURL = (endpoint, api) => {
        if (endpoint.startsWith("http")) return endpoint;
        if (api && HTTP_CONFIG.apis[api]) return HTTP_CONFIG.apis[api].baseURL + endpoint;
        return HTTP_CONFIG.baseURL + endpoint;
    };

    const buildHeaders = (api, customHeaders = {}) => {
        const cfg = HTTP_CONFIG.apis[api];
        let headers = { "Front-Request": "true", ...customHeaders };
        if (cfg?.auth?.type === "bearer") {
            const token = cfg.auth.getToken();
            if (token) headers.Authorization = `Bearer ${token}`;
        }
        if (cfg?.auth?.type === "apikey") {
            headers["X-API-KEY"] = cfg.auth.key;
        }
        return headers;
    };

    const handleUnauthorized = async (api, originalAjaxConfig) => {
        const auth = HTTP_CONFIG.apis[api]?.auth;
        if (!auth?.refresh) return false;
        try {
            const newToken = await auth.refresh();
            originalAjaxConfig.headers.Authorization = `Bearer ${newToken}`;
            $.ajax(originalAjaxConfig);
            return true;
        } catch {
            return false;
        }
    };

    const request = ({
                         endpoint,
                         api = "interna",
                         metodo = "POST",
                         data = {},
                         headers = {},
                         tipoEsperado = "json",
                         contentType = "application/x-www-form-urlencoded; charset=UTF-8",
                         processData = true,
                         showLoader = true,
                         timeout = HTTP_CONFIG.timeout,
                         retry = HTTP_CONFIG.retry,
                         onSuccess,
                         onError,
                         onAlways
                     }) => {

        if (typeof onSuccess !== "function") {
            console.error("onSuccess es obligatorio");
            return null;
        }

        let intento = 0;
        let xhr = null;

        const ejecutar = () => {
            if (showLoader) showWait();

            const ajaxConfig = {
                url: buildURL(endpoint, api),
                type: metodo,
                data,
                dataType: tipoEsperado,
                contentType,
                processData,
                timeout,
                headers: buildHeaders(api, headers),

                success: (resp, status, jqXHR) => {
                    try {
                        if (typeof resp === "string" && tipoEsperado !== "blob") {
                            resp = JSON.parse(resp);
                        }

                        if (tipoEsperado === "blob") {
                            const ct = jqXHR.getResponseHeader("Content-Type");
                            resp = new Blob([resp], { type: ct });
                        }

                        // success === false: fallo de negocio; no invocar onError (evita que modales
                        // con onError muestren "error de conexión" encima del mensaje de onSuccess).
                        if (resp && resp.success === false) {
                            onSuccess(resp);
                            if (typeof onError !== "function" && typeof showError === "function") {
                                const msgFallo = resp.mensaje || resp.error || "La operación no se completó.";
                                showError(msgFallo);
                            }
                            return;
                        }

                        onSuccess(resp);

                    } catch (err) {
                        if (err instanceof Error) {
                            manejarError(err, jqXHR);
                        }
                    }
                },

                    error: async (jqXHR, textStatus) => {
                        if (jqXHR.status === 401) {
                            const retryOk = await handleUnauthorized(api, ajaxConfig);
                            if (retryOk) return;
                        }

                        if (textStatus === "timeout" && intento < retry) {
                            intento++;
                            ejecutar();
                            return;
                        }

                        // Aquí sí hay fallo HTTP real
                        let mensaje = 
                            jqXHR.responseJSON?.mensaje ||
                            jqXHR.responseJSON?.error ||
                            jqXHR.responseText ||
                            "Error inesperado del servidor";
                        
                        // Si el mensaje es un objeto JSON stringificado, intentar parsearlo
                        if (typeof mensaje === "string" && mensaje.trim().startsWith("{")) {
                            try {
                                const parsed = JSON.parse(mensaje);
                                mensaje = parsed.error || parsed.mensaje || mensaje;
                            } catch (e) {
                                // Si no se puede parsear, usar el mensaje original
                            }
                        }
                        
                        if (typeof onError === "function") onError(mensaje, jqXHR);
                        else showError(mensaje);
                    },

                complete: () => {
                    if (showLoader) Swal.close();
                    if (typeof onAlways === "function") onAlways();
                }
            };

            xhr = $.ajax(ajaxConfig);
        };

        const manejarError = (logicError, jqXHR) => {
            // Extraer mensaje de diferentes fuentes
            let mensaje = 
                logicError?.mensaje ||
                logicError?.error ||
                jqXHR?.responseJSON?.mensaje ||
                jqXHR?.responseJSON?.error ||
                jqXHR?.responseText ||
                "Error inesperado del servidor";

            // Si el mensaje es un objeto JSON stringificado, intentar parsearlo
            if (typeof mensaje === "string" && mensaje.trim().startsWith("{")) {
                try {
                    const parsed = JSON.parse(mensaje);
                    mensaje = parsed.error || parsed.mensaje || mensaje;
                } catch (e) {
                    // Si no se puede parsear, usar el mensaje original
                }
            }

            console.warn("HTTP INFO", { endpoint, api, status: jqXHR?.status, mensaje });

            if (typeof onError === "function") {
                onError(mensaje, jqXHR);
            } else {
                showError(mensaje);
            }
        };

        ejecutar();
        return xhr;
    };

    return { request };
})();


const actualizaDatosTabla = (selector, datos, mantenerPagina = false) => {
    const tabla = $(selector).DataTable();
    let paginaActual = null;
    if (mantenerPagina) paginaActual = tabla.page.info().page;

    tabla.clear();

    if (Array.isArray(datos)) {
        datos.forEach(item => {
            if (Array.isArray(item)) tabla.row.add(item);
            else tabla.row.add(Object.values(item));
        });
    }

    if (paginaActual !== null) tabla.page(paginaActual);

    tabla.draw(); // solo UNA vez al final
};

const configuraTabla = (
    selector,
    {
        regXvista = true,
        buscar = true,
        footerInfo = true,
        paginacion = true,
        ordenar = true,
        responsive = true,
        registrosPorPagina = 10,
        columns = null, // <-- columnas personalizadas
        order = undefined  // orden inicial DataTables, ej. [[1, 'desc']]
    } = {}
) => {

    // Si no se pasan columnas, las detectamos del <thead>
    if (!columns) {
        const ths = $(`${selector} thead th`);
        columns = [];
        ths.each(function(index) {
            const title = $(this).text().trim();
            if (index === 0 && responsive) {
                // Primera columna para control responsive
                columns.push({ data: null, defaultContent: '', className: 'control', orderable: false });
            } else {
                columns.push({ data: title.toLowerCase().replace(/\s+/g, '_'), title: title });
            }
        });
    }

    const configuracion = {
        lengthMenu: [
            [10, 40, -1],
            [10, 40, "Todos"]
        ],
        pageLength: registrosPorPagina,
        order: order !== undefined ? order : [],
        autoWidth: false,
        columns: columns,
        language: {
            emptyTable: "No hay datos disponibles",
            info: "Mostrando de _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Sin registros para mostrar",
            zeroRecords: "No se encontraron registros",
            lengthMenu: "Mostrar _MENU_ registros",
            search: "Buscar:"
        },
        lengthChange: regXvista,
        searching: buscar,
        info: footerInfo,
        paging: paginacion,
        ordering: ordenar,
        destroy: true
    };

    if (responsive) {
        configuracion.responsive = {
            details: {
                type: "inline",
                target: "tr"
            }
        };
    }

    const tabla = $(selector).DataTable(configuracion);
    
    // Obtener el ID de la tabla (sin el #)
    const tableId = $(selector).attr('id') || selector.replace('#', '');
    const valor = registrosPorPagina.toString();
    
    // Asegurar que el select muestre el valor correcto después de la inicialización
    tabla.on('init.dt', function() {
        const select = $(`select[name="${tableId}_length"]`);
        if (select.length) {
            select.val(valor);
        }
    });
    
    // También establecer el valor después de un pequeño delay para asegurar que el DOM esté listo
    setTimeout(function() {
        const select = $(`select[name="${tableId}_length"]`);
        if (select.length && select.val() !== valor) {
            select.val(valor);
        }
    }, 50);

    return tabla;
};


window.http = http


/*
 * Fin Configuraciones globales JON
 *
 *
 *
 *
 */



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
    // Si el mensaje es null o undefined, usar un valor por defecto
    if (mensaje === null || mensaje === undefined) {
        mensaje = "";
    }
    
    // Si el mensaje es un objeto JSON con error, extraer el mensaje
    let textoMensaje = mensaje;
    if (typeof mensaje === "object" && mensaje !== null) {
        // Si tiene propiedad 'error', usar ese mensaje
        if (mensaje.error) {
            textoMensaje = mensaje.error;
        }
        // Si tiene propiedad 'mensaje', usar ese mensaje
        else if (mensaje.mensaje) {
            textoMensaje = mensaje.mensaje;
        }
        // Si no tiene propiedades conocidas, convertir a string
        else {
            textoMensaje = JSON.stringify(mensaje);
        }
    }
    // Si es un objeto JSON stringificado, intentar parsearlo
    else if (typeof mensaje === "string" && mensaje.startsWith("{")) {
        try {
            const parsed = JSON.parse(mensaje);
            textoMensaje = parsed.error || parsed.mensaje || mensaje;
        } catch (e) {
            textoMensaje = mensaje;
        }
    }
    
    let configMensaje = { text: textoMensaje };
    if (icono !== null && icono !== undefined) {
        configMensaje.icon = icono;
    }
    if (config) Object.assign(configMensaje, config);
    return Swal.fire(configMensaje);
}

const showError = (mensaje) => {
    // Filtrar errores de "Recurso no disponible" solo en la página de layoutlegacy
    if (typeof mensaje === "string" && mensaje.includes("Recurso no disponible")) {
        // Verificar si estamos en la página de layoutlegacy
        const currentPath = window.location.pathname.toLowerCase();
        if (currentPath.includes("layoutlegacy") || currentPath.includes("layout_legacy")) {
            console.warn("Error de recurso no disponible ignorado en layoutlegacy:", mensaje);
            return; // No mostrar el error, solo loguearlo en consola
        }
    }
    tipoMensaje(mensaje, "error");
}
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
        {
            metodo = "POST",
            tipoEsperado = "JSON",
            procesar = null,
            tipoContenido = null
        } = {}
    ) => {
        if (typeof fncOK !== "function") {
            console.error("fncOK no es una función")
            return
        }

        showWait()

        const configuracion = {
            url,
            data: datos,
            type: metodo,
            headers: { "Front-Request": "true" },
            dataType: tipoEsperado === "JSON" ? "json" : undefined,

            success: (respuesta, textStatus, jqXHR) => {
                try {
                    if (tipoEsperado === "blob") {
                        const contentTypeResp = jqXHR.getResponseHeader("Content-Type")
                        respuesta = new Blob([respuesta], { type: contentTypeResp })
                    }

                    Swal.close()
                    fncOK(respuesta)

                } catch (e) {
                    console.error("Error procesando respuesta:", e)
                    Swal.close()
                    showError("Error al procesar la respuesta del servidor.")
                }
            },

            error: (jqXHR) => {
                Swal.close()
                const msg = jqXHR.responseJSON?.mensaje
                    || jqXHR.responseText
                    || "El servidor respondió con un error."
                showError(msg)
            }
        }

        if (tipoContenido !== null) configuracion.contentType = tipoContenido
        if (procesar !== null) configuracion.processData = procesar

        $.ajax(configuracion)
    }

/*
 * Funciones para configruación y uso de tablas
 * Librerias:
 * DataTables -> https://datatables.net/
 */


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
