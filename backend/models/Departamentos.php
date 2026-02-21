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
        header('Content-Type: application/json; charset=utf-8');

        $id_dep = $id_departamento !== null && $id_departamento !== '' ? (int) $id_departamento : 0;
        if ($id_dep <= 0) {
            echo json_encode([
                "success" => true,
                "mensaje" => "Puestos encontrados.",
                "datos" => []
            ]);
            exit;
        }

        try {
            $db = new Database();
            $r = $db->queryAll(
                "
                SELECT 
                    p.id AS id_puesto, p.nombre AS puesto_nombre, '' AS descripcion, p.departamento_id AS id_departamento
                FROM __SPARTA_SECRET_REDACTED__.puesto p
                WHERE p.departamento_id = :id_departamento
                ORDER BY p.nivel DESC, p.id ASC
            ",
                ['id_departamento' => $id_dep]
            );
            $datos = is_array($r) ? $r : [];

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

        exit;
    }

    public static function UpdateNombrePuesto($id_puesto, $nombre)
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.puesto SET nombre = :nombre, clave = :clave WHERE id = :id_puesto",
                ['nombre' => $nombre, 'clave' => $nombre, 'id_puesto' => $id_puesto]
            );
            $r = true;
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

    /**
     * Actualiza el orden de los puestos reasignando la columna nivel.
     * Primer puesto de la lista = mayor nivel (dept*1000+999), último = dept*1000+1.
     * @param int $id_departamento
     * @param array $ordenes Array de id_puesto en el orden deseado (índice 0 = primero)
     */
    public static function UpdateOrdenPuestos($id_departamento, $ordenes)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_departamento = (int) $id_departamento;
            if (!$id_departamento || !is_array($ordenes)) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Datos inválidos.',
                    'datos' => []
                ]);
                exit;
            }

            $db = new Database();
            $base = $id_departamento * 1000; // Ej: 11 -> 11000, rango 11001..11999
            foreach ($ordenes as $pos => $id_puesto) {
                $id_puesto = (int) $id_puesto;
                if ($id_puesto <= 0) continue;
                $nivel = $base + (999 - (int) $pos);
                $db->CRUD(
                    "UPDATE __SPARTA_SECRET_REDACTED__.puesto SET nivel = :nivel WHERE id = :id_puesto AND departamento_id = :id_departamento",
                    ['nivel' => $nivel, 'id_puesto' => $id_puesto, 'id_departamento' => $id_departamento]
                );
            }

            echo json_encode([
                'success' => true,
                'mensaje' => 'Orden actualizado.',
                'datos' => []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al actualizar orden: ' . $e->getMessage(),
                'datos' => []
            ]);
        }
        exit;
    }

    public static function InsertPuestos($nombre, $id_departamento)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_departamento = (int) $id_departamento;
            $db = new Database();

            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.puesto (id, clave, nombre, nivel, activo, departamento_id, es_jefe, descripcion) VALUES (null, :nombre, :nombre2, 0, 1, :id_departamento, 1, NULL)",
                ['nombre' => $nombre, 'nombre2' => $nombre, 'id_departamento' => $id_departamento]
            );

            $newId = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id_puesto = (int) ($newId['id'] ?? 0);
            if ($id_puesto <= 0) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'No se obtuvo el ID del puesto insertado.',
                    'datos' => []
                ]);
                exit;
            }

            // Rebalancear niveles: todos los puestos del departamento en rango dept*1000+1 .. dept*1000+999
            // Orden actual: nivel DESC (el nuevo tiene 0, queda último). Asignar 11999, 11998, ... 11001
            $rows = $db->queryAll(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.puesto WHERE departamento_id = :id_departamento ORDER BY nivel DESC, id ASC",
                ['id_departamento' => $id_departamento]
            );
            $ordenes = is_array($rows) ? array_column($rows, 'id') : [];
            $base = $id_departamento * 1000;
            foreach ($ordenes as $pos => $id) {
                $nivel = $base + (999 - (int) $pos);
                $id = (int) $id;
                $db->CRUD(
                    "UPDATE __SPARTA_SECRET_REDACTED__.puesto SET nivel = :nivel WHERE id = :id AND departamento_id = :id_departamento",
                    ['nivel' => $nivel, 'id' => $id, 'id_departamento' => $id_departamento]
                );
            }

            echo json_encode([
                'success' => true,
                'mensaje' => 'Puesto insertado.',
                'datos' => ['id_puesto' => $id_puesto],
                'id_puesto' => $id_puesto
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

    public static function InsertDepartamento($nombre)
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.departamento (id, nombre, activo, img_url) VALUES (null, :nombre, 1, NULL)",
                ['nombre' => $nombre]
            );
            $r = true;
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Departamento insertado correctamente.",
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

    public static function UpdateNombreDepartamento($id_departamento, $nombre)
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $id_departamento = (int) $id_departamento;
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.departamento SET nombre = :nombre WHERE id = :id_departamento",
                ['nombre' => $nombre, 'id_departamento' => $id_departamento]
            );
            $r = true;
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Departamento actualizado.",
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

    /**
     * Elimina un departamento y sus puestos. Solo permite si no tiene personal asignado (total_personas = 0).
     */
    public static function eliminarDepartamento($id_departamento)
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int) $id_departamento;
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de departamento inválido.', 'datos' => []]);
            exit;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT COUNT(DISTINCT a.id_persona) AS total_personas
                 FROM departamento d
                 LEFT JOIN puesto p ON p.departamento_id = d.id
                 LEFT JOIN asigna_puesto a ON a.id_puesto = p.id
                 WHERE d.id = :id",
                ['id' => $id]
            );
            $totalPersonas = (int) ($row['total_personas'] ?? 0);
            if ($totalPersonas > 0) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'No se puede eliminar: el departamento tiene personal asignado. Reasigne o dé de baja al personal primero.',
                    'datos' => []
                ]);
                exit;
            }
            // Quitar asignaciones de puestos de este departamento (por si acaso)
            $db->queryOne("DELETE a FROM __SPARTA_SECRET_REDACTED__.asigna_puesto a INNER JOIN __SPARTA_SECRET_REDACTED__.puesto p ON p.id = a.id_puesto WHERE p.departamento_id = $id");
            // Borrar puestos del departamento
            $db->queryOne("DELETE FROM __SPARTA_SECRET_REDACTED__.puesto WHERE departamento_id = $id");
            // Borrar departamento
            $db->queryOne("DELETE FROM __SPARTA_SECRET_REDACTED__.departamento WHERE id = $id");
            echo json_encode([
                'success' => true,
                'mensaje' => 'Departamento eliminado correctamente.',
                'datos' => []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al eliminar el departamento.',
                'datos' => [],
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

}
