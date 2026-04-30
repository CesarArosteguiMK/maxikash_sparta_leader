<?php

namespace Controllers;

use Core\Controller;

require_once __DIR__ . '/../services/MotosApiService.php';

use Services\MotosApiService;

/**
 * MotosApi
 * -----------------------------------------------------------------------
 * Controlador proxy para la API externa de motocicletas provista por TI.
 *
 * Endpoints:
 *   POST /MotosApi/consultarMoto      → consulta por id_credito
 *   POST /MotosApi/consultarPorSerie  → consulta por número de serie
 *   GET  /MotosApi/estado             → diagnóstico: indica si la API está configurada
 *
 * Caché en storage/cache/ con TTL configurable (default 30 min) para no
 * saturar la API externa en recargas del mismo crédito.
 * -----------------------------------------------------------------------
 */
class MotosApi extends Controller
{
    /** Tiempo de vida del caché en segundos (30 minutos) */
    private const CACHE_TTL = 1800;

    /** Directorio de caché */
    private const CACHE_DIR = __DIR__ . '/../storage/cache';

    private MotosApiService $service;

    public function __construct()
    {
        parent::__construct();
        $this->service = new MotosApiService();
    }

    // ==================================================================
    // ENDPOINTS
    // ==================================================================

    /**
     * POST /MotosApi/consultarMoto
     * Body JSON: { "id_credito": 1234 }
     *
     * Respuesta:
     * {
     *   "success": true,
     *   "datos_moto": { "moto_marca": "...", "moto_modelo": "...", ... },
     *   "from_cache": false,
     *   "unavailable": false   // true si la API aún no está configurada
     * }
     */
    public function consultarMoto(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        // Si la API no está configurada aún, responder rápido con unavailable=true
        // para que el frontend muestre el estado "Pendiente de configuración".
        if (!$this->service->isConfigured()) {
            echo json_encode([
                'success'     => false,
                'unavailable' => true,
                'message'     => 'La API de motos aún no está configurada. TI proporcionará las credenciales.',
            ]);
            return;
        }

        // Intentar caché local
        $cacheKey  = 'motos_api_credito_' . $idCredito;
        $cached    = $this->_cacheGet($cacheKey);

        if ($cached !== null) {
            echo json_encode(array_merge($cached, ['from_cache' => true]));
            return;
        }

        try {
            $result = $this->service->consultarPorCredito($idCredito);

            if ($result['success']) {
                // Guardar en caché solo respuestas exitosas
                $this->_cacheSet($cacheKey, $result);
            }

            echo json_encode(array_merge($result, ['from_cache' => false]));
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno al consultar la API de motos.',
            ]);
        }
    }

    /**
     * POST /MotosApi/consultarPorSerie
     * Body JSON: { "no_serie": "ABC123" }
     */
    public function consultarPorSerie(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $body    = json_decode(file_get_contents('php://input'), true) ?? [];
        $noSerie = trim((string) ($body['no_serie'] ?? ''));

        if ($noSerie === '') {
            echo json_encode(['success' => false, 'message' => 'Número de serie requerido.']);
            return;
        }

        if (!$this->service->isConfigured()) {
            echo json_encode([
                'success'     => false,
                'unavailable' => true,
                'message'     => 'La API de motos aún no está configurada.',
            ]);
            return;
        }

        $cacheKey = 'motos_api_serie_' . md5(strtoupper($noSerie));
        $cached   = $this->_cacheGet($cacheKey);

        if ($cached !== null) {
            echo json_encode(array_merge($cached, ['from_cache' => true]));
            return;
        }

        try {
            $result = $this->service->consultarPorSerie($noSerie);

            if ($result['success']) {
                $this->_cacheSet($cacheKey, $result);
            }

            echo json_encode(array_merge($result, ['from_cache' => false]));
        } catch (\Throwable $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error interno al consultar la API de motos por serie.',
            ]);
        }
    }

    /**
     * GET /MotosApi/estado
     * Diagnóstico: indica si las credenciales están configuradas.
     * Útil para el panel de configuración o logs de TI.
     */
    public function estado(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'     => true,
            'configurada' => $this->service->isConfigured(),
            'mensaje'     => $this->service->isConfigured()
                ? 'API de motos configurada y lista.'
                : 'API de motos no configurada. Faltan MOTOS_API_URL y/o MOTOS_API_KEY en config_api.',
        ]);
    }

    /**
     * POST /MotosApi/limpiarCache
     * Invalida el caché de un crédito (útil tras actualizar datos en la API de TI).
     * Body JSON: { "id_credito": 1234 }
     */
    public function limpiarCache(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $body      = json_decode(file_get_contents('php://input'), true) ?? [];
        $idCredito = (int) ($body['id_credito'] ?? 0);

        if ($idCredito <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de crédito inválido.']);
            return;
        }

        $cacheKey  = 'motos_api_credito_' . $idCredito;
        $cacheFile = self::CACHE_DIR . '/' . $cacheKey . '.json';

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        echo json_encode(['success' => true, 'message' => 'Caché invalidado para crédito #' . $idCredito]);
    }

    // ==================================================================
    // CACHÉ — archivos JSON simples en storage/cache/
    // Sigue el mismo patrón de Api.php del proyecto.
    // ==================================================================

    private function _cacheGet(string $key): ?array
    {
        $file = self::CACHE_DIR . '/' . $key . '.json';
        if (!file_exists($file)) return null;

        $mtime = filemtime($file);
        if ($mtime === false || (time() - $mtime) > self::CACHE_TTL) {
            @unlink($file);
            return null;
        }

        $raw = @file_get_contents($file);
        if ($raw === false) return null;

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    private function _cacheSet(string $key, array $data): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            @mkdir(self::CACHE_DIR, 0755, true);
        }
        $file = self::CACHE_DIR . '/' . $key . '.json';
        @file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
