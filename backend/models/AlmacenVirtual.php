<?php

namespace Models;

use Core\Model;
use Core\Database;

class AlmacenVirtual extends Model
{
    private const MODULO_ALMACEN_VIRTUAL = 139;
    private const CELULA_MOTOS_ADJUDICADAS = 1;
    private const CELULA_FURIAMOTOS = 2;
    private const TRACKING_ESTATUS_RECOLECTADA = ['recolectada', 'recolectado', 'completado', 'completada'];
    private const TRACKING_RUTAS_NO_INVENTARIO = ['borrador', 'cancelada'];
    private const ESTATUS_PENDIENTE_EVIDENCIAS = 'pendiente_evidencias';
    private const ESTATUS_INCIDENCIA_EVIDENCIAS = 'incidencia_evidencias';
    private const ESTATUS_PENDIENTE_RECEPCION = 'pendiente_recepcion';
    private const ESTATUS_INCIDENCIA_RECEPCION = 'incidencia_recepcion';
    private const ESTATUS_PENDIENTE_REVISION = 'pendiente_revision';
    private const ESTATUS_EN_REVISION = 'en_revision';
    private const ESTATUS_REPARADA = 'reparada';
    private const ESTATUS_FUERA_PRESUPUESTO = 'fuera_presupuesto';
    private const ESTATUS_IRREPARABLE = 'irreparable';
    private const ESTATUS_LISTA_VENTA = 'lista_venta';
    private const ESTATUS_EN_TRASPASO = 'en_traspaso';
    private const ORIGEN_OVERRIDE_SUPERVISOR = 'override_supervisor';
    private const EVENTO_ASIGNACION_MECANICO = 'asignacion_mecanico';
    private const EVENTO_CIERRE_REVISION = 'cierre_revision_mecanica';
    private const EVENTO_ENVIO_PISO_VENTA = 'envio_piso_venta';
    private const EVENTO_ORDEN_TRASPASO = 'orden_traspaso';
    private const EVENTO_CIERRE_TRASPASO = 'cierre_traspaso';
    private const ETAPA_EVIDENCIAS_INGRESO = 'evidencias_ingreso';
    private const ETAPA_REVISION_MECANICA = 'revision_mecanica';
    private const SLOTS_EVIDENCIAS_INGRESO = [
        'foto_frontal' => 'Foto frontal',
        'foto_lateral_derecha' => 'Foto lateral derecha',
        'foto_trasera' => 'Foto trasera',
        'foto_lateral_izquierda' => 'Foto lateral izquierda',
        'foto_vin' => 'Foto VIN/NIV',
    ];
    private const SLOTS_EVIDENCIAS_REVISION = [
        'revision_mecanica' => 'Evidencia reparacion mecanica',
        'revision_electrica' => 'Evidencia reparacion electrica',
        'revision_estetica' => 'Evidencia reparacion estetica',
    ];
    private const CHECKLIST_REVISION_MECANICA = [
        'mecanica' => [
            'titulo' => 'Reparacion mecanica',
            'items' => [
                ['clave' => 'calibracion_llantas', 'descripcion' => 'CALIBRACION DE LLANTAS', 'tipo_servicio' => 'na'],
                ['clave' => 'ajuste_tornilleria', 'descripcion' => 'AJUSTE DE TORNILLERIA', 'tipo_servicio' => 'na'],
                ['clave' => 'ajuste_calibracion_cadena', 'descripcion' => 'AJUSTE Y CALIBRACION CADENA', 'tipo_servicio' => 'na'],
                ['clave' => 'chicote_clush', 'descripcion' => 'CHICOTE DE CLUSH', 'tipo_servicio' => 'mp_mc'],
                ['clave' => 'chicote_frenos', 'descripcion' => 'CHICOTE DE FRENOS', 'tipo_servicio' => 'mp_mc'],
                ['clave' => 'transmision', 'descripcion' => 'TRANSMISION', 'tipo_servicio' => 'mp_mc'],
                ['clave' => 'amortiguadores', 'descripcion' => 'AMORTIGUADORES', 'tipo_servicio' => 'mp_mc'],
                ['clave' => 'direccion', 'descripcion' => 'DIRECCION', 'tipo_servicio' => 'mp_mc'],
                ['clave' => 'carburador', 'descripcion' => 'CARBURADOR', 'tipo_servicio' => 'mp_mc'],
                ['clave' => 'radiador', 'descripcion' => 'RADIADOR', 'tipo_servicio' => 'mp_mc'],
                ['clave' => 'cambio_aceite', 'descripcion' => 'CAMBIO DE ACEITE', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_balatas', 'descripcion' => 'CAMBIO DE BALATAS', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_bujia', 'descripcion' => 'CAMBIO DE BUJIA', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_baleros', 'descripcion' => 'CAMBIO DE BALEROS', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_cadena', 'descripcion' => 'CAMBIO DE CADENA', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_chicote_clush', 'descripcion' => 'CAMBIO DE CHICOTE DE CLUSH', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_chicotes_frenos', 'descripcion' => 'CAMBIO DE CHICOTES DE FRENOS', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_liquido_frenos', 'descripcion' => 'CAMBIO DE LIQUIDO DE FRENOS', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_disco', 'descripcion' => 'CAMBIO DE DISCO', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_carburador', 'descripcion' => 'CAMBIO DE CARBURADOR', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_llantas', 'descripcion' => 'CAMBIO DE LLANTAS', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_rayos', 'descripcion' => 'CAMBIO DE RAYOS', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_filtro_aire', 'descripcion' => 'CAMBIO DE FILTRO DE AIRE', 'tipo_servicio' => 'na'],
            ],
        ],
        'electrica' => [
            'titulo' => 'Reparacion electrica',
            'items' => [
                ['clave' => 'carga_bateria', 'descripcion' => 'CARGA DE BATERIA', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_bateria', 'descripcion' => 'CAMBIO DE BATERIA', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_focos', 'descripcion' => 'CAMBIO DE FOCOS', 'tipo_servicio' => 'na'],
                ['clave' => 'cambio_arnes', 'descripcion' => 'CAMBIO DE ARNES', 'tipo_servicio' => 'na'],
                ['clave' => 'reparacion_cables', 'descripcion' => 'REPARACION DE CABLES', 'tipo_servicio' => 'na'],
                ['clave' => 'reparacion_arnes', 'descripcion' => 'REPARACION DE ARNES', 'tipo_servicio' => 'na'],
                ['clave' => 'reparacion_cambio_claxon', 'descripcion' => 'REPARACION/CAMBIO CLAXON', 'tipo_servicio' => 'na'],
                ['clave' => 'reparacion_cambio_tablero', 'descripcion' => 'REPARACION/CAMBIO TABLERO', 'tipo_servicio' => 'na'],
            ],
        ],
        'estetica' => [
            'titulo' => 'Reparacion estetica',
            'items' => [
                ['clave' => 'lavado_unidad', 'descripcion' => 'LAVADO DE UNIDAD', 'tipo_servicio' => 'na'],
                ['clave' => 'reparacion_plasticos', 'descripcion' => 'REPARACION DE PLASTICOS', 'tipo_servicio' => 'na'],
                ['clave' => 'reparacion_carroceria', 'descripcion' => 'REPARACION DE CARROCERIA', 'tipo_servicio' => 'na'],
            ],
        ],
    ];

    private Database $db;
    private static ?array $adjOperacionColumnas = null;

    public function __construct()
    {
        $this->db = new Database();
        $this->asegurarModuloWeb();
    }

    public static function moduloAlmacenVirtual(): int
    {
        return self::MODULO_ALMACEN_VIRTUAL;
    }

    public function obtenerCelulas(): array
    {
        return [
            self::CELULA_MOTOS_ADJUDICADAS => 'Motos Adjudicadas',
            self::CELULA_FURIAMOTOS => 'FuriaMotos',
        ];
    }

