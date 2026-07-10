<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Dirección leída una sola vez desde el INE del cliente para Sabueso.
 * La huella se basa en el nombre del archivo fuente: si cambia el INE,
 * Sabueso vuelve a solicitar la lectura y reemplaza el registro.
 */
class CreditoDireccionIneSabueso extends Model
{
    private static function tabla(Database $db): void
    {
        $db->CRUD(
            "CREATE TABLE IF NOT EXISTS estado_cuenta.credito_direccion_ine_sabueso (
                id_credito BIGINT NOT NULL,
                huella_fuente CHAR(64) NOT NULL,
                archivo_fuente VARCHAR(500) NOT NULL,
                direccion VARCHAR(1000) NOT NULL,
                nombre VARCHAR(255) NULL,
                curp VARCHAR(32) NULL,
                confianza VARCHAR(20) NULL,
                motor VARCHAR(40) NOT NULL,
                modelo VARCHAR(160) NULL,
                estado VARCHAR(32) NOT NULL DEFAULT 'valida',
                detalle_verificacion VARCHAR(500) NULL,
                fecha_extraccion DATETIME NOT NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (id_credito),
                KEY idx_credito_direccion_ine_actualizado (updated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );

        // La tabla pudo existir antes de incorporar el filtro de identidad.
        // La migracion es aditiva para no perder lecturas validas anteriores.
        foreach ([
            'estado' => "ALTER TABLE estado_cuenta.credito_direccion_ine_sabueso ADD COLUMN estado VARCHAR(32) NOT NULL DEFAULT 'valida' AFTER modelo",
            'detalle_verificacion' => "ALTER TABLE estado_cuenta.credito_direccion_ine_sabueso ADD COLUMN detalle_verificacion VARCHAR(500) NULL AFTER estado",
        ] as $columna => $sql) {
            $existe = $db->queryOne(
                "SELECT 1 AS existe
                 FROM information_schema.columns
                 WHERE table_schema = 'estado_cuenta'
                   AND table_name = 'credito_direccion_ine_sabueso'
                   AND column_name = :columna
                 LIMIT 1",
                ['columna' => $columna]
            );
            if (!$existe) {
                $db->CRUD($sql);
            }
        }
    }

    public static function obtenerPorHuella(int $idCredito, string $huellaFuente): ?array
    {
        if ($idCredito < 1 || $huellaFuente === '') {
            return null;
        }
        try {
            $db = new Database();
            self::tabla($db);
            $row = $db->queryOne(
                'SELECT id_credito, direccion, nombre, curp, confianza, motor, modelo, estado, detalle_verificacion, fecha_extraccion
                 FROM credito_direccion_ine_sabueso
                 WHERE id_credito = :id AND huella_fuente = :huella
                 LIMIT 1',
                ['id' => $idCredito, 'huella' => $huellaFuente]
            );
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function obtenerPorCredito(int $idCredito): ?array
    {
        if ($idCredito < 1) {
            return null;
        }
        try {
            $db = new Database();
            self::tabla($db);
            $row = $db->queryOne(
                'SELECT id_credito, direccion, nombre, curp, confianza, motor, modelo, estado, detalle_verificacion, fecha_extraccion
                 FROM credito_direccion_ine_sabueso
                 WHERE id_credito = :id
                 LIMIT 1',
                ['id' => $idCredito]
            );
            return is_array($row) ? $row : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function guardar(
        int $idCredito,
        string $huellaFuente,
        string $archivoFuente,
        string $direccion,
        ?string $nombre,
        ?string $curp,
        ?string $confianza,
        string $motor,
        ?string $modelo
    ): array {
        if ($idCredito < 1 || $huellaFuente === '' || trim($direccion) === '') {
            return self::resultado(false, 'Datos insuficientes para guardar la dirección del INE.');
        }
        try {
            $db = new Database();
            self::tabla($db);
            $ahora = date('Y-m-d H:i:s');
            $db->CRUD(
                "INSERT INTO credito_direccion_ine_sabueso
                    (id_credito, huella_fuente, archivo_fuente, direccion, nombre, curp, confianza, motor, modelo, estado, detalle_verificacion, fecha_extraccion, updated_at)
                 VALUES
                    (:id, :huella, :archivo, :direccion, :nombre, :curp, :confianza, :motor, :modelo, 'valida', NULL, :fecha, :fecha)
                 ON DUPLICATE KEY UPDATE
                    huella_fuente = VALUES(huella_fuente),
                    archivo_fuente = VALUES(archivo_fuente),
                    direccion = VALUES(direccion),
                    nombre = VALUES(nombre),
                    curp = VALUES(curp),
                    confianza = VALUES(confianza),
                    motor = VALUES(motor),
                    modelo = VALUES(modelo),
                    estado = 'valida',
                    detalle_verificacion = NULL,
                    fecha_extraccion = VALUES(fecha_extraccion),
                    updated_at = VALUES(updated_at)",
                [
                    'id' => $idCredito,
                    'huella' => $huellaFuente,
                    'archivo' => mb_substr($archivoFuente, 0, 500),
                    'direccion' => mb_substr(trim($direccion), 0, 1000),
                    'nombre' => $nombre !== null ? mb_substr(trim($nombre), 0, 255) : null,
                    'curp' => $curp !== null ? mb_substr(trim($curp), 0, 32) : null,
                    'confianza' => $confianza !== null ? mb_substr(trim($confianza), 0, 20) : null,
                    'motor' => mb_substr(trim($motor), 0, 40),
                    'modelo' => $modelo !== null ? mb_substr(trim($modelo), 0, 160) : null,
                    'fecha' => $ahora,
                ]
            );
            return self::resultado(true, 'Dirección del INE guardada.');
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo guardar la dirección del INE.', null, $e->getMessage());
        }
    }

    /** Guarda una lectura que no pertenece al cliente del credito para no usarla ni reintentarla en cada apertura. */
    public static function guardarIncompatible(
        int $idCredito,
        string $huellaFuente,
        string $archivoFuente,
        ?string $nombre,
        ?string $curp,
        string $motor,
        ?string $modelo,
        string $detalle
    ): array {
        if ($idCredito < 1 || $huellaFuente === '') {
            return self::resultado(false, 'Datos insuficientes para registrar la incompatibilidad del INE.');
        }
        try {
            $db = new Database();
            self::tabla($db);
            $ahora = date('Y-m-d H:i:s');
            $db->CRUD(
                "INSERT INTO credito_direccion_ine_sabueso
                    (id_credito, huella_fuente, archivo_fuente, direccion, nombre, curp, confianza, motor, modelo, estado, detalle_verificacion, fecha_extraccion, updated_at)
                 VALUES
                    (:id, :huella, :archivo, '', :nombre, :curp, NULL, :motor, :modelo, 'incompatible', :detalle, :fecha, :fecha)
                 ON DUPLICATE KEY UPDATE
                    huella_fuente = VALUES(huella_fuente),
                    archivo_fuente = VALUES(archivo_fuente),
                    direccion = '',
                    nombre = VALUES(nombre),
                    curp = VALUES(curp),
                    confianza = NULL,
                    motor = VALUES(motor),
                    modelo = VALUES(modelo),
                    estado = 'incompatible',
                    detalle_verificacion = VALUES(detalle_verificacion),
                    fecha_extraccion = VALUES(fecha_extraccion),
                    updated_at = VALUES(updated_at)",
                [
                    'id' => $idCredito,
                    'huella' => $huellaFuente,
                    'archivo' => mb_substr($archivoFuente, 0, 500),
                    'nombre' => $nombre !== null ? mb_substr(trim($nombre), 0, 255) : null,
                    'curp' => $curp !== null ? mb_substr(trim($curp), 0, 32) : null,
                    'motor' => mb_substr(trim($motor), 0, 40),
                    'modelo' => $modelo !== null ? mb_substr(trim($modelo), 0, 160) : null,
                    'detalle' => mb_substr(trim($detalle), 0, 500),
                    'fecha' => $ahora,
                ]
            );
            return self::resultado(true, 'INE incompatible registrado.');
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo registrar la incompatibilidad del INE.', null, $e->getMessage());
        }
    }
}
