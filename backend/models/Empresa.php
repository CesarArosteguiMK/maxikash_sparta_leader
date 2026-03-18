<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseMaxiGuat;
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
            WHERE Nombre_cliente LIKE :nombre
            LIMIT 10
        SQL;
        $params = ['nombre' => '%' . $nombre . '%'];

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Nombres encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaDireccionEstadoCuenta($id_credito)
    {
        $query = <<<SQL
           SELECT
               Domicilio_Completo,
               Id_credito,
               Id_cliente,
               Nombre_cliente
           FROM tbl_segundometro_semana
           WHERE Id_credito = :id_credito
           LIMIT 1
        SQL;
        $params = ['id_credito' => $id_credito];

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll($query, $params);
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
            COALESCE(p.rfc, '') AS rfc,
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
        WHERE o.id_oferta = :id_credito
        SQL;
        $params = ['id_credito' => $id_credito];

        try {
            $db = new \core\DatabaseMaxiProd();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Dirección encontrada.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getGuatemalaEstadoCuenta($id_credito)
    {
        $query = <<<SQL
               SELECT * FROM registro_croop WHERE pkey_credito = :id_credito
        SQL;
        $params = ['id_credito' => $id_credito];

        try {
            $db = new \core\DatabaseMaxiGuat();
            $r = $db->queryAll($query, $params);
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
        WHERE id_credito = :id_credito
        SQL;
        $params = ['id_credito' => $id_credito];

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'numero de notas encontrado.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaDepartamentos($post = [])
    {
        try {
            $db = new Database();
            $r = $db->queryAll("
                SELECT d.id AS departamento_id, d.nombre AS departamento_nombre
                FROM departamento d
                WHERE (d.activo IS NULL OR d.activo = 1)
                ORDER BY d.nombre
            ");
            $datos = is_array($r) ? $r : [];
            return self::resultado(true, 'Departamentos encontrados.', $datos);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', [], $e->getMessage());
        }
    }

    public static function getConsultaPuestos($departamento)
    {
        $query = <<<SQL
        SELECT
            p.id, p.nombre, p.nivel, d.nombre as departamento
        FROM puesto p
        INNER JOIN departamento d ON d.id = p.departamento_id
        SQL;

        $params = [];

        if ($departamento != null) {
            $query .= " WHERE d.id = :departamento";
            $params['departamento'] = $departamento;
        }

        try {
            $db = new Database();
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
                'Lunes'     => 1, 'Martes'   => 2, 'Miercoles' => 3,
                'Jueves'    => 4, 'Viernes'  => 5, 'Sabado'    => 6,
                'Domingo'   => 7,
            ];

            $cortes = [];
            foreach ($cols as $row) {
                $col = $row['COLUMN_NAME'];
                if (!preg_match(
                    '/^Dias_mora_(Lunes|Martes|Miercoles|Jueves|Viernes|Sabado|Domingo)_(\d{2})_(\d{2})$/',
                    $col, $m
                )) continue;

                $peso     = ($ordenDias[$m[1]] * 10000) + ((int)$m[2] * 100) + (int)$m[3];
                $cortes[] = ['columna' => $col, 'peso' => $peso];
            }

            usort($cortes, fn($a, $b) => $b['peso'] <=> $a['peso']);

            foreach ($cortes as $corte) {
                $col = $corte['columna'];
                $sql = "SELECT 1
                        FROM tbl_segundometro_semana
                        WHERE `$col` IS NOT NULL
                          AND TRIM(`$col`) <> ''
                        LIMIT 1";

                if ($db->queryOne($sql)) {
                    return self::resultado(true, "Corte encontrado.", ['columna' => $col]);
                }
            }

            return self::resultado(false, "No hay cortes con datos.", null);

        } catch (\Exception $e) {
            return self::resultado(false, "Error al procesar la solicitud.", null, $e->getMessage());
        }
    }

    public static function descargarCorte($corte)
    {
        if (!$corte) {
            return self::resultado(false, "No se recibió el nombre del corte.", null);
        }
        $corte = preg_replace('/[^a-zA-Z0-9_]/', '', $corte);

        try {
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

            $idList = array_filter(array_column($rowsGoogle, 'Id_oferta'));

            if (empty($idList)) {
                return self::resultado(false, "No hay id_oferta para consultar en AWS.", []);
            }

            $dbAWS    = new DatabaseAWS();
            $awsMerge = [];
            $chunks   = array_chunk($idList, 50);

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
                        0  AS Motivo_de_no_Pago,
                        0  AS cuando_le_pagan,
                        0  AS Giro_de_Trabajo,
                        0  AS hora_de_pago
                    FROM oferta o
                    INNER JOIN persona p  ON o.fk_persona   = p.id_persona
                    LEFT  JOIN persona_adicionales p2 ON p2.fk_persona = p.id_persona
                    WHERE o.id_oferta IN $idsText
                ";

                $rowsAWS = $dbAWS->queryAll($sqlAWS);
                foreach ($rowsAWS as $r) {
                    $awsMerge[$r['id_oferta']] = $r;
                }
            }

            $finalRows = [];
            foreach ($rowsGoogle as $row) {
                $id          = $row['Id_oferta'];
                $finalRows[] = array_merge($row, $awsMerge[$id] ?? []);
            }

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
                    ap.id_puesto, dd.nombre as departamento, dd.id as id_departamento,
                    aj.id_jefe, p.password
                FROM persona p
                INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
                INNER JOIN puesto        pu ON pu.id = ap.id_puesto
                INNER JOIN departamento  dd ON dd.id = pu.departamento_id
                INNER JOIN asigna_jefe   aj ON aj.id_persona = p.id
                WHERE p.id = :id_persona
                  AND p.estatus != 'Baja'
                LIMIT 1
            SQL;

            $persona = $db->queryOne($query, ['id_persona' => $idPersona]);
            return self::resultado(true, 'Persona encontrada.', $persona);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function descargarReporteLegacy()
    {
        try {
            $db  = new Database();
            $sql = "
                WITH RECURSIVE

                aj_vigente AS (
                    SELECT id_persona, id_jefe
                    FROM asigna_jefe
                    WHERE fecha_fin IS NULL
                       OR fecha_fin >= CURDATE()
                ),

                jerarquia AS (
                    SELECT
                        p.id       AS persona_id,
                        aj.id_jefe AS jefe_id,
                        1          AS lvl
                    FROM persona p
                    LEFT JOIN aj_vigente aj ON aj.id_persona = p.id

                    UNION ALL

                    SELECT
                        j.persona_id,
                        aj2.id_jefe,
                        j.lvl + 1
                    FROM jerarquia j
                    JOIN aj_vigente aj2 ON aj2.id_persona = j.jefe_id
                    WHERE j.jefe_id IS NOT NULL
                      AND j.lvl < 10
                ),

                jerarquia_detalle AS (
                    SELECT
                        j.persona_id,
                        j.jefe_id,
                        j.lvl,
                        pj.numero_empleado AS jefe_numero_empleado,
                        TRIM(CONCAT_WS(' ', pj.apellidop, pj.apellidom, pj.nombres, pj.segundo_nombre)) AS jefe_nombre,
                        elp.id_puesto_legacy AS jefe_puesto_legacy
                    FROM jerarquia j
                    JOIN persona pj ON pj.id = j.jefe_id
                    LEFT JOIN asigna_puesto apj ON apj.id_persona = j.jefe_id
                    LEFT JOIN equivalencias_legacy_puestos elp ON elp.id_puesto = apj.id_puesto
                ),

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
                    p.password        AS password,
                    ''                AS legion,
                    pl.clave          AS role,
                    ''                AS color,
                    lj.supervisor_id,
                    COALESCE(lj.supervisor_nombre,  '')  AS supervisor_nombre,
                    lj.subgerente_id,
                    COALESCE(lj.subgerente_nombre,  '')  AS subgerente_nombre,
                    lj.gerente_id,
                    COALESCE(lj.gerente_nombre,     '')  AS gerente_nombre,
                    lj.subdirector_id,
                    COALESCE(lj.subdirector_nombre, '')  AS subdirector_nombre,
                    '' AS city, '' AS state, '' AS municipality,
                    '' AS settlement_tupe, '' AS postal_code
                FROM persona p
                JOIN asigna_puesto ap ON ap.id_persona = p.id
                JOIN puesto        pp ON pp.id = ap.id_puesto
                JOIN departamento  d  ON d.id  = pp.departamento_id AND d.id IN (3,13,4,8)
                LEFT JOIN equivalencias_legacy_puestos el ON el.id_puesto = pp.id
                LEFT JOIN puestos_legacy               pl ON pl.id = el.id_puesto_legacy
                LEFT JOIN linea_jefes                  lj ON lj.persona_id = p.id
                WHERE p.estatus <> 'Baja'
                ORDER BY COALESCE(pp.nivel, 999) ASC
            ";

            $rows = $db->queryAll($sql);

            if (!$rows) {
                return self::resultado(false, "No hay datos para el reporte Legacy.", []);
            }

            return self::resultado(true, "Datos del corte obtenidos.", $rows);

        } catch (\Exception $e) {
            return self::resultado(false, "Error al procesar la solicitud.", null, $e->getMessage());
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  Detectar el corte más reciente con datos reales
    //  (Martes → Lunes, misma lógica que getObtenerUltimoCorte)
    // ══════════════════════════════════════════════════════════════
    public static function getCorteActual(): ?string
    {
        $ordenDias = [
            'Lunes'     => 1, 'Martes'   => 2, 'Miercoles' => 3,
            'Jueves'    => 4, 'Viernes'  => 5, 'Sabado'    => 6,
            'Domingo'   => 7,
        ];

        try {
            $db   = new DatabaseSegundometro();
            $cols = $db->queryAll("
                SELECT COLUMN_NAME
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '__SPARTA_SECRET_REDACTED__'
                  AND TABLE_NAME   = 'tbl_segundometro_semana'
                  AND COLUMN_NAME LIKE 'Dias_mora_%'
            ");

            $cortes = [];
            foreach ($cols as $row) {
                $col = $row['COLUMN_NAME'];
                if (!preg_match(
                    '/^Dias_mora_(Lunes|Martes|Miercoles|Jueves|Viernes|Sabado|Domingo)_(\d{2})_(\d{2})$/',
                    $col, $m
                )) continue;

                $peso     = ($ordenDias[$m[1]] * 10000) + ((int)$m[2] * 100) + (int)$m[3];
                $cortes[] = ['columna' => $col, 'peso' => $peso];
            }

            usort($cortes, fn($a, $b) => $b['peso'] <=> $a['peso']);

            foreach ($cortes as $c) {
                $col = $c['columna'];
                if ($db->queryOne("
                    SELECT 1
                    FROM tbl_segundometro_semana
                    WHERE `$col` IS NOT NULL
                      AND TRIM(`$col`) <> ''
                    LIMIT 1
                ")) {
                    return $col;
                }
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    // ══════════════════════════════════════════════════════════════
    //  Créditos con Fecha_primer_vencimiento = Lunes de cierre
    //  + comparativo bucket nacimiento vs corte actual dinámico
    // ══════════════════════════════════════════════════════════════
    public static function getVencimientosLunes(): array
    {
        $corteCol = self::getCorteActual();

        if (!$corteCol) {
            return [
                'success'      => false,
                'mensaje'      => 'No hay corte disponible.',
                'datos'        => [],
                'lunes_pasado' => null,
                'corte_actual' => null,
            ];
        }

        // Bucket calculado desde días mora del corte actual
        $bucketCorteSQL = "
            CASE
                WHEN `$corteCol` IS NULL              THEN NULL
                WHEN `$corteCol` < 1                  THEN 'a) Current'
                WHEN `$corteCol` BETWEEN 1  AND 7     THEN 'b) 1 a 7 dias'
                WHEN `$corteCol` BETWEEN 8  AND 30    THEN 'c) 8 a 30 dias'
                WHEN `$corteCol` BETWEEN 31 AND 60    THEN 'd) 31 a 60 dias'
                ELSE                                       'e) 61+ dias'
            END
        ";

        $sql = "
            SELECT
                t.Id_credito,
                t.Nombre_cliente,
                t.Bucket_Morosidad_Real          AS bucket_nacio,
                t.Gestor_Asignado,
                t.Jefe_de_Plaza,
                t.Zonal,
                t.Territorial,
                t.Cuotas_vencidas,
                t.Saldo_vencido_actualizado,
                t.Fecha_primer_vencimiento,
                `$corteCol`                      AS dias_mora_corte,
                ($bucketCorteSQL)                AS bucket_corte_actual,
                DATE_FORMAT(
                    DATE_SUB(CURDATE(),
                        INTERVAL IF((DAYOFWEEK(CURDATE())+5)%7=0, 7, (DAYOFWEEK(CURDATE())+5)%7) DAY
                    ), '%Y-%m-%d'
                ) AS lunes_calculado
            FROM tbl_segundometro_semana t
            WHERE
                STR_TO_DATE(t.Fecha_primer_vencimiento, '%Y-%m-%d') =
                DATE_SUB(CURDATE(),
                    INTERVAL IF((DAYOFWEEK(CURDATE())+5)%7=0, 7, (DAYOFWEEK(CURDATE())+5)%7) DAY
                )
            ORDER BY
                t.Territorial,
                t.Zonal,
                t.Jefe_de_Plaza,
                t.Gestor_Asignado,
                t.Nombre_cliente
        ";

        try {
            $db   = new DatabaseSegundometro();
            $rows = $db->queryAll($sql);

            $lunesPasado = !empty($rows)
                ? $rows[0]['lunes_calculado']
                : ($db->queryOne("
                    SELECT DATE_FORMAT(
                        DATE_SUB(CURDATE(),
                            INTERVAL IF((DAYOFWEEK(CURDATE())+5)%7=0, 7, (DAYOFWEEK(CURDATE())+5)%7) DAY
                        ), '%Y-%m-%d'
                    ) AS lunes_calculado
                  ")['lunes_calculado'] ?? null);

            foreach ($rows as &$row) {
                unset($row['lunes_calculado']);
            }
            unset($row);

            return [
                'success'      => true,
                'mensaje'      => 'Registros obtenidos.',
                'lunes_pasado' => $lunesPasado,
                'corte_actual' => $corteCol,
                'datos'        => $rows,
            ];

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

} // ← única llave de cierre de la clase
