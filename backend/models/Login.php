<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\UsuarioFantasmaReporteria;

class Login extends Model
{
    private const MODULOS_PERSONALES_PLANTILLA = [
        141 => [
            'nombre' => 'Mis documentos',
            'pestana' => 'Capital Humano',
            'descripcion' => 'Capital Humano > Mis documentos',
        ],
        147 => [
            'nombre' => 'Vacaciones',
            'pestana' => 'Capital Humano',
            'descripcion' => 'Capital Humano > Vacaciones',
        ],
    ];

    private static function asegurarModulosPersonalesPlantilla(Database $db): void
    {
        foreach (self::MODULOS_PERSONALES_PLANTILLA as $moduloId => $datos) {
            $existeModulo = $db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => $moduloId]
            );
            if (!$existeModulo) {
                $db->CRUD(
                    'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                     VALUES (:id, :nombre, :pestana, :descripcion, 1)',
                    ['id' => $moduloId] + $datos
                );
            }

            $db->CRUD(
                "INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id)
                 SELECT p.id, :modulo_web_id
                   FROM persona p
                  WHERE LOWER(TRIM(COALESCE(p.estatus, ''))) NOT IN ('baja', 'transito de baja')
                    AND NOT EXISTS (
                        SELECT 1
                          FROM asigna_modulo_web am
                         WHERE am.usuario_id = p.id
                           AND am.modulo_web_id = :modulo_web_id_check
                    )",
                ['modulo_web_id' => $moduloId, 'modulo_web_id_check' => $moduloId]
            );
        }
    }

    public static function validaUsuario($datos)
    {
        $usuario  = trim((string) ($datos['usuario'] ?? ''));
        $password = trim((string) ($datos['password'] ?? ''));

        $queryConPassword = <<<SQL
        SELECT p.id, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom,
            p.numero_empleado, p.user_name, p.password,
            pp.id AS id_puesto, pp.nombre AS nombre_puesto, pp.departamento_id,
            p.session_version, p.force_logout
        FROM persona p
        LEFT JOIN asigna_puesto a ON a.id = (
            SELECT ap.id
            FROM asigna_puesto ap
            WHERE ap.id_persona = p.id
              AND COALESCE(ap.activo, 0) = 1
            ORDER BY ap.id DESC
            LIMIT 1
        )
        LEFT JOIN puesto pp ON pp.id = a.id_puesto
        WHERE p.estatus = 'Activo' AND p.user_name = :usuario AND p.password = :password
        LIMIT 1
        SQL;

        try {
            $db = new Database();

            // Usuario + contraseña: comparación directa (sin hash)
            $r = $db->queryOne($queryConPassword, ['usuario' => $usuario, 'password' => $password]);

            if (!$r) {
                return self::resultado(false, 'Credenciales incorrectas.');
            }

            unset($r['password']);

            if ((int)($r['force_logout'] ?? 0) === 1) {
                $db->CRUD("UPDATE persona SET force_logout = 0 WHERE id = :id", ['id' => $r['id']]);
            }

            return self::resultado(true, 'Credenciales correctas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al procesar la solicitud.',
                null,
                $e->getMessage()
            );
        }
    }

    public static function getModulosUsuario($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return [];
        }

        $query = <<<SQL
        SELECT p.user_name AS user_name, am.modulo_web_id AS modulo_web_id
        FROM persona p
        LEFT JOIN asigna_modulo_web am ON am.usuario_id = p.id
        WHERE p.id = :idPersona
        SQL;

        try {
            $db = new Database();
            $rows = $db->queryAll($query, ['idPersona' => $idPersona]);
            $first = $rows[0] ?? null;
            if ($first && UsuarioFantasmaReporteria::matchUsername($first['user_name'] ?? $first['USER_NAME'] ?? null)) {
                return [UsuarioFantasmaReporteria::MODULO_COMPARATIVAS];
            }
            self::asegurarModulosPersonalesPlantilla($db);
        } catch (\Exception $e) {
            return [];
        }

        $ids = [];
        foreach ($rows as $row) {
            $id = $row['modulo_web_id'] ?? $row['MODULO_WEB_ID'] ?? null;
            if ($id !== null && $id !== '') {
                $ids[] = (int) $id;
            }
        }
        $ids = array_values(array_unique($ids));
        /* Módulo 19 = «Cartera actual» (Analítica); no inyectar Panel Admin (27). Solo 25 puede unificarse con 27. */
        if (in_array(25, $ids, true) && !in_array(27, $ids, true)) {
            $ids[] = 27;
            $ids = array_values(array_unique($ids));
        }
        foreach ([141, 147] as $moduloPersonal) {
            if (!in_array($moduloPersonal, $ids, true)) {
                $ids[] = $moduloPersonal;
            }
        }
        return $ids;
    }
}
