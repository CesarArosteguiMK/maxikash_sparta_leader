<?php

namespace Models;

use Core\DatabaseSegundometro;

/**
 * Reconciles the different Bucket views without modifying operational data.
 *
 * Historico uses the consolidated weekly closure, while Comparativo rebuilds
 * the selected intraday cut. Keeping both calculations here makes every
 * explanation traceable to the same rules used by their respective screens.
 */
final class DiagnosticoBucket
{
    /** @var list<string> */
    private const CORTES = [
        '07:30', '09:30', '11:30', '13:30', '14:30',
        '16:30', '18:30', '20:30', '23:50',
    ];

    /** @var array<string,string> */
    private const BUCKETS = [
        'a) Current' => 'Current',
        'b) 1 a 7 dias' => '1-7',
        'c) 8 a 14 dias' => '8-14',
        'd) 15 a 21 dias' => '15-21',
        'e) 22 a 30 dias' => '22-30',
        'f) 31 a 60 dias' => '31-60',
        'g) 61 a 90 dias' => '61-90',
        'h) 91 a 120 dias' => '91-120',
        'i) 121+ dias' => '121+',
    ];

    /** @return array<string,mixed> */
    public static function analizar(array $criterios): array
    {
        $db = new DatabaseSegundometro();
        $corte = self::normalizarCorte(isset($criterios['corte']) ? (string) $criterios['corte'] : null);
        $semana = self::resolverSemana($db, isset($criterios['semana']) ? (string) $criterios['semana'] : null);
        $credito = (int) ($criterios['id_credito'] ?? 0);

        if ($credito > 0) {
            return self::analizarCredito($db, $credito, $semana, $corte);
        }

        return self::analizarSemana(
            $db,
            $semana,
            $corte,
            isset($criterios['bucket']) ? (string) $criterios['bucket'] : null
        );
    }

