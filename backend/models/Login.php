<?php

namespace Models;

use Core\Model;
use Core\Database;

class Login extends Model
{
    public static function validaUsuario($datos)
    {
        $query = <<<SQL
             SELECT p.nombres, p.apellidop, p.apellidom , p.numero_empleado, p.user_name, p.password, pp.id as id_puesto, pp.nombre as nombre_puesto
            FROM
                persona p
                inner join asigna_puesto a on a.id_persona = p.id
                inner join puesto pp on pp.id = a.id_puesto
            WHERE
                estatus = 'Activo'
                AND user_name = :usuario
                AND password = :password
        SQL;

        $params = [
            'usuario' => $datos['usuario'],
            'password' => $datos['password']
        ];

        try {
            $db = new Database();
            $r = $db->queryOne($query, $params);
            if ($r === null) return self::resultado(false, 'Credenciales incorrectas.');
            return self::resultado(true, 'Credenciales correctas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
}
