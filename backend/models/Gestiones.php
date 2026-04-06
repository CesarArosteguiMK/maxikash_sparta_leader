<?php

namespace Models;

use Core\DatabaseSegundometro;
use Core\Model;
use Core\Database;
use Core\DatabaseLegacy;

class Gestiones extends Model
{

    public static function getDetalleGestion($credito, $nombre)
    {
        $credito = trim((string) $credito);
        if ($credito === '' || !ctype_digit($credito)) {
            return [];
        }

        $mysqli = new DatabaseSegundometro();

        $query = <<<SQL
        SELECT id_credito, Nombre_cliente, Codigo_postal_1, Celular, Referencia_stp, cuota
        FROM tbl_segundometro_histo
        WHERE id_credito = :credito
        LIMIT 1
SQL;

        return $mysqli->queryAll($query, ['credito' => (int) $credito]);
    }
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
     * Gestiones recientes para el panel de rastreo (solo N finales tras fusionar Legacy + Sky).
     * Evita cargar todo el historial de base_clientes por crédito.
     *
     * @param int $limiteFinal Registros devueltos (p. ej. 16).
     * @param int $porFuente Cuántas filas traer por fuente antes de fusionar y ordenar.
     */
    public static function getGestionesParaRastreoCredito($credito, int $limiteFinal = 16, int $porFuente = 80): array
    {
        $credito = trim((string) $credito);
        if ($credito === '' || !ctype_digit($credito)) {
            return [];
        }
        $limiteFinal = max(1, min($limiteFinal, 100));
        $porFuente = max($limiteFinal, min($porFuente, 300));

        $legacy = self::getGestionesLegacy($credito, $porFuente);
        $sky = self::getAllGestionesSkyLogic($credito, '', $porFuente);
        if (!is_array($legacy)) {
            $legacy = [];
        }
        if (!is_array($sky)) {
            $sky = [];
        }
        $resultado = array_merge($legacy, $sky);
        usort($resultado, function ($a, $b) {
            return strtotime($b['fecha_dispositivo'] ?? 0) <=> strtotime($a['fecha_dispositivo'] ?? 0);
        });

        return array_slice($resultado, 0, $limiteFinal);
    }

    /**
     * Gestiones desde __SPARTA_SECRET_REDACTED__ (Legacy): vista legacy_historico.
     * dictamen_campo / dictamen_ccc ← dictamen_for (opción de dictamen). comentarios_generales ← texto libre.
     * promesa_pago ← fecha + hora de promesa; porque_atraso_pago ← porque_se_atraso_en_su_pago.
     *
     * @param int|null $limit Si se indica, LIMIT en SQL (más recientes primero). null = sin límite.
     */
    public static function getGestionesLegacy($credito, ?int $limit = null)
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
         lh.nombre_usuario AS nombre_cliente,
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
            WHEN lh.contacto = 'campo' THEN COALESCE(NULLIF(TRIM(lh.medio_de_contacto_campo), ''), 'domicilio del cliente')
            ELSE '0'
        END AS medio_contactacion_campo,
        CASE
            WHEN lh.contacto = 'campo' THEN TRIM(COALESCE(lh.dictamen_for, ''))
            ELSE ''
        END AS dictamen_campo,
        CASE
            WHEN lh.contacto IN ('telefono', 'whatsapp') THEN TRIM(COALESCE(lh.dictamen_for, ''))
            ELSE ''
        END AS dictamen_ccc,
        TRIM(CONCAT_WS(' ', NULLIF(TRIM(lh.fecha_de_promesa_de_pago), ''), NULLIF(TRIM(lh.hora_de_promesa_de_pago), ''))) AS promesa_pago,
        lh.motivo_de_no_de_pago AS motivo_negativa,
        TRIM(COALESCE(lh.porque_se_atraso_en_su_pago, '')) AS porque_atraso_pago,
        '' AS con_quien_mala_experiencia,
        NOW() AS fecha_hora,
        '' AS kilometraje,
        '' AS numero_serie,
        '' AS marca_modelo,
        '' AS actualizacion_direccion,
        '' AS actualizacion_telefono,
        lh.comentarios_generales AS comentarios_generales,
        '' AS foto1,
        '' AS foto2,
        '' AS foto3,
        '' AS adjunto,
        '' AS video,
        '' AS device_imei,
        NOW() AS fecha_sistema,
        lh.fecha_dictamen AS fecha_dispositivo,
        lh.lat AS latitud,
        lh.lng AS longitud,
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
    ORDER BY lh.fecha_dictamen DESC
SQL;
        if ($limit !== null) {
            $lim = max(1, min((int) $limit, 500));
            $query .= ' LIMIT ' . $lim;
        }
        $query .= ';';

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
     * Completa filas Legacy con un snapshot reciente de Sky (mismo crédito).
     * No mezcla dictamen, comentarios, promesa ni medios: son por gestión en legacy_historico.
     */
    private static function mergeWithSkyLogicData($legacyRow, $skyData)
    {
        $fieldsToMerge = [
            'telefono_celular', 'cp', 'direccion', 'cuenta_clabe',
            'pago_semanal', 'pagos_vencidos', 'deuda_total',
            'referencia_personal1', 'parentesco1', 'telefono_referencia1',
            'referencia_personal2', 'parentesco2', 'telefono_referencia2',
            'geolocalizacion', 'direccion_geo',
            'longitud', 'latitud',
            'estatus', 'direccion_ine', 'direccion_actual',
            'images', 'ubicacion_usuario',
        ];

        foreach ($fieldsToMerge as $field) {
            // Si el campo en Legacy está vacío y existe en Sky Logic con datos
            if (empty($legacyRow[$field]) && !empty($skyData[$field])) {
                $legacyRow[$field] = $skyData[$field];
            }
        }

        return $legacyRow;
    }

