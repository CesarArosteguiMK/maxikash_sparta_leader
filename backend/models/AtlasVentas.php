<?php

namespace Models;

use Core\Database;
use Core\Model;

class AtlasVentas extends Model
{
    private const MAX_CANDIDATOS = 50001;
    private const MAX_RESULTADOS = 50000;
    private const CRITERIO_ACTIVACION = 'ACTIVACION_S2';
    private const CRITERIO_POR_DISPERSAR = 'POR_DISPERSAR';
    private const CRITERIO_DISPERSADO = 'DISPERSADO';
    private const ETAPA_S2CREDIT = 'S2CREDIT';
    private const ETAPA_POR_DISPERSAR = 'POR DISPERSAR';

    public static function consultar(array $input, bool $sinPaginacion = false): array
    {
        try {
            $filtros = self::normalizarFiltros($input);
            $db = new Database();
            $maxi = new \core\DatabaseMaxiProd();

            $reglas = self::normalizarReglas(self::cargarReglas($db));
            $candidatos = self::cargarCandidatos($maxi, $filtros);
            if (count($candidatos) >= self::MAX_CANDIDATOS) {
                throw new \RuntimeException(
                    'El periodo contiene mas de ' . self::MAX_RESULTADOS
                    . ' registros candidatos. Reduce el rango de fechas para generar un resultado completo.'
                );
            }

            $ventas = [];
            foreach ($candidatos as $candidato) {
                $seleccion = self::seleccionarVentaNormalizada(
                    $candidato,
                    $reglas,
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
                        'fecha_inicio' => $filtros['fecha_inicio'],
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
                        'descripcion' => 'Se aplica la regla vigente del distribuidor. Sin regla especifica, se toma Por dispersar, despues Dispersado y finalmente S2Credit.',
                        'total_reglas' => count($reglas),
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

    public static function normalizarFiltros(array $input): array
    {
        $zona = new \DateTimeZone('America/Mexico_City');
        $hoy = new \DateTimeImmutable('now', $zona);
        $inicio = self::fechaValida($input['fecha_inicio'] ?? $hoy->format('Y-m-01'), 'fecha inicial');
        $fin = self::fechaValida($input['fecha_fin'] ?? $hoy->format('Y-m-d'), 'fecha final');

        if ($inicio > $fin) {
            throw new \InvalidArgumentException('La fecha inicial no puede ser posterior a la fecha final.');
        }
        $dias = (int)(new \DateTimeImmutable($inicio, $zona))->diff(new \DateTimeImmutable($fin, $zona))->days;
        if ($dias > 731) {
            throw new \InvalidArgumentException('El rango maximo permitido es de 24 meses.');
        }

        $pageSize = (int)($input['page_size'] ?? 25);
        if (!in_array($pageSize, [25, 50, 100], true)) {
            $pageSize = 25;
        }

        return [
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'fk_sucursal' => max(0, (int)($input['fk_sucursal'] ?? 0)),
            'fk_distribuidor' => max(0, (int)($input['fk_distribuidor'] ?? 0)),
            'search' => mb_substr(trim((string)($input['search'] ?? '')), 0, 120, 'UTF-8'),
            'page' => max(1, (int)($input['page'] ?? 1)),
            'page_size' => $pageSize,
        ];
    }

    public static function seleccionarVenta(
        array $fila,
        array $reglas,
        string $fechaInicio,
        string $fechaFin
    ): ?array {
        return self::seleccionarVentaNormalizada(
            $fila,
            self::normalizarReglas($reglas),
            self::fechaValida($fechaInicio, 'fecha inicial'),
            self::fechaValida($fechaFin, 'fecha final')
        );
    }

    private static function cargarCandidatos(\core\DatabaseMaxiProd $maxi, array $filtros): array
    {
        $where = [];
        $params = [
            'por_inicio' => $filtros['fecha_inicio'],
            'por_fin' => $filtros['fecha_fin'],
            's2_inicio' => $filtros['fecha_inicio'],
            's2_fin' => $filtros['fecha_fin'],
            'activacion_inicio' => $filtros['fecha_inicio'],
            'activacion_fin' => $filtros['fecha_fin'],
            'dispersado_inicio' => $filtros['fecha_inicio'],
            'dispersado_fin' => $filtros['fecha_fin'],
        ];

        if ($filtros['fk_sucursal'] > 0) {
            $where[] = 'u.fk_sucursal = :fk_sucursal';
            $params['fk_sucursal'] = $filtros['fk_sucursal'];
        }
        if ($filtros['fk_distribuidor'] > 0) {
            $where[] = 's.fk_distribuidor = :fk_distribuidor';
            $params['fk_distribuidor'] = $filtros['fk_distribuidor'];
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
                o.fk_persona AS id_persona,
                o.id_oferta,
                o.fecha_hora AS fecha_oferta,
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
                dispersion_s2.fecha_activacion_s2,
                s2credit.fecha_paso_s2credit,
                por_dispersar.fecha_paso_por_dispersar,
                dispersado.fecha_paso_dispersado,
                (
                    SELECT MAX(actual.fecha_hora)
                    FROM oferta_bitacora actual
                    WHERE actual.fk_oferta = o.id_oferta
                      AND UPPER(TRIM(COALESCE(actual.etapa, ''))) = UPPER(TRIM(COALESCE(o.etapa, '')))
                      AND actual.fecha_hora IS NOT NULL
                ) AS fecha_etapa_actual
            FROM oferta o
            INNER JOIN usuario u
                    ON u.pk_usuario = o.fk_usuario_creacion
            INNER JOIN sucursal s
                    ON s.pk_sucursal = u.fk_sucursal
            LEFT JOIN distribuidor d
                   ON d.pk_distribuidor = s.fk_distribuidor
            LEFT JOIN persona p
                   ON p.id_persona = o.fk_persona
            LEFT JOIN (
                SELECT id_oferta, MIN(fecha_creacion) AS fecha_activacion_s2
                FROM bitacora_dispersiones
                WHERE fecha_creacion IS NOT NULL
                  AND estatus_operacion IS NOT NULL
                  AND UPPER(TRIM(estatus_operacion)) <> 'ER'
                GROUP BY id_oferta
            ) dispersion_s2
                   ON dispersion_s2.id_oferta = o.id_oferta
            LEFT JOIN (
                SELECT fk_oferta, MIN(fecha_hora) AS fecha_paso_s2credit
                FROM oferta_bitacora
                WHERE UPPER(TRIM(COALESCE(etapa, ''))) = 'S2CREDIT'
                  AND fecha_hora IS NOT NULL
                GROUP BY fk_oferta
            ) s2credit
                   ON s2credit.fk_oferta = o.id_oferta
            LEFT JOIN (
                SELECT fk_oferta, MIN(fecha_hora) AS fecha_paso_por_dispersar
                FROM oferta_bitacora
                WHERE UPPER(TRIM(COALESCE(etapa, ''))) = 'POR DISPERSAR'
                  AND fecha_hora IS NOT NULL
                GROUP BY fk_oferta
            ) por_dispersar
                   ON por_dispersar.fk_oferta = o.id_oferta
            LEFT JOIN (
                SELECT fk_oferta, MIN(fecha_hora) AS fecha_paso_dispersado
                FROM oferta_bitacora
                WHERE UPPER(TRIM(COALESCE(etapa, ''))) = 'DISPERSADO'
                  AND fecha_hora IS NOT NULL
                GROUP BY fk_oferta
            ) dispersado
                   ON dispersado.fk_oferta = o.id_oferta
            WHERE o.estatus = 1
              AND (
                    DATE(por_dispersar.fecha_paso_por_dispersar) BETWEEN :por_inicio AND :por_fin
                    OR DATE(s2credit.fecha_paso_s2credit) BETWEEN :s2_inicio AND :s2_fin
                    OR DATE(dispersion_s2.fecha_activacion_s2) BETWEEN :activacion_inicio AND :activacion_fin
                    OR DATE(dispersado.fecha_paso_dispersado) BETWEEN :dispersado_inicio AND :dispersado_fin
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

    private static function seleccionarVentaNormalizada(
        array $fila,
        array $reglas,
        string $fechaInicio,
        string $fechaFin
    ): ?array {
        $distribuidor = (int)($fila['fk_distribuidor'] ?? 0);
        $coincidencias = [];

        foreach ($reglas as $regla) {
            if ((int)$regla['fk_distribuidor'] !== $distribuidor) {
                continue;
            }
            [$fechaEvento, $valorEvento] = self::eventoParaRegla($fila, $regla);
            if ($fechaEvento === null || !self::fechaDentroDeRegla($fechaEvento, $regla)) {
                continue;
            }
            $coincidencias[] = [$regla, $fechaEvento, $valorEvento];
        }

        if (count($coincidencias) > 1) {
            throw new \RuntimeException("Hay mas de una regla aplicable para el distribuidor {$distribuidor}.");
        }
        if ($coincidencias) {
            [$regla, $fechaEvento, $valorEvento] = $coincidencias[0];
            if ((int)$regla['estatus'] !== 1 || $fechaEvento < $fechaInicio || $fechaEvento > $fechaFin) {
                return null;
            }
            return [
                'regla' => $regla,
                'criterio_fecha_venta' => $regla['criterio_fecha'],
                'fecha_contabilizacion_venta' => $valorEvento,
            ];
        }

        foreach (self::fechasEventos($fila) as $fechaEvento) {
            if ($fechaEvento === null) {
                continue;
            }
            $aplicables = array_filter($reglas, static function (array $regla) use ($distribuidor, $fechaEvento): bool {
                return (int)$regla['fk_distribuidor'] === $distribuidor
                    && self::fechaDentroDeRegla($fechaEvento, $regla);
            });
            if (count($aplicables) > 1) {
                throw new \RuntimeException("Hay mas de una regla vigente para el distribuidor {$distribuidor}.");
            }
            if ($aplicables) {
                return null;
            }
        }

        $eventosDefault = [
            [self::CRITERIO_POR_DISPERSAR, $fila['fecha_paso_por_dispersar'] ?? null],
            [self::CRITERIO_DISPERSADO, $fila['fecha_paso_dispersado'] ?? null],
            [self::ETAPA_S2CREDIT, self::valorEventoS2($fila)],
        ];
        foreach ($eventosDefault as [$criterio, $valorEvento]) {
            $fechaEvento = self::fechaDesdeValor($valorEvento);
            if ($fechaEvento !== null && $fechaEvento >= $fechaInicio && $fechaEvento <= $fechaFin) {
                return [
                    'regla' => [
                        'id' => null,
                        'nombre_distribuidor' => 'Regla general',
                        'criterio_fecha' => self::CRITERIO_DISPERSADO,
                        'etapa_requerida' => self::CRITERIO_DISPERSADO,
                        'estatus' => 1,
                    ],
                    'criterio_fecha_venta' => $criterio,
                    'fecha_contabilizacion_venta' => $valorEvento,
                ];
            }
        }

        return null;
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
        return $fila['fecha_activacion_s2'] ?? null;
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
