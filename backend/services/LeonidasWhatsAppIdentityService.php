<?php

namespace Services;

use Core\Database;

final class LeonidasWhatsAppIdentityService
{
    public function buscarPersonaActiva(string $phone): array
    {
        $db = new Database();
        $rows = $db->queryAll(
            "SELECT
                p.id,
                p.nombres,
                p.segundo_nombre,
                p.apellidop,
                p.apellidom,
                p.user_name,
                p.session_version,
                p.telefono_uno,
                p.telefono_dos,
                GROUP_CONCAT(
                    CASE
                        WHEN COALESCE(tp.estatus, 'Activo') = 'Activo' THEN tp.numero
                        ELSE NULL
                    END
                    SEPARATOR '|'
                ) AS telefonos_adicionales
             FROM persona p
             LEFT JOIN estado_cuenta.telefonos_persona tp ON tp.id_persona = p.id
             WHERE LOWER(TRIM(COALESCE(p.estatus, ''))) = 'activo'
             GROUP BY
                p.id, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom,
                p.user_name, p.session_version, p.telefono_uno, p.telefono_dos"
        );

        $matches = [];
        foreach ($rows as $row) {
            $phones = [
                (string) ($row['telefono_uno'] ?? ''),
                (string) ($row['telefono_dos'] ?? ''),
            ];
            foreach (explode('|', (string) ($row['telefonos_adicionales'] ?? '')) as $additional) {
                $phones[] = $additional;
            }

            foreach ($phones as $candidate) {
                if (LeonidasWhatsAppProtocol::telefonosCoinciden($phone, $candidate)) {
                    $matches[(int) $row['id']] = $row;
                    break;
                }
            }
        }

        if (count($matches) === 0) {
            return ['status' => 'not_found', 'persona' => null];
        }
        if (count($matches) > 1) {
            return ['status' => 'ambiguous', 'persona' => null];
        }

        return ['status' => 'ok', 'persona' => array_values($matches)[0]];
    }
}
