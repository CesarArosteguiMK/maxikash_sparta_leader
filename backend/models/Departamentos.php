<?php

namespace models;

use Core\Model;
use Core\Database;

class Departamentos extends Model
{
    private static function generarClavePuestoUnica(Database $db, string $nombre, int $id_departamento, int $excepto_id = 0): string
    {
        $base = preg_replace('/[^\pL\pN]+/u', '-', trim($nombre));
        $base = trim((string) $base, '-');
        if ($base === '') {
            $base = 'puesto';
        }

        $sufijoBase = '-' . $id_departamento;
        $limiteBase = max(1, 50 - strlen($sufijoBase));
        $claveBase = substr($base, 0, $limiteBase) . $sufijoBase;
        $clave = $claveBase;

        for ($i = 2; $i <= 999; $i++) {
            $params = ['clave' => $clave];
            $sql = "SELECT id FROM __SPARTA_SECRET_REDACTED__.puesto WHERE clave = :clave";
            if ($excepto_id > 0) {
                $sql .= " AND id <> :excepto_id";
                $params['excepto_id'] = $excepto_id;
            }
            $sql .= " LIMIT 1";

            $existe = $db->queryOne($sql, $params);
            if (!$existe) {
                return $clave;
            }

            $sufijo = '-' . $id_departamento . '-' . $i;
            $clave = substr($base, 0, max(1, 50 - strlen($sufijo))) . $sufijo;
        }

        return substr($base, 0, 35) . '-' . $id_departamento . '-' . time();
    }

