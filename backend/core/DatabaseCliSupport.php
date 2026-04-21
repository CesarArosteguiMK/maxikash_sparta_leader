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

    /**
     * Petición a EstadoCuenta::validarCredito (AJAX/fetch).
     * Si __SPARTA_SECRET_REDACTED__ no conecta, conviene excepción capturada por el modelo → JSON;
     * no HTML + exit() que rompe response.json() en el navegador.
     */
    public static function esEstadoCuentaValidarCreditoRequest(): bool
    {
        if (isset($_GET['url'])) {
            $u = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if ($u === 'estadocuenta/validarcredito') {
                return true;
            }
        }
        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        return (bool) preg_match('#/estadocuenta/validarcredito$#', $path);
    }

    /**
     * Histórico de gestiones (POST/GET): varias conexiones MySQL remotas.
     * Si falla PDO, no debe imprimirse HTML + exit (rompe el flujo); se lanza excepción y el modelo la captura.
     */
    public static function esGestionesSeguimientoRequest(): bool
    {
        if (isset($_GET['url'])) {
            $u = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if ($u === 'gestiones/seguimiento') {
                return true;
            }
        }
        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        return (bool) preg_match('#/gestiones/seguimiento$#', $path);
    }
}
