<?php

namespace Models;

use Core\DatabaseGeo;

/**
 * Direcciones alternas desde __SPARTA_SECRET_REDACTED__.oferta_coordenada.
 * fk_oferta = id_credito; columnas: coordenada_fat (DMS), direccion_maps, donde_firma.
 */
class OfertaCoordenada
{
    private const CACHE_TTL = 21600; // 6 horas

    private static function getCachePath(int $idCredito): string
    {
        return dirname(__DIR__) . '/storage/cache/oferta_coordenada_' . $idCredito . '.json';
    }

    private static function readCache(int $idCredito): ?array
    {
        $cachePath = self::getCachePath($idCredito);
        if (!is_file($cachePath)) {
            return null;
        }
        $raw = @file_get_contents($cachePath);
        if ($raw === false) {
            return null;
        }
        $cache = json_decode($raw, true);
        if (!is_array($cache) || !isset($cache['expires']) || !array_key_exists('payload', $cache)) {
            return null;
        }
        if ((int) $cache['expires'] < time()) {
            return null;
        }
        return is_array($cache['payload']) ? $cache['payload'] : [];
    }

    private static function writeCache(int $idCredito, array $payload): void
    {
        $cachePath = self::getCachePath($idCredito);
        $cacheDir = dirname($cachePath);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        @file_put_contents($cachePath, json_encode([
            'expires' => time() + self::CACHE_TTL,
            'payload' => $payload,
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * Convierte coordenada en formato DMS (ej. 19°40'23.3"N 99°09'53.3"W) a decimal [lat, lng].
     * @param string $dms
     * @return array|null ['lat' => float, 'lng' => float] o null si no se puede parsear
     */
    public static function dmsToDecimal($dms)
    {
        $dms = trim((string) $dms);
        if ($dms === '') {
            return null;
        }
        // Formato: 19°40'23.3"N 99°09'53.3"W (grados ° minutos ' segundos " N/S espacio grados ° minutos ' segundos " E/W). Espacios opcionales.
        if (preg_match('/^\s*(\d+)\s*°\s*(\d+)\s*[\'′]\s*([\d.]+)\s*["″]\s*([NS])\s+(\d+)\s*°\s*(\d+)\s*[\'′]\s*([\d.]+)\s*["″]\s*([EW])\s*$/iu', $dms, $m)) {
            $latDeg = (float) $m[1];
            $latMin = (float) $m[2];
            $latSec = (float) $m[3];
            $latSign = strtoupper($m[4]) === 'S' ? -1 : 1;
            $lat = $latSign * ($latDeg + $latMin / 60 + $latSec / 3600);

            $lngDeg = (float) $m[5];
            $lngMin = (float) $m[6];
            $lngSec = (float) $m[7];
            $lngSign = strtoupper($m[8]) === 'W' ? -1 : 1;
            $lng = $lngSign * ($lngDeg + $lngMin / 60 + $lngSec / 3600);

            return ['lat' => round($lat, 6), 'lng' => round($lng, 6)];
        }
        return null;
    }

    /**
     * Obtiene filas de oferta_coordenada por id_credito (fk_oferta).
     * Devuelve array de [ 'lat' => float, 'lng' => float, 'direccion_maps' => string, 'donde_firma' => string ].
     */
    public static function getPorIdCredito($idCredito)
    {
        $idCredito = (int) $idCredito;
        if ($idCredito < 1) {
            return [];
        }
        $cached = self::readCache($idCredito);
        if ($cached !== null) {
            return $cached;
        }
        try {
            $db = new DatabaseGeo();
            $sql = "SELECT coordenada_fat, direccion_maps, donde_firma FROM oferta_coordenada WHERE fk_oferta = :id ORDER BY idoferta_coordenada ASC";
            $rows = $db->queryAll($sql, ['id' => $idCredito]);
            if (empty($rows)) {
                self::writeCache($idCredito, []);
                return [];
            }
            $out = [];
            foreach ($rows as $row) {
                $coords = self::dmsToDecimal($row['coordenada_fat'] ?? '');
                if ($coords === null) {
                    continue;
                }
                $out[] = [
                    'lat' => $coords['lat'],
                    'lng' => $coords['lng'],
                    'direccion_maps' => trim((string) ($row['direccion_maps'] ?? '')),
                    'donde_firma' => trim((string) ($row['donde_firma'] ?? '')),
                ];
            }
            self::writeCache($idCredito, $out);
            return $out;
        } catch (\Exception $e) {
            error_log('[OfertaCoordenada] Error al consultar fk_oferta=' . $idCredito . ': ' . $e->getMessage());
            $stale = self::readCache($idCredito);
            if ($stale !== null) {
                return $stale;
            }
            return [];
        }
    }
}
