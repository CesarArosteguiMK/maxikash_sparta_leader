<?php

namespace Services;

/**
 * MotosApiService
 * -----------------------------------------------------------------------
 * Servicio dedicado al consumo de la API externa de motocicletas
 * provista por el equipo de TI.
 *
 * Configuración (cargada desde la tabla config_api en BD):
 *   MOTOS_API_URL      → URL base del endpoint (ej. https://api.ti-interna.com/motos)
 *   MOTOS_API_KEY      → Clave de autenticación (Bearer / header según indiquen)
 *   MOTOS_API_SECRET   → Secret adicional si se requiere HMAC o Basic Auth
 *   MOTOS_API_TIMEOUT  → Timeout en segundos (default: 10)
 *
 * Cuando las credenciales no estén configuradas, los métodos devuelven
 * ['success' => false, 'unavailable' => true] para que el frontend pueda
 * mostrar un estado "API no disponible" sin romper el flujo.
 *
 * -----------------------------------------------------------------------
 * TODO (completar cuando TI entregue credenciales y documentación):
 *   1. Confirmar el endpoint exacto en MOTOS_API_URL.
 *   2. Configurar MOTOS_API_KEY / MOTOS_API_SECRET en la tabla config_api.
 *   3. Mapear los campos en _mapearRespuesta() con base en lo que
 *      se detecte en Postman.
 *   4. Ajustar _buildHeaders() si el esquema de auth difiere de Bearer.
 * -----------------------------------------------------------------------
 */
class MotosApiService
{
    // ------------------------------------------------------------------
    // Claves exactas que deben existir en config_api
    // ------------------------------------------------------------------
    private const CFG_URL     = 'MOTOS_API_URL';
    private const CFG_KEY     = 'MOTOS_API_KEY';
    private const CFG_SECRET  = 'MOTOS_API_SECRET';
    private const CFG_TIMEOUT = 'MOTOS_API_TIMEOUT';

    /** @var array Configuración cargada desde BD */
    private array $cfg;

    public function __construct()
    {
        $this->cfg = $this->_loadConfig();
    }

    // ==================================================================
    // API PÚBLICA
    // ==================================================================

    /**
     * Consulta los datos de una moto a partir del ID de crédito interno.
     * El equipo de TI confirmará si el lookup se hace por id_credito,
     * número de serie u otro identificador; ajustar en _buildPayload().
     *
     * @param int $idCredito
     * @return array {
     *   success: bool,
     *   unavailable?: bool,       // true si la API no está configurada aún
     *   datos_moto?: array,       // campos normalizados (ver _mapearRespuesta)
     *   raw?: array,              // respuesta cruda (útil en desarrollo)
     *   message?: string
     * }
     */
    public function consultarPorCredito(int $idCredito): array
    {
        if (!$this->_isConfigured()) {
            return [
                'success'     => false,
                'unavailable' => true,
                'message'     => 'API de motos no configurada. Contacta a TI para obtener las credenciales.',
            ];
        }

        $payload = $this->_buildPayload('credito', ['id_credito' => $idCredito]);
        return $this->_request($payload);
    }

    /**
     * Consulta por número de serie VIN.
     * Método auxiliar — puede ser el identificador preferido por la API de TI.
     *
     * @param string $noSerie
     */
    public function consultarPorSerie(string $noSerie): array
    {
        if (!$this->_isConfigured()) {
            return [
                'success'     => false,
                'unavailable' => true,
                'message'     => 'API de motos no configurada.',
            ];
        }

        $payload = $this->_buildPayload('serie', ['no_serie' => $noSerie]);
        return $this->_request($payload);
    }

    // ==================================================================
    // CONFIGURACIÓN
    // ==================================================================

    /** @return bool La API está lista para usarse */
    public function isConfigured(): bool
    {
        return $this->_isConfigured();
    }

    // ==================================================================
    // INTERNOS
    // ==================================================================

    /**
     * Carga las keys de la tabla config_api.
     * Sigue el mismo patrón que ConfigApi.php del proyecto.
     */
    private function _loadConfig(): array
    {
        try {
            $db   = new \Core\Database();
            $rows = $db->queryAll(
                'SELECT clave, valor FROM config_api WHERE clave IN (?, ?, ?, ?)',
                [self::CFG_URL, self::CFG_KEY, self::CFG_SECRET, self::CFG_TIMEOUT]
            );
        } catch (\Throwable $e) {
            return [];
        }

        $cfg = [];
        foreach ($rows as $row) {
            $clave = $row['clave'] ?? '';
            $valor = $row['valor'] ?? '';
            if ($clave !== '') {
                $cfg[$clave] = (string) $valor;
            }
        }
        return $cfg;
    }

    private function _isConfigured(): bool
    {
        $url = trim($this->cfg[self::CFG_URL] ?? '');
        $key = trim($this->cfg[self::CFG_KEY] ?? '');
        return $url !== '' && $key !== '';
    }

