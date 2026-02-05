<?php

/**
 * CAPA 1 – MOTOR MATEMÁTICO DETERMINÍSTICO
 *
 * Contrato: calcularProbabilidadLocalizacion(array $datosParaMotor): array
 * Input: pagos_count:int, ubicaciones:[{id, etiqueta, cantidad_registros:int, ultima_fecha:ISO8601}],
 *        gestiones:[{id, fecha:ISO8601, tipo}], opcional horarios
 * Output: domicilio/trabajo/otro 0..100 (dos decimales), trazabilidad, motor_confidence 0..100
 * Reglas: domicilio+trabajo+otro = 100 (±0.01). Determinista, sin rand().
 */

namespace Services;

class LocationScoringService
{
    private float $weightPayments;
    private float $weightGps;
    private float $weightGestiones;
    private float $weightHorario;
    private float $paymentsNorm;
    private float $gpsVisitsNorm;
    private float $gestionesNorm;
    private float $gpsPenalty30;
    private float $gpsPenalty90;

    public function __construct(array $config = [])
    {
        $this->weightPayments   = (float) ($config['weight_payments'] ?? 0.40);
        $this->weightGps       = (float) ($config['weight_gps'] ?? 0.35);
        $this->weightGestiones = (float) ($config['weight_gestiones'] ?? 0.15);
        $this->weightHorario   = (float) ($config['weight_horario'] ?? 0.10);
        $this->paymentsNorm    = (float) ($config['payments_norm'] ?? 8.0);
        $this->gpsVisitsNorm  = (float) ($config['gps_visits_norm'] ?? 6.0);
        $this->gestionesNorm  = (float) ($config['gestiones_norm'] ?? 8.0);
        $this->gpsPenalty30   = (float) ($config['gps_penalty_30_days'] ?? 0.5);
        $this->gpsPenalty90   = (float) ($config['gps_penalty_90_days'] ?? 0.2);
    }

    /**
     * @param array $datosParaMotor [ 'pagos_count'=>int, 'ubicaciones'=>[{id, etiqueta, cantidad_registros, ultima_fecha}], 'gestiones'=>[{id, fecha, tipo}], 'horarios'=>? ]
     * @return array [ 'domicilio'=>float 0..100, 'trabajo'=>float, 'otro'=>float, 'trazabilidad'=>array, 'motor_confidence'=>float 0..100 ]
     */
    public function calcularProbabilidadLocalizacion(array $datosParaMotor): array
    {
        $pagosCount  = (int) ($datosParaMotor['pagos_count'] ?? 0);
        $ubicaciones = $datosParaMotor['ubicaciones'] ?? [];
        $gestiones   = $datosParaMotor['gestiones'] ?? [];

        $candidatos = $this->buildCandidatos($pagosCount, $ubicaciones, $gestiones);
        $scores     = $this->computeScores($candidatos);

        $domicilio = 0.0;
        $trabajo   = 0.0;
        $otro      = 0.0;
        foreach ($candidatos as $i => $c) {
            $prob = $scores[$i]['probability'] ?? 0.0;
            if (($c['place_type'] ?? '') === 'domicilio') {
                $domicilio += $prob;
            } elseif (($c['place_type'] ?? '') === 'trabajo') {
                $trabajo += $prob;
            } else {
                $otro += $prob;
            }
        }

        $total = $domicilio + $trabajo + $otro;
        if ($total < 1e-9) {
            $domicilio = 100.0 / 3.0;
            $trabajo   = 100.0 / 3.0;
            $otro      = 100.0 / 3.0;
        } else {
            $domicilio = round(100.0 * $domicilio / $total, 2);
            $trabajo   = round(100.0 * $trabajo / $total, 2);
            $otro      = round(100.0 * $otro / $total, 2);
        }
        $sum = $domicilio + $trabajo + $otro;
        if (abs($sum - 100.0) > 0.01) {
            $otro = round($otro + (100.0 - $sum), 2);
        }

        $motorConfidence = $this->computeMotorConfidence($candidatos, $scores);

        return [
            'domicilio'        => round($domicilio, 2),
            'trabajo'          => round($trabajo, 2),
            'otro'             => round($otro, 2),
            'trazabilidad'     => [
                'candidatos'   => $candidatos,
                'scores_raw'   => $scores,
                'pesos_usados' => [
                    'payments'  => $this->weightPayments,
                    'gps'       => $this->weightGps,
                    'gestiones' => $this->weightGestiones,
                    'horario'   => $this->weightHorario,
                ],
            ],
            'motor_confidence' => round($motorConfidence, 2),
        ];
    }

