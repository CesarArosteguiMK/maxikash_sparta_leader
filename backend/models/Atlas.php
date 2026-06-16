<?php

namespace Models;

use Core\Database;
use Core\Model;

class Atlas extends Model
{
    public const MODULO_ATLAS_SUCURSAL_PASO1 = 129;
    public const MODULO_ATLAS_SUCURSAL_PASO2 = 130;
    public const MODULO_ATLAS_RUTAS_COMBO_GESTOR_NIVELES = 138;
    public const MODULO_ATLAS_CREDITOS_OPERACION = 139;
    private const MODULOS_ATLAS_IDS = [129, 130, 132, 133, 134, 135, 136, 137, 138, 139];

    private static function asegurarPermisosSucursalesAtlas(Database $db): void
    {
        $permisos = [
            self::MODULO_ATLAS_SUCURSAL_PASO1 => [
                'nombre' => 'Atlas Sucursales Paso 1',
                'descripcion' => 'Permite capturar datos base, fiscales, bancarios y ubicacion de sucursales.',
            ],
            self::MODULO_ATLAS_SUCURSAL_PASO2 => [
                'nombre' => 'Atlas Sucursales Paso 2',
                'descripcion' => 'Permite capturar la asignacion operativa de gestores para sucursales.',
            ],
            132 => [
                'pestana' => 'Atlas',
                'nombre' => 'Atlas Rutas de gestores',
                'descripcion' => 'Acceso al modulo operativo de rutas de gestores.',
            ],
            133 => [
                'pestana' => 'Atlas',
                'nombre' => 'Atlas Catalogos Operativos',
                'descripcion' => 'Acceso a catalogos operativos de Atlas.',
            ],
            134 => [
                'pestana' => 'Atlas',
                'nombre' => 'Atlas Catalogos Comerciales',
                'descripcion' => 'Acceso a catalogos comerciales de Atlas.',
            ],
            135 => [
                'pestana' => 'Atlas',
                'nombre' => 'Atlas Presupuestos',
                'descripcion' => 'Acceso al modulo de presupuestos Atlas.',
            ],
            136 => [
                'pestana' => 'Atlas',
                'nombre' => 'Atlas Notificaciones App',
                'descripcion' => 'Acceso al modulo de notificaciones Atlas App.',
            ],
            137 => [
                'pestana' => 'Atlas',
                'nombre' => 'Atlas Accesos Atlas',
                'descripcion' => 'Acceso al modulo de accesos Atlas.',
            ],
            self::MODULO_ATLAS_CREDITOS_OPERACION => [
                'pestana' => 'Atlas',
                'nombre' => 'Atlas Creditos en Operacion',
                'descripcion' => 'Acceso al modulo de creditos de Maxi sincronizados para operacion Atlas.',
            ],
            self::MODULO_ATLAS_RUTAS_COMBO_GESTOR_NIVELES => [
                'pestana' => 'Permisos especiales',
                'nombre' => 'Combo gestor niveles de puesto',
                'descripcion' => 'Permite ver todos los niveles disponibles en el combo de gestor para crear rutas Atlas. No debe asignarse a gestores.',
            ],
        ];

        foreach ($permisos as $id => $permiso) {
            $datos = [
                'id' => (int)$id,
                'pestana' => $permiso['pestana'] ?? 'Permisos especiales',
                'nombre' => $permiso['nombre'],
                'descripcion' => $permiso['descripcion'],
            ];
            $existe = $db->queryOne("SELECT id FROM modulos_web WHERE id = :id LIMIT 1", ['id' => (int)$id]);
            if ($existe) {
                $db->CRUD("
                    UPDATE modulos_web
                    SET pestana = :pestana,
                        nombre = :nombre,
                        descripcion = :descripcion,
                        activo = 1
                    WHERE id = :id
                ", $datos);
                continue;
            }
            $db->CRUD("
                INSERT INTO modulos_web (id, pestana, nombre, descripcion, activo)
                VALUES (:id, :pestana, :nombre, :descripcion, 1)
            ", $datos);
        }
    }

    public static function permisosSucursalAtlas(int $usuarioId): array
    {
        try {
            $db = new Database();
            self::asegurarPermisosSucursalesAtlas($db);
        } catch (\Throwable $e) {
            // La pantalla puede seguir cargando; el guardado backend vuelve a validar permisos.
        }

        $mods = array_map('intval', (array)($_SESSION['modulos'] ?? []));
        $admin = in_array(1, $mods, true) || in_array(4, $mods, true);

        return [
            'paso1' => $admin || in_array(self::MODULO_ATLAS_SUCURSAL_PASO1, $mods, true),
            'paso2' => $admin || in_array(self::MODULO_ATLAS_SUCURSAL_PASO2, $mods, true),
            'modulo_paso1' => self::MODULO_ATLAS_SUCURSAL_PASO1,
            'modulo_paso2' => self::MODULO_ATLAS_SUCURSAL_PASO2,
            'usuario_id' => $usuarioId,
        ];
    }

    private static function asegurarColumnasPasoSucursal(Database $db): void
    {
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'paso_datos_completos', "TINYINT(1) NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'paso_datos_completos_at', "DATETIME NULL");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'paso_asignacion_completa', "TINYINT(1) NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'paso_asignacion_completa_at', "DATETIME NULL");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'calle', "VARCHAR(180) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'numero_exterior', "VARCHAR(40) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'numero_interior', "VARCHAR(80) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'colonia', "VARCHAR(120) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'divisional_persona_id', "INT NULL AFTER divisional_id");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'regional_persona_id', "INT NULL AFTER regional_id");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'supervisor_persona_id', "INT NULL AFTER supervisor_id");
        self::asegurarColumna($db, 'atlas_catalogo_sucursales', 'asesor_persona_id', "INT NULL AFTER asesor_id");
    }

    private static function asegurarConfiguracionAtlas(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_configuracion (
                clave VARCHAR(120) NOT NULL,
                valor TEXT NULL,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (clave)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private static function getConfiguracionCalidadSucursales(Database $db): array
    {
        self::asegurarConfiguracionAtlas($db);
        $row = $db->queryOne(
            "SELECT valor FROM atlas_configuracion WHERE clave = 'atlas_sucursales_sin_telefono_es_error' LIMIT 1"
        );
        return [
            'sin_telefono_es_error' => (int)($row['valor'] ?? 0) === 1 ? 1 : 0,
        ];
    }

    private static function minutosDesdeHoraConfig(?string $hora, int $fallback): int
    {
        $raw = trim((string)$hora);
        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $raw, $m)) {
            return $fallback;
        }
        return ((int)$m[1] * 60) + (int)$m[2];
    }

    private static function horaConfigDesdeMinutos(int $minutos): string
    {
        $minutos = max(0, min(23 * 60 + 59, $minutos));
        return str_pad((string)intdiv($minutos, 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)($minutos % 60), 2, '0', STR_PAD_LEFT);
    }

    private static function getConfiguracionHorarioOperativoRutas(Database $db): array
    {
        self::asegurarConfiguracionAtlas($db);
        $default = ['inicio' => '08:00', 'fin' => '20:00'];
        $row = $db->queryOne("SELECT valor FROM atlas_configuracion WHERE clave = 'atlas_rutas_horario_operativo' LIMIT 1");
        if (!$row) {
            $db->CRUD(
                "INSERT INTO atlas_configuracion (clave, valor)
                 VALUES ('atlas_rutas_horario_operativo', :valor)",
                ['valor' => json_encode($default, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
            );
        }
        $valor = json_decode((string)($row['valor'] ?? ''), true);
        if (!is_array($valor)) $valor = [];
        $inicioMin = self::minutosDesdeHoraConfig($valor['inicio'] ?? null, 8 * 60);
        $finMin = self::minutosDesdeHoraConfig($valor['fin'] ?? null, 20 * 60);
        if ($finMin <= $inicioMin) {
            $inicioMin = 8 * 60;
            $finMin = 20 * 60;
        }
        return [
            'inicio' => self::horaConfigDesdeMinutos($inicioMin),
            'fin' => self::horaConfigDesdeMinutos($finMin),
            'inicio_minutos' => $inicioMin,
            'fin_minutos' => $finMin,
            'duracion_minutos' => $finMin - $inicioMin,
        ];
    }

    public static function guardarConfiguracionCalidadSucursales(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarConfiguracionAtlas($db);
            $sinTelefonoEsError = self::activoVal($input['sin_telefono_es_error'] ?? 0);
            $db->CRUD(
                "INSERT INTO atlas_configuracion (clave, valor)
                 VALUES ('atlas_sucursales_sin_telefono_es_error', :valor)
                 ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
                ['valor' => (string)$sinTelefonoEsError]
            );
            return [
                'success' => true,
                'mensaje' => 'Configuracion guardada.',
                'datos' => ['sin_telefono_es_error' => $sinTelefonoEsError],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudo guardar la configuracion.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public static function guardarConfiguracionHorarioOperativoRutas(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarConfiguracionAtlas($db);
            $inicioMin = self::minutosDesdeHoraConfig(self::nullableStr($input['inicio'] ?? null), -1);
            $finMin = self::minutosDesdeHoraConfig(self::nullableStr($input['fin'] ?? null), -1);
            if ($inicioMin < 0 || $finMin < 0 || $finMin <= $inicioMin) {
                return ['success' => false, 'mensaje' => 'Captura un horario operativo valido. La hora fin debe ser mayor a la hora inicio.'];
            }
            $valor = [
                'inicio' => self::horaConfigDesdeMinutos($inicioMin),
                'fin' => self::horaConfigDesdeMinutos($finMin),
            ];
            $db->CRUD(
                "INSERT INTO atlas_configuracion (clave, valor)
                 VALUES ('atlas_rutas_horario_operativo', :valor)
                 ON DUPLICATE KEY UPDATE valor = VALUES(valor)",
                ['valor' => json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
            );
            return [
                'success' => true,
                'mensaje' => 'Horario operativo guardado.',
                'datos' => self::getConfiguracionHorarioOperativoRutas($db),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudo guardar el horario operativo.',
                'error' => $e->getMessage(),
            ];
        }
    }

    private static function asegurarDistribuidoresAtlas(Database $db): void
    {
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'nombre_comercial', "VARCHAR(180) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'razon_social', "VARCHAR(220) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'rfc', "VARCHAR(20) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'tipo_distribuidor', "VARCHAR(60) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'estatus', "VARCHAR(40) NOT NULL DEFAULT 'activo'");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'fecha_baja', "DATETIME NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'nombre_contacto', "VARCHAR(180) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'telefono_contacto', "VARCHAR(40) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'telefono_secundario', "VARCHAR(40) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'email_contacto', "VARCHAR(180) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'regimen_fiscal', "VARCHAR(180) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'constancia_fiscal_url', "TEXT NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'constancia_fiscal_nombre', "VARCHAR(220) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'constancia_fiscal_at', "DATETIME NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'tipo_motos', "VARCHAR(120) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'canal_venta', "VARCHAR(120) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'horario_atencion', "VARCHAR(180) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'dias_operacion', "VARCHAR(120) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'requiere_cita', "TINYINT(1) NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'tiempo_promedio_entrega', "VARCHAR(120) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'bloqueo_vigencia', "VARCHAR(20) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'bloqueo_fin_at', "DATETIME NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'created_by', "INT NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'updated_by', "INT NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'deleted_at', "DATETIME NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'observaciones', "TEXT NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'motivo_bloqueo', "VARCHAR(250) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'fecha_validacion', "DATETIME NULL");
        self::asegurarColumna($db, 'atlas_catalogo_distribuidores', 'validado_por', "INT NULL");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_catalogo_distribuidor_tipo_moto (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(120) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_atlas_dist_tipo_moto_nombre (nombre)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_catalogo_distribuidor_canal_venta (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(120) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_atlas_dist_canal_venta_nombre (nombre)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::sembrarCatalogoDistribuidor($db, 'atlas_catalogo_distribuidor_tipo_moto', ['Nuevas', 'Usadas', 'Adjudicadas', 'Seminuevas']);
        self::sembrarCatalogoDistribuidor($db, 'atlas_catalogo_distribuidor_canal_venta', ['Piso', 'Digital', 'Financiamiento', 'Marketplace', 'Convenio', 'Referido']);

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_asigna_presencia_distribuidor (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                distribuidor_id BIGINT NOT NULL,
                estado_id INT UNSIGNED NOT NULL,
                municipio_id INT UNSIGNED NOT NULL,
                estado VARCHAR(150) NOT NULL,
                municipio VARCHAR(180) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_atlas_pres_dist_mun (distribuidor_id, municipio_id),
                KEY idx_atlas_pres_dist_activo (distribuidor_id, activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_distribuidor_bitacora (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                distribuidor_id BIGINT NOT NULL,
                evento VARCHAR(80) NOT NULL,
                estatus_anterior VARCHAR(40) NULL,
                estatus_nuevo VARCHAR(40) NULL,
                motivo VARCHAR(250) NULL,
                bloqueo_vigencia VARCHAR(20) NULL,
                bloqueo_fin_at DATETIME NULL,
                usuario_id INT NULL,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_dist_bitacora_dist (distribuidor_id, fecha_alta)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::reactivarDistribuidoresVencidos($db);

        $db->CRUD("
            UPDATE atlas_catalogo_distribuidores
            SET nombre_comercial = COALESCE(NULLIF(nombre_comercial, ''), nombre),
                razon_social = COALESCE(NULLIF(razon_social, ''), nombre),
                estatus = CASE WHEN COALESCE(activo, 1) = 1 THEN COALESCE(NULLIF(estatus, ''), 'activo') ELSE 'inactivo' END
            WHERE nombre_comercial IS NULL
               OR nombre_comercial = ''
               OR razon_social IS NULL
               OR razon_social = ''
               OR estatus IS NULL
               OR estatus = ''
        ");
    }

    private static function asegurarTablaPlantillasNotificaciones(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_notificaciones_plantillas (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(150) NOT NULL,
                categoria VARCHAR(80) NOT NULL,
                asunto VARCHAR(180) NULL,
                mensaje_texto TEXT NULL,
                imagen_url TEXT NULL,
                html LONGTEXT NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_notif_plantillas_categoria (categoria),
                KEY idx_atlas_notif_plantillas_activo (activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $semillas = [
            [
                'nombre' => 'Feliz cumpleaÃƒÂ±os',
                'categoria' => 'cumpleanos',
                'asunto' => 'Feliz cumpleaÃƒÂ±os',
                'mensaje_texto' => 'Hoy celebramos contigo. Que este nuevo aÃƒÂ±o venga lleno de salud, alegrÃƒÂ­a y grandes momentos.',
                'html' => '<h2>Ã‚Â¡Feliz cumpleaÃƒÂ±os!</h2><p>Hoy celebramos contigo. Que este nuevo aÃƒÂ±o venga lleno de salud, alegrÃƒÂ­a y grandes momentos.</p><p><strong>Gracias por ser parte de Atlas.</strong></p>',
            ],
            [
                'nombre' => 'Avance de venta',
                'categoria' => 'avance_venta',
                'asunto' => 'Avance de venta',
                'mensaje_texto' => 'Tenemos una actualizaciÃƒÂ³n importante sobre tu avance de venta. Revisa el detalle y continÃƒÂºa con el seguimiento.',
                'html' => '<h2>Avance de venta</h2><p>Tenemos una actualizaciÃƒÂ³n importante sobre tu avance de venta.</p><ul><li>Revisa el detalle.</li><li>Da seguimiento oportuno.</li><li>ContinÃƒÂºa con el proceso indicado.</li></ul>',
            ],
            [
                'nombre' => 'NotificaciÃƒÂ³n especial',
                'categoria' => 'notificacion_especial',
                'asunto' => 'NotificaciÃƒÂ³n especial',
                'mensaje_texto' => 'Tenemos informaciÃƒÂ³n importante para ti. Revisa esta notificaciÃƒÂ³n y atiende las indicaciones correspondientes.',
                'html' => '<h2>NotificaciÃƒÂ³n especial</h2><p>Tenemos informaciÃƒÂ³n importante para ti. Revisa esta notificaciÃƒÂ³n y atiende las indicaciones correspondientes.</p>',
            ],
            [
                'nombre' => 'AtenciÃƒÂ³n al colaborador',
                'categoria' => 'atencion_colaborador',
                'asunto' => 'AtenciÃƒÂ³n al colaborador',
                'mensaje_texto' => 'Queremos acompaÃƒÂ±arte y darte seguimiento. Por favor revisa la informaciÃƒÂ³n y comunÃƒÂ­cate si necesitas apoyo.',
                'html' => '<h2>AtenciÃƒÂ³n al colaborador</h2><p>Queremos acompaÃƒÂ±arte y darte seguimiento.</p><p>Por favor revisa la informaciÃƒÂ³n y comunÃƒÂ­cate si necesitas apoyo.</p>',
            ],
        ];

        foreach ($semillas as $semilla) {
            $db->CRUD("
                INSERT INTO atlas_notificaciones_plantillas
                    (nombre, categoria, asunto, mensaje_texto, html, activo)
                SELECT :nombre, :categoria, :asunto, :mensaje_texto, :html, 1
                WHERE NOT EXISTS (
                    SELECT 1
                    FROM atlas_notificaciones_plantillas
                    WHERE categoria = :categoria2
                      AND nombre = :nombre2
                    LIMIT 1
                )
            ", [
                'nombre' => $semilla['nombre'],
                'categoria' => $semilla['categoria'],
                'asunto' => $semilla['asunto'],
                'mensaje_texto' => $semilla['mensaje_texto'],
                'html' => $semilla['html'],
                'categoria2' => $semilla['categoria'],
                'nombre2' => $semilla['nombre'],
            ]);
        }
    }

    public static function getPlantillasNotificaciones(): array
    {
        try {
            $db = new Database();
            self::asegurarTablaPlantillasNotificaciones($db);
            $datos = $db->queryAll("
                SELECT
                    id,
                    nombre,
                    categoria,
                    asunto,
                    mensaje_texto,
                    imagen_url,
                    html,
                    activo,
                    DATE_FORMAT(fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_notificaciones_plantillas
                ORDER BY activo DESC, categoria ASC, nombre ASC, id ASC
            ");

            return [
                'success' => true,
                'mensaje' => 'Plantillas obtenidas.',
                'datos' => $datos,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudieron obtener las plantillas.',
                'error' => $e->getMessage(),
                'datos' => [],
            ];
        }
    }

    public static function guardarPlantillaNotificacion(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarTablaPlantillasNotificaciones($db);

            $id = self::intVal($input['id'] ?? 0);
            $datos = [
                'nombre' => self::strVal($input['nombre'] ?? ''),
                'categoria' => self::strVal($input['categoria'] ?? ''),
                'asunto' => self::nullableStr($input['asunto'] ?? null),
                'mensaje_texto' => self::nullableStr($input['mensaje_texto'] ?? null),
                'imagen_url' => self::nullableStr($input['imagen_url'] ?? null),
                'html' => self::strVal($input['html'] ?? ''),
                'activo' => self::activoVal($input['activo'] ?? 1),
            ];

            if ($datos['nombre'] === '' || $datos['categoria'] === '' || $datos['html'] === '') {
                return ['success' => false, 'mensaje' => 'Captura nombre, categorÃƒÂ­a y contenido HTML.'];
            }

            if ($id > 0) {
                $datos['id'] = $id;
                $db->CRUD("
                    UPDATE atlas_notificaciones_plantillas
                    SET nombre = :nombre,
                        categoria = :categoria,
                        asunto = :asunto,
                        mensaje_texto = :mensaje_texto,
                        imagen_url = :imagen_url,
                        html = :html,
                        activo = :activo
                    WHERE id = :id
                ", $datos);
                return ['success' => true, 'mensaje' => 'Plantilla actualizada.', 'id' => $id];
            }

            $db->CRUD("
                INSERT INTO atlas_notificaciones_plantillas
                    (nombre, categoria, asunto, mensaje_texto, imagen_url, html, activo)
                VALUES
                    (:nombre, :categoria, :asunto, :mensaje_texto, :imagen_url, :html, :activo)
            ", $datos);

            return ['success' => true, 'mensaje' => 'Plantilla agregada.', 'id' => $db->lastInsertId()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar la plantilla.', 'error' => $e->getMessage()];
        }
    }

    public static function getUsuariosNotificacionesDisponibles(): array
    {
        try {
            $db = new Database();
            $datos = $db->queryAll("
                SELECT
                    t.id,
                    t.app,
                    t.user_id,
                    t.external_id,
                    CONCAT(LEFT(t.expo_push_token, 18), '...') AS token_corto,
                    t.platform,
                    t.device_name,
                    t.app_version,
                    t.is_active,
                    DATE_FORMAT(t.last_seen_at, '%d/%m/%Y %H:%i') AS last_seen_at_fmt,
                    DATE_FORMAT(t.created_at, '%d/%m/%Y %H:%i') AS created_at_fmt,
                    DATE_FORMAT(t.updated_at, '%d/%m/%Y %H:%i') AS updated_at_fmt,
                    p.id AS persona_match_id,
                    p.numero_empleado,
                    p.correo,
                    TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(p.nombres), ''),
                        NULLIF(TRIM(p.segundo_nombre), ''),
                        NULLIF(TRIM(p.apellidop), ''),
                        NULLIF(TRIM(p.apellidom), '')
                    )) AS nombre
                FROM atlas_push_tokens t
                LEFT JOIN persona p
                    ON (t.user_id REGEXP '^[0-9]+$' AND p.id = CAST(t.user_id AS UNSIGNED))
                    OR (TRIM(COALESCE(t.external_id, '')) <> '' AND p.numero_empleado = t.external_id)
                WHERE t.app = 'atlas'
                  AND TRIM(COALESCE(t.expo_push_token, '')) <> ''
                ORDER BY t.last_seen_at DESC, t.updated_at DESC, t.id DESC
            ");

            $totales = [
                'total' => count($datos),
                'android' => 0,
                'ios' => 0,
                'sin_plataforma' => 0,
            ];
            foreach ($datos as $row) {
                $platform = strtolower(trim((string)($row['platform'] ?? '')));
                if ($platform === 'android') {
                    $totales['android']++;
                } elseif ($platform === 'ios') {
                    $totales['ios']++;
                } else {
                    $totales['sin_plataforma']++;
                }
            }

            return [
                'success' => true,
                'mensaje' => 'Usuarios disponibles obtenidos desde la base local.',
                'datos' => $datos,
                'totales' => $totales,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudieron obtener los usuarios disponibles.',
                'error' => $e->getMessage(),
                'datos' => [],
            ];
        }
    }

    public static function getHistorialNotificacionesApp(): array
    {
        try {
            $db = new Database();
            $datos = $db->queryAll("
                SELECT
                    n.id,
                    n.type,
                    n.notification_type,
                    n.titulo,
                    n.mensaje,
                    DATE_FORMAT(n.created_at, '%d/%m/%Y %H:%i') AS created_at_fmt,
                    COUNT(DISTINCT un.id) AS total_destinatarios,
                    SUM(CASE WHEN un.is_read = 1 THEN 1 ELSE 0 END) AS total_leidas,
                    SUM(CASE WHEN un.is_active = 1 THEN 1 ELSE 0 END) AS total_activas,
                    DATE_FORMAT(MAX(COALESCE(un.sent_at, un.created_at, n.created_at)), '%d/%m/%Y %H:%i') AS ultima_actividad_fmt,
                    MAX(un.user_id) AS user_id_ref,
                    MAX(un.external_id) AS external_id_ref,
                    MAX(p.correo) AS correo_ref,
                    MAX(TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(p.nombres), ''),
                        NULLIF(TRIM(p.segundo_nombre), ''),
                        NULLIF(TRIM(p.apellidop), ''),
                        NULLIF(TRIM(p.apellidom), '')
                    ))) AS nombre_ref
                FROM atlas_notifications n
                LEFT JOIN atlas_user_notifications un ON un.notification_id = n.id
                LEFT JOIN persona p
                    ON (un.user_id REGEXP '^[0-9]+$' AND p.id = CAST(un.user_id AS UNSIGNED))
                    OR (TRIM(COALESCE(un.external_id, '')) <> '' AND p.numero_empleado = un.external_id)
                GROUP BY n.id, n.type, n.notification_type, n.titulo, n.mensaje, n.created_at
                ORDER BY n.created_at DESC, n.id DESC
                LIMIT 250
            ");

            foreach ($datos as &$row) {
                $total = (int)($row['total_destinatarios'] ?? 0);
                $nombre = trim((string)($row['nombre_ref'] ?? ''));
                $externalId = trim((string)($row['external_id_ref'] ?? ''));
                $userId = trim((string)($row['user_id_ref'] ?? ''));
                if ($total <= 1) {
                    $row['alcance'] = 'usuario';
                    $row['alcance_nombre'] = $nombre !== ''
                        ? $nombre
                        : 'Usuario ' . ($externalId !== '' ? $externalId : ($userId !== '' ? $userId : 'sin identificar'));
                } else {
                    $row['alcance'] = 'campania';
                    $row['alcance_nombre'] = 'CampaÃƒÂ±a a ' . $total . ' usuarios';
                }
            }
            unset($row);

            return [
                'success' => true,
                'mensaje' => 'Historial de notificaciones obtenido.',
                'datos' => $datos,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudo obtener el historial de notificaciones.',
                'error' => $e->getMessage(),
                'datos' => [],
            ];
        }
    }

    public static function getSucursales(): array
    {
        try {
            $db = new Database();
            self::asegurarColumnasPasoSucursal($db);
            self::asegurarResponsablesPersonaAtlas($db);
            $configuracionCalidad = self::getConfiguracionCalidadSucursales($db);
            $datos = $db->queryAll(
                "
                SELECT
                    s.id,
                    s.fk_sucursal,
                    s.distribuidor_id,
                    d.nombre AS distribuidor_nombre,
                    s.sucursal,
                    COALESCE(NULLIF(TRIM(s.direccion_sucursal), ''), dir.direccion, '') AS direccion,
                    s.coordenadas,
                    s.latitud,
                    s.longitud,
                    s.estado,
                    s.municipio,
                    s.localidad,
                    s.colonia,
                    s.codigo_postal,
                    s.divisional_id,
                    s.divisional_persona_id,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', pdvl.nombres, pdvl.segundo_nombre, pdvl.apellidop, pdvl.apellidom)), ''), dvl.nombre) AS divisional_nombre,
                    s.division_id,
                    divs.nombre AS division_nombre,
                    s.regional_id,
                    s.regional_persona_id,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', preg.nombres, preg.segundo_nombre, preg.apellidop, preg.apellidom)), ''), reg.nombre) AS regional_nombre,
                    s.supervisor_id,
                    s.supervisor_persona_id,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', psup.nombres, psup.segundo_nombre, psup.apellidop, psup.apellidom)), ''), sup.nombre) AS supervisor_nombre,
                    s.asesor_id,
                    s.asesor_persona_id,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', pase.nombres, pase.segundo_nombre, pase.apellidop, pase.apellidom)), ''), ase.nombre) AS asesor_nombre,
                    s.clasificacion_id,
                    c.nombre AS clasificacion_nombre,
                    c.icon_font AS clasificacion_icon_font,
                    c.color_hex AS clasificacion_color_hex,
                    s.paso_datos_completos,
                    s.paso_datos_completos_at,
                    DATE_FORMAT(s.paso_datos_completos_at, '%d/%m/%Y %H:%i') AS paso_datos_completos_at_fmt,
                    s.paso_asignacion_completa,
                    s.paso_asignacion_completa_at,
                    DATE_FORMAT(s.paso_asignacion_completa_at, '%d/%m/%Y %H:%i') AS paso_asignacion_completa_at_fmt,
                    s.calle,
                    s.numero_exterior,
                    s.numero_interior,
                    s.activo,
                    s.fecha_alta,
                    s.fecha_actualizacion,
                    tel.numero_telefono,
                    tel.nombre_contacto
                FROM atlas_catalogo_sucursales s
                INNER JOIN atlas_catalogo_clasificaciones c
                        ON c.id = s.clasificacion_id
                       AND c.activo = 1
                INNER JOIN atlas_catalogo_distribuidores d
                        ON d.id = s.distribuidor_id
                       AND d.activo = 1
                LEFT JOIN atlas_catalogo_divisionales dvl
                       ON dvl.id = s.divisional_id
                      AND dvl.activo = 1
                LEFT JOIN persona pdvl
                       ON pdvl.id = s.divisional_persona_id
                LEFT JOIN atlas_catalogo_divisiones divs
                       ON divs.id = s.division_id
                      AND divs.activo = 1
                LEFT JOIN atlas_catalogo_regionales reg
                       ON reg.id = s.regional_id
                      AND reg.activo = 1
                LEFT JOIN persona preg
                       ON preg.id = s.regional_persona_id
                LEFT JOIN atlas_catalogo_supervisores sup
                       ON sup.id = s.supervisor_id
                      AND sup.activo = 1
                LEFT JOIN persona psup
                       ON psup.id = s.supervisor_persona_id
                LEFT JOIN atlas_catalogo_asesores ase
                       ON ase.id = s.asesor_id
                      AND ase.activo = 1
                LEFT JOIN persona pase
                       ON pase.id = s.asesor_persona_id
                LEFT JOIN atlas_asigna_direccion_sucursal dir
                       ON dir.fk_sucursal = s.fk_sucursal
                      AND dir.activo = 1
                      AND dir.es_principal = 1
                LEFT JOIN atlas_asigna_telefono_sucursal tel
                       ON tel.fk_sucursal = s.fk_sucursal
                      AND tel.activo = 1
                      AND tel.es_principal = 1
                ORDER BY s.activo DESC, s.sucursal ASC, s.id ASC
                "
            );

            $totales = [
                'total' => count($datos),
                'activas' => 0,
                'inactivas' => 0,
                'con_coordenadas' => 0,
                'pendientes_paso2' => 0,
            ];

            foreach ($datos as &$row) {
                $row['activo'] = (int)($row['activo'] ?? 0);
                $row['paso_datos_completos'] = (int)($row['paso_datos_completos'] ?? 0);
                $row['paso_asignacion_completa'] = (int)($row['paso_asignacion_completa'] ?? 0);
                $row['estado'] = $row['activo'] === 1 ? 'Activa' : 'Inactiva';
                if ($row['activo'] === 1) {
                    $totales['activas']++;
                } else {
                    $totales['inactivas']++;
                }
                if (trim((string)($row['latitud'] ?? '')) !== '' && trim((string)($row['longitud'] ?? '')) !== '') {
                    $totales['con_coordenadas']++;
                }
                if ($row['activo'] === 1 && $row['paso_datos_completos'] === 1 && $row['paso_asignacion_completa'] !== 1) {
                    $totales['pendientes_paso2']++;
                }
            }
            unset($row);

            return [
                'success' => true,
                'mensaje' => 'Sucursales obtenidas.',
                'datos' => $datos,
                'totales' => $totales,
                'configuracion_calidad' => $configuracionCalidad,
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudieron obtener las sucursales.',
                'error' => $e->getMessage(),
                'datos' => [],
                'totales' => [
                    'total' => 0,
                    'activas' => 0,
                    'inactivas' => 0,
                    'con_coordenadas' => 0,
                    'pendientes_paso2' => 0,
                ],
                'configuracion_calidad' => ['sin_telefono_es_error' => 0],
            ];
        }
    }

    public static function getCatalogos(): array
    {
        try {
            $db = new Database();
            self::asegurarDistribuidoresAtlas($db);
            self::asegurarDivisionalesPersonaAtlas($db);
            self::asegurarResponsablesPersonaAtlas($db);
            return [
                'success' => true,
                'mensaje' => 'CatÃƒÂ¡logos obtenidos.',
                'datos' => [
                    'divisiones' => $db->queryAll("
                        SELECT
                            divs.id,
                            divs.divisional_id,
                            COALESCE(dvl.tipo_asignacion, CASE WHEN dvl.persona_id IS NULL THEN 'vacante' ELSE 'persona' END) AS tipo_asignacion,
                            dvl.nombre_vacante,
                            dvl.vacante_personal_id,
                            dvl.persona_id AS divisional_persona_id,
                            dvl.nombre AS divisional_nombre,
                            au.numero_empleado AS divisional_numero_empleado,
                            au.puesto AS divisional_puesto,
                            p.estatus AS divisional_persona_estatus,
                            DATE_FORMAT(bp.fecha_baja, '%d/%m/%Y') AS divisional_fecha_baja_fmt,
                            divs.nombre,
                            divs.activo,
                            divs.fecha_alta,
                            divs.fecha_actualizacion
                        FROM atlas_catalogo_divisiones divs
                        LEFT JOIN atlas_catalogo_divisionales dvl
                               ON dvl.id = divs.divisional_id
                              AND dvl.activo = 1
                        LEFT JOIN atlas_acceso_usuarios au
                               ON au.persona_id = dvl.persona_id
                        LEFT JOIN persona p
                               ON p.id = dvl.persona_id
                        LEFT JOIN (
                            SELECT bp1.id_persona, bp1.fecha_baja
                            FROM baja_persona bp1
                            INNER JOIN (
                                SELECT id_persona, MAX(id) AS id_ultima_baja
                                FROM baja_persona
                                GROUP BY id_persona
                            ) ult ON ult.id_persona = bp1.id_persona AND ult.id_ultima_baja = bp1.id
                        ) bp ON bp.id_persona = dvl.persona_id
                        ORDER BY divs.activo DESC, dvl.nombre ASC, divs.nombre ASC, divs.id ASC
                    "),
                    'divisionales' => $db->queryAll("
                        SELECT
                            dvl.id,
                            COALESCE(dvl.tipo_asignacion, CASE WHEN dvl.persona_id IS NULL THEN 'vacante' ELSE 'persona' END) AS tipo_asignacion,
                            dvl.nombre_vacante,
                            dvl.vacante_personal_id,
                            dvl.persona_id,
                            dvl.nombre,
                            au.numero_empleado,
                            au.puesto,
                            au.area,
                            au.direccion,
                    p.estatus AS persona_estatus,
                    aj.id_jefe AS jefe_persona_id,
                    aj.id_vacante_jefe AS jefe_vacante_id,
                    dvl.activo,
                            dvl.fecha_alta,
                            dvl.fecha_actualizacion
                        FROM atlas_catalogo_divisionales dvl
                        INNER JOIN atlas_acceso_usuarios au
                                ON au.persona_id = dvl.persona_id
                               AND au.activo = 1
                               AND au.excluido_operativo = 0
                        INNER JOIN persona p
                                ON p.id = dvl.persona_id
                               AND p.estatus <> 'Baja'
                        LEFT JOIN (
                            SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                            FROM asigna_jefe a
                            INNER JOIN (
                                SELECT id_persona, MAX(id) AS id_ultimo
                                FROM asigna_jefe
                                GROUP BY id_persona
                            ) ult ON ult.id_persona = a.id_persona AND ult.id_ultimo = a.id
                        ) aj ON aj.id_persona = dvl.persona_id
                        WHERE dvl.activo = 1
                        ORDER BY nombre ASC, id ASC
                    "),
                    'regionales' => $db->queryAll("
                        SELECT
                            reg.id,
                            reg.division_id,
                            reg.persona_id,
                            COALESCE(reg.tipo_asignacion, 'persona') AS tipo_asignacion,
                            reg.nombre_vacante,
                            reg.vacante_personal_id,
                            reg.nombre,
                            p.estatus AS persona_estatus,
                            aj.id_jefe AS jefe_persona_id,
                            aj.id_vacante_jefe AS jefe_vacante_id,
                            reg.activo,
                            reg.fecha_alta,
                            reg.fecha_actualizacion
                        FROM atlas_catalogo_regionales reg
                        INNER JOIN atlas_catalogo_divisiones divs
                                ON divs.id = reg.division_id
                               AND divs.activo = 1
                        LEFT JOIN persona p
                               ON p.id = reg.persona_id
                        LEFT JOIN (
                            SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                            FROM asigna_jefe a
                            INNER JOIN (
                                SELECT id_persona, MAX(id) AS id_ultimo
                                FROM asigna_jefe
                                GROUP BY id_persona
                            ) ult ON ult.id_persona = a.id_persona AND ult.id_ultimo = a.id
                        ) aj ON aj.id_persona = reg.persona_id
                        WHERE reg.activo = 1
                        ORDER BY reg.nombre ASC, reg.id ASC
                    "),
                    'supervisores' => $db->queryAll("
                        SELECT
                            sup.id,
                            sup.regional_id,
                            sup.persona_id,
                            COALESCE(sup.tipo_asignacion, 'persona') AS tipo_asignacion,
                            sup.nombre_vacante,
                            sup.vacante_personal_id,
                            sup.nombre,
                            p.estatus AS persona_estatus,
                            aj.id_jefe AS jefe_persona_id,
                            aj.id_vacante_jefe AS jefe_vacante_id,
                            sup.activo,
                            sup.fecha_alta,
                            sup.fecha_actualizacion
                        FROM atlas_catalogo_supervisores sup
                        INNER JOIN atlas_catalogo_regionales reg
                                ON reg.id = sup.regional_id
                               AND reg.activo = 1
                        LEFT JOIN persona p
                               ON p.id = sup.persona_id
                        LEFT JOIN (
                            SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                            FROM asigna_jefe a
                            INNER JOIN (
                                SELECT id_persona, MAX(id) AS id_ultimo
                                FROM asigna_jefe
                                GROUP BY id_persona
                            ) ult ON ult.id_persona = a.id_persona AND ult.id_ultimo = a.id
                        ) aj ON aj.id_persona = sup.persona_id
                        WHERE sup.activo = 1
                        ORDER BY sup.nombre ASC, sup.id ASC
                    "),
                    'asesores' => $db->queryAll("
                        SELECT
                            ase.id,
                            ase.supervisor_id,
                            ase.persona_id,
                            COALESCE(ase.tipo_asignacion, 'persona') AS tipo_asignacion,
                            ase.nombre_vacante,
                            ase.vacante_personal_id,
                            ase.nombre,
                            p.estatus AS persona_estatus,
                            aj.id_jefe AS jefe_persona_id,
                            aj.id_vacante_jefe AS jefe_vacante_id,
                            ase.activo,
                            ase.fecha_alta,
                            ase.fecha_actualizacion
                        FROM atlas_catalogo_asesores ase
                        INNER JOIN atlas_catalogo_supervisores sup
                                ON sup.id = ase.supervisor_id
                               AND sup.activo = 1
                        LEFT JOIN persona p
                               ON p.id = ase.persona_id
                        LEFT JOIN (
                            SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                            FROM asigna_jefe a
                            INNER JOIN (
                                SELECT id_persona, MAX(id) AS id_ultimo
                                FROM asigna_jefe
                                GROUP BY id_persona
                            ) ult ON ult.id_persona = a.id_persona AND ult.id_ultimo = a.id
                        ) aj ON aj.id_persona = ase.persona_id
                        WHERE ase.activo = 1
                        ORDER BY ase.nombre ASC, ase.id ASC
                    "),
                    'personas_comercial' => self::getPersonasComercialAtlas($db),
                    'distribuidores' => $db->queryAll("
                        SELECT
                            id,
                            nombre,
                            nombre_comercial,
                            razon_social,
                            rfc,
                            tipo_persona,
                            tipo_distribuidor,
                            estatus,
                            fecha_baja,
                            nombre_contacto,
                            telefono_contacto,
                            telefono_secundario,
                            email_contacto,
                            regimen_fiscal,
                            tipo_motos,
                            canal_venta,
                            horario_atencion,
                            dias_operacion,
                            requiere_cita,
                            tiempo_promedio_entrega,
                            bloqueo_vigencia,
                            DATE_FORMAT(bloqueo_fin_at, '%Y-%m-%dT%H:%i') AS bloqueo_fin_at,
                            estado,
                            municipio,
                            presencia_fisica,
                            constancia_fiscal_url,
                            constancia_fiscal_nombre,
                            DATE_FORMAT(constancia_fiscal_at, '%d/%m/%Y %H:%i') AS constancia_fiscal_at_fmt,
                            icon_font,
                            activo,
                            fecha_alta,
                            fecha_actualizacion,
                            observaciones,
                            motivo_bloqueo,
                            DATE_FORMAT(fecha_validacion, '%d/%m/%Y %H:%i') AS fecha_validacion_fmt,
                            validado_por
                        FROM atlas_catalogo_distribuidores
                        ORDER BY activo DESC, nombre ASC, id ASC
                    "),
                    'estados_presencia' => $db->queryAll("
                        SELECT id, nombre
                        FROM divisiones_administrativas
                        WHERE nivel = 1
                          AND COALESCE(activo, 1) = 1
                        ORDER BY nombre ASC, id ASC
                    "),
                    'presencias_distribuidores' => $db->queryAll("
                        SELECT
                            id,
                            distribuidor_id,
                            estado_id,
                            municipio_id,
                            estado,
                            municipio,
                            activo
                        FROM atlas_asigna_presencia_distribuidor
                        WHERE activo = 1
                        ORDER BY estado ASC, municipio ASC, id ASC
                    "),
                    'distribuidor_tipo_motos' => $db->queryAll("
                        SELECT id, nombre, activo
                        FROM atlas_catalogo_distribuidor_tipo_moto
                        WHERE activo = 1
                        ORDER BY nombre ASC, id ASC
                    "),
                    'distribuidor_canales_venta' => $db->queryAll("
                        SELECT id, nombre, activo
                        FROM atlas_catalogo_distribuidor_canal_venta
                        WHERE activo = 1
                        ORDER BY nombre ASC, id ASC
                    "),
                    'clasificaciones' => $db->queryAll("
                        SELECT id, nombre, icon_font, color_hex, orden, activo, fecha_alta, fecha_actualizacion
                        FROM atlas_catalogo_clasificaciones
                        ORDER BY COALESCE(orden, 999999), nombre ASC, id ASC
                    "),
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudieron obtener los catÃƒÂ¡logos.',
                'error' => $e->getMessage(),
                'datos' => [
                    'divisiones' => [],
                    'divisionales' => [],
                    'regionales' => [],
                    'supervisores' => [],
                    'asesores' => [],
                    'personas_comercial' => [],
                    'distribuidores' => [],
                    'estados_presencia' => [],
                    'presencias_distribuidores' => [],
                    'clasificaciones' => [],
                ],
            ];
        }
    }

    public static function getMunicipiosPresencia(int $estadoId): array
    {
        try {
            if ($estadoId <= 0) {
                return ['success' => false, 'mensaje' => 'Selecciona un estado.', 'datos' => []];
            }

            $db = new Database();
            $estado = $db->queryOne("
                SELECT id
                FROM divisiones_administrativas
                WHERE id = :id
                  AND nivel = 1
                  AND COALESCE(activo, 1) = 1
                LIMIT 1
            ", ['id' => $estadoId]);
            if (!$estado) {
                return ['success' => false, 'mensaje' => 'El estado seleccionado no esta activo.', 'datos' => []];
            }

            $datos = $db->queryAll("
                SELECT id, nombre
                FROM divisiones_administrativas
                WHERE id_padre = :estado_id
                  AND nivel = 2
                  AND COALESCE(activo, 1) = 1
                ORDER BY nombre ASC, id ASC
            ", ['estado_id' => $estadoId]);

            return ['success' => true, 'mensaje' => 'Municipios obtenidos.', 'datos' => $datos];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudieron cargar los municipios.',
                'error' => $e->getMessage(),
                'datos' => [],
            ];
        }
    }

    public static function guardarSucursal(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarColumnasPasoSucursal($db);

            $usuarioId = self::intVal($input['_usuario_id'] ?? $_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id'] ?? 0);
            $permisos = self::permisosSucursalAtlas($usuarioId);
            $id = self::intVal($input['id'] ?? 0);
            $pasoAlta = self::strVal($input['paso_alta'] ?? 'paso1');
            $esPaso2 = $pasoAlta === 'paso2';

            if ($id <= 0 && empty($permisos['paso1'])) {
                return ['success' => false, 'mensaje' => 'No tienes permiso para agregar sucursales.'];
            }
            if ($id > 0 && empty($permisos['paso1']) && empty($permisos['paso2'])) {
                return ['success' => false, 'mensaje' => 'No tienes permiso para modificar sucursales.'];
            }
            if ($esPaso2 && empty($permisos['paso2'])) {
                return ['success' => false, 'mensaje' => 'No tienes permiso para completar el Paso 2 de asignacion.'];
            }
            if (!$esPaso2 && empty($permisos['paso1'])) {
                return ['success' => false, 'mensaje' => 'No tienes permiso para modificar los datos base de la sucursal.'];
            }

            $sucursal = self::strVal($input['sucursal'] ?? '');
            $distribuidorId = self::intVal($input['distribuidor_id'] ?? 0);
            $fkSucursal = 0;

            if ($id > 0) {
                $actual = $db->queryOne(
                    "SELECT * FROM atlas_catalogo_sucursales WHERE id = :id LIMIT 1",
                    ['id' => $id]
                );
                if (!$actual) {
                    return ['success' => false, 'mensaje' => 'No se encontro la sucursal a actualizar.'];
                }
                $fkSucursal = self::intVal($actual['fk_sucursal'] ?? 0);
                if (empty($permisos['paso1']) && !empty($permisos['paso2'])) {
                    foreach ([
                        'sucursal', 'distribuidor_id', 'clasificacion_id', 'activo',
                        'direccion_sucursal', 'calle', 'numero_exterior', 'numero_interior',
                        'estado', 'municipio', 'localidad', 'colonia', 'codigo_postal',
                        'latitud', 'longitud', 'coordenadas'
                    ] as $campoBase) {
                        $input[$campoBase] = $actual[$campoBase] ?? ($input[$campoBase] ?? null);
                    }
                    $sucursal = self::strVal($input['sucursal'] ?? '');
                    $distribuidorId = self::intVal($input['distribuidor_id'] ?? 0);
                }
            } else {
                $siguiente = $db->queryOne(
                    "SELECT COALESCE(MAX(fk_sucursal), 0) + 1 AS fk_sucursal FROM atlas_catalogo_sucursales"
                );
                $fkSucursal = self::intVal($siguiente['fk_sucursal'] ?? 0);
            }

            if ($fkSucursal <= 0 || $sucursal === '' || $distribuidorId <= 0) {
                return ['success' => false, 'mensaje' => 'Captura sucursal y distribuidor.'];
            }

            $obligatorios = [
                'clasificacion_id' => 'clasificacion',
                'direccion_sucursal' => 'direccion',
                'estado' => 'estado',
                'municipio' => 'municipio',
                'localidad' => 'localidad',
                'colonia' => 'colonia',
                'codigo_postal' => 'codigo postal',
                'latitud' => 'latitud',
                'longitud' => 'longitud',
            ];
            $camposAsignacion = [
                'divisional_id' => 'divisional',
                'division_id' => 'division',
                'regional_id' => 'regional',
                'supervisor_id' => 'supervisor',
                'asesor_id' => 'asesor',
            ];
            $asignacionIniciada = false;
            foreach (array_keys($camposAsignacion) as $campoAsignacion) {
                if (self::strVal($input[$campoAsignacion] ?? '') !== '') {
                    $asignacionIniciada = true;
                    break;
                }
            }
            if ($esPaso2 || $asignacionIniciada) {
                $obligatorios = array_merge($obligatorios, $camposAsignacion);
            }

            $faltantes = [];
            foreach ($obligatorios as $campo => $label) {
                $valor = $input[$campo] ?? null;
                if (is_numeric($valor)) {
                    if ((float)$valor == 0.0 && !in_array($campo, ['latitud', 'longitud'], true)) {
                        $faltantes[] = $label;
                    }
                    continue;
                }
                if (self::strVal($valor) === '') {
                    $faltantes[] = $label;
                }
            }
            if ($faltantes) {
                return ['success' => false, 'mensaje' => 'Completa todos los campos obligatorios: ' . implode(', ', $faltantes) . '.'];
            }

            $datos = [
                'fk_sucursal' => $fkSucursal,
                'distribuidor_id' => $distribuidorId,
                'sucursal' => $sucursal,
                'direccion_sucursal' => self::nullableStr($input['direccion_sucursal'] ?? null),
                'coordenadas' => self::nullableStr($input['coordenadas'] ?? null),
                'latitud' => self::nullableDecimal($input['latitud'] ?? null),
                'longitud' => self::nullableDecimal($input['longitud'] ?? null),
                'estado' => self::nullableStr($input['estado'] ?? null),
                'municipio' => self::nullableStr($input['municipio'] ?? null),
                'localidad' => self::nullableStr($input['localidad'] ?? null),
                'colonia' => self::nullableStr($input['colonia'] ?? null),
                'codigo_postal' => self::nullableStr($input['codigo_postal'] ?? null),
                'calle' => self::nullableStr($input['calle'] ?? null),
                'numero_exterior' => self::nullableStr($input['numero_exterior'] ?? null),
                'numero_interior' => self::nullableStr($input['numero_interior'] ?? null),
                'divisional_id' => self::nullableInt($input['divisional_id'] ?? null),
                'division_id' => self::nullableInt($input['division_id'] ?? null),
                'regional_id' => self::nullableInt($input['regional_id'] ?? null),
                'supervisor_id' => self::nullableInt($input['supervisor_id'] ?? null),
                'asesor_id' => self::nullableInt($input['asesor_id'] ?? null),
                'clasificacion_id' => self::nullableInt($input['clasificacion_id'] ?? null),
                'activo' => self::activoVal($input['activo'] ?? 1),
            ];
            if ($datos['coordenadas'] === null && $datos['latitud'] !== null && $datos['longitud'] !== null) {
                $datos['coordenadas'] = $datos['latitud'] . ',' . $datos['longitud'];
            }
            $personasAsignacion = self::resolverPersonasAsignacionSucursal($db, $datos, $input);
            $datos = array_merge($datos, $personasAsignacion);

            if ($id > 0) {
                $datos['id'] = $id;
                $db->CRUD("
                    UPDATE atlas_catalogo_sucursales
                    SET fk_sucursal = :fk_sucursal,
                        distribuidor_id = :distribuidor_id,
                        sucursal = :sucursal,
                        direccion_sucursal = :direccion_sucursal,
                        coordenadas = :coordenadas,
                        latitud = :latitud,
                        longitud = :longitud,
                        estado = :estado,
                        municipio = :municipio,
                        localidad = :localidad,
                        colonia = :colonia,
                        codigo_postal = :codigo_postal,
                        calle = :calle,
                        numero_exterior = :numero_exterior,
                        numero_interior = :numero_interior,
                        divisional_id = :divisional_id,
                        divisional_persona_id = :divisional_persona_id,
                        division_id = :division_id,
                        regional_id = :regional_id,
                        regional_persona_id = :regional_persona_id,
                        supervisor_id = :supervisor_id,
                        supervisor_persona_id = :supervisor_persona_id,
                        asesor_id = :asesor_id,
                        asesor_persona_id = :asesor_persona_id,
                        clasificacion_id = :clasificacion_id,
                        activo = :activo
                    WHERE id = :id
                ", $datos);
                return ['success' => true, 'mensaje' => 'Sucursal actualizada.'];
            }

            $db->CRUD("
                INSERT INTO atlas_catalogo_sucursales
                    (fk_sucursal, distribuidor_id, sucursal, direccion_sucursal, coordenadas,
                     latitud, longitud, estado, municipio, localidad, colonia, codigo_postal, calle, numero_exterior, numero_interior,
                     divisional_id, divisional_persona_id, division_id, regional_id, regional_persona_id,
                     supervisor_id, supervisor_persona_id, asesor_id, asesor_persona_id, clasificacion_id, activo)
                VALUES
                    (:fk_sucursal, :distribuidor_id, :sucursal, :direccion_sucursal, :coordenadas,
                     :latitud, :longitud, :estado, :municipio, :localidad, :colonia, :codigo_postal, :calle, :numero_exterior, :numero_interior,
                     :divisional_id, :divisional_persona_id, :division_id, :regional_id, :regional_persona_id,
                     :supervisor_id, :supervisor_persona_id, :asesor_id, :asesor_persona_id, :clasificacion_id, :activo)
            ", $datos);
            return ['success' => true, 'mensaje' => 'Sucursal agregada.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar la sucursal.', 'error' => $e->getMessage()];
        }
    }
    public static function guardarDivision(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarDivisionalesPersonaAtlas($db);
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo preparar el catalogo de divisiones.', 'error' => $e->getMessage()];
        }

        return self::guardarSimple('atlas_catalogo_divisiones', [
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'icon_font' => self::strVal($input['icon_font'] ?? 'fa-solid fa-diagram-project'),
            'color_hex' => self::strVal($input['color_hex'] ?? '#2563EB'),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ], self::intVal($input['id'] ?? 0), ['nombre'], 'divisiÃ³n');
    }

    private static function resolverPersonasAsignacionSucursal(Database $db, array &$datos, array $input = []): array
    {
        $tokens = [
            'divisional' => self::parseTokenPersonaAsignacion($input['divisional_id'] ?? null),
            'regional' => self::parseTokenPersonaAsignacion($input['regional_id'] ?? null),
            'supervisor' => self::parseTokenPersonaAsignacion($input['supervisor_id'] ?? null),
            'asesor' => self::parseTokenPersonaAsignacion($input['asesor_id'] ?? null),
        ];
        foreach ($tokens as $campo => $personaId) {
            if ($personaId !== null) {
                $datos[$campo . '_id'] = null;
            }
        }
        return [
            'divisional_persona_id' => $tokens['divisional'] ?? self::personaIdDesdeCatalogoAsignacion($db, 'atlas_catalogo_divisionales', self::nullableInt($datos['divisional_id'] ?? null)),
            'regional_persona_id' => $tokens['regional'] ?? self::personaIdDesdeCatalogoAsignacion($db, 'atlas_catalogo_regionales', self::nullableInt($datos['regional_id'] ?? null)),
            'supervisor_persona_id' => $tokens['supervisor'] ?? self::personaIdDesdeCatalogoAsignacion($db, 'atlas_catalogo_supervisores', self::nullableInt($datos['supervisor_id'] ?? null)),
            'asesor_persona_id' => $tokens['asesor'] ?? self::personaIdDesdeCatalogoAsignacion($db, 'atlas_catalogo_asesores', self::nullableInt($datos['asesor_id'] ?? null)),
        ];
    }

    private static function parseTokenPersonaAsignacion($valor): ?int
    {
        $valor = self::strVal($valor);
        if (!preg_match('/^persona:(\d+)$/', $valor, $m)) {
            return null;
        }
        return self::nullableInt($m[1] ?? null);
    }

    private static function personaIdDesdeCatalogoAsignacion(Database $db, string $tabla, ?int $id): ?int
    {
        if ($id === null) {
            return null;
        }
        $row = $db->queryOne(
            "SELECT persona_id, tipo_asignacion FROM `$tabla` WHERE id = :id LIMIT 1",
            ['id' => $id]
        );
        if (!$row || ($row['tipo_asignacion'] ?? 'persona') !== 'persona') {
            return null;
        }
        return self::nullableInt($row['persona_id'] ?? null);
    }

    private static function getPersonasComercialAtlas(Database $db): array
    {
        return $db->queryAll("
            SELECT
                persona_id,
                numero_empleado,
                nombre,
                departamento,
                puesto,
                rol_atlas,
                jefe_persona_id,
                jefe_vacante_id
            FROM (
                SELECT
                    p.id AS persona_id,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ', NULLIF(p.nombres, ''), NULLIF(p.segundo_nombre, ''), NULLIF(p.apellidop, ''), NULLIF(p.apellidom, ''))) AS nombre,
                    dep.nombre AS departamento,
                    pu.nombre AS puesto,
                    aj.id_jefe AS jefe_persona_id,
                    aj.id_vacante_jefe AS jefe_vacante_id,
                    CASE
                        WHEN UPPER(COALESCE(dep.nombre, '')) = 'DIVISIONAL' AND UPPER(COALESCE(pu.nombre, '')) = 'GERENTE' THEN 'divisional'
                        WHEN UPPER(COALESCE(dep.nombre, '')) = 'REGIONAL' AND UPPER(COALESCE(pu.nombre, '')) = 'GERENTE' THEN 'regional'
                        WHEN UPPER(COALESCE(dep.nombre, '')) = 'VENTAS' AND UPPER(COALESCE(pu.nombre, '')) = 'SUPERVISOR' THEN 'supervisor'
                        WHEN UPPER(COALESCE(dep.nombre, '')) = 'VENTAS' AND UPPER(COALESCE(pu.nombre, '')) = 'ASESOR' THEN 'asesor'
                        ELSE NULL
                    END AS rol_atlas
                FROM persona p
                LEFT JOIN persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND ap.activo = 1
                LEFT JOIN puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                LEFT JOIN departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                LEFT JOIN (
                    SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                    FROM asigna_jefe a
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS id_ultimo
                        FROM asigna_jefe
                        GROUP BY id_persona
                    ) ult ON ult.id_persona = a.id_persona AND ult.id_ultimo = a.id
                ) aj ON aj.id_persona = p.id
                WHERE p.estatus = 'Activo'
            ) base
            WHERE rol_atlas IS NOT NULL
            ORDER BY rol_atlas ASC, nombre ASC, persona_id ASC
        ");
    }

    public static function guardarAsignacionDivision(array $input): array
    {
        $divisionId = self::nullableInt($input['division_id'] ?? null);
        if ($divisionId === null) {
            return ['success' => false, 'mensaje' => 'Selecciona una divisiÃ³n.'];
        }

        $tipo = strtolower(self::strVal($input['tipo_asignacion'] ?? 'persona'));
        if (!in_array($tipo, ['persona', 'vacante'], true)) {
            $tipo = 'persona';
        }

        try {
            $db = new Database();
            self::asegurarDivisionalesPersonaAtlas($db);

            $division = $db->queryOne("
                SELECT divs.id, divs.nombre, divs.divisional_id,
                       dvl.tipo_asignacion AS tipo_anterior,
                       dvl.persona_id AS persona_anterior_id,
                       dvl.nombre AS nombre_anterior,
                       dvl.nombre_vacante AS nombre_vacante_anterior
                FROM atlas_catalogo_divisiones divs
                LEFT JOIN atlas_catalogo_divisionales dvl ON dvl.id = divs.divisional_id
                WHERE divs.id = :id
                  AND divs.activo = 1
                LIMIT 1
            ", ['id' => $divisionId]);
            if (!$division) {
                return ['success' => false, 'mensaje' => 'La divisiÃ³n seleccionada no existe o estÃ¡ inactiva.'];
            }

            if ($tipo === 'persona') {
                $divisionalId = self::nullableInt($input['divisional_id'] ?? null);
                if ($divisionalId === null) {
                    return ['success' => false, 'mensaje' => 'Selecciona un colaborador para la divisiÃ³n.'];
                }
                $nuevo = $db->queryOne("
                    SELECT dvl.id, dvl.tipo_asignacion, dvl.persona_id, dvl.nombre
                    FROM atlas_catalogo_divisionales dvl
                    INNER JOIN atlas_acceso_usuarios au
                            ON au.persona_id = dvl.persona_id
                           AND au.activo = 1
                           AND au.excluido_operativo = 0
                    INNER JOIN persona p
                            ON p.id = dvl.persona_id
                           AND p.estatus <> 'Baja'
                    WHERE dvl.id = :id
                      AND dvl.activo = 1
                      AND dvl.tipo_asignacion = 'persona'
                    LIMIT 1
                ", ['id' => $divisionalId]);
                if (!$nuevo) {
                    return ['success' => false, 'mensaje' => 'El colaborador seleccionado no estÃ¡ disponible en Accesos Atlas o Capital Humano ya lo dio de baja.'];
                }
            } else {
                $nombreVacante = self::strVal($input['nombre_vacante'] ?? '');
                if ($nombreVacante === '') {
                    return ['success' => false, 'mensaje' => 'Captura el nombre de la vacante.'];
                }
                $nuevo = self::obtenerOCrearDivisionalVacante($db, $nombreVacante);
                $divisionalId = (int)$nuevo['id'];
            }

            $ocupada = $db->queryOne("
                SELECT id, nombre
                FROM atlas_catalogo_divisiones
                WHERE divisional_id = :divisional_id
                  AND activo = 1
                  AND id <> :division_id
                LIMIT 1
            ", ['divisional_id' => $divisionalId, 'division_id' => $divisionId]);
            if ($ocupada) {
                return ['success' => false, 'mensaje' => 'Ese responsable ya estÃ¡ asignado a la divisiÃ³n ' . ($ocupada['nombre'] ?? '') . '.'];
            }

            $anteriorId = self::nullableInt($division['divisional_id'] ?? null);
            $cambio = $anteriorId !== $divisionalId;

            $db->beginTransaction();
            $db->CRUD(
                "UPDATE atlas_catalogo_divisiones SET divisional_id = :divisional_id WHERE id = :id",
                ['divisional_id' => $divisionalId, 'id' => $divisionId]
            );

            if ($cambio) {
                self::registrarBitacoraAsignacionDivision($db, $division, $nuevo, $divisionId, self::nullableInt($input['_usuario_id'] ?? null));
                if ($anteriorId !== null && $anteriorId !== $divisionalId && ($division['tipo_anterior'] ?? '') === 'vacante') {
                    $usoVacante = $db->queryOne(
                        "SELECT COUNT(*) AS total FROM atlas_catalogo_divisiones WHERE divisional_id = :id AND activo = 1",
                        ['id' => $anteriorId]
                    );
                    if ((int)($usoVacante['total'] ?? 0) === 0) {
                        $db->CRUD("UPDATE atlas_catalogo_divisionales SET activo = 0 WHERE id = :id AND tipo_asignacion = 'vacante'", ['id' => $anteriorId]);
                    }
                }
            }
            $db->commit();

            return ['success' => true, 'mensaje' => $cambio ? 'AsignaciÃ³n actualizada.' : 'AsignaciÃ³n sin cambios.', 'id' => $divisionId];
        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo guardar la asignaciÃ³n de divisiÃ³n.', 'error' => $e->getMessage()];
        }
    }

    public static function getActualizacionesDivisionales(): array
    {
        try {
            $db = new Database();
            self::asegurarDivisionalesPersonaAtlas($db);
            self::asegurarAccesosAtlas($db);

            $disponibles = $db->queryAll("
                SELECT
                    au.persona_id,
                    au.numero_empleado,
                    au.nombre,
                    au.puesto,
                    au.area,
                    au.direccion
                FROM atlas_acceso_usuarios au
                INNER JOIN persona p
                        ON p.id = au.persona_id
                       AND p.estatus <> 'Baja'
                LEFT JOIN atlas_catalogo_divisionales dvl
                       ON dvl.persona_id = au.persona_id
                      AND dvl.activo = 1
                      AND dvl.tipo_asignacion = 'persona'
                WHERE au.activo = 1
                  AND au.excluido_operativo = 0
                  AND dvl.id IS NULL
                ORDER BY au.nombre ASC
            ");

            $sinUso = $db->queryAll("
                SELECT
                    dvl.id,
                    dvl.nombre,
                    dvl.tipo_asignacion,
                    dvl.nombre_vacante,
                    dvl.persona_id,
                    au.numero_empleado,
                    au.puesto,
                    au.area,
                    au.direccion,
                    COUNT(divs.id) AS total_divisiones
                FROM atlas_catalogo_divisionales dvl
                LEFT JOIN atlas_acceso_usuarios au
                       ON au.persona_id = dvl.persona_id
                LEFT JOIN atlas_catalogo_divisiones divs
                       ON divs.divisional_id = dvl.id
                      AND divs.activo = 1
                WHERE dvl.activo = 1
                GROUP BY dvl.id, dvl.nombre, dvl.tipo_asignacion, dvl.nombre_vacante, dvl.persona_id, au.numero_empleado, au.puesto, au.area, au.direccion
                HAVING total_divisiones = 0
                ORDER BY dvl.tipo_asignacion ASC, dvl.nombre ASC
            ");

            return [
                'success' => true,
                'mensaje' => 'Actualizaciones obtenidas.',
                'datos' => [
                    'disponibles' => $disponibles,
                    'sin_uso' => $sinUso,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudieron obtener las actualizaciones de divisionales.', 'error' => $e->getMessage()];
        }
    }

    public static function crearDivisionalDesdePersona(array $input): array
    {
        $personaId = self::nullableInt($input['persona_id'] ?? null);
        if ($personaId === null) {
            return ['success' => false, 'mensaje' => 'Selecciona un colaborador.'];
        }

        try {
            $db = new Database();
            self::asegurarDivisionalesPersonaAtlas($db);
            self::asegurarAccesosAtlas($db);

            $usuario = $db->queryOne("
                SELECT au.persona_id, au.nombre, au.numero_empleado, au.puesto
                FROM atlas_acceso_usuarios au
                INNER JOIN persona p
                        ON p.id = au.persona_id
                       AND p.estatus <> 'Baja'
                WHERE au.persona_id = :persona_id
                  AND au.activo = 1
                  AND au.excluido_operativo = 0
                LIMIT 1
            ", ['persona_id' => $personaId]);
            if (!$usuario) {
                return ['success' => false, 'mensaje' => 'El colaborador no estÃ¡ disponible en Accesos Atlas o Capital Humano ya lo dio de baja.'];
            }

            $activo = $db->queryOne("
                SELECT id
                FROM atlas_catalogo_divisionales
                WHERE persona_id = :persona_id
                  AND activo = 1
                  AND tipo_asignacion = 'persona'
                LIMIT 1
            ", ['persona_id' => $personaId]);
            if ($activo) {
                return ['success' => true, 'mensaje' => 'El colaborador ya estaba como divisional activo.', 'id' => (int)$activo['id']];
            }

            $nombre = self::strVal($usuario['nombre'] ?? '');
            if ($nombre === '') {
                return ['success' => false, 'mensaje' => 'El colaborador no tiene nombre vÃ¡lido.'];
            }

            $conflicto = $db->queryOne("
                SELECT id, persona_id, activo
                FROM atlas_catalogo_divisionales
                WHERE LOWER(nombre) = LOWER(:nombre)
                  AND (persona_id IS NULL OR persona_id <> :persona_id)
                LIMIT 1
            ", ['nombre' => $nombre, 'persona_id' => $personaId]);
            if ($conflicto) {
                return ['success' => false, 'mensaje' => 'Ya existe otro divisional con ese nombre. Revisa el catÃ¡logo antes de agregarlo.'];
            }

            $inactivo = $db->queryOne("
                SELECT id
                FROM atlas_catalogo_divisionales
                WHERE persona_id = :persona_id
                  AND tipo_asignacion = 'persona'
                ORDER BY id DESC
                LIMIT 1
            ", ['persona_id' => $personaId]);
            if ($inactivo) {
                $db->CRUD("
                    UPDATE atlas_catalogo_divisionales
                    SET nombre = :nombre,
                        nombre_vacante = NULL,
                        activo = 1,
                        fecha_actualizacion = NOW()
                    WHERE id = :id
                ", ['nombre' => $nombre, 'id' => (int)$inactivo['id']]);
                return ['success' => true, 'mensaje' => 'Divisional reactivado.', 'id' => (int)$inactivo['id']];
            }

            $db->CRUD("
                INSERT INTO atlas_catalogo_divisionales (nombre, persona_id, tipo_asignacion, nombre_vacante, activo, fecha_alta, fecha_actualizacion)
                VALUES (:nombre, :persona_id, 'persona', NULL, 1, NOW(), NOW())
            ", ['nombre' => $nombre, 'persona_id' => $personaId]);

            return ['success' => true, 'mensaje' => 'Divisional agregado.', 'id' => $db->lastInsertId()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo agregar el divisional.', 'error' => $e->getMessage()];
        }
    }

    public static function desactivarDivisional(array $input): array
    {
        $id = self::nullableInt($input['id'] ?? null);
        if ($id === null) {
            return ['success' => false, 'mensaje' => 'Selecciona un divisional.'];
        }

        try {
            $db = new Database();
            self::asegurarDivisionalesPersonaAtlas($db);

            $divisional = $db->queryOne("
                SELECT id, nombre, activo
                FROM atlas_catalogo_divisionales
                WHERE id = :id
                LIMIT 1
            ", ['id' => $id]);
            if (!$divisional || (int)($divisional['activo'] ?? 0) !== 1) {
                return ['success' => false, 'mensaje' => 'El divisional no existe o ya estÃ¡ inactivo.'];
            }

            $uso = $db->queryOne("
                SELECT COUNT(*) AS total
                FROM atlas_catalogo_divisiones
                WHERE divisional_id = :id
                  AND activo = 1
            ", ['id' => $id]);
            if ((int)($uso['total'] ?? 0) > 0) {
                return ['success' => false, 'mensaje' => 'No se puede sacar: todavÃ­a estÃ¡ asignado a una divisiÃ³n activa.'];
            }

            $db->CRUD("
                UPDATE atlas_catalogo_divisionales
                SET activo = 0,
                    fecha_actualizacion = NOW()
                WHERE id = :id
            ", ['id' => $id]);

            return ['success' => true, 'mensaje' => 'Divisional sacado del catÃ¡logo.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo sacar el divisional.', 'error' => $e->getMessage()];
        }
    }

    private static function obtenerOCrearDivisionalVacante(Database $db, string $nombreVacante): array
    {
        $nombreVacante = self::strVal($nombreVacante);
        $existente = $db->queryOne("
            SELECT id, tipo_asignacion, persona_id, nombre, nombre_vacante
            FROM atlas_catalogo_divisionales
            WHERE LOWER(nombre) = LOWER(:nombre)
            LIMIT 1
        ", ['nombre' => $nombreVacante]);

        if ($existente) {
            if ((int)($existente['persona_id'] ?? 0) > 0 || ($existente['tipo_asignacion'] ?? '') === 'persona') {
                throw new \RuntimeException('Ya existe un colaborador con ese nombre; usa otro nombre para la vacante.');
            }
            $db->CRUD("
                UPDATE atlas_catalogo_divisionales
                SET tipo_asignacion = 'vacante',
                    persona_id = NULL,
                    nombre_vacante = :nombre,
                    activo = 1
                WHERE id = :id
            ", ['nombre' => $nombreVacante, 'id' => (int)$existente['id']]);
            $existente['tipo_asignacion'] = 'vacante';
            $existente['persona_id'] = null;
            $existente['nombre'] = $nombreVacante;
            $existente['nombre_vacante'] = $nombreVacante;
            return $existente;
        }

        $db->CRUD("
            INSERT INTO atlas_catalogo_divisionales (nombre, persona_id, tipo_asignacion, nombre_vacante, activo, fecha_alta, fecha_actualizacion)
            VALUES (:nombre, NULL, 'vacante', :nombre_vacante, 1, NOW(), NOW())
        ", ['nombre' => $nombreVacante, 'nombre_vacante' => $nombreVacante]);

        return [
            'id' => $db->lastInsertId(),
            'tipo_asignacion' => 'vacante',
            'persona_id' => null,
            'nombre' => $nombreVacante,
            'nombre_vacante' => $nombreVacante,
        ];
    }

    private static function registrarBitacoraAsignacionDivision(Database $db, array $divisionAnterior, array $nuevo, int $divisionId, ?int $usuarioId): void
    {
        $tipoAnterior = (string)($divisionAnterior['tipo_anterior'] ?? '');
        if ($tipoAnterior === '') {
            $tipoAnterior = !empty($divisionAnterior['persona_anterior_id']) ? 'persona' : (!empty($divisionAnterior['nombre_anterior']) ? 'vacante' : null);
        }
        $nombreAnterior = $tipoAnterior === 'vacante'
            ? (string)($divisionAnterior['nombre_vacante_anterior'] ?: $divisionAnterior['nombre_anterior'] ?? '')
            : (string)($divisionAnterior['nombre_anterior'] ?? '');
        $tipoNuevo = (string)($nuevo['tipo_asignacion'] ?? '');
        $nombreNuevo = $tipoNuevo === 'vacante'
            ? (string)($nuevo['nombre_vacante'] ?: $nuevo['nombre'] ?? '')
            : (string)($nuevo['nombre'] ?? '');

        $db->CRUD("
            INSERT INTO atlas_bitacora_asignacion_divisiones (
                division_id,
                divisional_anterior_id,
                tipo_anterior,
                persona_anterior_id,
                nombre_anterior,
                divisional_nuevo_id,
                tipo_nuevo,
                persona_nuevo_id,
                nombre_nuevo,
                accion,
                usuario_id
            ) VALUES (
                :division_id,
                :divisional_anterior_id,
                :tipo_anterior,
                :persona_anterior_id,
                :nombre_anterior,
                :divisional_nuevo_id,
                :tipo_nuevo,
                :persona_nuevo_id,
                :nombre_nuevo,
                'cambio_asignacion',
                :usuario_id
            )
        ", [
            'division_id' => $divisionId,
            'divisional_anterior_id' => self::nullableInt($divisionAnterior['divisional_id'] ?? null),
            'tipo_anterior' => $tipoAnterior,
            'persona_anterior_id' => self::nullableInt($divisionAnterior['persona_anterior_id'] ?? null),
            'nombre_anterior' => $nombreAnterior,
            'divisional_nuevo_id' => (int)($nuevo['id'] ?? 0),
            'tipo_nuevo' => $tipoNuevo,
            'persona_nuevo_id' => self::nullableInt($nuevo['persona_id'] ?? null),
            'nombre_nuevo' => $nombreNuevo,
            'usuario_id' => $usuarioId,
        ]);
    }

    public static function guardarDistribuidor(array $input): array
    {
        $db = null;
        try {
            $db = new Database();
            self::asegurarDistribuidoresAtlas($db);

            $id = self::intVal($input['id'] ?? 0);
            $nombreComercial = self::strVal($input['nombre_comercial'] ?? $input['nombre'] ?? '');
            $razonSocial = self::strVal($input['razon_social'] ?? '');
            $rfc = strtoupper(preg_replace('/[^A-Z0-9Ã‘&]/u', '', self::strVal($input['rfc'] ?? '')));
            $tipoDistribuidor = self::strVal($input['tipo_distribuidor'] ?? '');
            $estatus = strtolower(self::strVal($input['estatus'] ?? 'activo'));
            $nombreContacto = self::strVal($input['nombre_contacto'] ?? '');
            $telefonoContacto = preg_replace('/\D+/', '', self::strVal($input['telefono_contacto'] ?? ''));
            $telefonoSecundario = preg_replace('/\D+/', '', self::strVal($input['telefono_secundario'] ?? ''));
            $emailContacto = strtolower(self::strVal($input['email_contacto'] ?? ''));
            $regimenFiscal = self::strVal($input['regimen_fiscal'] ?? '');
            $tipoMotos = self::strVal($input['tipo_motos'] ?? '');
            $canalVenta = self::strVal($input['canal_venta'] ?? '');
            $tipoPersona = strtolower(self::strVal($input['tipo_persona'] ?? 'moral'));
            $presenciaFisica = self::activoVal($input['presencia_fisica'] ?? 1);

            $faltantes = [];
            foreach ([
                'nombre comercial' => $nombreComercial,
                'razon social' => $razonSocial,
                'RFC' => $rfc,
                'tipo de distribuidor' => $tipoDistribuidor,
                'nombre contacto' => $nombreContacto,
                'telefono contacto' => $telefonoContacto,
                'email contacto' => $emailContacto,
                'regimen fiscal' => $regimenFiscal,
                'tipo de motos' => $tipoMotos,
                'canal venta' => $canalVenta,
            ] as $label => $valor) {
                if ($valor === '') {
                    $faltantes[] = $label;
                }
            }
            if ($faltantes) {
                return ['success' => false, 'mensaje' => 'Completa: ' . implode(', ', $faltantes) . '.'];
            }
            if (!filter_var($emailContacto, FILTER_VALIDATE_EMAIL)) {
                return ['success' => false, 'mensaje' => 'Captura un correo de contacto valido.'];
            }
            if (!preg_match('/^[A-ZÃ‘&]{3,4}[0-9]{6}[A-Z0-9]{3}$/u', $rfc)) {
                return ['success' => false, 'mensaje' => 'Captura un RFC valido.'];
            }
            if (!preg_match('/^[0-9]{10}$/', $telefonoContacto)) {
                return ['success' => false, 'mensaje' => 'El telefono principal debe tener 10 digitos.'];
            }
            if ($telefonoSecundario !== '' && !preg_match('/^[0-9]{10}$/', $telefonoSecundario)) {
                return ['success' => false, 'mensaje' => 'El telefono alterno debe tener 10 digitos.'];
            }
            $errorTipoMotos = self::validarValoresCatalogoDistribuidor($db, 'atlas_catalogo_distribuidor_tipo_moto', $tipoMotos, 'tipo de motos');
            if ($errorTipoMotos !== null) {
                return ['success' => false, 'mensaje' => $errorTipoMotos];
            }
            $errorCanalVenta = self::validarValoresCatalogoDistribuidor($db, 'atlas_catalogo_distribuidor_canal_venta', $canalVenta, 'canal de venta');
            if ($errorCanalVenta !== null) {
                return ['success' => false, 'mensaje' => $errorCanalVenta];
            }
            if (!in_array($estatus, ['activo', 'inactivo', 'suspendido', 'bloqueado', 'pausado', 'inhabilitado'], true)) {
                $estatus = 'activo';
            }
            if (!in_array($tipoPersona, ['fisica', 'moral'], true)) {
                $tipoPersona = 'moral';
            }
            $bloqueoVigencia = strtolower(self::strVal($input['bloqueo_vigencia'] ?? 'indefinida'));
            if (!in_array($bloqueoVigencia, ['indefinida', 'definida'], true)) {
                $bloqueoVigencia = 'indefinida';
            }
            $bloqueoFinAt = self::nullableStr($input['bloqueo_fin_at'] ?? null);
            $tiempoEstadia = self::nullableStr($input['tiempo_promedio_entrega'] ?? null);
            if ($tiempoEstadia !== null && !preg_match('/^[0-9]{1,4}\s+(minutos|horas|dias)$/i', $tiempoEstadia)) {
                return ['success' => false, 'mensaje' => 'El tiempo de permiso debe indicar cantidad y unidad: minutos, horas o dias.'];
            }
            if ($estatus === 'activo') {
                $bloqueoVigencia = null;
                $bloqueoFinAt = null;
            } elseif ($bloqueoVigencia === 'definida' && !$bloqueoFinAt) {
                return ['success' => false, 'mensaje' => 'Captura fecha y hora de fin para la inhabilitacion temporal definida.'];
            }

            $presencias = [];
            $rawPresencias = $input['presencias_json'] ?? '[]';
            if (is_string($rawPresencias)) {
                $decoded = json_decode($rawPresencias, true);
                $presencias = is_array($decoded) ? $decoded : [];
            } elseif (is_array($rawPresencias)) {
                $presencias = $rawPresencias;
            }
            if ($presenciaFisica === 1 && empty($presencias)) {
                return ['success' => false, 'mensaje' => 'Agrega al menos un estado y municipio para la presencia fisica.'];
            }

            $datos = [
                'nombre' => $nombreComercial,
                'nombre_comercial' => $nombreComercial,
                'razon_social' => $razonSocial,
                'rfc' => $rfc,
                'tipo_persona' => $tipoPersona,
                'tipo_distribuidor' => $tipoDistribuidor,
                'estatus' => $estatus,
                'fecha_baja' => $estatus === 'inactivo' ? self::nullableStr($input['fecha_baja'] ?? null) : null,
                'nombre_contacto' => $nombreContacto,
                'telefono_contacto' => $telefonoContacto,
                'telefono_secundario' => $telefonoSecundario !== '' ? $telefonoSecundario : null,
                'email_contacto' => $emailContacto,
                'regimen_fiscal' => $regimenFiscal,
                'tipo_motos' => $tipoMotos,
                'canal_venta' => $canalVenta,
                'horario_atencion' => self::nullableStr($input['horario_atencion'] ?? null),
                'dias_operacion' => self::nullableStr($input['dias_operacion'] ?? null),
                'requiere_cita' => self::activoVal($input['requiere_cita'] ?? 0),
                'tiempo_promedio_entrega' => $tiempoEstadia,
                'bloqueo_vigencia' => $bloqueoVigencia,
                'bloqueo_fin_at' => $bloqueoFinAt,
                'presencia_fisica' => $presenciaFisica,
                'icon_font' => self::nullableStr($input['icon_font'] ?? null),
                'activo' => $estatus === 'activo' ? 1 : 0,
                'observaciones' => self::nullableStr($input['observaciones'] ?? null),
                'motivo_bloqueo' => self::nullableStr($input['motivo_bloqueo'] ?? null),
                'updated_by' => self::nullableInt($input['_usuario_id'] ?? $_SESSION['usuario_id'] ?? null),
            ];

            $db->beginTransaction();
            $previo = $id > 0 ? $db->queryOne("
                SELECT estatus, motivo_bloqueo, bloqueo_vigencia, bloqueo_fin_at
                FROM atlas_catalogo_distribuidores
                WHERE id = :id
                LIMIT 1
            ", ['id' => $id]) : null;
            if ($id > 0) {
                $datos['id'] = $id;
                $db->CRUD("
                    UPDATE atlas_catalogo_distribuidores
                    SET nombre = :nombre,
                        nombre_comercial = :nombre_comercial,
                        razon_social = :razon_social,
                        rfc = :rfc,
                        tipo_persona = :tipo_persona,
                        tipo_distribuidor = :tipo_distribuidor,
                        estatus = :estatus,
                        fecha_baja = :fecha_baja,
                        nombre_contacto = :nombre_contacto,
                        telefono_contacto = :telefono_contacto,
                        telefono_secundario = :telefono_secundario,
                        email_contacto = :email_contacto,
                        regimen_fiscal = :regimen_fiscal,
                        tipo_motos = :tipo_motos,
                        canal_venta = :canal_venta,
                        horario_atencion = :horario_atencion,
                        dias_operacion = :dias_operacion,
                        requiere_cita = :requiere_cita,
                        tiempo_promedio_entrega = :tiempo_promedio_entrega,
                        bloqueo_vigencia = :bloqueo_vigencia,
                        bloqueo_fin_at = :bloqueo_fin_at,
                        presencia_fisica = :presencia_fisica,
                        icon_font = :icon_font,
                        activo = :activo,
                        observaciones = :observaciones,
                        motivo_bloqueo = :motivo_bloqueo,
                        updated_by = :updated_by
                    WHERE id = :id
                ", $datos);
            } else {
                $datos['created_by'] = $datos['updated_by'];
                $campos = array_keys($datos);
                $db->CRUD(
                    "INSERT INTO atlas_catalogo_distribuidores (" . implode(', ', $campos) . ") VALUES (:" . implode(', :', $campos) . ")",
                    $datos
                );
                $id = $db->lastInsertId();
            }

            self::guardarPresenciasDistribuidor($db, $id, $presencias, $presenciaFisica);
            self::registrarBitacoraDistribuidor($db, $id, $previo, $datos);
            $db->commit();

            return ['success' => true, 'mensaje' => 'Distribuidor guardado.', 'id' => $id];
        } catch (\Throwable $e) {
            if ($db && $db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo guardar el distribuidor.', 'error' => $e->getMessage()];
        }
    }

    private static function guardarPresenciasDistribuidor(Database $db, int $distribuidorId, array $presencias, int $presenciaFisica): void
    {
        $db->CRUD("UPDATE atlas_asigna_presencia_distribuidor SET activo = 0 WHERE distribuidor_id = :id", ['id' => $distribuidorId]);
        if ($presenciaFisica !== 1) {
            return;
        }

        $vistos = [];
        foreach ($presencias as $item) {
            $estadoId = self::intVal($item['estado_id'] ?? 0);
            $municipioId = self::intVal($item['municipio_id'] ?? 0);
            if ($estadoId <= 0 || $municipioId <= 0 || isset($vistos[$municipioId])) {
                continue;
            }
            $mun = $db->queryOne("
                SELECT mun.id, mun.nombre AS municipio, est.id AS estado_id, est.nombre AS estado
                FROM divisiones_administrativas mun
                INNER JOIN divisiones_administrativas est
                        ON est.id = mun.id_padre
                       AND est.nivel = 1
                       AND COALESCE(est.activo, 1) = 1
                WHERE mun.id = :municipio_id
                  AND mun.id_padre = :estado_id
                  AND mun.nivel = 2
                  AND COALESCE(mun.activo, 1) = 1
                LIMIT 1
            ", ['municipio_id' => $municipioId, 'estado_id' => $estadoId]);
            if (!$mun) {
                continue;
            }
            $vistos[$municipioId] = true;
            $db->CRUD("
                INSERT INTO atlas_asigna_presencia_distribuidor
                    (distribuidor_id, estado_id, municipio_id, estado, municipio, activo)
                VALUES
                    (:distribuidor_id, :estado_id, :municipio_id, :estado, :municipio, 1)
                ON DUPLICATE KEY UPDATE
                    estado = VALUES(estado),
                    municipio = VALUES(municipio),
                    activo = 1
            ", [
                'distribuidor_id' => $distribuidorId,
                'estado_id' => (int)$mun['estado_id'],
                'municipio_id' => (int)$mun['id'],
                'estado' => (string)$mun['estado'],
                'municipio' => (string)$mun['municipio'],
            ]);
        }
    }

    private static function registrarBitacoraDistribuidor(Database $db, int $distribuidorId, ?array $previo, array $nuevo): void
    {
        $estatusAnterior = strtolower((string)($previo['estatus'] ?? ''));
        $estatusNuevo = strtolower((string)($nuevo['estatus'] ?? ''));
        $motivoAnterior = (string)($previo['motivo_bloqueo'] ?? '');
        $motivoNuevo = (string)($nuevo['motivo_bloqueo'] ?? '');
        $vigenciaAnterior = (string)($previo['bloqueo_vigencia'] ?? '');
        $vigenciaNueva = (string)($nuevo['bloqueo_vigencia'] ?? '');
        $finAnterior = (string)($previo['bloqueo_fin_at'] ?? '');
        $finNuevo = (string)($nuevo['bloqueo_fin_at'] ?? '');

        $esNuevo = $previo === null;
        $cambioOperativo = $esNuevo || $estatusAnterior !== $estatusNuevo || $motivoAnterior !== $motivoNuevo || $vigenciaAnterior !== $vigenciaNueva || $finAnterior !== $finNuevo;
        if (!$cambioOperativo) {
            return;
        }

        $evento = $esNuevo ? 'alta_distribuidor' : 'cambio_estatus_distribuidor';
        if (in_array($estatusNuevo, ['bloqueado', 'pausado', 'inhabilitado'], true)) {
            $evento = $estatusNuevo . '_distribuidor';
        } elseif ($estatusNuevo === 'activo' && in_array($estatusAnterior, ['bloqueado', 'pausado', 'inhabilitado'], true)) {
            $evento = 'reactivacion_distribuidor';
        }

        $db->CRUD("
            INSERT INTO atlas_distribuidor_bitacora
                (distribuidor_id, evento, estatus_anterior, estatus_nuevo, motivo, bloqueo_vigencia, bloqueo_fin_at, usuario_id)
            VALUES
                (:distribuidor_id, :evento, :estatus_anterior, :estatus_nuevo, :motivo, :bloqueo_vigencia, :bloqueo_fin_at, :usuario_id)
        ", [
            'distribuidor_id' => $distribuidorId,
            'evento' => $evento,
            'estatus_anterior' => $estatusAnterior ?: null,
            'estatus_nuevo' => $estatusNuevo ?: null,
            'motivo' => $nuevo['motivo_bloqueo'] ?? null,
            'bloqueo_vigencia' => $nuevo['bloqueo_vigencia'] ?? null,
            'bloqueo_fin_at' => $nuevo['bloqueo_fin_at'] ?? null,
            'usuario_id' => $nuevo['updated_by'] ?? null,
        ]);
    }

    private static function reactivarDistribuidoresVencidos(Database $db): void
    {
        $rows = $db->queryAll("
            SELECT id, estatus, motivo_bloqueo, bloqueo_vigencia, bloqueo_fin_at
            FROM atlas_catalogo_distribuidores
            WHERE estatus IN ('bloqueado', 'pausado', 'inhabilitado')
              AND bloqueo_vigencia = 'definida'
              AND bloqueo_fin_at IS NOT NULL
              AND bloqueo_fin_at <= NOW()
        ");
        foreach ($rows as $row) {
            $id = (int)($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $db->CRUD("
                UPDATE atlas_catalogo_distribuidores
                SET estatus = 'activo',
                    activo = 1,
                    bloqueo_vigencia = NULL,
                    bloqueo_fin_at = NULL,
                    motivo_bloqueo = NULL
                WHERE id = :id
            ", ['id' => $id]);
            self::registrarBitacoraDistribuidor($db, $id, $row, [
                'estatus' => 'activo',
                'motivo_bloqueo' => 'Reactivacion automatica por fin de vigencia.',
                'bloqueo_vigencia' => null,
                'bloqueo_fin_at' => null,
                'updated_by' => null,
            ]);
        }
    }

    private static function sembrarCatalogoDistribuidor(Database $db, string $tabla, array $opciones): void
    {
        foreach ($opciones as $nombre) {
            $db->CRUD("
                INSERT INTO `$tabla` (nombre, activo)
                VALUES (:nombre, 1)
                ON DUPLICATE KEY UPDATE activo = 1
            ", ['nombre' => $nombre]);
        }
    }

    private static function validarValoresCatalogoDistribuidor(Database $db, string $tabla, string $valores, string $label): ?string
    {
        $seleccionados = array_values(array_unique(array_filter(array_map(
            static fn($v) => trim((string)$v),
            explode('|', $valores)
        ))));
        if (empty($seleccionados)) {
            return 'Selecciona al menos una opcion de ' . $label . '.';
        }

        $rows = $db->queryAll("SELECT nombre FROM `$tabla` WHERE activo = 1");
        $permitidos = [];
        foreach ($rows as $row) {
            $permitidos[mb_strtolower(trim((string)($row['nombre'] ?? '')), 'UTF-8')] = true;
        }

        foreach ($seleccionados as $valor) {
            if (!isset($permitidos[mb_strtolower($valor, 'UTF-8')])) {
                return 'La opcion "' . $valor . '" no existe en el catalogo de ' . $label . '.';
            }
        }

        return null;
    }

    public static function guardarCatalogoDistribuidorOpcion(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarDistribuidoresAtlas($db);
            $tipo = self::strVal($input['tipo'] ?? '');
            $nombre = self::strVal($input['nombre'] ?? '');
            $tablas = [
                'tipo_motos' => 'atlas_catalogo_distribuidor_tipo_moto',
                'canal_venta' => 'atlas_catalogo_distribuidor_canal_venta',
            ];
            if (!isset($tablas[$tipo])) {
                return ['success' => false, 'mensaje' => 'Catalogo no valido.'];
            }
            if ($nombre === '' || mb_strlen($nombre, 'UTF-8') > 120) {
                return ['success' => false, 'mensaje' => 'Captura un nombre valido.'];
            }
            $tabla = $tablas[$tipo];
            $db->CRUD("
                INSERT INTO `$tabla` (nombre, activo)
                VALUES (:nombre, 1)
                ON DUPLICATE KEY UPDATE activo = 1
            ", ['nombre' => $nombre]);
            $row = $db->queryOne("SELECT id, nombre, activo FROM `$tabla` WHERE nombre = :nombre LIMIT 1", ['nombre' => $nombre]);
            return ['success' => true, 'mensaje' => 'Opcion agregada.', 'dato' => $row];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar la opcion.', 'error' => $e->getMessage()];
        }
    }

    public static function subirConstanciaFiscalDistribuidor(array $post, array $archivo): array
    {
        try {
            $db = new Database();
            self::asegurarDistribuidoresAtlas($db);

            $id = self::intVal($post['id'] ?? 0);
            if ($id <= 0) {
                return ['success' => false, 'mensaje' => 'Guarda primero el distribuidor para cargar la constancia.'];
            }

            $dist = $db->queryOne("SELECT id FROM atlas_catalogo_distribuidores WHERE id = :id LIMIT 1", ['id' => $id]);
            if (!$dist) {
                return ['success' => false, 'mensaje' => 'No se encontro el distribuidor.'];
            }

            if (empty($archivo) || (int)($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                return ['success' => true, 'mensaje' => 'Distribuidor guardado sin constancia nueva.'];
            }

            $error = (int)($archivo['error'] ?? UPLOAD_ERR_OK);
            if ($error !== UPLOAD_ERR_OK) {
                return ['success' => false, 'mensaje' => 'No se pudo recibir la constancia fiscal.'];
            }

            $size = (int)($archivo['size'] ?? 0);
            if ($size <= 0 || $size > 10 * 1024 * 1024) {
                return ['success' => false, 'mensaje' => 'La constancia fiscal debe pesar maximo 10 MB.'];
            }

            $original = basename((string)($archivo['name'] ?? 'constancia'));
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $permitidos = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!in_array($ext, $permitidos, true)) {
                return ['success' => false, 'mensaje' => 'La constancia fiscal debe ser PDF, JPG o PNG.'];
            }

            $uploadsPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'UploadsPaths.php';
            if (!function_exists('sparta_uploads_join') && is_file($uploadsPath)) {
                require_once $uploadsPath;
            }

            $dir = function_exists('sparta_uploads_join')
                ? \sparta_uploads_join('atlas', 'distribuidores')
                : dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'atlas' . DIRECTORY_SEPARATOR . 'distribuidores';

            if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
                return ['success' => false, 'mensaje' => 'No se pudo crear la carpeta para constancias fiscales.'];
            }

            $nombreLimpio = preg_replace('/[^A-Za-z0-9._-]+/', '_', pathinfo($original, PATHINFO_FILENAME));
            $nombreArchivo = 'dist_' . $id . '_' . date('Ymd_His') . '_' . substr(sha1($original . microtime(true)), 0, 8) . '.' . $ext;
            if ($nombreLimpio !== '') {
                $nombreArchivo = 'dist_' . $id . '_' . date('Ymd_His') . '_' . substr($nombreLimpio, 0, 40) . '.' . $ext;
            }

            $destino = rtrim($dir, DIRECTORY_SEPARATOR . '/\\') . DIRECTORY_SEPARATOR . $nombreArchivo;
            if (!@move_uploaded_file((string)$archivo['tmp_name'], $destino)) {
                return ['success' => false, 'mensaje' => 'No se pudo guardar la constancia fiscal.'];
            }

            $urlRelativa = '/uploads/atlas/distribuidores/' . $nombreArchivo;
            $urlPublica = function_exists('sparta_url_publica_desde_repositorio')
                ? \sparta_url_publica_desde_repositorio($urlRelativa)
                : $urlRelativa;

            $db->CRUD("
                UPDATE atlas_catalogo_distribuidores
                SET constancia_fiscal_url = :url,
                    constancia_fiscal_nombre = :nombre,
                    constancia_fiscal_at = NOW()
                WHERE id = :id
            ", [
                'url' => $urlPublica,
                'nombre' => $original,
                'id' => $id,
            ]);

            return ['success' => true, 'mensaje' => 'Constancia fiscal cargada.', 'url' => $urlPublica];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar la constancia fiscal.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarClasificacion(array $input): array
    {
        $id = self::intVal($input['id'] ?? 0);
        $datos = [
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'icon_font' => self::nullableStr($input['icon_font'] ?? null),
            'color_hex' => self::normalizarColor($input['color_hex'] ?? null),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ];
        if ($id <= 0) {
            try {
                $db = new Database();
                $row = $db->queryOne("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM atlas_catalogo_clasificaciones");
                $datos['orden'] = (int)($row['siguiente'] ?? 1);
            } catch (\Throwable $e) {
                $datos['orden'] = null;
            }
        }

        return self::guardarSimple('atlas_catalogo_clasificaciones', $datos, $id, ['nombre'], 'clasificaciÃƒÂ³n');
    }

    public static function guardarOrdenClasificaciones(array $input): array
    {
        $ids = $input['ids'] ?? [];
        if (!is_array($ids) || count($ids) < 1) {
            return ['success' => false, 'mensaje' => 'No hay clasificaciones para ordenar.'];
        }

        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static function ($id) {
            return $id > 0;
        })));
        if (!$ids) {
            return ['success' => false, 'mensaje' => 'Orden invÃƒÂ¡lido.'];
        }

        try {
            $db = new Database();
            $db->beginTransaction();
            foreach ($ids as $idx => $id) {
                $db->CRUD(
                    "UPDATE atlas_catalogo_clasificaciones SET orden = :orden WHERE id = :id",
                    ['orden' => $idx + 1, 'id' => $id]
                );
            }
            $db->commit();
            return ['success' => true, 'mensaje' => 'Orden actualizado.'];
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo guardar el orden.', 'error' => $e->getMessage()];
        }
    }

    private static function asegurarTablasCatalogosComerciales(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_catalogo_dictamen (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                codigo_estatus VARCHAR(60) NULL,
                clave VARCHAR(80) NOT NULL,
                nombre VARCHAR(180) NOT NULL,
                objetivo TEXT NULL,
                orden INT NOT NULL DEFAULT 1,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                estado_registro VARCHAR(30) NOT NULL DEFAULT 'publicado',
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_dictamen_activo_orden (activo, orden),
                KEY idx_atlas_dictamen_clave (clave)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_dictamen_sub_estatus (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                dictamen_id INT UNSIGNED NOT NULL,
                clave VARCHAR(80) NOT NULL,
                nombre VARCHAR(180) NOT NULL,
                orden INT NOT NULL DEFAULT 1,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                estado_registro VARCHAR(30) NOT NULL DEFAULT 'publicado',
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_sub_dictamen_orden (dictamen_id, orden),
                KEY idx_atlas_sub_clave (clave)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_catalogo_tipos_gestion (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                clave VARCHAR(80) NOT NULL,
                nombre VARCHAR(180) NOT NULL,
                orden INT NOT NULL DEFAULT 1,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                estado_registro VARCHAR(30) NOT NULL DEFAULT 'publicado',
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_tipo_gestion_orden (activo, orden),
                KEY idx_atlas_tipo_gestion_clave (clave)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_catalogo_gestion (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                dictamen_id INT UNSIGNED NOT NULL,
                subestatus_id INT UNSIGNED NOT NULL,
                tipo_gestion_id INT UNSIGNED NULL,
                tipo_gestion VARCHAR(150) NULL,
                clave VARCHAR(80) NOT NULL,
                nombre VARCHAR(220) NOT NULL,
                ventana_complementaria VARCHAR(180) NULL,
                campos_adicionales TEXT NULL,
                requiere_fecha TINYINT(1) NOT NULL DEFAULT 0,
                permite_comentario TINYINT(1) NOT NULL DEFAULT 1,
                orden INT NOT NULL DEFAULT 1,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                estado_registro VARCHAR(30) NOT NULL DEFAULT 'publicado',
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_gestion_sub_orden (subestatus_id, orden),
                KEY idx_atlas_gestion_dictamen (dictamen_id),
                KEY idx_atlas_gestion_clave (clave)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'codigo_estatus', "VARCHAR(60) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'clave', "VARCHAR(80) NOT NULL DEFAULT ''");
        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'nombre', "VARCHAR(180) NOT NULL DEFAULT ''");
        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'objetivo', "TEXT NULL");
        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'orden', "INT NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'activo', "TINYINT(1) NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'estado_registro', "VARCHAR(30) NOT NULL DEFAULT 'publicado'");
        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'fecha_alta', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
        self::asegurarColumna($db, 'atlas_catalogo_dictamen', 'fecha_actualizacion', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        self::asegurarColumna($db, 'atlas_dictamen_sub_estatus', 'dictamen_id', "INT UNSIGNED NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_dictamen_sub_estatus', 'clave', "VARCHAR(80) NOT NULL DEFAULT ''");
        self::asegurarColumna($db, 'atlas_dictamen_sub_estatus', 'nombre', "VARCHAR(180) NOT NULL DEFAULT ''");
        self::asegurarColumna($db, 'atlas_dictamen_sub_estatus', 'orden', "INT NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_dictamen_sub_estatus', 'activo', "TINYINT(1) NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_dictamen_sub_estatus', 'estado_registro', "VARCHAR(30) NOT NULL DEFAULT 'publicado'");
        self::asegurarColumna($db, 'atlas_dictamen_sub_estatus', 'fecha_alta', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
        self::asegurarColumna($db, 'atlas_dictamen_sub_estatus', 'fecha_actualizacion', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        self::asegurarColumna($db, 'atlas_catalogo_tipos_gestion', 'clave', "VARCHAR(80) NOT NULL DEFAULT ''");
        self::asegurarColumna($db, 'atlas_catalogo_tipos_gestion', 'nombre', "VARCHAR(180) NOT NULL DEFAULT ''");
        self::asegurarColumna($db, 'atlas_catalogo_tipos_gestion', 'orden', "INT NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_catalogo_tipos_gestion', 'activo', "TINYINT(1) NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_catalogo_tipos_gestion', 'estado_registro', "VARCHAR(30) NOT NULL DEFAULT 'publicado'");
        self::asegurarColumna($db, 'atlas_catalogo_tipos_gestion', 'fecha_alta', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
        self::asegurarColumna($db, 'atlas_catalogo_tipos_gestion', 'fecha_actualizacion', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'dictamen_id', "INT UNSIGNED NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'sub_estatus_id', "INT UNSIGNED NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'subestatus_id', "INT UNSIGNED NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'tipo_gestion_id', "INT UNSIGNED NULL");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'tipo_gestion', "VARCHAR(150) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'clave', "VARCHAR(80) NOT NULL DEFAULT ''");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'nombre', "VARCHAR(220) NOT NULL DEFAULT ''");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'ventana_complementaria', "VARCHAR(180) NULL");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'campos_adicionales', "TEXT NULL");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'requiere_fecha', "TINYINT(1) NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'permite_comentario', "TINYINT(1) NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'orden', "INT NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'activo', "TINYINT(1) NOT NULL DEFAULT 1");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'estado_registro', "VARCHAR(30) NOT NULL DEFAULT 'publicado'");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'fecha_alta', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP");
        self::asegurarColumna($db, 'atlas_catalogo_gestion', 'fecha_actualizacion', "DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

        self::sincronizarTiposGestionDesdeGestiones($db);
        $db->CRUD("
            UPDATE atlas_catalogo_gestion
            SET subestatus_id = sub_estatus_id
            WHERE (subestatus_id IS NULL OR subestatus_id = 0)
              AND COALESCE(sub_estatus_id, 0) > 0
        ");
    }

    private static function asegurarColumna(Database $db, string $tabla, string $columna, string $definicion): void
    {
        $existe = $db->queryOne("SHOW COLUMNS FROM `$tabla` LIKE :columna", ['columna' => $columna]);
        if (!$existe) {
            $db->CRUD("ALTER TABLE `$tabla` ADD COLUMN `$columna` $definicion");
        }
    }

    private static function asegurarDivisionalesPersonaAtlas(Database $db): void
    {
        self::asegurarColumna($db, 'atlas_catalogo_divisionales', 'persona_id', "INT NULL AFTER nombre");
        self::asegurarColumna($db, 'atlas_catalogo_divisionales', 'tipo_asignacion', "VARCHAR(30) NOT NULL DEFAULT 'persona' AFTER persona_id");
        self::asegurarColumna($db, 'atlas_catalogo_divisionales', 'nombre_vacante', "VARCHAR(160) NULL AFTER tipo_asignacion");
        self::asegurarColumna($db, 'atlas_catalogo_divisionales', 'vacante_personal_id', "INT NULL AFTER nombre_vacante");

        $db->CRUD("
            UPDATE atlas_catalogo_divisionales
            SET tipo_asignacion = CASE WHEN persona_id IS NULL THEN 'vacante' ELSE 'persona' END,
                nombre_vacante = CASE WHEN persona_id IS NULL THEN COALESCE(NULLIF(nombre_vacante, ''), nombre) ELSE NULL END
            WHERE tipo_asignacion IS NULL
               OR tipo_asignacion = ''
               OR (persona_id IS NULL AND (nombre_vacante IS NULL OR nombre_vacante = ''))
        ");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_bitacora_asignacion_divisiones (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                division_id BIGINT NOT NULL,
                divisional_anterior_id BIGINT NULL,
                tipo_anterior VARCHAR(30) NULL,
                persona_anterior_id INT NULL,
                nombre_anterior VARCHAR(180) NULL,
                divisional_nuevo_id BIGINT NULL,
                tipo_nuevo VARCHAR(30) NULL,
                persona_nuevo_id INT NULL,
                nombre_nuevo VARCHAR(180) NULL,
                accion VARCHAR(80) NOT NULL DEFAULT 'cambio_asignacion',
                usuario_id INT NULL,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_bit_division (division_id),
                KEY idx_atlas_bit_fecha (fecha_alta)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $indice = $db->queryOne("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'atlas_catalogo_divisionales'
              AND index_name = 'idx_atlas_divisionales_persona'
            LIMIT 1
        ");
        if (!$indice) {
            $db->CRUD("ALTER TABLE atlas_catalogo_divisionales ADD INDEX idx_atlas_divisionales_persona (persona_id)");
        }

        $indiceVacante = $db->queryOne("
            SELECT 1
            FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'atlas_catalogo_divisionales'
              AND index_name = 'idx_atlas_divisionales_vacante_personal'
            LIMIT 1
        ");
        if (!$indiceVacante) {
            $db->CRUD("ALTER TABLE atlas_catalogo_divisionales ADD INDEX idx_atlas_divisionales_vacante_personal (vacante_personal_id)");
        }

        $fk = $db->queryOne("
            SELECT 1
            FROM information_schema.table_constraints
            WHERE constraint_schema = DATABASE()
              AND table_name = 'atlas_catalogo_divisionales'
              AND constraint_name = 'fk_atlas_divisionales_persona'
            LIMIT 1
        ");
        if (!$fk) {
            try {
                $db->CRUD("
                    ALTER TABLE atlas_catalogo_divisionales
                    ADD CONSTRAINT fk_atlas_divisionales_persona
                    FOREIGN KEY (persona_id) REFERENCES persona(id)
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
                ");
            } catch (\Throwable $e) {
                // Si una instancia no permite agregar la FK, la columna e indice quedan disponibles.
            }
        }

        $fkVacante = $db->queryOne("
            SELECT 1
            FROM information_schema.table_constraints
            WHERE constraint_schema = DATABASE()
              AND table_name = 'atlas_catalogo_divisionales'
              AND constraint_name = 'fk_atlas_divisionales_vacante_personal'
            LIMIT 1
        ");
        if (!$fkVacante) {
            try {
                $db->CRUD("
                    ALTER TABLE atlas_catalogo_divisionales
                    ADD CONSTRAINT fk_atlas_divisionales_vacante_personal
                    FOREIGN KEY (vacante_personal_id) REFERENCES vacantes_personal(id)
                    ON UPDATE CASCADE
                    ON DELETE SET NULL
                ");
            } catch (\Throwable $e) {
                // La columna e indice quedan disponibles aunque alguna instancia no permita crear la FK.
            }
        }

        self::sincronizarDivisionalesPersonaAtlas($db);
    }

    private static function asegurarResponsablesPersonaAtlas(Database $db): void
    {
        $tablas = [
            'atlas_catalogo_regionales' => 'regionales',
            'atlas_catalogo_supervisores' => 'supervisores',
            'atlas_catalogo_asesores' => 'asesores',
        ];

        foreach ($tablas as $tabla => $alias) {
            self::asegurarColumna($db, $tabla, 'persona_id', "INT NULL AFTER nombre");
            self::asegurarColumna($db, $tabla, 'tipo_asignacion', "VARCHAR(30) NOT NULL DEFAULT 'persona' AFTER persona_id");
            self::asegurarColumna($db, $tabla, 'nombre_vacante', "VARCHAR(160) NULL AFTER tipo_asignacion");
            self::asegurarColumna($db, $tabla, 'vacante_personal_id', "INT NULL AFTER nombre_vacante");

            $db->CRUD("
                UPDATE `$tabla`
                SET tipo_asignacion = CASE
                        WHEN UPPER(nombre) LIKE '%VACANTE%' OR UPPER(nombre) LIKE '%NO SE ASIGNA%' THEN 'vacante'
                        ELSE COALESCE(NULLIF(tipo_asignacion, ''), 'persona')
                    END,
                    persona_id = CASE
                        WHEN UPPER(nombre) LIKE '%VACANTE%' OR UPPER(nombre) LIKE '%NO SE ASIGNA%' THEN NULL
                        ELSE persona_id
                    END,
                    nombre_vacante = CASE
                        WHEN UPPER(nombre) LIKE '%VACANTE%' OR UPPER(nombre) LIKE '%NO SE ASIGNA%' THEN COALESCE(NULLIF(nombre_vacante, ''), nombre)
                        ELSE NULL
                    END
                WHERE tipo_asignacion IS NULL
                   OR tipo_asignacion = ''
                   OR UPPER(nombre) LIKE '%VACANTE%'
                   OR UPPER(nombre) LIKE '%NO SE ASIGNA%'
                   OR (tipo_asignacion = 'persona' AND nombre_vacante IS NOT NULL)
            ");

            $indice = $db->queryOne("
                SELECT 1
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = :tabla
                  AND index_name = :indice
                LIMIT 1
            ", ['tabla' => $tabla, 'indice' => 'idx_atlas_' . $alias . '_persona']);
            if (!$indice) {
                $db->CRUD("ALTER TABLE `$tabla` ADD INDEX `idx_atlas_" . $alias . "_persona` (`persona_id`)");
            }

            $indiceVacante = $db->queryOne("
                SELECT 1
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name = :tabla
                  AND index_name = :indice
                LIMIT 1
            ", ['tabla' => $tabla, 'indice' => 'idx_atlas_' . $alias . '_vacante_personal']);
            if (!$indiceVacante) {
                $db->CRUD("ALTER TABLE `$tabla` ADD INDEX `idx_atlas_" . $alias . "_vacante_personal` (`vacante_personal_id`)");
            }

            $fk = $db->queryOne("
                SELECT 1
                FROM information_schema.table_constraints
                WHERE constraint_schema = DATABASE()
                  AND table_name = :tabla
                  AND constraint_name = :fk
                LIMIT 1
            ", ['tabla' => $tabla, 'fk' => 'fk_atlas_' . $alias . '_persona']);
            if (!$fk) {
                try {
                    $db->CRUD("
                        ALTER TABLE `$tabla`
                        ADD CONSTRAINT `fk_atlas_" . $alias . "_persona`
                        FOREIGN KEY (`persona_id`) REFERENCES `persona` (`id`)
                        ON UPDATE CASCADE
                        ON DELETE SET NULL
                    ");
                } catch (\Throwable $e) {
                    // La columna e indice quedan disponibles aunque alguna instancia no permita crear la FK.
                }
            }

            $fkVacante = $db->queryOne("
                SELECT 1
                FROM information_schema.table_constraints
                WHERE constraint_schema = DATABASE()
                  AND table_name = :tabla
                  AND constraint_name = :fk
                LIMIT 1
            ", ['tabla' => $tabla, 'fk' => 'fk_atlas_' . $alias . '_vacante_personal']);
            if (!$fkVacante) {
                try {
                    $db->CRUD("
                        ALTER TABLE `$tabla`
                        ADD CONSTRAINT `fk_atlas_" . $alias . "_vacante_personal`
                        FOREIGN KEY (`vacante_personal_id`) REFERENCES `vacantes_personal` (`id`)
                        ON UPDATE CASCADE
                        ON DELETE SET NULL
                    ");
                } catch (\Throwable $e) {
                    // La columna e indice quedan disponibles aunque alguna instancia no permita crear la FK.
                }
            }
        }

        self::sincronizarResponsablesPersonaAtlas($db);
    }

    private static function esAsignacionVacanteAtlas(string $nombre): bool
    {
        $normalizado = self::normalizarNombreAtlas($nombre);
        return str_contains($normalizado, 'VACANTE') || str_contains($normalizado, 'NO SE ASIGNA');
    }

    private static function normalizarNombreAtlas(string $texto): string
    {
        $texto = trim($texto);
        if ($texto === '') {
            return '';
        }
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        $texto = strtoupper((string)($ascii !== false ? $ascii : $texto));
        $texto = preg_replace('/[^A-Z0-9 ]+/', ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', (string)$texto);
        return trim((string)$texto);
    }

    private static function scoreNombreAtlas(string $a, string $b): float
    {
        $ta = array_values(array_unique(array_filter(explode(' ', self::normalizarNombreAtlas($a)), static fn($v) => strlen($v) > 1)));
        $tb = array_values(array_unique(array_filter(explode(' ', self::normalizarNombreAtlas($b)), static fn($v) => strlen($v) > 1)));
        if (!$ta || !$tb) {
            return 0.0;
        }
        return count(array_intersect($ta, $tb)) / max(count($ta), count($tb));
    }

    private static function sincronizarDivisionalesPersonaAtlas(Database $db): void
    {
        self::asegurarAccesosAtlas($db);
        $divisionales = $db->queryAll("
            SELECT id, nombre, persona_id
            FROM atlas_catalogo_divisionales
            WHERE activo = 1
        ");
        $usuarios = $db->queryAll("
            SELECT persona_id, nombre
            FROM atlas_acceso_usuarios
            WHERE activo = 1
              AND excluido_operativo = 0
        ");

        foreach ($divisionales as $divisional) {
            if ((int)($divisional['persona_id'] ?? 0) > 0) {
                continue;
            }
            $matches = [];
            foreach ($usuarios as $usuario) {
                if (self::scoreNombreAtlas((string)$divisional['nombre'], (string)$usuario['nombre']) >= 1.0) {
                    $matches[] = $usuario;
                }
            }
            if (count($matches) !== 1) {
                continue;
            }
            $db->CRUD(
                "UPDATE atlas_catalogo_divisionales
                 SET persona_id = :persona_id,
                     tipo_asignacion = 'persona',
                     nombre_vacante = NULL
                 WHERE id = :id",
                ['persona_id' => (int)$matches[0]['persona_id'], 'id' => (int)$divisional['id']]
            );
        }
    }

    private static function sincronizarResponsablesPersonaAtlas(Database $db): void
    {
        $personas = $db->queryAll("
            SELECT id, estatus,
                   TRIM(CONCAT_WS(' ', NULLIF(nombres, ''), NULLIF(segundo_nombre, ''), NULLIF(apellidop, ''), NULLIF(apellidom, ''))) AS nombre
            FROM persona
            WHERE estatus <> 'Baja'
        ");
        $catalogos = [
            'atlas_catalogo_regionales',
            'atlas_catalogo_supervisores',
            'atlas_catalogo_asesores',
        ];

        foreach ($catalogos as $tabla) {
            $rows = $db->queryAll("
                SELECT id, nombre, persona_id, tipo_asignacion
                FROM `$tabla`
                WHERE activo = 1
            ");

            foreach ($rows as $row) {
                $nombre = (string)($row['nombre'] ?? '');
                if ((int)($row['persona_id'] ?? 0) > 0 || self::esAsignacionVacanteAtlas($nombre)) {
                    continue;
                }

                $matches = [];
                foreach ($personas as $persona) {
                    if (self::scoreNombreAtlas($nombre, (string)($persona['nombre'] ?? '')) >= 1.0) {
                        $matches[] = $persona;
                    }
                }
                if (count($matches) !== 1) {
                    continue;
                }

                $db->CRUD("
                    UPDATE `$tabla`
                    SET persona_id = :persona_id,
                        tipo_asignacion = 'persona',
                        nombre_vacante = NULL
                    WHERE id = :id
                ", [
                    'persona_id' => (int)$matches[0]['id'],
                    'id' => (int)$row['id'],
                ]);
            }
        }
    }

    private static function fechaServidorACdmx(?string $fecha): string
    {
        $fecha = trim((string)$fecha);
        if ($fecha === '') {
            return '';
        }
        try {
            return (new \DateTimeImmutable($fecha, new \DateTimeZone('UTC')))
                ->setTimezone(new \DateTimeZone('America/Mexico_City'))
                ->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return $fecha;
        }
    }

    private static function aplicarFechaActualizacionCdmx(array &$rows): void
    {
        foreach ($rows as &$row) {
            $row['fecha_actualizacion_fmt'] = self::fechaServidorACdmx($row['fecha_actualizacion_raw'] ?? $row['fecha_actualizacion'] ?? null);
        }
        unset($row);
    }

    public static function getSucursalesAsignadas(): array
    {
        try {
            $db = new Database();
            self::asegurarPresupuestosAtlas($db);
            $presupuestoBase = $db->queryOne("
                SELECT id, anio, mes, nombre_mes,
                       DATE_FORMAT(fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt
                FROM atlas_presupuestos_mensuales
                WHERE activo = 1
                ORDER BY anio DESC, mes DESC, id DESC
                LIMIT 1
            ") ?: null;
            $presupuestoBaseId = (int)($presupuestoBase['id'] ?? 0);
            $rows = $db->queryAll("
                SELECT
                    s.fk_sucursal,
                    s.sucursal,
                    COALESCE(NULLIF(s.direccion_sucursal, ''), 'Sin direccion') AS direccion,
                    s.latitud,
                    s.longitud,
                    COALESCE(tel.numero_telefono, '') AS numero_telefono,
                    COALESCE(divi.nombre, 'Sin division') AS division_nombre,
                    COALESCE(reg.nombre, 'Sin regional') AS regional_nombre,
                    COALESCE(gest.gestores_asignados, 'Sin gestor') AS gestores_asignados,
                    COALESCE(gest.total_gestores, 0) AS total_gestores,
                    COALESCE(res.total_pendientes, 0) AS total_pendientes,
                    COALESCE(res.total_revisar_etapa, 0) AS total_revisar_etapa,
                    COALESCE(res.cash_detenido_total, 0) AS cash_detenido_total,
                    COALESCE(res.cash_terminal_total, 0) AS cash_terminal_total,
                    COALESCE(res.cash_avanzado_total, 0) AS cash_avanzado_total,
                    COALESCE(res.creditos_sin_etapa, 0) AS creditos_sin_etapa,
                    COALESCE(res.cash_sin_etapa, 0) AS cash_sin_etapa,
                    COALESCE(res.bucket_principal_raw, 'Sin pipeline operativo') AS bucket_principal_raw,
                    COALESCE(buckets.bucket_resumen, '') AS bucket_resumen,
                    CASE WHEN pdet.id IS NULL THEN 0 ELSE 1 END AS tiene_presupuesto,
                    pdet.id AS presupuesto_detalle_id,
                    COALESCE(pdet.meta_creditos, 0) AS presupuesto_meta_creditos,
                    COALESCE(pdet.meta_cash, 0) AS presupuesto_meta_cash
                FROM atlas_catalogo_sucursales s
                LEFT JOIN (
                    SELECT
                        x.fk_sucursal,
                        SUM(CASE WHEN x.tipo_bucket_actual = 'detenido' OR x.bucket_actual LIKE 'DETENIDO:%' THEN 1 ELSE 0 END) AS total_pendientes,
                        SUM(CASE WHEN x.tipo_bucket_actual = 'revisar_etapa' OR x.bucket_actual LIKE 'REVISAR ETAPA:%' THEN 1 ELSE 0 END) AS total_revisar_etapa,
                        SUM(CASE WHEN x.tipo_bucket_actual = 'detenido' OR x.bucket_actual LIKE 'DETENIDO:%' THEN COALESCE(x.monto_financiar, 0) ELSE 0 END) AS cash_detenido_total,
                        SUM(CASE WHEN x.tipo_bucket_actual = 'terminal' OR x.bucket_actual = 'NO DETENIDO / TERMINAL O POST-OPERATIVO' THEN COALESCE(x.monto_financiar, 0) ELSE 0 END) AS cash_terminal_total,
                        SUM(CASE WHEN x.tipo_bucket_actual = 'terminal' OR x.bucket_actual = 'NO DETENIDO / TERMINAL O POST-OPERATIVO' THEN COALESCE(x.monto_financiar, 0) ELSE 0 END) AS cash_avanzado_total,
                        SUM(CASE WHEN x.tipo_bucket_actual = 'sin_etapa' OR x.bucket_actual = 'DATOS SIN ETAPA / DEPURAR' THEN 1 ELSE 0 END) AS creditos_sin_etapa,
                        SUM(CASE WHEN x.tipo_bucket_actual = 'sin_etapa' OR x.bucket_actual = 'DATOS SIN ETAPA / DEPURAR' THEN COALESCE(x.monto_financiar, 0) ELSE 0 END) AS cash_sin_etapa,
                        SUBSTRING_INDEX(
                            GROUP_CONCAT(CONCAT(x.bucket_actual, '::', x.bucket_count) ORDER BY x.bucket_count DESC, x.bucket_actual ASC SEPARATOR '|'),
                            '|',
                            1
                        ) AS bucket_principal_raw
                    FROM (
                        SELECT
                            ac.fk_sucursal,
                            COALESCE(NULLIF(snap.bucket_actual, ''), 'SIN SNAPSHOT') AS bucket_actual,
                            COALESCE(NULLIF(snap.tipo_bucket_actual, ''), 'sin_snapshot') AS tipo_bucket_actual,
                            SUM(COALESCE(snap.monto_financiar, c.cash_detenido, c.monto_credito, 0)) AS monto_financiar,
                            COUNT(*) AS bucket_count
                        FROM atlas_asigna_sucursal_credito ac
                        INNER JOIN atlas_creditos c
                                ON c.id = ac.credito_id
                               AND c.activo = 1
                        LEFT JOIN atlas_creditos_oferta_snapshot snap
                               ON snap.credito_id = c.id
                              AND snap.activo = 1
                        WHERE ac.activo = 1
                        GROUP BY ac.fk_sucursal, bucket_actual, tipo_bucket_actual
                    ) x
                    GROUP BY x.fk_sucursal
                ) res ON res.fk_sucursal = s.fk_sucursal
                LEFT JOIN (
                    SELECT
                        y.fk_sucursal,
                        GROUP_CONCAT(CONCAT(y.bucket_actual, ': ', y.bucket_count) ORDER BY y.bucket_count DESC, y.bucket_actual ASC SEPARATOR ' | ') AS bucket_resumen
                    FROM (
                        SELECT
                            ac.fk_sucursal,
                            COALESCE(NULLIF(snap.bucket_actual, ''), 'SIN SNAPSHOT') AS bucket_actual,
                            COUNT(*) AS bucket_count
                        FROM atlas_asigna_sucursal_credito ac
                        INNER JOIN atlas_creditos c
                                ON c.id = ac.credito_id
                               AND c.activo = 1
                        LEFT JOIN atlas_creditos_oferta_snapshot snap
                               ON snap.credito_id = c.id
                              AND snap.activo = 1
                        WHERE ac.activo = 1
                        GROUP BY ac.fk_sucursal, bucket_actual
                    ) y
                    GROUP BY y.fk_sucursal
                ) buckets ON buckets.fk_sucursal = s.fk_sucursal
                LEFT JOIN atlas_asigna_telefono_sucursal tel
                       ON tel.fk_sucursal = s.fk_sucursal
                      AND tel.activo = 1
                      AND tel.es_principal = 1
                LEFT JOIN atlas_catalogo_divisiones divi ON divi.id = s.division_id
                LEFT JOIN atlas_catalogo_regionales reg ON reg.id = s.regional_id
                LEFT JOIN atlas_presupuesto_sucursal_detalle pdet
                       ON pdet.fk_sucursal = s.fk_sucursal
                      AND pdet.presupuesto_id = :presupuesto_id
                      AND pdet.activo = 1
                LEFT JOIN (
                    SELECT
                        gs.fk_sucursal,
                        COUNT(*) AS total_gestores,
                        GROUP_CONCAT(
                            TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom))
                            ORDER BY gs.es_principal DESC, p.nombres ASC
                            SEPARATOR ', '
                        ) AS gestores_asignados
                    FROM atlas_gestor_sucursales gs
                    INNER JOIN atlas_gestores_operativos go
                            ON go.id = gs.gestor_id
                           AND go.activo = 1
                    INNER JOIN persona p ON p.id = gs.persona_id
                    WHERE gs.activo = 1
                    GROUP BY gs.fk_sucursal
                ) gest ON gest.fk_sucursal = s.fk_sucursal
                WHERE s.activo = 1
                ORDER BY res.cash_detenido_total DESC, res.total_pendientes DESC, s.sucursal ASC
            ", ['presupuesto_id' => $presupuestoBaseId]);

            foreach ($rows as &$row) {
                $raw = (string)($row['bucket_principal_raw'] ?? '');
                if ($raw !== '' && str_contains($raw, '::')) {
                    $row['bucket_principal'] = explode('::', $raw, 2)[0];
                }
                unset($row['bucket_principal_raw']);
            }
            unset($row);

            return [
                'success' => true,
                'mensaje' => 'Seguimiento obtenido.',
                'datos' => $rows,
                'meta' => [
                    'fuente' => 'sparta',
                    'total_sucursales' => count($rows),
                    'presupuesto_base' => $presupuestoBase,
                    'sucursales_sin_presupuesto' => count(array_filter($rows, static fn($row) => (int)($row['tiene_presupuesto'] ?? 0) === 0)),
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar seguimiento.', 'error' => $e->getMessage(), 'datos' => []];
        }
    }

    public static function getSucursalAsignadaDetalle(int $fkSucursal): array
    {
        if ($fkSucursal <= 0) {
            return ['success' => false, 'mensaje' => 'Sucursal invalida.'];
        }

        try {
            $db = new Database();
            $sucursal = $db->queryOne("
                SELECT
                    s.fk_sucursal,
                    s.sucursal,
                    COALESCE(NULLIF(s.direccion_sucursal, ''), 'Sin direccion') AS direccion,
                    s.latitud,
                    s.longitud,
                    COALESCE(tel.numero_telefono, '') AS numero_telefono,
                    COALESCE(divi.nombre, 'Sin division') AS division_nombre,
                    COALESCE(reg.nombre, 'Sin regional') AS regional_nombre
                FROM atlas_catalogo_sucursales s
                LEFT JOIN atlas_asigna_telefono_sucursal tel
                       ON tel.fk_sucursal = s.fk_sucursal
                      AND tel.activo = 1
                      AND tel.es_principal = 1
                LEFT JOIN atlas_catalogo_divisiones divi ON divi.id = s.division_id
                LEFT JOIN atlas_catalogo_regionales reg ON reg.id = s.regional_id
                WHERE s.fk_sucursal = :fk
                LIMIT 1
            ", ['fk' => $fkSucursal]);

            if (!$sucursal) {
                return ['success' => false, 'mensaje' => 'No se encontro la sucursal.'];
            }

            $gestores = $db->queryAll("
                SELECT
                    gs.id,
                    gs.gestor_id,
                    gs.persona_id,
                    gs.tipo_cobertura,
                    gs.es_principal,
                    p.numero_empleado,
                    p.user_name,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS gestor_nombre
                FROM atlas_gestor_sucursales gs
                INNER JOIN atlas_gestores_operativos go
                        ON go.id = gs.gestor_id
                       AND go.activo = 1
                INNER JOIN persona p ON p.id = gs.persona_id
                WHERE gs.fk_sucursal = :fk
                  AND gs.activo = 1
                ORDER BY gs.es_principal DESC, gestor_nombre ASC
            ", ['fk' => $fkSucursal]);

            $creditos = $db->queryAll("
                SELECT
                    c.id AS credito_id,
                    c.id_solicitud,
                    c.usuario,
                    c.fecha_siguiente_seguimiento,
                    DATE_FORMAT(c.fecha_siguiente_seguimiento, '%d/%m/%Y %H:%i') AS fecha_siguiente_seguimiento_fmt,
                    ac.credito_id AS vinculo_credito_id,
                    ac.fk_sucursal AS vinculo_fk_sucursal,
                    snap.oferta_id,
                    COALESCE(NULLIF(snap.etapa_actual, ''), c.estatus_actual, 'Sin etapa') AS etapa_original,
                    COALESCE(NULLIF(snap.bucket_actual, ''), c.estatus_actual, 'SIN SNAPSHOT') AS bucket_operativo,
                    COALESCE(NULLIF(snap.tipo_bucket_actual, ''), 'sin_snapshot') AS tipo_bucket,
                    COALESCE(snap.monto_financiar, c.cash_detenido, c.monto_credito, 0) AS monto_financiar,
                    CASE WHEN snap.monto_financiar IS NOT NULL THEN 'oferta.monto_financiar' ELSE 'atlas_creditos' END AS fuente_monto
                FROM atlas_asigna_sucursal_credito ac
                INNER JOIN atlas_creditos c
                        ON c.id = ac.credito_id
                       AND c.activo = 1
                LEFT JOIN atlas_creditos_oferta_snapshot snap
                       ON snap.credito_id = c.id
                      AND snap.activo = 1
                WHERE ac.fk_sucursal = :fk
                  AND ac.activo = 1
                ORDER BY
                    CASE
                        WHEN snap.es_pendiente_operativo = 1 THEN 0
                        WHEN snap.tipo_bucket_actual = 'revisar_etapa' THEN 1
                        ELSE 2
                    END,
                    monto_financiar DESC,
                    c.id_solicitud ASC
            ", ['fk' => $fkSucursal]);

            return [
                'success' => true,
                'mensaje' => 'Detalle obtenido.',
                'datos' => [
                    'sucursal' => $sucursal,
                    'gestores' => $gestores,
                    'creditos' => $creditos,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar detalle de sucursal.', 'error' => $e->getMessage()];
        }
    }

    private static function asegurarPresupuestosAtlas(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_presupuestos_mensuales (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                anio SMALLINT UNSIGNED NOT NULL,
                mes TINYINT UNSIGNED NOT NULL,
                nombre_mes VARCHAR(20) NOT NULL,
                archivo_original VARCHAR(220) NULL,
                total_sucursales INT UNSIGNED NOT NULL DEFAULT 0,
                total_creditos DECIMAL(14,2) NOT NULL DEFAULT 0,
                total_cash DECIMAL(16,2) NOT NULL DEFAULT 0,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                created_by INT NULL,
                updated_by INT NULL,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_atlas_presupuesto_anio_mes (anio, mes),
                KEY idx_atlas_presupuesto_activo (activo, anio, mes)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_presupuesto_sucursal_detalle (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                presupuesto_id BIGINT UNSIGNED NOT NULL,
                fk_sucursal INT NOT NULL,
                sucursal VARCHAR(180) NULL,
                diversificacion VARCHAR(120) NULL,
                distribuidor VARCHAR(180) NULL,
                divisional VARCHAR(180) NULL,
                regional VARCHAR(180) NULL,
                supervisor VARCHAR(180) NULL,
                asesor VARCHAR(180) NULL,
                estado VARCHAR(120) NULL,
                promedio_creditos DECIMAL(14,2) NULL,
                clasificacion VARCHAR(120) NULL,
                meta_creditos DECIMAL(14,2) NOT NULL DEFAULT 0,
                meta_cash DECIMAL(16,2) NOT NULL DEFAULT 0,
                observaciones TEXT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                updated_by INT NULL,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_atlas_pres_det_mes_sucursal (presupuesto_id, fk_sucursal),
                KEY idx_atlas_pres_det_sucursal (fk_sucursal),
                KEY idx_atlas_pres_det_activo (presupuesto_id, activo)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_presupuesto_bitacora (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                presupuesto_id BIGINT UNSIGNED NULL,
                anio SMALLINT UNSIGNED NOT NULL,
                mes TINYINT UNSIGNED NOT NULL,
                evento VARCHAR(60) NOT NULL,
                descripcion VARCHAR(250) NOT NULL,
                sucursal_detalle_id BIGINT UNSIGNED NULL,
                fk_sucursal INT NULL,
                meta_creditos_anterior DECIMAL(14,2) NULL,
                meta_creditos_nueva DECIMAL(14,2) NULL,
                meta_cash_anterior DECIMAL(16,2) NULL,
                meta_cash_nueva DECIMAL(16,2) NULL,
                archivo_original VARCHAR(220) NULL,
                total_sucursales INT UNSIGNED NULL,
                payload_json JSON NULL,
                usuario_id INT NULL,
                fecha_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_pres_bit_presupuesto (presupuesto_id, fecha_evento),
                KEY idx_atlas_pres_bit_anio_mes (anio, mes, fecha_evento),
                KEY idx_atlas_pres_bit_evento (evento)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private static function registrarBitacoraPresupuesto(Database $db, array $datos): void
    {
        $payload = $datos['payload_json'] ?? null;
        if (is_array($payload)) {
            $payload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $db->CRUD("
            INSERT INTO atlas_presupuesto_bitacora (
                presupuesto_id, anio, mes, evento, descripcion, sucursal_detalle_id, fk_sucursal,
                meta_creditos_anterior, meta_creditos_nueva, meta_cash_anterior, meta_cash_nueva,
                archivo_original, total_sucursales, payload_json, usuario_id, fecha_evento
            ) VALUES (
                :presupuesto_id, :anio, :mes, :evento, :descripcion, :sucursal_detalle_id, :fk_sucursal,
                :meta_creditos_anterior, :meta_creditos_nueva, :meta_cash_anterior, :meta_cash_nueva,
                :archivo_original, :total_sucursales, :payload_json, :usuario_id, NOW()
            )
        ", [
            'presupuesto_id' => $datos['presupuesto_id'] ?? null,
            'anio' => (int)($datos['anio'] ?? date('Y')),
            'mes' => (int)($datos['mes'] ?? date('n')),
            'evento' => (string)($datos['evento'] ?? 'evento'),
            'descripcion' => (string)($datos['descripcion'] ?? 'Movimiento de presupuesto.'),
            'sucursal_detalle_id' => $datos['sucursal_detalle_id'] ?? null,
            'fk_sucursal' => $datos['fk_sucursal'] ?? null,
            'meta_creditos_anterior' => $datos['meta_creditos_anterior'] ?? null,
            'meta_creditos_nueva' => $datos['meta_creditos_nueva'] ?? null,
            'meta_cash_anterior' => $datos['meta_cash_anterior'] ?? null,
            'meta_cash_nueva' => $datos['meta_cash_nueva'] ?? null,
            'archivo_original' => $datos['archivo_original'] ?? null,
            'total_sucursales' => $datos['total_sucursales'] ?? null,
            'payload_json' => $payload,
            'usuario_id' => $datos['usuario_id'] ?? null,
        ]);
    }

    public static function getPresupuestos(int $anio): array
    {
        try {
            $anio = $anio > 2000 ? $anio : (int)date('Y');
            $db = new Database();
            self::asegurarPresupuestosAtlas($db);

            $rows = $db->queryAll("
                SELECT
                    p.*,
                    COALESCE(bit.total_eventos, 0) AS total_eventos_bitacora,
                    COALESCE(bit.total_modificaciones, 0) AS total_modificaciones,
                    DATE_FORMAT(p.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(p.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt,
                    DATE_FORMAT(STR_TO_DATE(CONCAT(p.anio, '-', LPAD(p.mes, 2, '0'), '-01'), '%Y-%m-%d'), '%d/%m/%Y') AS fecha_inicio_operacion_fmt
                FROM atlas_presupuestos_mensuales p
                LEFT JOIN (
                    SELECT
                        presupuesto_id,
                        COUNT(*) AS total_eventos,
                        SUM(CASE WHEN evento = 'modificacion_sucursal' THEN 1 ELSE 0 END) AS total_modificaciones
                    FROM atlas_presupuesto_bitacora
                    GROUP BY presupuesto_id
                ) bit ON bit.presupuesto_id = p.id
                WHERE p.anio = :anio
                  AND p.activo = 1
                ORDER BY p.mes ASC
            ", ['anio' => $anio]);

            $porMes = [];
            foreach ($rows as $row) {
                $row['puede_eliminar'] = self::presupuestoMesEsFuturo((int)$row['anio'], (int)$row['mes']) ? 1 : 0;
                $row['total_sucursales'] = (int)($row['total_sucursales'] ?? 0);
                $row['total_creditos'] = (float)($row['total_creditos'] ?? 0);
                $row['total_cash'] = (float)($row['total_cash'] ?? 0);
                $porMes[(int)$row['mes']] = $row;
            }

            $calendario = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $calendario[] = [
                    'anio' => $anio,
                    'mes' => $mes,
                    'nombre_mes' => self::nombreMes($mes),
                    'presupuesto' => $porMes[$mes] ?? null,
                    'estado_mes' => self::presupuestoMesEsFuturo($anio, $mes) ? 'futuro' : 'vigente_o_historico',
                ];
            }

            return [
                'success' => true,
                'mensaje' => 'Presupuestos obtenidos.',
                'datos' => [
                    'anio' => $anio,
                    'historial' => array_values($porMes),
                    'calendario' => $calendario,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudieron cargar presupuestos.', 'error' => $e->getMessage(), 'datos' => ['historial' => [], 'calendario' => []]];
        }
    }

    public static function getPresupuestoDetalle(int $id): array
    {
        try {
            $db = new Database();
            self::asegurarPresupuestosAtlas($db);
            $presupuesto = $db->queryOne("
                SELECT
                    p.*,
                    DATE_FORMAT(p.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(p.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_presupuestos_mensuales p
                WHERE p.id = :id
                  AND p.activo = 1
                LIMIT 1
            ", ['id' => $id]);
            if (!$presupuesto) {
                return ['success' => false, 'mensaje' => 'No se encontro el presupuesto mensual.'];
            }

            $detalles = $db->queryAll("
                SELECT
                    d.*,
                    DATE_FORMAT(d.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_presupuesto_sucursal_detalle d
                WHERE d.presupuesto_id = :id
                  AND d.activo = 1
                ORDER BY d.sucursal ASC, d.fk_sucursal ASC
            ", ['id' => $id]);

            $presupuesto['puede_eliminar'] = self::presupuestoMesEsFuturo((int)$presupuesto['anio'], (int)$presupuesto['mes']) ? 1 : 0;

            return [
                'success' => true,
                'mensaje' => 'Detalle obtenido.',
                'datos' => [
                    'presupuesto' => $presupuesto,
                    'detalles' => $detalles,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar el detalle.', 'error' => $e->getMessage()];
        }
    }

    public static function getPresupuestoRanking(int $id, string $periodo = 'mes', int $semana = 1, string $orden = 'cash'): array
    {
        try {
            $db = new Database();
            self::asegurarPresupuestosAtlas($db);

            $presupuesto = $db->queryOne("
                SELECT id, anio, mes, nombre_mes
                FROM atlas_presupuestos_mensuales
                WHERE id = :id
                  AND activo = 1
                LIMIT 1
            ", ['id' => $id]);
            if (!$presupuesto) {
                return ['success' => false, 'mensaje' => 'No se encontro el presupuesto mensual.'];
            }

            $anio = (int)$presupuesto['anio'];
            $mes = (int)$presupuesto['mes'];
            $periodo = $periodo === 'semana' ? 'semana' : 'mes';
            $semana = max(1, min(5, $semana));
            $orden = in_array($orden, ['cash', 'avanzado', 'creditos'], true) ? $orden : 'cash';

            $inicioMes = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $anio, $mes));
            $finMes = $inicioMes->modify('first day of next month');
            $inicio = $inicioMes;
            $fin = $finMes;
            if ($periodo === 'semana') {
                $inicio = $inicioMes->modify('+' . (($semana - 1) * 7) . ' days');
                $fin = $inicio->modify('+7 days');
                if ($fin > $finMes) {
                    $fin = $finMes;
                }
            }

            $snapshotExiste = $db->queryOne("SHOW TABLES LIKE 'atlas_creditos_oferta_snapshot'");
            $fechaExpr = '';
            if ($snapshotExiste) {
                foreach (['fecha_salida_operativa', 'fecha_ultima_sync', 'updated_at', 'fecha_actualizacion'] as $columnaFecha) {
                    $col = $db->queryOne("SHOW COLUMNS FROM atlas_creditos_oferta_snapshot LIKE :columna", ['columna' => $columnaFecha]);
                    if ($col) {
                        $fechaExpr = "snap.`$columnaFecha`";
                        break;
                    }
                }
            }

            $rankingJoin = "
                LEFT JOIN (
                    SELECT NULL AS fk_sucursal, 0 AS creditos_vendidos, 0 AS cash_vendido
                ) ventas ON 1 = 0
            ";
            $params = ['id' => $id];
            if ($snapshotExiste) {
                $filtroFecha = '';
                if ($fechaExpr !== '') {
                    $filtroFecha = "AND $fechaExpr >= :inicio AND $fechaExpr < :fin";
                    $params['inicio'] = $inicio->format('Y-m-d H:i:s');
                    $params['fin'] = $fin->format('Y-m-d H:i:s');
                }
                $rankingJoin = "
                    LEFT JOIN (
                        SELECT
                            ac.fk_sucursal,
                            COUNT(DISTINCT c.id) AS creditos_vendidos,
                            COALESCE(SUM(COALESCE(snap.monto_financiar, c.cash_detenido, c.monto_credito, 0)), 0) AS cash_vendido
                        FROM atlas_asigna_sucursal_credito ac
                        INNER JOIN atlas_creditos c
                                ON c.id = ac.credito_id
                               AND c.activo = 1
                        INNER JOIN atlas_creditos_oferta_snapshot snap
                                ON snap.credito_id = c.id
                               AND snap.activo = 1
                        WHERE ac.activo = 1
                          AND (
                              snap.tipo_bucket_actual = 'terminal'
                              OR snap.bucket_actual = 'NO DETENIDO / TERMINAL O POST-OPERATIVO'
                          )
                          $filtroFecha
                        GROUP BY ac.fk_sucursal
                    ) ventas ON ventas.fk_sucursal = d.fk_sucursal
                ";
            }

            if ($orden === 'creditos') {
                $orderSql = "creditos_vendidos DESC, d.meta_creditos DESC, cash_vendido DESC";
            } elseif ($orden === 'avanzado') {
                $orderSql = "cash_vendido DESC, creditos_vendidos DESC, d.meta_cash DESC";
            } else {
                $orderSql = "d.meta_cash DESC, cash_vendido DESC, creditos_vendidos DESC";
            }

            $rows = $db->queryAll("
                SELECT
                    d.fk_sucursal,
                    d.sucursal,
                    d.distribuidor,
                    d.clasificacion,
                    d.meta_creditos,
                    d.meta_cash,
                    COALESCE(ventas.creditos_vendidos, 0) AS creditos_vendidos,
                    COALESCE(ventas.cash_vendido, 0) AS cash_vendido
                FROM atlas_presupuesto_sucursal_detalle d
                $rankingJoin
                WHERE d.presupuesto_id = :id
                  AND d.activo = 1
                ORDER BY $orderSql
            ", $params);

            $totalVendido = 0.0;
            $totalCreditos = 0;
            foreach ($rows as &$row) {
                $metaCash = (float)($row['meta_cash'] ?? 0);
                $cashAvanzado = (float)($row['cash_vendido'] ?? 0);
                $cashDetenido = max($metaCash - $cashAvanzado, 0);
                $row['cash_avanzado'] = $cashAvanzado;
                $row['cash_detenido'] = $cashDetenido;
                $row['porcentaje_avance'] = $metaCash > 0 ? round(($cashAvanzado / $metaCash) * 100, 2) : 0;
                $totalVendido += (float)($row['cash_vendido'] ?? 0);
                $totalCreditos += (int)($row['creditos_vendidos'] ?? 0);
            }
            unset($row);

            return [
                'success' => true,
                'mensaje' => 'Ranking obtenido.',
                'datos' => [
                    'presupuesto' => $presupuesto,
                    'periodo' => $periodo,
                    'semana' => $periodo === 'semana' ? $semana : null,
                    'inicio' => $inicio->format('Y-m-d H:i:s'),
                    'fin' => $fin->format('Y-m-d H:i:s'),
                    'orden' => $orden,
                    'fuente' => $snapshotExiste ? ($fechaExpr !== '' ? 'avance_local_fecha' : 'avance_local_sin_fecha') : 'presupuesto',
                    'vendido_real_disponible' => $totalVendido > 0 || $totalCreditos > 0,
                    'ranking' => $rows,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar el ranking.', 'error' => $e->getMessage()];
        }
    }

    public static function getPresupuestoBitacora(int $id = 0, int $anio = 0): array
    {
        try {
            $db = new Database();
            self::asegurarPresupuestosAtlas($db);
            $anio = $anio > 2000 ? $anio : (int)date('Y');
            $params = [];
            $where = "b.anio = :anio";
            $params['anio'] = $anio;
            $presupuesto = null;

            if ($id > 0) {
                $presupuesto = $db->queryOne("
                    SELECT
                        p.*,
                        DATE_FORMAT(p.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                        DATE_FORMAT(p.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt,
                        DATE_FORMAT(STR_TO_DATE(CONCAT(p.anio, '-', LPAD(p.mes, 2, '0'), '-01'), '%Y-%m-%d'), '%d/%m/%Y') AS fecha_inicio_operacion_fmt
                    FROM atlas_presupuestos_mensuales p
                    WHERE p.id = :id
                    LIMIT 1
                ", ['id' => $id]);
                if (!$presupuesto) {
                    return ['success' => false, 'mensaje' => 'No se encontro el presupuesto.'];
                }
                $where = "b.presupuesto_id = :id";
                $params = ['id' => $id];
                $anio = (int)$presupuesto['anio'];
            }

            $eventos = $db->queryAll("
                SELECT
                    b.*,
                    DATE_FORMAT(b.fecha_evento, '%d/%m/%Y %H:%i') AS fecha_evento_fmt,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)), ''), CONCAT('Usuario ', b.usuario_id), 'Sistema') AS usuario_nombre
                FROM atlas_presupuesto_bitacora b
                LEFT JOIN persona p ON p.id = b.usuario_id
                WHERE $where
                ORDER BY b.fecha_evento DESC, b.id DESC
                LIMIT 500
            ", $params) ?: [];

            if ($presupuesto && !$eventos) {
                $eventos[] = [
                    'id' => 0,
                    'presupuesto_id' => $id,
                    'anio' => (int)$presupuesto['anio'],
                    'mes' => (int)$presupuesto['mes'],
                    'evento' => 'carga_inicial',
                    'descripcion' => 'Presupuesto cargado antes de activar la bitacora operativa.',
                    'archivo_original' => $presupuesto['archivo_original'] ?? '',
                    'total_sucursales' => (int)($presupuesto['total_sucursales'] ?? 0),
                    'fecha_evento_fmt' => $presupuesto['fecha_alta_fmt'] ?? '',
                    'usuario_nombre' => 'Sistema',
                ];
            }

            $totalModificaciones = 0;
            $totalEliminaciones = 0;
            $ultimaCarga = null;
            $ultimaEliminacion = null;
            foreach ($eventos as $evento) {
                if (($evento['evento'] ?? '') === 'modificacion_sucursal') {
                    $totalModificaciones++;
                }
                if (($evento['evento'] ?? '') === 'eliminacion') {
                    $totalEliminaciones++;
                    $ultimaEliminacion = $ultimaEliminacion ?: ($evento['fecha_evento_fmt'] ?? null);
                }
                if (in_array(($evento['evento'] ?? ''), ['carga', 'recarga', 'carga_inicial'], true)) {
                    $ultimaCarga = $ultimaCarga ?: ($evento['fecha_evento_fmt'] ?? null);
                }
            }

            return [
                'success' => true,
                'mensaje' => 'Bitacora obtenida.',
                'datos' => [
                    'anio' => $anio,
                    'presupuesto' => $presupuesto,
                    'resumen' => [
                        'total_eventos' => count($eventos),
                        'total_modificaciones' => $totalModificaciones,
                        'total_eliminaciones' => $totalEliminaciones,
                        'ultima_carga' => $ultimaCarga,
                        'ultima_eliminacion' => $ultimaEliminacion,
                        'inicio_operacion' => $presupuesto['fecha_inicio_operacion_fmt'] ?? null,
                    ],
                    'eventos' => $eventos,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar la bitacora de presupuesto.', 'error' => $e->getMessage()];
        }
    }

    private static function asegurarRutasGestoresAtlas(Database $db): void
    {
        self::asegurarColumna($db, 'atlas_rutas_gestores', 'presupuesto_id', "BIGINT UNSIGNED NULL");
        self::asegurarColumna($db, 'atlas_rutas_gestores', 'criterio_prioridad', "VARCHAR(40) NULL");
        self::asegurarColumna($db, 'atlas_ruta_sucursales', 'prioridad_visita', "VARCHAR(20) NULL");
        self::asegurarColumna($db, 'atlas_ruta_sucursales', 'criterio_prioridad_visita', "VARCHAR(40) NULL");
        self::asegurarColumna($db, 'atlas_ruta_sucursales', 'fecha_inicio_visita', "DATE NULL");
        self::asegurarColumna($db, 'atlas_ruta_sucursales', 'fecha_fin_visita', "DATE NULL");
        self::asegurarColumna($db, 'atlas_ruta_sucursales', 'hora_llegada', "TIME NULL");
        self::asegurarColumna($db, 'atlas_ruta_sucursales', 'estancia_valor', "INT NULL");
        self::asegurarColumna($db, 'atlas_ruta_sucursales', 'estancia_unidad', "VARCHAR(20) NULL");
        self::asegurarColumna($db, 'atlas_ruta_sucursales', 'hora_salida_sugerida', "TIME NULL");
    }

    private static function rutaSucursalTieneGestion(Database $db, int $rutaSucursalId): bool
    {
        if ($rutaSucursalId <= 0) {
            return false;
        }
        $row = $db->queryOne("
            SELECT COUNT(g.id) AS total
            FROM atlas_ruta_sucursales rs
            INNER JOIN atlas_rutas_gestores r ON r.id = rs.ruta_id AND r.activo = 1
            INNER JOIN atlas_gestiones_credito g
                    ON g.fk_sucursal = rs.fk_sucursal
                   AND DATE(g.fecha_gestion) BETWEEN COALESCE(r.fecha_inicio, r.fecha_ruta) AND COALESCE(r.fecha_fin, r.fecha_ruta, r.fecha_inicio)
            WHERE rs.id = :id
              AND rs.activo = 1
        ", ['id' => $rutaSucursalId]);

        return (int)($row['total'] ?? 0) > 0;
    }

    private static function rutaGestorTieneGestiones(Database $db, int $rutaId): bool
    {
        if ($rutaId <= 0) {
            return false;
        }
        $row = $db->queryOne("
            SELECT COUNT(g.id) AS total
            FROM atlas_ruta_sucursales rs
            INNER JOIN atlas_rutas_gestores r ON r.id = rs.ruta_id AND r.activo = 1
            INNER JOIN atlas_gestiones_credito g
                    ON g.fk_sucursal = rs.fk_sucursal
                   AND DATE(g.fecha_gestion) BETWEEN COALESCE(r.fecha_inicio, r.fecha_ruta) AND COALESCE(r.fecha_fin, r.fecha_ruta, r.fecha_inicio)
            WHERE rs.ruta_id = :ruta
              AND rs.activo = 1
        ", ['ruta' => $rutaId]);

        return (int)($row['total'] ?? 0) > 0;
    }

    public static function getRutasGestores(): array
    {
        try {
            $db = new Database();
            self::asegurarRutasGestoresAtlas($db);
            $rows = $db->queryAll("
                SELECT
                    r.*,
                    DATE_FORMAT(COALESCE(r.fecha_inicio, r.fecha_ruta), '%d/%m/%Y') AS fecha_inicio_fmt,
                    DATE_FORMAT(COALESCE(r.fecha_fin, r.fecha_ruta), '%d/%m/%Y') AS fecha_fin_fmt,
                    p.nombre_mes AS presupuesto_mes,
                    p.mes AS presupuesto_mes_num,
                    p.anio AS presupuesto_anio,
                    COALESCE(suc.total_sucursales, 0) AS total_sucursales,
                    COALESCE(suc.sucursales_ruta, '') AS sucursales_ruta,
                    COALESCE(cred.total_creditos, 0) AS total_creditos,
                    COALESCE(cred.cash_detenido_operativo, 0) AS cash_detenido_operativo,
                    COALESCE(meta.meta_creditos, 0) AS meta_creditos,
                    COALESCE(meta.meta_cash, 0) AS meta_cash
                FROM atlas_rutas_gestores r
                LEFT JOIN atlas_presupuestos_mensuales p ON p.id = r.presupuesto_id
                LEFT JOIN (
                    SELECT rs.ruta_id, COUNT(*) AS total_sucursales, GROUP_CONCAT(cs.sucursal ORDER BY rs.orden_visita ASC SEPARATOR ', ') AS sucursales_ruta
                    FROM atlas_ruta_sucursales rs
                    LEFT JOIN atlas_catalogo_sucursales cs ON cs.fk_sucursal = rs.fk_sucursal
                    WHERE rs.activo = 1
                    GROUP BY rs.ruta_id
                ) suc ON suc.ruta_id = r.id
                LEFT JOIN (
                    SELECT rs.ruta_id, COUNT(DISTINCT ac.credito_id) AS total_creditos, SUM(COALESCE(snap.monto_financiar, c.cash_detenido, c.monto_credito, 0)) AS cash_detenido_operativo
                    FROM atlas_ruta_sucursales rs
                    INNER JOIN atlas_asigna_sucursal_credito ac ON ac.fk_sucursal = rs.fk_sucursal AND ac.activo = 1
                    INNER JOIN atlas_creditos c ON c.id = ac.credito_id AND c.activo = 1
                    LEFT JOIN atlas_creditos_oferta_snapshot snap ON snap.credito_id = c.id AND snap.activo = 1
                    WHERE rs.activo = 1
                      AND (snap.es_pendiente_operativo = 1 OR snap.tipo_bucket_actual = 'detenido' OR snap.bucket_actual LIKE 'DETENIDO:%')
                    GROUP BY rs.ruta_id
                ) cred ON cred.ruta_id = r.id
                LEFT JOIN (
                    SELECT rs.ruta_id,
                           SUM(COALESCE(pdet.meta_creditos, 0)) AS meta_creditos,
                           SUM(COALESCE(pdet.meta_cash, 0)) AS meta_cash
                    FROM atlas_ruta_sucursales rs
                    INNER JOIN atlas_rutas_gestores rg ON rg.id = rs.ruta_id AND rg.activo = 1
                    LEFT JOIN atlas_presupuesto_sucursal_detalle pdet
                           ON pdet.presupuesto_id = rg.presupuesto_id
                          AND pdet.fk_sucursal = rs.fk_sucursal
                          AND pdet.activo = 1
                    WHERE rs.activo = 1
                    GROUP BY rs.ruta_id
                ) meta ON meta.ruta_id = r.id
                WHERE r.activo = 1
                ORDER BY COALESCE(r.fecha_inicio, r.fecha_ruta) DESC, r.id DESC
            ");
            return ['success' => true, 'datos' => $rows];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudieron obtener rutas.', 'error' => $e->getMessage(), 'datos' => []];
        }
    }

    private static function contextoRutasAtlas(Database $db, int $usuarioId): array
    {
        self::asegurarPermisosSucursalesAtlas($db);
        self::asegurarAccesosAtlas($db);

        $modulos = array_map('intval', (array)($_SESSION['modulos'] ?? []));
        $usuario = null;
        if ($usuarioId > 0) {
            $usuario = $db->queryOne("
                SELECT persona_id, numero_empleado, nombre, puesto, departamento, area, direccion, rol_atlas
                FROM atlas_acceso_usuarios
                WHERE persona_id = :id OR id = :id
                ORDER BY CASE WHEN persona_id = :id THEN 0 WHEN id = :id THEN 1 ELSE 2 END
                LIMIT 1
            ", ['id' => $usuarioId]);
            if (!$usuario) {
                $usuario = $db->queryOne("
                    SELECT
                        id AS persona_id,
                        numero_empleado,
                        TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre,
                        puesto,
                        departamento,
                        area,
                        direccion,
                        NULL AS rol_atlas
                    FROM persona
                    WHERE id = :id
                    LIMIT 1
                ", ['id' => $usuarioId]);
            }
        }

        $puesto = strtoupper(trim((string)($usuario['puesto'] ?? '')));
        $departamento = strtoupper(trim((string)($usuario['departamento'] ?? '')));
        $personaSesionId = self::nullableInt($usuario['persona_id'] ?? null) ?? $usuarioId;
        $rolRutas = self::rolComercialAtlas($departamento, $puesto);
        $esGestor = (bool)preg_match('/\b(GESTOR|ASESOR|EJECUTIVO)\b/u', $puesto);
        $esAdmin = in_array(1, $modulos, true) || in_array(4, $modulos, true) || in_array(137, $modulos, true);
        $tienePermisoNiveles = in_array(self::MODULO_ATLAS_RUTAS_COMBO_GESTOR_NIVELES, $modulos, true);

        return [
            'usuario_id' => $personaSesionId,
            'nombre' => trim((string)($usuario['nombre'] ?? '')),
            'puesto' => (string)($usuario['puesto'] ?? ''),
            'departamento' => (string)($usuario['departamento'] ?? ''),
            'area' => (string)($usuario['area'] ?? ''),
            'direccion' => (string)($usuario['direccion'] ?? ''),
            'rol_atlas' => (string)($usuario['rol_atlas'] ?? ''),
            'rol_rutas' => $rolRutas,
            'es_gestor' => $esGestor,
            'combo_gestor_completo' => ($esAdmin || $tienePermisoNiveles) && !$esGestor,
            'modulo_combo_gestor_niveles' => self::MODULO_ATLAS_RUTAS_COMBO_GESTOR_NIVELES,
        ];
    }

    private static function rolComercialAtlas(string $departamento, string $puesto): string
    {
        if ($departamento === 'DIVISIONAL' && $puesto === 'GERENTE') {
            return 'divisional';
        }
        if ($departamento === 'REGIONAL' && $puesto === 'GERENTE') {
            return 'regional';
        }
        if ($departamento === 'VENTAS' && $puesto === 'SUPERVISOR') {
            return 'supervisor';
        }
        if ($departamento === 'VENTAS' && $puesto === 'ASESOR') {
            return 'asesor';
        }
        if (str_contains($puesto, 'DIVISIONAL') || str_contains($puesto, 'SUBDIRECTOR') || str_contains($puesto, 'DIRECTOR')) {
            return 'divisional';
        }
        if (str_contains($puesto, 'REGIONAL')) {
            return 'regional';
        }
        if (str_contains($puesto, 'SUPERVISOR') || str_contains($puesto, 'COORDINADOR')) {
            return 'supervisor';
        }
        return 'asesor';
    }

    private static function filtrarSucursalesPorContextoRutas(array $sucursales, array $contexto): array
    {
        if (!empty($contexto['combo_gestor_completo'])) {
            return $sucursales;
        }

        $usuarioId = self::nullableInt($contexto['usuario_id'] ?? null);
        if ($usuarioId === null) {
            return [];
        }

        $rol = (string)($contexto['rol_rutas'] ?? '');
        $campo = match ($rol) {
            'divisional' => 'divisional_id',
            'regional' => 'regional_id',
            'supervisor' => 'supervisor_id',
            default => 'asesor_id',
        };

        return array_values(array_filter($sucursales, static function ($sucursal) use ($campo, $usuarioId) {
            return (int)($sucursal[$campo] ?? 0) === $usuarioId;
        }));
    }

    public static function getRutasGestoresCatalogos(int $usuarioId = 0): array
    {
        try {
            $db = new Database();
            $gestores = $db->queryAll("
                SELECT id, numero_empleado, TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre, correo
                FROM persona
                WHERE estatus = 'Activo'
                ORDER BY nombre
                LIMIT 800
            ");
            $sucursales = $db->queryAll("
                SELECT
                    s.fk_sucursal,
                    s.sucursal,
                    COALESCE(NULLIF(s.direccion_sucursal, ''), TRIM(CONCAT_WS(', ', NULLIF(s.calle, ''), NULLIF(s.numero_exterior, ''), NULLIF(s.colonia, ''), NULLIF(s.municipio, ''), NULLIF(s.estado, '')))) AS direccion,
                    s.latitud,
                    s.longitud,
                    s.clasificacion_id,
                    COALESCE(s.divisional_persona_id, s.divisional_id) AS divisional_id,
                    s.division_id,
                    COALESCE(s.regional_persona_id, s.regional_id) AS regional_id,
                    COALESCE(s.supervisor_persona_id, s.supervisor_id) AS supervisor_id,
                    COALESCE(s.asesor_persona_id, s.asesor_id) AS asesor_id,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', pdvl.nombres, pdvl.segundo_nombre, pdvl.apellidop, pdvl.apellidom)), ''), dvl.nombre) AS divisional_nombre,
                    divi.nombre AS division_nombre,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', preg.nombres, preg.segundo_nombre, preg.apellidop, preg.apellidom)), ''), reg.nombre) AS regional_nombre,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', psup.nombres, psup.segundo_nombre, psup.apellidop, psup.apellidom)), ''), sup.nombre) AS supervisor_nombre,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', pase.nombres, pase.segundo_nombre, pase.apellidop, pase.apellidom)), ''), ase.nombre) AS asesor_nombre,
                    COALESCE(cls.nombre, '') AS clasificacion_nombre,
                    COALESCE(NULLIF(cls.icon_font, ''), 'fa-solid fa-location-dot') AS clasificacion_icon_font,
                    COALESCE(NULLIF(cls.color_hex, ''), '#2563EB') AS clasificacion_color_hex
                FROM atlas_catalogo_sucursales s
                LEFT JOIN atlas_catalogo_divisionales dvl ON dvl.id = s.divisional_id
                LEFT JOIN persona pdvl ON pdvl.id = s.divisional_persona_id
                LEFT JOIN atlas_catalogo_divisiones divi ON divi.id = s.division_id
                LEFT JOIN atlas_catalogo_regionales reg ON reg.id = s.regional_id
                LEFT JOIN persona preg ON preg.id = s.regional_persona_id
                LEFT JOIN atlas_catalogo_supervisores sup ON sup.id = s.supervisor_id
                LEFT JOIN persona psup ON psup.id = s.supervisor_persona_id
                LEFT JOIN atlas_catalogo_asesores ase ON ase.id = s.asesor_id
                LEFT JOIN persona pase ON pase.id = s.asesor_persona_id
                LEFT JOIN atlas_catalogo_clasificaciones cls ON cls.id = s.clasificacion_id
                WHERE s.activo = 1
                ORDER BY s.sucursal
            ");
            $contexto = self::contextoRutasAtlas($db, $usuarioId);
            $sucursales = self::filtrarSucursalesPorContextoRutas($sucursales, $contexto);
            $horarioOperativo = self::getConfiguracionHorarioOperativoRutas($db);

            return ['success' => true, 'datos' => ['gestores' => $gestores, 'sucursales' => $sucursales, 'creditos' => [], 'contexto' => $contexto, 'horario_operativo' => $horarioOperativo]];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudieron obtener catalogos de rutas.', 'error' => $e->getMessage(), 'datos' => ['gestores' => [], 'sucursales' => [], 'creditos' => [], 'horario_operativo' => ['inicio' => '08:00', 'fin' => '20:00', 'inicio_minutos' => 480, 'fin_minutos' => 1200, 'duracion_minutos' => 720]]];
        }
    }

    private static function atlasBucketOfertaMexico(?string $etapa): array
    {
        $etapaLimpia = strtoupper(trim((string)$etapa));
        if ($etapaLimpia === '') {
            return ['bucket' => 'DATOS SIN ETAPA / DEPURAR', 'tipo' => 'sin_etapa', 'pendiente' => 0, 'requiere' => 0, 'motivo' => 'OFERTA_SIN_ETAPA'];
        }

        $map = [
            'DETENIDO: ENGANCHE / PRIMER PAGO' => ['SOLICITUD', 'SOLCITUD', 'ENGANCHE DIFERIDO'],
            'DETENIDO: FIRMA / REVISION FIRMA' => ['POR FIRMAR', 'FIRMADO'],
            'DETENIDO: FACTURA' => ['FACTURA', 'PENDIENTE DE FACTURA'],
            'DETENIDO: ENTREGA' => ['POR ENTREGAR'],
            'DETENIDO: VALIDACION / RIESGO' => ['ANALISIS', 'REVISION', 'VTT', 'PREVENCION DE FRAUDE'],
            'DETENIDO: DISPERSION' => ['POR DISPERSAR', 'DISPERSADO'],
            'DETENIDO: REEMBOLSO / DEVOLUCION' => ['PENDIENTE DE REEMBOLSO', 'DEVOLUCION'],
        ];
        foreach ($map as $bucket => $etapas) {
            if (in_array($etapaLimpia, $etapas, true)) {
                return ['bucket' => $bucket, 'tipo' => 'detenido', 'pendiente' => 1, 'requiere' => 1, 'motivo' => null];
            }
        }

        $terminales = ['CANCELADO', 'CANCELADA', 'ELIMINADO', 'ELIMINADA', 'ENTREGADA', 'ENTREGADO', 'S2CREDIT'];
        if (in_array($etapaLimpia, $terminales, true)) {
            return ['bucket' => 'NO DETENIDO / TERMINAL O POST-OPERATIVO', 'tipo' => 'terminal', 'pendiente' => 0, 'requiere' => 0, 'motivo' => 'ETAPA_TERMINAL_' . preg_replace('/[^A-Z0-9]+/', '_', $etapaLimpia)];
        }

        return ['bucket' => 'REVISAR ETAPA: ' . $etapaLimpia, 'tipo' => 'revisar_etapa', 'pendiente' => 0, 'requiere' => 0, 'motivo' => 'ETAPA_NO_MAPEADA'];
    }

    private static function upsertDepuracionAtlas(Database $db, array $data, bool $dryRun): void
    {
        if ($dryRun) return;
        $db->CRUD("
            INSERT INTO atlas_bandeja_entrada_depuracion
            (tipo, credito_id, id_solicitud, oferta_id, fk_sucursal_origen, sucursal_origen, etapa_origen, bucket_operativo, motivo, payload_json, estatus, fecha_detectado, activo, fecha_actualizacion)
            VALUES
            (:tipo, :credito_id, :id_solicitud, :oferta_id, :fk_sucursal_origen, :sucursal_origen, :etapa_origen, :bucket_operativo, :motivo, :payload_json, 'pendiente', NOW(), 1, NOW())
            ON DUPLICATE KEY UPDATE
                credito_id = VALUES(credito_id),
                etapa_origen = VALUES(etapa_origen),
                bucket_operativo = VALUES(bucket_operativo),
                motivo = VALUES(motivo),
                payload_json = VALUES(payload_json),
                estatus = IF(estatus = 'resuelto', 'pendiente', estatus),
                activo = 1,
                fecha_actualizacion = NOW()
        ", [
            'tipo' => (string)($data['tipo'] ?? 'depuracion'),
            'credito_id' => self::nullableInt($data['credito_id'] ?? null),
            'id_solicitud' => (string)($data['id_solicitud'] ?? ''),
            'oferta_id' => self::nullableInt($data['oferta_id'] ?? null),
            'fk_sucursal_origen' => self::nullableInt($data['fk_sucursal_origen'] ?? null),
            'sucursal_origen' => self::nullableStr($data['sucursal_origen'] ?? null),
            'etapa_origen' => self::nullableStr($data['etapa_origen'] ?? null),
            'bucket_operativo' => self::nullableStr($data['bucket_operativo'] ?? null),
            'motivo' => (string)($data['motivo'] ?? 'DEPURACION'),
            'payload_json' => json_encode($data['payload'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public static function sincronizarCreditosOfertaMexico(array $input = []): array
    {
        $dryRun = (int)($input['dry_run'] ?? 1) === 1;
        $limit = max(1, min(5000, (int)($input['limit'] ?? $input['lote'] ?? 500)));
        $afterId = self::nullableInt($input['after_id'] ?? null);
        $resetCursor = (int)($input['reset_cursor'] ?? 0) === 1;
        $stats = [
            'dry_run' => $dryRun,
            'asignaciones_leidas' => 0,
            'cruzadas_con_oferta' => 0,
            'sin_llave_oferta' => 0,
            'sin_oferta' => 0,
            'ofertas_leidas_maxi' => 0,
            'creditos_upsert' => 0,
            'asignaciones_upsert' => 0,
            'sin_sucursal_maxi' => 0,
            'sucursal_no_mapeada' => 0,
            'max_oferta_id_procesado' => 0,
            'importadas_nuevas' => 0,
            'cambios_etapa' => 0,
            'cambios_monto' => 0,
            'terminalizadas' => 0,
            'reactivadas' => 0,
            'pendientes_operativas' => 0,
            'requieren_revision_etapa' => 0,
            'sin_etapa_depurar' => 0,
        ];

        try {
            $db = new Database();
            $maxi = new \core\DatabaseMaxiProd();
            $control = $db->queryOne("SELECT * FROM atlas_sync_oferta_mexico_control WHERE proceso = 'oferta_mexico' AND activo = 1 LIMIT 1");
            if (!$control && !$dryRun) {
                $db->CRUD("
                    INSERT INTO atlas_sync_oferta_mexico_control (proceso, last_offer_id, last_run_at, activo, fecha_alta, fecha_actualizacion)
                    VALUES ('oferta_mexico', 0, NOW(), 1, NOW(), NOW())
                ");
                $control = $db->queryOne("SELECT * FROM atlas_sync_oferta_mexico_control WHERE proceso = 'oferta_mexico' AND activo = 1 LIMIT 1");
            }
            $cursor = $resetCursor ? 0 : (int)($afterId ?? ($control['last_offer_id'] ?? 0));

            try {
                $ofertas = $maxi->queryAll("
                    SELECT
                        b.id_oferta,
                        b.id_oferta AS id_solicitud,
                        b.pk_sucursal,
                        b.sucursal,
                        b.distribuidor,
                        b.etapa,
                        b.monto_financiar
                    FROM vw_ofertas_base b
                    WHERE b.id_oferta > :cursor
                      AND b.estatus = 1
                    ORDER BY b.id_oferta ASC
                    LIMIT {$limit}
                ", ['cursor' => $cursor]);
                $stats['fuente_maxi'] = 'vw_ofertas_base';
            } catch (\Throwable $viewError) {
                $ofertas = $maxi->queryAll("
                    SELECT
                        o.id_oferta,
                        o.id_oferta AS id_solicitud,
                        u.fk_sucursal AS pk_sucursal,
                        s.nombre AS sucursal,
                        d.nombre AS distribuidor,
                        o.etapa,
                        o.monto_financiar
                    FROM oferta o
                    LEFT JOIN usuario u ON u.pk_usuario = o.fk_usuario_creacion
                    LEFT JOIN sucursal s ON s.pk_sucursal = u.fk_sucursal
                    LEFT JOIN distribuidor d ON d.pk_distribuidor = s.fk_distribuidor
                    WHERE o.id_oferta > :cursor
                      AND o.estatus = 1
                    ORDER BY o.id_oferta ASC
                    LIMIT {$limit}
                ", ['cursor' => $cursor]);
                $stats['fuente_maxi'] = 'oferta_usuario_sucursal';
                $stats['fallback_motivo'] = 'vw_ofertas_base_no_consultable';
            }
            $stats['ofertas_leidas_maxi'] = count($ofertas);
            $stats['asignaciones_leidas'] = count($ofertas);
            $stats['cruzadas_con_oferta'] = count($ofertas);
            if (!$ofertas) {
                return ['success' => true, 'mensaje' => 'No hay ofertas nuevas para sincronizar.', 'datos' => $stats];
            }

            $sucursales = $db->queryAll("SELECT fk_sucursal FROM atlas_catalogo_sucursales WHERE activo = 1");
            $sucursalesMap = array_fill_keys(array_map(static fn($r) => (string)$r['fk_sucursal'], $sucursales), true);
            $ids = array_map(static fn($r) => (string)$r['id_oferta'], $ofertas);
            $existentes = [];
            if ($ids) {
                $placeholders = [];
                $params = [];
                foreach ($ids as $i => $idOferta) {
                    $key = 'id' . $i;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $idOferta;
                }
                $rowsExistentes = $db->queryAll("SELECT id, id_solicitud FROM atlas_creditos WHERE id_solicitud IN (" . implode(',', $placeholders) . ")", $params);
                foreach ($rowsExistentes as $row) {
                    $existentes[(string)$row['id_solicitud']] = (int)$row['id'];
                }
            }

            $ultimoId = $cursor;
            foreach ($ofertas as $oferta) {
                $ofertaId = (int)$oferta['id_oferta'];
                $ultimoId = max($ultimoId, $ofertaId);
                $stats['max_oferta_id_procesado'] = $ultimoId;
                $idSolicitud = (string)$ofertaId;
                $fkSucursal = self::nullableInt($oferta['pk_sucursal'] ?? null);
                $bucket = self::atlasBucketOfertaMexico($oferta['etapa'] ?? null);
                $montoRaw = $oferta['monto_financiar'] ?? null;
                $monto = ($montoRaw === null || $montoRaw === '') ? 0.0 : (float)$montoRaw;

                if (!$fkSucursal) {
                    $stats['sin_sucursal_maxi']++;
                    self::upsertDepuracionAtlas($db, [
                        'tipo' => 'sin_sucursal',
                        'id_solicitud' => $idSolicitud,
                        'oferta_id' => $ofertaId,
                        'etapa_origen' => $oferta['etapa'] ?? null,
                        'bucket_operativo' => $bucket['bucket'],
                        'motivo' => 'SIN_SUCURSAL_MAXI',
                        'payload' => $oferta,
                    ], $dryRun);
                    continue;
                }
                if (!isset($sucursalesMap[(string)$fkSucursal])) {
                    $stats['sucursal_no_mapeada']++;
                    if (!$dryRun) {
                        $db->CRUD("
                            INSERT INTO atlas_sucursales_pendientes
                            (fuente, fk_sucursal_origen, sucursal_origen, distribuidor_origen, oferta_id, id_solicitud, motivo, estatus, payload_json, fecha_detectado, activo, fecha_actualizacion)
                            VALUES
                            ('maxi', :fk_sucursal, :sucursal, :distribuidor, :oferta_id, :id_solicitud, 'SUCURSAL_NO_EXISTE_EN_SPARTA', 'pendiente', :payload_json, NOW(), 1, NOW())
                            ON DUPLICATE KEY UPDATE
                                sucursal_origen = VALUES(sucursal_origen),
                                distribuidor_origen = VALUES(distribuidor_origen),
                                payload_json = VALUES(payload_json),
                                activo = 1,
                                fecha_actualizacion = NOW()
                        ", [
                            'fk_sucursal' => $fkSucursal,
                            'sucursal' => self::nullableStr($oferta['sucursal'] ?? null),
                            'distribuidor' => self::nullableStr($oferta['distribuidor'] ?? null),
                            'oferta_id' => $ofertaId,
                            'id_solicitud' => $idSolicitud,
                            'payload_json' => json_encode($oferta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                    }
                    self::upsertDepuracionAtlas($db, [
                        'tipo' => 'sucursal_no_mapeada',
                        'id_solicitud' => $idSolicitud,
                        'oferta_id' => $ofertaId,
                        'fk_sucursal_origen' => $fkSucursal,
                        'sucursal_origen' => $oferta['sucursal'] ?? null,
                        'etapa_origen' => $oferta['etapa'] ?? null,
                        'bucket_operativo' => $bucket['bucket'],
                        'motivo' => 'SUCURSAL_NO_EXISTE_EN_SPARTA',
                        'payload' => $oferta,
                    ], $dryRun);
                    continue;
                }

                if ($bucket['tipo'] === 'sin_etapa') $stats['sin_etapa_depurar']++;
                if ($bucket['tipo'] === 'revisar_etapa') $stats['requieren_revision_etapa']++;
                if ($bucket['pendiente']) $stats['pendientes_operativas']++;

                $creditoId = $existentes[$idSolicitud] ?? 0;
                $snapshotPrevio = $creditoId ? $db->queryOne("SELECT * FROM atlas_creditos_oferta_snapshot WHERE credito_id = :id LIMIT 1", ['id' => $creditoId]) : null;
                if (!$dryRun) {
                    $db->CRUD("
                        INSERT INTO atlas_creditos
                        (id_solicitud, fk_sucursal, usuario, monto_credito, cash_detenido, estatus_actual, prioridad, dias_en_estatus, activo, fecha_alta, fecha_actualizacion)
                        VALUES
                        (:id_solicitud, :fk_sucursal, :usuario, :monto, :cash, :estatus, :prioridad, 0, 1, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE
                            fk_sucursal = VALUES(fk_sucursal),
                            monto_credito = VALUES(monto_credito),
                            cash_detenido = VALUES(cash_detenido),
                            estatus_actual = VALUES(estatus_actual),
                            activo = 1,
                            fecha_actualizacion = NOW()
                    ", [
                        'id_solicitud' => $idSolicitud,
                        'fk_sucursal' => $fkSucursal,
                        'usuario' => self::nullableStr($oferta['sucursal'] ?? null),
                        'monto' => $monto,
                        'cash' => $bucket['pendiente'] ? $monto : 0,
                        'estatus' => $bucket['bucket'],
                        'prioridad' => $bucket['tipo'] === 'detenido' ? 'media' : 'baja',
                    ]);
                    if (!$creditoId) {
                        $creditoId = (int)$db->lastInsertId();
                        if (!$creditoId) {
                            $row = $db->queryOne("SELECT id FROM atlas_creditos WHERE id_solicitud = :id LIMIT 1", ['id' => $idSolicitud]);
                            $creditoId = (int)($row['id'] ?? 0);
                        }
                    }
                    $existentes[$idSolicitud] = $creditoId;
                } elseif (!$creditoId) {
                    $creditoId = -$ofertaId;
                }
                $stats['creditos_upsert']++;
                if (!$snapshotPrevio) $stats['importadas_nuevas']++;

                if (!$dryRun && $creditoId > 0) {
                    $db->CRUD("
                        INSERT INTO atlas_asigna_sucursal_credito
                        (credito_id, id_solicitud, fk_sucursal, tipo_asignacion, es_principal, activo, fecha_alta, fecha_actualizacion)
                        VALUES
                        (:credito_id, :id_solicitud, :fk_sucursal, 'maxi_sync', 1, 1, NOW(), NOW())
                        ON DUPLICATE KEY UPDATE
                            fk_sucursal = VALUES(fk_sucursal),
                            tipo_asignacion = VALUES(tipo_asignacion),
                            es_principal = 1,
                            activo = 1,
                            fecha_actualizacion = NOW()
                    ", ['credito_id' => $creditoId, 'id_solicitud' => $idSolicitud, 'fk_sucursal' => $fkSucursal]);
                }
                $stats['asignaciones_upsert']++;

                $evento = !$snapshotPrevio ? 'importado' : null;
                if ($snapshotPrevio) {
                    if ((string)($snapshotPrevio['etapa_actual'] ?? '') !== (string)($oferta['etapa'] ?? '') || (string)($snapshotPrevio['bucket_actual'] ?? '') !== $bucket['bucket']) {
                        $stats['cambios_etapa']++;
                        $evento = $bucket['tipo'] === 'terminal' ? 'terminalizado' : 'cambio_etapa';
                    }
                    if ((float)($snapshotPrevio['monto_financiar'] ?? 0) !== (float)$monto) {
                        $stats['cambios_monto']++;
                        $evento = $evento ?: 'cambio_monto';
                    }
                    if (($snapshotPrevio['tipo_bucket_actual'] ?? '') !== 'terminal' && $bucket['tipo'] === 'terminal') {
                        $stats['terminalizadas']++;
                        $evento = 'terminalizado';
                    }
                    if (($snapshotPrevio['tipo_bucket_actual'] ?? '') === 'terminal' && $bucket['tipo'] === 'detenido') {
                        $stats['reactivadas']++;
                        $evento = 'reactivado';
                    }
                } elseif ($bucket['tipo'] === 'terminal') {
                    $stats['terminalizadas']++;
                }

                if (!$dryRun && $creditoId > 0) {
                    $db->CRUD("
                        INSERT INTO atlas_creditos_oferta_snapshot
                        (credito_id, id_solicitud, oferta_id, fk_sucursal, etapa_origen, etapa_actual, bucket_origen, bucket_actual, tipo_bucket_actual, monto_financiar, es_pendiente_operativo, requiere_gestion, fecha_primer_snapshot, fecha_ultima_sync, activo, motivo_salida_operativa, fecha_salida_operativa, motivo_bloqueo_operativo, estatus_revision_operativa)
                        VALUES
                        (:credito_id, :id_solicitud, :oferta_id, :fk_sucursal, :etapa, :etapa, :bucket, :bucket, :tipo_bucket, :monto, :pendiente, :requiere, NOW(), NOW(), 1, :motivo_salida, :fecha_salida, :motivo_bloqueo, :estatus_revision)
                        ON DUPLICATE KEY UPDATE
                            fk_sucursal = VALUES(fk_sucursal),
                            etapa_actual = VALUES(etapa_actual),
                            bucket_actual = VALUES(bucket_actual),
                            tipo_bucket_actual = VALUES(tipo_bucket_actual),
                            monto_financiar = VALUES(monto_financiar),
                            es_pendiente_operativo = VALUES(es_pendiente_operativo),
                            requiere_gestion = VALUES(requiere_gestion),
                            fecha_ultima_sync = NOW(),
                            activo = 1,
                            motivo_salida_operativa = VALUES(motivo_salida_operativa),
                            fecha_salida_operativa = IF(VALUES(motivo_salida_operativa) IS NOT NULL AND fecha_salida_operativa IS NULL, NOW(), fecha_salida_operativa),
                            motivo_bloqueo_operativo = VALUES(motivo_bloqueo_operativo),
                            estatus_revision_operativa = VALUES(estatus_revision_operativa)
                    ", [
                        'credito_id' => $creditoId,
                        'id_solicitud' => $idSolicitud,
                        'oferta_id' => $ofertaId,
                        'fk_sucursal' => $fkSucursal,
                        'etapa' => self::nullableStr($oferta['etapa'] ?? null),
                        'bucket' => $bucket['bucket'],
                        'tipo_bucket' => $bucket['tipo'],
                        'monto' => $monto,
                        'pendiente' => $bucket['pendiente'],
                        'requiere' => $bucket['requiere'],
                        'motivo_salida' => $bucket['tipo'] === 'terminal' ? $bucket['motivo'] : null,
                        'fecha_salida' => $bucket['tipo'] === 'terminal' ? date('Y-m-d H:i:s') : null,
                        'motivo_bloqueo' => in_array($bucket['tipo'], ['sin_etapa', 'revisar_etapa'], true) ? $bucket['motivo'] : null,
                        'estatus_revision' => $bucket['tipo'] === 'detenido' ? 'operativo' : $bucket['tipo'],
                    ]);
                    if ($evento) {
                        $db->CRUD("
                            INSERT INTO atlas_credito_etapa_historial
                            (credito_id, id_solicitud, oferta_id, fk_sucursal, etapa_anterior, etapa_nueva, bucket_anterior, bucket_nuevo, tipo_bucket_anterior, tipo_bucket_nuevo, monto_financiar, evento, fuente, fecha_lectura, fecha_alta, motivo)
                            VALUES
                            (:credito_id, :id_solicitud, :oferta_id, :fk_sucursal, :etapa_anterior, :etapa_nueva, :bucket_anterior, :bucket_nuevo, :tipo_anterior, :tipo_nuevo, :monto, :evento, 'oferta_mexico', NOW(), NOW(), :motivo)
                        ", [
                            'credito_id' => $creditoId,
                            'id_solicitud' => $idSolicitud,
                            'oferta_id' => $ofertaId,
                            'fk_sucursal' => $fkSucursal,
                            'etapa_anterior' => $snapshotPrevio['etapa_actual'] ?? null,
                            'etapa_nueva' => self::nullableStr($oferta['etapa'] ?? null),
                            'bucket_anterior' => $snapshotPrevio['bucket_actual'] ?? null,
                            'bucket_nuevo' => $bucket['bucket'],
                            'tipo_anterior' => $snapshotPrevio['tipo_bucket_actual'] ?? null,
                            'tipo_nuevo' => $bucket['tipo'],
                            'monto' => $monto,
                            'evento' => $evento,
                            'motivo' => $bucket['motivo'],
                        ]);
                    }
                }

                if (in_array($bucket['tipo'], ['sin_etapa', 'revisar_etapa'], true)) {
                    self::upsertDepuracionAtlas($db, [
                        'tipo' => $bucket['tipo'],
                        'credito_id' => $creditoId > 0 ? $creditoId : null,
                        'id_solicitud' => $idSolicitud,
                        'oferta_id' => $ofertaId,
                        'fk_sucursal_origen' => $fkSucursal,
                        'sucursal_origen' => $oferta['sucursal'] ?? null,
                        'etapa_origen' => $oferta['etapa'] ?? null,
                        'bucket_operativo' => $bucket['bucket'],
                        'motivo' => $bucket['motivo'],
                        'payload' => $oferta,
                    ], $dryRun);
                }
            }

            if (!$dryRun) {
                $db->CRUD("
                    INSERT INTO atlas_sync_oferta_mexico_control (proceso, last_offer_id, last_run_at, last_success_at, last_stats_json, activo, fecha_alta, fecha_actualizacion)
                    VALUES ('oferta_mexico', :last_offer_id, NOW(), NOW(), :stats, 1, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        last_offer_id = GREATEST(last_offer_id, VALUES(last_offer_id)),
                        last_run_at = NOW(),
                        last_success_at = NOW(),
                        last_stats_json = VALUES(last_stats_json),
                        activo = 1,
                        fecha_actualizacion = NOW()
                ", ['last_offer_id' => $ultimoId, 'stats' => json_encode($stats, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
            }

            return ['success' => true, 'mensaje' => $dryRun ? 'Dry-run de oferta Mexico ejecutado.' : 'Sincronizacion de oferta Mexico ejecutada.', 'datos' => $stats];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudo sincronizar oferta Mexico.',
                'datos' => $stats,
                'error' => $e->getMessage(),
            ];
        }
    }

    public static function getGestoresOperativos(): array
    {
        try {
            $db = new Database();
            $nombrePersonaSql = "TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom))";
            $gestores = $db->queryAll("
                SELECT go.*, {$nombrePersonaSql} AS persona_nombre, p.numero_empleado,
                       COUNT(gs.id) AS total_sucursales
                FROM atlas_gestores_operativos go
                LEFT JOIN persona p ON p.id = go.persona_id
                LEFT JOIN atlas_gestor_sucursales gs ON gs.gestor_id = go.id AND gs.activo = 1
                WHERE go.activo = 1
                GROUP BY go.id
                ORDER BY persona_nombre
            ");
            $sucursales = $db->queryAll("
                SELECT gs.*, {$nombrePersonaSql} AS persona_nombre, s.sucursal,
                       COALESCE(NULLIF(s.direccion_sucursal, ''), TRIM(CONCAT_WS(', ', NULLIF(s.calle, ''), NULLIF(s.numero_exterior, ''), NULLIF(s.colonia, ''), NULLIF(s.municipio, ''), NULLIF(s.estado, '')))) AS direccion,
                       COALESCE(pend.total_creditos_pendientes, 0) AS total_creditos_pendientes,
                       COALESCE(pend.cash_detenido_operativo, 0) AS cash_detenido_operativo
                FROM atlas_gestor_sucursales gs
                LEFT JOIN atlas_gestores_operativos go ON go.id = gs.gestor_id
                LEFT JOIN persona p ON p.id = go.persona_id
                LEFT JOIN atlas_catalogo_sucursales s ON s.fk_sucursal = gs.fk_sucursal
                LEFT JOIN (
                    SELECT ac.fk_sucursal, COUNT(*) AS total_creditos_pendientes, SUM(COALESCE(snap.monto_financiar, c.cash_detenido, c.monto_credito, 0)) AS cash_detenido_operativo
                    FROM atlas_asigna_sucursal_credito ac
                    INNER JOIN atlas_creditos c ON c.id = ac.credito_id AND c.activo = 1
                    LEFT JOIN atlas_creditos_oferta_snapshot snap ON snap.credito_id = c.id AND snap.activo = 1
                    WHERE ac.activo = 1
                      AND (snap.es_pendiente_operativo = 1 OR snap.tipo_bucket_actual = 'detenido' OR snap.bucket_actual LIKE 'DETENIDO:%')
                    GROUP BY ac.fk_sucursal
                ) pend ON pend.fk_sucursal = gs.fk_sucursal
                WHERE gs.activo = 1
                ORDER BY persona_nombre, s.sucursal
            ");
            return ['success' => true, 'datos' => ['gestores' => $gestores, 'sucursales' => $sucursales]];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudieron obtener los gestores operativos.', 'error' => $e->getMessage(), 'datos' => ['gestores' => [], 'sucursales' => []]];
        }
    }

    private static function validarPublicacionRutaGestor(Database $db, ?string $estatus, ?string $fechaInicio, ?string $fechaFin): array
    {
        $estatusNormalizado = strtolower(trim((string)$estatus));
        if (in_array($estatusNormalizado, ['', 'borrador', 'cancelada'], true)) {
            return ['success' => true];
        }

        $inicio = self::nullableStr($fechaInicio);
        $fin = self::nullableStr($fechaFin) ?: $inicio;
        if (!$inicio || !$fin) {
            return ['success' => false, 'mensaje' => 'Actualiza las fechas de la ruta antes de publicarla.'];
        }
        if ($inicio > $fin) {
            return ['success' => false, 'mensaje' => 'La fecha fin global no puede ser menor a la fecha inicio global.'];
        }

        $tz = new \DateTimeZone('America/Mexico_City');
        $ahora = new \DateTimeImmutable('now', $tz);
        $hoy = $ahora->format('Y-m-d');
        $minutosActuales = ((int)$ahora->format('H') * 60) + (int)$ahora->format('i');
        $horario = self::getConfiguracionHorarioOperativoRutas($db);

        if ($fin < $hoy) {
            return ['success' => false, 'mensaje' => 'No se puede publicar una ruta vencida. Actualiza el rango de fechas.'];
        }
        if ($fin === $hoy && $minutosActuales >= (int)$horario['fin_minutos']) {
            return ['success' => false, 'mensaje' => "Ya paso la ventana operativa de visitas de hoy ({$horario['inicio']} a {$horario['fin']}). Actualiza las fechas antes de publicar."];
        }

        return ['success' => true];
    }

    private static function fechaMinimaOperativaRutaGestor(Database $db): string
    {
        $tz = new \DateTimeZone('America/Mexico_City');
        $ahora = new \DateTimeImmutable('now', $tz);
        $minutosActuales = ((int)$ahora->format('H') * 60) + (int)$ahora->format('i');
        $horario = self::getConfiguracionHorarioOperativoRutas($db);
        if ($minutosActuales >= (int)$horario['fin_minutos']) {
            $ahora = $ahora->modify('+1 day');
        }
        return $ahora->format('Y-m-d');
    }

    private static function validarPresupuestoRutaGestor(Database $db, ?int $presupuestoId, ?string $fechaInicio, ?string $fechaFin): array
    {
        if (!$presupuestoId) {
            return ['success' => false, 'mensaje' => 'Selecciona un presupuesto valido para la ruta.'];
        }
        $inicio = self::nullableStr($fechaInicio);
        $fin = self::nullableStr($fechaFin) ?: $inicio;
        if (!$inicio || !$fin) {
            return ['success' => false, 'mensaje' => 'Captura la fecha inicio y fecha fin de la ruta.'];
        }
        $tz = new \DateTimeZone('America/Mexico_City');
        $fechaInicioObj = \DateTimeImmutable::createFromFormat('!Y-m-d', $inicio, $tz);
        $fechaFinObj = \DateTimeImmutable::createFromFormat('!Y-m-d', $fin, $tz);
        if (!$fechaInicioObj || !$fechaFinObj) {
            return ['success' => false, 'mensaje' => 'Las fechas de la ruta no son validas.'];
        }
        if ($fechaInicioObj->format('Y-m') !== $fechaFinObj->format('Y-m')) {
            return ['success' => false, 'mensaje' => 'La ruta debe pertenecer a un solo mes de presupuesto.'];
        }

        $ahora = new \DateTimeImmutable('now', $tz);
        $actualAnio = (int)$ahora->format('Y');
        $actualMes = (int)$ahora->format('n');
        $siguienteInicio = (new \DateTimeImmutable('first day of next month', $tz))->setTime(0, 0, 0);
        $habilitaSiguiente = $siguienteInicio->modify('-5 days');
        $rutaAnio = (int)$fechaInicioObj->format('Y');
        $rutaMes = (int)$fechaInicioObj->format('n');
        $esActual = $rutaAnio === $actualAnio && $rutaMes === $actualMes;
        $esSiguiente = $rutaAnio === (int)$siguienteInicio->format('Y') && $rutaMes === (int)$siguienteInicio->format('n');
        if (!$esActual && !($esSiguiente && $ahora >= $habilitaSiguiente)) {
            return ['success' => false, 'mensaje' => 'Solo puedes crear rutas sobre el mes actual. El mes siguiente se habilita 5 dias antes si su presupuesto ya esta cargado.'];
        }

        $presupuesto = $db->queryOne("
            SELECT id, anio, mes, nombre_mes
            FROM atlas_presupuestos_mensuales
            WHERE id = :id
              AND activo = 1
            LIMIT 1
        ", ['id' => $presupuestoId]);
        if (!$presupuesto) {
            return ['success' => false, 'mensaje' => 'El presupuesto seleccionado no existe o no esta activo.'];
        }
        if ((int)$presupuesto['anio'] !== $rutaAnio || (int)$presupuesto['mes'] !== $rutaMes) {
            return ['success' => false, 'mensaje' => 'El presupuesto seleccionado no corresponde al mes de la ruta.'];
        }
        return ['success' => true, 'presupuesto' => $presupuesto];
    }

    private static function minutosHoraRuta(?string $hora): int
    {
        $parts = explode(':', (string)($hora ?: '00:00'));
        $h = isset($parts[0]) ? (int)$parts[0] : 0;
        $m = isset($parts[1]) ? (int)$parts[1] : 0;
        return ($h * 60) + $m;
    }

    private static function nullableHoraRuta($hora): ?string
    {
        $raw = trim((string)($hora ?? ''));
        if ($raw === '') return null;
        if (!preg_match('/^([01]?\d|2[0-3]):([0-5]\d)(?::[0-5]\d)?$/', $raw, $m)) {
            return null;
        }
        return str_pad((string)(int)$m[1], 2, '0', STR_PAD_LEFT) . ':' . $m[2] . ':00';
    }

    private static function horaRutaDesdeMinutos(int $minutos): string
    {
        $minutos = max(0, $minutos);
        return str_pad((string)intdiv($minutos, 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)($minutos % 60), 2, '0', STR_PAD_LEFT);
    }

    private static function duracionRutaTexto(int $minutos): string
    {
        $minutos = max(0, $minutos);
        $horas = intdiv($minutos, 60);
        $mins = $minutos % 60;
        if ($horas > 0 && $mins > 0) return "{$horas} h {$mins} min";
        if ($horas > 0) return "{$horas} h";
        return "{$mins} min";
    }

    private static function fechaRutaMensajeTexto(?string $fecha): string
    {
        $raw = trim((string)$fecha);
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $raw, $m)) {
            return "{$m[3]}-{$m[2]}-{$m[1]}";
        }
        return $raw;
    }

    private static function estanciaRutaMinutos(array $visita): int
    {
        $valor = max(1, (int)($visita['estancia_valor'] ?? 45));
        return ($visita['estancia_unidad'] ?? 'minutos') === 'horas' ? $valor * 60 : $valor;
    }

    private static function distanciaRutaKm(?array $a, ?array $b): ?float
    {
        if (!$a || !$b || !isset($a['latitud'], $a['longitud'], $b['latitud'], $b['longitud'])) {
            return null;
        }
        $lat1 = (float)$a['latitud']; $lng1 = (float)$a['longitud'];
        $lat2 = (float)$b['latitud']; $lng2 = (float)$b['longitud'];
        if ($lat1 == 0.0 || $lng1 == 0.0 || $lat2 == 0.0 || $lng2 == 0.0) {
            return null;
        }
        $r = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $h = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return 2 * $r * asin(min(1, sqrt($h)));
    }

    private static function trasladoRutaMinutos(?array $a, ?array $b): int
    {
        $km = self::distanciaRutaKm($a, $b);
        if ($km === null) {
            return 25;
        }
        return max(15, (int)ceil(($km / 28) * 60) + 5);
    }

    private static function validarFactibilidadOperativaRutaGestor(Database $db, array $visitas): array
    {
        if (!$visitas) {
            return ['success' => true];
        }
        $fks = array_values(array_unique(array_filter(array_map(static fn($v) => (int)($v['fk_sucursal'] ?? 0), $visitas))));
        $coords = [];
        if ($fks) {
            $params = [];
            $ph = [];
            foreach ($fks as $i => $fk) {
                $key = 'fk' . $i;
                $ph[] = ':' . $key;
                $params[$key] = $fk;
            }
            $rows = $db->queryAll("
                SELECT fk_sucursal, latitud, longitud
                FROM atlas_catalogo_sucursales
                WHERE fk_sucursal IN (" . implode(',', $ph) . ")
            ", $params);
            foreach ($rows as $row) {
                $coords[(int)$row['fk_sucursal']] = $row;
            }
        }

        $porDia = [];
        foreach ($visitas as $idx => $visita) {
            $dia = (string)($visita['fecha_inicio_visita'] ?? '');
            $visita['_orden'] = $idx + 1;
            $fk = (int)($visita['fk_sucursal'] ?? 0);
            if (isset($coords[$fk])) {
                $visita['latitud'] = $coords[$fk]['latitud'];
                $visita['longitud'] = $coords[$fk]['longitud'];
            }
            $porDia[$dia][] = $visita;
        }

        $horario = self::getConfiguracionHorarioOperativoRutas($db);
        $inicioDia = (int)$horario['inicio_minutos'];
        $finDia = (int)$horario['fin_minutos'];
        $separacion = 10;
        $comida = 60;
        foreach ($porDia as $dia => $lista) {
            usort($lista, static fn($a, $b) => (int)($a['orden_visita'] ?? $a['_orden']) <=> (int)($b['orden_visita'] ?? $b['_orden']));
            $servicio = 0; $traslados = 0; $anterior = null;
            foreach ($lista as $visita) {
                $estancia = self::estanciaRutaMinutos($visita);
                $servicio += $estancia;
                if ($estancia > ($finDia - $inicioDia)) {
                    return ['success' => false, 'mensaje' => "La visita {$visita['_orden']} requiere " . self::duracionRutaTexto($estancia) . " y no cabe en un dia operativo de " . self::duracionRutaTexto($finDia - $inicioDia) . "."];
                }
                if ($anterior !== null) {
                    $traslado = self::trasladoRutaMinutos($anterior, $visita);
                    $traslados += $traslado;
                }
                $anterior = $visita;
            }
            $separaciones = max(0, count($lista) - 1) * $separacion;
            $totalBase = $servicio + $traslados + $separaciones;
            $requiereComida = $totalBase >= (5 * 60);
            $totalRequerido = $totalBase + ($requiereComida ? $comida : 0);
            if ($totalRequerido > ($finDia - $inicioDia)) {
                $diasNecesarios = max(2, (int)ceil($totalRequerido / ($finDia - $inicioDia)));
                $extra = $requiereComida ? ' y comida' : '';
                $diaTexto = self::fechaRutaMensajeTexto((string)$dia);
                return ['success' => false, 'mensaje' => "La ruta del {$diaTexto} requiere aprox. " . self::duracionRutaTexto($totalRequerido) . " (" . count($lista) . " sucursal(es), estadia, traslados, separaciones{$extra}) y el dia operativo solo tiene " . self::duracionRutaTexto($finDia - $inicioDia) . ". Necesitas cambiar forzosamente la Fecha inicio y la Fecha fin para cubrir al menos {$diasNecesarios} dias y repartir las sucursales, o reducir sucursales/estadia."];
            }
        }
        return ['success' => true];
    }

    public static function guardarRutaGestor(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarRutasGestoresAtlas($db);
            $id = self::intVal($input['id'] ?? 0);
            $nombreRuta = self::nullableStr($input['nombre_ruta'] ?? null);
            if ($nombreRuta === null || $nombreRuta === '') {
                return ['success' => false, 'mensaje' => 'Captura el nombre de la ruta.'];
            }
            $gestorId = self::nullableInt($input['gestor_persona_id'] ?? null);
            $gestorNombre = self::nullableStr($input['gestor_nombre'] ?? null);
            if ($gestorId) {
                $p = $db->queryOne("SELECT TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre, numero_empleado FROM persona WHERE id = :id LIMIT 1", ['id' => $gestorId]);
                $gestorNombre = $p['nombre'] ?? $gestorNombre;
            }
            $payload = [
                'gestor_persona_id' => $gestorId,
                'gestor_nombre' => $gestorNombre,
                'fecha_ruta' => self::nullableStr($input['fecha_inicio'] ?? null) ?: date('Y-m-d'),
                'fecha_inicio' => self::nullableStr($input['fecha_inicio'] ?? null),
                'fecha_fin' => self::nullableStr($input['fecha_fin'] ?? null),
                'estatus' => self::nullableStr($input['estatus'] ?? 'borrador') ?: 'borrador',
                'observaciones' => self::nullableStr($input['observaciones'] ?? null),
                'nombre_ruta' => $nombreRuta,
                'tipo_ruta' => self::nullableStr($input['tipo_ruta'] ?? 'campo') ?: 'campo',
                'prioridad' => self::nullableStr($input['prioridad'] ?? 'media') ?: 'media',
                'presupuesto_id' => self::nullableInt($input['presupuesto_id'] ?? null),
                'criterio_prioridad' => self::nullableStr($input['criterio_prioridad'] ?? 'enganches') ?: 'enganches',
            ];
            if (!empty($payload['fecha_inicio']) && !empty($payload['fecha_fin']) && $payload['fecha_inicio'] > $payload['fecha_fin']) {
                return ['success' => false, 'mensaje' => 'La fecha fin global no puede ser menor a la fecha inicio global.'];
            }
            $fechaMinima = self::fechaMinimaOperativaRutaGestor($db);
            if (!empty($payload['fecha_inicio']) && $payload['fecha_inicio'] < $fechaMinima) {
                $horario = self::getConfiguracionHorarioOperativoRutas($db);
                return ['success' => false, 'mensaje' => "La fecha inicio debe respetar la ventana operativa CDMX. Despues de las {$horario['fin']}, la fecha minima es el dia siguiente."];
            }
            $validacionPresupuesto = self::validarPresupuestoRutaGestor($db, $payload['presupuesto_id'], $payload['fecha_inicio'], $payload['fecha_fin'] ?: $payload['fecha_ruta']);
            if (!$validacionPresupuesto['success']) {
                return $validacionPresupuesto;
            }
            $validacionPublicacion = self::validarPublicacionRutaGestor($db, $payload['estatus'], $payload['fecha_inicio'], $payload['fecha_fin'] ?: $payload['fecha_ruta']);
            if (!$validacionPublicacion['success']) {
                return $validacionPublicacion;
            }
            $sucursalesInput = $input['sucursales'] ?? [];
            if (is_string($sucursalesInput)) {
                $decoded = json_decode($sucursalesInput, true);
                $sucursalesInput = is_array($decoded) ? $decoded : [];
            }
            $fkInputRuta = self::nullableInt($input['fk_sucursal'] ?? null);
            if ($id > 0 && (!empty($sucursalesInput) || $fkInputRuta) && self::rutaGestorTieneGestiones($db, $id)) {
                return [
                    'success' => false,
                    'mensaje' => 'No se puede reconstruir esta ruta porque una o mas sucursales ya tienen gestion registrada. Conserva la ruta actual o crea una nueva.',
                ];
            }
            $sucursalesRuta = [];
            $rutaInicio = self::nullableStr($payload['fecha_inicio'] ?? null) ?: self::nullableStr($payload['fecha_ruta'] ?? null);
            $rutaFin = self::nullableStr($payload['fecha_fin'] ?? null) ?: $rutaInicio;
            foreach ((array)$sucursalesInput as $idx => $item) {
                $fkItem = is_array($item) ? self::nullableInt($item['fk_sucursal'] ?? null) : self::nullableInt($item);
                if (!$fkItem || isset($sucursalesRuta[$fkItem])) continue;
                $fechaInicioVisita = is_array($item) ? (self::nullableStr($item['fecha_inicio_visita'] ?? null) ?: $rutaInicio) : $rutaInicio;
                $fechaFinVisita = is_array($item) ? (self::nullableStr($item['fecha_fin_visita'] ?? null) ?: $fechaInicioVisita) : $fechaInicioVisita;
                if ($rutaInicio && $fechaInicioVisita && $fechaInicioVisita < $rutaInicio) {
                    return ['success' => false, 'mensaje' => 'Una visita inicia antes de la fecha global de la ruta.'];
                }
                if ($rutaFin && $fechaFinVisita && $fechaFinVisita > $rutaFin) {
                    return ['success' => false, 'mensaje' => 'Una visita termina despues de la fecha global de la ruta.'];
                }
                if ($fechaInicioVisita && $fechaFinVisita && $fechaInicioVisita > $fechaFinVisita) {
                    return ['success' => false, 'mensaje' => 'Una visita tiene fecha fin menor a su fecha inicio.'];
                }
                if ($fechaInicioVisita && $fechaFinVisita && $fechaInicioVisita !== $fechaFinVisita) {
                    return ['success' => false, 'mensaje' => 'Cada sucursal solo puede programarse por un dia dentro de la ruta. Crea otra ruta si necesita asistir otro dia.'];
                }
                $estanciaValor = is_array($item) ? (self::nullableInt($item['estancia_valor'] ?? null) ?: 45) : 45;
                $estanciaUnidad = is_array($item) ? (self::nullableStr($item['estancia_unidad'] ?? null) ?: 'minutos') : 'minutos';
                if ($estanciaUnidad === 'horas' && $estanciaValor > 5) {
                    return ['success' => false, 'mensaje' => 'Si la estancia es mayor a 5, la unidad debe ser minutos.'];
                }
                $horaLlegada = is_array($item) ? self::nullableHoraRuta($item['hora_llegada'] ?? null) : null;
                $sucursalesRuta[$fkItem] = [
                    'fk_sucursal' => $fkItem,
                    'orden_visita' => is_array($item) ? (self::nullableInt($item['orden_visita'] ?? null) ?: ($idx + 1)) : ($idx + 1),
                    'hora_programada' => $horaLlegada ?: (is_array($item) ? self::nullableHoraRuta($item['hora_programada'] ?? null) : null),
                    'prioridad_visita' => is_array($item) ? (self::nullableStr($item['prioridad'] ?? null) ?: 'media') : 'media',
                    'criterio_prioridad_visita' => is_array($item) ? (self::nullableStr($item['criterio_prioridad'] ?? null) ?: 'enganches') : 'enganches',
                    'fecha_inicio_visita' => $fechaInicioVisita,
                    'fecha_fin_visita' => $fechaFinVisita,
                    'hora_llegada' => $horaLlegada,
                    'estancia_valor' => max(1, $estanciaValor),
                    'estancia_unidad' => in_array($estanciaUnidad, ['minutos', 'horas'], true) ? $estanciaUnidad : 'minutos',
                    'hora_salida_sugerida' => is_array($item) ? self::nullableHoraRuta($item['hora_salida_sugerida'] ?? null) : null,
                ];
            }
            $fk = self::nullableInt($input['fk_sucursal'] ?? null);
            if (!$sucursalesRuta && $fk) {
                $sucursalesRuta[$fk] = [
                    'fk_sucursal' => $fk,
                    'orden_visita' => 1,
                    'hora_programada' => null,
                    'prioridad_visita' => self::nullableStr($input['prioridad'] ?? null) ?: 'media',
                    'criterio_prioridad_visita' => self::nullableStr($input['criterio_prioridad'] ?? null) ?: 'enganches',
                    'fecha_inicio_visita' => $rutaInicio,
                    'fecha_fin_visita' => $rutaInicio,
                    'hora_llegada' => null,
                    'estancia_valor' => 45,
                    'estancia_unidad' => 'minutos',
                    'hora_salida_sugerida' => null,
                ];
            }
            if ($sucursalesRuta) {
                $factibilidad = self::validarFactibilidadOperativaRutaGestor($db, array_values($sucursalesRuta));
                if (!$factibilidad['success']) {
                    return $factibilidad;
                }
                if ($id > 0 && self::rutaGestorTieneGestiones($db, $id)) {
                    return [
                        'success' => false,
                        'mensaje' => 'No se puede reconstruir esta ruta porque una o mas sucursales ya tienen gestion registrada. Conserva la ruta actual o crea una nueva.',
                    ];
                }
            }
            if ($id > 0) {
                $payload['id'] = $id;
                $db->CRUD("
                    UPDATE atlas_rutas_gestores
                    SET gestor_persona_id=:gestor_persona_id, gestor_nombre=:gestor_nombre, fecha_ruta=:fecha_ruta,
                        fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin, estatus=:estatus, observaciones=:observaciones,
                        nombre_ruta=:nombre_ruta, tipo_ruta=:tipo_ruta, prioridad=:prioridad,
                        presupuesto_id=:presupuesto_id, criterio_prioridad=:criterio_prioridad
                    WHERE id=:id
                ", $payload);
            } else {
                $db->CRUD("
                    INSERT INTO atlas_rutas_gestores
                    (gestor_persona_id, gestor_nombre, fecha_ruta, fecha_inicio, fecha_fin, estatus, observaciones, nombre_ruta, tipo_ruta, prioridad, presupuesto_id, criterio_prioridad, activo)
                    VALUES
                    (:gestor_persona_id, :gestor_nombre, :fecha_ruta, :fecha_inicio, :fecha_fin, :estatus, :observaciones, :nombre_ruta, :tipo_ruta, :prioridad, :presupuesto_id, :criterio_prioridad, 1)
                ", $payload);
                $id = $db->lastInsertId();
            }
            if ($sucursalesRuta) {
                $db->CRUD("UPDATE atlas_ruta_sucursales SET activo = 0 WHERE ruta_id = :ruta", ['ruta' => $id]);
                $orden = 1;
                foreach ($sucursalesRuta as $visita) {
                    $db->CRUD("
                        INSERT INTO atlas_ruta_sucursales (
                            ruta_id, fk_sucursal, orden_visita, hora_programada, prioridad_visita,
                            criterio_prioridad_visita, fecha_inicio_visita, fecha_fin_visita,
                            hora_llegada, estancia_valor, estancia_unidad, hora_salida_sugerida, activo
                        )
                        VALUES (
                            :ruta, :fk, :orden, :hora, :prioridad,
                            :criterio, :fecha_inicio, :fecha_fin,
                            :hora_llegada, :estancia_valor, :estancia_unidad, :hora_salida, 1
                        )
                    ", [
                        'ruta' => $id,
                        'fk' => $visita['fk_sucursal'],
                        'orden' => $orden++,
                        'hora' => $visita['hora_programada'],
                        'prioridad' => $visita['prioridad_visita'] ?: 'media',
                        'criterio' => $visita['criterio_prioridad_visita'] ?: 'enganches',
                        'fecha_inicio' => $visita['fecha_inicio_visita'],
                        'fecha_fin' => $visita['fecha_fin_visita'],
                        'hora_llegada' => $visita['hora_llegada'],
                        'estancia_valor' => $visita['estancia_valor'],
                        'estancia_unidad' => $visita['estancia_unidad'],
                        'hora_salida' => $visita['hora_salida_sugerida'],
                    ]);
                }
            }
            return ['success' => true, 'mensaje' => 'Ruta guardada.', 'id' => $id];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar la ruta.', 'error' => $e->getMessage()];
        }
    }

    public static function getRutaGestorDetalle(int $id): array
    {
        try {
            $db = new Database();
            self::asegurarRutasGestoresAtlas($db);
            $ruta = $db->queryOne("
                SELECT r.*,
                       p.anio AS presupuesto_anio,
                       p.mes AS presupuesto_mes_num,
                       p.nombre_mes AS presupuesto_mes
                FROM atlas_rutas_gestores r
                LEFT JOIN atlas_presupuestos_mensuales p ON p.id = r.presupuesto_id
                WHERE r.id = :id
                  AND r.activo = 1
                LIMIT 1
            ", ['id' => $id]);
            if (!$ruta) return ['success' => false, 'mensaje' => 'Ruta no encontrada.'];
            $rutaInicio = self::nullableStr($ruta['fecha_inicio'] ?? null) ?: self::nullableStr($ruta['fecha_ruta'] ?? null);
            $rutaFin = self::nullableStr($ruta['fecha_fin'] ?? null) ?: $rutaInicio;
            $sucursales = $db->queryAll("
                SELECT rs.*, s.sucursal,
                       COALESCE(NULLIF(s.direccion_sucursal, ''), TRIM(CONCAT_WS(', ', NULLIF(s.calle, ''), NULLIF(s.numero_exterior, ''), NULLIF(s.colonia, ''), NULLIF(s.municipio, ''), NULLIF(s.estado, '')))) AS direccion,
                       s.latitud, s.longitud, divi.nombre AS division_nombre,
                       COALESCE(tel.numero_telefono, '') AS numero_telefono,
                       COALESCE(pdet.meta_creditos, 0) AS meta_creditos,
                       COALESCE(pdet.meta_cash, 0) AS meta_cash,
                       COALESCE(pend.total_creditos, 0) AS total_creditos,
                       COALESCE(gest.total_gestiones, 0) AS total_gestiones,
                       CASE WHEN COALESCE(gest.total_gestiones, 0) > 0 THEN 1 ELSE 0 END AS tiene_gestion
                FROM atlas_ruta_sucursales rs
                LEFT JOIN atlas_catalogo_sucursales s ON s.fk_sucursal = rs.fk_sucursal
                LEFT JOIN atlas_catalogo_divisiones divi ON divi.id = s.division_id
                LEFT JOIN atlas_asigna_telefono_sucursal tel
                       ON tel.fk_sucursal = s.fk_sucursal
                      AND tel.activo = 1
                      AND tel.es_principal = 1
                LEFT JOIN atlas_presupuesto_sucursal_detalle pdet
                       ON pdet.presupuesto_id = :presupuesto_id
                      AND pdet.fk_sucursal = rs.fk_sucursal
                      AND pdet.activo = 1
                LEFT JOIN (
                    SELECT ac.fk_sucursal, COUNT(*) AS total_creditos
                    FROM atlas_asigna_sucursal_credito ac
                    INNER JOIN atlas_creditos c ON c.id = ac.credito_id AND c.activo = 1
                    LEFT JOIN atlas_creditos_oferta_snapshot snap ON snap.credito_id = c.id AND snap.activo = 1
                    WHERE ac.activo = 1
                      AND (snap.es_pendiente_operativo = 1 OR snap.tipo_bucket_actual = 'detenido' OR snap.bucket_actual LIKE 'DETENIDO:%')
                    GROUP BY ac.fk_sucursal
                ) pend ON pend.fk_sucursal = rs.fk_sucursal
                LEFT JOIN (
                    SELECT fk_sucursal, COUNT(*) AS total_gestiones
                    FROM atlas_gestiones_credito
                    WHERE DATE(fecha_gestion) BETWEEN :inicio AND :fin
                    GROUP BY fk_sucursal
                ) gest ON gest.fk_sucursal = rs.fk_sucursal
                WHERE rs.ruta_id = :id AND rs.activo = 1
                ORDER BY rs.orden_visita ASC, rs.id ASC
            ", ['id' => $id, 'inicio' => $rutaInicio, 'fin' => $rutaFin, 'presupuesto_id' => (int)($ruta['presupuesto_id'] ?? 0)]);
            $creditos = $db->queryAll("
                SELECT c.id AS credito_id, c.id_solicitud, ac.fk_sucursal, s.sucursal,
                       snap.bucket_actual, snap.fecha_ultima_sync AS fecha_ultima_sync_fmt,
                       COALESCE(snap.monto_financiar, c.cash_detenido, c.monto_credito, 0) AS monto_financiar
                FROM atlas_ruta_sucursales rs
                INNER JOIN atlas_asigna_sucursal_credito ac ON ac.fk_sucursal = rs.fk_sucursal AND ac.activo = 1
                INNER JOIN atlas_creditos c ON c.id = ac.credito_id AND c.activo = 1
                LEFT JOIN atlas_creditos_oferta_snapshot snap ON snap.credito_id = c.id AND snap.activo = 1
                LEFT JOIN atlas_catalogo_sucursales s ON s.fk_sucursal = ac.fk_sucursal
                WHERE rs.ruta_id = :id
                  AND rs.activo = 1
                  AND (snap.es_pendiente_operativo = 1 OR snap.tipo_bucket_actual = 'detenido' OR snap.bucket_actual LIKE 'DETENIDO:%')
                ORDER BY rs.orden_visita ASC, monto_financiar DESC
            ", ['id' => $id]);
            return ['success' => true, 'datos' => ['ruta' => $ruta, 'sucursales' => $sucursales, 'creditos' => $creditos]];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar la ruta.', 'error' => $e->getMessage()];
        }
    }

    public static function getRutaGestorResumenTecnico(int $id): array
    {
        try {
            $db = new Database();
            self::asegurarRutasGestoresAtlas($db);
            $ruta = $db->queryOne("
                SELECT
                    r.*,
                    DATE_FORMAT(COALESCE(r.fecha_inicio, r.fecha_ruta), '%d/%m/%Y') AS fecha_inicio_fmt,
                    DATE_FORMAT(COALESCE(r.fecha_fin, r.fecha_ruta), '%d/%m/%Y') AS fecha_fin_fmt,
                    p.anio AS presupuesto_anio,
                    p.mes AS presupuesto_mes_num,
                    p.nombre_mes AS presupuesto_mes,
                    p.archivo_original AS presupuesto_archivo,
                    p.total_sucursales AS presupuesto_total_sucursales,
                    TRIM(CONCAT_WS(' ', pg.nombres, pg.segundo_nombre, pg.apellidop, pg.apellidom)) AS gestor_persona_nombre,
                    pg.numero_empleado AS gestor_numero_empleado_actual,
                    au.departamento AS gestor_departamento,
                    au.puesto AS gestor_puesto,
                    au.correo AS gestor_correo,
                    au.telefono AS gestor_telefono
                FROM atlas_rutas_gestores r
                LEFT JOIN atlas_presupuestos_mensuales p ON p.id = r.presupuesto_id
                LEFT JOIN persona pg ON pg.id = r.gestor_persona_id
                LEFT JOIN atlas_acceso_usuarios au ON au.persona_id = r.gestor_persona_id AND au.origen = 'comercial_mexico'
                WHERE r.id = :id
                  AND r.activo = 1
                LIMIT 1
            ", ['id' => $id]);
            if (!$ruta) {
                return ['success' => false, 'mensaje' => 'Ruta no encontrada.'];
            }

            $sucursales = $db->queryAll("
                SELECT
                    rs.*,
                    s.id AS sucursal_id,
                    s.sucursal,
                    s.distribuidor_id,
                    d.nombre AS distribuidor,
                    COALESCE(NULLIF(s.direccion_sucursal, ''), TRIM(CONCAT_WS(', ', NULLIF(s.calle, ''), NULLIF(s.numero_exterior, ''), NULLIF(s.colonia, ''), NULLIF(s.municipio, ''), NULLIF(s.estado, '')))) AS direccion,
                    s.estado,
                    s.municipio,
                    s.localidad,
                    s.colonia,
                    s.codigo_postal,
                    s.latitud,
                    s.longitud,
                    divi.nombre AS division_nombre,
                    COALESCE(cls.nombre, pdet.clasificacion, '') AS clasificacion_nombre,
                    COALESCE(NULLIF(cls.icon_font, ''), 'fa-solid fa-location-dot') AS clasificacion_icon_font,
                    COALESCE(NULLIF(cls.color_hex, ''), '#2563EB') AS clasificacion_color_hex,
                    COALESCE(tel.numero_telefono, '') AS numero_telefono,
                    COALESCE(tel.nombre_contacto, '') AS contacto_telefono,
                    COALESCE(pdet.meta_creditos, 0) AS meta_creditos,
                    COALESCE(pdet.meta_cash, 0) AS meta_cash,
                    COALESCE(pdet.promedio_creditos, 0) AS promedio_creditos,
                    COALESCE(pdet.observaciones, '') AS presupuesto_observaciones,
                    COALESCE(pend.total_creditos, 0) AS total_creditos,
                    COALESCE(pend.cash_detenido_operativo, 0) AS cash_detenido_operativo
                FROM atlas_ruta_sucursales rs
                LEFT JOIN atlas_catalogo_sucursales s ON s.fk_sucursal = rs.fk_sucursal
                LEFT JOIN atlas_catalogo_distribuidores d ON d.id = s.distribuidor_id
                LEFT JOIN atlas_catalogo_divisiones divi ON divi.id = s.division_id
                LEFT JOIN atlas_catalogo_clasificaciones cls ON cls.id = s.clasificacion_id
                LEFT JOIN atlas_asigna_telefono_sucursal tel
                       ON tel.fk_sucursal = s.fk_sucursal
                      AND tel.activo = 1
                      AND tel.es_principal = 1
                LEFT JOIN atlas_presupuesto_sucursal_detalle pdet
                       ON pdet.presupuesto_id = :presupuesto_id
                      AND pdet.fk_sucursal = rs.fk_sucursal
                      AND pdet.activo = 1
                LEFT JOIN (
                    SELECT ac.fk_sucursal,
                           COUNT(DISTINCT ac.credito_id) AS total_creditos,
                           SUM(COALESCE(snap.monto_financiar, c.cash_detenido, c.monto_credito, 0)) AS cash_detenido_operativo
                    FROM atlas_asigna_sucursal_credito ac
                    INNER JOIN atlas_creditos c ON c.id = ac.credito_id AND c.activo = 1
                    LEFT JOIN atlas_creditos_oferta_snapshot snap ON snap.credito_id = c.id AND snap.activo = 1
                    WHERE ac.activo = 1
                      AND (snap.es_pendiente_operativo = 1 OR snap.tipo_bucket_actual = 'detenido' OR snap.bucket_actual LIKE 'DETENIDO:%')
                    GROUP BY ac.fk_sucursal
                ) pend ON pend.fk_sucursal = rs.fk_sucursal
                WHERE rs.ruta_id = :id
                  AND rs.activo = 1
                ORDER BY rs.orden_visita ASC, rs.id ASC
            ", ['id' => $id, 'presupuesto_id' => (int)($ruta['presupuesto_id'] ?? 0)]);

            $creditos = $db->queryAll("
                SELECT
                    c.id AS credito_id,
                    c.id_solicitud,
                    ac.fk_sucursal,
                    s.sucursal,
                    COALESCE(snap.bucket_actual, '') AS bucket_actual,
                    COALESCE(snap.tipo_bucket_actual, '') AS tipo_bucket_actual,
                    snap.fecha_ultima_sync AS fecha_ultima_sync,
                    COALESCE(snap.monto_financiar, c.cash_detenido, c.monto_credito, 0) AS monto_financiar
                FROM atlas_ruta_sucursales rs
                INNER JOIN atlas_asigna_sucursal_credito ac ON ac.fk_sucursal = rs.fk_sucursal AND ac.activo = 1
                INNER JOIN atlas_creditos c ON c.id = ac.credito_id AND c.activo = 1
                LEFT JOIN atlas_creditos_oferta_snapshot snap ON snap.credito_id = c.id AND snap.activo = 1
                LEFT JOIN atlas_catalogo_sucursales s ON s.fk_sucursal = ac.fk_sucursal
                WHERE rs.ruta_id = :id
                  AND rs.activo = 1
                  AND (snap.es_pendiente_operativo = 1 OR snap.tipo_bucket_actual = 'detenido' OR snap.bucket_actual LIKE 'DETENIDO:%')
                ORDER BY rs.orden_visita ASC, monto_financiar DESC
            ", ['id' => $id]);

            $resumen = [
                'total_sucursales' => count($sucursales),
                'sucursales_con_coordenadas' => 0,
                'sucursales_sin_coordenadas' => 0,
                'sucursales_sin_telefono' => 0,
                'meta_creditos' => 0,
                'meta_cash' => 0.0,
                'creditos_operativos' => count($creditos),
                'cash_operativo' => 0.0,
                'clasificaciones' => [],
                'estados' => [],
            ];
            foreach ($sucursales as $s) {
                $tieneCoords = trim((string)($s['latitud'] ?? '')) !== '' && trim((string)($s['longitud'] ?? '')) !== '';
                $resumen[$tieneCoords ? 'sucursales_con_coordenadas' : 'sucursales_sin_coordenadas']++;
                if (trim((string)($s['numero_telefono'] ?? '')) === '') {
                    $resumen['sucursales_sin_telefono']++;
                }
                $resumen['meta_creditos'] += (int)($s['meta_creditos'] ?? 0);
                $resumen['meta_cash'] += (float)($s['meta_cash'] ?? 0);
                $clas = trim((string)($s['clasificacion_nombre'] ?? 'Sin clasificacion')) ?: 'Sin clasificacion';
                $edo = trim((string)($s['estado'] ?? 'Sin estado')) ?: 'Sin estado';
                $resumen['clasificaciones'][$clas] = ($resumen['clasificaciones'][$clas] ?? 0) + 1;
                $resumen['estados'][$edo] = ($resumen['estados'][$edo] ?? 0) + 1;
            }
            foreach ($creditos as $c) {
                $resumen['cash_operativo'] += (float)($c['monto_financiar'] ?? 0);
            }
            ksort($resumen['clasificaciones']);
            ksort($resumen['estados']);

            return [
                'success' => true,
                'datos' => [
                    'ruta' => $ruta,
                    'sucursales' => $sucursales,
                    'creditos' => $creditos,
                    'resumen' => $resumen,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo generar el resumen tecnico de la ruta.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarGestorOperativo(array $input): array
    {
        try {
            $db = new Database();
            $id = self::intVal($input['id'] ?? 0);
            $personaId = self::intVal($input['persona_id'] ?? 0);
            if ($personaId <= 0) return ['success' => false, 'mensaje' => 'Selecciona una persona.'];
            $existe = $db->queryOne("SELECT id FROM atlas_gestores_operativos WHERE persona_id = :persona_id AND activo = 1 AND id <> :id LIMIT 1", ['persona_id' => $personaId, 'id' => $id]);
            if ($existe) return ['success' => true, 'mensaje' => 'El gestor ya estÃ¡ activo.', 'id' => (int)$existe['id']];
            if ($id > 0) {
                $db->CRUD("
                    UPDATE atlas_gestores_operativos
                    SET persona_id = :persona_id,
                        tipo_gestor = :tipo_gestor,
                        puede_gestionar_campo = :campo,
                        puede_gestionar_telefonica = :telefonica,
                        capacidad_visitas_dia = :capacidad,
                        activo = 1
                    WHERE id = :id
                ", [
                    'persona_id' => $personaId,
                    'tipo_gestor' => self::nullableStr($input['tipo_gestor'] ?? 'campo') ?: 'campo',
                    'campo' => self::activoVal($input['puede_gestionar_campo'] ?? 1),
                    'telefonica' => self::activoVal($input['puede_gestionar_telefonica'] ?? 1),
                    'capacidad' => max(1, self::intVal($input['capacidad_visitas_dia'] ?? 4)),
                    'id' => $id,
                ]);
                return ['success' => true, 'mensaje' => 'Gestor actualizado.', 'id' => $id];
            }
            $db->CRUD("
                INSERT INTO atlas_gestores_operativos (persona_id, tipo_gestor, puede_gestionar_campo, puede_gestionar_telefonica, capacidad_visitas_dia, activo)
                VALUES (:persona_id, :tipo_gestor, :campo, :telefonica, :capacidad, 1)
            ", [
                'persona_id' => $personaId,
                'tipo_gestor' => self::nullableStr($input['tipo_gestor'] ?? 'campo') ?: 'campo',
                'campo' => self::activoVal($input['puede_gestionar_campo'] ?? 1),
                'telefonica' => self::activoVal($input['puede_gestionar_telefonica'] ?? 1),
                'capacidad' => max(1, self::intVal($input['capacidad_visitas_dia'] ?? 4)),
            ]);
            return ['success' => true, 'mensaje' => 'Gestor habilitado.', 'id' => $db->lastInsertId()];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo habilitar gestor.', 'error' => $e->getMessage()];
        }
    }

    public static function eliminarGestorOperativo(array $input): array
    {
        $id = self::intVal($input['id'] ?? 0);
        if ($id <= 0) {
            return ['success' => false, 'mensaje' => 'Gestor invalido.'];
        }
        try {
            $db = new Database();
            $db->beginTransaction();
            $db->CRUD("UPDATE atlas_gestores_operativos SET activo = 0 WHERE id = :id", ['id' => $id]);
            $db->CRUD("UPDATE atlas_gestor_sucursales SET activo = 0 WHERE gestor_id = :id", ['id' => $id]);
            $db->commit();
            return ['success' => true, 'mensaje' => 'Gestor desactivado. Las rutas historicas se conservan.'];
        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo desactivar gestor.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarGestorSucursal(array $input): array
    {
        try {
            $db = new Database();
            $gestorId = self::intVal($input['gestor_id'] ?? 0);
            $fk = self::intVal($input['fk_sucursal'] ?? 0);
            if ($gestorId <= 0 || $fk <= 0) return ['success' => false, 'mensaje' => 'Selecciona gestor y sucursal.'];
            $gestor = $db->queryOne("SELECT persona_id FROM atlas_gestores_operativos WHERE id = :id AND activo = 1 LIMIT 1", ['id' => $gestorId]);
            if (!$gestor) return ['success' => false, 'mensaje' => 'Gestor no encontrado.'];
            $db->CRUD("
                INSERT INTO atlas_gestor_sucursales (gestor_id, persona_id, fk_sucursal, tipo_cobertura, es_principal, activo)
                VALUES (:gestor_id, :persona_id, :fk_sucursal, :tipo, :principal, 1)
                ON DUPLICATE KEY UPDATE activo = 1, tipo_cobertura = VALUES(tipo_cobertura), es_principal = VALUES(es_principal)
            ", [
                'gestor_id' => $gestorId,
                'persona_id' => (int)$gestor['persona_id'],
                'fk_sucursal' => $fk,
                'tipo' => self::nullableStr($input['tipo_cobertura'] ?? 'principal') ?: 'principal',
                'principal' => self::activoVal($input['es_principal'] ?? 0),
            ]);
            return ['success' => true, 'mensaje' => 'Sucursal asignada al gestor.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo asignar sucursal.', 'error' => $e->getMessage()];
        }
    }

    public static function eliminarGestorSucursal(array $input): array
    {
        try {
            $db = new Database();
            $db->CRUD("UPDATE atlas_gestor_sucursales SET activo = 0 WHERE id = :id", ['id' => self::intVal($input['id'] ?? 0)]);
            return ['success' => true, 'mensaje' => 'Sucursal desvinculada.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo desvincular.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarRutaSucursal(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarRutasGestoresAtlas($db);
            $rutaId = self::intVal($input['ruta_id'] ?? 0);
            $fk = self::intVal($input['fk_sucursal'] ?? 0);
            if ($rutaId <= 0 || $fk <= 0) return ['success' => false, 'mensaje' => 'Selecciona ruta y sucursal.'];
            $ruta = $db->queryOne("SELECT fecha_ruta, fecha_inicio, fecha_fin FROM atlas_rutas_gestores WHERE id = :id AND activo = 1 LIMIT 1", ['id' => $rutaId]) ?: [];
            $rutaInicio = self::nullableStr($ruta['fecha_inicio'] ?? null) ?: self::nullableStr($ruta['fecha_ruta'] ?? null);
            $rutaFin = self::nullableStr($ruta['fecha_fin'] ?? null) ?: $rutaInicio;
            $fechaInicioVisita = self::nullableStr($input['fecha_inicio_visita'] ?? null) ?: $rutaInicio;
            $fechaFinVisita = self::nullableStr($input['fecha_fin_visita'] ?? null) ?: $fechaInicioVisita;
            if ($rutaInicio && $fechaInicioVisita && $fechaInicioVisita < $rutaInicio) {
                return ['success' => false, 'mensaje' => 'La visita inicia antes de la fecha global de la ruta.'];
            }
            if ($rutaFin && $fechaFinVisita && $fechaFinVisita > $rutaFin) {
                return ['success' => false, 'mensaje' => 'La visita termina despues de la fecha global de la ruta.'];
            }
            if ($fechaInicioVisita && $fechaFinVisita && $fechaInicioVisita > $fechaFinVisita) {
                return ['success' => false, 'mensaje' => 'La fecha fin de la visita no puede ser menor a su inicio.'];
            }
            if ($fechaInicioVisita && $fechaFinVisita && $fechaInicioVisita !== $fechaFinVisita) {
                return ['success' => false, 'mensaje' => 'Cada sucursal solo puede programarse por un dia dentro de la ruta. Crea otra ruta si necesita asistir otro dia.'];
            }
            $estanciaValor = max(1, self::intVal($input['estancia_valor'] ?? 45));
            $estanciaUnidad = self::nullableStr($input['estancia_unidad'] ?? null) ?: 'minutos';
            $estanciaUnidad = in_array($estanciaUnidad, ['minutos', 'horas'], true) ? $estanciaUnidad : 'minutos';
            if ($estanciaUnidad === 'horas' && $estanciaValor > 5) {
                return ['success' => false, 'mensaje' => 'Si la estancia es mayor a 5, la unidad debe ser minutos.'];
            }
            $db->CRUD("
                INSERT INTO atlas_ruta_sucursales (
                    ruta_id, fk_sucursal, orden_visita, hora_programada, prioridad_visita,
                    criterio_prioridad_visita, fecha_inicio_visita, fecha_fin_visita,
                    hora_llegada, estancia_valor, estancia_unidad, hora_salida_sugerida, activo
                )
                VALUES (
                    :ruta, :fk, :orden, :hora, :prioridad,
                    :criterio, :fecha_inicio, :fecha_fin,
                    :hora_llegada, :estancia_valor, :estancia_unidad, :hora_salida, 1
                )
            ", [
                'ruta' => $rutaId,
                'fk' => $fk,
                'orden' => max(1, self::intVal($input['orden_visita'] ?? 1)),
                'hora' => self::nullableHoraRuta($input['hora_programada'] ?? null),
                'prioridad' => self::nullableStr($input['prioridad'] ?? null) ?: 'media',
                'criterio' => self::nullableStr($input['criterio_prioridad'] ?? null) ?: 'enganches',
                'fecha_inicio' => $fechaInicioVisita,
                'fecha_fin' => $fechaFinVisita,
                'hora_llegada' => self::nullableHoraRuta($input['hora_llegada'] ?? null),
                'estancia_valor' => $estanciaValor,
                'estancia_unidad' => $estanciaUnidad,
                'hora_salida' => self::nullableHoraRuta($input['hora_salida_sugerida'] ?? null),
            ]);
            return ['success' => true, 'mensaje' => 'Sucursal agregada a la ruta.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo agregar sucursal.', 'error' => $e->getMessage()];
        }
    }

    public static function actualizarEstatusRutaGestor(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarRutasGestoresAtlas($db);
            $id = self::intVal($input['id'] ?? 0);
            $estatus = self::nullableStr($input['estatus'] ?? 'borrador') ?: 'borrador';
            $ruta = $db->queryOne("
                SELECT fecha_ruta, fecha_inicio, fecha_fin, estatus
                FROM atlas_rutas_gestores
                WHERE id = :id AND activo = 1
                LIMIT 1
            ", ['id' => $id]);
            if (!$ruta) {
                return ['success' => false, 'mensaje' => 'Ruta no encontrada.'];
            }
            $fechaInicio = self::nullableStr($ruta['fecha_inicio'] ?? null) ?: self::nullableStr($ruta['fecha_ruta'] ?? null);
            $fechaFin = self::nullableStr($ruta['fecha_fin'] ?? null) ?: self::nullableStr($ruta['fecha_ruta'] ?? null) ?: $fechaInicio;
            $validacionPublicacion = self::validarPublicacionRutaGestor($db, $estatus, $fechaInicio, $fechaFin);
            if (!$validacionPublicacion['success']) {
                return $validacionPublicacion;
            }
            $db->CRUD("UPDATE atlas_rutas_gestores SET estatus = :estatus WHERE id = :id", [
                'estatus' => $estatus,
                'id' => $id,
            ]);
            return ['success' => true, 'mensaje' => 'Estatus actualizado.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo actualizar estatus.', 'error' => $e->getMessage()];
        }
    }

    public static function eliminarRutaSucursal(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarRutasGestoresAtlas($db);
            $id = self::intVal($input['id'] ?? 0);
            if (self::rutaSucursalTieneGestion($db, $id)) {
                return [
                    'success' => false,
                    'mensaje' => 'No se puede remover esta sucursal porque ya tiene gestion registrada en la ruta.',
                ];
            }
            $db->CRUD("UPDATE atlas_ruta_sucursales SET activo = 0 WHERE id = :id", ['id' => $id]);
            return ['success' => true, 'mensaje' => 'Sucursal removida de la ruta.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo remover sucursal.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarRutaCredito(array $input): array
    {
        return ['success' => true, 'mensaje' => 'Los crÃ©ditos se calculan automÃ¡ticamente por sucursal pendiente operativa.'];
    }

    public static function guardarOrdenRutaSucursales(array $input): array
    {
        $rutaId = self::intVal($input['ruta_id'] ?? 0);
        $idsRaw = is_array($input['ids'] ?? null) ? $input['ids'] : [];
        $ids = array_values(array_filter(array_map('intval', $idsRaw), static fn($id) => $id > 0));
        if ($rutaId <= 0 || !$ids) {
            return ['success' => false, 'mensaje' => 'Orden de ruta invalido.'];
        }

        $db = new Database();
        try {
            $db->beginTransaction();
            foreach ($ids as $idx => $id) {
                $db->CRUD("
                    UPDATE atlas_ruta_sucursales
                    SET orden_visita = :orden
                    WHERE id = :id
                      AND ruta_id = :ruta_id
                      AND activo = 1
                ", [
                    'orden' => $idx + 1,
                    'id' => $id,
                    'ruta_id' => $rutaId,
                ]);
            }
            $db->commit();
            return ['success' => true, 'mensaje' => 'Orden de visita actualizado.'];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo guardar el orden de visita.', 'error' => $e->getMessage()];
        }
    }

    public static function eliminarRutaCredito(array $input): array
    {
        return ['success' => true, 'mensaje' => 'Los creditos de la ruta se calculan automaticamente por sucursal.'];
    }

    public static function importarPresupuestoMensual(int $anio, int $mes, string $archivoOriginal, array $filas, int $usuarioId = 0): array
    {
        if ($anio < 2000 || $mes < 1 || $mes > 12) {
            return ['success' => false, 'mensaje' => 'Selecciona un aÃ±o y mes validos.'];
        }
        if (!$filas) {
            return ['success' => false, 'mensaje' => 'El Excel no contiene registros.'];
        }

        $db = new Database();
        self::asegurarPresupuestosAtlas($db);

        try {
            $db->beginTransaction();

            $actual = $db->queryOne("
                SELECT id
                FROM atlas_presupuestos_mensuales
                WHERE anio = :anio
                  AND mes = :mes
                  AND activo = 1
                LIMIT 1
            ", ['anio' => $anio, 'mes' => $mes]);

            if ($actual) {
                $db->rollback();
                return [
                    'success' => false,
                    'mensaje' => 'Ya existe un presupuesto cargado para ' . self::nombreMes($mes) . " $anio. Elimina el presupuesto actual antes de volver a cargarlo.",
                ];
            }

            $esRecarga = (bool)$actual;
            if ($actual) {
                $presupuestoId = (int)$actual['id'];
                $db->CRUD("
                    UPDATE atlas_presupuestos_mensuales
                    SET nombre_mes = :nombre_mes,
                        archivo_original = :archivo_original,
                        activo = 1,
                        updated_by = :usuario
                    WHERE id = :id
                ", [
                    'nombre_mes' => self::nombreMes($mes),
                    'archivo_original' => mb_substr($archivoOriginal, 0, 220),
                    'usuario' => $usuarioId ?: null,
                    'id' => $presupuestoId,
                ]);
            } else {
                $db->CRUD("
                    INSERT INTO atlas_presupuestos_mensuales
                        (anio, mes, nombre_mes, archivo_original, created_by, updated_by)
                    VALUES
                        (:anio, :mes, :nombre_mes, :archivo_original, :usuario, :usuario)
                ", [
                    'anio' => $anio,
                    'mes' => $mes,
                    'nombre_mes' => self::nombreMes($mes),
                    'archivo_original' => mb_substr($archivoOriginal, 0, 220),
                    'usuario' => $usuarioId ?: null,
                ]);
                $presupuestoId = $db->lastInsertId();
            }

            $db->CRUD("UPDATE atlas_presupuesto_sucursal_detalle SET activo = 0 WHERE presupuesto_id = :id", ['id' => $presupuestoId]);

            $sucursalesEsperadas = self::getSucursalesTemplatePresupuesto();
            $esperadasPorFk = [];
            foreach ($sucursalesEsperadas as $sucursalEsperada) {
                $fkEsperada = (int)($sucursalEsperada['fk_sucursal'] ?? 0);
                if ($fkEsperada > 0) {
                    $esperadasPorFk[$fkEsperada] = $sucursalEsperada;
                }
            }

            $filasRecibidas = count($filas);
            $filasUnicas = [];
            $duplicadas = [];
            $extras = [];
            $omitidasInvalidas = 0;
            foreach ($filas as $idx => $fila) {
                $excelRow = (int)($fila['_excel_row'] ?? ($idx + 2));
                $fkSucursal = (int)($fila['fk_sucursal'] ?? 0);
                if ($fkSucursal <= 0) {
                    $omitidasInvalidas++;
                    continue;
                }
                if (isset($filasUnicas[$fkSucursal])) {
                    $duplicadas[] = [
                        'fila' => $excelRow,
                        'fk_sucursal' => $fkSucursal,
                        'sucursal' => self::strVal($fila['sucursal'] ?? ''),
                        'distribuidor' => self::strVal($fila['distribuidor'] ?? ''),
                    ];
                }
                if ($esperadasPorFk && !isset($esperadasPorFk[$fkSucursal])) {
                    $extras[] = [
                        'fila' => $excelRow,
                        'fk_sucursal' => $fkSucursal,
                        'sucursal' => self::strVal($fila['sucursal'] ?? ''),
                        'distribuidor' => self::strVal($fila['distribuidor'] ?? ''),
                    ];
                    continue;
                }
                $filasUnicas[$fkSucursal] = $fila;
            }

            $faltantes = [];
            foreach ($esperadasPorFk as $fkEsperada => $sucursalEsperada) {
                if (!isset($filasUnicas[$fkEsperada])) {
                    $faltantes[] = [
                        'fk_sucursal' => (int)$fkEsperada,
                        'sucursal' => self::strVal($sucursalEsperada['sucursal'] ?? ''),
                        'distribuidor' => self::strVal($sucursalEsperada['distribuidor'] ?? ''),
                    ];
                }
            }

            $importadas = 0;
            foreach ($filasUnicas as $fkSucursal => $fila) {

                $datos = [
                    'presupuesto_id' => $presupuestoId,
                    'fk_sucursal' => (int)$fkSucursal,
                    'sucursal' => self::strVal($fila['sucursal'] ?? ''),
                    'diversificacion' => self::strVal($fila['diversificacion'] ?? ''),
                    'distribuidor' => self::strVal($fila['distribuidor'] ?? ''),
                    'divisional' => self::strVal($fila['divisional'] ?? ''),
                    'regional' => self::strVal($fila['regional'] ?? ''),
                    'supervisor' => self::strVal($fila['supervisor'] ?? ''),
                    'asesor' => self::strVal($fila['asesor'] ?? ''),
                    'estado' => self::strVal($fila['estado'] ?? ''),
                    'promedio_creditos' => self::decimalPresupuesto($fila['promedio_creditos'] ?? null),
                    'clasificacion' => self::strVal($fila['clasificacion'] ?? ''),
                    'meta_creditos' => self::decimalPresupuesto($fila['meta_creditos'] ?? 0),
                    'meta_cash' => self::decimalPresupuesto($fila['meta_cash'] ?? 0),
                    'usuario' => $usuarioId ?: null,
                ];

                $db->CRUD("
                    INSERT INTO atlas_presupuesto_sucursal_detalle (
                        presupuesto_id, fk_sucursal, sucursal, diversificacion, distribuidor, divisional,
                        regional, supervisor, asesor, estado, promedio_creditos, clasificacion,
                        meta_creditos, meta_cash, activo, updated_by
                    ) VALUES (
                        :presupuesto_id, :fk_sucursal, :sucursal, :diversificacion, :distribuidor, :divisional,
                        :regional, :supervisor, :asesor, :estado, :promedio_creditos, :clasificacion,
                        :meta_creditos, :meta_cash, 1, :usuario
                    )
                    ON DUPLICATE KEY UPDATE
                        sucursal = VALUES(sucursal),
                        diversificacion = VALUES(diversificacion),
                        distribuidor = VALUES(distribuidor),
                        divisional = VALUES(divisional),
                        regional = VALUES(regional),
                        supervisor = VALUES(supervisor),
                        asesor = VALUES(asesor),
                        estado = VALUES(estado),
                        promedio_creditos = VALUES(promedio_creditos),
                        clasificacion = VALUES(clasificacion),
                        meta_creditos = VALUES(meta_creditos),
                        meta_cash = VALUES(meta_cash),
                        activo = 1,
                        updated_by = VALUES(updated_by)
                ", $datos);
                $importadas++;
            }

            self::recalcularTotalesPresupuesto($db, $presupuestoId);
            $resumenImportacion = [
                'filas_leidas' => $filasRecibidas,
                'sucursales_esperadas' => count($esperadasPorFk),
                'registros_importados' => $importadas,
                'duplicados' => count($duplicadas),
                'extras' => count($extras),
                'faltantes' => count($faltantes),
                'omitidos_invalidos' => $omitidasInvalidas,
                'total_omitidos' => count($duplicadas) + count($extras) + count($faltantes) + $omitidasInvalidas,
                'detalle_duplicados' => $duplicadas,
                'detalle_extras' => $extras,
                'detalle_faltantes' => $faltantes,
            ];
            self::registrarBitacoraPresupuesto($db, [
                'presupuesto_id' => $presupuestoId,
                'anio' => $anio,
                'mes' => $mes,
                'evento' => $esRecarga ? 'recarga' : 'carga',
                'descripcion' => $esRecarga ? 'Presupuesto mensual recargado desde Excel.' : 'Presupuesto mensual cargado desde Excel.',
                'archivo_original' => mb_substr($archivoOriginal, 0, 220),
                'total_sucursales' => $importadas,
                'usuario_id' => $usuarioId ?: null,
                'payload_json' => $resumenImportacion,
            ]);
            $db->commit();

            return [
                'success' => true,
                'mensaje' => 'Presupuesto mensual importado correctamente.',
                'datos' => [
                    'presupuesto_id' => $presupuestoId,
                    'registros_importados' => $importadas,
                    'resumen_importacion' => $resumenImportacion,
                    'anio' => $anio,
                    'mes' => $mes,
                ],
            ];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo guardar el presupuesto mensual.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarPresupuestoSucursal(array $payload): array
    {
        $id = (int)($payload['id'] ?? 0);
        if ($id <= 0) {
            return ['success' => false, 'mensaje' => 'Registro invalido.'];
        }

        try {
            $db = new Database();
            self::asegurarPresupuestosAtlas($db);
            $row = $db->queryOne("
                SELECT d.id, d.presupuesto_id, d.fk_sucursal, d.sucursal, d.meta_creditos, d.meta_cash,
                       p.anio, p.mes
                FROM atlas_presupuesto_sucursal_detalle d
                INNER JOIN atlas_presupuestos_mensuales p ON p.id = d.presupuesto_id AND p.activo = 1
                WHERE d.id = :id
                  AND d.activo = 1
                LIMIT 1
            ", ['id' => $id]);
            if (!$row) {
                return ['success' => false, 'mensaje' => 'No se encontro el registro.'];
            }

            $metaCreditosNueva = self::decimalPresupuesto($payload['meta_creditos'] ?? 0);
            $metaCashNueva = self::decimalPresupuesto($payload['meta_cash'] ?? 0);
            $db->CRUD("
                UPDATE atlas_presupuesto_sucursal_detalle
                SET meta_creditos = :meta_creditos,
                    meta_cash = :meta_cash,
                    observaciones = :observaciones,
                    updated_by = :usuario
                WHERE id = :id
            ", [
                'meta_creditos' => $metaCreditosNueva,
                'meta_cash' => $metaCashNueva,
                'observaciones' => self::strVal($payload['observaciones'] ?? ''),
                'usuario' => (int)($payload['usuario_id'] ?? 0) ?: null,
                'id' => $id,
            ]);

            self::recalcularTotalesPresupuesto($db, (int)$row['presupuesto_id']);
            self::registrarBitacoraPresupuesto($db, [
                'presupuesto_id' => (int)$row['presupuesto_id'],
                'anio' => (int)$row['anio'],
                'mes' => (int)$row['mes'],
                'evento' => 'modificacion_sucursal',
                'descripcion' => 'Meta mensual de sucursal modificada.',
                'sucursal_detalle_id' => $id,
                'fk_sucursal' => (int)$row['fk_sucursal'],
                'meta_creditos_anterior' => (float)$row['meta_creditos'],
                'meta_creditos_nueva' => $metaCreditosNueva,
                'meta_cash_anterior' => (float)$row['meta_cash'],
                'meta_cash_nueva' => $metaCashNueva,
                'usuario_id' => (int)($payload['usuario_id'] ?? 0) ?: null,
                'payload_json' => ['sucursal' => $row['sucursal'] ?? ''],
            ]);

            return ['success' => true, 'mensaje' => 'Meta actualizada.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo actualizar la meta.', 'error' => $e->getMessage()];
        }
    }

    public static function eliminarPresupuestoMes(array $payload): array
    {
        $id = (int)($payload['id'] ?? 0);
        if ($id <= 0) {
            return ['success' => false, 'mensaje' => 'Presupuesto invalido.'];
        }

        $db = new Database();
        self::asegurarPresupuestosAtlas($db);

        try {
            $presupuesto = $db->queryOne("SELECT id, anio, mes, nombre_mes, archivo_original, total_sucursales FROM atlas_presupuestos_mensuales WHERE id = :id AND activo = 1 LIMIT 1", ['id' => $id]);
            if (!$presupuesto) {
                return ['success' => false, 'mensaje' => 'No se encontro el presupuesto.'];
            }
            if (!self::presupuestoMesEsFuturo((int)$presupuesto['anio'], (int)$presupuesto['mes'])) {
                return ['success' => false, 'mensaje' => 'Solo se pueden borrar meses que aun no llegan.'];
            }

            $db->beginTransaction();
            self::registrarBitacoraPresupuesto($db, [
                'presupuesto_id' => $id,
                'anio' => (int)$presupuesto['anio'],
                'mes' => (int)$presupuesto['mes'],
                'evento' => 'eliminacion',
                'descripcion' => 'Presupuesto futuro eliminado.',
                'archivo_original' => $presupuesto['archivo_original'] ?? null,
                'total_sucursales' => (int)($presupuesto['total_sucursales'] ?? 0),
                'usuario_id' => (int)($payload['usuario_id'] ?? 0) ?: null,
            ]);
            $db->CRUD("DELETE FROM atlas_presupuesto_sucursal_detalle WHERE presupuesto_id = :id", ['id' => $id]);
            $db->CRUD("DELETE FROM atlas_presupuestos_mensuales WHERE id = :id", ['id' => $id]);
            $db->commit();

            return ['success' => true, 'mensaje' => 'Presupuesto futuro borrado.'];
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo borrar el presupuesto.', 'error' => $e->getMessage()];
        }
    }

    public static function getSucursalesTemplatePresupuesto(): array
    {
        try {
            $db = new Database();
            self::asegurarColumnasPasoSucursal($db);
            self::asegurarResponsablesPersonaAtlas($db);
            return $db->queryAll("
                SELECT
                    s.fk_sucursal,
                    '' AS diversificacion,
                    COALESCE(d.nombre, '') AS distribuidor,
                    COALESCE(s.sucursal, '') AS sucursal,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', pdvl.nombres, pdvl.segundo_nombre, pdvl.apellidop, pdvl.apellidom)), ''), dvl.nombre, '') AS divisional,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', preg.nombres, preg.segundo_nombre, preg.apellidop, preg.apellidom)), ''), reg.nombre, '') AS regional,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', psup.nombres, psup.segundo_nombre, psup.apellidop, psup.apellidom)), ''), sup.nombre, '') AS supervisor,
                    COALESCE(NULLIF(TRIM(CONCAT_WS(' ', pase.nombres, pase.segundo_nombre, pase.apellidop, pase.apellidom)), ''), ase.nombre, '') AS asesor,
                    COALESCE(s.estado, '') AS estado,
                    COALESCE(cls.nombre, '') AS clasificacion
                FROM atlas_catalogo_sucursales s
                LEFT JOIN atlas_catalogo_distribuidores d ON d.id = s.distribuidor_id
                LEFT JOIN atlas_catalogo_divisionales dvl ON dvl.id = s.divisional_id
                LEFT JOIN persona pdvl ON pdvl.id = s.divisional_persona_id
                LEFT JOIN atlas_catalogo_regionales reg ON reg.id = s.regional_id
                LEFT JOIN persona preg ON preg.id = s.regional_persona_id
                LEFT JOIN atlas_catalogo_supervisores sup ON sup.id = s.supervisor_id
                LEFT JOIN persona psup ON psup.id = s.supervisor_persona_id
                LEFT JOIN atlas_catalogo_asesores ase ON ase.id = s.asesor_id
                LEFT JOIN persona pase ON pase.id = s.asesor_persona_id
                LEFT JOIN atlas_catalogo_clasificaciones cls ON cls.id = s.clasificacion_id
                WHERE s.activo = 1
                ORDER BY s.sucursal ASC, s.fk_sucursal ASC
            ");
        } catch (\Throwable $e) {
            return [];
        }
    }

    private static function recalcularTotalesPresupuesto(Database $db, int $presupuestoId): void
    {
        $tot = $db->queryOne("
            SELECT
                COUNT(*) AS total_sucursales,
                COALESCE(SUM(meta_creditos), 0) AS total_creditos,
                COALESCE(SUM(meta_cash), 0) AS total_cash
            FROM atlas_presupuesto_sucursal_detalle
            WHERE presupuesto_id = :id
              AND activo = 1
        ", ['id' => $presupuestoId]) ?: [];

        $db->CRUD("
            UPDATE atlas_presupuestos_mensuales
            SET total_sucursales = :total_sucursales,
                total_creditos = :total_creditos,
                total_cash = :total_cash
            WHERE id = :id
        ", [
            'total_sucursales' => (int)($tot['total_sucursales'] ?? 0),
            'total_creditos' => (float)($tot['total_creditos'] ?? 0),
            'total_cash' => (float)($tot['total_cash'] ?? 0),
            'id' => $presupuestoId,
        ]);
    }

    private static function presupuestoMesEsFuturo(int $anio, int $mes): bool
    {
        try {
            $tz = new \DateTimeZone('America/Mexico_City');
            $objetivo = new \DateTimeImmutable(sprintf('%04d-%02d-01 00:00:00', $anio, $mes), $tz);
            $actual = new \DateTimeImmutable('first day of this month 00:00:00', $tz);
            return $objetivo > $actual;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function nombreMes(int $mes): string
    {
        $nombres = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        return $nombres[$mes] ?? 'Mes';
    }

    private static function decimalPresupuesto($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        if (is_numeric($value)) {
            return (float)$value;
        }
        $text = preg_replace('/[^0-9.\-]/', '', str_replace(',', '', (string)$value));
        return is_numeric($text) ? (float)$text : 0.0;
    }

    private static function sincronizarTiposGestionDesdeGestiones(Database $db): void
    {
        $rows = $db->queryAll("
            SELECT DISTINCT TRIM(tipo_gestion) AS nombre
            FROM atlas_catalogo_gestion
            WHERE TRIM(COALESCE(tipo_gestion, '')) <> ''
            ORDER BY nombre ASC
        ");
        foreach ($rows as $idx => $row) {
            $nombre = self::strVal($row['nombre'] ?? '');
            if ($nombre === '') {
                continue;
            }
            $tipo = self::obtenerOCrearTipoGestionComercial($db, $nombre);
            if (!empty($tipo['id'])) {
                $db->CRUD(
                    "UPDATE atlas_catalogo_gestion
                     SET tipo_gestion_id = :tipo_id
                     WHERE TRIM(COALESCE(tipo_gestion, '')) = :nombre
                       AND (tipo_gestion_id IS NULL OR tipo_gestion_id = 0)",
                    ['tipo_id' => (int)$tipo['id'], 'nombre' => $nombre]
                );
            }
        }
    }

    public static function getCatalogosComerciales(): array
    {
        try {
            $db = new Database();
            self::asegurarTablasCatalogosComerciales($db);
            $dictamenes = $db->queryAll("
                SELECT id, codigo_estatus, clave, nombre, objetivo, orden, activo, estado_registro,
                       fecha_actualizacion AS fecha_actualizacion_raw,
                       DATE_FORMAT(fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_catalogo_dictamen
                ORDER BY orden ASC, nombre ASC, id ASC
            ");
            $subestatus = $db->queryAll("
                SELECT s.id, s.dictamen_id, d.nombre AS dictamen_nombre, d.codigo_estatus, s.clave, s.nombre,
                       s.orden, s.activo, s.estado_registro,
                       s.fecha_actualizacion AS fecha_actualizacion_raw,
                       DATE_FORMAT(s.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_dictamen_sub_estatus s
                INNER JOIN atlas_catalogo_dictamen d ON d.id = s.dictamen_id
                ORDER BY s.orden ASC, d.orden ASC, s.nombre ASC, s.id ASC
            ");
            $tiposGestion = $db->queryAll("
                SELECT id, clave, nombre, orden, activo, estado_registro,
                       fecha_actualizacion AS fecha_actualizacion_raw,
                       DATE_FORMAT(fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_catalogo_tipos_gestion
                ORDER BY orden ASC, nombre ASC, id ASC
            ");
            $gestiones = $db->queryAll("
                SELECT g.id, g.dictamen_id, d.nombre AS dictamen_nombre,
                       COALESCE(NULLIF(g.subestatus_id, 0), g.sub_estatus_id) AS subestatus_id,
                       s.nombre AS subestatus_nombre,
                       g.tipo_gestion_id, COALESCE(tg.nombre, g.tipo_gestion) AS tipo_gestion, g.clave, g.nombre, g.ventana_complementaria, g.campos_adicionales,
                       g.requiere_fecha, g.permite_comentario, g.orden, g.activo, g.estado_registro,
                       g.fecha_actualizacion AS fecha_actualizacion_raw,
                       DATE_FORMAT(g.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_catalogo_gestion g
                INNER JOIN atlas_catalogo_dictamen d ON d.id = g.dictamen_id
                INNER JOIN atlas_dictamen_sub_estatus s ON s.id = COALESCE(NULLIF(g.subestatus_id, 0), g.sub_estatus_id)
                LEFT JOIN atlas_catalogo_tipos_gestion tg ON tg.id = g.tipo_gestion_id
                ORDER BY d.orden ASC, s.orden ASC, g.orden ASC, g.nombre ASC, g.id ASC
            ");
            self::aplicarFechaActualizacionCdmx($dictamenes);
            self::aplicarFechaActualizacionCdmx($subestatus);
            self::aplicarFechaActualizacionCdmx($tiposGestion);
            self::aplicarFechaActualizacionCdmx($gestiones);

            return [
                'success' => true,
                'mensaje' => 'CatÃƒÂ¡logos comerciales obtenidos.',
                'datos' => [
                    'dictamenes' => $dictamenes,
                    'subestatus' => $subestatus,
                    'tipos_gestion' => $tiposGestion,
                    'gestiones' => $gestiones,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudieron obtener los catÃƒÂ¡logos comerciales.', 'error' => $e->getMessage(), 'datos' => ['dictamenes' => [], 'subestatus' => [], 'tipos_gestion' => [], 'gestiones' => []]];
        }
    }

    public static function guardarCatalogoComercial(array $input): array
    {
        try {
            $db = new Database();
            self::asegurarTablasCatalogosComerciales($db);
            $tipo = strtolower(self::strVal($input['tipo'] ?? ''));
            if ($tipo === 'dictamen') {
                return self::guardarDictamenComercial($db, $input);
            }
            if ($tipo === 'subestatus') {
                return self::guardarSubestatusComercial($db, $input);
            }
            if ($tipo === 'tipo_gestion') {
                return self::guardarTipoGestionComercial($db, $input);
            }
            if ($tipo === 'gestion') {
                return self::guardarGestionComercial($db, $input);
            }
            return ['success' => false, 'mensaje' => 'Tipo de catÃƒÂ¡logo no vÃƒÂ¡lido.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar el catÃƒÂ¡logo comercial.', 'error' => $e->getMessage()];
        }
    }

    private static function guardarDictamenComercial(Database $db, array $input): array
    {
        $id = self::intVal($input['id'] ?? 0);
        $nombre = self::strVal($input['nombre'] ?? '');
        if ($nombre === '') {
            return ['success' => false, 'mensaje' => 'Captura el nombre del estatus.'];
        }
        $codigo = self::nullableStr($input['codigo_estatus'] ?? null);
        $clave = self::nullableStr($input['clave'] ?? null) ?: self::claveDesdeTexto($nombre);
        $dup = $db->queryOne("SELECT id FROM atlas_catalogo_dictamen WHERE (LOWER(nombre)=LOWER(:nombre) OR LOWER(clave)=LOWER(:clave)) AND id <> :id LIMIT 1", ['nombre' => $nombre, 'clave' => $clave, 'id' => $id]);
        if ($dup) {
            return ['success' => false, 'mensaje' => 'Ya existe un estatus con el mismo nombre o clave.'];
        }
        $datos = [
            'codigo_estatus' => $codigo,
            'clave' => $clave,
            'nombre' => $nombre,
            'objetivo' => self::nullableStr($input['objetivo'] ?? null),
            'orden' => self::intVal($input['orden'] ?? 0),
            'activo' => self::activoVal($input['activo'] ?? 1),
            'estado_registro' => self::estadoCatalogoVal($input['estado_registro'] ?? 'publicado'),
        ];
        if ($datos['orden'] <= 0) {
            $row = $db->queryOne("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM atlas_catalogo_dictamen");
            $datos['orden'] = (int)($row['siguiente'] ?? 1);
        }
        return self::guardarFilaCatalogo($db, 'atlas_catalogo_dictamen', $datos, $id, 'Estatus');
    }

    private static function guardarSubestatusComercial(Database $db, array $input): array
    {
        $id = self::intVal($input['id'] ?? 0);
        $dictamenId = self::intVal($input['dictamen_id'] ?? 0);
        $nombre = self::strVal($input['nombre'] ?? '');
        if ($dictamenId <= 0 || $nombre === '') {
            return ['success' => false, 'mensaje' => 'Selecciona estatus y captura subestatus.'];
        }
        $clave = self::nullableStr($input['clave'] ?? null) ?: self::claveDesdeTexto($nombre);
        $dup = $db->queryOne("SELECT id FROM atlas_dictamen_sub_estatus WHERE dictamen_id = :dictamen_id AND LOWER(nombre)=LOWER(:nombre) AND id <> :id LIMIT 1", ['dictamen_id' => $dictamenId, 'nombre' => $nombre, 'id' => $id]);
        if ($dup) {
            return ['success' => false, 'mensaje' => 'Ya existe ese subestatus dentro del estatus.'];
        }
        $datos = [
            'dictamen_id' => $dictamenId,
            'clave' => $clave,
            'nombre' => $nombre,
            'orden' => self::intVal($input['orden'] ?? 0),
            'activo' => self::activoVal($input['activo'] ?? 1),
            'estado_registro' => self::estadoCatalogoVal($input['estado_registro'] ?? 'publicado'),
        ];
        if ($datos['orden'] <= 0) {
            $row = $db->queryOne("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM atlas_dictamen_sub_estatus WHERE dictamen_id = :id", ['id' => $dictamenId]);
            $datos['orden'] = (int)($row['siguiente'] ?? 1);
        }
        return self::guardarFilaCatalogo($db, 'atlas_dictamen_sub_estatus', $datos, $id, 'Subestatus');
    }

    private static function guardarTipoGestionComercial(Database $db, array $input): array
    {
        $id = self::intVal($input['id'] ?? 0);
        $nombre = self::strVal($input['nombre'] ?? '');
        if ($nombre === '') {
            return ['success' => false, 'mensaje' => 'Captura el tipo de gestiÃƒÂ³n.'];
        }
        $clave = self::nullableStr($input['clave'] ?? null) ?: self::claveDesdeTexto($nombre);
        $dup = $db->queryOne("SELECT id FROM atlas_catalogo_tipos_gestion WHERE (LOWER(nombre)=LOWER(:nombre) OR LOWER(clave)=LOWER(:clave)) AND id <> :id LIMIT 1", ['nombre' => $nombre, 'clave' => $clave, 'id' => $id]);
        if ($dup) {
            return ['success' => false, 'mensaje' => 'Ya existe ese tipo de gestiÃƒÂ³n.'];
        }
        $datos = [
            'clave' => $clave,
            'nombre' => $nombre,
            'orden' => self::intVal($input['orden'] ?? 0),
            'activo' => self::activoVal($input['activo'] ?? 1),
            'estado_registro' => self::estadoCatalogoVal($input['estado_registro'] ?? 'publicado'),
        ];
        if ($datos['orden'] <= 0) {
            $row = $db->queryOne("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM atlas_catalogo_tipos_gestion");
            $datos['orden'] = (int)($row['siguiente'] ?? 1);
        }
        return self::guardarFilaCatalogo($db, 'atlas_catalogo_tipos_gestion', $datos, $id, 'Tipo de gestiÃƒÂ³n');
    }

    private static function guardarGestionComercial(Database $db, array $input): array
    {
        $id = self::intVal($input['id'] ?? 0);
        $subestatusId = self::intVal($input['subestatus_id'] ?? 0);
        $sub = $subestatusId > 0 ? $db->queryOne("SELECT id, dictamen_id FROM atlas_dictamen_sub_estatus WHERE id = :id LIMIT 1", ['id' => $subestatusId]) : null;
        $nombre = self::strVal($input['nombre'] ?? '');
        if (!$sub || $nombre === '') {
            return ['success' => false, 'mensaje' => 'Selecciona subestatus y captura gestiÃƒÂ³n.'];
        }
        $tipoGestionId = self::intVal($input['tipo_gestion_id'] ?? 0);
        $tipoGestionNombre = self::nullableStr($input['tipo_gestion'] ?? null);
        if ($tipoGestionId > 0) {
            $tipo = $db->queryOne("SELECT id, nombre FROM atlas_catalogo_tipos_gestion WHERE id = :id LIMIT 1", ['id' => $tipoGestionId]);
            if (!$tipo) {
                return ['success' => false, 'mensaje' => 'Selecciona un tipo de gestiÃƒÂ³n vÃƒÂ¡lido.'];
            }
            $tipoGestionNombre = (string)$tipo['nombre'];
        } elseif ($tipoGestionNombre !== null) {
            $tipo = self::obtenerOCrearTipoGestionComercial($db, $tipoGestionNombre);
            $tipoGestionId = (int)$tipo['id'];
            $tipoGestionNombre = (string)$tipo['nombre'];
        }
        $clave = self::nullableStr($input['clave'] ?? null) ?: self::claveDesdeTexto($nombre);
        $dup = $db->queryOne("SELECT id FROM atlas_catalogo_gestion WHERE COALESCE(NULLIF(subestatus_id, 0), sub_estatus_id) = :subestatus_id AND LOWER(nombre)=LOWER(:nombre) AND id <> :id LIMIT 1", ['subestatus_id' => $subestatusId, 'nombre' => $nombre, 'id' => $id]);
        if ($dup) {
            return ['success' => false, 'mensaje' => 'Ya existe esa gestiÃƒÂ³n dentro del subestatus.'];
        }
        $datos = [
            'dictamen_id' => (int)$sub['dictamen_id'],
            'sub_estatus_id' => $subestatusId,
            'subestatus_id' => $subestatusId,
            'tipo_gestion_id' => $tipoGestionId > 0 ? $tipoGestionId : null,
            'tipo_gestion' => $tipoGestionNombre,
            'clave' => $clave,
            'nombre' => $nombre,
            'ventana_complementaria' => self::nullableStr($input['ventana_complementaria'] ?? null),
            'campos_adicionales' => self::nullableStr($input['campos_adicionales'] ?? null),
            'requiere_fecha' => self::activoVal($input['requiere_fecha'] ?? 0),
            'permite_comentario' => self::activoVal($input['permite_comentario'] ?? 1),
            'orden' => self::intVal($input['orden'] ?? 0),
            'activo' => self::activoVal($input['activo'] ?? 1),
            'estado_registro' => self::estadoCatalogoVal($input['estado_registro'] ?? 'publicado'),
        ];
        if ($datos['orden'] <= 0) {
            $row = $db->queryOne("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM atlas_catalogo_gestion WHERE COALESCE(NULLIF(subestatus_id, 0), sub_estatus_id) = :id", ['id' => $subestatusId]);
            $datos['orden'] = (int)($row['siguiente'] ?? 1);
        }
        return self::guardarFilaCatalogo($db, 'atlas_catalogo_gestion', $datos, $id, 'GestiÃƒÂ³n');
    }

    public static function guardarCatalogosComercialesBloque(array $input): array
    {
        $filas = $input['filas'] ?? [];
        if (!is_array($filas) || !$filas) {
            return ['success' => false, 'mensaje' => 'No hay filas para guardar.'];
        }
        try {
            $db = new Database();
            self::asegurarTablasCatalogosComerciales($db);
            $errores = self::validarFilasComerciales($filas);
            if ($errores) {
                return ['success' => false, 'mensaje' => 'Corrige los errores antes de publicar.', 'errores' => $errores];
            }
            $resumen = ['dictamenes' => 0, 'subestatus' => 0, 'tipos_gestion' => 0, 'gestiones' => 0];
            $db->beginTransaction();
            foreach (array_values($filas) as $idx => $fila) {
                $orden = $idx + 1;
                $dictamenNombre = self::strVal($fila['estatus'] ?? $fila['dictamen'] ?? '');
                $dictamen = self::obtenerOCrearDictamenComercial($db, $dictamenNombre, self::nullableStr($fila['objetivo'] ?? null), $orden);
                if ($dictamen['creado']) $resumen['dictamenes']++;
                $sub = self::obtenerOCrearSubestatusComercial($db, (int)$dictamen['id'], self::strVal($fila['subestatus'] ?? $fila['sub_estatus'] ?? ''), $orden);
                if ($sub['creado']) $resumen['subestatus']++;
                $tipoGestion = null;
                $tipoGestionTxt = self::nullableStr($fila['tipo_gestion'] ?? null);
                if ($tipoGestionTxt !== null) {
                    $tipoGestion = self::obtenerOCrearTipoGestionComercial($db, $tipoGestionTxt);
                    if ($tipoGestion['creado']) $resumen['tipos_gestion']++;
                }
                $gestion = self::obtenerOCrearGestionComercial($db, (int)$dictamen['id'], (int)$sub['id'], [
                    'tipo_gestion_id' => $tipoGestion ? (int)$tipoGestion['id'] : null,
                    'tipo_gestion' => $tipoGestion ? (string)$tipoGestion['nombre'] : null,
                    'nombre' => self::strVal($fila['gestion'] ?? $fila['lista_desplegable_gestion'] ?? ''),
                    'ventana_complementaria' => self::nullableStr($fila['ventana_complementaria'] ?? null),
                    'campos_adicionales' => self::nullableStr($fila['campos_adicionales'] ?? null),
                    'requiere_fecha' => self::activoVal($fila['requiere_fecha'] ?? 0),
                    'permite_comentario' => self::activoVal($fila['permite_comentario'] ?? 1),
                    'orden' => $orden,
                ]);
                if ($gestion['creado']) $resumen['gestiones']++;
            }
            $db->commit();
            return ['success' => true, 'mensaje' => 'CatÃƒÂ¡logos comerciales publicados.', 'resumen' => $resumen];
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo publicar el bloque.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarOrdenCatalogosComerciales(array $input): array
    {
        $tipo = strtolower(self::strVal($input['tipo'] ?? ''));
        $ids = array_values(array_filter(array_map('intval', is_array($input['ids'] ?? null) ? $input['ids'] : []), static function ($id) { return $id > 0; }));
        if (!$ids || !in_array($tipo, ['dictamen', 'subestatus', 'tipo_gestion', 'gestion'], true)) {
            return ['success' => false, 'mensaje' => 'Orden invÃƒÂ¡lido.'];
        }
        $tabla = $tipo === 'dictamen' ? 'atlas_catalogo_dictamen' : ($tipo === 'subestatus' ? 'atlas_dictamen_sub_estatus' : ($tipo === 'tipo_gestion' ? 'atlas_catalogo_tipos_gestion' : 'atlas_catalogo_gestion'));
        try {
            $db = new Database();
            self::asegurarTablasCatalogosComerciales($db);
            $db->beginTransaction();
            if ($tipo === 'subestatus') {
                $padres = [];
                foreach ($ids as $id) {
                    $row = $db->queryOne("SELECT dictamen_id FROM atlas_dictamen_sub_estatus WHERE id = :id LIMIT 1", ['id' => $id]);
                    $dictamenId = (int)($row['dictamen_id'] ?? 0);
                    if ($dictamenId <= 0) {
                        continue;
                    }
                    $padres[$dictamenId] = ($padres[$dictamenId] ?? 0) + 1;
                    $db->CRUD(
                        "UPDATE atlas_dictamen_sub_estatus SET orden = :orden WHERE id = :id",
                        ['orden' => $padres[$dictamenId], 'id' => $id]
                    );
                }
            } else {
                foreach ($ids as $idx => $id) {
                    $db->CRUD("UPDATE $tabla SET orden = :orden WHERE id = :id", ['orden' => $idx + 1, 'id' => $id]);
                }
            }
            $db->commit();
            return ['success' => true, 'mensaje' => 'Orden actualizado.'];
        } catch (\Throwable $e) {
            if (isset($db)) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo guardar el orden.', 'error' => $e->getMessage()];
        }
    }

    private static function validarFilasComerciales(array $filas): array
    {
        $errores = [];
        $subKeys = [];
        $gestionKeys = [];
        foreach (array_values($filas) as $idx => $fila) {
            $n = $idx + 1;
            $estatus = self::strVal($fila['estatus'] ?? $fila['dictamen'] ?? '');
            $sub = self::strVal($fila['subestatus'] ?? $fila['sub_estatus'] ?? '');
            $gestion = self::strVal($fila['gestion'] ?? $fila['lista_desplegable_gestion'] ?? '');
            if ($estatus === '') $errores[] = "Fila $n: estatus obligatorio.";
            if ($sub === '') $errores[] = "Fila $n: subestatus obligatorio.";
            if ($gestion === '') $errores[] = "Fila $n: gestiÃƒÂ³n obligatoria.";
            $subKey = mb_strtolower($estatus . '|' . $sub, 'UTF-8');
            $gestionKey = mb_strtolower($estatus . '|' . $sub . '|' . $gestion, 'UTF-8');
            if ($sub !== '' && isset($subKeys[$subKey]) && $subKeys[$subKey] !== $n) {
                // Permitimos repetir el subestatus si trae gestiones distintas.
            } else {
                $subKeys[$subKey] = $n;
            }
            if ($gestion !== '' && isset($gestionKeys[$gestionKey])) {
                $errores[] = "Fila $n: gestiÃƒÂ³n duplicada dentro del mismo subestatus.";
            }
            $gestionKeys[$gestionKey] = true;
        }
        return $errores;
    }

    private static function obtenerOCrearDictamenComercial(Database $db, string $nombre, ?string $objetivo, int $orden): array
    {
        $row = $db->queryOne("SELECT id FROM atlas_catalogo_dictamen WHERE LOWER(nombre)=LOWER(:nombre) LIMIT 1", ['nombre' => $nombre]);
        if ($row) {
            $db->CRUD("UPDATE atlas_catalogo_dictamen SET objetivo = COALESCE(:objetivo, objetivo), activo = 1, estado_registro = 'publicado' WHERE id = :id", ['objetivo' => $objetivo, 'id' => (int)$row['id']]);
            return ['id' => (int)$row['id'], 'creado' => false];
        }
        $clave = self::claveDesdeTexto($nombre);
        $db->CRUD("INSERT INTO atlas_catalogo_dictamen (codigo_estatus, clave, nombre, objetivo, orden, activo, estado_registro) VALUES (:codigo, :clave, :nombre, :objetivo, :orden, 1, 'publicado')", ['codigo' => null, 'clave' => $clave, 'nombre' => $nombre, 'objetivo' => $objetivo, 'orden' => $orden]);
        return ['id' => $db->lastInsertId(), 'creado' => true];
    }

    private static function obtenerOCrearSubestatusComercial(Database $db, int $dictamenId, string $nombre, int $orden): array
    {
        $row = $db->queryOne("SELECT id FROM atlas_dictamen_sub_estatus WHERE dictamen_id = :dictamen_id AND LOWER(nombre)=LOWER(:nombre) LIMIT 1", ['dictamen_id' => $dictamenId, 'nombre' => $nombre]);
        if ($row) {
            return ['id' => (int)$row['id'], 'creado' => false];
        }
        $db->CRUD("INSERT INTO atlas_dictamen_sub_estatus (dictamen_id, clave, nombre, orden, activo, estado_registro) VALUES (:dictamen_id, :clave, :nombre, :orden, 1, 'publicado')", ['dictamen_id' => $dictamenId, 'clave' => self::claveDesdeTexto($nombre), 'nombre' => $nombre, 'orden' => $orden]);
        return ['id' => $db->lastInsertId(), 'creado' => true];
    }

    private static function obtenerOCrearTipoGestionComercial(Database $db, string $nombre): array
    {
        $nombre = self::strVal($nombre);
        if ($nombre === '') {
            return ['id' => null, 'nombre' => null, 'creado' => false];
        }
        $row = $db->queryOne("SELECT id, nombre FROM atlas_catalogo_tipos_gestion WHERE LOWER(nombre)=LOWER(:nombre) LIMIT 1", ['nombre' => $nombre]);
        if ($row) {
            $db->CRUD("UPDATE atlas_catalogo_tipos_gestion SET activo = 1, estado_registro = 'publicado' WHERE id = :id", ['id' => (int)$row['id']]);
            return ['id' => (int)$row['id'], 'nombre' => (string)$row['nombre'], 'creado' => false];
        }
        $orden = $db->queryOne("SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM atlas_catalogo_tipos_gestion");
        $db->CRUD(
            "INSERT INTO atlas_catalogo_tipos_gestion (clave, nombre, orden, activo, estado_registro) VALUES (:clave, :nombre, :orden, 1, 'publicado')",
            ['clave' => self::claveDesdeTexto($nombre), 'nombre' => $nombre, 'orden' => (int)($orden['siguiente'] ?? 1)]
        );
        return ['id' => $db->lastInsertId(), 'nombre' => $nombre, 'creado' => true];
    }

    private static function obtenerOCrearGestionComercial(Database $db, int $dictamenId, int $subestatusId, array $datos): array
    {
        $nombre = self::strVal($datos['nombre'] ?? '');
        $row = $db->queryOne("SELECT id FROM atlas_catalogo_gestion WHERE COALESCE(NULLIF(subestatus_id, 0), sub_estatus_id) = :subestatus_id AND LOWER(nombre)=LOWER(:nombre) LIMIT 1", ['subestatus_id' => $subestatusId, 'nombre' => $nombre]);
        $payload = [
            'dictamen_id' => $dictamenId,
            'sub_estatus_id' => $subestatusId,
            'subestatus_id' => $subestatusId,
            'tipo_gestion_id' => self::nullableInt($datos['tipo_gestion_id'] ?? null),
            'tipo_gestion' => $datos['tipo_gestion'] ?? null,
            'clave' => self::claveDesdeTexto($nombre),
            'nombre' => $nombre,
            'ventana_complementaria' => $datos['ventana_complementaria'] ?? null,
            'campos_adicionales' => $datos['campos_adicionales'] ?? null,
            'requiere_fecha' => self::activoVal($datos['requiere_fecha'] ?? 0),
            'permite_comentario' => self::activoVal($datos['permite_comentario'] ?? 1),
            'orden' => self::intVal($datos['orden'] ?? 1),
        ];
        if ($row) {
            $payload['id'] = (int)$row['id'];
            $db->CRUD("UPDATE atlas_catalogo_gestion SET sub_estatus_id = :sub_estatus_id, subestatus_id = :subestatus_id, tipo_gestion_id = :tipo_gestion_id, tipo_gestion = :tipo_gestion, ventana_complementaria = :ventana_complementaria, campos_adicionales = :campos_adicionales, requiere_fecha = :requiere_fecha, permite_comentario = :permite_comentario, activo = 1, estado_registro = 'publicado' WHERE id = :id", $payload);
            return ['id' => (int)$row['id'], 'creado' => false];
        }
        $db->CRUD("INSERT INTO atlas_catalogo_gestion (dictamen_id, sub_estatus_id, subestatus_id, tipo_gestion_id, tipo_gestion, clave, nombre, ventana_complementaria, campos_adicionales, requiere_fecha, permite_comentario, orden, activo, estado_registro) VALUES (:dictamen_id, :sub_estatus_id, :subestatus_id, :tipo_gestion_id, :tipo_gestion, :clave, :nombre, :ventana_complementaria, :campos_adicionales, :requiere_fecha, :permite_comentario, :orden, 1, 'publicado')", $payload);
        return ['id' => $db->lastInsertId(), 'creado' => true];
    }

    private static function guardarFilaCatalogo(Database $db, string $tabla, array $datos, int $id, string $entidad): array
    {
        if ($id > 0) {
            $datos['id'] = $id;
            $sets = [];
            foreach (array_keys($datos) as $campo) {
                if ($campo !== 'id') $sets[] = "$campo = :$campo";
            }
            $db->CRUD("UPDATE $tabla SET " . implode(', ', $sets) . " WHERE id = :id", $datos);
            return ['success' => true, 'mensaje' => "$entidad actualizado.", 'id' => $id];
        }
        $campos = array_keys($datos);
        $db->CRUD("INSERT INTO $tabla (" . implode(', ', $campos) . ") VALUES (:" . implode(', :', $campos) . ")", $datos);
        return ['success' => true, 'mensaje' => "$entidad agregado.", 'id' => $db->lastInsertId()];
    }

    private static function asegurarAccesosAtlas(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_acceso_usuarios (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                persona_id INT NOT NULL,
                numero_empleado VARCHAR(50) NULL,
                nombre VARCHAR(260) NOT NULL,
                correo VARCHAR(120) NULL,
                telefono VARCHAR(40) NULL,
                foto_perfil TEXT NULL,
                puesto VARCHAR(180) NULL,
                departamento VARCHAR(180) NULL,
                area VARCHAR(180) NULL,
                direccion VARCHAR(180) NULL,
                pais VARCHAR(100) NULL,
                jefe_nombre VARCHAR(260) NULL,
                origen VARCHAR(80) NOT NULL DEFAULT 'comercial_mexico',
                rol_atlas VARCHAR(80) NOT NULL DEFAULT 'usuario',
                acceso_movil TINYINT(1) NOT NULL DEFAULT 0,
                puede_ver TINYINT(1) NOT NULL DEFAULT 1,
                puede_editar TINYINT(1) NOT NULL DEFAULT 0,
                puede_administrar TINYINT(1) NOT NULL DEFAULT 0,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                ultima_sincronizacion DATETIME NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uk_atlas_acceso_persona (persona_id),
                KEY idx_atlas_acceso_activo (activo),
                KEY idx_atlas_acceso_origen (origen)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        self::asegurarColumna($db, 'atlas_acceso_usuarios', 'excluido_operativo', "TINYINT(1) NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_acceso_usuarios', 'acceso_movil', "TINYINT(1) NOT NULL DEFAULT 0");
        self::asegurarColumna($db, 'atlas_acceso_usuarios', 'telefono', "VARCHAR(40) NULL");
        self::asegurarColumna($db, 'atlas_acceso_usuarios', 'foto_perfil', "TEXT NULL");
        self::asegurarColumna($db, 'atlas_acceso_usuarios', 'jefe_nombre', "VARCHAR(260) NULL");
        self::asegurarColumna($db, 'atlas_acceso_usuarios', 'excluido_motivo', "VARCHAR(250) NULL");
        self::asegurarColumna($db, 'atlas_acceso_usuarios', 'excluido_at', "DATETIME NULL");
        self::asegurarColumna($db, 'atlas_acceso_usuarios', 'excluido_by', "INT NULL");
    }

    private static function asegurarRestablecimientoPasswordAtlas(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_catalogo_motivos_reset_password (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                nombre VARCHAR(160) NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uk_atlas_motivo_reset_nombre (nombre)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_bitacora_reset_password (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                acceso_usuario_id BIGINT UNSIGNED NOT NULL,
                persona_id INT NOT NULL,
                motivo_id BIGINT UNSIGNED NOT NULL,
                motivo VARCHAR(160) NOT NULL,
                password_anterior VARCHAR(180) NULL,
                password_nueva VARCHAR(180) NOT NULL,
                restablecido_por INT NULL,
                fecha_alta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_atlas_reset_acceso (acceso_usuario_id),
                KEY idx_atlas_reset_persona (persona_id),
                KEY idx_atlas_reset_motivo (motivo_id),
                KEY idx_atlas_reset_fecha (fecha_alta)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $total = $db->queryOne("SELECT COUNT(*) AS total FROM atlas_catalogo_motivos_reset_password");
        if ((int)($total['total'] ?? 0) === 0) {
            foreach ([
                'Olvido de contrasena',
                'Cambio por seguridad',
                'Solicitud del responsable',
                'Alta o reactivacion de acceso movil',
                'Soporte operativo',
            ] as $motivo) {
                $db->CRUD(
                    "INSERT INTO atlas_catalogo_motivos_reset_password (nombre, activo) VALUES (:nombre, 1)",
                    ['nombre' => $motivo]
                );
            }
        }
    }

    private static function sqlUsuariosComercialMexico(): string
    {
        return "
            SELECT
                persona_id,
                MAX(numero_empleado) AS numero_empleado,
                MAX(nombre) AS nombre,
                MAX(correo) AS correo,
                MAX(telefono) AS telefono,
                MAX(foto_perfil) AS foto_perfil,
                MAX(puesto) AS puesto,
                MAX(departamento) AS departamento,
                MAX(area) AS area,
                MAX(direccion) AS direccion,
                MAX(pais) AS pais,
                MAX(jefe_nombre) AS jefe_nombre
            FROM (
                SELECT
                    p.id AS persona_id,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ',
                        NULLIF(TRIM(p.nombres), ''),
                        NULLIF(TRIM(p.segundo_nombre), ''),
                        NULLIF(TRIM(p.apellidop), ''),
                        NULLIF(TRIM(p.apellidom), '')
                    )) AS nombre,
                    p.correo,
                    CONVERT(COALESCE(NULLIF(TRIM(p.telefono_uno), ''), NULLIF(TRIM(p.telefono_dos), ''), '') USING utf8mb4) COLLATE utf8mb4_general_ci AS telefono,
                    pf.foto AS foto_perfil,
                    CONVERT(COALESCE(NULLIF(TRIM(pdr.puesto_texto), ''), pu.nombre, '') USING utf8mb4) COLLATE utf8mb4_general_ci AS puesto,
                    CONVERT(COALESCE(NULLIF(TRIM(pdr.departamento_texto), ''), dep.nombre, '') USING utf8mb4) COLLATE utf8mb4_general_ci AS departamento,
                    CONVERT(COALESCE(NULLIF(TRIM(pdr.area_texto), ''), dorg.nombre, '') USING utf8mb4) COLLATE utf8mb4_general_ci AS area,
                    CONVERT(COALESCE(NULLIF(TRIM(pdr.direccion_organizacional), ''), dir.nombre, '') USING utf8mb4) COLLATE utf8mb4_general_ci AS direccion,
                    CONVERT(COALESCE(pa.nombre, '') USING utf8mb4) COLLATE utf8mb4_general_ci AS pais,
                    CONVERT(COALESCE(TRIM(CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)), '') USING utf8mb4) COLLATE utf8mb4_general_ci AS jefe_nombre
                FROM persona p
                LEFT JOIN perfil pf ON pf.id_persona = p.id
                LEFT JOIN persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND ap.activo = 1
                LEFT JOIN puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                LEFT JOIN departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                LEFT JOIN departamento_organizacional dorg ON dorg.id = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                LEFT JOIN paises pa ON pa.id = p.id_pais
                LEFT JOIN (
                    SELECT id_persona, MAX(id) AS max_id
                    FROM asigna_jefe
                    GROUP BY id_persona
                ) aj_ult ON aj_ult.id_persona = p.id
                LEFT JOIN asigna_jefe aj ON aj.id = aj_ult.max_id
                LEFT JOIN vacantes_personal vp ON vp.id = aj.id_vacante_jefe
                LEFT JOIN persona pj ON pj.id = COALESCE(aj.id_jefe, vp.id_jefe)
                WHERE p.estatus = 'Activo'
                  AND (
                      CONVERT(COALESCE(pa.nombre, '') USING utf8mb4) COLLATE utf8mb4_general_ci IN ('Mexico', 'MÃ©xico')
                      OR p.id_pais = 1
                  )
            ) base
            WHERE LOWER(direccion) LIKE '%comercial%'
               OR LOWER(area) LIKE '%comercial%'
            GROUP BY persona_id
        ";
    }

    public static function sincronizarAccesosAtlasComercialMexico(): array
    {
        try {
            $db = new Database();
            self::asegurarAccesosAtlas($db);
            $sqlUsuarios = self::sqlUsuariosComercialMexico();

            $totalFuente = $db->queryOne("SELECT COUNT(*) AS total FROM ($sqlUsuarios) src");
            $totalAntes = $db->queryOne("SELECT COUNT(*) AS total FROM atlas_acceso_usuarios WHERE origen = 'comercial_mexico' AND activo = 1");

            $db->beginTransaction();
            $db->CRUD("
                INSERT INTO atlas_acceso_usuarios
                    (persona_id, numero_empleado, nombre, correo, telefono, foto_perfil, puesto, departamento, area, direccion, pais, jefe_nombre, origen, rol_atlas, activo, ultima_sincronizacion)
                SELECT
                    persona_id, numero_empleado, nombre, correo, telefono, foto_perfil, puesto, departamento, area, direccion, pais, jefe_nombre,
                    'comercial_mexico', 'usuario', 1, NOW()
                FROM ($sqlUsuarios) src
                ON DUPLICATE KEY UPDATE
                    numero_empleado = VALUES(numero_empleado),
                    nombre = VALUES(nombre),
                    correo = VALUES(correo),
                    telefono = VALUES(telefono),
                    foto_perfil = VALUES(foto_perfil),
                    puesto = VALUES(puesto),
                    departamento = VALUES(departamento),
                    area = VALUES(area),
                    direccion = VALUES(direccion),
                    pais = VALUES(pais),
                    jefe_nombre = VALUES(jefe_nombre),
                    origen = 'comercial_mexico',
                    activo = 1,
                    ultima_sincronizacion = NOW()
            ");
            $db->CRUD("
                UPDATE atlas_acceso_usuarios au
                LEFT JOIN ($sqlUsuarios) src ON src.persona_id = au.persona_id
                SET au.activo = 0,
                    au.ultima_sincronizacion = NOW()
                WHERE au.origen = 'comercial_mexico'
                  AND src.persona_id IS NULL
            ");
            $db->commit();

            $totalDespues = $db->queryOne("SELECT COUNT(*) AS total FROM atlas_acceso_usuarios WHERE origen = 'comercial_mexico' AND activo = 1");

            return [
                'success' => true,
                'mensaje' => 'Usuarios de Comercial Mexico sincronizados para Accesos Atlas.',
                'datos' => [
                    'fuente_comercial_mexico' => (int)($totalFuente['total'] ?? 0),
                    'activos_antes' => (int)($totalAntes['total'] ?? 0),
                    'activos_despues' => (int)($totalDespues['total'] ?? 0),
                ],
            ];
        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudieron sincronizar los usuarios de Accesos Atlas.', 'error' => $e->getMessage()];
        }
    }

    public static function getAccesosAtlas(): array
    {
        try {
            $db = new Database();
            self::asegurarAccesosAtlas($db);
            $usuarios = $db->queryAll("
                SELECT
                    au.id,
                    au.persona_id,
                    au.numero_empleado,
                    au.nombre,
                    au.correo,
                    au.telefono,
                    au.foto_perfil,
                    au.puesto,
                    au.departamento,
                    au.area,
                    au.direccion,
                    au.pais,
                    COALESCE(
                        NULLIF(CAST(TRIM(CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)) AS CHAR), ''),
                        NULLIF(CAST(au.jefe_nombre AS CHAR), ''),
                        ''
                    ) AS jefe_nombre,
                    au.rol_atlas,
                    au.puede_ver,
                    au.puede_editar,
                    au.puede_administrar,
                    au.excluido_operativo,
                    au.excluido_motivo,
                    DATE_FORMAT(au.excluido_at, '%d/%m/%Y %H:%i') AS excluido_at_fmt,
                    au.activo,
                    DATE_FORMAT(au.ultima_sincronizacion, '%d/%m/%Y %H:%i') AS ultima_sincronizacion_fmt
                FROM atlas_acceso_usuarios au
                LEFT JOIN (
                    SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                    FROM asigna_jefe a
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS id_ultimo
                        FROM asigna_jefe
                        GROUP BY id_persona
                    ) ult ON ult.id_persona = a.id_persona AND ult.id_ultimo = a.id
                ) aj ON aj.id_persona = au.persona_id
                LEFT JOIN vacantes_personal vj
                       ON vj.id = aj.id_vacante_jefe
                LEFT JOIN persona pj
                       ON pj.id = COALESCE(aj.id_jefe, vj.id_jefe)
                WHERE au.origen = 'comercial_mexico'
                ORDER BY au.activo DESC, au.nombre ASC
            ");
            $totales = $db->queryOne("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) AS activos,
                    SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) AS inactivos,
                    SUM(CASE WHEN excluido_operativo = 1 THEN 1 ELSE 0 END) AS excluidos,
                    SUM(CASE WHEN activo = 1 AND excluido_operativo = 0 THEN 1 ELSE 0 END) AS operativos
                FROM atlas_acceso_usuarios
                WHERE origen = 'comercial_mexico'
            ") ?: [];
            return ['success' => true, 'datos' => ['usuarios' => $usuarios, 'totales' => $totales]];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar el catalogo de accesos Atlas.', 'error' => $e->getMessage(), 'datos' => ['usuarios' => [], 'totales' => []]];
        }
    }

    public static function actualizarExclusionAccesosAtlas(array $input): array
    {
        try {
            $ids = $input['ids'] ?? [];
            if (!is_array($ids)) {
                $ids = [$ids];
            }
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids), static fn($id) => $id > 0)));
            if (!$ids) {
                return ['success' => false, 'mensaje' => 'Selecciona al menos un usuario.'];
            }
            $excluir = (int)($input['excluir'] ?? 1) === 1 ? 1 : 0;
            $motivo = self::nullableStr($input['motivo'] ?? null);
            $usuarioId = self::nullableInt($input['_usuario_id'] ?? null);

            $db = new Database();
            self::asegurarAccesosAtlas($db);
            $params = [
                'excluido' => $excluir,
                'motivo' => $excluir ? ($motivo ?: 'Exclusion operativa manual') : null,
                'usuario' => $usuarioId,
            ];
            $ph = [];
            foreach ($ids as $i => $id) {
                $key = 'id' . $i;
                $ph[] = ':' . $key;
                $params[$key] = $id;
            }
            $db->CRUD("
                UPDATE atlas_acceso_usuarios
                SET excluido_operativo = :excluido,
                    excluido_motivo = :motivo,
                    excluido_at = " . ($excluir ? "NOW()" : "NULL") . ",
                    excluido_by = :usuario
                WHERE id IN (" . implode(',', $ph) . ")
                  AND origen = 'comercial_mexico'
            ", $params);

            return [
                'success' => true,
                'mensaje' => $excluir ? 'Usuarios enviados a excluidos.' : 'Usuarios reincorporados a operaciÃ³n.',
                'datos' => ['afectados' => count($ids), 'excluido' => $excluir],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo actualizar la exclusion operativa.', 'error' => $e->getMessage()];
        }
    }

    public static function getAccesoAtlasDetalle(int $id): array
    {
        try {
            if ($id <= 0) {
                return ['success' => false, 'mensaje' => 'Usuario invalido.'];
            }
            $db = new Database();
            self::asegurarAccesosAtlas($db);
            self::asegurarPermisosSucursalesAtlas($db);
            self::asegurarRestablecimientoPasswordAtlas($db);

            $usuario = $db->queryOne("
                SELECT
                    au.id,
                    au.persona_id,
                    au.numero_empleado,
                    au.nombre,
                    au.correo,
                    au.puesto,
                    au.departamento,
                    au.area,
                    au.direccion,
                    au.excluido_operativo,
                    au.acceso_movil,
                    p.user_name,
                    p.password
                FROM atlas_acceso_usuarios au
                INNER JOIN persona p ON p.id = au.persona_id
                WHERE au.id = :id
                  AND au.origen = 'comercial_mexico'
                LIMIT 1
            ", ['id' => $id]);
            if (!$usuario) {
                return ['success' => false, 'mensaje' => 'No se encontro el usuario en Accesos Atlas.'];
            }

            $modulos = $db->queryAll("
                SELECT
                    m.id,
                    m.nombre,
                    m.pestana,
                    m.descripcion,
                    CASE WHEN am.usuario_id IS NULL THEN 0 ELSE 1 END AS asignado
                FROM modulos_web m
                LEFT JOIN asigna_modulo_web am
                       ON am.modulo_web_id = m.id
                      AND am.usuario_id = :persona_id
                WHERE m.activo = 1
                  AND m.id IN (" . implode(',', array_map('intval', self::MODULOS_ATLAS_IDS)) . ")
                ORDER BY FIELD(m.id, " . implode(',', array_map('intval', self::MODULOS_ATLAS_IDS)) . ")
            ", ['persona_id' => (int)$usuario['persona_id']]);
            $motivosReset = $db->queryAll("
                SELECT id, nombre
                FROM atlas_catalogo_motivos_reset_password
                WHERE activo = 1
                ORDER BY nombre ASC
            ");

            return [
                'success' => true,
                'datos' => [
                    'usuario' => $usuario,
                    'modulos' => $modulos,
                    'motivos_reset_password' => $motivosReset,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo cargar el detalle del acceso Atlas.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarPermisosAccesoAtlas(array $input): array
    {
        $db = null;
        try {
            $id = (int)($input['id'] ?? 0);
            if ($id <= 0) {
                return ['success' => false, 'mensaje' => 'Usuario invalido.'];
            }
            $modulos = $input['modulos'] ?? [];
            if (!is_array($modulos)) {
                $modulos = [];
            }
            $permitidos = array_fill_keys(self::MODULOS_ATLAS_IDS, true);
            $modulos = array_values(array_unique(array_filter(array_map('intval', $modulos), static function ($mid) use ($permitidos) {
                return isset($permitidos[$mid]);
            })));

            $db = new Database();
            self::asegurarAccesosAtlas($db);
            self::asegurarPermisosSucursalesAtlas($db);

            $usuario = $db->queryOne("
                SELECT au.id, au.persona_id, au.nombre, au.puesto
                FROM atlas_acceso_usuarios au
                WHERE au.id = :id
                  AND au.origen = 'comercial_mexico'
                LIMIT 1
            ", ['id' => $id]);
            if (!$usuario) {
                return ['success' => false, 'mensaje' => 'No se encontro el usuario en Accesos Atlas.'];
            }
            $puestoUsuario = strtoupper(trim((string)($usuario['puesto'] ?? '')));
            if (in_array(self::MODULO_ATLAS_RUTAS_COMBO_GESTOR_NIVELES, $modulos, true)
                && preg_match('/\b(GESTOR|ASESOR|EJECUTIVO)\b/u', $puestoUsuario)) {
                return ['success' => false, 'mensaje' => 'Este permiso especial no puede asignarse a gestores o asesores.'];
            }
            $personaId = (int)$usuario['persona_id'];
            $accesoMovil = (int)($input['acceso_movil'] ?? 0) === 1 ? 1 : 0;

            $db->beginTransaction();
            $db->CRUD(
                "UPDATE atlas_acceso_usuarios
                 SET acceso_movil = :acceso_movil
                 WHERE id = :id
                   AND origen = 'comercial_mexico'",
                ['acceso_movil' => $accesoMovil, 'id' => $id]
            );
            $db->CRUD(
                "DELETE FROM asigna_modulo_web
                 WHERE usuario_id = :uid
                   AND modulo_web_id IN (" . implode(',', array_map('intval', self::MODULOS_ATLAS_IDS)) . ")",
                ['uid' => $personaId]
            );
            foreach ($modulos as $moduloId) {
                $db->CRUD(
                    "INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id)
                     VALUES (:uid, :mid)",
                    ['uid' => $personaId, 'mid' => $moduloId]
                );
            }
            $db->CRUD(
                "UPDATE persona SET session_version = COALESCE(session_version, 1) + 1 WHERE id = :id",
                ['id' => $personaId]
            );
            $db->commit();

            return [
                'success' => true,
                'mensaje' => 'Permisos Atlas guardados.',
                'datos' => [
                    'persona_id' => $personaId,
                    'modulos' => $modulos,
                    'acceso_movil' => $accesoMovil,
                ],
            ];
        } catch (\Throwable $e) {
            if ($db && $db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudieron guardar los permisos Atlas.', 'error' => $e->getMessage()];
        }
    }

    public static function restablecerPasswordAccesoAtlas(array $input): array
    {
        $db = null;
        try {
            $id = (int)($input['id'] ?? 0);
            $motivoId = (int)($input['motivo_id'] ?? 0);
            $password = trim((string)($input['password'] ?? ''));
            $restablecidoPor = self::nullableInt($input['_usuario_id'] ?? null);

            if ($id <= 0) {
                return ['success' => false, 'mensaje' => 'Usuario invalido.'];
            }
            if ($motivoId <= 0) {
                return ['success' => false, 'mensaje' => 'Selecciona el motivo del restablecimiento.'];
            }
            if (strlen($password) < 6 || strlen($password) > 30) {
                return ['success' => false, 'mensaje' => 'La contrasena debe tener entre 6 y 30 caracteres.'];
            }

            $db = new Database();
            self::asegurarAccesosAtlas($db);
            self::asegurarRestablecimientoPasswordAtlas($db);

            $usuario = $db->queryOne("
                SELECT au.id, au.persona_id, au.nombre, p.password AS password_actual
                FROM atlas_acceso_usuarios au
                INNER JOIN persona p ON p.id = au.persona_id
                WHERE au.id = :id
                  AND au.origen = 'comercial_mexico'
                LIMIT 1
            ", ['id' => $id]);
            if (!$usuario) {
                return ['success' => false, 'mensaje' => 'No se encontro el usuario en Accesos Atlas.'];
            }

            $motivo = $db->queryOne("
                SELECT id, nombre
                FROM atlas_catalogo_motivos_reset_password
                WHERE id = :id
                  AND activo = 1
                LIMIT 1
            ", ['id' => $motivoId]);
            if (!$motivo) {
                return ['success' => false, 'mensaje' => 'El motivo seleccionado no esta activo.'];
            }

            $personaId = (int)$usuario['persona_id'];
            $db->beginTransaction();
            $db->CRUD(
                "UPDATE persona
                 SET password = :password,
                     session_version = COALESCE(session_version, 1) + 1
                 WHERE id = :id",
                ['password' => $password, 'id' => $personaId]
            );
            $db->CRUD(
                "INSERT INTO atlas_bitacora_reset_password
                    (acceso_usuario_id, persona_id, motivo_id, motivo, password_anterior, password_nueva, restablecido_por)
                 VALUES
                    (:acceso_id, :persona_id, :motivo_id, :motivo, :password_anterior, :password_nueva, :restablecido_por)",
                [
                    'acceso_id' => $id,
                    'persona_id' => $personaId,
                    'motivo_id' => (int)$motivo['id'],
                    'motivo' => (string)$motivo['nombre'],
                    'password_anterior' => $usuario['password_actual'] ?? null,
                    'password_nueva' => $password,
                    'restablecido_por' => $restablecidoPor,
                ]
            );
            $db->commit();

            return [
                'success' => true,
                'mensaje' => 'Contrasena restablecida.',
                'datos' => [
                    'persona_id' => $personaId,
                    'password' => $password,
                    'motivo' => (string)$motivo['nombre'],
                ],
            ];
        } catch (\Throwable $e) {
            if ($db && $db->inTransaction()) {
                $db->rollback();
            }
            return ['success' => false, 'mensaje' => 'No se pudo restablecer la contrasena.', 'error' => $e->getMessage()];
        }
    }

    private static function estadoCatalogoVal($v): string
    {
        $s = strtolower(self::strVal($v));
        return in_array($s, ['borrador', 'pendiente', 'publicado'], true) ? $s : 'publicado';
    }

    private static function claveDesdeTexto(string $texto): string
    {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
        $s = strtoupper(preg_replace('/[^A-Z0-9]+/', '_', (string)$s));
        $s = trim($s, '_');
        return $s !== '' ? substr($s, 0, 80) : 'SIN_CLAVE';
    }

    private static function guardarSimple(string $tabla, array $datos, int $id, array $requeridos, string $nombreEntidad): array
    {
        try {
            foreach ($requeridos as $campo) {
                if (trim((string)($datos[$campo] ?? '')) === '') {
                    return ['success' => false, 'mensaje' => 'Captura el nombre.'];
                }
            }

            $db = new Database();
            if ($id > 0) {
                $datos['id'] = $id;
                $sets = [];
                foreach (array_keys($datos) as $campo) {
                    if ($campo === 'id') {
                        continue;
                    }
                    $sets[] = "$campo = :$campo";
                }
                $db->CRUD("UPDATE $tabla SET " . implode(', ', $sets) . " WHERE id = :id", $datos);
                return ['success' => true, 'mensaje' => ucfirst($nombreEntidad) . ' actualizado.', 'id' => $id];
            }

            $campos = array_keys($datos);
            $db->CRUD(
                "INSERT INTO $tabla (" . implode(', ', $campos) . ") VALUES (:" . implode(', :', $campos) . ")",
                $datos
            );
            return ['success' => true, 'mensaje' => ucfirst($nombreEntidad) . ' agregado.', 'id' => $db->lastInsertId()];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'mensaje' => 'No se pudo guardar el catÃƒÂ¡logo.',
                'error' => $e->getMessage(),
            ];
        }
    }

    private static function strVal($v): string
    {
        return trim((string)($v ?? ''));
    }

    private static function nullableStr($v): ?string
    {
        $s = self::strVal($v);
        return $s === '' ? null : $s;
    }

    private static function intVal($v): int
    {
        return (int)($v ?? 0);
    }

    private static function nullableInt($v): ?int
    {
        $n = (int)($v ?? 0);
        return $n > 0 ? $n : null;
    }

    private static function nullableDecimal($v): ?string
    {
        $s = self::strVal($v);
        if ($s === '' || !is_numeric($s)) {
            return null;
        }
        return $s;
    }

    private static function activoVal($v): int
    {
        return (int)$v === 1 ? 1 : 0;
    }

    private static function normalizarColor($v): ?string
    {
        $s = self::nullableStr($v);
        if ($s === null) {
            return null;
        }
        if ($s[0] !== '#') {
            $s = '#' . $s;
        }
        return preg_match('/^#[0-9a-f]{6}$/i', $s) ? strtoupper($s) : null;
    }
}
