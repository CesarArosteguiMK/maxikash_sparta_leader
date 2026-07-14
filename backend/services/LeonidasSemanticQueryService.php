<?php

namespace Services;

use Core\Database;
use Models\AvanceBucket;
use Models\GastosCobranzaEstadistica;
use Models\Ticket;

/**
 * Converts natural-language questions into validated read-only queries.
 * Qwen selects from this catalog; it never creates or executes SQL.
 */
class LeonidasSemanticQueryService
{
    private const MAX_LIST_ROWS = 50;

    /** @return array<string, mixed>|null */
    public function resolver(string $mensaje, int $actorId): ?array
    {
        try {
            $operational = $this->resolverOperacionCobranza($mensaje);
            if ($operational !== null) {
                return $operational;
            }

            $plan = $this->planificar($mensaje);
            if (!is_array($plan) || ($plan['accion'] ?? '') !== 'consultar_datos') {
                return null;
            }
            return $this->ejecutar($plan, $actorId);
        } catch (\InvalidArgumentException $error) {
            error_log('[Leonidas] Rejected semantic plan: ' . $error->getMessage());
            return null;
        } catch (\Throwable $error) {
            error_log('[Leonidas] Semantic query failed: ' . $error->getMessage());
            return [
                'mensaje' => 'Entendi la consulta, pero la fuente de datos no respondio correctamente. No se realizo ningun cambio.',
                'tipo' => 'consulta_semantica_error',
            ];
        }
    }

    /** @return array<string, mixed>|null */
    private function resolverOperacionCobranza(string $mensaje): ?array
    {
        $normalized = $this->normalize($mensaje);
        if (preg_match('/\b(pagaron?|pago|pagos)\b.*\b(puntual|puntuales|ventana|tiempo)\b|\b(puntual|puntuales)\b.*\b(pago|pagaron?)\b/', $normalized)) {
            return $this->consultarPagosPuntuales($normalized);
        }
        if (preg_match('/\b(segundometro|bucket|buckets|morosidad)\b|\b(current|1\s*(?:(?:a|-)\s*)?7|8\s*(?:(?:a|-)\s*)?14|15\s*(?:(?:a|-)\s*)?21|22\s*(?:(?:a|-)\s*)?30|31\s*(?:(?:a|-)\s*)?60|61\s*(?:(?:a|-)\s*)?90|91\s*(?:(?:a|-)\s*)?120|121\s*\+)\b/', $normalized)) {
            return $this->consultarSegundometro($normalized);
        }
        if (preg_match('/\b(gastos? de cobranza|cargos? de cobranza|monto generado|monto recuperado|monto pendiente|monto condonado)\b/', $normalized)) {
            return $this->consultarGastosCobranza($normalized);
        }
        return null;
    }

    /** @return array<string, mixed> */
    private function consultarPagosPuntuales(string $normalized): array
    {
        $fecha = $this->fechaSolicitada($normalized);
        $resultado = Ticket::getTicketsDetallePorDia($fecha);
        if (empty($resultado['success'])) {
            throw new \RuntimeException((string) ($resultado['mensaje'] ?? 'No se pudo consultar Sabueso.'));
        }

        $creditosPagados = [];
        $evaluados = 0;
        $noVerificados = 0;
        foreach ((array) ($resultado['filas'] ?? []) as $fila) {
            $estado = $this->normalize((string) ($fila['pagaron'] ?? ''));
            if ($estado === 'no se pudo verificar') {
                $noVerificados++;
                continue;
            }
            if (!in_array($estado, ['si', 'no'], true)) {
                continue;
            }
            $evaluados++;
            $creditoId = (int) ($fila['id_credito'] ?? 0);
            if ($estado === 'si' && $creditoId > 0) {
                $creditosPagados[$creditoId] = true;
            }
        }

        $total = count($creditosPagados);
        $hoy = $this->fechaSolicitada('hoy');
        $ayer = $this->fechaSolicitada('ayer');
        $dia = $fecha === $hoy ? 'hoy' : ($fecha === $ayer ? 'ayer' : 'el ' . $fecha);
        $mensaje = ucfirst($dia) . ' hay ' . number_format($total) . ' créditos con pago confirmado dentro de la ventana evaluada por Sabueso.';
        $mensaje .= ' Se evaluaron ' . number_format($evaluados) . ' tickets';
        if ($noVerificados > 0) {
            $mensaje .= ' y ' . number_format($noVerificados) . ' no pudieron verificarse';
        }
        $mensaje .= '.';

        return $this->operationalResponse($mensaje, 'pagos_puntuales', [
            'fecha' => $fecha,
            'creditos_pagados' => $total,
            'tickets_evaluados' => $evaluados,
            'no_verificados' => $noVerificados,
            'definicion' => 'Pago confirmado dentro de la ventana evaluada por Sabueso',
        ]);
    }