    /** @return array<string,mixed> */
    private static function analizarCredito(
        DatabaseSegundometro $db,
        int $credito,
        ?string $semana,
        string $corte
    ): array {
        $semanaActual = self::semanaActual($db);
        $fila = null;
        $tabla = '';

        if ($semana === null || $semana === $semanaActual) {
            $fila = self::consultarCredito($db, 'tbl_segundometro_semana', $credito, $semanaActual, $corte);
            if ($fila !== null) {
                $tabla = 'tbl_segundometro_semana';
            }
        }

        if ($fila === null) {
            $semanas = $semana !== null ? [$semana] : self::ultimasSemanas($db, 24);
            if ($semanas !== []) {
                $fila = self::consultarCredito($db, 'tbl_segundometro_histo', $credito, $semanas, $corte);
                if ($fila !== null) {
                    $tabla = 'tbl_segundometro_histo';
                }
            }
        }

        if ($fila === null) {
            return [
                'success' => true,
                'modo' => 'credito',
                'id_credito' => $credito,
                'encontrado' => false,
                'semana_solicitada' => $semana,
                'corte' => $corte,
                'fuentes_revisadas' => ['tbl_segundometro_semana', 'tbl_segundometro_histo'],
            ];
        }

        $semanaEncontrada = trim((string) ($fila['semana'] ?? ''));
        $esActual = $tabla === 'tbl_segundometro_semana';
        $razones = [];
        if (trim((string) ($fila['variable_8'] ?? '')) !== '') {
            $razones[] = 'Variable_8 tiene valor y la vista conciliada fuerza el credito a Current.';
        }
        $ghost = trim((string) ($fila['ghost'] ?? ''));
        if ($ghost !== '' && $ghost !== '-') {
            $razones[] = 'Ghost tiene valor y la vista conciliada fuerza el credito a Current.';
        }
        if (($fila['bucket_historico'] ?? null) !== ($fila['bucket_comparativo'] ?? null)) {
            $razones[] = 'Historico usa el cierre semanal consolidado; Comparativo recalcula el corte intradia seleccionado.';
        }
        if (($fila['bucket_real'] ?? null) !== ($fila['bucket_dias_mora'] ?? null)) {
            $razones[] = 'El bucket real y los dias de mora del corte no coinciden; la regla conserva el rango menos moroso para no degradar artificialmente el credito.';
        }

        return [
            'success' => true,
            'modo' => 'credito',
            'id_credito' => $credito,
            'encontrado' => true,
            'en_semana_actual' => $esActual,
            'cliente' => trim((string) ($fila['cliente'] ?? '')),
            'semana' => $semanaEncontrada,
            'corte' => $corte,
            'dia_corte' => self::diaCorteNombre(),
            'columna_corte' => self::columnaDiasMoraCorte($corte),
            'fuente' => $tabla,
            'fecha_hora_fuente' => trim((string) ($fila['fecha_hora_fuente'] ?? '')),
            'consultado_at' => (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s'),
            'antiguedad_minutos' => self::antiguedadMinutos($fila['fecha_hora_fuente'] ?? null),
            'dias_mora_corte' => isset($fila['dias_mora_corte']) && $fila['dias_mora_corte'] !== null
                ? (int) $fila['dias_mora_corte']
                : null,
            'bucket_real' => self::etiqueta($fila['bucket_real'] ?? null),
            'bucket_nacimiento' => self::etiqueta($fila['bucket_real'] ?? null),
            'bucket_dias_mora' => self::etiqueta($fila['bucket_dias_mora'] ?? null),
            'bucket_segundometro' => self::etiqueta($fila['bucket_avance'] ?? null),
            'bucket_actual' => self::etiqueta($fila['bucket_avance'] ?? null),
            'bucket_historico' => self::etiqueta($fila['bucket_historico'] ?? null),
            'bucket_cierre_ajustado' => self::etiqueta($fila['bucket_historico'] ?? null),
            'bucket_comparativo' => self::etiqueta($fila['bucket_comparativo'] ?? null),
            'bucket_comparativo_conciliado' => self::etiqueta($fila['bucket_conciliado'] ?? null),
            'cierre_actual' => self::etiqueta($fila['cierre_actual'] ?? null),
            'bucket_ajustado_ghost' => self::etiqueta($fila['bucket_ajustado_ghost'] ?? null),
            'variable_8' => trim((string) ($fila['variable_8'] ?? '')),
            'ghost' => trim((string) ($fila['ghost'] ?? '')),
            'vistas' => self::construirVistasCredito($fila, $tabla, $semanaEncontrada, $corte),
            'razones' => $razones,
        ];
    }

    /** @return array<string,mixed>|null */
    private static function consultarCredito(
        DatabaseSegundometro $db,
        string $tabla,
        int $credito,
        string|array $semana,
        string $corte
    ): ?array {
        if (!in_array($tabla, ['tbl_segundometro_semana', 'tbl_segundometro_histo'], true)) {
            throw new \InvalidArgumentException('Fuente de Bucket no permitida.');
        }

        $columna = self::columnaDiasMoraCorte($corte);
        $bucketReal = self::normalizarBucketSql('Bucket_Morosidad_Real');
        $bucketDias = self::bucketDesdeDiasMoraSql($columna);
        $bucketAvance = self::bucketAvanceActualSql($columna);
        $bucketHistorico = self::bucketCierreHistoricoSql();
        $bucketComparativo = self::bucketComparativoSql($columna);
        $bucketConciliado = self::bucketComparativoConciliadoSql($columna);
        $parametros = ['credito' => $credito];
        if (is_array($semana)) {
            $placeholders = [];
            foreach (array_values($semana) as $indice => $etiquetaSemana) {
                $clave = 'semana_' . $indice;
                $placeholders[] = ':' . $clave;
                $parametros[$clave] = (string) $etiquetaSemana;
            }
            if ($placeholders === []) {
                return null;
            }
            $filtroSemana = 'SEMANA IN (' . implode(', ', $placeholders) . ')';
            $ordenSemana = "CAST(SUBSTRING_INDEX(SEMANA, '-', -1) AS UNSIGNED) DESC,
                            CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(SEMANA, ' ', -1), '-', 1) AS UNSIGNED) DESC,";
        } else {
            $filtroSemana = 'SEMANA = :semana';
            $parametros['semana'] = $semana;
            $ordenSemana = '';
        }

        return $db->queryOne("
            SELECT Id_credito AS id_credito,
                   Nombre_cliente AS cliente,
                   SEMANA AS semana,
                   `{$columna}` AS dias_mora_corte,
                   {$bucketReal} AS bucket_real,
                   {$bucketDias} AS bucket_dias_mora,
                   {$bucketAvance} AS bucket_avance,
                   {$bucketHistorico} AS bucket_historico,
                   {$bucketComparativo} AS bucket_comparativo,
                   {$bucketConciliado} AS bucket_conciliado,
                   Cierre_Actual AS cierre_actual,
                   Bucket_ajustado_ghost AS bucket_ajustado_ghost,
                   Variable_8 AS variable_8,
                   Ghost AS ghost,
                   fecha_hora_insert AS fecha_hora_fuente
            FROM `{$tabla}`
            WHERE {$filtroSemana}
              AND Id_credito = :credito
            ORDER BY {$ordenSemana} fecha_hora_insert DESC
            LIMIT 1
        ", $parametros);
    }

