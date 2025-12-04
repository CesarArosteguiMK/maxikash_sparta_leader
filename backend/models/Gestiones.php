<?php

namespace Models;

use Core\Model;
use Core\Database;

class Gestiones extends Model
{
    public static function getAllGestiones($credito, $nombre)
    {
        $mysqli = new Database();

        $query = <<<SQL
        SELECT id, id_team, team_supervisor, id_base, nombre_base, fecha_carga_base,
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

        return $mysqli->queryAll($query);
    }


}
