<?php

namespace Models;

use Core\Model;
use Core\Database;

class EstadoCuenta extends Model
{
    public static function buscarCreditoPorNombre($nombre)
    {
        $qry = "
            SELECT 
                id_credito,
                id_cliente,
                Nombre_cliente,
                Fecha_inicio
            FROM lista_cliente
            WHERE Nombre_cliente LIKE :nombre
            LIMIT 1000
        ";

        $val = [
            'nombre' => '%' . $nombre . '%'
        ];

        try {
            $db = new Database();
            $r = $db->queryAll($qry, $val);  // igual que fetchAll
            return self::resultado(true, 'Créditos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error buscando créditos.', null, $e->getMessage());
        }
    }

    // Igual que usan todos tus modelos
    private static function resultado($success, $mensaje, $datos = null, $error = null)
    {
        return [
            'success' => $success,
            'mensaje' => $mensaje,
            'datos'   => $datos,
            'error'   => $error
        ];
    }
}
