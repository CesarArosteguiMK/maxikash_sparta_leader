<?php

namespace Models;

use Core\DatabaseSegundometro;

/**
 * Dashboard «Estadísticas Gastos Cobranza» — KPIs y series sobre `gastos_cobranza` (__SPARTA_SECRET_REDACTED__).
 *
 * Clasificación de filas (coherente con sync / EstadoCuenta):
 * - condonado = 1 → condonado
 * - si no: estatus_pago = 2 → pagado (recuperado)
 * - si no: estatus_pago = 1 → parcial
 * - si no: pendiente (0 o NULL)
 *
 * KPI «Recuperado»: SUM(monto_parcial_pagado) con fecha de pago en el periodo
 * (misma ventana calendario que el filtro superior), estatus_pago IN (1,2), no condonado.
 * El resto de KPIs siguen usando la fecha de referencia del cargo (created_at / periodo_inicio).
 * Serie «por día» con filtro Semana: verde usa la misma base (fecha de pago); rojo sigue por fecha del cargo.
 */
class GastosCobranzaEstadistica
{
    private const TABLA = 'gastos_cobranza';

    /** Fecha de referencia para filtros y series. */
    private static function sqlFechaRef(): string
    {
        return 'COALESCE(DATE(gc.created_at), DATE(gc.periodo_inicio))';
    }

    /** WHERE para el periodo de análisis (calendario actual según tipo). */
    private static function sqlWherePeriodo(string $tipo): string
    {
        $f = self::sqlFechaRef();
        switch ($tipo) {
            case 'mes':
                return "DATE_FORMAT($f, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
            case 'trimestre':
                return "QUARTER($f) = QUARTER(CURDATE()) AND YEAR($f) = YEAR(CURDATE())";
            case 'anio':
                return "YEAR($f) = YEAR(CURDATE())";
            case 'semana':
            default:
                return "YEARWEEK($f, 1) = YEARWEEK(CURDATE(), 1) AND YEAR($f) = YEAR(CURDATE())";
        }
    }

    /** Etiqueta corta del periodo calendario actual (CDMX) para badges en KPIs. */
    private static function periodoLabelActual(string $periodo): string
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $now = new \DateTimeImmutable('now', $tz);
        $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

        if ($periodo === 'semana') {
            $isoY = (int) $now->format('o');
            $isoW = (int) $now->format('W');
            $lun = $now->setISODate($isoY, $isoW, 1);
            $dom = $lun->modify('+6 days');
            $d1 = (int) $lun->format('j');
            $d2 = (int) $dom->format('j');
            $m1 = (int) $lun->format('n') - 1;
            $m2 = (int) $dom->format('n') - 1;
            $y1 = (int) $lun->format('Y');
            $y2 = (int) $dom->format('Y');
            if ($m1 === $m2 && $y1 === $y2) {
                return sprintf('Sem %02d–%02d %s · %d', $d1, $d2, $meses[$m1], $y1);
            }

            return sprintf('Sem %02d %s – %02d %s · %d', $d1, $meses[$m1], $d2, $meses[$m2], $y2);
        }
        if ($periodo === 'mes') {
            $m = (int) $now->format('n') - 1;

            return sprintf('Mes %s · %s', $meses[$m], $now->format('Y'));
        }
        if ($periodo === 'trimestre') {
            $q = (int) ceil((int) $now->format('n') / 3);

            return sprintf('T%d · %s', $q, $now->format('Y'));
        }
        if ($periodo === 'anio') {
            return 'Año ' . $now->format('Y');
        }

