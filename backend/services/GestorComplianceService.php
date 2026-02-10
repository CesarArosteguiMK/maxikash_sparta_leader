<?php

/**
 * Verificación de cumplimiento del gestor (cercanía a ubicaciones del usuario).
 * 100% determinístico (sin IA). geofence_m configurable (default 100 m).
 */

namespace Services;

class GestorComplianceService
{
    /** Distancia en metros por debajo de la cual se considera "visita cercana" (geofence) */
    private const DEFAULT_GEOFENCE_M = 100;

    /** @var float */
    private $geofenceM;

    public function __construct(float $geofence_m = self::DEFAULT_GEOFENCE_M)
    {
        $this->geofenceM = $geofence_m > 0 ? $geofence_m : self::DEFAULT_GEOFENCE_M;
    }

    /**
     * Verifica cercanía del gestor a las ubicaciones del usuario.
     *
     * @param array $eventosGestor Lista de [lat, lng, timestamp, id?] (ubicación del gestor en cada evento)
     * @param array $ubicacionesUsuario Lista de [id?, lat, lng, ...]
     * @param float|null $geofence_m Override del radio en metros (opcional)
     * @return array visitas_cercanas (int), visitas_lejanas (int), porcentaje_cumplimiento, detalles [ {gestor_event_id, timestamp, distancia_m, ubicacion_id, cerca} ], alertas
     */
    public function verificarCercaniaGestor(array $eventosGestor, array $ubicacionesUsuario, ?float $geofence_m = null): array
    {
        $umbral = $geofence_m !== null && $geofence_m > 0 ? $geofence_m : $this->geofenceM;
        $visitas_cercanas_count = 0;
        $visitas_lejanas_count = 0;
        $detalles = [];
        $alertas = [];
        if (empty($eventosGestor)) {
            $alertas[] = 'Sin eventos de ubicación del gestor para comparar.';
            return [
                'visitas_cercanas' => 0,
                'visitas_lejanas' => 0,
                'porcentaje_cumplimiento' => null,
                'detalles' => [],
                'alertas' => $alertas,
            ];
        }
        if (empty($ubicacionesUsuario)) {
            $alertas[] = 'Sin ubicaciones del usuario; no se puede calcular cumplimiento.';
            return [
                'visitas_cercanas' => 0,
                'visitas_lejanas' => count($eventosGestor),
                'porcentaje_cumplimiento' => null,
                'detalles' => $this->buildDetallesSinUbicaciones($eventosGestor),
                'alertas' => $alertas,
            ];
        }
        foreach ($eventosGestor as $e) {
            $latG = $e['lat'] ?? $e['latitud'] ?? null;
            $lngG = $e['lng'] ?? $e['longitud'] ?? null;
            $gestorEventId = $e['id'] ?? $e['gestor_event_id'] ?? null;
            $ts = $e['timestamp'] ?? $e['fecha'] ?? $e['created_at'] ?? null;
            if ($ts !== null && !is_numeric($ts)) {
                $ts = strtotime($ts);
            }
            if ($latG === null || $lngG === null || (float) $latG === 0.0 && (float) $lngG === 0.0) {
                $alertas[] = 'Evento gestor sin GPS o coordenadas inválidas (gestor_event_id: ' . ($gestorEventId ?? '?') . ').';
                $detalles[] = [
                    'gestor_event_id' => $gestorEventId,
                    'timestamp' => $ts,
                    'distancia_m' => null,
                    'ubicacion_id' => null,
                    'cerca' => false,
                    'sin_gps' => true,
                    'distancias_por_ubicacion' => [],
                ];
                $visitas_lejanas_count++;
                continue;
            }
            $latG = (float) $latG;
            $lngG = (float) $lngG;
            [$minDist, $ubicacionId] = $this->minimaDistanciaYUbicacion($latG, $lngG, $ubicacionesUsuario);
            $distanciasPorUbicacion = $this->distanciasATodasUbicaciones($latG, $lngG, $ubicacionesUsuario);
            $cerca = $minDist <= $umbral;
            $detalles[] = [
                'gestor_event_id' => $gestorEventId,
                'timestamp' => $ts,
                'distancia_m' => round($minDist, 2),
                'ubicacion_id' => $ubicacionId,
                'cerca' => $cerca,
                'distancias_por_ubicacion' => $distanciasPorUbicacion,
            ];
            if ($cerca) {
                $visitas_cercanas_count++;
            } else {
                $visitas_lejanas_count++;
            }
        }
        $total = count($eventosGestor);
        $porcentaje_cumplimiento = $total > 0 ? round(100.0 * $visitas_cercanas_count / $total, 2) : null;
        if ($total >= 3 && $visitas_cercanas_count === 0) {
            $alertas[] = 'Gestor abrió ' . $total . ' veces fuera de geofence.';
        }
        return [
            'visitas_cercanas' => $visitas_cercanas_count,
            'visitas_lejanas' => $visitas_lejanas_count,
            'porcentaje_cumplimiento' => $porcentaje_cumplimiento,
            'detalles' => $detalles,
            'alertas' => array_values(array_unique($alertas)),
        ];
    }

    private function buildDetallesSinUbicaciones(array $eventosGestor): array
    {
        $out = [];
        foreach ($eventosGestor as $e) {
            $out[] = [
                'gestor_event_id' => $e['id'] ?? $e['gestor_event_id'] ?? null,
                'timestamp' => $e['timestamp'] ?? $e['fecha'] ?? null,
                'distancia_m' => null,
                'ubicacion_id' => null,
                'cerca' => false,
                'distancias_por_ubicacion' => [],
            ];
        }
        return $out;
    }

    /**
     * Distancia del punto (lat, lng) a cada ubicación.
     *
     * @return array list of [ 'ubicacion_id' => id, 'distancia_m' => float ]
     */
    private function distanciasATodasUbicaciones(float $lat, float $lng, array $ubicaciones): array
    {
        $out = [];
        foreach ($ubicaciones as $u) {
            $latU = (float) ($u['lat'] ?? $u['latitud'] ?? 0);
            $lngU = (float) ($u['lng'] ?? $u['longitud'] ?? 0);
            $out[] = [
                'ubicacion_id' => $u['id'] ?? null,
                'distancia_m' => round($this->haversineMetros($lat, $lng, $latU, $lngU), 2),
            ];
        }
        return $out;
    }

    private function minimaDistanciaYUbicacion(float $lat, float $lng, array $ubicaciones): array
    {
        $min = PHP_FLOAT_MAX;
        $ubicacionId = null;
        foreach ($ubicaciones as $u) {
            $latU = (float) ($u['lat'] ?? $u['latitud'] ?? 0);
            $lngU = (float) ($u['lng'] ?? $u['longitud'] ?? 0);
            $d = $this->haversineMetros($lat, $lng, $latU, $lngU);
            if ($d < $min) {
                $min = $d;
                $ubicacionId = $u['id'] ?? null;
            }
        }
        return [$min === PHP_FLOAT_MAX ? 0.0 : $min, $ubicacionId];
    }

    private function haversineMetros(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}
