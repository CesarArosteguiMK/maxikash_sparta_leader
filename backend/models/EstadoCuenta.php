<?php

namespace Models;

use Core\DatabaseSegundometro;
use Core\Model;
use Core\Database;
use Core\DatabaseAWS;

class EstadoCuenta extends Model
{
    public static function buscarCreditoPorNombre($nombre)
    {
        $qry = "
            SELECT
                id,
                CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) AS nombre_completo
            FROM persona
            WHERE CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) LIKE :nombre
              AND estatus = 'Activo'
            ORDER BY nombres, apellidop
            LIMIT 10
        ";
        $val = ['nombre' => '%' . $nombre . '%'];

        try {
            $db = new Database();
            $r = $db->queryAll($qry, $val);  // igual que fetchAll
            return self::resultado(true, 'Créditos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error buscando créditos.', null, $e->getMessage());
        }
    }
    public static function obtenerDocumentoOferta($id, $tipo)
    {
        if (!$id || !is_numeric($id)) {
            return self::resultado(false, 'ID inválido');
        }

        if (!in_array($tipo, ['FAD_DOC', 'EVIDENCIA'])) {
            return self::resultado(false, 'Tipo de documento inválido');
        }

        if ($tipo === 'FAD_DOC') {
            $qry = "
                SELECT nombre_archivo
                FROM oferta_documentos
                WHERE tipo_documento IN ('FAD', 'FAD_DOC')
                AND fk_oferta = :id
                LIMIT 1
            ";
        } else {
            $qry = "
                SELECT nombre_archivo
                FROM oferta_documentos
                WHERE tipo_documento = 'EVIDENCIA'
                AND fk_oferta = :id
                LIMIT 1
            ";
        }

        $val = [
            'id' => $id
        ];

        try {
            // Documentación / oferta_documentos está en __SPARTA_SECRET_REDACTED__
            $db = new DatabaseAWS('__SPARTA_SECRET_REDACTED__');
            $r = $db->queryAll($qry, $val);

            if (!$r || count($r) === 0) {
                return self::resultado(false, 'Documento no encontrado');
            }

            return self::resultado(true, 'Documento encontrado', $r[0]);

        } catch (\Throwable $e) {
            return self::resultado(false, 'Documento no encontrado', null, $e->getMessage());
        }
    }

    /**
     * Obtiene INE (frente y reverso) desde persona_documentos vía oferta (id crédito = id_oferta).
     * JOIN: oferta.id_oferta = id_credito → oferta.fk_persona → persona_documentos.fk_persona.
     *
     * @param int|string $idCredito ID de crédito (id_oferta)
     * @return array resultado(): success, data con archivo_ine_frente y archivo_ine_reverso, o error
     */
    public static function obtenerINEPersonaDocumentos($idCredito)
    {
        if (!$idCredito || !is_numeric($idCredito)) {
            return self::resultado(false, 'ID de crédito inválido', null);
        }

        $qry = "
            SELECT pd.archivo_ine_frente, pd.archivo_ine_reverso
            FROM persona_documentos pd
            INNER JOIN oferta o ON o.fk_persona = pd.fk_persona
            WHERE o.id_oferta = :id_credito
            LIMIT 1
        ";
        $val = ['id_credito' => (int) $idCredito];

        try {
            $db = new DatabaseAWS('__SPARTA_SECRET_REDACTED__');
            $r = $db->queryOne($qry, $val);
            if (!$r) {
                return self::resultado(false, 'INE no encontrado en persona_documentos', null);
            }
            return self::resultado(true, 'OK', $r);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al consultar INE', null, $e->getMessage());
        }
    }

    public static function getClientesEstadoCuentaPorNombre($data)
    {
        if (!$data || !isset($data['nombre'])) {
            return self::resultado(false, 'Nombre no recibido.', []);
        }

        $nombre = trim($data['nombre']);

        $query = <<<SQL
            SELECT
                id,
                CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) AS nombre_completo
            FROM persona
            WHERE CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) LIKE :nombre
              AND estatus = 'Activo'
            ORDER BY nombres, apellidop
            LIMIT 10
        SQL;

        try {
            $db = new Database();
            $params = [':nombre' => "%{$nombre}%"];
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Personas encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', [], $e->getMessage());
        }
    }

    /**
     * Registra en auditoria___SPARTA_SECRET_REDACTED__ cada consulta de estado de cuenta.
     * Columnas: usuario (email/user_name), id_credito, fecha_corte, fecha_consulta, exito, mensaje_error.
     *
     * @param string     $usuario     Usuario que consulta, ej. email ($_SESSION['usuario'])
     * @param int|string $id_credito ID del crédito consultado
     * @param string     $fecha_corte Fecha de corte consultada (Y-m-d)
     * @param int        $exito       1 = éxito, 0 = error
     * @param string|null $mensaje_error Mensaje de error cuando exito = 0
     * @return bool true si se insertó, false si falló (no lanza excepción)
     */
    public static function registrarAuditoria($usuario, $id_credito, $fecha_corte, $exito, $mensaje_error = null)
    {
        $sql = "
            INSERT INTO auditoria___SPARTA_SECRET_REDACTED__
            (usuario, id_credito, fecha_corte, fecha_consulta, exito, mensaje_error)
            VALUES (:usuario, :id_credito, :fecha_corte, :fecha_consulta, :exito, :mensaje_error)
        ";
        $valores = [
            'usuario'        => $usuario !== null && $usuario !== '' ? (string) $usuario : null,
            'id_credito'     => $id_credito !== null && $id_credito !== '' ? (int) $id_credito : null,
            'fecha_corte'    => $fecha_corte,
            'fecha_consulta' => date('Y-m-d H:i:s'),
            'exito'          => (int) $exito,
            'mensaje_error'  => $mensaje_error !== null && $mensaje_error !== '' ? (string) $mensaje_error : null
        ];
        try {
            $db = new Database();
            $db->CRUD($sql, $valores);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Registra en auditoria_documentos cada consulta de documento.
     * Columnas: usuario, documento_clave, documento_nombre, id_referencia, fecha_consulta, exito, mensaje_error.
     *
     * @param string     $usuario         Usuario que consulta, ej. email ($_SESSION['usuario'])
     * @param string     $documento_clave Clave del documento (ej. INE, FAD_DOC)
     * @param string     $documento_nombre Nombre para mostrar (ej. INE, Factura)
     * @param int|string|null $id_referencia ID consultado (crédito, etc.)
     * @param int        $exito           1 = éxito, 0 = error
     * @param string|null $mensaje_error  Mensaje de error cuando exito = 0
     * @return bool true si se insertó, false si falló (no lanza excepción)
     */
    public static function registrarAuditoriaDocumentos($usuario, $documento_clave, $documento_nombre, $id_referencia, $exito, $mensaje_error = null)
    {
        $sql = "
            INSERT INTO auditoria_documentos
            (usuario, documento_clave, documento_nombre, id_referencia, fecha_consulta, exito, mensaje_error)
            VALUES (:usuario, :documento_clave, :documento_nombre, :id_referencia, :fecha_consulta, :exito, :mensaje_error)
        ";
        $valores = [
            'usuario'         => $usuario !== null && $usuario !== '' ? (string) $usuario : null,
            'documento_clave' => $documento_clave !== null && $documento_clave !== '' ? (string) $documento_clave : null,
            'documento_nombre' => $documento_nombre !== null && $documento_nombre !== '' ? (string) $documento_nombre : null,
            'id_referencia'   => $id_referencia !== null && $id_referencia !== '' ? (int) $id_referencia : null,
            'fecha_consulta'  => date('Y-m-d H:i:s'),
            'exito'           => (int) $exito,
            'mensaje_error'   => $mensaje_error !== null && $mensaje_error !== '' ? (string) $mensaje_error : null
        ];
        try {
            $db = new Database();
            $db->CRUD($sql, $valores);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getPorNombreEstadoCuenta()
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $r = $db->queryAll(
                "SELECT
                        id,
                        CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) AS nombre_completo
                    FROM persona
                    WHERE CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) LIKE '%jona%'
                      AND estatus = 'Activo'
                    ORDER BY nombres, apellidop
                    LIMIT 10
            ");
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Departamentos encontrados.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit; // <- Muy importante: evita que se imprima algo extra
    }

    public static function insertNotas($data)
    {
        // 🔹 Escapamos valores
        $id_credito = addslashes($data['id_credito']);
        $nota = addslashes($data['nota']);
        $usuario = addslashes($data['usuario']);
        $usuario_id = addslashes($data['usuario_id']);


        try {
            $db = new Database();

            // 1️⃣ Ejecutamos INSERT con queryOne() aunque no devuelve filas
            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.notas_credito
            (id_nota, id_credito, nota, id_usuario, usuario, created_at)
            VALUES(DEFAULT, $id_credito, '$nota', '$usuario_id','$usuario', DEFAULT)
            ");


            // 2️⃣ Obtenemos el ID insertado con queryOne()
            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");


            return self::resultado(true, 'Nota agregada correctamente.', $result);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al agregar nota.', null, $e->getMessage());
        }
    }

    public static function getNotasCredito($idCredito)
    {

        $query = <<<SQL
        SELECT
            id_nota,
            id_credito,
            nota,
            usuario,
            created_at
        FROM __SPARTA_SECRET_REDACTED__.notas_credito
        WHERE id_credito = :id_credito
        ORDER BY created_at DESC
    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query, ['id_credito' => $idCredito]);

            return self::resultado(true, 'Notas encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener notas del crédito.',
                [],
                $e->getMessage()
            );
        }
    }

    public static function getTiposContacto()
    {
        $query = "SELECT id, nombre FROM cat_tipo_contacto ORDER BY nombre";

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Tipos de contacto', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener tipos', [], $e->getMessage());
        }
    }

    public static function getResultadosContacto($tipoContactoId)
    {
        $query = "
        SELECT id, nombre
        FROM cat_resultado_contacto
        WHERE tipo_contacto_id = :tipo_contacto_id
        ORDER BY nombre
    ";

        try {
            $db = new Database();
            $r = $db->queryAll($query, ['tipo_contacto_id' => $tipoContactoId]);
            return self::resultado(true, 'Resultados de contacto', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error resultados contacto', [], $e->getMessage());
        }
    }

    public static function getDictamenes($resultadoContactoId)
    {
        $query = "
        SELECT id, nombre
        FROM cat_dictamen
        WHERE resultado_contacto_id = :resultado_contacto_id
        ORDER BY nombre
    ";

        try {
            $db = new Database();
            $r = $db->queryAll($query, ['resultado_contacto_id' => $resultadoContactoId]);
            return self::resultado(true, 'Dictámenes', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error dictámenes', [], $e->getMessage());
        }
    }

    public static function getMotivosNoPago()
    {
        $query = "
        SELECT
            m.id,
            CONCAT(t.nombre, ' - ', m.descripcion) AS descripcion
        FROM cat_motivo_no_pago m
        JOIN cat_motivo_no_pago_tipo t ON t.id = m.tipo_id
        ORDER BY t.nombre, m.descripcion
    ";

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Motivos no pago', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error motivos no pago', [], $e->getMessage());
        }
    }

    public static function getPlataformas()
    {
        $query = "SELECT id, nombre FROM cat_plataforma ORDER BY nombre";

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Plataformas', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error plataformas', [], $e->getMessage());
        }
    }

    public static function getTiposMotivoNoPago()
    {
        $query = "
        SELECT id, nombre
        FROM cat_motivo_no_pago_tipo
        ORDER BY nombre
    ";

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Tipos motivo no pago', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error tipos motivo', [], $e->getMessage());
        }
    }

    public static function getMotivosNoPagoPorTipo($tipoId)
    {
        $query = "
        SELECT id, descripcion
        FROM cat_motivo_no_pago
        WHERE tipo_id = :tipo_id
        ORDER BY descripcion
    ";

        try {
            $db = new Database();
            $r = $db->queryAll($query, ['tipo_id' => $tipoId]);
            return self::resultado(true, 'Motivos no pago', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error motivos', [], $e->getMessage());
        }
    }

    public static function insertDictamenLlamada($data)
    {
        try {

            $db = new Database();

            $id_credito            = (int)$data['id_credito'];
            $tipo_contacto_id      = (int)$data['tipo_contacto_id'];
            $resultado_contacto_id = (int)$data['resultado_contacto_id'];
            $dictamen_id           = (int)$data['dictamen_id'];

            $motivo_no_pago_id = $data['motivo_no_pago_id']
                ? (int)$data['motivo_no_pago_id']
                : 'NULL';

            $plataforma_id = (int)$data['plataforma_id'];

            $fuente_ingresos = addslashes($data['fuente_ingresos']);
            $comentarios     = addslashes($data['comentarios']);

            // 👇 CAMPOS AUTOMÁTICOS
            $fecha_gestion = date('Y-m-d');
            $hora_gestion  = date('H:i:s');
            $agente        = addslashes($data['usuario_id']);

            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.dictamen_llamada (
                id_credito,
                tipo_contacto_id,
                resultado_contacto_id,
                dictamen_id,
                motivo_no_pago_id,
                plataforma_id,
                fuente_ingresos,
                fecha_gestion,
                hora_gestion,
                agente,
                comentarios
            ) VALUES (
                $id_credito,
                $tipo_contacto_id,
                $resultado_contacto_id,
                $dictamen_id,
                $motivo_no_pago_id,
                $plataforma_id,
                '$fuente_ingresos',
                '$fecha_gestion',
                '$hora_gestion',
                '$agente',
                '$comentarios'
            )
        ");

            $id = $db->queryOne("SELECT LAST_INSERT_ID() AS id");

            return self::resultado(true, 'Dictamen guardado correctamente', $id);

        } catch (\Exception $e) {

            return self::resultado(
                false,
                'Error al guardar el dictamen',
                null,
                $e->getMessage()
            );
        }
    }

    public static function obtenerReportesDictamenPorFecha($fechaInicio, $fechaFin)
{
    try {
        $query = "
            SELECT 
                dl.id AS id_dictamen,
                DATE(dl.fecha_gestion) AS fecha_registro,
                TIME(dl.hora_gestion) AS hora_registro,
                dl.id_credito,
                p.nombres,
                p.segundo_nombre,
                p.apellidop,
                p.apellidom,
                CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_cliente,
                tc.nombre AS tipo_contacto,
                rc.nombre AS resultado_contacto,
                cd.nombre AS dictamen,
                cmnp.descripcion AS motivo_no_pago,
                cmnpt.nombre AS tipo_motivo_no_pago,
                cp.nombre AS plataforma,
                dl.fuente_ingresos,
                dl.comentarios,
                dl.agente AS usuario_id,
                CONCAT_WS(' ', u.nombres, u.segundo_nombre, u.apellidop, u.apellidom) AS agente
            FROM __SPARTA_SECRET_REDACTED__.dictamen_llamada dl
            LEFT JOIN persona p ON dl.id_credito = p.id
            LEFT JOIN persona u ON dl.agente = u.id
            LEFT JOIN cat_tipo_contacto tc ON dl.tipo_contacto_id = tc.id
            LEFT JOIN cat_resultado_contacto rc ON dl.resultado_contacto_id = rc.id
            LEFT JOIN cat_dictamen cd ON dl.dictamen_id = cd.id
            LEFT JOIN cat_motivo_no_pago cmnp ON dl.motivo_no_pago_id = cmnp.id
            LEFT JOIN cat_motivo_no_pago_tipo cmnpt ON cmnp.tipo_id = cmnpt.id
            LEFT JOIN cat_plataforma cp ON dl.plataforma_id = cp.id
            
            WHERE DATE(dl.fecha_gestion) BETWEEN :fecha_inicio AND :fecha_fin
            ORDER BY dl.fecha_gestion DESC, dl.hora_gestion DESC
        ";
        
        $db = new Database();
        $params = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
        
        $resultados = $db->queryAll($query, $params);
        
        return self::resultado(true, 'Reportes obtenidos', $resultados);
        
    } catch (\Exception $e) {
        error_log("Error al obtener reportes de dictamen: " . $e->getMessage());
        return self::resultado(false, 'Error al obtener reportes', [], $e->getMessage());
    }
}



// Método adicional para descarga (sin formato JSON)
public static function obtenerReportesDictamenParaDescarga($fechaInicio, $fechaFin)
{
    try {
        $query = "
            SELECT 
                dl.id AS id_dictamen,
                DATE(dl.fecha_gestion) AS fecha_registro,
                TIME(dl.hora_gestion) AS hora_registro,
                dl.id_credito,
                p.nombres,
                p.segundo_nombre,
                p.apellidop,
                p.apellidom,
                CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_cliente,
                tc.nombre AS tipo_contacto,
                rc.nombre AS resultado_contacto,
                cd.nombre AS dictamen,
                cmnp.descripcion AS motivo_no_pago,
                cmnpt.nombre AS tipo_motivo_no_pago,
                cp.nombre AS plataforma,
                dl.fuente_ingresos,
                dl.comentarios,
                dl.agente AS usuario_id,
                CONCAT_WS(' ', u.nombres, u.segundo_nombre, u.apellidop, u.apellidom) AS agente
            FROM __SPARTA_SECRET_REDACTED__.dictamen_llamada dl
            LEFT JOIN persona p ON dl.id_credito = p.id
            LEFT JOIN persona u ON dl.agente = u.id
            LEFT JOIN cat_tipo_contacto tc ON dl.tipo_contacto_id = tc.id
            LEFT JOIN cat_resultado_contacto rc ON dl.resultado_contacto_id = rc.id
            LEFT JOIN cat_dictamen cd ON dl.dictamen_id = cd.id
            LEFT JOIN cat_motivo_no_pago cmnp ON dl.motivo_no_pago_id = cmnp.id
            LEFT JOIN cat_motivo_no_pago_tipo cmnpt ON cmnp.tipo_id = cmnpt.id
            LEFT JOIN cat_plataforma cp ON dl.plataforma_id = cp.id
            
            WHERE DATE(dl.fecha_gestion) BETWEEN :fecha_inicio AND :fecha_fin
            ORDER BY dl.fecha_gestion DESC, dl.hora_gestion DESC
        ";
        
        $db = new Database();
        $params = [
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
        
        return $db->queryAll($query, $params);
        
    } catch (\Exception $e) {
        error_log("Error al obtener reportes para descarga: " . $e->getMessage());
        return [];
    }
}

      public static function getGastosCobranza($idCredito)
    {
        $query = "
        SELECT
            id_gastos_cobranza,
            SEMANA,
            periodo_inicio,
            periodo_fin,
            monto_valor,
            cuota,
            parcialidad,
            Fecha_primer_vencimiento
        FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
        WHERE Id_credito = :id_credito
          AND (condonado IS NULL OR condonado = 0)
        ORDER BY periodo_inicio ASC
    ";

        try {
            $db = new DatabaseSegundometro();
            $r = $db->queryAll($query, ['id_credito' => $idCredito]);

            // Formateo para el frontend
            $datos = array_map(function ($row) {
                
                // 🔴 NUEVA PRIORIDAD 1️⃣: Calcular por fechas (correcto)
                $parcialidadCalculada = null;
                
                if (!empty($row['Fecha_primer_vencimiento']) && !empty($row['periodo_inicio'])) {
                    $fechaInicio = strtotime($row['Fecha_primer_vencimiento']);
                    $periodoInicio = strtotime($row['periodo_inicio']);
                    
                    if ($fechaInicio && $periodoInicio && $periodoInicio >= $fechaInicio) {
                        $diasTranscurridos = ($periodoInicio - $fechaInicio) / 86400; // 86400 = 24*60*60
                        $parcialidadCalculada = floor($diasTranscurridos / 7) + 1; // +1 porque la primera semana es 1
                    }
                }
                
                // 🔴 PRIORIDAD 2️⃣: Si falla el cálculo por fechas, intentar con SEMANA (fallback)
                if ($parcialidadCalculada === null && preg_match('/Semana (\d+)-/', $row['SEMANA'], $matches)) {
                    $numeroSemana = (int)$matches[1];
                    $parcialidadCalculada = $numeroSemana - 3; // Offset mágico (no confiable, solo fallback)
                }

                return [
                    'id_gasto' => (int)$row['id_gastos_cobranza'],
                    'semana'   => $row['SEMANA'],
                    'periodo' => date('d/m/Y', strtotime($row['periodo_inicio'])) .
                        ' - ' .
                        date('d/m/Y', strtotime($row['periodo_fin'])),
                    'monto'   => (float)$row['monto_valor'],
                    'cuota'   => (float)$row['cuota'],
                    // Si ambos cálculos fallan, usar el campo de BD como último recurso
                    'parcialidad' => $parcialidadCalculada ?? ($row['parcialidad'] ?? null)
                ];
            }, $r);

            return self::resultado(true, 'Gastos de cobranza', $datos);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al consultar gastos de cobranza',
                [],
                $e->getMessage()
            );
        }
    }

    public static function insertCondonacionCobranza($data)
    {
        // 🔹 Escapamos valores
        $id_credito = addslashes($data['id_credito']);
        $comentario = addslashes($data['comentario']);
        $total      = addslashes($data['total']);
        $id_usuario = addslashes($data['usuario_id'] ?? 0);
        $usuario    = addslashes($data['usuario'] ?? 'Sistema');

        // Fecha y hora de CDMX para created_at
        $tz = new \DateTimeZone('America/Mexico_City');
        $fechaRegistro = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
        $fechaRegistro = addslashes($fechaRegistro);

        try {
            $db = new DatabaseSegundometro();

            // 1️⃣ Insertar ticket (con id_usuario, usuario y created_at en hora CDMX)
            $db->queryOne("
            INSERT INTO condonaciones_cobranza
            (id_condonacion, id_credito, comentario, id_usuario, usuario, total_condonado, created_at)
            VALUES (
                DEFAULT,
                $id_credito,
                '$comentario',
                $id_usuario,
                '$usuario',
                $total,
                '$fechaRegistro'
            )
        ");

            // 2️⃣ Obtener ID insertado
            $result = $db->queryOne("
            SELECT LAST_INSERT_ID() AS id_condonacion
        ");


            return self::resultado(true, 'Ticket creado', $result);

        } catch (\Exception $e) {

            return self::resultado(
                false,
                'Error al crear ticket',
                null,
                $e->getMessage()
            );
        }
    }
    public static function insertCondonacionCobranzaDetalle($data)
    {
        $id_condonacion       = addslashes($data['id_condonacion']);
        $id_gastos_cobranza   = addslashes($data['id_gastos_cobranza']);
        $monto                = addslashes($data['monto']);

        try {
            $db = new DatabaseSegundometro();

            $db->queryOne("
            INSERT INTO condonaciones_cobranza_detalle
            (id, id_condonacion, id_gastos_cobranza, monto)
            VALUES (
                DEFAULT,
                $id_condonacion,
                $id_gastos_cobranza,
                $monto
            )
        ");

            return self::resultado(true, 'Detalle agregado', null);

        } catch (\Exception $e) {

            return self::resultado(
                false,
                'Error al registrar detalle',
                null,
                $e->getMessage()
            );
        }
    }

    public static function marcarGastoCondonado($idGasto)
    {
        try {
            $db = new DatabaseSegundometro();

            $idGasto = (int) $idGasto;

            $db->CRUD(
                "UPDATE gastos_cobranza SET condonado = 1, fecha_condonacion = CONVERT_TZ(NOW(), '+00:00', 'America/Mexico_City') WHERE id_gastos_cobranza = :id_gasto",
                ['id_gasto' => $idGasto]
            );

            return self::resultado(true, 'Gasto marcado como condonado');

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al marcar gasto como condonado',
                null,
                $e->getMessage()
            );
        }
    }

}