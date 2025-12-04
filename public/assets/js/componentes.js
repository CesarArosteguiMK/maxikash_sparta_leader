const menuAcciones = (opciones) => {
    const acciones = opciones
        .map((opcion) => {
            if (opcion === null || opcion === undefined) return ""
            if (opcion instanceof HTMLElement) return opcion.outerHTML
            if (opcion instanceof jQuery) return opcion[0].outerHTML
            if (typeof opcion === "string") {
                if (opcion === "divisor") return `<div class="dropdown-divider"></div>`
                return opcion
            }

            return `<a class="dropdown-item ${opcion.clase}" href="${
                opcion.href || "javascript:;"
            }" onclick="${opcion.funcion}">
                        <i class="fa ${opcion.icono}">&nbsp;</i>${opcion.texto}
                    </a>`
        })
        .join("")

    return `<button type="button" class="btn dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-vertical"></i></button>
        <div class="dropdown-menu">${acciones}</div>`
}

const mostrarArchivo = (url, { titulo = null, fncClose = null } = {}) => {
    const id = new Date().getTime()
    const idModal = `modalVisorArchivos_${id}`
    const idVisor = `archivoVisor_${id}`
    titulo = titulo || "Visor de Archivos"

    const modal = `<div class="modal fade" id="${idModal}" tabindex="-1" aria-hidden="true" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close btnCerrarVer" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center w-100">
                        <h4 class="address-title mb-2">${titulo}</h4>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 text-center">
                            <embed id="${idVisor}" src="${url}" style="width: 100%; min-height: 50vh;"></embed>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btnCerrarVer" data-bs-dismiss="modal" aria-label="Close">Cerrar</button>
                </div>
            </div>
        </div>
    </div>`

    $("body").append(modal)
    const modalElement = $(`#${idModal}`)
    modalElement.modal("show")
    modalElement.on("hidden.bs.modal", function () {
        if (typeof fncClose === "function") fncClose()
        $(this).remove()
    })
    return modalElement
}

const mostrarArchivoDescargado = (
    url,
    parametros = null,
    { metodo = "POST", titulo = null, fncClose = null } = {}
) => {
    showWait("Por favor, espere mientras se descarga el archivo.")
    fetch(url, {
        method: metodo,
        body: parametros
    })
        .then(async (response) => {
            if (!response.ok) throw new Error("Error al obtener el comprobante")
            const contentType = response.headers.get("Content-Type")
            if (contentType.includes("application/json")) {
                const data = await response.json()
                throw new Error(data.mensaje || "Error al obtener el comprobante")
            }
            return response.blob()
        })
        .then((blob) => {
            const urlModal = URL.createObjectURL(blob)
            mostrarArchivo(urlModal, {
                titulo,
                fncClose: () => {
                    URL.revokeObjectURL(urlModal)
                    if (typeof fncClose === "function") fncClose()
                }
            })
            Swal.close()
        })
        .catch((error) => {
            Swal.close()
            showError(error.message)
        })
}

const tomarFoto = (titulo, fncOK) => {
    titulo = titulo || "Tomar foto"
    let imagenCapturada = null

    const modal = `<div class="modal fade" id="modalTomarFoto" tabindex="-1" aria-hidden="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="text-center w-100">
                        <h4 class="address-title mb-2">${titulo}</h4>
                    </div>
                </div>
                <div class="modal-body">
                    <video id="videoComprobante" width="100%" height="auto" autoplay></video>
                    <canvas id="canvasComprobante" style="display: none;"></canvas>
                </div>
                <div class="modal-footer">
                    <div class="row w-100">
                        <div class="col-6">
                            <select id="selectorCamara" class="form-select">
                            </select>
                        </div>
                        <div class="col-6 d-flex justify-content-end">
                            <button id="btnCapturarFoto" class="btn btn-success"><i class="fa fa-camera">&nbsp;</i>Tomar foto</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>`

    const listarCamaras = async (selector) => {
        let camaras = window.camarasDisponibles

        if (!camaras) {
            try {
                await navigator.mediaDevices.getUserMedia({ video: true })
                const dispositivos = await navigator.mediaDevices.enumerateDevices()

                camaras = dispositivos.filter((d) => d.kind === "videoinput")
                window.camarasDisponibles = camaras
            } catch (error) {
                console.error("Error al listar cámaras:", error)
            }
        }

        if (camaras.length === 0) {
            selector.append("<option value=''>No se encontraron cámaras disponibles</option>")
            return
        }

        camaras.forEach((camara, index) => {
            const option = $("<option></option>")
            option.val(camara.deviceId)
            option.text(camara.label || "Cámara " + index + 1)
            selector.append(option)
        })
    }

    const iniciarCamara = async (selector, video) => {
        const configuracion = {
            video: {
                deviceId: { exact: selector.val() }
            },
            audio: false
        }

        try {
            detenerCamara(video)
            const stream = await navigator.mediaDevices.getUserMedia(configuracion)
            video.srcObject = stream
            video.play()
        } catch (error) {
            showError(
                "La cámara no se ha podido iniciar, verifique que la cámara esté conectada y tenga los permisos necesarios."
            )
        }
    }

    const detenerCamara = (video) => {
        if (video.srcObject) {
            const stream = video.srcObject
            const tracks = stream.getTracks()
            tracks.forEach((track) => track.stop())
            video.srcObject = null
        }
    }

    const capturarFoto = (modal, video, canvas) => {
        const contexto = canvas.getContext("2d")
        canvas.width = video.videoWidth
        canvas.height = video.videoHeight

        if (video.srcObject) {
            contexto.drawImage(video, 0, 0, canvas.width, canvas.height)
            canvas.toBlob((blob) => {
                const tiempo = moment().format("YYYYMMDD_HHmmss")
                const archivoFoto = new File([blob], "foto_" + tiempo + ".jpg", {
                    type: "image/jpeg"
                })
                const dataTransfer = new DataTransfer()
                dataTransfer.items.add(archivoFoto)
                imagenCapturada = dataTransfer.files
                modal.modal("hide")
            }, "image/jpeg")
        } else {
            showError("No hay imagen disponible para la foto.")
        }
    }

    $("body").append(modal)
    const modalElement = $("#modalTomarFoto")
    const videoElement = modalElement.find("#videoComprobante")[0]
    const canvasElement = modalElement.find("#canvasComprobante")[0]
    const selectorElement = modalElement.find("#selectorCamara")
    listarCamaras(selectorElement).then(() => {
        iniciarCamara(selectorElement, videoElement)
    })
    selectorElement.on("change", () => {
        iniciarCamara(selectorElement, videoElement)
    })
    modalElement.find("#btnCapturarFoto").on("click", () => {
        capturarFoto(modalElement, videoElement, canvasElement)
    })
    modalElement.modal("show")
    modalElement.on("hidden.bs.modal", function () {
        detenerCamara(videoElement)
        if (typeof fncOK === "function") fncOK(imagenCapturada)
        $(this).remove()
    })
}
