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
    // Modificar getConvenioActivo para incluir pdf_adjunto

public static function getConvenioActivo($id_credito)
{
    try {
        $db = new Database();

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

        if (!empty($convenio['pdf_adjunto'])) {
            $convenio['pdf_url'] = $convenio['pdf_adjunto'];
        }

        // ── Enriquecer con pagos reales de S2Movil ──────────────
        $pagosS2 = self::_getPagosS2Movil((int) $id_credito);
        $convenio['pagos_s2movil'] = $pagosS2;

        return self::resultado(true, 'Convenio encontrado.', $convenio);
    } catch (\Exception $e) {
        return self::resultado(false, 'Error al consultar convenio.', null, $e->getMessage());
    }
}

/**
 * Trae datosPagos de S2Movil indexados por numeroCuotaSemanal.
 * Cada entrada puede tener múltiples pagos (sobrantes + pagos normales).
 */
private static function _getPagosS2Movil($id_credito)
{
    try {
        $url     = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
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
                'Token: __SPARTA_TOKEN_REDACTED__',
            ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data      = json_decode($response, true);
        $datosPagos = $data['estadoCuenta']['datosPagos'] ?? [];

        if (empty($datosPagos)) return [];

        // Agrupar por numeroCuotaSemanal — puede haber varios pagos por cuota
        $indexado = [];
        foreach ($datosPagos as $pago) {
            $cuota = (int) $pago['numeroCuotaSemanal'];
            if ($cuota < 1) continue;

            if (!isset($indexado[$cuota])) {
                $indexado[$cuota] = [];
            }

            $montoPago = (float) ($pago['montoPago'] ?? 0);
            $capital   = (float) ($pago['capital']   ?? 0);
            $sobrante  = round($montoPago - $capital, 2);

            $indexado[$cuota][] = [
                'idPago'        => $pago['idPago']        ?? null,
                'fechaValor'    => $pago['fechaValor']    ?? null,
                'fechaDeposito' => $pago['fechaDeposito'] ?? null,
                'montoPago'     => $montoPago,
                'capital'       => $capital,
                'sobrante'      => $sobrante > 0 ? $sobrante : 0,
            ];
        }

        return $indexado;

    } catch (\Exception $e) {
        return [];
    }
}

    // ─────────────────────────────────────────────
// CANCELAR CONVENIO
// ─────────────────────────────────────────────