        return $meses[(int) $now->format('n') - 1] . ' ' . $now->format('Y');
    }

    /**
     * Rango calendario visible en badges (CDMX), p. ej. «ABR 01-30 - 2026» (mes) o «ABR 07-13 - 2026» (semana ISO).
     */
    private static function periodoBadgeRango(string $periodo): string
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $now = new \DateTimeImmutable('now', $tz);
        $M = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

        if ($periodo === 'mes') {
            $start = $now->modify('first day of this month');
            $end = $now->modify('last day of this month');
            $mi = (int) $start->format('n') - 1;

            return sprintf(
                '%s %02d-%02d - %s',
                $M[$mi],
                (int) $start->format('d'),
                (int) $end->format('d'),
                $start->format('Y')
            );
        }
        if ($periodo === 'semana') {
            $isoY = (int) $now->format('o');
            $isoW = (int) $now->format('W');
            $lun = $now->setISODate($isoY, $isoW, 1);
            $dom = $lun->modify('+6 days');
            $m1 = (int) $lun->format('n') - 1;
            $m2 = (int) $dom->format('n') - 1;
            $y2 = (int) $dom->format('Y');
            if ($m1 === $m2) {
                return sprintf('%s %02d-%02d - %d', $M[$m1], (int) $lun->format('d'), (int) $dom->format('d'), $y2);
            }

            return sprintf(
                '%s %02d - %s %02d - %d',
                $M[$m1],
                (int) $lun->format('d'),
                $M[$m2],
                (int) $dom->format('d'),
                $y2
            );
        }
        if ($periodo === 'trimestre') {
            $month = (int) $now->format('n');
            $q = (int) ceil($month / 3);
            $startMonth = 3 * $q - 2;
            $start = $now->setDate((int) $now->format('Y'), $startMonth, 1);
            $end = $start->modify('+2 months')->modify('last day of this month');
            $mi = (int) $start->format('n') - 1;
            $mie = (int) $end->format('n') - 1;
            $y = (int) $start->format('Y');

            return sprintf(
                '%s %02d - %s %02d - %d',
                $M[$mi],
                (int) $start->format('d'),
                $M[$mie],
                (int) $end->format('d'),
                $y
            );
        }
        if ($periodo === 'anio') {
            $y = (int) $now->format('Y');

            return sprintf('%s %02d - %s %02d - %d', $M[0], 1, $M[11], 31, $y);
        }

        return self::periodoLabelActual($periodo);
    }

    /** WHERE periodo aplicado a la fecha de pago (alineado a consultas por `fecha_pago`). */
    private static function sqlWherePeriodoFechaPago(string $tipo): string
    {
        $fp = 'DATE(gc.fecha_pago)';
        switch ($tipo) {
            case 'mes':
                return "DATE_FORMAT($fp, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')";
            case 'trimestre':
                return "QUARTER($fp) = QUARTER(CURDATE()) AND YEAR($fp) = YEAR(CURDATE())";
            case 'anio':
                return "YEAR($fp) = YEAR(CURDATE())";
            case 'semana':
            default:
                return "YEARWEEK($fp, 1) = YEARWEEK(CURDATE(), 1) AND YEAR($fp) = YEAR(CURDATE())";
        }
    }

    /**
     * @param string $periodo semana|mes|trimestre|anio
     * @param string $serieGrupo semana|mes — agrupación de la serie temporal (bloque B)
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    public static function getDashboard(string $periodo, string $serieGrupo): array
    {
        $periodo = in_array($periodo, ['semana', 'mes', 'trimestre', 'anio'], true) ? $periodo : 'mes';
        $serieGrupo = ($serieGrupo === 'mes') ? 'mes' : 'semana';

        try {
            $db = new DatabaseSegundometro();
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        $whereP = self::sqlWherePeriodo($periodo);
        $f = self::sqlFechaRef();
        $tab = self::TABLA;

        $sqlKpi = "
            SELECT
                COUNT(*) AS total_cargos,
                COALESCE(SUM(gc.monto_valor), 0) AS total_monto,
                SUM(CASE WHEN COALESCE(gc.condonado, 0) = 1 THEN 1 ELSE 0 END) AS cnt_condonado,
                COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 1 THEN gc.monto_valor ELSE 0 END), 0) AS monto_condonado,
                SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) = 1 THEN 1 ELSE 0 END) AS cnt_parcial,
                COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) = 1 THEN gc.monto_valor ELSE 0 END), 0) AS monto_parcial,
                SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) NOT IN (1, 2) THEN 1 ELSE 0 END) AS cnt_pendiente,
                COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) NOT IN (1, 2) THEN gc.monto_valor ELSE 0 END), 0) AS monto_pendiente
            FROM {$tab} gc
            WHERE {$whereP}
        ";

        try {
            $kpi = $db->queryOne($sqlKpi) ?: [];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        $whereFp = self::sqlWherePeriodoFechaPago($periodo);
        $sqlRec = "
            SELECT
                COUNT(*) AS cnt_recuperado,
                COALESCE(SUM(COALESCE(gc.monto_parcial_pagado, 0)), 0) AS monto_recuperado
            FROM {$tab} gc
            WHERE gc.fecha_pago IS NOT NULL
              AND {$whereFp}
              AND COALESCE(gc.condonado, 0) = 0
              AND COALESCE(gc.estatus_pago, 0) IN (1, 2)
        ";
        try {
            $rowRec = $db->queryOne($sqlRec) ?: [];
            $kpi['cnt_recuperado'] = (int) ($rowRec['cnt_recuperado'] ?? 0);
            $kpi['monto_recuperado'] = (float) ($rowRec['monto_recuperado'] ?? 0);
        } catch (\Throwable $e) {
            $kpi['cnt_recuperado'] = 0;
            $kpi['monto_recuperado'] = 0.0;
        }

        $totalM = (float) ($kpi['total_monto'] ?? 0);
        $pct = static function (float $m) use ($totalM): float {
            return $totalM > 0 ? round(($m / $totalM) * 100, 2) : 0.0;
        };

        $kpis = [
            'total_generado' => [
                'monto' => round($totalM, 2),
                'count' => (int) ($kpi['total_cargos'] ?? 0),
                'pct' => 100.0,
            ],
            'recuperado' => [
                'monto' => round((float) ($kpi['monto_recuperado'] ?? 0), 2),
                'count' => (int) ($kpi['cnt_recuperado'] ?? 0),
                'pct' => $pct((float) ($kpi['monto_recuperado'] ?? 0)),
            ],
            'pago_parcial' => [
                'monto' => round((float) ($kpi['monto_parcial'] ?? 0), 2),
                'count' => (int) ($kpi['cnt_parcial'] ?? 0),
                'pct' => $pct((float) ($kpi['monto_parcial'] ?? 0)),
            ],
            'pendiente' => [
                'monto' => round((float) ($kpi['monto_pendiente'] ?? 0), 2),
                'count' => (int) ($kpi['cnt_pendiente'] ?? 0),
                'pct' => $pct((float) ($kpi['monto_pendiente'] ?? 0)),
            ],
            'condonado' => [
                'monto' => round((float) ($kpi['monto_condonado'] ?? 0), 2),
                'count' => (int) ($kpi['cnt_condonado'] ?? 0),
                'pct' => $pct((float) ($kpi['monto_condonado'] ?? 0)),
            ],
        ];

        $mRecM = (float) ($kpis['recuperado']['monto'] ?? 0);
        $mCondM = (float) ($kpis['condonado']['monto'] ?? 0);
        $mPenResidual = max(0.0, round($totalM - $mRecM - $mCondM, 2));
        $kpis['pendiente'] = [
            'monto' => $mPenResidual,
            'count' => (int) ($kpi['cnt_pendiente'] ?? 0),
            'pct' => $pct($mPenResidual),
        ];

        // --- Serie temporal: siempre ambas agrupaciones en una sola respuesta (el toggle Semana/Mes en el front no debe repetir todo el dashboard).
        $serieSemana = [];
        $serieMes = [];
        $mapSerie = static function (array $rows): array {
            $out = [];
            foreach ($rows as $r) {
                $out[] = [
                    'periodo' => (string) ($r['periodo_key'] ?? ''),
                    'periodo_ini' => $r['periodo_ini'] ?? null,
                    'monto_pagado' => round((float) ($r['monto_pagado'] ?? 0), 2),
                    'monto_parcial' => round((float) ($r['monto_parcial'] ?? 0), 2),
                    'monto_sin_pago' => round((float) ($r['monto_sin_pago'] ?? 0), 2),
                ];
            }

            return $out;
        };
        try {
            $sqlSerieMes = "
                SELECT
                    DATE_FORMAT({$f}, '%Y-%m') AS periodo_key,
                    MIN({$f}) AS periodo_ini,
                    COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) = 2 THEN gc.monto_valor ELSE 0 END), 0) AS monto_pagado,
                    COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) = 1 THEN gc.monto_valor ELSE 0 END), 0) AS monto_parcial,
                    COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) NOT IN (1, 2) THEN gc.monto_valor ELSE 0 END), 0) AS monto_sin_pago
                FROM {$tab} gc
                WHERE {$f} >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 7 MONTH), '%Y-%m-01')
                GROUP BY DATE_FORMAT({$f}, '%Y-%m')
                ORDER BY periodo_key ASC
            ";
            $sqlSerieSemana = "
                SELECT
                    YEARWEEK({$f}, 1) AS periodo_key,
                    MIN({$f}) AS periodo_ini,
                    COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) = 2 THEN gc.monto_valor ELSE 0 END), 0) AS monto_pagado,
                    COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) = 1 THEN gc.monto_valor ELSE 0 END), 0) AS monto_parcial,
                    COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) NOT IN (1, 2) THEN gc.monto_valor ELSE 0 END), 0) AS monto_sin_pago
                FROM {$tab} gc
                WHERE {$f} >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
                GROUP BY YEARWEEK({$f}, 1)
                ORDER BY periodo_key ASC
            ";
            $serieMes = $mapSerie($db->queryAll($sqlSerieMes));
            $serieSemana = $mapSerie($db->queryAll($sqlSerieSemana));
        } catch (\Throwable $e) {
            $serieMes = [];
            $serieSemana = [];
        }
        $serie = $serieGrupo === 'mes' ? $serieMes : $serieSemana;

        /**
         * Con filtro «Semana»: barras por día (lunes–domingo ISO).
         * - Verde «Pagado»: SUM(monto_parcial_pagado) agrupado por **DATE(fecha_pago)** (misma lógica que el KPI Recuperado).
         *   Antes se usaba fecha del cargo + monto_valor solo si estatus=2, por eso no coincidía con el total semanal (~186K vs ~12K).
         * - Rojo «Sin pago» (stack): pendiente + parcial por **fecha del cargo** en la semana ISO (misma base que antes).
         */
        $serieDias = [];
        if ($periodo === 'semana') {
            try {
                $diaPagoExpr = 'DATE(gc.fecha_pago)';
                $sqlSerieDiaPagos = "
                    SELECT
                        {$diaPagoExpr} AS periodo_key,
                        {$diaPagoExpr} AS periodo_ini,
                        COALESCE(SUM(COALESCE(gc.monto_parcial_pagado, 0)), 0) AS monto_pagado
                    FROM {$tab} gc
                    WHERE gc.fecha_pago IS NOT NULL
                      AND {$whereFp}
                      AND COALESCE(gc.condonado, 0) = 0
                      AND COALESCE(gc.estatus_pago, 0) IN (1, 2)
                    GROUP BY {$diaPagoExpr}
                    ORDER BY periodo_key ASC
                ";
                $sqlSerieDiaCargo = "
                    SELECT
                        DATE({$f}) AS periodo_key,
                        DATE({$f}) AS periodo_ini,
                        COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) = 1 THEN gc.monto_valor ELSE 0 END), 0) AS monto_parcial,
                        COALESCE(SUM(CASE WHEN COALESCE(gc.condonado, 0) = 0 AND COALESCE(gc.estatus_pago, 0) NOT IN (1, 2) THEN gc.monto_valor ELSE 0 END), 0) AS monto_sin_pago
                    FROM {$tab} gc
                    WHERE YEARWEEK({$f}, 1) = YEARWEEK(CURDATE(), 1)
                      AND YEAR({$f}) = YEAR(CURDATE())
                    GROUP BY DATE({$f})
                    ORDER BY periodo_key ASC
                ";
                $byDayPago = [];
                foreach ($db->queryAll($sqlSerieDiaPagos) as $r) {
                    $key = substr((string) ($r['periodo_ini'] ?? $r['periodo_key'] ?? ''), 0, 10);
                    if ($key !== '') {
                        $byDayPago[$key] = (float) ($r['monto_pagado'] ?? 0);
                    }
                }
                $byDayCargo = [];
                foreach ($db->queryAll($sqlSerieDiaCargo) as $r) {
                    $key = substr((string) ($r['periodo_ini'] ?? $r['periodo_key'] ?? ''), 0, 10);
                    if ($key !== '') {
                        $byDayCargo[$key] = [
                            'monto_parcial' => (float) ($r['monto_parcial'] ?? 0),
                            'monto_sin_pago' => (float) ($r['monto_sin_pago'] ?? 0),
                        ];
                    }
                }
                $refIso = new \DateTimeImmutable('today');
                $isoYear = (int) $refIso->format('o');
                $isoWeek = (int) $refIso->format('W');
                $lunes = $refIso->setISODate($isoYear, $isoWeek, 1);
                for ($i = 0; $i < 7; $i++) {
                    $d = $lunes->modify('+' . $i . ' days')->format('Y-m-d');
                    $cargo = $byDayCargo[$d] ?? ['monto_parcial' => 0.0, 'monto_sin_pago' => 0.0];
                    $serieDias[] = [
                        'periodo' => $d,
                        'periodo_ini' => $d,
                        'monto_pagado' => round($byDayPago[$d] ?? 0.0, 2),
                        'monto_parcial' => round($cargo['monto_parcial'], 2),
                        'monto_sin_pago' => round($cargo['monto_sin_pago'], 2),
                    ];
                }
            } catch (\Throwable $e) {
                $serieDias = [];
            }
        }

        // --- Bloque C: generación del día
        $hoy = [
            'cargos_hoy' => 0,
            'creditos_distintos_hoy' => 0,
            'monto_generado_hoy' => 0.0,
            'cargo_base_unitario' => 0.0,
            'creditos_multiples_cargos_mismo_dia' => 0,
        ];
        try {
            $rowHoy = $db->queryOne("
                SELECT
                    COUNT(*) AS cargos_hoy,
                    COUNT(DISTINCT gc.Id_credito) AS creditos_distintos,
                    COALESCE(SUM(gc.monto_valor), 0) AS monto_hoy,
                    COALESCE(AVG(gc.monto_valor), 0) AS avg_monto
                FROM {$tab} gc
                WHERE {$f} = CURDATE()
            ");
            if ($rowHoy) {
                $hoy['cargos_hoy'] = (int) ($rowHoy['cargos_hoy'] ?? 0);
                $hoy['creditos_distintos_hoy'] = (int) ($rowHoy['creditos_distintos'] ?? 0);
                $hoy['monto_generado_hoy'] = round((float) ($rowHoy['monto_hoy'] ?? 0), 2);
                $hoy['cargo_base_unitario'] = round((float) ($rowHoy['avg_monto'] ?? 0), 2);
            }
            $rowMulti = $db->queryOne("
                SELECT COUNT(*) AS n FROM (
                    SELECT gc.Id_credito
                    FROM {$tab} gc
                    WHERE {$f} = CURDATE()
                    GROUP BY gc.Id_credito
                    HAVING COUNT(*) > 1
                ) x
            ");
            $hoy['creditos_multiples_cargos_mismo_dia'] = (int) ($rowMulti['n'] ?? 0);
        } catch (\Throwable $e) {
            // deja defaults
        }

        // --- Bloque D: top deuda pendiente
        $top = [];
        try {
            $top = $db->queryAll("
                SELECT
                    gc.Id_credito AS id_credito,
                    MAX(gc.Nombre_cliente) AS nombre_cliente,
                    COUNT(*) AS num_cargos,
                    COALESCE(SUM(gc.monto_valor - COALESCE(gc.monto_parcial_pagado, 0) - COALESCE(gc.condonacion_parcial_monto, 0)), 0) AS total_deuda
                FROM {$tab} gc
                WHERE COALESCE(gc.condonado, 0) = 0
                  AND COALESCE(gc.estatus_pago, 0) NOT IN (2)
                GROUP BY gc.Id_credito
                HAVING total_deuda > 0
                ORDER BY total_deuda DESC
                LIMIT 6
            ");
            foreach ($top as &$t) {
                $t['total_deuda'] = round((float) ($t['total_deuda'] ?? 0), 2);
                $t['id_credito'] = (int) ($t['id_credito'] ?? 0);
                $t['num_cargos'] = (int) ($t['num_cargos'] ?? 0);
            }
            unset($t);
        } catch (\Throwable $e) {
            $top = [];
        }

        // --- Bloque E: indicadores
        $mCond = (float) ($kpi['monto_condonado'] ?? 0);
        $mRec = (float) ($kpi['monto_recuperado'] ?? 0);
        $mPar = (float) ($kpi['monto_parcial'] ?? 0);
        $tasaCond = $totalM > 0 ? round(($mCond / $totalM) * 100, 2) : 0.0;
        $pctRecReal = $totalM > 0 ? round(($mRec / $totalM) * 100, 2) : 0.0;
        $pctRecParcial = $totalM > 0 ? round((($mRec + $mPar) / $totalM) * 100, 2) : 0.0;

        $totalCargosPeriodo = (int) ($kpi['total_cargos'] ?? 0);
        $clientesDeuda = 0;
        $moraProm = 0.0;
        try {
            $rClMora = $db->queryOne("
                SELECT
                    COUNT(DISTINCT gc.Id_credito) AS n,
                    AVG(CASE WHEN {$f} IS NOT NULL THEN DATEDIFF(CURDATE(), {$f}) ELSE NULL END) AS mora_dias
                FROM {$tab} gc
                WHERE {$whereP}
                  AND COALESCE(gc.condonado, 0) = 0
                  AND COALESCE(gc.estatus_pago, 0) NOT IN (2)
            ");
            $clientesDeuda = (int) ($rClMora['n'] ?? 0);
            $moraProm = round((float) ($rClMora['mora_dias'] ?? 0), 2);
        } catch (\Throwable $e) {
            // ignore
        }

        $cargoBase = 0.0;
        try {
            $rCb = $db->queryOne("
                SELECT COALESCE(AVG(gc.monto_valor), 0) AS cb
                FROM {$tab} gc
                WHERE {$whereP}
            ");
            $cargoBase = round((float) ($rCb['cb'] ?? 0), 2);
        } catch (\Throwable $e) {
            $cargoBase = round($totalCargosPeriodo > 0 ? $totalM / max(1, $totalCargosPeriodo) : 0, 2);
        }

        $indicadores = [
            'cargo_base_unitario' => $cargoBase,
            'total_cargos_periodo' => $totalCargosPeriodo,
            'clientes_con_deuda_activa' => $clientesDeuda,
            'tasa_condonacion_pct' => $tasaCond,
            'pct_recuperacion_real' => $pctRecReal,
            'pct_recuperacion_mas_parcial' => $pctRecParcial,
            'mora_promedio_dias' => $moraProm,
        ];

        $donut = [
            'recuperado' => $kpis['recuperado']['monto'],
            'pago_parcial' => 0.0,
            'pendiente' => $kpis['pendiente']['monto'],
            'condonado' => $kpis['condonado']['monto'],
        ];

        return [
            'success' => true,
            'datos' => [
                'periodo' => $periodo,
                'periodo_label' => self::periodoLabelActual($periodo),
                'periodo_badge' => self::periodoBadgeRango($periodo),
                'serie_grupo' => $serieGrupo,
                'kpis' => $kpis,
                'serie' => $serie,
                'serie_semana' => $serieSemana,
                'serie_mes' => $serieMes,
                'serie_dias' => $serieDias,
                'donut' => $donut,
                'hoy' => $hoy,
                'top_clientes' => $top,
                'indicadores' => $indicadores,
            ],
        ];
    }
}
