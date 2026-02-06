<?php

/**
 * Análisis geoespacial determinístico (sin IA).
 *
 * Fórmula Haversine: a = sin²(Δlat/2) + cos(lat1)·cos(lat2)·sin²(Δlon/2);
 * c = 2·atan2(√a, √(1−a)); d = R·c (R = 6371000 m).
 * geofence_radius_m: radio por defecto 100 m (configurable).
 */

namespace Services;

class SpatialAnalyticsService
{
    /** Radio de la Tierra en metros (Haversine) */
    private const RADIO_TIERRA_M = 6371000;

    /** Radio por defecto para geofence (m) */
    private const DEFAULT_GEOFENCE_RADIUS_M = 100;

    /** @var float */
    private $geofenceRadiusM;

    public function __construct(float $geofence_radius_m = self::DEFAULT_GEOFENCE_RADIUS_M)
    {
        $this->geofenceRadiusM = $geofence_radius_m > 0 ? $geofence_radius_m : self::DEFAULT_GEOFENCE_RADIUS_M;
    }

    /**
     * Distancia entre dos puntos en metros (Haversine).
     */
    public function haversineMetros(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return self::RADIO_TIERRA_M * $c;
    }

    /**
     * Distancia de cada ubicación del usuario respecto al domicilio.
     *
     * Input: ubicacionesUsuario array de {id, lat, lng, label, visitas_count, ultima_fecha}; domicilio {id, lat, lng, label}.
     * Output: array de {id, label, distancia_m, ultima_fecha, visitas_count}.
     */
    public function calcularDistanciasCasa(array $ubicacionesUsuario, array $domicilio): array
    {
        $latCasa = (float) ($domicilio['lat'] ?? $domicilio['latitud'] ?? 0);
        $lngCasa = (float) ($domicilio['lng'] ?? $domicilio['longitud'] ?? 0);
        $out = [];
        foreach ($ubicacionesUsuario as $u) {
            $lat = (float) ($u['lat'] ?? $u['latitud'] ?? 0);
            $lng = (float) ($u['lng'] ?? $u['longitud'] ?? 0);
            $dist = $this->haversineMetros($lat, $lng, $latCasa, $lngCasa);
            $out[] = [
                'id' => $u['id'] ?? '',
                'label' => $u['label'] ?? $u['texto'] ?? '',
                'distancia_m' => round($dist, 2),
                'ultima_fecha' => $u['ultima_fecha'] ?? null,
                'visitas_count' => (int) ($u['visitas_count'] ?? $u['cantidad_registros'] ?? 0),
            ];
        }
        return $out;
    }

