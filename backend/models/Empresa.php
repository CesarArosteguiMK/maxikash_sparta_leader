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

    /**
     * Teléfono del titular (oferta → persona) en __SPARTA_SECRET_REDACTED__.
     * Prueba varias columnas habituales; en muchos créditos el dato está en celular/telefono_movil, no en telefono.
     */
    public static function getTelefonoTitularCredito($id_credito)
    {
        if ($id_credito === null || $id_credito === '') {
            return self::resultado(true, 'ok', '');
        }
        $columnas = [
            'telefono',
            'celular',
            'telefono_celular',
            'telefono_movil',
            'numero_celular',
            'tel_celular',
        ];
        try {
            $db = new \core\DatabaseMaxiProd();
            if (!$db) {
                return self::resultado(true, 'ok', '');
            }
            foreach ($columnas as $col) {
                if (!preg_match('/^[a-z0-9_]+$/i', $col)) {
                    continue;
                }
                try {
                    $row = $db->queryOne(
                        "SELECT TRIM(COALESCE(p.`{$col}`, '')) AS t
                         FROM oferta o
                         INNER JOIN persona p ON o.fk_persona = p.id_persona
                         WHERE o.id_oferta = :id_credito
                         LIMIT 1",
                        ['id_credito' => $id_credito]
                    );
                    $t = trim((string)($row['t'] ?? ''));
                    if ($t !== '') {
                        return self::resultado(true, 'ok', $t);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            return self::resultado(true, 'sin telefono', '');
        }
        return self::resultado(true, 'ok', '');
    }

    /**
     * Celular del titular en segundómetro (misma fuente que usa la vista de estado de cuenta en varios flujos).
     */
    public static function getCelularCreditoSegundometro($id_credito)
    {
        if ($id_credito === null || $id_credito === '') {
            return self::resultado(true, 'ok', '');
        }
        try {
            $db = new DatabaseSegundometro();
            $row = $db->queryOne(
                'SELECT TRIM(COALESCE(Celular, \'\')) AS t
                 FROM tbl_segundometro_semana
                 WHERE Id_credito = :id_credito
                 LIMIT 1',
                ['id_credito' => $id_credito]
            );
            $t = trim((string)($row['t'] ?? ''));
            return self::resultado(true, 'ok', $t);
        } catch (\Exception $e) {
            return self::resultado(true, 'ok', '');
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
    //  Cache 5 min para evitar information_schema + múltiples probes por request.
    // ══════════════════════════════════════════════════════════════
    private const CORTE_ACTUAL_CACHE_TTL = 300;


    public static function getCorteActual(): ?string
    {
        // ── 1. Caché en archivo ────────────────────────────────────────────
        $cacheDir  = defined('RAIZ') ? (RAIZ . '/storage/cache') : (__DIR__ . '/../storage/cache');
        $cacheFile = $cacheDir . '/corte_actual_vencimientos_lunes.json';

        if (is_file($cacheFile)) {
            $raw = @file_get_contents($cacheFile);
            if ($raw !== false) {
                $data = @json_decode($raw, true);
                if (is_array($data) && isset($data['expires'], $data['col'])
                    && $data['expires'] > time()) {
                    return $data['col'] ?: null;
                }
            }
        }

        // ── 2. Detectar columna por fecha/hora — CERO queries a la DB ─────
        $diasNombre = ['Domingo','Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'];
        $hoy        = $diasNombre[(int)date('w')];
        $horaActual = (int)date('H') * 100 + (int)date('i'); // 1430 = 14:30

        $ordenDias = [
            'Lunes'     => 1, 'Martes'    => 2, 'Miercoles' => 3,
            'Jueves'    => 4, 'Viernes'   => 5, 'Sabado'    => 6,
            'Domingo'   => 7,
        ];

        // Todos los cortes posibles, de mayor peso a menor (más reciente primero)
        $cortes = [
            ['dia' => 'Domingo',   'hhmm' => 2350, 'col' => 'Dias_mora_Domingo_23_50'],
            ['dia' => 'Domingo',   'hhmm' => 2030, 'col' => 'Dias_mora_Domingo_20_30'],
            ['dia' => 'Domingo',   'hhmm' => 1830, 'col' => 'Dias_mora_Domingo_18_30'],
            ['dia' => 'Domingo',   'hhmm' => 1630, 'col' => 'Dias_mora_Domingo_16_30'],
            ['dia' => 'Domingo',   'hhmm' => 1430, 'col' => 'Dias_mora_Domingo_14_30'],
            ['dia' => 'Domingo',   'hhmm' => 1330, 'col' => 'Dias_mora_Domingo_13_30'],
            ['dia' => 'Domingo',   'hhmm' => 1130, 'col' => 'Dias_mora_Domingo_11_30'],
            ['dia' => 'Domingo',   'hhmm' =>  930, 'col' => 'Dias_mora_Domingo_09_30'],
            ['dia' => 'Domingo',   'hhmm' =>  730, 'col' => 'Dias_mora_Domingo_07_30'],
            ['dia' => 'Sabado',    'hhmm' => 2350, 'col' => 'Dias_mora_Sabado_23_50'],
            ['dia' => 'Sabado',    'hhmm' => 2030, 'col' => 'Dias_mora_Sabado_20_30'],
            ['dia' => 'Sabado',    'hhmm' => 1830, 'col' => 'Dias_mora_Sabado_18_30'],
            ['dia' => 'Sabado',    'hhmm' => 1630, 'col' => 'Dias_mora_Sabado_16_30'],
            ['dia' => 'Sabado',    'hhmm' => 1430, 'col' => 'Dias_mora_Sabado_14_30'],
            ['dia' => 'Sabado',    'hhmm' => 1330, 'col' => 'Dias_mora_Sabado_13_30'],
            ['dia' => 'Sabado',    'hhmm' => 1130, 'col' => 'Dias_mora_Sabado_11_30'],
            ['dia' => 'Sabado',    'hhmm' =>  930, 'col' => 'Dias_mora_Sabado_09_30'],
            ['dia' => 'Sabado',    'hhmm' =>  730, 'col' => 'Dias_mora_Sabado_07_30'],
            ['dia' => 'Viernes',   'hhmm' => 2350, 'col' => 'Dias_mora_Viernes_23_50'],
            ['dia' => 'Viernes',   'hhmm' => 2030, 'col' => 'Dias_mora_Viernes_20_30'],
            ['dia' => 'Viernes',   'hhmm' => 1830, 'col' => 'Dias_mora_Viernes_18_30'],
            ['dia' => 'Viernes',   'hhmm' => 1630, 'col' => 'Dias_mora_Viernes_16_30'],
            ['dia' => 'Viernes',   'hhmm' => 1430, 'col' => 'Dias_mora_Viernes_14_30'],
            ['dia' => 'Viernes',   'hhmm' => 1330, 'col' => 'Dias_mora_Viernes_13_30'],
            ['dia' => 'Viernes',   'hhmm' => 1130, 'col' => 'Dias_mora_Viernes_11_30'],
            ['dia' => 'Viernes',   'hhmm' =>  930, 'col' => 'Dias_mora_Viernes_09_30'],
            ['dia' => 'Viernes',   'hhmm' =>  730, 'col' => 'Dias_mora_Viernes_07_30'],
            ['dia' => 'Jueves',    'hhmm' => 2350, 'col' => 'Dias_mora_Jueves_23_50'],
            ['dia' => 'Jueves',    'hhmm' => 2030, 'col' => 'Dias_mora_Jueves_20_30'],
            ['dia' => 'Jueves',    'hhmm' => 1830, 'col' => 'Dias_mora_Jueves_18_30'],
            ['dia' => 'Jueves',    'hhmm' => 1630, 'col' => 'Dias_mora_Jueves_16_30'],
            ['dia' => 'Jueves',    'hhmm' => 1430, 'col' => 'Dias_mora_Jueves_14_30'],
            ['dia' => 'Jueves',    'hhmm' => 1330, 'col' => 'Dias_mora_Jueves_13_30'],
            ['dia' => 'Jueves',    'hhmm' => 1130, 'col' => 'Dias_mora_Jueves_11_30'],
            ['dia' => 'Jueves',    'hhmm' =>  930, 'col' => 'Dias_mora_Jueves_09_30'],
            ['dia' => 'Jueves',    'hhmm' =>  730, 'col' => 'Dias_mora_Jueves_07_30'],
            ['dia' => 'Miercoles', 'hhmm' => 2350, 'col' => 'Dias_mora_Miercoles_23_50'],
            ['dia' => 'Miercoles', 'hhmm' => 2030, 'col' => 'Dias_mora_Miercoles_20_30'],
            ['dia' => 'Miercoles', 'hhmm' => 1830, 'col' => 'Dias_mora_Miercoles_18_30'],
            ['dia' => 'Miercoles', 'hhmm' => 1630, 'col' => 'Dias_mora_Miercoles_16_30'],
            ['dia' => 'Miercoles', 'hhmm' => 1430, 'col' => 'Dias_mora_Miercoles_14_30'],
            ['dia' => 'Miercoles', 'hhmm' => 1330, 'col' => 'Dias_mora_Miercoles_13_30'],
            ['dia' => 'Miercoles', 'hhmm' => 1130, 'col' => 'Dias_mora_Miercoles_11_30'],
            ['dia' => 'Miercoles', 'hhmm' =>  930, 'col' => 'Dias_mora_Miercoles_09_30'],
            ['dia' => 'Miercoles', 'hhmm' =>  730, 'col' => 'Dias_mora_Miercoles_07_30'],
            ['dia' => 'Martes',    'hhmm' => 2350, 'col' => 'Dias_mora_Martes_23_50'],
            ['dia' => 'Martes',    'hhmm' => 2030, 'col' => 'Dias_mora_Martes_20_30'],
            ['dia' => 'Martes',    'hhmm' => 1830, 'col' => 'Dias_mora_Martes_18_30'],
            ['dia' => 'Martes',    'hhmm' => 1630, 'col' => 'Dias_mora_Martes_16_30'],
            ['dia' => 'Martes',    'hhmm' => 1430, 'col' => 'Dias_mora_Martes_14_30'],
            ['dia' => 'Martes',    'hhmm' => 1330, 'col' => 'Dias_mora_Martes_13_30'],
            ['dia' => 'Martes',    'hhmm' => 1130, 'col' => 'Dias_mora_Martes_11_30'],
            ['dia' => 'Martes',    'hhmm' =>  930, 'col' => 'Dias_mora_Martes_09_30'],
            ['dia' => 'Martes',    'hhmm' =>  730, 'col' => 'Dias_mora_Martes_07_30'],
            ['dia' => 'Lunes',     'hhmm' => 2350, 'col' => 'Dias_mora_Lunes_23_50'],
            ['dia' => 'Lunes',     'hhmm' => 2030, 'col' => 'Dias_mora_Lunes_20_30'],
            ['dia' => 'Lunes',     'hhmm' => 1830, 'col' => 'Dias_mora_Lunes_18_30'],
            ['dia' => 'Lunes',     'hhmm' => 1630, 'col' => 'Dias_mora_Lunes_16_30'],
            ['dia' => 'Lunes',     'hhmm' => 1430, 'col' => 'Dias_mora_Lunes_14_30'],
            ['dia' => 'Lunes',     'hhmm' => 1330, 'col' => 'Dias_mora_Lunes_13_30'],
            ['dia' => 'Lunes',     'hhmm' => 1130, 'col' => 'Dias_mora_Lunes_11_30'],
            ['dia' => 'Lunes',     'hhmm' =>  930, 'col' => 'Dias_mora_Lunes_09_30'],
            ['dia' => 'Lunes',     'hhmm' =>  730, 'col' => 'Dias_mora_Lunes_07_30'],
        ];

        // Peso del momento actual
        $pesoActual = ($ordenDias[$hoy] * 10000) + $horaActual;

        $result = null;
        foreach ($cortes as $c) {
            $pesoCandidato = ($ordenDias[$c['dia']] * 10000) + $c['hhmm'];
            if ($pesoCandidato <= $pesoActual) {
                $result = $c['col'];
                break; // El primero que sea <= al momento actual es el más reciente
            }
        }

        // ── 3. Guardar caché ───────────────────────────────────────────────
        if (is_dir($cacheDir) || @mkdir($cacheDir, 0755, true)) {
            @file_put_contents($cacheFile, json_encode([
                'expires' => time() + self::CORTE_ACTUAL_CACHE_TTL,
                'col'     => $result,
            ], JSON_UNESCAPED_UNICODE));
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════
    //  Créditos con Fecha_primer_vencimiento = Lunes de cierre
    //  + comparativo bucket nacimiento vs corte (dinámico o solo Lunes)
    // ══════════════════════════════════════════════════════════════
    /**
     * Slots del lunes (de más tardío a más temprano): primer no nulo = mora del lunes ya cargada en tabla.
     */
    private static function sqlExprMoraSoloLunes(): string
    {
        $cols = [
            'Dias_mora_Lunes_23_50',
            'Dias_mora_Lunes_20_30',
            'Dias_mora_Lunes_18_30',
            'Dias_mora_Lunes_16_30',
            'Dias_mora_Lunes_14_30',
            'Dias_mora_Lunes_13_30',
            'Dias_mora_Lunes_11_30',
            'Dias_mora_Lunes_09_30',
            'Dias_mora_Lunes_07_30',
        ];
        $parts = [];
        foreach ($cols as $c) {
            $parts[] = '`' . $c . '`';
        }

        return 'COALESCE(' . implode(', ', $parts) . ')';
    }

    /** Bucket de corte a partir de una expresión SQL de días mora (misma escala que el reporte dinámico). */
    private static function sqlBucketDesdeMoraExpr(string $moraExpr): string
    {
        return sprintf(
            'CASE '
            . 'WHEN (%1$s) IS NULL THEN NULL '
            . 'WHEN (%1$s) < 1 THEN \'a) Current\' '
            . 'WHEN (%1$s) BETWEEN 1 AND 7 THEN \'b) 1 a 7 dias\' '
            . 'WHEN (%1$s) BETWEEN 8 AND 30 THEN \'c) 8 a 30 dias\' '
            . 'WHEN (%1$s) BETWEEN 31 AND 60 THEN \'d) 31 a 60 dias\' '
            . 'ELSE \'e) 61+ dias\' END',
            $moraExpr
        );
    }

    /**
     * @param int  $offsetSemanas  0 = lunes de la semana en curso; 1 = siguiente lunes de cierre
     * @param bool $corteSoloLunes true = mora solo con columnas del día lunes (sin martes+); false = corte actual
     *                                (cartera desde martes ~8:00, avanza según día/hora)
     */
    public static function getVencimientosLunes(int $offsetSemanas = 0, bool $corteSoloLunes = false): array
    {
        if (!$corteSoloLunes) {
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
        }

        // Lunes de cierre (misma lógica que en SQL: días desde el último lunes)
        $dow   = (int)date('w');
        $dias  = ($dow + 6) % 7;
        $lunes = date('Y-m-d', strtotime("-$dias days"));
        if ($offsetSemanas !== 0) {
            $lunes = date('Y-m-d', strtotime($lunes . ' +' . (int)$offsetSemanas . ' weeks'));
        }

        if ($corteSoloLunes) {
            $moraExpr       = self::sqlExprMoraSoloLunes();
            $bucketCorteSQL = self::sqlBucketDesdeMoraExpr($moraExpr);
            $corteLabel     = 'Dias_mora_Lunes';
            $sql            = 'SELECT '
                . 't.Id_credito, '
                . 't.Nombre_cliente, '
                . 't.Bucket_Morosidad_Real AS bucket_nacio, '
                . 't.Gestor_Asignado, '
                . 't.Jefe_de_Plaza, '
                . 't.Zonal, '
                . 't.Territorial, '
                . 't.Cuotas_vencidas, '
                . 't.Saldo_vencido_actualizado, '
                . 't.Fecha_primer_vencimiento, '
                . '(' . $moraExpr . ') AS dias_mora_corte, '
                . $bucketCorteSQL . ' AS bucket_corte_actual '
                . 'FROM tbl_segundometro_semana t '
                . 'WHERE DATE(t.Fecha_primer_vencimiento) = :lunes '
                . 'ORDER BY t.Territorial, t.Zonal, t.Jefe_de_Plaza, t.Gestor_Asignado, t.Nombre_cliente';
        } else {
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

            $corteLabel = $corteCol;
            $sql        = "
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
                ($bucketCorteSQL)                AS bucket_corte_actual
            FROM tbl_segundometro_semana t
            WHERE DATE(t.Fecha_primer_vencimiento) = :lunes
            ORDER BY
                t.Territorial,
                t.Zonal,
                t.Jefe_de_Plaza,
                t.Gestor_Asignado,
                t.Nombre_cliente
        ";
        }

        try {
            $db                = new DatabaseSegundometro();
            $lunesCalendario   = $lunes;
            $rows              = $db->queryAll($sql, ['lunes' => $lunes]);
            $usadoFallbackLunes = false;

            // Semana actual (solo lunes): si el lunes de calendario aún no tiene filas en segundómetro, usar el último lunes con datos.
            if ($corteSoloLunes && count($rows) === 0) {
                $fb = $db->queryOne(
                    'SELECT MAX(DATE(t.Fecha_primer_vencimiento)) AS lm
                     FROM tbl_segundometro_semana t
                     WHERE DATE(t.Fecha_primer_vencimiento) <= :lunes
                       AND WEEKDAY(t.Fecha_primer_vencimiento) = 0',
                    ['lunes' => $lunesCalendario]
                );
                if (!empty($fb['lm'])) {
                    $lunesEfectivo = $fb['lm'];
                    $rows          = $db->queryAll($sql, ['lunes' => $lunesEfectivo]);
                    $usadoFallbackLunes = ($lunesEfectivo !== $lunesCalendario);
                    $lunes              = $lunesEfectivo;
                }
            }

            return [
                'success'              => true,
                'mensaje'              => 'Registros obtenidos.',
                'lunes_pasado'         => $lunes,
                'lunes_calendario'     => $lunesCalendario,
                'usado_fallback_lunes' => $usadoFallbackLunes,
                'corte_actual'         => $corteLabel,
                'datos'                => $rows,
            ];

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

} // ← única llave de cierre de la clase
