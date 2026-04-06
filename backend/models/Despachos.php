<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseSegundometro;

class Despachos extends Model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Fecha/hora actual en zona Ciudad de México (para fecha_alta / fecha_baja; evita NOW() del servidor MySQL).
     */
    private function fechaHoraCdmx(): string
    {
        $dt = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));

        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Fila de asignación considerada cerrada: baja registrada y estatus inactivo (permite nueva INSERT si la BD lo admite).
     */
    private function asignacionParEstaCerrada(?array $row): bool
    {
        if ($row === null || $row === []) {
            return false;
        }
        $st = $row['estatus'] ?? '';
        if ($st !== '0' && $st !== 0) {
            return false;
        }
        $fb = $row['fecha_baja'] ?? null;
        if ($fb === null || $fb === '') {
            return false;
        }
        if (trim((string) $fb) === '0000-00-00 00:00:00') {
            return false;
        }

        return true;
    }

    /**
     * Aplica pares (id_despacho, id_credito, id_celula) con la misma lógica que el importador
     * fila a fila, pero con pocas consultas SQL (precarga + tabla temporal + INSERT/UPDATE por lotes).
     *
     * @param array<int, array{d:int,c:int,cel:int,fila:int}> $pares
     * @return array{insertados:int, actualizados:int, duplicadosDetalle:array<int,array{id_despacho:int,id_credito:int}>}
     */
    private function aplicarAsignacionesCreditosDespachoEnLote(array $pares, int $usuarioAsignacion): array
    {
        $fechaAlta = $this->fechaHoraCdmx();
        $duplicadosDetalle = [];
        $insertados = 0;
        $actualizados = 0;

        // --- 1) Asignación activa por id_credito (una consulta por chunk de créditos) ---
        $idsCred = [];
        foreach ($pares as $p) {
            $idsCred[(int) $p['c']] = true;
        }
        $idsCred = array_keys($idsCred);
        $activoByCredito = [];

        $chunkCred = 2000;
        for ($i = 0; $i < count($idsCred); $i += $chunkCred) {
            $chunk = array_slice($idsCred, $i, $chunkCred);
            if ($chunk === []) {
                continue;
            }
            $ph = [];
            $par = [];
            foreach ($chunk as $j => $id) {
                $k = 'ic' . $j;
                $ph[] = ':' . $k;
                $par[$k] = (int) $id;
            }
            $sqlAct = 'SELECT id, id_credito, id_despacho, fecha_alta FROM asigna_creditos_despacho'
                . " WHERE estatus = '1' AND id_credito IN (" . implode(',', $ph) . ')'
                . ' ORDER BY id_credito ASC, fecha_alta DESC, id DESC';
            $rowsAct = $this->db->queryAll($sqlAct, $par);
            foreach ($rowsAct as $r) {
                $c = (int) $r['id_credito'];
                if (!isset($activoByCredito[$c])) {
                    $activoByCredito[$c] = [
                        'id' => (int) $r['id'],
                        'id_despacho' => (int) $r['id_despacho'],
                    ];
                }
            }
        }

        // --- 2) Tabla temporal con los pares del Excel (clave id_despacho + id_credito) ---
        $this->db->CRUD('DROP TEMPORARY TABLE IF EXISTS _tmp_acd_imp');
        try {
        $this->db->CRUD(
            'CREATE TEMPORARY TABLE _tmp_acd_imp (
                id_despacho INT NOT NULL,
                id_credito INT NOT NULL,
                id_celula SMALLINT NOT NULL DEFAULT 1,
                PRIMARY KEY (id_despacho, id_credito)
            ) ENGINE=InnoDB'
        );

        foreach (array_chunk($pares, 500) as $ci => $chunk) {
            $vals = [];
            $parIns = [];
            foreach ($chunk as $j => $p) {
                $vals[] = '(:d' . $ci . '_' . $j . ',:c' . $ci . '_' . $j . ',:cel' . $ci . '_' . $j . ')';
                $parIns['d' . $ci . '_' . $j] = (int) $p['d'];
                $parIns['c' . $ci . '_' . $j] = (int) $p['c'];
                $parIns['cel' . $ci . '_' . $j] = (int) $p['cel'];
            }
            $this->db->CRUD(
                'INSERT INTO _tmp_acd_imp (id_despacho, id_credito, id_celula) VALUES ' . implode(',', $vals),
                $parIns
            );
        }

        // --- 3) Última fila por (id_despacho, id_credito) — mismo criterio que ORDER BY fecha_alta DESC, id DESC LIMIT 1 ---
        $sqlUlt = <<<'SQL'
SELECT acd.id, acd.id_despacho, acd.id_credito, acd.fecha_baja, acd.estatus
FROM asigna_creditos_despacho acd
INNER JOIN _tmp_acd_imp t ON acd.id_despacho = t.id_despacho AND acd.id_credito = t.id_credito
WHERE NOT EXISTS (
    SELECT 1 FROM asigna_creditos_despacho s
    WHERE s.id_despacho = acd.id_despacho AND s.id_credito = acd.id_credito
      AND (s.fecha_alta > acd.fecha_alta OR (s.fecha_alta = acd.fecha_alta AND s.id > acd.id))
)
SQL;
        $ultRows = $this->db->queryAll($sqlUlt);
        $ultParByKey = [];
        foreach ($ultRows as $ur) {
            $k = (int) $ur['id_despacho'] . ':' . (int) $ur['id_credito'];
            $ultParByKey[$k] = $ur;
        }

        // --- 4) Clasificar en memoria ---
        $updates = [];
        $inserts = [];
        $insertsCerrada = [];

        foreach ($pares as $p) {
            $c = (int) $p['c'];
            $d = (int) $p['d'];
            $cel = (int) $p['cel'];

            $activo = $activoByCredito[$c] ?? null;
            if ($activo !== null) {
                $dAct = (int) $activo['id_despacho'];
                if ($dAct === $d) {
                    $duplicadosDetalle[] = ['id_despacho' => $d, 'id_credito' => $c];
                    continue;
                }
                $updates[] = ['id' => (int) $activo['id'], 'd' => $d, 'cel' => $cel];
                continue;
            }

            $key = $d . ':' . $c;
            $ultPar = $ultParByKey[$key] ?? null;

            if ($ultPar === null) {
                $inserts[] = ['d' => $d, 'c' => $c, 'cel' => $cel];
                continue;
            }

            if ($this->asignacionParEstaCerrada($ultPar)) {
                $insertsCerrada[] = ['d' => $d, 'c' => $c, 'cel' => $cel, 'ultId' => (int) $ultPar['id']];
                continue;
            }

            $updates[] = ['id' => (int) $ultPar['id'], 'd' => $d, 'cel' => $cel];
        }

        // --- 5) UPDATE por lotes (CASE id WHEN … THEN …) ---
        foreach (array_chunk($updates, 200) as $chunk) {
            $caseD = [];
            $caseCel = [];
            $ids = [];
            foreach ($chunk as $u) {
                $id = (int) $u['id'];
                $ids[] = $id;
                $caseD[] = 'WHEN ' . $id . ' THEN ' . (int) $u['d'];
                $caseCel[] = 'WHEN ' . $id . ' THEN ' . (int) $u['cel'];
            }
            $idsIn = implode(',', $ids);
            $caseDStr = implode("\n    ", $caseD);
            $caseCelStr = implode("\n    ", $caseCel);
            $sqlUp = 'UPDATE asigna_creditos_despacho SET '
                . 'id_despacho = CASE id ' . "\n    " . $caseDStr . "\n    END, "
                . 'fecha_alta = :fechaAlta, alta = :alta, estatus = \'1\', fecha_baja = NULL, baja = NULL, '
                . 'celula = CASE id ' . "\n    " . $caseCelStr . "\n    END, "
                . 'id_celula = CASE id ' . "\n    " . $caseCelStr . "\n    END "
                . 'WHERE id IN (' . $idsIn . ')';
            $n = $this->db->CRUD($sqlUp, ['fechaAlta' => $fechaAlta, 'alta' => $usuarioAsignacion]);
            $actualizados += $n > 0 ? $n : 0;
        }

        // --- 6) INSERT por lotes (parámetros únicos por fila; evita duplicar :nombre en PDO) ---
        $insertChunk = function (array $rows) use (&$insertados, $fechaAlta, $usuarioAsignacion) {
            foreach (array_chunk($rows, 250) as $chunk) {
                $vals = [];
                $par = [];
                foreach ($chunk as $i => $row) {
                    $vals[] = '(:d' . $i . ',:c' . $i . ',:fa' . $i . ',:alta' . $i . ", '1', :celA" . $i . ',:celB' . $i . ')';
                    $par['d' . $i] = $row['d'];
                    $par['c' . $i] = $row['c'];
                    $par['fa' . $i] = $fechaAlta;
                    $par['alta' . $i] = $usuarioAsignacion;
                    $par['celA' . $i] = $row['cel'];
                    $par['celB' . $i] = $row['cel'];
                }
                $sqlIns = 'INSERT INTO asigna_creditos_despacho '
                    . '(id_despacho, id_credito, fecha_alta, alta, estatus, celula, id_celula) VALUES '
                    . implode(',', $vals);
                $insertados += $this->db->CRUD($sqlIns, $par);
            }
        };

        $insertChunk($inserts);

        $sqlUpdateFila = <<<'SQL'
UPDATE asigna_creditos_despacho
SET id_despacho = :idDespacho,
    fecha_alta = :fechaAlta,
    alta = :usuarioAsignacion,
    estatus = '1',
    fecha_baja = NULL,
    baja = NULL,
    celula = :celulaVal,
    id_celula = :idCelulaVal
WHERE id = :id
SQL;

        foreach (array_chunk($insertsCerrada, 200) as $chunk) {
            try {
                $rowsSimple = [];
                foreach ($chunk as $ic) {
                    $rowsSimple[] = ['d' => $ic['d'], 'c' => $ic['c'], 'cel' => $ic['cel']];
                }
                $insertChunk($rowsSimple);
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $esDup = (strpos($msg, '1062') !== false
                    || stripos($msg, 'Duplicate') !== false
                    || stripos($msg, 'UNIQUE') !== false);
                if (!$esDup) {
                    throw $e;
                }
                foreach ($chunk as $ic) {
                    try {
                        $n = $this->db->CRUD(
                            'INSERT INTO asigna_creditos_despacho (id_despacho, id_credito, fecha_alta, alta, estatus, celula, id_celula) VALUES (:idDespacho, :idCredito, :fechaAlta, :usuarioAsignacion, \'1\', :idCelula, :idCelula2)',
                            [
                                'idDespacho' => $ic['d'],
                                'idCredito' => $ic['c'],
                                'fechaAlta' => $fechaAlta,
                                'usuarioAsignacion' => $usuarioAsignacion,
                                'idCelula' => $ic['cel'],
                                'idCelula2' => $ic['cel'],
                            ]
                        );
                        if ($n > 0) {
                            $insertados++;
                        }
                    } catch (\Exception $e2) {
                        $msg2 = $e2->getMessage();
                        $esDup2 = (strpos($msg2, '1062') !== false
                            || stripos($msg2, 'Duplicate') !== false
                            || stripos($msg2, 'UNIQUE') !== false);
                        if (!$esDup2) {
                            throw $e2;
                        }
                        $ok = $this->db->CRUD($sqlUpdateFila, [
                            'idDespacho' => $ic['d'],
                            'fechaAlta' => $fechaAlta,
                            'usuarioAsignacion' => $usuarioAsignacion,
                            'celulaVal' => $ic['cel'],
                            'idCelulaVal' => $ic['cel'],
                            'id' => $ic['ultId'],
                        ]) > 0;
                        if ($ok) {
                            $actualizados++;
                        }
                    }
                }
            }
        }
        } finally {
            try {
                $this->db->CRUD('DROP TEMPORARY TABLE IF EXISTS _tmp_acd_imp');
            } catch (\Throwable $e) {
                // conexión o tabla ya inexistente
            }
        }

        return [
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'duplicadosDetalle' => $duplicadosDetalle,
        ];
    }

    /**
     * Obtener dirección completa desde tbl_segundometro_semana
     * Esta función consulta la base de datos db-megae-reporte
     */
    private function obtenerDatosSegundometro($idCredito): ?array
    {
        try {
            $dbSegundo = new DatabaseSegundometro();
            $query = <<<SQL
            SELECT
                Domicilio_Completo,
                Bucket_Morosidad_Real,
                Id_cliente,
                Nombre_cliente
            FROM tbl_segundometro_semana
            WHERE Id_credito = :idCredito
            LIMIT 1
SQL;

            $resultado = $dbSegundo->queryOne($query, ['idCredito' => $idCredito]);
            return $resultado ?: null;
        } catch (\Exception $e) {
            error_log("Error al obtener datos de segundometro: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener lista de despachos (Gestores y Supervisores)
     * IDs de puesto: 24 = Gestor, 36 = Supervisor
     * Un despacho = Una persona con cualquiera de estos 2 puestos
     */
    public function obtenerDespachos($id_celula = 1)
{
    // Quitamos los comentarios dentro del String SQL para evitar confusiones al parser
    $query = <<<SQL
    SELECT
        d.id,
        d.id_persona,
        CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
        pu.id AS id_puesto,
        pu.nombre AS nombre_puesto,
        d.tipo_persona,
        d.numero_tel1,
        d.numero_tel2,
        d.correo_1,
        d.correo_2,
        d.direccion,
        d.fecha_alta,
        d.estatus,
        d.id_celula
    FROM despachos d
    INNER JOIN persona per ON per.id = d.id_persona
    LEFT JOIN asigna_puesto ap ON ap.id_persona = d.id_persona AND ap.activo = 1
    LEFT JOIN puesto pu ON pu.id = ap.id_puesto
    WHERE d.id_celula = :id_celula
    ORDER BY d.id
    SQL;

    // Usamos el nombre del parámetro :id_celula explícitamente
    return $this->db->queryAll($query, ['id_celula' => $id_celula]);
}
    /**
     * Catálogo para importación Excel: una fila por registro en tabla despachos (activos), con nombre de la persona titular.
     * No usa asigna_puesto: evita listar cientos de gestores/supervisores que no tienen fila en despachos.
     */
    public function obtenerCatalogoDespachosParaImportacionExcel()
    {
        $query = <<<SQL
        SELECT
            d.id AS id_despacho,
            d.id_persona,
            TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre_completo
        FROM despachos d
        LEFT JOIN persona per ON per.id = d.id_persona
        WHERE d.estatus = 'Activo'
        ORDER BY d.id
SQL;

        return $this->db->queryAll($query);
    }

    /**
     * Obtener datos de un despacho específico usando la tabla persona directamente
     * Si existe en despachos, traemos esos datos adicionales, sino solo de persona
     */
    public function obtenerDatosDespacho($idPersona)
    {
        $query = <<<SQL
        SELECT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.nombre AS puesto,
            per.telefono_uno AS telefono,
            per.correo AS correo,
            COALESCE(d.direccion, '') AS direccion,
            COALESCE(d.tipo_persona, '') AS tipo_persona,
            d.id AS id_despacho
        FROM persona per
        INNER JOIN asigna_puesto ap ON per.id = ap.id_persona
        INNER JOIN puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN despachos d ON per.id = d.id_persona AND d.estatus = 'Activo'
        WHERE per.id = :idPersona
          AND ap.id_puesto IN (24, 36)
          AND ap.activo = 1
        LIMIT 1
SQL;

        $result = $this->db->queryOne($query, ['idPersona' => $idPersona]);

        if (!$result) {
            return [
                'datos' => [
                    'nombre_completo' => '',
                    'puesto' => '',
                    'telefono' => '',
                    'correo' => '',
                    'direccion' => '',
                    'tipo_persona' => ''
                ],
                'comentarios' => '',
                'id_despacho' => null
            ];
        }

        // Obtener el último comentario (si existe y tiene id_despacho)
        $comentario = '';
        if (!empty($result['id_despacho'])) {
            $queryComentario = <<<SQL
            SELECT comentario
            FROM comentarios_despacho
            WHERE id_despacho = :idDespacho
            ORDER BY fecha_comentario DESC
            LIMIT 1
SQL;
            $comentarioResult = $this->db->queryOne($queryComentario, ['idDespacho' => $result['id_despacho']]);
            $comentario = $comentarioResult['comentario'] ?? '';
        }

        return [
            'datos' => $result,
            'comentarios' => $comentario,
            'id_despacho' => $result['id_despacho'] ?? null
        ];
    }

    /**
     * Importación Excel / popover: nombre desde persona y id_despacho desde despachos (JOIN por id_persona).
     * Mismos criterios de despacho que el listado (puestos 24 y 36, asigna_puesto activo).
     */
    public function obtenerDespachoImportacionPorIdPersona($idPersona)
    {
        $id = (int) $idPersona;
        if ($id <= 0) {
            return null;
        }

        $query = <<<SQL
        SELECT
            per.id AS id_persona,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            d.id AS id_despacho
        FROM persona per
        INNER JOIN asigna_puesto ap ON ap.id_persona = per.id
            AND ap.activo = 1
            AND ap.id_puesto IN (24, 36)
        LEFT JOIN despachos d ON d.id_persona = per.id AND d.estatus = 'Activo'
        WHERE per.id = :idPersona
        LIMIT 1
SQL;

        return $this->db->queryOne($query, ['idPersona' => $id]);
    }

    /**
     * Obtener comentarios de un despacho
     */
    public function obtenerComentarios($idDespacho)
    {
        $query = <<<SQL
        SELECT
            c.id,
            c.comentario,
            c.fecha_comentario,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_persona
        FROM comentarios_despacho c
        LEFT JOIN persona per ON c.id_persona_comenta = per.id
        WHERE c.id_despacho = ?
        ORDER BY c.fecha_comentario DESC
SQL;

        return $this->db->queryAll($query, [$idDespacho]);
    }

    /**
     * Obtener métricas de un despacho
     */
    public function obtenerMetricasDespacho($idPersona)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);

        if (!$despacho) {
            return [
                'creditos_asignados' => 0,
                'saldo_total' => 0,
                'recuperacion' => 0,
                'promedio_mora' => 0
            ];
        }

        $idDespacho = $despacho['id'];

        // Créditos asignados activos
        $queryCreditos = <<<SQL
        SELECT COUNT(*) as total
        FROM asigna_creditos_despacho
        WHERE id_despacho = :idDespacho AND estatus = 'Activo'
SQL;
        $creditos = $this->db->queryOne($queryCreditos, ['idDespacho' => $idDespacho]);

        // Por ahora, saldo, recuperación y mora se calculan cuando tengas acceso a la API de créditos
        // o cuando definas de dónde obtienes esta información

        return [
            'creditos_asignados' => $creditos['total'] ?? 0,
            'saldo_total' => 0,
            'recuperacion' => 0,
            'promedio_mora' => 0
        ];
    }

    /**
     * Buscar crédito por ID usando la API externa (como en EstadoCuenta)
     */
    public function buscarCredito($tipo, $valor)
    {
        // Usamos la API externa para obtener información del crédito
        // Similar a como se hace en EstadoCuenta
        $url = "https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta";

        $payload = json_encode([
            "idCredito" => intval($valor),
            "fechaCorte" => date('Y-m-d')
        ]);

        $headers = [
            "Token: __SPARTA_TOKEN_REDACTED__",
            "Content-Type: application/json"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return null;
        }

        $json = json_decode($response, true);

        if (!isset($json["estadoCuenta"])) {
            return null;
        }

        $estadoCuenta = $json["estadoCuenta"];

        // Si el crédito no existe en la API, idCredito viene null o vacío
        if (empty($estadoCuenta["idCredito"])) {
            return null;
        }

        $cliente = $estadoCuenta["datosCliente"] ?? [];

        // Construir dirección completa desde los campos del cliente
        $direccionParts = array_filter([
            $cliente["calle"] ?? '',
            $cliente["numeroExterior"] ?? '',
            $cliente["numeroInterior"] ?? '',
            $cliente["colonia"] ?? '',
            $cliente["municipio"] ?? '',
            $cliente["estado"] ?? '',
            $cliente["codigoPostal"] ?? ''
        ]);
        $direccionAPI = !empty($direccionParts) ? implode(', ', $direccionParts) : null;

        // Obtener datos de tbl_segundometro_semana (domicilio + bucket)
        $datosSegundo = $this->obtenerDatosSegundometro($valor);
        $domicilioCompleto   = $datosSegundo['Domicilio_Completo']    ?? null;
        $bucketMorosidad     = $datosSegundo['Bucket_Morosidad_Real'] ?? null;

        // Usar domicilio completo si existe, sino la de la API, sino mensaje por defecto
        $direccion = $domicilioCompleto ?: ($direccionAPI ?: 'Sin dirección registrada');

        return [
            'id_credito'            => $estadoCuenta["idCredito"],
            'nombre_cliente'        => $cliente["nombreCliente"] ?? 'Sin nombre',
            'saldo_actual'          => $estadoCuenta["datosSaldos"]["saldoTotalVencido"] ?? 0,
            'dias_mora'             => $estadoCuenta["datosSaldos"]["diasMoraMaximo"] ?? 0,
            'Bucket_Morosidad_Real' => $bucketMorosidad,
            'telefono'              => $cliente["celular"] ?? 'Sin teléfono',
            'curp'                  => $cliente["curp"] ?? 'Sin CURP',
            'direccion'             => $direccion,
            'direccion_api'         => $direccionAPI ?: 'No disponible en API',
            'direccion_megareporte' => $domicilioCompleto ?: 'No disponible en Megareporte',
            'sucursal'              => $cliente["sucursal"] ?? 'Sin sucursal',
            'fecha_desembolso'      => $estadoCuenta["fechaDesembolso"] ?? 'Sin fecha'
        ];
    }

    /**
     * Obtener información de asignación de un crédito
     * Devuelve datos del despacho si está asignado, o null si no
     */
    public function obtenerAsignacionCredito($idCredito)
    {
        $query = <<<SQL
        SELECT
            acd.id_credito,
            acd.estatus,
            DATE_FORMAT(acd.fecha_alta, '%Y-%m-%d %H:%i') as fecha_asignacion,
            DATE_FORMAT(acd.fecha_baja, '%Y-%m-%d %H:%i') as fecha_baja,
            d.id_persona,
            CONCAT_WS(' ', per.nombres, per.apellidop) as nombre_despacho,
            GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ') as puesto_despacho,
            per.telefono_uno as telefono_despacho,
            per.correo as correo_despacho,
            CONCAT_WS(' ', per_asigno.nombres, per_asigno.apellidop) as asignado_por
        FROM asigna_creditos_despacho acd
        INNER JOIN despachos d ON acd.id_despacho = d.id
        INNER JOIN persona per ON d.id_persona = per.id
        LEFT JOIN asigna_puesto ap ON per.id = ap.id_persona
        LEFT JOIN puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN persona per_asigno ON acd.alta = per_asigno.id
        WHERE acd.id_credito = :idCredito
        GROUP BY acd.id, acd.id_credito, acd.estatus, acd.fecha_alta, acd.fecha_baja,
                 d.id_persona, per.nombres, per.apellidop, per.telefono_uno, per.correo,
                 per_asigno.nombres, per_asigno.apellidop
        ORDER BY acd.fecha_alta DESC
        LIMIT 1
SQL;

        return $this->db->queryOne($query, ['idCredito' => $idCredito]);
    }

    /**
     * Verificar si un crédito ya está asignado a un despacho (activo)
     */
    public function verificarAsignacion($idCredito)
{
    $query = <<<SQL
    SELECT COUNT(*) AS total
    FROM asigna_creditos_despacho
    WHERE id_credito = :idCredito
      AND estatus    = '1'
      AND baja       IS NULL
    SQL;

    $result = $this->db->queryOne($query, ['idCredito' => $idCredito]);
    return ($result['total'] ?? 0) > 0;
}

    /**
     * Asignar crédito a un despacho
     */
    /**
 * Asignar crédito a un despacho
 * @param int $idPersona ID de la persona (despacho)
 * @param int $idCredito ID del crédito
 * @param int $idCelula ID de la célula (1=Despacho, 2=Gestión Call Center)
 * @return bool
 */

public function asignarCredito($idPersona, $idCredito, $idCelula = 1)
{
    // Primero obtener el id del despacho
    $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
    $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);

    if (!$despacho) {
        // Si no existe registro en despachos, crearlo automáticamente
        $queryInsert = "INSERT INTO despachos (id_persona, estatus, fecha_alta) VALUES (:idPersona, 'Activo', NOW())";
        $insertado = $this->db->CRUD($queryInsert, ['idPersona' => $idPersona]);
        if (!$insertado) {
            return false;
        }
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        if (!$despacho) {
            return false;
        }
    }

    // Misma pareja despacho+crédito (última fila): si está cerrada (fecha_baja), preferir INSERT nuevo
    $queryVerificar = <<<'SQL'
SELECT id, fecha_baja, estatus
FROM asigna_creditos_despacho
WHERE id_despacho = :idDespacho AND id_credito = :idCredito
ORDER BY fecha_alta DESC
LIMIT 1
SQL;
    $existente = $this->db->queryOne($queryVerificar, [
        'idDespacho' => $despacho['id'],
        'idCredito' => $idCredito
    ]);

    $usuarioAsignacion = $_SESSION['usuario_id'] ?? 1;

    $fechaAlta = $this->fechaHoraCdmx();

    $queryUpdate = <<<'SQL'
        UPDATE asigna_creditos_despacho
        SET estatus = '1',
            fecha_baja = NULL,
            fecha_alta = :fechaAlta,
            alta = :usuarioAsignacion,
            celula = :celulaVal,
            id_celula = :idCelulaVal
        WHERE id = :id
SQL;

    $queryInsert = <<<'SQL'
        INSERT INTO asigna_creditos_despacho
        (id_despacho, id_credito, fecha_alta, alta, estatus, celula, id_celula)
        VALUES (:idDespacho, :idCredito, :fechaAlta, :usuarioAsignacion, '1', :celulaVal, :idCelulaVal)
SQL;

    $paramsInsert = [
        'idDespacho' => $despacho['id'],
        'idCredito' => $idCredito,
        'fechaAlta' => $fechaAlta,
        'usuarioAsignacion' => $usuarioAsignacion,
        'celulaVal' => $idCelula,
        'idCelulaVal' => $idCelula
    ];

    if ($existente) {
        if ($this->asignacionParEstaCerrada($existente)) {
            try {
                return $this->db->CRUD($queryInsert, $paramsInsert) > 0;
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                $esDup = (strpos($msg, '1062') !== false
                    || stripos($msg, 'Duplicate') !== false
                    || stripos($msg, 'UNIQUE') !== false);
                if (!$esDup) {
                    throw $e;
                }
            }
        }
        return $this->db->CRUD($queryUpdate, [
            'id' => $existente['id'],
            'fechaAlta' => $fechaAlta,
            'usuarioAsignacion' => $usuarioAsignacion,
            'celulaVal' => $idCelula,
            'idCelulaVal' => $idCelula
        ]) > 0;
    }

    return $this->db->CRUD($queryInsert, $paramsInsert) > 0;
}

    /**
     * Importar Excel para asignar créditos masivamente.
     *
     * Modo A — columnas en fila 1: id_credito + id_despacho (por fila puede ir a distintos despachos).
     * Modo B — solo id_credito: requiere id_persona del despacho seleccionado en pantalla.
     *
     * Reglas por fila: si el crédito ya está activo en el mismo id_despacho que el Excel → duplicado (omitir).
     * Si está activo en otro id_despacho → actualizar a id_despacho del Excel. Sin activo: última fila (d,c) con fecha_baja y estatus 0 → intentar INSERT; si no cumple → UPDATE de esa fila; sin fila (d,c) → INSERT. Duplicado en BD → reabrir fila.
     * fecha_alta / fechas de baja en hora Ciudad de México (no NOW() del servidor MySQL).
     *
     * Valida números enteros > 0 y que id_despacho exista en despachos (estatus Activo).
     */

    public function importarAsignaCreditosDesdeExcel($idPersona, $excelPath)
    {
    if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
        require_once dirname(__DIR__) . '/bootstrap_composer.php';
        sparta_require_composer_autoload();
    }

    $errores = [];
    $usuarioAsignacion = $_SESSION['usuario_id'] ?? 1;

    $normalizeHeader = function ($v) {
        if ($v instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            $s = $v->getPlainText();
        } else {
            $s = trim((string) $v);
        }
        if ($s === '') return '';
        $s = preg_replace('/^\xEF\xBB\xBF/', '', $s);
        $s = str_replace(["\xC2\xA0", "\xE2\x80\xAF", "\xE2\x80\x89", "\xE2\x80\x87"], ' ', $s);
        $s = trim($s);
        if ($s === '') return '';
        $s = mb_strtolower($s, 'UTF-8');
        $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        $s = preg_replace('/[^a-z0-9]+/i', '_', $s);
        return trim((string) $s, '_');
    };

    $headerSlugAlnum = function ($norm) {
        return $norm === '' ? '' : preg_replace('/[^a-z0-9]/', '', $norm);
    };

    // =====================================================================
    // PASO 1: Cargar solo las primeras 60 filas de todas las hojas
    //         para detectar encabezados sin agotar la memoria.
    // =====================================================================
    try {
        $readerH = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($excelPath);
        $readerH->setReadDataOnly(true);
        $readerH->setReadEmptyCells(false);
        $readerH->setReadFilter(new class implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter {
            public function readCell($columnAddress, $row, $worksheetName = ''): bool {
                return $row <= 60;
            }
        });
        $spreadsheetH = $readerH->load($excelPath);
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'message' => 'Error al leer el Excel: ' . $e->getMessage(),
            'errores' => []
        ];
    }

    // =====================================================================
    // PASO 2: Escanear hojas buscando la que tenga id_credito + id_despacho.
    //         Si ninguna hoja tiene ambas, se usa la que solo tenga id_credito.
    // =====================================================================
    $bestCand = null; // ['title', 'row', 'cred', 'desp', 'cel', 'headers', 'hasBoth']

    $activeWs = $spreadsheetH->getActiveSheet();
    $sheetsToTry = [$activeWs];
    foreach ($spreadsheetH->getAllSheets() as $ws) {
        if ($ws !== $activeWs) $sheetsToTry[] = $ws;
    }

    foreach ($sheetsToTry as $trySheet) {
        $sheetMaxRow = min((int) $trySheet->getHighestRow(), 60);
        $maxColSheet  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
            (string) $trySheet->getHighestColumn()
        );

        for ($r = 1; $r <= $sheetMaxRow; $r++) {
            $lastIdxRow = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString(
                (string) $trySheet->getHighestColumn($r)
            );
            $scanHasta = max($lastIdxRow, $maxColSheet);
            if ($scanHasta < 1) continue;

            $hitCred = null;
            $hitDesp = null;
            $hitCel  = null;
            $hdrFound = [];

            for ($col = 1; $col <= $scanHasta; $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $cell = $trySheet->getCell($colLetter . $r);
                $v = $cell->getValue();
                if ($v === null || $v === '') $v = $cell->getCalculatedValue();
                $norm = $normalizeHeader($v);
                if ($norm === '') continue;
                $slug = $headerSlugAlnum($norm);
                $hdrFound[] = $norm;

                if ($norm === 'id_credito' || $slug === 'idcredito') $hitCred = $col;
                // Solo coincide con id_despacho/iddespacho estricto (no alias de nombre de despacho)
                if ($norm === 'id_despacho' || $slug === 'iddespacho') $hitDesp = $col;
                if ($norm === 'id_celula'   || $slug === 'idcelula')   $hitCel  = $col;
            }

            if ($hitCred !== null && $hitDesp !== null) {
                // Mejor caso: hoja con ambas columnas → usar de inmediato
                $bestCand = [
                    'title'   => $trySheet->getTitle(),
                    'row'     => $r,
                    'cred'    => $hitCred,
                    'desp'    => $hitDesp,
                    'cel'     => $hitCel,
                    'headers' => $hdrFound,
                    'hasBoth' => true,
                ];
                break 2;
            }

            if ($hitCred !== null && $bestCand === null) {
                // Fallback: solo id_credito; guardar y seguir buscando otra hoja mejor
                $bestCand = [
                    'title'   => $trySheet->getTitle(),
                    'row'     => $r,
                    'cred'    => $hitCred,
                    'desp'    => null,
                    'cel'     => $hitCel,
                    'headers' => $hdrFound,
                    'hasBoth' => false,
                ];
                break; // Saltar al siguiente sheet
            }
        }
    }

    unset($spreadsheetH); // Liberar memoria del escaneo de cabeceras

    if ($bestCand === null) {
        return [
            'success' => false,
            'message' => "Encabezado incorrecto: ninguna hoja tiene la columna 'id_credito' en las primeras 60 filas.",
            'errores' => []
        ];
    }

    $chosenSheetTitle  = $bestCand['title'];
    $headerRow         = $bestCand['row'];
    $colIndexIdCredito = $bestCand['cred'];
    $colIndexIdDespacho = $bestCand['desp'];
    $colIndexIdCelula  = $bestCand['cel'];
    $headersEncuentrados = $bestCand['headers'];

    // =====================================================================
    // PASO 3: Validar modo (por fila vs despacho seleccionado)
    // =====================================================================
    $modoPorFila = $colIndexIdDespacho !== null;
    $idDespachoFijo = null;

    if (!$modoPorFila) {
        $idPersonaInt = (int) $idPersona;
        if ($idPersonaInt <= 0) {
            $encabezadosInfo = !empty($headersEncuentrados)
                ? ' Encabezados encontrados en fila ' . $headerRow . ' de hoja "' . $chosenSheetTitle
                  . '": ' . implode(', ', array_unique($headersEncuentrados)) . '.'
                : '';
            return [
                'success' => false,
                'message' => 'No se encontró la columna id_despacho en el Excel, y tampoco hay despacho seleccionado en pantalla.'
                    . $encabezadosInfo
                    . ' Asegúrate de que la columna se llame exactamente "id_despacho".'
            ];
        }

        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersonaInt]);

        if (!$despacho) {
            $queryInsert = "INSERT INTO despachos (id_persona, estatus, fecha_alta) VALUES (:idPersona, 'Activo', NOW())";
            $insertado = $this->db->CRUD($queryInsert, ['idPersona' => $idPersonaInt]);
            if (!$insertado) {
                return ['success' => false, 'message' => 'No se pudo crear/obtener el despacho activo para el id_persona seleccionado.'];
            }
            $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersonaInt]);
        }

        if (!$despacho || empty($despacho['id'])) {
            return ['success' => false, 'message' => 'No se encontró el despacho activo y no se pudo crear.'];
        }

        $idDespachoFijo = (int) $despacho['id'];
    }

    $colLetterIdCredito  = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndexIdCredito);
    $colLetterIdDespacho = $modoPorFila
        ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndexIdDespacho)
        : null;
    $colLetterIdCelula   = $colIndexIdCelula !== null
        ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndexIdCelula)
        : null;

    // =====================================================================
    // PASO 4: Recargar el archivo cargando SOLO las columnas necesarias
    //         y solo la hoja elegida → drástica reducción de memoria/tiempo.
    // =====================================================================
    $neededCols = array_values(array_filter([$colLetterIdCredito, $colLetterIdDespacho, $colLetterIdCelula]));
    $sheetTitleForFilter = $chosenSheetTitle;
    $headerRowForFilter  = $headerRow;

    try {
        $readerD = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($excelPath);
        $readerD->setReadDataOnly(true);
        $readerD->setReadEmptyCells(false);
        $readerD->setReadFilter(
            new class($sheetTitleForFilter, $neededCols, $headerRowForFilter)
                implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
            {
                /** @var string */  private $title;
                /** @var array */   private $cols;
                /** @var int */     private $headerRow;

                public function __construct(string $title, array $cols, int $headerRow)
                {
                    $this->title     = $title;
                    $this->cols      = $cols;
                    $this->headerRow = $headerRow;
                }

                public function readCell($columnAddress, $row, $worksheetName = ''): bool
                {
                    if ($worksheetName !== $this->title) return false;
                    if ($row <= $this->headerRow) return true; // siempre carga filas de cabecera
                    return in_array($columnAddress, $this->cols, true);
                }
            }
        );
        $spreadsheetD = $readerD->load($excelPath);
        $sheet = $spreadsheetD->getSheetByName($chosenSheetTitle);
        if (!$sheet) {
            return ['success' => false, 'message' => 'No se pudo abrir la hoja "' . $chosenSheetTitle . '" para leer datos.', 'errores' => []];
        }
    } catch (\Throwable $e) {
        return [
            'success' => false,
            'message' => 'Error al cargar datos del Excel: ' . $e->getMessage(),
            'errores' => []
        ];
    }

    $highestRow = (int) $sheet->getHighestRow();

    $pares = []; // [ 'd' => int, 'c' => int, 'cel' => int, 'fila' => int ]
    $vistoPar = [];

    for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
        $rawCred = $sheet->getCell($colLetterIdCredito . $row)->getValue();
        if ($rawCred === null) {
            continue;
        }
        $rawCredStr = trim((string) $rawCred);
        if ($rawCredStr === '') {
            continue;
        }

        if (!is_numeric($rawCredStr)) {
            $errores[] = [
                'fila' => $row,
                'reason' => 'id_credito no es numérico',
                'valor' => $rawCredStr
            ];
            continue;
        }

        $idCredito = (int) round((float) $rawCredStr);
        if ($idCredito <= 0) {
            $errores[] = [
                'fila' => $row,
                'reason' => 'id_credito debe ser un entero > 0',
                'valor' => $rawCredStr
            ];
            continue;
        }

        // ✅ Obtener id_celula si existe la columna
        $idCelula = 1; // Default: Despacho
        if ($colLetterIdCelula !== null) {
            $rawCel = $sheet->getCell($colLetterIdCelula . $row)->getValue();
            if ($rawCel !== null) {
                $rawCelStr = trim((string) $rawCel);
                if ($rawCelStr !== '' && is_numeric($rawCelStr)) {
                    $tempCel = (int) round((float) $rawCelStr);
                    // Validar que sea 1 o 2
                    if (in_array($tempCel, [1, 2])) {
                        $idCelula = $tempCel;
                    } else {
                        $errores[] = [
                            'fila' => $row,
                            'reason' => 'id_celula debe ser 1 (Despacho) o 2 (Gestión Call Center)',
                            'valor' => $rawCelStr
                        ];
                        continue;
                    }
                }
            }
        }

        if ($modoPorFila) {
            $rawDesp = $sheet->getCell($colLetterIdDespacho . $row)->getValue();
            $rawDespStr = trim((string) ($rawDesp ?? ''));
            if ($rawDespStr === '') {
                $errores[] = [
                    'fila' => $row,
                    'reason' => 'id_despacho vacío',
                    'valor' => ''
                ];
                continue;
            }
            if (!is_numeric($rawDespStr)) {
                $errores[] = [
                    'fila' => $row,
                    'reason' => 'id_despacho no es numérico',
                    'valor' => $rawDespStr
                ];
                continue;
            }
            $idDespacho = (int) round((float) $rawDespStr);
            if ($idDespacho <= 0) {
                $errores[] = [
                    'fila' => $row,
                    'reason' => 'id_despacho debe ser un entero > 0',
                    'valor' => $rawDespStr
                ];
                continue;
            }
        } else {
            $idDespacho = $idDespachoFijo;
        }

        $clave = $idDespacho . ':' . $idCredito;
        if (isset($vistoPar[$clave])) {
            continue;
        }
        $vistoPar[$clave] = true;
        $pares[] = ['d' => $idDespacho, 'c' => $idCredito, 'cel' => $idCelula, 'fila' => $row];
    }

    $totalCreditosValidos = count($pares);

    if (empty($pares)) {
        return [
            'success' => false,
            'message' => 'No se encontraron filas válidas (id_credito' . ($modoPorFila ? ' + id_despacho' : '') . ') en el Excel.',
            'total_creditos_validos' => 0,
            'insertados' => 0,
            'actualizados' => 0,
            'duplicados' => 0,
            'duplicados_creditos' => [],
            'duplicados_detalle' => [],
            'modo' => $modoPorFila ? 'por_fila' : 'despacho_seleccionado',
            'errores' => $errores
        ];
    }

    if ($modoPorFila) {
        $idsDespachoUnicos = [];
        foreach ($pares as $p) {
            $idsDespachoUnicos[$p['d']] = true;
        }
        $listaDesp = array_map('intval', array_keys($idsDespachoUnicos));
        $validosDespacho = [];
        $chunkSize = 300;
        foreach (array_chunk($listaDesp, $chunkSize) as $chunk) {
            if (empty($chunk)) {
                continue;
            }
            $ph = [];
            $par = [];
            foreach ($chunk as $i => $did) {
                $k = 'dd' . $i;
                $ph[] = ':' . $k;
                $par[$k] = $did;
            }
            $sql = 'SELECT id FROM despachos WHERE estatus = \'Activo\' AND id IN (' . implode(',', $ph) . ')';
            $found = $this->db->queryAll($sql, $par);
            foreach ($found as $f) {
                if (!empty($f['id'])) {
                    $validosDespacho[(int) $f['id']] = true;
                }
            }
        }

        $paresOk = [];
        foreach ($pares as $p) {
            if (!isset($validosDespacho[$p['d']])) {
                $idD = (int) $p['d'];
                $idC = (int) $p['c'];
                $errores[] = [
                    'fila' => $p['fila'],
                    'reason' => sprintf(
                        'id_despacho %d no existe en «despachos» o no está activo (estatus distinto de «Activo»). Crédito en esta fila: %d.',
                        $idD,
                        $idC
                    ),
                    'valor' => (string) $idD,
                    'id_credito' => $idC,
                    'id_despacho' => $idD,
                ];
                continue;
            }
            $paresOk[] = $p;
        }
        $pares = $paresOk;
    }

    if (empty($pares)) {
        return [
            'success' => false,
            'message' => 'No quedaron filas válidas tras validar despachos.',
            'total_creditos_validos' => $totalCreditosValidos,
            'insertados' => 0,
            'actualizados' => 0,
            'duplicados' => 0,
            'duplicados_creditos' => [],
            'duplicados_detalle' => [],
            'modo' => $modoPorFila ? 'por_fila' : 'despacho_seleccionado',
            'errores' => $errores
        ];
    }

    $duplicadosDetalle = [];
    $duplicadosCreditosIds = [];

    $this->db->beginTransaction();
    try {
        $rLote = $this->aplicarAsignacionesCreditosDespachoEnLote($pares, $usuarioAsignacion);
        $insertados = $rLote['insertados'];
        $actualizados = $rLote['actualizados'];
        $duplicadosDetalle = $rLote['duplicadosDetalle'];

        $this->db->commit();
    } catch (\Exception $e) {
        $this->db->rollback();
        foreach ($duplicadosDetalle as $dd) {
            $duplicadosCreditosIds[] = (int) $dd['id_credito'];
        }
        $duplicadosCreditosIds = array_slice(array_values(array_unique($duplicadosCreditosIds)), 0, 50);

        return [
            'success' => false,
            'message' => 'Error al guardar en la base: ' . $e->getMessage(),
            'total_creditos_validos' => count($pares),
            'insertados' => 0,
            'actualizados' => 0,
            'duplicados' => count($duplicadosDetalle),
            'duplicados_creditos' => $duplicadosCreditosIds,
            'duplicados_detalle' => array_slice($duplicadosDetalle, 0, 30),
            'modo' => $modoPorFila ? 'por_fila' : 'despacho_seleccionado',
            'errores' => $errores
        ];
    }

    foreach ($duplicadosDetalle as $dd) {
        $duplicadosCreditosIds[] = (int) $dd['id_credito'];
    }
    $duplicadosCreditosIds = array_slice(array_values(array_unique($duplicadosCreditosIds)), 0, 50);

    $resp = [
        'success' => true,
        'total_creditos_validos' => count($pares),
        'insertados' => $insertados,
        'actualizados' => $actualizados,
        'duplicados' => count($duplicadosDetalle),
        'duplicados_creditos' => $duplicadosCreditosIds,
        'duplicados_detalle' => array_slice($duplicadosDetalle, 0, 30),
        'modo' => $modoPorFila ? 'por_fila' : 'despacho_seleccionado',
        'errores' => $errores,
        'total_errores' => count($errores)
    ];

    if (!$modoPorFila) {
        $resp['id_despacho'] = $idDespachoFijo;
    }

    return $resp;
}

    /**
     * Cambiar estatus de crédito (activar/desactivar)
     */
    public function cambiarEstatusCredito($idCredito, $nuevoEstatus)
{
    if ($nuevoEstatus === '0' || $nuevoEstatus === 0) {
        $query = <<<SQL
    UPDATE asigna_creditos_despacho
    SET estatus    = :nuevoEstatus,
        fecha_baja = :fechaBaja,
        baja       = NULL
    WHERE id_credito = :idCredito
    SQL;

        return $this->db->CRUD($query, [
            'idCredito' => $idCredito,
            'nuevoEstatus' => (string) $nuevoEstatus,
            'fechaBaja' => $this->fechaHoraCdmx(),
        ]) > 0;
    }

    $query = <<<SQL
    UPDATE asigna_creditos_despacho
    SET estatus    = :nuevoEstatus,
        fecha_baja = NULL,
        baja       = NULL
    WHERE id_credito = :idCredito
    SQL;

    return $this->db->CRUD($query, [
        'idCredito' => $idCredito,
        'nuevoEstatus' => (string) $nuevoEstatus,
    ]) > 0;
}

