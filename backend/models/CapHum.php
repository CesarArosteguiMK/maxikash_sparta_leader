<?php

namespace Models;

use Core\Model;
use Core\Database;

class CapHum extends Model
{
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ///
    ///

    public static function getConsultaGestoresAll($id_gestor_sesion)
    {
        // =========================
        // USUARIOS ADMIN / ESPECIALES
        // =========================
        if (in_array($id_gestor_sesion, [1, 2, 3, 396, 797 ])) {

            $query = <<<SQL
            SELECT
            p.id,
            p.numero_empleado,
            p.nombres,
            p.apellidop,
            p.apellidom,
        
            pp.id AS id_puesto,
            CASE 
                WHEN pp.nombre IS NULL THEN 'Sin puesto'
                ELSE pp.nombre
            END AS nombre_puesto,
            pp.nivel AS nivel_puesto,
        
            CASE 
                WHEN d.nombre IS NULL THEN 'Sin departamento'
                ELSE d.nombre
            END AS nombre_departamento,
        
            aj.id_jefe,
        
            CASE 
                WHEN pj.id IS NULL THEN 'Sin jefe'
                ELSE CONCAT(pj.nombres, ' ', pj.apellidop, ' ', pj.apellidom)
            END AS nombre_jefe,
        
            p.estatus,
            CASE 
                WHEN p.user_name IS NULL THEN 'Sin usuario'
                ELSE p.user_name
            END AS usuario
        
        FROM persona p
        
        LEFT JOIN asigna_puesto ap 
               ON p.id = ap.id_persona
        
        LEFT JOIN puesto pp 
               ON pp.id = ap.id_puesto
        
        LEFT JOIN departamento d 
               ON d.id = pp.departamento_id
        
        -- 🔥 MISMO FILTRO, SOLO REUBICADO
        LEFT JOIN (
            SELECT id_persona, id_jefe
            FROM asigna_jefe
            WHERE fecha_fin IS NULL 
               OR fecha_fin >= CURDATE()
        ) aj ON aj.id_persona = p.id
        
        LEFT JOIN persona pj 
               ON pj.id = aj.id_jefe
        
        WHERE p.estatus != 'Baja'
          AND (
                pp.id IS NULL
                OR EXISTS (
                    SELECT 1
                    FROM __SPARTA_SECRET_REDACTED__.privilegios_departamento pd
                    WHERE pd.idPersona = $id_gestor_sesion
                )
          )
        
        ORDER BY pp.nivel ASC;

        SQL;

        }
        // =========================
        // USUARIOS NORMALES (JERARQUÍA)
        // =========================
        else {

            $query = <<<SQL
        WITH RECURSIVE Jerarquia AS (

            -- =====================
            -- NIVEL RAÍZ
            -- =====================
            SELECT 
                p.id,
                p.nombres,
                p.apellidop,
                p.apellidom,
                pp.id AS id_puesto,
                pp.nombre AS nombre_puesto,
                pp.nivel AS nivel_puesto,
                d.nombre AS nombre_departamento,
                aj.id_jefe,
                p.estatus,
                1 AS nivel
            FROM persona p
            LEFT JOIN asigna_puesto ap ON p.id = ap.id_persona
            LEFT JOIN puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN asigna_jefe aj 
                   ON p.id = aj.id_persona
                  AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            WHERE p.estatus != 'Baja'
              AND (
                    aj.id_jefe = $id_gestor_sesion
                    OR aj.id_jefe IS NULL
                  )

            UNION ALL

            -- =====================
            -- SUBORDINADOS
            -- =====================
            SELECT 
                p2.id,
                p2.nombres,
                p2.apellidop,
                p2.apellidom,
                pp2.id AS id_puesto,
                pp2.nombre AS nombre_puesto,
                pp2.nivel AS nivel_puesto,
                d2.nombre AS nombre_departamento,
                aj2.id_jefe,
                p2.estatus,
                j.nivel + 1 AS nivel
            FROM persona p2
            LEFT JOIN asigna_puesto ap2 ON p2.id = ap2.id_persona
            LEFT JOIN puesto pp2 ON pp2.id = ap2.id_puesto
            LEFT JOIN departamento d2 ON d2.id = pp2.departamento_id
            LEFT JOIN asigna_jefe aj2 
                   ON p2.id = aj2.id_persona
                  AND (aj2.fecha_fin IS NULL OR aj2.fecha_fin >= CURDATE())
            JOIN Jerarquia j 
                 ON aj2.id_jefe = j.id
            WHERE p2.estatus != 'Baja'
        )

        SELECT *
        FROM Jerarquia
        ORDER BY nivel_puesto ASC, nivel ASC;
        SQL;
        }


        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getPersonaDetalle($idPersona)
    {
        try {
            $db = new Database();

            $query = <<<SQL
            SELECT 
                p.*,
                ap.id_puesto, dd.nombre as departamento, dd.id as id_departamento, aj.id_jefe, p.password
            FROM persona p
            LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id
            LEFT JOIN puesto pu ON pu.id = ap.id_puesto
            LEFT JOIN departamento dd ON dd.id = pu.departamento_id
            LEFT JOIN asigna_jefe aj ON aj.id_persona = p.id
            WHERE p.id = $idPersona
              AND p.estatus != 'Baja'
            LIMIT 1
        SQL;

            $persona = $db->queryOne($query);

            return self::resultado(true, 'Persona encontrada.', $persona);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function actualizarModuloPerfil($idPersona, $moduloId, $asignado)
    {
        try {
            $db = new Database();

            if ($asignado === 1) {

                // 1️⃣ Validar si ya existe
                $queryExiste = <<<SQL
                SELECT id
                FROM asigna_modulo_web
                WHERE usuario_id = $idPersona
                  AND modulo_web_id = $moduloId
                LIMIT 1
            SQL;

                $existe = $db->queryOne($queryExiste);

                if (!$existe) {
                    // 2️⃣ Insertar si no existe
                    $queryInsert = <<<SQL
                    INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id)
                    VALUES ($idPersona, $moduloId)
                SQL;

                    $db->queryOne($queryInsert);
                }

                return self::resultado(
                    true,
                    'Módulo asignado correctamente'
                );

            } else {

                // 3️⃣ Eliminar asignación
                $queryDelete = <<<SQL
                DELETE FROM asigna_modulo_web
                WHERE usuario_id = $idPersona
                  AND modulo_web_id = $moduloId
            SQL;

                $db->queryOne($queryDelete);

                return self::resultado(
                    true,
                    'Módulo eliminado correctamente'
                );
            }

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al actualizar el módulo',
                null,
                $e->getMessage()
            );
        }
    }


    public static function getPersonaDetallePerfil($idPersona)
    {
        try {
            $db = new Database();

            $query = <<<SQL
            SELECT 
                p.*
            FROM persona p
            WHERE p.id = $idPersona
              AND p.estatus != 'Baja'
            LIMIT 1
        SQL;

            $query_perfiles = <<<SQL
            SELECT 
                $idPersona AS usuario_id,
                m.id AS modulo_id,
                m.nombre AS modulo_nombre,
                m.pestana,
                m.descripcion,
                m.activo,
                CASE 
                    WHEN a.usuario_id IS NOT NULL THEN 'Asignado'
                    ELSE 'No asignado'
                END AS estado,
                CASE 
                    WHEN a.usuario_id IS NOT NULL THEN 1
                    ELSE 0
                END AS asignado_flag
            FROM modulos_web m
            LEFT JOIN asigna_modulo_web a
                ON m.id = a.modulo_web_id
                AND a.usuario_id = $idPersona
            WHERE m.activo = 1
            ORDER BY m.id;
        SQL;

            $persona = $db->queryOne($query);
            $perfiles = $db->queryAll($query_perfiles);

            return self::resultado(true, 'Persona encontrada.', [
                'persona' => $persona,
                'perfiles' => $perfiles
            ]);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
    public static function getComboDepartamentos($perfil_id = null)
    {
        $where = '';

        if (!empty($perfil_id)) {
            $perfil_id = intval($perfil_id); // 🔐 seguridad
            $where = "WHERE d.id = $perfil_id";
        }

        $query = <<<SQL
        SELECT DISTINCT d.*
        FROM privilegios_departamento pd
        INNER JOIN puesto p
            ON p.id = pd.idPuesto
        INNER JOIN departamento d
            ON d.id = p.departamento_id
        $where
        ORDER BY d.nombre ASC
    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaPuestos($departamento)
    {
        // Query base
        $query = <<<SQL
        SELECT
            p.id, p.nombre, p.nivel, d.nombre as departamento
        FROM puesto p
        INNER JOIN departamento d ON d.id = p.departamento_id
    SQL;

        $params = [];

        // Agregar filtro si se envió un departamento
        if ($departamento != null) {
            $query .= " WHERE d.id = :departamento";
            $params['departamento'] = $departamento;
        }

        try {
            $db = new Database();
            // Pasar parámetros si existen
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Puestos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getRazonesAusencia()
    {
        // Query base
        $query = <<<SQL
        SELECT
            id,
            clave,
            nombre,
            descripcion
        FROM razon_ausencia
        WHERE activo = 1
        ORDER BY nombre
    SQL;

        $params = [];

        try {
            $db = new Database();

            // Ejecutar query (no requiere parámetros)
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Razones de ausencia encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener razones de ausencia.',
                null,
                $e->getMessage()
            );
        }
    }

    public static function getAusenciasPersona($idPersona)
    {
        $query = <<<SQL
        SELECT
            a.id,
            r.nombre AS razon,
            a.fecha_inicio,
            a.fecha_fin,
            a.descripcion,
            a.activo
        FROM ausencia a
        INNER JOIN razon_ausencia r ON r.id = a.id_razon
        WHERE a.id_persona = :idPersona
        ORDER BY a.fecha_inicio DESC
    SQL;

        $params = ['idPersona' => $idPersona];

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Ausencias encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener ausencias.',
                null,
                $e->getMessage()
            );
        }
    }




    public static function getConsultaJefe($id_departamento)
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
            CONCAT(per.nombres, ' ', per.apellidop, ' ', per.apellidom) AS nombre_completo,
            pu.nombre AS nombre_puesto
        FROM asigna_puesto ap
        INNER JOIN persona per 
            ON per.id = ap.id_persona
        INNER JOIN puesto pu 
            ON pu.id = ap.id_puesto
        WHERE
            pu.es_jefe = 1 AND per.estatus = 1
            AND (
                pu.departamento_id = $id_departamento
                OR pu.id IN (8, 9)
            )
        ORDER BY per.nombres ASC;
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getAusenciaById($idAusencia)
    {
        $query = <<<SQL
        SELECT
            a.id,
            a.id_persona,
            a.id_razon,
            r.nombre AS razon,
            a.fecha_inicio,
            a.fecha_fin,
            a.descripcion,
            a.activo
        FROM ausencia a
        INNER JOIN razon_ausencia r ON r.id = a.id_razon
        WHERE a.id = :idAusencia
        LIMIT 1
    SQL;

        try {
            $db = new Database();

            $r = $db->queryOne($query, [
                'idAusencia' => $idAusencia
            ]);

            if (!$r) {
                return self::resultado(false, 'Ausencia no encontrada.', null);
            }

            return self::resultado(true, 'Ausencia encontrada.', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener la ausencia.', null, $e->getMessage());
        }
    }





    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    public static function getConsultaGestoresPorPuesto($id_puesto)
    {
        $query = <<<SQL
        SELECT DISTINCT
            p.id,
            CONCAT(p.nombres, ' ', p.apellidop, ' ', p.apellidom) AS nombre_completo,
            pp.nombre AS puesto,
            pp.nivel
        FROM persona p
        INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
        INNER JOIN puesto pp ON pp.id = ap.id_puesto
        WHERE p.estatus != 'Baja'
          AND pp.nivel > (
                SELECT nivel
                FROM puesto
                WHERE id = $id_puesto
            )
          AND pp.departamento_id = (
                SELECT departamento_id
                FROM puesto
                WHERE id = $id_puesto
            )
        ORDER BY pp.nivel ASC, nombre_completo
    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Jefes encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener jefes.', null, $e->getMessage());
        }
    }

    public static function getPersonasOrganigrama($departamento, $id_persona)
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
            //$nivelMax = max(array_column($puestos, 'nivel'));

            //$puestosTop = array_filter($puestos, function ($p) use ($nivelMax) {
            //    return $p['nivel'] == $nivelMax;
            //});
            $puestosTopIds = array_column($puestos, 'id');



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
                CONCAT(p.nombres, ' ', p.apellidop, ' ', p.apellidom, ' ----- (', UPPER(pp.nombre), ') '  ) AS nombre,
                ap.id_puesto
            FROM persona p
            INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            WHERE ap.id_puesto IN ($placeholdersStr)
              AND p.estatus != 'Baja'
            ORDER BY 
                pp.nivel DESC,      -- primero: puesto de mayor nivel
                nombre ASC          -- después: nombre de la persona
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

                -- NIVEL 1
                SELECT 
                    p.id,
                    p.nombres,
                    p.apellidop,
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    aj.id_jefe,
                    1 AS nivel
                FROM persona p
                JOIN asigna_puesto ap ON p.id = ap.id_persona
                JOIN puesto pp ON pp.id = ap.id_puesto
                JOIN asigna_jefe aj ON p.id = aj.id_persona
                WHERE p.estatus != 'Baja'
                  AND aj.id_jefe = $id_persona  -- ← tu jefe raíz
            
                UNION ALL
            
                -- NIVELES 2–4
                SELECT 
                    p2.id,
                    p2.nombres,
                    p2.apellidop,
                    ap2.id_puesto,
                    pp2.nombre AS nombre_puesto,
                    aj2.id_jefe,
                    j.nivel + 1 AS nivel
                FROM persona p2
                JOIN asigna_puesto ap2 ON p2.id = ap2.id_persona
                JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                JOIN asigna_jefe aj2 ON p2.id = aj2.id_persona
                JOIN Jerarquia j ON aj2.id_jefe = j.id
                WHERE p2.estatus != 'Baja'
                  AND j.nivel < 4
            )
            
            SELECT JSON_OBJECT(
                'id_jefe', $id_persona,
                'nombre_jefe', (
                    SELECT CONCAT(nombres, ' ', apellidop)
                    FROM persona
                    WHERE id = $id_persona
                ),
                'subordinados', (
                    SELECT COALESCE(JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'id', j1.id,
                            'nombre', CONCAT(j1.nombres, ' ', j1.apellidop),
                            'id_puesto', j1.id_puesto,
                            'nombre_puesto', j1.nombre_puesto,
                            'nivel', j1.nivel,
            
                            'subordinados', (
                                SELECT COALESCE(JSON_ARRAYAGG(
                                    JSON_OBJECT(
                                        'id', j2.id,
                                        'nombre', CONCAT(j2.nombres, ' ', j2.apellidop),
                                        'id_puesto', j2.id_puesto,
                                        'nombre_puesto', j2.nombre_puesto,
                                        'nivel', j2.nivel,
            
                                        'subordinados', (
                                            SELECT COALESCE(JSON_ARRAYAGG(
                                                JSON_OBJECT(
                                                    'id', j3.id,
                                                    'nombre', CONCAT(j3.nombres, ' ', j3.apellidop),
                                                    'id_puesto', j3.id_puesto,
                                                    'nombre_puesto', j3.nombre_puesto,
                                                    'nivel', j3.nivel,
            
                                                    'subordinados', (
                                                        SELECT COALESCE(JSON_ARRAYAGG(
                                                            JSON_OBJECT(
                                                                'id', j4.id,
                                                                'nombre', CONCAT(j4.nombres, ' ', j4.apellidop),
                                                                'id_puesto', j4.id_puesto,
                                                                'nombre_puesto', j4.nombre_puesto,
                                                                'nivel', j4.nivel
                                                            )
                                                        ), JSON_ARRAY())
                                                        FROM Jerarquia j4
                                                        WHERE j4.id_jefe = j3.id
                                                          AND j4.nivel = 4
                                                    )
                                                )
                                            ), JSON_ARRAY())
                                            FROM Jerarquia j3
                                            WHERE j3.id_jefe = j2.id
                                              AND j3.nivel = 3
                                        )
                                    )
                                ), JSON_ARRAY())
                                FROM Jerarquia j2
                                WHERE j2.id_jefe = j1.id
                                  AND j2.nivel = 2
                            )
                        )
                    ), JSON_ARRAY())
                    FROM Jerarquia j1
                    WHERE j1.id_jefe = $id_persona
                      AND j1.nivel = 1
                )
            ) AS organigrama_json;


    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
    ////////////////////////////////////////ES EL DE ADMIN
    public static function getConsultaDepartamentoGestor($perfil_id)
    {
        if($perfil_id == 1 OR $perfil_id == 2 OR $perfil_id == 3 OR $perfil_id == 396){
            $complet = '';
        }
        else
        {
            $complet = 'WHERE pd.idPersona = ' . $perfil_id;

        }
        $query = <<<SQL
           SELECT DISTINCT d.*
            FROM privilegios_departamento pd
            INNER JOIN puesto p
                    ON p.id = pd.idPuesto
            INNER JOIN departamento d
                    ON d.id = p.departamento_id
            $complet
            ORDER BY d.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
    ////////////////////////////////////////
    public static function getConsultaDepartamentoGestorOrganigrama($departamento)
    {

        $query = <<<SQL
           SELECT *
            FROM  puesto p
            WHERE p.departamento_id  = $departamento
            ORDER BY p.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
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
        $id_jefe = addslashes($data['id_jefe']);
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

            // Si no tiene jefe, él mismo será su jefe
            $id_jefe = isset($data['id_jefe']) && $data['id_jefe'] !== ''
                ? (int)$data['id_jefe']
                : $id_persona;

            if ($result)
            {
                $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto
                        (id, id_persona, id_puesto, fecha_asignacion, activo)
                    VALUES
                        (DEFAULT, $id_persona, $id_puesto, NOW(), 1)
                ");

                            $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_jefe
                        (id, id_persona, id_jefe, fecha_inicio, fecha_fin)
                    VALUES
                        (DEFAULT, $id_persona, $id_jefe, NOW(), NOW())
                ");
            }

            return self::resultado(true, 'Persona insertada correctamente.', $result);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al insertar persona.', null, $e->getMessage());
        }
    }

