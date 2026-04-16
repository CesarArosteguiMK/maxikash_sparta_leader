<?php

namespace Controllers;

use Core\Controller;
use Models\GastosCobranzaEstadistica;

/**
 * Gastos Cobranza — agente HTTP + reporte_cobranza.py (iterativo).
 * Permisos: módulo web id 31 (rutas en public/index.php).
 */
class Gastoscobranza extends Controller
{
    /** POST /run puede tardar horas (miles de llamadas S2); antes 600s PHP cortaba y el navegador veía «Error al invocar /run». */
    private const AGENTE_RUN_TIMEOUT_SEC = 7200;

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

    /**
     * Libera el bloqueo de sesión para que otras peticiones del mismo usuario (p. ej. logAgente cada 2,5 s)
     * puedan ejecutarse mientras esta petición espera al agente Node (worker, carga lista negra, etc.).
     */
    private function liberarSesionParaPeticionLarga()
    {
        if (function_exists('session_status') && session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }

    /**
     * Ruta relativa permitida bajo reporte/ del agente (raíz o historico/<carpeta>/).
     */
    private function archivoExcelReporteAgenteValido(string $rel): bool
    {
        $rel = str_replace('\\', '/', trim($rel));
        if ($rel === '' || strpos($rel, '..') !== false) {
            return false;
        }
        if (preg_match('#^reporte_cobranza_[A-Za-z0-9._-]+\.xlsx$#i', $rel)) {
            return true;
        }
        if (preg_match('#^historico/[a-zA-Z0-9._-]+/reporte_cobranza_[A-Za-z0-9._-]+\.xlsx$#i', $rel)) {
            return true;
        }

        return false;
    }

    /** Evita que PHP corte la petición a los ~30 s mientras el agente corre reporte / worker (respuesta HTML en lugar de JSON). */
    private function extenderTiempoEjecucionParaAgente()
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        @ini_set('max_execution_time', '0');
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
                if (is_array($payload) || is_object($payload)) {
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
            if (is_array($payload) || is_object($payload)) {
                $opts['http']['content'] = json_encode($payload);
            } else {
                $opts['http']['content'] = (string)$payload;
            }
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
     * Vista principal Gastos Cobranza.
     */
    public function shell()
    {
        $this->set('titulo', 'Gastos Cobranza');
        $this->set('tituloShell', 'Gastos Cobranza');
        $this->set('gastosCobranzaAgenteUrl', $this->agenteBaseUrl());
        $this->set('gastosCobranzaAgenteHabilitado', $this->agenteHabilitado());
        $resumen = $this->shellCalcularResumenReportesVista();
        $this->set('reportes_esta_semana', $resumen['reportes_esta_semana']);
        $this->set('ultimo_reporte', $resumen['ultimo_reporte']);
        $this->set('reporte_automatico', $resumen['reporte_automatico']);
        self::render('shell_gastos_cobranza');
    }

    /**
     * Pantalla «Estadísticas Gastos Cobranza» — resumen de condonaciones (módulo web 40).
     * El detalle por registro está en Condonaciones → Historial condonaciones.
     */
    public function estadisticagc()
    {
        $this->set('titulo', 'Estadísticas Gastos Cobranza');
        $this->set('script', '');
        self::render('gastos_cobranza_estadistica');
    }

    /**
     * JSON único para el dashboard Estadísticas Gastos Cobranza (módulo 40).
     * POST JSON: serie_grupo (semana|mes), y opcional fecha_inicio/fecha_fin (Y-m-d).
     */
    public function getDashboardEstadistica()
    {
        header('Content-Type: application/json; charset=utf-8');
        $raw = file_get_contents('php://input');
        $body = json_decode($raw ?: '[]', true);
        if (!is_array($body)) {
            $body = [];
        }
        $periodo = (string) ($body['periodo'] ?? 'mes');
        $serieGrupo = (string) ($body['serie_grupo'] ?? 'semana');
        $fechaInicio = isset($body['fecha_inicio']) ? (string) $body['fecha_inicio'] : null;
        $fechaFin = isset($body['fecha_fin']) ? (string) $body['fecha_fin'] : null;
        $res = GastosCobranzaEstadistica::getDashboard($periodo, $serieGrupo, $fechaInicio, $fechaFin);
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** Igual que en la vista JS: nombre de archivo sin carpeta. */
    private function gcNombreBaseArchivoListado(string $nom): string
    {
        $s = str_replace('\\', '/', trim($nom));
        $i = strrpos($s, '/');

        return $i === false ? $s : substr($s, $i + 1);
    }

    /** Fecha en nombre reporte_cobranza_DD-MM-YYYY.xlsx → [y,m,d] calendario del reporte. */
    private function gcParseNombreReporteCobranza(string $nom): ?array
    {
        $base = $this->gcNombreBaseArchivoListado($nom);
        if (!preg_match('/^reporte_cobranza_(\d{2})-(\d{2})-(\d{4})\.xlsx$/i', $base, $m)) {
            return null;
        }

        return ['d' => (int) $m[1], 'm' => (int) $m[2], 'y' => (int) $m[3]];
    }

    /** {y,m,d} en calendario Ciudad de México desde ISO de modificación. */
    private function gcModificadoCdmxYmd(?string $iso): ?array
    {
        if ($iso === null || $iso === '') {
            return null;
        }
        try {
            $dt = new \DateTimeImmutable($iso);
        } catch (\Exception $e) {
            return null;
        }
        $tz = new \DateTimeZone('America/Mexico_City');
        $local = $dt->setTimezone($tz);

        return ['y' => (int) $local->format('Y'), 'm' => (int) $local->format('n'), 'd' => (int) $local->format('j')];
    }

    /** 0 = lunes … 6 = domingo (misma fórmula que shell_gastos_cobranza.js). */
    private function gcCdmxWeekdayMon0(int $y, int $m, int $d): int
    {
        $t = [0, 3, 2, 5, 0, 3, 5, 1, 4, 6, 2, 4];
        $Y = $m < 3 ? $y - 1 : $y;
        $wSun0 = ($Y + intdiv($Y, 4) - intdiv($Y, 100) + intdiv($Y, 400) + $t[$m - 1] + $d) % 7;

        return ($wSun0 + 6) % 7;
    }

    /** @return array{y:int,m:int,d:int} */
    private function gcAddDaysYmd(int $y, int $m, int $d, int $delta): array
    {
        $dt = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $y, $m, $d), new \DateTimeZone('UTC'));
        $dt = $dt->modify(($delta >= 0 ? '+' : '') . $delta . ' days');

        return ['y' => (int) $dt->format('Y'), 'm' => (int) $dt->format('n'), 'd' => (int) $dt->format('j')];
    }

    /** @return array{y:int,m:int,d:int} lunes de la semana civil CDMX. */
    private function gcLunesSemanaCdmx(int $y, int $m, int $d): array
    {
        $k = $this->gcCdmxWeekdayMon0($y, $m, $d);

        return $this->gcAddDaysYmd($y, $m, $d, -$k);
    }

    private function gcClaveLunesYmd(array $L): string
    {
        return sprintf('%04d-%02d-%02d', $L['y'], $L['m'], $L['d']);
    }

    private function gcClaveSemanaActualCdmx(): string
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $now = new \DateTimeImmutable('now', $tz);
        $h = ['y' => (int) $now->format('Y'), 'm' => (int) $now->format('n'), 'd' => (int) $now->format('j')];
        $L = $this->gcLunesSemanaCdmx($h['y'], $h['m'], $h['d']);

        return $this->gcClaveLunesYmd($L);
    }