    /**
     * @param int|null $limit Si se indica, LIMIT en SQL (más recientes primero). null = sin límite.
     */
    public static function getAllGestionesSkyLogic($credito, $nombre = '', ?int $limit = null)
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
        if ($limit !== null) {
            $lim = max(1, min((int) $limit, 500));
            $query .= " LIMIT " . $lim;
        }

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

    /**
     * Indica si el registro corresponde a visita en campo (p. ej. domicilio del cliente), no solo contacto CCC.
     */
    public static function gestionEsVisitaCampo(array $g): bool
    {
        $contacto = strtolower(trim((string)($g['contacto'] ?? '')));
        $ccc = trim((string)($g['medio_contactacion_ccc'] ?? ''));
        $campoMedio = trim((string)($g['medio_contactacion_campo'] ?? ''));

        $esCampo = ($contacto === 'campo');
        if (($esCampo && $ccc === '')
            || ($campoMedio !== '' && $campoMedio !== '0')) {
            $esCampo = true;
        }
        $esTelefonico = ($contacto === 'telefono' || $contacto === 'telefonico' ||
            (!empty($ccc) && $campoMedio === ''));
        if (($esCampo && $ccc === '')
            || ($campoMedio !== '' && $campoMedio !== '0')) {
            $esTelefonico = false;
        }
        if ($esTelefonico) {
            return false;
        }
        return $esCampo;
    }

    /**
     * Última gestión de visita en campo en o después del envío del dictamen (histórico tipo Sabueso).
     * Dictamen / comentarios alineados con columnas del histórico (dictamen_campo / comentarios_generales).
     *
     * @param string      $idCredito
     * @param string|null $fechaEnvioDictamen ISO o vacío
     * @return array|null { fecha, medio_contactacion_campo, dictamen, comentarios }
     */
    public static function obtenerUltimaGestionCampoTrasEnvio(string $idCredito, ?string $fechaEnvioDictamen): ?array
    {
        if ($idCredito === '') {
            return null;
        }
        $gestiones = self::getAllGestiones($idCredito, '');
        if (empty($gestiones) || !is_array($gestiones)) {
            return null;
        }
        $tsEnvio = null;
        if ($fechaEnvioDictamen !== null && trim($fechaEnvioDictamen) !== '') {
            $tsEnvio = strtotime($fechaEnvioDictamen);
            if ($tsEnvio === false) {
                $tsEnvio = null;
            }
        }
        foreach ($gestiones as $g) {
            if (!is_array($g) || !self::gestionEsVisitaCampo($g)) {
                continue;
            }
            $fechaG = $g['fecha_dispositivo'] ?? $g['fecha_hora'] ?? $g['fecha_sistema'] ?? null;
            $tsG = false;
            if ($fechaG !== null && $fechaG !== '') {
                $tsG = is_numeric($fechaG) ? (int)$fechaG : strtotime((string)$fechaG);
            }
            if ($tsEnvio !== null && $tsG !== false && $tsG < $tsEnvio) {
                continue;
            }
            $dictamen = trim((string)($g['dictamen_campo'] ?? ''));
            if ($dictamen === '') {
                $dictamen = trim((string)($g['dictamen_ccc'] ?? ''));
            }
            $comentarios = trim((string)($g['comentarios_generales'] ?? ''));
            $medio = trim((string)($g['medio_contactacion_campo'] ?? ''));
            if ($medio === '' || $medio === '0') {
                $medio = 'domicilio del cliente';
            }
            return [
                'fecha' => $fechaG,
                'medio_contactacion_campo' => $medio,
                'dictamen' => $dictamen,
                'comentarios' => $comentarios,
            ];
        }
        return null;
    }
}
