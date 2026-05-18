<?php

namespace Models;

use Core\Model;
use Core\Database;

/**
 * Configuración de funciones de ticket por puesto (Sparta).
 * Determina qué categorías ve el usuario al levantar ticket según su puesto.
 */
class ConfigTicketPuesto extends Model
{
    /** Claves de función de ticket (mismo valor que data-categoria en el modal). */
    const FUNCIONES = [
        'sabueso' => ['label' => 'Sabueso', 'icon' => 'fa-solid fa-dog'],
        'viaticos' => ['label' => 'Viáticos', 'icon' => 'fa-solid fa-money-bill-1'],
        'aplicaciones_de_pago' => ['label' => 'Reclamos de bonos', 'icon' => 'fa-solid fa-gift'],
        'validaciones' => ['label' => 'Validaciones', 'icon' => 'fa-solid fa-square-check'],
        'plantilla' => ['label' => 'Plantilla', 'icon' => 'fa-solid fa-file-lines'],
        'atencion_cliente' => ['label' => 'Atención al cliente', 'icon' => 'fa-regular fa-message'],
        'credito_problematico' => ['label' => 'Pagos no identificados', 'icon' => 'fa-solid fa-magnifying-glass-dollar'],
        'solicitud_baja' => ['label' => 'Solicitud de baja', 'icon' => 'fa-solid fa-user-xmark'],
        'aclaracion_credito' => ['label' => 'Incidencias en asignacion de cartera', 'icon' => 'fa-solid fa-briefcase'],
    ];

    /**
     * Puestos Sparta (todos los activos, sin filtro por departamento).
     * A diferencia de Equivalencia de puestos, aquí se listan todos los puestos.
     */
    public static function getPuestosSparta()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT p.id, p.nombre, p.clave,
                       COALESCE(d.nombre, '') AS departamento_nombre
                FROM puesto p
                LEFT JOIN departamento d ON d.id = p.departamento_id
                WHERE (p.activo IS NULL OR p.activo = 1)
                ORDER BY d.nombre, p.nombre
            ");
            $datos = is_array($r) ? $r : [];
            echo json_encode(['success' => true, 'mensaje' => 'Puestos Sparta.', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al obtener puestos.', 'datos' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Configuración guardada: [{ id_puesto, funcion_ticket }, ...].
     */
    public static function getConfig()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT id_puesto, funcion_ticket
                FROM config_ticket_puesto
            ");
            $datos = is_array($r) ? $r : [];
            echo json_encode(['success' => true, 'mensaje' => 'Configuración.', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al obtener configuración.', 'datos' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Devuelve array de funcion_ticket asignados a un puesto (para uso interno, no envía JSON).
     */
    public static function getFuncionesPorPuesto($id_puesto)
    {
        $id_puesto = (int) $id_puesto;
        if ($id_puesto <= 0) {
            return [];
        }
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT funcion_ticket
                FROM config_ticket_puesto
                WHERE id_puesto = :id_puesto
            ", ['id_puesto' => $id_puesto]);
            if (!is_array($r)) {
                return [];
            }
            return array_values(array_unique(array_column($r, 'funcion_ticket')));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Devuelve todas las funciones de ticket asignadas a CUALQUIER puesto de la persona.
     * Si la persona tiene varios puestos (asigna_puesto), se unen las funciones de todos.
     */
    public static function getFuncionesPorPersona($id_persona)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return [];
        }
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT DISTINCT c.funcion_ticket
                FROM asigna_puesto a
                INNER JOIN config_ticket_puesto c ON c.id_puesto = a.id_puesto
                WHERE a.id_persona = :id_persona
            ", ['id_persona' => $id_persona]);
            if (!is_array($r)) {
                return [];
            }
            return array_values(array_unique(array_column($r, 'funcion_ticket')));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Guardar configuración. $pares = [ ['id_puesto' => x, 'funcion_ticket' => 'sabueso'], ... ]
     */
    public static function guardar($pares)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_array($pares)) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
            exit;
        }
        // Un puesto solo puede estar una vez por módulo de ticket: deduplicar por (id_puesto, funcion_ticket).
        $vistos = [];
        $pares = array_values(array_filter($pares, function ($p) use (&$vistos) {
            $id = (int) ($p['id_puesto'] ?? 0);
            $f = trim((string) ($p['funcion_ticket'] ?? ''));
            $key = $id . '-' . $f;
            if (isset($vistos[$key])) {
                return false;
            }
            $vistos[$key] = true;
            return true;
        }));
        $clavesValidas = array_keys(self::FUNCIONES);
        try {
            $db = new Database();
            $db->beginTransaction();
            $db->queryOne("DELETE FROM config_ticket_puesto");
            foreach ($pares as $p) {
                $id_puesto = (int) ($p['id_puesto'] ?? 0);
                $funcion = trim((string) ($p['funcion_ticket'] ?? ''));
                $funcion = strtolower(preg_replace('/[^a-z0-9_]/', '', $funcion));
                if ($id_puesto > 0 && $funcion !== '' && in_array($funcion, $clavesValidas, true)) {
                    $db->CRUD("
                        INSERT INTO config_ticket_puesto (id_puesto, funcion_ticket)
                        VALUES (:id_puesto, :funcion_ticket)
                    ", ['id_puesto' => $id_puesto, 'funcion_ticket' => $funcion]);
                }
            }
            $db->commit();
            echo json_encode(['success' => true, 'mensaje' => 'Configuración guardada correctamente.']);
        } catch (\Exception $e) {
            if (isset($db) && $db) {
                $db->rollback();
            }
            echo json_encode(['success' => false, 'mensaje' => 'Error al guardar.', 'error' => $e->getMessage()]);
        }
        exit;
    }
}
