<?php

/**
 * CAPA 3 – VERIFICADOR IA
 *
 * Valida coherencia entre datosReales, resultadoMotor e interpretacionIA.
 * Devuelve: veracity_score (int 0..100), suspected_test, evidencias_validadas[], claims_no_soportados[].
 * Si falla la LLM: reglas determinísticas (motor_confidence < 10 → suspected_test true).
 */

namespace Services;

class IAVerificationService
{
    /**
     * @param array $datosReales [ pagos_count, gps, gestiones, suspected_test, suspected_test_reasons ]
     * @param array $resultadoMotor Salida de LocationScoringService (incluye motor_confidence)
     * @param array $interpretacionIA Salida de IAInterpretationService
     * @param callable $llamarLLM
     * @return array [ veracity_score=>int 0..100, suspected_test=>bool, evidencias_validadas=>[], claims_no_soportados=>[] ]
     */
    public function verificar(array $datosReales, array $resultadoMotor, array $interpretacionIA, callable $llamarLLM): array
    {
        $motorConf = (float) ($resultadoMotor['motor_confidence'] ?? 50.0);

        $payload = [
            'datos_reales' => [
                'pagos_count' => $datosReales['pagos_count'] ?? 0,
                'gps_count'   => count($datosReales['gps'] ?? []),
                'gestiones_count' => count($datosReales['gestiones'] ?? []),
                'suspected_test' => $datosReales['suspected_test'] ?? false,
            ],
            'motor' => [
                'domicilio' => $resultadoMotor['domicilio'] ?? 0,
                'trabajo'   => $resultadoMotor['trabajo'] ?? 0,
                'otro'      => $resultadoMotor['otro'] ?? 0,
                'motor_confidence' => $motorConf,
            ],
            'interpretacion' => [
                'resumen' => substr($interpretacionIA['resumen'] ?? '', 0, 200),
                'prediccion_intencion' => $interpretacionIA['prediccion_intencion'] ?? [],
            ],
        ];

        $promptSistema = 'Verificador. Valida coherencia. NO inventes datos. RESPONDE SOLO JSON.';
        $promptUsuario = "INPUT:\n" . json_encode($payload, JSON_UNESCAPED_UNICODE) . "\n\n"
            . "JSON: {\"veracity_score\": 0-100 (int), \"suspected_test\": bool, \"evidencias_validadas\": [], \"claims_no_soportados\": []}\n";

        $resultado = $llamarLLM($promptSistema, [['text' => $promptUsuario]], 512);

        if (!$resultado['success'] || trim($resultado['texto'] ?? '') === '') {
            return $this->fallbackVerificacion($datosReales, $resultadoMotor);
        }

        $texto = trim($resultado['texto']);
        $texto = preg_replace('/^```(?:json)?\s*/i', '', $texto);
        $texto = preg_replace('/\s*```\s*$/i', '', $texto);
        $json = json_decode($texto, true);

        if (!is_array($json)) {
            return $this->fallbackVerificacion($datosReales, $resultadoMotor);
        }

        $veracity = isset($json['veracity_score']) ? (int) round((float) $json['veracity_score']) : 70;
        $veracity = max(0, min(100, $veracity));
        $suspected = (bool) ($json['suspected_test'] ?? $datosReales['suspected_test'] ?? false);
        if ($motorConf < 10) {
            $suspected = true;
            $veracity = min($veracity, 50);
        }
        if ($suspected && $veracity > 60) {
            $veracity = (int) round($veracity * 0.6);
        }

        return [
            'veracity_score'       => $veracity,
            'suspected_test'       => $suspected,
            'evidencias_validadas' => isset($json['evidencias_validadas']) && is_array($json['evidencias_validadas'])
                ? array_values($json['evidencias_validadas']) : [],
            'claims_no_soportados' => isset($json['claims_no_soportados']) && is_array($json['claims_no_soportados'])
                ? array_values($json['claims_no_soportados']) : [],
            'success'              => true,
            'raw_ia'               => $texto,
        ];
    }

    private function fallbackVerificacion(array $datosReales, array $resultadoMotor): array
    {
        $motorConf = (float) ($resultadoMotor['motor_confidence'] ?? 50.0);
        $suspected = (bool) ($datosReales['suspected_test'] ?? false);
        if ($motorConf < 10) {
            $suspected = true;
        }
        $reasons = $datosReales['suspected_test_reasons'] ?? [];
        $veracity = $suspected ? 50 : 75;
        if ($motorConf < 10) {
            $veracity = 30;
        }
        $evidencias = ['pagos_count y GPS usados en motor'];
        if ($suspected && !empty($reasons)) {
            $evidencias[] = 'suspected_test: ' . implode('; ', array_slice($reasons, 0, 2));
        }
        return [
            'veracity_score'       => $veracity,
            'suspected_test'       => $suspected,
            'evidencias_validadas' => $evidencias,
            'claims_no_soportados' => [],
            'success'              => false,
            'raw_ia'               => '',
        ];
    }
}
