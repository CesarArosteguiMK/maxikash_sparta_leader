<?php

namespace Services;

use Core\DatabaseSegundometro;
use Models\Convenios;
use Models\DiagnosticoBucket;

/**
 * Read-only evidence aggregator for an individual Bucket diagnosis.
 * A secondary source failure is reported explicitly and never replaces facts
 * obtained from the remaining sources.
 */
final class LeonidasBucketDiagnosticService
{
    /** @return array<string,mixed> */
    public function analizar(array $criterios): array
    {
        $diagnostico = DiagnosticoBucket::analizar($criterios);
        if (($diagnostico['modo'] ?? '') !== 'credito' || empty($diagnostico['encontrado'])) {
            return $diagnostico;
        }

        $credito = (int) ($diagnostico['id_credito'] ?? 0);
        $s2 = $this->consultarS2($credito);
        $condonaciones = $this->consultarCondonaciones($credito);
        $convenios = $this->consultarConvenios($credito);
        $movimientos = $this->combinarMovimientos($s2, $condonaciones, $convenios);

        $diagnostico['s2'] = $s2;
        $diagnostico['condonaciones'] = $condonaciones;
        $diagnostico['convenios'] = $convenios;
        $diagnostico['movimientos'] = $movimientos;
        $diagnostico['fuentes_estado'] = [
            'bucket_sparta' => 'disponible',
            's2' => (string) ($s2['estado'] ?? 'no_disponible'),
            'condonaciones' => (string) ($condonaciones['estado'] ?? 'no_disponible'),
            'convenios_reestructuras' => (string) ($convenios['estado'] ?? 'no_disponible'),
        ];
        $diagnostico['conclusion'] = $this->construirConclusion($diagnostico);

        return $diagnostico;
    }

    /** @return array<string,mixed> */
    private function consultarS2(int $credito): array
    {
        try {
            $datos = (new LeonidasS2Service())->consultarCredito($credito);
            return ['estado' => 'disponible'] + $datos;
        } catch (\Throwable $e) {
            error_log('Leonidas Bucket S2 credito ' . $credito . ': ' . $e->getMessage());
            return [
                'estado' => 'no_disponible',
                'fuente' => 's2_estado_cuenta',
                'error' => $this->mensajeFuente($e),
            ];
        }
    }

    /** @return array<string,mixed> */
    private function consultarCondonaciones(int $credito): array
    {
        try {
            $db = new DatabaseSegundometro();
            $filas = $db->queryAll(
                "SELECT cc.id_condonacion,
                        cc.comentario,
                        cc.total_condonado,
                        cc.created_at,
                        cc.usuario,
                        COUNT(ccd.id) AS total_detalles
                 FROM condonaciones_cobranza cc
                 LEFT JOIN condonaciones_cobranza_detalle ccd
                   ON ccd.id_condonacion = cc.id_condonacion
                 WHERE cc.id_credito = :id_credito
                 GROUP BY cc.id_condonacion, cc.comentario, cc.total_condonado,
                          cc.created_at, cc.usuario
                 ORDER BY cc.created_at DESC
                 LIMIT 20",
                ['id_credito' => $credito]
            );

            return [
                'estado' => $filas === [] ? 'sin_movimientos' : 'disponible',
                'fuente' => 'segundometro.condonaciones_cobranza',
                'total' => count($filas),
                'movimientos' => $filas,
                'consultado_at' => $this->ahora(),
            ];
        } catch (\Throwable $e) {
            error_log('Leonidas Bucket condonaciones credito ' . $credito . ': ' . $e->getMessage());
            return [
                'estado' => 'no_disponible',
                'fuente' => 'segundometro.condonaciones_cobranza',
                'error' => $this->mensajeFuente($e),
            ];
        }
    }

    /** @return array<string,mixed> */
    private function consultarConvenios(int $credito): array
    {
        try {
            $respuesta = Convenios::obtenerReporteIndividualConvenio(0, $credito);
            if (empty($respuesta['success'])) {
                $mensaje = trim((string) ($respuesta['mensaje'] ?? 'Convenio no encontrado.'));
                if (stripos($mensaje, 'no encontrado') !== false) {
                    return [
                        'estado' => 'sin_movimientos',
                        'fuente' => 'sparta.convenio_cliente',
                        'total' => 0,
                        'movimientos' => [],
                        'consultado_at' => $this->ahora(),
                    ];
                }
                throw new \RuntimeException($mensaje);
            }

            $datos = is_array($respuesta['datos'] ?? null) ? $respuesta['datos'] : [];
            $bitacora = array_values(array_filter((array) ($datos['bitacora'] ?? []), 'is_array'));
            return [
                'estado' => 'disponible',
                'fuente' => 'sparta.convenio_cliente',
                'total' => count($bitacora),
                'convenio' => is_array($datos['convenio'] ?? null) ? $datos['convenio'] : [],
                'movimientos' => $bitacora,
                'consultado_at' => (string) ($datos['actualizado_at'] ?? $this->ahora()),
            ];
        } catch (\Throwable $e) {
            error_log('Leonidas Bucket convenios credito ' . $credito . ': ' . $e->getMessage());
            return [
                'estado' => 'no_disponible',
                'fuente' => 'sparta.convenio_cliente',
                'error' => $this->mensajeFuente($e),
            ];
        }
    }

