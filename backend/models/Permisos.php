<?php

namespace Models;

use Core\Model;
use Core\Database;

class Permisos extends Model
{
    /**
     * Lista personas con resumen de puestos asignados
     */
    public static function listarPersonasConPuestos()
    {
        $query = <<<SQL
            SELECT
                p.id,
                CONCAT(p.nombres, ' ', p.apellidop, ' ', p.apellidom) AS nombre_completo,
                GROUP_CONCAT(pt.nombre ORDER BY pt.nombre SEPARATOR ', ') AS puestos
            FROM persona p
            LEFT JOIN privilegios_departamento pd ON pd.idPersona = p.id
            LEFT JOIN puesto pt ON pt.id = pd.idPuesto
            GROUP BY p.id
            ORDER BY nombre_completo
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);

            return self::resultado(
                true,
                'Personas encontradas.',
                $r
            );
        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener personas.',
                null,
                $e->getMessage()
            );
        }
    }

    /**
     * Obtiene todos los puestos y marca los asignados a la persona
     */
    public static function obtenerPuestosPersona($idPersona)
    {
        $query = <<<SQL
            SELECT
                pt.id,
                pt.nombre,
                CASE 
                    WHEN pd.id IS NULL THEN 0
                    ELSE 1
                END AS asignado
            FROM puesto pt
            LEFT JOIN privilegios_departamento pd
                ON pd.idPuesto = pt.id
               AND pd.idPersona = :idPersona
            WHERE pt.activo = 1
            ORDER BY pt.nombre
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query, [
                'idPersona' => $idPersona
            ]);

            return self::resultado(
                true,
                'Puestos obtenidos.',
                $r
            );
        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener puestos.',
                null,
                $e->getMessage()
            );
        }
    }

    /**
     * Guarda permisos (reemplaza todos)
     */
    public static function guardarPermisos($idPersona, $puestos)
    {
        try {
            $db = new Database();
            $db->beginTransaction();

            // Eliminar permisos actuales
            $delete = <<<SQL
                DELETE FROM privilegios_departamento
                WHERE idPersona = :idPersona
            SQL;
            $db->execute($delete, [
                'idPersona' => $idPersona
            ]);

            // Insertar nuevos permisos
            if (!empty($puestos)) {
                $insert = <<<SQL
                    INSERT INTO privilegios_departamento (idPersona, idPuesto)
                    VALUES (:idPersona, :idPuesto)
                SQL;

                foreach ($puestos as $idPuesto) {
                    $db->execute($insert, [
                        'idPersona' => $idPersona,
                        'idPuesto'  => $idPuesto
                    ]);
                }
            }

            $db->commit();

            return self::resultado(
                true,
                'permisos guardados correctamente.',
                null
            );
        } catch (\Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }

            return self::resultado(
                false,
                'Error al guardar permisos.',
                null,
                $e->getMessage()
            );
        }
    }
}
