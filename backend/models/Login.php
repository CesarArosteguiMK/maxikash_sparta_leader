<?php

namespace Models;

use Core\Model;
use Core\Database;

class Login extends Model
{
    public static function validaUsuario($datos)
    {
        $usuario  = trim((string) ($datos['usuario'] ?? ''));
        $password = trim((string) ($datos['password'] ?? ''));

        $queryConPassword = <<<SQL
        SELECT p.id, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom,
            p.numero_empleado, p.user_name, p.password,
            pp.id AS id_puesto, pp.nombre AS nombre_puesto, pp.departamento_id,
            p.session_version, p.force_logout
        FROM persona p
        LEFT JOIN asigna_puesto a ON a.id_persona = p.id
        LEFT JOIN puesto pp ON pp.id = a.id_puesto
        WHERE p.estatus = 'Activo' AND p.user_name = :usuario AND p.password = :password
        LIMIT 1
        SQL;

        $querySoloUsuario = <<<SQL
        SELECT p.id, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom,
            p.numero_empleado, p.user_name, p.password,
            pp.id AS id_puesto, pp.nombre AS nombre_puesto, pp.departamento_id,
            p.session_version, p.force_logout
        FROM persona p
        LEFT JOIN asigna_puesto a ON a.id_persona = p.id
        LEFT JOIN puesto pp ON pp.id = a.id_puesto
        WHERE p.estatus = 'Activo' AND p.user_name = :usuario
        LIMIT 1
        SQL;

        try {
            $db = new Database();

            // 1) Igual que siempre: usuario + contraseña en SQL (BD con contraseña en claro)
            $r = $db->queryOne($queryConPassword, ['usuario' => $usuario, 'password' => $password]);

            // 2) Si no hay fila, puede que la contraseña ya esté en hash (tras migración)
            if (!$r) {
                $r = $db->queryOne($querySoloUsuario, ['usuario' => $usuario]);
                if ($r) {
                    $almacenado = $r['password'] ?? '';
                    $esHash = strlen($almacenado) >= 60 && (strpos($almacenado, '$2y$') === 0 || strpos($almacenado, '$2a$') === 0 || strpos($almacenado, '$argon2') === 0);
                    if (!$esHash || !password_verify($password, $almacenado)) {
                        $r = null;
                    }
                }
            }

            if (!$r) {
                return self::resultado(false, 'Credenciales incorrectas.');
            }

            // Migrar a hash si la BD tiene contraseña en texto plano (requiere columna password VARCHAR(255))
            $almacenado = $r['password'] ?? '';
            $esHash = strlen($almacenado) >= 60 && (strpos($almacenado, '$2y$') === 0 || strpos($almacenado, '$2a$') === 0 || strpos($almacenado, '$argon2') === 0);
            if (!$esHash) {
                try {
                    $db->CRUD("UPDATE persona SET password = :pwd WHERE id = :id", [
                        'pwd' => password_hash($password, PASSWORD_DEFAULT),
                        'id'  => $r['id']
                    ]);
                } catch (\Exception $e) {
                    // Columna password demasiado corta (ej. VARCHAR(20)); ampliar a VARCHAR(255) para guardar hash
                    // Mientras tanto el login sigue siendo válido
                }
            }

            unset($r['password']);

            if ((int)($r['force_logout'] ?? 0) === 1) {
                $db->CRUD("UPDATE persona SET force_logout = 0 WHERE id = :id", ['id' => $r['id']]);
            }

            return self::resultado(true, 'Credenciales correctas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al procesar la solicitud.',
                null,
                $e->getMessage()
            );
        }
    }

    public static function getModulosUsuario($idPersona)
    {
        $query = <<<SQL
        SELECT modulo_web_id
        FROM asigna_modulo_web
        WHERE usuario_id = :idPersona
    SQL;

        $db = new Database();
        $rows = $db->queryAll($query, ['idPersona' => $idPersona]);

        return array_column($rows, 'modulo_web_id');
    }
}
