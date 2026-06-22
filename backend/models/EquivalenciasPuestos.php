<?php

namespace Models;

use Core\Model;
use Core\Database;

class EquivalenciasPuestos extends Model
{
    private const DIRECCION_COBRANZA_ID = 12;

    /**
     * Puestos del sistema legacy (columna izquierda).
     */
    public static function getPuestosLegacy()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT id, clave, nombre
                FROM puestos_legacy               
                ORDER BY nombre
            ");
            $datos = is_array($r) ? $r : [];
            echo json_encode(['success' => true, 'mensaje' => 'Puestos legacy.', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al obtener puestos legacy.', 'datos' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Puestos del sistema Sparta (columna derecha) con departamento.
     */
    public static function getPuestosSparta()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = new Database();
            $idDireccionCobranza = self::DIRECCION_COBRANZA_ID;
            $r = $db->queryAll("
                SELECT p.id, p.nombre, p.clave,
                       COALESCE(d.nombre, '') AS departamento_nombre
                FROM puesto p
                INNER JOIN departamento d ON d.id = p.departamento_id
                INNER JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
                INNER JOIN asigna_direcciones ad
                    ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                    AND COALESCE(ad.activo, 1) = 1
                INNER JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                WHERE (p.activo IS NULL OR p.activo = 1)
                    AND COALESCE(d.activo, 1) = 1
                    AND COALESCE(dorg.activo, 1) = 1
                    AND COALESCE(dir.activo, 1) = 1
                    AND dir.id = {$idDireccionCobranza}
                ORDER BY d.nombre, p.nombre
            ");
            $datos = is_array($r) ? $r : [];
            echo json_encode(['success' => true, 'mensaje' => 'Puestos Sparta.', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al obtener puestos Sparta.', 'datos' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Equivalencias guardadas: id_puesto (Sparta), id_puesto_legacy (Legacy).
     */
    public static function getEquivalencias()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = new Database();
            $idDireccionCobranza = self::DIRECCION_COBRANZA_ID;
            $r = $db->queryAll("
                SELECT elp.id_puesto, elp.id_puesto_legacy
                FROM equivalencias_legacy_puestos elp
                INNER JOIN puesto p ON p.id = elp.id_puesto
                INNER JOIN departamento d ON d.id = p.departamento_id
                INNER JOIN asigna_direcciones ad
                    ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                    AND COALESCE(ad.activo, 1) = 1
                INNER JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                WHERE dir.id = {$idDireccionCobranza}
            ");
            $datos = is_array($r) ? $r : [];
            echo json_encode(['success' => true, 'mensaje' => 'Equivalencias.', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al obtener equivalencias.', 'datos' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Guardar equivalencias. $pares = [ ['id_puesto' => x, 'id_puesto_legacy' => y], ... ]
     */
    public static function guardarEquivalencias($pares)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_array($pares)) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
            exit;
        }
        try {
            $db = new Database();
            $idDireccionCobranza = self::DIRECCION_COBRANZA_ID;
            $db->beginTransaction();
            $db->queryOne("
                DELETE elp
                FROM equivalencias_legacy_puestos elp
                INNER JOIN puesto p ON p.id = elp.id_puesto
                INNER JOIN departamento d ON d.id = p.departamento_id
                INNER JOIN asigna_direcciones ad
                    ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                    AND COALESCE(ad.activo, 1) = 1
                INNER JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                WHERE dir.id = {$idDireccionCobranza}
            ");
            foreach ($pares as $p) {
                $id_puesto = (int) ($p['id_puesto'] ?? 0);
                $id_puesto_legacy = (int) ($p['id_puesto_legacy'] ?? 0);
                if ($id_puesto > 0 && $id_puesto_legacy > 0) {
                    $db->queryOne("
                        INSERT INTO equivalencias_legacy_puestos (id_puesto, id_puesto_legacy)
                        SELECT p.id, $id_puesto_legacy
                        FROM puesto p
                        INNER JOIN departamento d ON d.id = p.departamento_id
                        INNER JOIN asigna_direcciones ad
                            ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                            AND COALESCE(ad.activo, 1) = 1
                        INNER JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                        WHERE p.id = $id_puesto
                            AND dir.id = {$idDireccionCobranza}
                        LIMIT 1
                    ");
                }
            }
            $db->commit();
            echo json_encode(['success' => true, 'mensaje' => 'Equivalencias guardadas correctamente.']);
        } catch (\Exception $e) {
            if (isset($db) && $db) $db->rollback();
            echo json_encode(['success' => false, 'mensaje' => 'Error al guardar.', 'error' => $e->getMessage()]);
        }
        exit;
    }
}
