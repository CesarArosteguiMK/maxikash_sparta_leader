<?php

namespace Models;

use Core\DatabaseSegundometro;

/**
 * Comparativo segundómetro: dos semanas históricas + semana actual por cortes del día.
 * Semana operativa: martes → lunes (el lunes sigue en la misma “semana” que empezó el martes anterior).
 * Lógica alineada al servicio FastAPI (tbl_segundometro_histo / tbl_segundometro_semana).
 */
final class SegundometroComparativaSemanal
{
    /** @var list<array{orden:int,clave:string,label:string}> */
    private const HORAS = [
        ['orden' => 1, 'clave' => '07_30', 'label' => '07:30 a.m.'],
        ['orden' => 2, 'clave' => '09_30', 'label' => '09:30 a.m.'],
        ['orden' => 3, 'clave' => '11_30', 'label' => '11:30 a.m.'],
        ['orden' => 4, 'clave' => '13_30', 'label' => '01:30 p.m.'],
        ['orden' => 5, 'clave' => '14_30', 'label' => '02:30 p.m.'],
        ['orden' => 6, 'clave' => '16_30', 'label' => '04:30 p.m.'],
        ['orden' => 7, 'clave' => '18_30', 'label' => '06:30 p.m.'],
        ['orden' => 8, 'clave' => '20_30', 'label' => '08:30 p.m.'],
        ['orden' => 9, 'clave' => '23_50', 'label' => '11:50 p.m.'],
    ];

    /** ISO día 1 = lunes … 7 = domingo (coherente con Python weekday + 1). */
    private const PREFIJOS_N = [
        1 => 'Lunes',
        2 => 'Martes',
        3 => 'Miercoles',
        4 => 'Jueves',
        5 => 'Viernes',
        6 => 'Sabado',
        7 => 'Domingo',
    ];

    /**
     * @return array{dia:string,fecha_referencia:string,fecha_min:string,fecha_max:string,hoy_calendario_cdmx:string,es_hoy:bool,semana_actual:string,semanas:list<string>,semanas_display:list<string>,datos:list<array<string,mixed>>,tiene_prev:bool}
     */
    public static function calcular(?string $fechaParam, ?string $hoyCalendarioMxYmd = null): array
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $hoy = self::resolverHoyCalendarioCdmx($tz, $hoyCalendarioMxYmd);

        $fechaRef = $hoy;
        if ($fechaParam !== null && trim($fechaParam) !== '') {
            $raw = substr(trim($fechaParam), 0, 10);
            // Forzamos 00:00:00 para comparar solo fecha calendario contra $hoy (también a medianoche).
            $fechaRef = \DateTimeImmutable::createFromFormat('!Y-m-d', $raw, $tz);
            if ($fechaRef === false || $fechaRef->format('Y-m-d') !== $raw) {
                throw new \InvalidArgumentException('fecha debe ser YYYY-MM-DD');
            }
        }

        $martesEsta = self::martesInicioSemanaNegocio($hoy);
        $martesMin = $martesEsta->modify('-7 days');
        if ($fechaRef < $martesMin || $fechaRef > $hoy) {
            throw new \InvalidArgumentException(
                'La fecha solo puede ser entre el martes de inicio de la semana anterior y hoy (' . $martesMin->format('Y-m-d') . ' — ' . $hoy->format('Y-m-d') . ').'
            );
        }

        $n = (int) $fechaRef->format('N');
        $diaAyerN = $n === 1 ? 7 : $n - 1;
        $prefijoRef = self::PREFIJOS_N[$n];
        $prefijoAyer = self::PREFIJOS_N[$diaAyerN];

        $cols = self::columnasFetch($prefijoRef, $prefijoAyer);

        $db = new DatabaseSegundometro();

