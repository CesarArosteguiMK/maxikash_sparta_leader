<?php

namespace Models;

use Core\Model;
use Core\Database;

class CierreCredito extends Model
{
    // ─────────────────────────────────────────────
    // LISTADOS POR ESTATUS
    // ─────────────────────────────────────────────

    /**
     * Devuelve todos los registros con estatus 'en_proceso'.
     */
    public static function getEnProceso(): array
    {
        try {
            $db = new Database();

            // 1. Registros pendientes de validación
            $rows = $db->queryAll(
                "SELECT id, id_credito, nombre_cliente, estatus,
                        fecha_alta, usuario_alta,
                        fecha_actualizacion, usuario_actualizacion,
                        fecha_envio_cartera
                 FROM cierre_credito_seguimiento
                 WHERE estatus = 'en_proceso'
                 ORDER BY fecha_alta DESC"
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
                        cc.adeudo_total_original, cc.numero_semanas
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
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

                $idConv = $row['id_convenio'];
                $comp   = $idConv ? ($comprobantesMap[$idConv] ?? ['con' => 0, 'total' => 0])
                                  : ['con' => 0, 'total' => 0];
                $row['comprobantes_subidos'] = $comp['con'];
                $row['comprobantes_total']   = $comp['total'];
            }
            unset($row);

            return self::resultado(true, 'Registros en proceso.', $rows);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener registros en proceso.', [], $e->getMessage());
        }
    }

    /**
     * Convenios con estatus 'saldado' — fuente principal de Cierre de Crédito.
     * Trae datos del convenio, producto, progreso de amortización y despacho asignado.
     */
    public static function getEnviadoFinalizado(): array
    {
        try {
            $db   = new Database();
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
                    (SELECT COUNT(*)
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id
                       AND a.estatus_pago = 'pagado')  AS cuotas_pagadas,
                    (SELECT COUNT(*)
                     FROM convenio_cliente_amortizacion a
                     WHERE a.id_convenio_cliente = cc.id
                       AND a.comprobante_path IS NOT NULL
                       AND a.comprobante_path != '')   AS comprobantes_subidos,
                    (SELECT TRIM(CONCAT_WS(' ',
                            per.nombres, per.segundo_nombre,
                            per.apellidop, per.apellidom))
                     FROM asigna_creditos_despacho acd
                     INNER JOIN despachos d   ON d.id  = acd.id_despacho
                     INNER JOIN persona   per ON per.id = d.id_persona
                     WHERE acd.id_credito = cc.id_credito
                       AND acd.estatus    = '1'
                     ORDER BY acd.fecha_alta DESC
                     LIMIT 1)                          AS nombre_despacho,
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
                     ORDER BY ccs.fecha_actualizacion DESC LIMIT 1) AS fecha_descarte
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.estatus = 'completado'
                   AND NOT EXISTS (
                       SELECT 1 FROM cierre_credito_seguimiento ccs
                       WHERE ccs.id_credito = cc.id_credito
                         AND ccs.estatus IN ('en_proceso', 'enviado_cartera', 'en_cola', 'listo_envio')
                   )
                 ORDER BY cc.fecha_alta DESC"
            );
            return self::resultado(true, 'Convenios saldados.', $rows ?: []);
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

