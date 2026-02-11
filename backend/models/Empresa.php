<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseSegundometro;
use Core\DatabaseAWS;
use Core\DatabaseLegacy;

class Empresa extends Model
{
    public static function getConsultaPersona()
    {
        $query = <<<SQL
           SELECT p.id,
                   p.nombres,
                   p.apellidop,
                   ap.id_puesto,
                   aj.id_jefe
            FROM persona p
            JOIN asigna_puesto ap ON p.id = ap.id_persona
            LEFT JOIN asigna_jefe aj 
                  ON p.id = aj.id_persona 
                 AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            WHERE p.estatus != 'Baja'
            LIMIT 1
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }





    public static function getConsultaPorNombre($nombre)
    {
        $query = <<<SQL
           SELECT Id_credito, Nombre_cliente 
            FROM tbl_segundometro_semana 
            WHERE Nombre_cliente LIKE '%$nombre%'
            LIMIT 10
        SQL;

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Nombres encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }


    public static function getConsultaDireccionEstadoCuenta($id_credito)
    {
        // Intentar obtener más campos si están disponibles en la tabla
        $query = <<<SQL
           SELECT 
               Domicilio_Completo,
               Id_credito,
               Id_cliente,
               Nombre_cliente
           FROM tbl_segundometro_semana 
           WHERE Id_credito = $id_credito
           LIMIT 1
        SQL;

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Dirección encontrada.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaReferenciasEstadoCuenta($id_credito)
    {
        $query = <<<SQL
               SELECT 
            o.id_oferta AS id_credito,
            CONCAT(p.primer_nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) AS nombre_completo,
            CONCAT(
                COALESCE(p2.nombre_referencia1,''), ' ',
                COALESCE(p2.apellido_paterno_referencia1,''), ' ',
                COALESCE(p2.apellido_materno_referencia1,'')
            ) AS nombre_completo_referencia1,
            COALESCE(p2.telefono_referencia1,'') AS telefono_referencia1,
            CONCAT(
                COALESCE(p2.nombre_referencia2,''), ' ',
                COALESCE(p2.apellido_paterno_referencia2,''), ' ',
                COALESCE(p2.apellido_materno_referencia2,'')
            ) AS nombre_completo_referencia2,
            COALESCE(p2.telefono_referencia2,'') AS telefono_referencia2,
            '' AS nombre_referencia_3,
            '' AS telefono_referencia_3
        FROM oferta o
        INNER JOIN persona p ON o.fk_persona = p.id_persona
        LEFT JOIN persona_adicionales p2 ON p2.fk_persona = p.id_persona
        WHERE o.id_oferta = $id_credito
        SQL;

        try {
            $db = new \core\DatabaseMaxiProd();
            $r = $db->queryAll($query);
           
            return self::resultado(true, 'Dirección encontrada.', $r);
        } catch (\Exception $e) {
            
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getNotasNum($id_credito)
    {
        $query = <<<SQL
               SELECT
            count(id_nota) as num
        FROM __SPARTA_SECRET_REDACTED__.notas_credito
        WHERE id_credito = $id_credito
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'numero de notas encontrado.', $r);
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

    public static function getObtenerUltimoCorte()
    {
        try {
            $db = new DatabaseSegundometro();

            $cols = $db->queryAll("
            SELECT COLUMN_NAME 
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '__SPARTA_SECRET_REDACTED__'
              AND TABLE_NAME = 'tbl_segundometro_semana'
              AND COLUMN_NAME LIKE 'Dias_mora_%'
        ");

            if (!$cols) {
                return self::resultado(false, "No existen columnas Dias_mora_%", null);
            }

            $ordenDias = [
                'Lunes' => 1,
                'Martes' => 2,
                'Miercoles' => 3,
                'Jueves' => 4,
                'Viernes' => 5,
                'Sabado' => 6,
                'Domingo' => 7,
            ];

            $cortes = [];

            foreach ($cols as $row) {
                $col = $row['COLUMN_NAME'];

                // SOLO columnas tipo Dias_mora_Dia_HH_MM
                if (!preg_match('/^Dias_mora_(Lunes|Martes|Miercoles|Jueves|Viernes|Sabado|Domingo)_(\d{2})_(\d{2})$/', $col, $m)) {
                    continue;
                }

                $dia   = $m[1];
                $hora  = (int)$m[2];
                $min   = (int)$m[3];

                $peso = ($ordenDias[$dia] * 10000) + ($hora * 100) + $min;

                $cortes[] = [
                    'columna' => $col,
                    'peso'    => $peso
                ];
            }

            // Ordenar por fecha real DESC
            usort($cortes, fn($a, $b) => $b['peso'] <=> $a['peso']);

            // Buscar el primer corte que tenga datos
            foreach ($cortes as $corte) {
                $col = $corte['columna'];

                $sql = "SELECT 1
            FROM tbl_segundometro_semana
            WHERE `$col` IS NOT NULL
              AND TRIM(`$col`) <> ''
            LIMIT 1";

                if ($db->queryOne($sql)) {
                    return self::resultado(true, "Corte encontrado.", [
                        "columna" => $col
                    ]);
                }
            }

            return self::resultado(false, "No hay cortes con datos.", null);

        } catch (\Exception $e) {
            return self::resultado(false, "Error al procesar la solicitud.", null, $e->getMessage());
        }
    }


    public static function descargarCorte($corte)
    {
        // Sanitizar corte
        if (!$corte) {
            return self::resultado(false, "No se recibió el nombre del corte.", null);
        }
        $corte = preg_replace('/[^a-zA-Z0-9_]/', '', $corte);

        try {

            /* -------------------------------------------------
                1) OBTENER DATOS DEL GOOGLE CLOUD (MYSQL PRINCIPAL)
            -------------------------------------------------- */
            $db = new DatabaseSegundometro();

            $sqlGoogle = "
            SELECT 
                Id_credito as Id_oferta,
                CONCAT(Id_credito, '_', Id_cliente) AS id_original, 
                Celular AS Telefono, 
                'Transferencia' AS fideicomiso, 
                Id_cliente AS mkm,
                Id_credito AS id_credit, 
                nombre_cliente AS nombre, 
                1 AS pagos_vencidos,
                saldo_vencido_inicio AS monto_vencido,
                '' AS bucket, 
                '' AS fecha_de_pago, 
                '' AS telefono_1,
                'Transferencia' AS tipoo_de_pago, 
                Referencia_stp AS clabe,
                'STP' AS banco, 
                '' AS atributo_segmento
            FROM tbl_segundometro_semana
            WHERE 
                $corte BETWEEN 1 AND 7
                AND Bucket_Morosidad_Real = 'b) 1 a 7 dias'
            ORDER BY KT
        ";

            $rowsGoogle = $db->queryAll($sqlGoogle);


            if (!$rowsGoogle) {
                return self::resultado(false, "No hay datos para el corte seleccionado ($corte).", []);
            }

            /* -------------------------------------------------
                2) EXTRAER LISTA DE id_oferta
            -------------------------------------------------- */
            $idList = array_filter(array_column($rowsGoogle, 'Id_oferta'));

            if (empty($idList)) {
                return self::resultado(false, "No hay id_oferta para consultar en AWS.", []);
            }

            /* -------------------------------------------------
                3) CONSULTA AWS POR CHUNKS
            -------------------------------------------------- */
            $dbAWS = new DatabaseAWS();

            $awsMerge = [];
            $chunks = array_chunk($idList, 50);

            foreach ($chunks as $chunk) {

                $idsText = "(" . implode(",", $chunk) . ")";

                $sqlAWS = "
                SELECT 
                    o.id_oferta,
                    CONCAT(p.primer_nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) AS nombre_completo,
                    CONCAT(p2.nombre_referencia1, ' ', p2.apellido_paterno_referencia1, ' ', p2.apellido_materno_referencia1) AS nombre_completo_referencia1,
                    p2.telefono_referencia1,
                    CONCAT(p2.nombre_referencia2, ' ', p2.apellido_paterno_referencia2, ' ', p2.apellido_materno_referencia2) AS nombre_completo_referencia2,
                    p2.telefono_referencia2,
                    '' AS nombre_referencia_3,
                    '' AS telefono_referencia_3,
                    0 AS Motivo_de_no_Pago,
                    0 AS cuando_le_pagan,
                    0 AS Giro_de_Trabajo,
                    0 AS hora_de_pago
                FROM oferta o
                INNER JOIN persona p ON o.fk_persona = p.id_persona
                LEFT JOIN persona_adicionales p2 ON p2.fk_persona = p.id_persona
                WHERE o.id_oferta IN $idsText
            ";

                // Aquí usas tu método nativo
                $rowsAWS = $dbAWS->queryAll($sqlAWS);

                foreach ($rowsAWS as $r) {
                    $awsMerge[$r['id_oferta']] = $r;
                }
            }

            /* -------------------------------------------------
                4) MERGE GOOGLE + AWS
            -------------------------------------------------- */
            $finalRows = [];

            foreach ($rowsGoogle as $row) {
                $id = $row['Id_oferta'];

                $finalRows[] = array_merge(
                    $row,
                    $awsMerge[$id] ?? []
                );
            }

           /// var_dump($finalRows);

            return self::resultado(true, "Datos del corte obtenidos.", $finalRows);

        } catch (\Exception $e) {
            return self::resultado(false, "Error al procesar la solicitud.", null, $e->getMessage());
        }
    }


    public static function getPersonasDetalle($idPersona)
    {
        try {
            $db = new Database();

            $query = <<<SQL
            SELECT 
                p.*,
                ap.id_puesto, dd.nombre as departamento, dd.id as id_departamento, aj.id_jefe, p.password
            FROM persona p
            INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
            INNER JOIN puesto pu ON pu.id = ap.id_puesto
            INNER JOIN departamento dd ON dd.id = pu.departamento_id
            INNER JOIN asigna_jefe aj ON aj.id_persona = p.id
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

    public static function descargarReporteLegacy()
    {
        
        try {

            /* -------------------------------------------------
                1) OBTENER DATOS DEL GOOGLE CLOUD (MYSQL PRINCIPAL)
            -------------------------------------------------- */
            $db = new Database();

            $sql = "
        WITH RECURSIVE
        
        /* 1) Relación vigente persona -> jefe */
        aj_vigente AS (
            SELECT id_persona, id_jefe
            FROM asigna_jefe
            WHERE fecha_fin IS NULL
               OR fecha_fin >= CURDATE()
        ),
        
        /* 2) Jerarquía completa */
        jerarquia AS (
            /* jefe inmediato */
            SELECT
                p.id       AS persona_id,
                aj.id_jefe AS jefe_id,
                1          AS lvl
            FROM persona p
            LEFT JOIN aj_vigente aj
                   ON aj.id_persona = p.id
        
            UNION ALL
        
            /* jefes hacia arriba */
            SELECT
                j.persona_id,
                aj2.id_jefe,
                j.lvl + 1
            FROM jerarquia j
            JOIN aj_vigente aj2
                 ON aj2.id_persona = j.jefe_id
            WHERE j.jefe_id IS NOT NULL
              AND j.lvl < 10
        ),
        
        /* 3) Detalle del jefe + puesto legacy */
        jerarquia_detalle AS (
            SELECT
                j.persona_id,
                j.jefe_id,
                j.lvl,
        
                pj.numero_empleado AS jefe_numero_empleado,
                TRIM(CONCAT_WS(' ', pj.apellidop, pj.apellidom, pj.nombres, pj.segundo_nombre)) AS jefe_nombre,
        
                elp.id_puesto_legacy AS jefe_puesto_legacy
            FROM jerarquia j
            JOIN persona pj
                 ON pj.id = j.jefe_id
        
            LEFT JOIN asigna_puesto apj
                   ON apj.id_persona = j.jefe_id
        
            LEFT JOIN equivalencias_legacy_puestos elp
                   ON elp.id_puesto = apj.id_puesto
        ),
        
        /* 4) Línea completa de mando */
        linea_jefes AS (
            SELECT
                persona_id,
        
                MAX(CASE WHEN jefe_puesto_legacy = 2 THEN jefe_numero_empleado END) AS supervisor_id,
                MAX(CASE WHEN jefe_puesto_legacy = 2 THEN jefe_nombre END)          AS supervisor_nombre,
        
                MAX(CASE WHEN jefe_puesto_legacy = 3 THEN jefe_numero_empleado END) AS subgerente_id,
                MAX(CASE WHEN jefe_puesto_legacy = 3 THEN jefe_nombre END)          AS subgerente_nombre,
        
                MAX(CASE WHEN jefe_puesto_legacy = 4 THEN jefe_numero_empleado END) AS gerente_id,
                MAX(CASE WHEN jefe_puesto_legacy = 4 THEN jefe_nombre END)          AS gerente_nombre,
        
                MAX(CASE WHEN jefe_puesto_legacy = 5 THEN jefe_numero_empleado END) AS subdirector_id,
                MAX(CASE WHEN jefe_puesto_legacy = 5 THEN jefe_nombre END)          AS subdirector_nombre
        
            FROM jerarquia_detalle
            GROUP BY persona_id
        )
        
        SELECT
            p.numero_empleado AS external_id,
            p.user_name       AS username,
        
            TRIM(CONCAT_WS(' ', p.apellidop, p.apellidom, p.nombres, p.segundo_nombre)) AS name,
        
            p.password AS password,
            '' AS legion,
        
            /* role legacy */
            pl.clave AS role,
        
            '' AS color,
        
            /* línea jerárquica */
            lj.supervisor_id,
            COALESCE(lj.supervisor_nombre, '')   AS supervisor_nombre,
        
            lj.subgerente_id,
            COALESCE(lj.subgerente_nombre, '')   AS subgerente_nombre,
        
            lj.gerente_id,
            COALESCE(lj.gerente_nombre, '')      AS gerente_nombre,
        
            lj.subdirector_id,
            COALESCE(lj.subdirector_nombre, '')  AS subdirector_nombre,
        
            '' AS city,
            '' AS state,
            '' AS municipality,
            '' AS settlement_tupe,
            '' AS postal_code
        
        FROM persona p
        
        /* puesto del usuario */
        JOIN asigna_puesto ap
             ON ap.id_persona = p.id
        
        JOIN puesto pp
             ON pp.id = ap.id_puesto
        
        /* 🔴 FILTRO REAL POR DEPARTAMENTO */
        JOIN departamento d
             ON d.id = pp.departamento_id
            AND d.id IN (3, 13, 4, 8)
        
        /* equivalencia legacy */
        LEFT JOIN equivalencias_legacy_puestos el
               ON el.id_puesto = pp.id
        
        LEFT JOIN puestos_legacy pl
               ON pl.id = el.id_puesto_legacy
        
        LEFT JOIN linea_jefes lj
               ON lj.persona_id = p.id
        
        WHERE p.estatus <> 'Baja'
        
        ORDER BY COALESCE(pp.nivel, 999) ASC;

        ";

            $rows = $db->queryAll($sql);


            if (!$rows) {
                return self::resultado(false, "No hay datos para el corte seleccionado ().", []);
            }

            
           /// var_dump($finalRows);

            return self::resultado(true, "Datos del corte obtenidos.", $rows);

        } catch (\Exception $e) {
            return self::resultado(false, "Error al procesar la solicitud.", null, $e->getMessage());
        }
    }





}
