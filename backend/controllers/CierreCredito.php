<?php

namespace Controllers;

use Core\Controller;
use Models\CierreCredito as CierreCreditoDAO;

class CierreCredito extends Controller
{
    // ─────────────────────────────────────────────
    // VISTA PRINCIPAL
    // ─────────────────────────────────────────────

    public function consulta()
    {
        $this->render('cierre_credito_consulta');
    }

    // ─────────────────────────────────────────────
    // API: LISTADO EN PROCESO
    // ─────────────────────────────────────────────

    public function getEnProceso()
    {
        $r = CierreCreditoDAO::getEnProceso();
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: LISTADO ENVIADO FINALIZADO
    // ─────────────────────────────────────────────

    public function getEnviadoFinalizado()
    {
        $r = CierreCreditoDAO::getEnviadoFinalizado();
        // Añadir el usuario de sesión para mostrarlo en la UI de validación
        $r['validador'] = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'Sin sesión';
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: CREAR REGISTRO
    // ─────────────────────────────────────────────

    public function crear()
    {
        $campos = ['id_credito', 'nombre_cliente', 'estatus'];

        $datos = [];
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
                self::respuestaJSON(self::respuesta(false, "Campo requerido faltante: $campo"));
            }
            $datos[$campo] = trim($_POST[$campo]);
        }

        $datos['usuario_alta'] = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';

        $r = CierreCreditoDAO::crear($datos);
        self::respuestaJSON($r);
    }

    // ─────────────────────────────────────────────
    // API: CAMBIAR ESTATUS
    // ─────────────────────────────────────────────

    public function cambiarEstatus()
    {
        $id      = isset($_POST['id'])      ? (int) $_POST['id']            : 0;
        $estatus = isset($_POST['estatus']) ? trim($_POST['estatus'])        : '';

        if ($id <= 0 || $estatus === '') {
            self::respuestaJSON(self::respuesta(false, 'Parámetros inválidos.'));
        }

        $usuario = $_SESSION['usuario_nombre'] ?? $_SESSION['usuario'] ?? 'sistema';

        $r = CierreCreditoDAO::cambiarEstatus($id, $estatus, $usuario);
        self::respuestaJSON($r);
    }
}
