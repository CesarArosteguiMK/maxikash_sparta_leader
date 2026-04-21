<?php

namespace Models;

use Core\Database;
use Core\DatabaseLegacy;

/**
 * Datos del tablero Asignación (ventanas mar–lun) compartidos por vista, JSON y Excel.
 */
class AsignacionTablero
{
    /** @var list<array{key:string,text:string,align:string}> */
    public const SUBCOLS = [
        ['key' => 'ext', 'text' => 'External ID', 'align' => 'text-start'],
        ['key' => 'nom', 'text' => 'Nombre del gestor', 'align' => 'text-start'],
        ['key' => 'pue', 'text' => 'Puesto', 'align' => 'text-start'],
    ];

    /** @return array{semanas:list<array<string,mixed>>,subcols:list<array<string,string>>} */
    public static function obtenerSemanasTablero(): array
    {
        $martesInicio = static function (\DateTimeImmutable $dia): \DateTimeImmutable {
            $n = (int) $dia->format('N');
            if ($n === 1) {
                return $dia->modify('-6 days');
            }

            return $dia->modify('-' . ($n - 2) . ' days');
        };

        $rangoDmY = static function (\DateTimeImmutable $martes): string {
            $lunes = $martes->modify('+6 days');

            return $martes->format('d/m/Y') . ' - ' . $lunes->format('d/m/Y');
        };

        $labelIso = static function (\DateTimeImmutable $martes): string {
            $jueves = $martes->modify('+2 days');
            $num = (int) $jueves->format('W');
            $anioIso = (int) $jueves->format('o');

            return 'Semana ' . $num . '-' . $anioIso;
        };

        try {
            $tz = new \DateTimeZone('America/Mexico_City');
        } catch (\Exception $e) {
            $tz = null;
        }
        $hoy = new \DateTimeImmutable('today', $tz);
        $martesActual = $martesInicio($hoy);

        /** Tres ventanas: semana pasada (mar–lun anterior), actual, próxima */
        $defs = [
            ['dias' => -7, 'th_class' => 'comp-th-hist', 'hist_level' => 1, 'chip_prefijo' => 'Semana pasada'],
            ['dias' => 0, 'th_class' => 'comp-th-act', 'hist_level' => 0, 'chip_prefijo' => 'Actual'],
            ['dias' => 7, 'th_class' => 'comp-th-fut', 'hist_level' => 0, 'chip_prefijo' => 'Próxima'],
        ];
        $semanas = [];
        foreach ($defs as $def) {
            $tue = $martesActual->modify(sprintf('%+d days', (int) $def['dias']));
            $rango = $rangoDmY($tue);
            $semanas[] = [
                'label' => $labelIso($tue),
                'range' => $rango,
                'chip_text' => $def['chip_prefijo'] . ': ' . $rango,
                'th_class' => $def['th_class'],
                'hist_level' => (int) $def['hist_level'],
            ];
        }

        return ['semanas' => $semanas, 'subcols' => self::SUBCOLS];
    }

