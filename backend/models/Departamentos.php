<?php

namespace models;

use Core\Model;
use Core\Database;

class Departamentos extends Model
{
    public static function getConsultaSucursalesRegistradas()
    {
        $query = <<<SQL
           SELECT
            NOMBRE,
            RFC,
            RAZON_SOCIAL,
            CASE
                WHEN ESTATUS = 1 THEN 'ALTA'
                WHEN ESTATUS = 0 THEN 'BAJA'
                ELSE 'DESCONOCIDO' -- Por si hay otros valores
            END AS ESTATUS
        FROM EMPRESA
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            //var_dump($r);
            return self::resultado(true, 'Empresas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
}
