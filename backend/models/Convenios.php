<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseSegundometro;

class Convenios extends Model
{
    // Buckets elegibles para cualquier oferta (mora 8+ dias)
    private static $BUCKETS_ELEGIBLES = ['c) 8 a 14 dias', 'd) 15 a 21 dias', 'e) 22 a 30 dias', 'f) 31 a 60 dias', 'g) 61 a 90 dias', 'h) 91 a 120 dias', 'i) 121+ dias'];

    /**
     * Resuelve los datos operativos del credito en Segundometro.
     * La semana actual tiene prioridad y el historico conserva la ultima fotografia disponible.
     */
    private static function _obtenerCreditoSegundometro(DatabaseSegundometro $db, int $idCredito): ?array
    {
        $credito = $db->queryOne(
            "SELECT
                Id_credito,
                Nombre_cliente,
                Bucket_Morosidad_Real,
                Dias_mora,
                Avance_Pago_Plazo,
                Numero_amortizaciones,
                Num_cuotas_pagadas,
                Saldo_total_capital,
                Saldo_para_liquidar_hoy AS Adeudo_total,
                Monto_otorgado,
                Rango_Monto,
                Sucursal,
                Gestor_Asignado
             FROM tbl_segundometro_semana
             WHERE Id_credito = :id
             LIMIT 1",
            ['id' => $idCredito]
        );

        if ($credito) {
            return ['credito' => $credito, 'origen' => 'semana'];
        }

        $credito = $db->queryOne(
            "SELECT
                id_credito AS Id_credito,
                Nombre_cliente,
                Bucket_Morosidad_Real,
                Dias_mora,
                Avance_Pago_Plazo,
                Numero_amortizaciones,
                Num_cuotas_pagadas,
                Saldo_total_capital,
                Saldo_para_liquidar_hoy AS Adeudo_total,
                Monto_otorgado,
                Rango_Monto,
                Sucursal,
                Gestor_Asignado
             FROM tbl_segundometro_histo
             WHERE id_credito = :id
             ORDER BY fecha_hora_insert DESC, id_segundometro_histo DESC
             LIMIT 1",
            ['id' => $idCredito]
        );

        return $credito ? ['credito' => $credito, 'origen' => 'historico'] : null;
    }

    /**
     * Recupera la ultima fotografia persistida al crear un convenio.
     */
    private static function _obtenerSnapshotConvenio(Database $db, int $idCredito): ?array
    {
        return $db->queryOne(
            "SELECT
                id_credito,
                nombre_cliente,
                bucket_morosidad_real,
                dias_mora,
                avance_pago_plazo,
                adeudo_total_original,
                estatus
             FROM convenio_cliente
             WHERE id_credito = :id
             ORDER BY id DESC
             LIMIT 1",
            ['id' => $idCredito]
        ) ?: null;
    }

    private static function _creditoDesdeSnapshotConvenio(array $convenio): array
    {
        return [
            'Id_credito'            => $convenio['id_credito'],
            'Nombre_cliente'        => $convenio['nombre_cliente'],
            'Bucket_Morosidad_Real' => $convenio['bucket_morosidad_real'],
            'Dias_mora'             => $convenio['dias_mora'],
            'Avance_Pago_Plazo'     => $convenio['avance_pago_plazo'],
            'Numero_amortizaciones' => null,
            'Num_cuotas_pagadas'    => null,
            'Adeudo_total'          => $convenio['adeudo_total_original'],
            'Saldo_total_capital'   => $convenio['adeudo_total_original'],
            'Monto_otorgado'        => null,
            'Rango_Monto'           => null,
            'Sucursal'              => null,
            'Gestor_Asignado'       => null,
        ];
    }

    // ─────────────────────────────────────────────
    // BÚSQUEDA DE CRÉDITO
    // ─────────────────────────────────────────────

    /**
     * Busca créditos por ID o nombre de cliente en tbl_segundometro_semana.
     */
    public static function buscarCredito($termino)
    {
        try {
            $db = new DatabaseSegundometro();
            $termino = trim($termino);

            if (is_numeric($termino)) {
                $fuente = self::_obtenerCreditoSegundometro($db, (int) $termino);
                if ($fuente) {
                    $rows = [$fuente['credito']];
                } else {
                    $snapshot = self::_obtenerSnapshotConvenio(new Database(), (int) $termino);
                    $rows = $snapshot ? [self::_creditoDesdeSnapshotConvenio($snapshot)] : [];
                }
            } else {
                $rows = $db->queryAll(
                    "SELECT Id_credito, Nombre_cliente, Bucket_Morosidad_Real, Dias_mora
                     FROM tbl_segundometro_semana
                     WHERE Nombre_cliente LIKE :nombre
                     LIMIT 10",
                    ['nombre' => '%' . $termino . '%']
                );

                if (!$rows) {
                    $rows = $db->queryAll(
                        "SELECT
                            h.id_credito AS Id_credito,
                            h.Nombre_cliente,
                            h.Bucket_Morosidad_Real,
                            h.Dias_mora
                         FROM tbl_segundometro_histo h
                         INNER JOIN (
                            SELECT id_credito, MAX(id_segundometro_histo) AS id_ultimo
                            FROM tbl_segundometro_histo
                            WHERE Nombre_cliente LIKE :nombre_hist
                            GROUP BY id_credito
                         ) ult ON ult.id_ultimo = h.id_segundometro_histo
                         LIMIT 10",
                        ['nombre_hist' => '%' . $termino . '%']
                    );
                }

                if (!$rows) {
                    $dbLocal = new Database();
                    $snapshots = $dbLocal->queryAll(
                        "SELECT cc.id_credito, cc.nombre_cliente, cc.bucket_morosidad_real,
                                cc.dias_mora, cc.avance_pago_plazo, cc.adeudo_total_original, cc.estatus
                         FROM convenio_cliente cc
                         INNER JOIN (
                            SELECT id_credito, MAX(id) AS id_ultimo
                            FROM convenio_cliente
                            WHERE nombre_cliente LIKE :nombre_local
                            GROUP BY id_credito
                         ) ult ON ult.id_ultimo = cc.id
                         LIMIT 10",
                        ['nombre_local' => '%' . $termino . '%']
                    );
                    $rows = array_map(
                        function (array $snapshot) {
                            return self::_creditoDesdeSnapshotConvenio($snapshot);
                        },
                        $snapshots ?: []
                    );
                }
            }

            return self::resultado(true, 'Búsqueda completada.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error en búsqueda.', [], $e->getMessage());
        }
    }

