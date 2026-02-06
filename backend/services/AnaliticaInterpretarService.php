<?php

/**
 * Interpretación de analíticas con schema estricto y reglas anti-alucinación.
 * La IA solo interpreta métricas y prioriza; NO escribe párrafos ni diseña UI.
 * Salida: overall_confidence, status_flags, key_findings (con worst_gestor), anomalies_detected, recommended_actions.
 */

namespace Services;

class AnaliticaInterpretarService
{
    private const CACHE_DIR = __DIR__ . '/../storage/cache';
    private const CACHE_TTL = 3600;
    private const MAX_RAW_CHARS = 4000;
    private const PROMPT_RETRY = 1;
    private const UMBRAL_CUMPLIMIENTO_CRITICO = 30;
    private const UMBRAL_DISTANCIA_KM_GESTOR_LEJANO = 1.0;

    /**
     * @param array $input [analitica_espacial, analitica_pagos, analitica_gestiones, metadata]
     * @param callable|null $llmFn (systemPrompt, userPrompt, maxTokens) => ['success'=>bool, 'texto'=>string]
     * @return array [success, data, mensaje?, cache_hit?, from_llm?, validation_error?]
     */
    public function interpretar(array $input, ?callable $llmFn = null): array
    {
        $metadata = $input['metadata'] ?? [];
        $idCredito = (int) ($metadata['idCredito'] ?? 0);
        $fechaActual = $metadata['fecha_actual'] ?? date('c');
        $nowTs = strtotime($fechaActual) ?: time();

        $espacial = $this->normalizarEspacial($input['analitica_espacial'] ?? []);
        $pagos = $this->normalizarPagos($input['analitica_pagos'] ?? [], $fechaActual);
        $gestiones = $this->normalizarGestiones($input['analitica_gestiones'] ?? []);

        $features = $this->calcularFeatures($espacial, $pagos, $gestiones, $nowTs);
        $worstGestor = $this->detectarWorstGestor($gestiones);
        $reglas = $this->aplicarReglasDeterministas($features, $worstGestor);

        $cacheKey = $idCredito > 0 ? 'interpretar:' . $idCredito . ':' . md5(json_encode($features) . json_encode($worstGestor)) : null;
        if ($cacheKey) {
            $cached = $this->cacheGet($cacheKey);
            if ($cached !== null) {
                return ['success' => true, 'data' => $cached, 'cache_hit' => true];
            }
        }

        $fallback = $this->buildFallbackDesdeReglas($features, $reglas, $worstGestor);
        if ($llmFn === null) {
            return ['success' => true, 'data' => $fallback, 'cache_hit' => false];
        }

        $rawData = $this->buildRawDataForPrompt($espacial, $pagos, $gestiones, $features, $reglas, $worstGestor);
        $prompt = $this->buildPrompt($features, $reglas, $worstGestor, $rawData);
        $systemPrompt = $this->getSystemPromptAntiAlucinacion();

        $jsonOut = null;
        for ($intento = 0; $intento <= self::PROMPT_RETRY; $intento++) {
            $result = $llmFn($systemPrompt, $prompt, 2048);
            if (!($result['success'] ?? false) || empty($result['texto'])) {
                break;
            }
            $jsonOut = $this->extraerYValidarJson($result['texto']);
            if ($jsonOut !== null) {
                break;
            }
        }

        $data = $jsonOut !== null ? $this->asegurarSchema($jsonOut) : $fallback;
        $data = $this->fusionarWorstGestorYNumeros($data, $worstGestor, $features);
        $validationError = $this->validarTextoVsNumeros($data, $features);
        $data = $this->aplicarMetricasVerificadas($data, $features, $worstGestor);
        if ($validationError !== null) {
            $data = $this->corregirContradicciones($data, $features, $worstGestor);
        }
        $data = $this->asegurarSchemaCompleto($data, $features, $worstGestor);
        $data['metricas_verificadas'] = $this->extraerMetricasVerificadas($features);
        $data['missing_data'] = $this->detectarDatosFaltantes($features);
        $data['overall_confidence'] = $this->ajustarConfianzaPorDatosFaltantes(
            (float) ($data['overall_confidence'] ?? 0.5),
            $data['missing_data']
        );

        if ($cacheKey && $data) {
            $this->cacheSet($cacheKey, $data);
        }

        return [
            'success' => true,
            'data' => $data,
            'cache_hit' => false,
            'from_llm' => $jsonOut !== null,
            'validation_error' => $validationError,
        ];
    }

