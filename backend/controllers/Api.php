<?php

/**
 * API REST para analíticas determinísticas (spatial, payments, compliance).
 * GET /api/analytics/spatial/{idCredito}?force=true
 * GET /api/analytics/payments/{idCredito}
 * GET /api/analytics/compliance/{idCredito}?gestorId=xyz
 * Cache 24h; ?force=true bypass. Audit opcional con SPARTA_ENABLE_FILE_LOGS=1 en carpeta temporal.
 */

namespace Controllers;

use Core\Controller;
use Models\Ubicacion as UbicacionDAO;
use Models\Gestiones as GestionesDAO;
use Models\Empresa as EmpresaDAO;
use Models\OfertaCoordenada;
use Models\SegundometroDAO;

require_once __DIR__ . '/../services/SpatialAnalyticsService.php';
require_once __DIR__ . '/../services/TemporalPaymentsService.php';
require_once __DIR__ . '/../services/GestorComplianceService.php';
require_once __DIR__ . '/../services/GeocodingService.php';
require_once __DIR__ . '/../services/AnaliticaInterpretarService.php';
require_once __DIR__ . '/../services/AnalisisPresenter.php';

use Services\SpatialAnalyticsService;
use Services\TemporalPaymentsService;
use Services\GestorComplianceService;
use Services\GeocodingService;
use Services\AnaliticaInterpretarService;
use Services\AnalisisPresenter;

class Api extends Controller
{
    private const CACHE_TTL = 86400; // 24h
    private const CACHE_DIR = __DIR__ . '/../storage/cache';

