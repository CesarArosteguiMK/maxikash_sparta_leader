<?php

namespace Models;

use Core\DatabaseSegundometro;

/**
 * Histórico semanal de primeros pagos a partir de `tbl_segundometro_histo` (__SPARTA_SECRET_REDACTED__).
 * Criterio de semana: columna **SEMANA** (misma etiqueta que Segundómetro / comparativa semanal).
 *
 * El resumen por semana replica métricas tipo primeros pagos: prioridad a cartera **martes a domingo**.
 * Si no hay filas en ese rango, se usa **lunes de cierre** (fecha exacta). Nunca se abre a toda la semana por `SEMANA` sola.
 * Nacimiento: misma columna que «Lunes de Cierre» (`Bucket_Morosidad_Real` en `tbl_segundometro_semana` → aquí en histórico).
 * Corte: misma escala que `Empresa::getVencimientosLunes` usando `COALESCE(Dias_mora_Lunes_*)` (primer valor no nulo).
 */
final class PrimerosPagosHistoricoSegundometro
{
    private const SEMANA_MAX_LEN = 160;

    /** Solo se ofrecen las últimas N semanas (por fecha de carga en histórico). */
    private const LISTA_SEMANAS_MAX = 4;

    /**
     * Ventana de filas a considerar para armar el ranking de semanas (evita GROUP BY sobre toda la tabla).
     * Con índice por fecha_hora_insert la consulta escala; sin índice sigue acotando filas vs. histórico completo.
     */
    private const LISTA_SEMANAS_LOOKBACK_DIAS = 30;

    /** Misma escala que el reporte en vivo (Reporteria / Empresa). */
    private const BUCKET_ORDER = [
        'a) Current',
        'b) 1 a 7 dias',
        'c) 8 a 30 dias',
        'd) 31 a 60 dias',
        'e) 61+ dias',
    ];

    /**
     * @return array{success:bool, mensaje?:string, datos?:list<array{semana:string,registros:int,ultimo_insert:string|null,ini:?string,fin:?string}>}
     */
    public static function listarSemanas(?int $limite = null): array
    {
        $limite = $limite ?? self::LISTA_SEMANAS_MAX;
        if ($limite < 1) {
            $limite = 1;
        }
        if ($limite > self::LISTA_SEMANAS_MAX) {
            $limite = self::LISTA_SEMANAS_MAX;
        }
        try {
            $db = new DatabaseSegundometro();
            // Leemos filas recientes ordenadas por inserción y deduplicamos semana en PHP.
            // Evita GROUP BY costoso sobre cientos de miles de filas.
            $limiteFilasRecientes = 350000;
            $sqlTop = 'SELECT CAST(SEMANA AS CHAR CHARACTER SET utf8mb4) AS semana,
                              fecha_hora_insert AS ultimo_insert
                       FROM tbl_segundometro_histo
                       WHERE fecha_hora_insert >= DATE_SUB(CURDATE(), INTERVAL ' . (int) self::LISTA_SEMANAS_LOOKBACK_DIAS . ' DAY)
                         AND SEMANA IS NOT NULL
                         AND SEMANA <> \'\'
                       ORDER BY fecha_hora_insert DESC
                       LIMIT ' . (int) $limiteFilasRecientes;
            $rows = $db->queryAll($sqlTop, null);
            $candidatas = [];
            $seen = [];
            foreach ($rows as $r) {
                $s = trim((string) ($r['semana'] ?? ''));
                if ($s === '') {
                    continue;
                }
                if (isset($seen[$s])) {
                    continue;
                }
                if (self::esEtiquetaSemanaActual($s)) {
                    continue;
                }
                $rango = self::resolverRangoMartesDomingoDesdeEtiquetaSemana($s);
                if ($rango === null) {
                    continue;
                }
                $seen[$s] = true;
                $candidatas[] = [
                    'semana' => $s,
                    'registros' => 0,
                    'ultimo_insert' => isset($r['ultimo_insert']) && $r['ultimo_insert'] !== null
                        ? (string) $r['ultimo_insert']
                        : null,
                    'ini' => $rango['martes'],
                    'fin' => $rango['domingo'],
                    'lunes_iso' => $rango['lunes_iso'],
                ];
                if (count($candidatas) >= $limite) {
                    break;
                }
            }

            $registrosPorSemana = self::contarRegistrosPorSemana($db, $candidatas);
            $out = [];
            foreach ($candidatas as $c) {
                $out[] = [
                    'semana' => $c['semana'],
                    'registros' => (int) ($registrosPorSemana[$c['semana']] ?? 0),
                    'ultimo_insert' => $c['ultimo_insert'],
                    'ini' => $c['ini'],
                    'fin' => $c['fin'],
                ];
                if (count($out) >= $limite) {
                    break;
                }
            }

            return ['success' => true, 'datos' => $out];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo leer el histórico (segundómetro).', 'error' => $e->getMessage()];
        }
    }

