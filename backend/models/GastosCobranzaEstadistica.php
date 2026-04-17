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

    /** Inicio (inclusive) y fin (exclusivo) del periodo actual en CDMX. */
    private static function rangoPeriodo(string $periodo): array
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $now = new \DateTimeImmutable('now', $tz);

        switch ($periodo) {
            case 'semana':
                $isoY = (int) $now->format('o');
                $isoW = (int) $now->format('W');
                $inicio = $now->setISODate($isoY, $isoW, 1)->setTime(0, 0, 0);
                $fin = $inicio->modify('+7 days');
                break;
            case 'trimestre':
                $month = (int) $now->format('n');
                $q = (int) ceil($month / 3);
                $startMonth = 3 * $q - 2;
                $inicio = $now->setDate((int) $now->format('Y'), $startMonth, 1)->setTime(0, 0, 0);
                $fin = $inicio->modify('+3 months');
                break;
            case 'anio':
                $inicio = $now->setDate((int) $now->format('Y'), 1, 1)->setTime(0, 0, 0);
                $fin = $inicio->modify('+1 year');
                break;
            case 'mes':
            default:
                $inicio = $now->modify('first day of this month')->setTime(0, 0, 0);
                $fin = $inicio->modify('+1 month');
                break;
        }

        return [
            'inicio' => $inicio->format('Y-m-d'),
            'fin' => $fin->format('Y-m-d'),
        ];
    }

    /**
     * Conteo de créditos por estado (universo por created_at en rango).
     * Lógica basada en id_credito: condonado > recuperado > pendiente.
     *
     * @return array{cnt_recuperado: int, cnt_condonado: int, cnt_pendiente: int, cnt_todos: int}|null
     */
    private static function fetchKpiCountsPorEstado(DatabaseSegundometro $db, string $inicioYmd, string $finExclusiveYmd): ?array
    {
        $tab = self::TABLA;
        $sql = "
            SELECT
                COUNT(*) AS cnt_todos,
                SUM(CASE WHEN COALESCE(e.es_condonado, 0) = 1 THEN 1 ELSE 0 END) AS cnt_condonado,
                SUM(
                    CASE
                        WHEN COALESCE(e.es_condonado, 0) = 0
                         AND COALESCE(e.es_recuperado, 0) = 1 THEN 1
                        ELSE 0
                    END
                ) AS cnt_recuperado,
                SUM(
                    CASE
                        WHEN COALESCE(e.es_condonado, 0) = 0
                         AND COALESCE(e.es_recuperado, 0) = 0 THEN 1
                        ELSE 0
                    END
                ) AS cnt_pendiente
            FROM (
                SELECT DISTINCT gc.id_credito
                FROM `{$tab}` gc
                WHERE DATE(gc.created_at) >= :inicio
                  AND DATE(gc.created_at) < :fin
            ) u
            LEFT JOIN (
                SELECT
                    gc.id_credito,
                    MAX(
                        CASE
                            WHEN gc.condonado = 1
                              OR COALESCE(gc.condonacion_parcial_monto, 0) > 0
                            THEN 1
                            ELSE 0
                        END
                    ) AS es_condonado,
                    MAX(
                        CASE
                            WHEN gc.fecha_pago IS NOT NULL
                              OR gc.estatus_pago IN (1, 2)
                            THEN 1
                            ELSE 0
                        END
                    ) AS es_recuperado
                FROM `{$tab}` gc
                GROUP BY gc.id_credito
            ) e ON e.id_credito = u.id_credito
        ";
        try {
            $row = $db->queryOne($sql, ['inicio' => $inicioYmd, 'fin' => $finExclusiveYmd]);
        } catch (\Throwable $e) {
            return null;
        }
        if (!is_array($row)) {
            return null;
        }

        return [
            'cnt_recuperado' => (int) ($row['cnt_recuperado'] ?? 0),
            'cnt_condonado'  => (int) ($row['cnt_condonado'] ?? 0),
            'cnt_pendiente'  => (int) ($row['cnt_pendiente'] ?? 0),
            'cnt_todos'      => (int) ($row['cnt_todos'] ?? 0),
        ];
    }

    private static function normalizarYmd(?string $ymd): ?string
    {
        $s = trim((string) $ymd);
        if ($s === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return null;
        }
        [$y, $m, $d] = array_map('intval', explode('-', $s));
        if (!checkdate($m, $d, $y)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $y, $m, $d);
    }

    private static function labelRangoCustom(string $inicioYmd, string $finYmd): string
    {
        return $inicioYmd . ' → ' . $finYmd;
    }

    /**
     * Consulta base diaria aportada por negocio.
     * El eje principal es fecha_pago, con nacidos por created_at y condonado por fecha_condonacion.
     */
    private static function sqlBaseDiaria(): string
    {
        $tab = self::TABLA;
        return "
            WITH base AS (
                SELECT
                    YEAR(gc.fecha_pago) AS anio,
                    MONTH(gc.fecha_pago) AS mes_num,
                    ELT(
                        MONTH(gc.fecha_pago),
                        'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
                    ) AS mes,
                    WEEK(gc.fecha_pago, 3) AS semana,
                    DAY(gc.fecha_pago) AS dia,
                    DATE(gc.fecha_pago) AS fecha_dia,
                    MIN(DATE_SUB(DATE(gc.fecha_pago), INTERVAL WEEKDAY(gc.fecha_pago) DAY)) AS semana_fecha_inicio,
                    MIN(
                        DATE_ADD(
                            DATE_SUB(DATE(gc.fecha_pago), INTERVAL WEEKDAY(gc.fecha_pago) DAY),
                            INTERVAL 6 DAY
                        )
                    ) AS semana_fecha_fin,
                    (
                        SELECT COUNT(*)
                        FROM {$tab} sub
                        WHERE DATE(sub.created_at) = MAX(DATE(gc.fecha_pago))
                    ) AS registros_nacidos,
                    (
                        SELECT COALESCE(SUM(COALESCE(sub.monto_valor, 0)), 0)
                        FROM {$tab} sub
                        WHERE DATE(sub.created_at) = MAX(DATE(gc.fecha_pago))
                    ) AS monto_nacido,
                    SUM(
                        CASE
                            WHEN gc.fecha_pago IS NOT NULL OR COALESCE(gc.estatus_pago, 0) IN (1, 2)
                                THEN COALESCE(gc.monto_parcial_pagado, 0)
                            ELSE 0
                        END
                    ) AS total_recuperado,
                    (
                        SELECT COALESCE(SUM(
                            CASE
                                WHEN sub.condonado = 1 AND COALESCE(sub.condonacion_parcial_monto, 0) = 0
                                    THEN COALESCE(sub.monto_valor, 0)
                                WHEN sub.condonado = 1 AND COALESCE(sub.condonacion_parcial_monto, 0) > 0
                                    THEN COALESCE(sub.monto_valor, 0) - COALESCE(sub.monto_parcial_pagado, 0)
                                WHEN sub.condonado = 0 AND COALESCE(sub.condonacion_parcial_monto, 0) > 0
                                    THEN COALESCE(sub.condonacion_parcial_monto, 0)
                                ELSE 0
                            END
                        ), 0)
                        FROM {$tab} sub
                        WHERE DATE(sub.fecha_condonacion) = MAX(DATE(gc.fecha_pago))
                    ) AS total_condonado
                FROM {$tab} gc
                WHERE gc.fecha_pago >= :fecha_inicio
                  AND gc.fecha_pago < :fecha_fin
                  AND gc.fecha_pago IS NOT NULL
                GROUP BY
                    YEAR(gc.fecha_pago),
                    MONTH(gc.fecha_pago),
                    ELT(
                        MONTH(gc.fecha_pago),
                        'Enero','Febrero','Marzo','Abril','Mayo','Junio',
                        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
                    ),
                    WEEK(gc.fecha_pago, 3),
                    DAY(gc.fecha_pago),
                    DATE(gc.fecha_pago)
            )
            SELECT
                anio,
                mes_num,
                mes,
                semana,
                dia,
                fecha_dia,
                DATE_FORMAT(fecha_dia, '%d/%m/%Y') AS fecha_formato,
                semana_fecha_inicio,
                semana_fecha_fin,
                CONCAT(
                    'Semana ', semana,
                    ' | ', DATE_FORMAT(semana_fecha_inicio, '%d/%m/%Y'),
                    ' - ', DATE_FORMAT(semana_fecha_fin, '%d/%m/%Y')
                ) AS periodo_semana,
                COALESCE(registros_nacidos, 0) AS registros_nacidos,
                COALESCE(monto_nacido, 0) AS monto_nacido,
                COALESCE(total_recuperado, 0) AS total_recuperado,
                COALESCE(total_condonado, 0) AS total_condonado
            FROM base
            ORDER BY anio, mes_num, semana, dia
        ";
    }

    /**
     * @param string $periodo semana|mes|trimestre|anio
     * @param string $serieGrupo semana|mes — agrupación de la serie temporal (bloque B)
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    public static function getDashboard(string $periodo, string $serieGrupo, ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $periodo = in_array($periodo, ['semana', 'mes', 'trimestre', 'anio'], true) ? $periodo : 'mes';
        $serieGrupo = ($serieGrupo === 'mes') ? 'mes' : 'semana';

        try {
            $db = new DatabaseSegundometro();
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        $rango = self::rangoPeriodo($periodo);
        $inicioSel = self::normalizarYmd($fechaInicio);
        $finSel = self::normalizarYmd($fechaFin);
        $usaRangoCustom = false;
        if ($inicioSel !== null && $finSel !== null && strcmp($inicioSel, $finSel) <= 0) {
            $usaRangoCustom = true;
            $inicioDt = new \DateTimeImmutable($inicioSel . ' 00:00:00');
            $finDtExcl = (new \DateTimeImmutable($finSel . ' 00:00:00'))->modify('+1 day');
            $rango = [
                'inicio' => $inicioDt->format('Y-m-d'),
                'fin' => $finDtExcl->format('Y-m-d'),
            ];
        }
        $rows = [];
        try {
            $rows = $db->queryAll(
                self::sqlBaseDiaria(),
                ['fecha_inicio' => $rango['inicio'], 'fecha_fin' => $rango['fin']]
            );
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        $totalRegistrosNacidos = 0;
        $totalMontoNacido = 0.0;
        $totalRecuperado = 0.0;
        $totalCondonado = 0.0;
        $serieDias = [];
        $semanaMap = [];
        $mesMap = [];
        foreach ($rows as $row) {
            $fechaDia = (string) ($row['fecha_dia'] ?? '');
            if ($fechaDia === '') {
                continue;
            }
            $regN = (int) ($row['registros_nacidos'] ?? 0);
            $mNacido = (float) ($row['monto_nacido'] ?? 0);
            $mRec = (float) ($row['total_recuperado'] ?? 0);
            $mCond = (float) ($row['total_condonado'] ?? 0);
            $mPendiente = max(0.0, $mNacido - $mRec - $mCond);

            $totalRegistrosNacidos += $regN;
            $totalMontoNacido += $mNacido;
            $totalRecuperado += $mRec;
            $totalCondonado += $mCond;

            $serieDias[] = [
                'periodo' => $fechaDia,
                'periodo_ini' => $fechaDia,
                'monto_pagado' => round($mRec, 2),
                'monto_parcial' => 0.0,
                'monto_sin_pago' => round($mPendiente, 2),
            ];

            $semanaKey = sprintf('%04d-W%02d', (int) ($row['anio'] ?? 0), (int) ($row['semana'] ?? 0));
            if (!isset($semanaMap[$semanaKey])) {
                $semanaMap[$semanaKey] = [
                    'periodo' => $semanaKey,
                    'periodo_ini' => substr((string) ($row['semana_fecha_inicio'] ?? $fechaDia), 0, 10),
                    'periodo_semana' => (string) ($row['periodo_semana'] ?? ''),
                    'monto_pagado' => 0.0,
                    'monto_parcial' => 0.0,
                    'monto_sin_pago' => 0.0,
                ];
            }
            $semanaMap[$semanaKey]['monto_pagado'] += $mRec;
            $semanaMap[$semanaKey]['monto_sin_pago'] += $mPendiente;

            $mesKey = sprintf('%04d-%02d', (int) ($row['anio'] ?? 0), (int) ($row['mes_num'] ?? 0));
            if (!isset($mesMap[$mesKey])) {
                $mesMap[$mesKey] = [
                    'periodo' => $mesKey,
                    'periodo_ini' => substr($fechaDia, 0, 7) . '-01',
                    'monto_pagado' => 0.0,
                    'monto_parcial' => 0.0,
                    'monto_sin_pago' => 0.0,
                ];
            }
            $mesMap[$mesKey]['monto_pagado'] += $mRec;
            $mesMap[$mesKey]['monto_sin_pago'] += $mPendiente;
        }

        ksort($semanaMap);
        ksort($mesMap);
        $serieSemana = array_values(array_map(static function (array $r): array {
            $r['monto_pagado'] = round((float) $r['monto_pagado'], 2);
            $r['monto_parcial'] = round((float) $r['monto_parcial'], 2);
            $r['monto_sin_pago'] = round((float) $r['monto_sin_pago'], 2);
            return $r;
        }, $semanaMap));
        $serieMes = array_values(array_map(static function (array $r): array {
            $r['monto_pagado'] = round((float) $r['monto_pagado'], 2);
            $r['monto_parcial'] = round((float) $r['monto_parcial'], 2);
            $r['monto_sin_pago'] = round((float) $r['monto_sin_pago'], 2);
            return $r;
        }, $mesMap));

        // En modo por periodo "semana", forzar lunes-domingo aunque no haya registros.
        if (!$usaRangoCustom && $periodo === 'semana') {
            $serieDiasMap = [];
            foreach ($serieDias as $sd) {
                $serieDiasMap[(string) $sd['periodo_ini']] = $sd;
            }
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = new \DateTimeImmutable('now', $tz);
            $isoY = (int) $now->format('o');
            $isoW = (int) $now->format('W');
            $lunes = $now->setISODate($isoY, $isoW, 1);
            $serieDiasSem = [];
            for ($i = 0; $i < 7; $i++) {
                $d = $lunes->modify('+' . $i . ' days')->format('Y-m-d');
                $serieDiasSem[] = $serieDiasMap[$d] ?? [
                    'periodo' => $d,
                    'periodo_ini' => $d,
                    'monto_pagado' => 0.0,
                    'monto_parcial' => 0.0,
                    'monto_sin_pago' => 0.0,
                ];
            }
            $serieDias = $serieDiasSem;
        }

        $serie = $serieGrupo === 'mes' ? $serieMes : $serieSemana;
        $totalM = round($totalMontoNacido, 2);
        $mRecM = round($totalRecuperado, 2);
        $mCondM = round($totalCondonado, 2);
        $mPenResidual = max(0.0, round($totalM - $mRecM - $mCondM, 2));
        $pct = static function (float $m) use ($totalM): float {
            return $totalM > 0 ? round(($m / $totalM) * 100, 2) : 0.0;
        };

        $kpis = [
            'total_generado' => [
                'monto' => round($totalM, 2),
                'count' => $totalRegistrosNacidos,
                'pct' => 100.0,
            ],
            'recuperado' => [
                'monto' => $mRecM,
                'count' => $totalRegistrosNacidos,
                'pct' => $pct($mRecM),
            ],
            'pendiente' => [
                'monto' => $mPenResidual,
                'count' => $totalRegistrosNacidos,
                'pct' => $pct($mPenResidual),
            ],
            'condonado' => [
                'monto' => $mCondM,
                'count' => $totalRegistrosNacidos,
                'pct' => $pct($mCondM),
            ],
        ];

        $cntPorEstado = self::fetchKpiCountsPorEstado($db, $rango['inicio'], $rango['fin']);
        if ($cntPorEstado !== null) {
            $cntTotal = max(0, (int) ($cntPorEstado['cnt_todos'] ?? 0));
            $cntRec = max(0, (int) ($cntPorEstado['cnt_recuperado'] ?? 0));
            $cntCond = max(0, (int) ($cntPorEstado['cnt_condonado'] ?? 0));
            $cntPend = max(0, $cntTotal - $cntRec - $cntCond);

            $kpis['total_generado']['count'] = $cntTotal;
            $kpis['recuperado']['count'] = $cntRec;
            $kpis['condonado']['count'] = $cntCond;
            $kpis['pendiente']['count'] = $cntPend;
        }

        $hoy = [
            'cargos_hoy' => 0,
            'creditos_distintos_hoy' => 0,
            'monto_generado_hoy' => 0.0,
            'cargo_base_unitario' => 0.0,
            'creditos_multiples_cargos_mismo_dia' => 0,
        ];
        foreach ($rows as $r) {
            $fh = substr((string) ($r['fecha_dia'] ?? ''), 0, 10);
            if ($fh === (new \DateTimeImmutable('today', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d')) {
                $hoy['cargos_hoy'] += (int) ($r['registros_nacidos'] ?? 0);
                $hoy['monto_generado_hoy'] += (float) ($r['monto_nacido'] ?? 0);
            }
        }
        $hoy['monto_generado_hoy'] = round((float) $hoy['monto_generado_hoy'], 2);
        $hoy['cargo_base_unitario'] = $hoy['cargos_hoy'] > 0
            ? round($hoy['monto_generado_hoy'] / $hoy['cargos_hoy'], 2)
            : 0.0;

        $top = [];

        $tasaCond = $totalM > 0 ? round(($mCondM / $totalM) * 100, 2) : 0.0;
        $pctRecReal = $totalM > 0 ? round(($mRecM / $totalM) * 100, 2) : 0.0;
        $cargoBase = $totalRegistrosNacidos > 0
            ? round($totalM / max(1, $totalRegistrosNacidos), 2)
            : 0.0;

        $indicadores = [
            'cargo_base_unitario' => $cargoBase,
            'total_cargos_periodo' => $totalRegistrosNacidos,
            'clientes_con_deuda_activa' => 0,
            'tasa_condonacion_pct' => $tasaCond,
            'pct_recuperacion_real' => $pctRecReal,
            'pct_recuperacion_mas_parcial' => $pctRecReal,
            'mora_promedio_dias' => 0.0,
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
                'periodo_label' => $usaRangoCustom
                    ? self::labelRangoCustom($inicioSel, $finSel)
                    : self::periodoLabelActual($periodo),
                'periodo_badge' => $usaRangoCustom
                    ? self::labelRangoCustom($inicioSel, $finSel)
                    : self::periodoBadgeRango($periodo),
                'fecha_inicio' => $usaRangoCustom ? $inicioSel : $rango['inicio'],
                'fecha_fin' => $usaRangoCustom
                    ? $finSel
                    : (new \DateTimeImmutable($rango['fin'] . ' 00:00:00'))->modify('-1 day')->format('Y-m-d'),
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
