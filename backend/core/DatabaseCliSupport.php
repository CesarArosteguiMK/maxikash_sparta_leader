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
     * La consulta de documentos se consume con fetch y espera siempre JSON. Si la
     * conexion de respaldo AWS falla, el controlador debe poder responder el error
     * controlado en vez de interrumpir la respuesta con HTML 503.
     */
    public static function esEstadoCuentaDocumentoRequest(): bool
    {
        if (isset($_GET['url'])) {
            $u = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if ($u === 'estadocuenta/descargar') {
                return true;
            }
        }
        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        return (bool) preg_match('#/estadocuenta/descargar$#', $path);
    }

    /** Las consultas de rastreo son AJAX y siempre deben recibir JSON. */
    public static function esSabuesoRastreoJsonRequest(): bool
    {
        $acciones = [
            'sabueso/getdatoscredito',
            'sabueso/getubicacionescredito',
            'sabueso/getpuntosgeocredito',
        ];

        if (isset($_GET['url'])) {
            $ruta = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if (in_array($ruta, $acciones, true)) {
                return true;
            }
        }

        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        foreach ($acciones as $accion) {
            if (str_ends_with($path, '/' . $accion)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Las analiticas del modal de rastreo se consumen con fetch y su contrato es
     * siempre JSON. Una base remota no disponible debe lanzar una excepcion para
     * permitir que los modelos usen las demas fuentes, nunca imprimir HTML 503.
     */
    public static function esApiAnalyticsJsonRequest(): bool
    {
        if (isset($_GET['url'])) {
            $ruta = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if ((bool) preg_match('#^api/analytics/(?:spatial|payments|compliance)/[0-9]+$#', $ruta)) {
                return true;
            }
        }

        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        return (bool) preg_match('#(?:^|/)api/analytics/(?:spatial|payments|compliance)/[0-9]+$#', trim($path, '/'));
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

    /**
     * Tablero Asignación (fetch/XHR): si falla PDO, no debe imprimirse HTML «Sistema fuera de línea» ni exit;
     * se lanza excepción y Reporteria::getAsignacionTableroJson responde JSON.
     */
    public static function esReporteriaGetAsignacionTableroJsonRequest(): bool
    {
        if (isset($_GET['url'])) {
            $u = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if ($u === 'reporteria/getasignaciontablerojson' || $u === 'analitica/getasignaciontablerojson') {
                return true;
            }
        }
        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        // Ruta bajo subcarpeta (XAMPP: /proyecto/public/...) o raíz: debe coincidir al final, no solo "^/analitica/…"
        if (str_ends_with($path, '/analitica/getasignaciontablerojson') || str_ends_with($path, '/reporteria/getasignaciontablerojson')) {
            return true;
        }
        if ((bool) preg_match('#/(?:reporteria|analitica)/getasignaciontablerojson$#', $path)) {
            return true;
        }

        return (bool) preg_match('#.*/(?:reporteria|analitica)/getasignaciontablerojson$#', $path);
    }

    public static function esReporteriaCapitalHumanoJsonRequest(): bool
    {
        $rutas = [
            'reporteria/getusuarioscapitalhumano',
            'analitica/getusuarioscapitalhumano',
            'reporteria/getbajascapitalhumano',
            'analitica/getbajascapitalhumano',
            'reporteria/getfiltroscapitalhumano',
            'analitica/getfiltroscapitalhumano',
        ];

        if (isset($_GET['url'])) {
            $u = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if (in_array($u, $rutas, true)) {
                return true;
            }
        }

        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        foreach ($rutas as $ruta) {
            if (str_ends_with($path, '/' . $ruta)) {
                return true;
            }
        }

        return (bool) preg_match('#.*/(?:reporteria|analitica)/(?:getusuarioscapitalhumano|getbajascapitalhumano|getfiltroscapitalhumano)$#', $path);
    }

    public static function esCaphumDocumentosJsonRequest(): bool
    {
        $rutas = [
            'caphum/getresumendocumentoscolaborador',
            'caphum/getresumendocumentosrrhh',
            'caphum/getdocumentoscandidatolist',
            'caphum/validardocumentocandidato',
            'caphum/subirdocumentomanualcandidato',
            'caphum/eliminardocumentocandidato',
            'caphum/verificaractadocumentocandidato',
            'caphum/gettokendocumentoscandidato',
            'caphum/reactivartokendocumentoscandidato',
        ];

        if (isset($_GET['url'])) {
            $u = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if (in_array($u, $rutas, true)) {
                return true;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && str_starts_with($u, 'caphum/subirdocumentoscandidato/')) {
                return true;
            }
        }

        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        foreach ($rutas as $ruta) {
            if (str_ends_with($path, '/' . $ruta)) {
                return true;
            }
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (bool) preg_match('#.*/caphum/subirdocumentoscandidato/[^/]+$#', $path)) {
            return true;
        }

        return (bool) preg_match('#.*/caphum/(?:getresumendocumentoscolaborador|getresumendocumentosrrhh|getdocumentoscandidatolist|validardocumentocandidato|subirdocumentomanualcandidato|eliminardocumentocandidato|verificaractadocumentocandidato|gettokendocumentoscandidato|reactivartokendocumentoscandidato)$#', $path);
    }

    /**
     * La bandeja de Evidencias intenta sincronizar datos auxiliares desde Legacy
     * antes de devolver su lista. Si Legacy no responde, esa sincronización debe
     * degradarse sin interrumpir el JSON de la bandeja local.
     */
    public static function esAtencionClientesEvidenciasJsonRequest(): bool
    {
        if (isset($_GET['url'])) {
            $ruta = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if ($ruta === 'atencionclientes/obtenerrecibidos') {
                return true;
            }
        }

        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        return (bool) preg_match('#.*/atencionclientes/obtenerrecibidos$#', $path);
    }

    /**
     * Todos los endpoints de Leonidas se consumen mediante fetch y su contrato es
     * JSON (o NDJSON para voz en tiempo real). Una caida de una base remota debe
     * convertirse en excepcion para que el controlador responda en ese formato;
     * nunca debe imprimir la pagina HTML de "Sistema fuera de linea".
     */
    public static function esLeonidasJsonRequest(): bool
    {
        if (isset($_GET['url'])) {
            $ruta = strtolower(trim(str_replace('\\', '/', (string) $_GET['url']), '/'));
            if ($ruta === 'leonidas' || str_starts_with($ruta, 'leonidas/')) {
                return true;
            }
        }

        $path = strtolower((string) (parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ''));
        return (bool) preg_match('#(?:^|/)leonidas(?:/[^/]*)?$#', trim($path, '/'));
    }
}
