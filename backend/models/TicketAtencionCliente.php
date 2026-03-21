<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Tickets tipo Atención al cliente (quejas, consultas, seguimiento).
 */
class TicketAtencionCliente extends Model
{
    public static function guardar(array $datos, $idPersonaCreador)
    {
        $idPersonaCreador = (int) $idPersonaCreador;
        if ($idPersonaCreador < 1) {
            return self::resultado(false, 'Sesión inválida.', null);
        }
        $asunto = isset($datos['asunto']) ? trim((string) $datos['asunto']) : '';
        $descripcion = isset($datos['descripcion']) ? trim((string) $datos['descripcion']) : '';
        $prioridad = isset($datos['prioridad']) ? trim((string) $datos['prioridad']) : 'media';
        $contactoTelefono = isset($datos['contacto_telefono']) ? trim((string) $datos['contacto_telefono']) : null;
        $contactoEmail = isset($datos['contacto_email']) ? trim((string) $datos['contacto_email']) : null;

        if ($asunto === '') {
            return self::resultado(false, 'El asunto es obligatorio.', null);
        }
        if (strlen($asunto) > 255) {
            return self::resultado(false, 'El asunto no debe exceder 255 caracteres.', null);
        }
        if ($descripcion === '') {
            return self::resultado(false, 'La descripción es obligatoria.', null);
        }
        if (!in_array($prioridad, ['alta', 'media', 'baja'], true)) {
            $prioridad = 'media';
        }

        try {
            $db = new Database();
            $db->CRUD(
                "INSERT INTO ticket_atencion_cliente (id_persona_creador, asunto, descripcion, prioridad, contacto_telefono, contacto_email, fecha_creacion) " .
                "VALUES (:id_persona_creador, :asunto, :descripcion, :prioridad, :contacto_telefono, :contacto_email, NOW())",
                [
                    'id_persona_creador'  => $idPersonaCreador,
                    'asunto'              => $asunto,
                    'descripcion'         => $descripcion,
                    'prioridad'           => $prioridad,
                    'contacto_telefono'   => $contactoTelefono === '' ? null : $contactoTelefono,
                    'contacto_email'      => $contactoEmail === '' ? null : $contactoEmail,
                ]
            );
            $row = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id = $row ? (int) $row['id'] : 0;
            return self::resultado(true, 'Ticket de atención al cliente registrado correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar.', null, $e->getMessage());
        }
    }
}
