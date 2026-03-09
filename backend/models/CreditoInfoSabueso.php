<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Datos extraídos para Sabueso (ej. información de ingresos / donde trabaja desde FAD_DOC).
 * Tabla: credito_info_sabueso (esquema __SPARTA_SECRET_REDACTED__).
 */
class CreditoInfoSabueso extends Model
{
    /**
     * Obtiene la fila de info Sabueso para un id_credito.
     *
     * @param int $idCredito
     * @return array|null ['informacion_ingresos', 'empresa', 'empleado', 'ingreso_mensual_neto', 'telefono', 'fecha_extraccion'] o null
     */
    public static function getPorCredito($idCredito)
    {
        $id = (int) $idCredito;
        if ($id < 1) {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                'SELECT id_credito, informacion_ingresos, empresa, empleado, ingreso_mensual_neto, telefono, fecha_extraccion FROM credito_info_sabueso WHERE id_credito = :id',
                ['id' => $id]
            );
            return $row ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Guarda o actualiza la información de ingresos para un crédito.
     *
     * @param int    $idCredito
     * @param string $informacionIngresos
     * @param string|null $empresa
     * @param string|null $empleado
     * @param string|null $ingresoMensualNeto
     * @param string|null $telefono
     * @return array ['success' => bool, 'mensaje' => string]
     */
    public static function guardar($idCredito, $informacionIngresos, $empresa = null, $empleado = null, $ingresoMensualNeto = null, $telefono = null)
    {
        $id = (int) $idCredito;
        if ($id < 1) {
            return self::resultado(false, 'ID de crédito inválido.');
        }
        $texto = is_string($informacionIngresos) ? trim($informacionIngresos) : '';
        $empresa = $empresa !== null ? trim((string) $empresa) : null;
        $empleado = $empleado !== null ? trim((string) $empleado) : null;
        $ingreso = $ingresoMensualNeto !== null ? trim((string) $ingresoMensualNeto) : null;
        $tel = $telefono !== null ? trim((string) $telefono) : null;
        try {
            $db = new Database();
            $existe = $db->queryOne('SELECT 1 FROM credito_info_sabueso WHERE id_credito = :id', ['id' => $id]);
            $now = date('Y-m-d H:i:s');
            if ($existe) {
                $db->CRUD(
                    'UPDATE credito_info_sabueso SET informacion_ingresos = :txt, empresa = :empresa, empleado = :empleado, ingreso_mensual_neto = :ingreso, telefono = :telefono, fecha_extraccion = :fecha, updated_at = :fecha WHERE id_credito = :id',
                    ['txt' => $texto, 'empresa' => $empresa, 'empleado' => $empleado, 'ingreso' => $ingreso, 'telefono' => $tel, 'fecha' => $now, 'id' => $id]
                );
            } else {
                $db->CRUD(
                    'INSERT INTO credito_info_sabueso (id_credito, informacion_ingresos, empresa, empleado, ingreso_mensual_neto, telefono, fecha_extraccion) VALUES (:id, :txt, :empresa, :empleado, :ingreso, :telefono, :fecha)',
                    ['id' => $id, 'txt' => $texto, 'empresa' => $empresa, 'empleado' => $empleado, 'ingreso' => $ingreso, 'telefono' => $tel, 'fecha' => $now]
                );
            }
            return self::resultado(true, 'OK');
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al guardar: ' . $e->getMessage());
        }
    }
}
