<?php

namespace Models;

use Core\Model;
use Core\Database;
use Models\ConfigMotosAdj;

class TrackingRecoleccion extends Model
{
    public const MODULO_TRACKING_CANCELAR_RUTA = 102;
    private const MODULO_TRACKING_CANCELAR_RUTA_NOMBRE = 'Cancelar rutas Tracking';
    private const MODULO_TRACKING_CANCELAR_RUTA_DESC = 'Tracking Recoleccion - Cancelar rutas registradas';

    /** @var Database */
    private $db;
    private static ?int $moduloCancelarRutaId = null;

    public function __construct()
    {
        $this->db = new Database();
        $this->asegurarTablas();
    }

    private function textoUbicacionBase(?string $valor): string
    {
        $txt = mb_strtoupper(trim((string) $valor), 'UTF-8');
        $txt = strtr($txt, [
            'Ã' => 'A', 'Ã‰' => 'E', 'Ã' => 'I', 'Ã“' => 'O', 'Ãš' => 'U', 'Ãœ' => 'U', 'Ã‘' => 'N',
            'Ã¡' => 'A', 'Ã©' => 'E', 'Ã­' => 'I', 'Ã³' => 'O', 'Ãº' => 'U', 'Ã¼' => 'U', 'Ã±' => 'N',
            'ÃƒÂ' => 'A', 'Ãƒâ€°' => 'E', 'ÃƒÂ' => 'I', 'Ãƒâ€œ' => 'O', 'ÃƒÅ¡' => 'U', 'ÃƒÅ“' => 'U', 'Ãƒâ€˜' => 'N',
            'ÃƒÂ¡' => 'A', 'ÃƒÂ©' => 'E', 'ÃƒÂ­' => 'I', 'ÃƒÂ³' => 'O', 'ÃƒÂº' => 'U', 'ÃƒÂ¼' => 'U', 'ÃƒÂ±' => 'N',
        ]);
        if (function_exists('iconv')) {
            $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $txt);
            if ($tmp !== false) {
                $txt = $tmp;
            }
        }
        $txt = preg_replace('/\s+/', ' ', $txt) ?? $txt;
        return trim($txt);
    }

    private function normalizarEstadoTracking(?string $valor): string
    {
        $txt = $this->textoUbicacionBase($valor);
        $txt = strtr($txt, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'A', 'é' => 'E', 'í' => 'I', 'ó' => 'O', 'ú' => 'U', 'ü' => 'U', 'ñ' => 'N',
            'Ã' => 'A', 'Ã‰' => 'E', 'Ã' => 'I', 'Ã“' => 'O', 'Ãš' => 'U', 'Ãœ' => 'U', 'Ã‘' => 'N',
            'Ã¡' => 'A', 'Ã©' => 'E', 'Ã­' => 'I', 'Ã³' => 'O', 'Ãº' => 'U', 'Ã¼' => 'U', 'Ã±' => 'N',
        ]);
        $txt = preg_replace('/[^A-Z0-9\s]/', ' ', $txt) ?? $txt;
        $txt = preg_replace('/\s+/', ' ', $txt) ?? $txt;
        $txt = trim($txt);

        $alias = [
            'CDMX' => 'CIUDAD DE MEXICO',
            'CMDX' => 'CIUDAD DE MEXICO',
            'DISTRITO FEDERAL' => 'CIUDAD DE MEXICO',
            'DF' => 'CIUDAD DE MEXICO',
            'MEXICO' => 'ESTADO DE MEXICO',
            'EDOMEX' => 'ESTADO DE MEXICO',
            'EDO MEX' => 'ESTADO DE MEXICO',
            'EDO DE MEX' => 'ESTADO DE MEXICO',
            'EDO DE MEXICO' => 'ESTADO DE MEXICO',
            'ESTADO MEXICO' => 'ESTADO DE MEXICO',
            'EDO MEXICO' => 'ESTADO DE MEXICO',
            'MICHOACAN DE OCAMPO' => 'MICHOACAN',
            'SLP' => 'SAN LUIS POTOSI',
            'SAN LUIS' => 'SAN LUIS POTOSI',
            'QRO' => 'QUERETARO',
            'VER' => 'VERACRUZ',
        ];
        return $alias[$txt] ?? $txt;
    }

    private function aliasesEstadoTracking(?string $estado): array
    {
        $canon = $this->normalizarEstadoTracking($estado);
        $map = [
            'CIUDAD DE MEXICO' => ['CIUDAD DE MEXICO', 'CDMX', 'CMDX', 'DISTRITO FEDERAL', 'DF'],
            'ESTADO DE MEXICO' => ['ESTADO DE MEXICO', 'MEXICO', 'EDOMEX', 'EDO MEX', 'EDO DE MEX', 'EDO DE MEXICO', 'ESTADO MEXICO', 'EDO MEXICO'],
            'MICHOACAN' => ['MICHOACAN', 'MICHOACAN DE OCAMPO'],
            'SAN LUIS POTOSI' => ['SAN LUIS POTOSI', 'SAN LUIS', 'SLP'],
            'QUERETARO' => ['QUERETARO', 'QRO'],
            'VERACRUZ' => ['VERACRUZ', 'VER'],
        ];
        $aliases = $map[$canon] ?? [$canon];
        return array_values(array_unique(array_map(fn($v) => $this->normalizarEstadoTracking($v), $aliases)));
    }

    private function sqlEstadoNormalizado(string $columna): string
    {
        $expr = "UPPER(TRIM(REPLACE(REPLACE(REPLACE(REPLACE({$columna}, '.', ''), ',', ''), ';', ''), ':', '')))";
        $map = [
            'Á' => 'A', 'À' => 'A', 'Ä' => 'A', 'Â' => 'A',
            'É' => 'E', 'È' => 'E', 'Ë' => 'E', 'Ê' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Ï' => 'I', 'Î' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Ö' => 'O', 'Ô' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Ü' => 'U', 'Û' => 'U',
            'Ñ' => 'N',
        ];
        foreach ($map as $from => $to) {
            $expr = "REPLACE({$expr}, '{$from}', '{$to}')";
        }
        return $expr;
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
                    `fecha_agregado`              DATETIME      NULL,
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
            $this->asegurarColumnasDetalleRuta();
            $this->asegurarPermisoCancelarRutaTracking();
            self::$tablasOk = true;
        } catch (\Throwable $e) {
            // No bloquear la carga del módulo; la BD podría no tener permisos DDL
            self::$tablasOk = true;
        }
    }

    public function asegurarPermisoCancelarRutaTracking(): int
    {
        if (self::$moduloCancelarRutaId !== null) {
            return self::$moduloCancelarRutaId;
        }

        try {
            $row = $this->db->queryOne(
                "SELECT id
                   FROM modulos_web
                  WHERE pestana = 'Permisos especiales'
                    AND (descripcion = :descripcion_where OR nombre = :nombre_where)
                  ORDER BY CASE
                      WHEN descripcion = :descripcion_order THEN 0
                      WHEN nombre = :nombre_order THEN 1
                      ELSE 2
                  END
                  LIMIT 1",
                [
                    'descripcion_where' => self::MODULO_TRACKING_CANCELAR_RUTA_DESC,
                    'nombre_where' => self::MODULO_TRACKING_CANCELAR_RUTA_NOMBRE,
                    'descripcion_order' => self::MODULO_TRACKING_CANCELAR_RUTA_DESC,
                    'nombre_order' => self::MODULO_TRACKING_CANCELAR_RUTA_NOMBRE,
                ]
            );

            if ($row && (int) ($row['id'] ?? 0) > 0) {
                self::$moduloCancelarRutaId = (int) $row['id'];
                $this->db->CRUD(
                    "UPDATE modulos_web
                        SET nombre = :nombre,
                            pestana = 'Permisos especiales',
                            descripcion = :descripcion,
                            activo = 1
                      WHERE id = :id",
                    [
                        'nombre' => self::MODULO_TRACKING_CANCELAR_RUTA_NOMBRE,
                        'descripcion' => self::MODULO_TRACKING_CANCELAR_RUTA_DESC,
                        'id' => self::$moduloCancelarRutaId,
                    ]
                );
                return self::$moduloCancelarRutaId;
            }

            $idOcupado = $this->db->queryOne(
                "SELECT id FROM modulos_web WHERE id = :id LIMIT 1",
                ['id' => self::MODULO_TRACKING_CANCELAR_RUTA]
            );

            if (!$idOcupado) {
                $this->db->CRUD(
                    "INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                     VALUES (:id, :nombre, 'Permisos especiales', :descripcion, 1)",
                    [
                        'id' => self::MODULO_TRACKING_CANCELAR_RUTA,
                        'nombre' => self::MODULO_TRACKING_CANCELAR_RUTA_NOMBRE,
                        'descripcion' => self::MODULO_TRACKING_CANCELAR_RUTA_DESC,
                    ]
                );
                self::$moduloCancelarRutaId = self::MODULO_TRACKING_CANCELAR_RUTA;
                return self::$moduloCancelarRutaId;
            }

            $this->db->CRUD(
                "INSERT INTO modulos_web (nombre, pestana, descripcion, activo)
                 VALUES (:nombre, 'Permisos especiales', :descripcion, 1)",
                [
                    'nombre' => self::MODULO_TRACKING_CANCELAR_RUTA_NOMBRE,
                    'descripcion' => self::MODULO_TRACKING_CANCELAR_RUTA_DESC,
                ]
            );
            $row = $this->db->queryOne(
                "SELECT id
                   FROM modulos_web
                  WHERE descripcion = :descripcion
                  ORDER BY id DESC
                  LIMIT 1",
                ['descripcion' => self::MODULO_TRACKING_CANCELAR_RUTA_DESC]
            );
            self::$moduloCancelarRutaId = (int) ($row['id'] ?? 0);
            return self::$moduloCancelarRutaId;
        } catch (\Throwable $e) {
            self::$moduloCancelarRutaId = 0;
            return self::$moduloCancelarRutaId;
        }
    }

    public function usuarioPuedeCancelarRutasTracking(int $idUsuario): bool
    {
        if ($idUsuario <= 0) {
            return false;
        }

        $moduloId = $this->asegurarPermisoCancelarRutaTracking();
        if ($moduloId <= 0) {
            return false;
        }

        $modulosSesion = array_map('intval', (array) ($_SESSION['modulos'] ?? []));
        if (in_array($moduloId, $modulosSesion, true)) {
            return true;
        }

        try {
            $row = $this->db->queryOne(
                "SELECT 1 AS ok
                   FROM asigna_modulo_web
                  WHERE usuario_id = :usuario
                    AND modulo_web_id = :modulo
                  LIMIT 1",
                [
                    'usuario' => $idUsuario,
                    'modulo' => $moduloId,
                ]
            );
            return (bool) $row;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function asegurarColumnasDetalleRuta(): void
    {
        try {
            if (!$this->columnaOpcionalExiste('asigna_horas_tracking_detalle', 'fecha_agregado')) {
                $this->db->CRUD(
                    "ALTER TABLE `asigna_horas_tracking_detalle`
                     ADD COLUMN `fecha_agregado` DATETIME NULL DEFAULT NULL AFTER `estatus_recoleccion`"
                );
            }
        } catch (\Throwable $e) {
            // No bloquear si el usuario de BD no tiene permisos DDL.
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
            // El catálogo puede ser creado manualmente por migración.
        }
    }

    private function asegurarColumnasTransportistaRuta(): void
    {
        $cols = [
            'tipo_transportista'  => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `tipo_transportista` ENUM('interno','externo') NULL AFTER `act_hora_1`",
            'id_transportista'    => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `id_transportista` INT NULL AFTER `tipo_transportista`",
            'id_agencia_tracking' => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `id_agencia_tracking` INT NULL AFTER `id_transportista`",
            'id_cedis_destino'    => "ALTER TABLE `asigna_horas_tracking` ADD COLUMN `id_cedis_destino` INT NULL AFTER `id_agencia_tracking`",
        ];
        foreach ($cols as $col => $sql) {
            if (!$this->columnaExiste('asigna_horas_tracking', $col)) {
                try { $this->db->CRUD($sql); } catch (\Throwable $e) {}
            }
        }
        try { $this->db->CRUD("ALTER TABLE `asigna_horas_tracking` ADD KEY `idx_tracking_transportista` (`tipo_transportista`, `id_transportista`)"); } catch (\Throwable $e) {}
        try { $this->db->CRUD("ALTER TABLE `asigna_horas_tracking` ADD KEY `idx_tracking_agencia_tracking` (`id_agencia_tracking`)"); } catch (\Throwable $e) {}
        try { $this->db->CRUD("ALTER TABLE `asigna_horas_tracking` ADD KEY `idx_tracking_cedis_destino` (`id_cedis_destino`)"); } catch (\Throwable $e) {}
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

    private function columnaOpcionalExiste(string $tabla, string $columna): bool
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
            return false;
        }
    }

    private function tablaOpcionalExiste(string $tabla): bool
    {
        try {
            $row = $this->db->queryOne("SHOW TABLES LIKE :tabla", ['tabla' => $tabla]);
            return (bool) $row;
        } catch (\Throwable $e) {
            return false;
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
            $aliases = $this->aliasesEstadoTracking($estado);
            $phs = [];
            foreach ($aliases as $i => $alias) {
                $key = "estado{$i}";
                $phs[] = ":{$key}";
                $params[$key] = $alias;
            }
            $where[] = $this->sqlEstadoNormalizado('ao.log_estado') . ' IN (' . implode(',', $phs) . ')';
        }
        if ($municipio !== null && $municipio !== '') {
            $where[]              = $this->sqlEstadoNormalizado('ao.log_ciudad') . ' = :municipio';
            $params['municipio']  = $this->sanitizarUbicacionMayus($municipio, 120);
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
            $aliases = $this->aliasesEstadoTracking($estado);
            $phs = [];
            $params = [];
            foreach ($aliases as $i => $alias) {
                $key = "estado{$i}";
                $phs[] = ":{$key}";
                $params[$key] = $alias;
            }
            $rows = $this->db->queryAll(
                "SELECT da.nombre
                 FROM divisiones_administrativas da
                 INNER JOIN divisiones_administrativas padre
                        ON padre.id = da.id_padre
                       AND " . $this->sqlEstadoNormalizado('padre.nombre') . " IN (" . implode(',', $phs) . ")
                       AND padre.id_pais = 1
                       AND padre.nivel   = 1
                 WHERE da.activo = 1
                 ORDER BY da.nombre",
                $params
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
    // CATÁLOGOS TRACKING: AGENCIAS / TRANSPORTISTAS
    // =========================================================================

    public function obtenerAgenciasTracking(bool $soloActivas = true): array
    {
        $where = $soloActivas ? 'WHERE activo = 1' : '';
        try {
            return $this->db->queryAll(
                "SELECT id_agencia, clave_agencia, nombre_agencia, tipo_ubicacion,
                        direccion, estado, municipio, codigo_postal,
                        latitud, longitud, link_ubicacion, telefono, extension,
                        encargado, email, horario, activo
                 FROM agencias_tracking
                 {$where}
                 ORDER BY tipo_ubicacion, nombre_agencia"
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function obtenerTransportistasTracking(?string $tipo = null, ?int $idAgencia = null, bool $soloActivos = true): array
    {
        $where = $soloActivos ? ['t.activo = 1'] : ['1 = 1'];
        $params = [];
        if ($tipo !== null && in_array($tipo, ['interno', 'externo'], true)) {
            $where[] = 't.tipo_transportista = :tipo';
            $params['tipo'] = $tipo;
        }
        if ($idAgencia !== null && $idAgencia > 0) {
            // Externos históricos pueden estar sin agencia asignada; se muestran junto con los ligados a la agencia.
            $where[] = '(t.id_agencia = :id_agencia OR (t.tipo_transportista = \'externo\' AND t.id_agencia IS NULL))';
            $params['id_agencia'] = $idAgencia;
        }

        try {
            $extraSelect = '';
            if ($this->columnaOpcionalExiste('transportistas_tracking', 'username')) {
                $extraSelect .= ",\n                    t.username";
            }
            if ($this->columnaOpcionalExiste('transportistas_tracking', 'debe_cambiar_password')) {
                $extraSelect .= ",\n                    t.debe_cambiar_password";
            }
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
                    t.activo,
                    a.nombre_agencia,
                    a.clave_agencia
                    {$extraSelect}
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
            'agencias'       => $this->obtenerAgenciasTracking(false),
            'transportistas' => $this->obtenerTransportistasTracking(null, null, false),
        ];
    }

    public function obtenerOperacionTransportistas(): array
    {
        $tablaCapacidad = 'transportistas_capacidad_tracking';
        $tablaCapacidadExiste = $this->tablaOpcionalExiste($tablaCapacidad);
        $tieneCapacidad = $tablaCapacidadExiste
            && $this->columnaOpcionalExiste($tablaCapacidad, 'id_capacidad')
            && $this->columnaOpcionalExiste($tablaCapacidad, 'id_transportista')
            && $this->columnaOpcionalExiste($tablaCapacidad, 'capacidad_motos');
        $capTieneActivo = $tieneCapacidad && $this->columnaOpcionalExiste($tablaCapacidad, 'activo');
        $capColumnas = [
            'id_capacidad' => 'id_capacidad',
            'tipo_unidad' => 'tipo_unidad',
            'capacidad_motos' => 'capacidad_motos',
            'marca' => 'marca',
            'modelo' => 'modelo',
            'anio' => 'anio',
            'color' => 'color',
            'placa' => 'placa',
            'numero_serie' => 'numero_serie',
            'numero_motor' => 'numero_motor',
            'numero_economico' => 'numero_economico',
            'aseguradora' => 'aseguradora',
            'poliza_seguro' => 'poliza_seguro',
            'vigencia_seguro' => 'vigencia_seguro',
            'observaciones' => 'observaciones',
            'activo' => 'activo',
        ];
        $capSelectInterno = ['c1.id_transportista'];
        $capSelectExterno = [
            'COALESCE(cap.capacidad_motos, 0) AS capacidad_motos',
        ];
        foreach ($capColumnas as $columna => $alias) {
            if ($columna === 'id_transportista') {
                continue;
            }
            $existeColumna = $tieneCapacidad && $this->columnaOpcionalExiste($tablaCapacidad, $columna);
            $capSelectInterno[] = $existeColumna
                ? "c1.{$columna} AS {$alias}"
                : "NULL AS {$alias}";
            if ($columna !== 'capacidad_motos') {
                $capSelectExterno[] = "cap.{$alias} AS unidad_{$alias}";
            }
        }
        $capSelect = $tieneCapacidad
            ? implode(",\n                ", $capSelectExterno)
            : "0 AS capacidad_motos,
                NULL AS unidad_id_capacidad,
                NULL AS unidad_tipo_unidad,
                NULL AS unidad_marca,
                NULL AS unidad_modelo,
                NULL AS unidad_anio,
                NULL AS unidad_color,
                NULL AS unidad_placa,
                NULL AS unidad_numero_serie,
                NULL AS unidad_numero_motor,
                NULL AS unidad_numero_economico,
                NULL AS unidad_aseguradora,
                NULL AS unidad_poliza_seguro,
                NULL AS unidad_vigencia_seguro,
                NULL AS unidad_observaciones,
                NULL AS unidad_activo";
        $capActivoWhere = $capTieneActivo ? 'WHERE activo = 1' : '';
        $capJoin = $tieneCapacidad
            ? "LEFT JOIN (
                    SELECT " . implode(", ", $capSelectInterno) . "
                    FROM transportistas_capacidad_tracking c1
                    INNER JOIN (
                        SELECT id_transportista, MAX(id_capacidad) AS id_capacidad
                        FROM transportistas_capacidad_tracking
                        {$capActivoWhere}
                        GROUP BY id_transportista
                    ) cm ON cm.id_capacidad = c1.id_capacidad
                ) cap ON cap.id_transportista = t.id_transportista"
            : '';

        $transportistas = $this->db->queryAll(
            "SELECT
                t.id_transportista,
                t.id_agencia,
                t.tipo_transportista,
                t.nombre_transportista,
                t.telefono,
                t.email,
                t.empresa_origen,
                t.puesto,
                t.activo,
                a.nombre_agencia AS cedis_base_nombre,
                a.estado AS cedis_base_estado,
                a.municipio AS cedis_base_municipio,
                {$capSelect}
             FROM transportistas_tracking t
             LEFT JOIN agencias_tracking a ON a.id_agencia = t.id_agencia
             {$capJoin}
             WHERE t.activo = 1
             ORDER BY t.tipo_transportista, t.nombre_transportista"
        ) ?: [];

        $rutas = $this->db->queryAll(
            "SELECT
                atr.id_ruta,
                atr.nombre_ruta,
                atr.estado,
                atr.municipio,
                atr.fecha_programada,
                CONCAT(DATE_FORMAT(atr.fecha_programada, '%d/'), ELT(MONTH(atr.fecha_programada), 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'), DATE_FORMAT(atr.fecha_programada, '/%Y')) AS fecha_programada_fmt,
                TIME_FORMAT(atr.hora_inicial, '%H:%i') AS hora_inicial,
                atr.estatus_ruta,
                atr.id_transportista,
                atr.id_cedis_destino,
                cedis_dest.nombre_agencia AS cedis_destino_nombre,
                cedis_dest.estado AS cedis_destino_estado,
                cedis_dest.municipio AS cedis_destino_municipio,
                cedis_dest.latitud AS cedis_destino_latitud,
                cedis_dest.longitud AS cedis_destino_longitud,
                COUNT(atd.id_detalle) AS total_creditos,
                SUM(CASE WHEN atd.estatus_confirmacion_gestor = 'confirmado' THEN 1 ELSE 0 END) AS confirmados,
                SUM(CASE WHEN LOWER(COALESCE(atd.estatus_recoleccion, '')) IN ('recolectada','recolectado') THEN 1 ELSE 0 END) AS recolectadas,
                SUM(CASE WHEN LOWER(COALESCE(atd.estatus_recoleccion, '')) = 'en_camino' THEN 1 ELSE 0 END) AS en_camino,
                SUM(CASE WHEN LOWER(COALESCE(atd.estatus_recoleccion, '')) = 'en_sitio' THEN 1 ELSE 0 END) AS en_sitio,
                GROUP_CONCAT(
                    DISTINCT CONCAT(COALESCE(atd.estado, ''), '|||', COALESCE(atd.municipio, ''))
                    ORDER BY atd.estado, atd.municipio SEPARATOR '@@'
                ) AS ubicaciones_lista
             FROM asigna_horas_tracking atr
             LEFT JOIN asigna_horas_tracking_detalle atd ON atd.id_ruta = atr.id_ruta
             LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = atr.id_cedis_destino
             WHERE atr.id_transportista IS NOT NULL
               AND atr.id_transportista > 0
               AND LOWER(COALESCE(atr.estatus_ruta, '')) NOT IN ('borrador','cancelada','concluida','completado','finalizada')
             GROUP BY
                atr.id_ruta,
                atr.nombre_ruta,
                atr.estado,
                atr.municipio,
                atr.fecha_programada,
                atr.hora_inicial,
                atr.estatus_ruta,
                atr.id_transportista,
                atr.id_cedis_destino,
                cedis_dest.nombre_agencia,
                cedis_dest.estado,
                cedis_dest.municipio,
                cedis_dest.latitud,
                cedis_dest.longitud
             ORDER BY atr.fecha_programada ASC, atr.hora_inicial ASC, atr.fecha_creacion DESC"
        ) ?: [];

        $porTransportista = [];
        foreach ($transportistas as $t) {
            $id = (int) ($t['id_transportista'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $porTransportista[$id] = [
                'id_transportista' => $id,
                'nombre_transportista' => (string) ($t['nombre_transportista'] ?? ''),
                'tipo_transportista' => (string) ($t['tipo_transportista'] ?? ''),
                'empresa_origen' => (string) ($t['empresa_origen'] ?? ''),
                'telefono' => (string) ($t['telefono'] ?? ''),
                'email' => (string) ($t['email'] ?? ''),
                'puesto' => (string) ($t['puesto'] ?? ''),
                'cedis_base' => [
                    'id_agencia' => (int) ($t['id_agencia'] ?? 0),
                    'nombre' => (string) ($t['cedis_base_nombre'] ?? ''),
                    'estado' => (string) ($t['cedis_base_estado'] ?? ''),
                    'municipio' => (string) ($t['cedis_base_municipio'] ?? ''),
                ],
                'unidad' => [
                    'id_capacidad' => (int) ($t['unidad_id_capacidad'] ?? 0),
                    'tipo_unidad' => (string) ($t['unidad_tipo_unidad'] ?? ''),
                    'marca' => (string) ($t['unidad_marca'] ?? ''),
                    'modelo' => (string) ($t['unidad_modelo'] ?? ''),
                    'anio' => $t['unidad_anio'] ?? null,
                    'color' => (string) ($t['unidad_color'] ?? ''),
                    'placa' => (string) ($t['unidad_placa'] ?? ''),
                    'numero_serie' => (string) ($t['unidad_numero_serie'] ?? ''),
                    'numero_motor' => (string) ($t['unidad_numero_motor'] ?? ''),
                    'numero_economico' => (string) ($t['unidad_numero_economico'] ?? ''),
                    'aseguradora' => (string) ($t['unidad_aseguradora'] ?? ''),
                    'poliza_seguro' => (string) ($t['unidad_poliza_seguro'] ?? ''),
                    'vigencia_seguro' => (string) ($t['unidad_vigencia_seguro'] ?? ''),
                    'observaciones' => (string) ($t['unidad_observaciones'] ?? ''),
                    'activo' => (int) ($t['unidad_activo'] ?? 1),
                ],
                'tipo_unidad' => (string) ($t['unidad_tipo_unidad'] ?? ''),
                'capacidad_total' => (int) ($t['capacidad_motos'] ?? 0),
                'capacidad_configurada' => (int) ($t['capacidad_motos'] ?? 0) > 0,
                'capacidad_usada' => 0,
                'capacidad_proyectada' => 0,
                'rutas_activas' => 0,
                'rutas_programadas' => 0,
                'rutas_en_camino_cedis' => 0,
                'rutas' => [],
                'ruta_activa' => null,
                'alertas' => [],
                'estatus_operativo' => 'disponible',
                'recomendacion' => 'Disponible',
            ];
        }

        foreach ($rutas as $r) {
            $idTransportista = (int) ($r['id_transportista'] ?? 0);
            if (!isset($porTransportista[$idTransportista])) {
                continue;
            }
            $estatus = strtolower((string) ($r['estatus_ruta'] ?? ''));
            $total = (int) ($r['total_creditos'] ?? 0);
            $confirmados = (int) ($r['confirmados'] ?? 0);
            $recolectadas = (int) ($r['recolectadas'] ?? 0);
            $enCamino = (int) ($r['en_camino'] ?? 0);
            $enSitio = (int) ($r['en_sitio'] ?? 0);
            $ruta = [
                'id_ruta' => (int) ($r['id_ruta'] ?? 0),
                'nombre_ruta' => (string) ($r['nombre_ruta'] ?? ''),
                'estatus_ruta' => $estatus,
                'fecha_programada' => (string) ($r['fecha_programada'] ?? ''),
                'fecha_programada_fmt' => (string) ($r['fecha_programada_fmt'] ?? ''),
                'hora_inicial' => (string) ($r['hora_inicial'] ?? ''),
                'estado' => (string) ($r['estado'] ?? ''),
                'municipio' => (string) ($r['municipio'] ?? ''),
                'id_cedis_destino' => (int) ($r['id_cedis_destino'] ?? 0),
                'cedis_destino_nombre' => (string) ($r['cedis_destino_nombre'] ?? ''),
                'cedis_destino_estado' => (string) ($r['cedis_destino_estado'] ?? ''),
                'cedis_destino_municipio' => (string) ($r['cedis_destino_municipio'] ?? ''),
                'cedis_destino_latitud' => $r['cedis_destino_latitud'] ?? null,
                'cedis_destino_longitud' => $r['cedis_destino_longitud'] ?? null,
                'total_creditos' => $total,
                'confirmados' => $confirmados,
                'recolectadas' => $recolectadas,
                'en_camino' => $enCamino,
                'en_sitio' => $enSitio,
                'pendientes_recoleccion' => max(0, $confirmados - $recolectadas),
                'ubicaciones_lista' => (string) ($r['ubicaciones_lista'] ?? ''),
            ];
            $porTransportista[$idTransportista]['rutas'][] = $ruta;

            if ($estatus === 'en_proceso') {
                $porTransportista[$idTransportista]['rutas_activas']++;
                $porTransportista[$idTransportista]['capacidad_usada'] += $recolectadas;
                $porTransportista[$idTransportista]['capacidad_proyectada'] += max($confirmados, $recolectadas);
                if ($porTransportista[$idTransportista]['ruta_activa'] === null) {
                    $porTransportista[$idTransportista]['ruta_activa'] = $ruta;
                }
                if ($confirmados > 0 && $recolectadas >= $confirmados) {
                    $porTransportista[$idTransportista]['rutas_en_camino_cedis']++;
                }
            } else {
                $porTransportista[$idTransportista]['rutas_programadas']++;
                if ($porTransportista[$idTransportista]['rutas_activas'] === 0) {
                    $porTransportista[$idTransportista]['capacidad_proyectada'] = max(
                        $porTransportista[$idTransportista]['capacidad_proyectada'],
                        $confirmados
                    );
                }
            }

            if ((int) ($r['id_cedis_destino'] ?? 0) <= 0) {
                $porTransportista[$idTransportista]['alertas'][] = [
                    'tipo' => 'sin_cedis',
                    'nivel' => 'warning',
                    'texto' => 'Ruta sin CEDIS destino.',
                ];
            }
        }

        $resumen = [
            'transportistas_activos' => count($porTransportista),
            'disponibles' => 0,
            'en_ruta' => 0,
            'programados' => 0,
            'advertencia' => 0,
            'saturados' => 0,
            'sin_capacidad' => 0,
        ];

        foreach ($porTransportista as &$t) {
            $capacidad = (int) $t['capacidad_total'];
            $proyectada = (int) $t['capacidad_proyectada'];
            if ($capacidad <= 0) {
                $t['alertas'][] = [
                    'tipo' => 'sin_capacidad',
                    'nivel' => 'info',
                    'texto' => 'Capacidad de unidad no configurada.',
                ];
                $resumen['sin_capacidad']++;
            }
            if ((int) $t['rutas_activas'] > 1) {
                $t['alertas'][] = [
                    'tipo' => 'rutas_activas_multiples',
                    'nivel' => 'danger',
                    'texto' => 'Tiene mas de una ruta en proceso.',
                ];
            }
            if ($capacidad > 0 && $proyectada >= $capacidad) {
                $t['estatus_operativo'] = 'saturado';
                $t['recomendacion'] = 'No asignar';
                $t['alertas'][] = [
                    'tipo' => 'capacidad_saturada',
                    'nivel' => 'danger',
                    'texto' => 'Capacidad proyectada al limite.',
                ];
                $resumen['saturados']++;
            } elseif ($capacidad > 0 && $proyectada >= (int) ceil($capacidad * 0.8)) {
                $t['estatus_operativo'] = 'advertencia';
                $t['recomendacion'] = 'Con cuidado';
                $t['alertas'][] = [
                    'tipo' => 'capacidad_advertencia',
                    'nivel' => 'warning',
                    'texto' => 'Capacidad proyectada arriba del 80%.',
                ];
                $resumen['advertencia']++;
            } elseif ((int) $t['rutas_activas'] > 0) {
                $t['estatus_operativo'] = 'en_ruta';
                $t['recomendacion'] = 'Evaluar paso de ruta';
                $resumen['en_ruta']++;
            } elseif ((int) $t['rutas_programadas'] > 0) {
                $t['estatus_operativo'] = 'programado';
                $t['recomendacion'] = 'Agenda ocupada';
                $resumen['programados']++;
            } else {
                $t['estatus_operativo'] = 'disponible';
                $t['recomendacion'] = 'Disponible';
                $resumen['disponibles']++;
            }

            $t['capacidad_disponible'] = $capacidad > 0 ? max(0, $capacidad - $proyectada) : null;
            $t['alertas'] = array_values(array_slice($t['alertas'], 0, 5));
        }
        unset($t);

        return [
            'resumen' => $resumen,
            'transportistas' => array_values($porTransportista),
            'capacidad_habilitada' => $tieneCapacidad,
        ];
    }

    private function textoCatalogo(?string $valor, int $max = 255): ?string
    {
        $txt = trim((string) $valor);
        if ($txt === '') {
            return null;
        }
        return mb_substr($txt, 0, $max, 'UTF-8');
    }

    private function textoCatalogoMayus(?string $valor, int $max = 255): ?string
    {
        $txt = $this->textoCatalogo($valor, $max);
        if ($txt === null) {
            return null;
        }
        return mb_substr($this->textoUbicacionBase($txt), 0, $max, 'UTF-8');
    }

    private function sanitizarUbicacionMayus(?string $valor, int $max = 120): string
    {
        $txt = $this->textoUbicacionBase($valor);
        if ($txt === '') {
            return '';
        }
        return mb_substr(mb_strtoupper($txt, 'UTF-8'), 0, $max, 'UTF-8');
    }

    private function decimalCatalogo($valor): ?float
    {
        if ($valor === null || trim((string) $valor) === '') {
            return null;
        }
        return is_numeric($valor) ? (float) $valor : null;
    }

    private function claveCatalogoDesdeNombre(string $texto): string
    {
        $txt = trim($texto);
        if (function_exists('iconv')) {
            $tmp = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $txt);
            if ($tmp !== false) {
                $txt = $tmp;
            }
        }
        $txt = strtoupper($txt);
        $txt = preg_replace('/[^A-Z0-9]+/', '_', $txt) ?? $txt;
        $txt = trim($txt, '_');
        return mb_substr($txt !== '' ? $txt : ('CEDIS_' . date('YmdHis')), 0, 80, 'UTF-8');
    }

    private function claveAgenciaUnica(string $clave, int $idAgencia = 0): string
    {
        $base = mb_substr($clave !== '' ? $clave : ('CEDIS_' . date('YmdHis')), 0, 76, 'UTF-8');
        $actual = $base;
        for ($i = 1; $i <= 30; $i++) {
            $row = $this->db->queryOne(
                'SELECT id_agencia FROM agencias_tracking WHERE clave_agencia = :clave AND id_agencia <> :id LIMIT 1',
                ['clave' => $actual, 'id' => $idAgencia]
            );
            if (!$row) {
                return $actual;
            }
            $sufijo = '_' . ($i + 1);
            $actual = mb_substr($base, 0, 80 - strlen($sufijo), 'UTF-8') . $sufijo;
        }
        return mb_substr($base, 0, 70, 'UTF-8') . '_' . substr(md5((string) microtime(true)), 0, 8);
    }

    private function usernameTransportistaUnico(string $username, int $idTransportista = 0): string
    {
        $base = mb_substr($username !== '' ? $username : ('TRK_' . date('YmdHis')), 0, 54, 'UTF-8');
        $actual = $base;
        for ($i = 1; $i <= 30; $i++) {
            $row = $this->db->queryOne(
                'SELECT id_transportista FROM transportistas_tracking WHERE username = :username AND id_transportista <> :id LIMIT 1',
                ['username' => $actual, 'id' => $idTransportista]
            );
            if (!$row) {
                return $actual;
            }
            $sufijo = '_' . ($i + 1);
            $actual = mb_substr($base, 0, 60 - strlen($sufijo), 'UTF-8') . $sufijo;
        }
        return mb_substr($base, 0, 50, 'UTF-8') . '_' . substr(md5((string) microtime(true)), 0, 8);
    }

    public function guardarAgenciaTracking(array $data): array
    {
        $idAgencia = (int) ($data['id_agencia'] ?? 0);
        $nombre = $this->textoCatalogo($data['nombre_agencia'] ?? '', 150);
        if ($nombre === null) {
            return ['success' => false, 'message' => 'El nombre del CEDIS es obligatorio.'];
        }

        $tipo = strtolower(trim((string) ($data['tipo_ubicacion'] ?? 'agencia')));
        if (!in_array($tipo, ['agencia', 'almacen_llegada'], true)) {
            $tipo = 'agencia';
        }

        $claveRaw = $this->textoCatalogo($data['clave_agencia'] ?? '', 80);
        $clave = $this->claveAgenciaUnica($this->claveCatalogoDesdeNombre($claveRaw ?: $nombre), $idAgencia);
        $activo = (int) (($data['activo'] ?? 1) ? 1 : 0);

        $params = [
            'clave_agencia' => $clave,
            'nombre_agencia' => $nombre,
            'tipo_ubicacion' => $tipo,
            'direccion' => $this->textoCatalogo($data['direccion'] ?? null, 255),
            'estado' => $this->textoCatalogoMayus($data['estado'] ?? null, 100),
            'municipio' => $this->textoCatalogoMayus($data['municipio'] ?? null, 120),
            'codigo_postal' => $this->textoCatalogo($data['codigo_postal'] ?? null, 10),
            'latitud' => $this->decimalCatalogo($data['latitud'] ?? null),
            'longitud' => $this->decimalCatalogo($data['longitud'] ?? null),
            'link_ubicacion' => $this->textoCatalogo($data['link_ubicacion'] ?? null, 1000),
            'telefono' => $this->textoCatalogo($data['telefono'] ?? null, 30),
            'extension' => $this->textoCatalogo($data['extension'] ?? null, 20),
            'encargado' => $this->textoCatalogo($data['encargado'] ?? null, 150),
            'email' => $this->textoCatalogo($data['email'] ?? null, 150),
            'horario' => $this->textoCatalogo($data['horario'] ?? null, 1000),
            'activo' => $activo,
        ];

        if ($idAgencia > 0) {
            $params['id_agencia'] = $idAgencia;
            $existe = $this->db->queryOne(
                'SELECT id_agencia FROM agencias_tracking WHERE id_agencia = :id_agencia LIMIT 1',
                ['id_agencia' => $idAgencia]
            );
            if (!$existe) {
                return ['success' => false, 'message' => 'CEDIS no encontrado.'];
            }
            $this->db->CRUD(
                "UPDATE agencias_tracking
                 SET clave_agencia = :clave_agencia,
                     nombre_agencia = :nombre_agencia,
                     tipo_ubicacion = :tipo_ubicacion,
                     direccion = :direccion,
                     estado = :estado,
                     municipio = :municipio,
                     codigo_postal = :codigo_postal,
                     latitud = :latitud,
                     longitud = :longitud,
                     link_ubicacion = :link_ubicacion,
                     telefono = :telefono,
                     extension = :extension,
                     encargado = :encargado,
                     email = :email,
                     horario = :horario,
                     activo = :activo
                 WHERE id_agencia = :id_agencia",
                $params
            );
            return ['success' => true, 'message' => 'CEDIS actualizado.', 'id_agencia' => $idAgencia];
        }

        $this->db->CRUD(
            "INSERT INTO agencias_tracking
             (clave_agencia, nombre_agencia, tipo_ubicacion, direccion, estado, municipio, codigo_postal,
              latitud, longitud, link_ubicacion, telefono, extension, encargado, email, horario, activo)
             VALUES
             (:clave_agencia, :nombre_agencia, :tipo_ubicacion, :direccion, :estado, :municipio, :codigo_postal,
              :latitud, :longitud, :link_ubicacion, :telefono, :extension, :encargado, :email, :horario, :activo)",
            $params
        );

        return ['success' => true, 'message' => 'CEDIS registrado.', 'id_agencia' => $this->db->lastInsertId()];
    }

    public function cambiarEstadoAgenciaTracking(int $idAgencia, int $activo): array
    {
        if ($idAgencia <= 0) {
            return ['success' => false, 'message' => 'CEDIS requerido.'];
        }
        $this->db->CRUD(
            'UPDATE agencias_tracking SET activo = :activo WHERE id_agencia = :id_agencia',
            ['activo' => $activo ? 1 : 0, 'id_agencia' => $idAgencia]
        );
        return ['success' => true, 'message' => $activo ? 'CEDIS activado.' : 'CEDIS desactivado.'];
    }

    public function guardarTransportistaTracking(array $data): array
    {
        $idTransportista = (int) ($data['id_transportista'] ?? 0);
        $nombre = $this->textoCatalogo($data['nombre_transportista'] ?? '', 180);
        if ($nombre === null) {
            return ['success' => false, 'message' => 'El nombre del transportista es obligatorio.'];
        }

        $tipo = strtolower(trim((string) ($data['tipo_transportista'] ?? '')));
        if (!in_array($tipo, ['interno', 'externo'], true)) {
            return ['success' => false, 'message' => 'Selecciona si el transportista es interno o externo.'];
        }

        $idAgencia = (int) ($data['id_agencia'] ?? 0);
        if ($idAgencia > 0) {
            $agencia = $this->db->queryOne(
                'SELECT id_agencia FROM agencias_tracking WHERE id_agencia = :id_agencia LIMIT 1',
                ['id_agencia' => $idAgencia]
            );
            if (!$agencia) {
                return ['success' => false, 'message' => 'El CEDIS seleccionado no existe.'];
            }
        }

        $params = [
            'id_agencia' => $idAgencia > 0 ? $idAgencia : null,
            'tipo_transportista' => $tipo,
            'nombre_transportista' => $nombre,
            'curp_rfc' => $this->textoCatalogo($data['curp_rfc'] ?? null, 25),
            'email' => $this->textoCatalogo($data['email'] ?? null, 150),
            'telefono' => $this->textoCatalogo($data['telefono'] ?? null, 30),
            'empresa_origen' => $this->textoCatalogo($data['empresa_origen'] ?? null, 180),
            'puesto' => $this->textoCatalogo($data['puesto'] ?? null, 120),
            'activo' => (int) (($data['activo'] ?? 1) ? 1 : 0),
        ];

        $extraSet = [];
        $extraInsertCols = [];
        $extraInsertVals = [];
        if ($this->columnaOpcionalExiste('transportistas_tracking', 'username')) {
            $username = $this->textoCatalogo($data['username'] ?? null, 60);
            if ($username === null && $idTransportista <= 0) {
                $username = mb_substr($this->claveCatalogoDesdeNombre($nombre), 0, 50, 'UTF-8') . '2026';
            }
            if ($username !== null) {
                $params['username'] = $this->usernameTransportistaUnico($username, $idTransportista);
                $extraSet[] = 'username = :username';
                $extraInsertCols[] = 'username';
                $extraInsertVals[] = ':username';
            }
        }
        $password = $this->textoCatalogo($data['password'] ?? '', 255);
        if ($password === null && $idTransportista <= 0 && $this->columnaOpcionalExiste('transportistas_tracking', 'password_hash')) {
            $password = '2026';
        }
        if ($password !== null && $this->columnaOpcionalExiste('transportistas_tracking', 'password_hash')) {
            $params['password_hash'] = $password;
            $extraSet[] = 'password_hash = :password_hash';
            $extraInsertCols[] = 'password_hash';
            $extraInsertVals[] = ':password_hash';
        }
        if ($this->columnaOpcionalExiste('transportistas_tracking', 'debe_cambiar_password')) {
            $debeCambiarPassword = $password !== null ? 1 : (int) (($data['debe_cambiar_password'] ?? 1) ? 1 : 0);
            if ($idTransportista <= 0) {
                $params['debe_cambiar_password'] = $debeCambiarPassword;
                $extraInsertCols[] = 'debe_cambiar_password';
                $extraInsertVals[] = ':debe_cambiar_password';
            } elseif ($password !== null) {
                $params['debe_cambiar_password'] = 1;
                $extraSet[] = 'debe_cambiar_password = :debe_cambiar_password';
            }
        }

        if ($idTransportista > 0) {
            $params['id_transportista'] = $idTransportista;
            $existe = $this->db->queryOne(
                'SELECT id_transportista FROM transportistas_tracking WHERE id_transportista = :id_transportista LIMIT 1',
                ['id_transportista' => $idTransportista]
            );
            if (!$existe) {
                return ['success' => false, 'message' => 'Transportista no encontrado.'];
            }
            $sqlExtra = $extraSet ? ",\n                     " . implode(",\n                     ", $extraSet) : '';
            $this->db->CRUD(
                "UPDATE transportistas_tracking
                 SET id_agencia = :id_agencia,
                     tipo_transportista = :tipo_transportista,
                     nombre_transportista = :nombre_transportista,
                     curp_rfc = :curp_rfc,
                     email = :email,
                     telefono = :telefono,
                     empresa_origen = :empresa_origen,
                     puesto = :puesto,
                     activo = :activo
                     {$sqlExtra}
                 WHERE id_transportista = :id_transportista",
                $params
            );
            return ['success' => true, 'message' => 'Transportista actualizado.', 'id_transportista' => $idTransportista];
        }

        $cols = array_merge([
            'id_agencia', 'tipo_transportista', 'nombre_transportista', 'curp_rfc', 'email',
            'telefono', 'empresa_origen', 'puesto', 'activo',
        ], $extraInsertCols);
        $vals = array_merge([
            ':id_agencia', ':tipo_transportista', ':nombre_transportista', ':curp_rfc', ':email',
            ':telefono', ':empresa_origen', ':puesto', ':activo',
        ], $extraInsertVals);

        $this->db->CRUD(
            'INSERT INTO transportistas_tracking (' . implode(', ', $cols) . ')
             VALUES (' . implode(', ', $vals) . ')',
            $params
        );

        return ['success' => true, 'message' => 'Transportista registrado.', 'id_transportista' => $this->db->lastInsertId()];
    }

    public function cambiarEstadoTransportistaTracking(int $idTransportista, int $activo): array
    {
        if ($idTransportista <= 0) {
            return ['success' => false, 'message' => 'Transportista requerido.'];
        }
        $this->db->CRUD(
            'UPDATE transportistas_tracking SET activo = :activo WHERE id_transportista = :id_transportista',
            ['activo' => $activo ? 1 : 0, 'id_transportista' => $idTransportista]
        );
        return ['success' => true, 'message' => $activo ? 'Transportista activado.' : 'Transportista desactivado.'];
    }

    public function guardarUnidadTransportistaTracking(array $data): array
    {
        $tabla = 'transportistas_capacidad_tracking';
        if (!$this->tablaOpcionalExiste($tabla)) {
            return ['success' => false, 'message' => 'La tabla de unidades/capacidad aun no existe.'];
        }
        foreach (['id_capacidad', 'id_transportista', 'capacidad_motos'] as $columna) {
            if (!$this->columnaOpcionalExiste($tabla, $columna)) {
                return ['success' => false, 'message' => "Falta la columna {$columna} en la tabla de unidades."];
            }
        }

        $idCapacidad = (int) ($data['id_capacidad'] ?? 0);
        $idTransportista = (int) ($data['id_transportista'] ?? 0);
        if ($idTransportista <= 0) {
            return ['success' => false, 'message' => 'Selecciona un transportista.'];
        }
        $transportista = $this->db->queryOne(
            'SELECT id_transportista FROM transportistas_tracking WHERE id_transportista = :id LIMIT 1',
            ['id' => $idTransportista]
        );
        if (!$transportista) {
            return ['success' => false, 'message' => 'Transportista no encontrado.'];
        }

        $capacidad = (int) ($data['capacidad_motos'] ?? 0);
        if ($capacidad <= 0) {
            return ['success' => false, 'message' => 'La capacidad debe ser mayor a cero.'];
        }

        $campos = [
            'id_transportista' => $idTransportista,
            'tipo_unidad' => $this->textoCatalogoMayus($data['tipo_unidad'] ?? null, 80),
            'capacidad_motos' => $capacidad,
            'marca' => $this->textoCatalogoMayus($data['marca'] ?? null, 80),
            'modelo' => $this->textoCatalogoMayus($data['modelo'] ?? null, 100),
            'anio' => isset($data['anio']) && trim((string) $data['anio']) !== '' ? (int) $data['anio'] : null,
            'color' => $this->textoCatalogoMayus($data['color'] ?? null, 60),
            'placa' => $this->textoCatalogoMayus($data['placa'] ?? null, 30),
            'numero_serie' => $this->textoCatalogoMayus($data['numero_serie'] ?? null, 80),
            'numero_motor' => $this->textoCatalogoMayus($data['numero_motor'] ?? null, 80),
            'numero_economico' => $this->textoCatalogoMayus($data['numero_economico'] ?? null, 60),
            'aseguradora' => $this->textoCatalogoMayus($data['aseguradora'] ?? null, 120),
            'poliza_seguro' => $this->textoCatalogoMayus($data['poliza_seguro'] ?? null, 80),
            'vigencia_seguro' => $this->textoCatalogo($data['vigencia_seguro'] ?? null, 20),
            'observaciones' => $this->textoCatalogo($data['observaciones'] ?? null, 500),
            'activo' => (int) (($data['activo'] ?? 1) ? 1 : 0),
        ];
        if (($campos['tipo_unidad'] ?? null) === null) {
            return ['success' => false, 'message' => 'El tipo de unidad es obligatorio.'];
        }

        $params = [];
        $columnas = [];
        foreach ($campos as $columna => $valor) {
            if ($this->columnaOpcionalExiste($tabla, $columna)) {
                $params[$columna] = $valor;
                $columnas[] = $columna;
            }
        }

        if (isset($params['activo']) && (int) $params['activo'] === 1) {
            $desactivarParams = ['id_transportista' => $idTransportista];
            $sqlExcepto = '';
            if ($idCapacidad > 0) {
                $desactivarParams['id_capacidad'] = $idCapacidad;
                $sqlExcepto = ' AND id_capacidad <> :id_capacidad';
            }
            $this->db->CRUD(
                "UPDATE {$tabla}
                 SET activo = 0
                 WHERE id_transportista = :id_transportista{$sqlExcepto}",
                $desactivarParams
            );
        }

        if ($idCapacidad > 0) {
            $existe = $this->db->queryOne(
                "SELECT id_capacidad FROM {$tabla} WHERE id_capacidad = :id LIMIT 1",
                ['id' => $idCapacidad]
            );
            if (!$existe) {
                return ['success' => false, 'message' => 'Unidad no encontrada.'];
            }
            $set = [];
            foreach ($columnas as $columna) {
                $set[] = "{$columna} = :{$columna}";
            }
            if ($this->columnaOpcionalExiste($tabla, 'fecha_actualizacion')) {
                $set[] = 'fecha_actualizacion = NOW()';
            }
            $params['id_capacidad'] = $idCapacidad;
            $this->db->CRUD(
                "UPDATE {$tabla}
                 SET " . implode(",\n                     ", $set) . "
                 WHERE id_capacidad = :id_capacidad",
                $params
            );
            return ['success' => true, 'message' => 'Unidad actualizada.', 'id_capacidad' => $idCapacidad];
        }

        $insertCols = $columnas;
        $insertVals = array_map(static fn($col) => ':' . $col, $columnas);
        if ($this->columnaOpcionalExiste($tabla, 'fecha_alta')) {
            $insertCols[] = 'fecha_alta';
            $insertVals[] = 'NOW()';
        }
        if ($this->columnaOpcionalExiste($tabla, 'fecha_actualizacion')) {
            $insertCols[] = 'fecha_actualizacion';
            $insertVals[] = 'NOW()';
        }
        $this->db->CRUD(
            "INSERT INTO {$tabla} (" . implode(', ', $insertCols) . ')
             VALUES (' . implode(', ', $insertVals) . ')',
            $params
        );

        return ['success' => true, 'message' => 'Unidad registrada.', 'id_capacidad' => $this->db->lastInsertId()];
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
    private function normalizarTextoZona(?string $valor): string
    {
        $txt = mb_strtoupper(trim((string) $valor), 'UTF-8');
        $map = [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'A', 'é' => 'E', 'í' => 'I', 'ó' => 'O', 'ú' => 'U', 'ü' => 'U', 'ñ' => 'N',
        ];
        return strtr($txt, $map);
    }

    private function sanitizarNombreRuta(string $nombre): string
    {
        $limpio = preg_replace('/^\s*(?:(?:#|BOR-|RUTA-)\s*\d+\s*)+/i', '', $nombre);
        $limpio = preg_replace('/\s+/', ' ', (string) $limpio);
        return mb_strtoupper(trim((string) $limpio), 'UTF-8');
    }

    private function obtenerRutasConNombreDuplicado(string $nombre, int $idRuta = 0, int $limit = 5): array
    {
        $nombreLimpio = $this->sanitizarNombreRuta($nombre);
        if ($nombreLimpio === '') {
            return [];
        }
        $nombreNormalizado = mb_strtoupper($nombreLimpio, 'UTF-8');
        $candidatas = $this->db->queryAll(
            "SELECT
                id_ruta,
                nombre_ruta,
                estatus_ruta,
                DATE_FORMAT(fecha_programada, '%d/%m/%Y') AS fecha_programada_fmt,
                TIME_FORMAT(COALESCE(act_hora_1, hora_inicial), '%H:%i') AS hora_salida_fmt
             FROM asigna_horas_tracking
             WHERE estatus_ruta <> 'cancelada'
               AND id_ruta <> :id
               AND (
                   UPPER(TRIM(nombre_ruta)) = :nombre
                   OR nombre_ruta LIKE :like_nombre
               )
             ORDER BY id_ruta DESC
             LIMIT 250",
            [
                'id' => $idRuta,
                'nombre' => $nombreNormalizado,
                'like_nombre' => '%' . $nombreLimpio . '%',
            ]
        ) ?: [];

        $duplicadas = array_values(array_filter($candidatas, function (array $ruta) use ($nombreNormalizado): bool {
            return mb_strtoupper($this->sanitizarNombreRuta((string) ($ruta['nombre_ruta'] ?? '')), 'UTF-8') === $nombreNormalizado;
        }));

        return array_slice($duplicadas, 0, max(1, $limit));
    }

    public function validarNombreRutaDisponible(string $nombre, int $idRuta = 0): array
    {
        $nombreLimpio = $this->sanitizarNombreRuta($nombre);
        if ($nombreLimpio === '') {
            return [
                'success' => true,
                'disponible' => false,
                'nombre_limpio' => '',
                'message' => 'El nombre de la ruta es obligatorio.',
            ];
        }

        $duplicadas = $this->obtenerRutasConNombreDuplicado($nombreLimpio, $idRuta, 5);
        return [
            'success' => true,
            'disponible' => empty($duplicadas),
            'nombre_limpio' => $nombreLimpio,
            'duplicados' => $duplicadas,
            'message' => empty($duplicadas)
                ? 'Nombre disponible.'
                : 'Nombre no permitido, ya existe una ruta con este nombre.',
        ];
    }

    private function esZonaTransportistaInterno(?string $estado, ?string $municipio): bool
    {
        $est = $this->normalizarTextoZona($estado);
        $mun = $this->normalizarTextoZona($municipio);
        if (in_array($est, ['CDMX', 'CIUDAD DE MEXICO', 'DISTRITO FEDERAL'], true)) {
            return true;
        }
        if (!in_array($est, ['ESTADO DE MEXICO', 'EDOMEX', 'MEXICO'], true)) {
            return false;
        }
        $municipiosOk = [
            'MIGUEL HIDALGO', 'CUAUHTEMOC', 'BENITO JUAREZ', 'ALVARO OBREGON', 'AZCAPOTZALCO',
            'COYOACAN', 'IZTAPALAPA', 'IZTACALCO', 'GUSTAVO A MADERO', 'VENUSTIANO CARRANZA',
            'TLALNEPANTLA', 'TLALNEPANTLA DE BAZ', 'CUAUTITLAN IZCALLI', 'CUAUTITLAN',
            'TULTITLAN', 'NAUCALPAN', 'NAUCALPAN DE JUAREZ', 'ATIZAPAN DE ZARAGOZA',
            'NEZAHUALCOYOTL', 'NEZA', 'ECATEPEC', 'ECATEPEC DE MORELOS', 'COACALCO',
            'COACALCO DE BERRIOZABAL', 'NICOLAS ROMERO', 'CHIMALHUACAN', 'LOS REYES',
            'LA PAZ', 'TECAMAC', 'HUIXQUILUCAN', 'CHALCO', 'VALLE DE CHALCO',
        ];
        return in_array($mun, $municipiosOk, true);
    }

    private function esCedisDestinoInternoPermitido(array $cedis): bool
    {
        $id = (int) ($cedis['id_agencia'] ?? 0);
        $clave = $this->normalizarTextoZona((string) ($cedis['clave_agencia'] ?? ''));
        return in_array($id, [1, 2], true)
            || in_array($clave, ['LOMAS_PLAZA_MAXIKASH', 'TLALNEPANTLA_MAXIKASH'], true);
    }

    private function cedisValorUbicacionUtil(?string $valor): bool
    {
        $txt = $this->normalizarTextoZona((string) $valor);
        return $txt !== ''
            && !in_array($txt, ['NA', 'N/A', 'NO APLICA', 'SIN DATOS', 'NO DISPONIBLE', 'EN ESPERA DE DATOS', 'SIN UBICACION'], true);
    }

    private function cedisTieneUbicacionOperativa(array $cedis): bool
    {
        $lat = $cedis['latitud'] ?? null;
        $lng = $cedis['longitud'] ?? null;
        if (is_numeric($lat) && is_numeric($lng) && (float)$lat !== 0.0 && (float)$lng !== 0.0) {
            return true;
        }
        return $this->cedisValorUbicacionUtil($cedis['estado'] ?? null)
            && $this->cedisValorUbicacionUtil($cedis['municipio'] ?? null);
    }

    private function validarConflictosRuta(int $idRuta, string $nombre, string $fechaStr, ?string $horaFmt, int $idTransportista): array
    {
        $conflictos = [];

        $rutasNombre = $this->obtenerRutasConNombreDuplicado($nombre, $idRuta, 5);

        if (!empty($rutasNombre)) {
            $conflictos[] = [
                'tipo' => 'nombre_ruta',
                'message' => 'Nombre no permitido, ya existe una ruta con este nombre.',
                'rutas' => $rutasNombre,
            ];
        }

        if ($idTransportista > 0 && $horaFmt !== null) {
            $rutasHorario = $this->db->queryAll(
                "SELECT
                    atr.id_ruta,
                    atr.nombre_ruta,
                    atr.estatus_ruta,
                    DATE_FORMAT(atr.fecha_programada, '%d/%m/%Y') AS fecha_programada_fmt,
                    TIME_FORMAT(COALESCE(atr.act_hora_1, atr.hora_inicial), '%H:%i') AS hora_salida_fmt,
                    tt.nombre_transportista
                 FROM asigna_horas_tracking atr
                 LEFT JOIN transportistas_tracking tt ON tt.id_transportista = atr.id_transportista
                 WHERE atr.id_transportista = :id_transportista
                   AND atr.fecha_programada = :fecha
                   AND TIME_FORMAT(COALESCE(atr.act_hora_1, atr.hora_inicial), '%H:%i:%s') = :hora
                   AND atr.estatus_ruta <> 'cancelada'
                   AND atr.id_ruta <> :id
                 ORDER BY atr.id_ruta DESC
                 LIMIT 5",
                [
                    'id_transportista' => $idTransportista,
                    'fecha' => $fechaStr,
                    'hora' => $horaFmt,
                    'id' => $idRuta,
                ]
            ) ?: [];

            if (!empty($rutasHorario)) {
                $conflictos[] = [
                    'tipo' => 'transportista_horario',
                    'message' => 'El transportista ya tiene una ruta asignada para la misma fecha y hora.',
                    'rutas' => $rutasHorario,
                ];
            }
        }

        return $conflictos;
    }

    public function guardarRuta(array $data, int $idUsuario): array
    {
        $modo      = trim((string) ($data['modo'] ?? 'borrador'));
        $idRuta    = (int) ($data['id_ruta'] ?? 0);
        $nombre    = $this->sanitizarNombreRuta((string) ($data['nombre_ruta'] ?? ''));
        $estado    = $this->sanitizarUbicacionMayus($data['estado'] ?? '', 100);
        $municipio = $this->sanitizarUbicacionMayus($data['municipio'] ?? '', 100);
        $fechaStr  = trim((string) ($data['fecha_programada'] ?? ''));
        $creditos  = is_array($data['creditos'] ?? null) ? $data['creditos'] : [];
        $tipoTransportista = strtolower(trim((string) ($data['tipo_transportista'] ?? '')));
        $idTransportista   = (int) ($data['id_transportista'] ?? 0);
        $idAgenciaTracking = (int) ($data['id_agencia_tracking'] ?? 0);
        $idCedisDestino    = (int) ($data['id_cedis_destino'] ?? 0);
        if (!in_array($tipoTransportista, ['interno', 'externo'], true)) {
            $tipoTransportista = '';
        }
        if (($estado === '' || $municipio === '') && !empty($creditos)) {
            $estadosRuta = [];
            $municipiosRuta = [];
            foreach ($creditos as $det) {
                $e = $this->sanitizarUbicacionMayus($det['estado'] ?? '', 100);
                $m = $this->sanitizarUbicacionMayus($det['municipio'] ?? '', 100);
                if ($e !== '') $estadosRuta[$e] = true;
                if ($m !== '') $municipiosRuta[$m] = true;
            }
            if ($estado === '' && !empty($estadosRuta)) {
                $estado = count($estadosRuta) > 1 ? 'MULTIPLES ESTADOS' : array_key_first($estadosRuta);
            }
            if ($municipio === '' && !empty($municipiosRuta)) {
                $municipio = count($municipiosRuta) > 1 ? 'MULTIPLES MUNICIPIOS' : array_key_first($municipiosRuta);
            }
        }

        // Validaciones comunes
        if ($nombre === '') {
            return ['success' => false, 'message' => 'El nombre de la ruta es obligatorio.'];
        }
        if (mb_strlen($nombre, 'UTF-8') > 100) {
            return ['success' => false, 'message' => 'El nombre de la ruta no puede exceder 100 caracteres.'];
        }
        if ($modo === 'borrador' && $fechaStr === '') {
            $diasMinimosBorrador = ConfigMotosAdj::obtenerDiasMinimosRuta();
            $fechaStr = (new \DateTime('today'))->modify('+' . $diasMinimosBorrador . ' days')->format('Y-m-d');
        }
        if ($modo !== 'borrador' && empty($creditos)) {
            return ['success' => false, 'message' => 'Debe agregar al menos un crédito a la ruta.'];
        }
        if ($modo !== 'borrador' && $estado === '') {
            return ['success' => false, 'message' => 'El estado es obligatorio.'];
        }
        if ($modo !== 'borrador' && $municipio === '') {
            return ['success' => false, 'message' => 'El municipio es obligatorio.'];
        }
        if ($modo !== 'borrador' && $tipoTransportista !== '' && $idTransportista <= 0) {
            return ['success' => false, 'message' => 'Selecciona un transportista válido.'];
        }
        if ($modo !== 'borrador' && $tipoTransportista === 'externo' && $idAgenciaTracking <= 0) {
            return ['success' => false, 'message' => 'Selecciona el CEDIS relacionado para el transportista externo.'];
        }
        if ($modo !== 'borrador' && $tipoTransportista !== '' && $idCedisDestino <= 0) {
            return ['success' => false, 'message' => 'Selecciona el CEDIS destino del transportista.'];
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
                return ['success' => false, 'message' => 'El transportista seleccionado no existe o está inactivo.'];
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
                return ['success' => false, 'message' => 'El CEDIS seleccionado no coincide con el transportista externo.'];
            }
        }
        $cedisDestino = null;
        if ($idCedisDestino > 0) {
            $cedisDestino = $this->db->queryOne(
                "SELECT id_agencia, clave_agencia, nombre_agencia, estado, municipio, latitud, longitud
                 FROM agencias_tracking
                 WHERE id_agencia = :id AND activo = 1
                 LIMIT 1",
                ['id' => $idCedisDestino]
            );
            if (!$cedisDestino) {
                return ['success' => false, 'message' => 'El CEDIS destino no existe o esta inactivo.'];
            }
            if (!$this->cedisTieneUbicacionOperativa($cedisDestino)) {
                return ['success' => false, 'message' => 'El CEDIS destino no tiene ubicacion operativa suficiente. Completa coordenadas o Estado/Municipio antes de asignarlo.'];
            }
        }
        if ($modo !== 'borrador' && $tipoTransportista === 'interno') {
            if ($cedisDestino && !$this->esCedisDestinoInternoPermitido($cedisDestino)) {
                return ['success' => false, 'message' => 'Para transportistas internos solo puedes seleccionar LOMAS PLAZA MAXIKASH o TLALNEPANTLA MAXIKASH como destino.'];
            }
            if ($cedisDestino && !$this->esZonaTransportistaInterno($cedisDestino['estado'] ?? null, $cedisDestino['municipio'] ?? null)) {
                return ['success' => false, 'message' => 'Los transportistas internos solo pueden tener destino en CDMX o zona metropolitana.'];
            }
            foreach ($creditos as $det) {
                if (!$this->esZonaTransportistaInterno((string) ($det['estado'] ?? ''), (string) ($det['municipio'] ?? ''))) {
                    $idCreditoZona = (int) ($det['id_credito'] ?? 0);
                    return [
                        'success' => false,
                        'message' => 'El credito #' . $idCreditoZona . ' esta fuera de CDMX/zona metropolitana. Asigna un transportista externo para esta ruta.',
                    ];
                }
            }
        }

        $conflictosRuta = $this->validarConflictosRuta($idRuta, $nombre, $fechaStr, $horaFmt, $idTransportista);
        if (!empty($conflictosRuta)) {
            return [
                'success' => false,
                'tipo' => 'conflictos_ruta',
                'message' => 'No se puede guardar la ruta porque existen conflictos.',
                'errores' => $conflictosRuta,
            ];
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
                         tipo_transportista = :tt, id_transportista = :it, id_agencia_tracking = :iat, id_cedis_destino = :icd,
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
                            'icd'=> $idCedisDestino > 0 ? $idCedisDestino : null,
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
                         (nombre_ruta, estado, municipio, fecha_programada, hora_inicial, tipo_transportista, id_transportista, id_agencia_tracking, id_cedis_destino, estatus_ruta, creado_por, fecha_creacion, fecha_actualizacion)
                     VALUES (:n, :e, :m, :f, :hi, :tt, :it, :iat, :icd, :er, :cp, :fc, :fa)',
                    [
                        'n'  => $nombre,
                        'e'  => $estado,
                        'm'  => $municipio,
                        'f'  => $fechaStr,
                        'hi' => $horaFmt,
                        'tt' => $tipoTransportista !== '' ? $tipoTransportista : null,
                        'it' => $idTransportista > 0 ? $idTransportista : null,
                        'iat'=> $idAgenciaTracking > 0 ? $idAgenciaTracking : null,
                        'icd'=> $idCedisDestino > 0 ? $idCedisDestino : null,
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
                        'es' => $this->sanitizarUbicacionMayus($det['estado'] ?? '', 100),
                        'mu' => $this->sanitizarUbicacionMayus($det['municipio'] ?? '', 100),
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
            return ['success' => true, 'id_ruta' => $idRuta, 'estatus_ruta' => $estatusRuta];
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
            $aliases = $this->aliasesEstadoTracking($estado);
            $phs = [];
            foreach ($aliases as $i => $alias) {
                $key = "estado{$i}";
                $phs[] = ":{$key}";
                $params[$key] = $alias;
            }
            $where[] = $this->sqlEstadoNormalizado('atr.estado') . ' IN (' . implode(',', $phs) . ')';
        }
        if ($municipio !== null && $municipio !== '') {
            $where[]              = $this->sqlEstadoNormalizado('atr.municipio') . ' = :municipio';
            $params['municipio']  = $this->sanitizarUbicacionMayus($municipio, 120);
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
            atr.fecha_creacion,
            atr.fecha_actualizacion,
            DATE_FORMAT(atr.fecha_creacion, \'%d/%m/%Y %H:%i\') AS fecha_creacion_fmt,
            DATE_FORMAT(atr.fecha_actualizacion, \'%d/%m/%Y %H:%i\') AS fecha_actualizacion_fmt,
            TIME_FORMAT(atr.hora_inicial, \'%H:%i\') AS hora_inicial,
            TIME_FORMAT(atr.act_hora_1,   \'%H:%i\') AS act_hora_1,
            atr.tipo_transportista,
            atr.id_transportista,
            atr.id_agencia_tracking,
            atr.id_cedis_destino,
            tt.nombre_transportista,
            tt.empresa_origen AS transportista_empresa,
            tt.telefono AS transportista_telefono,
            tt.email AS transportista_email,
            ag.nombre_agencia,
            ag.clave_agencia,
            cedis_dest.nombre_agencia AS cedis_destino_nombre,
            cedis_dest.direccion AS cedis_destino_direccion,
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
        LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = atr.id_cedis_destino
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
            atr.fecha_creacion,
            atr.fecha_actualizacion,
            DATE_FORMAT(atr.fecha_creacion, \'%d/%m/%Y %H:%i\') AS fecha_creacion_fmt,
            DATE_FORMAT(atr.fecha_actualizacion, \'%d/%m/%Y %H:%i\') AS fecha_actualizacion_fmt,
            TIME_FORMAT(atr.hora_inicial, \'%H:%i\') AS hora_inicial,
            TIME_FORMAT(atr.act_hora_1,   \'%H:%i\') AS act_hora_1,
            atr.tipo_transportista,
            atr.id_transportista,
            atr.id_agencia_tracking,
            atr.id_cedis_destino,
            tt.nombre_transportista,
            tt.empresa_origen AS transportista_empresa,
            tt.telefono AS transportista_telefono,
            tt.email AS transportista_email,
            ag.nombre_agencia,
            ag.clave_agencia,
            cedis_dest.nombre_agencia AS cedis_destino_nombre,
            cedis_dest.direccion AS cedis_destino_direccion,
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
        LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = atr.id_cedis_destino
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
                    cedis_dest.nombre_agencia AS cedis_destino_nombre,
                    cedis_dest.direccion AS cedis_destino_direccion,
                    cedis_dest.estado AS cedis_destino_estado,
                    cedis_dest.municipio AS cedis_destino_municipio,
                    cedis_dest.codigo_postal AS cedis_destino_codigo_postal,
                    cedis_dest.telefono AS cedis_destino_telefono,
                    cedis_dest.encargado AS cedis_destino_encargado,
                    cedis_dest.email AS cedis_destino_email,
                    cedis_dest.horario AS cedis_destino_horario,
                    cedis_dest.link_ubicacion AS cedis_destino_link_ubicacion,
                    TRIM(CONCAT_WS(' ', creador.nombres, creador.segundo_nombre, creador.apellidop, creador.apellidom)) AS creado_por_nombre,
                    CONCAT(DATE_FORMAT(atr.fecha_programada, '%d/'), ELT(MONTH(atr.fecha_programada), 'Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'), DATE_FORMAT(atr.fecha_programada, '/%Y')) AS fecha_programada_fmt,
                    DATE_FORMAT(atr.fecha_creacion,   '%d/%m/%Y %H:%i') AS fecha_creacion_fmt,
                    DATE_FORMAT(atr.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt,
                    DATE_FORMAT(atr.fecha_cancelacion, '%d/%m/%Y %H:%i') AS fecha_cancelacion_fmt
                 FROM asigna_horas_tracking atr
                  LEFT JOIN transportistas_tracking tt ON tt.id_transportista = atr.id_transportista
                  LEFT JOIN agencias_tracking ag ON ag.id_agencia = atr.id_agencia_tracking
                  LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = atr.id_cedis_destino
                  LEFT JOIN persona creador ON creador.id = atr.creado_por
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
                    DATE_FORMAT(atd.fecha_agregado, '%d/%m/%Y %H:%i') AS fecha_agregado_fmt,
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
    public function agregarCreditoRutaExistente(int $idRuta, int $idCredito, int $idUsuario = 0): array
    {
        if ($idRuta <= 0 || $idCredito <= 0) {
            return ['success' => false, 'message' => 'Ruta y credito son requeridos.'];
        }

        try {
            $ruta = $this->db->queryOne(
                "SELECT
                    atr.id_ruta,
                    atr.nombre_ruta,
                    atr.estatus_ruta,
                    atr.estado,
                    atr.municipio,
                    atr.tipo_transportista,
                    atr.id_transportista,
                    tt.tipo_transportista AS tipo_transportista_real
                 FROM asigna_horas_tracking atr
                 LEFT JOIN transportistas_tracking tt ON tt.id_transportista = atr.id_transportista
                 WHERE atr.id_ruta = :id
                 LIMIT 1",
                ['id' => $idRuta]
            );
            if (!$ruta) {
                return ['success' => false, 'message' => 'Ruta no encontrada.'];
            }

            $estatusRuta = strtolower((string)($ruta['estatus_ruta'] ?? ''));
            if (in_array($estatusRuta, ['cancelada', 'completado', 'concluida'], true)) {
                return ['success' => false, 'message' => 'La ruta ya no permite agregar creditos.'];
            }

            $credito = $this->db->queryOne(
                "SELECT
                    ao.id_credito,
                    ao.nombre_cliente,
                    ao.moto_marca,
                    ao.moto_modelo,
                    ao.moto_no_serie AS bin,
                    ao.log_estado AS estado,
                    ao.log_ciudad AS municipio,
                    ao.log_direccion AS direccion
                 FROM adj_operacion ao
                 WHERE ao.id_credito = :id_credito
                 LIMIT 1",
                ['id_credito' => $idCredito]
            );
            if (!$credito) {
                return ['success' => false, 'message' => 'Credito no encontrado.'];
            }

            $estadoCredito = $this->sanitizarUbicacionMayus($credito['estado'] ?? '', 100);
            $municipioCredito = $this->sanitizarUbicacionMayus($credito['municipio'] ?? '', 100);
            if ($estadoCredito === '' && $municipioCredito === '') {
                return ['success' => false, 'message' => 'El credito no tiene estado ni municipio registrado.'];
            }

            $asignado = $this->db->queryOne(
                "SELECT atr.id_ruta, atr.nombre_ruta
                 FROM asigna_horas_tracking_detalle atd
                 INNER JOIN asigna_horas_tracking atr ON atr.id_ruta = atd.id_ruta
                 WHERE atd.id_credito = :id_credito
                   AND atr.estatus_ruta NOT IN ('cancelada','completado','concluida')
                 LIMIT 1",
                ['id_credito' => $idCredito]
            );
            if ($asignado) {
                return [
                    'success' => false,
                    'message' => 'El credito ya esta asignado a la ruta #' . (int)$asignado['id_ruta'] . '.',
                ];
            }

            $this->db->beginTransaction();

            $ordenRow = $this->db->queryOne(
                'SELECT COALESCE(MAX(orden_ruta), 0) + 1 AS siguiente FROM asigna_horas_tracking_detalle WHERE id_ruta = :id',
                ['id' => $idRuta]
            );
            $orden = (int)($ordenRow['siguiente'] ?? 1);
            if ($orden <= 0) $orden = 1;

            $modelo = trim(implode(' ', array_filter([
                (string)($credito['moto_marca'] ?? ''),
                (string)($credito['moto_modelo'] ?? ''),
            ])));
            $fechaAgregado = date('Y-m-d H:i:s');

            $this->db->CRUD(
                'INSERT INTO asigna_horas_tracking_detalle
                    (id_ruta, id_credito, modelo, bin, estado, municipio, direccion, orden_ruta, estatus_confirmacion_gestor, fecha_agregado)
                 VALUES (:ir, :ic, :mo, :bi, :es, :mu, :di, :or, :cf, :fg)',
                [
                    'ir' => $idRuta,
                    'ic' => $idCredito,
                    'mo' => mb_substr($modelo, 0, 100),
                    'bi' => mb_substr(trim((string)($credito['bin'] ?? '')), 0, 100),
                    'es' => mb_substr($estadoCredito, 0, 100),
                    'mu' => mb_substr($municipioCredito, 0, 100),
                    'di' => mb_substr(trim((string)($credito['direccion'] ?? '')), 0, 200),
                    'or' => $orden,
                    'cf' => 'pendiente',
                    'fg' => $fechaAgregado,
                ]
            );

            $geoRows = $this->db->queryAll(
                "SELECT DISTINCT estado, municipio
                 FROM asigna_horas_tracking_detalle
                 WHERE id_ruta = :id",
                ['id' => $idRuta]
            ) ?: [];
            $estados = [];
            $municipios = [];
            foreach ($geoRows as $row) {
                $e = $this->sanitizarUbicacionMayus($row['estado'] ?? '', 100);
                $m = $this->sanitizarUbicacionMayus($row['municipio'] ?? '', 100);
                if ($e !== '') $estados[$e] = true;
                if ($m !== '') $municipios[$m] = true;
            }
            $estadoRuta = count($estados) > 1 ? 'MULTIPLES ESTADOS' : (array_key_first($estados) ?: (string)($ruta['estado'] ?? ''));
            $municipioRuta = count($municipios) > 1 ? 'MULTIPLES MUNICIPIOS' : (array_key_first($municipios) ?: (string)($ruta['municipio'] ?? ''));

            $this->db->CRUD(
                'UPDATE asigna_horas_tracking
                 SET estado = :estado, municipio = :municipio, fecha_actualizacion = :fecha
                 WHERE id_ruta = :id',
                [
                    'estado' => $estadoRuta,
                    'municipio' => $municipioRuta,
                    'fecha' => date('Y-m-d H:i:s'),
                    'id' => $idRuta,
                ]
            );

            $this->db->commit();
            return ['success' => true, 'message' => 'Credito agregado a la ruta.', 'id_ruta' => $idRuta, 'orden_ruta' => $orden, 'fecha_agregado' => $fechaAgregado];
        } catch (\Throwable $e) {
            try {
                $this->db->rollback();
            } catch (\Throwable $ignored) {
            }
            return ['success' => false, 'message' => 'No se pudo agregar el credito: ' . $e->getMessage()];
        }
    }

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
     * Elimina una ruta siempre que siga en estatus borrador.
     *
     * @return array{success:bool, message?:string}
     */
    public function eliminarBorrador(int $idRuta): array
    {
        if ($idRuta <= 0) {
            return ['success' => false, 'message' => 'Ruta requerida.'];
        }

        $enTransaccion = false;
        try {
            $ruta = $this->db->queryOne(
                "SELECT id_ruta, estatus_ruta
                 FROM asigna_horas_tracking
                 WHERE id_ruta = :id
                 LIMIT 1",
                ['id' => $idRuta]
            );
            if (!$ruta) {
                return ['success' => false, 'message' => 'Borrador no encontrado.'];
            }
            if ((string) $ruta['estatus_ruta'] !== 'borrador') {
                return ['success' => false, 'message' => 'Solo se pueden borrar rutas en borrador.'];
            }

            $this->db->beginTransaction();
            $enTransaccion = true;
            $this->db->CRUD('DELETE FROM asigna_horas_tracking_detalle WHERE id_ruta = :id', ['id' => $idRuta]);
            $this->db->CRUD('DELETE FROM asigna_horas_tracking_usuarios WHERE id_ruta = :id', ['id' => $idRuta]);
            $this->db->CRUD('DELETE FROM asigna_horas_tracking WHERE id_ruta = :id', ['id' => $idRuta]);
            $this->db->commit();

            return ['success' => true, 'message' => 'Borrador eliminado correctamente.'];
        } catch (\Throwable $e) {
            if ($enTransaccion) {
                $this->db->rollback();
            }
            return ['success' => false, 'message' => 'Error al eliminar el borrador.'];
        }
    }

    /**
     * Cancela una ruta registrada o iniciada.
     *
     * @return array{success:bool, message?:string}
     */
    public function cancelarRuta(int $idRuta, string $motivo, int $idUsuario, bool $permisoEspecial = false): array
    {
        $motivo = trim(preg_replace('/\s+/', ' ', $motivo));
        if (!$permisoEspecial) {
            return ['success' => false, 'message' => 'No tienes permiso para cancelar rutas registradas.'];
        }
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
            if ((string) $ruta['estatus_ruta'] === 'borrador') {
                return ['success' => false, 'message' => 'Los borradores se eliminan desde la pestana de borradores.'];
            }
            if ((string) $ruta['estatus_ruta'] === 'cancelada') {
                return ['success' => false, 'message' => 'La ruta ya se encuentra cancelada.'];
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
        $diasMinimos = ConfigMotosAdj::obtenerDiasMinimosRuta();
        $hoy      = new \DateTime('today');
        $minFecha = (clone $hoy)->modify('+' . $diasMinimos . ' days');
        if ($fecha < $minFecha) {
            return 'La fecha programada debe ser al menos ' . $diasMinimos . ' dia(s) despues de hoy.';
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