    /** Fecha de referencia para agrupar por semana (nombre DD-MM-YYYY o mtime CDMX). */
    private function gcFechaReferenciaSemanaArchivo(array $a): ?array
    {
        $nom = (string) ($a['nombre'] ?? '');
        $pn = $this->gcParseNombreReporteCobranza($nom);
        if ($pn !== null) {
            return ['y' => $pn['y'], 'm' => $pn['m'], 'd' => $pn['d']];
        }
        $base = $this->gcNombreBaseArchivoListado($nom);
        if (preg_match('/^reporte_cobranza_/i', $base)) {
            $fm = $this->gcModificadoCdmxYmd(isset($a['modificado']) ? (string) $a['modificado'] : null);
            if ($fm !== null) {
                return $fm;
            }
        }

        return $this->gcModificadoCdmxYmd(isset($a['modificado']) ? (string) $a['modificado'] : null);
    }

    private function gcClaveLunesSemanaDeArchivo(array $a, string $fallbackClave): string
    {
        $f = $this->gcFechaReferenciaSemanaArchivo($a);
        if ($f === null) {
            return $fallbackClave;
        }
        $L = $this->gcLunesSemanaCdmx($f['y'], $f['m'], $f['d']);

        return $this->gcClaveLunesYmd($L);
    }

    /** @return list<array<string,mixed>> */
    private function shellObtenerArchivosReporteAgente(): array
    {
        if (!$this->agenteHabilitado()) {
            return [];
        }
        $req = $this->agenteRequest('GET', '/reportes', null, 12);
        if (!$req['success'] || !is_array($req['json']) || empty($req['json']['success'])) {
            return [];
        }
        $arch = $req['json']['archivos'] ?? [];

        return is_array($arch) ? $arch : [];
    }

