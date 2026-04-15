<?php

namespace Models;

use Core\Model;
use Core\Database;

/**
 * Configuración de tipos de estadísticas por puesto (Sparta).
 * Determina qué tableros de estadísticas puede ver cada puesto.
 */
class ConfigEstadisticasPuesto extends Model
{
    /** Tipos de estadísticas (clave => label, icon). */
    const TIPOS = [
        'sabueso' => ['label' => 'Analítica sabueso', 'icon' => 'fa-solid fa-magnifying-glass-chart'],
        // Añadir más cuando existan: 'ventas' => ['label' => 'Estadísticas Ventas', 'icon' => 'fa-solid fa-chart-line'],
    ];

    /**
     * Configuración guardada: [{ id_puesto, tipo_estadistica }, ...].
     */
    public static function getConfig()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT id_puesto, tipo_estadistica
                FROM config_estadisticas_puesto
            ");
            $datos = is_array($r) ? $r : [];
            echo json_encode(['success' => true, 'mensaje' => 'Configuración estadísticas.', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al obtener configuración.', 'datos' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Tipos de estadísticas que puede ver la persona (unión de todos sus puestos).
     */
    public static function getTiposPorPersona($id_persona)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return [];
        }
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT DISTINCT c.tipo_estadistica
                FROM asigna_puesto a
                INNER JOIN config_estadisticas_puesto c ON c.id_puesto = a.id_puesto
                WHERE a.id_persona = :id_persona
            ", ['id_persona' => $id_persona]);
            if (!is_array($r)) {
                return [];
            }
            return array_values(array_unique(array_column($r, 'tipo_estadistica')));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Guardar configuración. $pares = [ ['id_puesto' => x, 'tipo_estadistica' => 'sabueso'], ... ]
     */
    public static function guardar($pares)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_array($pares)) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
            exit;
        }
        // Un puesto solo una vez por tipo de estadística: deduplicar por (id_puesto, tipo_estadistica).
        $vistos = [];
        $pares = array_values(array_filter($pares, function ($p) use (&$vistos) {
            $id = (int) ($p['id_puesto'] ?? 0);
            $t = trim((string) ($p['tipo_estadistica'] ?? ''));
            $key = $id . '-' . $t;
            if (isset($vistos[$key])) {
                return false;
            }
            $vistos[$key] = true;
            return true;
        }));
        $clavesValidas = array_keys(self::TIPOS);
        try {
            $db = new Database();
            $db->beginTransaction();
            $db->queryOne("DELETE FROM config_estadisticas_puesto");
            foreach ($pares as $p) {
                $id_puesto = (int) ($p['id_puesto'] ?? 0);
                $tipo = trim((string) ($p['tipo_estadistica'] ?? ''));
                $tipo = strtolower(preg_replace('/[^a-z0-9_]/', '', $tipo));
                if ($id_puesto > 0 && $tipo !== '' && in_array($tipo, $clavesValidas, true)) {
                    $db->CRUD("
                        INSERT INTO config_estadisticas_puesto (id_puesto, tipo_estadistica)
                        VALUES (:id_puesto, :tipo_estadistica)
                    ", ['id_puesto' => $id_puesto, 'tipo_estadistica' => $tipo]);
                }
            }
            $db->commit();
            echo json_encode(['success' => true, 'mensaje' => 'Configuración de estadísticas guardada correctamente.']);
        } catch (\Exception $e) {
            if (isset($db) && $db) {
                $db->rollback();
            }
            echo json_encode(['success' => false, 'mensaje' => 'Error al guardar.', 'error' => $e->getMessage()]);
        }
        exit;
    }
}
