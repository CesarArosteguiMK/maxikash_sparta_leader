<?php

namespace Models;

use Core\Model;
use Core\Database;

class Login extends Model
{
    public static function validaUsuario($datos)
    {
        $query = <<<SQL
        SELECT 
            p.id, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom,
            p.numero_empleado, p.user_name, p.password,
            pp.id AS id_puesto,
            pp.nombre AS nombre_puesto,
            pp.departamento_id,
            p.session_version,
            p.force_logout,
            pp.id as id_puesto
        FROM persona p
        LEFT JOIN asigna_puesto a ON a.id_persona = p.id
        LEFT JOIN puesto pp ON pp.id = a.id_puesto
        WHERE p.estatus = 'Activo'
          AND p.user_name = :usuario
          AND p.password = :password
        LIMIT 1
    SQL;

        $params = [
            'usuario' => $datos['usuario'],
            'password' => $datos['password']
        ];

        try {
            $db = new Database();
            $r = $db->queryOne($query, $params);

            // ❌ Credenciales inválidas
            if (!$r) {
                return self::resultado(false, 'Credenciales incorrectas.');
            }

            // 🔐 Si estaba forzado, limpiamos el logout
            if ((int)$r['force_logout'] === 1) {
                $db->query(
                    "UPDATE persona SET force_logout = 0 WHERE id = :id",
                    ['id' => $r['id']]
                );
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
