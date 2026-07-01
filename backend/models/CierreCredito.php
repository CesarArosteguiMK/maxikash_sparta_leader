<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseSegundometro;

class CierreCredito extends Model
{
    // ─────────────────────────────────────────────
    // HELPER: filtro SQL por célula
    // ─────────────────────────────────────────────

    /**
     * Genera un fragmento SQL + parámetros para filtrar por id_celula.
     * @param  ?array  $celulas  [1], [2], [1,2] o null (sin filtro)
     * @param  string  $alias    Alias de la tabla que tiene id_celula
     * @param  array   &$params  Se agregan los parámetros bind
     * @return string            '' si no hay filtro, o ' AND alias.id_celula IN (:cel_0, :cel_1)'
     */
    private static function _celulaWhere(?array $celulas, string $alias, array &$params): string
    {
        if ($celulas === null || count($celulas) === 0) return '';
        $parts = [];
        foreach ($celulas as $i => $c) {
            $key = 'cel_' . $i;
            $parts[] = ':' . $key;
            $params[$key] = (int) $c;
        }
        return " AND {$alias}.id_celula IN (" . implode(',', $parts) . ')';
    }

    // ─────────────────────────────────────────────
    // LISTADOS POR ESTATUS
    // ─────────────────────────────────────────────

    /**
     * Devuelve todos los registros con estatus 'en_proceso'.
     */
    public static function getEnProceso(?array $celulas = null): array
    {
        try {
            $db = new Database();

            // 1. Registros pendientes de validación
            $params = [];
            $celulaWhere = self::_celulaWhere($celulas, 'ccs', $params);
            $rows = $db->queryAll(
                "SELECT ccs.id, ccs.id_credito, ccs.nombre_cliente, ccs.estatus,
                        ccs.fecha_alta, ccs.usuario_alta,
                        ccs.fecha_actualizacion, ccs.usuario_actualizacion,
                    ccs.fecha_envio_cartera, ccs.id_celula,
                    ccs.vobo_validado_direccion, ccs.vobo_fecha_validacion,
                    ccs.vobo_comentario, ccs.vobo_archivo
                 FROM cierre_credito_seguimiento ccs
                 WHERE ccs.estatus = 'en_proceso'{$celulaWhere}
                 ORDER BY ccs.fecha_alta DESC",
                $params ?: null
            );

            if (!$rows) {
                return self::resultado(true, 'Registros en proceso.', []);
            }

            // 2. Cross-reference con convenio_cliente (misma DB)
            $placeholders = [];
            $params       = [];
            foreach ($rows as $idx => $row) {
                $key            = 'id_' . $idx;
                $placeholders[] = ':' . $key;
                $params[$key]   = (int) $row['id_credito'];
            }
            $in = implode(',', $placeholders);

            $convenios = $db->queryAll(
                "SELECT cc.id AS id_convenio, cc.id_credito,
                        pc.nombre AS nombre_producto,
                        cc.pdf_adjunto,
                        cc.total_a_pagar, cc.porcentaje_descuento,
                        cc.adeudo_total_original, cc.numero_semanas,
                        cc.fecha_acuerdo,
                        COALESCE(cc.base_calculo, pcd.base_calculo) AS base_calculo
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 LEFT JOIN producto_convenio_detalle pcd ON pcd.id = cc.id_producto_convenio_detalle
                 WHERE cc.id_credito IN ($in) AND cc.estatus = 'completado'
                 ORDER BY cc.fecha_alta DESC",
                $params
            );

            // Mapa id_credito → primer convenio completado
            $convenioMap = [];
            if ($convenios) {
                foreach ($convenios as $c) {
                    if (!isset($convenioMap[$c['id_credito']])) {
                        $convenioMap[$c['id_credito']] = $c;
                    }
                }
            }

            // 3. Contar comprobantes por id_convenio
            $comprobantesMap = [];
            if ($convenios) {
                $convIds = array_column($convenios, 'id_convenio');
                $phC     = [];
                $parC    = [];
                foreach ($convIds as $idx => $cid) {
                    $key      = 'cid_' . $idx;
                    $phC[]    = ':' . $key;
                    $parC[$key] = (int) $cid;
                }
                $inC = implode(',', $phC);

                $compRows = $db->queryAll(
                    "SELECT id_convenio_cliente,
                            SUM(CASE WHEN comprobante_path IS NOT NULL
                                          AND comprobante_path != ''
                                     THEN 1 ELSE 0 END) AS con_comprobante,
                            COUNT(*) AS total_semanas
                     FROM convenio_cliente_amortizacion
                     WHERE id_convenio_cliente IN ($inC)
                     GROUP BY id_convenio_cliente",
                    $parC
                );

                if ($compRows) {
                    foreach ($compRows as $cr) {
                        $comprobantesMap[(int)$cr['id_convenio_cliente']] = [
                            'con'   => (int) $cr['con_comprobante'],
                            'total' => (int) $cr['total_semanas'],
                        ];
                    }
                }
            }

            // 4. Mezclar todo en los registros
            foreach ($rows as &$row) {
                $conv = $convenioMap[$row['id_credito']] ?? null;
                $row['id_convenio']           = $conv ? (int) $conv['id_convenio'] : null;
                $row['nombre_producto']       = $conv['nombre_producto']       ?? '—';
                $row['pdf_adjunto']           = $conv['pdf_adjunto']           ?? null;
                $row['total_a_pagar']         = $conv['total_a_pagar']         ?? 0;
                $row['porcentaje_descuento']  = $conv['porcentaje_descuento']  ?? 0;
                $row['adeudo_total_original'] = $conv['adeudo_total_original'] ?? 0;
                $row['numero_semanas']        = $conv['numero_semanas']        ?? 0;
                $row['fecha_acuerdo']         = $conv['fecha_acuerdo']         ?? null;
                $row['base_calculo']          = $conv['base_calculo']          ?? null;

                $idConv = $row['id_convenio'];
                $comp   = $idConv ? ($comprobantesMap[$idConv] ?? ['con' => 0, 'total' => 0])
                                  : ['con' => 0, 'total' => 0];
                $row['comprobantes_subidos'] = $comp['con'];
                $row['comprobantes_total']   = $comp['total'];
            }
            unset($row);

            self::_enrichWithS2AndSemana($rows);
            return self::resultado(true, 'Registros en proceso.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener registros en proceso.', [], $e->getMessage());
        }
    }

    /**
     * Registros enviados a Vo.Bo (Dirección de cobranza).
     */
    public static function getVoBo(?array $celulas = null): array
    {
        try {
            $db = new Database();

            $params = [];
            $celulaWhere = self::_celulaWhere($celulas, 'ccs', $params);
            $rows = $db->queryAll(
                "SELECT ccs.id, ccs.id_credito, ccs.nombre_cliente, ccs.estatus,
                        ccs.fecha_alta, ccs.usuario_alta,
                        ccs.fecha_actualizacion, ccs.usuario_actualizacion,
                        ccs.id_celula,
                        ccs.vobo_comentario, ccs.vobo_archivo,
                        ccs.vobo_validado_direccion, ccs.vobo_fecha_validacion
                 FROM cierre_credito_seguimiento ccs
                 WHERE ccs.estatus = 'envio_cobranza'{$celulaWhere}
                 ORDER BY ccs.fecha_actualizacion DESC, ccs.fecha_alta DESC",
                $params ?: null
            );

            if (!$rows) {
                return self::resultado(true, 'Registros en Vo.Bo.', []);
            }

            $placeholders = [];
            $paramsConv   = [];
            foreach ($rows as $idx => $row) {
                $key = 'id_' . $idx;
                $placeholders[] = ':' . $key;
                $paramsConv[$key] = (int) $row['id_credito'];
            }
            $in = implode(',', $placeholders);

            $convenios = $db->queryAll(
                "SELECT cc.id AS id_convenio, cc.id_credito,
                        pc.nombre AS nombre_producto,
                        cc.pdf_adjunto,
                        cc.total_a_pagar, cc.porcentaje_descuento,
                        cc.adeudo_total_original, cc.numero_semanas,
                        cc.fecha_acuerdo,
                        COALESCE(cc.base_calculo, pcd.base_calculo) AS base_calculo
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 LEFT JOIN producto_convenio_detalle pcd ON pcd.id = cc.id_producto_convenio_detalle
                 WHERE cc.id_credito IN ($in) AND cc.estatus = 'completado'
                 ORDER BY cc.fecha_alta DESC",
                $paramsConv
            );

            $convenioMap = [];
            if ($convenios) {
                foreach ($convenios as $c) {
                    if (!isset($convenioMap[$c['id_credito']])) {
                        $convenioMap[$c['id_credito']] = $c;
                    }
                }
            }

            foreach ($rows as &$row) {
                $conv = $convenioMap[$row['id_credito']] ?? null;
                $row['id_convenio']           = $conv ? (int) $conv['id_convenio'] : null;
                $row['nombre_producto']       = $conv['nombre_producto']       ?? '—';
                $row['pdf_adjunto']           = $conv['pdf_adjunto']           ?? null;
                $row['total_a_pagar']         = $conv['total_a_pagar']         ?? 0;
                $row['porcentaje_descuento']  = $conv['porcentaje_descuento']  ?? 0;
                $row['adeudo_total_original'] = $conv['adeudo_total_original'] ?? 0;
                $row['numero_semanas']        = $conv['numero_semanas']        ?? 0;
                $row['fecha_acuerdo']         = $conv['fecha_acuerdo']         ?? null;
                $row['base_calculo']          = $conv['base_calculo']          ?? null;
            }
            unset($row);

            self::_enrichWithS2AndSemana($rows);
            return self::resultado(true, 'Registros en Vo.Bo.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener registros en Vo.Bo.', [], $e->getMessage());
        }
    }

