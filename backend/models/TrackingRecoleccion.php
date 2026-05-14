<?php

namespace Models;

use Core\Model;
use Core\Database;

class TrackingRecoleccion extends Model
{
    /** @var Database */
    private $db;

    public function __construct()
    {
        $this->db = new Database();
        $this->asegurarTablas();
    }

    // =========================================================================
    // BOOTSTRAP: crear tablas si no existen (mismo patrón que adj_s2_cache_dictamen)
    // =========================================================================

    private static bool $tablasOk = false;

    private function asegurarTablas(): void
    {
        if (self::$tablasOk) {
            return;
        }
        try {
            $this->db->CRUD(
                "CREATE TABLE IF NOT EXISTS `asigna_horas_tracking` (
                    `id_ruta`             INT           NOT NULL AUTO_INCREMENT,
                    `nombre_ruta`         VARCHAR(100)  NOT NULL,
                    `estado`              VARCHAR(100)  NULL,
                    `municipio`           VARCHAR(100)  NULL,
                    `fecha_programada`    DATE          NOT NULL,
                    `estatus_ruta`        ENUM('borrador','pendiente_confirmacion','lista_envio','enviada','en_proceso','concluida','cancelada')
                                          NOT NULL DEFAULT 'borrador',
                    `creado_por`          INT           NULL,
                    `fecha_creacion`      DATETIME      NULL,
                    `fecha_actualizacion` DATETIME      NULL,
                    PRIMARY KEY (`id_ruta`),
                    KEY `idx_tracking_estatus`    (`estatus_ruta`),
                    KEY `idx_tracking_fecha`      (`fecha_programada`),
                    KEY `idx_tracking_estado_mun` (`estado`, `municipio`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
            $this->db->CRUD(
                "CREATE TABLE IF NOT EXISTS `asigna_horas_tracking_detalle` (
                    `id_detalle`                  INT           NOT NULL AUTO_INCREMENT,
                    `id_ruta`                     INT           NOT NULL,
                    `id_credito`                  INT           NULL,
                    `modelo`                      VARCHAR(100)  NULL,
                    `bin`                         VARCHAR(100)  NULL,
                    `estado`                      VARCHAR(100)  NULL,
                    `municipio`                   VARCHAR(100)  NULL,
                    `direccion`                   VARCHAR(200)  NULL,
                    `latitud`                     DECIMAL(10,7) NULL,
                    `longitud`                    DECIMAL(10,7) NULL,
                    `orden_ruta`                  INT           NULL DEFAULT 0,
                    `estatus_confirmacion_gestor` ENUM('pendiente','confirmado','rechazado','en_revision')
                                                  NOT NULL DEFAULT 'pendiente',
                    `estatus_recoleccion`         VARCHAR(50)   NULL,
                    PRIMARY KEY (`id_detalle`),
                    KEY `fk_tracking_det_ruta` (`id_ruta`),
                    KEY `idx_tracking_det_credito` (`id_credito`),
                    KEY `idx_tracking_det_orden`   (`id_ruta`, `orden_ruta`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
            // FK separado para evitar fallo si tabla padre aún no tenía PK en la misma transacción
            try {
                $this->db->CRUD(
                    "ALTER TABLE `asigna_horas_tracking_detalle`
                     ADD CONSTRAINT `fk_tracking_det_ruta_fk`
                     FOREIGN KEY (`id_ruta`) REFERENCES `asigna_horas_tracking` (`id_ruta`) ON DELETE CASCADE"
                );
            } catch (\Throwable $e) {
                // Ya existe o no se puede agregar (ignorar)
            }
            $this->db->CRUD(
                "CREATE TABLE IF NOT EXISTS `asigna_horas_tracking_usuarios` (
                    `id`          INT NOT NULL AUTO_INCREMENT,
                    `id_ruta`     INT NOT NULL,
                    `id_usuario`  INT NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `fk_tracking_usr_ruta` (`id_ruta`),
                    UNIQUE KEY `ux_tracking_usr` (`id_ruta`, `id_usuario`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
            try {
                $this->db->CRUD(
                    "ALTER TABLE `asigna_horas_tracking_usuarios`
                     ADD CONSTRAINT `fk_tracking_usr_ruta_fk`
                     FOREIGN KEY (`id_ruta`) REFERENCES `asigna_horas_tracking` (`id_ruta`) ON DELETE CASCADE"
                );
            } catch (\Throwable $e) {
                // Ya existe
            }
            self::$tablasOk = true;
        } catch (\Throwable $e) {
            // No bloquear la carga del módulo; la BD podría no tener permisos DDL
            self::$tablasOk = true;
        }
    }

    // =========================================================================
    // CRÉDITOS PASO 2
    // =========================================================================

    /**
     * Créditos de adj_operacion disponibles para asignar a rutas de recolección.
     * Filtrables por estado/municipio (log_estado / log_ciudad).
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerCreditosPaso2(?string $estado = null, ?string $municipio = null): array
    {
        $where  = ['1 = 1'];
        $params = [];

        // Excluir estatus terminales
        $where[] = "ao.estatus NOT IN ('cancelado','Cancelado','Cartera','concluida')";

        // RN-04: excluir créditos ya en una ruta activa (enviada / en_proceso)
        $where[] = "ao.id_credito NOT IN (
            SELECT COALESCE(atd.id_credito, 0)
            FROM asigna_horas_tracking_detalle atd
            INNER JOIN asigna_horas_tracking atr ON atr.id_ruta = atd.id_ruta
            WHERE atr.estatus_ruta IN ('enviada','en_proceso')
              AND atd.id_credito IS NOT NULL
        )";

        if ($estado !== null && $estado !== '') {
            $where[]            = 'ao.log_estado = :estado';
            $params['estado']   = $estado;
        }
        if ($municipio !== null && $municipio !== '') {
            $where[]              = 'ao.log_ciudad = :municipio';
            $params['municipio']  = $municipio;
        }

        $sql = 'SELECT
            ao.id             AS id_operacion,
            ao.id_credito,
            ao.nombre_cliente,
            ao.estatus        AS estatus_proceso,
            ao.moto_marca,
            ao.moto_modelo,
            ao.moto_no_serie  AS bin,
            ao.log_estado     AS estado,
            ao.log_ciudad     AS municipio,
            ao.log_direccion  AS direccion,
            ao.datos_moto_at,
            TRIM(CONCAT_WS(\' \', per.nombres, per.apellidop)) AS gestor_nombre
        FROM adj_operacion ao
        LEFT JOIN asigna_creditos_adjudicacion aca
            ON aca.id_credito = ao.id_credito AND aca.estatus = \'1\'
        LEFT JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
        LEFT JOIN persona per              ON per.id = pa.id_persona
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY ao.log_estado, ao.log_ciudad, ao.nombre_cliente';

        try {
            return $this->db->queryAll($sql, $params) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    // =========================================================================
    // CATÁLOGOS DE FILTROS
    // =========================================================================

    /**
     * Listado de estados (nivel 1, id_pais=1) desde divisiones_administrativas.
     *
     * @return array<int, string>
     */
    public function obtenerEstados(): array
    {
        try {
            $rows = $this->db->queryAll(
                "SELECT nombre
                 FROM divisiones_administrativas
                 WHERE id_pais = 1
                   AND nivel   = 1
                   AND activo  = 1
                 ORDER BY nombre"
            ) ?: [];
            return array_column($rows, 'nombre');
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Municipios (nivel 2) de un estado dado, buscando por nombre de estado.
     *
     * @return array<int, string>
     */
    public function obtenerMunicipiosPorEstado(string $estado): array
    {
        if (trim($estado) === '') {
            return [];
        }
        try {
            $rows = $this->db->queryAll(
                "SELECT da.nombre
                 FROM divisiones_administrativas da
                 INNER JOIN divisiones_administrativas padre
                        ON padre.id = da.id_padre
                       AND padre.nombre  = :estado
                       AND padre.id_pais = 1
                       AND padre.nivel   = 1
                 WHERE da.activo = 1
                 ORDER BY da.nombre",
                ['estado' => $estado]
            ) ?: [];
            return array_column($rows, 'nombre');
        } catch (\Throwable $e) {
            return [];
        }
    }

    // =========================================================================
    // USUARIOS RECOLECTORES
    // =========================================================================

    /**
     * Personas activas disponibles para asignar como recolectores.
     *
     * @return array<int, array{id:int, nombre:string}>
     */
    public function obtenerUsuariosRecoleccion(): array
    {
        try {
            $rows = $this->db->queryAll(
                "SELECT
                    p.id,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre
                 FROM persona p
                 WHERE p.estatus = 'Activo'
                   AND p.nombres IS NOT NULL
                   AND TRIM(p.nombres) <> ''
                 ORDER BY p.nombres, p.apellidop
                 LIMIT 500"
            ) ?: [];
            return $rows;
        } catch (\Throwable $e) {
            return [];
        }
    }

    // =========================================================================
    // RUTAS — GUARDAR (crear o actualizar)
    // =========================================================================

    /**
     * Crea o actualiza una ruta de recolección.
     *
     * @param  array{
     *   id_ruta?:int,
     *   nombre_ruta:string,
     *   estado:string,
     *   municipio:string,
     *   fecha_programada:string,
     *   usuarios:int[],
     *   creditos:array<int,array{id_credito:int,modelo:string,bin:string,estado:string,municipio:string,direccion:string,latitud?:float,longitud?:float,orden_ruta:int}>,
     *   modo:string
     * } $data
     * @return array{success:bool, id_ruta?:int, message?:string}
     */
    public function guardarRuta(array $data, int $idUsuario): array
    {
        $modo      = trim((string) ($data['modo'] ?? 'borrador'));
        $idRuta    = (int) ($data['id_ruta'] ?? 0);
        $nombre    = trim((string) ($data['nombre_ruta'] ?? ''));
        $estado    = trim((string) ($data['estado'] ?? ''));
        $municipio = trim((string) ($data['municipio'] ?? ''));
        $fechaStr  = trim((string) ($data['fecha_programada'] ?? ''));
        $usuarios  = is_array($data['usuarios'] ?? null) ? $data['usuarios'] : [];
        $creditos  = is_array($data['creditos'] ?? null) ? $data['creditos'] : [];

        // Validaciones comunes
        if ($nombre === '') {
            return ['success' => false, 'message' => 'El nombre de la ruta es obligatorio.'];
        }
        if (mb_strlen($nombre, 'UTF-8') > 100) {
            return ['success' => false, 'message' => 'El nombre de la ruta no puede exceder 100 caracteres.'];
        }
        if (empty($creditos)) {
            return ['success' => false, 'message' => 'Debe agregar al menos un crédito a la ruta.'];
        }
        if ($estado === '') {
            return ['success' => false, 'message' => 'El estado es obligatorio.'];
        }
        if ($municipio === '') {
            return ['success' => false, 'message' => 'El municipio es obligatorio.'];
        }

        // Validar fecha
        $fechaOk = $this->validarFechaProgramada($fechaStr);
        if ($fechaOk !== null) {
            return ['success' => false, 'message' => $fechaOk];
        }

        // Validaciones adicionales para enviar (no borrador)
        if ($modo !== 'borrador') {
            if (empty($usuarios)) {
                return ['success' => false, 'message' => 'Debe asignar al menos un usuario responsable para enviar la ruta.'];
            }
            // RN-06: todos los créditos deben estar confirmados
            foreach ($creditos as $det) {
                $conf = (string) ($det['estatus_confirmacion_gestor'] ?? 'pendiente');
                if ($conf !== 'confirmado') {
                    return [
                        'success' => false,
                        'message' => 'Todos los créditos deben tener confirmación del gestor antes de enviar la ruta.',
                    ];
                }
            }
        }

        // RN-03: no duplicados dentro del lote enviado
        $idsEnvio = array_column($creditos, 'id_credito');
        if (count($idsEnvio) !== count(array_unique($idsEnvio))) {
            return ['success' => false, 'message' => 'Hay créditos duplicados en la ruta.'];
        }

        $ahora        = date('Y-m-d H:i:s');
        $estatusRuta  = ($modo === 'borrador') ? 'borrador' : 'enviada';

        try {
            $this->db->beginTransaction();

            if ($idRuta > 0) {
                // Verificar que la ruta existe y pertenece al usuario o es editable
                $existente = $this->db->queryOne(
                    'SELECT id_ruta, estatus_ruta FROM asigna_horas_tracking WHERE id_ruta = :id LIMIT 1',
                    ['id' => $idRuta]
                );
                if (!$existente) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'Ruta no encontrada.'];
                }
                if (!in_array($existente['estatus_ruta'], ['borrador', 'pendiente_confirmacion', 'lista_envio'], true)) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'La ruta no puede modificarse en su estado actual.'];
                }
                $this->db->CRUD(
                    'UPDATE asigna_horas_tracking
                     SET nombre_ruta = :n, estado = :e, municipio = :m,
                         fecha_programada = :f, estatus_ruta = :er,
                         fecha_actualizacion = :fa
                     WHERE id_ruta = :id',
                    [
                        'n'  => $nombre,
                        'e'  => $estado,
                        'm'  => $municipio,
                        'f'  => $fechaStr,
                        'er' => $estatusRuta,
                        'fa' => $ahora,
                        'id' => $idRuta,
                    ]
                );
                // Limpiar y reinsertar detalle y usuarios
                $this->db->CRUD('DELETE FROM asigna_horas_tracking_detalle WHERE id_ruta = :id', ['id' => $idRuta]);
                $this->db->CRUD('DELETE FROM asigna_horas_tracking_usuarios WHERE id_ruta = :id', ['id' => $idRuta]);
            } else {
                $this->db->CRUD(
                    'INSERT INTO asigna_horas_tracking
                         (nombre_ruta, estado, municipio, fecha_programada, estatus_ruta, creado_por, fecha_creacion, fecha_actualizacion)
                     VALUES (:n, :e, :m, :f, :er, :cp, :fc, :fa)',
                    [
                        'n'  => $nombre,
                        'e'  => $estado,
                        'm'  => $municipio,
                        'f'  => $fechaStr,
                        'er' => $estatusRuta,
                        'cp' => $idUsuario ?: null,
                        'fc' => $ahora,
                        'fa' => $ahora,
                    ]
                );
                $idRuta = $this->db->lastInsertId();
                if ($idRuta <= 0) {
                    $this->db->rollback();
                    return ['success' => false, 'message' => 'No se pudo crear la ruta.'];
                }
            }

            // Insertar detalle de créditos
            foreach ($creditos as $i => $det) {
                $idCredito = (int) ($det['id_credito'] ?? 0);
                if ($idCredito <= 0) {
                    continue;
                }
                $lat = isset($det['latitud'])  && is_numeric($det['latitud'])  ? (float) $det['latitud']  : null;
                $lng = isset($det['longitud']) && is_numeric($det['longitud']) ? (float) $det['longitud'] : null;
                $this->db->CRUD(
                    'INSERT INTO asigna_horas_tracking_detalle
                         (id_ruta, id_credito, modelo, bin, estado, municipio, direccion, latitud, longitud, orden_ruta, estatus_confirmacion_gestor)
                     VALUES (:ir, :ic, :mo, :bi, :es, :mu, :di, :la, :lo, :or, :cf)',
                    [
                        'ir' => $idRuta,
                        'ic' => $idCredito,
                        'mo' => mb_substr(trim((string) ($det['modelo']    ?? '')), 0, 100),
                        'bi' => mb_substr(trim((string) ($det['bin']       ?? '')), 0, 100),
                        'es' => mb_substr(trim((string) ($det['estado']    ?? '')), 0, 100),
                        'mu' => mb_substr(trim((string) ($det['municipio'] ?? '')), 0, 100),
                        'di' => mb_substr(trim((string) ($det['direccion'] ?? '')), 0, 200),
                        'la' => $lat,
                        'lo' => $lng,
                        'or' => (int) ($det['orden_ruta'] ?? ($i + 1)),
                        'cf' => $this->sanitizarEstatusConfirmacion((string) ($det['estatus_confirmacion_gestor'] ?? 'pendiente')),
                    ]
                );
            }

            // Insertar usuarios responsables
            foreach ($usuarios as $uid) {
                $uid = (int) $uid;
                if ($uid <= 0) {
                    continue;
                }
                $this->db->CRUD(
                    'INSERT IGNORE INTO asigna_horas_tracking_usuarios (id_ruta, id_usuario) VALUES (:ir, :iu)',
                    ['ir' => $idRuta, 'iu' => $uid]
                );
            }

            $this->db->commit();
            return ['success' => true, 'id_ruta' => $idRuta];
        } catch (\Throwable $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Error al guardar la ruta: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // RUTAS — LISTAR
    // =========================================================================

    /**
     * Listado de rutas con resumen de créditos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function obtenerRutas(?string $estado = null, ?string $municipio = null): array
    {
        $where  = ['1 = 1'];
        $params = [];
        if ($estado !== null && $estado !== '') {
            $where[]          = 'atr.estado = :estado';
            $params['estado'] = $estado;
        }
        if ($municipio !== null && $municipio !== '') {
            $where[]              = 'atr.municipio = :municipio';
            $params['municipio']  = $municipio;
        }

        $sql = 'SELECT
            atr.id_ruta,
            atr.nombre_ruta,
            atr.estado,
            atr.municipio,
            DATE_FORMAT(atr.fecha_programada, \'%d/%m/%Y\') AS fecha_programada_fmt,
            atr.fecha_programada,
            atr.estatus_ruta,
            atr.creado_por,
            DATE_FORMAT(atr.fecha_creacion, \'%d/%m/%Y %H:%i\') AS fecha_creacion_fmt,
            COUNT(atd.id_detalle) AS total_creditos,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'confirmado\'  THEN 1 ELSE 0 END) AS confirmados,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'rechazado\'   THEN 1 ELSE 0 END) AS rechazados,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'pendiente\'   THEN 1 ELSE 0 END) AS pendientes,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'en_revision\' THEN 1 ELSE 0 END) AS en_revision
        FROM asigna_horas_tracking atr
        LEFT JOIN asigna_horas_tracking_detalle atd ON atd.id_ruta = atr.id_ruta
        WHERE ' . implode(' AND ', $where) . '
        GROUP BY atr.id_ruta
        ORDER BY atr.fecha_creacion DESC';

        try {
            $rutas = $this->db->queryAll($sql, $params) ?: [];
            // Enriquecer con usuarios responsables (compacto: nombres concatenados)
            foreach ($rutas as &$r) {
                $r['usuarios_responsables'] = $this->obtenerNombresUsuariosRuta((int) $r['id_ruta']);
            }
            unset($r);
            return $rutas;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Detalle completo de una ruta (cabecera + créditos + usuarios).
     *
     * @return array<string, mixed>|null
     */
    public function obtenerDetalleRuta(int $idRuta): ?array
    {
        if ($idRuta <= 0) {
            return null;
        }
        try {
            $cabecera = $this->db->queryOne(
                "SELECT
                    atr.*,
                    DATE_FORMAT(atr.fecha_programada, '%d/%m/%Y') AS fecha_programada_fmt,
                    DATE_FORMAT(atr.fecha_creacion,   '%d/%m/%Y %H:%i') AS fecha_creacion_fmt
                 FROM asigna_horas_tracking atr
                 WHERE atr.id_ruta = :id
                 LIMIT 1",
                ['id' => $idRuta]
            );
            if (!$cabecera) {
                return null;
            }
            $detalles = $this->db->queryAll(
                "SELECT
                    atd.*,
                    ao.nombre_cliente,
                    ao.estatus AS estatus_proceso,
                    TRIM(CONCAT_WS(' ', per.nombres, per.apellidop)) AS gestor_nombre
                 FROM asigna_horas_tracking_detalle atd
                 LEFT JOIN adj_operacion ao ON ao.id_credito = atd.id_credito
                 LEFT JOIN asigna_creditos_adjudicacion aca
                     ON aca.id_credito = atd.id_credito AND aca.estatus = '1'
                 LEFT JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
                 LEFT JOIN persona per              ON per.id = pa.id_persona
                 WHERE atd.id_ruta = :id
                 ORDER BY atd.orden_ruta ASC, atd.id_detalle ASC",
                ['id' => $idRuta]
            ) ?: [];
            $usuarios = $this->db->queryAll(
                "SELECT atu.id_usuario,
                        TRIM(CONCAT_WS(' ', per.nombres, per.apellidop)) AS nombre_usuario
                 FROM asigna_horas_tracking_usuarios atu
                 LEFT JOIN persona per ON per.id = atu.id_usuario
                 WHERE atu.id_ruta = :id",
                ['id' => $idRuta]
            ) ?: [];
            $cabecera['detalle']  = $detalles;
            $cabecera['usuarios'] = $usuarios;
            return $cabecera;
        } catch (\Throwable $e) {
            return null;
        }
    }

    // =========================================================================
    // ACTUALIZAR CONFIRMACIÓN DEL GESTOR (desde vista interna)
    // =========================================================================

    /**
     * Actualiza el estatus de confirmación del gestor para un crédito en una ruta.
     *
     * @return array{success:bool, message?:string}
     */
    public function actualizarConfirmacionGestor(int $idRuta, int $idCredito, string $nuevoEstatus): array
    {
        $estatus = $this->sanitizarEstatusConfirmacion($nuevoEstatus);
        if ($estatus === '') {
            return ['success' => false, 'message' => 'Estatus de confirmación no válido.'];
        }
        try {
            $n = (int) $this->db->CRUD(
                'UPDATE asigna_horas_tracking_detalle
                 SET estatus_confirmacion_gestor = :e
                 WHERE id_ruta = :ir AND id_credito = :ic',
                ['e' => $estatus, 'ir' => $idRuta, 'ic' => $idCredito]
            );
            if ($n === 0) {
                return ['success' => false, 'message' => 'Crédito no encontrado en la ruta.'];
            }
            return ['success' => true];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error al actualizar confirmación.'];
        }
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function validarFechaProgramada(string $fechaStr): ?string
    {
        if ($fechaStr === '') {
            return 'La fecha programada es obligatoria.';
        }
        $fecha = \DateTime::createFromFormat('Y-m-d', $fechaStr);
        if ($fecha === false) {
            return 'Formato de fecha no válido. Use YYYY-MM-DD.';
        }
        $hoy      = new \DateTime('today');
        $minFecha = (clone $hoy)->modify('+2 days');
        if ($fecha < $minFecha) {
            return 'La fecha programada debe ser al menos 2 días después de hoy.';
        }
        return null;
    }

    private function sanitizarEstatusConfirmacion(string $v): string
    {
        $permitidos = ['pendiente', 'confirmado', 'rechazado', 'en_revision'];
        $v = strtolower(trim($v));
        return in_array($v, $permitidos, true) ? $v : '';
    }

    /**
     * @return string Nombres concatenados de usuarios responsables de una ruta
     */
    private function obtenerNombresUsuariosRuta(int $idRuta): string
    {
        try {
            $rows = $this->db->queryAll(
                "SELECT TRIM(CONCAT_WS(' ', per.nombres, per.apellidop)) AS nombre
                 FROM asigna_horas_tracking_usuarios atu
                 LEFT JOIN persona per ON per.id = atu.id_usuario
                 WHERE atu.id_ruta = :id",
                ['id' => $idRuta]
            ) ?: [];
            return implode(', ', array_column($rows, 'nombre'));
        } catch (\Throwable $e) {
            return '';
        }
    }
}