    /** @return list<array<string,mixed>> */
    private function combinarMovimientos(array $s2, array $condonaciones, array $convenios): array
    {
        $movimientos = [];
        foreach ((array) ($s2['pagos'] ?? []) as $pago) {
            if (!is_array($pago)) {
                continue;
            }
            $monto = $pago['monto'] ?? null;
            $movimientos[] = [
                'fecha' => (string) ($pago['fecha'] ?? ''),
                'tipo' => 'pago_s2',
                'titulo' => 'Pago aplicado en S2',
                'detalle' => is_numeric($monto) ? '$' . number_format((float) $monto, 2) : 'Monto no informado',
                'fuente' => 'S2',
            ];
        }
        foreach ((array) ($condonaciones['movimientos'] ?? []) as $fila) {
            if (!is_array($fila)) {
                continue;
            }
            $monto = $fila['total_condonado'] ?? null;
            $detalle = is_numeric($monto) ? '$' . number_format((float) $monto, 2) : 'Monto no informado';
            if (trim((string) ($fila['comentario'] ?? '')) !== '') {
                $detalle .= ' - ' . trim((string) $fila['comentario']);
            }
            $movimientos[] = [
                'fecha' => (string) ($fila['created_at'] ?? ''),
                'tipo' => 'condonacion',
                'titulo' => 'Condonacion registrada',
                'detalle' => $detalle,
                'fuente' => 'Sparta / Segundometro',
            ];
        }
        foreach ((array) ($convenios['movimientos'] ?? []) as $fila) {
            if (!is_array($fila)) {
                continue;
            }
            $movimientos[] = [
                'fecha' => (string) ($fila['fecha'] ?? ''),
                'tipo' => (string) ($fila['tipo'] ?? 'convenio'),
                'titulo' => (string) ($fila['titulo'] ?? 'Movimiento de convenio'),
                'detalle' => (string) ($fila['detalle'] ?? ''),
                'fuente' => 'Sparta / Convenios',
            ];
        }

        usort($movimientos, static fn(array $a, array $b): int => strcmp((string) ($b['fecha'] ?? ''), (string) ($a['fecha'] ?? '')));
        return array_slice($movimientos, 0, 20);
    }

    /** @return array<string,mixed> */
    private function construirConclusion(array $datos): array
    {
        $vistas = array_values(array_filter((array) ($datos['vistas'] ?? []), 'is_array'));
        $buckets = [];
        foreach ($vistas as $vista) {
            $bucket = trim((string) ($vista['bucket'] ?? ''));
            if ($bucket !== '') {
                $buckets[$bucket] = true;
            }
        }

        $fechaFuente = trim((string) ($datos['fecha_hora_fuente'] ?? ''));
        $pagoPosterior = null;
        $corteTs = $fechaFuente !== '' ? strtotime($fechaFuente) : false;
        if ($corteTs) {
            foreach ((array) ($datos['s2']['pagos'] ?? []) as $pago) {
                if (!is_array($pago)) {
                    continue;
                }
                $pagoTs = strtotime((string) ($pago['fecha'] ?? ''));
                if ($pagoTs && $pagoTs > $corteTs) {
                    $pagoPosterior = $pago;
                    break;
                }
            }
        }

        if (count($buckets) <= 1) {
            return [
                'nivel' => 'comprobado',
                'texto' => 'Las vistas revisadas coinciden en la clasificacion del credito para la evidencia disponible.',
            ];
        }
        if ($pagoPosterior !== null) {
            return [
                'nivel' => 'inferencia_fuerte',
                'texto' => sprintf(
                    'La diferencia es compatible con un desfase de corte: S2 registra un pago el %s, posterior a la fotografia de Bucket del %s. Esto explica una mejora posterior, pero debe compararse con la hora exacta de actualizacion de cada pantalla antes de atribuirle toda la diferencia.',
                    (string) ($pagoPosterior['fecha'] ?? 'fecha no informada'),
                    $fechaFuente
                ),
            ];
        }

        return [
            'nivel' => 'reglas_distintas',
            'texto' => 'La discrepancia comprobada proviene de reglas o fotografias distintas: las vistas no usan simultaneamente el mismo bucket base, dias de mora, cierre, filtros y conciliacion. No se encontro un pago posterior suficiente para atribuirle por si solo la diferencia.',
        ];
    }

    private function mensajeFuente(\Throwable $e): string
    {
        $mensaje = preg_replace('/\s+/', ' ', trim($e->getMessage())) ?: 'Fuente no disponible.';
        return mb_substr($mensaje, 0, 260, 'UTF-8');
    }

    private function ahora(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
    }
}