    /**
     * Convenios con estatus 'saldado' — fuente principal de Cierre de Crédito.
     * Trae datos del convenio, producto, progreso de amortización y despacho asignado.
     */
    public static function getEnviadoFinalizado(?array $celulas = null): array
    {
        try {
            $db     = new Database();
            $params = [];
            $celulaWhere = self::_celulaWhere($celulas, 'cc', $params);
            $rows = $db->queryAll(
                "SELECT
                    cc.id,
                    cc.id_credito,
                    cc.nombre_cliente,
                    cc.id_producto_convenio,
                    pc.nombre                      AS nombre_producto,
                    cc.adeudo_total_original,
                    cc.porcentaje_descuento,
                    cc.descuento_monto,
                    cc.total_a_pagar,
                    cc.monto_adicional,
                    cc.pago_inicial_monto,
                    cc.numero_semanas,
                    cc.pago_semanal,
                    cc.fecha_acuerdo,
                    cc.fecha_primer_pago,
                    cc.fecha_ultimo_pago,
                    cc.estatus,
                    cc.usuario_alta,
                    cc.fecha_alta,
                    cc.fecha_modifica,
                    cc.pdf_adjunto,
                    cc.id_celula,
                    (SELECT COUNT(*)
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id
                       AND a.estatus_pago = 'pagado')  AS cuotas_pagadas,
                    (SELECT COUNT(*)
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id
                       AND a.comprobante_path IS NOT NULL
                       AND a.comprobante_path != '')   AS comprobantes_subidos,
                    (SELECT GROUP_CONCAT(a.comprobante_path ORDER BY a.numero_semana SEPARATOR '|')
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id
                       AND a.comprobante_path IS NOT NULL
                       AND a.comprobante_path != '')   AS comprobantes_paths,
                    cc.usuario_alta                        AS nombre_despacho,
                    (SELECT cat.motivo
                     FROM cierre_credito_seguimiento ccs
                     LEFT JOIN catalogo_cierre_credito_seguimiento cat ON cat.id = ccs.motivo_descarte
                     WHERE ccs.id_credito = cc.id_credito
                       AND ccs.estatus = 'descartado'
                     ORDER BY ccs.fecha_actualizacion DESC LIMIT 1) AS ultimo_motivo_descarte,
                    (SELECT ccs.comentario_descarte
                     FROM cierre_credito_seguimiento ccs
                     WHERE ccs.id_credito = cc.id_credito
                       AND ccs.estatus = 'descartado'
                     ORDER BY ccs.fecha_actualizacion DESC LIMIT 1) AS ultimo_comentario_descarte,
                    (SELECT ccs.usuario_actualizacion
                     FROM cierre_credito_seguimiento ccs
                     WHERE ccs.id_credito = cc.id_credito
                       AND ccs.estatus = 'descartado'
                     ORDER BY ccs.fecha_actualizacion DESC LIMIT 1) AS usuario_descarte,
                    (SELECT ccs.fecha_actualizacion
                     FROM cierre_credito_seguimiento ccs
                     WHERE ccs.id_credito = cc.id_credito
                       AND ccs.estatus = 'descartado'
                     ORDER BY ccs.fecha_actualizacion DESC LIMIT 1) AS fecha_descarte,
                    COALESCE(cc.base_calculo, pcd.base_calculo) AS base_calculo,
                    (SELECT ccs.estatus
                     FROM cierre_credito_seguimiento ccs
                     WHERE ccs.id_credito = cc.id_credito
                     ORDER BY ccs.fecha_actualizacion DESC, ccs.fecha_alta DESC
                     LIMIT 1) AS estatus_seguimiento
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 LEFT JOIN producto_convenio_detalle pcd ON pcd.id = cc.id_producto_convenio_detalle
                 WHERE cc.estatus = 'completado'
                   AND NOT EXISTS (
                       SELECT 1 FROM cierre_credito_seguimiento ccs
                       WHERE ccs.id_credito = cc.id_credito
                         AND ccs.estatus IN ('en_proceso', 'envio_cobranza', 'enviado_cartera', 'en_cola', 'listo_envio',
                                             'cerrado')
                   ){$celulaWhere}
                 ORDER BY cc.fecha_alta DESC",
                $params ?: null
            );
            $rows = $rows ?: [];
            self::_enrichWithS2AndSemana($rows);
            // Excluir créditos que ya están Saldados según la API de S2 (doble confirmación)
            self::_filterSaldadosEnS2($rows);
            return self::resultado(true, 'Convenios saldados.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener convenios saldados.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // CREAR
    // ─────────────────────────────────────────────

    /**
     * Inserta un nuevo registro en cierre_credito_seguimiento.
     *
     * @param array $datos  Llaves: id_credito, nombre_cliente, estatus, usuario_alta
     */
    public static function crear(array $datos): array
    {
        try {
            $db = new Database();

            // Si ya existe un registro (cualquier estatus) para este crédito, reutilizarlo
            $existing = $db->queryOne(
                "SELECT id, estatus FROM cierre_credito_seguimiento
                 WHERE id_credito = :id
                 ORDER BY fecha_alta DESC
                 LIMIT 1",
                ['id' => (int) $datos['id_credito']]
            );

            if ($existing) {
                if ($existing['estatus'] === 'en_proceso') {
                    return self::resultado(true, 'Este crédito ya está en proceso de validación.');
                }
                // Reutilizar el registro existente (UPDATE en lugar de INSERT)
                $db->CRUD(
                    "UPDATE cierre_credito_seguimiento
                     SET estatus                = 'en_proceso',
                         nombre_cliente         = :nombre_cliente,
                         vobo_validado_direccion= 0,
                         vobo_fecha_validacion  = NULL,
                         vobo_comentario        = NULL,
                         vobo_archivo           = NULL,
                         usuario_actualizacion  = :usuario,
                         fecha_actualizacion    = NOW()
                     WHERE id = :id",
                    [
                        'nombre_cliente' => $datos['nombre_cliente'],
                        'usuario'        => $datos['usuario_alta'],
                        'id'             => (int) $existing['id'],
                    ]
                );
                return self::resultado(true, 'Registro actualizado a En Proceso.');
            }

            // Obtener la célula del convenio para propagarla al seguimiento
            $convCelula = $db->queryOne(
                "SELECT id_celula FROM convenio_cliente
                 WHERE id_credito = :id AND id_celula IS NOT NULL
                 ORDER BY fecha_alta DESC LIMIT 1",
                ['id' => (int) $datos['id_credito']]
            );
            $idCelula = $convCelula ? (int) $convCelula['id_celula'] : null;

            $db->CRUD(
                "INSERT INTO cierre_credito_seguimiento
                    (id_credito, nombre_cliente, estatus, usuario_alta, usuario_actualizacion, id_celula)
                 VALUES
                    (:id_credito, :nombre_cliente, :estatus, :usuario_alta, :usuario_actualizacion, :id_celula)",
                [
                    'id_credito'            => (int) $datos['id_credito'],
                    'nombre_cliente'        => $datos['nombre_cliente'],
                    'estatus'               => $datos['estatus'] ?? 'en_proceso',
                    'usuario_alta'          => $datos['usuario_alta'],
                    'usuario_actualizacion' => $datos['usuario_alta'],
                    'id_celula'             => $idCelula,
                ]
            );
            return self::resultado(true, 'Registro creado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al crear el registro.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // CAMBIAR ESTATUS
    // ─────────────────────────────────────────────

    /**
     * Actualiza el estatus de un registro existente.
     *
     * @param int    $id      PK de cierre_credito_seguimiento
     * @param string $estatus 'en_proceso' | 'enviado_finalizado'
     * @param string $usuario Nombre del usuario que realiza el cambio
     */
    public static function cambiarEstatus(int $id, string $estatus, string $usuario): array
    {
        $permitidos = ['en_proceso', 'envio_cobranza', 'vo_bo_rechazado', 'enviado_finalizado', 'enviado_cartera'];
        if (!in_array($estatus, $permitidos, true)) {
            return self::resultado(false, 'Estatus no válido.');
        }

        try {
            $db = new Database();
            $db->CRUD(
                "UPDATE cierre_credito_seguimiento
                 SET estatus = :estatus, usuario_actualizacion = :usuario, fecha_actualizacion = NOW()
                 WHERE id = :id",
                ['estatus' => $estatus, 'usuario' => $usuario, 'id' => $id]
            );
            return self::resultado(true, 'Estatus actualizado correctamente.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar el estatus.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // VO.BO (Dirección de cobranza)
    // ─────────────────────────────────────────────

    public static function enviarAVoBo(int $id, string $usuario, string $comentario, ?string $archivoUrl = null): array
    {
        try {
            $db = new Database();
            $registro = $db->queryOne(
                "SELECT id FROM cierre_credito_seguimiento
                 WHERE id = :id AND estatus = 'en_proceso'
                 LIMIT 1",
                ['id' => $id]
            );

            if (!$registro) {
                return self::resultado(false, 'Registro no encontrado o no está en En Proceso.');
            }

            $db->CRUD(
                "UPDATE cierre_credito_seguimiento
                 SET estatus                 = 'envio_cobranza',
                     vobo_comentario         = :comentario,
                     vobo_archivo            = :archivo,
                     vobo_validado_direccion = 0,
                     vobo_fecha_validacion   = NULL,
                     usuario_actualizacion   = :usuario,
                     fecha_actualizacion     = NOW()
                 WHERE id = :id",
                [
                    'id' => $id,
                    'usuario' => $usuario,
                    'comentario' => mb_substr(trim($comentario), 0, 500),
                    'archivo' => $archivoUrl,
                ]
            );

            return self::resultado(true, 'Registro enviado a Vo.Bo de Dirección de Cobranza.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al enviar a Vo.Bo.', [], $e->getMessage());
        }
    }

    public static function aprobarVoBo(int $id, string $usuario, string $comentario = ''): array
    {
        try {
            $db = new Database();
            $registro = $db->queryOne(
                "SELECT id FROM cierre_credito_seguimiento
                 WHERE id = :id AND estatus = 'envio_cobranza'
                 LIMIT 1",
                ['id' => $id]
            );

            if (!$registro) {
                return self::resultado(false, 'Registro no encontrado o no está en Vo.Bo.');
            }

            $db->CRUD(
                "UPDATE cierre_credito_seguimiento
                 SET estatus                 = 'en_proceso',
                     vobo_validado_direccion = 1,
                     vobo_fecha_validacion   = NOW(),
                     vobo_comentario         = :comentario,
                     usuario_actualizacion   = :usuario,
                     fecha_actualizacion     = NOW()
                 WHERE id = :id",
                [
                    'id' => $id,
                    'usuario' => $usuario,
                    'comentario' => mb_substr(trim($comentario), 0, 500),
                ]
            );

            return self::resultado(true, 'Vo.Bo aprobado. Regresó a En Proceso como validado por Dirección de Cobranza.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al aprobar Vo.Bo.', [], $e->getMessage());
        }
    }

    public static function rechazarVoBo(int $id, string $usuario, string $comentario): array
    {
        try {
            $db = new Database();
            $registro = $db->queryOne(
                "SELECT id FROM cierre_credito_seguimiento
                 WHERE id = :id AND estatus = 'envio_cobranza'
                 LIMIT 1",
                ['id' => $id]
            );

            if (!$registro) {
                return self::resultado(false, 'Registro no encontrado o no está en Vo.Bo.');
            }

            $db->CRUD(
                "UPDATE cierre_credito_seguimiento
                 SET estatus                 = 'vo_bo_rechazado',
                     vobo_validado_direccion = 0,
                     comentario_descarte     = :comentario,
                     usuario_actualizacion   = :usuario,
                     fecha_actualizacion     = NOW()
                 WHERE id = :id",
                [
                    'id' => $id,
                    'usuario' => $usuario,
                    'comentario' => mb_substr(trim($comentario), 0, 500),
                ]
            );

            return self::resultado(true, 'Vo.Bo rechazado. El registro quedó en estatus vo_bo_rechazado para re-trabajo.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al rechazar Vo.Bo.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // ENVIAR A CARTERA (email + actualizar estatus)
    // ─────────────────────────────────────────────

    /**
     * Envía un correo al departamento de cartera con el resumen del cierre y,
     * si tiene éxito (o si mail_cartera no está configurado), marca el registro
     * como 'enviado_cartera'.
     *
     * Para activar el envío de email, descomentar mail_cartera en config.ini [mail].
     */
    public static function enviarACartera(int $id, string $usuario, string $estatusOrigen = 'en_proceso'): array
    {
        try {
            $db = new Database();

            // 1. Obtener el registro
            $registro = $db->queryOne(
                "SELECT id, id_credito, nombre_cliente, estatus, id_celula
                 FROM cierre_credito_seguimiento
                 WHERE id = :id AND estatus = :estatus
                 LIMIT 1",
                ['id' => $id, 'estatus' => $estatusOrigen]
            );

            if (!$registro) {
                return self::resultado(false, 'Registro no encontrado o no está en el estatus esperado.');
            }

            // 2. Obtener datos del convenio (para el cuerpo del correo)
            $convenio = $db->queryOne(
                "SELECT cc.id AS id_convenio, cc.id_credito,
                        pc.nombre               AS nombre_producto,
                        cc.pdf_adjunto,
                        cc.total_a_pagar,
                        cc.adeudo_total_original,
                        cc.porcentaje_descuento,
                        cc.numero_semanas,
                        cc.fecha_acuerdo,
                        cc.id_celula
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.id_credito = :id_credito AND cc.estatus = 'completado'
                 ORDER BY cc.fecha_alta DESC
                 LIMIT 1",
                ['id_credito' => (int) $registro['id_credito']]
            );

            // 3. Obtener datos S2 desde tbl_segundometro_semana (solo Segundometro, sin CROOP)
            $s2CuotasContratadas = null;
            $s2CuotasPagadas     = null;
            $s2TotalPagado       = null;
            $s2MontoOtorgado     = null;
            $semanaAcuerdo       = null;
            $anioSemanaAcuerdo   = null;

            if ($convenio) {
                $fechaAcuerdoRaw = $convenio['fecha_acuerdo'] ?? null;
                if ($fechaAcuerdoRaw && preg_match('/^\d{4}-\d{2}-\d{2}/', $fechaAcuerdoRaw)) {
                    try {
                        $d = new \DateTime(substr($fechaAcuerdoRaw, 0, 10));
                        $semanaAcuerdo     = (int) $d->format('W');
                        $anioSemanaAcuerdo = (int) $d->format('o');
                    } catch (\Throwable $_) {}
                }

                try {
                    $dbSm  = new DatabaseSegundometro();
                    $s2Row = $dbSm->queryOne(
                        "SELECT Numero_amortizaciones, Num_cuotas_pagadas, Abonos_total, Monto_otorgado
                         FROM tbl_segundometro_semana
                         WHERE Id_credito = :id
                         LIMIT 1",
                        ['id' => (int) $registro['id_credito']]
                    );
                    if ($s2Row) {
                        $s2CuotasContratadas = (int)   $s2Row['Numero_amortizaciones'];
                        $s2CuotasPagadas     = (int)   $s2Row['Num_cuotas_pagadas'];
                        $s2TotalPagado       = (float) $s2Row['Abonos_total'];
                        $s2MontoOtorgado     = (float) $s2Row['Monto_otorgado'];
                    }
                } catch (\Throwable $smEx) {
                    error_log('CierreCredito::enviarACartera S2 -> ' . $smEx->getMessage());
                }
            }

            // 4. Leer configuración de email — selecciona sección según célula del crédito
            $configPath = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../../config/config.ini');
            $ini        = is_file($configPath) ? @parse_ini_file($configPath, true) : [];
            $idCelula   = (int) ($registro['id_celula'] ?? ($convenio['id_celula'] ?? 0));
            if ($idCelula === 2 && !empty($ini['mail_cierre_callcenter'])) {
                $mailCfg = $ini['mail_cierre_callcenter'];
            } elseif (!empty($ini['mail_cierre'])) {
                $mailCfg = $ini['mail_cierre'];
            } else {
                $mailCfg = $ini['mail'] ?? [];
            }

            $mailCartera = trim((string) ($mailCfg['mail_cartera'] ?? ''));
            $smtpHost    = trim((string) ($mailCfg['smtp_host']    ?? ''));
            $smtpUser    = trim((string) ($mailCfg['smtp_user']    ?? ''));
            $smtpPass    = trim((string) ($mailCfg['smtp_pass']    ?? ''));

            $emailEnviado      = false;
            $emailDestinatario = null;
            $emailError        = null;
            $smtpDebugLog      = '';

            // 4. Intentar enviar email solo si mail_cartera está configurado
            if ($mailCartera !== '' && filter_var($mailCartera, FILTER_VALIDATE_EMAIL)
                && $smtpHost !== '' && $smtpUser !== '' && $smtpPass !== '') {

                $autoload = defined('RAIZ') ? (dirname(RAIZ) . '/vendor/autoload.php')
                                             : (__DIR__ . '/../../../vendor/autoload.php');

                if (is_file($autoload)) {
                    require_once $autoload;

                    $smtpPort    = (int) ($mailCfg['smtp_port']    ?? 587);
                    $smtpSecure  = strtolower(trim((string) ($mailCfg['smtp_secure'] ?? 'tls')));
                    $fromName    = trim((string) ($mailCfg['mail_from_name'] ?? 'Sparta Ledger'));
                    $idCredito   = (int) $registro['id_credito'];
                    $cliente     = htmlspecialchars($registro['nombre_cliente'], ENT_QUOTES, 'UTF-8');
                    $producto    = $convenio ? htmlspecialchars($convenio['nombre_producto'],    ENT_QUOTES, 'UTF-8') : '—';
                    $total       = $convenio ? number_format((float) $convenio['total_a_pagar'],       2) : '—';
                    $adeudo      = $convenio ? number_format((float) $convenio['adeudo_total_original'], 2) : '—';
                    $descuento   = $convenio ? $convenio['porcentaje_descuento'] . '%'                   : '—';
                    $semanas     = $convenio ? (int) $convenio['numero_semanas']                         : '—';
                    $fechaAcuerdo = $convenio ? htmlspecialchars($convenio['fecha_acuerdo'] ?? '', ENT_QUOTES, 'UTF-8') : '—';
                    $fechaEnvio  = date('d/m/Y H:i');

                    // Datos S2
                    $s2CuotasContrStr = $s2CuotasContratadas !== null ? (string) $s2CuotasContratadas : '—';
                    $s2CuotasPagStr   = $s2CuotasPagadas     !== null ? (string) $s2CuotasPagadas     : '—';
                    $s2TotalStr       = $s2TotalPagado        !== null
                        ? '$' . number_format(abs($s2TotalPagado), 2) : '—';
                    $s2MontoOtorgadoStr = $s2MontoOtorgado !== null
                        ? '$' . number_format(abs($s2MontoOtorgado), 2) : '—';
                    $semanaStr = ($semanaAcuerdo !== null && $anioSemanaAcuerdo !== null)
                        ? "Sem. {$semanaAcuerdo} / {$anioSemanaAcuerdo}" : '—';

                    // Adjuntar PDF del convenio si existe
                    $adjuntos = [];
                    if ($convenio && !empty($convenio['pdf_adjunto'])) {
                        $pdfPath = sparta_uploads_join('convenios', basename($convenio['pdf_adjunto']));
                        if (is_file($pdfPath) && is_readable($pdfPath)) {
                            $adjuntos[] = $pdfPath;
                        }
                    }

                    // Adjuntar comprobantes de pago si existen
                    if ($convenio) {
                        $compRows = $db->queryAll(
                            "SELECT comprobante_path FROM convenio_cliente_amortizacion
                             WHERE id_convenio_cliente = :id
                               AND comprobante_path IS NOT NULL AND comprobante_path != ''",
                            ['id' => (int) $convenio['id_convenio']]
                        );
                        if ($compRows) {
                            foreach ($compRows as $cr) {
                                $compPath = sparta_uploads_join('comprobantes', basename($cr['comprobante_path']));
                                if (is_file($compPath) && is_readable($compPath)) {
                                    $adjuntos[] = $compPath;
                                }
                            }
                        }
                    }

                    $html = <<<HTML
                    <!DOCTYPE html>
                    <html lang="es">
                    <head><meta charset="UTF-8"><title>Cierre de Crédito</title></head>
                    <body style="font-family:Arial,sans-serif;color:#333;margin:0;padding:20px;">
                      <div style="max-width:640px;margin:auto;border:1px solid #ddd;border-radius:8px;overflow:hidden;">
                        <div style="background:#1a52a8;color:#fff;padding:20px 24px;">
                          <h2 style="margin:0;font-size:20px;">Cierre de Crédito — Envío a Cartera</h2>
                          <p style="margin:4px 0 0;font-size:13px;opacity:.85;">Enviado el {$fechaEnvio} por {$usuario}</p>
                        </div>
                        <div style="padding:24px;">
                          <table style="width:100%;border-collapse:collapse;font-size:14px;">
                            <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;width:45%;">Crédito #</td><td style="padding:8px;">{$idCredito}</td></tr>
                            <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Cliente</td><td style="padding:8px;">{$cliente}</td></tr>
                            <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Producto</td><td style="padding:8px;">{$producto}</td></tr>
                            <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Adeudo original</td><td style="padding:8px;">\${$adeudo}</td></tr>
                            <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Descuento aplicado</td><td style="padding:8px;">{$descuento}</td></tr>
                            <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Total a pagar</td><td style="padding:8px;color:#1a52a8;font-weight:bold;">\${$total}</td></tr>
                            <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Semanas</td><td style="padding:8px;">{$semanas}</td></tr>
                            <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Fecha de acuerdo</td><td style="padding:8px;">{$fechaAcuerdo}</td></tr>
                            <tr><td colspan="2" style="padding:10px 8px 4px;font-size:12px;font-weight:bold;color:#0369a1;background:#f0f9ff;border-top:2px solid #bae6fd;letter-spacing:.04em;text-transform:uppercase;"><i>Datos crédito</i></td></tr>
                            <tr><td style="padding:8px;background:#f0f9ff;font-weight:bold;">Semana del acuerdo</td><td style="padding:8px;">{$semanaStr}</td></tr>
                            <tr><td style="padding:8px;background:#f0f9ff;font-weight:bold;">Monto otorgado</td><td style="padding:8px;font-weight:bold;">{$s2MontoOtorgadoStr}</td></tr>
                            <tr><td style="padding:8px;background:#f0f9ff;font-weight:bold;">Cuotas contratadas</td><td style="padding:8px;">{$s2CuotasContrStr}</td></tr>
                            <tr><td style="padding:8px;background:#f0f9ff;font-weight:bold;">Cuotas pagadas</td><td style="padding:8px;">{$s2CuotasPagStr}</td></tr>
                            <tr><td style="padding:8px;background:#f0f9ff;font-weight:bold;">Total pagado</td><td style="padding:8px;color:#059669;font-weight:bold;">{$s2TotalStr}</td></tr>
                          </table>
                          <p style="margin-top:20px;font-size:13px;color:#666;">
                            Este crédito fue validado y está listo para su procesamiento en cartera.
                            Se adjuntan el PDF del convenio y los comprobantes de pago cuando están disponibles.
                          </p>
                        </div>
                        <div style="background:#f4f6fb;padding:12px 24px;font-size:11px;color:#999;text-align:center;">
                          Generado automáticamente por Sparta Ledger — no responder a este correo.
                        </div>
                      </div>
                    </body>
                    </html>
                    HTML;

                    // Activar debug SMTP: captura toda la conversación en un buffer
                    $smtpDebugLog = '';
                    try {
                        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
                        $mailer->isSMTP();
                        $mailer->SMTPDebug   = \PHPMailer\PHPMailer\SMTP::DEBUG_SERVER;
                        $mailer->Debugoutput = function (string $str, int $level) use (&$smtpDebugLog) {
                            $smtpDebugLog .= "[{$level}] {$str}\n";
                        };
                        $mailer->Host       = $smtpHost;
                        $mailer->Port       = $smtpPort;
                        $mailer->SMTPAuth   = true;
                        $mailer->Username   = $smtpUser;
                        $mailer->Password   = $smtpPass;
                        $mailer->SMTPSecure = ($smtpSecure === 'ssl')
                            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mailer->CharSet    = 'UTF-8';
                        $mailer->isHTML(true);
                        $mailer->setFrom($smtpUser, $fromName);
                        $mailer->Sender     = $smtpUser;
                        $mailer->addReplyTo($smtpUser, $fromName);
                        $mailer->addAddress($mailCartera);
                        $mailer->Subject    = "Cierre de Crédito #{$idCredito} — {$cliente}";
                        $mailer->Body       = $html;
                        $mailer->AltBody    = strip_tags($html);

                        foreach ($adjuntos as $adj) {
                            $mailer->addAttachment($adj);
                        }

                        $mailer->send();
                        $emailEnviado      = true;
                        $emailDestinatario = $mailCartera;

                        if ((string) getenv('SPARTA_ENABLE_FILE_LOGS') === '1') {
                            $logDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta___SPARTA_SECRET_REDACTED___smtp_logs';
                            if (is_dir($logDir) || @mkdir($logDir, 0755, true)) {
                                $logPath = $logDir . DIRECTORY_SEPARATOR . 'smtp_cierre_credito.log';
                                @file_put_contents($logPath, date('Y-m-d H:i:s') . " [OK] Credito#{$idCredito}\n" . $smtpDebugLog . "\n", FILE_APPEND);
                            }
                        }

                    } catch (\Throwable $mailEx) {
                        $emailError = $mailEx->getMessage();
                        if ((string) getenv('SPARTA_ENABLE_FILE_LOGS') === '1') {
                            $logDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta___SPARTA_SECRET_REDACTED___smtp_logs';
                            if (is_dir($logDir) || @mkdir($logDir, 0755, true)) {
                                $logPath = $logDir . DIRECTORY_SEPARATOR . 'smtp_cierre_credito.log';
                                @file_put_contents(
                                    $logPath,
                                    date('Y-m-d H:i:s') . " [ERROR] Credito#{$idCredito} - {$emailError}\n"
                                    . "From: {$smtpUser}  To: {$mailCartera}\n"
                                    . $smtpDebugLog . "\n",
                                    FILE_APPEND
                                );
                            }
                        }

                        error_log('CierreCredito::enviarACartera mail -> ' . $emailError);
                        // El correo falló pero el proceso continúa hacia el paso 5
                    }
                }
            }

            // 5. Actualizar estatus
            if ($emailError !== null) {
                // Email fallido: regresar a en_proceso y guardar fecha_envio_cartera como señal
                $db->CRUD(
                    "UPDATE cierre_credito_seguimiento
                     SET estatus               = 'en_proceso',
                         usuario_actualizacion = :usuario,
                         fecha_actualizacion   = NOW(),
                         fecha_envio_cartera   = NOW()
                     WHERE id = :id",
                    ['usuario' => $usuario, 'id' => $id]
                );
                $msg = 'El correo no pudo enviarse. El registro regresó a En Proceso para reintento.';
            } else {
                // Email OK o no configurado
                $db->CRUD(
                    "UPDATE cierre_credito_seguimiento
                     SET estatus               = 'enviado_cartera',
                         usuario_actualizacion = :usuario,
                         fecha_envio_cartera   = NOW(),
                         email_destino_cartera = :email
                     WHERE id = :id",
                    ['usuario' => $usuario, 'email' => $emailDestinatario, 'id' => $id]
                );
                if ($emailEnviado) {
                    $msg = "Cierre enviado a cartera y correo notificado a {$emailDestinatario}.";
                } else {
                    $msg = 'Cierre marcado como enviado a cartera. (Correo no configurado: revisar mail_cartera en config.ini)';
                }
            }

            $resultado = self::resultado(true, $msg);
            if ($emailError !== null) {
                $resultado['email_error']     = $emailError;
                $resultado['email_smtp_log']  = $smtpDebugLog ?? '';
            }
            return $resultado;

        } catch (\Throwable $e) {
            error_log('CierreCredito::enviarACartera -> ' . $e->getMessage());
            return self::resultado(false, 'Error al enviar a cartera.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // DETALLE PARA ACORDEÓN + EXCEL
    // ─────────────────────────────────────────────

    /**
     * Devuelve encabezado del convenio + tabla de amortización completa
     * para un registro de cierre_credito_seguimiento dado.
     *
     * @param int $idCierre  PK de cierre_credito_seguimiento
     */
    public static function getDetalleCierre(int $idCierre): array
    {
        try {
            $db = new Database();

            // 1. Registro de seguimiento
            $cierre = $db->queryOne(
                "SELECT id, id_credito, nombre_cliente, estatus,
                        usuario_alta, fecha_alta
                 FROM cierre_credito_seguimiento
                 WHERE id = :id LIMIT 1",
                ['id' => $idCierre]
            );
            if (!$cierre) {
                return self::resultado(false, 'Registro no encontrado.');
            }

            // 2. Convenio completado
            $convenio = $db->queryOne(
                "SELECT cc.id AS id_convenio, cc.id_credito,
                        pc.nombre               AS nombre_producto,
                        cc.pdf_adjunto,
                        cc.total_a_pagar, cc.porcentaje_descuento,
                        cc.descuento_monto,     cc.adeudo_total_original,
                        cc.monto_adicional,     cc.pago_inicial_monto,
                        cc.numero_semanas,      cc.pago_semanal,
                        cc.fecha_acuerdo,       cc.fecha_primer_pago,
                        cc.fecha_ultimo_pago
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.id_credito = :id_credito AND cc.estatus = 'completado'
                 ORDER BY cc.fecha_alta DESC LIMIT 1",
                ['id_credito' => (int) $cierre['id_credito']]
            );

            // 3. Tabla de amortización
            $amortizacion = [];
            if ($convenio) {
                $amortizacion = $db->queryAll(
                    "SELECT numero_semana, fecha_pago, pago_semanal,
                            capital, saldo_restante, estatus_pago,
                            comprobante_path, fecha_pago_real
                     FROM convenio_cliente_amortizacion
                     WHERE id_convenio_cliente = :id
                     ORDER BY numero_semana ASC",
                    ['id' => (int) $convenio['id_convenio']]
                ) ?: [];
            }

            return self::resultado(true, 'Detalle obtenido.', [
                'cierre'       => $cierre,
                'convenio'     => $convenio,
                'amortizacion' => $amortizacion,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener el detalle.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // CATÁLOGO DE MOTIVOS DE DESCARTE
    // ─────────────────────────────────────────────

    public static function getCatalogoDescarte(): array
    {
        try {
            $db   = new Database();
            $rows = $db->queryAll(
                "SELECT id, motivo FROM catalogo_cierre_credito_seguimiento
                 WHERE estatus = 1 ORDER BY id ASC"
            );
            return self::resultado(true, 'Catálogo obtenido.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener catálogo.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // DESCARTAR (regresa a Enviados Finalizados)
    // ─────────────────────────────────────────────

    /**
     * Elimina el registro de cierre_credito_seguimiento para que el convenio
     * vuelva a aparecer en la pestaña de Enviados Finalizados.
     */
    public static function descartar(int $id, string $usuario, int $motivoId = 0, string $comentario = ''): array
    {
        try {
            $db = new Database();

            $registro = $db->queryOne(
                "SELECT id FROM cierre_credito_seguimiento
                 WHERE id = :id AND estatus = 'en_proceso'
                 LIMIT 1",
                ['id' => $id]
            );

            if (!$registro) {
                return self::resultado(false, 'Registro no encontrado o ya no está en proceso.');
            }

            $db->CRUD(
                "UPDATE cierre_credito_seguimiento
                 SET estatus               = 'descartado',
                     motivo_descarte       = :motivo,
                     comentario_descarte   = :comentario,
                     usuario_actualizacion = :usuario,
                     fecha_actualizacion   = NOW()
                 WHERE id = :id",
                ['id' => $id, 'usuario' => $usuario, 'motivo' => $motivoId ?: null, 'comentario' => $comentario ?: null]
            );

            return self::resultado(true, 'Registro descartado. El convenio regresó a Enviados Finalizados.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al descartar el registro.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // HISTORIAL DE MOVIMIENTOS
    // ─────────────────────────────────────────────

    /**
     * Devuelve todos los registros de cierre_credito_seguimiento ordenados
     * por actividad más reciente, para la pestaña Historial.
     */
    // ─────────────────────────────────────────────
    // MARCAR LISTO PARA REENVÍO
    // ─────────────────────────────────────────────

    /**
     * Promueve un registro de 'en_cola' a 'listo_envio'.
     * El usuario decide manualmente cuando el límite de envíos ya se restableció.
     */
    public static function marcarListoEnvio(int $id, string $usuario): array
    {
        try {
            $db = new Database();
            $registro = $db->queryOne(
                "SELECT id FROM cierre_credito_seguimiento WHERE id = :id AND estatus = 'en_cola' LIMIT 1",
                ['id' => $id]
            );
            if (!$registro) {
                return self::resultado(false, 'Registro no encontrado o no está en cola.');
            }
            $db->CRUD(
                "UPDATE cierre_credito_seguimiento
                 SET estatus               = 'listo_envio',
                     usuario_actualizacion = :usuario,
                     fecha_actualizacion   = NOW()
                 WHERE id = :id",
                ['usuario' => $usuario, 'id' => $id]
            );
            return self::resultado(true, 'Registro marcado como listo para reenvío.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al actualizar.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // TODOS LOS CONVENIOS (pestaña Convenios)
    // ─────────────────────────────────────────────

    /**
     * Devuelve todos los convenios con progreso de pagos y documentos adjuntos.
     */
    public static function getAllConvenios(?array $celulas = null): array
    {
        try {
            $db     = new Database();
            $params = [];
            $celulaWhere = self::_celulaWhere($celulas, 'cc', $params);
            $rows = $db->queryAll(
                "SELECT
                    cc.id,
                    cc.id_credito,
                    cc.nombre_cliente,
                    pc.nombre               AS nombre_producto,
                    cc.porcentaje_descuento,
                    cc.descuento_monto,
                    cc.total_a_pagar,
                    cc.monto_adicional,
                    cc.adeudo_total_original,
                    cc.numero_semanas,
                    cc.pago_semanal,
                    cc.fecha_acuerdo,
                    cc.fecha_primer_pago,
                    cc.fecha_ultimo_pago,
                    cc.estatus,
                    cc.pdf_adjunto,
                    cc.usuario_alta,
                    cc.fecha_alta,
                    cc.fecha_modifica,
                    cc.id_celula,
                    (SELECT COUNT(*)
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id
                       AND a.estatus_pago = 'pagado')                         AS cuotas_pagadas,
                    (SELECT COUNT(*)
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id)                     AS num_semanas_amort,
                    (SELECT COUNT(*)
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id
                       AND a.comprobante_path IS NOT NULL
                       AND a.comprobante_path != '')                          AS comprobantes_subidos,
                    (SELECT ccs.estatus
                     FROM cierre_credito_seguimiento ccs
                     WHERE ccs.id_credito = cc.id_credito
                     ORDER BY ccs.fecha_actualizacion DESC, ccs.fecha_alta DESC
                     LIMIT 1)                                                 AS estatus_seguimiento
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.estatus IN ('activo', 'cancelado'){$celulaWhere}
                 ORDER BY cc.fecha_alta DESC",
                $params ?: null
            );
            $rows = $rows ?: [];
            self::_enrichWithS2AndSemana($rows);
            return self::resultado(true, 'Convenios.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener convenios.', [], $e->getMessage());
        }
    }

    /**
     * Convenios activos para el reporte XLSX de la pestana Convenios Activos.
     */
    public static function getConveniosActivosReporte(?array $celulas = null): array
    {
        try {
            $db     = new Database();
            $params = [];
            $celulaWhere = self::_celulaWhere($celulas, 'cc', $params);

            $rows = $db->queryAll(
                "SELECT
                    cc.id,
                    cc.id_credito,
                    cc.nombre_cliente,
                    pc.nombre AS nombre_producto,
                    cc.id_producto_convenio,
                    cc.id_producto_convenio_detalle,
                    cc.bucket_morosidad_real,
                    cc.dias_mora,
                    cc.avance_pago_plazo,
                    cc.adeudo_total_original,
                    cc.porcentaje_descuento,
                    cc.descuento_monto,
                    cc.total_a_pagar,
                    cc.monto_adicional,
                    cc.pago_inicial_monto,
                    cc.numero_semanas,
                    cc.pago_semanal,
                    cc.fecha_acuerdo,
                    cc.fecha_primer_pago,
                    cc.fecha_ultimo_pago,
                    cc.tipo_calendario,
                    cc.estatus,
                    cc.usuario_alta,
                    cc.pdf_adjunto,
                    cc.base_calculo,
                    CASE cc.id_celula
                        WHEN 1 THEN 'Despachos'
                        WHEN 2 THEN 'Call Center'
                        WHEN 3 THEN 'Campo'
                        ELSE COALESCE(CAST(cc.id_celula AS CHAR), 'Sin celula')
                    END AS celula,
                    cc.fecha_alta,
                    cc.fecha_modifica
                 FROM convenio_cliente cc
                 LEFT JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.estatus = 'activo'{$celulaWhere}
                 ORDER BY cc.fecha_alta DESC",
                $params ?: null
            );

            return self::resultado(true, 'Reporte de convenios activos.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener el reporte de convenios activos.', [], $e->getMessage());
        }
    }

    /**
     * Devuelve encabezado del convenio + tabla de amortizacion completa
     * para un convenio_cliente.id dado (acceso directo, sin cierre_credito_seguimiento).
     *
     * @param int $idConvenio PK de convenio_cliente
     */
    public static function getDetalleConvenio(int $idConvenio): array
    {
        try {
            $db      = new Database();
            $convenio = $db->queryOne(
                "SELECT cc.id AS id_convenio, cc.id_credito, cc.nombre_cliente,
                        pc.nombre               AS nombre_producto,
                        cc.pdf_adjunto,
                        cc.total_a_pagar,       cc.porcentaje_descuento,
                        cc.descuento_monto,     cc.adeudo_total_original,
                        cc.monto_adicional,     cc.pago_inicial_monto,
                        cc.numero_semanas,      cc.pago_semanal,
                        cc.fecha_acuerdo,       cc.fecha_primer_pago,
                        cc.fecha_ultimo_pago,   cc.usuario_alta,
                        (SELECT ccs.comentario_descarte
                         FROM cierre_credito_seguimiento ccs
                         WHERE ccs.id_credito = cc.id_credito
                           AND ccs.estatus = 'devuelto_cartera'
                         ORDER BY ccs.fecha_actualizacion DESC LIMIT 1) AS comentario_devolucion_cartera,
                        (SELECT ccs.usuario_actualizacion
                         FROM cierre_credito_seguimiento ccs
                         WHERE ccs.id_credito = cc.id_credito
                           AND ccs.estatus = 'devuelto_cartera'
                         ORDER BY ccs.fecha_actualizacion DESC LIMIT 1) AS usuario_devolucion_cartera,
                        (SELECT ccs.fecha_actualizacion
                         FROM cierre_credito_seguimiento ccs
                         WHERE ccs.id_credito = cc.id_credito
                           AND ccs.estatus = 'devuelto_cartera'
                         ORDER BY ccs.fecha_actualizacion DESC LIMIT 1) AS fecha_devolucion_cartera
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.id = :id LIMIT 1",
                ['id' => $idConvenio]
            );
            if (!$convenio) {
                return self::resultado(false, 'Convenio no encontrado.');
            }
            $amortizacion = $db->queryAll(
                "SELECT numero_semana, fecha_pago, pago_semanal,
                        capital, saldo_restante, estatus_pago,
                        comprobante_path, fecha_pago_real
                 FROM convenio_cliente_amortizacion
                 WHERE id_convenio_cliente = :id
                 ORDER BY numero_semana ASC",
                ['id' => $idConvenio]
            ) ?: [];
            return self::resultado(true, 'Detalle del convenio.', [
                'convenio'     => $convenio,
                'amortizacion' => $amortizacion,
            ]);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener el detalle.', [], $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    // CARTERA — NOTIFICACIONES A CARTERA
    // ─────────────────────────────────────────────

    /**
     * Notifica a cartera que un despacho creó un convenio.
     * Crea/actualiza un registro notificado_cartera y envía email con amortización.
     *
     * @param int    $idConvenio  PK de convenio_cliente
     * @param string $usuario     Usuario que notifica
     */
    public static function notificarConvenio(int $idConvenio, string $usuario): array
    {
        try {
            $db = new Database();

            // 1. Obtener el convenio
            $convenio = $db->queryOne(
                "SELECT cc.id AS id_convenio, cc.id_credito, cc.nombre_cliente,
                        pc.nombre               AS nombre_producto,
                        cc.pdf_adjunto,
                        cc.total_a_pagar,       cc.porcentaje_descuento,
                        cc.descuento_monto,     cc.adeudo_total_original,
                        cc.numero_semanas,      cc.pago_semanal,
                        cc.fecha_acuerdo,       cc.estatus,
                        cc.id_celula
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.id = :id LIMIT 1",
                ['id' => $idConvenio]
            );

            if (!$convenio) {
                return self::resultado(false, 'Convenio no encontrado.');
            }

            $idCredito     = (int) $convenio['id_credito'];
            $nombreCliente = (string) $convenio['nombre_cliente'];

            // 2. Tabla de amortización
            $amortizacion = $db->queryAll(
                "SELECT numero_semana, fecha_pago, pago_semanal, saldo_restante, estatus_pago
                 FROM convenio_cliente_amortizacion
                 WHERE id_convenio_cliente = :id ORDER BY numero_semana ASC",
                ['id' => $idConvenio]
            ) ?: [];

            // 3. Crear o actualizar registro en cierre_credito_seguimiento
            $existing = $db->queryOne(
                "SELECT id, estatus FROM cierre_credito_seguimiento
                 WHERE id_credito = :id ORDER BY fecha_alta DESC LIMIT 1",
                ['id' => $idCredito]
            );

            $idCelula = (int) ($convenio['id_celula'] ?? 0) ?: null;

            // Crear o actualizar el registro de seguimiento siempre que se notifique,
            // sin importar si el convenio está activo o completado.
            if ($existing) {
                $estatusActual = $existing['estatus'];
                // No interferir con flujo activo de cierre
                if (in_array($estatusActual, ['en_proceso', 'enviado_cartera', 'en_cola', 'listo_envio'], true)) {
                    return self::resultado(false, 'Este crédito ya tiene un proceso de cierre activo. No se puede re-notificar.');
                }
                // Actualizar a notificado_cartera
                $db->CRUD(
                    "UPDATE cierre_credito_seguimiento
                     SET estatus               = 'notificado_cartera',
                         nombre_cliente         = :nombre_cliente,
                         usuario_actualizacion  = :usuario,
                         fecha_actualizacion    = NOW()
                     WHERE id = :id",
                    ['nombre_cliente' => $nombreCliente, 'usuario' => $usuario, 'id' => (int) $existing['id']]
                );
            } else {
                $db->CRUD(
                    "INSERT INTO cierre_credito_seguimiento
                        (id_credito, nombre_cliente, estatus, usuario_alta, usuario_actualizacion, id_celula)
                     VALUES
                        (:id_credito, :nombre_cliente, 'notificado_cartera', :usuario, :usuario, :id_celula)",
                    ['id_credito' => $idCredito, 'nombre_cliente' => $nombreCliente,
                     'usuario' => $usuario, 'id_celula' => $idCelula]
                );
            }

            // 4. Enviar email
            $emailError = null;

            $configPath = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../../config/config.ini');
            $ini        = is_file($configPath) ? @parse_ini_file($configPath, true) : [];
            if ($idCelula === 2 && !empty($ini['mail_cierre_callcenter'])) {
                $mailCfg = $ini['mail_cierre_callcenter'];
            } elseif (!empty($ini['mail_cierre'])) {
                $mailCfg = $ini['mail_cierre'];
            } else {
                $mailCfg = $ini['mail'] ?? [];
            }

            $mailCartera = trim((string) ($mailCfg['mail_cartera'] ?? ''));
            $smtpHost    = trim((string) ($mailCfg['smtp_host']    ?? ''));
            $smtpUser    = trim((string) ($mailCfg['smtp_user']    ?? ''));
            $smtpPass    = trim((string) ($mailCfg['smtp_pass']    ?? ''));

            if ($mailCartera !== '' && filter_var($mailCartera, FILTER_VALIDATE_EMAIL)
                && $smtpHost !== '' && $smtpUser !== '' && $smtpPass !== '') {

                $autoload = defined('RAIZ') ? (dirname(RAIZ) . '/vendor/autoload.php')
                                             : (__DIR__ . '/../../../vendor/autoload.php');

                if (is_file($autoload)) {
                    require_once $autoload;

                    $smtpPort   = (int)    ($mailCfg['smtp_port']    ?? 587);
                    $smtpSecure = strtolower(trim((string) ($mailCfg['smtp_secure'] ?? 'tls')));
                    $fromName   = trim((string) ($mailCfg['mail_from_name'] ?? 'Sparta Ledger'));

                    $idCredFmt   = (int) $idCredito;
                    $cliente     = htmlspecialchars($nombreCliente, ENT_QUOTES, 'UTF-8');
                    $producto    = htmlspecialchars($convenio['nombre_producto'],     ENT_QUOTES, 'UTF-8');
                    $total       = number_format((float) $convenio['total_a_pagar'],         2);
                    $adeudo      = number_format((float) $convenio['adeudo_total_original'],  2);
                    $descuento   = $convenio['porcentaje_descuento'] . '%';
                    $semanas     = (int) $convenio['numero_semanas'];
                    $pagoSemanal = number_format((float) $convenio['pago_semanal'], 2);
                    $fechaAcuerdo = htmlspecialchars($convenio['fecha_acuerdo'] ?? '', ENT_QUOTES, 'UTF-8');
                    $fechaEnvio  = date('d/m/Y H:i');

                    // Tabla de amortización en HTML
                    $filasAmort = '';
                    foreach ($amortizacion as $a) {
                        $pagada = ($a['estatus_pago'] === 'pagado');
                        $bg     = $pagada ? '#f0fdf4' : '#fff';
                        $icono  = $pagada ? '✔' : '⏳';
                        $filasAmort .= "<tr style=\"background:{$bg};\">
                            <td style=\"padding:5px 8px;border:1px solid #e2e8f0;text-align:center;font-weight:bold;\">{$a['numero_semana']}</td>
                            <td style=\"padding:5px 8px;border:1px solid #e2e8f0;\">" . htmlspecialchars($a['fecha_pago'] ?? '—', ENT_QUOTES, 'UTF-8') . "</td>
                            <td style=\"padding:5px 8px;border:1px solid #e2e8f0;text-align:right;\">\$" . number_format((float) $a['pago_semanal'], 2) . "</td>
                            <td style=\"padding:5px 8px;border:1px solid #e2e8f0;text-align:right;\">\$" . number_format((float) $a['saldo_restante'], 2) . "</td>
                            <td style=\"padding:5px 8px;border:1px solid #e2e8f0;text-align:center;\">{$icono} " . ($pagada ? 'Pagado' : 'Pendiente') . "</td>
                        </tr>";
                    }

                    $html = <<<HTML
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<title>Notificación de Convenio a Cartera</title></head>
<body style="font-family:Arial,sans-serif;color:#333;margin:0;padding:20px;">
  <div style="max-width:680px;margin:auto;border:1px solid #ddd;border-radius:8px;overflow:hidden;">
    <div style="background:#0f5c8a;color:#fff;padding:20px 24px;">
      <h2 style="margin:0;font-size:20px;">📋 Nuevo Convenio — Notificación a Cartera</h2>
      <p style="margin:4px 0 0;font-size:13px;opacity:.85;">Notificado el {$fechaEnvio} por {$usuario}</p>
    </div>
    <div style="padding:24px;">
      <p style="background:#fefce8;border-left:4px solid #fbbf24;padding:10px 14px;font-size:14px;margin:0 0 18px;">
        El despacho ha creado un convenio de pago para el siguiente crédito. Por favor tome nota e ingrese los datos correspondientes en S2.
      </p>
      <table style="width:100%;border-collapse:collapse;font-size:14px;margin-bottom:20px;">
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;width:45%;">Crédito #</td><td style="padding:8px;">{$idCredFmt}</td></tr>
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Cliente</td><td style="padding:8px;">{$cliente}</td></tr>
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Producto</td><td style="padding:8px;">{$producto}</td></tr>
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Adeudo original</td><td style="padding:8px;">\${$adeudo}</td></tr>
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Descuento aplicado</td><td style="padding:8px;">{$descuento}</td></tr>
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Total a pagar</td><td style="padding:8px;color:#0f5c8a;font-weight:bold;">\${$total}</td></tr>
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Semanas</td><td style="padding:8px;">{$semanas}</td></tr>
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Pago semanal</td><td style="padding:8px;">\${$pagoSemanal}</td></tr>
        <tr><td style="padding:8px;background:#f4f6fb;font-weight:bold;">Fecha de acuerdo</td><td style="padding:8px;">{$fechaAcuerdo}</td></tr>
      </table>
      <div style="font-size:13px;font-weight:bold;text-transform:uppercase;letter-spacing:.04em;color:#0f5c8a;margin-bottom:8px;">
        Tabla de Amortización
      </div>
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="background:#0f5c8a;color:#fff;">
            <th style="padding:7px 8px;border:1px solid #0f5c8a;text-align:center;">#</th>
            <th style="padding:7px 8px;border:1px solid #0f5c8a;">Fecha pago</th>
            <th style="padding:7px 8px;border:1px solid #0f5c8a;text-align:right;">Pago</th>
            <th style="padding:7px 8px;border:1px solid #0f5c8a;text-align:right;">Saldo restante</th>
            <th style="padding:7px 8px;border:1px solid #0f5c8a;text-align:center;">Estatus</th>
          </tr>
        </thead>
        <tbody>{$filasAmort}</tbody>
      </table>
    </div>
    <div style="background:#f4f6fb;padding:12px 24px;font-size:11px;color:#999;text-align:center;">
      Generado automáticamente por Sparta Ledger — no responder a este correo.
    </div>
  </div>
</body></html>
HTML;

                    try {
                        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
                        $mailer->isSMTP();
                        $mailer->Host       = $smtpHost;
                        $mailer->SMTPAuth   = true;
                        $mailer->Username   = $smtpUser;
                        $mailer->Password   = $smtpPass;
                        $mailer->SMTPSecure = $smtpSecure === 'ssl'
                            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mailer->Port = $smtpPort;
                        $mailer->CharSet = 'UTF-8';
                        $mailer->setFrom($smtpUser, $fromName);
                        $mailer->addAddress($mailCartera);
                        $mailer->Subject = "Nuevo Convenio Crédito #{$idCredFmt} — {$cliente}";
                        $mailer->isHTML(true);
                        $mailer->Body    = $html;
                        $mailer->AltBody = "Nuevo convenio creado para crédito #{$idCredFmt} ({$cliente}). Total: \${$total}.";

                        // Adjuntar PDF del convenio si existe
                        if (!empty($convenio['pdf_adjunto'])) {
                            $pdfPath = sparta_uploads_join('convenios', basename($convenio['pdf_adjunto']));
                            if (is_file($pdfPath) && is_readable($pdfPath)) {
                                $mailer->addAttachment($pdfPath);
                            }
                        }

                        $mailer->send();
                    } catch (\Throwable $mailEx) {
                        $emailError = $mailEx->getMessage();
                        error_log('CierreCredito::notificarConvenio mail -> ' . $emailError);
                    }
                }
            }

            $msg = $emailError === null
                ? 'Cartera notificada correctamente.' . ($mailCartera ? " Correo enviado a {$mailCartera}." : ' (Correo no configurado.)')
                : "Notificación registrada, pero el correo no pudo enviarse: {$emailError}";

            $res = self::resultado(true, $msg);
            if ($emailError !== null) $res['email_error'] = $emailError;
            return $res;

        } catch (\Exception $e) {
            return self::resultado(false, 'Error al notificar a cartera.', [], $e->getMessage());
        }
    }

    /**
     * Devuelve los registros en estatus notificado_cartera / cerrado / devuelto_cartera
     * para la pestaña Cartera.
     */
    public static function getCartera(?array $celulas = null): array
    {
        try {
            $db     = new Database();
            $params = [];
            $celulaWhere = self::_celulaWhere($celulas, 'ccs', $params);

            $rows = $db->queryAll(
                "SELECT ccs.id AS id_cierre, ccs.id_credito, ccs.nombre_cliente, ccs.estatus,
                        ccs.fecha_alta, ccs.usuario_alta,
                        ccs.fecha_actualizacion, ccs.usuario_actualizacion,
                        ccs.id_celula
                 FROM cierre_credito_seguimiento ccs
                 WHERE ccs.estatus IN ('enviado_cartera', 'notificado_cartera', 'cerrado', 'devuelto_cartera'){$celulaWhere}
                 ORDER BY ccs.fecha_actualizacion DESC, ccs.fecha_alta DESC",
                $params ?: null
            ) ?: [];

            if (empty($rows)) {
                return self::resultado(true, 'Sin notificaciones para cartera.', []);
            }

            // Enriquecer con datos del convenio (el más reciente activo/cancelado del crédito)
            $placeholders = [];
            $paramsConv   = [];
            foreach ($rows as $idx => $row) {
                $key              = 'id_' . $idx;
                $placeholders[]   = ':' . $key;
                $paramsConv[$key] = (int) $row['id_credito'];
            }
            $in = implode(',', $placeholders);

            $convenios = $db->queryAll(
                "SELECT cc.id AS id_convenio, cc.id_credito,
                        pc.nombre               AS nombre_producto,
                        cc.pdf_adjunto,
                        cc.total_a_pagar,       cc.porcentaje_descuento,
                        cc.descuento_monto,     cc.adeudo_total_original,
                        cc.numero_semanas,      cc.pago_semanal,
                        cc.fecha_acuerdo,       cc.estatus AS estatus_convenio,
                        (SELECT COUNT(*) FROM convenio_cliente_amortizacion a
                         WHERE a.id_convenio_cliente = cc.id) AS num_semanas_amort,
                        (SELECT COUNT(*) FROM convenio_cliente_amortizacion a
                         WHERE a.id_convenio_cliente = cc.id AND a.estatus_pago = 'pagado') AS cuotas_pagadas
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.id_credito IN ($in)
                 ORDER BY cc.fecha_alta DESC",
                $paramsConv
            ) ?: [];

            // Mapa id_credito → primer convenio
            $convenioMap = [];
            foreach ($convenios as $c) {
                if (!isset($convenioMap[$c['id_credito']])) {
                    $convenioMap[$c['id_credito']] = $c;
                }
            }

            // Mezclar
            foreach ($rows as &$row) {
                $conv = $convenioMap[$row['id_credito']] ?? null;
                $row['id_convenio']         = $conv ? (int) $conv['id_convenio']  : null;
                $row['nombre_producto']     = $conv ? $conv['nombre_producto']    : null;
                $row['pdf_adjunto']         = $conv ? $conv['pdf_adjunto']        : null;
                $row['total_a_pagar']       = $conv ? $conv['total_a_pagar']      : null;
                $row['porcentaje_descuento']= $conv ? $conv['porcentaje_descuento'] : null;
                $row['adeudo_total_original']= $conv ? $conv['adeudo_total_original'] : null;
                $row['numero_semanas']      = $conv ? $conv['numero_semanas']     : null;
                $row['pago_semanal']        = $conv ? $conv['pago_semanal']       : null;
                $row['fecha_acuerdo']       = $conv ? $conv['fecha_acuerdo']      : null;
                $row['estatus_convenio']    = $conv ? $conv['estatus_convenio']   : null;
                $row['num_semanas_amort']   = $conv ? (int) $conv['num_semanas_amort'] : 0;
                $row['cuotas_pagadas']      = $conv ? (int) $conv['cuotas_pagadas']    : 0;
            }
            unset($row);

            // Enriquecer con datos S2 (cuotas contratadas/pagadas, monto otorgado, total pagado, semana acuerdo)
            self::_enrichWithS2AndSemana($rows);

            return self::resultado(true, 'Notificaciones cartera.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener notificaciones de cartera.', [], $e->getMessage());
        }
    }

    /**
     * Cierra el convenio desde cartera — estatus → cerrado.
     */
    public static function cerrarConvenioCartera(int $id, string $usuario): array
    {
        try {
            $db = new Database();

            // 1. Obtener el registro completo
            $registro = $db->queryOne(
                "SELECT id, id_credito, nombre_cliente, usuario_alta, id_celula
                 FROM cierre_credito_seguimiento
                 WHERE id = :id AND estatus IN ('notificado_cartera', 'enviado_cartera') LIMIT 1",
                ['id' => $id]
            );
            if (!$registro) {
                return self::resultado(false, 'Registro no encontrado o no está pendiente de revisión.');
            }

            // 2. Actualizar estatus → cerrado
            $db->CRUD(
                "UPDATE cierre_credito_seguimiento
                 SET estatus              = 'cerrado',
                     usuario_actualizacion = :usuario,
                     fecha_actualizacion   = NOW()
                 WHERE id = :id",
                ['usuario' => $usuario, 'id' => $id]
            );

            // 3. Destinatario fijo de confirmación de cierre
            $emailDestinatario = 'cipriano.mendez@__SPARTA_SECRET_REDACTED__.mx';

            // 4. Enviar correo de confirmación de cierre
            $emailError = null;
            $configPath = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../../config/config.ini');
            $ini        = is_file($configPath) ? @parse_ini_file($configPath, true) : [];
            $idCelula   = (int) ($registro['id_celula'] ?? 0);
            if ($idCelula === 2 && !empty($ini['mail_cierre_callcenter'])) {
                $mailCfg = $ini['mail_cierre_callcenter'];
            } elseif (!empty($ini['mail_cierre'])) {
                $mailCfg = $ini['mail_cierre'];
            } else {
                $mailCfg = $ini['mail'] ?? [];
            }

            $smtpHost   = trim((string) ($mailCfg['smtp_host']    ?? ''));
            $smtpUser   = trim((string) ($mailCfg['smtp_user']    ?? ''));
            $smtpPass   = trim((string) ($mailCfg['smtp_pass']    ?? ''));
            $smtpPort   = (int) ($mailCfg['smtp_port']    ?? 587);
            $smtpSecure = strtolower(trim((string) ($mailCfg['smtp_secure'] ?? 'tls')));
            $fromName   = trim((string) ($mailCfg['mail_from_name'] ?? 'Sparta Ledger'));

            if ($smtpHost !== '' && $smtpUser !== '' && $smtpPass !== '') {
                $autoload = defined('RAIZ') ? (dirname(RAIZ) . '/vendor/autoload.php')
                                             : (__DIR__ . '/../../../vendor/autoload.php');
                if (is_file($autoload)) {
                    require_once $autoload;

                    $idCredito = (int) $registro['id_credito'];
                    $cliente   = htmlspecialchars((string) ($registro['nombre_cliente'] ?? ''), ENT_QUOTES, 'UTF-8');
                    $fechaCierre = date('d/m/Y H:i');
                    $usuarioCartera = htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8');

                    $html = <<<HTML
                    <!DOCTYPE html>
                    <html lang="es">
                    <head><meta charset="UTF-8"><title>Cierre Confirmado</title></head>
                    <body style="font-family:Arial,sans-serif;color:#333;margin:0;padding:20px;">
                      <div style="max-width:600px;margin:auto;border:1px solid #ddd;border-radius:8px;overflow:hidden;">
                        <div style="background:#059669;color:#fff;padding:20px 24px;">
                          <h2 style="margin:0;font-size:20px;">&#10003; Cierre de Crédito Confirmado</h2>
                          <p style="margin:5px 0 0;font-size:13px;opacity:.85;">Procesado el {$fechaCierre} por <strong>{$usuarioCartera}</strong></p>
                        </div>
                        <div style="padding:24px;">
                          <p style="font-size:15px;margin-top:0;">El siguiente crédito ha sido <strong>cerrado correctamente por el área de Cartera</strong> y ha completado su ciclo de vida.</p>
                          <table style="width:100%;border-collapse:collapse;font-size:14px;">
                            <tr><td style="padding:10px 12px;background:#f0fdf4;font-weight:bold;width:45%;border-bottom:1px solid #d1fae5;">Crédito #</td><td style="padding:10px 12px;border-bottom:1px solid #d1fae5;font-size:15px;font-weight:bold;color:#059669;">{$idCredito}</td></tr>
                            <tr><td style="padding:10px 12px;background:#f0fdf4;font-weight:bold;border-bottom:1px solid #d1fae5;">Cliente</td><td style="padding:10px 12px;border-bottom:1px solid #d1fae5;">{$cliente}</td></tr>
                            <tr><td style="padding:10px 12px;background:#f0fdf4;font-weight:bold;border-bottom:1px solid #d1fae5;">Fecha de cierre</td><td style="padding:10px 12px;border-bottom:1px solid #d1fae5;">{$fechaCierre}</td></tr>
                            <tr><td style="padding:10px 12px;background:#f0fdf4;font-weight:bold;">Confirmado por</td><td style="padding:10px 12px;">{$usuarioCartera}</td></tr>
                          </table>
                          <p style="margin-top:20px;font-size:13px;color:#555;border-left:3px solid #059669;padding-left:12px;">
                            Este registro ha sido cerrado en el sistema y <strong>no requiere ninguna acción adicional</strong>.<br>
                            Si tienes alguna duda, comunícate con el área de Cartera.
                          </p>
                        </div>
                        <div style="background:#f4f6fb;padding:12px 24px;font-size:11px;color:#999;text-align:center;">
                          Notificación automática de Sparta Ledger — no responder a este correo.
                        </div>
                      </div>
                    </body>
                    </html>
                    HTML;

                    try {
                        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
                        $mailer->isSMTP();
                        $mailer->SMTPDebug   = 0;
                        $mailer->Host        = $smtpHost;
                        $mailer->Port        = $smtpPort;
                        $mailer->SMTPAuth    = true;
                        $mailer->Username    = $smtpUser;
                        $mailer->Password    = $smtpPass;
                        $mailer->SMTPSecure  = ($smtpSecure === 'ssl')
                            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mailer->CharSet     = 'UTF-8';
                        $mailer->isHTML(true);
                        $mailer->setFrom($smtpUser, $fromName);
                        $mailer->Sender      = $smtpUser;
                        $mailer->addReplyTo($smtpUser, $fromName);
                        $mailer->addAddress($emailDestinatario);
                        $mailer->Subject     = "CIERRE CONFIRMADO #{$idCredito} — {$registro['nombre_cliente']}";
                        $mailer->Body        = $html;
                        $mailer->AltBody     = "Cierre confirmado. Crédito #{$idCredito} — {$registro['nombre_cliente']}. Procesado el {$fechaCierre} por {$usuario}.";
                        $mailer->send();
                    } catch (\Throwable $mailEx) {
                        $emailError = $mailEx->getMessage();
                        error_log('CierreCredito::cerrarConvenioCartera mail -> ' . $emailError);
                    }
                }
            }

            $resultado = self::resultado(true, 'Convenio cerrado correctamente por cartera.');
            if ($emailError !== null) {
                $resultado['email_error'] = $emailError;
            }
            return $resultado;
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al cerrar convenio.', [], $e->getMessage());
        }
    }

    /**
     * Devuelve el convenio por cartera — estatus → devuelto_cartera.
     */
    public static function devolverPorCartera(int $id, string $usuario, string $comentario = ''): array
    {
        try {
            $db = new Database();
            $registro = $db->queryOne(
                "SELECT id FROM cierre_credito_seguimiento
                 WHERE id = :id AND estatus = 'notificado_cartera' LIMIT 1",
                ['id' => $id]
            );
            if (!$registro) {
                return self::resultado(false, 'Registro no encontrado o no está pendiente de revisión.');
            }
            $db->CRUD(
                "UPDATE cierre_credito_seguimiento
                 SET estatus              = 'devuelto_cartera',
                     comentario_descarte   = :comentario,
                     usuario_actualizacion = :usuario,
                     fecha_actualizacion   = NOW()
                 WHERE id = :id",
                ['usuario' => $usuario, 'comentario' => mb_substr($comentario, 0, 250), 'id' => $id]
            );
            return self::resultado(true, 'Convenio devuelto por cartera.');
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al devolver convenio.', [], $e->getMessage());
        }
    }

    /**
     * Enriquece $rows con datos de S2 (__SPARTA_SECRET_REDACTED__) y la semana ISO del acuerdo.
     * Sólo usa Segundometro — ignora CROOP.
     *
     * Campos añadidos:
     *   semana_acuerdo        int|null   Semana ISO de fecha_acuerdo
     *   anio_semana_acuerdo   int|null   Año ISO correspondiente
     *   s2_cuotas_contratadas int|null   Numero_amortizaciones en S2
     *   s2_cuotas_pagadas     int|null   Num_cuotas_pagadas en S2
     *   s2_total_pagado       float|null Abonos_total en S2
     */
    private static function _enrichWithS2AndSemana(array &$rows): void
    {
        if (empty($rows)) return;

        // 1. Semana ISO del acuerdo (desde fecha_acuerdo)
        foreach ($rows as &$row) {
            $fecha = $row['fecha_acuerdo'] ?? null;
            if ($fecha && preg_match('/^\d{4}-\d{2}-\d{2}/', $fecha)) {
                try {
                    $d = new \DateTime(substr($fecha, 0, 10));
                    $row['semana_acuerdo']      = (int) $d->format('W');
                    $row['anio_semana_acuerdo'] = (int) $d->format('o');
                } catch (\Throwable $e) {
                    $row['semana_acuerdo']      = null;
                    $row['anio_semana_acuerdo'] = null;
                }
            } else {
                $row['semana_acuerdo']      = null;
                $row['anio_semana_acuerdo'] = null;
            }
        }
        unset($row);

        // 2. Datos S2 desde tbl_segundometro_semana (__SPARTA_SECRET_REDACTED__)
        $ids = array_values(array_filter(array_map(
            fn($r) => (int) ($r['id_credito'] ?? 0), $rows
        )));
        if (empty($ids)) {
            foreach ($rows as &$row) {
                $row['s2_cuotas_contratadas'] = null;
                $row['s2_cuotas_pagadas']     = null;
                $row['s2_total_pagado']       = null;
            }
            unset($row);
            return;
        }

        $placeholders = [];
        $params       = [];
        foreach ($ids as $idx => $id) {
            $key            = 'sm_' . $idx;
            $placeholders[] = ':' . $key;
            $params[$key]   = $id;
        }
        $inSql = implode(',', $placeholders);

        $s2Map = [];
        try {
            $dbSm   = new DatabaseSegundometro();
            $s2Rows = $dbSm->queryAll(
                "SELECT Id_credito, Numero_amortizaciones, Num_cuotas_pagadas, Abonos_total, Monto_otorgado,
                        Status_credito
                 FROM tbl_segundometro_semana
                 WHERE Id_credito IN ($inSql)",
                $params
            );
            if ($s2Rows) {
                foreach ($s2Rows as $sr) {
                    $s2Map[(int) $sr['Id_credito']] = $sr;
                }
            }
        } catch (\Throwable $e) {
            error_log('CierreCredito::_enrichWithS2AndSemana S2 error: ' . $e->getMessage());
        }

        foreach ($rows as &$row) {
            $s2 = $s2Map[(int) ($row['id_credito'] ?? 0)] ?? null;
            $row['s2_cuotas_contratadas'] = $s2 !== null ? (int)   $s2['Numero_amortizaciones'] : null;
            $row['s2_cuotas_pagadas']     = $s2 !== null ? (int)   $s2['Num_cuotas_pagadas']    : null;
            $row['s2_total_pagado']       = $s2 !== null ? (float) $s2['Abonos_total']          : null;
            $row['s2_monto_otorgado']     = $s2 !== null ? (float) $s2['Monto_otorgado']        : null;
            $row['s2_status_credito']     = $s2 !== null ? ($s2['Status_credito'] ?? null)      : null;
        }
        unset($row);
    }

    /**
     * Consulta la API de S2 en paralelo (cURL multi) y elimina de $rows
     * los créditos cuyo statusCredito sea 'Saldado'.
     * Silencia errores individuales — si S2 no responde para un crédito, lo deja pasar.
     */
    private static function _filterSaldadosEnS2(array &$rows): void
    {
        if (empty($rows)) return;

        $endpoint   = defined('ENDPOINT') ? ENDPOINT : 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta';
        $token      = defined('TOKEN')    ? TOKEN    : '__SPARTA_TOKEN_REDACTED__';
        $fechaCorte = date('Y-m-d');
        $headers    = ['Content-Type: application/json', 'Token: ' . $token];

        // Indexar IDs por posición para correlacionar respuestas
        $idsPorIdx = [];
        foreach ($rows as $i => $row) {
            $id = (int) ($row['id_credito'] ?? 0);
            if ($id > 0) $idsPorIdx[$i] = $id;
        }
        if (empty($idsPorIdx)) return;

        // Abrir handles cURL en paralelo
        $mh      = curl_multi_init();
        $handles = [];
        foreach ($idsPorIdx as $idx => $idCredito) {
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode(['idCredito' => $idCredito, 'fechaCorte' => $fechaCorte]),
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_HTTPHEADER     => $headers,
            ]);
            $handles[$idx] = $ch;
            curl_multi_add_handle($mh, $ch);
        }

        // Ejecutar hasta que todos terminen
        $running = null;
        do {
            curl_multi_exec($mh, $running);
            curl_multi_select($mh, 1.0);
        } while ($running > 0);

        // Recolectar cuáles están Saldados
        $saldados = [];
        foreach ($handles as $idx => $ch) {
            $body = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            if ($body) {
                $data   = json_decode($body, true);
                $status = $data['estadoCuenta']['statusCredito'] ?? '';
                if ($status === 'Saldado') {
                    $saldados[$idsPorIdx[$idx]] = true;
                }
            }
        }
        curl_multi_close($mh);

        if (!empty($saldados)) {
            $rows = array_values(array_filter($rows, function ($row) use ($saldados) {
                return !isset($saldados[(int) ($row['id_credito'] ?? 0)]);
            }));
        }
    }

    public static function getHistorial(?array $celulas = null): array
    {
        try {
            $db     = new Database();
            $params = [];
            $celulaWhere = self::_celulaWhere($celulas, 'ccs', $params);
            $where  = $celulaWhere !== '' ? ('WHERE 1=1' . $celulaWhere) : '';
            $rows = $db->queryAll(
                "SELECT ccs.id, ccs.id_credito, ccs.nombre_cliente, ccs.estatus,
                        ccs.usuario_alta, ccs.fecha_alta,
                        ccs.usuario_actualizacion, ccs.fecha_actualizacion,
                        ccs.fecha_envio_cartera, ccs.email_destino_cartera, ccs.id_celula
                 FROM cierre_credito_seguimiento ccs
                 {$where}
                 ORDER BY COALESCE(ccs.fecha_envio_cartera, ccs.fecha_actualizacion, ccs.fecha_alta) DESC
                 LIMIT 300",
                $params ?: null
            );
            return self::resultado(true, 'Historial de movimientos.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener el historial.', [], $e->getMessage());
        }
    }
}