    private static function ensureDireccionesOrganizacionSchema(Database $db): void
    {
        try {
            $db->CRUD("
                CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.direcciones_organizacion (
                  id INT NOT NULL AUTO_INCREMENT,
                  nombre VARCHAR(120) NOT NULL,
                  id_pais INT NOT NULL DEFAULT 1,
                  activo TINYINT(1) NOT NULL DEFAULT 1,
                  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (id),
                  UNIQUE KEY ux_direcciones_pais_nombre (id_pais, nombre),
                  KEY idx_direcciones_pais_activo (id_pais, activo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            $db->CRUD("
                CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.asigna_direcciones (
                  id INT NOT NULL AUTO_INCREMENT,
                  id_direccion INT NOT NULL,
                  id_departamento_organizacional INT NOT NULL,
                  activo TINYINT(1) NOT NULL DEFAULT 1,
                  fecha_asignacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  fecha_actualizacion DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (id),
                  UNIQUE KEY ux_asigna_direccion_area (id_departamento_organizacional),
                  KEY idx_asigna_direcciones_direccion (id_direccion),
                  KEY idx_asigna_direcciones_area_activo (id_departamento_organizacional, activo)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            // No crear direcciones numeradas automáticamente; la dirección debe venir del flujo de captura.
            if (false) {
                $areasSinDireccionPropia = $db->queryAll(
                "SELECT dorg.id, dorg.id_pais
                 FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional dorg
                 LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_direcciones ad
                   ON ad.id_departamento_organizacional = dorg.id
                  AND COALESCE(ad.activo, 1) = 1
                 LEFT JOIN __SPARTA_SECRET_REDACTED__.direcciones_organizacion dir
                   ON dir.id = ad.id_direccion
                 WHERE ad.id IS NULL
                    OR LOWER(TRIM(COALESCE(dir.nombre, ''))) IN ('Dirección general', 'Dirección general', 'dirección general')
                 ORDER BY dorg.id_pais, dorg.id"
            );

            $contadorPorPais = [];
            foreach ($areasSinDireccionPropia as $area) {
                $idPais = (int)($area['id_pais'] ?? 1);
                if (!isset($contadorPorPais[$idPais])) {
                    $ultimaDireccion = $db->queryOne(
                        "SELECT MAX(CAST(REPLACE(nombre, 'Dirección ', '') AS UNSIGNED)) AS total
                         FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion
                         WHERE id_pais = :id_pais AND nombre REGEXP '^Dirección [0-9]+$'",
                        ['id_pais' => $idPais]
                    );
                    $contadorPorPais[$idPais] = (int)($ultimaDireccion['total'] ?? 0);
                }

                $contadorPorPais[$idPais]++;
                $nombreDireccion = 'Dirección ' . $contadorPorPais[$idPais];
                $db->CRUD(
                    "INSERT IGNORE INTO __SPARTA_SECRET_REDACTED__.direcciones_organizacion (nombre, id_pais, activo)
                     VALUES (:nombre, :id_pais, 1)",
                    ['nombre' => $nombreDireccion, 'id_pais' => $idPais]
                );

                $direccion = $db->queryOne(
                    "SELECT id FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion
                     WHERE id_pais = :id_pais AND nombre = :nombre
                     LIMIT 1",
                    ['id_pais' => $idPais, 'nombre' => $nombreDireccion]
                );

                $idDireccion = (int)($direccion['id'] ?? 0);
                $idArea = (int)($area['id'] ?? 0);
                if ($idDireccion > 0 && $idArea > 0) {
                    $db->CRUD(
                        "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_direcciones (id_direccion, id_departamento_organizacional, activo)
                         VALUES (:id_direccion, :id_area, 1)
                         ON DUPLICATE KEY UPDATE id_direccion = VALUES(id_direccion), activo = 1, fecha_actualizacion = NOW()",
                        ['id_direccion' => $idDireccion, 'id_area' => $idArea]
                    );
                }
            }
            }
        } catch (\Throwable $e) {
        }
    }

    public static function getConsultaDepartamentos()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            self::ensureDireccionesOrganizacionSchema($db);
            $r = $db->queryAll(
                "
                SELECT 
                    d.id AS departamento_id,
                    d.nombre AS departamento_nombre,
                    d.id_departamento_organizacional,
                    COALESCE(dorg.nombre, 'Sin departamento') AS departamento_organizacional_nombre,
                    COALESCE(dorg.activo, 1) AS departamento_organizacional_activo,
                    COALESCE(dir.id, 0) AS id_direccion,
                    COALESCE(dir.nombre, 'Sin dirección') AS direccion_nombre,
                    COALESCE(dir.activo, 1) AS direccion_activo,
                    COUNT(DISTINCT CASE WHEN COALESCE(p.activo, 1) = 1 THEN p.id END) AS total_puestos,
                    COUNT(DISTINCT CASE
                        WHEN COALESCE(p.activo, 1) = 1
                         AND COALESCE(a.activo, 1) = 1
                         AND COALESCE(per.estatus, 1) = 1
                        THEN a.id_persona
                    END) AS total_personas,
                    d.activo, d.img_url,
                    d.id_pais,
                    COALESCE(pa.nombre, 'Sin país') AS nombre_pais,
                    COALESCE(pa.codigo_iso, 'xx') AS codigo_iso_pais
                FROM departamento d
                LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
                LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = dorg.id AND COALESCE(ad.activo, 1) = 1
                LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
                LEFT JOIN puesto p ON p.departamento_id = d.id
                LEFT JOIN asigna_puesto a ON a.id_puesto = p.id
                LEFT JOIN persona per ON per.id = a.id_persona
                LEFT JOIN paises pa ON pa.id = d.id_pais
                WHERE COALESCE(d.activo, 1) = 1
                GROUP BY d.id, d.nombre, d.id_departamento_organizacional, dorg.nombre, dorg.activo, dir.id, dir.nombre, dir.activo, d.id_pais, pa.nombre, pa.codigo_iso
                ORDER BY FIELD(pa.codigo_iso, 'mx', 'gt', 'co'), direccion_nombre, departamento_organizacional_nombre, d.nombre;
            ");
            $datos = is_array($r) ? $r : [];

            echo json_encode([
                "success" => true,
                "mensaje" => "Departamentos encontrados.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    public static function getConsultaDepartamentosOrganizacionales()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            self::ensureDireccionesOrganizacionSchema($db);
            $r = $db->queryAll(
                "
                SELECT
                    dorg.id,
                    dorg.nombre,
                    dorg.activo,
                    dorg.id_pais,
                    COALESCE(dir.id, 0) AS id_direccion,
                    COALESCE(dir.nombre, 'Sin dirección') AS direccion_nombre,
                    COALESCE(dir.activo, 1) AS direccion_activo,
                    COALESCE(pa.nombre, 'Sin país') AS nombre_pais,
                    COALESCE(pa.codigo_iso, 'xx') AS codigo_iso_pais,
                    COUNT(DISTINCT CASE WHEN COALESCE(d.activo, 1) = 1 THEN d.id END) AS total_areas
                FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional dorg
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_direcciones ad ON ad.id_departamento_organizacional = dorg.id AND COALESCE(ad.activo, 1) = 1
                LEFT JOIN __SPARTA_SECRET_REDACTED__.direcciones_organizacion dir ON dir.id = ad.id_direccion
                LEFT JOIN __SPARTA_SECRET_REDACTED__.paises pa ON pa.id = dorg.id_pais
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id_departamento_organizacional = dorg.id
                GROUP BY dorg.id, dorg.nombre, dorg.activo, dorg.id_pais, dir.id, dir.nombre, dir.activo, pa.nombre, pa.codigo_iso
                ORDER BY FIELD(pa.codigo_iso, 'mx', 'gt', 'co'), direccion_nombre, dorg.nombre
                "
            );

            echo json_encode([
                "success" => true,
                "mensaje" => "Departamentos organizacionales encontrados.",
                "datos" => is_array($r) ? $r : []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    public static function getConsultaDirecciones()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            self::ensureDireccionesOrganizacionSchema($db);
            $r = $db->queryAll(
                "
                SELECT
                    dir.id,
                    dir.nombre,
                    dir.activo,
                    dir.id_pais,
                    COALESCE(pa.nombre, 'Sin país') AS nombre_pais,
                    COALESCE(pa.codigo_iso, 'xx') AS codigo_iso_pais,
                    COUNT(DISTINCT ad.id_departamento_organizacional) AS total_areas
                FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion dir
                LEFT JOIN __SPARTA_SECRET_REDACTED__.paises pa ON pa.id = dir.id_pais
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_direcciones ad ON ad.id_direccion = dir.id AND COALESCE(ad.activo, 1) = 1
                GROUP BY dir.id, dir.nombre, dir.activo, dir.id_pais, pa.nombre, pa.codigo_iso
                ORDER BY FIELD(pa.codigo_iso, 'mx', 'gt', 'co'), dir.nombre
                "
            );

            echo json_encode([
                "success" => true,
                "mensaje" => "Direcciones encontradas.",
                "datos" => is_array($r) ? $r : []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    public static function InsertDireccion($nombre, $id_pais = 1)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            self::ensureDireccionesOrganizacionSchema($db);
            $id_pais = (int) $id_pais;
            if ($id_pais < 1) $id_pais = 1;

            $nombre = trim((string) $nombre);
            if ($nombre === '') {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "El nombre de la dirección es requerido.",
                    "datos" => []
                ]);
                exit;
            }

            $existe = $db->queryOne(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.direcciones_organizacion
                 WHERE LOWER(TRIM(nombre)) = LOWER(:nombre) AND id_pais = :id_pais",
                ['nombre' => $nombre, 'id_pais' => $id_pais]
            );

            if ($existe) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Ya existe una dirección llamada \"{$nombre}\" en el país seleccionado.",
                    "datos" => []
                ]);
                exit;
            }

            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.direcciones_organizacion (nombre, activo, id_pais) VALUES (:nombre, 1, :id_pais)",
                ['nombre' => $nombre, 'id_pais' => $id_pais]
            );

            echo json_encode([
                "success" => true,
                "mensaje" => "Dirección creada correctamente.",
                "datos" => []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    public static function InsertDepartamentoOrganizacional($nombre, $id_pais = 1, $id_direccion = null)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            self::ensureDireccionesOrganizacionSchema($db);
            $id_pais = (int) $id_pais;
            if ($id_pais < 1) $id_pais = 1;
            $id_direccion = $id_direccion !== null && $id_direccion !== '' ? (int) $id_direccion : 0;

            $nombre = trim((string) $nombre);
            if ($nombre === '') {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "El nombre del departamento es requerido.",
                    "datos" => []
                ]);
                exit;
            }

            $existe = $db->queryOne(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.departamento_organizacional
                 WHERE LOWER(TRIM(nombre)) = LOWER(:nombre) AND id_pais = :id_pais",
                ['nombre' => $nombre, 'id_pais' => $id_pais]
            );

            if ($existe) {
                $idDepartamentoOrganizacional = (int)($existe['id'] ?? 0);
                if ($idDepartamentoOrganizacional > 0 && $id_direccion > 0) {
                    $asignacion = $db->queryOne(
                        "SELECT id, id_direccion, activo
                         FROM __SPARTA_SECRET_REDACTED__.asigna_direcciones
                         WHERE id_departamento_organizacional = :id_area
                         LIMIT 1",
                        ['id_area' => $idDepartamentoOrganizacional]
                    );

                    if (!$asignacion || (int)($asignacion['id_direccion'] ?? 0) !== $id_direccion || (int)($asignacion['activo'] ?? 0) !== 1) {
                        $db->CRUD(
                            "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_direcciones (id_direccion, id_departamento_organizacional, activo)
                             VALUES (:id_direccion, :id_area, 1)
                             ON DUPLICATE KEY UPDATE id_direccion = VALUES(id_direccion), activo = 1, fecha_actualizacion = NOW()",
                            ['id_direccion' => $id_direccion, 'id_area' => $idDepartamentoOrganizacional]
                        );

                        echo json_encode([
                            "success" => true,
                            "mensaje" => "Área vinculada correctamente.",
                            "datos" => []
                        ]);
                        exit;
                    }
                }

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Ya existe un área llamada \"{$nombre}\" en el país seleccionado.",
                    "datos" => []
                ]);
                exit;
            }

            if ($id_direccion <= 0) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "La dirección es requerida para crear el área.",
                    "datos" => []
                ]);
                exit;
            }

            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.departamento_organizacional (nombre, activo, id_pais) VALUES (:nombre, 1, :id_pais)",
                ['nombre' => $nombre, 'id_pais' => $id_pais]
            );
            $idDepartamentoOrganizacional = $db->lastInsertId();