    /**
     * Detecta el gestor con mayor incumplimiento: más visitas lejanas o mayor distancia promedio (> 1 km).
     */
    private function detectarWorstGestor(array $gestiones): ?array
    {
        $raw = $gestiones['raw_detalles'] ?? [];
        if (!is_array($raw) || empty($raw)) {
            return null;
        }
        $porGestor = [];
        foreach ($raw as $d) {
            $nombre = trim((string) ($d['gestor_nombre'] ?? $d['gestor_id'] ?? '—'));
            if ($nombre === '' || $nombre === '—') {
                continue;
            }
            if (!isset($porGestor[$nombre])) {
                $porGestor[$nombre] = ['visitas_lejanas' => 0, 'distancia_sum_m' => 0, 'total' => 0];
            }
            $cerca = $d['cerca'] ?? false;
            $distancia_m = isset($d['distancia_m']) ? (float) $d['distancia_m'] : null;
            $porGestor[$nombre]['total']++;
            if (!$cerca) {
                $porGestor[$nombre]['visitas_lejanas']++;
            }
            if ($distancia_m !== null) {
                $porGestor[$nombre]['distancia_sum_m'] += $distancia_m;
            }
        }
        $candidatos = [];
        foreach ($porGestor as $nombre => $stats) {
            $total = $stats['total'];
            $visitasLejanas = $stats['visitas_lejanas'];
            $distanciaPromedioKm = $total > 0 && $stats['distancia_sum_m'] > 0
                ? round($stats['distancia_sum_m'] / 1000.0 / $total, 2)
                : 0.0;
            if ($visitasLejanas > 0 || $distanciaPromedioKm >= self::UMBRAL_DISTANCIA_KM_GESTOR_LEJANO) {
                $candidatos[] = [
                    'nombre' => $nombre,
                    'distancia_promedio_km' => $distanciaPromedioKm,
                    'visitas_lejanas' => $visitasLejanas,
                    'total_visitas' => $total,
                ];
            }
        }
        if (empty($candidatos)) {
            return null;
        }
        usort($candidatos, function ($a, $b) {
            if ($b['visitas_lejanas'] !== $a['visitas_lejanas']) {
                return $b['visitas_lejanas'] <=> $a['visitas_lejanas'];
            }
            return ($b['distancia_promedio_km'] <=> $a['distancia_promedio_km']);
        });
        $w = $candidatos[0];
        return [
            'nombre' => $w['nombre'],
            'motivo' => $w['visitas_lejanas'] > 0
                ? 'Visitas reiteradas lejos del domicilio del cliente.'
                : 'Distancia promedio elevada respecto al domicilio.',
            'distancia_promedio_km' => $w['distancia_promedio_km'],
            'visitas_lejanas' => $w['visitas_lejanas'],
        ];
    }

    private function normalizarEspacial($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $distancias = $raw['distancias_a_casa'] ?? (isset($raw[0]) && is_array($raw[0]) ? $raw : []);
        $out = [];
        foreach (is_array($distancias) ? $distancias : [] as $r) {
            if (!is_array($r)) {
                continue;
            }
            $out[] = [
                'name' => $r['label'] ?? $r['name'] ?? '—',
                'visits' => (int) ($r['visitas_count'] ?? $r['visits'] ?? 0),
                'last_date' => $r['ultima_fecha'] ?? $r['last_date'] ?? null,
                'distance_m' => isset($r['distancia_m']) ? (float) $r['distancia_m'] : (isset($r['distance_m']) ? (float) $r['distance_m'] : null),
            ];
        }
        return $out;
    }

