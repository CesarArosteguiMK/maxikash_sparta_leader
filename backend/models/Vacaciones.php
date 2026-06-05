<?php

namespace Models;

use Core\Database;
use Core\Model;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Exception;

class Vacaciones extends Model
{
    private const TZ = 'America/Mexico_City';

    public static function asegurarTablas(): void
    {
        $db = new Database();

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes (
                id INT NOT NULL AUTO_INCREMENT,
                id_persona INT NOT NULL,
                periodo_inicio DATE NOT NULL,
                periodo_fin DATE NOT NULL,
                fecha_inicio DATE NOT NULL,
                fecha_fin DATE NOT NULL,
                modo_fechas VARCHAR(20) NOT NULL DEFAULT 'rango',
                dias_solicitados DECIMAL(6,2) NOT NULL DEFAULT 0,
                estatus VARCHAR(30) NOT NULL DEFAULT 'pendiente',
                comentario TEXT NULL,
                id_jefe_autoriza INT NULL,
                fecha_autorizacion DATETIME NULL,
                comentario_autorizacion TEXT NULL,
                creado_por INT NULL,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_vac_sol_persona_periodo (id_persona, periodo_inicio, periodo_fin),
                KEY idx_vac_sol_estatus (estatus),
                KEY idx_vac_sol_fechas (fecha_inicio, fecha_fin)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'modo_fechas', "VARCHAR(20) NOT NULL DEFAULT 'rango' AFTER fecha_fin");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'rrhh_estatus', "VARCHAR(30) NOT NULL DEFAULT 'pendiente' AFTER estatus");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'rrhh_comentario', "TEXT NULL AFTER rrhh_estatus");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'rrhh_id_persona', "INT NULL AFTER rrhh_comentario");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'rrhh_fecha', "DATETIME NULL AFTER rrhh_id_persona");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'jefe_estatus', "VARCHAR(30) NOT NULL DEFAULT 'pendiente' AFTER rrhh_fecha");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'jefe_comentario', "TEXT NULL AFTER jefe_estatus");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'jefe_id_persona', "INT NULL AFTER jefe_comentario");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'jefe_fecha', "DATETIME NULL AFTER jefe_id_persona");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'fuente', "VARCHAR(40) NULL AFTER creado_por");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'fuente_ref', "VARCHAR(191) NULL AFTER fuente");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'firma_colaborador', "LONGTEXT NULL AFTER comentario");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'firma_colaborador_fecha', "DATETIME NULL AFTER firma_colaborador");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'rrhh_firma', "LONGTEXT NULL AFTER rrhh_fecha");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'rrhh_firma_fecha', "DATETIME NULL AFTER rrhh_firma");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'jefe_firma', "LONGTEXT NULL AFTER jefe_fecha");
        self::asegurarColumna($db, 'vacaciones_solicitudes', 'jefe_firma_fecha', "DATETIME NULL AFTER jefe_firma");
        self::asegurarIndice($db, 'vacaciones_solicitudes', 'idx_vac_sol_fuente', 'fuente, fuente_ref');

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.vacaciones_solicitud_dias (
                id INT NOT NULL AUTO_INCREMENT,
                id_solicitud INT NOT NULL,
                fecha DATE NOT NULL,
                cuenta DECIMAL(4,2) NOT NULL DEFAULT 1,
                tipo VARCHAR(30) NOT NULL DEFAULT 'laboral',
                PRIMARY KEY (id),
                UNIQUE KEY uq_vac_sol_dia (id_solicitud, fecha),
                KEY idx_vac_dia_fecha (fecha),
                CONSTRAINT fk_vac_dias_solicitud
                    FOREIGN KEY (id_solicitud) REFERENCES __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes(id)
                    ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.vacaciones_dias_no_laborales (
                fecha DATE NOT NULL,
                descripcion VARCHAR(255) NULL,
                activo TINYINT(1) NOT NULL DEFAULT 1,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (fecha)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.vacaciones_excel_saldos (
                id INT NOT NULL AUTO_INCREMENT,
                fuente VARCHAR(40) NOT NULL,
                id_persona INT NOT NULL,
                nombre_excel VARCHAR(255) NOT NULL,
                fecha_ingreso_excel DATE NOT NULL,
                anio INT NOT NULL,
                dias_tomados DECIMAL(6,2) NOT NULL DEFAULT 0,
                dias_otorgados DECIMAL(6,2) NOT NULL DEFAULT 0,
                dias_restantes DECIMAL(6,2) NOT NULL DEFAULT 0,
                fila_resumen INT NULL,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_vac_excel_saldo_persona (fuente, id_persona),
                KEY idx_vac_excel_saldo_anio (anio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $db->CRUD("
            CREATE TABLE IF NOT EXISTS __SPARTA_SECRET_REDACTED__.vacaciones_excel_resumen_raw (
                id INT NOT NULL AUTO_INCREMENT,
                fuente VARCHAR(40) NOT NULL,
                fila_resumen INT NOT NULL,
                id_persona INT NULL,
                nombre_excel VARCHAR(255) NOT NULL,
                nombre_normalizado VARCHAR(255) NOT NULL,
                fecha_ingreso_excel DATE NOT NULL,
                anio INT NOT NULL,
                dias_tomados DECIMAL(6,2) NOT NULL DEFAULT 0,
                dias_otorgados DECIMAL(6,2) NOT NULL DEFAULT 0,
                dias_restantes DECIMAL(6,2) NOT NULL DEFAULT 0,
                creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_vac_excel_raw_fila (fuente, fila_resumen),
                KEY idx_vac_excel_raw_persona (id_persona),
                KEY idx_vac_excel_raw_nombre (nombre_normalizado),
                KEY idx_vac_excel_raw_anio (anio)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public static function resumenPersona(int $idPersona): array
    {
        try {
            self::asegurarTablas();
            $db = new Database();
            $persona = self::obtenerPersona($db, $idPersona);
            if (!$persona) {
                return self::resultado(false, 'Persona no encontrada.');
            }

            $periodo = self::periodoActual($persona['fecha_ingreso'] ?? null);
            if (!$periodo['success']) {
                return self::resultado(true, 'Resumen de vacaciones calculado.', [
                    'persona' => $persona,
                    'periodo' => $periodo['datos'],
                    'solicitudes' => [],
                ]);
            }

            $pi = $periodo['datos']['periodo_inicio'];
            $pf = $periodo['datos']['periodo_fin'];
            $totales = self::totalesPeriodo($db, $idPersona, $pi, $pf);
            $solicitudes = self::solicitudesPersona($db, $idPersona, $pi, $pf);

            $datos = $periodo['datos'];
            $datos['dias_aprobados'] = $totales['aprobados'];
            $datos['dias_pendientes'] = $totales['pendientes'];
            $datos['dias_disponibles'] = max(0, $datos['dias_otorgados'] - $totales['aprobados'] - $totales['pendientes']);
            $datos['dias_disponibles_reales'] = max(0, $datos['dias_otorgados'] - $totales['aprobados']);

            return self::resultado(true, 'Resumen de vacaciones calculado.', [
                'persona' => $persona,
                'periodo' => $datos,
                'solicitudes' => $solicitudes,
            ]);
        } catch (Exception $e) {
            return self::resultado(false, 'Error al calcular vacaciones.', null, $e->getMessage());
        }
    }

    public static function solicitar(int $idPersona, string $fechaInicio, string $fechaFin, string $comentario, int $creadoPor, string $modoFechas = 'rango', array $fechasSeparadas = [], string $firmaColaborador = ''): array
    {
        try {
            self::asegurarTablas();
            $db = new Database();
            $persona = self::obtenerPersona($db, $idPersona);
            if (!$persona) {
                return self::resultado(false, 'Persona no encontrada.');
            }

            $periodo = self::periodoActual($persona['fecha_ingreso'] ?? null);
            if (!$periodo['success']) {
                return self::resultado(false, $periodo['mensaje'] ?? 'La persona aún no tiene vacaciones disponibles.');
            }

            $modoFechas = strtolower(trim($modoFechas));
            $modoFechas = in_array($modoFechas, ['rango', 'separados'], true) ? $modoFechas : 'rango';

            $pi = self::parseDate($periodo['datos']['periodo_inicio']);
            $pf = self::parseDate($periodo['datos']['periodo_fin']);
            $dias = [];

            if ($modoFechas === 'separados') {
                $fechasSeparadas = self::normalizarFechasSeparadas($fechasSeparadas);
                if (empty($fechasSeparadas)) {
                    return self::resultado(false, 'Selecciona al menos un día de vacaciones.');
                }

                $diasInvalidos = [];
                foreach ($fechasSeparadas as $fecha) {
                    $dt = self::parseDate($fecha);
                    if (!$dt || $dt < $pi || $dt > $pf || !self::esDiaLaboral($db, $fecha)) {
                        $diasInvalidos[] = $fecha;
                        continue;
                    }
                    $dias[] = $fecha;
                }

                if (!empty($diasInvalidos)) {
                    return self::resultado(false, 'Hay días fuera del periodo vigente o no laborales: ' . implode(', ', $diasInvalidos));
                }

                sort($dias);
                $fechaInicio = $dias[0];
                $fechaFin = $dias[count($dias) - 1];
                $fi = self::parseDate($fechaInicio);
                $ff = self::parseDate($fechaFin);
            } else {
                $fi = self::parseDate($fechaInicio);
                $ff = self::parseDate($fechaFin);
                if (!$fi || !$ff) {
                    return self::resultado(false, 'Fechas inválidas. Usa formato YYYY-MM-DD.');
                }
                if ($ff < $fi) {
                    return self::resultado(false, 'La fecha final no puede ser menor que la fecha inicial.');
                }
                $dias = self::diasLaborales($db, $fechaInicio, $fechaFin);
            }

            if ($fi < $pi || $ff > $pf) {
                return self::resultado(false, 'La solicitud debe quedar dentro del periodo vacacional vigente.');
            }

            if (empty($dias)) {
                return self::resultado(false, $modoFechas === 'rango'
                    ? 'El rango no contiene días laborales para solicitar.'
                    : 'No hay días laborales válidos en la selección.');
            }

            if (self::tieneCruceDias($db, $idPersona, $dias)) {
                return self::resultado(false, 'Ya existe una solicitud activa que cruza con esas fechas.');
            }

            $totales = self::totalesPeriodo($db, $idPersona, $periodo['datos']['periodo_inicio'], $periodo['datos']['periodo_fin']);
            $disponibles = max(0, $periodo['datos']['dias_otorgados'] - $totales['aprobados'] - $totales['pendientes']);
            $diasSolicitados = (float) count($dias);
            if ($diasSolicitados > $disponibles) {
                return self::resultado(false, 'La solicitud excede los días disponibles del periodo.');
            }

            $firmaColaborador = trim($firmaColaborador);
            if ($firmaColaborador === '' || strpos($firmaColaborador, 'data:image/png;base64,') !== 0) {
                return self::resultado(false, 'La firma digital del colaborador es requerida.');
            }

            $db->beginTransaction();
            $db->CRUD("
                INSERT INTO __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes
                    (id_persona, periodo_inicio, periodo_fin, fecha_inicio, fecha_fin, modo_fechas, dias_solicitados, estatus, comentario, firma_colaborador, firma_colaborador_fecha, creado_por)
                VALUES
                    (:id_persona, :periodo_inicio, :periodo_fin, :fecha_inicio, :fecha_fin, :modo_fechas, :dias_solicitados, 'pendiente', :comentario, :firma_colaborador, NOW(), :creado_por)
            ", [
                'id_persona' => $idPersona,
                'periodo_inicio' => $periodo['datos']['periodo_inicio'],
                'periodo_fin' => $periodo['datos']['periodo_fin'],
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'modo_fechas' => $modoFechas,
                'dias_solicitados' => $diasSolicitados,
                'comentario' => $comentario,
                'firma_colaborador' => $firmaColaborador,
                'creado_por' => $creadoPor,
            ]);
            $idSolicitud = $db->lastInsertId();
            foreach ($dias as $fecha) {
                $db->CRUD("
                    INSERT INTO __SPARTA_SECRET_REDACTED__.vacaciones_solicitud_dias (id_solicitud, fecha, cuenta, tipo)
                    VALUES (:id_solicitud, :fecha, 1, 'laboral')
                ", ['id_solicitud' => $idSolicitud, 'fecha' => $fecha]);
            }
            $db->commit();

            return self::resultado(true, 'Solicitud de vacaciones registrada.', [
                'id_solicitud' => $idSolicitud,
                'dias_solicitados' => $diasSolicitados,
            ]);
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollback();
            }
            return self::resultado(false, 'Error al registrar solicitud de vacaciones.', null, $e->getMessage());
        }
    }

    public static function listarAdmin(int $limit = 150): array
    {
        try {
            self::asegurarTablas();
            $db = new Database();
            $limit = max(20, min(300, $limit));

            $rows = $db->queryAll("
                SELECT
                    s.id,
                    s.id_persona,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                    p.numero_empleado,
                    p.fecha_ingreso,
                    s.periodo_inicio,
                    s.periodo_fin,
                    s.fecha_inicio,
                    s.fecha_fin,
                    s.modo_fechas,
                    s.dias_solicitados,
                    s.estatus,
                    s.rrhh_estatus,
                    s.rrhh_fecha,
                    s.jefe_estatus,
                    s.jefe_fecha,
                    s.comentario,
                    s.creado_en,
                    pp.nombre AS puesto,
                    d.nombre AS departamento,
                    dorg.nombre AS area
                FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes s
                INNER JOIN __SPARTA_SECRET_REDACTED__.persona p ON p.id = s.id_persona
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = pp.departamento_id
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
                WHERE COALESCE(s.fuente, '') = ''
                ORDER BY
                    CASE s.estatus
                        WHEN 'pendiente' THEN 1
                        WHEN 'aprobada' THEN 2
                        WHEN 'rechazada' THEN 3
                        ELSE 4
                    END,
                    s.creado_en DESC,
                    s.id DESC
                LIMIT {$limit}
            ");

            return self::resultado(true, 'Solicitudes encontradas.', $rows);
        } catch (Exception $e) {
            return self::resultado(false, 'Error al consultar solicitudes.', null, $e->getMessage());
        }
    }

    public static function detalleAdmin(int $idSolicitud): array
    {
        try {
            self::asegurarTablas();
            $db = new Database();
            $solicitud = $db->queryOne("
                SELECT
                    s.*,
                    CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                    p.numero_empleado,
                    p.fecha_ingreso,
                    p.estatus AS persona_estatus,
                    rrhh.nombre_resuelve AS rrhh_nombre,
                    jefe.nombre_resuelve AS jefe_nombre,
                    pp.nombre AS puesto,
                    d.nombre AS departamento,
                    dorg.nombre AS area
                FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes s
                INNER JOIN __SPARTA_SECRET_REDACTED__.persona p ON p.id = s.id_persona
                LEFT JOIN (
                    SELECT id, CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) AS nombre_resuelve
                    FROM __SPARTA_SECRET_REDACTED__.persona
                ) rrhh ON rrhh.id = s.rrhh_id_persona
                LEFT JOIN (
                    SELECT id, CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom) AS nombre_resuelve
                    FROM __SPARTA_SECRET_REDACTED__.persona
                ) jefe ON jefe.id = s.jefe_id_persona
                LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
                LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = pp.departamento_id
                LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
                WHERE s.id = :id_solicitud
                LIMIT 1
            ", ['id_solicitud' => $idSolicitud]);

            if (!$solicitud) {
                return self::resultado(false, 'Solicitud no encontrada.');
            }

            $dias = $db->queryAll("
                SELECT fecha, cuenta, tipo
                FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitud_dias
                WHERE id_solicitud = :id_solicitud
                ORDER BY fecha ASC
            ", ['id_solicitud' => $idSolicitud]);

            return self::resultado(true, 'Solicitud encontrada.', [
                'solicitud' => $solicitud,
                'dias' => $dias,
            ]);
        } catch (Exception $e) {
            return self::resultado(false, 'Error al consultar solicitud.', null, $e->getMessage());
        }
    }

    public static function resolverAdmin(int $idSolicitud, string $etapa, string $accion, string $comentario, int $idResponsable, string $firmaResponsable = ''): array
    {
        try {
            self::asegurarTablas();
            $db = new Database();
            $etapa = strtolower(trim($etapa));
            $accion = strtolower(trim($accion));
            $comentario = trim($comentario);

            if (!in_array($etapa, ['rrhh', 'jefe'], true)) {
                return self::resultado(false, 'Etapa de aprobación inválida.');
            }
            if (!in_array($accion, ['aprobada', 'rechazada'], true)) {
                return self::resultado(false, 'Acción inválida.');
            }
            if ($accion === 'rechazada' && $comentario === '') {
                return self::resultado(false, 'Escribe el motivo del rechazo.');
            }
            $firmaResponsable = trim($firmaResponsable);
            if ($accion === 'aprobada' && ($firmaResponsable === '' || strpos($firmaResponsable, 'data:image/png;base64,') !== 0)) {
                return self::resultado(false, 'La firma digital es requerida para aprobar.');
            }

            $sol = $db->queryOne("
                SELECT id, estatus, rrhh_estatus, jefe_estatus
                FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes
                WHERE id = :id_solicitud
                LIMIT 1
            ", ['id_solicitud' => $idSolicitud]);
            if (!$sol) {
                return self::resultado(false, 'Solicitud no encontrada.');
            }

            if (in_array((string) $sol['estatus'], ['tomada', 'cancelada'], true)) {
                return self::resultado(false, 'Esta solicitud no puede resolverse desde el panel.');
            }
            if ((string) $sol['estatus'] === 'rechazada') {
                return self::resultado(false, 'Esta solicitud ya fue rechazada.');
            }

            $rrhh = (string) ($sol['rrhh_estatus'] ?? 'pendiente');
            $jefe = (string) ($sol['jefe_estatus'] ?? 'pendiente');
            $estatusEtapaActual = $etapa === 'rrhh' ? $rrhh : $jefe;
            if ($estatusEtapaActual !== 'pendiente') {
                return self::resultado(false, 'Esta etapa ya fue resuelta.');
            }
            if ($etapa === 'rrhh') {
                $rrhh = $accion;
            } else {
                $jefe = $accion;
            }

            $estatusGeneral = 'pendiente';
            if ($rrhh === 'rechazada' || $jefe === 'rechazada') {
                $estatusGeneral = 'rechazada';
            } elseif ($rrhh === 'aprobada' && $jefe === 'aprobada') {
                $estatusGeneral = 'aprobada';
            }

            $colEstatus = $etapa . '_estatus';
            $colComentario = $etapa . '_comentario';
            $colPersona = $etapa . '_id_persona';
            $colFecha = $etapa . '_fecha';
            $colFirma = $etapa . '_firma';
            $colFirmaFecha = $etapa . '_firma_fecha';

            $db->CRUD("
                UPDATE __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes
                SET
                    {$colEstatus} = :accion,
                    {$colComentario} = :comentario,
                    {$colPersona} = :id_responsable,
                    {$colFecha} = NOW(),
                    {$colFirma} = CASE WHEN :accion_firma = 'aprobada' THEN :firma ELSE {$colFirma} END,
                    {$colFirmaFecha} = CASE WHEN :accion_firma_fecha = 'aprobada' THEN NOW() ELSE {$colFirmaFecha} END,
                    estatus = :estatus,
                    fecha_autorizacion = CASE WHEN :estatus_aprobada = 'aprobada' THEN NOW() ELSE fecha_autorizacion END,
                    comentario_autorizacion = CASE WHEN :estatus_rechazada = 'rechazada' THEN :comentario_rechazo ELSE comentario_autorizacion END
                WHERE id = :id_solicitud
            ", [
                'accion' => $accion,
                'comentario' => $comentario,
                'id_responsable' => $idResponsable,
                'accion_firma' => $accion,
                'firma' => $firmaResponsable,
                'accion_firma_fecha' => $accion,
                'estatus' => $estatusGeneral,
                'estatus_aprobada' => $estatusGeneral,
                'estatus_rechazada' => $estatusGeneral,
                'comentario_rechazo' => $comentario,
                'id_solicitud' => $idSolicitud,
            ]);

            return self::resultado(true, 'Solicitud actualizada.', [
                'estatus' => $estatusGeneral,
                'rrhh_estatus' => $rrhh,
                'jefe_estatus' => $jefe,
            ]);
        } catch (Exception $e) {
            return self::resultado(false, 'Error al resolver solicitud.', null, $e->getMessage());
        }
    }

    public static function upsertSaldoExcel(
        Database $db,
        string $fuente,
        int $idPersona,
        string $nombreExcel,
        string $fechaIngresoExcel,
        int $anio,
        float $diasTomados,
        float $diasOtorgados,
        float $diasRestantes,
        int $filaResumen
    ): void {
        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.vacaciones_excel_saldos
                (fuente, id_persona, nombre_excel, fecha_ingreso_excel, anio, dias_tomados, dias_otorgados, dias_restantes, fila_resumen)
            VALUES
                (:fuente, :id_persona, :nombre_excel, :fecha_ingreso_excel, :anio, :dias_tomados, :dias_otorgados, :dias_restantes, :fila_resumen)
            ON DUPLICATE KEY UPDATE
                nombre_excel = VALUES(nombre_excel),
                fecha_ingreso_excel = VALUES(fecha_ingreso_excel),
                anio = VALUES(anio),
                dias_tomados = VALUES(dias_tomados),
                dias_otorgados = VALUES(dias_otorgados),
                dias_restantes = VALUES(dias_restantes),
                fila_resumen = VALUES(fila_resumen)
        ", [
            'fuente' => $fuente,
            'id_persona' => $idPersona,
            'nombre_excel' => $nombreExcel,
            'fecha_ingreso_excel' => $fechaIngresoExcel,
            'anio' => $anio,
            'dias_tomados' => $diasTomados,
            'dias_otorgados' => $diasOtorgados,
            'dias_restantes' => $diasRestantes,
            'fila_resumen' => $filaResumen,
        ]);
    }

    public static function upsertResumenExcelRaw(
        Database $db,
        string $fuente,
        int $filaResumen,
        ?int $idPersona,
        string $nombreExcel,
        string $nombreNormalizado,
        string $fechaIngresoExcel,
        int $anio,
        float $diasTomados,
        float $diasOtorgados,
        float $diasRestantes
    ): void {
        $db->CRUD("
            INSERT INTO __SPARTA_SECRET_REDACTED__.vacaciones_excel_resumen_raw
                (fuente, fila_resumen, id_persona, nombre_excel, nombre_normalizado, fecha_ingreso_excel, anio, dias_tomados, dias_otorgados, dias_restantes)
            VALUES
                (:fuente, :fila_resumen, :id_persona, :nombre_excel, :nombre_normalizado, :fecha_ingreso_excel, :anio, :dias_tomados, :dias_otorgados, :dias_restantes)
            ON DUPLICATE KEY UPDATE
                id_persona = VALUES(id_persona),
                nombre_excel = VALUES(nombre_excel),
                nombre_normalizado = VALUES(nombre_normalizado),
                fecha_ingreso_excel = VALUES(fecha_ingreso_excel),
                anio = VALUES(anio),
                dias_tomados = VALUES(dias_tomados),
                dias_otorgados = VALUES(dias_otorgados),
                dias_restantes = VALUES(dias_restantes)
        ", [
            'fuente' => $fuente,
            'fila_resumen' => $filaResumen,
            'id_persona' => $idPersona,
            'nombre_excel' => $nombreExcel,
            'nombre_normalizado' => $nombreNormalizado,
            'fecha_ingreso_excel' => $fechaIngresoExcel,
            'anio' => $anio,
            'dias_tomados' => $diasTomados,
            'dias_otorgados' => $diasOtorgados,
            'dias_restantes' => $diasRestantes,
        ]);
    }

    public static function upsertHistoricoExcel(Database $db, string $fuente, int $idPersona, array $fechas, int $anio): int
    {
        $fechas = self::normalizarFechasSeparadas($fechas);
        $fuenteRef = $fuente . ':' . $anio . ':' . $idPersona;

        $existente = $db->queryOne("
            SELECT id
            FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes
            WHERE fuente = :fuente AND fuente_ref = :fuente_ref
            LIMIT 1
        ", ['fuente' => $fuente, 'fuente_ref' => $fuenteRef]);

        if (empty($fechas)) {
            if ($existente) {
                $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes WHERE id = :id", ['id' => (int) $existente['id']]);
            }
            return 0;
        }

        $fechaInicio = $fechas[0];
        $fechaFin = $fechas[count($fechas) - 1];
        $dias = count($fechas);
        $periodoInicio = $anio . '-01-01';
        $periodoFin = $anio . '-12-31';

        if ($existente) {
            $idSolicitud = (int) $existente['id'];
            $db->CRUD("
                UPDATE __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes
                SET
                    periodo_inicio = :periodo_inicio,
                    periodo_fin = :periodo_fin,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    modo_fechas = 'separados',
                    dias_solicitados = :dias,
                    estatus = 'tomada',
                    rrhh_estatus = 'aprobada',
                    jefe_estatus = 'aprobada',
                    comentario = :comentario
                WHERE id = :id
            ", [
                'periodo_inicio' => $periodoInicio,
                'periodo_fin' => $periodoFin,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'dias' => $dias,
                'comentario' => 'Importado desde plantilla de vacaciones ' . $anio,
                'id' => $idSolicitud,
            ]);
            $db->CRUD("DELETE FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitud_dias WHERE id_solicitud = :id", ['id' => $idSolicitud]);
        } else {
            $db->CRUD("
                INSERT INTO __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes
                    (id_persona, periodo_inicio, periodo_fin, fecha_inicio, fecha_fin, modo_fechas, dias_solicitados, estatus,
                     rrhh_estatus, jefe_estatus, comentario, creado_por, fuente, fuente_ref)
                VALUES
                    (:id_persona, :periodo_inicio, :periodo_fin, :fecha_inicio, :fecha_fin, 'separados', :dias, 'tomada',
                     'aprobada', 'aprobada', :comentario, NULL, :fuente, :fuente_ref)
            ", [
                'id_persona' => $idPersona,
                'periodo_inicio' => $periodoInicio,
                'periodo_fin' => $periodoFin,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'dias' => $dias,
                'comentario' => 'Importado desde plantilla de vacaciones ' . $anio,
                'fuente' => $fuente,
                'fuente_ref' => $fuenteRef,
            ]);
            $idSolicitud = $db->lastInsertId();
        }

        foreach ($fechas as $fecha) {
            $db->CRUD("
                INSERT INTO __SPARTA_SECRET_REDACTED__.vacaciones_solicitud_dias (id_solicitud, fecha, cuenta, tipo)
                VALUES (:id_solicitud, :fecha, 1, 'laboral')
            ", ['id_solicitud' => $idSolicitud, 'fecha' => $fecha]);
        }

        return $dias;
    }

    private static function obtenerPersona(Database $db, int $idPersona): ?array
    {
        $persona = $db->queryOne("
            SELECT
                p.id,
                p.numero_empleado,
                CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom) AS nombre_completo,
                p.fecha_ingreso,
                p.estatus,
                pp.nombre AS puesto,
                d.nombre AS departamento,
                dorg.nombre AS area
            FROM __SPARTA_SECRET_REDACTED__.persona p
            LEFT JOIN __SPARTA_SECRET_REDACTED__.asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
            LEFT JOIN __SPARTA_SECRET_REDACTED__.puesto pp ON pp.id = ap.id_puesto
            LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento d ON d.id = pp.departamento_id
            LEFT JOIN __SPARTA_SECRET_REDACTED__.departamento_organizacional dorg ON dorg.id = d.id_departamento_organizacional
            WHERE p.id = :id_persona
            LIMIT 1
        ", ['id_persona' => $idPersona]);

        if (!$persona) {
            return null;
        }

        if (empty($persona['fecha_ingreso']) || (string) $persona['fecha_ingreso'] === '0000-00-00') {
            $fallback = self::fechaIngresoExcelFallback($db, $idPersona, (string) ($persona['nombre_completo'] ?? ''));
            if ($fallback) {
                $persona['fecha_ingreso'] = $fallback['fecha_ingreso_excel'];
                $persona['fecha_ingreso_origen'] = 'excel_vacaciones';
                $persona['fecha_ingreso_excel_fila'] = $fallback['fila_resumen'];
            }
        }

        return $persona;
    }

    private static function fechaIngresoExcelFallback(Database $db, int $idPersona, string $nombreCompleto): ?array
    {
        $row = $db->queryOne("
            SELECT fecha_ingreso_excel, fila_resumen
            FROM __SPARTA_SECRET_REDACTED__.vacaciones_excel_resumen_raw
            WHERE id_persona = :id_persona
            ORDER BY anio DESC, fila_resumen ASC
            LIMIT 1
        ", ['id_persona' => $idPersona]);
        if ($row) {
            return $row;
        }

        $nombre = self::normalizarNombre($nombreCompleto);
        if ($nombre === '') {
            return null;
        }

        return $db->queryOne("
            SELECT fecha_ingreso_excel, fila_resumen
            FROM __SPARTA_SECRET_REDACTED__.vacaciones_excel_resumen_raw
            WHERE nombre_normalizado = :nombre
            ORDER BY anio DESC, fila_resumen ASC
            LIMIT 1
        ", ['nombre' => $nombre]);
    }

    public static function normalizarNombre(string $nombre): string
    {
        $nombre = strtr(trim($nombre), [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
            'á' => 'A', 'é' => 'E', 'í' => 'I', 'ó' => 'O', 'ú' => 'U', 'ü' => 'U', 'ñ' => 'N',
        ]);
        $nombre = strtoupper($nombre);
        $nombre = preg_replace('/[^A-Z0-9 ]+/', ' ', $nombre) ?: '';
        $nombre = preg_replace('/\s+/', ' ', $nombre) ?: '';

        return trim($nombre);
    }

    private static function periodoActual(?string $fechaIngreso): array
    {
        $ingreso = self::parseDate((string) $fechaIngreso);
        if (!$ingreso) {
            return self::resultado(false, 'La persona no tiene fecha de ingreso capturada.', [
                'fecha_ingreso' => $fechaIngreso,
                'dias_otorgados' => 0,
                'dias_disponibles' => 0,
            ]);
        }

        $hoy = new DateTimeImmutable('today', new DateTimeZone(self::TZ));
        $anios = self::aniosCompletos($ingreso, $hoy);
        if ($anios < 1) {
            $primerAniversario = $ingreso->add(new DateInterval('P1Y'));
            return self::resultado(false, 'La persona aún no cumple un año laboral.', [
                'fecha_ingreso' => $ingreso->format('Y-m-d'),
                'anios_laborales' => $anios,
                'proximo_aniversario' => $primerAniversario->format('Y-m-d'),
                'dias_otorgados' => 0,
                'dias_disponibles' => 0,
            ]);
        }

        $inicio = $ingreso->add(new DateInterval('P' . $anios . 'Y'));
        $siguiente = $ingreso->add(new DateInterval('P' . ($anios + 1) . 'Y'));
        $fin = $siguiente->sub(new DateInterval('P1D'));

        return self::resultado(true, 'Periodo vigente calculado.', [
            'fecha_ingreso' => $ingreso->format('Y-m-d'),
            'anios_laborales' => $anios,
            'periodo_inicio' => $inicio->format('Y-m-d'),
            'periodo_fin' => $fin->format('Y-m-d'),
            'proximo_aniversario' => $siguiente->format('Y-m-d'),
            'dias_otorgados' => self::diasPorAntiguedad($anios),
        ]);
    }

    private static function aniosCompletos(DateTimeImmutable $inicio, DateTimeImmutable $fecha): int
    {
        $anios = (int) $inicio->diff($fecha)->y;
        return max(0, $anios);
    }

    private static function diasPorAntiguedad(int $anios): int
    {
        if ($anios <= 0) return 0;
        if ($anios === 1) return 12;
        if ($anios === 2) return 14;
        if ($anios === 3) return 16;
        if ($anios === 4) return 18;
        if ($anios === 5) return 20;
        if ($anios <= 10) return 22;
        if ($anios <= 15) return 24;
        if ($anios <= 20) return 26;
        if ($anios <= 25) return 28;
        if ($anios <= 30) return 30;
        return 32;
    }

    private static function totalesPeriodo(Database $db, int $idPersona, string $periodoInicio, string $periodoFin): array
    {
        $row = $db->queryOne("
            SELECT
                COALESCE(SUM(CASE WHEN s.estatus IN ('aprobada', 'tomada') THEN d.cuenta ELSE 0 END), 0) AS aprobados,
                COALESCE(SUM(CASE WHEN s.estatus = 'pendiente' THEN d.cuenta ELSE 0 END), 0) AS pendientes
            FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes s
            INNER JOIN __SPARTA_SECRET_REDACTED__.vacaciones_solicitud_dias d ON d.id_solicitud = s.id
            WHERE s.id_persona = :id_persona
              AND d.fecha BETWEEN :periodo_inicio AND :periodo_fin
              AND s.estatus NOT IN ('cancelada', 'rechazada')
        ", [
            'id_persona' => $idPersona,
            'periodo_inicio' => $periodoInicio,
            'periodo_fin' => $periodoFin,
        ]) ?: [];

        return [
            'aprobados' => (float) ($row['aprobados'] ?? 0),
            'pendientes' => (float) ($row['pendientes'] ?? 0),
        ];
    }

    private static function solicitudesPersona(Database $db, int $idPersona, ?string $periodoInicio = null, ?string $periodoFin = null): array
    {
        $filtroDiasPeriodo = '';
        $havingPeriodo = '';
        $selectPeriodoInicio = 's.periodo_inicio';
        $selectPeriodoFin = 's.periodo_fin';
        $params = ['id_persona' => $idPersona];
        if ($periodoInicio && $periodoFin) {
            $filtroDiasPeriodo = " AND d.fecha BETWEEN :periodo_inicio AND :periodo_fin";
            $havingPeriodo = " HAVING dias_solicitados > 0";
            $selectPeriodoInicio = ':periodo_inicio';
            $selectPeriodoFin = ':periodo_fin';
            $params['periodo_inicio'] = $periodoInicio;
            $params['periodo_fin'] = $periodoFin;
        }

        return $db->queryAll("
            SELECT
                s.id,
                {$selectPeriodoInicio} AS periodo_inicio,
                {$selectPeriodoFin} AS periodo_fin,
                COALESCE(MIN(d.fecha), s.fecha_inicio) AS fecha_inicio,
                COALESCE(MAX(d.fecha), s.fecha_fin) AS fecha_fin,
                CASE
                    WHEN COUNT(d.fecha) <= 1 THEN 'rango'
                    WHEN DATEDIFF(MAX(d.fecha), MIN(d.fecha)) + 1 = COUNT(d.fecha) THEN 'rango'
                    ELSE s.modo_fechas
                END AS modo_fechas,
                COALESCE(SUM(d.cuenta), s.dias_solicitados) AS dias_solicitados,
                s.estatus,
                s.comentario,
                s.creado_en,
                s.fecha_autorizacion,
                s.comentario_autorizacion
            FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes s
            LEFT JOIN __SPARTA_SECRET_REDACTED__.vacaciones_solicitud_dias d
                ON d.id_solicitud = s.id
                {$filtroDiasPeriodo}
            WHERE s.id_persona = :id_persona
            GROUP BY
                s.id, s.periodo_inicio, s.periodo_fin, s.fecha_inicio, s.fecha_fin, s.modo_fechas,
                s.dias_solicitados, s.estatus, s.comentario, s.creado_en, s.fecha_autorizacion, s.comentario_autorizacion
            {$havingPeriodo}
            ORDER BY s.creado_en DESC, s.id DESC
            LIMIT 30
        ", $params);
    }

    private static function tieneCruceDias(Database $db, int $idPersona, array $dias): bool
    {
        $dias = self::normalizarFechasSeparadas($dias);
        if (empty($dias)) {
            return false;
        }

        $params = ['id_persona' => $idPersona];
        $placeholders = [];
        foreach ($dias as $i => $fecha) {
            $key = 'fecha_' . $i;
            $placeholders[] = ':' . $key;
            $params[$key] = $fecha;
        }

        $row = $db->queryOne("
            SELECT s.id
            FROM __SPARTA_SECRET_REDACTED__.vacaciones_solicitudes s
            INNER JOIN __SPARTA_SECRET_REDACTED__.vacaciones_solicitud_dias d ON d.id_solicitud = s.id
            WHERE s.id_persona = :id_persona
              AND s.estatus NOT IN ('cancelada', 'rechazada')
              AND d.fecha IN (" . implode(',', $placeholders) . ")
            LIMIT 1
        ", $params);

        return !empty($row);
    }

    private static function diasLaborales(Database $db, string $fechaInicio, string $fechaFin): array
    {
        $feriados = [];
        foreach ($db->queryAll("
            SELECT fecha
            FROM __SPARTA_SECRET_REDACTED__.vacaciones_dias_no_laborales
            WHERE activo = 1
              AND fecha BETWEEN :fecha_inicio AND :fecha_fin
        ", ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) as $row) {
            $feriados[$row['fecha']] = true;
        }

        $dias = [];
        $fecha = self::parseDate($fechaInicio);
        $fin = self::parseDate($fechaFin);
        while ($fecha && $fin && $fecha <= $fin) {
            $ymd = $fecha->format('Y-m-d');
            $dow = (int) $fecha->format('N');
            if ($dow <= 5 && !isset($feriados[$ymd])) {
                $dias[] = $ymd;
            }
            $fecha = $fecha->add(new DateInterval('P1D'));
        }

        return $dias;
    }

    private static function esDiaLaboral(Database $db, string $fecha): bool
    {
        return in_array($fecha, self::diasLaborales($db, $fecha, $fecha), true);
    }

    private static function normalizarFechasSeparadas(array $fechas): array
    {
        $validas = [];
        foreach ($fechas as $fecha) {
            $fecha = trim((string) $fecha);
            if (self::parseDate($fecha)) {
                $validas[$fecha] = $fecha;
            }
        }
        $validas = array_values($validas);
        sort($validas);
        return $validas;
    }

    private static function asegurarColumna(Database $db, string $tabla, string $columna, string $definicion): void
    {
        $row = $db->queryOne("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :tabla
              AND COLUMN_NAME = :columna
            LIMIT 1
        ", ['tabla' => $tabla, 'columna' => $columna]);

        if (!$row) {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.$tabla ADD COLUMN $columna $definicion");
        }
    }

    private static function asegurarIndice(Database $db, string $tabla, string $indice, string $columnas): void
    {
        $row = $db->queryOne("
            SELECT INDEX_NAME
            FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :tabla
              AND INDEX_NAME = :indice
            LIMIT 1
        ", ['tabla' => $tabla, 'indice' => $indice]);

        if (!$row) {
            $db->CRUD("ALTER TABLE __SPARTA_SECRET_REDACTED__.$tabla ADD INDEX $indice ($columnas)");
        }
    }

    private static function parseDate(string $fecha): ?DateTimeImmutable
    {
        $fecha = trim($fecha);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return null;
        }
        $dt = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, new DateTimeZone(self::TZ));
        return $dt instanceof DateTimeImmutable ? $dt : null;
    }
}
