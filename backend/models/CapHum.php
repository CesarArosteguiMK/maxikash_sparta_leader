<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\UsuarioFantasmaReporteria;

class CapHum extends Model
{
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
        $sqlExP = UsuarioFantasmaReporteria::sqlExcluirPersona('p');
        $sqlExP2 = UsuarioFantasmaReporteria::sqlExcluirPersona('p2');

        // =========================
        // VER TODOS: admin O sin departamento asignado (módulo 10)
        // Si no tiene "Organización > Departamentos" asignado → ver todos los usuarios.
        // =========================
        $verTodos = in_array($id_gestor_sesion, [1, 2, 3, 396, 797]) || !$tieneDepartamento;

        if ($verTodos) {

            $query = <<<SQL
            SELECT
            p.id,
            p.numero_empleado,
            p.nombres,
            p.segundo_nombre,
            p.apellidop,
            p.apellidom,

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

            CASE
                WHEN pj.id IS NULL THEN 'Sin jefe'
                ELSE CONCAT_WS(' ', pj.nombres, pj.segundo_nombre, pj.apellidop, pj.apellidom)
            END AS nombre_jefe,

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

        LEFT JOIN asigna_puesto ap
               ON p.id = ap.id_persona

        LEFT JOIN puesto pp
               ON pp.id = ap.id_puesto

        LEFT JOIN departamento d
               ON d.id = pp.departamento_id

        LEFT JOIN paises pais
               ON pais.id = p.id_pais

        LEFT JOIN (
            SELECT a.id_persona, a.id_jefe
            FROM asigna_jefe a
            INNER JOIN (
                SELECT id_persona, MAX(id) AS mid
                FROM asigna_jefe
                GROUP BY id_persona
            ) m ON a.id_persona = m.id_persona AND a.id = m.mid
        ) aj ON aj.id_persona = p.id

        LEFT JOIN persona pj
               ON pj.id = aj.id_jefe

        WHERE p.estatus != 'Baja'
        {$sqlExP}

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
                p.apellidop,
                p.apellidom,
                pp.id AS id_puesto,
                pp.nombre AS nombre_puesto,
                pp.nivel AS nivel_puesto,
                d.id AS id_departamento,
                d.nombre AS nombre_departamento,
                aj.id_jefe,
                p.estatus,
                COALESCE(pais.id, 0) AS id_pais,
                COALESCE(pais.nombre, 'Sin país') AS nombre_pais,
                COALESCE(pais.codigo_iso, 'xx') AS codigo_iso_pais,
                p.fecha_ingreso,
                p.fecha_registro,
                1 AS nivel
            FROM persona p
            LEFT JOIN asigna_puesto ap ON p.id = ap.id_persona
            LEFT JOIN puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN paises pais ON pais.id = p.id_pais
            LEFT JOIN asigna_jefe aj
                   ON p.id = aj.id_persona
                  AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            WHERE p.estatus != 'Baja'
              {$sqlExP}AND (
                    aj.id_jefe = $id_gestor_sesion
                    OR aj.id_jefe IS NULL
                  )

            UNION ALL

            -- =====================
            -- SUBORDINADOS
            -- =====================
            SELECT
                p2.id,
                p2.nombres,
                p2.apellidop,
                p2.apellidom,
                pp2.id AS id_puesto,
                pp2.nombre AS nombre_puesto,
                pp2.nivel AS nivel_puesto,
                d2.id AS id_departamento,
                d2.nombre AS nombre_departamento,
                aj2.id_jefe,
                p2.estatus,
                COALESCE(pais2.id, 0) AS id_pais,
                COALESCE(pais2.nombre, 'Sin país') AS nombre_pais,
                COALESCE(pais2.codigo_iso, 'xx') AS codigo_iso_pais,
                p2.fecha_ingreso,
                p2.fecha_registro,
                j.nivel + 1 AS nivel
            FROM persona p2
            LEFT JOIN asigna_puesto ap2 ON p2.id = ap2.id_persona
            LEFT JOIN puesto pp2 ON pp2.id = ap2.id_puesto
            LEFT JOIN departamento d2 ON d2.id = pp2.departamento_id
            LEFT JOIN paises pais2 ON pais2.id = p2.id_pais
            LEFT JOIN asigna_jefe aj2
                   ON p2.id = aj2.id_persona
                  AND (aj2.fecha_fin IS NULL OR aj2.fecha_fin >= CURDATE())
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
            $r = $db->queryAll($query);
            return self::resultado(true, 'Departamentos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
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
            $whereConditions[] = "(SELECT COUNT(*) FROM asigna_puesto ap2 WHERE ap2.id_persona = p.id) > 1";
        } elseif ($multipuesto === 'unico') {
            $whereConditions[] = "(SELECT COUNT(*) FROM asigna_puesto ap2 WHERE ap2.id_persona = p.id) = 1";
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

        LEFT JOIN asigna_puesto ap ON p.id = ap.id_persona
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
                ap.id_puesto, dd.nombre as departamento, dd.id as id_departamento, aj.id_jefe, p.password,
                al.id_legion
            FROM persona p
            LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id
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

            // Obtener documentos activos desde la base de datos
            $documentos = $db->queryAll("
                SELECT id, nombre, clave
                FROM __SPARTA_SECRET_REDACTED__.documentos
                WHERE activo = 1
                ORDER BY nombre
            ");

            return self::resultado(true, 'Tipos de documentos encontrados.', $documentos ?? []);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener tipos de documentos.', [], $e->getMessage());
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
                FROM __SPARTA_SECRET_REDACTED__.documentos
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
                FROM __SPARTA_SECRET_REDACTED__.documentos
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

            $query = <<<SQL
            SELECT
                p.*
            FROM persona p
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
            ORDER BY d.id, p.nivel desc
        SQL;

            $query_asignacion_actual = <<<SQL
            SELECT
                d.nombre AS nombre_departamento,
                pp.nombre AS nombre_puesto
            FROM asigna_puesto ap
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN departamento d ON d.id = pp.departamento_id
            WHERE ap.id_persona = $idPersona
            ORDER BY pp.nivel ASC
            LIMIT 1
        SQL;


            $persona = $db->queryOne($query);
            $perfiles = $db->queryAll($query_perfiles);
            require_once __DIR__ . '/../config/menu_modulos_sidebar.php';
            $perfiles = enriquecerPerfilesModulosConMenuSidebar($perfiles);
            $puestos = $db->queryAll($query_puestos);
            $asignacionActual = $db->queryOne($query_asignacion_actual);

            return self::resultado(true, 'Persona encontrada.', [
                'persona' => $persona,
                'perfiles' => $perfiles,
                'puestos' => $puestos,
                'asignacion_actual' => $asignacionActual
            ]);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
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
            $n = $db->CRUD(
                "UPDATE persona SET force_logout = 1 WHERE id = :id AND estatus != 'Baja'",
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
            pu.es_jefe = 1 AND per.estatus != 'Baja'
            AND {$predPer}
            AND (
                pu.departamento_id = $id_departamento
                OR pu.id IN (8)
            )
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
          WHERE per.estatus != 'Baja'
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
          LEFT JOIN asigna_puesto ap ON ap.id_persona = per.id
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
        INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
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
        $complet = $perfil_id > 0 ? 'WHERE pd.idPersona = ' . $perfil_id : '';

        $query = <<<SQL
           SELECT DISTINCT d.*
            FROM privilegios_departamento pd
            INNER JOIN puesto p
                    ON p.id = pd.idPuesto
            INNER JOIN departamento d
                    ON d.id = p.departamento_id
            $complet
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
        $autoNumeroEmpleado = trim((string) ($data['numero_empleado'] ?? '')) === '';
        $correo = addslashes((string) ($data['correo'] ?? ''));
        $telefono_uno = addslashes((string) ($data['telefono'] ?? $data['telefono_uno'] ?? ''));
        $telefono_dos = addslashes((string) ($data['telefono_dos'] ?? ''));
        $estatus = addslashes((string) ($data['estatus'] ?? 'Activo'));
        $id_puesto = (int) ($data['id_puesto'] ?? 0);
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

            if ($autoNumeroEmpleado) {
                $numero_raw = self::siguienteNumeroEmpleadoLibre($db);
            } else {
                $numero_raw = trim((string) ($data['numero_empleado'] ?? ''));
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

            $id_persona = isset($result['id']) ? intval($result['id']) : null;

            // Si no tiene jefe, él mismo será su jefe
            $id_jefe = isset($data['id_jefe']) && $data['id_jefe'] !== ''
                ? (int)$data['id_jefe']
                : $id_persona;

            if ($result)
            {
                $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_puesto
                        (id, id_persona, id_puesto, fecha_asignacion, activo)
                    VALUES
                        (DEFAULT, $id_persona, $id_puesto, NOW(), 1)
                ");

                            $db->queryOne("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.asigna_jefe
                        (id, id_persona, id_jefe, fecha_inicio, fecha_fin)
                    VALUES
                        (DEFAULT, $id_persona, $id_jefe, NOW(), NOW())
                ");

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
        $nombres         = addslashes($data['nombres']);
        $segundo_nombre  = addslashes($data['segundo_nombre'] ?? '');
        $apellidop       = addslashes($data['apellidop']);
        $apellidom       = addslashes($data['apellidom']);
        $correo          = addslashes($data['correo'] ?? '');
        $telefono_uno    = addslashes($data['telefono_uno'] ?? $data['telefono'] ?? '');
        $id_jefe         = (int)$data['jefe_id'];
        $id_puesto       = (int)$data['puesto_id'];
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

        try {
            $db = new Database();

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
            $existeJefe = $db->queryOne("
            SELECT id
            FROM asigna_jefe
            WHERE id_persona = $id_persona
            LIMIT 1
        ");

            if ($existeJefe) {
                $db->queryOne("
                UPDATE asigna_jefe
                SET id_jefe = $id_jefe
                WHERE id_persona = $id_persona
            ");
            } else {
                $db->queryOne("
                INSERT INTO asigna_jefe (id_persona, id_jefe)
                VALUES ($id_persona, $id_jefe)
            ");
            }

            // 3️⃣ ASIGNA PUESTO(S) - Manejo de múltiples puestos
            // Si viene el array puestos_adicionales, usamos ese; si no, usamos el puesto_id tradicional
            $puestosAdicionales = $data['puestos_adicionales'] ?? null;

            if ($puestosAdicionales && is_array($puestosAdicionales) && count($puestosAdicionales) > 0) {
                // Eliminar todos los puestos actuales
                $db->queryOne("DELETE FROM asigna_puesto WHERE id_persona = $id_persona");

                // Insertar cada puesto del array
                foreach ($puestosAdicionales as $puesto) {
                    $puestoId = (int)$puesto['id_puesto'];
                    $db->queryOne("
                        INSERT INTO asigna_puesto (id_persona, id_puesto)
                        VALUES ($id_persona, $puestoId)
                    ");
                }
            } else {
                // Comportamiento tradicional (un solo puesto)
                $existePuesto = $db->queryOne("
                    SELECT id
                    FROM asigna_puesto
                    WHERE id_persona = $id_persona
                    LIMIT 1
                ");

                if ($existePuesto) {
                    $db->queryOne("
                        UPDATE asigna_puesto
                        SET id_puesto = $id_puesto
                        WHERE id_persona = $id_persona
                    ");
                } else {
                    $db->queryOne("
                        INSERT INTO asigna_puesto (id_persona, id_puesto)
                        VALUES ($id_persona, $id_puesto)
                    ");
                }
            }

            // 4️⃣ ASIGNA LEGIÓN
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

            // Auto-sincronizar despachos según los puestos actualizados
            $idCelulaDespacho = null;
            if ($puestosAdicionales && is_array($puestosAdicionales) && count($puestosAdicionales) > 0) {
                foreach ($puestosAdicionales as $pObj) {
                    $cel = self::resolverCelulaDespacho($db, (int)($pObj['id_puesto'] ?? 0));
                    if ($cel !== null) {
                        $idCelulaDespacho = $cel;
                        break;
                    }
                }
            } else {
                $idCelulaDespacho = self::resolverCelulaDespacho($db, $id_puesto);
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

            return self::resultado(true, 'Persona actualizada correctamente.', null);

        } catch (\Exception $e) {
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

            return self::resultado(true, 'Baja registrada correctamente con archivos.');

        } catch (\Exception $e) {
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
        return self::resultado(true, 'Estados encontrados.', $r);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener estados.', null, $e->getMessage());
    }
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
        return self::resultado(true, 'Municipios encontrados.', $r);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener municipios.', null, $e->getMessage());
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

}
