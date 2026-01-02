<?php

namespace Controllers;

use Core\Controller;
use Models\Permisos as PermisosDAO;

class Permisos extends Controller
{
    public function consulta()
    {
        $script = <<<HTML
<script>
const tabla = "#tablaPermisos";
let dataTable = null;

/* =============================
   CARGAR PERSONAS
============================= */
const cargarPersonas = () => {
    consultaServidor("/permisos/getPersonas", {}, (respuesta) => {
        if (!respuesta.success) {
            showError(respuesta.mensaje);
            return;
        }

     
        
                   const datos = respuesta.datos.map(d => [
                        '', // columna 0
                        d.nombre ?? '',     // columna 1
                        d.activo == 1 ? "Activo" : "Inactivo", // columna 2
                        `<button class="btn btn-sm btn-primary">Editar</button>` // columna 3
                    ]);

        if (!dataTable) {
            dataTable = configuraTabla(tabla);
        }

        dataTable.clear().rows.add(datos).draw();
    });
};

/* =============================
   DOM READY
============================= */
document.addEventListener("DOMContentLoaded", () => {
    cargarPersonas();
});

/* =============================
   ABRIR MODAL
============================= */
document.addEventListener("click", (e) => {
    const btn = e.target.closest(".btn-editar");
    if (!btn) return;

    const idPersona = btn.dataset.id;
    const nombre    = btn.dataset.nombre;

    document.getElementById("idPersona").value = idPersona;
    document.getElementById("nombrePersona").value = nombre;
    document.getElementById("contenedor-puestos").innerHTML = "Cargando...";

    consultaServidor("/permisos/getPuestosPersona", { idPersona }, (resp) => {
        if (!resp.success) {
            showError(resp.mensaje);
            return;
        }

        let html = "";
        resp.datos.forEach(p => {
            html += `
            <div class="col-md-4 mb-2">
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="puestos[]"
                           value="\${p.id}"
                           \${p.asignado ? "checked" : ""}>
                    <label class="form-check-label">\${p.nombre}</label>
                </div>
            </div>
            `;
        });

        document.getElementById("contenedor-puestos").innerHTML = html;
        new bootstrap.Modal(document.getElementById("modalPermisos")).show();
    });
});

/* =============================
   GUARDAR PERMISOS
============================= */
document.addEventListener("submit", (e) => {
    if (e.target.id !== "formPermisos") return;
    e.preventDefault();

    const formData = new FormData(e.target);

    consultaServidor("/permisos/guardarPermisos", formData, (resp) => {
        if (!resp.success) {
            showError(resp.mensaje);
            return;
        }

        bootstrap.Modal
            .getInstance(document.getElementById("modalPermisos"))
            .hide();

        cargarPersonas();
    });
});
</script>
HTML;

        self::set("titulo", "Permisos por Puesto");
        self::set("script", $script);
        self::render("permisos");
    }

    public function getPersonas()
    {
        self::respuestaJSON(
            PermisosDAO::listarPersonasConPuestos()
        );
    }

    public function getPuestosPersona()
    {
        $idPersona = $_POST["idPersona"] ?? null;

        if (!$idPersona) {
            self::respuestaJSONError("Persona inválida");
            return;
        }

        self::respuestaJSON(
            PermisosDAO::obtenerPuestosPersona($idPersona)
        );
    }

    public function guardarPermisos()
    {
        $idPersona = $_POST["idPersona"] ?? null;
        $puestos   = $_POST["puestos"] ?? [];

        if (!$idPersona) {
            self::respuestaJSONError("Persona inválida");
            return;
        }

        PermisosDAO::guardarPermisos($idPersona, $puestos);

        self::respuestaJSON([
            "success" => true,
            "mensaje" => "Permisos actualizados correctamente"
        ]);
    }
}
