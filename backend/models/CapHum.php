<?php

namespace Models;

use Core\Model;
use Core\Database;

class CapHum extends Model
{
    public static function getPersonasMayorRangoPorDepartamento($departamento)
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
        WHERE p.activo = 1
          AND p.nombre != 'Gestor 1-14'
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
              AND aj.id_jefe = 152
        
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
            'id_jefe', 152,
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
                WHERE j1.id_jefe = 152
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



}
