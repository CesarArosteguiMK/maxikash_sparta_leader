<?php

namespace Core;

use Core\Database;
use Models\Login as LoginDao;

class SessionGuard
{
    public static function validar()
    {
        if (!isset($_SESSION['login'], $_SESSION['usuario_id'])) {
            return;
        }

        $db = new Database();

        $sql = <<<SQL
            SELECT session_version, force_logout
            FROM persona
            WHERE id = :id
            LIMIT 1
        SQL;

        $row = $db->queryOne($sql, [
            'id' => $_SESSION['usuario_id'],
        ]);

        // 🔴 usuario eliminado o forzar logout — siempre consultar (no acotar a los 20 s de session_version)
        if (!$row || (int) ($row['force_logout'] ?? 0) === 1) {
            self::cerrar();
        }

        // 🔄 permisos actualizados (throttle: como mucho cada 20 s)
        $ultima = $_SESSION['last_session_check'] ?? 0;
        if (time() - $ultima < 20) {
            return;
        }

        if ((int) $row['session_version'] !== (int) $_SESSION['session_version']) {
            $_SESSION['modulos'] = LoginDao::getModulosUsuario(
                $_SESSION['usuario_id']
            );
            $_SESSION['session_version'] = (int) $row['session_version'];
        }

        $_SESSION['last_session_check'] = time();
    }

    private static function cerrar()
    {
        session_unset();
        session_destroy();
        header('Location: /Login');
        exit;
    }
}