    private function normalizarPagos($raw, string $fechaActual): array
    {
        if (!is_array($raw)) {
            return [];
        }
        return [
            'last_payment_date' => $raw['last_payment_date'] ?? null,
            'estado_actual' => $raw['estado_actual'] ?? null,
            'dias_mora' => isset($raw['dias_mora']) ? (int) $raw['dias_mora'] : null,
            'promesa_pago' => $raw['promesa_pago'] ?? null,
            'monto_prometido' => $raw['monto_prometido'] ?? null,
            'total_deuda' => $raw['total_deuda'] ?? null,
            'total_pagos' => (int) ($raw['total_pagos'] ?? 0),
        ];
    }

    private function normalizarGestiones($raw): array
    {
        if (!is_array($raw)) {
            return ['porcentaje_cumplimiento' => null, 'raw_detalles' => []];
        }
        $lista = $raw['detalles'] ?? (isset($raw[0]) && is_array($raw[0]) ? $raw : []);
        $out = [];
        $pct = isset($raw['porcentaje_cumplimiento']) ? (float) $raw['porcentaje_cumplimiento'] : null;
        foreach (is_array($lista) ? $lista : [] as $d) {
            if (!is_array($d)) {
                continue;
            }
            $out[] = [
                'gestor_nombre' => $d['gestor_nombre'] ?? $d['gestor_id'] ?? '—',
                'timestamp' => $d['timestamp'] ?? $d['fecha'] ?? null,
                'distancia_m' => isset($d['distancia_m']) ? (float) $d['distancia_m'] : null,
                'cerca' => !empty($d['cerca']),
            ];
        }
        return [
            'porcentaje_cumplimiento' => $pct,
            'raw_detalles' => $out,
        ];
    }

    private function calcularFeatures(array $espacial, array $pagos, array $gestiones, int $nowTs): array
    {
        $lastPaymentDays = null;
        if (!empty($pagos['last_payment_date'])) {
            $t = is_numeric($pagos['last_payment_date']) ? (int) $pagos['last_payment_date'] : strtotime($pagos['last_payment_date']);
            $lastPaymentDays = $t ? (int) floor(($nowTs - $t) / 86400) : null;
        }

        $homeFlag = false;
        $visitas30d = 0;
        foreach ($espacial as $loc) {
            $d = $loc['distance_m'] ?? null;
            if ($d !== null && $d <= 100) {
                $homeFlag = true;
            }
            $ld = $loc['last_date'] ?? null;
            if ($ld) {
                $ts = is_numeric($ld) ? (int) $ld : strtotime($ld);
                if ($ts && ($nowTs - $ts) <= 30 * 86400) {
                    $visitas30d += (int) ($loc['visits'] ?? 0);
                }
            }
        }

        $cumplimientoPromedio = $gestiones['porcentaje_cumplimiento'];
        $promesaVencida = false;
        $promesa = $pagos['promesa_pago'] ?? null;
        if ($promesa !== null && $promesa !== '') {
            $t = is_numeric($promesa) ? (int) $promesa : strtotime($promesa);
            if ($t && $t < $nowTs) {
                $promesaVencida = true;
            }
        }

        return [
            'last_payment_days' => $lastPaymentDays,
            'home_flag' => $homeFlag,
            'avg_visitas_30d' => $visitas30d,
            'cumplimiento_promedio' => $cumplimientoPromedio,
            'promesa_vencida' => $promesaVencida,
            'total_pagos' => (int) ($pagos['total_pagos'] ?? 0),
            'dias_mora' => $pagos['dias_mora'] ?? null,
        ];
    }

    private function aplicarReglasDeterministas(array $f, ?array $worstGestor): array
    {
        $reglas = [];
        if ($f['cumplimiento_promedio'] !== null && $f['cumplimiento_promedio'] < self::UMBRAL_CUMPLIMIENTO_CRITICO) {
            $reglas['gestion_estado'] = 'critico';
            $reglas['gestion_motivo'] = 'Cumplimiento en campo menor al 30%.';
        }
        if ($worstGestor !== null) {
            $reglas['worst_gestor'] = $worstGestor;
        }
        if ($f['last_payment_days'] !== null && $f['last_payment_days'] > 60 && $f['promesa_vencida']) {
            $reglas['pagos_estado'] = 'riesgo';
        }
        if ($f['home_flag'] && $f['avg_visitas_30d'] >= 3) {
            $reglas['cliente_estado'] = 'positivo';
        }
        return $reglas;
    }

