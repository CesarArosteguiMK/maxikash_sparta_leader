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
     * Obtener dirección completa desde tbl_segundometro_semana
     * Esta función consulta la base de datos db-megae-reporte
     */
    private function obtenerDomicilioCompleto($idCredito)
    {
        try {
            $dbSegundo = new DatabaseSegundometro();
            $query = <<<SQL
            SELECT
                Domicilio_Completo,
                Id_cliente,
                Nombre_cliente
            FROM tbl_segundometro_semana
            WHERE Id_credito = :idCredito
            LIMIT 1
SQL;

            $resultado = $dbSegundo->queryOne($query, ['idCredito' => $idCredito]);
            return $resultado['Domicilio_Completo'] ?? null;
        } catch (\Exception $e) {
            error_log("Error al obtener Domicilio_Completo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener lista de despachos (Gestores y Supervisores)
     * IDs de puesto: 24 = Gestor, 36 = Supervisor
     * Un despacho = Una persona con cualquiera de estos 2 puestos
     */
    public function obtenerDespachos()
    {
        $query = <<<SQL
        SELECT
            ap.id_persona,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.id AS id_puesto,
            pu.nombre AS nombre_puesto,
            ap.activo,
            per.estatus,
            per.correo,
            per.telefono_uno
        FROM asigna_puesto ap
        INNER JOIN persona per ON per.id = ap.id_persona
        INNER JOIN puesto pu ON pu.id = ap.id_puesto
        WHERE ap.id_puesto IN (24, 36)
        ORDER BY pu.id, per.nombres
SQL;

        return $this->db->queryAll($query);
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

        // Intentar obtener dirección desde tbl_segundometro_semana (prioridad)
        $domicilioCompleto = $this->obtenerDomicilioCompleto($valor);

        // Usar domicilio completo si existe, sino la de la API, sino mensaje por defecto
        $direccion = $domicilioCompleto ?: ($direccionAPI ?: 'Sin dirección registrada');

        return [
            'id_credito' => $estadoCuenta["idCredito"] ?? $valor,
            'nombre_cliente' => $cliente["nombreCliente"] ?? 'Sin nombre',
            'saldo_actual' => $estadoCuenta["datosSaldos"]["saldoTotalVencido"] ?? 0,
            'dias_mora' => $estadoCuenta["datosSaldos"]["diasMoraMaximo"] ?? 0,
            'telefono' => $cliente["celular"] ?? 'Sin teléfono',
            'curp' => $cliente["curp"] ?? 'Sin CURP',
            'direccion' => $direccion,
            'direccion_api' => $direccionAPI ?: 'No disponible en API',
            'direccion_megareporte' => $domicilioCompleto ?: 'No disponible en Megareporte',
            'sucursal' => $cliente["sucursal"] ?? 'Sin sucursal',
            'fecha_desembolso' => $estadoCuenta["fechaDesembolso"] ?? 'Sin fecha'
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
            pu.nombre as puesto_despacho,
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
        $asignacion = $this->obtenerAsignacionCredito($idCredito);
        return $asignacion && $asignacion['estatus'] === '1';
    }

    /**
     * Asignar crédito a un despacho
     */
    public function asignarCredito($idPersona, $idCredito)
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

        // Verificar si ya existe una asignación previa para este crédito y despacho
        $queryVerificar = "SELECT id FROM asigna_creditos_despacho WHERE id_despacho = :idDespacho AND id_credito = :idCredito LIMIT 1";
        $existente = $this->db->queryOne($queryVerificar, [
            'idDespacho' => $despacho['id'],
            'idCredito' => $idCredito
        ]);

        $usuarioAsignacion = $_SESSION['usuario_id'] ?? 1;

        if ($existente) {
            // Si ya existe, reactivar (UPDATE)
            $query = <<<SQL
            UPDATE asigna_creditos_despacho
            SET estatus = '1',
                fecha_baja = NULL,
                fecha_alta = NOW(),
                alta = :usuarioAsignacion,
                celula = 1
            WHERE id = :id
SQL;
            return $this->db->CRUD($query, [
                'id' => $existente['id'],
                'usuarioAsignacion' => $usuarioAsignacion
            ]) > 0;
        } else {
            // Si no existe, crear nuevo (INSERT)
            $query = <<<SQL
            INSERT INTO asigna_creditos_despacho
            (id_despacho, id_credito, fecha_alta, alta, estatus, celula)
            VALUES (:idDespacho, :idCredito, NOW(), :usuarioAsignacion, '1', 1)
SQL;
            return $this->db->CRUD($query, [
                'idDespacho' => $despacho['id'],
                'idCredito' => $idCredito,
                'usuarioAsignacion' => $usuarioAsignacion
            ]) > 0;
        }
    }

    /**
     * Importar Excel para asignar créditos masivamente.
     *
     * Modo A — columnas en fila 1: id_credito + id_despacho (por fila puede ir a distintos despachos).
     * Modo B — solo id_credito: usa el despacho activo del id_persona seleccionado en pantalla.
     *
     * Valida números enteros > 0 y que id_despacho exista en despachos (estatus Activo).
     */
    public function importarAsignaCreditosDesdeExcel($idPersona, $excelPath)
    {
        require_once __DIR__ . '/../libs/PhpSpreadsheet/vendor/autoload.php';

        $errores = [];
        $usuarioAsignacion = $_SESSION['usuario_id'] ?? 1;

        $sheet = null;
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($excelPath);
            $sheet = $spreadsheet->getActiveSheet();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al leer el Excel: ' . $e->getMessage(),
                'errores' => []
            ];
        }

        $highestRow = (int) $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

        $normalizeHeader = function ($v) {
            $s = trim((string) $v);
            if ($s === '') {
                return '';
            }
            $s = mb_strtolower($s, 'UTF-8');
            $s = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
            $s = preg_replace('/[^a-z0-9]+/i', '_', $s);
            $s = trim((string) $s, '_');
            return $s;
        };

        $colIndexIdCredito = null;
        $colIndexIdDespacho = null;
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $headerVal = $sheet->getCell($colLetter . '1')->getValue();
            $norm = $normalizeHeader($headerVal);
            if ($norm === 'id_credito') {
                $colIndexIdCredito = $col;
            }
            if ($norm === 'id_despacho') {
                $colIndexIdDespacho = $col;
            }
        }

        if ($colIndexIdCredito === null) {
            return [
                'success' => false,
                'message' => "Encabezado incorrecto: debe existir la columna 'id_credito' (no cambies el nombre).",
                'errores' => []
            ];
        }

        $modoPorFila = $colIndexIdDespacho !== null;

        if (!$modoPorFila) {
            $idPersonaInt = (int) $idPersona;
            if ($idPersonaInt <= 0) {
                return [
                    'success' => false,
                    'message' => 'Seleccione un despacho en pantalla o agregue la columna id_despacho en el Excel.'
                ];
            }

            $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
            $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersonaInt]);

            if (!$despacho) {
                $queryInsert = "INSERT INTO despachos (id_persona, estatus, fecha_alta) VALUES (:idPersona, 'Activo', NOW())";
                $insertado = $this->db->CRUD($queryInsert, ['idPersona' => $idPersonaInt]);
                if (!$insertado) {
                    return [
                        'success' => false,
                        'message' => 'No se pudo crear/obtener el despacho activo para el id_persona seleccionado.'
                    ];
                }
                $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersonaInt]);
            }

            if (!$despacho || empty($despacho['id'])) {
                return [
                    'success' => false,
                    'message' => 'No se encontró el despacho activo y no se pudo crear.'
                ];
            }

            $idDespachoFijo = (int) $despacho['id'];
        }

        $colLetterIdCredito = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndexIdCredito);
        $colLetterIdDespacho = $modoPorFila
            ? \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndexIdDespacho)
            : null;

        $pares = []; // [ 'd' => int, 'c' => int, 'fila' => int ]
        $vistoPar = [];

        for ($row = 2; $row <= $highestRow; $row++) {
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
            $pares[] = ['d' => $idDespacho, 'c' => $idCredito, 'fila' => $row];
        }

        $totalCreditosValidos = count($pares);

        if (empty($pares)) {
            return [
                'success' => false,
                'message' => 'No se encontraron filas válidas (id_credito' . ($modoPorFila ? ' + id_despacho' : '') . ') en el Excel.',
                'total_creditos_validos' => 0,
                'insertados' => 0,
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
                    $errores[] = [
                        'fila' => $p['fila'],
                        'reason' => 'id_despacho no existe o no está activo en la tabla despachos',
                        'valor' => (string) $p['d']
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
                'duplicados' => 0,
                'duplicados_creditos' => [],
                'duplicados_detalle' => [],
                'modo' => $modoPorFila ? 'por_fila' : 'despacho_seleccionado',
                'errores' => $errores
            ];
        }

        $duplicadosDetalle = [];
        $duplicadosSet = [];
        $chunkPairs = 200;
        foreach (array_chunk($pares, $chunkPairs) as $chunk) {
            if (empty($chunk)) {
                continue;
            }
            $parts = [];
            $params = [];
            $i = 0;
            foreach ($chunk as $p) {
                $parts[] = '(:d' . $i . ',:c' . $i . ')';
                $params['d' . $i] = $p['d'];
                $params['c' . $i] = $p['c'];
                $i++;
            }
            $sql = 'SELECT id_despacho, id_credito FROM asigna_creditos_despacho WHERE (id_despacho, id_credito) IN (' . implode(',', $parts) . ')';
            $rows = $this->db->queryAll($sql, $params);
            foreach ($rows as $r) {
                $d = (int) $r['id_despacho'];
                $c = (int) $r['id_credito'];
                $duplicadosSet["$d:$c"] = true;
                $duplicadosDetalle[] = ['id_despacho' => $d, 'id_credito' => $c];
            }
        }

        $toInsert = [];
        foreach ($pares as $p) {
            $k = $p['d'] . ':' . $p['c'];
            if (!isset($duplicadosSet[$k])) {
                $toInsert[] = $p;
            }
        }

        $duplicadosCreditosIds = [];
        foreach ($duplicadosDetalle as $d) {
            $duplicadosCreditosIds[] = (int) $d['id_credito'];
        }
        $duplicadosCreditosIds = array_slice(array_values(array_unique($duplicadosCreditosIds)), 0, 50);

        $insertados = 0;
        $this->db->beginTransaction();
        try {
            foreach ($toInsert as $p) {
                $query = <<<SQL
                INSERT INTO asigna_creditos_despacho
                (id_despacho, id_credito, fecha_alta, alta, estatus, celula)
                VALUES (:idDespacho, :idCredito, NOW(), :usuarioAsignacion, '1', 1)
SQL;
                $ok = $this->db->CRUD($query, [
                    'idDespacho' => $p['d'],
                    'idCredito' => $p['c'],
                    'usuarioAsignacion' => $usuarioAsignacion
                ]) > 0;
                if ($ok) {
                    $insertados++;
                }
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Error insertando en la base: ' . $e->getMessage(),
                'total_creditos_validos' => count($pares),
                'insertados' => 0,
                'duplicados' => count($duplicadosDetalle),
                'duplicados_creditos' => $duplicadosCreditosIds,
                'duplicados_detalle' => array_slice($duplicadosDetalle, 0, 30),
                'modo' => $modoPorFila ? 'por_fila' : 'despacho_seleccionado',
                'errores' => $errores
            ];
        }

        $resp = [
            'success' => true,
            'total_creditos_validos' => count($pares),
            'insertados' => $insertados,
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
        $fechaBaja = $nuevoEstatus === '0' ? 'NOW()' : 'NULL';

        // Actualizar todos los registros de este crédito (un crédito solo puede estar activo en un lugar a la vez)
        $query = <<<SQL
        UPDATE asigna_creditos_despacho
        SET estatus = :nuevoEstatus,
            fecha_baja = $fechaBaja
        WHERE id_credito = :idCredito
SQL;

        return $this->db->CRUD($query, [
            'idCredito' => $idCredito,
            'nuevoEstatus' => $nuevoEstatus
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
            acd.estatus as estado,
            DATE_FORMAT(acd.fecha_alta, '%Y-%m-%d %H:%i') as fecha_asignacion,
            CONCAT_WS(' ', per.nombres, per.apellidop) as asignado_por
        FROM asigna_creditos_despacho acd
        INNER JOIN despachos d ON acd.id_despacho = d.id
        LEFT JOIN persona per ON acd.alta = per.id
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
                SELECT Id_credito, MAX(Nombre_cliente) AS Nombre_cliente, MAX(Dias_mora) AS Dias_mora, MAX(Saldo_total_capital) AS saldo
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
                $credito['nombre_cliente'] = $mapaCreditos[$idCredito]['Nombre_cliente'] ?? 'No disponible';
                $credito['dias_mora'] = $mapaCreditos[$idCredito]['Dias_mora'] ?? 0;
                $credito['saldo'] = $mapaCreditos[$idCredito]['saldo'] ?? 0;
            } else {
                $credito['nombre_cliente'] = 'No disponible';
                $credito['dias_mora'] = 0;
                $credito['saldo'] = 0;
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
}
