<?php

namespace Services;

use Core\Database;

final class AtlasExpedientesCacheService
{
    private const TABLE = 'atlas_expedientes_sparta_cache';
    private const META_TABLE = 'atlas_expedientes_sparta_cache_meta';
    private const META_KEY = 'historico_s2credit';
    private const BATCH_SIZE = 1000;
    private const COLUMNS = [
        'credito_id',
        'numero_credito',
        'cliente_id',
        'cliente_nombre',
        'gestor_persona_id',
        'gestor_nombre',
        'fk_sucursal',
        'sucursal',
        'etapa_credito',
        'oferta_estatus',
        'fecha_activacion_s2',
        'monto_financiar',
        'estatus',
        'estatus_label',
        'origen_cambio',
        'fecha_estado',
    ];

    private Database $db;
    private bool $schemaReady = false;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? new Database();
    }

    public function isReady(): bool
    {
        return $this->metadata() !== null;
    }

    public function replaceSnapshot(array $data): array
    {
        $this->ensureSchema();
        $sourceRows = is_array($data['filas'] ?? null) ? $data['filas'] : [];
        $columns = is_array($data['columnas'] ?? null) ? $data['columnas'] : [];
        $columnar = ($data['formato'] ?? '') === 'columnar' && $columns !== [];
        $columnIndexes = $columnar ? array_flip($columns) : [];

        foreach (self::COLUMNS as $column) {
            if ($columnar && !array_key_exists($column, $columnIndexes)) {
                throw new \RuntimeException('La instantanea de Expedientes no contiene la columna ' . $column . '.');
            }
        }

        $metadata = [
            'periodo' => is_array($data['periodo'] ?? null) ? $data['periodo'] : [],
            'resumen' => is_array($data['resumen'] ?? null) ? $data['resumen'] : [],
            'catalogos' => is_array($data['catalogos'] ?? null) ? $data['catalogos'] : [],
            'instantanea' => is_array($data['instantanea'] ?? null) ? $data['instantanea'] : [],
        ];
        $metadataJson = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($metadataJson)) {
            throw new \RuntimeException('No se pudo preparar la metadata de Expedientes.');
        }

        $this->db->beginTransaction();
        try {
            $this->db->CRUD('DELETE FROM ' . self::TABLE);
            foreach (array_chunk($sourceRows, self::BATCH_SIZE) as $batchIndex => $batch) {
                $this->insertBatch($batch, $batchIndex, $columnar, $columnIndexes);
            }
            $this->db->CRUD(
                'INSERT INTO ' . self::META_TABLE . ' (
                    cache_key, row_count, metadata_json, source_updated_at, updated_at
                ) VALUES (
                    :cache_key, :row_count, :metadata_json, :source_updated_at, NOW()
                ) ON DUPLICATE KEY UPDATE
                    row_count = VALUES(row_count),
                    metadata_json = VALUES(metadata_json),
                    source_updated_at = VALUES(source_updated_at),
                    updated_at = VALUES(updated_at)',
                [
                    'cache_key' => self::META_KEY,
                    'row_count' => count($sourceRows),
                    'metadata_json' => $metadataJson,
                    'source_updated_at' => $metadata['instantanea']['actualizada_en'] ?? null,
                ]
            );
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }

        return [
            'filas_indexadas' => count($sourceRows),
            'actualizada_en' => $metadata['instantanea']['actualizada_en'] ?? null,
        ];
    }

    public function query(array $filters): array
    {
        $metadataRow = $this->metadata();
        if ($metadataRow === null) {
            return ['ready' => false];
        }

        $page = max(1, (int)($filters['page'] ?? 1));
        $pageSize = max(10, min(100, (int)($filters['page_size'] ?? 25)));
        [$periodWhere, $periodParams] = $this->buildWhere($filters, true);
        [$where, $params] = $this->buildWhere($filters, false);

        $totalRow = $this->db->queryOne(
            'SELECT COUNT(*) AS total FROM ' . self::TABLE . $periodWhere,
            $periodParams
        ) ?: [];
        $summary = $this->db->queryOne(
            'SELECT
                COUNT(*) AS total,
                COALESCE(SUM(estatus = \'pendiente\'), 0) AS pendientes,
                COALESCE(SUM(estatus = \'entregado\'), 0) AS entregados,
                COALESCE(SUM(estatus = \'no_entregado\'), 0) AS no_entregados,
                COALESCE(SUM(estatus = \'incidencia\'), 0) AS incidencias
             FROM ' . self::TABLE . $where,
            $params
        ) ?: [];

        $filteredTotal = (int)($summary['total'] ?? 0);
        $offset = ($page - 1) * $pageSize;
        $rows = $this->db->queryAll(
            'SELECT ' . implode(', ', self::COLUMNS) . '
             FROM ' . self::TABLE . $where . '
             ORDER BY fecha_activacion_s2 DESC, credito_id DESC
             LIMIT ' . $pageSize . ' OFFSET ' . $offset,
            $params
        );

        $metadata = json_decode((string)($metadataRow['metadata_json'] ?? ''), true);
        if (!is_array($metadata)) {
            $metadata = [];
        }
        $period = is_array($metadata['periodo'] ?? null) ? $metadata['periodo'] : [];
        $period['fecha_inicio'] = $filters['fecha_inicio'] ?? ($period['fecha_inicio'] ?? null);
        $period['fecha_fin'] = $filters['fecha_fin'] ?? ($period['fecha_fin'] ?? null);

        return [
            'ready' => true,
            'data' => [
                'periodo' => $period,
                'filas' => $rows,
                'resumen' => [
                    'total' => $filteredTotal,
                    'pendientes' => (int)($summary['pendientes'] ?? 0),
                    'entregados' => (int)($summary['entregados'] ?? 0),
                    'no_entregados' => (int)($summary['no_entregados'] ?? 0),
                    'incidencias' => (int)($summary['incidencias'] ?? 0),
                ],
                'paginacion' => [
                    'pagina' => $page,
                    'tamano' => $pageSize,
                    'total_registros' => (int)($totalRow['total'] ?? 0),
                    'total_filtrados' => $filteredTotal,
                    'total_paginas' => max(1, (int)ceil($filteredTotal / $pageSize)),
                ],
                'catalogos' => is_array($metadata['catalogos'] ?? null) ? $metadata['catalogos'] : [],
                'cache' => [
                    'fuente' => 'sparta_mysql',
                    'actualizada_en' => $metadataRow['updated_at'] ?? null,
                    'fuente_actualizada_en' => $metadataRow['source_updated_at'] ?? null,
                    'filas_indexadas' => (int)($metadataRow['row_count'] ?? 0),
                ],
            ],
        ];
    }

    public function updateMovements(array $movements, string $origin): void
    {
        if ($movements === [] || !$this->isReady()) {
            return;
        }

        $labels = [
            'pendiente' => 'Pendiente',
            'entregado' => 'Recolectado',
            'no_entregado' => 'No recolectado',
            'incidencia' => 'Incidencia',
        ];
        $this->db->beginTransaction();
        try {
            foreach ($movements as $movement) {
                $creditId = (int)($movement['credito_id'] ?? 0);
                $status = trim((string)($movement['accion'] ?? ''));
                if ($creditId <= 0 || !isset($labels[$status])) {
                    continue;
                }
                $this->db->CRUD(
                    'UPDATE ' . self::TABLE . '
                     SET estatus = :estatus,
                         estatus_label = :estatus_label,
                         origen_cambio = :origen_cambio,
                         fecha_estado = NOW()
                     WHERE credito_id = :credito_id',
                    [
                        'estatus' => $status,
                        'estatus_label' => $labels[$status],
                        'origen_cambio' => $origin,
                        'credito_id' => $creditId,
                    ]
                );
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollback();
            }
            throw $e;
        }
    }

    private function metadata(): ?array
    {
        try {
            return $this->db->queryOne(
                'SELECT row_count, metadata_json, source_updated_at, updated_at
                 FROM ' . self::META_TABLE . '
                 WHERE cache_key = :cache_key
                 LIMIT 1',
                ['cache_key' => self::META_KEY]
            );
        } catch (\Throwable $e) {
            $this->ensureSchema();
            return null;
        }
    }

    private function ensureSchema(): void
    {
        if ($this->schemaReady) {
            return;
        }
        $this->db->CRUD(
            'CREATE TABLE IF NOT EXISTS ' . self::TABLE . ' (
                credito_id BIGINT UNSIGNED NOT NULL,
                numero_credito VARCHAR(100) NULL,
                cliente_id VARCHAR(100) NULL,
                cliente_nombre VARCHAR(255) NULL,
                gestor_persona_id BIGINT NULL,
                gestor_nombre VARCHAR(255) NULL,
                fk_sucursal BIGINT NULL,
                sucursal VARCHAR(255) NULL,
                etapa_credito VARCHAR(80) NULL,
                oferta_estatus VARCHAR(100) NULL,
                fecha_activacion_s2 VARCHAR(40) NULL,
                monto_financiar DECIMAL(18,2) NOT NULL DEFAULT 0,
                estatus VARCHAR(30) NOT NULL DEFAULT \'pendiente\',
                estatus_label VARCHAR(80) NULL,
                origen_cambio VARCHAR(40) NULL,
                fecha_estado VARCHAR(40) NULL,
                search_text TEXT NOT NULL,
                PRIMARY KEY (credito_id),
                KEY idx_atlas_exp_fecha (fecha_activacion_s2, credito_id),
                KEY idx_atlas_exp_estatus_fecha (estatus, fecha_activacion_s2),
                KEY idx_atlas_exp_sucursal_fecha (fk_sucursal, fecha_activacion_s2)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->db->CRUD(
            'CREATE TABLE IF NOT EXISTS ' . self::META_TABLE . ' (
                cache_key VARCHAR(64) NOT NULL,
                row_count INT UNSIGNED NOT NULL DEFAULT 0,
                metadata_json LONGTEXT NOT NULL,
                source_updated_at VARCHAR(40) NULL,
                updated_at DATETIME NOT NULL,
                PRIMARY KEY (cache_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $this->schemaReady = true;
    }

    private function insertBatch(array $batch, int $batchIndex, bool $columnar, array $columnIndexes): void
    {
        $insertColumns = array_merge(self::COLUMNS, ['search_text']);
        $values = [];
        $placeholders = [];
        foreach ($batch as $rowIndex => $sourceRow) {
            if (!is_array($sourceRow)) {
                continue;
            }
            $row = [];
            foreach (self::COLUMNS as $column) {
                $row[$column] = $columnar
                    ? ($sourceRow[$columnIndexes[$column]] ?? null)
                    : ($sourceRow[$column] ?? null);
            }
            $row['credito_id'] = (int)($row['credito_id'] ?? 0);
            if ($row['credito_id'] <= 0) {
                continue;
            }
            $row['monto_financiar'] = (float)($row['monto_financiar'] ?? 0);
            $row['search_text'] = $this->normalizeSearch(implode(' ', array_filter([
                $row['credito_id'],
                $row['numero_credito'],
                $row['cliente_id'],
                $row['cliente_nombre'],
                $row['gestor_persona_id'],
                $row['gestor_nombre'],
                $row['fk_sucursal'],
                $row['sucursal'],
                $row['etapa_credito'],
                $row['oferta_estatus'],
                $row['estatus_label'],
            ], static fn($value) => $value !== null && $value !== '')));

            $prefix = 'b' . $batchIndex . '_r' . $rowIndex . '_';
            $rowPlaceholders = [];
            foreach ($insertColumns as $column) {
                $key = $prefix . $column;
                $rowPlaceholders[] = ':' . $key;
                $values[$key] = $row[$column] ?? null;
            }
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }
        if ($placeholders === []) {
            return;
        }

        $this->db->CRUD(
            'INSERT INTO ' . self::TABLE . ' (' . implode(', ', $insertColumns) . ') VALUES '
            . implode(', ', $placeholders),
            $values
        );
    }

    private function buildWhere(array $filters, bool $periodOnly): array
    {
        $where = [];
        $params = [];
        $start = trim((string)($filters['fecha_inicio'] ?? ''));
        $end = trim((string)($filters['fecha_fin'] ?? ''));
        if ($start !== '') {
            $where[] = 'fecha_activacion_s2 >= :fecha_inicio';
            $params['fecha_inicio'] = $start;
        }
        if ($end !== '') {
            $nextDay = date('Y-m-d', strtotime($end . ' +1 day'));
            $where[] = 'fecha_activacion_s2 < :fecha_fin_exclusiva';
            $params['fecha_fin_exclusiva'] = $nextDay;
        }
        if (!$periodOnly) {
            $status = trim((string)($filters['estatus'] ?? ''));
            if ($status !== '') {
                $where[] = 'estatus = :estatus';
                $params['estatus'] = $status;
            }
            $branchId = (int)($filters['fk_sucursal'] ?? 0);
            if ($branchId > 0) {
                $where[] = 'fk_sucursal = :fk_sucursal';
                $params['fk_sucursal'] = $branchId;
            }
            $search = $this->normalizeSearch((string)($filters['search'] ?? ''));
            if ($search !== '') {
                $where[] = 'search_text LIKE :search';
                $params['search'] = '%' . $search . '%';
            }
        }

        return [$where === [] ? '' : ' WHERE ' . implode(' AND ', $where), $params];
    }

    private function normalizeSearch(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        if (function_exists('iconv')) {
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if (is_string($ascii)) {
                $value = $ascii;
            }
        }
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }
}
