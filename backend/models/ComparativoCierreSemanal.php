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
     * @return array{success:bool,corte:string,corte_opciones:list<string>,dia_corte:string,semana_actual:string,semana_pasada:string,origen_semana_pasada:string,advertencias:list<string>,creditos:array<string,mixed>,capital:array<string,mixed>,tarjetas:array<string,mixed>}
     */
    public static function calcular(?string $corte = null, ?string $modoConciliacion = null): array
    {
        $db = new DatabaseSegundometro();
        $corteNormalizado = self::normalizarCorte($corte);
        $modoSolicitado = self::normalizarModoConciliacion($modoConciliacion);
        $semanaActual = self::resolverSemanaActual($db);
        $semanaPasada = self::semanaAnterior($semanaActual);
        $columnaDiasMora = self::columnaDiasMoraCorte($corteNormalizado);
        $diaCorte = self::diaCorteNombre();
        $conciliacionDisponible = self::semanaActualTieneConciliacion($db);
        $usarConciliacion = $modoSolicitado === 'con' && $conciliacionDisponible;

        $advertencias = [];
        if ($modoSolicitado === 'con' && !$conciliacionDisponible) {
            $advertencias[] = 'La semana actual aun no tiene datos en Ghost o Variable_8; se muestra sin conciliacion.';
        }

        $modoBucket = $usarConciliacion ? 'conciliado' : 'historico';
        $actual = self::agregadoPorBucket($db, 'tbl_segundometro_semana', null, $modoBucket, $columnaDiasMora);
        try {
            self::prepararConexionVistaUltimasSemanas($db);
            $pasada = self::agregadoPorBucket($db, 'vista_ultimas_semanas', $semanaPasada, $modoBucket, $columnaDiasMora);
            $origenSemanaPasada = 'vista_ultimas_semanas';
        } catch (\Throwable $e) {
            $pasada = self::agregadoPorBucket($db, 'tbl_segundometro_histo', $semanaPasada, $modoBucket, $columnaDiasMora);
            $origenSemanaPasada = 'tbl_segundometro_histo';
            $advertencias[] = 'vista_ultimas_semanas no respondio; se uso tbl_segundometro_histo para ' . $semanaPasada . '.';
            error_log('ComparativoCierreSemanal::vista_ultimas_semanas -> ' . $e->getMessage());
        }
        $advertencias = array_merge($advertencias, self::advertenciasFuenteCapital($db, $semanaPasada));

        return [
            'success' => true,
            'corte' => $corteNormalizado,
            'corte_opciones' => self::CORTES,
            'dia_corte' => $diaCorte,
            'semana_actual' => $semanaActual,
            'semana_pasada' => $semanaPasada,
            'origen_semana_pasada' => $origenSemanaPasada,
            'advertencias' => $advertencias,
            'modo_conciliacion' => $usarConciliacion ? 'con' : 'sin',
            'modo_conciliacion_solicitado' => $modoSolicitado,
            'conciliacion_disponible' => $conciliacionDisponible,
            'conciliacion_activa' => $usarConciliacion,
            'etiqueta_conciliacion' => $usarConciliacion
                ? 'Corte calculado con conciliación'
                : 'Corte calculado sin conciliación',
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

    private static function normalizarModoConciliacion(?string $modo): string
    {
        $valor = strtolower(trim((string) $modo));
        if (in_array($valor, ['con', 'conciliacion', 'conciliado', 'con_conciliacion'], true)) {
            return 'con';
        }

        return 'sin';
    }

    private static function semanaActualTieneConciliacion(DatabaseSegundometro $db): bool
    {
        $row = $db->queryOne(
            "SELECT COUNT(*) AS total
             FROM `tbl_segundometro_semana`
             WHERE (Variable_8 IS NOT NULL AND TRIM(CAST(Variable_8 AS CHAR)) <> '')
                OR (Ghost IS NOT NULL AND TRIM(CAST(Ghost AS CHAR)) <> '' AND TRIM(CAST(Ghost AS CHAR)) <> '-')"
        );

        return (int) ($row['total'] ?? 0) > 0;
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
    private static function agregadoPorBucket(DatabaseSegundometro $db, string $tabla, ?string $semana, string $modoBucket, ?string $columnaDiasMora = null): array
    {
        if (!in_array($tabla, ['tbl_segundometro_semana', 'tbl_segundometro_histo', 'vista_ultimas_semanas'], true)) {
            throw new \InvalidArgumentException('Tabla no permitida.');
        }

        if ($semana !== null) {
            $bucketSql = self::bucketCierreHistoricoSql();
        } else {
            $bucketSql = $modoBucket === 'conciliado'
                ? self::bucketConciliadoSql((string) $columnaDiasMora)
                : self::bucketHistoricoSql((string) $columnaDiasMora);
        }
        $saldoSql = "COALESCE(CAST(REPLACE(REPLACE(NULLIF(TRIM(CAST(Saldo_total_capital AS CHAR)), ''), '$', ''), ',', '') AS DECIMAL(18,2)), 0)";
        $where = $semana === null ? '' : 'WHERE SEMANA = :semana';

        $sql = "
            SELECT x.bucket_ajustado AS bucket,
                   COUNT(DISTINCT x.id_credito) AS creditos,
                   COALESCE(SUM(x.saldo_capital), 0) AS saldo_capital
            FROM (
                SELECT raw.id_credito,
                       raw.bucket_ajustado,
                       MAX(raw.saldo_capital) AS saldo_capital
                FROM (
                    SELECT Id_credito AS id_credito,
                           {$bucketSql} AS bucket_ajustado,
                           {$saldoSql} AS saldo_capital
                    FROM `{$tabla}`
                    {$where}
                ) raw
                WHERE raw.id_credito IS NOT NULL
                GROUP BY raw.id_credito, raw.bucket_ajustado
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

    private static function prepararConexionVistaUltimasSemanas(DatabaseSegundometro $db): void
    {
        $db->CRUD('SET collation_connection = utf8mb4_0900_ai_ci');
    }

    private static function bucketHistoricoSql(string $columnaDiasMora): string
    {
        $columnasPermitidas = self::columnasDiasMoraPermitidas();
        if (!in_array($columnaDiasMora, $columnasPermitidas, true)) {
            throw new \InvalidArgumentException('Columna de dias mora no permitida.');
        }

        $bucketDia = self::bucketDesdeDiasMoraSql($columnaDiasMora);
        $ordenDia = self::ordenBucketCaseSql("({$bucketDia})");
        $ordenReal = self::ordenBucketSql('Bucket_Morosidad_Real');

        return "
            CASE
                WHEN ({$ordenReal}) IS NOT NULL
                     AND ({$ordenDia}) IS NOT NULL
                     AND ({$ordenReal}) < ({$ordenDia})
                THEN Bucket_Morosidad_Real
                ELSE ({$bucketDia})
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

    private static function bucketConciliadoSql(string $columnaDiasMora): string
    {
        $bucketBase = self::bucketHistoricoSql($columnaDiasMora);

        return "
            CASE
                WHEN Variable_8 IS NOT NULL AND TRIM(CAST(Variable_8 AS CHAR)) <> '' THEN 'a) Current'
                WHEN Ghost IS NOT NULL AND TRIM(CAST(Ghost AS CHAR)) <> '' AND TRIM(CAST(Ghost AS CHAR)) <> '-' THEN 'a) Current'
                ELSE ({$bucketBase})
            END
        ";
    }

    private static function bucketAjustadoSql(): string
    {
        $ordenReal = self::ordenBucketSql('Bucket_Morosidad_Real');
        $ordenCierre = self::ordenBucketSql('Cierre_Actual');

        return "
            CASE
                WHEN Variable_8 IS NOT NULL AND TRIM(CAST(Variable_8 AS CHAR)) <> '' THEN 'a) Current'
                WHEN Ghost IS NOT NULL AND TRIM(CAST(Ghost AS CHAR)) <> '' AND TRIM(CAST(Ghost AS CHAR)) <> '-' THEN 'a) Current'
                WHEN ({$ordenReal}) IS NULL OR ({$ordenCierre}) IS NULL THEN NULL
                WHEN ({$ordenCierre}) > ({$ordenReal}) THEN Bucket_Morosidad_Real
                ELSE Cierre_Actual
            END
        ";
    }

    private static function bucketCierreHistoricoSql(): string
    {
        $bucketReal = self::normalizarBucketSql('Bucket_Morosidad_Real');
        $cierreBase = self::normalizarBucketSql("COALESCE(NULLIF(TRIM(CAST(Bucket_ajustado_ghost AS CHAR)), ''), Cierre_Actual)");
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
                WHEN ({$ordenCierre}) > ({$ordenReal}) THEN ({$bucketReal})
                ELSE ({$cierreActual})
            END
        ";
    }

    private static function bucketCierreActualSql(): string
    {
        $ordenReal = self::ordenBucketSql('Bucket_Morosidad_Real');
        $ordenCierre = self::ordenBucketSql('Cierre_Actual');

        return "
            CASE
                WHEN ({$ordenReal}) IS NULL OR ({$ordenCierre}) IS NULL THEN NULL
                WHEN ({$ordenCierre}) > ({$ordenReal}) THEN Bucket_Morosidad_Real
                ELSE Cierre_Actual
            END
        ";
    }

    private static function ordenBucketSql(string $columna): string
    {
        if (!in_array($columna, ['Bucket_Morosidad_Real', 'Cierre_Actual'], true)) {
            throw new \InvalidArgumentException('Columna no permitida.');
        }

        return self::ordenBucketCaseSql($columna);
    }

    private static function normalizarBucketSql(string $expresion): string
    {
        return "
            CASE TRIM(CAST({$expresion} AS CHAR))
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
            CASE {$expresion}
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
    private static function columnasDiasMoraPorDia(string $corte = '14:30'): array
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
        $dia = (int) $hoy->format('N');
        $columnas = self::columnasDiasMoraPorDia($corte);

        return $columnas[$dia];
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