    private function buildFallbackDesdeReglas(array $features, array $reglas, ?array $worstGestor): array
    {
        $cumplimiento = $features['cumplimiento_promedio'];
        $statusGestion = ($cumplimiento !== null && $cumplimiento < self::UMBRAL_CUMPLIMIENTO_CRITICO) ? 'critico' : ($cumplimiento !== null && $cumplimiento >= 70 ? 'positivo' : 'neutral');
        $statusCliente = $features['home_flag'] ? 'positivo' : ($features['avg_visitas_30d'] > 0 ? 'neutral' : 'riesgo');
        $statusPagos = 'neutral';
        if ($features['last_payment_days'] !== null && $features['last_payment_days'] > 60 && $features['promesa_vencida']) {
            $statusPagos = 'riesgo';
        } elseif ($features['total_pagos'] > 0 && ($features['last_payment_days'] === null || $features['last_payment_days'] <= 30)) {
            $statusPagos = 'positivo';
        }

        $confianzaGestion = $cumplimiento !== null ? $cumplimiento / 100.0 : 0.5;
        $confianzaCliente = $features['home_flag'] ? 0.75 : 0.5;
        $confianzaPagos = $statusPagos === 'positivo' ? 0.7 : ($statusPagos === 'riesgo' ? 0.3 : 0.5);

        $keyFindingsGestion = [
            'summary' => $statusGestion === 'critico' ? 'El desempeño en campo es bajo.' : ($statusGestion === 'positivo' ? 'Gestión en campo adecuada.' : 'Gestión en campo regular.'),
            'confidence' => $confianzaGestion,
            'worst_gestor' => $worstGestor,
        ];

        return [
            'overall_confidence' => ($confianzaCliente + $confianzaGestion + $confianzaPagos) / 3,
            'status_flags' => [
                'cliente' => $statusCliente,
                'gestion' => $statusGestion,
                'pagos' => $statusPagos,
            ],
            'key_findings' => [
                'cliente' => [
                    'summary' => $features['home_flag'] ? 'Domicilio con evidencia de visitas cercanas.' : 'Datos de ubicación insuficientes o sin confirmar.',
                    'confidence' => $confianzaCliente,
                ],
                'gestion' => $keyFindingsGestion,
                'pagos' => [
                    'summary' => $statusPagos === 'positivo' ? 'Hábito de pago activo.' : ($statusPagos === 'riesgo' ? 'Riesgo de pago elevado.' : 'Pagos irregulares o datos limitados.'),
                    'confidence' => $confianzaPagos,
                ],
            ],
            'anomalies_detected' => $worstGestor !== null ? [
                [
                    'tipo' => 'gestor_lejano_recurrente',
                    'descripcion' => 'Gestor con visitas reiteradas lejos del domicilio.',
                    'evidencia' => $worstGestor['nombre'] . ': ' . ($worstGestor['visitas_lejanas'] ?? 0) . ' visitas lejanas.',
                ],
            ] : [],
            'recommended_actions' => $this->generarAccionesDesdeReglas($features, $reglas, $worstGestor),
        ];
    }

    private function generarAccionesDesdeReglas(array $features, array $reglas, ?array $worstGestor): array
    {
        $acciones = [];
        if ($worstGestor !== null) {
            $acciones[] = [
                'accion' => 'Reasignar o auditar gestor con mayor incumplimiento',
                'prioridad' => 'alta',
                'justificacion' => 'Visitas reiteradas lejos del domicilio del cliente.',
            ];
        }
        if (($features['cumplimiento_promedio'] ?? 100) < self::UMBRAL_CUMPLIMIENTO_CRITICO) {
            $acciones[] = [
                'accion' => 'Reforzar supervisión de gestiones en campo',
                'prioridad' => 'alta',
                'justificacion' => 'Cumplimiento en campo menor al 30%.',
            ];
        }
        if (($features['home_flag'] ?? false) && ($features['avg_visitas_30d'] ?? 0) > 0) {
            $acciones[] = [
                'accion' => 'Verificar ubicación externa detectada si aplica',
                'prioridad' => 'media',
                'justificacion' => 'Actividad en ubicaciones distintas al domicilio.',
            ];
        }
        if (empty($acciones)) {
            $acciones[] = [
                'accion' => 'Mantener seguimiento habitual',
                'prioridad' => 'baja',
                'justificacion' => 'Sin señales críticas en los datos actuales.',
            ];
        }
        return array_slice($acciones, 0, 5);
    }