/**
 * Desasignar crédito de su despacho actual (soft-delete)
 * Solo actúa sobre el registro con estatus='1'
 * Después de esto, verificarAsignacion() devuelve false y el crédito queda libre
 */
public function desasignarCredito($idCredito)
{
    $usuarioBaja = $_SESSION['usuario_id'] ?? 1;

    $query = <<<SQL
    UPDATE asigna_creditos_despacho
    SET estatus    = '0',
        fecha_baja = :fechaBaja,
        baja       = :usuarioBaja
    WHERE id_credito = :idCredito
      AND estatus    = '1'
    SQL;

    return $this->db->CRUD($query, [
        'idCredito' => $idCredito,
        'fechaBaja' => $this->fechaHoraCdmx(),
        'usuarioBaja' => $usuarioBaja
    ]) > 0;
}


    /**
     * Obtener créditos asignados a un despacho
     * Consulta directamente desde asigna_creditos_despacho usando id_persona
     */
    public function obtenerCreditosAsignados($idPersona, $enriquecer = true)
    {
        // Obtener créditos asignados desde __SPARTA_SECRET_REDACTED__
        $query = <<<SQL
        SELECT
    acd.id_credito,
    acd.id_despacho,
    acd.estatus                                          AS estado,
    acd.baja,
    DATE_FORMAT(acd.fecha_alta, '%Y-%m-%d %H:%i')        AS fecha_asignacion,
    DATE_FORMAT(acd.fecha_baja, '%Y-%m-%d %H:%i')        AS fecha_desasignacion,
    CONCAT_WS(' ', per.nombres, per.apellidop)           AS asignado_por
FROM asigna_creditos_despacho acd
INNER JOIN despachos d  ON acd.id_despacho = d.id
LEFT JOIN  persona per  ON acd.alta        = per.id
WHERE d.id_persona = :idPersona
ORDER BY acd.fecha_alta DESC
SQL;

        $creditos = $this->db->queryAll($query, ['idPersona' => $idPersona]);

        if (empty($creditos) || !$enriquecer) {
            // Sin enriquecimiento: respuesta inmediata solo con datos locales
            return $creditos;
        }

        // Extraer IDs de crédito para buscar en tbl_segundometro_semana
        $idsCredito = array_column($creditos, 'id_credito');

        // Sanitizar IDs (asegurar que son numéricos)
        $idsCredito = array_map('intval', $idsCredito);

        // Conectar a db-megae-reporte
        $dbSegundometro = new \Core\DatabaseSegundometro();

        // Construir query con placeholders nombrados
        $placeholders = [];
        $params = [];
        foreach ($idsCredito as $idx => $idCredito) {
            $key = "id$idx";
            $placeholders[] = ":$key";
            $params[$key] = $idCredito;
        }
        $placeholdersStr = implode(',', $placeholders);

        $querySegundometro = "
            SELECT
                Id_credito,
                Nombre_cliente,
                Dias_mora,
                Bucket_Morosidad_Real,
                Saldo_total_capital as saldo
            FROM tbl_segundometro_semana
            WHERE Id_credito IN ($placeholdersStr)
        ";

        $datosCreditos = $dbSegundometro->queryAll($querySegundometro, $params);

        // Crear índice por id_credito para búsqueda rápida
        $mapaCreditos = [];
        foreach ($datosCreditos as $dato) {
            $mapaCreditos[$dato['Id_credito']] = $dato;
        }

        // Identificar IDs no encontrados en semana y buscarlos en histórico
        $idsFaltantes = array_filter($idsCredito, fn($id) => !isset($mapaCreditos[$id]));

        if (!empty($idsFaltantes)) {
            $placeholdersH = [];
            $paramsH = [];
            foreach (array_values($idsFaltantes) as $idx => $idCredito) {
                $key = "hid$idx";
                $placeholdersH[] = ":$key";
                $paramsH[$key] = $idCredito;
            }
            $placeholdersHStr = implode(',', $placeholdersH);

            $queryHisto = "
                SELECT Id_credito, MAX(Nombre_cliente) AS Nombre_cliente, MAX(Dias_mora) AS Dias_mora, MAX(Bucket_Morosidad_Real) AS Bucket_Morosidad_Real, MAX(Saldo_total_capital) AS saldo
                FROM tbl_segundometro_histo
                WHERE Id_credito IN ($placeholdersHStr)
                GROUP BY Id_credito
            ";
            $datosHisto = $dbSegundometro->queryAll($queryHisto, $paramsH);
            foreach ($datosHisto as $dato) {
                if (!isset($mapaCreditos[$dato['Id_credito']])) {
                    $mapaCreditos[$dato['Id_credito']] = $dato;
                }
            }
        }

        // Enriquecer créditos con datos de segundometro
        foreach ($creditos as &$credito) {
            $idCredito = $credito['id_credito'];
            if (isset($mapaCreditos[$idCredito])) {
                $credito['nombre_cliente']        = $mapaCreditos[$idCredito]['Nombre_cliente']       ?? 'No disponible';
                $credito['dias_mora']              = $mapaCreditos[$idCredito]['Dias_mora']             ?? 0;
                $credito['Bucket_Morosidad_Real']  = $mapaCreditos[$idCredito]['Bucket_Morosidad_Real'] ?? null;
                $credito['saldo']                  = $mapaCreditos[$idCredito]['saldo']                 ?? 0;
            } else {
                $credito['nombre_cliente']        = 'No disponible';
                $credito['dias_mora']              = 0;
                $credito['Bucket_Morosidad_Real']  = null;
                $credito['saldo']                  = 0;
            }
        }
        unset($credito); // Romper referencia

        return $creditos;
    }

    /**
     * Guardar comentario sobre un despacho
     */
    public function guardarComentario($idPersona, $comentario)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);

        if (!$despacho) {
            return false;
        }

        $query = <<<SQL
        INSERT INTO comentarios_despacho
        (id_despacho, comentario, id_persona_comenta, fecha_comentario)
        VALUES (:idDespacho, :comentario, :idPersona, NOW())
