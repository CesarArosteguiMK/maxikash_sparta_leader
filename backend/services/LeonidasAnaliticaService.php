<?php

namespace Services;

/**
 * Read-only analytics gateway for Leonidas. All figures come from the same
 * deterministic models used by the Analitica screens; the LLM never computes
 * or rewrites operational metrics.
 */
final class LeonidasAnaliticaService
{
    /** @var array<string,callable> */
    private array $adapters;

    public function __construct(array $adapters = [])
    {
        $defaults = [
            'bucket_actual' => static fn(?string $corte): array => \Models\AvanceBucket::calcular($corte),
            'bucket_historico' => static fn(?string $semana, ?string $corte): array => \Models\AvanceBucket::calcularHistorico($semana, $corte),
            'bucket_estresado' => static fn(?string $corte): array => \Models\AvanceBucket::calcularEstresado($corte),
            'bucket_comparativo' => static fn(?string $corte, ?string $conciliacion): array => \Models\ComparativoCierreSemanal::calcular($corte, $conciliacion),
        ];
        $this->adapters = $adapters + $defaults;
    }

    public function resolver(string $mensaje, string $normalizado, array $contexto): ?array
    {
        if (!$this->esConsultaAnalitica($normalizado)) {
            return null;
        }

        if ($this->esPreguntaConceptual($normalizado)) {
            if (empty($contexto['permisos_agente']['analitica'])) {
                return $this->denegado('los módulos de Analítica');
            }
            return $this->explicar($normalizado, $contexto);
        }

        $comparativo = preg_match('/\b(comparativ[a-z]*|semana pasada|contra la semana|versus|vs)\b/u', $normalizado) === 1;
        $historico = preg_match('/\b(historic[a-z]*|historial|semana\s+\d{1,2}[\s-]+\d{4})\b/u', $normalizado) === 1;
        $estresado = preg_match('/\b(estresa|estresado|mas uno|\+1)\b/u', $normalizado) === 1;

        if ($comparativo) {
            if (empty($contexto['permisos_agente']['comparativas'])) {
                return $this->denegado('Comparativas de Bucket');
            }
        } else {
            if (empty($contexto['permisos_agente']['bucket'])) {
                return $this->denegado('Avance de Bucket');
            }
        }

        $corte = $this->extraerCorte($normalizado);
        try {
            if ($comparativo) {
                $conciliacion = preg_match('/\b(con conciliacion|conciliado)\b/u', $normalizado) === 1 ? 'con' : 'sin';
                $datos = ($this->adapters['bucket_comparativo'])($corte, $conciliacion);
                return $this->formatearComparativo($datos);
            }
            if ($historico) {
                $datos = ($this->adapters['bucket_historico'])($this->extraerSemana($normalizado), $corte);
                return $this->formatearAvance($datos, 'Histórico de Bucket');
            }
            if ($estresado) {
                $datos = ($this->adapters['bucket_estresado'])($corte);
                return $this->formatearAvance($datos, 'Bucket estresado (+1)');
            }

            $datos = ($this->adapters['bucket_actual'])($corte);
            return $this->formatearAvance($datos, 'Avance de Bucket');
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Throwable $e) {
            error_log('LeonidasAnaliticaService: ' . $e->getMessage());
            return $this->error('La fuente de Analítica no respondió correctamente. No se inventó ningún resultado; intenta nuevamente o revisa el servicio Segundómetro.');
        }
    }

    private function esConsultaAnalitica(string $mensaje): bool
    {
        return preg_match('/\b(bucket|buckets|morosidad|avance de cartera|comparativo semanal|segundometro)\b/u', $mensaje) === 1;
    }

    private function esPreguntaConceptual(string $mensaje): bool
    {
        return preg_match('/\b(que es|que significa|como funciona|explica|explicame|para que sirve|diferencia|como se calcula|duda)\b/u', $mensaje) === 1;
    }

