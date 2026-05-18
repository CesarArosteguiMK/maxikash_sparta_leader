<?php

namespace Models;

use Core\Database;
use Core\Model;

class Ticket extends Model
{
    /** Fotos/adjuntos al crear el ticket (validaciones, viáticos, plantilla, etc.). */
    public const TIPO_ORIGEN_ADJUNTO_TICKET = 'adjunto_ticket';

    /** Evidencias subidas desde el panel Sabueso (dictamen / rastreo). */
    public const TIPO_ORIGEN_DICTAMEN_SABUESO = 'dictamen_sabueso';

    /** Ticket levantado desde la web (sesión / id persona). */
    public const CANAL_LEVANTAMIENTO_WEB = 'web';

    /** Ticket levantado desde app móvil (identificación típica por numero_empleado en request). */
    public const CANAL_LEVANTAMIENTO_APP_MOVIL = 'app_movil';

    /**
     * Tiempo Sabueso/tickets: toda fecha/hora que se escribe en BD en este módulo debe ser hora CDMX
     * (America/Mexico_City), no la del reloj del servidor ni NOW()/CURDATE() en SQL al insertar/actualizar.
     * Usar self::ahoraCdmx() o self::cdmxNowImmutable() para INSERT/UPDATE; las consultas de solo lectura
     * que filtren por "hoy" deben usar fechas generadas en PHP (fechaCdmx / inicioSemanaLunesCdmx) si aplica.
     */

    /**
     * Lista de tickets.
     * @param int $idUsuario ID de la persona (sesión).
     * @param bool $soloDelUsuario true = solo los que levantó ese usuario (menú Ticket); false = todos (Panel Admin).
     * Siempre incluye creador_nombre para Panel Admin.
     * @param array $filtros Opcional (solo Panel Admin): asignado (0=todos, -1=sin asignar, id=persona),
     *        dictamen_enviado (''|si|no), dictamen_visto (''|si|no, solo si enviado),
     *        ds_estado (''|pendiente|listo|sin_ds|prorroga_activa), prioridad_id (0=todos).
     */
    public static function getListaTickets($idUsuario, $soloDelUsuario = true, array $filtros = [])
    {
        $db = new Database();
        $tieneCategoriaGestion = false;
        try {
            $col = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'categoria_gestion' LIMIT 1");
            $tieneCategoriaGestion = !empty($col);
        } catch (\Exception $e) {
            $tieneCategoriaGestion = false;
        }
        $selCategoria = $tieneCategoriaGestion
            ? "COALESCE(NULLIF(TRIM(t.categoria_gestion),''), 'sabueso') AS categoria_gestion, "
            : "'sabueso' AS categoria_gestion, ";
        $tieneColsCategoria = false;
        if ($tieneCategoriaGestion) {
            try {
                $colTipo = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'tipo_categoria' LIMIT 1");
                $tieneColsCategoria = !empty($colTipo);
            } catch (\Exception $e) {
                $tieneColsCategoria = false;
            }
        }
        $selColsCategoria = $tieneColsCategoria
            ? "t.tipo_categoria, t.asunto, t.prioridad_categoria, t.contacto_telefono, t.contacto_email, "
            : "";
        $tieneNotaUrl = false;
        if ($tieneCategoriaGestion) {
            try {
                $colNota = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'nota' LIMIT 1");
                $tieneNotaUrl = !empty($colNota);
            } catch (\Exception $e) {
                $tieneNotaUrl = false;
            }
        }
        $selColsNotaUrl = $tieneNotaUrl ? "t.nota, t.url_direccion, " : "";
        $tieneColsSolVac = false;
        if ($tieneCategoriaGestion) {
            try {
                $colSv = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'solicitud_vacaciones_departamento' LIMIT 1");
                $tieneColsSolVac = !empty($colSv);
            } catch (\Exception $e) {
                $tieneColsSolVac = false;
            }
        }
        $tieneColAdjNomSolVac = false;
        if ($tieneColsSolVac) {
            try {
                $colAn = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'solicitud_vacaciones_adjunto_nombre_original' LIMIT 1");
                $tieneColAdjNomSolVac = !empty($colAn);
            } catch (\Exception $e) {
                $tieneColAdjNomSolVac = false;
            }
        }
        $selColsSolVac = $tieneColsSolVac
            ? 't.solicitud_vacaciones_departamento, t.solicitud_vacaciones_fecha_desde, t.solicitud_vacaciones_fecha_hasta, t.solicitud_vacaciones_quien_cubre'
                . ($tieneColAdjNomSolVac ? ', t.solicitud_vacaciones_adjunto_nombre_original' : '')
                . ', '
            : '';
        $tieneCanalLevantamiento = false;
        try {
            $colCanal = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'canal_levantamiento' LIMIT 1");
            $tieneCanalLevantamiento = !empty($colCanal);
        } catch (\Exception $e) {
            $tieneCanalLevantamiento = false;
        }
        $selCanalLevantamiento = $tieneCanalLevantamiento ? 't.canal_levantamiento, ' : '';
        $tieneColQuienAsigno = false;
        try {
            $colQ = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'asignacion_ticket' AND COLUMN_NAME = 'id_persona_quien_asigno' LIMIT 1");
            $tieneColQuienAsigno = !empty($colQ);
        } catch (\Exception $e) {
            $tieneColQuienAsigno = false;
        }
        $tieneTablaMotivo = false;
        try {
            $tabM = $db->queryOne("SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'asignacion_ticket_motivo' LIMIT 1");
            $tieneTablaMotivo = !empty($tabM);
        } catch (\Exception $e) {
            $tieneTablaMotivo = false;
        }
        $atSubSel = 'at1.id_ticket, at1.id_persona_asignada, at1.id_asignacion';
        if ($tieneColQuienAsigno) {
            $atSubSel .= ', at1.id_persona_quien_asigno';
        }
        $atJoinSub = 'LEFT JOIN (SELECT ' . $atSubSel . ' FROM asignacion_ticket at1 INNER JOIN (SELECT id_ticket, MAX(fecha_asignacion) AS max_fecha FROM asignacion_ticket WHERE (activo = 1 OR activo IS NULL) GROUP BY id_ticket) at2 ON at1.id_ticket = at2.id_ticket AND at1.fecha_asignacion = at2.max_fecha WHERE (at1.activo = 1 OR at1.activo IS NULL)) at ON at.id_ticket = t.id_ticket ';
        $joinPa = 'LEFT JOIN persona pa ON at.id_persona_asignada = pa.id ';
        $joinPqj = $tieneColQuienAsigno ? 'LEFT JOIN persona pqj ON pqj.id = at.id_persona_quien_asigno ' : '';
        $motivoSub = '(SELECT TRIM(CONCAT(TRIM(IFNULL(pcap.nombres, \'\')), \' \', TRIM(IFNULL(pcap.apellidop, \'\')))) FROM asignacion_ticket_motivo mm INNER JOIN persona pcap ON pcap.id = mm.id_persona_capo WHERE mm.id_asignacion_ticket = at.id_asignacion ORDER BY mm.id_motivo DESC LIMIT 1)';
        if ($tieneColQuienAsigno) {
            $selAsignadoPor = 'COALESCE(NULLIF(TRIM(CONCAT(TRIM(IFNULL(pqj.nombres, \'\')), \' \', TRIM(IFNULL(pqj.apellidop, \'\')))), \'\'), '
                . ($tieneTablaMotivo ? $motivoSub : 'NULL')
                . ') AS asignado_por_nombre, ';
        } elseif ($tieneTablaMotivo) {
            $selAsignadoPor = $motivoSub . ' AS asignado_por_nombre, ';
        } else {
            $selAsignadoPor = "'' AS asignado_por_nombre, ";
        }
        $baseSelect = "SELECT DISTINCT t.id_ticket, t.folio, t.id_credito, t.descripcion_inicial, t.fecha_creacion, t.fecha_vencimiento, " .
            $selCategoria .
            $selColsCategoria .
            $selColsNotaUrl .
            $selColsSolVac .
            $selCanalLevantamiento .
            "tt.nombre AS tipo_ticket_nombre, et.nombre AS estado_ticket_nombre, pt.nombre AS prioridad_nombre, ot.nombre AS origen_nombre, " .
            "CONCAT(TRIM(IFNULL(p.nombres, '')), ' ', TRIM(IFNULL(p.apellidop, ''))) AS creador_nombre, " .
            "at.id_persona_asignada, CONCAT(TRIM(IFNULL(pa.nombres, '')), ' ', TRIM(IFNULL(pa.apellidop, ''))) AS asignado_nombre, " .
            $selAsignadoPor .
            "dm.dictamen_estado, dm.dictamen_fecha_visto, dm.dictamen_fecha_envio, " .
            "dsm.ds_resultado, dsm.ds_detalle " .
            "FROM ticket t " .
            "INNER JOIN tipo_ticket tt ON t.id_tipo_ticket = tt.id_tipo_ticket " .
            "INNER JOIN estado_ticket et ON t.id_estado_ticket = et.id_estado_ticket " .
            "INNER JOIN prioridad_ticket pt ON t.id_prioridad = pt.id_prioridad " .
            "INNER JOIN origen_ticket ot ON t.id_origen_ticket = ot.id_origen_ticket " .
            "INNER JOIN persona p ON t.id_persona_creador = p.id " .
            $atJoinSub .
            $joinPa .
            $joinPqj .
            "LEFT JOIN (SELECT d.id_ticket, d.estado AS dictamen_estado, d.fecha_visto_gestor AS dictamen_fecha_visto, d.fecha_actualizacion AS dictamen_fecha_envio FROM dictamen d INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen GROUP BY id_ticket) mx ON d.id_ticket = mx.id_ticket AND d.id = mx.mid) dm ON dm.id_ticket = t.id_ticket " .
            "LEFT JOIN (SELECT ds1.id_ticket, ds1.resultado AS ds_resultado, ds1.detalle AS ds_detalle FROM dictamen_sistema ds1 INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen_sistema GROUP BY id_ticket) dsmx ON ds1.id_ticket = dsmx.id_ticket AND ds1.id = dsmx.mid) dsm ON dsm.id_ticket = t.id_ticket ";

        $params = [];
        if ($soloDelUsuario) {
            $params['id_persona'] = (int)$idUsuario;
        }

        $whereCandidates = [
            "WHERE (t.activo = 1 OR t.activo IS NULL) AND (t.fecha_eliminacion IS NULL)",
            "WHERE (t.activo = 1 OR t.activo IS NULL)",
            "WHERE (t.fecha_eliminacion IS NULL)",
            "WHERE 1=1",
        ];
        if ($soloDelUsuario) {
            foreach ($whereCandidates as $i => $w) {
                $whereCandidates[$i] .= ' AND t.id_persona_creador = :id_persona';
            }
        }

        // Filtros adicionales (Panel Admin): se aplican a todos los candidatos WHERE
        $extraWhere = [];
        if (!$soloDelUsuario && !empty($filtros)) {
            // Filtro por lista de asignados (ej. Territorial: todos los gestores permitidos)
            if (isset($filtros['asignado_ids']) && is_array($filtros['asignado_ids'])) {
                $idAsignados = array_values(array_filter(array_map(function ($v) {
                    return (int)$v;
                }, $filtros['asignado_ids']), function ($id) {
                    return $id > 0;
                }));

                if (empty($idAsignados)) {
                    // Sin ids => no hay resultados
                    $extraWhere[] = '1=0';
                } else {
                    $placeholders = [];
                    foreach ($idAsignados as $i => $id) {
                        $key = 'filtro_id_asignado_in_' . $i;
                        $placeholders[] = ':' . $key;
                        $params[$key] = (int)$id;
                    }
                    $extraWhere[] = 'at.id_persona_asignada IN (' . implode(',', $placeholders) . ')';
                }
            }
            $idAsignado = isset($filtros['asignado']) ? (int)$filtros['asignado'] : 0;
            if ($idAsignado === -1) {
                $extraWhere[] = 'at.id_persona_asignada IS NULL';
            } elseif ($idAsignado > 0) {
                $extraWhere[] = 'at.id_persona_asignada = :filtro_id_asignado';
                $params['filtro_id_asignado'] = $idAsignado;
            }
            $dictEnviado = isset($filtros['dictamen_enviado']) ? trim((string)$filtros['dictamen_enviado']) : '';
            if ($dictEnviado === 'si') {
                $extraWhere[] = "dm.dictamen_estado = 'enviado_al_gestor'";
            } elseif ($dictEnviado === 'no') {
                $extraWhere[] = '(dm.dictamen_estado IS NULL OR dm.dictamen_estado <> \'enviado_al_gestor\')';
            }
            $dsEstado = isset($filtros['ds_estado']) ? trim((string)$filtros['ds_estado']) : '';
            if ($dsEstado === 'pendiente') {
                $extraWhere[] = "dsm.ds_resultado = 'pendiente'";
            } elseif ($dsEstado === 'listo') {
                $extraWhere[] = "(dsm.ds_resultado IS NOT NULL AND dsm.ds_resultado <> '' AND dsm.ds_resultado <> 'pendiente')";
            } elseif ($dsEstado === 'sin_ds') {
                $extraWhere[] = 'dsm.ds_resultado IS NULL';
            } elseif ($dsEstado === 'prorroga_activa') {
                $extraWhere[] = "dsm.ds_resultado = 'prorroga_activa'";
            } elseif ($dsEstado === 'intensidad_activa') {
                $extraWhere[] = "dsm.ds_resultado = 'intensidad_activa'";
            }
            $vistoGestor = isset($filtros['dictamen_visto']) ? trim((string)$filtros['dictamen_visto']) : '';
            if ($vistoGestor === 'si') {
                $extraWhere[] = 'dm.dictamen_fecha_visto IS NOT NULL';
            } elseif ($vistoGestor === 'no') {
                $extraWhere[] = "dm.dictamen_estado = 'enviado_al_gestor' AND dm.dictamen_fecha_visto IS NULL";
            }
            $prioridadId = isset($filtros['prioridad_id']) ? (int)$filtros['prioridad_id'] : 0;
            if ($prioridadId > 0) {
                $extraWhere[] = 't.id_prioridad = :filtro_prioridad_id';
                $params['filtro_prioridad_id'] = $prioridadId;
            }
        }
        // Panel Admin: filtro por categoría (vacío = todas; 'sabueso', 'plantilla', 'viaticos', etc. = solo esa)
        $filtroCategoria = isset($filtros['categoria_gestion']) ? trim((string)$filtros['categoria_gestion']) : '';
        if (!$soloDelUsuario && $tieneCategoriaGestion && $filtroCategoria !== '') {
            $catVal = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-'], '_', $filtroCategoria)));
            if ($catVal !== '') {
                if ($catVal === 'ausencia') {
                    $extraWhere[] = "(COALESCE(NULLIF(TRIM(t.categoria_gestion),''), 'sabueso') IN ('ausencia', 'solicitud_vacaciones'))";
                } else {
                    $extraWhere[] = "(COALESCE(NULLIF(TRIM(t.categoria_gestion),''), 'sabueso') = :filtro_categoria_gestion)";
                    $params['filtro_categoria_gestion'] = $catVal;
                }
            }
        }
        if (!empty($extraWhere)) {
            $fragment = ' AND ' . implode(' AND ', $extraWhere);
            foreach ($whereCandidates as $i => $w) {
                $whereCandidates[$i] .= $fragment;
            }
        }

        $orderBy = " ORDER BY t.fecha_creacion DESC";

        $lastException = null;
        $lastErrorMsg = '';
        foreach ($whereCandidates as $where) {
            try {
                $rows = $db->queryAll($baseSelect . $where . $orderBy, $params);
                $datos = is_array($rows) ? $rows : [];
                foreach ($datos as &$row) {
                    $row['prorroga_otorgada'] = false;
                    $row['prorroga_activa'] = false;
                    $row['prorroga_fecha_limite'] = null;
                    $row['extension_countdown_tipo'] = '';
                    $det = !empty($row['ds_detalle']) ? json_decode($row['ds_detalle'], true) : null;
                    if (is_array($det)) {
                        $pr = (isset($det['prorroga']) && is_array($det['prorroga'])) ? $det['prorroga'] : [];
                        $in = (isset($det['intensidad']) && is_array($det['intensidad'])) ? $det['intensidad'] : [];
                        $row['prorroga_otorgada'] = !empty($pr['otorgada']) || !empty($in['otorgada']);
                        $prAct = !empty($pr['otorgada']) && empty($pr['evaluada']);
                        $inAct = !empty($in['otorgada']) && empty($in['evaluada']);
                        if ($inAct) {
                            $row['prorroga_activa'] = true;
                            $row['prorroga_fecha_limite'] = $in['fecha_limite'] ?? null;
                            $row['extension_countdown_tipo'] = 'intensidad';
                        } elseif ($prAct) {
                            $row['prorroga_activa'] = true;
                            $row['prorroga_fecha_limite'] = $pr['fecha_limite'] ?? null;
                            $row['extension_countdown_tipo'] = 'prorroga';
                        }
                    }
                    // HTML seguro para columnas DataTable: mostrar etiqueta legible (evita no_cumplio_prorr…)
                    $dsRes = trim((string)($row['ds_resultado'] ?? ''));
                    if ($dsRes === '') {
                        $row['ds_resultado_html'] = '<span class="text-muted">—</span>';
                    } else {
                        $etiqMostrar = $dsRes;
                        if (is_array($det) && !empty($det['cumplimiento_etiqueta'])) {
                            $etiqMostrar = (string)$det['cumplimiento_etiqueta'];
                        } else {
                            $cmp = self::cumplimientoMetadatos($dsRes);
                            if (!empty($cmp['cumplimiento_etiqueta'])) {
                                $etiqMostrar = (string)$cmp['cumplimiento_etiqueta'];
                            }
                        }
                        $etiqMostrar = str_replace('No visitó', 'No visito', $etiqMostrar);
                        $short = mb_strlen($etiqMostrar) > 22 ? mb_substr($etiqMostrar, 0, 20) . '…' : $etiqMostrar;
                        $mainSmall = '<small class="text-break d-block" title="' . htmlspecialchars($etiqMostrar, ENT_QUOTES, 'UTF-8') . '">'
                            . htmlspecialchars($short, ENT_QUOTES, 'UTF-8') . '</small>';
                        // Debajo: Pago Sí/No — no_cumplio_prorroga suele ser No; si en detalle consta pago_en_ventana, respetarlo
                        $pagoLine = '';
                        if ($dsRes === 'cumplido_pago') {
                            $pagoLine = '<span class="small text-success fw-semibold d-block mt-1">Pago: Sí</span>';
                        } elseif ($dsRes === 'dictamen_ilocalizable') {
                            $pagoLine = '<span class="small text-muted fw-semibold d-block mt-1" title="No aplica evaluación automática de pago">Pago: —</span>';
                        } elseif (is_array($det) && array_key_exists('pago_en_ventana', $det)) {
                            // Dictámenes antiguos sin __SPARTA_SECRET_REDACTED___consultado se tratan como consultados
                            $consultado = !array_key_exists('__SPARTA_SECRET_REDACTED___consultado', $det) || !empty($det['__SPARTA_SECRET_REDACTED___consultado']);
                            if (!empty($det['pago_en_ventana'])) {
                                $pagoLine = '<span class="small text-success fw-semibold d-block mt-1">Pago: Sí</span>';
                            } elseif (!$consultado) {
                                $pagoLine = '<span class="small text-warning fw-semibold d-block mt-1" title="Estado de cuenta no disponible al evaluar">Pago: No se pudo verificar</span>';
                            } else {
                                $pagoLine = '<span class="small text-danger fw-semibold d-block mt-1">Pago: No</span>';
                            }
                        } elseif ($dsRes !== 'pendiente' && $dsRes !== 'prorroga_activa' && $dsRes !== 'intensidad_activa' && $dsRes !== '') {
                            $pagoLine = '<span class="small text-danger fw-semibold d-block mt-1">Pago: No</span>';
                        }
                        $row['ds_resultado_html'] = $pagoLine !== ''
                            ? '<div class="text-center">' . $mainSmall . $pagoLine . '</div>'
                            : $mainSmall;
                    }
                    // Sin prórroga: cadena vacía (el JS concatena debajo del countdown solo si hay HTML;
                    // un "—" en span hacía que la condición !== '—' fuera siempre true y salía guión de más).
                    if (empty($row['prorroga_otorgada'])) {
                        $row['prorroga_html'] = '';
                    } else {
                        $activa = !empty($row['prorroga_activa']);
                        $extTipoRow = (string)($row['extension_countdown_tipo'] ?? '');
                        if ($activa) {
                            $cls = ($extTipoRow === 'intensidad') ? 'bg-info text-white' : 'bg-warning text-dark';
                        } else {
                            $cls = 'bg-secondary';
                        }
                        $txt = $activa ? 'Activa' : 'Usada';
                        $esIntBadge = $extTipoRow === 'intensidad'
                            || (is_array($det) && isset($det['intensidad']) && is_array($det['intensidad']) && !empty($det['intensidad']['otorgada']));
                        $tipBase = $esIntBadge ? 'Intensidad' : 'Prórroga';
                        $tip = !empty($row['prorroga_fecha_limite']) ? ($tipBase . ' · Límite: ' . $row['prorroga_fecha_limite']) : $tipBase;
                        // Misma celda que tiempo para visitar: badge compacto debajo del countdown (se concatena en JS)
                        $row['prorroga_html'] = '<div class="mt-1"><span class="badge ' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8')
                            . '" data-bs-toggle="tooltip" data-bs-title="' . htmlspecialchars($tip, ENT_QUOTES, 'UTF-8')
                            . '">' . htmlspecialchars($txt, ENT_QUOTES, 'UTF-8') . '</span></div>';
                    }
                }
                unset($row);
                return self::resultado(true, 'Tickets encontrados.', $datos);
            } catch (\Exception $e) {
                $lastException = $e;
                $lastErrorMsg = $e->getMessage();
                continue;
            }
        }
        // Si todos los intentos fallaron, devolver el error con más detalle
        $errorDetalle = $lastException ? $lastException->getMessage() : 'Error desconocido';
        error_log("Error en getListaTickets: " . $errorDetalle);
        return self::resultado(false, 'Error al consultar tickets: ' . $errorDetalle, null, $errorDetalle);
    }

