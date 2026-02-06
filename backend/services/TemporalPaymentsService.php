<?php

/**
 * Análisis temporal de pagos determinístico (sin IA).
 *
 * Intervalos, desviación, día más frecuente, consistencia y patrón regular/irregular.
 */

namespace Services;

class TemporalPaymentsService
{
    /** Umbral: desviación/intervalo_promedio < este valor => patrón regular */
    private const UMBRAL_REGULAR_CV = 0.35;

    /**
     * Analiza recurrencia y consistencia temporal de pagos.
     *
     * Input pagos: [{id, fecha_pago (ISO8601), monto}] o [{fecha}, ...].
     * Output: total_pagos, intervalo_promedio_dias, desviacion_intervalos, dia_mas_frecuente, consistencia_dia (0..1), patron_pago ('regular'|'irregular'|'insuficiente_datos').
     * Si total_pagos < 3 → patron_pago 'insuficiente_datos'.
     */
    public function analizarPagos(array $pagos): array
    {
        $fechas = $this->normalizarFechasPago($pagos);
        $total_pagos = count($fechas);
        if ($total_pagos === 0) {
            return [
                'total_pagos' => 0,
                'intervalo_promedio_dias' => 0.0,
                'desviacion_intervalos' => 0.0,
                'dia_mas_frecuente' => null,
                'consistencia_dia' => 0.0,
                'patron_pago' => 'insuficiente_datos',
            ];
        }
        if ($total_pagos < 3) {
            $diaSemana = (int) date('N', strtotime($fechas[0]));
            $diaNombre = $this->numeroADiaNombre($diaSemana);
            return [
                'total_pagos' => $total_pagos,
                'intervalo_promedio_dias' => $total_pagos >= 2 ? $this->calcularIntervaloUnico($fechas) : 0.0,
                'desviacion_intervalos' => 0.0,
                'dia_mas_frecuente' => $diaNombre,
                'consistencia_dia' => $total_pagos === 1 ? 0.0 : $this->consistenciaDiaSemana($fechas),
                'patron_pago' => 'insuficiente_datos',
            ];
        }
        rsort($fechas);
        $intervalos = [];
        for ($i = 0; $i < count($fechas) - 1; $i++) {
            $a = strtotime($fechas[$i]);
            $b = strtotime($fechas[$i + 1]);
            if ($a && $b) {
                $intervalos[] = abs($a - $b) / 86400.0;
            }
        }
        $intervalo_promedio_dias = empty($intervalos) ? 0.0 : array_sum($intervalos) / count($intervalos);
        $desviacion_intervalos = $this->desviacionEstandar($intervalos);
        $diaMasFrecuente = $this->diaMasFrecuente($fechas);
        $consistencia_dia = $this->consistenciaDiaSemana($fechas);
        $cv = $intervalo_promedio_dias > 0 ? ($desviacion_intervalos / $intervalo_promedio_dias) : 1.0;
        $patron_pago = $cv <= self::UMBRAL_REGULAR_CV ? 'regular' : 'irregular';
        return [
            'total_pagos' => $total_pagos,
            'intervalo_promedio_dias' => round($intervalo_promedio_dias, 2),
            'desviacion_intervalos' => round($desviacion_intervalos, 2),
            'dia_mas_frecuente' => $diaMasFrecuente,
            'consistencia_dia' => round($consistencia_dia, 4),
            'patron_pago' => $patron_pago,
        ];
    }

    private function normalizarFechasPago(array $pagos): array
    {
        $fechas = [];
        foreach ($pagos as $p) {
            if (is_scalar($p)) {
                $ts = is_numeric($p) ? (int) $p : strtotime($p);
                if ($ts) {
                    $fechas[] = date('Y-m-d', $ts);
                }
                continue;
            }
            $f = $p['fecha'] ?? $p['fecha_pago'] ?? $p['timestamp'] ?? null;
            if ($f === null) {
                continue;
            }
            $ts = is_numeric($f) ? (int) $f : strtotime($f);
            if ($ts) {
                $fechas[] = date('Y-m-d', $ts);
            }
        }
        return array_values(array_unique($fechas));
    }

    private function calcularIntervaloUnico(array $fechas): float
    {
        if (count($fechas) < 2) {
            return 0.0;
        }
        rsort($fechas);
        $intervalos = [];
        for ($i = 0; $i < count($fechas) - 1; $i++) {
            $a = strtotime($fechas[$i]);
            $b = strtotime($fechas[$i + 1]);
            if ($a && $b) {
                $intervalos[] = abs($a - $b) / 86400.0;
            }
        }
        return empty($intervalos) ? 0.0 : round(array_sum($intervalos) / count($intervalos), 2);
    }

    private function desviacionEstandar(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }
        $media = array_sum($values) / count($values);
        $sumSq = 0.0;
        foreach ($values as $v) {
            $sumSq += ($v - $media) ** 2;
        }
        return sqrt($sumSq / count($values));
    }

    private function diaMasFrecuente(array $fechas): ?string
    {
        $dias = [];
        foreach ($fechas as $f) {
            $n = (int) date('N', strtotime($f));
            $dias[$n] = ($dias[$n] ?? 0) + 1;
        }
        if (empty($dias)) {
            return null;
        }
        arsort($dias);
        $num = (int) array_key_first($dias);
        return $this->numeroADiaNombre($num);
    }

    private function numeroADiaNombre(int $numero): string
    {
        $map = [1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sábado', 7 => 'domingo'];
        return $map[$numero] ?? 'desconocido';
    }

    /**
     * Consistencia en día de la semana: proporción de pagos en el día más frecuente (0..1).
     */
    private function consistenciaDiaSemana(array $fechas): float
    {
        if (empty($fechas)) {
            return 0.0;
        }
        $porDia = [];
        foreach ($fechas as $f) {
            $n = (int) date('N', strtotime($f));
            $porDia[$n] = ($porDia[$n] ?? 0) + 1;
        }
        $max = max($porDia);
        return $max / count($fechas);
    }
}
