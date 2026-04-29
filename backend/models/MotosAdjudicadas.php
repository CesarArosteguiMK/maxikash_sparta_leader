<?php

namespace Models;

use Core\Model;
use Core\Database;
use Models\Adjudicacion as AdjudicacionModel;

class MotosAdjudicadas extends Model
{
    private $db;

    /** @var null|bool null = aún no comprobado, true = existen val_atn/comentario_atn */
    private static $adjEvidenciaAtnColumnas = null;

    /** @var null|bool columna adj_operacion.atencion_envio_validado */
    private static $adjOperacionEnvioAtencionCol = null;

    /** @var null|bool columna adj_operacion.fecha_llegada_almacen (migración 20260428_adj_operacion_fecha_llegada_almacen.sql) */
    private static $adjOperacionFechaLlegadaAlmacenCol = null;

    /** @var null|bool columnas recepcion_*_estado (migración 20260428_adj_operacion_recepcion_doc_estado.sql) */
    private static $adjOperacionRecepcionDocEstadoCol = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * true si en adj_evidencia existen val_atn y comentario_atn (migración aplicada).
     * Prueba con SELECT directo: information_schema a veces no está permitido para el usuario MySQL.
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

    /** Etapa «Correcciones» en evidencias (pipeline). */
    private function esEstatusRevisionRecuperaciones(string $estatus): bool
    {
        $e = trim($estatus);

        return $e === 'Revisión Recuperaciones';
    }