    /** @return array<string,mixed>|null */
    private function shellObtenerHealthAgente(): ?array
    {
        if (!$this->agenteHabilitado()) {
            return null;
        }
        $req = $this->agenteRequest('GET', '/health', null, 10);
        if (!$req['success'] || !is_array($req['json']) || empty($req['json']['success'])) {
            return null;
        }

        return $req['json'];
    }

    private function shellFormatearUltimoReporteUtc(?string $iso): string
    {
        if ($iso === null || $iso === '') {
            return '—';
        }
        try {
            $dt = new \DateTimeImmutable($iso);
        } catch (\Exception $e) {
            return '—';
        }
        $utc = new \DateTimeZone('UTC');
        $dtUtc = $dt->setTimezone($utc);
        $nowUtc = new \DateTimeImmutable('now', $utc);
        $meses = [1 => 'ene', 2 => 'feb', 3 => 'mar', 4 => 'abr', 5 => 'may', 6 => 'jun', 7 => 'jul', 8 => 'ago', 9 => 'sep', 10 => 'oct', 11 => 'nov', 12 => 'dic'];
        $dia = $dtUtc->format('d');
        $nMes = (int) $dtUtc->format('n');
        $mes = $meses[$nMes] ?? $dtUtc->format('m');
        $hm = $dtUtc->format('H:i');
        if ($dtUtc->format('Y-m-d') === $nowUtc->format('Y-m-d')) {
            return 'Hoy ' . $hm . ' UTC';
        }

        return $dia . ' ' . $mes . ' ' . $hm . ' UTC';
    }

    /**
     * Resumen para la fila informativa del shell (GET /reportes + /health).
     *
     * @return array{reportes_esta_semana: string, ultimo_reporte: string, reporte_automatico: string}
     */
    private function shellCalcularResumenReportesVista(): array
    {
        if (!$this->agenteHabilitado()) {
            return [
                'reportes_esta_semana' => '—',
                'ultimo_reporte' => '—',
                'reporte_automatico' => '—',
            ];
        }
        $archivos = $this->shellObtenerArchivosReporteAgente();
        $claveActual = $this->gcClaveSemanaActualCdmx();
        $n = 0;
        /** Solo reportes de la semana operativa actual (CDMX): el “último” se reinicia cada semana. */
        $ultimoIsoSemana = null;
        $ultimoTsSemana = null;
        foreach ($archivos as $a) {
            if (!is_array($a)) {
                continue;
            }
            if ($this->gcClaveLunesSemanaDeArchivo($a, $claveActual) !== $claveActual) {
                continue;
            }
            ++$n;
            $iso = isset($a['modificado']) ? (string) $a['modificado'] : '';
            if ($iso === '') {
                continue;
            }
            try {
                $ts = (new \DateTimeImmutable($iso))->getTimestamp();
            } catch (\Exception $e) {
                continue;
            }
            if ($ultimoTsSemana === null || $ts > $ultimoTsSemana) {
                $ultimoTsSemana = $ts;
                $ultimoIsoSemana = $iso;
            }
        }
        $health = $this->shellObtenerHealthAgente();
        $autoTxt = '—';
        if ($health !== null && isset($health['auto_run_cdmx']) && is_array($health['auto_run_cdmx'])) {
            $arc = $health['auto_run_cdmx'];
            if (array_key_exists('enabled', $arc)) {
                $en = $arc['enabled'];
                $on = $en === true || $en === 1 || $en === '1' || strtolower((string) $en) === 'true';
                $autoTxt = $on ? 'Activo' : 'Inactivo';
            }
        }

        $ultimoTxt = '----';
        if ($n > 0) {
            $ultimoTxt = $this->shellFormatearUltimoReporteUtc($ultimoIsoSemana);
        }

        return [
            'reportes_esta_semana' => $n . ' generados',
            'ultimo_reporte' => $ultimoTxt,
            'reporte_automatico' => $autoTxt,
        ];
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
     * Activa o desactiva la generación automática del reporte (timer CDMX en el agente Node).
     * Persistencia: archivo logs/.auto_run_reporte_runtime.txt en la carpeta del agente (no modifica .env).
     */
    public function configurarAutoRunReporte()
    {
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!$this->agenteHabilitado()) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Agente deshabilitado en config.ini ([gastoscobranza_agent] enabled=0).',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            $raw = file_get_contents('php://input');
            $body = json_decode($raw ?: 'null', true);
            if (!is_array($body) || !array_key_exists('enabled', $body)) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'JSON inválido: se requiere { "enabled": true|false }.',
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            $en = $body['enabled'] === true || $body['enabled'] === 1
                || $body['enabled'] === '1' || strtolower((string)$body['enabled']) === 'true';
            $req = $this->agenteRequest('POST', '/auto-run-reporte', ['enabled' => $en], 25);
            if (!$req['success'] || !is_array($req['json']) || empty($req['json']['success'])) {
                $msg = is_array($req['json']) && !empty($req['json']['mensaje'])
                    ? (string)$req['json']['mensaje']
                    : ($req['error'] ?: ('HTTP ' . ($req['status'] ?? 0)));
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'No se pudo actualizar el agente: ' . $msg,
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            echo json_encode($req['json'], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Dispara ejecución en el agente (POST /run).
     */
    public function ejecutarReporte()
    {
        try {
            $this->extenderTiempoEjecucionParaAgente();
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
            $this->liberarSesionParaPeticionLarga();
            $run = $this->agenteRequest('POST', '/run', new \stdClass(), self::AGENTE_RUN_TIMEOUT_SEC);
            if (is_array($run['json'])) {
                $out = $run['json'];
                if (!array_key_exists('success', $out)) {
                    $out['success'] = $run['success'];
                }
                self::respuestaJSON($out);
                return;
            }
            $msg = '';
            if (!empty($run['error'])) {
                $msg = trim((string)$run['error']);
            }
            if ($msg === '') {
                $msg = substr((string)($run['raw'] ?? ''), 0, 240);
            }
            if ($msg === '') {
                $msg = 'Error al invocar /run (sin respuesta JSON del agente; revise timeout de red o que el servicio Node siga en ejecución).';
            }
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $msg,
                'http_status' => $run['status'] ?? 0,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Cola de log del agente (GET /logs) para panel en vista.
     */
    public function logAgente()
    {
        try {
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Agente deshabilitado en config.ini.',
                    'contenido' => '',
                ]);
                return;
            }
            $lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 120;
            $lines = max(20, min(400, $lines));
            $path = '/logs?lines=' . $lines;
            $req = $this->agenteRequest('GET', $path, null, 15);
            if ($req['success'] && is_array($req['json']) && !empty($req['json']['success'])) {
                self::respuestaJSON([
                    'success' => true,
                    'contenido' => (string)($req['json']['contenido'] ?? ''),
                    'archivo' => $req['json']['archivo'] ?? null,
                ]);
                return;
            }
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $req['error'] ?: ('HTTP ' . ($req['status'] ?? 0)),
                'contenido' => '',
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage(), 'contenido' => '']);
        }
    }

