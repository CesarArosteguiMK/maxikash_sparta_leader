<?php

namespace Models;

use Core\Model;
use Core\Database;

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
        // =========================
        // VER TODOS: admin O sin departamento asignado (módulo 10)
        // Si no tiene "Configuración > Departamentos" asignado → ver todos los usuarios.
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
            END AS usuario
        
        FROM persona p
        
        LEFT JOIN asigna_puesto ap 
               ON p.id = ap.id_persona
        
        LEFT JOIN puesto pp 
               ON pp.id = ap.id_puesto
        
        LEFT JOIN departamento d 
               ON d.id = pp.departamento_id
        
        --  JEFE: una asignación por persona (la fila más reciente por id; si tiene fecha_fin pasada se muestra igual)
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
                1 AS nivel
            FROM persona p
            LEFT JOIN asigna_puesto ap ON p.id = ap.id_persona
            LEFT JOIN puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN departamento d ON d.id = pp.departamento_id
            LEFT JOIN asigna_jefe aj 
                   ON p.id = aj.id_persona
                  AND (aj.fecha_fin IS NULL OR aj.fecha_fin >= CURDATE())
            WHERE p.estatus != 'Baja'
              AND (
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
                j.nivel + 1 AS nivel
            FROM persona p2
            LEFT JOIN asigna_puesto ap2 ON p2.id = ap2.id_persona
            LEFT JOIN puesto pp2 ON pp2.id = ap2.id_puesto
            LEFT JOIN departamento d2 ON d2.id = pp2.departamento_id
            LEFT JOIN asigna_jefe aj2 
                   ON p2.id = aj2.id_persona
                  AND (aj2.fecha_fin IS NULL OR aj2.fecha_fin >= CURDATE())
            JOIN Jerarquia j 
                 ON aj2.id_jefe = j.id
            WHERE p2.estatus != 'Baja'
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
        $whereConditions = ["p.estatus != 'Baja'"];

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
            COALESCE(p.user_name, 'Sin usuario') AS usuario
        
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
                    // 2️⃣ Insertar si no existe
                    $queryInsert = <<<SQL
                    INSERT INTO asigna_modulo_web (usuario_id, modulo_web_id)
                    VALUES ($idPersona, $moduloId)
                SQL;

                    $db->queryOne($queryInsert);
                }

                return self::resultado(
                    true,
                    'Módulo asignado correctamente'
                );

            } else {

                // 3️⃣ Eliminar asignación
                $queryDelete = <<<SQL
                DELETE FROM asigna_modulo_web
                WHERE usuario_id = $idPersona
                  AND modulo_web_id = $moduloId
            SQL;

                $db->queryOne($queryDelete);

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
            $rutaArchivo = __DIR__ . '/../uploads/bajas/' . $nombreArchivo;
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
                15 => 'bajas', // Documento baja
                'default' => 'documentos' // Otros documentos
            ];
            
            $carpeta = $carpetas[$id_documento] ?? $carpetas['default'];
            $rutaArchivo = __DIR__ . '/../uploads/' . $carpeta . '/' . $nombreArchivo;
            
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
                m.nombre AS modulo_nombre,
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
                ON m.id = a.modulo_web_id
                AND a.usuario_id = $idPersona
            WHERE m.activo = 1
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



            $persona = $db->queryOne($query);
            $perfiles = $db->queryAll($query_perfiles);
            $puestos = $db->queryAll($query_puestos);

            return self::resultado(true, 'Persona encontrada.', [
                'persona' => $persona,
                'perfiles' => $perfiles,
                'puestos' => $puestos
            ]);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al procesar la solicitud.', null, $e->getMessage());
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
        $query = <<<SQL
          SELECT DISTINCT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.nombre AS nombre_puesto
          FROM asigna_puesto ap
          INNER JOIN persona per ON per.id = ap.id_persona
          INNER JOIN puesto pu ON pu.id = ap.id_puesto
          WHERE per.estatus != 'Baja'
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
        $query = <<<SQL
          SELECT
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            COALESCE(pu.nombre, '') AS nombre_puesto
          FROM persona per
          LEFT JOIN asigna_puesto ap ON ap.id_persona = per.id
          LEFT JOIN puesto pu ON pu.id = ap.id_puesto
          WHERE per.estatus != 'Baja'
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
            //$nivelMax = max(array_column($puestos, 'nivel'));

            //$puestosTop = array_filter($puestos, function ($p) use ($nivelMax) {
            //    return $p['nivel'] == $nivelMax;
            //});
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

                $queryPersonas = <<<SQL
                SELECT 
                p.id,
                CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre,
                ap.id_puesto
            FROM persona p
            INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
            INNER JOIN puesto pp ON pp.id = ap.id_puesto
            WHERE ap.id_puesto IN ($placeholdersStr)
              AND p.estatus != 'Baja'
            ORDER BY 
                pp.nivel DESC,
                nombre ASC
        SQL;

                $personas = $db->queryAll($queryPersonas, $params);

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
            $filtroDepto = " AND p.id IN (SELECT ap_in.id_persona FROM asigna_puesto ap_in INNER JOIN puesto pp_in ON pp_in.id = ap_in.id_puesto WHERE pp_in.departamento_id = $id_departamento)";
        }
        $filtroDepto2 = '';
        if ($id_departamento > 0) {
            $filtroDepto2 = " AND p2.id IN (SELECT ap_in.id_persona FROM asigna_puesto ap_in INNER JOIN puesto pp_in ON pp_in.id = ap_in.id_puesto WHERE pp_in.departamento_id = $id_departamento)";
        }

        $query = <<<SQL
               WITH RECURSIVE Jerarquia AS (

                -- NIVEL 1: un solo puesto por persona; opcionalmente solo personas con puesto en el departamento
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
                JOIN (SELECT id_persona, MIN(id_puesto) AS id_puesto FROM asigna_puesto GROUP BY id_persona) ap ON p.id = ap.id_persona
                JOIN puesto pp ON pp.id = ap.id_puesto
                JOIN (SELECT id_persona, MIN(id_jefe) AS id_jefe FROM asigna_jefe GROUP BY id_persona) aj ON p.id = aj.id_persona
                WHERE p.estatus != 'Baja'
                  AND aj.id_jefe = $id_persona
                  $filtroDepto
            
                UNION ALL
            
                -- NIVELES 2–4: un solo puesto y un solo jefe por persona; opcionalmente solo personas del departamento
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
                JOIN (SELECT id_persona, MIN(id_puesto) AS id_puesto FROM asigna_puesto GROUP BY id_persona) ap2 ON p2.id = ap2.id_persona
                JOIN puesto pp2 ON pp2.id = ap2.id_puesto
                JOIN (SELECT id_persona, MIN(id_jefe) AS id_jefe FROM asigna_jefe GROUP BY id_persona) aj2 ON p2.id = aj2.id_persona
                JOIN Jerarquia j ON aj2.id_jefe = j.id
                WHERE p2.estatus != 'Baja'
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
                    INNER JOIN asigna_puesto ap ON ap.id_persona = p.id
                    INNER JOIN puesto pp ON pp.id = ap.id_puesto
                    WHERE p.id = $id_persona
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
    ////////////////////////////////////////ES EL DE ADMIN
    public static function getConsultaDepartamentoGestor($perfil_id)
    {
        if($perfil_id == 1 OR $perfil_id == 2 OR $perfil_id == 3 OR $perfil_id == 396){
            $complet = '';
        }
        else
        {
            $complet = 'WHERE pd.idPersona = ' . $perfil_id;

        }
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


    public static function insertPersona($data)
    {
        // 🔹 Escapamos valores
        $nombres = addslashes($data['nombres']);
        $segundo_nombre = addslashes($data['segundo_nombre'] ?? '');
        $apellidop = addslashes($data['apellidop']);
        $apellidom = addslashes($data['apellidom']);
        $numero_empleado = addslashes($data['numero_empleado']);
        $correo = addslashes($data['correo'] ?? '');
        $telefono_uno = addslashes($data['telefono'] ?? $data['telefono_uno'] ?? '');
        $telefono_dos = addslashes($data['telefono_dos'] ?? '');
        $estatus = addslashes($data['estatus'] ?? 'Activo');
        $id_puesto = addslashes($data['id_puesto']);
        $id_jefe = addslashes($data['id_jefe']);
        $user_name = addslashes($data['usuario']);
        $password = addslashes($data['contrasena']);
        $fecha_ingreso = !empty($data['fecha_ingreso']) ? addslashes($data['fecha_ingreso']) : null;


        try {
            $db = new Database();

            $fecha_ingreso_sql = $fecha_ingreso !== null ? "'$fecha_ingreso'" : 'NULL';

            // Fecha y hora de CDMX para fecha_registro
            $tz = new \DateTimeZone('America/Mexico_City');
            $fechaRegistro = (new \DateTime('now', $tz))->format('Y-m-d H:i:s');
            $fechaRegistro = addslashes($fechaRegistro);

            // 1️⃣ Ejecutamos INSERT con queryOne() (fecha_registro = hora CDMX)
            $db->queryOne("
            INSERT INTO __SPARTA_SECRET_REDACTED__.persona
            (nombres, segundo_nombre, apellidop, apellidom, numero_empleado, correo, telefono_uno, telefono_dos, estatus, user_name, password, fecha_ingreso, fecha_registro)
            VALUES
            ('$nombres', '$segundo_nombre', '$apellidop', '$apellidom', '$numero_empleado', '$correo', '$telefono_uno', '$telefono_dos', '$estatus', '$user_name', '$password', $fecha_ingreso_sql, '$fechaRegistro')
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
                
                // 4️⃣ Asignar legión si se marcó el checkbox
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

        try {
            $db = new Database();

            // 1️⃣ UPDATE PERSONA
            $db->queryOne("
            UPDATE __SPARTA_SECRET_REDACTED__.persona
            SET 
                nombres       = '$nombres',
                segundo_nombre = '$segundo_nombre',
                apellidop     = '$apellidop',
                apellidom     = '$apellidom',
                correo        = '$correo',
                telefono_uno  = '$telefono_uno',
                user_name     = '$user_name',
                password      = '$password'
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

            return self::resultado(true, 'Persona actualizada correctamente.', null);

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar persona.', null, $e->getMessage());
        }
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

            return self::resultado(true, 'Baja registrada correctamente con archivos.');

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar la baja.', null, $e->getMessage());
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
                
                // 8) Bajas
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.baja_persona WHERE id_persona = $id");
                
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

    public static function getConsultaBajas($fecha_inicio = null, $fecha_fin = null)
    {
        $query = <<<SQL
        SELECT
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
        LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON p.id = ap.id_persona
        LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON pu.departamento_id = d.id
        WHERE p.estatus = 'Baja'
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

       public static function getConsultaBajasAvanzado($filtros = [])
    {
        $query = <<<SQL
        SELECT
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
        LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON p.id = ap.id_persona
        LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON pu.departamento_id = d.id
        WHERE p.estatus = 'Baja'
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

}
