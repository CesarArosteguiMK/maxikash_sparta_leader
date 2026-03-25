<?php

namespace Models;

use Core\DatabaseSegundometro;
use Core\Model;
use Core\Database;
use Core\DatabaseAWS;
use Core\DatabaseMaxiGuat;

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
     * Obtener documento por tipo genérico (FACTURA, VALIDACIONES, etc.) desde oferta_documentos.
     */
    public static function obtenerDocumentoPorTipo($idOferta, $tipoBD)
    {
        if (!$idOferta || !is_numeric($idOferta) || !$tipoBD) {
            return self::resultado(false, 'Parámetros inválidos');
        }
        $tipoBD = preg_replace('/[^A-Za-z0-9_]/', '', $tipoBD);
        if ($tipoBD === '') {
            return self::resultado(false, 'Tipo inválido');
        }
        try {
            $db = new DatabaseAWS('__SPARTA_SECRET_REDACTED__');
            $qry = "
                SELECT nombre_archivo
                FROM oferta_documentos
                WHERE tipo_documento = :tipo AND fk_oferta = :id
                LIMIT 1
            ";
            $r = $db->queryAll($qry, ['tipo' => $tipoBD, 'id' => $idOferta]);
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

    /**
     * Buscar reporte dictamen por rango de fechas y opcionalmente por usuario (agente).
     * Usado por el controlador EstadoCuenta::buscarReporteDictamen (POST desde dictamen_llamadas).
     */
    public static function buscarReporteDictamen($usuarioId, $fechaInicio, $fechaFin)
    {
        try {
            $db = new Database();
            $params = [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ];
            $where = " WHERE DATE(dl.fecha_gestion) BETWEEN :fecha_inicio AND :fecha_fin";
            if (!empty($usuarioId)) {
                $where .= " AND dl.agente = :usuario_id";
                $params['usuario_id'] = $usuarioId;
            }
            $query = "
            SELECT
                dl.id AS id,
                DATE(dl.fecha_gestion) AS fecha_registro,
                TIME(dl.hora_gestion) AS hora_registro,
                dl.id_credito,
                CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_cliente,
                tc.nombre AS tipo_contacto,
                rc.nombre AS resultado_contacto,
                cd.nombre AS dictamen,
                cmnp.descripcion AS motivo_no_pago,
                cmnpt.nombre AS tipo_motivo_no_pago,
                cp.nombre AS plataforma,
                dl.fuente_ingresos,
                dl.comentarios
            FROM __SPARTA_SECRET_REDACTED__.dictamen_llamada dl
            LEFT JOIN persona p ON dl.id_credito = p.id
            LEFT JOIN cat_tipo_contacto tc ON dl.tipo_contacto_id = tc.id
            LEFT JOIN cat_resultado_contacto rc ON dl.resultado_contacto_id = rc.id
            LEFT JOIN cat_dictamen cd ON dl.dictamen_id = cd.id
            LEFT JOIN cat_motivo_no_pago cmnp ON dl.motivo_no_pago_id = cmnp.id
            LEFT JOIN cat_motivo_no_pago_tipo cmnpt ON cmnp.tipo_id = cmnpt.id
            LEFT JOIN cat_plataforma cp ON dl.plataforma_id = cp.id
            " . $where . "
            ORDER BY dl.fecha_gestion DESC, dl.hora_gestion DESC
            ";
            $resultados = $db->queryAll($query, $params);
            return self::resultado(true, 'Reportes obtenidos', $resultados ?: []);
        } catch (\Exception $e) {
            error_log("Error en buscarReporteDictamen: " . $e->getMessage());
            return self::resultado(false, 'Error al obtener reportes', [], $e->getMessage());
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
        COALESCE(condonacion_parcial_monto, 0) AS condonacion_parcial_monto,
        condonacion_parcial_motivo,
        cuota,
        parcialidad,
        Fecha_primer_vencimiento,
        COALESCE(estatus_pago, 0)          AS estatus_pago,
        COALESCE(monto_parcial_pagado, 0)  AS monto_parcial_pagado
    FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
    WHERE Id_credito = :id_credito
      AND (condonado IS NULL OR condonado = 0)
      AND (estatus_pago IS NULL OR estatus_pago != 2)
    ORDER BY periodo_inicio ASC
    ";
// NOTA: dsb-mega-reporte`.gastos_cobranza_backup_despacho_20260324 es una tabla temporal
// SOLO copiar y pegar esto para reemplazar en la query: dsb-mega-reporte`.gastos_cobranza

    try {
        $db = new DatabaseSegundometro();
        $r = $db->queryAll($query, ['id_credito' => $idCredito]);
    } catch (\Exception $e) {
        $queryFallback = "
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
            $r = $db->queryAll($queryFallback, ['id_credito' => $idCredito]);
            foreach ($r as $i => $row) {
                $r[$i]['condonacion_parcial_monto']  = 0;
                $r[$i]['condonacion_parcial_motivo'] = '';
                $r[$i]['estatus_pago'] = 0; // ← nuevo
                $r[$i]['monto_parcial_pagado'] = 0;
            }
        } catch (\Exception $e2) {
            return self::resultado(
                false,
                'Error al consultar gastos de cobranza',
                [],
                $e2->getMessage()
            );
        }
    }

    try {
        $datos = array_map(function ($row) {

            // PRIORIDAD 1: Calcular por fechas (correcto)
            $parcialidadCalculada = null;

            if (!empty($row['Fecha_primer_vencimiento']) && !empty($row['periodo_inicio'])) {
                $fechaInicio   = strtotime($row['Fecha_primer_vencimiento']);
                $periodoInicio = strtotime($row['periodo_inicio']);

                if ($fechaInicio && $periodoInicio && $periodoInicio >= $fechaInicio) {
                    $diasTranscurridos    = ($periodoInicio - $fechaInicio) / 86400;
                    $parcialidadCalculada = floor($diasTranscurridos / 7) + 1;
                }
            }

            // PRIORIDAD 2: Fallback con SEMANA
            if ($parcialidadCalculada === null && preg_match('/Semana (\d+)-/', $row['SEMANA'], $matches)) {
                $numeroSemana         = (int)$matches[1];
                $parcialidadCalculada = $numeroSemana - 3;
            }

            $montoValor    = (float)$row['monto_valor'];
            $parcialMonto  = (float)($row['condonacion_parcial_monto'] ?? 0);
            $montoEfectivo = round($montoValor - $parcialMonto, 2);

            return [
                'id_gasto'                   => (int)$row['id_gastos_cobranza'],
                'semana'                     => $row['SEMANA'],
                'periodo'                    => date('d/m/Y', strtotime($row['periodo_inicio'])) .
                                               ' - ' .
                                               date('d/m/Y', strtotime($row['periodo_fin'])),
                'monto'                      => $montoEfectivo,
                'monto_original'             => $montoValor,
                'condonacion_parcial_monto'  => $parcialMonto,
                'condonacion_parcial_motivo' => $row['condonacion_parcial_motivo'] ?? '',
                'cuota'                      => (float)$row['cuota'],
                'parcialidad'                => $parcialidadCalculada ?? ($row['parcialidad'] ?? null),
                'estatus_pago'               => (int)($row['estatus_pago'] ?? 0) // ← nuevo
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

public static function getGastosTodosConEstatus($idCredito)
{
    $query = "
    SELECT id_gastos_cobranza, monto_valor, estatus_pago
    FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
    WHERE Id_credito = :id_credito
      AND (condonado IS NULL OR condonado = 0)
    ORDER BY periodo_inicio ASC
    ";

    try {
        $db = new DatabaseSegundometro();
        $r = $db->queryAll($query, ['id_credito' => $idCredito]);
        return self::resultado(true, 'OK', $r ?: []);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error', [], $e->getMessage());
    }
}

    /**
     * Valor de columna en fila PDO (FETCH_ASSOC) ignorando mayúsculas en el nombre de la clave.
     */
    private static function valorColumnaFila(array $row, string $nombreColumna)
    {
        if (array_key_exists($nombreColumna, $row)) {
            return $row[$nombreColumna];
        }
        foreach ($row as $clave => $valor) {
            if (strcasecmp((string) $clave, $nombreColumna) === 0) {
                return $valor;
            }
        }

        return null;
    }

    /**
     * Crédito asignado a despacho externo (tabla __SPARTA_SECRET_REDACTED__.asigna_creditos_despacho, estatus activo).
     */
    public static function creditoTieneAsignacionDespachoActiva($idCredito): bool
    {
        $id = (int) $idCredito;
        if ($id <= 0) {
            return false;
        }
        try {
            $db = new Database();
            // Activo: no explícitamente dado de baja (0). Incluye '1', 1, NULL u otros valores operativos.
            $sql = "SELECT 1 AS ok FROM asigna_creditos_despacho
                 WHERE id_credito = :id
                   AND (estatus IS NULL OR estatus NOT IN ('0', 0))
                 LIMIT 1";
            try {
                $row = $db->queryOne($sql, ['id' => $id]);
            } catch (\Exception $e1) {
                $row = $db->queryOne(
                    str_replace(
                        'FROM asigna_creditos_despacho',
                        'FROM __SPARTA_SECRET_REDACTED__.asigna_creditos_despacho',
                        $sql
                    ),
                    ['id' => $id]
                );
            }

            return $row !== null && $row !== false;
        } catch (\Exception $e) {
            error_log('creditoTieneAsignacionDespachoActiva: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Último valor de celula en gastos_cobranza (__SPARTA_SECRET_REDACTED__) para el crédito.
     */
    public static function obtenerCelulaUltimaGastoCobranza($idCredito): ?int
    {
        $id = (int) $idCredito;
        if ($id <= 0) {
            return null;
        }
        try {
            $db = new DatabaseSegundometro();
            $sqlCelula = "SELECT `celula` AS celula FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
                 WHERE `Id_credito` = :id
                 ORDER BY id_gastos_cobranza DESC
                 LIMIT 1";
            try {
                $row = $db->queryOne($sqlCelula, ['id' => $id]);
            } catch (\Exception $eCol) {
                $row = $db->queryOne(
                    str_replace('WHERE `Id_credito` = :id', 'WHERE id_credito = :id', $sqlCelula),
                    ['id' => $id]
                );
            }
            if ($row === null) {
                return null;
            }
            $raw = self::valorColumnaFila($row, 'celula');
            if ($raw === null || $raw === '') {
                return null;
            }

            return (int) $raw;
        } catch (\Exception $e) {
            error_log('obtenerCelulaUltimaGastoCobranza: ' . $e->getMessage());

            return null;
        }
    }

    public static function etiquetaCelulaGestionExterna(?int $celula): string
    {
        switch ($celula) {
            case 1:
                return 'Despachos de cobranza';
            case 2:
                return 'Gestión Call Center';
            case 3:
                return 'Cobranza Campo';
            case 4:
                return 'Gestión Call Center Sin Convenio';
            default:
                return $celula !== null ? ('Célula ' . $celula) : '';
        }
    }

    /**
     * Gestión externa para UI y bloqueo: asignación en despacho O célula indicada en gastos_cobranza.
     */
    public static function creditoEnGestionExternaRestringida($idCredito): bool
    {
        if (self::creditoTieneAsignacionDespachoActiva($idCredito)) {
            return true;
        }
        $c = self::obtenerCelulaUltimaGastoCobranza($idCredito);

        return $c !== null && (int) $c > 0;
    }

    /**
     * @return array{activa: bool, celula: ?int, etiqueta_celula: string}
     */
    public static function getDatosGestionExternaCredito($idCredito): array
    {
        $celula = self::obtenerCelulaUltimaGastoCobranza($idCredito);
        $enDespacho = self::creditoTieneAsignacionDespachoActiva($idCredito);
        $activa = $enDespacho || ($celula !== null && (int) $celula > 0);
        $etiqueta = '';
        if ($activa) {
            $etiqueta = self::etiquetaCelulaGestionExterna($celula);
        }

        return [
            'activa' => $activa,
            'celula' => $celula,
            'etiqueta_celula' => $etiqueta,
        ];
    }

    public static function mensajeBloqueoGestionExternaGastos(): string
    {
        return 'Esta opción no está disponible. El crédito está siendo gestionado de forma externa.';
    }

    public static function gastosCobranzaBloqueadosPorGestionExterna($idCredito): bool
    {
        return self::creditoEnGestionExternaRestringida($idCredito);
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
        $db->CRUD(
            "UPDATE `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
             SET condonado = 1,
                 estatus_pago = 0, -- ✅ Cambiado de 2 a 0 (Condonar no es pagar)
                 fecha_condonacion = NOW()
             WHERE id_gastos_cobranza = :id",
            ['id' => $idGasto]
        );
        return self::resultado(true, 'Gasto marcado como condonado correctamente');
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al condonar', null, $e->getMessage());
    }
}


    /**
     * Valida que el motivo de condonación parcial tenga al menos 100 caracteres y no sea texto sin sentido
     * (evita rellenado con repeticiones, tecleo aleatorio, etc.).
     *
     * @param string $motivo
     * @return array [ true ] o [ false, 'mensaje_error' ]
     */
    public static function validarMotivoCondonacionParcial($motivo)
    {
        $motivo = trim((string) $motivo);
        if (strlen($motivo) < 100) {
            return [false, 'El motivo debe tener al menos 100 caracteres.'];
        }
        // Evitar mismo carácter repetido (ej. xxx, aaaa)
        if (preg_match('/(.)\1{2,}/u', $motivo)) {
            return [false, 'El motivo no puede contener la misma letra o carácter repetido muchas veces.'];
        }
        // Proporción de caracteres únicos: si es muy baja, es probablemente relleno (ej. xxxxx, aaaaa)
        // En español normal se repiten muchas letras (e, a, o, r), por eso usamos umbral bajo (0.15)
        $len = mb_strlen($motivo);
        $unicos = count(array_unique(preg_split('//u', $motivo, -1, PREG_SPLIT_NO_EMPTY)));
        if ($len > 0 && ($unicos / $len) < 0.15) {
            return [false, 'El motivo debe describir con claridad la promoción o razón de la condonación (evita rellenar con caracteres repetidos).'];
        }
        // Mínimo de palabras (promoción a detalle)
        $palabras = preg_split('/\s+/u', $motivo, -1, PREG_SPLIT_NO_EMPTY);
        if (count($palabras) < 8) {
            return [false, 'El motivo debe incluir al menos 8 palabras describiendo la promoción o razón de la condonación.'];
        }
        // Rechazar patrones típicos de teclado (qwerty, asdf, 1234, etc.)
        $patrones = ['/qwerty/ui', '/asdfgh/ui', '/zxcvbn/ui', '/12345678/ui', '/abcdefgh/ui'];
        foreach ($patrones as $pat) {
            if (preg_match($pat, $motivo)) {
                return [false, 'El motivo debe describir la promoción o razón real de la condonación, no secuencias de teclado.'];
            }
        }
        // Palabras coherentes: en español toda palabra tiene al menos una vocal. Rechazar si hay palabras largas sin vocales (ej. asjbasjfb)
        $vocales = 'aáeéiíoóuúAÁEÉIÍOÓUÚ';
        $palabrasLargasSinVocal = 0;
        foreach ($palabras as $p) {
            $pLen = mb_strlen($p);
            if ($pLen >= 4) {
                $tieneVocal = (bool) preg_match('/[' . $vocales . ']/u', $p);
                if (!$tieneVocal) {
                    $palabrasLargasSinVocal++;
                }
            }
        }
        if ($palabrasLargasSinVocal >= 2) {
            return [false, 'El motivo debe usar palabras con sentido (evita cadenas sin vocales como "asjbasjfb"). Describe la promoción o razón de la condonación.'];
        }
        // Proporción de vocales en el texto: en español suele ser ~45%. Si es muy baja, huele a tecleo aleatorio
        $numVocales = preg_match_all('/[' . $vocales . ']/u', $motivo);
        if ($len > 0 && ($numVocales / $len) < 0.20) {
            return [false, 'El motivo debe describir la promoción o razón con palabras comprensibles (evita cadenas sin sentido).'];
        }
        return [true];
    }

    /**
     * Guarda la condonación parcial de un gasto de cobranza (monto condonado parcialmente + motivo).
     *
     * @param int    $id_gastos_cobranza
     * @param float  $monto_parcial
     * @param string $motivo
     * @return array resultado()
     */
    public static function updateCondonacionParcialGasto($id_gastos_cobranza, $monto_parcial, $motivo)
{
    $validacion = self::validarMotivoCondonacionParcial($motivo);
    if (!$validacion[0]) {
        return self::resultado(false, $validacion[1]);
    }

    $monto_parcial = round((float) $monto_parcial, 2);
    if ($monto_parcial <= 0) {
        return self::resultado(false, 'El monto a condonar parcialmente debe ser mayor a cero.');
    }

    $id_gastos_cobranza = (int) $id_gastos_cobranza;
    if ($id_gastos_cobranza <= 0) {
        return self::resultado(false, 'ID de gasto inválido.');
    }

    try {
        $db = new DatabaseSegundometro();

        $row = $db->queryOne(
            "SELECT monto_valor, Id_credito FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
             WHERE id_gastos_cobranza = :id",
            ['id' => $id_gastos_cobranza]
        );

        if (!$row) {
            return self::resultado(false, 'No se encontró el gasto de cobranza.');
        }

        $idCreditoGasto = (int) (self::valorColumnaFila($row, 'Id_credito') ?? self::valorColumnaFila($row, 'id_credito') ?? 0);
        if (self::creditoEnGestionExternaRestringida($idCreditoGasto)) {
            return self::resultado(false, self::mensajeBloqueoGestionExternaGastos());
        }

        $montoMax = (float) (self::valorColumnaFila($row, 'monto_valor') ?? 0);
        if ($monto_parcial >= $montoMax) {
            return self::resultado(false, 'El monto parcial no puede ser mayor o igual al monto total del gasto. Para condonación total usa el botón Condonar.');
        }

        $db->CRUD(
            "UPDATE `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
             SET
                condonacion_parcial_monto  = :monto,
                condonacion_parcial_motivo = :motivo,
                estatus_pago               = 1
             WHERE id_gastos_cobranza = :id",
            [
                'monto'  => $monto_parcial,
                'motivo' => $motivo,
                'id'     => $id_gastos_cobranza
            ]
        );

        return self::resultado(true, 'Condonación parcial guardada correctamente.');

    } catch (\Exception $e) {
        return self::resultado(
            false,
            'Error al guardar la condonación parcial',
            null,
            $e->getMessage()
        );
    }
}

    /**
     * Obtiene el país asociado a un crédito mediante id_persona.
     * Importante: el id_credito corresponde al id de persona en la tabla persona.
     *
     * @param int $idCreditoID del crédito (id de persona)
     * @return array Datos del país: id_pais, nombre_pais, codigo_iso, pais_activo
     */
    public static function getPaisCredito($idCredito)
    {
        error_log("[getPaisCredito] Buscando país para crédito ID: " . $idCredito);
        $qry = "
            SELECT
                p.id_pais,
                COALESCE(pais.nombre, 'México') AS nombre_pais,
                COALESCE(pais.codigo_iso, 'mx') AS codigo_iso,
                COALESCE(pais.activo, 1) AS pais_activo
            FROM persona p
            LEFT JOIN paises pais ON pais.id = p.id_pais
            WHERE p.id = :id_credito
            LIMIT 1
        ";
        $val = ['id_credito' => (int) $idCredito];

        try {
            $db = new Database();
            $r = $db->queryOne($qry, $val);
            error_log("[getPaisCredito] Resultado query: " . ($r ? json_encode($r) : 'NULL'));

            // Si no se encuentra el crédito o no tiene país asignado, default a México
            if (!$r || empty($r['nombre_pais'])) {
                error_log("[getPaisCredito] No encontrado o NULL, retornando México por defecto");
                return [
                    'id_pais' => null,
                    'nombre_pais' => 'México',
                    'codigo_iso' => 'mx',
                    'pais_activo' => 1
                ];
            }

            error_log("[getPaisCredito] País encontrado: " . $r['nombre_pais'] . " (" . $r['codigo_iso'] . ")");
            return $r;
        } catch (\Exception $e) {
            // En caso de error, retornar México por defecto
            error_log("[EstadoCuenta::getPaisCredito] Error: " . $e->getMessage());
            return [
                'id_pais' => null,
                'nombre_pais' => 'México',
                'codigo_iso' => 'mx',
                'pais_activo' => 1
            ];
        }
    }

    /**
     * Consultar datos de crédito/cliente desde Guatemala (registro_croop)
     * Busca por pkey_credito o pkey_cliente
     *
     * @param int $idCredito ID del crédito (pkey_credito)
     * @param int|null $idCliente ID del cliente (pkey_cliente) - opcional
     * @return array Resultado con datos parseados
     */
    public static function getDatosGuatemala($idCredito = null, $idCliente = null)
    {
        error_log("[getDatosGuatemala] INICIO - idCredito=$idCredito, idCliente=$idCliente");
        try {
            error_log("[getDatosGuatemala] Intentando crear conexión DatabaseMaxiGuat...");
            $db = new DatabaseMaxiGuat();
            error_log("[getDatosGuatemala] Conexión creada exitosamente");

            // Construir query dinámicamente según parámetros
            if ($idCredito) {
                $qry = "
                    SELECT
                        id_croop,
                        fk_oferta,
                        fk_persona,
                        pkey_cliente,
                        pkey_credito,
                        request_cliente,
                        response_cliente,
                        request_credito,
                        response_credito,
                        request_adicional,
                        response_adicional,
                        fecha_registro_cliente,
                        fecha_registro_credito,
                        fecha_registro_adicional,
                        estatus
                    FROM registro_croop
                    WHERE pkey_credito = :id
                    LIMIT 1
                ";
                $val = ['id' => $idCredito];
                error_log("[getDatosGuatemala] Query por CREDITO: " . json_encode($val));
            } elseif ($idCliente) {
                $qry = "
                    SELECT
                        id_croop,
                        fk_oferta,
                        fk_persona,
                        pkey_cliente,
                        pkey_credito,
                        request_cliente,
                        response_cliente,
                        request_credito,
                        response_credito,
                        request_adicional,
                        response_adicional,
                        fecha_registro_cliente,
                        fecha_registro_credito,
                        fecha_registro_adicional,
                        estatus
                    FROM registro_croop
                    WHERE pkey_cliente = :id
                    LIMIT 1
                ";
                $val = ['id' => $idCliente];
                error_log("[getDatosGuatemala] Query por CLIENTE: " . json_encode($val));
            } else {
                error_log("[getDatosGuatemala] ERROR: No se proporcionó ID");
                return self::resultado(false, 'Se requiere ID de crédito o cliente');
            }

            error_log("[getDatosGuatemala] Ejecutando queryOne...");
            $registro = $db->queryOne($qry, $val);
            error_log("[getDatosGuatemala] Resultado query: " . ($registro ? 'ENCONTRADO' : 'NULL'));

            if (!$registro) {
                error_log("[getDatosGuatemala] No se encontró registro para ID: " . json_encode($val));
                return self::resultado(false, 'No se encontró registro en Guatemala');
            }

            error_log("[getDatosGuatemala] Registro encontrado, pkey_credito=" . ($registro['pkey_credito'] ?? 'null'));

            // Parsear JSONs
            $requestCliente = json_decode($registro['request_cliente'] ?? '{}', true);
            $requestAdicional = json_decode($registro['request_adicional'] ?? '[]', true);
            $responseCliente = json_decode($registro['response_cliente'] ?? '{}', true);

            // Construir estructura de datos
            $datosCliente = [
                'idCredito' => $registro['pkey_credito'],
                'idCliente' => $registro['pkey_cliente'],
                'nombres' => $requestCliente['Nombre'] ?? '',
                'apellidoPaterno' => $requestCliente['APP'] ?? '',
                'apellidoMaterno' => $requestCliente['APM'] ?? '',
                'nombreCliente' => trim(
                    ($requestCliente['Nombre'] ?? '') . ' ' .
                    ($requestCliente['APP'] ?? '') . ' ' .
                    ($requestCliente['APM'] ?? '')
                ),
                'email' => $requestCliente['Email'] ?? '',
                'celular' => $requestCliente['Celular'] ?? '',
                'fechaNacimiento' => $requestCliente['Fecha_Nac'] ?? '',
                'ciudad' => $requestCliente['Ciudad'] ?? '',
                'calleNumero' => $requestCliente['Calle_Numero'] ?? '',
                'codigoPostal' => $requestCliente['Codigo_Postal'] ?? '',
                'nacionalidad' => $requestCliente['FK_Nacionalidad'] ?? '',
                'paisResidencia' => $requestCliente['FK_PaisResidencia'] ?? '',
                'genero' => $requestCliente['FK_Genero'] ?? '',
                'rfc' => $requestCliente['RFC'] ?? '',
                'curp' => $requestCliente['CURP'] ?? '',
            ];

            // Parsear datos adicionales (array de objetos)
            $datosAdicionales = [];
            if (is_array($requestAdicional)) {
                foreach ($requestAdicional as $item) {
                    if (isset($item['nombre']) && isset($item['valor'])) {
                        $datosAdicionales[$item['nombre']] = $item['valor'];
                    }
                }
            }

            error_log("[getDatosGuatemala] Datos parseados exitosamente, nombreCliente=" . $datosCliente['nombreCliente']);
            return self::resultado(true, 'Datos encontrados', [
                'registro' => $registro,
                'datosCliente' => $datosCliente,
                'datosAdicionales' => $datosAdicionales,
                'requestCliente' => $requestCliente,
                'requestAdicional' => $requestAdicional,
                'responseCliente' => $responseCliente
            ]);

        } catch (\Exception $e) {
            error_log("[getDatosGuatemala] EXCEPTION: " . $e->getMessage());
            error_log("[getDatosGuatemala] Stack trace: " . $e->getTraceAsString());
            return self::resultado(false, 'Error al consultar datos de Guatemala', null, $e->getMessage());
        }
    }


/**
 * @deprecated Método huérfano — no se llama en producción.
 *             Contiene bug de scope ($row no existe en este contexto).
 *             Pendiente eliminar en la próxima refactorización.
 *             Usar actualizarEstatusPagoGastoConMonto() en su lugar.
 */
public static function actualizarEstatusPagoGasto($idGasto, $estatusPago)
{
    // ⚠️ BUG CONOCIDO: $row no existe en este scope — método sin uso activo.
    // No se corrige aquí para no alterar comportamiento; se elimina en refactorización.
    return self::resultado(false, 'Método deprecado — usar actualizarEstatusPagoGastoConMonto()');
}

public static function getHistorialGastosCobranza($idCredito)
{
    try {
        $db = new DatabaseSegundometro();
        // 1. La consulta ahora incluye OR condonado = 1 para traer los perdonados
        $query = "
            SELECT
                id_gastos_cobranza,
                SEMANA,
                periodo_inicio,
                periodo_fin,
                monto_valor,
                condonacion_parcial_monto,
                condonado,
                estatus_pago,
                fecha_condonacion,
                fecha_pago,
                created_at
            FROM `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
            WHERE Id_credito = :id_credito
              AND (estatus_pago = 2 OR condonado = 1)
            ORDER BY periodo_inicio DESC
        ";

        $r = $db->queryAll($query, ['id_credito' => $idCredito]);

        $datos = array_map(function($row) {
            $montoOriginal = (float)$row['monto_valor'];
            $condParcial   = (float)($row['condonacion_parcial_monto'] ?? 0);

            return [
                'id_gasto'                  => (int)$row['id_gastos_cobranza'],
                'semana'                    => $row['SEMANA'],
                'periodo'                   => date('d/m/Y', strtotime($row['periodo_inicio'])) . ' - ' . date('d/m/Y', strtotime($row['periodo_fin'])),
                'monto_original'            => $montoOriginal,
                'condonacion_parcial_monto' => $condParcial,
                // Si es condonación total, el monto condonado es el original, si no, lo que se restó
                'monto_condonado'           => $row['condonado'] == 1 ? $montoOriginal : $condParcial,
                'fecha_condonacion'         => !empty($row['fecha_condonacion'])
                                                ? date('d/m/Y', strtotime($row['fecha_condonacion']))
                                                : (!empty($row['created_at']) ? date('d/m/Y', strtotime($row['created_at'])) : '—'),
                'fecha_pago'                => !empty($row['fecha_pago'])
                                                ? date('d/m/Y', strtotime($row['fecha_pago']))
                                                : '—',
                'estatus'                   => (int)$row['estatus_pago'],
                'condonado'                 => (int)$row['condonado']
            ];
        }, $r ?: []);

        return self::resultado(true, 'Historial recuperado', $datos);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener historial', null, $e->getMessage());
    }
}

/**
 * Actualiza estatus_pago y monto_parcial_pagado de un gasto de cobranza.
 *
 * REGLAS DE NEGOCIO:
 *   - estatus_pago = 1 → pago parcial, cliente sigue debiendo
 *   - estatus_pago = 2 → pagado total
 *   - condonacion_parcial_monto NO se toca aquí — es territorio exclusivo del gestor
 *   - $condonacionParcial se acepta por firma pero SOLO lo usa el gestor desde el front
 *
 * @param int   $idGasto
 * @param int   $estatusPago       1 = parcial, 2 = total
 * @param float $montoPagado       Monto que el cliente pagó
 * @param float $condonacionParcial Reservado para gestor — el cron siempre pasa 0
 */
public static function actualizarEstatusPagoGastoConMonto($idGasto, $estatusPago, $montoPagado, $condonacionParcial = 0, $fechaPago = null)
{
    try {
        $db = new DatabaseSegundometro();

        // $fechaPago: fecha real del pago (fechaMovimiento de la API S2).
        // Si no se recibe, se usa la fecha actual como fallback.
        $fechaPago = $fechaPago ?? date('Y-m-d');

        // Si el gestor envía una condonación (desde el front), se registra junto con fecha_condonacion.
        // El cron y el controller de cruce automático siempre pasan condonacionParcial = 0.
        if ((float) $condonacionParcial > 0) {
            // Gestor desde el front — registra condonación, fecha_condonacion y fecha_pago
            $db->CRUD(
                "UPDATE `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
                 SET estatus_pago             = :estatus_pago,
                     monto_parcial_pagado      = :monto_pagado,
                     condonacion_parcial_monto = :condonacion_parcial,
                     fecha_condonacion         = NOW(),
                     fecha_pago               = :fecha_pago
                 WHERE id_gastos_cobranza      = :id_gasto",
                [
                    'estatus_pago'        => (int) $estatusPago,
                    'monto_pagado'        => round((float) $montoPagado, 2),
                    'condonacion_parcial' => round((float) $condonacionParcial, 2),
                    'fecha_pago'          => $fechaPago ?? date('Y-m-d'),
                    'id_gasto'            => (int) $idGasto
                ]
            );
        } else {
            // Cruce automático (cron / controller) — toca estatus, monto y fecha_pago
            // condonacion_parcial_monto NO se toca
            $db->CRUD(
                "UPDATE `__SPARTA_SECRET_REDACTED__`.gastos_cobranza
                 SET estatus_pago         = :estatus_pago,
                     monto_parcial_pagado  = :monto_pagado,
                     fecha_pago           = :fecha_pago
                 WHERE id_gastos_cobranza  = :id_gasto",
                [
                    'estatus_pago' => (int) $estatusPago,
                    'monto_pagado' => round((float) $montoPagado, 2),
                    'fecha_pago'   => $fechaPago ?? date('Y-m-d'),
                    'id_gasto'     => (int) $idGasto
                ]
            );
        }

        return self::resultado(true, 'Estatus y montos actualizados');
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al actualizar gasto', null, $e->getMessage());
    }
}



}
