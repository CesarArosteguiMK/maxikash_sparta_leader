<?php

namespace Models;

use Core\Model;
use Core\Database;

class CapHum extends Model
{
    public static function getPersonasMayorRangoPorDepartamento($departamento, $permiso_todos, $id_persona)
    {
        try {
            $db = new Database();


            // -------------------------------------------------------
            // 1) Puestos activos del departamento
            // -------------------------------------------------------
            $queryPuestos = <<<SQL
            SELECT 
                p.id, 
                p.nombre, 
                p.nivel
            FROM puesto p
            WHERE p.activo = 1 AND es_jefe = 1
              AND p.departamento_id = :departamento
        SQL;

            $puestos = $db->queryAll($queryPuestos, [
                'departamento' => $departamento
            ]);

            if (!$puestos) {
                return self::resultado(true, 'No hay puestos activos en este departamento.', []);
            }

            // -------------------------------------------------------
            // 2) Mayor nivel jerárquico
            // -------------------------------------------------------
            $nivelMax = max(array_column($puestos, 'nivel'));



            $puestosTop = array_filter($puestos, function ($p) use ($nivelMax) {
                return $p['nivel'] == $nivelMax;
            });

            $puestosTopIds = array_column($puestosTop, 'id');

            // -------------------------------------------------------
            // 3) Crear placeholders con nombre (:p0, :p1, ...)
            // -------------------------------------------------------
            $params = [];
            $placeholders = [];

            foreach ($puestosTopIds as $i => $id) {
                $key = "p$i";
                $placeholders[] = ":$key";
                $params[$key] = $id;
            }

            $placeholdersStr = implode(',', $placeholders);

            // -------------------------------------------------------
            // 4) Personas por puestos top
            // -------------------------------------------------------

            if($permiso_todos == 0)
            {
                $queryPersonas = <<<SQL
                SELECT 
                    p.id,
                    CONCAT(p.apellidop, ' ', p.apellidom, ' ', p.nombres) AS nombre,
                    ap.id_puesto
                FROM persona p
                INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
                WHERE p.id = $id_persona
                  AND p.estatus != 'Baja'
        SQL;

                $personas = $db->queryAll($queryPersonas);
            }
            else{
                $queryPersonas = <<<SQL
                SELECT 
                    p.id,
                    CONCAT(p.apellidop, ' ', p.apellidom, ' ', p.nombres) AS nombre,
                    ap.id_puesto
                FROM persona p
                INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
                WHERE ap.id_puesto IN ($placeholdersStr)
                  AND p.estatus != 'Baja'
        SQL;

                $personas = $db->queryAll($queryPersonas, $params);
            }



            return self::resultado(true, 'Personas de mayor rango encontradas.', $personas);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaPersonasJerarquia($id_persona)
    {
        $query = <<<SQL
               WITH RECURSIVE Jerarquia AS (
            -- Nivel raíz (jefe inicial)
            SELECT 
                p.id,
                p.nombres,
                p.apellidop,
                ap.id_puesto,
                pp.nombre as nombre_puesto,
                aj.id_jefe,
                1 AS nivel
            FROM persona p
            JOIN asigna_puesto ap ON p.id = ap.id_persona
            JOIN puesto pp ON pp.id = ap.id_puesto
            JOIN asigna_jefe aj ON p.id = aj.id_persona
                AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            WHERE p.estatus != 'Baja'
              AND aj.id_jefe = $id_persona
        
            UNION ALL
        
            -- Subordinados recursivos
            SELECT 
                p2.id,
                p2.nombres,
                p2.apellidop,
                ap2.id_puesto,
                pp2.nombre as nombre_puesto,
                aj2.id_jefe,
                j.nivel + 1 AS nivel
            FROM persona p2
            JOIN asigna_puesto ap2 ON p2.id = ap2.id_persona
            JOIN puesto pp2 ON pp2.id = ap2.id_puesto      -- ⚠ Corrección aquí
            JOIN asigna_jefe aj2 ON p2.id = aj2.id_persona
                AND (aj2.fecha_fin IS NULL OR aj2.fecha_fin >= CURDATE())
            JOIN Jerarquia j ON aj2.id_jefe = j.id
            WHERE p2.estatus != 'Baja'
        )
        
        -- Convertir jerarquía en JSON anidado
        SELECT JSON_OBJECT(
            'id_jefe', $id_persona,
            'subordinados', (
                SELECT JSON_ARRAYAGG(
                    JSON_OBJECT(
                        'id', j1.id,
                        'nombre', CONCAT(j1.nombres, ' ', j1.apellidop),
                        'id_puesto', j1.id_puesto,
                        'nombre_puesto', j1.nombre_puesto,
                        'id_jefe', j1.id_jefe,
                        'nivel', j1.nivel,
                        'subordinados', (
                            SELECT COALESCE(
                                JSON_ARRAYAGG(
                                    JSON_OBJECT(
                                        'id', j2.id,
                                        'nombre', CONCAT(j2.nombres, ' ', j2.apellidop),
                                        'id_puesto', j2.id_puesto,
                                        'nombre_puesto', j2.nombre_puesto,
                                        'id_jefe', j2.id_jefe,
                                        'nivel', j2.nivel
                                    )
                                ),
                                JSON_ARRAY()
                            )
                            FROM Jerarquia j2
                            WHERE j2.id_jefe = j1.id
                        )
                    )
                )
                FROM Jerarquia j1
                WHERE j1.id_jefe = $id_persona
            )
        ) AS organigrama_json

    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaGestores($id_gestor_sesion)
    {
        if($id_gestor_sesion == 'admin')
        {
            $add = '';
        }
        else
        {
            $add = ' AND aj.id_jefe = '. $id_gestor_sesion;
        }
        $query = <<<SQL
          WITH RECURSIVE Jerarquia AS (
            -- Nivel raíz (jefe inicial)
            SELECT 
                p.id,
                p.nombres,
                p.apellidop,
                p.apellidom,
                pp.id AS id_puesto,
                pp.nombre AS nombre_puesto,
                d.nombre AS nombre_departamento,
                aj.id_jefe,
                p.estatus,
                1 AS nivel,
                 pp.nivel AS nivel_puesto
            FROM persona p
            JOIN asigna_puesto ap ON p.id = ap.id_persona
            JOIN puesto pp ON pp.id = ap.id_puesto
            JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN asigna_jefe aj 
                   ON p.id = aj.id_persona 
                  AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            WHERE p.estatus != 'Baja'
              $add -- opcional filtro extra
        
            UNION ALL
        
            -- Subordinados recursivos
            SELECT 
                p2.id,
                p2.nombres,
                p2.apellidop,
                p2.apellidom,
                pp2.id AS id_puesto,
                pp2.nombre AS nombre_puesto,
                d2.nombre AS nombre_departamento,
                aj2.id_jefe,
                p2.estatus,
                j.nivel + 1 AS nivel,
                 pp2.nivel AS nivel_puesto
            FROM persona p2
            JOIN asigna_puesto ap2 ON p2.id = ap2.id_persona
            JOIN puesto pp2 ON pp2.id = ap2.id_puesto
            JOIN departamento d2 ON d2.id = pp2.departamento_id
            LEFT JOIN asigna_jefe aj2 
                   ON p2.id = aj2.id_persona 
                  AND (aj2.fecha_fin IS NULL OR aj2.fecha_fin >= CURDATE())
            JOIN Jerarquia j ON aj2.id_jefe = j.id
            WHERE p2.estatus != 'Baja'
        )
        
        SELECT *
        FROM Jerarquia
        ORDER BY nivel_puesto ASC;

        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaGestoresDepartamento($id_departamento)
    {
        if(1 == 'admin')
        {
            $add = '';
        }
        else
        {
            $add = ' AND aj.id_jefe = ';
        }
        $query = <<<SQL
          SELECT
        per.id,
        CONCAT(per.nombres, ' ', per.apellidop) AS nombre_completo,
        pu.nombre AS nombre_puesto
        FROM asigna_puesto ap
        INNER JOIN persona per 
            ON per.id = ap.id_persona
        INNER JOIN puesto pu 
            ON pu.id = ap.id_puesto
        WHERE pu.departamento_id = $id_departamento
          AND pu.es_jefe = 1;
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function insertPersona($data)
    {
        // 🔹 Escapamos valores
        $nombres = addslashes($data['nombres']);
        $apellidop = addslashes($data['apellidop']);
        $apellidom = addslashes($data['apellidom']);
        $numero_empleado = addslashes($data['numero_empleado']);
        $correo = addslashes($data['correo'] ?? '');
        $telefono_uno = addslashes($data['telefono_uno'] ?? '');
        $telefono_dos = addslashes($data['telefono_dos'] ?? '');
        $estatus = addslashes($data['estatus'] ?? 'Activo');
        $id_puesto = addslashes($data['id_puesto']);
        $user_name = addslashes($data['usuario']);
        $password = addslashes($data['contrasena']);


        try {
            $db = new Database();

            // 1️⃣ Ejecutamos INSERT con queryOne() aunque no devuelve filas
            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.persona
            (nombres, apellidop, apellidom, numero_empleado, correo, telefono_uno, telefono_dos, estatus, user_name, password)
            VALUES
            ('$nombres', '$apellidop', '$apellidom', '$numero_empleado', '$correo', '$telefono_uno', '$telefono_dos', '$estatus', '$user_name', '$password')
        ");

            // 2️⃣ Obtenemos el ID insertado con queryOne()
            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");

            $id_persona = isset($result['id']) ? intval($result['id']) : null;

           if($result)
            {
                $db->queryOne("
                   INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto
                                (id, id_persona, id_puesto, fecha_asignacion, activo)
                    VALUES
                    (DEFAULT, $id_persona, $id_puesto, NOW(), 1)
                ");
            }

            return self::resultado(true, 'Persona insertada correctamente.', $result);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al insertar persona.', null, $e->getMessage());
        }
    }

    public static function UpdatePersona($data)
    {
        $id_persona = (int)$data['id'];
        $nombres = addslashes($data['nombres']);
        $apellidop = addslashes($data['apellidop']);
        $apellidom = addslashes($data['apellidom']);
        $numero_empleado = addslashes($data['numero_empleado']);
        $correo = addslashes($data['correo'] ?? '');
        $telefono_uno = addslashes($data['telefono_uno'] ?? '');
        $id_jefe = (int)$data['jefe_id'];
        $id_puesto = (int)$data['puesto_id'];
        $id_departamento = (int)$data['departamento_id'];
        $user_name = addslashes($data['usuario']);
        $password = addslashes($data['contrasena']);

        try {
            $db = new Database();

            $db->queryOne("
            UPDATE __SPARTA_SECRET_REDACTED__.persona
            SET 
                nombres = '$nombres',
                apellidop = '$apellidop',
                apellidom = '$apellidom',
                numero_empleado = '$numero_empleado',
                correo = '$correo',
                telefono_uno = '$telefono_uno',
                user_name = '$user_name',
                password = '$password'
            WHERE id = $id_persona
        ");

            $db->queryOne("
            UPDATE asigna_jefe 
            SET id_jefe = $id_jefe 
            WHERE id_persona = $id_persona
        ");

            $db->queryOne("
            UPDATE asigna_puesto 
            SET id_puesto = $id_puesto
            WHERE id_persona = $id_persona
        ");

            return self::resultado(true, 'Persona actualizada correctamente.', null);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar persona.', null, $e->getMessage());
        }
    }




}
