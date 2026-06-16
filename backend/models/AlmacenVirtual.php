<?php

namespace Models;

use Core\Model;
use Core\Database;

class AlmacenVirtual extends Model
{
    private const MODULO_ALMACEN_VIRTUAL = 139;
    private const CELULA_MOTOS_ADJUDICADAS = 1;
    private const CELULA_FURIAMOTOS = 2;

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
                'message' => 'Faltan tablas base de Almacen Virtual. Ejecuta la migracion inicial av_*.',
                'rows' => [],
                'total' => 0,
                'limit' => 8,
                'page' => 1,
            ];
        }

        $page = max(1, (int) ($filtros['page'] ?? 1));
        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));

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
            DATE_FORMAT(u.fecha_ingreso_virtual, '%d/%m/%Y %H:%i') AS fecha_ingreso_virtual_fmt,
            DATE_FORMAT(u.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
            DATE_FORMAT(u.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
        FROM av_unidades u
        LEFT JOIN av_ubicaciones ub ON ub.id_ubicacion = u.id_ubicacion_actual
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

    public function listarPendientesMotosAdjudicadas(array $filtros = []): array
    {
        if (!$this->tablaExiste('adj_operacion')) {
            return ['success' => false, 'message' => 'No existe la tabla adj_operacion.', 'rows' => [], 'total' => 0];
        }
        if (!$this->tablaExiste('av_unidades')) {
            return ['success' => false, 'message' => 'No existe la tabla av_unidades.', 'rows' => [], 'total' => 0];
        }

        $limit = max(1, min(100, (int) ($filtros['limit'] ?? 8)));
        $page = max(1, (int) ($filtros['page'] ?? 1));
        $q = trim((string) ($filtros['q'] ?? ''));

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

        $stage = [
            "ao.estatus IN ('Cierre Documentado', 'Retenciones')",
            "ao.estatus LIKE 'Recepci%'",
        ];
        if ($this->adjOperacionTieneColumna('fecha_llegada_almacen')) {
            $stage[] = 'ao.fecha_llegada_almacen IS NOT NULL';
        }
        if ($this->adjOperacionTieneColumna('recepcion_confirmada_at')) {
            $stage[] = 'ao.recepcion_confirmada_at IS NOT NULL';
        }
        $where[] = '(' . implode(' OR ', $stage) . ')';

        if ($q !== '') {
            $where[] = "(
                CAST(ao.id AS CHAR) LIKE :q
                OR CAST(ao.id_credito AS CHAR) LIKE :q
                OR ao.nombre_cliente LIKE :q
                OR ao.folio LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalRow = $this->db->queryOne(
            "SELECT COUNT(*) AS total
             FROM adj_operacion ao
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
                " . implode(",\n                ", $selectCols) . "
             FROM adj_operacion ao
             LEFT JOIN av_unidades av
               ON av.id_celula = :celula_rows
              AND av.id_origen = ao.id
              AND av.deleted_at IS NULL
             {$whereSql}
             ORDER BY ao.fecha_actualizacion DESC, ao.id DESC
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
            return ['success' => false, 'message' => 'Faltan tablas base de Almacen Virtual. Ejecuta la migracion inicial av_*.'];
        }

        $existente = $this->obtenerUnidadPorOrigen(self::CELULA_MOTOS_ADJUDICADAS, $idOperacion);
        if ($existente) {
            return [
                'success' => true,
                'ya_existe' => true,
                'message' => 'La operacion ya existe en Almacen Virtual.',
                'unidad' => $existente,
            ];
        }

        $op = $this->obtenerOperacionMotosAdjudicadas($idOperacion);
        if (!$op) {
            return ['success' => false, 'message' => 'No se encontro la operacion de Motos Adjudicadas.'];
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
                'Creada desde Motos Adjudicadas.',
                $idUsuario,
                $nombreUsuario,
                $ahora
            );
            $this->registrarBitacora(
                $idUnidad,
                'Almacen Virtual',
                'UNIDAD CREADA DESDE MOTOS ADJUDICADAS',
                'Operacion #' . $idOperacion,
                [
                    'id_operacion' => $idOperacion,
                    'id_credito' => $op['id_credito'] ?? null,
                    'estatus_origen' => $op['estatus'] ?? null,
                    'recepcion_confirmada_at' => $op['recepcion_confirmada_at'] ?? null,
                    'fecha_llegada_almacen' => $op['fecha_llegada_almacen'] ?? null,
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
                    'message' => 'La operacion ya existe en Almacen Virtual.',
                    'unidad' => $existente,
                ];
            }

            return ['success' => false, 'message' => 'No se pudo crear la unidad en Almacen Virtual.', 'error' => $e->getMessage()];
        }

        $creada = $this->obtenerUnidadPorId($idUnidad);

        return [
            'success' => true,
            'ya_existe' => false,
            'message' => 'Unidad creada en Almacen Virtual.',
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
    ): void {
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

    private function estatusInventarioInicialDesdeOperacion(array $op): string
    {
        if (trim((string) ($op['recepcion_confirmada_at'] ?? '')) !== '') {
            return 'pendiente_revision';
        }
        $estatus = trim((string) ($op['estatus'] ?? ''));
        if (trim((string) ($op['fecha_llegada_almacen'] ?? '')) !== '' || stripos($estatus, 'Recepci') === 0) {
            return 'en_recepcion';
        }

        return 'pendiente_recepcion';
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
                'pestana' => 'Inventario',
                'descripcion' => 'Almacen Virtual > Inventario',
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
