<?php

/**
 * API REST para analíticas determinísticas (spatial, payments, compliance).
 * GET /api/analytics/spatial/{idCredito}?force=true
 * GET /api/analytics/payments/{idCredito}
 * GET /api/analytics/compliance/{idCredito}?gestorId=xyz
 * Cache 24h; ?force=true bypass. Audit en location_audit.log (tipo analytics).
 */

namespace Controllers;

use Core\Controller;
use Models\Ubicacion as UbicacionDAO;
use Models\Gestiones as GestionesDAO;
use Models\Empresa as EmpresaDAO;

require_once __DIR__ . '/../services/SpatialAnalyticsService.php';
require_once __DIR__ . '/../services/TemporalPaymentsService.php';
require_once __DIR__ . '/../services/GestorComplianceService.php';
require_once __DIR__ . '/../services/GeocodingService.php';

use Services\SpatialAnalyticsService;
use Services\TemporalPaymentsService;
use Services\GestorComplianceService;
use Services\GeocodingService;

class Api extends Controller
{
    private const CACHE_TTL = 86400; // 24h
    private const CACHE_DIR = __DIR__ . '/../storage/cache';
    private const AUDIT_LOG = __DIR__ . '/../storage/logs/location_audit.log';

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
        ];
    }

    private function buildPaymentsAnalytics(int $idCredito): array
    {
        $gestiones = GestionesDAO::getAllGestiones($idCredito, '');
        $gestiones = is_array($gestiones) ? $gestiones : [];
        $pagos = [];
        foreach ($gestiones as $i => $g) {
            $tipo = (string) ($g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '');
            if (stripos($tipo, 'Pago') !== false) {
                $f = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? null;
                if ($f) {
                    $ts = is_numeric($f) ? (int) $f : strtotime($f);
                    $pagos[] = [
                        'id' => $g['id'] ?? 'g' . $i,
                        'fecha_pago' => $ts ? date('c', $ts) : null,
                        'monto' => $g['monto'] ?? null,
                    ];
                }
            }
        }
        $temporal = new TemporalPaymentsService();
        return $temporal->analizarPagos($pagos);
    }

    private function buildComplianceAnalytics(int $idCredito, ?string $gestorId): array
    {
        $ubic = UbicacionDAO::getUbicacionesFiltradasPorIdCredito($idCredito);
        $direcciones = $ubic['direcciones_resumen'] ?? [];
        $ubicacionesUsuario = [];
        foreach ($direcciones as $i => $d) {
            $ubicacionesUsuario[] = [
                'id' => $d['id'] ?? 'u' . $i,
                'lat' => (float) ($d['lat'] ?? $d['latitud'] ?? 0),
                'lng' => (float) ($d['lng'] ?? $d['longitud'] ?? 0),
            ];
        }
        $eventosGestor = GestionesDAO::getEventosGestorPorCredito($idCredito, $gestorId);
        $compliance = new GestorComplianceService();
        $out = $compliance->verificarCercaniaGestor($eventosGestor, $ubicacionesUsuario);
        foreach ($out['detalles'] as &$d) {
            if (isset($d['timestamp']) && $d['timestamp'] !== null && is_numeric($d['timestamp'])) {
                $d['timestamp'] = date('c', (int) $d['timestamp']);
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
        $dir = dirname(self::AUDIT_LOG);
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
        @file_put_contents(self::AUDIT_LOG, $line, FILE_APPEND | LOCK_EX);
    }
}
