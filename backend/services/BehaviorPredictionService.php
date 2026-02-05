<?php

/**
 * Predictor conductual determinístico.
 *
 * Predice evento futuro (intención/acción) del acreditado a partir de resultadoMotor, datosReales e historial_temporal.
 * Contrato: predecirIntencionAcreditado(resultadoMotor, datosReales, historial_temporal): array
 * Salida: evento_probable, confianza_evento (0..100), indicadores, ventana_tiempo_estimada, explicacion_deterministica, evidencias.
 * Reglas: determinista (sin rand()). Si datos insuficientes => evento_probable 'insuficiente_datos', confianza_evento < 30.
 *
 * Fórmula confianza_evento (en comentarios):
 * score = clamp( 0.4 * normalize(1/desviacion_intervalos)
 *             + 0.3 * normalize_recency(recencia_gps)
 *             + 0.2 * normalize_frecuencia(frecuencia_gestiones)
 *             + 0.1 * normalize_variabilidad(variabilidad_ubicacion), 0, 1)
 * confianza_evento = round(score * 100, 2)
 * normalize_*: mapean rango observado a 0..1 (ver métodos privados).
 */

namespace Services;

class BehaviorPredictionService
{
    /** Eventos posibles (valor de evento_probable). */
    private const EVENTO_PAGO_PROXIMO = 'pago_proximo';
    private const EVENTO_RETRASO_PAGO = 'retraso_pago';
    private const EVENTO_EVASION_CONTACTO = 'evasión_contacto';
    private const EVENTO_VISITA_DOM_EXITOSA = 'visita_domiciliaria_exitosa';
    private const EVENTO_VISITA_DOM_FALLIDA = 'visita_domiciliaria_fallida';
    private const EVENTO_PAGO_EN_CAJA = 'pago_en_caja';
    private const EVENTO_CAMBIO_UBICACION = 'cambio_ubicacion_habitual';
    private const EVENTO_INSUFICIENTE_DATOS = 'insuficiente_datos';

    /** Pesos para confianza_evento (coherencia histórica vía 1/desv, recencia, frecuencia, variabilidad). */
    private const W_DESVIACION = 0.40;
    private const W_RECENCIA = 0.30;
    private const W_FRECUENCIA = 0.20;
    private const W_VARIABILIDAD = 0.10;

    /** Normalización: desviación mínima para evitar división por cero; máximo intervalo considerado "regular" (días). */
    private const DESV_MIN = 0.5;
    private const INTERVALO_MAX_NORM = 30.0;
    private const RECENCIA_MAX_DIAS = 90;
    private const GESTIONES_MAX_NORM = 15;
    private const POIS_MAX_NORM = 10;