    private function getSystemPromptAntiAlucinacion(): string
    {
        return <<<PROMPT
Eres un analista de riesgo. Recibes métricas ya calculadas (cumplimiento de gestor, pagos, ubicación).
DEBES responder ÚNICAMENTE con un objeto JSON válido, sin markdown ni texto alrededor.

REGLAS OBLIGATORIAS (anti-alucinación):
- Si cumplimiento_gestor < 30% → status_flags.gestion DEBE ser "critico". NO puedes poner "positivo" ni "neutral" en ese caso.
- El worst_gestor (si se proporciona en los datos) ya está calculado; NO inventes otro. Usa los números proporcionados.
- NO inventes porcentajes ni calificativos no soportados por los datos.
- Toda conclusión debe poder justificarse con los números entregados.
- Si hay contradicción entre un número y un texto, el número gana y debes corregir el texto.

Schema JSON estricto (responde solo con esto):
{
  "overall_confidence": número 0..1,
  "status_flags": {
    "cliente": "positivo|neutral|riesgo",
    "gestion": "positivo|neutral|critico",
    "pagos": "positivo|neutral|riesgo"
  },
  "key_findings": {
    "cliente": { "summary": "string corto", "confidence": 0..1 },
    "gestion": { "summary": "string corto", "confidence": 0..1, "worst_gestor": null o { "nombre", "motivo", "distancia_promedio_km", "visitas_lejanas" } },
    "pagos": { "summary": "string corto", "confidence": 0..1 }
  },
  "anomalies_detected": [ { "tipo": "gestor_lejano_recurrente|patron_raro|contradiccion", "descripcion": "string corto", "evidencia": "referencia" } ],
  "recommended_actions": [ { "accion": "string", "prioridad": "alta|media|baja", "justificacion": "string corto" } ]
}

Máximo 2 frases por summary. Sin exageraciones. Idioma: español.
PROMPT;
    }

    private function buildRawDataForPrompt(array $espacial, array $pagos, array $gestiones, array $features, array $reglas, ?array $worstGestor): string
    {
        $payload = [
            'cumplimiento_promedio' => $features['cumplimiento_promedio'],
            'home_flag' => $features['home_flag'],
            'last_payment_days' => $features['last_payment_days'],
            'total_pagos' => $features['total_pagos'],
            'dias_mora' => $features['dias_mora'],
            'worst_gestor_calculado' => $worstGestor,
            'reglas_aplicadas' => array_keys($reglas),
        ];
        $s = json_encode($payload, JSON_UNESCAPED_UNICODE);
        return strlen($s) > self::MAX_RAW_CHARS ? substr($s, 0, self::MAX_RAW_CHARS) . '...' : $s;
    }

    private function buildPrompt(array $features, array $reglas, ?array $worstGestor, string $rawData): string
    {
        $text = "Métricas calculadas:\n";
        $text .= "- cumplimiento_promedio (gestor): " . ($features['cumplimiento_promedio'] !== null ? $features['cumplimiento_promedio'] . '%' : 'N/A') . "\n";
        $text .= "- home_flag (domicilio): " . ($features['home_flag'] ? 'true' : 'false') . "\n";
        $text .= "- last_payment_days: " . ($features['last_payment_days'] ?? 'N/A') . "\n";
        $text .= "- total_pagos: " . ($features['total_pagos'] ?? 0) . "\n";
        $text .= "- dias_mora: " . ($features['dias_mora'] ?? 'N/A') . "\n";
        if ($worstGestor !== null) {
            $text .= "- worst_gestor (ya calculado): " . json_encode($worstGestor, JSON_UNESCAPED_UNICODE) . "\n";
        }
        $text .= "\nDatos brutos:\n" . $rawData . "\n\n";
        $text .= "Responde ÚNICAMENTE con el JSON del schema indicado en el system prompt. Respeta las reglas anti-alucinación.";
        return $text;
    }

