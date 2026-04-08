<?php

namespace Models;

use Core\Model;
use Core\DatabaseSegundometro;

class CierreCredito extends Model
{
    // ─────────────────────────────────────────────
    // LISTADOS POR ESTATUS
    // ─────────────────────────────────────────────

    /**
     * Devuelve todos los registros con estatus 'en_proceso'.
     */
    public static function getEnProceso(): array
    {
        try {
            $db   = new DatabaseSegundometro();
            $rows = $db->queryAll(
                "SELECT id, id_credito, nombre_cliente, estatus,
                        fecha_alta, usuario_alta,
                        fecha_actualizacion, usuario_actualizacion
                 FROM estatus_cierre_final
                 WHERE estatus = 'en_proceso'
                 ORDER BY fecha_alta DESC"
            );
            return self::resultado(true, 'Registros en proceso.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener registros en proceso.', [], $e->getMessage());
        }
    }

    /**
     * Devuelve todos los registros con estatus 'enviado_finalizado'.
     */
    public static function getEnviadoFinalizado(): array
    {
        try {
            $db   = new DatabaseSegundometro();
            $rows = $db->queryAll(
                "SELECT id, id_credito, nombre_cliente, estatus,
                        fecha_alta, usuario_alta,
                        fecha_actualizacion, usuario_actualizacion
                 FROM estatus_cierre_final
                 WHERE estatus = 'enviado_finalizado'
                 ORDER BY fecha_alta DESC"
            );
            return self::resultado(true, 'Registros enviados / finalizados.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener registros finalizados.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // CREAR
    // ─────────────────────────────────────────────

    /**
     * Inserta un nuevo registro en estatus_cierre_final.
     *
     * @param array $datos  Llaves: id_credito, nombre_cliente, estatus, usuario_alta
     */
    public static function crear(array $datos): array
    {
        try {
            $db = new DatabaseSegundometro();
            $db->CRUD(
                "INSERT INTO estatus_cierre_final
                    (id_credito, nombre_cliente, estatus, usuario_alta, usuario_actualizacion)
                 VALUES
                    (:id_credito, :nombre_cliente, :estatus, :usuario_alta, :usuario_alta)",
                [
                    'id_credito'     => (int) $datos['id_credito'],
                    'nombre_cliente' => $datos['nombre_cliente'],
                    'estatus'        => $datos['estatus'] ?? 'en_proceso',
                    'usuario_alta'   => $datos['usuario_alta'],
                ]
            );
            return self::resultado(true, 'Registro creado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al crear el registro.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // CAMBIAR ESTATUS
    // ─────────────────────────────────────────────

    /**
     * Actualiza el estatus de un registro existente.
     *
     * @param int    $id      PK de estatus_cierre_final
     * @param string $estatus 'en_proceso' | 'enviado_finalizado'
     * @param string $usuario Nombre del usuario que realiza el cambio
     */
    public static function cambiarEstatus(int $id, string $estatus, string $usuario): array
    {
        $permitidos = ['en_proceso', 'enviado_finalizado'];
        if (!in_array($estatus, $permitidos, true)) {
            return self::resultado(false, 'Estatus no válido.');
        }

        try {
            $db = new DatabaseSegundometro();
            $db->CRUD(
                "UPDATE estatus_cierre_final
                 SET estatus = :estatus, usuario_actualizacion = :usuario
                 WHERE id = :id",
                ['estatus' => $estatus, 'usuario' => $usuario, 'id' => $id]
            );
            return self::resultado(true, 'Estatus actualizado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar el estatus.', [], $e->getMessage());
        }
    }
}