    /** @return array<string,mixed> */
    private static function analizarSemana(
        DatabaseSegundometro $db,
        ?string $semana,
        string $corte,
        ?string $bucketSolicitado
    ): array {
        $semana = $semana ?? self::semanaActual($db);
        $semanaActual = self::semanaActual($db);
        $fuenteComparativo = $semana === $semanaActual ? 'tbl_segundometro_semana' : 'vista_ultimas_semanas';
        $historicoDisponible = self::semanaTieneDatos($db, 'tbl_segundometro_histo', $semana);

        $historico = self::agruparSemana($db, 'tbl_segundometro_histo', $semana, $corte, 'historico');
        try {
            if ($fuenteComparativo === 'vista_ultimas_semanas') {
                $db->CRUD('SET collation_connection = utf8mb4_0900_ai_ci');
            }
            $comparativo = self::agruparSemana($db, $fuenteComparativo, $semana, $corte, 'comparativo');
        } catch (\Throwable $e) {
            if ($fuenteComparativo !== 'vista_ultimas_semanas') {
                throw $e;
            }
            error_log('DiagnosticoBucket vista_ultimas_semanas: ' . $e->getMessage());
            $fuenteComparativo = 'tbl_segundometro_histo';
            $comparativo = self::agruparSemana($db, $fuenteComparativo, $semana, $corte, 'comparativo');
        }

        $bucketClave = self::normalizarBucketSolicitado($bucketSolicitado);
        $transiciones = $historicoDisponible ? self::transicionesSemana($db, $semana, $corte) : [];
        $detalleDiferencias = $historicoDisponible
            ? self::detallesDiferenciasSemana($db, $semana, $corte, $bucketClave)
            : self::detalleDiferenciasVacio();
        $bucketDetalle = null;
        if ($bucketClave !== null) {
            $bucketDetalle = [
                'solicitado' => $bucketClave,
                'historico' => $historicoDisponible
                    ? self::valorBucket($historico['buckets'], $bucketClave)
                    : null,
                'comparativo' => self::valorBucket($comparativo['buckets'], $bucketClave),
            ];
            $bucketDetalle['diferencia'] = $historicoDisponible
                ? $bucketDetalle['comparativo'] - $bucketDetalle['historico']
                : null;
            $netoDetalle = (int) ($detalleDiferencias['resumen']['neto'] ?? 0);
            $bucketDetalle['detalle_cuadra'] = $historicoDisponible
                && $netoDetalle === (int) $bucketDetalle['diferencia'];
            $bucketDetalle['diferencia_no_explicada'] = $historicoDisponible
                ? (int) $bucketDetalle['diferencia'] - $netoDetalle
                : null;
        }

        return [
            'success' => true,
            'modo' => 'semana',
            'semana' => $semana,
            'corte' => $corte,
            'dia_corte' => self::diaCorteNombre(),
            'columna_corte' => self::columnaDiasMoraCorte($corte),
            'comparacion_disponible' => $historicoDisponible,
            'estado_semana' => $historicoDisponible ? 'cerrada' : 'abierta_sin_cierre_historico',
            'advertencia' => $historicoDisponible
                ? null
                : 'La semana operativa aun no tiene cierre en Historico. Se muestra Comparativo, pero no se calcula una diferencia contra cero.',
            'historico' => $historico + ['fuente' => 'tbl_segundometro_histo'],
            'comparativo' => $comparativo + ['fuente' => $fuenteComparativo],
            'diferencia_total_pantallas' => $historicoDisponible
                ? $comparativo['total_visible'] - $historico['total']
                : null,
            'diferencia_total_comparable' => $historicoDisponible
                ? $comparativo['total_visible'] - $historico['total_comparable']
                : null,
            'bucket_solicitado' => $bucketDetalle,
            'transiciones' => $transiciones,
            'creditos_diferencia' => $detalleDiferencias['creditos'],
            'resumen_creditos_diferencia' => $detalleDiferencias['resumen'],
            'detalle_creditos_truncado' => $detalleDiferencias['truncado'],
            'reglas' => [
                'Historico usa Bucket_ajustado_ghost o Cierre_Actual y aplica Ghost/Variable_8 sobre el cierre consolidado.',
                'Comparativo reconstruye el bucket con los dias de mora del dia y corte seleccionados.',
                'Comparativo visible incluye Current hasta 91-120; no muestra 121+ en su total.',
                '8-30 no es un bucket nativo: agrupa 8-14, 15-21 y 22-30.',
            ],
        ];
    }

