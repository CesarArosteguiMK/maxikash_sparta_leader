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
            'segundometro' => static fn(?string $fecha): array => \Models\SegundometroComparativaSemanal::calcular($fecha),
            'primeros_pagos' => static fn(int $semanas): array => \Models\PrimerosPagosHistoricoSegundometro::resumenUltimasNSemanas($semanas),
            'gastos_cobranza' => static fn(string $periodo, string $grupo, ?string $inicio, ?string $fin): array => \Models\GastosCobranzaEstadistica::getDashboard($periodo, $grupo, $inicio, $fin),
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

        if ($this->esGastosCobranza($normalizado)) {
            if (empty($contexto['permisos_agente']['gastos_cobranza'])) {
                return $this->denegado('Gastos de Cobranza');
            }
            return $this->resolverGastosCobranza($normalizado);
        }

        if ($this->esPrimerosPagos($normalizado)) {
            if (empty($contexto['permisos_agente']['primeros_pagos'])) {
                return $this->denegado('Primeros Pagos');
            }
            return $this->resolverPrimerosPagos($normalizado);
        }

        if ($this->esSegundometro($normalizado) && !$this->esBucket($normalizado)) {
            if (empty($contexto['permisos_agente']['segundometro'])) {
                return $this->denegado('Segundometro');
            }
            return $this->resolverSegundometro($normalizado);
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
        return preg_match('/\b(bucket|buckets|morosidad|avance de cartera|comparativo semanal|segundometro|primeros pagos|primer pago|gastos? de cobranza|recuperado|condonado|cobranza por hora|pagaron puntual)\b/u', $mensaje) === 1;
    }

    private function esBucket(string $mensaje): bool
    {
        return preg_match('/\b(bucket|buckets|morosidad|avance de cartera)\b/u', $mensaje) === 1;
    }

    private function esSegundometro(string $mensaje): bool
    {
        return preg_match('/\b(segundometro|cobranza por hora|pagaron puntual|cobrado hoy|creditos cobrados)\b/u', $mensaje) === 1;
    }

    private function esPrimerosPagos(string $mensaje): bool
    {
        return preg_match('/\b(primeros pagos|primer pago|primer vencimiento|pendientes de primer pago)\b/u', $mensaje) === 1;
    }

    private function esGastosCobranza(string $mensaje): bool
    {
        return preg_match('/\b(gastos? de cobranza|gasto cobranza|cargos de cobranza|monto recuperado|monto condonado|tasa de condonacion)\b/u', $mensaje) === 1;
    }

    private function esPreguntaConceptual(string $mensaje): bool
    {
        return preg_match('/\b(que es|que significa|como funciona|explica|explicame|para que sirve|diferencia|como se calcula|duda)\b/u', $mensaje) === 1;
    }

    private function explicar(string $mensaje, array $contexto): array
    {
        if ($this->esGastosCobranza($mensaje)) {
            $texto = 'Gastos de Cobranza muestra los cargos generados, recuperados, pendientes y condonados en un periodo. Leonidas puede comparar sus montos, porcentajes y tendencia por semana o mes usando la misma fuente del tablero.';
        } elseif ($this->esPrimerosPagos($mensaje)) {
            $texto = 'Primeros Pagos sigue los creditos desde su nacimiento hasta el primer vencimiento. Permite medir cuantos nacieron Current o 1-7, cuantos llegaron Current al corte y cuantos siguen pendientes de su primer pago.';
        } elseif ($this->esSegundometro($mensaje) && !$this->esBucket($mensaje)) {
            $texto = 'El Segundometro compara el avance intradia de cobranza por horarios de corte contra semanas anteriores. Muestra creditos y monto cobrado; no equivale por si solo a puntualidad contractual, por lo que Leonidas identifica claramente que metrica esta respondiendo.';
        } elseif (preg_match('/\b(historic[a-z]*|historial)\b/u', $mensaje)) {
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

    private function resolverSegundometro(string $mensaje): array
    {
        try {
            $fecha = $this->extraerFecha($mensaje);
            $datos = ($this->adapters['segundometro'])($fecha);
            $filasOrigen = is_array($datos['datos'] ?? null) ? $datos['datos'] : [];
            if ($filasOrigen === []) {
                return $this->error('Segundometro respondio sin cortes disponibles para la fecha solicitada.');
            }

            $corteSolicitado = $this->extraerCorte($mensaje);
            $seleccion = end($filasOrigen);
            if ($corteSolicitado !== null) {
                $claveCorte = str_replace(':', '_', $corteSolicitado);
                foreach ($filasOrigen as $fila) {
                    if (($fila['hora'] ?? null) === $claveCorte) {
                        $seleccion = $fila;
                        break;
                    }
                }
            }
            $hora = str_replace('_', ':', (string) ($seleccion['hora'] ?? 'ultimo corte'));
            $creditos = (int) ($seleccion['creditos_actual'] ?? 0);
            $cobrado = (float) ($seleccion['cobrado_actual'] ?? 0);
            $filas = [];
            $series = [];
            foreach ($filasOrigen as $fila) {
                $h = str_replace('_', ':', (string) ($fila['hora'] ?? ''));
                $c = (int) ($fila['creditos_actual'] ?? 0);
                $m = (float) ($fila['cobrado_actual'] ?? 0);
                $filas[] = ['nombre' => $h, 'estado' => number_format($c) . ' creditos', 'detalle' => '$' . number_format($m, 2) . ' cobrado'];
                $series[] = ['etiqueta' => $h, 'valor' => $c];
            }
            $mensajeRespuesta = sprintf(
                'Segundometro del %s al corte %s: %s creditos y $%s cobrados.',
                (string) ($datos['fecha_referencia'] ?? 'hoy'),
                $hora,
                number_format($creditos),
                number_format($cobrado, 2)
            );
            if (preg_match('/\b(puntual|puntuales)\b/u', $mensaje)) {
                $mensajeRespuesta .= ' Esta es la medicion operativa del corte; no la presento como puntualidad contractual sin una regla adicional de vencimiento.';
            }
            return [
                'mensaje' => $mensajeRespuesta,
                'tipo' => 'analitica_segundometro',
                'fuente' => 'segundometro_comparativa_semanal',
                'reporte' => ['titulo' => 'Segundometro · ' . ($datos['fecha_referencia'] ?? ''), 'total' => $creditos, 'filas' => $filas],
                'grafica' => ['titulo' => 'Creditos por corte', 'series' => $series],
                'metricas' => ['dataset' => 'segundometro', 'total' => $creditos, 'cobrado' => $cobrado, 'corte' => $hora],
            ];
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Throwable $e) {
            error_log('LeonidasAnaliticaService segundometro: ' . $e->getMessage());
            return $this->error('No fue posible consultar Segundometro. La fuente operativa no respondio correctamente.');
        }
    }

    private function resolverPrimerosPagos(string $mensaje): array
    {
        try {
            $semanasSolicitadas = $this->extraerNumeroSemanas($mensaje);
            $resultado = ($this->adapters['primeros_pagos'])($semanasSolicitadas);
            if (empty($resultado['success'])) {
                return $this->error((string) ($resultado['mensaje'] ?? 'No hay informacion historica de primeros pagos.'));
            }
            $semanas = (array) (($resultado['datos']['semanas'] ?? []));
            $filas = [];
            $series = [];
            $total = 0;
            $pendientes = 0;
            foreach ($semanas as $semana) {
                if (empty($semana['disponible'])) {
                    continue;
                }
                $etiqueta = (string) ($semana['semana'] ?? 'Semana');
                $cantidad = (int) ($semana['total'] ?? 0);
                $pendiente = (int) ($semana['corte']['pendientes_primeros_pagos'] ?? 0);
                $current = (int) ($semana['corte']['current_al_corte'] ?? 0);
                $total += $cantidad;
                $pendientes += $pendiente;
                $filas[] = ['nombre' => $etiqueta, 'estado' => number_format($cantidad) . ' creditos', 'detalle' => number_format($current) . ' Current / ' . number_format($pendiente) . ' pendientes'];
                $series[] = ['etiqueta' => $etiqueta, 'valor' => $pendiente];
            }
            return [
                'mensaje' => sprintf('Primeros Pagos de las ultimas %d semanas disponibles: %s creditos revisados y %s pendientes al corte.', $semanasSolicitadas, number_format($total), number_format($pendientes)),
                'tipo' => 'analitica_primeros_pagos',
                'fuente' => 'primeros_pagos_historico_segundometro',
                'reporte' => ['titulo' => 'Historico de Primeros Pagos', 'total' => $total, 'filas' => $filas],
                'grafica' => ['titulo' => 'Pendientes de primer pago por semana', 'series' => $series],
                'metricas' => ['dataset' => 'primeros_pagos', 'total' => $total, 'pendientes' => $pendientes],
            ];
        } catch (\Throwable $e) {
            error_log('LeonidasAnaliticaService primeros pagos: ' . $e->getMessage());
            return $this->error('No fue posible consultar el historico de Primeros Pagos.');
        }
    }

    private function resolverGastosCobranza(string $mensaje): array
    {
        try {
            [$periodo, $grupo, $inicio, $fin] = $this->extraerPeriodoGastos($mensaje);
            $resultado = ($this->adapters['gastos_cobranza'])($periodo, $grupo, $inicio, $fin);
            if (empty($resultado['success'])) {
                return $this->error((string) ($resultado['error'] ?? 'No fue posible calcular Gastos de Cobranza.'));
            }
            $datos = (array) ($resultado['datos'] ?? []);
            $kpis = (array) ($datos['kpis'] ?? []);
            $generado = (array) ($kpis['total_generado'] ?? []);
            $recuperado = (array) ($kpis['recuperado'] ?? []);
            $pendiente = (array) ($kpis['pendiente'] ?? []);
            $condonado = (array) ($kpis['condonado'] ?? []);
            $filas = [
                $this->filaMonetaria('Generado', $generado),
                $this->filaMonetaria('Recuperado', $recuperado),
                $this->filaMonetaria('Pendiente', $pendiente),
                $this->filaMonetaria('Condonado', $condonado),
            ];
            $series = [];
            foreach ($filas as $fila) {
                $series[] = ['etiqueta' => $fila['nombre'], 'valor' => (float) ($fila['_valor'] ?? 0)];
                unset($fila['_valor']);
            }
            foreach ($filas as &$fila) {
                unset($fila['_valor']);
            }
            unset($fila);
            return [
                'mensaje' => sprintf(
                    'Gastos de Cobranza (%s): $%s generados, $%s recuperados, $%s pendientes y $%s condonados.',
                    (string) ($datos['periodo_label'] ?? $periodo),
                    number_format((float) ($generado['monto'] ?? 0), 2),
                    number_format((float) ($recuperado['monto'] ?? 0), 2),
                    number_format((float) ($pendiente['monto'] ?? 0), 2),
                    number_format((float) ($condonado['monto'] ?? 0), 2)
                ),
                'tipo' => 'analitica_gastos_cobranza',
                'fuente' => 'gastos_cobranza_estadistica',
                'reporte' => ['titulo' => 'Gastos de Cobranza · ' . ($datos['periodo_label'] ?? $periodo), 'total' => (int) ($generado['count'] ?? 0), 'filas' => $filas],
                'grafica' => ['titulo' => 'Distribucion monetaria', 'series' => $series],
                'metricas' => ['dataset' => 'gastos_cobranza', 'total' => (int) ($generado['count'] ?? 0), 'periodo' => $periodo],
            ];
        } catch (\Throwable $e) {
            error_log('LeonidasAnaliticaService gastos cobranza: ' . $e->getMessage());
            return $this->error('No fue posible consultar Gastos de Cobranza.');
        }
    }

    private function filaMonetaria(string $nombre, array $kpi): array
    {
        return [
            'nombre' => $nombre,
            'estado' => '$' . number_format((float) ($kpi['monto'] ?? 0), 2),
            'detalle' => number_format((float) ($kpi['pct'] ?? 0), 2) . '% · ' . number_format((int) ($kpi['count'] ?? 0)) . ' registros',
            '_valor' => (float) ($kpi['monto'] ?? 0),
        ];
    }

    private function extraerFecha(string $mensaje): ?string
    {
        return preg_match('/\b(20\d{2}-\d{2}-\d{2})\b/u', $mensaje, $m) ? $m[1] : null;
    }

    private function extraerNumeroSemanas(string $mensaje): int
    {
        if (preg_match('/\b(?:ultimas?\s+)?(\d{1,2})\s+semanas?\b/u', $mensaje, $m)) {
            return max(1, min(12, (int) $m[1]));
        }
        return 5;
    }

    /** @return array{0:string,1:string,2:?string,3:?string} */
    private function extraerPeriodoGastos(string $mensaje): array
    {
        $periodo = preg_match('/\b(esta semana|semana actual|hoy)\b/u', $mensaje) ? 'semana'
            : (preg_match('/\b(trimestre|trimestral)\b/u', $mensaje) ? 'trimestre'
                : (preg_match('/\b(anio|anual|este ano)\b/u', $mensaje) ? 'anio' : 'mes'));
        $grupo = preg_match('/\b(por mes|mensual)\b/u', $mensaje) ? 'mes' : 'semana';
        preg_match_all('/\b20\d{2}-\d{2}-\d{2}\b/u', $mensaje, $fechas);
        $inicio = $fechas[0][0] ?? null;
        $fin = $fechas[0][1] ?? null;
        return [$periodo, $grupo, $inicio, $fin];
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
