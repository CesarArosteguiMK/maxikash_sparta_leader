<?php

/**
 * CAPA 2 – INTERPRETACIÓN IA
 *
 * Recibe SOLO resultado del motor. Produce resumen, acciones_recomendadas[], riesgos_detectados[] (strings),
 * patrones_conductuales[], prediccion_intencion { accion, evidencia[id_evidencia,...], nota }.
 * Si falla la LLM, devuelve fallback mínimo desde trazabilidad (evidencia = ids de candidatos).
 */

namespace Services;

class IAInterpretationService
{
    /**
     * @param array $resultadoMotor Salida de LocationScoringService (domicilio/trabajo/otro 0..100, trazabilidad)
     * @param callable $llamarLLM function(string $systemPrompt, array $parts, int $maxTokens): array { success, texto, mensaje }
     * @param string|null $contextoMinimo
     * @return array [ resumen, acciones_recomendadas[], riesgos_detectados[], patrones_conductuales[], prediccion_intencion=>[accion, evidencia[], nota] ]
     */
    public function interpretar(array $resultadoMotor, callable $llamarLLM, ?string $contextoMinimo = null): array
    {
        $dom = (float) ($resultadoMotor['domicilio'] ?? 0);
        $tra = (float) ($resultadoMotor['trabajo'] ?? 0);
        $otr = (float) ($resultadoMotor['otro'] ?? 0);
        $traz = $resultadoMotor['trazabilidad'] ?? [];
        $candidatos = $traz['candidatos'] ?? [];
        $idsEvidencia = array_values(array_filter(array_map(function ($c) {
            $id = $c['id'] ?? $c['key'] ?? null;
            return $id !== null ? (string) $id : null;
        }, $candidatos)));

        $lineas = [
            "Probabilidades (motor, NO modificar): domicilio={$dom}%, trabajo={$tra}%, otro={$otr}%.",
            "Candidatos (ids para evidencia): " . json_encode(array_map(function ($c) {
                return ['id' => $c['id'] ?? $c['key'], 'tipo' => $c['place_type'] ?? '', 'etiqueta' => $c['label'] ?? ''];
            }, $candidatos), JSON_UNESCAPED_UNICODE),
        ];
        if ($contextoMinimo !== null && $contextoMinimo !== '') {
            $lineas[] = "Contexto: " . $contextoMinimo;
        }

        $promptSistema = 'Eres un analista. INTERPRETA resultados ya calculados. NO inventes probabilidades. '
            . 'Devuelve prediccion_intencion con evidencia como array de IDs que refieran a candidatos del input. RESPONDE SOLO JSON.';
        $promptUsuario = "INPUT:\n" . implode("\n", $lineas) . "\n\n"
            . "Devuelve JSON:\n"
            . "{\n"
            . "  \"resumen\": \"string\",\n"
            . "  \"acciones_recomendadas\": [ \"string\" ],\n"
            . "  \"riesgos_detectados\": [ \"string\" ],\n"
            . "  \"patrones_conductuales\": [ \"string\" ],\n"
            . "  \"prediccion_intencion\": { \"accion\": \"string\", \"evidencia\": [ \"id\" ], \"nota\": \"string\" }\n"
            . "}\n";

        $resultado = $llamarLLM($promptSistema, [['text' => $promptUsuario]], 1024);

        if (!$resultado['success'] || trim($resultado['texto'] ?? '') === '') {
            return $this->fallbackInterpretacion($resultadoMotor, $idsEvidencia);
        }

        $texto = trim($resultado['texto']);
        $texto = preg_replace('/^```(?:json)?\s*/i', '', $texto);
        $texto = preg_replace('/\s*```\s*$/i', '', $texto);
        $json = json_decode($texto, true);

        if (!is_array($json)) {
            return $this->fallbackInterpretacion($resultadoMotor, $idsEvidencia);
        }

        $riesgos = isset($json['riesgos_detectados']) && is_array($json['riesgos_detectados'])
            ? array_values(array_map('strval', $json['riesgos_detectados']))
            : [];
        $predInt = $json['prediccion_intencion'] ?? [];
        if (!is_array($predInt)) {
            $predInt = ['accion' => '', 'evidencia' => [], 'nota' => ''];
        }
        $ev = $predInt['evidencia'] ?? [];
        $evidencia = is_array($ev) ? array_map('strval', array_values($ev)) : [];
        if (empty($evidencia) && !empty($idsEvidencia)) {
            $evidencia = array_slice($idsEvidencia, 0, 2);
        }

        return [
            'resumen'                => (string) ($json['resumen'] ?? 'Interpretación no disponible.'),
            'acciones_recomendadas'  => isset($json['acciones_recomendadas']) && is_array($json['acciones_recomendadas'])
                ? array_values(array_map('strval', $json['acciones_recomendadas'])) : [],
            'riesgos_detectados'     => $riesgos,
            'patrones_conductuales' => isset($json['patrones_conductuales']) && is_array($json['patrones_conductuales'])
                ? array_values(array_map('strval', $json['patrones_conductuales'])) : [],
            'prediccion_intencion'   => [
                'accion'    => (string) ($predInt['accion'] ?? 'Revisar mapa y gestiones'),
                'evidencia' => $evidencia,
                'nota'      => (string) ($predInt['nota'] ?? ''),
            ],
            'success'                => true,
            'raw_ia'                 => $texto,
        ];
    }

    private function fallbackInterpretacion(array $resultadoMotor, array $idsEvidencia = []): array
    {
        $dom = (float) ($resultadoMotor['domicilio'] ?? 0);
        $tra = (float) ($resultadoMotor['trabajo'] ?? 0);
        $otr = (float) ($resultadoMotor['otro'] ?? 0);
        $traz = $resultadoMotor['trazabilidad'] ?? [];
        $candidatos = $traz['candidatos'] ?? [];
        if (empty($idsEvidencia)) {
            foreach ($candidatos as $c) {
                $id = $c['id'] ?? $c['key'] ?? null;
                if ($id !== null) {
                    $idsEvidencia[] = (string) $id;
                }
            }
        }
        $resumen = sprintf(
            'Probabilidad domicilio %.2f%%, trabajo %.2f%%, otro %.2f%%. Interpretación IA no disponible.',
            $dom, $tra, $otr
        );
        return [
            'resumen'                => $resumen,
            'acciones_recomendadas'  => ['Revisar mapa de ubicaciones', 'Revisar historial de gestiones'],
            'riesgos_detectados'     => [],
            'patrones_conductuales'  => [],
            'prediccion_intencion'   => [
                'accion'    => 'Visita domiciliaria o revisión manual según motor',
                'evidencia' => array_slice($idsEvidencia, 0, 3),
                'nota'      => 'Fallback desde trazabilidad del motor.',
            ],
            'success'                => false,
            'raw_ia'                  => '',
        ];
    }
}