            $db->CRUD(
                "INSERT INTO cierre_credito_seguimiento
                    (id_credito, nombre_cliente, estatus, usuario_alta, usuario_actualizacion)
                 VALUES
                    (:id_credito, :nombre_cliente, :estatus, :usuario_alta, :usuario_actualizacion)",
                [
                    'id_credito'            => (int) $datos['id_credito'],
                    'nombre_cliente'        => $datos['nombre_cliente'],
                    'estatus'               => $datos['estatus'] ?? 'en_proceso',
                    'usuario_alta'          => $datos['usuario_alta'],
                    'usuario_actualizacion' => $datos['usuario_alta'],
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
        $permitidos = ['en_proceso', 'enviado_finalizado', 'enviado_cartera'];
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
                "SELECT id, id_credito, nombre_cliente, estatus
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
                        cc.fecha_acuerdo
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 WHERE cc.id_credito = :id_credito AND cc.estatus = 'completado'
                 ORDER BY cc.fecha_alta DESC
                 LIMIT 1",
                ['id_credito' => (int) $registro['id_credito']]
            );

            // 3. Leer configuración de email
            $configPath = defined('RAIZ') ? (RAIZ . '/config/config.ini') : (__DIR__ . '/../../config/config.ini');
            $ini        = is_file($configPath) ? @parse_ini_file($configPath, true) : [];
            $mailCfg    = $ini['mail'] ?? [];

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

                    // Adjuntar PDF del convenio si existe
                    $adjuntos = [];
                    if ($convenio && !empty($convenio['pdf_adjunto'])) {
                        $pdfPath = defined('RAIZ')
                            ? (dirname(RAIZ) . '/public/uploads/convenios/' . basename($convenio['pdf_adjunto']))
                            : (__DIR__ . '/../../public/uploads/convenios/' . basename($convenio['pdf_adjunto']));
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
                            $uploadsBase = defined('RAIZ')
                                ? (dirname(RAIZ) . '/public/uploads/comprobantes/')
                                : (__DIR__ . '/../../public/uploads/comprobantes/');
                            foreach ($compRows as $cr) {
                                $compPath = $uploadsBase . basename($cr['comprobante_path']);
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

                        // Guardar log exitoso (opcional, comentar si genera ruido)
                        $logPath = defined('RAIZ') ? (RAIZ . '/storage/logs/smtp_cierre_credito.log')
                                                    : (__DIR__ . '/../../storage/logs/smtp_cierre_credito.log');
                        @file_put_contents($logPath, date('Y-m-d H:i:s') . " [OK] Credito#{$idCredito}\n" . $smtpDebugLog . "\n", FILE_APPEND);

                    } catch (\Throwable $mailEx) {
                        $emailError = $mailEx->getMessage();

                        // Escribir log completo con la conversación SMTP
                        $logPath = defined('RAIZ') ? (RAIZ . '/storage/logs/smtp_cierre_credito.log')
                                                    : (__DIR__ . '/../../storage/logs/smtp_cierre_credito.log');
                        $logDir = dirname($logPath);
                        if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
                        @file_put_contents(
                            $logPath,
                            date('Y-m-d H:i:s') . " [ERROR] Credito#{$idCredito} — {$emailError}\n"
                            . "From: {$smtpUser}  To: {$mailCartera}\n"
                            . $smtpDebugLog . "\n",
                            FILE_APPEND
                        );

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
    public static function getAllConvenios(): array
    {
        try {
            $db   = new Database();
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
                       AND a.comprobante_path != '')                          AS comprobantes_subidos
                 FROM convenio_cliente cc
                 INNER JOIN producto_convenio pc ON pc.id = cc.id_producto_convenio
                 ORDER BY cc.fecha_alta DESC"
            );
            return self::resultado(true, 'Convenios.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener convenios.', [], $e->getMessage());
        }
    }

    /**
     * Devuelve encabezado del convenio + tabla de amortización completa
     * para un convenio_cliente.id dado (acceso directo, sin cierre_credito_seguimiento).
     *
     * @param int $idConvenio  PK de convenio_cliente
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
                        cc.fecha_ultimo_pago,   cc.usuario_alta
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

    public static function getHistorial(): array
    {
        try {
            $db   = new Database();
            $rows = $db->queryAll(
                "SELECT id, id_credito, nombre_cliente, estatus,
                        usuario_alta, fecha_alta,
                        usuario_actualizacion, fecha_actualizacion,
                        fecha_envio_cartera, email_destino_cartera
                 FROM cierre_credito_seguimiento
                 ORDER BY COALESCE(fecha_envio_cartera, fecha_actualizacion, fecha_alta) DESC
                 LIMIT 300"
            );
            return self::resultado(true, 'Historial de movimientos.', $rows ?: []);
        } catch (\Exception $e) {
            return self::resultado(false, 'Error al obtener el historial.', [], $e->getMessage());
        }
    }
}
