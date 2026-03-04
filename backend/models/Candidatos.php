<?php

namespace Models;

use Core\Model;
use Core\Database;

class Candidatos extends Model
{
    /**
     * Listar todos los candidatos con puesto y departamento de interés.
     */
    public static function getAll($estatus = null, $id_departamento = null, $id_puesto = null)
    {
        $query = <<<SQL
            SELECT
                c.id,
                c.nombres,
                c.segundo_nombre,
                c.apellidop,
                c.apellidom,
                c.email,
                c.telefono,
                c.id_puesto,
                c.id_departamento,
                c.estatus,
                c.notas,
                c.estatus,
                c.notas,
                c.postulacion_enviada,
                c.fecha_registro,
                c.fecha_actualizacion,
                p.nombre AS nombre_puesto,
                d.nombre AS nombre_departamento
            FROM candidatos c
            LEFT JOIN puesto p ON p.id = c.id_puesto
            LEFT JOIN departamento d ON d.id = c.id_departamento
            WHERE 1=1
        SQL;
        $params = [];

        if ($estatus !== null && $estatus !== '') {
            $query .= " AND c.estatus = :estatus";
            $params['estatus'] = $estatus;
        }
        if ($id_departamento !== null && $id_departamento !== '') {
            $query .= " AND c.id_departamento = :id_departamento";
            $params['id_departamento'] = (int) $id_departamento;
        }
        if ($id_puesto !== null && $id_puesto !== '') {
            $query .= " AND c.id_puesto = :id_puesto";
            $params['id_puesto'] = (int) $id_puesto;
        }

        $query .= " ORDER BY c.fecha_registro DESC";

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Candidatos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener candidatos.', null, $e->getMessage());
        }
    }

    /**
     * Obtener un candidato por ID.
     */
    public static function getById($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        $query = <<<SQL
            SELECT
                c.id,
                c.nombres,
                c.segundo_nombre,
                c.apellidop,
                c.apellidom,
                c.email,
                c.telefono,
                c.id_pais,
                c.id_puesto,
                c.id_departamento,
                c.id_posible_jefe,
                c.fecha_postulacion,
                c.id_legion,
                c.usuario,
                c.contrasena,
                c.estatus,
                c.notas,
                c.postulacion_enviada,
                c.fecha_registro,
                c.fecha_actualizacion,
                p.nombre AS nombre_puesto,
                d.nombre AS nombre_departamento
            FROM candidatos c
            LEFT JOIN puesto p ON p.id = c.id_puesto
            LEFT JOIN departamento d ON d.id = c.id_departamento
            WHERE c.id = :id
        SQL;
        try {
            $db = new Database();
            $r = $db->queryOne($query, ['id' => $id]);
            if (!$r) {
                return self::resultado(false, 'Candidato no encontrado.', null);
            }
            return self::resultado(true, 'Candidato encontrado.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener candidato.', null, $e->getMessage());
        }
    }

    /**
     * Insertar nuevo candidato (con postulación enviada y datos de postulación).
     */
    public static function insert($data)
    {
        $nombres = trim($data['nombres'] ?? '');
        $apellidop = trim($data['apellidop'] ?? '');
        if ($nombres === '' || $apellidop === '') {
            return self::resultado(false, 'Nombres y apellido paterno son obligatorios.', null);
        }

        $postulacionEnviada = !empty($data['postulacion_enviada']) ? 1 : 0;
        $fechaEnviada = $postulacionEnviada ? (date('Y-m-d H:i:s')) : null;

        $query = <<<SQL
            INSERT INTO candidatos (
                nombres, segundo_nombre, apellidop, apellidom,
                email, telefono, id_pais, id_puesto, id_departamento, id_posible_jefe,
                fecha_postulacion, id_legion, usuario, contrasena,
                postulacion_enviada, fecha_postulacion_enviada, estatus, notas
            ) VALUES (
                :nombres, :segundo_nombre, :apellidop, :apellidom,
                :email, :telefono, :id_pais, :id_puesto, :id_departamento, :id_posible_jefe,
                :fecha_postulacion, :id_legion, :usuario, :contrasena,
                :postulacion_enviada, :fecha_postulacion_enviada, :estatus, :notas
            )
        SQL;
        $params = [
            'nombres' => $nombres,
            'segundo_nombre' => trim($data['segundo_nombre'] ?? '') ?: null,
            'apellidop' => $apellidop,
            'apellidom' => trim($data['apellidom'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
            'id_pais' => !empty($data['id_pais']) ? (int) $data['id_pais'] : null,
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
            'id_posible_jefe' => !empty($data['id_posible_jefe']) ? (int) $data['id_posible_jefe'] : null,
            'fecha_postulacion' => !empty($data['fecha_postulacion']) ? $data['fecha_postulacion'] : null,
            'id_legion' => !empty($data['id_legion']) ? (int) $data['id_legion'] : null,
            'usuario' => trim($data['usuario'] ?? '') ?: null,
            'contrasena' => trim($data['contrasena'] ?? '') ?: null,
            'postulacion_enviada' => $postulacionEnviada,
            'fecha_postulacion_enviada' => $fechaEnviada,
            'estatus' => trim($data['estatus'] ?? '') ?: 'Por evaluar',
            'notas' => trim($data['notas'] ?? '') ?: null,
        ];

        try {
            $db = new Database();
            $db->CRUD($query, $params);
            $newId = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id = (int) ($newId['id'] ?? 0);
            return self::resultado(true, 'Candidato registrado correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar candidato.', null, $e->getMessage());
        }
    }

    /**
     * Actualizar candidato.
     */
    public static function update($id, $data)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }

        $nombres = trim($data['nombres'] ?? '');
        $apellidop = trim($data['apellidop'] ?? '');
        if ($nombres === '' || $apellidop === '') {
            return self::resultado(false, 'Nombres y apellido paterno son obligatorios.', null);
        }

        $query = <<<SQL
            UPDATE candidatos SET
                nombres = :nombres,
                segundo_nombre = :segundo_nombre,
                apellidop = :apellidop,
                apellidom = :apellidom,
                email = :email,
                telefono = :telefono,
                id_pais = :id_pais,
                id_puesto = :id_puesto,
                id_departamento = :id_departamento,
                id_posible_jefe = :id_posible_jefe,
                fecha_postulacion = :fecha_postulacion,
                id_legion = :id_legion,
                usuario = :usuario,
                contrasena = :contrasena,
                estatus = :estatus,
                notas = :notas
            WHERE id = :id
        SQL;
        $params = [
            'id' => $id,
            'nombres' => $nombres,
            'segundo_nombre' => trim($data['segundo_nombre'] ?? '') ?: null,
            'apellidop' => $apellidop,
            'apellidom' => trim($data['apellidom'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
            'id_pais' => !empty($data['id_pais']) ? (int) $data['id_pais'] : null,
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
            'id_posible_jefe' => !empty($data['id_posible_jefe']) ? (int) $data['id_posible_jefe'] : null,
            'fecha_postulacion' => !empty($data['fecha_postulacion']) ? $data['fecha_postulacion'] : null,
            'id_legion' => !empty($data['id_legion']) ? (int) $data['id_legion'] : null,
            'usuario' => isset($data['usuario']) ? (trim($data['usuario']) ?: null) : null,
            'contrasena' => isset($data['contrasena']) ? (trim($data['contrasena']) ?: null) : null,
            'estatus' => trim($data['estatus'] ?? '') ?: 'Por evaluar',
            'notas' => trim($data['notas'] ?? '') ?: null,
        ];

        try {
            $db = new Database();
            $db->CRUD($query, $params);
            return self::resultado(true, 'Candidato actualizado correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar candidato.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar candidato.
     */
    public static function delete($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidatos WHERE id = :id", ['id' => $id]);
            return self::resultado(true, 'Candidato eliminado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar candidato.', null, $e->getMessage());
        }
    }
    
    /**
     * Obtener o crear token único para link de subida de documentos del candidato.
     * Retorna el token (string) para construir la URL.
     */
    public static function getOrCreateTokenDocumentos($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT token FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1",
                ['id' => $id_candidato]
            );
            if ($row && !empty($row['token'])) {
                return self::resultado(true, 'Token existente.', $row['token']);
            }
            $token = bin2hex(random_bytes(32));
            $db->CRUD(
                "INSERT INTO candidato_documento_token (id_candidato, token) VALUES (:id_candidato, :token)",
                ['id_candidato' => $id_candidato, 'token' => $token]
            );
            return self::resultado(true, 'Token generado.', $token);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al generar token.', null, $e->getMessage());
        }
    }

    /**
     * Obtener id_candidato y datos básicos a partir del token (para la vista pública de subida).
     */
    public static function getCandidatoPorToken($token)
    {
        $token = trim($token ?? '');
        if ($token === '') {
            return self::resultado(false, 'Token inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT t.id_candidato, c.nombres, c.apellidop, c.apellidom, c.email FROM candidato_documento_token t INNER JOIN candidatos c ON c.id = t.id_candidato WHERE t.token = :token LIMIT 1",
                ['token' => $token]
            );
            if (!$row) {
                return self::resultado(false, 'Enlace no válido o expirado.', null);
            }
            return self::resultado(true, 'Candidato encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al validar enlace.', null, $e->getMessage());
        }
    }

    /**
     * Registrar un documento subido por el candidato (vía link).
     * Si se pasan $contenido y $mime_type, el archivo se guarda en la BD (contenido LONGBLOB)
     * y se sirve desde ahí para que cargue más rápido (como carga_documento_persona).
     *
     * @param string $tipo_documento Nombre del tipo (ej. SOLICITUD INTERNA, CURP, etc.)
     * @param string|null $contenido Contenido binario del archivo (opcional). Si se pasa, se guarda en BD.
     * @param string|null $mime_type application/pdf, image/jpeg, etc. (opcional, recomendado si hay contenido)
     */
    public static function guardarDocumento($id_candidato, $nombre_archivo, $ruta_archivo, $tipo_documento = '', $contenido = null, $mime_type = null)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($nombre_archivo ?? '') === '') {
            return self::resultado(false, 'Datos incompletos.');
        }
        if ($contenido === null && trim($ruta_archivo ?? '') === '') {
            return self::resultado(false, 'Indica ruta_archivo o contenido.');
        }
        try {
            $db = new Database();
            if ($contenido !== null) {
                $ruta = trim($ruta_archivo ?? '');
                $db->queryOne(
                    "INSERT INTO candidato_documento (id_candidato, tipo_documento, nombre_archivo, ruta_archivo, contenido, mime_type) VALUES (:id_candidato, :tipo_documento, :nombre_archivo, :ruta_archivo, :contenido, :mime_type)",
                    [
                        'id_candidato' => $id_candidato,
                        'tipo_documento' => trim($tipo_documento ?? ''),
                        'nombre_archivo' => $nombre_archivo,
                        'ruta_archivo' => $ruta,
                        'contenido' => $contenido,
                        'mime_type' => $mime_type !== null ? trim($mime_type) : null
                    ]
                );
            } else {
                $db->CRUD(
                    "INSERT INTO candidato_documento (id_candidato, tipo_documento, nombre_archivo, ruta_archivo) VALUES (:id_candidato, :tipo_documento, :nombre_archivo, :ruta_archivo)",
                    [
                        'id_candidato' => $id_candidato,
                        'tipo_documento' => trim($tipo_documento ?? ''),
                        'nombre_archivo' => $nombre_archivo,
                        'ruta_archivo' => $ruta_archivo
                    ]
                );
            }
            self::invalidateDocumentacionCache($id_candidato);
            return self::resultado(true, 'Documento guardado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar documento.', null, $e->getMessage());
        }
    }

    /**
     * Obtener solo ruta y nombre de un documento (sin contenido). Para servir desde disco sin cargar LONGBLOB.
     */
    public static function getDocumentoRutaSolo($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT nombre_archivo, ruta_archivo FROM candidato_documento WHERE id = :id",
                ['id' => $id_documento]
            );
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Obtener contenido de un documento para servirlo (ver/descargar).
     * Devuelve nombre_archivo, contenido (LONGBLOB), mime_type.
     * Si el registro tiene contenido en BD se usa; si no, contenido será null (servir desde ruta_archivo en disco).
     */
    public static function getDocumentoContenidoParaVer($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id, nombre_archivo, contenido, mime_type, ruta_archivo FROM candidato_documento WHERE id = :id",
                ['id' => $id_documento]
            );
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Listar documentos ya subidos por un candidato.
     */
    public static function getDocumentosCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID inválido.', []);
        }
        try {
            $db = new Database();
            $lista = $db->queryAll("SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga, validado, fecha_validado FROM candidato_documento WHERE id_candidato = :id ORDER BY fecha_carga DESC", ['id' => $id_candidato]);
            return self::resultado(true, 'Documentos encontrados.', $lista ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al listar documentos.', [], $e->getMessage());
        }
    }

    /**
     * Obtener un documento por ID (para verificar y servir/eliminar).
     */
    public static function getDocumentoById($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, validado FROM candidato_documento WHERE id = :id", ['id' => $id_documento]);
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar un documento del expediente (por ID).
     */
    public static function eliminarDocumento($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento WHERE id = :id", ['id' => $id_documento]);
            return self::resultado(true, 'Documento eliminado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar todos los documentos de un candidato (solo registros en BD).
     * Los archivos en disco deben borrarse desde el controlador.
     */
    public static function eliminarDocumentosDeCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento WHERE id_candidato = :id", ['id' => $id_candidato]);
            return self::resultado(true, 'Documentación eliminada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar documentación.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar el token de enlace de documentos del candidato (para no dejar huérfanos).
     */
    public static function eliminarTokenDocumentosCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento_token WHERE id_candidato = :id", ['id' => $id_candidato]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Guardar el último resultado de verificación de expediente (API validar-expediente).
     * @param int $id_candidato
     * @param string|null $jsonResultado JSON del resultado (checks_ok, alertas, todo_coincide, etc.)
     */
    public static function updateVerificacionExpediente($id_candidato, $jsonResultado)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE candidatos SET ultima_verificacion_expediente = :json WHERE id = :id",
                ['id' => $id_candidato, 'json' => $jsonResultado === null ? null : (is_string($jsonResultado) ? $jsonResultado : json_encode($jsonResultado))]
            );
        } catch (\Exception $e) {
            // Columna puede no existir si no se ejecutó la migración
        }
        self::invalidateDocumentacionCache($id_candidato);
    }

    /**
     * Obtener el último resultado de verificación de expediente (JSON decodificado o null).
     */
    public static function getVerificacionExpediente($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT ultima_verificacion_expediente FROM candidatos WHERE id = :id", ['id' => $id_candidato]);
            if (!$row || empty($row['ultima_verificacion_expediente'])) {
                return null;
            }
            $decoded = json_decode($row['ultima_verificacion_expediente'], true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Documentos + verificación en una sola conexión (para listado Documentación, optimizado).
     * @return array{documentos: array, verificacion: array|null}
     */
    public static function getDocumentosYVerificacion($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return ['documentos' => [], 'verificacion' => null];
        }
        try {
            $db = new Database();
            $documentos = $db->queryAll(
                "SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga, validado, fecha_validado FROM candidato_documento WHERE id_candidato = :id ORDER BY fecha_carga DESC",
                ['id' => $id_candidato]
            );
            $documentos = $documentos ?: [];
            $row = $db->queryOne("SELECT ultima_verificacion_expediente FROM candidatos WHERE id = :id", ['id' => $id_candidato]);
            $verificacion = null;
            if ($row && !empty($row['ultima_verificacion_expediente'])) {
                $decoded = json_decode($row['ultima_verificacion_expediente'], true);
                $verificacion = is_array($decoded) ? $decoded : null;
            }
            return ['documentos' => $documentos, 'verificacion' => $verificacion];
        } catch (\Exception $e) {
            return ['documentos' => [], 'verificacion' => null];
        }
    }

    /**
     * Invalidar caché de listado documentación para un candidato (al subir/eliminar doc o actualizar verificación).
     */
    public static function invalidateDocumentacionCache($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        $cacheDir = defined('RAIZ') ? (RAIZ . '/storage/cache') : (__DIR__ . '/../storage/cache');
        $file = $cacheDir . '/doc_candidato_' . $id_candidato . '.json';
        if (is_file($file)) {
            @unlink($file);
        }
        if (function_exists('apcu_delete')) {
            @apcu_delete('doc_candidato_' . $id_candidato);
        }
    }

    /**
     * Marcar/desmarcar un documento como validado por Capital Humano.
     */
    public static function toggleValidadoDocumento($id_documento, $validado)
    {
        $id_documento = (int) $id_documento;
        $validado = $validado ? 1 : 0;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE candidato_documento SET validado = :v, fecha_validado = " . ($validado ? "NOW()" : "NULL") . " WHERE id = :id",
                ['id' => $id_documento, 'v' => $validado]
            );
            return self::resultado(true, $validado ? 'Documento validado.' : 'Validación retirada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    /**
     * Contar documentos validados vs total de un candidato.
     */
    public static function contarValidados($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return ['total' => 0, 'validados' => 0];
        }
        try {
            $db = new Database();
            $r = $db->queryOne(
                "SELECT COUNT(*) AS total, SUM(validado) AS validados FROM candidato_documento WHERE id_candidato = :id",
                ['id' => $id_candidato]
            );
            return ['total' => (int) ($r['total'] ?? 0), 'validados' => (int) ($r['validados'] ?? 0)];
        } catch (\Exception $e) {
            return ['total' => 0, 'validados' => 0];
        }
    }

    /**
     * Actualizar estatus del candidato.
     */
    public static function updateEstatus($id_candidato, $estatus)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($estatus) === '') {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD("UPDATE candidatos SET estatus = :e, fecha_actualizacion = NOW() WHERE id = :id", ['id' => $id_candidato, 'e' => trim($estatus)]);
        } catch (\Exception $e) {
        }
    }
}