    private function explicar(string $mensaje, array $contexto): array
    {
        if (preg_match('/\b(historic[a-z]*|historial)\b/u', $mensaje)) {
            $texto = 'El Histórico de Bucket reconstruye una semana cerrada y permite revisar cómo se distribuyeron los créditos entre el bucket inicial y el cierre ajustado. Sirve para auditar tendencias sin mezclar datos de la semana actual.';
        } elseif (preg_match('/\b(comparativ[a-z]*|semana pasada|versus|vs)\b/u', $mensaje)) {
            $texto = 'La Comparativa de Bucket enfrenta la semana actual contra la anterior usando el mismo corte. Compara cantidad de créditos y capital por rango de mora; así muestra si Current y 1-7 mejoran o empeoran de forma comparable.';
        } elseif (preg_match('/\b(estresa|mas uno|\+1)\b/u', $mensaje)) {
            $texto = 'El Bucket estresado simula un desplazamiento adicional de mora (+1) sobre el cierre actual. Es un escenario preventivo, no el estado real del crédito.';
        } else {
            $texto = 'Un bucket agrupa créditos por días de mora: Current está al corriente; después siguen 1-7, 8-14, 15-21, 22-30, 31-60, 61-90, 91-120 y 121+. Avance de Bucket cruza el rango inicial contra el cierre ajustado para contar cuántos mejoran, permanecen igual o empeoran.';
        }

        return [
            'mensaje' => $texto,
            'tipo' => 'analitica_explicacion',
            'fuente' => 'reglas_analitica_sparta',
        ];
    }

    private function formatearAvance(array $datos, string $titulo): array
    {
        if (empty($datos['success'])) {
            return $this->error((string) ($datos['message'] ?? 'No fue posible calcular el avance de Bucket.'));
        }
        $total = (int) ($datos['total'] ?? 0);
        $corte = (string) ($datos['corte'] ?? 'corte actual');
        $resumen = is_array($datos['resumen_cierre'] ?? null) ? $datos['resumen_cierre'] : [];
        $filas = [];
        $series = [];
        foreach ($resumen as $fila) {
            $bucket = $this->limpiarBucket((string) ($fila['bucket'] ?? 'Sin bucket'));
            $valor = (int) ($fila['valor'] ?? 0);
            $porcentaje = (float) ($fila['porcentaje'] ?? 0);
            $filas[] = ['nombre' => $bucket, 'estado' => number_format($valor) . ' créditos', 'detalle' => number_format($porcentaje, 2) . '%'];
            $series[] = ['etiqueta' => $bucket, 'valor' => $valor];
        }
        $indicadores = is_array($datos['indicadores'] ?? null) ? $datos['indicadores'] : [];
        $mensaje = sprintf('%s al corte %s: %s créditos analizados.', $titulo, $corte, number_format($total));
        if ($indicadores) {
            $mensaje .= sprintf(
                ' Mejoran: %s; permanecen igual: %s; empeoran: %s.',
                number_format((int) ($indicadores['mejoran'] ?? 0)),
                number_format((int) ($indicadores['igual'] ?? 0)),
                number_format((int) ($indicadores['empeoran'] ?? 0))
            );
        }
        if ($total === 0) {
            $mensaje .= ' La fuente respondió sin créditos para ese corte.';
        }

        return [
            'mensaje' => $mensaje,
            'tipo' => 'analitica_bucket',
            'fuente' => (string) ($datos['origen'] ?? 'tbl_segundometro_semana'),
            'reporte' => ['titulo' => $titulo . ' · ' . $corte, 'total' => $total, 'filas' => $filas],
            'grafica' => ['titulo' => 'Créditos por bucket de cierre', 'series' => $series],
            'metricas' => ['dataset' => 'avance_bucket_' . (string) ($datos['modo'] ?? 'actual'), 'total' => $total, 'corte' => $corte],
        ];
    }

