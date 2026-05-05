<?php

namespace Models;

use Core\Model;
use Core\Database;
use Models\Adjudicacion as AdjudicacionModel;

class MotosAdjudicadas extends Model
{
    private $db;

    /** @var null|bool null = a?n no comprobado, true = existen val_atn/comentario_atn */
    private static $adjEvidenciaAtnColumnas = null;

    /** @var null|bool columna adj_operacion.atencion_envio_validado */
    private static $adjOperacionEnvioAtencionCol = null;

    /** @var null|bool columna adj_operacion.fecha_llegada_almacen (migraci?n 20260428_adj_operacion_fecha_llegada_almacen.sql) */
    private static $adjOperacionFechaLlegadaAlmacenCol = null;

    /** @var null|bool columnas recepcion_*_estado (migraci?n 20260428_adj_operacion_recepcion_doc_estado.sql) */
    private static $adjOperacionRecepcionDocEstadoCol = null;

    /** M?ximo de consultas REPUVE nuevas (POST a Nubarium) por usuario y d?a natural CDMX. */
    private const REPUVE_CONSULTAS_MAX_DIA = 5;

    /** Slots de evidencias fotogr?ficas (Mis adjudicaciones); debe coincidir con la vista y el resumen SQL. */
    private const MADJ_SLOTS_EVIDENCIA_MEDIA = [
        'rec_tacometro', 'rec_serie', 'rec_frontal', 'rec_lateral',
        'fis_vin', 'fis_tacometro', 'fis_frontal', 'fis_lateral', 'fis_360',
    ];

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * true si en adj_evidencia existen val_atn y comentario_atn (migraci?n aplicada).
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