    private function extraerYValidarJson(string $texto): ?array
    {
        $texto = trim($texto);
        $texto = preg_replace('/^```\s*json?\s*/i', '', $texto);
        $texto = preg_replace('/```\s*$/i', '', $texto);
        $texto = trim($texto);
        $data = @json_decode($texto, true);
        return is_array($data) ? $data : null;
    }

    private function asegurarSchema(array $data): array
    {
        $status = function ($v, $opciones) {
            $v = strtolower(trim((string) $v));
            return in_array($v, $opciones, true) ? $v : $opciones[1];
        };
        return [
            'overall_confidence' => isset($data['overall_confidence']) ? (float) $data['overall_confidence'] : 0.5,
            'status_flags' => [
                'cliente' => $status($data['status_flags']['cliente'] ?? '', ['positivo', 'neutral', 'riesgo']),
                'gestion' => $status($data['status_flags']['gestion'] ?? '', ['positivo', 'neutral', 'critico']),
                'pagos' => $status($data['status_flags']['pagos'] ?? '', ['positivo', 'neutral', 'riesgo']),
            ],
            'key_findings' => [
                'cliente' => [
                    'summary' => isset($data['key_findings']['cliente']['summary']) ? mb_substr((string) $data['key_findings']['cliente']['summary'], 0, 200) : '',
                    'confidence' => isset($data['key_findings']['cliente']['confidence']) ? (float) $data['key_findings']['cliente']['confidence'] : 0.5,
                ],
                'gestion' => [
                    'summary' => isset($data['key_findings']['gestion']['summary']) ? mb_substr((string) $data['key_findings']['gestion']['summary'], 0, 200) : '',
                    'confidence' => isset($data['key_findings']['gestion']['confidence']) ? (float) $data['key_findings']['gestion']['confidence'] : 0.5,
                    'worst_gestor' => isset($data['key_findings']['gestion']['worst_gestor']) && is_array($data['key_findings']['gestion']['worst_gestor'])
                        ? $data['key_findings']['gestion']['worst_gestor'] : null,
                ],
                'pagos' => [
                    'summary' => isset($data['key_findings']['pagos']['summary']) ? mb_substr((string) $data['key_findings']['pagos']['summary'], 0, 200) : '',
                    'confidence' => isset($data['key_findings']['pagos']['confidence']) ? (float) $data['key_findings']['pagos']['confidence'] : 0.5,
                ],
            ],
            'anomalies_detected' => is_array($data['anomalies_detected'] ?? null) ? $data['anomalies_detected'] : [],
            'recommended_actions' => is_array($data['recommended_actions'] ?? null) ? $data['recommended_actions'] : [],
        ];
    }

    private function fusionarWorstGestorYNumeros(array $data, ?array $worstGestor, array $features): array
    {
        if ($worstGestor !== null && isset($data['key_findings']['gestion'])) {
            $data['key_findings']['gestion']['worst_gestor'] = $worstGestor;
        }
        return $data;
    }

    /**
     * Valida que ningún status_flags ni porcentaje de la IA contradiga los números.
     */
    private function validarTextoVsNumeros(array $data, array $features): ?string
    {
        $cumplimiento = $features['cumplimiento_promedio'];
        $gestion = strtolower((string) ($data['status_flags']['gestion'] ?? ''));
        if ($cumplimiento !== null && $cumplimiento < self::UMBRAL_CUMPLIMIENTO_CRITICO && $gestion === 'positivo') {
            return 'status_flags.gestion no puede ser positivo con cumplimiento < 30%';
        }
        $confGestion = isset($data['key_findings']['gestion']['confidence']) ? (float) $data['key_findings']['gestion']['confidence'] : null;
        if ($cumplimiento !== null && $confGestion !== null && abs($confGestion * 100 - $cumplimiento) > 5) {
            return 'key_findings.gestion.confidence no coincide con cumplimiento real';
        }
        return null;
    }

