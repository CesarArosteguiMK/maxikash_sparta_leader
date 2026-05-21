<?php

namespace Models;

use Core\DatabaseSegundometro;

final class ComparativoCierreSemanal
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
    ];

    /**
     * @return array{success:bool,corte:string,dia_corte:string,columna_dias_mora:string,semana_actual:string,semana_pasada:string,advertencias:list<string>,creditos:array<string,mixed>,capital:array<string,mixed>,tarjetas:array<string,mixed>}
     */
    public static function calcular(): array
    {
        $db = new DatabaseSegundometro();
        $semanaActual = self::resolverSemanaActual($db);
        $semanaPasada = self::semanaAnterior($semanaActual);
        $columnaDiasMora = self::columnaDiasMoraCorte();
        $diaCorte = self::diaCorteNombre();

        $actual = self::agregadoPorBucket($db, 'tbl_segundometro_semana', null, $columnaDiasMora);
        $pasada = self::agregadoPorBucket($db, 'tbl_segundometro_histo', $semanaPasada, $columnaDiasMora);
        $advertencias = self::advertenciasFuenteCapital($db, $semanaPasada);

        return [
            'success' => true,
            'corte' => '9:30',
            'dia_corte' => $diaCorte,
            'columna_dias_mora' => $columnaDiasMora,
            'semana_actual' => $semanaActual,
            'semana_pasada' => $semanaPasada,
            'advertencias' => $advertencias,
            'creditos' => [
                'semana_actual' => self::filasMetricas($actual, 'creditos'),
                'semana_pasada' => self::filasMetricas($pasada, 'creditos'),
            ],
            'capital' => [
                'semana_actual' => self::filasMetricas($actual, 'saldo_capital'),
                'semana_pasada' => self::filasMetricas($pasada, 'saldo_capital'),
            ],
            'tarjetas' => [
                'creditos_semana_actual' => self::porcentajeCurrentMasUnoSiete($actual, 'creditos'),
                'creditos_semana_pasada' => self::porcentajeCurrentMasUnoSiete($pasada, 'creditos'),
                'capital_semana_actual' => self::porcentajeCurrentMasUnoSiete($actual, 'saldo_capital'),
                'capital_semana_pasada' => self::porcentajeCurrentMasUnoSiete($pasada, 'saldo_capital'),
            ],
        ];
    }

    private static function resolverSemanaActual(DatabaseSegundometro $db): string
    {
        $row = $db->queryOne(
            "SELECT SEMANA
             FROM `tbl_segundometro_semana`
             WHERE SEMANA IS NOT NULL AND TRIM(SEMANA) <> ''
             GROUP BY SEMANA
             ORDER BY MAX(fecha_hora_insert) DESC
             LIMIT 1"
        );
        $semana = trim((string) ($row['SEMANA'] ?? ''));
        if ($semana !== '') {
            return $semana;
        }

        $tz = new \DateTimeZone('America/Mexico_City');
        $hoy = new \DateTimeImmutable('today', $tz);

        return sprintf('Semana %d-%d', (int) $hoy->format('W'), (int) $hoy->format('o'));
    }

    private static function semanaAnterior(string $semanaActual): string
    {
        if (!preg_match('/Semana\s+(\d{1,2})-(\d{4})/i', $semanaActual, $m)) {
            throw new \RuntimeException('No se pudo interpretar la semana actual: ' . $semanaActual);
        }

        $week = (int) $m[1];
        $year = (int) $m[2];
        $d = (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))
            ->setISODate($year, $week, 1)
            ->modify('-7 days');

        return sprintf('Semana %d-%d', (int) $d->format('W'), (int) $d->format('o'));
    }

    /**
     * @return array<string,array{orden:int,bucket:string,creditos:int,saldo_capital:float}>
     */
    private static function agregadoPorBucket(DatabaseSegundometro $db, string $tabla, ?string $semana, string $columnaDiasMora): array
    {
        if (!in_array($tabla, ['tbl_segundometro_semana', 'tbl_segundometro_histo'], true)) {
            throw new \InvalidArgumentException('Tabla no permitida.');
        }

        $bucketSql = self::bucketDesdeDiasMoraSql($columnaDiasMora);
        $saldoSql = "COALESCE(CAST(REPLACE(REPLACE(NULLIF(TRIM(CAST(Saldo_total_capital AS CHAR)), ''), '$', ''), ',', '') AS DECIMAL(18,2)), 0)";
        $where = $semana === null ? '' : 'WHERE SEMANA = :semana';

        $sql = "
            SELECT x.bucket_ajustado AS bucket,
                   COUNT(*) AS creditos,
                   COALESCE(SUM(x.saldo_capital), 0) AS saldo_capital
            FROM (
                SELECT {$bucketSql} AS bucket_ajustado,
                       {$saldoSql} AS saldo_capital
                FROM `{$tabla}`
                {$where}
            ) x
            WHERE x.bucket_ajustado IN (
                'a) Current',
                'b) 1 a 7 dias',
                'c) 8 a 14 dias',
                'd) 15 a 21 dias',
                'e) 22 a 30 dias',
                'f) 31 a 60 dias',
                'g) 61 a 90 dias',
                'h) 91 a 120 dias'
            )
            GROUP BY x.bucket_ajustado
        ";

        $rows = $db->queryAll($sql, $semana === null ? null : ['semana' => $semana]);

        $out = [];
        foreach (self::BUCKETS as $b) {
            $out[$b['bucket']] = [
                'orden' => $b['orden'],
                'bucket' => $b['bucket'],
                'creditos' => 0,
                'saldo_capital' => 0.0,
            ];
        }

        foreach ($rows as $row) {
            $bucket = (string) ($row['bucket'] ?? '');
            if (!isset($out[$bucket])) {
                continue;
            }
            $out[$bucket]['creditos'] = (int) ($row['creditos'] ?? 0);
            $out[$bucket]['saldo_capital'] = round((float) ($row['saldo_capital'] ?? 0), 2);
        }

        return $out;
    }

    private static function bucketDesdeDiasMoraSql(string $columnaDiasMora): string
    {
        $columnasPermitidas = array_values(self::columnasDiasMoraPorDia());
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

    /**
     * @return array<int,string>
     */
    private static function columnasDiasMoraPorDia(): array
    {
        return [
            1 => 'Dias_mora_Lunes_09_30',
            2 => 'Dias_mora_Martes_09_30',
            3 => 'Dias_mora_Miercoles_09_30',
            4 => 'Dias_mora_Jueves_09_30',
            5 => 'Dias_mora_Viernes_09_30',
            6 => 'Dias_mora_Sabado_09_30',
            7 => 'Dias_mora_Domingo_09_30',
        ];
    }

    private static function columnaDiasMoraCorte(): string
    {
        $hoy = new \DateTimeImmutable('today', new \DateTimeZone('America/Mexico_City'));
        $dia = (int) $hoy->format('N');
        $columnas = self::columnasDiasMoraPorDia();

        return $columnas[$dia];
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
    private static function advertenciasFuenteCapital(DatabaseSegundometro $db, string $semanaPasada): array
    {
        $advertencias = [];
        if (!self::campoCapitalTieneDatos($db, 'tbl_segundometro_semana', null)) {
            $advertencias[] = 'Saldo_total_capital no tiene datos en tbl_segundometro_semana.';
        }
        if (!self::campoCapitalTieneDatos($db, 'tbl_segundometro_histo', $semanaPasada)) {
            $advertencias[] = 'Saldo_total_capital no tiene datos en tbl_segundometro_histo para ' . $semanaPasada . '.';
        }

        return $advertencias;
    }

    private static function campoCapitalTieneDatos(DatabaseSegundometro $db, string $tabla, ?string $semana): bool
    {
        if (!in_array($tabla, ['tbl_segundometro_semana', 'tbl_segundometro_histo'], true)) {
            throw new \InvalidArgumentException('Tabla no permitida.');
        }

        $where = $semana === null ? '' : 'AND SEMANA = :semana';
        $row = $db->queryOne(
            "SELECT COUNT(*) AS total
             FROM `{$tabla}`
             WHERE Saldo_total_capital IS NOT NULL
               AND TRIM(CAST(Saldo_total_capital AS CHAR)) <> ''
               {$where}",
            $semana === null ? null : ['semana' => $semana]
        );

        return (int) ($row['total'] ?? 0) > 0;
    }

    /**
     * @param array<string,array{orden:int,bucket:string,creditos:int,saldo_capital:float}> $agregado
     * @return array{filas:list<array<string,mixed>>,total:float|int}
     */
    private static function filasMetricas(array $agregado, string $campo): array
    {
        $total = 0.0;
        foreach ($agregado as $row) {
            $total += (float) ($row[$campo] ?? 0);
        }

        $filas = [];
        foreach ($agregado as $row) {
            $valor = $campo === 'creditos' ? (int) $row[$campo] : round((float) $row[$campo], 2);
            $filas[] = [
                'orden' => (int) $row['orden'],
                'bucket' => (string) $row['bucket'],
                'valor' => $valor,
                'porcentaje' => $total > 0 ? round(($valor / $total) * 100, 2) : 0.0,
            ];
        }

        return [
            'filas' => $filas,
            'total' => $campo === 'creditos' ? (int) $total : round($total, 2),
        ];
    }

    /**
     * @param array<string,array{orden:int,bucket:string,creditos:int,saldo_capital:float}> $agregado
     */
    private static function porcentajeCurrentMasUnoSiete(array $agregado, string $campo): float
    {
        $total = 0.0;
        $base = 0.0;
        foreach ($agregado as $row) {
            $valor = (float) ($row[$campo] ?? 0);
            $total += $valor;
            if (($row['orden'] ?? 0) === 1 || ($row['orden'] ?? 0) === 2) {
                $base += $valor;
            }
        }

        return $total > 0 ? round(($base / $total) * 100, 2) : 0.0;
    }
}