    /**
     * Catálogos activos para el modal de levantar ticket.
     */
    public static function getCatalogosTicket()
    {
        try {
            $db = new Database();

            $tipos = $db->queryAll("SELECT id_tipo_ticket AS id, nombre FROM tipo_ticket WHERE (activo = 1 OR activo IS NULL) ORDER BY nombre");
            $estados = $db->queryAll("SELECT id_estado_ticket AS id, nombre FROM estado_ticket WHERE (activo = 1 OR activo IS NULL) ORDER BY orden, nombre");
            $prioridades = $db->queryAll("SELECT id_prioridad AS id, nombre FROM prioridad_ticket ORDER BY nombre");
            $prioridades = self::asegurarPrioridadesDefault($db, is_array($prioridades) ? $prioridades : []);
            $origenes = $db->queryAll("SELECT id_origen_ticket AS id, nombre FROM origen_ticket WHERE (activo = 1 OR activo IS NULL) ORDER BY nombre");

            return self::resultado(true, 'Catálogos obtenidos.', [
                'tipos'       => is_array($tipos) ? $tipos : [],
                'estados'     => is_array($estados) ? $estados : [],
                'prioridades' => is_array($prioridades) ? $prioridades : [],
                'origenes'    => is_array($origenes) ? $origenes : [],
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener catálogos.', null, $e->getMessage());
        }
    }

    /**
     * Asegura que existan prioridades Alta, Media, Baja y Sin Prioridad; las inserta si faltan y reordena.
     */
    private static function asegurarPrioridadesDefault(Database $db, array $prioridades)
    {
        $nombres = array_map(function ($p) {
            return trim(mb_strtolower($p['nombre'] ?? ''));
        }, $prioridades);
        $insertarList = [];
        if (!in_array('alta', $nombres)) $insertarList[] = ['Alta', 24];
        if (!in_array('media', $nombres) && !in_array('medio', $nombres)) $insertarList[] = ['Media', 48];
        if (!in_array('baja', $nombres)) $insertarList[] = ['Baja', 72];
        if (!in_array('sin prioridad', $nombres)) $insertarList[] = ['Sin Prioridad', 0];
        foreach ($insertarList as $row) {
            $db->CRUD("INSERT INTO prioridad_ticket (nombre, sla_horas) VALUES (:nombre, :sla_horas)", ['nombre' => $row[0], 'sla_horas' => $row[1]]);
        }
        if (!empty($insertarList)) {
            $prioridades = $db->queryAll("SELECT id_prioridad AS id, nombre FROM prioridad_ticket ORDER BY nombre");
        }
        $orden = ['Sin Prioridad' => 0, 'Baja' => 1, 'Media' => 2, 'Medio' => 2, 'Alta' => 3];
        usort($prioridades, function ($a, $b) use ($orden) {
            $na = trim($a['nombre'] ?? '');
            $nb = trim($b['nombre'] ?? '');
            $oa = $orden[$na] ?? 99;
            $ob = $orden[$nb] ?? 99;
            if ($oa !== $ob) return $oa - $ob;
            return strcasecmp($na, $nb);
        });
        return $prioridades;
    }

    /**
     * Inserta un ticket. Folio TCK-XXXX secuencial, id_ticket = siguiente ID disponible.
     * Obligatorios: tipo, origen, id_credito, descripción.
     * Prioridad: siempre Alta (se ignora id_prioridad del cliente).
     * Fecha vencimiento: siempre 24 h después de fecha_creacion (se ignora fecha_vencimiento del cliente).
     * Opcionales (misma tabla que crearTicketSimple / app de tickets): tipo_categoria, asunto,
     * prioridad_categoria, contacto_telefono, contacto_email, nota, url_direccion.
     * Usa transacciones y reintentos para evitar condiciones de carrera y IDs duplicados.
     */
    public static function crear($datos, $idPersonaCreador)
    {
        $db = new Database();

        $idTipo = (int)($datos['id_tipo_ticket'] ?? 0);
        $idOrigen = (int)($datos['id_origen_ticket'] ?? 0);
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' && $datos['id_credito'] !== null
            ? (int)$datos['id_credito'] : null;
        $descripcion = isset($datos['descripcion_inicial']) ? trim((string)$datos['descripcion_inicial']) : '';

        if ($idTipo < 1 || $idOrigen < 1 || $descripcion === '') {
            return self::resultado(false, 'Faltan datos obligatorios (tipo, origen, descripción).', null);
        }
        if ($idCredito === null || $idCredito < 1) {
            return self::resultado(false, 'El ID de crédito es obligatorio y debe ser mayor a 0.', null);
        }

        // Categoría de gestión: sabueso → Panel Admin Sabueso; otras rutas cuando existan
        $catRaw = isset($datos['categoria_gestion']) ? trim((string)$datos['categoria_gestion']) : 'sabueso';
        $catRaw = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-'], '_', $catRaw)));
        if ($catRaw === '') {
            $catRaw = 'sabueso';
        }
        $categoriaGestion = $catRaw;

        $tipoCategoria = isset($datos['tipo_categoria']) ? trim((string)$datos['tipo_categoria']) : null;
        if ($tipoCategoria !== null && strlen($tipoCategoria) > 150) {
            $tipoCategoria = substr($tipoCategoria, 0, 150);
        }
        $asunto = isset($datos['asunto']) ? trim((string)$datos['asunto']) : null;
        if ($asunto !== null && strlen($asunto) > 255) {
            $asunto = substr($asunto, 0, 255);
        }
        $prioridadCat = isset($datos['prioridad_categoria']) ? trim((string)$datos['prioridad_categoria']) : null;
        if ($prioridadCat !== null && strlen($prioridadCat) > 50) {
            $prioridadCat = substr($prioridadCat, 0, 50);
        }
        $contactoTel = isset($datos['contacto_telefono']) ? trim((string)$datos['contacto_telefono']) : null;
        if ($contactoTel !== null && strlen($contactoTel) > 50) {
            $contactoTel = substr($contactoTel, 0, 50);
        }
        $contactoEmail = isset($datos['contacto_email']) ? trim((string)$datos['contacto_email']) : null;
        if ($contactoEmail !== null && strlen($contactoEmail) > 100) {
            $contactoEmail = substr($contactoEmail, 0, 100);
        }
        $nota = isset($datos['nota']) ? trim((string)$datos['nota']) : null;
        $urlDireccion = isset($datos['url_direccion']) ? trim((string)$datos['url_direccion']) : null;

        // Prioridad siempre Alta (no editable desde el formulario)
        $rowPrioridad = $db->queryOne(
            "SELECT id_prioridad FROM prioridad_ticket WHERE LOWER(TRIM(nombre)) = 'alta' LIMIT 1"
        );
        if (!$rowPrioridad || (int)($rowPrioridad['id_prioridad'] ?? 0) < 1) {
            $rowPrioridad = $db->queryOne(
                "SELECT id_prioridad FROM prioridad_ticket WHERE LOWER(TRIM(nombre)) LIKE '%alta%' LIMIT 1"
            );
        }
        $idPrioridad = $rowPrioridad ? (int)$rowPrioridad['id_prioridad'] : 0;
        if ($idPrioridad < 1) {
            return self::resultado(false, 'No se encontró la prioridad "Alta" en catálogo.', null);
        }

        $rowEstado = $db->queryOne("SELECT id_estado_ticket FROM estado_ticket WHERE LOWER(TRIM(nombre)) = 'abierto' AND (activo = 1 OR activo IS NULL) LIMIT 1");
        $idEstado = $rowEstado ? (int)$rowEstado['id_estado_ticket'] : 0;
        if ($idEstado < 1) {
            return self::resultado(false, 'No se encontró el estado "Abierto" en catálogo.', null);
        }

        $now = self::ahoraCdmx();
        // Vencimiento siempre 24 h después de la creación (CDMX)
        $fechaVenc = self::cdmxNowImmutable()->modify('+24 hours')->format('Y-m-d H:i:s');

        // INSERT con degradado si faltan columnas en BD (misma lógica que crearTicketSimple)
        $sqlConCatNotaUrl = 'INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, categoria_gestion, tipo_categoria, asunto, prioridad_categoria, contacto_telefono, contacto_email, nota, url_direccion, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :categoria_gestion, :tipo_categoria, :asunto, :prioridad_categoria, :contacto_telefono, :contacto_email, :nota, :url_direccion, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)';
        $sqlSinCatNotaUrl = 'INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, tipo_categoria, asunto, prioridad_categoria, contacto_telefono, contacto_email, nota, url_direccion, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :tipo_categoria, :asunto, :prioridad_categoria, :contacto_telefono, :contacto_email, :nota, :url_direccion, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)';
        $sqlConCat = 'INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, categoria_gestion, tipo_categoria, asunto, prioridad_categoria, contacto_telefono, contacto_email, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :categoria_gestion, :tipo_categoria, :asunto, :prioridad_categoria, :contacto_telefono, :contacto_email, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)';
        $sqlSinCat = 'INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, tipo_categoria, asunto, prioridad_categoria, contacto_telefono, contacto_email, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :tipo_categoria, :asunto, :prioridad_categoria, :contacto_telefono, :contacto_email, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)';
        $sqlConCatSinCols = 'INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, categoria_gestion, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :categoria_gestion, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)';
        $sqlSinCatSinCols = 'INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)';

        // Intentar crear el ticket con reintentos para evitar condiciones de carrera
        $maxIntentos = 3;
        $intento = 0;

        while ($intento < $maxIntentos) {
            try {
                $db->beginTransaction();

                // Obtener siguiente ID disponible usando MAX (más eficiente) con bloqueo para evitar condiciones de carrera
                $maxRow = $db->queryOne("SELECT MAX(id_ticket) AS max_id FROM ticket FOR UPDATE");
                $maxId = $maxRow && isset($maxRow['max_id']) && $maxRow['max_id'] !== null ? (int)$maxRow['max_id'] : 0;
                $siguienteId = $maxId + 1;

                // Obtener siguiente número de folio usando una consulta SQL optimizada
                // Extrae el número máximo directamente en SQL sin traer todos los registros
                $maxFolioRow = $db->queryOne("
                    SELECT MAX(CAST(SUBSTRING(folio, 5) AS UNSIGNED)) AS max_num
                    FROM ticket
                    WHERE folio LIKE 'TCK-%'
                    FOR UPDATE
                ");
                $maxNum = $maxFolioRow && isset($maxFolioRow['max_num']) && $maxFolioRow['max_num'] !== null ? (int)$maxFolioRow['max_num'] : 0;
                $num = $maxNum + 1;
                $folio = 'TCK-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);

                // Verificar que el folio no exista (solo una verificación rápida)
                $folioExiste = $db->queryOne("SELECT 1 FROM ticket WHERE folio = :folio LIMIT 1", ['folio' => $folio]);
                if ($folioExiste) {
                    // Si el folio existe, buscar el siguiente disponible (raro, pero por si acaso)
                    $num++;
                    $folio = 'TCK-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
                    $folioExiste = $db->queryOne("SELECT 1 FROM ticket WHERE folio = :folio LIMIT 1", ['folio' => $folio]);
                    // Si aún existe, incrementar una vez más (no hacer loop infinito)
                    if ($folioExiste) {
                        $num++;
                        $folio = 'TCK-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
                    }
                }

                // INSERT con categoria_gestion si la columna existe en ticket (esquema actualizado en BD)
                $params = [
                    'id_ticket'            => $siguienteId,
                    'folio'                => $folio,
                    'id_tipo_ticket'       => $idTipo,
                    'id_estado_ticket'     => $idEstado,
                    'id_prioridad'         => $idPrioridad,
                    'id_origen_ticket'     => $idOrigen,
                    'id_credito'           => $idCredito,
                    'descripcion_inicial'  => $descripcion,
                    'fecha_creacion'       => $now,
                    'fecha_vencimiento'    => $fechaVenc,
                    'id_persona_creador'   => (int)$idPersonaCreador,
                    'categoria_gestion'    => $categoriaGestion,
                ];
                $queryConCat = <<<SQL
                    INSERT INTO ticket (
                        id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket,
                        categoria_gestion,
                        id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento,
                        id_persona_creador, activo
                    ) VALUES (
                        :id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket,
                        :categoria_gestion,
                        :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento,
                        :id_persona_creador, 1
                    )
                SQL;
                $querySinCat = <<<SQL
                    INSERT INTO ticket (
                        id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket,
                        id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento,
                        id_persona_creador, activo
                    ) VALUES (
                        :id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket,
                        :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento,
                        :id_persona_creador, 1
                    )
                SQL;
                try {
                    $db->CRUD($queryConCat, $params);
                } catch (\Exception $eIns) {
                    if (stripos($eIns->getMessage(), 'categoria_gestion') !== false || stripos($eIns->getMessage(), 'Unknown column') !== false) {
                        unset($params['categoria_gestion']);
                        $db->CRUD($querySinCat, $params);
                    } else {
                        throw $eIns;
                    }
                }
                self::persistirCanalLevantamientoPostInsert($db, $siguienteId, is_array($datos) ? $datos : [], $catRaw);
                self::persistirEstatusNullSiColumnaExiste($db, $siguienteId);
                $db->commit();

                return self::resultado(true, 'Ticket creado correctamente.', ['folio' => $folio, 'id_ticket' => $siguienteId]);

            } catch (\Exception $e) {
                $db->rollback();

                // Si es error de clave duplicada, reintentar
                $errorMsg = $e->getMessage();
                if (strpos($errorMsg, 'Duplicate entry') !== false || strpos($errorMsg, '1062') !== false) {
                    $intento++;
                    if ($intento >= $maxIntentos) {
                        return self::resultado(false, 'Error al crear el ticket: no se pudo generar un ID único después de varios intentos.', null, $errorMsg);
                    }
                    // Esperar un tiempo aleatorio corto antes de reintentar (evitar contención)
                    usleep(rand(10000, 50000)); // 10-50ms
                    continue;
                }

                // Si es otro tipo de error, retornar inmediatamente
                return self::resultado(false, 'Error al crear el ticket.', null, $errorMsg);
            }
        }

        return self::resultado(false, 'Error al crear el ticket: se agotaron los intentos.', null);
    }

    private static function ticketColumnaExiste(Database $db, string $columnName): bool
    {
        try {
            $row = $db->queryOne(
                "SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket' AND COLUMN_NAME = :c LIMIT 1",
                ['c' => $columnName]
            );

            return !empty($row);
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function ticketTieneColumnasSolicitudVacaciones(Database $db): bool
    {
        foreach (['solicitud_vacaciones_departamento', 'solicitud_vacaciones_fecha_desde', 'solicitud_vacaciones_fecha_hasta', 'solicitud_vacaciones_quien_cubre'] as $c) {
            if (!self::ticketColumnaExiste($db, $c)) {
                return false;
            }
        }

        return true;
    }

    /** MySQL 1054 / SQLSTATE 42S22; mensajes en inglés o español. */
    private static function esErrorMysqlColumnaDesconocida(string $msg, string $nombreColumna): bool
    {
        if (stripos($msg, $nombreColumna) === false) {
            return false;
        }
        if (strpos($msg, '1054') !== false) {
            return true;
        }
        if (stripos($msg, '42S22') !== false) {
            return true;
        }
        if (stripos($msg, 'Unknown column') !== false) {
            return true;
        }
        if (stripos($msg, 'desconocid') !== false) {
            return true;
        }

        return false;
    }

    /**
     * @return '' si no debe persistirse canal; si no, {@see CANAL_LEVANTAMIENTO_WEB} o {@see CANAL_LEVANTAMIENTO_APP_MOVIL}
     */
    private static function resolverCanalLevantamientoNormalizado(array $datos, string $catRaw): string
    {
        $raw = '';
        if (array_key_exists('canal_levantamiento', $datos) && $datos['canal_levantamiento'] !== null) {
            $raw = strtolower(trim((string) $datos['canal_levantamiento']));
        }
        if ($raw === '' && $catRaw === 'solicitud_vacaciones') {
            $raw = self::CANAL_LEVANTAMIENTO_WEB;
        }
        if ($raw === '') {
            return '';
        }
        if ($raw === 'app') {
            $raw = self::CANAL_LEVANTAMIENTO_APP_MOVIL;
        }
        if (in_array($raw, [self::CANAL_LEVANTAMIENTO_WEB, self::CANAL_LEVANTAMIENTO_APP_MOVIL], true)) {
            return $raw;
        }

        return $catRaw === 'solicitud_vacaciones' ? self::CANAL_LEVANTAMIENTO_WEB : '';
    }

    /**
     * Tras INSERT en crearTicketSimple: columnas dedicadas solicitud_vacaciones (requiere ALTER en BD).
     */
    private static function persistirColumnasSolicitudVacacionesPostInsert(Database $db, int $idTicket, array $datos, string $catRaw): void
    {
        if ($catRaw !== 'solicitud_vacaciones' || $idTicket < 1) {
            return;
        }
        if (!self::ticketTieneColumnasSolicitudVacaciones($db)) {
            return;
        }
        $depto = isset($datos['solicitud_vacaciones_departamento']) ? trim((string) $datos['solicitud_vacaciones_departamento']) : '';
        $depto = $depto === '' ? null : mb_substr($depto, 0, 200);
        $qc = isset($datos['solicitud_vacaciones_quien_cubre']) ? trim((string) $datos['solicitud_vacaciones_quien_cubre']) : '';
        $qc = $qc === '' ? null : mb_substr($qc, 0, 255);
        $fd = isset($datos['solicitud_vacaciones_fecha_desde']) ? trim((string) $datos['solicitud_vacaciones_fecha_desde']) : '';
        $fh = isset($datos['solicitud_vacaciones_fecha_hasta']) ? trim((string) $datos['solicitud_vacaciones_fecha_hasta']) : '';
        $fdSql = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fd) ? $fd : null;
        $fhSql = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fh) ? $fh : null;
        $canal = self::resolverCanalLevantamientoNormalizado($datos, $catRaw);
        $paramsBase = ['depto' => $depto, 'fd' => $fdSql, 'fh' => $fhSql, 'qc' => $qc, 'id' => $idTicket, 'cat' => 'solicitud_vacaciones'];
        try {
            if ($canal !== '') {
                try {
                    $db->CRUD(
                        'UPDATE ticket SET solicitud_vacaciones_departamento = :depto, solicitud_vacaciones_fecha_desde = :fd, solicitud_vacaciones_fecha_hasta = :fh, solicitud_vacaciones_quien_cubre = :qc, canal_levantamiento = :canal '
                        . 'WHERE id_ticket = :id AND LOWER(TRIM(COALESCE(categoria_gestion, \'\'))) = :cat',
                        array_merge($paramsBase, ['canal' => $canal])
                    );

                    return;
                } catch (\Exception $eCanal) {
                    if (!self::esErrorMysqlColumnaDesconocida($eCanal->getMessage(), 'canal_levantamiento')) {
                        throw $eCanal;
                    }
                }
            }
            $db->CRUD(
                'UPDATE ticket SET solicitud_vacaciones_departamento = :depto, solicitud_vacaciones_fecha_desde = :fd, solicitud_vacaciones_fecha_hasta = :fh, solicitud_vacaciones_quien_cubre = :qc '
                . 'WHERE id_ticket = :id AND LOWER(TRIM(COALESCE(categoria_gestion, \'\'))) = :cat',
                $paramsBase
            );
        } catch (\Exception $e) {
            error_log('persistirColumnasSolicitudVacacionesPostInsert: ' . $e->getMessage());
        }
    }

    /**
     * Tras INSERT: indica si el ticket se registró desde web o app móvil (columna canal_levantamiento).
     * Valores: {@see CANAL_LEVANTAMIENTO_WEB}, {@see CANAL_LEVANTAMIENTO_APP_MOVIL}.
     */
    private static function persistirCanalLevantamientoPostInsert(Database $db, int $idTicket, array $datos, string $catRaw): void
    {
        if ($idTicket < 1) {
            return;
        }
        $raw = self::resolverCanalLevantamientoNormalizado($datos, $catRaw);
        if ($raw === '') {
            return;
        }
        try {
            $db->CRUD(
                'UPDATE ticket SET canal_levantamiento = :canal WHERE id_ticket = :id',
                ['canal' => $raw, 'id' => $idTicket]
            );
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (self::esErrorMysqlColumnaDesconocida($msg, 'canal_levantamiento')) {
                return;
            }
            error_log('persistirCanalLevantamientoPostInsert: ' . $msg);
        }
    }

    /**
     * Tras INSERT: Sabueso / Validación (y categorías levantadas vía crear / crearTicketSimple) no usan estatus por ahora → NULL explícito.
     */
    private static function persistirEstatusNullSiColumnaExiste(Database $db, int $idTicket): void
    {
        if ($idTicket < 1) {
            return;
        }
        try {
            $db->CRUD(
                'UPDATE ticket SET estatus = NULL WHERE id_ticket = :id',
                ['id' => $idTicket]
            );
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            if (self::esErrorMysqlColumnaDesconocida($msg, 'estatus')) {
                return;
            }
            error_log('persistirEstatusNullSiColumnaExiste: ' . $msg);
        }
    }

    /**
     * True si la tabla ticket tiene las columnas de solicitud de vacaciones (tras ejecutar el ALTER SQL).
     */
    public static function esquemaTieneSolicitudVacacionesColumnas(): bool
    {
        try {
            $db = new Database();
            return self::ticketTieneColumnasSolicitudVacaciones($db);
        } catch (\Exception $e) {
            return false;
        }
    }

    /** Útil para UI/API: si el ticket se levantó desde app móvil (vs web). */
    public static function ticketCanalIndicaAppMovil(?string $canal): bool
    {
        $c = strtolower(trim((string) $canal));

        return $c === self::CANAL_LEVANTAMIENTO_APP_MOVIL || $c === 'app';
    }

    /**
     * Crea un ticket "simple" (por categoría: plantilla, viáticos, validaciones, etc.) sin crédito obligatorio.
     * Se guarda en la misma tabla ticket con categoria_gestion y id_credito NULL.
     * $datos: categoria_gestion (obligatorio), descripcion_inicial (obligatorio), id_credito (opcional),
     *         tipo_categoria (opcional), asunto (opcional), prioridad_categoria (opcional), contacto_telefono (opcional), contacto_email (opcional),
     *         nota (opcional), url_direccion (opcional; se guarda completa, sin recortar).
     *         prefijo_folio (opcional): prefijo de 3 caracteres A–Z para folio PREFIJO-NNNN (por defecto TCK).
     *         fecha_vencimiento (opcional): Y-m-d H:i:s, Y-m-d o d/m/Y; si es solo fecha, fin de día 23:59:59 CDMX.
     *         id_tipo_ticket / id_origen_ticket (opcional): si existen y están activos en catálogo, sustituyen al primer registro por defecto.
     *         solicitud_vacaciones_departamento, solicitud_vacaciones_fecha_desde, solicitud_vacaciones_fecha_hasta (Y-m-d), solicitud_vacaciones_quien_cubre:
     *         opcionales; solo se guardan si categoria_gestion = solicitud_vacaciones y existen las columnas en ticket (script alter_ticket_columnas_solicitud_vacaciones.sql).
     *         Si la categoría es otra, esas claves en $datos se ignoran (no se escriben en BD).
     *         canal_levantamiento (opcional): "web" | "app_movil" (o "app"); si existe la columna en ticket, se guarda tras el INSERT para cualquier categoría.
     * Retorna { success, mensaje, datos: { id_ticket, folio } }.
     */
    public static function crearTicketSimple(array $datos, $idPersonaCreador)
    {
        $idPersonaCreador = (int) $idPersonaCreador;
        if ($idPersonaCreador < 1) {
            return self::resultado(false, 'Sesión inválida.', null);
        }
        $catRaw = isset($datos['categoria_gestion']) ? trim((string) $datos['categoria_gestion']) : '';
        $descripcion = isset($datos['descripcion_inicial']) ? trim((string) $datos['descripcion_inicial']) : '';
        $catRaw = strtolower(preg_replace('/[^a-z0-9_]/', '', str_replace([' ', '-'], '_', $catRaw)));
        if ($catRaw === '') {
            return self::resultado(false, 'La categoría de gestión es obligatoria.', null);
        }
        if ($catRaw !== 'solicitud_vacaciones') {
            unset(
                $datos['solicitud_vacaciones_departamento'],
                $datos['solicitud_vacaciones_fecha_desde'],
                $datos['solicitud_vacaciones_fecha_hasta'],
                $datos['solicitud_vacaciones_quien_cubre'],
                $datos['solicitud_vacaciones_adjunto_nombre_original']
            );
        }
        if ($descripcion === '') {
            return self::resultado(false, 'La descripción es obligatoria.', null);
        }
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' && $datos['id_credito'] !== null
            ? (int) $datos['id_credito'] : null;
        if ($idCredito !== null && $idCredito < 1) {
            $idCredito = null;
        }
        $tipoCategoria = isset($datos['tipo_categoria']) ? trim((string) $datos['tipo_categoria']) : null;
        if ($tipoCategoria !== null && strlen($tipoCategoria) > 150) {
            $tipoCategoria = substr($tipoCategoria, 0, 150);
        }
        $asunto = isset($datos['asunto']) ? trim((string) $datos['asunto']) : null;
        if ($asunto !== null && strlen($asunto) > 255) {
            $asunto = substr($asunto, 0, 255);
        }
        $prioridadCat = isset($datos['prioridad_categoria']) ? trim((string) $datos['prioridad_categoria']) : null;
        if ($prioridadCat !== null && strlen($prioridadCat) > 50) {
            $prioridadCat = substr($prioridadCat, 0, 50);
        }
        $contactoTel = isset($datos['contacto_telefono']) ? trim((string) $datos['contacto_telefono']) : null;
        if ($contactoTel !== null && strlen($contactoTel) > 50) {
            $contactoTel = substr($contactoTel, 0, 50);
        }
        $contactoEmail = isset($datos['contacto_email']) ? trim((string) $datos['contacto_email']) : null;
        if ($contactoEmail !== null && strlen($contactoEmail) > 100) {
            $contactoEmail = substr($contactoEmail, 0, 100);
        }
        $nota = isset($datos['nota']) ? trim((string) $datos['nota']) : null;
        $urlDireccion = isset($datos['url_direccion']) ? trim((string) $datos['url_direccion']) : null;

        try {
            $db = new Database();
            $rowTipo = $db->queryOne("SELECT id_tipo_ticket FROM tipo_ticket WHERE (activo = 1 OR activo IS NULL) ORDER BY id_tipo_ticket ASC LIMIT 1");
            $rowOrigen = $db->queryOne("SELECT id_origen_ticket FROM origen_ticket WHERE (activo = 1 OR activo IS NULL) ORDER BY id_origen_ticket ASC LIMIT 1");
            if (!$rowTipo || !$rowOrigen) {
                return self::resultado(false, 'No hay catálogos de tipo u origen de ticket.', null);
            }
            $idTipo = (int) $rowTipo['id_tipo_ticket'];
            $idOrigen = (int) $rowOrigen['id_origen_ticket'];

            $idTipoReq = isset($datos['id_tipo_ticket']) ? (int) $datos['id_tipo_ticket'] : 0;
            if ($idTipoReq > 0) {
                $chkTipo = $db->queryOne(
                    "SELECT id_tipo_ticket FROM tipo_ticket WHERE id_tipo_ticket = :id AND (activo = 1 OR activo IS NULL) LIMIT 1",
                    ['id' => $idTipoReq]
                );
                if (!empty($chkTipo['id_tipo_ticket'])) {
                    $idTipo = (int) $chkTipo['id_tipo_ticket'];
                }
            }
            $idOrigenReq = isset($datos['id_origen_ticket']) ? (int) $datos['id_origen_ticket'] : 0;
            if ($idOrigenReq > 0) {
                $chkOrigen = $db->queryOne(
                    "SELECT id_origen_ticket FROM origen_ticket WHERE id_origen_ticket = :id AND (activo = 1 OR activo IS NULL) LIMIT 1",
                    ['id' => $idOrigenReq]
                );
                if (!empty($chkOrigen['id_origen_ticket'])) {
                    $idOrigen = (int) $chkOrigen['id_origen_ticket'];
                }
            }

            $rowPrioridad = $db->queryOne("SELECT id_prioridad FROM prioridad_ticket WHERE LOWER(TRIM(nombre)) = 'alta' LIMIT 1");
            if (!$rowPrioridad) {
                $rowPrioridad = $db->queryOne("SELECT id_prioridad FROM prioridad_ticket WHERE LOWER(TRIM(nombre)) LIKE '%alta%' LIMIT 1");
            }
            $idPrioridad = $rowPrioridad ? (int) $rowPrioridad['id_prioridad'] : 0;
            $rowEstado = $db->queryOne("SELECT id_estado_ticket FROM estado_ticket WHERE LOWER(TRIM(nombre)) = 'abierto' AND (activo = 1 OR activo IS NULL) LIMIT 1");
            $idEstado = $rowEstado ? (int) $rowEstado['id_estado_ticket'] : 0;
            if ($idPrioridad < 1 || $idEstado < 1) {
                return self::resultado(false, 'No se encontró prioridad Alta o estado Abierto en catálogo.', null);
            }

            $now = self::ahoraCdmx();
            $fechaVenc = self::cdmxNowImmutable()->modify('+24 hours')->format('Y-m-d H:i:s');
            $fechaVencOverride = self::resolverFechaVencimientoTicketOpcional($datos['fecha_vencimiento'] ?? null);
            if ($fechaVencOverride !== null) {
                $fechaVenc = $fechaVencOverride;
            }

            $prefijoFolio = self::normalizarPrefijoFolio($datos['prefijo_folio'] ?? 'TCK');
            $subFolioStart = strlen($prefijoFolio) + 2;

            $db->beginTransaction();
            $maxRow = $db->queryOne("SELECT MAX(id_ticket) AS max_id FROM ticket FOR UPDATE");
            $siguienteId = ($maxRow && isset($maxRow['max_id']) && $maxRow['max_id'] !== null ? (int) $maxRow['max_id'] : 0) + 1;
            $patFolio = $prefijoFolio . '-%';
            $maxFolioRow = $db->queryOne(
                'SELECT MAX(CAST(SUBSTRING(folio, ' . (int) $subFolioStart . ') AS UNSIGNED)) AS max_num FROM ticket WHERE folio LIKE :pat_folio FOR UPDATE',
                ['pat_folio' => $patFolio]
            );
            $num = ($maxFolioRow && isset($maxFolioRow['max_num']) && $maxFolioRow['max_num'] !== null ? (int) $maxFolioRow['max_num'] : 0) + 1;
            $folio = $prefijoFolio . '-' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);

            $paramsBase = [
                'id_ticket'             => $siguienteId,
                'folio'                 => $folio,
                'id_tipo_ticket'        => $idTipo,
                'id_estado_ticket'      => $idEstado,
                'id_prioridad'          => $idPrioridad,
                'id_origen_ticket'      => $idOrigen,
                'id_credito'            => $idCredito,
                'descripcion_inicial'   => $descripcion,
                'fecha_creacion'        => $now,
                'fecha_vencimiento'     => $fechaVenc,
                'id_persona_creador'    => $idPersonaCreador,
                'tipo_categoria'        => $tipoCategoria,
                'asunto'                => $asunto,
                'prioridad_categoria'   => $prioridadCat,
                'contacto_telefono'     => $contactoTel,
                'contacto_email'        => $contactoEmail,
                'nota'                  => $nota,
                'url_direccion'         => $urlDireccion,
            ];
            $paramsConCat = $paramsBase;
            $paramsConCat['categoria_gestion'] = $catRaw;

            $sqlConCatNotaUrl = "INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, categoria_gestion, tipo_categoria, asunto, prioridad_categoria, contacto_telefono, contacto_email, nota, url_direccion, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :categoria_gestion, :tipo_categoria, :asunto, :prioridad_categoria, :contacto_telefono, :contacto_email, :nota, :url_direccion, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)";
            $sqlSinCatNotaUrl = "INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, tipo_categoria, asunto, prioridad_categoria, contacto_telefono, contacto_email, nota, url_direccion, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :tipo_categoria, :asunto, :prioridad_categoria, :contacto_telefono, :contacto_email, :nota, :url_direccion, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)";
            $sqlConCat = "INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, categoria_gestion, tipo_categoria, asunto, prioridad_categoria, contacto_telefono, contacto_email, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :categoria_gestion, :tipo_categoria, :asunto, :prioridad_categoria, :contacto_telefono, :contacto_email, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)";
            $sqlSinCat = "INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, tipo_categoria, asunto, prioridad_categoria, contacto_telefono, contacto_email, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :tipo_categoria, :asunto, :prioridad_categoria, :contacto_telefono, :contacto_email, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)";
            $sqlConCatSinCols = "INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, categoria_gestion, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :categoria_gestion, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)";
            $sqlSinCatSinCols = "INSERT INTO ticket (id_ticket, folio, id_tipo_ticket, id_estado_ticket, id_prioridad, id_origen_ticket, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, activo) VALUES (:id_ticket, :folio, :id_tipo_ticket, :id_estado_ticket, :id_prioridad, :id_origen_ticket, :id_credito, :descripcion_inicial, :fecha_creacion, :fecha_vencimiento, :id_persona_creador, 1)";

            $paramsBaseSinCols = [
                'id_ticket' => $siguienteId, 'folio' => $folio, 'id_tipo_ticket' => $idTipo, 'id_estado_ticket' => $idEstado,
                'id_prioridad' => $idPrioridad, 'id_origen_ticket' => $idOrigen, 'id_credito' => $idCredito,
                'descripcion_inicial' => $descripcion, 'fecha_creacion' => $now, 'fecha_vencimiento' => $fechaVenc, 'id_persona_creador' => $idPersonaCreador,
            ];
            $paramsConCatSinCols = $paramsBaseSinCols;
            $paramsConCatSinCols['categoria_gestion'] = $catRaw;

            try {
                $db->CRUD($sqlConCatNotaUrl, $paramsConCat);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (stripos($msg, 'Unknown column') !== false && (stripos($msg, 'nota') !== false || stripos($msg, 'url_direccion') !== false)) {
                    try {
                        $db->CRUD($sqlConCat, $paramsConCat);
                    } catch (\Exception $e2) {
                        $msg2 = $e2->getMessage();
                        if (stripos($msg2, 'Unknown column') !== false && (stripos($msg2, 'tipo_categoria') !== false || stripos($msg2, 'asunto') !== false)) {
                            try {
                                $db->CRUD($sqlConCatSinCols, $paramsConCatSinCols);
                            } catch (\Exception $e3) {
                                if (stripos($e3->getMessage(), 'categoria_gestion') !== false || stripos($e3->getMessage(), 'Unknown column') !== false) {
                                    $db->CRUD($sqlSinCatSinCols, $paramsBaseSinCols);
                                } else {
                                    throw $e3;
                                }
                            }
                        } elseif (stripos($msg2, 'categoria_gestion') !== false || stripos($msg2, 'Unknown column') !== false) {
                            try {
                                $db->CRUD($sqlSinCat, $paramsBase);
                            } catch (\Exception $e3) {
                                if (stripos($e3->getMessage(), 'Unknown column') !== false) {
                                    $db->CRUD($sqlSinCatSinCols, $paramsBaseSinCols);
                                } else {
                                    throw $e3;
                                }
                            }
                        } else {
                            throw $e2;
                        }
                    }
                } elseif (stripos($msg, 'Unknown column') !== false && (stripos($msg, 'tipo_categoria') !== false || stripos($msg, 'asunto') !== false)) {
                    try {
                        $db->CRUD($sqlConCatSinCols, $paramsConCatSinCols);
                    } catch (\Exception $e2) {
                        if (stripos($e2->getMessage(), 'categoria_gestion') !== false || stripos($e2->getMessage(), 'Unknown column') !== false) {
                            $db->CRUD($sqlSinCatSinCols, $paramsBaseSinCols);
                        } else {
                            throw $e2;
                        }
                    }
                } elseif (stripos($msg, 'categoria_gestion') !== false || stripos($msg, 'Unknown column') !== false) {
                    try {
                        $db->CRUD($sqlSinCatNotaUrl, $paramsBase);
                    } catch (\Exception $e2) {
                        if (stripos($e2->getMessage(), 'Unknown column') !== false && (stripos($e2->getMessage(), 'nota') !== false || stripos($e2->getMessage(), 'url_direccion') !== false)) {
                            try {
                                $db->CRUD($sqlSinCat, $paramsBase);
                            } catch (\Exception $e3) {
                                if (stripos($e3->getMessage(), 'Unknown column') !== false) {
                                    $db->CRUD($sqlSinCatSinCols, $paramsBaseSinCols);
                                } else {
                                    throw $e3;
                                }
                            }
                        } elseif (stripos($e2->getMessage(), 'Unknown column') !== false) {
                            $db->CRUD($sqlSinCatSinCols, $paramsBaseSinCols);
                        } else {
                            throw $e2;
                        }
                    }
                } else {
                    throw $e;
                }
            }
            self::persistirColumnasSolicitudVacacionesPostInsert($db, $siguienteId, $datos, $catRaw);
            self::persistirCanalLevantamientoPostInsert($db, $siguienteId, $datos, $catRaw);
            self::persistirEstatusNullSiColumnaExiste($db, $siguienteId);
            $db->commit();
            return self::resultado(true, 'Ticket registrado correctamente.', ['id_ticket' => $siguienteId, 'folio' => $folio]);
        } catch (\Exception $e) {
            if (isset($db) && $db) {
                try { $db->rollback(); } catch (\Exception $e2) {}
            }
            return self::resultado(false, 'Error al crear el ticket.', null, $e->getMessage());
        }
    }

    /**
     * Tickets asociados a un id_credito (para mostrar en modal de datos del crédito).
     */
    public static function getTicketsPorIdCredito($idCredito)
    {
        $id = (int)$idCredito;
        if ($id < 1) {
            return [];
        }
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT t.id_ticket, t.folio, t.descripcion_inicial, t.fecha_creacion, t.fecha_vencimiento, " .
                "tt.nombre AS tipo_nombre, et.nombre AS estado_nombre, pt.nombre AS prioridad_nombre, ot.nombre AS origen_nombre " .
                "FROM ticket t " .
                "INNER JOIN tipo_ticket tt ON t.id_tipo_ticket = tt.id_tipo_ticket " .
                "INNER JOIN estado_ticket et ON t.id_estado_ticket = et.id_estado_ticket " .
                "INNER JOIN prioridad_ticket pt ON t.id_prioridad = pt.id_prioridad " .
                "INNER JOIN origen_ticket ot ON t.id_origen_ticket = ot.id_origen_ticket " .
                "WHERE t.id_credito = :id AND (t.activo = 1 OR t.activo IS NULL) ORDER BY t.fecha_creacion DESC",
                ['id' => $id]
            );
            return is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            try {
                $rows = $db->queryAll(
                    "SELECT t.id_ticket, t.folio, t.descripcion_inicial, t.fecha_creacion, t.fecha_vencimiento, " .
                    "tt.nombre AS tipo_nombre, et.nombre AS estado_nombre, pt.nombre AS prioridad_nombre, ot.nombre AS origen_nombre " .
                    "FROM ticket t " .
                    "INNER JOIN tipo_ticket tt ON t.id_tipo_ticket = tt.id_tipo_ticket " .
                    "INNER JOIN estado_ticket et ON t.id_estado_ticket = et.id_estado_ticket " .
                    "INNER JOIN prioridad_ticket pt ON t.id_prioridad = pt.id_prioridad " .
                    "INNER JOIN origen_ticket ot ON t.id_origen_ticket = ot.id_origen_ticket " .
                    "WHERE t.id_credito = :id ORDER BY t.fecha_creacion DESC",
                    ['id' => $id]
                );
                return is_array($rows) ? $rows : [];
            } catch (\Exception $e2) {
                return [];
            }
        }
    }

