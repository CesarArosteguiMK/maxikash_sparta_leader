<?php

namespace Models;

use Core\Model;
use Core\Database;

class Empresa extends Model
{
    public static function getConsultaDepartamentos()
    {
        $query = <<<SQL
           SELECT
            *
        FROM departamento
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaPuestos($departamento)
    {

        // Query base
        $query = <<<SQL
        SELECT
            p.id, p.nombre, p.nivel, d.nombre as departamento
        FROM puesto p
        INNER JOIN departamento d ON d.id = p.departamento_id
    SQL;

        $params = [];

        // Agregar filtro si se envió un departamento
        if ($departamento != null) {
            $query .= " WHERE d.id = :departamento";
            $params['departamento'] = $departamento;
        }

        try {
            $db = new Database();
            // Pasar parámetros si existen
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Puestos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }



}
