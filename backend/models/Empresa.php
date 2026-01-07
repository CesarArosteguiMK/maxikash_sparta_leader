<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseSegundometro;
use Core\DatabaseAWS;

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


    public static function getConsultaDireccion($id_credito)
    {
        $query = <<<SQL
           SELECT Domicilio_Completo from tbl_segundometro_semana where Id_credito = $id_credito;
        SQL;

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Dirección encontrada.', $r);
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

            // 1) Obtener columnas que empiecen con Dias_mora_
            $cols = $db->queryAll("
            SELECT COLUMN_NAME 
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = '__SPARTA_SECRET_REDACTED__'
              AND TABLE_NAME = 'tbl_segundometro_semana'
              AND COLUMN_NAME LIKE 'Dias_mora_%'
            ORDER BY COLUMN_NAME ASC
        ");

            if (!$cols) {
                return self::resultado(false, "No existen columnas Dias_mora_%", null);
            }

            $columnas = array_column($cols, "COLUMN_NAME");

            // 2) Recorrer de la última a la primera (para obtener la más reciente)
            $ultima = null;

            foreach (array_reverse($columnas) as $col) {

                // 3) Verificar si tiene algún valor real
                $sql = "SELECT 1 FROM tbl_segundometro_semana 
                    WHERE `$col` IS NOT NULL 
                      AND `$col` <> '' 
                    LIMIT 1";

                $existe = $db->queryOne($sql);

                if ($existe) {
                    $ultima = $col;
                    break;
                }
            }

            if (!$ultima) {
                return self::resultado(false, "No hay cortes con datos.", null);
            }

            // 4) Respuesta final
            return self::resultado(true, "Corte encontrado.", [
                "columna" => $ultima
            ]);

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







}