    /** Misma fuente que `Empresa::getVencimientosLunes` (bucket_nacio). */
    private static function columnaNacimientoComoLunesCierre(DatabaseSegundometro $db): string
    {
        $sql = "SHOW COLUMNS FROM tbl_segundometro_histo LIKE 'Bucket_Morosidad_Real'";
        $row = $db->queryOne($sql);
        if (is_array($row) && !empty($row)) {
            return 'Bucket_Morosidad_Real';
        }

        return 'Bucket_Morosidad';
    }

    /**
     * Igual que `Empresa::sqlExprMoraSoloLunes()` — mora del lunes ya cargada en histórico.
     */
    private static function sqlExprMoraSoloLunes(): string
    {
        $cols = [
            'Dias_mora_Lunes_23_50',
            'Dias_mora_Lunes_20_30',
            'Dias_mora_Lunes_18_30',
            'Dias_mora_Lunes_16_30',
            'Dias_mora_Lunes_14_30',
            'Dias_mora_Lunes_13_30',
            'Dias_mora_Lunes_11_30',
            'Dias_mora_Lunes_09_30',
            'Dias_mora_Lunes_07_30',
        ];
        $parts = [];
        foreach ($cols as $c) {
            $parts[] = '`' . $c . '`';
        }

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    /** Predicado WHERE: etiqueta de semana alineada a `trim()` del listado. */
    private static function sqlWhereSemanaParam(): string
    {
        return 'TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4)) = :sem';
    }

    /** Bucket de corte desde una expresión SQL de días mora (no solo nombre de columna). */
    private static function sqlBucketCorteDesdeMoraSqlExpr(string $moraExpr): string
    {
        return '(CASE
            WHEN (' . $moraExpr . ') IS NULL THEN NULL
            WHEN (' . $moraExpr . ') < 1 THEN \'a) Current\'
            WHEN (' . $moraExpr . ') BETWEEN 1 AND 7 THEN \'b) 1 a 7 dias\'
            WHEN (' . $moraExpr . ') BETWEEN 8 AND 30 THEN \'c) 8 a 30 dias\'
            WHEN (' . $moraExpr . ') BETWEEN 31 AND 60 THEN \'d) 31 a 60 dias\'
            ELSE \'e) 61+ dias\' END)';
    }

    /** Orden 0..4 para bucket de corte desde expresión mora. */
    private static function sqlBucketCorteOrdDesdeMoraSqlExpr(string $moraExpr): string
    {
        return '(CASE
            WHEN (' . $moraExpr . ') IS NULL THEN NULL
            WHEN (' . $moraExpr . ') < 1 THEN 0
            WHEN (' . $moraExpr . ') BETWEEN 1 AND 7 THEN 1
            WHEN (' . $moraExpr . ') BETWEEN 8 AND 30 THEN 2
            WHEN (' . $moraExpr . ') BETWEEN 31 AND 60 THEN 3
            ELSE 4 END)';
    }

