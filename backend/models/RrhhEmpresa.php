<?php

namespace Models;

use Core\Model;
use Core\Database;

class RrhhEmpresa extends Model
{
    public static function getEmpresasActivas()
    {
        try {
            $db = new Database();
            $datos = $db->queryAll(
                "SELECT
                    e.id,
                    e.id_grupo AS id_grupo_corporativo,
                    e.clave,
                    e.nombre_comercial,
                    e.razon_social,
                    e.registro_patronal,
                    e.activo,
                    g.clave AS grupo_clave,
                    g.nombre AS grupo_nombre
                 FROM estado_cuenta.rrhh_empresas e
                 INNER JOIN estado_cuenta.rrhh_grupos_corporativos g
                    ON g.id = e.id_grupo
                 WHERE COALESCE(e.activo, 1) = 1
                   AND COALESCE(g.activo, 1) = 1
                 ORDER BY CASE WHEN e.id = 1 THEN 0 ELSE 1 END, e.nombre_comercial"
            );

            return self::resultado(true, 'Empresas RR.HH. encontradas.', is_array($datos) ? $datos : []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener empresas RR.HH.', [], $e->getMessage());
        }
    }
}
