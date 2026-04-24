<?php

namespace Models;

use Core\Database;
use Core\Model;
use Core\UsuarioFantasmaReporteria;
use DateTimeImmutable;

/**
 * KPIs y métricas del panel Estadísticas (Capital Humano).
 * Consultas sobre __SPARTA_SECRET_REDACTED__ (persona, bajas, candidatos, módulos, etc.).
 */
class CapHumEstadisticas extends Model
{
    private const MODULO_ONBOARDING_WEB = 44;

    /** @return array<string, mixed> */
    private static function rangoMesSemana(int $anio, int $mes, int $semana): array
    {
        $mes = max(1, min(12, $mes));
        $semana = max(0, min(4, $semana));
        $ini = new DateTimeImmutable(sprintf('%04d-%02d-01', $anio, $mes));
        $ultimoDia = (int) $ini->format('t');

        if ($semana === 0) {
            $dIni = 1;
            $dFin = $ultimoDia;
        } elseif ($semana === 1) {
            $dIni = 1;
            $dFin = min(7, $ultimoDia);
        } elseif ($semana === 2) {
            $dIni = 8;
            $dFin = min(14, $ultimoDia);
        } elseif ($semana === 3) {
            $dIni = 15;
            $dFin = min(21, $ultimoDia);
        } else {
            $dIni = 22;
            $dFin = $ultimoDia;
        }

        $fechaIni = $ini->setDate($anio, $mes, $dIni)->format('Y-m-d');
        $fechaFin = $ini->setDate($anio, $mes, $dFin)->format('Y-m-d');

        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
        ];
        $nombreMes = $meses[$mes] ?? (string) $mes;
        $periodo = ucfirst($nombreMes) . ' ' . $anio;
        if ($semana === 0) {
            $periodo .= ' — Mes completo';
        } else {
            $periodo .= ' — Semana ' . $semana . ' (' . $dIni . ' al ' . $dFin . ' de ' . $nombreMes . ')';
        }

