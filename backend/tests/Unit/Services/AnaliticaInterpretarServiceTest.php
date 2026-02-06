<?php

/**
 * Tests: interpretación de analíticas (cliente sin GPS, promesa vencida, baja eficacia gestores).
 */

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3) . '/services/AnaliticaInterpretarService.php';

class AnaliticaInterpretarServiceTest extends TestCase
{
    private function getService(): \Services\AnaliticaInterpretarService
    {
        return new \Services\AnaliticaInterpretarService();
    }

    private function inputBase(): array
    {
        $tz = new DateTimeZone('America/Mexico_City');
        return [
            'analitica_espacial' => [],
            'analitica_pagos' => [],
            'analitica_gestiones' => [],
            'metadata' => [
                'idCredito' => 999,
                'idTicket' => 0,
                'fecha_actual' => (new DateTime('now', $tz))->format('c'),
                'timezone' => 'America/Mexico_City',
            ],
        ];
    }

    /**
     * Cliente sin GPS: analitica_espacial vacía -> missing_data incluye analitica_espacial y salida con fallback.
     */
    public function testClienteSinGps(): void
    {
        $svc = $this->getService();
        $input = $this->inputBase();
        $input['analitica_espacial'] = [];
        $input['analitica_pagos'] = ['total_pagos' => 0];
        $input['analitica_gestiones'] = [];

        $result = $svc->interpretar($input, null);

        $this->assertTrue($result['success']);
        $this->assertIsArray($result['data']);
        $data = $result['data'];
        $this->assertArrayHasKey('one_line_summary', $data);
        $this->assertArrayHasKey('missing_data', $data);
        $this->assertContains('analitica_espacial', $data['missing_data']);
        $this->assertArrayHasKey('overall_confidence', $data);
        $this->assertGreaterThanOrEqual(0, $data['overall_confidence']);
        $this->assertLessThanOrEqual(1, $data['overall_confidence']);
        $this->assertArrayHasKey('predictions', $data);
        $this->assertArrayHasKey('sections', $data);
    }

    /**
     * Promesa vencida: last_payment_days > 60 y promesa_pago en el pasado -> regla riesgo_alto_pago.
     */
    public function testPromesaVencida(): void
    {
        $svc = $this->getService();
        $input = $this->inputBase();
        $input['analitica_espacial'] = [];
        $fechaActual = (new DateTime('now', new DateTimeZone('America/Mexico_City')))->format('c');
        $hace70Dias = (new DateTime('-70 days', new DateTimeZone('America/Mexico_City')))->format('c');
        $promesaPasada = (new DateTime('-5 days', new DateTimeZone('America/Mexico_City')))->format('Y-m-d');
        $input['analitica_pagos'] = [
            'last_payment_date' => $hace70Dias,
            'estado_actual' => null,
            'dias_mora' => 70,
            'promesa_pago' => $promesaPasada,
            'monto_prometido' => null,
            'total_deuda' => 1000,
            'total_pagos' => 1,
        ];
        $input['analitica_gestiones'] = [];
        $input['metadata']['fecha_actual'] = $fechaActual;

        $result = $svc->interpretar($input, null);

        $this->assertTrue($result['success']);
        $data = $result['data'];
        $this->assertArrayHasKey('predictions', $data);
        $labels = array_column($data['predictions'], 'label');
        $evidenceRefs = $data['evidence_references'] ?? [];
        $tieneRiesgoPago = false;
        foreach ($data['predictions'] as $p) {
            if (stripos($p['label'], 'riesgo') !== false || stripos($p['label'], 'pago') !== false) {
                $tieneRiesgoPago = true;
                break;
            }
        }
        $tieneRiesgoPago = $tieneRiesgoPago || in_array('rule:riesgo_alto_pago', $evidenceRefs, true);
        $this->assertTrue($tieneRiesgoPago, 'Se esperaba riesgo alto de pago (regla riesgo_alto_pago o prediction relacionada). evidence_references=' . json_encode($evidenceRefs));
    }

    /**
     * Baja eficacia gestores: gestor_response_rate < 30% -> regla baja_eficacia_gestores.
     */
    public function testBajaEficaciaGestores(): void
    {
        $svc = $this->getService();
        $input = $this->inputBase();
        $input['analitica_espacial'] = [];
        $input['analitica_pagos'] = ['total_pagos' => 0];
        $fecha = (new DateTime('now', new DateTimeZone('America/Mexico_City')))->format('c');
        $input['analitica_gestiones'] = [
            'porcentaje_cumplimiento' => 15,
            'detalles' => [
                ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => false],
                ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => false],
                ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => false],
                ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => true],
                ['gestor_nombre' => 'Gestor A', 'timestamp' => $fecha, 'cerca' => false],
            ],
        ];

        $result = $svc->interpretar($input, null);

        $this->assertTrue($result['success']);
        $data = $result['data'];
        $evidenceRefs = $data['evidence_references'] ?? [];
        $labels = array_column($data['predictions'], 'label');
        $tieneBajaEficacia = in_array('rule:baja_eficacia_gestores', $evidenceRefs, true);
        foreach ($data['predictions'] as $p) {
            if (stripos($p['label'], 'eficacia') !== false || stripos($p['label'], 'gestor') !== false) {
                $tieneBajaEficacia = true;
                break;
            }
        }
        $this->assertTrue($tieneBajaEficacia, 'Se esperaba baja eficacia de gestores. evidence_references=' . json_encode($evidenceRefs));
    }

    /**
     * Schema de salida: todas las claves requeridas presentes.
     */
    public function testSchemaSalida(): void
    {
        $svc = $this->getService();
        $input = $this->inputBase();
        $result = $svc->interpretar($input, null);

        $this->assertTrue($result['success']);
        $data = $result['data'];
        $this->assertArrayHasKey('one_line_summary', $data);
        $this->assertArrayHasKey('sections', $data);
        $this->assertArrayHasKey('predictions', $data);
        $this->assertArrayHasKey('next_steps', $data);
        $this->assertArrayHasKey('recommended_messages', $data);
        $this->assertArrayHasKey('missing_data', $data);
        $this->assertArrayHasKey('overall_confidence', $data);
        $this->assertArrayHasKey('evidence_references', $data);
        $this->assertIsArray($data['sections']);
        $this->assertArrayHasKey('cliente', $data['sections']);
        $this->assertArrayHasKey('gestores', $data['sections']);
        $this->assertArrayHasKey('pagos', $data['sections']);
    }
}
