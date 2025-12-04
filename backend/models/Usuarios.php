<?php

namespace models;

use Core\Model;
use Core\Database;

class Usuarios extends Model
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

    public static function getUsuarioPorNombre($usuario)
    {
        $query = <<<SQL
        SELECT ID, USUARIO, PASS
        FROM USUARIO
        WHERE USUARIO = :usuario 
SQL;

        $params = [
            'usuario' => $usuario
        ];

        try {
            $db = new Database();
            $resultado = $db->queryOne($query, $params);
            return $resultado ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function actualizarPassword($usuario, $password)
    {
        $hash = strtoupper(hash('sha256', $password));

        $qry = <<<SQL
            UPDATE USUARIO SET
                PASS = :pass
            WHERE USUARIO = :usuario
SQL;

        $params = [
            'usuario' => $usuario,
            'pass' => $hash
        ];

        try {
            $db = new Database();
            $db->beginTransaction();
            $db->CRUD($qry, $params);
            $db->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            $db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