        // Las 3 columnas se anclan a la semana del día seleccionado (fechaRef), no siempre a la de "hoy".
        $tHoy = self::martesInicioSemanaNegocio($hoy);
        $tActual = self::martesInicioSemanaNegocio($fechaRef);
        $s1d = $tActual->modify('-7 days');
        $s2d = $tActual->modify('-14 days');
        $s1 = self::etiquetaSemanaIso($s1d);
        $s2 = self::etiquetaSemanaIso($s2d);
        $sa = self::etiquetaSemanaIso($tActual);

        $r1 = self::leerHisto($db, $s1, $cols);
        $r2 = self::leerHisto($db, $s2, $cols);
        // Solo usamos la tabla "semana" viva cuando realmente estamos parados en la semana actual de calendario.
        // Si el usuario navega a semanas anteriores, la columna "Actual" debe salir del histórico de esa semana.
        $ract = $tActual->format('Y-m-d') === $tHoy->format('Y-m-d')
            ? self::leerActual($db, $cols)
            : self::leerHisto($db, $sa, $cols);

        $prev1 = $r1[0];
        $c1 = array_slice($r1, 1);
        $prev2 = $r2[0];
        $c2 = array_slice($r2, 1);
        $prevAc = $ract[0];
        $actual = array_slice($ract, 1);

        $cob1 = self::cobrado($c1, $prev1);
        $cob2 = self::cobrado($c2, $prev2);
        $cobAc = self::cobrado($actual, $prevAc);

        $filas = [];
        foreach (self::HORAS as $i => $h) {
            // Orden cronológico en columnas: semana más antigua ($s2) → más reciente histórica ($s1) → actual.
            $filas[] = [
                'orden' => $h['orden'],
                'hora' => $h['clave'],
                'creditos_' . $s2 => $c2[$i],
                'cobrado_' . $s2 => $cob2[$i],
                'creditos_' . $s1 => $c1[$i],
                'cobrado_' . $s1 => $cob1[$i],
                'creditos_actual' => $actual[$i],
                'cobrado_actual' => $cobAc[$i],
            ];
        }

