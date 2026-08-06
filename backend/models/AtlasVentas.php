<?php

namespace Models;

use Core\Database;
use Core\Model;

class AtlasVentas extends Model
{
    private const CACHE_TTL_MINUTES = 15;
    private const CACHE_KEY = 'ventas:v5:bi';
    private const MAX_CANDIDATOS = 100001;
    private const MAX_RESULTADOS = 100000;
    private const HISTORICAL_START = '2025-01-01';
    private const BI_DISPERSION_CUTOFF = '2026-06-29 00:00:00';
    private const BI_SPECIAL_DISTRIBUTORS = [
        736, 556, 531, 290, 211, 106, 70, 31, 14, 824, 849, 520,
    ];
    private const CRITERIO_DISPERSION_BANCARIA = 'DISPERSION_BANCARIA';
    private const CRITERIO_ACTIVACION = 'ACTIVACION_S2';
    private const CRITERIO_POR_DISPERSAR = 'POR_DISPERSAR';
    private const CRITERIO_DISPERSADO = 'DISPERSADO';
    private const ETAPA_S2CREDIT = 'S2CREDIT';
    private const ETAPA_POR_DISPERSAR = 'POR DISPERSAR';

    public static function precargar(bool $forzarActualizacion = false): array
    {
        $hoy = new \DateTimeImmutable('now', new \DateTimeZone('America/Mexico_City'));
        $inicio = self::HISTORICAL_START;
        $fin = $hoy->format('Y-m-d');
        $cacheKey = self::CACHE_KEY;
        $db = null;

        try {
            $db = new Database();
            self::asegurarCachePrecarga($db);
            self::asegurarCacheConsulta($db);
            if (!$forzarActualizacion) {
                $cached = self::leerCachePrecarga($db, $cacheKey, $inicio, $fin);
                if ($cached !== null) {
                    $totalCache = count($cached['datos']['filas'] ?? []);
                    if (!self::cacheConsultaLista($db, $cacheKey, $totalCache)) {
                        try {
                            self::guardarCacheConsulta($db, $cacheKey, $cached);
                        } catch (\Throwable $e) {
                            error_log('[AtlasVentas cache rows] ' . $e->getMessage());
                        }
                    }
                    return $cached;
                }
            }
        } catch (\Throwable $e) {
            error_log('[AtlasVentas cache] ' . $e->getMessage());
            $db = null;
        }

        $response = self::consultar([
            'historico' => true,
            'fecha_fin' => $fin,
        ], true);
        if (!empty($response['success']) && $db instanceof Database) {
            try {
                self::guardarCachePrecarga($db, $cacheKey, $inicio, $fin, $response);
                self::guardarCacheConsulta($db, $cacheKey, $response);
            } catch (\Throwable $e) {
                error_log('[AtlasVentas cache] ' . $e->getMessage());
            }
        }
        return $response;
    }

    public static function consultar(array $input, bool $sinPaginacion = false): array
    {
        try {
            $filtros = self::normalizarFiltros($input);
            $maxi = new \core\DatabaseMaxiProd();

            $candidatos = self::cargarCandidatos($maxi, $filtros);
            if (count($candidatos) >= self::MAX_CANDIDATOS) {
                throw new \RuntimeException(
                    'El periodo contiene mas de ' . self::MAX_RESULTADOS
                    . ' registros candidatos. Reduce el rango de fechas para generar un resultado completo.'
                );
            }

            $ventas = [];
            foreach ($candidatos as $candidato) {
                $seleccion = self::seleccionarVentaBi(
                    $candidato,
                    $filtros['fecha_inicio'],
                    $filtros['fecha_fin']
                );
                if ($seleccion === null) {
                    continue;
                }
                $ventas[] = self::normalizarVenta($candidato, $seleccion);
            }

            usort($ventas, static function (array $a, array $b): int {
                $porFecha = strcmp(
                    (string)($b['fecha_contabilizacion_venta'] ?? ''),
                    (string)($a['fecha_contabilizacion_venta'] ?? '')
                );
                return $porFecha !== 0
                    ? $porFecha
                    : ((int)($b['id_oferta'] ?? 0) <=> (int)($a['id_oferta'] ?? 0));
            });

            $total = count($ventas);
            $resumen = self::resumir($ventas);
            $periodoInicio = $filtros['fecha_inicio'];
            if ($filtros['historico'] && $ventas) {
                $fechasVenta = array_values(array_filter(array_map(
                    static fn(array $venta): string => substr(
                        (string)($venta['fecha_contabilizacion_venta'] ?? ''),
                        0,
                        10
                    ),
                    $ventas
                )));
                if ($fechasVenta) {
                    $periodoInicio = min($fechasVenta);
                }
            }
            $pagina = $sinPaginacion ? 1 : $filtros['page'];
            $tamano = $sinPaginacion ? max($total, 1) : $filtros['page_size'];
            $totalPaginas = max(1, (int)ceil($total / $tamano));
            $pagina = min($pagina, $totalPaginas);
            $filas = $sinPaginacion
                ? $ventas
                : array_slice($ventas, ($pagina - 1) * $tamano, $tamano);

            return [
                'success' => true,
                'mensaje' => $total === 1 ? 'Se encontro 1 venta.' : "Se encontraron {$total} ventas.",
                'datos' => [
                    'filas' => $filas,
                    'resumen' => $resumen,
                    'periodo' => [
                        'fecha_inicio' => $periodoInicio,
                        'fecha_fin' => $filtros['fecha_fin'],
                    ],
                    'paginacion' => [
                        'page' => $pagina,
                        'page_size' => $tamano,
                        'total' => $total,
                        'total_pages' => $totalPaginas,
                    ],
                    'catalogos' => $sinPaginacion ? [] : self::cargarCatalogos($maxi),
                    'regla' => [
                        'descripcion' => 'Criterio BI: distribuidores especiales usan S2Credit; los demas priorizan Dispersado, Por dispersar, Factura y S2Credit, con respaldo bancario antes del corte.',
                        'fecha_corte' => self::BI_DISPERSION_CUTOFF,
                        'total_distribuidores_especiales' => count(self::BI_SPECIAL_DISTRIBUTORS),
                    ],
                ],
            ];
        } catch (\InvalidArgumentException $e) {
            return ['success' => false, 'status' => 422, 'mensaje' => $e->getMessage()];
        } catch (\Throwable $e) {
            error_log('[AtlasVentas] ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'mensaje' => $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'No se pudo consultar Ventas. Intenta nuevamente o contacta a soporte.',
            ];
        }
    }

