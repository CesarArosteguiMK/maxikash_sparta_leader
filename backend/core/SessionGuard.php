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
            SELECT p.session_version,
                   p.force_logout,
                   ap.id_puesto,
                   pu.nombre AS nombre_puesto,
                   pu.departamento_id
            FROM persona p
            LEFT JOIN asigna_puesto ap ON ap.id = (
                SELECT ap_actual.id
                FROM asigna_puesto ap_actual
                WHERE ap_actual.id_persona = p.id
                  AND COALESCE(ap_actual.activo, 0) = 1
                ORDER BY ap_actual.id DESC
                LIMIT 1
            )
            LEFT JOIN puesto pu ON pu.id = ap.id_puesto
            WHERE p.id = :id
            LIMIT 1
        SQL;

        $row = $db->queryOne($sql, [
            'id' => $_SESSION['usuario_id'],
        ]);

        // 🔴 usuario eliminado o forzar logout — siempre consultar (no acotar a los 20 s de session_version)
        if (!$row || (int) ($row['force_logout'] ?? 0) === 1) {
            self::cerrar();
        }

        if (UsuarioFantasmaReporteria::es()) {
            $_SESSION['modulos'] = [UsuarioFantasmaReporteria::MODULO_COMPARATIVAS];
            $_SESSION['last_session_check'] = time();

            return;
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

        // El perfil puede cambiar mientras la sesión sigue abierta. Conservamos en
        // cabecera e inicio el puesto activo, nunca una asignación histórica.
        $_SESSION['nivel_puesto'] = (int) ($row['id_puesto'] ?? 0);
        $_SESSION['id_puesto'] = (int) ($row['id_puesto'] ?? 0);
        $_SESSION['nombre_puesto'] = trim((string) ($row['nombre_puesto'] ?? '')) ?: 'Usuario';
        $_SESSION['departamento'] = (int) ($row['departamento_id'] ?? 0);

        $_SESSION['last_session_check'] = time();
    }

    private static function cerrar()
    {
        session_unset();
        session_destroy();

        if (self::esSolicitudAjax()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'mensaje' => 'Tu sesión fue cerrada por seguridad. Inicia sesión nuevamente.',
                'redirect' => '/Login',
                'force_logout' => true,
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        header('Location: /Login');
        exit;
    }

    private static function esSolicitudAjax(): bool
    {
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));
        $frontRequest = strtolower((string) ($_SERVER['HTTP_FRONT_REQUEST'] ?? ''));
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));

        return $requestedWith === 'xmlhttprequest'
            || $frontRequest === 'true'
            || strpos($accept, 'application/json') !== false;
    }
}