    /**
     * Última apertura de la aplicación (evento GPS más reciente).
     *
     * Output: {lat, lng, timestamp, distancia_a_casa_m, ubicacion_id}.
     * Si se pasa $ubicacionesUsuario (array de {id, lat, lng, ...}), se asigna ubicacion_id al más cercano.
     */
    public function ultimaUbicacionApp(array $eventosGPS, ?array $domicilio = null, array $ubicacionesUsuario = []): array
    {
        if (empty($eventosGPS)) {
            return [];
        }
        $conTs = [];
        foreach ($eventosGPS as $e) {
            $lat = (float) ($e['lat'] ?? $e['latitud'] ?? 0);
            $lng = (float) ($e['lng'] ?? $e['longitud'] ?? 0);
            $ts = $e['timestamp'] ?? $e['fecha'] ?? $e['fecha_creacion'] ?? null;
            if ($ts !== null) {
                $ts = is_numeric($ts) ? (int) $ts : strtotime($ts);
            }
            if ($ts === false || $ts === null) {
                continue;
            }
            $conTs[] = ['lat' => $lat, 'lng' => $lng, 'timestamp' => $ts, 'ubicacion_id' => $e['ubicacion_id'] ?? null];
        }
        if (empty($conTs)) {
            return [];
        }
        usort($conTs, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });
        $ult = $conTs[0];
        $out = [
            'lat' => $ult['lat'],
            'lng' => $ult['lng'],
            'timestamp' => $ult['timestamp'],
            'ubicacion_id' => $ult['ubicacion_id'] ?? null,
        ];
        if ($domicilio !== null) {
            $latCasa = (float) ($domicilio['lat'] ?? $domicilio['latitud'] ?? 0);
            $lngCasa = (float) ($domicilio['lng'] ?? $domicilio['longitud'] ?? 0);
            $out['distancia_a_casa_m'] = round($this->haversineMetros($ult['lat'], $ult['lng'], $latCasa, $lngCasa), 2);
        }
        if (empty($out['ubicacion_id']) && !empty($ubicacionesUsuario)) {
            $out['ubicacion_id'] = $this->ubicacionMasCercana($ult['lat'], $ult['lng'], $ubicacionesUsuario);
        }
        return $out;
    }

    /**
     * Aperturas de la app en los últimos N días.
     *
     * Output: total_aperturas, aperturas_por_ubicacion [ {ubicacion_id, label, count, distancia_a_casa_m} ],
     * ubicaciones_distintas, resumen_por_dia [ {fecha: "YYYY-MM-DD", total: int} ].
     * Opcional: $ubicacionesUsuario y $domicilio para enriquecer con label y distancia_a_casa_m.
     */
    public function aperturasUltimosDias(array $eventosGPS, int $dias = 5, array $ubicacionesUsuario = [], array $domicilio = []): array
    {
        $cut = time() - $dias * 86400;
        $enVentana = [];
        foreach ($eventosGPS as $e) {
            $lat = (float) ($e['lat'] ?? $e['latitud'] ?? 0);
            $lng = (float) ($e['lng'] ?? $e['longitud'] ?? 0);
            $ts = $e['timestamp'] ?? $e['fecha'] ?? $e['fecha_creacion'] ?? null;
            if ($ts !== null) {
                $ts = is_numeric($ts) ? (int) $ts : strtotime($ts);
            }
            if ($ts === false || $ts === null || $ts < $cut) {
                continue;
            }
            $key = round($lat, 5) . '_' . round($lng, 5);
            $enVentana[] = [
                'key' => $key,
                'lat' => $lat,
                'lng' => $lng,
                'timestamp' => $ts,
                'dia' => date('Y-m-d', $ts),
            ];
        }
        $total_aperturas = count($enVentana);
        $countByKey = [];
        $resumenPorDia = [];
        foreach ($enVentana as $e) {
            $countByKey[$e['key']] = ($countByKey[$e['key']] ?? 0) + 1;
            $resumenPorDia[$e['dia']] = ($resumenPorDia[$e['dia']] ?? 0) + 1;
        }
        ksort($resumenPorDia);
        $aperturasPorUbicacion = [];
        $latCasa = (float) ($domicilio['lat'] ?? $domicilio['latitud'] ?? 0);
        $lngCasa = (float) ($domicilio['lng'] ?? $domicilio['longitud'] ?? 0);
        foreach ($countByKey as $key => $count) {
            $parts = explode('_', $key, 2);
            $lat = isset($parts[0]) ? (float) $parts[0] : 0;
            $lng = isset($parts[1]) ? (float) $parts[1] : 0;
            $dist = ($latCasa !== 0.0 || $lngCasa !== 0.0) ? round($this->haversineMetros($lat, $lng, $latCasa, $lngCasa), 2) : null;
            $uid = $this->ubicacionMasCercana($lat, $lng, $ubicacionesUsuario);
            $label = '';
            foreach ($ubicacionesUsuario as $u) {
                if (($u['id'] ?? '') === $uid) {
                    $label = $u['label'] ?? $u['texto'] ?? '';
                    break;
                }
            }
            $aperturasPorUbicacion[] = [
                'ubicacion_id' => $uid,
                'label' => $label,
                'count' => $count,
                'distancia_a_casa_m' => $dist,
            ];
        }
        usort($aperturasPorUbicacion, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $resumenPorDiaArr = [];
        foreach ($resumenPorDia as $fecha => $total) {
            $resumenPorDiaArr[] = ['fecha' => $fecha, 'total' => $total];
        }
        return [
            'total_aperturas' => $total_aperturas,
            'aperturas_por_ubicacion' => $aperturasPorUbicacion,
            'ubicaciones_distintas' => count($countByKey),
            'resumen_por_dia' => $resumenPorDiaArr,
        ];
    }

    public function getGeofenceRadiusM(): float
    {
        return $this->geofenceRadiusM;
    }

    private function ubicacionMasCercana(float $lat, float $lng, array $ubicaciones): ?string
    {
        if (empty($ubicaciones)) {
            return null;
        }
        $minDist = PHP_FLOAT_MAX;
        $id = null;
        foreach ($ubicaciones as $u) {
            $latU = (float) ($u['lat'] ?? $u['latitud'] ?? 0);
            $lngU = (float) ($u['lng'] ?? $u['longitud'] ?? 0);
            $d = $this->haversineMetros($lat, $lng, $latU, $lngU);
            if ($d < $minDist) {
                $minDist = $d;
                $id = $u['id'] ?? null;
            }
        }
        return $id;
    }
}