    /**
     * Vacía el log del agente en disco (POST /logs/clear).
     */
    public function vaciarLogAgente()
    {
        try {
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Agente deshabilitado en config.ini.',
                ]);
                return;
            }
            $req = $this->agenteRequest('POST', '/logs/clear', new \stdClass(), 15);
            if ($req['success'] && is_array($req['json']) && !empty($req['json']['success'])) {
                self::respuestaJSON([
                    'success' => true,
                    'mensaje' => (string)($req['json']['mensaje'] ?? 'Log vaciado.'),
                ]);
                return;
            }
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $req['error'] ?: ('HTTP ' . ($req['status'] ?? 0)),
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Lista archivos .xlsx en reporte/ del agente (GET /reportes).
     */
    public function listarReportes()
    {
        try {
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Agente deshabilitado.', 'archivos' => []]);
                return;
            }
            $req = $this->agenteRequest('GET', '/reportes', null, 15);
            if ($req['success'] && is_array($req['json']) && !empty($req['json']['success'])) {
                self::respuestaJSON([
                    'success' => true,
                    'archivos' => $req['json']['archivos'] ?? [],
                ]);
                return;
            }
            self::respuestaJSON([
                'success' => false,
                'mensaje' => $req['error'] ?: ('HTTP ' . ($req['status'] ?? 0)),
                'archivos' => [],
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage(), 'archivos' => []]);
        }
    }

    /**
     * Descarga un Excel desde reporte/ del agente (proxy al navegador).
     */
    public function descargarReporte()
    {
        $nombre = isset($_GET['nombre']) ? str_replace('\\', '/', trim((string)$_GET['nombre'])) : '';
        if ($nombre === '' || !$this->archivoExcelReporteAgenteValido($nombre)) {
            header('HTTP/1.0 400 Bad Request');
            echo 'Nombre de archivo inválido';
            exit;
        }
        if (!$this->agenteHabilitado()) {
            header('HTTP/1.0 503 Service Unavailable');
            echo 'Agente deshabilitado en config.ini.';
            exit;
        }
        $url = $this->agenteBaseUrl() . '/reportes/descargar?nombre=' . rawurlencode($nombre);
        $headers = ['Accept: */*'];
        $key = $this->agenteApiKey();
        if ($key !== '') {
            $headers[] = 'X-Api-Key: ' . $key;
        }
        if (!function_exists('curl_init')) {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'cURL no disponible';
            exit;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $bin = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($bin === false || $status < 200 || $status >= 300) {
            header('HTTP/1.0 502 Bad Gateway');
            echo $cerr !== '' ? $cerr : ('No se pudo descargar (HTTP ' . $status . ')');
            exit;
        }
        if (strpos($ctype, 'json') !== false) {
            header('HTTP/1.0 502 Bad Gateway');
            echo 'El agente devolvió error en lugar del archivo.';
            exit;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($nombre) . '"');
        header('Content-Length: ' . strlen($bin));
        echo $bin;
        exit;
    }

    /**
     * Descarga CSV generado por worker.php tras errores persistentes en la 2.ª pasada (proxy al agente).
     */
    public function descargarErroresReintento()
    {
        $nombre = isset($_GET['nombre']) ? (string)$_GET['nombre'] : '';
        if (
            $nombre === '' || $nombre !== basename($nombre)
            || !preg_match('/^ec_worker_errores_reintento_\d{8}_\d{6}\.csv$/', $nombre)
        ) {
            header('HTTP/1.0 400 Bad Request');
            echo 'Nombre de archivo inválido';
            exit;
        }
        if (!$this->agenteHabilitado()) {
            header('HTTP/1.0 503 Service Unavailable');
            echo 'Agente deshabilitado en config.ini.';
            exit;
        }
        $url = $this->agenteBaseUrl() . '/ec-uploads/descargar-errores-reintento?nombre=' . rawurlencode($nombre);
        $headers = ['Accept: text/csv, */*'];
        $key = $this->agenteApiKey();
        if ($key !== '') {
            $headers[] = 'X-Api-Key: ' . $key;
        }
        if (!function_exists('curl_init')) {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'cURL no disponible';
            exit;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $bin = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($bin === false || $status < 200 || $status >= 300) {
            header('HTTP/1.0 502 Bad Gateway');
            echo $cerr !== '' ? $cerr : ('No se pudo descargar (HTTP ' . $status . ')');
            exit;
        }
        if (strpos($ctype, 'json') !== false) {
            header('HTTP/1.0 502 Bad Gateway');
            echo 'El agente devolvió error en lugar del archivo.';
            exit;
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombre . '"');
        header('Content-Length: ' . strlen($bin));
        echo $bin;
        exit;
    }

    /**
     * Sube un .xlsx a reporte/ec-uploads del agente (para worker / enrich).
     */
    public function subirExcelEc()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Solo POST.']);
            return;
        }
        if (!isset($_FILES['archivo']) || !is_uploaded_file($_FILES['archivo']['tmp_name'] ?? '')) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Adjunte un archivo Excel (.xlsx).']);
            return;
        }
        $ext = strtolower((string)pathinfo((string)($_FILES['archivo']['name'] ?? ''), PATHINFO_EXTENSION));
        if ($ext !== 'xlsx') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Solo se acepta extensión .xlsx']);
            return;
        }
        if (!defined('RAIZ')) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Configuración RAIZ no disponible.']);
            return;
        }
        $dir = RAIZ . DIRECTORY_SEPARATOR . 'services' . DIRECTORY_SEPARATOR . 'gastos-cobranza-agent'
            . DIRECTORY_SEPARATOR . 'reporte' . DIRECTORY_SEPARATOR . 'ec-uploads';
        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No se pudo crear la carpeta ec-uploads.']);
            return;
        }
        $base = preg_replace('/[^a-zA-Z0-9._-]+/', '_', basename((string)$_FILES['archivo']['name']));
        if (strlen($base) > 180) {
            $base = substr($base, -180);
        }
        $nombre = 'ec_upload_' . date('Ymd_His') . '_' . $base;
        $dest = $dir . DIRECTORY_SEPARATOR . $nombre;
        if (!@move_uploaded_file($_FILES['archivo']['tmp_name'], $dest)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'No se pudo guardar el archivo.']);
            return;
        }
        self::respuestaJSON([
            'success' => true,
            'nombre' => $nombre,
            'mensaje' => 'Listo. Puede ejecutar worker o enrich desde el agente.',
        ]);
    }

    /**
     * Proxy a POST /ec-launcher/run del agente Node (worker.php o enrich_gc_excel.php).
     */
    public function ejecutarEcLauncher()
    {
        try {
            $this->extenderTiempoEjecucionParaAgente();
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Active [gastoscobranza_agent] en config.ini.']);
                return;
            }
            $raw = (string)file_get_contents('php://input');
            $body = json_decode($raw !== '' ? $raw : '{}', true);
            if (!is_array($body)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Cuerpo JSON inválido.']);
                return;
            }
            $nombreRaw = isset($body['nombre']) ? str_replace('\\', '/', trim((string)$body['nombre'])) : '';
            $tipo = isset($body['tipo']) ? strtolower(trim((string)$body['tipo'])) : '';
            $fechaCorte = isset($body['fechaCorte']) ? trim((string)$body['fechaCorte']) : '';
            $column = isset($body['column']) ? trim((string)$body['column']) : 'ID CREDITO';
            $omitir = isset($body['omitir']) ? (int)$body['omitir'] : 0;
            $soloColumnas = !empty($body['soloColumnas']);
            $origenCarpetaPrev = isset($body['origenCarpeta']) ? strtolower(trim((string)$body['origenCarpeta'])) : '';
            if ($nombreRaw === '' || substr(strtolower($nombreRaw), -5) !== '.xlsx') {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Indique el nombre del .xlsx subido previamente.']);
                return;
            }
            if ($origenCarpetaPrev === 'reporte') {
                if (!$this->archivoExcelReporteAgenteValido($nombreRaw)) {
                    self::respuestaJSON(['success' => false, 'mensaje' => 'Ruta del Excel en reporte/ no permitida.']);
                    return;
                }
                $nombre = $nombreRaw;
            } else {
                $nombre = basename($nombreRaw);
            }
            if ($tipo !== 'worker' && $tipo !== 'enrich') {
                self::respuestaJSON(['success' => false, 'mensaje' => 'El campo tipo debe ser worker o enrich.']);
                return;
            }
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCorte)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'fechaCorte debe ser YYYY-MM-DD.']);
                return;
            }
            $payload = [
                'tipo' => $tipo,
                'archivo' => $nombre,
                'fechaCorte' => $fechaCorte,
                'column' => $column !== '' ? $column : 'ID CREDITO',
                'omitir' => max(0, $omitir),
                'soloColumnas' => $soloColumnas,
            ];
            $traceId = isset($body['traceId']) ? trim((string)$body['traceId']) : '';
            if ($traceId !== '' && preg_match('/^[A-Za-z0-9._:-]{6,80}$/', $traceId)) {
                $payload['traceId'] = $traceId;
            }
            $origenCarpeta = isset($body['origenCarpeta']) ? strtolower(trim((string)$body['origenCarpeta'])) : '';
            if ($origenCarpeta === 'reporte') {
                $payload['origenCarpeta'] = 'reporte';
            }
            $health = $this->agenteRequest('GET', '/health', null, 10);
            if (!$health['success'] || !is_array($health['json']) || empty($health['json']['success'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'El agente Gastos Cobranza no responde.']);
                return;
            }
            if ($tipo === 'worker' && empty($health['json']['ec_worker_presente'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'En este equipo no está el worker EC en gastos-cobranza-agent/tools/ec-webhook-worker (worker.php).']);
                return;
            }
            if ($tipo === 'enrich' && empty($health['json']['ec_enrich_presente'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'En este equipo no está enrich en gastos-cobranza-agent/tools/ec-gc-excel-enrich.']);
                return;
            }
            $this->liberarSesionParaPeticionLarga();
            $run = $this->agenteRequest('POST', '/ec-launcher/run', $payload, 7200);
            if (is_array($run['json'])) {
                $out = $run['json'];
                if (!array_key_exists('success', $out)) {
                    $code = isset($out['codigo_salida']) ? (int)$out['codigo_salida'] : -1;
                    $out['success'] = $run['success'] && $code === 0;
                }
                self::respuestaJSON($out);
                return;
            }
            self::respuestaJSON([
                'success' => false,
                'mensaje' => substr((string)($run['raw'] ?? ''), 0, 400) ?: ('HTTP ' . ($run['status'] ?? 0)),
                'http_status' => $run['status'] ?? 0,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Proxy a POST /carga-verificacion-semana/run (Excel → cobranza_gc_verificacion_semana).
     */
    public function ejecutarCargaVerificacionSemana()
    {
        try {
            $this->extenderTiempoEjecucionParaAgente();
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Active [gastoscobranza_agent] en config.ini.']);
                return;
            }
            $raw = (string)file_get_contents('php://input');
            $body = json_decode($raw !== '' ? $raw : '{}', true);
            if (!is_array($body)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Cuerpo JSON inválido.']);
                return;
            }
            $nombreRaw = isset($body['archivo']) ? str_replace('\\', '/', trim((string)$body['archivo'])) : '';
            $origenCarga = isset($body['origenCarpeta']) ? strtolower(trim((string)$body['origenCarpeta'])) : '';
            if ($nombreRaw === '' || substr(strtolower($nombreRaw), -5) !== '.xlsx') {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'Indique el nombre del .xlsx (carpeta ec-uploads, o reporte/ si envía origenCarpeta=reporte).',
                ]);
                return;
            }
            if ($origenCarga === 'reporte') {
                if (!$this->archivoExcelReporteAgenteValido($nombreRaw)) {
                    self::respuestaJSON(['success' => false, 'mensaje' => 'Ruta del Excel en reporte/ no permitida.']);
                    return;
                }
                $nombre = $nombreRaw;
            } else {
                $nombre = basename($nombreRaw);
            }
            $inicioSemana = isset($body['inicioSemana']) ? trim((string)$body['inicioSemana']) : '';
            if ($inicioSemana !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicioSemana)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'inicioSemana debe ser YYYY-MM-DD o omitirse.']);
                return;
            }
            // Lista negra por Excel: siempre tipo_reporte = NULL en BD (no falta_aplicar). Requiere columna nullable.
            $payload = [
                'archivo' => $nombre,
                'dryRun' => !empty($body['dryRun']),
                'megaPhpDefaults' => !isset($body['megaPhpDefaults']) || !empty($body['megaPhpDefaults']),
                'tipoReporteNulo' => true,
            ];
            $traceId = isset($body['traceId']) ? trim((string)$body['traceId']) : '';
            if ($traceId !== '' && preg_match('/^[A-Za-z0-9._:-]{6,80}$/', $traceId)) {
                $payload['traceId'] = $traceId;
            }
            if ($origenCarga === 'reporte') {
                $payload['origenCarpeta'] = 'reporte';
            }
            if ($inicioSemana !== '') {
                $payload['inicioSemana'] = $inicioSemana;
            }
            if (isset($body['estatus']) && $body['estatus'] !== '' && $body['estatus'] !== null) {
                $payload['estatus'] = (int)$body['estatus'];
            }
            if (isset($body['mensaje']) && trim((string)$body['mensaje']) !== '') {
                $payload['mensaje'] = trim((string)$body['mensaje']);
            }
            if (isset($body['headerRow'])) {
                $hr = (int) $body['headerRow'];
                if ($hr >= 1 && $hr <= 200) {
                    $payload['headerRow'] = $hr;
                }
            }
            $health = $this->agenteRequest('GET', '/health', null, 10);
            if (!$health['success'] || !is_array($health['json']) || empty($health['json']['success'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'El agente Gastos Cobranza no responde.']);
                return;
            }
            if (empty($health['json']['script_carga_verificacion_semana'])) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'En el agente no está el script carga_cobranza_gc_verificacion_semana_desde_excel.py.',
                ]);
                return;
            }
            $this->liberarSesionParaPeticionLarga();
            $run = $this->agenteRequest('POST', '/carga-verificacion-semana/run', $payload, 7200);
            if (is_array($run['json'])) {
                $out = $run['json'];
                if (!array_key_exists('success', $out)) {
                    $code = isset($out['codigo_salida']) ? (int)$out['codigo_salida'] : -1;
                    $out['success'] = $run['success'] && $code === 0;
                }
                self::respuestaJSON($out);
                return;
            }
            self::respuestaJSON([
                'success' => false,
                'mensaje' => substr((string)($run['raw'] ?? ''), 0, 400) ?: ('HTTP ' . ($run['status'] ?? 0)),
                'http_status' => $run['status'] ?? 0,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Proxy a POST /descargo-estatus3/run (Excel + guía JSON desde cobranza_gc_verificacion_semana estatus=3).
     */
    public function ejecutarDescargoEstatus3()
    {
        try {
            $this->extenderTiempoEjecucionParaAgente();
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Active [gastoscobranza_agent] en config.ini.']);
                return;
            }
            $raw = (string)file_get_contents('php://input');
            $body = json_decode($raw !== '' ? $raw : '{}', true);
            if (!is_array($body)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Cuerpo JSON inválido.']);
                return;
            }
            $payload = [
                'megaPhpDefaults' => !isset($body['megaPhpDefaults']) || !empty($body['megaPhpDefaults']),
                'desdeCero' => !empty($body['desdeCero']),
                'sinActualizarGuia' => !empty($body['sinActualizarGuia']),
            ];
            $health = $this->agenteRequest('GET', '/health', null, 10);
            if (!$health['success'] || !is_array($health['json']) || empty($health['json']['success'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'El agente Gastos Cobranza no responde.']);
                return;
            }
            if (empty($health['json']['script_descargo_estatus3'])) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'En el agente no está el script descargo_cobranza_gc_estatus3.py.',
                ]);
                return;
            }
            $this->liberarSesionParaPeticionLarga();
            $run = $this->agenteRequest('POST', '/descargo-estatus3/run', $payload, 7200);
            if (is_array($run['json'])) {
                $out = $run['json'];
                if (!array_key_exists('success', $out)) {
                    $code = isset($out['codigo_salida']) ? (int)$out['codigo_salida'] : -1;
                    $out['success'] = $run['success'] && $code === 0;
                }
                self::respuestaJSON($out);
                return;
            }
            self::respuestaJSON([
                'success' => false,
                'mensaje' => substr((string)($run['raw'] ?? ''), 0, 400) ?: ('HTTP ' . ($run['status'] ?? 0)),
                'http_status' => $run['status'] ?? 0,
            ]);
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Ejecuta el descargo en el agente y devuelve el Excel al navegador (o JSON si error / sin filas nuevas).
     * Proxy a POST /descargo-estatus3/run-and-download.
     */
    public function descargoEstatus3EjecutarYDescargar()
    {
        try {
            $this->extenderTiempoEjecucionParaAgente();
            if (!$this->agenteHabilitado()) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Active [gastoscobranza_agent] en config.ini.']);
                return;
            }
            $raw = (string)file_get_contents('php://input');
            $body = json_decode($raw !== '' ? $raw : '{}', true);
            if (!is_array($body)) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'Cuerpo JSON inválido.']);
                return;
            }
            $payload = [
                'megaPhpDefaults' => !isset($body['megaPhpDefaults']) || !empty($body['megaPhpDefaults']),
                'desdeCero' => !empty($body['desdeCero']),
                'sinActualizarGuia' => !empty($body['sinActualizarGuia']),
            ];
            $health = $this->agenteRequest('GET', '/health', null, 10);
            if (!$health['success'] || !is_array($health['json']) || empty($health['json']['success'])) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'El agente Gastos Cobranza no responde.']);
                return;
            }
            if (empty($health['json']['script_descargo_estatus3'])) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => 'En el agente no está el script descargo_cobranza_gc_estatus3.py.',
                ]);
                return;
            }
            if (!function_exists('curl_init')) {
                self::respuestaJSON(['success' => false, 'mensaje' => 'cURL no disponible en el servidor PHP.']);
                return;
            }
            $this->liberarSesionParaPeticionLarga();
            $url = $this->agenteBaseUrl() . '/descargo-estatus3/run-and-download';
            $headers = ['Accept: */*', 'Content-Type: application/json'];
            $key = $this->agenteApiKey();
            if ($key !== '') {
                $headers[] = 'X-Api-Key: ' . $key;
            }
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 7200);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $bin = curl_exec($ch);
            $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $ctype = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $cerr = curl_error($ch);
            curl_close($ch);
            if ($bin === false) {
                self::respuestaJSON([
                    'success' => false,
                    'mensaje' => $cerr !== '' ? $cerr : 'Error de red al contactar al agente.',
                ]);
                return;
            }
            if ($status < 200 || $status >= 300) {
                header('Content-Type: application/json; charset=utf-8');
                echo $bin;
                exit;
            }
            $ctLower = strtolower($ctype);
            if (strpos($ctLower, 'json') !== false || (strlen($bin) > 0 && ($bin[0] === '{' || $bin[0] === '['))) {
                header('Content-Type: application/json; charset=utf-8');
                echo $bin;
                exit;
            }
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="descargo_estatus3.xlsx"');
            header('Content-Length: ' . strlen($bin));
            echo $bin;
            exit;
        } catch (\Exception $e) {
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage()]);
        }
    }

    /**
     * Descarga descargo_estatus3.xlsx o guia_descargo.json del agente (proxy).
     */
    public function descargarDescargoEstatus3()
    {
        $tipo = isset($_GET['tipo']) ? strtolower(trim((string)$_GET['tipo'])) : 'xlsx';
        if ($tipo !== 'xlsx' && $tipo !== 'guia') {
            header('HTTP/1.0 400 Bad Request');
            echo 'Parámetro tipo inválido (xlsx o guia).';
            exit;
        }
        if (!$this->agenteHabilitado()) {
            header('HTTP/1.0 503 Service Unavailable');
            echo 'Agente deshabilitado en config.ini.';
            exit;
        }
        $url = $this->agenteBaseUrl() . '/descargo-estatus3/descargar?tipo=' . rawurlencode($tipo);
        $headers = ['Accept: */*'];
        $key = $this->agenteApiKey();
        if ($key !== '') {
            $headers[] = 'X-Api-Key: ' . $key;
        }
        if (!function_exists('curl_init')) {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'cURL no disponible';
            exit;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $bin = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $ctype = (string)curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $cerr = curl_error($ch);
        curl_close($ch);
        if ($bin === false) {
            header('HTTP/1.0 502 Bad Gateway');
            echo $cerr !== '' ? $cerr : 'Error de red al descargar.';
            exit;
        }
        if ($status < 200 || $status >= 300) {
            header($status === 404 ? 'HTTP/1.0 404 Not Found' : 'HTTP/1.0 502 Bad Gateway');
            $j = json_decode($bin, true);
            echo is_array($j) && isset($j['mensaje']) ? (string)$j['mensaje'] : substr($bin, 0, 500);
            exit;
        }
        if ($tipo === 'guia') {
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="guia_descargo.json"');
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="descargo_estatus3.xlsx"');
        }
        header('Content-Length: ' . strlen($bin));
        echo $bin;
        exit;
    }
}
