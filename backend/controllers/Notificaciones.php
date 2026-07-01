<?php

namespace Controllers;

use Core\Controller;
use Models\Notificacion;

class Notificaciones extends Controller
{
    /**
     * GET/POST: lista notificaciones del usuario actual y cuenta de no leídas.
     * Solo se devuelven notificaciones donde id_persona = usuario en sesión (usuario_id, coherente con creación).
     */
    public function listar()
    {
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión inválida.', 'datos' => [], 'total_no_leidas' => 0]);
            return;
        }
        $result = Notificacion::listarConTotal($idUsuario, 50);
        $lista = is_array($result['lista']) ? $result['lista'] : [];
        $lista = array_values(array_filter($lista, function ($n) use ($idUsuario) {
            return (int)($n['id_persona'] ?? 0) === $idUsuario;
        }));
        foreach ($lista as &$n) {
            $payload = [];
            if (!empty($n['payload_json'])) {
                $decoded = json_decode((string) $n['payload_json'], true);
                if (is_array($decoded)) {
                    $payload = $decoded;
                }
            }
            $n['payload'] = $payload;
            unset($n['id_persona']);
            unset($n['payload_json']);
        }
        unset($n);
        $totalNoLeidas = count(array_filter($lista, function ($n) {
            return (int)($n['leida'] ?? 0) === 0;
        }));
        self::respuestaJSON([
            'success'         => true,
            'datos'           => $lista,
            'total_no_leidas' => $totalNoLeidas
        ]);
    }

    /**
     * POST: marca una notificación como leída. Body: { "id_notificacion": 123 }
     */
    public function marcarLeida()
    {
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario < 1) {
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
        $ok = Notificacion::marcarLeida($idNotif, $idUsuario);
        self::respuestaJSON(['success' => $ok, 'mensaje' => $ok ? 'OK' : 'No se pudo actualizar.']);
    }

    /**
     * POST: marca todas las notificaciones del usuario como leídas.
     */
    public function marcarTodasLeidas()
    {
        $idUsuario = (int)($_SESSION['usuario_id'] ?? 0);
        if ($idUsuario < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Sesión inválida.']);
            return;
        }
        $ok = Notificacion::marcarTodasLeidas($idUsuario);
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
