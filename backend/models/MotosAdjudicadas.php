<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseLegacy;
use Models\Adjudicacion as AdjudicacionModel;

class MotosAdjudicadas extends Model
{
    private $db;

    /** @var null|bool null = a?n no comprobado, true = existen val_atn/comentario_atn */
    private static $adjEvidenciaAtnColumnas = null;

    /** @var null|bool columna adj_operacion.atencion_envio_validado */
    private static $adjOperacionEnvioAtencionCol = null;

    /** @var null|bool columna adj_operacion.fecha_llegada_almacen (requiere esquema actualizado en BD) */
    private static $adjOperacionFechaLlegadaAlmacenCol = null;

    /** @var null|bool columnas recepcion_*_estado (requiere esquema actualizado en BD) */
    private static $adjOperacionRecepcionDocEstadoCol = null;

    /** @var null|bool tabla de caché para resumen S2 en modal de dictámenes */
    private static $cacheResumenS2DictamenTablaOk = null;

    /** M?ximo de consultas REPUVE nuevas (POST a Nubarium) por usuario y d?a natural CDMX. */
    private const REPUVE_CONSULTAS_MAX_DIA = 5;

    /** Campa?a Legacy: MOTOS ADJUDICADAS AUTORIZADAS. */
    private const LEGACY_CAMPAIGN_MOTOS_ADJ_AUTORIZADAS = 427;

    /** Slots de evidencias fotogr?ficas (Mis adjudicaciones); debe coincidir con la vista y el resumen SQL. */
    private const MADJ_SLOTS_EVIDENCIA_MEDIA = [
        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
        'fis_vin',
        'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
        'fis_tacometro',
        'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba',
    ];

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * true si en adj_evidencia existen val_atn y comentario_atn (migración aplicada).
     * Prueba con SELECT directo: information_schema a veces no est? permitido para el usuario MySQL.
     */
    private function adjEvidenciaTieneColumnasAtn(): bool
    {
        if (self::$adjEvidenciaAtnColumnas !== null) {
            return self::$adjEvidenciaAtnColumnas;
        }
        try {
            $this->db->queryOne('SELECT val_atn, comentario_atn FROM adj_evidencia LIMIT 1');
            self::$adjEvidenciaAtnColumnas = true;
        } catch (\Throwable $e) {
            self::$adjEvidenciaAtnColumnas = false;
        }
        return self::$adjEvidenciaAtnColumnas;
    }

    /** Etapa ?Correcciones? en evidencias (pipeline). */
    private function esEstatusRevisionRecuperaciones(string $estatus): bool
    {
        $e = trim($estatus);

        return $e === 'Revisión Recuperaciones';
    }

    /**
     * Ya envi? evidencias validadas desde Atenci?n (flag o bit?cora).
     * No debe pasarse a Correcciones solo por tener val_atn rechazados en pantalla.
     */
    private function operacionTieneEnvioAtencionMarcado(int $idOperacion): bool
    {
        if ($this->adjOperacionTieneColumnaEnvioAtencion()) {
            $f = $this->db->queryOne(
                'SELECT atencion_envio_validado AS v FROM adj_operacion WHERE id = :id LIMIT 1',
                ['id' => $idOperacion]
            );
            if ((int) ($f['v'] ?? 0) === 1) {
                return true;
            }
        }
        $b = $this->db->queryOne(
            "SELECT 1 AS ok
             FROM adj_bitacora
             WHERE id_operacion = :id
               AND accion LIKE :a
             LIMIT 1",
            ['id' => $idOperacion, 'a' => '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%']
        );

        return (bool) ($b && (int) ($b['ok'] ?? 0) === 1);
    }

    /**
     * true si existe adj_operacion.atencion_envio_validado (requiere esquema actualizado en BD).
     * Se prueba con SELECT directo: information_schema a veces no est? permitido para el usuario MySQL.
     */
    public function adjOperacionTieneColumnaEnvioAtencion(): bool
    {
        if (self::$adjOperacionEnvioAtencionCol !== null) {
            return self::$adjOperacionEnvioAtencionCol;
        }
        try {
            $this->db->queryOne('SELECT atencion_envio_validado FROM adj_operacion LIMIT 1');
            self::$adjOperacionEnvioAtencionCol = true;
        } catch (\Throwable $e) {
            self::$adjOperacionEnvioAtencionCol = false;
        }
        return self::$adjOperacionEnvioAtencionCol;
    }

    /**
     * true si existe adj_operacion.fecha_llegada_almacen.
     */
    public function adjOperacionTieneColumnaFechaLlegadaAlmacen(): bool
    {
        if (self::$adjOperacionFechaLlegadaAlmacenCol !== null) {
            return self::$adjOperacionFechaLlegadaAlmacenCol;
        }
        try {
            $this->db->queryOne('SELECT fecha_llegada_almacen FROM adj_operacion LIMIT 1');
            self::$adjOperacionFechaLlegadaAlmacenCol = true;
        } catch (\Throwable $e) {
            self::$adjOperacionFechaLlegadaAlmacenCol = false;
        }

        return self::$adjOperacionFechaLlegadaAlmacenCol;
    }

    public function adjOperacionTieneColumnasRecepcionDocEstado(): bool
    {
        if (self::$adjOperacionRecepcionDocEstadoCol !== null) {
            return self::$adjOperacionRecepcionDocEstadoCol;
        }
        try {
            $this->db->queryOne('SELECT recepcion_dacion_estado, recepcion_tarjeta_estado FROM adj_operacion LIMIT 1');
            self::$adjOperacionRecepcionDocEstadoCol = true;
        } catch (\Throwable $e) {
            self::$adjOperacionRecepcionDocEstadoCol = false;
        }

        return self::$adjOperacionRecepcionDocEstadoCol;
    }

    /**
     * Guarda estado de documento en recepci?n (sin archivo): pendiente / no recibido.
     *
     * @param  string  $documento  dacion | tarjeta
     * @param  string  $estado     pending | missing
     * @return array{success:bool, message?:string}
     */
    public function guardarRecepcionEstadoDocumento(int $idOperacion, string $documento, string $estado, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operaci?n inv?lida.'];
        }
        $documento = strtolower(trim($documento));
        $estado    = strtolower(trim($estado));
        if (!in_array($documento, ['dacion', 'tarjeta'], true)) {
            return ['success' => false, 'message' => 'Documento no reconocido.'];
        }
        if ($documento === 'dacion' && !in_array($estado, ['pending', 'missing'], true)) {
            return ['success' => false, 'message' => 'Estado no v?lido para contrato de daci?n.'];
        }
        if ($documento === 'tarjeta' && $estado !== 'missing') {
            return ['success' => false, 'message' => 'Estado no v?lido para tarjeta de circulaci?n.'];
        }
        if (!$this->adjOperacionTieneColumnasRecepcionDocEstado()) {
            return [
                'success' => false,
                'message' => 'Falta migración de base de datos: deben existir recepcion_dacion_estado y recepcion_tarjeta_estado en adj_operacion.',
            ];
        }
        $row = $this->db->queryOne(
            'SELECT id, estatus FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$row) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        if (trim((string) ($row['estatus'] ?? '')) !== 'Recepción') {
            return ['success' => false, 'message' => 'Solo aplica en etapa Recepción.'];
        }
        $col = $documento === 'dacion' ? 'recepcion_dacion_estado' : 'recepcion_tarjeta_estado';
        $ahora = $this->fechaHoraCdmx();
        $this->db->CRUD(
            "UPDATE adj_operacion SET `{$col}` = :est, fecha_actualizacion = :f WHERE id = :id",
            ['est' => $estado, 'f' => $ahora, 'id' => $idOperacion]
        );
        $label = $documento === 'dacion' ? 'CONTRATO DACIÓN' : 'TARJETA CIRCULACIÓN';
        $this->registrarBitacora(
            $idOperacion,
            "RECEPCIÓN DOC {$label}: " . strtoupper($estado),
            $idUsuario,
            $nombreUsuario,
            $ahora
        );