    /** @return array{buckets:array<string,int>,total:int,total_visible:int,total_comparable:int,total_121:int} */
    private static function agruparSemana(
        DatabaseSegundometro $db,
        string $tabla,
        string $semana,
        string $corte,
        string $modo
    ): array {
        if (!in_array($tabla, ['tbl_segundometro_semana', 'tbl_segundometro_histo', 'vista_ultimas_semanas'], true)) {
            throw new \InvalidArgumentException('Fuente semanal no permitida.');
        }
        $bucketInicio = self::normalizarBucketSql('Bucket_Morosidad_Real');
        $bucket = $modo === 'historico'
            ? self::bucketCierreHistoricoSql()
            : self::bucketComparativoSql(self::columnaDiasMoraCorte($corte));

        $filtroInicio = $modo === 'historico' ? 'AND bucket_inicio IS NOT NULL' : '';
        $rows = $db->queryAll("
            SELECT bucket, COUNT(DISTINCT id_credito) AS creditos
            FROM (
                SELECT Id_credito AS id_credito,
                       {$bucketInicio} AS bucket_inicio,
                       {$bucket} AS bucket
                FROM `{$tabla}`
                WHERE SEMANA = :semana
            ) x
            WHERE id_credito IS NOT NULL
              AND bucket IS NOT NULL
              {$filtroInicio}
            GROUP BY bucket
        ", ['semana' => $semana]);

        $buckets = array_fill_keys(array_keys(self::BUCKETS), 0);
        foreach ($rows as $row) {
            $clave = (string) ($row['bucket'] ?? '');
            if (array_key_exists($clave, $buckets)) {
                $buckets[$clave] = (int) ($row['creditos'] ?? 0);
            }
        }
        $total = array_sum($buckets);
        $total121 = $buckets['i) 121+ dias'];

        return [
            'buckets' => $buckets,
            'total' => $total,
            'total_visible' => $modo === 'comparativo' ? $total - $total121 : $total,
            'total_comparable' => $total - $total121,
            'total_121' => $total121,
        ];
    }

    /** @return list<array{historico:string,comparativo:string,creditos:int}> */
    private static function transicionesSemana(DatabaseSegundometro $db, string $semana, string $corte): array
    {
        $bucketHistorico = self::bucketCierreHistoricoSql();
        $bucketComparativo = self::bucketComparativoSql(self::columnaDiasMoraCorte($corte));
        $rows = $db->queryAll("
            SELECT bucket_historico, bucket_comparativo, COUNT(DISTINCT id_credito) AS creditos
            FROM (
                SELECT Id_credito AS id_credito,
                       {$bucketHistorico} AS bucket_historico,
                       {$bucketComparativo} AS bucket_comparativo
                FROM tbl_segundometro_histo
                WHERE SEMANA = :semana
            ) x
            WHERE id_credito IS NOT NULL
              AND bucket_historico IS NOT NULL
              AND bucket_comparativo IS NOT NULL
              AND bucket_historico <> bucket_comparativo
            GROUP BY bucket_historico, bucket_comparativo
            ORDER BY creditos DESC
            LIMIT 8
        ", ['semana' => $semana]);

        return array_map(static fn(array $row): array => [
            'historico' => self::etiqueta($row['bucket_historico'] ?? null) ?? 'Sin bucket',
            'comparativo' => self::etiqueta($row['bucket_comparativo'] ?? null) ?? 'Sin bucket',
            'creditos' => (int) ($row['creditos'] ?? 0),
        ], $rows);
    }

    /**
     * Returns the concrete credits behind a weekly difference. The net
     * difference can be smaller than the affected population when credits
     * enter and leave the requested bucket at the same time.
     *
     * @return array{
     *   creditos:list<array<string,mixed>>,
     *   resumen:array{afectados:int,entran:int,salen:int,reclasificados:int,neto:int},
     *   truncado:bool
     * }
     */
    private static function detallesDiferenciasSemana(
        DatabaseSegundometro $db,
        string $semana,
        string $corte,
        ?string $bucketSolicitado
    ): array {
        $columna = self::columnaDiasMoraCorte($corte);
        $bucketReal = self::normalizarBucketSql('Bucket_Morosidad_Real');
        $bucketDias = self::bucketDesdeDiasMoraSql($columna);
        $bucketHistorico = self::bucketCierreHistoricoSql();
        $bucketComparativo = self::bucketComparativoSql($columna);
        $limite = 2000;

        $rows = $db->queryAll("
            SELECT id_credito,
                   cliente,
                   dias_mora_corte,
                   bucket_real,
                   bucket_dias_mora,
                   bucket_historico,
                   bucket_comparativo,
                   cierre_actual,
                   bucket_ajustado_ghost,
                   variable_8,
                   ghost,
                   fecha_hora_fuente
            FROM (
                SELECT Id_credito AS id_credito,
                       Nombre_cliente AS cliente,
                       `{$columna}` AS dias_mora_corte,
                       {$bucketReal} AS bucket_real,
                       {$bucketDias} AS bucket_dias_mora,
                       {$bucketHistorico} AS bucket_historico,
                       {$bucketComparativo} AS bucket_comparativo,
                       Cierre_Actual AS cierre_actual,
                       Bucket_ajustado_ghost AS bucket_ajustado_ghost,
                       Variable_8 AS variable_8,
                       Ghost AS ghost,
                       fecha_hora_insert AS fecha_hora_fuente,
                       ROW_NUMBER() OVER (
                           PARTITION BY Id_credito
                           ORDER BY fecha_hora_insert DESC
                       ) AS numero_fila
                FROM tbl_segundometro_histo
                WHERE SEMANA = :semana
                  AND Id_credito IS NOT NULL
            ) x
            WHERE numero_fila = 1
              AND bucket_historico IS NOT NULL
              AND bucket_comparativo IS NOT NULL
              AND bucket_historico <> bucket_comparativo
            ORDER BY id_credito
        ", ['semana' => $semana]);

        $creditos = [];
        $entran = 0;
        $salen = 0;
        $reclasificados = 0;
        foreach ($rows as $row) {
            $historico = self::etiqueta($row['bucket_historico'] ?? null) ?? 'Sin bucket';
            $comparativo = self::etiqueta($row['bucket_comparativo'] ?? null) ?? 'Sin bucket';
            $coincideHistorico = self::bucketCoincide($historico, $bucketSolicitado);
            $coincideComparativo = self::bucketCoincide($comparativo, $bucketSolicitado);

            if ($bucketSolicitado !== null && !$coincideHistorico && !$coincideComparativo) {
                continue;
            }

            $movimiento = 'reclasificado';
            if ($bucketSolicitado !== null && !$coincideHistorico && $coincideComparativo) {
                $movimiento = 'entra';
                $entran++;
            } elseif ($bucketSolicitado !== null && $coincideHistorico && !$coincideComparativo) {
                $movimiento = 'sale';
                $salen++;
            } else {
                $reclasificados++;
            }

            $creditos[] = [
                'id_credito' => (int) ($row['id_credito'] ?? 0),
                'cliente' => trim((string) ($row['cliente'] ?? '')),
                'movimiento' => $movimiento,
                'bucket_historico' => $historico,
                'bucket_comparativo' => $comparativo,
                'bucket_nacimiento' => self::etiqueta($row['bucket_real'] ?? null),
                'bucket_por_mora' => self::etiqueta($row['bucket_dias_mora'] ?? null),
                'dias_mora_corte' => is_numeric($row['dias_mora_corte'] ?? null)
                    ? (int) $row['dias_mora_corte']
                    : null,
                'cierre_actual' => self::etiqueta($row['cierre_actual'] ?? null),
                'bucket_ajustado_ghost' => self::etiqueta($row['bucket_ajustado_ghost'] ?? null),
                'variable_8' => trim((string) ($row['variable_8'] ?? '')),
                'ghost' => trim((string) ($row['ghost'] ?? '')),
                'fecha_hora_fuente' => trim((string) ($row['fecha_hora_fuente'] ?? '')),
                'motivo' => self::motivoDiferenciaCredito($row, $historico, $comparativo, $corte),
                'formula_historico' => 'Cierre ajustado = Bucket_ajustado_ghost o Cierre_Actual; Ghost/Variable_8 fuerzan Current; nunca empeora frente al bucket de nacimiento.',
                'formula_comparativo' => 'Bucket del corte = rango de Dias_mora_' . self::diaCorteNombre() . '_' . str_replace(':', '_', $corte) . '; nunca empeora frente al bucket de nacimiento.',
            ];
        }

        $totalAfectados = count($creditos);
        $truncado = $totalAfectados > $limite;
        if ($truncado) {
            $creditos = array_slice($creditos, 0, $limite);
        }

        return [
            'creditos' => $creditos,
            'resumen' => [
                'afectados' => $totalAfectados,
                'entran' => $entran,
                'salen' => $salen,
                'reclasificados' => $reclasificados,
                'neto' => $bucketSolicitado !== null ? $entran - $salen : 0,
            ],
            'truncado' => $truncado,
        ];
    }

    /** @return array{creditos:array<never>,resumen:array{afectados:int,entran:int,salen:int,reclasificados:int,neto:int},truncado:bool} */
    private static function detalleDiferenciasVacio(): array
    {
        return [
            'creditos' => [],
            'resumen' => [
                'afectados' => 0,
                'entran' => 0,
                'salen' => 0,
                'reclasificados' => 0,
                'neto' => 0,
            ],
            'truncado' => false,
        ];
    }

    private static function semanaTieneDatos(DatabaseSegundometro $db, string $tabla, string $semana): bool
    {
        if (!in_array($tabla, ['tbl_segundometro_semana', 'tbl_segundometro_histo'], true)) {
            throw new \InvalidArgumentException('Fuente semanal no permitida.');
        }
        $row = $db->queryOne("SELECT 1 AS existe FROM `{$tabla}` WHERE SEMANA = :semana LIMIT 1", [
            'semana' => $semana,
        ]);
        return (int) ($row['existe'] ?? 0) === 1;
    }

    private static function bucketCoincide(string $bucket, ?string $solicitado): bool
    {
        if ($solicitado === null) {
            return false;
        }
        if ($solicitado === '8-30') {
            return in_array($bucket, ['8-14', '15-21', '22-30'], true);
        }
        return $bucket === $solicitado;
    }

    /** @param array<string,mixed> $row */
    private static function motivoDiferenciaCredito(array $row, string $historico, string $comparativo, string $corte): string
    {
        $variable8 = trim((string) ($row['variable_8'] ?? ''));
        if ($variable8 !== '') {
            return 'Variable_8 tiene valor y el cierre historico lo concilia como Current; el comparativo sin conciliacion recalcula el corte ' . $corte . '.';
        }
        $ghost = trim((string) ($row['ghost'] ?? ''));
        if ($ghost !== '' && $ghost !== '-') {
            return 'Ghost tiene valor y el cierre historico lo concilia como Current; el comparativo sin conciliacion recalcula el corte ' . $corte . '.';
        }

        $dias = is_numeric($row['dias_mora_corte'] ?? null) ? (int) $row['dias_mora_corte'] : null;
        $cierre = self::etiqueta($row['bucket_ajustado_ghost'] ?? null)
            ?? self::etiqueta($row['cierre_actual'] ?? null)
            ?? $historico;
        $mora = $dias === null ? 'sin dias de mora informados' : $dias . ' dia(s) de mora';
        return 'Historico conserva el cierre semanal ' . $cierre . '; Comparativo usa ' . $mora
            . ' al corte ' . $corte . ' y por eso lo clasifica en ' . $comparativo . '.';
    }

    private static function valorBucket(array $buckets, string $bucket): int
    {
        if ($bucket === '8-30') {
            return (int) ($buckets['c) 8 a 14 dias'] ?? 0)
                + (int) ($buckets['d) 15 a 21 dias'] ?? 0)
                + (int) ($buckets['e) 22 a 30 dias'] ?? 0);
        }
        foreach (self::BUCKETS as $clave => $etiqueta) {
            if ($etiqueta === $bucket) {
                return (int) ($buckets[$clave] ?? 0);
            }
        }
        return 0;
    }

    private static function normalizarBucketSolicitado(?string $bucket): ?string
    {
        $valor = trim((string) $bucket);
        return in_array($valor, array_merge(array_values(self::BUCKETS), ['8-30']), true) ? $valor : null;
    }

    private static function resolverSemana(DatabaseSegundometro $db, ?string $semana): ?string
    {
        $valor = trim((string) $semana);
        if ($valor === '') {
            return null;
        }
        if (preg_match('/^Semana\s+(\d{1,2})-(\d{4})$/i', $valor, $m)) {
            return 'Semana ' . (int) $m[1] . '-' . (int) $m[2];
        }
        if (!preg_match('/^(?:Semana\s+)?(\d{1,2})$/i', $valor, $m)) {
            throw new \InvalidArgumentException('No pude interpretar la semana solicitada. Usa, por ejemplo, semana 28 o semana 28-2026.');
        }
        $numero = (int) $m[1];
        $row = $db->queryOne("
            SELECT SEMANA
            FROM tbl_segundometro_histo
            WHERE SEMANA LIKE :patron
            GROUP BY SEMANA
            ORDER BY CAST(SUBSTRING_INDEX(SEMANA, '-', -1) AS UNSIGNED) DESC
            LIMIT 1
        ", ['patron' => 'Semana ' . $numero . '-%']);
        $resuelta = trim((string) ($row['SEMANA'] ?? ''));
        if ($resuelta === '') {
            throw new \InvalidArgumentException('No hay datos disponibles para la semana ' . $numero . '.');
        }
        return $resuelta;
    }

    private static function semanaActual(DatabaseSegundometro $db): string
    {
        $row = $db->queryOne("
            SELECT SEMANA
            FROM tbl_segundometro_semana
            WHERE SEMANA IS NOT NULL AND TRIM(SEMANA) <> ''
            GROUP BY SEMANA
            ORDER BY MAX(fecha_hora_insert) DESC
            LIMIT 1
        ");
        $semana = trim((string) ($row['SEMANA'] ?? ''));
        if ($semana === '') {
            throw new \RuntimeException('Segundometro no informo la semana actual.');
        }
        return $semana;
    }

    /** @return list<string> */
    private static function ultimasSemanas(DatabaseSegundometro $db, int $limite): array
    {
        $limite = max(1, min(30, $limite));
        $rows = $db->queryAll("
            SELECT SEMANA
            FROM tbl_segundometro_histo
            WHERE SEMANA IS NOT NULL AND TRIM(SEMANA) <> ''
            GROUP BY SEMANA
            ORDER BY CAST(SUBSTRING_INDEX(SEMANA, '-', -1) AS UNSIGNED) DESC,
                     CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(SEMANA, ' ', -1), '-', 1) AS UNSIGNED) DESC
            LIMIT {$limite}
        ");
        return array_values(array_filter(array_map(static fn(array $row): string => trim((string) ($row['SEMANA'] ?? '')), $rows)));
    }

    private static function bucketAvanceActualSql(string $columna): string
    {
        $real = self::normalizarBucketSql('Bucket_Morosidad_Real');
        $corte = self::bucketDesdeDiasMoraSql($columna);
        $ordenReal = self::ordenBucketSql($real);
        $ordenCorte = self::ordenBucketSql($corte);
        return "CASE
            WHEN Variable_8 IS NOT NULL AND TRIM(CAST(Variable_8 AS CHAR)) <> '' THEN 'a) Current'
            WHEN Ghost IS NOT NULL AND TRIM(CAST(Ghost AS CHAR)) <> '' AND TRIM(CAST(Ghost AS CHAR)) <> '-' THEN 'a) Current'
            WHEN ({$ordenReal}) IS NULL OR ({$ordenCorte}) IS NULL THEN NULL
            WHEN ({$ordenCorte}) > ({$ordenReal}) THEN ({$real})
            ELSE ({$corte}) END";
    }

    private static function bucketCierreHistoricoSql(): string
    {
        $real = self::normalizarBucketSql('Bucket_Morosidad_Real');
        $base = self::normalizarBucketSql("COALESCE(NULLIF(TRIM(CAST(Bucket_ajustado_ghost AS CHAR)), ''), Cierre_Actual)");
        $cierre = "CASE
            WHEN Variable_8 IS NOT NULL AND TRIM(CAST(Variable_8 AS CHAR)) <> '' THEN 'a) Current'
            WHEN Ghost IS NOT NULL AND TRIM(CAST(Ghost AS CHAR)) <> '' AND TRIM(CAST(Ghost AS CHAR)) <> '-' THEN 'a) Current'
            ELSE ({$base}) END";
        $ordenReal = self::ordenBucketSql($real);
        $ordenCierre = self::ordenBucketSql($cierre);
        return "CASE
            WHEN ({$ordenReal}) IS NULL OR ({$ordenCierre}) IS NULL THEN NULL
            WHEN ({$ordenCierre}) > ({$ordenReal}) THEN ({$real})
            ELSE ({$cierre}) END";
    }

    private static function bucketComparativoSql(string $columna): string
    {
        $real = self::normalizarBucketSql('Bucket_Morosidad_Real');
        $dia = self::bucketDesdeDiasMoraSql($columna);
        $ordenReal = self::ordenBucketSql($real);
        $ordenDia = self::ordenBucketSql($dia);
        return "CASE
            WHEN ({$ordenReal}) IS NOT NULL AND (({$ordenDia}) IS NULL OR ({$ordenReal}) < ({$ordenDia}))
            THEN ({$real}) ELSE ({$dia}) END";
    }

    private static function bucketComparativoConciliadoSql(string $columna): string
    {
        $base = self::bucketComparativoSql($columna);
        return "CASE
            WHEN Variable_8 IS NOT NULL AND TRIM(CAST(Variable_8 AS CHAR)) <> '' THEN 'a) Current'
            WHEN Ghost IS NOT NULL AND TRIM(CAST(Ghost AS CHAR)) <> '' AND TRIM(CAST(Ghost AS CHAR)) <> '-' THEN 'a) Current'
            ELSE ({$base}) END";
    }

    private static function bucketDesdeDiasMoraSql(string $columna): string
    {
        if (!in_array($columna, self::columnasDiasMoraPermitidas(), true)) {
            throw new \InvalidArgumentException('Columna de dias de mora no permitida.');
        }
        $dias = "CAST(NULLIF(TRIM(CAST(`{$columna}` AS CHAR)), '') AS SIGNED)";
        return "CASE
            WHEN ({$dias}) IS NULL THEN NULL
            WHEN ({$dias}) <= 0 THEN 'a) Current'
            WHEN ({$dias}) BETWEEN 1 AND 7 THEN 'b) 1 a 7 dias'
            WHEN ({$dias}) BETWEEN 8 AND 14 THEN 'c) 8 a 14 dias'
            WHEN ({$dias}) BETWEEN 15 AND 21 THEN 'd) 15 a 21 dias'
            WHEN ({$dias}) BETWEEN 22 AND 30 THEN 'e) 22 a 30 dias'
            WHEN ({$dias}) BETWEEN 31 AND 60 THEN 'f) 31 a 60 dias'
            WHEN ({$dias}) BETWEEN 61 AND 90 THEN 'g) 61 a 90 dias'
            WHEN ({$dias}) BETWEEN 91 AND 120 THEN 'h) 91 a 120 dias'
            ELSE 'i) 121+ dias' END";
    }

    private static function normalizarBucketSql(string $expresion): string
    {
        return "CASE TRIM(CAST({$expresion} AS CHAR))
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
            ELSE NULL END";
    }

    private static function ordenBucketSql(string $expresion): string
    {
        return "CASE ({$expresion})
            WHEN 'a) Current' THEN 1 WHEN 'b) 1 a 7 dias' THEN 2
            WHEN 'c) 8 a 14 dias' THEN 3 WHEN 'd) 15 a 21 dias' THEN 4
            WHEN 'e) 22 a 30 dias' THEN 5 WHEN 'f) 31 a 60 dias' THEN 6
            WHEN 'g) 61 a 90 dias' THEN 7 WHEN 'h) 91 a 120 dias' THEN 8
            WHEN 'i) 121+ dias' THEN 9 ELSE NULL END";
    }

    private static function normalizarCorte(?string $corte): string
    {
        $valor = str_replace('_', ':', trim((string) $corte));
        if ($valor === '') {
            $ahora = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
            $minutos = ((int) $ahora->format('H') * 60) + (int) $ahora->format('i');
            $valor = self::CORTES[0];
            foreach (self::CORTES as $opcion) {
                [$hora, $minuto] = array_map('intval', explode(':', $opcion));
                if (($hora * 60) + $minuto > $minutos) {
                    break;
                }
                $valor = $opcion;
            }
        }
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $valor, $m)) {
            $valor = sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }
        if (!in_array($valor, self::CORTES, true)) {
            throw new \InvalidArgumentException('Corte de Bucket no permitido: ' . $valor);
        }
        return $valor;
    }

    private static function columnaDiasMoraCorte(string $corte): string
    {
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo'];
        $hoy = new \DateTimeImmutable('today', new \DateTimeZone('America/Mexico_City'));
        return 'Dias_mora_' . $dias[(int) $hoy->format('N')] . '_' . str_replace(':', '_', $corte);
    }

    /** @return list<string> */
    private static function columnasDiasMoraPermitidas(): array
    {
        $out = [];
        foreach (['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'] as $dia) {
            foreach (self::CORTES as $corte) {
                $out[] = 'Dias_mora_' . $dia . '_' . str_replace(':', '_', $corte);
            }
        }
        return $out;
    }

    private static function diaCorteNombre(): string
    {
        $dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo'];
        $hoy = new \DateTimeImmutable('today', new \DateTimeZone('America/Mexico_City'));
        return $dias[(int) $hoy->format('N')];
    }

    /** @return list<array<string,mixed>> */
    private static function construirVistasCredito(
        array $fila,
        string $tabla,
        string $semana,
        string $corte
    ): array {
        $fechaFuente = trim((string) ($fila['fecha_hora_fuente'] ?? ''));
        $dias = isset($fila['dias_mora_corte']) && $fila['dias_mora_corte'] !== null
            ? (int) $fila['dias_mora_corte']
            : null;
        $comun = [
            'semana' => $semana,
            'corte' => self::diaCorteNombre() . ' ' . $corte,
            'fecha_hora_fuente' => $fechaFuente,
            'antiguedad_minutos' => self::antiguedadMinutos($fechaFuente),
            'dias_mora' => $dias,
        ];

        return [
            $comun + [
                'vista' => 'Nacimiento',
                'fuente' => $tabla . '.Bucket_Morosidad_Real',
                'bucket' => self::etiqueta($fila['bucket_real'] ?? null),
                'formula' => 'Normaliza Bucket_Morosidad_Real al catalogo Current, 1-7, 8-14, 15-21, 22-30, 31-60, 61-90, 91-120 o 121+.',
                'filtros' => ['Id_credito', 'SEMANA'],
            ],
            $comun + [
                'vista' => 'Segundometro / Avance',
                'fuente' => $tabla,
                'bucket' => self::etiqueta($fila['bucket_avance'] ?? null),
                'formula' => 'Si Variable_8 o Ghost tienen valor, fuerza Current. En otro caso compara el bucket de nacimiento contra el bucket calculado con los dias de mora del corte y conserva el rango menos moroso.',
                'filtros' => ['Id_credito', 'SEMANA', self::columnaDiasMoraCorte($corte), 'Ghost', 'Variable_8'],
            ],
            $comun + [
                'vista' => 'Historico / cierre ajustado',
                'fuente' => $tabla . '.Bucket_ajustado_ghost/Cierre_Actual',
                'bucket' => self::etiqueta($fila['bucket_historico'] ?? null),
                'formula' => 'Parte de Bucket_ajustado_ghost; si esta vacio usa Cierre_Actual. Ghost o Variable_8 fuerzan Current y el resultado no puede quedar mas moroso que el bucket de nacimiento.',
                'filtros' => ['Id_credito', 'SEMANA', 'Bucket_ajustado_ghost', 'Cierre_Actual', 'Ghost', 'Variable_8'],
            ],
            $comun + [
                'vista' => 'Comparativo',
                'fuente' => $tabla . '.' . self::columnaDiasMoraCorte($corte),
                'bucket' => self::etiqueta($fila['bucket_comparativo'] ?? null),
                'formula' => 'Convierte los dias de mora del corte en bucket y lo compara con el bucket de nacimiento; conserva el rango menos moroso. La variante conciliada aplica despues Ghost y Variable_8.',
                'filtros' => ['Id_credito', 'SEMANA', self::columnaDiasMoraCorte($corte)],
            ],
        ];
    }

    private static function antiguedadMinutos(mixed $fecha): ?int
    {
        $valor = trim((string) $fecha);
        if ($valor === '') {
            return null;
        }
        try {
            $zona = new \DateTimeZone('America/Mexico_City');
            $origen = new \DateTimeImmutable($valor, $zona);
            $ahora = new \DateTimeImmutable('now', $zona);
            return max(0, (int) floor(($ahora->getTimestamp() - $origen->getTimestamp()) / 60));
        } catch (\Throwable $e) {
            return null;
        }
    }

    private static function etiqueta(mixed $bucket): ?string
    {
        $normalizado = trim((string) $bucket);
        return self::BUCKETS[$normalizado] ?? ($normalizado !== '' ? $normalizado : null);
    }
}
