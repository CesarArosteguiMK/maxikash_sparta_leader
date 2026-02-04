<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseLegacy;

class Gestiones extends Model
{
    public static function getAllGestionesaa($credito, $nombre)
    {
        $mysqli = new Database();

        $query = <<<SQL
        SELECT 'Sky Logic' as app, id, id_team, team_supervisor, id_base, nombre_base, fecha_carga_base,
               id_registro, id_key, estatus, usuario_asignado, nombre_cliente, id_credito,
               cuenta_clabe, nombre_completo_cliente, pago_semanal, pagos_vencidos,
               deuda_total, codigo_gestor, usuario, telefono_celular, cp, direccion,
               direccion_ine, direccion_actual, geolocalizacion, direccion_geo,
               donde_firma, referencia_personal1, parentesco1, telefono_referencia1,
               referencia_personal2, parentesco2, telefono_referencia2, contacto,
               medio_contactacion_ccc, medio_contactacion_campo, dictamen_campo,
               dictamen_ccc, promesa_pago, motivo_negativa, porque_atraso_pago,
               con_quien_mala_experiencia, fecha_hora, kilometraje, numero_serie,
               marca_modelo, actualizacion_direccion, actualizacion_telefono,
               comentarios_generales, foto1, foto2, foto3, adjunto, video, device_imei,
               fecha_sistema, fecha_dispositivo, longitud, latitud, ubicacion_usuario,
               fake_gps, secure_area, images
        FROM base_clientes
        WHERE 1=1
SQL;

        if (!empty($nombre)) {
            $nombre = "%{$nombre}%";
            $query .= " AND nombre_completo_cliente LIKE '{$nombre}'";
        } else {
            $query .= " AND id_credito = '{$credito}'";
        }

        $query .= " ORDER BY base_clientes.fecha_dispositivo DESC";

        $res_u = $mysqli->queryAll($query);



        return $res_u;
    }

    public static function getAllGestiones($credito, $nombre = '')
    {
        $legacy = self::getGestionesLegacy($credito);
        $sky    = self::getAllGestionesSkyLogic($credito, $nombre);

        // LEGACY primero, luego SKY
        $resultado = array_merge($legacy, $sky);

        // ordenar por fecha_dispositivo DESC
        usort($resultado, function ($a, $b) {
            return strtotime($b['fecha_dispositivo']) <=> strtotime($a['fecha_dispositivo']);
        });

        return $resultado;
    }

    public static function getGestionesLegacy($credito)
    {
        $mysqli = new DatabaseLegacy(); // conexión LEGACY

        $query = <<<SQL
    SELECT 
        'LEGACY' AS app,
        '' AS id,
        '' AS id_team,
        ut.name AS team_supervisor,
        c.id AS id_base,
        c.name AS nombre_base,
        c.start_date AS fecha_carga_base,
        '' AS id_registro,
        '' AS id_key,
        '' AS estatus,
        u.name AS usuario_asignado,
        u.name AS nombre_cliente,
        t.credit_number AS id_credito,
        '' AS cuenta_clabe,
        u.name AS nombre_completo_cliente,
        '' AS pago_semanal,
        '' AS pagos_vencidos,
        '' AS deuda_total,
        '' AS codigo_gestor,
        u.name AS usuario,
        '' AS telefono_celular,
        '' AS cp,
        '' AS direccion,
        '' AS direccion_ine,
        '' AS direccion_actual,
        '' AS geolocalizacion,
        '' AS direccion_geo,
        '' AS donde_firma,
        '' AS referencia_personal1,
        '' AS parentesco1,
        '' AS telefono_referencia1,
        '' AS referencia_personal2,
        '' AS parentesco2,
        '' AS telefono_referencia2,
        '' AS contacto,
        '' AS medio_contactacion_ccc,
        '' AS medio_contactacion_campo,
        '' AS dictamen_campo,
        o.nombre_opcion AS dictamen_ccc,
        '' AS promesa_pago,
        '' AS motivo_negativa,
        '' AS porque_atraso_pago,
        '' AS con_quien_mala_experiencia,
        NOW() AS fecha_hora,
        '' AS kilometraje,
        '' AS numero_serie,
        '' AS marca_modelo,
        '' AS actualizacion_direccion,
        '' AS actualizacion_telefono,
        '' AS comentarios_generales,
        '' AS foto1,
        '' AS foto2,
        '' AS foto3,
        '' AS adjunto,
        '' AS video,
        '' AS device_imei,
        NOW() AS fecha_sistema,
        d.created_at AS fecha_dispositivo,
        '' AS longitud,
        '' AS latitud,
        '' AS ubicacion_usuario,
        '' AS fake_gps,
        '' AS secure_area,
        '' AS images
    FROM tasks t
    INNER JOIN campaigns c ON t.campaign_id = c.id
    INNER JOIN users u ON t.current_user_id = u.id
    INNER JOIN dictums d ON d.task_id = t.id
    INNER JOIN opcionesdictamen o ON o.id = d.opciondictamen_id
    LEFT JOIN users ut ON ut.id = 
        CASE
            WHEN u.supervisor_id IS NOT NULL THEN u.supervisor_id
            WHEN u.subgerente_id IS NOT NULL THEN u.subgerente_id
            WHEN u.gerente_id IS NOT NULL THEN u.gerente_id
            ELSE u.subdirector_id
        END
    WHERE t.credit_number = '{$credito}'
    ORDER BY d.created_at DESC
SQL;

        $legacyData = $mysqli->queryAll($query);
        
        // Si no hay datos de Legacy, retornar vacío
        if (empty($legacyData)) {
            return [];
        }

        // Obtener datos complementarios de Sky Logic
        $skyData = self::getSkyLogicComplementData($credito);
        
        // Si hay datos de Sky Logic, completar los campos vacíos de Legacy
        if (!empty($skyData)) {
            foreach ($legacyData as &$legacyRow) {
                $legacyRow = self::mergeWithSkyLogicData($legacyRow, $skyData);
            }
        }

        return $legacyData;
    }

