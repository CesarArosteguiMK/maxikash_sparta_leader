<?php

namespace Models;

use Core\Model;
use Core\Database;

/**
 * Perfil extendido por persona: nombre, apellidos, teléfono, correo, dirección, foto.
 * Vinculado a persona por id_persona (1:1). BD __SPARTA_SECRET_REDACTED__.
 */
class Perfil extends Model
{
    private static function asegurarTablaInvitacionPareja(Database $db): void
    {
        $db->CRUD(
            "CREATE TABLE IF NOT EXISTS perfil_invitacion_pareja (
                id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                id_emisor INT NOT NULL,
                id_destinataria INT NOT NULL,
                estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
                notificado_emisor TINYINT(1) NOT NULL DEFAULT 0,
                fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_respuesta DATETIME NULL,
                INDEX idx_destinataria_estado (id_destinataria, estado),
                INDEX idx_emisor_notificado (id_emisor, notificado_emisor)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public static function crearInvitacionPareja(int $idEmisor, int $idDestinataria): bool
    {
        try {
            $db = new Database();
            self::asegurarTablaInvitacionPareja($db);
            $db->CRUD(
                "UPDATE perfil_invitacion_pareja
                    SET estado = 'cerrada', fecha_respuesta = NOW()
                  WHERE id_emisor = :id_emisor AND id_destinataria = :id_destinataria AND estado = 'pendiente'",
                ['id_emisor' => $idEmisor, 'id_destinataria' => $idDestinataria]
            );
            $db->CRUD(
                "INSERT INTO perfil_invitacion_pareja (id_emisor, id_destinataria, estado)
                 VALUES (:id_emisor, :id_destinataria, 'pendiente')",
                ['id_emisor' => $idEmisor, 'id_destinataria' => $idDestinataria]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getInvitacionParejaPendiente(int $idDestinataria): ?array
    {
        try {
            $db = new Database();
            self::asegurarTablaInvitacionPareja($db);
            return $db->queryOne(
                "SELECT id FROM perfil_invitacion_pareja
                  WHERE id_destinataria = :id_destinataria AND estado = 'pendiente'
                  ORDER BY id DESC LIMIT 1",
                ['id_destinataria' => $idDestinataria]
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function responderInvitacionPareja(int $idInvitacion, int $idDestinataria, string $estado): bool
    {
        if (!in_array($estado, ['aceptada', 'cerrada'], true)) {
            return false;
        }
        try {
            $db = new Database();
            self::asegurarTablaInvitacionPareja($db);
            $db->CRUD(
                "UPDATE perfil_invitacion_pareja
                    SET estado = :estado, fecha_respuesta = NOW(), notificado_emisor = 0
                  WHERE id = :id AND id_destinataria = :id_destinataria AND estado = 'pendiente'",
                ['estado' => $estado, 'id' => $idInvitacion, 'id_destinataria' => $idDestinataria]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function consumirRespuestasInvitacionPareja(int $idEmisor): array
    {
        try {
            $db = new Database();
            self::asegurarTablaInvitacionPareja($db);
            $rows = $db->queryAll(
                "SELECT id, estado FROM perfil_invitacion_pareja
                  WHERE id_emisor = :id_emisor AND estado IN ('aceptada', 'cerrada') AND notificado_emisor = 0
                  ORDER BY id ASC",
                ['id_emisor' => $idEmisor]
            ) ?: [];
            if ($rows) {
                $ids = array_map(static fn ($row) => (int) $row['id'], $rows);
                $params = [];
                $marks = [];
                foreach ($ids as $index => $id) {
                    $key = 'id_' . $index;
                    $marks[] = ':' . $key;
                    $params[$key] = $id;
                }
                $db->CRUD('UPDATE perfil_invitacion_pareja SET notificado_emisor = 1 WHERE id IN (' . implode(',', $marks) . ')', $params);
            }
            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function eliminarNotificacionesInvitacionPareja(int $idPersona): void
    {
        try {
            $db = new Database();
            $db->CRUD(
                "DELETE FROM notificacion WHERE id_persona = :id_persona AND tipo LIKE 'invitacion_pareja%'",
                ['id_persona' => $idPersona]
            );
        } catch (\Exception $e) {
            // Esta limpieza no debe afectar el flujo de invitacion directa.
        }
    }

    /**
     * Busca colaboradores por el inicio de su nombre completo sin guardar IDs
     * de personas directamente en el codigo.
     */
    public static function buscarPersonasPorNombreInicio(string $nombre): array
    {
        $nombre = strtoupper(trim(preg_replace('/\s+/', ' ', $nombre)));
        if ($nombre === '') {
            return [];
        }

        try {
            $db = new Database();
            return $db->queryAll(
                "SELECT id, TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre_completo
                   FROM persona
                  WHERE UPPER(TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom))) LIKE :nombre
                  ORDER BY id ASC
                  LIMIT 5",
                ['nombre' => $nombre . '%']
            ) ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene datos de persona por id (tabla persona, __SPARTA_SECRET_REDACTED__).
     */
    public static function getPersonaById($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return null;
        }
        $query = "SELECT id, nombres, segundo_nombre, apellidop, apellidom, user_name
                  FROM persona WHERE id = :id LIMIT 1";
        try {
            $db = new Database();
            return $db->queryOne($query, ['id' => $idPersona]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtiene el perfil por id_persona (tabla perfil, __SPARTA_SECRET_REDACTED__).
     */
    public static function getByPersonaId($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return null;
        }
        $query = "SELECT id, id_persona, nombre, apellidopat, apellidomat, telefono, correo, direccion, username, foto, fecha_actualizacion
                  FROM perfil WHERE id_persona = :id_persona LIMIT 1";
        try {
            $db = new Database();
            return $db->queryOne($query, ['id_persona' => $idPersona]);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Guarda o actualiza perfil. Si no existe fila para id_persona, inserta; si existe, actualiza.
     * $datos: nombre, apellidopat, apellidomat, telefono, correo, direccion, username, pass (opcional), foto (ruta opcional).
     */
    public static function guardar($idPersona, array $datos)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'Id de persona inválido.');
        }
        try {
            $db = new Database();
            $existe = self::getByPersonaId($idPersona);
            $keys = ['nombre', 'apellidopat', 'apellidomat', 'telefono', 'correo', 'direccion', 'username', 'foto'];
            $campos = ['id_persona' => $idPersona];
            foreach ($keys as $k) {
                if (array_key_exists($k, $datos)) {
                    $campos[$k] = $datos[$k];
                }
            }
            if (!empty($datos['pass'])) {
                $campos['pass'] = $datos['pass'];
            }
            if ($existe) {
                $updateKeys = array_filter(array_keys($campos), function ($k) { return $k !== 'id_persona'; });
                if (empty($updateKeys)) {
                    return self::resultado(true, 'Sin cambios.');
                }
                $sets = [];
                $params = ['id_persona' => $idPersona];
                foreach ($updateKeys as $k) {
                    $sets[] = "`$k` = :$k";
                    $params[$k] = $campos[$k];
                }
                $sql = "UPDATE perfil SET " . implode(', ', $sets) . " WHERE id_persona = :id_persona";
                $db->CRUD($sql, $params);
            } else {
                $cols = array_keys($campos);
                $placeholders = array_map(function ($c) { return ':' . $c; }, $cols);
                $sql = "INSERT INTO perfil (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $placeholders) . ")";
                $db->CRUD($sql, $campos);
            }
            return self::resultado(true, 'Perfil guardado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar el perfil.', null, $e->getMessage());
        }
    }

    /**
     * Actualiza solo la ruta de la foto.
     */
    public static function actualizarFoto($idPersona, $rutaFoto)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'Id de persona inválido.');
        }
        try {
            $db = new Database();
            $existe = self::getByPersonaId($idPersona);
            if ($existe) {
                $db->CRUD("UPDATE perfil SET foto = :foto WHERE id_persona = :id_persona", [
                    'foto' => $rutaFoto,
                    'id_persona' => $idPersona,
                ]);
            } else {
                $db->CRUD("INSERT INTO perfil (id_persona, foto) VALUES (:id_persona, :foto)", [
                    'id_persona' => $idPersona,
                    'foto' => $rutaFoto,
                ]);
            }
            return self::resultado(true, 'Foto actualizada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar la foto.', null, $e->getMessage());
        }
    }

    /**
     * Elimina la foto de perfil: pone foto = null en BD para el id_persona.
     */
    public static function eliminarFoto($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'Id de persona inválido.');
        }
        try {
            $db = new Database();
            $existe = self::getByPersonaId($idPersona);
            if ($existe) {
                $db->CRUD("UPDATE perfil SET foto = NULL WHERE id_persona = :id_persona", [
                    'id_persona' => $idPersona,
                ]);
            }
            return self::resultado(true, 'Foto eliminada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar la foto.', null, $e->getMessage());
        }
    }
}