        return ['success' => true];
    }

    /** @var null|bool columnas recepcion_confirmada_at / ubicaci?n */
    private static $adjOperacionRecepcionConfirmCol = null;

    public function adjOperacionTieneColumnasRecepcionConfirmacion(): bool
    {
        if (self::$adjOperacionRecepcionConfirmCol !== null) {
            return self::$adjOperacionRecepcionConfirmCol;
        }
        try {
            $this->db->queryOne('SELECT recepcion_confirmada_at FROM adj_operacion LIMIT 1');
            self::$adjOperacionRecepcionConfirmCol = true;
        } catch (\Throwable $e) {
            self::$adjOperacionRecepcionConfirmCol = false;
        }

        return self::$adjOperacionRecepcionConfirmCol;
    }

    /**
     * Confirma recepci?n en almac?n (una vez): ubicaci?n, observaciones y validaciones m?nimas.
     *
     * @return array{success:bool, message?:string, confirmada_at_fmt?:string, ya_confirmada?:bool}
     */
    public function confirmarRecepcionAlmacen(int $idOperacion, string $ubicacion, string $observaciones, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operaci?n inv?lida.'];
        }
        if (!$this->adjOperacionTieneColumnasRecepcionConfirmacion()) {
            return [
                'success' => false,
                'message' => 'Falta migración de base de datos: deben existir las columnas de confirmación de recepción en adj_operacion.',
            ];
        }
        if (!$this->adjOperacionTieneColumnasRecepcionDocEstado()) {
            return [
                'success' => false,
                'message' => 'Falta migración de documentos: deben existir recepcion_dacion_estado y recepcion_tarjeta_estado en adj_operacion.',
            ];
        }
        $ubicacion     = trim($ubicacion);
        $observaciones = trim($observaciones);
        if ($ubicacion === '') {
            return ['success' => false, 'message' => 'Indique la ubicaci?n en almac?n.'];
        }
        $op = $this->db->queryOne(
            'SELECT id, estatus, fecha_llegada_almacen, recepcion_confirmada_at,
                    recepcion_dacion_estado, recepcion_tarjeta_estado
             FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        if (trim((string) ($op['estatus'] ?? '')) !== 'Recepción') {
            return ['success' => false, 'message' => 'Solo se confirma recepci?n en etapa Recepción.'];
        }
        if (!$this->adjOperacionTieneColumnaFechaLlegadaAlmacen() || empty($op['fecha_llegada_almacen'])) {
            return ['success' => false, 'message' => 'Debe registrar primero la llegada f?sica a almac?n.'];
        }
        if (!empty($op['recepcion_confirmada_at'])) {
            $fmt = $this->db->queryOne(
                "SELECT DATE_FORMAT(recepcion_confirmada_at, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
                ['id' => $idOperacion]
            );

            return [
                'success'          => false,
                'message'          => 'La recepci?n ya fue confirmada y no puede modificarse.',
                'ya_confirmada'    => true,
                'confirmada_at_fmt' => (string) ($fmt['f'] ?? $op['recepcion_confirmada_at']),
            ];
        }

        $urls = $this->db->queryAll(
            "SELECT slot, TRIM(url) AS url FROM adj_evidencia
             WHERE id_operacion = :id AND slot IN ('doc_dacion_rcpt','doc_tarjeta_rcpt','doc_firma_rcpt') AND url IS NOT NULL AND TRIM(url) <> ''",
            ['id' => $idOperacion]
        ) ?: [];
        $by = [];
        foreach ($urls as $u) {
            $by[(string) ($u['slot'] ?? '')] = (string) ($u['url'] ?? '');
        }
        $urlD = $by['doc_dacion_rcpt'] ?? '';
        $urlT = $by['doc_tarjeta_rcpt'] ?? '';
        $urlF = $by['doc_firma_rcpt'] ?? '';

        $dEst = strtolower(trim((string) ($op['recepcion_dacion_estado'] ?? '')));
        $tEst = strtolower(trim((string) ($op['recepcion_tarjeta_estado'] ?? '')));

        $dacionOk = in_array($dEst, ['pending', 'missing'], true) || $urlD !== '';
        if (!$dacionOk) {
            return ['success' => false, 'message' => 'Indique el estado del contrato de daci?n o suba el escaneo firmado.'];
        }
        $tarjOk = $tEst === 'missing' || $urlT !== '';
        if (!$tarjOk) {
            return ['success' => false, 'message' => 'Indique si no recibe la tarjeta o suba la foto de la tarjeta de circulaci?n.'];
        }
        if ($urlF === '') {
            return ['success' => false, 'message' => 'Debe subir la imagen de firma de recepci?n del agente de almac?n.'];
        }

        $ahora = $this->fechaHoraCdmx();
        $n = (int) $this->db->CRUD(
            'UPDATE adj_operacion SET
                recepcion_ubicacion = :u,
                recepcion_observaciones = :o,
                recepcion_confirmada_at = :c,
                fecha_actualizacion = :c2
             WHERE id = :id AND recepcion_confirmada_at IS NULL',
            [
                'u'  => $ubicacion,
                'o'  => $observaciones !== '' ? $observaciones : null,
                'c'  => $ahora,
                'c2' => $ahora,
                'id' => $idOperacion,
            ]
        );
        if ($n <= 0) {
            $fmt2 = $this->db->queryOne(
                "SELECT DATE_FORMAT(recepcion_confirmada_at, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
                ['id' => $idOperacion]
            );

            return [
                'success'            => false,
                'message'            => 'La recepci?n ya fue confirmada y no puede modificarse.',
                'ya_confirmada'      => true,
                'confirmada_at_fmt' => (string) ($fmt2['f'] ?? ''),
            ];
        }

        $slotsIn = "'vista_trs','vista_front','lado_izq','lado_der','tablero','vin','danos_vis','vid_gen'";
        $this->db->CRUD(
            "UPDATE adj_evidencia
             SET estatus = 'cierre_almacen'
             WHERE id_operacion = :id
               AND slot IN ($slotsIn)
               AND estatus = 'pendiente_almacen'",
            ['id' => $idOperacion]
        );

        $this->registrarBitacora(
            $idOperacion,
            'RECEPCIÓN EN ALMACÉN CONFIRMADA: ' . $ubicacion,
            $idUsuario,
            $nombreUsuario,
            $ahora
        );

        /* Flujo actual: tras Recepción sigue la etapa Retenciones (atenci?n / cierre de llamadas). */
        $this->cambiarEstatus($idOperacion, 'Retenciones', $idUsuario, $nombreUsuario);

        $fmt = $this->db->queryOne(
            "SELECT DATE_FORMAT(recepcion_confirmada_at, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
            ['id' => $idOperacion]
        );

        return [
            'success'            => true,
            'confirmada_at_fmt'  => (string) ($fmt['f'] ?? $ahora),
            'estatus_nuevo'      => 'Retenciones',
        ];
    }

    /**
     * Saldo capital y adeudo total seg?n API S2 (estado de cuenta), misma fuente que EstadoCuenta.
     *
     * @return array{success:bool, message?:string, saldo_capital?:float, adeudo_total?:float, fecha_corte?:string}
     */
    public function obtenerResumenFinancieroEstadoCuentaS2(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de cr?dito inv?lido.'];
        }
        try {
            $ctrl = new \Controllers\EstadoCuenta();
            $res  = $ctrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'), 20);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error al consultar estado de cuenta S2.'];
        }
        if (empty($res['ok']) || !is_array($res['data'])) {
            return ['success' => false, 'message' => (string) ($res['error'] ?? 'No se pudo obtener estado de cuenta.')];
        }
        $ds = $res['data']['datosSaldos'] ?? [];
        if (!is_array($ds)) {
            $ds = [];
        }
        $vig = (float) ($ds['saldoTotalVigente'] ?? $ds['CapitalPendientePago'] ?? $ds['capitalPendientePago'] ?? 0);
        $ven = (float) ($ds['saldoTotalVencido'] ?? 0);
        $adeudo = (float) ($ds['adeudoTotal'] ?? $ds['montoTotalAdeudado'] ?? 0);
        if ($adeudo <= 0) {
            $adeudo = $vig + $ven;
        }
        $saldoCapital = (float) ($ds['saldoCapital'] ?? $ds['saldoTotalCapital'] ?? 0);
        if ($saldoCapital <= 0) {
            $saldoCapital = $vig > 0 ? $vig : ($vig + $ven);
        }

        return [
            'success'        => true,
            'saldo_capital'  => round($saldoCapital, 2),
            'adeudo_total'   => round($adeudo, 2),
            'fecha_corte'    => date('Y-m-d'),
        ];
    }

    /**
     * Resumen financiero S2 (estado de cuenta) para el modal Lista Dictámenes — Motos adjudicadas.
     *
     * @return array{
     *   success:bool,
     *   message?:string,
     *   monto_otorgado?:float|null,
     *   cuotas_contratadas?:int|null,
     *   cuotas_pagadas?:int|null,
     *   total_pagado_cliente?:float|null,
     *   ultimo_efectivo_fecha?:string|null,
     *   ultimo_efectivo_monto?:float|null,
     *   ultimo_efectivo_es_estricto?:bool
     * }
     */
    public function obtenerResumenS2ModalDictamen(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de crédito inválido.'];
        }

        $cache = $this->obtenerCacheResumenS2ModalDictamen($idCredito);
        if ($cache !== null) {
            $hayPayload = !empty($cache['_hay_payload_s2']);
            unset($cache['_hay_payload_s2']);
            if (!$this->cacheResumenS2ModalRequiereRefrescoS2($cache, $hayPayload)) {
                return $cache + ['success' => true, 'from_cache' => true];
            }
        }

        try {
            $ctrl = new \Controllers\EstadoCuenta();
            $res  = $ctrl->api___SPARTA_SECRET_REDACTED__($idCredito, date('Y-m-d'), 25);
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error al consultar estado de cuenta S2.'];
        }
        if (empty($res['ok']) || !is_array($res['data'])) {
            return ['success' => false, 'message' => (string) ($res['error'] ?? 'No se pudo obtener estado de cuenta.')];
        }
        $ec = $res['data'];
        $ds = is_array($ec['datosSaldos'] ?? null) ? $ec['datosSaldos'] : [];

        $montoOtorgado = (float) ($ec['montoOtorgado'] ?? 0);
        if ($montoOtorgado <= 0.0) {
            $montoOtorgado = (float) ($ds['montoOtorgado'] ?? $ds['Monto_otorgado'] ?? 0);
        }

        $cuotasPagadas = null;
        foreach (['cuotasPagadas', 'Num_cuotas_pagadas', 'num_cuotas_pagadas', 'Cuotas_pagadas'] as $k) {
            if (isset($ds[$k]) && $ds[$k] !== '' && $ds[$k] !== null && is_numeric($ds[$k])) {
                $cuotasPagadas = (int) $ds[$k];
                break;
            }
        }

        $totalPagado = null;
        foreach (['Abonos_total', 'abonos_total'] as $k) {
            if (isset($ds[$k]) && is_numeric($ds[$k])) {
                $totalPagado = round((float) $ds[$k], 2);
                break;
            }
        }
        if ($totalPagado === null) {
            $pagosArr = is_array($ec['datosPagos'] ?? null) ? $ec['datosPagos'] : [];
            $sum      = 0.0;
            foreach ($pagosArr as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $sum += (float) ($p['montoPago'] ?? $p['monto'] ?? 0);
            }
            $totalPagado = round($sum, 2);
        }

        $ult                 = $this->extraerUltimoAbonoEfectivoDesdeEstadoCuenta($ec);
        $cuotasContratadas   = $this->extraerCuotasContratadasDesdeEstadoCuenta($ec);
        $salida = [
            'success'                       => true,
            'monto_otorgado'                => $montoOtorgado > 0 ? round($montoOtorgado, 2) : null,
            'cuotas_contratadas'            => $cuotasContratadas,
            'cuotas_pagadas'                => $cuotasPagadas,
            'total_pagado_cliente'          => $totalPagado,
            'ultimo_efectivo_fecha'         => $ult['fecha'],
            'ultimo_efectivo_monto'         => $ult['monto'],
            'ultimo_efectivo_es_estricto'   => $ult['estricto'],
            'from_cache'                    => false,
        ];
        $this->guardarCacheResumenS2ModalDictamen($idCredito, $salida, $ec);

        return $salida;
    }

    /**
     * Si la fila de caché tiene huecos en datos que debe proveer S2, se fuerza nueva consulta y UPDATE en BD.
     * Si ya existe `payload_json` de un estado de cuenta guardado, no se reconsulta la API por columnas
     * derivadas nulas (p. ej. cuotas contratadas que el S2 no envía en algunos créditos).
     *
     * @param  array<string,mixed>  $cache
     */
    private function cacheResumenS2ModalRequiereRefrescoS2(array $cache, bool $hayPayloadSnapshot = false): bool
    {
        if ($hayPayloadSnapshot) {
            return false;
        }
        if (($cache['cuotas_contratadas'] ?? null) === null) {
            return true;
        }
        if (($cache['monto_otorgado'] ?? null) === null) {
            return true;
        }
        if (($cache['cuotas_pagadas'] ?? null) === null) {
            return true;
        }
        if (($cache['total_pagado_cliente'] ?? null) === null) {
            return true;
        }

        return false;
    }

    /**
     * Cuotas contratadas / plazo desde respuesta estado de cuenta S2 (datosSaldos, datosCliente, raíz).
     */
    private function extraerCuotasContratadasDesdeEstadoCuenta(array $ec): ?int
    {
        $ds  = is_array($ec['datosSaldos'] ?? null) ? $ec['datosSaldos'] : [];
        $dc  = is_array($ec['datosCliente'] ?? null) ? $ec['datosCliente'] : [];
        $dcr = is_array($ec['datosCredito'] ?? null) ? $ec['datosCredito'] : [];
        $keys = [
            'cuotasContratadas', 'Cuotas_contratadas', 'cuotas_contratadas',
            'Numero_amortizaciones', 'numeroAmortizaciones', 'numero_amortizaciones',
            'Numero_cuotas', 'numero_cuotas', 'Num_cuotas',
            'plazoTotal', 'Plazo_total', 'numAmortizaciones', 'totalCuotas',
        ];
        foreach ($keys as $k) {
            foreach ([$ec, $ds, $dc, $dcr] as $src) {
                if (!is_array($src) || !array_key_exists($k, $src)) {
                    continue;
                }
                $v = $src[$k];
                if ($v === null || $v === '') {
                    continue;
                }
                if (is_numeric($v)) {
                    $n = (int) $v;
                    if ($n > 0) {
                        return $n;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Crea (si falta) la tabla de caché del resumen S2 del modal de dictámenes.
     */
    private function asegurarTablaCacheResumenS2ModalDictamen(): bool
    {
        if (self::$cacheResumenS2DictamenTablaOk !== null) {
            return self::$cacheResumenS2DictamenTablaOk;
        }
        try {
            $this->db->CRUD(
                "CREATE TABLE IF NOT EXISTS adj_s2_cache_dictamen (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    id_credito INT NOT NULL,
                    monto_otorgado DECIMAL(14,2) NULL,
                    cuotas_pagadas INT NULL,
                    total_pagado_cliente DECIMAL(14,2) NULL,
                    ultimo_efectivo_fecha VARCHAR(40) NULL,
                    ultimo_efectivo_monto DECIMAL(14,2) NULL,
                    ultimo_efectivo_es_estricto TINYINT(1) NOT NULL DEFAULT 0,
                    payload_json LONGTEXT NULL,
                    consultado_at DATETIME NOT NULL,
                    actualizado_at DATETIME NOT NULL,
                    UNIQUE KEY ux_adj_s2_cache_dictamen_credito (id_credito),
                    KEY idx_adj_s2_cache_dictamen_actualizado (actualizado_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"
            );
            // Extensiones para caché de nombre del cliente en la misma tabla.
            try {
                $this->db->CRUD("ALTER TABLE adj_s2_cache_dictamen ADD COLUMN nombre_cliente VARCHAR(255) NULL");
            } catch (\Throwable $e) {}
            try {
                $this->db->CRUD("ALTER TABLE adj_s2_cache_dictamen ADD COLUMN nombre_fuente VARCHAR(50) NULL");
            } catch (\Throwable $e) {}
            try {
                $this->db->CRUD("ALTER TABLE adj_s2_cache_dictamen ADD COLUMN nombre_actualizado_at DATETIME NULL");
            } catch (\Throwable $e) {}
            try {
                $this->db->CRUD('ALTER TABLE adj_s2_cache_dictamen ADD COLUMN ma_seg_comentarios TEXT NULL');
            } catch (\Throwable $e) {}
            try {
                $this->db->CRUD('ALTER TABLE adj_s2_cache_dictamen ADD COLUMN ma_seg_cuotas_contratadas INT NULL');
            } catch (\Throwable $e) {}
            try {
                $this->db->CRUD('ALTER TABLE adj_s2_cache_dictamen DROP COLUMN ma_seg_area');
            } catch (\Throwable $e) {}
            try {
                $this->db->CRUD('ALTER TABLE adj_s2_cache_dictamen ADD COLUMN ma_seg_aplica TINYINT(1) NULL');
            } catch (\Throwable $e) {}
            try {
                $this->db->CRUD('ALTER TABLE adj_s2_cache_dictamen ADD COLUMN ma_seg_actualizado_at DATETIME NULL');
            } catch (\Throwable $e) {}
            self::$cacheResumenS2DictamenTablaOk = true;
        } catch (\Throwable $e) {
            self::$cacheResumenS2DictamenTablaOk = false;
        }
        return self::$cacheResumenS2DictamenTablaOk;
    }

    /**
     * Seguimiento del modal Lista dictámenes (por id_credito en adj_s2_cache_dictamen).
     * Solo comentarios + aplica recolección (+ ma_seg_actualizado_at). Cuotas contratadas S2 van en ma_seg_cuotas_contratadas.
     *
     * @param  int[]  $idsCredito
     * @return array<int, array{comentarios: string, aplica: int|null}>
     */
    public function obtenerSeguimientoMaDictamenPorCreditos(array $idsCredito): array
    {
        $idsCredito = array_values(array_unique(array_filter(array_map('intval', $idsCredito), static fn ($v) => $v > 0)));
        if ($idsCredito === [] || !$this->asegurarTablaCacheResumenS2ModalDictamen()) {
            return [];
        }
        $ph     = [];
        $params = [];
        foreach ($idsCredito as $i => $id) {
            $k          = 'c'.$i;
            $ph[]       = ':'.$k;
            $params[$k] = $id;
        }
        try {
            $rows = $this->db->queryAll(
                'SELECT id_credito, ma_seg_comentarios, ma_seg_aplica
                 FROM adj_s2_cache_dictamen
                 WHERE id_credito IN ('.implode(',', $ph).')',
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $idc = (int) ($r['id_credito'] ?? 0);
            if ($idc <= 0) {
                continue;
            }
            $ap     = $r['ma_seg_aplica'] ?? null;
            $aplica = null;
            if ($ap !== null && $ap !== '') {
                $aplica = ((int) $ap) === 1 ? 1 : 0;
            }
            $out[$idc] = [
                'comentarios' => (string) ($r['ma_seg_comentarios'] ?? ''),
                'aplica'      => $aplica,
            ];
        }

        return $out;
    }

    /**
     * @param int[] $idsCredito
     * @return array<int, bool> [id_credito => true] si tiene asignación activa.
     */
    private function obtenerMapaAsignacionActivaPorCreditos(array $idsCredito): array
    {
        $idsCredito = array_values(array_unique(array_filter(array_map('intval', $idsCredito), static fn($v) => $v > 0)));
        if ($idsCredito === []) {
            return [];
        }
        $ph     = [];
        $params = [];
        foreach ($idsCredito as $i => $id) {
            $k          = 'ac' . $i;
            $ph[]       = ':' . $k;
            $params[$k] = $id;
        }
        try {
            $rows = $this->db->queryAll(
                'SELECT id_credito
                 FROM asigna_creditos_adjudicacion
                 WHERE estatus = \'1\'
                   AND id_credito IN (' . implode(',', $ph) . ')',
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
        $out = [];
        foreach ($rows as $r) {
            $idc = (int) ($r['id_credito'] ?? 0);
            if ($idc > 0) {
                $out[$idc] = true;
            }
        }

        return $out;
    }

    /**
     * @return array{success: bool, message?: string}
     */
    public function guardarSeguimientoMaDictamen(int $idCredito, string $comentarios, ?int $aplica): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'Crédito inválido.'];
        }
        $comentarios = trim($comentarios);
        if ($comentarios === '') {
            return ['success' => false, 'message' => 'El comentario es obligatorio.'];
        }
        if ($aplica !== 0 && $aplica !== 1) {
            return ['success' => false, 'message' => 'Seleccione sí o no en el desplegable.'];
        }
        if (!$this->asegurarTablaCacheResumenS2ModalDictamen()) {
            return ['success' => false, 'message' => 'No fue posible preparar el almacenamiento.'];
        }
        $ahora = date('Y-m-d H:i:s');
        try {
            $this->db->CRUD(
                'INSERT INTO adj_s2_cache_dictamen (
                    id_credito,
                    ma_seg_comentarios,
                    ma_seg_aplica,
                    ma_seg_actualizado_at,
                    consultado_at,
                    actualizado_at,
                    ultimo_efectivo_es_estricto
                ) VALUES (
                    :id_credito,
                    :com,
                    :aplica,
                    :seg_a,
                    :ahora,
                    :ahora2,
                    0
                )
                ON DUPLICATE KEY UPDATE
                    ma_seg_comentarios = VALUES(ma_seg_comentarios),
                    ma_seg_aplica = VALUES(ma_seg_aplica),
                    ma_seg_actualizado_at = VALUES(ma_seg_actualizado_at),
                    actualizado_at = VALUES(actualizado_at)',
                [
                    'id_credito' => $idCredito,
                    'com'        => $comentarios,
                    'aplica'     => $aplica,
                    'seg_a'      => $ahora,
                    'ahora'      => $ahora,
                    'ahora2'     => $ahora,
                ]
            );
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error al guardar.'];
        }

        return ['success' => true];
    }

    /**
     * Crea el task Legacy para la campana MOTOS ADJUDICADAS AUTORIZADAS.
     *
     * @return array{success:bool, message:string, task_id?:int, duplicate?:bool}
     */
    public function crearTaskLegacyMotoAutorizada(int $idCredito, int $idPersonaResponsable = 0, array $datosDictamen = []): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'Credito invalido para crear task legacy.'];
        }

        try {
            $legacyDb = new DatabaseLegacy();
            $campaignId = self::LEGACY_CAMPAIGN_MOTOS_ADJ_AUTORIZADAS;

            $dup = $legacyDb->queryOne(
                'SELECT id
                 FROM tasks
                 WHERE campaign_id = :campaign_id
                   AND credit_number = :credit_number
                   AND deleted_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1',
                [
                    'campaign_id'   => $campaignId,
                    'credit_number' => (string) $idCredito,
                ]
            );
            if ($dup && (int) ($dup['id'] ?? 0) > 0) {
                return [
                    'success'   => true,
                    'duplicate' => true,
                    'task_id'   => (int) $dup['id'],
                    'message'   => 'Ya existia task en la campana MOTOS ADJUDICADAS AUTORIZADAS.',
                ];
            }

            $currentUserId = $this->resolverLegacyUserIdPorPersona($idPersonaResponsable);
            if ($currentUserId <= 0) {
                $currentUserId = $this->resolverLegacyUserIdDesdeUltimoDictamenMoto($idCredito);
            }
            if ($currentUserId <= 0) {
                return ['success' => false, 'message' => 'No se encontro usuario Legacy para asignar el task.'];
            }

            $datos = $this->obtenerDatosTaskLegacyMotoAutorizada($idCredito, $datosDictamen);
            $ahora = $this->fechaHoraCdmx();
            $legacyDb->CRUD(
                'INSERT INTO tasks
                    (campaign_id, current_user_id, client_name, credit_number, address, lat, lng,
                     form_data, form_answered, status, deleted_at, created_at, updated_at)
                 VALUES
                    (:campaign_id, :current_user_id, :client_name, :credit_number, :address, :lat, :lng,
                     :form_data, :form_answered, :status, NULL, :created_at, :updated_at)',
                [
                    'campaign_id'     => $campaignId,
                    'current_user_id' => $currentUserId,
                    'client_name'     => $datos['client_name'],
                    'credit_number'   => (string) $idCredito,
                    'address'         => $datos['address'],
                    'lat'             => $datos['lat'],
                    'lng'             => $datos['lng'],
                    'form_data'       => $this->formDataLegacyMotoAutorizada(),
                    'form_answered'   => 0,
                    'status'          => 1,
                    'created_at'      => $ahora,
                    'updated_at'      => $ahora,
                ]
            );

            $row = $legacyDb->queryOne('SELECT LAST_INSERT_ID() AS id');
            $taskId = (int) ($row['id'] ?? 0);

            return [
                'success' => true,
                'task_id' => $taskId,
                'message' => 'Task creado en campana MOTOS ADJUDICADAS AUTORIZADAS.',
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'No se pudo crear el task Legacy: ' . $e->getMessage()];
        }
    }

    private function resolverLegacyUserIdPorPersona(int $idPersona): int
    {
        if ($idPersona <= 0) {
            return 0;
        }
        try {
            $persona = $this->db->queryOne(
                'SELECT TRIM(COALESCE(numero_empleado, \'\')) AS numero_empleado
                 FROM persona
                 WHERE id = :id
                 LIMIT 1',
                ['id' => $idPersona]
            );
            $numeroEmpleado = trim((string) ($persona['numero_empleado'] ?? ''));
            if ($numeroEmpleado === '') {
                return 0;
            }
            $legacyDb = new DatabaseLegacy();
            $user = $legacyDb->queryOne(
                'SELECT id
                 FROM users
                 WHERE TRIM(COALESCE(external_id, \'\')) = :external_id
                   AND deleted_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1',
                ['external_id' => $numeroEmpleado]
            );

            return (int) ($user['id'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function resolverLegacyUserIdDesdeUltimoDictamenMoto(int $idCredito): int
    {
        try {
            $legacyDb = new DatabaseLegacy();
            $row = $legacyDb->queryOne(
                'SELECT COALESCE(t.current_user_id, d.user_id, 0) AS user_id
                 FROM dictums d
                 INNER JOIN tasks t ON t.id = d.task_id
                 WHERE CAST(t.credit_number AS UNSIGNED) = :id_credito
                   AND d.opciondictamen_id = 13
                 ORDER BY d.id DESC
                 LIMIT 1',
                ['id_credito' => $idCredito]
            );

            return (int) ($row['user_id'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * @return array{client_name:string,address:string,lat:?string,lng:?string}
     */
    private function obtenerDatosTaskLegacyMotoAutorizada(int $idCredito, array $datosDictamen = []): array
    {
        $out = [
            'client_name' => 'Credito #' . $idCredito,
            'address'     => '',
            'lat'         => null,
            'lng'         => null,
        ];

        $latDictamen = trim((string) ($datosDictamen['lat'] ?? ''));
        $lngDictamen = trim((string) ($datosDictamen['lng'] ?? ''));
        $lugarAprox  = trim((string) ($datosDictamen['lugar_aprox'] ?? ''));
        if ($latDictamen !== '' && is_numeric($latDictamen)) {
            $out['lat'] = $latDictamen;
        }
        if ($lngDictamen !== '' && is_numeric($lngDictamen)) {
            $out['lng'] = $lngDictamen;
        }
        if ($lugarAprox !== '') {
            $out['address'] = $lugarAprox;
        }

        try {
            $sky = $this->db->queryOne(
                'SELECT
                    TRIM(COALESCE(nombre_completo_cliente, nombre_cliente, \'\')) AS nombre,
                    TRIM(COALESCE(direccion_actual, direccion, direccion_ine, direccion_geo, \'\')) AS direccion,
                    latitud,
                    longitud
                 FROM base_clientes
                 WHERE id_credito = :id
                 ORDER BY fecha_dispositivo DESC, id DESC
                 LIMIT 1',
                ['id' => $idCredito]
            );
            if ($sky) {
                $nom = trim((string) ($sky['nombre'] ?? ''));
                $dir = trim((string) ($sky['direccion'] ?? ''));
                if ($nom !== '') {
                    $out['client_name'] = $nom;
                }
                if ($dir !== '') {
                    $out['address'] = $out['address'] !== '' ? $out['address'] : $dir;
                }
                $lat = trim((string) ($sky['latitud'] ?? ''));
                $lng = trim((string) ($sky['longitud'] ?? ''));
                $out['lat'] = $out['lat'] !== null ? $out['lat'] : ($lat !== '' ? $lat : null);
                $out['lng'] = $out['lng'] !== null ? $out['lng'] : ($lng !== '' ? $lng : null);
            }
        } catch (\Throwable $e) {}

        if ($out['client_name'] === 'Credito #' . $idCredito || $out['address'] === '') {
            try {
                $adjModel = new AdjudicacionModel();
                $api = $adjModel->buscarCreditoPorId($idCredito);
                $c = !empty($api['success']) && is_array($api['credito'] ?? null) ? $api['credito'] : [];
                $nomApi = trim((string) ($c['nombre_cliente'] ?? ''));
                $dirApi = trim((string) ($c['direccion'] ?? ''));
                if ($nomApi !== '' && strcasecmp($nomApi, 'Sin nombre') !== 0) {
                    $out['client_name'] = $nomApi;
                }
                if ($out['address'] === '' && $dirApi !== '') {
                    $out['address'] = $dirApi;
                }
            } catch (\Throwable $e) {}
        }

        return $out;
    }

    private function formDataLegacyMotoAutorizada(): string
    {
        $json = '[{"type":"select","required":false,"label":"¿Tiene llave física?","className":"form-control","name":"tiene_llave_fisica","editable":true,"section":"questions","conditional":false,"uuid":"dfcd6ca6-f1ae-4807-a462-3d9c346f0b16","typeApp":"select","values":[{"label":"Sí","value":"si","selected":true},{"label":"No","value":"no","selected":false}],"value":""},{"type":"select","required":false,"label":"¿Tiene tarjeta de circulación en físico?","className":"form-control","name":"tiene_tarjeta_de_circulacion_en_fisico","editable":true,"section":"questions","conditional":false,"uuid":"5446560b-ac6a-4827-9c6b-cc3c74954a40","typeApp":"select","values":[{"label":"Sí","value":"si","selected":true},{"label":"No","value":"no","selected":false}],"value":""},{"type":"select","required":false,"label":"¿La moto tiene placa física ?","className":"form-control","name":"la_moto_tiene_placa_fisica","editable":true,"section":"questions","conditional":false,"uuid":"b07276c9-cc0c-42ac-b38c-adfefc42913e","typeApp":"select","values":[{"label":"Sí","value":"si","selected":true},{"label":"No","value":"no","selected":false}],"value":""},{"type":"textarea","required":true,"label":"Marca","className":"form-control","name":"marca","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"0d57879b-69c0-41f1-bb7c-cfe1833aa1aa","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Modelo","className":"form-control","name":"modelo","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"7f896bdd-3c9c-40e8-8c13-4b3925b1c8bc","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Año","className":"form-control","name":"ano","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"0743a74d-9c7e-4512-a5ed-9cd8a5ad60b8","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Color","className":"form-control","name":"color","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"5dfd8e41-7468-4ea4-a9dc-293a357f8294","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"No. de Serie (VIN","className":"form-control","name":"no_de_serie_vin","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"c692709e-6f49-434f-bdbb-85d0186098a7","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"No. de Motor","className":"form-control","name":"no_de_motor","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"46d9e3a1-7ee4-4230-836a-a22f7e2524fd","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Placas","className":"form-control","name":"placas","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"b2f01f91-b1fa-4c0a-a0ca-5e22f3233587","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Kilometraje","className":"form-control","name":"kilometraje","subtype":"textarea","editable":true,"section":"questions","conditional":false,"uuid":"2fdcef10-973b-408e-a0f0-46aceef4afab","typeApp":"textarea","value":""},{"type":"select","label":"¿Dónde resguardaras la moto?","uuid":"73eddd54-4de5-4054-babd-5f5766b18703","editable":true,"section":"questions","required":false,"className":"form-control","name":"donde_resguardaras_la_moto","conditional":false,"typeApp":"select","values":[{"label":"CEDIS Maxikash","value":"cedis-__SPARTA_SECRET_REDACTED__","selected":true},{"label":"Centro de acopio","value":"centro-de-acopio","selected":false},{"label":"Agencia ","value":"agencia","selected":false},{"label":"Otro","value":"otro","selected":false}],"value":""},{"type":"textarea","label":"Estado de lugar de resguardo (Ejemplo Ciudad de México, Veracruz, Oaxaca, etc.)","uuid":"3666bb60-bb6b-460e-9bbf-ddea362801af","editable":true,"section":"questions","required":true,"className":"form-control","name":"estado_de_lugar_de_resguardo_ejemplo_ciudad_de_mex","subtype":"textarea","conditional":false,"typeApp":"textarea","value":""},{"type":"textarea","label":"Ciudad / Municipio de lugar de Resguardo","uuid":"68bea6ea-8e99-4588-b20a-dcc2c959e8fb","editable":true,"section":"questions","required":true,"className":"form-control","name":"ciudad_municipio_de_lugar_de_resguardo","subtype":"textarea","conditional":false,"typeApp":"textarea","value":""},{"type":"textarea","label":"Calle y número de lugar de resguardo","uuid":"f7de67b6-27bd-4bf9-9839-0f76c349fb72","editable":true,"section":"questions","required":true,"className":"form-control","name":"calle_y_numero_de_lugar_de_resguardo","subtype":"textarea","conditional":false,"typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Responsable de Resguardo","className":"form-control","name":"responsable_de_resguardo","subtype":"textarea","editable":true,"section":"questions","conditional":false,"uuid":"688c766b-d427-4c65-bef3-66e07ba1a931","typeApp":"textarea","value":""},{"type":"number","required":true,"label":"Teléfono de contacto","className":"form-control","name":"telefono_de_contacto","subtype":"number","editable":true,"section":"questions","conditional":false,"uuid":"6ffb46d9-a417-4370-b531-efb5167f56fd","typeApp":"number","value":""},{"type":"text","label":"Foto de Tacómetro&nbsp; (Legible y visible el kilometraje)","uuid":"c90bb291-0701-4d2a-a18e-8d53b5fbbb27","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_de_tacometro_legible_y_visible_el_kilometraje","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto de Número de Serie (foto legible donde se lea la serie de 17 dígitos)","uuid":"a0e34323-191e-40a9-9f47-0e03338be6a3","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_de_numero_de_serie_foto_legible_donde_se_lea_","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto frontal de la moto (la foto debe estar visible toda la parte frontal y centrada)","uuid":"f8efc9e4-3d18-4abc-b222-2e8324844bd7","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_frontal_de_la_moto_la_foto_debe_estar_visible","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto trasera de la moto (la foto debe ser visible toda la parte trasera y centrada, se debe ver el espejo y la llanta)","uuid":"abb8ebb0-713e-4f3a-8223-539c8bebd687","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_trasera_de_la_moto_la_foto_debe_ser_visible_t","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto lateral izquierda de la moto (foto legible de preferencia agachado y centrada para poder ver toda la moto)","uuid":"a572d294-6fee-495c-a9c2-52e5f1ead6d9","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_lateral_izquierda_de_la_moto_foto_legible_de_","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto lateral derecha de la moto (foto legible de preferencia agachado y centrada para poder ver toda la moto)","uuid":"750b170c-8afe-4a4f-bbb5-a7cc68541255","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_lateral_derecha_de_la_moto_foto_legible_de_pr","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Inspección 360 de Moto (el video debe evidenciar el funcionamiento eléctrico y debe estar enfocada a toda la unidad)","uuid":"086f9488-15c5-4695-99cf-26184524f739","editable":true,"section":"questions","required":true,"className":"form-control","name":"inspeccion_360_de_moto_el_video_debe_evidenciar_el","subtype":"text","conditional":false,"typeApp":"video","value":""},{"type":"select","required":true,"label":"Dictamen","className":"form-control","name":"dictamen","editable":false,"section":"customer","conditional":false,"uuid":"","typeApp":"select","values":[{"label":"Atendido","value":0,"selected":true}],"value":null}]';
        $decoded = json_decode($json, true);
        return is_array($decoded) ? json_encode($decoded) : $json;
    }

    private function filaCacheS2TieneResumenFinanciero(array $r): bool
    {
        $pj = isset($r['payload_json']) ? trim((string) $r['payload_json']) : '';
        if ($pj !== '') {
            return true;
        }
        $m = $r['monto_otorgado'] ?? null;
        if ($m !== null && $m !== '' && (float) $m > 0) {
            return true;
        }
        $c = $r['cuotas_pagadas'] ?? null;
        if ($c !== null && $c !== '') {
            return true;
        }
        $t = $r['total_pagado_cliente'] ?? null;
        if ($t !== null && $t !== '' && (float) $t > 0) {
            return true;
        }
        $um = $r['ultimo_efectivo_monto'] ?? null;
        if ($um !== null && $um !== '' && (float) $um > 0) {
            return true;
        }
        $uf = isset($r['ultimo_efectivo_fecha']) ? trim((string) $r['ultimo_efectivo_fecha']) : '';

        return $uf !== '';
    }

    /**
     * @param array<string,mixed> $r Fila de adj_s2_cache_dictamen con payload_json opcional
     */
    private function filaTienePayloadS2Guardado(array $r): bool
    {
        $pj = isset($r['payload_json']) ? trim((string) $r['payload_json']) : '';

        return $pj !== '';
    }

    /**
     * Convierte una fila de adj_s2_cache_dictamen al shape del modal S2 (sin success/from_cache).
     *
     * @param array<string,mixed> $r
     *
     * @return array<string,mixed>|null
     */
    private function normalizarFilaCacheResumenS2ModalDictamen(array $r): ?array
    {
        if (!$this->filaCacheS2TieneResumenFinanciero($r)) {
            return null;
        }
        $cc = $r['ma_seg_cuotas_contratadas'] ?? null;

        return [
            'monto_otorgado'              => isset($r['monto_otorgado']) && $r['monto_otorgado'] !== '' ? (float) $r['monto_otorgado'] : null,
            'cuotas_contratadas'          => $cc !== null && $cc !== '' ? (int) $cc : null,
            'cuotas_pagadas'              => isset($r['cuotas_pagadas']) && $r['cuotas_pagadas'] !== '' ? (int) $r['cuotas_pagadas'] : null,
            'total_pagado_cliente'        => isset($r['total_pagado_cliente']) && $r['total_pagado_cliente'] !== '' ? (float) $r['total_pagado_cliente'] : null,
            'ultimo_efectivo_fecha'       => isset($r['ultimo_efectivo_fecha']) && $r['ultimo_efectivo_fecha'] !== '' ? (string) $r['ultimo_efectivo_fecha'] : null,
            'ultimo_efectivo_monto'       => isset($r['ultimo_efectivo_monto']) && $r['ultimo_efectivo_monto'] !== '' ? (float) $r['ultimo_efectivo_monto'] : null,
            'ultimo_efectivo_es_estricto' => !empty($r['ultimo_efectivo_es_estricto']),
        ];
    }

    /**
     * Resumen S2 del modal por muchos id_credito (una consulta), para pintar el modal sin esperar otro round-trip.
     *
     * @param int[] $idsCredito
     *
     * @return array<int, array<string, mixed>> [ id_credito => payload compatible con obtenerResumenS2ModalDictamen ]
     */
    private function obtenerMapaResumenS2ModalDictamenPorCreditos(array $idsCredito): array
    {
        $idsCredito = array_values(array_unique(array_filter(array_map('intval', $idsCredito), static fn ($v) => $v > 0)));
        if ($idsCredito === [] || !$this->asegurarTablaCacheResumenS2ModalDictamen()) {
            return [];
        }
        $ph     = [];
        $params = [];
        foreach ($idsCredito as $i => $id) {
            $k          = 's2'.$i;
            $ph[]       = ':'.$k;
            $params[$k] = $id;
        }
        try {
            $filas = $this->db->queryAll(
                'SELECT
                    id_credito,
                    monto_otorgado,
                    ma_seg_cuotas_contratadas,
                    cuotas_pagadas,
                    total_pagado_cliente,
                    ultimo_efectivo_fecha,
                    ultimo_efectivo_monto,
                    ultimo_efectivo_es_estricto,
                    payload_json
                 FROM adj_s2_cache_dictamen
                 WHERE id_credito IN ('.implode(',', $ph).')',
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
        $out = [];
        foreach ($filas as $r) {
            $idc = (int) ($r['id_credito'] ?? 0);
            if ($idc <= 0) {
                continue;
            }
            $norm = $this->normalizarFilaCacheResumenS2ModalDictamen($r);
            if ($norm === null) {
                continue;
            }
            $out[$idc] = array_merge($norm, [
                'success'    => true,
                'from_cache' => true,
            ]);
        }

        return $out;
    }

    /**
     * @return array<monto_otorgado:?float,cuotas_contratadas:?int,cuotas_pagadas:?int,total_pagado_cliente:?float,ultimo_efectivo_fecha:?string,ultimo_efectivo_monto:?float,ultimo_efectivo_es_estricto:bool,_hay_payload_s2?:bool>|null
     */
    private function obtenerCacheResumenS2ModalDictamen(int $idCredito): ?array
    {
        if ($idCredito <= 0) {
            return null;
        }
        if (!$this->asegurarTablaCacheResumenS2ModalDictamen()) {
            return null;
        }
        try {
            $r = $this->db->queryOne(
                "SELECT
                    monto_otorgado,
                    ma_seg_cuotas_contratadas,
                    cuotas_pagadas,
                    total_pagado_cliente,
                    ultimo_efectivo_fecha,
                    ultimo_efectivo_monto,
                    ultimo_efectivo_es_estricto,
                    payload_json
                 FROM adj_s2_cache_dictamen
                 WHERE id_credito = :id
                 LIMIT 1",
                ['id' => $idCredito]
            );
        } catch (\Throwable $e) {
            return null;
        }
        if (!$r) {
            return null;
        }
        $norm = $this->normalizarFilaCacheResumenS2ModalDictamen($r);
        if ($norm === null) {
            return null;
        }
        if ($this->filaTienePayloadS2Guardado($r)) {
            $norm['_hay_payload_s2'] = true;
        }

        return $norm;
    }

    /**
     * @param array{monto_otorgado?:?float,cuotas_contratadas?:?int,cuotas_pagadas?:?int,total_pagado_cliente?:?float,ultimo_efectivo_fecha?:?string,ultimo_efectivo_monto?:?float,ultimo_efectivo_es_estricto?:bool} $resumen
     * @param array<string,mixed> $payloadEstadoCuenta
     */
    private function guardarCacheResumenS2ModalDictamen(int $idCredito, array $resumen, array $payloadEstadoCuenta = []): void
    {
        if ($idCredito <= 0) {
            return;
        }
        if (!$this->asegurarTablaCacheResumenS2ModalDictamen()) {
            return;
        }
        $ahora = date('Y-m-d H:i:s');
        $jsonPayload = $payloadEstadoCuenta !== []
            ? json_encode($payloadEstadoCuenta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : null;
        try {
            $this->db->CRUD(
                "INSERT INTO adj_s2_cache_dictamen (
                    id_credito,
                    monto_otorgado,
                    ma_seg_cuotas_contratadas,
                    cuotas_pagadas,
                    total_pagado_cliente,
                    ultimo_efectivo_fecha,
                    ultimo_efectivo_monto,
                    ultimo_efectivo_es_estricto,
                    payload_json,
                    consultado_at,
                    actualizado_at
                ) VALUES (
                    :id_credito,
                    :monto_otorgado,
                    :ma_seg_cuotas_contratadas,
                    :cuotas_pagadas,
                    :total_pagado_cliente,
                    :ultimo_efectivo_fecha,
                    :ultimo_efectivo_monto,
                    :ultimo_efectivo_es_estricto,
                    :payload_json,
                    :consultado_at,
                    :actualizado_at
                )
                ON DUPLICATE KEY UPDATE
                    monto_otorgado = VALUES(monto_otorgado),
                    ma_seg_cuotas_contratadas = VALUES(ma_seg_cuotas_contratadas),
                    cuotas_pagadas = VALUES(cuotas_pagadas),
                    total_pagado_cliente = VALUES(total_pagado_cliente),
                    ultimo_efectivo_fecha = VALUES(ultimo_efectivo_fecha),
                    ultimo_efectivo_monto = VALUES(ultimo_efectivo_monto),
                    ultimo_efectivo_es_estricto = VALUES(ultimo_efectivo_es_estricto),
                    payload_json = VALUES(payload_json),
                    consultado_at = VALUES(consultado_at),
                    actualizado_at = VALUES(actualizado_at)",
                [
                    'id_credito'                  => $idCredito,
                    'monto_otorgado'              => $resumen['monto_otorgado'] ?? null,
                    'ma_seg_cuotas_contratadas'   => $resumen['cuotas_contratadas'] ?? null,
                    'cuotas_pagadas'              => $resumen['cuotas_pagadas'] ?? null,
                    'total_pagado_cliente'        => $resumen['total_pagado_cliente'] ?? null,
                    'ultimo_efectivo_fecha'       => $resumen['ultimo_efectivo_fecha'] ?? null,
                    'ultimo_efectivo_monto'       => $resumen['ultimo_efectivo_monto'] ?? null,
                    'ultimo_efectivo_es_estricto' => !empty($resumen['ultimo_efectivo_es_estricto']) ? 1 : 0,
                    'payload_json'                => $jsonPayload,
                    'consultado_at'               => $ahora,
                    'actualizado_at'              => $ahora,
                ]
            );
        } catch (\Throwable $e) {
            // No bloquear flujo principal por falla de caché.
        }
    }

    /**
     * @return array{fecha:?string,monto:?float,estricto:bool}
     */
    private function extraerUltimoAbonoEfectivoDesdeEstadoCuenta(array $ec): array
    {
        $pagos = is_array($ec['datosPagos'] ?? null) ? $ec['datosPagos'] : [];
        $efectivos = [];
        $todos     = [];
        foreach ($pagos as $p) {
            if (!is_array($p)) {
                continue;
            }
            $monto = (float) ($p['montoPago'] ?? $p['monto'] ?? 0);
            if ($monto <= 0) {
                continue;
            }
            $fechaRaw = $p['fechaDeposito'] ?? $p['fechaValor'] ?? $p['fechaRegistro'] ?? null;
            $ts       = $this->timestampDesdeFechaS2Dictamen($fechaRaw);
            if ($ts === null) {
                continue;
            }
            $item = ['ts' => $ts, 'fecha_raw' => $fechaRaw, 'monto' => round($monto, 2)];
            $todos[] = $item;
            if ($this->pagoS2PareceEfectivoDictamen($p)) {
                $efectivos[] = $item;
            }
        }
        $lista    = $efectivos !== [] ? $efectivos : $todos;
        $estricto = $efectivos !== [];
        if ($lista === []) {
            return ['fecha' => null, 'monto' => null, 'estricto' => false];
        }
        usort($lista, static function ($a, $b) {
            return ($a['ts'] ?? 0) <=> ($b['ts'] ?? 0);
        });
        $last = $lista[count($lista) - 1];

        return [
            'fecha'    => $this->formatearFechaDisplayMxDictamen($last['fecha_raw'] ?? null),
            'monto'    => $last['monto'],
            'estricto' => $estricto,
        ];
    }

    private function pagoS2PareceEfectivoDictamen(array $p): bool
    {
        foreach (['formaPago', 'tipoFormaPago', 'descripcionFormaPago', 'tipoDeposito', 'forma_de_pago', 'concepto'] as $k) {
            if (empty($p[$k])) {
                continue;
            }
            $t = mb_strtolower(trim((string) $p[$k]), 'UTF-8');
            if ($t === '') {
                continue;
            }
            if (strpos($t, 'efect') !== false || strpos($t, 'cash') !== false) {
                return true;
            }
        }

        return false;
    }

    /** @param mixed $raw */
    private function timestampDesdeFechaS2Dictamen($raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        if (is_numeric($raw)) {
            $n = (float) $raw;
            if ($n > 2000000000000) {
                return (int) round($n / 1000);
            }
            if ($n > 20000000000) {
                return (int) round($n / 1000);
            }
            if ($n > 1000000000) {
                return (int) $n;
            }
        }
        $t = strtotime((string) $raw);

        return $t !== false ? $t : null;
    }

    /** @param mixed $raw */
    private function formatearFechaDisplayMxDictamen($raw): ?string
    {
        $ts = $this->timestampDesdeFechaS2Dictamen($raw);
        if ($ts === null) {
            return null;
        }
        try {
            $dt = new \DateTime('@' . $ts);
            $dt->setTimezone(new \DateTimeZone('America/Mexico_City'));

            return $dt->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return date('d/m/Y H:i', $ts);
        }
    }

    /**
     * Registra una sola vez la llegada f?sica al almac?n (Recepción). No se puede modificar ni repetir.
     *
     * @return array{success:bool, message?:string, fecha_llegada_almacen?:string, ya_registrada?:bool}
     */
    public function registrarLlegadaAlmacenRecepcion(int $idOperacion, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operaci?n inv?lida.'];
        }
        if (!$this->adjOperacionTieneColumnaFechaLlegadaAlmacen()) {
            return [
                'success' => false,
                'message' => 'Falta migración de base de datos: debe existir la columna fecha_llegada_almacen en adj_operacion.',
            ];
        }
        $row = $this->db->queryOne(
            'SELECT id, estatus, fecha_llegada_almacen FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$row) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        $est = trim((string) ($row['estatus'] ?? ''));
        if ($est !== 'Recepción') {
            return ['success' => false, 'message' => 'Solo se registra llegada a almac?n cuando la operaci?n est? en etapa Recepción.'];
        }
        if (!empty($row['fecha_llegada_almacen'])) {
            $fmt = $this->db->queryOne(
                "SELECT DATE_FORMAT(fecha_llegada_almacen, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
                ['id' => $idOperacion]
            );

            return [
                'success'          => false,
                'message'          => 'La llegada a almac?n ya fue registrada y no puede modificarse.',
                'ya_registrada'    => true,
                'fecha_llegada_almacen' => (string) ($fmt['f'] ?? $row['fecha_llegada_almacen']),
            ];
        }
        $ahora = $this->fechaHoraCdmx();
        $n = (int) $this->db->CRUD(
            'UPDATE adj_operacion
                SET fecha_llegada_almacen = :fecha, fecha_actualizacion = :fecha2
              WHERE id = :id AND fecha_llegada_almacen IS NULL',
            ['fecha' => $ahora, 'fecha2' => $ahora, 'id' => $idOperacion]
        );
        if ($n <= 0) {
            $row2 = $this->db->queryOne(
                'SELECT fecha_llegada_almacen FROM adj_operacion WHERE id = :id LIMIT 1',
                ['id' => $idOperacion]
            );
            if (!empty($row2['fecha_llegada_almacen'])) {
                $fmt2 = $this->db->queryOne(
                    "SELECT DATE_FORMAT(fecha_llegada_almacen, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
                    ['id' => $idOperacion]
                );

                return [
                    'success'               => false,
                    'message'               => 'La llegada a almac?n ya fue registrada y no puede modificarse.',
                    'ya_registrada'         => true,
                    'fecha_llegada_almacen' => (string) ($fmt2['f'] ?? $row2['fecha_llegada_almacen']),
                ];
            }

            return ['success' => false, 'message' => 'No se pudo registrar la llegada. Intente de nuevo.'];
        }
        $this->registrarBitacora(
            $idOperacion,
            'LLEGADA A ALMACÉN REGISTRADA (RECEPCIÓN): ' . $ahora,
            $idUsuario,
            $nombreUsuario,
            $ahora
        );
        $fmt = $this->db->queryOne(
            "SELECT DATE_FORMAT(fecha_llegada_almacen, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
            ['id' => $idOperacion]
        );

        return [
            'success'               => true,
            'fecha_llegada_almacen' => (string) ($fmt['f'] ?? $ahora),
        ];
    }

    // =========================================================================
    // HELPERS PRIVADOS
    // =========================================================================

    private function fechaHoraCdmx(): string
    {
        $dt = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        return $dt->format('Y-m-d H:i:s');
    }

    /**
     * Genera el siguiente folio: ADJ-YYYY-NNNN
     */
    private function generarFolio(): string
    {
        $anio = date('Y');
        $row  = $this->db->queryOne(
            "SELECT MAX(CAST(SUBSTRING_INDEX(folio, '-', -1) AS UNSIGNED)) AS ultimo
             FROM adj_operacion
             WHERE folio LIKE :prefijo",
            ['prefijo' => "ADJ-{$anio}-%"]
        );
        $siguiente = (int) ($row['ultimo'] ?? 0) + 1;
        return sprintf('ADJ-%s-%04d', $anio, $siguiente);
    }

    // =========================================================================
    // BUSCAR CR??DITO EN ADJUDICACI??N
    // =========================================================================

    /**
     * Verifica que el cr?dito tiene asignaci?n activa en adj_creditos_adjudicacion
     * y enriquece con datos del cliente v?a S2.
     *
     * @return array{success:bool, message?:string, nombre_cliente?:string, ...}
     */
    public function buscarCreditoEnAdjudicacion(int $idCredito): array
    {
        // 1. ?Est? asignado activamente en adjudicaci?n?
        $asignacion = $this->db->queryOne(
            <<<SQL
            SELECT
                aca.id               AS id_asignacion,
                DATE_FORMAT(aca.fecha_alta, '%Y-%m-%d') AS fecha_asignacion,
                TRIM(CONCAT_WS(' ',
                    per.nombres, per.segundo_nombre,
                    per.apellidop, per.apellidom
                ))                   AS gestor_nombre
            FROM asigna_creditos_adjudicacion aca
            INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
            INNER JOIN persona per             ON per.id = pa.id_persona
            WHERE aca.id_credito = :id
              AND aca.estatus    = '1'
            LIMIT 1
            SQL,
            ['id' => $idCredito]
        );

        if (!$asignacion) {
            return [
                'success' => false,
                'message' => "El cr?dito #{$idCredito} no tiene asignaci?n activa en el m?dulo de Adjudicaci?n. As?gnalo primero desde \"Asignaci?n de Cr?ditos\".",
            ];
        }

        // 2. Datos del cliente v?a S2 (reutiliza l?gica existente)
        $adjModel  = new AdjudicacionModel();
        $creditData = $adjModel->buscarCreditoPorId($idCredito);

        if (!$creditData['success']) {
            return $creditData;
        }

        // Aplanar: buscarCreditoPorId devuelve ['success'=>true, 'credito'=>[...]]
        $c = $creditData['credito'] ?? [];

        return [
            'success'          => true,
            'id_credito'       => $c['id_credito']     ?? $idCredito,
            'nombre_cliente'   => $c['nombre_cliente'] ?? '',
            'telefono'         => $c['telefono']       ?? '',
            'curp'             => $c['curp']           ?? '',
            'email'            => $c['email']          ?? '',
            'direccion'        => $c['direccion']      ?? '',
            'saldo_actual'     => $c['saldo_actual']   ?? 0,
            'dias_mora'        => $c['dias_mora']      ?? 0,
            'status_credito'   => $c['status_credito'] ?? '',
            'sucursal'         => $c['sucursal']       ?? '',
            'gestor_nombre'    => trim((string) ($asignacion['gestor_nombre']    ?? '')),
            'fecha_asignacion' => $asignacion['fecha_asignacion'] ?? '',
        ];
    }

    // =========================================================================
    // SUBIR EVIDENCIA
    // =========================================================================

    /**
     * Valida, guarda en disco y registra en adj_evidencia.
     *
     * @param  array  $fileInfo  Elemento de $_FILES['archivo']
     * @return array{success:bool, url?:string, message?:string}
     */
    public function subirEvidencia(int $idOperacion, string $slot, array $fileInfo, int $idUsuario, string $nombreUsuario = ''): array
    {
        // 1. Whitelist de slots v?lidos
        $allowed = [
            'rec_tacometro', 'rec_serie',     'rec_frontal', 'rec_lateral',
            'fis_vin',       'fis_tacometro', 'fis_frontal', 'fis_lateral', 'fis_360', 'fis_contrato_dacion',
            'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
            'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
            'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba',
            'doc_repuve',    'doc_factura',   'doc_cierre_s2',
            'doc_dacion_rcpt', 'doc_tarjeta_rcpt', 'doc_firma_rcpt',
            'vista_trs', 'vista_front', 'lado_izq', 'lado_der',
            'tablero', 'vin', 'danos_vis', 'vid_gen',
        ];
        if (!in_array($slot, $allowed, true)) {
            return ['success' => false, 'message' => 'Slot de evidencia no reconocido.'];
        }

        // 2. Operaci?n existe (subir evidencias f?sicas puede ser antes de datos; enviar al pipeline exige datos guardados).
        $op = $this->db->queryOne('SELECT id FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }

        // 3. Validar tipo MIME seg?n slot
        $mime      = $fileInfo['type'] ?? '';
        $ext       = strtolower(pathinfo($fileInfo['name'] ?? '', PATHINFO_EXTENSION));
        $videoSlots = ['fis_360', 'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba', 'vid_gen'];
        $docSlots   = ['doc_repuve', 'doc_factura', 'doc_cierre_s2', 'doc_dacion_rcpt'];
        $pdfOrImgMisAdj = ['fis_contrato_dacion', 'fis_dacion_hoja_1', 'fis_dacion_hoja_2'];
        $recepImgSlots = [
            'doc_tarjeta_rcpt', 'doc_firma_rcpt',
            'vista_trs', 'vista_front', 'lado_izq', 'lado_der', 'tablero', 'vin', 'danos_vis',
        ];
        $estatusEvidencia = in_array($slot, self::RECEPCION_ALMACEN_SLOTS, true)
            ? 'pendiente_almacen'
            : 'pendiente_envio';

        if (in_array($slot, $recepImgSlots, true)) {
            if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
                return ['success' => false, 'message' => 'Solo se aceptan im?genes JPG o PNG.'];
            }
            $tipo = 'image';
        } elseif (in_array($slot, $videoSlots, true)) {
            if ($mime !== 'video/mp4' || $ext !== 'mp4') {
                return ['success' => false, 'message' => 'Este campo solo acepta video MP4.'];
            }
            $tipo = 'video';
        } elseif (in_array($slot, $pdfOrImgMisAdj, true)) {
            $okMimes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!in_array($mime, $okMimes, true)) {
                return ['success' => false, 'message' => 'Documento de dación: solo PDF, JPG o PNG.'];
            }
            $tipo = ($mime === 'application/pdf') ? 'pdf' : 'image';
        } elseif (in_array($slot, $docSlots, true)) {
            if ($slot === 'doc_repuve') {
                if ($mime !== 'application/pdf' && $ext !== 'pdf') {
                    return ['success' => false, 'message' => 'Repuve: solo se acepta PDF.'];
                }
                $tipo = 'pdf';
            } else {
                // doc_factura, doc_cierre_s2, doc_dacion_rcpt: PDF o imagen
                $okMimes = ['application/pdf', 'image/jpeg', 'image/png'];
                if (!in_array($mime, $okMimes, true)) {
                    return ['success' => false, 'message' => 'Solo se aceptan PDF, JPG o PNG.'];
                }
                $tipo = ($mime === 'application/pdf') ? 'pdf' : 'image';
            }
        } else {
            if (!in_array($mime, ['image/jpeg', 'image/png'], true)) {
                return ['success' => false, 'message' => 'Solo se aceptan im?genes JPG o PNG.'];
            }
            $tipo = 'image';
        }

        // 4. L?mite de tama?o: 20 MB
        if (($fileInfo['size'] ?? 0) > 20 * 1024 * 1024) {
            return ['success' => false, 'message' => 'El archivo supera el l?mite de 20 MB.'];
        }

        // 5. Crear directorio de destino
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/operaciones/' . $idOperacion . '/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'message' => 'No se pudo crear el directorio de subida.'];
            }
        }

        // 6. Eliminar archivo anterior de este slot (si existe)
        $old = $this->db->queryOne(
            'SELECT url FROM adj_evidencia WHERE id_operacion = :id AND slot = :slot LIMIT 1',
            ['id' => $idOperacion, 'slot' => $slot]
        );
        if ($old && !empty($old['url'])) {
            $oldPath = dirname(__DIR__, 2) . '/public' . $old['url'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // 7. Mover archivo al destino
        $filename = $slot . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (!move_uploaded_file($fileInfo['tmp_name'], $destPath)) {
            return ['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor.'];
        }

        $urlRelativa = '/uploads/operaciones/' . $idOperacion . '/' . $filename;
        $ahora       = $this->fechaHoraCdmx();

        // 8. INSERT o UPDATE en adj_evidencia (al reemplazar archivo se limpia veredicto de Atenci?n para revalidar)
        if ($old) {
            if ($this->adjEvidenciaTieneColumnasAtn()) {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                        SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = :estatus,
                            val_atn = NULL, comentario_atn = NULL
                      WHERE id_operacion = :id AND slot = :slot",
                    ['tipo' => $tipo, 'url' => $urlRelativa, 'fecha' => $ahora, 'estatus' => $estatusEvidencia,
                     'id'   => $idOperacion, 'slot' => $slot]
                );
            } else {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                        SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = :estatus
                      WHERE id_operacion = :id AND slot = :slot",
                    ['tipo' => $tipo, 'url' => $urlRelativa, 'fecha' => $ahora, 'estatus' => $estatusEvidencia,
                     'id'   => $idOperacion, 'slot' => $slot]
                );
            }
        } else {
            $this->db->CRUD(
                "INSERT INTO adj_evidencia (id_operacion, tipo, slot, url, fecha_alta, alta, estatus)
                 VALUES (:id, :tipo, :slot, :url, :fecha, :alta, :estatus)",
                ['id'   => $idOperacion, 'tipo' => $tipo, 'slot' => $slot,
                 'url'  => $urlRelativa, 'fecha' => $ahora, 'alta' => $idUsuario, 'estatus' => $estatusEvidencia]
            );
        }

        $slotLabel = self::SLOT_LABELS[$slot] ?? strtoupper($slot);
        $this->registrarBitacora($idOperacion, 'SUBIÓ EVIDENCIA EN ' . $slotLabel, $idUsuario, $nombreUsuario);

        if (in_array($slot, ['doc_dacion_rcpt', 'doc_tarjeta_rcpt'], true)) {
            $this->marcarRecepcionDocumentoRecibidoEnOperacion($idOperacion, $slot);
        }

        $urlClient = $urlRelativa;
        if (function_exists('sparta_url_publica_desde_repositorio')) {
            $urlClient = sparta_url_publica_desde_repositorio($urlRelativa);
        }

        /* Reemplazo en slots dictaminables: si ya no hay rechazos, regresa a bandeja Evidencias (Recibido). */
        if ($this->adjEvidenciaTieneColumnasAtn()
            && in_array($slot, self::SLOTS_VALIDACION_ATENCION_MEDIA, true)) {
            $this->finalizarCierreValidacionEvidenciaAtn($idOperacion, $idUsuario, $nombreUsuario);
        }

        return ['success' => true, 'url' => $urlClient];
    }

    /**
     * Marca recepcion_*_estado = received cuando existe migraci?n de columnas.
     */
    private function marcarRecepcionDocumentoRecibidoEnOperacion(int $idOperacion, string $slot): void
    {
        if (!$this->adjOperacionTieneColumnasRecepcionDocEstado()) {
            return;
        }
        $ahora = $this->fechaHoraCdmx();
        if ($slot === 'doc_dacion_rcpt') {
            $this->db->CRUD(
                'UPDATE adj_operacion SET recepcion_dacion_estado = :e, fecha_actualizacion = :f WHERE id = :id',
                ['e' => 'received', 'f' => $ahora, 'id' => $idOperacion]
            );
        } elseif ($slot === 'doc_tarjeta_rcpt') {
            $this->db->CRUD(
                'UPDATE adj_operacion SET recepcion_tarjeta_estado = :e, fecha_actualizacion = :f WHERE id = :id',
                ['e' => 'received', 'f' => $ahora, 'id' => $idOperacion]
            );
        }
    }

    // =========================================================================
    // BITÁCORA
    // =========================================================================

    /**
     * Textos guardados con codificación rota (?? en lugar de tilde) o mezcla legacy + UTF-8.
     */
    private function normalizarAdjBitacoraAccionDisplay(string $accion): string
    {
        static $map = [
            'SUBI??'        => 'SUBIÓ',
            'ENVI??'        => 'ENVIÓ',
            'VALIDACI??N'   => 'VALIDACIÓN',
            'DACI??N'       => 'DACIÓN',
            'RECEPCI??N'    => 'RECEPCIÓN',
            'ALMAC??N'      => 'ALMACÉN',
            'CONFIRMACI??N' => 'CONFIRMACIÓN',
            'CIRCULACI??N'  => 'CIRCULACIÓN',
            'TAC??METRO'    => 'TACÓMETRO',
            'RECOLECCI??N'  => 'RECOLECCIÓN',
            'AGREG??'       => 'AGREGÓ',
            'ACCI??N'       => 'ACCIÓN',
            'INSPECCI??N'   => 'INSPECCIÓN',
            'OD??METRO'     => 'ODÓMETRO',
            'DA??OS'        => 'DAÑOS',
            '(F?SICA)'      => '(FÍSICA)',
        ];

        return str_replace(array_keys($map), array_values($map), $accion);
    }

    private function registrarBitacora(int $idOperacion, string $accion, int $idUsuario, string $nombreUsuario, ?string $fecha = null): void
    {
        if ($idOperacion <= 0) return;
        $fecha = $fecha ?? $this->fechaHoraCdmx();
        $nom   = trim($nombreUsuario ?: 'SISTEMA');
        $acc   = $accion;
        if (function_exists('mb_strtoupper')) {
            $nom = mb_strtoupper($nom, 'UTF-8');
            $acc = mb_strtoupper($acc, 'UTF-8');
        } else {
            $nom = strtoupper($nom);
            $acc = strtoupper($acc);
        }
        $this->db->CRUD(
            "INSERT INTO adj_bitacora (id_operacion, id_usuario, nombre_usuario, accion, fecha_alta)
             VALUES (:id_op, :id_usr, :nombre, :accion, :fecha)",
            [
                'id_op'  => $idOperacion,
                'id_usr' => $idUsuario,
                'nombre' => $nom,
                'accion' => $acc,
                'fecha'  => $fecha,
            ]
        );
    }

    public function obtenerBitacora(int $idOperacion): array
    {
        $rows = $this->db->queryAll(
            "SELECT id, nombre_usuario, accion,
                    DATE_FORMAT(fecha_alta, '%d/%m/%Y %h:%i:%s %p') AS fecha_alta
             FROM adj_bitacora
             WHERE id_operacion = :id
             ORDER BY fecha_alta DESC
             LIMIT 100",
            ['id' => $idOperacion]
        ) ?: [];
        foreach ($rows as &$r) {
            $r['accion'] = $this->normalizarAdjBitacoraAccionDisplay((string) ($r['accion'] ?? ''));
        }
        unset($r);

        return $rows;
    }

    /**
     * Resumen para modal Flujo de Operaciones (etapa Recibido / evidencia).
     *
     * @param list<array<string,mixed>> $evs
     * @param array<string,mixed>       $op Fila adj_operacion (incl. es_validado_ia, score_ia si existen).
     * @return array<string, mixed>
     */
    private function construirResumenEvidenciaFlujo(array $evs, array $op): array
    {
        $bySlot = [];
        foreach ($evs as $r) {
            $sk = trim((string) ($r['slot'] ?? ''));
            if ($sk !== '') {
                $bySlot[$sk] = $r;
            }
        }

        $cargadas = 0;
        foreach (self::SLOTS_PIPELINE_EXPEDIENTE as $s) {
            if (isset($bySlot[$s]) && trim((string) ($bySlot[$s]['url'] ?? '')) !== '') {
                $cargadas++;
            }
        }

        $validadas = 0;
        $aceptadas = 0;
        $devueltas = 0;
        $pendientes = 0;

        foreach (self::SLOTS_VALIDACION_ATENCION_MEDIA as $s) {
            if (!isset($bySlot[$s])) {
                continue;
            }
            $url = trim((string) ($bySlot[$s]['url'] ?? ''));
            $va = (int) ($bySlot[$s]['val_atn'] ?? 0);
            if ($url !== '' && $va === 0) {
                $pendientes++;
            }
            if ($va === 1) {
                $aceptadas++;
                $validadas++;
            } elseif ($va === 2) {
                $devueltas++;
                $validadas++;
            }
        }

        // Repuve (PDF): expediente + env?o al pipeline + resultado IA en operaci?n
        $repuvePdf      = false;
        $repuveEstatus  = null;
        $repuveRow      = $bySlot[self::SLOT_REPVE_ATENCION] ?? null;
        if ($repuveRow !== null) {
            $repuvePdf = trim((string) ($repuveRow['url'] ?? '')) !== '';
            if ($repuvePdf) {
                $repuveEstatus = trim((string) ($repuveRow['estatus'] ?? ''));
                $repuveEstatus = $repuveEstatus !== '' ? $repuveEstatus : null;
            }
        }

        $iaRaw = $op['es_validado_ia'] ?? null;
        $iaInt = null;
        if ($iaRaw !== null && $iaRaw !== '') {
            $iaInt = (int) $iaRaw;
        }

        if ($iaInt === null) {
            $validacionIaTxt = 'Pendiente de validaci?n IA';
        } elseif ($iaInt === 1) {
            $validacionIaTxt = 'Conforme seg?n IA';
        } else {
            $validacionIaTxt = 'No conforme seg?n IA';
        }

        if (!$repuvePdf) {
            $estatusEnvioTxt = 'Sin PDF en expediente';
        } elseif ($repuveEstatus === 'recibido') {
            $estatusEnvioTxt = 'PDF enviado al pipeline';
        } elseif ($repuveEstatus === 'pendiente_envio') {
            $estatusEnvioTxt = 'PDF cargado; pendiente de env?o al pipeline';
        } elseif ($repuveEstatus !== null) {
            $estatusEnvioTxt = $repuveEstatus;
        } else {
            $estatusEnvioTxt = '—';
        }

        $scoreIa = null;
        if (\array_key_exists('score_ia', $op) && $op['score_ia'] !== null && $op['score_ia'] !== '') {
            $scoreIa = is_numeric($op['score_ia']) ? (float) $op['score_ia'] : null;
        }

        return [
            'total_slots'               => \count(self::SLOTS_PIPELINE_EXPEDIENTE),
            'cargadas_gestor'           => $cargadas,
            'slots_validacion_media'    => \count(self::SLOTS_VALIDACION_ATENCION_MEDIA),
            'validadas_en_evidencia'    => $validadas,
            'aceptadas'                 => $aceptadas,
            'devueltas'                 => $devueltas,
            'pendientes_validacion'     => $pendientes,
            'repuve'                    => [
                'pdf_cargado'        => $repuvePdf,
                'estatus_archivo'    => $repuveEstatus,
                'estatus_pipeline'   => $estatusEnvioTxt,
                'es_validado_ia'     => $iaInt,
                'validacion_ia_txt'  => $validacionIaTxt,
                'score_ia'           => $scoreIa,
            ],
        ];
    }

    /**
     * Vista 4 Cartera: registra que el usuario confirma haber dado de alta el cierre en S2
     * y env?a la operaci?n a la etapa Recepción (bandeja de entrada de la vista 5).
     *
     * @return array{success:bool, message?:string, estatus_nuevo?:string}
     */
    public function confirmarCierreDocumentacionEnS2(int $idOperacion, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Identificador de operaci?n inv?lido.'];
        }
        $row = $this->db->queryOne(
            'SELECT id, estatus FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$row) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        $est = trim((string) ($row['estatus'] ?? ''));
        if ($est !== 'Cierre Documentado') {
            return ['success' => false, 'message' => 'La operaci?n no est? en etapa Cierre documentado.'];
        }

        $this->registrarBitacora(
            $idOperacion,
            'CONFIRMACIÓN: Cierre documentado registrado en S2',
            $idUsuario,
            $nombreUsuario
        );

        /**
         * Registro expl?cito en adj_dictamen para que las vistas posteriores puedan mostrar
         * la l?nea de dictamen sin mantener la operaci?n ???colgada??? en filtros por estatus antiguos.
         */
        $ahora = $this->fechaHoraCdmx();
        $this->db->CRUD(
            "INSERT INTO adj_dictamen
                (id_operacion, llamada_a, numero, persona_contactada, tipo_contacto,
                 resultado, dictamen, plataforma, comentarios, id_usuario, fecha_alta)
             VALUES
                (:id_operacion, :llamada_a, :numero, :persona_contactada, :tipo_contacto,
                 :resultado, :dictamen, :plataforma, :comentarios, :id_usuario, :fecha_alta)",
            [
                'id_operacion'       => $idOperacion,
                'llamada_a'          => 'Cierre S2',
                'numero'             => '',
                'persona_contactada' => $nombreUsuario !== '' ? $nombreUsuario : 'Usuario',
                'tipo_contacto'      => 'Cierre documentación',
                'resultado'          => 'Confirmado en S2',
                'dictamen'           => 'Cierre documentado confirmado en S2',
                'plataforma'         => 'S2',
                'comentarios'        => null,
                'id_usuario'         => $idUsuario ?: null,
                'fecha_alta'         => $ahora,
            ]
        );

        $mov = $this->cambiarEstatus($idOperacion, 'Recepción', $idUsuario, $nombreUsuario);
        if (empty($mov['success'])) {
            return $mov;
        }

        return ['success' => true, 'estatus_nuevo' => 'Recepción'];
    }

    /**
     * Recuperaci?n (momento 3): con factura cargada, env?a la operaci?n a Cartera ??? estatus Cierre documentado.
     * Los comentarios son opcionales; si hay texto se guardan en adj_observacion.
     *
     * @return array{success:bool, message?:string}
     */
    public function enviarRecuperacionACartera(int $idOperacion, string $comentarios, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Identificador de operaci?n inv?lido.'];
        }
        $comentarios = trim($comentarios);

        $op = $this->db->queryOne(
            'SELECT id, estatus FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        $est = trim((string) ($op['estatus'] ?? ''));
        if ($est !== 'Procesando IA') {
            return ['success' => false, 'message' => 'La operaci?n no est? en etapa Procesando IA.'];
        }

        $fact = $this->db->queryOne(
            "SELECT id FROM adj_evidencia
             WHERE id_operacion = :id AND slot = 'doc_factura'
               AND url IS NOT NULL AND TRIM(url) <> ''
             LIMIT 1",
            ['id' => $idOperacion]
        );
        if (!$fact) {
            return ['success' => false, 'message' => 'Debe cargar la factura (momento 3) antes de enviar a cartera.'];
        }

        if ($comentarios !== '') {
            $obs = $this->agregarObservacion($idOperacion, 'Recuperación', 'Cartera', $idUsuario, $comentarios, $nombreUsuario);
            if (empty($obs['success'])) {
                return $obs;
            }
        }

        /**
         * Registro en adj_dictamen: las listas de 3.- Recuperaci?n (bandeja vs dictaminado) dependen de
         * tener dictamen al estar en Cierre documentado; sin esta fila la operaci?n queda ??colgada?? en bandeja.
         */
        $ahora = $this->fechaHoraCdmx();
        $this->db->CRUD(
            "INSERT INTO adj_dictamen
                (id_operacion, llamada_a, numero, persona_contactada, tipo_contacto,
                 resultado, dictamen, plataforma, comentarios, id_usuario, fecha_alta)
             VALUES
                (:id_operacion, :llamada_a, :numero, :persona_contactada, :tipo_contacto,
                 :resultado, :dictamen, :plataforma, :comentarios, :id_usuario, :fecha_alta)",
            [
                'id_operacion'       => $idOperacion,
                'llamada_a'          => 'Cartera',
                'numero'             => '',
                'persona_contactada' => $nombreUsuario !== '' ? $nombreUsuario : 'Usuario',
                'tipo_contacto'      => 'Recuperación',
                'resultado'          => 'Expediente enviado',
                'dictamen'           => 'Recuperación enviada a Cartera (evidencias y factura completas)',
                'plataforma'         => 'Sparta',
                'comentarios'        => $comentarios !== '' ? $comentarios : null,
                'id_usuario'         => $idUsuario ?: null,
                'fecha_alta'         => $ahora,
            ]
        );

        return $this->cambiarEstatus($idOperacion, 'Cierre Documentado', $idUsuario, $nombreUsuario);
    }

    // =========================================================================
    // PIPELINE / LECTURA
    // =========================================================================

    /**
     * Dictamen registrado desde Retenciones (misma regla que Models\AtencionClientes).
     *
     * @param string $alias Alias de adj_dictamen en la expresi?n (p. ej. "d2")
     */
    private function sqlEsDictamenLlamadaRetenciones(string $alias): string
    {
        return <<<SQL
(
    {$alias}.tipo_contacto IN ('Contacto', 'Sin contacto')
    OR (
        ({$alias}.tipo_contacto IS NULL OR TRIM({$alias}.tipo_contacto) = '')
        AND {$alias}.dictamen IN (
            'Autorizado para recolecci?n',
            'Cancelado, promesa de pago',
            'Pendiente de contacto',
            'No localizado'
        )
    )
)
SQL;
    }

    /**
     * Devuelve todas las operaciones activas (no cerradas-archivadas),
     * ordenadas por estatus y fecha_alta.
     */
    public function obtenerPipeline(): array
    {
        $predD2 = $this->sqlEsDictamenLlamadaRetenciones('d2');
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.area_actual,
            o.score_ia,
            o.responsable_entrega,
            o.telefono_contacto,
            o.direccion_recoleccion,
            o.es_validado_ia,
            o.es_validado_factura,
            o.marca,
            o.modelo,
            o.serie,
            o.num_motor,
            NULL AS placas,
            o.dias_mora,
            o.saldo_capital,
            o.adeudo_total,
            o.id_usuario_alta,
            DATE_FORMAT(o.fecha_alta,          '%Y-%m-%d %H:%i') AS fecha_alta,
            DATE_FORMAT(o.fecha_actualizacion, '%Y-%m-%d %H:%i') AS fecha_actualizacion,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            (SELECT COUNT(*) FROM adj_evidencia e WHERE e.id_operacion = o.id) AS evidencias_count,
            (SELECT TRIM(CONCAT_WS(' ', per2.nombres, per2.segundo_nombre, per2.apellidop, per2.apellidom))
               FROM asigna_creditos_adjudicacion aca2
               INNER JOIN personal_adjudicacion pa2 ON pa2.id = aca2.id_personal_adj
               INNER JOIN persona per2              ON per2.id = pa2.id_persona
              WHERE aca2.id_credito = o.id_credito AND aca2.estatus = '1'
              LIMIT 1) AS gestor_nombre,
            COALESCE(
                (SELECT DATE_FORMAT(h.fecha, '%d/%m/%Y %H:%i')
                 FROM adj_historial_estatus h
                 WHERE h.id_operacion = o.id
                   AND h.estatus_nuevo IN ('Retenciones', 'cancelado')
                 ORDER BY h.fecha ASC
                 LIMIT 1),
                DATE_FORMAT(o.fecha_alta, '%d/%m/%Y %H:%i')
            ) AS ret_registro_pipe_fmt,
            ret_d.tipo_contacto AS ret_llamada_tipo_contacto,
            ret_d.resultado AS ret_llamada_resultado,
            ret_d.dictamen AS ret_llamada_dictamen,
            DATE_FORMAT(ret_d.fecha_alta, '%d/%m/%Y %H:%i') AS ret_llamada_fecha_fmt
        FROM adj_operacion o
        LEFT JOIN adj_dictamen ret_d ON ret_d.id = (
            SELECT d2.id
            FROM adj_dictamen d2
            WHERE d2.id_operacion = o.id
              AND {$predD2}
            ORDER BY
                CASE WHEN TRIM(COALESCE(d2.dictamen, '')) = '' THEN 1 ELSE 0 END,
                d2.fecha_alta DESC,
                d2.id DESC
            LIMIT 1
        )
        ORDER BY
            FIELD(o.estatus,
                'Recibido',
                'en_transito',
                'Procesando IA',
                'Revisión Recuperaciones',
                'Cierre Documentado',
                'Recepción',
                'Retenciones',
                'cancelado'
            ),
            o.fecha_alta ASC
        SQL;

        try {
            return $this->db->queryAll($sql) ?: [];
        } catch (\Throwable $e) {
            // Compatibilidad: en algunos entornos legacy adj_operacion aún no tiene columna `placas`.
            if (stripos((string) $e->getMessage(), "Unknown column 'o.placas'") !== false) {
                $sqlFallback = str_replace(
                    "            o.placas,\n",
                    "            NULL AS placas,\n",
                    $sql
                );

                return $this->db->queryAll($sqlFallback) ?: [];
            }
            throw $e;
        }
    }

    /**
     * Detalle completo de una operaci?n incluyendo evidencias y observaciones.
     */
    public function obtenerDetalle(int $id): ?array
    {
        $op = $this->db->queryOne(
            "SELECT o.*,
                    DATE_FORMAT(o.fecha_alta,          '%Y-%m-%d %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(o.fecha_actualizacion, '%Y-%m-%d %H:%i') AS fecha_actualizacion_fmt,
                    DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline
             FROM adj_operacion o
             WHERE o.id = :id",
            ['id' => $id]
        );

        if (!$op) {
            return null;
        }

        $fla = $op['fecha_llegada_almacen'] ?? null;
        if ($fla !== null && (string) $fla !== '') {
            try {
                $dtFmt = new \DateTime((string) $fla, new \DateTimeZone('America/Mexico_City'));
                $op['fecha_llegada_almacen_fmt'] = $dtFmt->format('d/m/Y H:i');
            } catch (\Throwable $e) {
                $op['fecha_llegada_almacen_fmt'] = (string) $fla;
            }
        }

        $rca = $op['recepcion_confirmada_at'] ?? null;
        if ($rca !== null && (string) $rca !== '') {
            try {
                $dtC = new \DateTime((string) $rca, new \DateTimeZone('America/Mexico_City'));
                $op['recepcion_confirmada_at_fmt'] = $dtC->format('d/m/Y H:i');
            } catch (\Throwable $e) {
                $op['recepcion_confirmada_at_fmt'] = (string) $rca;
            }
        }

        if ($this->adjEvidenciaTieneColumnasAtn()) {
            $evs = $this->db->queryAll(
                "SELECT id, tipo, slot, url, estatus, val_atn, comentario_atn,
                        DATE_FORMAT(fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta
                 FROM adj_evidencia WHERE id_operacion = :id ORDER BY id ASC",
                ['id' => $id]
            ) ?: [];
        } else {
            $evs = $this->db->queryAll(
                "SELECT id, tipo, slot, url, estatus,
                        DATE_FORMAT(fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta
                 FROM adj_evidencia WHERE id_operacion = :id ORDER BY id ASC",
                ['id' => $id]
            ) ?: [];
        }
        // aprobada: 0 (legacy) ??? veredicto en val_atn (1=aceptar, 2=rechazar) si la migraci?n est? aplicada
        foreach ($evs as &$r) {
            $r['aprobada'] = 0;
            $va = isset($r['val_atn']) && $r['val_atn'] !== null && $r['val_atn'] !== ''
                ? (int) $r['val_atn'] : 0;
            $r['val_atn']         = $va;
            $r['comentario_atn']  = isset($r['comentario_atn']) ? (string) $r['comentario_atn'] : '';
            if (!empty($r['url'])) {
                $urlOriginal = (string) $r['url'];
                $urlLimpia = str_replace('\\', '/', trim($urlOriginal));
                $urlLimpia = preg_replace('#^https?://uploads(?=/|$)#i', '/uploads', $urlLimpia);
                $urlLimpia = preg_replace('#^/{2,}uploads(?=/|$)#i', '/uploads', $urlLimpia);
                $urlLimpia = preg_replace('#^/uploads/uploads/#i', '/uploads/', $urlLimpia);

                if ($urlLimpia !== $urlOriginal && !empty($r['id'])) {
                    $this->db->CRUD(
                        'UPDATE adj_evidencia SET url = :url WHERE id = :id',
                        ['url' => $urlLimpia, 'id' => (int) $r['id']]
                    );
                }

                $r['url'] = function_exists('sparta_url_publica_desde_repositorio')
                    ? sparta_url_publica_desde_repositorio($urlLimpia)
                    : $urlLimpia;
            }
        }
        unset($r);
        $op['evidencias'] = $evs;

        $op['resumen_evidencia_flujo'] = $this->construirResumenEvidenciaFlujo($evs, $op);
        $timeline = $this->db->queryAll(
            "SELECT nombre_usuario, accion,
                    DATE_FORMAT(fecha_alta, '%d/%m/%Y %h:%i:%s %p') AS fecha_fmt
             FROM adj_bitacora
             WHERE id_operacion = :id
               AND (
                   accion LIKE '%VALIDACIÓN EVIDENCIA%'
                   OR accion LIKE '%VALIDACI??N EVIDENCIA%'
               )
             ORDER BY fecha_alta ASC",
            ['id' => $id]
        ) ?: [];
        foreach ($timeline as &$tl) {
            $tl['accion'] = $this->normalizarAdjBitacoraAccionDisplay((string) ($tl['accion'] ?? ''));
        }
        unset($tl);
        $op['validaciones_evidencia_timeline'] = $timeline;

        $op['observaciones'] = $this->db->queryAll(
            "SELECT id, etapa, area, id_usuario, texto, DATE_FORMAT(fecha, '%Y-%m-%d %H:%i') AS fecha
             FROM adj_observacion WHERE id_operacion = :id ORDER BY fecha ASC",
            ['id' => $id]
        ) ?: [];

        $op['historial'] = $this->db->queryAll(
            "SELECT id, estatus_anterior, estatus_nuevo, id_usuario, DATE_FORMAT(fecha, '%Y-%m-%d %H:%i') AS fecha
             FROM adj_historial_estatus WHERE id_operacion = :id ORDER BY fecha DESC",
            ['id' => $id]
        ) ?: [];

        $op['bitacora'] = $this->obtenerBitacora($id);

        return $op;
    }

    // =========================================================================
    // ENVIAR EVIDENCIAS (pendiente_envio ??? recibido)
    // =========================================================================

    /**
     * Cambia todas las evidencias en estado 'pendiente_envio' de una operaci?n a 'recibido'.
     * @return array{success:bool, actualizadas?:int, message?:string}
     */
    public function enviarEvidencias(int $idOperacion, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        $op = $this->db->queryOne(
            'SELECT id, datos_moto_at FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        if (empty($op['datos_moto_at'])) {
            return [
                'success' => false,
                'message' => 'Debe guardar los datos de la motocicleta y la ubicación antes de enviar evidencias.',
            ];
        }

        $this->db->CRUD(
            "UPDATE adj_evidencia SET estatus = 'recibido'
              WHERE id_operacion = :id AND estatus = 'pendiente_envio'",
            ['id' => $idOperacion]
        );

        $this->registrarBitacora($idOperacion, 'ENVIÓ EVIDENCIAS AL PIPELINE', $idUsuario, $nombreUsuario);

        return ['success' => true];
    }

    // =========================================================================
    // CREAR OPERACI??N
    // =========================================================================

    /**
     * Crea una nueva operaci?n en el pipeline.
     * Retorna ['success'=>true, 'id'=>???, 'folio'=>???] o ['success'=>false, 'message'=>???]
     */
    public function crearOperacion(array $data, int $idUsuario): array
    {
        $ahora = $this->fechaHoraCdmx();
        $folio = $this->generarFolio();

        $campos = [
            'folio'                 => $folio,
            'id_credito'            => (int) ($data['id_credito']          ?? 0),
            'nombre_cliente'        => trim($data['nombre_cliente']         ?? ''),
            'responsable_entrega'   => trim($data['responsable_entrega']    ?? ''),
            'telefono_contacto'     => trim($data['telefono_contacto']      ?? ''),
            'direccion_recoleccion' => trim($data['direccion_recoleccion']  ?? ''),
            'marca'                 => trim($data['marca']                  ?? ''),
            'modelo'                => trim($data['modelo']                 ?? ''),
            'serie'                 => trim($data['serie']                  ?? ''),
            'num_motor'             => trim($data['num_motor']              ?? ''),
            'placas'                => trim($data['placas']                 ?? ''),
            'dias_mora'             => isset($data['dias_mora'])   ? (int) $data['dias_mora']   : null,
            'saldo_capital'         => isset($data['saldo_capital']) ? (float) $data['saldo_capital'] : null,
            'adeudo_total'          => isset($data['adeudo_total'])  ? (float) $data['adeudo_total']  : null,
            'area_actual'           => trim($data['area_actual']            ?? ''),
            'estatus'               => 'Recibido',
            'id_usuario_alta'       => $idUsuario,
            'fecha_alta'            => $ahora,
            'fecha_actualizacion'   => $ahora,
        ];

        // Limpiar nullables vac?os
        foreach (['dias_mora', 'saldo_capital', 'adeudo_total'] as $campo) {
            if ($campos[$campo] === null || $campos[$campo] === 0) {
                $campos[$campo] = null;
            }
        }

        $cols        = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($campos)));
        $placeholders = implode(', ', array_map(fn($k) => ":{$k}", array_keys($campos)));

        $this->db->CRUD(
            "INSERT INTO adj_operacion ({$cols}) VALUES ({$placeholders})",
            $campos
        );

        $newId = $this->db->lastInsertId();

        if ($newId <= 0) {
            return ['success' => false, 'message' => 'No se pudo registrar la operaci?n.'];
        }

        // Historial inicial
        $this->db->CRUD(
            "INSERT INTO adj_historial_estatus
                (id_operacion, estatus_anterior, estatus_nuevo, id_usuario, fecha)
             VALUES
                (:id_op, NULL, 'Recibido', :id_usr, :fecha)",
            ['id_op' => $newId, 'id_usr' => $idUsuario, 'fecha' => $ahora]
        );

        return ['success' => true, 'id' => $newId, 'folio' => $folio];
    }

    // =========================================================================
    // CAMBIAR ESTATUS (mover columna en el kanban)
    // =========================================================================

    private const SLOT_LABELS = [
        'rec_tacometro' => 'TACÓMETRO (RECOLECCIÓN)',
        'rec_serie'     => 'NO. SERIE (RECOLECCIÓN)',
        'rec_frontal'   => 'FRONTAL (RECOLECCIÓN)',
        'rec_lateral'   => 'LATERAL (RECOLECCIÓN)',
        'fis_vin'       => 'VIN (FÍSICA)',
        'fis_tacometro' => 'TACÓMETRO (FÍSICA)',
        'fis_frontal'   => 'FRONTAL (FÍSICA)',
        'fis_lateral'   => 'LATERAL (FÍSICA) [LEGACY]',
        'fis_360'       => 'INSPECCIÓN 360° [LEGACY]',
        'fis_contrato_dacion' => 'CONTRATO DACIÓN (FÍSICA) [LEGACY]',
        'fis_dacion_hoja_1' => 'DACIÓN HOJA 1 (FÍSICA)',
        'fis_dacion_hoja_2' => 'DACIÓN HOJA 2 (FÍSICA)',
        'fis_lateral_der' => 'LATERAL DERECHA (FÍSICA)',
        'fis_trasera' => 'TRASERA (FÍSICA)',
        'fis_lateral_izq' => 'LATERAL IZQUIERDA (FÍSICA)',
        'fis_video_cliente_acuerdo' => 'VIDEO CLIENTE DE ACUERDO (FÍSICA)',
        'fis_360_encendida' => 'VIDEO MOTO 360 ENCENDIDA (FÍSICA)',
        'fis_video_vuelta_prueba' => 'VIDEO VUELTA DE PRUEBA (FÍSICA)',
        'doc_repuve'    => 'REPUVE',
        'doc_factura'   => 'FACTURA',
        'doc_cierre_s2' => 'CONFIRMACIÓN CIERRE S2',
        'doc_dacion_rcpt'   => 'CONTRATO DACIÓN (RECEPCIÓN ALMACÉN)',
        'doc_tarjeta_rcpt'  => 'TARJETA CIRCULACIÓN (RECEPCIÓN ALMACÉN)',
        'doc_firma_rcpt'    => 'FIRMA RECEPCIÓN ALMACÉN',
        'vista_trs'         => 'VISTA TRASERA (RECEPCIÓN ALMACÉN)',
        'vista_front'       => 'VISTA FRONTAL (RECEPCIÓN ALMACÉN)',
        'lado_izq'          => 'LADO IZQUIERDO (RECEPCIÓN ALMACÉN)',
        'lado_der'          => 'LADO DERECHO (RECEPCIÓN ALMACÉN)',
        'tablero'           => 'TABLERO / ODÓMETRO (RECEPCIÓN ALMACÉN)',
        'vin'               => 'VIN (RECEPCIÓN ALMACÉN)',
        'danos_vis'         => 'DAÑOS VISIBLES (RECEPCIÓN ALMACÉN)',
        'vid_gen'           => 'VIDEO GENERAL 360° (RECEPCIÓN ALMACÉN)',
    ];

    private const RECEPCION_ALMACEN_SLOTS = [
        'vista_trs', 'vista_front', 'lado_izq', 'lado_der',
        'tablero', 'vin', 'danos_vis', 'vid_gen',
    ];

    /** Fotos/video que s? se dictaminan (aceptar/rechazar) en Atenci?n a clientes (solo evidencia f?sica momento 1). */
    private const SLOTS_VALIDACION_ATENCION_MEDIA = [
        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
        'fis_vin', 'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
        'fis_tacometro', 'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba',
    ];

    /** Repuve: solo debe existir PDF subido; no se usa val_atn en Atenci?n. */
    private const SLOT_REPVE_ATENCION = 'doc_repuve';

    /**
     * Slots del expediente en pipeline/kanban: recolección + física momento 1 (Mis adjudicaciones) +
     * momento 2 (Repuve) + momento 3 (Factura).
     *
     * @see MADJ_SLOTS_EVIDENCIA_MEDIA (debe mantener el mismo orden lógico de evidencia física)
     */
    private const SLOTS_PIPELINE_EXPEDIENTE = [
        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
        'fis_vin',
        'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
        'fis_tacometro',
        'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba',
        'doc_repuve', 'doc_factura',
    ];

    private const ESTATUS_VALIDOS = [
        'Recibido',
        'en_transito',
        'Procesando IA',
        'Revisión Recuperaciones',
        'Retenciones',
        'Cierre Documentado',
        'Recepción',
    ];

    /**
     * Cambia el estatus de una operaci?n y registra historial.
     */
    public function cambiarEstatus(int $id, string $estatusNuevo, int $idUsuario, string $nombreUsuario = ''): array
    {
        if (!in_array($estatusNuevo, self::ESTATUS_VALIDOS, true)) {
            return ['success' => false, 'message' => 'Estatus no v?lido.'];
        }

        $actual = $this->db->queryOne(
            "SELECT estatus FROM adj_operacion WHERE id = :id",
            ['id' => $id]
        );

        if (!$actual) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }

        $ahora = $this->fechaHoraCdmx();

        $this->db->CRUD(
            "UPDATE adj_operacion
             SET estatus = :estatus, fecha_actualizacion = :fecha
             WHERE id = :id",
            ['estatus' => $estatusNuevo, 'fecha' => $ahora, 'id' => $id]
        );

        $this->db->CRUD(
            "INSERT INTO adj_historial_estatus
                (id_operacion, estatus_anterior, estatus_nuevo, id_usuario, fecha)
             VALUES
                (:id_op, :ant, :nuevo, :id_usr, :fecha)",
            [
                'id_op'  => $id,
                'ant'    => $actual['estatus'],
                'nuevo'  => $estatusNuevo,
                'id_usr' => $idUsuario,
                'fecha'  => $ahora,
            ]
        );

        $this->registrarBitacora($id, 'MOVIO A ETAPA: ' . strtoupper($estatusNuevo), $idUsuario, $nombreUsuario, $ahora);

        return ['success' => true];
    }

    // =========================================================================
    // AGREGAR OBSERVACI??N
    // =========================================================================

    public function agregarObservacion(int $idOperacion, string $etapa, string $area, int $idUsuario, string $texto, string $nombreUsuario = ''): array
    {
        $texto = trim($texto);
        if ($texto === '') {
            return ['success' => false, 'message' => 'La observaci?n no puede estar vac?a.'];
        }

        $ahora = $this->fechaHoraCdmx();
        $this->db->CRUD(
            "INSERT INTO adj_observacion
                (id_operacion, etapa, area, id_usuario, texto, fecha)
             VALUES
                (:id_op, :etapa, :area, :id_usr, :texto, :fecha)",
            [
                'id_op'  => $idOperacion,
                'etapa'  => $etapa,
                'area'   => $area,
                'id_usr' => $idUsuario,
                'texto'  => $texto,
                'fecha'  => $ahora,
            ]
        );

        $newId = $this->db->lastInsertId();

        $pref = 'AGREGÓ ACCIÓN DE TRAMO: ';
        $tail = mb_strlen($texto, 'UTF-8') > 60 ? '…' : '';
        $mid  = mb_strtoupper(mb_substr($texto, 0, 60, 'UTF-8'), 'UTF-8');
        $accionBit = $pref . $mid . $tail;
        $this->registrarBitacora($idOperacion, $accionBit, $idUsuario, $nombreUsuario, $ahora);

        return ['success' => true, 'id' => $newId, 'fecha' => $ahora];
    }

    // =========================================================================
    // ELIMINAR OPERACI??N (soft: no existe columna activo, se elimina real solo si no tiene historial)
    // =========================================================================

    public function eliminarOperacion(int $id): array
    {
        $op = $this->db->queryOne("SELECT id FROM adj_operacion WHERE id = :id", ['id' => $id]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }

        $this->db->CRUD("DELETE FROM adj_operacion WHERE id = :id", ['id' => $id]);
        return ['success' => true];
    }

    // =========================================================================
    // MIS ADJUDICACIONES ??? cr?ditos asignados al usuario en sesi?n
    // =========================================================================

    /**
     * Devuelve los cr?ditos activos asignados al usuario.
     * El bucket se enriquece en esta misma respuesta para evitar una segunda llamada HTTP.
     */
    /**
     * @param bool $incluirMorosidad Si false, no consulta Segund?metro (m?s r?pido; bucket queda en "???" hasta un segundo request).
     * @return array{creditos: array<int, array>, resumen_evidencias: array<int, array{total:int, rechazo_atn:int, all_sent:bool}>}
     */
    public function obtenerMisAdjudicaciones(int $idPersona, bool $incluirMorosidad = true): array
    {
        $slots    = self::MADJ_SLOTS_EVIDENCIA_MEDIA;
        $slotPh   = [];
        $params   = ['idPersona' => $idPersona, 'idPersonaUlt' => $idPersona];
        foreach ($slots as $i => $slot) {
            $k            = 'mslot' . $i;
            $slotPh[]     = ':' . $k;
            $params[$k]   = $slot;
        }
        $slotIn = implode(',', $slotPh);

        $fragRechazoAtn = $this->adjEvidenciaTieneColumnasAtn()
            ? "COUNT(DISTINCT CASE
                    WHEN slot IN ($slotIn) AND estatus IN ('pendiente_envio', 'recibido')
                         AND val_atn = 2
                    THEN slot ELSE NULL END
                ) AS rechazo_atn"
            : '0 AS rechazo_atn';

        $creditos = $this->db->queryAll(
            "SELECT
                aca.id_credito                                          AS id_credito,
                IF(aca.estatus = '1', 'Activo', 'Inactivo')            AS estado,
                DATE_FORMAT(aca.fecha_alta, '%Y-%m-%d %H:%i')          AS fecha_asignacion,
                DATE_FORMAT(aca.fecha_baja, '%Y-%m-%d %H:%i')          AS fecha_desasignacion,
                COALESCE(NULLIF(TRIM(ult_op.nombre_cliente), ''), '???') AS nombre_cliente,
                TRIM(CONCAT_WS(' ', per_alta.nombres, per_alta.apellidop)) AS asignado_por,
                aca.id                                                  AS id_asignacion,
                COALESCE(madj_ev.total, 0)                              AS madj_ev_total,
                COALESCE(madj_ev.pendiente, 0)                          AS madj_ev_pendiente,
                COALESCE(madj_ev.rechazo_atn, 0)                        AS madj_ev_rechazo_atn
            FROM asigna_creditos_adjudicacion aca
            INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
            LEFT JOIN persona per_alta ON per_alta.id = aca.alta
            INNER JOIN (
                SELECT ao.id, ao.id_credito, ao.nombre_cliente, ao.estatus
                FROM adj_operacion ao
                INNER JOIN (
                    SELECT aop.id_credito, MAX(aop.id) AS id_max
                    FROM adj_operacion aop
                    INNER JOIN asigna_creditos_adjudicacion acx
                        ON acx.id_credito = aop.id_credito AND acx.estatus = '1'
                    INNER JOIN personal_adjudicacion pax
                        ON pax.id = acx.id_personal_adj AND pax.id_persona = :idPersonaUlt
                    GROUP BY aop.id_credito
                ) m ON m.id_max = ao.id AND m.id_credito = ao.id_credito
            ) ult_op ON ult_op.id_credito = aca.id_credito
            LEFT JOIN (
                SELECT id_operacion,
                       COUNT(DISTINCT CASE
                           WHEN slot IN ($slotIn) AND estatus IN ('pendiente_envio', 'recibido')
                           THEN slot ELSE NULL END
                       ) AS total,
                       COUNT(DISTINCT CASE
                           WHEN slot IN ($slotIn) AND estatus = 'pendiente_envio'
                           THEN slot ELSE NULL END
                       ) AS pendiente,
                       $fragRechazoAtn
                FROM adj_evidencia
                GROUP BY id_operacion
            ) madj_ev ON madj_ev.id_operacion = ult_op.id
            WHERE pa.id_persona = :idPersona
              AND aca.estatus = '1'
              AND ult_op.estatus IN (
                  'en_transito',
                  'Recibido',
                  'Procesando IA',
                  'Revisión Recuperaciones',
                  'Cierre Documentado',
                  'Recepción',
                  'Retenciones'
              )
            ORDER BY aca.fecha_alta DESC",
            $params
        ) ?: [];

        $totalSlotsVista = count($slots);
        $resumenEvidencias = [];
        foreach ($creditos as &$c) {
            $c['bucket'] = '???';
            $idCred = (int) ($c['id_credito'] ?? 0);
            $total  = (int) ($c['madj_ev_total'] ?? 0);
            $pend   = (int) ($c['madj_ev_pendiente'] ?? 0);
            $rej    = (int) ($c['madj_ev_rechazo_atn'] ?? 0);
            unset($c['madj_ev_total'], $c['madj_ev_pendiente'], $c['madj_ev_rechazo_atn']);
            if ($idCred > 0) {
                $resumenEvidencias[$idCred] = [
                    'total'       => $total,
                    'rechazo_atn' => $rej,
                    'all_sent'    => $total >= $totalSlotsVista && $pend === 0 && $rej === 0,
                ];
            }
        }
        unset($c);

        $idsCreditos = array_values(array_unique(array_filter(array_map(
            static fn($c) => (int) ($c['id_credito'] ?? 0),
            $creditos
        ))));

        if ($incluirMorosidad && $idsCreditos !== []) {
            $morosidad = $this->obtenerMorosidadSegundometroPorCreditos($idsCreditos);
            foreach ($creditos as &$c) {
                $idKey = (string) ((int) ($c['id_credito'] ?? 0));
                $bucket = trim((string) (($morosidad[$idKey]['bucket'] ?? '')));
                if ($bucket !== '') {
                    $c['bucket'] = $bucket;
                }
            }
            unset($c);
        }

        return [
            'creditos'           => $creditos,
            'resumen_evidencias' => $resumenEvidencias,
        ];
    }

    /**
     * Lista dictámenes de Motos Adjudicadas (opción 13).
     * Extrae valores del JSON `form_response` en SQL (mismo patrón que Gestiones / JSON_TABLE legacy).
     * Enriquece nombre con `base_clientes`, luego Segundómetro (lote) y, si sigue vacío, API S2 estado de cuenta.
     *
     * @param int|null $limit Si no es null y > 0, solo se traen hasta $limit filas (se pide una fila extra en SQL para detectar si hay más datos).
     * @param int $offset Desplazamiento (p. ej. segundo lote tras el primer lote rápido).
     * @param bool $modoRapido Si es true, omite fuentes externas lentas para mostrar resultados iniciales más rápido.
     * @return array{rows: array<int, array<string, mixed>>, has_more: bool}
     */
    public function obtenerListaDictumsMotos(?int $limit = null, int $offset = 0, bool $modoRapido = false): array
    {
        $offset = max(0, $offset);

        $sqlBase = <<<'EOSQL'
SELECT
    d.id                           AS id,
    d.task_id                      AS task_id,
    CAST(t.credit_number AS UNSIGNED) AS id_credito,
    d.user_id                      AS legacy_user_id,
    d.created_at                   AS fecha_registro,
    d.lat                          AS lat,
    d.lng                          AS lng,
    TRIM(COALESCE(
        JSON_UNQUOTE(JSON_EXTRACT(MAX(CASE WHEN j.name = 'direccion_actual' THEN j.raw END), '$.value')), ''
    )) AS direccion_actual,
    TRIM(COALESCE(
        JSON_UNQUOTE(JSON_EXTRACT(MAX(CASE WHEN j.name = 'comentarios_generales' THEN j.raw END), '$.value')), ''
    )) AS comentarios_generales,
    TRIM(COALESCE(
        JSON_UNQUOTE(JSON_EXTRACT(MAX(CASE WHEN j.name = 'marca_y_modelo' THEN j.raw END), '$.value')), ''
    )) AS marca_modelo,
    TRIM(COALESCE(
        JSON_UNQUOTE(JSON_EXTRACT(MAX(CASE WHEN j.name = 'numero_de_serie' THEN j.raw END), '$.value')), ''
    )) AS numero_serie,
    TRIM(COALESCE(
        JSON_UNQUOTE(JSON_EXTRACT(MAX(CASE WHEN j.name = 'kilometraje' THEN j.raw END), '$.value')), ''
    )) AS kilometraje,
    TRIM(COALESCE(
        JSON_UNQUOTE(JSON_EXTRACT(MAX(CASE WHEN j.name = 'fecha_de_moto_recuperada' THEN j.raw END), '$.value')), ''
    )) AS fecha_moto_recuperada,
    TRIM(COALESCE(
        JSON_UNQUOTE(JSON_EXTRACT(MAX(CASE WHEN j.name = 'tomar_foto' THEN j.raw END), '$.value')), ''
    )) AS url_foto,
    TRIM(COALESCE(
        JSON_UNQUOTE(JSON_EXTRACT(MAX(CASE WHEN j.name = 'video_de_moto_recuperada' THEN j.raw END), '$.value')), ''
    )) AS url_video
FROM dictums d
INNER JOIN tasks t ON t.id = d.task_id
JOIN JSON_TABLE(
    JSON_UNQUOTE(d.form_response),
    '$[*]' COLUMNS (
        name VARCHAR(255) PATH '$.name',
        value VARCHAR(255) PATH '$.value',
        raw JSON PATH '$'
    )
) j
WHERE d.opciondictamen_id = :opcion
GROUP BY d.id, d.task_id, t.credit_number, d.user_id, d.created_at, d.lat, d.lng
ORDER BY d.id DESC
EOSQL;

        $hasMore = false;
        if ($limit !== null && $limit > 0) {
            $take = $limit + 1;
            $sql  = $sqlBase . ' LIMIT ' . (int) $take . ' OFFSET ' . (int) $offset;
        } elseif ($offset > 0) {
            // MySQL exige LIMIT cuando se usa OFFSET: tomar el resto de filas desde $offset.
            $sql = $sqlBase . ' LIMIT 2147483647 OFFSET ' . (int) $offset;
        } else {
            $sql = $sqlBase;
        }

        try {
            $legacyDb = new DatabaseLegacy();
            $rows = $legacyDb->queryAll($sql, ['opcion' => 13]) ?: [];
        } catch (\Throwable $e) {
            $rows = $this->db->queryAll($sql, ['opcion' => 13]) ?: [];
        }

        $creditosIds = [];
        foreach ($rows as $r) {
            $idc = isset($r['id_credito']) ? (int) $r['id_credito'] : 0;
            if ($idc > 0) {
                $creditosIds[$idc] = true;
            }
        }

        /** @var array<int, string> */
        $nombrePorCredito = [];
        if ($creditosIds !== []) {
            $lista = array_keys($creditosIds);
            sort($lista);
            $ph     = [];
            $params = [];
            foreach ($lista as $i => $id) {
                $k        = 'c' . $i;
                $ph[]     = ':' . $k;
                $params[$k] = $id;
            }
            try {
                $sky = $this->db->queryAll(
                    'SELECT id_credito, MAX(TRIM(nombre_completo_cliente)) AS nombre_completo_cliente
                     FROM base_clientes
                     WHERE id_credito IN (' . implode(',', $ph) . ')
                     GROUP BY id_credito',
                    $params
                ) ?: [];
                foreach ($sky as $s) {
                    $cid = (int) ($s['id_credito'] ?? 0);
                    $nom = trim((string) ($s['nombre_completo_cliente'] ?? ''));
                    if ($cid > 0 && $nom !== '') {
                        $nombrePorCredito[$cid] = $nom;
                    }
                }
            } catch (\Throwable $e) {
                // Sin Sky Logic disponible para esos créditos: se deja el nombre vacío.
            }
        }

        foreach ($rows as &$r) {
            $cid                       = isset($r['id_credito']) ? (int) $r['id_credito'] : 0;
            $r['nombre_cliente']       = $nombrePorCredito[$cid] ?? '';
        }
        unset($r);

        // Completar faltantes con caché local de nombres (rápido, sin tocar servicios externos).
        $idsSinNombre = [];
        foreach ($rows as $r) {
            $cid = (int) ($r['id_credito'] ?? 0);
            if ($cid > 0 && trim((string) ($r['nombre_cliente'] ?? '')) === '') {
                $idsSinNombre[$cid] = true;
            }
        }
        if ($idsSinNombre !== []) {
            $cacheNombres = $this->obtenerNombresClienteCachePorCreditos(array_keys($idsSinNombre));
            if ($cacheNombres !== []) {
                foreach ($rows as &$r) {
                    $cid = (int) ($r['id_credito'] ?? 0);
                    if ($cid <= 0 || trim((string) ($r['nombre_cliente'] ?? '')) !== '') {
                        continue;
                    }
                    if (!empty($cacheNombres[$cid])) {
                        $r['nombre_cliente'] = (string) $cacheNombres[$cid];
                    }
                }
                unset($r);
            }
        }

        if (!$modoRapido) {
            $idsSinNombre = [];
            foreach ($rows as $r) {
                $cid = (int) ($r['id_credito'] ?? 0);
                if ($cid > 0 && trim((string) ($r['nombre_cliente'] ?? '')) === '') {
                    $idsSinNombre[$cid] = true;
                }
            }
            $listaSinNombre = array_keys($idsSinNombre);
            if ($listaSinNombre !== []) {
                $mapSm = $this->obtenerMorosidadSegundometroPorCreditos($listaSinNombre);
                foreach ($rows as &$r) {
                    $cid = (int) ($r['id_credito'] ?? 0);
                    if ($cid <= 0 || trim((string) ($r['nombre_cliente'] ?? '')) !== '') {
                        continue;
                    }
                    $key   = (string) $cid;
                    $nomSm = trim((string) (($mapSm[$key]['nombre_cliente'] ?? '')));
                    if ($nomSm !== '' && strcasecmp($nomSm, 'No disponible') !== 0) {
                        $r['nombre_cliente'] = $nomSm;
                    }
                }
                unset($r);

                $adjModel = new AdjudicacionModel();
                foreach ($rows as &$r) {
                    $cid = (int) ($r['id_credito'] ?? 0);
                    if ($cid <= 0 || trim((string) ($r['nombre_cliente'] ?? '')) !== '') {
                        continue;
                    }
                    $api = $adjModel->buscarCreditoPorId($cid);
                    if (!empty($api['success']) && !empty($api['credito'])) {
                        $nomApi = trim((string) ($api['credito']['nombre_cliente'] ?? ''));
                        if ($nomApi !== '' && strcasecmp($nomApi, 'Sin nombre') !== 0) {
                            $r['nombre_cliente'] = $nomApi;
                        }
                    }
                }
                unset($r);
            }

            // Guardar en caché local todos los nombres ya resueltos para próximas cargas rápidas.
            $aGuardar = [];
            foreach ($rows as $r) {
                $cid = (int) ($r['id_credito'] ?? 0);
                $nom = trim((string) ($r['nombre_cliente'] ?? ''));
                if ($cid > 0 && $nom !== '' && strcasecmp($nom, 'Sin nombre') !== 0 && strcasecmp($nom, 'No disponible') !== 0) {
                    $aGuardar[$cid] = $nom;
                }
            }
            if ($aGuardar !== []) {
                $this->guardarNombresClienteCachePorCredito($aGuardar);
            }
        }

        if ($limit !== null && $limit > 0) {
            if (count($rows) > $limit) {
                $hasMore = true;
                array_pop($rows);
            }
        }

        $idsCredSeg = [];
        foreach ($rows as $r) {
            $idc = (int) ($r['id_credito'] ?? 0);
            if ($idc > 0) {
                $idsCredSeg[] = $idc;
            }
        }
        $segMap = $this->obtenerSeguimientoMaDictamenPorCreditos($idsCredSeg);
        $s2Map  = $this->obtenerMapaResumenS2ModalDictamenPorCreditos($idsCredSeg);
        $asigActMap = $this->obtenerMapaAsignacionActivaPorCreditos($idsCredSeg);
        foreach ($rows as &$r) {
            $idc                     = (int) ($r['id_credito'] ?? 0);
            $s                       = $segMap[$idc] ?? null;
            $aplicaSeg               = $s['aplica'] ?? null;
            $r['ma_seg_comentarios'] = $s['comentarios'] ?? '';
            $r['ma_seg_aplica']      = $aplicaSeg;
            $r['ma_seg_asignacion_ok'] = ($aplicaSeg === 0)
                ? true
                : ($aplicaSeg === 1 ? !empty($asigActMap[$idc]) : null);
            $r['s2_modal_resumen']   = $s2Map[$idc] ?? null;
        }
        unset($r);

        // Lista dictámenes: solo pendientes de «Seguimiento interno»; si ya se guardó (sí/no recolección), no listar.
        $rows = array_values(array_filter($rows, static function (array $r): bool {
            $com = trim((string) ($r['ma_seg_comentarios'] ?? ''));
            if ($com === '') {
                return true;
            }
            $ap = $r['ma_seg_aplica'] ?? null;
            if ($ap === null || $ap === '') {
                return true;
            }
            $apNum = is_numeric($ap) ? (int) $ap : -1;

            return !($apNum === 0 || $apNum === 1);
        }));

        $this->enriquecerGestorLegacyListaDictums($rows);

        return ['rows' => $rows, 'has_more' => $hasMore];
    }

    /**
     * dictums.user_id (Legacy) → users.external_id → __SPARTA_SECRET_REDACTED__.persona.numero_empleado → nombre del gestor.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function enriquecerGestorLegacyListaDictums(array &$rows): void
    {
        $uids = [];
        foreach ($rows as $r) {
            $u = (int) ($r['legacy_user_id'] ?? 0);
            if ($u > 0) {
                $uids[$u] = true;
            }
        }
        $lista = array_keys($uids);
        if ($lista === []) {
            foreach ($rows as &$r) {
                $r['gestor_legacy_nombre']     = '';
                $r['gestor_legacy_id_persona'] = null;
            }
            unset($r);

            return;
        }
        $map = $this->obtenerDatosGestorLegacyPorDictumUserIds($lista);
        foreach ($rows as &$r) {
            $u = (int) ($r['legacy_user_id'] ?? 0);
            $d = ($u > 0 && isset($map[$u])) ? $map[$u] : null;
            $r['gestor_legacy_nombre']      = $d ? (string) ($d['nombre'] ?? '') : '';
            $r['gestor_legacy_id_persona']  = ($d && !empty($d['id_persona'])) ? (int) $d['id_persona'] : null;
        }
        unset($r);
    }

    /**
     * @param  int[]  $legacyUserIds  users.id en base Legacy (__SPARTA_SECRET_REDACTED__)
     * @return array<int, array{nombre: string, id_persona: int}>
     */
    private function obtenerDatosGestorLegacyPorDictumUserIds(array $legacyUserIds): array
    {
        $legacyUserIds = array_values(array_unique(array_filter(array_map('intval', $legacyUserIds), static fn ($v) => $v > 0)));
        if ($legacyUserIds === []) {
            return [];
        }

        $userRows = [];
        try {
            $legacyDb = new DatabaseLegacy();
            $ph       = [];
            $params   = [];
            foreach ($legacyUserIds as $i => $id) {
                $k          = 'lu'.$i;
                $ph[]       = ':'.$k;
                $params[$k] = $id;
            }
            $userRows = $legacyDb->queryAll(
                'SELECT id, TRIM(COALESCE(external_id, \'\')) AS external_id
                 FROM users
                 WHERE id IN ('.implode(',', $ph).')',
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }

        $userIdToExternal = [];
        $externals        = [];
        foreach ($userRows as $ur) {
            $uid = (int) ($ur['id'] ?? 0);
            $ext = trim((string) ($ur['external_id'] ?? ''));
            if ($uid <= 0 || $ext === '') {
                continue;
            }
            $userIdToExternal[$uid] = $ext;
            $externals[$ext]        = true;
        }
        if ($externals === []) {
            return [];
        }

        $extList = array_keys($externals);
        sort($extList);
        $ph2     = [];
        $params2 = [];
        foreach ($extList as $i => $ext) {
            $k           = 'ne'.$i;
            $ph2[]       = ':'.$k;
            $params2[$k] = $ext;
        }

        try {
            $perRows = $this->db->queryAll(
                'SELECT id,
                        TRIM(numero_empleado) AS numero_empleado,
                        TRIM(CONCAT_WS(\' \', nombres, segundo_nombre, apellidop, apellidom)) AS nombre_gestor
                 FROM persona
                 WHERE TRIM(numero_empleado) IN ('.implode(',', $ph2).')',
                $params2
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }

        $extToDatos = [];
        foreach ($perRows as $pr) {
            $ne = trim((string) ($pr['numero_empleado'] ?? ''));
            $ng = trim((string) ($pr['nombre_gestor'] ?? ''));
            $pid = (int) ($pr['id'] ?? 0);
            if ($ne === '' || $ng === '' || $pid <= 0 || isset($extToDatos[$ne])) {
                continue;
            }
            $extToDatos[$ne] = [
                'nombre'       => $ng,
                'id_persona'   => $pid,
            ];
        }

        $out = [];
        foreach ($userIdToExternal as $uid => $ext) {
            $d = $extToDatos[$ext] ?? null;
            if ($d !== null && ($d['nombre'] ?? '') !== '') {
                $out[$uid] = $d;
            }
        }

        return $out;
    }

    private function asegurarTablaCacheNombreCredito(): bool
    {
        // Usar la misma tabla adj_s2_cache_dictamen para no crear una tabla aparte.
        return $this->asegurarTablaCacheResumenS2ModalDictamen();
    }

    /**
     * @param int[] $idsCreditos
     * @return array<int,string> [id_credito => nombre_cliente]
     */
    private function obtenerNombresClienteCachePorCreditos(array $idsCreditos): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsCreditos), static fn($v) => $v > 0)));
        if ($ids === []) {
            return [];
        }
        if (!$this->asegurarTablaCacheNombreCredito()) {
            return [];
        }
        $ph = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $k = 'id' . $i;
            $ph[] = ':' . $k;
            $params[$k] = $id;
        }
        try {
            $rows = $this->db->queryAll(
                'SELECT id_credito, nombre_cliente
                 FROM adj_s2_cache_dictamen
                 WHERE id_credito IN (' . implode(',', $ph) . ')',
                $params
            ) ?: [];
        } catch (\Throwable $e) {
            return [];
        }
        $map = [];
        foreach ($rows as $r) {
            $cid = (int) ($r['id_credito'] ?? 0);
            $nom = trim((string) ($r['nombre_cliente'] ?? ''));
            if ($cid > 0 && $nom !== '') {
                $map[$cid] = $nom;
            }
        }
        return $map;
    }

    /**
     * @param array<int,string> $nombresPorCredito [id_credito => nombre_cliente]
     */
    private function guardarNombresClienteCachePorCredito(array $nombresPorCredito): void
    {
        if ($nombresPorCredito === []) {
            return;
        }
        if (!$this->asegurarTablaCacheNombreCredito()) {
            return;
        }
        $ahora = date('Y-m-d H:i:s');
        foreach ($nombresPorCredito as $cid => $nombre) {
            $idCredito = (int) $cid;
            $nom = trim((string) $nombre);
            if ($idCredito <= 0 || $nom === '') {
                continue;
            }
            try {
                $this->db->CRUD(
                    "INSERT INTO adj_s2_cache_dictamen (
                        id_credito, nombre_cliente, nombre_fuente, nombre_actualizado_at, consultado_at, actualizado_at
                    ) VALUES (
                        :id_credito, :nombre_cliente, :nombre_fuente, :nombre_actualizado_at, :consultado_at, :actualizado_at
                    )
                    ON DUPLICATE KEY UPDATE
                        nombre_cliente = VALUES(nombre_cliente),
                        nombre_fuente = VALUES(nombre_fuente),
                        nombre_actualizado_at = VALUES(nombre_actualizado_at),
                        actualizado_at = VALUES(actualizado_at)",
                    [
                        'id_credito' => $idCredito,
                        'nombre_cliente' => $nom,
                        'nombre_fuente' => 'dictamen_full',
                        'nombre_actualizado_at' => $ahora,
                        'consultado_at' => $ahora,
                        'actualizado_at' => $ahora,
                    ]
                );
            } catch (\Throwable $e) {
                // No bloquear flujo por fallo de caché.
            }
        }
    }

    /**
     * Resuelve nombres por lote (cache local + base_clientes + segundómetro) y los persiste en caché.
     *
     * @param int[] $idsCreditos
     * @return array<int,string> [id_credito => nombre_cliente]
     */
    public function resolverNombresClienteDictamenesPorCreditos(array $idsCreditos): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsCreditos), static fn($v) => $v > 0)));
        if ($ids === []) {
            return [];
        }

        $nombres = $this->obtenerNombresClienteCachePorCreditos($ids);

        $faltantes = array_values(array_filter($ids, static fn($id) => empty($nombres[$id])));
        if ($faltantes !== []) {
            $ph = [];
            $params = [];
            foreach ($faltantes as $i => $id) {
                $k = 'c' . $i;
                $ph[] = ':' . $k;
                $params[$k] = $id;
            }
            try {
                $rowsBase = $this->db->queryAll(
                    'SELECT id_credito, MAX(TRIM(nombre_completo_cliente)) AS nombre_completo_cliente
                     FROM base_clientes
                     WHERE id_credito IN (' . implode(',', $ph) . ')
                     GROUP BY id_credito',
                    $params
                ) ?: [];
                foreach ($rowsBase as $r) {
                    $cid = (int) ($r['id_credito'] ?? 0);
                    $nom = trim((string) ($r['nombre_completo_cliente'] ?? ''));
                    if ($cid > 0 && $nom !== '') {
                        $nombres[$cid] = $nom;
                    }
                }
            } catch (\Throwable $e) {
                // Ignorar y continuar con siguiente fuente.
            }
        }

        $faltantes = array_values(array_filter($ids, static fn($id) => empty($nombres[$id])));
        if ($faltantes !== []) {
            $mapSm = $this->obtenerMorosidadSegundometroPorCreditos($faltantes);
            foreach ($faltantes as $idc) {
                $key = (string) $idc;
                $nomSm = trim((string) (($mapSm[$key]['nombre_cliente'] ?? '')));
                if ($nomSm !== '' && strcasecmp($nomSm, 'No disponible') !== 0 && strcasecmp($nomSm, 'Sin nombre') !== 0) {
                    $nombres[$idc] = $nomSm;
                }
            }
        }

        if ($nombres !== []) {
            $this->guardarNombresClienteCachePorCredito($nombres);
        }

        return $nombres;
    }

    /**
     * Morosidad y saldo desde __SPARTA_SECRET_REDACTED__ (Segund?metro). Llamada as?ncrona desde la vista.
     *
     * @param int[] $idsCreditos
     * @return array<string, array{nombre_cliente: string, dias_mora: int, bucket: string, saldo: float}>
     */
    public function obtenerMorosidadSegundometroPorCreditos(array $idsCreditos): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsCreditos), fn($v) => $v > 0)));
        if ($ids === []) {
            return [];
        }

        $placeholders = [];
        $params       = [];
        foreach ($ids as $i => $id) {
            $key            = 'id' . $i;
            $placeholders[] = ':' . $key;
            $params[$key]   = $id;
        }
        $inStr = implode(',', $placeholders);

        $mapa = [];
        try {
            $dbS2 = new \Core\DatabaseSegundometro();

            $rows = $dbS2->queryAll(
                "SELECT Id_credito, Nombre_cliente, Dias_mora, Bucket_Morosidad_Real,
                        Saldo_total_capital AS saldo
                 FROM tbl_segundometro_semana
                 WHERE Id_credito IN ($inStr)",
                $params
            ) ?: [];

            foreach ($rows as $r) {
                $mapa[(string) (int) $r['Id_credito']] = $r;
            }

            $faltantes = array_filter($ids, fn($id) => !isset($mapa[(string) $id]));
            if ($faltantes !== []) {
                $ph2 = [];
                $p2  = [];
                foreach (array_values($faltantes) as $i => $id) {
                    $k        = 'h' . $i;
                    $ph2[]    = ':' . $k;
                    $p2[$k]   = $id;
                }
                $rowsH = $dbS2->queryAll(
                    'SELECT Id_credito,
                            MAX(Nombre_cliente)        AS Nombre_cliente,
                            MAX(Dias_mora)             AS Dias_mora,
                            MAX(Bucket_Morosidad_Real) AS Bucket_Morosidad_Real,
                            MAX(Saldo_total_capital)   AS saldo
                     FROM tbl_segundometro_histo
                     WHERE Id_credito IN (' . implode(',', $ph2) . ')
                     GROUP BY Id_credito',
                    $p2
                ) ?: [];
                foreach ($rowsH as $r) {
                    $mapa[(string) (int) $r['Id_credito']] = $r;
                }
            }
        } catch (\Throwable $e) {
            return [];
        }

        $out = [];
        foreach ($mapa as $idKey => $r) {
            $out[$idKey] = [
                'nombre_cliente' => (string) ($r['Nombre_cliente'] ?? 'No disponible'),
                'dias_mora'      => (int) ($r['Dias_mora'] ?? 0),
                'bucket'         => (string) ($r['Bucket_Morosidad_Real'] ?? '???'),
                'saldo'          => isset($r['saldo']) ? (float) $r['saldo'] : 0.0,
            ];
        }

        return $out;
    }

    // =========================================================================
    // EVIDENCIAS POR CR??DITO (mis_adjudicaciones)
    // =========================================================================

    /**
     * Devuelve el total de evidencias cargadas por cada cr?dito solicitado,
     * tomando la operaci?n m?s reciente por id_credito.
     *
     * @param int[] $idsCreditos
     * @return array<int,int> [id_credito => total_evidencias]
     */
    public function obtenerResumenEvidenciasPorCreditos(array $idsCreditos): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsCreditos), fn($v) => $v > 0)));
        if (empty($ids)) {
            return [];
        }

        $slotsPermitidos = self::MADJ_SLOTS_EVIDENCIA_MEDIA;

        $placeholders = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $k = 'id' . $i;
            $placeholders[] = ':' . $k;
            $params[$k] = $id;
        }
        $inStr = implode(',', $placeholders);

        $slotPh = [];
        foreach ($slotsPermitidos as $i => $slot) {
            $k = 'slot' . $i;
            $slotPh[] = ':' . $k;
            $params[$k] = $slot;
        }
        $slotIn = implode(',', $slotPh);

        $fragRechazoAtn = $this->adjEvidenciaTieneColumnasAtn()
            ? "COUNT(DISTINCT CASE
                    WHEN slot IN ($slotIn) AND estatus IN ('pendiente_envio', 'recibido')
                         AND val_atn = 2
                    THEN slot ELSE NULL END
                ) AS rechazo_atn"
            : '0 AS rechazo_atn';

        $rows = $this->db->queryAll(
            "SELECT ult.id_credito,
                    COALESCE(ev.total, 0)       AS total,
                    COALESCE(ev.pendiente, 0)   AS pendiente,
                    COALESCE(ev.rechazo_atn, 0) AS rechazo_atn
             FROM (
                SELECT id_credito, MAX(id) AS max_id
                FROM adj_operacion
                WHERE id_credito IN ($inStr)
                GROUP BY id_credito
             ) ult
             LEFT JOIN (
                SELECT id_operacion,
                       COUNT(DISTINCT CASE
                           WHEN slot IN ($slotIn) AND estatus IN ('pendiente_envio', 'recibido')
                           THEN slot ELSE NULL END
                       ) AS total,
                       COUNT(DISTINCT CASE
                           WHEN slot IN ($slotIn) AND estatus = 'pendiente_envio'
                           THEN slot ELSE NULL END
                       ) AS pendiente,
                       $fragRechazoAtn
                FROM adj_evidencia
                WHERE id_operacion IN (
                    SELECT MAX(id)
                    FROM adj_operacion
                    WHERE id_credito IN ($inStr)
                    GROUP BY id_credito
                )
                GROUP BY id_operacion
             ) ev ON ev.id_operacion = ult.max_id",
            $params
        ) ?: [];

        $resumen = [];
        foreach ($rows as $r) {
            $id       = (int) ($r['id_credito'] ?? 0);
            $total    = (int) ($r['total']      ?? 0);
            $pendiente = (int) ($r['pendiente'] ?? 0);
            $rechazoAtn = (int) ($r['rechazo_atn'] ?? 0);
            if ($id > 0) {
                $totalSlotsVista = count($slotsPermitidos);
                // No "todo enviado a revisi?n" si Atenci?n rechaz? alguna media: el gestor debe reemplazar y reenviar.
                $resumen[$id] = [
                    'total'         => $total,
                    'rechazo_atn'   => $rechazoAtn,
                    'all_sent'      => $total >= $totalSlotsVista && $pendiente === 0 && $rechazoAtn === 0,
                ];
            }
        }

        return $resumen;
    }
    // =========================================================================
    // DATOS DE MOTO + LOG?STICOS (Mis Adjudicaciones)
    // Campos en adj_operacion: moto_marca, moto_modelo, moto_anio, moto_color,
    //   moto_no_serie, moto_no_motor, moto_placas,
    //   log_direccion, log_ciudad, log_estado, log_lugar_resguardo, log_lugar_otro, log_telefono,
    //   responsable_entrega (nombre del responsable de resguardo / ubicación actual),
    //   datos_moto_at, datos_moto_by
    // Datos de moto y logísticos: columnas en adj_operacion (esquema actualizado en BD)
    // =========================================================================

    /** Columnas persistibles en adj_operacion para datos de moto (incluye hist?rico marca/modelo/serie/placas). */
    /**
     * Columnas de moto en adj_operacion (vista "No. de Motor" = moto_no_motor / num_motor; VIN = moto_no_serie; placa = moto_placas).
     */
    private const CAMPOS_DATOS_MOTO = [
        'moto_marca', 'moto_modelo', 'moto_anio', 'moto_color',
        'moto_no_serie', 'moto_no_motor', 'moto_placas',
        'marca', 'modelo', 'serie', 'num_motor', 'placas',
        'log_direccion', 'log_ciudad',
        'log_estado', 'log_lugar_resguardo', 'log_lugar_otro', 'log_telefono',
        'responsable_entrega',
    ];

    /** ISO 3779 / NHTSA: VIN de 17 caracteres m?x.; sin I, O, Q (motocicletas incluidas). */
    private const MADJ_VIN_MAX_LEN = 17;
    private const MADJ_VIN_MIN_LEN = 8;

    /** N?mero de motor (fabricante): l?mite superior t?pico en motos; sin est?ndar ?nico como el VIN. */
    private const MADJ_NO_MOTOR_MAX_LEN = 24;

    /** Placa motocicleta M?xico (NOM-001-SCT-2-2016 / formatos por estado): serie corta; margen por variantes. */
    private const MADJ_PLACAS_MOTO_MAX_LEN = 9;
    private const MADJ_PLACAS_MOTO_MIN_LEN = 4;

    /**
     * Validaci?n de formato para Mis adjudicaciones (motocicleta).
     * @return string|null mensaje de error o null si todo OK
     */
    private function validarDatosMotoFormatos(array $datos): ?string
    {
        if (array_key_exists('moto_no_serie', $datos)) {
            $vin = strtoupper(preg_replace('/\s+/u', '', (string) $datos['moto_no_serie']));
            $len = strlen($vin);
            if ($len < self::MADJ_VIN_MIN_LEN || $len > self::MADJ_VIN_MAX_LEN) {
                return 'El n?mero de serie (VIN) debe tener entre '
                    . self::MADJ_VIN_MIN_LEN . ' y ' . self::MADJ_VIN_MAX_LEN
                    . ' caracteres (ISO 3779 para motocicletas: m?ximo ' . self::MADJ_VIN_MAX_LEN . ').';
            }
            if (!preg_match('/^[A-HJ-NPR-Z0-9]+$/', $vin)) {
                return 'El VIN solo puede incluir letras (sin I, O ni Q) y d?gitos, sin espacios.';
            }
        }

        if (array_key_exists('moto_no_motor', $datos)) {
            $motor = strtoupper(preg_replace('/\s+/u', '', (string) $datos['moto_no_motor']));
            if ($motor === '') {
                return 'El n?mero de motor es obligatorio.';
            }
            if (strlen($motor) > self::MADJ_NO_MOTOR_MAX_LEN) {
                return 'El n?mero de motor admite como m?ximo '
                    . self::MADJ_NO_MOTOR_MAX_LEN
                    . ' caracteres (letras, n?meros y guion), t?pico en motocicletas.';
            }
            if (!preg_match('/^[A-Z0-9\-]+$/', $motor)) {
                return 'El n?mero de motor solo puede incluir letras, n?meros y guiones.';
            }
        }

        if (array_key_exists('moto_placas', $datos)) {
            $placas = strtoupper(preg_replace('/\s+/u', '', (string) $datos['moto_placas']));
            $lp     = strlen($placas);
            if ($lp < self::MADJ_PLACAS_MOTO_MIN_LEN || $lp > self::MADJ_PLACAS_MOTO_MAX_LEN) {
                return 'Las placas de motocicleta deben tener entre '
                    . self::MADJ_PLACAS_MOTO_MIN_LEN . ' y ' . self::MADJ_PLACAS_MOTO_MAX_LEN
                    . ' caracteres (en M?xico el formato de serie suele ser corto, p. ej. Y001AA).';
            }
            if (!preg_match('/^[A-Z0-9\-]+$/', $placas)) {
                return 'Las placas solo pueden incluir letras, n?meros y guion.';
            }
        }

        if (array_key_exists('moto_color', $datos)) {
            $color = trim((string) $datos['moto_color']);
            if ($color !== '' && !preg_match('/^[a-zA-Z������������\s]+$/u', $color)) {
                return 'El color debe contener solo letras (y espacios), sin n�meros.';
            }
        }

        if (array_key_exists('log_lugar_resguardo', $datos)) {
            $lr = strtolower(trim((string) $datos['log_lugar_resguardo']));
            $permitidos = ['mi_domicilio', 'sucursal', 'otro'];
            if ($lr === '' || !in_array($lr, $permitidos, true)) {
                return 'Selecciona un lugar de resguardo válido.';
            }
            if ($lr === 'otro') {
                $otro = trim((string) ($datos['log_lugar_otro'] ?? ''));
                if ($otro === '') {
                    return 'Indica cuál es el lugar de resguardo cuando eliges «Otro».';
                }
                if (mb_strlen($otro) > 200) {
                    return '«Indicar cuál» admite como máximo 200 caracteres.';
                }
                if (!preg_match('/^[\p{L}\p{N}\s\'\.\,\-\#\/]+$/u', $otro)) {
                    return '«Indicar cuál» solo puede incluir letras, números y signos básicos.';
                }
            }
        }

        if (array_key_exists('responsable_entrega', $datos)) {
            $nom = trim((string) $datos['responsable_entrega']);
            if ($nom === '') {
                return 'Indica el nombre del responsable de resguardo.';
            }
            $len = mb_strlen($nom);
            if ($len < 2 || $len > 160) {
                return 'Responsable de resguardo: entre 2 y 160 caracteres.';
            }
            if (!preg_match('/^[\p{L}\p{M}\s\.\'\-]+$/u', $nom)) {
                return 'El nombre del responsable solo puede incluir letras, espacios, punto y guion.';
            }
        }

        return null;
    }

    /**
     * REPUVE: consulta una sola vez por cr?dito y reutiliza el registro guardado.
     * Si existe registro PROCESANDO, solo consulta estatus por UUID (sin relanzar POST).
     */
    public function consultarRepuvePorCredito(int $idCredito, int $idUsuario = 0): array
    {
        return $this->consultarRepuvePorCreditoCore($idCredito, $idUsuario, null);
    }

    /**
     * Consulta REPUVE por placa o VIN (vista dedicada).
     * Nubarium usa las claves plate o vin en el POST de consultaRepuve.
     *
     * @param  string  $tipo  plate | vin
     */
    public function consultarRepuveConCriterio(int $idCredito, string $tipo, string $valorRaw, int $idUsuario = 0): array
    {
        $tipo = strtolower(trim($tipo));
        if (!in_array($tipo, ['plate', 'vin'], true)) {
            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                'success' => false,
                'message' => 'El criterio debe ser placa (plate) o VIN (vin).',
            ]);
        }
        $val = strtoupper(preg_replace('/\s+/u', '', trim($valorRaw)));
        if ($val === '') {
            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                'success' => false,
                'message' => 'Indica la placa o el número de serie (VIN) según el tipo elegido.',
            ]);
        }
        if ($tipo === 'vin') {
            if (strlen($val) < 8) {
                return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                    'success' => false,
                    'message' => 'El VIN parece incompleto.',
                ]);
            }
        } else {
            if (strlen($val) < 4) {
                return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                    'success' => false,
                    'message' => 'La placa parece incompleta.',
                ]);
            }
        }

        return $this->consultarRepuvePorCreditoCore($idCredito, $idUsuario, [
            'ok'    => true,
            'field' => $tipo,
            'value' => $val,
        ]);
    }

    /**
     * @param  array|null  $criterioForzado  null = placa o VIN en adj_operacion; o ['ok'=>true,'field'=>'plate|vin','value'=>...]
     */
    private function consultarRepuvePorCreditoCore(int $idCredito, int $idUsuario, ?array $criterioForzado): array
    {
        if ($idCredito <= 0) {
            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, ['success' => false, 'message' => 'ID de crédito inválido.']);
        }

        $this->repuveAsegurarTabla();
        $this->repuveAsegurarTablaLog();

        $cfg = $this->repuveCargarConfig();
        if (empty($cfg['configured'])) {
            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                'success'     => false,
                'unavailable' => true,
                'message'     => 'REPUVE no está configurado. Faltan REPUVE_API_BASE_URL y/o REPUVE_API_KEY en config_api.',
            ]);
        }

        $row = $this->db->queryOne(
            'SELECT * FROM adj_repuve_consulta WHERE id_credito = :id LIMIT 1',
            ['id' => $idCredito]
        );

        // Costo API: solo una consulta de inicio (POST) por id_credito. Si ya hay fila, no volver a cobrar;
        // en PROCESANDO solo se hacen GET de estatus (mismo UUID).
        if ($row) {
            $estado = strtoupper(trim((string) ($row['estado'] ?? '')));
            if ($estado === 'COMPLETADO' || $estado === 'ERROR') {
                $datosMoto = $this->repuveDatosMotoDesdeFila($row);
                $out = $this->repuveConstruirRespuestaConsulta(
                    $idCredito,
                    $row,
                    $datosMoto,
                    true,
                    'Datos REPUVE cargados desde caché.'
                );
                $out = $this->repuveAdjuntarRespuestasTecnicas($row, $out);

                return $this->repuveEnriquecerResultado($idCredito, $idUsuario, $out);
            }

            $uuid = trim((string) ($row['uuid'] ?? ''));
            if ($uuid !== '') {
                $estatus = $this->repuveSondearEstatus($cfg, $uuid);
                if (!empty($estatus['terminal'])) {
                    $this->repuveActualizarFilaFinal($idCredito, $estatus);
                    $row = $this->db->queryOne(
                        'SELECT * FROM adj_repuve_consulta WHERE id_credito = :id LIMIT 1',
                        ['id' => $idCredito]
                    );
                    $datosMoto = $this->repuveDatosMotoDesdeFila($row ?: []);
                    $out = $this->repuveConstruirRespuestaConsulta(
                        $idCredito,
                        $row ?: [],
                        $datosMoto,
                        true,
                        'Datos REPUVE actualizados desde estatus.'
                    );
                    $out = $this->repuveAdjuntarRespuestasTecnicas($row, $out);

                    return $this->repuveEnriquecerResultado($idCredito, $idUsuario, $out);
                }

                $ultJson = is_array($estatus['ultimo_estatus'] ?? null) ? $estatus['ultimo_estatus'] : null;
                $out = [
                    'success'    => false,
                    'from_cache' => true,
                    'id_credito' => $idCredito,
                    'repuve'     => [
                        'estado'       => 'PROCESANDO',
                        'message_code' => null,
                        'mensaje'      => 'Consulta REPUVE en proceso. Intenta nuevamente en unos segundos.',
                    ],
                    'message'    => 'Consulta REPUVE en proceso. Intenta nuevamente en unos segundos.',
                ];
                $out = $this->repuveAdjuntarRespuestasTecnicas($row, $out, $ultJson);

                return $this->repuveEnriquecerResultado($idCredito, $idUsuario, $out);
            }

            // Hay registro previo pero sin UUID: no repetir el POST de consulta (costo por crédito).
            $unico = [
                'success'                 => false,
                'from_cache'              => true,
                'id_credito'              => $idCredito,
                'repuve_consulta_unica'   => true,
                'message'                 => 'Este crédito ya tiene un intento de consulta REPUVE registrado. Solo se permite una consulta pagada por crédito. Si hubo error, contacte a sistemas.',
                'repuve'                  => [
                    'estado'  => (string) ($row['estado'] ?? ''),
                    'mensaje' => (string) ($row['mensaje'] ?? ''),
                ],
            ];

            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, $this->repuveAdjuntarRespuestasTecnicas($row, $unico));
        }

        $criterio = $criterioForzado ?? $this->repuveResolverCriterio($idCredito);
        if (empty($criterio['ok'])) {
            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                'success' => false,
                'message' => (string) ($criterio['message'] ?? 'No se pudo determinar criterio de búsqueda.'),
            ]);
        }

        if ($idUsuario > 0 && $this->repuveContarConsultasHoy($idUsuario) >= self::REPUVE_CONSULTAS_MAX_DIA) {
            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                'success'          => false,
                'message'          => 'Alcanzaste el máximo de ' . self::REPUVE_CONSULTAS_MAX_DIA . ' consultas REPUVE por día. Intenta mañana.',
                'limite_alcanzado' => true,
            ]);
        }

        $inicio = $this->repuveIniciarConsulta(
            $cfg,
            (string) $criterio['field'],
            (string) $criterio['value'],
            $idUsuario,
            $idCredito
        );

        $this->repuveGuardarInicio(
            $idCredito,
            (string) $criterio['field'],
            (string) $criterio['value'],
            $inicio
        );

        if (!empty($inicio['ok']) && $idUsuario > 0) {
            $this->repuveRegistrarUsoConsulta($idUsuario, $idCredito);
        }

        if (empty($inicio['ok'])) {
            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                'success'    => false,
                'id_credito' => $idCredito,
                'message'    => (string) ($inicio['mensaje'] ?? 'No se pudo iniciar la consulta REPUVE.'),
            ]);
        }

        $uuid = (string) ($inicio['uuid'] ?? '');
        if ($uuid === '') {
            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, [
                'success'    => false,
                'id_credito' => $idCredito,
                'message'    => 'REPUVE no devolvió UUID de seguimiento.',
            ]);
        }

        $estatus = $this->repuveSondearEstatus($cfg, $uuid);
        if (!empty($estatus['terminal'])) {
            $this->repuveActualizarFilaFinal($idCredito, $estatus);
            $row = $this->db->queryOne(
                'SELECT * FROM adj_repuve_consulta WHERE id_credito = :id LIMIT 1',
                ['id' => $idCredito]
            );
            $datosMoto = $this->repuveDatosMotoDesdeFila($row ?: []);
            $out = $this->repuveConstruirRespuestaConsulta(
                $idCredito,
                $row ?: [],
                $datosMoto,
                false,
                'Datos REPUVE consultados correctamente.'
            );
            $out = $this->repuveAdjuntarRespuestasTecnicas($row ?: [], $out);

            return $this->repuveEnriquecerResultado($idCredito, $idUsuario, $out);
        }

        $rowFresh = $this->db->queryOne(
            'SELECT * FROM adj_repuve_consulta WHERE id_credito = :id LIMIT 1',
            ['id' => $idCredito]
        ) ?: [];
        $ultJson = is_array($estatus['ultimo_estatus'] ?? null) ? $estatus['ultimo_estatus'] : null;
        $out = [
            'success'    => false,
            'id_credito' => $idCredito,
            'repuve'     => [
                'estado'       => 'PROCESANDO',
                'uuid'         => $uuid,
                'mensaje'      => 'Consulta REPUVE en proceso. Se guardó el UUID para seguimiento; el resultado puede tardar unos segundos.',
            ],
            'message'              => 'Consulta REPUVE en proceso. Se guardó el UUID para seguimiento. Pulsa de nuevo «Consultar REPUVE» en unos segundos.',
            'repuve_respuesta_raw' => $this->repuveDecodificarRespuesta((string) ($inicio['response_raw'] ?? '')),
            'repuve_ultima_encuesta' => $estatus['ultimo_estatus'] ?? null,
        ];
        $out = $this->repuveAdjuntarRespuestasTecnicas($rowFresh, $out, $ultJson);

        return $this->repuveEnriquecerResultado($idCredito, $idUsuario, $out);
    }

    /** A?ade cupo diario y sincroniza datos de moto hacia adj_operacion cuando hay resultado ?til. */
    private function repuveEnriquecerResultado(int $idCredito, int $idUsuario, array $result): array
    {
        $result['limite_consultas'] = $this->repuveInfoLimite($idUsuario);

        $dmSync = $result['datos_moto'] ?? [];
        if (
            $idCredito > 0
            && is_array($dmSync)
            && $this->repuveDatosMotoTienenAutocompletadoReal($dmSync)
        ) {
            $syncErr = $this->repuveSincronizarDatosMotoAOperacion($idCredito, $dmSync, $idUsuario);
            if ($syncErr !== null) {
                $result['adj_operacion_sync_error'] = $syncErr;
            }
        }

        return $result;
    }

    /** @return array{max:int,usado_hoy:int,restantes:int} */
    private function repuveInfoLimite(int $idUsuario): array
    {
        $max = self::REPUVE_CONSULTAS_MAX_DIA;
        if ($idUsuario <= 0) {
            return ['max' => $max, 'usado_hoy' => 0, 'restantes' => $max];
        }
        $usado = $this->repuveContarConsultasHoy($idUsuario);

        return [
            'max'       => $max,
            'usado_hoy' => $usado,
            'restantes' => max(0, $max - $usado),
        ];
    }

    /**
     * Cupo de consultas REPUVE del d?a (para barra en pantalla Consulta REPUVE).
     *
     * @return array{max:int,usado_hoy:int,restantes:int}
     */
    public function obtenerInfoLimiteRepuve(int $idUsuario = 0): array
    {
        $this->repuveAsegurarTablaLog();

        return $this->repuveInfoLimite($idUsuario);
    }

    /**
     * Persiste datos REPUVE en la fila de adj_operacion del mismo id_credito (la más reciente).
     *
     * @return string|null mensaje de error si no se pudo guardar; null si OK o sin datos
     */
    private function repuveSincronizarDatosMotoAOperacion(int $idCredito, array $datosMoto, int $idUsuario): ?string
    {
        if ($idCredito <= 0 || empty($datosMoto)) {
            return null;
        }
        $opRes = $this->obtenerOCrearOperacion($idCredito, '', $idUsuario);
        if (empty($opRes['success']) || empty($opRes['detalle']['id'])) {
            return 'No se encontró operación en adjudicación para este crédito; no se actualizaron datos.';
        }
        $idOp = (int) $opRes['detalle']['id'];
        $res  = $this->guardarDatosMoto($idOp, $datosMoto, $idUsuario, 'REPUVE', false);
        if (empty($res['success'])) {
            return (string) ($res['message'] ?? 'No se pudieron guardar los datos en adj_operacion.');
        }

        return null;
    }

    private function repuveAsegurarTablaLog(): void
    {
        $this->db->CRUD(
            "CREATE TABLE IF NOT EXISTS adj_repuve_consulta_log (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_usuario INT NOT NULL,
                id_credito BIGINT NOT NULL,
                creado_at DATETIME NOT NULL,
                PRIMARY KEY (id),
                KEY idx_repuve_log_u_fecha (id_usuario, creado_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    private function repuveContarConsultasHoy(int $idUsuario): int
    {
        if ($idUsuario <= 0) {
            return 0;
        }
        $tz    = new \DateTimeZone('America/Mexico_City');
        $start = (new \DateTime('today', $tz))->format('Y-m-d H:i:s');
        $end   = (new \DateTime('tomorrow', $tz))->format('Y-m-d H:i:s');
        $row   = $this->db->queryOne(
            'SELECT COUNT(*) AS c FROM adj_repuve_consulta_log
             WHERE id_usuario = :u AND creado_at >= :s AND creado_at < :e',
            ['u' => $idUsuario, 's' => $start, 'e' => $end]
        );

        return (int) ($row['c'] ?? 0);
    }

    private function repuveRegistrarUsoConsulta(int $idUsuario, int $idCredito): void
    {
        if ($idUsuario <= 0) {
            return;
        }
        $this->repuveAsegurarTablaLog();
        $ahora = $this->fechaHoraCdmx();
        $this->db->CRUD(
            'INSERT INTO adj_repuve_consulta_log (id_usuario, id_credito, creado_at) VALUES (:u, :c, :a)',
            ['u' => $idUsuario, 'c' => $idCredito, 'a' => $ahora]
        );
    }

    private function repuveAsegurarTabla(): void
    {
        $this->db->CRUD(
            "CREATE TABLE IF NOT EXISTS adj_repuve_consulta (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                id_credito BIGINT NOT NULL,
                criterio_tipo VARCHAR(10) NOT NULL,
                criterio_valor VARCHAR(64) NOT NULL,
                uuid VARCHAR(36) NULL,
                estado VARCHAR(20) NOT NULL DEFAULT 'PROCESANDO',
                http_status INT NULL,
                exito TINYINT(1) NULL,
                message_code INT NULL,
                mensaje VARCHAR(512) NULL,
                request_body LONGTEXT NULL,
                response_body LONGTEXT NULL,
                response_inicio LONGTEXT NULL,
                repuve_id VARCHAR(50) NULL,
                placa VARCHAR(32) NULL,
                vin VARCHAR(32) NULL,
                marca VARCHAR(100) NULL,
                modelo VARCHAR(100) NULL,
                anio_modelo VARCHAR(10) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uq_adj_repuve_credito (id_credito),
                KEY idx_adj_repuve_uuid (uuid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
        $this->repuveAsegurarColumnaResponseInicio();
    }

    /** Tablas antiguas sin columna: conservar JSON del POST (Paso 1) al persistir el GET final (Paso 2). */
    private function repuveAsegurarColumnaResponseInicio(): void
    {
        $chk = $this->db->queryOne(
            'SELECT COUNT(*) AS c FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = \'adj_repuve_consulta\' AND COLUMN_NAME = \'response_inicio\''
        );
        if ((int) ($chk['c'] ?? 0) > 0) {
            return;
        }
        $this->db->CRUD(
            'ALTER TABLE adj_repuve_consulta ADD COLUMN response_inicio LONGTEXT NULL AFTER response_body'
        );
    }

    private function repuveCargarConfig(): array
    {
        $rows = $this->db->queryAll(
            "SELECT clave, valor
             FROM config_api
             WHERE clave IN ('REPUVE_API_BASE_URL','REPUVE_API_KEY','REPUVE_API_USUARIO','REPUVE_API_TIMEOUT')"
        ) ?: [];

        $cfg = [];
        foreach ($rows as $r) {
            $k = (string) ($r['clave'] ?? '');
            if ($k !== '') {
                $cfg[$k] = trim((string) ($r['valor'] ?? ''));
            }
        }

        $base = rtrim((string) ($cfg['REPUVE_API_BASE_URL'] ?? ''), '/');
        $key  = (string) ($cfg['REPUVE_API_KEY'] ?? '');
        $usr  = (string) ($cfg['REPUVE_API_USUARIO'] ?? 'cobranza');
        $to   = (int) ($cfg['REPUVE_API_TIMEOUT'] ?? 25);
        if ($to < 5) {
            $to = 25;
        }

        return [
            'configured' => $base !== '' && $key !== '',
            'base_url'   => $base,
            'api_key'    => $key,
            'usuario'    => $usr !== '' ? $usr : 'cobranza',
            'timeout'    => $to,
        ];
    }

    private function repuveResolverCriterio(int $idCredito): array
    {
        $op = $this->db->queryOne(
            'SELECT moto_placas, moto_no_serie
             FROM adj_operacion
             WHERE id_credito = :id
             ORDER BY id DESC
             LIMIT 1',
            ['id' => $idCredito]
        );

        if (!$op) {
            return ['ok' => false, 'message' => 'No existe operaci?n para el cr?dito indicado.'];
        }

        $plateBase = trim((string) ($op['moto_placas'] ?? ''));
        $plate = strtoupper(preg_replace('/\s+/u', '', $plateBase));
        if ($plate !== '') {
            return ['ok' => true, 'field' => 'plate', 'value' => $plate];
        }

        $vinBase = trim((string) ($op['moto_no_serie'] ?? ''));
        $vin = strtoupper(preg_replace('/\s+/u', '', $vinBase));
        if ($vin !== '') {
            return ['ok' => true, 'field' => 'vin', 'value' => $vin];
        }

        return [
            'ok'      => false,
            'message' => 'No hay placa ni VIN en el expediente. Usa el campo «No. de Serie (VIN)» y el botón de consulta REPUVE junto al campo.',
        ];
    }

    private function repuveHeaders(array $cfg, int $idUsuario, int $idCredito): array
    {
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-API-KEY: ' . $cfg['api_key'],
            'usuario: ' . $cfg['usuario'],
            'idPersona: ' . max(0, $idUsuario),
            'idOferta: ' . max(0, $idCredito),
            'idFlujo: mis-adjudicaciones',
        ];
        return $headers;
    }

    private function repuveHttpJson(string $method, string $url, array $headers, ?array $body, int $timeout): array
    {
        $ch = curl_init();
        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT        => $timeout,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ];

        if (strtoupper($method) === 'POST') {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?? [], JSON_UNESCAPED_UNICODE);
        }

        curl_setopt_array($ch, $opts);
        $raw   = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $http  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($errno || $raw === false) {
            return [
                'ok'          => false,
                'http_status' => $http > 0 ? $http : null,
                'mensaje'     => 'Error de red REPUVE: ' . ($error ?: 'sin detalle'),
                'raw'         => null,
                'json'        => null,
            ];
        }

        $json = json_decode((string) $raw, true);
        if (!is_array($json)) {
            return [
                'ok'          => false,
                'http_status' => $http,
                'mensaje'     => 'REPUVE devolvi? respuesta no JSON.',
                'raw'         => (string) $raw,
                'json'        => null,
            ];
        }

        return [
            'ok'          => $http >= 200 && $http < 300,
            'http_status' => $http,
            'mensaje'     => (string) ($json['mensaje'] ?? ''),
            'raw'         => (string) $raw,
            'json'        => $json,
        ];
    }

    private function repuveIniciarConsulta(array $cfg, string $field, string $value, int $idUsuario, int $idCredito): array
    {
        $url  = $cfg['base_url'] . '/nubarium/consultaRepuve';
        $body = [$field => $value, 'pdf' => false];
        $res  = $this->repuveHttpJson('POST', $url, $this->repuveHeaders($cfg, $idUsuario, $idCredito), $body, (int) $cfg['timeout']);

        $uuid = '';
        $estado = 'ERROR';
        $msg = (string) ($res['mensaje'] ?? '');
        if (!empty($res['json']['resultado']['uuid'])) {
            $uuid = (string) $res['json']['resultado']['uuid'];
            $estado = strtoupper((string) ($res['json']['resultado']['estado'] ?? 'PROCESANDO'));
        }

        return [
            'ok'           => !empty($res['ok']) && $uuid !== '',
            'uuid'         => $uuid,
            'estado'       => $estado,
            'http_status'  => $res['http_status'] ?? null,
            'mensaje'      => $msg !== '' ? $msg : ((string) ($res['json']['resultado']['message'] ?? 'No se pudo iniciar consulta REPUVE.')),
            'request_body' => json_encode($body, JSON_UNESCAPED_UNICODE),
            'response_raw' => $res['raw'] ?? null,
        ];
    }

    private function repuveSondearEstatus(array $cfg, string $uuid): array
    {
        $url = $cfg['base_url'] . '/nubarium/estatusRepuve/' . rawurlencode($uuid);
        $headers = [
            'Accept: application/json',
            'X-API-KEY: ' . $cfg['api_key'],
            'usuario: ' . $cfg['usuario'],
        ];

        $intentos = 15;
        $ultimo = null;
        $ultimoJson = [];
        for ($i = 0; $i < $intentos; $i++) {
            $res = $this->repuveHttpJson('GET', $url, $headers, null, (int) $cfg['timeout']);
            $ultimo = $res;
            $json = is_array($res['json'] ?? null) ? $res['json'] : [];
            $ultimoJson = $json;
            $resultado = is_array($json['resultado'] ?? null) ? $json['resultado'] : [];
            $estado = strtoupper((string) ($resultado['estado'] ?? ''));
            $status = strtoupper((string) ($resultado['status'] ?? ''));
            $messageCode = isset($resultado['messageCode']) ? (int) $resultado['messageCode'] : null;

            $terminal = false;
            $estadoFinal = 'ERROR';
            if ($estado === 'PROCESANDO') {
                $terminal = false;
            } elseif ($status !== '' || $messageCode !== null) {
                $terminal = true;
                $estadoFinal = 'COMPLETADO';
            } elseif (empty($res['ok']) && (($res['http_status'] ?? 0) >= 400)) {
                $terminal = true;
                $estadoFinal = 'ERROR';
            }

            if ($terminal) {
                $datosMoto = $this->repuveMapearDatosMoto($json);
                return [
                    'terminal'     => true,
                    'estado_final' => $estadoFinal,
                    'http_status'  => $res['http_status'] ?? null,
                    'exito'        => $messageCode === 0 && !empty($datosMoto),
                    'message_code' => $messageCode,
                    'mensaje'      => (string) ($resultado['message'] ?? $json['mensaje'] ?? $res['mensaje'] ?? ''),
                    'response_raw' => $res['raw'] ?? null,
                    'datos_moto'   => $datosMoto,
                    'repuve_id'    => (string) (($resultado['data']['repuveId'] ?? '') ?: ''),
                ];
            }

            if ($i < ($intentos - 1)) {
                usleep(1500000);
            }
        }

        return [
            'terminal'       => false,
            'estado_final'   => 'PROCESANDO',
            'http_status'    => $ultimo['http_status'] ?? null,
            'mensaje'        => 'Consulta REPUVE en proceso.',
            'response_raw'   => $ultimo['raw'] ?? null,
            'ultimo_estatus' => $ultimoJson,
        ];
    }

    private function repuveGuardarInicio(int $idCredito, string $field, string $value, array $inicio): void
    {
        $this->db->CRUD(
            "INSERT INTO adj_repuve_consulta
                (id_credito, criterio_tipo, criterio_valor, uuid, estado, http_status, exito, mensaje, request_body, response_body, response_inicio)
             VALUES
                (:id_credito, :tipo, :valor, :uuid, :estado, :http_status, :exito, :mensaje, :request_body, :response_body, :response_inicio)
             ON DUPLICATE KEY UPDATE
                criterio_tipo = VALUES(criterio_tipo),
                criterio_valor = VALUES(criterio_valor),
                uuid = VALUES(uuid),
                estado = VALUES(estado),
                http_status = VALUES(http_status),
                exito = VALUES(exito),
                mensaje = VALUES(mensaje),
                request_body = VALUES(request_body),
                response_body = VALUES(response_body),
                response_inicio = IF(VALUES(response_inicio) IS NOT NULL AND VALUES(response_inicio) <> '', VALUES(response_inicio), response_inicio)",
            [
                'id_credito'   => $idCredito,
                'tipo'         => $field,
                'valor'        => $value,
                'uuid'         => (string) ($inicio['uuid'] ?? ''),
                'estado'       => (string) ($inicio['estado'] ?? 'ERROR'),
                'http_status'  => $inicio['http_status'] ?? null,
                'exito'        => !empty($inicio['ok']) ? 1 : 0,
                'mensaje'      => (string) ($inicio['mensaje'] ?? ''),
                'request_body' => $inicio['request_body'] ?? null,
                'response_body'=> $inicio['response_raw'] ?? null,
                'response_inicio' => $inicio['response_raw'] ?? null,
            ]
        );
    }

    private function repuveActualizarFilaFinal(int $idCredito, array $estatus): void
    {
        $datos = is_array($estatus['datos_moto'] ?? null) ? $estatus['datos_moto'] : [];
        $datos = $this->repuveCompletarDatosMotoDesdeCriterio($idCredito, $datos);
        $this->db->CRUD(
            "UPDATE adj_repuve_consulta SET
                estado = :estado,
                http_status = :http_status,
                exito = :exito,
                message_code = :message_code,
                mensaje = :mensaje,
                response_body = COALESCE(:response_body, response_body),
                repuve_id = :repuve_id,
                placa = :placa,
                vin = :vin,
                marca = :marca,
                modelo = :modelo,
                anio_modelo = :anio
             WHERE id_credito = :id_credito",
            [
                'estado'      => (string) ($estatus['estado_final'] ?? 'ERROR'),
                'http_status' => $estatus['http_status'] ?? null,
                'exito'       => !empty($estatus['exito']) ? 1 : 0,
                'message_code'=> $estatus['message_code'] ?? null,
                'mensaje'     => (string) ($estatus['mensaje'] ?? ''),
                'response_body' => $estatus['response_raw'] ?? null,
                'repuve_id'   => (string) ($estatus['repuve_id'] ?? ''),
                'placa'       => (string) ($datos['moto_placas'] ?? $datos['placas'] ?? ''),
                'vin'         => (string) ($datos['moto_no_serie'] ?? $datos['serie'] ?? ''),
                'marca'       => (string) ($datos['moto_marca'] ?? ''),
                'modelo'      => (string) ($datos['moto_modelo'] ?? ''),
                'anio'        => (string) ($datos['moto_anio'] ?? ''),
                'id_credito'  => $idCredito,
            ]
        );
    }

    /**
     * Rellena placa/VIN si el JSON de REPUVE no los trae: usamos lo que el usuario envió en la consulta
     * (criterio_tipo / criterio_valor en adj_repuve_consulta).
     *
     * Diferencia de columnas en adj_operacion:
     * - moto_placas: placa del vehículo (en calle / padrón). Si la API no manda "placa", se usa la placa
     *   con la que se hizo la consulta por plate.
     * - moto_no_serie: VIN (número de identificación del vehículo, ~17 caracteres; distinto del motor).
     * - moto_no_motor: número de motor del fabricante (el campo "No. de Motor" en la vista de evidencias).
     */
    private function repuveCompletarDatosMotoDesdeCriterio(int $idCredito, array $datos): array
    {
        if ($idCredito <= 0) {
            return $datos;
        }
        $row = $this->db->queryOne(
            'SELECT criterio_tipo, criterio_valor FROM adj_repuve_consulta WHERE id_credito = :id LIMIT 1',
            ['id' => $idCredito]
        );
        if (!$row) {
            return $datos;
        }
        $tipo = strtolower(trim((string) ($row['criterio_tipo'] ?? '')));
        $val  = strtoupper(preg_replace('/\s+/u', '', trim((string) ($row['criterio_valor'] ?? ''))));
        if ($val === '') {
            return $datos;
        }
        if ($tipo === 'plate') {
            $p = (string) ($datos['moto_placas'] ?? $datos['placas'] ?? '');
            if ($p === '') {
                $datos['moto_placas'] = $val;
                $datos['placas']      = $val;
            }
        }
        if ($tipo === 'vin') {
            $v = (string) ($datos['moto_no_serie'] ?? $datos['serie'] ?? '');
            if ($v === '') {
                $datos['moto_no_serie'] = $val;
                $datos['serie']         = $val;
            }
        }

        return $datos;
    }

    /** Obtiene número de motor desde vehicle[] probando claves habituales del middleware REPUVE / Nubarium. */
    private function repuveExtraerNumeroMotorDesdeVehicle(array $vehicle): string
    {
        $claveMotor = [
            'numMotor', 'numeroMotor', 'numero_motor', 'noMotor', 'numeroDeMotor',
            'NumeroMotor', 'NUMERO_MOTOR', 'no_de_motor', 'claveMotor', 'numMotorRepuve',
            'noMotorCompleto', 'identificadorMotor',
        ];
        foreach ($claveMotor as $k) {
            if (!isset($vehicle[$k])) {
                continue;
            }
            $raw = trim((string) $vehicle[$k]);
            if ($raw === '' || $raw === '0') {
                continue;
            }
            return strtoupper(preg_replace('/\s+/u', '', $raw));
        }

        return '';
    }

    /**
     * Busca placa en cualquier nivel del JSON (consulta por VIN a veces no trae vehicle.placa pero sí otro nodo).
     */
    private function repuveBuscarPlacaEnArbol($node, int $depth = 0): string
    {
        if ($depth > 14 || !is_array($node)) {
            return '';
        }
        $claveOk = static function (string $k): bool {
            $l = strtolower($k);

            return $l === 'placa' || $l === 'placavehiculo' || $l === 'numeroplaca' || $l === 'num_placa';
        };
        foreach ($node as $k => $v) {
            if (is_string($k) && $claveOk($k) && is_string($v)) {
                $p = strtoupper(preg_replace('/\s+/u', '', trim($v)));
                if ($p !== '' && strlen($p) >= 5 && strlen($p) <= 16 && preg_match('/^[A-Z0-9\-]+$/', $p)) {
                    return $p;
                }
            }
            if (is_array($v)) {
                $sub = $this->repuveBuscarPlacaEnArbol($v, $depth + 1);
                if ($sub !== '') {
                    return $sub;
                }
            }
        }

        return '';
    }

    private function repuveMapearDatosMoto(array $resp): array
    {
        $resultado = is_array($resp['resultado'] ?? null) ? $resp['resultado'] : [];
        $data      = is_array($resultado['data'] ?? null) ? $resultado['data'] : [];
        $vehicle   = is_array($data['vehicle'] ?? null) ? $data['vehicle'] : [];
        if ($vehicle === [] && is_array($resultado['vehicle'] ?? null)) {
            $vehicle = $resultado['vehicle'];
        }
        if ($vehicle === [] && is_array($data) && (isset($data['marca']) || isset($data['vin']) || isset($data['placa']))) {
            $vehicle = $data;
        }

        $marca = trim((string) ($vehicle['marca'] ?? ''));
        $linea = trim((string) ($vehicle['linea'] ?? ''));
        $mod   = trim((string) ($vehicle['modelo'] ?? ''));
        $modelo = $mod !== '' ? $mod : $linea;

        $anio  = trim((string) ($vehicle['anioModelo'] ?? $vehicle['anio'] ?? ''));
        $placa = strtoupper(preg_replace('/\s+/u', '', (string) ($vehicle['placa'] ?? $vehicle['placaVehiculo'] ?? '')));
        if ($placa === '' && $data !== []) {
            $placa = strtoupper(preg_replace('/\s+/u', '', (string) ($data['placa'] ?? '')));
        }
        $vin   = strtoupper(preg_replace('/\s+/u', '', (string) ($vehicle['vin'] ?? $vehicle['numeroSerie'] ?? $vehicle['niv'] ?? '')));

        $numMotor = $this->repuveExtraerNumeroMotorDesdeVehicle($vehicle);

        if ($placa === '') {
            $placa = $this->repuveBuscarPlacaEnArbol(is_array($resp) ? $resp : []);
        }

        $colorRaw = trim((string) ($vehicle['color'] ?? $vehicle['colorNombre'] ?? $vehicle['colorVehiculo'] ?? ''));

        $out = [];
        if ($marca !== '') {
            $out['moto_marca'] = $marca;
            $out['marca']      = $marca;
        }
        if ($modelo !== '') {
            $out['moto_modelo'] = $modelo;
            $out['modelo']      = $modelo;
        }
        if ($anio !== '') {
            $out['moto_anio'] = $anio;
        }
        if ($placa !== '') {
            $out['moto_placas'] = $placa;
            $out['placas']      = $placa;
        }
        if ($vin !== '') {
            $out['moto_no_serie'] = $vin;
            $out['serie']         = $vin;
        }
        if ($numMotor !== '') {
            $out['moto_no_motor'] = $numMotor;
            $out['num_motor']     = $numMotor;
        }
        if ($colorRaw !== '') {
            $out['moto_color'] = mb_substr($colorRaw, 0, 50);
        }

        return $out;
    }

    /**
     * Arma el bloque "como en la documentación Nubarium": cuerpo del POST, respuesta 200 del POST (Paso 1)
     * y respuesta 200 del GET estatus (Paso 2: exito, mensaje, resultado con data.vehicle, idBitacora).
     *
     * @param  array|null  $ultimaGetMientrasProcesa  Último JSON del sondeo si aún no hay terminal.
     */
    private function repuveArmarPaqueteRespuestaTecnica(array $row, ?array $ultimaGetMientrasProcesa = null): array
    {
        $estado = strtoupper(trim((string) ($row['estado'] ?? '')));
        $rawIni = trim((string) ($row['response_inicio'] ?? ''));
        $rawBody = trim((string) ($row['response_body'] ?? ''));

        $paso1 = $this->repuveDecodificarRespuesta($rawIni !== '' ? $rawIni : null);
        if ($paso1 === null && $rawBody !== '' && $estado === 'PROCESANDO') {
            $paso1 = $this->repuveDecodificarRespuesta($rawBody);
        }

        $paso2 = null;
        if ($estado === 'COMPLETADO' || $estado === 'ERROR') {
            $paso2 = $this->repuveDecodificarRespuesta($rawBody !== '' ? $rawBody : null);
        }

        $reqRaw = trim((string) ($row['request_body'] ?? ''));
        $requestDecoded = null;
        if ($reqRaw !== '') {
            $d = json_decode($reqRaw, true);
            $requestDecoded = is_array($d) ? $d : $reqRaw;
        }

        $out = [
            '_nota' => 'Mismo formato que la API Nubarium (documentación REPUVE): exito, mensaje, resultado{ uuid|estado|status|messageCode|data }, idBitacora.',
            'request_body_POST_consultaRepuve' => $requestDecoded,
            'paso1_POST_respuesta_200' => $paso1,
            'paso2_GET_estatusRepuve_respuesta_200' => $paso2,
        ];
        if ($ultimaGetMientrasProcesa !== null) {
            $out['ultima_respuesta_GET_mientras_PROCESANDO'] = $ultimaGetMientrasProcesa;
        }

        return $out;
    }

    /**
     * @return array  Mismo $out con repuve_respuesta_api (cuerpo final) y repuve_respuesta_tecnica (doc).
     */
    private function repuveAdjuntarRespuestasTecnicas(array $row, array $out, ?array $ultimaGetMientrasProcesa = null): array
    {
        $rawBody = trim((string) ($row['response_body'] ?? ''));
        $out['repuve_respuesta_api'] = $this->repuveDecodificarRespuesta($rawBody !== '' ? $rawBody : null);
        $out['repuve_respuesta_tecnica'] = $this->repuveArmarPaqueteRespuestaTecnica($row, $ultimaGetMientrasProcesa);

        return $out;
    }

    /** Decodifica respuesta cruda del API para la vista (sin romper si no es JSON). */
    private function repuveDecodificarRespuesta(?string $raw): ?array
    {
        if ($raw === null || trim($raw) === '') {
            return null;
        }
        $j = json_decode($raw, true);

        return is_array($j) ? $j : null;
    }

    private function repuveDatosMotoDesdeFila(array $row): array
    {
        $out = [];
        $marca = trim((string) ($row['marca'] ?? ''));
        $modelo = trim((string) ($row['modelo'] ?? ''));
        $anio = trim((string) ($row['anio_modelo'] ?? ''));
        $placa = strtoupper(preg_replace('/\s+/u', '', (string) ($row['placa'] ?? '')));
        $vin = strtoupper(preg_replace('/\s+/u', '', (string) ($row['vin'] ?? '')));

        if ($marca !== '') {
            $out['moto_marca'] = $marca;
            $out['marca']      = $marca;
        }
        if ($modelo !== '') {
            $out['moto_modelo'] = $modelo;
            $out['modelo']      = $modelo;
        }
        if ($anio !== '') {
            $out['moto_anio'] = $anio;
        }
        if ($placa !== '') {
            $out['moto_placas'] = $placa;
            $out['placas']      = $placa;
        }
        if ($vin !== '') {
            $out['moto_no_serie'] = $vin;
            $out['serie']         = $vin;
        }

        // Nº motor y otros no están en columnas denormalizadas de adj_repuve_consulta: extraer del JSON guardado.
        $raw = trim((string) ($row['response_body'] ?? ''));
        if ($raw !== '') {
            $j = json_decode($raw, true);
            if (is_array($j)) {
                $mapped = $this->repuveMapearDatosMoto($j);
                foreach ($mapped as $k => $v) {
                    if ($v === '' || $v === null) {
                        continue;
                    }
                    if (!isset($out[$k]) || $out[$k] === '' || $out[$k] === null) {
                        $out[$k] = $v;
                    }
                }
            }
        }

        $idCred = (int) ($row['id_credito'] ?? 0);

        return $this->repuveCompletarDatosMotoDesdeCriterio($idCred, $out);
    }

    /**
     * True si REPUVE aportó al menos un campo útil para autocompletar (no solo el VIN/placa que ya capturó el usuario).
     */
    private function repuveDatosMotoTienenAutocompletadoReal(array $datos): bool
    {
        foreach (['moto_marca', 'moto_modelo', 'moto_anio', 'moto_placas', 'moto_color', 'moto_no_motor'] as $k) {
            if (trim((string) ($datos[$k] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    /** Indica fallo del proveedor REPUVE (503, mensajes típicos, código 40, etc.), no “vehículo sin datos”. */
    private function repuveEsFalloServicioExterno(array $row, string $mensajeTabla): bool
    {
        $http = (int) ($row['http_status'] ?? 0);
        if ($http >= 500) {
            return true;
        }
        $estadoRow = strtoupper(trim((string) ($row['estado'] ?? '')));
        if ($estadoRow === 'ERROR') {
            return true;
        }
        $mc = isset($row['message_code']) ? (int) $row['message_code'] : null;
        if ($mc === 40) {
            return true;
        }
        $msgL = strtolower($mensajeTabla);
        foreach (['temporarily unavailable', 'service unavailable', 'gateway timeout', 'bad gateway', '503'] as $needle) {
            if ($msgL !== '' && str_contains($msgL, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Arma respuesta JSON unificada tras leer adj_repuve_consulta (evita marcar éxito solo porque el VIN coincide con la consulta).
     *
     * @return array<string, mixed>
     */
    private function repuveConstruirRespuestaConsulta(
        int $idCredito,
        array $row,
        array $datosMotoCrudos,
        bool $fromCache,
        string $mensajeExitoConDatos
    ): array {
        $tieneReal = $this->repuveDatosMotoTienenAutocompletadoReal($datosMotoCrudos);
        $http = (int) ($row['http_status'] ?? 0);
        $mcRaw = $row['message_code'] ?? null;
        $mcInt = $mcRaw !== null && $mcRaw !== '' ? (int) $mcRaw : null;
        $mensajeTabla = trim((string) ($row['mensaje'] ?? ''));
        $estadoRow = strtoupper(trim((string) ($row['estado'] ?? '')));

        if ($tieneReal) {
            $tipo = 'datos_ok';
            $message = $mensajeExitoConDatos;
            $errorServicio = false;
            $sinDatos = false;
        } elseif ($this->repuveEsFalloServicioExterno($row, $mensajeTabla)) {
            $tipo = 'fallo_servicio';
            $errorServicio = true;
            $sinDatos = false;
            // Mensaje breve para UI (tooltip, alertas). El detalle técnico sigue en `repuve` (http_status, message_code, mensaje).
            $message = "REPUVE no disponible\n\n"
                . "El servicio REPUVE no está disponible en este momento. Esto no se debe a la información que usted capturó.\n\n"
                . 'Complete manualmente los datos que falten. Gracias por su cooperación.';
        } else {
            $tipo = 'sin_datos_padron';
            $errorServicio = false;
            $sinDatos = true;
            if ($mensajeTabla !== '') {
                $message = 'REPUVE completó la consulta pero no devolvió datos del padrón para autocompletar (marca, modelo, año, etc.). Detalle: «'
                    . $mensajeTabla . '». Verifica el VIN o completa la captura manual.';
            } else {
                $message = 'REPUVE completó la consulta sin datos del vehículo para autocompletar. Verifica el VIN o usa captura manual.';
            }
            if ($mcInt !== null && $mcInt !== 0) {
                $message .= ' (código ' . $mcInt . ')';
            }
        }

        return [
            'success'                 => $tieneReal,
            'from_cache'              => $fromCache,
            'id_credito'              => $idCredito,
            'datos_moto'              => $tieneReal ? $datosMotoCrudos : [],
            'message'                 => $message,
            'repuve_resultado_tipo'   => $tipo,
            'repuve_error_servicio'   => $errorServicio,
            'repuve_sin_datos_padron' => $sinDatos,
            'repuve'                  => [
                'estado'         => $estadoRow !== '' ? $estadoRow : (string) ($row['estado'] ?? ''),
                'message_code'   => $mcInt,
                'mensaje'        => $mensajeTabla,
                'http_status'    => $http > 0 ? $http : null,
                'exito_registro' => ((int) ($row['exito'] ?? 0)) === 1,
            ],
        ];
    }

    /**
     * Extrae datos de motocicleta desde documento FACTURA:
     * VIN (No. serie), No. motor y color.
     */
    public function obtenerDatosMotoDesdeFactura(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de crédito inválido.'];
        }

        $path = '';
        $isTemp = false;
        try {
            $estadoCuenta = new \Controllers\EstadoCuenta();
            if (!method_exists($estadoCuenta, 'getRutaPdfFactura')) {
                return ['success' => false, 'unavailable' => true, 'message' => 'No está disponible la ruta de factura en este ambiente.'];
            }

            $info = $estadoCuenta->getRutaPdfFactura($idCredito);
            if (!$info || empty($info['path']) || !is_file((string) $info['path'])) {
                return ['success' => false, 'message' => 'No se encontró el documento de FACTURA para este crédito.'];
            }
            $path = (string) $info['path'];
            $isTemp = !empty($info['isTemp']);

            $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                return ['success' => false, 'message' => 'Formato de FACTURA no compatible para extracción automática.'];
            }

            $configPath = dirname(__DIR__) . '/config/config.ini';
            $config = is_file($configPath) ? parse_ini_file($configPath, true) : [];
            $apiUrl = isset($config['doc_verificacion']['api_url']) ? trim((string) $config['doc_verificacion']['api_url']) : '';
            $apiKey = isset($config['doc_verificacion']['api_key']) ? trim((string) $config['doc_verificacion']['api_key']) : 'sparta-__SPARTA_SECRET_REDACTED__-doc-verificacion-key';
            if ($apiUrl === '') {
                $apiUrl = 'http://127.0.0.1:8000/api/v1/verificar';
            }

            $baseUrl = preg_replace('#/verificar\s*$#', '', $apiUrl);
            $endpoint = rtrim((string) $baseUrl, '/') . '/factura/datos-moto';
            $mime = $this->mimeParaDocumentoMotoFactura($ext);
            $cfile = new \CURLFile($path, $mime, 'factura.' . $ext);

            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => ['documento' => $cfile],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 35,
                CURLOPT_HTTPHEADER => ['X-API-Key: ' . $apiKey],
            ]);
            $response = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr = curl_error($ch);
            curl_close($ch);

            if ($code !== 200 || $response === false) {
                return ['success' => false, 'message' => 'No se pudo extraer desde FACTURA: ' . ($curlErr ?: ('HTTP ' . $code))];
            }

            $json = json_decode((string) $response, true);
            if (!is_array($json) || empty($json['success'])) {
                return ['success' => false, 'message' => 'Respuesta inválida al extraer datos de FACTURA.'];
            }

            $datosMoto = $this->normalizarDatosMotoFactura([
                'moto_no_serie' => $json['vin'] ?? null,
                'moto_no_motor' => $json['no_motor'] ?? null,
                'moto_color' => $json['color'] ?? null,
            ]);

            if ($datosMoto === []) {
                return ['success' => false, 'message' => 'La FACTURA no contiene VIN, No. de motor o color legibles.'];
            }

            return [
                'success' => true,
                'message' => 'Datos de motocicleta autocompletados desde FACTURA.',
                'datos_moto' => $datosMoto,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Error al extraer datos desde FACTURA: ' . $e->getMessage()];
        } finally {
            if ($isTemp && $path !== '' && is_file($path)) {
                @unlink($path);
            }
        }
    }

    private function mimeParaDocumentoMotoFactura(string $ext): string
    {
        if ($ext === 'pdf') {
            return 'application/pdf';
        }
        if ($ext === 'png') {
            return 'image/png';
        }
        return 'image/jpeg';
    }

    private function normalizarDatosMotoFactura(array $raw): array
    {
        $out = [];

        $vin = strtoupper(preg_replace('/\s+/u', '', trim((string) ($raw['moto_no_serie'] ?? ''))));
        if ($vin !== '' && preg_match('/^[A-HJ-NPR-Z0-9]{8,17}$/', $vin)) {
            $out['moto_no_serie'] = $vin;
        }

        $motor = strtoupper(preg_replace('/\s+/u', '', trim((string) ($raw['moto_no_motor'] ?? ''))));
        if ($motor !== '' && preg_match('/^[A-Z0-9\-]{4,24}$/', $motor)) {
            $out['moto_no_motor'] = $motor;
        }

        $color = trim((string) ($raw['moto_color'] ?? ''));
        if ($color !== '') {
            $color = preg_replace('/\s+/u', ' ', $color);
            $color = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚüÜñÑ\s]/u', '', (string) $color);
            $color = trim((string) $color);
            if ($color !== '') {
                $out['moto_color'] = mb_substr($color, 0, 50);
            }
        }

        return $out;
    }

    public function guardarDatosMoto(int $idOperacion, array $datos, int $idUsuario = 0, string $nombreUsuario = '', bool $registrarBitacora = true): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operaci?n inv?lida.'];
        }

        $op = $this->db->queryOne(
            'SELECT id FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }

        // REPUVE/Nubarium: confiar en el origen; la validación estricta evitaba guardar en adj_operacion.
        if ($nombreUsuario !== 'REPUVE') {
            $fmtErr = $this->validarDatosMotoFormatos($datos);
            if ($fmtErr !== null) {
                return ['success' => false, 'message' => $fmtErr];
            }
        }

        $maxLen = [
            'moto_marca'     => 100, 'moto_modelo'    => 100, 'moto_color'     => 50,
            'moto_no_serie'  => self::MADJ_VIN_MAX_LEN,
            'moto_no_motor'  => self::MADJ_NO_MOTOR_MAX_LEN,
            'moto_placas'    => self::MADJ_PLACAS_MOTO_MAX_LEN,
            'marca'          => 100, 'modelo'         => 100,
            'serie'          => self::MADJ_VIN_MAX_LEN,
            'num_motor'      => self::MADJ_NO_MOTOR_MAX_LEN,
            'placas'         => self::MADJ_PLACAS_MOTO_MAX_LEN,
            'log_direccion'  => 100, 'log_ciudad'     => 50,
            'log_estado'     => 60,  'log_lugar_resguardo' => 32, 'log_lugar_otro' => 200,
            'log_telefono'   => 10,
            'responsable_entrega' => 160,
        ];

        $setClauses = [];
        $params     = ['id' => $idOperacion];

        $normalizaAlfanumerico = static function (string $campo, $valRaw) use ($maxLen): string {
            return mb_substr(
                strtoupper(preg_replace('/\s+/u', '', trim((string) $valRaw))),
                0,
                $maxLen[$campo] ?? 255
            );
        };

        foreach (self::CAMPOS_DATOS_MOTO as $campo) {
            if (!array_key_exists($campo, $datos)) {
                continue;
            }
            $valRaw = $datos[$campo];
            if ($campo === 'moto_anio') {
                $val = ((int) $valRaw ?: null);
            } elseif (in_array($campo, ['moto_no_serie', 'moto_no_motor', 'moto_placas', 'serie', 'placas', 'num_motor'], true)) {
                $val = $normalizaAlfanumerico($campo, $valRaw);
            } elseif ($campo === 'log_lugar_resguardo') {
                $lr = strtolower(trim((string) $valRaw));
                $permitidos = ['mi_domicilio', 'sucursal', 'otro'];
                $val = in_array($lr, $permitidos, true) ? $lr : '';
            } elseif ($campo === 'log_lugar_otro') {
                $val = mb_substr(trim((string) $valRaw), 0, $maxLen['log_lugar_otro']);
            } else {
                $val = mb_substr(trim((string) $valRaw), 0, $maxLen[$campo] ?? 255);
            }
            $setClauses[]      = "`{$campo}` = :{$campo}";
            $params[$campo]    = $val;
        }

        if (empty($setClauses)) {
            return ['success' => false, 'message' => 'No se recibieron campos v?lidos.'];
        }

        $ahora             = $this->fechaHoraCdmx();
        $setClauses[]      = '`datos_moto_at` = :datos_moto_at';
        $setClauses[]      = '`datos_moto_by` = :datos_moto_by';
        $setClauses[]      = '`fecha_actualizacion` = :fecha_actualizacion';
        $params['datos_moto_at']        = $ahora;
        $params['datos_moto_by']        = $idUsuario ?: null;
        $params['fecha_actualizacion']  = $ahora;

        $this->db->CRUD(
            'UPDATE adj_operacion SET ' . implode(', ', $setClauses) . ' WHERE id = :id',
            $params
        );

        if ($registrarBitacora) {
            $this->registrarBitacora(
                $idOperacion,
                'DATOS MOTO REGISTRADOS: ' . ($datos['moto_marca'] ?? '') . ' ' . ($datos['moto_modelo'] ?? '') . ' (' . ($datos['moto_placas'] ?? '') . ')',
                $idUsuario,
                $nombreUsuario,
                $ahora
            );
        }

        return ['success' => true];
    }

    /**
     * Busca la operaci?n m?s reciente para un id_credito en adj_operacion.
     * Si no existe ninguna, crea una autom?ticamente con datos m?nimos.
     *
     * @return array{success:bool, detalle?:array, creado?:bool, message?:string}
     */
    public function obtenerOCrearOperacion(int $idCredito, string $nombreCliente, int $idUsuario = 0): array
    {
        $op = $this->db->queryOne(
            'SELECT id FROM adj_operacion WHERE id_credito = :id ORDER BY id DESC LIMIT 1',
            ['id' => $idCredito]
        );

        if ($op) {
            $detalle = $this->obtenerDetalle((int) $op['id']);
            return ['success' => true, 'detalle' => $detalle];
        }

        // No existe ??? crear con datos m?nimos
        $ahora = $this->fechaHoraCdmx();
        $folio = $this->generarFolio();

        $campos = [
            'folio'               => $folio,
            'id_credito'          => $idCredito,
            'nombre_cliente'      => $nombreCliente !== '' ? $nombreCliente : "Cr?dito #{$idCredito}",
            'estatus'             => 'en_transito',
            'id_usuario_alta'     => $idUsuario ?: null,
            'fecha_alta'          => $ahora,
            'fecha_actualizacion' => $ahora,
        ];

        $cols = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($campos)));
        $ph   = implode(', ', array_map(fn($k) => ":{$k}", array_keys($campos)));
        $this->db->CRUD("INSERT INTO adj_operacion ({$cols}) VALUES ({$ph})", $campos);

        $newId   = (int) $this->db->lastInsertId();
        $detalle = $this->obtenerDetalle($newId);

        return ['success' => true, 'detalle' => $detalle, 'creado' => true];
    }

    // =========================================================================
    // VALIDACI??N EVIDENCIAS (ATENCI??N A CLIENTES ??? requiere columnas val_atn / comentario_atn)
    // =========================================================================

    /**
     * @return array{success:bool, message?:string}
     */
    public function guardarVeredictoEvidenciaAtn(int $idOperacion, int $idEvidencia, int $valAtn, string $comentario, int $idUsuario, string $nombreUsuario = ''): array
    {
        if ($idOperacion <= 0 || $idEvidencia <= 0) {
            return ['success' => false, 'message' => 'Par?metros inv?lidos.'];
        }
        if (!in_array($valAtn, [1, 2], true)) {
            return ['success' => false, 'message' => 'Veredicto no v?lido.'];
        }
        if (!$this->adjEvidenciaTieneColumnasAtn()) {
            return [
                'success' => false,
                'message' => 'Falta migración de base de datos: deben existir las columnas val_atn y comentario_atn en adj_evidencia.',
            ];
        }
        $comentario = mb_substr(trim($comentario), 0, 2000);
        $row = $this->db->queryOne(
            'SELECT id, slot FROM adj_evidencia WHERE id = :eid AND id_operacion = :op LIMIT 1',
            ['eid' => $idEvidencia, 'op' => $idOperacion]
        );
        if (!$row) {
            return ['success' => false, 'message' => 'Evidencia no encontrada.'];
        }
        if (($row['slot'] ?? '') === self::SLOT_REPVE_ATENCION) {
            return ['success' => false, 'message' => 'Repuve no se valida con aceptar/rechazar en Atenci?n (solo subir PDF).'];
        }
        $this->db->CRUD(
            'UPDATE adj_evidencia SET val_atn = :v, comentario_atn = :c
             WHERE id = :eid AND id_operacion = :op',
            [
                'v'   => $valAtn,
                'c'   => $comentario,
                'eid' => $idEvidencia,
                'op'  => $idOperacion,
            ]
        );
        $etiq = $valAtn === 1 ? 'ACEPTADA' : 'RECHAZADA';
        $this->registrarBitacora(
            $idOperacion,
            'VALIDACIÓN EVIDENCIA ' . $etiq . ' (id evidencia ' . $idEvidencia . ')',
            $idUsuario,
            $nombreUsuario
        );
        return ['success' => true];
    }

    /**
     * Listo para enviar a Procesando IA: evidencia f?sica (momento 1) completa con val_atn = 1 y PDF Repuve en expediente.
     */
    public function operacionTieneValidacionAtencionCompleta(int $idOperacion): bool
    {
        if (!$this->adjEvidenciaTieneColumnasAtn()) {
            return false;
        }
        $rows = $this->db->queryAll(
            'SELECT slot, val_atn, url FROM adj_evidencia WHERE id_operacion = :id',
            ['id' => $idOperacion]
        ) ?: [];
        $bySlot = [];
        foreach ($rows as $r) {
            $sk = (string) ($r['slot'] ?? '');
            if ($sk !== '') {
                $bySlot[$sk] = $r;
            }
        }
        foreach (self::SLOTS_VALIDACION_ATENCION_MEDIA as $slot) {
            if (!isset($bySlot[$slot])) {
                return false;
            }
            $url = trim((string) ($bySlot[$slot]['url'] ?? ''));
            if ($url === '') {
                return false;
            }
            $va = (int) ($bySlot[$slot]['val_atn'] ?? 0);
            if ($va !== 1) {
                return false;
            }
        }
        if (!isset($bySlot[self::SLOT_REPVE_ATENCION])) {
            return false;
        }
        $urlRep = trim((string) ($bySlot[self::SLOT_REPVE_ATENCION]['url'] ?? ''));

        return $urlRep !== '';
    }

    /**
     * Atenci?n a clientes: bot?n "Enviar evidencias validadas" ??? Procesando IA (pesta?a Aprobados).
     * No se llama autom?ticamente al cerrar el modal ni al guardar veredictos.
     *
     * @return array{success:bool, message?:string}
     */
    public function enviarEvidenciasValidadasAtencion(int $idOperacion, int $idUsuario, string $nombreUsuario = ''): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'ID de operaci?n inv?lido.'];
        }
        if (!$this->operacionTieneValidacionAtencionCompleta($idOperacion)) {
            return ['success' => false, 'message' => 'Faltan fotos/video por validar o el PDF de Repuve en expediente.'];
        }
        $op = $this->db->queryOne('SELECT id, estatus FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        $est = (string) ($op['estatus'] ?? '');

        $yaEnviado = false;
        if ($this->adjOperacionTieneColumnaEnvioAtencion()) {
            $f = $this->db->queryOne(
                'SELECT atencion_envio_validado AS v FROM adj_operacion WHERE id = :id LIMIT 1',
                ['id' => $idOperacion]
            );
            $yaEnviado = ((int) ($f['v'] ?? 0) === 1);
        }
        if (!$yaEnviado) {
            $b = $this->db->queryOne(
                "SELECT 1 AS ok
                 FROM adj_bitacora
                 WHERE id_operacion = :id
                   AND accion LIKE :a
                 LIMIT 1",
                ['id' => $idOperacion, 'a' => '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%']
            );
            $yaEnviado = (bool) ($b && (int) ($b['ok'] ?? 0) === 1);
        }
        if ($yaEnviado) {
            return ['success' => false, 'message' => 'Esta operaci?n ya fue enviada.'];
        }

        $previos = ['Recibido', 'en_transito', 'Revisión Recuperaciones', 'Procesando IA'];
        if (!in_array($est, $previos, true)) {
            return ['success' => false, 'message' => 'Esta operaci?n no est? en etapa para este paso.'];
        }
        if ($est !== 'Procesando IA') {
            $r = $this->cambiarEstatus($idOperacion, 'Procesando IA', $idUsuario, $nombreUsuario);
            if (empty($r['success'])) {
                return $r;
            }
        }
        if ($this->adjOperacionTieneColumnaEnvioAtencion()) {
            $this->db->CRUD(
                'UPDATE adj_operacion SET atencion_envio_validado = 1 WHERE id = :id',
                ['id' => $idOperacion]
            );
        }
        $this->registrarBitacora($idOperacion, 'ENVIÓ EVIDENCIAS VALIDADAS (PROCESANDO IA)', $idUsuario, $nombreUsuario);

        return ['success' => true];
    }

    /**
     * Si hay al menos una evidencia con val_atn = 2, mueve la operaci?n a "Revisión Recuperaciones".
     * Si ya no hay rechazos y estaba en "Revisión Recuperaciones", regresa a bandeja (Recibido/en_transito),
     * salvo que ya est? enviada desde Atenci?n, caso en el que vuelve a "Procesando IA".
     *
     * @return array{success:bool, message?:string, rechazos?:int, enviado_a_correcciones?:bool, regresado_de_correcciones?:bool}
     */
    public function finalizarCierreValidacionEvidenciaAtn(int $idOperacion, int $idUsuario, string $nombreUsuario = ''): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'ID de operaci?n inv?lido.'];
        }
        $op = $this->db->queryOne('SELECT id, estatus FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        $estatus = trim((string) ($op['estatus'] ?? ''));

        $n = 0;
        if ($this->adjEvidenciaTieneColumnasAtn()) {
            $countRow = $this->db->queryOne(
                'SELECT COUNT(*) AS c FROM adj_evidencia
                 WHERE id_operacion = :id AND val_atn = 2
                   AND IFNULL(slot, \'\') <> :slot_rep',
                ['id' => $idOperacion, 'slot_rep' => self::SLOT_REPVE_ATENCION]
            );
            $n = (int) ($countRow['c'] ?? 0);
        }

        if ($n > 0) {
            $enviado = false;
            // Si ya estaba en Procesando IA por env?o desde Atenci?n, no bajar a Correcciones por rechazos en UI.
            $noForzarCorrecciones = ($estatus === 'Procesando IA' && $this->operacionTieneEnvioAtencionMarcado($idOperacion));
            if (!$noForzarCorrecciones && !$this->esEstatusRevisionRecuperaciones($estatus)) {
                $r = $this->cambiarEstatus($idOperacion, 'Revisión Recuperaciones', $idUsuario, $nombreUsuario);
                if (empty($r['success'])) {
                    return $r;
                }
                $enviado = true;
            }
            return [
                'success'                 => true,
                'rechazos'                => $n,
                'enviado_a_correcciones' => $enviado,
            ];
        }

        $regresado = false;
        if ($this->esEstatusRevisionRecuperaciones($estatus)) {
            $destino = 'Recibido';
            $yaEnviadoAtencion = false;

            if ($this->adjOperacionTieneColumnaEnvioAtencion()) {
                $f = $this->db->queryOne(
                    'SELECT atencion_envio_validado AS v FROM adj_operacion WHERE id = :id LIMIT 1',
                    ['id' => $idOperacion]
                );
                $yaEnviadoAtencion = ((int) ($f['v'] ?? 0) === 1);
            }
            if (!$yaEnviadoAtencion) {
                $b = $this->db->queryOne(
                    "SELECT 1 AS ok
                     FROM adj_bitacora
                     WHERE id_operacion = :id
                       AND accion LIKE :a
                     LIMIT 1",
                    ['id' => $idOperacion, 'a' => '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%']
                );
                $yaEnviadoAtencion = (bool) ($b && (int) ($b['ok'] ?? 0) === 1);
            }

            if ($yaEnviadoAtencion) {
                $destino = 'Procesando IA';
            } else {
                $prev = $this->db->queryOne(
                    "SELECT estatus_anterior
                     FROM adj_historial_estatus
                     WHERE id_operacion = :id
                       AND estatus_nuevo = 'Revisión Recuperaciones'
                     ORDER BY id DESC
                     LIMIT 1",
                    ['id' => $idOperacion]
                );
                $estPrevio = trim((string) ($prev['estatus_anterior'] ?? ''));
                if (in_array($estPrevio, ['Recibido', 'en_transito'], true)) {
                    $destino = $estPrevio;
                } elseif ($estPrevio === 'Procesando IA') {
                    // Kanban u otro flujo: volver a bandeja (no Procesando IA sin env?o desde Atenci?n).
                    $destino = 'Recibido';
                }
            }

            $r = $this->cambiarEstatus($idOperacion, $destino, $idUsuario, $nombreUsuario);
            if (empty($r['success'])) {
                return $r;
            }
            $regresado = true;
        }

        return [
            'success'                   => true,
            'rechazos'                  => 0,
            'enviado_a_correcciones'    => false,
            'regresado_de_correcciones' => $regresado,
        ];
    }
}