    /**
     * Obtiene datos complementarios de Sky Logic para completar Legacy
     */
    private static function getSkyLogicComplementData($credito)
    {
        $db = new Database();
        
        $query = <<<SQL
    SELECT 
        telefono_celular, cp, direccion, cuenta_clabe,
        pago_semanal, pagos_vencidos, deuda_total,
        referencia_personal1, parentesco1, telefono_referencia1,
        referencia_personal2, parentesco2, telefono_referencia2,
        comentarios_generales, geolocalizacion, direccion_geo,
        longitud, latitud, medio_contactacion_campo, dictamen_campo,
        estatus, direccion_ine, direccion_actual,
        promesa_pago, porque_atraso_pago, motivo_negativa
    FROM base_clientes 
    WHERE id_credito = '{$credito}'
    ORDER BY fecha_dispositivo DESC
    LIMIT 1
SQL;

        $result = $db->queryAll($query);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Combina datos de Legacy con datos de Sky Logic
     * Prioriza datos de Sky Logic para campos vacíos en Legacy
     */
    private static function mergeWithSkyLogicData($legacyRow, $skyData)
    {
        // Campos a completar desde Sky Logic si están vacíos en Legacy
        $fieldsToMerge = [
            'telefono_celular', 'cp', 'direccion', 'cuenta_clabe',
            'pago_semanal', 'pagos_vencidos', 'deuda_total',
            'referencia_personal1', 'parentesco1', 'telefono_referencia1',
            'referencia_personal2', 'parentesco2', 'telefono_referencia2',
            'comentarios_generales', 'geolocalizacion', 'direccion_geo',
            'longitud', 'latitud', 'medio_contactacion_campo', 'dictamen_campo',
            'estatus', 'direccion_ine', 'direccion_actual',
            'promesa_pago', 'porque_atraso_pago', 'motivo_negativa'
        ];

        foreach ($fieldsToMerge as $field) {
            // Si el campo en Legacy está vacío y existe en Sky Logic con datos
            if (empty($legacyRow[$field]) && !empty($skyData[$field])) {
                $legacyRow[$field] = $skyData[$field];
            }
        }

        return $legacyRow;
    }

    public static function getAllGestionesSkyLogic($credito, $nombre)
    {
        $mysqli = new Database();

        $query = <<<SQL
    SELECT 
        'Sky Logic *' as app, 
        id, id_team, team_supervisor, id_base, nombre_base, fecha_carga_base,
        id_registro, id_key, estatus, usuario_asignado, nombre_cliente, id_credito,
        cuenta_clabe, nombre_completo_cliente, pago_semanal, pagos_vencidos,
        deuda_total, codigo_gestor, usuario, telefono_celular, cp, direccion,
        direccion_ine, direccion_actual, geolocalizacion, direccion_geo,
        donde_firma, referencia_personal1, parentesco1, telefono_referencia1,
        referencia_personal2, parentesco2, telefono_referencia2, contacto,
        medio_contactacion_ccc, medio_contactacion_campo, dictamen_campo,
        dictamen_ccc, promesa_pago, motivo_negativa, porque_atraso_pago,
        con_quien_mala_experiencia, fecha_hora, kilometraje, numero_serie,
        marca_modelo, actualizacion_direccion, actualizacion_telefono,
        comentarios_generales, foto1, foto2, foto3, adjunto, video, device_imei,
        fecha_sistema, fecha_dispositivo, longitud, latitud, ubicacion_usuario,
        fake_gps, secure_area, images
    FROM base_clientes
    WHERE 1=1
SQL;

        if (!empty($nombre)) {
            $nombre = "%{$nombre}%";
            $query .= " AND nombre_completo_cliente LIKE '{$nombre}'";
        } else {
            $query .= " AND id_credito = '{$credito}'";
        }

        $query .= " ORDER BY fecha_dispositivo DESC";

        return $mysqli->queryAll($query);
    }


}