    /**
     * Predice intención/evento probable del acreditado.
     *
     * @param array $resultadoMotor Salida de LocationScoringService (domicilio, trabajo, otro, trazabilidad, motor_confidence)
     * @param array $datosReales [ pagos_count, gps => [{ultima_fecha, visitas, ...}], gestiones => [{fecha, tipo, ...}] ]
     * @param array $historial_temporal Opcional: [ fechas_pago => [ISO8601,...], gestiones => [], gps => [] ]. Si fechas_pago no viene, se intenta extraer de gestiones (tipo contiene "Pago").
     * @return array [ evento_probable, confianza_evento, indicadores, ventana_tiempo_estimada, explicacion_deterministica, evidencias ]
     */
    public function predecirIntencionAcreditado(array $resultadoMotor, array $datosReales, array $historial_temporal = []): array
    {
        $evidencias = $this->extraerEvidencias($resultadoMotor, $datosReales);
        $gestiones = $datosReales['gestiones'] ?? [];
        $gps = $datosReales['gps'] ?? [];
        $pagosCount = (int) ($datosReales['pagos_count'] ?? 0);

        $fechasPago = $historial_temporal['fechas_pago'] ?? $this->extraerFechasPagoDeGestiones($gestiones);
        $intervaloPromedio = $this->calcularIntervaloPromedioPago($fechasPago);
        $desviacionIntervalos = $this->calcularDesviacionIntervalos($fechasPago);
        $frecuenciaGestiones = $this->calcularFrecuenciaGestiones($gestiones, 30);
        $recenciaGps = $this->calcularRecenciaGps($resultadoMotor, $gps);
        $variabilidadUbicacion = $this->calcularVariabilidadUbicacion($resultadoMotor, $gps);

        $indicadores = [
            'intervalo_promedio_pago' => round($intervaloPromedio, 2),
            'desviacion_intervalos'   => round($desviacionIntervalos, 2),
            'frecuencia_gestiones'    => $frecuenciaGestiones,
            'recencia_gps'            => $recenciaGps,
            'variabilidad_ubicacion'  => $variabilidadUbicacion,
        ];

        $datosSuficientes = ($pagosCount >= 1 || count($fechasPago) >= 1) && ($intervaloPromedio >= 0 || count($gestiones) > 0);
        if (!$datosSuficientes && $pagosCount === 0 && empty($gestiones) && empty($gps)) {
            return $this->respuestaInsuficiente($evidencias, $indicadores, 'Sin pagos ni gestiones ni GPS.');
        }

        $score = $this->calcularScoreConfianza($desviacionIntervalos, $recenciaGps, $frecuenciaGestiones, $variabilidadUbicacion);
        $confianzaEvento = round($score * 100.0, 2);
        $confianzaEvento = max(0.0, min(100.0, $confianzaEvento));

        if ($confianzaEvento < 30.0) {
            return $this->respuestaInsuficiente($evidencias, $indicadores, 'Confianza por debajo de umbral (datos irregulares o insuficientes).');
        }

        $evento = $this->inferirEventoProbable($intervaloPromedio, $desviacionIntervalos, $fechasPago, $recenciaGps, $frecuenciaGestiones);
        $ventana = $this->estimarVentanaTiempo($evento, $intervaloPromedio, $fechasPago);
        $explicacion = $this->construirExplicacion($evento, $indicadores, $evidencias);

        return [
            'evento_probable'           => $evento,
            'confianza_evento'          => $confianzaEvento,
            'indicadores'               => $indicadores,
            'ventana_tiempo_estimada'   => $ventana,
            'explicacion_deterministica' => $explicacion,
            'evidencias'                => $evidencias,
        ];
    }

    /**
     * Extrae IDs de evidencias desde resultadoMotor (candidatos) y datosReales (gps id, gestiones id).
     */
    private function extraerEvidencias(array $resultadoMotor, array $datosReales): array
    {
        $ids = [];
        $candidatos = $resultadoMotor['trazabilidad']['candidatos'] ?? [];
        foreach ($candidatos as $c) {
            $id = $c['id'] ?? $c['key'] ?? null;
            if ($id !== null && $id !== '') {
                $ids[] = (string) $id;
            }
        }
        foreach ($datosReales['gps'] ?? [] as $i => $g) {
            if (isset($g['id'])) {
                $ids[] = (string) $g['id'];
            } else {
                $ids[] = 'gps_' . $i;
            }
        }
        foreach ($datosReales['gestiones'] ?? [] as $i => $g) {
            $id = $g['id'] ?? 'g' . $i;
            $ids[] = (string) $id;
        }
        return array_values(array_unique($ids));
    }

    /** Extrae fechas de pago desde gestiones donde tipo indica pago. */
    private function extraerFechasPagoDeGestiones(array $gestiones): array
    {
        $fechas = [];
        foreach ($gestiones as $g) {
            $tipo = (string) ($g['tipo'] ?? $g['dictamen_campo'] ?? $g['dictamen_ccc'] ?? '');
            if (stripos($tipo, 'Pago') !== false || stripos($tipo, 'pago') !== false) {
                $f = $g['fecha'] ?? $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? null;
                if ($f) {
                    $ts = is_numeric($f) ? (int) $f : strtotime($f);
                    if ($ts) {
                        $fechas[] = date('Y-m-d', $ts);
                    }
                }
            }
        }
        rsort($fechas);
        return array_values(array_unique($fechas));
    }

    /** Promedio de días entre fechas de pago consecutivas. */
    private function calcularIntervaloPromedioPago(array $fechasPago): float
    {
        if (count($fechasPago) < 2) {
            return 0.0;
        }
        $intervalos = [];
        for ($i = 0; $i < count($fechasPago) - 1; $i++) {
            $a = strtotime($fechasPago[$i]);
            $b = strtotime($fechasPago[$i + 1]);
            if ($a && $b) {
                $intervalos[] = abs($a - $b) / 86400.0;
            }
        }
        return empty($intervalos) ? 0.0 : array_sum($intervalos) / count($intervalos);
    }