    /**
     * Tablero de dos ventanas mar–lun (misma grilla que el tablero de tres, sin la primera columna histórica):
     * - «Semana pasada» = datos de la columna que en el tablero completo es la semana actual (asignación vigente).
     * - «Semana actual» = datos de la columna que en el tablero completo es la semana próxima (proyección).
     *
     * @param array $portafolio Resultado de obtenerPortafolioAutomatico()
     * @return array Portafolio con semanas y cells de longitud 2; resumen y campanías se conservan.
     */
    public static function portafolioDosVentanasDesdeCompleto(array $portafolio): array
    {
        $semanasFull = is_array($portafolio['semanas'] ?? null) ? $portafolio['semanas'] : [];
        $filasFull = is_array($portafolio['filas'] ?? null) ? $portafolio['filas'] : [];
        $subcols = is_array($portafolio['subcols'] ?? null) ? $portafolio['subcols'] : self::SUBCOLS;
        $resumen = is_array($portafolio['resumen'] ?? null) ? $portafolio['resumen'] : [];
        $campanias = is_array($portafolio['campanias'] ?? null) ? $portafolio['campanias'] : [];

        if (count($semanasFull) < 3) {
            return [
                'semanas' => [],
                'subcols' => $subcols,
                'filas' => [],
                'resumen' => $resumen,
                'campanias' => $campanias,
            ];
        }

        $sAct = $semanasFull[1];
        $sProx = $semanasFull[2];
        $sem0 = array_merge($sAct, [
            'chip_text' => 'Semana pasada: ' . (string) ($sAct['range'] ?? ''),
            'th_class' => 'comp-th-hist',
            'hist_level' => 1,
        ]);
        $sem1 = array_merge($sProx, [
            'chip_text' => 'Actual: ' . (string) ($sProx['range'] ?? ''),
            'th_class' => 'comp-th-act',
            'hist_level' => 0,
        ]);
        $semanas = [$sem0, $sem1];

        $filas = [];
        foreach ($filasFull as $f) {
            $cellsFull = is_array($f['cells'] ?? null) ? $f['cells'] : [];
            $c0 = $cellsFull[1] ?? ['ext' => '—', 'nom' => '—', 'pue' => '—'];
            $c1 = $cellsFull[2] ?? ['ext' => '—', 'nom' => '—', 'pue' => '—'];
            $filas[] = [
                'id_credito' => $f['id_credito'] ?? '',
                'cells' => [$c0, $c1],
                'meta' => is_array($f['meta'] ?? null) ? $f['meta'] : [],
            ];
        }

        return [
            'semanas' => $semanas,
            'subcols' => $subcols,
            'filas' => $filas,
            'resumen' => $resumen,
            'campanias' => $campanias,
        ];
    }

