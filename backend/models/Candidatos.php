<?php

namespace Models;

use Core\Model;
use Core\Database;

class Candidatos extends Model
{
    private static function columnaExiste(Database $db, string $tabla, string $columna): bool
    {
        try {
            $row = $db->queryOne(
                "SELECT 1 AS ok
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = :tabla
                   AND COLUMN_NAME = :columna
                 LIMIT 1",
                ['tabla' => $tabla, 'columna' => $columna]
            );
            return !empty($row);
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function asegurarColumnasFlujoIngreso(Database $db): void
    {
        $columnas = [
            'id_jefe_divisional' => "INT NULL AFTER id_posible_jefe",
            'fecha_ingreso_programada' => "DATE NULL AFTER fecha_postulacion_enviada",
            'fecha_ingreso_notificada_en' => "DATETIME NULL AFTER fecha_ingreso_programada",
            'contrato_firmado_en' => "DATETIME NULL AFTER fecha_ingreso_notificada_en",
            'sueldo_bruto' => "DECIMAL(12,2) NULL AFTER contrato_firmado_en",
            'sueldo_neto' => "DECIMAL(12,2) NULL AFTER sueldo_bruto",
            'motivo_contratacion' => "VARCHAR(500) NULL AFTER sueldo_neto",
            'codigo_postal' => "VARCHAR(12) NULL AFTER domicilio_num_interior",
            'proceso_cerrado' => "TINYINT(1) NOT NULL DEFAULT 0 AFTER notas",
            'motivo_cierre' => "VARCHAR(100) NULL AFTER proceso_cerrado",
            'descripcion_cierre' => "TEXT NULL AFTER motivo_cierre",
            'fecha_cierre' => "DATETIME NULL AFTER descripcion_cierre",
        ];

        foreach ($columnas as $nombre => $definicion) {
            if (self::columnaExiste($db, 'candidatos', $nombre)) {
                continue;
            }
            try {
                $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.candidatos ADD COLUMN {$nombre} {$definicion}");
            } catch (\Exception $e) {
            }
        }
    }

    private static function candidatoTokenTieneColumnaExpira(Database $db): bool
    {
        try {
            $row = $db->queryOne(
                "SELECT COUNT(*) AS c
                 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'candidato_documento_token'
                   AND COLUMN_NAME = 'expira'"
            );
            return (int) ($row['c'] ?? 0) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function completarCodigoPostalDesdeColonia(Database $db, array &$params): void
    {
        if (!empty($params['codigo_postal']) || empty($params['id_div_nivel3'])) {
            return;
        }
        try {
            $row = $db->queryOne(
                "SELECT NULLIF(TRIM(codigo_interno), '') AS codigo_postal
                 FROM __SPARTA_SECRET_REDACTED__.divisiones_administrativas
                 WHERE id = :id AND activo = 1
                 LIMIT 1",
                ['id' => (int) $params['id_div_nivel3']]
            );
            if (!empty($row['codigo_postal'])) {
                $params['codigo_postal'] = substr(trim((string) $row['codigo_postal']), 0, 12);
            }
        } catch (\Exception $e) {
        }
    }

    private static function storageRoot(): string
    {
        return defined('RAIZ') ? (RAIZ . '/storage') : (__DIR__ . '/../storage');
    }

    private static function storageRoots(): array
    {
        $candidatos = [];
        if (defined('RAIZ')) {
            $candidatos[] = RAIZ . '/storage';
            $candidatos[] = dirname(RAIZ) . '/storage';
        }
        $candidatos[] = dirname(__DIR__) . '/storage';
        $candidatos[] = dirname(__DIR__, 2) . '/storage';

        $roots = [];
        foreach ($candidatos as $ruta) {
            $normalizada = rtrim(str_replace('\\', '/', (string) $ruta), '/');
            if ($normalizada !== '' && !in_array($normalizada, $roots, true)) {
                $roots[] = $normalizada;
            }
        }
        return $roots;
    }

    private static function resolverRutaStorageDocumento(string $rutaRelativa): ?string
    {
        $rutaRelativa = str_replace('\\', '/', trim($rutaRelativa));
        if ($rutaRelativa === '') {
            return null;
        }
        if (preg_match('/^[A-Za-z]:\//', $rutaRelativa) || strpos($rutaRelativa, '/') === 0) {
            return is_file($rutaRelativa) ? $rutaRelativa : null;
        }
        $rutaRelativa = ltrim($rutaRelativa, '/');
        if (stripos($rutaRelativa, 'storage/') === 0) {
            $rutaRelativa = substr($rutaRelativa, 8);
        }
        foreach (self::storageRoots() as $storageRoot) {
            $posible = $storageRoot . '/' . $rutaRelativa;
            if (is_file($posible)) {
                return $posible;
            }
        }
        return null;
    }

    private static function documentoTieneArchivoDisponible(array $doc): bool
    {
        $ruta = trim((string) ($doc['ruta_archivo'] ?? ''));
        if ($ruta !== '' && self::resolverRutaStorageDocumento($ruta) !== null) {
            return true;
        }
        return !empty($doc['tiene_contenido']);
    }

    private static function filtrarDocumentosConArchivo(array $documentos): array
    {
        $filtrados = [];
        foreach ($documentos as $doc) {
            $ruta = trim((string) ($doc['ruta_archivo'] ?? ''));
            if ($ruta !== '' && !self::documentoTieneArchivoDisponible($doc)) {
                continue;
            }
            $filtrados[] = $doc;
        }

        return $filtrados;
    }

    /**
     * Listar todos los candidatos con puesto y departamento de interés.
     */
    public static function getAll($estatus = null, $id_departamento = null, $id_puesto = null, $id_posible_jefe = null)
    {
        $query = <<<SQL
            SELECT
                c.id,
                c.nombres,
                c.segundo_nombre,
                c.apellidop,
                c.apellidom,
                c.email,
                c.telefono,
                COALESCE(c.id_div_nivel1, div2_padre.id, div3_estado.id) AS id_div_nivel1,
                COALESCE(c.id_div_nivel2, div3_municipio.id) AS id_div_nivel2,
                c.id_div_nivel3,
                c.domicilio_calle_texto,
                c.domicilio_num_exterior,
                c.domicilio_num_interior,
                c.codigo_postal,
                c.id_puesto,
                c.id_departamento,
                c.id_posible_jefe,
                c.id_jefe_divisional,
                c.estatus,
                c.notas,
                c.estatus,
                c.notas,
                c.postulacion_enviada,
                c.fecha_ingreso_programada,
                c.fecha_ingreso_notificada_en,
                c.contrato_firmado_en,
                c.sueldo_bruto,
                c.sueldo_neto,
                c.motivo_contratacion,
                c.fecha_registro,
                c.fecha_actualizacion,
                pais.nombre AS nombre_pais,
                COALESCE(div1.nombre, div2_padre.nombre, div3_estado.nombre) AS nombre_div_nivel1,
                COALESCE(div2.nombre, div3_municipio.nombre) AS nombre_div_nivel2,
                div3.nombre AS nombre_div_nivel3,
                p.nombre AS nombre_puesto,
                d.nombre AS nombre_departamento,
                COALESCE(dir.id, 0) AS id_direccion,
                COALESCE(dir.nombre, '') AS nombre_direccion,
                TRIM(CONCAT_WS(' ', jefe.nombres, jefe.segundo_nombre, jefe.apellidop, jefe.apellidom)) AS nombre_jefe,
                TRIM(CONCAT_WS(' ', jefe_divisional.nombres, jefe_divisional.segundo_nombre, jefe_divisional.apellidop, jefe_divisional.apellidom)) AS nombre_jefe_divisional,
                jefe_divisional.correo AS correo_jefe_divisional
            FROM candidatos c
            LEFT JOIN paises pais ON pais.id = c.id_pais
            LEFT JOIN divisiones_administrativas div1 ON div1.id = c.id_div_nivel1
            LEFT JOIN divisiones_administrativas div2 ON div2.id = c.id_div_nivel2
            LEFT JOIN divisiones_administrativas div3 ON div3.id = c.id_div_nivel3
            LEFT JOIN divisiones_administrativas div2_padre ON div2_padre.id = div2.id_padre AND div2_padre.nivel = 1
            LEFT JOIN divisiones_administrativas div3_municipio ON div3_municipio.id = div3.id_padre AND div3_municipio.nivel = 2
            LEFT JOIN divisiones_administrativas div3_estado ON div3_estado.id = div3_municipio.id_padre AND div3_estado.nivel = 1
            LEFT JOIN puesto p ON p.id = c.id_puesto
            LEFT JOIN departamento d ON d.id = c.id_departamento
            LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = d.id_departamento_organizacional AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
            LEFT JOIN persona jefe ON jefe.id = c.id_posible_jefe
            LEFT JOIN persona jefe_divisional ON jefe_divisional.id = c.id_jefe_divisional
            WHERE 1=1
        SQL;
        $params = [];

        $query .= " AND COALESCE(c.estatus, '') <> 'Contratado'";
        if (is_array($estatus)) {
            $estatusPermitidos = array_values(array_filter(array_unique(array_map('trim', $estatus)), static function ($valor) {
                return $valor !== '' && $valor !== 'Contratado';
            }));
            if (!empty($estatusPermitidos)) {
                $ph = [];
                foreach ($estatusPermitidos as $i => $valor) {
                    $key = 'estatus_' . $i;
                    $ph[] = ':' . $key;
                    $params[$key] = $valor;
                }
                $query .= " AND c.estatus IN (" . implode(',', $ph) . ")";
            }
        } elseif ($estatus !== null && $estatus !== '' && $estatus !== 'Contratado') {
            $query .= " AND c.estatus = :estatus";
            $params['estatus'] = $estatus;
        }
        if ($id_departamento !== null && $id_departamento !== '') {
            $query .= " AND c.id_departamento = :id_departamento";
            $params['id_departamento'] = (int) $id_departamento;
        }
        if ($id_puesto !== null && $id_puesto !== '') {
            $query .= " AND c.id_puesto = :id_puesto";
            $params['id_puesto'] = (int) $id_puesto;
        }
        if ($id_posible_jefe !== null && $id_posible_jefe !== '') {
            $query .= " AND c.id_posible_jefe = :id_posible_jefe";
            $params['id_posible_jefe'] = (int) $id_posible_jefe;
        }

        $query .= " ORDER BY c.fecha_registro DESC";

        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            $r = $db->queryAll($query, $params);
            return self::resultado(true, 'Candidatos encontrados.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener candidatos.', null, $e->getMessage());
        }
    }

    /**
     * Obtener un candidato por ID.
     */
    public static function getById($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        $query = <<<SQL
            SELECT
                c.id,
                c.nombres,
                c.segundo_nombre,
                c.apellidop,
                c.apellidom,
                c.email,
                c.telefono,
                c.id_pais,
                COALESCE(c.id_div_nivel1, div2_padre.id, div3_estado.id) AS id_div_nivel1,
                COALESCE(c.id_div_nivel2, div3_municipio.id) AS id_div_nivel2,
                c.id_div_nivel3,
                c.domicilio_calle_texto,
                c.domicilio_num_exterior,
                c.domicilio_num_interior,
                c.codigo_postal,
                c.id_puesto,
                c.id_departamento,
                c.id_posible_jefe,
                c.id_jefe_divisional,
                c.fecha_postulacion,
                c.id_legion,
                c.usuario,
                c.contrasena,
                c.estatus,
                c.notas,
                c.postulacion_enviada,
                c.fecha_postulacion_enviada,
                c.fecha_ingreso_programada,
                c.fecha_ingreso_notificada_en,
                c.contrato_firmado_en,
                c.sueldo_bruto,
                c.sueldo_neto,
                c.motivo_contratacion,
                c.fecha_registro,
                c.fecha_actualizacion,
                pais.nombre AS nombre_pais,
                COALESCE(div1.nombre, div2_padre.nombre, div3_estado.nombre) AS nombre_div_nivel1,
                COALESCE(div2.nombre, div3_municipio.nombre) AS nombre_div_nivel2,
                div3.nombre AS nombre_div_nivel3,
                p.nombre AS nombre_puesto,
                d.nombre AS nombre_departamento,
                COALESCE(dir.id, 0) AS id_direccion,
                COALESCE(dir.nombre, '') AS nombre_direccion,
                TRIM(CONCAT_WS(' ', jefe.nombres, jefe.segundo_nombre, jefe.apellidop, jefe.apellidom)) AS nombre_jefe,
                jefe.correo AS correo_jefe,
                TRIM(CONCAT_WS(' ', jefe_divisional.nombres, jefe_divisional.segundo_nombre, jefe_divisional.apellidop, jefe_divisional.apellidom)) AS nombre_jefe_divisional,
                jefe_divisional.correo AS correo_jefe_divisional
            FROM candidatos c
            LEFT JOIN paises pais ON pais.id = c.id_pais
            LEFT JOIN divisiones_administrativas div1 ON div1.id = c.id_div_nivel1
            LEFT JOIN divisiones_administrativas div2 ON div2.id = c.id_div_nivel2
            LEFT JOIN divisiones_administrativas div3 ON div3.id = c.id_div_nivel3
            LEFT JOIN divisiones_administrativas div2_padre ON div2_padre.id = div2.id_padre AND div2_padre.nivel = 1
            LEFT JOIN divisiones_administrativas div3_municipio ON div3_municipio.id = div3.id_padre AND div3_municipio.nivel = 2
            LEFT JOIN divisiones_administrativas div3_estado ON div3_estado.id = div3_municipio.id_padre AND div3_estado.nivel = 1
            LEFT JOIN puesto p ON p.id = c.id_puesto
            LEFT JOIN departamento d ON d.id = c.id_departamento
            LEFT JOIN departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
            LEFT JOIN asigna_direcciones ad ON ad.id_departamento_organizacional = d.id_departamento_organizacional AND COALESCE(ad.activo, 1) = 1
            LEFT JOIN direcciones_organizacion dir ON dir.id = ad.id_direccion
            LEFT JOIN persona jefe ON jefe.id = c.id_posible_jefe
            LEFT JOIN persona jefe_divisional ON jefe_divisional.id = c.id_jefe_divisional
            WHERE c.id = :id
        SQL;
        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            $r = $db->queryOne($query, ['id' => $id]);
            if (!$r) {
                return self::resultado(false, 'Candidato no encontrado.', null);
            }
            return self::resultado(true, 'Candidato encontrado.', $r);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener candidato.', null, $e->getMessage());
        }
    }

    /**
     * Fecha y hora actual en Ciudad de México (valor guardado como datetime “naive” interpretado siempre en CDMX).
     */
    private static function fechaHoraActualMexicoCiudad(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City')))->format('Y-m-d H:i:s');
    }

    private static function ensureTablaBitacoraCandidato(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $db = new Database();
            $db->CRUD(
                "CREATE TABLE IF NOT EXISTS candidato_bitacora (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_candidato INT NOT NULL,
                    evento VARCHAR(90) NOT NULL,
                    titulo VARCHAR(180) NOT NULL,
                    descripcion TEXT NULL,
                    detalle_json LONGTEXT NULL,
                    id_usuario INT NULL,
                    fecha_registro DATETIME NOT NULL,
                    INDEX idx_cb_candidato_fecha (id_candidato, fecha_registro),
                    INDEX idx_cb_evento (evento),
                    INDEX idx_cb_usuario (id_usuario)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (\Exception $e) {
        }
    }

    private static function ensureTablaHistoricoCandidato(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $db = new Database();
            $db->CRUD(
                "CREATE TABLE IF NOT EXISTS candidato_historico (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_candidato_original INT NOT NULL,
                    nombre_completo VARCHAR(255) NULL,
                    email VARCHAR(255) NULL,
                    telefono VARCHAR(50) NULL,
                    puesto VARCHAR(180) NULL,
                    departamento VARCHAR(180) NULL,
                    ubicacion VARCHAR(500) NULL,
                    estatus_final VARCHAR(80) NOT NULL,
                    motivo VARCHAR(180) NULL,
                    descripcion TEXT NULL,
                    snapshot_json LONGTEXT NULL,
                    id_usuario_accion INT NULL,
                    fecha_creacion DATETIME NULL,
                    fecha_accion DATETIME NOT NULL,
                    UNIQUE KEY uq_ch_candidato_estado (id_candidato_original, estatus_final),
                    INDEX idx_ch_fecha (fecha_accion),
                    INDEX idx_ch_estatus (estatus_final)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (\Exception $e) {
        }
    }

    private static function bitacoraEventoExiste($db, int $id_candidato, string $evento): bool
    {
        try {
            $row = $db->queryOne(
                "SELECT id FROM candidato_bitacora WHERE id_candidato = :id AND evento = :evento LIMIT 1",
                ['id' => $id_candidato, 'evento' => $evento]
            );
            return !empty($row);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function registrarBitacoraCandidato($id_candidato, $evento, $titulo, $descripcion = null, array $detalle = [], $id_usuario = null, $fechaHoraCdmxMysql = null): void
    {
        $id_candidato = (int) $id_candidato;
        $evento = strtoupper(trim((string) $evento));
        $titulo = trim((string) $titulo);
        if ($id_candidato <= 0 || $evento === '' || $titulo === '') {
            return;
        }
        self::ensureTablaBitacoraCandidato();
        try {
            $db = new Database();
            $fecha = trim((string) ($fechaHoraCdmxMysql ?: self::fechaHoraActualMexicoCiudad()));
            $db->CRUD(
                "INSERT INTO candidato_bitacora
                    (id_candidato, evento, titulo, descripcion, detalle_json, id_usuario, fecha_registro)
                 VALUES
                    (:id_candidato, :evento, :titulo, :descripcion, :detalle_json, :id_usuario, :fecha_registro)",
                [
                    'id_candidato' => $id_candidato,
                    'evento' => substr($evento, 0, 90),
                    'titulo' => substr($titulo, 0, 180),
                    'descripcion' => $descripcion !== null ? trim((string) $descripcion) : null,
                    'detalle_json' => !empty($detalle) ? json_encode($detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
                    'id_usuario' => $id_usuario !== null && (int) $id_usuario > 0 ? (int) $id_usuario : null,
                    'fecha_registro' => $fecha !== '' ? $fecha : self::fechaHoraActualMexicoCiudad(),
                ]
            );
        } catch (\Exception $e) {
        }
    }

    public static function registrarBitacoraCandidatoUnaVez($id_candidato, $evento, $titulo, $descripcion = null, array $detalle = [], $id_usuario = null, $fechaHoraCdmxMysql = null): void
    {
        $id_candidato = (int) $id_candidato;
        $evento = strtoupper(trim((string) $evento));
        if ($id_candidato <= 0 || $evento === '') {
            return;
        }
        self::ensureTablaBitacoraCandidato();
        try {
            $db = new Database();
            if (self::bitacoraEventoExiste($db, $id_candidato, $evento)) {
                return;
            }
        } catch (\Exception $e) {
        }
        self::registrarBitacoraCandidato($id_candidato, $evento, $titulo, $descripcion, $detalle, $id_usuario, $fechaHoraCdmxMysql);
    }

    public static function guardarSnapshotHistoricoCandidato($id_candidato, $estatus_final, $motivo = null, $descripcion = null, $id_usuario_accion = null, $fechaHoraCdmxMysql = null): void
    {
        $id_candidato = (int) $id_candidato;
        $estatus_final = trim((string) $estatus_final);
        if ($id_candidato <= 0 || $estatus_final === '') {
            return;
        }
        self::ensureTablaHistoricoCandidato();
        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            $row = $db->queryOne(
                "SELECT c.*, p.nombre AS nombre_puesto, d.nombre AS nombre_departamento,
                        pais.nombre AS nombre_pais, div1.nombre AS nombre_div_nivel1,
                        div2.nombre AS nombre_div_nivel2, div3.nombre AS nombre_div_nivel3
                 FROM candidatos c
                 LEFT JOIN puesto p ON p.id = c.id_puesto
                 LEFT JOIN departamento d ON d.id = c.id_departamento
                 LEFT JOIN paises pais ON pais.id = c.id_pais
                 LEFT JOIN divisiones_administrativas div1 ON div1.id = c.id_div_nivel1
                 LEFT JOIN divisiones_administrativas div2 ON div2.id = c.id_div_nivel2
                 LEFT JOIN divisiones_administrativas div3 ON div3.id = c.id_div_nivel3
                 WHERE c.id = :id
                 LIMIT 1",
                ['id' => $id_candidato]
            );
            if (!$row) {
                return;
            }
            $nombre = trim(implode(' ', array_filter([
                $row['nombres'] ?? '',
                $row['segundo_nombre'] ?? '',
                $row['apellidop'] ?? '',
                $row['apellidom'] ?? '',
            ])));
            $ubicacion = implode(' / ', array_filter([
                $row['nombre_pais'] ?? '',
                $row['nombre_div_nivel1'] ?? '',
                $row['nombre_div_nivel2'] ?? '',
                $row['nombre_div_nivel3'] ?? '',
            ]));
            $fechaAccion = trim((string) ($fechaHoraCdmxMysql ?: self::fechaHoraActualMexicoCiudad()));
            $db->CRUD(
                "INSERT INTO candidato_historico
                    (id_candidato_original, nombre_completo, email, telefono, puesto, departamento, ubicacion, estatus_final, motivo, descripcion, snapshot_json, id_usuario_accion, fecha_creacion, fecha_accion)
                 VALUES
                    (:id_candidato_original, :nombre_completo, :email, :telefono, :puesto, :departamento, :ubicacion, :estatus_final, :motivo, :descripcion, :snapshot_json, :id_usuario_accion, :fecha_creacion, :fecha_accion)
                 ON DUPLICATE KEY UPDATE
                    nombre_completo = VALUES(nombre_completo),
                    email = VALUES(email),
                    telefono = VALUES(telefono),
                    puesto = VALUES(puesto),
                    departamento = VALUES(departamento),
                    ubicacion = VALUES(ubicacion),
                    motivo = VALUES(motivo),
                    descripcion = VALUES(descripcion),
                    snapshot_json = VALUES(snapshot_json),
                    id_usuario_accion = VALUES(id_usuario_accion),
                    fecha_creacion = VALUES(fecha_creacion),
                    fecha_accion = VALUES(fecha_accion)",
                [
                    'id_candidato_original' => $id_candidato,
                    'nombre_completo' => $nombre !== '' ? $nombre : null,
                    'email' => trim((string) ($row['email'] ?? '')) ?: null,
                    'telefono' => trim((string) ($row['telefono'] ?? '')) ?: null,
                    'puesto' => trim((string) ($row['nombre_puesto'] ?? '')) ?: null,
                    'departamento' => trim((string) ($row['nombre_departamento'] ?? '')) ?: null,
                    'ubicacion' => $ubicacion !== '' ? $ubicacion : null,
                    'estatus_final' => substr($estatus_final, 0, 80),
                    'motivo' => $motivo !== null ? substr(trim((string) $motivo), 0, 180) : null,
                    'descripcion' => $descripcion !== null ? trim((string) $descripcion) : null,
                    'snapshot_json' => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'id_usuario_accion' => $id_usuario_accion !== null && (int) $id_usuario_accion > 0 ? (int) $id_usuario_accion : null,
                    'fecha_creacion' => $row['fecha_registro'] ?? null,
                    'fecha_accion' => $fechaAccion,
                ]
            );
        } catch (\Exception $e) {
        }
    }

    /**
     * Registra el momento en que se envió por correo la postulación/enlace de documentos (hora CDMX).
     */
    public static function registrarFechaEnvioCorreoPostulacion($id_candidato, $fechaHoraCdmxMysql)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || $fechaHoraCdmxMysql === null || trim((string) $fechaHoraCdmxMysql) === '') {
            return;
        }
        try {
            $db = new Database();
            $fe = trim((string) $fechaHoraCdmxMysql);
            $db->CRUD(
                'UPDATE candidatos SET fecha_postulacion_enviada = :fe, postulacion_enviada = 1, fecha_actualizacion = :fe WHERE id = :id',
                ['id' => $id_candidato, 'fe' => $fe]
            );
            self::registrarBitacoraCandidato($id_candidato, 'CORREO_DOCUMENTOS_ENVIADO', 'Correo de documentos enviado', 'Se envió el enlace para que el candidato cargue su documentación.', [], null, $fe);
        } catch (\Exception $e) {
        }
    }

    public static function registrarIngresoProgramado($id_candidato, $fechaIngreso, $fechaHoraCdmxMysql = null): void
    {
        $id_candidato = (int) $id_candidato;
        $fechaIngreso = trim((string) $fechaIngreso);
        if ($id_candidato <= 0 || $fechaIngreso === '') {
            return;
        }
        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            $fechaHora = trim((string) ($fechaHoraCdmxMysql ?: self::fechaHoraActualMexicoCiudad()));
            $db->CRUD(
                "UPDATE candidatos
                 SET fecha_ingreso_programada = :fecha_ingreso,
                     fecha_ingreso_notificada_en = :fecha_hora,
                     estatus = 'Ingreso programado',
                     fecha_actualizacion = :fecha_hora
                 WHERE id = :id",
                ['id' => $id_candidato, 'fecha_ingreso' => $fechaIngreso, 'fecha_hora' => $fechaHora]
            );
        } catch (\Exception $e) {
        }
    }

    public static function marcarContratoFirmado($id_candidato, $fechaHoraCdmxMysql = null): void
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            $fechaHora = trim((string) ($fechaHoraCdmxMysql ?: self::fechaHoraActualMexicoCiudad()));
            $db->CRUD(
                "UPDATE candidatos
                 SET contrato_firmado_en = :fecha_hora,
                     fecha_actualizacion = :fecha_hora
                 WHERE id = :id",
                ['id' => $id_candidato, 'fecha_hora' => $fechaHora]
            );
            self::registrarBitacoraCandidatoUnaVez($id_candidato, 'CONTRATO_FIRMADO', 'Contrato firmado', 'El candidato confirmó la firma/alta del proceso.', [], null, $fechaHora);
        } catch (\Exception $e) {
        }
    }

    /**
     * Insertar nuevo candidato (con postulación enviada y datos de postulación).
     */
    public static function insert($data)
    {
        $nombres = trim($data['nombres'] ?? '');
        $apellidop = trim($data['apellidop'] ?? '');
        if ($nombres === '' || $apellidop === '') {
            return self::resultado(false, 'Nombres y apellido paterno son obligatorios.', null);
        }

        $postulacionEnviada = !empty($data['postulacion_enviada']) ? 1 : 0;
        $fechaEnviada = $postulacionEnviada ? self::fechaHoraActualMexicoCiudad() : null;

        $query = <<<SQL
            INSERT INTO candidatos (
                nombres, segundo_nombre, apellidop, apellidom,
                email, telefono, id_pais, id_div_nivel1, id_div_nivel2, id_div_nivel3, domicilio_calle_texto, domicilio_num_exterior, domicilio_num_interior, codigo_postal,
                id_puesto, id_departamento, id_posible_jefe, id_jefe_divisional,
                fecha_postulacion, id_legion, usuario, contrasena,
                postulacion_enviada, fecha_postulacion_enviada, estatus, notas, fecha_registro
            ) VALUES (
                :nombres, :segundo_nombre, :apellidop, :apellidom,
                :email, :telefono, :id_pais, :id_div_nivel1, :id_div_nivel2, :id_div_nivel3, :domicilio_calle_texto, :domicilio_num_exterior, :domicilio_num_interior, :codigo_postal,
                :id_puesto, :id_departamento, :id_posible_jefe, :id_jefe_divisional,
                :fecha_postulacion, :id_legion, :usuario, :contrasena,
                :postulacion_enviada, :fecha_postulacion_enviada, :estatus, :notas, :fecha_registro
            )
        SQL;
        $params = [
            'nombres' => $nombres,
            'segundo_nombre' => trim($data['segundo_nombre'] ?? '') ?: null,
            'apellidop' => $apellidop,
            'apellidom' => trim($data['apellidom'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
            'id_pais' => !empty($data['id_pais']) ? (int) $data['id_pais'] : null,
            'id_div_nivel1' => !empty($data['id_div_nivel1']) ? (int) $data['id_div_nivel1'] : null,
            'id_div_nivel2' => !empty($data['id_div_nivel2']) ? (int) $data['id_div_nivel2'] : null,
            'id_div_nivel3' => !empty($data['id_div_nivel3']) ? (int) $data['id_div_nivel3'] : null,
            'domicilio_calle_texto' => trim($data['domicilio_calle_texto'] ?? '') ?: null,
            'domicilio_num_exterior' => trim($data['domicilio_num_exterior'] ?? '') ?: null,
            'domicilio_num_interior' => trim($data['domicilio_num_interior'] ?? '') ?: null,
            'codigo_postal' => substr(trim($data['codigo_postal'] ?? ''), 0, 12) ?: null,
            'id_puesto' => !empty($data['id_puesto']) ? (int) $data['id_puesto'] : null,
            'id_departamento' => !empty($data['id_departamento']) ? (int) $data['id_departamento'] : null,
            'id_posible_jefe' => !empty($data['id_posible_jefe']) ? (int) $data['id_posible_jefe'] : null,
            'id_jefe_divisional' => !empty($data['id_jefe_divisional']) ? (int) $data['id_jefe_divisional'] : null,
            'fecha_postulacion' => !empty($data['fecha_postulacion']) ? $data['fecha_postulacion'] : null,
            'id_legion' => !empty($data['id_legion']) ? (int) $data['id_legion'] : null,
            'usuario' => trim($data['usuario'] ?? '') ?: null,
            'contrasena' => trim($data['contrasena'] ?? '') ?: null,
            'postulacion_enviada' => $postulacionEnviada,
            'fecha_postulacion_enviada' => $fechaEnviada,
            'estatus' => trim($data['estatus'] ?? '') ?: 'Por evaluar',
            'notas' => trim($data['notas'] ?? '') ?: null,
            'fecha_registro' => self::fechaHoraActualMexicoCiudad(),
        ];

        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            self::completarCodigoPostalDesdeColonia($db, $params);
            $db->CRUD($query, $params);
            $newId = $db->queryOne("SELECT LAST_INSERT_ID() AS id");
            $id = (int) ($newId['id'] ?? 0);
            if ($id > 0) {
                self::registrarBitacoraCandidatoUnaVez($id, 'CANDIDATO_CREADO', 'Candidato creado', 'Se dio de alta el candidato en Selección de Personal.', [], null, $params['fecha_registro']);
                if ($fechaEnviada) {
                    self::registrarBitacoraCandidato($id, 'CORREO_DOCUMENTOS_ENVIADO', 'Correo de documentos enviado', 'Se envió el enlace para que el candidato cargue su documentación.', [], null, $fechaEnviada);
                }
            }
            return self::resultado(true, 'Candidato registrado correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al registrar candidato.', null, $e->getMessage());
        }
    }

    /**
     * Actualizar candidato.
     */
    public static function update($id, $data)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }

        $nombres = trim($data['nombres'] ?? '');
        $apellidop = trim($data['apellidop'] ?? '');
        if ($nombres === '' || $apellidop === '') {
            return self::resultado(false, 'Nombres y apellido paterno son obligatorios.', null);
        }

        try {
            $dbActual = new Database();
            self::asegurarColumnasFlujoIngreso($dbActual);
            $actual = $dbActual->queryOne(
                "SELECT
                    id_pais, id_div_nivel1, id_div_nivel2, id_div_nivel3,
                    domicilio_calle_texto, domicilio_num_exterior, domicilio_num_interior, codigo_postal,
                    nombres, segundo_nombre, apellidop, apellidom,
                    id_puesto, id_departamento, id_posible_jefe, id_jefe_divisional, fecha_postulacion,
                    id_legion, usuario, contrasena, notas
                 FROM candidatos
                 WHERE id = :id
                 LIMIT 1",
                ['id' => $id]
            );
            if (!$actual) {
                return self::resultado(false, 'Candidato no encontrado.', null);
            }
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar candidato.', null, $e->getMessage());
        }

        $usarActualIntSiVacio = static function (string $campo) use ($data, $actual) {
            if (array_key_exists($campo, $data)) {
                $valor = trim((string) ($data[$campo] ?? ''));
                if ($valor !== '') {
                    return (int) $valor;
                }
            }
            return !empty($actual[$campo]) ? (int) $actual[$campo] : null;
        };

        $usarActualTextoSiVacio = static function (string $campo, ?int $max = null) use ($data, $actual) {
            if (array_key_exists($campo, $data)) {
                $valor = trim((string) ($data[$campo] ?? ''));
                if ($valor !== '') {
                    return $max !== null ? substr($valor, 0, $max) : $valor;
                }
            }

            $valorActual = trim((string) ($actual[$campo] ?? ''));
            if ($valorActual === '') {
                return null;
            }
            return $max !== null ? substr($valorActual, 0, $max) : $valorActual;
        };

        $query = <<<SQL
            UPDATE candidatos SET
                nombres = :nombres,
                segundo_nombre = :segundo_nombre,
                apellidop = :apellidop,
                apellidom = :apellidom,
                email = :email,
                telefono = :telefono,
                id_pais = :id_pais,
                id_div_nivel1 = :id_div_nivel1,
                id_div_nivel2 = :id_div_nivel2,
                id_div_nivel3 = :id_div_nivel3,
                domicilio_calle_texto = :domicilio_calle_texto,
                domicilio_num_exterior = :domicilio_num_exterior,
                domicilio_num_interior = :domicilio_num_interior,
                codigo_postal = :codigo_postal,
                id_puesto = :id_puesto,
                id_departamento = :id_departamento,
                id_posible_jefe = :id_posible_jefe,
                id_jefe_divisional = :id_jefe_divisional,
                fecha_postulacion = :fecha_postulacion,
                id_legion = :id_legion,
                usuario = :usuario,
                contrasena = :contrasena,
                notas = :notas,
                fecha_actualizacion = :fecha_actualizacion
            WHERE id = :id
        SQL;
        $params = [
            'id' => $id,
            'nombres' => $nombres,
            'segundo_nombre' => trim($data['segundo_nombre'] ?? '') ?: null,
            'apellidop' => $apellidop,
            'apellidom' => trim($data['apellidom'] ?? '') ?: null,
            'email' => trim($data['email'] ?? '') ?: null,
            'telefono' => trim($data['telefono'] ?? '') ?: null,
            'id_pais' => $usarActualIntSiVacio('id_pais'),
            'id_div_nivel1' => $usarActualIntSiVacio('id_div_nivel1'),
            'id_div_nivel2' => $usarActualIntSiVacio('id_div_nivel2'),
            'id_div_nivel3' => $usarActualIntSiVacio('id_div_nivel3'),
            'domicilio_calle_texto' => $usarActualTextoSiVacio('domicilio_calle_texto'),
            'domicilio_num_exterior' => $usarActualTextoSiVacio('domicilio_num_exterior'),
            'domicilio_num_interior' => $usarActualTextoSiVacio('domicilio_num_interior'),
            'codigo_postal' => $usarActualTextoSiVacio('codigo_postal', 12),
            'id_puesto' => $usarActualIntSiVacio('id_puesto'),
            'id_departamento' => $usarActualIntSiVacio('id_departamento'),
            'id_posible_jefe' => $usarActualIntSiVacio('id_posible_jefe'),
            'id_jefe_divisional' => array_key_exists('id_jefe_divisional', $data)
                ? (!empty($data['id_jefe_divisional']) ? (int) $data['id_jefe_divisional'] : null)
                : (!empty($actual['id_jefe_divisional']) ? (int) $actual['id_jefe_divisional'] : null),
            'fecha_postulacion' => $usarActualTextoSiVacio('fecha_postulacion'),
            'id_legion' => array_key_exists('id_legion', $data)
                ? (!empty($data['id_legion']) ? (int) $data['id_legion'] : null)
                : (!empty($actual['id_legion']) ? (int) $actual['id_legion'] : null),
            'usuario' => $usarActualTextoSiVacio('usuario'),
            'contrasena' => $usarActualTextoSiVacio('contrasena'),
            'notas' => array_key_exists('notas', $data)
                ? (trim((string) ($data['notas'] ?? '')) ?: null)
                : ($actual['notas'] ?? null),
            'fecha_actualizacion' => self::fechaHoraActualMexicoCiudad(),
        ];

        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            self::completarCodigoPostalDesdeColonia($db, $params);
            $db->CRUD($query, $params);
            $nombreAnterior = trim(implode(' ', array_filter([
                $actual['nombres'] ?? '',
                $actual['segundo_nombre'] ?? '',
                $actual['apellidop'] ?? '',
                $actual['apellidom'] ?? '',
            ])));
            $nombreNuevo = trim(implode(' ', array_filter([
                $params['nombres'] ?? '',
                $params['segundo_nombre'] ?? '',
                $params['apellidop'] ?? '',
                $params['apellidom'] ?? '',
            ])));
            $normalizarNombre = static function ($valor) {
                $valor = mb_strtoupper(trim((string) $valor), 'UTF-8');
                $valor = preg_replace('/\s+/', ' ', $valor);
                return $valor ?? '';
            };
            if ($normalizarNombre($nombreAnterior) !== $normalizarNombre($nombreNuevo)) {
                self::updateVerificacionExpediente($id, null);
            } else {
                self::invalidateDocumentacionCache($id);
            }
            return self::resultado(true, 'Candidato actualizado correctamente.', ['id' => $id]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar candidato.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar candidato.
     */
    public static function delete($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidatos WHERE id = :id", ['id' => $id]);
            return self::resultado(true, 'Candidato eliminado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar candidato.', null, $e->getMessage());
        }
    }

    /** Días hábiles para plazo de documentación (sección [mail] de config.ini). */
    private static function diasHabilesLimiteDocumentosDesdeIni(): int
    {
        $n = 2;
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (is_file($configFile)) {
            $full = @parse_ini_file($configFile, true);
            $mailSection = is_array($full['mail'] ?? null) ? $full['mail'] : [];
            $n = (int) ($mailSection['dias_habiles_limite_documentos'] ?? 2);
        }
        return $n >= 1 ? $n : 2;
    }

    /** Correo de contacto para mensaje de enlace vencido (misma lógica que CapHum::enviarPostulacionCandidato). */
    private static function mailContactoDocumentacion(): string
    {
        $contacto = '';
        $configFile = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../config/config.ini');
        if (is_file($configFile)) {
            $full = @parse_ini_file($configFile, true);
            $mailSection = is_array($full['mail'] ?? null) ? $full['mail'] : [];
            $contacto = trim($mailSection['mail_contacto'] ?? '');
            if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
                $contacto = trim($mailSection['smtp_user'] ?? $mailSection['mail_from'] ?? '');
            }
        }
        if ($contacto === '' || !filter_var($contacto, FILTER_VALIDATE_EMAIL)) {
            $contacto = 'reporteria__SPARTA_SECRET_REDACTED__@gmail.com';
        }

        return $contacto;
    }

