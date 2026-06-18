<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\UsuarioFantasmaReporteria;

class CapHum extends Model
{
    private static $trayectoriaPuestoTablaAsegurada = false;
    public const MODULO_ACCESOS_CAPITAL_HUMANO = 140;
    private const MODULO_MIS_DOCUMENTOS = 141;
    private const MODULO_VALIDADOR_DOCUMENTAL_CANDIDATOS = 104;
    private const MODULO_VALIDADOR_DOCUMENTAL_RRHH_CANDIDATOS = 142;
    private const MODULO_GESTION_REGISTRAR_PERSONA = 143;
    private const MODULOS_ACCESOS_CAPITAL_HUMANO_IDS = [
        4, 5, 13, 34, 38, 42, 44, 82, 83, 86, 87, 88, 91, 93,
        94, 95, 96, 97, 98, 99, 101, 104, 105,
        140, 141, 142, 143,
    ];
    private const MODULO_CONVENIOS_DESCARGAR_EXCEL = 92;
    private const MODULO_CONVENIOS_DESCARGAR_EXCEL_NOMBRE = 'Descargar Excel';
    private const MODULO_CONVENIOS_DESCARGAR_EXCEL_DESC = 'Convenios - Cierre de Credito - Descargar Excel';
    private const MODULO_TRACKING_CANCELAR_RUTA = 102;
    private const MODULO_TRACKING_CANCELAR_RUTA_NOMBRE = 'Cancelar rutas Tracking';
    private const MODULO_TRACKING_CANCELAR_RUTA_DESC = 'Tracking Recoleccion - Cancelar rutas registradas';

    public static function asegurarModuloAccesosCapitalHumano(): void
    {
        try {
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarModuloAccesosCapitalHumano -> ' . $e->getMessage());
        }
    }

