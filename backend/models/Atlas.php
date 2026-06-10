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
                       DATE_FORMAT(fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_catalogo_dictamen
                ORDER BY orden ASC, nombre ASC, id ASC
            ");
            $subestatus = $db->queryAll("
                SELECT s.id, s.dictamen_id, d.nombre AS dictamen_nombre, d.codigo_estatus, s.clave, s.nombre,
                       s.orden, s.activo, s.estado_registro,
                       DATE_FORMAT(s.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_dictamen_sub_estatus s
                INNER JOIN atlas_catalogo_dictamen d ON d.id = s.dictamen_id
                ORDER BY s.orden ASC, d.orden ASC, s.nombre ASC, s.id ASC
            ");
            $tiposGestion = $db->queryAll("
                SELECT id, clave, nombre, orden, activo, estado_registro,
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
                       DATE_FORMAT(g.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                FROM atlas_catalogo_gestion g
                INNER JOIN atlas_catalogo_dictamen d ON d.id = g.dictamen_id
                INNER JOIN atlas_dictamen_sub_estatus s ON s.id = COALESCE(NULLIF(g.subestatus_id, 0), g.sub_estatus_id)
                LEFT JOIN atlas_catalogo_tipos_gestion tg ON tg.id = g.tipo_gestion_id
                ORDER BY d.orden ASC, s.orden ASC, g.orden ASC, g.nombre ASC, g.id ASC
            ");

            return [
                'success' => true,
                'mensaje' => 'Catálogos comerciales obtenidos.',
                'datos' => [
                    'dictamenes' => $dictamenes,
                    'subestatus' => $subestatus,
                    'tipos_gestion' => $tiposGestion,
                    'gestiones' => $gestiones,
                ],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudieron obtener los catálogos comerciales.', 'error' => $e->getMessage(), 'datos' => ['dictamenes' => [], 'subestatus' => [], 'tipos_gestion' => [], 'gestiones' => []]];
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
            return ['success' => false, 'mensaje' => 'Tipo de catálogo no válido.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'mensaje' => 'No se pudo guardar el catálogo comercial.', 'error' => $e->getMessage()];
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
            return ['success' => false, 'mensaje' => 'Captura el tipo de gestión.'];
        }
        $clave = self::nullableStr($input['clave'] ?? null) ?: self::claveDesdeTexto($nombre);
        $dup = $db->queryOne("SELECT id FROM atlas_catalogo_tipos_gestion WHERE (LOWER(nombre)=LOWER(:nombre) OR LOWER(clave)=LOWER(:clave)) AND id <> :id LIMIT 1", ['nombre' => $nombre, 'clave' => $clave, 'id' => $id]);
        if ($dup) {
            return ['success' => false, 'mensaje' => 'Ya existe ese tipo de gestión.'];
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
        return self::guardarFilaCatalogo($db, 'atlas_catalogo_tipos_gestion', $datos, $id, 'Tipo de gestión');
    }

    private static function guardarGestionComercial(Database $db, array $input): array
    {
        $id = self::intVal($input['id'] ?? 0);
        $subestatusId = self::intVal($input['subestatus_id'] ?? 0);
        $sub = $subestatusId > 0 ? $db->queryOne("SELECT id, dictamen_id FROM atlas_dictamen_sub_estatus WHERE id = :id LIMIT 1", ['id' => $subestatusId]) : null;
        $nombre = self::strVal($input['nombre'] ?? '');
        if (!$sub || $nombre === '') {
            return ['success' => false, 'mensaje' => 'Selecciona subestatus y captura gestión.'];
        }
        $tipoGestionId = self::intVal($input['tipo_gestion_id'] ?? 0);
        $tipoGestionNombre = self::nullableStr($input['tipo_gestion'] ?? null);
        if ($tipoGestionId > 0) {
            $tipo = $db->queryOne("SELECT id, nombre FROM atlas_catalogo_tipos_gestion WHERE id = :id LIMIT 1", ['id' => $tipoGestionId]);
            if (!$tipo) {
                return ['success' => false, 'mensaje' => 'Selecciona un tipo de gestión válido.'];
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
            return ['success' => false, 'mensaje' => 'Ya existe esa gestión dentro del subestatus.'];
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
        return self::guardarFilaCatalogo($db, 'atlas_catalogo_gestion', $datos, $id, 'Gestión');
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
            return ['success' => true, 'mensaje' => 'Catálogos comerciales publicados.', 'resumen' => $resumen];
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
            return ['success' => false, 'mensaje' => 'Orden inválido.'];
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
            if ($gestion === '') $errores[] = "Fila $n: gestión obligatoria.";
            $subKey = mb_strtolower($estatus . '|' . $sub, 'UTF-8');
            $gestionKey = mb_strtolower($estatus . '|' . $sub . '|' . $gestion, 'UTF-8');
            if ($sub !== '' && isset($subKeys[$subKey]) && $subKeys[$subKey] !== $n) {
                // Permitimos repetir el subestatus si trae gestiones distintas.
            } else {
                $subKeys[$subKey] = $n;
            }
            if ($gestion !== '' && isset($gestionKeys[$gestionKey])) {
                $errores[] = "Fila $n: gestión duplicada dentro del mismo subestatus.";
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
