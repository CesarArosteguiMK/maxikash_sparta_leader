<?php

namespace Models;

use Core\DatabaseSegundometro;

/**
 * Histórico semanal de primeros pagos a partir de `tbl_histo_primeros_pagos` (__SPARTA_SECRET_REDACTED__).
 * Criterio de semana: columna **SEMANA** (misma etiqueta que Segundómetro / comparativa semanal).
 *
 * El resumen por semana replica métricas tipo primeros pagos: prioridad a cartera **martes a domingo**.
 * Si no hay filas en ese rango, se usa **lunes de cierre** (fecha exacta). Nunca se abre a toda la semana por `SEMANA` sola.
 * Nacimiento / corte alineados al menú **Primeros pagos — Lunes de cierre** (`Empresa::getVencimientosLunes` sobre
 * `tbl_segundometro_semana`), pero leyendo **`tbl_histo_primeros_pagos`**: nacimiento = **`Bucket_Morosidad_Real`**
 * (si no existe, `Bucket_Morosidad`); mora al corte = **la misma columna que `Empresa::getCorteActual()`** si está en
 * la tabla histórico; si no, `COALESCE` de slots **`Dias_mora_Lunes_*`** (orden Empresa) y al final **`Dias_mora`**.
 *
 * Tabla física: por defecto `tbl_histo_primeros_pagos`. Si el ETL guarda ~69k filas/sem pero el reporte solo usa
 * la cartera de primeros pagos (~1k), conviene una tabla derivada con solo esas filas y definir
 * `SPARTA_PP_HISTO_TABLA` (nombre alfanumérico + guión bajo, máx. 64) apuntando a esa tabla.
 */
final class PrimerosPagosHistoricoSegundometro
{
    private const TABLA_HISTORICO_PRIMEROS_PAGOS_DEFAULT = 'tbl_histo_primeros_pagos';

    /** Origen de copia (solo `SELECT`; nunca se modifica). */
    private const TABLA_SEGUNDOMETRO_HISTO = 'tbl_segundometro_histo';

    /** Máximo de etiquetas `SEMANA` por una sola corrida (evita IN enorme). */
    private const COPIA_DESDE_HISTO_SEMANAS_MAX = 24;

    /** Identificador seguro para usar en SQL (evita inyección vía nombre de tabla). */
    private static function nombreTablaHistoricoPrimerosPagos(): string
    {
        static $memo;
        if ($memo !== null) {
            return $memo;
        }
        $v = getenv('SPARTA_PP_HISTO_TABLA');
        if (is_string($v)) {
            $v = trim($v);
            if ($v !== '' && strlen($v) <= 64 && preg_match('/^[A-Za-z0-9_]+$/', $v) === 1) {
                return $memo = $v;
            }
        }

        return $memo = self::TABLA_HISTORICO_PRIMEROS_PAGOS_DEFAULT;
    }

    private const SEMANA_MAX_LEN = 160;

    /** Solo se ofrecen las últimas N semanas (por fecha de carga en histórico). */
    private const LISTA_SEMANAS_MAX = 5;

    /**
     * Ventana de filas a considerar para armar el ranking de semanas (evita GROUP BY sobre toda la tabla).
     * Con índice por fecha_hora_insert la consulta escala; sin índice sigue acotando filas vs. histórico completo.
     */
    private const LISTA_SEMANAS_LOOKBACK_DIAS = 60;

    /** Misma escala que el reporte en vivo (Reporteria / Empresa). */
    private const BUCKET_ORDER = [
        'a) Current',
        'b) 1 a 7 dias',
        'c) 8 a 30 dias',
        'd) 31 a 60 dias',
        'e) 61+ dias',
    ];

    /** @var array<string, true>|null Cache por petición HTTP (evita decenas de lecturas a information_schema). */
    private static ?array $cacheMapColumnasHisto = null;

    /** @var array<string, mixed>|null Cache por petición de meta mora corte (columna menú + COALESCE). */
    private static ?array $cacheMetaSqlMoraCorteHisto = null;

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
            $candidatasCerradas = [];
            $tz = new \DateTimeZone('America/Mexico_City');
            $semanaCerrada = (new \DateTimeImmutable('now', $tz))->modify('-1 week');
            for ($i = 0; $i < $limite; $i++) {
                $semanaEtiqueta = 'Semana ' . (int)$semanaCerrada->format('W') . '-' . (int)$semanaCerrada->format('o');
                $rango = self::resolverRangoMartesDomingoDesdeEtiquetaSemana($semanaEtiqueta);
                if ($rango !== null) {
                    $candidatasCerradas[] = [
                        'semana' => $semanaEtiqueta,
                        'registros' => 0,
                        'ultimo_insert' => null,
                        'ini' => $rango['martes'],
                        'fin' => $rango['domingo'],
                        'lunes_iso' => $rango['lunes_iso'],
                    ];
                }
                $semanaCerrada = $semanaCerrada->modify('-1 week');
            }
            $registrosPorSemanaCerrada = self::contarRegistrosPorSemana($db, $candidatasCerradas);
            $outCerradas = [];
            foreach ($candidatasCerradas as $c) {
                $outCerradas[] = [
                    'semana' => $c['semana'],
                    'registros' => (int) ($registrosPorSemanaCerrada[$c['semana']] ?? 0),
                    'ultimo_insert' => $c['ultimo_insert'],
                    'ini' => $c['ini'],
                    'fin' => $c['fin'],
                ];
                if (count($outCerradas) >= $limite) {
                    break;
                }
            }