    /**
     * Servicio de portafolio automático:
     * - Semana pasada = gestor histórico (campaña anterior)
     * - Semana actual = asignación vigente (no se sustituye automáticamente)
     * - Próxima = proyección (sí puede mostrar reasignación por ausencia/baja/incapacidad)
     *
     * @return array{
     *   semanas:list<array<string,mixed>>,
     *   subcols:list<array<string,string>>,
     *   filas:list<array{
     *      id_credito:string,
     *      cells:list<array{ext:string,nom:string,pue:string}>,
     *      meta:array<string,mixed>
     *   }>,
     *   resumen:array<string,int>,
     *   campanias:array<string,mixed>
     * }
     */
    public static function obtenerPortafolioAutomatico(): array
    {
        $base = self::obtenerSemanasTablero();
        $out = [
            'semanas' => $base['semanas'],
            'subcols' => $base['subcols'],
            'filas' => [],
            'resumen' => ['total' => 0, 'continuidad' => 0, 'nuevo' => 0, 'huerfano' => 0, 'sin_jefe' => 0],
            'campanias' => ['actual' => [], 'anterior' => []],
        ];

        $dbLegacy = new DatabaseLegacy();
        $dbSeg = new Database();

        $campanias = self::obtenerDosCampanias($dbLegacy);
        if (count($campanias) < 2) {
            return $out;
        }
        $campActual = array_values(array_filter($campanias, static function (array $c): bool {
            return (int) ($c['semana_rank'] ?? 0) === 1;
        }));
        $campAnterior = array_values(array_filter($campanias, static function (array $c): bool {
            return (int) ($c['semana_rank'] ?? 0) === 2;
        }));
        if ($campActual === [] || $campAnterior === []) {
            return $out;
        }
        $out['campanias'] = ['actual' => $campActual, 'anterior' => $campAnterior];

        $idsActual = array_values(array_map(static fn(array $c): int => (int) ($c['campaign_id'] ?? 0), $campActual));
        $idsAnterior = array_values(array_map(static fn(array $c): int => (int) ($c['campaign_id'] ?? 0), $campAnterior));
        $idsCampanias = array_values(array_unique(array_filter(array_merge($idsActual, $idsAnterior), static fn(int $id): bool => $id > 0)));

        $tareas = self::obtenerTareasCampanias($dbLegacy, $idsCampanias);
        if ($tareas === []) {
            return $out;
        }

        $usuariosLegacy = self::obtenerUsuariosLegacy($dbLegacy);
        $personas = self::obtenerPersonasSegundometro($dbSeg);

        $creditosAnterior = [];
        $creditosActual = [];
        foreach ($tareas as $t) {
            $campId = (int) ($t['campaign_id'] ?? 0);
            $credito = trim((string) ($t['credit_number'] ?? ''));
            if ($credito === '') {
                continue;
            }
            if (in_array($campId, $idsAnterior, true) && !isset($creditosAnterior[$credito])) {
                $creditosAnterior[$credito] = $t;
            } elseif (in_array($campId, $idsActual, true) && !isset($creditosActual[$credito])) {
                $creditosActual[$credito] = $t;
            }
        }
        if ($creditosActual === []) {
            return $out;
        }

        $listaCreditos = array_keys($creditosActual);
        usort($listaCreditos, static function (string $a, string $b): int {
            return strnatcmp($a, $b);
        });

        foreach ($listaCreditos as $credito) {
            $rowActual = $creditosActual[$credito];
            $rowAnterior = $creditosAnterior[$credito] ?? null;

            $asigPasada = self::aplicarPuestoLegacySegundometro(
                self::resolverAsignacionUsuarioLegacy($rowAnterior, $usuariosLegacy),
                $personas
            );
            $asigActualBase = self::aplicarPuestoLegacySegundometro(
                self::resolverAsignacionUsuarioLegacy($rowActual, $usuariosLegacy),
                $personas
            );
            $esNuevo = $rowAnterior === null;
            $tipo = $esNuevo ? 'NUEVO' : 'CONTINUIDAD';

            // Regla de oro: match Segundometro ↔ Legacy por external_id del usuario en tasks (users.id → users.external_id → persona.numero_empleado).
            // Para disponibilidad se evalúa el gestor vigente de la semana actual.
            $asigParaReglas = $asigActualBase;
            $asigActual = $asigActualBase;
            $asigProxima = $asigActualBase;
            $reasignadoJefe = false;
            $sinJefe = false;
            $validPuestoGestor = true;
            $validPuestoJefe = true;
            $sinMatchSegundometro = false;
            $ausenciaActivaSi = false;
            $motivoCambio = 'Sin cambios';

            if ($asigParaReglas !== null) {
                $diag = self::evaluarDisponibilidadYJerarquia($asigParaReglas, $personas, $usuariosLegacy);
                $validPuestoGestor = (bool) ($diag['gestor_puesto_ok'] ?? true);
                $sinMatchSegundometro = (bool) ($diag['sin_match_segundometro'] ?? false);
                $ausenciaActivaSi = (bool) ($diag['ausencia_activa'] ?? false);
                if (!empty($diag['no_disponible'])) {
                    $tipo = 'HUERFANO';
                    if (isset($diag['jefe']) && is_array($diag['jefe'])) {
                        // Regla solicitada: semana actual no se sustituye; solo se proyecta el cambio a próxima.
                        $asigProxima = $diag['jefe'];
                        $reasignadoJefe = true;
                        $validPuestoJefe = (bool) ($diag['jefe_puesto_ok'] ?? true);
                        $motivoCambio = (string) ($diag['motivo_cambio'] ?? 'Reasignación automática');
                    } else {
                        $sinJefe = true;
                        $motivoCambio = (string) ($diag['motivo_cambio'] ?? 'Gestor no disponible y sin jefe asignado');
                    }
                }
                if ($tipo !== 'HUERFANO' && $esNuevo) {
                    $motivoCambio = 'Nuevo ingreso al portafolio';
                }
            } else {
                // Sin fila en __SPARTA_SECRET_REDACTED__.users para current_user_id: no hay external_id ni reglas Segundometro.
                $sinMatchSegundometro = true;
                $validPuestoGestor = false;
                $motivoCambio = 'Sin match de gestor en Segundómetro';
            }

            // Semana pasada: sin campaña anterior → indicación NUEVO (External ID); nombre y puesto en —.
            $celdaPasada = self::toCell($asigPasada, $esNuevo ? ['ext' => 'NUEVO', 'nom' => '—', 'pue' => '—'] : null);
            $celdaActual = self::toCell($asigActual, null);
            $celdaProxima = self::toCell($asigProxima, null);
            $hayCambioProxima = $celdaActual['ext'] !== $celdaProxima['ext'] || $celdaActual['nom'] !== $celdaProxima['nom'] || $celdaActual['pue'] !== $celdaProxima['pue'];
            if (!$hayCambioProxima && $tipo !== 'HUERFANO' && $esNuevo) {
                $motivoCambio = 'Nuevo ingreso al portafolio';
            } elseif (!$hayCambioProxima && $motivoCambio === '') {
                $motivoCambio = 'Sin cambios';
            }

            $out['filas'][] = [
                'id_credito' => $credito,
                'cells' => [$celdaPasada, $celdaActual, $celdaProxima],
                'meta' => [
                    'tipo' => $tipo,
                    'reasignado_jefe' => $reasignadoJefe,
                    'sin_jefe' => $sinJefe,
                    'puesto_legacy_valido_gestor' => $validPuestoGestor,
                    'puesto_legacy_valido_jefe' => $validPuestoJefe,
                    'puesto_coincide_jerarquia' => $validPuestoGestor,
                    'match_primario_segundometro' => $asigParaReglas !== null && !$sinMatchSegundometro,
                    'sin_match_segundometro' => $sinMatchSegundometro,
                    'ausencia_activa' => $ausenciaActivaSi,
                    'hay_cambio_proxima' => $hayCambioProxima,
                    'motivo_cambio' => $motivoCambio,
                ],
            ];

            $out['resumen']['total']++;
            if ($tipo === 'NUEVO') {
                $out['resumen']['nuevo']++;
            } elseif ($tipo === 'HUERFANO') {
                $out['resumen']['huerfano']++;
                if ($sinJefe) {
                    $out['resumen']['sin_jefe']++;
                }
            } else {
                $out['resumen']['continuidad']++;
            }
        }

        return $out;
    }