    /**
     * Ya envió evidencias validadas desde Atención (flag o bitácora).
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
     * true si existe adj_operacion.atencion_envio_validado (migración 20260427_adj_operacion_atencion_envio.sql).
     * Se prueba con SELECT directo: information_schema a veces no está permitido para el usuario MySQL.
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
     * Guarda estado de documento en recepción (sin archivo): pendiente / no recibido.
     *
     * @param  string  $documento  dacion | tarjeta
     * @param  string  $estado     pending | missing
     * @return array{success:bool, message?:string}
     */
    public function guardarRecepcionEstadoDocumento(int $idOperacion, string $documento, string $estado, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operación inválida.'];
        }
        $documento = strtolower(trim($documento));
        $estado    = strtolower(trim($estado));
        if (!in_array($documento, ['dacion', 'tarjeta'], true)) {
            return ['success' => false, 'message' => 'Documento no reconocido.'];
        }
        if ($documento === 'dacion' && !in_array($estado, ['pending', 'missing'], true)) {
            return ['success' => false, 'message' => 'Estado no válido para contrato de dación.'];
        }
        if ($documento === 'tarjeta' && $estado !== 'missing') {
            return ['success' => false, 'message' => 'Estado no válido para tarjeta de circulación.'];
        }
        if (!$this->adjOperacionTieneColumnasRecepcionDocEstado()) {
            return [
                'success' => false,
                'message' => 'Falta migración: ejecute backend/migrations/20260428_adj_operacion_recepcion_doc_estado.sql',
            ];
        }
        $row = $this->db->queryOne(
            'SELECT id, estatus FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$row) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
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

    /** @var null|bool columnas recepcion_confirmada_at / ubicación */
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
     * Confirma recepción en almacén (una vez): ubicación, observaciones y validaciones mínimas.
     *
     * @return array{success:bool, message?:string, confirmada_at_fmt?:string, ya_confirmada?:bool}
     */
    public function confirmarRecepcionAlmacen(int $idOperacion, string $ubicacion, string $observaciones, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operación inválida.'];
        }
        if (!$this->adjOperacionTieneColumnasRecepcionConfirmacion()) {
            return [
                'success' => false,
                'message' => 'Falta migración: ejecute backend/migrations/20260428_adj_operacion_recepcion_confirmacion.sql',
            ];
        }
        if (!$this->adjOperacionTieneColumnasRecepcionDocEstado()) {
            return [
                'success' => false,
                'message' => 'Falta migración de documentos: backend/migrations/20260428_adj_operacion_recepcion_doc_estado.sql',
            ];
        }
        $ubicacion     = trim($ubicacion);
        $observaciones = trim($observaciones);
        if ($ubicacion === '') {
            return ['success' => false, 'message' => 'Indique la ubicación en almacén.'];
        }
        $op = $this->db->queryOne(
            'SELECT id, estatus, fecha_llegada_almacen, recepcion_confirmada_at,
                    recepcion_dacion_estado, recepcion_tarjeta_estado
             FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
        }
        if (trim((string) ($op['estatus'] ?? '')) !== 'Recepción') {
            return ['success' => false, 'message' => 'Solo se confirma recepción en etapa Recepción.'];
        }
        if (!$this->adjOperacionTieneColumnaFechaLlegadaAlmacen() || empty($op['fecha_llegada_almacen'])) {
            return ['success' => false, 'message' => 'Debe registrar primero la llegada física a almacén.'];
        }
        if (!empty($op['recepcion_confirmada_at'])) {
            $fmt = $this->db->queryOne(
                "SELECT DATE_FORMAT(recepcion_confirmada_at, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
                ['id' => $idOperacion]
            );

            return [
                'success'          => false,
                'message'          => 'La recepción ya fue confirmada y no puede modificarse.',
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
            return ['success' => false, 'message' => 'Indique el estado del contrato de dación o suba el escaneo firmado.'];
        }
        $tarjOk = $tEst === 'missing' || $urlT !== '';
        if (!$tarjOk) {
            return ['success' => false, 'message' => 'Indique si no recibe la tarjeta o suba la foto de la tarjeta de circulación.'];
        }
        if ($urlF === '') {
            return ['success' => false, 'message' => 'Debe subir la imagen de firma de recepción del agente de almacén.'];
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
                'message'            => 'La recepción ya fue confirmada y no puede modificarse.',
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
        $fmt = $this->db->queryOne(
            "SELECT DATE_FORMAT(recepcion_confirmada_at, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
            ['id' => $idOperacion]
        );

        return [
            'success'            => true,
            'confirmada_at_fmt'  => (string) ($fmt['f'] ?? $ahora),
        ];
    }

    /**
     * Saldo capital y adeudo total según API S2 (estado de cuenta), misma fuente que EstadoCuenta.
     *
     * @return array{success:bool, message?:string, saldo_capital?:float, adeudo_total?:float, fecha_corte?:string}
     */
    public function obtenerResumenFinancieroEstadoCuentaS2(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de crédito inválido.'];
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
     * Registra una sola vez la llegada física al almacén (Recepción). No se puede modificar ni repetir.
     *
     * @return array{success:bool, message?:string, fecha_llegada_almacen?:string, ya_registrada?:bool}
     */
    public function registrarLlegadaAlmacenRecepcion(int $idOperacion, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operación inválida.'];
        }
        if (!$this->adjOperacionTieneColumnaFechaLlegadaAlmacen()) {
            return [
                'success' => false,
                'message' => 'Falta migración de base de datos: ejecute backend/migrations/20260428_adj_operacion_fecha_llegada_almacen.sql',
            ];
        }
        $row = $this->db->queryOne(
            'SELECT id, estatus, fecha_llegada_almacen FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$row) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
        }
        $est = trim((string) ($row['estatus'] ?? ''));
        if ($est !== 'Recepción') {
            return ['success' => false, 'message' => 'Solo se registra llegada a almacén cuando la operación está en etapa Recepción.'];
        }
        if (!empty($row['fecha_llegada_almacen'])) {
            $fmt = $this->db->queryOne(
                "SELECT DATE_FORMAT(fecha_llegada_almacen, '%d/%m/%Y %h:%i:%s %p') AS f FROM adj_operacion WHERE id = :id LIMIT 1",
                ['id' => $idOperacion]
            );

            return [
                'success'          => false,
                'message'          => 'La llegada a almacén ya fue registrada y no puede modificarse.',
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
                    'message'               => 'La llegada a almacén ya fue registrada y no puede modificarse.',
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
    // BUSCAR CRÉDITO EN ADJUDICACIÓN
    // =========================================================================

    /**
     * Verifica que el crédito tiene asignación activa en adj_creditos_adjudicacion
     * y enriquece con datos del cliente vía S2.
     *
     * @return array{success:bool, message?:string, nombre_cliente?:string, ...}
     */
    public function buscarCreditoEnAdjudicacion(int $idCredito): array
    {
        // 1. ¿Está asignado activamente en adjudicación?
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
                'message' => "El crédito #{$idCredito} no tiene asignación activa en el módulo de Adjudicación. Asígnalo primero desde \"Asignación de Créditos\".",
            ];
        }

        // 2. Datos del cliente vía S2 (reutiliza lógica existente)
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
        // 1. Whitelist de slots válidos
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

        // 2. Operación existe
        $op = $this->db->queryOne('SELECT id FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
        }

        // 3. Validar tipo MIME según slot
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
                return ['success' => false, 'message' => 'Solo se aceptan imágenes JPG o PNG.'];
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
                return ['success' => false, 'message' => 'Solo se aceptan imágenes JPG o PNG.'];
            }
            $tipo = 'image';
        }

        // 4. Límite de tamaño: 20 MB
        if (($fileInfo['size'] ?? 0) > 20 * 1024 * 1024) {
            return ['success' => false, 'message' => 'El archivo supera el límite de 20 MB.'];
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

        // 8. INSERT o UPDATE en adj_evidencia
        if ($old) {
            $this->db->CRUD(
                "UPDATE adj_evidencia
                    SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = :estatus
                  WHERE id_operacion = :id AND slot = :slot",
                ['tipo' => $tipo, 'url' => $urlRelativa, 'fecha' => $ahora, 'estatus' => $estatusEvidencia,
                 'id'   => $idOperacion, 'slot' => $slot]
            );
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
        return ['success' => true, 'url' => $urlClient];
    }

    /**
     * Marca recepcion_*_estado = received cuando existe migración de columnas.
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
     * Vista 4 Cartera: registra que el usuario confirma haber dado de alta el cierre en S2
     * y envía la operación a la etapa Recepción (bandeja de entrada de la vista 5).
     *
     * @return array{success:bool, message?:string, estatus_nuevo?:string}
     */
    public function confirmarCierreDocumentacionEnS2(int $idOperacion, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Identificador de operación inválido.'];
        }
        $row = $this->db->queryOne(
            'SELECT id, estatus FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$row) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
        }
        $est = trim((string) ($row['estatus'] ?? ''));
        if ($est !== 'Cierre Documentado') {
            return ['success' => false, 'message' => 'La operación no está en etapa Cierre documentado.'];
        }

        $this->registrarBitacora(
            $idOperacion,
            'CONFIRMACIÓN: Cierre documentado registrado en S2',
            $idUsuario,
            $nombreUsuario
        );

        /**
         * Registro explícito en adj_dictamen para que las vistas posteriores puedan mostrar
         * la línea de dictamen sin mantener la operación “colgada” en filtros por estatus antiguos.
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
     * Recuperación (momento 3): con factura cargada, envía la operación a Cartera — estatus Cierre documentado.
     * Los comentarios son opcionales; si hay texto se guardan en adj_observacion.
     *
     * @return array{success:bool, message?:string}
     */
    public function enviarRecuperacionACartera(int $idOperacion, string $comentarios, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Identificador de operación inválido.'];
        }
        $comentarios = trim($comentarios);

        $op = $this->db->queryOne(
            'SELECT id, estatus FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
        }
        $est = trim((string) ($op['estatus'] ?? ''));
        if ($est !== 'Procesando IA') {
            return ['success' => false, 'message' => 'La operación no está en etapa Procesando IA.'];
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

        return $this->cambiarEstatus($idOperacion, 'Cierre Documentado', $idUsuario, $nombreUsuario);
    }

    // =========================================================================
    // PIPELINE / LECTURA
    // =========================================================================


    /**
     * Devuelve todas las operaciones activas (no cerradas-archivadas),
     * ordenadas por estatus y fecha_alta.
     */
    public function obtenerPipeline(): array
    {
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
              LIMIT 1) AS gestor_nombre
        FROM adj_operacion o
        ORDER BY
            FIELD(o.estatus,
                'Recibido',
                'en_transito',
                'Procesando IA',
                'Revisión Recuperaciones',
                'Retenciones',
                'cancelado',
                'Cierre Documentado',
                'Recepción'
            ),
            o.fecha_alta ASC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    /**
     * Detalle completo de una operación incluyendo evidencias y observaciones.
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
        // aprobada: 0 (legacy) — veredicto en val_atn (1=aceptar, 2=rechazar) si la migración está aplicada
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
    // ENVIAR EVIDENCIAS (pendiente_envio → recibido)
    // =========================================================================

    /**
     * Cambia todas las evidencias en estado 'pendiente_envio' de una operación a 'recibido'.
     * @return array{success:bool, actualizadas?:int, message?:string}
     */
    public function enviarEvidencias(int $idOperacion, int $idUsuario = 0, string $nombreUsuario = ''): array
    {
        $op = $this->db->queryOne('SELECT id FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
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
    // CREAR OPERACIÓN
    // =========================================================================

    /**
     * Crea una nueva operación en el pipeline.
     * Retorna ['success'=>true, 'id'=>…, 'folio'=>…] o ['success'=>false, 'message'=>…]
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

        // Limpiar nullables vacíos
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
            return ['success' => false, 'message' => 'No se pudo registrar la operación.'];
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
        'fis_lateral'   => 'LATERAL (FÍSICA)',
        'fis_360'       => 'INSPECCIÓN 360°',
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

    /** Fotos/video que sí se dictaminan (aceptar/rechazar) en Atención a clientes. */
    private const SLOTS_VALIDACION_ATENCION_MEDIA = [
        'rec_tacometro', 'rec_serie', 'rec_frontal', 'rec_lateral',
        'fis_vin', 'fis_tacometro', 'fis_frontal', 'fis_lateral', 'fis_360',
    ];

    /** Repuve: solo debe existir PDF subido; no se usa val_atn en Atención. */
    private const SLOT_REPVE_ATENCION = 'doc_repuve';

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
     * Cambia el estatus de una operación y registra historial.
     */
    public function cambiarEstatus(int $id, string $estatusNuevo, int $idUsuario, string $nombreUsuario = ''): array
    {
        if (!in_array($estatusNuevo, self::ESTATUS_VALIDOS, true)) {
            return ['success' => false, 'message' => 'Estatus no válido.'];
        }

        $actual = $this->db->queryOne(
            "SELECT estatus FROM adj_operacion WHERE id = :id",
            ['id' => $id]
        );

        if (!$actual) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
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
    // AGREGAR OBSERVACIÓN
    // =========================================================================

    public function agregarObservacion(int $idOperacion, string $etapa, string $area, int $idUsuario, string $texto, string $nombreUsuario = ''): array
    {
        $texto = trim($texto);
        if ($texto === '') {
            return ['success' => false, 'message' => 'La observación no puede estar vacía.'];
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

        $accionBit = 'AGREGÓ ACCIÓN DE TRAMO: ' . mb_strtoupper(mb_substr($texto, 0, 60)) . (mb_strlen($texto) > 60 ? '…' : '');
        $this->registrarBitacora($idOperacion, $accionBit, $idUsuario, $nombreUsuario, $ahora);

        return ['success' => true, 'id' => $newId, 'fecha' => $ahora];
    }

    // =========================================================================
    // ELIMINAR OPERACIÓN (soft: no existe columna activo, se elimina real solo si no tiene historial)
    // =========================================================================

    public function eliminarOperacion(int $id): array
    {
        $op = $this->db->queryOne("SELECT id FROM adj_operacion WHERE id = :id", ['id' => $id]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
        }

        $this->db->CRUD("DELETE FROM adj_operacion WHERE id = :id", ['id' => $id]);
        return ['success' => true];
    }

    // =========================================================================
    // MIS ADJUDICACIONES — créditos asignados al usuario en sesión
    // =========================================================================

    /**
     * Devuelve los créditos activos asignados al usuario.
     * El bucket se enriquece en esta misma respuesta para evitar una segunda llamada HTTP.
     */
    public function obtenerMisAdjudicaciones(int $idPersona): array
    {
        $creditos = $this->db->queryAll(
            <<<SQL
            SELECT
                aca.id_credito                                          AS id_credito,
                IF(aca.estatus = '1', 'Activo', 'Inactivo')            AS estado,
                DATE_FORMAT(aca.fecha_alta, '%Y-%m-%d %H:%i')          AS fecha_asignacion,
                DATE_FORMAT(aca.fecha_baja, '%Y-%m-%d %H:%i')          AS fecha_desasignacion,
                COALESCE(NULLIF(TRIM(ult_op.nombre_cliente), ''), '—') AS nombre_cliente,
                TRIM(CONCAT_WS(' ', per_alta.nombres, per_alta.apellidop)) AS asignado_por,
                aca.id                                                  AS id_asignacion
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
            WHERE pa.id_persona = :idPersona
              AND aca.estatus = '1'
              AND ult_op.estatus IN (
                  'en_transito',
                  'Recibido',
                  'Procesando IA',
                  'Revisión Recuperaciones',
                  'Cierre Documentado',
                  'Recepción'
              )
            ORDER BY aca.fecha_alta DESC
            SQL,
            ['idPersona' => $idPersona, 'idPersonaUlt' => $idPersona]
        ) ?: [];

        foreach ($creditos as &$c) {
            $c['bucket'] = '—';
        }
        unset($c);

        $idsCreditos = array_values(array_unique(array_filter(array_map(
            static fn($c) => (int) ($c['id_credito'] ?? 0),
            $creditos
        ))));

        if ($idsCreditos !== []) {
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

        return $creditos;
    }

    /**
     * Morosidad y saldo desde __SPARTA_SECRET_REDACTED__ (Segundómetro). Llamada asíncrona desde la vista.
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
                'bucket'         => (string) ($r['Bucket_Morosidad_Real'] ?? '—'),
                'saldo'          => isset($r['saldo']) ? (float) $r['saldo'] : 0.0,
            ];
        }

        return $out;
    }

    // =========================================================================
    // EVIDENCIAS POR CRÉDITO (mis_adjudicaciones)
    // =========================================================================

    /**
     * Devuelve el total de evidencias cargadas por cada crédito solicitado,
     * tomando la operación más reciente por id_credito.
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

        // Slots válidos de Mis Adjudicaciones (9 evidencias requeridas en esta vista)
        $slotsPermitidos = [
            'rec_tacometro', 'rec_serie', 'rec_frontal', 'rec_lateral',
            'fis_vin', 'fis_tacometro', 'fis_frontal', 'fis_lateral', 'fis_360',
        ];

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

        $rows = $this->db->queryAll(
            "SELECT ult.id_credito,
                    COALESCE(ev.total, 0)     AS total,
                    COALESCE(ev.pendiente, 0) AS pendiente
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
                       ) AS pendiente
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
            if ($id > 0) {
                $totalSlotsVista = count($slotsPermitidos);
                $resumen[$id] = [
                    'total'    => $total,
                    'all_sent' => $total >= $totalSlotsVista && $pendiente === 0,
                ];
            }
        }

        return $resumen;
    }

    /**
     * Busca la operación más reciente para un id_credito en adj_operacion.
     * Si no existe ninguna, crea una automáticamente con datos mínimos.
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

        // No existe → crear con datos mínimos
        $ahora = $this->fechaHoraCdmx();
        $folio = $this->generarFolio();

        $campos = [
            'folio'               => $folio,
            'id_credito'          => $idCredito,
            'nombre_cliente'      => $nombreCliente !== '' ? $nombreCliente : "Crédito #{$idCredito}",
            'estatus'             => 'Retenciones',
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
    // VALIDACIÓN EVIDENCIAS (ATENCIÓN A CLIENTES — requiere columnas val_atn / comentario_atn)
    // =========================================================================

    /**
     * @return array{success:bool, message?:string}
     */
    public function guardarVeredictoEvidenciaAtn(int $idOperacion, int $idEvidencia, int $valAtn, string $comentario, int $idUsuario, string $nombreUsuario = ''): array
    {
        if ($idOperacion <= 0 || $idEvidencia <= 0) {
            return ['success' => false, 'message' => 'Parámetros inválidos.'];
        }
        if (!in_array($valAtn, [1, 2], true)) {
            return ['success' => false, 'message' => 'Veredicto no válido.'];
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
            return ['success' => false, 'message' => 'Repuve no se valida con aceptar/rechazar en Atención (solo subir PDF).'];
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
     * Atención a clientes: botón "Enviar evidencias validadas" → Procesando IA (pestaña Aprobados).
     * No se llama automáticamente al cerrar el modal ni al guardar veredictos.
     *
     * @return array{success:bool, message?:string}
     */
    public function enviarEvidenciasValidadasAtencion(int $idOperacion, int $idUsuario, string $nombreUsuario = ''): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'ID de operación inválido.'];
        }
        if (!$this->operacionTieneValidacionAtencionCompleta($idOperacion)) {
            return ['success' => false, 'message' => 'Faltan fotos/video por validar o el PDF de Repuve en expediente.'];
        }
        $op = $this->db->queryOne('SELECT id, estatus FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
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
            return ['success' => false, 'message' => 'Esta operación ya fue enviada.'];
        }

        $previos = ['Recibido', 'en_transito', 'Revisión Recuperaciones', 'Procesando IA'];
        if (!in_array($est, $previos, true)) {
            return ['success' => false, 'message' => 'Esta operación no está en etapa para este paso.'];
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
     * Si hay al menos una evidencia con val_atn = 2, mueve la operación a "Revisión Recuperaciones".
     * Si ya no hay rechazos y estaba en "Revisión Recuperaciones", regresa a bandeja (Recibido/en_transito),
     * salvo que ya esté enviada desde Atención, caso en el que vuelve a "Procesando IA".
     *
     * @return array{success:bool, message?:string, rechazos?:int, enviado_a_correcciones?:bool, regresado_de_correcciones?:bool}
     */
    public function finalizarCierreValidacionEvidenciaAtn(int $idOperacion, int $idUsuario, string $nombreUsuario = ''): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'ID de operación inválido.'];
        }
        $op = $this->db->queryOne('SELECT id, estatus FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
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
            // Si ya estaba en Procesando IA por envío desde Atención, no bajar a Correcciones por rechazos en UI.
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
                    // Kanban u otro flujo: volver a bandeja (no Procesando IA sin envío desde Atención).
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
