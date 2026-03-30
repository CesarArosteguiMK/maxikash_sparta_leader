<?php

namespace Core;

/**
 * En CLI (worker, cron) no debe imprimirse HTML de "Sistema fuera de línea" ni hacer exit;
 * se lanza excepción para que el caller registre el error y siga (p. ej. siguiente crédito).
 */
final class DatabaseCliSupport
{
    public static function isCli(): bool
    {
        $s = \PHP_SAPI;

        return $s === 'cli' || $s === 'phpdbg';
    }
}