    /**
     * Obtiene campañas de las últimas 2 semanas (pueden ser 2+ campañas por semana).
     *
     * @return list<array{
     *   campaign_id:int,
     *   campaign_name:string,
     *   numero_semana:int,
     *   semana_iso:int,
     *   semana_rank:int
     * }>
     */
    private static function obtenerDosCampanias(DatabaseLegacy $db): array
    {
        $sql = "
            WITH campanias_filtradas AS (
                SELECT
                    c.id,
                    c.name,
                    c.start_date,
                    WEEK(c.start_date, 1) AS numero_semana,
                    YEARWEEK(c.start_date, 1) AS semana_iso
                FROM campaigns c
                WHERE c.name NOT LIKE '%ESPEJO%'
                  AND c.name NOT LIKE 'ESP_%'
                  AND c.name NOT LIKE '%SUPERVISORES%'
                  AND c.start_date IS NOT NULL
                  AND (SELECT COUNT(*) FROM tasks t WHERE t.campaign_id = c.id) >= 100
            ),
            ranking AS (
                SELECT
                    id,
                    name,
                    numero_semana,
                    semana_iso,
                    DENSE_RANK() OVER (ORDER BY semana_iso DESC) AS semana_rank,
                    start_date
                FROM campanias_filtradas
            )
            SELECT
                id AS campaign_id,
                name AS campaign_name,
                numero_semana,
                semana_iso,
                semana_rank
            FROM ranking
            WHERE semana_rank <= 2
            ORDER BY semana_rank ASC, start_date DESC, campaign_id DESC
        ";
        $rows = $db->queryAll($sql);
        return is_array($rows) ? $rows : [];
    }

