<?php

namespace Models;

use Core\Model;
use Core\Database;

/**
 * Configuración de paneles admin por usuario (persona).
 * Determina qué usuarios pueden acceder a cada panel admin.
 */
class ConfigPanelUsuario extends Model
{
    /** Paneles admin disponibles (clave => label, icon, url). */
    const PANELES = [
        'sabueso_paneladmin' => [
            'label' => 'Panel Sabueso',
            'icon' => 'fa-solid fa-dog',
            'url' => '/sabueso/paneladmin',
        ],
        'sabueso_panel_validaciones' => [
            'label' => 'Validaciones',
            'icon' => 'fa-solid fa-clipboard-check',
            'url' => '/validaciones/paneladmin',
        ],
        'sabueso_panel_plantilla' => [
            'label' => 'Plantilla',
            'icon' => 'fa-solid fa-file-lines',
            'url' => '/plantilla/paneladmin',
        ],
        'sabueso_panel_atencioncliente' => [
            'label' => 'Atención al cliente',
            'icon' => 'fa-solid fa-headset',
            'url' => '/atencioncliente/paneladmin',
        ],
        'sabueso_panel_viaticos' => [
            'label' => 'Viáticos',
            'icon' => 'fa-solid fa-receipt',
            'url' => '/viaticos/paneladmin',
        ],
        'sabueso_panel_aplicacionespago' => [
            'label' => 'Aplicaciones de pago',
            'icon' => 'fa-solid fa-credit-card',
            'url' => '/aplicacionespago/paneladmin',
        ],
        'sabueso_panel_creditoproblematico' => [
            'label' => 'Crédito problemático',
            'icon' => 'fa-solid fa-triangle-exclamation',
            'url' => '/creditoproblematico/paneladmin',
        ],
        'sabueso_panel_aclaracioncredito' => [
            'label' => 'Aclaración de crédito',
            'icon' => 'fa-solid fa-circle-question',
            'url' => '/aclaracioncredito/paneladmin',
        ],
        'sabueso_panelsolicitudbaja' => [
            'label' => 'Panel Solicitud de baja',
            'icon' => 'fa-solid fa-user-xmark',
            'url' => '/sabueso/panelSolicitudBaja',
        ],
    ];

    /** Módulos que dan acceso a cada panel (clave => [id_modulo, ...]). */
    const PANELES_MODULOS = [
        'sabueso_paneladmin' => [19],
        'sabueso_panel_validaciones' => [19],
        'sabueso_panel_plantilla' => [19],
        'sabueso_panel_atencioncliente' => [19],
        'sabueso_panel_viaticos' => [19],
        'sabueso_panel_aplicacionespago' => [19],
        'sabueso_panel_creditoproblematico' => [19],
        'sabueso_panel_aclaracioncredito' => [19],
        'sabueso_panelsolicitudbaja' => [25],
    ];

    /**
     * Paneles que la persona puede ver: solo por filas en config_panel_usuario (pestaña Panel por usuario).
     * Cada panel es independiente: asignar solo «Panel Sabueso» NO otorga Validaciones, Viáticos, etc.;
     * hay que marcar cada módulo que deba ver.
     * Devuelve [ clave => ['label','icon','url'], ... ]
     */
    public static function getPanelesVisiblesParaPersona($id_persona, array $modulos = [])
    {
        $id_persona = (int) $id_persona;
        $panelesUsuario = [];
        if ($id_persona > 0) {
            $panelesUsuario = self::getPanelesPorPersona($id_persona);
        }
        $out = [];
        foreach (self::PANELES as $clave => $info) {
            if (in_array($clave, $panelesUsuario, true)) {
                $out[$clave] = $info;
            }
        }
        return $out;
    }

    /**
     * Lista de usuarios (personas activas con user_name) para asignar a paneles.
     */
    public static function getUsuarios()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT p.id,
                       TRIM(CONCAT(IFNULL(p.nombres,''), ' ', IFNULL(p.segundo_nombre,''), ' ', IFNULL(p.apellidop,''), ' ', IFNULL(p.apellidom,''))) AS nombre,
                       p.user_name
                FROM persona p
                WHERE (p.estatus = 'Activo' OR p.estatus IS NULL)
                  AND p.user_name IS NOT NULL AND TRIM(p.user_name) != ''
                ORDER BY nombre, p.user_name
            ");
            $datos = is_array($r) ? $r : [];
            echo json_encode(['success' => true, 'mensaje' => 'Usuarios.', 'datos' => $datos]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'mensaje' => 'Error al obtener usuarios.', 'datos' => [], 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Configuración guardada: [{ clave_panel, id_persona }, ...].
     * Si la tabla no existe, devuelve éxito con datos vacíos para que el panel cargue igual.
     */
    public static function getConfig()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT clave_panel, id_persona
                FROM config_panel_usuario
            ");
            $datos = is_array($r) ? $r : [];
            echo json_encode(['success' => true, 'mensaje' => 'Configuración paneles.', 'datos' => $datos]);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $tablaNoExiste = (strpos($msg, '1146') !== false || strpos($msg, '42S02') !== false || strpos($msg, "doesn't exist") !== false);
            if ($tablaNoExiste) {
                echo json_encode(['success' => true, 'mensaje' => 'Tabla no creada aún.', 'datos' => []]);
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'Error al obtener configuración.', 'datos' => [], 'error' => $msg]);
            }
        }
        exit;
    }

    /**
     * Guardar configuración. $pares = [ ['clave_panel' => 'sabueso_paneladmin', 'id_persona' => 1], ... ]
     */
    public static function guardar($pares)
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!is_array($pares)) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos.']);
            exit;
        }
        // Un usuario solo una vez por panel: deduplicar por (clave_panel, id_persona).
        $vistos = [];
        $pares = array_values(array_filter($pares, function ($p) use (&$vistos) {
            $c = trim((string) ($p['clave_panel'] ?? ''));
            $id = (int) ($p['id_persona'] ?? 0);
            $key = $c . '-' . $id;
            if (isset($vistos[$key])) {
                return false;
            }
            $vistos[$key] = true;
            return true;
        }));
        $clavesValidas = array_keys(self::PANELES);
        try {
            $db = new Database();
            $db->beginTransaction();
            $db->queryOne("DELETE FROM config_panel_usuario");
            foreach ($pares as $p) {
                $clave = trim((string) ($p['clave_panel'] ?? ''));
                $clave = strtolower(preg_replace('/[^a-z0-9_]/', '', $clave));
                $id_persona = (int) ($p['id_persona'] ?? 0);
                if ($clave !== '' && $id_persona > 0 && in_array($clave, $clavesValidas, true)) {
                    $db->CRUD("
                        INSERT INTO config_panel_usuario (clave_panel, id_persona)
                        VALUES (:clave_panel, :id_persona)
                    ", ['clave_panel' => $clave, 'id_persona' => $id_persona]);
                }
            }
            $db->commit();
            echo json_encode(['success' => true, 'mensaje' => 'Configuración de paneles guardada correctamente.']);
        } catch (\Exception $e) {
            if (isset($db) && $db) {
                $db->rollback();
            }
            echo json_encode(['success' => false, 'mensaje' => 'Error al guardar.', 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Paneles a los que tiene acceso la persona (para autorización).
     */
    public static function getPanelesPorPersona($id_persona)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return [];
        }
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT clave_panel
                FROM config_panel_usuario
                WHERE id_persona = :id_persona
            ", ['id_persona' => $id_persona]);
            if (!is_array($r)) {
                return [];
            }
            return array_values(array_unique(array_column($r, 'clave_panel')));
        } catch (\Exception $e) {
            return [];
        }
    }
}