            if ($idDepartamentoOrganizacional > 0 && $id_direccion > 0) {
                $db->CRUD(
                    "INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_direcciones (id_direccion, id_departamento_organizacional, activo)
                     VALUES (:id_direccion, :id_area, 1)
                     ON DUPLICATE KEY UPDATE id_direccion = VALUES(id_direccion), activo = 1, fecha_actualizacion = NOW()",
                    ['id_direccion' => $id_direccion, 'id_area' => $idDepartamentoOrganizacional]
                );
            }

            echo json_encode([
                "success" => true,
                "mensaje" => "Área creada correctamente.",
                "datos" => []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    public static function getConsultaPuestos($id_departamento)
    {
        header('Content-Type: application/json; charset=utf-8');

        $id_dep = $id_departamento !== null && $id_departamento !== '' ? (int) $id_departamento : 0;
        if ($id_dep <= 0) {
            echo json_encode([
                "success" => true,
                "mensaje" => "Puestos encontrados.",
                "datos" => []
            ]);
            exit;
        }

        try {
            $db = new Database();
            $r = $db->queryAll(
                "
                SELECT 
                    p.id AS id_puesto, p.nombre AS puesto_nombre, '' AS descripcion, p.departamento_id AS id_departamento
                FROM __SPARTA_SECRET_REDACTED__.puesto p
                WHERE p.departamento_id = :id_departamento
                ORDER BY p.nivel DESC, p.id ASC
            ",
                ['id_departamento' => $id_dep]
            );
            $datos = is_array($r) ? $r : [];

            echo json_encode([
                "success" => true,
                "mensaje" => "Puestos encontrados.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    public static function UpdateNombrePuesto($id_puesto, $nombre)
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $id_puesto = (int) $id_puesto;
            $nombre = trim((string) $nombre);
            if ($id_puesto <= 0 || $nombre === '') {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Nombre e ID de puesto son obligatorios.",
                    "datos" => []
                ]);
                exit;
            }

            $puesto = $db->queryOne(
                "SELECT id, departamento_id FROM __SPARTA_SECRET_REDACTED__.puesto WHERE id = :id_puesto LIMIT 1",
                ['id_puesto' => $id_puesto]
            );
            $id_departamento = (int) ($puesto['departamento_id'] ?? 0);
            if (!$puesto || $id_departamento <= 0) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "No se encontró el puesto a actualizar.",
                    "datos" => []
                ]);
                exit;
            }

            $duplicado = $db->queryOne(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.puesto
                 WHERE departamento_id = :id_departamento
                   AND id <> :id_puesto
                   AND COALESCE(activo, 1) = 1
                   AND LOWER(TRIM(nombre)) = LOWER(TRIM(:nombre))
                 LIMIT 1",
                ['id_departamento' => $id_departamento, 'id_puesto' => $id_puesto, 'nombre' => $nombre]
            );
            if ($duplicado) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Ya existe un puesto llamado \"{$nombre}\" en este departamento.",
                    "datos" => []
                ]);
                exit;
            }

            $clave = self::generarClavePuestoUnica($db, $nombre, $id_departamento, $id_puesto);
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.puesto SET nombre = :nombre, clave = :clave WHERE id = :id_puesto",
                ['nombre' => $nombre, 'clave' => $clave, 'id_puesto' => $id_puesto]
            );
            $r = true;
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Puesto actualizado.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit; // <- Muy importante: evita que se imprima algo extra
    }

    /**
     * Actualiza el orden de los puestos reasignando la columna nivel.
     * Primer puesto de la lista = mayor nivel (dept*1000+999), último = dept*1000+1.
     * @param int $id_departamento
     * @param array $ordenes Array de id_puesto en el orden deseado (índice 0 = primero)
     */
    public static function UpdateOrdenPuestos($id_departamento, $ordenes)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $id_departamento = (int) $id_departamento;
            if (!$id_departamento || !is_array($ordenes)) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Datos inválidos.',
                    'datos' => []
                ]);
                exit;
            }

            $db = new Database();
            foreach ($ordenes as $pos => $id_puesto) {
                $id_puesto = (int) $id_puesto;
                if ($id_puesto <= 0) continue;
                $nivel = max(1, 999 - (int) $pos);
                $db->CRUD(
                    "UPDATE __SPARTA_SECRET_REDACTED__.puesto SET nivel = :nivel WHERE id = :id_puesto AND departamento_id = :id_departamento",
                    ['nivel' => $nivel, 'id_puesto' => $id_puesto, 'id_departamento' => $id_departamento]
                );
            }

            echo json_encode([
                'success' => true,
                'mensaje' => 'Orden actualizado.',
                'datos' => []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al actualizar orden: ' . $e->getMessage(),
                'datos' => []
            ]);
        }
        exit;
    }

    public static function InsertPuestos($nombre, $id_departamento)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $nombre = trim((string) $nombre);
            $id_departamento = (int) $id_departamento;
            if ($nombre === '' || $id_departamento <= 0) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Nombre e ID de departamento son obligatorios.',
                    'datos' => [],
                ]);
                exit;
            }

            $db = new Database();

            $duplicado = $db->queryOne(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.puesto
                 WHERE departamento_id = :id_departamento
                   AND COALESCE(activo, 1) = 1
                   AND LOWER(TRIM(nombre)) = LOWER(TRIM(:nombre))
                 LIMIT 1",
                ['id_departamento' => $id_departamento, 'nombre' => $nombre]
            );
            if ($duplicado) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => "Ya existe un puesto llamado \"{$nombre}\" en este departamento.",
                    'datos' => [],
                ]);
                exit;
            }

            $clave = self::generarClavePuestoUnica($db, $nombre, $id_departamento);

            // Sin columna id: deja que AUTO_INCREMENT asigne (evita fallos con NULL en id en modo estricto).
            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.puesto (clave, nombre, nivel, activo, departamento_id, es_jefe, descripcion) VALUES (:clave, :nombre, 0, 1, :id_departamento, 1, NULL)",
                ['clave' => $clave, 'nombre' => $nombre, 'id_departamento' => $id_departamento]
            );

            $newId = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id_puesto = (int) ($newId['id'] ?? 0);
            if ($id_puesto <= 0) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'No se obtuvo el ID del puesto insertado.',
                    'datos' => []
                ]);
                exit;
            }

            // Rebalancear niveles: todos los puestos del departamento en rango dept*1000+1 .. dept*1000+999
            // Orden actual: nivel DESC (el nuevo tiene 0, queda último). Asignar 11999, 11998, ... 11001
            $rows = $db->queryAll(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.puesto WHERE departamento_id = :id_departamento ORDER BY nivel DESC, id ASC",
                ['id_departamento' => $id_departamento]
            );
            $ordenes = is_array($rows) ? array_column($rows, 'id') : [];
            foreach ($ordenes as $pos => $id) {
                $nivel = max(1, 999 - (int) $pos);
                $id = (int) $id;
                $db->CRUD(
                    "UPDATE __SPARTA_SECRET_REDACTED__.puesto SET nivel = :nivel WHERE id = :id AND departamento_id = :id_departamento",
                    ['nivel' => $nivel, 'id' => $id, 'id_departamento' => $id_departamento]
                );
            }

            echo json_encode([
                'success' => true,
                'mensaje' => 'Puesto insertado.',
                'datos' => ['id_puesto' => $id_puesto],
                'id_puesto' => $id_puesto
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al procesar la solicitud: ' . $e->getMessage(),
                'datos' => [],
                'error' => $e->getMessage(),
            ]);
        }

        exit; // <- Muy importante: evita que se imprima algo extra
    }

    public static function InsertDepartamento($nombre, $id_pais = 1, $id_departamento_organizacional = null)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $id_pais = (int) $id_pais;
            if ($id_pais < 1) $id_pais = 1;
            $id_departamento_organizacional = $id_departamento_organizacional !== null && $id_departamento_organizacional !== ''
                ? (int) $id_departamento_organizacional
                : null;

            $nombre = trim($nombre);
            if (empty($nombre)) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "El nombre del área es requerido.",
                    "datos" => []
                ]);
                exit;
            }

            if (!$id_departamento_organizacional) {
                echo json_encode([
                    "success" => false,
                    "mensaje" => "El departamento organizacional es requerido.",
                    "datos" => []
                ]);
                exit;
            }

            $existe = $db->queryOne(
                "SELECT id FROM __SPARTA_SECRET_REDACTED__.departamento 
                 WHERE LOWER(TRIM(nombre)) = LOWER(:nombre) AND id_pais = :id_pais AND id_departamento_organizacional = :id_departamento_organizacional",
                ['nombre' => $nombre, 'id_pais' => $id_pais, 'id_departamento_organizacional' => $id_departamento_organizacional]
            );

            if ($existe) {
                $pais = $db->queryOne("SELECT nombre FROM paises WHERE id = :id", ['id' => $id_pais]);
                $paisNombre = $pais['nombre'] ?? 'el país seleccionado';
                echo json_encode([
                    "success" => false,
                    "mensaje" => "Ya existe un área llamada \"{$nombre}\" en {$paisNombre}.",
                    "datos" => []
                ]);
                exit;
            }

            $db->CRUD(
                "INSERT INTO __SPARTA_SECRET_REDACTED__.departamento (id, nombre, activo, img_url, id_pais, id_departamento_organizacional) VALUES (null, :nombre, 1, NULL, :id_pais, :id_departamento_organizacional)",
                ['nombre' => $nombre, 'id_pais' => $id_pais, 'id_departamento_organizacional' => $id_departamento_organizacional]
            );

            echo json_encode([
                "success" => true,
                "mensaje" => "Área insertada correctamente.",
                "datos" => []
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit;
    }

    public static function UpdateNombreDepartamento($id_departamento, $nombre)
    {
        // Cabecera JSON
        header('Content-Type: application/json; charset=utf-8');

        try {
            $db = new Database();
            $id_departamento = (int) $id_departamento;
            $db->CRUD(
                "UPDATE __SPARTA_SECRET_REDACTED__.departamento SET nombre = :nombre WHERE id = :id_departamento",
                ['nombre' => $nombre, 'id_departamento' => $id_departamento]
            );
            $r = true;
            $datos = is_array($r) ? $r : [];

            // echo JSON puro y nada más
            echo json_encode([
                "success" => true,
                "mensaje" => "Departamento actualizado.",
                "datos" => $datos
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                "success" => false,
                "mensaje" => "Error al procesar la solicitud.",
                "datos" => [],
                "error" => $e->getMessage()
            ]);
        }

        exit; // <- Muy importante: evita que se imprima algo extra
    }

    /**
     * Elimina un departamento y sus puestos. Solo permite si no tiene personal asignado (total_personas = 0).
     */
    public static function eliminarDepartamento($id_departamento)
    {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int) $id_departamento;
        if ($id < 1) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de departamento inválido.', 'datos' => []]);
            exit;
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT COUNT(DISTINCT a.id_persona) AS total_personas
                 FROM departamento d
                 LEFT JOIN puesto p ON p.departamento_id = d.id
                 LEFT JOIN asigna_puesto a ON a.id_puesto = p.id
                 WHERE d.id = :id",
                ['id' => $id]
            );
            $totalPersonas = (int) ($row['total_personas'] ?? 0);
            if ($totalPersonas > 0) {
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'No se puede eliminar: el departamento tiene personal asignado. Reasigne o dé de baja al personal primero.',
                    'datos' => []
                ]);
                exit;
            }
            // Quitar asignaciones de puestos de este departamento (por si acaso)
            $db->queryOne("DELETE a FROM __SPARTA_SECRET_REDACTED__.asigna_puesto a INNER JOIN __SPARTA_SECRET_REDACTED__.puesto p ON p.id = a.id_puesto WHERE p.departamento_id = $id");
            // Borrar puestos del departamento
            $db->queryOne("DELETE FROM __SPARTA_SECRET_REDACTED__.puesto WHERE departamento_id = $id");
            // Borrar departamento
            $db->queryOne("DELETE FROM __SPARTA_SECRET_REDACTED__.departamento WHERE id = $id");
            echo json_encode([
                'success' => true,
                'mensaje' => 'Departamento eliminado correctamente.',
                'datos' => []
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al eliminar el departamento.',
                'datos' => [],
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }

}