    private static function zonaMexico(): \DateTimeZone
    {
        return new \DateTimeZone('America/Mexico_City');
    }

    private static function ahoraMexicoCiudadImmutable(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', self::zonaMexico());
    }

    private static function parseFechaHoraMexicoCiudad(?string $mysql): ?\DateTimeImmutable
    {
        if ($mysql === null || trim($mysql) === '') {
            return null;
        }
        $tz = self::zonaMexico();
        $raw = trim($mysql);
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $raw, $tz);
        if ($dt !== false) {
            return $dt;
        }
        try {
            return new \DateTimeImmutable($raw, $tz);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Fin de plazo para documentación: día calendario siguiente en CDMX + N días hábiles (lun–vie), cierre 23:59:59 CDMX.
     * Misma regla que CapHum::documentacionLimiteFinDesdeReferencia.
     */
    private static function documentacionLimiteFinDesdeReferencia(\DateTimeImmutable $referenciaCdmx, int $diasHabiles): \DateTimeImmutable
    {
        $tz = self::zonaMexico();
        $d = $referenciaCdmx->setTimezone($tz);
        $rest = max(1, $diasHabiles);
        while ($rest > 0) {
            $d = $d->modify('+1 day');
            $n = (int) $d->format('N');
            if ($n >= 1 && $n <= 5) {
                $rest--;
            }
        }

        return $d;
    }

    /**
     * Fecha/hora límite del token (misma base que el correo: referencia = fecha_postulacion_enviada si existe, si no fecha_registro, si no ahora).
     */
    public static function calcularExpiraTokenMysqlDesdeCandidato(array $c): string
    {
        $dias = self::diasHabilesLimiteDocumentosDesdeIni();
        $refStr = trim((string) ($c['fecha_postulacion_enviada'] ?? ''));
        if ($refStr === '') {
            $refStr = trim((string) ($c['fecha_registro'] ?? ''));
        }
        $ref = self::parseFechaHoraMexicoCiudad($refStr !== '' ? $refStr : null);
        if ($ref === null) {
            $ref = self::ahoraMexicoCiudadImmutable();
        }

        return self::documentacionLimiteFinDesdeReferencia($ref, $dias)->format('Y-m-d H:i:s');
    }

    /**
     * Actualiza la fecha de vencimiento del enlace de documentos (p. ej. al enviar el correo con fecha límite exacta).
     */
    public static function actualizarExpiraTokenDocumentos(int $id_candidato, string $expiraYmdHis): bool
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($expiraYmdHis) === '') {
            return false;
        }
        try {
            $db = new Database();
            $db->CRUD(
                'UPDATE candidato_documento_token SET expira = :e WHERE id_candidato = :id',
                ['e' => $expiraYmdHis, 'id' => $id_candidato]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener o crear token único para link de subida de documentos del candidato.
     * Retorna el token (string) para construir la URL.
     * La columna expira debe existir en la tabla candidato_documento (enlace de subida de documentos).
     */
    public static function getTokenDocumentosInfo(int $id_candidato): array
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }

        try {
            $db = new Database();
            $usaExpira = self::candidatoTokenTieneColumnaExpira($db);
            $row = $db->queryOne(
                $usaExpira
                    ? 'SELECT token, expira FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1'
                    : 'SELECT token FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1',
                ['id' => $id_candidato]
            );

            if (!$row || empty($row['token'])) {
                return self::resultado(false, 'Token no encontrado.', null);
            }

            $expiraMysql = $row['expira'] ?? null;
            if ($usaExpira && ($expiraMysql === null || trim((string) $expiraMysql) === '')) {
                $cand = $db->queryOne(
                    'SELECT id, fecha_postulacion_enviada, fecha_registro FROM candidatos WHERE id = :id LIMIT 1',
                    ['id' => $id_candidato]
                );
                if ($cand) {
                    $expiraMysql = self::calcularExpiraTokenMysqlDesdeCandidato($cand);
                    self::actualizarExpiraTokenDocumentos($id_candidato, $expiraMysql);
                }
            }

            $vencido = false;
            if ($usaExpira) {
                $limite = self::parseFechaHoraMexicoCiudad((string) $expiraMysql);
                $ahora = self::ahoraMexicoCiudadImmutable();
                $vencido = $limite instanceof \DateTimeImmutable ? ($ahora > $limite) : true;
            }

            return self::resultado(true, 'OK', [
                'token' => $row['token'],
                'expira' => $expiraMysql,
                'vencido' => $vencido,
                'usa_expira' => $usaExpira,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar token.', null, $e->getMessage());
        }
    }

    public static function reactivarTokenDocumentos(int $id_candidato, ?string $referenciaYmdHis = null): array
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }

        try {
            $db = new Database();
            $cand = $db->queryOne(
                'SELECT id, fecha_postulacion_enviada, fecha_registro FROM candidatos WHERE id = :id LIMIT 1',
                ['id' => $id_candidato]
            );
            if (!$cand) {
                return self::resultado(false, 'Candidato no encontrado.', null);
            }

            $usaExpira = self::candidatoTokenTieneColumnaExpira($db);
            $row = $db->queryOne(
                $usaExpira
                    ? 'SELECT token, expira FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1'
                    : 'SELECT token FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1',
                ['id' => $id_candidato]
            );

            $token = trim((string) ($row['token'] ?? ''));
            if ($token === '') {
                $token = bin2hex(random_bytes(32));
            }

            $ref = self::parseFechaHoraMexicoCiudad($referenciaYmdHis);
            if ($ref === null) {
                $ref = self::ahoraMexicoCiudadImmutable();
            }
            $expiraMysql = self::documentacionLimiteFinDesdeReferencia(
                $ref,
                self::diasHabilesLimiteDocumentosDesdeIni()
            )->format('Y-m-d H:i:s');

            if ($row && !empty($row['token'])) {
                if ($usaExpira) {
                    $db->CRUD(
                        'UPDATE candidato_documento_token SET expira = :expira WHERE id_candidato = :id',
                        ['expira' => $expiraMysql, 'id' => $id_candidato]
                    );
                }
            } elseif ($usaExpira) {
                $db->CRUD(
                    'INSERT INTO candidato_documento_token (id_candidato, token, expira) VALUES (:id, :token, :expira)',
                    ['id' => $id_candidato, 'token' => $token, 'expira' => $expiraMysql]
                );
            } else {
                $db->CRUD(
                    'INSERT INTO candidato_documento_token (id_candidato, token) VALUES (:id, :token)',
                    ['id' => $id_candidato, 'token' => $token]
                );
            }

            return self::resultado(true, 'Link reactivado.', [
                'token' => $token,
                'expira' => $expiraMysql,
                'vencido' => false,
                'usa_expira' => $usaExpira,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al reactivar link.', null, $e->getMessage());
        }
    }

    public static function getOrCreateTokenDocumentos($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }
        try {
            $db = new Database();
            $cand = $db->queryOne(
                'SELECT id, fecha_postulacion_enviada, fecha_registro FROM candidatos WHERE id = :id LIMIT 1',
                ['id' => $id_candidato]
            );
            if (!$cand) {
                return self::resultado(false, 'Candidato no encontrado.', null);
            }
            $expiraMysql = self::calcularExpiraTokenMysqlDesdeCandidato($cand);
            $usaExpira = self::candidatoTokenTieneColumnaExpira($db);
            $row = $db->queryOne(
                $usaExpira
                    ? 'SELECT token, expira FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1'
                    : 'SELECT token FROM candidato_documento_token WHERE id_candidato = :id LIMIT 1',
                ['id' => $id_candidato]
            );
            if ($row && !empty($row['token'])) {
                $expCol = $row['expira'] ?? null;
                if ($usaExpira && ($expCol === null || trim((string) $expCol) === '')) {
                    self::actualizarExpiraTokenDocumentos($id_candidato, $expiraMysql);
                }

                return self::resultado(true, 'Token existente.', $row['token']);
            }
            $token = bin2hex(random_bytes(32));
            if ($usaExpira) {
                $db->CRUD(
                    'INSERT INTO candidato_documento_token (id_candidato, token, expira) VALUES (:id_candidato, :token, :expira)',
                    ['id_candidato' => $id_candidato, 'token' => $token, 'expira' => $expiraMysql]
                );
            } else {
                $db->CRUD(
                    'INSERT INTO candidato_documento_token (id_candidato, token) VALUES (:id_candidato, :token)',
                    ['id_candidato' => $id_candidato, 'token' => $token]
                );
            }

            return self::resultado(true, 'Token generado.', $token);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al generar token.', null, $e->getMessage());
        }
    }

    /**
     * Obtener id_candidato y datos básicos a partir del token (para la vista pública de subida).
     * Rechaza tokens vencidos (expira en hora local CDMX, fin de día 23:59:59).
     */
    public static function getCandidatoPorToken($token)
    {
        $token = trim($token ?? '');
        if ($token === '') {
            return self::resultado(false, 'Token inválido.', null);
        }
        try {
            $db = new Database();
            $usaExpira = self::candidatoTokenTieneColumnaExpira($db);
            $row = $db->queryOne(
                $usaExpira
                    ? ('SELECT t.id_candidato, t.expira, c.nombres, c.apellidop, c.apellidom, c.email, c.fecha_postulacion_enviada, c.fecha_registro '
                        . 'FROM candidato_documento_token t INNER JOIN candidatos c ON c.id = t.id_candidato WHERE t.token = :token LIMIT 1')
                    : ('SELECT t.id_candidato, c.nombres, c.apellidop, c.apellidom, c.email, c.fecha_postulacion_enviada, c.fecha_registro '
                        . 'FROM candidato_documento_token t INNER JOIN candidatos c ON c.id = t.id_candidato WHERE t.token = :token LIMIT 1'),
                ['token' => $token]
            );
            if (!$row) {
                return self::resultado(false, 'Enlace no válido o expirado.', null);
            }
            $expiraMysql = $row['expira'] ?? null;
            if ($usaExpira && ($expiraMysql === null || trim((string) $expiraMysql) === '')) {
                $expiraMysql = self::calcularExpiraTokenMysqlDesdeCandidato($row);
                self::actualizarExpiraTokenDocumentos((int) $row['id_candidato'], $expiraMysql);
            }
            if ($usaExpira) {
                $limite = self::parseFechaHoraMexicoCiudad((string) $expiraMysql);
                $ahora = self::ahoraMexicoCiudadImmutable();
                if ($limite instanceof \DateTimeImmutable && $ahora > $limite) {
                    $mailCt = self::mailContactoDocumentacion();

                    return self::resultado(
                        false,
                        'Link expirado. Este enlace ya no está disponible para subir documentos. Solicite a Capital Humano la reactivación del enlace o escríbanos a '
                        . $mailCt . '.',
                        null
                    );
                }
            }
            unset($row['expira'], $row['fecha_postulacion_enviada'], $row['fecha_registro']);

            return self::resultado(true, 'Candidato encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al validar enlace.', null, $e->getMessage());
        }
    }

    /**
     * Crea token heredado de confirmacion externa del candidato.
     * Retorna token y expira en 7 días.
     */
    public static function createTokenConfirmacionAlta($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }
        try {
            $db = new Database();
            $token = bin2hex(random_bytes(32));
            $expira = self::ahoraMexicoCiudadImmutable()->modify('+7 days')->format('Y-m-d H:i:s');
            $db->CRUD(
                "INSERT INTO candidato_confirmacion_alta_token (token, id_candidato, expira) VALUES (:token, :id_candidato, :expira)",
                ['token' => $token, 'id_candidato' => $id_candidato, 'expira' => $expira]
            );
            return self::resultado(true, 'Token creado.', $token);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al crear token.', null, $e->getMessage());
        }
    }