    /**
     * Construye el payload para el request.
     * TODO: ajustar la estructura exacta cuando TI comparta la documentación.
     *
     * @param string $tipo  'credito' | 'serie'
     * @param array  $params
     */
    private function _buildPayload(string $tipo, array $params): array
    {
        // TODO: mapear según la firma real de la API de TI
        return array_merge(['tipo_busqueda' => $tipo], $params);
    }

    /**
     * Construye los headers de autenticación.
     * TODO: cambiar si TI usa Basic Auth, HMAC u otro esquema.
     */
    private function _buildHeaders(): array
    {
        $key    = trim($this->cfg[self::CFG_KEY]    ?? '');
        $secret = trim($this->cfg[self::CFG_SECRET] ?? '');

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer ' . $key,
        ];

        if ($secret !== '') {
            // TODO: confirmar si se envía como header separado, HMAC signature, etc.
            $headers[] = 'X-Api-Secret: ' . $secret;
        }

        return $headers;
    }

    /**
     * Ejecuta el request HTTP con cURL.
     */
    private function _request(array $payload): array
    {
        $url     = rtrim($this->cfg[self::CFG_URL] ?? '', '/');
        $timeout = (int) ($this->cfg[self::CFG_TIMEOUT] ?? 10);
        if ($timeout < 1) $timeout = 10;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $this->_buildHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno || $raw === false) {
            return [
                'success' => false,
                'message' => 'Error de red al contactar la API de motos: ' . $error,
            ];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return [
                'success' => false,
                'message' => 'La API de motos devolvió una respuesta no válida (HTTP ' . $httpCode . ').',
            ];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? ('HTTP ' . $httpCode);
            return ['success' => false, 'message' => 'API de motos: ' . $msg];
        }

        // TODO: verificar el campo de éxito real que use la API de TI
        // (puede ser 'success', 'ok', 'status' == 'OK', etc.)
        $isSuccess = isset($decoded['success'])
            ? (bool) $decoded['success']
            : ($httpCode >= 200 && $httpCode < 300);

        if (!$isSuccess) {
            $msg = $decoded['message'] ?? $decoded['error'] ?? 'La API no devolvió éxito.';
            return ['success' => false, 'message' => $msg];
        }

        return [
            'success'    => true,
            'datos_moto' => $this->_mapearRespuesta($decoded),
            'raw'        => $decoded, // útil durante integración/debugging
        ];
    }

    /**
     * Normaliza la respuesta de la API al esquema interno usado por el frontend.
     *
     * Campos esperados en la respuesta normalizada:
     *   moto_marca    → Marca
     *   moto_modelo   → Modelo
     *   moto_anio     → Año
     *   moto_color    → Color
     *   moto_no_serie → Número de serie / VIN
     *   moto_no_motor → Número de motor
     *   moto_placas   → Placas
     *
     * TODO: completar el mapeo una vez que Postman revele la estructura real.
     *       Ejemplo: si la API devuelve { "marca": "Honda", "vin": "ABC123" },
     *       mapear: 'moto_marca' => $raw['marca'], 'moto_no_serie' => $raw['vin']
     *
     * @param array $raw  Respuesta completa decodificada de la API
     */
    private function _mapearRespuesta(array $raw): array
    {
        // TODO: reemplazar los valores de ejemplo con el mapeo real de la API de TI
        //
        // Estructura de ejemplo — AJUSTAR según Postman:
        // $data = $raw['data'] ?? $raw['moto'] ?? $raw['resultado'] ?? $raw;
        $data = $raw['data'] ?? $raw['moto'] ?? $raw['resultado'] ?? $raw;

        return [
            // TODO: mapear el campo real de la API  ↓
            'moto_marca'    => $this->_str($data, ['marca',    'brand',  'MARCA']),
            'moto_modelo'   => $this->_str($data, ['modelo',   'model',  'MODELO']),
            'moto_anio'     => $this->_str($data, ['anio',     'year',   'AÑO', 'ANIO']),
            'moto_color'    => $this->_str($data, ['color',    'COLOR']),
            'moto_no_serie' => $this->_str($data, ['no_serie', 'serie',  'vin', 'VIN', 'NO_SERIE']),
            'moto_no_motor' => $this->_str($data, ['no_motor', 'motor',  'NO_MOTOR']),
            'moto_placas'   => $this->_str($data, ['placas',   'plates', 'PLACAS']),
        ];
    }

    /**
     * Extrae un string del array intentando múltiples keys candidatas.
     * Ignora valores nulos/vacíos y retorna '' si no se encuentra nada.
     */
    private function _str(array $data, array $candidatos): string
    {
        foreach ($candidatos as $k) {
            if (isset($data[$k]) && $data[$k] !== null && $data[$k] !== '') {
                return trim((string) $data[$k]);
            }
        }
        return '';
    }
}