SQL;

        $idPersonaComenta = $_SESSION['usuario_id'] ?? 1;

        return $this->db->CRUD($query, [
            'idDespacho' => $despacho['id'],
            'comentario' => $comentario,
            'idPersona' => $idPersonaComenta
        ]) > 0;
    }

    /**
     * Obtener catálogo de documentos para despachos
     */
    public function obtenerCatalogoDocumentos()
    {
        $query = "SELECT id, nombre_documento, descripcion
                  FROM catalogo_documentos_despacho
                  ORDER BY nombre_documento";

        return $this->db->queryAll($query);
    }

    /**
     * Obtener documentos cargados para un despacho específico
     */
    public function obtenerDocumentosDespacho($idPersona)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);

        if (!$despacho) {
            return [];
        }

        $query = <<<SQL
        SELECT
            dd.id,
            dd.id_catalogo_documento,
            dd.nombre_archivo,
            dd.ruta_archivo,
            dd.fecha_carga,
            dd.estatus,
            cd.nombre_documento,
            cd.descripcion,
            'Sistema' as cargado_por
        FROM documentos_despacho dd
        INNER JOIN catalogo_documentos_despacho cd ON dd.id_catalogo_documento = cd.id
        WHERE dd.id_despacho = :idDespacho
        ORDER BY dd.fecha_carga DESC