    /**
     * GET /api/analytics/{type}/{idCredito}
     * type: spatial | payments | compliance
     * Query: force=true (bypass cache), gestorId= (solo compliance)
     */
    public function analytics(string $type = '', string $idCredito = '')
    {
        header('Content-Type: application/json; charset=utf-8');
        $idCredito = (int) $idCredito;
        $force = isset($_GET['force']) && ($_GET['force'] === 'true' || $_GET['force'] === '1');
        $gestorId = isset($_GET['gestorId']) ? trim((string) $_GET['gestorId']) : null;
        $type = strtolower($type);
        if (!in_array($type, ['spatial', 'payments', 'compliance'], true)) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Tipo de analítica no válido.', 'data' => null]);
            return;
        }
        if ($idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'ID de crédito requerido.', 'data' => null]);
            return;
        }
        $cacheKey = 'analytics:' . $type . ':' . $idCredito . ($gestorId !== null ? ':' . md5($gestorId) : '');
        $cacheHit = false;
        if (!$force) {
            $cached = $this->cacheGet($cacheKey);
            if ($cached !== null) {
                $cacheHit = true;
                $this->auditLog($type, $idCredito, ['gestorId' => $gestorId], true, $cached);
                self::respuestaJSON(['success' => true, 'data' => $cached, 'cache_hit' => true]);
                return;
            }
        }
        try {
            if ($type === 'spatial') {
                $data = $this->buildSpatialAnalytics($idCredito);
            } elseif ($type === 'payments') {
                $data = $this->buildPaymentsAnalytics($idCredito);
            } else {
                $data = $this->buildComplianceAnalytics($idCredito, $gestorId);
            }
        } catch (\Throwable $e) {
            $this->auditLog($type, $idCredito, ['gestorId' => $gestorId], false, ['error' => $e->getMessage()]);
            self::respuestaJSON(['success' => false, 'mensaje' => $e->getMessage(), 'data' => null]);
            return;
        }
        $this->cacheSet($cacheKey, $data);
        $this->auditLog($type, $idCredito, ['gestorId' => $gestorId], false, $data);
        self::respuestaJSON(['success' => true, 'data' => $data, 'cache_hit' => false]);
    }

    private function buildSpatialAnalytics(int $idCredito): array
    {
        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $direcciones = $ubic['direcciones_resumen'] ?? [];
        $domicilio = [];
        $ubicacionesUsuario = [];
        // Casa = domicilio megareporte (dirección que el usuario dio al registrarse). Geocodificar si es texto.
        $dirMegareporte = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $domicilioCompleto = ($dirMegareporte['success'] ?? false) && !empty($dirMegareporte['datos'][0]['Domicilio_Completo'])
            ? trim((string) $dirMegareporte['datos'][0]['Domicilio_Completo'])
            : '';
        if ($domicilioCompleto !== '') {
            $geocoding = new GeocodingService();
            $coordsMegareporte = $geocoding->getDomicilioCoordsForCredito($idCredito, $domicilioCompleto);
            if (!empty($coordsMegareporte)) {
                $domicilio = [
                    'id' => 'megareporte',
                    'lat' => (float) $coordsMegareporte['lat'],
                    'lng' => (float) $coordsMegareporte['lng'],
                    'label' => $coordsMegareporte['label'] ?? 'Domicilio megareporte',
                ];
            }
        }
        foreach ($direcciones as $i => $d) {
            $u = [
                'id' => $d['id'] ?? 'u' . $i,
                'lat' => (float) ($d['lat'] ?? $d['latitud'] ?? 0),
                'lng' => (float) ($d['lng'] ?? $d['longitud'] ?? 0),
                'label' => $d['texto'] ?? ('Ubicación ' . ($i + 1)),
                'visitas_count' => (int) ($d['cantidad_registros'] ?? 0),
                'ultima_fecha' => $d['ultima_fecha'] ?? null,
            ];
            $ubicacionesUsuario[] = $u;
            if (empty($domicilio)) {
                $domicilio = ['id' => $u['id'], 'lat' => $u['lat'], 'lng' => $u['lng'], 'label' => $u['label']];
            }
        }
        $eventosGPS = [];
        $idCliente = UbicacionDAO::getIdClientePorIdCredito($idCredito);
        if ($idCliente !== null) {
            $brutos = UbicacionDAO::getUbicacionesBrutasPorIdCliente($idCliente);
            foreach ($brutos ?? [] as $b) {
                $ts = $b['fecha'] ?? $b['fecha_creacion'] ?? null;
                if ($ts !== null) {
                    $ts = is_numeric($ts) ? (int) $ts : strtotime($ts);
                }
                if ($ts) {
                    $eventosGPS[] = [
                        'lat' => (float) ($b['latitud'] ?? 0),
                        'lng' => (float) ($b['longitud'] ?? 0),
                        'timestamp' => $ts,
                    ];
                }
            }
        }
        $spatial = new SpatialAnalyticsService();
        $distanciasCasa = $spatial->calcularDistanciasCasa($ubicacionesUsuario, $domicilio);
        $ultimaApertura = $spatial->ultimaUbicacionApp($eventosGPS, $domicilio, $ubicacionesUsuario);
        $aperturas5 = $spatial->aperturasUltimosDias($eventosGPS, 5, $ubicacionesUsuario, $domicilio);
        foreach ($distanciasCasa as &$row) {
            if (isset($row['ultima_fecha']) && $row['ultima_fecha'] && is_numeric($row['ultima_fecha']) === false) {
                $row['ultima_fecha'] = date('c', strtotime($row['ultima_fecha']));
            }
        }
        unset($row);
        if (!empty($ultimaApertura) && isset($ultimaApertura['timestamp'])) {
            $ultimaApertura['timestamp'] = date('c', (int) $ultimaApertura['timestamp']);
        }
        return [
            'distancias_a_casa' => $distanciasCasa,
            'ultima_apertura' => $ultimaApertura,
            'aperturas_ultimos_5_dias' => $aperturas5,
            'direccion_megareporte' => $domicilioCompleto !== '' ? $domicilioCompleto : null,
        ];
    }

    private function buildPaymentsAnalytics(int $idCredito): array
    {
        $pagosParaTemporal = $this->getPagosDesdeEstadoCuenta($idCredito);
        if (!empty($pagosParaTemporal)) {
            $temporal = new TemporalPaymentsService();
            $data = $temporal->analizarPagos($pagosParaTemporal);
        } else {
            $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
            $gestiones = is_array($gestiones) ? $gestiones : [];
            $pagos = [];
            foreach ($gestiones as $i => $g) {
                $tipo = (string) ($g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '');
                if (stripos($tipo, 'Pago') !== false) {
                    $f = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? null;
                    if ($f) {
                        $ts = is_numeric($f) ? (int) $f : strtotime($f);
                        $pagos[] = ['fecha' => $ts ? date('Y-m-d', $ts) : null];
                    }
                }
            }
            $temporal = new TemporalPaymentsService();
            $data = $temporal->analizarPagos($pagos);
        }
        $data['bucket_morosidad_counts'] = SegundometroDAO::getBucketMorosidadCounts($idCredito);
        return $data;
    }

    /**
     * Obtiene fechas de pago desde API estado de cuenta (misma fuente que Analizar IA).
     * @return array [] de ['fecha' => 'Y-m-d'] para TemporalPaymentsService::analizarPagos
     */
    private function getPagosDesdeEstadoCuenta(int $idCredito): array
    {
        try {
            $estadoCuentaCtrl = new \Controllers\EstadoCuenta();
            $res = $estadoCuentaCtrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'));
            if (empty($res['ok']) || empty($res['data']['datosPagos'])) {
                return [];
            }
            $pagos = $res['data']['datosPagos'];
            $out = [];
            foreach ($pagos as $p) {
                $f = $p['fechaDeposito'] ?? $p['fechaRegistro'] ?? $p['fechaValor'] ?? null;
                if ($f) {
                    $fechaNorm = is_numeric($f) ? date('Y-m-d', (int) $f) : date('Y-m-d', strtotime($f));
                    if ($fechaNorm) {
                        $out[] = ['fecha' => $fechaNorm];
                    }
                }
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function buildComplianceAnalytics(int $idCredito, ?string $gestorId): array
    {
        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $direcciones = $ubic['direcciones_resumen'] ?? [];
        $domicilio = [];
        $todasUbicaciones = [];
        $dirMegareporte = EmpresaDAO::getConsultaDireccionEstadoCuenta($idCredito);
        $domicilioCompleto = ($dirMegareporte['success'] ?? false) && !empty($dirMegareporte['datos'][0]['Domicilio_Completo'])
            ? trim((string) $dirMegareporte['datos'][0]['Domicilio_Completo'])
            : '';
        if ($domicilioCompleto !== '') {
            $geocoding = new GeocodingService();
            $coordsMegareporte = $geocoding->getDomicilioCoordsForCredito($idCredito, $domicilioCompleto);
            if (!empty($coordsMegareporte)) {
                $domicilio = [
                    'id' => 'megareporte',
                    'lat' => (float) $coordsMegareporte['lat'],
                    'lng' => (float) $coordsMegareporte['lng'],
                    'label' => $coordsMegareporte['label'] ?? 'Domicilio megareporte',
                ];
            }
        }
        if (empty($domicilio) && !empty($direcciones)) {
            $primera = $direcciones[0];
            $domicilio = [
                'id' => $primera['id'] ?? 'u0',
                'lat' => (float) ($primera['lat'] ?? $primera['latitud'] ?? 0),
                'lng' => (float) ($primera['lng'] ?? $primera['longitud'] ?? 0),
                'label' => $primera['texto'] ?? 'Domicilio',
            ];
        }
        if (!empty($domicilio) && isset($domicilio['lat']) && isset($domicilio['lng'])) {
            $todasUbicaciones[] = [
                'id' => $domicilio['id'] ?? 'megareporte',
                'lat' => (float) $domicilio['lat'],
                'lng' => (float) $domicilio['lng'],
                'label' => $domicilio['label'] ?? 'Domicilio megareporte',
            ];
        }
        foreach ($direcciones as $i => $d) {
            $orden = $d['orden'] ?? ($i + 1);
            $texto = trim((string) ($d['texto'] ?? ''));
            $label = $texto !== '' ? 'Ubicación ' . $orden . ': ' . $texto : 'Ubicación ' . $orden;
            $todasUbicaciones[] = [
                'id' => $d['id'] ?? 'u' . $i,
                'lat' => (float) ($d['lat'] ?? $d['latitud'] ?? 0),
                'lng' => (float) ($d['lng'] ?? $d['longitud'] ?? 0),
                'label' => $label,
            ];
        }
        $puntosGeo = OfertaCoordenada::getPorIdCredito($idCredito);
        if (!empty($puntosGeo)) {
            foreach ($puntosGeo as $i => $g) {
                $latG = (float) ($g['lat'] ?? $g['latitud'] ?? 0);
                $lngG = (float) ($g['lng'] ?? $g['longitud'] ?? 0);
                if ($latG !== 0.0 || $lngG !== 0.0) {
                    $donde = trim((string) ($g['donde_firma'] ?? ''));
                    $todasUbicaciones[] = [
                        'id' => 'geo_' . $i,
                        'lat' => $latG,
                        'lng' => $lngG,
                        'label' => $donde !== '' ? $donde : 'Donde firma ' . ($i + 1),
                    ];
                }
            }
        }
        $eventosGestor = GestionesDAO::getEventosGestorPorCredito($idCredito, $gestorId);
        $compliance = new GestorComplianceService();
        $out = $compliance->verificarCercaniaGestor($eventosGestor, $todasUbicaciones);
        $detalles = $out['detalles'];
        $detalles = array_slice($detalles, 0, 16);
        $out['detalles'] = array_values($detalles);
        foreach ($out['detalles'] as $i => &$d) {
            if (isset($d['timestamp']) && $d['timestamp'] !== null && is_numeric($d['timestamp'])) {
                $d['timestamp'] = date('c', (int) $d['timestamp']);
            }
            $d['gestor_nombre'] = isset($eventosGestor[$i]) ? trim((string) ($eventosGestor[$i]['usuario_asignado'] ?? '')) : '—';
            if ($d['gestor_nombre'] === '') {
                $d['gestor_nombre'] = trim((string) ($eventosGestor[$i]['codigo_gestor'] ?? '')) ?: '—';
            }
            if ($d['gestor_nombre'] === '') {
                $d['gestor_nombre'] = trim((string) ($eventosGestor[$i]['usuario'] ?? '')) ?: '—';
            }
            $e = $eventosGestor[$i] ?? [];
            $ccc = (string) ($e['medio_contactacion_ccc'] ?? '');
            $d['tipo_contacto'] = ($ccc === '0') ? 'Campo' : 'Telefónico';
            if (trim((string) ($d['ubicacion_label'] ?? '')) === '') {
                $d['ubicacion_label'] = $d['ubicacion_id'] ?? '—';
            }
        }
        unset($d);
        return $out;
    }

    private function cacheGet(string $key): ?array
    {
        $safe = preg_replace('/[^a-zA-Z0-9:_-]/', '_', $key);
        $path = self::CACHE_DIR . '/analytics_' . $safe . '.json';
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

    private function cacheSet(string $key, array $payload): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            @mkdir(self::CACHE_DIR, 0755, true);
        }
        $safe = preg_replace('/[^a-zA-Z0-9:_-]/', '_', $key);
        $path = self::CACHE_DIR . '/analytics_' . $safe . '.json';
        $data = [
            'expires' => time() + self::CACHE_TTL,
            'payload' => $payload,
        ];
        @file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    private function auditLog(string $endpoint, int $idCredito, array $params, bool $cacheHit, $summary): void
    {
        if ((string) getenv('SPARTA_ENABLE_FILE_LOGS') !== '1') {
            return;
        }
        $logPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta___SPARTA_SECRET_REDACTED___php_logs' . DIRECTORY_SEPARATOR . 'location_audit.log';
        $dir = dirname($logPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $summaryShort = [];
        if (is_array($summary)) {
            if (isset($summary['total_aperturas'])) {
                $summaryShort['total_aperturas'] = $summary['total_aperturas'];
            }
            if (isset($summary['total_pagos'])) {
                $summaryShort['total_pagos'] = $summary['total_pagos'];
            }
            if (isset($summary['porcentaje_cumplimiento'])) {
                $summaryShort['porcentaje_cumplimiento'] = $summary['porcentaje_cumplimiento'];
            }
            if (isset($summary['error'])) {
                $summaryShort['error'] = substr($summary['error'], 0, 200);
            }
        }
        $line = json_encode([
            'type' => 'analytics',
            'endpoint' => $endpoint,
            'id_credito' => $idCredito,
            'params' => $params,
            'cache_hit' => $cacheHit,
            'timestamp' => date('c'),
            'summary' => $summaryShort,
        ], JSON_UNESCAPED_UNICODE) . "\n";
        @file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * GET /api/analitica/interpretar?idCredito=123&idTicket=0
     * O POST con body JSON: { analitica_espacial, analitica_pagos, analitica_gestiones, metadata }.
     * Devuelve informe estructurado (one_line_summary, sections, predictions, next_steps, etc.).
     */
    public function analitica(string $action = '')
    {
        header('Content-Type: application/json; charset=utf-8');
        $action = strtolower(trim($action));
        if ($action !== 'interpretar') {
            self::respuestaJSON(['success' => false, 'mensaje' => 'Acción no válida. Use: analitica/interpretar', 'data' => null]);
            return;
        }

        $idCredito = (int) ($_GET['idCredito'] ?? $_GET['id_credito'] ?? 0);
        $idTicket = (int) ($_GET['idTicket'] ?? $_GET['id_ticket'] ?? 0);
        $input = null;

        $raw = file_get_contents('php://input');
        if ($raw !== '' && $raw !== false) {
            $body = json_decode($raw, true);
            if (is_array($body) && isset($body['metadata'])) {
                $input = $body;
                $idCredito = (int) ($body['metadata']['idCredito'] ?? $idCredito);
            }
        }

        if ($input === null && $idCredito < 1) {
            self::respuestaJSON(['success' => false, 'mensaje' => 'idCredito requerido (query o metadata en body).', 'data' => null]);
            return;
        }

        if ($input === null) {
            $input = $this->buildInputFromCredito($idCredito, $idTicket);
        }

        $tz = new \DateTimeZone('America/Mexico_City');
        if (empty($input['metadata']['fecha_actual'])) {
            $input['metadata'] = array_merge($input['metadata'] ?? [], [
                'idCredito' => $idCredito,
                'idTicket' => $idTicket,
                'fecha_actual' => (new \DateTime('now', $tz))->format('c'),
                'timezone' => 'America/Mexico_City',
            ]);
        }

        $sabueso = new Sabueso();
        $llmFn = function (string $systemPrompt, string $userPrompt, int $maxTokens) use ($sabueso) {
            return $sabueso->callGemini($systemPrompt, [['text' => $userPrompt]], $maxTokens);
        };

        $service = new AnaliticaInterpretarService();
        $result = $service->interpretar($input, $llmFn);

        if (!($result['success'] ?? false)) {
            self::respuestaJSON(['success' => false, 'mensaje' => $result['mensaje'] ?? 'Error al interpretar.', 'data' => null]);
            return;
        }
        $data = $result['data'];
        $presenter = new AnalisisPresenter();
        $presentation = $presenter->present($data);
        $response = [
            'success' => true,
            'data' => $data,
            'presentation' => $presentation,
            'cache_hit' => $result['cache_hit'] ?? false,
            'from_llm' => $result['from_llm'] ?? false,
        ];
        if (!empty($result['validation_error'])) {
            $response['validation_error'] = $result['validation_error'];
        }
        self::respuestaJSON($response);
    }

    private function buildInputFromCredito(int $idCredito, int $idTicket): array
    {
        $spatial = $this->buildSpatialAnalytics($idCredito);
        $payments = $this->buildPaymentsAnalytics($idCredito);
        $compliance = $this->buildComplianceAnalytics($idCredito, null);

        $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
        $gestiones = is_array($gestiones) ? $gestiones : [];
        $lastPaymentDate = null;
        foreach ($gestiones as $g) {
            $tipo = (string) ($g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '');
            if (stripos($tipo, 'Pago') !== false) {
                $f = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? null;
                if ($f) {
                    $ts = is_numeric($f) ? (int) $f : strtotime($f);
                    if ($ts && ($lastPaymentDate === null || $ts > strtotime($lastPaymentDate))) {
                        $lastPaymentDate = date('c', $ts);
                    }
                }
            }
        }
        $ultimaGestion = $gestiones[0] ?? [];
        $promesaPago = $ultimaGestion['promesa_pago'] ?? null;
        $diasMora = isset($ultimaGestion['pagos_vencidos']) ? (int) $ultimaGestion['pagos_vencidos'] : null;
        $totalDeuda = $ultimaGestion['deuda_total'] ?? null;

        $analiticaPagos = [
            'last_payment_date' => $lastPaymentDate,
            'estado_actual' => null,
            'dias_mora' => $diasMora,
            'promesa_pago' => $promesaPago,
            'monto_prometido' => null,
            'total_deuda' => $totalDeuda,
            'total_pagos' => $payments['total_pagos'] ?? 0,
        ];

        $tz = new \DateTimeZone('America/Mexico_City');
        return [
            'analitica_espacial' => $spatial,
            'analitica_pagos' => $analiticaPagos,
            'analitica_gestiones' => $compliance,
            'metadata' => [
                'idCredito' => $idCredito,
                'idTicket' => $idTicket,
                'fecha_actual' => (new \DateTime('now', $tz))->format('c'),
                'timezone' => 'America/Mexico_City',
            ],
        ];
    }
}