    /**
     * Obtiene id_candidato por token heredado. Valido si no usado y no expirado.
     */
    public static function getPorTokenConfirmacionAlta($token)
    {
        $token = trim($token ?? '');
        if ($token === '') {
            return self::resultado(false, 'Enlace no válido.', null);
        }
        try {
            $db = new Database();
            $ahora = self::fechaHoraActualMexicoCiudad();
            $row = $db->queryOne(
                "SELECT id_candidato FROM candidato_confirmacion_alta_token WHERE token = :token AND usado = 0 AND expira > :ahora LIMIT 1",
                ['token' => $token, 'ahora' => $ahora]
            );
            if (!$row) {
                return self::resultado(false, 'Enlace no válido, ya usado o expirado.', null);
            }
            return self::resultado(true, 'OK', (int) $row['id_candidato']);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al validar enlace.', null, $e->getMessage());
        }
    }

    /**
     * Marca token heredado como usado (respuesta si o no).
     */
    public static function marcarTokenConfirmacionAltaUsado($token, $respuesta)
    {
        $token = trim($token ?? '');
        $respuesta = strtolower(trim($respuesta ?? '')) === 'si' ? 'si' : 'no';
        if ($token === '') {
            return false;
        }
        try {
            $db = new Database();
            $fechaUso = self::fechaHoraActualMexicoCiudad();
            $db->CRUD(
                "UPDATE candidato_confirmacion_alta_token SET usado = 1, respuesta = :respuesta, fecha_uso = :fecha_uso WHERE token = :token",
                ['respuesta' => $respuesta, 'fecha_uso' => $fechaUso, 'token' => $token]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Registrar un documento subido por el candidato (vía link).
     * Si se pasan $contenido y $mime_type, el archivo se guarda en la BD (contenido LONGBLOB)
     * y se sirve desde ahí para que cargue más rápido (como carga_documento_persona).
     *
     * @param string $tipo_documento Nombre del tipo (ej. SOLICITUD INTERNA, CURP, etc.)
     * @param string|null $contenido Contenido binario del archivo (opcional). Si se pasa, se guarda en BD.
     * @param string|null $mime_type application/pdf, image/jpeg, etc. (opcional, recomendado si hay contenido)
     * @param string|null $verificacion_fiscal_json JSON con resultado de verificación constancia fiscal (solo tipo CONSTANCIA DE SITUACION FISCAL)
     * @param string|null $verificacion_calidad_json JSON con notas de revisión para identificación oficial (ej. exceso de brillo)
     */
    public static function guardarDocumento($id_candidato, $nombre_archivo, $ruta_archivo, $tipo_documento = '', $contenido = null, $mime_type = null, $verificacion_fiscal_json = null, $verificacion_calidad_json = null)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($nombre_archivo ?? '') === '') {
            return self::resultado(false, 'Datos incompletos.');
        }
        if ($contenido === null && trim($ruta_archivo ?? '') === '') {
            return self::resultado(false, 'Indica ruta_archivo o contenido.');
        }
        try {
            $db = new Database();
            $fechaCarga = self::fechaHoraActualMexicoCiudad();
            if ($contenido !== null) {
                $ruta = trim($ruta_archivo ?? '');
                $sql = "INSERT INTO candidato_documento (id_candidato, tipo_documento, nombre_archivo, ruta_archivo, contenido, mime_type, verificacion_fiscal_json, verificacion_calidad_json, fecha_carga) VALUES (:id_candidato, :tipo_documento, :nombre_archivo, :ruta_archivo, :contenido, :mime_type, :verificacion_fiscal_json, :verificacion_calidad_json, :fecha_carga)";
                $params = [
                    'id_candidato' => $id_candidato,
                    'tipo_documento' => trim($tipo_documento ?? ''),
                    'nombre_archivo' => $nombre_archivo,
                    'ruta_archivo' => $ruta,
                    'contenido' => $contenido,
                    'mime_type' => $mime_type !== null ? trim($mime_type) : null,
                    'verificacion_fiscal_json' => $verificacion_fiscal_json,
                    'verificacion_calidad_json' => $verificacion_calidad_json,
                    'fecha_carga' => $fechaCarga,
                ];
                $db->queryOne($sql, $params);
            } else {
                $db->CRUD(
                    "INSERT INTO candidato_documento (id_candidato, tipo_documento, nombre_archivo, ruta_archivo, verificacion_fiscal_json, verificacion_calidad_json, fecha_carga) VALUES (:id_candidato, :tipo_documento, :nombre_archivo, :ruta_archivo, :verificacion_fiscal_json, :verificacion_calidad_json, :fecha_carga)",
                    [
                        'id_candidato' => $id_candidato,
                        'tipo_documento' => trim($tipo_documento ?? ''),
                        'nombre_archivo' => $nombre_archivo,
                        'ruta_archivo' => $ruta_archivo,
                        'verificacion_fiscal_json' => $verificacion_fiscal_json,
                        'verificacion_calidad_json' => $verificacion_calidad_json,
                        'fecha_carga' => $fechaCarga,
                    ]
                );
            }
            self::invalidateDocumentacionCache($id_candidato);
            $tipoBit = trim((string) ($tipo_documento ?? ''));
            self::registrarBitacoraCandidato($id_candidato, 'DOCUMENTO_CARGADO', 'Documento cargado', ($tipoBit !== '' ? $tipoBit : 'Documento') . ': ' . trim((string) $nombre_archivo), [
                'tipo_documento' => $tipoBit,
                'nombre_archivo' => trim((string) $nombre_archivo),
                'origen' => 'candidato',
            ], null, $fechaCarga);
            try {
                $conteoDocs = $db->queryOne(
                    "SELECT COUNT(*) AS total, MAX(fecha_carga) AS ultima_carga FROM candidato_documento WHERE id_candidato = :id",
                    ['id' => $id_candidato]
                );
                if ((int) ($conteoDocs['total'] ?? 0) >= 10) {
                    self::registrarBitacoraCandidatoUnaVez(
                        $id_candidato,
                        'EXPEDIENTE_COMPLETO',
                        'Expediente completo',
                        'El candidato cargó todos los documentos requeridos.',
                        ['total_documentos' => (int) ($conteoDocs['total'] ?? 0)],
                        null,
                        $conteoDocs['ultima_carga'] ?? null
                    );
                }
            } catch (\Exception $e) {
            }
            return self::resultado(true, 'Documento guardado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar documento.', null, $e->getMessage());
        }
    }

    /**
     * Actualizar resultados de verificación OCR/API de un documento ya guardado.
     */
    private static function ensureTablaSubidaManualDocumentoCandidato(): void
    {
        try {
            $db = new Database();
            $db->CRUD(
                "CREATE TABLE IF NOT EXISTS candidato_documento_subida_manual (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_candidato INT NOT NULL,
                    tipo_documento VARCHAR(120) NOT NULL,
                    nombre_archivo VARCHAR(255) NOT NULL,
                    ruta_archivo VARCHAR(500) NULL,
                    id_usuario_rrhh INT NULL,
                    motivo VARCHAR(500) NULL,
                    fecha_registro DATETIME NOT NULL,
                    INDEX idx_cdsm_candidato (id_candidato),
                    INDEX idx_cdsm_usuario (id_usuario_rrhh)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (\Exception $e) {
        }
    }

    public static function registrarSubidaManualDocumentoCandidato($id_candidato, $tipo_documento, $nombre_archivo, $ruta_archivo, $id_usuario_rrhh = null, $motivo = null)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID invalido.');
        }
        self::ensureTablaSubidaManualDocumentoCandidato();
        try {
            $db = new Database();
            $fechaRegistro = self::fechaHoraActualMexicoCiudad();
            $db->CRUD(
                "INSERT INTO candidato_documento_subida_manual
                    (id_candidato, tipo_documento, nombre_archivo, ruta_archivo, id_usuario_rrhh, motivo, fecha_registro)
                 VALUES
                    (:id_candidato, :tipo_documento, :nombre_archivo, :ruta_archivo, :id_usuario_rrhh, :motivo, :fecha_registro)",
                [
                    'id_candidato' => $id_candidato,
                    'tipo_documento' => trim((string) $tipo_documento),
                    'nombre_archivo' => trim((string) $nombre_archivo),
                    'ruta_archivo' => trim((string) $ruta_archivo),
                    'id_usuario_rrhh' => $id_usuario_rrhh ? (int) $id_usuario_rrhh : null,
                    'motivo' => $motivo !== null ? substr(trim((string) $motivo), 0, 500) : null,
                    'fecha_registro' => $fechaRegistro,
                ]
            );
            self::registrarBitacoraCandidato($id_candidato, 'DOCUMENTO_SUBIDO_MANUALMENTE', 'Documento subido manualmente', trim((string) $tipo_documento) . ': ' . trim((string) $nombre_archivo), [
                'tipo_documento' => trim((string) $tipo_documento),
                'nombre_archivo' => trim((string) $nombre_archivo),
                'motivo' => $motivo !== null ? substr(trim((string) $motivo), 0, 500) : null,
            ], $id_usuario_rrhh, $fechaRegistro);
            return self::resultado(true, 'Subida manual registrada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'No se pudo registrar la subida manual.', null, $e->getMessage());
        }
    }

    public static function updateVerificacionDocumento($id_documento, $verificacion_fiscal_json = null, $verificacion_calidad_json = null)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $doc = $db->queryOne("SELECT id_candidato FROM candidato_documento WHERE id = :id", ['id' => $id_documento]);
            if (!$doc) {
                return self::resultado(false, 'Documento no encontrado.');
            }
            $db->CRUD(
                "UPDATE candidato_documento SET verificacion_fiscal_json = :vf, verificacion_calidad_json = :vc WHERE id = :id",
                [
                    'id' => $id_documento,
                    'vf' => $verificacion_fiscal_json,
                    'vc' => $verificacion_calidad_json,
                ]
            );
            self::invalidateDocumentacionCache((int) ($doc['id_candidato'] ?? 0));
            return self::resultado(true, 'Verificación actualizada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar verificación.', null, $e->getMessage());
        }
    }