SQL;

        return $this->db->queryAll($query, ['idDespacho' => $despacho['id']]);
    }

    /**
     * Subir un documento para un despacho
     */
    public function subirDocumento($idPersona, $idCatalogoDocumento, $nombreArchivo, $rutaArchivo)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);

        if (!$despacho) {
            return false;
        }

        $query = <<<SQL
        INSERT INTO documentos_despacho
        (id_despacho, id_catalogo_documento, nombre_archivo, ruta_archivo, fecha_carga, estatus)
        VALUES (:idDespacho, :idCatalogoDocumento, :nombreArchivo, :rutaArchivo, NOW(), 'Vigente')
SQL;

        return $this->db->CRUD($query, [
            'idDespacho' => $despacho['id'],
            'idCatalogoDocumento' => $idCatalogoDocumento,
            'nombreArchivo' => $nombreArchivo,
            'rutaArchivo' => $rutaArchivo
        ]) > 0;
    }

    /**
     * Actualizar tipo de persona en la tabla despachos
     */
    public function actualizarTipoPersona($idPersona, $tipoPersona)
    {
        // Primero verificar si existe registro en despachos
        $queryVerificar = "SELECT id FROM despachos WHERE id_persona = :idPersona LIMIT 1";
        $despacho = $this->db->queryOne($queryVerificar, ['idPersona' => $idPersona]);

        if ($despacho) {
            // Si existe, actualizar
            $query = "UPDATE despachos SET tipo_persona = :tipoPersona WHERE id = :id";
            return $this->db->CRUD($query, [
                'id' => $despacho['id'],
                'tipoPersona' => $tipoPersona
            ]) > 0;
        } else {
            // Si no existe, crear registro en despachos
            $query = <<<SQL
            INSERT INTO despachos (id_persona, tipo_persona, estatus, fecha_alta)
            VALUES (:idPersona, :tipoPersona, 'Activo', NOW())
SQL;
            return $this->db->CRUD($query, [
                'idPersona' => $idPersona,
                'tipoPersona' => $tipoPersona
            ]) > 0;
        }
    }

    /**
     * Obtener información de un documento por su ID
     */
    public function obtenerInfoDocumento($idDocumento)
    {
        $query = "SELECT nombre_archivo, ruta_archivo FROM documentos_despacho WHERE id = :id";
        return $this->db->queryOne($query, ['id' => $idDocumento]);
    }

    /**
     * Obtener historial COMPLETO de asignaciones de un crédito (todos los gestores que lo tuvieron).
     * Sin LIMIT: devuelve todas las filas ordenadas de más reciente a más antigua.
     */
    public function obtenerHistorialGestores($idCredito)
    {
        $query = <<<SQL
        SELECT
            acd.id_credito,
            acd.estatus,
            DATE_FORMAT(acd.fecha_alta, '%Y-%m-%d %H:%i') AS fecha_asignacion,
            DATE_FORMAT(acd.fecha_baja, '%Y-%m-%d %H:%i') AS fecha_baja,
            d.id_persona,
            CONCAT_WS(' ', per.nombres, per.apellidop) AS nombre_despacho,
            GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ') AS puesto_despacho,
            CONCAT_WS(' ', per_asigno.nombres, per_asigno.apellidop) AS asignado_por
        FROM asigna_creditos_despacho acd
        INNER JOIN despachos d ON acd.id_despacho = d.id
        INNER JOIN persona per ON d.id_persona = per.id
        LEFT JOIN asigna_puesto ap ON per.id = ap.id_persona AND ap.activo = 1
        LEFT JOIN puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN persona per_asigno ON acd.alta = per_asigno.id
        WHERE acd.id_credito = :idCredito
        GROUP BY acd.id, acd.id_credito, acd.estatus, acd.fecha_alta, acd.fecha_baja,
                 d.id_persona, per.nombres, per.apellidop, per_asigno.nombres, per_asigno.apellidop
        ORDER BY acd.fecha_alta DESC
SQL;

        return $this->db->queryAll($query, ['idCredito' => $idCredito]);
    }

    /**
     * Obtener convenios registrados para un crédito.
     * Incluye conteo de pagos realizados desde la tabla de amortización.
     */
    public function obtenerConveniosCredito($idCredito)
    {
        $query = <<<SQL
        SELECT
            cc.id,
            cc.estatus,
            DATE_FORMAT(cc.fecha_acuerdo, '%Y-%m-%d') AS fecha_registro,
            cc.usuario_alta                            AS registrado_por,
            cc.total_a_pagar                          AS monto_total,
            cc.numero_semanas                         AS total_parcialidades,
            cc.pago_semanal                           AS monto_parcialidad,
            COALESCE(
                (SELECT COUNT(*)
                 FROM convenio_cliente_amortizacion cca
                 WHERE cca.id_convenio_cliente = cc.id
                   AND cca.estatus_pago = 'pagado'),
            0) AS pagos_realizados
        FROM convenio_cliente cc
        WHERE cc.id_credito = :idCredito
        ORDER BY cc.fecha_acuerdo DESC
SQL;

        return $this->db->queryAll($query, ['idCredito' => $idCredito]);
    }
}