    public static function guardarAusencia($data)
    {
        $db = new Database();

        $id_ausencia  = isset($data['idAusencia']) && $data['idAusencia'] !== ''
            ? (int)$data['idAusencia']
            : null;

        $id_persona   = (int)$data['idPersona'];
        $id_razon     = (int)$data['idRazon'];
        $descripcion  = addslashes($data['descripcion'] ?? '');
        $fecha_inicio = addslashes($data['fechaInicio']);
        $fecha_fin    = addslashes($data['fechaFin']);
        $creado_por   = addslashes($_SESSION['usuario'] ?? 'sistema');

        try {

            // 🔄 UPDATE
            if ($id_ausencia) {

                $db->queryOne("
                UPDATE __SPARTA_SECRET_REDACTED__.ausencia
                SET
                    id_razon     = $id_razon,
                    descripcion  = '$descripcion',
                    fecha_inicio = '$fecha_inicio',
                    fecha_fin    = '$fecha_fin'
                WHERE id = $id_ausencia
                LIMIT 1
            ");

                return self::resultado(
                    true,
                    'Ausencia actualizada correctamente.',
                    ['id' => $id_ausencia]
                );
            }

            // ➕ INSERT
            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.ausencia
                (id_persona, id_razon, descripcion, fecha_inicio, fecha_fin, creado_por, activo)
            VALUES
                ($id_persona, $id_razon, '$descripcion', '$fecha_inicio', '$fecha_fin', '$creado_por', 1)
        ");

            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");

            return self::resultado(
                true,
                'Ausencia registrada correctamente.',
                $result
            );

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al guardar ausencia.',
                null,
                $e->getMessage()
            );
        }
    }