    private function formatearComparativo(array $datos): array
    {
        if (empty($datos['success'])) {
            return $this->error((string) ($datos['message'] ?? 'No fue posible calcular la comparativa de Bucket.'));
        }
        $actual = $datos['creditos']['semana_actual'] ?? [];
        $pasada = $datos['creditos']['semana_pasada'] ?? [];
        $actualFilas = is_array($actual['filas'] ?? null) ? $actual['filas'] : [];
        $pasadaFilas = is_array($pasada['filas'] ?? null) ? $pasada['filas'] : [];
        $anteriorPorBucket = [];
        foreach ($pasadaFilas as $fila) {
            $anteriorPorBucket[(string) ($fila['bucket'] ?? '')] = (int) ($fila['valor'] ?? 0);
        }
        $filas = [];
        $series = [];
        foreach ($actualFilas as $fila) {
            $clave = (string) ($fila['bucket'] ?? '');
            $bucket = $this->limpiarBucket($clave);
            $valor = (int) ($fila['valor'] ?? 0);
            $anterior = (int) ($anteriorPorBucket[$clave] ?? 0);
            $diferencia = $valor - $anterior;
            $filas[] = [
                'nombre' => $bucket,
                'estado' => number_format($valor) . ' actual / ' . number_format($anterior) . ' anterior',
                'detalle' => 'Diferencia: ' . ($diferencia >= 0 ? '+' : '') . number_format($diferencia),
            ];
            $series[] = ['etiqueta' => $bucket, 'valor' => $valor];
        }
        $totalActual = (int) ($actual['total'] ?? 0);
        $totalPasada = (int) ($pasada['total'] ?? 0);
        $semanaActual = (string) ($datos['semana_actual'] ?? 'semana actual');
        $semanaPasada = (string) ($datos['semana_pasada'] ?? 'semana anterior');
        $mensaje = sprintf(
            'Comparativa %s contra %s al corte %s: %s créditos actuales frente a %s anteriores.',
            $semanaActual,
            $semanaPasada,
            (string) ($datos['corte'] ?? 'actual'),
            number_format($totalActual),
            number_format($totalPasada)
        );
        $advertencias = array_values(array_filter(array_map('strval', (array) ($datos['advertencias'] ?? []))));
        if ($advertencias) {
            $mensaje .= ' Advertencia: ' . implode(' ', $advertencias);
        }

        return [
            'mensaje' => $mensaje,
            'tipo' => 'analitica_bucket_comparativa',
            'fuente' => 'comparativo_cierre_semanal',
            'reporte' => ['titulo' => 'Comparativa semanal de Bucket', 'total' => $totalActual, 'filas' => $filas],
            'grafica' => ['titulo' => $semanaActual . ' por bucket', 'series' => $series],
            'metricas' => ['dataset' => 'comparativo_bucket_semanal', 'total' => $totalActual, 'total_anterior' => $totalPasada],
        ];
    }

    private function extraerCorte(string $mensaje): ?string
    {
        return preg_match('/\b(0?7:30|0?9:30|11:30|13:30|14:30|16:30|18:30|20:30|23:50)\b/u', $mensaje, $m)
            ? str_pad((string) $m[1], 5, '0', STR_PAD_LEFT)
            : null;
    }

    private function extraerSemana(string $mensaje): ?string
    {
        return preg_match('/\bsemana\s+(\d{1,2})[\s-]+(\d{4})\b/u', $mensaje, $m)
            ? 'Semana ' . (int) $m[1] . '-' . (int) $m[2]
            : null;
    }

    private function limpiarBucket(string $bucket): string
    {
        return trim((string) preg_replace('/^[a-z]\)\s*/i', '', $bucket));
    }

    private function error(string $mensaje): array
    {
        return ['mensaje' => $mensaje, 'tipo' => 'analitica_error', 'fuente' => 'analitica_sparta'];
    }

    private function denegado(string $modulo): array
    {
        return [
            'mensaje' => 'Tu perfil no tiene permiso para consultar ' . $modulo . '.',
            'tipo' => 'analitica_denegada',
            'fuente' => 'permisos_sparta',
        ];
    }
}