    /** Desviación estándar de los intervalos entre pagos. */
    private function calcularDesviacionIntervalos(array $fechasPago): float
    {
        if (count($fechasPago) < 2) {
            return 0.0;
        }
        $intervalos = [];
        for ($i = 0; $i < count($fechasPago) - 1; $i++) {
            $a = strtotime($fechasPago[$i]);
            $b = strtotime($fechasPago[$i + 1]);
            if ($a && $b) {
                $intervalos[] = abs($a - $b) / 86400.0;
            }
        }
        if (empty($intervalos)) {
            return 0.0;
        }
        $media = array_sum($intervalos) / count($intervalos);
        $sumSq = 0.0;
        foreach ($intervalos as $v) {
            $sumSq += ($v - $media) ** 2;
        }
        $var = $sumSq / count($intervalos);
        return sqrt($var);
    }

    /** Gestiones en los últimos N días. */
    private function calcularFrecuenciaGestiones(array $gestiones, int $dias): int
    {
        $cut = time() - $dias * 86400;
        $count = 0;
        foreach ($gestiones as $g) {
            $f = $g['fecha'] ?? $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? null;
            if ($f) {
                $ts = is_numeric($f) ? (int) $f : strtotime($f);
                if ($ts && $ts >= $cut) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /** Días desde la última visita GPS (usa trazabilidad candidatos o datosReales gps). */
    private function calcularRecenciaGps(array $resultadoMotor, array $gps): int
    {
        $candidatos = $resultadoMotor['trazabilidad']['candidatos'] ?? [];
        if (!empty($candidatos)) {
            $minDays = 9999;
            foreach ($candidatos as $c) {
                $days = (int) ($c['last_gps_days'] ?? 9999);
                if ($days < $minDays) {
                    $minDays = $days;
                }
            }
            return $minDays === 9999 ? 90 : $minDays;
        }
        if (empty($gps)) {
            return 90;
        }
        $now = time();
        $minDays = 90;
        foreach ($gps as $g) {
            $f = $g['ultima_fecha'] ?? null;
            if ($f) {
                $ts = is_numeric($f) ? (int) $f : strtotime($f);
                if ($ts) {
                    $d = (int) (($now - $ts) / 86400);
                    if ($d < $minDays) {
                        $minDays = $d;
                    }
                }
            }
        }
        return $minDays;
    }

    /** Número de ubicaciones/POIs distintos (candidatos o gps). */
    private function calcularVariabilidadUbicacion(array $resultadoMotor, array $gps): int
    {
        $candidatos = $resultadoMotor['trazabilidad']['candidatos'] ?? [];
        if (!empty($candidatos)) {
            return count($candidatos);
        }
        return count($gps);
    }

    /**
     * Score 0..1 para confianza_evento.
     * score = 0.4 * normalize(1/desv) + 0.3 * normalize_recency(recencia_gps) + 0.2 * normalize_frecuencia(frecuencia_gestiones) + 0.1 * normalize_variabilidad(variabilidad).
     * normalize(1/desv): desv alta => 1/desv bajo => score bajo. Desv baja (regular) => 1/desv alto => clamp a 1.
     * normalize_recency: menos días = más reciente = mayor score. recencia 0 => 1, recencia RECENCIA_MAX_DIAS => 0.
     * normalize_frecuencia: más gestiones = más actividad = mayor score (cap GESTIONES_MAX_NORM).
     * normalize_variabilidad: más POIs puede ser más incertidumbre; mapeamos a 0..1 (más variabilidad => ligeramente menor peso).
     */
    private function calcularScoreConfianza(float $desviacionIntervalos, int $recenciaGps, int $frecuenciaGestiones, int $variabilidadUbicacion): float
    {
        $invDesv = $desviacionIntervalos <= self::DESV_MIN ? 1.0 / self::DESV_MIN : (1.0 / $desviacionIntervalos);
        $normDesv = min(1.0, $invDesv / (1.0 / self::DESV_MIN));

        $normRecencia = 1.0 - min(1.0, $recenciaGps / (float) self::RECENCIA_MAX_DIAS);

        $normFrecuencia = min(1.0, $frecuenciaGestiones / (float) self::GESTIONES_MAX_NORM);

        $normVariabilidad = $variabilidadUbicacion <= 0 ? 0.5 : min(1.0, $variabilidadUbicacion / (float) self::POIS_MAX_NORM);
        $normVariabilidad = 1.0 - ($normVariabilidad * 0.3);

        $score = self::W_DESVIACION * $normDesv
            + self::W_RECENCIA * $normRecencia
            + self::W_FRECUENCIA * $normFrecuencia
            + self::W_VARIABILIDAD * max(0, $normVariabilidad);
        return max(0.0, min(1.0, $score));
    }

    private function inferirEventoProbable(float $intervaloPromedio, float $desviacionIntervalos, array $fechasPago, int $recenciaGps, int $frecuenciaGestiones): string
    {
        if (empty($fechasPago) && $intervaloPromedio <= 0) {
            return self::EVENTO_INSUFICIENTE_DATOS;
        }
        $diasDesdeUltimoPago = 0;
        if (!empty($fechasPago)) {
            $ultima = strtotime($fechasPago[0]);
            $diasDesdeUltimoPago = $ultima ? (int) ((time() - $ultima) / 86400) : 999;
        }
        if ($intervaloPromedio >= 1 && $desviacionIntervalos <= $intervaloPromedio * 0.5) {
            if ($diasDesdeUltimoPago >= 0 && $diasDesdeUltimoPago <= $intervaloPromedio * 0.4) {
                return self::EVENTO_PAGO_PROXIMO;
            }
            if ($diasDesdeUltimoPago > $intervaloPromedio * 1.2) {
                return self::EVENTO_RETRASO_PAGO;
            }
        }
        if ($recenciaGps > 60 && $frecuenciaGestiones > 3) {
            return self::EVENTO_EVASION_CONTACTO;
        }
        if ($recenciaGps <= 7 && $frecuenciaGestiones >= 1) {
            return self::EVENTO_VISITA_DOM_EXITOSA;
        }
        if ($intervaloPromedio >= 1 && $diasDesdeUltimoPago <= 3) {
            return self::EVENTO_PAGO_EN_CAJA;
        }
        return self::EVENTO_PAGO_PROXIMO;
    }

    private function estimarVentanaTiempo(string $evento, float $intervaloPromedio, array $fechasPago): array
    {
        if ($evento === self::EVENTO_PAGO_PROXIMO || $evento === self::EVENTO_PAGO_EN_CAJA) {
            $horas = $intervaloPromedio <= 0 ? 48.0 : min(168.0, $intervaloPromedio * 24.0 * 0.5);
            return ['desde_horas' => (int) max(0, $horas - 24), 'hasta_horas' => (int) min(168, $horas + 24)];
        }
        if ($evento === self::EVENTO_RETRASO_PAGO) {
            return ['desde_horas' => 0, 'hasta_horas' => 72];
        }
        return ['desde_horas' => 24, 'hasta_horas' => 120];
    }

    private function construirExplicacion(string $evento, array $indicadores, array $evidencias): string
    {
        $parts = [];
        $parts[] = 'Evento: ' . $evento . '.';
        $parts[] = 'Indicadores: intervalo_promedio_pago=' . ($indicadores['intervalo_promedio_pago'] ?? 0) . ' días, desviacion_intervalos=' . ($indicadores['desviacion_intervalos'] ?? 0) . ', frecuencia_gestiones=' . ($indicadores['frecuencia_gestiones'] ?? 0) . ', recencia_gps=' . ($indicadores['recencia_gps'] ?? 0) . ' días.';
        if (!empty($evidencias)) {
            $parts[] = 'Evidencias (ids): ' . implode(', ', array_slice($evidencias, 0, 10));
        }
        return implode(' ', $parts);
    }

    private function respuestaInsuficiente(array $evidencias, array $indicadores, string $motivo): array
    {
        return [
            'evento_probable'           => self::EVENTO_INSUFICIENTE_DATOS,
            'confianza_evento'          => 25.0,
            'indicadores'               => $indicadores,
            'ventana_tiempo_estimada'   => ['desde_horas' => 0, 'hasta_horas' => 168],
            'explicacion_deterministica' => 'Datos insuficientes: ' . $motivo . ' Indicadores: ' . json_encode($indicadores, JSON_UNESCAPED_UNICODE),
            'evidencias'                => $evidencias,
        ];
    }
}
