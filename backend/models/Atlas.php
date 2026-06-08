<?php

namespace Models;

use Core\Database;
use Core\Model;

class Atlas extends Model
{
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
                'nombre' => 'Feliz cumpleaños',
                'categoria' => 'cumpleanos',
                'asunto' => 'Feliz cumpleaños',
                'mensaje_texto' => 'Hoy celebramos contigo. Que este nuevo año venga lleno de salud, alegría y grandes momentos.',
                'html' => '<h2>¡Feliz cumpleaños!</h2><p>Hoy celebramos contigo. Que este nuevo año venga lleno de salud, alegría y grandes momentos.</p><p><strong>Gracias por ser parte de Atlas.</strong></p>',
            ],
            [
                'nombre' => 'Avance de venta',
                'categoria' => 'avance_venta',
                'asunto' => 'Avance de venta',
                'mensaje_texto' => 'Tenemos una actualización importante sobre tu avance de venta. Revisa el detalle y continúa con el seguimiento.',
                'html' => '<h2>Avance de venta</h2><p>Tenemos una actualización importante sobre tu avance de venta.</p><ul><li>Revisa el detalle.</li><li>Da seguimiento oportuno.</li><li>Continúa con el proceso indicado.</li></ul>',
            ],
            [
                'nombre' => 'Notificación especial',
                'categoria' => 'notificacion_especial',
                'asunto' => 'Notificación especial',
                'mensaje_texto' => 'Tenemos información importante para ti. Revisa esta notificación y atiende las indicaciones correspondientes.',
                'html' => '<h2>Notificación especial</h2><p>Tenemos información importante para ti. Revisa esta notificación y atiende las indicaciones correspondientes.</p>',
            ],
            [
                'nombre' => 'Atención al colaborador',
                'categoria' => 'atencion_colaborador',
                'asunto' => 'Atención al colaborador',
                'mensaje_texto' => 'Queremos acompañarte y darte seguimiento. Por favor revisa la información y comunícate si necesitas apoyo.',
                'html' => '<h2>Atención al colaborador</h2><p>Queremos acompañarte y darte seguimiento.</p><p>Por favor revisa la información y comunícate si necesitas apoyo.</p>',
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
                return ['success' => false, 'mensaje' => 'Captura nombre, categoría y contenido HTML.'];
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
                  AND t.is_active = 1
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
                    $row['alcance_nombre'] = 'Campaña a ' . $total . ' usuarios';
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
            $datos = $db->queryAll(
                "
                SELECT
                    s.id,
                    s.fk_sucursal,
                    s.distribuidor_id,
                    d.nombre AS distribuidor_nombre,
                    s.sucursal,
                    s.diversificacion_id,
                    s.diversificacion,
                    dv.nombre AS diversificacion_nombre,
                    COALESCE(NULLIF(TRIM(s.direccion_sucursal), ''), dir.direccion, '') AS direccion,
                    s.coordenadas,
                    s.latitud,
                    s.longitud,
                    s.divisional_id,
                    dvl.nombre AS divisional_nombre,
                    s.division_id,
                    divs.nombre AS division_nombre,
                    s.regional_id,
                    reg.nombre AS regional_nombre,
                    s.supervisor_id,
                    sup.nombre AS supervisor_nombre,
                    s.asesor_id,
                    ase.nombre AS asesor_nombre,
                    s.clasificacion_id,
                    c.nombre AS clasificacion_nombre,
                    c.icon_font AS clasificacion_icon_font,
                    c.color_hex AS clasificacion_color_hex,
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
                INNER JOIN atlas_catalogo_diversificaciones dv
                        ON dv.id = s.diversificacion_id
                       AND dv.activo = 1
                LEFT JOIN atlas_catalogo_divisionales dvl
                       ON dvl.id = s.divisional_id
                      AND dvl.activo = 1
                LEFT JOIN atlas_catalogo_divisiones divs
                       ON divs.id = s.division_id
                      AND divs.activo = 1
                LEFT JOIN atlas_catalogo_regionales reg
                       ON reg.id = s.regional_id
                      AND reg.activo = 1
                LEFT JOIN atlas_catalogo_supervisores sup
                       ON sup.id = s.supervisor_id
                      AND sup.activo = 1
                LEFT JOIN atlas_catalogo_asesores ase
                       ON ase.id = s.asesor_id
                      AND ase.activo = 1
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
            ];

            foreach ($datos as &$row) {
                $row['activo'] = (int)($row['activo'] ?? 0);
                $row['estado'] = $row['activo'] === 1 ? 'Activa' : 'Inactiva';
                if ($row['activo'] === 1) {
                    $totales['activas']++;
                } else {
                    $totales['inactivas']++;
                }
                if (trim((string)($row['latitud'] ?? '')) !== '' && trim((string)($row['longitud'] ?? '')) !== '') {
                    $totales['con_coordenadas']++;
                }
            }
            unset($row);

            return [
                'success' => true,
                'mensaje' => 'Sucursales obtenidas.',
                'datos' => $datos,
                'totales' => $totales,
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
                ],
            ];
        }
    }

    public static function getCatalogos(): array
    {
        try {
            $db = new Database();
            return [
                'success' => true,
                'mensaje' => 'Catálogos obtenidos.',
                'datos' => [
                    'divisiones' => $db->queryAll("
                        SELECT
                            divs.id,
                            divs.divisional_id,
                            dvl.nombre AS divisional_nombre,
                            divs.nombre,
                            divs.activo,
                            divs.fecha_alta,
                            divs.fecha_actualizacion
                        FROM atlas_catalogo_divisiones divs
                        INNER JOIN atlas_catalogo_divisionales dvl
                                ON dvl.id = divs.divisional_id
                               AND dvl.activo = 1
                        ORDER BY divs.activo DESC, dvl.nombre ASC, divs.nombre ASC, divs.id ASC
                    "),
                    'divisionales' => $db->queryAll("
                        SELECT id, nombre, activo, fecha_alta, fecha_actualizacion
                        FROM atlas_catalogo_divisionales
                        WHERE activo = 1
                        ORDER BY nombre ASC, id ASC
                    "),
                    'regionales' => $db->queryAll("
                        SELECT reg.id, reg.division_id, reg.nombre, reg.activo, reg.fecha_alta, reg.fecha_actualizacion
                        FROM atlas_catalogo_regionales reg
                        INNER JOIN atlas_catalogo_divisiones divs
                                ON divs.id = reg.division_id
                               AND divs.activo = 1
                        WHERE reg.activo = 1
                        ORDER BY reg.nombre ASC, reg.id ASC
                    "),
                    'supervisores' => $db->queryAll("
                        SELECT sup.id, sup.regional_id, sup.nombre, sup.activo, sup.fecha_alta, sup.fecha_actualizacion
                        FROM atlas_catalogo_supervisores sup
                        INNER JOIN atlas_catalogo_regionales reg
                                ON reg.id = sup.regional_id
                               AND reg.activo = 1
                        WHERE sup.activo = 1
                        ORDER BY sup.nombre ASC, sup.id ASC
                    "),
                    'asesores' => $db->queryAll("
                        SELECT ase.id, ase.supervisor_id, ase.nombre, ase.activo, ase.fecha_alta, ase.fecha_actualizacion
                        FROM atlas_catalogo_asesores ase
                        INNER JOIN atlas_catalogo_supervisores sup
                                ON sup.id = ase.supervisor_id
                               AND sup.activo = 1
                        WHERE ase.activo = 1
                        ORDER BY ase.nombre ASC, ase.id ASC
                    "),
                    'distribuidores' => $db->queryAll("
                        SELECT id, nombre, activo, fecha_alta, fecha_actualizacion
                        FROM atlas_catalogo_distribuidores
                        ORDER BY activo DESC, nombre ASC, id ASC
                    "),
                    'diversificaciones' => $db->queryAll("
                        SELECT id, nombre, activo, fecha_alta, fecha_actualizacion
                        FROM atlas_catalogo_diversificaciones
                        ORDER BY activo DESC, nombre ASC, id ASC
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
                'mensaje' => 'No se pudieron obtener los catálogos.',
                'error' => $e->getMessage(),
                'datos' => [
                    'divisiones' => [],
                    'divisionales' => [],
                    'regionales' => [],
                    'supervisores' => [],
                    'asesores' => [],
                    'distribuidores' => [],
                    'diversificaciones' => [],
                    'clasificaciones' => [],
                ],
            ];
        }
    }

    public static function guardarSucursal(array $input): array
    {
        try {
            $db = new Database();
            $id = self::intVal($input['id'] ?? 0);
            $sucursal = self::strVal($input['sucursal'] ?? '');
            $distribuidorId = self::intVal($input['distribuidor_id'] ?? 0);
            $diversificacionId = self::intVal($input['diversificacion_id'] ?? 0);
            $fkSucursal = 0;

            if ($id > 0) {
                $actual = $db->queryOne(
                    "SELECT fk_sucursal FROM atlas_catalogo_sucursales WHERE id = :id LIMIT 1",
                    ['id' => $id]
                );
                if (!$actual) {
                    return ['success' => false, 'mensaje' => 'No se encontró la sucursal a actualizar.'];
                }
                $fkSucursal = self::intVal($actual['fk_sucursal'] ?? 0);
            } else {
                $siguiente = $db->queryOne(
                    "SELECT COALESCE(MAX(fk_sucursal), 0) + 1 AS fk_sucursal FROM atlas_catalogo_sucursales"
                );
                $fkSucursal = self::intVal($siguiente['fk_sucursal'] ?? 0);
            }

            if ($fkSucursal <= 0 || $sucursal === '' || $distribuidorId <= 0 || $diversificacionId <= 0) {
                return ['success' => false, 'mensaje' => 'Captura sucursal, distribuidor y diversificación.'];
            }

            $obligatorios = [
                'clasificacion_id' => 'clasificación',
                'divisional_id' => 'divisional',
                'division_id' => 'división',
                'regional_id' => 'regional',
                'supervisor_id' => 'supervisor',
                'asesor_id' => 'asesor',
                'direccion_sucursal' => 'dirección',
                'estado' => 'estado',
                'municipio' => 'municipio',
                'localidad' => 'localidad',
                'codigo_postal' => 'código postal',
                'latitud' => 'latitud',
                'longitud' => 'longitud',
            ];
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

            $diversificacion = $db->queryOne(
                "SELECT id, nombre FROM atlas_catalogo_diversificaciones WHERE id = :id AND activo = 1 LIMIT 1",
                ['id' => $diversificacionId]
            );
            if (!$diversificacion) {
                return ['success' => false, 'mensaje' => 'La diversificación seleccionada no está activa.'];
            }

            $datos = [
                'fk_sucursal' => $fkSucursal,
                'distribuidor_id' => $distribuidorId,
                'sucursal' => $sucursal,
                'diversificacion_id' => $diversificacionId,
                'diversificacion' => self::nullableStr($diversificacion['nombre'] ?? null),
                'direccion_sucursal' => self::nullableStr($input['direccion_sucursal'] ?? null),
                'coordenadas' => self::nullableStr($input['coordenadas'] ?? null),
                'latitud' => self::nullableDecimal($input['latitud'] ?? null),
                'longitud' => self::nullableDecimal($input['longitud'] ?? null),
                'estado' => self::nullableStr($input['estado'] ?? null),
                'municipio' => self::nullableStr($input['municipio'] ?? null),
                'localidad' => self::nullableStr($input['localidad'] ?? null),
                'codigo_postal' => self::nullableStr($input['codigo_postal'] ?? null),
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

            if ($id > 0) {
                $datos['id'] = $id;
                $db->CRUD("
                    UPDATE atlas_catalogo_sucursales
                    SET fk_sucursal = :fk_sucursal,
                        distribuidor_id = :distribuidor_id,
                        sucursal = :sucursal,
                        diversificacion_id = :diversificacion_id,
                        diversificacion = :diversificacion,
                        direccion_sucursal = :direccion_sucursal,
                        coordenadas = :coordenadas,
                        latitud = :latitud,
                        longitud = :longitud,
                        estado = :estado,
                        municipio = :municipio,
                        localidad = :localidad,
                        codigo_postal = :codigo_postal,
                        divisional_id = :divisional_id,
                        division_id = :division_id,
                        regional_id = :regional_id,
                        supervisor_id = :supervisor_id,
                        asesor_id = :asesor_id,
                        clasificacion_id = :clasificacion_id,
                        activo = :activo
                    WHERE id = :id
                ", $datos);
                return ['success' => true, 'mensaje' => 'Sucursal actualizada.'];
            }

            $db->CRUD("
                INSERT INTO atlas_catalogo_sucursales
                    (fk_sucursal, distribuidor_id, sucursal, diversificacion_id, diversificacion, direccion_sucursal, coordenadas,
                     latitud, longitud, estado, municipio, localidad, codigo_postal, divisional_id, division_id,
                     regional_id, supervisor_id, asesor_id, clasificacion_id, activo)
                VALUES
                    (:fk_sucursal, :distribuidor_id, :sucursal, :diversificacion_id, :diversificacion, :direccion_sucursal, :coordenadas,
                     :latitud, :longitud, :estado, :municipio, :localidad, :codigo_postal, :divisional_id, :division_id,
                     :regional_id, :supervisor_id, :asesor_id, :clasificacion_id, :activo)
            ", $datos);
            return ['success' => true, 'mensaje' => 'Sucursal agregada.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar la sucursal.', 'error' => $e->getMessage()];
        }
    }

    public static function guardarDivision(array $input): array
    {
        $divisionalId = self::nullableInt($input['divisional_id'] ?? null);
        if ($divisionalId === null) {
            return ['success' => false, 'mensaje' => 'Selecciona un divisional activo.'];
        }

        try {
            $db = new Database();
            $existe = $db->queryOne(
                "SELECT id FROM atlas_catalogo_divisionales WHERE id = :id AND activo = 1 LIMIT 1",
                ['id' => $divisionalId]
            );
            if (!$existe) {
                return ['success' => false, 'mensaje' => 'El divisional seleccionado no está activo.'];
            }
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo validar el divisional.', 'error' => $e->getMessage()];
        }

        return self::guardarSimple('atlas_catalogo_divisiones', [
            'divisional_id' => $divisionalId,
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ], self::intVal($input['id'] ?? 0), ['nombre'], 'división');
    }

    public static function guardarDistribuidor(array $input): array
    {
        return self::guardarSimple('atlas_catalogo_distribuidores', [
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ], self::intVal($input['id'] ?? 0), ['nombre'], 'distribuidor');
    }

    public static function guardarDiversificacion(array $input): array
    {
        return self::guardarSimple('atlas_catalogo_diversificaciones', [
            'nombre' => self::strVal($input['nombre'] ?? ''),
            'activo' => self::activoVal($input['activo'] ?? 1),
        ], self::intVal($input['id'] ?? 0), ['nombre'], 'diversificación');
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

        return self::guardarSimple('atlas_catalogo_clasificaciones', $datos, $id, ['nombre'], 'clasificación');
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
            return ['success' => false, 'mensaje' => 'Orden inválido.'];
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
                'mensaje' => 'No se pudo guardar el catálogo.',
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
