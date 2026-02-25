<?php

namespace Controllers;

use Core\Controller;
use Models\Notificacion;

class Notificaciones extends Controller
{
    /**
     * GET/POST: lista notificaciones del usuario actual y cuenta de no leídas.
     * Una sola ronda a BD (purga + sync una vez, luego listado + conteo).
     */
    public function listar()
    {
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => [], 'total_no_leidas' => 0]);
            return;
        }
        $result = Notificacion::listarConTotal($idPersona, 50);
        self::respuestaJSON([
            'success'         => true,
            'datos'           => $result['lista'],
            'total_no_leidas' => $result['total_no_leidas']
        ]);
    }

    /**
     * POST: marca una notificación como leída. Body: { "id_notificacion": 123 }
     */
    public function marcarLeida()
    {
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $raw = file_get_contents('php://input');
        $datos = json_decode($raw, true) ?: [];
        $idNotif = (int)($datos['id_notificacion'] ?? 0);
        if ($idNotif < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de notificación requerido.']);
            return;
        }
        $ok = Notificacion::marcarLeida($idNotif, $idPersona);
        self::respuestaJSON(['success' => $ok, 'mensaje' => $ok ? 'OK' : 'No se pudo actualizar.']);
    }

    /**
     * POST: marca todas las notificaciones del usuario como leídas.
     */
    public function marcarTodasLeidas()
    {
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        if ($idPersona < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $ok = Notificacion::marcarTodasLeidas($idPersona);
        self::respuestaJSON(['success' => $ok, 'mensaje' => $ok ? 'OK' : 'No se pudo actualizar.']);
    }

    /**
     * GET: diagnóstico para ver por qué no se activa la campana al reactivar la alerta del dictamen.
     * Devuelve: id_persona en sesión, tickets con dictamen no visto, filas en notificacion, si existen las tablas.
     */
    public function debugSync()
    {
        $idPersona = (int)($_SESSION['persona_id'] ?? $_SESSION['usuario_id'] ?? 0);
        $info = Notificacion::debugSync($idPersona);
        $info['session_persona_id'] = $_SESSION['persona_id'] ?? null;
        $info['session_usuario_id'] = $_SESSION['usuario_id'] ?? null;
        self::respuestaJSON(['success' => true, 'debug' => $info]);
    }
}
