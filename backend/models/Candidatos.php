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
<<<<<<< HEAD
=======
                c.postulacion_enviada,
>>>>>>> 609f4c827579b9f7fdfa55f652e91ba318875f5f
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
<<<<<<< HEAD
                c.id_puesto,
                c.id_departamento,
                c.estatus,
                c.notas,
=======
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
>>>>>>> 609f4c827579b9f7fdfa55f652e91ba318875f5f
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
<<<<<<< HEAD
     * Insertar nuevo candidato.
=======
     * Insertar nuevo candidato (con postulación enviada y datos de postulación).
>>>>>>> 609f4c827579b9f7fdfa55f652e91ba318875f5f
     */
    public static function insert($data)
    {
        $nombres = trim($data['nombres'] ?? '');
        $apellidop = trim($data['apellidop'] ?? '');
        if ($nombres === '' || $apellidop === '') {
            return self::resultado(false, 'Nombres y apellido paterno son obligatorios.', null);
        }

<<<<<<< HEAD
        $query = <<<SQL
            INSERT INTO candidatos (
                nombres, segundo_nombre, apellidop, apellidom,
                email, telefono, id_puesto, id_departamento, estatus, notas
            ) VALUES (
                :nombres, :segundo_nombre, :apellidop, :apellidom,
                :email, :telefono, :id_puesto, :id_departamento, :estatus, :notas
=======
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
>>>>>>> 609f4c827579b9f7fdfa55f652e91ba318875f5f
            )
        SQL;
        $params = [
            'nombres' => $nombres,
            'segundo_nombre' => trim($data['segundo_nombre'] ?? '') ?: null,
            'apellidop' => $apellidop,
            'apellidom' => trim($data['apellidom'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
<<<<<<< HEAD
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
=======
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
>>>>>>> 609f4c827579b9f7fdfa55f652e91ba318875f5f
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
<<<<<<< HEAD
                id_puesto = :id_puesto,
                id_departamento = :id_departamento,
=======
                id_pais = :id_pais,
                id_puesto = :id_puesto,
                id_departamento = :id_departamento,
                id_posible_jefe = :id_posible_jefe,
                fecha_postulacion = :fecha_postulacion,
                id_legion = :id_legion,
                usuario = :usuario,
                contrasena = :contrasena,
>>>>>>> 609f4c827579b9f7fdfa55f652e91ba318875f5f
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
<<<<<<< HEAD
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
=======
            'id_pais' => !empty($data['id_pais']) ? (int) $data['id_pais'] : null,
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
            'id_posible_jefe' => !empty($data['id_posible_jefe']) ? (int) $data['id_posible_jefe'] : null,
            'fecha_postulacion' => !empty($data['fecha_postulacion']) ? $data['fecha_postulacion'] : null,
            'id_legion' => !empty($data['id_legion']) ? (int) $data['id_legion'] : null,
            'usuario' => isset($data['usuario']) ? (trim($data['usuario']) ?: null) : null,
            'contrasena' => isset($data['contrasena']) ? (trim($data['contrasena']) ?: null) : null,
>>>>>>> 609f4c827579b9f7fdfa55f652e91ba318875f5f
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
<<<<<<< HEAD
=======

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
     * @param string $tipo_documento Nombre del tipo (ej. SOLICITUD INTERNA, CURP, etc.)
     */
    public static function guardarDocumento($id_candidato, $nombre_archivo, $ruta_archivo, $tipo_documento = '')
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($nombre_archivo ?? '') === '' || trim($ruta_archivo ?? '') === '') {
            return self::resultado(false, 'Datos incompletos.');
        }
        try {
            $db = new Database();
            $db->CRUD(
                "INSERT INTO candidato_documento (id_candidato, tipo_documento, nombre_archivo, ruta_archivo) VALUES (:id_candidato, :tipo_documento, :nombre_archivo, :ruta_archivo)",
                [
                    'id_candidato' => $id_candidato,
                    'tipo_documento' => trim($tipo_documento ?? ''),
                    'nombre_archivo' => $nombre_archivo,
                    'ruta_archivo' => $ruta_archivo
                ]
            );
            return self::resultado(true, 'Documento guardado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar documento.', null, $e->getMessage());
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
            $lista = $db->queryAll("SELECT id, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga FROM candidato_documento WHERE id_candidato = :id ORDER BY fecha_carga DESC", ['id' => $id_candidato]);
            return self::resultado(true, 'Documentos encontrados.', $lista ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al listar documentos.', [], $e->getMessage());
        }
    }
>>>>>>> 609f4c827579b9f7fdfa55f652e91ba318875f5f
}
