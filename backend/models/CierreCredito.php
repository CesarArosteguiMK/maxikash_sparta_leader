<?php

namespace Models;

use Core\Model;
use Core\Database;
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
     * Convenios con estatus 'saldado' — fuente principal de Cierre de Crédito.
     * Trae datos del convenio, producto, progreso de amortización y despacho asignado.
     */
    public static function getEnviadoFinalizado(): array
    {
        try {
            $db   = new Database();
            $rows = $db->queryAll(
                "SELECT
                    cc.id,
                    cc.id_credito,
                    cc.nombre_cliente,
                    cc.id_producto_convenio,
                    pc.nombre                      AS nombre_producto,
                    cc.adeudo_total_original,
                    cc.porcentaje_descuento,
                    cc.descuento_monto,
                    cc.total_a_pagar,
                    cc.monto_adicional,
                    cc.pago_inicial_monto,
                    cc.numero_semanas,
                    cc.pago_semanal,
                    cc.fecha_acuerdo,
                    cc.fecha_primer_pago,
                    cc.fecha_ultimo_pago,
                    cc.estatus,
                    cc.usuario_alta,
                    cc.fecha_alta,
                    (SELECT COUNT(*)
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id
                       AND a.estatus_pago = 'pagado')  AS cuotas_pagadas,
                    (SELECT TRIM(CONCAT_WS(' ',
                            per.nombres, per.segundo_nombre,
                            per.apellidop, per.apellidom))
                     FROM asigna_creditos_despacho acd
                     INNER JOIN despachos d   ON d.id  = acd.id_despacho
                     INNER JOIN persona   per ON per.id = d.id_persona
                     WHERE acd.id_credito = cc.id_credito
                       AND acd.estatus    = '1'
                     ORDER BY acd.fecha_alta DESC
                     LIMIT 1)                          AS nombre_despacho
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.estatus = 'completado'
                 ORDER BY cc.fecha_alta DESC"
            );
            return self::resultado(true, 'Convenios saldados.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener convenios saldados.', [], $e->getMessage());
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
