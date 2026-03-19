<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Formularios de validación (plantillas de cuestionario).
 * Cada formulario tiene nombre, activo y puede tener preguntas asociadas (id_formulario en formulario_validacion_pregunta).
 */
class FormularioValidacion extends Model
{
    /**
     * Lista todos los formularios para el panel (del usuario o todos si es admin).
     */
    public static function listar(int $idPersona): array
    {
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT id, nombre, descripcion, activo, id_persona_creador, fecha_creacion
                FROM formulario_validacion
                ORDER BY fecha_creacion DESC, id DESC
            ");
            return is_array($r) ? $r : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene un formulario por ID.
     */
    public static function obtenerPorId(int $id): ?array
    {
        try {
            $db = new Database();
            $r = $db->queryOne("SELECT id, nombre, descripcion, activo, id_persona_creador, fecha_creacion FROM formulario_validacion WHERE id = :id", ['id' => $id]);
            return $r ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Crear nuevo formulario.
     */
    public static function crear(string $nombre, int $idPersona): array
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return self::resultado(false, 'El nombre del formulario es obligatorio.', null);
        }
        try {
            $db = new Database();
            $db->CRUD("
                INSERT INTO formulario_validacion (nombre, activo, id_persona_creador)
                VALUES (:nombre, 1, :id_persona)
            ", ['nombre' => $nombre, 'id_persona' => $idPersona]);
            $row = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $nuevoId = $row ? (int) $row['id'] : 0;
            return self::resultado(true, 'Formulario creado.', ['id' => $nuevoId]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al crear.', null, $e->getMessage());
        }
    }

    /**
     * Actualizar nombre y descripción de un formulario.
     */
    public static function actualizar(int $id, string $nombre, string $descripcion, int $idPersona): array
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return self::resultado(false, 'El nombre es obligatorio.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id FROM formulario_validacion WHERE id = :id", ['id' => $id]);
            if (!$row) {
                return self::resultado(false, 'Formulario no encontrado.', null);
            }
            $db->CRUD("UPDATE formulario_validacion SET nombre = :nombre, descripcion = :descripcion WHERE id = :id", [
                'nombre' => $nombre,
                'descripcion' => trim($descripcion),
                'id' => $id,
            ]);
            return self::resultado(true, 'Formulario actualizado.', null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    /**
     * Activar o desactivar formulario (activo = 1 o 0).
     */
    public static function toggleActivo(int $id, int $activo, int $idPersona): array
    {
        $activo = $activo ? 1 : 0;
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id FROM formulario_validacion WHERE id = :id", ['id' => $id]);
            if (!$row) {
                return self::resultado(false, 'Formulario no encontrado.', null);
            }
            $db->CRUD("UPDATE formulario_validacion SET activo = :activo WHERE id = :id", ['activo' => $activo, 'id' => $id]);
            return self::resultado(true, $activo ? 'Formulario habilitado.' : 'Formulario inhabilitado.', ['activo' => $activo]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar formulario (y opcionalmente sus preguntas asociadas).
     */
    public static function eliminar(int $id, int $idPersona): array
    {
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id FROM formulario_validacion WHERE id = :id", ['id' => $id]);
            if (!$row) {
                return self::resultado(false, 'Formulario no encontrado.', null);
            }
            $db->CRUD("UPDATE formulario_validacion_pregunta SET id_formulario = NULL WHERE id_formulario = :id", ['id' => $id]);
            $db->CRUD("DELETE FROM formulario_validacion WHERE id = :id", ['id' => $id]);
            return self::resultado(true, 'Formulario eliminado.', null);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar.', null, $e->getMessage());
        }
    }
}
