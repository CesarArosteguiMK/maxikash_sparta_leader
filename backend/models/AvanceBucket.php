<?php

namespace Models;

use Core\DatabaseSegundometro;

final class AvanceBucket
{
    /** @var list<array{orden:int,bucket:string}> */
    private const BUCKETS = [
        ['orden' => 1, 'bucket' => 'a) Current'],
        ['orden' => 2, 'bucket' => 'b) 1 a 7 dias'],
        ['orden' => 3, 'bucket' => 'c) 8 a 14 dias'],
        ['orden' => 4, 'bucket' => 'd) 15 a 21 dias'],
        ['orden' => 5, 'bucket' => 'e) 22 a 30 dias'],
        ['orden' => 6, 'bucket' => 'f) 31 a 60 dias'],
        ['orden' => 7, 'bucket' => 'g) 61 a 90 dias'],
        ['orden' => 8, 'bucket' => 'h) 91 a 120 dias'],
        ['orden' => 9, 'bucket' => 'i) 121+ dias'],
    ];

    /** @var list<string> */
    private const CORTES = [
        '07:30',
        '09:30',
        '11:30',
        '13:30',
        '14:30',
        '16:30',
        '18:30',
        '20:30',
        '23:50',
    ];

    /**
     * @return array<string,mixed>
     */
    public static function calcular(?string $corte = null): array
    {
        $db = new DatabaseSegundometro();
        $corteNormalizado = self::normalizarCorte($corte);
        $columnaCorte = self::columnaDiasMoraCorte($corteNormalizado);
        $bucketInicioSql = self::bucketSql('Bucket_Morosidad_Real');
        $bucketCierreSql = self::bucketCierreAjustadoSql($columnaCorte);

        $rows = $db->queryAll("
            SELECT bucket_inicio, bucket_cierre, COUNT(*) AS creditos
            FROM (
                SELECT {$bucketInicioSql} AS bucket_inicio,
                       {$bucketCierreSql} AS bucket_cierre
                FROM tbl_segundometro_semana
            ) x
            WHERE bucket_inicio IS NOT NULL
              AND bucket_cierre IS NOT NULL
            GROUP BY bucket_inicio, bucket_cierre
        ");

        return self::formatear($rows, $corteNormalizado, self::diaCorteNombre());
    }

    /**
     * Calcula la misma matriz de Avance Bucket, pero usando snapshots historicos.
     *
     * @return array<string,mixed>
     */
    public static function calcularHistorico(?string $semana = null, ?string $corte = null): array
    {
        $db = new DatabaseSegundometro();
        $semanas = self::ultimasSemanasHistoricas($db, 6);
        if ($semanas === []) {
            return [
                'success' => false,
                'mensaje' => 'No hay semanas historicas disponibles en tbl_segundometro_histo.',
                'semanas' => [],
            ];
        }

        $semanaNormalizada = self::normalizarSemanaHistorica($semana, $semanas);
        $corteNormalizado = self::normalizarCorte($corte);
        $bucketInicioSql = self::bucketSql('Bucket_Morosidad_Real');
        $bucketCierreSql = self::bucketCierreAjustadoDaxHistoricoSql();

        $rows = $db->queryAll("
            SELECT bucket_inicio, bucket_cierre, COUNT(*) AS creditos
            FROM (
                SELECT {$bucketInicioSql} AS bucket_inicio,
                       {$bucketCierreSql} AS bucket_cierre
                FROM tbl_segundometro_histo
                WHERE SEMANA = :semana
            ) x
            WHERE bucket_inicio IS NOT NULL
              AND bucket_cierre IS NOT NULL
            GROUP BY bucket_inicio, bucket_cierre
        ", ['semana' => $semanaNormalizada]);

        $payload = self::formatear($rows, $corteNormalizado, self::diaCorteNombre());
        $payload['modo'] = 'historico';
        $payload['semana'] = $semanaNormalizada;
        $payload['semanas'] = $semanas;
        $payload['origen'] = 'tbl_segundometro_histo';

        return $payload;
    }

    /**
     * Simulacion Avance de Buckets (+1) usando el corte de Dias_mora de tbl_segundometro_semana.
     *
     * @return array<string,mixed>
     */
    public static function calcularEstresado(?string $corte = null): array
    {
        $db = new DatabaseSegundometro();
        $corteNormalizado = self::normalizarCorte($corte);
        $columnaCorte = self::columnaDiasMoraCorte($corteNormalizado);
        $bucketInicioSql = self::bucketSql('Bucket_Morosidad_Real');
        $bucketCierreAjustadoSql = self::bucketCierreAjustadoSql($columnaCorte);
        $bucketMasUnoSql = self::bucketMasUnoSql('cierre_ajustado');

        $rows = $db->queryAll("
            SELECT bucket_inicio, bucket_cierre, COUNT(DISTINCT id_credito) AS creditos
            FROM (
                SELECT bucket_inicio,
                       CASE
                           WHEN bucket_inicio IS NULL OR cierre_ajustado IS NULL THEN NULL
                           WHEN bucket_inicio = 'a) Current' THEN 'a) Current'
                           ELSE ({$bucketMasUnoSql})
                       END AS bucket_cierre,
                       id_credito
                FROM (
                    SELECT {$bucketInicioSql} AS bucket_inicio,
                           {$bucketCierreAjustadoSql} AS cierre_ajustado,
                           Id_credito AS id_credito
                    FROM tbl_segundometro_semana
                ) base
            ) x
            WHERE bucket_inicio IS NOT NULL
              AND bucket_cierre IS NOT NULL
              AND id_credito IS NOT NULL
            GROUP BY bucket_inicio, bucket_cierre
        ");

        $payload = self::formatear($rows, $corteNormalizado, self::diaCorteNombre(), 'Bucket Morosidad Real', 'Cierre Actual +1');
        $rowsInvertidos = array_map(static function (array $row): array {
            return [
                'bucket_inicio' => $row['bucket_cierre'] ?? null,
                'bucket_cierre' => $row['bucket_inicio'] ?? null,
                'creditos' => $row['creditos'] ?? 0,
            ];
        }, $rows);
        $payloadInvertido = self::formatear($rowsInvertidos, $corteNormalizado, self::diaCorteNombre(), 'Cierre Actual +1', 'Bucket Morosidad Real');
        $payload['modo'] = 'estresado';
        $payload['origen'] = 'tbl_segundometro_semana';
        $payload['titulo'] = 'Bucket estresado';
        $payload['resumen_inicio'] = self::ordenarResumenPorValor($payload['resumen_inicio']);
        $payload['matriz_invertida'] = $payloadInvertido['matriz_creditos'];
        $payload['matriz_secundaria_titulo'] = 'Matriz de avance bucket invertida';
        $payload['matriz_secundaria_tipo'] = 'creditos';

        return $payload;
    }

    /**
     * @param list<array<string,mixed>> $rows
     * @return array<string,mixed>
     */
    private static function formatear(
        array $rows,
        string $corte,
        string $diaCorte,
        string $rowLabel = 'Bucket Inicio',
        string $columnLabel = 'Cierre Ajustado'
    ): array
    {
        $buckets = array_column(self::BUCKETS, 'bucket');
        $matrix = [];
        $rowTotals = [];
        $columnTotals = [];
        $total = 0;

        foreach ($buckets as $rowBucket) {
            $matrix[$rowBucket] = [];
            $rowTotals[$rowBucket] = 0;
            foreach ($buckets as $columnBucket) {
                $matrix[$rowBucket][$columnBucket] = 0;
                $columnTotals[$columnBucket] = $columnTotals[$columnBucket] ?? 0;
            }
        }

        foreach ($rows as $row) {
            $inicio = (string) ($row['bucket_inicio'] ?? '');
            $cierre = (string) ($row['bucket_cierre'] ?? '');
            if (!isset($matrix[$inicio][$cierre])) {
                continue;
            }

            $creditos = (int) ($row['creditos'] ?? 0);
            $matrix[$inicio][$cierre] += $creditos;
            $rowTotals[$inicio] += $creditos;
            $columnTotals[$cierre] += $creditos;
            $total += $creditos;
        }

        return [
            'success' => true,
            'modo' => 'actual',
            'corte' => $corte,
            'corte_opciones' => self::CORTES,
            'dia_corte' => $diaCorte,
            'row_label' => $rowLabel,
            'column_label' => $columnLabel,
            'total_label' => 'Creditos',
            'buckets' => self::BUCKETS,
            'total' => $total,
            'resumen_inicio' => self::resumenBuckets($rowTotals, $total),
            'resumen_cierre' => self::resumenBuckets($columnTotals, $total),
            'matriz_creditos' => self::matriz($matrix, $rowTotals, $columnTotals, $total, false),
            'matriz_porcentajes' => self::matriz($matrix, $rowTotals, $columnTotals, $total, true),
            'indicadores' => self::indicadores($matrix),
        ];
    }

    /**
     * @param list<array{bucket:string,valor:int,porcentaje:float}> $rows
     * @return list<array{bucket:string,valor:int,porcentaje:float}>
     */
    private static function ordenarResumenPorValor(array $rows): array
    {
        usort($rows, static function (array $a, array $b): int {
            $valor = ((int) ($b['valor'] ?? 0)) <=> ((int) ($a['valor'] ?? 0));
            if ($valor !== 0) {
                return $valor;
            }

            return strcmp((string) ($a['bucket'] ?? ''), (string) ($b['bucket'] ?? ''));
        });

        return $rows;
    }

    /**
     * @param array<string,int> $totales
     * @return list<array{bucket:string,valor:int,porcentaje:float}>
     */
    private static function resumenBuckets(array $totales, int $total): array
    {
        $out = [];
        foreach (self::BUCKETS as $bucket) {
            $label = $bucket['bucket'];
            $valor = (int) ($totales[$label] ?? 0);
            $out[] = [
                'bucket' => $label,
                'valor' => $valor,
                'porcentaje' => $total > 0 ? round(($valor / $total) * 100, 2) : 0.0,
            ];
        }

        return $out;
    }

    /**
     * @param array<string,array<string,int>> $matrix
     * @param array<string,int> $rowTotals
     * @param array<string,int> $columnTotals
     * @return array{filas:list<array<string,mixed>>,totales_columnas:array<string,float|int>,total:float|int}
     */
    private static function matriz(array $matrix, array $rowTotals, array $columnTotals, int $total, bool $porcentaje): array
    {
        $filas = [];
        foreach (self::BUCKETS as $rowBucket) {
            $label = $rowBucket['bucket'];
            $celdas = [];
            foreach (self::BUCKETS as $columnBucket) {
                $col = $columnBucket['bucket'];
                $valor = (int) ($matrix[$label][$col] ?? 0);
                $celdas[$col] = $porcentaje
                    ? ($total > 0 ? round(($valor / $total) * 100, 2) : 0.0)
                    : $valor;
            }

            $rowTotal = (int) ($rowTotals[$label] ?? 0);
            $filas[] = [
                'bucket' => $label,
                'celdas' => $celdas,
                'total' => $porcentaje
                    ? ($total > 0 ? round(($rowTotal / $total) * 100, 2) : 0.0)
                    : $rowTotal,
            ];
        }

        $totalesColumnas = [];
        foreach (self::BUCKETS as $columnBucket) {
            $label = $columnBucket['bucket'];
            $columnTotal = (int) ($columnTotals[$label] ?? 0);
            $totalesColumnas[$label] = $porcentaje
                ? ($total > 0 ? round(($columnTotal / $total) * 100, 2) : 0.0)
                : $columnTotal;
        }

        return [
            'filas' => $filas,
            'totales_columnas' => $totalesColumnas,
            'total' => $porcentaje ? 100.0 : $total,
        ];
    }

    /**
     * @param array<string,array<string,int>> $matrix
     * @return array<string,int>
     */
    private static function indicadores(array $matrix): array
    {
        $mejoran = 0;
        $igual = 0;
        $empeoran = 0;
        $orden = [];
        foreach (self::BUCKETS as $bucket) {
            $orden[$bucket['bucket']] = $bucket['orden'];
        }

        foreach ($matrix as $inicio => $cols) {
            foreach ($cols as $cierre => $creditos) {
                if ($creditos <= 0 || !isset($orden[$inicio], $orden[$cierre])) {
                    continue;
                }
                if ($orden[$cierre] < $orden[$inicio]) {
                    $mejoran += $creditos;
                } elseif ($orden[$cierre] === $orden[$inicio]) {
                    $igual += $creditos;
                } else {
                    $empeoran += $creditos;
                }
            }
        }

        return [
            'mejoran' => $mejoran,
            'igual' => $igual,
            'empeoran' => $empeoran,
        ];
    }

    private static function bucketCierreAjustadoSql(string $columnaCorte): string
    {
        $bucketReal = self::bucketSql('Bucket_Morosidad_Real');
        $cierreActual = self::bucketDesdeDiasMoraSql($columnaCorte);
        $ordenReal = self::ordenBucketCaseSql($bucketReal);
        $ordenCierre = self::ordenBucketCaseSql($cierreActual);

        return "
            CASE
                WHEN Variable_8 IS NOT NULL AND TRIM(CAST(Variable_8 AS CHAR)) <> '' THEN 'a) Current'
                WHEN Ghost IS NOT NULL AND TRIM(CAST(Ghost AS CHAR)) <> '' AND TRIM(CAST(Ghost AS CHAR)) <> '-' THEN 'a) Current'
                WHEN ({$ordenReal}) IS NULL OR ({$ordenCierre}) IS NULL THEN NULL
                WHEN ({$ordenReal}) <= 5 AND ({$ordenCierre}) > ({$ordenReal}) THEN ({$bucketReal})
                ELSE ({$cierreActual})
            END
        ";
    }

    private static function bucketCierreAjustadoDaxHistoricoSql(): string
    {
        $bucketReal = self::bucketSql('Bucket_Morosidad_Real');
        $cierreBase = self::bucketSql("COALESCE(NULLIF(TRIM(CAST(Bucket_ajustado_ghost AS CHAR)), ''), Cierre_Actual)");
        $cierreActual = "
            CASE
                WHEN Variable_8 IS NOT NULL AND TRIM(CAST(Variable_8 AS CHAR)) <> '' THEN 'a) Current'
                WHEN Ghost IS NOT NULL AND TRIM(CAST(Ghost AS CHAR)) <> '' AND TRIM(CAST(Ghost AS CHAR)) <> '-' THEN 'a) Current'
                ELSE ({$cierreBase})
            END
        ";
        $ordenReal = self::ordenBucketCaseSql($bucketReal);
        $ordenCierre = self::ordenBucketCaseSql($cierreActual);

        return "
            CASE
                WHEN ({$ordenReal}) IS NULL OR ({$ordenCierre}) IS NULL THEN NULL
                WHEN ({$ordenReal}) <= 5 AND ({$ordenCierre}) > ({$ordenReal}) THEN ({$bucketReal})
                ELSE ({$cierreActual})
            END
        ";
    }

    private static function bucketMasUnoSql(string $expresion): string
    {
        return "
            CASE ({$expresion})
                WHEN 'a) Current' THEN 'b) 1 a 7 dias'
                WHEN 'b) 1 a 7 dias' THEN 'c) 8 a 14 dias'
                WHEN 'c) 8 a 14 dias' THEN 'd) 15 a 21 dias'
                WHEN 'd) 15 a 21 dias' THEN 'e) 22 a 30 dias'
                WHEN 'e) 22 a 30 dias' THEN 'f) 31 a 60 dias'
                WHEN 'f) 31 a 60 dias' THEN 'g) 61 a 90 dias'
                WHEN 'g) 61 a 90 dias' THEN 'h) 91 a 120 dias'
                WHEN 'h) 91 a 120 dias' THEN 'i) 121+ dias'
                WHEN 'i) 121+ dias' THEN 'i) 121+ dias'
                ELSE NULL
            END
        ";
    }

    private static function bucketDesdeDiasMoraSql(string $columnaDiasMora): string
    {
        $columnasPermitidas = self::columnasDiasMoraPermitidas();
        if (!in_array($columnaDiasMora, $columnasPermitidas, true)) {
            throw new \InvalidArgumentException('Columna de dias mora no permitida.');
        }

        $diasMoraSql = "CAST(NULLIF(TRIM(CAST(`{$columnaDiasMora}` AS CHAR)), '') AS SIGNED)";

        return "
            CASE
                WHEN ({$diasMoraSql}) IS NULL THEN NULL
                WHEN ({$diasMoraSql}) <= 0 THEN 'a) Current'
                WHEN ({$diasMoraSql}) BETWEEN 1 AND 7 THEN 'b) 1 a 7 dias'
                WHEN ({$diasMoraSql}) BETWEEN 8 AND 14 THEN 'c) 8 a 14 dias'
                WHEN ({$diasMoraSql}) BETWEEN 15 AND 21 THEN 'd) 15 a 21 dias'
                WHEN ({$diasMoraSql}) BETWEEN 22 AND 30 THEN 'e) 22 a 30 dias'
                WHEN ({$diasMoraSql}) BETWEEN 31 AND 60 THEN 'f) 31 a 60 dias'
                WHEN ({$diasMoraSql}) BETWEEN 61 AND 90 THEN 'g) 61 a 90 dias'
                WHEN ({$diasMoraSql}) BETWEEN 91 AND 120 THEN 'h) 91 a 120 dias'
                ELSE 'i) 121+ dias'
            END
        ";
    }

    private static function bucketSql(string $columna): string
    {
        if (!in_array($columna, [
            'Bucket_Morosidad_Real',
            'Cierre_Actual',
            'mas_uno',
            'menos_uno',
            "COALESCE(NULLIF(TRIM(CAST(Bucket_ajustado_ghost AS CHAR)), ''), Cierre_Actual)",
        ], true)) {
            throw new \InvalidArgumentException('Columna no permitida.');
        }

        return "
            CASE TRIM(CAST({$columna} AS CHAR))
                WHEN 'a) Current' THEN 'a) Current'
                WHEN 'b) 1 a 7 dias' THEN 'b) 1 a 7 dias'
                WHEN 'c) 8 a 14 dias' THEN 'c) 8 a 14 dias'
                WHEN 'd) 15 a 21 dias' THEN 'd) 15 a 21 dias'
                WHEN 'e) 22 a 30 dias' THEN 'e) 22 a 30 dias'
                WHEN 'f) 31 a 60 dias' THEN 'f) 31 a 60 dias'
                WHEN 'g) 61 a 90 dias' THEN 'g) 61 a 90 dias'
                WHEN 'h) 91 a 120 dias' THEN 'h) 91 a 120 dias'
                WHEN 'i) 120+ dias' THEN 'i) 121+ dias'
                WHEN 'i) 121+ dias' THEN 'i) 121+ dias'
                ELSE NULL
            END
        ";
    }

    private static function ordenBucketCaseSql(string $expresion): string
    {
        return "
            CASE ({$expresion})
                WHEN 'a) Current' THEN 1
                WHEN 'b) 1 a 7 dias' THEN 2
                WHEN 'c) 8 a 14 dias' THEN 3
                WHEN 'd) 15 a 21 dias' THEN 4
                WHEN 'e) 22 a 30 dias' THEN 5
                WHEN 'f) 31 a 60 dias' THEN 6
                WHEN 'g) 61 a 90 dias' THEN 7
                WHEN 'h) 91 a 120 dias' THEN 8
                WHEN 'i) 121+ dias' THEN 9
                ELSE NULL
            END
        ";
    }

    /**
     * @return array<int,string>
     */
    private static function columnasDiasMoraPorDia(string $corte): array
    {
        $slot = str_replace(':', '_', self::normalizarCorte($corte));

        return [
            1 => 'Dias_mora_Lunes_' . $slot,
            2 => 'Dias_mora_Martes_' . $slot,
            3 => 'Dias_mora_Miercoles_' . $slot,
            4 => 'Dias_mora_Jueves_' . $slot,
            5 => 'Dias_mora_Viernes_' . $slot,
            6 => 'Dias_mora_Sabado_' . $slot,
            7 => 'Dias_mora_Domingo_' . $slot,
        ];
    }

    private static function columnaDiasMoraCorte(string $corte): string
    {
        $hoy = new \DateTimeImmutable('today', new \DateTimeZone('America/Mexico_City'));
        $columnas = self::columnasDiasMoraPorDia($corte);

        return $columnas[(int) $hoy->format('N')];
    }

    /**
     * @return list<string>
     */
    private static function columnasDiasMoraPermitidas(): array
    {
        $permitidas = [];
        foreach (self::CORTES as $corte) {
            foreach (self::columnasDiasMoraPorDia($corte) as $columna) {
                $permitidas[] = $columna;
            }
        }

        return $permitidas;
    }

    private static function diaCorteNombre(): string
    {
        $nombres = [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo',
        ];
        $hoy = new \DateTimeImmutable('today', new \DateTimeZone('America/Mexico_City'));

        return $nombres[(int) $hoy->format('N')];
    }

    /**
     * @return list<string>
     */
    private static function ultimasSemanasHistoricas(DatabaseSegundometro $db, int $limite): array
    {
        $limite = max(1, min(12, $limite));
        $rows = $db->queryAll("
            SELECT DISTINCT SEMANA
            FROM tbl_segundometro_histo
            WHERE SEMANA IS NOT NULL
              AND LENGTH(TRIM(CAST(SEMANA AS CHAR))) > 0
            ORDER BY
                CAST(SUBSTRING_INDEX(TRIM(CAST(SEMANA AS CHAR)), '-', -1) AS UNSIGNED) DESC,
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(TRIM(CAST(SEMANA AS CHAR)), ' ', -1), '-', 1) AS UNSIGNED) DESC
            LIMIT {$limite}
        ");

        $out = [];
        foreach ($rows as $row) {
            $semana = trim((string) ($row['SEMANA'] ?? ''));
            if ($semana !== '' && !in_array($semana, $out, true)) {
                $out[] = $semana;
                if (count($out) >= $limite) {
                    break;
                }
            }
        }

        return $out;
    }

    /**
     * @param list<string> $semanas
     */
    private static function normalizarSemanaHistorica(?string $semana, array $semanas): string
    {
        $valor = trim((string) ($semana ?? ''));
        if ($valor === '') {
            return $semanas[0];
        }

        if (!in_array($valor, $semanas, true)) {
            throw new \InvalidArgumentException('Semana historica no permitida.');
        }

        return $valor;
    }

    private static function normalizarCorte(?string $corte): string
    {
        $valor = trim((string) ($corte ?? ''));
        if ($valor === '') {
            return self::corteAutomatico();
        }

        $valor = str_replace('_', ':', $valor);
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $valor, $m)) {
            $valor = sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        if (!in_array($valor, self::CORTES, true)) {
            throw new \InvalidArgumentException('Corte no permitido: ' . $corte);
        }

        return $valor;
    }

    private static function corteAutomatico(): string
    {
        $ahora = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
        $horaActual = ((int) $ahora->format('H') * 60) + (int) $ahora->format('i');
        $seleccionado = self::CORTES[0];

        foreach (self::CORTES as $corte) {
            [$hora, $minuto] = array_map('intval', explode(':', $corte));
            $minutosCorte = ($hora * 60) + $minuto;
            if ($minutosCorte > $horaActual) {
                break;
            }
            $seleccionado = $corte;
        }

        return $seleccionado;
    }
}
