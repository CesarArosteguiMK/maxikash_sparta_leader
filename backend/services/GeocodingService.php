<?php

/**
 * Geocodificación de direcciones (ej. Domicilio_Completo megareporte) para obtener lat/lng.
 * Usa Google Maps Geocoding API. Cache por id_credito (7 días) para no repetir llamadas.
 * Sin IA; determinista.
 */

namespace Services;

class GeocodingService
{
    private const CACHE_TTL = 604800; // 7 días
    private const CACHE_PREFIX = 'geocode_megareporte_';
    private const GEOCODE_TIMEOUT = 5;

    /** @var string */
    private $cacheDir;

    /** @var string|null */
    private $apiKey;

    public function __construct(?string $apiKey = null, ?string $cacheDir = null)
    {
        $this->apiKey = $apiKey ?? (defined('GOOGLE_MAPS_API_KEY') ? (string) GOOGLE_MAPS_API_KEY : '');
        $this->cacheDir = $cacheDir ?? (__DIR__ . '/../storage/cache');
    }

    /**
     * Obtiene coordenadas del domicilio megareporte para un crédito.
     * Usa cache por id_credito; si no hay cache, geocodifica la dirección.
     *
     * @param int $idCredito
     * @param string $address Domicilio_Completo (ej. "AV SAN FRANCISCO S/N SAN FRANCISCO MOLONCO México")
     * @return array ['lat' => float, 'lng' => float, 'label' => 'Domicilio megareporte'] o [] si falla o address vacío
     */
    public function getDomicilioCoordsForCredito(int $idCredito, string $address): array
    {
        $address = trim($address);
        if ($address === '') {
            return [];
        }
        $cached = $this->getCachedCoords($idCredito);
        if ($cached !== null) {
            return $cached;
        }
        $coords = $this->geocode($address);
        if (!empty($coords)) {
            $this->setCachedCoords($idCredito, $coords['lat'], $coords['lng']);
            return [
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'label' => 'Domicilio megareporte',
            ];
        }
        return [];
    }

    /**
     * Geocodifica una dirección con Google Maps Geocoding API.
     *
     * @param string $address Dirección en texto (ej. "AV SAN FRANCISCO S/N SAN FRANCISCO MOLONCO México")
     * @return array ['lat' => float, 'lng' => float] o [] si falla o sin API key
     */
    public function geocode(string $address): array
    {
        $address = trim($address);
        if ($address === '' || $this->apiKey === '') {
            return [];
        }
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . rawurlencode($address) . '&key=' . rawurlencode($this->apiKey);
        $ctx = stream_context_create([
            'http' => [
                'timeout' => self::GEOCODE_TIMEOUT,
                'ignore_errors' => true,
            ],
        ]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return [];
        }
        $data = @json_decode($raw, true);
        if (!is_array($data) || ($data['status'] ?? '') !== 'OK' || empty($data['results'][0]['geometry']['location'])) {
            return [];
        }
        $loc = $data['results'][0]['geometry']['location'];
        $lat = (float) ($loc['lat'] ?? 0);
        $lng = (float) ($loc['lng'] ?? 0);
        if ($lat === 0.0 && $lng === 0.0) {
            return [];
        }
        return ['lat' => $lat, 'lng' => $lng];
    }

    private function getCachedCoords(int $idCredito): ?array
    {
        $path = $this->cacheDir . '/' . self::CACHE_PREFIX . $idCredito . '.json';
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }
        $data = @json_decode($raw, true);
        if (!is_array($data) || !isset($data['expires']) || $data['expires'] < time()) {
            @unlink($path);
            return null;
        }
        $lat = (float) ($data['lat'] ?? 0);
        $lng = (float) ($data['lng'] ?? 0);
        if ($lat === 0.0 && $lng === 0.0) {
            return null;
        }
        return [
            'lat' => $lat,
            'lng' => $lng,
            'label' => 'Domicilio megareporte',
        ];
    }

    private function setCachedCoords(int $idCredito, float $lat, float $lng): void
    {
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
        $path = $this->cacheDir . '/' . self::CACHE_PREFIX . $idCredito . '.json';
        $data = [
            'expires' => time() + self::CACHE_TTL,
            'lat' => $lat,
            'lng' => $lng,
        ];
        @file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}
