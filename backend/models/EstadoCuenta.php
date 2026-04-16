<?php

namespace Models;

use Core\DatabaseSegundometro;
use Core\Model;
use Core\Database;
use Core\DatabaseAWS;
use Core\DatabaseMaxiGuat;
use Models\Empresa as EmpresasDAO;

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

    /**
     * Opciones "Llamada a" sin consultar maxi: usa la fila de referencias ya cargada en la vista + celular del API + historial local.
     *
     * @param array<string, mixed> $row Fila de getConsultaReferenciasEstadoCuenta (datos[0]).
     * @return array<int, array{value:string, label:string, telefono:string, nombre:string, parentesco:string}>
     */
    public static function construirOpcionesContactoDictamenParaPreload($idCredito, array $row, $celularTitular)
    {
        $id = (int)$idCredito;
        if ($id <= 0) {
            return [];
        }
        $telTitular = trim((string)$celularTitular);
        $nombreCliente = trim((string)($row['nombre_completo'] ?? ''));

        $opciones = [];
        $opciones[] = [
            'value' => 'cliente',
            'label' => 'Cliente (titular)',
            'telefono' => $telTitular,
            'nombre' => $nombreCliente,
            'parentesco' => '',
        ];
        for ($i = 1; $i <= 3; $i++) {
            $nk = 'nombre_completo_referencia' . $i;
            $tk = 'telefono_referencia' . $i;
            $nom = trim((string)($row[$nk] ?? ''));
            if ($nom === '') {
                continue;
            }
            $opciones[] = [
                'value' => 'referencia_' . $i,
                'label' => 'Referencia ' . $i,
                'telefono' => trim((string)($row[$tk] ?? '')),
                'nombre' => $nom,
                'parentesco' => 'Referencia',
            ];
        }
        foreach (self::listarContactosOtrosHistorialDictamenLlamada($id) as $ex) {
            $eid = (int)($ex['id'] ?? 0);
            if ($eid <= 0) {
                continue;
            }
            $opciones[] = [
                'value' => 'prev:' . $eid,
                'label' => 'Otro guardado: ' . trim((string)($ex['nombre'] ?? '')),
                'telefono' => trim((string)($ex['telefono'] ?? '')),
                'nombre' => trim((string)($ex['nombre'] ?? '')),
                'parentesco' => trim((string)($ex['parentesco'] ?? '')),
            ];
        }
        $opciones[] = [
            'value' => 'otros',
            'label' => 'Otros (nuevo contacto)',
            'telefono' => '',
            'nombre' => '',
            'parentesco' => '',
        ];
        return $opciones;
    }

    /**
     * Contactos "Otros" ya usados en dictámenes previos del mismo crédito (una fila representativa por combinación).
     *
     * @return array<int, array{id:int, telefono:string, nombre:string, parentesco:string}>
     */
    public static function listarContactosOtrosHistorialDictamenLlamada($idCredito)
    {
        try {
            $db = new Database();
            $id = (int)$idCredito;
            $sql = "
                SELECT dl.id,
                       dl.llamada_telefono AS telefono,
                       dl.llamada_nombre_persona AS nombre,
                       dl.llamada_parentesco AS parentesco
                FROM __SPARTA_SECRET_REDACTED__.dictamen_llamada dl
                INNER JOIN (
                    SELECT MAX(id) AS max_id
                    FROM __SPARTA_SECRET_REDACTED__.dictamen_llamada
                    WHERE id_credito = :id_credito
                      AND llamada_origen IN ('otros', 'extra')
                      AND COALESCE(TRIM(llamada_telefono), '') <> ''
                      AND COALESCE(TRIM(llamada_nombre_persona), '') <> ''
                    GROUP BY
                        TRIM(llamada_telefono),
                        TRIM(llamada_nombre_persona),
                        COALESCE(TRIM(llamada_parentesco), '')
                ) t ON dl.id = t.max_id
                ORDER BY dl.id DESC
            ";
            $rows = $db->queryAll($sql, ['id_credito' => $id]);
            return is_array($rows) ? $rows : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Datos de contacto de una fila dictamen_llamada (mismo crédito).
     */
    public static function obtenerSnapshotContactoDictamenLlamada($idDictamen, $idCredito)
    {
        try {
            $db = new Database();
            return $db->queryOne(
                'SELECT id_credito, llamada_telefono, llamada_nombre_persona, llamada_parentesco
                 FROM __SPARTA_SECRET_REDACTED__.dictamen_llamada
                 WHERE id = :id AND id_credito = :idc
                 LIMIT 1',
                ['id' => (int)$idDictamen, 'idc' => (int)$idCredito]
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Opciones para el modal Dictaminar llamada: titular, referencias (__SPARTA_SECRET_REDACTED__), historial otros y "Otros".
     */
    public static function getOpcionesContactoDictamenLlamada($idCredito)
    {
        try {
            $id = (int)$idCredito;
            if ($id <= 0) {
                return self::resultado(false, 'id_credito requerido', []);
            }
            $refRes = EmpresasDAO::getConsultaReferenciasEstadoCuenta($idCredito);
            $row = [];
            if (!empty($refRes['success']) && !empty($refRes['datos'][0]) && is_array($refRes['datos'][0])) {
                $row = $refRes['datos'][0];
            }
            $telTitular = '';
            $telRes = EmpresasDAO::getTelefonoTitularCredito($idCredito);
            if (!empty($telRes['success']) && array_key_exists('datos', $telRes)) {
                $telTitular = trim((string)$telRes['datos']);
            }
            if ($telTitular === '') {
                $telSm = EmpresasDAO::getCelularCreditoSegundometro($idCredito);
                if (!empty($telSm['success']) && array_key_exists('datos', $telSm)) {
                    $telTitular = trim((string)$telSm['datos']);
                }
            }
            $nombreCliente = trim((string)($row['nombre_completo'] ?? ''));

            $opciones = [];
            $opciones[] = [
                'value' => 'cliente',
                'label' => 'Cliente (titular)',
                'telefono' => $telTitular,
                'nombre' => $nombreCliente,
                'parentesco' => '',
            ];
            for ($i = 1; $i <= 3; $i++) {
                $nk = 'nombre_completo_referencia' . $i;
                $tk = 'telefono_referencia' . $i;
                $nom = trim((string)($row[$nk] ?? ''));
                if ($nom === '') {
                    continue;
                }
                $opciones[] = [
                    'value' => 'referencia_' . $i,
                    'label' => 'Referencia ' . $i,
                    'telefono' => trim((string)($row[$tk] ?? '')),
                    'nombre' => $nom,
                    'parentesco' => 'Referencia',
                ];
            }
            foreach (self::listarContactosOtrosHistorialDictamenLlamada($id) as $ex) {
                $eid = (int)($ex['id'] ?? 0);
                if ($eid <= 0) {
                    continue;
                }
                $opciones[] = [
                    'value' => 'prev:' . $eid,
                    'label' => 'Otro guardado: ' . trim((string)($ex['nombre'] ?? '')),
                    'telefono' => trim((string)($ex['telefono'] ?? '')),
                    'nombre' => trim((string)($ex['nombre'] ?? '')),
                    'parentesco' => trim((string)($ex['parentesco'] ?? '')),
                ];
            }
            $opciones[] = [
                'value' => 'otros',
                'label' => 'Otros (nuevo contacto)',
                'telefono' => '',
                'nombre' => '',
                'parentesco' => '',
            ];
            return self::resultado(true, 'Opciones de contacto', $opciones);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al armar contactos', [], $e->getMessage());
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

            $llamada_origen = addslashes(trim((string)($data['llamada_origen'] ?? '')));
            $llamada_telefono = addslashes(trim((string)($data['llamada_telefono'] ?? '')));
            $llamada_nombre = addslashes(trim((string)($data['llamada_nombre_persona'] ?? '')));
            $llamada_parentesco = trim((string)($data['llamada_parentesco'] ?? ''));
            $llamada_parentesco_sql = $llamada_parentesco !== ''
                ? ("'" . addslashes($llamada_parentesco) . "'")
                : 'NULL';

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
                comentarios,
                llamada_origen,
                llamada_telefono,
                llamada_nombre_persona,
                llamada_parentesco
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
                '$comentarios',
                '$llamada_origen',
                '$llamada_telefono',
                '$llamada_nombre',
                $llamada_parentesco_sql
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
                $r[$i]['estatus_pago']               = 0;
                $r[$i]['monto_parcial_pagado']        = 0;
            }
        } catch (\Exception $e2) {
            return self::resultado(
                false,
                'Error al consultar Gastos Cobranza',
                [],
                $e2->getMessage()
            );
        }
    }

    try {
        $datos = array_map(function ($row) {

            $montoValor         = (float)$row['monto_valor'];
            $parcialMonto       = (float)($row['condonacion_parcial_monto'] ?? 0);
            $montoParcialPagado = (float)($row['monto_parcial_pagado'] ?? 0);
            $estatusPago        = (int)($row['estatus_pago'] ?? 0);

            // Pendiente real = original - condonado - pagado
            $montoEfectivo = round(max(0, $montoValor - $parcialMonto - $montoParcialPagado), 2);

            return [
                'id_gasto'                   => (int)$row['id_gastos_cobranza'],
                'semana'                     => $row['SEMANA'],
                'periodo'                    => date('d/m/Y', strtotime($row['periodo_inicio'])) .
                                               ' - ' .
                                               date('d/m/Y', strtotime($row['periodo_fin'])),
                'monto'                      => $montoEfectivo,
                'monto_original'             => $montoValor,
                'monto_parcial_pagado'       => $montoParcialPagado,
                'condonacion_parcial_monto'  => $parcialMonto,
                'condonacion_parcial_motivo' => $row['condonacion_parcial_motivo'] ?? '',
                'cuota'                      => (float)$row['cuota'],
                'parcialidad'                => (int)$row['parcialidad'],
                'estatus_pago'               => $estatusPago,
                'tiene_pago_parcial'         => ($estatusPago === 1 && $montoParcialPagado > 0),
            ];
        }, $r);

        return self::resultado(true, 'Gastos Cobranza', $datos);

    } catch (\Exception $e) {
        return self::resultado(
            false,
            'Error al consultar Gastos Cobranza',
            [],
            $e->getMessage()
        );
    }
}

/**
 * Lee la fecha de último pago efectivo en tbl_segundometro_semana (misma fuente que insertAclaracionGcVerificacionSemana).
 *
 * Puede haber **varias filas** por Id_credito (p. ej. histórico por semana). Antes se usaba LIMIT 1 sin ORDER BY,
 * lo que devolvía una fila arbitraria (a veces una fecha vieja). Se usa MAX para alinear con el último pago real.
 *
 * @return array{ymd: string, datetime_sql: string}|null ymd = Y-m-d (calendario); datetime_sql para columna ultimo_pago_efectivo.
 */
public static function obtenerUltimoPagoEfectivoSegundometroParaCredito(int $idCredito): ?array
{
    if ($idCredito <= 0) {
        return null;
    }
    try {
        $dbSm = new DatabaseSegundometro();
        $filaSm = $dbSm->queryOne(
            'SELECT MAX(s.`Fecha_ultimo_pago_efectivo`) AS f FROM `tbl_segundometro_semana` AS s '
            . 'WHERE s.`Id_credito` = :id_credito',
            ['id_credito' => $idCredito]
        );
        if (empty($filaSm) || !array_key_exists('f', $filaSm) || $filaSm['f'] === null || $filaSm['f'] === '') {
            return null;
        }
        $rawF = $filaSm['f'];
        $datetimeSql = null;
        $ymd = null;
        if ($rawF instanceof \DateTimeInterface) {
            $datetimeSql = $rawF->format('Y-m-d H:i:s');
            $ymd = $rawF->format('Y-m-d');
        } else {
            $s = trim((string) $rawF);
            if (strlen($s) >= 10) {
                $ymd = substr($s, 0, 10);
                $datetimeSql = strlen($s) > 10
                    ? substr(str_replace('T', ' ', $s), 0, 19)
                    : ($ymd . ' 00:00:00');
            }
        }
        if ($ymd === null || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd)) {
            return null;
        }
        if ($datetimeSql === null) {
            $datetimeSql = $ymd . ' 00:00:00';
        }

        return ['ymd' => $ymd, 'datetime_sql' => $datetimeSql];
    } catch (\Throwable $e) {
        error_log('obtenerUltimoPagoEfectivoSegundometroParaCredito: ' . $e->getMessage());

        return null;
    }
}

/**
 * Ventana «falta aplicar» (calendario Ciudad de México).
 * Martes a domingo: último pago debe ser ayer u hoy.
 * Lunes: solo si el último pago efectivo es del mismo lunes; sábado o domingo no aplican (cartera el martes).
 *
 * @return array{ok: bool, mensaje: string, lunes_fin_semana?: bool}
 */
public static function validarUltimoPagoEfectivoVentanaAclaracionGc(?string $fechaYmd): array
{
    if ($fechaYmd === null || $fechaYmd === '') {
        return [
            'ok' => false,
            'mensaje' => 'No se encontró fecha de último pago efectivo en Segundómetro para este crédito. No es posible registrar la aclaración.',
        ];
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaYmd)) {
        return [
            'ok' => false,
            'mensaje' => 'La fecha de último pago efectivo no es válida. No es posible registrar la aclaración.',
        ];
    }
    try {
        $tz = new \DateTimeZone('America/Mexico_City');
        $hoy = new \DateTimeImmutable('today', $tz);
        $dowHoy = (int) $hoy->format('N');
        $fp = \DateTimeImmutable::createFromFormat('!Y-m-d', $fechaYmd, $tz);
        if ($fp === false) {
            return ['ok' => false, 'mensaje' => 'No se pudo interpretar la fecha del último pago efectivo.'];
        }
        if ($fp > $hoy) {
            return [
                'ok' => false,
                'mensaje' => 'La fecha del último pago efectivo es posterior a hoy; no es posible registrar la aclaración.',
            ];
        }

        if ($dowHoy === 1) {
            $dowFp = (int) $fp->format('N');
            if ($dowFp === 6 || $dowFp === 7) {
                return [
                    'ok' => false,
                    'mensaje' => 'El último pago efectivo corresponde al fin de semana. No es necesario registrar «falta aplicar»: la aplicación en cartera se reflejará el martes.',
                    'lunes_fin_semana' => true,
                ];
            }
            if ($fp->format('Y-m-d') !== $hoy->format('Y-m-d')) {
                return [
                    'ok' => false,
                    'mensaje' => 'Los lunes solo puede registrarse «falta aplicar» si el último pago efectivo es del mismo día (Ciudad de México). El último pago registrado es del '
                        . $fp->format('d/m/Y') . '.',
                ];
            }

            return ['ok' => true, 'mensaje' => ''];
        }

        $ayer = $hoy->modify('-1 day');
        if ($fp < $ayer) {
            return [
                'ok' => false,
                'mensaje' => 'El último pago efectivo queda fuera de la ventana permitida para «falta aplicar» (calendario Ciudad de México). El último pago registrado es del '
                    . $fp->format('d/m/Y') . '.',
            ];
        }

        return ['ok' => true, 'mensaje' => ''];
    } catch (\Throwable $e) {
        return ['ok' => false, 'mensaje' => 'Error al validar la fecha del último pago.'];
    }
}

/**
 * Inserta registro de aclaración GC en cobranza_gc_verificacion_semana (__SPARTA_SECRET_REDACTED__).
 * Requiere columnas: nombre, tipo_reporte, monto_aplicar, estatus (TINYINT), celula (además de las base).
 * tipo_reporte: error | falta_aplicar.
 * estatus: códigos numéricos; 3 = reportado por call center (estado de cuenta).
 * s2_exitoso / incluido_reporte: siempre 1 en flujo Aclaraciones (modal estado de cuenta).
 * id_usuario_reporte: usuario Ledger en sesión que envía la aclaración (columna en BD).
 * monto_aplicar: positivo si falta_aplicar; negativo si error (monto a corregir).
 * inicio_semana: martes que inicia la semana operativa (mar–lun); si hoy es lunes, es el martes anterior.
 * anio_iso / semana_iso: semana ISO del calendario asociada a ese martes.
 * ultimo_pago_efectivo: se obtiene en servidor desde tbl_segundometro_semana (usuario no interviene).
 * Ventana CDMX para falta_aplicar (lunes distinto a mar–dom): solo aplica a tipo_reporte falta_aplicar. Con tipo error puede guardarse aunque la fecha sea anterior.
 */
public static function insertAclaracionGcVerificacionSemana(array $p): array
{
    $idCredito = (int) ($p['id_credito'] ?? 0);
    if ($idCredito <= 0) {
        return self::resultado(false, 'ID de crédito inválido', [], 'id_credito');
    }
    $tipo = (string) ($p['tipo_reporte'] ?? '');
    if ($tipo !== 'error' && $tipo !== 'falta_aplicar') {
        return self::resultado(false, 'Tipo de reporte inválido', [], 'tipo_reporte');
    }
    $montoAbs = isset($p['monto']) ? round(abs((float) $p['monto']), 2) : 0.0;
    if ($montoAbs <= 0) {
        return self::resultado(false, 'El monto debe ser mayor a cero', [], 'monto');
    }
    $montoAplicar = ($tipo === 'error') ? -$montoAbs : $montoAbs;
    $nombre = trim((string) ($p['nombre'] ?? ''));
    if ($nombre === '') {
        $nombre = '—';
    }
    if (mb_strlen($nombre) > 255) {
        $nombre = mb_substr($nombre, 0, 255);
    }
    $mensaje = trim((string) ($p['mensaje'] ?? ''));
    $estatus = (int) ($p['estatus'] ?? 3);
    if ($estatus < 0 || $estatus > 255) {
        return self::resultado(false, 'Estatus inválido', [], 'estatus');
    }
    $celula = $p['celula'];
    if ($celula !== null && $celula !== '') {
        $celula = (int) $celula;
    } else {
        $celula = null;
    }
    $idUsuarioReporte = $p['id_usuario_reporte'] ?? null;
    if ($idUsuarioReporte !== null && $idUsuarioReporte !== '') {
        $idUsuarioReporte = (int) $idUsuarioReporte;
        if ($idUsuarioReporte <= 0) {
            $idUsuarioReporte = null;
        }
    } else {
        $idUsuarioReporte = null;
    }

    try {
        $tz = new \DateTimeZone('America/Mexico_City');
        $now = new \DateTimeImmutable('now', $tz);
        $registradoEnCdmx = $now->format('Y-m-d H:i:s');
        // Semana operativa mar–lun: inicio_semana = martes de esa semana (lunes → martes anterior).
        $n = (int) $now->format('N');
        $diasAlMartesInicio = ($n === 1) ? -6 : (2 - $n);
        $martesInicio = $now->modify((string) $diasAlMartesInicio . ' days');
        $inicioSemana = $martesInicio->format('Y-m-d');
        $anioIso = (int) $martesInicio->format('o');
        $semanaIso = (int) $martesInicio->format('W');
    } catch (\Throwable $e) {
        return self::resultado(false, 'Error al calcular fecha', [], $e->getMessage());
    }

    $ultimoInfo = self::obtenerUltimoPagoEfectivoSegundometroParaCredito($idCredito);
    $ymdUltimo = $ultimoInfo['ymd'] ?? null;
    if ($ymdUltimo === null || $ymdUltimo === '') {
        return self::resultado(
            false,
            'No se encontró fecha de último pago efectivo en Segundómetro para este crédito. No es posible registrar la aclaración.',
            [],
            'ultimo_pago_efectivo'
        );
    }
    if ($tipo === 'falta_aplicar') {
        $valUp = self::validarUltimoPagoEfectivoVentanaAclaracionGc($ymdUltimo);
        if (!$valUp['ok']) {
            return self::resultado(false, $valUp['mensaje'], [], 'ultimo_pago_efectivo');
        }
    }
    $ultimoPagoEfectivo = $ultimoInfo['datetime_sql'] ?? null;

    $sqlDup = "
        SELECT `estatus`
        FROM `cobranza_gc_verificacion_semana`
        WHERE `id_credito` = :id_credito AND `inicio_semana` = :inicio_semana
    ";

    $sql = "
    INSERT INTO `cobranza_gc_verificacion_semana` (
        `id_credito`,
        `inicio_semana`,
        `anio_iso`,
        `semana_iso`,
        `registrado_en_cdmx`,
        `ultimo_pago_efectivo`,
        `s2_exitoso`,
        `incluido_reporte`,
        `mensaje`,
        `nombre`,
        `tipo_reporte`,
        `monto_aplicar`,
        `estatus`,
        `celula`,
        `id_usuario_reporte`
    ) VALUES (
        :id_credito,
        :inicio_semana,
        :anio_iso,
        :semana_iso,
        :registrado_en_cdmx,
        :ultimo_pago_efectivo,
        :s2_exitoso,
        :incluido_reporte,
        :mensaje,
        :nombre,
        :tipo_reporte,
        :monto_aplicar,
        :estatus,
        :celula,
        :id_usuario_reporte
    )
    ";

    $params = [
        'id_credito'             => $idCredito,
        'inicio_semana'          => $inicioSemana,
        'anio_iso'               => $anioIso,
        'semana_iso'             => $semanaIso,
        'registrado_en_cdmx'     => $registradoEnCdmx,
        'ultimo_pago_efectivo'   => $ultimoPagoEfectivo,
        's2_exitoso'             => 1,
        'incluido_reporte'       => 1,
        'mensaje'                => $mensaje,
        'nombre'                 => $nombre,
        'tipo_reporte'           => $tipo,
        'monto_aplicar'          => $montoAplicar,
        'estatus'                => $estatus,
        'celula'                 => $celula,
        'id_usuario_reporte'     => $idUsuarioReporte,
    ];

    try {
        $db = new DatabaseSegundometro();
        $dup = $db->queryAll($sqlDup, [
            'id_credito'    => $idCredito,
            'inicio_semana' => $inicioSemana,
        ]);
        if (!empty($dup)) {
            $hayEstatus3 = false;
            foreach ($dup as $fila) {
                if ((int) ($fila['estatus'] ?? 0) === 3) {
                    $hayEstatus3 = true;
                    break;
                }
            }
            if ($hayEstatus3) {
                return self::resultado(
                    false,
                    'Este crédito ya está en proceso ante cartera. '
                    . 'La confirmación puede demorar hasta 24 horas hábiles; le pedimos un poco de paciencia. '
                    . 'Muchas gracias por su atención.',
                    ['alerta' => 'info']
                );
            }
            return self::resultado(
                false,
                'Este crédito ya fue incorporado al reporte de esta semana y está confirmado por cartera. '
                . 'No es necesario volver a enviarlo. Gracias por su tiempo y apoyo.',
                ['alerta' => 'info']
            );
        }
        $db->CRUD($sql, $params);
        return self::resultado(true, 'Aclaración registrada', ['id_credito' => $idCredito]);
    } catch (\Throwable $e) {
        error_log('insertAclaracionGcVerificacionSemana: ' . $e->getMessage());
        return self::resultado(false, 'No se pudo guardar la aclaración', [], $e->getMessage());
    }
}

public static function getGastosTodosConEstatus($idCredito)
{
    $query = "
    SELECT id_gastos_cobranza,
           monto_valor,
           estatus_pago,
           COALESCE(monto_parcial_pagado, 0) AS monto_parcial_pagado
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
     * Cruce automático de gastos de cobranza a partir de datosNotasCargos (API S2).
     *
     * Única vía aplicada al abrir el menú Estado de cuenta y al cron masivo: aquí (y en
     * actualizarEstatusPagoGastoConMonto) es donde puede cambiarse estatus_pago por el cruce automático.
     * Si $persistirEnBd es false, solo se calcula (misma lógica, sin UPDATE).
     *
     * @param array  $notasCargos listaNotasCargos de estadoCuenta
     * @param mixed  $idCredito   Id_credito
     * @param bool   $persistirEnBd true = escribir en BD como la pantalla; false = simular
     * @return array{gastos_procesados: list<array>, saldo_favor: float, _sin_actualizacion?: bool, _causa?: string}
     */
    public static function procesarGastosCobranzaDesdeNotas(array $notasCargos, $idCredito, bool $persistirEnBd = true): array
    {
        $fechaInicio = '2026-01-28';

        $notasFiltradas = array_filter($notasCargos, function ($nota) use ($fechaInicio) {
            return
                ($nota['concepto'] ?? '') === 'NOTA DE DE CARGO GASTOS DE COBRANZA' &&
                ($nota['fechaMovimiento'] ?? '') >= $fechaInicio;
        });

        if (empty($notasFiltradas)) {
            return [
                'gastos_procesados' => [],
                'saldo_favor'       => 0.0,
                '_sin_actualizacion' => true,
                '_causa'             => 'sin_notas_validas',
            ];
        }

        $totalNotas      = array_sum(array_column($notasFiltradas, 'monto'));
        $montoDisponible = round($totalNotas, 2);

        $resultadoTodos = self::getGastosTodosConEstatus($idCredito);
        if ($resultadoTodos['success'] && !empty($resultadoTodos['datos'])) {
            foreach ($resultadoTodos['datos'] as $g) {
                $estatus = (int) ($g['estatus_pago'] ?? 0);

                if ($estatus === 2) {
                    $consumido = round((float) ($g['monto_parcial_pagado'] ?? 0), 2);
                    if ($consumido <= 0) {
                        $consumido = round((float) ($g['monto_valor'] ?? 0), 2);
                    }
                    $montoDisponible = round($montoDisponible - $consumido, 2);
                } elseif ($estatus === 1) {
                    $montoDisponible = round($montoDisponible - (float) ($g['monto_parcial_pagado'] ?? 0), 2);
                }
            }
            $montoDisponible = max(0, $montoDisponible);
        }

        if ($montoDisponible <= 0) {
            return [
                'gastos_procesados' => [],
                'saldo_favor'       => 0.0,
                '_sin_actualizacion' => true,
                '_causa'             => 'monto_disponible_cero',
            ];
        }

        $resultadoGastos = self::getGastosCobranza($idCredito);

        if (!$resultadoGastos['success'] || empty($resultadoGastos['datos'])) {
            return [
                'gastos_procesados' => [],
                'saldo_favor'       => 0.0,
                '_sin_actualizacion' => true,
                '_causa'             => 'sin_filas_gastos',
            ];
        }

        $gastosPendientes = array_filter($resultadoGastos['datos'], function ($g) {
            $estatus   = (int) ($g['estatus_pago'] ?? 0);
            $condonado = (int) ($g['condonado'] ?? 0);

            return ($estatus === 0 || $estatus === 1) && $condonado === 0;
        });

        if (empty($gastosPendientes)) {
            return [
                'gastos_procesados' => [],
                'saldo_favor'       => 0.0,
                '_sin_actualizacion' => true,
                '_causa'             => 'sin_pendientes',
            ];
        }

        $gastosProcessados = [];

        $ultimaNotaFecha = null;
        foreach ($notasFiltradas as $nota) {
            $fn = $nota['fechaMovimiento'] ?? null;
            if ($fn && ($ultimaNotaFecha === null || $fn > $ultimaNotaFecha)) {
                $ultimaNotaFecha = $fn;
            }
        }

        $fechaPagoGasto = !empty($ultimaNotaFecha) ? $ultimaNotaFecha : date('Y-m-d');

        foreach ($gastosPendientes as $gasto) {

            if ($montoDisponible <= 0) {
                break;
            }

            $montoGastoOriginal      = round((float) $gasto['monto'], 2);
            $yaPagado                = (int) $gasto['estatus_pago'] === 1 ? round((float) $gasto['monto_parcial_pagado'], 2) : 0;
            $montoRestantePorCubrir  = round($montoGastoOriginal - $yaPagado, 2);

            if ($montoDisponible >= $montoRestantePorCubrir) {
                $montoDisponible = round($montoDisponible - $montoRestantePorCubrir, 2);

                $gastosProcessados[] = [
                    'id_gasto'    => $gasto['id_gasto'],
                    'monto'       => $montoGastoOriginal,
                    'aplicado'    => $montoGastoOriginal,
                    'estatus'     => 2,
                    'estatus_txt' => 'PAGADO',
                    'celula'      => $gasto['celula'] ?? 0,
                    'fecha_pago'  => $fechaPagoGasto,
                ];

                if ($persistirEnBd) {
                    self::actualizarEstatusPagoGastoConMonto($gasto['id_gasto'], 2, $montoGastoOriginal, 0, $fechaPagoGasto);
                }
            } else {
                $montoAAbonarAhora = $montoDisponible;
                $totalPagadoFinal  = round($yaPagado + $montoAAbonarAhora, 2);
                $pendiente         = round($montoGastoOriginal - $totalPagadoFinal, 2);

                $gastosProcessados[] = [
                    'id_gasto'    => $gasto['id_gasto'],
                    'monto'       => $montoGastoOriginal,
                    'aplicado'    => $totalPagadoFinal,
                    'estatus'     => 1,
                    'estatus_txt' => 'PAGO PARCIAL',
                    'pendiente'   => $pendiente,
                    'fecha_pago'  => $fechaPagoGasto,
                ];

                if ($persistirEnBd) {
                    self::actualizarEstatusPagoGastoConMonto(
                        $gasto['id_gasto'],
                        1,
                        $totalPagadoFinal,
                        0,
                        $fechaPagoGasto
                    );
                }

                $montoDisponible = 0;
            }
        }

        return [
            'gastos_procesados' => $gastosProcessados,
            'saldo_favor'       => round($montoDisponible, 2),
        ];
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
     * Valida que el motivo de condonación parcial tenga al menos 25 caracteres (tras trim).
     *
     * @param string $motivo
     * @return array [ true ] o [ false, 'mensaje_error' ]
     */
    public static function validarMotivoCondonacionParcial($motivo)
    {
        $motivo = trim((string) $motivo);
        if (mb_strlen($motivo, 'UTF-8') < 25) {
            return [false, 'El motivo debe tener al menos 25 caracteres.'];
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
        created_at,
        parcialidad,
        COALESCE(monto_parcial_pagado, 0) AS monto_parcial_pagado
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
                'monto_condonado'           => $row['condonado'] == 1 ? $montoOriginal : $condParcial,
                'monto_parcial_pagado'      => (float)($row['monto_parcial_pagado'] ?? 0), // 👈
                'parcialidad'               => (int)$row['parcialidad'],                   // 👈
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
