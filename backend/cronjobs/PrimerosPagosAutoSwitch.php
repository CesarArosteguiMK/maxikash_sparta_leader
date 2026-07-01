<?php

/**
 * Interruptor persistente en el servidor para el envío automático
 * de "Primeros pagos — Lunes de cierre".
 *
 * El archivo vive en storage/runtime/primeros_pagos/ (no va a git). Si no existe,
 * el envío automático queda DESACTIVADO hasta que alguien lo active en el menú.
 */
class PrimerosPagosAutoSwitch
{
    public static function path(): string
    {
        return dirname(__DIR__) . '/storage/runtime/primeros_pagos/primeros_pagos_auto_switch.json';
    }

    private static function legacyPath(): string
    {
        return __DIR__ . '/logs/primeros_pagos_auto_switch.json';
    }

    /**
     * @return array{enabled:bool, updated_at:?string}
     */
    public static function getState(): array
    {
        $path = self::path();
        if (!is_file($path) && is_file(self::legacyPath())) {
            $legacy = self::legacyPath();
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            @copy($legacy, $path);
        }
        if (!is_file($path)) {
            return ['enabled' => false, 'updated_at' => null];
        }
        $raw = @file_get_contents($path);
        $json = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($json)) {
            return ['enabled' => false, 'updated_at' => null];
        }
        if (!array_key_exists('enabled', $json)) {
            return ['enabled' => false, 'updated_at' => $json['updated_at'] ?? null];
        }
        return [
            'enabled' => (bool)$json['enabled'],
            'updated_at' => isset($json['updated_at']) ? (string)$json['updated_at'] : null,
        ];
    }

    public static function isEnabled(): bool
    {
        return self::getState()['enabled'];
    }

    /**
     * @return array{enabled:bool, updated_at:string}
     */
    public static function setEnabled(bool $enabled): array
    {
        $dir = dirname(self::path());
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $updatedAt = date('c');
        $payload = [
            'enabled' => $enabled,
            'updated_at' => $updatedAt,
            'timezone' => 'America/Mexico_City',
        ];
        @file_put_contents(
            self::path(),
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
        return ['enabled' => $enabled, 'updated_at' => $updatedAt];
    }
}
