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
        
            // Cambiar entre ID y Nombre
            function actualizarInputs() {
                    const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
                    document.getElementById('divNombre').style.display = modo === 'nombre' ? 'block' : 'none';
                    document.getElementById('divID').style.display = modo === 'id' ? 'block' : 'none';
            }
        
            document.querySelectorAll('input[name="modoBusqueda"]').forEach(el =>
                el.addEventListener('change', actualizarInputs)
            );
            actualizarInputs();
        
            // Botón limpiar filtros
            document.getElementById("btnResetFiltros").addEventListener("click", () => {
                document.getElementById("idCredito").value = "";
                document.getElementById("nombre").value = "";
                document.getElementById("modoID").checked = true;
                actualizarInputs();
            });
        
            // Validación antes de enviar
            document.getElementById("formBusqueda").addEventListener("submit", e => {
                const idCredito = document.getElementById("idCredito").value.trim();
                const modo = document.querySelector('input[name="modoBusqueda"]:checked')?.value;
        
                if (modo === "id" && idCredito === "") {
                    e.preventDefault();
                    return Swal.fire({
                        icon: "warning",
                        title: "Falta el ID Crédito",
                        text: "Por favor ingresa el ID del crédito."
                    });
                }
        
                // Loading
                Swal.fire({
                    title: "Procesando solicitud...",
                    text: "Espere un momento por favor.",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            });
        
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
        self::set("titulo", "Busqueda Gestiones SKY");
        self::set("script", $script);
        return self::render("gestiones_consulta");
    }



}