    /**
     * @param list<int> $ids
     * @return list<array{credit_number:string,current_user_id:int,campaign_id:int,campaign_name:string}>
     */
    private static function obtenerTareasCampanias(DatabaseLegacy $db, array $ids): array
    {
        if (count($ids) < 2) {
            return [];
        }
        $placeholders = [];
        $params = [];
        foreach (array_values($ids) as $i => $id) {
            $k = 'id' . $i;
            $placeholders[] = ':' . $k;
            $params[$k] = (int) $id;
        }
        if ($placeholders === []) {
            return [];
        }
        $sql = "
            SELECT
                t.credit_number,
                t.current_user_id,
                t.campaign_id,
                c.name AS campaign_name
            FROM tasks t
            INNER JOIN campaigns c ON c.id = t.campaign_id
            WHERE t.campaign_id IN (" . implode(', ', $placeholders) . ")
              AND t.credit_number IS NOT NULL
              AND t.credit_number <> ''
            ORDER BY c.start_date DESC, c.id DESC
        ";
        $rows = $db->queryAll($sql, $params);
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array{
     *   by_id:array<int,array<string,mixed>>,
     *   by_external:array<string,array<string,mixed>>
     * }
     */
    private static function obtenerUsuariosLegacy(DatabaseLegacy $db): array
    {
        $sql = "
            SELECT
                id,
                name,
                external_id,
                CASE
                    WHEN subdirector_id IS NOT NULL THEN 'SUBDIRECTOR'
                    WHEN gerente_id IS NOT NULL THEN 'GERENTE'
                    WHEN subgerente_id IS NOT NULL THEN 'SUBGERENTE'
                    WHEN supervisor_id IS NOT NULL THEN 'SUPERVISOR'
                    ELSE 'GESTOR / STAFF'
                END AS puesto_segun_jerarquia
            FROM users
            WHERE deleted_at IS NULL
        ";
        $rows = $db->queryAll($sql);
        $byId = [];
        $byExternal = [];
        foreach ((array) $rows as $r) {
            $id = (int) ($r['id'] ?? 0);
            $ext = self::normalizarExternalId($r['external_id'] ?? null);
            if ($id > 0) {
                $byId[$id] = $r;
            }
            if ($ext !== '') {
                $byExternal[$ext] = $r;
            }
        }

        return ['by_id' => $byId, 'by_external' => $byExternal];
    }

    /**
     * @return array{
     *   by_external:array<string,array<string,mixed>>,
     *   by_persona_id:array<int,array<string,mixed>>
     * }
     */
    private static function obtenerPersonasSegundometro(Database $db): array
    {
        $sql = "
            SELECT
                p.id AS id_persona,
                p.numero_empleado AS external_id_legacy,
                CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_gestor,
                pu.nombre AS puesto_gestor,
                pl.nombre AS puesto_gestor_legacy,
                pl.nombre AS puesto_legacy,
                p.estatus,
                CASE WHEN a.id IS NOT NULL THEN 'SI' ELSE 'NO' END AS ausencia_activa,
                ra.nombre AS razon_ausencia,
                j.id AS id_jefe,
                j.numero_empleado AS external_id_jefe_legacy,
                CONCAT_WS(' ', j.nombres, j.segundo_nombre, j.apellidop, j.apellidom) AS nombre_jefe,
                puj.nombre AS puesto_jefe,
                plj.nombre AS puesto_jefe_legacy,
                CASE WHEN j.estatus = 'Activo' THEN 'SI' ELSE 'NO' END AS jefe_activo
            FROM persona p
            LEFT JOIN asigna_puesto ap
                ON ap.id_persona = p.id AND ap.activo = 1
            LEFT JOIN puesto pu
                ON pu.id = ap.id_puesto
            LEFT JOIN equivalencias_legacy_puestos elp
                ON elp.id_puesto = pu.id
            LEFT JOIN puestos_legacy pl
                ON pl.id = elp.id_puesto_legacy
            LEFT JOIN ausencia a
                ON a.id_persona = p.id
               AND a.activo = 1
               AND NOW() BETWEEN a.fecha_inicio AND a.fecha_fin
            LEFT JOIN razon_ausencia ra ON ra.id = a.id_razon
            LEFT JOIN asigna_jefe aj
                ON aj.id_persona = p.id
               AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            LEFT JOIN persona j
                ON j.id = aj.id_jefe
            LEFT JOIN asigna_puesto apj
                ON apj.id_persona = j.id AND apj.activo = 1
            LEFT JOIN puesto puj
                ON puj.id = apj.id_puesto
            LEFT JOIN equivalencias_legacy_puestos elpj
                ON elpj.id_puesto = puj.id
            LEFT JOIN puestos_legacy plj
                ON plj.id = elpj.id_puesto_legacy
        ";
        $rows = $db->queryAll($sql);

        $byExternal = [];
        $byPersona = [];
        foreach ((array) $rows as $r) {
            $pid = (int) ($r['id_persona'] ?? 0);
            $ext = self::normalizarExternalId($r['external_id_legacy'] ?? null);
            if ($pid > 0 && !isset($byPersona[$pid])) {
                $byPersona[$pid] = $r;
            }
            if ($ext !== '' && !isset($byExternal[$ext])) {
                $byExternal[$ext] = $r;
            }
        }

        return ['by_external' => $byExternal, 'by_persona_id' => $byPersona];
    }

    /**
     * @param array<string,mixed>|null $taskRow
     * @param array{by_id:array<int,array<string,mixed>>,by_external:array<string,array<string,mixed>>} $usuariosLegacy
     * @return array<string,mixed>|null
     */
    private static function resolverAsignacionUsuarioLegacy(?array $taskRow, array $usuariosLegacy): ?array
    {
        if (!is_array($taskRow)) {
            return null;
        }
        $uid = (int) ($taskRow['current_user_id'] ?? 0);
        if ($uid < 1) {
            return null;
        }
        $u = $usuariosLegacy['by_id'][$uid] ?? null;
        if (!is_array($u)) {
            return null;
        }

        return [
            'legacy_user_id' => $uid,
            'external_id' => (string) ($u['external_id'] ?? ''),
            'nombre' => trim((string) ($u['name'] ?? '')),
            'puesto_legacy' => (string) ($u['puesto_segun_jerarquia'] ?? '—'),
        ];
    }

    /**
     * Match primario: external_id del usuario Legacy = numero_empleado (external_id_legacy) en Segundometro.
     * Doble validación: puesto legacy (Segundometro, equivalencias) vs puesto según jerarquía (users).
     * Huérfano: ausencia activa, baja o incapacidad → asignación al jefe (external_id del jefe → users).
     *
     * @param array<string,mixed>|null $asig
     * @param array{
     *   by_external:array<string,array<string,mixed>>,
     *   by_persona_id:array<int,array<string,mixed>>
     * } $personas
     * @param array{
     *   by_id:array<int,array<string,mixed>>,
     *   by_external:array<string,array<string,mixed>>
     * } $usuariosLegacy
     * @return array{
     *   no_disponible:bool,
     *   gestor_puesto_ok:bool,
     *   jefe_puesto_ok:bool,
     *   sin_match_segundometro:bool,
     *   ausencia_activa:bool,
     *   motivo_cambio:string,
     *   jefe?:array<string,mixed>
     * }
     */
    private static function evaluarDisponibilidadYJerarquia(?array $asig, array $personas, array $usuariosLegacy): array
    {
        $base = [
            'no_disponible' => false,
            'gestor_puesto_ok' => true,
            'jefe_puesto_ok' => true,
            'sin_match_segundometro' => false,
            'ausencia_activa' => false,
            'motivo_cambio' => 'Sin cambios',
        ];
        if (!is_array($asig)) {
            return $base;
        }

        $ext = self::normalizarExternalId($asig['external_id'] ?? null);
        if ($ext === '') {
            return array_merge($base, ['gestor_puesto_ok' => false, 'sin_match_segundometro' => true, 'motivo_cambio' => 'Sin match de gestor en Segundómetro']);
        }
        $p = $personas['by_external'][$ext] ?? null;
        if (!is_array($p)) {
            return array_merge($base, ['gestor_puesto_ok' => false, 'sin_match_segundometro' => true, 'motivo_cambio' => 'Sin match de gestor en Segundómetro']);
        }

        $resultado = $base;
        $resultado['sin_match_segundometro'] = false;

        $estatus = mb_strtoupper(trim((string) ($p['estatus'] ?? '')));
        $ausenciaActiva = mb_strtoupper(trim((string) ($p['ausencia_activa'] ?? 'NO'))) === 'SI';
        $resultado['ausencia_activa'] = $ausenciaActiva;
        $esBaja = str_contains($estatus, 'BAJA');
        $esIncapacidad = str_contains($estatus, 'INCAP');
        $resultado['no_disponible'] = $ausenciaActiva || $esBaja || $esIncapacidad;
        if ($resultado['no_disponible']) {
            if ($esBaja) {
                $resultado['motivo_cambio'] = 'Baja';
            } elseif ($esIncapacidad) {
                $resultado['motivo_cambio'] = 'Incapacidad';
            } elseif ($ausenciaActiva) {
                $razonAusencia = trim((string) ($p['razon_ausencia'] ?? ''));
                $resultado['motivo_cambio'] = $razonAusencia !== '' ? $razonAusencia : 'Permiso / Vacaciones';
            }
        }

        // Segundometro: nombre puesto legacy (equivalencias). Legacy users: puesto_segun_jerarquia (guardado en $asig['puesto_legacy']).
        $puestoLegacyPersona = self::normalizarTextoPuesto((string) ($p['puesto_gestor_legacy'] ?? $p['puesto_legacy'] ?? ''));
        $puestoJerarquiaUser = self::normalizarTextoPuesto((string) ($asig['puesto_legacy'] ?? ''));
        if ($puestoLegacyPersona !== '' && $puestoJerarquiaUser !== '') {
            $resultado['gestor_puesto_ok'] = $puestoLegacyPersona === $puestoJerarquiaUser;
        } elseif ($puestoLegacyPersona === '' xor $puestoJerarquiaUser === '') {
            $resultado['gestor_puesto_ok'] = false;
        }

        if (!$resultado['no_disponible']) {
            return $resultado;
        }

        $idJefe = (int) ($p['id_jefe'] ?? 0);
        if ($idJefe < 1) {
            return $resultado;
        }
        $jefePersona = $personas['by_persona_id'][$idJefe] ?? null;
        if (!is_array($jefePersona)) {
            return $resultado;
        }
        $jefeExt = self::normalizarExternalId($jefePersona['external_id_legacy'] ?? $jefePersona['external_id_jefe_legacy'] ?? null);
        $jefeLegacy = $jefeExt !== '' ? ($usuariosLegacy['by_external'][$jefeExt] ?? null) : null;

        $jefeAsignacion = [
            'legacy_user_id' => isset($jefeLegacy['id']) ? (int) $jefeLegacy['id'] : 0,
            'external_id' => $jefeExt,
            'nombre' => trim((string) ($jefeLegacy['name'] ?? $jefePersona['nombre_gestor'] ?? $p['nombre_jefe'] ?? '—')),
            'puesto_legacy' => (string) ($jefeLegacy['puesto_segun_jerarquia'] ?? $jefePersona['puesto_legacy'] ?? $p['puesto_jefe_legacy'] ?? '—'),
        ];
        $resultado['jefe'] = $jefeAsignacion;

        $puestoJefePersona = self::normalizarTextoPuesto((string) ($p['puesto_jefe_legacy'] ?? ''));
        $puestoJefeLegacy = self::normalizarTextoPuesto((string) ($jefeAsignacion['puesto_legacy'] ?? ''));
        if ($puestoJefePersona !== '' && $puestoJefeLegacy !== '') {
            $resultado['jefe_puesto_ok'] = $puestoJefePersona === $puestoJefeLegacy;
        } elseif ($puestoJefePersona === '' xor $puestoJefeLegacy === '') {
            $resultado['jefe_puesto_ok'] = false;
        }

        return $resultado;
    }

    /** Comparación insensible a mayúsculas y espacios para etiquetas de puesto. */
    private static function normalizarTextoPuesto(string $s): string
    {
        $s = mb_strtoupper(trim($s));
        if ($s === '') {
            return '';
        }
        $t = preg_replace('/\s+/u', ' ', $s);

        return is_string($t) ? $t : $s;
    }

    /**
     * Usa el puesto legacy proveniente de Segundómetro (equivalencias) para la persona match por external_id.
     * Esto evita mostrar el puesto por jerarquía de Legacy (p. ej. SUPERVISOR) cuando la equivalencia del gestor es otra.
     *
     * @param ?array<string,mixed> $asig
     * @param array{by_external:array<string,array<string,mixed>>,by_persona_id:array<int,array<string,mixed>>} $personas
     * @return ?array<string,mixed>
     */
    private static function aplicarPuestoLegacySegundometro(?array $asig, array $personas): ?array
    {
        if (!is_array($asig)) {
            return $asig;
        }

        $ext = self::normalizarExternalId($asig['external_id'] ?? null);
        if ($ext === '') {
            return $asig;
        }

        $p = $personas['by_external'][$ext] ?? null;
        if (!is_array($p)) {
            return $asig;
        }

        $puestoSeg = trim((string) ($p['puesto_gestor_legacy'] ?? $p['puesto_legacy'] ?? ''));
        if ($puestoSeg !== '') {
            $asig['puesto_legacy'] = $puestoSeg;
        }

        return $asig;
    }

    /**
     * @param array<string,mixed>|null $asig
     * @param array{ext:string,nom:string,pue:string}|null $fallback
     * @return array{ext:string,nom:string,pue:string}
     */
    private static function toCell(?array $asig, ?array $fallback): array
    {
        if (!is_array($asig)) {
            return $fallback ?? ['ext' => '—', 'nom' => '—', 'pue' => '—'];
        }

        $ext = trim((string) ($asig['external_id'] ?? ''));
        $nom = trim((string) ($asig['nombre'] ?? ''));
        $pue = trim((string) ($asig['puesto_legacy'] ?? ''));

        return [
            'ext' => $ext !== '' ? $ext : '—',
            'nom' => $nom !== '' ? $nom : '—',
            'pue' => $pue !== '' ? $pue : '—',
        ];
    }

    private static function normalizarExternalId($v): string
    {
        if ($v === null) {
            return '';
        }
        return trim((string) $v);
    }

    /**
     * Límite de filas en tablero/export: 10, 50, 100 o null = todas.
     * Si $raw viene vacío se usa $defecto (pantalla: '10' por rendimiento; pasar 'todas' para listado completo).
     * Valores no reconocidos → 10.
     *
     * @return int|null null = sin límite (todas las filas)
     */
    public static function parseLimiteMostrar(?string $raw, string $defecto = '10'): ?int
    {
        $s = ($raw !== null && trim($raw) !== '') ? strtolower(trim($raw)) : strtolower(trim($defecto));
        if ($s === 'todas' || $s === 'todos' || $s === 'all' || $s === '0') {
            return null;
        }
        $n = (int) $s;
        if (in_array($n, [10, 50, 100], true)) {
            return $n;
        }

        return 10;
    }

    /**
     * @param list<mixed> $filas
     * @return list<mixed>
     */
    public static function aplicarLimiteFilas(array $filas, ?int $limite): array
    {
        if ($limite === null || $limite < 1) {
            return $filas;
        }

        return array_slice($filas, 0, $limite);
    }

    /** Valor estable para query string: 10 | 50 | 100 | todas */
    public static function limiteMostrarAQuery(?int $limite): string
    {
        if ($limite === null) {
            return 'todas';
        }

        return (string) $limite;
    }
}
