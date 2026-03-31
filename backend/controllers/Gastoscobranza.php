<?php

namespace Controllers;

use Core\Controller;

/**
 * Shell Gastos Cobranza — agente HTTP + reporte_cobranza.py (iterativo).
 * Permisos: módulo web id 31 (rutas en public/index.php).
 */
class Gastoscobranza extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function agenteHabilitado()
    {
        // Solo [gastoscobranza_agent] vía parse_ini(process_sections); no usar CONFIGURACION plano (colisiona "enabled"/"url"/"key").
        $enabled = $this->agenteIni('enabled', '0');
        return in_array((string)$enabled, ['1', 'true', 'TRUE', 'yes', 'on'], true);
    }

    private function agenteBaseUrl()
    {
        $url = trim((string)$this->agenteIni('url', 'http://127.0.0.1:3120'));
        return rtrim($url, '/');
    }

    private function agenteApiKey()
    {
        return trim((string)$this->agenteIni('key', ''));
    }

    private function agenteIni($key, $default = '')
    {
        static $cfg = null;
        if ($cfg === null) {
            $cfg = [];
            $configFile = __DIR__ . '/../config/config.ini';
            if (is_file($configFile)) {
                $parsed = @parse_ini_file($configFile, true);
                if (is_array($parsed) && isset($parsed['gastoscobranza_agent']) && is_array($parsed['gastoscobranza_agent'])) {
                    $cfg = $parsed['gastoscobranza_agent'];
                }
            }
        }
        if (array_key_exists($key, $cfg)) {
            return $cfg[$key];
        }
        return $default;
    }

    private function agenteRequest($method, $path, $payload = null, $timeoutSec = 120)
    {
        $url = $this->agenteBaseUrl() . $path;
        $headers = ['Accept: application/json'];
        $key = $this->agenteApiKey();
        if ($key !== '') {
            $headers[] =  'X-Api-Key: ' . $key;
        }
        $timeoutSec = max(5, (int)$timeoutSec);

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(8, $timeoutSec));
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutSec);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if ($payload !== null) {
                if (is_array($payload)) {
                    $json = json_encode($payload);
                    $headers[] = 'Content-Type: application/json';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                }
            }
            $raw = curl_exec($ch);
            $err = curl_error($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($raw === false) {
                return ['success' => false, 'status' => 0, 'error' => $err ?: 'Error CURL', 'json' => null, 'raw' => ''];
            }
            $json = json_decode($raw, true);
            return ['success' => ($status >= 200 && $status < 300), 'status' => $status, 'error' => $err, 'json' => $json, 'raw' => $raw];
        }

        $opts = ['http' => ['method' => strtoupper($method), 'timeout' => $timeoutSec, 'ignore_errors' => true, 'header' => implode("\r\n", $headers)]];
        if ($payload !== null) {
            $opts['http']['header'] .= "\r\nContent-Type: application/json";
            $opts['http']['content'] = is_array($payload) ? json_encode($payload) : (string)$payload;
        }
        $ctx = stream_context_create($opts);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            return ['success' => false, 'status' => 0, 'error' => 'No se pudo conectar al agente', 'json' => null, 'raw' => ''];
        }
        $status = 200;
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
            $status = (int)$m[1];
        }
        $json = json_decode($raw, true);
        return ['success' => ($status >= 200 && $status < 300), 'status' => $status, 'error' => '', 'json' => $json, 'raw' => $raw];
    }

    /**
     * Vista principal (Configuración → Shell Gastos Cobranza).
     */
    public function shell()
    {
        $this->set('tituloShell', 'Shell Gastos Cobranza');
        $this->set('gastosCobranzaAgenteUrl', $this->agenteBaseUrl());
        $this->set('gastosCobranzaAgenteHabilitado', $this->agenteHabilitado());
        self::render('shell_gastos_cobranza');
    }

    /**
     * Estado del agente Node (GET /health).
     */
    public function estadoAgente()
    {
        try {
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON([
                    'success' => true,
                    'agente_configurado' => false,
                    'agente_online' => false,
                    'detalle' => 'Agente deshabilitado en config.ini ([gastoscobranza_agent] enabled=0).',
                    'url' => $this->agenteBaseUrl(),
                ]);
                return;
            }
            $health = $this->agenteRequest('GET', '/health', null, 12);
            if (!$health['success'] || !is_array($health['json']) || empty($health['json']['success'])) {
                self::respuestaJSON([
                    'success' => true,
                    'agente_configurado' => true,
                    'agente_online' => false,
                    'detalle' => 'No responde /health (' . ($health['error'] ?: ('HTTP ' . ($health['status'] ?? 0))) . ').',
                    'url' => $this->agenteBaseUrl(),
                ]);
                return;
            }
            self::respuestaJSON([
                'success' => true,
                'agente_configurado' => true,
                'agente_online' => true,
                'detalle' => $health['json']['servicio'] ?? 'Agente en línea.',
                'url' => $this->agenteBaseUrl(),
                'agente' => $health['json'],
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON([
                'success' => false,
                'agente_configurado' => $this->agenteHabilitado(),
                'agente_online' => false,
                'mensaje' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Dispara ejecución en el agente (POST /run).
     */
    public function ejecutarReporte()
    {
        try {
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Active [gastoscobranza_agent].enabled=1 en config.ini y levante el servicio Node.',
                ]);
                return;
            }
            $health = $this->agenteRequest('GET', '/health', null, 8);
            if (!$health['success'] || !is_array($health['json']) || empty($health['json']['success'])) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'El agente no responde en ' . $this->agenteBaseUrl() . '.',
                ]);
                return;
            }
            $run = $this->agenteRequest('POST', '/run', new \stdClass(), 600);
            if (is_array($run['json'])) {
                $out = $run['json'];
                if (!array_key_exists('success', $out)) {
                    $out['success'] = $run['success'];
                }
                self::respuestaJSON($out);
                return;
            }
            $msg = substr((string)$run['raw'], 0, 200) ?: 'Error al invocar /run';
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $msg,
                'http_status' => $run['status'] ?? 0,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }
}
