<?php

namespace models;

use Core\Model;
use Core\Database;

class Departamentos extends Model
{
    public static function getConsultaDepartamentos()
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $r = $db->queryAll(
                "
                SELECT 
                    d.id AS departamento_id,
                    d.nombre AS departamento_nombre,
                    COUNT(DISTINCT p.id) AS total_puestos,
                    COUNT(DISTINCT a.id_persona) AS total_personas,
                    d.activo, d.img_url
                FROM departamento d
                LEFT JOIN puesto p ON p.departamento_id = d.id
                LEFT JOIN asigna_puesto a ON a.id_puesto = p.id
                GROUP BY d.id, d.nombre
                ORDER BY d.nombre;
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

    public static function getConsultaPuestos($id_departamento)
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $r = $db->queryAll(
                "
                SELECT 
                    id as id_puesto, nombre as puesto_nombre, '' as descripcion
                FROM puesto
                WHERE departamento_id = $id_departamento
            ");
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Puestos encontrados.",
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

    public static function UpdateNombrePuesto($id_puesto, $nombre)
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $r = $db->queryOne(
                "
                UPDATE __SPARTA_SECRET_REDACTED__.puesto
                SET nombre='$nombre' WHERE id=$id_puesto
            ");
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Puesto actualizado.",
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

    public static function InsertPuestos($nombre)
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $r = $db->queryOne(
                "
                INSERT INTO __SPARTA_SECRET_REDACTED__.puesto
                    (id, clave, nombre, nivel, activo, departamento_id, es_jefe, descripcion)
                    VALUES(null, '$nombre', '$nombre', 100, 1, 2, 1, NULL);
                                ");
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Puesto insertado.",
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
