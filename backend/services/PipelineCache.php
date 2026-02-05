<?php

/**
 * Cache por hash del input del motor (24h TTL).
 * Almacena respuestas IA (interpretación + verificación) para evitar llamadas repetidas.
 */

namespace Services;

class PipelineCache
{
    private string $cacheDir;
    private int $ttlSeconds;

    public function __construct(?string $cacheDir = null, int $ttlSeconds = 86400)
    {
        $this->cacheDir   = $cacheDir ?? (__DIR__ . '/../storage/cache');
        $this->ttlSeconds = $ttlSeconds;
    }

    public static function hashInput(array $datosParaMotor): string
    {
        $normalized = [
            'pagos_count' => (int) ($datosParaMotor['pagos_count'] ?? 0),
            'ubicaciones'  => array_map(function ($u) {
                return [
                    'id' => $u['id'] ?? null,
                    'etiqueta' => $u['etiqueta'] ?? $u['texto'] ?? '',
                    'cantidad_registros' => (int) ($u['cantidad_registros'] ?? 0),
                    'ultima_fecha' => $u['ultima_fecha'] ?? '',
                ];
            }, $datosParaMotor['ubicaciones'] ?? []),
            'gestiones' => array_map(function ($g) {
                return [
                    'id' => $g['id'] ?? null,
                    'fecha' => $g['fecha'] ?? $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '',
                    'tipo' => $g['tipo'] ?? '',
                ];
            }, $datosParaMotor['gestiones'] ?? []),
        ];
        ksort($normalized);
        return md5(json_encode($normalized, JSON_UNESCAPED_UNICODE));
    }

    public function get(string $key): ?array
    {
        $path = $this->path($key);
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
        return $data['payload'] ?? null;
    }

    public function set(string $key, array $payload): void
    {
        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0755, true);
        }
        $path = $this->path($key);
        $data = [
            'expires' => time() + $this->ttlSeconds,
            'payload' => $payload,
        ];
        @file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function path(string $key): string
    {
        return $this->cacheDir . '/pipe_' . preg_replace('/[^a-f0-9]/', '', $key) . '.json';
    }
}
