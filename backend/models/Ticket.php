<?php

namespace Models;

use Core\Database;
use Core\Model;

class Ticket extends Model
{
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
     */
    public static function getListaTickets($idUsuario, $soloDelUsuario = true)
    {
        $baseSelect = "SELECT DISTINCT t.id_ticket, t.folio, t.id_credito, t.descripcion_inicial, t.fecha_creacion, t.fecha_vencimiento, " .
            "tt.nombre AS tipo_ticket_nombre, et.nombre AS estado_ticket_nombre, pt.nombre AS prioridad_nombre, ot.nombre AS origen_nombre, " .
            "CONCAT(TRIM(IFNULL(p.nombres, '')), ' ', TRIM(IFNULL(p.apellidop, ''))) AS creador_nombre, " .
            "CONCAT(TRIM(IFNULL(pa.nombres, '')), ' ', TRIM(IFNULL(pa.apellidop, ''))) AS asignado_nombre, " .
            "dm.dictamen_estado, dm.dictamen_fecha_visto, dm.dictamen_fecha_envio, " .
            "dsm.ds_resultado, dsm.ds_detalle " .
            "FROM ticket t " .
            "INNER JOIN tipo_ticket tt ON t.id_tipo_ticket = tt.id_tipo_ticket " .
            "INNER JOIN estado_ticket et ON t.id_estado_ticket = et.id_estado_ticket " .
            "INNER JOIN prioridad_ticket pt ON t.id_prioridad = pt.id_prioridad " .
            "INNER JOIN origen_ticket ot ON t.id_origen_ticket = ot.id_origen_ticket " .
            "INNER JOIN persona p ON t.id_persona_creador = p.id " .
            "LEFT JOIN (SELECT at1.id_ticket, at1.id_persona_asignada FROM asignacion_ticket at1 INNER JOIN (SELECT id_ticket, MAX(fecha_asignacion) AS max_fecha FROM asignacion_ticket WHERE (activo = 1 OR activo IS NULL) GROUP BY id_ticket) at2 ON at1.id_ticket = at2.id_ticket AND at1.fecha_asignacion = at2.max_fecha WHERE (at1.activo = 1 OR at1.activo IS NULL)) at ON at.id_ticket = t.id_ticket " .
            "LEFT JOIN persona pa ON at.id_persona_asignada = pa.id " .
            "LEFT JOIN (SELECT d.id_ticket, d.estado AS dictamen_estado, d.fecha_visto_gestor AS dictamen_fecha_visto, d.fecha_actualizacion AS dictamen_fecha_envio FROM dictamen d INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen GROUP BY id_ticket) mx ON d.id_ticket = mx.id_ticket AND d.id = mx.mid) dm ON dm.id_ticket = t.id_ticket " .
            "LEFT JOIN (SELECT ds1.id_ticket, ds1.resultado AS ds_resultado, ds1.detalle AS ds_detalle FROM dictamen_sistema ds1 INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen_sistema GROUP BY id_ticket) dsmx ON ds1.id_ticket = dsmx.id_ticket AND ds1.id = dsmx.mid) dsm ON dsm.id_ticket = t.id_ticket ";

        $params = [];
        if ($soloDelUsuario) {
            $params['id_persona'] = (int)$idUsuario;
        }

        $db = new Database();
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
                    $det = !empty($row['ds_detalle']) ? json_decode($row['ds_detalle'], true) : null;
                    if (is_array($det) && isset($det['prorroga']) && is_array($det['prorroga'])) {
                        $pr = $det['prorroga'];
                        $row['prorroga_otorgada'] = !empty($pr['otorgada']);
                        $row['prorroga_activa'] = !empty($pr['otorgada']) && empty($pr['evaluada']);
                        $row['prorroga_fecha_limite'] = $pr['fecha_limite'] ?? null;
                    }
                    // HTML seguro para columnas DataTable (evita embeber HTML en el JS embebido de Sabueso.php)
                    $dsRes = trim((string)($row['ds_resultado'] ?? ''));
                    if ($dsRes === '') {
                        $row['ds_resultado_html'] = '<span class="text-muted">—</span>';
                    } else {
                        $short = mb_strlen($dsRes) > 18 ? mb_substr($dsRes, 0, 16) . '…' : $dsRes;
                        $row['ds_resultado_html'] = '<small class="text-break" title="' . htmlspecialchars($dsRes, ENT_QUOTES, 'UTF-8') . '">'
                            . htmlspecialchars($short, ENT_QUOTES, 'UTF-8') . '</small>';
                    }
                    if (empty($row['prorroga_otorgada'])) {
                        $row['prorroga_html'] = '<span class="text-muted">—</span>';
                    } else {
                        $activa = !empty($row['prorroga_activa']);
                        $cls = $activa ? 'bg-warning text-dark' : 'bg-secondary';
                        $txt = $activa ? 'Activa' : 'Usada';
                        $tip = !empty($row['prorroga_fecha_limite']) ? 'Límite: ' . $row['prorroga_fecha_limite'] : 'Prórroga';
                        $row['prorroga_html'] = '<span class="badge ' . htmlspecialchars($cls, ENT_QUOTES, 'UTF-8')
                            . '" data-bs-toggle="tooltip" data-bs-title="' . htmlspecialchars($tip, ENT_QUOTES, 'UTF-8')
                            . '">' . htmlspecialchars($txt, ENT_QUOTES, 'UTF-8') . '</span>';
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
     * Todos los campos son obligatorios: tipo, prioridad, origen, id_credito, descripción, fecha_vencimiento.
     * Usa transacciones y reintentos para evitar condiciones de carrera y IDs duplicados.
     */
    public static function crear($datos, $idPersonaCreador)
    {
        $db = new Database();

        $idTipo = (int)($datos['id_tipo_ticket'] ?? 0);
        $idPrioridad = (int)($datos['id_prioridad'] ?? 0);
        $idOrigen = (int)($datos['id_origen_ticket'] ?? 0);
        $idCredito = isset($datos['id_credito']) && $datos['id_credito'] !== '' && $datos['id_credito'] !== null
            ? (int)$datos['id_credito'] : null;
        $descripcion = isset($datos['descripcion_inicial']) ? trim((string)$datos['descripcion_inicial']) : '';
        $fechaVenc = isset($datos['fecha_vencimiento']) && trim((string)$datos['fecha_vencimiento']) !== ''
            ? trim((string)$datos['fecha_vencimiento'])
            : null;

        if ($idTipo < 1 || $idPrioridad < 1 || $idOrigen < 1 || $descripcion === '') {
            return self::resultado(false, 'Faltan datos obligatorios (tipo, prioridad, origen, descripción).', null);
        }
        if ($idCredito === null || $idCredito < 1) {
            return self::resultado(false, 'El ID de crédito es obligatorio y debe ser mayor a 0.', null);
        }
        if ($fechaVenc === null || $fechaVenc === '') {
            return self::resultado(false, 'La fecha de vencimiento es obligatoria.', null);
        }

        $rowEstado = $db->queryOne("SELECT id_estado_ticket FROM estado_ticket WHERE LOWER(TRIM(nombre)) = 'abierto' AND (activo = 1 OR activo IS NULL) LIMIT 1");
        $idEstado = $rowEstado ? (int)$rowEstado['id_estado_ticket'] : 0;
        if ($idEstado < 1) {
            return self::resultado(false, 'No se encontró el estado "Abierto" en catálogo.', null);
        }

        $now = self::ahoraCdmx();

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

                $query = <<<SQL
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
                ];

                $db->CRUD($query, $params);
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
     * Asigna un ticket a una persona usando la tabla asignacion_ticket.
     * Desactiva la asignación anterior (activo=0, fecha_liberacion=ahora CDMX) e inserta la nueva.
     */
    public static function asignar($idTicket, $idPersona)
    {
        $tid = (int)$idTicket;
        $pid = (int)$idPersona;
        if ($tid < 1 || $pid < 1) {
            return self::resultado(false, 'ID de ticket o persona inválido.', null);
        }
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $db->CRUD(
                "UPDATE asignacion_ticket SET activo = 0, fecha_liberacion = :ahora WHERE id_ticket = :id_ticket AND (activo = 1 OR activo IS NULL)",
                ['ahora' => $now, 'id_ticket' => $tid]
            );
            $db->CRUD(
                "INSERT INTO asignacion_ticket (id_ticket, id_persona_asignada, fecha_asignacion, activo) VALUES (:id_ticket, :id_persona, :fecha_asignacion, 1)",
                ['id_ticket' => $tid, 'id_persona' => $pid, 'fecha_asignacion' => $now]
            );
            return self::resultado(true, 'Ticket asignado correctamente.');
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
     * Guardar dictamen como borrador (insert o update). tipo y descripcion obligatorios.
     */
    public static function guardarDictamenBorrador($idTicket, $idPersona, $tipo, $descripcion)
    {
        $tid = (int)$idTicket;
        $pid = (int)$idPersona;
        $tipo = trim((string)$tipo);
        $descripcion = trim((string)$descripcion);
        if ($tid < 1 || $pid < 1 || $tipo === '' || $descripcion === '') {
            return self::resultado(false, 'Faltan tipo o descripción.');
        }
        if (strlen($descripcion) > 2000) {
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
            if ($tipo === '' || $descripcion === '') {
                return self::resultado(false, 'Debe seleccionar el tipo de dictamen y escribir una descripción antes de enviar al gestor.');
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
            $evidencias = $db->queryAll("SELECT id, ruta_archivo, nombre_original, fecha_subida FROM ticket_evidencia WHERE id_ticket = :id_ticket ORDER BY fecha_subida ASC", ['id_ticket' => $id]);

            // Parsear domicilios de visita desde la descripción ("Podrás encontrar al usuario en desc link; desc2 link2")
            $domicilios = [];
            $descripcionBase = $dictamen ? ($dictamen['descripcion'] ?? '') : '';
            if ($dictamen && !empty($dictamen['descripcion'])) {
                $desc = (string) $dictamen['descripcion'];
                $prefijo = 'Podrás encontrar al usuario en ';
                $pos = strpos($desc, $prefijo);
                if ($pos !== false) {
                    $descripcionBase = trim(preg_replace('/\.\s*$/', '', substr($desc, 0, $pos)));
                    $domStr = trim(substr($desc, $pos + strlen($prefijo)));
                    $bloques = preg_split('/\s*;\s*/', $domStr, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($bloques as $bloq) {
                        $bloq = trim($bloq);
                        if ($bloq === '') continue;
                        if (preg_match('/\s+(https?:\/\/\S+)$/u', $bloq, $m)) {
                            $domicilios[] = ['desc' => trim(substr($bloq, 0, -strlen($m[0]))), 'link' => $m[1]];
                        } else {
                            $domicilios[] = ['desc' => $bloq, 'link' => ''];
                        }
                    }
                }
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
     * Lista de evidencias (imágenes) por ticket.
     */
    public static function getEvidenciasPorTicket($idTicket)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', []);
        }
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT id, id_ticket, id_persona, ruta_archivo, nombre_original, fecha_subida FROM ticket_evidencia WHERE id_ticket = :id_ticket ORDER BY fecha_subida ASC",
                ['id_ticket' => $id]
            );
            return self::resultado(true, 'OK', is_array($rows) ? $rows : []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener evidencias.', null, $e->getMessage());
        }
    }

    /**
     * Guardar registro de evidencia (ruta ya guardada en disco).
     */
    public static function guardarEvidencia($idTicket, $idPersona, $rutaArchivo, $nombreOriginal)
    {
        $tid = (int)$idTicket;
        $pid = (int)$idPersona;
        $ruta = trim((string)$rutaArchivo);
        $nombre = trim((string)$nombreOriginal) ?: 'imagen';
        if ($tid < 1 || $pid < 1 || $ruta === '') {
            return self::resultado(false, 'Datos inválidos.', null);
        }
        try {
            $db = new Database();
            $now = self::ahoraCdmx();
            $db->CRUD(
                "INSERT INTO ticket_evidencia (id_ticket, id_persona, ruta_archivo, nombre_original, fecha_subida) VALUES (:id_ticket, :id_persona, :ruta_archivo, :nombre_original, :fecha_subida)",
                ['id_ticket' => $tid, 'id_persona' => $pid, 'ruta_archivo' => $ruta, 'nombre_original' => $nombre, 'fecha_subida' => $now]
            );
            $lastId = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            return self::resultado(true, 'Evidencia guardada.', ['id' => $lastId['id'] ?? null]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar evidencia.', null, $e->getMessage());
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
    } catch (\Exception $e) {
        error_log('getNombresClienteParaReporte error: ' . $e->getMessage());
        return [];
    }
}

    // =====================================================================
    //  DICTAMEN DEL SISTEMA — verificación automática de visita
    // =====================================================================

    /**
     * Guarda snapshot de gestiones al momento de enviar el dictamen al gestor.
     */
    private static function guardarSnapshotDictamenSistema(int $idTicket, int $idDictamen, string $fechaEnvio, Database $db)
    {
        $ticketRow = $db->queryOne("SELECT id_credito FROM ticket WHERE id_ticket = :id LIMIT 1", ['id' => $idTicket]);
        $idCredito = $ticketRow ? (int)($ticketRow['id_credito'] ?? 0) : 0;
        if ($idCredito < 1) return;

        $idGestor = self::getCreadorIdPorTicket($idTicket);
        $nombreGestor = $idGestor > 0 ? self::getNombrePersona($idGestor) : '';

        $gestiones = Gestiones::getAllGestiones((string)$idCredito, '');
        $totalGestiones = is_array($gestiones) ? count($gestiones) : 0;

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
     * Genera el dictamen del sistema: compara gestiones antes/después y calcula distancias.
     * Se invoca desde el botón en Panel Admin una vez que pasan las 12 horas.
     */
    public static function generarDictamenSistema(int $idTicket): array
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
                return self::resultado(false, 'No existe registro de dictamen_sistema para este ticket. Es posible que el dictamen se haya enviado antes de activar esta función.');
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

            $esRevisionProrroga = !empty($prorrogaPrev['otorgada']) && empty($prorrogaPrev['evaluada']);
            $fechaInicioVentana = (string)($ds['fecha_envio_dictamen'] ?? '');
            $totalAntes = (int)($ds['gestiones_al_enviar'] ?? 0);
            if ($esRevisionProrroga) {
                if (!empty($prorrogaPrev['fecha_otorgada'])) {
                    $fechaInicioVentana = (string)$prorrogaPrev['fecha_otorgada'];
                }
                if (isset($prorrogaPrev['gestiones_al_otorgar'])) {
                    $totalAntes = (int)$prorrogaPrev['gestiones_al_otorgar'];
                }
            }

            $inicioWinDt = new \DateTime($fechaInicioVentana !== '' ? $fechaInicioVentana : $now, $tz);
            $finWinDt = clone $inicioWinDt;
            $finWinDt->modify('+12 hours');
            $fechaInicioWin = $inicioWinDt->format('Y-m-d H:i:s');
            $fechaFinWin = $finWinDt->format('Y-m-d H:i:s');
            $nowTs = (new \DateTime($now, $tz))->getTimestamp();
            if ($nowTs < $finWinDt->getTimestamp()) {
                $rest = max(0, $finWinDt->getTimestamp() - $nowTs);
                $h = floor($rest / 3600);
                $m = floor(($rest % 3600) / 60);
                $tipo = $esRevisionProrroga ? 'la prórroga' : 'la ventana inicial';
                return self::resultado(false, 'Aún no vence ' . $tipo . ' de 12 horas. Restante aproximado: ' . $h . 'h ' . $m . 'm.');
            }

            $pagosEnVentana = self::getPagosEstadoCuentaEnVentana($idCredito, $fechaInicioWin, $fechaFinWin);
            $hayPagoEnVentana = !empty($pagosEnVentana);

            $gestionesAhora = Gestiones::getAllGestiones((string)$idCredito, '');
            $totalAhora = is_array($gestionesAhora) ? count($gestionesAhora) : 0;
            $nuevas = $totalAhora > $totalAntes ? array_slice($gestionesAhora, 0, $totalAhora - $totalAntes) : [];

            // Obtener coordenadas del dictamen (direcciones proporcionadas)
            $dictamenRow = $db->queryOne(
                "SELECT descripcion FROM dictamen WHERE id = :id LIMIT 1",
                ['id' => (int)$ds['id_dictamen']]
            );
            $coordsDictamen = self::extraerCoordenadasDictamen($dictamenRow['descripcion'] ?? '');

            $analisis = [];
            $visitoCampo = false;
            $visitoTelefonico = false;
            $sinCoordenadas = true;
            $coberturaDirecciones = [];
            foreach ($coordsDictamen as $ix => $cd) {
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
                $esTelefonico = ($contacto === 'telefono' || $contacto === 'telefono' ||
                    !empty(trim((string)($g['medio_contactacion_ccc'] ?? ''))) &&
                    trim((string)($g['medio_contactacion_campo'] ?? '')) === '' &&
                    trim((string)($g['medio_contactacion_campo'] ?? '')) !== 'domicilio del cliente');

                if ($esCampo && empty(trim((string)($g['medio_contactacion_ccc'] ?? '')))
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

            if ($esRevisionProrroga) {
                $prorrogaPrev['evaluada'] = true;
                $prorrogaPrev['fecha_revision'] = $now;
                $prorrogaPrev['resultado_base'] = $resultadoBase;
                $prorrogaPrev['resultado_final'] = $resultadoFinal;
                $resultadoFinal = ($resultadoFinal === 'cumplido_pago' || $resultadoFinal === 'cumplido_sin_pago_todas_direcciones')
                    ? 'cumplio_prorroga'
                    : 'no_cumplio_prorroga';
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
                    'tipo' => $esRevisionProrroga ? 'prorroga_12h' : 'inicial_12h',
                ],
                'pago_en_ventana' => $hayPagoEnVentana,
                'pagos_en_ventana' => $pagosEnVentana,
                'direcciones_dictamen_total' => $direccionesTotal,
                'direcciones_visitadas' => $direccionesVisitadas,
                'visito_todas_direcciones' => $visitoTodasDirecciones,
                'visita_parcial_direcciones' => $visitaParcialDirecciones,
                'cobertura_direcciones' => array_values($coberturaDirecciones),
            ];
            if (!empty($prorrogaPrev)) {
                $detalleBase['prorroga'] = $prorrogaPrev;
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

            $detalle = !empty($ds['detalle']) ? json_decode($ds['detalle'], true) : [];
            if (!is_array($detalle)) {
                $detalle = [];
            }
            $pr = (isset($detalle['prorroga']) && is_array($detalle['prorroga'])) ? $detalle['prorroga'] : [];
            if (!empty($pr['otorgada'])) {
                return self::resultado(false, 'Este ticket ya tiene prórroga otorgada (solo una vez).');
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
     * Consulta pagos en estado de cuenta dentro de una ventana (12 h).
     */
    private static function getPagosEstadoCuentaEnVentana(int $idCredito, string $inicio, string $fin): array
    {
        if ($idCredito < 1 || $inicio === '' || $fin === '') {
            return [];
        }
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
            $iniTs = (new \DateTime($inicio, $tz))->getTimestamp();
            $finTs = (new \DateTime($fin, $tz))->getTimestamp();
            if ($finTs < $iniTs) {
                return [];
            }
            $estadoCuentaCtrl = new \Controllers\EstadoCuenta();
            $resEstado = $estadoCuentaCtrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'));
            $pagos = $resEstado['data']['datosPagos'] ?? [];
            if (empty($resEstado['ok']) || !is_array($pagos)) {
                return [];
            }
            $out = [];
            foreach ($pagos as $p) {
                $raw = $p['fechaDeposito'] ?? $p['fechaRegistro'] ?? $p['fechaValor'] ?? null;
                if ($raw === null || $raw === '') {
                    continue;
                }
                $ts = is_numeric($raw) ? (int)$raw : strtotime((string)$raw);
                if (!$ts) {
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
                        'fecha' => date('Y-m-d H:i:s', $ts),
                        'monto' => $p['montoPago'] ?? null,
                        'referencia' => $p['referencia'] ?? ($p['descripcion'] ?? null),
                    ];
                }
            }
            return $out;
        } catch (\Throwable $e) {
            return [];
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
            // Prórroga vencida: generar automáticamente una vez (sin botón; generarDictamenSistema ya valida ventana)
            if (($ds['resultado'] ?? '') === 'prorroga_activa' && is_array($ds['detalle_parsed'])) {
                $pr = $ds['detalle_parsed']['prorroga'] ?? null;
                $limite = is_array($pr) ? ($pr['fecha_limite'] ?? '') : '';
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
            return self::resultado(true, 'OK', ['dictamen_sistema' => $ds]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener dictamen del sistema.', null, $e->getMessage());
        }
    }

    /**
     * Extrae coordenadas lat/lng de las URLs de Google Maps en la descripción del dictamen.
     * Formato esperado: "Podrás encontrar al usuario en [desc] https://maps...?q=lat,lng; ..."
     */
    private static function extraerCoordenadasDictamen(string $descripcion): array
    {
        $coords = [];
        $prefijo = 'Podrás encontrar al usuario en ';
        $pos = strpos($descripcion, $prefijo);
        if ($pos === false) return $coords;

        $domStr = trim(substr($descripcion, $pos + strlen($prefijo)));
        $bloques = preg_split('/\s*;\s*/', $domStr, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($bloques as $bloq) {
            $bloq = trim($bloq);
            if ($bloq === '') continue;

            $desc = $bloq;
            $lat = null;
            $lng = null;

            // Extraer URL de Google Maps
            if (preg_match('/(https?:\/\/\S+)/u', $bloq, $urlMatch)) {
                $url = $urlMatch[1];
                $desc = trim(str_replace($url, '', $bloq));

                // Intentar extraer coordenadas de la URL
                // Patrones comunes: ?q=lat,lng  @lat,lng  /place/lat,lng
                if (preg_match('/[?&]q=(-?\d+\.?\d*),(-?\d+\.?\d*)/', $url, $m)) {
                    $lat = (float)$m[1];
                    $lng = (float)$m[2];
                } elseif (preg_match('/@(-?\d+\.?\d*),(-?\d+\.?\d*)/', $url, $m)) {
                    $lat = (float)$m[1];
                    $lng = (float)$m[2];
                } elseif (preg_match('/\/place\/(-?\d+\.?\d*),(-?\d+\.?\d*)/', $url, $m)) {
                    $lat = (float)$m[1];
                    $lng = (float)$m[2];
                }
            }

            if ($lat !== null && $lng !== null && ($lat != 0.0 || $lng != 0.0)) {
                $coords[] = ['desc' => $desc, 'lat' => $lat, 'lng' => $lng];
            }
        }

        return $coords;
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
        $out = [
            'success' => true,
            'mensaje' => 'OK',
            'totales' => ['tickets_activos' => 0, 'con_dictamen_enviado' => 0, 'con_dictamen_visto' => 0],
            'por_dia' => [],
            'por_semana' => [],
            'por_mes' => [],
            'por_anio' => [],
            'tiempos_sabueso_segundos' => null,
            'tiempos_gestor_segundos' => null,
            'detalle_timings' => [],
            'por_sabueso' => [],
            'por_gestor_lectura' => [],
            'kpis_extra' => [],
        ];

        try {
            $row = $db->queryOne("SELECT COUNT(*) AS c FROM ticket t WHERE $whereActivo");
            $out['totales']['tickets_activos'] = (int)($row['c'] ?? 0);
        } catch (\Exception $e) {
            $out['success'] = false;
            $out['mensaje'] = $e->getMessage();
            return $out;
        }

        // Conteos por día (últimos 90 días)
        try {
            $rows = $db->queryAll(
                "SELECT DATE(t.fecha_creacion) AS periodo, COUNT(*) AS n FROM ticket t " .
                "WHERE $whereActivo AND t.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) " .
                "GROUP BY DATE(t.fecha_creacion) ORDER BY periodo DESC"
            );
            $out['por_dia'] = is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            $out['por_dia'] = [];
        }

        // Por semana (año-semana ISO aproximado)
        try {
            $rows = $db->queryAll(
                "SELECT YEARWEEK(t.fecha_creacion, 1) AS periodo, COUNT(*) AS n FROM ticket t " .
                "WHERE $whereActivo AND t.fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 365 DAY) " .
                "GROUP BY YEARWEEK(t.fecha_creacion, 1) ORDER BY periodo DESC LIMIT 52"
            );
            $out['por_semana'] = is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            $out['por_semana'] = [];
        }

        // Por mes
        try {
            $rows = $db->queryAll(
                "SELECT DATE_FORMAT(t.fecha_creacion, '%Y-%m') AS periodo, COUNT(*) AS n FROM ticket t " .
                "WHERE $whereActivo GROUP BY DATE_FORMAT(t.fecha_creacion, '%Y-%m') ORDER BY periodo DESC LIMIT 36"
            );
            $out['por_mes'] = is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            $out['por_mes'] = [];
        }

        // Por año
        try {
            $rows = $db->queryAll(
                "SELECT YEAR(t.fecha_creacion) AS periodo, COUNT(*) AS n FROM ticket t " .
                "WHERE $whereActivo GROUP BY YEAR(t.fecha_creacion) ORDER BY periodo DESC"
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

        // Tiempo Sabueso: desde primera asignación en asignacion_ticket hasta fecha_actualizacion del dictamen
        // enviado al gestor (un solo dictamen por ticket: el de mayor fecha_actualizacion en estado enviado).
        try {
            $sqlSabueso = "
                SELECT AVG(TIMESTAMPDIFF(SECOND, at.min_fa, d.fecha_actualizacion)) AS avg_sec,
                       MIN(TIMESTAMPDIFF(SECOND, at.min_fa, d.fecha_actualizacion)) AS min_sec,
                       MAX(TIMESTAMPDIFF(SECOND, at.min_fa, d.fecha_actualizacion)) AS max_sec,
                       COUNT(*) AS n
                FROM (
                    SELECT id_ticket, MIN(fecha_asignacion) AS min_fa
                    FROM asignacion_ticket
                    WHERE (activo = 1 OR activo IS NULL)
                    GROUP BY id_ticket
                ) at
                INNER JOIN (
                    SELECT d1.id_ticket, d1.fecha_actualizacion
                    FROM dictamen d1
                    INNER JOIN (
                        SELECT id_ticket, MAX(fecha_actualizacion) AS mx
                        FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket
                    ) dm ON d1.id_ticket = dm.id_ticket AND d1.fecha_actualizacion = dm.mx AND d1.estado = 'enviado_al_gestor'
                ) d ON d.id_ticket = at.id_ticket
                INNER JOIN ticket t ON t.id_ticket = d.id_ticket AND $whereActivo
                WHERE d.fecha_actualizacion IS NOT NULL AND at.min_fa IS NOT NULL
                  AND d.fecha_actualizacion >= at.min_fa
                  AND d.fecha_actualizacion >= $inicioSemanaLunes
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
                    'alcance_texto' => 'Semana actual (desde lunes): promedio solo con envíos ocurridos esta semana; al llegar el próximo lunes el conteo se reinicia.',
                ];
            }
        } catch (\Exception $e) {
            $out['tiempos_sabueso_segundos'] = null;
        }

        // Tiempo gestor: desde envío del dictamen (fecha_actualizacion) hasta fecha_visto_gestor
        // (cuando el gestor abre el modal). Una fila por ticket con dictamen enviado y ya visto.
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
                  AND d.fecha_visto_gestor >= $inicioSemanaLunes
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
                    'alcance_texto' => 'Semana actual (desde lunes): promedio solo con aperturas registradas esta semana; cada lunes el acumulado vuelve a empezar.',
                ];
            }
        } catch (\Exception $e) {
            $out['tiempos_gestor_segundos'] = null;
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

    public static function getEstadisticasGestorDetalle(int $idPersonaCreador): array
    {
        $db = new Database();
        $whereActivo = '(t.activo = 1 OR t.activo IS NULL) AND (t.fecha_eliminacion IS NULL)';
        $cid = (int)$idPersonaCreador;
        if ($cid < 1) {
            return ['success' => false, 'mensaje' => 'ID inválido', 'nombre' => '', 'filas' => []];
        }
        try {
            $nom = self::getNombrePersona($cid);
            // Un dictamen por ticket: el enviado con visto preferente; si no, el de max fecha_actualizacion
            $rows = $db->queryAll(
                "SELECT t.id_ticket, t.folio, t.fecha_creacion, " .
                "d.fecha_actualizacion AS dictamen_envio, d.fecha_visto_gestor AS dictamen_visto " .
                "FROM ticket t " .
                "INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' " .
                "INNER JOIN (SELECT id_ticket, MAX(fecha_actualizacion) AS mx FROM dictamen WHERE estado = 'enviado_al_gestor' GROUP BY id_ticket) dm " .
                "ON d.id_ticket = dm.id_ticket AND d.fecha_actualizacion = dm.mx " .
                "WHERE t.id_persona_creador = :cid AND $whereActivo " .
                "ORDER BY d.fecha_actualizacion DESC LIMIT 200",
                ['cid' => $cid]
            );
            $filas = [];
            $listaRows = is_array($rows) ? $rows : [];
            $idsT = [];
            foreach ($listaRows as $r) {
                $tid = (int)($r['id_ticket'] ?? 0);
                if ($tid > 0) {
                    $idsT[$tid] = true;
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
                    // Columna "Pago": solo Sí / No — hubo pago registrado en estado de cuenta dentro de la ventana 12h evaluada
                    if ($resDs === 'cumplido_pago') {
                        $pagoVentanaTxt = 'Sí';
                    } elseif (is_array($detJson) && array_key_exists('pago_en_ventana', $detJson)) {
                        $pagoVentanaTxt = !empty($detJson['pago_en_ventana']) ? 'Sí' : 'No';
                    } elseif ($resDs !== null && $resDs !== '' && $resDs !== 'pendiente' && $resDs !== 'prorroga_activa') {
                        // Ya hay resultado (ej. no visito) y no consta pago en las 12h → No (obvio: sin pago en ese rango)
                        $pagoVentanaTxt = 'No';
                    }
                }
                $resultadoMostrar = null;
                if ($etiq !== null && $etiq !== '') {
                    $resultadoMostrar = $etiq;
                } elseif ($resDs !== null && $resDs !== '') {
                    $cmp = self::cumplimientoMetadatos($resDs);
                    $resultadoMostrar = $cmp['cumplimiento_etiqueta'] ?? $resDs;
                }
                $filas[] = [
                    'id_ticket' => $tid,
                    'folio' => $r['folio'] ?? '',
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
                ];
            }
            return ['success' => true, 'mensaje' => 'OK', 'nombre' => $nom, 'filas' => $filas];
        } catch (\Exception $e) {
            return ['success' => false, 'mensaje' => $e->getMessage(), 'nombre' => '', 'filas' => []];
        }
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
