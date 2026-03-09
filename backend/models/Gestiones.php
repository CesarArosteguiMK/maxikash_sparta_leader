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

        $params = [];
        if (!empty($nombre)) {
            $params['nombre'] = '%' . $nombre . '%';
            $query .= " AND nombre_completo_cliente LIKE :nombre";
        } else {
            $params['credito'] = $credito;
            $query .= " AND id_credito = :credito";
        }
        $query .= " ORDER BY base_clientes.fecha_dispositivo DESC";

        $res_u = $mysqli->queryAll($query, $params);



        return $res_u;
    }

    public static function getAllGestiones($credito, $nombre = '')
    {
        $legacy = self::getGestionesLegacy($credito);
        $sky    = self::getAllGestionesSkyLogic($credito, $nombre);

        if (!is_array($legacy)) {
            $legacy = [];
        }
        if (!is_array($sky)) {
            $sky = [];
        }

        // LEGACY primero, luego SKY
        $resultado = array_merge($legacy, $sky);

        // Ordenar por fecha_dispositivo DESC
        usort($resultado, function ($a, $b) {
            return strtotime($b['fecha_dispositivo'] ?? 0) <=> strtotime($a['fecha_dispositivo'] ?? 0);
        });

        return $resultado;
    }

    /**
     * Gestiones desde __SPARTA_SECRET_REDACTED__ (Legacy): tasks + dictums + opcionesdictamen.
     * Coordenadas del gestor: dictums.lat, dictums.lng (al registrar el dictamen).
     * tasks también tiene lat/lng; si dictums viene NULL se puede usar COALESCE(d.lat, t.lat).
     */
    public static function getGestionesLegacy($credito)
    {
        $mysqli = new DatabaseLegacy(); // conexión LEGACY

        $query = <<<SQL
        SELECT
        'LEGACY' AS app,
        '' AS id,
        '' AS id_team,
        ut.name AS team_supervisor,
        '' AS id_base,
        lh.campana AS nombre_base,
        lh.fecha_dictamen AS fecha_carga_base,
        '' AS id_registro,
        '' AS id_key,
        '' AS estatus,
        lh.nombre_usuario AS usuario_asignado,
        '' AS nombre_cliente,
        lh.id_credit AS id_credito,
        '' AS cuenta_clabe,
        '' AS nombre_completo_cliente,
        '' AS pago_semanal,
        '' AS pagos_vencidos,
        '' AS deuda_total,
        '' AS codigo_gestor,
        lh.nombre_usuario AS usuario,
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
        CASE
            WHEN lh.contacto = 'telefono' THEN 'telefono'
            WHEN lh.contacto = 'whatsapp' THEN 'telefono'
            ELSE                               'campo'
        END AS contacto,
        CASE
            WHEN lh.contacto = 'telefono' THEN 'llamada telefonica'
            WHEN lh.contacto = 'whatsapp' THEN 'Whatsapp'
            WHEN lh.contacto = 'campo'    THEN '0'
            ELSE ''
        END AS medio_contactacion_ccc,
        CASE
            WHEN lh.contacto = 'campo' THEN 'domicilio del cliente'
            ELSE '0'
        END AS medio_contactacion_campo,
        CASE
            WHEN lh.contacto = 'campo' THEN lh.comentarios_generales
            ELSE ''
        END AS dictamen_campo,
        CASE
            WHEN lh.contacto IN ('telefono', 'whatsapp') THEN lh.comentarios_generales
            ELSE ''
        END AS dictamen_ccc,
        lh.hora_de_promesa_de_pago AS promesa_pago,
        lh.motivo_de_no_de_pago AS motivo_negativa,
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
        lh.fecha_dictamen AS fecha_dispositivo,
        NULL AS longitud,
        NULL AS latitud,
        CONCAT(lh.lat, ',', lh.lng) AS ubicacion_usuario,
        '' AS fake_gps,
        '' AS secure_area,
        '' AS images
    FROM legacy_historico lh
    LEFT JOIN users u ON u.name = lh.nombre_usuario
    LEFT JOIN users ut ON ut.id =
        CASE
            WHEN u.supervisor_id IS NOT NULL THEN u.supervisor_id
            WHEN u.subgerente_id IS NOT NULL THEN u.subgerente_id
            WHEN u.gerente_id IS NOT NULL THEN u.gerente_id
            ELSE u.subdirector_id
        END
    WHERE lh.id_credit = :credito
    ORDER BY lh.fecha_dictamen DESC;
SQL;

        $legacyData = $mysqli->queryAll($query, ['credito' => $credito]);

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
        promesa_pago, porque_atraso_pago, motivo_negativa,
        images, ubicacion_usuario
    FROM base_clientes
    WHERE id_credito = :credito
    ORDER BY fecha_dispositivo DESC
    LIMIT 1
SQL;

        $result = $db->queryAll($query, ['credito' => $credito]);
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
            'promesa_pago', 'porque_atraso_pago', 'motivo_negativa',
            'images', 'ubicacion_usuario'
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

        $params = [];
        if (!empty($nombre)) {
            $params['nombre'] = '%' . $nombre . '%';
            $query .= " AND nombre_completo_cliente LIKE :nombre";
        } else {
            $params['credito'] = $credito;
            $query .= " AND id_credito = :credito";
        }
        $query .= " ORDER BY fecha_dispositivo DESC";

        return $mysqli->queryAll($query, $params);
    }

    /**
     * Devuelve eventos de ubicación del gestor (coordenadas en cada gestión) para un crédito.
     * Origen: Sky Logic (base_clientes.longitud/latitud) y Legacy __SPARTA_SECRET_REDACTED__ (dictums.longitud/latitud).
     * Formato: [ ['lat'=>float, 'lng'=>float, 'timestamp'=>int, 'id'=>string], ... ]
     *
     * @param string|int $idCredito
     * @param string|null $gestorId Si se pasa, filtra por usuario_asignado/codigo_gestor (opcional)
     * @return array
     */
    public static function getEventosGestorPorCredito($idCredito, $gestorId = null): array
    {
        $gestiones = self::getAllGestiones((string) $idCredito, '');
        $eventos = [];
        foreach ($gestiones as $i => $g) {
            $lat = $g['latitud'] ?? null;
            $lng = $g['longitud'] ?? null;
            if ($lat === null && $lng === null) {
                continue;
            }
            $lat = trim((string) $lat);
            $lng = trim((string) $lng);
            if ($lat === '' || $lng === '' || ($lat === '0' && $lng === '0')) {
                continue;
            }
            $latF = (float) $lat;
            $lngF = (float) $lng;
            if ($latF === 0.0 && $lngF === 0.0) {
                continue;
            }
            if ($gestorId !== null && $gestorId !== '') {
                $asignado = trim((string) ($g['usuario_asignado'] ?? ''));
                $codigo  = trim((string) ($g['codigo_gestor'] ?? ''));
                if ($asignado !== $gestorId && $codigo !== $gestorId) {
                    continue;
                }
            }
            $fecha = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? $g['fecha_sistema'] ?? null;
            $ts = null;
            if ($fecha !== null) {
                $ts = is_numeric($fecha) ? (int) $fecha : strtotime($fecha);
            }
            $eventId = $g['id_registro'] ?? $g['id'] ?? 'g_' . $i;
            $eventos[] = [
                'lat' => $latF,
                'lng' => $lngF,
                'timestamp' => $ts,
                'id' => (string) $eventId,
                'usuario_asignado' => trim((string) ($g['usuario_asignado'] ?? '')),
                'codigo_gestor' => trim((string) ($g['codigo_gestor'] ?? '')),
                'usuario' => trim((string) ($g['usuario'] ?? '')),
                'medio_contactacion_ccc' => trim((string) ($g['medio_contactacion_ccc'] ?? '')),
                'medio_contactacion_campo' => trim((string) ($g['medio_contactacion_campo'] ?? '')),
            ];
        }
        return $eventos;
    }
}
