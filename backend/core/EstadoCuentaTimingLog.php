<?php

namespace Core;

/**
 * Trazas de tiempo por consulta de estado de cuenta (POST index).
 *
 * Activar en el servidor (Apache SetEnv, .env cargado por PHP, o php.ini):
 *   ESTADO_CUENTA_TIMING=1
 *
 * Muestreo opcional (1 de cada N consultas):
 *   ESTADO_CUENTA_TIMING_SAMPLE=10
 *
 * Salida: backend/storage/logs/__SPARTA_SECRET_REDACTED___timing.log (carpeta en .gitignore).
 */
class EstadoCuentaTimingLog
{
    private static $rid = '';

    private static $idCredito = '-';

    private static $t0 = 0.0;

    private static $tLast = 0.0;

    private static $active = false;

    public static function start(string $idCredito): void
    {
        if (!self::shouldRun()) {
            return;
        }
        self::$active = true;
        self::$rid = bin2hex(random_bytes(4));
        self::$idCredito = $idCredito !== '' ? $idCredito : '-';
        self::$t0 = microtime(true);
        self::$tLast = self::$t0;
        self::writeLine('START', 0.0, 0.0);
    }

    public static function mark(string $step): void
    {
        if (!self::$active) {
            return;
        }
        $now = microtime(true);
        $dtMs = ($now - self::$tLast) * 1000.0;
        $cumMs = ($now - self::$t0) * 1000.0;
        self::$tLast = $now;
        self::writeLine($step, $dtMs, $cumMs);
    }

    public static function finish(string $step = 'END'): void
    {
        if (!self::$active) {
            return;
        }
        self::mark($step);
        self::$active = false;
    }

    private static function shouldRun(): bool
    {
        $v = getenv('ESTADO_CUENTA_TIMING');
        if ($v === false || $v === '' || $v === '0' || strtolower((string) $v) === 'false') {
            return false;
        }
        $sample = (int) getenv('ESTADO_CUENTA_TIMING_SAMPLE');
        if ($sample > 1) {
            try {
                if (random_int(1, $sample) !== 1) {
                    return false;
                }
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }

    private static function writeLine(string $step, float $dtMs, float $cumMs): void
    {
        $dir = dirname(__DIR__) . '/storage/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $file = $dir . '/__SPARTA_SECRET_REDACTED___timing.log';
        $ts = date('Y-m-d H:i:s');
        $stepSafe = preg_replace('/[\r\n\t]/', ' ', $step);
        $line = sprintf(
            "[%s] rid=%s id_credito=%s step=%s dt_ms=%.1f cum_ms=%.1f\n",
            $ts,
            self::$rid,
            self::$idCredito,
            $stepSafe,
            $dtMs,
            $cumMs
        );
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