    /**
     * Obtener solo ruta y nombre de un documento (sin contenido). Para servir desde disco sin cargar LONGBLOB.
     */
    public static function getDocumentoRutaSolo($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id, id_candidato, nombre_archivo, ruta_archivo FROM candidato_documento WHERE id = :id",
                ['id' => $id_documento]
            );
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Obtener contenido de un documento para servirlo (ver/descargar).
     * Devuelve nombre_archivo, contenido (LONGBLOB), mime_type.
     * Si el registro tiene contenido en BD se usa; si no, contenido será null (servir desde ruta_archivo en disco).
     */
    public static function getDocumentoContenidoParaVer($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne(
                "SELECT id, id_candidato, nombre_archivo, contenido, mime_type, ruta_archivo FROM candidato_documento WHERE id = :id",
                ['id' => $id_documento]
            );
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Listar documentos ya subidos por un candidato.
     */
    public static function getDocumentosCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID inválido.', []);
        }
        try {
            $db = new Database();
            $lista = $db->queryAll("SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga, validado, fecha_validado, verificacion_fiscal_json, verificacion_calidad_json, CASE WHEN contenido IS NULL THEN 0 ELSE 1 END AS tiene_contenido FROM candidato_documento WHERE id_candidato = :id ORDER BY fecha_carga DESC", ['id' => $id_candidato]);
            return self::resultado(true, 'Documentos encontrados.', self::filtrarDocumentosConArchivo($lista ?: []));
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al listar documentos.', [], $e->getMessage());
        }
    }

    /**
     * Obtener un documento por ID (para verificar y servir/eliminar).
     */
    public static function getDocumentoById($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.', null);
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, validado FROM candidato_documento WHERE id = :id", ['id' => $id_documento]);
            return self::resultado(true, $row ? 'OK' : 'No encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar un documento del expediente (por ID).
     */
    public static function eliminarDocumento($id_documento)
    {
        $id_documento = (int) $id_documento;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento WHERE id = :id", ['id' => $id_documento]);
            return self::resultado(true, 'Documento eliminado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar todos los documentos de un candidato (solo registros en BD).
     * Los archivos en disco deben borrarse desde el controlador.
     */
    public static function eliminarDocumentosDeCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento WHERE id_candidato = :id", ['id' => $id_candidato]);
            return self::resultado(true, 'Documentación eliminada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al eliminar documentación.', null, $e->getMessage());
        }
    }

    /**
     * Eliminar el token de enlace de documentos del candidato (para no dejar huérfanos).
     */
    public static function eliminarTokenDocumentosCandidato($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD("DELETE FROM candidato_documento_token WHERE id_candidato = :id", ['id' => $id_candidato]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Guardar el último resultado de verificación de expediente (API validar-expediente).
     * @param int $id_candidato
     * @param string|null $jsonResultado JSON del resultado (checks_ok, alertas, todo_coincide, etc.)
     */
    public static function updateVerificacionExpediente($id_candidato, $jsonResultado)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE candidatos SET ultima_verificacion_expediente = :json WHERE id = :id",
                ['id' => $id_candidato, 'json' => $jsonResultado === null ? null : (is_string($jsonResultado) ? $jsonResultado : json_encode($jsonResultado))]
            );
        } catch (\Exception $e) {
            // Columna puede no existir si no se ejecutó la migración
        }
        self::invalidateDocumentacionCache($id_candidato);
    }

    /**
     * Obtener el último resultado de verificación de expediente (JSON decodificado o null).
     */
    public static function getVerificacionExpediente($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return null;
        }
        try {
            $db = new Database();
            $row = $db->queryOne("SELECT ultima_verificacion_expediente FROM candidatos WHERE id = :id", ['id' => $id_candidato]);
            if (!$row || empty($row['ultima_verificacion_expediente'])) {
                return null;
            }
            $decoded = json_decode($row['ultima_verificacion_expediente'], true);
            return is_array($decoded) ? $decoded : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Documentos + verificación en una sola conexión (para listado Documentación, optimizado).
     * @return array{documentos: array, verificacion: array|null}
     */
    private static function asegurarTablaJobsVerificacionDocumental(Database $db): void
    {
        try {
            $db->CRUD(
                "CREATE TABLE IF NOT EXISTS candidato_verificacion_documental_job (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_candidato INT NOT NULL,
                    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
                    prioridad TINYINT NOT NULL DEFAULT 5,
                    intentos TINYINT NOT NULL DEFAULT 0,
                    max_intentos TINYINT NOT NULL DEFAULT 3,
                    origen VARCHAR(40) NULL,
                    tipos_subidos_json TEXT NULL,
                    expediente_completo TINYINT NULL,
                    locked_at DATETIME NULL,
                    started_at DATETIME NULL,
                    finished_at DATETIME NULL,
                    next_run_at DATETIME NOT NULL,
                    last_error TEXT NULL,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME NOT NULL,
                    INDEX idx_cvdj_estado_next (estado, next_run_at),
                    INDEX idx_cvdj_candidato_estado (id_candidato, estado),
                    INDEX idx_cvdj_locked (locked_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (\Exception $e) {
        }
    }

    public static function encolarVerificacionDocumental($id_candidato, array $tiposSubidos = [], ?bool $expedienteCompleto = null, string $origen = 'upload')
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID invalido.');
        }

        $tipos = [];
        foreach ($tiposSubidos as $tipo) {
            $n = (int) $tipo;
            if ($n > 0) {
                $tipos[$n] = true;
            }
        }
        $expedienteValor = $expedienteCompleto === null ? null : ($expedienteCompleto ? 1 : 0);

        try {
            $db = new Database();
            self::asegurarTablaJobsVerificacionDocumental($db);
            $ahora = self::fechaHoraActualMexicoCiudad();

            $maxIntentosJob = $expedienteValor === 1 ? 2 : 3;
            $prioridadJob = $expedienteValor === 1 ? 9 : 5;

            $existente = $db->queryOne(
                "SELECT id, tipos_subidos_json, expediente_completo
                 FROM candidato_verificacion_documental_job
                 WHERE id_candidato = :id
                   AND estado = 'pendiente'
                 ORDER BY id DESC
                 LIMIT 1",
                ['id' => $id_candidato]
            );

            if ($existente) {
                $tiposPrevios = json_decode((string) ($existente['tipos_subidos_json'] ?? '[]'), true);
                if (!is_array($tiposPrevios)) {
                    $tiposPrevios = [];
                }
                foreach ($tiposPrevios as $tipoPrevio) {
                    $n = (int) $tipoPrevio;
                    if ($n > 0) {
                        $tipos[$n] = true;
                    }
                }
                $expedienteFinal = $expedienteValor;
                if ($expedienteFinal === null && $existente['expediente_completo'] !== null) {
                    $expedienteFinal = (int) $existente['expediente_completo'];
                }
                if ((int) ($existente['expediente_completo'] ?? 0) === 1) {
                    $expedienteFinal = 1;
                }
                $db->CRUD(
                    "UPDATE candidato_verificacion_documental_job
                     SET estado = CASE WHEN estado = 'procesando' THEN estado ELSE 'pendiente' END,
                         tipos_subidos_json = :tipos,
                         expediente_completo = :expediente,
                         origen = :origen,
                         prioridad = GREATEST(prioridad, :prioridad),
                         max_intentos = :max_intentos,
                         next_run_at = :ahora,
                         last_error = NULL,
                         updated_at = :ahora
                     WHERE id = :job",
                    [
                        'job' => (int) $existente['id'],
                        'tipos' => json_encode(array_keys($tipos)),
                        'expediente' => $expedienteFinal,
                        'origen' => substr($origen, 0, 40),
                        'prioridad' => $expedienteFinal === 1 ? 9 : $prioridadJob,
                        'max_intentos' => $expedienteFinal === 1 ? 2 : $maxIntentosJob,
                        'ahora' => $ahora,
                    ]
                );
                return self::resultado(true, 'Verificacion documental encolada.', ['id_job' => (int) $existente['id']]);
            }

            $db->CRUD(
                "INSERT INTO candidato_verificacion_documental_job
                    (id_candidato, estado, prioridad, max_intentos, origen, tipos_subidos_json, expediente_completo, next_run_at, created_at, updated_at)
                 VALUES
                    (:id, 'pendiente', :prioridad, :max_intentos, :origen, :tipos, :expediente, :ahora, :ahora, :ahora)",
                [
                    'id' => $id_candidato,
                    'prioridad' => $prioridadJob,
                    'origen' => substr($origen, 0, 40),
                    'tipos' => json_encode(array_keys($tipos)),
                    'expediente' => $expedienteValor,
                    'max_intentos' => $maxIntentosJob,
                    'ahora' => $ahora,
                ]
            );
            return self::resultado(true, 'Verificacion documental encolada.', ['id_job' => $db->lastInsertId()]);
        } catch (\Exception $e) {
            return self::resultado(false, 'No se pudo encolar la verificacion documental.', null, $e->getMessage());
        }
    }

    public static function existeJobVerificacionDocumentalMasNuevo($id_candidato, $id_job): bool
    {
        $id_candidato = (int) $id_candidato;
        $id_job = (int) $id_job;
        if ($id_candidato <= 0 || $id_job <= 0) {
            return false;
        }
        try {
            $db = new Database();
            self::asegurarTablaJobsVerificacionDocumental($db);
            $row = $db->queryOne(
                "SELECT id
                 FROM candidato_verificacion_documental_job
                 WHERE id_candidato = :id
                   AND id > :job
                 ORDER BY id DESC
                 LIMIT 1",
                ['id' => $id_candidato, 'job' => $id_job]
            );
            return !empty($row);
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function tomarSiguienteJobVerificacionDocumental(int $staleMinutes = 3)
    {
        $staleMinutes = max(1, min(120, $staleMinutes));
        try {
            $db = new Database();
            self::asegurarTablaJobsVerificacionDocumental($db);
            $ahora = self::fechaHoraActualMexicoCiudad();
            $staleAntesDe = self::ahoraMexicoCiudadImmutable()->modify('-' . $staleMinutes . ' minutes')->format('Y-m-d H:i:s');
            $db->beginTransaction();
            $job = $db->queryOne(
                "SELECT *
                 FROM candidato_verificacion_documental_job
                 WHERE (
                        estado = 'pendiente'
                        AND next_run_at <= :ahora
                       )
                    OR (
                        estado = 'procesando'
                        AND locked_at IS NOT NULL
                        AND locked_at < :stale_antes_de
                       )
                 ORDER BY prioridad DESC, id ASC
                 LIMIT 1
                 FOR UPDATE",
                ['ahora' => $ahora, 'stale_antes_de' => $staleAntesDe]
            );
            if (!$job) {
                $db->commit();
                return null;
            }
            $db->CRUD(
                "UPDATE candidato_verificacion_documental_job
                 SET estado = 'procesando',
                     intentos = intentos + 1,
                     locked_at = :ahora,
                     started_at = :ahora,
                     finished_at = NULL,
                     last_error = NULL,
                     updated_at = :ahora
                 WHERE id = :id",
                ['id' => (int) $job['id'], 'ahora' => $ahora]
            );
            $db->commit();
            $job['intentos'] = (int) ($job['intentos'] ?? 0) + 1;
            return $job;
        } catch (\Exception $e) {
            try {
                if (isset($db)) {
                    $db->rollback();
                }
            } catch (\Exception $e2) {
            }
            return null;
        }
    }

    public static function finalizarJobVerificacionDocumental($id_job, bool $ok, ?string $error = null)
    {
        $id_job = (int) $id_job;
        if ($id_job <= 0) {
            return;
        }
        try {
            $db = new Database();
            self::asegurarTablaJobsVerificacionDocumental($db);
            $ahora = self::fechaHoraActualMexicoCiudad();
            if ($ok) {
                $db->CRUD(
                    "UPDATE candidato_verificacion_documental_job
                     SET estado = 'terminado',
                         finished_at = :ahora,
                         locked_at = NULL,
                         last_error = NULL,
                         updated_at = :ahora
                     WHERE id = :id",
                    ['id' => $id_job, 'ahora' => $ahora]
                );
                return;
            }
            $siguienteIntento = $ahora;
            $db->CRUD(
                "UPDATE candidato_verificacion_documental_job
                 SET estado = CASE WHEN intentos >= max_intentos THEN 'error' ELSE 'pendiente' END,
                     next_run_at = CASE WHEN intentos >= max_intentos THEN next_run_at ELSE :siguiente_intento END,
                     finished_at = CASE WHEN intentos >= max_intentos THEN :ahora ELSE finished_at END,
                     locked_at = NULL,
                     last_error = :error,
                     updated_at = :ahora
                 WHERE id = :id",
                [
                    'id' => $id_job,
                    'error' => $error !== null ? substr($error, 0, 2000) : null,
                    'ahora' => $ahora,
                    'siguiente_intento' => $siguienteIntento,
                ]
            );
        } catch (\Exception $e) {
        }
    }

    public static function getDocumentosYVerificacion($id_candidato, bool $verificarDisponibilidadArchivos = true, bool $asegurarColumnas = true)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return ['documentos' => [], 'verificacion' => null];
        }
        try {
            $db = new Database();
            if ($asegurarColumnas) {
                self::asegurarColumnasFlujoIngreso($db);
            }
            $documentos = $db->queryAll(
                "SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga, validado, fecha_validado, verificacion_fiscal_json, verificacion_calidad_json, CASE WHEN contenido IS NULL THEN 0 ELSE 1 END AS tiene_contenido FROM candidato_documento WHERE id_candidato = :id ORDER BY fecha_carga DESC",
                ['id' => $id_candidato]
            );
            $documentos = $documentos ?: [];
            foreach ($documentos as &$d) {
                $d['archivo_disponible'] = $verificarDisponibilidadArchivos
                    ? (self::documentoTieneArchivoDisponible($d) ? 1 : 0)
                    : (!empty($d['ruta_archivo']) || !empty($d['tiene_contenido']) ? 1 : 0);
                if (!empty($d['verificacion_fiscal_json'])) {
                    $dec = json_decode($d['verificacion_fiscal_json'], true);
                    $d['verificacion_fiscal'] = is_array($dec) ? $dec : null;
                } else {
                    $d['verificacion_fiscal'] = null;
                }
                if (!empty($d['verificacion_calidad_json'])) {
                    $dec = json_decode($d['verificacion_calidad_json'], true);
                    $d['verificacion_calidad'] = is_array($dec) ? $dec : null;
                } else {
                    $d['verificacion_calidad'] = null;
                }
            }
            unset($d);
            $row = $db->queryOne("SELECT ultima_verificacion_expediente, sueldo_bruto, sueldo_neto, motivo_contratacion FROM candidatos WHERE id = :id", ['id' => $id_candidato]);
            $verificacion = null;
            if ($row && !empty($row['ultima_verificacion_expediente'])) {
                $decoded = json_decode($row['ultima_verificacion_expediente'], true);
                $verificacion = is_array($decoded) ? $decoded : null;
            }
            return [
                'documentos' => $documentos,
                'verificacion' => $verificacion,
                'sueldo' => [
                    'bruto' => $row && $row['sueldo_bruto'] !== null ? (string) $row['sueldo_bruto'] : '',
                    'neto' => $row && $row['sueldo_neto'] !== null ? (string) $row['sueldo_neto'] : '',
                    'motivo_contratacion' => $row && $row['motivo_contratacion'] !== null ? (string) $row['motivo_contratacion'] : '',
                ],
            ];
        } catch (\Exception $e) {
            return ['documentos' => [], 'verificacion' => null, 'sueldo' => ['bruto' => '', 'neto' => '']];
        }
    }

    public static function getDocumentosYVerificacionMultiple(array $idsCandidatos, bool $verificarDisponibilidadArchivos = false): array
    {
        $ids = [];
        foreach ($idsCandidatos as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        if (empty($ids)) {
            return [];
        }

        $resultado = [];
        foreach ($ids as $id) {
            $resultado[$id] = [
                'documentos' => [],
                'verificacion' => null,
                'sueldo' => ['bruto' => '', 'neto' => '', 'motivo_contratacion' => ''],
            ];
        }

        try {
            $db = new Database();
            $params = [];
            $placeholders = [];
            $i = 0;
            foreach ($ids as $id) {
                $key = 'id' . $i++;
                $params[$key] = $id;
                $placeholders[] = ':' . $key;
            }
            $in = implode(',', $placeholders);

            $documentos = $db->queryAll(
                "SELECT id, id_candidato, tipo_documento, nombre_archivo, ruta_archivo, fecha_carga, validado, fecha_validado, verificacion_fiscal_json, verificacion_calidad_json, CASE WHEN contenido IS NULL THEN 0 ELSE 1 END AS tiene_contenido
                 FROM candidato_documento
                 WHERE id_candidato IN ({$in})
                 ORDER BY id_candidato ASC, fecha_carga DESC",
                $params
            );
            foreach (($documentos ?: []) as $d) {
                $idCandidato = (int) ($d['id_candidato'] ?? 0);
                if (!isset($resultado[$idCandidato])) {
                    continue;
                }
                $d['archivo_disponible'] = $verificarDisponibilidadArchivos
                    ? (self::documentoTieneArchivoDisponible($d) ? 1 : 0)
                    : (!empty($d['ruta_archivo']) || !empty($d['tiene_contenido']) ? 1 : 0);
                if (!empty($d['verificacion_fiscal_json'])) {
                    $dec = json_decode($d['verificacion_fiscal_json'], true);
                    $d['verificacion_fiscal'] = is_array($dec) ? $dec : null;
                } else {
                    $d['verificacion_fiscal'] = null;
                }
                if (!empty($d['verificacion_calidad_json'])) {
                    $dec = json_decode($d['verificacion_calidad_json'], true);
                    $d['verificacion_calidad'] = is_array($dec) ? $dec : null;
                } else {
                    $d['verificacion_calidad'] = null;
                }
                $resultado[$idCandidato]['documentos'][] = $d;
            }

            $rows = $db->queryAll(
                "SELECT id, ultima_verificacion_expediente, sueldo_bruto, sueldo_neto, motivo_contratacion
                 FROM candidatos
                 WHERE id IN ({$in})",
                $params
            );
            foreach (($rows ?: []) as $row) {
                $idCandidato = (int) ($row['id'] ?? 0);
                if (!isset($resultado[$idCandidato])) {
                    continue;
                }
                if (!empty($row['ultima_verificacion_expediente'])) {
                    $decoded = json_decode($row['ultima_verificacion_expediente'], true);
                    $resultado[$idCandidato]['verificacion'] = is_array($decoded) ? $decoded : null;
                }
                $resultado[$idCandidato]['sueldo'] = [
                    'bruto' => $row['sueldo_bruto'] !== null ? (string) $row['sueldo_bruto'] : '',
                    'neto' => $row['sueldo_neto'] !== null ? (string) $row['sueldo_neto'] : '',
                    'motivo_contratacion' => $row['motivo_contratacion'] !== null ? (string) $row['motivo_contratacion'] : '',
                ];
            }
        } catch (\Exception $e) {
        }

        return $resultado;
    }

    private static function normalizarSueldo($valor): ?float
    {
        $txt = trim((string) ($valor ?? ''));
        if ($txt === '') {
            return null;
        }
        $txt = str_replace([',', '$', ' '], '', $txt);
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $txt)) {
            return null;
        }
        $num = (float) $txt;
        return $num > 0 ? round($num, 2) : null;
    }

    public static function guardarSueldosDocumentacion($id_candidato, $sueldo_bruto, $sueldo_neto, $motivo_contratacion = null): array
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato invalido.');
        }

        $bruto = self::normalizarSueldo($sueldo_bruto);
        $neto = self::normalizarSueldo($sueldo_neto);
        if ($bruto === null && $neto === null) {
            return self::resultado(false, 'Capture sueldo bruto o sueldo neto antes de continuar.');
        }
        $motivo = trim((string) ($motivo_contratacion ?? ''));
        if ($motivo !== '') {
            $motivo = mb_substr($motivo, 0, 500);
        } else {
            $motivo = null;
        }

        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            $fechaHora = self::fechaHoraActualMexicoCiudad();
            $db->CRUD(
                "UPDATE candidatos
                    SET sueldo_bruto = :bruto,
                        sueldo_neto = :neto,
                        motivo_contratacion = :motivo,
                        fecha_actualizacion = :fecha_hora
                  WHERE id = :id",
                [
                    'id' => $id_candidato,
                    'bruto' => $bruto,
                    'neto' => $neto,
                    'motivo' => $motivo,
                    'fecha_hora' => $fechaHora,
                ]
            );
            return self::resultado(true, 'Sueldo guardado.', [
                'id_candidato' => $id_candidato,
                'sueldo_bruto' => $bruto !== null ? number_format($bruto, 2, '.', '') : '',
                'sueldo_neto' => $neto !== null ? number_format($neto, 2, '.', '') : '',
                'motivo_contratacion' => $motivo ?? '',
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'No se pudo guardar el sueldo.', null, $e->getMessage());
        }
    }

    /**
     * Invalidar caché de listado documentación para un candidato (al subir/eliminar doc o actualizar verificación).
     */
    public static function invalidateDocumentacionCache($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return;
        }
        $cacheDir = defined('RAIZ') ? (RAIZ . '/storage/cache') : (__DIR__ . '/../storage/cache');
        $prefixes = ['doc_candidato_'];
        for ($v = 2; $v <= 12; $v++) {
            $prefixes[] = 'doc_candidato_v' . $v . '_';
        }
        foreach ($prefixes as $prefix) {
            $file = $cacheDir . '/' . $prefix . $id_candidato . '.json';
            if (is_file($file)) {
                @unlink($file);
            }
        }
        if (function_exists('apcu_delete')) {
            foreach ($prefixes as $prefix) {
                @apcu_delete($prefix . $id_candidato);
            }
        }
    }

    /**
     * Marcar/desmarcar un documento como validado por Capital Humano.
     */
    public static function toggleValidadoDocumento($id_documento, $validado)
    {
        $id_documento = (int) $id_documento;
        $validado = $validado ? 1 : 0;
        if ($id_documento <= 0) {
            return self::resultado(false, 'ID inválido.');
        }
        try {
            $db = new Database();
            $fechaValidado = self::fechaHoraActualMexicoCiudad();
            $paramsValidado = ['id' => $id_documento, 'v' => $validado];
            if ($validado) {
                $paramsValidado['fecha_validado'] = $fechaValidado;
            }
            $db->CRUD(
                "UPDATE candidato_documento SET validado = :v, fecha_validado = " . ($validado ? ":fecha_validado" : "NULL") . " WHERE id = :id",
                $paramsValidado
            );
            return self::resultado(true, $validado ? 'Documento validado.' : 'Validación retirada.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    /**
     * Contar documentos validados vs total de un candidato.
     */
    public static function contarValidados($id_candidato)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return ['total' => 0, 'validados' => 0];
        }
        try {
            $db = new Database();
            $r = $db->queryOne(
                "SELECT COUNT(*) AS total, SUM(validado) AS validados FROM candidato_documento WHERE id_candidato = :id",
                ['id' => $id_candidato]
            );
            return ['total' => (int) ($r['total'] ?? 0), 'validados' => (int) ($r['validados'] ?? 0)];
        } catch (\Exception $e) {
            return ['total' => 0, 'validados' => 0];
        }
    }

    private static function agregarEventoBitacora(array &$eventos, string $evento, string $titulo, ?string $descripcion, ?string $fecha, string $color = 'primary', array $detalle = []): void
    {
        $fecha = trim((string) $fecha);
        if ($fecha === '' || $fecha === '0000-00-00 00:00:00' || $fecha === '0000-00-00') {
            return;
        }
        $eventos[] = [
            'evento' => strtoupper(trim($evento)),
            'titulo' => trim($titulo),
            'descripcion' => $descripcion !== null ? trim($descripcion) : '',
            'fecha' => $fecha,
            'color' => $color,
            'detalle' => $detalle,
        ];
    }

    public static function getHistoricoCandidatos(): array
    {
        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            self::ensureTablaHistoricoCandidato();
            $datos = $db->queryAll(
                "SELECT
                    candidato_historico.id,
                    id_candidato_original AS id_candidato,
                    nombre_completo,
                    email,
                    telefono,
                    puesto,
                    departamento,
                    ubicacion,
                    estatus_final AS estatus,
                    motivo AS motivo_cierre,
                    descripcion AS descripcion_cierre,
                    fecha_accion AS fecha_cierre,
                    fecha_creacion AS fecha_registro,
                    fecha_accion AS fecha_actualizacion,
                    1 AS eliminado,
                    TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS usuario_accion,
                    'historico' AS fuente
                 FROM candidato_historico
                 LEFT JOIN persona per ON per.id = candidato_historico.id_usuario_accion
                 WHERE estatus_final IN ('Contratado', 'Proceso cerrado', 'Eliminado')
                 ORDER BY fecha_accion DESC, candidato_historico.id DESC"
            );
            return self::resultado(true, 'HistÃ³rico de candidatos encontrado.', $datos);

            $actuales = $db->queryAll(
                "SELECT
                    c.id,
                    TRIM(CONCAT_WS(' ', c.nombres, c.segundo_nombre, c.apellidop, c.apellidom)) AS nombre_completo,
                    c.email,
                    c.telefono,
                    p.nombre AS puesto,
                    d.nombre AS departamento,
                    CONCAT_WS(' / ', pais.nombre, div1.nombre, div2.nombre, div3.nombre) AS ubicacion,
                    COALESCE(c.estatus, 'Por evaluar') AS estatus,
                    c.motivo_cierre,
                    c.descripcion_cierre,
                    c.fecha_cierre,
                    c.fecha_registro,
                    c.fecha_actualizacion,
                    '' AS usuario_accion,
                    0 AS eliminado
                 FROM candidatos c
                 LEFT JOIN puesto p ON p.id = c.id_puesto
                 LEFT JOIN departamento d ON d.id = c.id_departamento
                 LEFT JOIN paises pais ON pais.id = c.id_pais
                 LEFT JOIN divisiones_administrativas div1 ON div1.id = c.id_div_nivel1
                 LEFT JOIN divisiones_administrativas div2 ON div2.id = c.id_div_nivel2
                 LEFT JOIN divisiones_administrativas div3 ON div3.id = c.id_div_nivel3
                 ORDER BY c.fecha_registro DESC, c.id DESC"
            );

            $idsActuales = [];
            foreach ($actuales as &$a) {
                $a['id_candidato'] = (int) ($a['id'] ?? 0);
                $a['fuente'] = 'actual';
                $idsActuales[$a['id_candidato']] = true;
            }
            unset($a);

            $eliminados = $db->queryAll(
                "SELECT
                    id_candidato_original AS id_candidato,
                    nombre_completo,
                    email,
                    telefono,
                    puesto,
                    departamento,
                    ubicacion,
                    estatus_final AS estatus,
                    motivo AS motivo_cierre,
                    descripcion AS descripcion_cierre,
                    fecha_accion AS fecha_cierre,
                    fecha_creacion AS fecha_registro,
                    fecha_accion AS fecha_actualizacion,
                    1 AS eliminado,
                    TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS usuario_accion,
                    'historico' AS fuente
                 FROM candidato_historico
                 LEFT JOIN persona per ON per.id = candidato_historico.id_usuario_accion
                 ORDER BY fecha_accion DESC, id DESC"
            );

            $datos = $actuales;
            foreach ($eliminados as $e) {
                $idOrig = (int) ($e['id_candidato'] ?? 0);
                if ($idOrig > 0 && isset($idsActuales[$idOrig])) {
                    continue;
                }
                $datos[] = $e;
            }

            usort($datos, function ($a, $b) {
                $fa = strtotime((string) ($a['fecha_registro'] ?? $a['fecha_actualizacion'] ?? '')) ?: 0;
                $fb = strtotime((string) ($b['fecha_registro'] ?? $b['fecha_actualizacion'] ?? '')) ?: 0;
                return $fb <=> $fa;
            });

            return self::resultado(true, 'Histórico de candidatos encontrado.', $datos);
        } catch (\Exception $e) {
            return self::resultado(false, 'No se pudo consultar el histórico de candidatos.', null, $e->getMessage());
        }
    }

    public static function getBitacoraCandidato($id_candidato): array
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', []);
        }

        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            self::ensureTablaBitacoraCandidato();
            self::ensureTablaEliminacionDocumentoCandidato();
            $eventos = [];

            $cRes = self::getById($id_candidato);
            if (empty($cRes['success']) || empty($cRes['datos'])) {
                self::ensureTablaHistoricoCandidato();
                $hist = $db->queryOne(
                    "SELECT * FROM candidato_historico WHERE id_candidato_original = :id ORDER BY fecha_accion DESC LIMIT 1",
                    ['id' => $id_candidato]
                );
                if (!$hist) {
                    return self::resultado(false, 'Candidato no encontrado.', []);
                }
                $snapshot = [];
                if (!empty($hist['snapshot_json'])) {
                    $tmpSnap = json_decode((string) $hist['snapshot_json'], true);
                    if (is_array($tmpSnap)) {
                        $snapshot = $tmpSnap;
                    }
                }
                $c = array_merge($snapshot, [
                    'id' => $id_candidato,
                    'nombres' => $hist['nombre_completo'] ?? '',
                    'segundo_nombre' => '',
                    'apellidop' => '',
                    'apellidom' => '',
                    'email' => $hist['email'] ?? null,
                    'telefono' => $hist['telefono'] ?? null,
                    'nombre_puesto' => $hist['puesto'] ?? null,
                    'nombre_departamento' => $hist['departamento'] ?? null,
                    'estatus' => $hist['estatus_final'] ?? 'Eliminado',
                    'fecha_registro' => $hist['fecha_creacion'] ?? null,
                    'fecha_actualizacion' => $hist['fecha_accion'] ?? null,
                ]);
                self::agregarEventoBitacora(
                    $eventos,
                    strtoupper((string) ($hist['estatus_final'] ?? 'ELIMINADO')),
                    ((string) ($hist['estatus_final'] ?? 'Eliminado') === 'Eliminado') ? 'Candidato eliminado' : 'Movimiento histórico',
                    trim((string) ($hist['motivo'] ?? '')) . (trim((string) ($hist['descripcion'] ?? '')) !== '' ? ': ' . trim((string) $hist['descripcion']) : ''),
                    $hist['fecha_accion'] ?? null,
                    'danger',
                    ['historico' => true]
                );
                $cRes = ['success' => true, 'datos' => $c];
            }
            $c = $cRes['datos'];
            $nombre = trim(implode(' ', array_filter([
                $c['nombres'] ?? '',
                $c['segundo_nombre'] ?? '',
                $c['apellidop'] ?? '',
                $c['apellidom'] ?? '',
            ])));

            $explicit = $db->queryAll(
                "SELECT cb.id, cb.evento, cb.titulo, cb.descripcion, cb.detalle_json, cb.id_usuario, cb.fecha_registro,
                        TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS usuario_accion
                 FROM candidato_bitacora cb
                 LEFT JOIN persona per ON per.id = cb.id_usuario
                 WHERE id_candidato = :id
                 ORDER BY cb.fecha_registro ASC, cb.id ASC",
                ['id' => $id_candidato]
            );
            $eventosExplicitos = [];
            foreach ($explicit as $ev) {
                $evento = strtoupper(trim((string) ($ev['evento'] ?? '')));
                if ($evento !== '') {
                    $eventosExplicitos[$evento] = true;
                }
                if ($evento === 'DOCUMENTO_CARGADO') {
                    continue;
                }
                $detalle = [];
                if (!empty($ev['detalle_json'])) {
                    $tmp = json_decode((string) $ev['detalle_json'], true);
                    if (is_array($tmp)) {
                        $detalle = $tmp;
                    }
                }
                if (!empty($ev['usuario_accion'])) {
                    $detalle['usuario_accion'] = $ev['usuario_accion'];
                } elseif (!empty($ev['id_usuario'])) {
                    $detalle['usuario_accion'] = 'Usuario ID ' . (int) $ev['id_usuario'];
                }
                self::agregarEventoBitacora(
                    $eventos,
                    $evento ?: 'MOVIMIENTO',
                    (string) ($ev['titulo'] ?? 'Movimiento'),
                    $ev['descripcion'] ?? '',
                    $ev['fecha_registro'] ?? null,
                    $detalle['color'] ?? 'primary',
                    $detalle
                );
            }

            if (empty($eventosExplicitos['CANDIDATO_CREADO'])) {
                self::agregarEventoBitacora($eventos, 'CANDIDATO_CREADO', 'Candidato creado', $nombre !== '' ? $nombre : 'Se dio de alta el candidato.', $c['fecha_registro'] ?? null, 'info');
            }
            if (empty($eventosExplicitos['CORREO_DOCUMENTOS_ENVIADO'])) {
                self::agregarEventoBitacora($eventos, 'CORREO_DOCUMENTOS_ENVIADO', 'Correo de documentos enviado', 'Se envió el enlace para la carga de documentos.', $c['fecha_postulacion_enviada'] ?? null, 'purple');
            }
            if (!empty($c['fecha_ingreso_notificada_en']) && empty($eventosExplicitos['FECHA_INGRESO_NOTIFICADA'])) {
                self::agregarEventoBitacora($eventos, 'FECHA_INGRESO_NOTIFICADA', 'Fecha de ingreso notificada', 'Se envió la fecha de ingreso al candidato y al jefe correspondiente.', $c['fecha_ingreso_notificada_en'], 'success', [
                    'fecha_ingreso' => $c['fecha_ingreso_programada'] ?? null,
                    'correo_jefe' => $c['correo_jefe'] ?? null,
                ]);
            }
            if (!empty($c['contrato_firmado_en']) && empty($eventosExplicitos['CONTRATO_FIRMADO'])) {
                self::agregarEventoBitacora($eventos, 'CONTRATO_FIRMADO', 'Contrato firmado', 'El candidato confirmó la firma/alta del proceso.', $c['contrato_firmado_en'], 'success');
            }

            $docs = $db->queryAll(
                "SELECT id, tipo_documento, nombre_archivo, fecha_carga, validado, fecha_validado
                 FROM candidato_documento
                 WHERE id_candidato = :id
                 ORDER BY fecha_carga ASC, id ASC",
                ['id' => $id_candidato]
            );
            $totalDocs = count($docs);
            if ($totalDocs > 0) {
                $ultimaCarga = null;
                $ultimaValidacion = null;
                $validados = 0;
                foreach ($docs as $d) {
                    $ultimaCarga = max($ultimaCarga ?: (string) ($d['fecha_carga'] ?? ''), (string) ($d['fecha_carga'] ?? ''));
                    if ((int) ($d['validado'] ?? 0) === 1) {
                        $validados++;
                        $fv = trim((string) ($d['fecha_validado'] ?? ''));
                        if ($fv !== '') {
                            $ultimaValidacion = max($ultimaValidacion ?: $fv, $fv);
                        }
                    }
                }
                if ($totalDocs >= 10 && empty($eventosExplicitos['EXPEDIENTE_COMPLETO'])) {
                    self::agregarEventoBitacora($eventos, 'EXPEDIENTE_COMPLETO', 'Expediente completo', 'Se cargaron ' . $totalDocs . ' documentos del candidato.', $ultimaCarga, 'success', ['total_documentos' => $totalDocs]);
                }
                if ($totalDocs >= 10 && $validados >= 10 && empty($eventosExplicitos['DOCUMENTOS_VALIDADOS'])) {
                    self::agregarEventoBitacora($eventos, 'DOCUMENTOS_VALIDADOS', 'Documentos validados', 'Capital Humano validó todos los documentos requeridos.', $ultimaValidacion ?: ($c['fecha_actualizacion'] ?? null), 'success', ['validados' => $validados, 'total_documentos' => $totalDocs]);
                }
            }

            $estatus = trim((string) ($c['estatus'] ?? ''));
            if (strcasecmp($estatus, 'Pendiente de validacion final') === 0 && empty($eventosExplicitos['ENVIADO_VALIDACION_FINAL'])) {
                self::agregarEventoBitacora($eventos, 'ENVIADO_VALIDACION_FINAL', 'Enviado a validación final', 'El expediente fue enviado al validador final.', $c['fecha_actualizacion'] ?? null, 'warning');
            }

            $faseSelect = self::columnaExiste($db, 'candidato_documento_eliminacion', 'fase_revision')
                ? "COALESCE(fase_revision, '') AS fase_revision"
                : "'' AS fase_revision";
            $eliminados = $db->queryAll(
                "SELECT id_documento_eliminado, tipo_documento, nombre_archivo, comentario, id_usuario_rrhh, fecha_registro, {$faseSelect}
                 FROM candidato_documento_eliminacion
                 WHERE id_candidato = :id
                 ORDER BY fecha_registro ASC",
                ['id' => $id_candidato]
            );
            foreach ($eliminados as $del) {
                $fase = trim((string) ($del['fase_revision'] ?? ''));
                $esFinal = $fase === 'validacion_final';
                $tipo = trim((string) ($del['tipo_documento'] ?? 'Documento'));
                $archivo = trim((string) ($del['nombre_archivo'] ?? ''));
                self::agregarEventoBitacora(
                    $eventos,
                    $esFinal ? 'DOCUMENTO_RECHAZADO_VALIDACION_FINAL' : 'DOCUMENTO_ELIMINADO',
                    $esFinal ? 'Documento rechazado en validación final' : 'Documento eliminado/rechazado',
                    $tipo . ($archivo !== '' ? ' - ' . $archivo : '') . '. Motivo: ' . trim((string) ($del['comentario'] ?? '')),
                    $del['fecha_registro'] ?? null,
                    $esFinal ? 'danger' : 'warning',
                    [
                        'tipo_documento' => $tipo,
                        'nombre_archivo' => $archivo,
                        'fase' => $fase,
                    ]
                );
            }

            usort($eventos, function ($a, $b) {
                $ta = strtotime((string) ($a['fecha'] ?? '')) ?: 0;
                $tb = strtotime((string) ($b['fecha'] ?? '')) ?: 0;
                if ($ta === $tb) {
                    return strcmp((string) ($a['titulo'] ?? ''), (string) ($b['titulo'] ?? ''));
                }
                return $ta <=> $tb;
            });

            return self::resultado(true, 'Bitácora encontrada.', [
                'id_candidato' => $id_candidato,
                'candidato' => $nombre,
                'eventos' => $eventos,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'No se pudo consultar la bitácora.', null, $e->getMessage());
        }
    }

    /**
     * Actualizar estatus del candidato.
     */
    public static function updateEstatus($id_candidato, $estatus)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($estatus) === '') {
            return;
        }
        try {
            $db = new Database();
            $fechaHora = self::fechaHoraActualMexicoCiudad();
            $db->CRUD("UPDATE candidatos SET estatus = :e, fecha_actualizacion = :fecha_hora WHERE id = :id", ['id' => $id_candidato, 'e' => trim($estatus), 'fecha_hora' => $fechaHora]);
        } catch (\Exception $e) {
        }
    }

    /**
     * Cerrar proceso del candidato: guarda motivo, descripción y actualiza estatus a "Proceso cerrado".
     * Requiere que existan las columnas proceso_cerrado, motivo_cierre, descripcion_cierre, fecha_cierre.
     *
     * @param int $id_candidato
     * @param string $motivo Clave del motivo (ej. no_cubre_perfil, desistio, sin_info_a_tiempo, otro)
     * @param string|null $descripcion Descripción opcional
     * @return array { success, mensaje, datos?, error? }
     */
    public static function cerrarProceso($id_candidato, $motivo, $descripcion = null, $id_usuario = null)
    {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0) {
            return self::resultado(false, 'ID de candidato inválido.', null);
        }
        $motivo = trim($motivo ?? '');
        if ($motivo === '') {
            return self::resultado(false, 'El motivo del cierre es obligatorio.', null);
        }
        $descripcion = trim($descripcion ?? '') ?: null;
        try {
            $db = new Database();
            self::asegurarColumnasFlujoIngreso($db);
            $fechaCierre = self::fechaHoraActualMexicoCiudad();
            $db->CRUD(
                "UPDATE candidatos SET proceso_cerrado = 1, motivo_cierre = :motivo, descripcion_cierre = :descripcion, fecha_cierre = :fecha_cierre, estatus = 'Proceso cerrado', fecha_actualizacion = :fecha_cierre WHERE id = :id",
                ['id' => $id_candidato, 'motivo' => $motivo, 'descripcion' => $descripcion, 'fecha_cierre' => $fechaCierre]
            );
            self::registrarBitacoraCandidato(
                $id_candidato,
                'PROCESO_CERRADO',
                'Proceso cerrado',
                'Motivo: ' . $motivo . ($descripcion !== null && $descripcion !== '' ? '. ' . $descripcion : ''),
                ['motivo' => $motivo, 'descripcion' => $descripcion],
                $id_usuario,
                $fechaCierre
            );
            self::guardarSnapshotHistoricoCandidato($id_candidato, 'Proceso cerrado', $motivo, $descripcion, $id_usuario, $fechaCierre);
            return self::resultado(true, 'Proceso cerrado correctamente.', ['id' => $id_candidato]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cerrar el proceso.', null, $e->getMessage());
        }
    }

    /**
     * Tabla de auditoría cuando RRHH elimina un documento del expediente (motivo + correo al candidato).
     */
    private static function ensureTablaEliminacionDocumentoCandidato(): void
    {
        static $done = false;
        if ($done) {
            return;
        }
        $done = true;
        try {
            $db = new Database();
            $db->CRUD(
                'CREATE TABLE IF NOT EXISTS candidato_documento_eliminacion (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_candidato INT NOT NULL,
                    id_documento_eliminado INT NULL,
                    tipo_documento VARCHAR(255) NULL,
                    nombre_archivo VARCHAR(500) NULL,
                    comentario TEXT NOT NULL,
                    fase_revision VARCHAR(40) NULL,
                    id_usuario_rrhh INT NULL,
                    fecha_registro DATETIME NOT NULL,
                    INDEX idx_candidato (id_candidato),
                    INDEX idx_fecha (fecha_registro)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
            );
            if (!self::columnaExiste($db, 'candidato_documento_eliminacion', 'fase_revision')) {
                $db->CRUD("ALTER TABLE candidato_documento_eliminacion ADD COLUMN fase_revision VARCHAR(40) NULL AFTER comentario");
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * @param int|null $id_usuario_rrhh ID en sesión de quien elimina (opcional)
     */
    public static function registrarEliminacionDocumentoCandidato(
        $id_candidato,
        $id_documento_eliminado,
        $tipo_documento,
        $nombre_archivo,
        $comentario,
        $id_usuario_rrhh = null,
        $fase_revision = null
    ) {
        $id_candidato = (int) $id_candidato;
        if ($id_candidato <= 0 || trim($comentario ?? '') === '') {
            return self::resultado(false, 'Datos inválidos para registrar la eliminación.');
        }
        self::ensureTablaEliminacionDocumentoCandidato();
        try {
            $db = new Database();
            $db->CRUD(
                'INSERT INTO candidato_documento_eliminacion
                    (id_candidato, id_documento_eliminado, tipo_documento, nombre_archivo, comentario, fase_revision, id_usuario_rrhh, fecha_registro)
                 VALUES (:idc, :idd, :tipo, :nom, :com, :fase, :usr, :fecha_registro)',
                [
                    'idc' => $id_candidato,
                    'idd' => $id_documento_eliminado > 0 ? $id_documento_eliminado : null,
                    'tipo' => $tipo_documento !== null && $tipo_documento !== '' ? substr(trim((string) $tipo_documento), 0, 255) : null,
                    'nom' => $nombre_archivo !== null && $nombre_archivo !== '' ? substr(trim((string) $nombre_archivo), 0, 500) : null,
                    'com' => trim((string) $comentario),
                    'fase' => $fase_revision !== null && trim((string) $fase_revision) !== '' ? substr(trim((string) $fase_revision), 0, 40) : null,
                    'usr' => $id_usuario_rrhh !== null && (int) $id_usuario_rrhh > 0 ? (int) $id_usuario_rrhh : null,
                    'fecha_registro' => self::fechaHoraActualMexicoCiudad(),
                ]
            );
            return self::resultado(true, 'Registro guardado.');
        } catch (\Exception $e) {
            return self::resultado(false, 'No se pudo guardar el motivo de eliminación.', null, $e->getMessage());
        }
    }
}