            return ['success' => true, 'datos' => $outCerradas];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo leer el histórico de primeros pagos.', 'error' => $e->getMessage()];
        }
    }

    /** Misma fuente que `Empresa::getVencimientosLunes` (bucket_nacio). */
    private static function columnaNacimientoComoLunesCierre(DatabaseSegundometro $db): string
    {
        $sql = 'SHOW COLUMNS FROM `' . self::nombreTablaHistoricoPrimerosPagos() . "` LIKE 'Bucket_Morosidad_Real'";
        $row = $db->queryOne($sql);
        if (is_array($row) && !empty($row)) {
            return 'Bucket_Morosidad_Real';
        }

        return 'Bucket_Morosidad';
    }

    /**
     * Expresión SQL de días de mora “base” para nacimiento (columna `Dias_mora` si existe).
     */
    private static function sqlExprNacimientoDiasMora(DatabaseSegundometro $db): ?string
    {
        $m = self::mapColumnasTablaHisto($db);

        return isset($m['Dias_mora']) ? 'CAST(`Dias_mora` AS SIGNED)' : null;
    }

    /**
     * @return array{bn: string, ordN: string}
     */
    private static function sqlNacimientoBucketYOrd(DatabaseSegundometro $db): array
    {
        $m = self::mapColumnasTablaHisto($db);
        if (isset($m['Bucket_Morosidad_Real'])) {
            $col = 'Bucket_Morosidad_Real';
        } elseif (isset($m['Bucket_Morosidad'])) {
            $col = 'Bucket_Morosidad';
        } else {
            $col = null;
        }

        // Igual que Lunes de Cierre: nacimiento sale de bucket, no de mora al corte.
        if ($col !== null) {
            return [
                'bn' => self::sqlBucketNacimientoCanonExpr($col),
                'ordN' => self::sqlBucketNacimientoOrdExpr($col),
            ];
        }

        // Respaldo solo si no existe bucket en histórico.
        $moraNac = self::sqlExprNacimientoDiasMora($db);
        if ($moraNac !== null) {
            return [
                'bn' => self::sqlBucketCorteDesdeMoraSqlExpr($moraNac),
                'ordN' => self::sqlBucketCorteOrdDesdeMoraSqlExpr($moraNac),
            ];
        }
        $colFallback = self::columnaNacimientoComoLunesCierre($db);

        return [
            'bn' => self::sqlBucketNacimientoCanonExpr($colFallback),
            'ordN' => self::sqlBucketNacimientoOrdExpr($colFallback),
        ];
    }

    /**
     * Expresión SQL de bucket/orden de nacimiento por semana (histórico), alineada al menú Lunes de cierre.
     *
     * @return array{bn:string,ordN:string,col_nacimiento:string}
     */
    private static function sqlNacimientoBucketYOrdParaSemana(
        DatabaseSegundometro $db,
        string $sem,
        string $martesIso,
        string $domingoIso,
        string $modoFecha,
        ?string $lunesIsoExacto
    ): array {
        $m = self::mapColumnasTablaHisto($db);
        $hasReal = isset($m['Bucket_Morosidad_Real']);
        $hasBase = isset($m['Bucket_Morosidad']);

        // Igual que menú Lunes de cierre: si existe `Bucket_Morosidad_Real`, se usa siempre (sin 2 escaneos extra por semana).
        if ($hasReal && $hasBase) {
            $col = 'Bucket_Morosidad_Real';

            return [
                'bn' => self::sqlBucketNacimientoCanonExpr($col),
                'ordN' => self::sqlBucketNacimientoOrdExpr($col),
                'col_nacimiento' => $col,
            ];
        }

        if ($hasReal || $hasBase) {
            $col = $hasReal ? 'Bucket_Morosidad_Real' : 'Bucket_Morosidad';

            return [
                'bn' => self::sqlBucketNacimientoCanonExpr($col),
                'ordN' => self::sqlBucketNacimientoOrdExpr($col),
                'col_nacimiento' => $col,
            ];
        }

        $fallback = self::sqlNacimientoBucketYOrd($db);
        $fallback['col_nacimiento'] = 'Dias_mora';

        return $fallback;
    }

    /**
     * Slots Lunes de mora (de más tardío a más temprano), mismo orden que `Empresa::sqlExprMoraSoloLunes()`.
     *
     * @return list<string>
     */
    private static function ordenColumnasMoraSoloLunesEmpresa(): array
    {
        return [
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
    }

    /**
     * Misma regla de columna de corte que el menú Lunes de cierre (`Empresa::getCorteActual()` + `tbl_segundometro_semana`).
     *
     * @return array{mora_sql: string, columna_menu: ?string, usa_columna_menu: bool, columnas_fallback_coalesce: list<string>}
     */
    private static function metaSqlMoraCorteHisto(DatabaseSegundometro $db): array
    {
        if (self::$cacheMetaSqlMoraCorteHisto !== null) {
            return self::$cacheMetaSqlMoraCorteHisto;
        }
        $map = self::mapColumnasTablaHisto($db);
        $menuCol = Empresa::getCorteActual();
        if ($menuCol !== null && $menuCol !== '' && !empty($map[$menuCol])) {
            self::$cacheMetaSqlMoraCorteHisto = [
                'mora_sql' => 'CAST(`' . $menuCol . '` AS SIGNED)',
                'columna_menu' => $menuCol,
                'usa_columna_menu' => true,
                'columnas_fallback_coalesce' => [],
            ];

            return self::$cacheMetaSqlMoraCorteHisto;
        }
        $parts = [];
        $fallback = [];
        foreach (self::ordenColumnasMoraSoloLunesEmpresa() as $c) {
            if (!empty($map[$c])) {
                $parts[] = 'CAST(`' . $c . '` AS SIGNED)';
                $fallback[] = $c;
            }
        }
        if (!empty($map['Dias_mora'])) {
            $parts[] = 'CAST(`Dias_mora` AS SIGNED)';
            $fallback[] = 'Dias_mora';
        }
        $moraSql = $parts === [] ? 'NULL' : 'COALESCE(' . implode(', ', $parts) . ')';

        self::$cacheMetaSqlMoraCorteHisto = [
            'mora_sql' => $moraSql,
            'columna_menu' => $menuCol,
            'usa_columna_menu' => false,
            'columnas_fallback_coalesce' => $fallback,
        ];

        return self::$cacheMetaSqlMoraCorteHisto;
    }

    /** Expresión SQL de mora al corte (alineada a `Empresa::getVencimientosLunes` / `getCorteActual`). */
    private static function sqlExprMoraCorteHisto(DatabaseSegundometro $db): string
    {
        return self::metaSqlMoraCorteHisto($db)['mora_sql'];
    }

    /**
     * Agregados sobre la misma cartera que el resumen (para ver si la BD trae mora/bucket distinto de lo esperado).
     *
     * @return array<string, mixed>
     */
    private static function queryDiagnosticoResumenSemana(
        DatabaseSegundometro $db,
        string $sem,
        string $martesIso,
        string $domingoIso,
        string $modoFecha,
        ?string $lunesIsoExacto
    ): array {
        $map = self::mapColumnasTablaHisto($db);
        $metaMora = self::metaSqlMoraCorteHisto($db);
        $moraSql = $metaMora['mora_sql'];
        [$whereFecha, $paramsExtra] = self::whereFechaSqlYParams($modoFecha, $martesIso, $domingoIso, $lunesIsoExacto);
        $tabla = self::nombreTablaHistoricoPrimerosPagos();
        $baseWhere = self::sqlWhereSemanaParam() . $whereFecha;
        $params = ['sem' => $sem] + $paramsExtra;
        $dmField = isset($map['Dias_mora']) ? 'CAST(`Dias_mora` AS SIGNED)' : 'NULL';
        $sqlAgg = 'SELECT MIN(mm) AS mora_corte_min, MAX(mm) AS mora_corte_max,
                SUM(CASE WHEN mm IS NULL THEN 1 ELSE 0 END) AS filas_mora_null,
                SUM(CASE WHEN mm IS NOT NULL AND mm < 1 THEN 1 ELSE 0 END) AS filas_mora_lt_1,
                MIN(dm) AS dias_mora_min, MAX(dm) AS dias_mora_max,
                SUM(CASE WHEN dm IS NULL THEN 1 ELSE 0 END) AS filas_dias_mora_null
            FROM (
                SELECT (' . $moraSql . ') AS mm, (' . $dmField . ') AS dm
                FROM `' . $tabla . '`
                WHERE ' . $baseWhere . '
            ) x';
        $rowAgg = $db->queryOne($sqlAgg, $params);
        $colBucket = isset($map['Bucket_Morosidad_Real']) ? 'Bucket_Morosidad_Real' : (isset($map['Bucket_Morosidad']) ? 'Bucket_Morosidad' : null);
        $topBuckets = [];
        if ($colBucket !== null) {
            $sqlB = 'SELECT TRIM(CAST(`' . $colBucket . '` AS CHAR CHARACTER SET utf8mb4)) AS b, COUNT(*) AS n
                FROM `' . $tabla . '`
                WHERE ' . $baseWhere . '
                GROUP BY TRIM(CAST(`' . $colBucket . '` AS CHAR CHARACTER SET utf8mb4))
                ORDER BY n DESC
                LIMIT 12';
            $topBuckets = $db->queryAll($sqlB, $params);
        }
        $topLista = [];
        foreach ((array) $topBuckets as $r) {
            $topLista[] = [
                'valor' => (string) ($r['b'] ?? ''),
                'n' => (int) ($r['n'] ?? 0),
            ];
        }

        return [
            'tabla' => $tabla,
            'corte_columna_menu_lunes_cierre' => $metaMora['columna_menu'],
            'corte_usa_misma_columna_que_menu' => $metaMora['usa_columna_menu'],
            'corte_fallback_coalesce_columnas' => $metaMora['columnas_fallback_coalesce'],
            'columna_bucket_etiquetas' => $colBucket,
            'mora_corte_min' => $rowAgg['mora_corte_min'] ?? null,
            'mora_corte_max' => $rowAgg['mora_corte_max'] ?? null,
            'filas_expr_mora_null' => (int) ($rowAgg['filas_mora_null'] ?? 0),
            'filas_expr_mora_menor_1' => (int) ($rowAgg['filas_mora_lt_1'] ?? 0),
            'dias_mora_min' => $rowAgg['dias_mora_min'] ?? null,
            'dias_mora_max' => $rowAgg['dias_mora_max'] ?? null,
            'filas_dias_mora_null' => (int) ($rowAgg['filas_dias_mora_null'] ?? 0),
            'top_valores_bucket_crudo' => $topLista,
            'nota' => 'Corte = columna `Empresa::getCorteActual()` si existe en histórico; si no, COALESCE Lunes_* + Dias_mora (como respaldo). Nacimiento = Bucket_Morosidad_Real como tbl_segundometro_semana.',
        ];
    }

    /** Predicado WHERE: etiqueta de semana alineada a `trim()` del listado. */
    private static function sqlWhereSemanaParam(): string
    {
        return 'TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4)) = :sem';
    }

    /**
     * COUNT(*) vía PDO: el alias puede llegar con distinto casing o drivers raros devuelven solo valor escalar.
     */
    private static function intDesdeFilaSqlCount(?array $fila): int
    {
        if ($fila === null) {
            return 0;
        }
        foreach (['c', 'C', 'cnt', 'total', 'n', 'total_filas'] as $k) {
            if (array_key_exists($k, $fila) && is_numeric($fila[$k])) {
                return (int) $fila[$k];
            }
        }
        foreach ($fila as $v) {
            if (is_numeric($v)) {
                return (int) $v;
            }
        }

        return 0;
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

    /** Etiqueta "Semana NN-AAAA" de la semana ISO en curso (hoy, zona MX), o `null` si no se puede calcular. */
    private static function etiquetaSemanaActualIso(): ?string
    {
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
            $hoy = new \DateTimeImmutable('now', $tz);
            $semana = (int) $hoy->format('W');
            $anio = (int) $hoy->format('o');
            if ($semana < 1 || $semana > 53 || $anio < 2000) {
                return null;
            }

            return 'Semana ' . str_pad((string) $semana, 2, '0', STR_PAD_LEFT) . '-' . (string) $anio;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Compara dos etiquetas por (semana ISO, año ISO) ignorando espacios/mayúsculas/separadores. */
    private static function mismaEtiquetaSemanaIso(string $a, string $b): bool
    {
        $na = self::parseSemanaIso($a);
        $nb = self::parseSemanaIso($b);
        if ($na === null || $nb === null) {
            return false;
        }

        return $na[0] === $nb[0] && $na[1] === $nb[1];
    }

    /**
     * @return array{0:int,1:int}|null [semana, anio]
     */
    private static function parseSemanaIso(string $etiqueta): ?array
    {
        if (preg_match('/(?:semana\s*)?(\d{1,2})\s*[-_\/ ]\s*(\d{4})/iu', $etiqueta, $m) !== 1) {
            return null;
        }
        $sem = (int) $m[1];
        $anio = (int) $m[2];
        if ($sem < 1 || $sem > 53 || $anio < 2000 || $anio > 2100) {
            return null;
        }

        return [$sem, $anio];
    }

    /**
     * Comparativo "nacimiento vs corte actual" de las últimas N semanas cerradas (excluye semana ISO en curso).
     *
     * @return array{success:bool, mensaje?:string, datos?:array<string,mixed>, error?:string}
     */
    public static function resumenUltimasNSemanas(int $n = 5): array
    {
        $n = max(1, min($n, self::LISTA_SEMANAS_MAX));
        try {
            $listado = self::listarSemanas($n);
            if (!($listado['success'] ?? false)) {
                return [
                    'success' => false,
                    'mensaje' => (string) ($listado['mensaje'] ?? 'No se pudo listar semanas del histórico.'),
                    'error' => isset($listado['error']) ? (string) $listado['error'] : null,
                ];
            }
            $semanas = $listado['datos'] ?? [];
            if ($semanas === []) {
                return [
                    'success' => false,
                    'mensaje' => 'Aún no hay semanas cerradas en el histórico.',
                ];
            }
            $db = new DatabaseSegundometro();
            $resumen = [];
            foreach ($semanas as $s) {
                $sem = (string) ($s['semana'] ?? '');
                if ($sem === '') {
                    continue;
                }
                $r = self::resumenPorSemanaDesdeDb($db, $sem, [
                    'diagnostico' => false,
                    'jerarquia' => false,
                    'jerarquia_html' => false,
                ]);
                if (!($r['success'] ?? false)) {
                    $resumen[] = [
                        'semana' => $sem,
                        'ini' => (string) ($s['ini'] ?? ''),
                        'fin' => (string) ($s['fin'] ?? ''),
                        'disponible' => false,
                        'mensaje' => (string) ($r['mensaje'] ?? 'Sin datos'),
                    ];
                    continue;
                }
                $d = (array) ($r['datos'] ?? []);
                $nd = (array) (($d['nacimiento']['nac_dist'] ?? []));
                $corte = (array) ($d['corte'] ?? []);
                $total = (int) ($d['total'] ?? 0);
                $nacCur = (int) ($nd['a) Current'] ?? 0);
                $nac17 = (int) ($nd['b) 1 a 7 dias'] ?? 0);
                $nacOtros = max(0, $total - $nacCur - $nac17);
                $curAlCorte = (int) ($corte['current_al_corte'] ?? 0);
                $pendPp = (int) ($corte['pendientes_primeros_pagos'] ?? 0);
                $resumen[] = [
                    'semana' => $sem,
                    'ini' => (string) ($d['periodo_martes'] ?? ($s['ini'] ?? '')),
                    'fin' => (string) ($d['periodo_domingo'] ?? ($s['fin'] ?? '')),
                    'lunes_primer_vencimiento' => (string) ($d['lunes_primer_vencimiento'] ?? ''),
                    'criterio_fecha' => (string) ($d['criterio_fecha'] ?? ''),
                    'total' => $total,
                    'nacimiento' => [
                        'current' => $nacCur,
                        'd1_7' => $nac17,
                        'otros' => $nacOtros,
                        'pct_current' => $total > 0 ? (int) round($nacCur * 100 / $total) : 0,
                        'pct_1_7' => $total > 0 ? (int) round($nac17 * 100 / $total) : 0,
                        'nac_dist' => $nd,
                    ],
                    'corte' => [
                        'current_al_corte' => $curAlCorte,
                        'pendientes_primeros_pagos' => $pendPp,
                        'pct_current_al_corte' => $total > 0 ? (int) round($curAlCorte * 100 / $total) : 0,
                        'pct_pendientes' => $total > 0 ? (int) round($pendPp * 100 / $total) : 0,
                    ],
                    'recuperacion' => [
                        'pct_sobre_1_7' => $nac17 > 0 ? (int) round(max(0, $curAlCorte - $nacCur) * 100 / $nac17) : 0,
                    ],
                    'jerarquia_agregada' => (array) ($d['jerarquia_agregada'] ?? []),
                    'disponible' => true,
                ];
            }
            if ($resumen === []) {
                return ['success' => false, 'mensaje' => 'Sin datos para las últimas semanas cerradas.'];
            }

            return ['success' => true, 'datos' => ['semanas' => $resumen]];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'Error al armar el comparativo.', 'error' => $e->getMessage()];
        }
    }

    /**
     * Meta + expresión de nacimiento para una semana con datos (reutilizable para resumen o solo jerarquía).
     *
     * @return array{type:string, mensaje?:string, sem?:string, m1?:string, m2?:string, lunesIso?:string, criterio?:string, lunesPv?:string, modoFecha?:string, lunesParam?:?string, total?:int, nacSql?:array}
     */
    private static function resolverContextoResumenSemana(DatabaseSegundometro $db, string $sem): array
    {
        $rangoPv = self::resolverRangoMartesDomingoDesdeEtiquetaSemana($sem);
        if ($rangoPv === null) {
            return ['type' => 'err', 'mensaje' => 'La etiqueta de semana no tiene formato válido (Semana NN-AAAA).'];
        }
        $m1 = $rangoPv['martes'];
        $m2 = $rangoPv['domingo'];
        $lunesIso = $rangoPv['lunes_iso'];

        $meta = self::queryMetaSemana($db, $sem, $m1, $m2, 'lunes', $lunesIso);
        $criterio = 'lunes_cierre';
        if ((int) ($meta['total'] ?? 0) < 1) {
            $meta = self::queryMetaSemana($db, $sem, $m1, $m2, 'rango', null);
            $criterio = 'martes_domingo';
        }
        $total = (int) ($meta['total'] ?? 0);
        if ($total < 1) {
            return ['type' => 'err', 'mensaje' => 'No hay datos para la semana indicada.'];
        }
        $lunesPv = $criterio === 'lunes_cierre' ? $lunesIso : $m1;
        $modoFecha = $criterio === 'lunes_cierre' ? 'lunes' : 'rango';
        $lunesParam = $criterio === 'lunes_cierre' ? $lunesIso : null;
        $nacSql = self::sqlNacimientoBucketYOrdParaSemana($db, $sem, $m1, $m2, $modoFecha, $lunesParam);

        return [
            'type' => 'ok',
            'sem' => $sem,
            'm1' => $m1,
            'm2' => $m2,
            'lunesIso' => $lunesIso,
            'criterio' => $criterio,
            'lunesPv' => $lunesPv,
            'modoFecha' => $modoFecha,
            'lunesParam' => $lunesParam,
            'total' => $total,
            'nacSql' => $nacSql,
        ];
    }

    /**
     * @param array{diagnostico?:bool,jerarquia?:bool,jerarquia_html?:bool} $opts
     *
     * @return array{success:bool, mensaje?:string, datos?:array<string,mixed>, error?:string}
     */
    private static function resumenPorSemanaDesdeDb(DatabaseSegundometro $db, string $semana, array $opts = []): array
    {
        $opts = array_merge([
            'diagnostico' => true,
            'jerarquia' => true,
            'jerarquia_html' => true,
        ], $opts);
        $sem = self::normalizarSemanaParam($semana);
        if ($sem === null) {
            return ['success' => false, 'mensaje' => 'Parámetro de semana no válido.'];
        }
        try {
            $ctx = self::resolverContextoResumenSemana($db, $sem);
            if (($ctx['type'] ?? '') !== 'ok') {
                return ['success' => false, 'mensaje' => (string) ($ctx['mensaje'] ?? 'Sin datos')];
            }
            $m1 = (string) $ctx['m1'];
            $m2 = (string) $ctx['m2'];
            $lunesIso = (string) $ctx['lunesIso'];
            $criterio = (string) $ctx['criterio'];
            $lunesPv = (string) $ctx['lunesPv'];
            $modoFecha = (string) $ctx['modoFecha'];
            $lunesParam = $ctx['lunesParam'];
            $total = (int) $ctx['total'];
            $nacSql = (array) $ctx['nacSql'];

            $pares = self::queryDistribNacimientoCorte($db, $sem, $m1, $m2, $nacSql['bn'], $modoFecha, $lunesParam);
            $jerRows = ($opts['jerarquia'] ?? true)
                ? self::queryJerarquiaAgregada($db, $sem, $m1, $m2, $nacSql['ordN'], $modoFecha, $lunesParam)
                : [];

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

            $totalCurrentNac = (int) ($nacDist['a) Current'] ?? 0);
            $mat17 = $matriz['b) 1 a 7 dias'] ?? [];
            $recuperados1a7 = (int) ($mat17['a) Current'] ?? 0);
            $total1a7Nac = (int) ($nacDist['b) 1 a 7 dias'] ?? 0);
            $currentMasRecuperados = $totalCurrentNac + $recuperados1a7;
            $pendientesPp = max(0, $total1a7Nac - $recuperados1a7);

            $bar = self::barGlobalCurrentVs17($nacDist);
            $diag = ($opts['diagnostico'] ?? true)
                ? self::queryDiagnosticoResumenSemana($db, $sem, $m1, $m2, $modoFecha, $lunesParam)
                : [];

            $datos = [
                'semana' => $sem,
                'total' => $total,
                'periodo_martes' => $m1,
                'periodo_domingo' => $m2,
                'rango_cartera_texto' => $criterio === 'lunes_cierre' ? ($lunesIso . ' a ' . $lunesIso) : ($m1 . ' a ' . $m2),
                'criterio_fecha' => $criterio,
                'lunes_primer_vencimiento' => $lunesPv,
                'corte_label' => 'Misma lógica que menú Lunes de cierre: mora corte = columna `Empresa::getCorteActual()` (si existe en histórico; si no, COALESCE `Dias_mora_Lunes_*` + `Dias_mora`). Nacimiento = `Bucket_Morosidad_Real` (o `Bucket_Morosidad`).',
                'columna_nacimiento_usada' => $nacSql['col_nacimiento'],
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
                'jerarquia_agregada' => $jerRows,
            ];
            if ($opts['diagnostico'] ?? true) {
                $datos['diagnostico'] = $diag;
            }
            if (($opts['jerarquia_html'] ?? true) && ($opts['jerarquia'] ?? true) && $jerRows !== []) {
                $datos['jerarquia_html'] = self::renderJerarquiaHtmlDesdeAgregados($jerRows);
            }

            return ['success' => true, 'datos' => $datos];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'Error al armar el resumen de la semana.', 'error' => $e->getMessage()];
        }
    }

    /**
     * @return array{success:bool, mensaje?:string, datos?:array<string,mixed>, error?:string}
     */
    public static function resumenPorSemana(string $semana): array
    {
        try {
            $db = new DatabaseSegundometro();

            return self::resumenPorSemanaDesdeDb($db, $semana, [
                'diagnostico' => true,
                'jerarquia' => true,
                'jerarquia_html' => true,
            ]);
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'Error al armar el resumen de la semana.', 'error' => $e->getMessage()];
        }
    }

    /**
     * Jerarquías agregadas para varias etiquetas de semana (una conexión; consulta pesada solo aquí).
     * Máximo 8 etiquetas por petición.
     *
     * @param list<string> $semanasEtiqueta
     *
     * @return array{success:bool, mensaje?:string, datos?:array<string, list<array<string, mixed>>>, error?:string}
     */
    public static function jerarquiasAgregadasPorSemanas(array $semanasEtiqueta): array
    {
        $max = 8;
        $lista = [];
        foreach ($semanasEtiqueta as $raw) {
            $s = self::normalizarSemanaParam(trim((string) $raw));
            if ($s !== null && $s !== '' && !isset($lista[$s])) {
                $lista[$s] = true;
            }
        }
        $keys = array_keys($lista);
        if ($keys === []) {
            return ['success' => false, 'mensaje' => 'Indique al menos una semana válida.'];
        }
        if (count($keys) > $max) {
            $keys = array_slice($keys, 0, $max);
        }
        try {
            $db = new DatabaseSegundometro();
            $out = [];
            foreach ($keys as $sem) {
                $ctx = self::resolverContextoResumenSemana($db, $sem);
                if (($ctx['type'] ?? '') !== 'ok') {
                    $out[$sem] = [];

                    continue;
                }
                $out[$sem] = self::queryJerarquiaAgregada(
                    $db,
                    $sem,
                    (string) $ctx['m1'],
                    (string) $ctx['m2'],
                    (string) (($ctx['nacSql'] ?? [])['ordN'] ?? ''),
                    (string) $ctx['modoFecha'],
                    $ctx['lunesParam']
                );
            }

            return ['success' => true, 'datos' => $out];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'Error al consultar jerarquías.', 'error' => $e->getMessage()];
        }
    }

    /**
     * Borra filas que el histórico no usa: misma regla que `resolverContextoResumenSemana` + `whereFechaSqlYParams`.
     * Use primero `$dryRun = true` (solo cuenta). Con `$dryRun = false` ejecuta DELETE por etiqueta de semana.
     *
     * @param array{verbose?:bool} $opciones Si `verbose` es true, añade totales de depuración (sin cambiar el borrado).
     *
     * @return array{success:bool, dry_run:bool, eliminadas:int, por_semana:array, omitidas:array, error?:string, total_filas_tabla?:int}
     */
    public static function purgarFilasFueraCarteraHistorico(bool $dryRun, ?int $maxEtiquetasSemana = null, array $opciones = []): array
    {
        self::$cacheMapColumnasHisto = null;
        self::$cacheMetaSqlMoraCorteHisto = null;
        $verbose = (bool) ($opciones['verbose'] ?? false);
        $tabla = self::nombreTablaHistoricoPrimerosPagos();
        $porSemana = [];
        $omitidas = [];
        $totalElim = 0;
        $totalFilasTabla = null;
        try {
            $db = new DatabaseSegundometro();
            if ($verbose) {
                $totalFilasTabla = self::intDesdeFilaSqlCount(
                    $db->queryOne('SELECT COUNT(*) AS total_filas FROM `' . $tabla . '`')
                );
            }
            $rows = $db->queryAll(
                'SELECT DISTINCT TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4)) AS sem
                 FROM `' . $tabla . '`
                 WHERE SEMANA IS NOT NULL
                   AND TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4)) <> \'\'
                 ORDER BY sem DESC'
            );
            $lista = [];
            foreach ($rows as $r) {
                $s = trim((string) ($r['sem'] ?? ''));
                if ($s !== '') {
                    $lista[] = $s;
                }
            }
            if ($maxEtiquetasSemana !== null && $maxEtiquetasSemana > 0) {
                $lista = array_slice($lista, 0, $maxEtiquetasSemana);
            }
            foreach ($lista as $sem) {
                $ctx = self::resolverContextoResumenSemana($db, $sem);
                if (($ctx['type'] ?? '') !== 'ok') {
                    $razon = (string) ($ctx['mensaje'] ?? '');
                    if ($razon !== '' && str_contains($razon, 'No hay datos')) {
                        $sqlCnt = 'SELECT COUNT(*) AS c FROM `' . $tabla . '` WHERE ' . self::sqlWhereSemanaParam();
                        $n = self::intDesdeFilaSqlCount($db->queryOne($sqlCnt, ['sem' => $sem]));
                        $porSemana[] = ['semana' => $sem, 'filas' => $n, 'modo' => 'todas_etiqueta_sin_cartera'];
                        $totalElim += $n;
                        if (!$dryRun && $n > 0) {
                            $db->CRUD('DELETE FROM `' . $tabla . '` WHERE ' . self::sqlWhereSemanaParam(), ['sem' => $sem]);
                        }
                    } else {
                        $omitidas[] = ['semana' => $sem, 'razon' => $razon !== '' ? $razon : 'contexto inválido'];
                    }

                    continue;
                }
                $modoFecha = (string) $ctx['modoFecha'];
                $m1 = (string) $ctx['m1'];
                $m2 = (string) $ctx['m2'];
                $lunesParam = $ctx['lunesParam'];
                [$wf, $pex] = self::whereFechaSqlYParams($modoFecha, $m1, $m2, $lunesParam);
                $inner = preg_replace('/^\s*AND\s+/i', '', $wf, 1);
                if ($inner === '' || $inner === $wf) {
                    $omitidas[] = ['semana' => $sem, 'razon' => 'whereFecha vacío'];

                    continue;
                }
                [$wbTail, $pexBorrar] = self::whereBorrarFueraCarteraSqlYParams($modoFecha, $m1, $m2, $lunesParam);
                if ($wbTail === '') {
                    $omitidas[] = ['semana' => $sem, 'razon' => 'whereBorrar vacío'];

                    continue;
                }
                $paramsCartera = ['sem' => $sem] + $pex;
                $params = ['sem' => $sem] + $pexBorrar;
                $whereBorrar = self::sqlWhereSemanaParam() . $wbTail;
                $sqlCnt = 'SELECT COUNT(*) AS c FROM `' . $tabla . '` WHERE ' . $whereBorrar;
                $n = self::intDesdeFilaSqlCount($db->queryOne($sqlCnt, $params));
                $filaSem = ['semana' => $sem, 'filas' => $n, 'modo' => $modoFecha];
                if ($verbose) {
                    $sqlEt = 'SELECT COUNT(*) AS c FROM `' . $tabla . '` WHERE ' . self::sqlWhereSemanaParam();
                    $sqlCar = 'SELECT COUNT(*) AS c FROM `' . $tabla . '` WHERE ' . self::sqlWhereSemanaParam() . $wf;
                    $filaSem['filas_etiqueta'] = self::intDesdeFilaSqlCount($db->queryOne($sqlEt, ['sem' => $sem]));
                    $filaSem['filas_en_cartera'] = self::intDesdeFilaSqlCount($db->queryOne($sqlCar, $paramsCartera));
                }
                $porSemana[] = $filaSem;
                $totalElim += $n;
                if (!$dryRun && $n > 0) {
                    $db->CRUD('DELETE FROM `' . $tabla . '` WHERE ' . $whereBorrar, $params);
                }
            }

            $out = [
                'success' => true,
                'dry_run' => $dryRun,
                'eliminadas' => $totalElim,
                'por_semana' => $porSemana,
                'omitidas' => $omitidas,
            ];
            if ($verbose && $totalFilasTabla !== null) {
                $out['total_filas_tabla'] = $totalFilasTabla;
            }

            return $out;
        } catch (\Throwable $e) {
            $out = [
                'success' => false,
                'dry_run' => $dryRun,
                'eliminadas' => $totalElim,
                'por_semana' => $porSemana,
                'omitidas' => $omitidas,
                'error' => $e->getMessage(),
            ];
            if ($verbose && $totalFilasTabla !== null) {
                $out['total_filas_tabla'] = $totalFilasTabla;
            }

            return $out;
        } finally {
            self::$cacheMapColumnasHisto = null;
            self::$cacheMetaSqlMoraCorteHisto = null;
        }
    }

    /**
     * Borra **todas** las filas cuya columna `SEMANA` (tras `trim`) coincide con la etiqueta.
     *
     * Útil para **reemplazar** una semana ya cargada (p. ej. nuevo ETL desde `tbl_segundometro_histo`):
     * `purgarFilasFueraCarteraHistorico` solo elimina filas **fuera** de la ventana de cartera (fechas de
     * primer vencimiento), no sustituye filas que siguen siendo “válidas” para el reporte.
     *
     * @return array{success:bool, dry_run:bool, tabla?:string, semana?:string, filas?:int, mensaje?:string, error?:string}
     */
    public static function borrarTodasLasFilasPorEtiquetaSemana(string $semanaEtiqueta, bool $dryRun): array
    {
        $sem = self::normalizarSemanaParam(trim($semanaEtiqueta));
        if ($sem === null) {
            return [
                'success' => false,
                'dry_run' => $dryRun,
                'mensaje' => 'Etiqueta de semana no válida (vacía, demasiado larga o caracteres no permitidos).',
            ];
        }
        $tabla = self::nombreTablaHistoricoPrimerosPagos();
        try {
            $db = new DatabaseSegundometro();
            $sqlCnt = 'SELECT COUNT(*) AS c FROM `' . $tabla . '` WHERE ' . self::sqlWhereSemanaParam();
            $n = self::intDesdeFilaSqlCount($db->queryOne($sqlCnt, ['sem' => $sem]));
            if (!$dryRun && $n > 0) {
                $db->CRUD('DELETE FROM `' . $tabla . '` WHERE ' . self::sqlWhereSemanaParam(), ['sem' => $sem]);
            }

            return [
                'success' => true,
                'dry_run' => $dryRun,
                'tabla' => $tabla,
                'semana' => $sem,
                'filas' => $n,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'dry_run' => $dryRun,
                'tabla' => $tabla,
                'semana' => $sem,
                'filas' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Copia filas desde `tbl_segundometro_histo` hacia el histórico de primeros pagos (solo `INSERT` en destino).
     * No altera la tabla fuente. Mapeo y columnas alineados al SQL operativo del negocio (fechas con REGEXP + STR_TO_DATE).
     *
     * Flujo típico: (1) este método con las semanas deseadas; (2) `purgarFilasFueraCarteraHistorico` para dejar solo cartera.
     *
     * @param list<string> $semanasEtiqueta Etiquetas `SEMANA` (p. ej. «Semana 16-2026»), deduplicadas y validadas.
     * @param array{replace_dest?:bool} $opciones Si `replace_dest` es true, borra antes en **destino** las mismas etiquetas (no toca `tbl_segundometro_histo`).
     *
     * @return array{
     *   success:bool,
     *   dry_run:bool,
     *   mensaje?:string,
     *   error?:string,
     *   tabla_destino?:string,
     *   tabla_origen?:string,
     *   semanas?:list<string>,
     *   filas_en_origen?:int,
     *   por_semana?:list<array{semana:string,cnt:int}>,
     *   filas_destino_antes?:int,
     *   filas_eliminadas_destino?:int,
     *   filas_insertadas?:int
     * }
     */
    public static function copiarDesdeSegundometroHistoHaciaPrimerosPagos(array $semanasEtiqueta, bool $dryRun, array $opciones = []): array
    {
        $replaceDest = (bool) ($opciones['replace_dest'] ?? false);
        $tablaDst = self::nombreTablaHistoricoPrimerosPagos();
        $tablaSrc = self::TABLA_SEGUNDOMETRO_HISTO;

        $lista = [];
        foreach ($semanasEtiqueta as $raw) {
            $s = self::normalizarSemanaParam(trim((string) $raw));
            if ($s !== null && $s !== '' && !isset($lista[$s])) {
                $lista[$s] = true;
            }
        }
        $semanas = array_keys($lista);
        if ($semanas === []) {
            return [
                'success' => false,
                'dry_run' => $dryRun,
                'mensaje' => 'Indique al menos una etiqueta SEMANA válida.',
            ];
        }
        if (count($semanas) > self::COPIA_DESDE_HISTO_SEMANAS_MAX) {
            return [
                'success' => false,
                'dry_run' => $dryRun,
                'mensaje' => 'Demasiadas semanas en una sola corrida (máx. ' . (string) self::COPIA_DESDE_HISTO_SEMANAS_MAX . ').',
            ];
        }

        $whereSemIn = [];
        $paramsIn = [];
        foreach ($semanas as $i => $sem) {
            $k = 'sem' . $i;
            $whereSemIn[] = ':' . $k;
            $paramsIn[$k] = $sem;
        }
        $inSql = implode(',', $whereSemIn);
        $whereTrimSrc = 'TRIM(CAST(s.SEMANA AS CHAR CHARACTER SET utf8mb4)) IN (' . $inSql . ')';

        try {
            $db = new DatabaseSegundometro();
            $sqlAgg = 'SELECT TRIM(CAST(s.SEMANA AS CHAR CHARACTER SET utf8mb4)) AS sem, COUNT(*) AS c
                FROM `' . $tablaSrc . '` s
                WHERE ' . $whereTrimSrc . '
                GROUP BY TRIM(CAST(s.SEMANA AS CHAR CHARACTER SET utf8mb4))';
            $aggRows = $db->queryAll($sqlAgg, $paramsIn);
            $porSemana = [];
            $totalOrigen = 0;
            foreach ($aggRows as $r) {
                $sem = trim((string) ($r['sem'] ?? ''));
                $c = (int) ($r['c'] ?? 0);
                if ($sem !== '') {
                    $porSemana[] = ['semana' => $sem, 'cnt' => $c];
                    $totalOrigen += $c;
                }
            }
            $sqlCntDest = 'SELECT COUNT(*) AS c FROM `' . $tablaDst . '` WHERE TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4)) IN (' . $inSql . ')';
            $filasDestAntes = self::intDesdeFilaSqlCount($db->queryOne($sqlCntDest, $paramsIn));

            if (!$dryRun && !$replaceDest && $filasDestAntes > 0) {
                return [
                    'success' => false,
                    'dry_run' => false,
                    'mensaje' => 'Ya existen filas en destino para alguna de esas semanas. Use replace_dest en opciones o borre esas semanas en `tbl_histo_primeros_pagos` antes de copiar.',
                    'tabla_destino' => $tablaDst,
                    'tabla_origen' => $tablaSrc,
                    'semanas' => $semanas,
                    'filas_en_origen' => $totalOrigen,
                    'por_semana' => $porSemana,
                    'filas_destino_antes' => $filasDestAntes,
                ];
            }

            if ($dryRun) {
                return [
                    'success' => true,
                    'dry_run' => true,
                    'tabla_destino' => $tablaDst,
                    'tabla_origen' => $tablaSrc,
                    'semanas' => $semanas,
                    'filas_en_origen' => $totalOrigen,
                    'por_semana' => $porSemana,
                    'filas_destino_antes' => $filasDestAntes,
                ];
            }

            $eliminadas = 0;
            if ($totalOrigen < 1) {
                return [
                    'success' => true,
                    'dry_run' => false,
                    'tabla_destino' => $tablaDst,
                    'tabla_origen' => $tablaSrc,
                    'semanas' => $semanas,
                    'filas_en_origen' => 0,
                    'por_semana' => $porSemana,
                    'filas_destino_antes' => $filasDestAntes,
                    'filas_eliminadas_destino' => 0,
                    'filas_insertadas' => 0,
                    'mensaje' => 'Sin filas en `tbl_segundometro_histo` para las semanas indicadas; no se insertó nada.',
                ];
            }

            $db->beginTransaction();
            if ($replaceDest && $filasDestAntes > 0) {
                $sqlDel = 'DELETE FROM `' . $tablaDst . '` WHERE TRIM(CAST(SEMANA AS CHAR CHARACTER SET utf8mb4)) IN (' . $inSql . ')';
                $eliminadas = $db->CRUD($sqlDel, $paramsIn);
            }

            $sqlInsert = self::sqlInsertPrimerosPagosDesdeSegundometroHisto($tablaDst, $tablaSrc, $whereTrimSrc);
            $insertadas = $db->CRUD($sqlInsert, $paramsIn);
            $db->commit();

            return [
                'success' => true,
                'dry_run' => false,
                'tabla_destino' => $tablaDst,
                'tabla_origen' => $tablaSrc,
                'semanas' => $semanas,
                'filas_en_origen' => $totalOrigen,
                'por_semana' => $porSemana,
                'filas_destino_antes' => $filasDestAntes,
                'filas_eliminadas_destino' => $eliminadas,
                'filas_insertadas' => $insertadas,
            ];
        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                try {
                    $db->rollback();
                } catch (\Throwable $e2) {
                }
            }

            return [
                'success' => false,
                'dry_run' => $dryRun,
                'tabla_destino' => $tablaDst,
                'tabla_origen' => $tablaSrc,
                'semanas' => $semanas,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * INSERT…SELECT fijo (negocio): `tbl_segundometro_histo` → histórico primeros pagos.
     *
     * @param string $whereTrimSrc Debe ser `TRIM(CAST(s.SEMANA …)) IN (:sem0,…)` con binds ya definidos.
     */
    private static function sqlInsertPrimerosPagosDesdeSegundometroHisto(string $tablaDst, string $tablaSrc, string $whereTrimSrc): string
    {
        return 'INSERT INTO `' . $tablaDst . '` (
            KT,
            Id_credito,
            Id_cliente,
            Nombre_cliente,
            Fecha_nacimiento,
            Curp,
            Rfc,
            Email,
            Celular,
            Telefono_casa,
            Sucursal,
            Status_credito,
            Plazo,
            Numero_amortizaciones,
            Capital,
            Monto_otorgado,
            Cuota,
            Fecha_inicio,
            Fecha_primer_vencimiento,
            Fecha_ultimo_vencimiento,
            Referencia_stp,
            Dias_mora,
            Dias_mora_max,
            Saldo_total_vencido,
            Saldo_total_capital,
            Saldo_total_vigente,
            Abonos_capital,
            Abonos_interes,
            Abonos_gasto_admin,
            Abonos_moratorios,
            Abonos_extemporaneos,
            Abonos_seguro_vida,
            Abonos_seguro_resguardo,
            Abonos_seguro_bienes,
            Abonos_total,
            Efectivo_total,
            Codigo_postal,
            Colonia,
            Estado,
            Ciudad,
            Municipio,
            Calle,
            Num_exterior,
            Num_interior,
            Telefono_referencia_01,
            Tipo_referencia_01,
            Nombre_referencia_02,
            Telefono_referencia_02,
            Tipo_referencia_02,
            Num_cuotas_restantes,
            Num_cuotas_pagadas,
            Saldo_vencido_inicio,
            Saldo_para_liquidar_hoy,
            Codigo_postal_1,
            Estado_1,
            Avance_Pago_Plazo,
            Avance_Pago_Capital,
            Rango_Avance_Capital,
            Monto_otorgado_2,
            Cuotas_devengadas,
            Bucket_Morosidad,
            Dias_mora_ajustado,
            Dias_mora_ajustado_2,
            Bucket_Morosidad_Real,
            Bucket_Morosidad_Final,
            Ajuste,
            Referencia_stp_limpia,
            Tipo_Referencia,
            Domicilio_Completo,
            Dias_mora_Jueves_07_30,
            SEMANA,
            fecha_hora_insert
        )
        SELECT
            s.KT,
            s.Id_credito,
            s.Id_cliente,
            s.Nombre_cliente,
            CASE
                WHEN s.Fecha_nacimiento REGEXP \'^[0-9]{4}-[0-9]{2}-[0-9]{2}$\'
                    THEN STR_TO_DATE(s.Fecha_nacimiento, \'%Y-%m-%d\')
                WHEN s.Fecha_nacimiento REGEXP \'^[0-9]{2}/[0-9]{2}/[0-9]{4}$\'
                    THEN STR_TO_DATE(s.Fecha_nacimiento, \'%d/%m/%Y\')
                ELSE NULL
            END,
            NULL,
            NULL,
            NULL,
            s.Celular,
            NULL,
            s.Sucursal,
            s.Status_credito,
            NULL,
            s.Numero_amortizaciones,
            NULL,
            s.Monto_otorgado,
            s.Cuota,
            CASE
                WHEN s.Fecha_inicio REGEXP \'^[0-9]{4}-[0-9]{2}-[0-9]{2}$\'
                    THEN STR_TO_DATE(s.Fecha_inicio, \'%Y-%m-%d\')
                WHEN s.Fecha_inicio REGEXP \'^[0-9]{2}/[0-9]{2}/[0-9]{4}$\'
                    THEN STR_TO_DATE(s.Fecha_inicio, \'%d/%m/%Y\')
                ELSE NULL
            END,
            CASE
                WHEN s.Fecha_primer_vencimiento REGEXP \'^[0-9]{4}-[0-9]{2}-[0-9]{2}$\'
                    THEN STR_TO_DATE(s.Fecha_primer_vencimiento, \'%Y-%m-%d\')
                WHEN s.Fecha_primer_vencimiento REGEXP \'^[0-9]{2}/[0-9]{2}/[0-9]{4}$\'
                    THEN STR_TO_DATE(s.Fecha_primer_vencimiento, \'%d/%m/%Y\')
                ELSE NULL
            END,
            CASE
                WHEN s.Fecha_ultimo_vencimiento REGEXP \'^[0-9]{4}-[0-9]{2}-[0-9]{2}$\'
                    THEN STR_TO_DATE(s.Fecha_ultimo_vencimiento, \'%Y-%m-%d\')
                WHEN s.Fecha_ultimo_vencimiento REGEXP \'^[0-9]{2}/[0-9]{2}/[0-9]{4}$\'
                    THEN STR_TO_DATE(s.Fecha_ultimo_vencimiento, \'%d/%m/%Y\')
                ELSE NULL
            END,
            s.Referencia_stp,
            s.Dias_mora,
            s.Dias_mora_max,
            NULL,
            s.Saldo_total_capital,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            s.Abonos_total,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            NULL,
            s.Num_cuotas_pagadas,
            s.Saldo_vencido_inicio,
            s.Saldo_para_liquidar_hoy,
            s.Codigo_postal_1,
            s.Estado_1,
            s.Avance_Pago_Plazo,
            NULL,
            NULL,
            s.Monto_otorgado_2,
            s.Cuotas_devengadas,
            s.Bucket_Morosidad,
            s.Dias_mora_ajustado,
            s.Dias_mora_ajustado_2,
            s.Bucket_Morosidad_Real,
            s.Bucket_Morosidad_Final,
            s.Ajuste,
            NULL,
            NULL,
            s.Domicilio_Completo,
            s.Dias_mora_Jueves_07_30,
            s.SEMANA,
            NOW()
        FROM `' . $tablaSrc . '` s
        WHERE ' . $whereTrimSrc;
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
                FROM `' . self::nombreTablaHistoricoPrimerosPagos() . '`
                WHERE ' . self::sqlWhereSemanaParam() . $whereFecha;
        $params = ['sem' => $sem] + $paramsExtra;
        $r = $db->queryOne($sql, $params);

        return [
            'total' => self::intDesdeFilaSqlCount($r),
        ];
    }

    /**
     * Predicado AND … para filas **fuera** de la cartera (equivalente a negar el `whereFecha` de cartera, incluyendo `Fecha_primer_vencimiento` NULL).
     * Evita `NOT((col = :a OR col = :b))` y también `IS NULL OR (<> … AND <>)` con los mismos binds: con PDO+MySQL (`ATTR_EMULATE_PREPARES = false`) el `COUNT`/`DELETE` podía devolver **0** pese a cientos de miles de filas fuera de cartera. Se usa `<=>` y dos `NOT` independientes.
     *
     * @return array{0:string,1:array<string,string>} [fragmento SQL que empieza con " AND (...)", parámetros sin :sem]
     */
    private static function whereBorrarFueraCarteraSqlYParams(string $modoFecha, string $martesIso, string $domingoIso, ?string $lunesIsoExacto): array
    {
        if ($modoFecha === 'lunes' && $lunesIsoExacto !== null && $lunesIsoExacto !== '') {
            $dtLunes = \DateTimeImmutable::createFromFormat('Y-m-d', $lunesIsoExacto);
            $lunesDmy = $dtLunes instanceof \DateTimeImmutable ? $dtLunes->format('d/m/Y') : $lunesIsoExacto;

            // Equivalente a NOT(FPV=:iso OR FPV=:dmy) sin agrupar OR bajo NOT (PDO+MySQL nativo puede devolver 0 filas).
            // `<=>` trata NULL como distinto de las fechas de cartera → esas filas también se purgan.
            // `d/m/Y` como literal comparado a DATETIME dispara SQLSTATE 22007 en modo estricto; STR_TO_DATE evita el cast inválido.
            return [
                ' AND ((NOT (Fecha_primer_vencimiento <=> :lunes_iso)) AND (NOT (Fecha_primer_vencimiento <=> STR_TO_DATE(:lunes_dmy, \'%d/%m/%Y\'))))',
                ['lunes_iso' => $lunesIsoExacto, 'lunes_dmy' => $lunesDmy],
            ];
        }
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
                $phDmy[] = 'STR_TO_DATE(:' . $kDmy . ', \'%d/%m/%Y\')';
                $params[$kIso] = $fIso;
                $params[$kDmy] = $fechasDmy[$i];
            }

            return [
                ' AND (Fecha_primer_vencimiento IS NULL OR ((Fecha_primer_vencimiento NOT IN (' . implode(',', $phIso) . ')) AND (Fecha_primer_vencimiento NOT IN (' . implode(',', $phDmy) . '))))',
                $params,
            ];
        }

        return ['', []];
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
                $phDmy[] = 'STR_TO_DATE(:' . $kDmy . ', \'%d/%m/%Y\')';
                $params[$kIso] = $fIso;
                $params[$kDmy] = $fechasDmy[$i];
            }

            $where = ' AND (Fecha_primer_vencimiento IN (' . implode(',', $phIso) . ') OR Fecha_primer_vencimiento IN (' . implode(',', $phDmy) . '))';
            return [$where, $params];
        }
        if ($modoFecha === 'lunes' && $lunesIsoExacto !== null && $lunesIsoExacto !== '') {
            $dtLunes = \DateTimeImmutable::createFromFormat('Y-m-d', $lunesIsoExacto);
            $lunesDmy = $dtLunes instanceof \DateTimeImmutable ? $dtLunes->format('d/m/Y') : $lunesIsoExacto;

            return [
                ' AND (Fecha_primer_vencimiento = :lunes_iso OR Fecha_primer_vencimiento <=> STR_TO_DATE(:lunes_dmy, \'%d/%m/%Y\'))',
                ['lunes_iso' => $lunesIsoExacto, 'lunes_dmy' => $lunesDmy],
            ];
        }

        return ['', []];
    }

    /**
     * Pares (nacimiento, corte) con conteo — pocas filas vs. leer toda la tabla al cliente.
     *
     * @param string $sqlBnNacimiento expresión SQL completa del bucket de nacimiento (p. ej. CASE sobre `Dias_mora`)
     *
     * @return list<array{bucket_nacio:?string,bucket_corte:?string,cnt:int}>
     */
    private static function queryDistribNacimientoCorte(
        DatabaseSegundometro $db,
        string $sem,
        string $martesIso,
        string $domingoIso,
        string $sqlBnNacimiento,
        string $modoFecha,
        ?string $lunesIsoExacto
    ): array
    {
        $sqlBucketNacio = $sqlBnNacimiento;
        $moraCorte = self::sqlExprMoraCorteHisto($db);
        $sqlBucketCorte = self::sqlBucketCorteDesdeMoraSqlExpr($moraCorte);
        [$whereFecha, $paramsExtra] = self::whereFechaSqlYParams($modoFecha, $martesIso, $domingoIso, $lunesIsoExacto);
        $sql = 'SELECT bn AS bucket_nacio, bc AS bucket_corte, COUNT(*) AS cnt
                FROM (
                    SELECT ' . $sqlBucketNacio . ' AS bn,
                           ' . $sqlBucketCorte . ' AS bc
                    FROM `' . self::nombreTablaHistoricoPrimerosPagos() . '`
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
     * @return array<string, true>
     */
    private static function mapColumnasTablaHisto(DatabaseSegundometro $db): array
    {
        if (self::$cacheMapColumnasHisto !== null) {
            return self::$cacheMapColumnasHisto;
        }
        $tabla = self::nombreTablaHistoricoPrimerosPagos();
        $rows = $db->queryAll(
            'SELECT COLUMN_NAME AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tbl',
            ['tbl' => $tabla]
        );
        $m = [];
        foreach ((array) $rows as $r) {
            $c = (string) ($r['c'] ?? $r['COLUMN_NAME'] ?? '');
            if ($c !== '') {
                $m[$c] = true;
            }
        }
        self::$cacheMapColumnasHisto = $m;

        return self::$cacheMapColumnasHisto;
    }

    /**
     * COALESCE de columnas existentes; si ninguna existe, literal SQL (sin columna).
     *
     * @param array<string, true> $present
     * @param list<string>         $preferencia
     */
    private static function sqlDimJerarquia(array $present, array $preferencia, string $defecto): string
    {
        $chunks = [];
        foreach ($preferencia as $col) {
            if (isset($present[$col])) {
                $chunks[] = 'NULLIF(TRIM(CAST(`' . $col . '` AS CHAR CHARACTER SET utf8mb4)), \'\')';
            }
        }
        $lit = str_replace('\'', '\'\'', $defecto);
        if ($chunks === []) {
            return '\'' . $lit . '\'';
        }

        return 'COALESCE(' . implode(', ', $chunks) . ', \'' . $lit . '\')';
    }

    /**
     * @param string $sqlOrdNacimiento expresión SQL completa 0..4 para orden de bucket de nacimiento
     *
     * @return list<array{Territorial:string,Zonal:string,Jefe_de_Plaza:string,Gestor_Asignado:string,total:int,cobrados:int}>
     */
    private static function queryJerarquiaAgregada(
        DatabaseSegundometro $db,
        string $sem,
        string $martesIso,
        string $domingoIso,
        string $sqlOrdNacimiento,
        string $modoFecha,
        ?string $lunesIsoExacto
    ): array
    {
        $ordN = $sqlOrdNacimiento;
        $moraCorte = self::sqlExprMoraCorteHisto($db);
        $ordC = self::sqlBucketCorteOrdDesdeMoraSqlExpr($moraCorte);
        [$whereFecha, $paramsExtra] = self::whereFechaSqlYParams($modoFecha, $martesIso, $domingoIso, $lunesIsoExacto);
        $present = self::mapColumnasTablaHisto($db);
        $sqlTer = self::sqlDimJerarquia($present, ['Territorial', 'Estado'], '(Sin territorial)');
        $sqlZon = self::sqlDimJerarquia($present, ['Zonal', 'Municipio', 'Ciudad'], '(Sin zonal)');
        $sqlJef = self::sqlDimJerarquia($present, ['Jefe_de_Plaza', 'Sucursal'], '(Sin jefe)');
        $sqlGest = self::sqlDimJerarquia($present, ['Gestor_Asignado'], '(Sin gestor)');
        $sql = 'SELECT ter AS Territorial, zon AS Zonal, jefe AS Jefe_de_Plaza, gest AS Gestor_Asignado,
                       COUNT(*) AS total,
                       SUM(CASE WHEN ord_n IS NOT NULL AND ord_c IS NOT NULL
                                     AND ord_n BETWEEN 0 AND 4 AND ord_c BETWEEN 0 AND 4
                                     AND ord_c < ord_n
                                THEN 1 ELSE 0 END) AS cobrados
                FROM (
                    SELECT
                        ' . $sqlTer . ' AS ter,
                        ' . $sqlZon . ' AS zon,
                        ' . $sqlJef . ' AS jefe,
                        ' . $sqlGest . ' AS gest,
                        ' . $ordN . ' AS ord_n,
                        ' . $ordC . ' AS ord_c
                    FROM `' . self::nombreTablaHistoricoPrimerosPagos() . '`
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
             FROM `' . self::nombreTablaHistoricoPrimerosPagos() . '`
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
             FROM `' . self::nombreTablaHistoricoPrimerosPagos() . '`
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
                FROM `' . self::nombreTablaHistoricoPrimerosPagos() . '`
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
            WHEN \'Current\' THEN \'a) Current\'
            WHEN \'current\' THEN \'a) Current\'
            WHEN \'b) 1 a 7 dias\' THEN \'b) 1 a 7 dias\'
            WHEN \'b) 1 a 7 días\' THEN \'b) 1 a 7 dias\'
            WHEN \'c) 8 a 30 dias\' THEN \'c) 8 a 30 dias\'
            WHEN \'c) 8 a 30 días\' THEN \'c) 8 a 30 dias\'
            WHEN \'c) 8 a 14 dias\' THEN \'c) 8 a 30 dias\'
            WHEN \'c) 8 a 14 días\' THEN \'c) 8 a 30 dias\'
            WHEN \'d) 15 a 21 dias\' THEN \'c) 8 a 30 dias\'
            WHEN \'d) 15 a 21 días\' THEN \'c) 8 a 30 dias\'
            WHEN \'e) 22 a 29 dias\' THEN \'c) 8 a 30 dias\'
            WHEN \'e) 22 a 30 dias\' THEN \'c) 8 a 30 dias\'
            WHEN \'e) 22 a 30 días\' THEN \'c) 8 a 30 dias\'
            WHEN \'d) 31 a 60 dias\' THEN \'d) 31 a 60 dias\'
            WHEN \'d) 31 a 60 días\' THEN \'d) 31 a 60 dias\'
            WHEN \'f) 31 a 60 dias\' THEN \'d) 31 a 60 dias\'
            WHEN \'f) 31 a 60 días\' THEN \'d) 31 a 60 dias\'
            WHEN \'e) 61+ dias\' THEN \'e) 61+ dias\'
            WHEN \'e) 61+ días\' THEN \'e) 61+ dias\'
            WHEN \'g) 61 a 90 dias\' THEN \'e) 61+ dias\'
            WHEN \'h) 91 a 120 dias\' THEN \'e) 61+ dias\'
            WHEN \'i) 121+ dias\' THEN \'e) 61+ dias\'
            WHEN \'j) First Payment Default\' THEN \'b) 1 a 7 dias\'
            WHEN \'j) Second Payment Default\' THEN \'c) 8 a 30 dias\'
            WHEN \'k) Never Paid\' THEN \'e) 61+ dias\'
            ELSE NULL END)';
    }

    /** 0..4 según bucket de nacimiento; NULL si no reconocido. */
    private static function sqlBucketNacimientoOrdExpr(string $col): string
    {
        return '(CASE TRIM(CAST(`' . $col . '` AS CHAR CHARACTER SET utf8mb4))
            WHEN \'a) Current\' THEN 0
            WHEN \'Current\' THEN 0
            WHEN \'current\' THEN 0
            WHEN \'b) 1 a 7 dias\' THEN 1
            WHEN \'b) 1 a 7 días\' THEN 1
            WHEN \'c) 8 a 30 dias\' THEN 2
            WHEN \'c) 8 a 30 días\' THEN 2
            WHEN \'c) 8 a 14 dias\' THEN 2
            WHEN \'c) 8 a 14 días\' THEN 2
            WHEN \'d) 15 a 21 dias\' THEN 2
            WHEN \'d) 15 a 21 días\' THEN 2
            WHEN \'e) 22 a 29 dias\' THEN 2
            WHEN \'e) 22 a 30 dias\' THEN 2
            WHEN \'e) 22 a 30 días\' THEN 2
            WHEN \'d) 31 a 60 dias\' THEN 3
            WHEN \'d) 31 a 60 días\' THEN 3
            WHEN \'f) 31 a 60 dias\' THEN 3
            WHEN \'f) 31 a 60 días\' THEN 3
            WHEN \'e) 61+ dias\' THEN 4
            WHEN \'e) 61+ días\' THEN 4
            WHEN \'g) 61 a 90 dias\' THEN 4
            WHEN \'h) 91 a 120 dias\' THEN 4
            WHEN \'i) 121+ dias\' THEN 4
            WHEN \'j) First Payment Default\' THEN 1
            WHEN \'j) Second Payment Default\' THEN 2
            WHEN \'k) Never Paid\' THEN 4
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

        $tieneCarteraReal = false;
        foreach ($terOrdenados as $t) {
            if (!self::esCurrentTerritorial((string) ($t['nombre'] ?? '')) && (int) ($t['total'] ?? 0) > 0) {
                $tieneCarteraReal = true;
                break;
            }
        }

        $html = '';
        foreach ($terOrdenados as $idx => $ter) {
            $nombreTer = (string) ($ter['nombre'] ?? '');
            if (self::esCurrentTerritorial($nombreTer)) {
                if (!$tieneCarteraReal) {
                    $html .= '
                        <div class="card mb-3 border-start border-3 border-secondary">
                            <div class="card-body py-3">
                                <p class="mb-0 text-muted" style="font-size:.82rem;">
                                    <i class="fa fa-circle-info text-secondary me-2"></i>
                                    El seguimiento de los créditos se podrá visualizar una vez que se asigne la cartera. Consulte disponibilidad con el administrador de asignación de cartera.
                                </p>
                            </div>
                        </div>';
                }

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
