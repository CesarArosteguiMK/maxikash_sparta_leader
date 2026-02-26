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
                c.id_puesto,
                c.id_departamento,
                c.estatus,
                c.notas,
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
     * Insertar nuevo candidato.
     */
    public static function insert($data)
    {
        $nombres = trim($data['nombres'] ?? '');
        $apellidop = trim($data['apellidop'] ?? '');
        if ($nombres === '' || $apellidop === '') {
            return self::resultado(false, 'Nombres y apellido paterno son obligatorios.', null);
        }

        $query = <<<SQL
            INSERT INTO candidatos (
                nombres, segundo_nombre, apellidop, apellidom,
                email, telefono, id_puesto, id_departamento, estatus, notas
            ) VALUES (
                :nombres, :segundo_nombre, :apellidop, :apellidom,
                :email, :telefono, :id_puesto, :id_departamento, :estatus, :notas
            )
        SQL;
        $params = [
            'nombres' => $nombres,
            'segundo_nombre' => trim($data['segundo_nombre'] ?? '') ?: null,
            'apellidop' => $apellidop,
            'apellidom' => trim($data['apellidom'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
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
                id_puesto = :id_puesto,
                id_departamento = :id_departamento,
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
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
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
}