    private static function asegurarModuloAccesosCapitalHumanoDb(Database $db): void
    {
        $modulos = [
            [
                'id' => self::MODULO_ACCESOS_CAPITAL_HUMANO,
                'nombre' => 'Accesos Capital Humano',
                'pestana' => 'Capital Humano',
                'descripcion' => 'Acceso al modulo de administracion de permisos de Capital Humano.',
            ],
            [
                'id' => self::MODULO_MIS_DOCUMENTOS,
                'nombre' => 'Mis documentos',
                'pestana' => 'Capital Humano',
                'descripcion' => 'Capital Humano > Mis documentos',
            ],
            [
                'id' => self::MODULO_VALIDADOR_DOCUMENTAL_RRHH_CANDIDATOS,
                'nombre' => 'Validador documental RRHH',
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite validar documentos de candidatos que no pertenecen a la direccion Cobranza',
            ],
            [
                'id' => self::MODULO_GESTION_REGISTRAR_PERSONA,
                'nombre' => 'Registrar persona en Gestion de Personal',
                'pestana' => 'Permisos especiales',
                'descripcion' => 'Permite ver y usar la opcion Persona al agregar usuarios en Gestion de Personal.',
            ],
        ];

        foreach ($modulos as $datos) {
            $existe = $db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => $datos['id']]
            );
            if ($existe) {
                $db->CRUD(
                    'UPDATE modulos_web
                        SET nombre = :nombre,
                            pestana = :pestana,
                            descripcion = :descripcion,
                            activo = 1
                      WHERE id = :id',
                    $datos
                );
                continue;
            }
            $db->CRUD(
                'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                 VALUES (:id, :nombre, :pestana, :descripcion, 1)',
                $datos
            );
        }
    }

    private static function asegurarModuloConveniosDescargarExcel(Database $db): void
    {
        try {
            $datos = [
                'id' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL,
                'nombre' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL_NOMBRE,
                'pestana' => 'Permisos especiales',
                'descripcion' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL_DESC,
            ];
            $existe = $db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL]
            );

            if ($existe) {
                $db->CRUD(
                    'UPDATE modulos_web
                        SET nombre = :nombre,
                            pestana = :pestana,
                            descripcion = :descripcion,
                            activo = 1
                      WHERE id = :id',
                    $datos
                );
                return;
            }

            $db->CRUD(
                'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                 VALUES (:id, :nombre, :pestana, :descripcion, 1)',
                $datos
            );
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarModuloConveniosDescargarExcel -> ' . $e->getMessage());
        }
    }

    private static function asegurarModuloTrackingCancelarRuta(Database $db): void
    {
        try {
            $datos = [
                'nombre' => self::MODULO_TRACKING_CANCELAR_RUTA_NOMBRE,
                'pestana' => 'Permisos especiales',
                'descripcion' => self::MODULO_TRACKING_CANCELAR_RUTA_DESC,
            ];
            $existe = $db->queryOne(
                'SELECT id
                   FROM modulos_web
                  WHERE pestana = :pestana
                    AND (descripcion = :descripcion OR nombre = :nombre)
                  LIMIT 1',
                $datos
            );

            if ($existe) {
                $db->CRUD(
                    'UPDATE modulos_web
                        SET nombre = :nombre,
                            pestana = :pestana,
                            descripcion = :descripcion,
                            activo = 1
                      WHERE id = :id',
                    $datos + ['id' => (int) $existe['id']]
                );
                return;
            }

            $idOcupado = $db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => self::MODULO_TRACKING_CANCELAR_RUTA]
            );
            if (!$idOcupado) {
                $db->CRUD(
                    'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                     VALUES (:id, :nombre, :pestana, :descripcion, 1)',
                    $datos + ['id' => self::MODULO_TRACKING_CANCELAR_RUTA]
                );
                return;
            }

            $db->CRUD(
                'INSERT INTO modulos_web (nombre, pestana, descripcion, activo)
                 VALUES (:nombre, :pestana, :descripcion, 1)',
                $datos
            );
        } catch (\Throwable $e) {
            error_log('CapHum::asegurarModuloTrackingCancelarRuta -> ' . $e->getMessage());
        }
    }

    private static function agregarModuloConveniosDescargarExcelSiFalta(array $perfiles, int $idPersona, Database $db): array
    {
        foreach ($perfiles as $perfil) {
            if ((int) ($perfil['modulo_id'] ?? 0) === self::MODULO_CONVENIOS_DESCARGAR_EXCEL) {
                return $perfiles;
            }
        }

        $asignado = null;
        try {
            $asignado = $db->queryOne(
                'SELECT id
                   FROM asigna_modulo_web
                  WHERE usuario_id = :uid
                    AND modulo_web_id = :mid
                  LIMIT 1',
                [
                    'uid' => $idPersona,
                    'mid' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL,
                ]
            );
        } catch (\Throwable $e) {
            error_log('CapHum::agregarModuloConveniosDescargarExcelSiFalta -> ' . $e->getMessage());
        }

        $perfiles[] = [
            'usuario_id' => $idPersona,
            'modulo_id' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL,
            'modulo_nombre' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL_NOMBRE,
            'pestana' => 'Permisos especiales',
            'descripcion' => self::MODULO_CONVENIOS_DESCARGAR_EXCEL_DESC,
            'activo' => 1,
            'estado' => $asignado ? 'Asignado' : 'No asignado',
            'asignado_flag' => $asignado ? 1 : 0,
        ];

        return $perfiles;
    }
    private const DOCUMENTO_RFC_RRHH = 10;
    private const DOCUMENTO_CONSTANCIA_FISCAL_RRHH = 22;
    private const DOCUMENTOS_EXCLUIDOS_RRHH = [19, 20, 21];
    private const DOCUMENTOS_ALIAS_RRHH = [
        19 => 12, // Acta de nacimiento certificada -> Acta de Nacimiento
        20 => 9,  // Identificacion oficial duplicada -> Identificacion Oficial (INE)
    ];

    public static function fechaHoraCdmx(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
    }

    public static function asegurarTablaTrayectoriaPuesto(Database $db): void
    {
        if (self::$trayectoriaPuestoTablaAsegurada) {
            return;
        }

        $db->CRUD("CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_persona INT NOT NULL,
            accion VARCHAR(60) NOT NULL,
            id_puesto_anterior INT NULL,
            fecha_asignacion_anterior DATETIME NULL,
            id_puesto_nuevo INT NULL,
            fecha_asignacion_nueva DATETIME NULL,
            nombre_puesto_anterior VARCHAR(180) NULL,
            nombre_puesto_nuevo VARCHAR(180) NULL,
            id_departamento_anterior INT NULL,
            id_departamento_nuevo INT NULL,
            nombre_departamento_anterior VARCHAR(180) NULL,
            nombre_departamento_nuevo VARCHAR(180) NULL,
            nivel_anterior INT NULL,
            nivel_nuevo INT NULL,
            motivo VARCHAR(500) NULL,
            origen VARCHAR(80) NULL,
            creado_por INT NULL,
            creado_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY idx_puesto_trayectoria_persona (id_persona, creado_at),
            KEY idx_puesto_trayectoria_puesto_nuevo (id_puesto_nuevo),
            KEY idx_puesto_trayectoria_creado_por (creado_por)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        self::asegurarColumnaTrayectoriaPuesto($db, 'fecha_asignacion_anterior', "DATETIME NULL AFTER id_puesto_anterior");
        self::asegurarColumnaTrayectoriaPuesto($db, 'fecha_asignacion_nueva', "DATETIME NULL AFTER id_puesto_nuevo");
        self::$trayectoriaPuestoTablaAsegurada = true;
    }

    private static function asegurarColumnaTrayectoriaPuesto(Database $db, string $columna, string $definicion): void
    {
        $existe = $db->queryOne(
            "SHOW COLUMNS FROM __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria LIKE :columna",
            ['columna' => $columna]
        );
        if (!$existe) {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria ADD COLUMN {$columna} {$definicion}");
        }
    }

    public static function puestosActivosTrayectoria(Database $db, int $idPersona): array
    {
        if ($idPersona <= 0) {
            return [];
        }

        return $db->queryAll("
            SELECT
                ap.id AS id_asigna_puesto,
                ap.id_puesto,
                ap.fecha_asignacion,
                pu.nombre AS nombre_puesto,
                pu.departamento_id AS id_departamento,
                dep.nombre AS nombre_departamento,
                COALESCE(pu.nivel, 0) AS nivel
            FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
            INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pu ON pu.id = ap.id_puesto
            LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento dep ON dep.id = pu.departamento_id
            WHERE ap.id_persona = :id_persona
              AND COALESCE(ap.activo, 1) = 1
            ORDER BY COALESCE(pu.nivel, 0) DESC, ap.id ASC
        ", ['id_persona' => $idPersona]);
    }

    private static function indexarPuestosTrayectoria(array $puestos): array
    {
        $out = [];
        foreach ($puestos as $puesto) {
            $id = (int)($puesto['id_puesto'] ?? 0);
            if ($id > 0) {
                $out[$id] = $puesto;
            }
        }
        return $out;
    }

    public static function registrarTrayectoriaPuesto(
        Database $db,
        int $idPersona,
        string $accion,
        ?array $puestoAnterior = null,
        ?array $puestoNuevo = null,
        ?int $creadoPor = null,
        string $motivo = '',
        string $origen = 'gestion_personal'
    ): void {
        if ($idPersona <= 0 || trim($accion) === '') {
            return;
        }

        self::asegurarTablaTrayectoriaPuesto($db);
        $fechaCdmx = self::fechaHoraCdmx();
        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria
                (id_persona, accion, id_puesto_anterior, fecha_asignacion_anterior, id_puesto_nuevo, fecha_asignacion_nueva,
                 nombre_puesto_anterior, nombre_puesto_nuevo,
                 id_departamento_anterior, id_departamento_nuevo,
                 nombre_departamento_anterior, nombre_departamento_nuevo,
                 nivel_anterior, nivel_nuevo, motivo, origen, creado_por, creado_at)
            VALUES
                (:id_persona, :accion, :id_puesto_anterior, :fecha_asignacion_anterior, :id_puesto_nuevo, :fecha_asignacion_nueva,
                 :nombre_puesto_anterior, :nombre_puesto_nuevo,
                 :id_departamento_anterior, :id_departamento_nuevo,
                 :nombre_departamento_anterior, :nombre_departamento_nuevo,
                 :nivel_anterior, :nivel_nuevo, :motivo, :origen, :creado_por, :creado_at)
        ", [
            'id_persona' => $idPersona,
            'accion' => mb_substr(trim($accion), 0, 60),
            'id_puesto_anterior' => $puestoAnterior['id_puesto'] ?? null,
            'fecha_asignacion_anterior' => $puestoAnterior['fecha_asignacion'] ?? null,
            'id_puesto_nuevo' => $puestoNuevo['id_puesto'] ?? null,
            'fecha_asignacion_nueva' => $puestoNuevo['fecha_asignacion'] ?? null,
            'nombre_puesto_anterior' => $puestoAnterior['nombre_puesto'] ?? null,
            'nombre_puesto_nuevo' => $puestoNuevo['nombre_puesto'] ?? null,
            'id_departamento_anterior' => $puestoAnterior['id_departamento'] ?? null,
            'id_departamento_nuevo' => $puestoNuevo['id_departamento'] ?? null,
            'nombre_departamento_anterior' => $puestoAnterior['nombre_departamento'] ?? null,
            'nombre_departamento_nuevo' => $puestoNuevo['nombre_departamento'] ?? null,
            'nivel_anterior' => isset($puestoAnterior['nivel']) ? (int)$puestoAnterior['nivel'] : null,
            'nivel_nuevo' => isset($puestoNuevo['nivel']) ? (int)$puestoNuevo['nivel'] : null,
            'motivo' => mb_substr(trim($motivo), 0, 500),
            'origen' => mb_substr(trim($origen), 0, 80),
            'creado_por' => $creadoPor && $creadoPor > 0 ? $creadoPor : null,
            'creado_at' => $fechaCdmx,
        ]);
    }

    public static function registrarCambiosTrayectoriaPuestos(
        Database $db,
        int $idPersona,
        array $puestosAntes,
        array $puestosDespues,
        ?int $creadoPor = null,
        string $origen = 'gestion_personal'
    ): void {
        if ($idPersona <= 0) {
            return;
        }

        $antes = self::indexarPuestosTrayectoria($puestosAntes);
        $despues = self::indexarPuestosTrayectoria($puestosDespues);
        $idsAgregados = array_diff(array_keys($despues), array_keys($antes));
        $idsRemovidos = array_diff(array_keys($antes), array_keys($despues));

        $principalAntes = array_values($puestosAntes)[0] ?? null;
        $principalDespues = array_values($puestosDespues)[0] ?? null;
        if ($principalAntes && $principalDespues && (int)$principalAntes['id_puesto'] !== (int)$principalDespues['id_puesto']) {
            $nivelAntes = (int)($principalAntes['nivel'] ?? 0);
            $nivelDespues = (int)($principalDespues['nivel'] ?? 0);
            $accion = $nivelDespues > $nivelAntes ? 'ascenso_puesto' : 'cambio_puesto_principal';
            self::registrarTrayectoriaPuesto(
                $db,
                $idPersona,
                $accion,
                $principalAntes,
                $principalDespues,
                $creadoPor,
                $accion === 'ascenso_puesto' ? 'Aumento de puesto principal.' : 'Cambio de puesto principal.',
                $origen
            );

            $idsAgregados = array_values(array_filter($idsAgregados, function ($idPuesto) use ($principalDespues) {
                return (int)$idPuesto !== (int)($principalDespues['id_puesto'] ?? 0);
            }));
            $idsRemovidos = array_values(array_filter($idsRemovidos, function ($idPuesto) use ($principalAntes) {
                return (int)$idPuesto !== (int)($principalAntes['id_puesto'] ?? 0);
            }));
        }

        foreach ($idsAgregados as $idPuesto) {
            $puestoNuevo = $despues[$idPuesto] ?? null;
            if (!$puestoNuevo) {
                continue;
            }
            self::registrarTrayectoriaPuesto(
                $db,
                $idPersona,
                empty($antes) ? 'alta_puesto' : 'agrego_puesto',
                null,
                $puestoNuevo,
                $creadoPor,
                empty($antes) ? 'Asignacion inicial del colaborador.' : 'Puesto agregado al colaborador.',
                $origen
            );
        }

        foreach ($idsRemovidos as $idPuesto) {
            $puestoAnterior = $antes[$idPuesto] ?? null;
            if (!$puestoAnterior) {
                continue;
            }
            self::registrarTrayectoriaPuesto(
                $db,
                $idPersona,
                'removio_puesto',
                $puestoAnterior,
                null,
                $creadoPor,
                'Puesto retirado del colaborador.',
                $origen
            );
        }
    }

    public static function sembrarTrayectoriaPuestosActuales(Database $db, ?int $creadoPor = null): int
    {
        self::asegurarTablaTrayectoriaPuesto($db);
        $fechaCdmx = self::fechaHoraCdmx();
        return $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria
                (id_persona, accion, id_puesto_anterior, fecha_asignacion_anterior, id_puesto_nuevo, fecha_asignacion_nueva,
                 nombre_puesto_anterior, nombre_puesto_nuevo,
                 id_departamento_anterior, id_departamento_nuevo,
                 nombre_departamento_anterior, nombre_departamento_nuevo,
                 nivel_anterior, nivel_nuevo, motivo, origen, creado_por, creado_at)
            SELECT
                ap.id_persona,
                CASE
                    WHEN NOT EXISTS (
                        SELECT 1
                        FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap2
                        INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pu2 ON pu2.id = ap2.id_puesto
                        WHERE ap2.id_persona = ap.id_persona
                          AND COALESCE(ap2.activo, 1) = 1
                          AND (
                              COALESCE(pu2.nivel, 0) > COALESCE(pu.nivel, 0)
                              OR (COALESCE(pu2.nivel, 0) = COALESCE(pu.nivel, 0) AND ap2.id < ap.id)
                          )
                    )
                    THEN 'alta_puesto'
                    ELSE 'agrego_puesto'
                END AS accion,
                NULL AS id_puesto_anterior,
                NULL AS fecha_asignacion_anterior,
                ap.id_puesto AS id_puesto_nuevo,
                ap.fecha_asignacion AS fecha_asignacion_nueva,
                NULL AS nombre_puesto_anterior,
                pu.nombre AS nombre_puesto_nuevo,
                NULL AS id_departamento_anterior,
                pu.departamento_id AS id_departamento_nuevo,
                NULL AS nombre_departamento_anterior,
                dep.nombre AS nombre_departamento_nuevo,
                NULL AS nivel_anterior,
                COALESCE(pu.nivel, 0) AS nivel_nuevo,
                CASE
                    WHEN NOT EXISTS (
                        SELECT 1
                        FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap3
                        INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pu3 ON pu3.id = ap3.id_puesto
                        WHERE ap3.id_persona = ap.id_persona
                          AND COALESCE(ap3.activo, 1) = 1
                          AND (
                              COALESCE(pu3.nivel, 0) > COALESCE(pu.nivel, 0)
                              OR (COALESCE(pu3.nivel, 0) = COALESCE(pu.nivel, 0) AND ap3.id < ap.id)
                          )
                    )
                    THEN 'Linea base de trayectoria creada desde el puesto activo actual.'
                    ELSE 'Linea base de puesto adicional activo actual.'
                END AS motivo,
                'semilla_estado_actual' AS origen,
                :creado_por AS creado_por,
                COALESCE(ap.fecha_asignacion, :fecha_cdmx) AS creado_at
            FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
            INNER JOIN __SPARTA_SECRET_REDACTED__.persona p ON p.id = ap.id_persona
            INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pu ON pu.id = ap.id_puesto
            LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento dep ON dep.id = pu.departamento_id
            WHERE COALESCE(ap.activo, 1) = 1
              AND p.estatus != 'Baja'
              AND NOT EXISTS (
                  SELECT 1
                  FROM __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria t
                  WHERE t.id_persona = ap.id_persona
              )
        ", [
            'creado_por' => $creadoPor && $creadoPor > 0 ? $creadoPor : null,
            'fecha_cdmx' => $fechaCdmx,
        ]);
    }

    public static function actualizarFechasTrayectoriaDesdeAsignaPuesto(Database $db): int
    {
        self::asegurarTablaTrayectoriaPuesto($db);
        return $db->CRUD("
            UPDATE __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria t
            SET
                t.fecha_asignacion_nueva = COALESCE(
                    t.fecha_asignacion_nueva,
                    (
                        SELECT ap.fecha_asignacion
                        FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                        WHERE ap.id_persona = t.id_persona
                          AND ap.id_puesto = t.id_puesto_nuevo
                        ORDER BY COALESCE(ap.activo, 0) DESC, ap.id DESC
                        LIMIT 1
                    )
                ),
                t.fecha_asignacion_anterior = COALESCE(
                    t.fecha_asignacion_anterior,
                    (
                        SELECT ap2.fecha_asignacion
                        FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap2
                        WHERE ap2.id_persona = t.id_persona
                          AND ap2.id_puesto = t.id_puesto_anterior
                        ORDER BY COALESCE(ap2.activo, 0) DESC, ap2.id DESC
                        LIMIT 1
                    )
                ),
                t.creado_at = CASE
                    WHEN t.origen IN ('semilla_estado_actual', 'estado_actual')
                         AND COALESCE(t.fecha_asignacion_nueva, '') <> ''
                    THEN t.fecha_asignacion_nueva
                    ELSE t.creado_at
                END
            WHERE t.fecha_asignacion_nueva IS NULL
               OR t.fecha_asignacion_anterior IS NULL
               OR (t.origen IN ('semilla_estado_actual', 'estado_actual')
                   AND t.fecha_asignacion_nueva IS NOT NULL
                   AND t.creado_at <> t.fecha_asignacion_nueva)
        ");
    }

    public static function getTrayectoriaPuestoPersona(int $idPersona): array
    {
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona invalido.', []);
        }

        try {
            $db = new Database();
            self::asegurarTablaTrayectoriaPuesto($db);
            $rows = $db->queryAll("
                SELECT
                    t.*,
                    CASE
                        WHEN t.origen IN ('semilla_estado_actual', 'estado_actual')
                            THEN COALESCE(t.fecha_asignacion_nueva, t.creado_at)
                        ELSE t.creado_at
                    END AS fecha_movimiento,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS responsable_nombre
                FROM __SPARTA_SECRET_REDACTED__.persona_puesto_trayectoria t
                LEFT JOIN __SPARTA_SECRET_REDACTED__.persona p ON p.id = t.creado_por
                WHERE t.id_persona = :id_persona
                ORDER BY t.creado_at DESC, t.id DESC
            ", ['id_persona' => $idPersona]);

            if (empty($rows)) {
                foreach (self::puestosActivosTrayectoria($db, $idPersona) as $puestoActual) {
                    $rows[] = [
                        'id' => 0,
                        'id_persona' => $idPersona,
                        'accion' => 'puesto_actual',
                        'id_puesto_anterior' => null,
                        'fecha_asignacion_anterior' => null,
                        'id_puesto_nuevo' => $puestoActual['id_puesto'] ?? null,
                        'fecha_asignacion_nueva' => $puestoActual['fecha_asignacion'] ?? null,
                        'nombre_puesto_anterior' => null,
                        'nombre_puesto_nuevo' => $puestoActual['nombre_puesto'] ?? '',
                        'id_departamento_anterior' => null,
                        'id_departamento_nuevo' => $puestoActual['id_departamento'] ?? null,
                        'nombre_departamento_anterior' => null,
                        'nombre_departamento_nuevo' => $puestoActual['nombre_departamento'] ?? '',
                        'nivel_anterior' => null,
                        'nivel_nuevo' => $puestoActual['nivel'] ?? null,
                        'motivo' => 'Puesto activo actual. No hay movimientos históricos registrados todavía.',
                        'origen' => 'estado_actual',
                        'creado_por' => null,
                        'creado_at' => null,
                        'fecha_movimiento' => $puestoActual['fecha_asignacion'] ?? null,
                        'responsable_nombre' => 'Sistema',
                    ];
                }
            }

            return self::resultado(true, 'Trayectoria consultada correctamente.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar trayectoria de puesto.', [], $e->getMessage());
        }
    }

    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ///
    ///

        /**
     * Consulta bajas con filtros avanzados (departamento, puesto, estatus, multipuesto)
     * @param array $filtros
     * @return array
     */

    public static function getConsultaGestoresAll($id_gestor_sesion, $tieneDepartamento = true)
    {
        $id_gestor_sesion = (int)$id_gestor_sesion;
        $sqlExP = UsuarioFantasmaReporteria::sqlExcluirPersona('p');
        $sqlExP2 = UsuarioFantasmaReporteria::sqlExcluirPersona('p2');

        // =========================
        // VER TODOS: admin O sin departamento asignado (módulo 10)
        // Si no tiene "Organización > Departamentos" asignado → ver todos los usuarios.
        // =========================
        $verTodos = in_array($id_gestor_sesion, [1, 2, 3, 396, 797], true);
        $filtroPuestosSesion = $verTodos ? '' : "
        AND EXISTS (
            SELECT 1
            FROM privilegios_departamento pd_perm
            WHERE pd_perm.idPersona = $id_gestor_sesion
              AND pd_perm.idPuesto = ap.id_puesto
        )";

        if (true) {

            $query = <<<SQL
            SELECT
            p.id,
            p.numero_empleado,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            pf.foto AS foto_perfil,

            pp.id AS id_puesto,
            CASE
                WHEN pp.nombre IS NULL THEN 'Sin puesto'
                ELSE pp.nombre
            END AS nombre_puesto,
            pp.nivel AS nivel_puesto,

            d.id AS id_departamento,
            CASE
                WHEN d.nombre IS NULL THEN 'Sin departamento'
                ELSE d.nombre
            END AS nombre_departamento,

            aj.id_jefe,
            aj.id_vacante_jefe,

            CASE
                WHEN pj.id IS NULL THEN 'Sin jefe'
                ELSE CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)
            END AS nombre_jefe,

            CASE
                WHEN vj.id IS NULL THEN NULL
                ELSE CONCAT('Vacante #', vj.id, ' - ', COALESCE(pvj.nombre, 'Sin puesto'))
            END AS nombre_vacante_jefe,

            p.estatus,
            CASE
                WHEN p.user_name IS NULL THEN 'Sin usuario'
                ELSE p.user_name
            END AS usuario,

            COALESCE(pais.id, 0) AS id_pais,
            COALESCE(pais.nombre, 'Sin país') AS nombre_pais,
            COALESCE(pais.codigo_iso, 'xx') AS codigo_iso_pais,

            p.fecha_ingreso,
            p.fecha_registro

        FROM persona p

        LEFT JOIN perfil pf
               ON pf.id_persona = p.id

        LEFT JOIN asigna_puesto ap
               ON p.id = ap.id_persona
              AND COALESCE(ap.activo, 1) = 1

        LEFT JOIN puesto pp
               ON pp.id = ap.id_puesto

        LEFT JOIN departamento d
               ON d.id = pp.departamento_id

        LEFT JOIN paises pais
               ON pais.id = p.id_pais

        LEFT JOIN (
            SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
            FROM asigna_jefe a
            INNER JOIN (
                SELECT id_persona, MAX(id) AS mid
                FROM asigna_jefe
                GROUP BY id_persona
            ) m ON a.id_persona = m.id_persona AND a.id = m.mid
        ) aj ON aj.id_persona = p.id

        LEFT JOIN persona pj
               ON pj.id = aj.id_jefe

        LEFT JOIN vacantes_personal vj
               ON vj.id = aj.id_vacante_jefe

        LEFT JOIN puesto pvj
               ON pvj.id = vj.id_puesto

        WHERE p.estatus != 'Baja'
        {$sqlExP}
        {$filtroPuestosSesion}

        ORDER BY pp.nivel ASC;

        SQL;

        }
        // =========================
        // USUARIOS NORMALES (JERARQUÍA)
        // =========================
        else {

            $query = <<<SQL
        WITH RECURSIVE Jerarquia AS (

            -- =====================
            -- NIVEL RAÍZ
            -- =====================
            SELECT
                p.id,
                p.nombres,
                p.segundo_nombre,
                p.apellidop,
                p.apellidom,
                pf.foto AS foto_perfil,
                pp.id AS id_puesto,
                pp.nombre AS nombre_puesto,
                pp.nivel AS nivel_puesto,
                d.id AS id_departamento,
                d.nombre AS nombre_departamento,
                aj.id_jefe,
                aj.id_vacante_jefe,
                CASE
                    WHEN vj.id IS NULL THEN NULL
                    ELSE CONCAT('Vacante #', vj.id, ' - ', COALESCE(pvj.nombre, 'Sin puesto'))
                END AS nombre_vacante_jefe,
                p.estatus,
                COALESCE(pais.id, 0) AS id_pais,
                COALESCE(pais.nombre, 'Sin país') AS nombre_pais,
                COALESCE(pais.codigo_iso, 'xx') AS codigo_iso_pais,
                p.fecha_ingreso,
                p.fecha_registro,
                1 AS nivel
            FROM persona p
            LEFT JOIN perfil pf ON pf.id_persona = p.id
            LEFT JOIN asigna_puesto ap ON p.id = ap.id_persona AND COALESCE(ap.activo, 1) = 1
            LEFT JOIN puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN paises pais ON pais.id = p.id_pais
            LEFT JOIN asigna_jefe aj
                   ON p.id = aj.id_persona
                  AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            LEFT JOIN vacantes_personal vj
                   ON vj.id = aj.id_vacante_jefe
            LEFT JOIN puesto pvj
                   ON pvj.id = vj.id_puesto
            WHERE p.estatus != 'Baja'
              {$sqlExP}AND (
                    aj.id_jefe = $id_gestor_sesion
                    OR aj.id_jefe IS NULL
                    OR NOT EXISTS (
                        SELECT 1
                        FROM persona jefe_activo
                        WHERE jefe_activo.id = aj.id_jefe
                          AND jefe_activo.estatus != 'Baja'
                    )
                  )

            UNION ALL

            -- =====================
            -- SUBORDINADOS
            -- =====================
            SELECT
                p2.id,
                p2.nombres,
                p2.segundo_nombre,
                p2.apellidop,
                p2.apellidom,
                pf2.foto AS foto_perfil,
                pp2.id AS id_puesto,
                pp2.nombre AS nombre_puesto,
                pp2.nivel AS nivel_puesto,
                d2.id AS id_departamento,
                d2.nombre AS nombre_departamento,
                aj2.id_jefe,
                aj2.id_vacante_jefe,
                CASE
                    WHEN vj2.id IS NULL THEN NULL
                    ELSE CONCAT('Vacante #', vj2.id, ' - ', COALESCE(pvj2.nombre, 'Sin puesto'))
                END AS nombre_vacante_jefe,
                p2.estatus,
                COALESCE(pais2.id, 0) AS id_pais,
                COALESCE(pais2.nombre, 'Sin país') AS nombre_pais,
                COALESCE(pais2.codigo_iso, 'xx') AS codigo_iso_pais,
                p2.fecha_ingreso,
                p2.fecha_registro,
                j.nivel + 1 AS nivel
            FROM persona p2
            LEFT JOIN perfil pf2 ON pf2.id_persona = p2.id
            LEFT JOIN asigna_puesto ap2 ON p2.id = ap2.id_persona AND COALESCE(ap2.activo, 1) = 1
            LEFT JOIN puesto pp2 ON pp2.id = ap2.id_puesto
            LEFT JOIN departamento d2 ON d2.id = pp2.departamento_id
            LEFT JOIN paises pais2 ON pais2.id = p2.id_pais
            LEFT JOIN asigna_jefe aj2
                   ON p2.id = aj2.id_persona
                  AND (aj2.fecha_fin IS NULL OR aj2.fecha_fin >= CURDATE())
            LEFT JOIN vacantes_personal vj2
                   ON vj2.id = aj2.id_vacante_jefe
            LEFT JOIN puesto pvj2
                   ON pvj2.id = vj2.id_puesto
            JOIN Jerarquia j
                 ON aj2.id_jefe = j.id
            WHERE p2.estatus != 'Baja'
              {$sqlExP2}
        )

        SELECT *
        FROM Jerarquia
        ORDER BY nivel_puesto ASC, nivel ASC;
        SQL;
        }


        try {
            $db = new Database();
            self::asegurarAsignaJefeSoportaVacante($db);
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getDatosReasignacionBaja($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona invalido.', null);
        }

        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarAsignaJefeSoportaVacante($db);

            $puestosPersona = $db->queryAll("
                SELECT
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    pp.departamento_id,
                    d.nombre AS nombre_departamento
                FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = pp.departamento_id
                WHERE ap.id_persona = :id
                  AND COALESCE(ap.activo, 1) = 1
                ORDER BY pp.nivel DESC, ap.id DESC
            ", ['id' => $idPersona]);
            $puestoPersona = $puestosPersona[0] ?? null;

            $subordinados = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                    COALESCE(pp.nombre, 'Sin puesto') AS nombre_puesto,
                    COALESCE(d.nombre, 'Sin departamento') AS nombre_departamento
                FROM __SPARTA_SECRET_REDACTED__.asigna_jefe aj
                INNER JOIN __SPARTA_SECRET_REDACTED__.persona p ON p.id = aj.id_persona
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = pp.departamento_id
                WHERE aj.id_jefe = :id
                  AND p.estatus != 'Baja'
                ORDER BY nombre_completo ASC
            ", ['id' => $idPersona]);

            $personas = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                    COALESCE(pp.nombre, 'Sin puesto') AS nombre_puesto,
                    COALESCE(d.nombre, 'Sin departamento') AS nombre_departamento
                FROM __SPARTA_SECRET_REDACTED__.persona p
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = pp.departamento_id
                WHERE p.estatus != 'Baja'
                  AND p.id <> :id
                ORDER BY nombre_completo ASC
            ", ['id' => $idPersona]);

            $vacantesMismoPuesto = [];
            if (!empty($puestosPersona)) {
                $paramsVacantes = [];
                $idsPuesto = [];
                $nombresPuesto = [];
                foreach ($puestosPersona as $idxPuesto => $puestoActivo) {
                    if (!empty($puestoActivo['id_puesto'])) {
                        $key = 'id_puesto_' . $idxPuesto;
                        $idsPuesto[] = ':' . $key;
                        $paramsVacantes[$key] = (int)$puestoActivo['id_puesto'];
                    }
                    if (!empty($puestoActivo['nombre_puesto'])) {
                        $key = 'nombre_puesto_' . $idxPuesto;
                        $nombresPuesto[] = ':' . $key;
                        $nombrePuesto = trim((string)$puestoActivo['nombre_puesto']);
                        $paramsVacantes[$key] = function_exists('mb_strtoupper') ? mb_strtoupper($nombrePuesto, 'UTF-8') : strtoupper($nombrePuesto);
                    }
                }

                $condicionesVacante = [];
                if (!empty($idsPuesto)) {
                    $condicionesVacante[] = 'v.id_puesto IN (' . implode(',', $idsPuesto) . ')';
                }
                if (!empty($nombresPuesto)) {
                    $condicionesVacante[] = 'UPPER(TRIM(pp.nombre)) IN (' . implode(',', $nombresPuesto) . ')';
                }

                if (!empty($condicionesVacante)) {
                    $vacantesMismoPuesto = $db->queryAll("
                    SELECT
                        v.id,
                        v.id_jefe,
                        v.id_departamento,
                        v.id_puesto,
                        v.origen,
                        v.fecha_creacion,
                        pp.nombre AS nombre_puesto,
                        d.nombre AS nombre_departamento,
                        CONCAT_WS(' ', jefe.nombres, jefe.segundo_nombre, jefe.apellidop, jefe.apellidom) AS nombre_jefe
                    FROM __SPARTA_SECRET_REDACTED__.vacantes_personal v
                    INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = v.id_puesto
                    LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = v.id_departamento
                    LEFT JOIN __SPARTA_SECRET_REDACTED__.persona jefe ON jefe.id = v.id_jefe
                    WHERE v.estatus = 'Activa'
                      AND (" . implode(' OR ', $condicionesVacante) . ")
                    ORDER BY v.fecha_creacion ASC
                    ", $paramsVacantes);
                }
            }

            return self::resultado(true, 'Datos de reasignacion encontrados.', [
                'subordinados' => $subordinados,
                'personas' => $personas,
                'puesto_baja' => $puestoPersona,
                'puestos_baja' => $puestosPersona,
                'vacantes_mismo_puesto' => $vacantesMismoPuesto
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener datos de reasignacion.', null, $e->getMessage());
        }
    }

    private static function asegurarTablaVacantesPersonal(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.vacantes_personal (
                id INT AUTO_INCREMENT PRIMARY KEY,
                id_departamento INT NOT NULL,
                id_puesto INT NOT NULL,
                nombre_vacante VARCHAR(180) NULL,
                id_jefe INT NULL,
                id_persona_baja INT NULL,
                id_persona_cubre INT NULL,
                origen VARCHAR(30) NOT NULL DEFAULT 'manual',
                estatus VARCHAR(20) NOT NULL DEFAULT 'Activa',
                creado_por INT NULL,
                fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_cierre DATETIME NULL,
                INDEX idx_vacantes_personal_jefe (id_jefe),
                INDEX idx_vacantes_personal_persona_cubre (id_persona_cubre),
                INDEX idx_vacantes_personal_depto (id_departamento),
                INDEX idx_vacantes_personal_puesto (id_puesto),
                INDEX idx_vacantes_personal_estatus (estatus)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $columnaCubre = $db->queryOne("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'vacantes_personal'
              AND COLUMN_NAME = 'id_persona_cubre'
            LIMIT 1
        ");
        if (!$columnaCubre) {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.vacantes_personal ADD COLUMN id_persona_cubre INT NULL AFTER id_persona_baja");
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.vacantes_personal ADD INDEX idx_vacantes_personal_persona_cubre (id_persona_cubre)");
        }

        $columnaNombreVacante = $db->queryOne("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'vacantes_personal'
              AND COLUMN_NAME = 'nombre_vacante'
            LIMIT 1
        ");
        if (!$columnaNombreVacante) {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.vacantes_personal ADD COLUMN nombre_vacante VARCHAR(180) NULL AFTER id_puesto");
        }
    }

    private static function asegurarAsignaJefeSoportaVacante(Database $db): void
    {
        $columna = $db->queryOne("
            SELECT IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'asigna_jefe'
              AND COLUMN_NAME = 'id_jefe'
            LIMIT 1
        ");

        if ($columna && strtoupper((string)$columna['IS_NULLABLE']) !== 'YES') {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.asigna_jefe MODIFY id_jefe INT NULL");
        }

        $columnaVacante = $db->queryOne("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'asigna_jefe'
              AND COLUMN_NAME = 'id_vacante_jefe'
            LIMIT 1
        ");

        if (!$columnaVacante) {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.asigna_jefe ADD COLUMN id_vacante_jefe INT NULL AFTER id_jefe");
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.asigna_jefe ADD INDEX idx_vacante_jefe (id_vacante_jefe)");
        }
    }

    private static function divisionesAdministrativasApiConfig(): array
    {
        $cfg = function_exists('config_api_load_from_db') ? config_api_load_from_db() : [];
        $leerValor = static function (array $keys) use ($cfg): string {
            foreach ($keys as $key) {
                $valor = trim((string)($cfg[$key] ?? ''));
                if ($valor !== '') {
                    return $valor;
                }
                $env = getenv($key);
                if ($env !== false && trim((string)$env) !== '') {
                    return trim((string)$env);
                }
            }
            return '';
        };

        $baseUrl = $leerValor(['DIVISIONES_ADMINISTRATIVAS_API_BASE_URL', 'MOTOS_ADJUDICADAS_PUSH_BASE_URL']);
        if ($baseUrl === '') {
            $baseUrl = 'https://motosadjudicadas-601258367060.us-central1.run.app/api/divisiones-administrativas';
        }
        $baseUrl = rtrim($baseUrl, '/');
        if (!preg_match('#/api/divisiones-administrativas$#', $baseUrl)) {
            $baseUrl .= '/api/divisiones-administrativas';
        }

        $apiKey = $leerValor(['DIVISIONES_ADMINISTRATIVAS_API_KEY', 'MOTOS_ADJUDICADAS_API_KEY', 'MOTOS_ADJUDICADAS_TOKEN']);
        if ($apiKey === '') {
            $apiKey = 'ARt4a6Atn0VhiPJ_0bgXeprr9DUuSAQ7b3oKzICSTy0';
        }

        return [
            'base_url' => $baseUrl,
            'api_key' => $apiKey,
        ];
    }

    private static function divisionesAdministrativasApiGet(string $path, array $query = []): array
    {
        $cfg = self::divisionesAdministrativasApiConfig();
        if ($cfg['base_url'] === '' || $cfg['api_key'] === '' || !function_exists('curl_init')) {
            return ['success' => false, 'datos' => []];
        }

        $url = $cfg['base_url'] . '/' . ltrim($path, '/');
        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'X-API-Key: ' . $cfg['api_key'],
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $raw = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode($raw === false ? '' : (string)$raw, true);
        if ($httpCode < 200 || $httpCode >= 300 || !is_array($json) || empty($json['success'])) {
            return ['success' => false, 'datos' => []];
        }

        $datos = is_array($json['data'] ?? null) ? $json['data'] : [];
        return ['success' => true, 'datos' => $datos];
    }

    private static function normalizarDivisionAdministrativaApi(array $row): array
    {
        return [
            'id' => $row['id'] ?? null,
            'nombre' => $row['nombre'] ?? '',
            'codigo_interno' => $row['codigo_interno'] ?? null,
            'codigo_iso' => $row['codigo_iso'] ?? null,
            'id_padre' => $row['id_padre'] ?? null,
            'tipo_label' => $row['tipo_nombre'] ?? $row['tipo_label'] ?? '',
            'tipo_codigo' => $row['tipo_codigo'] ?? '',
        ];
    }

    public static function crearVacantePersonal($data)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idDepartamento = (int)($data['id_departamento'] ?? 0);
            $idPuesto = (int)($data['id_puesto'] ?? 0);
            $idJefe = !empty($data['id_jefe']) ? (int)$data['id_jefe'] : null;
            $idPersonaBaja = !empty($data['id_persona_baja']) ? (int)$data['id_persona_baja'] : null;
            $origen = trim((string)($data['origen'] ?? 'manual'));
            $creadoPor = !empty($data['creado_por']) ? (int)$data['creado_por'] : null;

            if ($idDepartamento <= 0 || $idPuesto <= 0) {
                return self::resultado(false, 'Departamento y puesto son obligatorios para registrar la vacante.');
            }

            $db->CRUD("
                INSERT INTO __SPARTA_SECRET_REDACTED__.vacantes_personal
                    (id_departamento, id_puesto, id_jefe, id_persona_baja, origen, estatus, creado_por)
                VALUES
                    (:id_departamento, :id_puesto, :id_jefe, :id_persona_baja, :origen, 'Activa', :creado_por)
            ", [
                'id_departamento' => $idDepartamento,
                'id_puesto' => $idPuesto,
                'id_jefe' => $idJefe,
                'id_persona_baja' => $idPersonaBaja,
                'origen' => $origen !== '' ? $origen : 'manual',
                'creado_por' => $creadoPor,
            ]);

            $id = $db->lastInsertId();
            return self::resultado(true, 'Vacante registrada correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar la vacante.', null, $e->getMessage());
        }
    }

    public static function getVacantesDisponiblesParaAsignar($idDepartamento, $idPuesto)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idDepartamento = (int)$idDepartamento;
            $idPuesto = (int)$idPuesto;
            if ($idDepartamento <= 0 || $idPuesto <= 0) {
                return self::resultado(true, 'Vacantes encontradas.', []);
            }

            $rows = $db->queryAll("
                SELECT
                    v.id,
                    v.id_departamento,
                    v.id_puesto,
                    v.id_jefe,
                    v.nombre_vacante,
                    v.origen,
                    v.fecha_creacion,
                    COALESCE(NULLIF(TRIM(v.nombre_vacante), ''), pp.nombre) AS nombre_puesto,
                    pp.nombre AS nombre_puesto_base,
                    d.nombre AS nombre_departamento,
                    CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom) AS nombre_jefe,
                    COUNT(DISTINCT ps.id) AS subordinados
                FROM __SPARTA_SECRET_REDACTED__.vacantes_personal v
                INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = v.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = v.id_departamento
                LEFT JOIN __SPARTA_SECRET_REDACTED__.persona pj ON pj.id = v.id_jefe
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_jefe ajv ON ajv.id_vacante_jefe = v.id
                LEFT JOIN __SPARTA_SECRET_REDACTED__.persona ps ON ps.id = ajv.id_persona AND ps.estatus != 'Baja'
                WHERE v.id_departamento = :id_departamento
                  AND v.id_puesto = :id_puesto
                  AND UPPER(TRIM(v.estatus)) = 'ACTIVA'
                GROUP BY v.id, v.id_departamento, v.id_puesto, v.id_jefe, v.nombre_vacante, v.origen, v.fecha_creacion, pp.nombre, d.nombre, nombre_jefe
                ORDER BY v.fecha_creacion ASC, v.id ASC
            ", [
                'id_departamento' => $idDepartamento,
                'id_puesto' => $idPuesto,
            ]);

            return self::resultado(true, 'Vacantes encontradas.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar vacantes disponibles.', null, $e->getMessage());
        }
    }

    public static function getVacantesJefeDirecto($idDepartamento, $idPuesto = null)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idDepartamento = (int)$idDepartamento;
            $idPuesto = (int)$idPuesto;
            if ($idDepartamento <= 0) {
                return self::resultado(true, 'Vacantes encontradas.', []);
            }

            $params = ['id_departamento' => $idDepartamento];
            $whereNivel = 'AND pp.es_jefe = 1';
            if ($idPuesto > 0) {
                $whereNivel = "
                    AND pp.nivel > (
                        SELECT nivel
                        FROM __SPARTA_SECRET_REDACTED__.puesto
                        WHERE id = :id_puesto_ref
                        LIMIT 1
                    )
                ";
                $params['id_puesto_ref'] = $idPuesto;
            }

            $rows = $db->queryAll("
                SELECT
                    v.id,
                    v.id_departamento,
                    v.id_puesto,
                    v.nombre_vacante,
                    COALESCE(NULLIF(TRIM(v.nombre_vacante), ''), pp.nombre) AS nombre_puesto,
                    pp.nombre AS nombre_puesto_base,
                    d.nombre AS nombre_departamento,
                    COUNT(DISTINCT ps.id) AS subordinados
                FROM __SPARTA_SECRET_REDACTED__.vacantes_personal v
                INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = v.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = v.id_departamento
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_jefe ajv ON ajv.id_vacante_jefe = v.id
                LEFT JOIN __SPARTA_SECRET_REDACTED__.persona ps ON ps.id = ajv.id_persona AND ps.estatus != 'Baja'
                WHERE v.id_departamento = :id_departamento
                  AND UPPER(TRIM(v.estatus)) = 'ACTIVA'
                  $whereNivel
                GROUP BY v.id, v.id_departamento, v.id_puesto, v.nombre_vacante, pp.nombre, d.nombre
                ORDER BY pp.nivel ASC, v.fecha_creacion ASC, v.id ASC
            ", $params);

            return self::resultado(true, 'Vacantes encontradas.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar vacantes para jefe directo.', null, $e->getMessage());
        }
    }

    public static function actualizarJefeVacantePersonal($idVacante, $idJefe)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idVacante = (int)$idVacante;
            $idJefe = (int)$idJefe;

            if ($idVacante <= 0 || $idJefe <= 0) {
                return self::resultado(false, 'Seleccione la vacante y el jefe destino.');
            }

            $vacante = $db->queryOne("
                SELECT id, id_departamento, id_puesto, id_jefe, estatus
                FROM __SPARTA_SECRET_REDACTED__.vacantes_personal
                WHERE id = :id
                LIMIT 1
            ", ['id' => $idVacante]);

            if (!$vacante || strtoupper(trim((string)($vacante['estatus'] ?? ''))) !== 'ACTIVA') {
                return self::resultado(false, 'La vacante ya no esta activa.');
            }

            $jefe = $db->queryOne("
                SELECT p.id
                FROM __SPARTA_SECRET_REDACTED__.persona p
                WHERE p.id = :id_jefe
                  AND COALESCE(p.estatus, '') != 'Baja'
                LIMIT 1
            ", ['id_jefe' => $idJefe]);

            if (!$jefe) {
                return self::resultado(false, 'El jefe seleccionado no esta activo.');
            }

            $db->CRUD("
                UPDATE __SPARTA_SECRET_REDACTED__.vacantes_personal
                SET id_jefe = :id_jefe
                WHERE id = :id_vacante
                LIMIT 1
            ", [
                'id_jefe' => $idJefe,
                'id_vacante' => $idVacante,
            ]);

            return self::resultado(true, 'Jefe de vacante actualizado correctamente.', [
                'id_vacante' => $idVacante,
                'id_jefe' => $idJefe,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar el jefe de la vacante.', null, $e->getMessage());
        }
    }

    public static function actualizarNombreVacantePersonal($idVacante, $nombreVacante)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);

            $idVacante = (int)$idVacante;
            $nombreVacante = trim(preg_replace('/\s+/', ' ', (string)$nombreVacante));
            $nombreVacante = trim(preg_replace('/\s*\(\s*vacante\s*\)\s*$/iu', '', $nombreVacante));

            if ($idVacante <= 0) {
                return self::resultado(false, 'Seleccione la vacante.');
            }
            if ($nombreVacante === '' || mb_strlen($nombreVacante) < 3) {
                return self::resultado(false, 'Escribe un nombre valido para la vacante.');
            }
            if (mb_strlen($nombreVacante) > 180) {
                return self::resultado(false, 'El nombre de la vacante no debe superar 180 caracteres.');
            }

            $vacante = $db->queryOne("
                SELECT id, estatus
                FROM __SPARTA_SECRET_REDACTED__.vacantes_personal
                WHERE id = :id
                LIMIT 1
            ", ['id' => $idVacante]);

            if (!$vacante || strtoupper(trim((string)($vacante['estatus'] ?? ''))) !== 'ACTIVA') {
                return self::resultado(false, 'La vacante ya no esta activa.');
            }

            $db->CRUD("
                UPDATE __SPARTA_SECRET_REDACTED__.vacantes_personal
                SET nombre_vacante = :nombre_vacante
                WHERE id = :id_vacante
                LIMIT 1
            ", [
                'nombre_vacante' => $nombreVacante,
                'id_vacante' => $idVacante,
            ]);

            return self::resultado(true, 'Nombre de vacante actualizado correctamente.', [
                'id_vacante' => $idVacante,
                'nombre_vacante' => $nombreVacante,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar el nombre de la vacante.', null, $e->getMessage());
        }
    }

    public static function eliminarVacantePersonal($idVacante, $modoMovimiento, $idJefeDestino = 0)
    {
        $db = null;
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarAsignaJefeSoportaVacante($db);

            $idVacante = (int)$idVacante;
            $modoMovimiento = trim((string)$modoMovimiento);
            $idJefeDestino = (int)$idJefeDestino;

            if ($idVacante <= 0) {
                return self::resultado(false, 'Seleccione la vacante a eliminar.');
            }
            if (!in_array($modoMovimiento, ['jefe_superior', 'jefe_destino'], true)) {
                return self::resultado(false, 'Seleccione como se moveran los subordinados.');
            }

            $vacante = $db->queryOne("
                SELECT v.id, v.id_jefe, v.id_departamento, v.id_puesto, v.estatus,
                       COALESCE(NULLIF(TRIM(v.nombre_vacante), ''), pp.nombre) AS nombre_puesto
                FROM __SPARTA_SECRET_REDACTED__.vacantes_personal v
                INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = v.id_puesto
                WHERE v.id = :id
                LIMIT 1
            ", ['id' => $idVacante]);

            if (!$vacante || strtoupper(trim((string)($vacante['estatus'] ?? ''))) !== 'ACTIVA') {
                return self::resultado(false, 'La vacante ya no esta activa.');
            }

            $jefeDestino = $modoMovimiento === 'jefe_superior' ? (int)($vacante['id_jefe'] ?? 0) : $idJefeDestino;
            if ($jefeDestino <= 0) {
                return self::resultado(false, 'Seleccione un jefe destino para mover los subordinados.');
            }

            $jefe = $db->queryOne("
                SELECT id
                FROM __SPARTA_SECRET_REDACTED__.persona
                WHERE id = :id_jefe
                  AND COALESCE(estatus, '') != 'Baja'
                LIMIT 1
            ", ['id_jefe' => $jefeDestino]);

            if (!$jefe) {
                return self::resultado(false, 'El jefe destino no esta activo.');
            }

            $subordinadosDirectos = $db->queryAll("
                SELECT aj.id_persona
                FROM __SPARTA_SECRET_REDACTED__.asigna_jefe aj
                INNER JOIN (
                    SELECT id_persona, MAX(id) AS mid
                    FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                    GROUP BY id_persona
                ) ult ON ult.id_persona = aj.id_persona AND ult.mid = aj.id
                INNER JOIN __SPARTA_SECRET_REDACTED__.persona p
                        ON p.id = aj.id_persona
                       AND COALESCE(p.estatus, '') != 'Baja'
                WHERE aj.id_vacante_jefe = :id_vacante
            ", ['id_vacante' => $idVacante]);

            foreach ($subordinadosDirectos as $sub) {
                if ((int)($sub['id_persona'] ?? 0) === $jefeDestino) {
                    return self::resultado(false, 'No puedes mover la vacante a una persona que depende directamente de esa misma vacante.');
                }
            }

            $db->beginTransaction();
            $subordinadosMovidos = $db->CRUD("
                UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe aj
                INNER JOIN (
                    SELECT ult.mid
                    FROM (
                        SELECT id_persona, MAX(id) AS mid
                        FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                        GROUP BY id_persona
                    ) ult
                ) vigente ON vigente.mid = aj.id
                SET aj.id_jefe = :id_jefe,
                    aj.id_vacante_jefe = NULL
                WHERE aj.id_vacante_jefe = :id_vacante
            ", [
                'id_jefe' => $jefeDestino,
                'id_vacante' => $idVacante,
            ]);

            $db->CRUD("
                UPDATE __SPARTA_SECRET_REDACTED__.vacantes_personal
                SET estatus = 'Eliminada',
                    fecha_cierre = NOW()
                WHERE id = :id_vacante
                  AND UPPER(TRIM(estatus)) = 'ACTIVA'
                LIMIT 1
            ", [
                'id_vacante' => $idVacante,
            ]);
            $db->commit();

            return self::resultado(true, 'Vacante eliminada y subordinados reasignados correctamente.', [
                'id_vacante' => $idVacante,
                'jefe_destino' => $jefeDestino,
                'subordinados_movidos' => $subordinadosMovidos,
                'modo_movimiento' => $modoMovimiento,
            ]);
        } catch (\Exception $e) {
            if ($db) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al eliminar la vacante.', null, $e->getMessage());
        }
    }

    public static function actualizarJefePersonaOrganigrama($idPersona, $jefeRaw)
    {
        try {
            $db = new Database();
            self::asegurarAsignaJefeSoportaVacante($db);

            $idPersona = (int)$idPersona;
            $jefeRaw = trim((string)$jefeRaw);
            $idJefe = 0;
            $idVacanteJefe = 0;

            if (preg_match('/^vacante:(\d+)$/', $jefeRaw, $m)) {
                $idVacanteJefe = (int)$m[1];
            } else {
                $idJefe = (int)$jefeRaw;
            }

            if ($idPersona <= 0 || ($idJefe <= 0 && $idVacanteJefe <= 0)) {
                return self::resultado(false, 'Seleccione la persona y el jefe destino.');
            }

            if ($idJefe > 0 && $idPersona === $idJefe) {
                return self::resultado(false, 'Una persona no puede ser su propio jefe.');
            }

            $persona = $db->queryOne("
                SELECT id
                FROM __SPARTA_SECRET_REDACTED__.persona
                WHERE id = :id_persona
                  AND COALESCE(estatus, '') != 'Baja'
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if (!$persona) {
                return self::resultado(false, 'La persona seleccionada no esta activa.');
            }

            if ($idJefe > 0) {
                $jefe = $db->queryOne("
                    SELECT id
                    FROM __SPARTA_SECRET_REDACTED__.persona
                    WHERE id = :id_jefe
                      AND COALESCE(estatus, '') != 'Baja'
                    LIMIT 1
                ", ['id_jefe' => $idJefe]);

                if (!$jefe) {
                    return self::resultado(false, 'El jefe seleccionado no esta activo.');
                }

                $actual = $idJefe;
                $vistos = [];
                for ($i = 0; $i < 80 && $actual > 0; $i++) {
                    if ($actual === $idPersona) {
                        return self::resultado(false, 'No se puede asignar ese jefe porque generaria un ciclo en el organigrama.');
                    }
                    if (isset($vistos[$actual])) break;
                    $vistos[$actual] = true;

                    $rel = $db->queryOne("
                        SELECT id_jefe, id_vacante_jefe
                        FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                        WHERE id_persona = :id_persona
                        ORDER BY id DESC
                        LIMIT 1
                    ", ['id_persona' => $actual]);

                    if (!$rel) break;
                    if (!empty($rel['id_jefe'])) {
                        $actual = (int)$rel['id_jefe'];
                        continue;
                    }
                    if (!empty($rel['id_vacante_jefe'])) {
                        $vacJefe = $db->queryOne("
                            SELECT id_jefe
                            FROM __SPARTA_SECRET_REDACTED__.vacantes_personal
                            WHERE id = :id_vacante
                            LIMIT 1
                        ", ['id_vacante' => (int)$rel['id_vacante_jefe']]);
                        $actual = !empty($vacJefe['id_jefe']) ? (int)$vacJefe['id_jefe'] : 0;
                        continue;
                    }
                    break;
                }
            } else {
                $vacante = $db->queryOne("
                    SELECT id
                    FROM __SPARTA_SECRET_REDACTED__.vacantes_personal
                    WHERE id = :id_vacante
                      AND UPPER(TRIM(COALESCE(estatus, ''))) = 'ACTIVA'
                    LIMIT 1
                ", ['id_vacante' => $idVacanteJefe]);

                if (!$vacante) {
                    return self::resultado(false, 'La vacante seleccionada ya no esta activa.');
                }
            }

            $asignacion = $db->queryOne("
                SELECT id
                FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                WHERE id_persona = :id_persona
                ORDER BY id DESC
                LIMIT 1
            ", ['id_persona' => $idPersona]);

            if ($asignacion) {
                $db->CRUD("
                    UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe
                    SET id_jefe = :id_jefe,
                        id_vacante_jefe = :id_vacante_jefe
                    WHERE id = :id
                    LIMIT 1
                ", [
                    'id_jefe' => $idJefe > 0 ? $idJefe : null,
                    'id_vacante_jefe' => $idVacanteJefe > 0 ? $idVacanteJefe : null,
                    'id' => (int)$asignacion['id'],
                ]);
            } else {
                $db->CRUD("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_jefe (id_persona, id_jefe, id_vacante_jefe)
                    VALUES (:id_persona, :id_jefe, :id_vacante_jefe)
                ", [
                    'id_persona' => $idPersona,
                    'id_jefe' => $idJefe > 0 ? $idJefe : null,
                    'id_vacante_jefe' => $idVacanteJefe > 0 ? $idVacanteJefe : null,
                ]);
            }

            return self::resultado(true, 'Jefe actualizado correctamente.', [
                'id_persona' => $idPersona,
                'id_jefe' => $idJefe > 0 ? $idJefe : null,
                'id_vacante_jefe' => $idVacanteJefe > 0 ? $idVacanteJefe : null,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar el jefe.', null, $e->getMessage());
        }
    }

    public static function getMetaOrganigrama($idsPersonas, $idDepartamento = 0)
    {
        try {
            $db = new Database();
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarAsignaJefeSoportaVacante($db);

            $ids = [];
            foreach ((array)$idsPersonas as $id) {
                $id = (int)$id;
                if ($id > 0) $ids[$id] = $id;
            }

            $ausencias = [];
            if (!empty($ids)) {
                $params = [];
                $ph = [];
                $i = 0;
                foreach ($ids as $id) {
                    $key = 'id' . $i++;
                    $ph[] = ':' . $key;
                    $params[$key] = $id;
                }
                $rowsAus = $db->queryAll("
                    SELECT a.id_persona, ra.nombre AS razon_nombre, a.fecha_inicio, a.fecha_fin
                    FROM __SPARTA_SECRET_REDACTED__.ausencia a
                    INNER JOIN __SPARTA_SECRET_REDACTED__.razon_ausencia ra ON ra.id = a.id_razon
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS max_id
                        FROM __SPARTA_SECRET_REDACTED__.ausencia
                        WHERE activo = 1
                          AND DATE(fecha_inicio) <= CURDATE()
                          AND DATE(fecha_fin) >= CURDATE()
                          AND id_persona IN (" . implode(',', $ph) . ")
                        GROUP BY id_persona
                    ) latest ON latest.id_persona = a.id_persona AND latest.max_id = a.id
                ", $params);
                foreach ($rowsAus as $row) {
                    $ausencias[(int)$row['id_persona']] = $row;
                }
            }

            $paramsVac = [];
            $whereDepto = '';
            $idDepartamento = (int)$idDepartamento;
            if ($idDepartamento > 0) {
                $whereDepto = ' AND v.id_departamento = :id_departamento';
                $paramsVac['id_departamento'] = $idDepartamento;
            }

            $vacantes = $db->queryAll("
                SELECT
                    v.id,
                    v.id_jefe,
                    v.id_departamento,
                    v.id_puesto,
                    v.origen,
                    v.nombre_vacante,
                    COALESCE(NULLIF(TRIM(v.nombre_vacante), ''), pp.nombre) AS nombre_puesto,
                    pp.nombre AS nombre_puesto_base,
                    d.nombre AS nombre_departamento
                FROM __SPARTA_SECRET_REDACTED__.vacantes_personal v
                INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = v.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = v.id_departamento
                WHERE v.estatus = 'Activa'
                $whereDepto
                ORDER BY v.fecha_creacion ASC
            ", $paramsVac);

            $subordinadosVacante = $db->queryAll("
                SELECT
                    aj.id_vacante_jefe,
                    p.id,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre,
                    COALESCE(pp.nombre, 'Sin puesto') AS nombre_puesto
                FROM (
                    SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                    FROM __SPARTA_SECRET_REDACTED__.asigna_jefe a
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS mid
                        FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                        GROUP BY id_persona
                    ) m ON m.id_persona = a.id_persona AND m.mid = a.id
                ) aj
                INNER JOIN __SPARTA_SECRET_REDACTED__.vacantes_personal v
                        ON v.id = aj.id_vacante_jefe
                       AND v.estatus = 'Activa'
                INNER JOIN __SPARTA_SECRET_REDACTED__.persona p
                        ON p.id = aj.id_persona
                       AND p.estatus != 'Baja'
                LEFT JOIN (
                    SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto
                    FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                    WHERE COALESCE(ap.activo, 1) = 1
                    GROUP BY ap.id_persona
                ) ap_sel ON ap_sel.id_persona = p.id
                LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp
                       ON pp.id = ap_sel.id_puesto
                WHERE aj.id_vacante_jefe IS NOT NULL
                  $whereDepto
                ORDER BY p.nombres ASC, p.apellidop ASC
            ", $paramsVac);

            return self::resultado(true, 'Meta de organigrama encontrada.', [
                'ausencias' => $ausencias,
                'vacantes' => $vacantes,
                'subordinados_vacante' => $subordinadosVacante,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener meta de organigrama.', null, $e->getMessage());
        }
    }

    private static function asegurarTablaPermisosPuesto(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.permisos_puesto (
                id INT NOT NULL AUTO_INCREMENT,
                id_puesto INT NOT NULL,
                modulo_web_id INT NOT NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_permisos_puesto (id_puesto, modulo_web_id),
                KEY idx_permisos_puesto_puesto (id_puesto),
                KEY idx_permisos_puesto_modulo (modulo_web_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public static function getPermisosPuestoConfig($idPuesto = 0)
    {
        try {
            $db = new Database();
            self::asegurarTablaPermisosPuesto($db);
            self::asegurarModuloConveniosDescargarExcel($db);
            self::asegurarModuloAccesosCapitalHumanoDb($db);

            $puestos = $db->queryAll("
                SELECT
                    p.id,
                    p.nombre,
                    COALESCE(p.nivel, 0) AS nivel,
                    COALESCE(d.nombre, 'Sin departamento') AS departamento,
                    COALESCE(dorg.nombre, 'Sin area') AS area,
                    COALESCE(dir.nombre, 'Sin direccion') AS direccion
                FROM __SPARTA_SECRET_REDACTED__.puesto p
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d
                    ON d.id = p.departamento_id
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento_organizacional dorg
                    ON dorg.id = d.id_departamento_organizacional
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_direcciones ad
                    ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                   AND COALESCE(ad.activo, 1) = 1
                LEFT JOIN __SPARTA_SECRET_REDACTED__.direcciones_organizacion dir
                    ON dir.id = ad.id_direccion
                WHERE COALESCE(p.activo, 1) = 1
                  AND COALESCE(d.activo, 1) = 1
                ORDER BY direccion ASC, area ASC, departamento ASC, p.nombre ASC
            ");

            $modulos = $db->queryAll("
                SELECT
                    m.id,
                    CASE WHEN m.id = 27 THEN 'Panel Admin' ELSE m.nombre END AS modulo_nombre,
                    COALESCE(NULLIF(TRIM(m.pestana), ''), m.nombre) AS pestana,
                    COALESCE(NULLIF(TRIM(m.descripcion), ''), '') AS descripcion
                FROM __SPARTA_SECRET_REDACTED__.modulos_web m
                WHERE COALESCE(m.activo, 1) = 1
                  AND m.id NOT IN (25)
                  AND LOWER(TRIM(COALESCE(m.pestana, ''))) <> 'permisos especiales'
                ORDER BY modulo_nombre ASC, pestana ASC, m.id ASC
            ");

            $seleccionados = [];
            $idPuesto = (int) $idPuesto;
            if ($idPuesto > 0) {
                $seleccionados = self::modulosPlantillaPuesto($db, $idPuesto);
            }

            return self::resultado(true, 'Configuracion de permisos cargada.', [
                'puestos' => $puestos,
                'modulos' => $modulos,
                'seleccionados' => $seleccionados,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cargar permisos por puesto.', null, $e->getMessage());
        }
    }

    public static function getPermisosPuesto($idPuesto)
    {
        try {
            $db = new Database();
            self::asegurarTablaPermisosPuesto($db);

            $idPuesto = (int) $idPuesto;
            if ($idPuesto <= 0) {
                return self::resultado(false, 'ID de puesto invalido.');
            }

            return self::resultado(true, 'Permisos del puesto cargados.', [
                'id_puesto' => $idPuesto,
                'seleccionados' => self::modulosPlantillaPuesto($db, $idPuesto),
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cargar permisos del puesto.', null, $e->getMessage());
        }
    }

    public static function guardarPermisosPuesto($idPuesto, array $modulos)
    {
        $db = null;
        try {
            $db = new Database();
            self::asegurarTablaPermisosPuesto($db);
            self::asegurarModuloConveniosDescargarExcel($db);
            self::asegurarModuloAccesosCapitalHumanoDb($db);

            $idPuesto = (int) $idPuesto;
            if ($idPuesto <= 0) {
                return self::resultado(false, 'Selecciona un puesto valido.');
            }

            $puesto = $db->queryOne(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.puesto WHERE id = :id AND COALESCE(activo, 1) = 1 LIMIT 1",
                ['id' => $idPuesto]
            );
            if (!$puesto) {
                return self::resultado(false, 'Puesto no encontrado.');
            }

            $modulos = array_values(array_unique(array_filter(array_map('intval', $modulos), function ($id) {
                return $id > 0 && $id !== 25;
            })));

            $modulosValidos = [];
            if (!empty($modulos)) {
                $placeholders = [];
                $params = [];
                foreach ($modulos as $idx => $moduloId) {
                    $key = 'm' . $idx;
                    $placeholders[] = ':' . $key;
                    $params[$key] = $moduloId;
                }
                $rows = $db->queryAll(
                    "SELECT id
                       FROM __SPARTA_SECRET_REDACTED__.modulos_web
                      WHERE COALESCE(activo, 1) = 1
                        AND LOWER(TRIM(COALESCE(pestana, ''))) <> 'permisos especiales'
                        AND id IN (" . implode(',', $placeholders) . ")",
                    $params
                );
                $modulosValidos = array_map('intval', array_column($rows, 'id'));
            }

            $db->beginTransaction();
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.permisos_puesto SET activo = 0 WHERE id_puesto = :id_puesto",
                ['id_puesto' => $idPuesto]
            );

            foreach ($modulosValidos as $moduloId) {
                $db->CRUD(
                    "INSERT INTO __SPARTA_SECRET_REDACTED__.permisos_puesto (id_puesto, modulo_web_id, activo)
                     VALUES (:id_puesto, :modulo_web_id, 1)
                     ON DUPLICATE KEY UPDATE activo = 1, actualizado_en = NOW()",
                    ['id_puesto' => $idPuesto, 'modulo_web_id' => $moduloId]
                );
            }

            $db->commit();

            return self::resultado(true, 'Plantilla de permisos guardada.', [
                'id_puesto' => $idPuesto,
                'seleccionados' => $modulosValidos,
            ]);
        } catch (\Exception $e) {
            if ($db) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al guardar plantilla de permisos.', null, $e->getMessage());
        }
    }

    private static function modulosPlantillaPuesto(Database $db, int $idPuesto): array
    {
        $rows = $db->queryAll(
            "SELECT pp.modulo_web_id
               FROM __SPARTA_SECRET_REDACTED__.permisos_puesto pp
               INNER JOIN __SPARTA_SECRET_REDACTED__.modulos_web mw
                   ON mw.id = pp.modulo_web_id
                  AND COALESCE(mw.activo, 1) = 1
                  AND LOWER(TRIM(COALESCE(mw.pestana, ''))) <> 'permisos especiales'
              WHERE pp.id_puesto = :id_puesto
                AND pp.activo = 1
              ORDER BY pp.modulo_web_id ASC",
            ['id_puesto' => $idPuesto]
        );

        return array_map('intval', array_column($rows, 'modulo_web_id'));
    }

    private static function aplicarPermisosPuestoAPersonaConDb(Database $db, int $idPersona, int $idPuesto): int
    {
        if ($idPersona <= 0 || $idPuesto <= 0) {
            return 0;
        }

        self::asegurarTablaPermisosPuesto($db);
        $modulos = self::modulosPlantillaPuesto($db, $idPuesto);
        if (empty($modulos)) {
            return 0;
        }

        $insertados = 0;
        foreach ($modulos as $moduloId) {
            $existe = $db->queryOne(
                "SELECT id
                   FROM __SPARTA_SECRET_REDACTED__.asigna_modulo_web
                  WHERE usuario_id = :usuario_id
                    AND modulo_web_id = :modulo_web_id
                  LIMIT 1",
                ['usuario_id' => $idPersona, 'modulo_web_id' => $moduloId]
            );

            if ($existe) {
                continue;
            }

            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_modulo_web (usuario_id, modulo_web_id)
                 VALUES (:usuario_id, :modulo_web_id)",
                ['usuario_id' => $idPersona, 'modulo_web_id' => $moduloId]
            );
            $insertados++;
        }

        if ($insertados > 0) {
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.persona
                    SET session_version = COALESCE(session_version, 1) + 1
                  WHERE id = :id",
                ['id' => $idPersona]
            );
        }

        return $insertados;
    }

    public static function aplicarPermisosPuestoAPersona(int $idPersona, int $idPuesto)
    {
        try {
            $db = new Database();
            $insertados = self::aplicarPermisosPuestoAPersonaConDb($db, $idPersona, $idPuesto);
            return self::resultado(true, 'Permisos automaticos aplicados.', ['insertados' => $insertados]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al aplicar permisos automaticos.', null, $e->getMessage());
        }
    }

    /**
     * Función optimizada para reporte de Capital Humano
     * Los filtros se aplican directamente en SQL (más rápido)
     */
    public static function getGestoresParaReporte($filtros = [])
    {
        $departamento = $filtros['departamento'] ?? null;
        $puesto = $filtros['puesto'] ?? null;
        $estatus = $filtros['estatus'] ?? null;
        $multipuesto = $filtros['multipuesto'] ?? null;

        $params = [];
        $whereConditions = [
            "p.estatus != 'Baja'",
            UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p'),
        ];

        // Filtro por departamento
        if (!empty($departamento)) {
            $whereConditions[] = "d.nombre = :departamento";
            $params['departamento'] = $departamento;
        }

        // Filtro por puesto
        if (!empty($puesto)) {
            $whereConditions[] = "pp.nombre = :puesto";
            $params['puesto'] = $puesto;
        }

        // Filtro por estatus
        if (!empty($estatus)) {
            $whereConditions[] = "p.estatus = :estatus";
            $params['estatus'] = $estatus;
        }

        // Filtro por multipuesto (subquery optimizada)
        $multipuestoJoin = "";
        if ($multipuesto === 'multiples') {
            $whereConditions[] = "(SELECT COUNT(*) FROM asigna_puesto ap2 WHERE ap2.id_persona = p.id AND COALESCE(ap2.activo, 1) = 1) > 1";
        } elseif ($multipuesto === 'unico') {
            $whereConditions[] = "(SELECT COUNT(*) FROM asigna_puesto ap2 WHERE ap2.id_persona = p.id AND COALESCE(ap2.activo, 1) = 1) = 1";
        }

        $whereSQL = implode(" AND ", $whereConditions);

        $query = <<<SQL
        SELECT
            p.id,
            p.numero_empleado,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,
            CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
            COALESCE(p.telefono_uno, '') AS telefono,
            COALESCE(p.telefono_dos, '') AS telefono_dos,
            COALESCE(p.correo, '') AS correo,
            COALESCE(p.domicilio_calle_texto, '') AS domicilio_calle_texto,
            COALESCE(p.codigo_postal, '') AS codigo_postal,

            pp.id AS id_puesto,
            COALESCE(pp.nombre, 'Sin puesto') AS nombre_puesto,

            d.id AS id_departamento,
            COALESCE(d.nombre, 'Sin departamento') AS nombre_departamento,

            COALESCE(
                CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom),
                'Sin jefe'
            ) AS nombre_jefe,

            p.estatus,
            COALESCE(p.user_name, 'Sin usuario') AS usuario,

            aus_activa.razon_nombre  AS ausencia_razon,
            aus_activa.fecha_inicio  AS ausencia_fecha_inicio,
            aus_activa.fecha_fin     AS ausencia_fecha_fin

        FROM persona p

        LEFT JOIN asigna_puesto ap ON p.id = ap.id_persona AND COALESCE(ap.activo, 1) = 1
        LEFT JOIN puesto pp ON pp.id = ap.id_puesto
        LEFT JOIN departamento d ON d.id = pp.departamento_id

        LEFT JOIN (
            SELECT a.id_persona, a.id_jefe
            FROM asigna_jefe a
            INNER JOIN (
                SELECT id_persona, MAX(id) AS mid
                FROM asigna_jefe
                GROUP BY id_persona
            ) m ON a.id_persona = m.id_persona AND a.id = m.mid
        ) aj ON aj.id_persona = p.id

        LEFT JOIN persona pj ON pj.id = aj.id_jefe

        LEFT JOIN (
            SELECT a.id_persona, ra.nombre AS razon_nombre, a.fecha_inicio, a.fecha_fin
            FROM ausencia a
            INNER JOIN razon_ausencia ra ON ra.id = a.id_razon
            INNER JOIN (
                SELECT id_persona, MAX(id) AS max_id
                FROM ausencia
                WHERE activo = 1
                  AND DATE(fecha_inicio) <= CURDATE()
                  AND DATE(fecha_fin)    >= CURDATE()
                GROUP BY id_persona
            ) latest ON latest.id_persona = a.id_persona AND latest.max_id = a.id
        ) aus_activa ON aus_activa.id_persona = p.id

        WHERE {$whereSQL}

        ORDER BY d.nombre ASC, pp.nombre ASC, p.nombres ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Gestores encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener gestores.', null, $e->getMessage());
        }
    }

    public static function getPersonaDetalle($idPersona)
    {
        try {
            $db = new Database();

            $query = <<<SQL
            SELECT
                p.*,
                p.telefono_uno AS telefono,
                ap.id_puesto,
                dd.nombre as departamento,
                dd.id as id_departamento,
                CASE
                    WHEN aj.id_vacante_jefe IS NOT NULL THEN CONCAT('vacante:', aj.id_vacante_jefe)
                    ELSE aj.id_jefe
                END AS id_jefe,
                aj.id_vacante_jefe,
                p.password,
                al.id_legion
            FROM persona p
            LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
            LEFT JOIN puesto pu ON pu.id = ap.id_puesto
            LEFT JOIN departamento dd ON dd.id = pu.departamento_id
            LEFT JOIN asigna_jefe aj ON aj.id_persona = p.id
            LEFT JOIN asigna_legion al ON al.id_persona = p.id AND al.activo = 1
            WHERE p.id = $idPersona
              AND p.estatus != 'Baja'
            LIMIT 1
        SQL;

            $persona = $db->queryOne($query);

            return self::resultado(true, 'Persona encontrada.', $persona);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function actualizarModuloPerfil($idPersona, $moduloId, $asignado)
    {
        try {
            $db = new Database();
            if ((int) $moduloId === self::MODULO_CONVENIOS_DESCARGAR_EXCEL) {
                self::asegurarModuloConveniosDescargarExcel($db);
            }
            if ((int) $moduloId === self::MODULO_ACCESOS_CAPITAL_HUMANO) {
                self::asegurarModuloAccesosCapitalHumanoDb($db);
            }
            if ((int) $moduloId === self::MODULO_TRACKING_CANCELAR_RUTA) {
                self::asegurarModuloTrackingCancelarRuta($db);
            }

            if ($asignado === 1) {

                // 1️⃣ Validar si ya existe
                $queryExiste = <<<SQL
                SELECT id
                FROM asigna_modulo_web
                WHERE usuario_id = $idPersona
                  AND modulo_web_id = $moduloId
                LIMIT 1
            SQL;

                $existe = $db->queryOne($queryExiste);

                if (!$existe) {
                    $moduloId = (int) $moduloId;
                    $db->CRUD(
                        "INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id) VALUES (:uid, :mid)",
                        ['uid' => $idPersona, 'mid' => $moduloId]
                    );
                    if ($moduloId === 27) {
                        $db->CRUD('DELETE FROM asigna_modulo_web WHERE usuario_id = :uid AND modulo_web_id IN (25)', ['uid' => $idPersona]);
                    }
                }
                $db->CRUD(
                    "UPDATE persona SET session_version = COALESCE(session_version, 1) + 1 WHERE id = :id",
                    ['id' => (int) $idPersona]
                );

                return self::resultado(
                    true,
                    'Módulo asignado correctamente'
                );

            } else {

                // 3️⃣ Eliminar asignación (Panel Admin = 27: quitar también 25 ligado legado)
                $moduloId = (int) $moduloId;
                $db->CRUD(
                    "DELETE FROM asigna_modulo_web WHERE usuario_id = :uid AND modulo_web_id = :mid",
                    ['uid' => $idPersona, 'mid' => $moduloId]
                );
                if ($moduloId === 27) {
                    $db->CRUD(
                        'DELETE FROM asigna_modulo_web WHERE usuario_id = :uid AND modulo_web_id IN (25)',
                        ['uid' => $idPersona]
                    );
                }
                $db->CRUD(
                    "UPDATE persona SET session_version = COALESCE(session_version, 1) + 1 WHERE id = :id",
                    ['id' => (int) $idPersona]
                );

                return self::resultado(
                    true,
                    'Módulo eliminado correctamente'
                );
            }
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar módulo del perfil.', null, $e->getMessage());
        }
    }

    /**
     * Obtener documentos de una baja usando registro_baja
     */
    public static function getDocumentosBaja($registro_baja)
    {
        try {
            $db = new Database();

            // Primero obtener el id_persona desde baja_persona
            $baja = $db->queryOne("
                SELECT id_persona
                FROM __SPARTA_SECRET_REDACTED__.baja_persona
                WHERE id = :registro_baja
            ", ['registro_baja' => $registro_baja]);

            if (!$baja || !isset($baja['id_persona'])) {
                return self::resultado(false, 'Baja no encontrada.', []);
            }

            $id_persona = $baja['id_persona'];
            $id_documento = 15; // Documento Baja

            // Obtener documentos
            $documentos = $db->queryAll("
                SELECT
                    cdp.id,
                    cdp.archivo,
                    DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona cdp
                WHERE cdp.id_persona = :id_persona
                AND cdp.id_documento = :id_documento
                ORDER BY cdp.fecha_carga DESC
            ", [
                'id_persona' => $id_persona,
                'id_documento' => $id_documento
            ]);

            return self::resultado(true, 'Documentos encontrados.', $documentos ?? []);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener documentos.', [], $e->getMessage());
        }
    }

    /**
     * Guardar documentos de una baja
     */
    public static function guardarDocumentosBaja($registro_baja, $archivos)
    {
        try {
            $db = new Database();

            // Obtener el id_persona desde baja_persona
            $baja = $db->queryOne("
                SELECT id_persona
                FROM __SPARTA_SECRET_REDACTED__.baja_persona
                WHERE id = :registro_baja
            ", ['registro_baja' => $registro_baja]);

            if (!$baja || !isset($baja['id_persona'])) {
                return self::resultado(false, 'Baja no encontrada.');
            }

            $id_persona = $baja['id_persona'];
            $id_documento = 15; // Documento Baja

            $archivosGuardados = [];

            foreach ($archivos as $nombreArchivo) {
                $archivoEsc = addslashes($nombreArchivo);

                $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.carga_documento_persona
                    (id_persona, id_documento, archivo, fecha_carga)
                    VALUES
                    (:id_persona, :id_documento, :archivo, NOW())
                ", [
                    'id_persona' => $id_persona,
                    'id_documento' => $id_documento,
                    'archivo' => $archivoEsc
                ]);

                $archivosGuardados[] = $nombreArchivo;
            }

            return self::resultado(true, 'Documentos guardados correctamente.', $archivosGuardados);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar documentos.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar documento de una baja
     */
    public static function eliminarDocumentoBaja($id_documento_carga)
    {
        try {
            $db = new Database();

            // Primero obtener el nombre del archivo para eliminarlo físicamente
            $documento = $db->queryOne("
                SELECT archivo
                FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona
                WHERE id = :id
            ", ['id' => $id_documento_carga]);

            if (!$documento) {
                return self::resultado(false, 'Documento no encontrado.');
            }

            $nombreArchivo = $documento['archivo'];

            // Eliminar de la base de datos
            $db->queryOne("
                DELETE FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona
                WHERE id = :id
            ", ['id' => $id_documento_carga]);

            // Eliminar archivo físico
            $rutaArchivo = sparta_uploads_join('bajas', $nombreArchivo);
            if (file_exists($rutaArchivo)) {
                @unlink($rutaArchivo);
            }

            return self::resultado(true, 'Documento eliminado correctamente.');

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar documento.', null, $e->getMessage());
        }
    }

    /**
     * Obtener tipos de documentos disponibles desde la base de datos
     */
    public static function getTiposDocumentos()
    {
        try {
            $db = new Database();
            self::asegurarDocumentoOtros($db);

            // Obtener documentos activos desde la base de datos
            $documentos = $db->queryAll("
                SELECT id, nombre, clave
                FROM __SPARTA_SECRET_REDACTED__.documento
                WHERE activo = 1
                  AND id NOT IN (" . implode(',', self::DOCUMENTOS_EXCLUIDOS_RRHH) . ")
                ORDER BY nombre
            ");

            return self::resultado(true, 'Tipos de documentos encontrados.', $documentos ?? []);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener tipos de documentos.', [], $e->getMessage());
        }
    }

    private static function asegurarDocumentoOtros(Database $db): void
    {
        $otros = $db->queryOne("
            SELECT id, activo
            FROM __SPARTA_SECRET_REDACTED__.documento
            WHERE clave = 'OTROS'
               OR LOWER(TRIM(nombre)) = 'otros'
            LIMIT 1
        ");

        if ($otros) {
            if ((int) ($otros['activo'] ?? 0) !== 1) {
                $db->CRUD("
                    UPDATE __SPARTA_SECRET_REDACTED__.documento
                    SET activo = 1
                    WHERE id = :id
                ", ['id' => (int) $otros['id']]);
            }
            return;
        }

        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.documento (clave, nombre, obligatorio, activo)
            VALUES ('OTROS', 'Otros', 0, 1)
        ");
    }

    public static function getPersonasParaImportacionDocumentos()
    {
        try {
            $db = new Database();
            $personas = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    p.apellidom,
                    p.curp,
                    COALESCE(p.estatus, '') AS estatus,
                    DATE_FORMAT(bp.fecha_baja, '%Y-%m-%d') AS fecha_baja
                FROM __SPARTA_SECRET_REDACTED__.persona p
                LEFT JOIN (
                    SELECT id_persona, MAX(id) AS id_ultima_baja
                    FROM __SPARTA_SECRET_REDACTED__.baja_persona
                    GROUP BY id_persona
                ) ub ON ub.id_persona = p.id
                LEFT JOIN __SPARTA_SECRET_REDACTED__.baja_persona bp ON bp.id = ub.id_ultima_baja
                ORDER BY
                    CASE WHEN p.estatus = 'Baja' THEN 1 ELSE 0 END,
                    p.nombres ASC,
                    p.apellidop ASC,
                    p.apellidom ASC
            ");

            return self::resultado(true, 'Personas encontradas.', $personas ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener personas para importación.', [], $e->getMessage());
        }
    }

    public static function getCatalogoDocumentosImportacion()
    {
        try {
            $db = new Database();
            self::asegurarDocumentoOtros($db);
            $documentos = $db->queryAll("
                SELECT id, clave, nombre
                FROM __SPARTA_SECRET_REDACTED__.documento
                WHERE activo = 1
                  AND id NOT IN (" . implode(',', self::DOCUMENTOS_EXCLUIDOS_RRHH) . ")
                ORDER BY id ASC
            ");

            return self::resultado(true, 'Catálogo de documentos encontrado.', $documentos ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener catálogo de documentos.', [], $e->getMessage());
        }
    }

    public static function getDocumentosPersonaIndex(array $idsPersonas)
    {
        try {
            $idsPersonas = array_values(array_unique(array_filter(array_map('intval', $idsPersonas))));
            if (empty($idsPersonas)) {
                return self::resultado(true, 'Sin personas para consultar.', []);
            }

            $params = [];
            $placeholders = [];
            foreach ($idsPersonas as $i => $idPersona) {
                $key = 'id_' . $i;
                $params[$key] = $idPersona;
                $placeholders[] = ':' . $key;
            }

            $db = new Database();
            $rows = $db->queryAll("
                SELECT id, id_persona, id_documento, archivo, fecha_carga
                FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona
                WHERE id_persona IN (" . implode(',', $placeholders) . ")
            ", $params);

            $index = [];
            foreach ($rows as $row) {
                $idPersona = (int) ($row['id_persona'] ?? 0);
                $idDocumento = (int) ($row['id_documento'] ?? 0);
                if ($idPersona <= 0 || $idDocumento <= 0) {
                    continue;
                }
                if (!isset($index[$idPersona])) {
                    $index[$idPersona] = [];
                }
                if (!isset($index[$idPersona][$idDocumento])) {
                    $index[$idPersona][$idDocumento] = [];
                }
                $index[$idPersona][$idDocumento][] = $row;
            }

            return self::resultado(true, 'Documentos existentes encontrados.', $index);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar documentos existentes.', [], $e->getMessage());
        }
    }

    /**
     * Obtener ID de documento por nombre (usando la BD)
     */
    public static function getIdDocumentoPorNombre($nombreDocumento)
    {
        try {
            $db = new Database();

            // Limpiar el nombre del documento (trim para espacios y caracteres especiales)
            $nombreDocumento = trim($nombreDocumento);
            $nombreDocumento = preg_replace('/\s+/', ' ', $nombreDocumento); // Normalizar espacios múltiples

            // Primero intentar búsqueda exacta (más rápida y precisa)
            $documento = $db->queryOne("
                SELECT id
                FROM __SPARTA_SECRET_REDACTED__.documento
                WHERE nombre = :nombre
                AND activo = 1
                LIMIT 1
            ", ['nombre' => $nombreDocumento]);

            if ($documento && isset($documento['id'])) {
                return (int)$documento['id'];
            }

            // Si no se encuentra, intentar búsqueda case-insensitive con trim
            $documento = $db->queryOne("
                SELECT id
                FROM __SPARTA_SECRET_REDACTED__.documento
                WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:nombre))
                AND activo = 1
                LIMIT 1
            ", ['nombre' => $nombreDocumento]);

            if ($documento && isset($documento['id'])) {
                return (int)$documento['id'];
            }

            return null;

        } catch (\Exception $e) {
            error_log("Error en getIdDocumentoPorNombre: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Obtener documentos de una persona por tipo
     */
    public static function getDocumentosPersona($id_persona, $id_documento = null)
    {
        try {
            $db = new Database();

            $query = "
                SELECT
                    cdp.id,
                    cdp.archivo,
                    cdp.id_documento,
                    DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona cdp
                WHERE cdp.id_persona = :id_persona
            ";

            $params = ['id_persona' => $id_persona];

            if ($id_documento) {
                $query .= " AND cdp.id_documento = :id_documento";
                $params['id_documento'] = $id_documento;
            }

            $query .= " ORDER BY cdp.fecha_carga DESC";

            $documentos = $db->queryAll($query, $params);

            return self::resultado(true, 'Documentos encontrados.', $documentos ?? []);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener documentos.', [], $e->getMessage());
        }
    }

    private static function coberturaDocumentoRrhh(int $idDocumento, array $mapaCargados): array
    {
        $info = $mapaCargados[$idDocumento] ?? null;
        if ($info !== null && (int) ($info['total_archivos'] ?? 0) > 0) {
            return ['cargado' => true, 'info' => $info, 'cubierto_por' => null];
        }

        if ($idDocumento === self::DOCUMENTO_RFC_RRHH) {
            $infoConstancia = $mapaCargados[self::DOCUMENTO_CONSTANCIA_FISCAL_RRHH] ?? null;
            if ($infoConstancia !== null && (int) ($infoConstancia['total_archivos'] ?? 0) > 0) {
                return [
                    'cargado' => true,
                    'info' => $infoConstancia,
                    'cubierto_por' => 'Constancia de situacion fiscal',
                ];
            }
        }

        return ['cargado' => false, 'info' => null, 'cubierto_por' => null];
    }

    public static function getResumenDocumentosColaborador($id_persona)
    {
        try {
            $id_persona = (int) $id_persona;
            if ($id_persona <= 0) {
                return self::resultado(false, 'No se pudo identificar al colaborador.', []);
            }

            $db = new Database();
            $persona = $db->queryOne("
                SELECT
                    p.id,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.correo,
                    COALESCE(p.estatus, '') AS estatus
                FROM __SPARTA_SECRET_REDACTED__.persona p
                WHERE p.id = :id_persona
                LIMIT 1
            ", ['id_persona' => $id_persona]);

            if (!$persona) {
                return self::resultado(false, 'Colaborador no encontrado.', []);
            }

            $tipos = $db->queryAll("
                SELECT id, nombre, clave, obligatorio
                FROM __SPARTA_SECRET_REDACTED__.documento
                WHERE activo = 1
                  AND id NOT IN (15, 16, " . implode(',', self::DOCUMENTOS_EXCLUIDOS_RRHH) . ")
                  AND clave <> 'OTROS'
                ORDER BY obligatorio DESC, nombre ASC
            ");

            $idsConsulta = array_values(array_unique(array_merge(
                array_map(static fn($doc) => (int) ($doc['id'] ?? 0), $tipos),
                array_keys(self::DOCUMENTOS_ALIAS_RRHH)
            )));
            $cargados = $db->queryAll("
                SELECT
                    cdp.id_documento,
                    COUNT(*) AS total_archivos,
                    MAX(cdp.fecha_carga) AS ultima_fecha,
                    GROUP_CONCAT(cdp.archivo ORDER BY cdp.fecha_carga DESC SEPARATOR '||') AS archivos
                FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona cdp
                WHERE cdp.id_persona = :id_persona
                  AND cdp.id_documento IN (" . implode(',', $idsConsulta) . ")
                GROUP BY cdp.id_documento
            ", ['id_persona' => $id_persona]);

            $mapaCargados = [];
            foreach ($cargados as $row) {
                $idDocumentoOriginal = (int) $row['id_documento'];
                $idDocumento = self::DOCUMENTOS_ALIAS_RRHH[$idDocumentoOriginal] ?? $idDocumentoOriginal;
                if (!isset($mapaCargados[$idDocumento])) {
                    $row['id_documento'] = $idDocumento;
                    $mapaCargados[$idDocumento] = $row;
                    continue;
                }

                $mapaCargados[$idDocumento]['total_archivos'] = (int) ($mapaCargados[$idDocumento]['total_archivos'] ?? 0) + (int) ($row['total_archivos'] ?? 0);
                if (strtotime((string) ($row['ultima_fecha'] ?? '')) > strtotime((string) ($mapaCargados[$idDocumento]['ultima_fecha'] ?? ''))) {
                    $mapaCargados[$idDocumento]['ultima_fecha'] = $row['ultima_fecha'];
                }
                $archivosActuales = (string) ($mapaCargados[$idDocumento]['archivos'] ?? '');
                $archivosNuevos = (string) ($row['archivos'] ?? '');
                $mapaCargados[$idDocumento]['archivos'] = trim($archivosActuales . '||' . $archivosNuevos, '|');
            }

            $documentos = [];
            $totalCargados = 0;
            foreach ($tipos as $tipo) {
                $idDocumento = (int) $tipo['id'];
                $cobertura = self::coberturaDocumentoRrhh($idDocumento, $mapaCargados);
                $info = $cobertura['info'];
                $estaCargado = (bool) ($cobertura['cargado'] ?? false);
                $cubiertoPor = (string) ($cobertura['cubierto_por'] ?? '');
                if ($estaCargado) {
                    $totalCargados++;
                }

                $documentos[] = [
                    'id_documento' => $idDocumento,
                    'nombre' => $tipo['nombre'] ?? '',
                    'clave' => $tipo['clave'] ?? '',
                    'obligatorio' => (int) ($tipo['obligatorio'] ?? 0) === 1,
                    'estatus' => $estaCargado ? ($cubiertoPor !== '' ? 'Cubierto' : 'Cargado') : 'Faltante',
                    'cargado' => $estaCargado,
                    'cubierto_por' => $cubiertoPor,
                    'total_archivos' => $estaCargado && $cubiertoPor === '' ? (int) $info['total_archivos'] : 0,
                    'ultima_fecha' => $estaCargado && $cubiertoPor === '' && !empty($info['ultima_fecha'])
                        ? date('Y-m-d H:i', strtotime((string) $info['ultima_fecha']))
                        : null,
                    'archivos' => $estaCargado && $cubiertoPor === '' && !empty($info['archivos'])
                        ? explode('||', (string) $info['archivos'])
                        : [],
                ];
            }

            $totalRequeridos = count($documentos);
            $totalFaltantes = max(0, $totalRequeridos - $totalCargados);
            $porcentaje = $totalRequeridos > 0 ? round(($totalCargados / $totalRequeridos) * 100, 1) : 0;

            return self::resultado(true, 'Resumen documental encontrado.', [
                'persona' => $persona,
                'metricas' => [
                    'total_requeridos' => $totalRequeridos,
                    'total_cargados' => $totalCargados,
                    'total_faltantes' => $totalFaltantes,
                    'porcentaje' => $porcentaje,
                ],
                'documentos' => $documentos,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener resumen documental.', [], $e->getMessage());
        }
    }

    public static function getResumenDocumentosRrhhGlobal()
    {
        try {
            $db = new Database();

            $tipos = $db->queryAll("
                SELECT id, nombre, clave, obligatorio
                FROM __SPARTA_SECRET_REDACTED__.documento
                WHERE activo = 1
                  AND id NOT IN (15, 16, " . implode(',', self::DOCUMENTOS_EXCLUIDOS_RRHH) . ")
                  AND clave <> 'OTROS'
                ORDER BY obligatorio DESC, nombre ASC
            ");

            $personas = $db->queryAll("
                SELECT
                    p.id,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.correo,
                    COALESCE(p.estatus, '') AS estatus,
                    GROUP_CONCAT(DISTINCT d.nombre ORDER BY d.nombre SEPARATOR ', ') AS departamentos,
                    GROUP_CONCAT(DISTINCT pp.nombre ORDER BY pp.nombre SEPARATOR ', ') AS puestos
                FROM __SPARTA_SECRET_REDACTED__.persona p
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                    ON ap.id_persona = p.id
                   AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp
                    ON pp.id = ap.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d
                    ON d.id = pp.departamento_id
                WHERE p.estatus != 'Baja'
                GROUP BY p.id, p.numero_empleado, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom, p.correo, p.estatus
                ORDER BY nombre_completo ASC
            ");

            $idsDocumento = array_map(static fn($doc) => (int) ($doc['id'] ?? 0), $tipos);
            $idsDocumento = array_values(array_filter($idsDocumento));
            $idsConsulta = array_values(array_unique(array_merge($idsDocumento, array_keys(self::DOCUMENTOS_ALIAS_RRHH))));
            $cargas = [];
            if (!empty($idsConsulta)) {
                $cargas = $db->queryAll("
                    SELECT
                        cdp.id_persona,
                        cdp.id_documento,
                        COUNT(*) AS total_archivos,
                        MAX(cdp.fecha_carga) AS ultima_fecha
                    FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona cdp
                    WHERE cdp.id_documento IN (" . implode(',', $idsConsulta) . ")
                    GROUP BY cdp.id_persona, cdp.id_documento
                ");
            }

            $mapaCargas = [];
            foreach ($cargas as $row) {
                $idPersona = (int) ($row['id_persona'] ?? 0);
                $idDocumentoOriginal = (int) ($row['id_documento'] ?? 0);
                $idDocumento = self::DOCUMENTOS_ALIAS_RRHH[$idDocumentoOriginal] ?? $idDocumentoOriginal;
                if ($idPersona <= 0 || $idDocumento <= 0) {
                    continue;
                }
                if (!isset($mapaCargas[$idPersona])) {
                    $mapaCargas[$idPersona] = [];
                }
                if (!isset($mapaCargas[$idPersona][$idDocumento])) {
                    $row['id_documento'] = $idDocumento;
                    $mapaCargas[$idPersona][$idDocumento] = $row;
                    continue;
                }

                $mapaCargas[$idPersona][$idDocumento]['total_archivos'] = (int) ($mapaCargas[$idPersona][$idDocumento]['total_archivos'] ?? 0) + (int) ($row['total_archivos'] ?? 0);
                if (strtotime((string) ($row['ultima_fecha'] ?? '')) > strtotime((string) ($mapaCargas[$idPersona][$idDocumento]['ultima_fecha'] ?? ''))) {
                    $mapaCargas[$idPersona][$idDocumento]['ultima_fecha'] = $row['ultima_fecha'];
                }
            }

            $totalTipos = count($tipos);
            $colaboradores = [];
            $totalCargadosGlobal = 0;
            $colaboradoresCompletos = 0;
            $colaboradoresConFaltantes = 0;
            $colaboradoresSinDocumentos = 0;
            $colaboradoresParciales = 0;

            foreach ($personas as $persona) {
                $idPersona = (int) ($persona['id'] ?? 0);
                $cargadosLocal = 0;
                $documentos = [];
                $faltantes = [];

                foreach ($tipos as $tipo) {
                    $idDocumento = (int) ($tipo['id'] ?? 0);
                    $cobertura = self::coberturaDocumentoRrhh($idDocumento, $mapaCargas[$idPersona] ?? []);
                    $info = $cobertura['info'];
                    $estaCargado = (bool) ($cobertura['cargado'] ?? false);
                    $cubiertoPor = (string) ($cobertura['cubierto_por'] ?? '');
                    if ($estaCargado) {
                        $cargadosLocal++;
                    } else {
                        $faltantes[] = $tipo['nombre'] ?? '';
                    }

                    $documentos[] = [
                        'id_documento' => $idDocumento,
                        'nombre' => $tipo['nombre'] ?? '',
                        'clave' => $tipo['clave'] ?? '',
                        'obligatorio' => (int) ($tipo['obligatorio'] ?? 0) === 1,
                        'estatus' => $estaCargado ? ($cubiertoPor !== '' ? 'Cubierto' : 'Cargado') : 'Faltante',
                        'cargado' => $estaCargado,
                        'cubierto_por' => $cubiertoPor,
                        'total_archivos' => $estaCargado && $cubiertoPor === '' ? (int) ($info['total_archivos'] ?? 0) : 0,
                        'ultima_fecha' => $estaCargado && $cubiertoPor === '' && !empty($info['ultima_fecha'])
                            ? date('Y-m-d H:i', strtotime((string) $info['ultima_fecha']))
                            : null,
                    ];
                }

                $totalCargadosGlobal += $cargadosLocal;
                $faltantesLocal = max(0, $totalTipos - $cargadosLocal);
                $porcentajeLocal = $totalTipos > 0 ? round(($cargadosLocal / $totalTipos) * 100, 1) : 0;
                if ($faltantesLocal > 0) {
                    $colaboradoresConFaltantes++;
                    if ($cargadosLocal === 0) {
                        $colaboradoresSinDocumentos++;
                    } else {
                        $colaboradoresParciales++;
                    }
                } else {
                    $colaboradoresCompletos++;
                }

                $colaboradores[] = [
                    'id_persona' => $idPersona,
                    'numero_empleado' => $persona['numero_empleado'] ?? '',
                    'nombre_completo' => $persona['nombre_completo'] ?? '',
                    'correo' => $persona['correo'] ?? '',
                    'departamentos' => $persona['departamentos'] ?: 'Sin departamento',
                    'puestos' => $persona['puestos'] ?: 'Sin puesto',
                    'estatus' => $persona['estatus'] ?? '',
                    'total_requeridos' => $totalTipos,
                    'total_cargados' => $cargadosLocal,
                    'total_faltantes' => $faltantesLocal,
                    'porcentaje_local' => $porcentajeLocal,
                    'faltantes_resumen' => array_slice(array_values(array_filter($faltantes)), 0, 4),
                    'documentos' => $documentos,
                ];
            }

            usort($colaboradores, static function ($a, $b) {
                $pa = (float) ($a['porcentaje_local'] ?? 0);
                $pb = (float) ($b['porcentaje_local'] ?? 0);
                if ($pa !== $pb) {
                    return $pa <=> $pb;
                }
                return strcasecmp((string) ($a['nombre_completo'] ?? ''), (string) ($b['nombre_completo'] ?? ''));
            });

            $totalColaboradores = count($colaboradores);
            $totalRequeridosGlobal = $totalColaboradores * $totalTipos;
            $totalFaltantesGlobal = max(0, $totalRequeridosGlobal - $totalCargadosGlobal);
            $porcentajeGlobal = $totalRequeridosGlobal > 0 ? round(($totalCargadosGlobal / $totalRequeridosGlobal) * 100, 1) : 0;

            return self::resultado(true, 'Resumen documental global encontrado.', [
                'metricas' => [
                    'total_colaboradores' => $totalColaboradores,
                    'total_documentos_catalogo' => $totalTipos,
                    'total_requeridos_global' => $totalRequeridosGlobal,
                    'total_cargados_global' => $totalCargadosGlobal,
                    'total_faltantes_global' => $totalFaltantesGlobal,
                    'porcentaje_global' => $porcentajeGlobal,
                    'colaboradores_completos' => $colaboradoresCompletos,
                    'colaboradores_con_faltantes' => $colaboradoresConFaltantes,
                    'colaboradores_sin_documentos' => $colaboradoresSinDocumentos,
                    'colaboradores_parciales' => $colaboradoresParciales,
                ],
                'colaboradores' => $colaboradores,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener resumen documental global.', [], $e->getMessage());
        }
    }

    public static function getDocumentosPersonaPorIds(array $ids)
    {
        try {
            $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
            if (empty($ids)) {
                return self::resultado(false, 'No se seleccionaron documentos.', []);
            }

            $params = [];
            $placeholders = [];
            foreach ($ids as $i => $id) {
                $key = 'id_' . $i;
                $params[$key] = $id;
                $placeholders[] = ':' . $key;
            }

            $db = new Database();
            $documentos = $db->queryAll("
                SELECT
                    cdp.id,
                    cdp.id_persona,
                    cdp.archivo,
                    cdp.id_documento,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                    p.numero_empleado,
                    DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona cdp
                LEFT JOIN __SPARTA_SECRET_REDACTED__.persona p ON p.id = cdp.id_persona
                WHERE cdp.id IN (" . implode(',', $placeholders) . ")
                ORDER BY cdp.fecha_carga DESC, cdp.id DESC
            ", $params);

            return self::resultado(true, 'Documentos encontrados.', $documentos ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener documentos.', [], $e->getMessage());
        }
    }

    /**
     * Guardar documentos de una persona
     */
    public static function guardarDocumentosPersona($id_persona, $id_documento, $archivos)
    {
        try {
            $db = new Database();

            $archivosGuardados = [];

            foreach ($archivos as $nombreArchivo) {
                $archivoEsc = addslashes($nombreArchivo);

                $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.carga_documento_persona
                    (id_persona, id_documento, archivo, fecha_carga)
                    VALUES
                    (:id_persona, :id_documento, :archivo, NOW())
                ", [
                    'id_persona' => $id_persona,
                    'id_documento' => $id_documento,
                    'archivo' => $archivoEsc
                ]);

                $archivosGuardados[] = $nombreArchivo;
            }

            return self::resultado(true, 'Documentos guardados correctamente.', $archivosGuardados);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar documentos.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar documento de una persona
     */
    public static function eliminarDocumentoPersona($id_documento_carga)
    {
        try {
            $db = new Database();

            // Primero obtener el nombre del archivo para eliminarlo físicamente
            $documento = $db->queryOne("
                SELECT archivo, id_documento
                FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona
                WHERE id = :id
            ", ['id' => $id_documento_carga]);

            if (!$documento) {
                return self::resultado(false, 'Documento no encontrado.');
            }

            $nombreArchivo = $documento['archivo'];
            $id_documento = $documento['id_documento'];

            // Eliminar de la base de datos
            $db->queryOne("
                DELETE FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona
                WHERE id = :id
            ", ['id' => $id_documento_carga]);

            // Eliminar archivo físico (puede estar en diferentes carpetas según el tipo)
            $carpetas = [
                15 => 'bajas',    // Documento baja
                16 => 'reingresos', // Documento reingreso
                'default' => 'documentos'
            ];

            $carpeta = $carpetas[$id_documento] ?? $carpetas['default'];
            $rutaArchivo = sparta_uploads_join($carpeta, $nombreArchivo);

            if (file_exists($rutaArchivo)) {
                @unlink($rutaArchivo);
            }

            return self::resultado(true, 'Documento eliminado correctamente.');

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar documento.', null, $e->getMessage());
        }
    }

    public static function getPersonaDetallePerfil($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }

        try {
            $db = new Database();
            self::asegurarModuloConveniosDescargarExcel($db);
            self::asegurarModuloAccesosCapitalHumanoDb($db);

            $query = <<<SQL
            SELECT
                p.id,
                p.numero_empleado,
                p.nombres,
                p.segundo_nombre,
                p.apellidop,
                p.apellidom,
                p.correo,
                p.user_name,
                p.estatus,
                COALESCE(pais.nombre, 'Sin país') AS nombre_pais,
                COALESCE(pais.codigo_iso, 'xx') AS codigo_iso_pais
            FROM persona p
            LEFT JOIN paises pais
                   ON pais.id = p.id_pais
            WHERE p.id = $idPersona
              AND p.estatus != 'Baja'
            LIMIT 1
        SQL;

            $query_perfiles = <<<SQL
            SELECT
                $idPersona AS usuario_id,
                m.id AS modulo_id,
                CASE WHEN m.id = 27 THEN 'Panel Admin' ELSE m.nombre END AS modulo_nombre,
                m.pestana,
                m.descripcion,
                m.activo,
                CASE
                    WHEN a.usuario_id IS NOT NULL THEN 'Asignado'
                    ELSE 'No asignado'
                END AS estado,
                CASE
                    WHEN a.usuario_id IS NOT NULL THEN 1
                    ELSE 0
                END AS asignado_flag
            FROM modulos_web m
            LEFT JOIN asigna_modulo_web a
                ON a.usuario_id = $idPersona
                AND (a.modulo_web_id = m.id OR (m.id = 27 AND a.modulo_web_id IN (25)))
            WHERE m.activo = 1
              AND m.id NOT IN (25)
            ORDER BY m.id;
        SQL;

            $query_puestos= <<<SQL
               SELECT
                p.id AS id_puesto,
                p.nombre AS nombre_puesto,
                p.nivel as nivel,
                d.id AS id_departamento,
                d.nombre AS nombre_departamento,
                dorg.id AS id_area,
                dorg.nombre AS nombre_area,
                dir.id AS id_direccion,
                dir.nombre AS nombre_direccion,
                COALESCE(pa.nombre, 'Sin país') AS nombre_pais,
                CASE
                    WHEN p2.idPersona IS NULL THEN 'No asignado'
                    ELSE 'Asignado'
                END AS estado,
                CASE
                                WHEN p2.idPersona IS NULL THEN 0
                                ELSE 1
                            END AS asignado_flag
            FROM puesto p
            LEFT JOIN privilegios_departamento p2 ON p.id = p2.idPuesto AND p2.idPersona  = $idPersona
            LEFT JOIN departamento d ON d.id = p.departamento_id
            LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = d.id_departamento_organizacional AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
            LEFT JOIN paises pa ON pa.id = COALESCE(dorg.id_pais, d.id_pais)
            WHERE COALESCE(p.activo, 1) = 1
              AND COALESCE(d.activo, 1) = 1
              AND COALESCE(dorg.activo, 1) = 1
            ORDER BY d.id, p.nivel desc
        SQL;

            $query_asignacion_actual = <<<SQL
            SELECT
                d.nombre AS nombre_departamento,
                pp.nombre AS nombre_puesto,
                dorg.nombre AS nombre_area,
                dir.nombre AS nombre_direccion,
                COALESCE(pa.nombre, 'Sin país') AS nombre_pais
            FROM asigna_puesto ap
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = d.id_departamento_organizacional AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
            LEFT JOIN paises pa ON pa.id = COALESCE(dorg.id_pais, d.id_pais)
            WHERE ap.id_persona = $idPersona
              AND COALESCE(ap.activo, 1) = 1
            ORDER BY pp.nivel ASC
            LIMIT 1
        SQL;


            $persona = $db->queryOne($query);
            self::asegurarModuloTrackingCancelarRuta($db);
            $perfiles = $db->queryAll($query_perfiles);
            $perfiles = self::agregarModuloConveniosDescargarExcelSiFalta($perfiles, $idPersona, $db);
            require_once __DIR__ . '/../config/menu_modulos_sidebar.php';
            $perfiles = enriquecerPerfilesModulosConMenuSidebar($perfiles);
            $puestos = $db->queryAll($query_puestos);
            $asignacionActual = $db->queryOne($query_asignacion_actual);
            $permisosJerarquia = self::getPermisosJerarquicosPerfil($idPersona);

            return self::resultado(true, 'Persona encontrada.', [
                'persona' => $persona,
                'perfiles' => $perfiles,
                'puestos' => $puestos,
                'asignacion_actual' => $asignacionActual,
                'permisos_jerarquia' => ($permisosJerarquia['success'] ?? false) ? ($permisosJerarquia['datos'] ?? []) : null
            ]);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    private static function idsAccesosCapitalHumanoSql(): string
    {
        return implode(',', array_map('intval', self::MODULOS_ACCESOS_CAPITAL_HUMANO_IDS));
    }

    private static function modulosGestionablesAccesoCapitalHumano(): array
    {
        return array_values(array_filter(
            self::MODULOS_ACCESOS_CAPITAL_HUMANO_IDS,
            static fn ($id) => (int) $id !== self::MODULO_VALIDADOR_DOCUMENTAL_CANDIDATOS
        ));
    }

    private static function idsGestionablesAccesosCapitalHumanoSql(): string
    {
        return implode(',', array_map('intval', self::modulosGestionablesAccesoCapitalHumano()));
    }

    private static function grupoModuloAccesoCapitalHumano(int $id, string $pestana, string $nombre): array
    {
        if ($id >= 107 && $id <= 127) {
            return ['grupo' => 'Edicion cobranza', 'icono' => 'fa-solid fa-pen-to-square', 'orden' => 30];
        }
        if (in_array($id, [94, 95, 96, 97, 98, 99, 101, 103, 143], true)) {
            return ['grupo' => 'Gestiones de personal', 'icono' => 'fa-solid fa-users-gear', 'orden' => 20];
        }
        if (in_array($id, [104, 105, 142], true)) {
            return ['grupo' => 'Seleccion de personal', 'icono' => 'fa-solid fa-user-check', 'orden' => 25];
        }
        if ($id === self::MODULO_ACCESOS_CAPITAL_HUMANO) {
            return ['grupo' => 'Administracion de accesos', 'icono' => 'fa-solid fa-user-shield', 'orden' => 40];
        }
        if (strcasecmp(trim($pestana), 'Permisos especiales') === 0) {
            return ['grupo' => 'Permisos especiales', 'icono' => 'fa-solid fa-shield-halved', 'orden' => 35];
        }

        return ['grupo' => 'Modulos Capital Humano', 'icono' => 'fa-solid fa-users', 'orden' => 10];
    }

    public static function getAccesosCapitalHumano(): array
    {
        try {
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            $idsSql = self::idsGestionablesAccesosCapitalHumanoSql();

            $usuarios = $db->queryAll("
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
                    p.user_name,
                    p.estatus,
                    CONVERT(COALESCE(NULLIF(TRIM(p.telefono_uno), ''), NULLIF(TRIM(p.telefono_dos), ''), '') USING utf8mb4) COLLATE utf8mb4_general_ci AS telefono,
                    pf.foto AS foto_perfil,
                    COALESCE(NULLIF(TRIM(pdr.puesto_texto), ''), pu.nombre, '') AS puesto,
                    COALESCE(NULLIF(TRIM(pdr.departamento_texto), ''), dep.nombre, '') AS departamento,
                    COALESCE(NULLIF(TRIM(pdr.area_texto), ''), dorg.nombre, '') AS area,
                    COALESCE(NULLIF(TRIM(pdr.direccion_organizacional), ''), dir.nombre, '') AS direccion,
                    COALESCE(pa.nombre, '') AS pais,
                    COALESCE(pa.codigo_iso, 'mx') AS codigo_iso_pais,
                    COALESCE(TRIM(CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)), '') AS jefe_nombre,
                    COALESCE(am.total_modulos_ch, 0) AS total_modulos_ch,
                    COALESCE(am.tiene_accesos_ch, 0) AS tiene_accesos_ch
                FROM persona p
                LEFT JOIN perfil pf ON pf.id_persona = p.id
                LEFT JOIN persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                LEFT JOIN departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                LEFT JOIN departamento_organizacional dorg ON dorg.id = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = COALESCE(pdr.id_area, dep.id_departamento_organizacional)
                   AND COALESCE(ad.activo, 1) = 1
                LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                LEFT JOIN paises pa ON pa.id = p.id_pais
                LEFT JOIN (
                    SELECT id_persona, MAX(id) AS id_ultimo
                    FROM asigna_jefe
                    GROUP BY id_persona
                ) aj_ult ON aj_ult.id_persona = p.id
                LEFT JOIN asigna_jefe aj ON aj.id = aj_ult.id_ultimo
                LEFT JOIN vacantes_personal vp ON vp.id = aj.id_vacante_jefe
                LEFT JOIN persona pj ON pj.id = COALESCE(aj.id_jefe, vp.id_jefe)
                LEFT JOIN (
                    SELECT
                        usuario_id,
                        COUNT(*) AS total_modulos_ch,
                        MAX(CASE WHEN modulo_web_id = " . (int) self::MODULO_ACCESOS_CAPITAL_HUMANO . " THEN 1 ELSE 0 END) AS tiene_accesos_ch
                    FROM asigna_modulo_web
                    WHERE modulo_web_id IN ($idsSql)
                    GROUP BY usuario_id
                ) am ON am.usuario_id = p.id
                WHERE COALESCE(p.estatus, '') <> 'Baja'
                ORDER BY CASE WHEN p.estatus = 'Activo' THEN 0 ELSE 1 END, nombre ASC
            ");

            $totales = [
                'total' => count($usuarios),
                'activos' => 0,
                'inactivos' => 0,
                'con_permisos_ch' => 0,
                'sin_permisos_ch' => 0,
                'con_acceso_panel' => 0,
            ];
            foreach ($usuarios as $u) {
                if (strcasecmp((string)($u['estatus'] ?? ''), 'Activo') === 0) {
                    $totales['activos']++;
                } else {
                    $totales['inactivos']++;
                }
                if ((int)($u['total_modulos_ch'] ?? 0) > 0) {
                    $totales['con_permisos_ch']++;
                } else {
                    $totales['sin_permisos_ch']++;
                }
                if ((int)($u['tiene_accesos_ch'] ?? 0) === 1) {
                    $totales['con_acceso_panel']++;
                }
            }

            return self::resultado(true, 'Usuarios cargados.', [
                'usuarios' => $usuarios,
                'totales' => $totales,
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudieron cargar los accesos de Capital Humano.', [
                'usuarios' => [],
                'totales' => ['total' => 0, 'activos' => 0, 'inactivos' => 0, 'con_permisos_ch' => 0, 'sin_permisos_ch' => 0, 'con_acceso_panel' => 0],
            ], $e->getMessage());
        }
    }

    public static function getAccesoCapitalHumanoDetalle(int $idPersona): array
    {
        try {
            if ($idPersona <= 0) {
                return self::resultado(false, 'Usuario invalido.');
            }
            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            $idsSql = self::idsGestionablesAccesosCapitalHumanoSql();

            $usuario = $db->queryOne("
                SELECT
                    p.id AS persona_id,
                    p.numero_empleado,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre,
                    p.correo,
                    p.user_name,
                    p.password,
                    p.estatus,
                    COALESCE(NULLIF(TRIM(pdr.puesto_texto), ''), pu.nombre, '') AS puesto,
                    COALESCE(NULLIF(TRIM(pdr.departamento_texto), ''), dep.nombre, '') AS departamento
                FROM persona p
                LEFT JOIN persona_datos_rrhh pdr ON pdr.id_persona = p.id
                LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN puesto pu ON pu.id = COALESCE(pdr.id_puesto, ap.id_puesto)
                LEFT JOIN departamento dep ON dep.id = COALESCE(pdr.id_departamento, pu.departamento_id)
                WHERE p.id = :id
                  AND COALESCE(p.estatus, '') <> 'Baja'
                LIMIT 1
            ", ['id' => $idPersona]);
            if (!$usuario) {
                return self::resultado(false, 'No se encontro el usuario en Capital Humano.');
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
                WHERE COALESCE(m.activo, 1) = 1
                  AND m.id IN ($idsSql)
                ORDER BY FIELD(m.id, $idsSql), m.nombre ASC
            ", ['persona_id' => $idPersona]);

            foreach ($modulos as &$m) {
                $meta = self::grupoModuloAccesoCapitalHumano(
                    (int)($m['id'] ?? 0),
                    (string)($m['pestana'] ?? ''),
                    (string)($m['nombre'] ?? '')
                );
                $m['grupo_ch'] = $meta['grupo'];
                $m['grupo_icono'] = $meta['icono'];
                $m['grupo_orden'] = $meta['orden'];
            }
            unset($m);
            usort($modulos, static function (array $a, array $b): int {
                $ga = (int)($a['grupo_orden'] ?? 999);
                $gb = (int)($b['grupo_orden'] ?? 999);
                if ($ga !== $gb) {
                    return $ga <=> $gb;
                }
                return ((int)($a['id'] ?? 0)) <=> ((int)($b['id'] ?? 0));
            });

            return self::resultado(true, 'Detalle cargado.', [
                'usuario' => $usuario,
                'modulos' => $modulos,
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'No se pudo cargar el detalle del acceso Capital Humano.', null, $e->getMessage());
        }
    }

    public static function guardarPermisosAccesoCapitalHumano(array $input): array
    {
        $db = null;
        try {
            $idPersona = (int)($input['id_persona'] ?? 0);
            if ($idPersona <= 0) {
                return self::resultado(false, 'Usuario invalido.');
            }
            $modulos = $input['modulos'] ?? [];
            if (!is_array($modulos)) {
                $modulos = [];
            }
            $permitidos = array_fill_keys(self::modulosGestionablesAccesoCapitalHumano(), true);
            $modulos = array_values(array_unique(array_filter(array_map('intval', $modulos), static function ($mid) use ($permitidos) {
                return isset($permitidos[$mid]);
            })));

            $db = new Database();
            self::asegurarModuloAccesosCapitalHumanoDb($db);
            $idsSql = self::idsGestionablesAccesosCapitalHumanoSql();

            $usuario = $db->queryOne(
                "SELECT id FROM persona WHERE id = :id AND COALESCE(estatus, '') <> 'Baja' LIMIT 1",
                ['id' => $idPersona]
            );
            if (!$usuario) {
                return self::resultado(false, 'No se encontro el usuario en Capital Humano.');
            }

            if (!empty($modulos)) {
                $modulosActivos = $db->queryAll("
                    SELECT id
                    FROM modulos_web
                    WHERE COALESCE(activo, 1) = 1
                      AND id IN ($idsSql)
                      AND id IN (" . implode(',', array_map('intval', $modulos)) . ")
                ");
                $activos = array_fill_keys(array_map('intval', array_column($modulosActivos, 'id')), true);
                $modulos = array_values(array_filter($modulos, static function ($mid) use ($activos) {
                    return isset($activos[(int)$mid]);
                }));
            }

            $db->beginTransaction();
            $db->CRUD(
                "DELETE FROM asigna_modulo_web
                 WHERE usuario_id = :uid
                   AND modulo_web_id IN ($idsSql)",
                ['uid' => $idPersona]
            );
            foreach ($modulos as $moduloId) {
                $db->CRUD(
                    "INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id)
                     VALUES (:uid, :mid)",
                    ['uid' => $idPersona, 'mid' => $moduloId]
                );
            }
            $db->CRUD(
                "UPDATE persona SET session_version = COALESCE(session_version, 1) + 1 WHERE id = :id",
                ['id' => $idPersona]
            );
            $db->commit();

            return self::resultado(true, 'Permisos de Capital Humano guardados.', [
                'persona_id' => $idPersona,
                'modulos' => $modulos,
            ]);
        } catch (\Throwable $e) {
            if ($db && $db->inTransaction()) {
                $db->rollback();
            }
            return self::resultado(false, 'No se pudieron guardar los permisos de Capital Humano.', null, $e->getMessage());
        }
    }

    private static function existeTablaPermisosJerarquicos(Database $db): bool
    {
        try {
            $row = $db->queryOne("SHOW TABLES LIKE 'privilegios_jerarquia'");
            return !empty($row);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private static function placeholdersIn(string $prefijo, array $valores, array &$params): string
    {
        $keys = [];
        foreach (array_values($valores) as $i => $valor) {
            $k = $prefijo . '_' . $i;
            $keys[] = ':' . $k;
            $params[$k] = (int) $valor;
        }
        return implode(', ', $keys);
    }

    private static function obtenerSeleccionesJerarquicas(Database $db, int $idPersona): array
    {
        $sel = ['pais' => [], 'area' => [], 'departamento' => [], 'puesto' => []];
        if (!self::existeTablaPermisosJerarquicos($db)) {
            return $sel;
        }
        $rows = $db->queryAll(
            "SELECT nivel, id_nodo
             FROM privilegios_jerarquia
             WHERE id_persona = :id_persona",
            ['id_persona' => $idPersona]
        ) ?: [];
        foreach ($rows as $r) {
            $nivel = (string) ($r['nivel'] ?? '');
            if (!isset($sel[$nivel])) {
                continue;
            }
            $sel[$nivel][] = (int) ($r['id_nodo'] ?? 0);
        }
        foreach ($sel as $k => $ids) {
            $sel[$k] = array_values(array_unique(array_filter(array_map('intval', $ids))));
        }
        return $sel;
    }

    private static function sincronizarLegacyDesdeJerarquia(Database $db, int $idPersona, array $seleccion): void
    {
        $idsPais = array_values(array_unique(array_filter(array_map('intval', $seleccion['pais'] ?? []))));
        $idsArea = array_values(array_unique(array_filter(array_map('intval', $seleccion['area'] ?? []))));
        $idsDepartamento = array_values(array_unique(array_filter(array_map('intval', $seleccion['departamento'] ?? []))));
        $idsPuesto = array_values(array_unique(array_filter(array_map('intval', $seleccion['puesto'] ?? []))));

        $puestosFinales = [];
        foreach ($idsPuesto as $idp) {
            $puestosFinales[$idp] = true;
        }

        if (!empty($idsDepartamento)) {
            $params = [];
            $in = self::placeholdersIn('dep', $idsDepartamento, $params);
            $rows = $db->queryAll(
                "SELECT p.id
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 WHERE p.departamento_id IN ($in)
                   AND COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1",
                $params
            ) ?: [];
            foreach ($rows as $r) {
                $puestosFinales[(int) ($r['id'] ?? 0)] = true;
            }
        }

        if (!empty($idsArea)) {
            $params = [];
            $in = self::placeholdersIn('area', $idsArea, $params);
            $rows = $db->queryAll(
                "SELECT p.id
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 WHERE d.id_departamento_organizacional IN ($in)
                   AND COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1",
                $params
            ) ?: [];
            foreach ($rows as $r) {
                $puestosFinales[(int) ($r['id'] ?? 0)] = true;
            }
        }

        if (!empty($idsPais)) {
            $params = [];
            $in = self::placeholdersIn('pais', $idsPais, $params);
            $rows = $db->queryAll(
                "SELECT p.id
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 INNER JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 WHERE a.id_pais IN ($in)
                   AND COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1",
                $params
            ) ?: [];
            foreach ($rows as $r) {
                $puestosFinales[(int) ($r['id'] ?? 0)] = true;
            }
        }

        $db->CRUD(
            "DELETE FROM privilegios_departamento WHERE idPersona = :id_persona",
            ['id_persona' => $idPersona]
        );

        foreach (array_keys($puestosFinales) as $idPuestoInsert) {
            if ($idPuestoInsert <= 0) {
                continue;
            }
            $db->CRUD(
                "INSERT INTO privilegios_departamento (idPersona, idPuesto)
                 VALUES (:id_persona, :id_puesto)",
                ['id_persona' => $idPersona, 'id_puesto' => (int) $idPuestoInsert]
            );
        }
    }

    public static function getPermisosJerarquicosPerfil(int $idPersona): array
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }

        try {
            $db = new Database();
            $seleccion = self::obtenerSeleccionesJerarquicas($db, $idPersona);

            // Fallback de compatibilidad: si aún no existe selección jerárquica,
            // preseleccionar puestos desde la tabla legacy.
            $totalSel = count($seleccion['pais']) + count($seleccion['area']) + count($seleccion['departamento']) + count($seleccion['puesto']);
            if ($totalSel === 0) {
                $rowsLegacy = $db->queryAll(
                    "SELECT pd.idPuesto
                     FROM privilegios_departamento pd
                     INNER JOIN puesto p ON p.id = pd.idPuesto
                     INNER JOIN departamento d ON d.id = p.departamento_id
                     LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                     WHERE pd.idPersona = :id_persona
                       AND COALESCE(p.activo, 1) = 1
                       AND COALESCE(d.activo, 1) = 1
                       AND COALESCE(a.activo, 1) = 1",
                    ['id_persona' => $idPersona]
                ) ?: [];
                foreach ($rowsLegacy as $r) {
                    $idP = (int) ($r['idPuesto'] ?? 0);
                    if ($idP > 0) {
                        $seleccion['puesto'][] = $idP;
                    }
                }
                $seleccion['puesto'] = array_values(array_unique($seleccion['puesto']));
            }

            $paises = $db->queryAll(
                "SELECT id, nombre, COALESCE(codigo_iso, 'xx') AS codigo_iso
                 FROM paises
                 ORDER BY nombre ASC"
            ) ?: [];
            $areas = $db->queryAll(
                "SELECT
                    a.id,
                    a.nombre,
                    a.id_pais,
                    COALESCE(pa.nombre, 'Sin país') AS nombre_pais
                 FROM departamento_organizacional a
                 LEFT JOIN paises pa ON pa.id = a.id_pais
                 WHERE COALESCE(a.activo, 1) = 1
                 ORDER BY pa.nombre ASC, a.nombre ASC"
            ) ?: [];
            $departamentos = $db->queryAll(
                "SELECT
                    d.id,
                    d.nombre,
                    d.id_departamento_organizacional AS id_area
                 FROM departamento d
                 LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 WHERE COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1
                 ORDER BY d.nombre ASC"
            ) ?: [];
            $puestos = $db->queryAll(
                "SELECT
                    p.id,
                    p.nombre,
                    p.nivel,
                    p.departamento_id AS id_departamento
                 FROM puesto p
                 INNER JOIN departamento d ON d.id = p.departamento_id
                 LEFT JOIN departamento_organizacional a ON a.id = d.id_departamento_organizacional
                 WHERE COALESCE(p.activo, 1) = 1
                   AND COALESCE(d.activo, 1) = 1
                   AND COALESCE(a.activo, 1) = 1
                 ORDER BY p.nivel DESC, p.nombre ASC"
            ) ?: [];

            return self::resultado(true, 'Permisos jerárquicos cargados.', [
                'paises' => $paises,
                'areas' => $areas,
                'departamentos' => $departamentos,
                'puestos' => $puestos,
                'seleccion' => [
                    'pais' => array_values($seleccion['pais']),
                    'area' => array_values($seleccion['area']),
                    'departamento' => array_values($seleccion['departamento']),
                    'puesto' => array_values($seleccion['puesto']),
                ],
            ]);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al obtener permisos jerárquicos.', null, $e->getMessage());
        }
    }

    public static function guardarPermisosJerarquicosPerfil(int $idPersona, array $seleccion): array
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }
        $permitidos = ['pais', 'area', 'departamento', 'puesto'];
        $limpia = ['pais' => [], 'area' => [], 'departamento' => [], 'puesto' => []];
        foreach ($permitidos as $nivel) {
            $vals = $seleccion[$nivel] ?? [];
            if (!is_array($vals)) {
                $vals = [];
            }
            $limpia[$nivel] = array_values(array_unique(array_filter(array_map('intval', $vals))));
        }

        $db = new Database();
        try {
            $db->beginTransaction();

            if (self::existeTablaPermisosJerarquicos($db)) {
                $db->CRUD(
                    "DELETE FROM privilegios_jerarquia WHERE id_persona = :id_persona",
                    ['id_persona' => $idPersona]
                );
                foreach ($permitidos as $nivel) {
                    foreach ($limpia[$nivel] as $idNodo) {
                        $db->CRUD(
                            "INSERT INTO privilegios_jerarquia (id_persona, nivel, id_nodo)
                             VALUES (:id_persona, :nivel, :id_nodo)",
                            ['id_persona' => $idPersona, 'nivel' => $nivel, 'id_nodo' => (int) $idNodo]
                        );
                    }
                }
            }

            self::sincronizarLegacyDesdeJerarquia($db, $idPersona, $limpia);
            $db->commit();

            return self::resultado(true, 'Permisos jerárquicos guardados correctamente.', [
                'seleccion' => $limpia
            ]);
        } catch (\Throwable $e) {
            $db->rollback();
            return self::resultado(false, 'Error al guardar permisos jerárquicos.', null, $e->getMessage());
        }
    }

    public static function actualizarPermisoJerarquicoPerfil(int $idPersona, string $nivel, int $idNodo, int $asignado): array
    {
        $idPersona = (int) $idPersona;
        $idNodo = (int) $idNodo;
        $asignado = ((int) $asignado) === 1 ? 1 : 0;
        $nivel = trim(strtolower($nivel));
        if ($idPersona <= 0 || $idNodo <= 0 || !in_array($nivel, ['pais', 'area', 'departamento', 'puesto'], true)) {
            return self::resultado(false, 'Parámetros inválidos.', null);
        }

        try {
            $db = new Database();
            $seleccion = self::obtenerSeleccionesJerarquicas($db, $idPersona);
            $ids = array_values(array_unique(array_filter(array_map('intval', $seleccion[$nivel] ?? []))));

            if ($asignado === 1) {
                if (!in_array($idNodo, $ids, true)) {
                    $ids[] = $idNodo;
                }
            } else {
                $ids = array_values(array_filter($ids, function ($v) use ($idNodo) {
                    return (int) $v !== (int) $idNodo;
                }));
            }
            $seleccion[$nivel] = $ids;

            return self::guardarPermisosJerarquicosPerfil($idPersona, $seleccion);
        } catch (\Throwable $e) {
            return self::resultado(false, 'Error al actualizar permiso jerárquico.', null, $e->getMessage());
        }
    }

    /**
     * Marca persona.force_logout = 1. SessionGuard cerrará la sesión en la próxima validación (~20 s).
     */
    public static function forzarLogoutPersona($idPersona)
    {
        $idPersona = (int) $idPersona;
        if ($idPersona <= 0) {
            return self::resultado(false, 'ID de persona inválido.');
        }

        try {
            $db = new Database();
            $persona = $db->queryOne(
                "SELECT id, estatus, force_logout
                   FROM persona
                  WHERE id = :id
                  LIMIT 1",
                ['id' => $idPersona]
            );

            if (!$persona) {
                return self::resultado(false, 'No se encontro el usuario indicado.');
            }

            $estatus = strtolower(trim((string) ($persona['estatus'] ?? '')));
            if ($estatus === 'baja') {
                return self::resultado(false, 'No se puede forzar cierre porque el usuario esta dado de baja.');
            }

            if ((int) ($persona['force_logout'] ?? 0) === 1) {
                return self::resultado(
                    true,
                    'El cierre de sesion ya estaba solicitado. Se aplicara en cuanto el sistema valide la sesion del usuario.'
                );
            }

            $n = $db->CRUD(
                "UPDATE persona
                    SET force_logout = 1
                  WHERE id = :id",
                ['id' => $idPersona]
            );
            if ($n < 1) {
                return self::resultado(
                    false,
                    'No se pudo actualizar. Verifique que el usuario exista y no esté dado de baja.'
                );
            }

            return self::resultado(
                true,
                'Cierre de sesión solicitado. Se aplicará en cuanto el sistema valide la sesión del usuario (unos segundos).'
            );
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    public static function getComboDepartamentos($perfil_id = null)
    {
        $where = '';

        if (!empty($perfil_id)) {
            $perfil_id = intval($perfil_id); // 🔐 seguridad
            $where = "WHERE d.id = $perfil_id";
        }

        $query = <<<SQL
        SELECT DISTINCT d.*
        FROM privilegios_departamento pd
        INNER JOIN puesto p
            ON p.id = pd.idPuesto
        INNER JOIN departamento d
            ON d.id = p.departamento_id
        $where
        ORDER BY d.nombre ASC
    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getConsultaPuestos($departamento)
    {
        // Query base
        $query = <<<SQL
        SELECT
            p.id, p.nombre, p.nivel, d.nombre as departamento
        FROM puesto p
        INNER JOIN departamento d ON d.id = p.departamento_id
    SQL;

        $params = [];

        // Agregar filtro si se envió un departamento
        if ($departamento != null) {
            $query .= " WHERE d.id = :departamento";
            $params['departamento'] = $departamento;
        }

        try {
            $db = new Database();
            // Pasar parámetros si existen
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Puestos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /**
     * Puestos de un departamento que la persona puede asignar: solo los que tiene en privilegios_departamento.
     * Usado en Gestión de Usuarios (Agregar/Editar Puesto) para que solo se listen puestos a los que el usuario en sesión tiene acceso.
     */
    public static function getConsultaPuestosParaGestor($departamento, $id_persona)
    {
        $departamento = $departamento !== null ? (int) $departamento : 0;
        $id_persona = (int) $id_persona;
        if ($departamento <= 0 || $id_persona <= 0) {
            return self::resultado(true, 'Puestos encontrados.', []);
        }

        // Mismo criterio que getConsultaDepartamentoGestor: pd.idPersona y puesto por departamento
        $query = <<<SQL
        SELECT DISTINCT
            p.id, p.nombre, p.nivel, d.nombre as departamento
        FROM privilegios_departamento pd
        INNER JOIN puesto p ON p.id = pd.idPuesto
        INNER JOIN departamento d ON d.id = p.departamento_id
        WHERE pd.idPersona = $id_persona AND d.id = :departamento
        ORDER BY p.nivel ASC, p.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query, ['departamento' => $departamento]);
            return self::resultado(true, 'Puestos encontrados.', $r ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getRazonesAusencia()
    {
        // Query base
        $query = <<<SQL
        SELECT
            id,
            clave,
            nombre,
            descripcion
        FROM razon_ausencia
        WHERE activo = 1
        ORDER BY nombre
    SQL;

        $params = [];

        try {
            $db = new Database();

            // Ejecutar query (no requiere parámetros)
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Razones de ausencia encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener razones de ausencia.',
                null,
                $e->getMessage()
            );
        }
    }

    public static function getAusenciasPersona($idPersona)
    {
        $query = <<<SQL
        SELECT
            a.id,
            r.nombre AS razon,
            a.fecha_inicio,
            a.fecha_fin,
            a.descripcion,
            a.activo
        FROM ausencia a
        INNER JOIN razon_ausencia r ON r.id = a.id_razon
        WHERE a.id_persona = :idPersona
        ORDER BY a.fecha_inicio DESC
    SQL;

        $params = ['idPersona' => $idPersona];

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);

            return self::resultado(true, 'Ausencias encontradas.', $r);

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al obtener ausencias.',
                null,
                $e->getMessage()
            );
        }
    }




    public static function getConsultaJefe($id_departamento)
    {
        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $query = <<<SQL
          SELECT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.nombre AS nombre_puesto
        FROM asigna_puesto ap
        INNER JOIN persona per
            ON per.id = ap.id_persona
        INNER JOIN puesto pu
            ON pu.id = ap.id_puesto
        WHERE
            COALESCE(ap.activo, 1) = 1
            AND pu.es_jefe = 1 AND per.estatus != 'Baja'
            AND {$predPer}
            AND pu.departamento_id = $id_departamento
        ORDER BY per.nombres ASC;
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /** Personas con puesto en el departamento (para combo jefe cuando no hay es_jefe ni por nivel) */
    public static function getPersonasPorDepartamento($id_departamento)
    {
        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $query = <<<SQL
          SELECT DISTINCT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.nombre AS nombre_puesto
          FROM asigna_puesto ap
          INNER JOIN persona per ON per.id = ap.id_persona
          INNER JOIN puesto pu ON pu.id = ap.id_puesto
          WHERE COALESCE(ap.activo, 1) = 1
            AND per.estatus != 'Baja'
            AND {$predPer}
            AND pu.departamento_id = $id_departamento
          ORDER BY per.nombres ASC
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /** Personas activas de la empresa para escoger jefe cuando el puesto no tiene jefe jerarquico configurado. */
    public static function getPersonasActivasEmpresaParaJefe()
    {
        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $query = <<<SQL
          SELECT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            COALESCE(MIN(pu.nombre), '') AS nombre_puesto
          FROM persona per
          LEFT JOIN asigna_puesto ap
            ON ap.id_persona = per.id
           AND COALESCE(ap.activo, 1) = 1
          LEFT JOIN puesto pu
            ON pu.id = ap.id_puesto
          WHERE per.estatus != 'Baja'
            AND {$predPer}
          GROUP BY per.id, per.nombres, per.segundo_nombre, per.apellidop, per.apellidom
          ORDER BY nombre_completo ASC
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas activas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /** Jefe por defecto cuando no hay resultados (ej. Legal/Abogado): JONNATHAN MARLON FLORES RODRIGUEZ */
    public static function getJefeDefault()
    {
        $predPer = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('per');
        $query = <<<SQL
          SELECT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            COALESCE(pu.nombre, '') AS nombre_puesto
          FROM persona per
          LEFT JOIN asigna_puesto ap ON ap.id_persona = per.id AND COALESCE(ap.activo, 1) = 1
          LEFT JOIN puesto pu ON pu.id = ap.id_puesto
          WHERE per.estatus != 'Baja'
            AND {$predPer}
            AND per.nombres LIKE '%JONNATHAN%'
            AND (per.apellidop LIKE '%FLORES%' OR per.apellidop LIKE '%FLÓRES%')
            AND (per.apellidom LIKE '%RODRIGUEZ%' OR per.apellidom LIKE '%RODRÍGUEZ%')
          LIMIT 1
        SQL;
        try {
            $db = new Database();
            $r = $db->queryOne($query);
            return $r ? self::resultado(true, 'Jefe por defecto.', [$r]) : self::resultado(true, 'Sin resultados.', []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    public static function getAusenciaById($idAusencia)
    {
        $query = <<<SQL
        SELECT
            a.id,
            a.id_persona,
            a.id_razon,
            r.nombre AS razon,
            a.fecha_inicio,
            a.fecha_fin,
            a.descripcion,
            a.activo
        FROM ausencia a
        INNER JOIN razon_ausencia r ON r.id = a.id_razon
        WHERE a.id = :idAusencia
        LIMIT 1
    SQL;

        try {
            $db = new Database();

            $r = $db->queryOne($query, [
                'idAusencia' => $idAusencia
            ]);

            if (!$r) {
                return self::resultado(false, 'Ausencia no encontrada.', null);
            }

            return self::resultado(true, 'Ausencia encontrada.', $r);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener la ausencia.', null, $e->getMessage());
        }
    }





    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    ////////////////////////////////////////////////////////////////////// VALIDADO AL 100
    public static function getConsultaGestoresPorPuesto($id_puesto)
    {
        $predP = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p');
        $query = <<<SQL
        SELECT DISTINCT
            p.id,
            CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
            pp.nombre AS puesto,
            pp.nivel
        FROM persona p
        INNER JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
        INNER JOIN puesto pp ON pp.id = ap.id_puesto
        WHERE p.estatus != 'Baja'
          AND {$predP}
          AND pp.nivel > (
                SELECT nivel
                FROM puesto
                WHERE id = $id_puesto
            )
          AND pp.departamento_id = (
                SELECT departamento_id
                FROM puesto
                WHERE id = $id_puesto
            )
        ORDER BY pp.nivel ASC, nombre_completo
    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Jefes encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener jefes.', null, $e->getMessage());
        }
    }

    public static function getPersonasOrganigrama($departamento, $id_persona)
    {
        try {
            $db = new Database();
            // -------------------------------------------------------
            // 1) Puestos activos del departamento
            // -------------------------------------------------------
            $queryPuestos = <<<SQL
            SELECT
                p.id,
                p.nombre,
                p.nivel
            FROM puesto p
            WHERE p.activo = 1 AND es_jefe = 1
              AND p.departamento_id = :departamento
        SQL;

            $puestos = $db->queryAll($queryPuestos, [
                'departamento' => $departamento
            ]);

            if (!$puestos) {
                return self::resultado(true, 'No hay puestos activos en este departamento.', []);
            }

            // -------------------------------------------------------
            // 2) Mayor nivel jerárquico
            // -------------------------------------------------------
            $puestosTopIds = array_column($puestos, 'id');



            // -------------------------------------------------------
            // 3) Crear placeholders con nombre (:p0, :p1, ...)
            // -------------------------------------------------------
            $params = [];
            $placeholders = [];

            foreach ($puestosTopIds as $i => $id) {
                $key = "p$i";
                $placeholders[] = ":$key";
                $params[$key] = $id;
            }

            $placeholdersStr = implode(',', $placeholders);


            // -------------------------------------------------------
            // 4) Personas por puestos top
            // -------------------------------------------------------
                $predOrg = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p');
                $queryPersonas = <<<SQL
                SELECT
                p.id,
                CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre,
                ap.id_puesto
            FROM persona p
            INNER JOIN asigna_puesto ap ON ap.id_persona = p.id AND ap.activo = 1
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            WHERE ap.id_puesto IN ($placeholdersStr)
              AND p.estatus != 'Baja'
              AND {$predOrg}
            ORDER BY
                pp.nivel DESC,
                nombre ASC
        SQL;

                $personas = $db->queryAll($queryPersonas, $params);

            // Una sola entrada por persona (evitar duplicados cuando tiene varios puestos en el departamento)
            $byPersonId = [];
            foreach ($personas as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id && !isset($byPersonId[$id])) {
                    $byPersonId[$id] = $row;
                }
            }
            $personas = array_values($byPersonId);

            return self::resultado(true, 'Personas de mayor rango encontradas.', $personas);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
    public static function getConsultaPersonasJerarquia($id_persona, $id_departamento = 0)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }
        $id_departamento = (int) $id_departamento;
        $filtroDepto = '';
        if ($id_departamento > 0) {
            $filtroDepto = " AND p.id IN (SELECT ap_in.id_persona FROM asigna_puesto ap_in INNER JOIN puesto pp_in ON pp_in.id = ap_in.id_puesto WHERE ap_in.activo = 1 AND pp_in.departamento_id = $id_departamento)";
        }
        $filtroDepto2 = '';
        if ($id_departamento > 0) {
            $filtroDepto2 = " AND p2.id IN (SELECT ap_in.id_persona FROM asigna_puesto ap_in INNER JOIN puesto pp_in ON pp_in.id = ap_in.id_puesto WHERE ap_in.activo = 1 AND pp_in.departamento_id = $id_departamento)";
        }
        // Un puesto por persona: si hay departamento, solo puestos de ese departamento y el de mayor rango (menor nivel)
        // Sin departamento: cualquier puesto, desempate por MIN(id_puesto)
        // Con departamento: el puesto de MAYOR nivel (mayor rango) en ese departamento; desempate por MIN(id_puesto)
        // Solo asigna_puesto activo (activo=1); si no, filas históricas/inactivas sesgan MAX(nivel) y el título en organigrama.
        $subqueryPuesto = "SELECT id_persona, MIN(id_puesto) AS id_puesto FROM asigna_puesto WHERE activo = 1 GROUP BY id_persona";
        if ($id_departamento > 0) {
            $subqueryPuesto = "SELECT ap.id_persona, MIN(ap.id_puesto) AS id_puesto FROM asigna_puesto ap "
                . "INNER JOIN puesto pp ON pp.id = ap.id_puesto AND pp.departamento_id = $id_departamento "
                . "INNER JOIN (SELECT ap2.id_persona, MAX(pp2.nivel) AS max_nivel FROM asigna_puesto ap2 INNER JOIN puesto pp2 ON pp2.id = ap2.id_puesto AND pp2.departamento_id = $id_departamento WHERE ap2.activo = 1 GROUP BY ap2.id_persona) sel ON sel.id_persona = ap.id_persona AND pp.nivel = sel.max_nivel "
                . "WHERE ap.activo = 1 "
                . "GROUP BY ap.id_persona";
        }
        $filtroPuestoRaiz = $id_departamento > 0 ? " AND pp.departamento_id = $id_departamento" : '';
        $orderPuestoRaiz = $id_departamento > 0 ? " ORDER BY pp.nivel DESC" : '';
        $exJP = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p');
        $exJP2 = UsuarioFantasmaReporteria::sqlPredicadoExcluirPersona('p2');

        $query = <<<SQL
               WITH RECURSIVE Jerarquia AS (

                -- NIVEL 1: un solo puesto por persona (del departamento si se filtró); solo personas con puesto en el departamento
                SELECT
                    p.id,
                    p.nombres,
                    p.segundo_nombre,
                    p.apellidop,
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    aj.id_jefe,
                    1 AS nivel
                FROM persona p
                JOIN ($subqueryPuesto) ap ON p.id = ap.id_persona
                JOIN puesto pp ON pp.id = ap.id_puesto
                JOIN (SELECT id_persona, MIN(id_jefe) AS id_jefe FROM asigna_jefe GROUP BY id_persona) aj ON p.id = aj.id_persona
                WHERE p.estatus != 'Baja'
                  AND {$exJP}
                  AND aj.id_jefe = $id_persona
                  $filtroDepto

                UNION ALL

                -- NIVELES 2–4: un solo puesto por persona (del departamento si se filtró); solo personas del departamento
                SELECT
                    p2.id,
                    p2.nombres,
                    p2.segundo_nombre,
                    p2.apellidop,
                    ap2.id_puesto,
                    pp2.nombre AS nombre_puesto,
                    aj2.id_jefe,
                    j.nivel + 1 AS nivel
                FROM persona p2
                JOIN ($subqueryPuesto) ap2 ON p2.id = ap2.id_persona
                JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                JOIN (SELECT id_persona, MIN(id_jefe) AS id_jefe FROM asigna_jefe GROUP BY id_persona) aj2 ON p2.id = aj2.id_persona
                JOIN Jerarquia j ON aj2.id_jefe = j.id
                WHERE p2.estatus != 'Baja'
                  AND {$exJP2}
                  AND j.nivel < 4
                  $filtroDepto2
            )

            SELECT JSON_OBJECT(
                'id_jefe', $id_persona,
                'nombre_jefe', (
                    SELECT CONCAT_WS(' ', nombres, segundo_nombre, apellidop)
                    FROM persona
                    WHERE id = $id_persona
                ),
                'nombre_puesto', (
                    SELECT pp.nombre
                    FROM persona p
                    INNER JOIN asigna_puesto ap ON ap.id_persona = p.id AND ap.activo = 1
                    INNER JOIN puesto pp ON pp.id = ap.id_puesto
                    WHERE p.id = $id_persona $filtroPuestoRaiz
                    $orderPuestoRaiz
                    LIMIT 1
                ),
                'subordinados', (
                    SELECT COALESCE(JSON_ARRAYAGG(
                        JSON_OBJECT(
                            'id', j1.id,
                            'nombre', CONCAT_WS(' ', j1.nombres, j1.segundo_nombre, j1.apellidop),
                            'id_puesto', j1.id_puesto,
                            'nombre_puesto', j1.nombre_puesto,
                            'nivel', j1.nivel,

                            'subordinados', (
                                SELECT COALESCE(JSON_ARRAYAGG(
                                    JSON_OBJECT(
                                        'id', j2.id,
                                        'nombre', CONCAT_WS(' ', j2.nombres, j2.segundo_nombre, j2.apellidop),
                                        'id_puesto', j2.id_puesto,
                                        'nombre_puesto', j2.nombre_puesto,
                                        'nivel', j2.nivel,

                                        'subordinados', (
                                            SELECT COALESCE(JSON_ARRAYAGG(
                                                JSON_OBJECT(
                                                    'id', j3.id,
                                                    'nombre', CONCAT_WS(' ', j3.nombres, j3.segundo_nombre, j3.apellidop),
                                                    'id_puesto', j3.id_puesto,
                                                    'nombre_puesto', j3.nombre_puesto,
                                                    'nivel', j3.nivel,

                                                    'subordinados', (
                                                        SELECT COALESCE(JSON_ARRAYAGG(
                                                            JSON_OBJECT(
                                                                'id', j4.id,
                                                                'nombre', CONCAT_WS(' ', j4.nombres, j4.segundo_nombre, j4.apellidop),
                                                                'id_puesto', j4.id_puesto,
                                                                'nombre_puesto', j4.nombre_puesto,
                                                                'nivel', j4.nivel
                                                            )
                                                        ), JSON_ARRAY())
                                                        FROM Jerarquia j4
                                                        WHERE j4.id_jefe = j3.id
                                                          AND j4.nivel = 4
                                                    )
                                                )
                                            ), JSON_ARRAY())
                                            FROM Jerarquia j3
                                            WHERE j3.id_jefe = j2.id
                                              AND j3.nivel = 3
                                        )
                                    )
                                ), JSON_ARRAY())
                                FROM Jerarquia j2
                                WHERE j2.id_jefe = j1.id
                                  AND j2.nivel = 2
                            )
                        )
                    ), JSON_ARRAY())
                    FROM Jerarquia j1
                    WHERE j1.id_jefe = $id_persona
                      AND j1.nivel = 1
                )
            ) AS organigrama_json;


    SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }
    /** Departamentos que el usuario puede usar (asignar/cambiar): solo los que tienen puestos en privilegios_departamento para ESE usuario. */
    public static function getConsultaDepartamentoGestor($perfil_id)
    {
        $perfil_id = (int) $perfil_id;
        $condiciones = [
            'COALESCE(d.activo, 1) = 1',
            'COALESCE(p.activo, 1) = 1',
            'COALESCE(dorg.activo, 1) = 1'
        ];
        if ($perfil_id > 0) {
            $condiciones[] = 'pd.idPersona = ' . $perfil_id;
        }
        $complet = 'WHERE ' . implode(' AND ', $condiciones);

        $query = <<<SQL
           SELECT DISTINCT
                d.*,
                d.id_departamento_organizacional,
                COALESCE(dorg.nombre, 'Sin departamento') AS departamento_organizacional_nombre,
                COALESCE(dorg.activo, 1) AS departamento_organizacional_activo,
                COALESCE(dir.id, 0) AS id_direccion,
                COALESCE(dir.nombre, 'Sin dirección') AS direccion_nombre,
                COALESCE(dir.activo, 1) AS direccion_activo,
                pa.nombre AS nombre_pais,
                COALESCE(pa.codigo_iso, 'xx') AS codigo_iso_pais
            FROM privilegios_departamento pd
            INNER JOIN puesto p
                    ON p.id = pd.idPuesto
            INNER JOIN departamento d
                    ON d.id = p.departamento_id
            LEFT JOIN departamento_organizacional dorg
                    ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad
                    ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                   AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir
                    ON dir.id = ad.id_direccion
            LEFT JOIN paises pa
                    ON pa.id = d.id_pais
            $complet
            ORDER BY FIELD(pa.codigo_iso, 'mx', 'gt', 'co'), direccion_nombre, departamento_organizacional_nombre, d.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /** Lista de personas para select "Posible jefe" en candidatos. */
    public static function getListaPersonasParaJefe()
    {
        $pred = UsuarioFantasmaReporteria::sqlPredicadoExcluirUserNameSinAlias();
        $query = <<<SQL
            SELECT id, CONCAT(TRIM(COALESCE(nombres,'')), ' ', TRIM(COALESCE(apellidop,'')), ' ', TRIM(COALESCE(apellidom,''))) AS nombre
            FROM persona
            WHERE (estatus IS NULL OR estatus != 'Baja')
              AND ({$pred})
            ORDER BY nombres, apellidop, apellidom
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return is_array($r) ? $r : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /** Para organigrama: devuelve todos los departamentos (sin filtrar por gestor). */
    public static function getTodosDepartamentos()
    {
        $query = <<<SQL
            SELECT id, nombre
            FROM departamento
            WHERE COALESCE(activo, 1) = 1
            ORDER BY nombre ASC
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener departamentos.', null, $e->getMessage());
        }
    }

    /** Para Gestion de Personal: departamentos completos por pais, sin depender de privilegios por puesto. */
    public static function getTodosDepartamentosGestion()
    {
        $query = <<<SQL
            SELECT
                d.*,
                d.id_departamento_organizacional,
                COALESCE(dorg.nombre, 'Sin departamento') AS departamento_organizacional_nombre,
                COALESCE(dorg.activo, 1) AS departamento_organizacional_activo,
                COALESCE(dir.id, 0) AS id_direccion,
                COALESCE(dir.nombre, 'Sin dirección') AS direccion_nombre,
                COALESCE(dir.activo, 1) AS direccion_activo,
                pa.nombre AS nombre_pais,
                COALESCE(pa.codigo_iso, 'xx') AS codigo_iso_pais
            FROM departamento d
            LEFT JOIN departamento_organizacional dorg
                   ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad
                   ON ad.id_departamento_organizacional = d.id_departamento_organizacional
                  AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir
                   ON dir.id = ad.id_direccion
            LEFT JOIN paises pa
                   ON pa.id = d.id_pais
            WHERE COALESCE(d.activo, 1) = 1
              AND COALESCE(dorg.activo, 1) = 1
            ORDER BY FIELD(pa.codigo_iso, 'mx', 'gt', 'co'), direccion_nombre, departamento_organizacional_nombre, d.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener departamentos.', null, $e->getMessage());
        }
    }

    /** Puestos asignados a una persona (para organigrama). Si id_departamento se pasa, solo puestos de ese departamento. */
    public static function getPuestosPorPersona($id_persona, $id_departamento = 0)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona inválido.', null);
        }
        $id_departamento = (int) $id_departamento;
        $params = ['id_persona' => $id_persona];
        $filtroDepto = '';
        if ($id_departamento > 0) {
            $filtroDepto = ' AND pp.departamento_id = :id_departamento';
            $params['id_departamento'] = $id_departamento;
        }
        $query = <<<SQL
            SELECT pp.id, pp.nombre
            FROM asigna_puesto ap
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            WHERE ap.id_persona = :id_persona
              AND ap.activo = 1
            $filtroDepto
            ORDER BY pp.nombre ASC
        SQL;
        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Puestos encontrados.', $r ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener puestos.', null, $e->getMessage());
        }
    }

    public static function getPuestosActivosPersonaParaEdicion($id_persona)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona invalido.', []);
        }

        try {
            $db = new Database();
            $rows = $db->queryAll(
                "SELECT
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    pp.departamento_id AS id_departamento,
                    d.nombre AS nombre_departamento,
                    pp.nivel
                 FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                 INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                 LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = pp.departamento_id
                 WHERE ap.id_persona = :id_persona
                   AND COALESCE(ap.activo, 1) = 1
                 ORDER BY pp.nivel DESC, ap.id ASC",
                ['id_persona' => $id_persona]
            );
            return self::resultado(true, 'Puestos activos encontrados.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener puestos activos.', [], $e->getMessage());
        }
    }

    /** Nombre del puesto por ID (para organigrama por cargo). */
    public static function getNombrePuesto($id_puesto)
    {
        $id_puesto = (int) $id_puesto;
        if ($id_puesto <= 0) return null;
        $query = "SELECT nombre FROM puesto WHERE id = :id";
        try {
            $db = new Database();
            $r = $db->queryAll($query, ['id' => $id_puesto]);
            return isset($r[0]['nombre']) ? $r[0]['nombre'] : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    ////////////////////////////////////////
    public static function getConsultaDepartamentoGestorOrganigrama($departamento)
    {

        $query = <<<SQL
           SELECT *
            FROM  puesto p
            WHERE p.departamento_id  = $departamento
            ORDER BY p.nombre ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    ////////////////////////////////////////
    public static function getPersonasBaja()
    {
        $query = <<<SQL
            SELECT
                id,
                nombres,
                apellidop,
                apellidom,
                numero_empleado,
                estatus,
                user_name
            FROM __SPARTA_SECRET_REDACTED__.persona
            WHERE estatus = 'Baja'
            ORDER BY numero_empleado ASC
        SQL;

        try {
            $db = new Database();
            $r = $db->queryAll($query);
            return self::resultado(true, 'Personas dadas de baja encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener las personas dadas de baja.', null, $e->getMessage());
        }
    }

    /**
     * Siguiente número de empleado libre: toma el máximo entre valores puramente numéricos,
     * suma 1 y avanza hasta encontrar un valor que no exista en persona.numero_empleado.
     */
    private static function siguienteNumeroEmpleadoLibre(Database $db): string
    {
        $row = $db->queryOne(
            "SELECT COALESCE(MAX(CAST(numero_empleado AS UNSIGNED)), 0) AS mx
             FROM __SPARTA_SECRET_REDACTED__.persona
             WHERE TRIM(numero_empleado) <> ''
               AND TRIM(numero_empleado) REGEXP '^[0-9]+$'"
        );
        $next = isset($row['mx']) ? (int) $row['mx'] + 1 : 1;
        for ($i = 0; $i < 100000; $i++) {
            $candidate = (string) $next;
            $ex = $db->queryOne(
                'SELECT 1 AS ok FROM __SPARTA_SECRET_REDACTED__.persona WHERE numero_empleado = :n LIMIT 1',
                ['n' => $candidate]
            );
            if (empty($ex)) {
                return $candidate;
            }
            $next++;
        }

        return 'NEO' . strtoupper(bin2hex(random_bytes(4)));
    }

    public static function insertPersona($data)
    {
        // 🔹 Escapamos valores
        $nombres = addslashes((string) ($data['nombres'] ?? ''));
        $segundo_nombre = addslashes((string) ($data['segundo_nombre'] ?? ''));
        $apellidop = addslashes((string) ($data['apellidop'] ?? ''));
        $apellidom = addslashes((string) ($data['apellidom'] ?? ''));
        // Si no viene número de empleado, se genera en BD (max numérico + 1, sin colisiones).
        $numeroEmpleadoEntrada = trim((string) ($data['numero_empleado'] ?? ''));
        $autoNumeroEmpleado = $numeroEmpleadoEntrada === ''
            || strcasecmp($numeroEmpleadoEntrada, 'PEND') === 0
            || strcasecmp($numeroEmpleadoEntrada, 'PENDIENTE') === 0;
        $correo = addslashes((string) ($data['correo'] ?? ''));
        $telefono_uno = addslashes((string) ($data['telefono'] ?? $data['telefono_uno'] ?? ''));
        $telefono_dos = addslashes((string) ($data['telefono_dos'] ?? ''));
        $estatus = addslashes((string) ($data['estatus'] ?? 'Activo'));
        $id_puesto = (int) ($data['id_puesto'] ?? 0);
        $id_departamento = (int) ($data['departamento_id'] ?? $data['id_departamento'] ?? 0);
        $vacante_existente_id = (int) ($data['vacante_existente_id'] ?? 0);
        $user_name = addslashes((string) ($data['usuario'] ?? ''));
        $password = addslashes((string) ($data['contrasena'] ?? ''));
        $fecha_ingreso = !empty($data['fecha_ingreso']) ? addslashes($data['fecha_ingreso']) : null;
        $id_pais = (int) ($data['id_pais'] ?? 1);
        if ($id_pais <= 0) {
            $id_pais = 1;
        }
        // FK a divisiones_administrativas.id por nivel (null/""/0 del JSON → NULL SQL, no 0).
        $id_div_nivel1 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel1'] ?? null);
        $id_div_nivel2 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel2'] ?? null);
        $id_div_nivel3 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel3'] ?? null);
        $curp_norm = strtoupper(preg_replace('/\s+/', '', (string) ($data['curp'] ?? '')));
        $curp_norm = mb_substr($curp_norm, 0, 18);
        $curp_sql = $curp_norm !== '' ? "'" . addslashes($curp_norm) . "'" : 'NULL';
        $dom_ext = trim((string) ($data['domicilio_num_exterior'] ?? ''));
        $dom_int = trim((string) ($data['domicilio_num_interior'] ?? ''));
        $cp = trim((string) ($data['codigo_postal'] ?? ''));
        $dom_ext_sql = $dom_ext !== '' ? "'" . addslashes($dom_ext) . "'" : 'NULL';
        $dom_int_sql = $dom_int !== '' ? "'" . addslashes($dom_int) . "'" : 'NULL';
        $cp_sql = $cp !== '' ? "'" . addslashes($cp) . "'" : 'NULL';

        try {
            $db = new Database();
            $vacanteSeleccionada = null;
            if ($vacante_existente_id > 0) {
                self::asegurarTablaVacantesPersonal($db);
                self::asegurarAsignaJefeSoportaVacante($db);
                $vacanteSeleccionada = $db->queryOne("
                    SELECT id, id_jefe, id_departamento, id_puesto
                    FROM __SPARTA_SECRET_REDACTED__.vacantes_personal
                    WHERE id = :id
                      AND UPPER(TRIM(estatus)) = 'ACTIVA'
                    LIMIT 1
                ", ['id' => $vacante_existente_id]);

                if (!$vacanteSeleccionada) {
                    return self::resultado(false, 'La vacante seleccionada ya no esta disponible.');
                }
                if ((int)$vacanteSeleccionada['id_puesto'] !== $id_puesto || ($id_departamento > 0 && (int)$vacanteSeleccionada['id_departamento'] !== $id_departamento)) {
                    return self::resultado(false, 'La vacante seleccionada no corresponde al departamento y puesto elegidos.');
                }
            }

            if ($autoNumeroEmpleado) {
                $numero_raw = self::siguienteNumeroEmpleadoLibre($db);
            } else {
                $numero_raw = $numeroEmpleadoEntrada;
            }
            $numero_empleado = addslashes($numero_raw);

            if ($cp === '' && $id_div_nivel3 !== 'NULL') {
                $crow = $db->queryOne(
                    'SELECT NULLIF(TRIM(codigo_interno), \'\') AS cp FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas WHERE id = :id AND activo = 1 LIMIT 1',
                    ['id' => (int) $id_div_nivel3]
                );
                if (!empty($crow['cp'])) {
                    $cp = trim((string) $crow['cp']);
                    $cp_sql = "'" . addslashes($cp) . "'";
                }
            }

            $dom_calle = self::domicilioCalleTextoParaGuardar($db, $data);
            $dom_calle_sql = $dom_calle !== '' ? "'" . addslashes($dom_calle) . "'" : 'NULL';

            $fecha_ingreso_sql = $fecha_ingreso !== null ? "'$fecha_ingreso'" : 'NULL';

            $tz = new \DateTimeZone('America/Mexico_City');
            $fechaRegistro = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
            $fechaRegistro = addslashes($fechaRegistro);

            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.persona
            (nombres, segundo_nombre, apellidop, apellidom, curp, numero_empleado, correo, telefono_uno, telefono_dos, estatus, user_name, password, fecha_ingreso, fecha_registro, id_pais, id_div_nivel1, id_div_nivel2, id_div_nivel3, domicilio_calle_texto, domicilio_num_exterior, domicilio_num_interior, codigo_postal)
            VALUES
            ('$nombres', '$segundo_nombre', '$apellidop', '$apellidom', $curp_sql, '$numero_empleado', '$correo', '$telefono_uno', '$telefono_dos', '$estatus', '$user_name', '$password', $fecha_ingreso_sql, '$fechaRegistro', $id_pais, $id_div_nivel1, $id_div_nivel2, $id_div_nivel3, $dom_calle_sql, $dom_ext_sql, $dom_int_sql, $cp_sql)
        ");


            // 2️⃣ Obtenemos el ID insertado con queryOne()
            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            if (is_array($result)) {
                $result['numero_empleado'] = $numero_raw;
            }

            $id_persona = isset($result['id']) ? intval($result['id']) : null;

            // Si no tiene jefe, él mismo será su jefe
            $jefeRaw = trim((string)($data['id_jefe'] ?? ''));
            $id_vacante_jefe = 0;
            if (preg_match('/^vacante:(\d+)$/', $jefeRaw, $m)) {
                self::asegurarAsignaJefeSoportaVacante($db);
                $id_vacante_jefe = (int)$m[1];
                $id_jefe = null;
            } else {
                $id_jefe = $jefeRaw !== '' ? (int)$jefeRaw : null;
            }
            if ($vacanteSeleccionada && !empty($vacanteSeleccionada['id_jefe'])) {
                $id_jefe = (int)$vacanteSeleccionada['id_jefe'];
                $id_vacante_jefe = 0;
            }

            if ($result)
            {
                $fechaAsignacionCdmx = self::fechaHoraCdmx();
                $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto
                        (id, id_persona, id_puesto, fecha_asignacion, activo)
                    VALUES
                        (DEFAULT, $id_persona, $id_puesto, '$fechaAsignacionCdmx', 1)
                ");

                self::aplicarPermisosPuestoAPersonaConDb($db, (int) $id_persona, (int) $id_puesto);

                $puestosDespuesAlta = self::puestosActivosTrayectoria($db, (int)$id_persona);
                self::registrarCambiosTrayectoriaPuestos(
                    $db,
                    (int)$id_persona,
                    [],
                    $puestosDespuesAlta,
                    isset($data['usuario_edita']) ? (int)$data['usuario_edita'] : (int)($_SESSION['usuario_id'] ?? 0),
                    'alta_gestion_personal'
                );

                if ($id_vacante_jefe > 0) {
                    $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_jefe
                        (id, id_persona, id_jefe, id_vacante_jefe, fecha_inicio, fecha_fin)
                    VALUES
                        (DEFAULT, $id_persona, NULL, $id_vacante_jefe, NOW(), NOW())
                ");
                } else {
                    $idJefeSql = $id_jefe !== null ? (int)$id_jefe : 'NULL';
                    $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_jefe
                        (id, id_persona, id_jefe, fecha_inicio, fecha_fin)
                    VALUES
                        (DEFAULT, $id_persona, $idJefeSql, NOW(), NOW())
                ");
                }

                // 4️⃣ Auto-registrar en despachos si el puesto lo requiere
                $id_celula_despacho = self::resolverCelulaDespacho($db, (int)$id_puesto);
                if ($id_celula_despacho !== null) {
                    $existeDespacho = $db->queryOne(
                        "SELECT id FROM despachos WHERE id_persona = :idp AND estatus = 'Activo' LIMIT 1",
                        ['idp' => $id_persona]
                    );
                    if (!$existeDespacho) {
                        $db->queryOne(
                            "INSERT INTO despachos (id_persona, estatus, fecha_alta, id_celula) VALUES (:idp, 'Activo', NOW(), :cel)",
                            ['idp' => $id_persona, 'cel' => $id_celula_despacho]
                        );
                    }
                }

                // 5️⃣ Asignar legión si se marcó el checkbox
                if (isset($data['asignar_legion']) && $data['asignar_legion'] && isset($data['id_legion']) && $data['id_legion']) {
                    $id_legion = (int)$data['id_legion'];

                    // Desactivar cualquier legión activa previa para esta persona
                    $db->queryOne("
                        UPDATE __SPARTA_SECRET_REDACTED__.asigna_legion
                        SET activo = 0, fecha_fin = NOW()
                        WHERE id_persona = $id_persona AND activo = 1
                    ");

                    // Insertar la nueva asignación de legión
                    $db->queryOne("
                        INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_legion
                            (id, id_persona, id_legion, fecha_asignacion, activo)
                        VALUES
                            (DEFAULT, $id_persona, $id_legion, NOW(), 1)
                    ");
                }

                if ($vacanteSeleccionada) {
                    $db->CRUD("
                        UPDATE __SPARTA_SECRET_REDACTED__.vacantes_personal
                        SET estatus = 'Ocupada',
                            id_persona_cubre = :id_persona,
                            fecha_cierre = NOW()
                        WHERE id = :id_vacante
                          AND UPPER(TRIM(estatus)) = 'ACTIVA'
                    ", [
                        'id_persona' => $id_persona,
                        'id_vacante' => $vacante_existente_id,
                    ]);

                    $db->CRUD("
                        UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe
                        SET id_jefe = :id_persona,
                            id_vacante_jefe = NULL
                        WHERE id_vacante_jefe = :id_vacante
                    ", [
                        'id_persona' => $id_persona,
                        'id_vacante' => $vacante_existente_id,
                    ]);
                }
            }

            return self::resultado(true, 'Persona insertada correctamente.', $result);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al insertar persona.', null, $e->getMessage());
        }
    }

    /** ID del módulo Onboarding en modulos_web (solo acceso a menú Onboarding para nuevos incorporados). */
    const MODULO_ONBOARDING_ID = 44;

    /**
     * Asigna únicamente el módulo Onboarding al usuario (para nuevos colaboradores desde candidatos).
     * Elimina cualquier otro módulo asignado y deja solo Onboarding.
     *
     * @param int $id_persona ID de persona en __SPARTA_SECRET_REDACTED__.persona
     * @return array { success, mensaje }
     */
    public static function asignarSoloModuloOnboarding($id_persona)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD('DELETE FROM asigna_modulo_web WHERE usuario_id = :uid', ['uid' => $id_persona]);
            $db->CRUD(
                'INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id) VALUES (:uid, :mid)',
                ['uid' => $id_persona, 'mid' => self::MODULO_ONBOARDING_ID]
            );
            return self::resultado(true, 'Módulo Onboarding asignado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al asignar módulo Onboarding.', null, $e->getMessage());
        }
    }

    public static function guardarAusencia($data)
    {
        $db = new Database();

        $id_ausencia  = isset($data['idAusencia']) && $data['idAusencia'] !== ''
            ? (int)$data['idAusencia']
            : null;

        $id_persona   = (int)$data['idPersona'];
        $id_razon     = (int)$data['idRazon'];
        $descripcion  = addslashes($data['descripcion'] ?? '');
        $fecha_inicio = addslashes($data['fechaInicio']);
        $fecha_fin    = addslashes($data['fechaFin']);
        $creado_por   = addslashes($_SESSION['usuario'] ?? 'sistema');

        try {

            // 🔄 UPDATE
            if ($id_ausencia) {

                $db->queryOne("
                UPDATE __SPARTA_SECRET_REDACTED__.ausencia
                SET
                    id_razon     = $id_razon,
                    descripcion  = '$descripcion',
                    fecha_inicio = '$fecha_inicio',
                    fecha_fin    = '$fecha_fin'
                WHERE id = $id_ausencia
                LIMIT 1
            ");

                return self::resultado(
                    true,
                    'Ausencia actualizada correctamente.',
                    ['id' => $id_ausencia]
                );
            }

            // ➕ INSERT
            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.ausencia
                (id_persona, id_razon, descripcion, fecha_inicio, fecha_fin, creado_por, activo)
            VALUES
                ($id_persona, $id_razon, '$descripcion', '$fecha_inicio', '$fecha_fin', '$creado_por', 1)
        ");

            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");

            return self::resultado(
                true,
                'Ausencia registrada correctamente.',
                $result
            );

        } catch (\Exception $e) {
            return self::resultado(
                false,
                'Error al guardar ausencia.',
                null,
                $e->getMessage()
            );
        }
    }



    public static function UpdatePersona($data)
    {
        $id_persona      = (int)$data['id'];
        $numero_empleado = trim((string)($data['numero_empleado'] ?? ''));
        $nombres         = addslashes($data['nombres']);
        $segundo_nombre  = addslashes($data['segundo_nombre'] ?? '');
        $apellidop       = addslashes($data['apellidop']);
        $apellidom       = addslashes($data['apellidom']);
        $correo          = addslashes($data['correo'] ?? '');
        $telefono_uno    = addslashes($data['telefono_uno'] ?? $data['telefono'] ?? '');
        $jefeRaw         = trim((string)($data['jefe_id'] ?? ''));
        $preservarJefeActual = $jefeRaw === '';
        $id_jefe = null;
        $id_vacante_jefe = 0;
        if (preg_match('/^vacante:(\d+)$/', $jefeRaw, $m)) {
            $id_jefe = null;
            $id_vacante_jefe = (int)$m[1];
        } elseif ($jefeRaw !== '') {
            $id_jefe = (int)$jefeRaw;
        }
        $id_puesto       = (int)$data['puesto_id'];
        $puestosAdicionalesEntrada = $data['puestos_adicionales'] ?? null;
        $sincronizarPuestosDesdeLista = is_array($puestosAdicionalesEntrada);
        $puestosEliminadosEntrada = is_array($data['puestos_eliminados'] ?? null) ? $data['puestos_eliminados'] : [];
        $idPuestoPrincipalOriginal = (int)($data['puesto_principal_original'] ?? 0);
        $idPuestoPrincipalEliminado = 0;
        foreach ($puestosEliminadosEntrada as $puestoEliminadoEntrada) {
            if (!empty($puestoEliminadoEntrada['era_principal'])) {
                $idPuestoPrincipalEliminado = (int)($puestoEliminadoEntrada['id_puesto'] ?? 0);
                break;
            }
        }
        $idsPuestosEntrada = [];
        if (is_array($puestosAdicionalesEntrada)) {
            foreach ($puestosAdicionalesEntrada as $puestoEntrada) {
                $puestoEntradaId = (int)($puestoEntrada['id_puesto'] ?? 0);
                if ($puestoEntradaId > 0) {
                    $idsPuestosEntrada[$puestoEntradaId] = true;
                }
            }
        }
        $idsPuestosEntrada = array_keys($idsPuestosEntrada);
        if (!empty($idsPuestosEntrada)) {
            $id_puesto = (int)$idsPuestosEntrada[0];
        }
        $user_name       = addslashes($data['usuario']);
        $password        = addslashes($data['contrasena']);
        $id_div_nivel1 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel1'] ?? null);
        $id_div_nivel2 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel2'] ?? null);
        $id_div_nivel3 = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel3'] ?? null);
        $curp_norm = strtoupper(preg_replace('/\s+/', '', (string) ($data['curp'] ?? '')));
        $curp_norm = mb_substr($curp_norm, 0, 18);
        $curp_sql = $curp_norm !== '' ? "'" . addslashes($curp_norm) . "'" : 'NULL';
        $dom_ext = trim((string) ($data['domicilio_num_exterior'] ?? ''));
        $dom_int = trim((string) ($data['domicilio_num_interior'] ?? ''));
        $cp = trim((string) ($data['codigo_postal'] ?? ''));
        $dom_ext_sql = $dom_ext !== '' ? "'" . addslashes($dom_ext) . "'" : 'NULL';
        $dom_int_sql = $dom_int !== '' ? "'" . addslashes($dom_int) . "'" : 'NULL';
        $cp_sql = $cp !== '' ? "'" . addslashes($cp) . "'" : 'NULL';
        $transaccionActiva = false;

        try {
            $db = new Database();
            self::asegurarAsignaJefeSoportaVacante($db);
            self::asegurarTablaVacantesPersonal($db);
            self::asegurarTablaTrayectoriaPuesto($db);

            if ($numero_empleado === '') {
                $actualNumero = $db->queryOne(
                    "SELECT numero_empleado FROM __SPARTA_SECRET_REDACTED__.persona WHERE id = :id LIMIT 1",
                    ['id' => $id_persona]
                );
                $numero_empleado = trim((string)($actualNumero['numero_empleado'] ?? ''));
            }

            if ($numero_empleado !== '') {
                $duplicadoNumero = $db->queryOne(
                    "SELECT id FROM __SPARTA_SECRET_REDACTED__.persona WHERE numero_empleado = :numero_empleado AND id <> :id LIMIT 1",
                    ['numero_empleado' => $numero_empleado, 'id' => $id_persona]
                );
                if ($duplicadoNumero) {
                    return self::resultado(false, 'El numero de empleado ya existe en otro usuario.');
                }
            }
            $numero_empleado_sql = "'" . addslashes($numero_empleado) . "'";

            $puestoAnterior = $db->queryOne("
                SELECT
                    ap.id_puesto,
                    pp.nombre AS nombre_puesto,
                    pp.departamento_id,
                    pp.nivel,
                    aj.id_jefe AS id_jefe_anterior,
                    aj.id_vacante_jefe AS id_vacante_jefe_anterior
                FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN (
                    SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                    FROM __SPARTA_SECRET_REDACTED__.asigna_jefe a
                    INNER JOIN (
                        SELECT id_persona, MAX(id) AS mid
                        FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                        GROUP BY id_persona
                    ) m ON m.id_persona = a.id_persona AND m.mid = a.id
                ) aj ON aj.id_persona = ap.id_persona
                WHERE ap.id_persona = :id_persona
                  AND COALESCE(ap.activo, 1) = 1
                ORDER BY pp.nivel DESC, ap.id ASC
                LIMIT 1
            ", ['id_persona' => $id_persona]);

            $puestoNuevo = $id_puesto > 0 ? $db->queryOne("
                SELECT id, nombre AS nombre_puesto, departamento_id, nivel
                FROM __SPARTA_SECRET_REDACTED__.puesto
                WHERE id = :id_puesto
                LIMIT 1
            ", ['id_puesto' => $id_puesto]) : null;

            $principalFueEliminado = $idPuestoPrincipalEliminado > 0
                && !in_array($idPuestoPrincipalEliminado, array_map('intval', $idsPuestosEntrada), true);
            $principalCambio = $idPuestoPrincipalOriginal > 0
                && $id_puesto > 0
                && $idPuestoPrincipalOriginal !== (int)$id_puesto;
            $idPuestoPrincipalAnterior = $principalFueEliminado
                ? $idPuestoPrincipalEliminado
                : ($principalCambio ? $idPuestoPrincipalOriginal : 0);
            if ($idPuestoPrincipalAnterior > 0) {
                $puestoPrincipalEliminado = $db->queryOne("
                    SELECT
                        ap.id_puesto,
                        pp.nombre AS nombre_puesto,
                        pp.departamento_id,
                        pp.nivel,
                        aj.id_jefe AS id_jefe_anterior,
                        aj.id_vacante_jefe AS id_vacante_jefe_anterior
                    FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                    INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                    LEFT JOIN (
                        SELECT a.id_persona, a.id_jefe, a.id_vacante_jefe
                        FROM __SPARTA_SECRET_REDACTED__.asigna_jefe a
                        INNER JOIN (
                            SELECT id_persona, MAX(id) AS mid
                            FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                            GROUP BY id_persona
                        ) m ON m.id_persona = a.id_persona AND m.mid = a.id
                    ) aj ON aj.id_persona = ap.id_persona
                    WHERE ap.id_persona = :id_persona
                      AND ap.id_puesto = :id_puesto
                      AND COALESCE(ap.activo, 1) = 1
                    ORDER BY ap.id DESC
                    LIMIT 1
                ", [
                    'id_persona' => $id_persona,
                    'id_puesto' => $idPuestoPrincipalAnterior,
                ]);
                if ($puestoPrincipalEliminado) {
                    $puestoAnterior = $puestoPrincipalEliminado;
                }
            }

            $subordinadosPuestoAnterior = [];
            $esDegradacionConHueco = false;
            if ($puestoAnterior && $principalFueEliminado) {
                $esDegradacionConHueco = true;
            } elseif ((!$sincronizarPuestosDesdeLista || $principalCambio) && $puestoAnterior && $puestoNuevo && (int)$puestoAnterior['id_puesto'] !== (int)$id_puesto) {
                $nivelAnterior = (int)($puestoAnterior['nivel'] ?? 0);
                $nivelNuevo = (int)($puestoNuevo['nivel'] ?? 0);
                $esDegradacionConHueco = $nivelAnterior > $nivelNuevo;
            }
            if ($esDegradacionConHueco) {
                $subordinadosPuestoAnterior = $db->queryAll("
                    SELECT p.id, CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo
                    FROM __SPARTA_SECRET_REDACTED__.asigna_jefe aj
                    INNER JOIN __SPARTA_SECRET_REDACTED__.persona p ON p.id = aj.id_persona
                    WHERE aj.id_jefe = :id_persona
                      AND p.estatus != 'Baja'
                    ORDER BY p.nombres ASC, p.apellidop ASC
                ", ['id_persona' => $id_persona]);
            }

            $resolverPuestoAnterior = trim((string)($data['resolver_puesto_anterior'] ?? ''));
            $idSustitutoPuestoAnterior = (int)($data['id_sustituto_puesto_anterior'] ?? 0);
            if ($esDegradacionConHueco && !empty($subordinadosPuestoAnterior) && !in_array($resolverPuestoAnterior, ['vacante', 'sustituto'], true)) {
                $sustitutos = $db->queryAll("
                    SELECT
                        p.id,
                        CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                        pp.nombre AS nombre_puesto,
                        MAX(pp.nivel) AS nivel_orden
                    FROM __SPARTA_SECRET_REDACTED__.persona p
                    INNER JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                    INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                    WHERE p.estatus != 'Baja'
                      AND p.id <> :id_persona
                      AND pp.departamento_id = :id_departamento
                    GROUP BY p.id, nombre_completo, pp.nombre
                    ORDER BY nivel_orden DESC, nombre_completo ASC
                ", [
                    'id_persona' => $id_persona,
                    'id_departamento' => (int)$puestoAnterior['departamento_id'],
                ]);
                return self::resultado(false, 'El puesto anterior tiene subordinados. Indique si desea crear una vacante o asignar un sustituto antes de continuar.', [
                    'requiere_resolucion_puesto_anterior' => true,
                    'puesto_anterior' => $puestoAnterior,
                    'puesto_nuevo' => $puestoNuevo,
                    'subordinados_count' => count($subordinadosPuestoAnterior),
                    'sustitutos' => $sustitutos,
                ]);
            }

            if ($esDegradacionConHueco && !empty($subordinadosPuestoAnterior) && $resolverPuestoAnterior === 'sustituto') {
                if ($idSustitutoPuestoAnterior <= 0 || $idSustitutoPuestoAnterior === $id_persona) {
                    return self::resultado(false, 'Seleccione una persona valida para sustituir el puesto anterior.');
                }
                $sustitutoValido = $db->queryOne("
                    SELECT p.id
                    FROM __SPARTA_SECRET_REDACTED__.persona p
                    INNER JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                    INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                    WHERE p.id = :id_sustituto
                      AND p.estatus != 'Baja'
                      AND pp.departamento_id = :id_departamento
                    LIMIT 1
                ", [
                    'id_sustituto' => $idSustitutoPuestoAnterior,
                    'id_departamento' => (int)$puestoAnterior['departamento_id'],
                ]);
                if (!$sustitutoValido) {
                    return self::resultado(false, 'El sustituto seleccionado no pertenece al departamento del puesto anterior o no esta activo.');
                }
            }

            $puestosTrayectoriaAntes = self::puestosActivosTrayectoria($db, $id_persona);
            $db->beginTransaction();
            $transaccionActiva = true;

            if ($cp === '' && $id_div_nivel3 !== 'NULL') {
                $crow = $db->queryOne(
                    'SELECT NULLIF(TRIM(codigo_interno), \'\') AS cp FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas WHERE id = :id AND activo = 1 LIMIT 1',
                    ['id' => (int) $id_div_nivel3]
                );
                if (!empty($crow['cp'])) {
                    $cp = trim((string) $crow['cp']);
                    $cp_sql = "'" . addslashes($cp) . "'";
                }
            }

            $dom_calle = self::domicilioCalleTextoParaGuardar($db, $data);
            $dom_calle_sql = $dom_calle !== '' ? "'" . addslashes($dom_calle) . "'" : 'NULL';

            // 1️⃣ UPDATE PERSONA
            $db->queryOne("
            UPDATE __SPARTA_SECRET_REDACTED__.persona
            SET
                numero_empleado = $numero_empleado_sql,
                nombres       = '$nombres',
                segundo_nombre = '$segundo_nombre',
                apellidop     = '$apellidop',
                apellidom     = '$apellidom',
                curp          = $curp_sql,
                correo        = '$correo',
                telefono_uno  = '$telefono_uno',
                user_name     = '$user_name',
                password      = '$password',
                id_div_nivel1  = $id_div_nivel1,
                id_div_nivel2  = $id_div_nivel2,
                id_div_nivel3  = $id_div_nivel3,
                domicilio_calle_texto = $dom_calle_sql,
                domicilio_num_exterior = $dom_ext_sql,
                domicilio_num_interior = $dom_int_sql,
                codigo_postal = $cp_sql
            WHERE id = $id_persona
        ");

            // 2️⃣ ASIGNA JEFE (si existe UPDATE, si no INSERT)
            if (!$preservarJefeActual) {
                $idJefeSql = ($id_jefe !== null && (int)$id_jefe > 0) ? (string)(int)$id_jefe : 'NULL';

                $existeJefe = $db->queryOne("
            SELECT id
            FROM asigna_jefe
            WHERE id_persona = $id_persona
            LIMIT 1
        ");

                if ($existeJefe) {
                    if ($id_vacante_jefe > 0) {
                        $db->queryOne("
                    UPDATE asigna_jefe
                    SET id_jefe = NULL,
                        id_vacante_jefe = $id_vacante_jefe
                    WHERE id_persona = $id_persona
                ");
                    } else {
                        $db->queryOne("
                    UPDATE asigna_jefe
                    SET id_jefe = $idJefeSql,
                        id_vacante_jefe = NULL
                    WHERE id_persona = $id_persona
                ");
                    }
                } else {
                    if ($id_vacante_jefe > 0) {
                        $db->queryOne("
                    INSERT INTO asigna_jefe (id_persona, id_jefe, id_vacante_jefe)
                    VALUES ($id_persona, NULL, $id_vacante_jefe)
                ");
                    } else {
                        $db->queryOne("
                    INSERT INTO asigna_jefe (id_persona, id_jefe, id_vacante_jefe)
                    VALUES ($id_persona, $idJefeSql, NULL)
                ");
                    }
                }
            }

            // 3️⃣ ASIGNA PUESTO(S) - Manejo de múltiples puestos
            // Si viene el array puestos_adicionales, usamos ese; si no, usamos el puesto_id tradicional
            $idsPuestosGuardar = [];

            if ($sincronizarPuestosDesdeLista) {
                foreach ($idsPuestosEntrada as $puestoId) {
                    $idsPuestosGuardar[(int)$puestoId] = true;
                }
            } elseif ($id_puesto > 0) {
                $idsPuestosGuardar[$id_puesto] = true;
            }

            $idsPuestosGuardar = array_keys($idsPuestosGuardar);
            if (!$sincronizarPuestosDesdeLista && empty($idsPuestosGuardar)) {
                throw new \Exception('Debe quedar al menos un puesto asignado.');
            }
            $fechaAsignacionCdmx = self::fechaHoraCdmx();

            $idsPuestosActivosAntes = [];
            foreach ($puestosTrayectoriaAntes as $puestoActivoAntes) {
                $idPuestoActivoAntes = (int)($puestoActivoAntes['id_puesto'] ?? 0);
                if ($idPuestoActivoAntes > 0) {
                    $idsPuestosActivosAntes[$idPuestoActivoAntes] = true;
                }
            }

            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.asigna_puesto
                 SET activo = 0
                 WHERE id_persona = :id_persona",
                ['id_persona' => $id_persona]
            );

            foreach ($idsPuestosGuardar as $puestoId) {
                $asignacionExistente = $db->queryOne(
                    "SELECT id
                     FROM __SPARTA_SECRET_REDACTED__.asigna_puesto
                     WHERE id_persona = :id_persona
                       AND id_puesto = :id_puesto
                     ORDER BY id DESC
                     LIMIT 1",
                    ['id_persona' => $id_persona, 'id_puesto' => $puestoId]
                );

                if ($asignacionExistente) {
                    $actualizarFechaAsignacion = !isset($idsPuestosActivosAntes[(int)$puestoId]);
                    $db->CRUD(
                        "UPDATE __SPARTA_SECRET_REDACTED__.asigna_puesto
                         SET activo = 1" . ($actualizarFechaAsignacion ? ", fecha_asignacion = :fecha_asignacion" : "") . "
                         WHERE id = :id",
                        $actualizarFechaAsignacion
                            ? ['id' => (int)$asignacionExistente['id'], 'fecha_asignacion' => $fechaAsignacionCdmx]
                            : ['id' => (int)$asignacionExistente['id']]
                    );
                } else {
                    $db->CRUD(
                        "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto (id_persona, id_puesto, fecha_asignacion, activo)
                         VALUES (:id_persona, :id_puesto, :fecha_asignacion, 1)",
                        ['id_persona' => $id_persona, 'id_puesto' => $puestoId, 'fecha_asignacion' => $fechaAsignacionCdmx]
                    );
                }
            }

            // 4️⃣ ASIGNA LEGIÓN
            foreach ($idsPuestosGuardar as $puestoIdAutoPermiso) {
                self::aplicarPermisosPuestoAPersonaConDb($db, (int) $id_persona, (int) $puestoIdAutoPermiso);
            }

            if ($esDegradacionConHueco && !empty($subordinadosPuestoAnterior) && in_array($resolverPuestoAnterior, ['vacante', 'sustituto'], true)) {
                $idsSubordinados = array_values(array_map(function ($row) {
                    return (int)$row['id'];
                }, $subordinadosPuestoAnterior));
                $phSubordinados = [];
                $paramsSubordinados = ['id_persona' => $id_persona];
                foreach ($idsSubordinados as $idxSub => $idSubordinado) {
                    $keySub = 'sub_' . $idxSub;
                    $phSubordinados[] = ':' . $keySub;
                    $paramsSubordinados[$keySub] = $idSubordinado;
                }

                if (!empty($phSubordinados)) {
                    if ($resolverPuestoAnterior === 'vacante') {
                        $idJefeVacanteAnterior = !empty($puestoAnterior['id_jefe_anterior'])
                            ? (int)$puestoAnterior['id_jefe_anterior']
                            : null;
                        $db->CRUD("
                            INSERT INTO __SPARTA_SECRET_REDACTED__.vacantes_personal
                                (id_departamento, id_puesto, id_jefe, id_persona_baja, origen, estatus, creado_por)
                            VALUES
                                (:id_departamento, :id_puesto, :id_jefe, NULL, 'degradacion', 'Activa', :creado_por)
                        ", [
                            'id_departamento' => (int)$puestoAnterior['departamento_id'],
                            'id_puesto' => (int)$puestoAnterior['id_puesto'],
                            'id_jefe' => $idJefeVacanteAnterior,
                            'creado_por' => !empty($data['usuario_edita']) ? (int)$data['usuario_edita'] : null,
                        ]);
                        $idVacantePuestoAnterior = $db->lastInsertId();

                        $db->CRUD("
                            UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe
                            SET id_jefe = NULL,
                                id_vacante_jefe = :id_vacante_jefe
                            WHERE id_jefe = :id_persona
                              AND id_persona IN (" . implode(',', $phSubordinados) . ")
                        ", array_merge($paramsSubordinados, [
                            'id_vacante_jefe' => $idVacantePuestoAnterior,
                        ]));
                    } else {
                        if (in_array($idSustitutoPuestoAnterior, $idsSubordinados, true)) {
                            if (!empty($puestoAnterior['id_vacante_jefe_anterior'])) {
                                $db->CRUD("
                                    UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe
                                    SET id_jefe = NULL,
                                        id_vacante_jefe = :id_vacante_jefe
                                    WHERE id_persona = :id_sustituto
                                ", [
                                    'id_vacante_jefe' => (int)$puestoAnterior['id_vacante_jefe_anterior'],
                                    'id_sustituto' => $idSustitutoPuestoAnterior,
                                ]);
                            } else {
                                $db->CRUD("
                                    UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe
                                    SET id_jefe = :id_jefe_anterior,
                                        id_vacante_jefe = NULL
                                    WHERE id_persona = :id_sustituto
                                ", [
                                    'id_jefe_anterior' => !empty($puestoAnterior['id_jefe_anterior']) ? (int)$puestoAnterior['id_jefe_anterior'] : null,
                                    'id_sustituto' => $idSustitutoPuestoAnterior,
                                ]);
                            }
                        }

                        $idsParaSustituto = array_values(array_filter($idsSubordinados, function ($idSubordinado) use ($idSustitutoPuestoAnterior) {
                            return (int)$idSubordinado !== (int)$idSustitutoPuestoAnterior;
                        }));
                        if (!empty($idsParaSustituto)) {
                            $phSustituto = [];
                            $paramsSustituto = [
                                'id_persona' => $id_persona,
                                'id_sustituto' => $idSustitutoPuestoAnterior,
                            ];
                            foreach ($idsParaSustituto as $idxSustituto => $idSubordinado) {
                                $keySustituto = 'sust_' . $idxSustituto;
                                $phSustituto[] = ':' . $keySustituto;
                                $paramsSustituto[$keySustituto] = (int)$idSubordinado;
                            }
                            $db->CRUD("
                                UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe
                                SET id_jefe = :id_sustituto,
                                    id_vacante_jefe = NULL
                                WHERE id_jefe = :id_persona
                                  AND id_persona IN (" . implode(',', $phSustituto) . ")
                            ", $paramsSustituto);
                        }
                    }
                }
            }

            if (empty($data['_preservar_legion'])) {
                $asignarLegion = isset($data['asignar_legion']) && $data['asignar_legion'];
                $id_legion = isset($data['id_legion']) && $data['id_legion'] !== '' && $data['id_legion'] !== null
                    ? (int)$data['id_legion']
                    : null;

                $db->queryOne("
                    UPDATE __SPARTA_SECRET_REDACTED__.asigna_legion
                    SET activo = 0, fecha_fin = NOW()
                    WHERE id_persona = $id_persona AND activo = 1
                ");

                if ($asignarLegion && $id_legion) {
                    $db->queryOne("
                        INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_legion
                            (id, id_persona, id_legion, fecha_asignacion, activo)
                        VALUES
                            (DEFAULT, $id_persona, $id_legion, NOW(), 1)
                    ");
                }
            }

            // Auto-sincronizar despachos según los puestos actualizados
            $idCelulaDespacho = null;
            if (!empty($idsPuestosGuardar)) {
                foreach ($idsPuestosGuardar as $puestoIdDespacho) {
                    $cel = self::resolverCelulaDespacho($db, (int)$puestoIdDespacho);
                    if ($cel !== null) {
                        $idCelulaDespacho = $cel;
                        break;
                    }
                }
            }

            $existeDespachoActivo = $db->queryOne(
                "SELECT id FROM despachos WHERE id_persona = :idp AND estatus = 'Activo' LIMIT 1",
                ['idp' => $id_persona]
            );

            if ($idCelulaDespacho !== null) {
                if ($existeDespachoActivo) {
                    $db->queryOne(
                        'UPDATE despachos SET id_celula = :cel WHERE id = :id',
                        ['cel' => $idCelulaDespacho, 'id' => $existeDespachoActivo['id']]
                    );
                } else {
                    $db->queryOne(
                        "INSERT INTO despachos (id_persona, estatus, fecha_alta, id_celula) VALUES (:idp, 'Activo', NOW(), :cel)",
                        ['idp' => $id_persona, 'cel' => $idCelulaDespacho]
                    );
                }
            } elseif ($existeDespachoActivo) {
                $db->queryOne(
                    "UPDATE despachos SET estatus = 'Inactivo' WHERE id = :id",
                    ['id' => $existeDespachoActivo['id']]
                );
            }

            $puestosTrayectoriaDespues = self::puestosActivosTrayectoria($db, $id_persona);
            self::registrarCambiosTrayectoriaPuestos(
                $db,
                $id_persona,
                $puestosTrayectoriaAntes,
                $puestosTrayectoriaDespues,
                !empty($data['usuario_edita']) ? (int)$data['usuario_edita'] : (int)($_SESSION['usuario_id'] ?? 0),
                'edicion_gestion_personal'
            );

            if ($transaccionActiva && $db->inTransaction()) {
                $db->commit();
            }
            $transaccionActiva = false;

            return self::resultado(true, 'Persona actualizada correctamente.', null);

        } catch (\Exception $e) {
            if (isset($db) && $transaccionActiva && $db->inTransaction()) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al actualizar persona.', null, $e->getMessage());
        }
    }

    /**
     * Resuelve el id_celula para la tabla despachos según el nombre del puesto.
     * Devuelve 1 (Despacho), 2 (Call Center) o null si el puesto no aplica.
     *
     * Puestos cubiertas:
     *  id_celula = 1: Gestor Despacho, Supervisor Despacho
     *  id_celula = 2: Agente Call Center, Supervisora Call Center
     */
    private static function resolverCelulaDespacho(Database $db, int $id_puesto): ?int
    {
        if ($id_puesto <= 0) {
            return null;
        }
        $row = $db->queryOne(
            'SELECT nombre FROM puesto WHERE id = :id LIMIT 1',
            ['id' => $id_puesto]
        );
        if (!$row) {
            return null;
        }
        $nombre = strtolower(trim((string)($row['nombre'] ?? '')));
        if (in_array($nombre, ['agente call center', 'supervisora call center'], true)) {
            return 2;
        }
        if (in_array($nombre, ['gestor despacho', 'supervisor despacho'], true)) {
            return 1;
        }
        return null;
    }

    public static function registrarBajaGestor($data)
    {
        try {
            $db = new Database();

            // 🔹 Escapamos valores
            $id_persona  = addslashes($data['id_gestor']);
            $motivo      = addslashes($data['motivo']);
            $descripcion = addslashes($data['descripcion']);
            $fecha_baja  = addslashes($data['fecha_baja']);
            $usuario_baja  = addslashes($data['usuario_baja']);
            $archivos    = $data['archivos'] ?? [];
            $modoReasignacion = $data['modo_reasignacion'] ?? 'sin_subordinados';
            $sustitutoId = !empty($data['sustituto_id']) ? (int) $data['sustituto_id'] : null;
            $subordinadosSeleccionadosRaw = $data['subordinados_seleccionados'] ?? null;
            $asignacionesJefeRaw = is_array($data['asignaciones_jefe'] ?? null) ? $data['asignaciones_jefe'] : [];
            $asignacionesJefe = [];
            $vacanteExistenteId = !empty($data['vacante_existente_id']) ? (int)$data['vacante_existente_id'] : null;

            // 0️⃣ Guard de idempotencia: solo bloquear si la persona YA está de baja actualmente.
            // No se usa baja_persona como guard porque puede tener registros históricos (bajas previas
            // antes de un reingreso), y esos casos deben permitir una nueva baja.
            $personaActual = $db->queryOne("
                SELECT estatus FROM __SPARTA_SECRET_REDACTED__.persona WHERE id = '$id_persona' LIMIT 1
            ");
            if ($personaActual && $personaActual['estatus'] === 'Baja') {
                return self::resultado(false, 'Esta persona ya se encuentra dada de baja en el sistema.');
            }

            $subordinadosActivos = $db->queryAll("
                SELECT aj.id_persona
                FROM __SPARTA_SECRET_REDACTED__.asigna_jefe aj
                INNER JOIN __SPARTA_SECRET_REDACTED__.persona p ON p.id = aj.id_persona
                WHERE aj.id_jefe = :id_persona
                  AND p.estatus != 'Baja'
            ", ['id_persona' => (int) $id_persona]);

            if (!empty($subordinadosActivos) && is_array($subordinadosSeleccionadosRaw)) {
                $seleccionados = [];
                foreach ($subordinadosSeleccionadosRaw as $idSeleccionado) {
                    $idSeleccionado = (int)$idSeleccionado;
                    if ($idSeleccionado > 0) $seleccionados[$idSeleccionado] = true;
                }
                $subordinadosActivos = array_values(array_filter($subordinadosActivos, function ($row) use ($seleccionados) {
                    return isset($seleccionados[(int)($row['id_persona'] ?? 0)]);
                }));
                if (empty($subordinadosActivos)) {
                    return self::resultado(false, 'Debe seleccionar al menos un subordinado para reasignar.');
                }
            }

            if (!empty($subordinadosActivos)) {
                if (!in_array($modoReasignacion, ['vacante', 'sustituto'], true)) {
                    return self::resultado(false, 'Debe seleccionar si los subordinados quedan como vacante o pasan a un sustituto.');
                }

                if ($modoReasignacion === 'sustituto') {
                    $idsSubordinadosValidos = [];
                    foreach ($subordinadosActivos as $rowSubordinado) {
                        $idsSubordinadosValidos[(int)$rowSubordinado['id_persona']] = true;
                    }

                    foreach ($asignacionesJefeRaw as $idSubordinado => $idJefeDestino) {
                        $idSubordinado = (int)$idSubordinado;
                        $idJefeDestino = (int)$idJefeDestino;
                        if ($idSubordinado > 0 && $idJefeDestino > 0 && isset($idsSubordinadosValidos[$idSubordinado])) {
                            $asignacionesJefe[$idSubordinado] = $idJefeDestino;
                        }
                    }

                    if (empty($asignacionesJefe) && $sustitutoId) {
                        foreach ($idsSubordinadosValidos as $idSubordinado => $_) {
                            $asignacionesJefe[$idSubordinado] = $sustitutoId;
                        }
                    }

                    foreach ($idsSubordinadosValidos as $idSubordinado => $_) {
                        if (empty($asignacionesJefe[$idSubordinado])) {
                            return self::resultado(false, 'Debe asignar un jefe destino a todas las personas seleccionadas.');
                        }
                        if ((int)$asignacionesJefe[$idSubordinado] === (int)$id_persona) {
                            return self::resultado(false, 'El jefe destino no puede ser la persona que se dara de baja.');
                        }
                    }

                    $idsJefesDestino = array_values(array_unique(array_map('intval', array_values($asignacionesJefe))));
                    $phJefes = [];
                    $paramsJefes = [];
                    foreach ($idsJefesDestino as $idxJefe => $idJefeDestino) {
                        $keyJefe = 'jefe_' . $idxJefe;
                        $phJefes[] = ':' . $keyJefe;
                        $paramsJefes[$keyJefe] = $idJefeDestino;
                    }
                    $jefesActivosRows = $db->queryAll("
                        SELECT id
                        FROM __SPARTA_SECRET_REDACTED__.persona
                        WHERE estatus != 'Baja'
                          AND id IN (" . implode(',', $phJefes) . ")
                    ", $paramsJefes);
                    $jefesActivos = [];
                    foreach ($jefesActivosRows as $rowJefe) {
                        $jefesActivos[(int)$rowJefe['id']] = true;
                    }
                    foreach ($idsJefesDestino as $idJefeDestino) {
                        if (empty($jefesActivos[$idJefeDestino])) {
                            return self::resultado(false, 'Uno de los jefes destino no esta activo o no existe.');
                        }
                    }
                }
            }

            $puestoVacante = null;
            $idJefeVacante = null;
            $vacanteDestinoId = null;
            if ($modoReasignacion === 'vacante') {
                self::asegurarTablaVacantesPersonal($db);
                self::asegurarAsignaJefeSoportaVacante($db);

                $puestoVacante = $db->queryOne("
                    SELECT ap.id_puesto, pp.departamento_id
                    FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                    INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                    WHERE ap.id_persona = :id_persona
                      AND COALESCE(ap.activo, 1) = 1
                    ORDER BY pp.nivel DESC, ap.id ASC
                    LIMIT 1
                ", ['id_persona' => (int) $id_persona]);

                if (empty($puestoVacante['id_puesto']) || empty($puestoVacante['departamento_id'])) {
                    $puestoVacante = $db->queryOne("
                        SELECT ap.id_puesto, pp.departamento_id
                        FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap
                        INNER JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                        WHERE ap.id_persona = :id_persona
                        ORDER BY COALESCE(ap.activo, 0) DESC, pp.nivel DESC, ap.id DESC
                        LIMIT 1
                    ", ['id_persona' => (int) $id_persona]);
                }

                $jefeVacante = $db->queryOne("
                    SELECT id_jefe
                    FROM __SPARTA_SECRET_REDACTED__.asigna_jefe
                    WHERE id_persona = :id_persona
                    ORDER BY id DESC
                    LIMIT 1
                ", ['id_persona' => (int)$id_persona]);
                $idJefeVacante = !empty($jefeVacante['id_jefe']) ? (int)$jefeVacante['id_jefe'] : null;

                if (empty($puestoVacante['id_puesto']) || empty($puestoVacante['departamento_id'])) {
                    return self::resultado(false, 'No se pudo crear la vacante porque la persona no tiene un puesto activo asignado.');
                }

                if (!$vacanteExistenteId) {
                    $vacanteActiva = $db->queryOne("
                        SELECT id
                        FROM __SPARTA_SECRET_REDACTED__.vacantes_personal
                        WHERE id_puesto = :id_puesto
                          AND id_departamento = :id_departamento
                          AND UPPER(TRIM(estatus)) = 'ACTIVA'
                        ORDER BY id ASC
                        LIMIT 1
                    ", [
                        'id_puesto' => (int)$puestoVacante['id_puesto'],
                        'id_departamento' => (int)$puestoVacante['departamento_id'],
                    ]);
                    if (!empty($vacanteActiva['id'])) {
                        return self::resultado(false, 'Ya existe una vacante activa para este puesto. Seleccione esa vacante antes de confirmar la baja.');
                    }
                }
            }

            $db->beginTransaction();

            // 1️⃣ Insertar la baja en baja_persona
            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.baja_persona
            (id_persona, motivo, fecha_baja, descripcion, usuario_baja)
            VALUES
            ('$id_persona', '$motivo', '$fecha_baja', '$descripcion', '$usuario_baja')
        ");

            // Obtener el ID de la baja recién creada
            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id_baja = isset($result['id']) ? intval($result['id']) : null;

            if (!$id_baja) {
                return self::resultado(false, 'No se pudo obtener el ID de la baja.');
            }

            // 2️⃣ Insertar cada archivo en carga_documento_persona
            foreach ($archivos as $archivo) {

                // Asumimos que el documento 'Documento baja' ya existe con id = 15
                $id_documento = 15;

                $archivoEsc = addslashes($archivo);

                $db->queryOne("
                INSERT INTO __SPARTA_SECRET_REDACTED__.carga_documento_persona
                (id_persona, id_documento, archivo, fecha_carga)
                VALUES
                ('$id_persona', '$id_documento', '$archivoEsc', NOW())
            ");
            }

            // 3️⃣ Actualizar estatus de la persona a 'Baja'
            $db->queryOne("
            UPDATE __SPARTA_SECRET_REDACTED__.persona
            SET estatus = 'Baja'
            WHERE id = '$id_persona'
        ");

            // 4️⃣ Inhabilitar en despachos si el gestor estaba registrado
            $db->queryOne("
            UPDATE __SPARTA_SECRET_REDACTED__.despachos
            SET estatus = 'Inactivo'
            WHERE id_persona = '$id_persona' AND estatus = 'Activo'
        ");

            if ($modoReasignacion === 'vacante' && !$vacanteExistenteId) {
                $db->CRUD("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.vacantes_personal
                        (id_departamento, id_puesto, id_jefe, id_persona_baja, origen, estatus, creado_por)
                    VALUES
                        (:id_departamento, :id_puesto, :id_jefe, :id_persona_baja, 'baja', 'Activa', :creado_por)
                ", [
                    'id_departamento' => (int)$puestoVacante['departamento_id'],
                    'id_puesto' => (int)$puestoVacante['id_puesto'],
                    'id_jefe' => $idJefeVacante,
                    'id_persona_baja' => (int)$id_persona,
                    'creado_por' => (int)$usuario_baja,
                ]);
                $vacanteDestinoId = $db->lastInsertId();
            } elseif ($modoReasignacion === 'vacante') {
                $vacanteDestinoId = (int)$vacanteExistenteId;
                $db->CRUD("
                    UPDATE __SPARTA_SECRET_REDACTED__.vacantes_personal
                    SET id_jefe = :id_jefe
                    WHERE id = :id_vacante
                ", [
                    'id_jefe' => $idJefeVacante,
                    'id_vacante' => $vacanteDestinoId,
                ]);
            }

            if (!empty($subordinadosActivos)) {
                $idsSubordinadosReasignar = array_values(array_map(function ($row) {
                    return (int)$row['id_persona'];
                }, $subordinadosActivos));
                $phSubordinados = [];
                $paramsSubordinados = ['id_persona' => (int)$id_persona];
                foreach ($idsSubordinadosReasignar as $idxSub => $idSubordinado) {
                    $keySub = 'sub_' . $idxSub;
                    $phSubordinados[] = ':' . $keySub;
                    $paramsSubordinados[$keySub] = $idSubordinado;
                }

                if ($modoReasignacion === 'sustituto') {
                    $porJefe = [];
                    foreach ($idsSubordinadosReasignar as $idSubordinado) {
                        $idJefeDestino = (int)($asignacionesJefe[$idSubordinado] ?? 0);
                        if ($idJefeDestino > 0) $porJefe[$idJefeDestino][] = $idSubordinado;
                    }
                    foreach ($porJefe as $idJefeDestino => $idsGrupo) {
                        $phGrupo = [];
                        $paramsGrupo = [
                            'id_persona' => (int)$id_persona,
                            'jefe_destino' => (int)$idJefeDestino,
                        ];
                        foreach ($idsGrupo as $idxGrupo => $idGrupoSubordinado) {
                            $keyGrupo = 'grupo_' . $idxGrupo;
                            $phGrupo[] = ':' . $keyGrupo;
                            $paramsGrupo[$keyGrupo] = (int)$idGrupoSubordinado;
                        }
                        $db->CRUD("
                            UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe
                            SET id_jefe = :jefe_destino
                            WHERE id_jefe = :id_persona
                              AND id_persona IN (" . implode(',', $phGrupo) . ")
                        ", $paramsGrupo);
                    }
                } else {
                    $db->CRUD("
                        UPDATE __SPARTA_SECRET_REDACTED__.asigna_jefe
                        SET id_jefe = NULL,
                            id_vacante_jefe = :id_vacante_jefe
                        WHERE id_jefe = :id_persona
                          AND id_persona IN (" . implode(',', $phSubordinados) . ")
                    ", array_merge($paramsSubordinados, [
                        'id_vacante_jefe' => $vacanteDestinoId ?: null,
                    ]));
                }
            }

            $db->commit();

            $legacySync = LegacyUserSync::sincronizarBajaDesdeSpartan((int)$id_persona, (int)$usuario_baja);

            return self::resultado(true, 'Baja registrada correctamente con archivos.', [
                'id_persona' => (int)$id_persona,
                'legacy_sync' => $legacySync,
            ]);

        } catch (\Exception $e) {
            if (isset($db)) {
                try { $db->rollback(); } catch (\Exception $rollbackError) {}
            }
            return self::resultado(false, 'Error al registrar la baja.', null, $e->getMessage());
        }
    }

    /** ID de documento para "Documento Reingreso" en carga_documento_persona */
    const ID_DOCUMENTO_REINGRESO = 16;

    /**
     * Registrar reingreso de un gestor (pasar de Baja a Activo).
     * Inserta en reingresos, guarda PDFs en carga_documento_persona (id_documento=16) y actualiza persona.estatus = 'Activo'.
     */
    public static function registrarReingresoGestor($data)
    {
        try {
            $db = new Database();

            $id_persona   = (int)($data['id_gestor'] ?? 0);
            $motivo       = (string)($data['motivo_reingreso'] ?? '');
            $descripcion  = (string)($data['descripcion_reingreso'] ?? '');
            $fecha_reingreso = (string)($data['fecha_reingreso'] ?? date('Y-m-d H:i:s'));
            $usuario_reingreso = (string)($data['usuario_reingreso'] ?? 'sistema');
            $archivos    = $data['archivos'] ?? [];

            if ($id_persona < 1) {
                return self::resultado(false, 'ID de persona inválido.');
            }

            // 1) Insertar en reingresos (consultas preparadas para evitar errores por caracteres especiales)
            $db->queryOne("
                INSERT INTO __SPARTA_SECRET_REDACTED__.reingresos
                (id_persona, fecha_reingreso, motivo_reingreso, descripcion_reingreso, usuario_reingreso)
                VALUES
                (:id_persona, :fecha_reingreso, :motivo_reingreso, :descripcion_reingreso, :usuario_reingreso)
            ", [
                'id_persona' => $id_persona,
                'fecha_reingreso' => $fecha_reingreso,
                'motivo_reingreso' => $motivo,
                'descripcion_reingreso' => $descripcion,
                'usuario_reingreso' => $usuario_reingreso
            ]);

            $result = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id_reingreso = isset($result['id']) ? (int)$result['id'] : null;
            if (!$id_reingreso) {
                return self::resultado(false, 'No se pudo obtener el ID del reingreso.');
            }

            // 2) Guardar cada archivo en carga_documento_persona (Documento Reingreso = 16)
            $id_documento = self::ID_DOCUMENTO_REINGRESO;
            foreach ($archivos as $archivo) {
                $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.carga_documento_persona
                    (id_persona, id_documento, archivo, fecha_carga)
                    VALUES
                    (:id_persona, :id_documento, :archivo, NOW())
                ", [
                    'id_persona' => $id_persona,
                    'id_documento' => $id_documento,
                    'archivo' => (string)$archivo
                ]);
            }

            // 3) Pasar a la plantilla: estatus = 'Activo'
            $db->queryOne("
                UPDATE __SPARTA_SECRET_REDACTED__.persona
                SET estatus = 'Activo'
                WHERE id = :id_persona
            ", ['id_persona' => $id_persona]);

            return self::resultado(true, 'Reingreso registrado correctamente. La persona ha sido reactivada en la plantilla.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar el reingreso.', null, $e->getMessage());
        }
    }

    /**
     * Obtener documentos de un reingreso (por id de registro reingreso).
     */
    public static function getDocumentosReingreso($registro_reingreso)
    {
        try {
            $db = new Database();
            $reingreso = $db->queryOne("
                SELECT id_persona FROM __SPARTA_SECRET_REDACTED__.reingresos WHERE id = :id
            ", ['id' => $registro_reingreso]);
            if (!$reingreso || !isset($reingreso['id_persona'])) {
                return self::resultado(false, 'Reingreso no encontrado.', []);
            }
            $id_persona = $reingreso['id_persona'];
            $id_documento = self::ID_DOCUMENTO_REINGRESO;
            $documentos = $db->queryAll("
                SELECT cdp.id, cdp.archivo, DATE_FORMAT(cdp.fecha_carga, '%Y-%m-%d %H:%i') AS fecha_carga
                FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona cdp
                WHERE cdp.id_persona = :id_persona AND cdp.id_documento = :id_documento
                ORDER BY cdp.fecha_carga DESC
            ", ['id_persona' => $id_persona, 'id_documento' => $id_documento]);
            return self::resultado(true, 'Documentos encontrados.', $documentos ?? []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener documentos.', [], $e->getMessage());
        }
    }

    /**
     * Elimina por completo una persona y sus datos relacionados (solo si no tiene dependencias críticas).
     * Orden: anular referencias en ticket, borrar tablas hijas, luego persona.
     */
    public static function eliminarPersonaCompleto($id_persona)
    {
        $id = (int) $id_persona;
        if ($id < 1) {
            return self::resultado(false, 'ID de persona inválido.');
        }
        try {
            $db = new Database();

            // Iniciar transacción para garantizar integridad
            $db->beginTransaction();

            try {
                // ========== TICKETS (actualizar en lugar de eliminar para no perder historial) ==========
                // 1) Tickets: dejar de referenciar a esta persona como creador
                $db->CRUD("UPDATE ticket SET id_persona_creador = NULL WHERE id_persona_creador = $id");

                // 2) ticket_historico: desvincular gestor y asignado
                try {
                    $db->CRUD("UPDATE __SPARTA_SECRET_REDACTED__.ticket_historico SET gestor_id = NULL WHERE gestor_id = $id");
                    $db->CRUD("UPDATE __SPARTA_SECRET_REDACTED__.ticket_historico SET usuario_asignado = NULL WHERE usuario_asignado = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // 3) Asignaciones de ticket
                $db->CRUD("DELETE FROM asignacion_ticket WHERE id_persona_asignada = $id");

                // ========== MÓDULOS Y PERMISOS ==========
                // 4) Módulos web
                $db->CRUD("DELETE FROM asigna_modulo_web WHERE usuario_id = $id");

                // ========== JERARQUÍA Y ORGANIGRAMA ==========
                // 5) asigna_jefe: eliminar como persona subordinada
                try {
                    $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.asigna_jefe WHERE id_persona = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // 6) asigna_jefe: eliminar como jefe de otros (reasignar subordinados a NULL o eliminar)
                try {
                    $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.asigna_jefe WHERE id_jefe = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // ========== ASIGNACIONES ==========
                // 7) Puestos
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.asigna_puesto WHERE id_persona = $id");

                // 8) Bajas y reingresos
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.baja_persona WHERE id_persona = $id");
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.reingresos WHERE id_persona = $id");

                // 9) Legión
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.asigna_legion WHERE id_persona = $id");

                // ========== DOCUMENTOS ==========
                // 10) Documentos cargados
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.carga_documento_persona WHERE id_persona = $id");

                // 11) documentos_persona
                try {
                    $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.documentos_persona WHERE id_persona = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // ========== PERFIL ==========
                // 12) Perfil (si existe la tabla)
                try {
                    $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.perfil WHERE id_persona = $id");
                } catch (\Exception $e) { /* ignorar si no existe */ }

                // ========== SABUESO / CHAT ==========
                // 13) Chat / dictamen / evidencias
                try {
                    $db->CRUD("DELETE FROM chat WHERE id_persona = $id");
                    $db->CRUD("DELETE FROM dictamen WHERE id_persona = $id");
                    $db->CRUD("DELETE FROM ticket_evidencia WHERE id_persona = $id");
                } catch (\Exception $e) { /* ignorar si no existen */ }

                // ========== FINALMENTE: ELIMINAR PERSONA ==========
                // 14) Persona
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.persona WHERE id = $id");

                // Confirmar transacción
                $db->commit();

                return self::resultado(true, 'Usuario eliminado del sistema correctamente.');

            } catch (\Exception $innerEx) {
                // Revertir todo si algo falla
                $db->rollback();
                throw $innerEx;
            }

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar el usuario.', null, $e->getMessage());
        }
    }

    /**
     * Lista personas en estatus Baja: una fila por persona (solo la baja más reciente).
     */
    public static function getConsultaBajas($fecha_inicio = null, $fecha_fin = null)
    {
        $exB = UsuarioFantasmaReporteria::sqlExcluirPersona('p');
        $query = <<<SQL
        SELECT
            p.id,
            p.id AS numero_empleado,
            p.nombres,
            p.apellidop,
            p.apellidom,
            p.numero_empleado AS external_id,
            d.nombre AS departamento,
            pu.nombre AS nombre_puesto,
            bp.fecha_baja,
            bp.id AS registro_baja,
            bp.motivo,
            bp.descripcion,
            p.user_name
        FROM __SPARTA_SECRET_REDACTED__.persona p
        INNER JOIN __SPARTA_SECRET_REDACTED__.baja_persona bp ON p.id = bp.id_persona
        INNER JOIN (
            SELECT id_persona, MAX(id) AS id_ultima_baja
            FROM __SPARTA_SECRET_REDACTED__.baja_persona
            GROUP BY id_persona
        ) ult ON bp.id_persona = ult.id_persona AND bp.id = ult.id_ultima_baja
        LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON p.id = ap.id_persona
        LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON pu.departamento_id = d.id
        WHERE p.estatus = 'Baja'
        {$exB}
        SQL;

        // Agregar filtro de fecha si se proporciona
        if ($fecha_inicio && $fecha_fin) {
            $query .= " AND DATE(bp.fecha_baja) BETWEEN :fecha_inicio AND :fecha_fin";
        } elseif ($fecha_inicio) {
            $query .= " AND DATE(bp.fecha_baja) >= :fecha_inicio";
        } elseif ($fecha_fin) {
            $query .= " AND DATE(bp.fecha_baja) <= :fecha_fin";
        }

        $query .= " ORDER BY bp.fecha_baja DESC";

        try {
            $db = new Database();

            // Si hay filtros de fecha, usar parámetros preparados
            // NOTA: Las claves NO deben incluir el ':' porque Database::runQuery lo agrega automáticamente
            if ($fecha_inicio || $fecha_fin) {
                $params = [];
                if ($fecha_inicio) $params['fecha_inicio'] = $fecha_inicio;
                if ($fecha_fin) $params['fecha_fin'] = $fecha_fin;
                $r = $db->queryAll($query, $params);
            } else {
                $r = $db->queryAll($query);
            }

            return self::resultado(true, 'Bajas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /**
     * Lista personas en estatus Baja (avanzado): una fila por persona (solo la baja más reciente).
     */
    public static function getConsultaBajasAvanzado($filtros = [])
    {
        $exB = UsuarioFantasmaReporteria::sqlExcluirPersona('p');
        $query = <<<SQL
        SELECT
            p.id,
            p.id AS numero_empleado,
            p.nombres,
            p.apellidop,
            p.apellidom,
            p.numero_empleado AS external_id,
            d.nombre AS departamento,
            pu.nombre AS nombre_puesto,
            bp.fecha_baja,
            bp.id AS registro_baja,
            bp.motivo,
            bp.descripcion,
            p.user_name
        FROM __SPARTA_SECRET_REDACTED__.persona p
        INNER JOIN __SPARTA_SECRET_REDACTED__.baja_persona bp ON p.id = bp.id_persona
        INNER JOIN (
            SELECT id_persona, MAX(id) AS id_ultima_baja
            FROM __SPARTA_SECRET_REDACTED__.baja_persona
            GROUP BY id_persona
        ) ult ON bp.id_persona = ult.id_persona AND bp.id = ult.id_ultima_baja
        LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON p.id = ap.id_persona
        LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON pu.departamento_id = d.id
        WHERE p.estatus = 'Baja'
        {$exB}
        SQL;

        $params = [];

        // Fechas eliminadas como filtro

        // Departamento
        if (!empty($filtros['departamento'])) {
            $query .= " AND d.id = :departamento";
            $params['departamento'] = $filtros['departamento'];
        }

        // Puesto
        if (!empty($filtros['puesto'])) {
            $query .= " AND pu.id = :puesto";
            $params['puesto'] = $filtros['puesto'];
        }

        // Estatus (por si se requiere filtrar por otro estatus de baja)
        if (!empty($filtros['estatus'])) {
            $query .= " AND bp.motivo = :estatus";
            $params['estatus'] = $filtros['estatus'];
        }

        // Multipuesto (si se requiere filtrar por empleados con más de un puesto)
        if (isset($filtros['multipuesto']) && $filtros['multipuesto'] !== '' && $filtros['multipuesto'] !== null) {
            if ($filtros['multipuesto'] === 'multiples') {
                $query .= " AND (SELECT COUNT(*) FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap2 WHERE ap2.id_persona = p.id) > 1";
            } elseif ($filtros['multipuesto'] === 'unico') {
                $query .= " AND (SELECT COUNT(*) FROM __SPARTA_SECRET_REDACTED__.asigna_puesto ap2 WHERE ap2.id_persona = p.id) = 1";
            }
        }

        $query .= " ORDER BY bp.fecha_baja DESC";

        try {
            $db = new Database();
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Bajas encontradas.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar persona de forma segura (elimina todas las dependencias primero)
     * Solo para administradores - usar con precaución
     * @param int $id_persona
     * @param bool $confirmar Si es false, solo muestra las dependencias sin eliminar
     * @return array
     */
    public static function eliminarPersonaSeguro($id_persona, $confirmar = false)
    {
        $id_persona = (int) $id_persona;
        if ($id_persona <= 0) {
            return self::resultado(false, 'ID de persona inválido.');
        }

        try {
            $db = new Database();

            // Verificar que la persona existe
            $persona = $db->queryOne("SELECT id, nombre, apellido_paterno, apellido_materno FROM __SPARTA_SECRET_REDACTED__.persona WHERE id = :id", ['id' => $id_persona]);
            if (!$persona) {
                return self::resultado(false, 'Persona no encontrada.');
            }

            $nombreCompleto = trim($persona['nombre'] . ' ' . $persona['apellido_paterno'] . ' ' . ($persona['apellido_materno'] ?? ''));

            // Buscar todas las dependencias
            $dependencias = [];

            // 1. asigna_puesto
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.asigna_puesto WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['asigna_puesto'] = (int)$count['c'];

            // 2. asigna_jefe (como persona)
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.asigna_jefe WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['asigna_jefe_persona'] = (int)$count['c'];

            // 3. asigna_jefe (como jefe de otros)
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.asigna_jefe WHERE id_jefe = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['asigna_jefe_jefe'] = (int)$count['c'];

            // 4. asigna_legion
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.asigna_legion WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['asigna_legion'] = (int)$count['c'];

            // 5. baja_persona
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.baja_persona WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['baja_persona'] = (int)$count['c'];
            // 5b. reingresos
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.reingresos WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['reingresos'] = (int)$count['c'];

            // 6. ticket_historico (gestor_id)
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.ticket_historico WHERE gestor_id = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['ticket_historico_gestor'] = (int)$count['c'];

            // 7. ticket_historico (usuario_asignado)
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.ticket_historico WHERE usuario_asignado = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['ticket_historico_asignado'] = (int)$count['c'];

            // 8. documentos_persona
            $count = $db->queryOne("SELECT COUNT(*) as c FROM __SPARTA_SECRET_REDACTED__.documentos_persona WHERE id_persona = :id", ['id' => $id_persona]);
            if ($count['c'] > 0) $dependencias['documentos_persona'] = (int)$count['c'];

            // Si solo es consulta (no confirmar), devolver dependencias
            if (!$confirmar) {
                return self::resultado(true, 'Dependencias encontradas para: ' . $nombreCompleto, [
                    'id' => $id_persona,
                    'nombre' => $nombreCompleto,
                    'dependencias' => $dependencias,
                    'total_dependencias' => array_sum($dependencias)
                ]);
            }

            // ELIMINAR - ejecutar en transacción
            $db->beginTransaction();

            try {
                // Eliminar en orden de dependencias
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.documentos_persona WHERE id_persona = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.asigna_legion WHERE id_persona = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.asigna_jefe WHERE id_persona = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.asigna_jefe WHERE id_jefe = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.asigna_puesto WHERE id_persona = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.baja_persona WHERE id_persona = :id", ['id' => $id_persona]);
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.reingresos WHERE id_persona = :id", ['id' => $id_persona]);

                // Para tickets, en lugar de eliminar, ponemos NULL (para no perder historial)
                $db->CRUD("UPDATE __SPARTA_SECRET_REDACTED__.ticket_historico SET gestor_id = NULL WHERE gestor_id = :id", ['id' => $id_persona]);
                $db->CRUD("UPDATE __SPARTA_SECRET_REDACTED__.ticket_historico SET usuario_asignado = NULL WHERE usuario_asignado = :id", ['id' => $id_persona]);

                // Finalmente eliminar la persona
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.persona WHERE id = :id", ['id' => $id_persona]);

                $db->commit();

                return self::resultado(true, 'Persona eliminada correctamente: ' . $nombreCompleto, [
                    'id' => $id_persona,
                    'nombre' => $nombreCompleto,
                    'dependencias_eliminadas' => $dependencias
                ]);

            } catch (\Exception $e) {
                $db->rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar persona: ' . $e->getMessage());
        }
    }

    /**
     * Calle en persona: una sola columna domicilio_calle_texto (texto libre o nombre desde catálogo si el front envía id_div_nivel4).
     */
    private static function domicilioCalleTextoParaGuardar(Database $db, array $data): string
    {
        $txt = mb_substr(trim((string) ($data['domicilio_calle_texto'] ?? '')), 0, 200);
        if ($txt !== '') {
            return $txt;
        }
        $idFk = self::sqlIdDivisionAdministrativaFk($data['id_div_nivel4'] ?? null);
        if ($idFk === 'NULL') {
            return '';
        }
        $nr = $db->queryOne(
            'SELECT nombre FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas WHERE id = :id AND activo = 1 LIMIT 1',
            ['id' => (int) $idFk]
        );

        return mb_substr(trim((string) ($nr['nombre'] ?? '')), 0, 200);
    }

    /**
     * Valor SQL para FK id → divisiones_administrativas.id.
     * El front puede enviar null, "" o omitir la clave; no debe guardarse 0.
     */
    private static function sqlIdDivisionAdministrativaFk($value): string
    {
        if ($value === null || $value === false) {
            return 'NULL';
        }
        if (is_string($value) && trim($value) === '') {
            return 'NULL';
        }
        if ($value === '') {
            return 'NULL';
        }
        $i = (int) $value;

        return $i > 0 ? (string) $i : 'NULL';
    }

    /**
 * Obtener estados/divisiones nivel 1 por país
 */
public static function getEstadosPorPais($id_pais)
{
    $id_pais = (int) $id_pais;
    $query = <<<SQL
    SELECT
        da.id,
        da.nombre,
        da.codigo_interno,
        dat.nombre  AS tipo_label,
        dat.codigo  AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_pais = $id_pais
      AND da.nivel   = 1
      AND da.activo  = 1
    ORDER BY da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $r  = $db->queryAll($query);
        if (!empty($r) || $id_pais !== 1) {
            return self::resultado(true, 'Estados encontrados.', $r);
        }
    } catch (\Exception $e) {
        $localError = $e->getMessage();
    }

    if ($id_pais === 1) {
        $api = self::divisionesAdministrativasApiGet('estados');
        if (!empty($api['success']) && !empty($api['datos'])) {
            $datos = array_map([self::class, 'normalizarDivisionAdministrativaApi'], $api['datos']);
            usort($datos, static function ($a, $b) {
                return strcasecmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? ''));
            });
            return self::resultado(true, 'Estados encontrados.', $datos);
        }
    }

    return self::resultado(false, 'Error al obtener estados.', null, $localError ?? 'Catalogo local y remoto sin datos.');
}

/**
 * Obtener municipios/alcaldías nivel 2 por estado/división padre
 */
public static function getMunicipiosPorEstado($id_estado)
{
    $id_estado = (int) $id_estado;
    $query = <<<SQL
    SELECT
        da.id,
        da.nombre,
        da.codigo_interno,
        dat.nombre  AS tipo_label,
        dat.codigo  AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_padre = $id_estado
      AND da.nivel    = 2
      AND da.activo   = 1
    ORDER BY da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $r  = $db->queryAll($query);
        if (!empty($r)) {
            return self::resultado(true, 'Municipios encontrados.', $r);
        }
    } catch (\Exception $e) {
        $localError = $e->getMessage();
    }

    if ($id_estado > 0 && $id_estado <= 32) {
        $api = self::divisionesAdministrativasApiGet('municipios', ['id_padre' => $id_estado]);
        if (!empty($api['success']) && !empty($api['datos'])) {
            $datos = array_map([self::class, 'normalizarDivisionAdministrativaApi'], $api['datos']);
            usort($datos, static function ($a, $b) {
                return strcasecmp((string)($a['nombre'] ?? ''), (string)($b['nombre'] ?? ''));
            });
            return self::resultado(true, 'Municipios encontrados.', $datos);
        }
    }

    return self::resultado(true, 'Municipios encontrados.', []);
}

public static function getEstadosMunicipiosMexico()
{
    $query = <<<SQL
    SELECT
        da.id,
        da.id_padre,
        da.nivel,
        da.nombre,
        da.codigo_interno,
        dat.nombre AS tipo_label,
        dat.codigo AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_pais = 1
      AND da.nivel IN (1, 2)
      AND da.activo = 1
    ORDER BY da.nivel ASC, da.id_padre ASC, da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $rows = $db->queryAll($query);
        $estados = [];
        $municipiosPorEstado = [];

        foreach ($rows as $row) {
            $nivel = (int)($row['nivel'] ?? 0);
            if ($nivel === 1) {
                $estados[] = $row;
                continue;
            }

            if ($nivel === 2) {
                $idPadre = (string)($row['id_padre'] ?? '');
                if ($idPadre === '') {
                    continue;
                }
                if (!isset($municipiosPorEstado[$idPadre])) {
                    $municipiosPorEstado[$idPadre] = [];
                }
                $municipiosPorEstado[$idPadre][] = $row;
            }
        }

        return self::resultado(true, 'Catalogo de Mexico encontrado.', [
            'estados' => $estados,
            'municipios_por_estado' => $municipiosPorEstado,
        ]);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener catalogo de Mexico.', null, $e->getMessage());
    }
}

/**
 * Colonias (nivel 3) bajo un municipio/alcaldía (nivel 2).
 * codigo_postal devuelto desde codigo_interno del catálogo cuando aplica.
 */
public static function getColoniasPorMunicipio($id_municipio)
{
    $id_municipio = (int) $id_municipio;
    if ($id_municipio <= 0) {
        return self::resultado(false, 'ID de municipio inválido.', []);
    }

    $query = <<<SQL
    SELECT
        da.id,
        da.nombre,
        da.codigo_interno,
        NULLIF(TRIM(da.codigo_interno), '') AS codigo_postal,
        dat.nombre  AS tipo_label,
        dat.codigo  AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_padre = $id_municipio
      AND da.nivel    = 3
      AND da.activo   = 1
    ORDER BY da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $r  = $db->queryAll($query);
        return self::resultado(true, 'Colonias encontradas.', $r);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener colonias.', null, $e->getMessage());
    }
}

/**
 * Calles (nivel 4) bajo una colonia (nivel 3).
 */
public static function getCallesPorColonia($id_colonia)
{
    $id_colonia = (int) $id_colonia;
    if ($id_colonia <= 0) {
        return self::resultado(false, 'ID de colonia inválido.', []);
    }

    $query = <<<SQL
    SELECT
        da.id,
        da.nombre,
        da.codigo_interno,
        dat.nombre  AS tipo_label,
        dat.codigo  AS tipo_codigo
    FROM divisiones_administrativas da
    INNER JOIN division_administrativa_tipos dat ON dat.id = da.id_tipo
    WHERE da.id_padre = $id_colonia
      AND da.nivel    = 4
      AND da.activo   = 1
    ORDER BY da.nombre ASC
    SQL;

    try {
        $db = new Database();
        $r  = $db->queryAll($query);
        return self::resultado(true, 'Calles encontradas.', $r);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener calles.', null, $e->getMessage());
    }
}

public static function asegurarMapaOrganizacionalPuesto(?Database $db = null): void
{
    $db = $db ?: new Database();

    $db->CRUD("
        CREATE TABLE IF NOT EXISTS `mapa_organizacional_puesto` (
          `id` int NOT NULL AUTO_INCREMENT,
          `id_pais` int NOT NULL COMMENT 'FK a paises.id',
          `id_puesto` int NOT NULL COMMENT 'FK a puesto.id',
          `id_puesto_padre` int DEFAULT NULL COMMENT 'Puesto superior jerarquico dentro del mapa',
          `posicion_x` int DEFAULT NULL COMMENT 'Coordenada X en el canvas',
          `posicion_y` int DEFAULT NULL COMMENT 'Coordenada Y en el canvas',
          `estatus` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 = Activo, 0 = Inactivo',
          `id_pais_activo_key` int DEFAULT NULL,
          `id_puesto_activo_key` int DEFAULT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `uq_mapa_pais_puesto_activo` (`id_pais_activo_key`, `id_puesto_activo_key`),
          KEY `idx_mapa_pais` (`id_pais`),
          KEY `idx_mapa_puesto` (`id_puesto`),
          KEY `idx_mapa_puesto_padre` (`id_puesto_padre`),
          KEY `idx_mapa_estatus` (`estatus`),
          CONSTRAINT `fk_mapa_pais`
            FOREIGN KEY (`id_pais`)
            REFERENCES `paises` (`id`)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
          CONSTRAINT `fk_mapa_puesto`
            FOREIGN KEY (`id_puesto`)
            REFERENCES `puesto` (`id`)
            ON DELETE RESTRICT
            ON UPDATE CASCADE,
          CONSTRAINT `fk_mapa_puesto_padre`
            FOREIGN KEY (`id_puesto_padre`)
            REFERENCES `puesto` (`id`)
            ON DELETE SET NULL
            ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        COMMENT='Mapa jerarquico manual de puestos por pais'
    ");

    $db->CRUD("DROP TRIGGER IF EXISTS `bi_mapa_org_puesto_activo_key`");
    $db->CRUD("DROP TRIGGER IF EXISTS `bu_mapa_org_puesto_activo_key`");
    $db->CRUD("
        CREATE TRIGGER `bi_mapa_org_puesto_activo_key`
        BEFORE INSERT ON `mapa_organizacional_puesto`
        FOR EACH ROW
        BEGIN
            SET NEW.id_pais_activo_key = IF(NEW.estatus = 1, NEW.id_pais, NULL);
            SET NEW.id_puesto_activo_key = IF(NEW.estatus = 1, NEW.id_puesto, NULL);
        END
    ");
    $db->CRUD("
        CREATE TRIGGER `bu_mapa_org_puesto_activo_key`
        BEFORE UPDATE ON `mapa_organizacional_puesto`
        FOR EACH ROW
        BEGIN
            SET NEW.id_pais_activo_key = IF(NEW.estatus = 1, NEW.id_pais, NULL);
            SET NEW.id_puesto_activo_key = IF(NEW.estatus = 1, NEW.id_puesto, NULL);
        END
    ");
}

public static function getConstructorEstructuraOrganizacional($idPais = 0): array
{
    try {
        $db = new Database();
        if (class_exists('\\Models\\EstadoCuenta') && method_exists('\\Models\\EstadoCuenta', 'asegurarNivelOrganizacionalPuesto')) {
            \Models\EstadoCuenta::asegurarNivelOrganizacionalPuesto($db);
        }
        self::asegurarMapaOrganizacionalPuesto($db);

        $idPais = (int) $idPais;
        $paises = $db->queryAll("
            SELECT id, nombre, codigo_iso
            FROM paises
            WHERE activo = 1
            ORDER BY nombre ASC
        ");

        if ($idPais <= 0) {
            $paisMexico = null;
            foreach ($paises as $pais) {
                $nombrePais = mb_strtolower((string) ($pais['nombre'] ?? ''), 'UTF-8');
                if ($nombrePais === 'méxico' || $nombrePais === 'mexico') {
                    $paisMexico = $pais;
                    break;
                }
            }
            $idPais = (int) (($paisMexico['id'] ?? null) ?: ($paises[0]['id'] ?? 0));
        }

        $niveles = $db->queryAll("
            SELECT id, clave, nombre, orden
            FROM nivel_organizacional
            WHERE activo = 1
            ORDER BY orden ASC, nombre ASC
        ");

        $areas = [];
        $departamentos = [];
        $puestos = [];
        $mapa = [];

        if ($idPais > 0) {
            $areas = $db->queryAll("
                SELECT id, nombre
                FROM departamento_organizacional
                WHERE activo = 1
                  AND id_pais = :id_pais
                ORDER BY nombre ASC
            ", ['id_pais' => $idPais]);

            $departamentos = $db->queryAll("
                SELECT
                    d.id,
                    d.nombre,
                    d.id_departamento_organizacional
                FROM departamento d
                LEFT JOIN departamento_organizacional dor
                    ON dor.id = d.id_departamento_organizacional
                WHERE d.activo = 1
                  AND COALESCE(dor.id_pais, d.id_pais) = :id_pais
                ORDER BY d.nombre ASC
            ", ['id_pais' => $idPais]);

            $puestos = $db->queryAll("
                SELECT
                    p.id AS id_puesto,
                    p.clave,
                    p.nombre AS puesto,
                    d.id AS id_departamento,
                    d.nombre AS departamento,
                    dor.id AS id_area_organizacional,
                    dor.nombre AS area_organizacional,
                    no.id AS id_nivel_organizacional,
                    no.nombre AS nivel_organizacional,
                    CASE WHEN mop.id IS NULL THEN 0 ELSE 1 END AS en_mapa
                FROM puesto p
                INNER JOIN departamento d
                    ON d.id = p.departamento_id
                LEFT JOIN departamento_organizacional dor
                    ON dor.id = d.id_departamento_organizacional
                LEFT JOIN asigna_nivel_organizacional_puesto anop
                    ON anop.id_puesto = p.id
                   AND anop.estatus = 1
                LEFT JOIN nivel_organizacional no
                    ON no.id = anop.id_nivel_organizacional
                LEFT JOIN mapa_organizacional_puesto mop
                    ON mop.id_pais = :id_pais
                   AND mop.id_puesto = p.id
                   AND mop.estatus = 1
                WHERE p.activo = 1
                  AND COALESCE(dor.id_pais, d.id_pais) = :id_pais
                ORDER BY COALESCE(no.orden, 999), dor.nombre ASC, d.nombre ASC, p.nombre ASC
            ", ['id_pais' => $idPais]);

            $mapa = $db->queryAll("
                SELECT
                    mop.id,
                    mop.id_pais,
                    mop.id_puesto,
                    p.clave,
                    p.nombre AS puesto,
                    d.id AS id_departamento,
                    d.nombre AS departamento,
                    dor.id AS id_area_organizacional,
                    dor.nombre AS area_organizacional,
                    no.id AS id_nivel_organizacional,
                    no.nombre AS nivel_organizacional,
                    mop.id_puesto_padre,
                    COALESCE(mop.posicion_x, 120) AS posicion_x,
                    COALESCE(mop.posicion_y, 120) AS posicion_y
                FROM mapa_organizacional_puesto mop
                INNER JOIN puesto p
                    ON p.id = mop.id_puesto
                LEFT JOIN departamento d
                    ON d.id = p.departamento_id
                LEFT JOIN departamento_organizacional dor
                    ON dor.id = d.id_departamento_organizacional
                LEFT JOIN asigna_nivel_organizacional_puesto anop
                    ON anop.id_puesto = p.id
                   AND anop.estatus = 1
                LEFT JOIN nivel_organizacional no
                    ON no.id = anop.id_nivel_organizacional
                WHERE mop.id_pais = :id_pais
                  AND mop.estatus = 1
                ORDER BY COALESCE(no.orden, 999), mop.posicion_y ASC, mop.posicion_x ASC
            ", ['id_pais' => $idPais]);
        }

        return self::resultado(true, 'Estructura organizacional cargada.', [
            'id_pais' => $idPais,
            'paises' => $paises,
            'niveles' => $niveles,
            'areas' => $areas,
            'departamentos' => $departamentos,
            'puestos' => $puestos,
            'mapa' => $mapa,
        ]);
    } catch (\Throwable $e) {
        return self::resultado(false, 'Error al cargar estructura organizacional.', null, $e->getMessage());
    }
}

public static function guardarConstructorEstructuraOrganizacional($idPais, array $nodos): array
{
    $idPais = (int) $idPais;
    if ($idPais <= 0) {
        return self::resultado(false, 'Selecciona un pais para guardar el mapa.');
    }

    $limpios = [];
    $vistos = [];
    foreach ($nodos as $nodo) {
        $idPuesto = (int) ($nodo['id_puesto'] ?? 0);
        if ($idPuesto <= 0 || isset($vistos[$idPuesto])) {
            continue;
        }
        $vistos[$idPuesto] = true;
        $padre = (int) ($nodo['id_puesto_padre'] ?? 0);
        $limpios[] = [
            'id_puesto' => $idPuesto,
            'id_puesto_padre' => $padre > 0 ? $padre : null,
            'posicion_x' => max(0, (int) ($nodo['posicion_x'] ?? 120)),
            'posicion_y' => max(0, (int) ($nodo['posicion_y'] ?? 120)),
        ];
    }

    foreach ($limpios as &$nodo) {
        if ($nodo['id_puesto_padre'] !== null && !isset($vistos[$nodo['id_puesto_padre']])) {
            $nodo['id_puesto_padre'] = null;
        }
        if ($nodo['id_puesto_padre'] === $nodo['id_puesto']) {
            $nodo['id_puesto_padre'] = null;
        }
    }
    unset($nodo);

    $padres = [];
    foreach ($limpios as $nodo) {
        $padres[$nodo['id_puesto']] = $nodo['id_puesto_padre'];
    }
    foreach ($padres as $idPuesto => $idPadre) {
        $visitados = [];
        while ($idPadre !== null) {
            if (isset($visitados[$idPadre]) || (int) $idPadre === (int) $idPuesto) {
                return self::resultado(false, 'La jerarquia no puede guardarse porque contiene un ciclo.');
            }
            $visitados[$idPadre] = true;
            $idPadre = $padres[$idPadre] ?? null;
        }
    }

    try {
        $db = new Database();
        self::asegurarMapaOrganizacionalPuesto($db);
        $db->beginTransaction();

        $db->CRUD("
            UPDATE mapa_organizacional_puesto
            SET estatus = 0
            WHERE id_pais = :id_pais
              AND estatus = 1
        ", ['id_pais' => $idPais]);

        foreach ($limpios as $nodo) {
            $db->CRUD("
                INSERT INTO mapa_organizacional_puesto
                    (id_pais, id_puesto, id_puesto_padre, posicion_x, posicion_y, estatus)
                VALUES
                    (:id_pais, :id_puesto, :id_puesto_padre, :posicion_x, :posicion_y, 1)
            ", [
                'id_pais' => $idPais,
                'id_puesto' => $nodo['id_puesto'],
                'id_puesto_padre' => $nodo['id_puesto_padre'],
                'posicion_x' => $nodo['posicion_x'],
                'posicion_y' => $nodo['posicion_y'],
            ]);
        }

        $db->commit();
        return self::resultado(true, 'Mapa organizacional guardado.', [
            'total_nodos' => count($limpios),
        ]);
    } catch (\Throwable $e) {
        if (isset($db)) {
            $db->rollback();
        }
        return self::resultado(false, 'Error al guardar el mapa organizacional.', null, $e->getMessage());
    }
}

}
