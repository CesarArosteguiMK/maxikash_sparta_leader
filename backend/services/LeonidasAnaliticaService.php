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
            'bucket_diagnostico' => static fn(array $criterios): array => (new \Services\LeonidasBucketDiagnosticService())->analizar($criterios),
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

        if ($this->esDiagnosticoBucket($normalizado)) {
            if (empty($contexto['permisos_agente']['analitica']) || empty($contexto['permisos_agente']['bucket'])) {
                return $this->denegado('el diagnostico de Bucket');
            }
            if ($this->mencionaComparativa($normalizado) && empty($contexto['permisos_agente']['comparativas'])) {
                return $this->denegado('Comparativas de Bucket');
            }
            if ($this->esSegundometro($normalizado) && empty($contexto['permisos_agente']['segundometro'])) {
                return $this->denegado('Segundometro');
            }
            return $this->resolverDiagnosticoBucket($normalizado);
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

    private function esDiagnosticoBucket(string $mensaje): bool
    {
        $mencionaCredito = preg_match('/\bcredito\s*(?:numero|no\.?|id|#)?\s*[:#-]?\s*\d{1,12}\b/u', $mensaje) === 1;
        $mencionaSemana = preg_match('/\bsemana\s+\d{1,2}(?:[\s-]+\d{4})?\b/u', $mensaje) === 1;
        $mencionaVistaBucket = $this->esBucket($mensaje)
            || $this->esSegundometro($mensaje)
            || preg_match('/\b(historic[a-z]*|comparativ[a-z]*|current|1[\s-]*7|8[\s-]*30|121\+)\b/u', $mensaje) === 1;
        $pideExplicacion = preg_match('/\b(por que|porque|xq|diferent[a-z]*|no coincide[n]?|no cuadra[n]?|aparece|reconcili[a-z]*|concili[a-z]*|diagnostic[a-z]*|compar[a-z]*|resumen|explica|total|cantidad)\b/u', $mensaje) === 1;

        return $mencionaVistaBucket && $pideExplicacion && ($mencionaCredito || $mencionaSemana);
    }

    private function mencionaComparativa(string $mensaje): bool
    {
        return preg_match('/\b(comparativ[a-z]*|semana pasada|contra la semana|versus|vs|otra pantalla)\b/u', $mensaje) === 1;
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

    private function resolverDiagnosticoBucket(string $mensaje): array
    {
        $criterios = [
            'corte' => $this->extraerCorte($mensaje),
            'bucket' => $this->extraerBucketDiagnostico($mensaje),
        ];

        if (preg_match('/\bcredito\s*(?:numero|no\.?|id|#)?\s*[:#-]?\s*(\d{1,12})\b/u', $mensaje, $m)) {
            $criterios['id_credito'] = (int) $m[1];
        } elseif (preg_match('/\bsemana\s+(\d{1,2})(?:[\s-]+(\d{4}))?\b/u', $mensaje, $m)) {
            $criterios['semana'] = !empty($m[2])
                ? 'Semana ' . (int) $m[1] . '-' . (int) $m[2]
                : (string) (int) $m[1];
        }

        try {
            $datos = ($this->adapters['bucket_diagnostico'])($criterios);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage());
        } catch (\Throwable $e) {
            error_log('LeonidasAnaliticaService diagnostico bucket: ' . $e->getMessage());
            return $this->error('No pude conciliar las fuentes de Bucket. No se invento ningun resultado; revisa la disponibilidad de Segundometro e intenta nuevamente.');
        }

        if (($datos['modo'] ?? '') === 'credito') {
            return $this->formatearDiagnosticoCredito($datos, $mensaje);
        }
        return $this->formatearDiagnosticoSemana($datos);
    }

    private function formatearDiagnosticoCredito(array $datos, string $pregunta): array
    {
        $credito = (int) ($datos['id_credito'] ?? 0);
        if (empty($datos['encontrado'])) {
            return [
                'mensaje' => sprintf(
                    'Revise el credito %s en la semana actual y en las ultimas semanas historicas disponibles, pero no aparece en esas fuentes de Bucket. No puedo confirmar una ubicacion sin evidencia; indica la pantalla y la semana donde lo viste para ampliar la conciliacion.',
                    number_format($credito)
                ),
                'tipo' => 'analitica_bucket_diagnostico',
                'fuente' => 'conciliacion_bucket_sparta',
                'metricas' => ['dataset' => 'diagnostico_bucket_credito', 'id_credito' => $credito, 'encontrado' => false],
            ];
        }

        $cliente = trim((string) ($datos['cliente'] ?? ''));
        $semana = (string) ($datos['semana'] ?? 'semana no identificada');
        $corte = (string) ($datos['corte'] ?? 'corte no identificado');
        $dia = (string) ($datos['dia_corte'] ?? 'dia no identificado');
        $conclusion = is_array($datos['conclusion'] ?? null) ? $datos['conclusion'] : [];
        $nivelConclusion = (string) ($conclusion['nivel'] ?? 'evidencia_parcial');
        $textoConclusion = trim((string) ($conclusion['texto'] ?? 'No hay evidencia suficiente para atribuir la diferencia a una sola causa.'));
        $vistas = array_values(array_filter((array) ($datos['vistas'] ?? []), 'is_array'));
        $movimientos = array_values(array_filter((array) ($datos['movimientos'] ?? []), 'is_array'));
        $fuentes = is_array($datos['fuentes_estado'] ?? null) ? $datos['fuentes_estado'] : [];
        $s2 = is_array($datos['s2'] ?? null) ? $datos['s2'] : [];
        $metricasS2 = is_array($s2['metricas'] ?? null) ? $s2['metricas'] : [];
        $condonaciones = is_array($datos['condonaciones'] ?? null) ? $datos['condonaciones'] : [];
        $convenios = is_array($datos['convenios'] ?? null) ? $datos['convenios'] : [];

        $lineas = [
            sprintf('Diagnostico individual del credito %s%s', number_format($credito), $cliente !== '' ? ' - ' . $cliente : ''),
            '',
            'Conclusion (' . str_replace('_', ' ', $nivelConclusion) . '): ' . $textoConclusion,
            '',
            'Clasificacion comprobada:',
            '- Bucket de nacimiento: ' . $this->valorDiagnostico($datos['bucket_nacimiento'] ?? $datos['bucket_real'] ?? null) . '.',
            '- Bucket actual dentro de la fotografia consultada (Segundometro/Avance): ' . $this->valorDiagnostico($datos['bucket_actual'] ?? $datos['bucket_segundometro'] ?? null) . '.',
            '- Bucket de cierre ajustado de esa fotografia (Historico): ' . $this->valorDiagnostico($datos['bucket_cierre_ajustado'] ?? $datos['bucket_historico'] ?? null) . '.',
            '- Comparativo: ' . $this->valorDiagnostico($datos['bucket_comparativo'] ?? null) . '; conciliado: ' . $this->valorDiagnostico($datos['bucket_comparativo_conciliado'] ?? null) . '.',
            '',
            'Detalle por vista:',
        ];

        $filas = [];
        foreach ($vistas as $vista) {
            $nombreVista = trim((string) ($vista['vista'] ?? 'Vista sin nombre'));
            $bucketVista = $this->valorDiagnostico($vista['bucket'] ?? null);
            $diasVista = isset($vista['dias_mora']) && $vista['dias_mora'] !== null
                ? (int) $vista['dias_mora'] . ' dias'
                : 'no informados';
            $corteVista = $this->valorDiagnostico($vista['corte'] ?? null);
            $fechaVista = $this->valorDiagnostico($vista['fecha_hora_fuente'] ?? null);
            $fuenteVista = $this->valorDiagnostico($vista['fuente'] ?? null);
            $antiguedad = $this->antiguedadDiagnostico($vista['antiguedad_minutos'] ?? null);
            $formula = trim((string) ($vista['formula'] ?? 'Formula no documentada.'));
            $filtrosVista = array_values(array_filter(array_map('strval', (array) ($vista['filtros'] ?? []))));

            $lineas[] = sprintf(
                '- %s: %s; mora usada: %s; corte: %s; fotografia: %s (%s); fuente: %s.',
                $nombreVista,
                $bucketVista,
                $diasVista,
                $corteVista,
                $fechaVista,
                $antiguedad,
                $fuenteVista
            );
            $lineas[] = '  Formula exacta: ' . $formula;
            $lineas[] = '  Filtros aplicados: ' . ($filtrosVista !== [] ? implode(', ', $filtrosVista) : 'ninguno informado') . '.';
            $filas[] = [
                'nombre' => $nombreVista,
                'estado' => $bucketVista,
                'detalle' => $diasVista . ' | ' . $corteVista . ' | ' . $fechaVista,
                'fuente' => $fuenteVista,
                'formula' => $formula,
            ];
        }

        if ($vistas === []) {
            $lineas[] = '- No fue posible desglosar las vistas; solo esta disponible la clasificacion resumida.';
        }

        $lineas[] = '';
        $lineas[] = 'Pagos y estado de cuenta S2:';
        if (($s2['estado'] ?? '') === 'disponible') {
            $lineas[] = sprintf(
                '- Mora reportada por S2: %s; saldo: %s; saldo vencido: %s; estatus: %s.',
                $this->valorDiagnostico($metricasS2['mora'] ?? null),
                $this->dineroDiagnostico($metricasS2['saldo'] ?? null),
                $this->dineroDiagnostico($metricasS2['saldo_vencido'] ?? null),
                $this->valorDiagnostico($metricasS2['estatus'] ?? null)
            );
            $lineas[] = sprintf(
                '- Ultimo pago reflejado: %s por %s; pagos localizados: %s; fecha de corte informada por S2: %s; consulta S2: %s.',
                $this->valorDiagnostico($metricasS2['ultimo_pago_fecha'] ?? null),
                $this->dineroDiagnostico($metricasS2['ultimo_pago_monto'] ?? null),
                number_format((int) ($metricasS2['pagos_registrados'] ?? 0)),
                $this->valorDiagnostico($metricasS2['fecha_corte'] ?? null),
                $this->valorDiagnostico($s2['consultado_at'] ?? null)
            );
        } else {
            $lineas[] = '- S2 no estuvo disponible: ' . $this->valorDiagnostico($s2['error'] ?? null) . '.';
        }

        $lineas[] = '';
        $lineas[] = 'Movimientos que pueden cambiar el bucket:';
        if ($movimientos === []) {
            $lineas[] = '- No se encontraron pagos, condonaciones, convenios o reestructuras recientes en las fuentes disponibles.';
        } else {
            foreach (array_slice($movimientos, 0, 8) as $movimiento) {
                $lineas[] = sprintf(
                    '- %s | %s | %s | %s.',
                    $this->valorDiagnostico($movimiento['fecha'] ?? null),
                    $this->valorDiagnostico($movimiento['titulo'] ?? $movimiento['tipo'] ?? null),
                    $this->valorDiagnostico($movimiento['detalle'] ?? null),
                    $this->valorDiagnostico($movimiento['fuente'] ?? null)
                );
            }
        }
        $lineas[] = '- Condonaciones: ' . $this->estadoMovimientoDiagnostico($condonaciones) . '.';
        $lineas[] = '- Convenios o reestructuras: ' . $this->estadoMovimientoDiagnostico($convenios) . '.';

        $razones = array_values(array_filter(array_map('strval', (array) ($datos['razones'] ?? []))));
        if ($razones !== []) {
            $lineas[] = '';
            $lineas[] = 'Reglas que influyeron:';
            foreach ($razones as $razon) {
                $lineas[] = '- ' . rtrim(trim($razon), '.') . '.';
            }
        }
        if (empty($datos['en_semana_actual'])) {
            $lineas[] = '';
            $lineas[] = 'Advertencia: No aparece en la semana actual; el diagnostico usa su registro historico mas reciente (' . $semana . ').';
        }
        if (preg_match('/\b8[\s-]*(?:a[\s-]*)?30\b/u', $pregunta)) {
            $lineas[] = 'Aclaracion: 8-30 no es un bucket nativo; agrupa 8-14, 15-21 y 22-30.';
        }

        $lineas[] = '';
        $lineas[] = 'Fuentes y vigencia:';
        foreach ($fuentes as $nombreFuente => $estadoFuente) {
            $lineas[] = '- ' . str_replace('_', ' ', (string) $nombreFuente) . ': ' . str_replace('_', ' ', (string) $estadoFuente) . '.';
        }
        $lineas[] = '- Evidencia Bucket: ' . $semana . ', corte ' . $dia . ' ' . $corte . ', fotografia ' . $this->valorDiagnostico($datos['fecha_hora_fuente'] ?? null) . ' (' . $this->antiguedadDiagnostico($datos['antiguedad_minutos'] ?? null) . ').';
        $lineas[] = '- Diagnostico generado: ' . $this->valorDiagnostico($datos['consultado_at'] ?? null) . '.';

        $mensaje = implode("\n", $lineas);

        return [
            'mensaje' => $mensaje,
            'tipo' => 'analitica_bucket_diagnostico',
            'fuente' => 'conciliacion_bucket_sparta_s2_movimientos',
            'reporte' => ['titulo' => 'Diagnostico de Bucket del credito ' . $credito, 'total' => 1, 'filas' => $filas],
            'metricas' => [
                'dataset' => 'diagnostico_bucket_credito',
                'id_credito' => $credito,
                'semana' => $semana,
                'corte' => $corte,
                'encontrado' => true,
                'nivel_conclusion' => $nivelConclusion,
                'fuentes' => $fuentes,
                'movimientos_encontrados' => count($movimientos),
            ],
        ];
    }

    private function valorDiagnostico(mixed $valor): string
    {
        if ($valor === null || trim((string) $valor) === '') {
            return 'no informado';
        }
        return trim((string) $valor);
    }

    private function dineroDiagnostico(mixed $valor): string
    {
        return is_numeric($valor) ? '$' . number_format((float) $valor, 2) : 'no informado';
    }

    private function antiguedadDiagnostico(mixed $minutos): string
    {
        if (!is_numeric($minutos)) {
            return 'antiguedad no calculable';
        }
        $total = max(0, (int) $minutos);
        if ($total < 60) {
            return $total . ' min de antiguedad';
        }
        if ($total >= 1440) {
            $dias = intdiv($total, 1440);
            $horas = intdiv($total % 1440, 60);
            return $dias . ' d ' . $horas . ' h de antiguedad';
        }
        $horas = intdiv($total, 60);
        $resto = $total % 60;
        return $horas . ' h ' . $resto . ' min de antiguedad';
    }

    private function estadoMovimientoDiagnostico(array $fuente): string
    {
        $estado = (string) ($fuente['estado'] ?? 'no_disponible');
        if ($estado === 'disponible') {
            return number_format((int) ($fuente['total'] ?? 0)) . ' movimiento(s) encontrado(s)';
        }
        if ($estado === 'sin_movimientos') {
            return 'sin movimientos registrados';
        }
        return 'fuente no disponible (' . $this->valorDiagnostico($fuente['error'] ?? null) . ')';
    }

    private function formatearDiagnosticoSemana(array $datos): array
    {
        $semana = (string) ($datos['semana'] ?? 'Semana no identificada');
        $corte = (string) ($datos['corte'] ?? 'corte no identificado');
        $dia = (string) ($datos['dia_corte'] ?? 'dia no identificado');
        $comparacionDisponible = !array_key_exists('comparacion_disponible', $datos)
            || !empty($datos['comparacion_disponible']);
        $historico = (array) ($datos['historico'] ?? []);
        $comparativo = (array) ($datos['comparativo'] ?? []);
        $totalHistorico = (int) ($historico['total'] ?? 0);
        $totalComparable = (int) ($historico['total_comparable'] ?? 0);
        $total121 = (int) ($historico['total_121'] ?? 0);
        $totalComparativo = (int) ($comparativo['total_visible'] ?? 0);
        $diferenciaPantallas = $comparacionDisponible
            ? (int) ($datos['diferencia_total_pantallas'] ?? ($totalComparativo - $totalHistorico))
            : null;
        $diferenciaComparable = $comparacionDisponible
            ? (int) ($datos['diferencia_total_comparable'] ?? ($totalComparativo - $totalComparable))
            : null;
        $creditosDiferencia = array_values(array_filter((array) ($datos['creditos_diferencia'] ?? []), 'is_array'));
        $resumenDiferencia = is_array($datos['resumen_creditos_diferencia'] ?? null)
            ? $datos['resumen_creditos_diferencia']
            : [];
        $bucket = is_array($datos['bucket_solicitado'] ?? null) ? $datos['bucket_solicitado'] : null;

        if (!$comparacionDisponible) {
            $mensaje = sprintf(
                '%s sigue abierta. Comparativo registra %s creditos al corte %s %s, pero Historico aun no tiene un cierre para esa semana.',
                $semana,
                number_format($totalComparativo),
                $dia,
                $corte
            );
            if ($bucket !== null) {
                $mensaje .= sprintf(
                    ' En %s hay %s creditos en el corte operativo.',
                    (string) ($bucket['solicitado'] ?? 'el bucket solicitado'),
                    number_format((int) ($bucket['comparativo'] ?? 0))
                );
            }
            $mensaje .= ' No calculare una diferencia contra cero ni atribuire creditos como entrada o salida hasta que exista el cierre historico. Para una conciliacion exacta, consulta una semana cerrada.';

            $filas = [[
                'nombre' => 'Comparativo operativo',
                'estado' => number_format($totalComparativo) . ' creditos',
                'detalle' => 'Corte ' . $dia . ' ' . $corte,
            ]];
            if ($bucket !== null) {
                $filas[] = [
                    'nombre' => 'Bucket ' . (string) ($bucket['solicitado'] ?? ''),
                    'estado' => number_format((int) ($bucket['comparativo'] ?? 0)) . ' creditos',
                    'detalle' => 'Sin cierre historico comparable',
                ];
            }

            $series = [];
            foreach ((array) ($comparativo['buckets'] ?? []) as $clave => $valor) {
                $series[] = ['etiqueta' => $this->limpiarBucket((string) $clave), 'valor' => (int) $valor];
            }

            return [
                'mensaje' => $mensaje,
                'tipo' => 'analitica_bucket_diagnostico',
                'fuente' => 'conciliacion_bucket_sparta',
                'reporte' => ['titulo' => 'Corte operativo ' . $semana . ' - ' . $dia . ' ' . $corte, 'total' => $totalComparativo, 'filas' => $filas],
                'grafica' => ['titulo' => 'Distribucion operativa por bucket', 'series' => $series],
                'metricas' => [
                    'dataset' => 'diagnostico_bucket_semana',
                    'semana' => $semana,
                    'corte' => $corte,
                    'estado_semana' => (string) ($datos['estado_semana'] ?? 'abierta_sin_cierre_historico'),
                    'comparacion_disponible' => false,
                    'total_comparativo' => $totalComparativo,
                    'total_historico' => null,
                    'diferencia_comparable' => null,
                ],
            ];
        }

        $mensaje = sprintf(
            '%s al corte %s %s: Historico muestra %s creditos; Comparativo muestra %s. La diferencia visible es %s.',
            $semana,
            $dia,
            $corte,
            number_format($totalHistorico),
            number_format($totalComparativo),
            $this->numeroConSigno($diferenciaPantallas)
        );
        if ($total121 > 0) {
            $mensaje .= sprintf(
                ' Historico incluye %s creditos en 121+, mientras que el total visible del Comparativo termina en 91-120. Al excluir 121+ de ambos universos, la diferencia queda en %s.',
                number_format($total121),
                $this->numeroConSigno($diferenciaComparable)
            );
        }

        if ($bucket !== null) {
            $mensaje .= sprintf(
                ' En %s: Historico tiene %s y Comparativo %s; diferencia %s.',
                (string) ($bucket['solicitado'] ?? 'el bucket solicitado'),
                number_format((int) ($bucket['historico'] ?? 0)),
                number_format((int) ($bucket['comparativo'] ?? 0)),
                $this->numeroConSigno((int) ($bucket['diferencia'] ?? 0))
            );
        }

        $transiciones = array_values(array_filter((array) ($datos['transiciones'] ?? []), 'is_array'));
        if ($diferenciaComparable !== 0 || $bucket !== null) {
            $mensaje .= ' La causa restante es de clasificacion: Historico usa el cierre semanal consolidado y Comparativo recalcula los dias de mora del corte seleccionado.';
            if ($transiciones !== []) {
                $principales = array_slice($transiciones, 0, 3);
                $movimientos = array_map(static fn(array $fila): string => sprintf(
                    '%s a %s: %s',
                    (string) ($fila['historico'] ?? 'Sin bucket'),
                    (string) ($fila['comparativo'] ?? 'Sin bucket'),
                    number_format((int) ($fila['creditos'] ?? 0))
                ), $principales);
                $mensaje .= ' Principales reclasificaciones: ' . implode('; ', $movimientos) . '.';
            }
        }

        if ($bucket !== null && $creditosDiferencia !== []) {
            $afectados = (int) ($resumenDiferencia['afectados'] ?? count($creditosDiferencia));
            $entran = (int) ($resumenDiferencia['entran'] ?? 0);
            $salen = (int) ($resumenDiferencia['salen'] ?? 0);
            $neto = (int) ($resumenDiferencia['neto'] ?? ($entran - $salen));
            $diferenciaBucket = (int) ($bucket['diferencia'] ?? 0);
            $detalleCuadra = array_key_exists('detalle_cuadra', $bucket)
                ? !empty($bucket['detalle_cuadra'])
                : $neto === $diferenciaBucket;
            $diferenciaNoExplicada = array_key_exists('diferencia_no_explicada', $bucket)
                ? (int) ($bucket['diferencia_no_explicada'] ?? 0)
                : $diferenciaBucket - $neto;
            if ($detalleCuadra) {
                $mensaje .= sprintf(
                    ' La diferencia neta de %s en %s queda reconciliada exactamente y se forma con %s credito(s) que entran y %s que salen: %s credito(s) afectados en total.',
                    $this->numeroConSigno($neto),
                    (string) ($bucket['solicitado'] ?? 'el bucket solicitado'),
                    number_format($entran),
                    number_format($salen),
                    number_format($afectados)
                );
            } else {
                $mensaje .= sprintf(
                    ' El detalle identificado produce un neto de %s, pero la diferencia del bucket es %s. Quedan %s credito(s) sin reconciliar y no dare la diferencia por explicada.',
                    $this->numeroConSigno($neto),
                    $this->numeroConSigno($diferenciaBucket),
                    $this->numeroConSigno($diferenciaNoExplicada)
                );
            }
            $mensaje .= ' Creditos concretos que explican el movimiento:';
            foreach (array_slice($creditosDiferencia, 0, 20) as $credito) {
                $idCredito = (int) ($credito['id_credito'] ?? 0);
                $cliente = trim((string) ($credito['cliente'] ?? ''));
                $movimiento = trim((string) ($credito['movimiento'] ?? 'reclasificado'));
                $historicoCredito = $this->valorDiagnostico($credito['bucket_historico'] ?? null);
                $comparativoCredito = $this->valorDiagnostico($credito['bucket_comparativo'] ?? null);
                $diasMora = isset($credito['dias_mora_corte']) && $credito['dias_mora_corte'] !== null
                    ? (int) $credito['dias_mora_corte'] . ' dia(s) de mora'
                    : 'mora no informada';
                $motivo = trim((string) ($credito['motivo'] ?? 'La clasificacion cambio por la regla del corte.'));
                $mensaje .= sprintf(
                    ' Credito %s%s: %s; %s -> %s; %s. %s',
                    number_format($idCredito),
                    $cliente !== '' ? ' - ' . $cliente : '',
                    $movimiento,
                    $historicoCredito,
                    $comparativoCredito,
                    $diasMora,
                    rtrim($motivo, '.') . '.'
                );
            }
            if (count($creditosDiferencia) > 20 || !empty($datos['detalle_creditos_truncado'])) {
                $mensaje .= ' El reporte adjunto conserva el desglose disponible; el texto muestra los primeros 20 para mantenerlo legible.';
            }
        } elseif ($bucket !== null && (int) ($bucket['diferencia'] ?? 0) !== 0) {
            $mensaje .= ' No fue posible identificar creditos individuales suficientes para reconciliar esa diferencia; no atribuire la causa sin evidencia.';
        }
        $mensaje .= ' Por eso dos cantidades pueden ser correctas dentro de sus propias reglas, pero no son comparables hasta igualar semana, dia, hora de corte, conciliacion y buckets visibles.';

        $filas = [
            ['nombre' => 'Historico completo', 'estado' => number_format($totalHistorico) . ' creditos', 'detalle' => 'Incluye Current hasta 121+'],
            ['nombre' => 'Historico comparable', 'estado' => number_format($totalComparable) . ' creditos', 'detalle' => 'Excluye 121+'],
            ['nombre' => 'Comparativo visible', 'estado' => number_format($totalComparativo) . ' creditos', 'detalle' => 'Current hasta 91-120'],
        ];
        if ($bucket !== null) {
            $filas[] = [
                'nombre' => 'Bucket ' . (string) ($bucket['solicitado'] ?? ''),
                'estado' => number_format((int) ($bucket['historico'] ?? 0)) . ' historico',
                'detalle' => number_format((int) ($bucket['comparativo'] ?? 0)) . ' comparativo',
            ];
        }
        foreach (array_slice($transiciones, 0, 5) as $movimiento) {
            $filas[] = [
                'nombre' => (string) ($movimiento['historico'] ?? 'Sin bucket') . ' -> ' . (string) ($movimiento['comparativo'] ?? 'Sin bucket'),
                'estado' => number_format((int) ($movimiento['creditos'] ?? 0)) . ' creditos',
                'detalle' => 'Reclasificacion por regla de corte',
            ];
        }
        foreach ($creditosDiferencia as $credito) {
            $idCredito = (int) ($credito['id_credito'] ?? 0);
            $cliente = trim((string) ($credito['cliente'] ?? ''));
            $filas[] = [
                'nombre' => 'Credito ' . number_format($idCredito) . ($cliente !== '' ? ' - ' . $cliente : ''),
                'estado' => $this->valorDiagnostico($credito['bucket_historico'] ?? null)
                    . ' -> ' . $this->valorDiagnostico($credito['bucket_comparativo'] ?? null),
                'detalle' => ucfirst((string) ($credito['movimiento'] ?? 'reclasificado'))
                    . ' | ' . $this->valorDiagnostico($credito['dias_mora_corte'] ?? null) . ' dia(s) de mora'
                    . ' | ' . trim((string) ($credito['motivo'] ?? '')),
                'fuente' => $this->valorDiagnostico($credito['fecha_hora_fuente'] ?? null),
                'movimiento' => (string) ($credito['movimiento'] ?? 'reclasificado'),
                'bucket_nacimiento' => $this->valorDiagnostico($credito['bucket_nacimiento'] ?? null),
                'bucket_historico' => $this->valorDiagnostico($credito['bucket_historico'] ?? null),
                'bucket_comparativo' => $this->valorDiagnostico($credito['bucket_comparativo'] ?? null),
                'bucket_por_mora' => $this->valorDiagnostico($credito['bucket_por_mora'] ?? null),
                'dias_mora_corte' => $credito['dias_mora_corte'] ?? null,
                'cierre_actual' => $this->valorDiagnostico($credito['cierre_actual'] ?? null),
                'bucket_ajustado_ghost' => $this->valorDiagnostico($credito['bucket_ajustado_ghost'] ?? null),
                'variable_8' => $this->valorDiagnostico($credito['variable_8'] ?? null),
                'ghost' => $this->valorDiagnostico($credito['ghost'] ?? null),
                'formula_historico' => trim((string) ($credito['formula_historico'] ?? '')),
                'formula_comparativo' => trim((string) ($credito['formula_comparativo'] ?? '')),
            ];
        }

        $series = [];
        foreach ((array) ($historico['buckets'] ?? []) as $clave => $valor) {
            $series[] = ['etiqueta' => $this->limpiarBucket((string) $clave), 'valor' => (int) $valor];
        }

        return [
            'mensaje' => $mensaje,
            'tipo' => 'analitica_bucket_diagnostico',
            'fuente' => 'conciliacion_bucket_sparta',
            'reporte' => ['titulo' => 'Conciliacion ' . $semana . ' - ' . $dia . ' ' . $corte, 'total' => $totalHistorico, 'filas' => $filas],
            'grafica' => ['titulo' => 'Distribucion historica por bucket', 'series' => $series],
            'metricas' => [
                'dataset' => 'diagnostico_bucket_semana',
                'semana' => $semana,
                'corte' => $corte,
                'total_historico' => $totalHistorico,
                'total_comparativo' => $totalComparativo,
                'diferencia_comparable' => $diferenciaComparable,
                'creditos_afectados' => (int) ($resumenDiferencia['afectados'] ?? count($creditosDiferencia)),
                'creditos_entran' => (int) ($resumenDiferencia['entran'] ?? 0),
                'creditos_salen' => (int) ($resumenDiferencia['salen'] ?? 0),
                'diferencia_neta_detalle' => (int) ($resumenDiferencia['neto'] ?? 0),
                'detalle_cuadra' => $bucket !== null ? !empty($bucket['detalle_cuadra']) : null,
                'diferencia_no_explicada' => $bucket['diferencia_no_explicada'] ?? null,
            ],
        ];
    }

    private function extraerBucketDiagnostico(string $mensaje): ?string
    {
        $patrones = [
            '121+' => '/\b121\s*\+/u',
            '91-120' => '/\b91\s*(?:a|-)\s*120\b/u',
            '61-90' => '/\b61\s*(?:a|-)\s*90\b/u',
            '31-60' => '/\b31\s*(?:a|-)\s*60\b/u',
            '8-30' => '/\b8\s*(?:a|-)\s*30\b/u',
            '22-30' => '/\b22\s*(?:a|-)\s*30\b/u',
            '15-21' => '/\b15\s*(?:a|-)\s*21\b/u',
            '8-14' => '/\b8\s*(?:a|-)\s*14\b/u',
            '1-7' => '/\b1\s*(?:a|-)\s*7\b/u',
            'Current' => '/\bcurrent\b/u',
        ];
        foreach ($patrones as $bucket => $patron) {
            if (preg_match($patron, $mensaje)) {
                return $bucket;
            }
        }
        return null;
    }

    private function numeroConSigno(int $valor): string
    {
        return ($valor >= 0 ? '+' : '') . number_format($valor);
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