    private static function esEtiquetaSemanaActual(string $etiqueta): bool
    {
        $semana = null;
        $anio = null;
        if (preg_match('/(?:semana\\s*)?(\\d{1,2})\\s*[-_\\/ ]\\s*(\\d{4})/iu', $etiqueta, $m) === 1) {
            $semana = (int) $m[1];
            $anio = (int) $m[2];
        } elseif (preg_match('/(?:semana\\s*)(\\d{1,2})/iu', $etiqueta, $m2) === 1) {
            $semana = (int) $m2[1];
        }
        if ($semana === null || $semana < 1 || $semana > 53) {
            return false;
        }
        if ($anio === null) {
            $anio = (int) (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('o');
        }

        $now = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
        $weekNow = (int) $now->format('W');
        $yearNow = (int) $now->format('o');

        return $semana === $weekNow && $anio === $yearNow;
    }

    /**
     * Etiqueta tipo «Semana 16-2026» (W/o del martes de negocio) → rango **martes–domingo**.
     *
     * @return array{martes:string,domingo:string,lunes_iso:string}|null fechas Y-m-d
     */
    private static function resolverRangoMartesDomingoDesdeEtiquetaSemana(string $etiqueta): ?array
    {
        if (preg_match('/(?:semana\\s*)?(\\d{1,2})\\s*[-_\\/ ]\\s*(\\d{4})/iu', $etiqueta, $m) !== 1) {
            return null;
        }
        $semana = (int) $m[1];
        $anio = (int) $m[2];
        if ($semana < 1 || $semana > 53 || $anio < 2000 || $anio > 2100) {
            return null;
        }
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
            $lunesIso = (new \DateTimeImmutable('now', $tz))->setISODate($anio, $semana, 1);
            $martes = $lunesIso->modify('+1 day');
            $domingo = $lunesIso->modify('+6 days');

            return [
                'martes' => $martes->format('Y-m-d'),
                'domingo' => $domingo->format('Y-m-d'),
                'lunes_iso' => $lunesIso->format('Y-m-d'),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * @return array{success:bool, mensaje?:string, datos?:array<string,mixed>, error?:string}
     */
    public static function resumenPorSemana(string $semana): array
    {
        $sem = self::normalizarSemanaParam($semana);
        if ($sem === null) {
            return ['success' => false, 'mensaje' => 'Parámetro de semana no válido.'];
        }
        try {
            $db = new DatabaseSegundometro();
            $rangoPv = self::resolverRangoMartesDomingoDesdeEtiquetaSemana($sem);
            if ($rangoPv === null) {
                return ['success' => false, 'mensaje' => 'La etiqueta de semana no tiene formato válido (Semana NN-AAAA).'];
            }
            $m1 = $rangoPv['martes'];
            $m2 = $rangoPv['domingo'];
            $lunesIso = $rangoPv['lunes_iso'];

            // Priorizamos lunes de cierre (volumen esperado de primeros pagos).
            $meta = self::queryMetaSemana($db, $sem, $m1, $m2, 'lunes', $lunesIso);
            $criterio = 'lunes_cierre';
            if ((int) ($meta['total'] ?? 0) < 1) {
                // Fallback controlado a martes-domingo (sin abrir a toda la semana).
                $meta = self::queryMetaSemana($db, $sem, $m1, $m2, 'rango', null);
                $criterio = 'martes_domingo';
            }
            $total = (int) ($meta['total'] ?? 0);
            if ($total < 1) {
                return ['success' => false, 'mensaje' => 'No hay datos para la semana indicada.'];
            }
            $colNacio = self::columnaNacimientoComoLunesCierre($db);

            $lunesPv = $criterio === 'lunes_cierre' ? $lunesIso : $m1;
            $modoFecha = $criterio === 'lunes_cierre' ? 'lunes' : 'rango';
            $lunesParam = $criterio === 'lunes_cierre' ? $lunesIso : null;
            $pares = self::queryDistribNacimientoCorte($db, $sem, $m1, $m2, $colNacio, $modoFecha, $lunesParam);
            $jerRows = self::queryJerarquiaAgregada($db, $sem, $m1, $m2, $colNacio, $modoFecha, $lunesParam);

            $nacDist = [];
            foreach (self::BUCKET_ORDER as $b) {
                $nacDist[$b] = 0;
            }
            $matriz = [];
            foreach (self::BUCKET_ORDER as $bn) {
                $matriz[$bn] = [];
                foreach (self::BUCKET_ORDER as $bc) {
                    $matriz[$bn][$bc] = 0;
                }
            }

            foreach ($pares as $row) {
                $bn = $row['bucket_nacio'] ?? null;
                $bc = $row['bucket_corte'] ?? null;
                $cnt = (int) ($row['cnt'] ?? 0);
                if ($bn !== null && $bn !== '' && isset($nacDist[$bn])) {
                    $nacDist[$bn] += $cnt;
                }
                if ($bn !== null && $bn !== '' && $bc !== null && $bc !== ''
                    && isset($matriz[$bn]) && isset($matriz[$bn][$bc])) {
                    $matriz[$bn][$bc] += $cnt;
                }
            }

            // Misma lógica que `Reporteria` / «Lunes de Cierre»: current al corte = nacidos Current + 1–7d ya en Current al corte.
            $totalCurrentNac = (int) ($nacDist['a) Current'] ?? 0);
            $mat17 = $matriz['b) 1 a 7 dias'] ?? [];
            $recuperados1a7 = (int) ($mat17['a) Current'] ?? 0);
            $total1a7Nac = (int) ($nacDist['b) 1 a 7 dias'] ?? 0);
            $currentMasRecuperados = $totalCurrentNac + $recuperados1a7;
            $pendientesPp = max(0, $total1a7Nac - $recuperados1a7);

            $bar = self::barGlobalCurrentVs17($nacDist);

            return [
                'success' => true,
                'datos' => [
                    'semana' => $sem,
                    'total' => $total,
                    'periodo_martes' => $m1,
                    'periodo_domingo' => $m2,
                    'rango_cartera_texto' => $criterio === 'lunes_cierre' ? ($lunesIso . ' a ' . $lunesIso) : ($m1 . ' a ' . $m2),
                    'criterio_fecha' => $criterio,
                    'lunes_primer_vencimiento' => $lunesPv,
                    'corte_label' => 'COALESCE(Dias_mora_Lunes_*) — mismo criterio que Lunes de Cierre',
                    'nacimiento' => [
                        'nac_dist' => $nacDist,
                        'bar_current_pct' => $bar['pC'],
                        'bar_17_pct' => $bar['p7'],
                        'mostrar_bar_global' => $bar['mostrar'],
                    ],
                    'corte' => [
                        'current_al_corte' => $currentMasRecuperados,
                        'pendientes_primeros_pagos' => $pendientesPp,
                    ],
                    'jerarquia_html' => self::renderJerarquiaHtmlDesdeAgregados($jerRows),
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'Error al armar el resumen de la semana.', 'error' => $e->getMessage()];
        }
    }

    private static function normalizarSemanaParam(string $semana): ?string
    {
        $s = trim($semana);
        if ($s === '' || strlen($s) > self::SEMANA_MAX_LEN) {
            return null;
        }
        if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F`]/u', $s)) {
            return null;
        }

        return $s;
    }

    /**
     * @return array{total:int}
     */
    private static function queryMetaSemana(
        DatabaseSegundometro $db,
        string $sem,
        string $martesIso,
        string $domingoIso,
        string $modoFecha,
        ?string $lunesIsoExacto
    ): array
    {
        [$whereFecha, $paramsExtra] = self::whereFechaSqlYParams($modoFecha, $martesIso, $domingoIso, $lunesIsoExacto);
        $sql = 'SELECT COUNT(*) AS total
                FROM tbl_segundometro_histo
                WHERE ' . self::sqlWhereSemanaParam() . $whereFecha;
        $params = ['sem' => $sem] + $paramsExtra;
        $r = $db->queryOne($sql, $params);

        return [
            'total' => (int) ($r['total'] ?? 0),
        ];
    }

    /**
     * @return array{0:string,1:array<string,string>}
     */
    private static function whereFechaSqlYParams(string $modoFecha, string $martesIso, string $domingoIso, ?string $lunesIsoExacto): array
    {
        if ($modoFecha === 'rango') {
            $fechasIso = [];
            $fechasDmy = [];
            $dt = new \DateTimeImmutable($martesIso);
            $fin = new \DateTimeImmutable($domingoIso);
            while ($dt <= $fin) {
                $fechasIso[] = $dt->format('Y-m-d');
                $fechasDmy[] = $dt->format('d/m/Y');
                $dt = $dt->modify('+1 day');
            }

            $phIso = [];
            $phDmy = [];
            $params = [];
            foreach ($fechasIso as $i => $fIso) {
                $kIso = 'fi' . $i;
                $kDmy = 'fd' . $i;
                $phIso[] = ':' . $kIso;
                $phDmy[] = ':' . $kDmy;
                $params[$kIso] = $fIso;
                $params[$kDmy] = $fechasDmy[$i];
            }

            $where = ' AND (Fecha_primer_vencimiento IN (' . implode(',', $phIso) . ') OR Fecha_primer_vencimiento IN (' . implode(',', $phDmy) . '))';
            return [$where, $params];
        }
        if ($modoFecha === 'lunes' && $lunesIsoExacto !== null && $lunesIsoExacto !== '') {
            $dtLunes = \DateTimeImmutable::createFromFormat('Y-m-d', $lunesIsoExacto);
            $lunesDmy = $dtLunes instanceof \DateTimeImmutable ? $dtLunes->format('d/m/Y') : $lunesIsoExacto;
            return [' AND (Fecha_primer_vencimiento = :lunes_iso OR Fecha_primer_vencimiento = :lunes_dmy)', ['lunes_iso' => $lunesIsoExacto, 'lunes_dmy' => $lunesDmy]];
        }

        return ['', []];
    }

    /**
     * Pares (nacimiento, corte) con conteo — pocas filas vs. leer toda la tabla al cliente.
     *
     * @return list<array{bucket_nacio:?string,bucket_corte:?string,cnt:int}>
     */
    private static function queryDistribNacimientoCorte(
        DatabaseSegundometro $db,
        string $sem,
        string $martesIso,
        string $domingoIso,
        string $colNacimiento,
        string $modoFecha,
        ?string $lunesIsoExacto
    ): array
    {
        $sqlBucketNacio = self::sqlBucketNacimientoCanonExpr($colNacimiento);
        $moraLunes = self::sqlExprMoraSoloLunes();
        $sqlBucketCorte = self::sqlBucketCorteDesdeMoraSqlExpr($moraLunes);
        [$whereFecha, $paramsExtra] = self::whereFechaSqlYParams($modoFecha, $martesIso, $domingoIso, $lunesIsoExacto);
        $sql = 'SELECT bn AS bucket_nacio, bc AS bucket_corte, COUNT(*) AS cnt
                FROM (
                    SELECT ' . $sqlBucketNacio . ' AS bn,
                           ' . $sqlBucketCorte . ' AS bc
                    FROM tbl_segundometro_histo
                    WHERE ' . self::sqlWhereSemanaParam() . $whereFecha . '
                ) t
                GROUP BY bn, bc';
        $params = ['sem' => $sem] + $paramsExtra;
        $rows = $db->queryAll($sql, $params);
        $out = [];
        foreach ($rows as $r) {
            $bn = $r['bucket_nacio'] ?? null;
            $bc = $r['bucket_corte'] ?? null;
            if ($bn === '') {
                $bn = null;
            }
            if ($bc === '') {
                $bc = null;
            }
            $out[] = [
                'bucket_nacio' => $bn !== null ? (string) $bn : null,
                'bucket_corte' => $bc !== null ? (string) $bc : null,
                'cnt' => (int) ($r['cnt'] ?? 0),
            ];
        }

        return $out;
    }

    /**
     * @return list<array{Territorial:string,Zonal:string,Jefe_de_Plaza:string,Gestor_Asignado:string,total:int,cobrados:int}>
     */
    private static function queryJerarquiaAgregada(
        DatabaseSegundometro $db,
        string $sem,
        string $martesIso,
        string $domingoIso,
        string $colNacimiento,
        string $modoFecha,
        ?string $lunesIsoExacto
    ): array
    {
        $ordN = self::sqlBucketNacimientoOrdExpr($colNacimiento);
        $moraLunes = self::sqlExprMoraSoloLunes();
        $ordC = self::sqlBucketCorteOrdDesdeMoraSqlExpr($moraLunes);
        [$whereFecha, $paramsExtra] = self::whereFechaSqlYParams($modoFecha, $martesIso, $domingoIso, $lunesIsoExacto);
        $sql = 'SELECT ter AS Territorial, zon AS Zonal, jefe AS Jefe_de_Plaza, gest AS Gestor_Asignado,
                       COUNT(*) AS total,
                       SUM(CASE WHEN ord_n IS NOT NULL AND ord_c IS NOT NULL
                                     AND ord_n BETWEEN 0 AND 4 AND ord_c BETWEEN 0 AND 4
                                     AND ord_c < ord_n
                                THEN 1 ELSE 0 END) AS cobrados
                FROM (
                    SELECT
                        COALESCE(NULLIF(TRIM(CAST(Territorial AS CHAR CHARACTER SET utf8mb4)), \'\'), \'(Sin territorial)\') AS ter,
                        COALESCE(NULLIF(TRIM(CAST(Zonal AS CHAR CHARACTER SET utf8mb4)), \'\'), \'(Sin zonal)\') AS zon,
                        COALESCE(NULLIF(TRIM(CAST(Jefe_de_Plaza AS CHAR CHARACTER SET utf8mb4)), \'\'), \'(Sin jefe)\') AS jefe,
                        COALESCE(NULLIF(TRIM(CAST(Gestor_Asignado AS CHAR CHARACTER SET utf8mb4)), \'\'), \'(Sin gestor)\') AS gest,
                        ' . $ordN . ' AS ord_n,
                        ' . $ordC . ' AS ord_c
                    FROM tbl_segundometro_histo
                    WHERE ' . self::sqlWhereSemanaParam() . $whereFecha . '
                ) x
                GROUP BY ter, zon, jefe, gest';
        $params = ['sem' => $sem] + $paramsExtra;
        $rows = $db->queryAll($sql, $params);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'Territorial' => (string) ($r['Territorial'] ?? ''),
                'Zonal' => (string) ($r['Zonal'] ?? ''),
                'Jefe_de_Plaza' => (string) ($r['Jefe_de_Plaza'] ?? ''),
                'Gestor_Asignado' => (string) ($r['Gestor_Asignado'] ?? ''),
                'total' => (int) ($r['total'] ?? 0),
                'cobrados' => (int) ($r['cobrados'] ?? 0),
            ];
        }

        return $out;
    }

    private static function contarPrimerosPagosRango(DatabaseSegundometro $db, string $sem, string $martesIso, string $domingoIso): int
    {
        [$whereFecha, $paramsExtra] = self::whereFechaSqlYParams('rango', $martesIso, $domingoIso, null);
        $r = $db->queryOne(
            'SELECT COUNT(*) AS c
             FROM tbl_segundometro_histo
             WHERE ' . self::sqlWhereSemanaParam() . $whereFecha,
            ['sem' => $sem] + $paramsExtra
        );

        return (int) ($r['c'] ?? 0);
    }

    private static function contarPrimerosPagosLunes(DatabaseSegundometro $db, string $sem, string $lunesIso): int
    {
        [$whereFecha, $paramsExtra] = self::whereFechaSqlYParams('lunes', $lunesIso, $lunesIso, $lunesIso);
        $r = $db->queryOne(
            'SELECT COUNT(*) AS c
             FROM tbl_segundometro_histo
             WHERE ' . self::sqlWhereSemanaParam() . $whereFecha,
            ['sem' => $sem] + $paramsExtra
        );

        return (int) ($r['c'] ?? 0);
    }

    /**
     * @param list<array{semana:string,registros:int,ultimo_insert:?string,ini:string,fin:string,lunes_iso:string}> $candidatas
     * @return array<string,int>
     */
    private static function contarRegistrosPorSemana(DatabaseSegundometro $db, array $candidatas): array
    {
        if ($candidatas === []) {
            return [];
        }
        $placeholders = [];
        $params = [];
        foreach ($candidatas as $i => $c) {
            $k = 's' . $i;
            $placeholders[] = ':' . $k;
            $params[$k] = $c['semana'];
        }
        $sql = 'SELECT TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4)) AS semana, COUNT(*) AS c
                FROM tbl_segundometro_histo
                WHERE TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4)) IN (' . implode(',', $placeholders) . ')
                GROUP BY TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4))';
        $rows = $db->queryAll($sql, $params);
        $out = [];
        foreach ($rows as $r) {
            $sem = trim((string) ($r['semana'] ?? ''));
            if ($sem === '') {
                continue;
            }
            $out[$sem] = (int) ($r['c'] ?? 0);
        }

        return $out;
    }


    /** Expresión SQL: etiqueta canónica de nacimiento o NULL. */
    private static function sqlBucketNacimientoCanonExpr(string $col): string
    {
        return '(CASE TRIM(CAST(`' . $col . '` AS CHAR CHARACTER SET utf8mb4))
            WHEN \'a) Current\' THEN \'a) Current\'
            WHEN \'b) 1 a 7 dias\' THEN \'b) 1 a 7 dias\'
            WHEN \'b) 1 a 7 días\' THEN \'b) 1 a 7 dias\'
            WHEN \'c) 8 a 30 dias\' THEN \'c) 8 a 30 dias\'
            WHEN \'c) 8 a 30 días\' THEN \'c) 8 a 30 dias\'
            WHEN \'d) 31 a 60 dias\' THEN \'d) 31 a 60 dias\'
            WHEN \'d) 31 a 60 días\' THEN \'d) 31 a 60 dias\'
            WHEN \'e) 61+ dias\' THEN \'e) 61+ dias\'
            WHEN \'e) 61+ días\' THEN \'e) 61+ dias\'
            ELSE NULL END)';
    }

    /** 0..4 según bucket de nacimiento; NULL si no reconocido. */
    private static function sqlBucketNacimientoOrdExpr(string $col): string
    {
        return '(CASE TRIM(CAST(`' . $col . '` AS CHAR CHARACTER SET utf8mb4))
            WHEN \'a) Current\' THEN 0
            WHEN \'b) 1 a 7 dias\' THEN 1
            WHEN \'b) 1 a 7 días\' THEN 1
            WHEN \'c) 8 a 30 dias\' THEN 2
            WHEN \'c) 8 a 30 días\' THEN 2
            WHEN \'d) 31 a 60 dias\' THEN 3
            WHEN \'d) 31 a 60 días\' THEN 3
            WHEN \'e) 61+ dias\' THEN 4
            WHEN \'e) 61+ días\' THEN 4
            ELSE NULL END)';
    }

    /**
     * @param array<string,int> $nacDist
     * @return array{mostrar:bool,pC:int,p7:int}
     */
    private static function barGlobalCurrentVs17(array $nacDist): array
    {
        $totalCurrent = (int) ($nacDist['a) Current'] ?? 0);
        $total17 = (int) ($nacDist['b) 1 a 7 dias'] ?? 0);
        $totalG = $totalCurrent + $total17;
        if ($totalG <= 0) {
            return ['mostrar' => false, 'pC' => 0, 'p7' => 0];
        }
        $pC = (int) round($totalCurrent / $totalG * 100);
        $p7 = 100 - $pC;

        return ['mostrar' => true, 'pC' => $pC, 'p7' => $p7];
    }

    private static function h(string $s): string
    {
        return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
    }

    private static function esCurrentTerritorial(string $nombre): bool
    {
        $t = trim($nombre);

        return $t === '' || preg_match('/^current$/i', $t) === 1 || $t === '(Sin territorial)';
    }

    /**
     * @param list<array{Territorial:string,Zonal:string,Jefe_de_Plaza:string,Gestor_Asignado:string,total:int,cobrados:int}> $rows
     */
    private static function renderJerarquiaHtmlDesdeAgregados(array $rows): string
    {
        $territoriales = [];
        foreach ($rows as $r) {
            $ter = $r['Territorial'] ?? '(Sin territorial)';
            $zon = $r['Zonal'] ?? '(Sin zonal)';
            $jefe = $r['Jefe_de_Plaza'] ?? '(Sin jefe)';
            $gest = $r['Gestor_Asignado'] ?? '(Sin gestor)';
            $tot = (int) ($r['total'] ?? 0);
            $cob = (int) ($r['cobrados'] ?? 0);
            $pend = max(0, $tot - $cob);

            if (!isset($territoriales[$ter])) {
                $territoriales[$ter] = ['total' => 0, 'cobrados' => 0, 'pendientes' => 0, 'zonales' => []];
            }
            $T = &$territoriales[$ter];
            $T['total'] += $tot;
            $T['cobrados'] += $cob;
            $T['pendientes'] += $pend;

            $zonKey = ($zon === $jefe) ? $zon : ($zon . '|||' . $jefe);
            if (!isset($T['zonales'][$zonKey])) {
                $T['zonales'][$zonKey] = [
                    'zonNombre' => $zon,
                    'jefNombre' => $jefe,
                    'mismoNombre' => $zon === $jefe,
                    'total' => 0,
                    'cobrados' => 0,
                    'pendientes' => 0,
                    'gestores' => [],
                ];
            }
            $Z = &$T['zonales'][$zonKey];
            $Z['total'] += $tot;
            $Z['cobrados'] += $cob;
            $Z['pendientes'] += $pend;

            if (!isset($Z['gestores'][$gest])) {
                $Z['gestores'][$gest] = ['total' => 0, 'cobrados' => 0, 'pendientes' => 0];
            }
            $G = &$Z['gestores'][$gest];
            $G['total'] += $tot;
            $G['cobrados'] += $cob;
            $G['pendientes'] += $pend;
            unset($T, $Z, $G);
        }

        $barColor = static function (int $pct): string {
            if ($pct >= 70) {
                return '#28a745';
            }
            if ($pct >= 40) {
                return '#fd7e14';
            }

            return '#dc3545';
        };
        $pctClass = static function (int $pct): string {
            if ($pct >= 70) {
                return 'text-success';
            }
            if ($pct >= 40) {
                return 'text-warning';
            }

            return 'text-danger';
        };
        $borderClass = static function (int $pct): string {
            if ($pct >= 70) {
                return 'border-success';
            }
            if ($pct >= 40) {
                return 'border-warning';
            }

            return 'border-danger';
        };

        $terOrdenados = [];
        foreach ($territoriales as $k => $v) {
            $terOrdenados[] = array_merge(['nombre' => $k], $v);
        }
        usort($terOrdenados, static function (array $a, array $b): int {
            $esA = self::esCurrentTerritorial((string) ($a['nombre'] ?? ''));
            $esB = self::esCurrentTerritorial((string) ($b['nombre'] ?? ''));
            if ($esA && !$esB) {
                return -1;
            }
            if (!$esA && $esB) {
                return 1;
            }
            $ra = ($a['total'] ?? 0) > 0 ? ($a['cobrados'] ?? 0) / $a['total'] : 0;
            $rb = ($b['total'] ?? 0) > 0 ? ($b['cobrados'] ?? 0) / $b['total'] : 0;

            return $ra <=> $rb;
        });

        $html = '';
        foreach ($terOrdenados as $idx => $ter) {
            $nombreTer = (string) ($ter['nombre'] ?? '');
            if (self::esCurrentTerritorial($nombreTer)) {
                $html .= '
                        <div class="card mb-3 border-start border-3 border-secondary">
                            <div class="card-body py-3">
                                <p class="mb-0 text-muted" style="font-size:.82rem;">
                                    <i class="fa fa-circle-info text-secondary me-2"></i>
                                    El seguimiento de los créditos se podrá visualizar una vez que se asigne la cartera. Consulte disponibilidad con el administrador de asignación de cartera.
                                </p>
                            </div>
                        </div>';

                continue;
            }

            $pctTer = ($ter['total'] ?? 0) ? (int) round(($ter['cobrados'] ?? 0) / $ter['total'] * 100) : 0;
            $bcTer = $borderClass($pctTer);
            $colTer = $barColor($pctTer);
            $clTer = $pctClass($pctTer);

            $zonales = array_values($ter['zonales'] ?? []);
            usort($zonales, static function (array $a, array $b): int {
                $ra = ($a['total'] ?? 0) > 0 ? ($a['cobrados'] ?? 0) / $a['total'] : 0;
                $rb = ($b['total'] ?? 0) > 0 ? ($b['cobrados'] ?? 0) / $b['total'] : 0;

                return $ra <=> $rb;
            });

            $htmlZon = '';
            foreach ($zonales as $zon) {
                $pZ = ($zon['total'] ?? 0) ? (int) round(($zon['cobrados'] ?? 0) / $zon['total'] * 100) : 0;
                $colZ = $barColor($pZ);
                $clZ = $pctClass($pZ);

                $mismoNombre = !empty($zon['mismoNombre']);
                $nivelBadge = $mismoNombre
                    ? '<span class="badge bg-label-info me-2" style="font-size:.62rem;">Zonal · Jefe de plaza</span>'
                    : '<span class="badge bg-label-info me-1" style="font-size:.62rem;">Zonal</span>
                               <span class="text-muted me-1" style="font-size:.68rem;">' . self::h((string) ($zon['zonNombre'] ?? '')) . '</span>
                               <span class="badge bg-label-primary me-2" style="font-size:.62rem;">Jefe de plaza</span>';
                $nombreMostrar = $mismoNombre
                    ? self::h((string) ($zon['zonNombre'] ?? ''))
                    : self::h((string) ($zon['jefNombre'] ?? ''));

                $gestList = [];
                foreach (($zon['gestores'] ?? []) as $gk => $gv) {
                    $gestList[] = array_merge(['nombre' => $gk], $gv);
                }
                usort($gestList, static function (array $a, array $b): int {
                    $ra = ($a['total'] ?? 0) > 0 ? ($a['cobrados'] ?? 0) / $a['total'] : 0;
                    $rb = ($b['total'] ?? 0) > 0 ? ($b['cobrados'] ?? 0) / $b['total'] : 0;

                    return $ra <=> $rb;
                });

                $htmlGest = '';
                foreach ($gestList as $gest) {
                    $pG = ($gest['total'] ?? 0) ? (int) round(($gest['cobrados'] ?? 0) / $gest['total'] * 100) : 0;
                    $colG = $barColor($pG);
                    $clG = $pctClass($pG);
                    $gc = (int) ($gest['cobrados'] ?? 0);
                    $gp = (int) ($gest['pendientes'] ?? 0);
                    $htmlGest .= '
                            <tr>
                                <td style="padding-left:2.2rem;font-size:.72rem;">
                                    <i class="fa fa-user text-muted me-1"></i>' . self::h((string) ($gest['nombre'] ?? '')) . '
                                </td>
                                <td class="text-center" style="font-size:.72rem;">' . (int) ($gest['total'] ?? 0) . '</td>
                                <td class="text-center ' . ($gc > 0 ? 'text-success' : 'text-muted') . '" style="font-size:.72rem;">' . $gc . '</td>
                                <td class="text-center ' . ($gp > 0 ? 'text-warning' : 'text-muted') . '" style="font-size:.72rem;">' . $gp . '</td>
                                <td class="text-center" style="font-size:.72rem;">
                                    <div class="progress d-inline-flex" style="height:4px;width:50px;vertical-align:middle;">
                                        <div class="progress-bar" style="width:' . $pG . '%;background:' . $colG . ';"></div>
                                    </div>
                                    <span class="ms-1 ' . $clG . '">' . $pG . '%</span>
                                </td>
                            </tr>';
                }

                $htmlZon .= '
                        <tr class="table-light">
                            <td style="padding-left:.8rem;font-size:.75rem;">
                                ' . $nivelBadge . '
                                <span class="fw-semibold">' . $nombreMostrar . '</span>
                            </td>
                            <td class="text-center fw-semibold" style="font-size:.75rem;">' . (int) ($zon['total'] ?? 0) . '</td>
                            <td class="text-center text-success fw-semibold" style="font-size:.75rem;">' . (int) ($zon['cobrados'] ?? 0) . '</td>
                            <td class="text-center text-warning fw-semibold" style="font-size:.75rem;">' . (int) ($zon['pendientes'] ?? 0) . '</td>
                            <td class="text-center" style="font-size:.75rem;">
                                <div class="progress d-inline-flex" style="height:5px;width:55px;vertical-align:middle;">
                                    <div class="progress-bar" style="width:' . $pZ . '%;background:' . $colZ . ';"></div>
                                </div>
                                <span class="ms-1 ' . $clZ . '">' . $pZ . '%</span>
                            </td>
                        </tr>' . $htmlGest;
            }

            $html .= '
                    <div class="card mb-3 border-start border-3 ' . $bcTer . '">
                        <div class="card-header d-flex align-items-center justify-content-between py-2"
                             style="cursor:pointer;"
                             data-bs-toggle="collapse"
                             data-bs-target="#pph_ter_' . (int) $idx . '">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-label-secondary" style="font-size:.63rem;">Territorial</span>
                                <strong style="font-size:.85rem;">' . self::h($nombreTer) . '</strong>
                                <span class="badge bg-label-secondary">' . (int) ($ter['total'] ?? 0) . ' créditos</span>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex flex-column align-items-end" style="font-size:.75rem;">
                                    <span class="text-success"><i class="fa fa-circle-check me-1"></i>' . (int) ($ter['cobrados'] ?? 0) . ' cobrados</span>
                                    <span class="text-warning"><i class="fa fa-clock me-1"></i>' . (int) ($ter['pendientes'] ?? 0) . ' pendientes</span>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-1">
                                    <span class="' . $clTer . ' fw-bold" style="font-size:.85rem;">' . $pctTer . '%</span>
                                    <div class="progress" style="height:4px;width:60px;">
                                        <div class="progress-bar" style="width:' . $pctTer . '%;background:' . $colTer . ';"></div>
                                    </div>
                                </div>
                                <i class="fa fa-chevron-down text-muted"></i>
                            </div>
                        </div>
                        <div class="collapse" id="pph_ter_' . (int) $idx . '">
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0 align-middle" style="font-size:.74rem;">
                                    <thead class="table-dark" style="font-size:.67rem;">
                                        <tr>
                                            <th>Nivel y nombre</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Cobrados</th>
                                            <th class="text-center">Pendientes</th>
                                            <th class="text-center">Efectividad</th>
                                        </tr>
                                    </thead>
                                    <tbody>' . $htmlZon . '</tbody>
                                </table>
                            </div>
                        </div>
                    </div>';
        }

        if ($html === '') {
            return '<p class="text-muted">Sin datos.</p>';
        }

        return $html;
    }
}