    /** @return array<string, mixed> */
    private function consultarSegundometro(string $normalized): array
    {
        $resultado = AvanceBucket::calcular();
        if (empty($resultado['success'])) {
            throw new \RuntimeException((string) ($resultado['mensaje'] ?? 'No se pudo consultar Segundómetro.'));
        }

        $buckets = $this->bucketsSolicitados($normalized);
        if ($buckets !== []) {
            $destino = $buckets[count($buckets) - 1];
            $valor = 0;
            foreach ((array) ($resultado['resumen_cierre'] ?? []) as $fila) {
                if (($fila['bucket'] ?? '') === $destino) {
                    $valor = (int) ($fila['valor'] ?? 0);
                    break;
                }
            }

            if (count($buckets) >= 2 && preg_match('/\b(de|desde)\b.*\b(a|hacia)\b/', $normalized)) {
                $origen = $buckets[0];
                foreach ((array) (($resultado['matriz_creditos']['filas'] ?? [])) as $fila) {
                    if (($fila['bucket'] ?? '') === $origen) {
                        $valor = (int) (($fila['celdas'][$destino] ?? 0));
                        break;
                    }
                }
                return $this->operationalResponse(
                    number_format($valor) . ' créditos pasaron de ' . $this->bucketCorto($origen) . ' a ' . $this->bucketCorto($destino) . ' en el corte actual de Segundómetro.',
                    'segundometro_transicion',
                    ['origen' => $origen, 'destino' => $destino, 'creditos' => $valor, 'corte' => $resultado['corte'] ?? 'actual']
                );
            }

            if (preg_match('/\b(pasaron|migraron|cayeron|avanzaron)\b.*\b(a|hacia)\b/', $normalized)) {
                $migraron = 0;
                foreach ((array) (($resultado['matriz_creditos']['filas'] ?? [])) as $fila) {
                    if (($fila['bucket'] ?? '') === $destino) {
                        continue;
                    }
                    $migraron += (int) (($fila['celdas'][$destino] ?? 0));
                }
                return $this->operationalResponse(
                    number_format($migraron) . ' créditos migraron hacia ' . $this->bucketCorto($destino)
                        . ' desde otros buckets en el corte actual. El cierre ajustado contiene '
                        . number_format($valor) . ' créditos en ese bucket.',
                    'segundometro_destino',
                    [
                        'destino' => $destino,
                        'creditos_migrados' => $migraron,
                        'creditos_cierre' => $valor,
                        'corte' => $resultado['corte'] ?? 'actual',
                    ]
                );
            }

            return $this->operationalResponse(
                'En el cierre ajustado actual hay ' . number_format($valor) . ' créditos en ' . $this->bucketCorto($destino) . '.',
                'segundometro_bucket',
                ['bucket' => $destino, 'creditos' => $valor, 'total' => (int) ($resultado['total'] ?? 0), 'corte' => $resultado['corte'] ?? 'actual']
            );
        }

        $indicadores = (array) ($resultado['indicadores'] ?? []);
        $mensaje = 'Segundómetro registra ' . number_format((int) ($resultado['total'] ?? 0)) . ' créditos en el corte actual: '
            . number_format((int) ($indicadores['mejoran'] ?? 0)) . ' mejoraron de bucket, '
            . number_format((int) ($indicadores['igual'] ?? 0)) . ' permanecieron igual y '
            . number_format((int) ($indicadores['empeoran'] ?? 0)) . ' empeoraron.';
        return $this->operationalResponse($mensaje, 'segundometro_resumen', [
            'total' => (int) ($resultado['total'] ?? 0),
            'mejoran' => (int) ($indicadores['mejoran'] ?? 0),
            'igual' => (int) ($indicadores['igual'] ?? 0),
            'empeoran' => (int) ($indicadores['empeoran'] ?? 0),
            'corte' => $resultado['corte'] ?? 'actual',
        ]);
    }

    /** @return array<string, mixed> */
    private function consultarGastosCobranza(string $normalized): array
    {
        [$inicio, $fin, $periodoTexto] = $this->rangoSolicitado($normalized);
        $resultado = GastosCobranzaEstadistica::getDashboard('semana', 'semana', $inicio, $fin);
        if (empty($resultado['success'])) {
            throw new \RuntimeException((string) ($resultado['error'] ?? 'No se pudo consultar Gastos de Cobranza.'));
        }
        $datos = (array) ($resultado['datos'] ?? []);
        $kpis = (array) ($datos['kpis'] ?? []);
        $clave = match (true) {
            str_contains($normalized, 'recuper') => 'recuperado',
            str_contains($normalized, 'pendient') => 'pendiente',
            str_contains($normalized, 'condon') => 'condonado',
            default => 'total_generado',
        };
        $kpi = (array) ($kpis[$clave] ?? []);
        $etiqueta = str_replace('_', ' ', $clave);
        $inicioFrase = $this->inicioPeriodo($periodoTexto);
        $mensaje = $inicioFrase . ', Gastos de Cobranza reporta ' . $etiqueta . ' por $'
            . number_format((float) ($kpi['monto'] ?? 0), 2) . ' (' . number_format((float) ($kpi['pct'] ?? 0), 2) . '% del monto generado).';
        if ($clave === 'total_generado') {
            $mensaje = $inicioFrase . ' se generaron ' . number_format((int) ($kpi['count'] ?? 0))
                . ' cargos de cobranza. El monto generado registrado para ese periodo es $'
                . number_format((float) ($kpi['monto'] ?? 0), 2) . '.';
        }
        return $this->operationalResponse($mensaje, 'gastos_cobranza', [
            'periodo' => $periodoTexto,
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'concepto' => $clave,
            'monto' => (float) ($kpi['monto'] ?? 0),
            'porcentaje' => (float) ($kpi['pct'] ?? 0),
            'cargos_generados' => (int) (($kpis['total_generado']['count'] ?? 0)),
        ]);
    }

