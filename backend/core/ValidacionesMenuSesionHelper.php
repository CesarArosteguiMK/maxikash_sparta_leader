<?php

namespace Core;

use Core\Database;

/**
 * Menú y accesos de Validaciones para gestores/capos sin módulos 18/27 en sesión.
 */
class ValidacionesMenuSesionHelper
{
    public static function sesionTieneModuloSabuesoValidaciones(): bool
    {
        $mods = $_SESSION['modulos'] ?? [];
        if (!is_array($mods)) {
            return false;
        }

        return in_array(18, $mods, true) || in_array(27, $mods, true);
    }

    public static function tieneAsignacionActivaTicketValidaciones(int $personaId): bool
    {
        if ($personaId < 1) {
            return false;
        }
        try {
            $db = new Database();
            $asigRow = $db->queryOne(
                "SELECT COUNT(1) AS total
                 FROM asignacion_ticket at
                 INNER JOIN ticket t ON t.id_ticket = at.id_ticket
                 WHERE at.id_persona_asignada = :id_persona
                   AND (at.activo = 1 OR at.fecha_liberacion IS NULL)
                   AND (t.activo = 1 OR t.activo IS NULL)
                   AND LOWER(COALESCE(NULLIF(TRIM(t.categoria_gestion),''), 'sabueso')) = 'validaciones'",
                ['id_persona' => $personaId]
            );

            return (int) ($asigRow['total'] ?? 0) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Mismo criterio que Validaciones::getCapoInfoForTerritorial (jefe con segmento 1_7 / 8_21 en nombre de puesto).
     */
    public static function esCapoTerritorialValidaciones(int $personaId): bool
    {
        if ($personaId < 1) {
            return false;
        }
        try {
            $db = new Database();
            $rowCapo = $db->queryOne(
                "SELECT pp.nombre AS puesto_nombre, pp.departamento_id AS departamento_id
                 FROM asigna_puesto ap
                 INNER JOIN puesto pp ON pp.id = ap.id_puesto
                 WHERE ap.id_persona = :id_persona
                   AND pp.es_jefe = 1
                   AND (ap.activo = 1 OR ap.activo IS NULL)
                 ORDER BY pp.departamento_id ASC, pp.nivel ASC
                 LIMIT 1",
                ['id_persona' => $personaId]
            );
            $puestoNombreMenu = $rowCapo && isset($rowCapo['puesto_nombre'])
                ? strtolower(trim((string) $rowCapo['puesto_nombre']))
                : '';
            $campoTerritorialMenu = '';
            if ($puestoNombreMenu !== '') {
                if (strpos($puestoNombreMenu, '1_7') !== false) {
                    $campoTerritorialMenu = '1_7';
                } elseif (strpos($puestoNombreMenu, '8_21') !== false) {
                    $campoTerritorialMenu = '8_21';
                }
                if ($campoTerritorialMenu === '') {
                    if (preg_match('/1\s*[-_ ]?\s*7/', $puestoNombreMenu)) {
                        $campoTerritorialMenu = '1_7';
                    } elseif (preg_match('/8\s*[-_ ]?\s*21/', $puestoNombreMenu)) {
                        $campoTerritorialMenu = '8_21';
                    }
                }
            }
            $capoDepartamentoId = $rowCapo && isset($rowCapo['departamento_id']) ? (int) $rowCapo['departamento_id'] : 0;

            return $capoDepartamentoId > 0 && $campoTerritorialMenu !== '';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * URL de entrada (gestor o territorial) o null si no aplica ítem automático.
     *
     * @param list<string> $panelesUsuario claves de paneles (p. ej. ConfigPanelUsuario::getPanelesPorPersona)
     */
    public static function resolverUrlMenuValidaciones(int $personaId, array $panelesUsuario): ?string
    {
        if ($personaId < 1) {
            return null;
        }
        if (in_array('sabueso_panel_validaciones', $panelesUsuario, true)) {
            return null;
        }
        if (self::esCapoTerritorialValidaciones($personaId)) {
            return '/validaciones/territorial';
        }
        if (self::tieneAsignacionActivaTicketValidaciones($personaId)) {
            return '/validaciones/gestor';
        }

        return null;
    }

    public static function puedeAccederVistaGestorValidaciones(int $personaId): bool
    {
        if (self::sesionTieneModuloSabuesoValidaciones()) {
            return true;
        }

        return self::tieneAsignacionActivaTicketValidaciones($personaId);
    }

    public static function puedeAccederVistaTerritorialValidaciones(int $personaId): bool
    {
        if (self::sesionTieneModuloSabuesoValidaciones()) {
            return true;
        }

        return self::esCapoTerritorialValidaciones($personaId);
    }

    /**
     * Lectura de formularios/preguntas (API) para panel gestor o usuarios con módulo.
     */
    public static function puedeApiLecturaFormulariosValidaciones(int $personaId, callable $tienePanelAdminValidaciones): bool
    {
        if (self::sesionTieneModuloSabuesoValidaciones()) {
            return true;
        }
        if ($personaId > 0 && $tienePanelAdminValidaciones($personaId)) {
            return true;
        }

        return self::tieneAsignacionActivaTicketValidaciones($personaId);
    }
}