    private function buildCandidatos(int $pagosCount, array $ubicaciones, array $gestiones): array
    {
        $top = array_slice($ubicaciones, 0, 5);
        $totalGestiones = count($gestiones);
        $gestionesSample = array_slice($gestiones, 0, 16);

        $horarioScoreGlobal = $this->computeHorarioScore($gestionesSample);
        $now = time();
        $candidatos = [];

        foreach ($top as $i => $d) {
            $visitas = (int) ($d['cantidad_registros'] ?? 0);
            $ultimaFecha = $d['ultima_fecha'] ?? '';
            $lastGpsDays = 9999;
            if ($ultimaFecha !== '') {
                $ts = is_numeric($ultimaFecha) ? (int) $ultimaFecha : strtotime($ultimaFecha);
                if ($ts) {
                    $lastGpsDays = (int) (($now - $ts) / 86400);
                }
            }
            $placeType = $i === 0 ? 'domicilio' : ($i === 1 ? 'trabajo' : 'otro');
            $candidatos[] = [
                'id'             => $d['id'] ?? $i,
                'key'            => $i,
                'place_type'     => $placeType,
                'label'          => $d['etiqueta'] ?? $d['texto'] ?? $placeType,
                'payments_count' => $i === 0 ? $pagosCount : 0,
                'gps_visits'     => $visitas,
                'last_gps_days'  => $lastGpsDays,
                'gestiones_count'=> $i === 0 ? $totalGestiones : 0,
                'horario_score'  => $i === 0 ? $horarioScoreGlobal : (min(1.0, $visitas / 4.0) * 0.5),
            ];
        }
        return $candidatos;
    }

    private function computeHorarioScore(array $gestiones): float
    {
        if (empty($gestiones)) {
            return 0.5;
        }
        $ventanas = ['06-09' => 0, '09-12' => 0, '12-15' => 0, '15-18' => 0, '18-21' => 0, '21-24' => 0];
        foreach ($gestiones as $g) {
            $f = $g['fecha'] ?? $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? null;
            if ($f && preg_match('/T?(\d{1,2}):/', (string) $f, $m)) {
                $h = (int) $m[1];
                if ($h >= 6 && $h < 9) $ventanas['06-09']++;
                elseif ($h >= 9 && $h < 12) $ventanas['09-12']++;
                elseif ($h >= 12 && $h < 15) $ventanas['12-15']++;
                elseif ($h >= 15 && $h < 18) $ventanas['15-18']++;
                elseif ($h >= 18 && $h < 21) $ventanas['18-21']++;
                else $ventanas['21-24']++;
            }
        }
        $maxVentana = max($ventanas);
        return $maxVentana > 0 ? min(1.0, $maxVentana / 4.0) : 0.5;
    }

    private function computeScores(array $candidatos): array
    {
        $raw = [];
        foreach ($candidatos as $c) {
            $key = $c['key'] ?? count($raw);
            $paymentsScore = min(1.0, (($c['payments_count'] ?? 0) / $this->paymentsNorm));
            $gpsFreqNorm   = min(1.0, (($c['gps_visits'] ?? 0) / $this->gpsVisitsNorm));
            $lastGpsDays   = $c['last_gps_days'] ?? 9999;
            $gpsMultiplier = 1.0;
            if ($lastGpsDays > 90) {
                $gpsMultiplier = $this->gpsPenalty90;
            } elseif ($lastGpsDays > 30) {
                $gpsMultiplier = $this->gpsPenalty30;
            }
            $gpsScore       = $gpsFreqNorm * $gpsMultiplier;
            $gestionesScore= min(1.0, (($c['gestiones_count'] ?? 0) / $this->gestionesNorm));
            $horarioScore   = (float) ($c['horario_score'] ?? 0);
            $rawScore = $this->weightPayments * $paymentsScore
                + $this->weightGps * $gpsScore
                + $this->weightGestiones * $gestionesScore
                + $this->weightHorario * $horarioScore;
            $raw[$key] = $rawScore;
        }
        $total = array_sum($raw) + 1e-12;
        $out = [];
        foreach ($raw as $key => $r) {
            $out[$key] = ['raw' => $r, 'probability' => $r / $total];
        }
        return $out;
    }

    /** motor_confidence 0..100 basado en cantidad de datos y recencia */
    private function computeMotorConfidence(array $candidatos, array $scores): float
    {
        if (empty($candidatos)) {
            return 10.0;
        }
        $maxRaw = 0.0;
        foreach ($scores as $s) {
            $maxRaw = max($maxRaw, $s['raw'] ?? 0);
        }
        $base = min(100.0, 30.0 + $maxRaw * 70.0);
        $penalty = 0;
        foreach ($candidatos as $c) {
            $days = $c['last_gps_days'] ?? 9999;
            if ($days > 90) $penalty += 20;
            elseif ($days > 30) $penalty += 10;
        }
        return max(0, min(100, $base - $penalty));
    }
}