        return $e === 'Revisi?n Recuperaciones';
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
     * true si existe adj_operacion.atencion_envio_validado (migraci?n 20260427_adj_operacion_atencion_envio.sql).
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
                'message' => 'Falta migraci?n: ejecute backend/migrations/20260428_adj_operacion_recepcion_doc_estado.sql',
            ];
        }
        $row = $this->db->queryOne(
            'SELECT id, estatus FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$row) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        if (trim((string) ($row['estatus'] ?? '')) !== 'Recepci?n') {
            return ['success' => false, 'message' => 'Solo aplica en etapa Recepci?n.'];
        }
        $col = $documento === 'dacion' ? 'recepcion_dacion_estado' : 'recepcion_tarjeta_estado';
        $ahora = $this->fechaHoraCdmx();
        $this->db->CRUD(
            "UPDATE adj_operacion SET `{$col}` = :est, fecha_actualizacion = :f WHERE id = :id",
            ['est' => $estado, 'f' => $ahora, 'id' => $idOperacion]
        );
        $label = $documento === 'dacion' ? 'CONTRATO DACI??N' : 'TARJETA CIRCULACI??N';
        $this->registrarBitacora(
            $idOperacion,
            "RECEPCI??N DOC {$label}: " . strtoupper($estado),
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
                'message' => 'Falta migraci?n: ejecute backend/migrations/20260428_adj_operacion_recepcion_confirmacion.sql',
            ];
        }
        if (!$this->adjOperacionTieneColumnasRecepcionDocEstado()) {
            return [
                'success' => false,
                'message' => 'Falta migraci?n de documentos: backend/migrations/20260428_adj_operacion_recepcion_doc_estado.sql',
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
        if (trim((string) ($op['estatus'] ?? '')) !== 'Recepci?n') {
            return ['success' => false, 'message' => 'Solo se confirma recepci?n en etapa Recepci?n.'];
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
            'RECEPCI??N EN ALMAC??N CONFIRMADA: ' . $ubicacion,
            $idUsuario,
            $nombreUsuario,
            $ahora
        );

        /* Flujo actual: tras Recepci?n sigue la etapa Retenciones (atenci?n / cierre de llamadas). */
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
     * Registra una sola vez la llegada f?sica al almac?n (Recepci?n). No se puede modificar ni repetir.
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
                'message' => 'Falta migraci?n de base de datos: ejecute backend/migrations/20260428_adj_operacion_fecha_llegada_almacen.sql',
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
        if ($est !== 'Recepci?n') {
            return ['success' => false, 'message' => 'Solo se registra llegada a almac?n cuando la operaci?n est? en etapa Recepci?n.'];
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
            'LLEGADA A ALMAC??N REGISTRADA (RECEPCI??N): ' . $ahora,
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
            'fis_vin',       'fis_tacometro', 'fis_frontal', 'fis_lateral', 'fis_360',
            'doc_repuve',    'doc_factura',   'doc_cierre_s2',
            'doc_dacion_rcpt', 'doc_tarjeta_rcpt', 'doc_firma_rcpt',
            'vista_trs', 'vista_front', 'lado_izq', 'lado_der',
            'tablero', 'vin', 'danos_vis', 'vid_gen',
        ];
        if (!in_array($slot, $allowed, true)) {
            return ['success' => false, 'message' => 'Slot de evidencia no reconocido.'];
        }

        // 2. Operaci?n existe
        $op = $this->db->queryOne('SELECT id FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }

        // 3. Validar tipo MIME seg?n slot
        $mime      = $fileInfo['type'] ?? '';
        $ext       = strtolower(pathinfo($fileInfo['name'] ?? '', PATHINFO_EXTENSION));
        $videoSlots = ['fis_360', 'vid_gen'];
        $docSlots   = ['doc_repuve', 'doc_factura', 'doc_cierre_s2', 'doc_dacion_rcpt'];
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
        $this->registrarBitacora($idOperacion, 'SUBI?? EVIDENCIA EN ' . $slotLabel, $idUsuario, $nombreUsuario);

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
    // BIT?CORA
    // =========================================================================

    private function registrarBitacora(int $idOperacion, string $accion, int $idUsuario, string $nombreUsuario, ?string $fecha = null): void
    {
        if ($idOperacion <= 0) return;
        $fecha = $fecha ?? $this->fechaHoraCdmx();
        $this->db->CRUD(
            "INSERT INTO adj_bitacora (id_operacion, id_usuario, nombre_usuario, accion, fecha_alta)
             VALUES (:id_op, :id_usr, :nombre, :accion, :fecha)",
            [
                'id_op'  => $idOperacion,
                'id_usr' => $idUsuario,
                'nombre' => strtoupper(trim($nombreUsuario ?: 'SISTEMA')),
                'accion' => strtoupper($accion),
                'fecha'  => $fecha,
            ]
        );
    }

    public function obtenerBitacora(int $idOperacion): array
    {
        return $this->db->queryAll(
            "SELECT id, nombre_usuario, accion,
                    DATE_FORMAT(fecha_alta, '%d/%m/%Y %h:%i:%s %p') AS fecha_alta
             FROM adj_bitacora
             WHERE id_operacion = :id
             ORDER BY fecha_alta DESC
             LIMIT 100",
            ['id' => $idOperacion]
        ) ?: [];
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
            $estatusEnvioTxt = '???';
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
     * y env?a la operaci?n a la etapa Recepci?n (bandeja de entrada de la vista 5).
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
            'CONFIRMACI??N: Cierre documentado registrado en S2',
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
                'tipo_contacto'      => 'Cierre documentaci?n',
                'resultado'          => 'Confirmado en S2',
                'dictamen'           => 'Cierre documentado confirmado en S2',
                'plataforma'         => 'S2',
                'comentarios'        => null,
                'id_usuario'         => $idUsuario ?: null,
                'fecha_alta'         => $ahora,
            ]
        );

        $mov = $this->cambiarEstatus($idOperacion, 'Recepci?n', $idUsuario, $nombreUsuario);
        if (empty($mov['success'])) {
            return $mov;
        }

        return ['success' => true, 'estatus_nuevo' => 'Recepci?n'];
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
            $obs = $this->agregarObservacion($idOperacion, 'Recuperaci?n', 'Cartera', $idUsuario, $comentarios, $nombreUsuario);
            if (empty($obs['success'])) {
                return $obs;
            }
        }

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
            o.placas,
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
                'Revisi?n Recuperaciones',
                'Cierre Documentado',
                'Recepci?n',
                'Retenciones',
                'cancelado'
            ),
            o.fecha_alta ASC
        SQL;

        return $this->db->queryAll($sql) ?: [];
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
        $op['validaciones_evidencia_timeline'] = $this->db->queryAll(
            "SELECT nombre_usuario, accion,
                    DATE_FORMAT(fecha_alta, '%d/%m/%Y %h:%i:%s %p') AS fecha_fmt
             FROM adj_bitacora
             WHERE id_operacion = :id AND accion LIKE '%VALIDACI??N EVIDENCIA%'
             ORDER BY fecha_alta ASC",
            ['id' => $id]
        ) ?: [];

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
        $op = $this->db->queryOne('SELECT id FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }

        $this->db->CRUD(
            "UPDATE adj_evidencia SET estatus = 'recibido'
              WHERE id_operacion = :id AND estatus = 'pendiente_envio'",
            ['id' => $idOperacion]
        );

        $this->registrarBitacora($idOperacion, 'ENVI?? EVIDENCIAS AL PIPELINE', $idUsuario, $nombreUsuario);

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
        'rec_tacometro' => 'TAC??METRO (RECOLECCI??N)',
        'rec_serie'     => 'NO. SERIE (RECOLECCI??N)',
        'rec_frontal'   => 'FRONTAL (RECOLECCI??N)',
        'rec_lateral'   => 'LATERAL (RECOLECCI??N)',
        'fis_vin'       => 'VIN (F?SICA)',
        'fis_tacometro' => 'TAC??METRO (F?SICA)',
        'fis_frontal'   => 'FRONTAL (F?SICA)',
        'fis_lateral'   => 'LATERAL (F?SICA)',
        'fis_360'       => 'INSPECCI??N 360?',
        'doc_repuve'    => 'REPUVE',
        'doc_factura'   => 'FACTURA',
        'doc_cierre_s2' => 'CONFIRMACI??N CIERRE S2',
        'doc_dacion_rcpt'   => 'CONTRATO DACI??N (RECEPCI??N ALMAC??N)',
        'doc_tarjeta_rcpt'  => 'TARJETA CIRCULACI??N (RECEPCI??N ALMAC??N)',
        'doc_firma_rcpt'    => 'FIRMA RECEPCI??N ALMAC??N',
        'vista_trs'         => 'VISTA TRASERA (RECEPCI??N ALMAC??N)',
        'vista_front'       => 'VISTA FRONTAL (RECEPCI??N ALMAC??N)',
        'lado_izq'          => 'LADO IZQUIERDO (RECEPCI??N ALMAC??N)',
        'lado_der'          => 'LADO DERECHO (RECEPCI??N ALMAC??N)',
        'tablero'           => 'TABLERO / OD??METRO (RECEPCI??N ALMAC??N)',
        'vin'               => 'VIN (RECEPCI??N ALMAC??N)',
        'danos_vis'         => 'DA??OS VISIBLES (RECEPCI??N ALMAC??N)',
        'vid_gen'           => 'VIDEO GENERAL 360? (RECEPCI??N ALMAC??N)',
    ];

    private const RECEPCION_ALMACEN_SLOTS = [
        'vista_trs', 'vista_front', 'lado_izq', 'lado_der',
        'tablero', 'vin', 'danos_vis', 'vid_gen',
    ];

    /** Fotos/video que s? se dictaminan (aceptar/rechazar) en Atenci?n a clientes. */
    private const SLOTS_VALIDACION_ATENCION_MEDIA = [
        'rec_tacometro', 'rec_serie', 'rec_frontal', 'rec_lateral',
        'fis_vin', 'fis_tacometro', 'fis_frontal', 'fis_lateral', 'fis_360',
    ];

    /** Repuve: solo debe existir PDF subido; no se usa val_atn en Atenci?n. */
    private const SLOT_REPVE_ATENCION = 'doc_repuve';

    /** Slots del expediente de evidencias en pipeline (11; alineado con la vista operaciones_pipeline). */
    private const SLOTS_PIPELINE_EXPEDIENTE = [
        'rec_tacometro', 'rec_serie', 'rec_frontal', 'rec_lateral',
        'fis_vin', 'fis_tacometro', 'fis_frontal', 'fis_lateral', 'fis_360',
        'doc_repuve', 'doc_factura',
    ];

    private const ESTATUS_VALIDOS = [
        'Recibido',
        'en_transito',
        'Procesando IA',
        'Revisi?n Recuperaciones',
        'Retenciones',
        'Cierre Documentado',
        'Recepci?n',
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

        $accionBit = 'AGREG?? ACCI??N DE TRAMO: ' . mb_strtoupper(mb_substr($texto, 0, 60)) . (mb_strlen($texto) > 60 ? '???' : '');
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
                  'Revisi?n Recuperaciones',
                  'Cierre Documentado',
                  'Recepci?n',
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
    //   log_ubicacion, log_direccion, log_ciudad, log_estado, log_responsable, log_telefono,
    //   datos_moto_at, datos_moto_by
    // Migraci?n: 20260430_adj_operacion_datos_moto_logisticos.sql
    // =========================================================================

    /** Columnas persistibles en adj_operacion para datos de moto (incluye hist?rico marca/modelo/serie/placas). */
    /**
     * Columnas de moto en adj_operacion (vista "No. de Motor" = moto_no_motor / num_motor; VIN = moto_no_serie; placa = moto_placas).
     */
    private const CAMPOS_DATOS_MOTO = [
        'moto_marca', 'moto_modelo', 'moto_anio', 'moto_color',
        'moto_no_serie', 'moto_no_motor', 'moto_placas',
        'marca', 'modelo', 'serie', 'num_motor', 'placas',
        'log_ubicacion', 'log_direccion', 'log_ciudad',
        'log_estado', 'log_responsable', 'log_telefono',
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

        if (array_key_exists('log_responsable', $datos)) {
            $nom = trim((string) $datos['log_responsable']);
            if ($nom !== '' && !preg_match('/^[a-z???????????????????\s\'\.\-]+$/u', $nom)) {
                return 'El responsable de resguardo debe ser un nombre (letras); no se permiten n?meros.';
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
                $out = [
                    'success'      => !empty($datosMoto),
                    'from_cache'   => true,
                    'id_credito'   => $idCredito,
                    'datos_moto'   => $datosMoto,
                    'repuve'       => [
                        'estado'       => $estado,
                        'message_code' => isset($row['message_code']) ? (int) $row['message_code'] : null,
                        'mensaje'      => (string) ($row['mensaje'] ?? ''),
                    ],
                    'message'      => !empty($datosMoto)
                        ? 'Datos REPUVE cargados desde caché.'
                        : ((string) ($row['mensaje'] ?? 'No hay datos de vehículo para autocompletar.')),
                ];
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
                    $out = [
                        'success'    => !empty($datosMoto),
                        'from_cache' => true,
                        'id_credito' => $idCredito,
                        'datos_moto' => $datosMoto,
                        'repuve'     => [
                            'estado'       => (string) (($row['estado'] ?? 'PROCESANDO')),
                            'message_code' => isset($row['message_code']) ? (int) $row['message_code'] : null,
                            'mensaje'      => (string) ($row['mensaje'] ?? ''),
                        ],
                        'message'    => !empty($datosMoto)
                            ? 'Datos REPUVE actualizados desde estatus.'
                            : ((string) ($row['mensaje'] ?? 'Consulta REPUVE sin datos autocompletables.')),
                    ];
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
            $out = [
                'success'    => !empty($datosMoto),
                'from_cache' => false,
                'id_credito' => $idCredito,
                'datos_moto' => $datosMoto,
                'repuve'     => [
                    'estado'       => (string) (($row['estado'] ?? 'COMPLETADO')),
                    'message_code' => isset($row['message_code']) ? (int) $row['message_code'] : null,
                    'mensaje'      => (string) ($row['mensaje'] ?? ''),
                ],
                'message'    => !empty($datosMoto)
                    ? 'Datos REPUVE consultados correctamente.'
                    : ((string) ($row['mensaje'] ?? 'Consulta REPUVE completada sin datos autocompletables.')),
            ];
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

        if (
            $idCredito > 0
            && !empty($result['datos_moto'])
            && is_array($result['datos_moto'])
        ) {
            $syncErr = $this->repuveSincronizarDatosMotoAOperacion($idCredito, $result['datos_moto'], $idUsuario);
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
            'SELECT moto_placas, placas, moto_no_serie, serie
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
        if ($plateBase === '') {
            $plateBase = trim((string) ($op['placas'] ?? ''));
        }
        $plate = strtoupper(preg_replace('/\s+/u', '', $plateBase));
        if ($plate !== '') {
            return ['ok' => true, 'field' => 'plate', 'value' => $plate];
        }

        $vinBase = trim((string) ($op['moto_no_serie'] ?? ''));
        if ($vinBase === '') {
            $vinBase = trim((string) ($op['serie'] ?? ''));
        }
        $vin = strtoupper(preg_replace('/\s+/u', '', $vinBase));
        if ($vin !== '') {
            return ['ok' => true, 'field' => 'vin', 'value' => $vin];
        }

        return [
            'ok'      => false,
            'message' => 'No hay placa ni VIN para consultar REPUVE. Captura y guarda la placa o el VIN de la motocicleta en la operación.',
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
            'log_ubicacion'  => 100, 'log_direccion'  => 100, 'log_ciudad'     => 50,
            'log_estado'     => 60,  'log_responsable'=> 100, 'log_telefono'   => 10,
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
                'message' => 'Ejecute en MySQL el script backend/migrations/20260227_adj_evidencia_atencion_val.sql (columna val_atn faltante).',
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
            'VALIDACI??N EVIDENCIA ' . $etiq . ' (id evidencia ' . $idEvidencia . ')',
            $idUsuario,
            $nombreUsuario
        );
        return ['success' => true];
    }

    /**
     * Listo para enviar a Procesando IA: 9 evidencias media con val_atn = 1 y Repuve con archivo en expediente (no dictamina Repuve).
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

        $previos = ['Recibido', 'en_transito', 'Revisi?n Recuperaciones', 'Procesando IA'];
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
        $this->registrarBitacora($idOperacion, 'ENVI?? EVIDENCIAS VALIDADAS (PROCESANDO IA)', $idUsuario, $nombreUsuario);

        return ['success' => true];
    }

    /**
     * Si hay al menos una evidencia con val_atn = 2, mueve la operaci?n a "Revisi?n Recuperaciones".
     * Si ya no hay rechazos y estaba en "Revisi?n Recuperaciones", regresa a bandeja (Recibido/en_transito),
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
                $r = $this->cambiarEstatus($idOperacion, 'Revisi?n Recuperaciones', $idUsuario, $nombreUsuario);
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
                       AND estatus_nuevo = 'Revisi?n Recuperaciones'
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
