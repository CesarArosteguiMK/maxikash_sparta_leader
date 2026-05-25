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
                    `motivo_cancelacion`  VARCHAR(200)  NULL,
                    `fecha_cancelacion`   DATETIME      NULL,
                    `cancelado_por`       INT           NULL,
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
            $this->asegurarCatalogosTracking();
            $this->asegurarColumnasTransportistaRuta();
            $this->asegurarColumnasCancelacionRuta();
            self::$tablasOk = true;
        } catch (\Throwable $e) {
            // No bloquear la carga del módulo; la BD podría no tener permisos DDL
            self::$tablasOk = true;
        }
    }

    private function asegurarCatalogosTracking(): void
    {
        try {
            $this->db->CRUD(
                "CREATE TABLE IF NOT EXISTS `agencias_tracking` (
                    `id_agencia` INT NOT NULL AUTO_INCREMENT,
                    `clave_agencia` VARCHAR(80) NOT NULL,
                    `nombre_agencia` VARCHAR(150) NOT NULL,
                    `tipo_ubicacion` ENUM('agencia','almacen_llegada') NOT NULL DEFAULT 'agencia',
                    `direccion` VARCHAR(255) NULL,
                    `estado` VARCHAR(100) NULL,
                    `municipio` VARCHAR(120) NULL,
                    `codigo_postal` VARCHAR(10) NULL,
                    `latitud` DECIMAL(11,8) NULL,
                    `longitud` DECIMAL(11,8) NULL,
                    `link_ubicacion` TEXT NULL,
                    `telefono` VARCHAR(30) NULL,
                    `extension` VARCHAR(20) NULL,
                    `encargado` VARCHAR(150) NULL,
                    `email` VARCHAR(150) NULL,
                    `horario` TEXT NULL,
                    `activo` TINYINT(1) NOT NULL DEFAULT 1,
                    `fecha_alta` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id_agencia`),
                    UNIQUE KEY `ux_agencias_tracking_clave` (`clave_agencia`),
                    KEY `idx_agencias_tracking_estado_municipio` (`estado`, `municipio`),
                    KEY `idx_agencias_tracking_tipo_activo` (`tipo_ubicacion`, `activo`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
            $this->db->CRUD(
                "CREATE TABLE IF NOT EXISTS `transportistas_tracking` (
                    `id_transportista` INT NOT NULL AUTO_INCREMENT,
                    `id_agencia` INT NULL,
                    `tipo_transportista` ENUM('interno','externo') NOT NULL,
                    `nombre_transportista` VARCHAR(180) NOT NULL,
                    `curp_rfc` VARCHAR(25) NULL,
                    `email` VARCHAR(150) NULL,
                    `telefono` VARCHAR(30) NULL,
                    `empresa_origen` VARCHAR(180) NULL,
                    `puesto` VARCHAR(120) NULL,
                    `activo` TINYINT(1) NOT NULL DEFAULT 1,
                    `fecha_alta` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `fecha_actualizacion` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id_transportista`),
                    UNIQUE KEY `ux_transportistas_tracking_curp_rfc` (`curp_rfc`),
                    KEY `idx_transportistas_tracking_agencia` (`id_agencia`),
                    KEY `idx_transportistas_tracking_tipo` (`tipo_transportista`, `activo`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
        } catch (\Throwable $e) {
            // El catÃ¡logo puede ser creado manualmente por migraciÃ³n.
        }
    }

    private function asegurarColumnasTransportistaRuta(): void
    {
        $cols = [
            'tipo_transportista'  => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `tipo_transportista` ENUM('interno','externo') NULL AFTER `act_hora_1`",
            'id_transportista'    => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `id_transportista` INT NULL AFTER `tipo_transportista`",
            'id_agencia_tracking' => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `id_agencia_tracking` INT NULL AFTER `id_transportista`",
        ];
        foreach ($cols as $col => $sql) {
            if (!$this->columnaExiste('asigna_horas_tracking', $col)) {
                try { $this->db->CRUD($sql); } catch (\Throwable $e) {}
            }
        }
        try { $this->db->CRUD("ALTER TABLE `asigna_horas_tracking` ADD KEY `idx_tracking_transportista` (`tipo_transportista`, `id_transportista`)"); } catch (\Throwable $e) {}
        try { $this->db->CRUD("ALTER TABLE `asigna_horas_tracking` ADD KEY `idx_tracking_agencia_tracking` (`id_agencia_tracking`)"); } catch (\Throwable $e) {}
    }

    private function asegurarColumnasCancelacionRuta(): void
    {
        $cols = [
            'motivo_cancelacion' => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `motivo_cancelacion` VARCHAR(200) NULL AFTER `estatus_ruta`",
            'fecha_cancelacion'  => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `fecha_cancelacion` DATETIME NULL AFTER `motivo_cancelacion`",
            'cancelado_por'      => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `cancelado_por` INT NULL AFTER `fecha_cancelacion`",
        ];
        foreach ($cols as $col => $sql) {
            if (!$this->columnaExiste('asigna_horas_tracking', $col)) {
                try { $this->db->CRUD($sql); } catch (\Throwable $e) {}
            }
        }
    }

    private function columnaExiste(string $tabla, string $columna): bool
    {
        try {
            $row = $this->db->queryOne(
                "SELECT 1 AS existe
                 FROM INFORMATION_SCHEMA.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :tabla
                   AND COLUMN_NAME = :columna
                 LIMIT 1",
                ['tabla' => $tabla, 'columna' => $columna]
            );
            return (bool) $row;
        } catch (\Throwable $e) {
            return true;
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

        // RN-04: excluir créditos ya en una ruta activa (borrador / enviada / en_proceso)
        $where[] = "ao.id_credito NOT IN (
            SELECT COALESCE(atd.id_credito, 0)
            FROM asigna_horas_tracking_detalle atd
            INNER JOIN asigna_horas_tracking atr ON atr.id_ruta = atd.id_ruta
            WHERE atr.estatus_ruta IN ('borrador','pendiente_confirmacion','lista_envio','enviada','en_proceso')
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
    // CATÃLOGOS TRACKING: AGENCIAS / TRANSPORTISTAS
    // =========================================================================

    public function obtenerAgenciasTracking(): array
    {
        try {
            return $this->db->queryAll(
                "SELECT id_agencia, clave_agencia, nombre_agencia, tipo_ubicacion,
                        direccion, estado, municipio, codigo_postal,
                        latitud, longitud, link_ubicacion, telefono, extension,
                        encargado, email, horario, activo
                 FROM agencias_tracking
                 WHERE activo = 1
                 ORDER BY tipo_ubicacion, nombre_agencia"
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function obtenerTransportistasTracking(?string $tipo = null, ?int $idAgencia = null): array
    {
        $where = ['t.activo = 1'];
        $params = [];
        if ($tipo !== null && in_array($tipo, ['interno', 'externo'], true)) {
            $where[] = 't.tipo_transportista = :tipo';
            $params['tipo'] = $tipo;
        }
        if ($idAgencia !== null && $idAgencia > 0) {
            // Externos histÃ³ricos pueden estar sin agencia asignada; se muestran junto con los ligados a la agencia.
            $where[] = '(t.id_agencia = :id_agencia OR (t.tipo_transportista = \'externo\' AND t.id_agencia IS NULL))';
            $params['id_agencia'] = $idAgencia;
        }

        try {
            return $this->db->queryAll(
                "SELECT
                    t.id_transportista,
                    t.id_agencia,
                    t.tipo_transportista,
                    t.nombre_transportista,
                    t.curp_rfc,
                    t.email,
                    t.telefono,
                    t.empresa_origen,
                    t.puesto,
                    a.nombre_agencia,
                    a.clave_agencia
                 FROM transportistas_tracking t
                 LEFT JOIN agencias_tracking a ON a.id_agencia = t.id_agencia
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY t.tipo_transportista, COALESCE(a.nombre_agencia, t.empresa_origen), t.nombre_transportista",
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function obtenerCatalogoAgenciasTransportistas(): array
    {
        return [
            'agencias'       => $this->obtenerAgenciasTracking(),
            'transportistas' => $this->obtenerTransportistasTracking(),
        ];
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
        $creditos  = is_array($data['creditos'] ?? null) ? $data['creditos'] : [];
        $tipoTransportista = strtolower(trim((string) ($data['tipo_transportista'] ?? '')));
        $idTransportista   = (int) ($data['id_transportista'] ?? 0);
        $idAgenciaTracking = (int) ($data['id_agencia_tracking'] ?? 0);
        if (!in_array($tipoTransportista, ['interno', 'externo'], true)) {
            $tipoTransportista = '';
        }

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
        if ($tipoTransportista !== '' && $idTransportista <= 0) {
            return ['success' => false, 'message' => 'Selecciona un transportista vÃ¡lido.'];
        }
        if ($tipoTransportista === 'externo' && $idAgenciaTracking <= 0) {
            return ['success' => false, 'message' => 'Selecciona la agencia relacionada para el transportista externo.'];
        }

        // Validar fecha
        $fechaOk = $this->validarFechaProgramada($fechaStr);
        if ($fechaOk !== null) {
            return ['success' => false, 'message' => $fechaOk];
        }

        // Validaciones adicionales para enviar (no borrador, no actualizar)
        if ($modo !== 'borrador' && $modo !== 'actualizar') {
            if ($tipoTransportista === '' || $idTransportista <= 0) {
                return ['success' => false, 'message' => 'Debe seleccionar tipo y transportista para enviar la ruta.'];
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
        // 'actualizar' preserva el estatus existente (se sobreescribe dentro del bloque UPDATE)
        $estatusRuta  = ($modo === 'borrador') ? 'borrador' : 'enviada';

        // Validar y normalizar hora de salida (formato HH:MM)
        $horaRaw = trim((string) ($data['hora_salida'] ?? ''));
        $horaFmt = null;
        if ($horaRaw !== '') {
            // Acepta H:MM, HH:MM, HH:MM:SS
            if (preg_match('/^(\d{1,2}):(\d{2})(:\d{2})?$/', $horaRaw, $m)) {
                $h = (int) $m[1];
                $min = (int) $m[2];
                if ($h >= 0 && $h <= 23 && $min >= 0 && $min <= 59) {
                    $horaFmt = sprintf('%02d:%02d:00', $h, $min);
                }
            }
            if ($horaFmt === null) {
                return ['success' => false, 'message' => 'El formato de hora no es válido.'];
            }
        }

        if ($idTransportista > 0) {
            $transportista = $this->db->queryOne(
                "SELECT id_transportista, id_agencia, tipo_transportista
                 FROM transportistas_tracking
                 WHERE id_transportista = :id AND activo = 1
                 LIMIT 1",
                ['id' => $idTransportista]
            );
            if (!$transportista) {
                return ['success' => false, 'message' => 'El transportista seleccionado no existe o estÃ¡ inactivo.'];
            }
            if ($tipoTransportista === '') {
                $tipoTransportista = (string) $transportista['tipo_transportista'];
            }
            if ($tipoTransportista !== (string) $transportista['tipo_transportista']) {
                return ['success' => false, 'message' => 'El tipo de transportista no coincide con el transportista seleccionado.'];
            }
            if ($tipoTransportista === 'interno' && $idAgenciaTracking <= 0 && !empty($transportista['id_agencia'])) {
                $idAgenciaTracking = (int) $transportista['id_agencia'];
            }
            if ($tipoTransportista === 'externo' && !empty($transportista['id_agencia']) && $idAgenciaTracking !== (int) $transportista['id_agencia']) {
                return ['success' => false, 'message' => 'La agencia seleccionada no coincide con el transportista externo.'];
            }
        }

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
                // Sin restricción por estatus: cualquier ruta puede editarse
                // Modo 'actualizar': preservar estatus actual de la ruta
                if ($modo === 'actualizar') {
                    $estatusRuta = $existente['estatus_ruta'];
                }
                // Leer hora_inicial actual para decidir si se actualiza
                $horaActual = $this->db->queryOne(
                    'SELECT hora_inicial FROM asigna_horas_tracking WHERE id_ruta = :id LIMIT 1',
                    ['id' => $idRuta]
                );
                $horaInicialActual = $horaActual['hora_inicial'] ?? null;

                // Determinar qué columnas de hora actualizar
                $setHora = '';
                $horaParams = [];
                if ($horaFmt !== null) {
                    if ($horaInicialActual === null) {
                        // Primera vez: guardar como hora_inicial
                        $setHora = ', hora_inicial = :hi';
                        $horaParams['hi'] = $horaFmt;
                    } elseif ($horaFmt !== $horaInicialActual) {
                        // Cambio de hora: guardar en act_hora_1
                        $setHora = ', act_hora_1 = :ah1';
                        $horaParams['ah1'] = $horaFmt;
                    }
                }

                $this->db->CRUD(
                    "UPDATE asigna_horas_tracking
                     SET nombre_ruta = :n, estado = :e, municipio = :m,
                         fecha_programada = :f, estatus_ruta = :er,
                         tipo_transportista = :tt, id_transportista = :it, id_agencia_tracking = :iat,
                         fecha_actualizacion = :fa{$setHora}
                     WHERE id_ruta = :id",
                    array_merge(
                        [
                            'n'  => $nombre,
                            'e'  => $estado,
                            'm'  => $municipio,
                            'f'  => $fechaStr,
                            'er' => $estatusRuta,
                            'tt' => $tipoTransportista !== '' ? $tipoTransportista : null,
                            'it' => $idTransportista > 0 ? $idTransportista : null,
                            'iat'=> $idAgenciaTracking > 0 ? $idAgenciaTracking : null,
                            'fa' => $ahora,
                            'id' => $idRuta,
                        ],
                        $horaParams
                    )
                );
                // Limpiar y reinsertar detalle; se purgan asignaciones legacy de usuarios.
                $this->db->CRUD('DELETE FROM asigna_horas_tracking_detalle WHERE id_ruta = :id', ['id' => $idRuta]);
                $this->db->CRUD('DELETE FROM asigna_horas_tracking_usuarios WHERE id_ruta = :id', ['id' => $idRuta]);
            } else {
                $this->db->CRUD(
                    'INSERT INTO asigna_horas_tracking
                         (nombre_ruta, estado, municipio, fecha_programada, hora_inicial, tipo_transportista, id_transportista, id_agencia_tracking, estatus_ruta, creado_por, fecha_creacion, fecha_actualizacion)
                     VALUES (:n, :e, :m, :f, :hi, :tt, :it, :iat, :er, :cp, :fc, :fa)',
                    [
                        'n'  => $nombre,
                        'e'  => $estado,
                        'm'  => $municipio,
                        'f'  => $fechaStr,
                        'hi' => $horaFmt,
                        'tt' => $tipoTransportista !== '' ? $tipoTransportista : null,
                        'it' => $idTransportista > 0 ? $idTransportista : null,
                        'iat'=> $idAgenciaTracking > 0 ? $idAgenciaTracking : null,
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

                $fechaEta = null;
                if (!empty($det['fecha_eta'])) {
                    $fe = trim((string) $det['fecha_eta']);
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fe)) {
                        $fechaEta = $fe;
                    }
                }
                $horaEtaIni = null;
                if (!empty($det['hora_eta_ini'])) {
                    $hi = trim((string) $det['hora_eta_ini']);
                    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hi)) {
                        $horaEtaIni = substr($hi, 0, 5);
                    }
                }
                $horaEtaFin = null;
                if (!empty($det['hora_eta_fin'])) {
                    $hf = trim((string) $det['hora_eta_fin']);
                    if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hf)) {
                        $horaEtaFin = substr($hf, 0, 5);
                    }
                }

                $this->db->CRUD(
                    'INSERT INTO asigna_horas_tracking_detalle
                         (id_ruta, id_credito, modelo, bin, estado, municipio, direccion, latitud, longitud, orden_ruta, estatus_confirmacion_gestor, fecha_eta, hora_eta_ini, hora_eta_fin)
                     VALUES (:ir, :ic, :mo, :bi, :es, :mu, :di, :la, :lo, :or, :cf, :fe, :hi, :hf)',
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
                        'fe' => $fechaEta,
                        'hi' => $horaEtaIni,
                        'hf' => $horaEtaFin,
                    ]
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

        // Excluir rutas borrador (tienen su propia pestaña)
        $where[] = "atr.estatus_ruta != 'borrador'";

        $sql = 'SELECT
            atr.id_ruta,
            atr.nombre_ruta,
            atr.estado,
            atr.municipio,
            CONCAT(DATE_FORMAT(atr.fecha_programada, \'%d/\'), ELT(MONTH(atr.fecha_programada), \'Enero\',\'Febrero\',\'Marzo\',\'Abril\',\'Mayo\',\'Junio\',\'Julio\',\'Agosto\',\'Septiembre\',\'Octubre\',\'Noviembre\',\'Diciembre\'), DATE_FORMAT(atr.fecha_programada, \'/%Y\')) AS fecha_programada_fmt,
            atr.fecha_programada,
            atr.estatus_ruta,
            atr.creado_por,
            DATE_FORMAT(atr.fecha_creacion, \'%d/%m/%Y %H:%i\') AS fecha_creacion_fmt,
            TIME_FORMAT(atr.hora_inicial, \'%H:%i\') AS hora_inicial,
            TIME_FORMAT(atr.act_hora_1,   \'%H:%i\') AS act_hora_1,
            atr.tipo_transportista,
            atr.id_transportista,
            atr.id_agencia_tracking,
            tt.nombre_transportista,
            tt.empresa_origen AS transportista_empresa,
            tt.telefono AS transportista_telefono,
            tt.email AS transportista_email,
            ag.nombre_agencia,
            ag.clave_agencia,
            COUNT(atd.id_detalle) AS total_creditos,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'confirmado\'  THEN 1 ELSE 0 END) AS confirmados,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'rechazado\'   THEN 1 ELSE 0 END) AS rechazados,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'pendiente\'   THEN 1 ELSE 0 END) AS pendientes,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'en_revision\' THEN 1 ELSE 0 END) AS en_revision,
            GROUP_CONCAT(
                CONCAT(\'#\', atd.id_credito,
                    CASE WHEN atd.modelo IS NOT NULL AND atd.modelo != \'\' THEN CONCAT(\' - \', atd.modelo) ELSE \'\'  END
                )
                ORDER BY atd.orden_ruta SEPARATOR \'||\'
            ) AS creditos_lista,
            GROUP_CONCAT(
                DISTINCT CONCAT(
                    COALESCE(atd.estado, \'\'),
                    \'|||\'  ,
                    COALESCE(atd.municipio, \'|\')
                )
                ORDER BY atd.estado, atd.municipio SEPARATOR \'@@\'
            ) AS ubicaciones_lista
        FROM asigna_horas_tracking atr
        LEFT JOIN asigna_horas_tracking_detalle atd ON atd.id_ruta = atr.id_ruta
        LEFT JOIN transportistas_tracking tt ON tt.id_transportista = atr.id_transportista
        LEFT JOIN agencias_tracking ag ON ag.id_agencia = atr.id_agencia_tracking
        WHERE ' . implode(' AND ', $where) . '
        GROUP BY atr.id_ruta
        ORDER BY atr.fecha_creacion DESC';

        try {
            $rutas = $this->db->queryAll($sql, $params) ?: [];
            return $rutas;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Obtiene únicamente las rutas con estatus 'borrador'.
     * Misma estructura de columnas que obtenerRutas().
     */
    public function obtenerBorradores(): array
    {
        $sql = 'SELECT
            atr.id_ruta,
            atr.nombre_ruta,
            atr.estado,
            atr.municipio,
            CONCAT(DATE_FORMAT(atr.fecha_programada, \'%d/\'), ELT(MONTH(atr.fecha_programada), \'Enero\',\'Febrero\',\'Marzo\',\'Abril\',\'Mayo\',\'Junio\',\'Julio\',\'Agosto\',\'Septiembre\',\'Octubre\',\'Noviembre\',\'Diciembre\'), DATE_FORMAT(atr.fecha_programada, \'/%Y\')) AS fecha_programada_fmt,
            atr.fecha_programada,
            atr.estatus_ruta,
            atr.creado_por,
            DATE_FORMAT(atr.fecha_creacion, \'%d/%m/%Y %H:%i\') AS fecha_creacion_fmt,
            TIME_FORMAT(atr.hora_inicial, \'%H:%i\') AS hora_inicial,
            TIME_FORMAT(atr.act_hora_1,   \'%H:%i\') AS act_hora_1,
            atr.tipo_transportista,
            atr.id_transportista,
            atr.id_agencia_tracking,
            tt.nombre_transportista,
            tt.empresa_origen AS transportista_empresa,
            tt.telefono AS transportista_telefono,
            tt.email AS transportista_email,
            ag.nombre_agencia,
            ag.clave_agencia,
            COUNT(atd.id_detalle) AS total_creditos,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'confirmado\'  THEN 1 ELSE 0 END) AS confirmados,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'rechazado\'   THEN 1 ELSE 0 END) AS rechazados,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'pendiente\'   THEN 1 ELSE 0 END) AS pendientes,
            SUM(CASE WHEN atd.estatus_confirmacion_gestor = \'en_revision\' THEN 1 ELSE 0 END) AS en_revision,
            GROUP_CONCAT(
                CONCAT(\'#\', atd.id_credito,
                    CASE WHEN atd.modelo IS NOT NULL AND atd.modelo != \'\'  THEN CONCAT(\' - \', atd.modelo) ELSE \'\'  END
                )
                ORDER BY atd.orden_ruta SEPARATOR \'||\'
            ) AS creditos_lista,
            GROUP_CONCAT(
                DISTINCT CONCAT(
                    COALESCE(atd.estado, \'\'),
                    \'|||\'  ,
                    COALESCE(atd.municipio, \'|\')
                )
                ORDER BY atd.estado, atd.municipio SEPARATOR \'@@\'
            ) AS ubicaciones_lista
        FROM asigna_horas_tracking atr
        LEFT JOIN asigna_horas_tracking_detalle atd ON atd.id_ruta = atr.id_ruta
        LEFT JOIN transportistas_tracking tt ON tt.id_transportista = atr.id_transportista
        LEFT JOIN agencias_tracking ag ON ag.id_agencia = atr.id_agencia_tracking
        WHERE atr.estatus_ruta = \'borrador\'
        GROUP BY atr.id_ruta
        ORDER BY atr.fecha_creacion DESC';

        try {
            $rutas = $this->db->queryAll($sql) ?: [];
            return $rutas;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Detalle completo de una ruta (cabecera + créditos).
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
                    tt.nombre_transportista,
                    tt.empresa_origen AS transportista_empresa,
                    tt.telefono AS transportista_telefono,
                    tt.email AS transportista_email,
                    tt.curp_rfc AS transportista_curp_rfc,
                    tt.puesto AS transportista_puesto,
                    ag.nombre_agencia,
                    ag.clave_agencia,
                    ag.direccion AS agencia_direccion,
                    ag.telefono AS agencia_telefono,
                    ag.encargado AS agencia_encargado,
                    ag.email AS agencia_email,
                    CONCAT(DATE_FORMAT(atr.fecha_programada, '%d/'), ELT(MONTH(atr.fecha_programada), 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'), DATE_FORMAT(atr.fecha_programada, '/%Y')) AS fecha_programada_fmt,
                    DATE_FORMAT(atr.fecha_creacion,   '%d/%m/%Y %H:%i') AS fecha_creacion_fmt,
                    DATE_FORMAT(atr.fecha_cancelacion, '%d/%m/%Y %H:%i') AS fecha_cancelacion_fmt
                 FROM asigna_horas_tracking atr
                 LEFT JOIN transportistas_tracking tt ON tt.id_transportista = atr.id_transportista
                 LEFT JOIN agencias_tracking ag ON ag.id_agencia = atr.id_agencia_tracking
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
            $cabecera['detalle']  = $detalles;
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

    /**
     * Cancela una ruta registrada o iniciada.
     *
     * @return array{success:bool, message?:string}
     */
    public function cancelarRuta(int $idRuta, string $motivo, int $idUsuario): array
    {
        $motivo = trim(preg_replace('/\s+/', ' ', $motivo));
        if ($idRuta <= 0) {
            return ['success' => false, 'message' => 'Ruta requerida.'];
        }
        if ($motivo === '') {
            return ['success' => false, 'message' => 'El motivo de cancelación es obligatorio.'];
        }
        if (mb_strlen($motivo, 'UTF-8') > 200) {
            return ['success' => false, 'message' => 'El motivo de cancelación no puede exceder 200 caracteres.'];
        }

        try {
            $ruta = $this->db->queryOne(
                "SELECT id_ruta, estatus_ruta
                 FROM asigna_horas_tracking
                 WHERE id_ruta = :id
                 LIMIT 1",
                ['id' => $idRuta]
            );
            if (!$ruta) {
                return ['success' => false, 'message' => 'Ruta no encontrada.'];
            }
            if (in_array((string) $ruta['estatus_ruta'], ['borrador', 'concluida', 'cancelada'], true)) {
                return ['success' => false, 'message' => 'Esta ruta no se puede cancelar por su estatus actual.'];
            }

            $this->db->CRUD(
                "UPDATE asigna_horas_tracking
                 SET estatus_ruta = 'cancelada',
                     motivo_cancelacion = :motivo,
                     fecha_cancelacion = :fecha,
                     cancelado_por = :usuario,
                     fecha_actualizacion = :fecha
                 WHERE id_ruta = :id",
                [
                    'motivo'  => $motivo,
                    'fecha'   => date('Y-m-d H:i:s'),
                    'usuario' => $idUsuario > 0 ? $idUsuario : null,
                    'id'      => $idRuta,
                ]
            );

            return ['success' => true, 'message' => 'Ruta cancelada correctamente.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error al cancelar la ruta.'];
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
     * Enriquece un array de créditos recibido de la API externa con los campos
     * id_credito y nombre_cliente obtenidos de la base de datos local,
     * usando id_detalle como clave de unión.
     *
     * @param array $creditos  Items de ruta.creditos devueltos por la API externa.
     * @return array           Los mismos items con id_credito y nombre_cliente añadidos.
     */
    public function enriquecerCreditosConDatosLocales(array $creditos): array
    {
        if (empty($creditos)) {
            return $creditos;
        }
        $ids = array_values(array_filter(array_map(fn($c) => isset($c['id_detalle']) ? (int)$c['id_detalle'] : null, $creditos)));
        if (empty($ids)) {
            return $creditos;
        }
        try {
            $params = [];
            $placeholders = [];
            foreach ($ids as $i => $id) {
                $key = "id{$i}";
                $placeholders[] = ":{$key}";
                $params[$key]   = $id;
            }
            $rows = $this->db->queryAll(
                "SELECT atd.id_detalle, atd.id_credito, ao.nombre_cliente
                 FROM asigna_horas_tracking_detalle atd
                 LEFT JOIN adj_operacion ao ON ao.id_credito = atd.id_credito
                 WHERE atd.id_detalle IN (" . implode(',', $placeholders) . ")",
                $params
            ) ?: [];
            $map = [];
            foreach ($rows as $row) {
                $map[(int)$row['id_detalle']] = [
                    'id_credito'     => $row['id_credito'],
                    'nombre_cliente' => $row['nombre_cliente'] ?? '',
                ];
            }
            foreach ($creditos as &$c) {
                $idDet = isset($c['id_detalle']) ? (int)$c['id_detalle'] : 0;
                if ($idDet && isset($map[$idDet])) {
                    $c['id_credito']     = $map[$idDet]['id_credito'];
                    $c['nombre_cliente'] = $map[$idDet]['nombre_cliente'];
                }
            }
            unset($c);
        } catch (\Throwable $e) {
            // Devolver sin enriquecer antes de propagar el error
        }
        return $creditos;
    }
}
