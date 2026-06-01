<?php

namespace Models;

use Core\Database;
use Core\Model;

class Notificacion extends Model
{
    /**
     * Regla: ninguna parte del código debe forzar una notificación a "no leída" (leida=0).
     * Las nuevas se insertan con leida=0; al leerlas se actualiza a leida=1 y así se quedan.
     */

    /**
     * Obtiene los id_persona que tienen al menos uno de los módulos indicados (ej. 18, 19 = Sabueso).
     */
    public static function getPersonasConModulos(array $moduloIds): array
    {
        if (empty($moduloIds)) {
            return [];
        }
        try {
            $db = new Database();
            $params = [];
            $placeholders = [];
            foreach ($moduloIds as $i => $id) {
                $key = 'mod_' . $i;
                $placeholders[] = ':' . $key;
                $params[$key] = (int) $id;
            }
            $placeholdersStr = implode(',', $placeholders);
            $rows = $db->queryAll(
                "SELECT DISTINCT usuario_id FROM asigna_modulo_web WHERE modulo_web_id IN ($placeholdersStr)",
                $params
            );
            return array_values(array_unique(array_column($rows ?: [], 'usuario_id')));
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Crea una notificación para una persona.
     * Para tipos Sabueso (con id_ticket): solo una notificación por (persona, tipo, ticket). No duplica.
     * Para otros (ej. candidato_expediente_completo sin id_ticket): puede repetirse si el evento se da de nuevo (ej. re-subida de documentos).
     */
    public static function crear(int $idPersona, string $tipo, string $mensaje, ?int $idTicket = null): bool
    {
        if ($idPersona < 1 || $tipo === '' || $mensaje === '') {
            return false;
        }
        if ($idTicket !== null && $idTicket > 0) {
            if (self::yaExisteParaTicket($idPersona, $tipo, $idTicket)) {
                return true;
            }
        }
        $mensaje = mb_substr($mensaje, 0, 500);
        try {
            $db = new Database();
            $db->CRUD(
                "INSERT INTO notificacion (id_persona, tipo, mensaje, id_ticket, leida) VALUES (:id_persona, :tipo, :mensaje, :id_ticket, 0)",
                [
                    'id_persona' => $idPersona,
                    'tipo'       => $tipo,
                    'mensaje'    => $mensaje,
                    'id_ticket'  => $idTicket
                ]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Sabueso: evita duplicar notificación para el mismo (persona, tipo, ticket).
     */
    public static function yaExisteParaTicket(int $idPersona, string $tipo, int $idTicket): bool
    {
        if ($idPersona < 1 || $tipo === '' || $idTicket < 1) {
            return false;
        }
        try {
            $db = new Database();
            $r = $db->queryOne(
                "SELECT 1 FROM notificacion WHERE id_persona = :id_persona AND tipo = :tipo AND id_ticket = :id_ticket LIMIT 1",
                ['id_persona' => $idPersona, 'tipo' => $tipo, 'id_ticket' => $idTicket]
            );
            return !empty($r);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Crea la misma notificación para varias personas (ej. equipo Sabueso).
     * Usa un solo INSERT con múltiples filas cuando hay id_ticket para no bloquear la respuesta.
     */
    public static function crearParaPersonas(array $idPersonas, string $tipo, string $mensaje, ?int $idTicket = null): void
    {
        $idPersonas = array_values(array_unique(array_map('intval', $idPersonas)));
        $idPersonas = array_filter($idPersonas, function ($id) {
            return $id > 0;
        });
        if (empty($idPersonas)) {
            return;
        }
        $mensaje = mb_substr($mensaje, 0, 500);
        try {
            $db = new Database();
            if ($idTicket !== null && $idTicket > 0) {
                $paramsExist = ['tipo' => $tipo, 'id_ticket' => $idTicket];
                $placeholders = [];
                foreach ($idPersonas as $i => $id) {
                    $key = 'id_' . $i;
                    $paramsExist[$key] = $id;
                    $placeholders[] = ":$key";
                }
                $placeholdersStr = implode(',', $placeholders);
                $stmt = $db->queryAll(
                    "SELECT id_persona FROM notificacion WHERE tipo = :tipo AND id_ticket = :id_ticket AND id_persona IN ($placeholdersStr)",
                    $paramsExist
                );
                $yaExisten = is_array($stmt) ? array_column($stmt, 'id_persona') : [];
                $idPersonas = array_values(array_diff($idPersonas, $yaExisten));
            }
            if (empty($idPersonas)) {
                return;
            }
            $values = [];
            $params = ['tipo' => $tipo, 'mensaje' => $mensaje, 'id_ticket' => $idTicket];
            foreach ($idPersonas as $i => $id) {
                $key = 'id_' . $i;
                $params[$key] = $id;
                $values[] = "(:$key, :tipo, :mensaje, :id_ticket, 0)";
            }
            $sql = "INSERT INTO notificacion (id_persona, tipo, mensaje, id_ticket, leida) VALUES " . implode(', ', $values);
            $db->CRUD($sql, $params);
        } catch (\Exception $e) {
            foreach ($idPersonas as $id) {
                self::crear($id, $tipo, $mensaje, $idTicket);
            }
        }
    }

    /**
     * Lista notificaciones para una persona (no leídas primero, luego por fecha descendente).
     * Ejecuta purga y sync una sola vez y devuelve lista + total no leídas en una sola ronda a BD.
     */
    public static function listarParaPersona(int $idPersona, int $limite = 50): array
    {
        if ($idPersona < 1) {
            return [];
        }
        self::purgarAntiguas(5);
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT id, tipo, mensaje, id_ticket, leida, fecha_creacion
                 FROM notificacion
                 WHERE id_persona = :id_persona
                 ORDER BY leida ASC, fecha_creacion DESC
                 LIMIT " . (int)$limite,
                ['id_persona' => $idPersona]
            );
            return is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Lista notificaciones y total no leídas en una sola petición (purga y sync una vez).
     * Uso: listar() en el controlador (purga una vez, luego listado + conteo). No se ejecuta sync para no tocar leída.
     */
    public static function listarConTotal(int $idPersona, int $limite = 50): array
    {
        if ($idPersona < 1) {
            return ['lista' => [], 'total_no_leidas' => 0];
        }
        self::purgarAntiguas(5);
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT id, id_persona, tipo, mensaje, id_ticket, leida, fecha_creacion
                 FROM notificacion
                 WHERE id_persona = :id_persona
                 ORDER BY leida ASC, fecha_creacion DESC
                 LIMIT " . (int)$limite,
                ['id_persona' => $idPersona]
            );
            $lista = is_array($rows) ? $rows : [];
            $r = $db->queryOne(
                "SELECT COUNT(*) AS total FROM notificacion WHERE id_persona = :id_persona AND leida = 0",
                ['id_persona' => $idPersona]
            );
            $totalNoLeidas = (int)($r['total'] ?? 0);
            return ['lista' => $lista, 'total_no_leidas' => $totalNoLeidas];
        } catch (\Exception $e) {
            return ['lista' => [], 'total_no_leidas' => 0];
        }
    }

    /**
     * Cuenta no leídas para una persona.
     */
    public static function contarNoLeidas(int $idPersona): int
    {
        if ($idPersona < 1) {
            return 0;
        }
        self::purgarAntiguas(5);
        try {
            $db = new Database();
            $r = $db->queryOne(
                "SELECT COUNT(*) AS total FROM notificacion WHERE id_persona = :id_persona AND leida = 0",
                ['id_persona' => $idPersona]
            );
            return (int)($r['total'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Elimina notificaciones con más de $dias días para no acumular indefinidamente.
     * Se ejecuta como máximo una vez por minuto por sesión para no ralentizar cada petición.
     */
    public static function purgarAntiguas(int $dias = 5): void
    {
        if ($dias < 1) {
            return;
        }
        $key = 'notif_purga_ts';
        $now = time();
        if (isset($_SESSION[$key]) && ($now - (int)$_SESSION[$key]) < 60) {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD(
                "DELETE FROM notificacion
                 WHERE fecha_creacion < DATE_SUB(NOW(), INTERVAL :dias DAY)
                   AND tipo <> 'recuperate_pronto_sandra'",
                ['dias' => $dias]
            );
            $_SESSION[$key] = $now;
        } catch (\Exception $e) {
            // silenciar
        }
    }

    /**
     * Crea notificaciones "dictamen_enviado" para tickets con dictamen no visto que aún no tengan notificación.
     * No se llama desde listar/contar para que nada fuerce leída=0. Solo usar desde cron o desde el flujo que envía dictamen si se necesita rellenar faltantes.
     */
    public static function syncDictamenNoVisto(int $idPersona): void
    {
        if ($idPersona < 1) {
            return;
        }
        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT t.id_ticket FROM ticket t
                 INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' AND d.fecha_visto_gestor IS NULL
                 WHERE t.id_persona_creador = :id_persona AND (t.activo = 1 OR t.activo IS NULL)",
                ['id_persona' => $idPersona]
            );
            if (empty($rows)) {
                return;
            }
            $ids = array_values(array_unique(array_map('intval', array_column($rows, 'id_ticket'))));
            $ids = array_filter($ids);
            if (empty($ids)) {
                return;
            }
            $placeholders = [];
            $params = ['id_persona' => $idPersona];
            foreach ($ids as $i => $id) {
                $pk = 'id_' . $i;
                $placeholders[] = ':' . $pk;
                $params[$pk] = $id;
            }
            $placeholdersStr = implode(',', $placeholders);
            // Solo crear notificaciones nuevas para tickets que aún no tienen. No tocar las que ya están leídas.
            $existentes = $db->queryAll(
                "SELECT id_ticket FROM notificacion WHERE id_persona = :id_persona AND tipo = 'dictamen_enviado' AND id_ticket IN ($placeholdersStr)",
                $params
            );
            $idsExistentes = array_flip(array_map('intval', array_column($existentes ?: [], 'id_ticket')));
            foreach ($ids as $idTicket) {
                if (isset($idsExistentes[$idTicket])) {
                    continue;
                }
                $db->CRUD(
                    "INSERT INTO notificacion (id_persona, tipo, mensaje, id_ticket, leida) VALUES (:id_persona, 'dictamen_enviado', 'Dictamen enviado (pendiente de revisar)', :id_ticket, 0)",
                    ['id_persona' => $idPersona, 'id_ticket' => $idTicket]
                );
            }
        } catch (\Exception $e) {
            // silenciar; no romper listado ni conteo
        }
    }

    /**
     * Diagnóstico para campana: sesión, tickets con dictamen no visto, filas en notificacion.
     * No lanza excepciones; devuelve info para depurar por qué no se activa la notificación.
     */
    public static function debugSync(int $idPersona): array
    {
        $out = [
            'id_persona'       => $idPersona,
            'tickets_no_visto' => [],
            'notificaciones'   => [],
            'tabla_notificacion_existe' => null,
            'tabla_ticket_existe'       => null,
            'tabla_dictamen_existe'     => null,
            'error'            => null,
        ];
        if ($idPersona < 1) {
            $out['error'] = 'id_persona inválido';
            return $out;
        }
        try {
            $db = new Database();
            $out['tabla_notificacion_existe'] = (bool) $db->queryOne("SELECT 1 FROM notificacion LIMIT 1");
            $out['tabla_ticket_existe']       = (bool) $db->queryOne("SELECT 1 FROM ticket LIMIT 1");
            $out['tabla_dictamen_existe']     = (bool) $db->queryOne("SELECT 1 FROM dictamen LIMIT 1");
        } catch (\Exception $e) {
            $out['error'] = 'Al verificar tablas: ' . $e->getMessage();
            return $out;
        }
        try {
            $rows = $db->queryAll(
                "SELECT t.id_ticket, t.id_persona_creador, t.activo FROM ticket t
                 INNER JOIN dictamen d ON d.id_ticket = t.id_ticket AND d.estado = 'enviado_al_gestor' AND d.fecha_visto_gestor IS NULL
                 WHERE t.id_persona_creador = :id_persona AND (t.activo = 1 OR t.activo IS NULL)",
                ['id_persona' => $idPersona]
            );
            $out['tickets_no_visto'] = is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            $out['error'] = 'Al buscar tickets: ' . $e->getMessage();
            return $out;
        }
        try {
            $db = new Database();
            $out['notificaciones'] = $db->queryAll(
                "SELECT id, tipo, mensaje, id_ticket, leida, fecha_creacion FROM notificacion WHERE id_persona = :id_persona ORDER BY fecha_creacion DESC LIMIT 20",
                ['id_persona' => $idPersona]
            ) ?: [];
        } catch (\Exception $e) {
            $out['error'] = ($out['error'] ? $out['error'] . '; ' : '') . 'Al listar notificaciones: ' . $e->getMessage();
        }
        return $out;
    }

    private static function asegurarTablaEventosEspeciales(Database $db): void
    {
        static $asegurada = false;
        if ($asegurada) {
            return;
        }

        $db->CRUD(
            "CREATE TABLE IF NOT EXISTS notificacion_evento_especial (
                id INT NOT NULL AUTO_INCREMENT,
                id_notificacion INT NOT NULL,
                id_persona INT NOT NULL,
                tipo VARCHAR(120) NOT NULL,
                evento VARCHAR(60) NOT NULL,
                fecha_evento DATETIME NOT NULL,
                user_agent VARCHAR(255) NULL,
                ip VARCHAR(45) NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_notificacion_evento (id_notificacion, evento),
                KEY idx_persona_tipo_evento (id_persona, tipo, evento, fecha_evento)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        try {
            $db->CRUD("ALTER TABLE notificacion_evento_especial MODIFY COLUMN fecha_evento DATETIME NOT NULL");
        } catch (\Exception $e) {
            // Si la columna ya esta en el formato correcto o no se puede alterar aqui, no rompemos el flujo.
        }
        $asegurada = true;
    }

    private static function ahoraCdmx(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
    }

    public static function registrarVistaEspecial(int $idNotificacion, int $idPersona, string $tipo, string $userAgent = '', string $ip = ''): bool
    {
        if ($idNotificacion < 1 || $idPersona < 1 || $tipo === '') {
            return false;
        }

        try {
            $db = new Database();
            self::asegurarTablaEventosEspeciales($db);

            $row = $db->queryOne(
                "SELECT id
                 FROM notificacion
                 WHERE id = :id
                   AND id_persona = :id_persona
                   AND tipo = :tipo
                 LIMIT 1",
                ['id' => $idNotificacion, 'id_persona' => $idPersona, 'tipo' => $tipo]
            );
            if (!$row) {
                return false;
            }

            $db->beginTransaction();
            try {
                $db->CRUD(
                    "INSERT IGNORE INTO notificacion_evento_especial
                        (id_notificacion, id_persona, tipo, evento, fecha_evento, user_agent, ip)
                     VALUES
                        (:id_notificacion, :id_persona, :tipo, 'visto_modal', :fecha_evento, :user_agent, :ip)",
                    [
                        'id_notificacion' => $idNotificacion,
                        'id_persona' => $idPersona,
                        'tipo' => $tipo,
                        'fecha_evento' => self::ahoraCdmx(),
                        'user_agent' => mb_substr($userAgent, 0, 255),
                        'ip' => mb_substr($ip, 0, 45),
                    ]
                );
                $db->CRUD(
                    "UPDATE notificacion SET leida = 1 WHERE id = :id AND id_persona = :id_persona AND tipo = :tipo",
                    ['id' => $idNotificacion, 'id_persona' => $idPersona, 'tipo' => $tipo]
                );
                $db->commit();
                return true;
            } catch (\Exception $e) {
                $db->rollback();
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function estadoEventoEspecial(int $idPersona, string $tipo): array
    {
        if ($idPersona < 1 || $tipo === '') {
            return ['existe' => false, 'visto' => false];
        }

        try {
            $db = new Database();
            self::asegurarTablaEventosEspeciales($db);
            $row = $db->queryOne(
                "SELECT n.id,
                        n.leida,
                        n.fecha_creacion,
                        e.fecha_evento AS fecha_visto
                 FROM notificacion n
                 LEFT JOIN notificacion_evento_especial e
                   ON e.id_notificacion = n.id
                  AND e.evento = 'visto_modal'
                 WHERE n.id_persona = :id_persona
                   AND n.tipo = :tipo
                 ORDER BY n.id DESC
                 LIMIT 1",
                ['id_persona' => $idPersona, 'tipo' => $tipo]
            );
            if (!$row) {
                return ['existe' => false, 'visto' => false];
            }

            return [
                'existe' => true,
                'id_notificacion' => (int)($row['id'] ?? 0),
                'leida' => (int)($row['leida'] ?? 0),
                'fecha_envio' => $row['fecha_creacion'] ?? null,
                'fecha_visto' => $row['fecha_visto'] ?? null,
                'zona_horaria' => 'America/Mexico_City',
                'visto' => !empty($row['fecha_visto']),
            ];
        } catch (\Exception $e) {
            return ['existe' => false, 'visto' => false, 'error' => $e->getMessage()];
        }
    }

    public static function marcarLeida(int $idNotificacion, int $idPersona): bool
    {
        if ($idNotificacion < 1 || $idPersona < 1) {
            return false;
        }
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE notificacion
                 SET leida = 1
                 WHERE id = :id
                   AND id_persona = :id_persona
                   AND tipo <> 'recuperate_pronto_sandra'",
                ['id' => $idNotificacion, 'id_persona' => $idPersona]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Marca todas las notificaciones de una persona como leídas.
     */
    public static function marcarTodasLeidas(int $idPersona): bool
    {
        if ($idPersona < 1) {
            return false;
        }
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE notificacion
                 SET leida = 1
                 WHERE id_persona = :id_persona
                   AND tipo <> 'recuperate_pronto_sandra'",
                ['id_persona' => $idPersona]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
