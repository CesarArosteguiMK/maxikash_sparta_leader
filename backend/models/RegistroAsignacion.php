<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Historial de asignaciones por crédito (registro_asignacion).
 * Fechas en zona horaria CDMX; fecha_eliminacion NULL = asignación activa.
 */
class RegistroAsignacion extends Model
{
    private static function nowCdmx(): string
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        return (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
    }

    /**
     * Al asignar una persona: cierra la asignación activa (si existe) e inserta la nueva.
     * Regla: nunca más de un registro activo por crédito.
     */
    public static function registrarAsignacion(int $idCredito, string $personaAsignada): array
    {
        if ($idCredito < 1 || trim($personaAsignada) === '') {
            return self::resultado(false, 'id_credito y persona_asignada requeridos.');
        }
        try {
            $db = new Database();
            $now = self::nowCdmx();
            $persona = trim(mb_substr($personaAsignada, 0, 150));
            $db->CRUD(
                "UPDATE registro_asignacion SET fecha_eliminacion = :ahora WHERE id_credito = :id_credito AND fecha_eliminacion IS NULL",
                ['ahora' => $now, 'id_credito' => $idCredito]
            );
            $db->CRUD(
                "INSERT INTO registro_asignacion (id_credito, persona_asignada, fecha_asignacion, fecha_eliminacion) VALUES (:id_credito, :persona, :fecha_asignacion, NULL)",
                ['id_credito' => $idCredito, 'persona' => $persona, 'fecha_asignacion' => $now]
            );
            return self::resultado(true, 'Asignación registrada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar asignación.', null, $e->getMessage());
        }
    }

    /**
     * Al quitar la asignación: marca fecha_eliminacion en el registro activo.
     */
    public static function cerrarAsignacionActiva(int $idCredito): array
    {
        if ($idCredito < 1) {
            return self::resultado(false, 'id_credito requerido.');
        }
        try {
            $db = new Database();
            $now = self::nowCdmx();
            $db->CRUD(
                "UPDATE registro_asignacion SET fecha_eliminacion = :ahora WHERE id_credito = :id_credito AND fecha_eliminacion IS NULL",
                ['ahora' => $now, 'id_credito' => $idCredito]
            );
            return self::resultado(true, 'Asignación cerrada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cerrar asignación.', null, $e->getMessage());
        }
    }

    /**
     * Historial por crédito: asignado_actual, estado, historial (persona, desde, hasta, duracion_humana).
     * estado: primera_asignacion (no hay registros), con_historial (hay activo), sin_asignar (hay historial pero no activo).
     */
    public static function getHistorialPorCredito(int $idCredito): array
    {
        if ($idCredito < 1) {
            return [
                'asignado_actual' => null,
                'estado' => 'primera_asignacion',
                'historial' => [],
            ];
        }
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT id, id_credito, persona_asignada, fecha_asignacion, fecha_eliminacion " .
                "FROM registro_asignacion WHERE id_credito = :id ORDER BY fecha_asignacion DESC",
                ['id' => $idCredito]
            );
            $rows = is_array($rows) ? $rows : [];
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->getTimestamp();

            $asignadoActual = null;
            $historial = [];
            foreach ($rows as $r) {
                $desdeTs = strtotime($r['fecha_asignacion']);
                $hastaRaw = $r['fecha_eliminacion'];
                $hastaTs = $hastaRaw ? strtotime($hastaRaw) : $now;
                if ($hastaRaw === null) {
                    $asignadoActual = trim($r['persona_asignada']);
                }
                $desdeFmt = $desdeTs ? date('Y-m-d H:i', $desdeTs) : '—';
                $hastaFmt = $hastaRaw ? date('Y-m-d H:i', $hastaTs) : ($now ? date('Y-m-d H:i', $now) : '—');
                $duracionHumana = self::duracionHumana($desdeTs, $hastaTs);
                $historial[] = [
                    'persona' => trim($r['persona_asignada']),
                    'desde' => $desdeFmt,
                    'hasta' => $hastaFmt,
                    'duracion_humana' => $duracionHumana,
                ];
            }

            if (count($rows) === 0) {
                $estado = 'primera_asignacion';
            } elseif ($asignadoActual !== null) {
                $estado = 'con_historial';
            } else {
                $estado = 'sin_asignar';
            }

            return [
                'asignado_actual' => $asignadoActual,
                'estado' => $estado,
                'historial' => $historial,
            ];
        } catch (\Exception $e) {
            return [
                'asignado_actual' => null,
                'estado' => 'primera_asignacion',
                'historial' => [],
            ];
        }
    }

    /**
     * Formato humano: minutos (< 1h), horas (< 24h), días (< 7d), semanas (< 30d), meses (>= 30d).
     */
    public static function duracionHumana(?int $desdeTs, ?int $hastaTs): string
    {
        if ($desdeTs === null || $hastaTs === null || $hastaTs < $desdeTs) {
            return '—';
        }
        $segundos = $hastaTs - $desdeTs;
        $minutos = (int) floor($segundos / 60);
        $horas = (int) floor($segundos / 3600);
        $dias = (int) floor($segundos / 86400);
        if ($minutos < 60) {
            return $minutos . ' minuto' . ($minutos !== 1 ? 's' : '');
        }
        if ($horas < 24) {
            return $horas . ' hora' . ($horas !== 1 ? 's' : '');
        }
        if ($dias < 7) {
            return $dias . ' día' . ($dias !== 1 ? 's' : '');
        }
        $semanas = (int) floor($dias / 7);
        if ($dias < 30) {
            return $semanas . ' semana' . ($semanas !== 1 ? 's' : '');
        }
        $meses = (int) floor($dias / 30);
        return $meses . ' mes' . ($meses !== 1 ? 'es' : '');
    }
}
