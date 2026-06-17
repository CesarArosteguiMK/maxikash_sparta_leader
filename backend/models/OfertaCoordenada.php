<?php

namespace Models;

use Services\MkValidationsService;

/**
 * Coordenada de firma desde DynamoDB mk-validations.
 * Mantiene el formato esperado por el mapa de direcciones alternas.
 */
class OfertaCoordenada
{
    private const CACHE_TTL = 120; // La tabla cambia constantemente; cache muy corto solo para doble clic/recarga inmediata.

    private static function getCachePath(int $idCredito): string
    {
        return dirname(__DIR__) . '/storage/cache/oferta_coordenada_dynamo_v3_' . $idCredito . '.json';
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
        // Fallback tolerante para coordenadas con simbolos UTF-8 correctos o mojibake.
        $normalizado = html_entity_decode($dms, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalizado = str_replace(
            ['Â°', 'º', '˚', '°', '′', '’', '`', '´', '″', '“', '”'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
            $normalizado
        );
        if (
            preg_match_all('/-?\d+(?:[.,]\d+)?/', $normalizado, $nums)
            && count($nums[0]) >= 6
            && preg_match_all('/[NSEW]/iu', $normalizado, $dirs)
            && count($dirs[0]) >= 2
        ) {
            $latDeg = (float) str_replace(',', '.', $nums[0][0]);
            $latMin = (float) str_replace(',', '.', $nums[0][1]);
            $latSec = (float) str_replace(',', '.', $nums[0][2]);
            $lngDeg = (float) str_replace(',', '.', $nums[0][3]);
            $lngMin = (float) str_replace(',', '.', $nums[0][4]);
            $lngSec = (float) str_replace(',', '.', $nums[0][5]);
            $latSign = strtoupper($dirs[0][0]) === 'S' ? -1 : 1;
            $lngSign = strtoupper($dirs[0][1]) === 'W' ? -1 : 1;

            $lat = $latSign * ($latDeg + $latMin / 60 + $latSec / 3600);
            $lng = $lngSign * ($lngDeg + $lngMin / 60 + $lngSec / 3600);

            return ['lat' => round($lat, 6), 'lng' => round($lng, 6)];
        }

        return null;
    }

    /**
     * Obtiene la coordenada donde se firmo por id_credito/id_oferta.
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
            self::ensureComposerAutoload();

            $service = new MkValidationsService();
            $result = $service->getCoordenadasFirma($idCredito);
            if (empty($result['success']) || empty($result['datos'][0]['firma'])) {
                return [];
            }

            $row = $result['datos'][0];
            $firma = $row['firma'];
            $lat = isset($firma['lat']) ? (float) $firma['lat'] : null;
            $lng = isset($firma['lng']) ? (float) $firma['lng'] : null;
            if ($lat === null || $lng === null || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                self::writeCache($idCredito, []);
                return [];
            }

            $out = [[
                'lat' => $lat,
                'lng' => $lng,
                'direccion_maps' => $lat . ',' . $lng,
                'direccion_label' => 'Ubicación donde firmó el cliente',
                'donde_firma' => 'Lugar de firma FAD',
                'fuente' => 'dynamodb_mk_validations',
                'fecha_validacion' => $row['fecha_validacion'] ?? null,
                'is_valid' => (bool)($row['is_valid'] ?? false),
            ]];
            self::writeCache($idCredito, $out);
            return $out;
        } catch (\Exception $e) {
            error_log('[OfertaCoordenada] Error al consultar DynamoDB id_oferta=' . $idCredito . ': ' . $e->getMessage());
            $stale = self::readCache($idCredito);
            if ($stale !== null) {
                return $stale;
            }
            return [];
        }
    }

    private static function ensureComposerAutoload(): void
    {
        if (!defined('RAIZ')) {
            return;
        }

        $bootstrap = RAIZ . '/bootstrap_composer.php';
        if (is_file($bootstrap)) {
            require_once $bootstrap;
            if (function_exists('sparta_require_composer_autoload')) {
                sparta_require_composer_autoload();
            }
        }
    }
}