    public function obtenerResumen(): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'tablas_disponibles' => false,
                'total' => 0,
                'por_estatus' => [],
                'por_celula' => [],
            ];
        }

        $total = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_unidades
             WHERE deleted_at IS NULL"
        );

        $estatusRows = $this->db->queryAll(
            "SELECT estatus_inventario, COUNT(*) AS total
             FROM av_unidades
             WHERE deleted_at IS NULL
             GROUP BY estatus_inventario
             ORDER BY total DESC, estatus_inventario ASC"
        ) ?: [];

        $celulas = $this->obtenerCelulas();
        $celulaRows = $this->db->queryAll(
            "SELECT id_celula, COUNT(*) AS total
             FROM av_unidades
             WHERE deleted_at IS NULL
             GROUP BY id_celula
             ORDER BY id_celula ASC"
        ) ?: [];
        foreach ($celulaRows as &$row) {
            $id = (int) ($row['id_celula'] ?? 0);
            $row['nombre_celula'] = $celulas[$id] ?? ('Celula ' . $id);
        }
        unset($row);

        return [
            'tablas_disponibles' => true,
            'total' => (int) ($total['total'] ?? 0),
            'por_estatus' => $estatusRows,
            'por_celula' => $celulaRows,
        ];
    }

    public function listarUnidades(array $filtros = []): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
            ];
        }

        $page = max(1, (int) ($filtros['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));
        $trackingDisponible = $this->trackingRecoleccionDisponible();

        $where = ['u.deleted_at IS NULL'];
        $params = [];

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $where[] = "(
                u.folio_unidad LIKE :q
                OR u.vin LIKE :q
                OR u.no_motor LIKE :q
                OR u.placas LIKE :q
                OR u.marca LIKE :q
                OR u.modelo LIKE :q
                OR CAST(u.id_unidad AS CHAR) LIKE :q
                OR CAST(u.id_credito AS CHAR) LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        $idCelula = (int) ($filtros['id_celula'] ?? 0);
        if ($idCelula > 0) {
            $where[] = 'u.id_celula = :id_celula';
            $params['id_celula'] = $idCelula;
        }

        $estatus = trim((string) ($filtros['estatus'] ?? ''));
        if ($estatus !== '') {
            $where[] = 'u.estatus_inventario = :estatus';
            $params['estatus'] = $estatus;
        }

        $idUbicacion = (int) ($filtros['id_ubicacion'] ?? 0);
        if ($idUbicacion > 0) {
            $where[] = 'u.id_ubicacion_actual = :id_ubicacion';
            $params['id_ubicacion'] = $idUbicacion;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalRow = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_unidades u
             {$whereSql}",
            $params
        ) ?: [];
        $total = (int) ($totalRow['total'] ?? 0);
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);
        $offset = ($page - 1) * $limit;
        $trackingSelect = <<<SQL
            NULL AS tracking_id_detalle,
            NULL AS tracking_estatus_recoleccion,
            NULL AS tracking_id_ruta,
            NULL AS tracking_nombre_ruta,
            NULL AS tracking_estatus_ruta,
            NULL AS tracking_cedis_destino_nombre,
            NULL AS tracking_fecha_finalizacion_fmt,
        SQL;
        $trackingJoin = '';
        if ($trackingDisponible) {
            $estatusRecolectada = $this->trackingRecolectadaSqlIn();
            $rutasNoInventario = $this->trackingRutasNoInventarioSqlIn();
            $trackingSelect = <<<SQL
            trk_det.id_detalle AS tracking_id_detalle,
            trk_det.estatus_recoleccion AS tracking_estatus_recoleccion,
            trk_ruta.id_ruta AS tracking_id_ruta,
            trk_ruta.nombre_ruta AS tracking_nombre_ruta,
            trk_ruta.estatus_ruta AS tracking_estatus_ruta,
            cedis_dest.nombre_agencia AS tracking_cedis_destino_nombre,
            DATE_FORMAT(trk_ruta.fecha_finalizacion, '%d/%m/%Y') AS tracking_fecha_finalizacion_fmt,
            SQL;
            $trackingJoin = <<<SQL
        LEFT JOIN (
            SELECT atd_idx.id_credito, MAX(atd_idx.id_detalle) AS id_detalle
            FROM asigna_horas_tracking_detalle atd_idx
            INNER JOIN asigna_horas_tracking atr_idx ON atr_idx.id_ruta = atd_idx.id_ruta
            WHERE atd_idx.id_credito IS NOT NULL
              AND LOWER(TRIM(COALESCE(atd_idx.estatus_recoleccion, ''))) IN ({$estatusRecolectada})
              AND LOWER(TRIM(COALESCE(atr_idx.estatus_ruta, ''))) NOT IN ({$rutasNoInventario})
            GROUP BY atd_idx.id_credito
        ) trk_idx ON trk_idx.id_credito = u.id_credito
        LEFT JOIN asigna_horas_tracking_detalle trk_det ON trk_det.id_detalle = trk_idx.id_detalle
        LEFT JOIN asigna_horas_tracking trk_ruta ON trk_ruta.id_ruta = trk_det.id_ruta
        LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = trk_ruta.id_cedis_destino
        SQL;
        }

        $sql = <<<SQL
        SELECT
            u.id_unidad,
            u.folio_unidad,
            u.id_celula,
            u.id_origen,
            u.id_credito,
            u.vin,
            u.no_motor,
            u.placas,
            u.marca,
            u.modelo,
            u.anio,
            u.color,
            u.kilometraje,
            u.tipo_unidad,
            u.categoria,
            u.cilindraje,
            u.estatus_inventario,
            u.id_ubicacion_actual,
            ub.nombre_ubicacion,
            ub.tipo_ubicacion,
            {$trackingSelect}
            DATE_FORMAT(u.fecha_ingreso_virtual, '%d/%m/%Y %H:%i') AS fecha_ingreso_virtual_fmt,
            DATE_FORMAT(u.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
            DATE_FORMAT(u.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
        FROM av_unidades u
        LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
        {$trackingJoin}
        {$whereSql}
        ORDER BY u.fecha_alta DESC, u.id_unidad DESC
        LIMIT {$limit} OFFSET {$offset}
        SQL;

        $rows = $this->db->queryAll($sql, $params) ?: [];
        $celulas = $this->obtenerCelulas();
        foreach ($rows as &$row) {
            $id = (int) ($row['id_celula'] ?? 0);
            $row['nombre_celula'] = $celulas[$id] ?? ('Celula ' . $id);
        }
        unset($row);

        return [
            'success' => true,
            'rows' => $rows,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function listarUbicacionesActivas(): array
    {
        if (!$this->tablaExiste('av_ubicaciones')) {
            return [];
        }

        return $this->db->queryAll(
            "SELECT id_ubicacion, clave_ubicacion, nombre_ubicacion, tipo_ubicacion
             FROM av_ubicaciones
             WHERE activo = 1
             ORDER BY tipo_ubicacion ASC, nombre_ubicacion ASC"
        ) ?: [];
    }

    public function obtenerSlotsEvidenciasIngreso(): array
    {
        $slots = [];
        foreach (self::SLOTS_EVIDENCIAS_INGRESO as $slot => $titulo) {
            $slots[] = ['slot' => $slot, 'titulo' => $titulo];
        }

        return $slots;
    }

    public function obtenerChecklistRevisionMecanica(): array
    {
        return self::CHECKLIST_REVISION_MECANICA;
    }

    public function obtenerResumenEvidencias(): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'tablas_disponibles' => false,
                'pendientes' => 0,
                'incidencias' => 0,
                'listas_recepcion' => 0,
                'total_abiertas' => 0,
            ];
        }

        $rows = $this->db->queryAll(
            "SELECT estatus_inventario, COUNT(*) AS total
             FROM av_unidades
             WHERE deleted_at IS NULL
               AND estatus_inventario IN (:pendiente, :incidencia, :recepcion)
             GROUP BY estatus_inventario",
            [
                'pendiente' => self::ESTATUS_PENDIENTE_EVIDENCIAS,
                'incidencia' => self::ESTATUS_INCIDENCIA_EVIDENCIAS,
                'recepcion' => self::ESTATUS_PENDIENTE_RECEPCION,
            ]
        ) ?: [];

        $resumen = [
            'tablas_disponibles' => true,
            'pendientes' => 0,
            'incidencias' => 0,
            'listas_recepcion' => 0,
            'total_abiertas' => 0,
        ];
        foreach ($rows as $row) {
            $estatus = (string) ($row['estatus_inventario'] ?? '');
            $total = (int) ($row['total'] ?? 0);
            if ($estatus === self::ESTATUS_PENDIENTE_EVIDENCIAS) {
                $resumen['pendientes'] = $total;
            } elseif ($estatus === self::ESTATUS_INCIDENCIA_EVIDENCIAS) {
                $resumen['incidencias'] = $total;
            } elseif ($estatus === self::ESTATUS_PENDIENTE_RECEPCION) {
                $resumen['listas_recepcion'] = $total;
            }
        }
        $resumen['total_abiertas'] = $resumen['pendientes'] + $resumen['incidencias'];

        return $resumen;
    }

    public function listarEvidenciasUnidades(array $filtros = []): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
                'pages' => 1,
            ];
        }

        $page = max(1, (int) ($filtros['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));
        $trackingDisponible = $this->trackingRecoleccionDisponible();

        $where = ['u.deleted_at IS NULL'];
        $params = [];

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $busqueda = [
                'u.folio_unidad LIKE :q',
                'u.vin LIKE :q',
                'u.no_motor LIKE :q',
                'u.placas LIKE :q',
                'u.marca LIKE :q',
                'u.modelo LIKE :q',
                'CAST(u.id_unidad AS CHAR) LIKE :q',
                'CAST(u.id_origen AS CHAR) LIKE :q',
                'CAST(u.id_credito AS CHAR) LIKE :q',
            ];
            if ($trackingDisponible) {
                $busqueda[] = 'trk_ruta.nombre_ruta LIKE :q';
                $busqueda[] = 'cedis_dest.nombre_agencia LIKE :q';
            }
            $where[] = '(' . implode(' OR ', $busqueda) . ')';
            $params['q'] = '%' . $q . '%';
        }

        $idCelula = (int) ($filtros['id_celula'] ?? 0);
        if ($idCelula > 0) {
            $where[] = 'u.id_celula = :id_celula';
            $params['id_celula'] = $idCelula;
        }

        $estatus = trim((string) ($filtros['estatus'] ?? 'abiertas'));
        if ($estatus === '' || $estatus === 'abiertas') {
            $where[] = "u.estatus_inventario IN (:estatus_pendiente_ev, :estatus_incidencia_ev)";
            $params['estatus_pendiente_ev'] = self::ESTATUS_PENDIENTE_EVIDENCIAS;
            $params['estatus_incidencia_ev'] = self::ESTATUS_INCIDENCIA_EVIDENCIAS;
        } else {
            $where[] = 'u.estatus_inventario = :estatus';
            $params['estatus'] = $estatus;
        }

        $trackingSelect = <<<SQL
            NULL AS tracking_id_detalle,
            NULL AS tracking_estatus_recoleccion,
            NULL AS tracking_id_ruta,
            NULL AS tracking_nombre_ruta,
            NULL AS tracking_estatus_ruta,
            NULL AS tracking_cedis_destino_nombre,
            NULL AS tracking_fecha_finalizacion_fmt,
        SQL;
        $trackingJoin = '';
        if ($trackingDisponible) {
            $estatusRecolectada = $this->trackingRecolectadaSqlIn();
            $rutasNoInventario = $this->trackingRutasNoInventarioSqlIn();
            $trackingSelect = <<<SQL
            trk_det.id_detalle AS tracking_id_detalle,
            trk_det.estatus_recoleccion AS tracking_estatus_recoleccion,
            trk_ruta.id_ruta AS tracking_id_ruta,
            trk_ruta.nombre_ruta AS tracking_nombre_ruta,
            trk_ruta.estatus_ruta AS tracking_estatus_ruta,
            cedis_dest.nombre_agencia AS tracking_cedis_destino_nombre,
            DATE_FORMAT(trk_ruta.fecha_finalizacion, '%d/%m/%Y') AS tracking_fecha_finalizacion_fmt,
            SQL;
            $trackingJoin = <<<SQL
        LEFT JOIN (
            SELECT atd_idx.id_credito, MAX(atd_idx.id_detalle) AS id_detalle
            FROM asigna_horas_tracking_detalle atd_idx
            INNER JOIN asigna_horas_tracking atr_idx ON atr_idx.id_ruta = atd_idx.id_ruta
            WHERE atd_idx.id_credito IS NOT NULL
              AND LOWER(TRIM(COALESCE(atd_idx.estatus_recoleccion, ''))) IN ({$estatusRecolectada})
              AND LOWER(TRIM(COALESCE(atr_idx.estatus_ruta, ''))) NOT IN ({$rutasNoInventario})
            GROUP BY atd_idx.id_credito
        ) trk_idx ON trk_idx.id_credito = u.id_credito
        LEFT JOIN asigna_horas_tracking_detalle trk_det ON trk_det.id_detalle = trk_idx.id_detalle
        LEFT JOIN asigna_horas_tracking trk_ruta ON trk_ruta.id_ruta = trk_det.id_ruta
        LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = trk_ruta.id_cedis_destino
        SQL;
        }

        $slotsSql = $this->sqlInConstantes(array_keys(self::SLOTS_EVIDENCIAS_INGRESO));
        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalRow = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_unidades u
             {$trackingJoin}
             {$whereSql}",
            $params
        ) ?: [];
        $total = (int) ($totalRow['total'] ?? 0);
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);
        $offset = ($page - 1) * $limit;

        $rows = $this->db->queryAll(
            "SELECT
                u.id_unidad,
                u.folio_unidad,
                u.id_celula,
                u.id_origen,
                u.id_credito,
                u.vin,
                u.no_motor,
                u.placas,
                u.marca,
                u.modelo,
                u.anio,
                u.color,
                u.estatus_inventario,
                {$trackingSelect}
                COALESCE(ev.evidencias_recibidas, 0) AS evidencias_recibidas,
                COALESCE(ev.evidencias_validadas, 0) AS evidencias_validadas,
                cod.codigo AS codigo_verificacion,
                cod.estatus AS codigo_estatus,
                DATE_FORMAT(cod.fecha_expiracion, '%d/%m/%Y %H:%i') AS codigo_expiracion_fmt,
                DATE_FORMAT(u.fecha_ingreso_virtual, '%d/%m/%Y %H:%i') AS fecha_ingreso_virtual_fmt,
                DATE_FORMAT(u.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
             FROM av_unidades u
             {$trackingJoin}
             LEFT JOIN (
                SELECT id_unidad,
                    COUNT(DISTINCT CASE
                        WHEN etapa = :etapa_ev_rec
                         AND slot IN ({$slotsSql})
                         AND estatus NOT IN ('reemplazado', 'eliminado')
                        THEN slot END) AS evidencias_recibidas,
                    COUNT(DISTINCT CASE
                        WHEN etapa = :etapa_ev_val
                         AND slot IN ({$slotsSql})
                         AND estatus = 'validado'
                        THEN slot END) AS evidencias_validadas
                FROM av_evidencias
                GROUP BY id_unidad
             ) ev ON ev.id_unidad = u.id_unidad
             LEFT JOIN (
                SELECT c.*
                FROM av_codigos_verificacion c
                INNER JOIN (
                    SELECT id_unidad, MAX(id_codigo) AS id_codigo
                    FROM av_codigos_verificacion
                    WHERE tipo_codigo = 'ingreso_almacen'
                    GROUP BY id_unidad
                ) ult ON ult.id_codigo = c.id_codigo
             ) cod ON cod.id_unidad = u.id_unidad
             {$whereSql}
             ORDER BY FIELD(u.estatus_inventario, :ord_inc, :ord_pend, :ord_rec),
                      u.fecha_actualizacion DESC,
                      u.id_unidad DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params + [
                'etapa_ev_rec' => self::ETAPA_EVIDENCIAS_INGRESO,
                'etapa_ev_val' => self::ETAPA_EVIDENCIAS_INGRESO,
                'ord_inc' => self::ESTATUS_INCIDENCIA_EVIDENCIAS,
                'ord_pend' => self::ESTATUS_PENDIENTE_EVIDENCIAS,
                'ord_rec' => self::ESTATUS_PENDIENTE_RECEPCION,
            ]
        ) ?: [];

        $celulas = $this->obtenerCelulas();
        $totalSlots = count(self::SLOTS_EVIDENCIAS_INGRESO);
        foreach ($rows as &$row) {
            $id = (int) ($row['id_celula'] ?? 0);
            $row['nombre_celula'] = $celulas[$id] ?? ('Celula ' . $id);
            $row['evidencias_requeridas'] = $totalSlots;
        }
        unset($row);

        return [
            'success' => true,
            'rows' => $rows,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function generarCodigoVerificacionUnidad(int $idUnidad, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        if ($idUnidad <= 0 || !$this->tablasBaseDisponibles()) {
            return ['success' => false, 'message' => 'Unidad invalida o tablas no disponibles.'];
        }

        $unidad = $this->obtenerUnidadPorId($idUnidad);
        if (!$unidad) {
            return ['success' => false, 'message' => 'No se encontro la unidad.'];
        }

        $estatus = (string) ($unidad['estatus_inventario'] ?? '');
        if (!in_array($estatus, [self::ESTATUS_PENDIENTE_EVIDENCIAS, self::ESTATUS_INCIDENCIA_EVIDENCIAS], true)) {
            return ['success' => false, 'message' => 'La unidad no esta en etapa de evidencias.'];
        }

        $activo = $this->codigoVerificacionActivo($idUnidad);
        if ($activo) {
            return [
                'success' => true,
                'message' => 'Codigo vigente recuperado.',
                'codigo' => $activo['codigo'],
                'fecha_expiracion' => $activo['fecha_expiracion'] ?? null,
            ];
        }

        $ahora = $this->fechaHoraCdmx();
        $expira = (new \DateTime($ahora, new \DateTimeZone('America/Mexico_City')))
            ->modify('+24 hours')
            ->format('Y-m-d H:i:s');
        $codigo = $this->generarCodigoVerificacionUnico();

        $this->db->CRUD(
            "INSERT INTO av_codigos_verificacion (
                id_unidad,
                tipo_codigo,
                codigo,
                estatus,
                generado_por,
                fecha_generacion,
                fecha_expiracion,
                observaciones
             ) VALUES (
                :id_unidad,
                'ingreso_almacen',
                :codigo,
                'generado',
                :generado_por,
                :fecha_generacion,
                :fecha_expiracion,
                :observaciones
             )",
            [
                'id_unidad' => $idUnidad,
                'codigo' => $codigo,
                'generado_por' => $idUsuario > 0 ? $idUsuario : null,
                'fecha_generacion' => $ahora,
                'fecha_expiracion' => $expira,
                'observaciones' => 'Codigo generado para validacion de evidencias de ingreso.',
            ]
        );

        $this->registrarBitacora(
            $idUnidad,
            'Evidencias y Codigo',
            'CODIGO DE VERIFICACION GENERADO',
            'Codigo generado para validar identidad de unidad antes de recepcion.',
            ['codigo' => $codigo, 'fecha_expiracion' => $expira],
            $idUsuario,
            $nombreUsuario,
            $ahora
        );

        return [
            'success' => true,
            'message' => 'Codigo generado.',
            'codigo' => $codigo,
            'fecha_expiracion' => $expira,
        ];
    }

    public function codigoVerificacionPendienteValido(int $idUnidad, string $codigo): array
    {
        $codigo = strtoupper(trim($codigo));
        if ($idUnidad <= 0 || $codigo === '') {
            return ['success' => false, 'message' => 'Codigo invalido.'];
        }

        $activo = $this->codigoVerificacionActivo($idUnidad);
        if (!$activo) {
            return ['success' => false, 'message' => 'Genera un codigo vigente antes de validar evidencias.'];
        }
        if (strtoupper((string) ($activo['codigo'] ?? '')) !== $codigo) {
            return ['success' => false, 'message' => 'El codigo de verificacion no coincide.'];
        }

        return ['success' => true, 'codigo' => $activo];
    }

    public function validarEvidenciasCodigoUnidad(
        int $idUnidad,
        array $datos,
        array $evidenciasSubidas,
        int $idUsuario = 0,
        string $nombreUsuario = ''
    ): array {
        if ($idUnidad <= 0) {
            return ['success' => false, 'message' => 'Unidad invalida.'];
        }
        if (!$this->tablasBaseDisponibles()) {
            return ['success' => false, 'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.'];
        }

        $unidad = $this->obtenerUnidadPorId($idUnidad);
        if (!$unidad) {
            return ['success' => false, 'message' => 'No se encontro la unidad en inventario.'];
        }

        $estatusActual = (string) ($unidad['estatus_inventario'] ?? '');
        if (!in_array($estatusActual, [self::ESTATUS_PENDIENTE_EVIDENCIAS, self::ESTATUS_INCIDENCIA_EVIDENCIAS], true)) {
            return ['success' => false, 'message' => 'La unidad ya no esta pendiente de evidencias.'];
        }

        $codigo = strtoupper(trim((string) ($datos['codigo_verificacion'] ?? '')));
        $codigoOk = $this->codigoVerificacionPendienteValido($idUnidad, $codigo);
        if (empty($codigoOk['success'])) {
            $this->incrementarIntentosCodigo($idUnidad);
            return $codigoOk;
        }

        $vin = $this->normalizarVin((string) ($datos['vin'] ?? ''));
        if ($vin === null || strlen($vin) !== 17) {
            return ['success' => false, 'message' => 'Captura un VIN/NIV valido de 17 caracteres.'];
        }

        $vinAnterior = $this->normalizarVin((string) ($unidad['vin'] ?? ''));
        $vinCoincide = $vinAnterior === null || $vinAnterior === $vin;
        $evidenciasActuales = $this->evidenciasActualesPorUnidad($idUnidad);
        $slotsPresentes = array_fill_keys(array_keys($evidenciasActuales), true);
        foreach ($evidenciasSubidas as $slot => $_info) {
            if (isset(self::SLOTS_EVIDENCIAS_INGRESO[$slot])) {
                $slotsPresentes[$slot] = true;
            }
        }

        $faltantes = [];
        foreach (self::SLOTS_EVIDENCIAS_INGRESO as $slot => $titulo) {
            if (empty($slotsPresentes[$slot])) {
                $faltantes[] = $titulo;
            }
        }

        $observaciones = $this->normalizarTexto((string) ($datos['observaciones'] ?? ''), 1000);
        $discrepancias = [];
        if (!$vinCoincide) {
            $discrepancias[] = 'VIN capturado diferente al VIN origen.';
        }
        foreach ($faltantes as $faltante) {
            $discrepancias[] = 'Falta evidencia: ' . $faltante;
        }

        $validacionOk = empty($discrepancias);
        $estatusNuevo = $validacionOk ? self::ESTATUS_PENDIENTE_RECEPCION : self::ESTATUS_INCIDENCIA_EVIDENCIAS;
        $ahora = $this->fechaHoraCdmx();

        try {
            $this->db->beginTransaction();
            $this->db->CRUD(
                "UPDATE av_unidades
                 SET vin = :vin,
                     estatus_inventario = :estatus_nuevo,
                     actualizado_por = :actualizado_por,
                     fecha_actualizacion = :fecha_actualizacion
                 WHERE id_unidad = :id_unidad
                   AND deleted_at IS NULL",
                [
                    'vin' => $vin,
                    'estatus_nuevo' => $estatusNuevo,
                    'actualizado_por' => $idUsuario > 0 ? $idUsuario : null,
                    'fecha_actualizacion' => $ahora,
                    'id_unidad' => $idUnidad,
                ]
            );

            $estatusEvidencia = $validacionOk ? 'validado' : 'recibido';
            foreach ($evidenciasSubidas as $slot => $info) {
                if (!isset(self::SLOTS_EVIDENCIAS_INGRESO[$slot])) {
                    continue;
                }
                $this->registrarEvidenciaUnidad($idUnidad, $slot, $info, $estatusEvidencia, $idUsuario, $nombreUsuario, $ahora);
            }
            if ($validacionOk) {
                $this->marcarEvidenciasIngresoValidadas($idUnidad, $ahora);
                $this->marcarCodigoVerificacionUsado(
                    (int) (($codigoOk['codigo']['id_codigo'] ?? 0)),
                    $idUsuario,
                    $ahora
                );
            }

            $comentario = $validacionOk
                ? 'Evidencias y codigo validados. Unidad lista para recepcion de almacen.'
                : 'Evidencias guardadas con incidencia para correccion.';
            $this->registrarMovimiento(
                $idUnidad,
                'validacion_evidencias',
                $estatusActual,
                $estatusNuevo,
                $this->intONull($unidad['id_ubicacion_actual'] ?? null),
                $this->intONull($unidad['id_ubicacion_actual'] ?? null),
                $comentario,
                $idUsuario,
                $nombreUsuario,
                $ahora
            );
            $this->registrarBitacora(
                $idUnidad,
                'Evidencias y Codigo',
                $validacionOk ? 'EVIDENCIAS VALIDADAS' : 'EVIDENCIAS CON INCIDENCIA',
                $comentario,
                [
                    'vin_capturado' => $vin,
                    'vin_origen' => $unidad['vin'] ?? null,
                    'codigo_verificacion' => $codigo,
                    'evidencias_subidas' => array_keys($evidenciasSubidas),
                    'faltantes' => $faltantes,
                    'discrepancias' => $discrepancias,
                    'observaciones' => $observaciones,
                ],
                $idUsuario,
                $nombreUsuario,
                $ahora
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'No se pudo validar evidencias y codigo.',
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => $validacionOk
                ? 'Evidencias validadas. La unidad paso a Recepcion de Almacen.'
                : 'Evidencias guardadas con incidencia. Corrige faltantes o discrepancias.',
            'resultado' => $validacionOk ? 'validada' : 'incidencia',
            'estatus_nuevo' => $estatusNuevo,
            'discrepancias' => $discrepancias,
            'unidad' => $this->obtenerUnidadPorId($idUnidad),
        ];
    }

    public function obtenerFichaUnidad(int $idUnidad): array
    {
        if ($idUnidad <= 0 || !$this->tablasBaseDisponibles()) {
            return ['success' => false, 'message' => 'Unidad invalida o tablas no disponibles.'];
        }

        $unidad = $this->obtenerUnidadPorId($idUnidad);
        if (!$unidad) {
            return ['success' => false, 'message' => 'No se encontro la unidad.'];
        }

        $evidencias = $this->evidenciasActualesPorUnidad($idUnidad);
        $movimientos = $this->db->queryAll(
            "SELECT
                m.id_movimiento,
                m.tipo_movimiento,
                m.estatus_anterior,
                m.estatus_nuevo,
                m.comentario,
                m.nombre_usuario,
                uo.nombre_ubicacion AS ubicacion_origen,
                ud.nombre_ubicacion AS ubicacion_destino,
                DATE_FORMAT(m.fecha_movimiento, '%d/%m/%Y %H:%i') AS fecha_movimiento_fmt
             FROM av_movimientos m
             LEFT JOIN av_ubicaciones uo ON uo.id_ubicacion = m.id_ubicacion_origen
             LEFT JOIN av_ubicaciones ud ON ud.id_ubicacion = m.id_ubicacion_destino
             WHERE m.id_unidad = :id
             ORDER BY m.fecha_movimiento DESC, m.id_movimiento DESC
             LIMIT 20",
            ['id' => $idUnidad]
        ) ?: [];
        $codigos = $this->db->queryAll(
            "SELECT
                id_codigo,
                tipo_codigo,
                codigo,
                estatus,
                intentos,
                DATE_FORMAT(fecha_generacion, '%d/%m/%Y %H:%i') AS fecha_generacion_fmt,
                DATE_FORMAT(fecha_expiracion, '%d/%m/%Y %H:%i') AS fecha_expiracion_fmt,
                DATE_FORMAT(fecha_uso, '%d/%m/%Y %H:%i') AS fecha_uso_fmt
             FROM av_codigos_verificacion
             WHERE id_unidad = :id
             ORDER BY id_codigo DESC
             LIMIT 10",
            ['id' => $idUnidad]
        ) ?: [];
        $bitacora = $this->db->queryAll(
            "SELECT
                id_bitacora,
                modulo,
                accion,
                detalle,
                payload_json,
                nombre_usuario,
                DATE_FORMAT(fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt
             FROM av_bitacora
             WHERE id_unidad = :id
             ORDER BY fecha_alta DESC, id_bitacora DESC
             LIMIT 100",
            ['id' => $idUnidad]
        ) ?: [];
        $transicionesKanban = [];
        if ($this->tablaExiste('av_kanban_transiciones')) {
            $transicionesKanban = $this->db->queryAll(
                "SELECT
                    id_transicion,
                    estatus_anterior,
                    estatus_nuevo,
                    origen_evento,
                    evento_negocio,
                    justificacion,
                    payload_json,
                    nombre_usuario,
                    DATE_FORMAT(fecha_hora, '%d/%m/%Y %H:%i') AS fecha_hora_fmt
                 FROM av_kanban_transiciones
                 WHERE id_unidad = :id
                 ORDER BY fecha_hora DESC, id_transicion DESC
                 LIMIT 100",
                ['id' => $idUnidad]
            ) ?: [];
        }

        return [
            'success' => true,
            'unidad' => $unidad,
            'evidencias' => array_values($evidencias),
            'movimientos' => $movimientos,
            'codigos' => $codigos,
            'bitacora' => $bitacora,
            'kanban_transiciones' => $transicionesKanban,
        ];
    }

    public function obtenerResumenRecepcion(): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'tablas_disponibles' => false,
                'pendientes' => 0,
                'en_recepcion' => 0,
                'incidencias' => 0,
                'recibidas' => 0,
                'total_abiertas' => 0,
            ];
        }

        $rows = $this->db->queryAll(
            "SELECT estatus_inventario, COUNT(*) AS total
             FROM av_unidades
             WHERE deleted_at IS NULL
               AND estatus_inventario IN ('pendiente_recepcion', 'en_recepcion', 'incidencia_recepcion', 'pendiente_revision')
             GROUP BY estatus_inventario"
        ) ?: [];

        $resumen = [
            'tablas_disponibles' => true,
            'pendientes' => 0,
            'en_recepcion' => 0,
            'incidencias' => 0,
            'recibidas' => 0,
            'total_abiertas' => 0,
        ];
        foreach ($rows as $row) {
            $estatus = (string) ($row['estatus_inventario'] ?? '');
            $total = (int) ($row['total'] ?? 0);
            if ($estatus === 'pendiente_recepcion') {
                $resumen['pendientes'] = $total;
            } elseif ($estatus === 'en_recepcion') {
                $resumen['en_recepcion'] = $total;
            } elseif ($estatus === 'incidencia_recepcion') {
                $resumen['incidencias'] = $total;
            } elseif ($estatus === 'pendiente_revision') {
                $resumen['recibidas'] = $total;
            }
        }
        $resumen['total_abiertas'] = $resumen['pendientes'] + $resumen['en_recepcion'] + $resumen['incidencias'];

        return $resumen;
    }

    public function listarRecepcionUnidades(array $filtros = []): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
                'pages' => 1,
            ];
        }

        $page = max(1, (int) ($filtros['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));
        $trackingDisponible = $this->trackingRecoleccionDisponible();

        $where = ['u.deleted_at IS NULL'];
        $params = [];

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $busqueda = [
                'u.folio_unidad LIKE :q',
                'u.vin LIKE :q',
                'u.no_motor LIKE :q',
                'u.placas LIKE :q',
                'u.marca LIKE :q',
                'u.modelo LIKE :q',
                'CAST(u.id_unidad AS CHAR) LIKE :q',
                'CAST(u.id_origen AS CHAR) LIKE :q',
                'CAST(u.id_credito AS CHAR) LIKE :q',
            ];
            if ($trackingDisponible) {
                $busqueda[] = 'trk_ruta.nombre_ruta LIKE :q';
                $busqueda[] = 'cedis_dest.nombre_agencia LIKE :q';
            }
            $where[] = '(' . implode(' OR ', $busqueda) . ')';
            $params['q'] = '%' . $q . '%';
        }

        $idCelula = (int) ($filtros['id_celula'] ?? 0);
        if ($idCelula > 0) {
            $where[] = 'u.id_celula = :id_celula';
            $params['id_celula'] = $idCelula;
        }

        $estatus = trim((string) ($filtros['estatus'] ?? 'abiertas'));
        if ($estatus === '' || $estatus === 'abiertas') {
            $where[] = "u.estatus_inventario IN ('pendiente_recepcion', 'en_recepcion', 'incidencia_recepcion')";
        } else {
            $where[] = 'u.estatus_inventario = :estatus';
            $params['estatus'] = $estatus;
        }

        $idUbicacion = (int) ($filtros['id_ubicacion'] ?? 0);
        if ($idUbicacion > 0) {
            $where[] = 'u.id_ubicacion_actual = :id_ubicacion';
            $params['id_ubicacion'] = $idUbicacion;
        }

        $trackingSelect = <<<SQL
            NULL AS tracking_id_detalle,
            NULL AS tracking_estatus_recoleccion,
            NULL AS tracking_id_ruta,
            NULL AS tracking_nombre_ruta,
            NULL AS tracking_estatus_ruta,
            NULL AS tracking_cedis_destino_nombre,
            NULL AS tracking_fecha_finalizacion_fmt,
        SQL;
        $trackingJoin = '';
        if ($trackingDisponible) {
            $estatusRecolectada = $this->trackingRecolectadaSqlIn();
            $rutasNoInventario = $this->trackingRutasNoInventarioSqlIn();
            $trackingSelect = <<<SQL
            trk_det.id_detalle AS tracking_id_detalle,
            trk_det.estatus_recoleccion AS tracking_estatus_recoleccion,
            trk_ruta.id_ruta AS tracking_id_ruta,
            trk_ruta.nombre_ruta AS tracking_nombre_ruta,
            trk_ruta.estatus_ruta AS tracking_estatus_ruta,
            cedis_dest.nombre_agencia AS tracking_cedis_destino_nombre,
            DATE_FORMAT(trk_ruta.fecha_finalizacion, '%d/%m/%Y') AS tracking_fecha_finalizacion_fmt,
            SQL;
            $trackingJoin = <<<SQL
        LEFT JOIN (
            SELECT atd_idx.id_credito, MAX(atd_idx.id_detalle) AS id_detalle
            FROM asigna_horas_tracking_detalle atd_idx
            INNER JOIN asigna_horas_tracking atr_idx ON atr_idx.id_ruta = atd_idx.id_ruta
            WHERE atd_idx.id_credito IS NOT NULL
              AND LOWER(TRIM(COALESCE(atd_idx.estatus_recoleccion, ''))) IN ({$estatusRecolectada})
              AND LOWER(TRIM(COALESCE(atr_idx.estatus_ruta, ''))) NOT IN ({$rutasNoInventario})
            GROUP BY atd_idx.id_credito
        ) trk_idx ON trk_idx.id_credito = u.id_credito
        LEFT JOIN asigna_horas_tracking_detalle trk_det ON trk_det.id_detalle = trk_idx.id_detalle
        LEFT JOIN asigna_horas_tracking trk_ruta ON trk_ruta.id_ruta = trk_det.id_ruta
        LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = trk_ruta.id_cedis_destino
        SQL;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalRow = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_unidades u
             LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
             {$trackingJoin}
             {$whereSql}",
            $params
        ) ?: [];
        $total = (int) ($totalRow['total'] ?? 0);
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);
        $offset = ($page - 1) * $limit;

        $rows = $this->db->queryAll(
            "SELECT
                u.id_unidad,
                u.folio_unidad,
                u.id_celula,
                u.id_origen,
                u.id_credito,
                u.vin,
                u.no_motor,
                u.placas,
                u.marca,
                u.modelo,
                u.anio,
                u.color,
                u.kilometraje,
                u.estatus_inventario,
                u.id_ubicacion_actual,
                ub.nombre_ubicacion,
                ub.tipo_ubicacion,
                {$trackingSelect}
                DATE_FORMAT(u.fecha_ingreso_virtual, '%d/%m/%Y %H:%i') AS fecha_ingreso_virtual_fmt,
                DATE_FORMAT(u.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
             FROM av_unidades u
             LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
             {$trackingJoin}
             {$whereSql}
             ORDER BY FIELD(u.estatus_inventario, 'incidencia_recepcion', 'en_recepcion', 'pendiente_recepcion', 'pendiente_revision'),
                      u.fecha_actualizacion DESC,
                      u.id_unidad DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        ) ?: [];

        $celulas = $this->obtenerCelulas();
        foreach ($rows as &$row) {
            $id = (int) ($row['id_celula'] ?? 0);
            $row['nombre_celula'] = $celulas[$id] ?? ('Celula ' . $id);
        }
        unset($row);

        return [
            'success' => true,
            'rows' => $rows,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function confirmarRecepcionAlmacen(int $idUnidad, array $datos, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        if ($idUnidad <= 0) {
            return ['success' => false, 'message' => 'Unidad invalida.'];
        }
        if (!$this->tablasBaseDisponibles()) {
            return ['success' => false, 'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.'];
        }

        $unidad = $this->obtenerUnidadPorId($idUnidad);
        if (!$unidad) {
            return ['success' => false, 'message' => 'No se encontro la unidad en inventario.'];
        }

        $estatusActual = (string) ($unidad['estatus_inventario'] ?? '');
        $estatusPermitidos = ['pendiente_recepcion', 'en_recepcion', 'incidencia_recepcion'];
        if (!in_array($estatusActual, $estatusPermitidos, true)) {
            return ['success' => false, 'message' => 'La unidad ya no esta pendiente de recepcion.'];
        }
        if (!$this->evidenciasIngresoValidas($idUnidad)) {
            return [
                'success' => false,
                'message' => 'Primero valida Evidencias y Codigo antes de confirmar la recepcion de almacen.',
            ];
        }

        $idUbicacion = (int) ($datos['id_ubicacion'] ?? 0);
        $ubicacion = $this->obtenerUbicacionPorId($idUbicacion);
        if (!$ubicacion) {
            return ['success' => false, 'message' => 'Selecciona una ubicacion valida para recepcion.'];
        }
        if (strtoupper((string) ($ubicacion['clave_ubicacion'] ?? '')) === 'SIN_ASIGNAR') {
            return ['success' => false, 'message' => 'Selecciona una ubicacion fisica distinta a Sin asignar.'];
        }

        $vin = $this->normalizarVin((string) ($datos['vin'] ?? ''));
        if ($vin === null || strlen($vin) !== 17) {
            return ['success' => false, 'message' => 'Captura un VIN/NIV valido de 17 caracteres.'];
        }

        $noMotor = $this->normalizarAlfanumerico((string) ($datos['no_motor'] ?? ''), 24);
        if ($noMotor === null) {
            $noMotor = $unidad['no_motor'] ?: null;
        }
        $placas = $this->normalizarAlfanumerico((string) ($datos['placas'] ?? ''), 20);
        if ($placas === null) {
            $placas = $unidad['placas'] ?: null;
        }
        $kilometraje = $this->intONull($datos['kilometraje'] ?? null);
        if ($kilometraje === null && isset($unidad['kilometraje'])) {
            $kilometraje = $this->intONull($unidad['kilometraje']);
        }

        $vinAnterior = $this->normalizarVin((string) ($unidad['vin'] ?? ''));
        $vinCoincideFisico = $vinAnterior === null || $vinAnterior === $vin;
        $checklist = [
            'vin_coincide' => $this->boolDesdePayload($datos['vin_coincide'] ?? null) && $vinCoincideFisico,
            'evidencia_4_angulos' => $this->boolDesdePayload($datos['evidencia_4_angulos'] ?? null),
            'evidencia_vin' => $this->boolDesdePayload($datos['evidencia_vin'] ?? null),
            'documentos_completos' => $this->boolDesdePayload($datos['documentos_completos'] ?? null),
            'arranque_motor' => $this->boolDesdePayload($datos['arranque_motor'] ?? null),
            'sin_danos_mayores' => $this->boolDesdePayload($datos['sin_danos_mayores'] ?? null),
        ];

        $discrepancias = [];
        if (!$vinCoincideFisico) {
            $discrepancias[] = 'VIN capturado diferente al VIN origen.';
        }
        foreach ($checklist as $key => $ok) {
            if (!$ok) {
                $discrepancias[] = $key;
            }
        }

        $recepcionOk = empty($discrepancias);
        $estatusNuevo = $recepcionOk ? 'pendiente_revision' : 'incidencia_recepcion';
        $resultado = $recepcionOk ? 'recibida' : 'incidencia';
        $observaciones = $this->normalizarTexto((string) ($datos['observaciones'] ?? ''), 1000);
        $ahora = $this->fechaHoraCdmx();

        try {
            $this->db->beginTransaction();
            $this->db->CRUD(
                "UPDATE av_unidades
                 SET vin = :vin,
                     no_motor = :no_motor,
                     placas = :placas,
                     kilometraje = :kilometraje,
                     id_ubicacion_actual = :id_ubicacion,
                     estatus_inventario = :estatus_nuevo,
                     actualizado_por = :actualizado_por,
                     fecha_actualizacion = :fecha_actualizacion
                 WHERE id_unidad = :id_unidad
                   AND deleted_at IS NULL",
                [
                    'vin' => $vin,
                    'no_motor' => $noMotor,
                    'placas' => $placas,
                    'kilometraje' => $kilometraje,
                    'id_ubicacion' => $idUbicacion,
                    'estatus_nuevo' => $estatusNuevo,
                    'actualizado_por' => $idUsuario > 0 ? $idUsuario : null,
                    'fecha_actualizacion' => $ahora,
                    'id_unidad' => $idUnidad,
                ]
            );

            $comentario = $recepcionOk
                ? 'Recepcion de almacen confirmada. Unidad enviada a revision mecanica.'
                : 'Recepcion registrada con incidencia para validacion.';
            $idMovimiento = $this->registrarMovimiento(
                $idUnidad,
                'recepcion_almacen',
                $estatusActual,
                $estatusNuevo,
                $this->intONull($unidad['id_ubicacion_actual'] ?? null),
                $idUbicacion,
                $comentario,
                $idUsuario,
                $nombreUsuario,
                $ahora
            );

            $payload = [
                'resultado' => $resultado,
                'datos_previos' => [
                    'vin' => $unidad['vin'] ?? null,
                    'no_motor' => $unidad['no_motor'] ?? null,
                    'placas' => $unidad['placas'] ?? null,
                    'kilometraje' => $unidad['kilometraje'] ?? null,
                    'id_ubicacion' => $unidad['id_ubicacion_actual'] ?? null,
                ],
                'datos_capturados' => [
                    'vin' => $vin,
                    'no_motor' => $noMotor,
                    'placas' => $placas,
                    'kilometraje' => $kilometraje,
                    'id_ubicacion' => $idUbicacion,
                    'ubicacion' => $ubicacion['nombre_ubicacion'] ?? null,
                ],
                'checklist' => $checklist,
                'discrepancias' => $discrepancias,
                'observaciones' => $observaciones,
            ];

            $this->registrarBitacora(
                $idUnidad,
                'Recepcion Almacen Virtual',
                $recepcionOk ? 'RECEPCION CONFIRMADA' : 'RECEPCION CON INCIDENCIA',
                $comentario,
                $payload,
                $idUsuario,
                $nombreUsuario,
                $ahora
            );

            $this->registrarRecepcionEstructurada(
                $idUnidad,
                $idMovimiento,
                $idUbicacion,
                $vin,
                $noMotor,
                $placas,
                $kilometraje,
                $checklist,
                $resultado,
                $observaciones,
                $idUsuario,
                $nombreUsuario,
                $ahora
            );
            $this->sincronizarRecepcionOperacionOrigen($unidad, $ubicacion, $observaciones ?? '', $resultado, $ahora);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'No se pudo confirmar la recepcion de almacen.',
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => $recepcionOk
                ? 'Recepcion confirmada. La unidad paso a revision mecanica.'
                : 'Recepcion guardada con incidencia. Revisa las discrepancias antes de continuar.',
            'resultado' => $resultado,
            'estatus_nuevo' => $estatusNuevo,
            'unidad' => $this->obtenerUnidadPorId($idUnidad),
        ];
    }

    public function obtenerResumenRevisionMecanica(): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'tablas_disponibles' => false,
                'tablas_revision_disponibles' => false,
                'pendientes' => 0,
                'en_revision' => 0,
                'reparadas' => 0,
                'fuera_presupuesto' => 0,
                'irreparables' => 0,
                'total_abiertas' => 0,
            ];
        }

        $rows = $this->db->queryAll(
            "SELECT estatus_inventario, COUNT(*) AS total
             FROM av_unidades
             WHERE deleted_at IS NULL
               AND estatus_inventario IN (:pendiente, :en_revision, :reparada, :fuera_presupuesto, :irreparable)
             GROUP BY estatus_inventario",
            [
                'pendiente' => self::ESTATUS_PENDIENTE_REVISION,
                'en_revision' => self::ESTATUS_EN_REVISION,
                'reparada' => self::ESTATUS_REPARADA,
                'fuera_presupuesto' => self::ESTATUS_FUERA_PRESUPUESTO,
                'irreparable' => self::ESTATUS_IRREPARABLE,
            ]
        ) ?: [];

        $resumen = [
            'tablas_disponibles' => true,
            'tablas_revision_disponibles' => $this->tablasRevisionMecanicaDisponibles(),
            'pendientes' => 0,
            'en_revision' => 0,
            'reparadas' => 0,
            'fuera_presupuesto' => 0,
            'irreparables' => 0,
            'total_abiertas' => 0,
        ];
        foreach ($rows as $row) {
            $estatus = (string) ($row['estatus_inventario'] ?? '');
            $total = (int) ($row['total'] ?? 0);
            if ($estatus === self::ESTATUS_PENDIENTE_REVISION) {
                $resumen['pendientes'] = $total;
            } elseif ($estatus === self::ESTATUS_EN_REVISION) {
                $resumen['en_revision'] = $total;
            } elseif ($estatus === self::ESTATUS_REPARADA) {
                $resumen['reparadas'] = $total;
            } elseif ($estatus === self::ESTATUS_FUERA_PRESUPUESTO) {
                $resumen['fuera_presupuesto'] = $total;
            } elseif ($estatus === self::ESTATUS_IRREPARABLE) {
                $resumen['irreparables'] = $total;
            }
        }
        $resumen['total_abiertas'] = $resumen['pendientes'] + $resumen['en_revision'];

        return $resumen;
    }

    public function listarRevisionMecanicaUnidades(array $filtros = []): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
                'pages' => 1,
            ];
        }
        if (!$this->tablasRevisionMecanicaDisponibles()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas de Revision Mecanica. Ejecuta scripts/migration_almacen_virtual_revision_mecanica.sql.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
                'pages' => 1,
            ];
        }

        $page = max(1, (int) ($filtros['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));
        $trackingDisponible = $this->trackingRecoleccionDisponible();
        $recepcionDisponible = $this->tablaExiste('av_recepciones');

        $where = ['u.deleted_at IS NULL'];
        $params = [];

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $busqueda = [
                'u.folio_unidad LIKE :q',
                'u.vin LIKE :q',
                'u.no_motor LIKE :q',
                'u.placas LIKE :q',
                'u.marca LIKE :q',
                'u.modelo LIKE :q',
                'CAST(u.id_unidad AS CHAR) LIKE :q',
                'CAST(u.id_origen AS CHAR) LIKE :q',
                'CAST(u.id_credito AS CHAR) LIKE :q',
            ];
            if ($trackingDisponible) {
                $busqueda[] = 'trk_ruta.nombre_ruta LIKE :q';
                $busqueda[] = 'cedis_dest.nombre_agencia LIKE :q';
            }
            $where[] = '(' . implode(' OR ', $busqueda) . ')';
            $params['q'] = '%' . $q . '%';
        }

        $idCelula = (int) ($filtros['id_celula'] ?? 0);
        if ($idCelula > 0) {
            $where[] = 'u.id_celula = :id_celula';
            $params['id_celula'] = $idCelula;
        }

        $estatus = trim((string) ($filtros['estatus'] ?? 'abiertas'));
        if ($estatus === '' || $estatus === 'abiertas') {
            $where[] = 'u.estatus_inventario IN (:estatus_pendiente_revision, :estatus_en_revision)';
            $params['estatus_pendiente_revision'] = self::ESTATUS_PENDIENTE_REVISION;
            $params['estatus_en_revision'] = self::ESTATUS_EN_REVISION;
        } else {
            $where[] = 'u.estatus_inventario = :estatus';
            $params['estatus'] = $estatus;
        }

        $idUbicacion = (int) ($filtros['id_ubicacion'] ?? 0);
        if ($idUbicacion > 0) {
            $where[] = 'u.id_ubicacion_actual = :id_ubicacion';
            $params['id_ubicacion'] = $idUbicacion;
        }

        $trackingSelect = <<<SQL
            NULL AS tracking_id_detalle,
            NULL AS tracking_estatus_recoleccion,
            NULL AS tracking_id_ruta,
            NULL AS tracking_nombre_ruta,
            NULL AS tracking_estatus_ruta,
            NULL AS tracking_cedis_destino_nombre,
            NULL AS tracking_fecha_finalizacion_fmt,
        SQL;
        $trackingJoin = '';
        if ($trackingDisponible) {
            $estatusRecolectada = $this->trackingRecolectadaSqlIn();
            $rutasNoInventario = $this->trackingRutasNoInventarioSqlIn();
            $trackingSelect = <<<SQL
            trk_det.id_detalle AS tracking_id_detalle,
            trk_det.estatus_recoleccion AS tracking_estatus_recoleccion,
            trk_ruta.id_ruta AS tracking_id_ruta,
            trk_ruta.nombre_ruta AS tracking_nombre_ruta,
            trk_ruta.estatus_ruta AS tracking_estatus_ruta,
            cedis_dest.nombre_agencia AS tracking_cedis_destino_nombre,
            DATE_FORMAT(trk_ruta.fecha_finalizacion, '%d/%m/%Y') AS tracking_fecha_finalizacion_fmt,
            SQL;
            $trackingJoin = <<<SQL
        LEFT JOIN (
            SELECT atd_idx.id_credito, MAX(atd_idx.id_detalle) AS id_detalle
            FROM asigna_horas_tracking_detalle atd_idx
            INNER JOIN asigna_horas_tracking atr_idx ON atr_idx.id_ruta = atd_idx.id_ruta
            WHERE atd_idx.id_credito IS NOT NULL
              AND LOWER(TRIM(COALESCE(atd_idx.estatus_recoleccion, ''))) IN ({$estatusRecolectada})
              AND LOWER(TRIM(COALESCE(atr_idx.estatus_ruta, ''))) NOT IN ({$rutasNoInventario})
            GROUP BY atd_idx.id_credito
        ) trk_idx ON trk_idx.id_credito = u.id_credito
        LEFT JOIN asigna_horas_tracking_detalle trk_det ON trk_det.id_detalle = trk_idx.id_detalle
        LEFT JOIN asigna_horas_tracking trk_ruta ON trk_ruta.id_ruta = trk_det.id_ruta
        LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = trk_ruta.id_cedis_destino
        SQL;
        }

        $recepcionSelect = <<<SQL
            NULL AS recepcion_resultado,
            NULL AS recepcion_vin_coincide,
            NULL AS recepcion_evidencia_4_angulos,
            NULL AS recepcion_evidencia_vin,
            NULL AS recepcion_documentos_completos,
            NULL AS recepcion_arranque_motor,
            NULL AS recepcion_sin_danos_mayores,
            NULL AS recepcion_observaciones,
            NULL AS recepcion_fecha_fmt,
        SQL;
        $recepcionJoin = '';
        if ($recepcionDisponible) {
            $recepcionSelect = <<<SQL
            rec.resultado AS recepcion_resultado,
            rec.vin_coincide AS recepcion_vin_coincide,
            rec.evidencia_4_angulos AS recepcion_evidencia_4_angulos,
            rec.evidencia_vin AS recepcion_evidencia_vin,
            rec.documentos_completos AS recepcion_documentos_completos,
            rec.arranque_motor AS recepcion_arranque_motor,
            rec.sin_danos_mayores AS recepcion_sin_danos_mayores,
            rec.observaciones AS recepcion_observaciones,
            DATE_FORMAT(rec.fecha_recepcion, '%d/%m/%Y %H:%i') AS recepcion_fecha_fmt,
            SQL;
            $recepcionJoin = <<<SQL
        LEFT JOIN (
            SELECT r.*
            FROM av_recepciones r
            INNER JOIN (
                SELECT id_unidad, MAX(id_recepcion) AS id_recepcion
                FROM av_recepciones
                GROUP BY id_unidad
            ) ult_rec ON ult_rec.id_recepcion = r.id_recepcion
        ) rec ON rec.id_unidad = u.id_unidad
        SQL;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalRow = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_unidades u
             LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
             {$trackingJoin}
             {$recepcionJoin}
             {$whereSql}",
            $params
        ) ?: [];
        $total = (int) ($totalRow['total'] ?? 0);
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);
        $offset = ($page - 1) * $limit;

        $rows = $this->db->queryAll(
            "SELECT
                u.id_unidad,
                u.folio_unidad,
                u.id_celula,
                u.id_origen,
                u.id_credito,
                u.vin,
                u.no_motor,
                u.placas,
                u.marca,
                u.modelo,
                u.anio,
                u.color,
                u.kilometraje,
                u.estatus_inventario,
                u.id_ubicacion_actual,
                ub.nombre_ubicacion,
                ub.tipo_ubicacion,
                {$trackingSelect}
                {$recepcionSelect}
                rev.id_revision,
                rev.estatus_revision,
                rev.dictamen,
                rev.diagnostico_general,
                rev.comentario_mecanica,
                rev.comentario_electrica,
                rev.comentario_estetica,
                DATE_FORMAT(rev.fecha_inicio, '%d/%m/%Y %H:%i') AS revision_fecha_inicio_fmt,
                DATE_FORMAT(rev.fecha_cierre, '%d/%m/%Y %H:%i') AS revision_fecha_cierre_fmt,
                DATE_FORMAT(u.fecha_ingreso_virtual, '%d/%m/%Y %H:%i') AS fecha_ingreso_virtual_fmt,
                DATE_FORMAT(u.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
             FROM av_unidades u
             LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
             {$trackingJoin}
             {$recepcionJoin}
             LEFT JOIN (
                SELECT r.*
                FROM av_revisiones_mecanicas r
                INNER JOIN (
                    SELECT id_unidad, MAX(id_revision) AS id_revision
                    FROM av_revisiones_mecanicas
                    GROUP BY id_unidad
                ) ult_rev ON ult_rev.id_revision = r.id_revision
             ) rev ON rev.id_unidad = u.id_unidad
             {$whereSql}
             ORDER BY FIELD(u.estatus_inventario, :ord_pend, :ord_en, :ord_rep, :ord_fuera, :ord_irrep),
                      u.fecha_actualizacion DESC,
                      u.id_unidad DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params + [
                'ord_pend' => self::ESTATUS_PENDIENTE_REVISION,
                'ord_en' => self::ESTATUS_EN_REVISION,
                'ord_rep' => self::ESTATUS_REPARADA,
                'ord_fuera' => self::ESTATUS_FUERA_PRESUPUESTO,
                'ord_irrep' => self::ESTATUS_IRREPARABLE,
            ]
        ) ?: [];

        $celulas = $this->obtenerCelulas();
        foreach ($rows as &$row) {
            $id = (int) ($row['id_celula'] ?? 0);
            $row['nombre_celula'] = $celulas[$id] ?? ('Celula ' . $id);
        }
        unset($row);

        return [
            'success' => true,
            'rows' => $rows,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function iniciarRevisionMecanica(int $idUnidad, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        if ($idUnidad <= 0) {
            return ['success' => false, 'message' => 'Unidad invalida.'];
        }
        if (!$this->tablasBaseDisponibles() || !$this->tablasRevisionMecanicaDisponibles()) {
            return ['success' => false, 'message' => 'Faltan tablas de Revision Mecanica. Ejecuta la migracion correspondiente.'];
        }

        $unidad = $this->obtenerUnidadPorId($idUnidad);
        if (!$unidad) {
            return ['success' => false, 'message' => 'No se encontro la unidad en inventario.'];
        }

        $estatusActual = (string) ($unidad['estatus_inventario'] ?? '');
        if (!in_array($estatusActual, [self::ESTATUS_PENDIENTE_REVISION, self::ESTATUS_EN_REVISION], true)) {
            return ['success' => false, 'message' => 'La unidad no esta disponible para revision mecanica.'];
        }

        $ahora = $this->fechaHoraCdmx();
        try {
            $this->db->beginTransaction();

            $revision = $this->obtenerRevisionActiva($idUnidad);
            $idMovimientoInicio = null;
            if ($estatusActual === self::ESTATUS_PENDIENTE_REVISION) {
                $cambio = $this->aplicarCambioEstatusUnidad(
                    $unidad,
                    self::ESTATUS_EN_REVISION,
                    self::EVENTO_ASIGNACION_MECANICO,
                    $idUsuario,
                    null,
                    ['modulo' => 'revision_mecanica'],
                    $nombreUsuario,
                    false,
                    $ahora
                );
                if (empty($cambio['success'])) {
                    $this->db->rollback();
                    return $cambio;
                }
                $idMovimientoInicio = (int) ($cambio['id_movimiento'] ?? 0);
                $unidad['estatus_inventario'] = self::ESTATUS_EN_REVISION;
            }

            $registroNuevo = false;
            if (!$revision) {
                $this->db->CRUD(
                    "INSERT INTO av_revisiones_mecanicas (
                        id_unidad,
                        id_movimiento_inicio,
                        estatus_revision,
                        id_usuario_inicio,
                        nombre_usuario_inicio,
                        fecha_inicio
                     ) VALUES (
                        :id_unidad,
                        :id_movimiento_inicio,
                        'en_revision',
                        :id_usuario_inicio,
                        :nombre_usuario_inicio,
                        :fecha_inicio
                     )",
                    [
                        'id_unidad' => $idUnidad,
                        'id_movimiento_inicio' => $idMovimientoInicio,
                        'id_usuario_inicio' => $idUsuario > 0 ? $idUsuario : null,
                        'nombre_usuario_inicio' => $nombreUsuario !== '' ? $nombreUsuario : null,
                        'fecha_inicio' => $ahora,
                    ]
                );
                $revision = ['id_revision' => $this->db->lastInsertId()];
                $registroNuevo = true;
            } elseif ($idMovimientoInicio) {
                $this->db->CRUD(
                    "UPDATE av_revisiones_mecanicas
                     SET id_movimiento_inicio = COALESCE(id_movimiento_inicio, :id_movimiento_inicio)
                     WHERE id_revision = :id_revision",
                    [
                        'id_movimiento_inicio' => $idMovimientoInicio,
                        'id_revision' => (int) ($revision['id_revision'] ?? 0),
                    ]
                );
            }

            if ($idMovimientoInicio || $registroNuevo) {
                $this->registrarBitacora(
                    $idUnidad,
                    'Revision Mecanica',
                    'REVISION INICIADA',
                    'Unidad tomada para diagnostico mecanico.',
                    ['id_revision' => (int) ($revision['id_revision'] ?? 0)],
                    $idUsuario,
                    $nombreUsuario,
                    $ahora
                );
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'No se pudo iniciar la revision mecanica.',
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Revision mecanica iniciada.',
            'id_revision' => (int) ($revision['id_revision'] ?? 0),
            'unidad' => $this->obtenerUnidadPorId($idUnidad),
        ];
    }

    public function finalizarRevisionMecanica(
        int $idUnidad,
        array $datos,
        array $evidencias = [],
        int $idUsuario = 0,
        string $nombreUsuario = ''
    ): array {
        if ($idUnidad <= 0) {
            return ['success' => false, 'message' => 'Unidad invalida.'];
        }
        if (!$this->tablasBaseDisponibles() || !$this->tablasRevisionMecanicaDisponibles()) {
            return ['success' => false, 'message' => 'Faltan tablas de Revision Mecanica. Ejecuta la migracion correspondiente.'];
        }

        $unidad = $this->obtenerUnidadPorId($idUnidad);
        if (!$unidad) {
            return ['success' => false, 'message' => 'No se encontro la unidad en inventario.'];
        }

        $estatusActual = (string) ($unidad['estatus_inventario'] ?? '');
        if (!in_array($estatusActual, [self::ESTATUS_PENDIENTE_REVISION, self::ESTATUS_EN_REVISION], true)) {
            return ['success' => false, 'message' => 'La unidad no esta abierta para revision mecanica.'];
        }

        $dictamen = $this->normalizarDictamenRevision((string) ($datos['dictamen'] ?? ''));
        if ($dictamen === null) {
            return ['success' => false, 'message' => 'Selecciona un dictamen final valido.'];
        }

        $diagnostico = $this->normalizarTexto((string) ($datos['diagnostico_general'] ?? ''), 2000);
        if ($diagnostico === null) {
            return ['success' => false, 'message' => 'Captura el diagnostico general de la unidad.'];
        }

        $comentarioMecanica = $this->normalizarTexto((string) ($datos['comentario_mecanica'] ?? ''), 1500);
        $comentarioElectrica = $this->normalizarTexto((string) ($datos['comentario_electrica'] ?? ''), 1500);
        $comentarioEstetica = $this->normalizarTexto((string) ($datos['comentario_estetica'] ?? ''), 1500);
        $otrosMecanica = $this->normalizarTexto((string) ($datos['otros_mecanica'] ?? ''), 1000);
        $otrosElectrica = $this->normalizarTexto((string) ($datos['otros_electrica'] ?? ''), 1000);
        $otrosEstetica = $this->normalizarTexto((string) ($datos['otros_estetica'] ?? ''), 1000);
        $itemsRevision = $this->normalizarItemsRevision($datos);
        $ahora = $this->fechaHoraCdmx();

        try {
            $this->db->beginTransaction();

            $idUbicacionActual = $this->intONull($unidad['id_ubicacion_actual'] ?? null);
            $revision = $this->obtenerRevisionActiva($idUnidad);
            $idMovimientoInicio = null;
            $estatusCierreAnterior = $estatusActual;

            if ($estatusActual === self::ESTATUS_PENDIENTE_REVISION) {
                $cambioInicio = $this->aplicarCambioEstatusUnidad(
                    $unidad,
                    self::ESTATUS_EN_REVISION,
                    self::EVENTO_ASIGNACION_MECANICO,
                    $idUsuario,
                    null,
                    ['modulo' => 'revision_mecanica', 'cierre_inmediato' => true],
                    $nombreUsuario,
                    false,
                    $ahora
                );
                if (empty($cambioInicio['success'])) {
                    $this->db->rollback();
                    return $cambioInicio;
                }
                $idMovimientoInicio = (int) ($cambioInicio['id_movimiento'] ?? 0);
                $estatusCierreAnterior = self::ESTATUS_EN_REVISION;
                $unidad['estatus_inventario'] = self::ESTATUS_EN_REVISION;
            }

            if (!$revision) {
                $this->db->CRUD(
                    "INSERT INTO av_revisiones_mecanicas (
                        id_unidad,
                        id_movimiento_inicio,
                        estatus_revision,
                        id_usuario_inicio,
                        nombre_usuario_inicio,
                        fecha_inicio
                     ) VALUES (
                        :id_unidad,
                        :id_movimiento_inicio,
                        'en_revision',
                        :id_usuario_inicio,
                        :nombre_usuario_inicio,
                        :fecha_inicio
                     )",
                    [
                        'id_unidad' => $idUnidad,
                        'id_movimiento_inicio' => $idMovimientoInicio,
                        'id_usuario_inicio' => $idUsuario > 0 ? $idUsuario : null,
                        'nombre_usuario_inicio' => $nombreUsuario !== '' ? $nombreUsuario : null,
                        'fecha_inicio' => $ahora,
                    ]
                );
                $revision = ['id_revision' => $this->db->lastInsertId()];
            } elseif ($idMovimientoInicio) {
                $this->db->CRUD(
                    "UPDATE av_revisiones_mecanicas
                     SET id_movimiento_inicio = COALESCE(id_movimiento_inicio, :id_movimiento_inicio)
                     WHERE id_revision = :id_revision",
                    [
                        'id_movimiento_inicio' => $idMovimientoInicio,
                        'id_revision' => (int) ($revision['id_revision'] ?? 0),
                    ]
                );
            }

            $idRevision = (int) ($revision['id_revision'] ?? 0);
            $this->db->CRUD(
                "DELETE FROM av_revision_mecanica_items
                 WHERE id_revision = :id_revision",
                ['id_revision' => $idRevision]
            );
            foreach ($itemsRevision as $item) {
                $this->registrarItemRevisionMecanica($idRevision, $item);
            }

            foreach ($evidencias as $slot => $info) {
                if (!isset(self::SLOTS_EVIDENCIAS_REVISION[$slot])) {
                    continue;
                }
                $this->registrarEvidenciaUnidadEtapa(
                    $idUnidad,
                    self::ETAPA_REVISION_MECANICA,
                    $slot,
                    self::SLOTS_EVIDENCIAS_REVISION[$slot],
                    $info,
                    'validado',
                    $idUsuario,
                    $nombreUsuario,
                    $ahora
                );
            }

            $contextoCierre = [
                'id_revision' => $idRevision,
                'diagnostico_general' => $diagnostico,
                'items' => $itemsRevision,
                'otros' => [
                    'mecanica' => $otrosMecanica,
                    'electrica' => $otrosElectrica,
                    'estetica' => $otrosEstetica,
                ],
                'comentarios' => [
                    'mecanica' => $comentarioMecanica,
                    'electrica' => $comentarioElectrica,
                    'estetica' => $comentarioEstetica,
                ],
                'evidencias' => array_keys($evidencias),
            ];
            $unidadCierre = $unidad;
            $unidadCierre['estatus_inventario'] = $estatusCierreAnterior;
            $cambioCierre = $this->aplicarCambioEstatusUnidad(
                $unidadCierre,
                $dictamen,
                self::EVENTO_CIERRE_REVISION,
                $idUsuario,
                in_array($dictamen, [self::ESTATUS_FUERA_PRESUPUESTO, self::ESTATUS_IRREPARABLE], true) ? $diagnostico : null,
                $contextoCierre,
                $nombreUsuario,
                false,
                $ahora
            );
            if (empty($cambioCierre['success'])) {
                $this->db->rollback();
                return $cambioCierre;
            }
            $idMovimientoCierre = (int) ($cambioCierre['id_movimiento'] ?? 0);

            $this->db->CRUD(
                "UPDATE av_revisiones_mecanicas
                 SET id_movimiento_cierre = :id_movimiento_cierre,
                     diagnostico_general = :diagnostico_general,
                     comentario_mecanica = :comentario_mecanica,
                     comentario_electrica = :comentario_electrica,
                     comentario_estetica = :comentario_estetica,
                     otros_mecanica = :otros_mecanica,
                     otros_electrica = :otros_electrica,
                     otros_estetica = :otros_estetica,
                     dictamen = :dictamen,
                     estatus_revision = 'finalizada',
                     id_usuario_cierre = :id_usuario_cierre,
                     nombre_usuario_cierre = :nombre_usuario_cierre,
                     fecha_cierre = :fecha_cierre
                 WHERE id_revision = :id_revision",
                [
                    'id_movimiento_cierre' => $idMovimientoCierre,
                    'diagnostico_general' => $diagnostico,
                    'comentario_mecanica' => $comentarioMecanica,
                    'comentario_electrica' => $comentarioElectrica,
                    'comentario_estetica' => $comentarioEstetica,
                    'otros_mecanica' => $otrosMecanica,
                    'otros_electrica' => $otrosElectrica,
                    'otros_estetica' => $otrosEstetica,
                    'dictamen' => $dictamen,
                    'id_usuario_cierre' => $idUsuario > 0 ? $idUsuario : null,
                    'nombre_usuario_cierre' => $nombreUsuario !== '' ? $nombreUsuario : null,
                    'fecha_cierre' => $ahora,
                    'id_revision' => $idRevision,
                ]
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'No se pudo finalizar la revision mecanica.',
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
            'message' => 'Revision mecanica finalizada. Dictamen: ' . $this->etiquetaDictamenRevision($dictamen) . '.',
            'dictamen' => $dictamen,
            'unidad' => $this->obtenerUnidadPorId($idUnidad),
        ];
    }

    public function cambiarEstatusUnidad(
        int $unidadId,
        string $estatusNuevo,
        string $origenEvento,
        int $usuarioId,
        ?string $justificacion = null,
        array $contexto = [],
        string $nombreUsuario = '',
        bool $overrideSupervisorAutorizado = false
    ): array {
        if ($unidadId <= 0) {
            return ['success' => false, 'message' => 'Unidad invalida.'];
        }
        if (!$this->tablasBaseDisponibles()) {
            return ['success' => false, 'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.'];
        }
        if (!$this->tablaExiste('av_kanban_transiciones')) {
            return ['success' => false, 'message' => 'Falta la tabla av_kanban_transiciones. Ejecuta la migracion de Kanban.'];
        }

        $unidad = $this->obtenerUnidadPorId($unidadId);
        if (!$unidad) {
            return ['success' => false, 'message' => 'No se encontro la unidad en inventario.'];
        }

        $ahora = $this->fechaHoraCdmx();
        try {
            $this->db->beginTransaction();
            $resultado = $this->aplicarCambioEstatusUnidad(
                $unidad,
                $estatusNuevo,
                $origenEvento,
                $usuarioId,
                $justificacion,
                $contexto,
                $nombreUsuario,
                $overrideSupervisorAutorizado,
                $ahora
            );
            if (empty($resultado['success'])) {
                $this->db->rollback();
                return $resultado;
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'No se pudo cambiar el estatus de la unidad.',
                'error' => $e->getMessage(),
            ];
        }

        return $resultado + [
            'unidad' => $this->obtenerUnidadPorId($unidadId),
        ];
    }

    public function obtenerColumnasKanbanOperativo(): array
    {
        return array_values($this->columnasKanbanOperativo());
    }

    public function obtenerCatalogosKanbanOperativo(): array
    {
        return [
            'columnas' => $this->obtenerColumnasKanbanOperativo(),
            'tipos_unidad' => $this->obtenerTiposUnidadKanban(),
        ];
    }

    public function obtenerResumenKanbanOperativo(array $filtros = []): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'tablas_disponibles' => false,
                'total' => 0,
                'por_estatus' => [],
            ];
        }

        $params = [];
        $where = $this->whereKanbanOperativo($filtros, $params);
        $estatusSql = $this->sqlInConstantes(array_keys($this->columnasKanbanOperativo()));
        $where[] = "u.estatus_inventario IN ({$estatusSql})";
        $whereSql = 'WHERE ' . implode(' AND ', $where);

        $rows = $this->db->queryAll(
            "SELECT u.estatus_inventario, COUNT(*) AS total
             FROM av_unidades u
             LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
             {$whereSql}
             GROUP BY u.estatus_inventario",
            $params
        ) ?: [];

        $porEstatus = [];
        $total = 0;
        foreach ($this->columnasKanbanOperativo() as $estatus => $columna) {
            $porEstatus[$estatus] = $columna + ['total' => 0];
        }
        foreach ($rows as $row) {
            $estatus = (string) ($row['estatus_inventario'] ?? '');
            $conteo = (int) ($row['total'] ?? 0);
            if (isset($porEstatus[$estatus])) {
                $porEstatus[$estatus]['total'] = $conteo;
                $total += $conteo;
            }
        }

        return [
            'tablas_disponibles' => true,
            'total' => $total,
            'por_estatus' => array_values($porEstatus),
        ];
    }

    public function listarKanbanOperativo(array $filtros = []): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.',
                'columnas' => [],
                'total' => 0,
            ];
        }

        $limitPorColumna = max(5, min(100, (int) ($filtros['limit_por_columna'] ?? 40)));
        $resumen = $this->obtenerResumenKanbanOperativo($filtros);
        $totales = [];
        foreach (($resumen['por_estatus'] ?? []) as $row) {
            $totales[(string) ($row['estatus'] ?? '')] = (int) ($row['total'] ?? 0);
        }

        $columnas = [];
        foreach ($this->columnasKanbanOperativo() as $estatus => $columna) {
            $params = ['estatus' => $estatus, 'foto_etapa' => self::ETAPA_EVIDENCIAS_INGRESO];
            $where = $this->whereKanbanOperativo($filtros, $params);
            $where[] = 'u.estatus_inventario = :estatus';
            $whereSql = 'WHERE ' . implode(' AND ', $where);

            $rows = $this->db->queryAll(
                "SELECT
                    u.id_unidad,
                    u.folio_unidad,
                    u.id_celula,
                    u.id_origen,
                    u.id_credito,
                    u.vin,
                    u.no_motor,
                    u.placas,
                    u.marca,
                    u.modelo,
                    u.anio,
                    u.color,
                    u.kilometraje,
                    u.tipo_unidad,
                    u.categoria,
                    u.estatus_inventario,
                    u.id_ubicacion_actual,
                    ub.nombre_ubicacion,
                    ub.tipo_ubicacion,
                    foto.url AS foto_url,
                    DATEDIFF(CURDATE(), DATE(COALESCE(u.fecha_ingreso_virtual, u.fecha_alta))) AS dias_almacen,
                    DATE_FORMAT(u.fecha_ingreso_virtual, '%d/%m/%Y %H:%i') AS fecha_ingreso_virtual_fmt,
                    DATE_FORMAT(u.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
                 FROM av_unidades u
                 LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
                 LEFT JOIN (
                    SELECT e.*
                    FROM av_evidencias e
                    INNER JOIN (
                        SELECT id_unidad, MAX(id_evidencia) AS id_evidencia
                        FROM av_evidencias
                        WHERE etapa = :foto_etapa
                          AND slot = 'foto_frontal'
                          AND estatus NOT IN ('reemplazado', 'eliminado')
                        GROUP BY id_unidad
                    ) ult_foto ON ult_foto.id_evidencia = e.id_evidencia
                 ) foto ON foto.id_unidad = u.id_unidad
                 {$whereSql}
                 ORDER BY COALESCE(u.fecha_actualizacion, u.fecha_alta) DESC, u.id_unidad DESC
                 LIMIT {$limitPorColumna}",
                $params
            ) ?: [];

            $celulas = $this->obtenerCelulas();
            foreach ($rows as &$row) {
                $idCelula = (int) ($row['id_celula'] ?? 0);
                $row['nombre_celula'] = $celulas[$idCelula] ?? ('Celula ' . $idCelula);
                $row['foto_url_publica'] = $this->urlPublicaEvidencia((string) ($row['foto_url'] ?? ''));
                $row['dias_almacen'] = max(0, (int) ($row['dias_almacen'] ?? 0));
                $row['sla'] = $this->alertaSlaKanban((string) ($row['estatus_inventario'] ?? ''), (int) $row['dias_almacen']);
            }
            unset($row);

            $columnas[] = $columna + [
                'total' => $totales[$estatus] ?? count($rows),
                'rows' => $rows,
                'limit' => $limitPorColumna,
                'truncada' => (($totales[$estatus] ?? 0) > count($rows)),
            ];
        }

        return [
            'success' => true,
            'columnas' => $columnas,
            'total' => (int) ($resumen['total'] ?? 0),
            'limit_por_columna' => $limitPorColumna,
        ];
    }

    public function enviarUnidadPisoVenta(int $idUnidad, array $datos, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return ['success' => false, 'message' => 'Faltan tablas base de Inventario.'];
        }
        if (!$this->tablaExiste('av_piso_venta_envios')) {
            return ['success' => false, 'message' => 'Falta la tabla av_piso_venta_envios. Ejecuta el CREATE TABLE de Piso de Venta en DBeaver.'];
        }

        $destinoVenta = $this->normalizarTexto((string) ($datos['destino_venta'] ?? ''), 120);
        $clientesVenta = ['Pension a Max', 'Amigo Efectivo'];
        if ($destinoVenta === null || !in_array($destinoVenta, $clientesVenta, true)) {
            return ['success' => false, 'message' => 'Selecciona un cliente valido: Pension a Max o Amigo Efectivo.'];
        }
        $observaciones = $this->normalizarTexto((string) ($datos['observaciones'] ?? ''), 1000);

        $unidad = $this->obtenerUnidadPorId($idUnidad);
        if (!$unidad) {
            return ['success' => false, 'message' => 'Unidad no encontrada.'];
        }

        $fecha = $this->fechaHoraCdmx();
        $contexto = [
            'destino_venta' => $destinoVenta,
            'observaciones' => $observaciones,
        ];

        try {
            $this->db->beginTransaction();
            $resultado = $this->aplicarCambioEstatusUnidad(
                $unidad,
                self::ESTATUS_LISTA_VENTA,
                self::EVENTO_ENVIO_PISO_VENTA,
                $idUsuario,
                null,
                $contexto,
                $nombreUsuario,
                false,
                $fecha
            );
            if (empty($resultado['success'])) {
                $this->db->rollback();
                return $resultado;
            }

            $idEnvio = $this->registrarPisoVentaEstructurado(
                (int) $unidad['id_unidad'],
                (int) ($resultado['id_transicion'] ?? 0),
                $destinoVenta,
                $observaciones,
                $idUsuario,
                $nombreUsuario,
                $fecha
            );

            $this->db->commit();

            return $resultado + [
                'message' => 'Unidad enviada a piso de venta para ' . $destinoVenta . '.',
                'id_envio_piso_venta' => $idEnvio,
                'cliente_destino' => $destinoVenta,
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'No se pudo enviar la unidad a piso de venta.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function obtenerResumenPisoVenta(): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'tablas_disponibles' => false,
                'total' => 0,
                'pension_max' => 0,
                'amigo_efectivo' => 0,
                'sin_cliente' => 0,
            ];
        }

        $pisoDisponible = $this->tablaExiste('av_piso_venta_envios');
        $total = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_unidades
             WHERE deleted_at IS NULL
               AND estatus_inventario = :estatus",
            ['estatus' => self::ESTATUS_LISTA_VENTA]
        ) ?: [];

        $resumen = [
            'tablas_disponibles' => $pisoDisponible,
            'total' => (int) ($total['total'] ?? 0),
            'pension_max' => 0,
            'amigo_efectivo' => 0,
            'sin_cliente' => 0,
        ];

        if (!$pisoDisponible) {
            $resumen['sin_cliente'] = $resumen['total'];
            return $resumen;
        }

        $rows = $this->db->queryAll(
            "SELECT COALESCE(pv.cliente_destino, 'SIN_CLIENTE') AS cliente_destino, COUNT(*) AS total
             FROM av_unidades u
             LEFT JOIN (
                SELECT p1.*
                FROM av_piso_venta_envios p1
                INNER JOIN (
                    SELECT id_unidad, MAX(id_envio) AS id_envio
                    FROM av_piso_venta_envios
                    WHERE estatus_envio <> 'cancelada'
                    GROUP BY id_unidad
                ) ult ON ult.id_envio = p1.id_envio
             ) pv ON pv.id_unidad = u.id_unidad
             WHERE u.deleted_at IS NULL
               AND u.estatus_inventario = :estatus
             GROUP BY COALESCE(pv.cliente_destino, 'SIN_CLIENTE')",
            ['estatus' => self::ESTATUS_LISTA_VENTA]
        ) ?: [];

        foreach ($rows as $row) {
            $cliente = (string) ($row['cliente_destino'] ?? '');
            $cantidad = (int) ($row['total'] ?? 0);
            if ($cliente === 'Pension a Max') {
                $resumen['pension_max'] = $cantidad;
            } elseif ($cliente === 'Amigo Efectivo') {
                $resumen['amigo_efectivo'] = $cantidad;
            } else {
                $resumen['sin_cliente'] += $cantidad;
            }
        }

        return $resumen;
    }

    public function listarPisoVentaUnidades(array $filtros = []): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas base de Inventario.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
                'pages' => 1,
            ];
        }

        $page = max(1, (int) ($filtros['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));
        $pisoDisponible = $this->tablaExiste('av_piso_venta_envios');

        $where = [
            'u.deleted_at IS NULL',
            'u.estatus_inventario = :estatus',
        ];
        $params = ['estatus' => self::ESTATUS_LISTA_VENTA];

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $where[] = "(
                u.folio_unidad LIKE :q
                OR u.vin LIKE :q
                OR u.no_motor LIKE :q
                OR u.placas LIKE :q
                OR u.marca LIKE :q
                OR u.modelo LIKE :q
                OR u.color LIKE :q
                OR CAST(u.id_unidad AS CHAR) LIKE :q
                OR CAST(u.id_credito AS CHAR) LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        $idCelula = (int) ($filtros['id_celula'] ?? 0);
        if ($idCelula > 0) {
            $where[] = 'u.id_celula = :id_celula';
            $params['id_celula'] = $idCelula;
        }

        $idUbicacion = (int) ($filtros['id_ubicacion'] ?? 0);
        if ($idUbicacion > 0) {
            $where[] = 'u.id_ubicacion_actual = :id_ubicacion';
            $params['id_ubicacion'] = $idUbicacion;
        }

        $cliente = trim((string) ($filtros['cliente_destino'] ?? ''));
        if ($cliente !== '' && $pisoDisponible) {
            $where[] = 'pv.cliente_destino = :cliente_destino';
            $params['cliente_destino'] = $cliente;
        }

        $pisoSelect = "NULL AS id_envio_piso_venta,
            NULL AS cliente_destino,
            NULL AS estatus_envio,
            NULL AS fecha_envio_fmt";
        $pisoJoin = '';
        if ($pisoDisponible) {
            $pisoSelect = "pv.id_envio AS id_envio_piso_venta,
            pv.cliente_destino,
            pv.estatus_envio,
            DATE_FORMAT(pv.fecha_envio, '%d/%m/%Y %H:%i') AS fecha_envio_fmt";
            $pisoJoin = "LEFT JOIN (
                SELECT p1.*
                FROM av_piso_venta_envios p1
                INNER JOIN (
                    SELECT id_unidad, MAX(id_envio) AS id_envio
                    FROM av_piso_venta_envios
                    WHERE estatus_envio <> 'cancelada'
                    GROUP BY id_unidad
                ) ult ON ult.id_envio = p1.id_envio
             ) pv ON pv.id_unidad = u.id_unidad";
        }
        $orderFecha = $pisoDisponible
            ? 'COALESCE(pv.fecha_envio, u.fecha_actualizacion, u.fecha_alta)'
            : 'COALESCE(u.fecha_actualizacion, u.fecha_alta)';

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalRow = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_unidades u
             {$pisoJoin}
             {$whereSql}",
            $params
        ) ?: [];
        $total = (int) ($totalRow['total'] ?? 0);
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);
        $offset = ($page - 1) * $limit;

        $rows = $this->db->queryAll(
            "SELECT
                u.id_unidad,
                u.folio_unidad,
                u.id_celula,
                u.id_origen,
                u.id_credito,
                u.vin,
                u.no_motor,
                u.placas,
                u.marca,
                u.modelo,
                u.anio,
                u.color,
                u.kilometraje,
                u.tipo_unidad,
                u.estatus_inventario,
                u.id_ubicacion_actual,
                ub.nombre_ubicacion,
                ub.tipo_ubicacion,
                foto.url AS foto_url,
                DATEDIFF(CURDATE(), DATE(COALESCE(u.fecha_ingreso_virtual, u.fecha_alta))) AS dias_almacen,
                DATE_FORMAT(u.fecha_ingreso_virtual, '%d/%m/%Y %H:%i') AS fecha_ingreso_virtual_fmt,
                DATE_FORMAT(u.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt,
                {$pisoSelect}
             FROM av_unidades u
             LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
             {$pisoJoin}
             LEFT JOIN (
                SELECT e.*
                FROM av_evidencias e
                INNER JOIN (
                    SELECT id_unidad, MAX(id_evidencia) AS id_evidencia
                    FROM av_evidencias
                    WHERE estatus NOT IN ('reemplazado', 'eliminado')
                    GROUP BY id_unidad
                ) ult_foto ON ult_foto.id_evidencia = e.id_evidencia
             ) foto ON foto.id_unidad = u.id_unidad
             {$whereSql}
             ORDER BY {$orderFecha} DESC, u.id_unidad DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        ) ?: [];

        $celulas = $this->obtenerCelulas();
        foreach ($rows as &$row) {
            $id = (int) ($row['id_celula'] ?? 0);
            $row['nombre_celula'] = $celulas[$id] ?? ('Celula ' . $id);
            $row['foto_url_publica'] = $this->urlPublicaEvidencia((string) ($row['foto_url'] ?? ''));
            $row['dias_almacen'] = max(0, (int) ($row['dias_almacen'] ?? 0));
            $row['sla'] = $this->alertaSlaKanban(self::ESTATUS_LISTA_VENTA, (int) $row['dias_almacen']);
        }
        unset($row);

        return [
            'success' => true,
            'tablas_disponibles' => $pisoDisponible,
            'rows' => $rows,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function obtenerResumenTraspasos(): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return [
                'tablas_disponibles' => false,
                'disponibles' => 0,
                'creadas' => 0,
                'en_transito' => 0,
                'recibidas' => 0,
                'total_ordenes' => 0,
            ];
        }

        $disponibles = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_unidades
             WHERE deleted_at IS NULL
               AND estatus_inventario = :estatus",
            ['estatus' => self::ESTATUS_LISTA_VENTA]
        ) ?: [];

        $resumen = [
            'tablas_disponibles' => $this->tablaExiste('av_traspasos'),
            'disponibles' => (int) ($disponibles['total'] ?? 0),
            'creadas' => 0,
            'en_transito' => 0,
            'recibidas' => 0,
            'total_ordenes' => 0,
        ];

        if (!$resumen['tablas_disponibles']) {
            return $resumen;
        }

        $row = $this->db->queryOne(
            "SELECT
                COUNT(*) AS total_ordenes,
                SUM(estatus_traspaso = 'creada') AS creadas,
                SUM(estatus_traspaso = 'en_transito') AS en_transito,
                SUM(estatus_traspaso = 'recibida') AS recibidas
             FROM av_traspasos"
        ) ?: [];

        $resumen['total_ordenes'] = (int) ($row['total_ordenes'] ?? 0);
        $resumen['creadas'] = (int) ($row['creadas'] ?? 0);
        $resumen['en_transito'] = (int) ($row['en_transito'] ?? 0);
        $resumen['recibidas'] = (int) ($row['recibidas'] ?? 0);

        return $resumen;
    }

    public function listarUnidadesDisponiblesTraspaso(array $filtros = []): array
    {
        return $this->listarPisoVentaUnidades($filtros);
    }

    public function listarTraspasos(array $filtros = []): array
    {
        if (!$this->tablasBaseDisponibles() || !$this->tablaExiste('av_traspasos')) {
            return [
                'success' => false,
                'message' => 'Faltan tablas de Traspasos. Ejecuta el CREATE TABLE de av_traspasos en DBeaver.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
                'pages' => 1,
            ];
        }

        $page = max(1, (int) ($filtros['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));
        $where = ['u.deleted_at IS NULL'];
        $params = [];

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $where[] = "(
                t.folio_traspaso LIKE :q
                OR t.transportista_nombre LIKE :q
                OR u.folio_unidad LIKE :q
                OR u.vin LIKE :q
                OR u.no_motor LIKE :q
                OR u.placas LIKE :q
                OR u.marca LIKE :q
                OR u.modelo LIKE :q
                OR CAST(u.id_unidad AS CHAR) LIKE :q
                OR CAST(u.id_credito AS CHAR) LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        $estatus = trim((string) ($filtros['estatus_traspaso'] ?? ''));
        if ($estatus !== '') {
            $where[] = 't.estatus_traspaso = :estatus_traspaso';
            $params['estatus_traspaso'] = $estatus;
        }

        $idCelula = (int) ($filtros['id_celula'] ?? 0);
        if ($idCelula > 0) {
            $where[] = 'u.id_celula = :id_celula';
            $params['id_celula'] = $idCelula;
        }

        $idOrigen = (int) ($filtros['id_ubicacion_origen'] ?? 0);
        if ($idOrigen > 0) {
            $where[] = 't.id_ubicacion_origen = :id_ubicacion_origen';
            $params['id_ubicacion_origen'] = $idOrigen;
        }

        $idDestino = (int) ($filtros['id_ubicacion_destino'] ?? 0);
        if ($idDestino > 0) {
            $where[] = 't.id_ubicacion_destino = :id_ubicacion_destino';
            $params['id_ubicacion_destino'] = $idDestino;
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalRow = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM av_traspasos t
             INNER JOIN av_unidades u ON u.id_unidad = t.id_unidad
             {$whereSql}",
            $params
        ) ?: [];
        $total = (int) ($totalRow['total'] ?? 0);
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);
        $offset = ($page - 1) * $limit;

        $rows = $this->db->queryAll(
            "SELECT
                t.*,
                DATE_FORMAT(t.fecha_salida_estimada, '%d/%m/%Y %H:%i') AS fecha_salida_estimada_fmt,
                DATE_FORMAT(t.fecha_recoleccion_origen, '%d/%m/%Y %H:%i') AS fecha_recoleccion_origen_fmt,
                DATE_FORMAT(t.fecha_recepcion_destino, '%d/%m/%Y %H:%i') AS fecha_recepcion_destino_fmt,
                DATE_FORMAT(t.fecha_creacion, '%d/%m/%Y %H:%i') AS fecha_creacion_fmt,
                u.folio_unidad,
                u.id_celula,
                u.id_credito,
                u.vin,
                u.no_motor,
                u.placas,
                u.marca,
                u.modelo,
                u.anio,
                u.color,
                u.estatus_inventario,
                origen.nombre_ubicacion AS ubicacion_origen_nombre,
                destino.nombre_ubicacion AS ubicacion_destino_nombre,
                foto.url AS foto_url
             FROM av_traspasos t
             INNER JOIN av_unidades u ON u.id_unidad = t.id_unidad
             LEFT JOIN av_ubicaciones origen ON origen.id_ubicacion = t.id_ubicacion_origen
             LEFT JOIN av_ubicaciones destino ON destino.id_ubicacion = t.id_ubicacion_destino
             LEFT JOIN (
                SELECT e.*
                FROM av_evidencias e
                INNER JOIN (
                    SELECT id_unidad, MAX(id_evidencia) AS id_evidencia
                    FROM av_evidencias
                    WHERE estatus NOT IN ('reemplazado', 'eliminado')
                    GROUP BY id_unidad
                ) ult_foto ON ult_foto.id_evidencia = e.id_evidencia
             ) foto ON foto.id_unidad = u.id_unidad
             {$whereSql}
             ORDER BY t.fecha_creacion DESC, t.id_traspaso DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        ) ?: [];

        $celulas = $this->obtenerCelulas();
        foreach ($rows as &$row) {
            $id = (int) ($row['id_celula'] ?? 0);
            $row['nombre_celula'] = $celulas[$id] ?? ('Celula ' . $id);
            $row['foto_url_publica'] = $this->urlPublicaEvidencia((string) ($row['foto_url'] ?? ''));
        }
        unset($row);

        return [
            'success' => true,
            'rows' => $rows,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function crearOrdenTraspaso(int $idUnidad, array $datos, array $evidencias, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        if (!$this->tablasBaseDisponibles() || !$this->tablaExiste('av_traspasos')) {
            return ['success' => false, 'message' => 'Faltan tablas de Traspasos. Ejecuta el CREATE TABLE de av_traspasos en DBeaver.'];
        }
        if (empty($evidencias)) {
            return ['success' => false, 'message' => 'Agrega evidencia fotografica de origen antes de crear el traspaso.'];
        }

        $unidad = $this->obtenerUnidadPorId($idUnidad);
        if (!$unidad) {
            return ['success' => false, 'message' => 'Unidad no encontrada.'];
        }

        $idOrigen = $this->intONull($unidad['id_ubicacion_actual'] ?? null);
        $idDestino = (int) ($datos['id_ubicacion_destino'] ?? 0);
        $destino = $this->obtenerUbicacionPorId($idDestino);
        if (!$idOrigen) {
            return ['success' => false, 'message' => 'La unidad no tiene ubicacion origen asignada.'];
        }
        if (!$destino) {
            return ['success' => false, 'message' => 'Selecciona una agencia destino valida.'];
        }
        if ($idOrigen === $idDestino) {
            return ['success' => false, 'message' => 'La agencia destino debe ser diferente a la ubicacion actual.'];
        }

        $tipoTransportista = strtolower(trim((string) ($datos['tipo_transportista'] ?? '')));
        if (!in_array($tipoTransportista, ['interno', 'externo'], true)) {
            return ['success' => false, 'message' => 'Selecciona tipo de transportista interno o externo.'];
        }

        $transportistaNombre = $this->normalizarTexto((string) ($datos['transportista_nombre'] ?? ''), 150);
        if ($transportistaNombre === null) {
            return ['success' => false, 'message' => 'Captura el nombre del transportista.'];
        }

        $fechaSalida = $this->normalizarFechaHoraInput((string) ($datos['fecha_salida_estimada'] ?? ''));
        if ($fechaSalida === null) {
            return ['success' => false, 'message' => 'Captura fecha y hora estimada de salida.'];
        }

        $transportistaContacto = $this->normalizarTexto((string) ($datos['transportista_contacto'] ?? ''), 80);
        $observacionesOrigen = $this->normalizarTexto((string) ($datos['observaciones_origen'] ?? ''), 1200);
        $fecha = $this->fechaHoraCdmx();

        try {
            $this->db->beginTransaction();
            $folio = $this->generarFolioTraspaso($fecha);
            $contexto = [
                'folio_traspaso' => $folio,
                'id_ubicacion_origen' => $idOrigen,
                'id_ubicacion_destino' => $idDestino,
                'tipo_transportista' => $tipoTransportista,
                'transportista_nombre' => $transportistaNombre,
                'fecha_salida_estimada' => $fechaSalida,
            ];

            $cambio = $this->aplicarCambioEstatusUnidad(
                $unidad,
                self::ESTATUS_EN_TRASPASO,
                self::EVENTO_ORDEN_TRASPASO,
                $idUsuario,
                null,
                $contexto,
                $nombreUsuario,
                false,
                $fecha
            );
            if (empty($cambio['success'])) {
                $this->db->rollback();
                return $cambio;
            }

            $this->db->CRUD(
                "INSERT INTO av_traspasos (
                    folio_traspaso,
                    id_unidad,
                    id_ubicacion_origen,
                    id_ubicacion_destino,
                    id_transicion_salida,
                    tipo_transportista,
                    transportista_nombre,
                    transportista_contacto,
                    fecha_salida_estimada,
                    fecha_recoleccion_origen,
                    estatus_traspaso,
                    observaciones_origen,
                    id_usuario_creacion,
                    nombre_usuario_creacion,
                    fecha_creacion
                 ) VALUES (
                    :folio_traspaso,
                    :id_unidad,
                    :id_ubicacion_origen,
                    :id_ubicacion_destino,
                    :id_transicion_salida,
                    :tipo_transportista,
                    :transportista_nombre,
                    :transportista_contacto,
                    :fecha_salida_estimada,
                    :fecha_recoleccion_origen,
                    'en_transito',
                    :observaciones_origen,
                    :id_usuario_creacion,
                    :nombre_usuario_creacion,
                    :fecha_creacion
                 )",
                [
                    'folio_traspaso' => $folio,
                    'id_unidad' => (int) $unidad['id_unidad'],
                    'id_ubicacion_origen' => $idOrigen,
                    'id_ubicacion_destino' => $idDestino,
                    'id_transicion_salida' => (int) ($cambio['id_transicion'] ?? 0),
                    'tipo_transportista' => $tipoTransportista,
                    'transportista_nombre' => $transportistaNombre,
                    'transportista_contacto' => $transportistaContacto,
                    'fecha_salida_estimada' => $fechaSalida,
                    'fecha_recoleccion_origen' => $fecha,
                    'observaciones_origen' => $observacionesOrigen,
                    'id_usuario_creacion' => $idUsuario > 0 ? $idUsuario : null,
                    'nombre_usuario_creacion' => $nombreUsuario !== '' ? $nombreUsuario : null,
                    'fecha_creacion' => $fecha,
                ]
            );
            $idTraspaso = $this->db->lastInsertId();

            $this->registrarEvidenciasTraspaso((int) $unidad['id_unidad'], 'traspaso_origen', $evidencias, $idUsuario, $nombreUsuario, $fecha);
            $this->registrarBitacora(
                (int) $unidad['id_unidad'],
                'Traspasos',
                'ORDEN CREADA',
                'Orden de traspaso ' . $folio . ' creada y unidad en transito.',
                $contexto + ['id_traspaso' => $idTraspaso],
                $idUsuario,
                $nombreUsuario,
                $fecha
            );

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Orden de traspaso creada correctamente.',
                'id_traspaso' => $idTraspaso,
                'folio_traspaso' => $folio,
                'id_transicion' => (int) ($cambio['id_transicion'] ?? 0),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'No se pudo crear la orden de traspaso.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function confirmarRecepcionTraspaso(int $idTraspaso, array $datos, array $evidencias, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        if (!$this->tablasBaseDisponibles() || !$this->tablaExiste('av_traspasos')) {
            return ['success' => false, 'message' => 'Faltan tablas de Traspasos.'];
        }
        if (empty($evidencias)) {
            return ['success' => false, 'message' => 'Agrega evidencia fotografica de destino para cerrar el traspaso.'];
        }

        $traspaso = $this->obtenerTraspasoPorId($idTraspaso);
        if (!$traspaso) {
            return ['success' => false, 'message' => 'Orden de traspaso no encontrada.'];
        }
        if (!in_array((string) ($traspaso['estatus_traspaso'] ?? ''), ['creada', 'en_transito'], true)) {
            return ['success' => false, 'message' => 'La orden ya fue cerrada o cancelada.'];
        }

        $unidad = $this->obtenerUnidadPorId((int) ($traspaso['id_unidad'] ?? 0));
        if (!$unidad) {
            return ['success' => false, 'message' => 'Unidad no encontrada.'];
        }

        $observacionesDestino = $this->normalizarTexto((string) ($datos['observaciones_destino'] ?? ''), 1200);
        $fecha = $this->fechaHoraCdmx();
        $idOrigen = $this->intONull($traspaso['id_ubicacion_origen'] ?? null);
        $idDestino = $this->intONull($traspaso['id_ubicacion_destino'] ?? null);
        if (!$idDestino) {
            return ['success' => false, 'message' => 'La orden de traspaso no tiene destino valido.'];
        }

        try {
            $this->db->beginTransaction();
            $contexto = [
                'id_traspaso' => $idTraspaso,
                'folio_traspaso' => (string) ($traspaso['folio_traspaso'] ?? ''),
                'id_ubicacion_origen' => $idOrigen,
                'id_ubicacion_destino' => $idDestino,
            ];
            $cambio = $this->aplicarCambioEstatusUnidad(
                $unidad,
                self::ESTATUS_LISTA_VENTA,
                self::EVENTO_CIERRE_TRASPASO,
                $idUsuario,
                null,
                $contexto,
                $nombreUsuario,
                false,
                $fecha
            );
            if (empty($cambio['success'])) {
                $this->db->rollback();
                return $cambio;
            }

            $this->db->CRUD(
                "UPDATE av_unidades
                 SET id_ubicacion_actual = :id_ubicacion_actual,
                     actualizado_por = :actualizado_por,
                     fecha_actualizacion = :fecha
                 WHERE id_unidad = :id_unidad
                   AND deleted_at IS NULL",
                [
                    'id_ubicacion_actual' => $idDestino,
                    'actualizado_por' => $idUsuario > 0 ? $idUsuario : null,
                    'fecha' => $fecha,
                    'id_unidad' => (int) $unidad['id_unidad'],
                ]
            );

            $idMovimientoUbicacion = $this->registrarMovimiento(
                (int) $unidad['id_unidad'],
                'traspaso_recepcion_destino',
                self::ESTATUS_EN_TRASPASO,
                self::ESTATUS_LISTA_VENTA,
                $idOrigen,
                $idDestino,
                'Recepcion y VoBo destino de traspaso ' . (string) ($traspaso['folio_traspaso'] ?? '') . '.',
                $idUsuario,
                $nombreUsuario,
                $fecha
            );

            $this->db->CRUD(
                "UPDATE av_traspasos
                 SET estatus_traspaso = 'recibida',
                     fecha_recepcion_destino = :fecha_recepcion_destino,
                     observaciones_destino = :observaciones_destino,
                     id_usuario_recepcion = :id_usuario_recepcion,
                     nombre_usuario_recepcion = :nombre_usuario_recepcion,
                     id_transicion_recepcion = :id_transicion_recepcion
                 WHERE id_traspaso = :id_traspaso",
                [
                    'fecha_recepcion_destino' => $fecha,
                    'observaciones_destino' => $observacionesDestino,
                    'id_usuario_recepcion' => $idUsuario > 0 ? $idUsuario : null,
                    'nombre_usuario_recepcion' => $nombreUsuario !== '' ? $nombreUsuario : null,
                    'id_transicion_recepcion' => (int) ($cambio['id_transicion'] ?? 0),
                    'id_traspaso' => $idTraspaso,
                ]
            );

            $this->registrarEvidenciasTraspaso((int) $unidad['id_unidad'], 'traspaso_destino', $evidencias, $idUsuario, $nombreUsuario, $fecha);
            $this->registrarBitacora(
                (int) $unidad['id_unidad'],
                'Traspasos',
                'VOBO DESTINO',
                'Traspaso ' . (string) ($traspaso['folio_traspaso'] ?? '') . ' recibido en destino.',
                $contexto + [
                    'observaciones_destino' => $observacionesDestino,
                    'id_movimiento_ubicacion' => $idMovimientoUbicacion,
                    'id_transicion' => (int) ($cambio['id_transicion'] ?? 0),
                ],
                $idUsuario,
                $nombreUsuario,
                $fecha
            );

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Traspaso recibido y unidad lista para venta en destino.',
                'id_traspaso' => $idTraspaso,
                'id_unidad' => (int) $unidad['id_unidad'],
                'id_transicion' => (int) ($cambio['id_transicion'] ?? 0),
            ];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            return [
                'success' => false,
                'message' => 'No se pudo confirmar la recepcion del traspaso.',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function sincronizarRecolectadasMotosAdjudicadas(int $idUsuario = 0, string $nombreUsuario = '', int $limit = 200): array
    {
        if (!$this->tablasBaseDisponibles()) {
            return ['success' => false, 'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.'];
        }
        if (!$this->trackingRecoleccionDisponible()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas de Tracking Recoleccion para detectar motos recolectadas.',
                'creadas' => 0,
                'existentes' => 0,
                'errores' => [],
            ];
        }

        $limit = max(1, min(500, $limit));
        $pendientes = $this->listarPendientesMotosAdjudicadas(['limit' => $limit, 'page' => 1]);
        if (empty($pendientes['success'])) {
            return [
                'success' => false,
                'message' => $pendientes['message'] ?? 'No se pudieron consultar recolectadas pendientes.',
                'creadas' => 0,
                'existentes' => 0,
                'errores' => [],
            ];
        }

        $creadas = 0;
        $existentes = 0;
        $errores = [];
        foreach (($pendientes['rows'] ?? []) as $row) {
            $idOperacion = (int) ($row['id_operacion'] ?? 0);
            if ($idOperacion <= 0) {
                continue;
            }

            $res = $this->crearDesdeMotosAdjudicadas($idOperacion, $idUsuario, $nombreUsuario);
            if (!empty($res['success'])) {
                if (!empty($res['ya_existe'])) {
                    $existentes++;
                } else {
                    $creadas++;
                }
                continue;
            }

            $errores[] = [
                'id_operacion' => $idOperacion,
                'message' => $res['message'] ?? 'No se pudo sincronizar.',
            ];
        }

        return [
            'success' => empty($errores),
            'message' => empty($errores)
                ? 'Sincronizacion de recolectadas completada.'
                : 'Sincronizacion parcial de recolectadas.',
            'creadas' => $creadas,
            'existentes' => $existentes,
            'errores' => $errores,
            'procesadas' => $creadas + $existentes + count($errores),
            'pendientes_restantes' => max(0, (int) ($pendientes['total'] ?? 0) - $limit),
        ];
    }

    public function listarPendientesMotosAdjudicadas(array $filtros = []): array
    {
        if (!$this->tablaExiste('adj_operacion')) {
            return ['success' => false, 'message' => 'No existe la tabla adj_operacion.', 'rows' => [], 'total' => 0];
        }
        if (!$this->tablaExiste('av_unidades')) {
            return ['success' => false, 'message' => 'No existe la tabla av_unidades.', 'rows' => [], 'total' => 0];
        }
        if (!$this->trackingRecoleccionDisponible()) {
            return [
                'success' => false,
                'message' => 'Faltan tablas de Tracking Recoleccion para filtrar motos recolectadas.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
                'pages' => 1,
            ];
        }

        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));
        $page = max(1, (int) ($filtros['page'] ?? 1));
        $q = trim((string) ($filtros['q'] ?? ''));
        $trackingJoin = $this->joinTrackingRecolectadasSql('ao');

        $selectCols = [
            $this->adjOperacionSelectColumnaONull('moto_marca', 'ao'),
            $this->adjOperacionSelectColumnaONull('moto_modelo', 'ao'),
            $this->adjOperacionSelectColumnaONull('moto_anio', 'ao'),
            $this->adjOperacionSelectColumnaONull('moto_color', 'ao'),
            $this->adjOperacionSelectColumnaONull('moto_no_serie', 'ao'),
            $this->adjOperacionSelectColumnaONull('moto_no_motor', 'ao'),
            $this->adjOperacionSelectColumnaONull('moto_placas', 'ao'),
            $this->adjOperacionSelectColumnaONull('marca', 'ao'),
            $this->adjOperacionSelectColumnaONull('modelo', 'ao'),
            $this->adjOperacionSelectColumnaONull('serie', 'ao'),
            $this->adjOperacionSelectColumnaONull('num_motor', 'ao'),
            $this->adjOperacionSelectColumnaONull('placas', 'ao'),
            $this->adjOperacionSelectColumnaONull('fecha_llegada_almacen', 'ao'),
            $this->adjOperacionSelectColumnaONull('recepcion_confirmada_at', 'ao'),
        ];

        $where = [
            'av.id_unidad IS NULL',
            "COALESCE(ao.estatus, '') NOT IN ('cancelado', 'Cancelado')",
        ];
        $params = [];

        if ($q !== '') {
            $where[] = "(
                CAST(ao.id AS CHAR) LIKE :q
                OR CAST(ao.id_credito AS CHAR) LIKE :q
                OR ao.nombre_cliente LIKE :q
                OR ao.folio LIKE :q
                OR trk_ruta.nombre_ruta LIKE :q
                OR cedis_dest.nombre_agencia LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalRow = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM adj_operacion ao
             {$trackingJoin}
             LEFT JOIN av_unidades av
               ON av.id_celula = :celula_total
              AND av.id_origen = ao.id
              AND av.deleted_at IS NULL
             {$whereSql}",
            ['celula_total' => self::CELULA_MOTOS_ADJUDICADAS] + $params
        ) ?: [];
        $total = (int) ($totalRow['total'] ?? 0);
        $pages = max(1, (int) ceil($total / $limit));
        $page = min($page, $pages);
        $offset = ($page - 1) * $limit;

        $rows = $this->db->queryAll(
            "SELECT
                ao.id AS id_operacion,
                ao.folio,
                ao.id_credito,
                ao.nombre_cliente,
                ao.estatus,
                DATE_FORMAT(ao.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                DATE_FORMAT(ao.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt,
                trk_det.id_detalle AS tracking_id_detalle,
                trk_det.estatus_recoleccion AS tracking_estatus_recoleccion,
                trk_ruta.id_ruta AS tracking_id_ruta,
                trk_ruta.nombre_ruta AS tracking_nombre_ruta,
                trk_ruta.estatus_ruta AS tracking_estatus_ruta,
                trk_ruta.id_cedis_destino AS tracking_id_cedis_destino,
                DATE_FORMAT(trk_ruta.fecha_programada, '%d/%m/%Y') AS tracking_fecha_programada_fmt,
                DATE_FORMAT(trk_ruta.fecha_finalizacion, '%d/%m/%Y') AS tracking_fecha_finalizacion_fmt,
                cedis_dest.nombre_agencia AS tracking_cedis_destino_nombre,
                " . implode(",\n                ", $selectCols) . "
             FROM adj_operacion ao
             {$trackingJoin}
             LEFT JOIN av_unidades av
               ON av.id_celula = :celula_rows
              AND av.id_origen = ao.id
              AND av.deleted_at IS NULL
             {$whereSql}
             ORDER BY trk_det.id_detalle DESC, ao.fecha_actualizacion DESC, ao.id DESC
             LIMIT {$limit} OFFSET {$offset}",
            ['celula_rows' => self::CELULA_MOTOS_ADJUDICADAS] + $params
        ) ?: [];

        foreach ($rows as &$row) {
            $row['vin'] = $this->primerValor($row, ['moto_no_serie', 'serie']);
            $row['no_motor'] = $this->primerValor($row, ['moto_no_motor', 'num_motor']);
            $row['placas_unidad'] = $this->primerValor($row, ['moto_placas', 'placas']);
            $row['marca_unidad'] = $this->primerValor($row, ['moto_marca', 'marca']);
            $row['modelo_unidad'] = $this->primerValor($row, ['moto_modelo', 'modelo']);
            $row['estatus_inventario_sugerido'] = $this->estatusInventarioInicialDesdeOperacion($row);
        }
        unset($row);

        return [
            'success' => true,
            'rows' => $rows,
            'total' => $total,
            'limit' => $limit,
            'page' => $page,
            'pages' => $pages,
        ];
    }

    public function crearDesdeMotosAdjudicadas(int $idOperacion, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operacion invalida.'];
        }
        if (!$this->tablasBaseDisponibles()) {
            return ['success' => false, 'message' => 'Faltan tablas base de Inventario. Ejecuta la migracion inicial av_*.'];
        }

        $existente = $this->obtenerUnidadPorOrigen(self::CELULA_MOTOS_ADJUDICADAS, $idOperacion);
        if ($existente) {
            return [
                'success' => true,
                'ya_existe' => true,
                'message' => 'La operacion ya existe en inventario.',
                'unidad' => $existente,
            ];
        }

        $op = $this->obtenerOperacionMotosAdjudicadas($idOperacion);
        if (!$op) {
            return ['success' => false, 'message' => 'No se encontro la operacion de Motos Adjudicadas.'];
        }
        $trackingRecolectado = $this->obtenerTrackingRecolectadoPorCredito((int) ($op['id_credito'] ?? 0));
        if (!$trackingRecolectado) {
            return [
                'success' => false,
                'message' => 'La operacion aun no tiene una recoleccion confirmada en Tracking. Solo se migran motos recolectadas.',
            ];
        }

        $ahora = $this->fechaHoraCdmx();
        $estatusInicial = $this->estatusInventarioInicialDesdeOperacion($op);
        $idUbicacion = $this->obtenerUbicacionSinAsignarId();
        $folio = $this->generarFolioUnidad();
        $unidad = [
            'folio_unidad' => $folio,
            'id_celula' => self::CELULA_MOTOS_ADJUDICADAS,
            'id_origen' => $idOperacion,
            'id_credito' => $this->intONull($op['id_credito'] ?? null),
            'vin' => $this->normalizarAlfanumerico($this->primerValor($op, ['moto_no_serie', 'serie']), 17),
            'no_motor' => $this->normalizarAlfanumerico($this->primerValor($op, ['moto_no_motor', 'num_motor']), 24),
            'placas' => $this->normalizarAlfanumerico($this->primerValor($op, ['moto_placas', 'placas']), 20),
            'marca' => $this->normalizarTexto($this->primerValor($op, ['moto_marca', 'marca']), 100),
            'modelo' => $this->normalizarTexto($this->primerValor($op, ['moto_modelo', 'modelo']), 100),
            'anio' => $this->intONull($this->primerValor($op, ['moto_anio'])),
            'color' => $this->normalizarTexto($this->primerValor($op, ['moto_color']), 50),
            'kilometraje' => $this->intONull($this->primerValor($op, ['kilometraje'])),
            'tipo_unidad' => 'moto',
            'categoria' => null,
            'cilindraje' => null,
            'estatus_inventario' => $estatusInicial,
            'id_ubicacion_actual' => $idUbicacion,
            'fecha_ingreso_virtual' => $ahora,
            'creado_por' => $idUsuario > 0 ? $idUsuario : null,
            'actualizado_por' => $idUsuario > 0 ? $idUsuario : null,
            'fecha_alta' => $ahora,
            'fecha_actualizacion' => $ahora,
        ];

        try {
            $this->db->beginTransaction();
            $cols = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($unidad)));
            $ph = implode(', ', array_map(fn($k) => ":{$k}", array_keys($unidad)));
            $this->db->CRUD("INSERT INTO av_unidades ({$cols}) VALUES ({$ph})", $unidad);
            $idUnidad = $this->db->lastInsertId();
            if ($idUnidad <= 0) {
                throw new \RuntimeException('No se pudo generar la unidad.');
            }

            $this->registrarMovimiento(
                $idUnidad,
                'ingreso_virtual',
                null,
                $estatusInicial,
                null,
                $idUbicacion,
                'Creada desde Motos Adjudicadas con recoleccion confirmada en Tracking.',
                $idUsuario,
                $nombreUsuario,
                $ahora
            );
            $this->registrarBitacora(
                $idUnidad,
                'Inventario Motos Adjudicadas',
                'UNIDAD CREADA DESDE MOTOS ADJUDICADAS',
                'Operacion #' . $idOperacion,
                [
                    'id_operacion' => $idOperacion,
                    'id_credito' => $op['id_credito'] ?? null,
                    'estatus_origen' => $op['estatus'] ?? null,
                    'recepcion_confirmada_at' => $op['recepcion_confirmada_at'] ?? null,
                    'fecha_llegada_almacen' => $op['fecha_llegada_almacen'] ?? null,
                    'tracking_recoleccion' => $trackingRecolectado,
                ],
                $idUsuario,
                $nombreUsuario,
                $ahora
            );
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }

            $existente = $this->obtenerUnidadPorOrigen(self::CELULA_MOTOS_ADJUDICADAS, $idOperacion);
            if ($existente) {
                return [
                    'success' => true,
                    'ya_existe' => true,
                    'message' => 'La operacion ya existe en inventario.',
                    'unidad' => $existente,
                ];
            }

            return ['success' => false, 'message' => 'No se pudo crear la unidad en inventario.', 'error' => $e->getMessage()];
        }

        $creada = $this->obtenerUnidadPorId($idUnidad);

        return [
            'success' => true,
            'ya_existe' => false,
            'message' => 'Unidad creada en inventario.',
            'unidad' => $creada,
        ];
    }

    public function generarFolioUnidad(): string
    {
        $prefijo = 'AV-' . date('Ymd') . '-';
        $row = $this->db->queryOne(
            "SELECT folio_unidad
             FROM av_unidades
             WHERE folio_unidad LIKE :prefijo
             ORDER BY folio_unidad DESC
             LIMIT 1",
            ['prefijo' => $prefijo . '%']
        );
        $ultimo = 0;
        if ($row && !empty($row['folio_unidad'])) {
            $partes = explode('-', (string) $row['folio_unidad']);
            $ultimo = (int) end($partes);
        }

        return $prefijo . str_pad((string) ($ultimo + 1), 5, '0', STR_PAD_LEFT);
    }

    private function obtenerUnidadPorOrigen(int $idCelula, int $idOrigen): ?array
    {
        if ($idCelula <= 0 || $idOrigen <= 0 || !$this->tablaExiste('av_unidades')) {
            return null;
        }

        $row = $this->db->queryOne(
            "SELECT id_unidad
             FROM av_unidades
             WHERE id_celula = :celula
               AND id_origen = :origen
               AND deleted_at IS NULL
             LIMIT 1",
            ['celula' => $idCelula, 'origen' => $idOrigen]
        );
        if (!$row) {
            return null;
        }

        return $this->obtenerUnidadPorId((int) $row['id_unidad']);
    }

    private function obtenerUnidadPorId(int $idUnidad): ?array
    {
        if ($idUnidad <= 0 || !$this->tablaExiste('av_unidades')) {
            return null;
        }

        $row = $this->db->queryOne(
            "SELECT
                u.*,
                ub.nombre_ubicacion,
                ub.tipo_ubicacion
             FROM av_unidades u
             LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
             WHERE u.id_unidad = :id
               AND u.deleted_at IS NULL
             LIMIT 1",
            ['id' => $idUnidad]
        );
        if (!$row) {
            return null;
        }

        $celulas = $this->obtenerCelulas();
        $idCelula = (int) ($row['id_celula'] ?? 0);
        $row['nombre_celula'] = $celulas[$idCelula] ?? ('Celula ' . $idCelula);

        return $row;
    }

    private function trackingRecoleccionDisponible(): bool
    {
        return $this->tablaExiste('asigna_horas_tracking')
            && $this->tablaExiste('asigna_horas_tracking_detalle')
            && $this->tablaExiste('agencias_tracking');
    }

    private function sqlInConstantes(array $valores): string
    {
        return implode(', ', array_map(
            static fn($valor) => "'" . str_replace("'", "''", strtolower((string) $valor)) . "'",
            $valores
        ));
    }

    private function trackingRecolectadaSqlIn(): string
    {
        return $this->sqlInConstantes(self::TRACKING_ESTATUS_RECOLECTADA);
    }

    private function trackingRutasNoInventarioSqlIn(): string
    {
        return $this->sqlInConstantes(self::TRACKING_RUTAS_NO_INVENTARIO);
    }

    private function joinTrackingRecolectadasSql(string $aliasOperacion): string
    {
        $aliasOperacion = preg_replace('/[^A-Za-z0-9_]/', '', $aliasOperacion) ?: 'ao';
        $estatusRecolectada = $this->trackingRecolectadaSqlIn();
        $rutasNoInventario = $this->trackingRutasNoInventarioSqlIn();

        return <<<SQL
        INNER JOIN (
            SELECT atd_idx.id_credito, MAX(atd_idx.id_detalle) AS id_detalle
            FROM asigna_horas_tracking_detalle atd_idx
            INNER JOIN asigna_horas_tracking atr_idx ON atr_idx.id_ruta = atd_idx.id_ruta
            WHERE atd_idx.id_credito IS NOT NULL
              AND LOWER(TRIM(COALESCE(atd_idx.estatus_recoleccion, ''))) IN ({$estatusRecolectada})
              AND LOWER(TRIM(COALESCE(atr_idx.estatus_ruta, ''))) NOT IN ({$rutasNoInventario})
            GROUP BY atd_idx.id_credito
        ) trk_idx ON trk_idx.id_credito = {$aliasOperacion}.id_credito
        INNER JOIN asigna_horas_tracking_detalle trk_det ON trk_det.id_detalle = trk_idx.id_detalle
        INNER JOIN asigna_horas_tracking trk_ruta ON trk_ruta.id_ruta = trk_det.id_ruta
        LEFT JOIN agencias_tracking cedis_dest ON cedis_dest.id_agencia = trk_ruta.id_cedis_destino
        SQL;
    }

    private function obtenerTrackingRecolectadoPorCredito(int $idCredito): ?array
    {
        if ($idCredito <= 0 || !$this->trackingRecoleccionDisponible()) {
            return null;
        }

        $estatusRecolectada = $this->trackingRecolectadaSqlIn();
        $rutasNoInventario = $this->trackingRutasNoInventarioSqlIn();
        $row = $this->db->queryOne(
            "SELECT
                atd.id_detalle,
                atd.id_credito,
                atd.estatus_recoleccion,
                atr.id_ruta,
                atr.nombre_ruta,
                atr.estatus_ruta,
                atr.id_cedis_destino,
                cedis.nombre_agencia AS cedis_destino_nombre,
                atr.fecha_programada,
                atr.fecha_finalizacion,
                atr.fecha_actualizacion
             FROM asigna_horas_tracking_detalle atd
             INNER JOIN asigna_horas_tracking atr ON atr.id_ruta = atd.id_ruta
             LEFT JOIN agencias_tracking cedis ON cedis.id_agencia = atr.id_cedis_destino
             WHERE atd.id_credito = :id_credito
               AND LOWER(TRIM(COALESCE(atd.estatus_recoleccion, ''))) IN ({$estatusRecolectada})
               AND LOWER(TRIM(COALESCE(atr.estatus_ruta, ''))) NOT IN ({$rutasNoInventario})
             ORDER BY atd.id_detalle DESC
             LIMIT 1",
            ['id_credito' => $idCredito]
        );

        return $row ?: null;
    }

    private function obtenerOperacionMotosAdjudicadas(int $idOperacion): ?array
    {
        $cols = [
            $this->adjOperacionSelectColumnaONull('moto_marca', 'o'),
            $this->adjOperacionSelectColumnaONull('moto_modelo', 'o'),
            $this->adjOperacionSelectColumnaONull('moto_anio', 'o'),
            $this->adjOperacionSelectColumnaONull('moto_color', 'o'),
            $this->adjOperacionSelectColumnaONull('moto_no_serie', 'o'),
            $this->adjOperacionSelectColumnaONull('moto_no_motor', 'o'),
            $this->adjOperacionSelectColumnaONull('moto_placas', 'o'),
            $this->adjOperacionSelectColumnaONull('marca', 'o'),
            $this->adjOperacionSelectColumnaONull('modelo', 'o'),
            $this->adjOperacionSelectColumnaONull('serie', 'o'),
            $this->adjOperacionSelectColumnaONull('num_motor', 'o'),
            $this->adjOperacionSelectColumnaONull('placas', 'o'),
            $this->adjOperacionSelectColumnaONull('kilometraje', 'o'),
            $this->adjOperacionSelectColumnaONull('fecha_llegada_almacen', 'o'),
            $this->adjOperacionSelectColumnaONull('recepcion_confirmada_at', 'o'),
        ];

        return $this->db->queryOne(
            "SELECT
                o.id,
                o.id_credito,
                o.nombre_cliente,
                o.estatus,
                " . implode(",\n                ", $cols) . "
             FROM adj_operacion o
             WHERE o.id = :id
             LIMIT 1",
            ['id' => $idOperacion]
        );
    }

    private function registrarMovimiento(
        int $idUnidad,
        string $tipoMovimiento,
        ?string $estatusAnterior,
        ?string $estatusNuevo,
        ?int $idUbicacionOrigen,
        ?int $idUbicacionDestino,
        string $comentario,
        int $idUsuario,
        string $nombreUsuario,
        string $fecha
    ): int {
        $this->db->CRUD(
            "INSERT INTO av_movimientos (
                id_unidad,
                tipo_movimiento,
                estatus_anterior,
                estatus_nuevo,
                id_ubicacion_origen,
                id_ubicacion_destino,
                comentario,
                id_usuario,
                nombre_usuario,
                fecha_movimiento
             ) VALUES (
                :id_unidad,
                :tipo_movimiento,
                :estatus_anterior,
                :estatus_nuevo,
                :id_ubicacion_origen,
                :id_ubicacion_destino,
                :comentario,
                :id_usuario,
                :nombre_usuario,
                :fecha_movimiento
             )",
            [
                'id_unidad' => $idUnidad,
                'tipo_movimiento' => $tipoMovimiento,
                'estatus_anterior' => $estatusAnterior,
                'estatus_nuevo' => $estatusNuevo,
                'id_ubicacion_origen' => $idUbicacionOrigen,
                'id_ubicacion_destino' => $idUbicacionDestino,
                'comentario' => $comentario,
                'id_usuario' => $idUsuario > 0 ? $idUsuario : null,
                'nombre_usuario' => $nombreUsuario !== '' ? $nombreUsuario : null,
                'fecha_movimiento' => $fecha,
            ]
        );

        return $this->db->lastInsertId();
    }

    private function registrarBitacora(
        int $idUnidad,
        string $modulo,
        string $accion,
        string $detalle,
        array $payload,
        int $idUsuario,
        string $nombreUsuario,
        string $fecha
    ): void {
        $this->db->CRUD(
            "INSERT INTO av_bitacora (
                id_unidad,
                modulo,
                accion,
                detalle,
                payload_json,
                id_usuario,
                nombre_usuario,
                fecha_alta
             ) VALUES (
                :id_unidad,
                :modulo,
                :accion,
                :detalle,
                :payload_json,
                :id_usuario,
                :nombre_usuario,
                :fecha_alta
             )",
            [
                'id_unidad' => $idUnidad,
                'modulo' => $modulo,
                'accion' => $accion,
                'detalle' => $detalle,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'id_usuario' => $idUsuario > 0 ? $idUsuario : null,
                'nombre_usuario' => $nombreUsuario !== '' ? $nombreUsuario : null,
                'fecha_alta' => $fecha,
            ]
        );
    }

    private function obtenerUbicacionSinAsignarId(): ?int
    {
        if (!$this->tablaExiste('av_ubicaciones')) {
            return null;
        }

        try {
            $this->db->CRUD(
                "INSERT INTO av_ubicaciones (clave_ubicacion, nombre_ubicacion, tipo_ubicacion)
                 VALUES ('SIN_ASIGNAR', 'Sin asignar', 'otro')
                 ON DUPLICATE KEY UPDATE nombre_ubicacion = VALUES(nombre_ubicacion)"
            );
        } catch (\Throwable $e) {}

        $row = $this->db->queryOne(
            "SELECT id_ubicacion
             FROM av_ubicaciones
             WHERE clave_ubicacion = 'SIN_ASIGNAR'
             LIMIT 1"
        );

        return $row ? (int) $row['id_ubicacion'] : null;
    }

    private function obtenerUbicacionPorId(int $idUbicacion): ?array
    {
        if ($idUbicacion <= 0 || !$this->tablaExiste('av_ubicaciones')) {
            return null;
        }

        $row = $this->db->queryOne(
            "SELECT id_ubicacion, clave_ubicacion, nombre_ubicacion, tipo_ubicacion, estado, municipio
             FROM av_ubicaciones
             WHERE id_ubicacion = :id
               AND activo = 1
             LIMIT 1",
            ['id' => $idUbicacion]
        );

        return $row ?: null;
    }

    private function registrarRecepcionEstructurada(
        int $idUnidad,
        int $idMovimiento,
        int $idUbicacion,
        string $vin,
        ?string $noMotor,
        ?string $placas,
        ?int $kilometraje,
        array $checklist,
        string $resultado,
        ?string $observaciones,
        int $idUsuario,
        string $nombreUsuario,
        string $fecha
    ): void {
        if (!$this->tablaExiste('av_recepciones')) {
            return;
        }

        $this->db->CRUD(
            "INSERT INTO av_recepciones (
                id_unidad,
                id_movimiento,
                id_ubicacion_recepcion,
                vin_capturado,
                no_motor_capturado,
                placas_capturadas,
                kilometraje_capturado,
                vin_coincide,
                evidencia_4_angulos,
                evidencia_vin,
                documentos_completos,
                arranque_motor,
                sin_danos_mayores,
                resultado,
                observaciones,
                id_usuario,
                nombre_usuario,
                fecha_recepcion
             ) VALUES (
                :id_unidad,
                :id_movimiento,
                :id_ubicacion_recepcion,
                :vin_capturado,
                :no_motor_capturado,
                :placas_capturadas,
                :kilometraje_capturado,
                :vin_coincide,
                :evidencia_4_angulos,
                :evidencia_vin,
                :documentos_completos,
                :arranque_motor,
                :sin_danos_mayores,
                :resultado,
                :observaciones,
                :id_usuario,
                :nombre_usuario,
                :fecha_recepcion
             )",
            [
                'id_unidad' => $idUnidad,
                'id_movimiento' => $idMovimiento > 0 ? $idMovimiento : null,
                'id_ubicacion_recepcion' => $idUbicacion,
                'vin_capturado' => $vin,
                'no_motor_capturado' => $noMotor,
                'placas_capturadas' => $placas,
                'kilometraje_capturado' => $kilometraje,
                'vin_coincide' => !empty($checklist['vin_coincide']) ? 1 : 0,
                'evidencia_4_angulos' => !empty($checklist['evidencia_4_angulos']) ? 1 : 0,
                'evidencia_vin' => !empty($checklist['evidencia_vin']) ? 1 : 0,
                'documentos_completos' => !empty($checklist['documentos_completos']) ? 1 : 0,
                'arranque_motor' => !empty($checklist['arranque_motor']) ? 1 : 0,
                'sin_danos_mayores' => !empty($checklist['sin_danos_mayores']) ? 1 : 0,
                'resultado' => $resultado === 'recibida' ? 'recibida' : 'incidencia',
                'observaciones' => $observaciones,
                'id_usuario' => $idUsuario > 0 ? $idUsuario : null,
                'nombre_usuario' => $nombreUsuario !== '' ? $nombreUsuario : null,
                'fecha_recepcion' => $fecha,
            ]
        );
    }

    private function sincronizarRecepcionOperacionOrigen(
        array $unidad,
        array $ubicacion,
        string $observaciones,
        string $resultado,
        string $fecha
    ): void {
        if ((int) ($unidad['id_celula'] ?? 0) !== self::CELULA_MOTOS_ADJUDICADAS) {
            return;
        }
        $idOperacion = (int) ($unidad['id_origen'] ?? 0);
        if ($idOperacion <= 0 || !$this->tablaExiste('adj_operacion')) {
            return;
        }

        $sets = [];
        $params = ['id' => $idOperacion];
        if ($this->adjOperacionTieneColumna('fecha_llegada_almacen')) {
            $sets[] = 'fecha_llegada_almacen = COALESCE(fecha_llegada_almacen, :fecha_llegada_almacen)';
            $params['fecha_llegada_almacen'] = $fecha;
        }
        if ($resultado === 'recibida' && $this->adjOperacionTieneColumna('recepcion_confirmada_at')) {
            $sets[] = 'recepcion_confirmada_at = COALESCE(recepcion_confirmada_at, :recepcion_confirmada_at)';
            $params['recepcion_confirmada_at'] = $fecha;
        }
        if ($this->adjOperacionTieneColumna('recepcion_ubicacion')) {
            $sets[] = 'recepcion_ubicacion = :recepcion_ubicacion';
            $params['recepcion_ubicacion'] = (string) ($ubicacion['nombre_ubicacion'] ?? '');
        }
        if ($this->adjOperacionTieneColumna('recepcion_observaciones')) {
            $sets[] = 'recepcion_observaciones = :recepcion_observaciones';
            $params['recepcion_observaciones'] = $observaciones !== '' ? $observaciones : null;
        }
        if ($this->adjOperacionTieneColumna('fecha_actualizacion')) {
            $sets[] = 'fecha_actualizacion = :fecha_actualizacion';
            $params['fecha_actualizacion'] = $fecha;
        }

        if (empty($sets)) {
            return;
        }

        $this->db->CRUD(
            'UPDATE adj_operacion SET ' . implode(', ', $sets) . ' WHERE id = :id',
            $params
        );
    }

    private function evidenciasActualesPorUnidad(int $idUnidad): array
    {
        if ($idUnidad <= 0 || !$this->tablaExiste('av_evidencias')) {
            return [];
        }

        $rows = $this->db->queryAll(
            "SELECT e.*
             FROM av_evidencias e
             INNER JOIN (
                SELECT slot, MAX(id_evidencia) AS id_evidencia
                FROM av_evidencias
                WHERE id_unidad = :id
                  AND etapa = :etapa
                  AND estatus NOT IN ('reemplazado', 'eliminado')
                GROUP BY slot
             ) ult ON ult.id_evidencia = e.id_evidencia
             ORDER BY e.fecha_alta DESC, e.id_evidencia DESC",
            ['id' => $idUnidad, 'etapa' => self::ETAPA_EVIDENCIAS_INGRESO]
        ) ?: [];

        $out = [];
        foreach ($rows as $row) {
            $slot = (string) ($row['slot'] ?? '');
            if ($slot === '') {
                continue;
            }
            $row['titulo_slot'] = self::SLOTS_EVIDENCIAS_INGRESO[$slot] ?? ($row['titulo'] ?? $slot);
            $row['url_publica'] = $this->urlPublicaEvidencia((string) ($row['url'] ?? ''));
            $out[$slot] = $row;
        }

        return $out;
    }

    private function codigoVerificacionActivo(int $idUnidad): ?array
    {
        if ($idUnidad <= 0 || !$this->tablaExiste('av_codigos_verificacion')) {
            return null;
        }

        $row = $this->db->queryOne(
            "SELECT *
             FROM av_codigos_verificacion
             WHERE id_unidad = :id
               AND tipo_codigo = 'ingreso_almacen'
               AND estatus = 'generado'
               AND (fecha_expiracion IS NULL OR fecha_expiracion >= :ahora)
             ORDER BY id_codigo DESC
             LIMIT 1",
            ['id' => $idUnidad, 'ahora' => $this->fechaHoraCdmx()]
        );

        return $row ?: null;
    }

    private function incrementarIntentosCodigo(int $idUnidad): void
    {
        if ($idUnidad <= 0 || !$this->tablaExiste('av_codigos_verificacion')) {
            return;
        }

        $this->db->CRUD(
            "UPDATE av_codigos_verificacion
             SET intentos = intentos + 1
             WHERE id_unidad = :id
               AND tipo_codigo = 'ingreso_almacen'
               AND estatus = 'generado'
             ORDER BY id_codigo DESC
             LIMIT 1",
            ['id' => $idUnidad]
        );
    }

    private function marcarCodigoVerificacionUsado(int $idCodigo, int $idUsuario, string $fecha): void
    {
        if ($idCodigo <= 0 || !$this->tablaExiste('av_codigos_verificacion')) {
            return;
        }

        $this->db->CRUD(
            "UPDATE av_codigos_verificacion
             SET estatus = 'usado',
                 usado_por = :usado_por,
                 fecha_uso = :fecha_uso
             WHERE id_codigo = :id
               AND estatus = 'generado'",
            [
                'usado_por' => $idUsuario > 0 ? $idUsuario : null,
                'fecha_uso' => $fecha,
                'id' => $idCodigo,
            ]
        );
    }

    private function generarCodigoVerificacionUnico(): string
    {
        for ($i = 0; $i < 8; $i++) {
            $codigo = 'AV-' . strtoupper(bin2hex(random_bytes(3)));
            $existe = $this->db->queryOne(
                'SELECT id_codigo FROM av_codigos_verificacion WHERE codigo = :codigo LIMIT 1',
                ['codigo' => $codigo]
            );
            if (!$existe) {
                return $codigo;
            }
        }

        return 'AV-' . strtoupper(bin2hex(random_bytes(5)));
    }

    private function registrarEvidenciaUnidad(
        int $idUnidad,
        string $slot,
        array $info,
        string $estatus,
        int $idUsuario,
        string $nombreUsuario,
        string $fecha
    ): void {
        if ($idUnidad <= 0 || !isset(self::SLOTS_EVIDENCIAS_INGRESO[$slot])) {
            return;
        }

        $this->db->CRUD(
            "UPDATE av_evidencias
             SET estatus = 'reemplazado'
             WHERE id_unidad = :id_unidad
               AND etapa = :etapa
               AND slot = :slot
               AND estatus NOT IN ('reemplazado', 'eliminado')",
            [
                'id_unidad' => $idUnidad,
                'etapa' => self::ETAPA_EVIDENCIAS_INGRESO,
                'slot' => $slot,
            ]
        );

        $this->db->CRUD(
            "INSERT INTO av_evidencias (
                id_unidad,
                etapa,
                tipo_evidencia,
                slot,
                titulo,
                url,
                mime_type,
                tamano_bytes,
                estatus,
                id_usuario_alta,
                nombre_usuario_alta,
                fecha_alta
             ) VALUES (
                :id_unidad,
                :etapa,
                :tipo_evidencia,
                :slot,
                :titulo,
                :url,
                :mime_type,
                :tamano_bytes,
                :estatus,
                :id_usuario_alta,
                :nombre_usuario_alta,
                :fecha_alta
             )",
            [
                'id_unidad' => $idUnidad,
                'etapa' => self::ETAPA_EVIDENCIAS_INGRESO,
                'tipo_evidencia' => $info['tipo_evidencia'] ?? 'foto',
                'slot' => $slot,
                'titulo' => self::SLOTS_EVIDENCIAS_INGRESO[$slot],
                'url' => (string) ($info['url'] ?? ''),
                'mime_type' => $info['mime_type'] ?? null,
                'tamano_bytes' => isset($info['tamano_bytes']) ? (int) $info['tamano_bytes'] : null,
                'estatus' => in_array($estatus, ['recibido', 'validado', 'rechazado'], true) ? $estatus : 'recibido',
                'id_usuario_alta' => $idUsuario > 0 ? $idUsuario : null,
                'nombre_usuario_alta' => $nombreUsuario !== '' ? $nombreUsuario : null,
                'fecha_alta' => $fecha,
            ]
        );
    }

    private function registrarEvidenciaUnidadEtapa(
        int $idUnidad,
        string $etapa,
        string $slot,
        string $titulo,
        array $info,
        string $estatus,
        int $idUsuario,
        string $nombreUsuario,
        string $fecha
    ): void {
        if ($idUnidad <= 0 || $etapa === '' || $slot === '' || trim((string) ($info['url'] ?? '')) === '') {
            return;
        }

        $this->db->CRUD(
            "UPDATE av_evidencias
             SET estatus = 'reemplazado'
             WHERE id_unidad = :id_unidad
               AND etapa = :etapa
               AND slot = :slot
               AND estatus NOT IN ('reemplazado', 'eliminado')",
            [
                'id_unidad' => $idUnidad,
                'etapa' => $etapa,
                'slot' => $slot,
            ]
        );

        $tipo = (string) ($info['tipo_evidencia'] ?? 'foto');
        if (!in_array($tipo, ['foto', 'video', 'documento', 'firma', 'otro'], true)) {
            $tipo = 'otro';
        }

        $this->db->CRUD(
            "INSERT INTO av_evidencias (
                id_unidad,
                etapa,
                tipo_evidencia,
                slot,
                titulo,
                url,
                mime_type,
                tamano_bytes,
                estatus,
                id_usuario_alta,
                nombre_usuario_alta,
                fecha_alta
             ) VALUES (
                :id_unidad,
                :etapa,
                :tipo_evidencia,
                :slot,
                :titulo,
                :url,
                :mime_type,
                :tamano_bytes,
                :estatus,
                :id_usuario_alta,
                :nombre_usuario_alta,
                :fecha_alta
             )",
            [
                'id_unidad' => $idUnidad,
                'etapa' => $etapa,
                'tipo_evidencia' => $tipo,
                'slot' => $slot,
                'titulo' => $titulo,
                'url' => (string) ($info['url'] ?? ''),
                'mime_type' => $info['mime_type'] ?? null,
                'tamano_bytes' => isset($info['tamano_bytes']) ? (int) $info['tamano_bytes'] : null,
                'estatus' => in_array($estatus, ['recibido', 'validado', 'rechazado'], true) ? $estatus : 'recibido',
                'id_usuario_alta' => $idUsuario > 0 ? $idUsuario : null,
                'nombre_usuario_alta' => $nombreUsuario !== '' ? $nombreUsuario : null,
                'fecha_alta' => $fecha,
            ]
        );
    }

    private function marcarEvidenciasIngresoValidadas(int $idUnidad, string $fecha): void
    {
        if ($idUnidad <= 0 || !$this->tablaExiste('av_evidencias')) {
            return;
        }

        $slotsSql = $this->sqlInConstantes(array_keys(self::SLOTS_EVIDENCIAS_INGRESO));
        $this->db->CRUD(
            "UPDATE av_evidencias e
             INNER JOIN (
                SELECT slot, MAX(id_evidencia) AS id_evidencia
                FROM av_evidencias
                WHERE id_unidad = :id
                  AND etapa = :etapa
                  AND slot IN ({$slotsSql})
                  AND estatus NOT IN ('reemplazado', 'eliminado')
                GROUP BY slot
             ) ult ON ult.id_evidencia = e.id_evidencia
             SET e.estatus = 'validado',
                 e.fecha_alta = e.fecha_alta
             WHERE e.id_unidad = :id2",
            ['id' => $idUnidad, 'id2' => $idUnidad, 'etapa' => self::ETAPA_EVIDENCIAS_INGRESO]
        );
    }

    private function evidenciasIngresoValidas(int $idUnidad): bool
    {
        $actuales = $this->evidenciasActualesPorUnidad($idUnidad);
        foreach (self::SLOTS_EVIDENCIAS_INGRESO as $slot => $_titulo) {
            if (($actuales[$slot]['estatus'] ?? '') !== 'validado') {
                return false;
            }
        }

        return true;
    }

    private function registrarPisoVentaEstructurado(
        int $idUnidad,
        int $idTransicion,
        string $clienteDestino,
        ?string $observaciones,
        int $idUsuario,
        string $nombreUsuario,
        string $fecha
    ): int {
        $this->db->CRUD(
            "INSERT INTO av_piso_venta_envios (
                id_unidad,
                id_transicion,
                cliente_destino,
                estatus_envio,
                observaciones,
                id_usuario,
                nombre_usuario,
                fecha_envio
             ) VALUES (
                :id_unidad,
                :id_transicion,
                :cliente_destino,
                'lista',
                :observaciones,
                :id_usuario,
                :nombre_usuario,
                :fecha_envio
             )",
            [
                'id_unidad' => $idUnidad,
                'id_transicion' => $idTransicion > 0 ? $idTransicion : null,
                'cliente_destino' => $clienteDestino,
                'observaciones' => $observaciones,
                'id_usuario' => $idUsuario > 0 ? $idUsuario : null,
                'nombre_usuario' => $nombreUsuario !== '' ? $nombreUsuario : null,
                'fecha_envio' => $fecha,
            ]
        );

        return $this->db->lastInsertId();
    }

    private function generarFolioTraspaso(string $fecha): string
    {
        $prefijo = 'AVT-' . date('Ymd', strtotime($fecha)) . '-';
        $row = $this->db->queryOne(
            "SELECT folio_traspaso
             FROM av_traspasos
             WHERE folio_traspaso LIKE :prefijo
             ORDER BY folio_traspaso DESC
             LIMIT 1",
            ['prefijo' => $prefijo . '%']
        );
        $ultimo = 0;
        if ($row && !empty($row['folio_traspaso'])) {
            $partes = explode('-', (string) $row['folio_traspaso']);
            $ultimo = (int) end($partes);
        }

        return $prefijo . str_pad((string) ($ultimo + 1), 4, '0', STR_PAD_LEFT);
    }

    private function obtenerTraspasoPorId(int $idTraspaso): ?array
    {
        if ($idTraspaso <= 0 || !$this->tablaExiste('av_traspasos')) {
            return null;
        }

        $row = $this->db->queryOne(
            "SELECT
                t.*,
                u.folio_unidad,
                u.estatus_inventario,
                u.id_ubicacion_actual
             FROM av_traspasos t
             INNER JOIN av_unidades u ON u.id_unidad = t.id_unidad
             WHERE t.id_traspaso = :id
               AND u.deleted_at IS NULL
             LIMIT 1",
            ['id' => $idTraspaso]
        );

        return $row ?: null;
    }

    private function registrarEvidenciasTraspaso(
        int $idUnidad,
        string $etapa,
        array $evidencias,
        int $idUsuario,
        string $nombreUsuario,
        string $fecha
    ): void {
        $titulos = [
            'traspaso_origen' => 'Evidencia origen transportista',
            'traspaso_destino' => 'Evidencia destino VoBo',
        ];
        foreach ($evidencias as $slot => $info) {
            if (!is_array($info)) {
                continue;
            }
            $this->registrarEvidenciaUnidadEtapa(
                $idUnidad,
                $etapa,
                (string) $slot,
                $titulos[$etapa] ?? 'Evidencia traspaso',
                $info,
                'validado',
                $idUsuario,
                $nombreUsuario,
                $fecha
            );
        }
    }

    private function normalizarFechaHoraInput(string $valor): ?string
    {
        $valor = trim(str_replace('T', ' ', $valor));
        if ($valor === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $valor)) {
            $valor .= ':00';
        }

        try {
            $dt = new \DateTime($valor, new \DateTimeZone('America/Mexico_City'));
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function maquinaEstadosKanban(): array
    {
        return [
            self::ESTATUS_PENDIENTE_REVISION => [
                self::ESTATUS_EN_REVISION => [self::EVENTO_ASIGNACION_MECANICO],
            ],
            self::ESTATUS_EN_REVISION => [
                self::ESTATUS_REPARADA => [self::EVENTO_CIERRE_REVISION],
                self::ESTATUS_FUERA_PRESUPUESTO => [self::EVENTO_CIERRE_REVISION],
                self::ESTATUS_IRREPARABLE => [self::EVENTO_CIERRE_REVISION],
            ],
            self::ESTATUS_REPARADA => [
                self::ESTATUS_LISTA_VENTA => [self::EVENTO_ENVIO_PISO_VENTA],
            ],
            self::ESTATUS_LISTA_VENTA => [
                self::ESTATUS_EN_TRASPASO => [self::EVENTO_ORDEN_TRASPASO],
            ],
            self::ESTATUS_EN_TRASPASO => [
                self::ESTATUS_LISTA_VENTA => [self::EVENTO_CIERRE_TRASPASO],
            ],
        ];
    }

    private function aplicarCambioEstatusUnidad(
        array $unidad,
        string $estatusNuevo,
        string $origenEvento,
        int $usuarioId,
        ?string $justificacion,
        array $contexto,
        string $nombreUsuario,
        bool $overrideSupervisorAutorizado,
        string $fecha
    ): array {
        if (!$this->tablaExiste('av_kanban_transiciones')) {
            return ['success' => false, 'message' => 'Falta la tabla av_kanban_transiciones. Ejecuta la migracion de Kanban.'];
        }

        $idUnidad = (int) ($unidad['id_unidad'] ?? 0);
        $estatusAnterior = (string) ($unidad['estatus_inventario'] ?? '');
        $estatusNuevo = strtolower(trim($estatusNuevo));
        $origenEvento = strtolower(trim($origenEvento));
        $justificacion = $this->normalizarTexto((string) ($justificacion ?? ''), 2000);
        $validacion = $this->validarTransicionKanban(
            $estatusAnterior,
            $estatusNuevo,
            $origenEvento,
            $justificacion,
            $overrideSupervisorAutorizado
        );
        if (empty($validacion['success'])) {
            return $validacion;
        }

        $esOverride = $origenEvento === self::ORIGEN_OVERRIDE_SUPERVISOR;
        $origenRegistro = $esOverride ? self::ORIGEN_OVERRIDE_SUPERVISOR : 'evento_negocio';
        $comentario = $this->comentarioTransicionKanban($estatusAnterior, $estatusNuevo, $origenEvento, $justificacion);

        $this->db->CRUD(
            "UPDATE av_unidades
             SET estatus_inventario = :estatus_nuevo,
                 actualizado_por = :actualizado_por,
                 fecha_actualizacion = :fecha
             WHERE id_unidad = :id
               AND deleted_at IS NULL",
            [
                'estatus_nuevo' => $estatusNuevo,
                'actualizado_por' => $usuarioId > 0 ? $usuarioId : null,
                'fecha' => $fecha,
                'id' => $idUnidad,
            ]
        );

        $idMovimiento = $this->registrarMovimiento(
            $idUnidad,
            $esOverride ? 'kanban_override_supervisor' : $origenEvento,
            $estatusAnterior,
            $estatusNuevo,
            $this->intONull($unidad['id_ubicacion_actual'] ?? null),
            $this->intONull($unidad['id_ubicacion_actual'] ?? null),
            $comentario,
            $usuarioId,
            $nombreUsuario,
            $fecha
        );

        $payload = [
            'origen_evento' => $origenRegistro,
            'evento_negocio' => $esOverride ? null : $origenEvento,
            'estatus_anterior' => $estatusAnterior,
            'estatus_nuevo' => $estatusNuevo,
            'justificacion' => $justificacion,
            'contexto' => $contexto,
        ];
        $this->registrarBitacora(
            $idUnidad,
            'Kanban Operativo',
            $esOverride ? 'OVERRIDE SUPERVISOR' : 'CAMBIO ESTATUS',
            $comentario,
            $payload + ['id_movimiento' => $idMovimiento],
            $usuarioId,
            $nombreUsuario,
            $fecha
        );

        $idTransicion = $this->registrarTransicionKanban(
            $idUnidad,
            $estatusAnterior,
            $estatusNuevo,
            $origenRegistro,
            $esOverride ? null : $origenEvento,
            $justificacion,
            $payload,
            $idMovimiento,
            $usuarioId,
            $nombreUsuario,
            $fecha
        );

        if ($esOverride) {
            $this->registrarNotificacionKanbanOverride($idTransicion, $idUnidad, $estatusAnterior, $estatusNuevo, $justificacion, $fecha);
        }

        return [
            'success' => true,
            'message' => 'Estatus actualizado correctamente.',
            'estatus_anterior' => $estatusAnterior,
            'estatus_nuevo' => $estatusNuevo,
            'id_movimiento' => $idMovimiento,
            'id_transicion' => $idTransicion,
        ];
    }

    private function validarTransicionKanban(
        string $estatusAnterior,
        string $estatusNuevo,
        string $origenEvento,
        ?string $justificacion,
        bool $overrideSupervisorAutorizado
    ): array {
        $estatusValidos = array_keys($this->columnasKanbanOperativo());
        if (!in_array($estatusNuevo, $estatusValidos, true)) {
            return ['success' => false, 'message' => 'Estatus destino no permitido para Kanban.'];
        }
        if ($estatusAnterior === $estatusNuevo) {
            return ['success' => false, 'message' => 'La unidad ya se encuentra en ese estatus.'];
        }

        if (in_array($estatusNuevo, [self::ESTATUS_FUERA_PRESUPUESTO, self::ESTATUS_IRREPARABLE], true)) {
            if ($justificacion === null || mb_strlen($justificacion) < 20) {
                return ['success' => false, 'message' => 'Fuera de presupuesto e Irreparable requieren justificacion minima de 20 caracteres.'];
            }
        }

        if ($origenEvento === self::ORIGEN_OVERRIDE_SUPERVISOR) {
            if (!$overrideSupervisorAutorizado) {
                return ['success' => false, 'message' => 'No tienes permiso para hacer override de supervisor.'];
            }
            if ($justificacion === null || trim($justificacion) === '') {
                return ['success' => false, 'message' => 'El override de supervisor requiere justificacion.'];
            }

            return ['success' => true];
        }

        $maquina = $this->maquinaEstadosKanban();
        $eventosPermitidos = $maquina[$estatusAnterior][$estatusNuevo] ?? [];
        if (!in_array($origenEvento, $eventosPermitidos, true)) {
            return [
                'success' => false,
                'message' => 'Transicion no permitida por la maquina de estados del Kanban.',
            ];
        }

        return ['success' => true];
    }

    private function comentarioTransicionKanban(string $estatusAnterior, string $estatusNuevo, string $origenEvento, ?string $justificacion): string
    {
        $labels = array_column($this->columnasKanbanOperativo(), 'titulo', 'estatus');
        $comentario = 'Cambio Kanban: ' . ($labels[$estatusAnterior] ?? $estatusAnterior)
            . ' -> ' . ($labels[$estatusNuevo] ?? $estatusNuevo)
            . ' por ' . str_replace('_', ' ', $origenEvento) . '.';
        if ($justificacion) {
            $comentario .= ' Justificacion: ' . $justificacion;
        }

        return $comentario;
    }

    private function registrarTransicionKanban(
        int $idUnidad,
        string $estatusAnterior,
        string $estatusNuevo,
        string $origenEvento,
        ?string $eventoNegocio,
        ?string $justificacion,
        array $payload,
        int $idMovimiento,
        int $idUsuario,
        string $nombreUsuario,
        string $fecha
    ): int {
        $this->db->CRUD(
            "INSERT INTO av_kanban_transiciones (
                id_unidad,
                estatus_anterior,
                estatus_nuevo,
                origen_evento,
                evento_negocio,
                justificacion,
                payload_json,
                id_movimiento,
                usuario_id,
                nombre_usuario,
                fecha_hora
             ) VALUES (
                :id_unidad,
                :estatus_anterior,
                :estatus_nuevo,
                :origen_evento,
                :evento_negocio,
                :justificacion,
                :payload_json,
                :id_movimiento,
                :usuario_id,
                :nombre_usuario,
                :fecha_hora
             )",
            [
                'id_unidad' => $idUnidad,
                'estatus_anterior' => $estatusAnterior,
                'estatus_nuevo' => $estatusNuevo,
                'origen_evento' => $origenEvento,
                'evento_negocio' => $eventoNegocio,
                'justificacion' => $justificacion,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'id_movimiento' => $idMovimiento > 0 ? $idMovimiento : null,
                'usuario_id' => $idUsuario > 0 ? $idUsuario : null,
                'nombre_usuario' => $nombreUsuario !== '' ? $nombreUsuario : null,
                'fecha_hora' => $fecha,
            ]
        );

        return $this->db->lastInsertId();
    }

    private function registrarNotificacionKanbanOverride(
        int $idTransicion,
        int $idUnidad,
        string $estatusAnterior,
        string $estatusNuevo,
        ?string $justificacion,
        string $fecha
    ): void {
        if ($idTransicion <= 0 || !$this->tablaExiste('av_kanban_notificaciones')) {
            return;
        }

        $labels = array_column($this->columnasKanbanOperativo(), 'titulo', 'estatus');
        $mensaje = 'Override de supervisor en unidad #' . $idUnidad . ': '
            . ($labels[$estatusAnterior] ?? $estatusAnterior)
            . ' -> ' . ($labels[$estatusNuevo] ?? $estatusNuevo) . '.';
        if ($justificacion) {
            $mensaje .= ' Justificacion: ' . $justificacion;
        }

        $this->db->CRUD(
            "INSERT INTO av_kanban_notificaciones (
                id_transicion,
                id_unidad,
                area_responsable,
                mensaje,
                estatus,
                fecha_creacion
             ) VALUES (
                :id_transicion,
                :id_unidad,
                'Almacen',
                :mensaje,
                'pendiente',
                :fecha_creacion
             )",
            [
                'id_transicion' => $idTransicion,
                'id_unidad' => $idUnidad,
                'mensaje' => $mensaje,
                'fecha_creacion' => $fecha,
            ]
        );
    }

    private function columnasKanbanOperativo(): array
    {
        return [
            self::ESTATUS_PENDIENTE_REVISION => [
                'estatus' => self::ESTATUS_PENDIENTE_REVISION,
                'titulo' => 'Pendiente revision',
                'color' => 'purple',
                'icono' => 'fa-clock',
            ],
            self::ESTATUS_EN_REVISION => [
                'estatus' => self::ESTATUS_EN_REVISION,
                'titulo' => 'En revision',
                'color' => 'blue',
                'icono' => 'fa-screwdriver-wrench',
            ],
            self::ESTATUS_REPARADA => [
                'estatus' => self::ESTATUS_REPARADA,
                'titulo' => 'Reparada',
                'color' => 'green',
                'icono' => 'fa-circle-check',
            ],
            self::ESTATUS_FUERA_PRESUPUESTO => [
                'estatus' => self::ESTATUS_FUERA_PRESUPUESTO,
                'titulo' => 'Fuera presupuesto',
                'color' => 'red',
                'icono' => 'fa-file-invoice-dollar',
            ],
            self::ESTATUS_IRREPARABLE => [
                'estatus' => self::ESTATUS_IRREPARABLE,
                'titulo' => 'Irreparable',
                'color' => 'gray',
                'icono' => 'fa-ban',
            ],
            self::ESTATUS_LISTA_VENTA => [
                'estatus' => self::ESTATUS_LISTA_VENTA,
                'titulo' => 'Lista para venta',
                'color' => 'teal',
                'icono' => 'fa-store',
            ],
            self::ESTATUS_EN_TRASPASO => [
                'estatus' => self::ESTATUS_EN_TRASPASO,
                'titulo' => 'En traspaso',
                'color' => 'orange',
                'icono' => 'fa-right-left',
            ],
        ];
    }

    private function whereKanbanOperativo(array $filtros, array &$params): array
    {
        $where = ['u.deleted_at IS NULL'];

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $where[] = "(
                u.folio_unidad LIKE :q
                OR u.vin LIKE :q
                OR u.no_motor LIKE :q
                OR u.placas LIKE :q
                OR u.marca LIKE :q
                OR u.modelo LIKE :q
                OR u.color LIKE :q
                OR CAST(u.id_unidad AS CHAR) LIKE :q
                OR CAST(u.id_origen AS CHAR) LIKE :q
                OR CAST(u.id_credito AS CHAR) LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        $idCelula = (int) ($filtros['id_celula'] ?? 0);
        if ($idCelula > 0) {
            $where[] = 'u.id_celula = :id_celula';
            $params['id_celula'] = $idCelula;
        }

        $idUbicacion = (int) ($filtros['id_ubicacion'] ?? 0);
        if ($idUbicacion > 0) {
            $where[] = 'u.id_ubicacion_actual = :id_ubicacion';
            $params['id_ubicacion'] = $idUbicacion;
        }

        $tipoUnidad = trim((string) ($filtros['tipo_unidad'] ?? ''));
        if ($tipoUnidad !== '') {
            $where[] = 'u.tipo_unidad = :tipo_unidad';
            $params['tipo_unidad'] = mb_substr($tipoUnidad, 0, 50);
        }

        return $where;
    }

    private function obtenerTiposUnidadKanban(): array
    {
        if (!$this->tablaExiste('av_unidades')) {
            return [];
        }

        $estatusSql = $this->sqlInConstantes(array_keys($this->columnasKanbanOperativo()));
        $rows = $this->db->queryAll(
            "SELECT DISTINCT tipo_unidad
             FROM av_unidades
             WHERE deleted_at IS NULL
               AND estatus_inventario IN ({$estatusSql})
               AND tipo_unidad IS NOT NULL
               AND TRIM(tipo_unidad) <> ''
             ORDER BY tipo_unidad ASC"
        ) ?: [];

        return array_values(array_map(
            static fn($row) => (string) ($row['tipo_unidad'] ?? ''),
            $rows
        ));
    }

    private function alertaSlaKanban(string $estatus, int $diasAlmacen): array
    {
        $umbralAtencion = in_array($estatus, [self::ESTATUS_PENDIENTE_REVISION, self::ESTATUS_EN_REVISION], true) ? 2 : 7;
        $umbralVencido = in_array($estatus, [self::ESTATUS_PENDIENTE_REVISION, self::ESTATUS_EN_REVISION], true) ? 4 : 15;
        if ($diasAlmacen >= $umbralVencido) {
            return ['nivel' => 'vencido', 'label' => 'SLA vencido'];
        }
        if ($diasAlmacen >= $umbralAtencion) {
            return ['nivel' => 'atencion', 'label' => 'SLA atencion'];
        }

        return ['nivel' => 'normal', 'label' => 'SLA normal'];
    }

    private function obtenerRevisionActiva(int $idUnidad): ?array
    {
        if ($idUnidad <= 0 || !$this->tablaExiste('av_revisiones_mecanicas')) {
            return null;
        }

        $row = $this->db->queryOne(
            "SELECT *
             FROM av_revisiones_mecanicas
             WHERE id_unidad = :id
               AND estatus_revision = 'en_revision'
             ORDER BY id_revision DESC
             LIMIT 1",
            ['id' => $idUnidad]
        );

        return $row ?: null;
    }

    private function normalizarItemsRevision(array $datos): array
    {
        $items = $datos['items'] ?? [];
        if (!is_array($items)) {
            $items = [$items];
        }
        $tiposServicio = is_array($datos['tipo_servicio'] ?? null) ? $datos['tipo_servicio'] : [];
        $catalogo = $this->catalogoItemsRevisionPorClave();
        $out = [];

        foreach ($items as $raw) {
            $parts = explode('|', (string) $raw, 2);
            if (count($parts) !== 2) {
                continue;
            }
            $categoria = preg_replace('/[^a-z_]/', '', strtolower(trim($parts[0])));
            $clave = preg_replace('/[^a-z0-9_]/', '', strtolower(trim($parts[1])));
            $idx = $categoria . '|' . $clave;
            if (!isset($catalogo[$idx]) || isset($out[$idx])) {
                continue;
            }

            $item = $catalogo[$idx];
            $tipoServicio = 'na';
            if (($item['tipo_servicio'] ?? 'na') === 'mp_mc') {
                $tipoRaw = strtolower(trim((string) ($tiposServicio[$categoria . '_' . $clave] ?? $tiposServicio[$clave] ?? '')));
                $tipoServicio = in_array($tipoRaw, ['mp', 'mc'], true) ? $tipoRaw : 'mp_mc';
            }

            $out[$idx] = [
                'categoria' => $categoria,
                'clave' => $clave,
                'descripcion' => (string) $item['descripcion'],
                'tipo_servicio' => $tipoServicio,
            ];
        }

        return array_values($out);
    }

    private function catalogoItemsRevisionPorClave(): array
    {
        $out = [];
        foreach (self::CHECKLIST_REVISION_MECANICA as $categoria => $grupo) {
            foreach (($grupo['items'] ?? []) as $item) {
                $clave = (string) ($item['clave'] ?? '');
                if ($clave === '') {
                    continue;
                }
                $out[$categoria . '|' . $clave] = [
                    'categoria' => $categoria,
                    'clave' => $clave,
                    'descripcion' => (string) ($item['descripcion'] ?? $clave),
                    'tipo_servicio' => (string) ($item['tipo_servicio'] ?? 'na'),
                ];
            }
        }

        return $out;
    }

    private function registrarItemRevisionMecanica(int $idRevision, array $item): void
    {
        if ($idRevision <= 0) {
            return;
        }

        $categoria = (string) ($item['categoria'] ?? '');
        $clave = (string) ($item['clave'] ?? '');
        $descripcion = (string) ($item['descripcion'] ?? '');
        $tipoServicio = (string) ($item['tipo_servicio'] ?? 'na');
        if (!in_array($categoria, ['mecanica', 'electrica', 'estetica'], true) || $clave === '' || $descripcion === '') {
            return;
        }
        if (!in_array($tipoServicio, ['mp', 'mc', 'mp_mc', 'na'], true)) {
            $tipoServicio = 'na';
        }

        $this->db->CRUD(
            "INSERT INTO av_revision_mecanica_items (
                id_revision,
                categoria,
                clave,
                descripcion,
                seleccionado,
                tipo_servicio
             ) VALUES (
                :id_revision,
                :categoria,
                :clave,
                :descripcion,
                1,
                :tipo_servicio
             )
             ON DUPLICATE KEY UPDATE
                descripcion = VALUES(descripcion),
                seleccionado = VALUES(seleccionado),
                tipo_servicio = VALUES(tipo_servicio)",
            [
                'id_revision' => $idRevision,
                'categoria' => $categoria,
                'clave' => $clave,
                'descripcion' => $descripcion,
                'tipo_servicio' => $tipoServicio,
            ]
        );
    }

    private function normalizarDictamenRevision(string $dictamen): ?string
    {
        $dictamen = strtolower(trim($dictamen));
        $validos = [
            self::ESTATUS_REPARADA,
            self::ESTATUS_FUERA_PRESUPUESTO,
            self::ESTATUS_IRREPARABLE,
        ];

        return in_array($dictamen, $validos, true) ? $dictamen : null;
    }

    private function etiquetaDictamenRevision(string $dictamen): string
    {
        $labels = [
            self::ESTATUS_REPARADA => 'Reparada',
            self::ESTATUS_FUERA_PRESUPUESTO => 'Fuera de presupuesto',
            self::ESTATUS_IRREPARABLE => 'Irreparable',
        ];

        return $labels[$dictamen] ?? $dictamen;
    }

    private function urlPublicaEvidencia(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (!function_exists('sparta_url_publica_desde_repositorio')) {
            $uploadsPath = dirname(__DIR__) . '/core/UploadsPaths.php';
            if (is_file($uploadsPath)) {
                require_once $uploadsPath;
            }
        }

        return function_exists('sparta_url_publica_desde_repositorio')
            ? sparta_url_publica_desde_repositorio($url)
            : $url;
    }

    private function estatusInventarioInicialDesdeOperacion(array $op): string
    {
        if (trim((string) ($op['recepcion_confirmada_at'] ?? '')) !== '') {
            return self::ESTATUS_PENDIENTE_REVISION;
        }

        return self::ESTATUS_PENDIENTE_EVIDENCIAS;
    }

    private function adjOperacionSelectColumnaONull(string $columna, string $alias): string
    {
        $aliasSql = str_replace('`', '', $columna);
        if ($this->adjOperacionTieneColumna($columna)) {
            return $alias . '.`' . str_replace('`', '``', $columna) . '` AS `' . $aliasSql . '`';
        }

        return 'NULL AS `' . $aliasSql . '`';
    }

    private function adjOperacionTieneColumna(string $columna): bool
    {
        if (self::$adjOperacionColumnas === null) {
            self::$adjOperacionColumnas = [];
            try {
                foreach ($this->db->queryAll('SHOW COLUMNS FROM adj_operacion') ?: [] as $row) {
                    $field = (string) ($row['Field'] ?? '');
                    if ($field !== '') {
                        self::$adjOperacionColumnas[$field] = true;
                    }
                }
            } catch (\Throwable $e) {
                self::$adjOperacionColumnas = [];
            }
        }

        return isset(self::$adjOperacionColumnas[$columna]);
    }

    private function primerValor(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            $valor = trim((string) ($row[$key] ?? ''));
            if ($valor !== '') {
                return $valor;
            }
        }

        return '';
    }

    private function normalizarAlfanumerico(string $valor, int $maxLen): ?string
    {
        $valor = strtoupper(preg_replace('/\s+/u', '', trim($valor)));
        $valor = preg_replace('/[^A-Z0-9\-]/', '', (string) $valor);
        $valor = substr((string) $valor, 0, $maxLen);

        return $valor !== '' ? $valor : null;
    }

    private function normalizarVin(string $valor): ?string
    {
        $valor = strtoupper(preg_replace('/\s+/u', '', trim($valor)));
        $valor = preg_replace('/[^A-Z0-9]/', '', (string) $valor);

        return $valor !== '' ? $valor : null;
    }

    private function normalizarTexto(string $valor, int $maxLen): ?string
    {
        $valor = trim(preg_replace('/\s+/u', ' ', $valor));
        if ($valor === '') {
            return null;
        }

        return mb_substr($valor, 0, $maxLen);
    }

    private function intONull($valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        $int = (int) $valor;

        return $int > 0 ? $int : null;
    }

    private function boolDesdePayload($valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }
        $valor = strtolower(trim((string) $valor));

        return in_array($valor, ['1', 'true', 'on', 'si', 'yes'], true);
    }

    private function fechaHoraCdmx(): string
    {
        $dt = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));

        return $dt->format('Y-m-d H:i:s');
    }

    private function tablasBaseDisponibles(): bool
    {
        foreach (['av_unidades', 'av_ubicaciones', 'av_movimientos', 'av_evidencias', 'av_bitacora', 'av_codigos_verificacion'] as $tabla) {
            if (!$this->tablaExiste($tabla)) {
                return false;
            }
        }

        return true;
    }

    private function tablasRevisionMecanicaDisponibles(): bool
    {
        foreach (['av_revisiones_mecanicas', 'av_revision_mecanica_items'] as $tabla) {
            if (!$this->tablaExiste($tabla)) {
                return false;
            }
        }

        return true;
    }

    private function tablaExiste(string $tabla): bool
    {
        try {
            return (bool) $this->db->queryOne("SHOW TABLES LIKE :tabla", ['tabla' => $tabla]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function asegurarModuloWeb(): void
    {
        try {
            $datos = [
                'id' => self::MODULO_ALMACEN_VIRTUAL,
                'nombre' => 'Almacen Virtual',
                'pestana' => 'Motos Adjudicadas',
                'descripcion' => 'Motos Adjudicadas > Almacen Virtual',
            ];

            $existe = $this->db->queryOne(
                'SELECT id FROM modulos_web WHERE id = :id LIMIT 1',
                ['id' => self::MODULO_ALMACEN_VIRTUAL]
            );
            if ($existe) {
                $this->db->CRUD(
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

            $this->db->CRUD(
                'INSERT INTO modulos_web (id, nombre, pestana, descripcion, activo)
                 VALUES (:id, :nombre, :pestana, :descripcion, 1)',
                $datos
            );
        } catch (\Throwable $e) {
            // No bloquear la vista si el usuario de BD no tiene permisos sobre modulos_web.
        }
    }
}
