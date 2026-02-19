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