    private function fechaSolicitada(string $normalized): string
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $fecha = new \DateTimeImmutable('today', $tz);
        if (str_contains($normalized, 'ayer')) {
            $fecha = $fecha->modify('-1 day');
        }
        return $fecha->format('Y-m-d');
    }

    private function inicioPeriodo(string $periodo): string
    {
        return match ($periodo) {
            'hoy' => 'Hoy',
            'ayer' => 'Ayer',
            'la semana actual' => 'Durante la semana actual',
            'el mes actual' => 'Durante el mes actual',
            default => 'Durante ' . $periodo,
        };
    }

    /** @return array{0:string,1:string,2:string} */
    private function rangoSolicitado(string $normalized): array
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $hoy = new \DateTimeImmutable('today', $tz);
        if (str_contains($normalized, 'ayer')) {
            $ayer = $hoy->modify('-1 day')->format('Y-m-d');
            return [$ayer, $ayer, 'ayer'];
        }
        if (preg_match('/\b(este mes|mes actual)\b/', $normalized)) {
            return [$hoy->modify('first day of this month')->format('Y-m-d'), $hoy->format('Y-m-d'), 'el mes actual'];
        }
        if (preg_match('/\b(esta semana|semana actual)\b/', $normalized)) {
            return [$hoy->modify('monday this week')->format('Y-m-d'), $hoy->format('Y-m-d'), 'la semana actual'];
        }
        return [$hoy->format('Y-m-d'), $hoy->format('Y-m-d'), 'hoy'];
    }

    /** @return list<string> */
    private function bucketsSolicitados(string $normalized): array
    {
        $patterns = [
            'a) Current' => '/\bcurrent\b/',
            'b) 1 a 7 dias' => '/\b1\s*(?:(?:a|-)\s*)?7\b/',
            'c) 8 a 14 dias' => '/\b8\s*(?:(?:a|-)\s*)?14\b/',
            'd) 15 a 21 dias' => '/\b15\s*(?:(?:a|-)\s*)?21\b/',
            'e) 22 a 30 dias' => '/\b22\s*(?:(?:a|-)\s*)?30\b/',
            'f) 31 a 60 dias' => '/\b31\s*(?:(?:a|-)\s*)?60\b/',
            'g) 61 a 90 dias' => '/\b61\s*(?:(?:a|-)\s*)?90\b/',
            'h) 91 a 120 dias' => '/\b91\s*(?:(?:a|-)\s*)?120\b/',
            'i) 121+ dias' => '/\b121\s*(?:\+|mas)\b/',
        ];
        $matches = [];
        foreach ($patterns as $bucket => $pattern) {
            if (preg_match($pattern, $normalized, $match, PREG_OFFSET_CAPTURE)) {
                $matches[] = ['bucket' => $bucket, 'offset' => $match[0][1]];
            }
        }
        usort($matches, static fn(array $a, array $b): int => $a['offset'] <=> $b['offset']);
        return array_column($matches, 'bucket');
    }

    private function bucketCorto(string $bucket): string
    {
        $corto = preg_replace('/^[a-i]\)\s*/', '', $bucket) ?: $bucket;
        return str_replace([' dias', ' mas'], [' días', ' más'], $corto);
    }

    /** @param array<string, mixed> $metrics @return array<string, mixed> */
    private function operationalResponse(string $mensaje, string $dataset, array $metrics): array
    {
        return [
            'mensaje' => $mensaje,
            'tipo' => 'consulta_operativa',
            'metricas' => $metrics + ['dataset' => $dataset],
            'ia_disponible' => true,
            'modelo_ia' => 'Qwen + fuentes operativas de Sparta',
        ];
    }

    /** @return array<string, mixed>|null */
    private function planificar(string $mensaje): ?array
    {
        $localPlan = $this->planificarLocal($mensaje);
        if ($localPlan !== null) {
            return $localPlan;
        }

        $catalogo = $this->catalogoPublico();
        $prompt = 'Convierte la pregunta en un plan de consulta usando exclusivamente el catalogo. '
            . 'No escribas SQL. Si la pregunta pide una explicacion, una accion, datos sensibles o no corresponde al catalogo, usa accion="ninguna". '
            . 'Las operaciones validas son conteo, lista y agrupacion. agrupacion requiere agrupar_por. '
            . 'Los operadores validos son igual, distinto, contiene, en, verdadero, falso, nulo y no_nulo. '
            . 'Para fechas usa periodo con campo y preset: hoy, ayer, semana_actual, mes_actual, mes_anterior, anio_actual, ultimos_7_dias, ultimos_30_dias; '
            . 'o inicio y fin en YYYY-MM-DD. Usa solo campos declarados. La confianza va de 0 a 1. '
            . 'Devuelve exclusivamente JSON: '
            . '{"accion":"consultar_datos|ninguna","dataset":"","operacion":"conteo|lista|agrupacion",'
            . '"filtros":[{"campo":"","operador":"igual","valor":""}],'
            . '"agrupar_por":"","periodo":{"campo":"","preset":"","inicio":"","fin":""},'
            . '"limite":20,"confianza":0.0}. '
            . "\nFECHA ACTUAL: " . date('Y-m-d')
            . "\nCATALOGO AUTORIZADO:\n" . json_encode($catalogo, JSON_UNESCAPED_SLASHES)
            . "\nPREGUNTA:\n" . $mensaje;

        $plan = (new LeonidasQwenClient())->json(
            'Eres el planificador seguro de Leonidas para Sparta. Clasificas preguntas, no respondes al usuario y nunca generas SQL.',
            $prompt,
            1000
        );
        if (!is_array($plan)) {
            return null;
        }

        $confianza = (float) ($plan['confianza'] ?? 0);
        if ($confianza < 0.55) {
            return null;
        }
        return $plan;
    }

    /** @return array<string, mixed>|null */
    private function planificarLocal(string $mensaje): ?array
    {
        $normalized = $this->normalize($mensaje);
        if (preg_match('/\b(salarios?|sueldos?|curp|rfc|nss|cuentas? bancarias?|clabe|documentos? sensibles?)\b/', $normalized)) {
            return ['accion' => 'ninguna', 'confianza' => 1.0];
        }
        if (preg_match('/\b(actualiza|cambia|modifica|elimina|borra|asigna|otorga|concede|manda|envia|descarga)\b/', $normalized)) {
            return ['accion' => 'ninguna', 'confianza' => 1.0];
        }
        if (preg_match('/\b(como funciona|que hace|para que sirve|explica|explicame|opinion|opina|analiza|evalua)\b/', $normalized)) {
            return ['accion' => 'ninguna', 'confianza' => 1.0];
        }

        $dataset = '';
        if (preg_match('/\b(candidato|candidatos|candidata|candidatas|seleccion de personal)\b/', $normalized)) {
            $dataset = 'candidatos';
        } elseif (preg_match('/\b(modulo|modulos|permiso|permisos|acceso|accesos)\b/', $normalized)) {
            $dataset = 'modulos';
        } elseif (preg_match('/\b(usuario|usuarios|persona|personas|colaborador|colaboradores|empleado|empleados|plantilla|gestor|gestores|activo|activos|baja|bajas)\b/', $normalized)) {
            $dataset = 'personal';
        }
        if ($dataset === '') {
            return null;
        }

        $groupFields = ['estatus', 'empresa', 'direccion', 'area', 'departamento', 'puesto'];
        if ($dataset === 'personal') {
            $groupFields[] = 'es_externo';
        } elseif ($dataset === 'candidatos') {
            $groupFields[] = 'es_reingreso';
            $groupFields[] = 'proceso_cerrado';
        } else {
            $groupFields = ['categoria', 'activo', 'asignado_al_usuario'];
        }

        $group = '';
        foreach ($groupFields as $candidate) {
            $label = str_replace('_', '\\s+', preg_quote($candidate, '/'));
            if (preg_match('/\bpor\s+' . $label . '\b/', $normalized)) {
                $group = $candidate;
                break;
            }
        }
        $groupIntent = $group !== '' || preg_match('/\b(agrupa|agrupado|distribuye|distribucion|desglose)\b/', $normalized);
        $listIntent = preg_match('/\b(lista|listar|muestra|mostrar|quienes|reporte|detalle|dame los|dame las)\b/', $normalized);
        $countIntent = preg_match('/\b(cuanto|cuantos|cuantas|conteo|total|cantidad|numero)\b/', $normalized);
        if ($dataset === 'modulos' && preg_match('/\b(que|cuales)\b/', $normalized)) {
            $listIntent = 1;
        }
        if (!$groupIntent && !$listIntent && !$countIntent) {
            return null;
        }
        $operation = $groupIntent ? 'agrupacion' : ($listIntent ? 'lista' : 'conteo');
        if ($dataset === 'modulos' && preg_match('/\b(que|cuales)\b/', $normalized)) {
            $operation = 'lista';
        }
        if ($operation === 'agrupacion' && $group === '') {
            return null;
        }

        $filters = [];
        if (preg_match('/\b(furia motos|furia|pensionamax)\b/', $normalized)) {
            $filters[] = ['campo' => 'empresa', 'operador' => 'igual', 'valor' => 'Furia Motos'];
        } elseif (preg_match('/\b(maxikash|amigos efectivo)\b/', $normalized)) {
            $filters[] = ['campo' => 'empresa', 'operador' => 'igual', 'valor' => 'MaxiKash'];
        }

        if ($dataset === 'personal') {
            if (preg_match('/\b(activo|activos|activa|activas)\b/', $normalized)) {
                $filters[] = ['campo' => 'estatus', 'operador' => 'igual', 'valor' => 'Activo'];
            } elseif (preg_match('/\b(baja|bajas|inactivo|inactivos)\b/', $normalized)) {
                $filters[] = ['campo' => 'estatus', 'operador' => 'igual', 'valor' => 'Baja'];
            }
            if (preg_match('/\bcampo\s*1\s*(?:-|a)\s*7\b/', $normalized)) {
                $filters[] = ['campo' => 'departamento', 'operador' => 'igual', 'valor' => 'Campo 1-7'];
            } elseif (preg_match('/\bcampo\s*30\s*(?:\+|mas)\b/', $normalized)) {
                $filters[] = ['campo' => 'departamento', 'operador' => 'igual', 'valor' => 'Campo 30+'];
            }
            if (preg_match('/\bgestor|gestores\b/', $normalized)) {
                $filters[] = ['campo' => 'puesto', 'operador' => 'contiene', 'valor' => 'Gestor'];
            }
        } elseif ($dataset === 'candidatos') {
            if (preg_match('/\bvalidacion final\b/', $normalized)) {
                $filters[] = ['campo' => 'estatus', 'operador' => 'igual', 'valor' => 'Pendiente de validacion final'];
            } elseif (preg_match('/\b(por evaluar|revision|en revision)\b/', $normalized)) {
                $filters[] = ['campo' => 'estatus', 'operador' => 'igual', 'valor' => 'Por evaluar'];
            } elseif (preg_match('/\b(contratado|contratados|contratada|contratadas|pasaron a plantilla|ingresaron a plantilla)\b/', $normalized)) {
                $filters[] = ['campo' => 'estatus', 'operador' => 'igual', 'valor' => 'Contratado'];
            }
            if (preg_match('/\breingreso|reingresos\b/', $normalized)) {
                $filters[] = ['campo' => 'es_reingreso', 'operador' => 'verdadero', 'valor' => ''];
            }
        } elseif (preg_match('/\b(asignado|asignados|tengo|mis modulos|mis permisos)\b/', $normalized)) {
            $filters[] = ['campo' => 'asignado_al_usuario', 'operador' => 'verdadero', 'valor' => ''];
        }

        $period = [];
        $preset = match (true) {
            preg_match('/\bhoy\b/', $normalized) === 1 => 'hoy',
            preg_match('/\bayer\b/', $normalized) === 1 => 'ayer',
            preg_match('/\beste mes|mes actual\b/', $normalized) === 1 => 'mes_actual',
            preg_match('/\bmes anterior|mes pasado\b/', $normalized) === 1 => 'mes_anterior',
            preg_match('/\beste ano|ano actual\b/', $normalized) === 1 => 'anio_actual',
            preg_match('/\bultimos 7 dias\b/', $normalized) === 1 => 'ultimos_7_dias',
            preg_match('/\bultimos 30 dias\b/', $normalized) === 1 => 'ultimos_30_dias',
            default => '',
        };
        if ($preset !== '') {
            $dateField = $dataset === 'personal' ? 'fecha_ingreso' : 'fecha_registro';
            if ($dataset === 'candidatos' && $this->hasFilter($filters, 'estatus', 'Contratado')) {
                $dateField = 'contrato_firmado_en';
            }
            if ($dataset !== 'modulos') {
                $period = ['campo' => $dateField, 'preset' => $preset, 'inicio' => '', 'fin' => ''];
            }
        }

        return [
            'accion' => 'consultar_datos',
            'dataset' => $dataset,
            'operacion' => $operation,
            'filtros' => $filters,
            'agrupar_por' => $group,
            'periodo' => $period,
            'limite' => 20,
            'confianza' => 1.0,
        ];
    }

    /** @param array<int, array<string, mixed>> $filters */
    private function hasFilter(array $filters, string $field, string $value): bool
    {
        foreach ($filters as $filter) {
            if (($filter['campo'] ?? '') === $field && ($filter['valor'] ?? '') === $value) {
                return true;
            }
        }
        return false;
    }

    /** @return array<string, mixed> */
    private function catalogoPublico(): array
    {
        return [
            'personal' => [
                'descripcion' => 'Plantilla de colaboradores activos o de baja y su estructura laboral.',
                'campos' => [
                    'nombre', 'numero_empleado', 'estatus', 'empresa', 'direccion', 'area',
                    'departamento', 'puesto', 'fecha_ingreso', 'es_externo',
                ],
                'fechas' => ['fecha_ingreso'],
                'agrupaciones' => ['estatus', 'empresa', 'direccion', 'area', 'departamento', 'puesto', 'es_externo'],
                'sinonimos' => [
                    'usuarios, empleados, personas, colaboradores, plantilla' => 'personal',
                    'activos' => 'estatus igual Activo',
                    'bajas' => 'estatus igual Baja',
                ],
            ],
            'candidatos' => [
                'descripcion' => 'Personas en seleccion, evaluacion, validacion final, contratacion o proceso cerrado.',
                'campos' => [
                    'nombre', 'estatus', 'empresa', 'direccion', 'area', 'departamento', 'puesto',
                    'fecha_registro', 'fecha_ingreso_programada', 'contrato_firmado_en',
                    'es_reingreso', 'proceso_cerrado',
                ],
                'fechas' => ['fecha_registro', 'fecha_ingreso_programada', 'contrato_firmado_en'],
                'agrupaciones' => ['estatus', 'empresa', 'direccion', 'area', 'departamento', 'puesto', 'es_reingreso', 'proceso_cerrado'],
                'valores_estatus' => ['Por evaluar', 'Pendiente de validacion final', 'Contratado'],
            ],
            'modulos' => [
                'descripcion' => 'Catalogo funcional de modulos y permisos visibles en Sparta.',
                'campos' => ['nombre', 'categoria', 'descripcion', 'activo', 'asignado_al_usuario'],
                'agrupaciones' => ['categoria', 'activo', 'asignado_al_usuario'],
            ],
        ];
    }

    /** @param array<string, mixed> $plan */
    private function ejecutar(array $plan, int $actorId): array
    {
        $dataset = (string) ($plan['dataset'] ?? '');
        $operation = (string) ($plan['operacion'] ?? '');
        if (!in_array($dataset, ['personal', 'candidatos', 'modulos'], true)) {
            throw new \InvalidArgumentException('Dataset not allowed.');
        }
        if (!in_array($operation, ['conteo', 'lista', 'agrupacion'], true)) {
            throw new \InvalidArgumentException('Operation not allowed.');
        }

        $definition = $this->definition($dataset, $actorId);
        $params = $definition['params'];
        $where = $definition['base_where'];
        $filters = is_array($plan['filtros'] ?? null) ? $plan['filtros'] : [];
        foreach (array_slice($filters, 0, 8) as $index => $filter) {
            if (!is_array($filter)) {
                continue;
            }
            $condition = $this->compileFilter($filter, $definition['fields'], $params, $index);
            if ($condition !== '') {
                $where[] = $condition;
            }
        }
        $period = is_array($plan['periodo'] ?? null) ? $plan['periodo'] : [];
        $periodCondition = $this->compilePeriod($period, $definition['dates'], $params);
        if ($periodCondition !== '') {
            $where[] = $periodCondition;
        }

        $whereSql = $where ? "\n WHERE " . implode("\n   AND ", $where) : '';
        $db = new Database();
        if ($operation === 'conteo') {
            $sql = 'SELECT COUNT(DISTINCT ' . $definition['id'] . ') AS total '
                . $definition['from'] . $whereSql;
            $row = $db->queryOne($sql, $params);
            $total = (int) ($row['total'] ?? 0);
            return $this->countResponse($dataset, $total, $filters, $period);
        }

        if ($operation === 'agrupacion') {
            $group = (string) ($plan['agrupar_por'] ?? '');
            if (!isset($definition['groupable'][$group])) {
                throw new \InvalidArgumentException('Group field not allowed.');
            }
            $expression = $definition['groupable'][$group];
            $sql = 'SELECT ' . $expression . ' AS etiqueta, COUNT(DISTINCT ' . $definition['id'] . ') AS total '
                . $definition['from'] . $whereSql
                . ' GROUP BY ' . $expression . ' ORDER BY total DESC, etiqueta ASC LIMIT 50';
            $rows = $db->queryAll($sql, $params);
            $rows = array_map(static fn(array $row): array => [
                'nombre' => (string) ($row['etiqueta'] ?? 'Sin dato'),
                'detalle' => number_format((int) ($row['total'] ?? 0), 0, '.', ',')
                    . ((int) ($row['total'] ?? 0) === 1 ? ' registro' : ' registros'),
                'total' => (int) ($row['total'] ?? 0),
            ], $rows);
            $total = array_sum(array_column($rows, 'total'));
            return $this->reportResponse('Distribucion de ' . $this->datasetLabel($dataset) . ' por ' . $this->fieldLabel($group), $rows, $total);
        }

        $limit = max(1, min((int) ($plan['limite'] ?? 20), self::MAX_LIST_ROWS));
        $countSql = 'SELECT COUNT(DISTINCT ' . $definition['id'] . ') AS total '
            . $definition['from'] . $whereSql;
        $countRow = $db->queryOne($countSql, $params);
        $total = (int) ($countRow['total'] ?? 0);
        $sql = 'SELECT DISTINCT ' . implode(', ', $definition['select']) . ' '
            . $definition['from'] . $whereSql
            . ' ORDER BY ' . $definition['order'] . ' LIMIT ' . $limit;
        $rows = $db->queryAll($sql, $params);
        $rows = array_map([$this, 'normalizeRow'], $rows);
        return $this->reportResponse('Consulta de ' . $this->datasetLabel($dataset), $rows, $total);
    }

    /** @return array<string, mixed> */
    private function definition(string $dataset, int $actorId): array
    {
        $companyId = "COALESCE(p.id_empresa, pu.id_empresa, dep.id_empresa, area.id_empresa, dir.id_empresa, 1)";
        $companyName = "CASE WHEN {$companyId} = 2 THEN 'Furia Motos' ELSE 'MaxiKash' END";
        $structureFrom = " LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1"
            . " LEFT JOIN puesto pu ON pu.id = ap.id_puesto"
            . " LEFT JOIN departamento dep ON dep.id = pu.departamento_id"
            . " LEFT JOIN departamento_organizacional area ON area.id = dep.id_departamento_organizacional"
            . " LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = area.id AND COALESCE(ad.activo, 1) = 1"
            . " LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion";

        if ($dataset === 'personal') {
            return [
                'id' => 'p.id',
                'from' => 'FROM persona p' . $structureFrom,
                'params' => [],
                'base_where' => [],
                'fields' => [
                    'nombre' => "TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom))",
                    'numero_empleado' => 'p.numero_empleado',
                    'estatus' => "COALESCE(NULLIF(TRIM(p.estatus), ''), 'Activo')",
                    'empresa' => $companyName,
                    'direccion' => "COALESCE(NULLIF(TRIM(dir.nombre), ''), 'Sin direccion')",
                    'area' => "COALESCE(NULLIF(TRIM(area.nombre), ''), 'Sin area')",
                    'departamento' => "COALESCE(NULLIF(TRIM(dep.nombre), ''), 'Sin departamento')",
                    'puesto' => "COALESCE(NULLIF(TRIM(pu.nombre), ''), 'Sin puesto')",
                    'fecha_ingreso' => 'p.fecha_ingreso',
                    'es_externo' => 'COALESCE(p.es_externo, 0)',
                ],
                'dates' => ['fecha_ingreso' => 'p.fecha_ingreso'],
                'groupable' => [
                    'estatus' => "COALESCE(NULLIF(TRIM(p.estatus), ''), 'Activo')",
                    'empresa' => $companyName,
                    'direccion' => "COALESCE(NULLIF(TRIM(dir.nombre), ''), 'Sin direccion')",
                    'area' => "COALESCE(NULLIF(TRIM(area.nombre), ''), 'Sin area')",
                    'departamento' => "COALESCE(NULLIF(TRIM(dep.nombre), ''), 'Sin departamento')",
                    'puesto' => "COALESCE(NULLIF(TRIM(pu.nombre), ''), 'Sin puesto')",
                    'es_externo' => "CASE WHEN COALESCE(p.es_externo, 0) = 1 THEN 'Si' ELSE 'No' END",
                ],
                'select' => [
                    'p.id AS id',
                    "TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre",
                    'p.numero_empleado AS numero_empleado',
                    "COALESCE(NULLIF(TRIM(p.estatus), ''), 'Activo') AS estatus",
                    $companyName . ' AS empresa',
                    "COALESCE(NULLIF(TRIM(dep.nombre), ''), 'Sin departamento') AS departamento",
                    "COALESCE(NULLIF(TRIM(pu.nombre), ''), 'Sin puesto') AS puesto",
                ],
                'order' => 'nombre ASC',
            ];
        }

        if ($dataset === 'candidatos') {
            $candidateCompanyId = 'COALESCE(c.id_empresa, pu.id_empresa, dep.id_empresa, area.id_empresa, dir.id_empresa, 1)';
            $candidateCompanyName = "CASE WHEN {$candidateCompanyId} = 2 THEN 'Furia Motos' ELSE 'MaxiKash' END";
            $from = 'FROM candidatos c'
                . ' LEFT JOIN puesto pu ON pu.id = c.id_puesto'
                . ' LEFT JOIN departamento dep ON dep.id = c.id_departamento'
                . ' LEFT JOIN departamento_organizacional area ON area.id = dep.id_departamento_organizacional'
                . ' LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = area.id AND COALESCE(ad.activo, 1) = 1'
                . ' LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion';
            return [
                'id' => 'c.id',
                'from' => $from,
                'params' => [],
                'base_where' => [],
                'fields' => [
                    'nombre' => "TRIM(CONCAT_WS(' ', c.nombres, c.segundo_nombre, c.apellidop, c.apellidom))",
                    'estatus' => "COALESCE(NULLIF(TRIM(c.estatus), ''), 'Sin estatus')",
                    'empresa' => $candidateCompanyName,
                    'direccion' => "COALESCE(NULLIF(TRIM(dir.nombre), ''), 'Sin direccion')",
                    'area' => "COALESCE(NULLIF(TRIM(area.nombre), ''), 'Sin area')",
                    'departamento' => "COALESCE(NULLIF(TRIM(dep.nombre), ''), 'Sin departamento')",
                    'puesto' => "COALESCE(NULLIF(TRIM(pu.nombre), ''), 'Sin puesto')",
                    'fecha_registro' => 'c.fecha_registro',
                    'fecha_ingreso_programada' => 'c.fecha_ingreso_programada',
                    'contrato_firmado_en' => 'c.contrato_firmado_en',
                    'es_reingreso' => 'COALESCE(c.es_reingreso, 0)',
                    'proceso_cerrado' => 'COALESCE(c.proceso_cerrado, 0)',
                ],
                'dates' => [
                    'fecha_registro' => 'c.fecha_registro',
                    'fecha_ingreso_programada' => 'c.fecha_ingreso_programada',
                    'contrato_firmado_en' => 'c.contrato_firmado_en',
                ],
                'groupable' => [
                    'estatus' => "COALESCE(NULLIF(TRIM(c.estatus), ''), 'Sin estatus')",
                    'empresa' => $candidateCompanyName,
                    'direccion' => "COALESCE(NULLIF(TRIM(dir.nombre), ''), 'Sin direccion')",
                    'area' => "COALESCE(NULLIF(TRIM(area.nombre), ''), 'Sin area')",
                    'departamento' => "COALESCE(NULLIF(TRIM(dep.nombre), ''), 'Sin departamento')",
                    'puesto' => "COALESCE(NULLIF(TRIM(pu.nombre), ''), 'Sin puesto')",
                    'es_reingreso' => "CASE WHEN COALESCE(c.es_reingreso, 0) = 1 THEN 'Si' ELSE 'No' END",
                    'proceso_cerrado' => "CASE WHEN COALESCE(c.proceso_cerrado, 0) = 1 THEN 'Si' ELSE 'No' END",
                ],
                'select' => [
                    'c.id AS id',
                    "TRIM(CONCAT_WS(' ', c.nombres, c.segundo_nombre, c.apellidop, c.apellidom)) AS nombre",
                    "COALESCE(NULLIF(TRIM(c.estatus), ''), 'Sin estatus') AS estatus",
                    $candidateCompanyName . ' AS empresa',
                    "COALESCE(NULLIF(TRIM(dep.nombre), ''), 'Sin departamento') AS departamento",
                    "COALESCE(NULLIF(TRIM(pu.nombre), ''), 'Sin puesto') AS puesto",
                    'c.fecha_registro AS fecha',
                ],
                'order' => 'c.fecha_registro DESC, c.id DESC',
            ];
        }

        return [
            'id' => 'm.id',
            'from' => 'FROM modulos_web m',
            'params' => ['actor_id' => $actorId],
            'base_where' => [],
            'fields' => [
                'nombre' => 'm.nombre',
                'categoria' => "COALESCE(NULLIF(TRIM(m.pestana), ''), 'Otros')",
                'descripcion' => "COALESCE(m.descripcion, '')",
                'activo' => 'COALESCE(m.activo, 1)',
                'asignado_al_usuario' => 'EXISTS (SELECT 1 FROM asigna_modulo_web am WHERE am.modulo_web_id = m.id AND am.usuario_id = :actor_id)',
            ],
            'dates' => [],
            'groupable' => [
                'categoria' => "COALESCE(NULLIF(TRIM(m.pestana), ''), 'Otros')",
                'activo' => "CASE WHEN COALESCE(m.activo, 1) = 1 THEN 'Activo' ELSE 'Inactivo' END",
                'asignado_al_usuario' => "CASE WHEN EXISTS (SELECT 1 FROM asigna_modulo_web am WHERE am.modulo_web_id = m.id AND am.usuario_id = :actor_id) THEN 'Asignado' ELSE 'No asignado' END",
            ],
            'select' => [
                'm.id AS id',
                'm.nombre AS nombre',
                "COALESCE(NULLIF(TRIM(m.pestana), ''), 'Otros') AS categoria",
                "COALESCE(m.descripcion, '') AS detalle",
                "CASE WHEN EXISTS (SELECT 1 FROM asigna_modulo_web am WHERE am.modulo_web_id = m.id AND am.usuario_id = :actor_id) THEN 'Asignado' ELSE 'No asignado' END AS estatus",
            ],
            'order' => 'categoria ASC, nombre ASC',
        ];
    }

    /** @param array<string, mixed> $filter @param array<string, string> $fields @param array<string, mixed> $params */
    private function compileFilter(array $filter, array $fields, array &$params, int $index): string
    {
        $field = (string) ($filter['campo'] ?? '');
        $operator = (string) ($filter['operador'] ?? 'igual');
        if (!isset($fields[$field])) {
            throw new \InvalidArgumentException('Filter field not allowed.');
        }
        $expression = $fields[$field];
        if ($operator === 'nulo' || $operator === 'no_nulo') {
            return $expression . ($operator === 'nulo' ? ' IS NULL' : ' IS NOT NULL');
        }
        if ($operator === 'verdadero' || $operator === 'falso') {
            return '(' . $expression . ') = ' . ($operator === 'verdadero' ? '1' : '0');
        }

        $value = $filter['valor'] ?? '';
        if ($operator === 'en') {
            $values = is_array($value) ? array_values($value) : [$value];
            $values = array_slice(array_filter($values, static fn($item): bool => is_scalar($item) && trim((string) $item) !== ''), 0, 12);
            if (!$values) {
                throw new \InvalidArgumentException('Empty IN filter.');
            }
            $placeholders = [];
            foreach ($values as $valueIndex => $item) {
                $key = 'f' . $index . '_' . $valueIndex;
                $params[$key] = $this->normalizeValue($field, (string) $item);
                $placeholders[] = ':' . $key;
            }
            return $expression . ' IN (' . implode(', ', $placeholders) . ')';
        }

        if (!in_array($operator, ['igual', 'distinto', 'contiene'], true) || !is_scalar($value)) {
            throw new \InvalidArgumentException('Filter operator not allowed.');
        }
        $key = 'f' . $index;
        $value = $this->normalizeValue($field, trim((string) $value));
        $params[$key] = $operator === 'contiene' ? '%' . $value . '%' : $value;
        $sqlOperator = $operator === 'distinto' ? '<>' : ($operator === 'contiene' ? 'LIKE' : '=');
        return $expression . ' ' . $sqlOperator . ' :' . $key;
    }

    /** @param array<string, mixed> $period @param array<string, string> $dateFields @param array<string, mixed> $params */
    private function compilePeriod(array $period, array $dateFields, array &$params): string
    {
        $field = (string) ($period['campo'] ?? '');
        if ($field === '') {
            return '';
        }
        if (!isset($dateFields[$field])) {
            throw new \InvalidArgumentException('Date field not allowed.');
        }

        $range = $this->dateRange((string) ($period['preset'] ?? ''), (string) ($period['inicio'] ?? ''), (string) ($period['fin'] ?? ''));
        if ($range === null) {
            throw new \InvalidArgumentException('Invalid date range.');
        }
        $params['period_start'] = $range[0];
        $params['period_end'] = $range[1];
        return $dateFields[$field] . ' >= :period_start AND ' . $dateFields[$field] . ' < DATE_ADD(:period_end, INTERVAL 1 DAY)';
    }

    /** @return array{0:string,1:string}|null */
    private function dateRange(string $preset, string $start, string $end): ?array
    {
        $today = new \DateTimeImmutable('today');
        $ranges = [
            'hoy' => [$today, $today],
            'ayer' => [$today->modify('-1 day'), $today->modify('-1 day')],
            'semana_actual' => [$today->modify('monday this week'), $today->modify('sunday this week')],
            'mes_actual' => [$today->modify('first day of this month'), $today->modify('last day of this month')],
            'mes_anterior' => [$today->modify('first day of last month'), $today->modify('last day of last month')],
            'anio_actual' => [$today->setDate((int) $today->format('Y'), 1, 1), $today->setDate((int) $today->format('Y'), 12, 31)],
            'ultimos_7_dias' => [$today->modify('-6 days'), $today],
            'ultimos_30_dias' => [$today->modify('-29 days'), $today],
        ];
        if (isset($ranges[$preset])) {
            return [$ranges[$preset][0]->format('Y-m-d'), $ranges[$preset][1]->format('Y-m-d')];
        }
        if (!$this->validDate($start) || !$this->validDate($end) || $start > $end) {
            return null;
        }
        return [$start, $end];
    }

    private function validDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value;
    }

    private function normalizeValue(string $field, string $value): string
    {
        $normalized = $this->normalize($value);
        if ($field === 'empresa') {
            return str_contains($normalized, 'furia') || str_contains($normalized, 'pensionamax') ? 'Furia Motos' : 'MaxiKash';
        }
        if ($field === 'estatus') {
            if (in_array($normalized, ['activo', 'activos', 'activa', 'activas'], true)) {
                return 'Activo';
            }
            if (in_array($normalized, ['baja', 'bajas', 'inactivo', 'inactivos'], true)) {
                return 'Baja';
            }
            if (str_contains($normalized, 'validacion final')) {
                return 'Pendiente de validacion final';
            }
            if (str_contains($normalized, 'evaluar') || str_contains($normalized, 'revision')) {
                return 'Por evaluar';
            }
            if (str_contains($normalized, 'contrat')) {
                return 'Contratado';
            }
        }
        if ($field === 'departamento') {
            if (preg_match('/campo\s*1\s*(?:-|a)\s*7/', $normalized)) {
                return 'Campo 1-7';
            }
            if (preg_match('/campo\s*30\s*(?:\+|mas)/', $normalized)) {
                return 'Campo 30+';
            }
        }
        return $value;
    }

    /** @param array<int, mixed> $filters @param array<string, mixed> $period */
    private function countResponse(string $dataset, int $total, array $filters, array $period): array
    {
        $label = $total === 1 ? $this->datasetSingularLabel($dataset) : $this->datasetLabel($dataset);
        $context = $this->filterSummary($filters, $period);
        return [
            'mensaje' => 'El resultado actual es ' . number_format($total, 0, '.', ',') . ' ' . $label . ($context !== '' ? ' ' . $context : '') . '.',
            'tipo' => 'consulta_semantica',
            'metricas' => ['total' => $total, 'dataset' => $dataset, 'criterio' => $context !== '' ? $context : 'sin filtros'],
            'ia_disponible' => true,
            'modelo_ia' => 'Qwen + consulta segura de Sparta',
        ];
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function reportResponse(string $title, array $rows, int $total): array
    {
        $recordLabel = $total === 1 ? 'registro' : 'registros';
        return [
            'mensaje' => 'Prepare ' . number_format($total, 0, '.', ',') . ' ' . $recordLabel . ' para "' . $title . '".',
            'tipo' => 'consulta_semantica',
            'reporte' => ['titulo' => $title, 'total' => $total, 'filas' => $rows],
            'ia_disponible' => true,
            'modelo_ia' => 'Qwen + consulta segura de Sparta',
        ];
    }

    /** @param array<string, mixed> $row @return array<string, mixed> */
    private function normalizeRow(array $row): array
    {
        $result = [];
        foreach ($row as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $result[(string) $key] = $value === null ? '' : (string) $value;
            }
        }
        return $result;
    }

    /** @param array<int, mixed> $filters @param array<string, mixed> $period */
    private function filterSummary(array $filters, array $period): string
    {
        $parts = [];
        foreach ($filters as $filter) {
            if (!is_array($filter) || empty($filter['campo'])) {
                continue;
            }
            $value = $filter['valor'] ?? ($filter['operador'] ?? '');
            if (in_array((string) ($filter['operador'] ?? ''), ['verdadero', 'falso'], true)) {
                $value = ($filter['operador'] ?? '') === 'verdadero' ? 'Si' : 'No';
            }
            $value = is_array($value) ? implode(', ', array_map('strval', $value)) : (string) $value;
            $parts[] = 'con ' . $this->fieldLabel((string) $filter['campo']) . ' ' . $value;
        }
        if (!empty($period['campo'])) {
            $parts[] = 'en el periodo ' . ((string) ($period['preset'] ?? '') ?: ((string) ($period['inicio'] ?? '') . ' a ' . (string) ($period['fin'] ?? '')));
        }
        return implode(' y ', $parts);
    }

    private function datasetLabel(string $dataset): string
    {
        return match ($dataset) {
            'personal' => 'personas de plantilla',
            'candidatos' => 'candidatos',
            'modulos' => 'modulos',
            default => 'registros',
        };
    }

    private function datasetSingularLabel(string $dataset): string
    {
        return match ($dataset) {
            'personal' => 'persona de plantilla',
            'candidatos' => 'candidato',
            'modulos' => 'modulo',
            default => 'registro',
        };
    }

    private function fieldLabel(string $field): string
    {
        return str_replace('_', ' ', $field);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return $ascii === false ? $value : $ascii;
    }
}