        return [
            'fecha_ini' => $fechaIni,
            'fecha_fin' => $fechaFin,
            'periodo_label' => $periodo,
        ];
    }

    /**
     * Rango explícito desde calendario (YYYY-MM-DD), misma idea que el filtro Flatpickr de control de bajas.
     *
     * @return array{fecha_ini: string, fecha_fin: string, periodo_label: string}|null
     */
    private static function rangoDesdeCalendario(?string $fechaIni, ?string $fechaFin): ?array
    {
        if ($fechaIni === null || $fechaFin === null) {
            return null;
        }
        $fechaIni = trim($fechaIni);
        $fechaFin = trim($fechaFin);
        if ($fechaIni === '' || $fechaFin === '') {
            return null;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaIni) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            return null;
        }
        try {
            $d1 = new DateTimeImmutable($fechaIni);
            $d2 = new DateTimeImmutable($fechaFin);
        } catch (\Throwable $e) {
            return null;
        }
        if ($d2 < $d1) {
            $tmp = $fechaIni;
            $fechaIni = $fechaFin;
            $fechaFin = $tmp;
            $d1 = new DateTimeImmutable($fechaIni);
            $d2 = new DateTimeImmutable($fechaFin);
        }
        $maxDias = 800;
        if ($d1->diff($d2)->days > $maxDias) {
            return null;
        }
        $dmy = static function (string $ymd): string {
            $p = explode('-', $ymd);
            if (count($p) !== 3) {
                return $ymd;
            }

            return (string) ((int) $p[2]) . '/' . (string) ((int) $p[1]) . '/' . $p[0];
        };
        $periodo = 'Rango personalizado — ' . $dmy($fechaIni) . ' al ' . $dmy($fechaFin);

        return [
            'fecha_ini' => $fechaIni,
            'fecha_fin' => $fechaFin,
            'periodo_label' => $periodo,
        ];
    }

    private static function scalarInt(Database $db, string $sql, array $params = []): int
    {
        try {
            $r = $db->queryOne($sql, self::paramsForSql($sql, $params));
            if (!$r) {
                return 0;
            }
            $v = $r['c'] ?? $r['cnt'] ?? $r['n'] ?? null;

            return (int) $v;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function scalarFloat(Database $db, string $sql, array $params = []): float
    {
        try {
            $r = $db->queryOne($sql, self::paramsForSql($sql, $params));
            if (!$r) {
                return 0.0;
            }
            $v = $r['v'] ?? $r['avg'] ?? null;
            if ($v === null) {
                return 0.0;
            }

            return round((float) $v, 1);
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /** @return list<array<string, mixed>> */
    private static function queryAllSafe(Database $db, string $sql, array $params = []): array
    {
        try {
            $r = $db->queryAll($sql, self::paramsForSql($sql, $params));

            return is_array($r) ? $r : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /** @return array<string, mixed>|null */
    private static function queryOneSafe(Database $db, string $sql, array $params = []): ?array
    {
        try {
            $r = $db->queryOne($sql, self::paramsForSql($sql, $params));

            return is_array($r) ? $r : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Database::runQuery intenta bindear TODAS las llaves del arreglo; si alguna
     * no existe en el SQL, PDO lanza "Invalid parameter number".
     * Aquí filtramos solo los placeholders realmente presentes en la sentencia.
     *
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private static function paramsForSql(string $sql, array $params): array
    {
        if (empty($params)) {
            return [];
        }
        if (!preg_match_all('/:[a-zA-Z_][a-zA-Z0-9_]*/', $sql, $m)) {
            return [];
        }
        $out = [];
        $usados = array_unique(array_map(static function ($ph) {
            return ltrim((string) $ph, ':');
        }, $m[0] ?? []));
        foreach ($usados as $k) {
            if (array_key_exists($k, $params)) {
                $out[$k] = $params[$k];
            }
        }

        return $out;
    }

    private static function diasHabilesRango(string $fechaIni, string $fechaFin): int
    {
        try {
            $d1 = new DateTimeImmutable($fechaIni);
            $d2 = new DateTimeImmutable($fechaFin);
            if ($d2 < $d1) {
                return 1;
            }
            $n = 0;
            for ($d = $d1; $d <= $d2; $d = $d->modify('+1 day')) {
                $w = (int) $d->format('w');
                if ($w !== 0 && $w !== 6) {
                    ++$n;
                }
            }

            return max(1, $n);
        } catch (\Throwable $e) {
            return 1;
        }
    }

    /** Nombre de tabla calificado al esquema de la app (misma lógica que CapHum::getConsultaBajas). */
    private static function tblBd(string $tabla): string
    {
        $s = getenv('DB_NAME') ?: getenv('DB_ESQUEMA') ?: '__SPARTA_SECRET_REDACTED__';
        $s = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $s) ?: '__SPARTA_SECRET_REDACTED__';
        $t = preg_replace('/[^a-zA-Z0-9_]/', '', $tabla);

        return '`' . $s . '`.`' . $t . '`';
    }

    private static function personaTieneColumna(Database $db, string $columna): bool
    {
        $permitidas = ['genero', 'sexo', 'fecha_fin_induccion'];
        if (!in_array($columna, $permitidas, true)) {
            return false;
        }
        try {
            $c = str_replace('`', '', $columna);
            $tp = self::tblBd('persona');
            $db->queryOne("SELECT p.`{$c}` FROM {$tp} p LIMIT 0");

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Convierte promedio de días en etiqueta "X años Y meses" (aprox.). */
    private static function antiguedadLabelDesdeDiasPromedio(float $avgDias): string
    {
        if ($avgDias <= 0) {
            return '0 años 0 meses';
        }
        $totalMeses = (int) round($avgDias / 30.437);
        $y = intdiv($totalMeses, 12);
        $m = $totalMeses % 12;

        return $y . ' años ' . $m . ' meses';
    }

    /**
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    public static function getDatosPanel(int $anio, int $mes, int $semana, ?string $fechaIniCal = null, ?string $fechaFinCal = null): array
    {
        try {
            $db = new Database();
            $rango = self::rangoDesdeCalendario($fechaIniCal, $fechaFinCal)
                ?? self::rangoMesSemana($anio, $mes, $semana);
            $fi = $rango['fecha_ini'];
            $ff = $rango['fecha_fin'];
            /*
             * PDO con ATTR_EMULATE_PREPARES=false: en una misma sentencia cada placeholder debe ser único.
             * Si se repite :ff o :fi, MySQL/PDO falla y scalarInt/query* devuelve 0 (silencioso) → KPI en ceros.
             */
            $paramsRango = [
                'fi' => $fi,
                'ff' => $ff,
                'ff_hi' => $ff,
                'ff_hc_ing' => $ff,
                'ff_ant' => $ff,
                'ff_90a' => $ff,
                'ff_90b' => $ff,
                'ff_prb' => $ff,
                'ff_aus' => $ff,
            ];
            $paramsAusSum = [
                'fi_a' => $fi, 'ff_a' => $ff,
                'fi_b' => $fi, 'ff_b' => $ff,
                'fi_c' => $fi, 'ff_c' => $ff,
            ];

            $tp = self::tblBd('persona');
            $tap = self::tblBd('asigna_puesto');
            $tpu = self::tblBd('puesto');
            $td = self::tblBd('departamento');
            $tbp = self::tblBd('baja_persona');
            $tcnd = self::tblBd('candidatos');
            $tmw = self::tblBd('asigna_modulo_web');
            $trg = self::tblBd('reingresos');
            $tpa = self::tblBd('paises');
            $taus = self::tblBd('ausencia');

            // Misma idea que Gestión (CapHum::getGestoresParaReporte): no contar «Baja»; activo = texto case-insensitive.
            $sqlNoBaja = '(p.estatus IS NULL OR LOWER(TRIM(COALESCE(p.estatus,\'\'))) <> \'baja\')';
            $sqlActivo = "LOWER(TRIM(COALESCE(p.estatus,''))) = 'activo'";
            $sqlIngHastaFfHi = '(p.fecha_ingreso IS NULL OR p.fecha_ingreso IN (\'0000-00-00\',\'0000-00-00 00:00:00\') OR DATE(p.fecha_ingreso) <= :ff_hi)';
            $sqlExFantasma = UsuarioFantasmaReporteria::sqlExcluirPersona('p');

            $sqlHeadcountBase = '
                FROM ' . $tp . ' p
                WHERE ' . $sqlNoBaja . '
                  AND ' . $sqlIngHastaFfHi . $sqlExFantasma;
            // Plantilla al cierre del periodo (excluye baja formal; ingreso hasta último día): usada en tasas por periodo.
            $headcountPlantillaCierre = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c ' . $sqlHeadcountBase,
                $paramsRango
            );
            // KPI superior: totales actuales en tabla persona (sin filtro de fechas del periodo).
            $totalPersonaTabla = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p WHERE 1=1 ' . $sqlExFantasma,
                []
            );
            $empleadosActivosCierre = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c ' . $sqlHeadcountBase . ' AND ' . $sqlActivo,
                $paramsRango
            );
            $empleadosActivos = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p WHERE ' . $sqlActivo . $sqlExFantasma,
                []
            );
            $empleadosBaja = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p
                 WHERE LOWER(TRIM(COALESCE(p.estatus,\'\'))) = \'baja\'' . $sqlExFantasma,
                []
            );
            $totalDepartamentos = self::scalarInt(
                $db,
                'SELECT COUNT(DISTINCT pu.departamento_id) AS c
                 FROM ' . $tp . ' p
                 INNER JOIN ' . $tap . ' ap ON ap.id_persona = p.id
                 INNER JOIN ' . $tpu . ' pu ON pu.id = ap.id_puesto
                 WHERE ' . $sqlActivo . '
                   AND ' . $sqlIngHastaFfHi . $sqlExFantasma,
                $paramsRango
            );
            $puestosUnicos = self::scalarInt(
                $db,
                'SELECT COUNT(DISTINCT pu.id) AS c
                 FROM ' . $tp . ' p
                 INNER JOIN ' . $tap . ' ap ON ap.id_persona = p.id
                 INNER JOIN ' . $tpu . ' pu ON pu.id = ap.id_puesto
                 WHERE ' . $sqlActivo . '
                   AND ' . $sqlIngHastaFfHi . $sqlExFantasma,
                $paramsRango
            );

            $hcActivoCierre = "
                  AND (p.fecha_ingreso IS NULL OR p.fecha_ingreso IN ('0000-00-00','0000-00-00 00:00:00') OR DATE(p.fecha_ingreso) <= :ff_hc_ing)";

            $ingresos = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p
                 WHERE p.fecha_ingreso IS NOT NULL
                   AND p.fecha_ingreso NOT IN (\'0000-00-00\',\'0000-00-00 00:00:00\')
                   AND DATE(p.fecha_ingreso) BETWEEN :fi AND :ff' . $sqlExFantasma,
                $paramsRango
            );

            $bajas = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tbp . ' bp
                 WHERE DATE(bp.fecha_baja) BETWEEN :fi AND :ff',
                $paramsRango
            );

            $reingresos = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $trg . ' r
                 WHERE DATE(r.fecha_reingreso) BETWEEN :fi AND :ff',
                $paramsRango
            );

            $denRot = $empleadosActivos > 0 ? $empleadosActivos : 1;
            $rotacionPct = round(100.0 * ($bajas / $denRot), 1);
            if ($rotacionPct <= 5.0) {
                $rotacionBadge = 'bg-success';
                $rotacionBadgeText = 'Controlada';
                $rotacionAyuda = "Controlada\n\n"
                    . 'La rotación se mantiene en un nivel bajo y dentro de lo esperado. Las bajas del periodo no representan una alerta relevante, aunque conviene seguir observando su comportamiento para confirmar que la tendencia se conserve estable.';
            } elseif ($rotacionPct <= 15.0) {
                $rotacionBadge = 'bg-warning text-dark';
                $rotacionBadgeText = 'Moderada';
                $rotacionAyuda = "Moderada\n\n"
                    . 'La rotación presenta un nivel intermedio: ya hay más bajas de las deseadas, pero todavía no se considera un foco crítico. Es recomendable revisar las causas de salida y detectar si el comportamiento se concentra en alguna área, puesto o grupo específico.';
            } else {
                $rotacionBadge = 'bg-danger';
                $rotacionBadgeText = 'Elevada';
                $rotacionAyuda = "Elevada\n\n"
                    . 'La rotación está en un nivel alto y puede reflejar una fuga importante de personal. Conviene analizar de inmediato las causas, identificar áreas con mayor incidencia y definir acciones con Capital Humano o la dirección para contenerla.';
            }

            $vacantesAbiertas = self::scalarInt(
                $db,
                'SELECT COUNT(DISTINCT CONCAT(IFNULL(c.id_departamento, 0), \'-\', IFNULL(c.id_puesto, 0))) AS c
                 FROM ' . $tcnd . ' c
                 WHERE c.estatus IN (\'Por evaluar\',\'En entrevista\',\'Validado\')',
                []
            );

            $candidatosActivos = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tcnd . ' c
                 WHERE c.estatus IN (\'Por evaluar\',\'En entrevista\',\'Validado\')',
                []
            );

            $selBadge = $candidatosActivos > 10 ? 'bg-danger' : 'bg-info text-dark';
            $selBadgeText = $candidatosActivos > 10 ? 'Alta demanda' : 'En flujo';

            $contrataciones = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tcnd . ' c
                 WHERE c.estatus = \'Contratado\'
                   AND DATE(COALESCE(c.fecha_actualizacion, c.fecha_registro)) BETWEEN :fi AND :ff',
                $paramsRango
            );

            $diasPromedio = self::scalarFloat(
                $db,
                'SELECT AVG(DATEDIFF(COALESCE(c.fecha_actualizacion, c.fecha_registro), c.fecha_registro)) AS v
                 FROM ' . $tcnd . ' c
                 WHERE c.estatus = \'Contratado\'
                   AND DATE(COALESCE(c.fecha_actualizacion, c.fecha_registro)) BETWEEN :fi AND :ff',
                $paramsRango
            );

            $induccionProgreso = self::scalarInt(
                $db,
                'SELECT COUNT(DISTINCT am.usuario_id) AS c
                 FROM ' . $tmw . ' am
                 INNER JOIN ' . $tp . ' p ON p.id = am.usuario_id
                 WHERE am.modulo_web_id = :mid
                   AND ' . $sqlActivo . $sqlExFantasma . $hcActivoCierre,
                array_merge($paramsRango, ['mid' => self::MODULO_ONBOARDING_WEB])
            );

            $induccionCompletados = self::scalarInt(
                $db,
                'SELECT COUNT(DISTINCT p.id) AS c
                 FROM ' . $tp . ' p
                 WHERE ' . $sqlActivo . $sqlExFantasma . '
                   AND DATE(p.fecha_ingreso) BETWEEN :fi AND :ff
                   AND NOT EXISTS (
                       SELECT 1 FROM ' . $tmw . ' am
                       WHERE am.usuario_id = p.id AND am.modulo_web_id = :mid
                   )' . $hcActivoCierre,
                array_merge($paramsRango, ['mid' => self::MODULO_ONBOARDING_WEB])
            );

            $tasaAprobacion = self::scalarFloat(
                $db,
                'SELECT (100.0 * SUM(CASE WHEN c.estatus IN (\'Contratado\',\'Validado\') THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)) AS v
                 FROM ' . $tcnd . ' c
                 WHERE DATE(c.fecha_registro) BETWEEN :fi AND :ff',
                $paramsRango
            );

            // --- Plantilla & estructura (sede / género / antigüedad / nuevos 90 días / bajas 90 días) ---
            $plantillaSedeTop = [];
            $plantillaTotalSedes = 0;
            $rowsSede = self::queryAllSafe(
                $db,
                'SELECT COALESCE(pa.nombre, \'Sin sede\') AS nombre_sede, COUNT(*) AS cnt
                 FROM ' . $tp . ' p
                 LEFT JOIN ' . $tpa . ' pa ON pa.id = p.id_pais
                 WHERE ' . $sqlActivo . $sqlExFantasma . $hcActivoCierre . '
                 GROUP BY p.id_pais, pa.nombre
                 ORDER BY cnt DESC
                 LIMIT 3',
                $paramsRango
            );
            foreach ($rowsSede as $row) {
                $plantillaSedeTop[] = [
                    'nombre' => (string) ($row['nombre_sede'] ?? ''),
                    'cnt' => (int) ($row['cnt'] ?? 0),
                ];
            }
            $plantillaTotalSedes = self::scalarInt(
                $db,
                'SELECT COUNT(DISTINCT p.id_pais) AS c FROM ' . $tp . ' p WHERE ' . $sqlActivo . $sqlExFantasma . $hcActivoCierre,
                $paramsRango
            );

            $plantillaOmitGenero = true;
            $plantillaGeneroH = 0;
            $plantillaGeneroM = 0;
            $plantillaGeneroBadge = '';
            $colGen = null;
            if (self::personaTieneColumna($db, 'genero')) {
                $colGen = 'genero';
            } elseif (self::personaTieneColumna($db, 'sexo')) {
                $colGen = 'sexo';
            }
            if ($colGen !== null) {
                $plantillaOmitGenero = false;
                $plantillaGeneroH = self::scalarInt(
                    $db,
                    "SELECT COUNT(*) AS c FROM {$tp} p
                     WHERE {$sqlActivo}{$sqlExFantasma}
                       AND (
                         LOWER(TRIM(p.`{$colGen}`)) IN ('m','masculino','h','hombre','hombre ','male')
                         OR LOWER(TRIM(p.`{$colGen}`)) LIKE 'masc%'
                       )" . $hcActivoCierre,
                    $paramsRango
                );
                $plantillaGeneroM = self::scalarInt(
                    $db,
                    "SELECT COUNT(*) AS c FROM {$tp} p
                     WHERE {$sqlActivo}{$sqlExFantasma}
                       AND (
                         LOWER(TRIM(p.`{$colGen}`)) IN ('f','femenino','mujer','female')
                         OR LOWER(TRIM(p.`{$colGen}`)) LIKE 'femen%'
                       )" . $hcActivoCierre,
                    $paramsRango
                );
                $plantillaGeneroBadge = $plantillaGeneroH >= $plantillaGeneroM ? 'Hombres' : 'Mujeres';
            }

            $avgDiasAntig = self::scalarFloat(
                $db,
                'SELECT AVG(DATEDIFF(:ff_ant, DATE(p.fecha_ingreso))) AS v
                 FROM ' . $tp . ' p
                 WHERE ' . $sqlActivo . $sqlExFantasma . ' AND p.fecha_ingreso IS NOT NULL' . $hcActivoCierre,
                $paramsRango
            );
            $plantillaAntigN = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c
                 FROM ' . $tp . ' p
                 WHERE ' . $sqlActivo . $sqlExFantasma . ' AND p.fecha_ingreso IS NOT NULL' . $hcActivoCierre,
                $paramsRango
            );
            $plantillaAntigLabel = self::antiguedadLabelDesdeDiasPromedio($avgDiasAntig);
            $plantillaNuevos90Desde = date('Y-m-d', strtotime($ff . ' -89 days'));

            $plantillaEmpleadosNuevos90 = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p
                 WHERE ' . $sqlActivo . $sqlExFantasma . '
                   AND p.fecha_ingreso IS NOT NULL
                   AND p.fecha_ingreso NOT IN (\'0000-00-00\',\'0000-00-00 00:00:00\')
                   AND DATE(p.fecha_ingreso) BETWEEN DATE_SUB(:ff_90a, INTERVAL 89 DAY) AND :ff_90b' . $hcActivoCierre,
                $paramsRango
            );

            $plantillaEmpleadosBajas90 = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tbp . ' bp
                 WHERE DATE(bp.fecha_baja) BETWEEN DATE_SUB(:ff_90a, INTERVAL 89 DAY) AND :ff_90b',
                $paramsRango
            );

            $onbEnPrueba = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p
                 WHERE ' . $sqlActivo . $sqlExFantasma . '
                   AND p.fecha_ingreso IS NOT NULL
                   AND DATEDIFF(:ff_prb, DATE(p.fecha_ingreso)) < 90' . $hcActivoCierre,
                $paramsRango
            );

            // Criterio alineado al panel: "completa" = sin módulo web 44 (inducción cerrada en sistema); "pendiente" = con módulo asignado.
            $onbCompletaActivos = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p
                 WHERE ' . $sqlActivo . $sqlExFantasma . '
                   AND NOT EXISTS (
                       SELECT 1 FROM ' . $tmw . ' am
                       WHERE am.usuario_id = p.id AND am.modulo_web_id = :mid
                   )' . $hcActivoCierre,
                array_merge($paramsRango, ['mid' => self::MODULO_ONBOARDING_WEB])
            );
            $onbPendienteActivos = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p
                 INNER JOIN ' . $tmw . ' am ON am.usuario_id = p.id AND am.modulo_web_id = :mid
                 WHERE ' . $sqlActivo . $sqlExFantasma . $hcActivoCierre,
                array_merge($paramsRango, ['mid' => self::MODULO_ONBOARDING_WEB])
            );

            $omitTiempoInduccion = true;
            $onbDiasPromInduccion = 0.0;
            if (self::personaTieneColumna($db, 'fecha_fin_induccion')) {
                $onbDiasPromInduccion = self::scalarFloat(
                    $db,
                    'SELECT AVG(DATEDIFF(DATE(p.fecha_fin_induccion), DATE(p.fecha_ingreso))) AS v
                     FROM ' . $tp . ' p
                     WHERE ' . $sqlActivo . $sqlExFantasma . '
                       AND p.fecha_ingreso IS NOT NULL
                       AND p.fecha_fin_induccion IS NOT NULL
                       AND DATE(p.fecha_fin_induccion) BETWEEN :fi AND :ff' . $hcActivoCierre,
                    $paramsRango
                );
                $omitTiempoInduccion = false;
            }

            $ingresosInduccionCompletosPeriodo = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c FROM ' . $tp . ' p
                 WHERE ' . $sqlActivo . $sqlExFantasma . '
                   AND DATE(p.fecha_ingreso) BETWEEN :fi AND :ff
                   AND NOT EXISTS (
                       SELECT 1 FROM ' . $tmw . ' am
                       WHERE am.usuario_id = p.id AND am.modulo_web_id = :mid
                   )' . $hcActivoCierre,
                array_merge($paramsRango, ['mid' => self::MODULO_ONBOARDING_WEB])
            );
            $onbPctInduccionCompleta = $ingresos > 0
                ? round(100.0 * $ingresosInduccionCompletosPeriodo / $ingresos, 1)
                : 0.0;

            // --- Ausentismo (tabla ausencia) ---
            $omitAusentismo = false;
            $ausDiasTotal = 0;
            $ausEmpleados3mas = 0;
            $ausSinAusencia = 0;
            $ausTasaPct = 0.0;
            $diasHabilesRango = self::diasHabilesRango($fi, $ff);
            try {
                $db->queryOne('SELECT id FROM ' . $taus . ' LIMIT 1');
            } catch (\Throwable $e) {
                $omitAusentismo = true;
            }
            if (!$omitAusentismo) {
                $ausDiasTotal = self::scalarInt(
                    $db,
                    "SELECT COALESCE(SUM(
                        CASE
                            WHEN LEAST(IFNULL(DATE(a.fecha_fin), DATE(a.fecha_inicio)), :ff_a)
                                 >= GREATEST(DATE(a.fecha_inicio), :fi_a)
                            THEN DATEDIFF(
                                LEAST(IFNULL(DATE(a.fecha_fin), DATE(a.fecha_inicio)), :ff_b),
                                GREATEST(DATE(a.fecha_inicio), :fi_b)
                            ) + 1
                            ELSE 0
                        END
                    ), 0) AS c
                    FROM {$taus} a
                    WHERE IFNULL(a.activo, 1) = 1
                      AND DATE(a.fecha_inicio) <= :ff_c
                      AND DATE(IFNULL(a.fecha_fin, a.fecha_inicio)) >= :fi_c",
                    $paramsAusSum
                );
                $ausEmpleados3mas = self::scalarInt(
                    $db,
                    "SELECT COUNT(*) AS c FROM (
                        SELECT a.id_persona
                        FROM {$taus} a
                        WHERE IFNULL(a.activo, 1) = 1
                          AND DATE(a.fecha_inicio) <= :ff
                          AND DATE(IFNULL(a.fecha_fin, a.fecha_inicio)) >= :fi
                        GROUP BY a.id_persona
                        HAVING COUNT(*) >= 3
                    ) t",
                    $paramsRango
                );
                $ausSinAusencia = self::scalarInt(
                    $db,
                    'SELECT COUNT(*) AS c
                     FROM ' . $tp . ' p
                     WHERE ' . $sqlActivo . $sqlExFantasma . '
                       AND NOT EXISTS (
                           SELECT 1 FROM ' . $taus . ' a
                           WHERE a.id_persona = p.id
                             AND IFNULL(a.activo, 1) = 1
                             AND DATE(a.fecha_inicio) <= :ff_aus
                             AND DATE(IFNULL(a.fecha_fin, a.fecha_inicio)) >= :fi
                       )' . $hcActivoCierre,
                    $paramsRango
                );
                $denTasa = $diasHabilesRango * max(1, $empleadosActivosCierre);
                $ausTasaPct = $denTasa > 0 ? round(100.0 * ($ausDiasTotal / $denTasa), 1) : 0.0;
            }

            // --- Estructura operativa ---
            // Agente Call Center real: nombre de puesto alineado a CapHum (celula 2), sin el LIKE genérico «%agente%».
            // Una fila de asignación activa por persona (MAX id) para no duplicar ni mezclar puestos viejos.
            // Última fila de asigna_puesto por persona (como en listados); sin filtro «activo» por si la columna no existe.
            $sqlApActivoUltimo = '
                 INNER JOIN (
                     SELECT apx.id_persona, apx.id_puesto
                     FROM ' . $tap . ' apx
                     INNER JOIN (
                         SELECT id_persona, MAX(id) AS mid
                         FROM ' . $tap . '
                         GROUP BY id_persona
                     ) apm ON apm.id_persona = apx.id_persona AND apm.mid = apx.id
                 ) ap ON ap.id_persona = p.id
                 INNER JOIN ' . $tpu . ' pu ON pu.id = ap.id_puesto';
            $opAgentesCall = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c
                 FROM ' . $tp . ' p' . $sqlApActivoUltimo . '
                 WHERE ' . $sqlActivo . $sqlExFantasma . '
                   AND (
                     LOWER(TRIM(pu.nombre)) = \'agente call center\'
                     OR (
                       LOWER(pu.nombre) LIKE \'%call center%\'
                       AND LOWER(pu.nombre) LIKE \'%agente%\'
                       AND LOWER(pu.nombre) NOT LIKE \'%supervis%\'
                     )
                   )' . $hcActivoCierre,
                $paramsRango
            );
            $opSupervisores = self::scalarInt(
                $db,
                'SELECT COUNT(*) AS c
                 FROM ' . $tp . ' p' . $sqlApActivoUltimo . '
                 WHERE ' . $sqlActivo . $sqlExFantasma . ' AND IFNULL(pu.es_jefe, 0) = 1' . $hcActivoCierre,
                $paramsRango
            );
            $opRatioX = ($opSupervisores > 0 && $opAgentesCall > 0)
                ? (int) round($opAgentesCall / $opSupervisores)
                : 0;
            $opRatioStr = ($opSupervisores > 0 && $opAgentesCall > 0)
                ? ('1 : ' . $opRatioX)
                : '—';

            $opDeptoTopNombre = '';
            $opDeptoTopCnt = 0;
            $rowDepto = self::queryOneSafe(
                $db,
                'SELECT d.nombre AS nombre_depto, COUNT(*) AS cnt
                 FROM ' . $tp . ' p' . $sqlApActivoUltimo . '
                 INNER JOIN ' . $td . ' d ON d.id = pu.departamento_id
                 WHERE ' . $sqlActivo . $sqlExFantasma . $hcActivoCierre . '
                 GROUP BY d.id, d.nombre
                 ORDER BY cnt DESC
                 LIMIT 1',
                $paramsRango
            );
            if (is_array($rowDepto)) {
                $opDeptoTopNombre = (string) ($rowDepto['nombre_depto'] ?? '');
                $opDeptoTopCnt = (int) ($rowDepto['cnt'] ?? 0);
            }

            $rowsPlantDepto = self::queryAllSafe(
                $db,
                'SELECT COALESCE(NULLIF(TRIM(d.nombre), \'\'), \'Sin departamento\') AS nombre, COUNT(*) AS cnt
                 FROM ' . $tp . ' p' . $sqlApActivoUltimo . '
                 LEFT JOIN ' . $td . ' d ON d.id = pu.departamento_id
                 WHERE ' . $sqlActivo . $sqlExFantasma . $hcActivoCierre . '
                 GROUP BY d.id, d.nombre
                 ORDER BY cnt DESC',
                $paramsRango
            );
            $plantillaPorDepto = [];
            $maxSlices = 20;
            $nPlantDept = count($rowsPlantDepto);
            if ($nPlantDept === 0) {
                // sin filas
            } elseif ($nPlantDept <= $maxSlices) {
                foreach ($rowsPlantDepto as $r) {
                    $plantillaPorDepto[] = [
                        'nombre' => (string) ($r['nombre'] ?? '—'),
                        'cnt' => (int) ($r['cnt'] ?? 0),
                    ];
                }
            } else {
                for ($i = 0; $i < $maxSlices - 1; $i++) {
                    $r = $rowsPlantDepto[$i];
                    $plantillaPorDepto[] = [
                        'nombre' => (string) ($r['nombre'] ?? '—'),
                        'cnt' => (int) ($r['cnt'] ?? 0),
                    ];
                }
                $otrosSum = 0;
                for ($i = $maxSlices - 1; $i < $nPlantDept; $i++) {
                    $otrosSum += (int) ($rowsPlantDepto[$i]['cnt'] ?? 0);
                }
                if ($otrosSum > 0) {
                    $plantillaPorDepto[] = ['nombre' => 'Otros', 'cnt' => $otrosSum];
                }
            }

            $datos = [
                'periodo_label' => $rango['periodo_label'],
                'fecha_ini' => $fi,
                'fecha_fin' => $ff,
                'total_empleados' => $totalPersonaTabla,
                'plantilla_cierre_total' => $headcountPlantillaCierre,
                'empleados_activos' => $empleadosActivos,
                'empleados_baja' => $empleadosBaja,
                'total_departamentos' => $totalDepartamentos,
                'puestos_unicos' => $puestosUnicos,
                'ingresos' => $ingresos,
                'bajas' => $bajas,
                'reingresos' => $reingresos,
                'plantilla_por_departamento' => $plantillaPorDepto,
                'rotacion_pct' => $rotacionPct,
                'rotacion_badge_class' => $rotacionBadge,
                'rotacion_badge_text' => $rotacionBadgeText,
                'rotacion_ayuda' => $rotacionAyuda,
                'vacantes_abiertas' => $vacantesAbiertas,
                'candidatos_activos' => $candidatosActivos,
                'seleccion_badge_class' => $selBadge,
                'seleccion_badge_text' => $selBadgeText,
                'contrataciones' => $contrataciones,
                'dias_promedio_contratacion' => $diasPromedio,
                'induccion_progreso' => $induccionProgreso,
                'induccion_completados' => $induccionCompletados,
                'tasa_aprobacion_pct' => round($tasaAprobacion, 1),
                'plantilla_sede_top' => $plantillaSedeTop,
                'plantilla_total_sedes_activas' => $plantillaTotalSedes,
                'plantilla_omit_genero' => $plantillaOmitGenero,
                'plantilla_genero_hombres' => $plantillaGeneroH,
                'plantilla_genero_mujeres' => $plantillaGeneroM,
                'plantilla_genero_badge' => $plantillaGeneroBadge,
                'plantilla_antiguedad_label' => $plantillaAntigLabel,
                'plantilla_antiguedad_n' => $plantillaAntigN,
                'plantilla_empleados_nuevos_90' => $plantillaEmpleadosNuevos90,
                'plantilla_nuevos90_desde' => $plantillaNuevos90Desde,
                'plantilla_nuevos90_hasta' => $ff,
                'plantilla_empleados_bajas_90' => $plantillaEmpleadosBajas90,
                'plantilla_bajas90_desde' => $plantillaNuevos90Desde,
                'plantilla_bajas90_hasta' => $ff,
                'ausentismo_omit' => $omitAusentismo,
                'ausentismo_dias_total' => $ausDiasTotal,
                'ausentismo_empleados_3mas' => $ausEmpleados3mas,
                'ausentismo_tasa_pct' => $ausTasaPct,
                'ausentismo_sin_ausencia' => $ausSinAusencia,
                'ausentismo_dias_habiles_rango' => $diasHabilesRango,
                'onb_en_prueba' => $onbEnPrueba,
                'onb_completa_activos' => $onbCompletaActivos,
                'onb_pendiente_activos' => $onbPendienteActivos,
                'onb_omit_tiempo_induccion' => $omitTiempoInduccion,
                'onb_dias_prom_induccion' => round($onbDiasPromInduccion, 1),
                'onb_pct_induccion_completa' => $onbPctInduccionCompleta,
                'op_agentes_call' => $opAgentesCall,
                'op_supervisores' => $opSupervisores,
                'op_ratio_str' => $opRatioStr,
                'op_depto_top_nombre' => $opDeptoTopNombre,
                'op_depto_top_cnt' => $opDeptoTopCnt,
            ];

            return self::resultado(true, 'OK', $datos);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al calcular estadísticas.', null, $e->getMessage());
        }
    }

    /**
     * Agrupa filas [{nombre, cnt}, ...] en máximo $maxSlices segmentos (último = «Otros»).
     *
     * @param list<array<string, mixed>> $rows
     * @return list<array{nombre: string, cnt: int}>
     */
    private static function bucketDepartamentoRows(array $rows, int $maxSlices = 20): array
    {
        $maxSlices = max(2, $maxSlices);
        $out = [];
        $n = count($rows);
        if ($n === 0) {
            return [];
        }
        if ($n <= $maxSlices) {
            foreach ($rows as $r) {
                $out[] = [
                    'nombre' => (string) ($r['nombre'] ?? '—'),
                    'cnt' => (int) ($r['cnt'] ?? 0),
                ];
            }

            return $out;
        }
        for ($i = 0; $i < $maxSlices - 1; $i++) {
            $r = $rows[$i];
            $out[] = [
                'nombre' => (string) ($r['nombre'] ?? '—'),
                'cnt' => (int) ($r['cnt'] ?? 0),
            ];
        }
        $otros = 0;
        for ($i = $maxSlices - 1; $i < $n; $i++) {
            $otros += (int) ($rows[$i]['cnt'] ?? 0);
        }
        if ($otros > 0) {
            $out[] = ['nombre' => 'Otros', 'cnt' => $otros];
        }

        return $out;
    }

    /**
     * Desglose por departamento de ingresos, bajas o reingresos en el mismo rango que el panel.
     * Departamento = del puesto de la última fila de asigna_puesto por persona (MAX id).
     *
     * @return array{success: bool, datos?: array<string, mixed>, error?: string}
     */
    public static function getMovimientoPorDepartamento(string $tipo, int $anio, int $mes, int $semana, ?string $fechaIniCal = null, ?string $fechaFinCal = null): array
    {
        $tipo = strtolower(trim($tipo));
        if (!in_array($tipo, ['ingresos', 'bajas', 'reingresos'], true)) {
            return self::resultado(false, 'Tipo no válido. Use ingresos, bajas o reingresos.', null);
        }
        try {
            $db = new Database();
            $rango = self::rangoDesdeCalendario($fechaIniCal, $fechaFinCal)
                ?? self::rangoMesSemana($anio, $mes, $semana);
            $fi = $rango['fecha_ini'];
            $ff = $rango['fecha_fin'];
            $params = ['fi' => $fi, 'ff' => $ff];

            $tp = self::tblBd('persona');
            $tap = self::tblBd('asigna_puesto');
            $tpu = self::tblBd('puesto');
            $td = self::tblBd('departamento');
            $tbp = self::tblBd('baja_persona');
            $trg = self::tblBd('reingresos');
            $sqlExFantasma = UsuarioFantasmaReporteria::sqlExcluirPersona('p');

            $sqlJoinUltPuestoDepto = '
                 LEFT JOIN (
                     SELECT apx.id_persona, apx.id_puesto
                     FROM ' . $tap . ' apx
                     INNER JOIN (
                         SELECT id_persona, MAX(id) AS mid
                         FROM ' . $tap . '
                         GROUP BY id_persona
                     ) apm ON apm.id_persona = apx.id_persona AND apm.mid = apx.id
                 ) ap ON ap.id_persona = p.id
                 LEFT JOIN ' . $tpu . ' pu ON pu.id = ap.id_puesto
                 LEFT JOIN ' . $td . ' d ON d.id = pu.departamento_id';

            if ($tipo === 'ingresos') {
                $sql = 'SELECT COALESCE(NULLIF(TRIM(d.nombre), \'\'), \'Sin departamento\') AS nombre, COUNT(DISTINCT p.id) AS cnt
                    FROM ' . $tp . ' p' . $sqlJoinUltPuestoDepto . '
                    WHERE p.fecha_ingreso IS NOT NULL
                      AND p.fecha_ingreso NOT IN (\'0000-00-00\',\'0000-00-00 00:00:00\')
                      AND DATE(p.fecha_ingreso) BETWEEN :fi AND :ff' . $sqlExFantasma . '
                    GROUP BY d.id, d.nombre
                    ORDER BY cnt DESC';
            } elseif ($tipo === 'bajas') {
                $sql = 'SELECT COALESCE(NULLIF(TRIM(d.nombre), \'\'), \'Sin departamento\') AS nombre, COUNT(*) AS cnt
                    FROM ' . $tbp . ' bp
                    INNER JOIN ' . $tp . ' p ON p.id = bp.id_persona' . $sqlJoinUltPuestoDepto . '
                    WHERE DATE(bp.fecha_baja) BETWEEN :fi AND :ff' . $sqlExFantasma . '
                    GROUP BY d.id, d.nombre
                    ORDER BY cnt DESC';
            } else {
                $sql = 'SELECT COALESCE(NULLIF(TRIM(d.nombre), \'\'), \'Sin departamento\') AS nombre, COUNT(*) AS cnt
                    FROM ' . $trg . ' r
                    INNER JOIN ' . $tp . ' p ON p.id = r.id_persona' . $sqlJoinUltPuestoDepto . '
                    WHERE DATE(r.fecha_reingreso) BETWEEN :fi AND :ff' . $sqlExFantasma . '
                    GROUP BY d.id, d.nombre
                    ORDER BY cnt DESC';
            }

            $rows = self::queryAllSafe($db, $sql, $params);
            $porDepto = self::bucketDepartamentoRows($rows, 20);
            $total = 0;
            foreach ($porDepto as $item) {
                $total += (int) ($item['cnt'] ?? 0);
            }

            return self::resultado(true, 'OK', [
                'tipo' => $tipo,
                'fecha_ini' => $fi,
                'fecha_fin' => $ff,
                'total' => $total,
                'por_departamento' => $porDepto,
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al calcular el desglose.', null, $e->getMessage());
        }
    }
}
