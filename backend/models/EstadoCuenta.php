<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseAWS;

class EstadoCuenta extends Model
{
    public static function buscarCreditoPorNombre($nombre)
    {
        $qry = "
            SELECT
                id,
                CONCAT_WS(' ', nombres, apellidop, apellidom) AS nombre_completo
            FROM persona
            WHERE CONCAT_WS(' ', nombres, apellidop, apellidom)  LIKE '%$nombre%'
              AND estatus = 'Activo'
            ORDER BY nombres, apellidop
            LIMIT 10
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

    public static function obtenerDocumentoOferta($id, $tipo)
    {
        if (!$id || !is_numeric($id)) {
            return self::resultado(false, 'ID inválido');
        }

        if (!in_array($tipo, ['FAD_DOC', 'EVIDENCIA'])) {
            return self::resultado(false, 'Tipo de documento inválido');
        }

        if ($tipo === 'FAD_DOC') {
            $qry = "
                SELECT nombre_archivo
                FROM oferta_documentos
                WHERE tipo_documento = 'FAD'
                AND fk_oferta = :id
                LIMIT 1
            ";
        } else {
            $qry = "
                SELECT nombre_archivo
                FROM oferta_documentos
                WHERE tipo_documento = 'EVIDENCIA'
                AND fk_oferta = :id
                LIMIT 1
            ";
        }

        $val = [
            'id' => $id
        ];

        try {
            // 🔥 conexión 3
            $db = new DatabaseAWS();
            $r = $db->queryAll($qry, $val);

            if (!$r || count($r) === 0) {
                return self::resultado(false, 'Documento no encontrado');
            }

            return self::resultado(true, 'Documento encontrado', $r[0]);

        } catch (Exception $e) {
            return self::resultado(false, 'Error consultando documento', null, $e->getMessage());
        }
    }

    public static function getClientesEstadoCuentaPorNombre($data)
    {
        if (!$data || !isset($data['nombre'])) {
            return self::resultado(false, 'Nombre no recibido.', []);
        }

        $nombre = trim($data['nombre']);

        $query = <<<SQL
            SELECT
                id,
                CONCAT_WS(' ', nombres, apellidop, apellidom) AS nombre_completo
            FROM persona
            WHERE CONCAT_WS(' ', nombres, apellidop, apellidom) LIKE :nombre
              AND estatus = 'Activo'
            ORDER BY nombres, apellidop
            LIMIT 10
        SQL;

        try {
            $db = new Database();
            $params = [':nombre' => "%{$nombre}%"];
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Personas encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', [], $e->getMessage());
        }
    }

    public static function getPorNombreEstadoCuenta()
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $r = $db->queryAll(
                "SELECT
                        id,
                        CONCAT_WS(' ', nombres, apellidop, apellidom) AS nombre_completo
                    FROM persona
                    WHERE CONCAT_WS(' ', nombres, apellidop, apellidom) LIKE '%jona%'
                      AND estatus = 'Activo'
                    ORDER BY nombres, apellidop
                    LIMIT 10
            ");
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Departamentos encontrados.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit; // <- Muy importante: evita que se imprima algo extra
    }



}