        return [
            'dia' => $prefijoRef,
            'fecha_referencia' => $fechaRef->format('Y-m-d'),
            'fecha_min' => $martesMin->format('Y-m-d'),
            'fecha_max' => $hoy->format('Y-m-d'),
            'hoy_calendario_cdmx' => $hoy->format('Y-m-d'),
            'es_hoy' => $fechaRef->format('Y-m-d') === $hoy->format('Y-m-d'),
            'semana_actual' => $sa,
            'semanas' => [$s2, $s1, 'Actual'],
            'semanas_display' => [
                self::etiquetaRangoSemana($s2d),
                self::etiquetaRangoSemana($s1d),
                self::etiquetaRangoSemana($tActual),
            ],
            'datos' => $filas,
            'tiene_prev' => true,
        ];
    }

    /**
     * Día calendario "hoy" en CDMX: preferir Y-m-d enviado por el cliente (Intl + America/Mexico_City)
     * para no depender del reloj del servidor; si no viene, usar reloj del servidor en esa zona.
     */
    private static function resolverHoyCalendarioCdmx(\DateTimeZone $tz, ?string $hoyCalendarioMxYmd): \DateTimeImmutable
    {
        if ($hoyCalendarioMxYmd !== null && trim($hoyCalendarioMxYmd) !== '') {
            $raw = substr(trim($hoyCalendarioMxYmd), 0, 10);
            $parsed = \DateTimeImmutable::createFromFormat('Y-m-d', $raw, $tz);
            if ($parsed === false || $parsed->format('Y-m-d') !== $raw) {
                throw new \InvalidArgumentException('hoy_mx debe ser YYYY-MM-DD (calendario Ciudad de México).');
            }

            return new \DateTimeImmutable($raw . ' 00:00:00', $tz);
        }

        return new \DateTimeImmutable('today', $tz);
    }

    private static function etiquetaSemanaIso(\DateTimeImmutable $d): string
    {
        return sprintf('Semana %d-%d', (int) $d->format('W'), (int) $d->format('o'));
    }

    /**
     * Primer día (martes) de la semana operativa martes–lunes que contiene $cualquierDía.
     * N ISO: 1=lunes … 7=domingo.
     */
    private static function martesInicioSemanaNegocio(\DateTimeImmutable $cualquierDia): \DateTimeImmutable
    {
        $n = (int) $cualquierDia->format('N');
        $retroceder = $n === 1 ? 6 : ($n - 2);

        return $cualquierDia->modify('-' . $retroceder . ' days');
    }

    /** Rango martes–lunes (7 días de la semana operativa). */
    private static function etiquetaRangoSemana(\DateTimeImmutable $cualquierDiaDeLaSemana): string
    {
        $martes = self::martesInicioSemanaNegocio($cualquierDiaDeLaSemana);
        $lunesFin = $martes->modify('+6 days');

        return $martes->format('d/m/Y') . ' - ' . $lunesFin->format('d/m/Y');
    }

    /**
     * @return list<string>
     */
    private static function columnasFetch(string $prefijoRef, string $prefijoAyer): array
    {
        if (!in_array($prefijoRef, self::PREFIJOS_N, true) || !in_array($prefijoAyer, self::PREFIJOS_N, true)) {
            throw new \InvalidArgumentException('Prefijo de día no válido');
        }
        $cols = ['Dias_mora_' . $prefijoAyer . '_23_50'];
        foreach (self::HORAS as $h) {
            $cols[] = 'Dias_mora_' . $prefijoRef . '_' . $h['clave'];
        }

        return $cols;
    }

    /**
     * @param list<string> $columnas
     * @return list<int>
     */
    private static function leerHisto(DatabaseSegundometro $db, string $semana, array $columnas): array
    {
        $parts = [];
        foreach ($columnas as $i => $col) {
            if (!preg_match('/^Dias_mora_[A-Za-z]+_[0-9]{2}_[0-9]{2}$/', $col)) {
                throw new \InvalidArgumentException('Columna no permitida');
            }
            $parts[] = 'COALESCE(COUNT(DISTINCT CASE WHEN `' . $col . '` BETWEEN 1 AND 7 THEN Id_credito ELSE NULL END), 0) AS k' . $i;
        }
        $sql = 'SELECT ' . implode(', ', $parts) . ' FROM `tbl_segundometro_histo` WHERE SEMANA = :sem';
        $row = $db->queryOne($sql, ['sem' => $semana]);
        if ($row === null) {
            return array_fill(0, count($columnas), 0);
        }
        $out = [];
        foreach (array_keys($columnas) as $i) {
            $out[] = (int) ($row['k' . $i] ?? 0);
        }

        return $out;
    }

    /**
     * @param list<string> $columnas
     * @return list<int>
     */
    private static function leerActual(DatabaseSegundometro $db, array $columnas): array
    {
        $parts = [];
        foreach ($columnas as $i => $col) {
            if (!preg_match('/^Dias_mora_[A-Za-z]+_[0-9]{2}_[0-9]{2}$/', $col)) {
                throw new \InvalidArgumentException('Columna no permitida');
            }
            $parts[] = 'COALESCE(COUNT(DISTINCT CASE WHEN `' . $col . '` BETWEEN 1 AND 7 THEN Id_credito ELSE NULL END), 0) AS k' . $i;
        }
        $sql = 'SELECT ' . implode(', ', $parts) . ' FROM `tbl_segundometro_semana`';
        $row = $db->queryOne($sql, null);
        if ($row === null) {
            return array_fill(0, count($columnas), 0);
        }
        $out = [];
        foreach (array_keys($columnas) as $i) {
            $out[] = (int) ($row['k' . $i] ?? 0);
        }

        return $out;
    }

    /**
     * @param list<int> $creditos
     * @return list<int|null>
     */
    private static function cobrado(array $creditos, int $prev): array
    {
        $primer = $prev - $creditos[0];
        $out = [$primer];
        $n = count($creditos);
        for ($i = 1; $i < $n; $i++) {
            $out[] = $creditos[$i - 1] - $creditos[$i];
        }

        return $out;
    }
}