    /**
     * Indica si el usuario (creador) ya tiene al menos un ticket activo con ese id_credito.
     * Solo se considera duplicado para el mismo gestor que levantó otro ticket con el mismo crédito.
     *
     * @param int $idCredito
     * @param int $idPersonaCreador
     * @return bool
     */
    public static function tieneTicketConCreditoPorCreador($idCredito, $idPersonaCreador)
    {
        $id = (int) $idCredito;
        $idCreador = (int) $idPersonaCreador;
        if ($id < 1 || $idCreador < 1) {
            return false;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT 1 FROM ticket WHERE id_credito = :id_credito AND id_persona_creador = :id_creador AND (activo = 1 OR activo IS NULL) LIMIT 1",
                ['id_credito' => $id, 'id_creador' => $idCreador]
            );
            return !empty($row);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Último ticket activo del mismo creador para un crédito.
     * Se usa para mostrar contexto en la alerta de posible duplicado.
     *
     * @param int $idCredito
     * @param int $idPersonaCreador
     * @return array|null { id_ticket, folio, fecha_creacion }
     */
    public static function getUltimoTicketActivoConCreditoPorCreador($idCredito, $idPersonaCreador): ?array
    {
        $id = (int)$idCredito;
        $idCreador = (int)$idPersonaCreador;
        if ($id < 1 || $idCreador < 1) {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id_ticket, folio, fecha_creacion " .
                "FROM ticket " .
                "WHERE id_credito = :id_credito AND id_persona_creador = :id_creador AND (activo = 1 OR activo IS NULL) " .
                "ORDER BY fecha_creacion DESC LIMIT 1",
                ['id_credito' => $id, 'id_creador' => $idCreador]
            );
            if (!$row) {
                return null;
            }
            return [
                'id_ticket' => isset($row['id_ticket']) ? (int)$row['id_ticket'] : null,
                'folio' => $row['folio'] ?? null,
                'fecha_creacion' => $row['fecha_creacion'] ?? null,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * ID del origen de ticket "WhatsApp" (para tickets creados por el bot).
     * @return int 0 si no existe.
     */
    public static function getIdOrigenWhatsApp()
    {
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id_origen_ticket AS id FROM origen_ticket WHERE LOWER(TRIM(nombre)) = 'whatsapp' AND (activo = 1 OR activo IS NULL) LIMIT 1");
            return $row ? (int)$row['id'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ID de la persona "Bot WhatsApp" (user_name = bot_whatsapp) para id_persona_creador.
     * @return int 0 si no existe.
     */
    public static function getIdPersonaBotWhatsApp()
    {
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id FROM persona WHERE user_name = 'bot_whatsapp' LIMIT 1");
            return $row ? (int)$row['id'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Si existe la columna id_persona_quien_asigno en asignacion_ticket (script de migración).
     */
    private static function asignacionTicketTieneColumnaQuienAsigno(): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        try {
            $db = new Database();
            $col = $db->queryOne("SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'asignacion_ticket' AND COLUMN_NAME = 'id_persona_quien_asigno' LIMIT 1");
            $cache = !empty($col);
        } catch (\Exception $e) {
            $cache = false;
        }

        return $cache;
    }

    /**
     * Asigna un ticket a una persona usando la tabla asignacion_ticket.
     * Desactiva la asignación anterior (activo=0, fecha_liberacion=ahora CDMX) e inserta la nueva.
     *
     * @param int|null $idPersonaQuienAsigno persona que ejecuta la asignación (p. ej. jefe territorial); opcional
     */
    public static function asignar($idTicket, $idPersona, $idPersonaQuienAsigno = null)
    {
        $tid = (int)$idTicket;
        $pid = (int)$idPersona;
        if ($tid < 1 || $pid < 1) {
            return self::resultado(false, 'ID de ticket o persona inválido.', null);
        }
        $quien = $idPersonaQuienAsigno !== null ? (int)$idPersonaQuienAsigno : 0;
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $db->CRUD(
                "UPDATE asignacion_ticket SET activo = 0, fecha_liberacion = :ahora WHERE id_ticket = :id_ticket AND (activo = 1 OR activo IS NULL)",
                ['ahora' => $now, 'id_ticket' => $tid]
            );
            if (self::asignacionTicketTieneColumnaQuienAsigno() && $quien > 0) {
                $db->CRUD(
                    "INSERT INTO asignacion_ticket (id_ticket, id_persona_asignada, id_persona_quien_asigno, fecha_asignacion, activo) VALUES (:id_ticket, :id_persona, :quien, :fecha_asignacion, 1)",
                    ['id_ticket' => $tid, 'id_persona' => $pid, 'quien' => $quien, 'fecha_asignacion' => $now]
                );
            } else {
                $db->CRUD(
                    "INSERT INTO asignacion_ticket (id_ticket, id_persona_asignada, fecha_asignacion, activo) VALUES (:id_ticket, :id_persona, :fecha_asignacion, 1)",
                    ['id_ticket' => $tid, 'id_persona' => $pid, 'fecha_asignacion' => $now]
                );
            }
            $idAsignacion = $db->lastInsertId();
            if ($idAsignacion < 1) {
                $rowId = $db->queryOne(
                    "SELECT id_asignacion FROM asignacion_ticket WHERE id_ticket = :id_ticket AND id_persona_asignada = :id_persona AND (activo = 1 OR activo IS NULL) ORDER BY fecha_asignacion DESC, id_asignacion DESC LIMIT 1",
                    ['id_ticket' => $tid, 'id_persona' => $pid]
                );
                $idAsignacion = $rowId && isset($rowId['id_asignacion']) ? (int) $rowId['id_asignacion'] : 0;
            }
            return self::resultado(true, 'Ticket asignado correctamente.', ['id_asignacion' => $idAsignacion]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al asignar el ticket.', null, $e->getMessage());
        }
    }

    /**
     * Quitar la asignación actual del ticket (activo = 0 en asignacion_ticket).
     */
    public static function quitarAsignacion($idTicket)
    {
        $tid = (int)$idTicket;
        if ($tid < 1) {
            return self::resultado(false, 'ID de ticket inválido.', null);
        }
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $db->CRUD(
                "UPDATE asignacion_ticket SET activo = 0, fecha_liberacion = :ahora WHERE id_ticket = :id_ticket AND (activo = 1 OR activo IS NULL)",
                ['ahora' => $now, 'id_ticket' => $tid]
            );
            return self::resultado(true, 'Asignación quitada correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al quitar la asignación.', null, $e->getMessage());
        }
    }

    /**
     * id_persona de la asignación activa del ticket (activo = 1), o 0 si no hay.
     */
    public static function getIdPersonaAsignadaActivaPorTicket(int $idTicket): int
    {
        $tid = (int) $idTicket;
        if ($tid < 1) {
            return 0;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                'SELECT id_persona_asignada FROM asignacion_ticket WHERE id_ticket = :id AND (activo = 1 OR activo IS NULL) ORDER BY fecha_asignacion DESC LIMIT 1',
                ['id' => $tid]
            );

            return $row && isset($row['id_persona_asignada']) ? (int) $row['id_persona_asignada'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Asignación activa del gestor a un ticket de categoría validaciones.
     */
    public static function personaTieneAsignacionActivaTicketValidaciones(int $personaId, int $idTicket): bool
    {
        $pid = (int) $personaId;
        $tid = (int) $idTicket;
        if ($pid < 1 || $tid < 1) {
            return false;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT 1 AS ok
                 FROM asignacion_ticket at
                 INNER JOIN ticket t ON t.id_ticket = at.id_ticket
                 WHERE at.id_ticket = :id_ticket
                   AND at.id_persona_asignada = :id_persona
                   AND (at.activo = 1 OR at.activo IS NULL)
                   AND (t.activo = 1 OR t.activo IS NULL)
                   AND LOWER(COALESCE(NULLIF(TRIM(t.categoria_gestion),''), 'sabueso')) = 'validaciones'
                 LIMIT 1",
                ['id_ticket' => $tid, 'id_persona' => $pid]
            );

            return !empty($row);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * id_credito del ticket (para registrar historial de asignación por crédito).
     */
    public static function getIdCreditoPorTicket(int $idTicket): ?int
    {
        if ($idTicket < 1) {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id_credito FROM ticket WHERE id_ticket = :id AND (activo = 1 OR activo IS NULL)", ['id' => $idTicket]);
            return $row && isset($row['id_credito']) ? (int) $row['id_credito'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Nombre completo de una persona por id (para "Tomar asignación").
     */
    public static function getNombrePersona($idPersona)
    {
        $id = (int)$idPersona;
        if ($id < 1) return '';
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT CONCAT(TRIM(IFNULL(nombres,'')), ' ', TRIM(IFNULL(apellidop,''))) AS nombre FROM persona WHERE id = :id", ['id' => $id]);
            return $row ? trim($row['nombre']) : '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Persona activa por número de empleado (misma lógica que login: estatus Activo).
     * Usado por la app móvil que identifica al solicitante con numero_empleado en lugar de id.
     *
     * @return array{id: int, nombre: string, numero_empleado: string}|null
     */
    public static function getPersonaActivaPorNumeroEmpleado(string $numeroEmpleado): ?array
    {
        $ne = trim($numeroEmpleado);
        if ($ne === '') {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                'SELECT p.id, TRIM(CONCAT(TRIM(IFNULL(p.nombres, \'\')), \' \', TRIM(IFNULL(p.apellidop, \'\')))) AS nombre, TRIM(p.numero_empleado) AS numero_empleado '
                . 'FROM persona p WHERE TRIM(p.numero_empleado) = :ne AND p.estatus = \'Activo\' LIMIT 1',
                ['ne' => $ne]
            );
            if (!$row || (int) ($row['id'] ?? 0) < 1) {
                return null;
            }

            return [
                'id'               => (int) $row['id'],
                'nombre'           => trim((string) ($row['nombre'] ?? '')),
                'numero_empleado'  => trim((string) ($row['numero_empleado'] ?? '')),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Personas del departamento Sabueso (id 5): activas, con puesto asignado en ese departamento.
     */
    public static function getPersonasDepartamentoSabueso()
    {
        $idDepartamento = 5;
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT DISTINCT p.id, CONCAT(TRIM(IFNULL(p.nombres,'')), ' ', TRIM(IFNULL(p.apellidop,''))) AS nombre_completo " .
                "FROM persona p " .
                "INNER JOIN asigna_puesto ap ON ap.id_persona = p.id AND (ap.activo = 1 OR ap.activo IS NULL) " .
                "INNER JOIN puesto pu ON pu.id = ap.id_puesto AND pu.departamento_id = :dep " .
                "WHERE (p.estatus = 'Activo' OR p.estatus IS NULL) AND p.estatus != 'Baja' " .
                "ORDER BY nombre_completo",
                ['dep' => $idDepartamento]
            );
            return self::resultado(true, 'OK', is_array($rows) ? $rows : []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener personas.', null, $e->getMessage());
        }
    }

    /** Segmento 1–7 por nombre de departamento. */
    private static function esDepartamentoCampo1a7($nombreDepartamento)
    {
        $n = mb_strtolower(trim((string)$nombreDepartamento));
        return $n !== '' && (bool) preg_match('/campo.*1\s*[-–]?\s*7|1\s*[-–]?\s*7.*campo|1\s+a\s+7/i', $n);
    }

    /** Segmento 8–21 por nombre de departamento. */
    private static function esDepartamentoCampo8a21($nombreDepartamento)
    {
        $n = mb_strtolower(trim((string)$nombreDepartamento));
        return $n !== '' && (bool) preg_match('/campo.*8\s*[-–]?\s*21|8\s*[-–]?\s*21.*campo|8\s+a\s+21/i', $n);
    }

    /**
     * Busca el id del departamento "Campo 1-7" o "Campo 8-21" por nombre en tabla departamento.
     */
    private static function getDepartamentoCampoPorSegmento($campo)
    {
        try {
            $db = new Database();
            $rows = $db->queryAll("SELECT id, nombre FROM departamento ORDER BY nombre");
            if (!is_array($rows)) {
                return 0;
            }
            foreach ($rows as $r) {
                $id = (int) ($r['id'] ?? 0);
                $nombre = (string) ($r['nombre'] ?? '');
                if ($id < 1 || $nombre === '') {
                    continue;
                }
                if ($campo === '1_7' && self::esDepartamentoCampo1a7($nombre)) {
                    return $id;
                }
                if ($campo === '8_21' && self::esDepartamentoCampo8a21($nombre)) {
                    return $id;
                }
            }
        } catch (\Exception $e) {
            return 0;
        }
        return 0;
    }

    /**
     * Personas de máximo rango por segmento (misma lógica de Organigrama).
     * Toma el departamento "Campo 1-7" o "Campo 8-21" y llama al mismo DAO del organigrama.
     *
     * @param string $campo '1_7' | '8_21'
     */
    public static function getPersonasJefesSabuesoPorCampoMorosidad($campo)
    {
        $campo = strtolower(trim((string)$campo));
        if ($campo !== '1_7' && $campo !== '8_21') {
            return self::resultado(false, 'Segmento inválido.', []);
        }
        $idDepartamento = self::getDepartamentoCampoPorSegmento($campo);
        if ($idDepartamento < 1) {
            $txt = $campo === '1_7' ? 'Campo 1-7' : 'Campo 8-21';
            return self::resultado(true, 'No se encontró el departamento "' . $txt . '" en Organigrama.', []);
        }

        $res = CapHum::getPersonasOrganigrama($idDepartamento, 0);
        $datos = is_array($res['datos'] ?? null) ? $res['datos'] : [];
        $out = [];
        $seen = [];
        foreach ($datos as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1 || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $out[] = [
                'id' => $id,
                'nombre_completo' => trim((string) ($row['nombre'] ?? $row['nombre_completo'] ?? '')),
                'nombre_puesto' => '',
            ];
        }
        usort($out, function ($a, $b) {
            return strcasecmp($a['nombre_completo'], $b['nombre_completo']);
        });

        return self::resultado(true, empty($out) ? 'No hay personas de máximo rango para este segmento.' : 'OK', $out);
    }

    /**
     * Inserta un registro en ticket_historico (snapshot del ticket) y luego hace soft-delete en ticket.
     *
     * @param int $idTicket
     * @param string $tipoAccion 'cerrado' | 'eliminado'
     * @param int|null $idPersonaElimino Quien cerró/eliminó (sesión).
     */
    public static function registrarEnHistorico($idTicket, $tipoAccion, $idPersonaElimino = null)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return;
        }
        $tipoAccion = strtolower((string)$tipoAccion) === 'cerrado' ? 'cerrado' : 'eliminado';
        try {
            $db = new Database();
            $now = self::ahoraCdmx();

            $row = $db->queryOne(
                "SELECT t.id_ticket, t.id_credito, t.folio, t.id_tipo_ticket, t.id_estado_ticket, t.id_prioridad, t.descripcion_inicial, " .
                "t.fecha_creacion, t.fecha_vencimiento, t.id_persona_creador, " .
                "tt.nombre AS tipo_ticket_nombre, et.nombre AS estado_ticket_nombre, pt.nombre AS prioridad_nombre, " .
                "CONCAT(TRIM(IFNULL(p.nombres,'')), ' ', TRIM(IFNULL(p.apellidop,''))) AS creador_nombre, " .
                "CONCAT(TRIM(IFNULL(pe.nombres,'')), ' ', TRIM(IFNULL(pe.apellidop,''))) AS quien_elimino_nombre " .
                "FROM ticket t " .
                "INNER JOIN tipo_ticket tt ON t.id_tipo_ticket = tt.id_tipo_ticket " .
                "INNER JOIN estado_ticket et ON t.id_estado_ticket = et.id_estado_ticket " .
                "INNER JOIN prioridad_ticket pt ON t.id_prioridad = pt.id_prioridad " .
                "INNER JOIN persona p ON t.id_persona_creador = p.id " .
                "LEFT JOIN persona pe ON t.id_persona_elimino = pe.id " .
                "WHERE t.id_ticket = :id AND (t.activo = 1 OR t.activo IS NULL)",
                ['id' => $id]
            );
            if (!$row) {
                return;
            }
            $asignado = self::getUltimoAsignadoPorTicket($id);
            $quienElimino = $idPersonaElimino !== null ? trim(self::getNombrePersona((int)$idPersonaElimino)) : '';
            if ($quienElimino === '' && isset($row['quien_elimino_nombre'])) {
                $quienElimino = trim($row['quien_elimino_nombre'] ?? '');
            }

            $db->CRUD(
                "INSERT INTO ticket_historico (id_ticket, id_credito, folio, id_tipo_ticket, tipo_ticket_nombre, id_estado_ticket, estado_ticket_nombre, " .
                "id_prioridad, prioridad_nombre, descripcion_inicial, fecha_creacion, fecha_vencimiento, id_persona_creador, creador_nombre, asignado_nombre, " .
                "id_persona_elimino, quien_elimino_nombre, fecha_eliminacion, tipo_accion) " .
                "VALUES (:id_ticket, :id_credito, :folio, :id_tipo, :tipo_nombre, :id_estado, :estado_nombre, :id_prioridad, :prioridad_nombre, :descripcion, " .
                ":fecha_creacion, :fecha_vencimiento, :id_creador, :creador_nombre, :asignado_nombre, :id_elimino, :quien_elimino, :fecha_eliminacion, :tipo_accion)",
                [
                    'id_ticket' => $id,
                    'id_credito' => $row['id_credito'] ?? null,
                    'folio' => $row['folio'] ?? null,
                    'id_tipo' => $row['id_tipo_ticket'] ?? null,
                    'tipo_nombre' => $row['tipo_ticket_nombre'] ?? null,
                    'id_estado' => $row['id_estado_ticket'] ?? null,
                    'estado_nombre' => $row['estado_ticket_nombre'] ?? null,
                    'id_prioridad' => $row['id_prioridad'] ?? null,
                    'prioridad_nombre' => $row['prioridad_nombre'] ?? null,
                    'descripcion' => $row['descripcion_inicial'] ?? null,
                    'fecha_creacion' => $row['fecha_creacion'] ?? null,
                    'fecha_vencimiento' => $row['fecha_vencimiento'] ?? null,
                    'id_creador' => $row['id_persona_creador'] ?? null,
                    'creador_nombre' => $row['creador_nombre'] ?? null,
                    'asignado_nombre' => $asignado !== '—' ? $asignado : null,
                    'id_elimino' => $idPersonaElimino !== null ? (int)$idPersonaElimino : null,
                    'quien_elimino' => $quienElimino !== '' ? $quienElimino : null,
                    'fecha_eliminacion' => $now,
                    'tipo_accion' => $tipoAccion,
                ]
            );
        } catch (\Exception $e) {
            // Log opcional; no fallar el flujo
        }
    }

    /**
     * Marca el ticket como eliminado (soft delete): registra en ticket_historico y luego activo=0, fecha_eliminacion, id_persona_elimino.
     *
     * @param int $idTicket
     * @param int|null $idPersonaElimino Quien eliminó (sesión); null = no registrar.
     */
    public static function eliminar($idTicket, $idPersonaElimino = null)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', null);
        }
        try {
            self::registrarEnHistorico($id, 'eliminado', $idPersonaElimino);
            $db = new Database();
            $now = self::ahoraCdmx();
            $idPersona = $idPersonaElimino !== null ? (int)$idPersonaElimino : null;
            try {
                $db->CRUD(
                    "UPDATE ticket SET activo = 0, fecha_eliminacion = :ahora, id_persona_elimino = :id_persona WHERE id_ticket = :id",
                    ['ahora' => $now, 'id_persona' => $idPersona, 'id' => $id]
                );
            } catch (\Exception $e) {
                if (stripos($e->getMessage(), 'activo') !== false) {
                    $db->CRUD(
                        "UPDATE ticket SET fecha_eliminacion = :ahora, id_persona_elimino = :id_persona WHERE id_ticket = :id",
                        ['ahora' => $now, 'id_persona' => $idPersona, 'id' => $id]
                    );
                } else {
                    throw $e;
                }
            }
            return self::resultado(true, 'Ticket eliminado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar el ticket.', null, $e->getMessage());
        }
    }

    /**
     * Cierra el ticket: registra en ticket_historico (tipo_accion=cerrado) y hace soft-delete igual que eliminar.
     *
     * @param int $idTicket
     * @param int|null $idPersonaCierra Quien cerró (sesión).
     */
    public static function cerrar($idTicket, $idPersonaCierra = null)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', null);
        }
        try {
            self::registrarEnHistorico($id, 'cerrado', $idPersonaCierra);
            $db = new Database();
            $now = self::ahoraCdmx();
            $idPersona = $idPersonaCierra !== null ? (int)$idPersonaCierra : null;
            try {
                $db->CRUD(
                    "UPDATE ticket SET activo = 0, fecha_eliminacion = :ahora, id_persona_elimino = :id_persona WHERE id_ticket = :id",
                    ['ahora' => $now, 'id_persona' => $idPersona, 'id' => $id]
                );
            } catch (\Exception $e) {
                if (stripos($e->getMessage(), 'activo') !== false) {
                    $db->CRUD(
                        "UPDATE ticket SET fecha_eliminacion = :ahora, id_persona_elimino = :id_persona WHERE id_ticket = :id",
                        ['ahora' => $now, 'id_persona' => $idPersona, 'id' => $id]
                    );
                } else {
                    throw $e;
                }
            }
            return self::resultado(true, 'Ticket cerrado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cerrar el ticket.', null, $e->getMessage());
        }
    }

    /**
     * Lista de tickets cerrados/eliminados desde ticket_historico (con tipo_accion: cerrado | eliminado).
     */
    public static function getListaTicketsCerradosEliminados()
    {
        $query = <<<SQL
            SELECT
                id_ticket,
                folio,
                id_credito,
                descripcion_inicial,
                fecha_creacion,
                fecha_vencimiento,
                fecha_eliminacion,
                id_persona_elimino,
                tipo_ticket_nombre,
                estado_ticket_nombre,
                prioridad_nombre,
                creador_nombre,
                asignado_nombre,
                quien_elimino_nombre,
                tipo_accion
            FROM ticket_historico
            ORDER BY fecha_eliminacion DESC
        SQL;
        try {
            $db = new Database();
            $rows = $db->queryAll($query);
            $rows = is_array($rows) ? $rows : [];
            return self::resultado(true, 'Tickets encontrados.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar tickets.', null, $e->getMessage());
        }
    }

    /**
     * Un ticket cerrado/eliminado por id_ticket desde ticket_historico (último registro para ese id_ticket).
     */
    public static function getTicketCerradoEliminadoPorId(int $idTicket): ?array
    {
        if ($idTicket < 1) {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id_ticket, folio, id_credito, descripcion_inicial, fecha_creacion, fecha_vencimiento, fecha_eliminacion, " .
                "id_persona_elimino, tipo_ticket_nombre, estado_ticket_nombre, prioridad_nombre, creador_nombre, asignado_nombre, " .
                "quien_elimino_nombre, tipo_accion FROM ticket_historico WHERE id_ticket = :id ORDER BY fecha_eliminacion DESC LIMIT 1",
                ['id' => $idTicket]
            );
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Historial de asignación por ticket (para modal rastreo: "Asignado a" por ticket, no por crédito).
     * Devuelve asignado_actual (nombre), estado, historial (persona, desde, hasta, duracion_humana).
     */
    public static function getHistorialAsignacionPorTicket(int $idTicket): array
    {
        if ($idTicket < 1) {
            return [
                'asignado_actual' => null,
                'estado'          => 'primera_asignacion',
                'historial'       => [],
            ];
        }
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT at.id_asignacion, at.id_persona_asignada, at.fecha_asignacion, at.fecha_liberacion, at.activo, " .
                "CONCAT(TRIM(IFNULL(p.nombres,'')), ' ', TRIM(IFNULL(p.apellidop,''))) AS persona_nombre " .
                "FROM asignacion_ticket at INNER JOIN persona p ON at.id_persona_asignada = p.id " .
                "WHERE at.id_ticket = :id ORDER BY at.fecha_asignacion DESC",
                ['id' => $idTicket]
            );
            $rows = is_array($rows) ? $rows : [];
            $now = self::cdmxNowImmutable()->getTimestamp();
            $asignadoActual = null;
            $historial = [];
            foreach ($rows as $r) {
                $desdeTs = strtotime($r['fecha_asignacion']);
                $hastaRaw = $r['fecha_liberacion'] ?? null;
                $activo = (int)($r['activo'] ?? 0);
                $hastaTs = $hastaRaw ? strtotime($hastaRaw) : ($activo ? $now : ($desdeTs ?: $now));
                if ($activo === 1) {
                    $asignadoActual = trim($r['persona_nombre'] ?? '');
                }
                $desdeFmt = $desdeTs ? date('Y-m-d H:i', $desdeTs) : '—';
                $hastaFmt = $hastaRaw ? date('Y-m-d H:i', strtotime($hastaRaw)) : ($activo ? date('Y-m-d H:i', $now) : '—');
                $duracionHumana = self::duracionHumanaAsignacion($desdeTs, $hastaTs);
                $historial[] = [
                    'persona'         => trim($r['persona_nombre'] ?? ''),
                    'desde'           => $desdeFmt,
                    'hasta'           => $hastaFmt,
                    'duracion_humana' => $duracionHumana,
                ];
            }
            if (count($rows) === 0) {
                $estado = 'primera_asignacion';
            } elseif ($asignadoActual !== null && $asignadoActual !== '') {
                $estado = 'con_historial';
            } else {
                $estado = 'sin_asignar';
            }
            return [
                'asignado_actual' => $asignadoActual !== '' ? $asignadoActual : null,
                'estado'         => $estado,
                'historial'      => $historial,
            ];
        } catch (\Exception $e) {
            return [
                'asignado_actual' => null,
                'estado'          => 'primera_asignacion',
                'historial'       => [],
            ];
        }
    }

    private static function duracionHumanaAsignacion($desdeTs, $hastaTs): string
    {
        if (!$desdeTs || !$hastaTs || $hastaTs < $desdeTs) {
            return '—';
        }
        $seg = $hastaTs - $desdeTs;
        if ($seg < 60) {
            return $seg . ' s';
        }
        if ($seg < 3600) {
            return round($seg / 60) . ' min';
        }
        if ($seg < 86400) {
            return round($seg / 3600, 1) . ' h';
        }
        return round($seg / 86400) . ' días';
    }

    /**
     * Última persona asignada al ticket (desde asignacion_ticket, activo o no).
     */
    public static function getUltimoAsignadoPorTicket(int $idTicket): string
    {
        if ($idTicket < 1) {
            return '—';
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT CONCAT(TRIM(IFNULL(p.nombres,'')), ' ', TRIM(IFNULL(p.apellidop,''))) AS nombre " .
                "FROM asignacion_ticket at INNER JOIN persona p ON at.id_persona_asignada = p.id " .
                "WHERE at.id_ticket = :id ORDER BY at.fecha_asignacion DESC LIMIT 1",
                ['id' => $idTicket]
            );
            return $row ? trim($row['nombre']) : '—';
        } catch (\Exception $e) {
            return '—';
        }
    }

    /**
     * ID de la última persona asignada al ticket (para notificaciones de dictamen enviado).
     */
    public static function getUltimoAsignadoIdPorTicket(int $idTicket): int
    {
        if ($idTicket < 1) {
            return 0;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT at.id_persona_asignada FROM asignacion_ticket at WHERE at.id_ticket = :id ORDER BY at.fecha_asignacion DESC LIMIT 1",
                ['id' => $idTicket]
            );
            return $row ? (int)$row['id_persona_asignada'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ID del creador del ticket (quien levantó el ticket = gestor que recibe el dictamen en menú Ticket).
     */
    public static function getCreadorIdPorTicket(int $idTicket): int
    {
        if ($idTicket < 1) {
            return 0;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id_persona_creador FROM ticket WHERE id_ticket = :id AND (activo = 1 OR activo IS NULL) LIMIT 1",
                ['id' => $idTicket]
            );
            return $row ? (int)$row['id_persona_creador'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Folio del ticket (ej. TCK-0001) para mensajes de notificación.
     */
    public static function getFolioPorTicket(int $idTicket): string
    {
        if ($idTicket < 1) {
            return '';
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT folio FROM ticket WHERE id_ticket = :id LIMIT 1", ['id' => $idTicket]);
            return $row && isset($row['folio']) ? trim((string)$row['folio']) : '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * ID de la persona que envió el dictamen al gestor (autor del dictamen enviado) para notificación "dictamen revisado".
     */
    public static function getDictamenAutorIdPorTicket(int $idTicket): int
    {
        if ($idTicket < 1) {
            return 0;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id_persona FROM dictamen WHERE id_ticket = :id AND estado = 'enviado_al_gestor' ORDER BY fecha_creacion DESC LIMIT 1",
                ['id' => $idTicket]
            );
            return $row ? (int)$row['id_persona'] : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Mensajes del chat (bitácora) por ticket.
     */
    public static function getChatPorTicket($idTicket)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', []);
        }
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT c.id, c.id_ticket, c.id_persona, c.mensaje, c.fecha_creacion, " .
                "CONCAT(TRIM(IFNULL(p.nombres,'')), ' ', TRIM(IFNULL(p.apellidop,''))) AS persona_nombre " .
                "FROM chat c INNER JOIN persona p ON c.id_persona = p.id " .
                "WHERE c.id_ticket = :id_ticket ORDER BY c.fecha_creacion ASC",
                ['id_ticket' => $id]
            );
            return self::resultado(true, 'OK', is_array($rows) ? $rows : []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener chat.', null, $e->getMessage());
        }
    }

    /**
     * Agregar mensaje al chat (bitácora) del ticket.
     */
    public static function agregarChat($idTicket, $idPersona, $mensaje)
    {
        $tid = (int)$idTicket;
        $pid = (int)$idPersona;
        $msg = trim((string)$mensaje);
        if ($tid < 1 || $pid < 1 || $msg === '') {
            return self::resultado(false, 'Datos inválidos.', null);
        }
        if (strlen($msg) > 2000) {
            return self::resultado(false, 'Mensaje demasiado largo.', null);
        }
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $db->CRUD(
                "INSERT INTO chat (id_ticket, id_persona, mensaje, fecha_creacion) VALUES (:id_ticket, :id_persona, :mensaje, :fecha_creacion)",
                ['id_ticket' => $tid, 'id_persona' => $pid, 'mensaje' => $msg, 'fecha_creacion' => $now]
            );
            return self::resultado(true, 'Mensaje guardado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar mensaje.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar un mensaje del chat (bitácora) por id.
     * Opcional: verificar que el mensaje pertenezca al ticket dado.
     */
    public static function eliminarMensajeChat($idMensaje, $idTicket = null)
    {
        $id = (int)$idMensaje;
        if ($id < 1) {
            return self::resultado(false, 'ID de mensaje inválido.');
        }
        try {
            $db = new Database();
            if ($idTicket !== null && (int)$idTicket > 0) {
                $db->CRUD('DELETE FROM chat WHERE id = :id AND id_ticket = :id_ticket', ['id' => $id, 'id_ticket' => (int)$idTicket]);
            } else {
                $db->CRUD('DELETE FROM chat WHERE id = :id', ['id' => $id]);
            }
            return self::resultado(true, 'Mensaje eliminado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar mensaje.', null, $e->getMessage());
        }
    }

    /**
     * Mensajes de dictamen por ticket (igual estructura que chat/bitácora).
     */
    public static function getDictamenPorTicket($idTicket)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', []);
        }
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT d.id, d.id_ticket, d.id_persona, d.descripcion AS mensaje, d.fecha_creacion, " .
                "CONCAT(TRIM(IFNULL(p.nombres,'')), ' ', TRIM(IFNULL(p.apellidop,''))) AS persona_nombre " .
                "FROM dictamen d INNER JOIN persona p ON d.id_persona = p.id " .
                "WHERE d.id_ticket = :id_ticket ORDER BY d.fecha_creacion ASC",
                ['id_ticket' => $id]
            );
            return self::resultado(true, 'OK', is_array($rows) ? $rows : []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener dictamen.', null, $e->getMessage());
        }
    }

    /**
     * Agregar mensaje de dictamen al ticket (usa descripcion; tipo=otro, estado=borrador).
     */
    public static function agregarDictamen($idTicket, $idPersona, $mensaje)
    {
        $tid = (int)$idTicket;
        $pid = (int)$idPersona;
        $msg = self::normalizarDescripcionDictamenConMaps((string)$mensaje);
        if ($tid < 1 || $pid < 1 || $msg === '') {
            return self::resultado(false, 'Datos inválidos.', null);
        }
        if (strlen($msg) > 12000) {
            return self::resultado(false, 'Mensaje demasiado largo.', null);
        }
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $db->CRUD(
                "INSERT INTO dictamen (id_ticket, id_persona, tipo, descripcion, estado, fecha_creacion, fecha_actualizacion) VALUES (:id_ticket, :id_persona, 'otro', :descripcion, 'borrador', :fecha_creacion, :fecha_actualizacion)",
                ['id_ticket' => $tid, 'id_persona' => $pid, 'descripcion' => $msg, 'fecha_creacion' => $now, 'fecha_actualizacion' => $now]
            );
            return self::resultado(true, 'Dictamen guardado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar dictamen.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar un mensaje de dictamen por id.
     */
    public static function eliminarMensajeDictamen($idMensaje, $idTicket = null)
    {
        $id = (int)$idMensaje;
        if ($id < 1) {
            return self::resultado(false, 'ID de mensaje inválido.');
        }
        try {
            $db = new Database();
            if ($idTicket !== null && (int)$idTicket > 0) {
                $db->CRUD('DELETE FROM dictamen WHERE id = :id AND id_ticket = :id_ticket', ['id' => $id, 'id_ticket' => (int)$idTicket]);
            } else {
                $db->CRUD('DELETE FROM dictamen WHERE id = :id', ['id' => $id]);
            }
            return self::resultado(true, 'Dictamen eliminado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar dictamen.', null, $e->getMessage());
        }
    }

    /**
     * Obtiene el dictamen actual (último por ticket) para prellenar formulario y estado del botón.
     * Incluye tipo, descripcion, estado, fecha_creacion, fecha_actualizacion.
     */
    public static function getDictamenActualPorTicket($idTicket)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT d.id, d.id_ticket, d.id_persona, d.tipo, d.descripcion, d.estado, d.fecha_creacion, d.fecha_actualizacion, d.fecha_visto_gestor " .
                "FROM dictamen d WHERE d.id_ticket = :id_ticket ORDER BY d.fecha_creacion DESC LIMIT 1",
                ['id_ticket' => $id]
            );
            return self::resultado(true, 'OK', $row ?: null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener dictamen.', null, $e->getMessage());
        }
    }

    /**
     * Tipos de dictamen permitidos (catálogo actual + valores legado en BD).
     */
    public static function tiposDictamenPermitidos(): array
    {
        return ['ilocalizable', 'localizable', 'dual_zonificacion', 'falta_intensidad_gestion', 'localizado', 'no_localizado', 'promesa_pago', 'otro'];
    }

    /** Dictamen tipo ILOCALIZABLE: no requiere comentarios ni evidencia. */
    public static function esTipoDictamenIlocalizable(string $tipo): bool
    {
        return strtolower(trim($tipo)) === 'ilocalizable';
    }

    /** Etiqueta legible para tipo de dictamen (modal gestor, PDFs, etc.). */
    public static function etiquetaTipoDictamen(string $tipo): string
    {
        $t = strtolower(trim($tipo));
        $map = [
            'ilocalizable' => 'ILOCALIZABLE',
            'localizable' => 'LOCALIZABLE',
            'dual_zonificacion' => 'DUAL || ZONIFICACIÓN',
            'falta_intensidad_gestion' => 'FALTA INTENSIDAD DE GESTION',
            'localizado' => 'Localizado',
            'no_localizado' => 'No localizado',
            'promesa_pago' => 'Promesa de pago',
            'otro' => 'Otro',
        ];
        return $map[$t] ?? ($tipo !== '' ? $tipo : '—');
    }

    /**
     * Guardar dictamen como borrador (insert o update). tipo obligatorio; descripción obligatoria salvo ILOCALIZABLE.
     */
    public static function guardarDictamenBorrador($idTicket, $idPersona, $tipo, $descripcion)
    {
        $tid = (int)$idTicket;
        $pid = (int)$idPersona;
        $tipo = trim((string)$tipo);
        $descripcion = self::normalizarDescripcionDictamenConMaps((string)$descripcion);
        if ($tid < 1 || $pid < 1 || $tipo === '') {
            return self::resultado(false, 'Faltan datos del dictamen (tipo).');
        }
        if (!in_array(strtolower((string)$tipo), self::tiposDictamenPermitidos(), true)) {
            return self::resultado(false, 'Tipo de dictamen no válido.');
        }
        if (!self::esTipoDictamenIlocalizable($tipo) && $descripcion === '') {
            return self::resultado(false, 'Faltan tipo o descripción.');
        }
        if (strlen($descripcion) > 12000) {
            return self::resultado(false, 'Descripción demasiado larga.');
        }
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $actual = $db->queryOne("SELECT id FROM dictamen WHERE id_ticket = :id_ticket AND estado = 'borrador' ORDER BY fecha_creacion DESC LIMIT 1", ['id_ticket' => $tid]);
            if ($actual && !empty($actual['id'])) {
                // Asegurar id_persona en borrador para que al enviar quede autor (estadísticas Por Sabueso)
                if ($pid > 0) {
                    $db->CRUD(
                        "UPDATE dictamen SET tipo = :tipo, descripcion = :descripcion, fecha_actualizacion = :fecha_actualizacion, id_persona = COALESCE(NULLIF(id_persona,0), :pid) WHERE id = :id",
                        ['tipo' => $tipo, 'descripcion' => $descripcion, 'fecha_actualizacion' => $now, 'pid' => $pid, 'id' => (int)$actual['id']]
                    );
                } else {
                    $db->CRUD(
                        "UPDATE dictamen SET tipo = :tipo, descripcion = :descripcion, fecha_actualizacion = :fecha_actualizacion WHERE id = :id",
                        ['tipo' => $tipo, 'descripcion' => $descripcion, 'fecha_actualizacion' => $now, 'id' => (int)$actual['id']]
                    );
                }
                return self::resultado(true, 'Borrador actualizado.', ['id_dictamen' => (int)$actual['id']]);
            }
            $db->CRUD(
                "INSERT INTO dictamen (id_ticket, id_persona, tipo, descripcion, estado, fecha_creacion, fecha_actualizacion) VALUES (:id_ticket, :id_persona, :tipo, :descripcion, 'borrador', :fecha_creacion, :fecha_actualizacion)",
                ['id_ticket' => $tid, 'id_persona' => $pid, 'tipo' => $tipo, 'descripcion' => $descripcion, 'fecha_creacion' => $now, 'fecha_actualizacion' => $now]
            );
            $lastId = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            return self::resultado(true, 'Borrador guardado.', ['id_dictamen' => (int)($lastId['id'] ?? 0)]);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            $logDir = __DIR__ . '/../storage/logs';
            if (is_dir($logDir)) {
                @file_put_contents($logDir . '/dictamen_error.log', '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n", FILE_APPEND | LOCK_EX);
            }
            $mensajeUsuario = 'Error al guardar borrador.';
            if (stripos($msg, 'Unknown column') !== false || stripos($msg, 'column') !== false && stripos($msg, 'exist') !== false) {
                $mensajeUsuario = 'Faltan columnas en la tabla dictamen. Contacte al administrador para aplicar los cambios de esquema.';
            }
            return self::resultado(false, $mensajeUsuario, null, $msg);
        }
    }

    /**
     * Marcar el dictamen del ticket como enviado al gestor (estado = enviado_al_gestor).
     * También guarda el snapshot de gestiones para dictamen_sistema.
     */
    /**
     * @param int $idPersonaRemitente Quien envía (sesión); si el borrador no tiene id_persona, se guarda aquí para estadísticas Por Sabueso.
     */
    public static function enviarDictamenGestor($idTicket, $idPersonaRemitente = 0)
    {
        $tid = (int)$idTicket;
        if ($tid < 1) {
            return self::resultado(false, 'ID de ticket inválido.');
        }
        $pidRemitente = (int)$idPersonaRemitente;
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $actual = $db->queryOne("SELECT id, tipo, descripcion, id_persona FROM dictamen WHERE id_ticket = :id_ticket ORDER BY fecha_creacion DESC LIMIT 1", ['id_ticket' => $tid]);
            if (!$actual || empty($actual['id'])) {
                return self::resultado(false, 'No hay dictamen para enviar. Guarde un borrador primero.');
            }
            $tipo = trim((string)($actual['tipo'] ?? ''));
            $descripcion = trim((string)($actual['descripcion'] ?? ''));
            if ($tipo === '') {
                return self::resultado(false, 'Debe seleccionar el tipo de dictamen antes de enviar al gestor.');
            }
            if (!self::esTipoDictamenIlocalizable($tipo) && $descripcion === '') {
                return self::resultado(false, 'Debe escribir una descripción o comentarios antes de enviar al gestor (salvo tipo ILOCALIZABLE).');
            }
            // Autor del envío: si el borrador ya tenía id_persona se respeta; si no, el remitente en sesión (evita por_sabueso vacío)
            $idPersonaRow = (int)($actual['id_persona'] ?? 0);
            if ($idPersonaRow < 1 && $pidRemitente > 0) {
                $db->CRUD(
                    "UPDATE dictamen SET estado = 'enviado_al_gestor', fecha_actualizacion = :fecha_actualizacion, id_persona = :id_persona WHERE id = :id",
                    ['fecha_actualizacion' => $now, 'id_persona' => $pidRemitente, 'id' => (int)$actual['id']]
                );
            } else {
                $db->CRUD(
                    "UPDATE dictamen SET estado = 'enviado_al_gestor', fecha_actualizacion = :fecha_actualizacion WHERE id = :id",
                    ['fecha_actualizacion' => $now, 'id' => (int)$actual['id']]
                );
                // Si seguía NULL (borrador antiguo), intentar última asignación antes del envío
                if ($idPersonaRow < 1) {
                    $rowAsig = $db->queryOne(
                        "SELECT id_persona_asignada AS pid FROM asignacion_ticket WHERE id_ticket = :tid AND fecha_asignacion <= :fh " .
                        "ORDER BY fecha_asignacion DESC LIMIT 1",
                        ['tid' => $tid, 'fh' => $now]
                    );
                    $pidAsig = (int)($rowAsig['pid'] ?? 0);
                    if ($pidAsig > 0) {
                        $db->CRUD("UPDATE dictamen SET id_persona = :pid WHERE id = :id AND (id_persona IS NULL OR id_persona = 0)", ['pid' => $pidAsig, 'id' => (int)$actual['id']]);
                    }
                }
            }

            // Guardar snapshot de gestiones en dictamen_sistema
            try {
                self::guardarSnapshotDictamenSistema($tid, (int)$actual['id'], $now, $db);
            } catch (\Exception $snapErr) {
                error_log('dictamen_sistema snapshot error: ' . $snapErr->getMessage());
            }

            return self::resultado(true, 'Dictamen enviado al gestor.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al enviar dictamen.', null, $e->getMessage());
        }
    }

    /**
     * Detalle del dictamen para el modal del gestor (tipo, descripción, fechas, evidencias del ticket).
     */
    public static function getDictamenDetallePorTicket($idTicket)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', null);
        }
        try {
            $db = new Database();
            try {
                $dictamen = $db->queryOne(
                    "SELECT d.id, d.id_ticket, d.tipo, d.descripcion, d.estado, d.fecha_creacion, d.fecha_actualizacion, d.fecha_visto_gestor, d.id_persona_visto_gestor, " .
                    "CONCAT(TRIM(IFNULL(pv.nombres,'')), ' ', TRIM(IFNULL(pv.apellidop,''))) AS visto_gestor_nombre " .
                    "FROM dictamen d " .
                    "LEFT JOIN persona pv ON d.id_persona_visto_gestor = pv.id " .
                    "WHERE d.id_ticket = :id_ticket AND d.estado = 'enviado_al_gestor' ORDER BY d.fecha_creacion DESC LIMIT 1",
                    ['id_ticket' => $id]
                );
            } catch (\Exception $e) {
                $dictamen = $db->queryOne(
                    "SELECT d.id, d.id_ticket, d.tipo, d.descripcion, d.estado, d.fecha_creacion, d.fecha_actualizacion, d.fecha_visto_gestor " .
                    "FROM dictamen d WHERE d.id_ticket = :id_ticket AND d.estado = 'enviado_al_gestor' ORDER BY d.fecha_creacion DESC LIMIT 1",
                    ['id_ticket' => $id]
                );
            }
            $evRes = self::getEvidenciasPorTicket($id, self::TIPO_ORIGEN_DICTAMEN_SABUESO);
            $evidencias = ($evRes['success'] ?? false) && isset($evRes['datos']) && is_array($evRes['datos'])
                ? $evRes['datos']
                : [];

            $domicilios = [];
            $descripcionBase = $dictamen ? ($dictamen['descripcion'] ?? '') : '';
            if ($dictamen && !empty($dictamen['descripcion'])) {
                $parsed = self::parsearDomiciliosDictamen((string)$dictamen['descripcion']);
                $descripcionBase = $parsed['base'] ?? $descripcionBase;
                $domicilios = is_array($parsed['domicilios'] ?? null) ? $parsed['domicilios'] : [];
            }
            if ($dictamen !== null) {
                $dictamen['descripcion_base'] = $descripcionBase;
            }

            return self::resultado(true, 'OK', [
                'dictamen' => $dictamen ?: null,
                'evidencias' => is_array($evidencias) ? $evidencias : [],
                'domicilios' => $domicilios,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener detalle.', null, $e->getMessage());
        }
    }

    /**
     * Marca fecha_visto_gestor = ahora CDMX e id_persona_visto_gestor cuando el gestor abre el modal del dictamen.
     * @param int $idTicket
     * @param int $idPersona ID de la persona (gestor) que abre el dictamen (sesión).
     */
    public static function marcarDictamenVisto($idTicket, $idPersona = 0)
    {
        $tid = (int)$idTicket;
        if ($tid < 1) {
            return self::resultado(false, 'ID de ticket inválido.');
        }
        $pid = (int)$idPersona;
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $ok = false;
            if ($pid > 0) {
                try {
                    $db->CRUD(
                        "UPDATE dictamen SET fecha_visto_gestor = :fecha, id_persona_visto_gestor = :id_persona WHERE id_ticket = :id_ticket AND estado = 'enviado_al_gestor'",
                        ['fecha' => $now, 'id_persona' => $pid, 'id_ticket' => $tid]
                    );
                    $ok = true;
                } catch (\Exception $e) {
                    // Columna id_persona_visto_gestor puede no existir aún; guardar al menos la fecha
                }
            }
            if (!$ok) {
                $db->CRUD(
                    "UPDATE dictamen SET fecha_visto_gestor = :fecha WHERE id_ticket = :id_ticket AND estado = 'enviado_al_gestor'",
                    ['fecha' => $now, 'id_ticket' => $tid]
                );
            }
            return self::resultado(true, 'OK');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Lista de evidencias por ticket.
     *
     * @param string|null $filtroTipoOrigen null = todas; {@see TIPO_ORIGEN_ADJUNTO_TICKET} o {@see TIPO_ORIGEN_DICTAMEN_SABUESO}
     */
    public static function getEvidenciasPorTicket($idTicket, $filtroTipoOrigen = null)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', []);
        }
        try {
            $db = new Database();
            $tieneTipo = self::ticketEvidenciaTieneColumnaTipoOrigen($db);
            $sel = 'SELECT id, id_ticket, id_persona, ruta_archivo, nombre_original, fecha_subida';
            if ($tieneTipo) {
                $sel .= ', tipo_origen';
            }
            $sql = $sel . ' FROM ticket_evidencia WHERE id_ticket = :id_ticket';
            $params = ['id_ticket' => $id];
            if ($filtroTipoOrigen !== null && $filtroTipoOrigen !== '') {
                if ($tieneTipo) {
                    $sql .= ' AND (
                        CASE
                            WHEN tipo_origen IS NOT NULL AND TRIM(tipo_origen) <> \'\' THEN TRIM(tipo_origen)
                            WHEN SUBSTRING_INDEX(ruta_archivo, \'/\', -1) LIKE \'ev_%\' THEN \'dictamen_sabueso\'
                            ELSE \'adjunto_ticket\'
                        END = :ftipo
                    )';
                    $params['ftipo'] = $filtroTipoOrigen;
                } else {
                    if ($filtroTipoOrigen === self::TIPO_ORIGEN_DICTAMEN_SABUESO) {
                        $sql .= " AND SUBSTRING_INDEX(ruta_archivo, '/', -1) LIKE 'ev_%'";
                    } elseif ($filtroTipoOrigen === self::TIPO_ORIGEN_ADJUNTO_TICKET) {
                        $sql .= " AND SUBSTRING_INDEX(ruta_archivo, '/', -1) NOT LIKE 'ev_%'";
                    }
                }
            }
            $sql .= ' ORDER BY fecha_subida ASC';
            $rows = $db->queryAll($sql, $params);
            return self::resultado(true, 'OK', is_array($rows) ? $rows : []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener evidencias.', null, $e->getMessage());
        }
    }

    private static function ticketEvidenciaTieneColumnaTipoOrigen(Database $db): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }
        try {
            $c = $db->queryOne(
                "SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_evidencia' AND COLUMN_NAME = 'tipo_origen' LIMIT 1"
            );
            $cache = !empty($c);
        } catch (\Exception $e) {
            $cache = false;
        }

        return $cache;
    }

    /**
     * Guardar registro de evidencia (ruta ya guardada en disco).
     *
     * @param string $tipoOrigen {@see TIPO_ORIGEN_ADJUNTO_TICKET} | {@see TIPO_ORIGEN_DICTAMEN_SABUESO}
     */
    public static function guardarEvidencia($idTicket, $idPersona, $rutaArchivo, $nombreOriginal, $tipoOrigen = self::TIPO_ORIGEN_ADJUNTO_TICKET)
    {
        $tid = (int)$idTicket;
        $pid = (int)$idPersona;
        $ruta = trim((string)$rutaArchivo);
        $nombre = trim((string)$nombreOriginal) ?: 'imagen';
        $origen = trim((string)$tipoOrigen);
        if ($origen !== self::TIPO_ORIGEN_DICTAMEN_SABUESO) {
            $origen = self::TIPO_ORIGEN_ADJUNTO_TICKET;
        }
        if ($tid < 1 || $pid < 1 || $ruta === '') {
            return self::resultado(false, 'Datos inválidos.', null);
        }
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            if (self::ticketEvidenciaTieneColumnaTipoOrigen($db)) {
                $db->CRUD(
                    'INSERT INTO ticket_evidencia (id_ticket, id_persona, ruta_archivo, nombre_original, tipo_origen, fecha_subida) VALUES (:id_ticket, :id_persona, :ruta_archivo, :nombre_original, :tipo_origen, :fecha_subida)',
                    [
                        'id_ticket' => $tid,
                        'id_persona' => $pid,
                        'ruta_archivo' => $ruta,
                        'nombre_original' => $nombre,
                        'tipo_origen' => $origen,
                        'fecha_subida' => $now,
                    ]
                );
            } else {
                $db->CRUD(
                    "INSERT INTO ticket_evidencia (id_ticket, id_persona, ruta_archivo, nombre_original, fecha_subida) VALUES (:id_ticket, :id_persona, :ruta_archivo, :nombre_original, :fecha_subida)",
                    ['id_ticket' => $tid, 'id_persona' => $pid, 'ruta_archivo' => $ruta, 'nombre_original' => $nombre, 'fecha_subida' => $now]
                );
            }
            $lastId = $db->queryOne('SELECT LAST_INSERT_ID() AS id');
            return self::resultado(true, 'Evidencia guardada.', ['id' => $lastId['id'] ?? null]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar evidencia.', null, $e->getMessage());
        }
    }

    /**
     * Actualiza url_direccion del ticket (ruta relativa bajo uploads, p. ej. sabueso_evidencias/...).
     * Si existe la columna solicitud_vacaciones_adjunto_nombre_original y se pasa $nombreOriginalAdjunto, también la rellena.
     */
    public static function actualizarUrlDireccionTicket(int $idTicket, string $rutaRelativa, ?string $nombreOriginalAdjunto = null)
    {
        $tid = (int) $idTicket;
        $ruta = trim($rutaRelativa);
        if ($tid < 1 || $ruta === '') {
            return self::resultado(false, 'Datos inválidos.', null);
        }
        try {
            $db = new Database();
            $nom = $nombreOriginalAdjunto !== null ? trim($nombreOriginalAdjunto) : '';
            if ($nom !== '' && self::ticketColumnaExiste($db, 'solicitud_vacaciones_adjunto_nombre_original')) {
                $nom = mb_substr($nom, 0, 300);
                $db->CRUD(
                    'UPDATE ticket SET url_direccion = :ruta, solicitud_vacaciones_adjunto_nombre_original = :nom WHERE id_ticket = :id',
                    ['ruta' => $ruta, 'nom' => $nom, 'id' => $tid]
                );
            } else {
                $db->CRUD('UPDATE ticket SET url_direccion = :ruta WHERE id_ticket = :id', ['ruta' => $ruta, 'id' => $tid]);
            }
            return self::resultado(true, 'Ruta actualizada.', null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar url_direccion.', null, $e->getMessage());
        }
    }

    /**
     * Obtener una evidencia por id (para eliminar y obtener ruta).
     */
    public static function getEvidenciaPorId($idEvidencia)
    {
        $id = (int)$idEvidencia;
        if ($id < 1) return null;
        try {
            $db = new Database();
            return $db->queryOne("SELECT id, id_ticket, ruta_archivo FROM ticket_evidencia WHERE id = :id", ['id' => $id]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Eliminar evidencia (registro en BD). El controlador debe borrar el archivo si existe.
     */
    public static function eliminarEvidencia($idEvidencia)
    {
        $id = (int)$idEvidencia;
        if ($id < 1) {
            return self::resultado(false, 'ID de evidencia inválido.', null);
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM ticket_evidencia WHERE id = :id", ['id' => $id]);
            return self::resultado(true, 'Evidencia eliminada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar evidencia.', null, $e->getMessage());
        }
    }

    /**
 * Obtiene nombre_cliente desde __SPARTA_SECRET_REDACTED__ para un listado de id_credito.
 * Exclusivo para reportes — no afecta el flujo normal de tickets.
 * @param array $idsCredito Array de id_credito
 * @return array ['id_credito' => 'nombre_cliente', ...]
 */
public static function getNombresClienteParaReporte(array $idsCredito): array
{
    if (empty($idsCredito)) {
        return [];
    }

    // Filtrar nulls y duplicados
    $ids = array_unique(array_filter(array_map('intval', $idsCredito)));
    if (empty($ids)) {
        return [];
    }

    try {
        $placeholders = implode(',', $ids); // Son enteros, seguro sin PDO
        $db = new Database();
        $rows = $db->queryAll(
            "SELECT id_credito, nombre_cliente
             FROM `__SPARTA_SECRET_REDACTED__`.tbl_segundometro_semana
             WHERE id_credito IN ($placeholders)"
        );
        $mapa = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $mapa[(int)$row['id_credito']] = $row['nombre_cliente'] ?? '—';
        }
        return $mapa;
    } catch (\Throwable $e) {
        error_log('getNombresClienteParaReporte error: ' . $e->getMessage());
        return [];
    }
}

    /**
     * Nombres de persona (gestores) en una sola consulta — reportes masivos.
     *
     * @param array $idsPersona Lista de id persona
     * @return array<int, string> id => nombre
     */
    private static function getNombresPersonaParaReporte(array $idsPersona): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsPersona), static function ($v) {
            return $v > 0;
        })));
        if ($ids === []) {
            return [];
        }
        try {
            $in = implode(',', $ids);
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT id, TRIM(CONCAT(TRIM(IFNULL(nombres,'')), ' ', TRIM(IFNULL(apellidop,'')))) AS nombre " .
                "FROM persona WHERE id IN ($in)"
            );
            $map = [];
            foreach (is_array($rows) ? $rows : [] as $row) {
                $id = (int)($row['id'] ?? 0);
                if ($id > 0) {
                    $map[$id] = trim((string)($row['nombre'] ?? ''));
                }
            }
            return $map;
        } catch (\Throwable $e) {
            error_log('getNombresPersonaParaReporte error: ' . $e->getMessage());
            return [];
        }
    }

    // =====================================================================
    //  DICTAMEN DEL SISTEMA — verificación automática de visita
    // =====================================================================

    /**
     * Cuenta gestiones del crédito con fecha (dispositivo o registro) <= corte (CDMX).
     * Sirve para snapshot retroactivo: aproxima el conteo que hubiera al enviar el dictamen.
     *
     * @param string $hastaCdmx Fecha/hora límite inclusive (mismo formato que usa el sistema).
     */
    private static function contarGestionesCreditoHasta(string $idCredito, string $hastaCdmx): int
    {
        $gestiones = Gestiones::getAllGestiones($idCredito, '');
        if (!is_array($gestiones) || $gestiones === []) {
            return 0;
        }
        $tz = new \DateTimeZone('America/Mexico_City');
        try {
            $limite = new \DateTime($hastaCdmx, $tz);
        } catch (\Exception $e) {
            return count($gestiones);
        }
        $tsLim = $limite->getTimestamp();
        $n = 0;
        foreach ($gestiones as $g) {
            $raw = trim((string)($g['fecha_dispositivo'] ?? ''));
            if ($raw === '') {
                $raw = trim((string)($g['fecha_hora'] ?? ''));
            }
            if ($raw === '') {
                $n++;
                continue;
            }
            $t = strtotime($raw);
            if ($t !== false && $t <= $tsLim) {
                $n++;
            }
        }

        return $n;
    }

    /**
     * Guarda snapshot de gestiones al momento de enviar el dictamen al gestor.
     *
     * @param bool $conteoRetroactivoAlMomentoEnvio Si true, no usa el total actual de la lista sino
     *                                            gestiones con fecha <= fecha de envío (reparación de filas faltantes).
     */
    private static function guardarSnapshotDictamenSistema(int $idTicket, int $idDictamen, string $fechaEnvio, Database $db, bool $conteoRetroactivoAlMomentoEnvio = false)
    {
        $ticketRow = $db->queryOne("SELECT id_credito FROM ticket WHERE id_ticket = :id LIMIT 1", ['id' => $idTicket]);
        $idCredito = $ticketRow ? (int)($ticketRow['id_credito'] ?? 0) : 0;
        if ($idCredito < 1) return;

        $idGestor = self::getCreadorIdPorTicket($idTicket);
        $nombreGestor = $idGestor > 0 ? self::getNombrePersona($idGestor) : '';

        $gestiones = Gestiones::getAllGestiones((string)$idCredito, '');
        if ($conteoRetroactivoAlMomentoEnvio) {
            $totalGestiones = self::contarGestionesCreditoHasta((string)$idCredito, $fechaEnvio);
        } else {
            $totalGestiones = is_array($gestiones) ? count($gestiones) : 0;
        }

        $existe = $db->queryOne(
            "SELECT id FROM dictamen_sistema WHERE id_dictamen = :idd LIMIT 1",
            ['idd' => $idDictamen]
        );
        if ($existe) {
            $db->CRUD(
                "UPDATE dictamen_sistema SET gestiones_al_enviar = :g, fecha_envio_dictamen = :f, resultado = 'pendiente' WHERE id = :id",
                ['g' => $totalGestiones, 'f' => $fechaEnvio, 'id' => (int)$existe['id']]
            );
        } else {
            $db->CRUD(
                "INSERT INTO dictamen_sistema (id_ticket, id_dictamen, id_credito, id_gestor, nombre_gestor, gestiones_al_enviar, resultado, fecha_envio_dictamen, fecha_creacion) " .
                "VALUES (:tid, :did, :cred, :gest, :nom, :g, 'pendiente', :fenv, :fc)",
                [
                    'tid'  => $idTicket,
                    'did'  => $idDictamen,
                    'cred' => $idCredito,
                    'gest' => $idGestor > 0 ? $idGestor : null,
                    'nom'  => $nombreGestor !== '' ? $nombreGestor : null,
                    'g'    => $totalGestiones,
                    'fenv' => $fechaEnvio,
                    'fc'   => $fechaEnvio,
                ]
            );
        }
    }

    /**
     * Si falta fila en dictamen_sistema pero ya hay dictamen enviado al gestor (p. ej. envío
     * previo a activar el snapshot o fallo silencioso en enviarDictamenGestor), crea el snapshot.
     */
    private static function intentarCompletarSnapshotDictamenSistema(int $idTicket, Database $db, string $fechaFallback): void
    {
        $dictamenEnviado = $db->queryOne(
            "SELECT id, fecha_actualizacion, fecha_creacion FROM dictamen WHERE id_ticket = :tid AND estado = 'enviado_al_gestor' ORDER BY id DESC LIMIT 1",
            ['tid' => $idTicket]
        );
        if (!$dictamenEnviado || empty($dictamenEnviado['id'])) {
            return;
        }
        $fh = trim((string)($dictamenEnviado['fecha_actualizacion'] ?? ''));
        if ($fh === '') {
            $fh = trim((string)($dictamenEnviado['fecha_creacion'] ?? ''));
        }
        if ($fh === '') {
            $fh = $fechaFallback;
        }
        self::guardarSnapshotDictamenSistema($idTicket, (int)$dictamenEnviado['id'], $fh, $db, true);
    }

    /**
     * Genera el dictamen del sistema: compara gestiones antes/después y calcula distancias.
     * Se invoca desde el botón en Panel Admin una vez que pasan las 12 horas.
     *
     * @param int  $idTicket            ID del ticket.
     * @param bool $forzarRegeneracion  Si true, omite la validación de ventana de 12 h (p. ej. para recalcular tras reparar direcciones).
     */
    public static function generarDictamenSistema(int $idTicket, bool $forzarRegeneracion = false): array
    {
        if ($idTicket < 1) {
            return self::resultado(false, 'ID de ticket inválido.');
        }
        try {
            $db = new Database();
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = self::ahoraCdmx();

            // Solo tickets creados a partir del 10-mar-2026 (función no aplica a tickets viejos)
            $ticketRow = $db->queryOne(
                "SELECT fecha_creacion FROM ticket WHERE id_ticket = :tid AND (activo = 1 OR activo IS NULL) LIMIT 1",
                ['tid' => $idTicket]
            );
            if ($ticketRow && !empty($ticketRow['fecha_creacion'])) {
                $fc = new \DateTime($ticketRow['fecha_creacion'], $tz);
                $minimo = new \DateTime('2026-03-10 00:00:00', $tz);
                if ($fc < $minimo) {
                    return self::resultado(false, 'El dictamen del sistema solo aplica a tickets creados a partir del 10 de marzo de 2026.');
                }
            }

            $ds = $db->queryOne(
                "SELECT * FROM dictamen_sistema WHERE id_ticket = :tid ORDER BY id DESC LIMIT 1",
                ['tid' => $idTicket]
            );
            if (!$ds) {
                self::intentarCompletarSnapshotDictamenSistema($idTicket, $db, $now);
                $ds = $db->queryOne(
                    "SELECT * FROM dictamen_sistema WHERE id_ticket = :tid ORDER BY id DESC LIMIT 1",
                    ['tid' => $idTicket]
                );
            }
            if (!$ds) {
                return self::resultado(
                    false,
                    'No hay registro de dictamen del sistema para este ticket. Debe existir un dictamen ya enviado al gestor y el ticket debe tener crédito asociado. Si el envío fue antes de activar esta función, vuelva a intentar generar; si el error continúa, revise que el ticket tenga id_credito en base de datos.'
                );
            }

            $idCredito = (int)($ds['id_credito'] ?? 0);
            if ($idCredito < 1) {
                return self::resultado(false, 'El ticket no tiene crédito asociado.');
            }

            $detallePrev = !empty($ds['detalle']) ? json_decode($ds['detalle'], true) : [];
            if (!is_array($detallePrev)) {
                $detallePrev = [];
            }
            $prorrogaPrev = (isset($detallePrev['prorroga']) && is_array($detallePrev['prorroga'])) ? $detallePrev['prorroga'] : [];
            $intensidadPrev = (isset($detallePrev['intensidad']) && is_array($detallePrev['intensidad'])) ? $detallePrev['intensidad'] : [];

            $esRevisionProrroga = !empty($prorrogaPrev['otorgada']) && empty($prorrogaPrev['evaluada']);
            $esRevisionIntensidad = !empty($intensidadPrev['otorgada']) && empty($intensidadPrev['evaluada']);
            if ($esRevisionProrroga && $esRevisionIntensidad) {
                $esRevisionIntensidad = false;
            }
            $esRevisionExtension = $esRevisionProrroga || $esRevisionIntensidad;
            $extensionPrev = $esRevisionProrroga ? $prorrogaPrev : ($esRevisionIntensidad ? $intensidadPrev : []);

            $fechaInicioVentana = (string)($ds['fecha_envio_dictamen'] ?? '');
            $totalAntes = (int)($ds['gestiones_al_enviar'] ?? 0);
            if ($esRevisionExtension) {
                if (!empty($extensionPrev['fecha_otorgada'])) {
                    $fechaInicioVentana = (string)$extensionPrev['fecha_otorgada'];
                }
                if (isset($extensionPrev['gestiones_al_otorgar'])) {
                    $totalAntes = (int)$extensionPrev['gestiones_al_otorgar'];
                }
            }

            $inicioWinDt = new \DateTime($fechaInicioVentana !== '' ? $fechaInicioVentana : $now, $tz);
            $finWinDt = clone $inicioWinDt;
            $finWinDt->modify('+12 hours');
            $fechaInicioWin = $inicioWinDt->format('Y-m-d H:i:s');
            $fechaFinWin = $finWinDt->format('Y-m-d H:i:s');
            $nowTs = (new \DateTime($now, $tz))->getTimestamp();
            $ventanaVencida = $nowTs >= $finWinDt->getTimestamp();
            if (!$ventanaVencida) {
                $rest = max(0, $finWinDt->getTimestamp() - $nowTs);
                $h = floor($rest / 3600);
                $m = floor(($rest % 3600) / 60);
                $tipo = $esRevisionExtension
                    ? ($esRevisionProrroga ? 'la prórroga' : 'la intensidad')
                    : 'la ventana inicial';
                return self::resultado(false, 'Aún no vence ' . $tipo . ' de 12 horas. Restante aproximado: ' . $h . 'h ' . $m . 'm.');
            }

            $idDictamenSab = (int)($ds['id_dictamen'] ?? 0);
            $dictamenRowSab = $idDictamenSab > 0
                ? $db->queryOne(
                    "SELECT descripcion, tipo FROM dictamen WHERE id = :id LIMIT 1",
                    ['id' => $idDictamenSab]
                )
                : null;
            $tipoDictamenSab = strtolower(trim((string)($dictamenRowSab['tipo'] ?? '')));
            if (self::esTipoDictamenIlocalizable($tipoDictamenSab)) {
                $gestionesAhoraIl = Gestiones::getAllGestiones((string)$idCredito, '');
                $totalAhoraIl = is_array($gestionesAhoraIl) ? count($gestionesAhoraIl) : 0;
                $nuevasIl = $totalAhoraIl > $totalAntes ? ($totalAhoraIl - $totalAntes) : 0;
                $etiqTipo = self::etiquetaTipoDictamen($tipoDictamenSab);
                $cmp = self::cumplimientoMetadatos('dictamen_ilocalizable');
                $detalleIl = [
                    'gestiones_antes' => $totalAntes,
                    'gestiones_ahora' => $totalAhoraIl,
                    'nuevas_gestiones' => $nuevasIl,
                    'mensaje' => 'El dictamen del Sabueso es ILOCALIZABLE: no aplica evaluación automática por visita en campo ni por pago en ventana; no se penaliza al gestor por esos criterios.',
                    'evaluacion_visitas_pago_no_aplica' => true,
                    'dictamen_sabueso_tipo' => $tipoDictamenSab,
                    'dictamen_sabueso_etiqueta' => $etiqTipo,
                    'ventana_revision' => [
                        'inicio' => $fechaInicioWin,
                        'fin' => $fechaFinWin,
                        'tipo' => $esRevisionExtension
                            ? ($esRevisionProrroga ? 'prorroga_12h' : 'intensidad_12h')
                            : 'inicial_12h',
                    ],
                    '__SPARTA_SECRET_REDACTED___consultado' => true,
                    'pago_en_ventana' => false,
                    'pago_evaluacion_no_aplica' => true,
                ];
                if ($esRevisionExtension && $extensionPrev !== []) {
                    $extensionPrevEval = $extensionPrev;
                    $extensionPrevEval['evaluada'] = true;
                    $extensionPrevEval['fecha_revision'] = $now;
                    $extensionPrevEval['resultado_final'] = 'dictamen_ilocalizable';
                    if ($esRevisionProrroga) {
                        $prorrogaPrev = $extensionPrevEval;
                        $detalleIl['prorroga'] = $prorrogaPrev;
                    } else {
                        $intensidadPrev = $extensionPrevEval;
                        $detalleIl['intensidad'] = $intensidadPrev;
                    }
                }
                $detalleIl = array_merge($detalleIl, $cmp);
                $detalleJson = json_encode($detalleIl, JSON_UNESCAPED_UNICODE);
                $db->CRUD(
                    "UPDATE dictamen_sistema SET gestiones_al_revisar = :ga, resultado = :res, detalle = :d, fecha_revision = :fr WHERE id = :id",
                    ['ga' => $totalAhoraIl, 'res' => 'dictamen_ilocalizable', 'd' => $detalleJson, 'fr' => $now, 'id' => (int)$ds['id']]
                );

                return self::resultado(true, 'Dictamen del sistema generado (ILOCALIZABLE: sin evaluación GPS/pago).', [
                    'resultado' => 'dictamen_ilocalizable',
                    'detalle' => json_decode($detalleJson, true),
                ]);
            }

            $resPagos = self::getPagosEstadoCuentaEnVentana($idCredito, $fechaInicioWin, $fechaFinWin);
            $pagosEnVentana = is_array($resPagos['pagos'] ?? null) ? $resPagos['pagos'] : [];
            $hayPagoEnVentana = !empty($pagosEnVentana);
            $estadoCuentaConsultado = !empty($resPagos['__SPARTA_SECRET_REDACTED___consultado']);

            $gestionesAhora = Gestiones::getAllGestiones((string)$idCredito, '');
            $totalAhora = is_array($gestionesAhora) ? count($gestionesAhora) : 0;
            $nuevas = $totalAhora > $totalAntes ? array_slice($gestionesAhora, 0, $totalAhora - $totalAntes) : [];

            // Obtener direcciones y coordenadas del dictamen (direcciones proporcionadas)
            $dictamenDescripcion = (string)($dictamenRowSab['descripcion'] ?? '');
            $parsedDomicilios = self::parsearDomiciliosDictamen($dictamenDescripcion);
            $domiciliosDictamen = is_array($parsedDomicilios['domicilios'] ?? null) ? $parsedDomicilios['domicilios'] : [];
            $coordsDictamen = self::extraerCoordenadasDictamen($dictamenDescripcion);

            $analisis = [];
            $visitoCampo = false;
            $visitoTelefonico = false;
            $sinCoordenadas = true;
            $coberturaDirecciones = [];
            $fuenteCobertura = !empty($domiciliosDictamen) ? $domiciliosDictamen : $coordsDictamen;
            foreach ($fuenteCobertura as $ix => $cd) {
                $coberturaDirecciones[$ix] = [
                    'direccion' => $cd['desc'] ?? ('Dirección ' . ($ix + 1)),
                    'visitada' => false,
                    'min_distancia_metros' => null,
                ];
            }

            foreach ($nuevas as $idx => $g) {
                $latG = self::toFloat($g['latitud'] ?? null);
                $lngG = self::toFloat($g['longitud'] ?? null);
                $contacto = strtolower(trim((string)($g['contacto'] ?? '')));
                $esCampo = ($contacto === 'campo' || !empty(trim((string)($g['medio_contactacion_campo'] ?? ''))));
                $esTelefonico = ($contacto === 'telefono' || $contacto === 'telefonico' ||
                    !empty(trim((string)($g['medio_contactacion_ccc'] ?? ''))) &&
                    trim((string)($g['medio_contactacion_campo'] ?? '')) === '' &&
                    trim((string)($g['medio_contactacion_campo'] ?? '')) !== 'domicilio del cliente');

                if (($esCampo && empty(trim((string)($g['medio_contactacion_ccc'] ?? ''))))
                    || (!empty(trim((string)($g['medio_contactacion_campo'] ?? '')))
                        && trim((string)($g['medio_contactacion_campo'] ?? '')) !== '0')) {
                    $esTelefonico = false;
                    $esCampo = true;
                }

                $tipoGestion = $esCampo ? 'campo' : ($esTelefonico ? 'telefonico' : 'otro');

                $gestionAnalisis = [
                    'indice' => $idx + 1,
                    'fecha' => $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? '',
                    'tipo' => $tipoGestion,
                    'lat' => $latG,
                    'lng' => $lngG,
                    'usuario' => $g['usuario_asignado'] ?? $g['usuario'] ?? '',
                    'distancias' => [],
                ];

                $tieneCoords = ($latG != 0.0 || $lngG != 0.0) && $latG !== null && $lngG !== null;

                if ($tieneCoords && !empty($coordsDictamen)) {
                    $sinCoordenadas = false;
                    foreach ($coordsDictamen as $ix => $cd) {
                        $dist = self::haversine($latG, $lngG, $cd['lat'], $cd['lng']);
                        $distRed = (int)round($dist);
                        $gestionAnalisis['distancias'][] = [
                            'direccion' => $cd['desc'],
                            'lat_dictamen' => $cd['lat'],
                            'lng_dictamen' => $cd['lng'],
                            'distancia_metros' => $distRed,
                        ];
                        if ($dist < 100) {
                            if ($esCampo) {
                                $visitoCampo = true;
                                if (isset($coberturaDirecciones[$ix])) {
                                    $coberturaDirecciones[$ix]['visitada'] = true;
                                }
                            }
                            if ($esTelefonico) $visitoTelefonico = true;
                        }
                        if (isset($coberturaDirecciones[$ix])) {
                            $minPrev = $coberturaDirecciones[$ix]['min_distancia_metros'];
                            if ($minPrev === null || $distRed < $minPrev) {
                                $coberturaDirecciones[$ix]['min_distancia_metros'] = $distRed;
                            }
                        }
                    }
                } elseif (!$tieneCoords) {
                    $gestionAnalisis['nota'] = 'Sin coordenadas GPS en esta gestión.';
                }

                $analisis[] = $gestionAnalisis;
            }

            $direccionesTotal = count($coberturaDirecciones);
            $direccionesVisitadas = 0;
            foreach ($coberturaDirecciones as $cd) {
                if (!empty($cd['visitada'])) {
                    $direccionesVisitadas++;
                }
            }
            $visitoTodasDirecciones = $direccionesTotal > 0 && $direccionesVisitadas === $direccionesTotal;
            $visitaParcialDirecciones = $direccionesTotal > 0 && $direccionesVisitadas > 0 && !$visitoTodasDirecciones;

            // Determinar resultado base por GPS/gestión
            $resultadoBase = 'no_visito';
            $mensajeFinal = '';
            if (count($nuevas) <= 0) {
                $resultadoBase = 'no_visito';
                $mensajeFinal = 'No se registraron nuevas gestiones después de la ventana evaluada.';
            } elseif ($visitoCampo && $visitoTodasDirecciones) {
                $resultadoBase = 'visito_todas_direcciones';
                $mensajeFinal = 'El gestor realizó visita de campo y cubrió todas las direcciones del dictamen.';
            } elseif ($visitoCampo && $visitaParcialDirecciones) {
                $resultadoBase = 'visita_parcial';
                $mensajeFinal = 'El gestor realizó visita de campo, pero solo cubrió parcialmente las direcciones del dictamen.';
            } elseif ($visitoCampo) {
                $resultadoBase = 'visito_campo';
                $mensajeFinal = 'El gestor realizó visita de campo dentro del rango permitido.';
            } elseif ($visitoTelefonico) {
                $resultadoBase = 'visito_telefonico';
                $mensajeFinal = 'El gestor registró gestión telefónica, pero no se detectó visita de campo.';
            } elseif ($sinCoordenadas && empty($coordsDictamen)) {
                $resultadoBase = 'sin_coordenadas';
                $mensajeFinal = 'No fue posible comparar: no se encontraron coordenadas en las direcciones del dictamen.';
            } elseif ($sinCoordenadas) {
                $resultadoBase = 'sin_coordenadas';
                $mensajeFinal = 'Las nuevas gestiones no tienen coordenadas GPS para comparar.';
            } else {
                $mensajeFinal = 'Se registraron nuevas gestiones pero ninguna estuvo a menos de 100 metros de las direcciones del dictamen.';
                $resultadoBase = 'distancia_lejana';
            }

            // Regla negocio:
            // - Si hay pago dentro de las 12h => cumplido aunque no cubra todas las direcciones.
            // - Si no hay pago => cumple si cubrió todas las direcciones.
            $resultadoFinal = $resultadoBase;
            if ($hayPagoEnVentana) {
                $resultadoFinal = 'cumplido_pago';
            } elseif ($visitoTodasDirecciones) {
                $resultadoFinal = 'cumplido_sin_pago_todas_direcciones';
            }

            if ($esRevisionExtension) {
                $extensionPrev['evaluada'] = true;
                $extensionPrev['fecha_revision'] = $now;
                $extensionPrev['resultado_base'] = $resultadoBase;
                $extensionPrev['resultado_final'] = $resultadoFinal;
                $resultadoFinal = ($resultadoFinal === 'cumplido_pago' || $resultadoFinal === 'cumplido_sin_pago_todas_direcciones')
                    ? 'cumplio_prorroga'
                    : 'no_cumplio_prorroga';
                if ($esRevisionProrroga) {
                    $prorrogaPrev = $extensionPrev;
                } else {
                    $intensidadPrev = $extensionPrev;
                }
            }

            $cmp = self::cumplimientoMetadatos($resultadoFinal);
            $detalleBase = [
                'gestiones_antes'  => $totalAntes,
                'gestiones_ahora'  => $totalAhora,
                'nuevas_gestiones' => count($nuevas),
                'coords_dictamen'  => $coordsDictamen,
                'analisis'         => $analisis,
                'mensaje'          => $mensajeFinal,
                'resultado_base'   => $resultadoBase,
                'ventana_revision' => [
                    'inicio' => $fechaInicioWin,
                    'fin' => $fechaFinWin,
                    'tipo' => $esRevisionExtension
                        ? ($esRevisionProrroga ? 'prorroga_12h' : 'intensidad_12h')
                        : 'inicial_12h',
                ],
                '__SPARTA_SECRET_REDACTED___consultado' => $estadoCuentaConsultado,
                'pago_en_ventana' => $hayPagoEnVentana,
                'pagos_en_ventana' => $pagosEnVentana,
                'domicilios_dictamen_total' => count($domiciliosDictamen),
                'direcciones_dictamen_total' => $direccionesTotal,
                'direcciones_visitadas' => $direccionesVisitadas,
                'visito_todas_direcciones' => $visitoTodasDirecciones,
                'visita_parcial_direcciones' => $visitaParcialDirecciones,
                'cobertura_direcciones' => array_values($coberturaDirecciones),
            ];
            if (!empty($prorrogaPrev)) {
                $detalleBase['prorroga'] = $prorrogaPrev;
            }
            if (!empty($intensidadPrev)) {
                $detalleBase['intensidad'] = $intensidadPrev;
            }
            $detalle = json_encode(array_merge($detalleBase, $cmp), JSON_UNESCAPED_UNICODE);

            $db->CRUD(
                "UPDATE dictamen_sistema SET gestiones_al_revisar = :ga, resultado = :res, detalle = :d, fecha_revision = :fr WHERE id = :id",
                ['ga' => $totalAhora, 'res' => $resultadoFinal, 'd' => $detalle, 'fr' => $now, 'id' => (int)$ds['id']]
            );

            return self::resultado(true, 'Dictamen del sistema generado.', [
                'resultado' => $resultadoFinal,
                'detalle' => json_decode($detalle, true),
            ]);
        } catch (\Exception $e) {
            error_log('generarDictamenSistema(id_ticket=' . $idTicket . ') error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return self::resultado(false, 'Error al generar dictamen del sistema.', null, $e->getMessage());
        }
    }

    /**
     * Prórroga única de 12 horas para tickets no cumplidos y sin pago dentro de las 12h.
     */
    public static function otorgarProrrogaDictamenSistema(int $idTicket, int $idPersonaOtorga = 0, string $nombreOtorga = ''): array
    {
        if ($idTicket < 1) {
            return self::resultado(false, 'ID de ticket inválido.');
        }
        try {
            $db = new Database();
            $nowDt = self::cdmxNowImmutable();
            $now = $nowDt->format('Y-m-d H:i:s');
            $limite = (clone $nowDt)->modify('+12 hours')->format('Y-m-d H:i:s');

            $ds = $db->queryOne(
                "SELECT * FROM dictamen_sistema WHERE id_ticket = :tid ORDER BY id DESC LIMIT 1",
                ['tid' => $idTicket]
            );
            if (!$ds) {
                return self::resultado(false, 'No existe dictamen del sistema para este ticket.');
            }
            if ((string)($ds['resultado'] ?? '') === 'pendiente') {
                return self::resultado(false, 'Debe generar primero el dictamen del sistema inicial.');
            }

            $idDictamenPr = (int)($ds['id_dictamen'] ?? 0);
            if ($idDictamenPr > 0) {
                $rowTipo = $db->queryOne(
                    "SELECT tipo FROM dictamen WHERE id = :id LIMIT 1",
                    ['id' => $idDictamenPr]
                );
                $tipoPr = strtolower(trim((string)($rowTipo['tipo'] ?? '')));
                if (self::esTipoDictamenIlocalizable($tipoPr)) {
                    return self::resultado(false, 'No aplica prórroga: el dictamen del Sabueso es ILOCALIZABLE (no se evalúa por visita/pago automático).');
                }
            }

            $detalle = !empty($ds['detalle']) ? json_decode($ds['detalle'], true) : [];
            if (!is_array($detalle)) {
                $detalle = [];
            }
            $pr = (isset($detalle['prorroga']) && is_array($detalle['prorroga'])) ? $detalle['prorroga'] : [];
            if (!empty($pr['otorgada'])) {
                return self::resultado(false, 'Este ticket ya tiene prórroga otorgada (solo una vez).');
            }
            $int0 = (isset($detalle['intensidad']) && is_array($detalle['intensidad'])) ? $detalle['intensidad'] : [];
            if (!empty($int0['otorgada'])) {
                return self::resultado(false, 'Este ticket ya tiene Intensidad otorgada (solo una extensión de 12 h).');
            }
            if (!empty($detalle['pago_en_ventana']) || !empty($detalle['visito_todas_direcciones'])) {
                return self::resultado(false, 'No aplica prórroga: el ticket ya cumple.');
            }

            $idCredito = (int)($ds['id_credito'] ?? 0);
            $gestionesAhora = $idCredito > 0 ? Gestiones::getAllGestiones((string)$idCredito, '') : [];
            $gNow = is_array($gestionesAhora) ? count($gestionesAhora) : 0;

            $detalle['prorroga'] = [
                'otorgada' => true,
                'fecha_otorgada' => $now,
                'fecha_limite' => $limite,
                'id_persona_otorga' => $idPersonaOtorga > 0 ? $idPersonaOtorga : null,
                'nombre_otorga' => $nombreOtorga !== '' ? $nombreOtorga : null,
                'gestiones_al_otorgar' => $gNow,
                'evaluada' => false,
                'nota' => 'Prórroga única de 12 horas para completar direcciones pendientes o validar pago.',
            ];
            $detalle = array_merge($detalle, self::cumplimientoMetadatos('prorroga_activa'));
            $detalleJson = json_encode($detalle, JSON_UNESCAPED_UNICODE);

            $db->CRUD(
                "UPDATE dictamen_sistema SET resultado = 'prorroga_activa', detalle = :d, gestiones_al_enviar = :g, fecha_revision = NULL WHERE id = :id",
                ['d' => $detalleJson, 'g' => $gNow, 'id' => (int)$ds['id']]
            );

            return self::resultado(true, 'Prórroga otorgada por 12 horas.', [
                'resultado' => 'prorroga_activa',
                'detalle' => $detalle,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al otorgar prórroga.', null, $e->getMessage());
        }
    }

    /**
     * Indica si el detalle ya generado permite otorgar Intensidad (+12 h): hubo visita en campo y no hubo pago en la ventana.
     */
    private static function dictamenSistemaPermiteOtorgarIntensidad(array $detalle, string $resultado): bool
    {
        if (!empty($detalle['pago_en_ventana'])) {
            return false;
        }
        $rb = (string)($detalle['resultado_base'] ?? '');
        if (in_array($rb, ['visito_campo', 'visita_parcial', 'visito_todas_direcciones'], true)) {
            return true;
        }
        if ($resultado === 'cumplido_sin_pago_todas_direcciones') {
            return true;
        }
        if ((int)($detalle['direcciones_visitadas'] ?? 0) > 0) {
            return true;
        }
        return false;
    }

    /**
     * Extensión única de 12 horas cuando ya hubo visita en campo pero no pago en la ventana inicial (equivalente operativo a prórroga, nombre Intensidad).
     */
    public static function otorgarIntensidadDictamenSistema(int $idTicket, int $idPersonaOtorga = 0, string $nombreOtorga = ''): array
    {
        if ($idTicket < 1) {
            return self::resultado(false, 'ID de ticket inválido.');
        }
        try {
            $db = new Database();
            $nowDt = self::cdmxNowImmutable();
            $now = $nowDt->format('Y-m-d H:i:s');
            $limite = (clone $nowDt)->modify('+12 hours')->format('Y-m-d H:i:s');

            $ds = $db->queryOne(
                "SELECT * FROM dictamen_sistema WHERE id_ticket = :tid ORDER BY id DESC LIMIT 1",
                ['tid' => $idTicket]
            );
            if (!$ds) {
                return self::resultado(false, 'No existe dictamen del sistema para este ticket.');
            }
            if ((string)($ds['resultado'] ?? '') === 'pendiente') {
                return self::resultado(false, 'Debe generar primero el dictamen del sistema inicial.');
            }

            $idDictamenPr = (int)($ds['id_dictamen'] ?? 0);
            if ($idDictamenPr > 0) {
                $rowTipo = $db->queryOne(
                    "SELECT tipo FROM dictamen WHERE id = :id LIMIT 1",
                    ['id' => $idDictamenPr]
                );
                $tipoPr = strtolower(trim((string)($rowTipo['tipo'] ?? '')));
                if (self::esTipoDictamenIlocalizable($tipoPr)) {
                    return self::resultado(false, 'No aplica Intensidad: el dictamen del Sabueso es ILOCALIZABLE.');
                }
            }

            $detalle = !empty($ds['detalle']) ? json_decode($ds['detalle'], true) : [];
            if (!is_array($detalle)) {
                $detalle = [];
            }
            $pr = (isset($detalle['prorroga']) && is_array($detalle['prorroga'])) ? $detalle['prorroga'] : [];
            if (!empty($pr['otorgada'])) {
                return self::resultado(false, 'Este ticket ya tiene prórroga otorgada.');
            }
            $int = (isset($detalle['intensidad']) && is_array($detalle['intensidad'])) ? $detalle['intensidad'] : [];
            if (!empty($int['otorgada'])) {
                return self::resultado(false, 'Este ticket ya tiene Intensidad otorgada (solo una vez).');
            }
            $resDs = (string)($ds['resultado'] ?? '');
            if (!self::dictamenSistemaPermiteOtorgarIntensidad($detalle, $resDs)) {
                return self::resultado(false, 'No aplica Intensidad: se requiere visita en campo registrada en el dictamen del sistema y sin pago en la ventana de 12 h.');
            }

            $idCredito = (int)($ds['id_credito'] ?? 0);
            $gestionesAhora = $idCredito > 0 ? Gestiones::getAllGestiones((string)$idCredito, '') : [];
            $gNow = is_array($gestionesAhora) ? count($gestionesAhora) : 0;

            $detalle['intensidad'] = [
                'otorgada' => true,
                'fecha_otorgada' => $now,
                'fecha_limite' => $limite,
                'id_persona_otorga' => $idPersonaOtorga > 0 ? $idPersonaOtorga : null,
                'nombre_otorga' => $nombreOtorga !== '' ? $nombreOtorga : null,
                'gestiones_al_otorgar' => $gNow,
                'evaluada' => false,
                'nota' => 'Intensidad: extensión única de 12 horas tras visita en campo sin pago en la ventana inicial.',
            ];
            $detalle = array_merge($detalle, self::cumplimientoMetadatos('intensidad_activa'));
            $detalleJson = json_encode($detalle, JSON_UNESCAPED_UNICODE);

            $db->CRUD(
                "UPDATE dictamen_sistema SET resultado = 'intensidad_activa', detalle = :d, gestiones_al_enviar = :g, fecha_revision = NULL WHERE id = :id",
                ['d' => $detalleJson, 'g' => $gNow, 'id' => (int)$ds['id']]
            );

            return self::resultado(true, 'Intensidad otorgada: 12 horas adicionales.', [
                'resultado' => 'intensidad_activa',
                'detalle' => $detalle,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al otorgar Intensidad.', null, $e->getMessage());
        }
    }

    /**
     * Convierte fecha cruda de pago (API estado de cuenta) a timestamp.
     */
    private static function pagoRawATimestamp($raw, \DateTimeZone $tz): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_numeric($raw)) {
            $ts = (int)$raw;
            // Algunas APIs devuelven epoch en milisegundos.
            if ($ts > 9999999999) {
                $ts = (int)floor($ts / 1000);
            }
            return $ts > 0 ? $ts : null;
        }
        $rawStr = trim((string)$raw);
        try {
            return (new \DateTime($rawStr, $tz))->getTimestamp();
        } catch (\Throwable $e) {
            $ts = strtotime($rawStr);
            return $ts ? (int)$ts : null;
        }
    }

    /**
     * Determina si el error de estado de cuenta es de infraestructura (red/servicio),
     * para cortar llamadas repetidas dentro de la misma petición.
     */
    private static function esFallaInfraEstadoCuenta(array $resEstado): bool
    {
        $status = (int)($resEstado['status'] ?? 0);
        if ($status === 0 || $status >= 500) {
            return true;
        }
        $err = mb_strtolower(trim((string)($resEstado['error'] ?? '')), 'UTF-8');
        if ($err === '') {
            return false;
        }
        $agujas = [
            'error al conectar',
            'no hay conexión',
            'no hay conexion',
            'timed out',
            'timeout',
            'json inválido',
            'json invalido',
            'server',
            'servidor',
        ];
        foreach ($agujas as $needle) {
            if (strpos($err, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    /** Precarga estado de cuenta (reporte semanal): evita N llamadas serie a la API S2 por crédito. */
    private static ?array $reporteSemanalEcPreload = null;

    /**
     * Cache de estadísticas en disco (carpeta temporal). Sin tabla en BD.
     */
    private static function statsCacheDir(): string
    {
        $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta___SPARTA_SECRET_REDACTED___stats_cache';
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir;
    }

    private static function statsCacheRead(string $key, int $ttlSegundos): ?array
    {
        return self::statsCacheReadFile($key, $ttlSegundos);
    }

    private static function statsCacheReadFile(string $key, int $ttlSegundos): ?array
    {
        if ($ttlSegundos <= 0 || $key === '') {
            return null;
        }
        $file = self::statsCacheDir() . DIRECTORY_SEPARATOR . md5($key) . '.json';
        if (!is_file($file)) {
            return null;
        }
        $mtime = @filemtime($file);
        if ($mtime === false || (time() - $mtime) > $ttlSegundos) {
            return null;
        }
        $raw = @file_get_contents($file);
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * @param int $ttlSegundos Tiempo de vida del cache en segundos (por defecto 300).
     */
    private static function statsCacheWrite(string $key, array $data, int $ttlSegundos = 300): void
    {
        self::statsCacheWriteFile($key, $data);
    }

    private static function statsCacheWriteFile(string $key, array $data): void
    {
        if ($key === '' || empty($data)) {
            return;
        }
        $file = self::statsCacheDir() . DIRECTORY_SEPARATOR . md5($key) . '.json';
        @file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /** Invalida un archivo de caché de estadísticas (p. ej. tras reconsultar EC en reporte semanal). */
    private static function statsCacheDelete(string $key): void
    {
        if ($key === '') {
            return;
        }
        $file = self::statsCacheDir() . DIRECTORY_SEPARATOR . md5($key) . '.json';
        if (is_file($file)) {
            @unlink($file);
        }
    }

    /**
     * Directorio persistente del reporte semanal global (JSON por semana). No usa BD.
     */
    private static function reporteSemanalGlobalArchivoDir(): string
    {
        $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'reporte_semanal_global';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private static function reporteSemanalGlobalArchivoPath(string $semanaInicioYmd): string
    {
        $safe = preg_match('/^\d{4}-\d{2}-\d{2}$/', $semanaInicioYmd) ? $semanaInicioYmd : 'invalido';
        return self::reporteSemanalGlobalArchivoDir() . DIRECTORY_SEPARATOR . 'reporte_semanal_global_v3_' . $safe . '.json';
    }

    /**
     * Lee snapshot del reporte semanal desde disco (rápido; evita timeouts por muchas llamadas a API).
     */
    private static function reporteSemanalGlobalArchivoLeer(string $semanaInicioYmd): ?array
    {
        $path = self::reporteSemanalGlobalArchivoPath($semanaInicioYmd);
        if (!is_file($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['success'])) {
            return null;
        }
        unset($data['_archivo_meta']);
        return $data;
    }

    private static function reporteSemanalGlobalArchivoEscribir(string $semanaInicioYmd, array $payload): void
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $semanaInicioYmd)) {
            return;
        }
        $path = self::reporteSemanalGlobalArchivoPath($semanaInicioYmd);
        $payload['_archivo_meta'] = [
            'version' => 3,
            'generado_en' => self::ahoraCdmx(),
            'semana_inicio' => $semanaInicioYmd,
        ];
        @file_put_contents(
            $path,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    /**
     * Recalcula resumen KPIs a partir de filas (tras actualizar una fila desde reconsulta EC).
     */
    private static function reporteSemanalGlobalRecalcularResumen(array $filas): array
    {
        $resumen = [
            'total_tickets' => count($filas),
            'ilocalizable' => 0,
            'localizable' => 0,
            'pago_12h' => 0,
            'todas_direcciones' => 0,
            'prorroga' => 0,
            'pago_semana' => 0,
        ];
        foreach ($filas as $f) {
            if (!empty($f['ilocalizable'])) {
                $resumen['ilocalizable']++;
            }
            if (!empty($f['pago_semana_consultado']) && empty($f['ilocalizable'])) {
                $resumen['localizable']++;
            }
            if (($f['pago_12h'] ?? null) === true) {
                $resumen['pago_12h']++;
            }
            if (($f['fue_todas_direcciones'] ?? null) === true) {
                $resumen['todas_direcciones']++;
            }
            if (($f['prorroga_si'] ?? null) === true) {
                $resumen['prorroga']++;
            }
            if (!empty($f['pago_semana_si'])) {
                $resumen['pago_semana']++;
            }
        }
        return $resumen;
    }

    /**
     * Tras reconsultar un crédito, actualiza el JSON de la semana si existe (sin regenerar todo).
     */
    private static function reporteSemanalGlobalArchivoMergeReconsulta(
        string $semanaInicioYmd,
        int $idTicket,
        bool $pagoSemanaSi,
        int $pagoSemanaCount,
        bool $pagoSemanaConsultado,
        bool $ilocalizable
    ): void {
        if ($idTicket < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $semanaInicioYmd)) {
            return;
        }
        $path = self::reporteSemanalGlobalArchivoPath($semanaInicioYmd);
        if (!is_file($path)) {
            return;
        }
        $raw = @file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['filas']) || !is_array($data['filas'])) {
            return;
        }
        $updated = false;
        foreach ($data['filas'] as $i => $f) {
            if ((int)($f['id_ticket'] ?? 0) === $idTicket) {
                $data['filas'][$i]['pago_semana_si'] = $pagoSemanaSi;
                $data['filas'][$i]['pago_semana_count'] = $pagoSemanaCount;
                $data['filas'][$i]['pago_semana_consultado'] = $pagoSemanaConsultado;
                $data['filas'][$i]['ilocalizable_auto'] = $ilocalizable;
                if (empty($data['filas'][$i]['ilocalizable_override'])) {
                    $data['filas'][$i]['ilocalizable'] = $ilocalizable;
                }
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            return;
        }
        $data['resumen'] = self::reporteSemanalGlobalRecalcularResumen($data['filas']);
        unset($data['_archivo_meta']);
        self::reporteSemanalGlobalArchivoEscribir($semanaInicioYmd, $data);
    }

    /**
     * Ajuste manual de «ilocalizable» en el JSON del reporte semanal (persistente por semana y ticket).
     * modo: auto | si | no
     */
    public static function guardarIlocalizableReporteSemanal(string $semanaInicio, int $idTicket, string $modo): array
    {
        if ($idTicket < 1 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $semanaInicio)) {
            return ['success' => false, 'mensaje' => 'Parámetros inválidos'];
        }
        $modo = strtolower(trim($modo));
        if (in_array($modo, ['true', '1', 'sí', 'si'], true)) {
            $modo = 'si';
        } elseif (in_array($modo, ['false', '0', 'no'], true)) {
            $modo = 'no';
        }
        if (!in_array($modo, ['auto', 'si', 'no'], true)) {
            return ['success' => false, 'mensaje' => 'modo inválido (use auto, si o no)'];
        }
        $path = self::reporteSemanalGlobalArchivoPath($semanaInicio);
        if (!is_file($path)) {
            return ['success' => false, 'mensaje' => 'No hay reporte guardado para esa semana. Abra el reporte primero.'];
        }
        $raw = @file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return ['success' => false, 'mensaje' => 'No se pudo leer el reporte.'];
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['filas']) || !is_array($data['filas'])) {
            return ['success' => false, 'mensaje' => 'Reporte corrupto o vacío.'];
        }
        $updated = false;
        foreach ($data['filas'] as $i => $f) {
            if ((int)($f['id_ticket'] ?? 0) !== $idTicket) {
                continue;
            }
            $prevIl = !empty($f['ilocalizable']);
            $auto = array_key_exists('ilocalizable_auto', $f) ? !empty($f['ilocalizable_auto']) : $prevIl;
            if ($modo === 'auto') {
                $data['filas'][$i]['ilocalizable_override'] = false;
                $data['filas'][$i]['ilocalizable'] = $auto;
            } elseif ($modo === 'si') {
                if (!array_key_exists('ilocalizable_auto', $f)) {
                    $data['filas'][$i]['ilocalizable_auto'] = $auto;
                }
                $data['filas'][$i]['ilocalizable_override'] = true;
                $data['filas'][$i]['ilocalizable'] = true;
            } else {
                if (!array_key_exists('ilocalizable_auto', $f)) {
                    $data['filas'][$i]['ilocalizable_auto'] = $auto;
                }
                $data['filas'][$i]['ilocalizable_override'] = true;
                $data['filas'][$i]['ilocalizable'] = false;
            }
            $updated = true;
            break;
        }
        if (!$updated) {
            return ['success' => false, 'mensaje' => 'Ticket no encontrado en el reporte de esa semana.'];
        }
        $data['resumen'] = self::reporteSemanalGlobalRecalcularResumen($data['filas']);
        unset($data['_archivo_meta']);
        self::reporteSemanalGlobalArchivoEscribir($semanaInicio, $data);
        self::statsCacheDelete('reporte_semanal_global:v3:' . $semanaInicio);
        $row = null;
        foreach ($data['filas'] as $f) {
            if ((int)($f['id_ticket'] ?? 0) === $idTicket) {
                $row = $f;
                break;
            }
        }
        return [
            'success' => true,
            'mensaje' => 'OK',
            'id_ticket' => $idTicket,
            'semana_inicio' => $semanaInicio,
            'ilocalizable' => !empty($row['ilocalizable']),
            'ilocalizable_auto' => !empty($row['ilocalizable_auto'] ?? $row['ilocalizable'] ?? false),
            'ilocalizable_override' => !empty($row['ilocalizable_override']),
        ];
    }

    /**
     * Obtiene y normaliza pagos de estado de cuenta por crédito (cacheado por request).
     * Evita repetir llamadas a API externa por cada ventana evaluada.
     * @param array $opts ['timeout_segundos' => int, 'max_api_calls' => int, 'cache_ttl_segundos' => int]
     * @return array{consultado: bool, pagos: array}
     */
    private static function reporteSemanalPrecargarEstadoCuenta(array $idsCredito, array $opts): void
    {
        self::$reporteSemanalEcPreload = null;
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsCredito), static function ($v) {
            return $v > 0;
        })));
        if ($ids === []) {
            return;
        }
        $timeoutSeg = max(2, min(20, (int)($opts['timeout_segundos'] ?? 20)));
        $conc = max(1, min(24, (int)($opts['ec_parallel_concurrency'] ?? 12)));
        $cacheTtl = max(0, (int)($opts['cache_ttl_segundos'] ?? 0));
        $fechaCorte = self::fechaCdmx();

        try {
            $ec = new \Controllers\EstadoCuenta();
            $respuestas = $ec->api___SPARTA_SECRET_REDACTED___parallel($ids, $fechaCorte, $timeoutSeg, $conc);
        } catch (\Throwable $e) {
            error_log('reporteSemanalPrecargarEstadoCuenta: ' . $e->getMessage());
            $respuestas = [];
        }

        $preload = [];
        $tz = new \DateTimeZone('America/Mexico_City');
        $vacío = ['consultado' => false, 'pagos' => []];
        foreach ($ids as $cid) {
            $resEstado = is_array($respuestas) && array_key_exists($cid, $respuestas) ? $respuestas[$cid] : null;
            if (!is_array($resEstado)) {
                $preload[$cid] = $vacío;
                continue;
            }
            $pagosRaw = $resEstado['data']['datosPagos'] ?? [];
            if (empty($resEstado['ok']) || !is_array($pagosRaw)) {
                $preload[$cid] = $vacío;
                continue;
            }
            $out = [];
            foreach ($pagosRaw as $p) {
                $raw = $p['fechaDeposito'] ?? $p['fechaRegistro'] ?? $p['fechaValor'] ?? null;
                $ts = self::pagoRawATimestamp($raw, $tz);
                if ($ts === null) {
                    continue;
                }
                $out[] = [
                    'ts' => $ts,
                    'fecha' => date('Y-m-d H:i:s', $ts),
                    'monto' => $p['montoPago'] ?? null,
                    'referencia' => $p['referencia'] ?? ($p['descripcion'] ?? null),
                ];
            }
            $norm = ['consultado' => true, 'pagos' => $out];
            $preload[$cid] = $norm;
            if ($cacheTtl > 0) {
                $cacheKey = '__SPARTA_SECRET_REDACTED___pagos:' . $cid . ':' . $fechaCorte;
                self::statsCacheWrite($cacheKey, $norm, $cacheTtl);
            }
        }
        self::$reporteSemanalEcPreload = $preload;
    }

    private static function getPagosEstadoCuentaNormalizados(int $idCredito, array $opts = []): array
    {
        static $cachePagosCredito = [];
        static $totalApiCalls = 0;
        static $infraCaida = false;
        $vacío = ['consultado' => false, 'pagos' => []];
        if ($idCredito < 1) {
            return $vacío;
        }
        if (is_array(self::$reporteSemanalEcPreload) && array_key_exists($idCredito, self::$reporteSemanalEcPreload)) {
            return self::$reporteSemanalEcPreload[$idCredito];
        }
        if (array_key_exists($idCredito, $cachePagosCredito)) {
            return $cachePagosCredito[$idCredito];
        }
        $timeoutSeg = max(2, min(20, (int)($opts['timeout_segundos'] ?? 20)));
        $maxApiCalls = max(1, (int)($opts['max_api_calls'] ?? 1000000));
        $cacheTtl = max(0, (int)($opts['cache_ttl_segundos'] ?? 0));
        $fechaCorte = self::fechaCdmx();
        if ($cacheTtl > 0) {
            $cacheKey = '__SPARTA_SECRET_REDACTED___pagos:' . $idCredito . ':' . $fechaCorte;
            $cached = self::statsCacheRead($cacheKey, $cacheTtl);
            if (is_array($cached) && array_key_exists('consultado', $cached) && array_key_exists('pagos', $cached)) {
                $cachePagosCredito[$idCredito] = $cached;
                return $cached;
            }
        }
        if ($infraCaida || $totalApiCalls >= $maxApiCalls) {
            $cachePagosCredito[$idCredito] = $vacío;
            return $vacío;
        }
        try {
            $estadoCuentaCtrl = new \Controllers\EstadoCuenta();
            $resEstado = $estadoCuentaCtrl->api___SPARTA_SECRET_REDACTED__($idCredito, $fechaCorte, $timeoutSeg);
            $totalApiCalls++;
            $pagos = $resEstado['data']['datosPagos'] ?? [];
            if (empty($resEstado['ok']) || !is_array($pagos)) {
                if (self::esFallaInfraEstadoCuenta(is_array($resEstado) ? $resEstado : [])) {
                    $infraCaida = true;
                }
                $cachePagosCredito[$idCredito] = $vacío;
                return $vacío;
            }
            $tz = new \DateTimeZone('America/Mexico_City');
            $out = [];
            foreach ($pagos as $p) {
                $raw = $p['fechaDeposito'] ?? $p['fechaRegistro'] ?? $p['fechaValor'] ?? null;
                $ts = self::pagoRawATimestamp($raw, $tz);
                if ($ts === null) {
                    continue;
                }
                $out[] = [
                    'ts' => $ts,
                    'fecha' => date('Y-m-d H:i:s', $ts),
                    'monto' => $p['montoPago'] ?? null,
                    'referencia' => $p['referencia'] ?? ($p['descripcion'] ?? null),
                ];
            }
            $cachePagosCredito[$idCredito] = ['consultado' => true, 'pagos' => $out];
            if ($cacheTtl > 0) {
                self::statsCacheWrite($cacheKey, $cachePagosCredito[$idCredito], $cacheTtl);
            }
            return $cachePagosCredito[$idCredito];
        } catch (\Throwable $e) {
            $cachePagosCredito[$idCredito] = $vacío;
            return $vacío;
        }
    }

    /**
     * Consulta pagos en estado de cuenta dentro de una ventana.
     * @return array{__SPARTA_SECRET_REDACTED___consultado: bool, pagos: array}
     */
    private static function getPagosEstadoCuentaEnVentana(int $idCredito, string $inicio, string $fin, array $opts = []): array
    {
        $vacío = ['__SPARTA_SECRET_REDACTED___consultado' => false, 'pagos' => []];
        if ($idCredito < 1 || $inicio === '' || $fin === '') {
            return $vacío;
        }
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
            $iniTs = (new \DateTime($inicio, $tz))->getTimestamp();
            $finTs = (new \DateTime($fin, $tz))->getTimestamp();
            if ($finTs < $iniTs) {
                return $vacío;
            }
            $res = self::getPagosEstadoCuentaNormalizados($idCredito, $opts);
            $pagos = is_array($res['pagos'] ?? null) ? $res['pagos'] : [];
            $consultado = !empty($res['consultado']);
            if (!$consultado) {
                return ['__SPARTA_SECRET_REDACTED___consultado' => false, 'pagos' => []];
            }
            $out = [];
            foreach ($pagos as $p) {
                $ts = (int)($p['ts'] ?? 0);
                if ($ts <= 0) {
                    continue;
                }
                $isDentro = ($ts >= $iniTs && $ts <= $finTs);
                if (!$isDentro) {
                    $fechaRaw = date('Y-m-d', $ts);
                    $fechaIni = date('Y-m-d', $iniTs);
                    $fechaFin = date('Y-m-d', $finTs);
                    $isDentro = ($fechaRaw >= $fechaIni && $fechaRaw <= $fechaFin);
                }
                if ($isDentro) {
                    $out[] = [
                        'fecha' => $p['fecha'] ?? date('Y-m-d H:i:s', $ts),
                        'monto' => $p['monto'] ?? null,
                        'referencia' => $p['referencia'] ?? null,
                    ];
                }
            }
            return ['__SPARTA_SECRET_REDACTED___consultado' => true, 'pagos' => $out];
        } catch (\Throwable $e) {
            return $vacío;
        }
    }

    /**
     * Reglas de cumplimiento: % efectividad y medidas preventivas según resultado del dictamen sistema.
     * Cumplir = visita en campo cerca de direcciones del dictamen; si no hay pago en estado de cuenta
     * habría que exigir cobertura de todas las direcciones (la verificación de pago se puede integrar después).
     */
    public static function cumplimientoMetadatos(?string $resultado): array
    {
        $r = $resultado ?? 'pendiente';
        $map = [
            'dictamen_ilocalizable' => [
                'pct_efectividad' => null,
                'cumplimiento_etiqueta' => 'N/A — ILOCALIZABLE',
                'medidas_preventivas' => 'El Sabueso clasificó el caso como ILOCALIZABLE: no corresponde evaluar al gestor por visita en campo ni por pago en ventana. Las reglas automáticas de GPS y estado de cuenta no aplican.',
            ],
            'pendiente' => [
                'pct_efectividad' => null,
                'cumplimiento_etiqueta' => 'Pendiente',
                'medidas_preventivas' => 'Esperar 12 h y generar dictamen del sistema. Si hay pago en estado de cuenta alineado con la visita, considerar cumplido además del GPS.',
            ],
            'prorroga_activa' => [
                'pct_efectividad' => null,
                'cumplimiento_etiqueta' => 'Prórroga activa',
                'medidas_preventivas' => 'Prórroga otorgada (12 h). Al vencer, generar de nuevo el dictamen del sistema para obtener el resultado final de prórroga.',
            ],
            'intensidad_activa' => [
                'pct_efectividad' => null,
                'cumplimiento_etiqueta' => 'Intensidad activa',
                'medidas_preventivas' => 'Intensidad otorgada (12 h adicionales tras visita en campo sin pago). Al vencer, el sistema reevalúa al abrir el panel o la tabla.',
            ],
            'cumplido_pago' => [
                'pct_efectividad' => 100,
                'cumplimiento_etiqueta' => 'Cumplido por pago',
                'medidas_preventivas' => 'Hay pago en estado de cuenta dentro de las 12h; se considera cumplido aunque no haya cobertura total de direcciones.',
            ],
            'cumplido_sin_pago_todas_direcciones' => [
                'pct_efectividad' => 95,
                'cumplimiento_etiqueta' => 'Cumplido por cobertura total',
                'medidas_preventivas' => 'Sin pago dentro de las 12h, pero sí cubrió todas las direcciones del dictamen con visita de campo.',
            ],
            'visito_todas_direcciones' => [
                'pct_efectividad' => 95,
                'cumplimiento_etiqueta' => 'Visitó todas direcciones',
                'medidas_preventivas' => 'Validar pago en estado de cuenta. Si no pagó, se mantiene cumplimiento por cobertura total.',
            ],
            'visita_parcial' => [
                'pct_efectividad' => 60,
                'cumplimiento_etiqueta' => 'Visita parcial',
                'medidas_preventivas' => 'Solo cubrió parte de direcciones. Si no hay pago dentro de las 12h, evaluar prórroga única.',
            ],
            'cumplio_prorroga' => [
                'pct_efectividad' => 90,
                'cumplimiento_etiqueta' => 'Cumplió en prórroga',
                'medidas_preventivas' => 'Resultado final tras prórroga: cumplió por pago dentro de las 12h o por cobertura total.',
            ],
            'no_cumplio_prorroga' => [
                'pct_efectividad' => 20,
                'cumplimiento_etiqueta' => 'No cumplió prórroga',
                'medidas_preventivas' => 'Prórroga agotada sin cumplimiento. Escalar seguimiento y cerrar por política operativa.',
            ],
            'no_visito' => [
                'pct_efectividad' => 0,
                'cumplimiento_etiqueta' => 'No visito', // sin acento: texto único en tablas/modales
                'medidas_preventivas' => 'Capacitación a gestor; revisar asignación y seguimiento. Verificar en estado de cuenta si hubo pago pese a no registrar gestión nueva.',
            ],
            'visito_campo' => [
                'pct_efectividad' => 100,
                'cumplimiento_etiqueta' => 'Visita campo OK',
                'medidas_preventivas' => 'Confirmar en estado de cuenta si hubo pago en fecha coherente. Si pagó → cumplimiento alto. Si no pagó, validar que haya cubierto todas las direcciones del dictamen.',
            ],
            'visito_telefonico' => [
                'pct_efectividad' => 55,
                'cumplimiento_etiqueta' => 'Solo telefónico',
                'medidas_preventivas' => 'Refuerzo: priorizar visita física a direcciones del dictamen. Revisar si el crédito pagó; si no, plan de visita a todas las ubicaciones enviadas.',
            ],
            'sin_coordenadas' => [
                'pct_efectividad' => 30,
                'cumplimiento_etiqueta' => 'Sin GPS',
                'medidas_preventivas' => 'Exigir coordenadas en dictamen y en gestiones. Revisión manual de estado de cuenta y visitas declaradas.',
            ],
            'distancia_lejana' => [
                'pct_efectividad' => 25,
                'cumplimiento_etiqueta' => 'Lejos de dirección',
                'medidas_preventivas' => 'Gestor debe acercarse a menos de 100 m de cada punto del dictamen o justificar. Sin pago: verificar cobertura de todas las direcciones.',
            ],
        ];
        return $map[$r] ?? [
            'pct_efectividad' => 40,
            'cumplimiento_etiqueta' => $r,
            'medidas_preventivas' => 'Revisar detalle JSON del dictamen sistema y estado de cuenta.',
        ];
    }

    /**
     * Obtiene el dictamen_sistema existente para un ticket (para mostrar en UI).
     */
    public static function getDictamenSistema(int $idTicket): array
    {
        if ($idTicket < 1) {
            return self::resultado(false, 'ID de ticket inválido.');
        }
        try {
            $db = new Database();
            $ds = $db->queryOne(
                "SELECT * FROM dictamen_sistema WHERE id_ticket = :tid ORDER BY id DESC LIMIT 1",
                ['tid' => $idTicket]
            );
            if (!$ds) {
                return self::resultado(true, 'No hay dictamen del sistema.', ['dictamen_sistema' => null]);
            }
            $ds['detalle_parsed'] = !empty($ds['detalle']) ? json_decode($ds['detalle'], true) : null;
            if (is_array($ds['detalle_parsed'])) {
                $dirTot = (int)($ds['detalle_parsed']['direcciones_dictamen_total'] ?? 0);
                $cob = $ds['detalle_parsed']['cobertura_direcciones'] ?? [];
                $sinCobertura = !is_array($cob) || count($cob) === 0;
                if ($dirTot === 0 && $sinCobertura) {
                    $descripcionDictamen = '';
                    $idDictamen = (int)($ds['id_dictamen'] ?? 0);
                    if ($idDictamen > 0) {
                        $dictamenRow = $db->queryOne(
                            "SELECT descripcion FROM dictamen WHERE id = :id LIMIT 1",
                            ['id' => $idDictamen]
                        );
                        $descripcionDictamen = (string)($dictamenRow['descripcion'] ?? '');
                    }
                    if ($descripcionDictamen !== '') {
                        $parsed = self::parsearDomiciliosDictamen($descripcionDictamen);
                        $domicilios = is_array($parsed['domicilios'] ?? null) ? $parsed['domicilios'] : [];
                        if (!empty($domicilios)) {
                            $cobertura = [];
                            foreach ($domicilios as $i => $dom) {
                                $cobertura[] = [
                                    'direccion' => $dom['desc'] ?? ('Dirección ' . ($i + 1)),
                                    'visitada' => false,
                                    'min_distancia_metros' => null,
                                ];
                            }
                            $ds['detalle_parsed']['domicilios_dictamen_total'] = count($domicilios);
                            $ds['detalle_parsed']['direcciones_dictamen_total'] = count($domicilios);
                            $ds['detalle_parsed']['direcciones_visitadas'] = (int)($ds['detalle_parsed']['direcciones_visitadas'] ?? 0);
                            $ds['detalle_parsed']['cobertura_direcciones'] = $cobertura;
                        }
                    }
                }
            }
            // Prórroga o Intensidad vencida: generar automáticamente (generarDictamenSistema valida ventana)
            $resDsRow = (string)($ds['resultado'] ?? '');
            if (($resDsRow === 'prorroga_activa' || $resDsRow === 'intensidad_activa') && is_array($ds['detalle_parsed'])) {
                $pr = $ds['detalle_parsed']['prorroga'] ?? null;
                $in = $ds['detalle_parsed']['intensidad'] ?? null;
                $limite = '';
                if (is_array($pr) && !empty($pr['fecha_limite'])) {
                    $limite = (string)$pr['fecha_limite'];
                }
                if ($limite === '' && is_array($in) && !empty($in['fecha_limite'])) {
                    $limite = (string)$in['fecha_limite'];
                }
                if ($limite !== '') {
                    try {
                        $tz = new \DateTimeZone('America/Mexico_City');
                        $finPr = (new \DateTime($limite, $tz))->getTimestamp();
                        $nowTs = (new \DateTime('now', $tz))->getTimestamp();
                        if ($nowTs >= $finPr) {
                            $gen = self::generarDictamenSistema($idTicket);
                            if (!empty($gen['success'])) {
                                $dsNuevo = $db->queryOne(
                                    "SELECT * FROM dictamen_sistema WHERE id_ticket = :tid ORDER BY id DESC LIMIT 1",
                                    ['tid' => $idTicket]
                                );
                                if ($dsNuevo) {
                                    $dsNuevo['detalle_parsed'] = !empty($dsNuevo['detalle']) ? json_decode($dsNuevo['detalle'], true) : null;
                                    $ds = $dsNuevo;
                                }
                            }
                        }
                    } catch (\Throwable $e) {
                        // si falla parseo o generar, devolver ds actual
                    }
                }
            }
            $ds['historico_visita_gestion'] = null;
            $idCredHist = (int)($ds['id_credito'] ?? 0);
            $fEnvHist = trim((string)($ds['fecha_envio_dictamen'] ?? ''));
            if ($idCredHist > 0 && is_array($ds['detalle_parsed'])) {
                $dp = $ds['detalle_parsed'];
                $tieneVisita = ((int)($dp['direcciones_visitadas'] ?? 0) > 0)
                    || in_array((string)($dp['resultado_base'] ?? ''), ['visito_campo', 'visita_parcial', 'visito_todas_direcciones'], true)
                    || (string)($ds['resultado'] ?? '') === 'cumplido_sin_pago_todas_direcciones';
                if ($tieneVisita) {
                    $hist = Gestiones::obtenerUltimaGestionCampoTrasEnvio((string)$idCredHist, $fEnvHist !== '' ? $fEnvHist : null);
                    if ($hist !== null) {
                        $ds['historico_visita_gestion'] = $hist;
                    }
                }
            }
            return self::resultado(true, 'OK', ['dictamen_sistema' => $ds]);
        } catch (\Exception $e) {
            error_log('getDictamenSistema(id_ticket=' . $idTicket . ') error: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return self::resultado(false, 'Error al obtener dictamen del sistema.', null, $e->getMessage());
        }
    }

    /**
     * Parsea la descripción del dictamen y separa texto base + domicilios (desc/link).
     * Formato esperado: "... Podrás encontrar al usuario en [desc] [url]; [desc2] [url2]"
     */
    private static function parsearDomiciliosDictamen(string $descripcion): array
    {
        $out = ['base' => trim($descripcion), 'domicilios' => []];
        $prefijos = ['Podrás encontrar al usuario en ', 'Podras encontrar al usuario en '];
        $prefijo = null;
        $pos = false;
        foreach ($prefijos as $p) {
            $pp = strpos($descripcion, $p);
            if ($pp !== false) {
                $prefijo = $p;
                $pos = $pp;
                break;
            }
        }
        if ($prefijo === null || $pos === false) {
            return $out;
        }

        $out['base'] = trim(preg_replace('/\.\s*$/', '', substr($descripcion, 0, $pos)));
        $domStr = trim(substr($descripcion, $pos + strlen($prefijo)));
        $bloques = preg_split('/\s*;\s*/', $domStr, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($bloques as $bloq) {
            $bloq = trim($bloq);
            if ($bloq === '') {
                continue;
            }
            $desc = $bloq;
            $link = '';
            if (preg_match('/\s+(https?:\/\/\S+)$/u', $bloq, $m)) {
                $link = trim((string)$m[1]);
                $desc = trim(substr($bloq, 0, -strlen($m[0])));
            } elseif (preg_match('/(https?:\/\/\S+)/u', $bloq, $m2)) {
                $link = trim((string)$m2[1]);
                $desc = trim(str_replace($m2[1], '', $bloq));
            }
            $out['domicilios'][] = ['desc' => $desc, 'link' => $link];
        }
        return $out;
    }

    /**
     * Normaliza la descripción del dictamen:
     * - expande short links de mapas cuando existan;
     * - vuelve a construir el bloque de domicilios para almacenar URLs finales.
     */
    private static function normalizarDescripcionDictamenConMaps(string $descripcion): string
    {
        $descripcion = trim($descripcion);
        if ($descripcion === '') {
            return '';
        }

        $parsed = self::parsearDomiciliosDictamen($descripcion);
        $domicilios = is_array($parsed['domicilios'] ?? null) ? $parsed['domicilios'] : [];
        if (empty($domicilios)) {
            return $descripcion;
        }

        $bloques = [];
        foreach ($domicilios as $dom) {
            $desc = trim((string)($dom['desc'] ?? ''));
            $link = trim((string)($dom['link'] ?? ''));
            if ($link !== '') {
                $link = self::expandirUrlMapaSiEsCorta($link);
                $link = trim($link);
            }
            if ($desc !== '' && $link !== '') {
                $bloques[] = $desc . ' ' . $link;
            } elseif ($link !== '') {
                $bloques[] = $link;
            } elseif ($desc !== '') {
                $bloques[] = $desc;
            }
        }

        if (empty($bloques)) {
            return trim((string)($parsed['base'] ?? $descripcion));
        }

        $base = trim((string)($parsed['base'] ?? ''));
        $out = $base !== '' ? rtrim($base, ". \t\n\r\0\x0B") . '. ' : '';
        $out .= 'Podrás encontrar al usuario en ' . implode('; ', $bloques);
        return trim($out);
    }

    /**
     * Repara dictámenes ya guardados que contienen short links (maps.app.goo.gl / goo.gl/maps).
     * Expande las URLs y actualiza la columna descripcion con el texto normalizado.
     *
     * @param bool $dryRun Si true, no escribe en BD; solo cuenta cuántos se actualizarían.
     * @return array{updated: int, skipped: int, errors: array, total_candidates: int, id_tickets_actualizados: int[]}
     */
    public static function repararDictamenesConShortLinks(bool $dryRun = false): array
    {
        $result = ['updated' => 0, 'skipped' => 0, 'errors' => [], 'total_candidates' => 0, 'id_tickets_actualizados' => []];
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT id, id_ticket, descripcion FROM dictamen WHERE descripcion LIKE '%maps.app.goo.gl%' OR descripcion LIKE '%goo.gl/maps%'"
            );
            $result['total_candidates'] = is_array($rows) ? count($rows) : 0;
            if ($result['total_candidates'] === 0) {
                return $result;
            }
            foreach ($rows as $row) {
                $id = (int)($row['id'] ?? 0);
                $idTicket = (int)($row['id_ticket'] ?? 0);
                $descripcion = (string)($row['descripcion'] ?? '');
                $normalizada = self::normalizarDescripcionDictamenConMaps($descripcion);
                if ($normalizada === $descripcion) {
                    $result['skipped']++;
                    continue;
                }
                if (!$dryRun) {
                    try {
                        $db->CRUD(
                            "UPDATE dictamen SET descripcion = :descripcion WHERE id = :id",
                            ['descripcion' => $normalizada, 'id' => $id]
                        );
                        $result['updated']++;
                        if ($idTicket > 0) {
                            $result['id_tickets_actualizados'][] = $idTicket;
                        }
                    } catch (\Throwable $e) {
                        $result['errors'][] = ['id' => $id, 'error' => $e->getMessage()];
                    }
                } else {
                    $result['updated']++;
                }
            }
        } catch (\Throwable $e) {
            $result['errors'][] = ['id' => null, 'error' => $e->getMessage()];
        }
        return $result;
    }

    /**
     * Extrae coordenadas lat/lng de las URLs en domicilios del dictamen.
     */
    private static function extraerCoordenadasDictamen(string $descripcion): array
    {
        $coords = [];
        $parsed = self::parsearDomiciliosDictamen($descripcion);
        $domicilios = is_array($parsed['domicilios'] ?? null) ? $parsed['domicilios'] : [];
        foreach ($domicilios as $dom) {
            $desc = trim((string)($dom['desc'] ?? ''));
            $url = trim((string)($dom['link'] ?? ''));
            if ($url === '') {
                continue;
            }
            // Maps short links (maps.app.goo.gl / goo.gl/maps) do not contain lat/lng directly.
            // Expand redirects first, then parse coordinates from final URL.
            $expandedUrl = self::expandirUrlMapaSiEsCorta($url);
            $decoded = self::normalizarUrlParaExtraccion($expandedUrl);
            $lat = null;
            $lng = null;
            if (preg_match('/[?&](?:q|ll|query|daddr)=(-?\d+\.?\d*),(-?\d+\.?\d*)/i', $decoded, $m)) {
                $lat = (float)$m[1];
                $lng = (float)$m[2];
            } elseif (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/i', $decoded, $m2)) {
                $lat = (float)$m2[1];
                $lng = (float)$m2[2];
            } elseif (preg_match('/\/place\/(-?\d+\.?\d*),(-?\d+\.?\d*)/i', $decoded, $m3)) {
                $lat = (float)$m3[1];
                $lng = (float)$m3[2];
            } elseif (preg_match('/(-?\d{1,2}\.\d+),\s*(-?\d{1,3}\.\d+)/', $decoded, $m4)) {
                // Fallback: cualquier par lat,lng embebido en la URL.
                $lat = (float)$m4[1];
                $lng = (float)$m4[2];
            }
            if ($lat === null || $lng === null) {
                continue;
            }
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                continue;
            }
            if ((float)$lat == 0.0 && (float)$lng == 0.0) {
                continue;
            }
            $coords[] = ['desc' => $desc !== '' ? $desc : 'Dirección', 'lat' => $lat, 'lng' => $lng];
        }
        if (empty($coords)) {
            // Last-resort fallback: parse any coordinates present in full text.
            $decodedDesc = self::normalizarUrlParaExtraccion($descripcion);
            if (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/i', $decodedDesc, $mAt)) {
                $lat = (float)$mAt[1];
                $lng = (float)$mAt[2];
                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180 && !($lat == 0.0 && $lng == 0.0)) {
                    $coords[] = ['desc' => 'Dirección', 'lat' => $lat, 'lng' => $lng];
                }
            } elseif (preg_match('/(-?\d{1,2}\.\d+),\s*(-?\d{1,3}\.\d+)/', $decodedDesc, $mPair)) {
                $lat = (float)$mPair[1];
                $lng = (float)$mPair[2];
                if ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180 && !($lat == 0.0 && $lng == 0.0)) {
                    $coords[] = ['desc' => 'Dirección', 'lat' => $lat, 'lng' => $lng];
                }
            }
        }
        return $coords;
    }

    private static function normalizarUrlParaExtraccion(string $url): string
    {
        $txt = trim($url);
        for ($i = 0; $i < 2; $i++) {
            $decoded = urldecode($txt);
            if ($decoded === $txt) {
                break;
            }
            $txt = $decoded;
        }
        return $txt;
    }

    private static function expandirUrlMapaSiEsCorta(string $url): string
    {
        $url = trim($url);
        if ($url === '' || !self::esShortLinkMapa($url) || !function_exists('curl_init')) {
            return $url;
        }

        // 1) Try HEAD request following redirects.
        $ch = curl_init($url);
        if ($ch === false) {
            return $url;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_NOBODY => true,
            CURLOPT_USERAGENT => 'SpartaLedger/1.0 (DictamenSistema)',
        ]);
        curl_exec($ch);
        $final = (string)curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($final !== '' && strcasecmp($final, $url) !== 0) {
            return $final;
        }

        // 2) Some shorteners reject HEAD; retry with GET.
        if ($http === 405 || $http === 0) {
            $ch2 = curl_init($url);
            if ($ch2 === false) {
                return $url;
            }
            curl_setopt_array($ch2, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 8,
                CURLOPT_CONNECTTIMEOUT => 4,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'SpartaLedger/1.0 (DictamenSistema)',
            ]);
            curl_exec($ch2);
            $final2 = (string)curl_getinfo($ch2, CURLINFO_EFFECTIVE_URL);
            curl_close($ch2);
            if ($final2 !== '' && strcasecmp($final2, $url) !== 0) {
                return $final2;
            }
        }

        return $url;
    }

    private static function esShortLinkMapa(string $url): bool
    {
        $host = strtolower((string)parse_url($url, PHP_URL_HOST));
        $path = strtolower((string)parse_url($url, PHP_URL_PATH));
        if (strpos($host, 'www.') === 0) {
            $host = substr($host, 4);
        }
        if ($host === 'maps.app.goo.gl') {
            return true;
        }
        if ($host === 'goo.gl' && strpos($path, '/maps') === 0) {
            return true;
        }
        return false;
    }

    /**
     * Distancia entre dos puntos (lat/lng) en metros usando la fórmula de Haversine.
     */
    private static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371000; // Radio de la Tierra en metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    private static function toFloat($val): ?float
    {
        if ($val === null || $val === '') return null;
        $v = (float)trim((string)$val);
        return $v;
    }

    /**
     * Estadísticas agregadas de tickets (solo lectura): conteos por periodo, tiempos de revisión Sabueso y de visto por gestor.
     * Responde preguntas del tipo: cuántos por día/semana/mes/año, quién gestionó/asignó, tiempos hasta dictamen y hasta visto.
     */
    /**
     * @param array $options Opcional: detalle_limit (int) — filas máx. en detalle_timings (default 80; p.ej. 2000 para Excel).
     */
    public static function getEstadisticasTickets(array $options = []): array
    {
        $db = new Database();
        $detalleLimit = (int)($options['detalle_limit'] ?? 80);
        if ($detalleLimit < 1) {
            $detalleLimit = 80;
        }
        if ($detalleLimit > 5000) {
            $detalleLimit = 5000;
        }
        // Filtro solo para "Por Sabueso (dictaminó)": ventana por fecha de envío del dictamen (independiente de Tickets levantados)
        $periodoSabueso = (string)($options['periodo_sabueso'] ?? 'por_dia');
        if (!in_array($periodoSabueso, ['por_dia', 'por_semana', 'por_mes', 'por_anio'], true)) {
            $periodoSabueso = 'por_dia';
        }
        // Inicio de semana en CDMX (no CURDATE() del servidor)
        $inicioSemanaLunes = "'" . self::inicioSemanaLunesCdmx() . "'";
        $whereActivo = '(t.activo = 1 OR t.activo IS NULL) AND (t.fecha_eliminacion IS NULL)';
        $andSabueso = self::andTicketSoloSabuesoSql($db);
        $whereActivo .= $andSabueso;
        // Levantados (día/semana/mes/año): incluye cerrados; excluye solo eliminados (último historial).
        // Cerrar ticket pone fecha_eliminacion en ticket, por eso whereActivo ocultaba todo el histórico.
        $whereLevantadosHist = self::whereTicketLevantadosHistoricoSql() . $andSabueso;
        $fechaCreacionValida = 't.fecha_creacion IS NOT NULL AND DATE(t.fecha_creacion) >= \'2001-01-01\'';
        $out = [
            'success' => true,
            'mensaje' => 'OK',
            'semana_actual_periodo_ui' => self::semanaActualPeriodoUiCdmx(),
            'totales' => [
                'tickets_activos' => 0,
                'con_dictamen_enviado' => 0,
                'con_dictamen_visto' => 0,
                'tickets_cerrados' => 0,
            ],
            'por_dia' => [],
            'por_semana' => [],
            'por_mes' => [],
            'por_anio' => [],
            'tiempos_sabueso_segundos' => null,
            'tiempos_gestor_segundos' => null,
            'tiempos_por_semana' => ['semanas' => []],
            'detalle_timings' => [],
            'por_sabueso' => [],
            'por_gestor_lectura' => [],
            'kpis_extra' => [],
        ];

        try {
            // "Activos" = en flujo: no cerrados ni eliminados (misma condición que Panel Admin listado principal)
            $row = $db->queryOne("SELECT COUNT(*) AS c FROM ticket t WHERE $whereActivo");
            $out['totales']['tickets_activos'] = (int)($row['c'] ?? 0);
        } catch (\Exception $e) {
            $out['success'] = false;
            $out['mensaje'] = $e->getMessage();
            return $out;
        }
        try {
            $row = $db->queryOne(
                "SELECT COUNT(DISTINCT th.id_ticket) AS c FROM ticket_historico th " .
                "INNER JOIN ticket t ON t.id_ticket = th.id_ticket " .
                "WHERE th.tipo_accion = 'cerrado'" . $andSabueso
            );
            $out['totales']['tickets_cerrados'] = (int)($row['c'] ?? 0);
        } catch (\Exception $e) {
            $out['totales']['tickets_cerrados'] = 0;
        }

        // Conteos por día: solo semana calendario actual (lunes→domingo CDMX).
        // Al cambiar de semana, el listado se "reinicia"; el histórico se ve en por_semana.
        try {
            $monday = self::cdmxNowImmutable()->modify('-' . ((int)self::cdmxNowImmutable()->format('N') - 1) . ' days');
            $lunesDate = $monday->format('Y-m-d');
            $domingoDate = $monday->modify('+6 days')->format('Y-m-d');
            $rows = $db->queryAll(
                "SELECT DATE(t.fecha_creacion) AS periodo, COUNT(*) AS n FROM ticket t " .
                "WHERE $whereLevantadosHist AND $fechaCreacionValida AND DATE(t.fecha_creacion) >= '" . $lunesDate . "' " .
                "AND DATE(t.fecha_creacion) <= '" . $domingoDate . "' " .
                "GROUP BY DATE(t.fecha_creacion) ORDER BY periodo DESC"
            );
            $out['por_dia'] = is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            $out['por_dia'] = [];
        }

        // Por semana (Tickets levantados): agrupar por lunes calendario Lun–MySQL WEEKDAY (0=lun).
        // YEARWEEK(...) a veces agrupaba mal (p. ej. fechas inválidas/NULL → un solo grupo) o no coincidía con el drill.
        try {
            $sqlSem = "SELECT DATE_SUB(DATE(t.fecha_creacion), INTERVAL WEEKDAY(DATE(t.fecha_creacion)) DAY) AS lunes, COUNT(*) AS n " .
                "FROM ticket t WHERE $whereLevantadosHist AND $fechaCreacionValida " .
                "GROUP BY DATE_SUB(DATE(t.fecha_creacion), INTERVAL WEEKDAY(DATE(t.fecha_creacion)) DAY) " .
                "ORDER BY lunes DESC LIMIT 104";
            $rows = $db->queryAll($sqlSem);
            $filasSem = [];
            foreach (is_array($rows) ? $rows : [] as $r) {
                $lunes = trim((string)($r['lunes'] ?? ''));
                if ($lunes === '' || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $lunes)) {
                    continue;
                }
                $filasSem[] = [
                    'periodo' => $lunes,
                    'n' => (int)($r['n'] ?? 0),
                    'lunes' => $lunes,
                ];
            }
            $out['por_semana'] = $filasSem;
        } catch (\Exception $e) {
            $out['por_semana'] = [];
        }

        // Por mes: todos los meses con tickets (histórico reciente), más reciente primero.
        // Antes solo el año calendario en curso ocultaba meses de años anteriores.
        try {
            $rows = $db->queryAll(
                "SELECT DATE_FORMAT(t.fecha_creacion, '%Y-%m') AS periodo, COUNT(*) AS n FROM ticket t " .
                "WHERE $whereLevantadosHist AND $fechaCreacionValida " .
                "GROUP BY DATE_FORMAT(t.fecha_creacion, '%Y-%m') ORDER BY periodo DESC LIMIT 72"
            );
            $out['por_mes'] = is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            $out['por_mes'] = [];
        }

        // Por año (lista años con conteo; drill abre meses → semanas → 7 días)
        try {
            $rows = $db->queryAll(
                "SELECT YEAR(t.fecha_creacion) AS periodo, COUNT(*) AS n FROM ticket t " .
                "WHERE $whereLevantadosHist AND $fechaCreacionValida GROUP BY YEAR(t.fecha_creacion) ORDER BY periodo DESC"
            );
            $out['por_anio'] = is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            $out['por_anio'] = [];
        }

        // Dictamen enviado / visto (sobre tickets activos que tienen dictamen)
        try {
            $row = $db->queryOne(
                "SELECT COUNT(DISTINCT d.id_ticket) AS c FROM dictamen d " .
                "INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo " .
                "WHERE d.estado = 'enviado_al_gestor'"
            );
            $out['totales']['con_dictamen_enviado'] = (int)($row['c'] ?? 0);
        } catch (\Exception $e) {
            // ignorar
        }
        try {
            $row = $db->queryOne(
                "SELECT COUNT(DISTINCT d.id_ticket) AS c FROM dictamen d " .
                "INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo " .
                "WHERE d.estado = 'enviado_al_gestor' AND d.fecha_visto_gestor IS NOT NULL"
            );
            $out['totales']['con_dictamen_visto'] = (int)($row['c'] ?? 0);
        } catch (\Exception $e) {
            // ignorar
        }

        // Tiempo Sabueso (semana actual lun→hoy CDMX):
        // Solo envíos con fecha_actualizacion en la semana. El diff es desde la ÚLTIMA asignación
        // antes del envío (no desde la primera asignación histórica del ticket, que inflaba a días/semanas).
        try {
            $sqlSabueso = "
                SELECT AVG(TIMESTAMPDIFF(SECOND, x.fa_before, x.fa)) AS avg_sec,
                       MIN(TIMESTAMPDIFF(SECOND, x.fa_before, x.fa)) AS min_sec,
                       MAX(TIMESTAMPDIFF(SECOND, x.fa_before, x.fa)) AS max_sec,
                       COUNT(*) AS n
                FROM (
                    SELECT d.id_ticket, d.fecha_actualizacion AS fa,
                           (SELECT MAX(at2.fecha_asignacion) FROM asignacion_ticket at2
                            WHERE at2.id_ticket = d.id_ticket
                              AND at2.fecha_asignacion <= d.fecha_actualizacion
                              AND (at2.activo = 1 OR at2.activo IS NULL)
                           ) AS fa_before
                    FROM dictamen d
                    INNER JOIN (
                        SELECT id_ticket, MAX(fecha_actualizacion) AS mx
                        FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket
                    ) dm ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor'
                    INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                    WHERE d.fecha_actualizacion IS NOT NULL
                      AND d.fecha_actualizacion >= $inicioSemanaLunes
                ) x
                WHERE x.fa_before IS NOT NULL
                  AND x.fa_before <= x.fa
                  AND TIMESTAMPDIFF(SECOND, x.fa_before, x.fa) >= 0
                  AND TIMESTAMPDIFF(SECOND, x.fa_before, x.fa) <= 604800
            ";
            $row = $db->queryOne($sqlSabueso);
            if ($row && (int)($row['n'] ?? 0) > 0) {
                $out['tiempos_sabueso_segundos'] = [
                    'muestras' => (int)$row['n'],
                    'promedio_seg' => round((float)($row['avg_sec'] ?? 0)),
                    'min_seg' => (int)($row['min_sec'] ?? 0),
                    'max_seg' => (int)($row['max_sec'] ?? 0),
                    'promedio_humano' => self::segundosAHumano((int)round((float)($row['avg_sec'] ?? 0))),
                    'alcance' => 'semana_actual',
                    'alcance_texto' => 'Semana actual (lun→hoy CDMX): promedio con envíos de esta semana; tiempo desde última asignación antes del envío hasta enviar (máx. 7 días por muestra). Cada lunes se reinicia.',
                ];
            }
        } catch (\Exception $e) {
            $out['tiempos_sabueso_segundos'] = null;
        }

        // Tiempo gestor (semana actual): solo pares envío+visto donde el ENVÍO también es de esta semana.
        // Así no entran aperturas de dictámenes enviados hace semanas (que inflaban el promedio).
        try {
            $sqlGestor = "
                SELECT AVG(TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor)) AS avg_sec,
                       MIN(TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor)) AS min_sec,
                       MAX(TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor)) AS max_sec,
                       COUNT(*) AS n
                FROM dictamen d
                INNER JOIN (
                    SELECT id_ticket, MAX(fecha_actualizacion) AS mx
                    FROM dictamen
                    WHERE estado = 'enviado_al_gestor' AND fecha_visto_gestor IS NOT NULL
                    GROUP BY id_ticket
                ) dm ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor'
                INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                WHERE d.estado = 'enviado_al_gestor'
                  AND d.fecha_actualizacion IS NOT NULL
                  AND d.fecha_visto_gestor IS NOT NULL
                  AND d.fecha_visto_gestor >= d.fecha_actualizacion
                  AND d.fecha_actualizacion >= $inicioSemanaLunes
                  AND d.fecha_visto_gestor >= $inicioSemanaLunes
                  AND TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor) <= 604800
            ";
            $row = $db->queryOne($sqlGestor);
            if ($row && (int)($row['n'] ?? 0) > 0) {
                $out['tiempos_gestor_segundos'] = [
                    'muestras' => (int)$row['n'],
                    'promedio_seg' => round((float)($row['avg_sec'] ?? 0)),
                    'min_seg' => (int)($row['min_sec'] ?? 0),
                    'max_seg' => (int)($row['max_sec'] ?? 0),
                    'promedio_humano' => self::segundosAHumano((int)round((float)($row['avg_sec'] ?? 0))),
                    'alcance' => 'semana_actual',
                    'alcance_texto' => 'Semana actual (lun→hoy CDMX): solo envíos de esta semana ya vistos; tiempo desde envío hasta apertura (máx. 7 días por muestra). Cada lunes se reinicia.',
                ];
            }
        } catch (\Exception $e) {
            $out['tiempos_gestor_segundos'] = null;
        }

        try {
            $out['tiempos_por_semana'] = [
                'semanas' => self::buildTiemposDictamenPorSemanaSeries($db, 24),
            ];
        } catch (\Exception $e) {
            $out['tiempos_por_semana'] = ['semanas' => []];
        }

        // Detalle reciente: folio, creador, asignado, fechas clave (últimos 80 con dictamen enviado)
        try {
            $rows = $db->queryAll(
                "SELECT t.id_ticket, t.folio, t.fecha_creacion, t.fecha_vencimiento, t.id_persona_creador AS creador_id, " .
                "CONCAT(TRIM(IFNULL(pc.nombres,'')),' ',TRIM(IFNULL(pc.apellidop,''))) AS creador_nombre, " .
                "CONCAT(TRIM(IFNULL(pa.nombres,'')),' ',TRIM(IFNULL(pa.apellidop,''))) AS asignado_nombre, " .
                "d.fecha_creacion AS dictamen_fecha_creacion, d.fecha_actualizacion AS dictamen_fecha_envio, " .
                "d.fecha_visto_gestor AS dictamen_fecha_visto, d.estado AS dictamen_estado, " .
                "CONCAT(TRIM(IFNULL(paut.nombres,'')),' ',TRIM(IFNULL(paut.apellidop,''))) AS dictamen_autor_nombre " .
                "FROM ticket t " .
                "INNER JOIN persona pc ON t.id_persona_creador = pc.id " .
                "LEFT JOIN (SELECT at1.id_ticket, at1.id_persona_asignada FROM asignacion_ticket at1 " .
                "INNER JOIN (SELECT id_ticket, MAX(fecha_asignacion) AS mx FROM asignacion_ticket WHERE (activo=1 OR activo IS NULL) GROUP BY id_ticket) at2 " .
                "ON at1.id_ticket = at2.id_ticket AND at1.fecha_asignacion = at2.mx WHERE (at1.activo=1 OR at1.activo IS NULL)) at ON at.id_ticket = t.id_ticket " .
                "LEFT JOIN persona pa ON at.id_persona_asignada = pa.id " .
                "INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' " .
                "LEFT JOIN persona paut ON d.id_persona = paut.id " .
                "WHERE $whereActivo " .
                "ORDER BY d.fecha_actualizacion DESC LIMIT " . $detalleLimit
            );
            $lista = is_array($rows) ? $rows : [];
            // Una fila por ticket: tomar el dictamen más reciente por ticket si hay duplicados
            $porTicket = [];
            foreach ($lista as $r) {
                $tid = (int)($r['id_ticket'] ?? 0);
                if ($tid < 1 || isset($porTicket[$tid])) {
                    continue;
                }
                $porTicket[$tid] = $r;
            }
            $out['detalle_timings'] = array_values($porTicket);
            // Enriquecer con dictamen_sistema: id gestor, % efectividad, medidas preventivas
            if (!empty($out['detalle_timings'])) {
                $idsT = [];
                foreach ($out['detalle_timings'] as $r) {
                    $tid = (int)($r['id_ticket'] ?? 0);
                    if ($tid > 0) {
                        $idsT[$tid] = true;
                    }
                }
                $idsList = array_keys($idsT);
                if (!empty($idsList)) {
                    // Solo enteros (vienen de nuestro propio listado)
                    $in = implode(',', array_map('intval', $idsList));
                    $dsRows = $db->queryAll(
                        "SELECT ds1.id_ticket, ds1.id_gestor, ds1.resultado, ds1.detalle FROM dictamen_sistema ds1 " .
                        "INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen_sistema WHERE id_ticket IN ($in) GROUP BY id_ticket) dsmx " .
                        "ON ds1.id_ticket = dsmx.id_ticket AND ds1.id = dsmx.mid"
                    );
                    $dsPorTicket = [];
                    if (is_array($dsRows)) {
                        foreach ($dsRows as $dsr) {
                            $dsPorTicket[(int)$dsr['id_ticket']] = $dsr;
                        }
                    }
                    foreach ($out['detalle_timings'] as &$r) {
                        $tid = (int)($r['id_ticket'] ?? 0);
                        $r['id_gestor_dictamen'] = null;
                        $r['dictamen_sistema_resultado'] = null;
                        $r['pct_efectividad'] = null;
                        $r['medidas_preventivas'] = null;
                        $r['cumplimiento_etiqueta'] = null;
                        $r['pago_en_ventana_si'] = false;
                        $r['visito_campo_si'] = false;
                        $r['prorroga_otorgada_si'] = false;
                        if ($tid > 0 && isset($dsPorTicket[$tid])) {
                            $ds = $dsPorTicket[$tid];
                            $r['id_gestor_dictamen'] = isset($ds['id_gestor']) ? (int)$ds['id_gestor'] : null;
                            $res = $ds['resultado'] ?? null;
                            $r['dictamen_sistema_resultado'] = $res;
                            $detJson = !empty($ds['detalle']) ? json_decode($ds['detalle'], true) : null;
                            if (is_array($detJson) && isset($detJson['pct_efectividad'])) {
                                $r['pct_efectividad'] = $detJson['pct_efectividad'];
                                $r['medidas_preventivas'] = $detJson['medidas_preventivas'] ?? null;
                                $r['cumplimiento_etiqueta'] = $detJson['cumplimiento_etiqueta'] ?? null;
                            } else {
                                $cmp = self::cumplimientoMetadatos($res);
                                $r['pct_efectividad'] = $cmp['pct_efectividad'];
                                $r['medidas_preventivas'] = $cmp['medidas_preventivas'];
                                $r['cumplimiento_etiqueta'] = $cmp['cumplimiento_etiqueta'];
                            }
                            // Misma lógica que getEstadisticasGestorDetalle (columna Pago)
                            $r['__SPARTA_SECRET_REDACTED___consultado'] = (is_array($detJson) && array_key_exists('__SPARTA_SECRET_REDACTED___consultado', $detJson)) ? !empty($detJson['__SPARTA_SECRET_REDACTED___consultado']) : true;
                            if ($res === 'cumplido_pago') {
                                $r['pago_en_ventana_si'] = true;
                                $r['pago_en_ventana_txt'] = 'Sí';
                            } elseif (is_array($detJson) && array_key_exists('pago_en_ventana', $detJson)) {
                                $r['pago_en_ventana_si'] = !empty($detJson['pago_en_ventana']);
                                $r['pago_en_ventana_txt'] = $r['pago_en_ventana_si'] ? 'Sí' : ($r['__SPARTA_SECRET_REDACTED___consultado'] ? 'No' : 'No se pudo verificar');
                            } elseif ($res !== null && $res !== '' && $res !== 'pendiente' && $res !== 'prorroga_activa' && $res !== 'intensidad_activa') {
                                $r['pago_en_ventana_si'] = false;
                                $r['pago_en_ventana_txt'] = 'No';
                            } else {
                                $r['pago_en_ventana_txt'] = null;
                            }
                            // Visita de campo: resultados que implican GPS/direcciones visitadas o visita registrada
                            $visitaResultados = [
                                'visito_campo', 'visito_todas_direcciones', 'cumplido_sin_pago_todas_direcciones',
                                'visita_parcial', 'distancia_lejana',
                            ];
                            if (is_array($detJson)) {
                                $dirVis = (int)($detJson['direcciones_visitadas'] ?? 0);
                                if ($dirVis > 0) {
                                    $r['visito_campo_si'] = true;
                                }
                            }
                            if (!$r['visito_campo_si'] && $res !== null && in_array($res, $visitaResultados, true)) {
                                $r['visito_campo_si'] = true;
                            }
                            if (is_array($detJson) && isset($detJson['prorroga']) && is_array($detJson['prorroga']) && !empty($detJson['prorroga']['otorgada'])) {
                                $r['prorroga_otorgada_si'] = true;
                            }
                        }
                    }
                    unset($r);
                }
            }
        } catch (\Exception $e) {
            $out['detalle_timings'] = [];
        }

        // Panorama global de cumplimiento (dictamen_sistema): conteos por resultado y % promedio
        $out['cumplimiento_global'] = [
            'muestra' => count($out['detalle_timings']),
            'con_evaluacion' => 0,
            'sin_evaluacion' => 0,
            'pendiente' => 0,
            'por_resultado' => [],
            'pct_promedio' => null,
            'leyenda' => 'Cumplir = visita en campo según GPS del dictamen; sin pago conviene validar todas las direcciones y estado de cuenta.',
        ];
        $pctSum = 0;
        $pctN = 0;
        foreach ($out['detalle_timings'] as $r) {
            $res = $r['dictamen_sistema_resultado'] ?? null;
            if ($res === null || $res === '') {
                $out['cumplimiento_global']['sin_evaluacion']++;
                continue;
            }
            $out['cumplimiento_global']['con_evaluacion']++;
            if ($res === 'pendiente') {
                $out['cumplimiento_global']['pendiente']++;
            }
            if (!isset($out['cumplimiento_global']['por_resultado'][$res])) {
                $out['cumplimiento_global']['por_resultado'][$res] = 0;
            }
            $out['cumplimiento_global']['por_resultado'][$res]++;
            if (isset($r['pct_efectividad']) && is_numeric($r['pct_efectividad'])) {
                $pctSum += (int)$r['pct_efectividad'];
                $pctN++;
            }
        }
        if ($pctN > 0) {
            $out['cumplimiento_global']['pct_promedio'] = (int)round($pctSum / $pctN);
        }

        // Por gestor = por quien levantó el ticket (id_persona_creador → creador_nombre), no por asignado Sabueso.
        // Misma base que detalle reciente (dictamen enviado). Incluye agregados de cumplimiento por gestor.
        $out['por_gestor'] = [];
        try {
            $agg = [];
            foreach ($out['detalle_timings'] as $r) {
                $cid = (int)($r['creador_id'] ?? 0);
                $nom = trim((string)($r['creador_nombre'] ?? ''));
                if ($nom === '' || $nom === '—') {
                    $nom = '(Sin dato)';
                }
                $key = $cid > 0 ? 'id_' . $cid : 'nom_' . md5($nom);
                if (!isset($agg[$key])) {
                    $agg[$key] = [
                        'id_persona' => $cid,
                        'nombre' => $nom,
                        'tickets' => 0,
                        'vistos' => 0,
                        'sin_leer' => 0,
                        'pagaron' => 0,
                        'visitaron' => 0,
                        'prorroga_dadas' => 0,
                        'cumplimiento_por_resultado' => [],
                        'cumplimiento_pct_sum' => 0,
                        'cumplimiento_pct_n' => 0,
                        'cumplimiento_sin_evaluar' => 0,
                    ];
                }
                $agg[$key]['tickets']++;
                $visto = !empty($r['dictamen_fecha_visto']);
                if ($visto) {
                    $agg[$key]['vistos']++;
                } else {
                    $agg[$key]['sin_leer']++;
                }
                // Cumplimiento por gestor (quien levantó)
                $res = $r['dictamen_sistema_resultado'] ?? null;
                if ($res === null || $res === '') {
                    $agg[$key]['cumplimiento_sin_evaluar']++;
                } else {
                    if (!isset($agg[$key]['cumplimiento_por_resultado'][$res])) {
                        $agg[$key]['cumplimiento_por_resultado'][$res] = 0;
                    }
                    $agg[$key]['cumplimiento_por_resultado'][$res]++;
                    if (isset($r['pct_efectividad']) && is_numeric($r['pct_efectividad'])) {
                        $agg[$key]['cumplimiento_pct_sum'] += (int)$r['pct_efectividad'];
                        $agg[$key]['cumplimiento_pct_n']++;
                    }
                    if (!empty($r['pago_en_ventana_si'])) {
                        $agg[$key]['pagaron']++;
                    }
                    if (!empty($r['visito_campo_si'])) {
                        $agg[$key]['visitaron']++;
                    }
                    if (!empty($r['prorroga_otorgada_si'])) {
                        $agg[$key]['prorroga_dadas']++;
                    }
                }
            }
            foreach ($agg as &$g) {
                $g['tasa'] = $g['tickets'] > 0 ? (int)round(($g['vistos'] / $g['tickets']) * 100) : 0;
                $g['tiempo_lectura'] = '—';
                $g['tiempo_envio'] = '—';
                $n = (int)($g['cumplimiento_pct_n'] ?? 0);
                $g['cumplimiento_pct_promedio'] = $n > 0 ? (int)round($g['cumplimiento_pct_sum'] / $n) : null;
                $g['cumplimiento_evaluados'] = $g['tickets'] - (int)($g['cumplimiento_sin_evaluar'] ?? 0);
                // Texto resumido para tooltip/UI
                $partes = [];
                foreach ($g['cumplimiento_por_resultado'] as $res => $cnt) {
                    $partes[] = $res . ':' . $cnt;
                }
                $g['cumplimiento_resumen_texto'] = $partes ? implode(', ', $partes) : 'sin dictamen sistema aún';
                unset($g['cumplimiento_pct_sum'], $g['cumplimiento_pct_n']);
            }
            unset($g);
            $listaGestores = array_values($agg);
            usort($listaGestores, function ($a, $b) {
                return ($b['tickets'] ?? 0) - ($a['tickets'] ?? 0);
            });
            $out['por_gestor'] = $listaGestores;
        } catch (\Exception $e) {
            $out['por_gestor'] = [];
        }

        // Por Sabueso: misma lógica centralizada; backfill solo en carga completa (runBackfill true)
        $soloSab = self::getEstadisticasPorSabuesoSolo($periodoSabueso, true);
        $out['por_sabueso'] = $soloSab['por_sabueso'] ?? [];
        $out['cdmx_referencia'] = $soloSab['cdmx_referencia'] ?? null;

        // Lectura dictamen por gestor (creador): promedio desde envío hasta fecha_visto_gestor
        try {
            $sqlLect = "
                SELECT t.id_persona_creador AS id_persona,
                       CONCAT(TRIM(IFNULL(pc.nombres,'')),' ',TRIM(IFNULL(pc.apellidop,''))) AS nombre,
                       COUNT(*) AS n,
                       AVG(TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor)) AS avg_sec
                FROM dictamen d
                INNER JOIN (
                    SELECT id_ticket, MAX(fecha_actualizacion) AS mx
                    FROM dictamen WHERE estado = 'enviado_al_gestor' AND fecha_visto_gestor IS NOT NULL
                    GROUP BY id_ticket
                ) dm ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor'
                INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                INNER JOIN persona pc ON pc.id = t.id_persona_creador
                WHERE d.fecha_visto_gestor IS NOT NULL AND d.fecha_visto_gestor >= d.fecha_actualizacion
                GROUP BY t.id_persona_creador, pc.nombres, pc.apellidop
                HAVING n > 0
                ORDER BY n DESC
            ";
            $rows = $db->queryAll($sqlLect);
            $out['por_gestor_lectura'] = [];
            foreach (is_array($rows) ? $rows : [] as $r) {
                $avgSec = isset($r['avg_sec']) ? (int)round((float)$r['avg_sec']) : 0;
                $out['por_gestor_lectura'][] = [
                    'id_persona' => (int)($r['id_persona'] ?? 0),
                    'nombre' => trim((string)($r['nombre'] ?? '')),
                    'muestras' => (int)($r['n'] ?? 0),
                    'tiempo_lectura_humano' => self::segundosAHumano($avgSec),
                    'tiempo_lectura_seg' => $avgSec,
                ];
            }
        } catch (\Exception $e) {
            $out['por_gestor_lectura'] = [];
        }

        // Rellenar tiempo_lectura en por_gestor desde por_gestor_lectura (mismo id creador)
        $lecturaPorId = [];
        foreach ($out['por_gestor_lectura'] as $pl) {
            $lecturaPorId[(int)($pl['id_persona'] ?? 0)] = $pl['tiempo_lectura_humano'] ?? '—';
        }
        foreach ($out['por_gestor'] as &$g) {
            $cid = (int)($g['id_persona'] ?? 0);
            if ($cid > 0 && isset($lecturaPorId[$cid])) {
                $g['tiempo_lectura'] = $lecturaPorId[$cid];
            }
        }
        unset($g);

        // --- KPIs extra (solo si hay datos en BD) ---
        try {
            $kpis = [];
            // Tiempo global desde creación del ticket hasta la primera asignación (cualquier miembro Sabueso)
            $row = $db->queryOne("
                SELECT AVG(TIMESTAMPDIFF(SECOND, t.fecha_creacion, fa.primera)) AS avg_sec, COUNT(*) AS n
                FROM ticket t
                INNER JOIN (
                    SELECT id_ticket, MIN(fecha_asignacion) AS primera
                    FROM asignacion_ticket WHERE (activo = 1 OR activo IS NULL) GROUP BY id_ticket
                ) fa ON fa.id_ticket = t.id_ticket AND fa.primera >= t.fecha_creacion
                WHERE $whereActivo
            ");
            if ($row && (int)($row['n'] ?? 0) > 0 && $row['avg_sec'] !== null) {
                $sec = (int)round((float)$row['avg_sec']);
                $kpis['tiempo_creacion_a_primera_asignacion'] = [
                    'promedio_humano' => self::segundosAHumano($sec),
                    'muestras' => (int)$row['n'],
                ];
            }
            // Reasignaciones promedio antes de enviar (filas en asignacion antes de fecha envío)
            $row = $db->queryOne("
                SELECT AVG(cnt) AS avg_r FROM (
                    SELECT d.id_ticket, COUNT(at.id_asignacion) AS cnt
                    FROM dictamen d
                    INNER JOIN (
                        SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen
                        WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket
                    ) dm ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor'
                    INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                    INNER JOIN asignacion_ticket at ON at.id_ticket = d.id_ticket AND at.fecha_asignacion <= d.fecha_actualizacion
                    GROUP BY d.id_ticket
                ) x
            ");
            if ($row && $row['avg_r'] !== null) {
                $kpis['reasignaciones_promedio_antes_envio'] = round((float)$row['avg_r'], 1);
            }
            // Dictámenes en borrador sin enviar (activos, con asignado actual)
            $row = $db->queryOne("
                SELECT COUNT(*) AS c FROM dictamen d
                INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                WHERE d.estado = 'borrador'
            ");
            $kpis['dictamenes_borrador_sin_enviar'] = (int)($row['c'] ?? 0);
            // % vistos dentro de 12 h desde envío
            $row = $db->queryOne("
                SELECT
                  SUM(CASE WHEN TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor) <= 43200 THEN 1 ELSE 0 END) AS dentro,
                  COUNT(*) AS total
                FROM dictamen d
                INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                WHERE d.estado = 'enviado_al_gestor' AND d.fecha_visto_gestor IS NOT NULL
                  AND d.fecha_visto_gestor >= d.fecha_actualizacion
            ");
            $tot = (int)($row['total'] ?? 0);
            if ($tot > 0) {
                $kpis['pct_visto_dentro_12h'] = (int)round(((int)($row['dentro'] ?? 0) / $tot) * 100);
                $kpis['visto_dentro_12h_muestras'] = $tot;
            }
            // Tickets activos: sin ninguna fila en asignacion_ticket, o primera asignación > 24 h tras creación
            $row = $db->queryOne("
                SELECT COUNT(*) AS c FROM ticket t
                WHERE $whereActivo
                AND (
                    NOT EXISTS (SELECT 1 FROM asignacion_ticket at WHERE at.id_ticket = t.id_ticket)
                    OR (
                        (SELECT MIN(at2.fecha_asignacion) FROM asignacion_ticket at2 WHERE at2.id_ticket = t.id_ticket) >
                        DATE_ADD(t.fecha_creacion, INTERVAL 24 HOUR)
                    )
                )
            ");
            $kpis['tickets_cola_lenta_24h'] = (int)($row['c'] ?? 0);
            $out['kpis_extra'] = $kpis;
        } catch (\Exception $e) {
            $out['kpis_extra'] = [];
        }

        // Por Sabueso: tiempo creación → primera asignación en tickets que él dictaminó (espera hasta que alguien asignó)
        try {
            if (!empty($out['por_sabueso'])) {
                $sqlPrim = "
                    SELECT d.id_persona AS id_persona,
                           AVG(TIMESTAMPDIFF(SECOND, t.fecha_creacion, fx.primera)) AS avg_sec,
                           COUNT(*) AS n
                    FROM dictamen d
                    INNER JOIN (
                        SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen
                        WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket
                    ) dm ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor'
                    INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                    INNER JOIN (
                        SELECT id_ticket, MIN(fecha_asignacion) AS primera
                        FROM asignacion_ticket WHERE (activo = 1 OR activo IS NULL) GROUP BY id_ticket
                    ) fx ON fx.id_ticket = d.id_ticket AND fx.primera >= t.fecha_creacion
                    WHERE d.id_persona IS NOT NULL AND d.id_persona > 0
                    GROUP BY d.id_persona
                ";
                $rows = $db->queryAll($sqlPrim);
                $mapPrim = [];
                foreach (is_array($rows) ? $rows : [] as $r) {
                    $idp = (int)($r['id_persona'] ?? 0);
                    if ($idp < 1) {
                        continue;
                    }
                    $sec = isset($r['avg_sec']) && $r['avg_sec'] !== null ? (int)round((float)$r['avg_sec']) : null;
                    $mapPrim[$idp] = $sec !== null ? self::segundosAHumano($sec) : '—';
                }
                foreach ($out['por_sabueso'] as &$s) {
                    $idp = (int)($s['id_persona'] ?? 0);
                    $s['creacion_a_primera_asignacion_humano'] = $mapPrim[$idp] ?? '—';
                }
                unset($s);
            }
        } catch (\Exception $e) {
            // ignore
        }

        return $out;
    }

    /**
     * Detalle por gestor (creador): tickets que levantó con dictamen enviado; por ticket: envío, visto, tiempo lectura.
     */
    /**
     * Detalle por Sabueso (autor dictamen): tickets que dictaminó (envió) con folio, fechas, visto gestor.
     */
    public static function getEstadisticasSabuesoDetalle(int $idPersonaAutor): array
    {
        $db = new Database();
        $whereActivo = '(t.activo = 1 OR t.activo IS NULL) AND (t.fecha_eliminacion IS NULL)';
        $whereActivo .= self::andTicketSoloSabuesoSql($db);
        $pid = (int)$idPersonaAutor;
        if ($pid < 1) {
            return ['success' => false, 'mensaje' => 'ID inválido', 'nombre' => '', 'filas' => []];
        }
        try {
            $nom = self::getNombrePersona($pid);
            // Por ticket: dictamen enviado más reciente con este autor; tiempos Sabueso vía subconsultas
            $rows = $db->queryAll(
                "SELECT t.id_ticket, t.folio, t.fecha_creacion, " .
                "CONCAT(TRIM(IFNULL(pc.nombres,'')),' ',TRIM(IFNULL(pc.apellidop,''))) AS creador_nombre, " .
                "d.fecha_actualizacion AS dictamen_envio, d.fecha_visto_gestor AS dictamen_visto, " .
                "(SELECT MIN(at0.fecha_asignacion) FROM asignacion_ticket at0 WHERE at0.id_ticket = t.id_ticket) AS primera_asignacion, " .
                "(SELECT MAX(at1.fecha_asignacion) FROM asignacion_ticket at1 " .
                " WHERE at1.id_ticket = d.id_ticket AND at1.id_persona_asignada = :pid2 AND at1.fecha_asignacion <= d.fecha_actualizacion) AS asignado_a_autor_antes_envio " .
                "FROM dictamen d " .
                "INNER JOIN (SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket) dm " .
                "ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor' " .
                "INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo " .
                "INNER JOIN persona pc ON pc.id = t.id_persona_creador " .
                "WHERE d.id_persona = :pid " .
                "ORDER BY d.fecha_actualizacion DESC LIMIT 200",
                ['pid' => $pid, 'pid2' => $pid]
            );
            $filas = [];
            foreach (is_array($rows) ? $rows : [] as $r) {
                $visto = !empty($r['dictamen_visto']);
                $envio = $r['dictamen_envio'] ?? null;
                $vistoF = $r['dictamen_visto'] ?? null;
                $secLectura = null;
                if ($visto && $envio && $vistoF) {
                    $t1 = strtotime($envio);
                    $t2 = strtotime($vistoF);
                    if ($t1 !== false && $t2 !== false && $t2 >= $t1) {
                        $secLectura = $t2 - $t1;
                    }
                }
                $primAsig = $r['primera_asignacion'] ?? null;
                $asigAntes = $r['asignado_a_autor_antes_envio'] ?? null;
                $secAsignadoEnvio = null;
                if ($asigAntes && $envio) {
                    $ta = strtotime($asigAntes);
                    $te = strtotime($envio);
                    if ($ta !== false && $te !== false && $te >= $ta) {
                        $secAsignadoEnvio = $te - $ta;
                    }
                }
                $secCola = null;
                if ($primAsig && !empty($r['fecha_creacion'])) {
                    $tc = strtotime($r['fecha_creacion']);
                    $tp = strtotime($primAsig);
                    if ($tc !== false && $tp !== false && $tp >= $tc) {
                        $secCola = $tp - $tc;
                    }
                }
                $filas[] = [
                    'id_ticket' => (int)($r['id_ticket'] ?? 0),
                    'folio' => $r['folio'] ?? '',
                    'creador_nombre' => trim((string)($r['creador_nombre'] ?? '')),
                    'fecha_creacion' => $r['fecha_creacion'] ?? null,
                    'dictamen_envio' => $envio,
                    'visto_si_no' => $visto ? 'Sí' : 'No',
                    'tiempo_lectura_gestor_humano' => ($secLectura !== null) ? self::segundosAHumano((int)$secLectura) : ($visto ? '—' : 'Pendiente'),
                    'estaba_asignado_a_el' => $asigAntes ? 'Sí' : 'No',
                    'tiempo_asignado_a_envio_humano' => ($secAsignadoEnvio !== null) ? self::segundosAHumano((int)$secAsignadoEnvio) : '—',
                    'tiempo_cola_hasta_primera_asignacion_humano' => ($secCola !== null) ? self::segundosAHumano((int)$secCola) : '—',
                ];
            }
            return ['success' => true, 'mensaje' => 'OK', 'nombre' => $nom, 'filas' => $filas];
        } catch (\Exception $e) {
            return ['success' => false, 'mensaje' => $e->getMessage(), 'nombre' => '', 'filas' => []];
        }
    }

    /**
     * @param int $idPersonaCreador ID del gestor (creador de tickets).
     * @param int $page Página (1-based).
     * @param int $perPage Filas por página (máx 100).
     * @return array { success, mensaje, nombre, filas, total, page, per_page }
     */
    public static function getEstadisticasGestorDetalle(int $idPersonaCreador, int $page = 1, int $perPage = 50, string $vista = 'lectura'): array
    {
        $db = new Database();
        $whereActivo = '(t.activo = 1 OR t.activo IS NULL) AND (t.fecha_eliminacion IS NULL)';
        $whereActivo .= self::andTicketSoloSabuesoSql($db);
        $cid = (int)$idPersonaCreador;
        if ($cid < 1) {
            return ['success' => false, 'mensaje' => 'ID inválido', 'nombre' => '', 'filas' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage];
        }
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));
        $offset = ($page - 1) * $perPage;
        try {
            $vista = trim((string)$vista);
            $esVistaPagosVisitas = ($vista === 'pagos_visitas');
            $cacheKey = 'estad_gestor_detalle:' . $cid . ':' . $vista . ':' . $page . ':' . $perPage . ':' . self::fechaCdmx();
            $cacheTtl = $esVistaPagosVisitas ? 300 : 120;
            $cacheHit = self::statsCacheRead($cacheKey, $cacheTtl);
            if (is_array($cacheHit) && !empty($cacheHit['success'])) {
                return $cacheHit;
            }
            $nom = self::getNombrePersona($cid);
            $countRow = $db->queryOne(
                "SELECT COUNT(*) AS n FROM (" .
                "SELECT t.id_ticket " .
                "FROM ticket t " .
                "INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' " .
                "INNER JOIN (SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket) dm " .
                "ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx " .
                "WHERE t.id_persona_creador = :cid AND $whereActivo" .
                ") x",
                ['cid' => $cid]
            );
            $total = (int)($countRow['n'] ?? 0);
            // Un dictamen por ticket: el enviado con visto preferente; si no, el de max fecha_actualizacion
            $rows = $db->queryAll(
                "SELECT t.id_ticket, t.folio, t.id_credito, t.fecha_creacion, " .
                "d.fecha_actualizacion AS dictamen_envio, d.fecha_visto_gestor AS dictamen_visto " .
                "FROM ticket t " .
                "INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' " .
                "INNER JOIN (SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket) dm " .
                "ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx " .
                "WHERE t.id_persona_creador = :cid AND $whereActivo " .
                "ORDER BY d.fecha_actualizacion DESC LIMIT " . (int)$perPage . " OFFSET " . (int)$offset,
                ['cid' => $cid]
            );
            $filas = [];
            $listaRows = is_array($rows) ? $rows : [];
            $idsT = [];
            $idsCredito = [];
            foreach ($listaRows as $r) {
                $tid = (int)($r['id_ticket'] ?? 0);
                if ($tid > 0) {
                    $idsT[$tid] = true;
                }
                $idCr = (int)($r['id_credito'] ?? 0);
                if ($idCr > 0) {
                    $idsCredito[$idCr] = true;
                }
            }
            $nombresCliente = [];
            if (!empty($idsCredito)) {
                $nombresCliente = self::getNombresClienteParaReporte(array_keys($idsCredito));
            }
            // Pagos durante la semana actual (lunes 00:00 CDMX → ahora), por id_credito (API estado de cuenta)
            // Solo en vista pagos_visitas para evitar latencia innecesaria en "lectura y tasa".
            $pagosPorCredito = [];
            if ($esVistaPagosVisitas) {
                $inicioSemana = self::inicioSemanaLunesCdmx();
                $finSemana = self::ahoraCdmx();
                foreach (array_keys($idsCredito) as $idCr) {
                    $idCr = (int)$idCr;
                    if ($idCr < 1) {
                        continue;
                    }
                    $resPagos = self::getPagosEstadoCuentaEnVentana($idCr, $inicioSemana, $finSemana, [
                        'timeout_segundos' => 4,
                        'max_api_calls' => 30,
                        'cache_ttl_segundos' => 600,
                    ]);
                    $pagos = $resPagos['pagos'] ?? [];
                    $pagosPorCredito[$idCr] = [
                        'si' => count($pagos) > 0,
                        'count' => count($pagos),
                        'consultado' => !empty($resPagos['__SPARTA_SECRET_REDACTED___consultado']),
                    ];
                }
            }
            // Último dictamen_sistema por ticket (mismo criterio que Panel Admin / detalle_timings)
            $dsPorTicket = [];
            if (!empty($idsT)) {
                $in = implode(',', array_map('intval', array_keys($idsT)));
                $dsRows = $db->queryAll(
                    "SELECT ds1.id_ticket, ds1.resultado, ds1.detalle FROM dictamen_sistema ds1 " .
                    "INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen_sistema WHERE id_ticket IN ($in) GROUP BY id_ticket) dsmx " .
                    "ON ds1.id_ticket = dsmx.id_ticket AND ds1.id = dsmx.mid"
                );
                if (is_array($dsRows)) {
                    foreach ($dsRows as $dsr) {
                        $dsPorTicket[(int)$dsr['id_ticket']] = $dsr;
                    }
                }
            }
            foreach ($listaRows as $r) {
                $visto = !empty($r['dictamen_visto']);
                $envio = $r['dictamen_envio'] ?? null;
                $vistoF = $r['dictamen_visto'] ?? null;
                $sec = null;
                if ($visto && $envio && $vistoF) {
                    $t1 = strtotime($envio);
                    $t2 = strtotime($vistoF);
                    if ($t1 !== false && $t2 !== false && $t2 >= $t1) {
                        $sec = $t2 - $t1;
                    }
                }
                $tid = (int)($r['id_ticket'] ?? 0);
                $pct = null;
                $resDs = null;
                $etiq = null;
                $pagoVentanaTxt = null;
                if ($tid > 0 && isset($dsPorTicket[$tid])) {
                    $ds = $dsPorTicket[$tid];
                    $resDs = $ds['resultado'] ?? null;
                    $detJson = !empty($ds['detalle']) ? json_decode($ds['detalle'], true) : null;
                    if (is_array($detJson) && array_key_exists('pct_efectividad', $detJson)) {
                        $pct = $detJson['pct_efectividad'];
                        $etiq = $detJson['cumplimiento_etiqueta'] ?? null;
                    } elseif ($resDs !== null) {
                        $cmp = self::cumplimientoMetadatos($resDs);
                        $pct = $cmp['pct_efectividad'];
                        $etiq = $cmp['cumplimiento_etiqueta'];
                    }
                    // Texto corto solo para UI (nunca mostrar código tipo no_visito)
                    if ($etiq !== null && $etiq !== '') {
                        $etiq = str_replace('No visitó', 'No visito', (string)$etiq);
                    }
                    // Columna "Pago": Sí / No / No se pudo verificar (si estado de cuenta no se consultó correctamente)
                    if ($resDs === 'cumplido_pago') {
                        $pagoVentanaTxt = 'Sí';
                    } elseif (is_array($detJson) && array_key_exists('pago_en_ventana', $detJson)) {
                        $consultado = !array_key_exists('__SPARTA_SECRET_REDACTED___consultado', $detJson) || !empty($detJson['__SPARTA_SECRET_REDACTED___consultado']);
                        if (!$consultado && empty($detJson['pago_en_ventana'])) {
                            $pagoVentanaTxt = 'No se pudo verificar';
                        } else {
                            $pagoVentanaTxt = !empty($detJson['pago_en_ventana']) ? 'Sí' : 'No';
                        }
                    } elseif ($resDs !== null && $resDs !== '' && $resDs !== 'pendiente' && $resDs !== 'prorroga_activa' && $resDs !== 'intensidad_activa') {
                        $pagoVentanaTxt = 'No';
                    }
                }
                // Visitó (campo): coherente con agregado por_gestor.visitaron
                $visitoVentanaTxt = null;
                if ($tid > 0 && isset($dsPorTicket[$tid])) {
                    $visitaResultados = [
                        'visito_campo', 'visito_todas_direcciones', 'cumplido_sin_pago_todas_direcciones',
                        'visita_parcial', 'distancia_lejana',
                    ];
                    $dsV = $dsPorTicket[$tid];
                    $resV = $dsV['resultado'] ?? null;
                    $detV = !empty($dsV['detalle']) ? json_decode($dsV['detalle'], true) : null;
                    if (is_array($detV) && (int)($detV['direcciones_visitadas'] ?? 0) > 0) {
                        $visitoVentanaTxt = 'Sí';
                    } elseif ($resV !== null && in_array($resV, $visitaResultados, true)) {
                        $visitoVentanaTxt = 'Sí';
                    } elseif ($resV === 'no_visito' || $resV === 'visito_telefonico') {
                        $visitoVentanaTxt = 'No';
                    } elseif ($resV !== null && $resV !== '' && $resV !== 'pendiente' && $resV !== 'prorroga_activa' && $resV !== 'intensidad_activa') {
                        $visitoVentanaTxt = 'No';
                    }
                }
                // Extensión (estadísticas): columna muestra «Prórroga» o «Intensidad» (— si no aplica)
                $prorrogaTxt = null;
                $pagoEnProrrogaTxt = null; // Sí/No / No se pudo verificar en 2.ª ventana; — si no aplica
                if ($tid > 0 && isset($dsPorTicket[$tid])) {
                    $dsPr = $dsPorTicket[$tid];
                    $detPr = !empty($dsPr['detalle']) ? json_decode($dsPr['detalle'], true) : null;
                    $resPr = $dsPr['resultado'] ?? null;
                    $tipoVentanaRev = '';
                    $tipoVentanaLeg = '';
                    if (is_array($detPr)) {
                        $vr = $detPr['ventana_revision'] ?? null;
                        if (is_array($vr)) {
                            $tipoVentanaRev = trim((string)($vr['tipo'] ?? ''));
                        }
                        $vl = $detPr['ventana'] ?? null;
                        if (is_array($vl)) {
                            $tipoVentanaLeg = trim((string)($vl['tipo'] ?? ''));
                        }
                    }
                    $segundaVentanaEnDetalle = is_array($detPr) && array_key_exists('pago_en_ventana', $detPr)
                        && ($tipoVentanaRev === 'prorroga_12h' || $tipoVentanaRev === 'intensidad_12h' || $tipoVentanaLeg === 'prorroga_12h');
                    if ($segundaVentanaEnDetalle) {
                        $consultadoPr = !array_key_exists('__SPARTA_SECRET_REDACTED___consultado', $detPr) || !empty($detPr['__SPARTA_SECRET_REDACTED___consultado']);
                        if (!$consultadoPr && empty($detPr['pago_en_ventana'])) {
                            $pagoEnProrrogaTxt = 'No se pudo verificar';
                        } else {
                            $pagoEnProrrogaTxt = !empty($detPr['pago_en_ventana']) ? 'Sí' : 'No';
                        }
                    }
                    $inD = is_array($detPr) ? ($detPr['intensidad'] ?? []) : [];
                    $prD = is_array($detPr) ? ($detPr['prorroga'] ?? []) : [];
                    $intensidadOtorgada = is_array($inD) && !empty($inD['otorgada']);
                    $prorrogaOtorgada = is_array($prD) && !empty($prD['otorgada']);
                    if ($intensidadOtorgada) {
                        $prorrogaTxt = 'Intensidad';
                    } elseif ($prorrogaOtorgada) {
                        $prorrogaTxt = 'Prórroga';
                    } elseif ($resPr === 'cumplio_prorroga' || $resPr === 'no_cumplio_prorroga') {
                        $prorrogaTxt = ($tipoVentanaRev === 'intensidad_12h' || !empty($inD['evaluada'])) ? 'Intensidad' : 'Prórroga';
                    }
                    if ($prorrogaTxt === null && !empty($segundaVentanaEnDetalle)) {
                        $prorrogaTxt = ($tipoVentanaRev === 'intensidad_12h') ? 'Intensidad' : 'Prórroga';
                    }
                    $idCreditoPr = (int)($r['id_credito'] ?? 0);
                    if ($esVistaPagosVisitas && $pagoEnProrrogaTxt === null && $idCreditoPr > 0 && is_array($detPr)) {
                        $fOt = '';
                        $fLi = '';
                        if ($intensidadOtorgada) {
                            $fOt = trim((string)($inD['fecha_otorgada'] ?? ''));
                            $fLi = trim((string)($inD['fecha_limite'] ?? ''));
                        } elseif ($prorrogaOtorgada) {
                            $fOt = trim((string)($prD['fecha_otorgada'] ?? ''));
                            $fLi = trim((string)($prD['fecha_limite'] ?? ''));
                        } elseif ($resPr === 'cumplio_prorroga' || $resPr === 'no_cumplio_prorroga') {
                            if ($prorrogaTxt === 'Intensidad' && is_array($inD)) {
                                $fOt = trim((string)($inD['fecha_otorgada'] ?? ''));
                                $fLi = trim((string)($inD['fecha_limite'] ?? ''));
                            }
                            if (($fOt === '' || $fLi === '') && is_array($prD)) {
                                $fOt = trim((string)($prD['fecha_otorgada'] ?? ''));
                                $fLi = trim((string)($prD['fecha_limite'] ?? ''));
                            }
                        }
                        if ($fOt !== '' && $fLi !== '') {
                            $resProrroga = self::getPagosEstadoCuentaEnVentana($idCreditoPr, $fOt, $fLi);
                            $pagosProrroga = $resProrroga['pagos'] ?? [];
                            $pagoEnProrrogaTxt = !empty($resProrroga['__SPARTA_SECRET_REDACTED___consultado']) ? (!empty($pagosProrroga) ? 'Sí' : 'No') : 'No se pudo verificar';
                        }
                    }
                    if ($pagoEnProrrogaTxt === null && ($prorrogaTxt === 'Prórroga' || $prorrogaTxt === 'Intensidad')) {
                        $pagoEnProrrogaTxt = '—';
                    }
                }
                $resultadoMostrar = null;
                if ($etiq !== null && $etiq !== '') {
                    $resultadoMostrar = $etiq;
                } elseif ($resDs !== null && $resDs !== '') {
                    $cmp = self::cumplimientoMetadatos($resDs);
                    $resultadoMostrar = $cmp['cumplimiento_etiqueta'] ?? $resDs;
                }
                $idCredito = (int)($r['id_credito'] ?? 0);
                $nombreCliente = ($idCredito > 0 && isset($nombresCliente[$idCredito])) ? $nombresCliente[$idCredito] : '—';
                $infoPagoSemana = $idCredito > 0 && isset($pagosPorCredito[$idCredito])
                    ? $pagosPorCredito[$idCredito]
                    : ['si' => false, 'count' => 0, 'consultado' => false];
                $filas[] = [
                    'id_ticket' => $tid,
                    'folio' => $r['folio'] ?? '',
                    'id_credito' => $idCredito > 0 ? $idCredito : null,
                    'nombre_cliente' => $nombreCliente,
                    'fecha_creacion' => $r['fecha_creacion'] ?? null,
                    'dictamen_envio' => $envio,
                    'visto_si_no' => $visto ? 'Sí' : 'No',
                    'visto_cuando' => $vistoF,
                    'tiempo_lectura_humano' => ($sec !== null) ? self::segundosAHumano((int)$sec) : ($visto ? 'Menos de 1 min' : 'Pendiente'),
                    'dictamen_sistema_resultado' => $resDs,
                    'pct_efectividad' => $pct,
                    'cumplimiento_etiqueta' => $etiq,
                    'resultado_ds_mostrar' => $resultadoMostrar,
                    'pago_en_ventana_resumen' => $pagoVentanaTxt,
                    'visito_campo_resumen' => $visitoVentanaTxt,
                    'prorroga_otorgada_resumen' => $prorrogaTxt,
                    'pago_en_prorroga_resumen' => $pagoEnProrrogaTxt,
                    'pago_durante_semana_si' => $infoPagoSemana['si'],
                    'pago_durante_semana_count' => $infoPagoSemana['count'],
                    'pago_durante_semana_consultado' => !empty($infoPagoSemana['consultado']),
                ];
            }
            $payload = ['success' => true, 'mensaje' => 'OK', 'nombre' => $nom, 'filas' => $filas, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
            self::statsCacheWrite($cacheKey, $payload, $cacheTtl);
            return $payload;
        } catch (\Throwable $e) {
            error_log('getEstadisticasGestorDetalle error: ' . $e->getMessage());
            return ['success' => false, 'mensaje' => $e->getMessage(), 'nombre' => '', 'filas' => [], 'total' => 0, 'page' => 1, 'per_page' => $perPage];
        }
    }

    /**
     * Reporte semanal global (por quien levantó): siempre sobre semanas vencidas.
     * Semana cerrada = lunes 00:00:00 a domingo 23:59:59 (CDMX).
     */
    public static function getReporteSemanalGestorGlobal(string $semanaInicio = ''): array
    {
        $db = new Database();
        /**
         * Cerrar ticket pone activo=0 y fecha_eliminacion (igual que eliminar en ticket).
         * Para el reporte semanal histórico deben seguir contando los cerrados.
         * Excluimos solo tickets cuyo ÚLTIMO registro en ticket_historico sea tipo_accion = 'eliminado'.
         */
        $whereReporteTicket = "NOT EXISTS (
            SELECT 1
            FROM ticket_historico he
            WHERE he.id_ticket = t.id_ticket
              AND he.tipo_accion = 'eliminado'
              AND he.fecha_eliminacion = (
                  SELECT MAX(hx.fecha_eliminacion)
                  FROM ticket_historico hx
                  WHERE hx.id_ticket = t.id_ticket
              )
        )";
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = self::cdmxNowImmutable();
            $dow = (int)$now->format('N');
            $mondayCurrent = $now->modify('-' . ($dow - 1) . ' days')->setTime(0, 0, 0);
            $mondayLastClosed = $mondayCurrent->modify('-7 days');

            $semanaSelDt = $mondayLastClosed;
            if ($semanaInicio !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $semanaInicio)) {
                $tmp = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $semanaInicio . ' 00:00:00', $tz);
                if ($tmp instanceof \DateTimeImmutable && $tmp < $mondayCurrent) {
                    $semanaSelDt = $tmp;
                }
            }

            $semanaSelInicio = $semanaSelDt->format('Y-m-d') . ' 00:00:00';
            $semanaSelFinExcl = $semanaSelDt->modify('+7 days')->format('Y-m-d') . ' 00:00:00';
            $semanaSelFinIncl = $semanaSelDt->modify('+6 days')->format('Y-m-d') . ' 23:59:59';
            $cacheKey = 'reporte_semanal_global:v3:' . $semanaSelDt->format('Y-m-d');
            $cacheHit = self::statsCacheRead($cacheKey, 300);
            if (is_array($cacheHit) && !empty($cacheHit['success'])) {
                return $cacheHit;
            }

            $archivoHit = self::reporteSemanalGlobalArchivoLeer($semanaSelDt->format('Y-m-d'));
            if (is_array($archivoHit) && !empty($archivoHit['success'])) {
                self::statsCacheWrite($cacheKey, $archivoHit, 300);
                return $archivoHit;
            }

            @set_time_limit(600);
            if (function_exists('ini_set')) {
                @ini_set('max_execution_time', '600');
            }

            $semanasRows = $db->queryAll(
                "SELECT DISTINCT DATE_SUB(DATE(d.fecha_actualizacion), INTERVAL WEEKDAY(d.fecha_actualizacion) DAY) AS semana_inicio " .
                "FROM ticket t " .
                "INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' " .
                "INNER JOIN (SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket) dm " .
                "ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx " .
                "WHERE $whereReporteTicket AND d.fecha_actualizacion < :mondayCurrent " .
                "ORDER BY semana_inicio DESC LIMIT 32",
                ['mondayCurrent' => $mondayCurrent->format('Y-m-d H:i:s')]
            );
            $weekStarts = [$mondayLastClosed->format('Y-m-d')];
            $selKey = $semanaSelDt->format('Y-m-d');
            if (!in_array($selKey, $weekStarts, true)) {
                $weekStarts[] = $selKey;
            }
            if (is_array($semanasRows)) {
                foreach ($semanasRows as $wr) {
                    $w = trim((string)($wr['semana_inicio'] ?? ''));
                    if ($w !== '' && !in_array($w, $weekStarts, true)) {
                        $weekStarts[] = $w;
                    }
                }
            }
            rsort($weekStarts);

            $semanas = [];
            foreach ($weekStarts as $w) {
                $ini = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $w . ' 00:00:00', $tz);
                if (!$ini) {
                    continue;
                }
                if ($ini >= $mondayCurrent) {
                    continue;
                }
                $fin = $ini->modify('+6 days');
                $semanas[] = [
                    'inicio' => $ini->format('Y-m-d'),
                    'fin' => $fin->format('Y-m-d'),
                    'label' => 'Semana ' . $ini->format('d/m') . ' al ' . $fin->format('d/m/Y'),
                    'selected' => $ini->format('Y-m-d') === $semanaSelDt->format('Y-m-d'),
                ];
            }

            $rows = $db->queryAll(
                "SELECT t.id_ticket, t.folio, t.id_credito, t.id_persona_creador, t.fecha_creacion, " .
                "d.fecha_actualizacion AS dictamen_envio, d.tipo AS dictamen_tipo_sabueso " .
                "FROM ticket t " .
                "INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' " .
                "INNER JOIN (SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket) dm " .
                "ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx " .
                "WHERE $whereReporteTicket AND d.fecha_actualizacion >= :fi AND d.fecha_actualizacion < :ff " .
                "ORDER BY d.fecha_actualizacion DESC",
                ['fi' => $semanaSelInicio, 'ff' => $semanaSelFinExcl]
            );
            $listaRows = is_array($rows) ? $rows : [];

            $idsT = [];
            $idsCredito = [];
            $idsGestor = [];
            foreach ($listaRows as $r) {
                $tid = (int)($r['id_ticket'] ?? 0);
                $cid = (int)($r['id_credito'] ?? 0);
                $gid = (int)($r['id_persona_creador'] ?? 0);
                if ($tid > 0) $idsT[$tid] = true;
                if ($cid > 0) $idsCredito[$cid] = true;
                if ($gid > 0) $idsGestor[$gid] = true;
            }

            $nombresCliente = !empty($idsCredito) ? self::getNombresClienteParaReporte(array_keys($idsCredito)) : [];
            $nombresGestor = !empty($idsGestor) ? self::getNombresPersonaParaReporte(array_keys($idsGestor)) : [];

            $optsReporteSemanal = [
                'timeout_segundos' => 10,
                'max_api_calls' => 1000,
                'cache_ttl_segundos' => 604800,
                'ec_parallel_concurrency' => 12,
            ];
            self::reporteSemanalPrecargarEstadoCuenta(array_keys($idsCredito), $optsReporteSemanal);
            $pagosSemanaPorCredito = [];
            foreach (array_keys($idsCredito) as $cid) {
                $cid = (int)$cid;
                if ($cid < 1) {
                    continue;
                }
                $resPs = self::getPagosEstadoCuentaEnVentana($cid, $semanaSelInicio, $semanaSelFinIncl, $optsReporteSemanal);
                $ps = $resPs['pagos'] ?? [];
                $pagosSemanaPorCredito[$cid] = [
                    'si' => !empty($ps),
                    'count' => count($ps),
                    'consultado' => !empty($resPs['__SPARTA_SECRET_REDACTED___consultado']),
                ];
            }

            $dsPorTicket = [];
            if (!empty($idsT)) {
                $in = implode(',', array_map('intval', array_keys($idsT)));
                $dsRows = $db->queryAll(
                    "SELECT ds1.id_ticket, ds1.resultado, ds1.detalle FROM dictamen_sistema ds1 " .
                    "INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen_sistema WHERE id_ticket IN ($in) GROUP BY id_ticket) dsmx " .
                    "ON ds1.id_ticket = dsmx.id_ticket AND ds1.id = dsmx.mid"
                );
                if (is_array($dsRows)) {
                    foreach ($dsRows as $dsr) {
                        $dsPorTicket[(int)$dsr['id_ticket']] = $dsr;
                    }
                }
            }

            $filas = [];
            foreach ($listaRows as $r) {
                $tid = (int)($r['id_ticket'] ?? 0);
                $idCredito = (int)($r['id_credito'] ?? 0);
                $idGestor = (int)($r['id_persona_creador'] ?? 0);
                $nombreCliente = ($idCredito > 0 && isset($nombresCliente[$idCredito])) ? $nombresCliente[$idCredito] : '—';
                $nombreGestor = ($idGestor > 0 && isset($nombresGestor[$idGestor])) ? $nombresGestor[$idGestor] : '—';
                $pagoSemana = $idCredito > 0 && isset($pagosSemanaPorCredito[$idCredito]) ? $pagosSemanaPorCredito[$idCredito] : ['si' => false, 'count' => 0, 'consultado' => false];

                $fueDirecciones = null;
                $direccionesFue = '—';
                $pago12h = null;
                $prorroga = null;
                $extension12hTipo = null; // 'Prórroga' | 'Intensidad' | null (— en UI)
                $pagoProrroga = null;
                $tipoContacto = '—'; // Campo | Telefónica | —
                $detJson = null;
                $res = '';
                if ($tid > 0 && isset($dsPorTicket[$tid])) {
                    $ds = $dsPorTicket[$tid];
                    $res = trim((string)($ds['resultado'] ?? ''));
                    $detJson = !empty($ds['detalle']) ? json_decode($ds['detalle'], true) : null;
                    if ($res === 'visito_telefonico') {
                        $tipoContacto = 'Telefónica';
                    } elseif (in_array($res, ['visito_campo', 'visito_todas_direcciones', 'cumplido_sin_pago_todas_direcciones', 'visita_parcial', 'distancia_lejana'], true)) {
                        $tipoContacto = 'Campo';
                    }

                    if ($res === 'cumplido_pago') {
                        $pago12h = true;
                    } elseif (is_array($detJson) && array_key_exists('pago_en_ventana', $detJson)) {
                        $pago12h = !empty($detJson['pago_en_ventana']);
                    }

                    if (is_array($detJson)) {
                        $totDir = (int)($detJson['direcciones_dictamen_total'] ?? 0);
                        $visDir = (int)($detJson['direcciones_visitadas'] ?? 0);
                        if (array_key_exists('visito_todas_direcciones', $detJson)) {
                            $fueDirecciones = !empty($detJson['visito_todas_direcciones']);
                        } elseif ($totDir > 0) {
                            $fueDirecciones = ($visDir === $totDir);
                        }
                        if (!empty($detJson['cobertura_direcciones']) && is_array($detJson['cobertura_direcciones'])) {
                            $dirs = [];
                            foreach ($detJson['cobertura_direcciones'] as $cd) {
                                if (!empty($cd['visitada'])) {
                                    $dirs[] = trim((string)($cd['direccion'] ?? 'Dirección'));
                                }
                            }
                            $direccionesFue = !empty($dirs) ? implode(' | ', $dirs) : (($visDir > 0 && $totDir > 0) ? ($visDir . ' de ' . $totDir) : '—');
                        } else {
                            $direccionesFue = ($visDir > 0 && $totDir > 0) ? ($visDir . ' de ' . $totDir) : '—';
                        }

                        $tipoRev = '';
                        if (!empty($detJson['ventana_revision']) && is_array($detJson['ventana_revision'])) {
                            $tipoRev = trim((string)($detJson['ventana_revision']['tipo'] ?? ''));
                        }
                        $tipoLeg = '';
                        if (!empty($detJson['ventana']) && is_array($detJson['ventana'])) {
                            $tipoLeg = trim((string)($detJson['ventana']['tipo'] ?? ''));
                        }
                        $segundaEnJson = array_key_exists('pago_en_ventana', $detJson)
                            && ($tipoRev === 'prorroga_12h' || $tipoRev === 'intensidad_12h' || $tipoLeg === 'prorroga_12h');
                        $inJ = isset($detJson['intensidad']) && is_array($detJson['intensidad']) ? $detJson['intensidad'] : [];
                        $prJ = isset($detJson['prorroga']) && is_array($detJson['prorroga']) ? $detJson['prorroga'] : [];
                        $intOt = !empty($inJ['otorgada']);
                        $prOt = !empty($prJ['otorgada']);
                        if ($intOt) {
                            $prorroga = true;
                            $extension12hTipo = 'Intensidad';
                            if ($segundaEnJson) {
                                $pagoProrroga = !empty($detJson['pago_en_ventana']);
                            } else {
                                $fOt = trim((string)($inJ['fecha_otorgada'] ?? ''));
                                $fLi = trim((string)($inJ['fecha_limite'] ?? ''));
                                if ($idCredito > 0 && $fOt !== '' && $fLi !== '') {
                                    $resPpr = self::getPagosEstadoCuentaEnVentana($idCredito, $fOt, $fLi, $optsReporteSemanal ?? []);
                                    $pagoProrroga = !empty($resPpr['pagos'] ?? []);
                                }
                            }
                        } elseif ($prOt) {
                            $prorroga = true;
                            $extension12hTipo = 'Prórroga';
                            if ($segundaEnJson) {
                                $pagoProrroga = !empty($detJson['pago_en_ventana']);
                            } else {
                                $fOt = trim((string)($prJ['fecha_otorgada'] ?? ''));
                                $fLi = trim((string)($prJ['fecha_limite'] ?? ''));
                                if ($idCredito > 0 && $fOt !== '' && $fLi !== '') {
                                    $resPpr = self::getPagosEstadoCuentaEnVentana($idCredito, $fOt, $fLi, $optsReporteSemanal ?? []);
                                    $pagoProrroga = !empty($resPpr['pagos'] ?? []);
                                }
                            }
                        } elseif ($res === 'cumplio_prorroga' || $res === 'no_cumplio_prorroga') {
                            $prorroga = true;
                            $extension12hTipo = ($tipoRev === 'intensidad_12h' || !empty($inJ['evaluada'])) ? 'Intensidad' : 'Prórroga';
                            if ($segundaEnJson) {
                                $pagoProrroga = !empty($detJson['pago_en_ventana']);
                            } elseif ($idCredito > 0) {
                                $fOt = '';
                                $fLi = '';
                                if ($extension12hTipo === 'Intensidad') {
                                    $fOt = trim((string)($inJ['fecha_otorgada'] ?? ''));
                                    $fLi = trim((string)($inJ['fecha_limite'] ?? ''));
                                }
                                if ($fOt === '' || $fLi === '') {
                                    $fOt = trim((string)($prJ['fecha_otorgada'] ?? ''));
                                    $fLi = trim((string)($prJ['fecha_limite'] ?? ''));
                                }
                                if ($fOt !== '' && $fLi !== '') {
                                    $resPpr = self::getPagosEstadoCuentaEnVentana($idCredito, $fOt, $fLi, $optsReporteSemanal ?? []);
                                    $pagoProrroga = !empty($resPpr['pagos'] ?? []);
                                }
                            }
                        }
                        if ($prorroga === true && $extension12hTipo === null && $segundaEnJson) {
                            $extension12hTipo = ($tipoRev === 'intensidad_12h') ? 'Intensidad' : 'Prórroga';
                        }
                    }
                }

                // Ilocalizable en reporte semanal:
                // (a) dictamen al gestor tipo Sabueso ILOCALIZABLE, o DS resultado dictamen_ilocalizable;
                // (b) regla operativa: visitó todas las direcciones y NO pagó en la semana (EC consultado).
                $esIlocalizableOperativo = ($fueDirecciones === true) && !empty($pagoSemana['consultado']) && empty($pagoSemana['si']);
                $esIlocalizablePorDictamen = self::esTipoDictamenIlocalizable((string)($r['dictamen_tipo_sabueso'] ?? ''));
                $esIlocalizablePorDs = ($res === 'dictamen_ilocalizable');
                $esIlocalizable = $esIlocalizableOperativo || $esIlocalizablePorDictamen || $esIlocalizablePorDs;

                $filas[] = [
                    'id_ticket' => $tid,
                    'folio' => (string)($r['folio'] ?? ''),
                    'id_credito' => $idCredito > 0 ? $idCredito : null,
                    'nombre_cliente' => $nombreCliente,
                    'id_gestor' => $idGestor > 0 ? $idGestor : null,
                    'nombre_gestor' => $nombreGestor,
                    'tipo_contacto' => $tipoContacto,
                    'dictamen_envio' => $r['dictamen_envio'] ?? null,
                    'fue_todas_direcciones' => $fueDirecciones,
                    'direcciones_fue' => $direccionesFue,
                    'pago_12h' => $pago12h,
                    'prorroga_si' => $prorroga,
                    'extension_12h_tipo' => $extension12hTipo,
                    'pago_prorroga_12h' => $pagoProrroga,
                    'pago_semana_si' => !empty($pagoSemana['si']),
                    'pago_semana_count' => (int)($pagoSemana['count'] ?? 0),
                    'pago_semana_consultado' => !empty($pagoSemana['consultado']),
                    'ilocalizable' => $esIlocalizable,
                    'ilocalizable_auto' => $esIlocalizable,
                    'ilocalizable_override' => false,
                ];
            }

            $resumen = [
                'total_tickets' => count($filas),
                'ilocalizable' => 0,
                'localizable' => 0,
                'pago_12h' => 0,
                'todas_direcciones' => 0,
                'prorroga' => 0,
                'pago_semana' => 0,
            ];
            foreach ($filas as $f) {
                if (!empty($f['ilocalizable'])) {
                    $resumen['ilocalizable']++;
                }
                if (!empty($f['pago_semana_consultado']) && empty($f['ilocalizable'])) {
                    $resumen['localizable']++;
                }
                if ($f['pago_12h'] === true) {
                    $resumen['pago_12h']++;
                }
                if ($f['fue_todas_direcciones'] === true) {
                    $resumen['todas_direcciones']++;
                }
                if ($f['prorroga_si'] === true) {
                    $resumen['prorroga']++;
                }
                if (!empty($f['pago_semana_si'])) {
                    $resumen['pago_semana']++;
                }
            }

            $payload = [
                'success' => true,
                'mensaje' => 'OK',
                'semana_inicio' => $semanaSelDt->format('Y-m-d'),
                'semana_fin' => $semanaSelDt->modify('+6 days')->format('Y-m-d'),
                'semanas' => $semanas,
                'resumen' => $resumen,
                'filas' => $filas,
            ];
            self::reporteSemanalGlobalArchivoEscribir($semanaSelDt->format('Y-m-d'), $payload);
            self::statsCacheWrite($cacheKey, $payload, 300);
            return $payload;
        } catch (\Throwable $e) {
            error_log('getReporteSemanalGestorGlobal error: ' . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => $e->getMessage(),
                'semana_inicio' => '',
                'semana_fin' => '',
                'semanas' => [],
                'resumen' => [],
                'filas' => [],
            ];
        } finally {
            self::$reporteSemanalEcPreload = null;
        }
    }

    /**
     * Reconsulta estado de cuenta para un solo crédito/ticket en el contexto del reporte semanal
     * (evita el límite global max_api_calls del reporte masivo). Válido aunque el ticket esté cerrado.
     */
    public static function reconsultarPagoSemanaReporteSemanal(int $idTicket, string $semanaInicio = ''): array
    {
        $db = new Database();
        $whereReporteTicket = "NOT EXISTS (
            SELECT 1
            FROM ticket_historico he
            WHERE he.id_ticket = t.id_ticket
              AND he.tipo_accion = 'eliminado'
              AND he.fecha_eliminacion = (
                  SELECT MAX(hx.fecha_eliminacion)
                  FROM ticket_historico hx
                  WHERE hx.id_ticket = t.id_ticket
              )
        )";
        if ($idTicket < 1) {
            return ['success' => false, 'mensaje' => 'id_ticket inválido'];
        }
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = self::cdmxNowImmutable();
            $dow = (int)$now->format('N');
            $mondayCurrent = $now->modify('-' . ($dow - 1) . ' days')->setTime(0, 0, 0);
            $mondayLastClosed = $mondayCurrent->modify('-7 days');

            $semanaSelDt = $mondayLastClosed;
            if ($semanaInicio !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $semanaInicio)) {
                $tmp = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $semanaInicio . ' 00:00:00', $tz);
                if ($tmp instanceof \DateTimeImmutable && $tmp < $mondayCurrent) {
                    $semanaSelDt = $tmp;
                }
            }

            $semanaSelInicio = $semanaSelDt->format('Y-m-d') . ' 00:00:00';
            $semanaSelFinExcl = $semanaSelDt->modify('+7 days')->format('Y-m-d') . ' 00:00:00';
            $semanaSelFinIncl = $semanaSelDt->modify('+6 days')->format('Y-m-d') . ' 23:59:59';

            $row = $db->queryOne(
                "SELECT t.id_ticket, t.id_credito, d.tipo AS dictamen_tipo_sabueso " .
                "FROM ticket t " .
                "INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' " .
                "INNER JOIN (SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket) dm " .
                "ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx " .
                "WHERE t.id_ticket = :tid AND $whereReporteTicket " .
                "AND d.fecha_actualizacion >= :fi AND d.fecha_actualizacion < :ff",
                ['tid' => $idTicket, 'fi' => $semanaSelInicio, 'ff' => $semanaSelFinExcl]
            );
            if (!is_array($row) || empty($row['id_ticket'])) {
                return [
                    'success' => false,
                    'mensaje' => 'El ticket no aparece en el reporte de esa semana (dictamen al gestor fuera del rango o ticket eliminado).',
                ];
            }
            $idCredito = (int)($row['id_credito'] ?? 0);
            if ($idCredito < 1) {
                return ['success' => false, 'mensaje' => 'El ticket no tiene crédito asociado.'];
            }

            $cacheKeyEC = '__SPARTA_SECRET_REDACTED___pagos:' . $idCredito . ':' . self::fechaCdmx();
            self::statsCacheDelete($cacheKeyEC);

            $optsUnCredito = [
                'timeout_segundos' => 15,
                'max_api_calls' => 50,
                'cache_ttl_segundos' => 604800,
            ];
            $resPs = self::getPagosEstadoCuentaEnVentana($idCredito, $semanaSelInicio, $semanaSelFinIncl, $optsUnCredito);
            $pagoSemana = [
                'si' => !empty($resPs['pagos'] ?? []),
                'count' => is_array($resPs['pagos'] ?? null) ? count($resPs['pagos']) : 0,
                'consultado' => !empty($resPs['__SPARTA_SECRET_REDACTED___consultado']),
            ];

            $fueDirecciones = null;
            $resDs = '';
            $dsRow = $db->queryOne(
                "SELECT ds1.resultado, ds1.detalle FROM dictamen_sistema ds1 " .
                "INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen_sistema WHERE id_ticket = :tid GROUP BY id_ticket) dsmx " .
                "ON ds1.id_ticket = dsmx.id_ticket AND ds1.id = dsmx.mid",
                ['tid' => $idTicket]
            );
            if (is_array($dsRow) && $dsRow !== []) {
                $resDs = trim((string)($dsRow['resultado'] ?? ''));
                $detJson = !empty($dsRow['detalle']) ? json_decode((string)$dsRow['detalle'], true) : null;
                if (is_array($detJson)) {
                    $totDir = (int)($detJson['direcciones_dictamen_total'] ?? 0);
                    $visDir = (int)($detJson['direcciones_visitadas'] ?? 0);
                    if (array_key_exists('visito_todas_direcciones', $detJson)) {
                        $fueDirecciones = !empty($detJson['visito_todas_direcciones']);
                    } elseif ($totDir > 0) {
                        $fueDirecciones = ($visDir === $totDir);
                    }
                }
            }

            $esIlocalizableOperativo = ($fueDirecciones === true) && !empty($pagoSemana['consultado']) && empty($pagoSemana['si']);
            $esIlocalizablePorDictamen = self::esTipoDictamenIlocalizable((string)($row['dictamen_tipo_sabueso'] ?? ''));
            $esIlocalizablePorDs = ($resDs === 'dictamen_ilocalizable');
            $esIlocalizable = $esIlocalizableOperativo || $esIlocalizablePorDictamen || $esIlocalizablePorDs;

            $cacheKeyReporte = 'reporte_semanal_global:v3:' . $semanaSelDt->format('Y-m-d');
            self::statsCacheDelete($cacheKeyReporte);

            self::reporteSemanalGlobalArchivoMergeReconsulta(
                $semanaSelDt->format('Y-m-d'),
                $idTicket,
                !empty($pagoSemana['si']),
                (int)$pagoSemana['count'],
                !empty($pagoSemana['consultado']),
                $esIlocalizable
            );

            $filaFinal = null;
            $pathRep = self::reporteSemanalGlobalArchivoPath($semanaSelDt->format('Y-m-d'));
            $rawRep = @file_get_contents($pathRep);
            if (is_string($rawRep) && $rawRep !== '') {
                $dataRep = json_decode($rawRep, true);
                if (is_array($dataRep) && !empty($dataRep['filas']) && is_array($dataRep['filas'])) {
                    foreach ($dataRep['filas'] as $fr) {
                        if ((int)($fr['id_ticket'] ?? 0) === $idTicket) {
                            $filaFinal = $fr;
                            break;
                        }
                    }
                }
            }
            $ilEff = $filaFinal !== null ? !empty($filaFinal['ilocalizable']) : $esIlocalizable;
            $ilAut = $filaFinal !== null && array_key_exists('ilocalizable_auto', $filaFinal)
                ? !empty($filaFinal['ilocalizable_auto'])
                : $esIlocalizable;
            $ilOv = $filaFinal !== null && !empty($filaFinal['ilocalizable_override']);

            return [
                'success' => true,
                'mensaje' => 'OK',
                'id_ticket' => $idTicket,
                'id_credito' => $idCredito,
                'semana_inicio' => $semanaSelDt->format('Y-m-d'),
                'pago_semana_si' => !empty($pagoSemana['si']),
                'pago_semana_count' => (int)$pagoSemana['count'],
                'pago_semana_consultado' => !empty($pagoSemana['consultado']),
                'ilocalizable' => $ilEff,
                'ilocalizable_auto' => $ilAut,
                'ilocalizable_override' => $ilOv,
            ];
        } catch (\Throwable $e) {
            error_log('reconsultarPagoSemanaReporteSemanal error: ' . $e->getMessage());
            return ['success' => false, 'mensaje' => $e->getMessage()];
        }
    }

    /**
     * Prefijo de folio para crearTicketSimple (solo letras A–Z, máx. 6; por defecto TCK).
     */
    private static function normalizarPrefijoFolio($valor): string
    {
        $s = strtoupper(preg_replace('/[^A-Z]/', '', (string) $valor));
        if ($s === '' || strlen($s) > 6) {
            return 'TCK';
        }

        return $s;
    }

    /**
     * Interpreta fecha_vencimiento opcional: datetime completo, Y-m-d o d/m/Y (fin de día 23:59:59 CDMX).
     */
    private static function resolverFechaVencimientoTicketOpcional($valor): ?string
    {
        if ($valor === null) {
            return null;
        }
        $s = trim((string) $valor);
        if ($s === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $s)) {
            return $s;
        }
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $s, $m)) {
            $y = (int) $m[1];
            $mo = (int) $m[2];
            $d = (int) $m[3];
            if (!checkdate($mo, $d, $y)) {
                return null;
            }

            return sprintf('%04d-%02d-%02d 23:59:59', $y, $mo, $d);
        }
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $s, $m)) {
            $d = (int) $m[1];
            $mo = (int) $m[2];
            $y = (int) $m[3];
            if (!checkdate($mo, $d, $y)) {
                return null;
            }

            return sprintf('%04d-%02d-%02d 23:59:59', $y, $mo, $d);
        }

        return null;
    }

    /**
     * Hora de referencia en CDMX (no usar CURDATE()/NOW() de MySQL si el servidor está en otra TZ).
     * Todo lo que sea "hoy", "esta semana", etc. en estadísticas/dictamen debe basarse aquí.
     */
    private static function cdmxNowImmutable(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
    }

    public static function ahoraCdmx(): string
    {
        return self::cdmxNowImmutable()->format('Y-m-d H:i:s');
    }

    public static function fechaCdmx(): string
    {
        return self::cdmxNowImmutable()->format('Y-m-d');
    }

    /** Lunes 00:00:00 de la semana actual en CDMX (N=1..7) */
    public static function inicioSemanaLunesCdmx(): string
    {
        $now = self::cdmxNowImmutable();
        $dow = (int)$now->format('N'); // 1 = lunes
        $monday = $now->modify('-' . ($dow - 1) . ' days');
        return $monday->format('Y-m-d') . ' 00:00:00';
    }

    /**
     * Rango de la semana en curso para UI (misma lógica que estadísticas lun→hoy CDMX): dd/mm/aaaa - dd/mm/aaaa.
     */
    public static function semanaActualPeriodoUiCdmx(): string
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $lun = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', self::inicioSemanaLunesCdmx(), $tz);
        $hoy = \DateTimeImmutable::createFromFormat('Y-m-d', self::fechaCdmx(), $tz);
        if (!$lun instanceof \DateTimeImmutable || !$hoy instanceof \DateTimeImmutable) {
            return '';
        }

        return $lun->format('d/m/Y') . ' - ' . $hoy->format('d/m/Y');
    }

    /**
     * Drill-down Tickets levantados: Año → meses → semanas del mes → 7 días de la semana (lunes dado).
     * tipo: meses | semanas | dias
     */
    public static function getEstadisticasLevantadosDrill(string $tipo, array $params = []): array
    {
        $db = new Database();
        $whereLevantadosHist = self::whereTicketLevantadosHistoricoSql() . self::andTicketSoloSabuesoSql($db);
        $fechaCreacionValida = 't.fecha_creacion IS NOT NULL AND DATE(t.fecha_creacion) >= \'2001-01-01\'';
        $out = ['success' => true, 'mensaje' => 'OK', 'tipo' => $tipo, 'filas' => []];

        try {
            if ($tipo === 'meses') {
                $anio = (int)($params['anio'] ?? 0);
                if ($anio < 2000 || $anio > 2100) {
                    $out['success'] = false;
                    $out['mensaje'] = 'Año inválido';
                    return $out;
                }
                $rows = $db->queryAll(
                    "SELECT DATE_FORMAT(t.fecha_creacion, '%Y-%m') AS periodo, COUNT(*) AS n FROM ticket t " .
                    "WHERE $whereLevantadosHist AND $fechaCreacionValida AND YEAR(t.fecha_creacion) = " . $anio . " " .
                    "GROUP BY DATE_FORMAT(t.fecha_creacion, '%Y-%m') ORDER BY periodo DESC"
                );
                $out['filas'] = is_array($rows) ? $rows : [];
                return $out;
            }

            if ($tipo === 'semanas') {
                $anio = (int)($params['anio'] ?? 0);
                $mes = (int)($params['mes'] ?? 0);
                if ($anio < 2000 || $anio > 2100 || $mes < 1 || $mes > 12) {
                    $out['success'] = false;
                    $out['mensaje'] = 'Año o mes inválido';
                    return $out;
                }
                $rows = $db->queryAll(
                    "SELECT YEARWEEK(t.fecha_creacion, 1) AS periodo, COUNT(*) AS n FROM ticket t " .
                    "WHERE $whereLevantadosHist AND $fechaCreacionValida AND YEAR(t.fecha_creacion) = " . $anio . " AND MONTH(t.fecha_creacion) = " . $mes . " " .
                    "GROUP BY YEARWEEK(t.fecha_creacion, 1) ORDER BY periodo DESC"
                );
                $filas = [];
                foreach (is_array($rows) ? $rows : [] as $r) {
                    $yw = (int)($r['periodo'] ?? 0);
                    $n = (int)($r['n'] ?? 0);
                    $lunes = '';
                    if ($yw >= 200001) {
                        $yy = (int)floor($yw / 100);
                        $ww = (int)($yw % 100);
                        if ($ww >= 1 && $ww <= 53) {
                            try {
                                $lunes = (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))
                                    ->setISODate($yy, $ww)->format('Y-m-d');
                            } catch (\Exception $e) {
                                $lunes = '';
                            }
                        }
                    }
                    $filas[] = ['periodo' => $yw, 'n' => $n, 'lunes' => $lunes];
                }
                $out['filas'] = $filas;
                return $out;
            }

            if ($tipo === 'dias') {
                $lunes = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($params['lunes'] ?? '')) ? $params['lunes'] : '';
                if ($lunes === '') {
                    $out['success'] = false;
                    $out['mensaje'] = 'Fecha lunes inválida (use YYYY-MM-DD)';
                    return $out;
                }
                $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $lunes, new \DateTimeZone('America/Mexico_City'));
                if (!$dt || $dt->format('Y-m-d') !== $lunes) {
                    $out['success'] = false;
                    $out['mensaje'] = 'Fecha lunes inválida';
                    return $out;
                }
                $domingo = $dt->modify('+6 days')->format('Y-m-d');
                $rows = $db->queryAll(
                    "SELECT DATE(t.fecha_creacion) AS periodo, COUNT(*) AS n FROM ticket t " .
                    "WHERE $whereLevantadosHist AND $fechaCreacionValida AND DATE(t.fecha_creacion) >= '" . $lunes . "' " .
                    "AND DATE(t.fecha_creacion) <= '" . $domingo . "' " .
                    "GROUP BY DATE(t.fecha_creacion) ORDER BY periodo DESC"
                );
                $out['filas'] = is_array($rows) ? $rows : [];
                $out['lunes'] = $lunes;
                $out['domingo'] = $domingo;
                return $out;
            }

            $out['success'] = false;
            $out['mensaje'] = 'tipo debe ser meses, semanas o dias';
        } catch (\Exception $e) {
            $out['success'] = false;
            $out['mensaje'] = $e->getMessage();
        }
        return $out;
    }

    /**
     * Detalle de tickets levantados en una fecha (para modal en Analítica sabueso).
     * Fecha en YYYY-MM-DD (CDMX). Devuelve filas con: folio, id_credito, gestor, hora levantado,
     * tiempo dictamen enviado, cuando abrieron, tiempo apertura, resultado DS, prórroga, pagaron, cumplimiento.
     */
    public static function getTicketsDetallePorDia(string $fecha): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return ['success' => false, 'mensaje' => 'Fecha inválida (YYYY-MM-DD)', 'filas' => []];
        }
        $db = new Database();
        $whereLevantadosHist = self::whereTicketLevantadosHistoricoSql() . self::andTicketSoloSabuesoSql($db);
        $fechaCreacionValida = 't.fecha_creacion IS NOT NULL AND DATE(t.fecha_creacion) >= \'2001-01-01\'';
        try {
            $sql = "SELECT t.id_ticket, t.folio, t.id_credito, t.fecha_creacion, " .
                "CONCAT(TRIM(IFNULL(p.nombres,'')), ' ', TRIM(IFNULL(p.apellidop,''))) AS creador_nombre, " .
                "dm.fecha_actualizacion AS dictamen_fecha_envio, dm.fecha_visto_gestor AS dictamen_fecha_visto, " .
                "dsm.resultado AS ds_resultado, dsm.detalle AS ds_detalle " .
                "FROM ticket t " .
                "INNER JOIN persona p ON t.id_persona_creador = p.id " .
                "LEFT JOIN (SELECT d.id_ticket, d.fecha_actualizacion, d.fecha_visto_gestor FROM dictamen d " .
                "INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket) mx ON d.id_ticket = mx.id_ticket AND d.id = mx.mid) dm ON dm.id_ticket = t.id_ticket " .
                "LEFT JOIN (SELECT ds1.id_ticket, ds1.resultado, ds1.detalle FROM dictamen_sistema ds1 " .
                "INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen_sistema GROUP BY id_ticket) dsmx ON ds1.id_ticket = dsmx.id_ticket AND ds1.id = dsmx.mid) dsm ON dsm.id_ticket = t.id_ticket " .
                "WHERE $whereLevantadosHist AND $fechaCreacionValida AND DATE(t.fecha_creacion) = :fecha ORDER BY t.fecha_creacion ASC";
            $rows = $db->queryAll($sql, ['fecha' => $fecha]);
            $filas = [];
            foreach (is_array($rows) ? $rows : [] as $r) {
                $fc = $r['fecha_creacion'] ?? null;
                $horaLevantado = $fc ? (new \DateTimeImmutable($fc, new \DateTimeZone('America/Mexico_City')))->format('H:i') : '—';
                $fEnv = $r['dictamen_fecha_envio'] ?? null;
                $fVisto = $r['dictamen_fecha_visto'] ?? null;
                $tiempoEnvio = '—';
                if ($fc && $fEnv) {
                    $seg = (new \DateTimeImmutable($fEnv))->getTimestamp() - (new \DateTimeImmutable($fc))->getTimestamp();
                    $tiempoEnvio = $seg >= 0 ? self::segundosAHumano((int)$seg) : '—';
                }
                $cuandoAbrieron = $fVisto ? (new \DateTimeImmutable($fVisto, new \DateTimeZone('America/Mexico_City')))->format('H:i') : '—';
                $tiempoApertura = '—';
                if ($fEnv && $fVisto) {
                    $segAp = (new \DateTimeImmutable($fVisto))->getTimestamp() - (new \DateTimeImmutable($fEnv))->getTimestamp();
                    $tiempoApertura = $segAp >= 0 ? self::segundosAHumano((int)$segAp) : '—';
                }
                $det = !empty($r['ds_detalle']) ? json_decode($r['ds_detalle'], true) : null;
                $prorroga = '—';
                $pagaron = '—';
                $cumplimiento = '—';
                if (is_array($det)) {
                    $tipoRevD = '';
                    if (!empty($det['ventana_revision']) && is_array($det['ventana_revision'])) {
                        $tipoRevD = trim((string)($det['ventana_revision']['tipo'] ?? ''));
                    }
                    $tipoLegD = '';
                    if (!empty($det['ventana']) && is_array($det['ventana'])) {
                        $tipoLegD = trim((string)($det['ventana']['tipo'] ?? ''));
                    }
                    $segundaDet = array_key_exists('pago_en_ventana', $det)
                        && ($tipoRevD === 'prorroga_12h' || $tipoRevD === 'intensidad_12h' || $tipoLegD === 'prorroga_12h');
                    $inDet = isset($det['intensidad']) && is_array($det['intensidad']) ? $det['intensidad'] : [];
                    $prDet = isset($det['prorroga']) && is_array($det['prorroga']) ? $det['prorroga'] : [];
                    if (!empty($inDet['otorgada'])) {
                        $prorroga = 'Intensidad';
                    } elseif (!empty($prDet['otorgada'])) {
                        $prorroga = 'Prórroga';
                    } elseif ($segundaDet) {
                        $prorroga = ($tipoRevD === 'intensidad_12h') ? 'Intensidad' : 'Prórroga';
                    }
                    if (!empty($det['evaluacion_visitas_pago_no_aplica']) || !empty($det['pago_evaluacion_no_aplica'])) {
                        $pagaron = '—';
                    } elseif (array_key_exists('pago_en_ventana', $det)) {
                        $consultado = !array_key_exists('__SPARTA_SECRET_REDACTED___consultado', $det) || !empty($det['__SPARTA_SECRET_REDACTED___consultado']);
                        $pagaron = !empty($det['pago_en_ventana']) ? 'Sí' : ($consultado ? 'No' : 'No se pudo verificar');
                    }
                    if (!empty($det['cumplimiento_etiqueta'])) {
                        $cumplimiento = (string)$det['cumplimiento_etiqueta'];
                    }
                }
                $resDs = trim((string)($r['ds_resultado'] ?? ''));
                if ($resDs !== '' && $cumplimiento === '—') {
                    $cmp = self::cumplimientoMetadatos($resDs);
                    $cumplimiento = $cmp['cumplimiento_etiqueta'] ?? $resDs;
                }
                if ($prorroga === '—' && ($resDs === 'cumplio_prorroga' || $resDs === 'no_cumplio_prorroga') && is_array($det)) {
                    $tipoRevPost = '';
                    if (!empty($det['ventana_revision']) && is_array($det['ventana_revision'])) {
                        $tipoRevPost = trim((string)($det['ventana_revision']['tipo'] ?? ''));
                    }
                    $inPost = isset($det['intensidad']) && is_array($det['intensidad']) ? $det['intensidad'] : [];
                    $prorroga = ($tipoRevPost === 'intensidad_12h' || !empty($inPost['evaluada'])) ? 'Intensidad' : 'Prórroga';
                }
                $filas[] = [
                    'id_ticket' => (int)($r['id_ticket'] ?? 0),
                    'folio' => trim((string)($r['folio'] ?? '')),
                    'id_credito' => (int)($r['id_credito'] ?? 0) ?: null,
                    'gestor_nombre' => trim((string)($r['creador_nombre'] ?? '')),
                    'hora_levantado' => $horaLevantado,
                    'tiempo_dictamen_enviado' => $tiempoEnvio,
                    'cuando_abrieron' => $cuandoAbrieron,
                    'tiempo_apertura' => $tiempoApertura,
                    'resultado_ds' => $resDs !== '' ? $resDs : '—',
                    'prorroga' => $prorroga,
                    'pagaron' => $pagaron,
                    'cumplimiento' => $cumplimiento,
                ];
            }
            return ['success' => true, 'mensaje' => 'OK', 'fecha' => $fecha, 'filas' => $filas];
        } catch (\Exception $e) {
            return ['success' => false, 'mensaje' => $e->getMessage(), 'filas' => []];
        }
    }

    /**
     * Solo agregado Por Sabueso (dictaminó) + cola — sin el resto de estadísticas.
     * Para cambiar Días/Semanas/… sin recalcular todo.
     *
     * @param bool $runBackfill si false, no hace UPDATE masivo (más rápido al cambiar filtro)
     */
    public static function getEstadisticasPorSabuesoSolo(string $periodoSabueso, bool $runBackfill = false): array
    {
        if (!in_array($periodoSabueso, ['por_dia', 'por_semana', 'por_mes', 'por_anio'], true)) {
            $periodoSabueso = 'por_dia';
        }
        $db = new Database();
        $whereActivo = '(t.activo = 1 OR t.activo IS NULL) AND (t.fecha_eliminacion IS NULL)';
        $whereActivo .= self::andTicketSoloSabuesoSql($db);
        $fechaCdmx = self::fechaCdmx();
        $lunesCdmx = self::inicioSemanaLunesCdmx();
        $y = self::cdmxNowImmutable()->format('Y');
        $m = self::cdmxNowImmutable()->format('m');
        $fecha90 = self::cdmxNowImmutable()->modify('-90 days')->format('Y-m-d');

        $wherePorSabuesoFecha = '';
        if ($periodoSabueso === 'por_dia') {
            $wherePorSabuesoFecha = " AND DATE(d.fecha_actualizacion) = '" . $fechaCdmx . "' ";
        } elseif ($periodoSabueso === 'por_semana') {
            $wherePorSabuesoFecha = " AND d.fecha_actualizacion >= '" . $lunesCdmx . "' ";
        } elseif ($periodoSabueso === 'por_mes') {
            $wherePorSabuesoFecha = " AND YEAR(d.fecha_actualizacion) = " . (int)$y . " AND MONTH(d.fecha_actualizacion) = " . (int)$m . " ";
        } else {
            $wherePorSabuesoFecha = ' AND YEAR(d.fecha_actualizacion) = ' . (int)$y . ' ';
        }

        if ($runBackfill) {
            try {
                $db->CRUD(
                    "UPDATE dictamen d " .
                    "INNER JOIN ( " .
                    "  SELECT d2.id AS did, (SELECT at2.id_persona_asignada FROM asignacion_ticket at2 " .
                    "    WHERE at2.id_ticket = d2.id_ticket AND at2.fecha_asignacion <= d2.fecha_actualizacion " .
                    "    ORDER BY at2.fecha_asignacion DESC LIMIT 1) AS pid " .
                    "  FROM dictamen d2 " .
                    "  WHERE d2.estado = 'enviado_al_gestor' AND (d2.id_persona IS NULL OR d2.id_persona = 0) " .
                    "  AND d2.fecha_actualizacion >= '" . $fecha90 . "' " .
                    ") x ON x.did = d.id AND x.pid IS NOT NULL " .
                    "SET d.id_persona = x.pid"
                );
            } catch (\Exception $e) {
                // ignorar
            }
        }

        $listaSab = [];
        try {
            $sqlSab = "
                SELECT d.id_persona AS id_persona,
                       CONCAT(TRIM(IFNULL(p.nombres,'')),' ',TRIM(IFNULL(p.apellidop,''))) AS nombre,
                       COUNT(*) AS dictaminados,
                       AVG(
                         CASE WHEN (
                           SELECT MAX(at2.fecha_asignacion) FROM asignacion_ticket at2
                           WHERE at2.id_ticket = d.id_ticket AND at2.id_persona_asignada = d.id_persona
                             AND at2.fecha_asignacion <= d.fecha_actualizacion
                         ) IS NOT NULL THEN
                           TIMESTAMPDIFF(SECOND,
                             (SELECT MAX(at2.fecha_asignacion) FROM asignacion_ticket at2
                              WHERE at2.id_ticket = d.id_ticket AND at2.id_persona_asignada = d.id_persona
                                AND at2.fecha_asignacion <= d.fecha_actualizacion),
                             d.fecha_actualizacion)
                         ELSE NULL END
                       ) AS avg_sec_asignado_a_envio,
                       SUM(CASE WHEN (
                         SELECT MAX(at2.fecha_asignacion) FROM asignacion_ticket at2
                         WHERE at2.id_ticket = d.id_ticket AND at2.id_persona_asignada = d.id_persona
                           AND at2.fecha_asignacion <= d.fecha_actualizacion
                       ) IS NOT NULL THEN 1 ELSE 0 END) AS n_con_asignacion_previa
                FROM dictamen d
                INNER JOIN (
                    SELECT id_ticket, MAX(fecha_actualizacion) AS mx
                    FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket
                ) dm ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor'
                INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                INNER JOIN persona p ON p.id = d.id_persona
                WHERE d.id_persona IS NOT NULL AND d.id_persona > 0
                $wherePorSabuesoFecha
                GROUP BY d.id_persona, p.nombres, p.apellidop
                ORDER BY dictaminados DESC
            ";
            $rows = $db->queryAll($sqlSab);
            foreach (is_array($rows) ? $rows : [] as $r) {
                $avgSec = isset($r['avg_sec_asignado_a_envio']) && $r['avg_sec_asignado_a_envio'] !== null
                    ? (int)round((float)$r['avg_sec_asignado_a_envio']) : null;
                $listaSab[] = [
                    'id_persona' => (int)($r['id_persona'] ?? 0),
                    'nombre' => trim((string)($r['nombre'] ?? '')),
                    'dictaminados' => (int)($r['dictaminados'] ?? 0),
                    'tiempo_asignado_a_envio_humano' => $avgSec !== null ? self::segundosAHumano($avgSec) : '—',
                    'tiempo_asignado_a_envio_seg' => $avgSec,
                    'muestras_con_asignacion' => (int)($r['n_con_asignacion_previa'] ?? 0),
                ];
            }
        } catch (\Exception $e) {
            $listaSab = [];
        }

        // Cola (misma lógica que getEstadisticasTickets; persona para GROUP BY seguro)
        try {
            $sqlCola = "
                SELECT at.id_persona_asignada AS id_persona,
                       COUNT(*) AS n,
                       AVG(TIMESTAMPDIFF(SECOND, t.fecha_creacion, at.primera_fa)) AS avg_sec
                FROM (
                    SELECT id_ticket, id_persona_asignada, MIN(fecha_asignacion) AS primera_fa
                    FROM asignacion_ticket
                    WHERE (activo = 1 OR activo IS NULL)
                    GROUP BY id_ticket, id_persona_asignada
                ) at
                INNER JOIN ticket t ON t.id_ticket = at.id_ticket AND $whereActivo
                INNER JOIN persona p ON p.id = at.id_persona_asignada
                WHERE at.primera_fa >= t.fecha_creacion
                GROUP BY at.id_persona_asignada, p.nombres, p.apellidop
                HAVING n >= 1
            ";
            $rows = $db->queryAll($sqlCola);
            $mapCola = [];
            foreach (is_array($rows) ? $rows : [] as $r) {
                $idp = (int)($r['id_persona'] ?? 0);
                if ($idp < 1) {
                    continue;
                }
                $avgSec = isset($r['avg_sec']) && $r['avg_sec'] !== null ? (int)round((float)$r['avg_sec']) : null;
                $mapCola[$idp] = [
                    'tiempo_hasta_asignarle_humano' => $avgSec !== null ? self::segundosAHumano($avgSec) : '—',
                    'tiempo_hasta_asignarle_seg' => $avgSec,
                    'muestras_cola' => (int)($r['n'] ?? 0),
                ];
            }
            foreach ($listaSab as &$s) {
                $idp = (int)($s['id_persona'] ?? 0);
                if ($idp > 0 && isset($mapCola[$idp])) {
                    $s['tiempo_hasta_asignarle_humano'] = $mapCola[$idp]['tiempo_hasta_asignarle_humano'];
                    $s['tiempo_hasta_asignarle_seg'] = $mapCola[$idp]['tiempo_hasta_asignarle_seg'];
                    $s['muestras_cola'] = $mapCola[$idp]['muestras_cola'];
                } else {
                    $s['tiempo_hasta_asignarle_humano'] = '—';
                    $s['tiempo_hasta_asignarle_seg'] = null;
                    $s['muestras_cola'] = 0;
                }
            }
            unset($s);
        } catch (\Exception $e) {
            // sin cola
        }

        return [
            'success' => true,
            'por_sabueso' => $listaSab,
            'periodo_sabueso' => $periodoSabueso,
            'cdmx_referencia' => self::ahoraCdmx(),
        ];
    }

    /**
     * Promedios de tiempos dictamen (Sabueso / gestor) agrupados por semana natural (lun CDMX según DATE del envío).
     * Incluye tickets ya cerrados si el dictamen se envió/vio en esa semana (misma base que «levantados» histórico:
     * excluye solo tickets cuyo último ticket_historico sea eliminado). Así el histórico semanal no queda vacío al cerrar tickets.
     * (Los KPI «semana actual» de tiempos_sabueso / tiempos_gestor siguen usando solo tickets en flujo.)
     *
     * @return list<array{lunes: string, sabueso: array|null, gestor: array|null}>
     */
    private static function buildTiemposDictamenPorSemanaSeries(Database $db, int $limite = 24): array
    {
        $limite = max(4, min(52, $limite));
        $joinTicketHistorico = '(' . self::whereTicketLevantadosHistoricoSql() . ')' . self::andTicketSoloSabuesoSql($db);
        $porSab = [];
        try {
            $sqlSab = "
                SELECT DATE_SUB(DATE(x.fa), INTERVAL WEEKDAY(DATE(x.fa)) DAY) AS lunes,
                       AVG(TIMESTAMPDIFF(SECOND, x.fa_before, x.fa)) AS avg_sec,
                       MIN(TIMESTAMPDIFF(SECOND, x.fa_before, x.fa)) AS min_sec,
                       MAX(TIMESTAMPDIFF(SECOND, x.fa_before, x.fa)) AS max_sec,
                       COUNT(*) AS n
                FROM (
                    SELECT d.id_ticket, d.fecha_actualizacion AS fa,
                           (SELECT MAX(at2.fecha_asignacion) FROM asignacion_ticket at2
                            WHERE at2.id_ticket = d.id_ticket
                              AND at2.fecha_asignacion <= d.fecha_actualizacion
                              AND (at2.activo = 1 OR at2.activo IS NULL)
                           ) AS fa_before
                    FROM dictamen d
                    INNER JOIN (
                        SELECT id_ticket, MAX(fecha_actualizacion) AS mx
                        FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket
                    ) dm ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor'
                    INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $joinTicketHistorico
                    WHERE d.fecha_actualizacion IS NOT NULL
                ) x
                WHERE x.fa_before IS NOT NULL
                  AND x.fa_before <= x.fa
                  AND TIMESTAMPDIFF(SECOND, x.fa_before, x.fa) >= 0
                  AND TIMESTAMPDIFF(SECOND, x.fa_before, x.fa) <= 604800
                GROUP BY DATE_SUB(DATE(x.fa), INTERVAL WEEKDAY(DATE(x.fa)) DAY)
                ORDER BY lunes DESC
                LIMIT " . $limite;
            $rows = $db->queryAll($sqlSab);
            foreach (is_array($rows) ? $rows : [] as $r) {
                $lunes = trim((string)($r['lunes'] ?? ''));
                if ($lunes === '' || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $lunes)) {
                    continue;
                }
                $n = (int)($r['n'] ?? 0);
                if ($n < 1) {
                    continue;
                }
                $avg = (float)($r['avg_sec'] ?? 0);
                $porSab[$lunes] = [
                    'muestras' => $n,
                    'promedio_seg' => (int)round($avg),
                    'min_seg' => (int)($r['min_sec'] ?? 0),
                    'max_seg' => (int)($r['max_sec'] ?? 0),
                    'promedio_humano' => self::segundosAHumano((int)round($avg)),
                ];
            }
        } catch (\Exception $e) {
            $porSab = [];
        }
        $porGest = [];
        try {
            $sqlGest = "
                SELECT DATE_SUB(DATE(d.fecha_actualizacion), INTERVAL WEEKDAY(DATE(d.fecha_actualizacion)) DAY) AS lunes,
                       AVG(TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor)) AS avg_sec,
                       MIN(TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor)) AS min_sec,
                       MAX(TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor)) AS max_sec,
                       COUNT(*) AS n
                FROM dictamen d
                INNER JOIN (
                    SELECT id_ticket, MAX(fecha_actualizacion) AS mx
                    FROM dictamen
                    WHERE estado = 'enviado_al_gestor' AND fecha_visto_gestor IS NOT NULL
                    GROUP BY id_ticket
                ) dm ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx AND d.estado = 'enviado_al_gestor'
                INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $joinTicketHistorico
                WHERE d.estado = 'enviado_al_gestor'
                  AND d.fecha_actualizacion IS NOT NULL
                  AND d.fecha_visto_gestor IS NOT NULL
                  AND d.fecha_visto_gestor >= d.fecha_actualizacion
                  AND TIMESTAMPDIFF(SECOND, d.fecha_actualizacion, d.fecha_visto_gestor) <= 604800
                GROUP BY DATE_SUB(DATE(d.fecha_actualizacion), INTERVAL WEEKDAY(DATE(d.fecha_actualizacion)) DAY)
                ORDER BY lunes DESC
                LIMIT " . $limite;
            $rowsG = $db->queryAll($sqlGest);
            foreach (is_array($rowsG) ? $rowsG : [] as $r) {
                $lunes = trim((string)($r['lunes'] ?? ''));
                if ($lunes === '' || !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $lunes)) {
                    continue;
                }
                $n = (int)($r['n'] ?? 0);
                if ($n < 1) {
                    continue;
                }
                $avg = (float)($r['avg_sec'] ?? 0);
                $porGest[$lunes] = [
                    'muestras' => $n,
                    'promedio_seg' => (int)round($avg),
                    'min_seg' => (int)($r['min_sec'] ?? 0),
                    'max_seg' => (int)($r['max_sec'] ?? 0),
                    'promedio_humano' => self::segundosAHumano((int)round($avg)),
                ];
            }
        } catch (\Exception $e) {
            $porGest = [];
        }
        $todas = array_unique(array_merge(array_keys($porSab), array_keys($porGest)));
        rsort($todas, SORT_STRING);
        $todas = array_slice($todas, 0, $limite);
        $salida = [];
        foreach ($todas as $lunes) {
            $salida[] = [
                'lunes' => $lunes,
                'sabueso' => $porSab[$lunes] ?? null,
                'gestor' => $porGest[$lunes] ?? null,
            ];
        }

        return $salida;
    }

    /**
     * Predicado SQL (sin AND inicial) para estadísticas de tickets levantados en el tiempo:
     * cuenta cerrados y activos; excluye solo tickets cuyo último registro en ticket_historico sea eliminado.
     * (Cerrar pone activo=0 y fecha_eliminacion en ticket, igual que eliminar; el historial distingue cerrado vs eliminado.)
     */
    private static function whereTicketLevantadosHistoricoSql(): string
    {
        return 'NOT EXISTS (
            SELECT 1
            FROM ticket_historico he
            WHERE he.id_ticket = t.id_ticket
              AND he.tipo_accion = \'eliminado\'
              AND he.fecha_eliminacion = (
                  SELECT MAX(hx.fecha_eliminacion)
                  FROM ticket_historico hx
                  WHERE hx.id_ticket = t.id_ticket
              )
        )';
    }

    /**
     * Fragmento SQL AND … para contar solo tickets del módulo Sabueso (categoria_gestion vacío o 'sabueso').
     * Cadena vacía si no existe la columna (BD sin migración).
     */
    private static function andTicketSoloSabuesoSql(Database $db): string
    {
        static $tieneColumna = null;
        if ($tieneColumna === null) {
            $tieneColumna = false;
            try {
                $col = $db->queryOne(
                    "SELECT 1 AS ok FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() " .
                    "AND TABLE_NAME = 'ticket' AND COLUMN_NAME = 'categoria_gestion' LIMIT 1"
                );
                $tieneColumna = !empty($col);
            } catch (\Exception $e) {
                $tieneColumna = false;
            }
        }
        return $tieneColumna
            ? " AND (COALESCE(NULLIF(TRIM(t.categoria_gestion),''), 'sabueso') = 'sabueso')"
            : '';
    }

    /**
     * Convierte segundos a texto legible (días, horas, min).
     */
    private static function segundosAHumano(int $seg): string
    {
        if ($seg < 0) {
            return '—';
        }
        if ($seg === 0) {
            return 'Menos de 1 min';
        }
        if ($seg < 60) {
            return $seg . ' s';
        }
        if ($seg < 3600) {
            return round($seg / 60) . ' min';
        }
        if ($seg < 86400) {
            return round($seg / 3600, 1) . ' h';
        }
        $d = (int)floor($seg / 86400);
        $h = (int)floor(($seg % 86400) / 3600);
        if ($d > 0 && $h > 0) {
            return $d . ' d ' . $h . ' h';
        }
        if ($d > 0) {
            return $d . ' d';
        }
        return round($seg / 3600, 1) . ' h';
    }

}