    public static function consultarPaginado(
        array $input,
        bool $sinPaginacion = false,
        bool $forzarActualizacion = false
    ): array {
        try {
            $filtros = self::normalizarFiltros($input);
            $db = new Database();
            self::asegurarCachePrecarga($db);
            self::asegurarCacheConsulta($db);

            if ($forzarActualizacion || !self::cacheConsultaLista($db, self::CACHE_KEY)) {
                $precarga = self::precargar($forzarActualizacion);
                if (empty($precarga['success'])) {
                    return $precarga;
                }
            }
            if (!self::cacheConsultaLista($db, self::CACHE_KEY)) {
                return [
                    'success' => false,
                    'status' => 503,
                    'mensaje' => 'La consulta rápida de Ventas se está preparando. Intenta nuevamente en unos momentos.',
                ];
            }

            return self::consultarCache($db, self::CACHE_KEY, $filtros, $sinPaginacion);
        } catch (\InvalidArgumentException $e) {
            return ['success' => false, 'status' => 422, 'mensaje' => $e->getMessage()];
        } catch (\Throwable $e) {
            error_log('[AtlasVentas cache query] ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'mensaje' => 'No se pudo consultar Ventas. Intenta nuevamente o contacta a soporte.',
            ];
        }
    }

    public static function normalizarFiltros(array $input): array
    {
        $zona = new \DateTimeZone('America/Mexico_City');
        $hoy = new \DateTimeImmutable('now', $zona);
        $historico = filter_var($input['historico'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $inicio = $historico
            ? self::HISTORICAL_START
            : self::fechaValida($input['fecha_inicio'] ?? $hoy->format('Y-m-01'), 'fecha inicial');
        $fin = self::fechaValida($input['fecha_fin'] ?? $hoy->format('Y-m-d'), 'fecha final');

        if ($inicio > $fin) {
            throw new \InvalidArgumentException('La fecha inicial no puede ser posterior a la fecha final.');
        }
        $dias = (int)(new \DateTimeImmutable($inicio, $zona))->diff(new \DateTimeImmutable($fin, $zona))->days;
        if (!$historico && $dias > 731) {
            throw new \InvalidArgumentException('El rango maximo permitido es de 24 meses.');
        }

        $pageSize = (int)($input['page_size'] ?? 25);
        if (!in_array($pageSize, [25, 50, 100], true)) {
            $pageSize = 25;
        }

        return [
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'historico' => $historico,
            'fk_sucursal' => max(0, (int)($input['fk_sucursal'] ?? 0)),
            'fk_distribuidor' => max(0, (int)($input['fk_distribuidor'] ?? 0)),
            'etapa' => mb_substr(self::normalizarTexto($input['etapa'] ?? ''), 0, 80, 'UTF-8'),
            'search' => mb_substr(trim((string)($input['search'] ?? '')), 0, 120, 'UTF-8'),
            'page' => max(1, (int)($input['page'] ?? 1)),
            'page_size' => $pageSize,
            'include_catalogs' => filter_var($input['include_catalogs'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public static function seleccionarVenta(
        array $fila,
        array $reglas,
        string $fechaInicio,
        string $fechaFin
    ): ?array {
        return self::seleccionarVentaBi(
            $fila,
            self::fechaValida($fechaInicio, 'fecha inicial'),
            self::fechaValida($fechaFin, 'fecha final')
        );
    }

    private static function asegurarCachePrecarga(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_ventas_precarga_cache (
                cache_key VARCHAR(40) NOT NULL,
                periodo_inicio DATE NOT NULL,
                periodo_fin DATE NOT NULL,
                total_registros INT UNSIGNED NOT NULL DEFAULT 0,
                payload_gzip LONGBLOB NOT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (cache_key),
                INDEX idx_atlas_ventas_cache_updated (updated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    private static function leerCachePrecarga(
        Database $db,
        string $cacheKey,
        string $inicio,
        string $fin
    ): ?array {
        $row = $db->queryOne("
            SELECT payload_gzip
            FROM atlas_ventas_precarga_cache
            WHERE cache_key = :cache_key
              AND periodo_inicio = :periodo_inicio
              AND periodo_fin = :periodo_fin
              AND updated_at >= DATE_SUB(NOW(), INTERVAL " . self::CACHE_TTL_MINUTES . " MINUTE)
            LIMIT 1
        ", [
            'cache_key' => $cacheKey,
            'periodo_inicio' => $inicio,
            'periodo_fin' => $fin,
        ]);
        if (!$row || !is_string($row['payload_gzip'] ?? null)) {
            return null;
        }
        $json = gzdecode($row['payload_gzip']);
        if (!is_string($json)) {
            return null;
        }
        $payload = json_decode($json, true);
        return is_array($payload) && !empty($payload['success']) ? $payload : null;
    }

    private static function guardarCachePrecarga(
        Database $db,
        string $cacheKey,
        string $inicio,
        string $fin,
        array $response
    ): void {
        $json = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            return;
        }
        $gzip = gzencode($json, 6);
        if (!is_string($gzip)) {
            return;
        }
        $total = count($response['datos']['filas'] ?? []);
        $db->CRUD("
            INSERT INTO atlas_ventas_precarga_cache (
                cache_key,
                periodo_inicio,
                periodo_fin,
                total_registros,
                payload_gzip,
                updated_at
            ) VALUES (
                :cache_key,
                :periodo_inicio,
                :periodo_fin,
                :total_registros,
                :payload_gzip,
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                periodo_inicio = VALUES(periodo_inicio),
                periodo_fin = VALUES(periodo_fin),
                total_registros = VALUES(total_registros),
                payload_gzip = VALUES(payload_gzip),
                updated_at = NOW()
        ", [
            'cache_key' => $cacheKey,
            'periodo_inicio' => $inicio,
            'periodo_fin' => $fin,
            'total_registros' => $total,
            'payload_gzip' => $gzip,
        ]);
    }

    private static function asegurarCacheConsulta(Database $db): void
    {
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_ventas_cache_filas (
                cache_key VARCHAR(40) NOT NULL,
                id_oferta BIGINT UNSIGNED NOT NULL,
                id_persona BIGINT UNSIGNED NOT NULL DEFAULT 0,
                nombre_cliente VARCHAR(255) NULL,
                fecha_dispersion DATETIME NULL,
                fecha_contabilizacion_venta DATETIME NULL,
                sucursal VARCHAR(255) NULL,
                distribuidor VARCHAR(255) NULL,
                fecha_oferta DATETIME NULL,
                fecha_etapa_actual DATETIME NULL,
                etapa VARCHAR(100) NULL,
                precio_moto DECIMAL(16,2) NOT NULL DEFAULT 0,
                enganche DECIMAL(16,2) NOT NULL DEFAULT 0,
                monto_financiar DECIMAL(16,2) NOT NULL DEFAULT 0,
                semanas VARCHAR(60) NULL,
                oferta VARCHAR(255) NULL,
                modelo_moto VARCHAR(255) NULL,
                marca_moto VARCHAR(255) NULL,
                usuario VARCHAR(180) NULL,
                nombre_vendedor VARCHAR(255) NULL,
                pk_sucursal INT NOT NULL DEFAULT 0,
                fk_distribuidor INT NOT NULL DEFAULT 0,
                criterio_fecha_venta VARCHAR(80) NULL,
                regla_dispersion_id BIGINT NULL,
                PRIMARY KEY (cache_key, id_oferta),
                INDEX idx_atlas_ventas_cache_dispersion (cache_key, fecha_dispersion, id_persona, id_oferta),
                INDEX idx_atlas_ventas_cache_sucursal (cache_key, pk_sucursal, fecha_dispersion),
                INDEX idx_atlas_ventas_cache_distribuidor (cache_key, fk_distribuidor, fecha_dispersion),
                INDEX idx_atlas_ventas_cache_etapa (cache_key, etapa, fecha_dispersion)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_ventas_cache_estado (
                cache_key VARCHAR(40) NOT NULL,
                periodo_inicio DATE NULL,
                periodo_fin DATE NULL,
                total_registros INT UNSIGNED NOT NULL DEFAULT 0,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (cache_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $db->CRUD("
            CREATE TABLE IF NOT EXISTS atlas_ventas_cache_catalogos (
                cache_key VARCHAR(40) NOT NULL,
                payload_gzip MEDIUMBLOB NOT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (cache_key)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        $indiceDispersion = $db->queryOne("
            SHOW INDEX FROM atlas_ventas_cache_filas
            WHERE Key_name = 'idx_atlas_ventas_cache_dispersion'
        ");
        if (!$indiceDispersion) {
            $db->CRUD("
                ALTER TABLE atlas_ventas_cache_filas
                ADD INDEX idx_atlas_ventas_cache_dispersion (
                    cache_key, fecha_dispersion, id_persona, id_oferta
                )
            ");
        }
    }

    private static function cacheConsultaLista(Database $db, string $cacheKey, ?int $totalEsperado = null): bool
    {
        $estado = $db->queryOne("
            SELECT total_registros
            FROM atlas_ventas_cache_estado
            WHERE cache_key = :cache_key
              AND updated_at >= DATE_SUB(NOW(), INTERVAL " . self::CACHE_TTL_MINUTES . " MINUTE)
            LIMIT 1
        ", ['cache_key' => $cacheKey]);
        if (!$estado) {
            return false;
        }
        return $totalEsperado === null || (int)$estado['total_registros'] === $totalEsperado;
    }

    private static function guardarCacheConsulta(Database $db, string $cacheKey, array $response): void
    {
        $filas = is_array($response['datos']['filas'] ?? null) ? $response['datos']['filas'] : [];
        $columnas = [
            'cache_key', 'id_oferta', 'id_persona', 'nombre_cliente', 'fecha_dispersion',
            'fecha_contabilizacion_venta', 'sucursal', 'distribuidor', 'fecha_oferta',
            'fecha_etapa_actual', 'etapa', 'precio_moto', 'enganche', 'monto_financiar',
            'semanas', 'oferta', 'modelo_moto', 'marca_moto', 'usuario', 'nombre_vendedor',
            'pk_sucursal', 'fk_distribuidor', 'criterio_fecha_venta', 'regla_dispersion_id',
        ];
        $inicio = null;
        $fin = null;

        $db->beginTransaction();
        try {
            $db->CRUD(
                "DELETE FROM atlas_ventas_cache_filas WHERE cache_key = :cache_key",
                ['cache_key' => $cacheKey]
            );

            foreach (array_chunk($filas, 200) as $lote) {
                $valoresSql = [];
                $parametros = [];
                foreach ($lote as $indice => $fila) {
                    $normalizada = self::normalizarFilaCache($fila);
                    if ($normalizada['id_oferta'] <= 0) {
                        continue;
                    }
                    $normalizada['cache_key'] = $cacheKey;
                    $marcadores = [];
                    foreach ($columnas as $columna) {
                        $clave = 'r' . $indice . '_' . $columna;
                        $marcadores[] = ':' . $clave;
                        $parametros[$clave] = $normalizada[$columna] ?? null;
                    }
                    $valoresSql[] = '(' . implode(', ', $marcadores) . ')';
                    $fechaVenta = self::fechaCache($normalizada['fecha_contabilizacion_venta'] ?? null);
                    if ($fechaVenta !== null) {
                        $fecha = substr($fechaVenta, 0, 10);
                        $inicio = $inicio === null || $fecha < $inicio ? $fecha : $inicio;
                        $fin = $fin === null || $fecha > $fin ? $fecha : $fin;
                    }
                }
                if ($valoresSql) {
                    $db->CRUD(
                        'INSERT INTO atlas_ventas_cache_filas (' . implode(', ', $columnas) . ') VALUES '
                        . implode(', ', $valoresSql),
                        $parametros
                    );
                }
            }

            $periodo = is_array($response['datos']['periodo'] ?? null) ? $response['datos']['periodo'] : [];
            $inicio = $inicio ?: self::fechaDesdeValor($periodo['fecha_inicio'] ?? null);
            $fin = $fin ?: self::fechaDesdeValor($periodo['fecha_fin'] ?? null);
            $db->CRUD("
                INSERT INTO atlas_ventas_cache_estado (
                    cache_key, periodo_inicio, periodo_fin, total_registros, updated_at
                ) VALUES (
                    :cache_key, :periodo_inicio, :periodo_fin, :total_registros, NOW()
                )
                ON DUPLICATE KEY UPDATE
                    periodo_inicio = VALUES(periodo_inicio),
                    periodo_fin = VALUES(periodo_fin),
                    total_registros = VALUES(total_registros),
                    updated_at = NOW()
            ", [
                'cache_key' => $cacheKey,
                'periodo_inicio' => $inicio,
                'periodo_fin' => $fin,
                'total_registros' => count($filas),
            ]);
            self::guardarCatalogosCache(
                $db,
                $cacheKey,
                self::construirCatalogosCache($db, $cacheKey)
            );
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            throw $e;
        }
    }

    private static function normalizarFilaCache(array $fila): array
    {
        return [
            'id_oferta' => (int)($fila['id_oferta'] ?? 0),
            'id_persona' => (int)($fila['id_persona'] ?? 0),
            'nombre_cliente' => self::nullableTextoCache($fila['nombre_cliente'] ?? null),
            'fecha_dispersion' => self::fechaCache($fila['fecha_dispersion'] ?? null),
            'fecha_contabilizacion_venta' => self::fechaCache($fila['fecha_contabilizacion_venta'] ?? null),
            'sucursal' => self::nullableTextoCache($fila['sucursal'] ?? null),
            'distribuidor' => self::nullableTextoCache($fila['distribuidor'] ?? null),
            'fecha_oferta' => self::fechaCache($fila['fecha_oferta'] ?? null),
            'fecha_etapa_actual' => self::fechaCache($fila['fecha_etapa_actual'] ?? null),
            'etapa' => self::nullableTextoCache($fila['etapa'] ?? null),
            'precio_moto' => round((float)($fila['precio_moto'] ?? 0), 2),
            'enganche' => round((float)($fila['enganche'] ?? 0), 2),
            'monto_financiar' => round((float)($fila['monto_financiar'] ?? 0), 2),
            'semanas' => self::nullableTextoCache($fila['semanas'] ?? null),
            'oferta' => self::nullableTextoCache($fila['oferta'] ?? null),
            'modelo_moto' => self::nullableTextoCache($fila['modelo_moto'] ?? null),
            'marca_moto' => self::nullableTextoCache($fila['marca_moto'] ?? null),
            'usuario' => self::nullableTextoCache($fila['usuario'] ?? null),
            'nombre_vendedor' => self::nullableTextoCache($fila['nombre_vendedor'] ?? null),
            'pk_sucursal' => (int)($fila['pk_sucursal'] ?? 0),
            'fk_distribuidor' => (int)($fila['fk_distribuidor'] ?? 0),
            'criterio_fecha_venta' => self::nullableTextoCache($fila['criterio_fecha_venta'] ?? null),
            'regla_dispersion_id' => isset($fila['regla_dispersion_id'])
                ? (int)$fila['regla_dispersion_id']
                : null,
        ];
    }

    private static function fechaCache($valor): ?string
    {
        $texto = trim((string)$valor);
        if ($texto === '' || !preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}(?::\d{2})?)?/', $texto, $m)) {
            return null;
        }
        $fecha = str_replace('T', ' ', $m[0]);
        if (strlen($fecha) === 10) {
            return $fecha . ' 00:00:00';
        }
        return strlen($fecha) === 16 ? $fecha . ':00' : substr($fecha, 0, 19);
    }

    private static function nullableTextoCache($valor): ?string
    {
        $texto = trim((string)$valor);
        return $texto === '' ? null : $texto;
    }

    private static function consultarCache(
        Database $db,
        string $cacheKey,
        array $filtros,
        bool $sinPaginacion
    ): array {
        [$rangoSql, $parametrosRango] = self::rangoSqlCache($cacheKey, $filtros);
        [$filtrosSql, $parametrosFiltros] = self::filtrosSqlCache($filtros, 'venta');
        $parametros = array_merge($parametrosRango, $parametrosFiltros, [
            'cache_key_fila' => $cacheKey,
        ]);
        $ventasUnicasSql = "
            FROM atlas_ventas_cache_filas venta
            INNER JOIN (
                SELECT id_persona, MAX(id_oferta) AS id_oferta
                FROM atlas_ventas_cache_filas
                WHERE {$rangoSql}
                GROUP BY id_persona
            ) ultima
                    ON ultima.id_persona = venta.id_persona
                   AND ultima.id_oferta = venta.id_oferta
            WHERE venta.cache_key = :cache_key_fila
              AND {$filtrosSql}
        ";
        $resumenRow = $db->queryOne("
            SELECT
                COUNT(*) AS unidades_vendidas,
                COALESCE(SUM(venta.monto_financiar), 0) AS monto_financiado,
                COALESCE(SUM(venta.precio_moto), 0) AS precio_motos,
                COALESCE(SUM(venta.enganche), 0) AS enganche,
                COUNT(DISTINCT NULLIF(venta.pk_sucursal, 0)) AS sucursales,
                COUNT(DISTINCT NULLIF(venta.fk_distribuidor, 0)) AS distribuidores
            {$ventasUnicasSql}
        ", $parametros) ?: [];
        $total = (int)($resumenRow['unidades_vendidas'] ?? 0);
        $pagina = $sinPaginacion ? 1 : $filtros['page'];
        $tamano = $sinPaginacion ? max($total, 1) : $filtros['page_size'];
        $totalPaginas = max(1, (int)ceil($total / $tamano));
        $pagina = min($pagina, $totalPaginas);
        $limiteSql = '';
        if (!$sinPaginacion) {
            $offset = ($pagina - 1) * $tamano;
            $limiteSql = " LIMIT {$tamano} OFFSET {$offset}";
        }
        $filas = $db->queryAll("
            SELECT
                venta.id_persona, venta.id_oferta, venta.nombre_cliente, venta.fecha_dispersion,
                venta.fecha_contabilizacion_venta, venta.sucursal, venta.distribuidor, venta.fecha_oferta,
                venta.fecha_etapa_actual, venta.etapa, venta.precio_moto, venta.enganche, venta.monto_financiar,
                venta.semanas, venta.oferta, venta.modelo_moto, venta.marca_moto, venta.usuario,
                venta.nombre_vendedor, venta.pk_sucursal, venta.fk_distribuidor,
                venta.criterio_fecha_venta, venta.regla_dispersion_id
            {$ventasUnicasSql}
            ORDER BY venta.fecha_dispersion DESC, venta.id_oferta ASC
            {$limiteSql}
        ", $parametros);
        $filas = array_map(static function (array $fila): array {
            foreach (['id_persona', 'id_oferta', 'pk_sucursal', 'fk_distribuidor'] as $campo) {
                $fila[$campo] = (int)($fila[$campo] ?? 0);
            }
            foreach (['precio_moto', 'enganche', 'monto_financiar'] as $campo) {
                $fila[$campo] = (float)($fila[$campo] ?? 0);
            }
            $fila['regla_dispersion_id'] = isset($fila['regla_dispersion_id'])
                ? (int)$fila['regla_dispersion_id']
                : null;
            return $fila;
        }, $filas ?: []);
        $estado = $db->queryOne("
            SELECT periodo_inicio, periodo_fin, total_registros, updated_at
            FROM atlas_ventas_cache_estado
            WHERE cache_key = :cache_key
            LIMIT 1
        ", ['cache_key' => $cacheKey]) ?: [];
        $catalogos = !empty($filtros['include_catalogs'])
            ? self::cargarCatalogosCache($db, $cacheKey)
            : [];

        return [
            'success' => true,
            'mensaje' => $total === 1 ? 'Se encontro 1 venta.' : "Se encontraron {$total} ventas.",
            'datos' => [
                'filas' => $filas,
                'resumen' => [
                    'unidades_vendidas' => $total,
                    'monto_financiado' => round((float)($resumenRow['monto_financiado'] ?? 0), 2),
                    'precio_motos' => round((float)($resumenRow['precio_motos'] ?? 0), 2),
                    'enganche' => round((float)($resumenRow['enganche'] ?? 0), 2),
                    'sucursales' => (int)($resumenRow['sucursales'] ?? 0),
                    'distribuidores' => (int)($resumenRow['distribuidores'] ?? 0),
                ],
                'periodo' => [
                    'fecha_inicio' => $filtros['fecha_inicio'],
                    'fecha_fin' => $filtros['fecha_fin'],
                ],
                'limites' => [
                    'fecha_inicio' => (string)($estado['periodo_inicio'] ?? $filtros['fecha_inicio']),
                    'fecha_fin' => (string)($estado['periodo_fin'] ?? $filtros['fecha_fin']),
                ],
                'paginacion' => [
                    'page' => $pagina,
                    'page_size' => $tamano,
                    'total' => $total,
                    'total_pages' => $totalPaginas,
                ],
                'catalogos' => $catalogos,
                'cache' => [
                    'total_registros' => (int)($estado['total_registros'] ?? 0),
                    'actualizado_en' => (string)($estado['updated_at'] ?? ''),
                ],
                'regla' => [
                    'descripcion' => 'Criterio de dispersion alineado con BI.',
                    'fecha_corte' => self::BI_DISPERSION_CUTOFF,
                    'total_distribuidores_especiales' => count(self::BI_SPECIAL_DISTRIBUTORS),
                ],
            ],
        ];
    }

    private static function rangoSqlCache(string $cacheKey, array $filtros): array
    {
        $where = [
            'cache_key = :cache_key_rango',
            'id_persona > 0',
            'fecha_dispersion IS NOT NULL',
            'fecha_dispersion >= :fecha_inicio_rango',
            'fecha_dispersion < DATE_ADD(:fecha_fin_rango, INTERVAL 1 DAY)',
        ];
        $parametros = [
            'cache_key_rango' => $cacheKey,
            'fecha_inicio_rango' => $filtros['fecha_inicio'],
            'fecha_fin_rango' => $filtros['fecha_fin'],
        ];
        return [implode(' AND ', $where), $parametros];
    }

    private static function filtrosSqlCache(array $filtros, string $alias): array
    {
        $where = [];
        $parametros = [];
        if ($filtros['fk_sucursal'] > 0) {
            $where[] = "{$alias}.pk_sucursal = :fk_sucursal_cache";
            $parametros['fk_sucursal_cache'] = $filtros['fk_sucursal'];
        }
        if ($filtros['fk_distribuidor'] > 0) {
            $where[] = "{$alias}.fk_distribuidor = :fk_distribuidor_cache";
            $parametros['fk_distribuidor_cache'] = $filtros['fk_distribuidor'];
        }
        if ($filtros['etapa'] !== '') {
            $where[] = "UPPER(TRIM({$alias}.etapa)) = :etapa_cache";
            $parametros['etapa_cache'] = $filtros['etapa'];
        }
        if ($filtros['search'] !== '') {
            $valor = '%' . $filtros['search'] . '%';
            $campos = [
                "CAST({$alias}.id_oferta AS CHAR)", "CAST({$alias}.id_persona AS CHAR)",
                "{$alias}.nombre_cliente", "{$alias}.sucursal", "{$alias}.distribuidor",
                "{$alias}.usuario", "{$alias}.nombre_vendedor", "{$alias}.modelo_moto",
                "{$alias}.marca_moto", "{$alias}.etapa", "{$alias}.oferta",
                "CAST({$alias}.pk_sucursal AS CHAR)",
                "CAST({$alias}.fk_distribuidor AS CHAR)",
            ];
            $busquedas = [];
            foreach ($campos as $indice => $campo) {
                $clave = 'search_cache_' . $indice;
                $busquedas[] = "COALESCE({$campo}, '') LIKE :{$clave}";
                $parametros[$clave] = $valor;
            }
            $where[] = '(' . implode(' OR ', $busquedas) . ')';
        }
        return [$where ? implode(' AND ', $where) : '1 = 1', $parametros];
    }

    private static function cargarCatalogosCache(Database $db, string $cacheKey): array
    {
        $row = $db->queryOne("
            SELECT payload_gzip
            FROM atlas_ventas_cache_catalogos
            WHERE cache_key = :cache_key
            LIMIT 1
        ", ['cache_key' => $cacheKey]);
        if ($row && is_string($row['payload_gzip'] ?? null)) {
            $json = gzdecode($row['payload_gzip']);
            $catalogos = is_string($json) ? json_decode($json, true) : null;
            if (is_array($catalogos)) {
                return $catalogos;
            }
        }

        $catalogos = self::construirCatalogosCache($db, $cacheKey);
        self::guardarCatalogosCache($db, $cacheKey, $catalogos);
        return $catalogos;
    }

    private static function construirCatalogosCache(Database $db, string $cacheKey): array
    {
        return [
            'sucursales' => $db->queryAll("
                SELECT
                    pk_sucursal AS id,
                    MAX(COALESCE(NULLIF(sucursal, ''), CONCAT('Sucursal ', pk_sucursal))) AS nombre,
                    MAX(fk_distribuidor) AS fk_distribuidor
                FROM atlas_ventas_cache_filas
                WHERE cache_key = :cache_key
                  AND pk_sucursal > 0
                GROUP BY pk_sucursal
                ORDER BY nombre ASC, pk_sucursal ASC
            ", ['cache_key' => $cacheKey]),
            'distribuidores' => $db->queryAll("
                SELECT
                    fk_distribuidor AS id,
                    MAX(COALESCE(NULLIF(distribuidor, ''), CONCAT('Distribuidor ', fk_distribuidor))) AS nombre
                FROM atlas_ventas_cache_filas
                WHERE cache_key = :cache_key
                  AND fk_distribuidor > 0
                GROUP BY fk_distribuidor
                ORDER BY nombre ASC, fk_distribuidor ASC
            ", ['cache_key' => $cacheKey]),
            'etapas' => $db->queryAll("
                SELECT DISTINCT UPPER(TRIM(etapa)) AS valor
                FROM atlas_ventas_cache_filas
                WHERE cache_key = :cache_key
                  AND etapa IS NOT NULL
                  AND TRIM(etapa) <> ''
                ORDER BY valor ASC
            ", ['cache_key' => $cacheKey]),
        ];
    }

    private static function guardarCatalogosCache(Database $db, string $cacheKey, array $catalogos): void
    {
        $json = json_encode($catalogos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $gzip = is_string($json) ? gzencode($json, 6) : false;
        if (!is_string($gzip)) {
            return;
        }
        $db->CRUD("
            INSERT INTO atlas_ventas_cache_catalogos (cache_key, payload_gzip, updated_at)
            VALUES (:cache_key, :payload_gzip, NOW())
            ON DUPLICATE KEY UPDATE
                payload_gzip = VALUES(payload_gzip),
                updated_at = NOW()
        ", [
            'cache_key' => $cacheKey,
            'payload_gzip' => $gzip,
        ]);
    }

    private static function cargarCandidatos(\core\DatabaseMaxiProd $maxi, array $filtros): array
    {
        $where = [];
        $params = [
            'por_inicio' => $filtros['fecha_inicio'],
            'por_fin' => $filtros['fecha_fin'],
            's2_inicio' => $filtros['fecha_inicio'],
            's2_fin' => $filtros['fecha_fin'],
            'factura_inicio' => $filtros['fecha_inicio'],
            'factura_fin' => $filtros['fecha_fin'],
            'bancaria_inicio' => $filtros['fecha_inicio'],
            'bancaria_fin' => $filtros['fecha_fin'],
            'dispersado_inicio' => $filtros['fecha_inicio'],
            'dispersado_fin' => $filtros['fecha_fin'],
            'oferta_desde' => self::HISTORICAL_START . ' 00:00:00',
        ];

        if ($filtros['fk_sucursal'] > 0) {
            $where[] = 'u.fk_sucursal = :fk_sucursal';
            $params['fk_sucursal'] = $filtros['fk_sucursal'];
        }
        if ($filtros['fk_distribuidor'] > 0) {
            $where[] = 's.fk_distribuidor = :fk_distribuidor';
            $params['fk_distribuidor'] = $filtros['fk_distribuidor'];
        }
        if ($filtros['etapa'] !== '') {
            $where[] = 'UPPER(TRIM(o.etapa)) = :etapa_actual';
            $params['etapa_actual'] = $filtros['etapa'];
        }
        if ($filtros['search'] !== '') {
            $value = '%' . $filtros['search'] . '%';
            $where[] = "(
                CAST(o.id_oferta AS CHAR) LIKE :search_oferta
                OR CAST(o.fk_persona AS CHAR) LIKE :search_persona
                OR COALESCE(p.nombre_completo, '') LIKE :search_cliente
                OR CONCAT_WS(' ', p.primer_nombre, p.segundo_nombre, p.apellido_paterno, p.apellido_materno) LIKE :search_cliente_partes
                OR COALESCE(s.nombre, '') LIKE :search_sucursal
                OR COALESCE(d.nombre, '') LIKE :search_distribuidor
                OR COALESCE(u.usuario, '') LIKE :search_usuario
                OR CONCAT_WS(' ', u.primer_nombre, u.segundo_nombre, u.apellido_paterno, u.apellido_materno) LIKE :search_vendedor
            )";
            foreach ([
                'search_oferta', 'search_persona', 'search_cliente', 'search_cliente_partes',
                'search_sucursal', 'search_distribuidor', 'search_usuario', 'search_vendedor',
            ] as $key) {
                $params[$key] = $value;
            }
        }

        $whereSql = $where ? ' AND ' . implode(' AND ', $where) : '';
        return $maxi->queryAll("
            SELECT
                p.id_persona AS id_persona,
                o.id_oferta,
                DATE_ADD(o.fecha_hora, INTERVAL -6 HOUR) AS fecha_oferta,
                o.etapa,
                o.precio_moto,
                o.enganche,
                o.monto_financiar,
                o.semanas,
                o.oferta,
                o.modelo_moto,
                o.marca_moto,
                u.usuario,
                u.primer_nombre AS vendedor_primer_nombre,
                u.segundo_nombre AS vendedor_segundo_nombre,
                u.apellido_paterno AS vendedor_apellido_paterno,
                u.apellido_materno AS vendedor_apellido_materno,
                u.fk_sucursal AS pk_sucursal,
                s.nombre AS sucursal,
                s.fk_distribuidor,
                d.nombre AS distribuidor,
                p.nombre_completo AS cliente_nombre_completo,
                p.primer_nombre AS cliente_primer_nombre,
                p.segundo_nombre AS cliente_segundo_nombre,
                p.apellido_paterno AS cliente_apellido_paterno,
                p.apellido_materno AS cliente_apellido_materno,
                dispersion_bancaria.fecha_dispersion_bancaria,
                etapas_venta.fecha_paso_s2credit,
                etapas_venta.fecha_paso_por_dispersar,
                etapas_venta.fecha_paso_factura,
                etapas_venta.fecha_paso_dispersado,
                ultima_etapa.fecha_etapa_actual
            FROM oferta o
            INNER JOIN (
                SELECT fk_oferta, MAX(fecha_hora) AS fecha_etapa_actual
                FROM oferta_bitacora
                WHERE fecha_hora IS NOT NULL
                GROUP BY fk_oferta
            ) ultima_etapa
                    ON ultima_etapa.fk_oferta = o.id_oferta
            INNER JOIN usuario u
                    ON u.pk_usuario = o.fk_usuario_creacion
            INNER JOIN sucursal s
                    ON s.pk_sucursal = u.fk_sucursal
            INNER JOIN distribuidor d
                    ON d.pk_distribuidor = s.fk_distribuidor
            LEFT JOIN persona p
                   ON p.id_persona = o.fk_persona
            LEFT JOIN (
                SELECT id_oferta, MIN(fecha_creacion) AS fecha_dispersion_bancaria
                FROM bitacora_dispersiones
                WHERE fecha_creacion IS NOT NULL
                  AND estatus_operacion IS NOT NULL
                  AND UPPER(TRIM(estatus_operacion)) <> 'ER'
                GROUP BY id_oferta
            ) dispersion_bancaria
                   ON dispersion_bancaria.id_oferta = o.id_oferta
            LEFT JOIN (
                SELECT
                    fk_oferta,
                    MIN(CASE WHEN etapa = 'S2CREDIT' THEN fecha_hora END) AS fecha_paso_s2credit,
                    MIN(CASE WHEN etapa = 'POR DISPERSAR' THEN fecha_hora END) AS fecha_paso_por_dispersar,
                    MIN(CASE WHEN etapa = 'FACTURA' THEN fecha_hora END) AS fecha_paso_factura,
                    MIN(CASE WHEN etapa = 'DISPERSADO' THEN fecha_hora END) AS fecha_paso_dispersado
                FROM oferta_bitacora
                WHERE etapa IN ('S2CREDIT', 'POR DISPERSAR', 'FACTURA', 'DISPERSADO')
                  AND fecha_hora IS NOT NULL
                GROUP BY fk_oferta
            ) etapas_venta
                   ON etapas_venta.fk_oferta = o.id_oferta
            WHERE o.fecha_hora >= :oferta_desde
              AND (
                    (etapas_venta.fecha_paso_por_dispersar >= :por_inicio
                     AND etapas_venta.fecha_paso_por_dispersar < DATE_ADD(:por_fin, INTERVAL 1 DAY))
                    OR (etapas_venta.fecha_paso_s2credit >= :s2_inicio
                        AND etapas_venta.fecha_paso_s2credit < DATE_ADD(:s2_fin, INTERVAL 1 DAY))
                    OR (etapas_venta.fecha_paso_factura >= :factura_inicio
                        AND etapas_venta.fecha_paso_factura < DATE_ADD(:factura_fin, INTERVAL 1 DAY))
                    OR (dispersion_bancaria.fecha_dispersion_bancaria >= :bancaria_inicio
                        AND dispersion_bancaria.fecha_dispersion_bancaria < DATE_ADD(:bancaria_fin, INTERVAL 1 DAY))
                    OR (etapas_venta.fecha_paso_dispersado >= :dispersado_inicio
                        AND etapas_venta.fecha_paso_dispersado < DATE_ADD(:dispersado_fin, INTERVAL 1 DAY))
              )
              {$whereSql}
            LIMIT " . self::MAX_CANDIDATOS . "
        ", $params);
    }

    private static function cargarReglas(Database $db): array
    {
        return $db->queryAll("
            SELECT
                id,
                fk_distribuidor,
                nombre_distribuidor,
                criterio_fecha,
                etapa_requerida,
                estatus,
                vigencia_desde,
                vigencia_hasta,
                motivo_cambio,
                version,
                updated_at
            FROM atlas_reglas_dispersion_distribuidor
            ORDER BY fk_distribuidor ASC, vigencia_desde ASC, id ASC
        ");
    }

    private static function cargarCatalogos(\core\DatabaseMaxiProd $maxi): array
    {
        return [
            'sucursales' => $maxi->queryAll("
                SELECT pk_sucursal AS id, nombre, fk_distribuidor
                FROM sucursal
                WHERE estatus = 1
                ORDER BY nombre ASC, pk_sucursal ASC
            "),
            'distribuidores' => $maxi->queryAll("
                SELECT pk_distribuidor AS id, nombre
                FROM distribuidor
                WHERE estatus = 1
                ORDER BY nombre ASC, pk_distribuidor ASC
            "),
        ];
    }

    private static function normalizarReglas(array $filas): array
    {
        $reglas = [];
        $intervalos = [];
        foreach ($filas as $fila) {
            $id = (int)($fila['id'] ?? 0);
            $distribuidor = (int)($fila['fk_distribuidor'] ?? 0);
            $inicio = self::fechaDesdeValor($fila['vigencia_desde'] ?? null);
            $fin = self::fechaDesdeValor($fila['vigencia_hasta'] ?? null) ?: '9999-12-31';
            $criterio = self::normalizarCriterio($fila['criterio_fecha'] ?? '');
            $etapa = self::normalizarTexto($fila['etapa_requerida'] ?? '');
            if ($criterio === self::CRITERIO_ACTIVACION && $etapa === self::ETAPA_POR_DISPERSAR) {
                $etapa = self::ETAPA_S2CREDIT;
            }
            if ($criterio === self::CRITERIO_DISPERSADO && $etapa === self::ETAPA_POR_DISPERSAR) {
                $etapa = self::CRITERIO_DISPERSADO;
            }

            if ($distribuidor <= 0 || $inicio === null || $fin < $inicio) {
                throw new \RuntimeException("La regla de ventas {$id} tiene una vigencia invalida.");
            }
            if (!in_array($criterio, [self::CRITERIO_ACTIVACION, self::CRITERIO_DISPERSADO], true)) {
                throw new \RuntimeException("La regla de ventas {$id} tiene un criterio invalido.");
            }
            if (!in_array($etapa, [self::ETAPA_S2CREDIT, self::CRITERIO_DISPERSADO, self::ETAPA_POR_DISPERSAR], true)) {
                throw new \RuntimeException("La regla de ventas {$id} tiene una etapa invalida.");
            }
            $estatus = (int)($fila['estatus'] ?? -1);
            if (!in_array($estatus, [0, 1], true)) {
                throw new \RuntimeException("La regla de ventas {$id} tiene un estatus invalido.");
            }

            $regla = $fila;
            $regla['id'] = $id;
            $regla['fk_distribuidor'] = $distribuidor;
            $regla['criterio_fecha'] = $criterio;
            $regla['etapa_requerida'] = $etapa;
            $regla['estatus'] = $estatus;
            $regla['vigencia_desde'] = $inicio;
            $regla['vigencia_hasta'] = $fin === '9999-12-31' ? null : $fin;
            $reglas[] = $regla;
            $intervalos[$distribuidor][] = [$inicio, $fin, $id];
        }

        foreach ($intervalos as $distribuidor => $items) {
            usort($items, static fn(array $a, array $b): int => [$a[0], $a[1], $a[2]] <=> [$b[0], $b[1], $b[2]]);
            $anterior = null;
            foreach ($items as $actual) {
                if ($anterior !== null && $actual[0] <= $anterior[1]) {
                    throw new \RuntimeException(
                        "Las reglas de ventas {$anterior[2]} y {$actual[2]} del distribuidor {$distribuidor} se traslapan."
                    );
                }
                $anterior = $actual;
            }
        }

        return $reglas;
    }

    private static function seleccionarVentaBi(
        array $fila,
        string $fechaInicio,
        string $fechaFin
    ): ?array {
        $distribuidor = (int)($fila['fk_distribuidor'] ?? 0);
        $esEspecial = in_array($distribuidor, self::BI_SPECIAL_DISTRIBUTORS, true);
        $criterio = self::ETAPA_S2CREDIT;
        $candidato = $fila['fecha_paso_s2credit'] ?? null;

        if (!$esEspecial) {
            $eventos = [
                self::CRITERIO_DISPERSADO => $fila['fecha_paso_dispersado'] ?? null,
                self::CRITERIO_POR_DISPERSAR => $fila['fecha_paso_por_dispersar'] ?? null,
                'FACTURA' => $fila['fecha_paso_factura'] ?? null,
                self::ETAPA_S2CREDIT => $fila['fecha_paso_s2credit'] ?? null,
            ];
            $candidato = null;
            foreach ($eventos as $tipo => $valor) {
                if (trim((string)$valor) === '') {
                    continue;
                }
                $criterio = $tipo;
                $candidato = $valor;
                break;
            }
        }

        $fechaDispersion = self::fechaCache($candidato);
        if ($fechaDispersion === null || $fechaDispersion < self::BI_DISPERSION_CUTOFF) {
            $criterio = self::CRITERIO_DISPERSION_BANCARIA;
            $fechaDispersion = self::fechaCache($fila['fecha_dispersion_bancaria'] ?? null);
        }
        if ($fechaDispersion === null) {
            return null;
        }

        $fecha = substr($fechaDispersion, 0, 10);
        if ($fecha < $fechaInicio || $fecha > $fechaFin) {
            return null;
        }

        return [
            'regla' => ['id' => null],
            'criterio_fecha_venta' => $criterio,
            'fecha_dispersion' => $fechaDispersion,
            'fecha_contabilizacion_venta' => $fechaDispersion,
        ];
    }

    private static function normalizarVenta(array $fila, array $seleccion): array
    {
        $cliente = trim((string)($fila['cliente_nombre_completo'] ?? ''));
        if ($cliente === '') {
            $cliente = self::nombreCompleto($fila, 'cliente_');
        }

        return [
            'id_persona' => (int)($fila['id_persona'] ?? 0),
            'id_oferta' => (int)($fila['id_oferta'] ?? 0),
            'nombre_cliente' => $cliente,
            'fecha_dispersion' => (string)($seleccion['fecha_dispersion'] ?? ''),
            'fecha_contabilizacion_venta' => (string)($seleccion['fecha_contabilizacion_venta'] ?? ''),
            'sucursal' => trim((string)($fila['sucursal'] ?? '')),
            'distribuidor' => trim((string)($fila['distribuidor'] ?? '')),
            'fecha_oferta' => (string)($fila['fecha_oferta'] ?? ''),
            'fecha_etapa_actual' => (string)($fila['fecha_etapa_actual'] ?? ''),
            'etapa' => trim((string)($fila['etapa'] ?? '')),
            'precio_moto' => (float)($fila['precio_moto'] ?? 0),
            'enganche' => (float)($fila['enganche'] ?? 0),
            'monto_financiar' => (float)($fila['monto_financiar'] ?? 0),
            'semanas' => trim((string)($fila['semanas'] ?? '')),
            'oferta' => trim((string)($fila['oferta'] ?? '')),
            'modelo_moto' => trim((string)($fila['modelo_moto'] ?? '')),
            'marca_moto' => trim((string)($fila['marca_moto'] ?? '')),
            'usuario' => trim((string)($fila['usuario'] ?? '')),
            'nombre_vendedor' => self::nombreCompleto($fila, 'vendedor_'),
            'pk_sucursal' => (int)($fila['pk_sucursal'] ?? 0),
            'fk_distribuidor' => (int)($fila['fk_distribuidor'] ?? 0),
            'criterio_fecha_venta' => (string)($seleccion['criterio_fecha_venta'] ?? ''),
            'regla_dispersion_id' => isset($seleccion['regla']['id']) ? (int)$seleccion['regla']['id'] : null,
        ];
    }

    private static function resumir(array $ventas): array
    {
        $sucursales = [];
        $distribuidores = [];
        $resumen = [
            'unidades_vendidas' => count($ventas),
            'monto_financiado' => 0.0,
            'precio_motos' => 0.0,
            'enganche' => 0.0,
            'sucursales' => 0,
            'distribuidores' => 0,
        ];
        foreach ($ventas as $venta) {
            $resumen['monto_financiado'] += (float)$venta['monto_financiar'];
            $resumen['precio_motos'] += (float)$venta['precio_moto'];
            $resumen['enganche'] += (float)$venta['enganche'];
            if ((int)$venta['pk_sucursal'] > 0) {
                $sucursales[(int)$venta['pk_sucursal']] = true;
            }
            if ((int)$venta['fk_distribuidor'] > 0) {
                $distribuidores[(int)$venta['fk_distribuidor']] = true;
            }
        }
        $resumen['monto_financiado'] = round($resumen['monto_financiado'], 2);
        $resumen['precio_motos'] = round($resumen['precio_motos'], 2);
        $resumen['enganche'] = round($resumen['enganche'], 2);
        $resumen['sucursales'] = count($sucursales);
        $resumen['distribuidores'] = count($distribuidores);
        return $resumen;
    }

    private static function eventoParaRegla(array $fila, array $regla): array
    {
        $valor = $regla['criterio_fecha'] === self::CRITERIO_ACTIVACION
            ? self::valorEventoS2($fila)
            : ($fila['fecha_paso_dispersado'] ?? null);
        return [self::fechaDesdeValor($valor), $valor];
    }

    private static function valorEventoS2(array $fila)
    {
        $s2credit = $fila['fecha_paso_s2credit'] ?? null;
        if (trim((string)$s2credit) !== '') {
            return $s2credit;
        }
        return $fila['fecha_dispersion'] ?? null;
    }

    private static function fechasEventos(array $fila): array
    {
        return [
            self::fechaDesdeValor($fila['fecha_paso_por_dispersar'] ?? null),
            self::fechaDesdeValor($fila['fecha_paso_dispersado'] ?? null),
            self::fechaDesdeValor(self::valorEventoS2($fila)),
        ];
    }

    private static function fechaDentroDeRegla(string $fecha, array $regla): bool
    {
        $inicio = (string)$regla['vigencia_desde'];
        $fin = self::fechaDesdeValor($regla['vigencia_hasta'] ?? null) ?: '9999-12-31';
        return $fecha >= $inicio && $fecha <= $fin;
    }

    private static function nombreCompleto(array $fila, string $prefijo): string
    {
        $partes = [];
        foreach (['primer_nombre', 'segundo_nombre', 'apellido_paterno', 'apellido_materno'] as $campo) {
            $valor = trim((string)($fila[$prefijo . $campo] ?? ''));
            if ($valor !== '') {
                $partes[] = $valor;
            }
        }
        return implode(' ', $partes);
    }

    private static function normalizarCriterio($valor): string
    {
        $normalizado = str_replace(' ', '_', self::normalizarTexto($valor));
        if (in_array($normalizado, [
            'ACTIVACION', 'ACTIVATION', 'FECHA_ACTIVACION', 'FECHA_DE_ACTIVACION',
            'ACTIVACION_S2', 'S2CREDIT', 'S2_CREDIT', 'FECHA_ACTIVACION_S2', 'FECHA_S2CREDIT',
        ], true)) {
            return self::CRITERIO_ACTIVACION;
        }
        if (in_array($normalizado, [
            'DISPERSADO', 'FECHA_DISPERSADO', 'FECHA_DE_DISPERSADO', 'POR_DISPERSAR',
            'DISPERSION', 'FECHA_POR_DISPERSAR', 'FECHA_DE_DISPERSION',
        ], true)) {
            return self::CRITERIO_DISPERSADO;
        }
        return $normalizado;
    }

    private static function normalizarTexto($valor): string
    {
        $texto = mb_strtoupper(trim((string)$valor), 'UTF-8');
        return strtr($texto, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);
    }

    private static function fechaValida($valor, string $etiqueta): string
    {
        $texto = trim((string)$valor);
        $fecha = \DateTimeImmutable::createFromFormat('!Y-m-d', $texto);
        $errores = \DateTimeImmutable::getLastErrors();
        if (
            !$fecha
            || ($errores !== false && (($errores['warning_count'] ?? 0) > 0 || ($errores['error_count'] ?? 0) > 0))
            || $fecha->format('Y-m-d') !== $texto
        ) {
            throw new \InvalidArgumentException("La {$etiqueta} no es valida.");
        }
        return $texto;
    }

    private static function fechaDesdeValor($valor): ?string
    {
        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d');
        }
        $texto = trim((string)$valor);
        if ($texto === '') {
            return null;
        }
        $fecha = substr($texto, 0, 10);
        $objeto = \DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
        return $objeto && $objeto->format('Y-m-d') === $fecha ? $fecha : null;
    }
}
