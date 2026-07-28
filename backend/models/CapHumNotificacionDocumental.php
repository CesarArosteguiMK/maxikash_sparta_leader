<?php

namespace Models;

use Core\Database;
use Core\Model;

/**
 * Campañas obligatorias de documentos para Capital Humano.
 *
 * La primera campaña disponible es la constancia de semanas cotizadas IMSS
 * (segundos patrones), con dos entregas independientes por año.
 */
class CapHumNotificacionDocumental extends Model
{
    public const TIPO_SEMANAS_COTIZADAS = 'semanas_cotizadas';
    public const DOCUMENTO_SEMANAS_COTIZADAS = 33;
    public const URL_IMSS_SEMANAS_COTIZADAS = 'https://serviciosdigitales.imss.gob.mx/semanascotizadas-web/usuarios/IngresoAsegurado';

    private static bool $tablasAseguradas = false;

    public static function asegurarTablas(): void
    {
        if (self::$tablasAseguradas) {
            return;
        }

        $db = new Database();
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_notificacion_documental_campania (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                tipo VARCHAR(60) NOT NULL,
                anio SMALLINT UNSIGNED NOT NULL,
                semestre TINYINT UNSIGNED NOT NULL,
                titulo VARCHAR(180) NOT NULL,
                mensaje TEXT NOT NULL,
                url_descarga VARCHAR(500) NOT NULL,
                codigo_pais VARCHAR(8) NOT NULL DEFAULT 'mx',
                obligatoria TINYINT(1) NOT NULL DEFAULT 1,
                activa TINYINT(1) NOT NULL DEFAULT 1,
                fecha_inicio DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_limite DATETIME NULL,
                creado_por INT NULL,
                actualizado_por INT NULL,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_rrhh_notif_tipo_periodo_pais (tipo, anio, semestre, codigo_pais),
                KEY idx_rrhh_notif_activa (activa, codigo_pais, fecha_inicio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS estado_cuenta.rrhh_notificacion_documental_entrega (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_campania BIGINT UNSIGNED NOT NULL,
                id_persona INT NOT NULL,
                id_documento_carga INT NOT NULL,
                nombre_logico VARCHAR(220) NOT NULL,
                nombre_original VARCHAR(255) NOT NULL,
                archivo VARCHAR(255) NOT NULL,
                tamanio_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
                sha256 CHAR(64) NOT NULL,
                cargado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_rrhh_notif_entrega_persona (id_campania, id_persona),
                UNIQUE KEY uq_rrhh_notif_entrega_documento (id_documento_carga),
                KEY idx_rrhh_notif_entrega_persona (id_persona, cargado_en),
                KEY idx_rrhh_notif_entrega_campania (id_campania, cargado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::$tablasAseguradas = true;
    }

    public static function catalogoTipos(): array
    {
        return [[
            'clave' => self::TIPO_SEMANAS_COTIZADAS,
            'nombre' => 'Semanas cotizadas IMSS (segundos patrones)',
            'documento_id' => self::DOCUMENTO_SEMANAS_COTIZADAS,
            'url_descarga' => self::URL_IMSS_SEMANAS_COTIZADAS,
        ]];
    }

    public static function nombrePeriodo(int $anio, int $semestre): string
    {
        return sprintf('Semanas cotizadas %d - %d semestre', $anio, $semestre);
    }

    private static function validarPeriodo(int $anio, int $semestre): void
    {
        if ($anio < 2020 || $anio > 2100) {
            throw new \InvalidArgumentException('El año debe estar entre 2020 y 2100.');
        }
        if (!in_array($semestre, [1, 2], true)) {
            throw new \InvalidArgumentException('El semestre debe ser 1 o 2.');
        }
    }

    private static function mensajePredeterminado(int $anio, int $semestre): string
    {
        return 'Capital Humano solicita tu constancia detallada de semanas cotizadas del IMSS correspondiente a '
            . $anio . ', ' . $semestre . ' semestre. Este documento es obligatorio para mantener actualizado '
            . 'tu expediente laboral. Descárgalo desde el portal oficial del IMSS y súbelo en formato PDF.';
    }

    private static function condicionPersonaElegible(string $alias = 'p', string $aliasPais = 'pa'): string
    {
        return "
            LOWER(TRIM(COALESCE({$alias}.estatus, 'activo'))) NOT IN ('baja', 'transito de baja', 'inactivo')
            AND COALESCE({$alias}.es_externo, 0) = 0
            AND NULLIF(TRIM(COALESCE({$alias}.user_name, '')), '') IS NOT NULL
            AND LOWER(TRIM(COALESCE({$aliasPais}.codigo_iso, 'mx'))) = LOWER(TRIM(c.codigo_pais))
        ";
    }

    public static function guardarCampania(array $datos, int $idUsuario): array
    {
        try {
            self::asegurarTablas();
            $tipo = trim((string)($datos['tipo'] ?? ''));
            if ($tipo !== self::TIPO_SEMANAS_COTIZADAS) {
                return self::resultado(false, 'Tipo de notificación no disponible.');
            }

            $anio = (int)($datos['anio'] ?? 0);
            $semestre = (int)($datos['semestre'] ?? 0);
            self::validarPeriodo($anio, $semestre);
            $titulo = trim((string)($datos['titulo'] ?? ''));
            if ($titulo === '') {
                $titulo = self::nombrePeriodo($anio, $semestre);
            }
            $mensaje = trim((string)($datos['mensaje'] ?? ''));
            if ($mensaje === '') {
                $mensaje = self::mensajePredeterminado($anio, $semestre);
            }
            $url = trim((string)($datos['url_descarga'] ?? ''));
            if ($url === '') {
                $url = self::URL_IMSS_SEMANAS_COTIZADAS;
            }
            if (!filter_var($url, FILTER_VALIDATE_URL) || stripos($url, 'https://') !== 0) {
                return self::resultado(false, 'La liga de descarga debe ser una URL HTTPS válida.');
            }
            $fechaLimite = trim((string)($datos['fecha_limite'] ?? ''));
            if ($fechaLimite !== '') {
                $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $fechaLimite);
                if (!$dt || $dt->format('Y-m-d') !== $fechaLimite) {
                    return self::resultado(false, 'La fecha límite no es válida.');
                }
                $fechaLimite .= ' 23:59:59';
            } else {
                $fechaLimite = null;
            }
            $activa = !empty($datos['activa']) ? 1 : 0;

            $db = new Database();
            $db->CRUD("
                INSERT INTO estado_cuenta.rrhh_notificacion_documental_campania
                    (tipo, anio, semestre, titulo, mensaje, url_descarga, codigo_pais,
                     obligatoria, activa, fecha_inicio, fecha_limite, creado_por, actualizado_por)
                VALUES
                    (:tipo, :anio, :semestre, :titulo, :mensaje, :url, 'mx',
                     1, :activa, NOW(), :fecha_limite, :usuario, :usuario)
                ON DUPLICATE KEY UPDATE
                    id = LAST_INSERT_ID(id),
                    titulo = VALUES(titulo),
                    mensaje = VALUES(mensaje),
                    url_descarga = VALUES(url_descarga),
                    obligatoria = 1,
                    fecha_inicio = IF(VALUES(activa) = 1 AND activa = 0, NOW(), fecha_inicio),
                    activa = VALUES(activa),
                    fecha_limite = VALUES(fecha_limite),
                    actualizado_por = VALUES(actualizado_por),
                    actualizado_en = NOW()
            ", [
                'tipo' => $tipo,
                'anio' => $anio,
                'semestre' => $semestre,
                'titulo' => mb_substr($titulo, 0, 180),
                'mensaje' => mb_substr($mensaje, 0, 5000),
                'url' => mb_substr($url, 0, 500),
                'activa' => $activa,
                'fecha_limite' => $fechaLimite,
                'usuario' => $idUsuario > 0 ? $idUsuario : null,
            ]);
            $id = $db->lastInsertId();
            if ($id <= 0) {
                $fila = $db->queryOne("
                    SELECT id
                    FROM estado_cuenta.rrhh_notificacion_documental_campania
                    WHERE tipo = :tipo AND anio = :anio AND semestre = :semestre AND codigo_pais = 'mx'
                    LIMIT 1
                ", ['tipo' => $tipo, 'anio' => $anio, 'semestre' => $semestre]);
                $id = (int)($fila['id'] ?? 0);
            }

            return self::resultado(true, $activa
                ? 'Notificación obligatoria activada.'
                : 'Campaña guardada como inactiva.', ['id' => $id]);
        } catch (\InvalidArgumentException $e) {
            return self::resultado(false, $e->getMessage());
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo guardar la campaña documental.', null, $e->getMessage());
        }
    }

    public static function cambiarEstadoCampania(int $idCampania, bool $activa, int $idUsuario): array
    {
        try {
            self::asegurarTablas();
            if ($idCampania <= 0) {
                return self::resultado(false, 'Campaña no válida.');
            }
            $db = new Database();
            $afectadas = $db->CRUD("
                UPDATE estado_cuenta.rrhh_notificacion_documental_campania
                SET fecha_inicio = IF(:activa = 1 AND activa = 0, NOW(), fecha_inicio),
                    activa = :activa,
                    actualizado_por = :usuario,
                    actualizado_en = NOW()
                WHERE id = :id
            ", [
                'activa' => $activa ? 1 : 0,
                'usuario' => $idUsuario > 0 ? $idUsuario : null,
                'id' => $idCampania,
            ]);
            if ($afectadas < 1) {
                return self::resultado(false, 'No se encontró la campaña.');
            }
            return self::resultado(true, $activa ? 'Campaña activada.' : 'Campaña pausada.');
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo cambiar el estado de la campaña.', null, $e->getMessage());
        }
    }

    public static function listarCampanias(): array
    {
        try {
            self::asegurarTablas();
            $db = new Database();
            $condicion = self::condicionPersonaElegible();
            $rows = $db->queryAll("
                SELECT
                    c.id,
                    c.tipo,
                    c.anio,
                    c.semestre,
                    c.titulo,
                    c.mensaje,
                    c.url_descarga,
                    c.codigo_pais,
                    c.obligatoria,
                    c.activa,
                    DATE_FORMAT(c.fecha_inicio, '%Y-%m-%d %H:%i') AS fecha_inicio,
                    DATE_FORMAT(c.fecha_limite, '%Y-%m-%d') AS fecha_limite,
                    DATE_FORMAT(c.creado_en, '%Y-%m-%d %H:%i') AS creado_en,
                    COUNT(DISTINCT p.id) AS total_personas,
                    COUNT(DISTINCT e.id_persona) AS entregados,
                    GREATEST(COUNT(DISTINCT p.id) - COUNT(DISTINCT e.id_persona), 0) AS pendientes
                FROM estado_cuenta.rrhh_notificacion_documental_campania c
                LEFT JOIN estado_cuenta.persona p ON 1 = 1
                LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
                LEFT JOIN estado_cuenta.rrhh_notificacion_documental_entrega e
                    ON e.id_campania = c.id AND e.id_persona = p.id
                WHERE {$condicion}
                GROUP BY c.id
                ORDER BY c.anio DESC, c.semestre DESC, c.id DESC
            ");
            return self::resultado(true, 'Campañas encontradas.', $rows ?: []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudieron consultar las campañas.', [], $e->getMessage());
        }
    }

    public static function personasCampania(int $idCampania, string $estado = 'todos', string $buscar = ''): array
    {
        try {
            self::asegurarTablas();
            if ($idCampania <= 0) {
                return self::resultado(false, 'Campaña no válida.', []);
            }
            $estado = in_array($estado, ['todos', 'pendientes', 'entregados'], true) ? $estado : 'todos';
            $params = ['campania' => $idCampania];
            $filtroEstado = '';
            if ($estado === 'pendientes') {
                $filtroEstado = ' AND e.id IS NULL';
            } elseif ($estado === 'entregados') {
                $filtroEstado = ' AND e.id IS NOT NULL';
            }
            $filtroBuscar = '';
            $buscar = trim($buscar);
            if ($buscar !== '') {
                $filtroBuscar = " AND (
                    p.numero_empleado LIKE :buscar
                    OR p.user_name LIKE :buscar
                    OR CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) LIKE :buscar
                )";
                $params['buscar'] = '%' . mb_substr($buscar, 0, 80) . '%';
            }

            $db = new Database();
            $condicion = self::condicionPersonaElegible();
            $rows = $db->queryAll("
                SELECT
                    p.id AS id_persona,
                    p.numero_empleado,
                    p.user_name,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                    p.correo,
                    COALESCE(p.estatus, '') AS estatus,
                    CASE WHEN e.id IS NULL THEN 'Pendiente' ELSE 'Entregado' END AS estado_entrega,
                    e.nombre_logico,
                    e.nombre_original,
                    DATE_FORMAT(e.cargado_en, '%Y-%m-%d %H:%i') AS cargado_en
                FROM estado_cuenta.rrhh_notificacion_documental_campania c
                INNER JOIN estado_cuenta.persona p ON 1 = 1
                LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
                LEFT JOIN estado_cuenta.rrhh_notificacion_documental_entrega e
                    ON e.id_campania = c.id AND e.id_persona = p.id
                WHERE c.id = :campania
                  AND {$condicion}
                  {$filtroEstado}
                  {$filtroBuscar}
                ORDER BY (e.id IS NULL) DESC, nombre ASC
                LIMIT 1000
            ", $params);
            return self::resultado(true, 'Personas encontradas.', $rows ?: []);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo consultar el avance de la campaña.', [], $e->getMessage());
        }
    }

    private static function obtenerPersonaElegible(Database $db, int $idPersona, string $codigoPais): ?array
    {
        return $db->queryOne("
            SELECT
                p.id,
                p.numero_empleado,
                p.user_name,
                TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                LOWER(TRIM(COALESCE(pa.codigo_iso, 'mx'))) AS codigo_pais
            FROM estado_cuenta.persona p
            LEFT JOIN estado_cuenta.paises pa ON pa.id = p.id_pais
            WHERE p.id = :id
              AND LOWER(TRIM(COALESCE(p.estatus, 'activo'))) NOT IN ('baja', 'transito de baja', 'inactivo')
              AND COALESCE(p.es_externo, 0) = 0
              AND NULLIF(TRIM(COALESCE(p.user_name, '')), '') IS NOT NULL
              AND LOWER(TRIM(COALESCE(pa.codigo_iso, 'mx'))) = LOWER(TRIM(:pais))
            LIMIT 1
        ", ['id' => $idPersona, 'pais' => $codigoPais]);
    }

    public static function obtenerPendientePersona(int $idPersona): array
    {
        try {
            self::asegurarTablas();
            if ($idPersona <= 0) {
                return self::resultado(true, 'Sin persona asociada.', null);
            }
            $db = new Database();
            $campania = $db->queryOne("
                SELECT
                    c.id,
                    c.tipo,
                    c.anio,
                    c.semestre,
                    c.titulo,
                    c.mensaje,
                    c.url_descarga,
                    c.codigo_pais,
                    c.obligatoria,
                    DATE_FORMAT(c.fecha_limite, '%Y-%m-%d') AS fecha_limite
                FROM estado_cuenta.rrhh_notificacion_documental_campania c
                LEFT JOIN estado_cuenta.rrhh_notificacion_documental_entrega e
                    ON e.id_campania = c.id AND e.id_persona = :persona
                WHERE c.activa = 1
                  AND c.obligatoria = 1
                  AND c.fecha_inicio <= NOW()
                  AND e.id IS NULL
                ORDER BY c.anio ASC, c.semestre ASC, c.id ASC
                LIMIT 1
            ", ['persona' => $idPersona]);
            if (!$campania) {
                return self::resultado(true, 'No hay documentos obligatorios pendientes.', null);
            }
            $persona = self::obtenerPersonaElegible($db, $idPersona, (string)$campania['codigo_pais']);
            if (!$persona) {
                return self::resultado(true, 'La campaña no aplica a esta persona.', null);
            }
            $campania['nombre_documento'] = self::nombrePeriodo(
                (int)$campania['anio'],
                (int)$campania['semestre']
            );
            $campania['formatos_aceptados'] = ['PDF'];
            $campania['tamanio_maximo_mb'] = 15;
            return self::resultado(true, 'Documento obligatorio pendiente.', $campania);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo comprobar la obligación documental.', null, $e->getMessage());
        }
    }

    public static function registrarEntrega(
        int $idCampania,
        int $idPersona,
        string $archivo,
        string $nombreOriginal,
        int $tamanio,
        string $sha256
    ): array {
        try {
            self::asegurarTablas();
            $db = new Database();
            $campania = $db->queryOne("
                SELECT id, tipo, anio, semestre, codigo_pais, activa, obligatoria, fecha_inicio
                FROM estado_cuenta.rrhh_notificacion_documental_campania
                WHERE id = :id
                LIMIT 1
            ", ['id' => $idCampania]);
            if (!$campania || (int)$campania['activa'] !== 1 || (int)$campania['obligatoria'] !== 1) {
                return self::resultado(false, 'La solicitud ya no está activa.');
            }
            if (strtotime((string)$campania['fecha_inicio']) > time()) {
                return self::resultado(false, 'La solicitud todavía no ha iniciado.');
            }
            if ((string)$campania['tipo'] !== self::TIPO_SEMANAS_COTIZADAS) {
                return self::resultado(false, 'Tipo de documento no compatible.');
            }
            if (!self::obtenerPersonaElegible($db, $idPersona, (string)$campania['codigo_pais'])) {
                return self::resultado(false, 'Esta obligación no corresponde al colaborador.');
            }
            $existente = $db->queryOne("
                SELECT id, nombre_logico, cargado_en
                FROM estado_cuenta.rrhh_notificacion_documental_entrega
                WHERE id_campania = :campania AND id_persona = :persona
                LIMIT 1
            ", ['campania' => $idCampania, 'persona' => $idPersona]);
            if ($existente) {
                return self::resultado(true, 'El documento de este periodo ya fue entregado.', [
                    'ya_entregado' => true,
                    'nombre_documento' => $existente['nombre_logico'],
                ]);
            }

            $nombreLogico = self::nombrePeriodo((int)$campania['anio'], (int)$campania['semestre']);
            $db->beginTransaction();
            try {
                $db->CRUD("
                    INSERT INTO estado_cuenta.carga_documento_persona
                        (id_persona, id_documento, archivo, fecha_carga)
                    VALUES (:persona, :documento, :archivo, NOW())
                ", [
                    'persona' => $idPersona,
                    'documento' => self::DOCUMENTO_SEMANAS_COTIZADAS,
                    'archivo' => $archivo,
                ]);
                $idDocumentoCarga = $db->lastInsertId();
                if ($idDocumentoCarga <= 0) {
                    throw new \RuntimeException('No se obtuvo el identificador del documento.');
                }
                $db->CRUD("
                    INSERT INTO estado_cuenta.rrhh_notificacion_documental_entrega
                        (id_campania, id_persona, id_documento_carga, nombre_logico,
                         nombre_original, archivo, tamanio_bytes, sha256, cargado_en)
                    VALUES
                        (:campania, :persona, :documento_carga, :nombre_logico,
                         :nombre_original, :archivo, :tamanio, :sha256, NOW())
                ", [
                    'campania' => $idCampania,
                    'persona' => $idPersona,
                    'documento_carga' => $idDocumentoCarga,
                    'nombre_logico' => $nombreLogico,
                    'nombre_original' => mb_substr($nombreOriginal, 0, 255),
                    'archivo' => $archivo,
                    'tamanio' => max(0, $tamanio),
                    'sha256' => strtolower($sha256),
                ]);
                $db->commit();
            } catch (\Throwable $e) {
                if ($db->inTransaction()) {
                    $db->rollback();
                }
                throw $e;
            }

            return self::resultado(true, 'Documento recibido correctamente.', [
                'ya_entregado' => false,
                'nombre_documento' => $nombreLogico,
                'id_documento_carga' => $idDocumentoCarga,
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo registrar la entrega documental.', null, $e->getMessage());
        }
    }
}
