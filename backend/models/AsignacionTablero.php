<?php

namespace Models;

use Core\Database;
use Core\DatabaseLegacy;
use Core\DatabaseSegundometro;
use Core\UsuarioFantasmaReporteria;

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
     * Tablero de dos ventanas mar–lun: las **dos primeras** columnas del tablero de tres (sin la «próxima»).
     * - Columna 1 = semana pasada real (histórico de campaña / ventana mar–lun anterior) → `semanas[0]` y `cells[0]`.
     * - Columna 2 = semana actual (asignación vigente) → `semanas[1]` y `cells[1]`.
     * (Antes se tomaban [1] y [2], lo que mostraba vigente+próxima en lugar de pasada+actual.)
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

        $sPas = $semanasFull[0];
        $sAct = $semanasFull[1];
        $sem0 = array_merge($sPas, [
            'chip_text' => 'Semana pasada: ' . (string) ($sPas['range'] ?? ''),
            'th_class' => 'comp-th-hist',
            'hist_level' => 1,
        ]);
        $sem1 = array_merge($sAct, [
            'chip_text' => 'Actual: ' . (string) ($sAct['range'] ?? ''),
            'th_class' => 'comp-th-act',
            'hist_level' => 0,
        ]);
        $semanas = [$sem0, $sem1];

        $filas = [];
        foreach ($filasFull as $f) {
            $cellsFull = is_array($f['cells'] ?? null) ? $f['cells'] : [];
            $defCell = ['ext' => '—', 'nom' => '—', 'pue' => '—', 'Bucket_Morosidad_Real' => ''];
            $c0 = array_merge($defCell, is_array($cellsFull[0] ?? null) ? $cellsFull[0] : []);
            $c1 = array_merge($defCell, is_array($cellsFull[1] ?? null) ? $cellsFull[1] : []);
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
     * - Semana pasada = **tbl_segundometro_histo** (SEMANA = label de la primera ventana): Gestor_Asignado + bucket; ext/puesto enriquecidos desde persona si el nombre coincide.
     * - Semana actual = asignación vigente por campaña Legacy + Segundómetro (no se sustituye automáticamente)
     * - Próxima = proyección (sí puede mostrar reasignación por ausencia/baja/incapacidad)
     *
     * @return array{
     *   semanas:list<array<string,mixed>>,
     *   subcols:list<array<string,string>>,
     *   filas:list<array{
     *      id_credito:string,
     *      cells:list<array{ext:string,nom:string,pue:string,Bucket_Morosidad_Real?:string}>,
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

        try {
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
        $externalsNecesarios = [];
        foreach ($tareas as $t) {
            $uid = (int) ($t['current_user_id'] ?? 0);
            if ($uid < 1) {
                continue;
            }
            $u = $usuariosLegacy['by_id'][$uid] ?? null;
            if (!is_array($u)) {
                continue;
            }
            $ext = self::normalizarExternalId($u['external_id'] ?? null);
            if ($ext !== '') {
                $externalsNecesarios[$ext] = true;
            }
        }
        $personas = self::obtenerPersonasSegundometro($dbSeg, array_keys($externalsNecesarios));
        $idxPersonaPorNombre = self::indicePersonasPorNombreGestor($personas);
        $idxLegacyPorNombre = self::indiceUsuariosLegacyPorNombre($usuariosLegacy);

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

        $bucketsPorCreditoSemana = [];
        $histoSemanaPasadaPorCredito = [];
        $labelSemanaPasada = trim((string) ($out['semanas'][0]['label'] ?? ''));
        $rangeSemanaPasada = trim((string) ($out['semanas'][0]['range'] ?? ''));
        $habilitarHistoSemanaPasada = getenv('ASIGNACION_HISTO_SEMANA_PASADA') === '1';
        try {
            $dbMega = new DatabaseSegundometro();
            $bucketsPorCreditoSemana = self::obtenerBucketsMorosidadPorCreditos($dbMega, $listaCreditos);
            // El histórico puede volverse muy lento según tamaño/índices de tbl_segundometro_histo.
            // Para mantener el tablero operativo, queda detrás de flag (por defecto apagado).
            if ($habilitarHistoSemanaPasada && ($labelSemanaPasada !== '' || $rangeSemanaPasada !== '')) {
                $histoSemanaPasadaPorCredito = self::obtenerHistoSemanaPasadaPorCreditos($dbMega, $labelSemanaPasada, $rangeSemanaPasada, $listaCreditos);
            }
        } catch (\Throwable $e) {
            error_log('AsignacionTablero::carga_histo_semana_buckets -> ' . $e->getMessage());
        }

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

            $histoPas = $histoSemanaPasadaPorCredito[self::claveIdCredito($credito)] ?? null;
            $gestorHisto = '';
            $bvHistoPasada = '';
            if (is_array($histoPas)) {
                $gestorHisto = trim((string) ($histoPas['Gestor_Asignado'] ?? $histoPas['gestor_asignado'] ?? ''));
                $bvHistoPasada = trim((string) ($histoPas['Bucket_Morosidad_Real'] ?? $histoPas['bucket_morosidad_real'] ?? ''));
            }
            if ($gestorHisto !== '') {
                $celdaPasada = self::celdaDesdeHistoGestor($histoPas, $idxPersonaPorNombre, $idxLegacyPorNombre, $asigPasada);
            } elseif (is_array($histoPas) && $bvHistoPasada !== '') {
                $celdaPasada = ['ext' => '—', 'nom' => '—', 'pue' => '—'];
            } elseif ($esNuevo) {
                $celdaPasada = ['ext' => 'NUEVO', 'nom' => '—', 'pue' => '—'];
            } else {
                $celdaPasada = self::toCell($asigPasada, null);
            }
            $celdaActual = self::toCell($asigActual, null);
            $celdaProxima = self::toCell($asigProxima, null);
            $bvSemana = trim((string) ($bucketsPorCreditoSemana[self::claveIdCredito($credito)] ?? ''));
            $celdaPasada['Bucket_Morosidad_Real'] = $bvHistoPasada;
            $celdaActual['Bucket_Morosidad_Real'] = $bvSemana;
            $celdaProxima['Bucket_Morosidad_Real'] = $bvSemana;
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
        } catch (\Throwable $e) {
            error_log('AsignacionTablero::obtenerPortafolioAutomatico -> ' . $e->getMessage());

            return $out;
        }
    }

    /**
     * Bucket de morosidad actual por Id_credito: tbl_segundometro_semana en __SPARTA_SECRET_REDACTED__ (DatabaseSegundometro).
     *
     * @param list<string> $idsCreditos Valores de credit_number / Id_credito del portafolio
     * @return array<string,string> id_credito → Bucket_Morosidad_Real (vacío si no hay fila)
     */
    private static function obtenerBucketsMorosidadPorCreditos(DatabaseSegundometro $db, array $idsCreditos): array
    {
        $ids = array_values(array_unique(array_filter(array_map(
            static fn($id): string => self::claveIdCredito(trim((string) $id)),
            $idsCreditos
        ), static fn(string $id): bool => $id !== '')));
        if ($ids === []) {
            return [];
        }
        $map = [];
        foreach (array_chunk($ids, 800) as $chunk) {
            $placeholders = [];
            $params = [];
            foreach ($chunk as $i => $idCred) {
                $k = 'c' . $i;
                $placeholders[] = ':' . $k;
                $params[$k] = $idCred;
            }
            $sql = '
                SELECT Id_credito, Bucket_Morosidad_Real
                FROM tbl_segundometro_semana
                WHERE Id_credito IN (' . implode(', ', $placeholders) . ')
            ';
            $rows = $db->queryAll($sql, $params);
            foreach ((array) $rows as $r) {
                $idc = self::claveIdCredito($r['Id_credito'] ?? $r['id_credito'] ?? '');
                if ($idc === '') {
                    continue;
                }
                $bmr = $r['Bucket_Morosidad_Real'] ?? $r['bucket_morosidad_real'] ?? '';
                $map[$idc] = trim((string) $bmr);
            }
        }

        return $map;
    }

    /**
     * Snapshot por crédito en tbl_segundometro_histo (semana pasada): gestor + **Bucket_Morosidad_Real**.
     * Prueba variantes de etiqueta SEMANA (martes vs jueves ISO, espacios) y filtro por rango de la ventana.
     *
     * @param list<string> $idsCreditos
     * @return array<string, array{Gestor_Asignado:string, Bucket_Morosidad_Real:string}>
     */
    private static function obtenerHistoSemanaPasadaPorCreditos(DatabaseSegundometro $db, string $semanaEtiqueta, string $semanaRange, array $idsCreditos): array
    {
        $variantes = self::variantesEtiquetaSemanaHisto(trim($semanaEtiqueta), $semanaRange);
        if ($variantes === []) {
            return [];
        }
        $ids = array_values(array_unique(array_filter(array_map(
            static fn($id): string => self::claveIdCredito(trim((string) $id)),
            $idsCreditos
        ), static fn(string $id): bool => $id !== '')));
        if ($ids === []) {
            return [];
        }
        $map = [];
        foreach (array_chunk($ids, 400) as $chunk) {
            $placeholders = [];
            $params = [];
            foreach ($chunk as $i => $idCred) {
                $k = 'c' . $i;
                $placeholders[] = ':' . $k;
                $params[$k] = $idCred;
            }
            $agr = [];
            $semIn = [];
            $semParams = [];
            $si = 0;
            $valsSem = [];
            foreach ($variantes as $lab) {
                $v = trim((string) $lab);
                if ($v !== '') {
                    $valsSem[$v] = true;
                }
                $v2 = preg_replace('/\s+/u', ' ', $v);
                if (is_string($v2)) {
                    $v2 = trim($v2);
                    if ($v2 !== '') {
                        $valsSem[$v2] = true;
                    }
                }
            }
            foreach (array_keys($valsSem) as $cand) {
                $sk = 'semv' . $si;
                $semIn[] = ':' . $sk;
                $semParams[$sk] = $cand;
                $si++;
            }

            if ($semIn !== []) {
                $sqlSem = '
                    SELECT Id_credito, Gestor_Asignado, Bucket_Morosidad_Real, Bucket_Morosidad, fecha_hora_insert, SEMANA
                    FROM tbl_segundometro_histo
                    WHERE Id_credito IN (' . implode(', ', $placeholders) . ')
                      AND SEMANA IN (' . implode(', ', $semIn) . ')
                    ORDER BY Id_credito, fecha_hora_insert DESC
                ';
                $rowsSem = $db->queryAll($sqlSem, array_merge($params, $semParams));
                foreach ((array) $rowsSem as $r) {
                    $idc = self::claveIdCredito($r['Id_credito'] ?? $r['id_credito'] ?? '');
                    if ($idc === '') {
                        continue;
                    }
                    $agr[$idc][] = $r;
                }
            }

            foreach ($agr as $idc => $filas) {
                $map[$idc] = self::elegirFilaHistoSemanaPasada($filas, $variantes);
            }
        }

        return $map;
    }

    private static function claveIdCredito($id): string
    {
        $s = trim((string) $id);
        if ($s === '') {
            return '';
        }
        if (preg_match('/^\d+$/', $s)) {
            return (string) (int) $s;
        }
        $f = filter_var($s, FILTER_VALIDATE_FLOAT);
        if (is_float($f) && $f === floor($f) && abs($f) < 1e15) {
            return (string) (int) $f;
        }

        return $s;
    }

    /** Minúsculas y espacios (incl. NBSP) colapsados para comparar `SEMANA` del histórico. */
    private static function normalizarTextoSemanaHisto(string $s): string
    {
        $s = str_replace(["\xc2\xa0", "\xe2\x80\xaf"], ' ', $s);
        $r = preg_replace('/\s+/u', ' ', trim($s));

        return is_string($r) && $r !== '' ? mb_strtolower($r) : '';
    }

    /**
     * @return list<string>
     */
    private static function variantesEtiquetaSemanaHisto(string $label, string $range): array
    {
        $set = [];
        $l = trim($label);
        if ($l !== '') {
            $set[mb_strtolower($l)] = $l;
            $norm = preg_replace('/(Semana)\s+(\d+)\s*-\s*(\d+)/iu', '$1 $2-$3', $l);
            if (is_string($norm)) {
                $norm = trim($norm);
                if ($norm !== '' && $norm !== $l) {
                    $set[mb_strtolower($norm)] = $norm;
                }
            }
            // En BD a veces viene sin la palabra «Semana» (p. ej. «16-2026» o «16 - 2026»).
            if (preg_match('/Semana\s+(\d+)\s*[-–]\s*(\d{4})/iu', $l, $m)) {
                $w = (int) $m[1];
                $y = (int) $m[2];
                $compact = $w . '-' . $y;
                $set[mb_strtolower($compact)] = $compact;
                $set[mb_strtolower(sprintf('semana %s', $compact))] = 'Semana ' . $compact;
            }
        }
        $rg = self::parseRangeDdMmYyyy($range);
        if ($rg !== null) {
            try {
                $tz = new \DateTimeZone('America/Mexico_City');
            } catch (\Exception $e) {
                $tz = new \DateTimeZone('UTC');
            }
            $tue = \DateTimeImmutable::createFromFormat('!Y-m-d', $rg['inicio'], $tz);
            if ($tue instanceof \DateTimeImmutable) {
                $labM = sprintf('Semana %d-%d', (int) $tue->format('W'), (int) $tue->format('o'));
                $set[mb_strtolower($labM)] = $labM;
                $labMY = sprintf('Semana %d-%d', (int) $tue->format('W'), (int) $tue->format('Y'));
                $set[mb_strtolower($labMY)] = $labMY;
                $jue = $tue->modify('+2 days');
                $labJ = sprintf('Semana %d-%d', (int) $jue->format('W'), (int) $jue->format('o'));
                $set[mb_strtolower($labJ)] = $labJ;
                $labJY = sprintf('Semana %d-%d', (int) $jue->format('W'), (int) $jue->format('Y'));
                $set[mb_strtolower($labJY)] = $labJY;
            }
        }

        return array_values($set);
    }

    /**
     * @param list<array<string,mixed>> $filas Misma clave Id_credito, orden DESC por fecha_hora_insert
     * @param list<string> $variantesEtiqueta
     * @return array{Gestor_Asignado:string, Bucket_Morosidad_Real:string}
     */
    private static function elegirFilaHistoSemanaPasada(array $filas, array $variantesEtiqueta): array
    {
        if ($filas === []) {
            return ['Gestor_Asignado' => '', 'Bucket_Morosidad_Real' => ''];
        }
        $varsL = [];
        foreach ($variantesEtiqueta as $ve) {
            $t = self::normalizarTextoSemanaHisto((string) $ve);
            if ($t !== '') {
                $varsL[$t] = true;
            }
            $t2 = mb_strtolower(trim((string) $ve));
            if ($t2 !== '' && $t2 !== $t) {
                $varsL[$t2] = true;
            }
        }
        $mejor = null;
        $mejorP = -1;
        foreach ($filas as $f) {
            $semRaw = (string) ($f['SEMANA'] ?? $f['semana'] ?? '');
            $semL = self::normalizarTextoSemanaHisto($semRaw);
            $brReal = trim((string) ($f['Bucket_Morosidad_Real'] ?? $f['bucket_morosidad_real'] ?? ''));
            $brAlt = trim((string) ($f['Bucket_Morosidad'] ?? $f['bucket_morosidad'] ?? ''));
            $br = $brReal !== '' ? $brReal : $brAlt;
            $g = trim((string) ($f['Gestor_Asignado'] ?? $f['gestor_asignado'] ?? ''));
            $coincideSem = false;
            foreach (array_keys($varsL) as $vl) {
                if ($semL === $vl) {
                    $coincideSem = true;
                    break;
                }
            }
            $ts = strtotime((string) ($f['fecha_hora_insert'] ?? '')) ?: 0;
            // Priorizar siempre fila cuyo texto SEMANA coincide con la ventana (evita bucket de otra semana al ampliar fechas).
            $prio = ($coincideSem ? 5_000_000_000_000 : 0)
                + ($br !== '' ? 1_000_000_000_000 : 0)
                + ($g !== '' ? 10_000_000_000 : 0)
                + $ts;
            if ($prio > $mejorP) {
                $mejorP = $prio;
                $mejor = $f;
            }
        }
        if (!is_array($mejor)) {
            return ['Gestor_Asignado' => '', 'Bucket_Morosidad_Real' => ''];
        }

        $bucketOut = trim((string) ($mejor['Bucket_Morosidad_Real'] ?? $mejor['bucket_morosidad_real'] ?? ''));
        if ($bucketOut === '') {
            $bucketOut = trim((string) ($mejor['Bucket_Morosidad'] ?? $mejor['bucket_morosidad'] ?? ''));
        }

        return [
            'Gestor_Asignado' => trim((string) ($mejor['Gestor_Asignado'] ?? $mejor['gestor_asignado'] ?? '')),
            'Bucket_Morosidad_Real' => $bucketOut,
        ];
    }

    /**
     * @return array{inicio:string,fin:string}|null
     */
    private static function parseRangeDdMmYyyy(string $range): ?array
    {
        $r = trim($range);
        if ($r === '') {
            return null;
        }
        $parts = preg_split('/\s*-\s*/', $r);
        if (!is_array($parts) || count($parts) !== 2) {
            return null;
        }
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
        } catch (\Exception $e) {
            $tz = new \DateTimeZone('UTC');
        }
        $ini = \DateTimeImmutable::createFromFormat('d/m/Y', trim((string) $parts[0]), $tz);
        $fin = \DateTimeImmutable::createFromFormat('d/m/Y', trim((string) $parts[1]), $tz);
        if ($ini === false || $fin === false) {
            return null;
        }

        return ['inicio' => $ini->format('Y-m-d'), 'fin' => $fin->format('Y-m-d')];
    }

    /**
     * @param array{by_external:array<string,array<string,mixed>>,by_persona_id:array<int,array<string,mixed>>} $personas
     * @return array<string, array{external_id:string, puesto:string, nombre:string}>
     */
    private static function indicePersonasPorNombreGestor(array $personas): array
    {
        $map = [];
        foreach ($personas['by_persona_id'] ?? [] as $p) {
            if (!is_array($p)) {
                continue;
            }
            $nomRaw = trim((string) ($p['nombre_gestor'] ?? ''));
            if ($nomRaw === '') {
                continue;
            }
            $key = self::normalizarNombreParaMatch($nomRaw);
            if ($key === '' || isset($map[$key])) {
                continue;
            }
            $map[$key] = [
                'external_id' => self::normalizarExternalId($p['external_id_legacy'] ?? null),
                'puesto' => trim((string) ($p['puesto_gestor_legacy'] ?? $p['puesto_legacy'] ?? '')),
                'nombre' => $nomRaw,
            ];
        }

        return $map;
    }

    /**
     * @param array{by_id:array<int,array<string,mixed>>,by_external:array<string,array<string,mixed>>} $usuariosLegacy
     * @return array<string, array{external_id:string, puesto:string, nombre:string}>
     */
    private static function indiceUsuariosLegacyPorNombre(array $usuariosLegacy): array
    {
        $map = [];
        foreach ($usuariosLegacy['by_id'] ?? [] as $u) {
            if (!is_array($u)) {
                continue;
            }
            $nomRaw = trim((string) ($u['name'] ?? ''));
            if ($nomRaw === '') {
                continue;
            }
            $key = self::normalizarNombreParaMatch($nomRaw);
            if ($key === '' || isset($map[$key])) {
                continue;
            }
            $map[$key] = [
                'external_id' => self::normalizarExternalId($u['external_id'] ?? null),
                'puesto' => trim((string) ($u['puesto_segun_jerarquia'] ?? '')),
                'nombre' => $nomRaw,
            ];
        }

        return $map;
    }

    private static function normalizarNombreParaMatch(string $s): string
    {
        $s = trim($s);
        if ($s === '') {
            return '';
        }
        $s = strtr($s, [
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A',
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ñ' => 'N', 'ñ' => 'n',
        ]);
        $s = preg_replace('/[^A-Za-z0-9 ]+/u', ' ', $s) ?? $s;
        $t = preg_replace('/\s+/u', ' ', $s);
        $s = is_string($t) ? $t : $s;

        return mb_strtolower($s);
    }

    /**
     * @param array<string,mixed> $histoRow Fila mínima con Gestor_Asignado (tbl_segundometro_histo)
     * @param array<string, array{external_id:string, puesto:string, nombre:string}> $idxNombrePersona
     * @param array<string, array{external_id:string, puesto:string, nombre:string}> $idxNombreLegacy
     * @param ?array<string,mixed> $fallbackAsig Asignación campaña anterior (misma cadena que semana actual: users + aplicarPuestoLegacySegundometro)
     * @return array{ext:string,nom:string,pue:string}
     */
    private static function celdaDesdeHistoGestor(array $histoRow, array $idxNombrePersona, array $idxNombreLegacy, ?array $fallbackAsig): array
    {
        $gestorTxt = trim((string) ($histoRow['Gestor_Asignado'] ?? $histoRow['gestor_asignado'] ?? ''));
        $nom = $gestorTxt !== '' ? $gestorTxt : '—';
        $ext = '—';
        $pue = '—';

        // Prioridad: mismo criterio que la columna «Actual» (crédito → tasks/users → equivalencia persona/puestos_legacy).
        // Antes se prefería el match por nombre del texto del histórico contra Legacy (puesto_segun_jerarquia: SUBGERENTE, etc.)
        // y contra Persona con otro orden, lo que duplicaba puestos distintos para el mismo external_id.
        if (is_array($fallbackAsig)) {
            $eFb = trim((string) ($fallbackAsig['external_id'] ?? ''));
            $puFb = trim((string) ($fallbackAsig['puesto_legacy'] ?? ''));
            if ($eFb !== '') {
                $ext = $eFb;
            }
            if ($puFb !== '' && $puFb !== '—') {
                $pue = $puFb;
            }
            $nomFb = trim((string) ($fallbackAsig['nombre'] ?? ''));
            if (($nom === '—' || $nom === '') && $nomFb !== '') {
                $nom = $nomFb;
            }
        }

        if ($gestorTxt !== '') {
            $key = self::normalizarNombreParaMatch($gestorTxt);
            if ($ext === '—' || $pue === '—') {
                if ($key !== '' && isset($idxNombrePersona[$key])) {
                    $info = $idxNombrePersona[$key];
                    if ($ext === '—') {
                        $e = trim((string) ($info['external_id'] ?? ''));
                        if ($e !== '') {
                            $ext = $e;
                        }
                    }
                    if ($pue === '—') {
                        $pu = trim((string) ($info['puesto'] ?? ''));
                        if ($pu !== '') {
                            $pue = $pu;
                        }
                    }
                } elseif ($key !== '' && isset($idxNombreLegacy[$key])) {
                    $info = $idxNombreLegacy[$key];
                    if ($ext === '—') {
                        $e = trim((string) ($info['external_id'] ?? ''));
                        if ($e !== '') {
                            $ext = $e;
                        }
                    }
                    if ($pue === '—') {
                        $pu = trim((string) ($info['puesto'] ?? ''));
                        if ($pu !== '') {
                            $pue = $pu;
                        }
                    }
                }
            }
        } elseif (is_array($fallbackAsig)) {
            $e = trim((string) ($fallbackAsig['external_id'] ?? ''));
            if ($e !== '') {
                $ext = $e;
            }
            $pu = trim((string) ($fallbackAsig['puesto_legacy'] ?? ''));
            if ($pu !== '' && $pu !== '—') {
                $pue = $pu;
            }
            $nomFb = trim((string) ($fallbackAsig['nombre'] ?? ''));
            if ($nomFb !== '') {
                $nom = $nomFb;
            }
        }

        return [
            'ext' => $ext,
            'nom' => $nom,
            'pue' => $pue,
        ];
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
        $whereBase = "c.name NOT LIKE '%ESPEJO%'
                  AND c.name NOT LIKE 'ESP_%'
                  AND c.name NOT LIKE '%SUPERVISORES%'
                  AND c.start_date IS NOT NULL
                  AND (SELECT COUNT(*) FROM tasks t WHERE t.campaign_id = c.id) >= 100";
        $sqlTopSem = "
            SELECT DISTINCT YEARWEEK(c.start_date, 1) AS semana_iso
            FROM campaigns c
            WHERE {$whereBase}
            ORDER BY semana_iso DESC
            LIMIT 2
        ";
        $topS = $db->queryAll($sqlTopSem);
        if (!is_array($topS) || $topS === []) {
            return [];
        }
        $yws = [];
        foreach ($topS as $tr) {
            $y = (int) ($tr['semana_iso'] ?? 0);
            if ($y > 0) {
                $yws[] = $y;
            }
        }
        if ($yws === []) {
            return [];
        }
        rsort($yws, SORT_NUMERIC);
        $rankPorIso = [];
        $rango = 1;
        foreach ($yws as $y) {
            if (!isset($rankPorIso[$y])) {
                $rankPorIso[$y] = $rango++;
            }
        }
        $ph = [];
        $pr = [];
        foreach (array_values($yws) as $i => $y) {
            $k = 'yw' . $i;
            $ph[] = ':' . $k;
            $pr[$k] = (int) $y;
        }
        $inYw = implode(', ', $ph);
        $sql = "
            SELECT
                c.id AS campaign_id,
                c.name AS campaign_name,
                WEEK(c.start_date, 1) AS numero_semana,
                YEARWEEK(c.start_date, 1) AS semana_iso
            FROM campaigns c
            WHERE {$whereBase}
              AND YEARWEEK(c.start_date, 1) IN ({$inYw})
            ORDER BY semana_iso ASC, c.start_date DESC, c.id DESC
        ";
        $raw = $db->queryAll($sql, $pr);
        if (!is_array($raw) || $raw === []) {
            return [];
        }
        $out = [];
        foreach ($raw as $r) {
            if (!is_array($r)) {
                continue;
            }
            $iso = (int) ($r['semana_iso'] ?? 0);
            if (!isset($rankPorIso[$iso])) {
                continue;
            }
            $r['semana_rank'] = $rankPorIso[$iso];
            $out[] = $r;
        }

        return $out;
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
    private static function obtenerPersonasSegundometro(Database $db, array $externalIds = []): array
    {
        $filtroSql = '';
        $filtroParams = [];
        $exts = array_values(array_unique(array_filter(array_map(
            static fn($v): string => self::normalizarExternalId($v),
            $externalIds
        ), static fn(string $s): bool => $s !== '')));
        if ($exts !== []) {
            $ph = [];
            foreach ($exts as $i => $ext) {
                $k = 'ext' . $i;
                $ph[] = ':' . $k;
                $filtroParams[$k] = $ext;
            }
            $filtroSql = ' WHERE p.numero_empleado IN (' . implode(', ', $ph) . ')';
        }
        $exRep = ' AND ' . UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p');
        if ($filtroSql === '') {
            $filtroSql = ' WHERE 1=1' . $exRep;
        } else {
            $filtroSql .= $exRep;
        }
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
            {$filtroSql}
        ";
        $rows = $db->queryAll($sql, $filtroParams);

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
