<?php

namespace Models;

use Core\Database;
use Core\Model;

class Ticket extends Model
{
    /**
     * Lista de tickets.
     * @param int $idUsuario ID de la persona (sesión).
     * @param bool $soloDelUsuario true = solo los que levantó ese usuario (menú Ticket); false = todos (Panel Admin).
     * Siempre incluye creador_nombre para Panel Admin.
     */
    public static function getListaTickets($idUsuario, $soloDelUsuario = true)
    {
        $baseSelect = "SELECT t.id_ticket, t.folio, t.id_credito, t.descripcion_inicial, t.fecha_creacion, t.fecha_vencimiento, " .
            "tt.nombre AS tipo_ticket_nombre, et.nombre AS estado_ticket_nombre, pt.nombre AS prioridad_nombre, ot.nombre AS origen_nombre, " .
            "CONCAT(TRIM(IFNULL(p.nombres, '')), ' ', TRIM(IFNULL(p.apellidop, ''))) AS creador_nombre, " .
            "CONCAT(TRIM(IFNULL(pa.nombres, '')), ' ', TRIM(IFNULL(pa.apellidop, ''))) AS asignado_nombre, " .
            "dm.dictamen_estado, dm.dictamen_fecha_visto, dm.dictamen_fecha_envio " .
            "FROM ticket t " .
            "INNER JOIN tipo_ticket tt ON t.id_tipo_ticket = tt.id_tipo_ticket " .
            "INNER JOIN estado_ticket et ON t.id_estado_ticket = et.id_estado_ticket " .
            "INNER JOIN prioridad_ticket pt ON t.id_prioridad = pt.id_prioridad " .
            "INNER JOIN origen_ticket ot ON t.id_origen_ticket = ot.id_origen_ticket " .
            "INNER JOIN persona p ON t.id_persona_creador = p.id " .
            "LEFT JOIN asignacion_ticket at ON at.id_ticket = t.id_ticket AND (at.activo = 1 OR at.activo IS NULL) " .
            "LEFT JOIN persona pa ON at.id_persona_asignada = pa.id " .
            "LEFT JOIN (SELECT d.id_ticket, d.estado AS dictamen_estado, d.fecha_visto_gestor AS dictamen_fecha_visto, d.fecha_actualizacion AS dictamen_fecha_envio FROM dictamen d INNER JOIN (SELECT id_ticket, MAX(id) AS mid FROM dictamen GROUP BY id_ticket) mx ON d.id_ticket = mx.id_ticket AND d.id = mx.mid) dm ON dm.id_ticket = t.id_ticket ";

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
        foreach ($whereCandidates as $where) {
            try {
                $rows = $db->queryAll($baseSelect . $where . $orderBy, $params);
                $datos = is_array($rows) ? $rows : [];
                return self::resultado(true, 'Tickets encontrados.', $datos);
            } catch (\Exception $e) {
                $lastException = $e;
                continue;
            }
        }
        return self::resultado(false, 'Error al consultar tickets.', null, $lastException ? $lastException->getMessage() : '');
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
     */
    public static function crear($datos, $idPersonaCreador)
    {
        $db = new Database();

        // Siguiente id_ticket disponible (reutilizar huecos si se borraron tickets)
        $rows = $db->queryAll("SELECT id_ticket FROM ticket ORDER BY id_ticket");
        $usados = is_array($rows) ? array_column($rows, 'id_ticket') : [];
        $siguienteId = 1;
        while (in_array($siguienteId, $usados, true)) {
            $siguienteId++;
        }

        $ultimo = $db->queryOne(
            "SELECT folio FROM ticket WHERE folio LIKE 'TCK-%' ORDER BY id_ticket DESC LIMIT 1"
        );
        $num = 1;
        if ($ultimo && !empty($ultimo['folio']) && preg_match('/^TCK-(\d+)$/', trim($ultimo['folio']), $m)) {
            $num = (int)$m[1] + 1;
        }
        $folio = 'TCK-' . str_pad((string)$num, 4, '0', STR_PAD_LEFT);

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

        $tz = new \DateTimeZone('America/Mexico_City');
        $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');

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

        try {
            $db->CRUD($query, $params);
            return self::resultado(true, 'Ticket creado correctamente.', ['folio' => $folio, 'id_ticket' => $siguienteId]);
        } catch (\Exception $e) {
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
     * Desactiva la asignación anterior (activo=0, fecha_liberacion=NOW()) e inserta la nueva.
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');

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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->getTimestamp();
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
            $actual = $db->queryOne("SELECT id FROM dictamen WHERE id_ticket = :id_ticket AND estado = 'borrador' ORDER BY fecha_creacion DESC LIMIT 1", ['id_ticket' => $tid]);
            if ($actual && !empty($actual['id'])) {
                $db->CRUD(
                    "UPDATE dictamen SET tipo = :tipo, descripcion = :descripcion, fecha_actualizacion = :fecha_actualizacion WHERE id = :id",
                    ['tipo' => $tipo, 'descripcion' => $descripcion, 'fecha_actualizacion' => $now, 'id' => (int)$actual['id']]
                );
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
                $mensajeUsuario = 'Faltan columnas en la tabla dictamen. Ejecute la migración: backend/migrations/alter_dictamen_nuevo_flujo.sql';
            }
            return self::resultado(false, $mensajeUsuario, null, $msg);
        }
    }

    /**
     * Marcar el dictamen del ticket como enviado al gestor (estado = enviado_al_gestor).
     */
    public static function enviarDictamenGestor($idTicket)
    {
        $tid = (int)$idTicket;
        if ($tid < 1) {
            return self::resultado(false, 'ID de ticket inválido.');
        }
        try {
            $db = new Database();
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
            $actual = $db->queryOne("SELECT id FROM dictamen WHERE id_ticket = :id_ticket ORDER BY fecha_creacion DESC LIMIT 1", ['id_ticket' => $tid]);
            if (!$actual || empty($actual['id'])) {
                return self::resultado(false, 'No hay dictamen para enviar. Guarde un borrador primero.');
            }
            $db->CRUD(
                "UPDATE dictamen SET estado = 'enviado_al_gestor', fecha_actualizacion = :fecha_actualizacion WHERE id = :id",
                ['fecha_actualizacion' => $now, 'id' => (int)$actual['id']]
            );
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
            return self::resultado(true, 'OK', [
                'dictamen' => $dictamen ?: null,
                'evidencias' => is_array($evidencias) ? $evidencias : [],
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener detalle.', null, $e->getMessage());
        }
    }

    /**
     * Marca fecha_visto_gestor = NOW() y id_persona_visto_gestor cuando el gestor abre el modal del dictamen.
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
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
            $tz = new \DateTimeZone('America/Mexico_City');
            $now = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
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
}