    /**
     * Devuelve los datos completos de un crédito necesarios para calcular ofertas.
     */
    public static function getCreditoDetalle($id_credito)
    {
        try {
            $db = new DatabaseSegundometro();
            $fuente = self::_obtenerCreditoSegundometro($db, (int) $id_credito);
            $row = $fuente['credito'] ?? null;

            if (!$row) {
                $snapshot = self::_obtenerSnapshotConvenio(new Database(), (int) $id_credito);
                $row = $snapshot ? self::_creditoDesdeSnapshotConvenio($snapshot) : null;
            }

            if (!$row) {
                return self::resultado(false, 'Crédito no encontrado.');
            }
            return self::resultado(true, 'Crédito encontrado.', $row);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener crédito.', null, $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // OFERTAS ELEGIBLES
    // ─────────────────────────────────────────────

    /**
     * Dado un id_credito, devuelve todas las ofertas activas que aplican
     * según bucket, avance_pago y reglas de cada producto.
     */
    public static function getOfertasElegibles($id_credito)
{
    try {
        $dbSeg = new DatabaseSegundometro();
        $db    = new Database();

        // 1. Datos del crédito
        $fuenteCredito = self::_obtenerCreditoSegundometro($dbSeg, (int) $id_credito);
        $credito = $fuenteCredito['credito'] ?? null;
        $convenioPrevio = null;

        if (!$fuenteCredito || $fuenteCredito['origen'] !== 'semana') {
            $convenioPrevio = self::_obtenerSnapshotConvenio($db, (int) $id_credito);
        }

        if (!$credito && $convenioPrevio) {
            $credito = self::_creditoDesdeSnapshotConvenio($convenioPrevio);
        }

        if ($credito && $convenioPrevio && ($convenioPrevio['estatus'] ?? '') === 'completado') {
            return self::resultado(true, 'OK', [
                'credito'              => $credito,
                'ofertas'              => [],
                'ofertas_reactivables' => [],
                'elegible'             => false,
                'razon'                => 'convenio_completado',
                'productos_bloqueados' => [],
            ]);
        }

        if (!$credito) {
            // No existe en las fuentes operativas ni en el respaldo local de convenios.
            return self::resultado(false, 'Crédito no encontrado.');
        }

        $bucket        = $credito['Bucket_Morosidad_Real'] ?? '';
        $avancePagoStr = $credito['Avance_Pago_Plazo'] ?? '';
        $avancePago    = 0;
        if (preg_match('/^(\d+)/', $avancePagoStr, $m)) {
            $avancePago = (int) $m[1];
        }
        $adeudoTotal = (float) ($credito['Adeudo_total']       ?? 0);
        $saldoCapital = (float) ($credito['Saldo_total_capital'] ?? 0);

        // Bucket requerido para ofertas normales; la reactivacion es una excepcion auditada.
        $bucketElegible = in_array($bucket, self::$BUCKETS_ELEGIBLES);
        // 2. Productos con historial previo. Reactivar es una excepcion de negocio:
        // no depende del estatus anterior; el permiso especial decide si procede.
        $bloqueadosRows = $db->queryAll(
            "SELECT cc.id_producto_convenio
             FROM convenio_cliente cc
             INNER JOIN (
                SELECT id_producto_convenio, MAX(id) AS id_ultimo
                FROM convenio_cliente
                WHERE id_credito = :id_sub
                  AND estatus <> 'activo'
                GROUP BY id_producto_convenio
             ) ult ON ult.id_ultimo = cc.id
             WHERE cc.id_credito = :id_outer
               AND cc.estatus = 'cancelado'",
            ['id_sub' => (int) $id_credito, 'id_outer' => (int) $id_credito]
        );
        $productosBloqueados = $bloqueadosRows
            ? array_map('intval', array_column($bloqueadosRows, 'id_producto_convenio'))
            : [];
        $reactivacionesAprobadas = self::_getReactivacionesAprobadasMap($db, (int) $id_credito);
        $reactivacionesAbiertas = self::_getReactivacionesAbiertasMap($db, (int) $id_credito);

        // 3. Productos activos del catálogo
        $productos = $db->queryAll(
            "SELECT
                pc.id,
                pc.nombre,
                pc.tipo_calendario,
                pcd.id                    AS id_detalle,
                pcd.porcentaje_descuento,
                pcd.base_calculo,
                pcd.pago_inicial,
                pcd.porcentaje_pago_inicial,
                pcd.pago_inicial_momento,
                pcd.periodo_inicio,
                pcd.periodo_fin,
                pcd.buckets_aplicables,
                pcd.avance_pago_minimo
             FROM producto_convenio pc
             INNER JOIN producto_convenio_detalle pcd
                    ON pcd.id_producto_convenio = pc.id
             WHERE pc.activo = 1
             ORDER BY pc.id"
        );

        if (!$productos) {
            return self::resultado(false, 'No hay productos configurados.');
        }

        $origenesReactivables = [];
        foreach (self::_getUltimosConveniosPorProducto($db, (int) $id_credito) as $origenReact) {
            $origenesReactivables[(int) $origenReact['id_producto_convenio']] = $origenReact;
        }

        // 4. Filtrar ofertas elegibles
        $ofertas = [];
        $ofertasReactivables = [];
        foreach ($productos as $prod) {

            // Bloqueo permanente por incumplimiento
            $idProdActual = (int) $prod['id'];
            $reactivacion = $reactivacionesAprobadas[$idProdActual] ?? null;
            $reactivacionAbierta = $reactivacionesAbiertas[$idProdActual] ?? null;
            $estaBloqueado = in_array($idProdActual, $productosBloqueados, true);

            $bucketsProducto = array_map('trim', explode(',', $prod['buckets_aplicables']));
            $cumpleReglasProducto = $bucketElegible && in_array($bucket, $bucketsProducto);

            // Validar bucket
            if (!in_array($bucket, $bucketsProducto)) {
                $cumpleReglasProducto = false;
            }

            // Validar avance de pago mínimo
            if ($prod['avance_pago_minimo'] !== null && $avancePago < (float) $prod['avance_pago_minimo']) {
                $cumpleReglasProducto = false;
            }

            // Calcular montos
            $baseCalculo    = $prod['base_calculo'];
            $montoBase      = ($baseCalculo === 'saldo_total_capital') ? $saldoCapital : $adeudoTotal;
            $pct            = (float) $prod['porcentaje_descuento'];
            $descuentoMonto = round($montoBase * ($pct / 100), 2);
            $totalAPagar    = round($montoBase - $descuentoMonto, 2);

            // Pago inicial
            $pagoInicialMonto = null;
            if ($prod['pago_inicial'] === 'Si' && $prod['porcentaje_pago_inicial']) {
                $pctInicial = (float) $prod['porcentaje_pago_inicial'];
                if ($prod['pago_inicial_momento'] === 'antes') {
                    $pagoInicialMonto = round($montoBase * ($pctInicial / 100), 2);
                    $totalAPagar      = round($totalAPagar - $pagoInicialMonto, 2);
                } else {
                    $pagoInicialMonto = round($totalAPagar * ($pctInicial / 100), 2);
                    $totalAPagar      = round($totalAPagar - $pagoInicialMonto, 2);
                }
            }

            $semanasMax = self::calcularPlazoMaximo($db, $prod['id'], $adeudoTotal, $prod['periodo_fin']);

            $oferta = [
                'id_producto'          => $idProdActual,
                'id_detalle'           => $prod['id_detalle'],
                'nombre'               => $prod['nombre'],
                'tipo_calendario'      => $prod['tipo_calendario'] ?? 'semanal',
                'porcentaje_descuento' => $pct,
                'base_calculo'         => $baseCalculo,
                'monto_base'           => $montoBase,
                'descuento_monto'      => $descuentoMonto,
                'total_a_pagar'        => $totalAPagar,
                'pago_inicial'         => $prod['pago_inicial'],
                'pago_inicial_monto'   => $pagoInicialMonto,
                'pago_inicial_momento' => $prod['pago_inicial_momento'],
                'periodo_inicio'       => (int) $prod['periodo_inicio'],
                'periodo_fin_producto' => (int) $prod['periodo_fin'],
                'semanas_max'          => $semanasMax,
                'buckets_aplicables'   => $bucketsProducto,
                'reactivado'           => (bool) $reactivacion,
                'id_peticion_reactivacion' => $reactivacion ? (int) $reactivacion['id'] : null,
                'id_convenio_origen'   => $reactivacion ? (int) $reactivacion['id_convenio_origen'] : null,
                'reactivacion_numero'  => $reactivacion ? (int) $reactivacion['reactivacion_numero'] : null,
                'reactivacion_estado'  => $reactivacionAbierta ? (string) $reactivacionAbierta['estatus'] : null,
            ];

            if (isset($origenesReactivables[$idProdActual]) && !$reactivacion && !$reactivacionAbierta) {
                $oferta['reactivable'] = true;
                $ofertasReactivables[$idProdActual] = $oferta;
            }

            if ($estaBloqueado && !$reactivacion) {
                if (!$reactivacionAbierta && !isset($ofertasReactivables[$idProdActual])) {
                    $oferta['reactivable'] = true;
                    $ofertasReactivables[$idProdActual] = $oferta;
                }
                continue;
            }

            if (!$cumpleReglasProducto && !$reactivacion) {
                continue;
            }

            $ofertas[] = $oferta;
        }

        $productosBloqueadosVisibles = array_values(array_filter(
            $productosBloqueados,
            function ($idProd) use ($reactivacionesAbiertas) {
                return !isset($reactivacionesAbiertas[(int) $idProd]);
            }
        ));

        return self::resultado(true, 'Ofertas calculadas.', [
            'credito'              => $credito,
            'ofertas'              => $ofertas,
            'ofertas_reactivables' => array_values($ofertasReactivables),
            'elegible'             => count($ofertas) > 0,
            'productos_bloqueados' => $productosBloqueadosVisibles,
            'reactivado'           => count($reactivacionesAprobadas) > 0,
            'reactivaciones'       => array_values($reactivacionesAprobadas),
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al calcular ofertas.', null, $e->getMessage());
    }
}

    /**
     * Obtiene el máximo de semanas para un producto según el monto del adeudo_total.
     * Si no hay fila en producto_convenio_plazos_monto, usa periodo_fin del producto.
     */
    private static function calcularPlazoMaximo($db, $id_producto, $adeudoTotal, $periodoFinProducto)
    {
        $row = $db->queryOne(
            "SELECT semanas_max
             FROM producto_convenio_plazos_monto
             WHERE id_producto_convenio = :id
               AND monto_desde <= :monto_desde
               AND (monto_hasta >= :monto_hasta OR monto_hasta IS NULL)
             ORDER BY monto_desde DESC
             LIMIT 1",
            ['id' => $id_producto, 'monto_desde' => $adeudoTotal, 'monto_hasta' => $adeudoTotal]
        );

        return $row ? (int) $row['semanas_max'] : (int) $periodoFinProducto;
    }

    private static function _getReactivacionesAprobadasMap(Database $db, int $idCredito): array
    {
        $rows = $db->queryAll(
            "SELECT crp.*,
                    (
                        SELECT COUNT(*)
                        FROM convenio_reactivacion_peticion crp2
                        WHERE crp2.id_credito = crp.id_credito
                          AND crp2.id_producto_convenio = crp.id_producto_convenio
                          AND crp2.estatus IN ('aprobada', 'ejecutada')
                          AND crp2.id <= crp.id
                    ) AS reactivacion_numero
             FROM convenio_reactivacion_peticion crp
             WHERE crp.id_credito = :id
               AND crp.estatus = 'aprobada'
             ORDER BY crp.fecha_resolucion DESC, crp.fecha_solicitud DESC",
            ['id' => $idCredito]
        );

        $map = [];
        foreach ($rows ?: [] as $row) {
            $prod = (int) $row['id_producto_convenio'];
            if (!isset($map[$prod])) {
                $map[$prod] = $row;
            }
        }
        return $map;
    }

    private static function _getReactivacionesAbiertasMap(Database $db, int $idCredito): array
    {
        $rows = $db->queryAll(
            "SELECT crp.*,
                    (
                        SELECT COUNT(*)
                        FROM convenio_reactivacion_peticion crp2
                        WHERE crp2.id_credito = crp.id_credito
                          AND crp2.id_producto_convenio = crp.id_producto_convenio
                          AND crp2.estatus IN ('aprobada', 'ejecutada')
                          AND crp2.id <= crp.id
                    ) AS reactivacion_numero
             FROM convenio_reactivacion_peticion crp
             WHERE crp.id_credito = :id
               AND crp.estatus IN ('pendiente', 'aprobada')
             ORDER BY crp.fecha_resolucion DESC, crp.fecha_solicitud DESC",
            ['id' => $idCredito]
        );

        $map = [];
        foreach ($rows ?: [] as $row) {
            $prod = (int) $row['id_producto_convenio'];
            if (!isset($map[$prod])) {
                $map[$prod] = $row;
            }
        }
        return $map;
    }

    private static function _getUltimoConvenioProducto(Database $db, int $idCredito, int $idProducto): ?array
    {
        return $db->queryOne(
            "SELECT cc.*, pc.nombre AS nombre_producto
             FROM convenio_cliente cc
             INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
             WHERE cc.id_credito = :id
               AND cc.id_producto_convenio = :prod
               AND cc.estatus <> 'activo'
             ORDER BY cc.fecha_alta DESC, cc.id DESC
             LIMIT 1",
            ['id' => $idCredito, 'prod' => $idProducto]
        );
    }

    private static function _getUltimosConveniosPorProducto(Database $db, int $idCredito): array
    {
        $rows = $db->queryAll(
            "SELECT cc.*, pc.nombre AS nombre_producto
             FROM convenio_cliente cc
             INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
             WHERE cc.id_credito = :id
               AND cc.estatus <> 'activo'
             ORDER BY cc.fecha_alta DESC, cc.id DESC",
            ['id' => $idCredito]
        );

        $porProducto = [];
        foreach ($rows ?: [] as $row) {
            $prod = (int) $row['id_producto_convenio'];
            if (!isset($porProducto[$prod])) {
                $porProducto[$prod] = $row;
            }
        }
        return array_values($porProducto);
    }

    private static function _resolverReactivacionAprobada(Database $db, array $datos): ?array
    {
        $idCredito = (int) ($datos['id_credito'] ?? 0);
        $idProducto = (int) ($datos['id_producto_convenio'] ?? 0);
        $idPeticion = (int) ($datos['id_peticion_reactivacion'] ?? 0);

        if ($idCredito <= 0 || $idProducto <= 0) {
            return null;
        }
        if (!self::_tablaTieneColumna($db, 'convenio_reactivacion_peticion', 'estatus')) {
            return null;
        }

        $wherePeticion = '';
        $params = ['id' => $idCredito, 'prod' => $idProducto];
        if ($idPeticion > 0) {
            $wherePeticion = ' AND crp.id = :peticion';
            $params['peticion'] = $idPeticion;
        }

        return $db->queryOne(
            "SELECT crp.*,
                    (
                        SELECT COUNT(*)
                        FROM convenio_reactivacion_peticion crp2
                        WHERE crp2.id_credito = crp.id_credito
                          AND crp2.id_producto_convenio = crp.id_producto_convenio
                          AND crp2.estatus IN ('aprobada', 'ejecutada')
                          AND crp2.id <= crp.id
                    ) AS reactivacion_numero
             FROM convenio_reactivacion_peticion crp
             WHERE crp.id_credito = :id
               AND crp.id_producto_convenio = :prod
               AND crp.estatus = 'aprobada'
               $wherePeticion
             ORDER BY crp.fecha_resolucion DESC, crp.fecha_solicitud DESC
             LIMIT 1",
            $params
        );
    }

    private static function _tablaExiste(Database $db, string $tabla): bool
    {
        $row = $db->queryOne("SHOW TABLES LIKE :tabla", ['tabla' => $tabla]);
        return (bool) $row;
    }

    private static function _tablaTieneColumna(Database $db, string $tabla, string $columna): bool
    {
        if (!self::_tablaExiste($db, $tabla)) {
            return false;
        }
        $row = $db->queryOne("SHOW COLUMNS FROM {$tabla} LIKE :col", ['col' => $columna]);
        return (bool) $row;
    }

    private static function _valoresEnumColumna(Database $db, string $tabla, string $columna): array
    {
        if (!self::_tablaTieneColumna($db, $tabla, $columna)) {
            return [];
        }

        $row = $db->queryOne("SHOW COLUMNS FROM {$tabla} LIKE :col", ['col' => $columna]);
        $tipo = (string) ($row['Type'] ?? $row['type'] ?? '');
        if (!preg_match_all("/'((?:[^'\\\\]|\\\\.)*)'/", $tipo, $matches)) {
            return [];
        }

        return array_map(
            fn($v) => str_replace("\\'", "'", $v),
            $matches[1]
        );
    }

    private static function _resolverValorEnum(Database $db, string $tabla, string $columna, array $preferidos): ?string
    {
        $valores = self::_valoresEnumColumna($db, $tabla, $columna);
        if (!$valores) {
            return $preferidos[0] ?? null;
        }

        foreach ($preferidos as $valor) {
            if (in_array($valor, $valores, true)) {
                return $valor;
            }
        }

        return null;
    }

    private static function _marcarConvenioReactivado(Database $db, int $idConvenio, ?array $reactivacion, array $datos): void
    {
        if (!$reactivacion) {
            return;
        }

        $usuario = (string) ($datos['usuario_alta'] ?? 'sistema');
        $motivo = (string) ($reactivacion['motivo_solicitud'] ?? $reactivacion['comentario_resolucion'] ?? '');
        $reactivacionNumero = (int) ($reactivacion['reactivacion_numero'] ?? 1);

        $setsConvenio = [];
        $paramsConvenio = ['id' => $idConvenio];

        if (self::_tablaTieneColumna($db, 'convenio_cliente', 'es_reactivado')) {
            $setsConvenio[] = 'es_reactivado = 1';
        }
        if (self::_tablaTieneColumna($db, 'convenio_cliente', 'id_convenio_origen')) {
            $setsConvenio[] = 'id_convenio_origen = :origen';
            $paramsConvenio['origen'] = (int) $reactivacion['id_convenio_origen'];
        }
        if (self::_tablaTieneColumna($db, 'convenio_cliente', 'id_peticion_reactivacion')) {
            $setsConvenio[] = 'id_peticion_reactivacion = :peticion_cc';
            $paramsConvenio['peticion_cc'] = (int) $reactivacion['id'];
        }
        if (self::_tablaTieneColumna($db, 'convenio_cliente', 'reactivacion_numero')) {
            $setsConvenio[] = 'reactivacion_numero = :numero';
            $paramsConvenio['numero'] = $reactivacionNumero;
        }
        if (self::_tablaTieneColumna($db, 'convenio_cliente', 'motivo_reactivacion')) {
            $setsConvenio[] = 'motivo_reactivacion = :motivo';
            $paramsConvenio['motivo'] = mb_substr($motivo, 0, 300);
        }
        if (self::_tablaTieneColumna($db, 'convenio_cliente', 'usuario_reactiva')) {
            $setsConvenio[] = 'usuario_reactiva = :usuario_cc';
            $paramsConvenio['usuario_cc'] = $usuario;
        }
        if (self::_tablaTieneColumna($db, 'convenio_cliente', 'fecha_reactivacion')) {
            $setsConvenio[] = 'fecha_reactivacion = NOW()';
        }

        if ($setsConvenio) {
            $db->CRUD(
                "UPDATE convenio_cliente SET " . implode(', ', $setsConvenio) . " WHERE id = :id",
                $paramsConvenio
            );
        }

        $setsPeticion = ["estatus = 'ejecutada'"];
        $paramsPeticion = [
            'peticion' => (int) $reactivacion['id'],
        ];

        if (self::_tablaTieneColumna($db, 'convenio_reactivacion_peticion', 'id_convenio_nuevo')) {
            $setsPeticion[] = 'id_convenio_nuevo = :nuevo';
            $paramsPeticion['nuevo'] = $idConvenio;
        }
        if (self::_tablaTieneColumna($db, 'convenio_reactivacion_peticion', 'usuario_resuelve')) {
            $setsPeticion[] = 'usuario_resuelve = COALESCE(usuario_resuelve, :usuario)';
            $paramsPeticion['usuario'] = $usuario;
        }
        if (self::_tablaTieneColumna($db, 'convenio_reactivacion_peticion', 'fecha_resolucion')) {
            $setsPeticion[] = 'fecha_resolucion = COALESCE(fecha_resolucion, NOW())';
        }
        if (self::_tablaTieneColumna($db, 'convenio_reactivacion_peticion', 'comentario_resolucion')) {
            $setsPeticion[] = "comentario_resolucion = COALESCE(comentario_resolucion, 'Reactivacion consumida al generar convenio')";
        }

        if (self::_tablaTieneColumna($db, 'convenio_reactivacion_peticion', 'estatus')) {
            $db->CRUD(
                "UPDATE convenio_reactivacion_peticion SET " . implode(', ', $setsPeticion) . "
                 WHERE id = :peticion
                   AND estatus = 'aprobada'",
                $paramsPeticion
            );
        }
    }

    private static function _generarAmortizacionSemanal(Database $db, int $idConvenio, int $semanas, string $fechaPrimerPago, float $totalAPagar, float $pagoSemanal): array
    {
        $saldoActual = $totalAPagar;

        for ($s = 1; $s <= $semanas; $s++) {
            $fechaPago   = date('Y-m-d', strtotime($fechaPrimerPago . ' +' . (($s - 1) * 7) . ' days'));
            $capital     = ($s < $semanas) ? $pagoSemanal : $saldoActual;
            $saldoActual = round($saldoActual - $capital, 2);
            if ($saldoActual < 0) $saldoActual = 0;

            $db->CRUD(
                "INSERT INTO convenio_cliente_amortizacion
                    (id_convenio_cliente, numero_semana, fecha_pago, pago_semanal, capital, saldo_restante)
                 VALUES (:id, :num, :fecha, :pago, :capital, :saldo)",
                [
                    'id'      => $idConvenio,
                    'num'     => $s,
                    'fecha'   => $fechaPago,
                    'pago'    => $pagoSemanal,
                    'capital' => $capital,
                    'saldo'   => $saldoActual,
                ]
            );
        }

        return $db->queryAll(
            "SELECT * FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id
             ORDER BY numero_semana",
            ['id' => $idConvenio]
        );
    }

    // ─────────────────────────────────────────────
    // GUARDAR CONVENIO
    // ─────────────────────────────────────────────

    /**
     * Guarda el convenio acordado y genera la tabla de amortización.
     *
     * $datos esperado:
     *   id_credito, id_producto_convenio, id_producto_convenio_detalle,
     *   nombre_cliente, bucket_morosidad_real, dias_mora, avance_pago_plazo,
     *   adeudo_total_original, porcentaje_descuento, descuento_monto,
     *   total_a_pagar, pago_inicial_monto (null si no aplica),
     *   numero_semanas, pago_semanal,
     *   fecha_acuerdo (Y-m-d),
     *   usuario_alta,
     *   tipo_calendario ('semanal' | 'libre'),
     *   fechas_pagos (JSON solo si tipo_calendario='libre'):
     *     [{"fecha":"Y-m-d","monto":0.00}, ...]
     */
    public static function guardarConvenio($datos)
    {
        $db = null;
        try {
            $db = new Database(); // __SPARTA_SECRET_REDACTED__: tablas de convenios

            // Verificar que no haya convenio activo
            $activo = $db->queryOne(
                "SELECT id FROM convenio_cliente
                 WHERE id_credito = :id AND estatus = 'activo'
                 LIMIT 1",
                ['id' => (int) $datos['id_credito']]
            );

            if ($activo) {
                return self::resultado(false, 'Este crédito ya tiene un convenio activo.');
            }

            $reactivacion = self::_resolverReactivacionAprobada($db, $datos);

            $tipoCalendario = isset($datos['tipo_calendario']) ? $datos['tipo_calendario'] : 'semanal';
            $fechaAcuerdo   = $datos['fecha_acuerdo'];

            // ── Modo LIBRE: fechas y montos explícitos por pago ────────────────
            if ($tipoCalendario === 'libre') {
                $pagosRaw = isset($datos['fechas_pagos']) ? $datos['fechas_pagos'] : '';
                $pagos    = json_decode($pagosRaw, true);

                if (!is_array($pagos) || count($pagos) === 0) {
                    return self::resultado(false, 'Se requieren las fechas y montos de los pagos.');
                }

                $semanas         = count($pagos);
                $fechaPrimerPago = $pagos[0]['fecha'];
                $fechaUltimoPago = $pagos[$semanas - 1]['fecha'];
                $totalAPagar     = (float) $datos['total_a_pagar'];
                $pagoPromedio    = $semanas > 0 ? round($totalAPagar / $semanas, 2) : $totalAPagar;

                $pdfAdjunto = isset($datos['pdf_adjunto']) ? $datos['pdf_adjunto'] : null;
                $baseCalculo = isset($datos['base_calculo']) && in_array($datos['base_calculo'], ['saldo_total_capital', 'interes', 'adeudo_total'])
                    ? $datos['base_calculo'] : null;

                $db->beginTransaction();

                $ok = $db->CRUD(
                    "INSERT INTO convenio_cliente (
                        id_credito, id_producto_convenio, id_producto_convenio_detalle,
                        nombre_cliente, bucket_morosidad_real, dias_mora, avance_pago_plazo,
                        adeudo_total_original, porcentaje_descuento, descuento_monto,
                        total_a_pagar, monto_adicional, pago_inicial_monto, numero_semanas, pago_semanal,
                        fecha_acuerdo, fecha_primer_pago, fecha_ultimo_pago,
                        tipo_calendario, estatus, usuario_alta, pdf_adjunto, base_calculo, id_celula
                    ) VALUES (
                        :id_credito, :id_producto, :id_detalle,
                        :nombre_cliente, :bucket, :dias_mora, :avance_pago,
                        :adeudo_original, :pct_descuento, :descuento_monto,
                        :total_pagar, :monto_adicional, :pago_inicial, :num_semanas, :pago_semanal,
                        :fecha_acuerdo, :fecha_primer_pago, :fecha_ultimo_pago,
                        'libre', 'activo', :usuario, :pdf_adjunto, :base_calculo, :id_celula
                    )",
                    [
                        'id_credito'        => (int) $datos['id_credito'],
                        'id_producto'       => (int) $datos['id_producto_convenio'],
                        'id_detalle'        => (int) $datos['id_producto_convenio_detalle'],
                        'nombre_cliente'    => $datos['nombre_cliente'],
                        'bucket'            => $datos['bucket_morosidad_real'],
                        'dias_mora'         => (int) $datos['dias_mora'],
                        'avance_pago'       => $datos['avance_pago_plazo'],
                        'adeudo_original'   => (float) $datos['adeudo_total_original'],
                        'pct_descuento'     => (float) $datos['porcentaje_descuento'],
                        'descuento_monto'   => (float) $datos['descuento_monto'],
                        'total_pagar'       => $totalAPagar,
                        'monto_adicional'   => isset($datos['monto_adicional']) ? (float) $datos['monto_adicional'] : 0.0,
                        'pago_inicial'      => isset($datos['pago_inicial_monto']) ? (float) $datos['pago_inicial_monto'] : null,
                        'num_semanas'       => $semanas,
                        'pago_semanal'      => $pagoPromedio,
                        'fecha_acuerdo'     => $fechaAcuerdo,
                        'fecha_primer_pago' => $fechaPrimerPago,
                        'fecha_ultimo_pago' => $fechaUltimoPago,
                        'usuario'           => $datos['usuario_alta'],
                        'pdf_adjunto'       => $pdfAdjunto,
                        'base_calculo'      => $baseCalculo,
                        'id_celula'         => isset($datos['id_celula']) ? $datos['id_celula'] : null,
                    ]
                );

                if (!$ok) {
                    $db->rollback();
                    return self::resultado(false, 'No se pudo guardar el convenio.');
                }

                $idConvenio  = $db->lastInsertId();
                $saldoActual = $totalAPagar;

                foreach ($pagos as $idx => $pago) {
                    $numPago     = $idx + 1;
                    $montoPago   = round((float) $pago['monto'], 2);
                    $saldoActual = round($saldoActual - $montoPago, 2);
                    if ($saldoActual < 0) $saldoActual = 0;

                    $db->CRUD(
                        "INSERT INTO convenio_cliente_amortizacion
                            (id_convenio_cliente, numero_semana, fecha_pago, pago_semanal, capital, saldo_restante)
                         VALUES (:id, :num, :fecha, :pago, :capital, :saldo)",
                        [
                            'id'      => $idConvenio,
                            'num'     => $numPago,
                            'fecha'   => $pago['fecha'],
                            'pago'    => $montoPago,
                            'capital' => $montoPago,
                            'saldo'   => $saldoActual,
                        ]
                    );
                }

                self::_marcarConvenioReactivado($db, $idConvenio, $reactivacion, $datos);
                $db->commit();

                return self::resultado(true, 'Convenio guardado correctamente.', ['id_convenio' => $idConvenio]);
            }

            // ── Modo SEMANAL: lógica original ──────────────────────────────────
            $fechaPrimerPago = date('Y-m-d', strtotime($fechaAcuerdo . ' +8 days'));
            $semanas         = (int) $datos['numero_semanas'];
            $fechaUltimoPago = date('Y-m-d', strtotime($fechaPrimerPago . ' +' . (($semanas - 1) * 7) . ' days'));

            // Insertar convenio
            $pdfAdjunto = isset($datos['pdf_adjunto']) ? $datos['pdf_adjunto'] : null;
            $baseCalculo = isset($datos['base_calculo']) && in_array($datos['base_calculo'], ['saldo_total_capital', 'interes', 'adeudo_total'])
                ? $datos['base_calculo'] : null;

            $db->beginTransaction();

        $ok = $db->CRUD(
    "INSERT INTO convenio_cliente (
        id_credito, id_producto_convenio, id_producto_convenio_detalle,
        nombre_cliente, bucket_morosidad_real, dias_mora, avance_pago_plazo,
        adeudo_total_original, porcentaje_descuento, descuento_monto,
        total_a_pagar, monto_adicional, pago_inicial_monto, numero_semanas, pago_semanal,
        fecha_acuerdo, fecha_primer_pago, fecha_ultimo_pago, estatus, usuario_alta, pdf_adjunto, base_calculo, id_celula
    ) VALUES (
        :id_credito, :id_producto, :id_detalle,
        :nombre_cliente, :bucket, :dias_mora, :avance_pago,
        :adeudo_original, :pct_descuento, :descuento_monto,
        :total_pagar, :monto_adicional, :pago_inicial, :num_semanas, :pago_semanal,
        :fecha_acuerdo, :fecha_primer_pago, :fecha_ultimo_pago, 'activo', :usuario, :pdf_adjunto, :base_calculo, :id_celula
    )",
    [
        'id_credito'        => (int) $datos['id_credito'],
        'id_producto'       => (int) $datos['id_producto_convenio'],
        'id_detalle'        => (int) $datos['id_producto_convenio_detalle'],
        'nombre_cliente'    => $datos['nombre_cliente'],
        'bucket'            => $datos['bucket_morosidad_real'],
        'dias_mora'         => (int) $datos['dias_mora'],
        'avance_pago'       => $datos['avance_pago_plazo'],
        'adeudo_original'   => (float) $datos['adeudo_total_original'],
        'pct_descuento'     => (float) $datos['porcentaje_descuento'],
        'descuento_monto'   => (float) $datos['descuento_monto'],
        'total_pagar'       => (float) $datos['total_a_pagar'],
        'monto_adicional'   => isset($datos['monto_adicional']) ? (float) $datos['monto_adicional'] : 0.0,
        'pago_inicial'      => isset($datos['pago_inicial_monto']) ? (float) $datos['pago_inicial_monto'] : null,
        'num_semanas'       => $semanas,
        'pago_semanal'      => (float) $datos['pago_semanal'],
        'fecha_acuerdo'     => $fechaAcuerdo,
        'fecha_primer_pago' => $fechaPrimerPago,
        'fecha_ultimo_pago' => $fechaUltimoPago,
        'usuario'           => $datos['usuario_alta'],
        'pdf_adjunto'       => $pdfAdjunto,
        'base_calculo'      => $baseCalculo,
        'id_celula'         => isset($datos['id_celula']) ? $datos['id_celula'] : null,
    ]
);
            if (!$ok) {
                $db->rollback();
                return self::resultado(false, 'No se pudo guardar el convenio.');
            }

            $idConvenio   = $db->lastInsertId();
            $totalAPagar  = (float) $datos['total_a_pagar'];
            $pagoSemanal  = (float) $datos['pago_semanal'];

            self::_generarAmortizacionSemanal($db, $idConvenio, $semanas, $fechaPrimerPago, $totalAPagar, $pagoSemanal);
            self::_marcarConvenioReactivado($db, $idConvenio, $reactivacion, $datos);
            $db->commit();

            return self::resultado(true, 'Convenio guardado correctamente.', ['id_convenio' => $idConvenio]);
        } catch (\Exception $e) {
            if ($db && $db->inTransaction()) {
                $db->rollback();
            }
            return self::resultado(false, 'Error al guardar convenio.', null, $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // CONSULTAS DE CONVENIOS EXISTENTES
    // ─────────────────────────────────────────────

    /**
     * Actualiza el PDF adjunto de un convenio existente.
     */
    public static function actualizarPdfConvenio($idConvenio, $pdfPath)
    {
        try {
            $db = new Database();
            $ok = $db->CRUD(
                "UPDATE convenio_cliente SET pdf_adjunto = :pdf WHERE id = :id",
                ['pdf' => $pdfPath, 'id' => $idConvenio]
            );
            return $ok
                ? self::resultado(true, 'Documento adjuntado correctamente.')
                : self::resultado(false, 'No se pudo actualizar el convenio.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', null, $e->getMessage());
        }
    }

    /**
     * Devuelve el convenio activo de un crédito (si existe) con su amortización.
     */
    // Modificar getConvenioActivo para incluir pdf_adjunto
public static function getConvenioActivo($id_credito)
{
    try {
        $db = new Database();

        $convenio = $db->queryOne(
            "SELECT cc.*, pc.nombre AS nombre_producto
             FROM convenio_cliente cc
             INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
             WHERE cc.id_credito = :id AND cc.estatus IN ('activo', 'completado')
             ORDER BY cc.fecha_alta DESC
             LIMIT 1",
            ['id' => (int) $id_credito]
        );

        if (!$convenio) {
            return self::resultado(true, 'Sin convenio activo.', null);
        }

        // Para convenios ya completados no aplicar lógica de auto-cancelación.
        // Sí cargar la amortización y correr auto-conciliación S2 por si hay
        // semanas que siguen en pendiente_conciliar o pendiente/vencido
        // (ej: convenio recreado desde BD con estatus=completado pero filas sin conciliar).
        if ($convenio['estatus'] === 'completado') {
            $amortCompletada = $db->queryAll(
                "SELECT * FROM convenio_cliente_amortizacion
                 WHERE id_convenio_cliente = :id
                 ORDER BY numero_semana",
                ['id' => (int) $convenio['id']]
            );

            // Verificar si hay semanas que aún requieren conciliación S2
            $hayPendienteConciliar = false;
            foreach ($amortCompletada as $f) {
                if (in_array($f['estatus_pago'], ['pendiente_conciliar', 'pendiente', 'vencido', 'parcial'])) {
                    $hayPendienteConciliar = true;
                    break;
                }
            }

            if ($hayPendienteConciliar) {
                $resultS2c   = self::_getPagosS2Movil((int) $id_credito);
                $rawS2c      = $resultS2c['raw'];
                $porIdPagoC  = $resultS2c['porIdPago'];
                $fechaAcC    = $convenio['fecha_acuerdo'] ?? null;
                $fechaFiltroC = $fechaAcC
                    ? (new \DateTime($fechaAcC))->format('Y-m-d')
                    : null;
                if ($fechaFiltroC) {
                    $rawS2c = array_values(array_filter($rawS2c, function ($p) use ($fechaFiltroC) {
                        $fechaPago = self::_fechaPagoS2Corta($p);
                        return !empty($fechaPago) && $fechaPago >= $fechaFiltroC;
                    }));
                    $porIdPagoC = array_filter($porIdPagoC, function ($p) use ($fechaFiltroC) {
                        $fechaPago = self::_fechaPagoS2Corta($p);
                        return !empty($fechaPago) && $fechaPago >= $fechaFiltroC;
                    });
                }
                $resultadoMapaC    = self::_mapearPagosS2Waterfall($rawS2c, $amortCompletada);
                $mapaC             = $resultadoMapaC['mapa'];
                $mapaMontosC       = $resultadoMapaC['montos'];
                $mapaSecundariosC  = $resultadoMapaC['secundarios'];
                $estadoCuentaS2C   = $resultS2c['estadoCuenta'] ?? null;

                foreach ($amortCompletada as &$filaC) {
                    $numSemC = (int)$filaC['numero_semana'];
                    $idPagoC = $mapaC[$numSemC] ?? null;

                    // Adjuntar datos S2 a la fila para el frontend
                    $filaC['cuota_s2']         = $idPagoC;
                    $filaC['pagos_s2']         = ($idPagoC !== null && isset($porIdPagoC[$idPagoC]))
                        ? [ $porIdPagoC[$idPagoC] ] : [];
                    $filaC['pagos_secundarios'] = $mapaSecundariosC[$numSemC] ?? [];

                    // Solo conciliar filas que aún no están pagadas/canceladas
                    if (!in_array($filaC['estatus_pago'], ['pendiente_conciliar', 'pendiente', 'vencido', 'parcial'])) {
                        // Backfill monto_secundario en filas ya pagadas
                        if ($filaC['estatus_pago'] === 'pagado'
                            && !empty($mapaSecundariosC[$numSemC])
                            && ($filaC['monto_secundario'] === null || (float)$filaC['monto_secundario'] === 0.0)
                        ) {
                            $msBack = round(array_sum(array_column($mapaSecundariosC[$numSemC], 'montoPago')), 2);
                            $db->CRUD(
                                "UPDATE convenio_cliente_amortizacion SET monto_secundario = :ms WHERE id = :id",
                                ['ms' => $msBack, 'id' => (int)$filaC['id']]
                            );
                            $filaC['monto_secundario'] = $msBack;
                        }
                        continue;
                    }

                    if ($idPagoC === null) continue;

                    $pagoS2c    = $porIdPagoC[$idPagoC] ?? null;
                    $fechaPagoC = $pagoS2c ? ($pagoS2c['fechaValor'] ?? date('Y-m-d')) : date('Y-m-d');
                    $montoPagoC = isset($mapaMontosC[$numSemC])
                        ? round((float)$mapaMontosC[$numSemC], 2)
                        : ($pagoS2c ? round((float)($pagoS2c['montoPago'] ?? $filaC['pago_semanal']), 2) : round((float)$filaC['pago_semanal'], 2));

                    $msC = !empty($mapaSecundariosC[$numSemC])
                        ? round(array_sum(array_column($mapaSecundariosC[$numSemC], 'montoPago')), 2)
                        : null;
                    $totalAplicadoC = round($montoPagoC + (float)($msC ?? 0), 2);
                    $cuotaEspC      = self::_montoEsperadoSemanaConvenio($filaC);
                    $estatusC       = ($totalAplicadoC >= $cuotaEspC - 0.01) ? 'pagado' : 'parcial';

                    $db->CRUD(
                        "UPDATE convenio_cliente_amortizacion
                            SET estatus_pago     = :estatus,
                                fecha_pago_real  = :fecha,
                                monto_pagado     = :monto,
                                monto_secundario = :ms
                          WHERE id = :id
                            AND estatus_pago NOT IN ('pagado','cancelado')",
                        ['estatus' => $estatusC, 'fecha' => $fechaPagoC, 'monto' => $montoPagoC, 'ms' => $msC, 'id' => (int)$filaC['id']]
                    );
                    $filaC['estatus_pago']    = $estatusC;
                    $filaC['fecha_pago_real'] = $fechaPagoC;
                    $filaC['monto_pagado']    = $montoPagoC;
                    $filaC['monto_secundario'] = $msC;
                }
                unset($filaC);

                $cerradoPorS2C = self::_estadoCuentaS2EstaSaldado($estadoCuentaS2C)
                    ? self::_cerrarConvenioPorLiquidacionS2($db, $convenio, $amortCompletada, $estadoCuentaS2C)
                    : false;

                // Si ahora todas quedaron pagadas, confirmar convenio como completado en BD
                $todasC = array_reduce($amortCompletada,
                    fn($c, $f) => $c && ($f['estatus_pago'] === 'pagado'), true);
                if (!$cerradoPorS2C && $todasC && !empty($amortCompletada)) {
                    $db->CRUD(
                        "UPDATE convenio_cliente SET estatus = 'completado', fecha_modifica = NOW()
                          WHERE id = :id AND estatus != 'cancelado'",
                        ['id' => (int)$convenio['id']]
                    );
                    $convenio['estatus'] = 'completado';
                }
            } else {
                // Todas las filas ya pagadas — solo adjuntar datos S2 para UI
                foreach ($amortCompletada as &$filaC) {
                    $filaC['cuota_s2']          = null;
                    $filaC['pagos_s2']          = [];
                    $filaC['pagos_secundarios'] = [];
                }
                unset($filaC);
            }

            $convenio['amortizacion']  = $amortCompletada ?: [];
            $convenio['pagos_s2movil'] = [];
            return self::resultado(true, 'Convenio completado.', $convenio);
        }

        // ── Auto-cancelación por incumplimiento (3 días corridos) ──
        // IMPORTANTE: verificar conciliación S2 ANTES de cancelar para evitar
        // marcar como cancelado un convenio cuyo pago sí consta en S2Movil.
        $hoy          = new \DateTime();
        $primerVencida = $db->queryOne(
            "SELECT id, numero_semana, fecha_pago
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id
               AND estatus_pago = 'pendiente'
             ORDER BY numero_semana ASC
             LIMIT 1",
            ['id' => (int) $convenio['id']]
        );

        if ($primerVencida) {
            $fechaPago   = new \DateTime($primerVencida['fecha_pago']);
            $diasVencido = (int) $hoy->diff($fechaPago)->days;
            $yaVencio    = $hoy > $fechaPago;

            if ($yaVencio && $diasVencido > 3) {
                // Antes de cancelar: cruzar con S2 para confirmar incumplimiento real
                $amortParaVerif = $db->queryAll(
                    "SELECT * FROM convenio_cliente_amortizacion
                     WHERE id_convenio_cliente = :id ORDER BY numero_semana",
                    ['id' => (int) $convenio['id']]
                );
                $s2Verif      = self::_getPagosS2Movil((int) $id_credito);
                $rawVerif     = $s2Verif['raw'];
                $s2YaSaldado  = self::_estadoCuentaS2EstaSaldado($s2Verif['estadoCuenta'] ?? null);
                $fechaAcuerdo = $convenio['fecha_acuerdo'] ?? null;
                if ($fechaAcuerdo) {
                    // Los pagos del convenio se consideran desde fecha_acuerdo.
                    $fechaFiltroVerif = (new \DateTime($fechaAcuerdo))->format('Y-m-d');
                    $rawVerif = array_values(array_filter($rawVerif, function ($p) use ($fechaFiltroVerif) {
                        $fechaPago = self::_fechaPagoS2Corta($p);
                        return !empty($fechaPago) && $fechaPago >= $fechaFiltroVerif;
                    }));
                }
                $resultadoVerif  = self::_mapearPagosS2Waterfall($rawVerif, $amortParaVerif);
                $mapaVerif       = $resultadoVerif['mapa'];
                $numSemVencida   = (int) $primerVencida['numero_semana'];
                $pagoConfirmadoS2 = $s2YaSaldado || isset($mapaVerif[$numSemVencida]);

                if ($pagoConfirmadoS2) {
                    // El pago existe en S2 — no cancelar; continuar y mostrar estado conciliado
                } else {
                    $db->CRUD(
                        "UPDATE convenio_cliente SET
                            estatus                   = 'cancelado',
                            fecha_cancelacion         = :fecha,
                            numero_semana_cancelacion = :semana,
                            usuario_cancela           = 'sistema_auto',
                            fecha_modifica            = NOW()
                         WHERE id = :id",
                        [
                            'fecha'  => $hoy->format('Y-m-d'),
                            'semana' => $primerVencida['numero_semana'],
                            'id'     => (int) $convenio['id'],
                        ]
                    );

                    $db->CRUD(
                        "UPDATE convenio_cliente_amortizacion SET
                            estatus_pago = 'cancelado'
                         WHERE id_convenio_cliente = :id
                           AND estatus_pago = 'pendiente'",
                        ['id' => (int) $convenio['id']]
                    );

                    return self::resultado(true, 'Convenio cancelado por incumplimiento.', null);
                }
            }
        }

        $amortizacion = $db->queryAll(
            "SELECT * FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id
             ORDER BY numero_semana",
            ['id' => (int) $convenio['id']]
        );

        if (empty($amortizacion)
            && ($convenio['estatus'] ?? '') === 'activo'
            && (($convenio['tipo_calendario'] ?? 'semanal') !== 'libre')
            && (int) ($convenio['numero_semanas'] ?? 0) > 0
            && !empty($convenio['fecha_primer_pago'])
            && (float) ($convenio['total_a_pagar'] ?? 0) > 0
            && (float) ($convenio['pago_semanal'] ?? 0) > 0
        ) {
            $db->beginTransaction();
            try {
                $amortizacion = self::_generarAmortizacionSemanal(
                    $db,
                    (int) $convenio['id'],
                    (int) $convenio['numero_semanas'],
                    (string) $convenio['fecha_primer_pago'],
                    (float) $convenio['total_a_pagar'],
                    (float) $convenio['pago_semanal']
                );
                $reactivacionPendiente = self::_resolverReactivacionAprobada($db, [
                    'id_credito' => (int) $convenio['id_credito'],
                    'id_producto_convenio' => (int) $convenio['id_producto_convenio'],
                ]);
                self::_marcarConvenioReactivado($db, (int) $convenio['id'], $reactivacionPendiente, [
                    'usuario_alta' => $convenio['usuario_alta'] ?? 'sistema',
                ]);
                $db->commit();
            } catch (\Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollback();
                }
                throw $e;
            }
        }

        $convenio['amortizacion'] = $amortizacion ?: [];

        if (!empty($convenio['pdf_adjunto'])) {
            $convenio['pdf_url'] = $convenio['pdf_adjunto'];
        }

        // ── Enriquecer con pagos reales de S2Movil ──────────────
        $resultadoS2     = self::_getPagosS2Movil((int) $id_credito);
        $pagosS2Indexado = $resultadoS2['indexado'];   // cuota_s2 => pagos[] (compatibilidad)
        $pagosS2Raw      = $resultadoS2['raw'];
        $pagosS2PorId    = $resultadoS2['porIdPago'];  // NUEVO: idPago => pago
        $estadoCuentaS2  = $resultadoS2['estadoCuenta'] ?? null;


                 // Filtrar solo pagos S2 dentro del ámbito del convenio.
                 // FIX #4: se usa (fecha_acuerdo - 7 días) como límite inferior para
                 // tolerar pagos que el cliente realizó en S2 unos días antes de que
                 // el convenio fuera formalmente capturado en el sistema.
         $fechaAcuerdo = $convenio['fecha_acuerdo'] ?? null;
         $fechaFiltroConv = $fechaAcuerdo
             ? (new \DateTime($fechaAcuerdo))->format('Y-m-d')
             : null;
         $pagosS2Raw   = array_filter($pagosS2Raw, function($p) use ($fechaFiltroConv) {
             $fechaPago = self::_fechaPagoS2Corta($p);
             if (!$fechaFiltroConv) return true;
             if (empty($fechaPago)) return false;
             return $fechaPago >= $fechaFiltroConv;
         });
         $pagosS2Raw = array_values($pagosS2Raw);

         // También filtrar porIdPago
         $pagosS2PorId = array_filter($pagosS2PorId, function($p) use ($fechaFiltroConv) {
             $fechaPago = self::_fechaPagoS2Corta($p);
             if (!$fechaFiltroConv) return true;
             if (empty($fechaPago)) return false;
             return $fechaPago >= $fechaFiltroConv;
         });

        // Mapeo por capital acumulado → devuelve ['mapa' => numSem=>idPago, 'montos' => numSem=>montoAcumulado, 'secundarios' => numSem=>[...]]
        $resultadoMapa = self::_mapearPagosS2Waterfall($pagosS2Raw, $amortizacion);
        $mapaCuotas      = $resultadoMapa['mapa'];
        $mapaMontos      = $resultadoMapa['montos'];
        $mapaSecundarios = $resultadoMapa['secundarios'];

        // Enriquecer cada fila con su pago S2 correspondiente
        foreach ($amortizacion as &$fila) {
            $numSemana = (int) $fila['numero_semana'];
            $idPago    = $mapaCuotas[$numSemana] ?? null;

            $fila['cuota_s2'] = $idPago;

            if ($idPago !== null && isset($pagosS2PorId[$idPago])) {
                $fila['pagos_s2'] = [ $pagosS2PorId[$idPago] ];
            } else {
                $fila['pagos_s2'] = [];
            }
            // Pagos secundarios que completaron el déficit de esta semana (ej: $60 que completó el $2,000)
            $fila['pagos_secundarios'] = $mapaSecundarios[$numSemana] ?? [];
        }
        unset($fila);

        // ── Auto-conciliación S2 : actualizar BD sin esperar acción manual ──
        $todasPagadas = true;
        foreach ($amortizacion as &$fila) {
            $numSem = (int) $fila['numero_semana'];
            $idPago = $mapaCuotas[$numSem] ?? null;

            if ($idPago !== null
                && in_array($fila['estatus_pago'], ['pendiente', 'vencido', 'pendiente_conciliar', 'parcial'])
            ) {
                // S2 confirma pago → registrar monto real y determinar estatus.
                // mapaMontos puede contener el monto acumulado de varios pagos S2 combinados
                // (ej: $2,000 + $60 = $2,060 cuando un pago pequeño completó el déficit).
                $pagoS2      = $pagosS2PorId[$idPago] ?? null;
                $fechaPagoS2 = $pagoS2 ? ($pagoS2['fechaValor'] ?? $hoy->format('Y-m-d')) : $hoy->format('Y-m-d');
                // Preferir monto acumulado del mapa; fallback al montoPago del pago primario.
                $montoPagoS2 = isset($mapaMontos[$numSem])
                    ? round((float)$mapaMontos[$numSem], 2)
                    : ($pagoS2 ? round((float)($pagoS2['montoPago'] ?? $fila['pago_semanal']), 2) : round((float)$fila['pago_semanal'], 2));

                // Threshold real: capital de BD cuando sea residual de última semana.
                $cuotaEsperada = self::_montoEsperadoSemanaConvenio($fila);

                // Calcular monto_secundario: suma de los pagos complementarios que completaron el déficit
                $montoSecundario = null;
                if (!empty($mapaSecundarios[$numSem])) {
                    $montoSecundario = round(
                        array_sum(array_column($mapaSecundarios[$numSem], 'montoPago')), 2
                    );
                }
                $totalAplicadoS2 = round($montoPagoS2 + (float)($montoSecundario ?? 0), 2);
                $estatusPagoS2   = ($totalAplicadoS2 >= $cuotaEsperada - 0.01) ? 'pagado' : 'parcial';

                $db->CRUD(
                    "UPDATE convenio_cliente_amortizacion
                        SET estatus_pago     = :estatus,
                            fecha_pago_real  = :fecha,
                            monto_pagado     = :monto,
                            monto_secundario = :ms
                      WHERE id = :id
                        AND estatus_pago NOT IN ('pagado','cancelado')",
                    [
                        'estatus' => $estatusPagoS2,
                        'fecha'   => $fechaPagoS2,
                        'monto'   => $montoPagoS2,
                        'ms'      => $montoSecundario,
                        'id'      => (int) $fila['id'],
                    ]
                );
                $fila['estatus_pago']    = $estatusPagoS2;
                $fila['fecha_pago_real'] = $fechaPagoS2;
                $fila['monto_pagado']    = $montoPagoS2;
                $fila['monto_secundario'] = $montoSecundario;

            } elseif ($idPago === null && $fila['estatus_pago'] === 'pendiente') {
                // Sin pago S2 y fecha vencida → marcar como vencido
                $fechaFilaObj = new \DateTime($fila['fecha_pago']);
                if ($hoy > $fechaFilaObj) {
                    $db->CRUD(
                        "UPDATE convenio_cliente_amortizacion
                            SET estatus_pago = 'vencido'
                          WHERE id = :id
                            AND estatus_pago = 'pendiente'",
                        ['id' => (int) $fila['id']]
                    );
                    $fila['estatus_pago'] = 'vencido';
                }
            }

            // Backfill: row ya pagada con monto_secundario NULL pero hay pagos secundarios
            // detectados en $mapaSecundarios (cargado previo a que se guardara la columna).
            // Corre para TODOS los rows después del if/elseif.
            if ($fila['estatus_pago'] === 'pagado'
                && !empty($mapaSecundarios[$numSem])
                && ($fila['monto_secundario'] === null || (float)$fila['monto_secundario'] === 0.0)
            ) {
                $montoSecBackfill = round(
                    array_sum(array_column($mapaSecundarios[$numSem], 'montoPago')), 2
                );
                $db->CRUD(
                    "UPDATE convenio_cliente_amortizacion
                        SET monto_secundario = :ms
                      WHERE id = :id",
                    ['ms' => $montoSecBackfill, 'id' => (int) $fila['id']]
                );
                $fila['monto_secundario'] = $montoSecBackfill;
            }

            if ($fila['estatus_pago'] !== 'pagado') {
                $todasPagadas = false;
            }
        }
        unset($fila);

        // Si todas las filas quedaron pagadas → completar el convenio
        // FIX #3: guardia extra — no completar si alguna semana no tiene monto confirmado
        // (evita marcar completado cuando el mapa asignó pagos parciales/incorrectos)
        $cerradoPorS2 = self::_estadoCuentaS2EstaSaldado($estadoCuentaS2)
            ? self::_cerrarConvenioPorLiquidacionS2($db, $convenio, $amortizacion, $estadoCuentaS2)
            : false;

        if (!$cerradoPorS2 && $todasPagadas && !empty($amortizacion)) {
            $todasConMonto = array_reduce(
                $amortizacion,
                fn($carry, $f) => $carry && ($f['monto_pagado'] !== null),
                true
            );
            if ($todasConMonto) {
                $db->CRUD(
                    "UPDATE convenio_cliente
                        SET estatus = 'completado', fecha_modifica = NOW()
                      WHERE id = :id AND estatus = 'activo'",
                    ['id' => (int) $convenio['id']]
                );
                $convenio['estatus'] = 'completado';
            }
        }

        $convenio['amortizacion']  = $amortizacion;
        $convenio['pagos_s2movil'] = $pagosS2Indexado;
        $convenio['mapa_cuotas']   = $mapaCuotas;

        return self::resultado(true, 'Convenio encontrado.', $convenio);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al consultar convenio.', null, $e->getMessage());
    }
}

/**
 * Retorna solo statusCredito, fechaLiquidacion y motivo desde S2Movil.
 * Llamada ligera para el banner del módulo de convenios.
 */
public static function getEstatusS2($id_credito)
{
    try {
        $url     = ENDPOINT;
        $payload = json_encode([
            'idCredito'  => (int) $id_credito,
            'fechaCorte' => date('Y-m-d'),
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . TOKEN,
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $ec   = $data['estadoCuenta'] ?? null;

        if (!$ec) {
            return self::resultado(true, 'Sin datos S2.', null);
        }

        return self::resultado(true, 'Estatus S2 obtenido.', [
            'statusCredito'    => $ec['statusCredito']    ?? '',
            'fechaLiquidacion' => $ec['fechaLiquidacion'] ?? null,
            'motivo'           => $ec['motivo']           ?? null,
            'adeudoTotal'      => (float) ($ec['datosSaldos']['adeudoTotal'] ?? 0),
        ]);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al consultar S2.', null);
    }
}

/**
 * Igual que getConvenioActivo pero sin filtrar por estatus.
 * Usado para generar el PDF de cualquier convenio (activo, cancelado, completado).
 */
public static function getConvenioCualquierEstatus($id_credito)
{
    try {
        $db = new Database();

        $convenio = $db->queryOne(
            "SELECT cc.*, pc.nombre AS nombre_producto
             FROM convenio_cliente cc
             INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
             WHERE cc.id_credito = :id
             ORDER BY cc.fecha_alta DESC
             LIMIT 1",
            ['id' => (int) $id_credito]
        );

        if (!$convenio) {
            return self::resultado(true, 'Sin convenio.', null);
        }

        $amortizacion = $db->queryAll(
            "SELECT * FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id
             ORDER BY numero_semana",
            ['id' => (int) $convenio['id']]
        );

        $convenio['amortizacion'] = $amortizacion ?: [];

        if (!empty($convenio['pdf_adjunto'])) {
            $convenio['pdf_url'] = $convenio['pdf_adjunto'];
        }

        return self::resultado(true, 'Convenio encontrado.', $convenio);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al consultar convenio.', null, $e->getMessage());
    }
}

/**
 * Trae datosPagos de S2Movil indexados por numeroCuotaSemanal.
 * Cada entrada puede tener múltiples pagos (sobrantes + pagos normales).
 */
/**
 * Trae datosPagos de S2Movil e indexa por número de cuota S2.
 * Retorna también un mapa fecha→cuota para cruce en amortización.
 */
private static function _getPagosS2Movil($id_credito)
{
    try {
        $url     = ENDPOINT;
        $payload = json_encode([
            'idCredito'  => $id_credito,
            'fechaCorte' => date('Y-m-d'),
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . (defined('TOKEN') ? TOKEN : (getenv('S2_ESTADO_CUENTA_TOKEN') ?: '')),
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data         = json_decode($response, true);
        $estadoCuenta = $data['estadoCuenta'] ?? [];
        $datosPagos   = $estadoCuenta['datosPagos'] ?? [];

        if (empty($datosPagos)) {
            return ['indexado' => [], 'raw' => [], 'porIdPago' => [], 'estadoCuenta' => $estadoCuenta];
        }

        // ── Índice por cuota S2 (compatibilidad con código existente) ──
        $indexado  = [];
        // ── NUEVO: índice por idPago (para el mapeo acumulado) ─────────
        $porIdPago = [];

        foreach ($datosPagos as $pago) {
            $cuotasRaw = $pago['numeroCuotaSemanal'] ?? null;
            if ($cuotasRaw === null) continue;

            $cuotas = is_string($cuotasRaw)
                ? array_map('intval', array_map('trim', explode(',', $cuotasRaw)))
                : [(int) $cuotasRaw];

            $montoPago = (float) ($pago['montoPago'] ?? 0);
            $capital   = (float) ($pago['capital']   ?? 0);
            $sobrante  = round($montoPago - $capital, 2);
            $idPago    = $pago['idPago'] ?? null;

            $entrada = [
                'idPago'        => $idPago,
                'fechaValor'    => $pago['fechaValor']    ?? null,
                'fechaDeposito' => $pago['fechaDeposito'] ?? null,
                'montoPago'     => $montoPago,
                'capital'       => $capital,
                'sobrante'      => $sobrante > 0 ? $sobrante : 0,
                'cuotas'        => $cuotas,
            ];

            // Índice por cuota (sin cambios vs versión original)
            $cuotaPrincipal = $cuotas[0];
            if ($cuotaPrincipal < 1) continue;
            if (!isset($indexado[$cuotaPrincipal])) {
                $indexado[$cuotaPrincipal] = [];
            }
            $indexado[$cuotaPrincipal][] = $entrada;

            // NUEVO: índice por idPago (deduplicado — un idPago = una entrada)
            if ($idPago !== null && !isset($porIdPago[$idPago])) {
                $porIdPago[$idPago] = $entrada;
            }
        }

        return [
            'indexado'  => $indexado,
            'raw'       => $datosPagos,
            'porIdPago' => $porIdPago,   // ← NUEVO
            'estadoCuenta' => $estadoCuenta,
        ];

    } catch (\Exception $e) {
        return ['indexado' => [], 'raw' => [], 'porIdPago' => [], 'estadoCuenta' => []];
    }
}


/**
 * Dado el array raw de datosPagos y la amortización del convenio,
 * determina qué número de cuota S2 corresponde a cada semana del convenio.
 *
 * Estrategia: para cada semana del convenio, busca en datosPagos
 * cuál entrada tiene fechaValor dentro del rango [fecha_pago, fecha_pago+13 días].
 * De esa entrada extrae el primer numeroCuotaSemanal como cuota representativa.
 *
 * Retorna: array [ numero_semana => numero_cuota_s2 ]
 */
private static function _mapearCuotasS2AConvenio(array $rawPagos, array $amortizacion): array
{
    if (empty($rawPagos) || empty($amortizacion)) {
        return ['mapa' => [], 'montos' => [], 'secundarios' => []];
    }

    // ── 1. Ordenar pagos S2 por fechaValor ASC ───────────────────
    usort($rawPagos, function ($a, $b) {
        return strcmp($a['fechaValor'] ?? '9999', $b['fechaValor'] ?? '9999');
    });

    // ── 2. Deduplicar por idPago — sumar montoPago del mismo idPago
    $pagosPorId = [];
    foreach ($rawPagos as $p) {
        $id = $p['idPago'] ?? null;
        if ($id === null) continue;
        if (!isset($pagosPorId[$id])) {
            $pagosPorId[$id] = [
                'idPago'     => $id,
                'fechaValor' => $p['fechaValor'] ?? null,
                'montoPago'  => 0.0,
            ];
        }
        // Usamos montoPago — es el dinero real depositado por el cliente
        $pagosPorId[$id]['montoPago'] += (float)($p['montoPago'] ?? 0);
    }
    $pagosUnicos = array_values($pagosPorId);

    // ── 3. Ordenar semanas ASC ────────────────────────────────────
    usort($amortizacion, fn($a, $b) => (int)$a['numero_semana'] - (int)$b['numero_semana']);

    // ── 4. Derramar montoPago sobre semanas secuencialmente ───────
    //
    //    Lógica: igual que S2Movil aplica internamente.
    //    Cada pago cubre semanas completas ($pago_semanal cada una)
    //    hasta agotar su montoPago. El sobrante pasa al siguiente pago.
    //
    //    Pago 1 ($5,000.00) → 10 semanas completas ($4,890.90) + $109.10 sobrante
    //    Pago 2 ($16,519.00) → retoma sem 11 con $109.10 ya abonado, etc.
    //    Pago 3 ($1.00)      → residuo final

    $mapa           = [];   // numero_semana => idPago (pago principal)
    $mapaMontos     = [];   // numero_semana => monto total acumulado (puede combinar varios pagos)
    $mapaSecundarios = [];  // numero_semana => [ ['idPago'=>..,'montoPago'=>..,'fechaValor'=>..], ... ]
    $sobrante         = 0.0;
    $sobrante_pago_id = null;  // idPago que generó el sobrante actual
    $idxSem           = 0;
    $nSemanas         = count($amortizacion);

    // Seguimiento de déficit: cuando una semana se mapea dentro del 5% de tolerancia pero
    // queda corta (ej: $2,000 vs $2,060 → déficit $60), el siguiente pago PEQUEÑO
    // (≤ déficit + $100) completa esa semana. Pagos GRANDES son nueva semana.
    $deficitSem    = 0.0;
    $deficitNumSem = null;

    foreach ($pagosUnicos as $pago) {
        if ($idxSem >= $nSemanas && $deficitSem <= 0.01) break;

        $disponible = round($pago['montoPago'] + $sobrante, 4);
        $sobrante   = 0.0;

        // ── Paso 1: completar déficit de la semana anterior ───────────────────
        if ($deficitSem > 0.01 && $deficitNumSem !== null) {
            if ($disponible <= $deficitSem + 100.00) {
                // Pago pequeño: va a cubrir el déficit
                $mapaMontos[$deficitNumSem] = round(
                    ($mapaMontos[$deficitNumSem] ?? 0) + $disponible, 4
                );
                // Registrar como pago secundario para mostrar en UI
                $mapaSecundarios[$deficitNumSem][] = [
                    'idPago'     => $pago['idPago'],
                    'montoPago'  => $pago['montoPago'],
                    'fechaValor' => $pago['fechaValor'],
                ];
                $deficitSem -= $disponible;
                $disponible  = 0.0;
                if ($deficitSem <= 1.00) {
                    $deficitSem    = 0.0;
                    $deficitNumSem = null;
                }
                if ($disponible <= 0.001) continue;
            } else {
                // Pago grande: semana previa queda como parcial
                $deficitSem    = 0.0;
                $deficitNumSem = null;
            }
        }

        // ── Paso 2: derramar disponible sobre las semanas siguientes ──────────
        while ($idxSem < $nSemanas && $disponible > 0.001) {
            $semana      = $amortizacion[$idxSem];
            $numSem      = (int)$semana['numero_semana'];
            // Threshold real: capital de BD cuando es significativamente menor que
            // pago_semanal (última semana residual). Guarda: capital 0/null → fallback.
            $psSem  = round((float)$semana['pago_semanal'], 4);
            $capSem = (float)($semana['capital'] ?? 0);
            $pagoSemanal = ($capSem > 0.50 && $capSem < $psSem - 1.00) ? round($capSem, 4) : $psSem;

            // Semana ya pagada en BD: consumir su cuota del disponible para evitar que
            // el mismo pago S2 se reasigne a semanas posteriores en ejecuciones sucesivas.
            // Aun así mapeamos el pago y calculamos el déficit para que los pagos
            // secundarios (ej: $60 que completan un $2,000) queden registrados en
            // $mapaSecundarios y sean visibles en la UI.
            if (($semana['estatus_pago'] ?? '') === 'pagado') {
                $toleranciaPagada = max(1.00, $pagoSemanal * 0.05);
                if ($disponible >= $pagoSemanal - $toleranciaPagada) {
                    // Mapear el pago a esta semana (para que pagos_s2 y pagos_secundarios funcionen)
                    if (!isset($mapa[$numSem])) {
                        $mapa[$numSem]       = $pago['idPago'];
                        $mapaMontos[$numSem] = round(min($disponible, $pagoSemanal), 4);
                    }
                    // Si el pago llegó corto (dentro de tolerancia), registrar déficit
                    // para que el siguiente pago pequeño lo complete en $mapaSecundarios.
                    $deficit = max(0.0, $pagoSemanal - $disponible);
                    if ($deficit > 1.00) {
                        $deficitSem    = $deficit;
                        $deficitNumSem = $numSem;
                    }
                    $disponible = max(0.0, round($disponible - $pagoSemanal, 4));
                    $idxSem++;
                } else {
                    $sobrante         = $disponible;
                    $sobrante_pago_id = $pago['idPago'];
                    $disponible       = 0;
                }
                continue;
            }

            // Tolerancia del 5%: acepta pagos casi completos como cobertura de la semana.
            // Ej: $4,700 vs $4,729.40 (diff 0.62%) → MAPEADO (parcial real).
            // Ej: $60 vs $2,060 (diff 97%) → NO mapeado, sobrante al siguiente pago.
            $tolerancia = max(1.00, $pagoSemanal * 0.05);
            if ($disponible >= $pagoSemanal - $tolerancia) {
                if (!isset($mapa[$numSem])) {
                    $mapa[$numSem]       = $pago['idPago'];
                    $mapaMontos[$numSem] = round(min($disponible, $pagoSemanal), 4);
                }
                // Si quedó corto (dentro del 5%), registrar el déficit para completar
                // con el próximo pago pequeño.
                $deficit = max(0.0, $pagoSemanal - $disponible);
                if ($deficit > 1.00) {
                    $deficitSem    = $deficit;
                    $deficitNumSem = $numSem;
                }
                $disponible = max(0.0, round($disponible - $pagoSemanal, 4));
                $idxSem++;
            } else {
                // No alcanza — sobrante al siguiente pago
                $sobrante         = $disponible;
                $sobrante_pago_id = $pago['idPago'];
                $disponible       = 0;
            }
        }

        if ($disponible > 0.001 && $idxSem < $nSemanas) {
            $sobrante         = $disponible;
            $sobrante_pago_id = $pago['idPago'];
        }
    }

    // Sobrante final: si un pago cubrió sem N completa y quedó sobrante que no alcanzó
    // para sem N+1 (< 95% del pago_semanal), ese sobrante se aplica como pago parcial.
    // Sin este bloque, sem N+1 quedaría con $idPago=null y la conciliación la ignoraría.
    if ($sobrante > 0.001 && $sobrante_pago_id !== null && $idxSem < $nSemanas) {
        $semana = $amortizacion[$idxSem];
        $numSem = (int)$semana['numero_semana'];
        if (!isset($mapa[$numSem]) && ($semana['estatus_pago'] ?? '') !== 'pagado') {
            $mapa[$numSem]       = $sobrante_pago_id;
            $mapaMontos[$numSem] = round($sobrante, 4);
        }
    }

    return ['mapa' => $mapa, 'montos' => $mapaMontos, 'secundarios' => $mapaSecundarios];
}

private static function _fechaPagoS2Corta(array $pago): ?string
{
    $fecha = $pago['fechaValor']
        ?? $pago['fechaDeposito']
        ?? $pago['fechaRegistro']
        ?? null;

    if (empty($fecha)) {
        return null;
    }

    $fechaCorta = substr((string)$fecha, 0, 10);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCorta) ? $fechaCorta : null;
}

private static function _mapearPagosS2Waterfall(array $rawPagos, array $amortizacion): array
{
    if (empty($rawPagos) || empty($amortizacion)) {
        return ['mapa' => [], 'montos' => [], 'secundarios' => []];
    }

    usort($rawPagos, function ($a, $b) {
        $fechaA = self::_fechaPagoS2Corta($a) ?? '9999-12-31';
        $fechaB = self::_fechaPagoS2Corta($b) ?? '9999-12-31';
        $cmp = strcmp($fechaA, $fechaB);
        if ($cmp !== 0) {
            return $cmp;
        }
        return ((int)($a['idPago'] ?? 0)) <=> ((int)($b['idPago'] ?? 0));
    });

    $pagosPorId = [];
    foreach ($rawPagos as $pago) {
        $idPago = $pago['idPago'] ?? null;
        if ($idPago === null) {
            continue;
        }
        if (!isset($pagosPorId[$idPago])) {
            $pagosPorId[$idPago] = $pago;
            $pagosPorId[$idPago]['montoPago'] = 0.0;
            $pagosPorId[$idPago]['fechaValor'] = self::_fechaPagoS2Corta($pago);
        }
        $pagosPorId[$idPago]['montoPago'] += (float)($pago['montoPago'] ?? 0);
    }
    $pagosUnicos = array_values($pagosPorId);

    usort($pagosUnicos, function ($a, $b) {
        $fechaA = self::_fechaPagoS2Corta($a) ?? '9999-12-31';
        $fechaB = self::_fechaPagoS2Corta($b) ?? '9999-12-31';
        $cmp = strcmp($fechaA, $fechaB);
        if ($cmp !== 0) {
            return $cmp;
        }
        return ((int)($a['idPago'] ?? 0)) <=> ((int)($b['idPago'] ?? 0));
    });

    usort($amortizacion, fn($a, $b) => (int)$a['numero_semana'] <=> (int)$b['numero_semana']);

    $mapa = [];
    $mapaMontos = [];
    $mapaSecundarios = [];
    $idxSemana = 0;
    $totalSemanas = count($amortizacion);

    foreach ($pagosUnicos as $pago) {
        $idPago = $pago['idPago'];
        $disponible = round((float)($pago['montoPago'] ?? 0), 4);

        if ($disponible <= 0.001) {
            continue;
        }

        while ($idxSemana < $totalSemanas && $disponible > 0.001) {
            $semana = $amortizacion[$idxSemana];

            if (($semana['estatus_pago'] ?? '') === 'cancelado') {
                $idxSemana++;
                continue;
            }

            $numSemana = (int)$semana['numero_semana'];
            $esperado = self::_montoEsperadoSemanaConvenio($semana);

            if ($esperado <= 0.01) {
                $idxSemana++;
                continue;
            }

            $principalActual = (float)($mapaMontos[$numSemana] ?? 0);
            $secundarioActual = !empty($mapaSecundarios[$numSemana])
                ? (float)array_sum(array_column($mapaSecundarios[$numSemana], 'montoPago'))
                : 0.0;
            $faltante = round($esperado - $principalActual - $secundarioActual, 4);

            if ($faltante <= 0.001) {
                $idxSemana++;
                continue;
            }

            $aplicar = round(min($disponible, $faltante), 4);
            if ($aplicar <= 0.001) {
                break;
            }

            if (!isset($mapa[$numSemana])) {
                $mapa[$numSemana] = $idPago;
                $mapaMontos[$numSemana] = round(($mapaMontos[$numSemana] ?? 0) + $aplicar, 4);
            } elseif ((string)$mapa[$numSemana] === (string)$idPago) {
                $mapaMontos[$numSemana] = round(($mapaMontos[$numSemana] ?? 0) + $aplicar, 4);
            } else {
                $mapaSecundarios[$numSemana][] = [
                    'idPago' => $idPago,
                    'montoPago' => round($aplicar, 2),
                    'fechaValor' => $pago['fechaValor'] ?? null,
                ];
            }

            $disponible = round($disponible - $aplicar, 4);
            $faltante = round($faltante - $aplicar, 4);

            if ($faltante <= 0.001) {
                $idxSemana++;
            } else {
                break;
            }
        }
    }

    return ['mapa' => $mapa, 'montos' => $mapaMontos, 'secundarios' => $mapaSecundarios];
}

// HELPERS: CIERRE POR LIQUIDACION S2

private static function _estadoCuentaS2EstaSaldado(?array $estadoCuenta): bool
{
    if (empty($estadoCuenta)) return false;

    $status = strtolower(trim((string)($estadoCuenta['statusCredito'] ?? '')));
    if ($status === 'saldado') return true;

    $adeudo = (float)($estadoCuenta['datosSaldos']['adeudoTotal'] ?? 0);
    return $adeudo <= 0.01 && !empty($estadoCuenta['fechaLiquidacion']);
}

private static function _fechaLiquidacionS2(?array $estadoCuenta): string
{
    $fecha = trim((string)($estadoCuenta['fechaLiquidacion'] ?? ''));
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) ? $fecha : date('Y-m-d');
}

private static function _montoEsperadoSemanaConvenio(array $fila): float
{
    $pagoSemanal = round((float)($fila['pago_semanal'] ?? 0), 2);
    $capital     = (float)($fila['capital'] ?? 0);

    return ($capital > 0.50 && $capital < $pagoSemanal - 1.00)
        ? round($capital, 2)
        : $pagoSemanal;
}

private static function _cerrarConvenioPorLiquidacionS2($db, array &$convenio, array &$amortizacion, ?array $estadoCuenta): bool
{
    if (empty($amortizacion) || ($convenio['estatus'] ?? '') === 'cancelado') {
        return false;
    }

    $fechaCierre = self::_fechaLiquidacionS2($estadoCuenta);

    foreach ($amortizacion as &$fila) {
        if (($fila['estatus_pago'] ?? '') === 'cancelado') {
            continue;
        }

        $esperado       = self::_montoEsperadoSemanaConvenio($fila);
        $montoPrincipal = max(0.0, (float)($fila['monto_pagado'] ?? 0));
        $montoSecActual = max(0.0, (float)($fila['monto_secundario'] ?? 0));
        $faltante       = max(0.0, round($esperado - $montoPrincipal - $montoSecActual, 2));

        $nuevoMonto = $montoPrincipal > 0.01 ? $montoPrincipal : $esperado;
        $nuevoSec   = $montoSecActual;
        if ($montoPrincipal > 0.01 && $faltante > 0.01) {
            $nuevoSec = round($montoSecActual + $faltante, 2);
        }
        $nuevoSecParam = $nuevoSec > 0.01 ? $nuevoSec : null;

        $db->CRUD(
            "UPDATE convenio_cliente_amortizacion
                SET estatus_pago     = 'pagado',
                    fecha_pago_real  = COALESCE(fecha_pago_real, :fecha),
                    monto_pagado     = :monto,
                    monto_secundario = :ms
              WHERE id = :id
                AND estatus_pago != 'cancelado'",
            [
                'fecha' => $fechaCierre,
                'monto' => round($nuevoMonto, 2),
                'ms'    => $nuevoSecParam,
                'id'    => (int)$fila['id'],
            ]
        );

        $fila['estatus_pago']     = 'pagado';
        $fila['fecha_pago_real']  = $fila['fecha_pago_real'] ?: $fechaCierre;
        $fila['monto_pagado']     = round($nuevoMonto, 2);
        $fila['monto_secundario'] = $nuevoSecParam;
    }
    unset($fila);

    $db->CRUD(
        "UPDATE convenio_cliente
            SET estatus = 'completado', fecha_modifica = NOW()
          WHERE id = :id AND estatus != 'cancelado'",
        ['id' => (int)$convenio['id']]
    );
    $convenio['estatus'] = 'completado';

    return true;
}

    // ─────────────────────────────────────────────
// CANCELAR CONVENIO
// ─────────────────────────────────────────────

public static function cancelarConvenio($id_convenio, $usuario, $motivo = null)
{
    try {
        $db = new Database();
        $motivoLimpio = mb_substr(trim(strip_tags((string) $motivo)), 0, 200);

        // 1. Verificar que exista y no este cancelado.
        $convenio = $db->queryOne(
            "SELECT id, id_credito
             FROM convenio_cliente
             WHERE id = :id AND estatus != 'cancelado'
             LIMIT 1",
            ['id' => (int) $id_convenio]
        );

        if (!$convenio) {
            return self::resultado(false, 'El convenio no existe o ya está cancelado.');
        }

        // 2. Semana de referencia para auditoria. No bloquea por estatus de filas.
        $semanaRef = $db->queryOne(
            "SELECT
                COALESCE(
                    MIN(CASE
                        WHEN estatus_pago NOT IN ('pagado', 'cancelado') THEN numero_semana
                        ELSE NULL
                    END),
                    MAX(numero_semana),
                    1
                ) AS numero_semana
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id",
            ['id' => (int) $id_convenio]
        );

        $semanaCancelacion = max(1, (int) ($semanaRef['numero_semana'] ?? 1));
        $fechaCancelacion  = date('Y-m-d');

        // 3. Actualizar convenio_cliente
        $ok = $db->CRUD(
            "UPDATE convenio_cliente SET
                estatus                  = 'cancelado',
                fecha_cancelacion        = :fecha_cancelacion,
                numero_semana_cancelacion = :semana,
                usuario_cancela          = :usuario,
                motivo_cancelamiento     = COALESCE(NULLIF(:motivo_cancelamiento, ''), motivo_cancelamiento),
                usuario_modifica         = :usuario_modifica,
                fecha_modifica           = NOW()
             WHERE id = :id",
            [
                'fecha_cancelacion' => $fechaCancelacion,
                'semana'            => $semanaCancelacion,
                'usuario'           => $usuario,
                'motivo_cancelamiento' => $motivoLimpio,
                'usuario_modifica'  => $usuario,
                'id'                => (int) $id_convenio,
            ]
        );

        if (!$ok) {
            return self::resultado(false, 'No se pudo actualizar el convenio.');
        }

        // 4. Marcar como canceladas las semanas no pagadas.
        $db->CRUD(
            "UPDATE convenio_cliente_amortizacion SET
                estatus_pago = 'cancelado'
             WHERE id_convenio_cliente = :id
               AND estatus_pago NOT IN ('pagado', 'cancelado')",
            [
                'id' => (int) $id_convenio,
            ]
        );

        return self::resultado(true, 'Convenio cancelado correctamente.', [
            'numero_semana_cancelacion' => $semanaCancelacion,
            'fecha_cancelacion'         => $fechaCancelacion,
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al cancelar convenio.', null, $e->getMessage());
    }
}

// ─────────────────────────────────────────────
// SOLICITAR CANCELAMIENTO (registra petición pendiente de autorizar)
// ─────────────────────────────────────────────

public static function solicitarCancelamiento($id_convenio, $usuario, $motivo)
{
    try {
        $db = new Database();

        $convenio = $db->queryOne(
            "SELECT id FROM convenio_cliente
             WHERE id = :id AND estatus = 'activo'
               AND solicitud_cancelamiento_fecha IS NULL
             LIMIT 1",
            ['id' => (int) $id_convenio]
        );

        if (!$convenio) {
            return self::resultado(false, 'El convenio no existe, no está activo, o ya tiene una solicitud pendiente.');
        }

        $ok = $db->CRUD(
            "UPDATE convenio_cliente SET
                motivo_cancelamiento           = :motivo,
                solicitud_cancelamiento_fecha  = NOW(),
                usuario_cancela                = :usuario,
                usuario_modifica               = :usuario,
                fecha_modifica                 = NOW()
             WHERE id = :id",
            [
                'motivo'  => mb_substr(strip_tags($motivo), 0, 200),
                'usuario' => $usuario,
                'id'      => (int) $id_convenio,
            ]
        );

        if (!$ok) {
            return self::resultado(false, 'No se pudo registrar la solicitud.');
        }

        return self::resultado(true, 'Solicitud de cancelamiento enviada. Queda pendiente de autorización.');

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al registrar solicitud.', null, $e->getMessage());
    }
}

// ─────────────────────────────────────────────
// PETICIONES PENDIENTES DE CANCELAMIENTO
// ─────────────────────────────────────────────

public static function getPeticionesCancelamiento(?array $celulas = null)
{
    try {
        $db = new Database();

        $where = "cc.estatus = 'activo' AND cc.solicitud_cancelamiento_fecha IS NOT NULL";
        $params = [];

        if (!empty($celulas)) {
            $placeholders = implode(',', array_map(fn($i) => ":cel$i", array_keys($celulas)));
            $where .= " AND cc.id_celula IN ($placeholders)";
            foreach ($celulas as $i => $v) {
                $params["cel$i"] = $v;
            }
        }

        $rows = $db->queryAll(
            "SELECT cc.id,
                    cc.id AS id_peticion,
                    'cancelamiento' AS tipo_peticion,
                    cc.id_credito,
                    cc.nombre_cliente,
                    cc.motivo_cancelamiento,
                    cc.solicitud_cancelamiento_fecha,
                    cc.usuario_cancela,
                    cc.fecha_acuerdo,
                    cc.total_a_pagar,
                    cc.numero_semanas,
                    cc.id_celula,
                    pc.nombre AS nombre_producto
             FROM convenio_cliente cc
             INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
             WHERE $where
             ORDER BY cc.solicitud_cancelamiento_fecha ASC",
            $params
        );

        return self::resultado(true, 'OK', ['peticiones' => $rows ?: []]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener peticiones.', null, $e->getMessage());
    }
}

// ─────────────────────────────────────────────
// DESCARTAR SOLICITUD DE CANCELAMIENTO
// ─────────────────────────────────────────────

public static function descartarCancelamiento($id_convenio, $usuario)
{
    try {
        $db = new Database();

        $check = $db->queryOne(
            "SELECT id FROM convenio_cliente
             WHERE id = :id AND estatus = 'activo'
               AND solicitud_cancelamiento_fecha IS NOT NULL
             LIMIT 1",
            ['id' => (int) $id_convenio]
        );

        if (!$check) {
            return self::resultado(false, 'El convenio no tiene una solicitud pendiente o no está activo.');
        }

        $ok = $db->CRUD(
            "UPDATE convenio_cliente SET
                motivo_cancelamiento           = NULL,
                solicitud_cancelamiento_fecha  = NULL,
                usuario_cancela                = NULL,
                usuario_modifica               = :usuario,
                fecha_modifica                 = NOW()
             WHERE id = :id",
            [
                'usuario' => $usuario,
                'id'      => (int) $id_convenio,
            ]
        );

        if (!$ok) {
            return self::resultado(false, 'No se pudo descartar la solicitud.');
        }

        return self::resultado(true, 'La solicitud de cancelamiento fue descartada. El convenio permanece activo.');

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al descartar solicitud.', null, $e->getMessage());
    }
}

public static function solicitarReactivacionOferta($id_credito, $id_producto, $usuario, $motivo)
{
    try {
        $db = new Database();
        $idCredito = (int) $id_credito;
        $idProducto = (int) $id_producto;

        $activo = $db->queryOne(
            "SELECT id FROM convenio_cliente
             WHERE id_credito = :id AND estatus = 'activo'
             LIMIT 1",
            ['id' => $idCredito]
        );
        if ($activo) {
            return self::resultado(false, 'Este crÃ©dito ya tiene un convenio activo.');
        }

        $origen = self::_getUltimoConvenioProducto($db, $idCredito, $idProducto);
        if (!$origen) {
            return self::resultado(false, 'No existe historial anterior para reactivar esta oferta.');
        }

        $pendiente = $db->queryOne(
            "SELECT id, estatus
             FROM convenio_reactivacion_peticion
             WHERE id_credito = :id
               AND id_producto_convenio = :prod
               AND estatus IN ('pendiente', 'aprobada')
             ORDER BY fecha_solicitud DESC
             LIMIT 1",
            ['id' => $idCredito, 'prod' => $idProducto]
        );
        if ($pendiente) {
            $msg = $pendiente['estatus'] === 'aprobada'
                ? 'Esta oferta ya fue reactivada y estÃ¡ pendiente de generar el nuevo convenio.'
                : 'Ya existe una solicitud de reactivaciÃ³n pendiente para esta oferta.';
            return self::resultado(false, $msg, ['id_peticion' => (int) $pendiente['id']]);
        }

        $db->CRUD(
            "INSERT INTO convenio_reactivacion_peticion (
                id_credito, id_convenio_origen, id_producto_convenio,
                estatus, motivo_solicitud, usuario_solicita, fecha_solicitud
             ) VALUES (
                :id_credito, :id_origen, :id_producto,
                'pendiente', :motivo, :usuario, NOW()
             )",
            [
                'id_credito' => $idCredito,
                'id_origen' => (int) $origen['id'],
                'id_producto' => $idProducto,
                'motivo' => mb_substr(strip_tags($motivo), 0, 300),
                'usuario' => $usuario,
            ]
        );

        $idPeticion = (int) $db->queryOne("SELECT LAST_INSERT_ID() AS id")['id'];
        return self::resultado(true, 'Solicitud de reactivaciÃ³n enviada. Queda pendiente de autorizaciÃ³n.', [
            'id_peticion' => $idPeticion,
            'id_convenio_origen' => (int) $origen['id'],
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al solicitar reactivaciÃ³n.', null, $e->getMessage());
    }
}

public static function solicitarReactivacionOfertas($id_credito, array $id_productos, $usuario, $motivo)
{
    $ids = array_values(array_unique(array_filter(array_map('intval', $id_productos))));
    if (!$ids) {
        return self::resultado(false, 'Selecciona al menos una oferta para reactivar.');
    }

    $ok = [];
    $errores = [];
    foreach ($ids as $idProducto) {
        $r = self::solicitarReactivacionOferta($id_credito, $idProducto, $usuario, $motivo);
        if ($r['success']) {
            $ok[] = [
                'id_producto_convenio' => $idProducto,
                'id_peticion' => (int) ($r['datos']['id_peticion'] ?? 0),
            ];
        } else {
            $errores[] = [
                'id_producto_convenio' => $idProducto,
                'mensaje' => $r['mensaje'] ?? 'No se pudo solicitar la reactivacion.',
            ];
        }
    }

    if (!$ok) {
        return self::resultado(false, $errores[0]['mensaje'] ?? 'No se pudo solicitar ninguna reactivacion.', [
            'errores' => $errores,
        ]);
    }

    $msg = count($ok) === 1
        ? 'Solicitud de reactivacion enviada. Queda pendiente de autorizacion.'
        : 'Solicitudes de reactivacion enviadas. Quedan pendientes de autorizacion.';

    return self::resultado(true, $msg, [
        'solicitudes' => $ok,
        'errores' => $errores,
    ]);
}

public static function reactivarOfertasCredito($id_credito, $usuario, $motivo = '', $id_producto = 0, array $id_productos = [])
{
    try {
        $db = new Database();
        $idCredito = (int) $id_credito;
        $idProducto = (int) $id_producto;
        $idsProductos = array_values(array_unique(array_filter(array_map('intval', $id_productos))));
        if ($idProducto > 0 && !in_array($idProducto, $idsProductos, true)) {
            $idsProductos[] = $idProducto;
        }

        $activo = $db->queryOne(
            "SELECT id FROM convenio_cliente
             WHERE id_credito = :id AND estatus = 'activo'
             LIMIT 1",
            ['id' => $idCredito]
        );
        if ($activo) {
            return self::resultado(false, 'Este crÃ©dito ya tiene un convenio activo.');
        }

        if ($idsProductos) {
            $origenes = [];
            foreach ($idsProductos as $prodSeleccionado) {
                $origen = self::_getUltimoConvenioProducto($db, $idCredito, $prodSeleccionado);
                if ($origen) {
                    $origenes[] = $origen;
                }
            }
        } else {
            $origenes = self::_getUltimosConveniosPorProducto($db, $idCredito);
        }

        if (!$origenes) {
            return self::resultado(false, 'No hay convenios anteriores para reactivar.');
        }

        $aprobadas = [];
        foreach ($origenes as $origen) {
            $prod = (int) $origen['id_producto_convenio'];
            $existente = $db->queryOne(
                "SELECT id, estatus
                 FROM convenio_reactivacion_peticion
                 WHERE id_credito = :id
                   AND id_producto_convenio = :prod
                   AND estatus IN ('pendiente', 'aprobada')
                 ORDER BY fecha_solicitud DESC
                 LIMIT 1",
                ['id' => $idCredito, 'prod' => $prod]
            );

            if ($existente && $existente['estatus'] === 'aprobada') {
                $aprobadas[] = ['id_peticion' => (int) $existente['id'], 'id_producto_convenio' => $prod];
                continue;
            }

            if ($existente && $existente['estatus'] === 'pendiente') {
                $db->CRUD(
                    "UPDATE convenio_reactivacion_peticion SET
                        estatus = 'aprobada',
                        usuario_resuelve = :usuario,
                        fecha_resolucion = NOW(),
                        comentario_resolucion = :comentario
                     WHERE id = :id",
                    [
                        'usuario' => $usuario,
                        'comentario' => mb_substr($motivo ?: 'Reactivacion directa por permiso maestro', 0, 300),
                        'id' => (int) $existente['id'],
                    ]
                );
                $aprobadas[] = ['id_peticion' => (int) $existente['id'], 'id_producto_convenio' => $prod];
                continue;
            }

            $db->CRUD(
                "INSERT INTO convenio_reactivacion_peticion (
                    id_credito, id_convenio_origen, id_producto_convenio,
                    estatus, motivo_solicitud, usuario_solicita, fecha_solicitud,
                    usuario_resuelve, fecha_resolucion, comentario_resolucion
                 ) VALUES (
                    :id_credito, :id_origen, :id_producto,
                    'aprobada', :motivo, :usuario, NOW(),
                    :usuario, NOW(), :comentario
                 )",
                [
                    'id_credito' => $idCredito,
                    'id_origen' => (int) $origen['id'],
                    'id_producto' => $prod,
                    'motivo' => mb_substr($motivo ?: 'Reactivacion directa por permiso maestro', 0, 300),
                    'usuario' => $usuario,
                    'comentario' => mb_substr($motivo ?: 'Reactivacion directa por permiso maestro', 0, 300),
                ]
            );
            $aprobadas[] = [
                'id_peticion' => (int) $db->queryOne("SELECT LAST_INSERT_ID() AS id")['id'],
                'id_producto_convenio' => $prod,
            ];
        }

        if (!$aprobadas) {
            return self::resultado(false, 'No hay ofertas con historial anterior para reactivar.');
        }

        return self::resultado(true, 'Oferta(s) reactivada(s). Ya puedes generar un nuevo convenio.', [
            'reactivaciones' => $aprobadas,
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al reactivar ofertas.', null, $e->getMessage());
    }
}

public static function getPeticionesReactivacion(?array $celulas = null)
{
    try {
        $db = new Database();
        $where = "crp.estatus = 'pendiente'";
        $params = [];

        if (!empty($celulas)) {
            $placeholders = implode(',', array_map(fn($i) => ":cel$i", array_keys($celulas)));
            $where .= " AND cc.id_celula IN ($placeholders)";
            foreach ($celulas as $i => $v) {
                $params["cel$i"] = $v;
            }
        }

        $rows = $db->queryAll(
            "SELECT crp.id AS id,
                    crp.id AS id_peticion,
                    'reactivacion' AS tipo_peticion,
                    crp.id_credito,
                    cc.nombre_cliente,
                    crp.id_producto_convenio,
                    crp.id_convenio_origen,
                    crp.motivo_solicitud,
                    crp.motivo_solicitud AS motivo_cancelamiento,
                    crp.usuario_solicita,
                    crp.usuario_solicita AS usuario_cancela,
                    crp.fecha_solicitud,
                    crp.fecha_solicitud AS solicitud_cancelamiento_fecha,
                    cc.fecha_acuerdo,
                    cc.total_a_pagar,
                    cc.numero_semanas,
                    cc.id_celula,
                    CONCAT(pc.nombre, ' - Reactivacion') AS nombre_producto
             FROM convenio_reactivacion_peticion crp
             INNER JOIN convenio_cliente cc ON cc.id = crp.id_convenio_origen
             INNER JOIN producto_convenio pc ON pc.id = crp.id_producto_convenio
             WHERE $where
             ORDER BY crp.fecha_solicitud ASC",
            $params
        );

        return self::resultado(true, 'OK', ['peticiones' => $rows ?: []]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener peticiones de reactivaciÃ³n.', null, $e->getMessage());
    }
}

public static function getPeticionesConvenio(?array $celulas = null)
{
    $cancel = self::getPeticionesCancelamiento($celulas);
    if (!$cancel['success']) {
        return $cancel;
    }

    $react = self::getPeticionesReactivacion($celulas);
    if (!$react['success']) {
        return $react;
    }

    $rows = array_merge(
        $cancel['datos']['peticiones'] ?? [],
        $react['datos']['peticiones'] ?? []
    );
    usort($rows, function ($a, $b) {
        $fa = $a['fecha_solicitud'] ?? $a['solicitud_cancelamiento_fecha'] ?? '';
        $fb = $b['fecha_solicitud'] ?? $b['solicitud_cancelamiento_fecha'] ?? '';
        return strcmp((string) $fa, (string) $fb);
    });

    return self::resultado(true, 'OK', ['peticiones' => $rows]);
}

public static function autorizarReactivacionOferta($id_peticion, $usuario, $comentario = '')
{
    try {
        $db = new Database();
        $pet = $db->queryOne(
            "SELECT id FROM convenio_reactivacion_peticion
             WHERE id = :id AND estatus = 'pendiente'
             LIMIT 1",
            ['id' => (int) $id_peticion]
        );
        if (!$pet) {
            return self::resultado(false, 'La peticiÃ³n no existe o ya fue resuelta.');
        }

        $db->CRUD(
            "UPDATE convenio_reactivacion_peticion SET
                estatus = 'aprobada',
                usuario_resuelve = :usuario,
                fecha_resolucion = NOW(),
                comentario_resolucion = :comentario
             WHERE id = :id",
            [
                'usuario' => $usuario,
                'comentario' => mb_substr($comentario ?: 'Reactivacion autorizada', 0, 300),
                'id' => (int) $id_peticion,
            ]
        );

        return self::resultado(true, 'ReactivaciÃ³n autorizada. La oferta ya puede usarse para crear un nuevo convenio.');

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al autorizar reactivaciÃ³n.', null, $e->getMessage());
    }
}

public static function descartarReactivacionOferta($id_peticion, $usuario, $comentario = '')
{
    try {
        $db = new Database();
        $pet = $db->queryOne(
            "SELECT id FROM convenio_reactivacion_peticion
             WHERE id = :id AND estatus = 'pendiente'
             LIMIT 1",
            ['id' => (int) $id_peticion]
        );
        if (!$pet) {
            return self::resultado(false, 'La peticiÃ³n no existe o ya fue resuelta.');
        }

        $estatusDescarte = self::_resolverValorEnum(
            $db,
            'convenio_reactivacion_peticion',
            'estatus',
            ['descartada', 'descartado', 'rechazada', 'rechazado', 'cancelada', 'cancelado']
        );
        if (!$estatusDescarte) {
            $valores = self::_valoresEnumColumna($db, 'convenio_reactivacion_peticion', 'estatus');
            return self::resultado(
                false,
                'No hay un estatus de descarte configurado para reactivaciones.',
                ['estatus_disponibles' => $valores]
            );
        }

        $db->CRUD(
            "UPDATE convenio_reactivacion_peticion SET
                estatus = :estatus,
                usuario_resuelve = :usuario,
                fecha_resolucion = NOW(),
                comentario_resolucion = :comentario
             WHERE id = :id",
            [
                'estatus' => $estatusDescarte,
                'usuario' => $usuario,
                'comentario' => mb_substr($comentario ?: 'Reactivacion descartada', 0, 300),
                'id' => (int) $id_peticion,
            ]
        );

        return self::resultado(true, 'La solicitud de reactivaciÃ³n fue descartada.');

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al descartar reactivaciÃ³n.', null, $e->getMessage());
    }
}

    /**
     * Detecta incumplimiento: devuelve true si existió convenio y el último pago
     * vencido tiene más de 30 días sin pagar.
     */
    public static function tieneIncumplimiento($id_credito)
    {
        try {
            $db = new Database(); // __SPARTA_SECRET_REDACTED__: tablas de convenios

            $row = $db->queryOne(
                "SELECT cca.fecha_pago
                 FROM convenio_cliente_amortizacion cca
                 INNER JOIN convenio_cliente cc ON cc.id = cca.id_convenio_cliente
                 WHERE cc.id_credito = :id
                   AND cca.estatus_pago = 'vencido'
                 ORDER BY cca.fecha_pago DESC
                 LIMIT 1",
                ['id' => (int) $id_credito]
            );

            if (!$row) return false;
            $diasVencido = (int) ((time() - strtotime($row['fecha_pago'])) / 86400);
            return $diasVencido > 30;
        } catch (\Exception $e) {
            return false;
        }
    }


    // ─────────────────────────────────────────────
// HISTORIAL DE CONVENIOS
// ─────────────────────────────────────────────

/**
 * Devuelve todos los convenios de un crédito ordenados por fecha descendente.
 */
public static function getHistorialConvenios($id_credito)
{
    try {
        $db = new Database();
        $rows = $db->queryAll(
    "SELECT
        cc.id,
        cc.id_credito,
        cc.id_producto_convenio,        -- ← agregar esto
        pc.nombre           AS nombre_producto,
        cc.total_a_pagar,
        cc.numero_semanas,
        cc.estatus,
        cc.fecha_acuerdo,
        cc.fecha_cancelacion,
        cc.numero_semana_cancelacion,
        cc.usuario_alta,
        cc.usuario_cancela,
        cc.pdf_adjunto,
        CASE WHEN crp.id IS NULL THEN 0 ELSE 1 END AS es_reactivado,
        crp.id_convenio_origen,
        crp.id AS id_peticion_reactivacion,
        cc.usuario_cancela = 'sistema_auto' AS cancelado_por_incumplimiento  -- ← agregar esto
     FROM convenio_cliente cc
     INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
     LEFT JOIN convenio_reactivacion_peticion crp
            ON crp.id_convenio_nuevo = cc.id
           AND crp.estatus = 'ejecutada'
     WHERE cc.id_credito = :id
     ORDER BY cc.fecha_alta DESC",
    ['id' => (int) $id_credito]
    );

        return self::resultado(true, 'Historial obtenido.', $rows ?: []);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener historial.', null, $e->getMessage());
    }
}

// ════════════════════════════════════════════════
// REGISTRAR PAGO DE SEMANA (cruza con S2Movil)
// ════════════════════════════════════════════════

/**
 * Marca una semana del convenio como pagada.
 * Consulta S2Movil para obtener fechaValor y montoPago reales.
 *
 * @param int $id_convenio
 * @param int $numero_semana   — semana del convenio (1-N)
 * @param int $id_credito      — para consultar S2Movil
 */
public static function registrarPago($id_convenio, $numero_semana, $id_credito)
{
    try {
        $db = new Database();

        // 1. Verificar que la fila existe y está pendiente o vencida
        $fila = $db->queryOne(
            "SELECT id, estatus_pago, fecha_pago
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id AND numero_semana = :num
             LIMIT 1",
            ['id' => $id_convenio, 'num' => $numero_semana]
        );

        if (!$fila) {
            return self::resultado(false, 'Semana no encontrada en el convenio.');
        }
        if ($fila['estatus_pago'] === 'pagado') {
            return self::resultado(false, 'Esta semana ya está registrada como pagada.');
        }
        if ($fila['estatus_pago'] === 'cancelado') {
            return self::resultado(false, 'No se puede pagar una semana cancelada.');
        }

        // 2. Buscar en S2Movil un pago con fechaValor en el rango de esta semana
        $url     = ENDPOINT;
        $payload = json_encode([
            'idCredito'  => (int) $id_credito,
            'fechaCorte' => date('Y-m-d'),
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . (defined('TOKEN') ? TOKEN : (getenv('S2_ESTADO_CUENTA_TOKEN') ?: '')),
            ],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data       = json_decode($response, true);
        $datosPagos = $data['estadoCuenta']['datosPagos'] ?? [];

        // Rango de la semana
        $fechaInicioSemana = new \DateTime($fila['fecha_pago']);
        $fechaFinSemana    = (clone $fechaInicioSemana)->modify('+6 days');

        $fechaPagoReal = date('Y-m-d');
        $montoPagado   = null;

        foreach ($datosPagos as $pago) {
            if (empty($pago['fechaValor'])) continue;
            $fechaValor = new \DateTime($pago['fechaValor']);
            if ($fechaValor >= $fechaInicioSemana && $fechaValor <= $fechaFinSemana) {
                $fechaPagoReal = $pago['fechaValor'];
                $montoPagado   = $pago['montoPago'] ?? null;
                break;
            }
        }

        // FIX #2: bloquear si S2 no confirmó ningún pago en el rango de la semana
        if ($montoPagado === null) {
            return self::resultado(false,
                'No se encontró pago en S2Movil para la semana ' . $numero_semana .
                '. Verifique que el cliente haya realizado el pago antes de registrarlo.'
            );
        }

        // 3. Actualizar la fila
        $db->CRUD(
            "UPDATE convenio_cliente_amortizacion
             SET estatus_pago    = 'pagado',
                 fecha_pago_real = :fecha,
                 monto_pagado    = :monto
             WHERE id_convenio_cliente = :id AND numero_semana = :num",
            [
                'fecha' => $fechaPagoReal,
                'monto' => $montoPagado,
                'id'    => $id_convenio,
                'num'   => $numero_semana,
            ]
        );

        // 4. Verificar si todas las semanas están pagadas
        $conteo = $db->queryOne(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN estatus_pago = 'pagado' THEN 1 ELSE 0 END) as pagadas
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id",
            ['id' => $id_convenio]
        );

        if ($conteo && $conteo['total'] > 0 && $conteo['total'] == $conteo['pagadas']) {
            // FIX #3b: igual que la auto-conciliación, no completar si alguna semana
            // quedó sin monto_pagado (consistencia ante datos pre-existentes corruptos)
            $sinMonto = $db->queryOne(
                "SELECT COUNT(*) AS cnt
                 FROM convenio_cliente_amortizacion
                 WHERE id_convenio_cliente = :id
                   AND estatus_pago = 'pagado'
                   AND monto_pagado IS NULL",
                ['id' => $id_convenio]
            );
            if (!$sinMonto || (int)$sinMonto['cnt'] === 0) {
                $db->CRUD(
                    "UPDATE convenio_cliente
                     SET estatus = 'completado', fecha_modifica = NOW()
                     WHERE id = :id",
                    ['id' => $id_convenio]
                );
            }
        }

        return self::resultado(true, 'Pago registrado correctamente.', [
            'fecha_pago_real' => $fechaPagoReal,
            'monto_pagado'    => $montoPagado,
            'numero_semana'   => $numero_semana,
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al registrar pago.', null, $e->getMessage());
    }
}

// ════════════════════════════════════════════════
// ESTADO DE CUENTA EXTERNO
// ════════════════════════════════════════════════


public static function getEstadoCuenta($id_credito)
{
    try {
        $url        = ENDPOINT;
        $fechaCorte = date('Y-m-d');

        $payload = json_encode([
            'idCredito'  => (int) $id_credito,
            'fechaCorte' => $fechaCorte,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . (defined('TOKEN') ? TOKEN : (getenv('S2_ESTADO_CUENTA_TOKEN') ?: '')),
            ],
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return self::resultado(false, 'Error de conexión con servicio externo.', null, $curlError);
        }

        if ($httpCode !== 200) {
            return self::resultado(false, 'Servicio externo respondió con error ' . $httpCode);
        }

        $data = json_decode($response, true);

        if (!$data || !isset($data['estadoCuenta']['datosPagos'])) {
            return self::resultado(false, 'Respuesta inesperada del servicio externo.');
        }

        $datosPagos     = $data['estadoCuenta']['datosPagos'];
        $pagosIndexados = [];

        foreach ($datosPagos as $pago) {
            $cuotas  = $pago['numeroCuotaSemanal'];
            $numeros = is_string($cuotas)
                ? array_map('trim', explode(',', $cuotas))
                : [(string) $cuotas];

            foreach ($numeros as $num) {
                $num = (int) $num;
                if ($num > 0) {
                    $pagosIndexados[$num] = [
                        'fechaValor'    => $pago['fechaValor']    ?? null,
                        'fechaDeposito' => $pago['fechaDeposito'] ?? null,
                        'monto'         => $pago['montoPago']     ?? null,
                    ];
                }
            }
        }

        return self::resultado(true, 'Estado de cuenta obtenido.', [
            'pagos_indexados' => $pagosIndexados,
            'total_pagadas'   => count($pagosIndexados),
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al consultar estado de cuenta.', null, $e->getMessage());
    }
}

// ────────────────────────────────────────────────
// HELPER: buscar número de cuota original por fecha
// ────────────────────────────────────────────────

/**
 * Dado el id_credito y la fecha_pago preferencial del convenio,
 * busca en datosCargos de S2Movil cuál cuota original
 * tiene fechaVencimiento dentro del rango [fecha_pago, fecha_pago + 7 días].
 */
private static function _buscarCuotaOriginal($id_credito, $fecha_pago_convenio)
{
    try {
        $url     = ENDPOINT;
        $payload = json_encode([
            'idCredito'  => (int) $id_credito,
            'fechaCorte' => date('Y-m-d'),
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . (defined('TOKEN') ? TOKEN : (getenv('S2_ESTADO_CUENTA_TOKEN') ?: '')),
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!$data || !isset($data['estadoCuenta']['datosCargos'])) return null;

        // Intento 1: buscar cargo cuya fechaVencimiento cae en el rango [fecha_pago, +7 días]
        $cargos    = $data['estadoCuenta']['datosCargos'];
        $fechaConv = new \DateTime($fecha_pago_convenio);
        $fechaFin  = (clone $fechaConv)->modify('+7 days');

        foreach ($cargos as $cargo) {
            $fechaVenc = new \DateTime($cargo['fechaVencimiento']);
            if ($fechaVenc >= $fechaConv && $fechaVenc <= $fechaFin) {
                return $cargo['idCargo'];
            }
        }

        // Intento 2: crédito ya venció — devolver el idCargo del último cargo
        // para que registrarPago busque en pagosIndexados el último pago real
        $ultimoCargo = end($cargos);
        return $ultimoCargo ? $ultimoCargo['idCargo'] : null;

    } catch (\Exception $e) {
        return null;
    }
}


// ────────────────────────────────────────────────
// HELPER: completar convenio si todas las semanas pagadas
// ────────────────────────────────────────────────

private static function _verificarConvenioCompleto($id_convenio)
{
    try {
        $db  = new Database();
        $res = $db->queryOne(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN estatus_pago = 'pagado' THEN 1 ELSE 0 END) as pagadas
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id",
            ['id' => (int) $id_convenio]
        );

        if ($res && $res['total'] > 0 && $res['total'] == $res['pagadas']) {
            $db->CRUD(
                "UPDATE convenio_cliente
                    SET estatus = 'completado', fecha_modifica = NOW()
                  WHERE id = :id AND estatus = 'activo'",
                ['id' => (int) $id_convenio]
            );
        }
    } catch (\Exception $e) {
        // No fatal — log silently
    }
}


public static function getProductosConvenio()
{
    try {
        $db = new Database();
        $productos = $db->queryAll(
            "SELECT pc.id, pc.nombre, pcd.id AS id_detalle,
                    pcd.porcentaje_descuento, pcd.porcentaje_variable,
                    pcd.periodo_inicio, pcd.periodo_fin
             FROM producto_convenio pc
             INNER JOIN producto_convenio_detalle pcd ON pcd.id_producto_convenio = pc.id
             WHERE pc.activo = 1
             ORDER BY pc.nombre, pcd.porcentaje_descuento",
            []
        );

        if (!$productos) {
            return self::resultado(true, 'Sin productos.', []);
        }

        $agrupados = [];
        foreach ($productos as $row) {
            $idP = $row['id'];
            if (!isset($agrupados[$idP])) {
                $agrupados[$idP] = [
                    'id'       => $idP,
                    'nombre'   => $row['nombre'],
                    'detalles' => [],
                ];
            }
            $agrupados[$idP]['detalles'][] = [
                'id'                   => $row['id_detalle'],
                'porcentaje_descuento' => $row['porcentaje_descuento'],
                'porcentaje_variable'  => (int) $row['porcentaje_variable'],
                'periodo_inicio'       => $row['periodo_inicio'],
                'periodo_fin'          => $row['periodo_fin'],
            ];
        }

        return self::resultado(true, 'Productos obtenidos.', array_values($agrupados));

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener productos.', null, $e->getMessage());
    }
}

// ════════════════════════════════════════════════
// ELIMINAR CONVENIOS DE UN CRÉDITO (acción admin express)
// Borra todos los registros de convenio_cliente (y su amortización)
// para el id_credito dado, dejando al sistema libre para generar uno nuevo.
// ════════════════════════════════════════════════

public static function eliminarConveniosCredito(int $id_credito): array
{
    if ($id_credito <= 0) {
        return self::resultado(false, 'ID de crédito inválido.');
    }
    try {
        $db = new Database();

        // Obtener todos los IDs de convenio del crédito
        $convenios = $db->queryAll(
            "SELECT id FROM convenio_cliente WHERE id_credito = :id",
            ['id' => $id_credito]
        );

        if (empty($convenios)) {
            return self::resultado(false, 'No se encontró ningún convenio para el crédito ' . $id_credito . '.');
        }

        $ids = array_map('intval', array_column($convenios, 'id'));
        $idsStr = implode(',', $ids);

        // 1. Eliminar amortizaciones
        $db->CRUD("DELETE FROM convenio_cliente_amortizacion WHERE id_convenio_cliente IN ($idsStr)");

        // 2. Eliminar convenios
        $db->CRUD("DELETE FROM convenio_cliente WHERE id IN ($idsStr)");

        $total = count($ids);
        return self::resultado(true, "Se eliminaron $total convenio(s) y su amortización para el crédito $id_credito.");

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al eliminar los convenios.', null, $e->getMessage());
    }
}

// ════════════════════════════════════════════════
    // VALIDAR CRÉDITO EN DESPACHO
    // Consulta __SPARTA_SECRET_REDACTED__.asigna_creditos_despacho.
    //
    // Retorna success=true solo si:
    //   - El crédito existe en la tabla (estuvo en mora 8+)
    //   - Su estatus actual es 1 (activo en despacho)
    //
    // Retorna success=false con mensaje descriptivo si:
    //   - El crédito no existe en la tabla (nunca estuvo en despacho)
    //   - El crédito tiene estatus=0 (ya se regularizó / está current)
    // ════════════════════════════════════════════════

    public static function validarCreditoEnDespacho($id_credito)
    {
        try {
            $db = new Database(); // __SPARTA_SECRET_REDACTED__

            $row = $db->queryOne(
                "SELECT id, estatus
                 FROM asigna_creditos_despacho
                 WHERE id_credito = :id
                 ORDER BY fecha_alta DESC
                 LIMIT 1",
                ['id' => (int) $id_credito]
            );

            // El crédito nunca estuvo asignado a un despacho
            if (!$row) {
                return self::resultado(
                    false,
                    'Este crédito no está registrado en despacho. No es posible registrar un convenio existente.'
                );
            }

            // El crédito ya se regularizó (estatus = 0 → current)
            if ((int) $row['estatus'] === 0) {
                return self::resultado(
                    false,
                    'Este crédito ya está al corriente (estatus current). No se puede registrar un convenio existente para un crédito regularizado.'
                );
            }

            // estatus = 1 → activo en despacho, mora 8+, elegible
            return self::resultado(true, 'Crédito elegible para registrar convenio existente.');

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al validar el crédito en despacho.', null, $e->getMessage());
        }
    }


// ════════════════════════════════════════════════
// MIGRAR CONVENIO EXISTENTE (registrado por despacho)
// ════════════════════════════════════════════════

public static function migrarConvenio($datos)
{
    try {
        $db = new Database();

        // 1. Verificar que no haya convenio activo
        $activo = $db->queryOne(
            "SELECT id FROM convenio_cliente
             WHERE id_credito = :id AND estatus = 'activo' LIMIT 1",
            ['id' => (int) $datos['id_credito']]
        );

        if ($activo) {
            return self::resultado(false, 'Este crédito ya tiene un convenio activo.');
        }

        $reactivacion = self::_resolverReactivacionAprobada($db, $datos);

        // 2. Calcular montos y semanas
        $fechaInicio  = $datos['fecha_inicio'];
        $adeudoBase   = (float) $datos['adeudo_base'];
        $pctDescuento = (float) $datos['porcentaje_descuento'];
        $pagoSemanal  = (float) $datos['pago_semanal'];
        $idProducto   = (int)   $datos['id_producto_convenio'];
        $idDetalle    = (int)   $datos['id_producto_convenio_detalle'];
        $pdfAdjunto   = isset($datos['pdf_adjunto']) ? $datos['pdf_adjunto'] : null;

        $montoAdicional = (float) ($datos['monto_adicional'] ?? 0);

        // Usar el total enviado directamente por el frontend para evitar errores de redondeo
        // al reconvertir desde el porcentaje (que ya estaba redondeado a 2 decimales).
        // El campo 'total_final_con_adicional' es la fuente de verdad.
        if (!empty($datos['total_final_con_adicional']) && (float) $datos['total_final_con_adicional'] > 0) {
            $totalAPagar    = round((float) $datos['total_final_con_adicional'], 2);
            $descuentoMonto = round($adeudoBase - ($totalAPagar - $montoAdicional), 2);
            // Recalcular pct para que coincida con el total real guardado
            $pctDescuento   = $adeudoBase > 0 ? round(($descuentoMonto / $adeudoBase) * 100, 4) : $pctDescuento;
        } else {
            $descuentoMonto = round($adeudoBase * ($pctDescuento / 100), 2);
            $totalAPagar    = round($adeudoBase - $descuentoMonto + $montoAdicional, 2);
        }

        $semanasEnteras = (int) floor($totalAPagar / $pagoSemanal);
        $residuo        = round($totalAPagar - ($semanasEnteras * $pagoSemanal), 2);
        // Solo genera semana extra si el residuo es >= $1.00 (evita cuota fantasma por centavos)
        $semanas        = $residuo >= 1.00 ? $semanasEnteras + 1 : $semanasEnteras;
        $fechaUltimoPago = date('Y-m-d', strtotime($fechaInicio . ' +' . (($semanas - 1) * 7) . ' days'));

        // 3. Insertar convenio (ahora con campo pdf_adjunto)
        $baseCalculo = isset($datos['base_calculo']) && in_array($datos['base_calculo'], ['saldo_total_capital', 'interes', 'adeudo_total'])
            ? $datos['base_calculo']
            : null;

        $ok = $db->CRUD(
            "INSERT INTO convenio_cliente (
                id_credito, id_producto_convenio, id_producto_convenio_detalle,
                nombre_cliente, bucket_morosidad_real, dias_mora, avance_pago_plazo,
                adeudo_total_original, porcentaje_descuento, descuento_monto,
                 total_a_pagar, monto_adicional, pago_inicial_monto, numero_semanas, pago_semanal,
                 fecha_acuerdo, fecha_primer_pago, fecha_ultimo_pago, estatus,
                usuario_alta, pdf_adjunto, base_calculo, id_celula
            ) VALUES (
                :id_credito, :id_producto, :id_detalle,
                :nombre_cliente, :bucket, :dias_mora, :avance_pago,
                :adeudo_original, :pct_descuento, :descuento_monto,
                 :total_pagar, :monto_adicional, NULL, :num_semanas, :pago_semanal,
                 :fecha_acuerdo, :fecha_primer_pago, :fecha_ultimo_pago, 'activo',
                :usuario, :pdf_adjunto, :base_calculo, :id_celula
            )",
            [
                'id_credito'       => (int) $datos['id_credito'],
                'id_producto'      => $idProducto,
                'id_detalle'       => $idDetalle,
                'nombre_cliente'   => $datos['nombre_cliente'],
                'bucket'           => $datos['bucket_morosidad_real'] ?? '',
                'dias_mora'        => (int) ($datos['dias_mora'] ?? 0),
                'avance_pago'      => $datos['avance_pago_plazo'] ?? '',
                'adeudo_original'  => $adeudoBase,
                'pct_descuento'    => $pctDescuento,
                'descuento_monto'  => $descuentoMonto,
                'total_pagar'      => $totalAPagar,
                'monto_adicional'  => $montoAdicional,
                'num_semanas'      => $semanas,
                'pago_semanal'     => $pagoSemanal,
                'fecha_acuerdo'    => $fechaInicio,
                'fecha_primer_pago'=> $fechaInicio,
                'fecha_ultimo_pago'=> $fechaUltimoPago,
                'usuario'          => $datos['usuario_alta'],
                'pdf_adjunto'      => $pdfAdjunto,
                'base_calculo'     => $baseCalculo,
                'id_celula'        => isset($datos['id_celula']) ? $datos['id_celula'] : null,
            ]
        );

        if (!$ok) {
            return self::resultado(false, 'No se pudo guardar el convenio.');
        }

        $idConvenio  = (int) $db->queryOne("SELECT LAST_INSERT_ID() AS id")['id'];
        self::_marcarConvenioReactivado($db, $idConvenio, $reactivacion, $datos);
        $saldoActual = $totalAPagar;

        // 4. Generar amortización
        $hoy = new \DateTime();

        for ($s = 1; $s <= $semanas; $s++) {
            $fechaPago   = date('Y-m-d', strtotime($fechaInicio . ' +' . (($s - 1) * 7) . ' days'));
            $capital     = ($s < $semanas) ? $pagoSemanal : $saldoActual;
            $saldoActual = round($saldoActual - $capital, 2);
            if ($saldoActual < 0) $saldoActual = 0;

            // Semanas cuya fecha ya pasó nacen como 'vencido' — son históricas
            // Semanas futuras nacen como 'pendiente' — son las que el sistema vigilará
            // NOTA: comparamos solo fecha (sin hora) para evitar que el mismo día de registro
            //       quede marcado como vencido por la hora actual.
            $fechaPagoObj  = new \DateTime($fechaPago);
            $fechaPagoObj->setTime(0, 0, 0);
            $hoyMediodia   = (clone $hoy)->setTime(0, 0, 0);
            $estatuInicial = $fechaPagoObj < $hoyMediodia ? 'vencido' : 'pendiente';

            $db->CRUD(
                "INSERT INTO convenio_cliente_amortizacion
                     (id_convenio_cliente, numero_semana, fecha_pago, pago_semanal, capital, saldo_restante, estatus_pago)
                 VALUES (:id, :num, :fecha, :pago, :capital, :saldo, :estatus)",
                [
                    'id'      => $idConvenio,
                    'num'     => $s,
                    'fecha'   => $fechaPago,
                    'pago'    => $pagoSemanal,
                    'capital' => $capital,
                    'saldo'   => $saldoActual,
                    'estatus' => $estatuInicial,
                ]
            );
        }

        // 5. Cruzar con S2Movil y marcar semanas ya pagadas
        $semanasMarcadas = self::_marcarSemanasDesdeS2Movil(
            $idConvenio,
            (int) $datos['id_credito'],
            $fechaInicio,
            $semanas,
            $db
        );

        return self::resultado(true, 'Convenio migrado correctamente.', [
            'id_convenio'     => $idConvenio,
            'semanas_total'   => $semanas,
            'semanas_pagadas' => $semanasMarcadas,
            'total_a_pagar'   => $totalAPagar,
            'descuento_monto' => $descuentoMonto,
            'pdf_adjunto'     => $pdfAdjunto,
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al migrar convenio.', null, $e->getMessage());
    }
}

// ════════════════════════════════════════════════
// HELPER: marcar semanas pagadas cruzando S2Movil
// ════════════════════════════════════════════════

private static function _marcarSemanasDesdeS2Movil($idConvenio, $idCredito, $fechaInicio, $semanas, $db)
{
    try {
        $url     = ENDPOINT;
        $payload = json_encode([
            'idCredito'  => $idCredito,
            'fechaCorte' => date('Y-m-d'),
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . (defined('TOKEN') ? TOKEN : (getenv('S2_ESTADO_CUENTA_TOKEN') ?: '')),
            ],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data       = json_decode($response, true);
        $datosPagos = $data['estadoCuenta']['datosPagos'] ?? [];

        if (empty($datosPagos)) return 0;

        // ── Paso 1: Sumar todos los pagos reales de S2 que caen
        //           ANTES o EN la fecha de inicio del convenio
        //           (pagos históricos que el cliente ya hizo)
        // ──────────────────────────────────────────────────────
        $fechaInicioObj  = new \DateTime($fechaInicio);
        $montoDisponible = 0.0;
        $fechaPrimerPago = null;

        foreach ($datosPagos as $pago) {
            if (empty($pago['fechaValor'])) continue;
            $fechaValor = new \DateTime($pago['fechaValor']);

            // Solo pagos hasta 13 días después del inicio del convenio
            $limiteVentana = (clone $fechaInicioObj)->modify('+13 days');
            if ($fechaValor > $limiteVentana) continue;

            // Solo pagos que caen cerca de la fecha de inicio
            $inicioVentana = (clone $fechaInicioObj)->modify('-3 days');
            if ($fechaValor < $inicioVentana) continue;

            $montoDisponible += (float) ($pago['montoPago'] ?? 0);

            if ($fechaPrimerPago === null) {
                $fechaPrimerPago = $pago['fechaValor'];
            }
        }

        if ($montoDisponible <= 0) return 0;

        // ── Paso 2: Aplicar en cascada semana por semana ──────
        $marcadas        = 0;
        $sobrante        = $montoDisponible;
        $fechaAplicacion = $fechaPrimerPago ?? $fechaInicio;

        for ($s = 1; $s <= $semanas; $s++) {
            if ($sobrante <= 0) break;

            // Traer la fila de esta semana
            $fila = $db->queryOne(
                "SELECT id, pago_semanal, estatus_pago
                 FROM convenio_cliente_amortizacion
                 WHERE id_convenio_cliente = :id AND numero_semana = :num",
                ['id' => $idConvenio, 'num' => $s]
            );

            if (!$fila) continue;

            // Ya pagada — saltamos pero no consumimos sobrante
            if ($fila['estatus_pago'] === 'pagado') {
                $marcadas++;
                continue;
            }

            // Solo procesamos pendiente, vencido y pendiente_conciliar
            if (!in_array($fila['estatus_pago'], ['pendiente', 'vencido', 'parcial', 'pendiente_conciliar'])) {
                continue;
            }

            // Threshold real: capital cuando sea significativamente menor que pago_semanal
            // (última semana con saldo residual). Guarda: capital 0/null → usar pago_semanal.
            $psFila2  = (float)$fila['pago_semanal'];
            $capFila2 = (float)($fila['capital'] ?? 0);
            $pagoSemanal = ($capFila2 > 0.50 && $capFila2 < $psFila2 - 1.00) ? $capFila2 : $psFila2;

            if ($sobrante >= $pagoSemanal) {
                // ── Pago completo ──────────────────────────────
                $db->CRUD(
                    "UPDATE convenio_cliente_amortizacion SET
                        estatus_pago    = 'pagado',
                        fecha_pago_real = :fecha,
                        monto_pagado    = :monto
                     WHERE id_convenio_cliente = :id AND numero_semana = :num",
                    [
                        'fecha' => $fechaAplicacion,
                        'monto' => round($pagoSemanal, 2),
                        'id'    => $idConvenio,
                        'num'   => $s,
                    ]
                );
                $sobrante = round($sobrante - $pagoSemanal, 2);
                $marcadas++;

            } else {
                // ── Pago parcial — sobrante no alcanza para cubrir la semana ──
                $db->CRUD(
                    "UPDATE convenio_cliente_amortizacion SET
                        estatus_pago    = 'parcial',
                        fecha_pago_real = :fecha,
                        monto_pagado    = :monto
                     WHERE id_convenio_cliente = :id AND numero_semana = :num",
                    [
                        'fecha' => $fechaAplicacion,
                        'monto' => round($sobrante, 2),
                        'id'    => $idConvenio,
                        'num'   => $s,
                    ]
                );
                $sobrante = 0;
                // parcial no cuenta como marcada completa
            }
        }

        // ── Paso 3: Verificar si todas quedaron pagadas → completar convenio ──
        if ($marcadas === $semanas) {
            $db->CRUD(
                "UPDATE convenio_cliente
                 SET estatus = 'completado', fecha_modifica = NOW()
                 WHERE id = :id",
                ['id' => $idConvenio]
            );
        }

        return $marcadas;

    } catch (\Exception $e) {
        return 0;
    }
}


public static function getAmortizacionConvenio($id_convenio)
{
    try {
        $db   = new Database();
        $rows = $db->queryAll(
            "SELECT numero_semana, fecha_pago, pago_semanal, capital,
                    saldo_restante, estatus_pago, fecha_pago_real, monto_pagado
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id
             ORDER BY numero_semana",
            ['id' => $id_convenio]
        );
        return self::resultado(true, 'Amortización obtenida.', $rows ?: []);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener amortización.', null, $e->getMessage());
    }
}

// ════════════════════════════════════════════════
// CONCILIACIÓN — obtener datos de S2 para una semana
// ════════════════════════════════════════════════

public static function getConciliacionSemana($id_convenio, $numero_semana, $id_credito, $semanas_grupo = null)
{
    try {
        $db = new Database();

        // Parsear semanas del grupo — si no viene, solo la semana actual
        $grupoArr = $semanas_grupo
            ? array_map('intval', array_map('trim', explode(',', $semanas_grupo)))
            : [$numero_semana];

        // Asegurar que la semana principal esté incluida
        if (!in_array((int)$numero_semana, $grupoArr)) {
            array_unshift($grupoArr, (int)$numero_semana);
        }

        // Obtener datos de TODAS las semanas del grupo — named params
        $params       = ['id_convenio' => (int) $id_convenio];
        $placeholders = [];
        foreach ($grupoArr as $idx => $numSem) {
            $key                = 'sem_' . $idx;
            $placeholders[]     = ':' . $key;
            $params[$key]       = $numSem;
        }

        $filasGrupo = $db->queryAll(
            "SELECT numero_semana, fecha_pago, fecha_pago_real, pago_semanal,
                    monto_pagado, estatus_pago
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id_convenio
               AND numero_semana IN (" . implode(',', $placeholders) . ")
             ORDER BY numero_semana",
            $params
        );

        if (!$filasGrupo) {
            return self::resultado(false, 'Semanas no encontradas.');
        }

        // Fila principal (primera semana del grupo)
        $filaPrincipal = $filasGrupo[0];

        // Verificar si ya existe conciliación para la semana principal
        $conciliacion = $db->queryOne(
            "SELECT * FROM convenio_conciliacion
             WHERE id_convenio_cliente = :id AND numero_semana = :num",
            ['id' => $id_convenio, 'num' => $numero_semana]
        );

        // ── Traer pagos S2Movil ───────────────────────────────
        $url     = ENDPOINT;
        $payload = json_encode([
            'idCredito'  => (int) $id_credito,
            'fechaCorte' => date('Y-m-d'),
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Token: ' . (defined('TOKEN') ? TOKEN : (getenv('S2_ESTADO_CUENTA_TOKEN') ?: '')),
            ],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data       = json_decode($response, true);
        $datosPagos = $data['estadoCuenta']['datosPagos'] ?? [];

        // Buscar pagos S2 en rango de la semana principal
        // Usamos fecha_pago_real si existe, sino fecha_pago preferencial
        $fechaBase   = $filaPrincipal['fecha_pago_real'] ?: $filaPrincipal['fecha_pago'];
        $fechaInicio = new \DateTime($fechaBase);
        $fechaFin    = (clone $fechaInicio)->modify('+6 days');
        $inicioVentana = (clone $fechaInicio)->modify('-3 days');

        $pagosEncontrados = [];
        $idPagosVistos    = [];

        foreach ($datosPagos as $pago) {
            if (empty($pago['fechaValor'])) continue;
            $fechaValor = new \DateTime($pago['fechaValor']);
            if ($fechaValor < $inicioVentana || $fechaValor > $fechaFin) continue;

            $idPago = $pago['idPago'] ?? null;
            if (in_array($idPago, $idPagosVistos)) continue;
            $idPagosVistos[] = $idPago;

            $montoPago = (float) ($pago['montoPago'] ?? 0);
            $capital   = (float) ($pago['capital']   ?? 0);
            $sobrante  = round($montoPago - $capital, 2);

            $pagosEncontrados[] = [
                'idPago'     => $idPago,
                'fechaValor' => $pago['fechaValor'] ?? null,
                'montoPago'  => $montoPago,
                'capital'    => $capital,
                'interes'    => (float) ($pago['interes'] ?? 0),
                'aplicado'   => $capital,
                'sobrante'   => $sobrante > 0 ? $sobrante : 0,
                'cuotas'     => $pago['numeroCuotaSemanal'] ?? null,
            ];
        }

        // ── Construir resumen de aplicación al convenio ───────
        // Cada semana del grupo con lo que se le aplicó en cascada
        $resumenConvenio = array_map(function($fila) {
            return [
                'numero_semana' => (int) $fila['numero_semana'],
                'fecha_pago'    => $fila['fecha_pago'],
                'pago_semanal'  => (float) $fila['pago_semanal'],
                'monto_pagado'  => (float) ($fila['monto_pagado'] ?? 0),
                'estatus_pago'  => $fila['estatus_pago'],
                'faltante'      => round(
                    (float)$fila['pago_semanal'] - (float)($fila['monto_pagado'] ?? 0),
                    2
                ),
            ];
        }, $filasGrupo);

        // Total pagado al convenio en este grupo
        $totalAplicadoConvenio = array_sum(array_column($resumenConvenio, 'monto_pagado'));

        return self::resultado(true, 'Datos de conciliación obtenidos.', [
            'semana'                 => $numero_semana,
            'semanas_grupo'          => $grupoArr,
            'fecha_pago'             => $filaPrincipal['fecha_pago'],
            'pago_semanal'           => $filaPrincipal['pago_semanal'],
            'pagos_s2'               => $pagosEncontrados,
            'resumen_convenio'       => $resumenConvenio,
            'total_aplicado_convenio'=> $totalAplicadoConvenio,
            'conciliacion'           => $conciliacion ?: null,
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al obtener conciliación.', null, $e->getMessage());
    }
}

// ════════════════════════════════════════════════
// CONCILIACIÓN — guardar conciliación
// ════════════════════════════════════════════════

public static function guardarConciliacion($datos, $archivo = null)
{
    try {
        $db = new Database();

        // Verificar si ya existe
        $existe = $db->queryOne(
            "SELECT id FROM convenio_conciliacion
             WHERE id_convenio_cliente = :id AND numero_semana = :num",
            ['id' => $datos['id_convenio'], 'num' => $datos['numero_semana']]
        );

        $archivoPath = null;
        if ($archivo && !empty($archivo['tmp_name'])) {
            $directorio = sparta_uploads_join('conciliaciones');
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }
            $extension   = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreArch  = 'conciliacion_' . $datos['id_convenio'] . '_sem' . $datos['numero_semana'] . '_' . date('Ymd_His') . '.' . $extension;
            $rutaCompleta = sparta_uploads_join('conciliaciones', $nombreArch);
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                $archivoPath = '/uploads/conciliaciones/' . $nombreArch;
            }
        }

        if ($existe) {
            // Actualizar
            $db->CRUD(
                "UPDATE convenio_conciliacion SET
                    monto_pago          = :monto_pago,
                    monto_aplicado      = :monto_aplicado,
                    monto_sobrante      = :monto_sobrante,
                    fecha_pago          = :fecha_pago,
                    comentario          = :comentario,
                    fecha_conciliacion  = NOW(),
                    usuario_concilia    = :usuario,
                    estatus             = 'conciliado'
                    " . ($archivoPath ? ", archivo_comprobante = :archivo" : "") . "
                 WHERE id_convenio_cliente = :id AND numero_semana = :num",
                array_merge([
                    'monto_pago'     => (float) $datos['monto_pago'],
                    'monto_aplicado' => (float) $datos['monto_aplicado'],
                    'monto_sobrante' => (float) $datos['monto_sobrante'],
                    'fecha_pago'     => $datos['fecha_pago'],
                    'comentario'     => $datos['comentario'] ?? null,
                    'usuario'        => $datos['usuario'],
                    'id'             => $datos['id_convenio'],
                    'num'            => $datos['numero_semana'],
                ], $archivoPath ? ['archivo' => $archivoPath] : [])
            );
        } else {
            // Insertar
            $db->CRUD(
                "INSERT INTO convenio_conciliacion (
                    id_convenio_cliente, numero_semana,
                    monto_pago, monto_aplicado, monto_sobrante,
                    fecha_pago, archivo_comprobante, comentario,
                    fecha_conciliacion, usuario_concilia, estatus
                ) VALUES (
                    :id, :num,
                    :monto_pago, :monto_aplicado, :monto_sobrante,
                    :fecha_pago, :archivo, :comentario,
                    NOW(), :usuario, 'conciliado'
                )",
                [
                    'id'             => $datos['id_convenio'],
                    'num'            => $datos['numero_semana'],
                    'monto_pago'     => (float) $datos['monto_pago'],
                    'monto_aplicado' => (float) $datos['monto_aplicado'],
                    'monto_sobrante' => (float) $datos['monto_sobrante'],
                    'fecha_pago'     => $datos['fecha_pago'],
                    'archivo'        => $archivoPath,
                    'comentario'     => $datos['comentario'] ?? null,
                    'usuario'        => $datos['usuario'],
                ]
            );
        }

        return self::resultado(true, 'Conciliación guardada correctamente.');

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al guardar conciliación.', null, $e->getMessage());
    }
}

// ════════════════════════════════════════════════
// SUBIR COMPROBANTE — semana vencida
// ════════════════════════════════════════════════

public static function subirComprobante($datos, $archivo)
{
    try {
        $db = new Database();

        // Verificar que la semana existe y está vencida
        $fila = $db->queryOne(
            "SELECT id, estatus_pago, pago_semanal
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id AND numero_semana = :num",
            ['id' => (int) $datos['id_convenio'], 'num' => (int) $datos['numero_semana']]
        );

        if (!$fila) {
            return self::resultado(false, 'Semana no encontrada.');
        }

        if (!in_array($fila['estatus_pago'], ['vencido', 'parcial', 'pendiente', 'pagado', 'pendiente_conciliar'])) {
            return self::resultado(false, 'Solo se pueden subir comprobantes de semanas vencidas, parciales, pendientes o pagadas.');
       }

        $esPagado = ($fila['estatus_pago'] === 'pagado');

        // Guardar archivo
        if (!$archivo || empty($archivo['tmp_name'])) {
            return self::resultado(false, 'El comprobante es obligatorio.');
        }

        $directorio = sparta_uploads_join('comprobantes');
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }

        $extension    = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidos   = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($extension, $permitidos)) {
            return self::resultado(false, 'Formato no permitido. Solo PDF, JPG o PNG.');
        }

        if ($archivo['size'] > 5 * 1024 * 1024) {
            return self::resultado(false, 'El archivo no debe exceder 5MB.');
        }

        $nombreArchivo = 'comprobante_conv' . $datos['id_convenio'] .
                         '_sem' . $datos['numero_semana'] .
                         '_' . date('Ymd_His') . '.' . $extension;
        $rutaCompleta  = sparta_uploads_join('comprobantes', $nombreArchivo);
        $rutaRelativa  = '/uploads/comprobantes/' . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return self::resultado(false, 'Error al guardar el archivo.');
        }


        // Semanas a las que aplica este pago
$semanasAplica = isset($datos['semanas_aplica'])
    ? array_map('intval', array_map('trim', explode(',', $datos['semanas_aplica'])))
    : [(int) $datos['numero_semana']];

// Actualizar cada semana → pendiente_conciliar (o mantener pagado si ya lo estaba)
foreach ($semanasAplica as $numSem) {
    if ($esPagado) {
        // Semana ya pagada: solo adjuntar el comprobante sin cambiar el estatus
        $db->CRUD(
            "UPDATE convenio_cliente_amortizacion SET
                fecha_pago_real   = COALESCE(fecha_pago_real, :fecha),
                comentario_gestor = :comentario,
                comprobante_path  = :comprobante
             WHERE id_convenio_cliente = :id AND numero_semana = :num",
            [
                'fecha'       => $datos['fecha_pago_real'],
                'comentario'  => $datos['comentario'] ?? '',
                'comprobante' => $rutaRelativa,
                'id'          => (int) $datos['id_convenio'],
                'num'         => $numSem,
            ]
        );
    } else {
        $db->CRUD(
            "UPDATE convenio_cliente_amortizacion SET
                estatus_pago      = 'pendiente_conciliar',
                fecha_pago_real   = :fecha,
                comentario_gestor = :comentario,
                comprobante_path  = :comprobante
             WHERE id_convenio_cliente = :id AND numero_semana = :num",
            [
                'fecha'       => $datos['fecha_pago_real'],
                'comentario'  => $datos['comentario'] ?? '',
                'comprobante' => $rutaRelativa,
                'id'          => (int) $datos['id_convenio'],
                'num'         => $numSem,
            ]
        );
    }
}

        // Verificar si todas las semanas quedaron pagadas → completar convenio
        $conteo = $db->queryOne(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN estatus_pago = 'pagado' THEN 1 ELSE 0 END) as pagadas
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id",
            ['id' => (int) $datos['id_convenio']]
        );

        if ($conteo && $conteo['total'] > 0 && $conteo['total'] == $conteo['pagadas']) {
            $db->CRUD(
                "UPDATE convenio_cliente SET estatus = 'completado', fecha_modifica = NOW()
                 WHERE id = :id",
                ['id' => (int) $datos['id_convenio']]
            );
        }

        return self::resultado(true, 'Comprobante subido correctamente.', [
            'semanas_pagadas' => $semanasAplica,
            'comprobante'     => $rutaRelativa,
        ]);

    } catch (\Exception $e) {
        return self::resultado(false, 'Error al subir comprobante.', null, $e->getMessage());
    }
}

public static function registrarConvenioGlobo($datos)
{
    try {
        error_log("=== registrarConvenioGlobo MODEL - INICIO ===");
        $db = new Database();

        // ── 1. Verificar que no haya convenio activo ──────────────────
        $activo = $db->queryOne(
            "SELECT id FROM convenio_cliente
             WHERE id_credito = :id AND estatus = 'activo'
             LIMIT 1",
            ['id' => (int) $datos['id_credito']]
        );

        if ($activo) {
            return self::resultado(false, 'Este crédito ya tiene un convenio activo.');
        }

        // ── 2. Validar que los montos cuadren (CORREGIDO) ──────────────
        $datos['id_producto_convenio'] = 6;
        $reactivacion = self::_resolverReactivacionAprobada($db, $datos);

        $pagoInicial       = (float) ($datos['pago_inicial_monto'] ?? 0); // Capturamos el inicial
        $pagosIgualesCant  = (int)   $datos['pagos_iguales_cantidad'];
        $pagosIgualesMonto = (float) $datos['pagos_iguales_monto'];
        $pagoGloboMonto    = (float) $datos['pago_globo_monto'];
        $totalAPagar       = (float) $datos['total_a_pagar'];
        $frecuencia        = $datos['frecuencia'] === 'quincenal' ? 'quincenal' : 'semanal';
        $diasIntervalo     = $frecuencia === 'quincenal' ? 14 : 7;

        // INCLUIMOS el pago inicial en la suma calculada
        $sumCalculada = round($pagoInicial + ($pagosIgualesCant * $pagosIgualesMonto) + $pagoGloboMonto, 2);

        if (abs($sumCalculada - $totalAPagar) > 1.00) {
            return self::resultado(false,
                "Los montos no cuadran: (Inicial \${$pagoInicial} + {$pagosIgualesCant} × \${$pagosIgualesMonto} + Globo \${$pagoGloboMonto}) = \${$sumCalculada}, pero total_a_pagar es \${$totalAPagar}."
            );
        }

        // ── 3. Calcular fechas ────────────────────────────────────────
        $fechaPrimerPago = $datos['fecha_primer_pago'];
        $hayInicial      = $pagoInicial > 0;
        $hayGlobo        = $pagoGloboMonto > 0;
        // filas = (inicial si existe) + N normales + (globo si existe)
        $totalRegistrosAmort = $pagosIgualesCant + ($hayInicial ? 1 : 0) + ($hayGlobo ? 1 : 0);

        $fechaUltimoPago = date('Y-m-d', strtotime(
            $fechaPrimerPago . ' +' . (($totalRegistrosAmort - 1) * $diasIntervalo) . ' days'
        ));

        // ── 4. Calcular porcentajes ───────────────────────────────────
        $adeudoOriginal = (float) $datos['adeudo_total_original'];
        $descuentoMonto = round($adeudoOriginal - $totalAPagar, 2);
        $porcentajeDescto = $adeudoOriginal > 0 ? round(($descuentoMonto / $adeudoOriginal) * 100, 2) : 0;

        // ── 5. Insertar en convenio_cliente (CORREGIDO EL NULL) ────────
        $ok = $db->CRUD(
            "INSERT INTO convenio_cliente (
                id_credito, id_producto_convenio, id_producto_convenio_detalle,
                nombre_cliente, bucket_morosidad_real, dias_mora, avance_pago_plazo,
                adeudo_total_original, porcentaje_descuento, descuento_monto,
                total_a_pagar, monto_adicional, pago_inicial_monto,
                numero_semanas, pago_semanal,
                frecuencia, tipo, condonacion_aplicada,
                fecha_acuerdo, fecha_primer_pago, fecha_ultimo_pago,
                estatus, usuario_alta
            ) VALUES (
                :id_credito, 6, 7,
                :nombre_cliente, :bucket, :dias_mora, :avance_pago,
                :adeudo_original, :pct_descuento, :descuento_monto,
                :total_pagar, 0.00, :pago_inicial,
                :num_pagos, :pago_semanal,
                :frecuencia, 'globo', :condonacion,
                :fecha_acuerdo, :fecha_primer_pago, :fecha_ultimo_pago,
                'activo', :usuario
            )",
            [
                'id_credito'      => (int)$datos['id_credito'],
                'nombre_cliente'  => $datos['nombre_cliente'],
                'bucket'          => $datos['bucket_morosidad_real'],
                'dias_mora'       => (int)$datos['dias_mora'],
                'avance_pago'     => $datos['avance_pago_plazo'] ?? '',
                'adeudo_original' => $adeudoOriginal,
                'pct_descuento'   => $porcentajeDescto,
                'descuento_monto' => $descuentoMonto,
                'total_pagar'     => $totalAPagar,
                'pago_inicial'    => $pagoInicial, // Ahora se guarda correctamente
                'num_pagos'       => $pagosIgualesCant,
                'pago_semanal'    => $pagosIgualesMonto,
                'frecuencia'      => $frecuencia,
                'condonacion'     => $datos['condonacion_aplicada'] ?? null,
                'fecha_acuerdo'   => date('Y-m-d'),
                'fecha_primer_pago' => $fechaPrimerPago,
                'fecha_ultimo_pago' => $fechaUltimoPago,
                'usuario'         => $datos['usuario_alta'],
            ]
        );

        $idConvenio = (int) $db->queryOne("SELECT LAST_INSERT_ID() AS id")['id'];
        self::_marcarConvenioReactivado($db, $idConvenio, $reactivacion, $datos);

        // ── 6. Generar tabla de amortización completa ─────────────────
        $saldoActual = $totalAPagar;

        for ($p = 1; $p <= $totalRegistrosAmort; $p++) {
            if ($hayInicial && $p === 1) {
                // Fila 1 (solo si hay pago inicial): Pago Inicial
                $tipoPago = "Inicial";
                $monto = $pagoInicial;
            } elseif ($hayGlobo && $p === $totalRegistrosAmort) {
                // Última fila (solo si hay pago globo > 0): Pago Globo
                $tipoPago = "Globo";
                $monto = $pagoGloboMonto;
            } else {
                // Filas intermedias: Pagos normales
                $tipoPago = "Normal";
                $monto = $pagosIgualesMonto;
            }

            $fechaPago = date('Y-m-d', strtotime($fechaPrimerPago . ' +' . (($p - 1) * $diasIntervalo) . ' days'));
            $saldoActual = round($saldoActual - $monto, 2);
            if ($saldoActual < 0) $saldoActual = 0;

            $db->CRUD(
                "INSERT INTO convenio_cliente_amortizacion
                    (id_convenio_cliente, numero_semana, fecha_pago, pago_semanal, capital, saldo_restante)
                 VALUES (:id, :num, :fecha, :pago, :capital, :saldo)",
                [
                    'id'      => $idConvenio,
                    'num'     => $p,
                    'fecha'   => $fechaPago,
                    'pago'    => $monto,
                    'capital' => $monto,
                    'saldo'   => $saldoActual,
                ]
            );
        }

        return self::resultado(true, 'Convenio registrado correctamente.');

    } catch (\Exception $e) {
        return self::resultado(false, 'Error: ' . $e->getMessage());
    }
}

    // ══════════════════════════════════════════════════════════
    // ESTADÍSTICAS CONVENIOS
    // ══════════════════════════════════════════════════════════

    /** Calcula el primer y último día de un mes. */
    private static function _cvRangoMes(int $anio, int $mes): array
    {
        $finDia   = (int) date('t', mktime(0, 0, 0, $mes, 1, $anio));
        $fechaIni = sprintf('%04d-%02d-01', $anio, $mes);
        $fechaFin = sprintf('%04d-%02d-%02d', $anio, $mes, $finDia);
        return [$fechaIni, $fechaFin];
    }

    private static function _cvNormYmd(?string $s): ?string
    {
        if ($s === null) {
            return null;
        }
        $s = trim($s);
        if ($s === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
            return null;
        }
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $s);
        return ($d && $d->format('Y-m-d') === $s) ? $s : null;
    }

    /** Mismo texto que Gastos Cobranza (`iniYmd + ' a ' + finYmd`). */
    private static function _cvPeriodoLabelRango(string $fi, string $ff): string
    {
        return $fi . ' a ' . $ff;
    }

    /**
     * Prioriza fecha_inicio + fecha_fin (Y-m-d, fin no posterior a hoy). Si no, usa el mes calendario de anio/mes.
     *
     * @return array{ini:string,fin:string,label:string}
     */
    private static function _cvResolverRangoFechas(int $anio, ?int $mes, ?string $fechaInicio, ?string $fechaFin): array
    {
        $mesN = ($mes === null || $mes < 1 || $mes > 12) ? (int) date('n') : (int) $mes;
        $anioN = $anio > 0 ? $anio : (int) date('Y');

        $fi = self::_cvNormYmd($fechaInicio);
        $ff = self::_cvNormYmd($fechaFin);
        $hoy = (new \DateTimeImmutable('today'))->format('Y-m-d');
        if ($fi !== null && $ff !== null && strcmp($fi, $ff) <= 0 && strcmp($ff, $hoy) <= 0) {
            return [
                'ini' => $fi,
                'fin' => $ff,
                'label' => self::_cvPeriodoLabelRango($fi, $ff),
            ];
        }

        [$a, $b] = self::_cvRangoMes($anioN, $mesN);

        return [
            'ini' => $a,
            'fin' => $b,
            'label' => self::_cvPeriodoLabelRango($a, $b),
        ];
    }

    /** Rango por defecto (lunes de la semana actual → hoy), mismo criterio que Gastos Cobranza. */
    public static function cvRangoLunesHoyEstadisticas(): array
    {
        $hoy = new \DateTimeImmutable('today');
        $dow = (int) $hoy->format('N');
        $lun = $hoy->modify('-' . ($dow - 1) . ' days');

        return [$lun->format('Y-m-d'), $hoy->format('Y-m-d')];
    }

    /**
     * Valida rango Y-m-d para estadísticas (fin no posterior a hoy).
     *
     * @return array{ini:string,fin:string}|null
     */
    public static function cvParseRangoEstadisticas($fechaInicio, $fechaFin): ?array
    {
        $fi = self::_cvNormYmd(is_string($fechaInicio) ? $fechaInicio : null);
        $ff = self::_cvNormYmd(is_string($fechaFin) ? $fechaFin : null);
        if ($fi === null || $ff === null || strcmp($fi, $ff) > 0) {
            return null;
        }
        $hoy = (new \DateTimeImmutable('today'))->format('Y-m-d');
        if (strcmp($ff, $hoy) > 0) {
            return null;
        }

        return ['ini' => $fi, 'fin' => $ff];
    }

    // ──────────────────────────────────────────────────────────
    // getEstadisticasConvenios
    // KPIs globales (totales por estatus) + nuevos del período
    // (fecha_alta dentro del mes/año seleccionado).
    // ──────────────────────────────────────────────────────────

    public static function getEstadisticasConvenios(int $anio, ?int $mes, ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        try {
            $db = new Database();
            $rango = self::_cvResolverRangoFechas($anio, $mes, $fechaInicio, $fechaFin);
            $fechaIni = $rango['ini'];
            $fechaFin = $rango['fin'];
            $periodoLabel = $rango['label'];

            // ── KPIs globales (sin filtro de fecha) ──
            $kpis = $db->queryOne(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN LOWER(estatus) = 'activo'     THEN 1 ELSE 0 END) AS total_activos,
                    SUM(CASE WHEN LOWER(estatus) = 'completado' THEN 1 ELSE 0 END) AS total_completados,
                    SUM(CASE WHEN LOWER(estatus) = 'cancelado'  THEN 1 ELSE 0 END) AS total_cancelados
                 FROM convenio_cliente",
                []
            ) ?: [];

            // ── Nuevos del período (fecha_alta dentro del mes) ──
            $nuevos = $db->queryOne(
                "SELECT
                    COUNT(*) AS nuevos_total,
                    SUM(CASE WHEN LOWER(estatus) = 'activo'     THEN 1 ELSE 0 END) AS nuevos_activos,
                    SUM(CASE WHEN LOWER(estatus) = 'completado' THEN 1 ELSE 0 END) AS nuevos_completados,
                    SUM(CASE WHEN LOWER(estatus) = 'cancelado'  THEN 1 ELSE 0 END) AS nuevos_cancelados
                 FROM convenio_cliente
                 WHERE DATE(fecha_alta) BETWEEN :fi AND :ff",
                ['fi' => $fechaIni, 'ff' => $fechaFin]
            ) ?: [];

            $datos = [
                'periodo_label'      => $periodoLabel,
                'fecha_ini'          => $fechaIni,
                'fecha_fin'          => $fechaFin,
                'total_convenios'    => (int) ($kpis['total']             ?? 0),
                'total_activos'      => (int) ($kpis['total_activos']     ?? 0),
                'total_completados'  => (int) ($kpis['total_completados'] ?? 0),
                'total_cancelados'   => (int) ($kpis['total_cancelados']  ?? 0),
                'nuevos_total'       => (int) ($nuevos['nuevos_total']     ?? 0),
                'nuevos_activos'     => (int) ($nuevos['nuevos_activos']   ?? 0),
                'nuevos_completados' => (int) ($nuevos['nuevos_completados'] ?? 0),
                'nuevos_cancelados'  => (int) ($nuevos['nuevos_cancelados'] ?? 0),
            ];

            return self::resultado(true, 'OK', $datos);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener estadísticas de convenios.', [], $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // getEstadisticasConveniosDetalle
    // Desglose por producto al clic: convenio_cliente JOIN
    // producto_convenio, filtrado por estatus + fecha_alta.
    // ──────────────────────────────────────────────────────────

    public static function getEstadisticasConveniosDetalle(int $anio, ?int $mes, string $tipo = 'activos', ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $tiposValidos = ['activos', 'completados', 'cancelados'];
        if (!in_array($tipo, $tiposValidos, true)) {
            $tipo = 'activos';
        }
        try {
            $db = new Database();
            $rango = self::_cvResolverRangoFechas($anio, $mes, $fechaInicio, $fechaFin);
            $fechaIni = $rango['ini'];
            $fechaFin = $rango['fin'];
            $periodoLabel = $rango['label'];

            $estatusMap = ['activos' => 'activo', 'completados' => 'completado', 'cancelados' => 'cancelado'];
            $estatus    = $estatusMap[$tipo];

            $rows = $db->queryAll(
                "SELECT
                    COALESCE(pc.nombre, 'Sin producto') AS nombre,
                    COUNT(*) AS cnt
                 FROM convenio_cliente cc
                 LEFT JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE LOWER(cc.estatus) = :estatus
                   AND DATE(cc.fecha_alta) BETWEEN :fi AND :ff
                 GROUP BY pc.id, pc.nombre
                 ORDER BY cnt DESC",
                ['estatus' => $estatus, 'fi' => $fechaIni, 'ff' => $fechaFin]
            ) ?: [];

            $datos = [
                'tipo'            => $tipo,
                'periodo_label'   => $periodoLabel,
                'fecha_ini'       => $fechaIni,
                'fecha_fin'       => $fechaFin,
                'total'           => (int) array_sum(array_column($rows, 'cnt')),
                'por_producto'    => $rows,
            ];

            return self::resultado(true, 'OK', $datos);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener desglose por producto.', [], $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // getEstadisticasCierresCredito
    // Recuperación: monto comprometido (activos+completados) vs
    // monto pagado en amortizaciones del período seleccionado.
    // ──────────────────────────────────────────────────────────

    public static function getEstadisticasCierresCredito(int $anio, ?int $mes, ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        try {
            $db = new Database();
            $rango = self::_cvResolverRangoFechas($anio, $mes, $fechaInicio, $fechaFin);
            $fechaIni = $rango['ini'];
            $fechaFin = $rango['fin'];

            // Monto total comprometido (convenios activos + completados, sin filtro de fecha)
            $montos = $db->queryOne(
                "SELECT COALESCE(SUM(total_a_pagar), 0) AS monto_comprometido
                 FROM convenio_cliente
                 WHERE LOWER(estatus) IN ('activo', 'completado')",
                []
            ) ?: [];

            // Semanas cuya fecha_pago cae en el período, agrupadas por estatus_pago
            $semanas = $db->queryOne(
                "SELECT
                    COALESCE(SUM(CASE WHEN LOWER(estatus_pago) = 'pagado'              THEN pago_semanal ELSE 0 END), 0) AS monto_pagado,
                    COUNT(CASE WHEN LOWER(estatus_pago) = 'pagado'              THEN 1 END) AS semanas_pagadas,
                    COUNT(CASE WHEN LOWER(estatus_pago) = 'pendiente'           THEN 1 END) AS semanas_pendientes,
                    COUNT(CASE WHEN LOWER(estatus_pago) = 'vencido'             THEN 1 END) AS semanas_vencidas,
                    COUNT(CASE WHEN LOWER(estatus_pago) = 'parcial'             THEN 1 END) AS semanas_parciales,
                    COUNT(CASE WHEN LOWER(estatus_pago) = 'cancelado'           THEN 1 END) AS semanas_canceladas,
                    COUNT(CASE WHEN LOWER(estatus_pago) = 'pendiente_conciliar' THEN 1 END) AS semanas_conciliar
                 FROM convenio_cliente_amortizacion
                 WHERE fecha_pago BETWEEN :fi AND :ff",
                ['fi' => $fechaIni, 'ff' => $fechaFin]
            ) ?: [];

            $montoComp  = (float) ($montos['monto_comprometido'] ?? 0);
            $montoRecup = (float) ($semanas['monto_pagado']      ?? 0);
            $pctRecup   = $montoComp > 0 ? round(($montoRecup / $montoComp) * 100, 1) : 0.0;

            if ($pctRecup >= 60)     { $badgeText = 'Recuperación alta';  $badgeClass = 'success'; }
            elseif ($pctRecup >= 30) { $badgeText = 'Recuperación media'; $badgeClass = 'warning'; }
            else                     { $badgeText = 'Recuperación baja';  $badgeClass = 'danger';  }

            $datos = [
                'fecha_ini'                => $fechaIni,
                'fecha_fin'                => $fechaFin,
                'monto_comprometido'       => $montoComp,
                'monto_recuperado'         => $montoRecup,
                'pct_recuperacion'         => $pctRecup,
                'recuperacion_badge_text'  => $badgeText,
                'recuperacion_badge_class' => $badgeClass,
                'semanas_pagadas'          => (int) ($semanas['semanas_pagadas']    ?? 0),
                'semanas_pendientes'       => (int) ($semanas['semanas_pendientes'] ?? 0),
                'semanas_vencidas'         => (int) ($semanas['semanas_vencidas']   ?? 0),
                'semanas_parciales'        => (int) ($semanas['semanas_parciales']  ?? 0),
                'semanas_canceladas'       => (int) ($semanas['semanas_canceladas'] ?? 0),
                'semanas_conciliar'        => (int) ($semanas['semanas_conciliar']  ?? 0),
            ];

            return self::resultado(true, 'OK', $datos);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener recuperación.', [], $e->getMessage());
        }
    }

    // ──────────────────────────────────────────────────────────
    // getEstadisticasAsignacionCreditos
    // Penetración: créditos en despacho (asigna_creditos_despacho
    // estatus=1) cruzados con convenio_cliente estatus=activo.
    // ──────────────────────────────────────────────────────────

    public static function getEstadisticasAsignacionCreditos(int $anio, ?int $mes, ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        try {
            $db = new Database();
            $rango = self::_cvResolverRangoFechas($anio, $mes, $fechaInicio, $fechaFin);
            $fechaIni = $rango['ini'];
            $fechaFin = $rango['fin'];

            // Total créditos activos en despacho
            $despacho = $db->queryOne(
                "SELECT COUNT(*) AS total_despacho
                 FROM asigna_creditos_despacho
                 WHERE estatus = 1",
                []
            ) ?: [];

            // De esos, cuántos tienen convenio activo
            $conConvenio = $db->queryOne(
                "SELECT COUNT(DISTINCT acd.id_credito) AS con_convenio
                 FROM asigna_creditos_despacho acd
                 INNER JOIN convenio_cliente cc
                         ON cc.id_credito = acd.id_credito
                        AND LOWER(cc.estatus) = 'activo'
                 WHERE acd.estatus = 1",
                []
            ) ?: [];

            $totalDesp = (int) ($despacho['total_despacho']  ?? 0);
            $totalConv = (int) ($conConvenio['con_convenio'] ?? 0);
            $sinConv   = max(0, $totalDesp - $totalConv);
            $pctPen    = $totalDesp > 0 ? round(($totalConv / $totalDesp) * 100, 1) : 0.0;

            if ($pctPen >= 40)     { $badgeText = 'Cobertura alta';  $badgeClass = 'success'; }
            elseif ($pctPen >= 20) { $badgeText = 'Cobertura media'; $badgeClass = 'warning'; }
            else                   { $badgeText = 'Cobertura baja';  $badgeClass = 'danger';  }

            // ── KPIs de entidades de despacho ──────────────────────────────
            // Total despachos activos (filas en tabla despachos)
            $despActivos = $db->queryOne(
                "SELECT COUNT(*) AS total FROM despachos WHERE estatus = 'Activo'",
                []
            ) ?: [];

            // Despachos con al menos un convenio activo
            $despConConvenio = $db->queryOne(
                "SELECT COUNT(DISTINCT d.id) AS total
                 FROM despachos d
                 INNER JOIN asigna_creditos_despacho acd ON acd.id_despacho = d.id AND acd.estatus = 1
                 INNER JOIN convenio_cliente cc
                         ON cc.id_credito = acd.id_credito
                        AND LOWER(cc.estatus) = 'activo'
                 WHERE d.estatus = 'Activo'",
                []
            ) ?: [];

            // Células (id_celula: 1=Despacho, 2=Gestión Call Center)
            $celulaRows = $db->queryAll(
                "SELECT id_celula, COUNT(*) AS cnt
                 FROM despachos
                 WHERE estatus = 'Activo'
                 GROUP BY id_celula",
                []
            ) ?: [];
            $celulaMap = [];
            foreach ($celulaRows as $row) {
                $celulaMap[(int) $row['id_celula']] = (int) $row['cnt'];
            }

            // Despacho con más convenios activos (TOP 1)
            $topDespacho = $db->queryOne(
                "SELECT d.id,
                        CONCAT_WS(' ', per.nombres, per.apellidop) AS nombre_despacho,
                        sub.total_convenios
                 FROM (
                     SELECT acd.id_despacho, COUNT(cc.id) AS total_convenios
                     FROM asigna_creditos_despacho acd
                     INNER JOIN convenio_cliente cc
                             ON cc.id_credito = acd.id_credito
                            AND LOWER(cc.estatus) = 'activo'
                     WHERE acd.estatus = 1
                     GROUP BY acd.id_despacho
                     ORDER BY total_convenios DESC
                     LIMIT 1
                 ) sub
                 INNER JOIN despachos d ON d.id = sub.id_despacho AND d.estatus = 'Activo'
                 LEFT JOIN persona per ON per.id = d.id_persona",
                []
            ) ?: [];

            $totalDespActivos  = (int) ($despActivos['total']      ?? 0);
            $despConConv       = (int) ($despConConvenio['total']  ?? 0);
            $despSinConv       = max(0, $totalDespActivos - $despConConv);

            // Despacho con menos convenios activos (BOTTOM 1 — solo los que tienen al menos 1)
            $bottomDespacho = $db->queryOne(
                "SELECT d.id,
                        CONCAT_WS(' ', per.nombres, per.apellidop) AS nombre_despacho,
                        sub.total_convenios
                 FROM (
                     SELECT acd.id_despacho, COUNT(cc.id) AS total_convenios
                     FROM asigna_creditos_despacho acd
                     INNER JOIN convenio_cliente cc
                             ON cc.id_credito = acd.id_credito
                            AND LOWER(cc.estatus) = 'activo'
                     WHERE acd.estatus = 1
                     GROUP BY acd.id_despacho
                     ORDER BY total_convenios ASC
                     LIMIT 1
                 ) sub
                 INNER JOIN despachos d ON d.id = sub.id_despacho AND d.estatus = 'Activo'
                 LEFT JOIN persona per ON per.id = d.id_persona",
                []
            ) ?: [];

            // Créditos en gestión (asignados a despacho activo)
            $creditosGestion = $db->queryOne(
                "SELECT COUNT(DISTINCT acd.id_credito) AS total
                 FROM asigna_creditos_despacho acd
                 INNER JOIN despachos d ON d.id = acd.id_despacho AND d.estatus = 'Activo'
                 WHERE acd.estatus = 1",
                []
            ) ?: [];

            // Gestores con meta cumplida (≥ 5 convenios activos)
            $gestoresEnMeta = $db->queryOne(
                "SELECT COUNT(*) AS total FROM (
                    SELECT d.id
                    FROM despachos d
                    INNER JOIN asigna_creditos_despacho acd ON acd.id_despacho = d.id AND acd.estatus = 1
                    INNER JOIN convenio_cliente cc
                            ON cc.id_credito = acd.id_credito
                           AND LOWER(cc.estatus) = 'activo'
                    WHERE d.estatus = 'Activo'
                    GROUP BY d.id
                    HAVING COUNT(cc.id) >= 5
                ) sub",
                []
            ) ?: [];

            // Gestor más activo del período (convenios pactados en el rango)
            $topGestorPeriodo = $db->queryOne(
                "SELECT d.id,
                        CONCAT_WS(' ', per.nombres, per.apellidop) AS nombre_gestor,
                        sub.total_convenios
                 FROM (
                     SELECT acd.id_despacho, COUNT(cc.id) AS total_convenios
                     FROM asigna_creditos_despacho acd
                     INNER JOIN convenio_cliente cc
                             ON cc.id_credito = acd.id_credito
                            AND LOWER(cc.estatus) = 'activo'
                            AND cc.fecha_acuerdo BETWEEN :fi AND :ff
                     WHERE acd.estatus = 1
                     GROUP BY acd.id_despacho
                     ORDER BY total_convenios DESC
                     LIMIT 1
                 ) sub
                 INNER JOIN despachos d ON d.id = sub.id_despacho AND d.estatus = 'Activo'
                 LEFT JOIN persona per ON per.id = d.id_persona",
                ['fi' => $fechaIni, 'ff' => $fechaFin]
            ) ?: [];

            // % gestores activos y promedio de convenios por gestor (calculados)
            $pctGestoresActivos      = $totalDespActivos > 0
                ? round(($despConConv / $totalDespActivos) * 100, 1) : 0.0;
            $promedioConveniosGestor = $despConConv > 0
                ? round(($totalConv / $despConConv), 1) : 0.0;

            $datos = [
                'fecha_ini'               => $fechaIni,
                'fecha_fin'               => $fechaFin,
                'total_despacho'          => $totalDesp,
                'con_convenio_activo'     => $totalConv,
                'sin_convenio'            => $sinConv,
                'pct_penetracion'         => $pctPen,
                'penetracion_badge_text'  => $badgeText,
                'penetracion_badge_class' => $badgeClass,
                // ── nuevos KPIs de entidades de despacho ──
                'total_despachos_activos' => $totalDespActivos,
                'despachos_con_convenio'  => $despConConv,
                'despachos_sin_convenio'  => $despSinConv,
                'celula_despacho_cnt'     => $celulaMap[1] ?? 0,
                'celula_callcenter_cnt'   => $celulaMap[2] ?? 0,
                'top_despacho_nombre'     => $topDespacho['nombre_despacho']  ?? '—',
                'top_despacho_convenios'  => (int) ($topDespacho['total_convenios'] ?? 0),
                'bottom_despacho_nombre'     => $bottomDespacho['nombre_despacho'] ?? '—',
                'bottom_despacho_convenios'  => (int) ($bottomDespacho['total_convenios'] ?? 0),
                // ── KPIs adicionales gestores ──
                'creditos_en_gestion'         => (int) ($creditosGestion['total']     ?? 0),
                'gestores_en_meta'            => (int) ($gestoresEnMeta['total']      ?? 0),
                'top_gestor_periodo_nombre'   => $topGestorPeriodo['nombre_gestor']   ?? '—',
                'top_gestor_periodo_convenios'=> (int) ($topGestorPeriodo['total_convenios'] ?? 0),
                'pct_gestores_activos'        => $pctGestoresActivos,
                'promedio_convenios_gestor'   => $promedioConveniosGestor,
            ];

            return self::resultado(true, 'OK', $datos);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener penetración de cartera.', [], $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // REPORTERIA CONVENIOS
    // ---------------------------------------------------------------------

    public static function obtenerReporteHistoricoConvenios(array $filtros = []): array
    {
        try {
            $db = new Database();

            $params = [];
            $where = ['1 = 1'];

            $fechaInicio = self::_cvNormYmd(isset($filtros['fecha_inicio']) ? (string) $filtros['fecha_inicio'] : null);
            $fechaFin = self::_cvNormYmd(isset($filtros['fecha_fin']) ? (string) $filtros['fecha_fin'] : null);
            if ($fechaInicio !== null) {
                $where[] = 'COALESCE(cc.fecha_acuerdo, DATE(cc.fecha_alta)) >= :fecha_inicio';
                $params['fecha_inicio'] = $fechaInicio;
            }
            if ($fechaFin !== null) {
                $where[] = 'COALESCE(cc.fecha_acuerdo, DATE(cc.fecha_alta)) <= :fecha_fin';
                $params['fecha_fin'] = $fechaFin;
            }

            $estatus = isset($filtros['estatus']) ? strtolower(trim((string) $filtros['estatus'])) : '';
            if ($estatus !== '' && in_array($estatus, ['activo', 'completado', 'cancelado'], true)) {
                $where[] = 'LOWER(cc.estatus) = :estatus';
                $params['estatus'] = $estatus;
            }

            $idProducto = isset($filtros['id_producto_convenio']) ? (int) $filtros['id_producto_convenio'] : 0;
            if ($idProducto > 0) {
                $where[] = 'cc.id_producto_convenio = :id_producto_convenio';
                $params['id_producto_convenio'] = $idProducto;
            }

            $celula = strtolower(trim((string) ($filtros['celula'] ?? '')));
            if (in_array($celula, ['1', 'despachos', 'despacho'], true)) {
                $where[] = 'cc.id_celula = 1';
                $celula = 'despachos';
            } elseif (in_array($celula, ['2', 'callcenter', 'call_center', 'call center'], true)) {
                $where[] = 'cc.id_celula = 2';
                $celula = 'callcenter';
            } elseif (in_array($celula, ['3', 'campo'], true)) {
                $where[] = '(cc.id_celula = 3 OR cc.id_celula IS NULL OR cc.id_celula NOT IN (1, 2))';
                $celula = 'campo';
            } else {
                $celula = '';
            }

            $q = isset($filtros['q']) ? trim((string) $filtros['q']) : '';
            if ($q !== '') {
                $where[] = "(cc.nombre_cliente LIKE :q
                    OR CAST(cc.id AS CHAR) LIKE :q
                    OR CAST(cc.id_credito AS CHAR) LIKE :q
                    OR pc.nombre LIKE :q
                    OR cc.usuario_alta LIKE :q)";
                $params['q'] = '%' . $q . '%';
            }

            $whereCatalogoGestores = $where;
            $paramsCatalogoGestores = $params;

            $gestor = isset($filtros['gestor']) ? trim((string) $filtros['gestor']) : '';
            if ($gestor !== '') {
                $where[] = 'cc.usuario_alta = :gestor';
                $params['gestor'] = $gestor;
            }

            $limitRaw = isset($filtros['limit']) ? strtolower(trim((string) $filtros['limit'])) : '0';
            $sinLimite = in_array($limitRaw, ['', '0', 'all', 'todos'], true);
            $limit = $sinLimite ? 0 : (int) $limitRaw;
            if ($limit > 0 && $limit < 50) {
                $limit = 50;
            }
            if ($limit > 3000) {
                $limit = 3000;
            }

            $colsReactivacion = [];
            foreach ([
                'es_reactivado',
                'id_convenio_origen',
                'id_peticion_reactivacion',
                'reactivacion_numero',
                'motivo_reactivacion',
                'usuario_reactiva',
                'fecha_reactivacion',
            ] as $col) {
                if (self::_tablaTieneColumna($db, 'convenio_cliente', $col)) {
                    $colsReactivacion[] = 'cc.' . $col;
                }
            }
            $selectReactivacion = $colsReactivacion ? ', ' . implode(', ', $colsReactivacion) : '';

            $whereSql = implode("\n                 AND ", $where);
            $limitSql = $limit > 0 ? "\n                 LIMIT {$limit}" : '';
            $sql = "SELECT
                    cc.id AS id_convenio,
                    cc.id_credito,
                    cc.nombre_cliente,
                    COALESCE(cc.fecha_acuerdo, DATE(cc.fecha_alta)) AS fecha_convenio,
                    cc.fecha_alta,
                    cc.fecha_modifica,
                    cc.fecha_acuerdo,
                    cc.fecha_primer_pago,
                    cc.fecha_ultimo_pago,
                    cc.fecha_cancelacion,
                    cc.adeudo_total_original AS monto_original,
                    COALESCE(pc.nombre, 'Sin oferta') AS oferta_seleccionada,
                    cc.id_producto_convenio,
                    cc.porcentaje_descuento,
                    cc.descuento_monto,
                    cc.monto_adicional,
                    cc.total_a_pagar AS monto_convenio,
                    cc.pago_semanal,
                    cc.numero_semanas,
                    cc.estatus,
                    cc.usuario_alta,
                    cc.usuario_modifica,
                    cc.usuario_cancela,
                    cc.motivo_cancelamiento,
                    cc.solicitud_cancelamiento_fecha,
                    cc.base_calculo,
                    cc.tipo_calendario,
                    cc.id_celula,
                    CASE cc.id_celula
                        WHEN 1 THEN 'Despachos'
                        WHEN 2 THEN 'Call Center'
                        WHEN 3 THEN 'Campo'
                        ELSE COALESCE(CAST(cc.id_celula AS CHAR), 'Sin celula')
                    END AS celula,
                    COALESCE(am.total_cuotas, 0) AS total_cuotas,
                    COALESCE(am.cuotas_pagadas, 0) AS cuotas_pagadas,
                    COALESCE(am.cuotas_parciales, 0) AS cuotas_parciales,
                    COALESCE(am.cuotas_vencidas, 0) AS cuotas_vencidas,
                    COALESCE(am.total_pagado, 0) AS total_pagado,
                    GREATEST(cc.total_a_pagar - COALESCE(am.total_pagado, 0), 0) AS saldo_reportado
                    {$selectReactivacion}
                 FROM convenio_cliente cc
                 LEFT JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 LEFT JOIN (
                    SELECT
                        id_convenio_cliente,
                        COUNT(*) AS total_cuotas,
                        SUM(CASE WHEN LOWER(estatus_pago) = 'pagado' THEN 1 ELSE 0 END) AS cuotas_pagadas,
                        SUM(CASE WHEN LOWER(estatus_pago) = 'parcial' THEN 1 ELSE 0 END) AS cuotas_parciales,
                        SUM(CASE WHEN LOWER(estatus_pago) = 'vencido' THEN 1 ELSE 0 END) AS cuotas_vencidas,
                        SUM(
                            CASE
                                WHEN monto_pagado REGEXP '^-?[0-9]+(\\.[0-9]+)?$'
                                THEN CAST(monto_pagado AS DECIMAL(14,2))
                                ELSE 0
                            END + COALESCE(monto_secundario, 0)
                        ) AS total_pagado
                    FROM convenio_cliente_amortizacion
                    GROUP BY id_convenio_cliente
                 ) am ON am.id_convenio_cliente = cc.id
                 WHERE {$whereSql}
                 ORDER BY COALESCE(cc.fecha_acuerdo, DATE(cc.fecha_alta)) DESC, cc.id DESC{$limitSql}";

            $rows = $db->queryAll($sql, $params ?: null) ?: [];

            $resumen = [
                'total_convenios' => count($rows),
                'monto_original' => 0.0,
                'monto_convenio' => 0.0,
                'descuento_total' => 0.0,
                'total_pagado' => 0.0,
                'saldo_reportado' => 0.0,
                'activos' => 0,
                'completados' => 0,
                'cancelados' => 0,
            ];

            foreach ($rows as $row) {
                $resumen['monto_original'] += (float) ($row['monto_original'] ?? 0);
                $resumen['monto_convenio'] += (float) ($row['monto_convenio'] ?? 0);
                $resumen['descuento_total'] += (float) ($row['descuento_monto'] ?? 0);
                $resumen['total_pagado'] += (float) ($row['total_pagado'] ?? 0);
                $resumen['saldo_reportado'] += (float) ($row['saldo_reportado'] ?? 0);
                $estatusRow = strtolower((string) ($row['estatus'] ?? ''));
                if ($estatusRow === 'activo') {
                    $resumen['activos']++;
                } elseif ($estatusRow === 'completado') {
                    $resumen['completados']++;
                } elseif ($estatusRow === 'cancelado') {
                    $resumen['cancelados']++;
                }
            }

            $productos = $db->queryAll(
                "SELECT id, nombre
                 FROM producto_convenio
                 ORDER BY nombre ASC",
                []
            ) ?: [];

            $whereGestoresSql = implode("\n                 AND ", $whereCatalogoGestores);
            $gestores = $db->queryAll(
                "SELECT
                    cc.usuario_alta AS usuario,
                    CASE cc.id_celula
                        WHEN 1 THEN 'Despachos'
                        WHEN 2 THEN 'Call Center'
                        WHEN 3 THEN 'Campo'
                        ELSE COALESCE(CAST(cc.id_celula AS CHAR), 'Sin celula')
                    END AS celula,
                    COUNT(*) AS total
                 FROM convenio_cliente cc
                 LEFT JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE {$whereGestoresSql}
                   AND COALESCE(TRIM(cc.usuario_alta), '') <> ''
                 GROUP BY cc.usuario_alta, cc.id_celula
                 ORDER BY cc.usuario_alta ASC",
                $paramsCatalogoGestores ?: null
            ) ?: [];

            return self::resultado(true, 'Reporte historico de convenios.', [
                'rows' => $rows,
                'resumen' => $resumen,
                'catalogos' => [
                    'productos' => $productos,
                    'gestores' => $gestores,
                ],
                'filtros' => [
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'estatus' => $estatus,
                    'id_producto_convenio' => $idProducto,
                    'celula' => $celula,
                    'gestor' => $gestor,
                    'q' => $q,
                    'limit' => $limit > 0 ? $limit : 'todos',
                ],
                'actualizado_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener el reporte historico de convenios.', [], $e->getMessage());
        }
    }

    public static function obtenerReporteIndividualConvenio(int $idConvenio = 0, int $idCredito = 0): array
    {
        try {
            $db = new Database();
            if ($idConvenio <= 0 && $idCredito <= 0) {
                return self::resultado(false, 'Indique un ID de convenio o ID de credito.');
            }

            $colsReactivacion = [];
            foreach ([
                'es_reactivado',
                'id_convenio_origen',
                'id_peticion_reactivacion',
                'reactivacion_numero',
                'motivo_reactivacion',
                'usuario_reactiva',
                'fecha_reactivacion',
            ] as $col) {
                if (self::_tablaTieneColumna($db, 'convenio_cliente', $col)) {
                    $colsReactivacion[] = 'cc.' . $col;
                }
            }
            $selectReactivacion = $colsReactivacion ? ', ' . implode(', ', $colsReactivacion) : '';

            $where = $idConvenio > 0 ? 'cc.id = :id' : 'cc.id_credito = :id_credito';
            $params = $idConvenio > 0 ? ['id' => $idConvenio] : ['id_credito' => $idCredito];

            $convenio = $db->queryOne(
                "SELECT
                    cc.*,
                    cc.id AS id_convenio,
                    COALESCE(pc.nombre, 'Sin oferta') AS oferta_seleccionada,
                    CASE cc.id_celula
                        WHEN 1 THEN 'Despachos'
                        WHEN 2 THEN 'Call Center'
                        WHEN 3 THEN 'Campo'
                        ELSE COALESCE(CAST(cc.id_celula AS CHAR), 'Sin celula')
                    END AS celula
                    {$selectReactivacion}
                 FROM convenio_cliente cc
                 LEFT JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE {$where}
                 ORDER BY cc.id DESC
                 LIMIT 1",
                $params
            );

            if (!$convenio) {
                return self::resultado(false, 'Convenio no encontrado.');
            }

            $idConvenioReal = (int) $convenio['id_convenio'];
            $idCreditoReal = (int) $convenio['id_credito'];

            $amortizacion = $db->queryAll(
                "SELECT *
                 FROM convenio_cliente_amortizacion
                 WHERE id_convenio_cliente = :id
                 ORDER BY numero_semana ASC",
                ['id' => $idConvenioReal]
            ) ?: [];

            $seguimiento = [];
            if (self::_tablaExiste($db, 'cierre_credito_seguimiento')) {
                $seguimiento = $db->queryAll(
                    "SELECT *
                     FROM cierre_credito_seguimiento
                     WHERE id_credito = :id_credito
                     ORDER BY COALESCE(fecha_actualizacion, fecha_alta) ASC, id ASC",
                    ['id_credito' => $idCreditoReal]
                ) ?: [];
            }

            $reactivaciones = [];
            if (self::_tablaExiste($db, 'convenio_reactivacion_peticion')) {
                $whereReactivacion = ['id_credito = :id_credito'];
                $paramsReactivacion = ['id_credito' => $idCreditoReal];
                if (self::_tablaTieneColumna($db, 'convenio_reactivacion_peticion', 'id_convenio_origen')) {
                    $whereReactivacion[] = 'id_convenio_origen = :id_convenio';
                    $paramsReactivacion['id_convenio'] = $idConvenioReal;
                }

                $reactivaciones = $db->queryAll(
                    "SELECT *
                     FROM convenio_reactivacion_peticion
                     WHERE (" . implode(' OR ', $whereReactivacion) . ")
                     ORDER BY COALESCE(fecha_resolucion, fecha_solicitud) ASC, id ASC",
                    $paramsReactivacion
                ) ?: [];
            }

            $bitacora = [];
            $addEvento = static function (?string $fecha, string $tipo, string $titulo, string $detalle = '', string $usuario = '', array $meta = []) use (&$bitacora): void {
                $fecha = $fecha !== null ? trim($fecha) : '';
                if ($fecha === '' || $fecha === '0000-00-00' || $fecha === '0000-00-00 00:00:00') {
                    return;
                }
                $sort = strlen($fecha) === 10 ? $fecha . ' 00:00:00' : $fecha;
                $bitacora[] = [
                    'fecha' => $fecha,
                    'fecha_sort' => $sort,
                    'tipo' => $tipo,
                    'titulo' => $titulo,
                    'detalle' => $detalle,
                    'usuario' => $usuario,
                    'meta' => $meta,
                ];
            };

            $addEvento(
                $convenio['fecha_alta'] ?? null,
                'convenio',
                'Convenio creado',
                'Oferta: ' . (string) ($convenio['oferta_seleccionada'] ?? 'Sin oferta'),
                (string) ($convenio['usuario_alta'] ?? '')
            );

            if (!empty($convenio['fecha_acuerdo'])) {
                $addEvento(
                    $convenio['fecha_acuerdo'],
                    'convenio',
                    'Fecha de convenio',
                    'Monto del convenio: $' . number_format((float) ($convenio['total_a_pagar'] ?? 0), 2),
                    (string) ($convenio['usuario_alta'] ?? '')
                );
            }

            if (!empty($convenio['fecha_reactivacion']) || (isset($convenio['es_reactivado']) && (int) $convenio['es_reactivado'] === 1)) {
                $addEvento(
                    $convenio['fecha_reactivacion'] ?? ($convenio['fecha_alta'] ?? null),
                    'reactivacion',
                    'Oferta reactivada',
                    (string) ($convenio['motivo_reactivacion'] ?? 'Reactivacion de oferta'),
                    (string) ($convenio['usuario_reactiva'] ?? $convenio['usuario_alta'] ?? '')
                );
            }

            if (!empty($convenio['solicitud_cancelamiento_fecha'])) {
                $addEvento(
                    $convenio['solicitud_cancelamiento_fecha'],
                    'cancelamiento',
                    'Solicitud de cancelamiento',
                    (string) ($convenio['motivo_cancelamiento'] ?? ''),
                    (string) ($convenio['usuario_modifica'] ?? '')
                );
            }

            if (!empty($convenio['fecha_cancelacion'])) {
                $addEvento(
                    $convenio['fecha_cancelacion'],
                    'cancelamiento',
                    'Convenio cancelado',
                    (string) ($convenio['motivo_cancelamiento'] ?? ''),
                    (string) ($convenio['usuario_cancela'] ?? '')
                );
            }

            if (strtolower((string) ($convenio['estatus'] ?? '')) === 'completado') {
                $addEvento(
                    $convenio['fecha_modifica'] ?? $convenio['fecha_ultimo_pago'] ?? null,
                    'convenio',
                    'Convenio liquidado',
                    'El convenio fue liquidado.',
                    (string) ($convenio['usuario_modifica'] ?? '')
                );
            }

            foreach ($amortizacion as $am) {
                $montoPrimario = 0.0;
                $montoRaw = trim((string) ($am['monto_pagado'] ?? ''));
                if ($montoRaw !== '' && is_numeric($montoRaw)) {
                    $montoPrimario = (float) $montoRaw;
                }
                $monto = $montoPrimario + (float) ($am['monto_secundario'] ?? 0);
                $estatusPago = strtolower((string) ($am['estatus_pago'] ?? ''));
                if ($monto > 0 || in_array($estatusPago, ['pagado', 'parcial', 'pendiente_conciliar'], true)) {
                    $addEvento(
                        $am['fecha_pago_real'] ?? $am['fecha_pago'] ?? null,
                        'pago',
                        'Pago semana ' . (string) ($am['numero_semana'] ?? ''),
                        ucfirst($estatusPago ?: 'sin estatus') . ' - $' . number_format($monto > 0 ? $monto : (float) ($am['pago_semanal'] ?? 0), 2),
                        '',
                        [
                            'id_amortizacion' => $am['id'] ?? null,
                            'comprobante_path' => $am['comprobante_path'] ?? '',
                        ]
                    );
                }
            }

            foreach ($seguimiento as $seg) {
                $addEvento(
                    $seg['fecha_alta'] ?? null,
                    'cierre_credito',
                    'Alta en cierre de credito',
                    'Estatus: ' . (string) ($seg['estatus'] ?? ''),
                    (string) ($seg['usuario_alta'] ?? '')
                );
                if (!empty($seg['fecha_actualizacion']) && ($seg['fecha_actualizacion'] !== ($seg['fecha_alta'] ?? null))) {
                    $detalle = 'Estatus: ' . (string) ($seg['estatus'] ?? '');
                    if (!empty($seg['comentario_descarte'])) {
                        $detalle .= ' - ' . (string) $seg['comentario_descarte'];
                    }
                    $addEvento(
                        $seg['fecha_actualizacion'],
                        'cierre_credito',
                        'Actualizacion cierre de credito',
                        $detalle,
                        (string) ($seg['usuario_actualizacion'] ?? '')
                    );
                }
            }

            foreach ($reactivaciones as $rx) {
                $addEvento(
                    $rx['fecha_solicitud'] ?? null,
                    'reactivacion',
                    'Solicitud de reactivacion',
                    (string) ($rx['motivo_solicitud'] ?? $rx['comentario_solicitud'] ?? ''),
                    (string) ($rx['usuario_solicita'] ?? '')
                );
                if (!empty($rx['fecha_resolucion'])) {
                    $detalle = (string) ($rx['estatus'] ?? '');
                    $comentario = (string) ($rx['comentario_resolucion'] ?? '');
                    if ($comentario !== '') {
                        $detalle .= ' - ' . $comentario;
                    }
                    $addEvento(
                        $rx['fecha_resolucion'],
                        'reactivacion',
                        'Resolucion de reactivacion',
                        $detalle,
                        (string) ($rx['usuario_resuelve'] ?? '')
                    );
                }
            }

            usort($bitacora, static function (array $a, array $b): int {
                return strcmp((string) ($a['fecha_sort'] ?? ''), (string) ($b['fecha_sort'] ?? ''));
            });

            foreach ($bitacora as &$evento) {
                unset($evento['fecha_sort']);
            }
            unset($evento);

            return self::resultado(true, 'Reporte individual de convenio.', [
                'convenio' => $convenio,
                'amortizacion' => $amortizacion,
                'seguimiento_cierre' => $seguimiento,
                'reactivaciones' => $reactivaciones,
                'bitacora' => $bitacora,
                'actualizado_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener el reporte individual de convenio.', [], $e->getMessage());
        }
    }
}