    public static function UpdatePersona($data)
    {
        $id_persona      = (int)$data['id'];
        $nombres         = addslashes($data['nombres']);
        $apellidop       = addslashes($data['apellidop']);
        $apellidom       = addslashes($data['apellidom']);
        $correo          = addslashes($data['correo'] ?? '');
        $telefono_uno    = addslashes($data['telefono_uno'] ?? '');
        $id_jefe         = (int)$data['jefe_id'];
        $id_puesto       = (int)$data['puesto_id'];
        $user_name       = addslashes($data['usuario']);
        $password        = addslashes($data['contrasena']);

        try {
            $db = new Database();

            // 1️⃣ UPDATE PERSONA
            $db->queryOne("
            UPDATE __SPARTA_SECRET_REDACTED__.persona
            SET 
                nombres      = '$nombres',
                apellidop    = '$apellidop',
                apellidom    = '$apellidom',
                correo       = '$correo',
                telefono_uno = '$telefono_uno',
                user_name    = '$user_name',
                password     = '$password'
            WHERE id = $id_persona
        ");

            // 2️⃣ ASIGNA JEFE (si existe UPDATE, si no INSERT)
            $existeJefe = $db->queryOne("
            SELECT id 
            FROM asigna_jefe 
            WHERE id_persona = $id_persona
            LIMIT 1
        ");

            if ($existeJefe) {
                $db->queryOne("
                UPDATE asigna_jefe
                SET id_jefe = $id_jefe
                WHERE id_persona = $id_persona
            ");
            } else {
                $db->queryOne("
                INSERT INTO asigna_jefe (id_persona, id_jefe)
                VALUES ($id_persona, $id_jefe)
            ");
            }

            // 3️⃣ ASIGNA PUESTO (si existe UPDATE, si no INSERT)
            $existePuesto = $db->queryOne("
            SELECT id 
            FROM asigna_puesto 
            WHERE id_persona = $id_persona
            LIMIT 1
        ");

            if ($existePuesto) {
                $db->queryOne("
                UPDATE asigna_puesto
                SET id_puesto = $id_puesto
                WHERE id_persona = $id_persona
            ");
            } else {
                $db->queryOne("
                INSERT INTO asigna_puesto (id_persona, id_puesto)
                VALUES ($id_persona, $id_puesto)
            ");
            }

            return self::resultado(true, 'Persona actualizada correctamente.', null);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar persona.', null, $e->getMessage());
        }
    }

    public static function registrarBajaGestor($data)
    {
        try {
            $db = new Database();

            // 🔹 Escapamos valores
            $id_persona  = addslashes($data['id_gestor']);
            $motivo      = addslashes($data['motivo']);
            $descripcion = addslashes($data['descripcion']);
            $fecha_baja  = addslashes($data['fecha_baja']);
            $usuario_baja  = addslashes($data['usuario_baja']);
            $archivos    = $data['archivos'] ?? [];

            // 1️⃣ Insertar la baja en baja_persona
            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.baja_persona
            (id_persona, motivo, fecha_baja, descripcion, usuario_baja)
            VALUES
            ('$id_persona', '$motivo', '$fecha_baja', '$descripcion', '$usuario_baja')
        ");

            // Obtener el ID de la baja recién creada
            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id_baja = isset($result['id']) ? intval($result['id']) : null;

            if (!$id_baja) {
                return self::resultado(false, 'No se pudo obtener el ID de la baja.');
            }

            // 2️⃣ Insertar cada archivo en carga_documento_persona
            foreach ($archivos as $archivo) {

                // Asumimos que el documento 'Documento baja' ya existe con id = 15
                $id_documento = 15;

                $archivoEsc = addslashes($archivo);

                $db->queryOne("
                INSERT INTO __SPARTA_SECRET_REDACTED__.carga_documento_persona
                (id_persona, id_documento, archivo, fecha_carga)
                VALUES
                ('$id_persona', '$id_documento', '$archivoEsc', NOW())
            ");
            }

            // 3️⃣ Actualizar estatus de la persona a 'Baja'
            $db->queryOne("
            UPDATE __SPARTA_SECRET_REDACTED__.persona
            SET estatus = 'Baja'
            WHERE id = '$id_persona'
        ");

            return self::resultado(true, 'Baja registrada correctamente con archivos.');

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar la baja.', null, $e->getMessage());
        }
    }







}