    /**
     * OBLIGATORIO: sobrescribe con datos reales. La IA no manda; los números sí.
     */
    private function aplicarMetricasVerificadas(array $data, array $features, ?array $worstGestor): array
    {
        $cumplimiento = $features['cumplimiento_promedio'];
        if ($cumplimiento !== null) {
            $data['status_flags']['gestion'] = $cumplimiento < self::UMBRAL_CUMPLIMIENTO_CRITICO ? 'critico' : ($cumplimiento >= 70 ? 'positivo' : 'neutral');
            if (isset($data['key_findings']['gestion'])) {
                $data['key_findings']['gestion']['confidence'] = $cumplimiento / 100.0;
                $data['key_findings']['gestion']['summary'] = $cumplimiento < self::UMBRAL_CUMPLIMIENTO_CRITICO
                    ? 'El desempeño en campo es bajo.'
                    : ($cumplimiento >= 70 ? 'Gestión en campo adecuada.' : 'Gestión en campo regular.');
                $data['key_findings']['gestion']['worst_gestor'] = $worstGestor;
            }
        }
        return $data;
    }

    private function corregirContradicciones(array $data, array $features, ?array $worstGestor): array
    {
        return $this->aplicarMetricasVerificadas($data, $features, $worstGestor);
    }

    private function extraerMetricasVerificadas(array $features): array
    {
        return [
            'cumplimiento_promedio' => $features['cumplimiento_promedio'],
            'last_payment_days' => $features['last_payment_days'],
            'total_pagos' => $features['total_pagos'],
            'dias_mora' => $features['dias_mora'],
            'home_flag' => $features['home_flag'],
            'promesa_vencida' => $features['promesa_vencida'],
        ];
    }

    private function detectarDatosFaltantes(array $features): array
    {
        $missing = [];
        if (empty($features['raw_espacial'] ?? null) && !($features['home_flag'] ?? false)) {
            $missing[] = 'ubicaciones';
        }
        if (($features['total_pagos'] ?? 0) === 0) {
            $missing[] = 'historial_pagos';
        }
        if (($features['last_payment_days'] ?? null) === null && ($features['total_pagos'] ?? 0) > 0) {
            $missing[] = 'fecha_ultimo_pago';
        }
        if (($features['cumplimiento_promedio'] ?? null) === null && !empty($features['raw_gestiones'] ?? [])) {
            $missing[] = 'cumplimiento_gestor';
        }
        return $missing;
    }

    private function ajustarConfianzaPorDatosFaltantes(float $confianza, array $missingData): float
    {
        if (empty($missingData)) {
            return $confianza;
        }
        $penalizacion = min(0.25, count($missingData) * 0.08);
        return max(0.2, $confianza - $penalizacion);
    }

    private function asegurarSchemaCompleto(array $data, array $features, ?array $worstGestor): array
    {
        if (empty($data['recommended_actions']) && ($worstGestor !== null || ($features['cumplimiento_promedio'] ?? 100) < self::UMBRAL_CUMPLIMIENTO_CRITICO)) {
            $data['recommended_actions'] = $this->generarAccionesDesdeReglas($features, ['worst_gestor' => $worstGestor], $worstGestor);
        }
        return $data;
    }

    private function cacheGet(string $key): ?array
    {
        $path = self::CACHE_DIR . '/interpretar_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $key) . '.json';
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return null;
        }
        $decoded = @json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded['expires']) || $decoded['expires'] < time()) {
            @unlink($path);
            return null;
        }
        return $decoded['payload'] ?? null;
    }

    private function cacheSet(string $key, array $payload): void
    {
        if (!is_dir(self::CACHE_DIR)) {
            @mkdir(self::CACHE_DIR, 0755, true);
        }
        $path = self::CACHE_DIR . '/interpretar_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $key) . '.json';
        @file_put_contents($path, json_encode(['expires' => time() + self::CACHE_TTL, 'payload' => $payload], JSON_UNESCAPED_UNICODE));
    }
}
