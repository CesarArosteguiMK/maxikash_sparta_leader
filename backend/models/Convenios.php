<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseSegundometro;

class Convenios extends Model
{
    // Buckets elegibles para cualquier oferta (mora > 22 días)
    private static $BUCKETS_ELEGIBLES = ['e) 22 a 30 dias', 'f) 31 a 60 dias', 'g) 61 a 90 dias', 'h) 91 a 120 dias', 'i) 121+ dias'];

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
                $rows = $db->queryAll(
                    "SELECT Id_credito, Nombre_cliente, Bucket_Morosidad_Real, Dias_mora
                     FROM tbl_segundometro_semana
                     WHERE Id_credito = :id
                     LIMIT 10",
                    ['id' => (int) $termino]
                );
            } else {
                $rows = $db->queryAll(
                    "SELECT Id_credito, Nombre_cliente, Bucket_Morosidad_Real, Dias_mora
                     FROM tbl_segundometro_semana
                     WHERE Nombre_cliente LIKE :nombre
                     LIMIT 10",
                    ['nombre' => '%' . $termino . '%']
                );
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
            $row = $db->queryOne(
                "SELECT
                    Id_credito,
                    Nombre_cliente,
                    Bucket_Morosidad_Real,
                    Dias_mora,
                    Avance_Pago_Plazo,
                    Numero_amortizaciones,
                    Num_cuotas_pagadas,
                    Saldo_total_capital,
                    Saldo_para_liquidar_hoy   AS Adeudo_total,
                    Monto_otorgado,
                    Rango_Monto,
                    Sucursal,
                    Gestor_Asignado
                 FROM tbl_segundometro_semana
                 WHERE Id_credito = :id
                 LIMIT 1",
                ['id' => (int) $id_credito]
            );

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
            $dbSeg = new DatabaseSegundometro(); // __SPARTA_SECRET_REDACTED__ (créditos)
            $db    = new Database();             // __SPARTA_SECRET_REDACTED__   (productos/convenios)

            // 1. Datos del crédito
            $credito = $dbSeg->queryOne(
                "SELECT
                    Id_credito,
                    Nombre_cliente,
                    Bucket_Morosidad_Real,
                    Dias_mora,
                    Avance_Pago_Plazo,
                    Saldo_total_capital,
                    Saldo_para_liquidar_hoy AS Adeudo_total,
                    Rango_Monto
                 FROM tbl_segundometro_semana
                 WHERE Id_credito = :id
                 LIMIT 1",
                ['id' => (int) $id_credito]
            );

            if (!$credito) {
                return self::resultado(false, 'Crédito no encontrado.');
            }

            $bucket          = $credito['Bucket_Morosidad_Real'] ?? '';
            // Avance_Pago_Plazo está almacenado como rango string (ej. "61-80%");
            // se extrae el límite inferior para comparar numéricamente.
            $avancePagoStr   = $credito['Avance_Pago_Plazo'] ?? '';
            $avancePago      = 0;
            if (preg_match('/^(\d+)/', $avancePagoStr, $m)) {
                $avancePago = (int) $m[1];
            }
            $adeudoTotal     = (float) ($credito['Adeudo_total'] ?? 0);
            $saldoCapital    = (float) ($credito['Saldo_total_capital'] ?? 0);

            // El crédito debe estar en bucket e→i (mora >22 días)
            if (!in_array($bucket, self::$BUCKETS_ELEGIBLES)) {
                return self::resultado(true, 'El crédito no cumple los criterios de bucket.', [
                    'credito'       => $credito,
                    'ofertas'       => [],
                    'elegible'      => false,
                    'razon'         => 'bucket_fuera_de_rango'
                ]);
            }

            // 2. Obtener todos los productos activos (__SPARTA_SECRET_REDACTED__)
            $productos = $db->queryAll(
                "SELECT
                    pc.id,
                    pc.nombre,
                    pcd.id                      AS id_detalle,
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

            // 3. Filtrar productos elegibles para este crédito
            $ofertas = [];
            foreach ($productos as $prod) {
                $bucketsProducto = array_map('trim', explode(',', $prod['buckets_aplicables']));

                // Validar bucket del crédito contra los del producto
                if (!in_array($bucket, $bucketsProducto)) {
                    continue;
                }

                // Validar avance de pago mínimo si aplica
                if ($prod['avance_pago_minimo'] !== null && $avancePago < (float) $prod['avance_pago_minimo']) {
                    continue;
                }

                // Calcular montos según base de cálculo
                $baseCalculo     = $prod['base_calculo'];
                $montoBase       = ($baseCalculo === 'saldo_total_capital') ? $saldoCapital : $adeudoTotal;
                $pct             = (float) $prod['porcentaje_descuento'];
                $descuentoMonto  = round($montoBase * ($pct / 100), 2);
                $totalAPagar     = round($montoBase - $descuentoMonto, 2);

                // Pago inicial
                $pagoInicialMonto = null;
                if ($prod['pago_inicial'] === 'Si' && $prod['porcentaje_pago_inicial']) {
                    $pctInicial = (float) $prod['porcentaje_pago_inicial'];
                    if ($prod['pago_inicial_momento'] === 'antes') {
                        // 10% sobre montoBase antes del descuento
                        $pagoInicialMonto = round($montoBase * ($pctInicial / 100), 2);
                        $totalAPagar      = round($totalAPagar - $pagoInicialMonto, 2);
                    } else {
                        // 10% sobre total_a_pagar después del descuento
                        $pagoInicialMonto = round($totalAPagar * ($pctInicial / 100), 2);
                        $totalAPagar      = round($totalAPagar - $pagoInicialMonto, 2);
                    }
                }

                // Plazo máximo según monto (__SPARTA_SECRET_REDACTED__)
                $semanasMax = self::calcularPlazoMaximo($db, $prod['id'], $adeudoTotal, $prod['periodo_fin']);

                $ofertas[] = [
                    'id_producto'           => $prod['id'],
                    'id_detalle'            => $prod['id_detalle'],
                    'nombre'                => $prod['nombre'],
                    'porcentaje_descuento'  => $pct,
                    'base_calculo'          => $baseCalculo,
                    'monto_base'            => $montoBase,
                    'descuento_monto'       => $descuentoMonto,
                    'total_a_pagar'         => $totalAPagar,
                    'pago_inicial'          => $prod['pago_inicial'],
                    'pago_inicial_monto'    => $pagoInicialMonto,
                    'pago_inicial_momento'  => $prod['pago_inicial_momento'],
                    'periodo_inicio'        => (int) $prod['periodo_inicio'],
                    'periodo_fin_producto'  => (int) $prod['periodo_fin'],
                    'semanas_max'           => $semanasMax,
                    'buckets_aplicables'    => $bucketsProducto,
                ];
            }

            return self::resultado(true, 'Ofertas calculadas.', [
                'credito'   => $credito,
                'ofertas'   => $ofertas,
                'elegible'  => count($ofertas) > 0,
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
     *   usuario_alta
     */
    public static function guardarConvenio($datos)
    {
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

            // Fecha primer pago: 8 días después de la fecha de acuerdo
            $fechaAcuerdo    = $datos['fecha_acuerdo'];
            $fechaPrimerPago = date('Y-m-d', strtotime($fechaAcuerdo . ' +8 days'));
            $semanas         = (int) $datos['numero_semanas'];
            $fechaUltimoPago = date('Y-m-d', strtotime($fechaPrimerPago . ' +' . (($semanas - 1) * 7) . ' days'));

            // Insertar convenio
            $ok = $db->CRUD(
                "INSERT INTO convenio_cliente (
                    id_credito, id_producto_convenio, id_producto_convenio_detalle,
                    nombre_cliente, bucket_morosidad_real, dias_mora, avance_pago_plazo,
                    adeudo_total_original, porcentaje_descuento, descuento_monto,
                    total_a_pagar, pago_inicial_monto, numero_semanas, pago_semanal,
                    fecha_acuerdo, fecha_primer_pago, fecha_ultimo_pago, estatus, usuario_alta
                ) VALUES (
                    :id_credito, :id_producto, :id_detalle,
                    :nombre_cliente, :bucket, :dias_mora, :avance_pago,
                    :adeudo_original, :pct_descuento, :descuento_monto,
                    :total_pagar, :pago_inicial, :num_semanas, :pago_semanal,
                    :fecha_acuerdo, :fecha_primer_pago, :fecha_ultimo_pago, 'activo', :usuario
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
                    'pago_inicial'      => isset($datos['pago_inicial_monto']) ? (float) $datos['pago_inicial_monto'] : null,
                    'num_semanas'       => $semanas,
                    'pago_semanal'      => (float) $datos['pago_semanal'],
                    'fecha_acuerdo'     => $fechaAcuerdo,
                    'fecha_primer_pago' => $fechaPrimerPago,
                    'fecha_ultimo_pago' => $fechaUltimoPago,
                    'usuario'           => $datos['usuario_alta'],
                ]
            );

            if (!$ok) {
                return self::resultado(false, 'No se pudo guardar el convenio.');
            }

            $idConvenio   = (int) $db->queryOne("SELECT LAST_INSERT_ID() AS id")['id'];
            $totalAPagar  = (float) $datos['total_a_pagar'];
            $pagoSemanal  = (float) $datos['pago_semanal'];
            $saldoActual  = $totalAPagar;

            // Generar tabla de amortización
            for ($s = 1; $s <= $semanas; $s++) {
                $fechaPago   = date('Y-m-d', strtotime($fechaPrimerPago . ' +' . (($s - 1) * 7) . ' days'));
                $capital     = ($s < $semanas) ? $pagoSemanal : $saldoActual; // última semana liquida saldo
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

            return self::resultado(true, 'Convenio guardado correctamente.', ['id_convenio' => $idConvenio]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al guardar convenio.', null, $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // CONSULTAS DE CONVENIOS EXISTENTES
    // ─────────────────────────────────────────────

    /**
     * Devuelve el convenio activo de un crédito (si existe) con su amortización.
     */
    public static function getConvenioActivo($id_credito)
    {
        try {
            $db = new Database(); // __SPARTA_SECRET_REDACTED__: tablas de convenios

            $convenio = $db->queryOne(
                "SELECT cc.*, pc.nombre AS nombre_producto
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.id_credito = :id AND cc.estatus = 'activo'
                 ORDER BY cc.fecha_alta DESC
                 LIMIT 1",
                ['id' => (int) $id_credito]
            );

            if (!$convenio) {
                return self::resultado(true, 'Sin convenio activo.', null);
            }

            $amortizacion = $db->queryAll(
                "SELECT * FROM convenio_cliente_amortizacion
                 WHERE id_convenio_cliente = :id
                 ORDER BY numero_semana",
                ['id' => (int) $convenio['id']]
            );

            $convenio['amortizacion'] = $amortizacion ?: [];
            return self::resultado(true, 'Convenio encontrado.', $convenio);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al consultar convenio.', null, $e->getMessage());
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
}
