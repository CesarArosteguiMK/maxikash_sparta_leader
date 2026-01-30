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
        $baseQuery = <<<SQL
            SELECT
                t.id_ticket,
                t.folio,
                t.id_credito,
                t.descripcion_inicial,
                t.fecha_creacion,
                t.fecha_vencimiento,
                t.activo,
                tt.nombre AS tipo_ticket_nombre,
                et.nombre AS estado_ticket_nombre,
                pt.nombre AS prioridad_nombre,
                ot.nombre AS origen_nombre,
                CONCAT(TRIM(IFNULL(p.nombres, '')), ' ', TRIM(IFNULL(p.apellidop, ''))) AS creador_nombre
            FROM ticket t
            INNER JOIN tipo_ticket tt ON t.id_tipo_ticket = tt.id_tipo_ticket
            INNER JOIN estado_ticket et ON t.id_estado_ticket = et.id_estado_ticket
            INNER JOIN prioridad_ticket pt ON t.id_prioridad = pt.id_prioridad
            INNER JOIN origen_ticket ot ON t.id_origen_ticket = ot.id_origen_ticket
            INNER JOIN persona p ON t.id_persona_creador = p.id
            WHERE (t.activo = 1 OR t.activo IS NULL)
        SQL;

        $params = [];
        if ($soloDelUsuario) {
            $baseQuery .= ' AND t.id_persona_creador = :id_persona';
            $params['id_persona'] = (int)$idUsuario;
        }

        $baseQuery .= ' ORDER BY t.fecha_creacion DESC';

        try {
            $db = new Database();
            $rows = $db->queryAll($baseQuery, $params);
            $datos = is_array($rows) ? $rows : [];
            return self::resultado(true, 'Tickets encontrados.', $datos);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar tickets.', null, $e->getMessage());
        }
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
            return [];
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
     * Elimina un ticket de la base de datos (borrado físico).
     */
    public static function eliminar($idTicket)
    {
        $id = (int)$idTicket;
        if ($id < 1) {
            return self::resultado(false, 'ID de ticket inválido.', null);
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM ticket WHERE id_ticket = :id", ['id' => $id]);
            return self::resultado(true, 'Ticket eliminado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar el ticket.', null, $e->getMessage());
        }
    }
}