public static function cancelarConvenio($id_convenio, $usuario)
{
    try {
        $db = new Database();

        // 1. Verificar que exista y esté activo
        $convenio = $db->queryOne(
            "SELECT id, id_credito
             FROM convenio_cliente
             WHERE id = :id AND estatus = 'activo'
             LIMIT 1",
            ['id' => (int) $id_convenio]
        );

        if (!$convenio) {
            return self::resultado(false, 'El convenio no existe o no está activo.');
        }

        // 2. Primera semana pendiente = semana de cancelación
        $primerPendiente = $db->queryOne(
            "SELECT numero_semana
             FROM convenio_cliente_amortizacion
             WHERE id_convenio_cliente = :id
               AND estatus_pago = 'pendiente'
             ORDER BY numero_semana ASC
             LIMIT 1",
            ['id' => (int) $id_convenio]
        );

        if (!$primerPendiente) {
            return self::resultado(false, 'Este convenio no tiene semanas pendientes.');
        }

        $semanaCancelacion = (int) $primerPendiente['numero_semana'];
        $fechaCancelacion  = date('Y-m-d');

        // 3. Actualizar convenio_cliente
        $ok = $db->CRUD(
            "UPDATE convenio_cliente SET
                estatus                  = 'cancelado',
                fecha_cancelacion        = :fecha_cancelacion,
                numero_semana_cancelacion = :semana,
                usuario_cancela          = :usuario,
                usuario_modifica         = :usuario_modifica,
                fecha_modifica           = NOW()
             WHERE id = :id",
            [
                'fecha_cancelacion' => $fechaCancelacion,
                'semana'            => $semanaCancelacion,
                'usuario'           => $usuario,
                'usuario_modifica'  => $usuario,
                'id'                => (int) $id_convenio,
            ]
        );

        if (!$ok) {
            return self::resultado(false, 'No se pudo actualizar el convenio.');
        }

        // 4. Marcar semanas pendientes desde la cancelación como 'cancelado'
        $db->CRUD(
            "UPDATE convenio_cliente_amortizacion SET
                estatus_pago = 'cancelado'
             WHERE id_convenio_cliente = :id
               AND numero_semana >= :semana
               AND estatus_pago = 'pendiente'",
            [
                'id'     => (int) $id_convenio,
                'semana' => $semanaCancelacion,
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
                pc.nombre           AS nombre_producto,
                cc.total_a_pagar,
                cc.numero_semanas,
                cc.estatus,
                cc.fecha_acuerdo,
                cc.fecha_cancelacion,
                cc.numero_semana_cancelacion,
                cc.usuario_alta,
                cc.usuario_cancela,
                cc.pdf_adjunto
             FROM convenio_cliente cc
             INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
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
        $url     = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
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
                'Token: __SPARTA_TOKEN_REDACTED__',
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
            $db->CRUD(
                "UPDATE convenio_cliente
                 SET estatus = 'completado', fecha_modifica = NOW()
                 WHERE id = :id",
                ['id' => $id_convenio]
            );
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
        $url        = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
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
                'Token: __SPARTA_TOKEN_REDACTED__',
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
        $url     = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
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
                'Token: __SPARTA_TOKEN_REDACTED__',
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
    $stmt = self::$db->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN estatus_pago = 'pagado' THEN 1 ELSE 0 END) as pagadas
        FROM convenio_cliente_amortizacion
        WHERE id_convenio_cliente = ?
    ");
    $stmt->execute([$id_convenio]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($res && $res['total'] > 0 && $res['total'] == $res['pagadas']) {
        self::$db->prepare("
            UPDATE convenio_cliente
            SET estatus = 'completado', fecha_modifica = NOW()
            WHERE id = ?
        ")->execute([$id_convenio]);
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

        // 2. Calcular montos y semanas
        $fechaInicio  = $datos['fecha_inicio'];
        $adeudoBase   = (float) $datos['adeudo_base'];
        $pctDescuento = (float) $datos['porcentaje_descuento'];
        $pagoSemanal  = (float) $datos['pago_semanal'];
        $idProducto   = (int)   $datos['id_producto_convenio'];
        $idDetalle    = (int)   $datos['id_producto_convenio_detalle'];
        $pdfAdjunto   = isset($datos['pdf_adjunto']) ? $datos['pdf_adjunto'] : null;

        $descuentoMonto = round($adeudoBase * ($pctDescuento / 100), 2);
        $totalAPagar    = round($adeudoBase - $descuentoMonto, 2);
        $semanasEnteras = (int) floor($totalAPagar / $pagoSemanal);
        $residuo        = round($totalAPagar - ($semanasEnteras * $pagoSemanal), 2);
        $semanas        = $residuo > 0 ? $semanasEnteras + 1 : $semanasEnteras;
        $fechaUltimoPago = date('Y-m-d', strtotime($fechaInicio . ' +' . (($semanas - 1) * 7) . ' days'));

        // 3. Insertar convenio (ahora con campo pdf_adjunto)
        $ok = $db->CRUD(
            "INSERT INTO convenio_cliente (
                id_credito, id_producto_convenio, id_producto_convenio_detalle,
                nombre_cliente, bucket_morosidad_real, dias_mora, avance_pago_plazo,
                adeudo_total_original, porcentaje_descuento, descuento_monto,
                total_a_pagar, pago_inicial_monto, numero_semanas, pago_semanal,
                fecha_acuerdo, fecha_primer_pago, fecha_ultimo_pago, estatus,
                usuario_alta, pdf_adjunto
            ) VALUES (
                :id_credito, :id_producto, :id_detalle,
                :nombre_cliente, :bucket, :dias_mora, :avance_pago,
                :adeudo_original, :pct_descuento, :descuento_monto,
                :total_pagar, NULL, :num_semanas, :pago_semanal,
                :fecha_acuerdo, :fecha_primer_pago, :fecha_ultimo_pago, 'activo',
                :usuario, :pdf_adjunto
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
                'num_semanas'      => $semanas,
                'pago_semanal'     => $pagoSemanal,
                'fecha_acuerdo'    => $fechaInicio,
                'fecha_primer_pago'=> $fechaInicio,
                'fecha_ultimo_pago'=> $fechaUltimoPago,
                'usuario'          => $datos['usuario_alta'],
                'pdf_adjunto'      => $pdfAdjunto,
            ]
        );

        if (!$ok) {
            return self::resultado(false, 'No se pudo guardar el convenio.');
        }

        $idConvenio  = (int) $db->queryOne("SELECT LAST_INSERT_ID() AS id")['id'];
        $saldoActual = $totalAPagar;

        // 4. Generar amortización
        for ($s = 1; $s <= $semanas; $s++) {
            $fechaPago   = date('Y-m-d', strtotime($fechaInicio . ' +' . (($s - 1) * 7) . ' days'));
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
        // Traer estado de cuenta completo
        $url     = 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
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
                'Token: __SPARTA_TOKEN_REDACTED__',
            ],
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data      = json_decode($response, true);
        $datosPagos = $data['estadoCuenta']['datosPagos'] ?? [];

        if (empty($datosPagos)) return 0;

        $marcadas = 0;

        for ($s = 1; $s <= $semanas; $s++) {
            // Rango de la semana del convenio
            $fechaInicioSemana = new \DateTime(
                date('Y-m-d', strtotime($fechaInicio . ' +' . (($s - 1) * 7) . ' days'))
            );
            $fechaFinSemana = (clone $fechaInicioSemana)->modify('+6 days');

            // Buscar en datosPagos si hay un pago con fechaValor dentro de ese rango
            $pagoEncontrado = null;
            foreach ($datosPagos as $pago) {
                if (empty($pago['fechaValor'])) continue;

                $fechaValor = new \DateTime($pago['fechaValor']);
                if ($fechaValor >= $fechaInicioSemana && $fechaValor <= $fechaFinSemana) {
                    $pagoEncontrado = $pago;
                    break;
                }
            }

            if (!$pagoEncontrado) continue;

            // Marcar semana como pagada
            $db->CRUD(
                "UPDATE convenio_cliente_amortizacion
                 SET estatus_pago    = 'pagado',
                     fecha_pago_real = :fecha,
                     monto_pagado    = :monto
                 WHERE id_convenio_cliente = :id AND numero_semana = :num",
                [
                    'fecha' => $pagoEncontrado['fechaValor'],
                    'monto' => $pagoEncontrado['montoPago'] ?? null,
                    'id'    => $idConvenio,
                    'num'   => $s,
                ]
            );
            $marcadas++;
        }

        // Si todas las semanas quedaron pagadas → completar convenio
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


}
