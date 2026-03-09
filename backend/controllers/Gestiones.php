<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresaDAO;
use Models\Gestiones as GestionesDAO;

class Gestiones extends Controller
{

    public function Seguimiento()
    {
        // --- JS COMPLETO EN EL CONTROLADOR ---
        $script = <<<JS
        <script>

        document.addEventListener("DOMContentLoaded", () => {

            // Cambiar entre ID y Nombre (solo en página de consulta con formulario)
            function actualizarInputs() {
                    const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                    const divNombre = document.getElementById('divNombre');
                    const divID = document.getElementById('divID');
                    if (divNombre) divNombre.style.display = modo === 'nombre' ? 'block' : 'none';
                    if (divID) divID.style.display = modo === 'id' ? 'block' : 'none';
            }

            document.querySelectorAll('input[name="modoBusqueda"]').forEach(el =>
                el.addEventListener('change', actualizarInputs)
            );
            actualizarInputs();

            const btnReset = document.getElementById("btnResetFiltros");
            if (btnReset) {
                btnReset.addEventListener("click", () => {
                    const idCredito = document.getElementById("idCredito");
                    const nombre = document.getElementById("nombre");
                    const modoID = document.getElementById("modoID");
                    if (idCredito) idCredito.value = "";
                    if (nombre) nombre.value = "";
                    if (modoID) modoID.checked = true;
                    actualizarInputs();
                });
            }

            const formBusqueda = document.getElementById("formBusqueda");
            if (formBusqueda) {
                formBusqueda.addEventListener("submit", e => {
                    const idCredito = document.getElementById("idCredito")?.value?.trim() ?? "";
                    const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;

                    if (modo === "id" && idCredito === "") {
                        e.preventDefault();
                        return Swal.fire({
                            icon: "warning",
                            title: "Falta el ID Crédito",
                            text: "Por favor ingresa el ID del crédito."
                        });
                    }

                    Swal.fire({
                        title: "Procesando solicitud...",
                        text: "Espere un momento por favor.",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => Swal.showLoading()
                    });
                });
            }

        });

        </script>
JS;
        $script_error = <<<JS
        <script>
                document.addEventListener('DOMContentLoaded',()=>mostrarMensajeAll({tipo:'error',titulo:'Error de busqueda',mensaje:'No se encontraron resultados'}));
        </script>
JS;

        # -----------------------------
        # PETICIÓN POST
        # -----------------------------
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $idCredito = $_POST['idCredito'] ?? null;
            $nombre = $_POST['nombre'] ?? null;

            $GestionesAll = GestionesDao::getAllGestiones($idCredito, $nombre);

            if (empty($GestionesAll)) {
                self::set("titulo", "Sin resultados para solicitud");
                self::set("errorGestiones", "No se encontraron resultados");
                $script_completo = $script . "\n" . $script_error;
                self::set("script", $script_completo);
                return self::render("gestiones_consulta");
            }

            self::set("gestiones", $GestionesAll);
            self::set("titulo", "Resultado de la solicitud");
            self::set("script", $script);
            return self::render("gestiones_request");
        }

        # -----------------------------
        # GET NORMAL
        # -----------------------------
        self::set("titulo", "Histórico de Gestiones");
        self::set("script", $script);
        return self::render("gestiones_consulta");
    }



}
