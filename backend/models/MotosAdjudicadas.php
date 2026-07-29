<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseLegacy;
use Models\Adjudicacion as AdjudicacionModel;
use Services\AnthropicMotoConditionClient;

class MotosAdjudicadas extends Model
{
    private const ACCION_GESTOR_ENVIO_EVIDENCIAS_ADJUDICACION = 'EL GESTOR ENVIO EVIDENCIAS DE LA ADJUDICACION';
    private const ACCION_MONITOREO_FORZO_EVIDENCIAS = 'MONITOREO FORZO ENVIO A EVIDENCIAS';

    private $db;

    /** @var null|bool null = a?n no comprobado, true = existen val_atn/comentario_atn */
    private static $adjEvidenciaAtnColumnas = null;

    /** @var null|array<string, bool> columnas reales de adj_evidencia_rechazo_historial */
    private static $adjEvidenciaRechazoHistorialColumnas = null;

    /** @var null|bool columna adj_operacion.atencion_envio_validado */
    private static $adjOperacionEnvioAtencionCol = null;

    /** @var null|bool columna adj_operacion.fecha_llegada_almacen (requiere esquema actualizado en BD) */
    private static $adjOperacionFechaLlegadaAlmacenCol = null;

    /** @var null|array<string, bool> columnas reales de adj_operacion en este ambiente */
    private static $adjOperacionColumnas = null;

    /** @var null|bool columnas recepcion_*_estado (requiere esquema actualizado en BD) */
    private static $adjOperacionRecepcionDocEstadoCol = null;

    /** @var null|bool tabla de caché para resumen S2 en modal de dictámenes */
    private static $cacheResumenS2DictamenTablaOk = null;

    /** M?ximo de consultas REPUVE nuevas (POST a Nubarium) por usuario y d?a natural CDMX. */
    private const REPUVE_CONSULTAS_MAX_DIA = 5;

    /** Campa?a Legacy: MOTOS ADJUDICADAS AUTORIZADAS. */
    private const LEGACY_CAMPAIGN_MOTOS_ADJ_AUTORIZADAS = 432;

    /** Prefijo de campanas Legacy vigentes para asignacion semanal. */
    private const LEGACY_CAMPAIGN_ASIGNACION_PREFIX = 'ASIGNACION_W';

    /** Slots de evidencias fotogr?ficas (Mis adjudicaciones); debe coincidir con la vista y el resumen SQL. */
    private const MADJ_SLOTS_EVIDENCIA_MEDIA = [
        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
        'fis_vin',
        'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
        'fis_tacometro',
        'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba',
        'fis_checklist',
    ];

    /** Perfil nuevo de Etapa 2: INE por ambos lados sustituye el video del cliente. */
    private const MADJ_SLOTS_EVIDENCIA_MEDIA_ETAPA2 = [
        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
        'fis_ine_frente', 'fis_ine_reverso',
        'fis_vin',
        'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
        'fis_tacometro',
        'fis_360_encendida', 'fis_video_vuelta_prueba',
        'fis_checklist',
    ];

    /** Mapeo del formulario de la app (dictums.form_response) a slots de Mis adjudicaciones. */
    private const DICTUM_APP_EVIDENCIA_SLOTS = [
        'foto_dacion_hoja_1' => 'fis_dacion_hoja_1',
        'foto_de_dacion_hoja_1' => 'fis_dacion_hoja_1',
        'foto_dacion_hoja_2' => 'fis_dacion_hoja_2',
        'foto_de_dacion_hoja_2' => 'fis_dacion_hoja_2',
        'ine_frente' => 'fis_ine_frente',
        'foto_ine_frente' => 'fis_ine_frente',
        'foto_de_ine_frente' => 'fis_ine_frente',
        'ine_reverso' => 'fis_ine_reverso',
        'foto_ine_reverso' => 'fis_ine_reverso',
        'foto_de_ine_reverso' => 'fis_ine_reverso',
        'foto_de_tacometro_legible_y_visible_el_kilometraje' => 'fis_tacometro',
        'text-1778722329133-0' => 'fis_tacometro',
        'foto_de_numero_de_serie_foto_legible_donde_se_lea_' => 'fis_vin',
        'foto_frontal_de_la_moto_la_foto_debe_estar_visible' => 'fis_frontal',
        'foto_trasera_de_la_moto_la_foto_debe_ser_visible_t' => 'fis_trasera',
        'foto_lateral_izquierda_de_la_moto_foto_legible_de_' => 'fis_lateral_izq',
        'foto_lateral_derecha_de_la_moto_foto_legible_de_pr' => 'fis_lateral_der',
        'inspeccion_360_de_moto_el_video_debe_evidenciar_el' => 'fis_360_encendida',
        'video_cliente_de_acuerdo' => 'fis_video_cliente_acuerdo',
        'video_del_cliente_aceptando_la_adjudicacion_de_mot' => 'fis_video_cliente_acuerdo',
        'video_vuelta_de_prueba' => 'fis_video_vuelta_prueba',
        'tomar_video' => 'fis_video_vuelta_prueba',
        'foto_de_checklist' => 'fis_checklist',
        'foto_de_check_list' => 'fis_checklist',
        'video_de_moto_recuperada' => 'fis_360_encendida',
    ];

    public function __construct()
    {
        $this->db = new Database();
    }

    private function sqlExisteEnvioEvidenciasAdjudicacion(string $aliasOperacion = 'o'): string
    {
        return "EXISTS (
            SELECT 1
            FROM adj_bitacora b_env
            WHERE b_env.id_operacion = {$aliasOperacion}.id
              AND (
                  b_env.accion LIKE '%AL PIPELINE%'
                  OR b_env.accion LIKE '%EVIDENCIAS DE LA ADJUDICACION%'
              )
        )";
    }

    private function adjOperacionTieneColumna(string $columna): bool
    {
        if (self::$adjOperacionColumnas === null) {
            self::$adjOperacionColumnas = [];
            try {
                foreach ($this->db->queryAll('SHOW COLUMNS FROM adj_operacion') ?: [] as $row) {
                    $field = (string) ($row['Field'] ?? '');
                    if ($field !== '') {
                        self::$adjOperacionColumnas[$field] = true;
                    }
                }
            } catch (\Throwable $e) {
                self::$adjOperacionColumnas = [];
            }
        }

        return isset(self::$adjOperacionColumnas[$columna]);
    }

    /**
     * Algunas bases antiguas no traen los campos capturados manualmente desde la app.
     * Si faltan, los crea de forma idempotente para que guardarDatosMoto no los ignore.
     */
    private function asegurarColumnasFormularioMoto(): void
    {
        $faltantes = [
            'kilometraje' => 'VARCHAR(40) NULL',
            'tiene_llave_fisica' => 'VARCHAR(10) NULL',
            'tiene_tarjeta_de_circulacion_en_fisico' => 'VARCHAR(10) NULL',
            'la_moto_tiene_placa_fisica' => 'VARCHAR(10) NULL',
            'llave_fisica' => 'VARCHAR(10) NULL',
            'tarjeta_circulacion' => 'VARCHAR(10) NULL',
            'placa_fisica' => 'VARCHAR(10) NULL',
        ];

        $alterado = false;
        foreach ($faltantes as $columna => $definicion) {
            if ($this->adjOperacionTieneColumna($columna)) {
                continue;
            }

            try {
                $this->db->CRUD(
                    'ALTER TABLE adj_operacion ADD COLUMN `' . str_replace('`', '``', $columna) . '` ' . $definicion
                );
                $alterado = true;
            } catch (\Throwable $e) {
                // Si otra ejecución ya creó la columna entre el SHOW y el ALTER, recargamos caché y seguimos.
                self::$adjOperacionColumnas = null;
                if (!$this->adjOperacionTieneColumna($columna)) {
                    throw $e;
                }
            }
        }

        if ($alterado) {
            self::$adjOperacionColumnas = null;
        }
    }

    private function asegurarColumnasCodigoEntregaLegacy(): void
    {
        $faltantes = [
            'codigo_entrega' => 'VARCHAR(6) NULL',
            'codigo_entrega_estatus' => "VARCHAR(20) NULL DEFAULT 'activo'",
            'codigo_entrega_usado_at' => 'DATETIME NULL',
            'codigo_entrega_origen' => 'VARCHAR(80) NULL',
            'codigo_entrega_generado_at' => 'DATETIME NULL',
            'codigo_entrega_generado_por' => 'INT NULL',
        ];

        $alterado = false;
        foreach ($faltantes as $columna => $definicion) {
            if ($this->adjOperacionTieneColumna($columna)) {
                continue;
            }

            try {
                $this->db->CRUD(
                    'ALTER TABLE adj_operacion ADD COLUMN `' . str_replace('`', '``', $columna) . '` ' . $definicion
                );
                $alterado = true;
            } catch (\Throwable $e) {
                self::$adjOperacionColumnas = null;
                if (!$this->adjOperacionTieneColumna($columna)) {
                    throw $e;
                }
            }
        }

        if ($alterado) {
            self::$adjOperacionColumnas = null;
        }
    }

    private function adjOperacionSelectColumnaONull(string $columna, string $prefijo = ''): string
    {
        $alias = str_replace('`', '', $columna);
        if ($this->adjOperacionTieneColumna($columna)) {
            $col = '`' . str_replace('`', '``', $columna) . '`';
            return ($prefijo !== '' ? $prefijo . '.' : '') . $col;
        }
        return 'NULL AS `' . str_replace('`', '``', $alias) . '`';
    }

    /**
     * true si en adj_evidencia existen val_atn y comentario_atn (migración aplicada).
     * Prueba con SELECT directo: information_schema a veces no est? permitido para el usuario MySQL.
     */
    public function guardarCodigoEntregaLegacyLocal(int $idOperacion, string $codigo, int $idUsuario, string $nombreUsuario): array
    {
        if ($idOperacion <= 0 || !preg_match('/^\d{6}$/', $codigo)) {
            return ['success' => false, 'message' => 'Datos invalidos para guardar codigo Legacy.'];
        }
        try {
            $this->asegurarColumnasCodigoEntregaLegacy();
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'No se pudo preparar el almacenamiento local del codigo Legacy.',
                'migration_required' => true,
            ];
        }

        if (!$this->adjOperacionTieneColumna('codigo_entrega')) {
            return [
                'success' => false,
                'message' => 'No existe el campo local para guardar el codigo Legacy.',
                'column_missing' => true,
            ];
        }

        $op = $this->db->queryOne('SELECT id, estatus FROM adj_operacion WHERE id = :id LIMIT 1', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operacion no encontrada.'];
        }

        $estatus = strtolower(trim((string) ($op['estatus'] ?? '')));
        if (strpos($estatus, 'entreg') !== false || strpos($estatus, 'finaliz') !== false || strpos($estatus, 'concluid') !== false || strpos($estatus, 'complet') !== false) {
            return ['success' => false, 'message' => 'La operacion ya fue entregada o finalizada; no permite generar codigo Legacy.'];
        }
        if (strpos($estatus, 'cancel') !== false) {
            return ['success' => false, 'message' => 'La operacion esta cancelada; no permite generar codigo Legacy.'];
        }

        $sets = ['codigo_entrega = :codigo'];
        $params = ['codigo' => $codigo, 'id' => $idOperacion];

        $columnasOpcionales = [
            'codigo_entrega_estatus' => ['sql' => "codigo_entrega_estatus = 'activo'"],
            'codigo_entrega_usado_at' => ['sql' => 'codigo_entrega_usado_at = NULL'],
            'codigo_entrega_origen' => ['sql' => 'codigo_entrega_origen = :origen', 'params' => ['origen' => 'sparta_otp_emergencia']],
            'codigo_entrega_generado_at' => ['sql' => 'codigo_entrega_generado_at = NOW()'],
            'codigo_entrega_generado_por' => ['sql' => 'codigo_entrega_generado_por = :generado_por', 'params' => ['generado_por' => $idUsuario]],
            'fecha_actualizacion' => ['sql' => 'fecha_actualizacion = NOW()'],
        ];

        foreach ($columnasOpcionales as $columna => $def) {
            if (!$this->adjOperacionTieneColumna($columna)) {
                continue;
            }
            $sets[] = $def['sql'];
            foreach (($def['params'] ?? []) as $key => $value) {
                $params[$key] = $value;
            }
        }

        $this->db->CRUD(
            'UPDATE adj_operacion SET ' . implode(', ', $sets) . ' WHERE id = :id',
            $params
        );
        $this->registrarBitacora(
            $idOperacion,
            'GENERO CODIGO DE ACCESO LEGACY DESDE OTP DE EMERGENCIA',
            $idUsuario,
            $nombreUsuario
        );

        return [
            'success' => true,
            'message' => 'Codigo Legacy guardado en adj_operacion.codigo_entrega.',
            'codigo_entrega' => $codigo,
            'local_store' => true,
        ];
    }

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

    private function adjEvidenciaRechazoHistorialTieneColumna(string $columna): bool
    {
        if (self::$adjEvidenciaRechazoHistorialColumnas === null) {
            self::$adjEvidenciaRechazoHistorialColumnas = [];
            try {
                foreach ($this->db->queryAll('SHOW COLUMNS FROM adj_evidencia_rechazo_historial') ?: [] as $row) {
                    $field = (string) ($row['Field'] ?? '');
                    if ($field !== '') {
                        self::$adjEvidenciaRechazoHistorialColumnas[$field] = true;
                    }
                }
            } catch (\Throwable $e) {
                self::$adjEvidenciaRechazoHistorialColumnas = [];
            }
        }

        return isset(self::$adjEvidenciaRechazoHistorialColumnas[$columna]);
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
     * Se prueba con SHOW COLUMNS puntual para evitar un SELECT fallido cuando la columna no existe.
     */
    public function adjOperacionTieneColumnaEnvioAtencion(): bool
    {
        if (self::$adjOperacionEnvioAtencionCol !== null) {
            return self::$adjOperacionEnvioAtencionCol;
        }
        try {
            self::$adjOperacionEnvioAtencionCol = (bool) $this->db->queryOne(
                "SHOW COLUMNS FROM adj_operacion LIKE 'atencion_envio_validado'"
            );
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
     * Prepara el body para el endpoint legacy que registra rechazos en lote y manda una sola push agrupada.
     *
     * @param array<int,array<string,mixed>> $evidenciasInput
     * @return array{success:bool,message?:string,payload?:array<string,mixed>}
     */
    public function prepararPayloadRechazoEvidenciasBulk(int $idOperacion, array $evidenciasInput, int $idUsuario, string $motivoGeneral = ''): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'ID de operacion no valido.'];
        }
        if ($evidenciasInput === []) {
            return ['success' => false, 'message' => 'No hay evidencias rechazadas para notificar.'];
        }

        $op = $this->db->queryOne(
            'SELECT id, id_credito FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op) {
            return ['success' => false, 'message' => 'No se encontro la operacion.'];
        }

        $idCredito = (int) ($op['id_credito'] ?? 0);
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'La operacion no tiene credito asociado.'];
        }

        $ids = [];
        foreach ($evidenciasInput as $ev) {
            $idEv = (int) ($ev['id_evidencia'] ?? 0);
            if ($idEv > 0) {
                $ids[$idEv] = $idEv;
            }
        }
        $ids = array_values($ids);
        if ($ids === []) {
            return ['success' => false, 'message' => 'Las evidencias rechazadas no son validas.'];
        }

        $params = ['op' => $idOperacion];
        $placeholders = [];
        foreach ($ids as $idx => $idEv) {
            $key = 'ev' . $idx;
            $placeholders[] = ':' . $key;
            $params[$key] = $idEv;
        }

        $rows = $this->db->queryAll(
            'SELECT id, slot, url
             FROM adj_evidencia
             WHERE id_operacion = :op
               AND id IN (' . implode(',', $placeholders) . ')',
            $params
        ) ?: [];
        $porId = [];
        foreach ($rows as $row) {
            $porId[(int) ($row['id'] ?? 0)] = $row;
        }

        $motivoGeneral = mb_substr(trim($motivoGeneral), 0, 500);
        if ($motivoGeneral === '') {
            $motivoGeneral = 'Evidencias incompletas o borrosas.';
        }

        $evidencias = [];
        foreach ($evidenciasInput as $ev) {
            $idEv = (int) ($ev['id_evidencia'] ?? 0);
            if ($idEv <= 0 || !isset($porId[$idEv])) {
                return ['success' => false, 'message' => 'Una evidencia no pertenece a la operacion seleccionada.'];
            }

            $row = $porId[$idEv];
            $slot = trim((string) ($row['slot'] ?? ''));
            if ($slot === '' || $slot === self::SLOT_REPVE_ATENCION) {
                return ['success' => false, 'message' => 'Una evidencia no se puede rechazar desde este flujo.'];
            }

            $motivo = mb_substr(trim((string) ($ev['motivo_rechazo'] ?? '')), 0, 500);
            if ($motivo === '') {
                $motivo = $motivoGeneral;
            }
            $urlVieja = trim((string) ($ev['url_vieja_rechazada'] ?? ''));
            if ($urlVieja === '') {
                $urlVieja = trim((string) ($row['url'] ?? ''));
            }

            $evidencias[] = [
                'id_evidencia' => $idEv,
                'slot' => $slot,
                'motivo_rechazo' => $motivo,
                'url_vieja_rechazada' => $urlVieja,
            ];
        }

        $destinatarios = $this->obtenerDestinatariosLegacyPorCredito($idCredito);
        $principal = $destinatarios[0] ?? [];
        if ($destinatarios === [] || trim((string) ($principal['external_id'] ?? '')) === '' || (int) ($principal['user_id_legacy'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'No se pudo identificar al usuario Legacy que debe recibir la notificacion.'];
        }

        return [
            'success' => true,
            'payload' => [
                'id_operacion' => $idOperacion,
                'id_credito' => $idCredito,
                'user_id_legacy' => (string) ((int) ($principal['user_id_legacy'] ?? 0)),
                'external_id' => (string) ($principal['external_id'] ?? ''),
                'rechazado_por' => $idUsuario,
                'motivo_general' => $motivoGeneral,
                'evidencias' => $evidencias,
            ],
            'destinatarios' => $destinatarios,
        ];
    }

    public function prepararPayloadAprobacionEvidenciasAtencion(int $idOperacion): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'ID de operacion invalido.'];
        }

        $op = $this->db->queryOne(
            'SELECT id, id_credito, nombre_cliente FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op || (int) ($op['id_credito'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'No se encontro la operacion para notificar aprobacion.'];
        }

        $idCredito = (int) ($op['id_credito'] ?? 0);
        $destinatarios = $this->obtenerDestinatariosLegacyPorCredito($idCredito);
        $principal = $destinatarios[0] ?? [];
        if ($destinatarios === [] || trim((string) ($principal['external_id'] ?? '')) === '' || (int) ($principal['user_id_legacy'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'No se pudo identificar al usuario Legacy que debe recibir la notificacion.'];
        }

        return [
            'success' => true,
            'payload' => [
                'id_operacion' => $idOperacion,
                'id_credito' => $idCredito,
                'nombre_cliente' => trim((string) ($op['nombre_cliente'] ?? '')),
                'user_id_legacy' => (string) ((int) ($principal['user_id_legacy'] ?? 0)),
                'external_id' => (string) ($principal['external_id'] ?? ''),
            ],
            'destinatarios' => $destinatarios,
        ];
    }

    /**
     * Devuelve posibles destinatarios de push para un credito, empezando por la asignacion activa.
     * Si el responsable activo no tiene app registrada, el controlador puede probar la asignacion anterior.
     *
     * @return array<int,array{user_id_legacy:int,external_id:string,nombre:string,origen:string}>
     */
    private function obtenerDestinatariosLegacyPorCredito(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return [];
        }

        $rows = $this->db->queryAll(
            'SELECT per.id AS id_persona,
                    TRIM(COALESCE(per.numero_empleado, \'\')) AS external_id,
                    TRIM(CONCAT_WS(\' \', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS nombre,
                    aca.estatus,
                    aca.id
             FROM asigna_creditos_adjudicacion aca
             INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
             INNER JOIN persona per ON per.id = pa.id_persona
             WHERE aca.id_credito = :id_credito
             ORDER BY (aca.estatus = \'1\') DESC, aca.id DESC
             LIMIT 8',
            ['id_credito' => $idCredito]
        ) ?: [];

        $externals = [];
        foreach ($rows as $row) {
            $externalId = trim((string) ($row['external_id'] ?? ''));
            if ($externalId !== '') {
                $externals[$externalId] = true;
            }
        }
        if ($externals === []) {
            return [];
        }

        $legacyPorExternal = [];
        try {
            $legacyDb = new DatabaseLegacy();
            $ph = [];
            $params = [];
            foreach (array_keys($externals) as $idx => $externalId) {
                $key = 'ext' . $idx;
                $ph[] = ':' . $key;
                $params[$key] = $externalId;
            }
            $legacyRows = $legacyDb->queryAll(
                'SELECT id, TRIM(COALESCE(external_id, \'\')) AS external_id
                 FROM users
                 WHERE TRIM(COALESCE(external_id, \'\')) IN (' . implode(',', $ph) . ')
                   AND deleted_at IS NULL
                 ORDER BY id DESC',
                $params
            ) ?: [];
            foreach ($legacyRows as $legacyRow) {
                $externalId = trim((string) ($legacyRow['external_id'] ?? ''));
                $id = (int) ($legacyRow['id'] ?? 0);
                if ($externalId !== '' && $id > 0 && !isset($legacyPorExternal[$externalId])) {
                    $legacyPorExternal[$externalId] = $id;
                }
            }
        } catch (\Throwable $e) {
            return [];
        }

        $out = [];
        $vistos = [];
        foreach ($rows as $row) {
            $externalId = trim((string) ($row['external_id'] ?? ''));
            $legacyUserId = (int) ($legacyPorExternal[$externalId] ?? 0);
            if ($externalId === '' || $legacyUserId <= 0 || isset($vistos[$externalId])) {
                continue;
            }
            $vistos[$externalId] = true;
            $out[] = [
                'user_id_legacy' => $legacyUserId,
                'external_id' => $externalId,
                'nombre' => trim((string) ($row['nombre'] ?? '')),
                'origen' => ((string) ($row['estatus'] ?? '') === '1') ? 'asignacion_activa' : 'asignacion_anterior',
            ];
        }

        return $out;
    }

    /**
     * Registra el historial local de rechazos sin escribir valores invalidos en adj_evidencia.estatus.
     *
     * @param array<string,mixed> $payload
     * @return array{success:bool,message?:string,rechazos?:array<int,array<string,mixed>>,slots?:array<int,string>}
     */
    public function registrarRechazosEvidenciasBulkLocal(array $payload, int $idUsuario, string $motivoGeneral = '', string $nombreUsuario = ''): array
    {
        $idOperacion = (int) ($payload['id_operacion'] ?? 0);
        $evidencias = $payload['evidencias'] ?? [];
        if ($idOperacion <= 0 || !is_array($evidencias) || $evidencias === []) {
            return ['success' => false, 'message' => 'No hay evidencias rechazadas para registrar.'];
        }

        $tieneMotivo = $this->adjEvidenciaRechazoHistorialTieneColumna('motivo_rechazo');
        $tieneMotivoGeneral = $this->adjEvidenciaRechazoHistorialTieneColumna('motivo_general');
        $tieneUpdatedAt = $this->adjEvidenciaRechazoHistorialTieneColumna('updated_at');
        $tieneAtn = $this->adjEvidenciaTieneColumnasAtn();
        $motivoGeneral = mb_substr(trim($motivoGeneral), 0, 500);
        if ($motivoGeneral === '') {
            $motivoGeneral = 'Favor de corregir las evidencias marcadas.';
        }

        $rechazos = [];
        $slots = [];

        try {
            $this->db->beginTransaction();

            foreach ($evidencias as $ev) {
                if (!is_array($ev)) {
                    throw new \RuntimeException('Una evidencia no es valida.');
                }

                $idEvidencia = (int) ($ev['id_evidencia'] ?? 0);
                $slot = trim((string) ($ev['slot'] ?? ''));
                $urlVieja = trim((string) ($ev['url_vieja_rechazada'] ?? ''));
                $motivo = mb_substr(trim((string) ($ev['motivo_rechazo'] ?? '')), 0, 500);
                if ($motivo === '') {
                    $motivo = $motivoGeneral;
                }
                if ($idEvidencia <= 0 || $slot === '') {
                    throw new \RuntimeException('Una evidencia rechazada no tiene datos completos.');
                }

                $existente = $this->db->queryOne(
                    'SELECT id
                     FROM adj_evidencia_rechazo_historial
                     WHERE id_operacion = :op
                       AND id_evidencia = :ev
                       AND slot = :slot
                       AND url_nueva IS NULL
                     ORDER BY id DESC
                     LIMIT 1',
                    [
                        'op' => $idOperacion,
                        'ev' => $idEvidencia,
                        'slot' => $slot,
                    ]
                );

                if ($existente) {
                    $set = [
                        'url_vieja_rechazada = :url_vieja',
                        'fecha_rechazo = NOW()',
                        'id_usuario_rechazo = :usuario',
                    ];
                    $params = [
                        'id' => (int) ($existente['id'] ?? 0),
                        'url_vieja' => $urlVieja,
                        'usuario' => $idUsuario,
                    ];
                    if ($tieneMotivo) {
                        $set[] = 'motivo_rechazo = :motivo';
                        $params['motivo'] = $motivo;
                    }
                    if ($tieneMotivoGeneral) {
                        $set[] = 'motivo_general = :motivo_general';
                        $params['motivo_general'] = $motivoGeneral;
                    }
                    if ($tieneUpdatedAt) {
                        $set[] = 'updated_at = NOW()';
                    }
                    $this->db->CRUD(
                        'UPDATE adj_evidencia_rechazo_historial
                         SET ' . implode(', ', $set) . '
                         WHERE id = :id',
                        $params
                    );
                    $rechazoId = (int) ($existente['id'] ?? 0);
                } else {
                    $cols = [
                        'id_operacion',
                        'id_evidencia',
                        'slot',
                        'url_vieja_rechazada',
                        'fecha_rechazo',
                        'id_usuario_rechazo',
                    ];
                    $vals = [
                        ':op',
                        ':ev',
                        ':slot',
                        ':url_vieja',
                        'NOW()',
                        ':usuario',
                    ];
                    $params = [
                        'op' => $idOperacion,
                        'ev' => $idEvidencia,
                        'slot' => $slot,
                        'url_vieja' => $urlVieja,
                        'usuario' => $idUsuario,
                    ];
                    if ($tieneMotivo) {
                        $cols[] = 'motivo_rechazo';
                        $vals[] = ':motivo';
                        $params['motivo'] = $motivo;
                    }
                    if ($tieneMotivoGeneral) {
                        $cols[] = 'motivo_general';
                        $vals[] = ':motivo_general';
                        $params['motivo_general'] = $motivoGeneral;
                    }

                    $this->db->CRUD(
                        'INSERT INTO adj_evidencia_rechazo_historial (`' . implode('`, `', $cols) . '`)
                         VALUES (' . implode(', ', $vals) . ')',
                        $params
                    );
                    $rechazoId = $this->db->lastInsertId();
                }

                if ($tieneAtn) {
                    $this->db->CRUD(
                        'UPDATE adj_evidencia
                         SET val_atn = 2,
                             comentario_atn = :comentario
                         WHERE id = :ev
                           AND id_operacion = :op',
                        [
                            'comentario' => $motivo,
                            'ev' => $idEvidencia,
                            'op' => $idOperacion,
                        ]
                    );
                }

                $rechazos[] = [
                    'id_evidencia' => $idEvidencia,
                    'slot' => $slot,
                    'rechazo_historial_id' => $rechazoId,
                ];
                $slots[$slot] = $slot;
            }

            $this->registrarBitacora(
                $idOperacion,
                'REGISTRO RECHAZOS EVIDENCIAS APP (' . count($rechazos) . ')',
                $idUsuario,
                $nombreUsuario
            );

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => 'Error al registrar rechazos: ' . $e->getMessage()];
        }

        return [
            'success' => true,
            'rechazos' => $rechazos,
            'slots' => array_values($slots),
        ];
    }

    /**
     * Crea el task Legacy para la campana MOTOS ADJUDICADAS AUTORIZADAS.
     *
     * @return array{success:bool, message:string, task_id?:int, duplicate?:bool}
     */
    public function crearTaskLegacyMotoAutorizada(int $idCredito, int $idPersonaUsuarioAlta = 0, array $datosDictamen = []): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'Credito invalido para crear task legacy.'];
        }

        try {
            $legacyDb = new DatabaseLegacy();
            $campaignId = self::LEGACY_CAMPAIGN_MOTOS_ADJ_AUTORIZADAS;
            $legacyUserId = $this->resolverLegacyUserIdPorPersona($idPersonaUsuarioAlta);
            if ($legacyUserId <= 0) {
                return ['success' => false, 'message' => 'No se encontro usuario Legacy para quien guarda el seguimiento.'];
            }

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
                $taskId = (int) $dup['id'];
                $ahora = $this->fechaHoraCdmx();
                $datos = $this->obtenerDatosTaskLegacyMotoAutorizada($idCredito, $datosDictamen);
                $legacyDb->CRUD(
                    'UPDATE tasks
                     SET current_user_id = :user_id,
                         client_name = :client_name,
                         address = :address,
                         lat = :lat,
                         lng = :lng,
                         updated_at = :updated_at
                     WHERE id = :task_id',
                    [
                        'user_id' => $legacyUserId,
                        'client_name' => $datos['client_name'],
                        'address' => $datos['address'],
                        'lat' => $datos['lat'],
                        'lng' => $datos['lng'],
                        'updated_at' => $ahora,
                        'task_id' => $taskId,
                    ]
                );
                $this->asegurarAsignacionTaskLegacy($legacyDb, $taskId, $legacyUserId, $ahora);

                $verificacion = $this->verificarTaskLegacyMotoAutorizada(
                    $idCredito,
                    $idPersonaUsuarioAlta,
                    $taskId,
                    (string) $datos['client_name']
                );

                return [
                    'success'   => !empty($verificacion['success']),
                    'duplicate' => true,
                    'task_id'   => $taskId,
                    'message'   => !empty($verificacion['success'])
                        ? 'Ya existia la tarea Legacy; responsable y datos del cliente actualizados y verificados.'
                        : (string) ($verificacion['message'] ?? 'No se pudo verificar la tarea Legacy.'),
                    'verificacion' => $verificacion,
                ];
            }

            $datos = $this->obtenerDatosTaskLegacyMotoAutorizada($idCredito, $datosDictamen);
            $ahora = $this->fechaHoraCdmx();
            $legacyDb->CRUD(
                'INSERT INTO tasks
                    (campaign_id, current_user_id, client_name, credit_number, address, lat, lng,
                     form_data, form_answered, status, deleted_at, created_at, updated_at)
                 VALUES
                    (:campaign_id, :current_user_id, :client_name, :credit_number, :address, :lat, :lng,
                     :form_data, NULL, :status, NULL, :created_at, :updated_at)',
                [
                    'campaign_id'     => $campaignId,
                    'current_user_id' => $legacyUserId,
                    'client_name'     => $datos['client_name'],
                    'credit_number'   => (string) $idCredito,
                    'address'         => $datos['address'],
                    'lat'             => $datos['lat'],
                    'lng'             => $datos['lng'],
                    'form_data'       => $this->formDataLegacyMotoAutorizada(),
                    'status'          => 0,
                    'created_at'      => $ahora,
                    'updated_at'      => $ahora,
                ]
            );

            $row = $legacyDb->queryOne('SELECT LAST_INSERT_ID() AS id');
            $taskId = (int) ($row['id'] ?? 0);
            if ($taskId <= 0) {
                return ['success' => false, 'message' => 'Task creado, pero no se pudo obtener su ID.'];
            }

            $this->asegurarAsignacionTaskLegacy($legacyDb, $taskId, $legacyUserId, $ahora);

            $verificacion = $this->verificarTaskLegacyMotoAutorizada(
                $idCredito,
                $idPersonaUsuarioAlta,
                $taskId,
                (string) $datos['client_name']
            );

            return [
                'success' => !empty($verificacion['success']),
                'task_id' => $taskId,
                'message' => !empty($verificacion['success'])
                    ? 'Tarea creada y verificada en Motos Adjudicadas Legacy.'
                    : (string) ($verificacion['message'] ?? 'La tarea se creo, pero no pudo verificarse.'),
                'verificacion' => $verificacion,
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

    private function asegurarAsignacionTaskLegacy(DatabaseLegacy $legacyDb, int $taskId, int $legacyUserId, string $fecha): void
    {
        if ($taskId <= 0 || $legacyUserId <= 0) {
            return;
        }

        $legacyDb->CRUD(
            'UPDATE task_user_assignments
             SET unassigned_at = :unassigned_at, updated_at = :updated_at
             WHERE task_id = :task_id
               AND user_id <> :user_id
               AND unassigned_at IS NULL',
            [
                'unassigned_at' => $fecha,
                'updated_at' => $fecha,
                'task_id' => $taskId,
                'user_id' => $legacyUserId,
            ]
        );

        $asignacion = $legacyDb->queryOne(
            'SELECT id
             FROM task_user_assignments
             WHERE task_id = :task_id
               AND user_id = :user_id
               AND unassigned_at IS NULL
             ORDER BY id DESC
             LIMIT 1',
            [
                'task_id' => $taskId,
                'user_id' => $legacyUserId,
            ]
        );
        if ($asignacion && (int) ($asignacion['id'] ?? 0) > 0) {
            return;
        }

        $legacyDb->CRUD(
            'INSERT INTO task_user_assignments
                (task_id, user_id, assigned_at, unassigned_at, created_at, updated_at)
             VALUES
                (:task_id, :user_id, :assigned_at, NULL, :created_at, :updated_at)',
            [
                'task_id'     => $taskId,
                'user_id'     => $legacyUserId,
                'assigned_at' => $fecha,
                'created_at'  => $fecha,
                'updated_at'  => $fecha,
            ]
        );
    }

    /**
     * Comprueba que la tarea Legacy, su responsable y la asignacion activa
     * correspondan al credito y a la persona indicados.
     */
    public function verificarTaskLegacyMotoAutorizada(
        int $idCredito,
        int $idPersona,
        int $taskId = 0,
        string $clienteEsperado = ''
    ): array
    {
        if ($idCredito <= 0 || $idPersona <= 0) {
            return ['success' => false, 'message' => 'Datos incompletos para verificar la tarea Legacy.'];
        }

        try {
            $persona = $this->db->queryOne(
                "SELECT
                    TRIM(CONCAT_WS(' ', nombres, segundo_nombre, apellidop, apellidom)) AS nombre,
                    TRIM(COALESCE(numero_empleado, '')) AS numero_empleado
                 FROM persona
                 WHERE id = :id
                 LIMIT 1",
                ['id' => $idPersona]
            );
            $externalId = trim((string) ($persona['numero_empleado'] ?? ''));
            if ($externalId === '') {
                return ['success' => false, 'message' => 'La persona no tiene numero_empleado para vincularla con Legacy.'];
            }

            $legacyDb = new DatabaseLegacy();
            $usuario = $legacyDb->queryOne(
                "SELECT id, TRIM(COALESCE(external_id, '')) AS external_id
                 FROM users
                 WHERE TRIM(COALESCE(external_id, '')) = :external_id
                   AND deleted_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1",
                ['external_id' => $externalId]
            );
            $legacyUserId = (int) ($usuario['id'] ?? 0);
            if ($legacyUserId <= 0) {
                return ['success' => false, 'message' => 'No existe un usuario Legacy activo con external_id ' . $externalId . '.'];
            }

            $params = [
                'campaign_id' => self::LEGACY_CAMPAIGN_MOTOS_ADJ_AUTORIZADAS,
                'credit_number' => (string) $idCredito,
            ];
            $filtroTask = '';
            if ($taskId > 0) {
                $filtroTask = ' AND t.id = :task_id';
                $params['task_id'] = $taskId;
            }
            $task = $legacyDb->queryOne(
                'SELECT t.id, t.current_user_id, t.client_name, t.credit_number,
                        TRIM(COALESCE(c.name, \'\')) AS campaign_name
                 FROM tasks t
                 LEFT JOIN campaigns c ON c.id = t.campaign_id
                 WHERE t.campaign_id = :campaign_id
                   AND t.credit_number = :credit_number
                   AND t.deleted_at IS NULL' . $filtroTask . '
                 ORDER BY t.id DESC
                 LIMIT 1',
                $params
            );
            $taskIdReal = (int) ($task['id'] ?? 0);
            if ($taskIdReal <= 0) {
                return ['success' => false, 'message' => 'No se encontro la tarea del credito en Motos Adjudicadas Legacy.'];
            }

            $asignacion = $legacyDb->queryOne(
                'SELECT id
                 FROM task_user_assignments
                 WHERE task_id = :task_id
                   AND user_id = :user_id
                   AND unassigned_at IS NULL
                 ORDER BY id DESC
                 LIMIT 1',
                ['task_id' => $taskIdReal, 'user_id' => $legacyUserId]
            );
            $otrasAsignaciones = $legacyDb->queryOne(
                'SELECT COUNT(*) AS total
                 FROM task_user_assignments
                 WHERE task_id = :task_id
                   AND user_id <> :user_id
                   AND unassigned_at IS NULL',
                ['task_id' => $taskIdReal, 'user_id' => $legacyUserId]
            );
            $responsableCorrecto = (int) ($task['current_user_id'] ?? 0) === $legacyUserId;
            $asignacionActiva = (int) ($asignacion['id'] ?? 0) > 0;
            $asignacionExclusiva = (int) ($otrasAsignaciones['total'] ?? 0) === 0;
            $clienteActual = trim((string) ($task['client_name'] ?? ''));
            $normalizarNombre = static function (string $valor): string {
                $valor = preg_replace('/\s+/u', ' ', trim($valor)) ?? trim($valor);
                return mb_strtoupper($valor, 'UTF-8');
            };
            $clienteCorrecto = $clienteEsperado === ''
                ? $clienteActual !== ''
                : $normalizarNombre($clienteActual) === $normalizarNombre($clienteEsperado);
            $verificado = $responsableCorrecto && $asignacionActiva && $asignacionExclusiva && $clienteCorrecto;

            return [
                'success' => $verificado,
                'message' => $verificado
                    ? 'Tarea, responsable y nombre del cliente verificados en Legacy.'
                    : 'La tarea existe, pero no coincidieron el responsable, la asignacion exclusiva o el nombre del cliente.',
                'task_id' => $taskIdReal,
                'legacy_user_id' => $legacyUserId,
                'campaign_name' => trim((string) ($task['campaign_name'] ?? '')),
                'external_id' => $externalId,
                'responsable' => trim((string) ($persona['nombre'] ?? '')),
                'client_name' => $clienteActual,
                'responsable_correcto' => $responsableCorrecto,
                'asignacion_activa' => $asignacionActiva,
                'asignacion_exclusiva' => $asignacionExclusiva,
                'cliente_correcto' => $clienteCorrecto,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'No se pudo verificar la tarea Legacy: ' . $e->getMessage()];
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
        $selectSiNo = static function (string $label, string $name, string $uuid, bool $required = false): array {
            return [
                'type' => 'select', 'required' => $required, 'label' => $label,
                'className' => 'form-control', 'name' => $name, 'editable' => true,
                'section' => 'questions', 'conditional' => false, 'uuid' => $uuid,
                'typeApp' => 'select',
                'values' => [
                    ['label' => 'Sí', 'value' => 'si', 'selected' => true],
                    ['label' => 'No', 'value' => 'no', 'selected' => false],
                ],
                'value' => '',
            ];
        };

        $textarea = static function (
            string $label,
            string $name,
            string $uuid,
            string $section = 'questions',
            bool $required = true
        ): array {
            return [
                'type' => 'textarea', 'required' => $required, 'label' => $label,
                'className' => 'form-control', 'name' => $name, 'subtype' => 'textarea',
                'editable' => true, 'section' => $section, 'conditional' => false,
                'uuid' => $uuid, 'typeApp' => 'textarea', 'value' => '',
            ];
        };

        $media = static function (
            string $label,
            string $name,
            string $uuid,
            string $typeApp,
            bool $required = true
        ): array {
            return [
                'type' => 'text', 'required' => $required, 'label' => $label,
                'className' => 'form-control', 'name' => $name, 'subtype' => 'text',
                'editable' => true, 'section' => 'questions', 'conditional' => false,
                'uuid' => $uuid, 'typeApp' => $typeApp, 'value' => '',
            ];
        };

        $fields = [
            $selectSiNo('¿Tiene llave física?', 'tiene_llave_fisica', 'dfcd6ca6-f1ae-4807-a462-3d9c346f0b16'),
            $selectSiNo('¿Tiene tarjeta de circulación en físico?', 'tiene_tarjeta_de_circulacion_en_fisico', '5446560b-ac6a-4827-9c6b-cc3c74954a40'),
            $selectSiNo('¿La moto tiene placa física ?', 'la_moto_tiene_placa_fisica', 'b07276c9-cc0c-42ac-b38c-adfefc42913e'),
            $textarea('Marca', 'marca', '0d57879b-69c0-41f1-bb7c-cfe1833aa1aa', 'customer'),
            $textarea('Modelo', 'modelo', '7f896bdd-3c9c-40e8-8c13-4b3925b1c8bc', 'customer'),
            $textarea('Año', 'ano', '0743a74d-9c7e-4512-a5ed-9cd8a5ad60b8', 'customer'),
            $textarea('Color', 'color', '5dfd8e41-7468-4ea4-a9dc-293a357f8294', 'customer'),
            $textarea('No. de Serie (VIN', 'no_de_serie_vin', 'c692709e-6f49-434f-bdbb-85d0186098a7', 'customer'),
            $textarea('No. de Motor', 'no_de_motor', '46d9e3a1-7ee4-4230-836a-a22f7e2524fd', 'customer'),
            $textarea('Placas', 'placas', 'b2f01f91-b1fa-4c0a-a0ca-5e22f3233587', 'customer'),
            $textarea('Kilometraje', 'kilometraje', '2fdcef10-973b-408e-a0f0-46aceef4afab'),
            [
                'type' => 'select', 'required' => false, 'label' => '¿Dónde resguardaras la moto?',
                'className' => 'form-control', 'name' => 'donde_resguardaras_la_moto',
                'editable' => true, 'section' => 'questions', 'conditional' => false,
                'uuid' => '73eddd54-4de5-4054-babd-5f5766b18703', 'typeApp' => 'select',
                'values' => [
                    ['label' => 'CEDIS Maxikash', 'value' => 'cedis-__SPARTA_SECRET_REDACTED__', 'selected' => true],
                    ['label' => 'Centro de acopio', 'value' => 'centro-de-acopio', 'selected' => false],
                    ['label' => 'Agencia ', 'value' => 'agencia', 'selected' => false],
                    ['label' => 'Otro', 'value' => 'otro', 'selected' => false],
                ],
                'value' => '',
            ],
            $textarea('Estado de lugar de resguardo (Ejemplo Ciudad de México, Veracruz, Oaxaca, etc.)', 'estado_de_lugar_de_resguardo_ejemplo_ciudad_de_mex', '3666bb60-bb6b-460e-9bbf-ddea362801af'),
            $textarea('Ciudad / Municipio de lugar de Resguardo', 'ciudad_municipio_de_lugar_de_resguardo', '68bea6ea-8e99-4588-b20a-dcc2c959e8fb'),
            $textarea('Calle y número de lugar de resguardo', 'calle_y_numero_de_lugar_de_resguardo', 'f7de67b6-27bd-4bf9-9839-0f76c349fb72'),
            $textarea('Responsable de Resguardo', 'responsable_de_resguardo', '688c766b-d427-4c65-bef3-66e07ba1a931'),
            [
                'type' => 'number', 'required' => true, 'label' => 'Teléfono de contacto',
                'className' => 'form-control', 'name' => 'telefono_de_contacto',
                'subtype' => 'number', 'editable' => true, 'section' => 'questions',
                'conditional' => false, 'uuid' => '6ffb46d9-a417-4370-b531-efb5167f56fd',
                'typeApp' => 'number', 'value' => '',
            ],
            $media('Foto dación hoja 1', 'foto_dacion_hoja_1', 'c90bb291-0701-4d2a-a18e-8d53b5fbbb27', 'photo'),
            $media('Foto dación hoja 2', 'foto_dacion_hoja_2', '2a939c63-df8d-4f81-bb2d-7feb2c4bcb20', 'photo'),
            $media('Foto de Tacómetro&nbsp; (Legible y visible el kilometraje)', 'text-1778722329133-0', 'be225e92-cc45-42b8-98a3-27f8a9244f14', 'photo'),
            $media('Foto de Número de Serie (foto legible donde se lea la serie de 17 dígitos)', 'foto_de_numero_de_serie_foto_legible_donde_se_lea_', 'a0e34323-191e-40a9-9f47-0e03338be6a3', 'photo'),
            $media('Foto frontal de la moto (la foto debe estar visible toda la parte frontal y centrada)', 'foto_frontal_de_la_moto_la_foto_debe_estar_visible', 'f8efc9e4-3d18-4abc-b222-2e8324844bd7', 'photo'),
            $media('Foto trasera de la moto (la foto debe ser visible toda la parte trasera y centrada, se debe ver el espejo y la llanta)', 'foto_trasera_de_la_moto_la_foto_debe_ser_visible_t', 'abb8ebb0-713e-4f3a-8223-539c8bebd687', 'photo'),
            $media('Foto lateral izquierda de la moto (foto legible de preferencia agachado y centrada para poder ver toda la moto)', 'foto_lateral_izquierda_de_la_moto_foto_legible_de_', 'a572d294-6fee-495c-a9c2-52e5f1ead6d9', 'photo'),
            $media('Foto lateral derecha de la moto (foto legible de preferencia agachado y centrada para poder ver toda la moto)', 'foto_lateral_derecha_de_la_moto_foto_legible_de_pr', '750b170c-8afe-4a4f-bbb5-a7cc68541255', 'photo'),
            $media('Foto de check list', 'foto_de_check_list', 'dddb74c9-578b-41b8-b459-50fab0c209c8', 'photo'),
            $media('Inspección 360 de Moto (el video debe evidenciar el funcionamiento eléctrico y debe estar enfocada a toda la unidad)', 'inspeccion_360_de_moto_el_video_debe_evidenciar_el', '086f9488-15c5-4695-99cf-26184524f739', 'video'),
            $media('Video cliente de acuerdo&nbsp;', 'video_cliente_de_acuerdo', '7697e3ac-aa9e-48e5-825c-b4ea4f0afe21', 'video'),
            $media('Video vuelta de prueba', 'video_vuelta_de_prueba', '4fce9cf9-d409-4387-842f-059ae483af00', 'video'),
            [
                'type' => 'select', 'required' => true, 'label' => 'Dictamen',
                'className' => 'form-control', 'name' => 'dictamen', 'editable' => false,
                'section' => 'customer', 'conditional' => false, 'uuid' => '',
                'typeApp' => 'select',
                'values' => [['label' => 'Atendido', 'value' => 0, 'selected' => true]],
                'value' => null,
            ],
        ];

        $json = json_encode($fields, JSON_UNESCAPED_UNICODE);
        return json_encode($json) ?: (string) $json;

        $json = '[{"type":"select","required":false,"label":"¿Tiene llave física?","className":"form-control","name":"tiene_llave_fisica","editable":true,"section":"questions","conditional":false,"uuid":"dfcd6ca6-f1ae-4807-a462-3d9c346f0b16","typeApp":"select","values":[{"label":"Sí","value":"si","selected":true},{"label":"No","value":"no","selected":false}],"value":""},{"type":"select","required":false,"label":"¿Tiene tarjeta de circulación en físico?","className":"form-control","name":"tiene_tarjeta_de_circulacion_en_fisico","editable":true,"section":"questions","conditional":false,"uuid":"5446560b-ac6a-4827-9c6b-cc3c74954a40","typeApp":"select","values":[{"label":"Sí","value":"si","selected":true},{"label":"No","value":"no","selected":false}],"value":""},{"type":"select","required":false,"label":"¿La moto tiene placa física ?","className":"form-control","name":"la_moto_tiene_placa_fisica","editable":true,"section":"questions","conditional":false,"uuid":"b07276c9-cc0c-42ac-b38c-adfefc42913e","typeApp":"select","values":[{"label":"Sí","value":"si","selected":true},{"label":"No","value":"no","selected":false}],"value":""},{"type":"textarea","required":true,"label":"Marca","className":"form-control","name":"marca","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"0d57879b-69c0-41f1-bb7c-cfe1833aa1aa","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Modelo","className":"form-control","name":"modelo","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"7f896bdd-3c9c-40e8-8c13-4b3925b1c8bc","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Año","className":"form-control","name":"ano","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"0743a74d-9c7e-4512-a5ed-9cd8a5ad60b8","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Color","className":"form-control","name":"color","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"5dfd8e41-7468-4ea4-a9dc-293a357f8294","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"No. de Serie (VIN","className":"form-control","name":"no_de_serie_vin","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"c692709e-6f49-434f-bdbb-85d0186098a7","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"No. de Motor","className":"form-control","name":"no_de_motor","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"46d9e3a1-7ee4-4230-836a-a22f7e2524fd","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Placas","className":"form-control","name":"placas","subtype":"textarea","editable":true,"section":"customer","conditional":false,"uuid":"b2f01f91-b1fa-4c0a-a0ca-5e22f3233587","typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Kilometraje","className":"form-control","name":"kilometraje","subtype":"textarea","editable":true,"section":"questions","conditional":false,"uuid":"2fdcef10-973b-408e-a0f0-46aceef4afab","typeApp":"textarea","value":""},{"type":"select","label":"¿Dónde resguardaras la moto?","uuid":"73eddd54-4de5-4054-babd-5f5766b18703","editable":true,"section":"questions","required":false,"className":"form-control","name":"donde_resguardaras_la_moto","conditional":false,"typeApp":"select","values":[{"label":"CEDIS Maxikash","value":"cedis-__SPARTA_SECRET_REDACTED__","selected":true},{"label":"Centro de acopio","value":"centro-de-acopio","selected":false},{"label":"Agencia ","value":"agencia","selected":false},{"label":"Otro","value":"otro","selected":false}],"value":""},{"type":"textarea","label":"Estado de lugar de resguardo (Ejemplo Ciudad de México, Veracruz, Oaxaca, etc.)","uuid":"3666bb60-bb6b-460e-9bbf-ddea362801af","editable":true,"section":"questions","required":true,"className":"form-control","name":"estado_de_lugar_de_resguardo_ejemplo_ciudad_de_mex","subtype":"textarea","conditional":false,"typeApp":"textarea","value":""},{"type":"textarea","label":"Ciudad / Municipio de lugar de Resguardo","uuid":"68bea6ea-8e99-4588-b20a-dcc2c959e8fb","editable":true,"section":"questions","required":true,"className":"form-control","name":"ciudad_municipio_de_lugar_de_resguardo","subtype":"textarea","conditional":false,"typeApp":"textarea","value":""},{"type":"textarea","label":"Calle y número de lugar de resguardo","uuid":"f7de67b6-27bd-4bf9-9839-0f76c349fb72","editable":true,"section":"questions","required":true,"className":"form-control","name":"calle_y_numero_de_lugar_de_resguardo","subtype":"textarea","conditional":false,"typeApp":"textarea","value":""},{"type":"textarea","required":true,"label":"Responsable de Resguardo","className":"form-control","name":"responsable_de_resguardo","subtype":"textarea","editable":true,"section":"questions","conditional":false,"uuid":"688c766b-d427-4c65-bef3-66e07ba1a931","typeApp":"textarea","value":""},{"type":"number","required":true,"label":"Teléfono de contacto","className":"form-control","name":"telefono_de_contacto","subtype":"number","editable":true,"section":"questions","conditional":false,"uuid":"6ffb46d9-a417-4370-b531-efb5167f56fd","typeApp":"number","value":""},{"type":"text","label":"Foto de Tacómetro&nbsp; (Legible y visible el kilometraje)","uuid":"c90bb291-0701-4d2a-a18e-8d53b5fbbb27","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_de_tacometro_legible_y_visible_el_kilometraje","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto de Número de Serie (foto legible donde se lea la serie de 17 dígitos)","uuid":"a0e34323-191e-40a9-9f47-0e03338be6a3","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_de_numero_de_serie_foto_legible_donde_se_lea_","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto frontal de la moto (la foto debe estar visible toda la parte frontal y centrada)","uuid":"f8efc9e4-3d18-4abc-b222-2e8324844bd7","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_frontal_de_la_moto_la_foto_debe_estar_visible","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto trasera de la moto (la foto debe ser visible toda la parte trasera y centrada, se debe ver el espejo y la llanta)","uuid":"abb8ebb0-713e-4f3a-8223-539c8bebd687","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_trasera_de_la_moto_la_foto_debe_ser_visible_t","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto lateral izquierda de la moto (foto legible de preferencia agachado y centrada para poder ver toda la moto)","uuid":"a572d294-6fee-495c-a9c2-52e5f1ead6d9","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_lateral_izquierda_de_la_moto_foto_legible_de_","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Foto lateral derecha de la moto (foto legible de preferencia agachado y centrada para poder ver toda la moto)","uuid":"750b170c-8afe-4a4f-bbb5-a7cc68541255","editable":true,"section":"questions","required":true,"className":"form-control","name":"foto_lateral_derecha_de_la_moto_foto_legible_de_pr","subtype":"text","conditional":false,"typeApp":"photo","value":""},{"type":"text","label":"Inspección 360 de Moto (el video debe evidenciar el funcionamiento eléctrico y debe estar enfocada a toda la unidad)","uuid":"086f9488-15c5-4695-99cf-26184524f739","editable":true,"section":"questions","required":true,"className":"form-control","name":"inspeccion_360_de_moto_el_video_debe_evidenciar_el","subtype":"text","conditional":false,"typeApp":"video","value":""},{"type":"select","required":true,"label":"Dictamen","className":"form-control","name":"dictamen","editable":false,"section":"customer","conditional":false,"uuid":"","typeApp":"select","values":[{"label":"Atendido","value":0,"selected":true}],"value":null}]';
        return json_encode($json) ?: $json;
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

    private function normalizarFechaHoraOperacion(?string $fecha = null): string
    {
        $valor = trim((string) $fecha);
        if ($valor === '' || $valor === '0000-00-00 00:00:00') {
            return $this->fechaHoraCdmx();
        }

        $valor = str_replace('T', ' ', $valor);
        try {
            $dt = new \DateTime($valor, new \DateTimeZone('America/Mexico_City'));
            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return $this->fechaHoraCdmx();
        }
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
            'fis_ine_frente', 'fis_ine_reverso',
            'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
            'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba',
            'fis_checklist',
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
        if ($slot === self::SLOT_REPVE_ATENCION && $this->adjEvidenciaTieneColumnasAtn()) {
            $faltantesMedia = $this->faltantesMediaAceptadaAtencion($idOperacion);
            if ($faltantesMedia !== []) {
                return [
                    'success' => false,
                    'message' => 'Antes de subir REPUVE deben estar aceptadas las 12 evidencias fisicas. Faltan: '
                        . implode(', ', array_slice($faltantesMedia, 0, 4))
                        . (count($faltantesMedia) > 4 ? '...' : ''),
                ];
            }
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

        // 4. Limite de tamano: 100 MB
        if (($fileInfo['size'] ?? 0) > 100 * 1024 * 1024) {
            return ['success' => false, 'message' => 'El archivo supera el limite de 100 MB.'];
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
        $limpiarVeredictoAtencion = !$this->operacionTieneEnvioAtencionMarcado($idOperacion);

        // 8. INSERT o UPDATE en adj_evidencia (al reemplazar archivo se limpia veredicto de Atenci?n para revalidar)
        if ($old) {
            if ($this->adjEvidenciaTieneColumnasAtn() && $limpiarVeredictoAtencion) {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                        SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = :estatus,
                            val_atn = NULL, comentario_atn = NULL
                      WHERE id_operacion = :id AND slot = :slot",
                    ['tipo' => $tipo, 'url' => $urlRelativa, 'fecha' => $ahora, 'estatus' => $estatusEvidencia,
                     'id'   => $idOperacion, 'slot' => $slot]
                );
            } elseif ($this->adjEvidenciaTieneColumnasAtn()) {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                        SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = :estatus
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
     * Reemplazo especial desde Atención: solo para evidencias físicas validables.
     * Limpia el veredicto y neutraliza historial de rechazo pendiente para que no se regenere.
     */
    public function reemplazarEvidenciaGestor(int $idOperacion, string $slot, array $fileInfo, int $idUsuario, string $nombreUsuario = ''): array
    {
        if (!in_array($slot, self::SLOTS_VALIDACION_ATENCION_MEDIA, true)) {
            return ['success' => false, 'message' => 'Este slot no se puede reemplazar desde esta herramienta.'];
        }

        $res = $this->subirEvidencia($idOperacion, $slot, $fileInfo, $idUsuario, $nombreUsuario);
        if (empty($res['success'])) {
            return $res;
        }

        if ($this->adjEvidenciaTieneColumnasAtn()) {
            $row = $this->db->queryOne(
                'SELECT id, url FROM adj_evidencia WHERE id_operacion = :id AND slot = :slot LIMIT 1',
                ['id' => $idOperacion, 'slot' => $slot]
            );
            $idEvidencia = (int) ($row['id'] ?? 0);
            $urlNueva = trim((string) ($row['url'] ?? ''));
            $ahora = $this->fechaHoraCdmx();

            if ($idEvidencia > 0) {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                     SET val_atn = NULL,
                         comentario_atn = NULL,
                         estatus = 'recibido'
                     WHERE id = :id_evidencia
                       AND id_operacion = :id_operacion",
                    ['id_evidencia' => $idEvidencia, 'id_operacion' => $idOperacion]
                );

                try {
                    $this->db->CRUD(
                        "UPDATE adj_evidencia_rechazo_historial h
                         INNER JOIN (
                             SELECT id
                             FROM adj_evidencia_rechazo_historial
                             WHERE id_operacion = :id_operacion_sel
                               AND id_evidencia = :id_evidencia_sel
                             ORDER BY id DESC
                             LIMIT 1
                         ) ult ON ult.id = h.id
                         SET h.url_nueva = :url_nueva,
                             h.fecha_url_nueva = :fecha,
                             h.updated_at = :fecha2",
                        [
                            'id_operacion_sel' => $idOperacion,
                            'id_evidencia_sel' => $idEvidencia,
                            'url_nueva' => $urlNueva,
                            'fecha' => $ahora,
                            'fecha2' => $ahora,
                        ]
                    );
                } catch (\Throwable $e) {
                    error_log('[MotosAdjudicadas] reemplazarEvidenciaGestor historial: ' . $e->getMessage());
                }
            }
        }

        $slotLabel = self::SLOT_LABELS[$slot] ?? strtoupper($slot);
        $this->registrarBitacora($idOperacion, 'REEMPLAZO ESPECIAL DE EVIDENCIA: ' . $slotLabel, $idUsuario, $nombreUsuario);
        $this->finalizarCierreValidacionEvidenciaAtn($idOperacion, $idUsuario, $nombreUsuario);

        return $res;
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
            '(PROCESANDO IA)' => '(RECUPERACION)',
            'ENVIÓ EVIDENCIAS AL PIPELINE' => self::ACCION_GESTOR_ENVIO_EVIDENCIAS_ADJUDICACION,
            'ENVIO EVIDENCIAS AL PIPELINE' => self::ACCION_GESTOR_ENVIO_EVIDENCIAS_ADJUDICACION,
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
        $labelsPorEvidencia = [];
        foreach (($this->db->queryAll(
            'SELECT id, slot FROM adj_evidencia WHERE id_operacion = :id',
            ['id' => $idOperacion]
        ) ?: []) as $ev) {
            $idEv = (int) ($ev['id'] ?? 0);
            $slot = (string) ($ev['slot'] ?? '');
            if ($idEv > 0 && $slot !== '') {
                $labelsPorEvidencia[$idEv] = self::SLOT_LABELS[$slot] ?? $slot;
            }
        }
        foreach ($rows as &$r) {
            $accion = $this->normalizarAdjBitacoraAccionDisplay((string) ($r['accion'] ?? ''));
            $accion = preg_replace_callback(
                '/\s*\(id evidencia\s+(\d+)\)\s*/i',
                static function (array $m) use ($labelsPorEvidencia): string {
                    $idEv = (int) ($m[1] ?? 0);
                    $label = trim((string) ($labelsPorEvidencia[$idEv] ?? ''));
                    return $label !== '' ? ' - ' . $label : '';
                },
                $accion
            );
            if (preg_match('/^VALIDACI(?:Ó|O)N EVIDENCIA\s+(ACEPTADA|RECHAZADA)\s+-\s+(.+)$/iu', $accion, $m)) {
                $accion = 'VALIDACION EVIDENCIA ' . trim((string) $m[2]) . ': ' . strtoupper((string) $m[1]);
            }
            $r['accion'] = $accion;
        }
        unset($r);

        return $rows;
    }

    private function obtenerUltimoAnalistaEvidencias(int $idOperacion): ?array
    {
        if ($idOperacion <= 0) {
            return null;
        }

        $row = $this->db->queryOne(
            "SELECT nombre_usuario,
                    accion,
                    DATE_FORMAT(fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta
             FROM adj_bitacora
             WHERE id_operacion = :id
               AND (
                    UPPER(accion) LIKE '%VALIDACI%'
                    OR UPPER(accion) LIKE '%RECHAZOS EVIDENCIAS%'
                    OR UPPER(accion) LIKE '%EVIDENCIAS VALIDADAS%'
                    OR UPPER(accion) LIKE '%REEMPLAZO ESPECIAL DE EVIDENCIA%'
               )
               AND TRIM(COALESCE(nombre_usuario, '')) <> ''
               AND UPPER(TRIM(COALESCE(nombre_usuario, ''))) NOT IN ('SISTEMA', 'SYSTEM', 'MAXIKASH APP')
             ORDER BY adj_bitacora.fecha_alta DESC, adj_bitacora.id DESC
             LIMIT 1",
            ['id' => $idOperacion]
        );

        if (!$row) {
            return null;
        }

        $accion = $this->normalizarAdjBitacoraAccionDisplay((string) ($row['accion'] ?? ''));
        if (preg_match('/\s*\(id evidencia\s+(\d+)\)\s*/i', $accion, $m)) {
            $idEv = (int) ($m[1] ?? 0);
            $label = '';
            if ($idEv > 0) {
                $ev = $this->db->queryOne(
                    'SELECT slot FROM adj_evidencia WHERE id = :id_ev AND id_operacion = :id_op LIMIT 1',
                    ['id_ev' => $idEv, 'id_op' => $idOperacion]
                );
                $slot = (string) ($ev['slot'] ?? '');
                $label = trim((string) (self::SLOT_LABELS[$slot] ?? $slot));
            }
            $accion = preg_replace('/\s*\(id evidencia\s+\d+\)\s*/i', $label !== '' ? ' - ' . $label : '', $accion);
            if (preg_match('/^VALIDACI(?:Ó|O)N EVIDENCIA\s+(ACEPTADA|RECHAZADA)\s+-\s+(.+)$/iu', $accion, $mv)) {
                $accion = 'VALIDACION EVIDENCIA ' . trim((string) $mv[2]) . ': ' . strtoupper((string) $mv[1]);
            }
        }
        $row['accion'] = $accion;
        return $row;
    }

    private function obtenerUltimoGestorOperacion(int $idCredito): ?array
    {
        if ($idCredito <= 0) {
            return null;
        }

        return $this->db->queryOne(
            "SELECT TRIM(CONCAT_WS(' ',
                        per.nombres,
                        per.segundo_nombre,
                        per.apellidop,
                        per.apellidom
                    )) AS nombre,
                    DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_asignacion
             FROM asigna_creditos_adjudicacion aca
             INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
             INNER JOIN persona per ON per.id = pa.id_persona
             WHERE aca.id_credito = :id_credito
             ORDER BY
                CASE WHEN aca.estatus = '1' THEN 0 ELSE 1 END,
                aca.fecha_alta DESC,
                aca.id DESC
             LIMIT 1",
            ['id_credito' => $idCredito]
        ) ?: null;
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
            return ['success' => false, 'message' => 'La operaci?n no est? en etapa Recuperacion.'];
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
    public function obtenerPipeline(string $filtro = '', int $limit = 500): array
    {
        // El NIV se captura como VIN en moto_no_serie. serie se conserva como
        // respaldo para operaciones creadas antes del formulario actual.
        $predD2 = $this->sqlEsDictamenLlamadaRetenciones('d2');
        $limit = max(50, min(500, $limit));
        $filtro = trim($filtro);
        $where = [
            "o.estatus NOT IN ('Retenciones', 'cancelado')",
            "(o.estatus NOT IN ('Recibido', 'en_transito') OR {$this->sqlExisteEnvioEvidenciasAdjudicacion('o')})",
        ];
        $params = [];
        if ($filtro !== '') {
            $where[] = "(CAST(o.id_credito AS CHAR) LIKE :q OR o.nombre_cliente LIKE :q OR o.folio LIKE :q)";
            $params['q'] = '%' . $filtro . '%';
        }
        $whereSql = 'WHERE ' . implode(' AND ', $where);
        $totalSql = "SELECT COUNT(*) AS total FROM adj_operacion o {$whereSql}";
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
            COALESCE(NULLIF(TRIM(o.moto_no_serie), ''), NULLIF(TRIM(o.serie), '')) AS niv,
            o.num_motor,
            NULL AS placas,
            o.dias_mora,
            o.saldo_capital,
            o.adeudo_total,
            o.id_usuario_alta,
            DATE_FORMAT(o.fecha_alta,          '%d/%m/%Y %H:%i') AS fecha_alta,
            DATE_FORMAT(o.fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion,
            DATE_FORMAT(
                COALESCE(
                    (SELECT MAX(h.fecha)
                     FROM adj_historial_estatus h
                     WHERE h.id_operacion = o.id
                       AND h.estatus_nuevo = o.estatus),
                    o.fecha_actualizacion,
                    o.fecha_alta
                ),
                '%d/%m/%Y %H:%i'
            ) AS fecha_estatus_actual,
            DATEDIFF(
                NOW(),
                COALESCE(
                    (SELECT MAX(h.fecha)
                     FROM adj_historial_estatus h
                     WHERE h.id_operacion = o.id
                       AND h.estatus_nuevo = o.estatus),
                    o.fecha_actualizacion,
                    o.fecha_alta
                )
            ) AS dias_en_pipeline,
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
        {$whereSql}
        ORDER BY
            FIELD(o.estatus,
                'Recibido',
                'en_transito',
                'Validacion IA',
                'Bloqueado IA',
                'Procesando IA',
                'Revisión Recuperaciones',
                'Cierre Documentado',
                'Recepción',
                'Retenciones',
                'cancelado'
            ),
            o.fecha_alta ASC
        LIMIT {$limit}
        SQL;

        try {
            $totalRow = $this->db->queryOne($totalSql, $params) ?: [];
            return [
                'rows' => $this->db->queryAll($sql, $params) ?: [],
                'total' => (int) ($totalRow['total'] ?? 0),
                'limit' => $limit,
            ];
        } catch (\Throwable $e) {
            // Compatibilidad: en algunos entornos legacy adj_operacion aún no tiene columna `placas`.
            if (stripos((string) $e->getMessage(), "Unknown column 'o.placas'") !== false) {
                $sqlFallback = str_replace(
                    "            o.placas,\n",
                    "            NULL AS placas,\n",
                    $sql
                );

                $totalRow = $this->db->queryOne($totalSql, $params) ?: [];
                return [
                    'rows' => $this->db->queryAll($sqlFallback, $params) ?: [],
                    'total' => (int) ($totalRow['total'] ?? 0),
                    'limit' => $limit,
                ];
            }
            throw $e;
        }
    }

    public function obtenerMonitoreoAdjudicaciones(array $filtros = []): array
    {
        $where = [];
        $params = [];

        $etapa = trim((string) ($filtros['etapa'] ?? ''));
        $mapaEtapas = [
            'evidencias' => ['Recibido', 'en_transito'],
            'recuperacion' => ['Validacion IA', 'Bloqueado IA', 'Procesando IA', 'Revisión Recuperaciones'],
            'cartera' => ['Cierre Documentado'],
            'recepcion' => ['Recepción'],
        ];
        if ($etapa !== '' && isset($mapaEtapas[$etapa])) {
            $ph = [];
            foreach ($mapaEtapas[$etapa] as $idx => $estatusEtapa) {
                $key = 'estatusEtapa' . $idx;
                $ph[] = ':' . $key;
                $params[$key] = $estatusEtapa;
            }
            $where[] = 'o.estatus IN (' . implode(',', $ph) . ')';
        } else {
            $where[] = "o.estatus IN ('Recibido', 'en_transito', 'Validacion IA', 'Bloqueado IA', 'Procesando IA', 'Revisión Recuperaciones', 'Cierre Documentado', 'Recepción')";
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.area_actual,
            o.responsable_entrega,
            o.telefono_contacto,
            o.marca,
            o.modelo,
            o.serie,
            o.num_motor,
            o.dias_mora,
            o.saldo_capital,
            o.adeudo_total,
            o.id_usuario_alta,
            DATE_FORMAT(o.fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta,
            DATE_FORMAT(o.fecha_actualizacion, '%Y-%m-%d %H:%i') AS fecha_actualizacion,
            TRIM(CONCAT_WS(' ', per_alta.nombres, per_alta.segundo_nombre, per_alta.apellidop, per_alta.apellidom)) AS levantado_por,
            aca.id AS id_asignacion_actual,
            pa.id_persona AS id_responsable_actual,
            TRIM(CONCAT_WS(' ', per_resp.nombres, per_resp.segundo_nombre, per_resp.apellidop, per_resp.apellidom)) AS responsable_actual,
            GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ') AS responsable_puesto,
            DATE_FORMAT(aca.fecha_alta, '%Y-%m-%d %H:%i') AS fecha_asignacion_actual,
            TRIM(CONCAT_WS(' ', per_asig.nombres, per_asig.segundo_nombre, per_asig.apellidop, per_asig.apellidom)) AS asignado_por,
            COALESCE(ev.evidencias_count, 0) AS evidencias_count,
            COALESCE(obs.observaciones_count, 0) AS observaciones_count,
            0 AS bitacora_count
        FROM adj_operacion o
        LEFT JOIN persona per_alta ON per_alta.id = o.id_usuario_alta
        LEFT JOIN asigna_creditos_adjudicacion aca ON aca.id = (
            SELECT aca2.id
            FROM asigna_creditos_adjudicacion aca2
            WHERE aca2.id_credito = o.id_credito
              AND aca2.estatus = '1'
            ORDER BY aca2.fecha_alta DESC, aca2.id DESC
            LIMIT 1
        )
        LEFT JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
        LEFT JOIN persona per_resp ON per_resp.id = pa.id_persona
        LEFT JOIN persona per_asig ON per_asig.id = aca.alta
        LEFT JOIN asigna_puesto ap ON ap.id_persona = pa.id_persona AND COALESCE(ap.activo, 1) = 1
        LEFT JOIN puesto pu ON pu.id = ap.id_puesto
        LEFT JOIN (
            SELECT id_operacion, COUNT(*) AS evidencias_count
            FROM adj_evidencia
            GROUP BY id_operacion
        ) ev ON ev.id_operacion = o.id
        LEFT JOIN (
            SELECT id_operacion, COUNT(*) AS observaciones_count
            FROM adj_observacion
            GROUP BY id_operacion
        ) obs ON obs.id_operacion = o.id
        {$whereSql}
        GROUP BY
            o.id, o.folio, o.id_credito, o.nombre_cliente, o.estatus, o.area_actual,
            o.responsable_entrega, o.telefono_contacto, o.marca, o.modelo, o.serie, o.num_motor,
            o.dias_mora, o.saldo_capital, o.adeudo_total, o.id_usuario_alta,
            o.fecha_alta, o.fecha_actualizacion,
            per_alta.nombres, per_alta.segundo_nombre, per_alta.apellidop, per_alta.apellidom,
            aca.id, aca.fecha_alta, aca.alta, pa.id_persona,
            per_resp.nombres, per_resp.segundo_nombre, per_resp.apellidop, per_resp.apellidom,
            per_asig.nombres, per_asig.segundo_nombre, per_asig.apellidop, per_asig.apellidom
        ORDER BY o.fecha_actualizacion DESC, o.id DESC
        LIMIT 250
        SQL;

        return $this->db->queryAll($sql, $params) ?: [];
    }

    public function buscarPersonasParaMonitoreo(string $buscar = ''): array
    {
        $buscar = trim($buscar);
        $params = [];
        $where = "WHERE pa.estatus = 'Activo' AND LOWER(TRIM(COALESCE(p.estatus, 'Activo'))) NOT IN ('baja', 'transito de baja')";
        if ($buscar !== '') {
            $where .= " AND (
                pa.id = :idExacto
                OR p.id = :idExacto
                OR p.numero_empleado LIKE :q
                OR TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) LIKE :q
            )";
            $params['idExacto'] = ctype_digit($buscar) ? (int) $buscar : 0;
            $params['q'] = '%' . $buscar . '%';
        }

        $sql = <<<SQL
        SELECT
            MIN(pa.id) AS id_personal_adj,
            p.id,
            p.numero_empleado,
            TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
            GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ') AS puesto
        FROM personal_adjudicacion pa
        INNER JOIN persona p ON p.id = pa.id_persona
        LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
        LEFT JOIN puesto pu ON pu.id = ap.id_puesto
        {$where}
        GROUP BY p.id, p.numero_empleado, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom
        ORDER BY nombre_completo ASC
        LIMIT 300
        SQL;

        return $this->db->queryAll($sql, $params) ?: [];
    }

    public function buscarDestinatariosCampaniaLegacy(string $buscar = ''): array
    {
        $buscar = trim($buscar);
        if ($buscar === '' || strlen($buscar) < 2) {
            return [];
        }

        $params = [
            'idExacto' => ctype_digit($buscar) ? (int) $buscar : 0,
            'q' => '%' . $buscar . '%',
        ];
        $tokens = preg_split('/\s+/', $buscar) ?: [];
        $tokenConditions = [];
        foreach ($tokens as $idx => $token) {
            $token = trim((string) $token);
            if ($token === '' || mb_strlen($token) < 2) {
                continue;
            }
            $key = 'qtok' . $idx;
            $params[$key] = '%' . $token . '%';
            $tokenConditions[] = "(
                p.numero_empleado LIKE :{$key}
                OR p.user_name LIKE :{$key}
                OR p.nombres LIKE :{$key}
                OR p.segundo_nombre LIKE :{$key}
                OR p.apellidop LIKE :{$key}
                OR p.apellidom LIKE :{$key}
                OR TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) LIKE :{$key}
                OR TRIM(CONCAT_WS(' ', p.apellidop, p.apellidom, p.nombres, p.segundo_nombre)) LIKE :{$key}
            )";
        }
        $tokenSql = $tokenConditions !== [] ? ' OR (' . implode(' AND ', $tokenConditions) . ')' : '';

        $rows = $this->db->queryAll(
            "SELECT
                p.id,
                TRIM(COALESCE(p.numero_empleado, '')) AS numero_empleado,
                TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre_completo,
                GROUP_CONCAT(DISTINCT pu.nombre ORDER BY pu.nombre SEPARATOR ' - ') AS puesto
             FROM persona p
             LEFT JOIN asigna_puesto ap ON ap.id_persona = p.id AND COALESCE(ap.activo, 1) = 1
             LEFT JOIN puesto pu ON pu.id = ap.id_puesto
             WHERE LOWER(TRIM(COALESCE(p.estatus, 'Activo'))) NOT IN ('baja', 'transito de baja')
               AND TRIM(COALESCE(p.numero_empleado, '')) <> ''
               AND (
                    p.id = :idExacto
                    OR p.numero_empleado LIKE :q
                    OR p.user_name LIKE :q
                    OR TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) LIKE :q
                    OR TRIM(CONCAT_WS(' ', p.apellidop, p.apellidom, p.nombres, p.segundo_nombre)) LIKE :q
                    {$tokenSql}
               )
             GROUP BY p.id, p.numero_empleado, p.nombres, p.segundo_nombre, p.apellidop, p.apellidom
             ORDER BY nombre_completo ASC
             LIMIT 30",
            $params
        ) ?: [];

        if ($rows === []) {
            return [];
        }

        $externalIds = [];
        foreach ($rows as $row) {
            $external = trim((string) ($row['numero_empleado'] ?? ''));
            if ($external !== '') {
                $externalIds[$external] = true;
            }
        }

        $legacyPorExternal = [];
        if ($externalIds !== []) {
            try {
                $legacyDb = new DatabaseLegacy();
                $placeholders = [];
                $legacyParams = [];
                foreach (array_keys($externalIds) as $idx => $external) {
                    $key = 'ext' . $idx;
                    $placeholders[] = ':' . $key;
                    $legacyParams[$key] = $external;
                }
                $legacyRows = $legacyDb->queryAll(
                    "SELECT id, TRIM(COALESCE(external_id, '')) AS external_id
                     FROM users
                     WHERE deleted_at IS NULL
                       AND TRIM(COALESCE(external_id, '')) IN (" . implode(',', $placeholders) . ")",
                    $legacyParams
                ) ?: [];
                foreach ($legacyRows as $legacyRow) {
                    $external = trim((string) ($legacyRow['external_id'] ?? ''));
                    if ($external !== '') {
                        $legacyPorExternal[$external] = (int) ($legacyRow['id'] ?? 0);
                    }
                }
            } catch (\Throwable $e) {
                $legacyPorExternal = [];
            }
        }

        $salida = [];
        foreach ($rows as $row) {
            $external = trim((string) ($row['numero_empleado'] ?? ''));
            if ($external === '') {
                continue;
            }
            $salida[] = [
                'id_persona' => (int) ($row['id'] ?? 0),
                'nombre' => trim((string) ($row['nombre_completo'] ?? '')),
                'numero_empleado' => $external,
                'puesto' => trim((string) ($row['puesto'] ?? '')),
                'user_id_legacy' => $legacyPorExternal[$external] ?? null,
            ];
        }

        return $salida;
    }

    public function reasignarCreditoMonitoreo(int $idOperacion, int $idPersonaDestino, int $idUsuario, string $nombreUsuario = ''): array
    {
        if ($idOperacion <= 0 || $idPersonaDestino <= 0) {
            return ['success' => false, 'message' => 'Parámetros inválidos.'];
        }

        $op = $this->db->queryOne(
            'SELECT id, id_credito, nombre_cliente FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op || (int) ($op['id_credito'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'Operación no encontrada o sin crédito asociado.'];
        }

        $persona = $this->db->queryOne(
            "SELECT
                    pa.id AS id_personal_adj,
                    p.id,
                    TRIM(CONCAT_WS(' ', p.nombres, p.segundo_nombre, p.apellidop, p.apellidom)) AS nombre
             FROM personal_adjudicacion pa
             INNER JOIN persona p ON p.id = pa.id_persona
             WHERE p.id = :id
               AND pa.estatus = 'Activo'
               AND LOWER(TRIM(COALESCE(p.estatus, 'Activo'))) NOT IN ('baja', 'transito de baja')
             ORDER BY pa.id DESC
             LIMIT 1",
            ['id' => $idPersonaDestino]
        );
        if (!$persona) {
            return ['success' => false, 'message' => 'La persona seleccionada no está activa en personal de adjudicación.'];
        }

        $idCredito = (int) $op['id_credito'];
        $ahora = $this->fechaHoraCdmx();

        try {
            $this->db->beginTransaction();

            $asignacion = $this->db->queryOne(
                "SELECT id, id_personal_adj
                 FROM asigna_creditos_adjudicacion
                 WHERE id_credito = :idCredito AND estatus = '1'
                 ORDER BY id DESC
                 LIMIT 1",
                ['idCredito' => $idCredito]
            );
            $idPersonalAdj = (int) $persona['id_personal_adj'];
            if (!$asignacion) {
                $this->db->CRUD(
                    "INSERT INTO asigna_creditos_adjudicacion
                        (id_personal_adj, id_credito, fecha_alta, alta, estatus)
                     VALUES (:idPersonalAdj, :idCredito, :fechaAlta, :alta, '1')",
                    [
                        'idPersonalAdj' => $idPersonalAdj,
                        'idCredito' => $idCredito,
                        'fechaAlta' => $ahora,
                        'alta' => $idUsuario,
                    ]
                );
                $yaAsignado = false;
            } else {
                if ((int) $asignacion['id_personal_adj'] === $idPersonalAdj) {
                    $yaAsignado = true;
                } else {
                    $yaAsignado = false;
                }

                $this->db->CRUD(
                    "UPDATE asigna_creditos_adjudicacion
                     SET id_personal_adj = :idPersonalAdj
                     WHERE id_credito = :idCredito AND estatus = '1'",
                    [
                        'idPersonalAdj' => $idPersonalAdj,
                        'idCredito' => $idCredito,
                    ]
                );
            }

            if (!$yaAsignado) {
                $this->registrarBitacora(
                    $idOperacion,
                    'ACTUALIZO RESPONSABLE A: ' . mb_strtoupper((string) $persona['nombre'], 'UTF-8'),
                    $idUsuario,
                    $nombreUsuario,
                    $ahora
                );
            }

            $this->db->commit();

            $taskLegacy = null;
            $advertenciaLegacy = '';
            try {
                $taskLegacy = $this->crearTaskLegacyMotoAutorizada($idCredito, $idPersonaDestino);
                if (!empty($taskLegacy['success'])) {
                    $this->sincronizarDictumAppCreditoOperacion($idCredito, $idOperacion, $idPersonaDestino, 'APP MOVIL');
                } else {
                    $advertenciaLegacy = ' Asignacion local OK, pero no se pudo preparar la tarea Legacy: '
                        . (string) ($taskLegacy['message'] ?? 'sin detalle.');
                }
            } catch (\Throwable $e) {
                $advertenciaLegacy = ' Asignacion local OK, pero no se pudo preparar la tarea Legacy: '
                    . $e->getMessage();
            }
            return [
                'success' => true,
                'message' => ($yaAsignado
                    ? 'El responsable seleccionado ya está asignado a este crédito.'
                    : 'Responsable actualizado correctamente.') . $advertenciaLegacy,
                'task_legacy' => $taskLegacy,
            ];
        } catch (\Throwable $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Detalle completo de una operaci?n incluyendo evidencias y observaciones.
     */
    public function obtenerDetalle(int $id): ?array
    {
        $this->asegurarColumnasFormularioMoto();

        $op = $this->db->queryOne(
            "SELECT o.*,
                    DATE_FORMAT(o.fecha_alta,          '%Y-%m-%d %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(o.fecha_alta,          '%d/%m/%Y %H:%i') AS fecha_gestion_legacy,
                    DATE_FORMAT(o.fecha_actualizacion, '%Y-%m-%d %H:%i') AS fecha_actualizacion_fmt,
                    DATE_FORMAT(o.datos_moto_at,       '%d/%m/%Y %H:%i') AS datos_moto_fecha,
                    DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline
             FROM adj_operacion o
             WHERE o.id = :id",
            ['id' => $id]
        );

        if (!$op) {
            return null;
        }

        $this->sincronizarUltimosReemplazosEvidenciaOperacion($id);

        $opSincronizada = $this->db->queryOne(
            "SELECT o.*,
                    DATE_FORMAT(o.fecha_alta,          '%Y-%m-%d %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(o.fecha_alta,          '%d/%m/%Y %H:%i') AS fecha_gestion_legacy,
                    DATE_FORMAT(o.fecha_actualizacion, '%Y-%m-%d %H:%i') AS fecha_actualizacion_fmt,
                    DATE_FORMAT(o.datos_moto_at,       '%d/%m/%Y %H:%i') AS datos_moto_fecha,
                    DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline
             FROM adj_operacion o
             WHERE o.id = :id",
            ['id' => $id]
        );
        if ($opSincronizada) {
            $op = $opSincronizada;
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

                // Un registro aceptado no garantiza que su archivo siga presente.
                // Las vistas usan este estado para no mostrar evidencia fantasma como completa.
                $r['archivo_estado'] = $this->obtenerEstadoArchivoEvidencia($urlLimpia);
                $r['url'] = function_exists('sparta_url_publica_desde_repositorio')
                    ? sparta_url_publica_desde_repositorio($urlLimpia)
                    : $urlLimpia;
            } else {
                $r['archivo_estado'] = 'sin_archivo';
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
        $op['validacion_knockout'] = null;
        if ($this->tablaKnockoutDisponible()) {
            try {
                $op['validacion_knockout'] = $this->db->queryOne(
                    "SELECT tipo, estado, etiqueta, proveedor, modelo, confianza, motivo,
                            DATE_FORMAT(fecha_actualizacion, '%Y-%m-%d %H:%i') AS fecha_actualizacion
                     FROM adj_validacion_knockout
                     WHERE id_operacion = :id AND tipo = 'ESTADO_MOTO_CLAUDE'
                     ORDER BY id DESC LIMIT 1",
                    ['id' => $id]
                ) ?: null;
            } catch (\Throwable $ignored) {
                // The detail remains available while the additive migration is pending.
            }
        }
        $ultimoAnalista = $this->obtenerUltimoAnalistaEvidencias($id);
        $op['ultimo_analista_evidencias'] = $ultimoAnalista;
        $op['ultimo_analista_nombre'] = $ultimoAnalista['nombre_usuario'] ?? null;
        $op['ultimo_analista_fecha'] = $ultimoAnalista['fecha_alta'] ?? null;
        $op['ultimo_analista_accion'] = $ultimoAnalista['accion'] ?? null;

        $ultimoGestor = $this->obtenerUltimoGestorOperacion((int) ($op['id_credito'] ?? 0));
        $op['ultimo_gestor_operacion'] = $ultimoGestor;
        $op['ultimo_gestor_nombre'] = $ultimoGestor['nombre'] ?? null;
        $op['ultimo_gestor_fecha'] = $ultimoGestor['fecha_asignacion'] ?? null;
        $op['niv'] = trim((string) ($op['moto_no_serie'] ?? ''));
        if ($op['niv'] === '') {
            $op['niv'] = trim((string) ($op['serie'] ?? ''));
        }

        return $op;
    }

    /**
     * Verifica archivos locales sin llamar a proveedores remotos al abrir un expediente.
     * Los enlaces externos se mantienen como pendientes de verificar para no ralentizar la vista.
     */
    private function obtenerEstadoArchivoEvidencia(string $url): string
    {
        $raw = trim(str_replace('\\', '/', $url));
        if ($raw === '') {
            return 'sin_archivo';
        }

        $path = (string) (parse_url($raw, PHP_URL_PATH) ?: $raw);
        $uploadsPos = stripos($path, '/uploads/');
        if ($uploadsPos !== false) {
            if (!function_exists('sparta_uploads_resolve_relative')) {
                require_once dirname(__DIR__) . '/core/UploadsPaths.php';
            }

            $relative = substr($path, $uploadsPos + strlen('/uploads/'));
            $local = sparta_uploads_resolve_relative($relative);
            return ($local && is_file($local)) ? 'disponible' : 'no_disponible';
        }

        if (!preg_match('#^https?://#i', $raw)) {
            return is_file($raw) ? 'disponible' : 'no_disponible';
        }

        return 'externo_sin_verificar';
    }

    private function sincronizarUltimosReemplazosEvidenciaOperacion(int $idOperacion): void
    {
        if ($idOperacion <= 0 || !$this->adjEvidenciaTieneColumnasAtn()) {
            return;
        }

        try {
            $rows = $this->db->queryAll(
                "SELECT h.id_evidencia,
                        h.url_vieja_rechazada,
                        h.url_nueva,
                        e.url AS url_actual,
                        e.slot
                 FROM adj_evidencia_rechazo_historial h
                 INNER JOIN (
                     SELECT id_evidencia, MAX(id) AS id_historial
                     FROM adj_evidencia_rechazo_historial
                     WHERE id_operacion = :id
                     GROUP BY id_evidencia
                 ) ult ON ult.id_historial = h.id
                 INNER JOIN adj_evidencia e ON e.id = h.id_evidencia
                 WHERE h.id_operacion = :id2",
                ['id' => $idOperacion, 'id2' => $idOperacion]
            ) ?: [];

            if ($rows === []) {
                return;
            }

            $tocadas = false;
            foreach ($rows as $row) {
                $idEvidencia = (int) ($row['id_evidencia'] ?? 0);
                if ($idEvidencia <= 0) {
                    continue;
                }

                $urlNueva = trim((string) ($row['url_nueva'] ?? ''));
                if ($urlNueva !== '') {
                    $urlAplicable = $urlNueva;
                    if (preg_match('#^https?://#i', $urlNueva)) {
                        $urlAplicable = $this->respaldarEvidenciaDictumApp(
                            $idOperacion,
                            trim((string) ($row['slot'] ?? '')),
                            $urlNueva
                        );
                        if ($urlAplicable === null) {
                            continue;
                        }
                    }
                    $urlActual = trim((string) ($row['url_actual'] ?? ''));
                    if ($urlActual === $urlAplicable) {
                        continue;
                    }
                    if ($this->operacionTieneEnvioAtencionMarcado($idOperacion)) {
                        $this->db->CRUD(
                            "UPDATE adj_evidencia
                             SET tipo = :tipo,
                                 url = :url,
                                 estatus = 'recibido'
                             WHERE id = :id_evidencia
                               AND id_operacion = :id_operacion",
                            [
                                'tipo' => $this->tipoEvidenciaPorUrl($urlAplicable),
                                'url' => $urlAplicable,
                                'id_evidencia' => $idEvidencia,
                                'id_operacion' => $idOperacion,
                            ]
                        );
                    } else {
                        $this->db->CRUD(
                            "UPDATE adj_evidencia
                             SET tipo = :tipo,
                                 url = :url,
                                 val_atn = NULL,
                                 comentario_atn = NULL,
                                 estatus = 'recibido'
                             WHERE id = :id_evidencia
                               AND id_operacion = :id_operacion",
                            [
                                'tipo' => $this->tipoEvidenciaPorUrl($urlAplicable),
                                'url' => $urlAplicable,
                                'id_evidencia' => $idEvidencia,
                                'id_operacion' => $idOperacion,
                            ]
                        );
                    }
                    $tocadas = true;
                    continue;
                }

                $urlRechazada = trim((string) ($row['url_vieja_rechazada'] ?? ''));
                $params = [
                    'comentario' => 'Evidencia rechazada; pendiente de reemplazo.',
                    'id_evidencia' => $idEvidencia,
                    'id_operacion' => $idOperacion,
                ];
                $setUrl = '';
                if ($urlRechazada !== '') {
                    $setUrl = ', url = :url, tipo = :tipo';
                    $params['url'] = $urlRechazada;
                    $params['tipo'] = $this->tipoEvidenciaPorUrl($urlRechazada);
                }

                $this->db->CRUD(
                    "UPDATE adj_evidencia
                     SET val_atn = 2,
                         comentario_atn = :comentario,
                         estatus = 'recibido'
                         {$setUrl}
                     WHERE id = :id_evidencia
                       AND id_operacion = :id_operacion",
                    $params
                );
                $tocadas = true;
            }

            if ($tocadas) {
                $this->finalizarCierreValidacionEvidenciaAtn($idOperacion, 1, 'SISTEMA');
            }
        } catch (\Throwable $e) {
            error_log('[MotosAdjudicadas] sincronizarUltimosReemplazosEvidenciaOperacion: ' . $e->getMessage());
        }
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

        $this->registrarBitacora($idOperacion, self::ACCION_GESTOR_ENVIO_EVIDENCIAS_ADJUDICACION, $idUsuario, $nombreUsuario);

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
        'fis_ine_frente' => 'INE FRENTE (FÍSICA)',
        'fis_ine_reverso' => 'INE REVERSO (FÍSICA)',
        'fis_lateral_der' => 'LATERAL DERECHA (FÍSICA)',
        'fis_trasera' => 'TRASERA (FÍSICA)',
        'fis_lateral_izq' => 'LATERAL IZQUIERDA (FÍSICA)',
        'fis_video_cliente_acuerdo' => 'VIDEO CLIENTE DE ACUERDO (FÍSICA)',
        'fis_360_encendida' => 'VIDEO MOTO 360 ENCENDIDA (FÍSICA)',
        'fis_video_vuelta_prueba' => 'VIDEO VUELTA DE PRUEBA (FÍSICA)',
        'fis_checklist' => 'CHECKLIST (FÍSICA)',
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
        'fis_checklist',
    ];

    private const SLOTS_VALIDACION_ATENCION_MEDIA_ETAPA2 = [
        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
        'fis_ine_frente', 'fis_ine_reverso',
        'fis_vin', 'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
        'fis_tacometro', 'fis_360_encendida', 'fis_video_vuelta_prueba',
        'fis_checklist',
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
        'fis_ine_frente', 'fis_ine_reverso',
        'fis_vin',
        'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
        'fis_tacometro',
        'fis_video_cliente_acuerdo', 'fis_360_encendida', 'fis_video_vuelta_prueba',
        'fis_checklist',
        'doc_repuve', 'doc_factura',
    ];

    private const ESTATUS_VALIDOS = [
        'Recibido',
        'en_transito',
        'Validacion IA',
        'Bloqueado IA',
        'Procesando IA',
        'Revisión Recuperaciones',
        'Retenciones',
        'Cierre Documentado',
        'Recepción',
    ];

    /** Evidencia minima del segundo knockout; configurable antes de habilitarlo. */
    private const KNOCKOUT_IA_FOTOS = ['fis_frontal', 'fis_trasera', 'fis_lateral_izq', 'fis_lateral_der'];
    private const KNOCKOUT_IA_VIDEOS = ['fis_360_encendida', 'fis_video_vuelta_prueba'];

    /**
     * Cambia el estatus de una operaci?n y registra historial.
     */
    public function cambiarEstatus(int $id, string $estatusNuevo, int $idUsuario, string $nombreUsuario = '', string $origen = ''): array
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

        $estatusActual = (string) ($actual['estatus'] ?? '');
        if ($estatusActual === 'Validacion IA'
            && !in_array($estatusNuevo, ['Validacion IA', 'Procesando IA', 'Bloqueado IA', 'Revisión Recuperaciones'], true)) {
            return ['success' => false, 'message' => 'La operacion esta en validacion IA y no puede avanzar hasta recibir el resultado del knockout.'];
        }
        if ($estatusActual === 'Bloqueado IA' && $estatusNuevo !== 'Bloqueado IA') {
            return ['success' => false, 'message' => 'La adjudicacion esta bloqueada por la validacion del estado de la moto.'];
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
        if ($estatusNuevo === 'Recibido' && strtolower(trim($origen)) === 'monitoreo') {
            $this->registrarBitacora($id, self::ACCION_MONITOREO_FORZO_EVIDENCIAS, $idUsuario, $nombreUsuario, $ahora);
        }

        return ['success' => true];
    }

    public function sincronizarDictumsAppPendientes(bool $forzar = false, bool $soloAsignacionVigente = false): void
    {
        $lockHandle = null;
        $lockTomado = false;
        try {
            $lockPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta_madj_dictums_sync.lock';
            $stampPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sparta_madj_dictums_sync.stamp';
            $intervaloMinimo = 300;

            if (!$forzar && is_file($stampPath) && (time() - (int) @filemtime($stampPath)) < $intervaloMinimo) {
                return;
            }

            $lockHandle = @fopen($lockPath, 'c');
            if (!$lockHandle) {
                return;
            }
            if (!@flock($lockHandle, LOCK_EX | LOCK_NB)) {
                @fclose($lockHandle);
                $lockHandle = null;
                return;
            }
            $lockTomado = true;

            if (!$forzar && is_file($stampPath) && (time() - (int) @filemtime($stampPath)) < $intervaloMinimo) {
                return;
            }
            @touch($stampPath);

            $this->sincronizarDictumsAsignacionVigentePendientes();

            if ($soloAsignacionVigente) {
                $this->sincronizarDictumsOperacionesLocalesPendientes();
                return;
            }

            $rows = $this->db->queryAll(
                "SELECT ao.id AS id_operacion, ao.id_credito
                 FROM adj_operacion ao
                 INNER JOIN (
                     SELECT aop.id_credito, MAX(aop.id) AS id_max
                     FROM adj_operacion aop
                     INNER JOIN asigna_creditos_adjudicacion aca
                         ON aca.id_credito = aop.id_credito AND aca.estatus = '1'
                     WHERE aop.estatus IN ('en_transito', 'Recibido')
                     GROUP BY aop.id_credito
                 ) ult ON ult.id_max = ao.id AND ult.id_credito = ao.id_credito
                 ORDER BY ao.fecha_actualizacion DESC
                 LIMIT 300"
            ) ?: [];

            foreach ($rows as $row) {
                $this->sincronizarDictumAppCreditoOperacion(
                    (int) ($row['id_credito'] ?? 0),
                    (int) ($row['id_operacion'] ?? 0),
                    0,
                    'APP MOVIL'
                );
            }
        } catch (\Throwable $e) {
            error_log('[MotosAdjudicadas] sincronizarDictumsAppPendientes: ' . $e->getMessage());
        } finally {
            if (is_resource($lockHandle)) {
                if ($lockTomado) {
                    @flock($lockHandle, LOCK_UN);
                }
                @fclose($lockHandle);
            }
        }
    }

    private function sincronizarDictumsOperacionesLocalesPendientes(): void
    {
        try {
            $rows = $this->db->queryAll(
                "SELECT ao.id AS id_operacion, ao.id_credito
                 FROM adj_operacion ao
                 WHERE ao.estatus IN ('en_transito', 'Recibido')
                   AND NOT EXISTS (
                       SELECT 1
                       FROM adj_bitacora b
                       WHERE b.id_operacion = ao.id
                         AND (
                              b.accion LIKE '%AL PIPELINE%'
                              OR b.accion LIKE '%EVIDENCIAS DE LA ADJUDICACION%'
                         )
                   )
                 ORDER BY ao.fecha_actualizacion DESC
                 LIMIT 300"
            ) ?: [];

            foreach ($rows as $row) {
                $this->sincronizarDictumAppCreditoOperacion(
                    (int) ($row['id_credito'] ?? 0),
                    (int) ($row['id_operacion'] ?? 0),
                    0,
                    'APP MOVIL'
                );
            }
        } catch (\Throwable $e) {
            // La sincronizacion es auxiliar; la bandeja no debe romper si Legacy no responde.
        }
    }

    private function sincronizarDictumsAsignacionVigentePendientes(): void
    {
        try {
            $legacyDb = new DatabaseLegacy();
            $rows = $legacyDb->queryAll(
                "SELECT
                    d.id AS dictum_id,
                    d.task_id,
                    d.user_id AS legacy_user_id,
                    d.opciondictamen_id,
                    d.form_response,
                    d.created_at,
                    d.lat,
                    d.lng,
                    t.campaign_id,
                    t.credit_number,
                    t.client_name,
                    c.name AS campaign_name
                 FROM tasks t
                 INNER JOIN dictums d ON d.task_id = t.id
                 INNER JOIN campaigns c ON c.id = t.campaign_id
                 WHERE (
                    (c.name LIKE :campaign_prefix AND d.opciondictamen_id = 13)
                    OR t.campaign_id = :campaign_motos
                 )
                   AND c.deleted_at IS NULL
                   AND t.deleted_at IS NULL
                   AND c.start_date <= CURDATE()
                   AND d.created_at >= c.start_date
                   AND d.updated_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
                   AND d.form_response IS NOT NULL
                   AND TRIM(CAST(d.form_response AS CHAR)) <> ''
                 ORDER BY d.id DESC
                 LIMIT 200",
                [
                    'campaign_prefix' => self::LEGACY_CAMPAIGN_ASIGNACION_PREFIX . '%',
                    'campaign_motos' => self::LEGACY_CAMPAIGN_MOTOS_ADJ_AUTORIZADAS,
                ]
            ) ?: [];
        } catch (\Throwable $e) {
            return;
        }

        if ($rows === []) {
            return;
        }

        $creditosRows = [];
        foreach ($rows as $row) {
            $idCredito = (int) ($row['credit_number'] ?? 0);
            if ($idCredito > 0) {
                $creditosRows[$idCredito] = true;
            }
        }

        if ($creditosRows !== []) {
            $ph = [];
            $params = [];
            foreach (array_keys($creditosRows) as $i => $idCredito) {
                $k = 'sync_credito_' . $i;
                $ph[] = ':' . $k;
                $params[$k] = $idCredito;
            }

            $yaEnPipeline = [];
            try {
                $localRows = $this->db->queryAll(
                    "SELECT DISTINCT ao.id_credito
                     FROM adj_operacion ao
                     INNER JOIN adj_bitacora b
                        ON b.id_operacion = ao.id
                       AND (
                            b.accion LIKE '%AL PIPELINE%'
                            OR b.accion LIKE '%EVIDENCIAS DE LA ADJUDICACION%'
                       )
                     WHERE ao.id_credito IN (" . implode(',', $ph) . ")",
                    $params
                ) ?: [];
                foreach ($localRows as $localRow) {
                    $cid = (int) ($localRow['id_credito'] ?? 0);
                    if ($cid > 0) {
                        $yaEnPipeline[$cid] = true;
                    }
                }
            } catch (\Throwable $e) {
                $yaEnPipeline = [];
            }

            if ($yaEnPipeline !== []) {
                $rows = array_values(array_filter($rows, static function ($row) use ($yaEnPipeline): bool {
                    $cid = (int) ($row['credit_number'] ?? 0);
                    return $cid > 0 && !isset($yaEnPipeline[$cid]);
                }));
                if ($rows === []) {
                    return;
                }
            }
        }

        $legacyUserIds = [];
        foreach ($rows as $row) {
            $uid = (int) ($row['legacy_user_id'] ?? 0);
            if ($uid > 0) {
                $legacyUserIds[$uid] = true;
            }
        }
        $gestoresPorLegacyUser = $this->obtenerDatosGestorLegacyPorDictumUserIds(array_keys($legacyUserIds));

        foreach ($rows as $row) {
            $idCredito = (int) ($row['credit_number'] ?? 0);
            if ($idCredito <= 0) {
                continue;
            }

            $campos = $this->normalizarFormResponseDictumApp((string) ($row['form_response'] ?? ''));
            if ($campos === [] || !$this->dictumAsignacionEsMotoAdjudicada($row, $campos)) {
                continue;
            }

            $legacyUserId = (int) ($row['legacy_user_id'] ?? 0);
            $idPersona = (int) ($gestoresPorLegacyUser[$legacyUserId]['id_persona'] ?? 0);
            if ($idPersona <= 0) {
                continue;
            }

            $this->asegurarAsignacionAdjudicacionDesdeDictum($idCredito, $idPersona);

            $nombreCliente = trim((string) ($row['client_name'] ?? ''));
            if ($nombreCliente === '') {
                $nombreCliente = 'Credito #' . $idCredito;
            }

            $op = $this->obtenerOCrearOperacion($idCredito, $nombreCliente, $idPersona);
            $idOperacion = (int) ($op['detalle']['id'] ?? 0);
            if (empty($op['success']) || $idOperacion <= 0) {
                continue;
            }

            $this->sincronizarCamposDictumAppOperacion(
                $idCredito,
                $idOperacion,
                $campos,
                $idPersona,
                'APP MOVIL'
            );
        }
    }

    private function dictumAsignacionEsMotoAdjudicada(array $row, array $campos): bool
    {
        if ((int) ($row['campaign_id'] ?? 0) === self::LEGACY_CAMPAIGN_MOTOS_ADJ_AUTORIZADAS) {
            return true;
        }

        if ((int) ($row['opciondictamen_id'] ?? 0) === 13) {
            return true;
        }

        $dictamen = strtolower(trim(
            $this->valorCampoDictumApp($campos, 'dictamen', true)
            . ' '
            . $this->valorCampoDictumApp($campos, 'dictamen')
        ));

        return strpos($dictamen, 'moto adjudicada') !== false;
    }

    private function asegurarAsignacionAdjudicacionDesdeDictum(int $idCredito, int $idPersona): void
    {
        if ($idCredito <= 0 || $idPersona <= 0) {
            return;
        }

        try {
            $activa = $this->db->queryOne(
                "SELECT aca.id, aca.id_personal_adj, pa.id_persona
                 FROM asigna_creditos_adjudicacion aca
                 INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
                 WHERE aca.id_credito = :idCredito
                   AND aca.estatus = '1'
                 ORDER BY aca.id DESC
                 LIMIT 1",
                ['idCredito' => $idCredito]
            );
            if ($activa) {
                return;
            }

            $adj = new AdjudicacionModel();
            $adj->asignarCredito($idPersona, $idCredito, $idPersona);
        } catch (\Throwable $e) {
        }
    }

    private function sincronizarDictumsAppParaPersona(int $idPersona): void
    {
        if ($idPersona <= 0) {
            return;
        }

        try {
            $rows = $this->db->queryAll(
                "SELECT ao.id AS id_operacion, ao.id_credito
                 FROM adj_operacion ao
                 INNER JOIN (
                     SELECT aop.id_credito, MAX(aop.id) AS id_max
                     FROM adj_operacion aop
                     INNER JOIN asigna_creditos_adjudicacion aca
                         ON aca.id_credito = aop.id_credito AND aca.estatus = '1'
                     INNER JOIN personal_adjudicacion pa
                         ON pa.id = aca.id_personal_adj AND pa.id_persona = :idPersona
                     GROUP BY aop.id_credito
                 ) ult ON ult.id_max = ao.id AND ult.id_credito = ao.id_credito",
                ['idPersona' => $idPersona]
            ) ?: [];

            foreach ($rows as $row) {
                $this->sincronizarDictumAppCreditoOperacion(
                    (int) ($row['id_credito'] ?? 0),
                    (int) ($row['id_operacion'] ?? 0),
                    $idPersona,
                    'APP MOVIL'
                );
            }
        } catch (\Throwable $e) {
            // La sincronizacion es auxiliar; la bandeja no debe romper si Legacy no responde.
        }
    }

    private function sincronizarDictumsAppParaCreditos(array $idsCreditos): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsCreditos), fn($v) => $v > 0)));
        if ($ids === []) {
            return;
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
                "SELECT ao.id AS id_operacion, ao.id_credito
                 FROM adj_operacion ao
                 INNER JOIN (
                     SELECT id_credito, MAX(id) AS id_max
                     FROM adj_operacion
                     WHERE id_credito IN (" . implode(',', $ph) . ")
                     GROUP BY id_credito
                 ) ult ON ult.id_max = ao.id AND ult.id_credito = ao.id_credito",
                $params
            ) ?: [];

            foreach ($rows as $row) {
                $this->sincronizarDictumAppCreditoOperacion(
                    (int) ($row['id_credito'] ?? 0),
                    (int) ($row['id_operacion'] ?? 0),
                    0,
                    'APP MOVIL'
                );
            }
        } catch (\Throwable $e) {
        }
    }

    private function sincronizarDictumAppCreditoOperacion(int $idCredito, int $idOperacion, int $idUsuario = 0, string $nombreUsuario = 'APP MOVIL', ?string $fechaOperacion = null): void
    {
        if ($idCredito <= 0 || $idOperacion <= 0) {
            return;
        }

        try {
            $legacyDb = new DatabaseLegacy();
            $dictum = $legacyDb->queryOne(
                "SELECT d.id, d.form_response, d.created_at
                 FROM dictums d
                 INNER JOIN tasks t ON t.id = d.task_id
                 WHERE t.campaign_id = :campaign_id
                   AND TRIM(CAST(t.credit_number AS CHAR)) = :credit_number
                   AND d.form_response IS NOT NULL
                   AND TRIM(CAST(d.form_response AS CHAR)) <> ''
                 ORDER BY d.id DESC
                 LIMIT 1",
                [
                    'campaign_id' => self::LEGACY_CAMPAIGN_MOTOS_ADJ_AUTORIZADAS,
                    'credit_number' => (string) $idCredito,
                ]
            );
        } catch (\Throwable $e) {
            return;
        }

        if (!$dictum || empty($dictum['form_response'])) {
            try {
                $legacyDb = $legacyDb ?? new DatabaseLegacy();
                $dictum = $legacyDb->queryOne(
                    "SELECT d.id, d.form_response, d.created_at
                     FROM dictums d
                     INNER JOIN tasks t ON t.id = d.task_id
                     WHERE TRIM(CAST(t.credit_number AS CHAR)) = :credit_number
                       AND d.opciondictamen_id = 13
                       AND d.form_response IS NOT NULL
                       AND TRIM(CAST(d.form_response AS CHAR)) <> ''
                     ORDER BY d.id DESC
                     LIMIT 1",
                    [
                        'credit_number' => (string) $idCredito,
                    ]
                );
            } catch (\Throwable $e) {
                return;
            }
            if (!$dictum || empty($dictum['form_response'])) {
                return;
            }
        }

        $campos = $this->normalizarFormResponseDictumApp((string) $dictum['form_response']);
        if ($campos === []) {
            return;
        }

        $ahora = $this->normalizarFechaHoraOperacion($fechaOperacion ?: (string) ($dictum['created_at'] ?? ''));
        $procesado = false;
        $datosMoto = $this->extraerDatosMotoDesdeDictumApp($campos);
        if ($datosMoto !== []) {
            $resDatos = $this->guardarDatosMoto($idOperacion, $datosMoto, $idUsuario, 'REPUVE', false, $ahora);
            if (!empty($resDatos['success'])) {
                $procesado = true;
            }
        }

        foreach (self::DICTUM_APP_EVIDENCIA_SLOTS as $nombreCampo => $slot) {
            $url = $this->valorCampoDictumApp($campos, $nombreCampo);
            if ($url === '' || !preg_match('#^https?://#i', $url)) {
                continue;
            }
            if ($this->upsertEvidenciaDictumApp($idOperacion, $slot, $url, $idUsuario, $ahora)) {
                $procesado = true;
            }
        }

        if (!$procesado) {
            return;
        }

        $this->db->CRUD(
            "UPDATE adj_operacion
             SET estatus = CASE
                    WHEN estatus IN ('en_transito', 'Recibido') THEN 'Recibido'
                    ELSE estatus
                 END,
                 fecha_actualizacion = :fecha
             WHERE id = :id",
            ['fecha' => $ahora, 'id' => $idOperacion]
        );

        if (
            $this->faltantesEvidenciaFisicaOperacion($idOperacion) === []
            && !$this->existeBitacoraEnvioEvidenciasAdjudicacion($idOperacion)
        ) {
            $this->registrarBitacora($idOperacion, self::ACCION_GESTOR_ENVIO_EVIDENCIAS_ADJUDICACION, $idUsuario, $nombreUsuario, $ahora);
        }
    }

    private function normalizarFormResponseDictumApp(string $raw): array
    {
        $valor = trim($raw);
        for ($i = 0; $i < 3; $i++) {
            $decoded = json_decode($valor, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [];
            }
            if (is_string($decoded)) {
                $valor = $decoded;
                continue;
            }
            if (is_array($decoded) && count($decoded) === 1 && isset($decoded[0]) && is_string($decoded[0])) {
                $valor = $decoded[0];
                continue;
            }
            if (!is_array($decoded)) {
                return [];
            }

            $campos = [];
            foreach ($decoded as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $name = trim((string) ($item['name'] ?? ''));
                if ($name !== '') {
                    $campos[$name] = $item;
                }
                $labelKey = $this->normalizarClaveDictumApp((string) ($item['label'] ?? ''));
                if ($labelKey !== '') {
                    $campos['__label__' . $labelKey] = $item;
                }
            }
            return $campos;
        }

        return [];
    }

    private function normalizarClaveDictumApp(string $valor): string
    {
        $valor = trim($valor);
        if ($valor === '') {
            return '';
        }
        $valor = html_entity_decode($valor, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $valor = str_replace(["\xc2\xa0", '&nbsp;'], ' ', $valor);
        if (class_exists('\Normalizer', false)) {
            $valor = \Normalizer::normalize($valor, \Normalizer::FORM_D) ?: $valor;
        } else {
            $valor = strtr($valor, [
                'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
                'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            ]);
        }
        $valor = preg_replace('/[\x{0300}-\x{036f}]/u', '', $valor);
        $valor = strtolower((string) $valor);
        $valor = preg_replace('/[^a-z0-9]+/u', '_', $valor);
        return trim((string) $valor, '_');
    }

    private function aliasesCampoDictumApp(string $name): array
    {
        static $aliases = [
            'no_de_serie_vin' => ['numero_de_serie', 'numero_de_serie_vin', 'no_de_serie', 'serie', 'vin', 'serie_vin'],
            'no_de_motor' => ['numero_de_motor', 'num_motor', 'motor'],
            'tiene_tarjeta_de_circulacion_en_fisico' => ['tiene_tarjeta_de_circulacion', 'tarjeta_de_circulacion', 'tarjeta_circulacion'],
            'responsable_de_resguardo' => ['nombre_de_responsable_de_resguardo', 'responsable_resguardo'],
            'telefono_de_contacto' => ['contacto_de_resguardo', 'telefono_de_resguardo', 'celular'],
            'estado_de_lugar_de_resguardo_ejemplo_ciudad_de_mex' => ['estado_de_lugar_de_resguardo', 'estado_resguardo'],
            'ciudad_municipio_de_lugar_de_resguardo' => ['ciudad_de_lugar_de_resguardo', 'municipio_de_lugar_de_resguardo', 'ciudad_municipio_resguardo'],
            'calle_y_numero_de_lugar_de_resguardo' => ['calle_numero_de_lugar_de_resguardo', 'direccion_de_resguardo', 'domicilio_de_resguardo'],
        ];

        $keys = [$name, $this->normalizarClaveDictumApp($name)];
        foreach ($aliases[$name] ?? [] as $alias) {
            $keys[] = $alias;
            $keys[] = $this->normalizarClaveDictumApp($alias);
        }

        return array_values(array_unique(array_filter($keys, static fn ($v) => is_string($v) && $v !== '')));
    }

    private function valorCampoDictumApp(array $campos, string $name, bool $usarLabelSeleccionado = false): string
    {
        $campo = null;
        foreach ($this->aliasesCampoDictumApp($name) as $key) {
            if (isset($campos[$key]) && is_array($campos[$key])) {
                $campo = $campos[$key];
                break;
            }
            $labelKey = '__label__' . $this->normalizarClaveDictumApp($key);
            if (isset($campos[$labelKey]) && is_array($campos[$labelKey])) {
                $campo = $campos[$labelKey];
                break;
            }
        }
        if ($campo === null) {
            return '';
        }

        $valor = $campo['value'] ?? null;
        if (is_scalar($valor) && trim((string) $valor) !== '') {
            return trim((string) $valor);
        }

        $values = $campo['values'] ?? [];
        if (is_array($values)) {
            foreach ($values as $opt) {
                if (!is_array($opt) || empty($opt['selected'])) {
                    continue;
                }
                $key = $usarLabelSeleccionado ? 'label' : 'value';
                $sel = $opt[$key] ?? ($opt['value'] ?? $opt['label'] ?? '');
                return is_scalar($sel) ? trim((string) $sel) : '';
            }
        }

        return '';
    }

    private function sincronizarCamposDictumAppOperacion(int $idCredito, int $idOperacion, array $campos, int $idUsuario = 0, string $nombreUsuario = 'APP MOVIL'): void
    {
        if ($idCredito <= 0 || $idOperacion <= 0 || $campos === []) {
            return;
        }

        $procesado = false;
        $datosMoto = $this->extraerDatosMotoDesdeDictumApp($campos);
        if ($datosMoto !== []) {
            $res = $this->guardarDatosMoto($idOperacion, $datosMoto, $idUsuario, 'REPUVE', false);
            if (!empty($res['success'])) {
                $procesado = true;
            }
        }

        $ahora = $this->fechaHoraCdmx();
        foreach (self::DICTUM_APP_EVIDENCIA_SLOTS as $nombreCampo => $slot) {
            $url = $this->valorCampoDictumApp($campos, $nombreCampo);
            if ($url === '' || !preg_match('#^https?://#i', $url)) {
                continue;
            }
            if ($this->upsertEvidenciaDictumApp($idOperacion, $slot, $url, $idUsuario, $ahora)) {
                $procesado = true;
            }
        }

        if (!$procesado) {
            return;
        }

        $this->db->CRUD(
            "UPDATE adj_operacion
             SET estatus = CASE
                    WHEN estatus IN ('en_transito', 'Recibido') THEN 'Recibido'
                    ELSE estatus
                 END,
                 fecha_actualizacion = :fecha
             WHERE id = :id",
            ['fecha' => $ahora, 'id' => $idOperacion]
        );

        if (
            $this->faltantesEvidenciaFisicaOperacion($idOperacion) === []
            && !$this->existeBitacoraEnvioEvidenciasAdjudicacion($idOperacion)
        ) {
            $this->registrarBitacora($idOperacion, self::ACCION_GESTOR_ENVIO_EVIDENCIAS_ADJUDICACION, $idUsuario, $nombreUsuario, $ahora);
        }
    }

    private function faltantesEvidenciaFisicaOperacion(int $idOperacion): array
    {
        if ($idOperacion <= 0) {
            return self::MADJ_SLOTS_EVIDENCIA_MEDIA;
        }

        $rows = $this->db->queryAll(
            'SELECT slot, url FROM adj_evidencia WHERE id_operacion = :id',
            ['id' => $idOperacion]
        ) ?: [];

        $slotsConArchivo = [];
        foreach ($rows as $row) {
            $slot = trim((string) ($row['slot'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            if ($slot !== '' && $url !== '') {
                $slotsConArchivo[$slot] = true;
            }
        }

        $faltantes = [];
        foreach (self::MADJ_SLOTS_EVIDENCIA_MEDIA as $slot) {
            if (empty($slotsConArchivo[$slot])) {
                $faltantes[] = self::SLOT_LABELS[$slot] ?? $slot;
            }
        }

        return $faltantes;
    }

    private function extraerDatosMotoDesdeDictumApp(array $campos): array
    {
        $map = [
            'marca' => ['moto_marca'],
            'modelo' => ['moto_modelo'],
            'ano' => ['moto_anio'],
            'color' => ['moto_color'],
            'no_de_serie_vin' => ['moto_no_serie', 'serie'],
            'no_de_motor' => ['moto_no_motor', 'num_motor'],
            'placas' => ['moto_placas', 'placas'],
            'kilometraje' => ['kilometraje'],
            'tiene_llave_fisica' => ['tiene_llave_fisica', 'llave_fisica'],
            'tiene_tarjeta_de_circulacion_en_fisico' => ['tiene_tarjeta_de_circulacion_en_fisico', 'tarjeta_circulacion'],
            'la_moto_tiene_placa_fisica' => ['la_moto_tiene_placa_fisica', 'placa_fisica'],
            'marca_y_modelo' => ['moto_modelo', 'modelo'],
            'direccion_actual' => ['log_direccion'],
            'actualizacion_de_direccion' => ['log_direccion'],
            'actualizacion_de_numero_telefonico' => ['log_telefono'],
            'celular' => ['log_telefono'],
            'estado_de_lugar_de_resguardo_ejemplo_ciudad_de_mex' => ['log_estado'],
            'ciudad_municipio_de_lugar_de_resguardo' => ['log_ciudad'],
            'calle_y_numero_de_lugar_de_resguardo' => ['log_direccion'],
            'responsable_de_resguardo' => ['responsable_entrega'],
            'telefono_de_contacto' => ['log_telefono'],
        ];

        $datos = [];
        foreach ($map as $nombreCampo => $cols) {
            $valor = $this->valorCampoDictumApp($campos, $nombreCampo);
            if ($valor === '') {
                continue;
            }
            foreach ($cols as $col) {
                $datos[$col] = $valor;
            }
        }

        $lugar = strtolower($this->valorCampoDictumApp($campos, 'donde_resguardaras_la_moto'));
        $lugarLabel = $this->valorCampoDictumApp($campos, 'donde_resguardaras_la_moto', true);
        if ($lugar !== '' || $lugarLabel !== '') {
            if (in_array($lugar, ['mi_domicilio', 'mi domicilio'], true)) {
                $datos['log_lugar_resguardo'] = 'mi_domicilio';
                $datos['log_lugar_otro'] = null;
            } elseif (in_array($lugar, ['cedis-__SPARTA_SECRET_REDACTED__', 'sucursal'], true)) {
                $datos['log_lugar_resguardo'] = 'sucursal';
                $datos['log_lugar_otro'] = $lugarLabel !== '' ? $lugarLabel : $lugar;
            } else {
                $datos['log_lugar_resguardo'] = 'otro';
                $datos['log_lugar_otro'] = $lugarLabel !== '' ? $lugarLabel : $lugar;
            }
        }

        return $datos;
    }

    private function upsertEvidenciaDictumApp(int $idOperacion, string $slot, string $url, int $idUsuario, string $fecha): bool
    {
        $urlRespaldada = $this->respaldarEvidenciaDictumApp($idOperacion, $slot, $url);
        if ($urlRespaldada === null) {
            return false;
        }

        $tipo = $this->tipoEvidenciaPorUrl($urlRespaldada);
        $old = $this->db->queryOne(
            'SELECT id, url, estatus FROM adj_evidencia WHERE id_operacion = :id AND slot = :slot LIMIT 1',
            ['id' => $idOperacion, 'slot' => $slot]
        );

        if ($old) {
            $urlAnterior = trim((string) ($old['url'] ?? ''));
            if ($this->operacionTieneEnvioAtencionMarcado($idOperacion)) {
                /*
                 * Aunque la evidencia ya haya pasado por AtenciÃ³n, la URL de
                 * la app mÃ³vil no se debe conservar. El respaldo local ya se
                 * descargÃ³ arriba; actualizar la referencia sin borrar el
                 * veredicto evita que Firebase deje un expediente sin archivo.
                 */
                if ($urlAnterior !== $urlRespaldada) {
                    $this->db->CRUD(
                        "UPDATE adj_evidencia
                         SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = 'recibido'
                         WHERE id_operacion = :id AND slot = :slot",
                        [
                            'tipo' => $tipo,
                            'url' => $urlRespaldada,
                            'fecha' => $fecha,
                            'id' => $idOperacion,
                            'slot' => $slot,
                        ]
                    );
                }
                return true;
            }
            $historialRechazo = $this->db->queryOne(
                'SELECT 1 AS ok
                 FROM adj_evidencia_rechazo_historial
                 WHERE id_operacion = :id
                   AND id_evidencia = :evidencia
                 LIMIT 1',
                [
                    'id' => $idOperacion,
                    'evidencia' => (int) ($old['id'] ?? 0),
                ]
            );
            if ($historialRechazo) {
                return true;
            }

            if ($urlAnterior === $urlRespaldada) {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                     SET estatus = 'recibido'
                     WHERE id_operacion = :id AND slot = :slot AND estatus <> 'recibido'",
                    ['id' => $idOperacion, 'slot' => $slot]
                );
                return true;
            }

            if ($this->adjEvidenciaTieneColumnasAtn() && !$this->operacionTieneEnvioAtencionMarcado($idOperacion)) {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                     SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = 'recibido',
                         val_atn = NULL, comentario_atn = NULL
                     WHERE id_operacion = :id AND slot = :slot",
                    ['tipo' => $tipo, 'url' => $urlRespaldada, 'fecha' => $fecha, 'id' => $idOperacion, 'slot' => $slot]
                );
            } elseif ($this->adjEvidenciaTieneColumnasAtn()) {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                     SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = 'recibido'
                     WHERE id_operacion = :id AND slot = :slot",
                    ['tipo' => $tipo, 'url' => $urlRespaldada, 'fecha' => $fecha, 'id' => $idOperacion, 'slot' => $slot]
                );
            } else {
                $this->db->CRUD(
                    "UPDATE adj_evidencia
                     SET tipo = :tipo, url = :url, fecha_alta = :fecha, estatus = 'recibido'
                     WHERE id_operacion = :id AND slot = :slot",
                    ['tipo' => $tipo, 'url' => $urlRespaldada, 'fecha' => $fecha, 'id' => $idOperacion, 'slot' => $slot]
                );
            }
            return true;
        }

        $this->db->CRUD(
            "INSERT INTO adj_evidencia (id_operacion, tipo, slot, url, fecha_alta, alta, estatus)
             VALUES (:id, :tipo, :slot, :url, :fecha, :alta, 'recibido')",
            ['id' => $idOperacion, 'tipo' => $tipo, 'slot' => $slot, 'url' => $urlRespaldada, 'fecha' => $fecha, 'alta' => $idUsuario ?: 0]
        );

        return true;
    }

    /**
     * La app movil entrega enlaces temporales de Firebase. Conservamos una copia
     * local antes de registrar la evidencia para que no dependa del origen remoto.
     */
    private function respaldarEvidenciaDictumApp(int $idOperacion, string $slot, string $url): ?string
    {
        if (!function_exists('sparta_uploads_join')) {
            require_once dirname(__DIR__) . '/core/UploadsPaths.php';
        }

        $url = trim($url);
        if ($idOperacion <= 0 || $slot === '' || !preg_match('#^https?://#i', $url)) {
            return null;
        }

        $ext = $this->extensionEvidenciaRemotaDictum($url, $slot);
        $hash = substr(hash('sha256', $url), 0, 20);
        $nombreArchivo = 'dictum_' . $slot . '_' . $hash . '.' . $ext;
        $rutaRelativa = 'operaciones/' . $idOperacion . '/' . $nombreArchivo;
        $rutaDestino = sparta_uploads_join($rutaRelativa);

        if (is_file($rutaDestino) && filesize($rutaDestino) > 0) {
            return '/uploads/' . str_replace('\\', '/', $rutaRelativa);
        }

        $directorio = dirname($rutaDestino);
        if (!is_dir($directorio) && !mkdir($directorio, 0755, true) && !is_dir($directorio)) {
            return null;
        }

        $temporal = tempnam(sys_get_temp_dir(), 'sp_dictum_ev_');
        if ($temporal === false) {
            return null;
        }

        $descargado = false;
        try {
            if (function_exists('curl_init')) {
                $archivoTemporal = fopen($temporal, 'wb');
                if ($archivoTemporal !== false) {
                    $curl = curl_init($url);
                    curl_setopt_array($curl, [
                        CURLOPT_FILE => $archivoTemporal,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_MAXREDIRS => 3,
                        CURLOPT_CONNECTTIMEOUT => 10,
                        CURLOPT_TIMEOUT => 90,
                        CURLOPT_FAILONERROR => true,
                        CURLOPT_USERAGENT => 'sparta-evidencias/1.0',
                    ]);
                    $descargado = curl_exec($curl) === true;
                    curl_close($curl);
                    fclose($archivoTemporal);
                }
            }

            if (!$descargado || !is_file($temporal) || filesize($temporal) <= 0 || filesize($temporal) > 100 * 1024 * 1024) {
                return null;
            }

            $mime = $this->mimeEvidenciaRemotaDictum($temporal);
            if (!$this->mimeCorrespondeAEvidenciaDictum($slot, $mime)) {
                return null;
            }

            if (!@rename($temporal, $rutaDestino)) {
                return null;
            }
            $temporal = '';

            return '/uploads/' . str_replace('\\', '/', $rutaRelativa);
        } finally {
            if ($temporal !== '' && is_file($temporal)) {
                @unlink($temporal);
            }
        }
    }

    /**
     * Migra evidencias antiguas que aÃºn apuntan directamente a Firebase a la
     * carpeta local de Sparta. No modifica validaciones ni fechas de captura.
     * Las que el origen ya no puede entregar se reportan para recuperarlas desde
     * el respaldo de la app, sin ocultar el problema con un enlace roto.
     *
     * @return array{revisadas:int,respaldadas:int,no_disponibles:int,omitidas:int}
     */
    public function migrarRespaldosEvidenciasFirebase(?int $idOperacion = null, int $limite = 500): array
    {
        $limite = max(1, min($limite, 5000));
        $params = ['firebase' => 'https://firebasestorage.googleapis.com/%'];
        $whereOperacion = '';
        if ($idOperacion !== null && $idOperacion > 0) {
            $whereOperacion = ' AND id_operacion = :id_operacion';
            $params['id_operacion'] = $idOperacion;
        }

        $rows = $this->db->queryAll(
            "SELECT id, id_operacion, slot, url
             FROM adj_evidencia
             WHERE url LIKE :firebase{$whereOperacion}
             ORDER BY id ASC
             LIMIT {$limite}",
            $params
        ) ?: [];

        $resultado = ['revisadas' => 0, 'respaldadas' => 0, 'no_disponibles' => 0, 'omitidas' => 0];
        $slotsDictum = array_values(self::DICTUM_APP_EVIDENCIA_SLOTS);
        foreach ($rows as $row) {
            $resultado['revisadas']++;
            $slot = trim((string) ($row['slot'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            $idEvidencia = (int) ($row['id'] ?? 0);
            $operacion = (int) ($row['id_operacion'] ?? 0);
            if ($idEvidencia <= 0 || $operacion <= 0 || $url === '' || !in_array($slot, $slotsDictum, true)) {
                $resultado['omitidas']++;
                continue;
            }

            $urlLocal = $this->respaldarEvidenciaDictumApp($operacion, $slot, $url);
            if ($urlLocal === null) {
                $resultado['no_disponibles']++;
                continue;
            }

            $this->db->CRUD(
                'UPDATE adj_evidencia
                 SET url = :url_local, tipo = :tipo
                 WHERE id = :id AND id_operacion = :id_operacion AND url = :url_remota',
                [
                    'url_local' => $urlLocal,
                    'tipo' => $this->tipoEvidenciaPorUrl($urlLocal),
                    'id' => $idEvidencia,
                    'id_operacion' => $operacion,
                    'url_remota' => $url,
                ]
            );
            $resultado['respaldadas']++;
        }

        return $resultado;
    }

    private function extensionEvidenciaRemotaDictum(string $url, string $slot): string
    {
        $path = rawurldecode((string) (parse_url($url, PHP_URL_PATH) ?: ''));
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'mp4'], true)) {
            return $ext;
        }

        return in_array($slot, ['fis_360_encendida', 'fis_video_cliente_acuerdo', 'fis_video_vuelta_prueba'], true)
            ? 'mp4'
            : 'jpg';
    }

    private function mimeEvidenciaRemotaDictum(string $archivo): string
    {
        if (!function_exists('finfo_open')) {
            return '';
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return '';
        }
        $mime = (string) finfo_file($finfo, $archivo);
        finfo_close($finfo);

        return strtolower($mime);
    }

    private function mimeCorrespondeAEvidenciaDictum(string $slot, string $mime): bool
    {
        $esVideo = in_array($slot, ['fis_360_encendida', 'fis_video_cliente_acuerdo', 'fis_video_vuelta_prueba'], true);
        if ($esVideo) {
            return in_array($mime, ['video/mp4', 'application/octet-stream'], true);
        }

        return in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function tipoEvidenciaPorUrl(string $url): string
    {
        $path = strtolower((string) parse_url($url, PHP_URL_PATH));
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'mp4' || strpos($path, '/videos/') !== false) {
            return 'video';
        }
        if ($ext === 'pdf') {
            return 'pdf';
        }
        return 'image';
    }

    private function existeBitacoraOperacion(int $idOperacion, string $like): bool
    {
        $row = $this->db->queryOne(
            'SELECT 1 AS ok FROM adj_bitacora WHERE id_operacion = :id AND accion LIKE :accion LIMIT 1',
            ['id' => $idOperacion, 'accion' => $like]
        );

        return (bool) ($row && (int) ($row['ok'] ?? 0) === 1);
    }

    private function existeBitacoraEnvioEvidenciasAdjudicacion(int $idOperacion): bool
    {
        $row = $this->db->queryOne(
            "SELECT 1 AS ok
             FROM adj_bitacora
             WHERE id_operacion = :id
               AND (
                    accion LIKE '%AL PIPELINE%'
                    OR accion LIKE '%EVIDENCIAS DE LA ADJUDICACION%'
               )
             LIMIT 1",
            ['id' => $idOperacion]
        );

        return (bool) ($row && (int) ($row['ok'] ?? 0) === 1);
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
        $this->sincronizarDictumsAppParaPersona($idPersona);

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
                TRIM(CONCAT_WS(' ', per_resp.nombres, per_resp.apellidop)) AS responsable_nombre,
                TRIM(CONCAT_WS(' ', per_alta.nombres, per_alta.apellidop)) AS asignado_por,
                aca.id                                                  AS id_asignacion,
                COALESCE(madj_ev.total, 0)                              AS madj_ev_total,
                COALESCE(madj_ev.pendiente, 0)                          AS madj_ev_pendiente,
                COALESCE(madj_ev.rechazo_atn, 0)                        AS madj_ev_rechazo_atn
            FROM asigna_creditos_adjudicacion aca
            INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
            INNER JOIN persona per_resp ON per_resp.id = pa.id_persona
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

    private function asegurarOperacionesParaAsignacionesActivas(int $idPersona): void
    {
        if ($idPersona <= 0) {
            return;
        }

        try {
            $rows = $this->db->queryAll(
                "SELECT aca.id_credito,
                        COALESCE(NULLIF(TRIM(ao.nombre_cliente), ''), CONCAT('Crédito #', aca.id_credito)) AS nombre_cliente
                 FROM asigna_creditos_adjudicacion aca
                 INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
                 LEFT JOIN adj_operacion ao ON ao.id = (
                     SELECT ao2.id
                     FROM adj_operacion ao2
                     WHERE ao2.id_credito = aca.id_credito
                     ORDER BY ao2.id DESC
                     LIMIT 1
                 )
                 WHERE pa.id_persona = :idPersona
                   AND aca.estatus = '1'
                   AND ao.id IS NULL
                 ORDER BY aca.fecha_alta DESC
                 LIMIT 50",
                ['idPersona' => $idPersona]
            ) ?: [];

            foreach ($rows as $row) {
                $idCredito = (int) ($row['id_credito'] ?? 0);
                if ($idCredito <= 0) {
                    continue;
                }
                $datosCredito = $this->obtenerDatosTaskLegacyMotoAutorizada($idCredito);
                $nombreCliente = trim((string) ($datosCredito['client_name'] ?? ''));
                $this->obtenerOCrearOperacion(
                    $idCredito,
                    $nombreCliente !== '' ? $nombreCliente : trim((string) ($row['nombre_cliente'] ?? '')),
                    $idPersona
                );
            }
        } catch (\Throwable $e) {
        }
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
        $this->sincronizarDictumsAppParaCreditos($ids);

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
        'kilometraje', 'tiene_llave_fisica', 'tiene_tarjeta_de_circulacion_en_fisico',
        'la_moto_tiene_placa_fisica', 'llave_fisica', 'tarjeta_circulacion', 'placa_fisica',
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
            // Una motocicleta puede no tener placas. En ese caso se guarda una
            // cadena vacia; una placa capturada si conserva la validacion.
            if ($placas !== '') {
                $lp = strlen($placas);
                if ($lp < self::MADJ_PLACAS_MOTO_MIN_LEN || $lp > self::MADJ_PLACAS_MOTO_MAX_LEN) {
                    return 'Las placas de motocicleta deben tener entre '
                        . self::MADJ_PLACAS_MOTO_MIN_LEN . ' y ' . self::MADJ_PLACAS_MOTO_MAX_LEN
                        . ' caracteres (en M?xico el formato de serie suele ser corto, p. ej. Y001AA).';
                }
                if (!preg_match('/^[A-Z0-9\-]+$/', $placas)) {
                    return 'Las placas solo pueden incluir letras, n?meros y guion.';
                }
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
            $reintentarFalloServicio = false;
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

                if (
                    empty($criterioForzado['ok'])
                    || empty($out['repuve_error_servicio'])
                    || !$this->repuveEsFalloServicioReintentable($row)
                ) {
                    return $this->repuveEnriquecerResultado($idCredito, $idUsuario, $out);
                }
                $reintentarFalloServicio = true;
            }

            if (!$reintentarFalloServicio) {
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

        // REPUVE puede responder con el dictamen antes de incluir todos los datos de la moto.
        // Este indicador solo se activa con una confirmacion expresa de reporte de robo.
        $result['reporte_robo'] = $this->analizarReporteRoboRepuve($result);

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

    /**
     * Determina si la respuesta de REPUVE confirma un reporte de robo vigente.
     * No infiere robo por errores, ausencia de datos o mensajes ambiguos.
     *
     * @return array{confirmado: bool, motivo: ?string, campo: ?string}
     */
    public function analizarReporteRoboRepuve(array $resultado): array
    {
        $sinConfirmacion = ['confirmado' => false, 'motivo' => null, 'campo' => null];
        $origenes = [
            $resultado['repuve_respuesta_api'] ?? null,
            $resultado['repuve_ultima_encuesta'] ?? null,
        ];

        foreach ($origenes as $origen) {
            if (!is_array($origen)) {
                continue;
            }
            $deteccion = $this->repuveBuscarReporteRoboEnNodo($origen);
            if (!empty($deteccion['confirmado'])) {
                return $deteccion;
            }
        }

        return $sinConfirmacion;
    }

    /** @return array{confirmado: bool, motivo: ?string, campo: ?string} */
    private function repuveBuscarReporteRoboEnNodo($nodo, string $ruta = ''): array
    {
        $vacio = ['confirmado' => false, 'motivo' => null, 'campo' => null];
        if (!is_array($nodo)) {
            return $vacio;
        }

        foreach ($nodo as $clave => $valor) {
            $rutaActual = $ruta === '' ? (string) $clave : $ruta . '.' . $clave;
            if (is_array($valor)) {
                $encontrado = $this->repuveBuscarReporteRoboEnNodo($valor, $rutaActual);
                if (!empty($encontrado['confirmado'])) {
                    return $encontrado;
                }
                continue;
            }

            $claveNormalizada = $this->repuveNormalizarTexto((string) $clave);
            if (!$this->repuveClavePuedeIndicarRobo($claveNormalizada)) {
                continue;
            }

            $dictamen = $this->repuveValorIndicaReporteRobo($valor);
            if ($dictamen === true) {
                return [
                    'confirmado' => true,
                    'motivo' => $this->textoRepuveValor($valor),
                    'campo' => $rutaActual,
                ];
            }
        }

        return $vacio;
    }

    private function repuveClavePuedeIndicarRobo(string $clave): bool
    {
        if ($clave === '') {
            return false;
        }

        foreach ([
            'reporterobo', 'reportederobo', 'tienereporterobo', 'roboactivo',
            'robovigente', 'vehiculorobado', 'unidadrobada', 'estatusrobo',
            'estadorobo', 'situacionrobo', 'alertarobo',
        ] as $indicador) {
            if (str_contains($clave, $indicador)) {
                return true;
            }
        }

        // Algunos proveedores entregan el dictamen en un campo genérico de mensaje.
        return in_array($clave, ['mensaje', 'message', 'observacion', 'observaciones', 'situacion', 'estatus', 'estado'], true);
    }

    /** null = sin dictamen concluyente. */
    private function repuveValorIndicaReporteRobo($valor): ?bool
    {
        if (is_bool($valor)) {
            return $valor;
        }
        if (is_int($valor) || is_float($valor)) {
            return (float) $valor > 0 ? true : false;
        }

        $texto = $this->repuveNormalizarTexto($this->textoRepuveValor($valor));
        if ($texto === '') {
            return null;
        }

        foreach ([
            'sin reporte de robo', 'sin reporte', 'no cuenta con reporte',
            'no tiene reporte', 'sin antecedentes de robo', 'no robado',
            'vehiculo recuperado', 'unidad recuperada', 'recuperado',
        ] as $negativo) {
            if (str_contains($texto, $negativo)) {
                return false;
            }
        }

        if (in_array($texto, ['si', 'true', '1', 'robado'], true)) {
            return true;
        }
        foreach ([
            'con reporte de robo', 'reporte de robo vigente', 'reporte de robo activo',
            'vehiculo robado', 'unidad robada', 'reporte por robo',
        ] as $positivo) {
            if (str_contains($texto, $positivo)) {
                return true;
            }
        }

        if (in_array($texto, ['no', 'false', '0'], true)) {
            return false;
        }

        return null;
    }

    private function repuveNormalizarTexto(string $texto): string
    {
        $texto = trim($texto);
        if (function_exists('mb_strtolower')) {
            $texto = mb_strtolower($texto, 'UTF-8');
        } else {
            $texto = strtolower($texto);
        }
        $texto = strtr($texto, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ü' => 'u', 'ñ' => 'n',
        ]);
        return preg_replace('/\s+/u', ' ', $texto) ?? '';
    }

    private function textoRepuveValor($valor): string
    {
        return trim(is_scalar($valor) ? (string) $valor : '');
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
                $estadoFinal = ($status === 'ERROR' || empty($res['ok'])) ? 'ERROR' : 'COMPLETADO';
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
     * Fallos de disponibilidad del proveedor que no deben dejar atrapado el credito en cache.
     * Si el usuario vuelve a consultar desde la vista dedicada, se permite un nuevo intento.
     */
    private function repuveEsFalloServicioReintentable(array $row): bool
    {
        $http = (int) ($row['http_status'] ?? 0);
        if (in_array($http, [408, 429, 500, 502, 503, 504], true)) {
            return true;
        }

        $mc = isset($row['message_code']) && $row['message_code'] !== '' ? (int) $row['message_code'] : null;
        if ($mc === 40) {
            return true;
        }

        $msgL = strtolower(trim((string) ($row['mensaje'] ?? '')));
        foreach (['temporarily unavailable', 'try again later', 'service unavailable', 'timeout', 'bad gateway', 'gateway'] as $needle) {
            if ($msgL !== '' && str_contains($msgL, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Arma respuesta JSON unificada tras leer adj_repuve_consulta (evita marcar exito solo porque el VIN coincide con la consulta).
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
        $estadoRespuesta = $estadoRow !== '' ? $estadoRow : (string) ($row['estado'] ?? '');

        if ($tieneReal) {
            $tipo = 'datos_ok';
            $message = $mensajeExitoConDatos;
            $errorServicio = false;
            $sinDatos = false;
        } elseif ($this->repuveEsFalloServicioExterno($row, $mensajeTabla)) {
            $tipo = 'fallo_servicio';
            $errorServicio = true;
            $estadoRespuesta = 'ERROR';
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
            'repuve_reintento_disponible' => $errorServicio && $this->repuveEsFalloServicioReintentable($row),
            'repuve_sin_datos_padron' => $sinDatos,
            'repuve'                  => [
                'estado'         => $estadoRespuesta,
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
                $apiUrl = 'http://127.0.0.1:8001/api/v1/verificar';
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

    public function guardarDatosMoto(
        int $idOperacion,
        array $datos,
        int $idUsuario = 0,
        string $nombreUsuario = '',
        bool $registrarBitacora = true,
        ?string $fechaOperacion = null,
        bool $permitirVaciosExplicitos = false
    ): array
    {
        if ($idOperacion <= 0) {
            return ['success' => false, 'message' => 'Operaci?n inv?lida.'];
        }

        $this->asegurarColumnasFormularioMoto();

        $op = $this->db->queryOne(
            'SELECT id FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }

        // REPUVE/Nubarium: confiar en el origen; la validación estricta evitaba guardar en adj_operacion.
        if ($nombreUsuario !== 'REPUVE') {
            $datosValidacion = $datos;
            if ($permitirVaciosExplicitos) {
                foreach ($datosValidacion as $campo => $valor) {
                    if (is_scalar($valor) && trim((string) $valor) === '') {
                        unset($datosValidacion[$campo]);
                    }
                }
            }

            $fmtErr = $this->validarDatosMotoFormatos($datosValidacion);
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
            if (!$this->adjOperacionTieneColumna($campo)) {
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

        $ahora             = $this->normalizarFechaHoraOperacion($fechaOperacion);
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

    public function obtenerOperacionOtpLegacyPorCredito(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de credito invalido.'];
        }

        $op = $this->db->queryOne(
            "SELECT id, folio, id_credito, nombre_cliente, estatus,
                    moto_marca, moto_modelo,
                    DATE_FORMAT(fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(fecha_actualizacion, '%d/%m/%Y %H:%i') AS fecha_actualizacion_fmt
             FROM adj_operacion
             WHERE id_credito = :id_credito
             ORDER BY id DESC
             LIMIT 1",
            ['id_credito' => $idCredito]
        );

        if (!$op || (int) ($op['id'] ?? 0) <= 0) {
            return ['success' => false, 'message' => 'No se encontro una operacion para este credito.'];
        }

        return [
            'success' => true,
            'operacion' => [
                'id_operacion' => (int) $op['id'],
                'folio' => (string) ($op['folio'] ?? ''),
                'id_credito' => (int) ($op['id_credito'] ?? 0),
                'nombre_cliente' => trim((string) ($op['nombre_cliente'] ?? '')),
                'estatus' => (string) ($op['estatus'] ?? ''),
                'moto' => trim((string) (($op['moto_marca'] ?? '') . ' ' . ($op['moto_modelo'] ?? ''))),
                'fecha_alta_fmt' => (string) ($op['fecha_alta_fmt'] ?? ''),
                'fecha_actualizacion_fmt' => (string) ($op['fecha_actualizacion_fmt'] ?? ''),
            ],
        ];
    }

    /**
     * Busca la operaci?n m?s reciente para un id_credito en adj_operacion.
     * Si no existe ninguna, crea una autom?ticamente con datos m?nimos.
     *
     * @return array{success:bool, detalle?:array, creado?:bool, message?:string}
     */
    public function obtenerOCrearOperacion(int $idCredito, string $nombreCliente, int $idUsuario = 0, ?string $fechaOperacion = null): array
    {
        $fecha = $this->normalizarFechaHoraOperacion($fechaOperacion);
        $op = $this->db->queryOne(
            'SELECT id FROM adj_operacion WHERE id_credito = :id ORDER BY id DESC LIMIT 1',
            ['id' => $idCredito]
        );

        if ($op) {
            $this->sincronizarDictumAppCreditoOperacion($idCredito, (int) $op['id'], $idUsuario, 'APP MOVIL', $fecha);
            $detalle = $this->obtenerDetalle((int) $op['id']);
            return ['success' => true, 'detalle' => $detalle];
        }

        // No existe ??? crear con datos m?nimos
        $folio = $this->generarFolio();

        $campos = [
            'folio'               => $folio,
            'id_credito'          => $idCredito,
            'nombre_cliente'      => $nombreCliente !== '' ? $nombreCliente : "Cr?dito #{$idCredito}",
            'estatus'             => 'en_transito',
            'id_usuario_alta'     => $idUsuario ?: null,
            'fecha_alta'          => $fecha,
            'fecha_actualizacion' => $fecha,
        ];

        $cols = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($campos)));
        $ph   = implode(', ', array_map(fn($k) => ":{$k}", array_keys($campos)));
        $this->db->CRUD("INSERT INTO adj_operacion ({$cols}) VALUES ({$ph})", $campos);

        $newId   = (int) $this->db->lastInsertId();
        $this->sincronizarDictumAppCreditoOperacion($idCredito, $newId, $idUsuario, 'APP MOVIL', $fecha);
        $detalle = $this->obtenerDetalle($newId);

        return ['success' => true, 'detalle' => $detalle, 'creado' => true];
    }

    /**
     * Detalle liviano para vistas que ya listaron la operacion.
     * Evita consultar/sincronizar Legacy en cada apertura del modal.
     */
    public function obtenerDetalleRapidoPorCredito(int $idCredito): array
    {
        if ($idCredito <= 0) {
            return ['success' => false, 'message' => 'ID de credito invalido.'];
        }

        $this->asegurarColumnasFormularioMoto();

        $colsFormularioCapturado = implode(",\n                    ", [
            $this->adjOperacionSelectColumnaONull('kilometraje'),
            $this->adjOperacionSelectColumnaONull('tiene_llave_fisica'),
            $this->adjOperacionSelectColumnaONull('tiene_tarjeta_de_circulacion_en_fisico'),
            $this->adjOperacionSelectColumnaONull('la_moto_tiene_placa_fisica'),
            $this->adjOperacionSelectColumnaONull('llave_fisica'),
            $this->adjOperacionSelectColumnaONull('tarjeta_circulacion'),
            $this->adjOperacionSelectColumnaONull('placa_fisica'),
        ]);

        $op = $this->db->queryOne(
            "SELECT id, folio, id_credito, nombre_cliente, estatus,
                    moto_marca, moto_modelo, moto_anio, moto_color,
                    moto_no_serie, moto_no_motor, moto_placas,
                    {$colsFormularioCapturado},
                    log_direccion, log_ciudad, log_estado, log_lugar_resguardo,
                    log_lugar_otro, log_telefono, responsable_entrega,
                    DATE_FORMAT(fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(fecha_actualizacion, '%Y-%m-%d %H:%i') AS fecha_actualizacion_fmt,
                    DATE_FORMAT(datos_moto_at, '%d/%m/%Y %H:%i') AS datos_moto_fecha
             FROM adj_operacion
             WHERE id_credito = :id
             ORDER BY id DESC
             LIMIT 1",
            ['id' => $idCredito]
        );

        if (!$op || empty($op['id'])) {
            return ['success' => false, 'message' => 'Operacion no encontrada.'];
        }

        if ($this->adjEvidenciaTieneColumnasAtn()) {
            $evs = $this->db->queryAll(
                "SELECT id, tipo, slot, url, estatus, val_atn, comentario_atn,
                        DATE_FORMAT(fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta
                 FROM adj_evidencia
                 WHERE id_operacion = :id
                 ORDER BY id ASC",
                ['id' => (int) $op['id']]
            ) ?: [];
        } else {
            $evs = $this->db->queryAll(
                "SELECT id, tipo, slot, url, estatus,
                        DATE_FORMAT(fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta
                 FROM adj_evidencia
                 WHERE id_operacion = :id
                 ORDER BY id ASC",
                ['id' => (int) $op['id']]
            ) ?: [];
        }

        foreach ($evs as &$ev) {
            $ev['aprobada'] = 0;
            $ev['val_atn'] = isset($ev['val_atn']) && $ev['val_atn'] !== null && $ev['val_atn'] !== ''
                ? (int) $ev['val_atn']
                : 0;
            $ev['comentario_atn'] = isset($ev['comentario_atn']) ? (string) $ev['comentario_atn'] : '';
            $ev['archivo_estado'] = !empty($ev['url'])
                ? $this->obtenerEstadoArchivoEvidencia((string) $ev['url'])
                : 'sin_archivo';
        }
        unset($ev);

        $op['evidencias'] = $evs;
        $op['historial'] = [];
        $op['observaciones'] = [];

        return ['success' => true, 'detalle' => $op];
    }

    /**
     * Ruta original de una evidencia para servirla mediante un endpoint estable.
     * Evita que las vistas dependan de la ubicacion publica de uploads.
     */
    public function obtenerArchivoEvidenciaPorId(int $idEvidencia): ?array
    {
        if ($idEvidencia <= 0) {
            return null;
        }

        return $this->db->queryOne(
            'SELECT id, url, tipo, slot
             FROM adj_evidencia
             WHERE id = :id
             LIMIT 1',
            ['id' => $idEvidencia]
        ) ?: null;
    }

    public function obtenerDetallesRapidosPorCreditos(array $idsCredito): array
    {
        $ids = [];
        foreach ($idsCredito as $id) {
            $n = (int) $id;
            if ($n > 0) $ids[$n] = $n;
        }
        $ids = array_values($ids);
        if ($ids === []) {
            return [];
        }

        $this->asegurarColumnasFormularioMoto();

        $params = [];
        $ph = [];
        foreach ($ids as $i => $id) {
            $k = 'c' . $i;
            $ph[] = ':' . $k;
            $params[$k] = $id;
        }

        $colsFormularioCapturado = implode(",\n                    ", [
            $this->adjOperacionSelectColumnaONull('kilometraje', 'ao'),
            $this->adjOperacionSelectColumnaONull('tiene_llave_fisica', 'ao'),
            $this->adjOperacionSelectColumnaONull('tiene_tarjeta_de_circulacion_en_fisico', 'ao'),
            $this->adjOperacionSelectColumnaONull('la_moto_tiene_placa_fisica', 'ao'),
            $this->adjOperacionSelectColumnaONull('llave_fisica', 'ao'),
            $this->adjOperacionSelectColumnaONull('tarjeta_circulacion', 'ao'),
            $this->adjOperacionSelectColumnaONull('placa_fisica', 'ao'),
        ]);

        $ops = $this->db->queryAll(
            "SELECT ao.id, ao.folio, ao.id_credito, ao.nombre_cliente, ao.estatus,
                    ao.moto_marca, ao.moto_modelo, ao.moto_anio, ao.moto_color,
                    ao.moto_no_serie, ao.moto_no_motor, ao.moto_placas,
                    {$colsFormularioCapturado},
                    ao.log_direccion, ao.log_ciudad, ao.log_estado, ao.log_lugar_resguardo,
                    ao.log_lugar_otro, ao.log_telefono, ao.responsable_entrega,
                    DATE_FORMAT(ao.fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta_fmt,
                    DATE_FORMAT(ao.fecha_actualizacion, '%Y-%m-%d %H:%i') AS fecha_actualizacion_fmt,
                    DATE_FORMAT(ao.datos_moto_at, '%d/%m/%Y %H:%i') AS datos_moto_fecha
             FROM adj_operacion ao
             INNER JOIN (
                 SELECT id_credito, MAX(id) AS id_max
                 FROM adj_operacion
                 WHERE id_credito IN (" . implode(',', $ph) . ")
                 GROUP BY id_credito
             ) ult ON ult.id_max = ao.id AND ult.id_credito = ao.id_credito",
            $params
        ) ?: [];

        if ($ops === []) {
            return [];
        }

        $out = [];
        $opIds = [];
        foreach ($ops as $op) {
            $idOp = (int) ($op['id'] ?? 0);
            $idCredito = (int) ($op['id_credito'] ?? 0);
            if ($idOp <= 0 || $idCredito <= 0) continue;
            $op['evidencias'] = [];
            $op['historial'] = [];
            $op['observaciones'] = [];
            $out[$idCredito] = ['success' => true, 'detalle' => $op];
            $opIds[$idOp] = $idCredito;
        }

        if ($opIds === []) {
            return $out;
        }

        $paramsEv = [];
        $phEv = [];
        foreach (array_keys($opIds) as $i => $idOp) {
            $k = 'op' . $i;
            $phEv[] = ':' . $k;
            $paramsEv[$k] = (int) $idOp;
        }

        if ($this->adjEvidenciaTieneColumnasAtn()) {
            $evs = $this->db->queryAll(
                "SELECT id_operacion, id, tipo, slot, url, estatus, val_atn, comentario_atn,
                        DATE_FORMAT(fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta
                 FROM adj_evidencia
                 WHERE id_operacion IN (" . implode(',', $phEv) . ")
                 ORDER BY id_operacion ASC, id ASC",
                $paramsEv
            ) ?: [];
        } else {
            $evs = $this->db->queryAll(
                "SELECT id_operacion, id, tipo, slot, url, estatus,
                        DATE_FORMAT(fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta
                 FROM adj_evidencia
                 WHERE id_operacion IN (" . implode(',', $phEv) . ")
                 ORDER BY id_operacion ASC, id ASC",
                $paramsEv
            ) ?: [];
        }

        foreach ($evs as $ev) {
            $idOp = (int) ($ev['id_operacion'] ?? 0);
            $idCredito = $opIds[$idOp] ?? 0;
            if ($idCredito <= 0 || empty($out[$idCredito])) continue;
            unset($ev['id_operacion']);
            $ev['aprobada'] = 0;
            $ev['val_atn'] = isset($ev['val_atn']) && $ev['val_atn'] !== null && $ev['val_atn'] !== ''
                ? (int) $ev['val_atn']
                : 0;
            $ev['comentario_atn'] = isset($ev['comentario_atn']) ? (string) $ev['comentario_atn'] : '';
            $out[$idCredito]['detalle']['evidencias'][] = $ev;
        }

        return $out;
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
        $slot = (string) ($row['slot'] ?? '');
        $labelEvidencia = trim((string) (self::SLOT_LABELS[$slot] ?? $slot));
        $this->registrarBitacora(
            $idOperacion,
            'VALIDACION EVIDENCIA ' . ($labelEvidencia !== '' ? $labelEvidencia . ': ' : '') . $etiq,
            $idUsuario,
            $nombreUsuario
        );
        return ['success' => true];
    }

    /**
     * Rechazo originado desde Tracking de Recoleccion. Conserva el mismo
     * mecanismo de evidencias, pero identifica el origen y envia la operacion
     * directamente a la bandeja de Correcciones.
     *
     * @return array{success:bool, message?:string, enviado_a_correcciones?:bool}
     */
    public function rechazarEvidenciaDesdeTracking(
        int $idOperacion,
        int $idEvidencia,
        string $motivo,
        int $idUsuario,
        string $nombreUsuario = ''
    ): array {
        $motivo = mb_substr(trim($motivo), 0, 1800);
        if ($motivo === '') {
            return ['success' => false, 'message' => 'Indica el motivo del rechazo.'];
        }

        // Antes de cambiar el estado, dejamos una version inmutable de la
        // evidencia rechazada. Asi una correccion o una nueva carga nunca
        // sustituye ni borra el archivo que Tracking reviso.
        $evidencia = $this->db->queryOne(
            'SELECT id, slot, url, val_atn, comentario_atn
             FROM adj_evidencia
             WHERE id = :evidencia AND id_operacion = :operacion
             LIMIT 1',
            ['evidencia' => $idEvidencia, 'operacion' => $idOperacion]
        );
        if (!$evidencia) {
            return ['success' => false, 'message' => 'Evidencia no encontrada.'];
        }
        $slot = trim((string) ($evidencia['slot'] ?? ''));
        if ($slot === '' || $slot === self::SLOT_REPVE_ATENCION) {
            return ['success' => false, 'message' => 'Esta evidencia no se puede rechazar desde Tracking.'];
        }

        $comentario = 'RECHAZADO POR TRACKING: ' . $motivo;
        $registro = $this->registrarRechazosEvidenciasBulkLocal(
            [
                'id_operacion' => $idOperacion,
                'evidencias' => [[
                    'id_evidencia' => $idEvidencia,
                    'slot' => $slot,
                    'url_vieja_rechazada' => (string) ($evidencia['url'] ?? ''),
                    'motivo_rechazo' => $comentario,
                ]],
            ],
            $idUsuario,
            $comentario,
            $nombreUsuario
        );
        if (empty($registro['success'])) {
            return $registro;
        }

        $operacion = $this->db->queryOne(
            'SELECT estatus FROM adj_operacion WHERE id = :id LIMIT 1',
            ['id' => $idOperacion]
        );
        if (!$operacion) {
            return ['success' => false, 'message' => 'Operacion no encontrada.'];
        }

        $enviado = false;
        if (!$this->esEstatusRevisionRecuperaciones((string) ($operacion['estatus'] ?? ''))) {
            $cambio = $this->cambiarEstatus(
                $idOperacion,
                'Revisión Recuperaciones',
                $idUsuario,
                $nombreUsuario,
                'TRACKING RECOLECCION'
            );
            if (empty($cambio['success'])) {
                // registrarRechazosEvidenciasBulkLocal confirma su propia
                // transaccion. Si el cambio de etapa fallara despues, se
                // compensa el rechazo para no dejar una evidencia marcada sin
                // que la operacion haya llegado realmente a Correcciones.
                try {
                    $this->db->beginTransaction();
                    $this->db->CRUD(
                        'UPDATE adj_evidencia
                         SET val_atn = :val_atn,
                             comentario_atn = :comentario_atn
                         WHERE id = :evidencia AND id_operacion = :operacion',
                        [
                            'val_atn' => $evidencia['val_atn'],
                            'comentario_atn' => $evidencia['comentario_atn'],
                            'evidencia' => $idEvidencia,
                            'operacion' => $idOperacion,
                        ]
                    );
                    foreach ((array) ($registro['rechazos'] ?? []) as $rechazo) {
                        $idRechazo = (int) ($rechazo['rechazo_historial_id'] ?? 0);
                        if ($idRechazo > 0) {
                            $this->db->CRUD(
                                'DELETE FROM adj_evidencia_rechazo_historial WHERE id = :id AND url_nueva IS NULL',
                                ['id' => $idRechazo]
                            );
                        }
                    }
                    $bitacora = $this->db->queryOne(
                        'SELECT id FROM adj_bitacora
                         WHERE id_operacion = :operacion
                           AND id_usuario = :usuario
                           AND accion = :accion
                         ORDER BY id DESC
                         LIMIT 1',
                        [
                            'operacion' => $idOperacion,
                            'usuario' => $idUsuario,
                            'accion' => 'REGISTRO RECHAZOS EVIDENCIAS APP (' . count((array) ($registro['rechazos'] ?? [])) . ')',
                        ]
                    );
                    if ($bitacora) {
                        $this->db->CRUD('DELETE FROM adj_bitacora WHERE id = :id', ['id' => (int) $bitacora['id']]);
                    }
                    $this->db->commit();
                } catch (\Throwable $rollbackError) {
                    $this->db->rollback();
                }
                return $cambio;
            }
            $enviado = true;
        }

        $this->registrarBitacora(
            $idOperacion,
            'RECHAZADO POR TRACKING - EVIDENCIA ' . $idEvidencia . ' - ' . $slot,
            $idUsuario,
            $nombreUsuario
        );

        return ['success' => true, 'enviado_a_correcciones' => $enviado];
    }

    private function diagnosticarValidacionAtencion(int $idOperacion): array
    {
        $faltantes = [];
        if (!$this->adjEvidenciaTieneColumnasAtn()) {
            return [
                'completa' => false,
                'faltantes' => ['Falta migración de base de datos: val_atn/comentario_atn.'],
            ];
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
            $label = self::SLOT_LABELS[$slot] ?? $slot;
            if (!isset($bySlot[$slot])) {
                $faltantes[] = $label . ' (sin evidencia)';
                continue;
            }
            $url = trim((string) ($bySlot[$slot]['url'] ?? ''));
            if ($url === '') {
                $faltantes[] = $label . ' (sin archivo)';
                continue;
            }
            $va = (int) ($bySlot[$slot]['val_atn'] ?? 0);
            if ($va !== 1) {
                $faltantes[] = $label . ($va === 2 ? ' (rechazada)' : ' (pendiente de aceptar)');
            }
        }
        if (!isset($bySlot[self::SLOT_REPVE_ATENCION])) {
            $faltantes[] = 'REPUVE (sin PDF)';
        } else {
            $urlRep = trim((string) ($bySlot[self::SLOT_REPVE_ATENCION]['url'] ?? ''));
            if ($urlRep === '') {
                $faltantes[] = 'REPUVE (sin PDF)';
            }
        }

        return [
            'completa' => $faltantes === [],
            'faltantes' => $faltantes,
        ];
    }

    private function faltantesMediaAceptadaAtencion(int $idOperacion): array
    {
        if ($idOperacion <= 0 || !$this->adjEvidenciaTieneColumnasAtn()) {
            return ['Evidencias fisicas'];
        }

        $rows = $this->db->queryAll(
            'SELECT slot, val_atn, url FROM adj_evidencia WHERE id_operacion = :id',
            ['id' => $idOperacion]
        ) ?: [];
        $bySlot = [];
        foreach ($rows as $r) {
            $sk = (string) ($r['slot'] ?? '');
            if ($sk !== '') $bySlot[$sk] = $r;
        }

        $faltantes = [];
        foreach (self::SLOTS_VALIDACION_ATENCION_MEDIA as $slotReq) {
            $label = self::SLOT_LABELS[$slotReq] ?? $slotReq;
            $row = $bySlot[$slotReq] ?? null;
            $url = trim((string) ($row['url'] ?? ''));
            $va = (int) ($row['val_atn'] ?? 0);
            if (!$row || $url === '') {
                $faltantes[] = $label . ' (sin evidencia)';
            } elseif ($va !== 1) {
                $faltantes[] = $label . ($va === 2 ? ' (rechazada)' : ' (pendiente)');
            }
        }

        return $faltantes;
    }

    /**
     * Listo para enviar a Procesando IA: evidencia f?sica (momento 1) completa con val_atn = 1 y PDF Repuve en expediente.
     */
    public function operacionTieneValidacionAtencionCompleta(int $idOperacion): bool
    {
        return (bool) ($this->diagnosticarValidacionAtencion($idOperacion)['completa'] ?? false);
    }

    /**
     * Deja la operacion en cola para el segundo knockout. La cola solo se usa
     * cuando la bandera esta habilitada y la clave vive en el servidor.
     *
     * @return array{success:bool,enabled?:bool,message?:string,media_hash?:string}
     */
    public function encolarValidacionEstadoMotoClaude(int $idOperacion): array
    {
        $cliente = new AnthropicMotoConditionClient();
        $config = $cliente->config();
        if (!$config['enabled']) {
            return ['success' => true, 'enabled' => false];
        }
        if (!$this->tablaKnockoutDisponible()) {
            return ['success' => false, 'message' => 'Falta aplicar scripts/migration_adjudicacion_knockouts.sql.'];
        }
        if ($config['api_key'] === '') {
            return ['success' => false, 'message' => 'La validacion IA esta habilitada, pero falta ANTHROPIC_API_KEY en el servidor.'];
        }

        $media = $this->recolectarMediosKnockoutIA($idOperacion, false);
        if (empty($media['success'])) {
            return $media;
        }
        $ahora = $this->fechaHoraCdmx();
        $this->db->CRUD(
            "INSERT INTO adj_validacion_knockout
                (id_operacion, tipo, estado, etiqueta, proveedor, media_hash, intentos, fecha_alta, fecha_actualizacion)
             VALUES (:id, 'ESTADO_MOTO_CLAUDE', 'PENDIENTE', 'Validacion IA pendiente', 'anthropic', :hash, 0, :fecha, :fecha)
             ON DUPLICATE KEY UPDATE
                estado = IF(media_hash <> VALUES(media_hash), 'PENDIENTE', estado),
                etiqueta = IF(media_hash <> VALUES(media_hash), 'Validacion IA pendiente', etiqueta),
                media_hash = VALUES(media_hash), fecha_actualizacion = VALUES(fecha_actualizacion)",
            ['id' => $idOperacion, 'hash' => $media['media_hash'], 'fecha' => $ahora]
        );
        return ['success' => true, 'enabled' => true, 'media_hash' => $media['media_hash']];
    }

    /** Procesado por scripts/procesar_validaciones_ia_adjudicacion.php. */
    public function procesarValidacionEstadoMotoClaude(int $idOperacion): array
    {
        if ($idOperacion <= 0 || !$this->tablaKnockoutDisponible()) {
            return ['success' => false, 'message' => 'No existe la cola de validacion IA.'];
        }
        $cliente = new AnthropicMotoConditionClient();
        $config = $cliente->config();
        if (!$config['enabled'] || $config['api_key'] === '') {
            return ['success' => false, 'pending_configuration' => true, 'message' => 'Anthropic aun no esta configurado.'];
        }
        $media = $this->recolectarMediosKnockoutIA($idOperacion, true);
        if (empty($media['success'])) {
            return $this->guardarResultadoKnockoutIA(
                $idOperacion,
                'REVISION_MANUAL',
                0,
                [$media['message'] ?? 'No se pudieron preparar las evidencias.'],
                [],
                '',
                $media['media_hash'] ?? null
            );
        }

        try {
            $respuesta = $cliente->analizarEstadoMoto($media['imagenes']);
            if (empty($respuesta['success'])) {
                return $this->guardarResultadoKnockoutIA(
                    $idOperacion,
                    'REVISION_MANUAL',
                    0,
                    [(string) ($respuesta['message'] ?? 'Anthropic no pudo evaluar las evidencias.')],
                    [],
                    '',
                    $media['media_hash']
                );
            }
            return $this->guardarResultadoKnockoutIA(
                $idOperacion,
                (string) $respuesta['estado'],
                (int) ($respuesta['confianza'] ?? 0),
                is_array($respuesta['motivos'] ?? null) ? $respuesta['motivos'] : [],
                is_array($respuesta['raw'] ?? null) ? $respuesta['raw'] : [],
                (string) ($respuesta['model'] ?? ''),
                $media['media_hash']
            );
        } finally {
            foreach ((array) ($media['temporales'] ?? []) as $temporal) {
                if (is_string($temporal) && is_file($temporal)) @unlink($temporal);
            }
        }
    }

    /** @return list<array{id_operacion:int}> */
    public function obtenerValidacionesEstadoMotoPendientes(int $limite = 20): array
    {
        if (!$this->tablaKnockoutDisponible()) return [];
        $limite = max(1, min(100, $limite));
        return $this->db->queryAll(
            "SELECT id_operacion
               FROM adj_validacion_knockout
              WHERE tipo = 'ESTADO_MOTO_CLAUDE'
                AND estado = 'PENDIENTE'
                AND id_operacion IS NOT NULL
              ORDER BY fecha_actualizacion ASC, id ASC
              LIMIT {$limite}"
        ) ?: [];
    }

    /** @return array{success:bool,message?:string,media_hash?:string,imagenes?:array,temporales?:array} */
    private function recolectarMediosKnockoutIA(int $idOperacion, bool $extraerFotogramas): array
    {
        if ($idOperacion <= 0) return ['success' => false, 'message' => 'Operacion invalida.'];
        $slots = array_merge(self::KNOCKOUT_IA_FOTOS, self::KNOCKOUT_IA_VIDEOS);
        $params = ['id_operacion' => $idOperacion];
        $in = [];
        foreach ($slots as $index => $slot) {
            $key = 'slot_' . $index;
            $in[] = ':' . $key;
            $params[$key] = $slot;
        }
        $rows = $this->db->queryAll(
            'SELECT slot, url FROM adj_evidencia WHERE id_operacion = :id_operacion AND slot IN (' . implode(',', $in) . ') ORDER BY id DESC',
            $params
        ) ?: [];
        $porSlot = [];
        foreach ($rows as $row) {
            $slot = (string) ($row['slot'] ?? '');
            if ($slot !== '' && !array_key_exists($slot, $porSlot)) {
                $porSlot[$slot] = (string) ($row['url'] ?? '');
            }
        }

        $faltantes = [];
        $fuentes = [];
        foreach ($slots as $slot) {
            $ruta = $this->rutaLocalEvidenciaKnockout((string) ($porSlot[$slot] ?? ''));
            if ($ruta === null) {
                $faltantes[] = self::SLOT_LABELS[$slot] ?? $slot;
            } else {
                $fuentes[$slot] = $ruta;
            }
        }
        if ($faltantes !== []) {
            return ['success' => false, 'message' => 'Faltan evidencias para el knockout IA: ' . implode(', ', $faltantes) . '.'];
        }

        $hashes = [];
        foreach ($fuentes as $slot => $ruta) {
            $hashes[] = $slot . ':' . (hash_file('sha256', $ruta) ?: '');
        }
        sort($hashes, SORT_STRING);
        $mediaHash = hash('sha256', implode('|', $hashes));
        if (!$extraerFotogramas) return ['success' => true, 'media_hash' => $mediaHash];

        $imagenes = [];
        foreach (self::KNOCKOUT_IA_FOTOS as $slot) $imagenes[] = $fuentes[$slot];
        $temporales = [];
        foreach (self::KNOCKOUT_IA_VIDEOS as $slot) {
            $frames = $this->extraerFotogramasKnockoutIA($idOperacion, $slot, $fuentes[$slot], $mediaHash);
            if ($frames === []) {
                foreach ($temporales as $archivo) if (is_file($archivo)) @unlink($archivo);
                return ['success' => false, 'media_hash' => $mediaHash, 'message' => 'No se pudieron extraer fotogramas del video ' . (self::SLOT_LABELS[$slot] ?? $slot) . '.'];
            }
            $imagenes = array_merge($imagenes, $frames);
            $temporales = array_merge($temporales, $frames);
        }
        return ['success' => true, 'media_hash' => $mediaHash, 'imagenes' => $imagenes, 'temporales' => $temporales];
    }

    private function rutaLocalEvidenciaKnockout(string $url): ?string
    {
        $url = str_replace('\\', '/', trim($url));
        if (!preg_match('#^/uploads/operaciones/[0-9]+/[A-Za-z0-9_.-]+$#', $url)) return null;
        $path = dirname(__DIR__, 2) . '/public' . $url;
        return is_file($path) && is_readable($path) ? $path : null;
    }

    /** @return list<string> */
    private function extraerFotogramasKnockoutIA(int $idOperacion, string $slot, string $video, string $mediaHash): array
    {
        $ffmpeg = $this->valorEnvKnockoutIA('ADJUDICACION_IA_FFMPEG_PATH') ?: 'ffmpeg';
        $base = dirname(__DIR__) . '/storage/adjudicacion_ia/' . $idOperacion . '/' . substr($mediaHash, 0, 16) . '/' . $slot;
        if (!is_dir($base) && !@mkdir($base, 0770, true)) return [];
        foreach (glob($base . '/*.jpg') ?: [] as $old) @unlink($old);
        $pattern = $base . '/frame_%02d.jpg';
        $command = escapeshellarg($ffmpeg) . ' -hide_banner -loglevel error -y -i ' . escapeshellarg($video)
            . ' -vf ' . escapeshellarg('fps=1/5') . ' -frames:v 3 ' . escapeshellarg($pattern) . ' 2>&1';
        $output = [];
        $exit = 1;
        @exec($command, $output, $exit);
        if ($exit !== 0) return [];
        $frames = glob($base . '/frame_*.jpg') ?: [];
        sort($frames, SORT_NATURAL);
        return array_values(array_filter($frames, 'is_file'));
    }

    private function valorEnvKnockoutIA(string $key): string
    {
        $value = getenv($key);
        if (is_string($value) && trim($value) !== '') return trim($value);
        $envPath = dirname(__DIR__) . '/API/.env';
        if (!is_readable($envPath)) return '';
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=\s*(.*)\s*$/', $line, $m)) {
                return trim($m[1], " \t\n\r\0\x0B\"'");
            }
        }
        return '';
    }

    private function tablaKnockoutDisponible(): bool
    {
        try {
            $this->db->queryOne('SELECT id FROM adj_validacion_knockout LIMIT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** @param list<string> $motivos @param array<string,mixed> $raw */
    private function guardarResultadoKnockoutIA(int $idOperacion, string $estado, int $confianza, array $motivos, array $raw, string $modelo, ?string $mediaHash): array
    {
        $estado = strtoupper($estado);
        $mapa = [
            'BUEN_ESTADO' => ['APROBADO', 'Moto en Buen Estado', 'Procesando IA'],
            'MAL_ESTADO' => ['BLOQUEADO', 'Moto en Mal Estado', 'Bloqueado IA'],
            'REVISION_MANUAL' => ['REVISION_MANUAL', 'Revisión manual requerida', 'Revisión Recuperaciones'],
        ];
        $regla = $mapa[$estado] ?? $mapa['REVISION_MANUAL'];
        $ahora = $this->fechaHoraCdmx();
        $motivo = trim(implode(' | ', array_slice(array_map('strval', $motivos), 0, 5)));
        $this->db->CRUD(
            "UPDATE adj_validacion_knockout
                SET estado = :estado, etiqueta = :etiqueta, proveedor = 'anthropic', modelo = :modelo,
                    confianza = :confianza, motivo = :motivo, detalle_json = :detalle, media_hash = :hash,
                    intentos = intentos + 1, fecha_actualizacion = :fecha, fecha_resolucion = :fecha
              WHERE id_operacion = :id AND tipo = 'ESTADO_MOTO_CLAUDE'",
            [
                'estado' => $regla[0], 'etiqueta' => $regla[1], 'modelo' => $modelo ?: null,
                'confianza' => max(0, min(100, $confianza)), 'motivo' => $motivo ?: null,
                'detalle' => json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'hash' => $mediaHash, 'fecha' => $ahora, 'id' => $idOperacion,
            ]
        );
        $mov = $this->cambiarEstatus($idOperacion, $regla[2], 0, 'Validacion automatica Anthropic', 'knockout_ia');
        if (empty($mov['success'])) return $mov;
        $mensaje = $estado === 'MAL_ESTADO'
            ? 'No se puede Proceder con la Adjudicacion. Cualquier duda contacta a tu lider.'
            : ($estado === 'BUEN_ESTADO' ? 'Moto en Buen Estado.' : 'La moto requiere revision manual antes de continuar.');
        $this->registrarBitacora($idOperacion, 'KNOCKOUT IA: ' . $regla[1] . ($motivo !== '' ? ' - ' . $motivo : ''), 0, 'Anthropic', $ahora);
        return ['success' => true, 'estado' => $estado, 'etiqueta' => $regla[1], 'message' => $mensaje];
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
        $diag = $this->diagnosticarValidacionAtencion($idOperacion);
        if (empty($diag['completa'])) {
            $faltantes = $diag['faltantes'] ?? [];
            $detalle = $faltantes ? ' Faltan: ' . implode(', ', array_slice($faltantes, 0, 6)) . (count($faltantes) > 6 ? '...' : '') : '';
            return ['success' => false, 'message' => 'Faltan fotos/video por validar o el PDF de Repuve en expediente.' . $detalle];
        }
        $op = $this->db->queryOne('SELECT id, estatus FROM adj_operacion WHERE id = :id', ['id' => $idOperacion]);
        if (!$op) {
            return ['success' => false, 'message' => 'Operaci?n no encontrada.'];
        }
        $est = (string) ($op['estatus'] ?? '');

        if ($est === 'Validacion IA') {
            return ['success' => true, 'validacion_ia_pendiente' => true, 'message' => 'La validacion IA de la moto ya esta en cola.'];
        }

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

        $previos = ['Recibido', 'en_transito', 'Revisión Recuperaciones', 'Validacion IA', 'Procesando IA'];
        if (!in_array($est, $previos, true)) {
            return ['success' => false, 'message' => 'Esta operaci?n no est? en etapa para este paso.'];
        }
        $colaIa = $this->encolarValidacionEstadoMotoClaude($idOperacion);
        if (!empty($colaIa['enabled'])) {
            if (empty($colaIa['success'])) {
                return $colaIa;
            }
            $r = $this->cambiarEstatus($idOperacion, 'Validacion IA', $idUsuario, $nombreUsuario);
            if (empty($r['success'])) {
                return $r;
            }
            if ($this->adjOperacionTieneColumnaEnvioAtencion()) {
                $this->db->CRUD(
                    'UPDATE adj_operacion SET atencion_envio_validado = 1 WHERE id = :id',
                    ['id' => $idOperacion]
                );
            }
            $this->registrarBitacora($idOperacion, 'EVIDENCIAS ENCOLADAS PARA KNOCKOUT IA', $idUsuario, $nombreUsuario);
            return ['success' => true, 'validacion_ia_pendiente' => true, 'message' => 'Evidencias recibidas. La moto esta pendiente de validacion IA.'];
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

    private function madjFechaFiltro(?string $fecha): ?string
    {
        $fecha = trim((string) $fecha);
        if ($fecha === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return null;
        }

        return $fecha;
    }

    private function madjEstadoNormalizadoSql(string $alias = 'o'): string
    {
        $campo = $alias . '.log_estado';
        return "CASE
            WHEN TRIM(COALESCE({$campo}, '')) = '' THEN 'SIN ESTADO'
            WHEN UPPER(TRIM({$campo})) IN ('CDMX', 'CMDX', 'CIUDAD DE MEXICO', 'CIUDAD DE MÉXICO') THEN 'CIUDAD DE MEXICO'
            WHEN UPPER(TRIM({$campo})) IN ('MEXICO', 'MÉXICO', 'EDO DE MEX', 'EDO DE MEXICO', 'ESTADO DE MÉXICO') THEN 'ESTADO DE MEXICO'
            ELSE UPPER(TRIM({$campo}))
        END";
    }

    private function madjUbicacionIncompletaSql(string $alias = 'o'): string
    {
        $dir = "UPPER(TRIM(COALESCE({$alias}.log_direccion, '')))";
        $ciu = "UPPER(TRIM(COALESCE({$alias}.log_ciudad, '')))";
        $edo = "UPPER(TRIM(COALESCE({$alias}.log_estado, '')))";

        return "({$dir} IN ('', 'NA', 'N/A', 'SIN DIRECCION', 'SIN DIRECCIÓN')
            OR {$ciu} IN ('', 'NA', 'N/A')
            OR {$edo} IN ('', 'NA', 'N/A'))";
    }

    private function madjWhereFechaAlta(array $filtros, array &$params): string
    {
        $where = '1=1';
        $desde = $this->madjFechaFiltro($filtros['desde'] ?? null);
        $hasta = $this->madjFechaFiltro($filtros['hasta'] ?? null);

        if ($desde !== null) {
            $where .= ' AND DATE(o.fecha_alta) >= :desde';
            $params['desde'] = $desde;
        }
        if ($hasta !== null) {
            $where .= ' AND DATE(o.fecha_alta) <= :hasta';
            $params['hasta'] = $hasta;
        }

        return $where;
    }

    public function obtenerDashboardMotosAdjudicadas(array $filtros = []): array
    {
        $params = [];
        $where = $this->madjWhereFechaAlta($filtros, $params);
        $ubicacionIncompleta = $this->madjUbicacionIncompletaSql('o');

        $resumen = $this->db->queryOne(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(o.estatus, ''))) = 'en_transito' THEN 1 ELSE 0 END) AS en_transito,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(o.estatus, ''))) IN ('recepcion', 'recepción') THEN 1 ELSE 0 END) AS recepcion,
                SUM(CASE WHEN LOWER(TRIM(COALESCE(o.estatus, ''))) IN ('procesando ia', 'procesando_ia') THEN 1 ELSE 0 END) AS procesando_ia,
                SUM(CASE WHEN {$ubicacionIncompleta} THEN 1 ELSE 0 END) AS ubicacion_incompleta,
                SUM(CASE WHEN o.fecha_llegada_almacen IS NOT NULL THEN 1 ELSE 0 END) AS llegada_almacen,
                SUM(CASE WHEN COALESCE(o.es_validado_ia, 0) = 1 THEN 1 ELSE 0 END) AS validadas_ia,
                SUM(CASE WHEN COALESCE(o.es_validado_factura, 0) = 1 THEN 1 ELSE 0 END) AS validadas_factura,
                SUM(CASE WHEN DATE(o.fecha_alta) = CURDATE() THEN 1 ELSE 0 END) AS altas_hoy,
                SUM(CASE WHEN o.fecha_actualizacion >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS actualizadas_7d,
                AVG(NULLIF(o.dias_mora, 0)) AS promedio_dias_mora,
                SUM(COALESCE(o.saldo_capital, 0)) AS saldo_capital,
                SUM(COALESCE(o.adeudo_total, 0)) AS adeudo_total
             FROM adj_operacion o
             WHERE {$where}",
            $params
        ) ?: [];

        $porEstatus = $this->db->queryAll(
            "SELECT COALESCE(NULLIF(TRIM(o.estatus), ''), 'Sin estatus') AS label, COUNT(*) AS total
             FROM adj_operacion o
             WHERE {$where}
             GROUP BY label
             ORDER BY total DESC, label ASC
             LIMIT 12",
            $params
        ) ?: [];

        $estadoSql = $this->madjEstadoNormalizadoSql('o');
        $porEstado = $this->db->queryAll(
            "SELECT {$estadoSql} AS label, COUNT(*) AS total
             FROM adj_operacion o
             WHERE {$where}
             GROUP BY label
             ORDER BY total DESC, label ASC
             LIMIT 10",
            $params
        ) ?: [];

        $porDia = $this->db->queryAll(
            "SELECT DATE(o.fecha_alta) AS label, COUNT(*) AS total
             FROM adj_operacion o
             WHERE {$where}
               AND o.fecha_alta >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
             GROUP BY DATE(o.fecha_alta)
             ORDER BY label ASC",
            $params
        ) ?: [];

        $alertas = $this->db->queryAll(
            "SELECT alerta, total
             FROM (
                SELECT 'Ubicacion incompleta' AS alerta, SUM(CASE WHEN {$ubicacionIncompleta} THEN 1 ELSE 0 END) AS total
                FROM adj_operacion o WHERE {$where}
                UNION ALL
                SELECT 'Datos de unidad incompletos' AS alerta,
                       SUM(CASE WHEN TRIM(COALESCE(o.moto_marca, o.marca, '')) = '' OR TRIM(COALESCE(o.moto_modelo, o.modelo, '')) = '' THEN 1 ELSE 0 END) AS total
                FROM adj_operacion o WHERE {$where}
                UNION ALL
                SELECT 'Sin llegada a almacen' AS alerta,
                       SUM(CASE WHEN o.fecha_llegada_almacen IS NULL THEN 1 ELSE 0 END) AS total
                FROM adj_operacion o WHERE {$where}
             ) x
             WHERE total > 0
             ORDER BY total DESC",
            $params
        ) ?: [];

        $tracking = [
            'rutas_total' => 0,
            'rutas_activas' => 0,
            'creditos_en_ruta' => 0,
        ];
        try {
            $row = $this->db->queryOne(
                "SELECT
                    COUNT(DISTINCT atr.id_ruta) AS rutas_total,
                    COUNT(DISTINCT CASE WHEN LOWER(COALESCE(atr.estatus_ruta, '')) IN ('en_proceso', 'en proceso', 'enviada', 'operativa') THEN atr.id_ruta END) AS rutas_activas,
                    COUNT(atd.id_detalle) AS creditos_en_ruta
                 FROM asigna_horas_tracking atr
                 LEFT JOIN asigna_horas_tracking_detalle atd ON atd.id_ruta = atr.id_ruta"
            );
            if ($row) {
                $tracking = [
                    'rutas_total' => (int) ($row['rutas_total'] ?? 0),
                    'rutas_activas' => (int) ($row['rutas_activas'] ?? 0),
                    'creditos_en_ruta' => (int) ($row['creditos_en_ruta'] ?? 0),
                ];
            }
        } catch (\Throwable $e) {
            // El dashboard no debe romper si tracking aun no tiene sus tablas listas.
        }

        return [
            'resumen' => [
                'total' => (int) ($resumen['total'] ?? 0),
                'en_transito' => (int) ($resumen['en_transito'] ?? 0),
                'recepcion' => (int) ($resumen['recepcion'] ?? 0),
                'procesando_ia' => (int) ($resumen['procesando_ia'] ?? 0),
                'ubicacion_incompleta' => (int) ($resumen['ubicacion_incompleta'] ?? 0),
                'llegada_almacen' => (int) ($resumen['llegada_almacen'] ?? 0),
                'validadas_ia' => (int) ($resumen['validadas_ia'] ?? 0),
                'validadas_factura' => (int) ($resumen['validadas_factura'] ?? 0),
                'altas_hoy' => (int) ($resumen['altas_hoy'] ?? 0),
                'actualizadas_7d' => (int) ($resumen['actualizadas_7d'] ?? 0),
                'promedio_dias_mora' => round((float) ($resumen['promedio_dias_mora'] ?? 0), 1),
                'saldo_capital' => (float) ($resumen['saldo_capital'] ?? 0),
                'adeudo_total' => (float) ($resumen['adeudo_total'] ?? 0),
            ],
            'por_estatus' => $porEstatus,
            'por_estado' => $porEstado,
            'por_dia' => $porDia,
            'alertas' => $alertas,
            'tracking' => $tracking,
            'actualizado_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function obtenerReporteSeguimientoMotosAdjudicadas(array $filtros = []): array
    {
        $params = [];
        $where = $this->madjWhereFechaAlta($filtros, $params);
        $estadoSql = $this->madjEstadoNormalizadoSql('o');
        $ubicacionIncompleta = $this->madjUbicacionIncompletaSql('o');

        $estatus = trim((string) ($filtros['estatus'] ?? ''));
        if ($estatus !== '') {
            $where .= ' AND LOWER(TRIM(COALESCE(o.estatus, \'\'))) = LOWER(:estatus)';
            $params['estatus'] = $estatus;
        }

        $estado = trim((string) ($filtros['estado'] ?? ''));
        if ($estado !== '') {
            $where .= " AND {$estadoSql} = :estado";
            $params['estado'] = strtoupper($estado);
        }

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $where .= " AND (
                CAST(o.id_credito AS CHAR) LIKE :q
                OR CAST(o.id AS CHAR) LIKE :q
                OR o.folio LIKE :q
                OR o.nombre_cliente LIKE :q
                OR COALESCE(o.moto_no_serie, o.serie, '') LIKE :q
                OR COALESCE(o.moto_modelo, o.modelo, '') LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        $limit = (int) ($filtros['limit'] ?? 200);
        $limit = max(25, min(1000, $limit));

        $rows = $this->db->queryAll(
            "SELECT
                o.id,
                o.folio,
                o.id_credito,
                o.nombre_cliente,
                COALESCE(NULLIF(TRIM(o.estatus), ''), 'Sin estatus') AS estatus,
                {$estadoSql} AS estado_normalizado,
                COALESCE(NULLIF(TRIM(o.log_ciudad), ''), 'SIN MUNICIPIO') AS municipio,
                COALESCE(NULLIF(TRIM(o.log_direccion), ''), 'Sin direccion') AS direccion,
                TRIM(CONCAT_WS(' ', COALESCE(o.moto_marca, o.marca), COALESCE(o.moto_modelo, o.modelo))) AS unidad,
                COALESCE(o.moto_anio, NULL) AS anio,
                COALESCE(o.moto_color, '') AS color,
                COALESCE(o.moto_no_serie, o.serie, '') AS vin,
                COALESCE(o.moto_placas, '') AS placas,
                o.fecha_alta,
                o.fecha_actualizacion,
                o.fecha_llegada_almacen,
                o.dias_mora,
                o.saldo_capital,
                o.adeudo_total,
                CASE WHEN {$ubicacionIncompleta} THEN 1 ELSE 0 END AS ubicacion_incompleta,
                CASE WHEN TRIM(COALESCE(o.moto_marca, o.marca, '')) = '' OR TRIM(COALESCE(o.moto_modelo, o.modelo, '')) = '' THEN 1 ELSE 0 END AS unidad_incompleta
             FROM adj_operacion o
             WHERE {$where}
             ORDER BY COALESCE(o.fecha_actualizacion, o.fecha_alta) DESC, o.id DESC
             LIMIT {$limit}",
            $params
        ) ?: [];

        $estatusRows = $this->db->queryAll(
            "SELECT COALESCE(NULLIF(TRIM(o.estatus), ''), 'Sin estatus') AS label, COUNT(*) AS total
             FROM adj_operacion o
             GROUP BY label
             ORDER BY total DESC, label ASC"
        ) ?: [];

        $estadoRows = $this->db->queryAll(
            "SELECT {$estadoSql} AS label, COUNT(*) AS total
             FROM adj_operacion o
             GROUP BY label
             ORDER BY total DESC, label ASC"
        ) ?: [];

        return [
            'rows' => $rows,
            'catalogos' => [
                'estatus' => $estatusRows,
                'estados' => $estadoRows,
            ],
            'total' => count($rows),
            'limit' => $limit,
            'actualizado_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function madjHistoricoFlujoEtapas(): array
    {
        return [
            'asignacion_gestor' => [
                'key' => 'asignacion_gestor',
                'titulo' => 'Asignacion de credito al gestor',
                'descripcion' => 'Creditos que entraron a motos adjudicadas y aun no avanzan a evidencias.',
                'icon' => 'fa-user-check',
            ],
            'evidencias' => [
                'key' => 'evidencias',
                'titulo' => 'Evidencias',
                'descripcion' => 'Creditos con captura fotografica o documental registrada.',
                'icon' => 'fa-camera',
            ],
            'recuperacion' => [
                'key' => 'recuperacion',
                'titulo' => 'Recuperacion',
                'descripcion' => 'Creditos con movimientos de recuperacion o validacion operativa.',
                'icon' => 'fa-motorcycle',
            ],
            'envio_cartera' => [
                'key' => 'envio_cartera',
                'titulo' => 'Envio a cartera',
                'descripcion' => 'Creditos enviados a gestion de cartera o cierre documental.',
                'icon' => 'fa-folder-open',
            ],
            'tracking_recoleccion' => [
                'key' => 'tracking_recoleccion',
                'titulo' => 'Tracking de recoleccion',
                'descripcion' => 'Creditos agregados a rutas de recoleccion.',
                'icon' => 'fa-route',
            ],
            'recepcion' => [
                'key' => 'recepcion',
                'titulo' => 'Recepcion',
                'descripcion' => 'Creditos con llegada o recepcion confirmada en almacen/CEDIS.',
                'icon' => 'fa-warehouse',
            ],
        ];
    }

    private function madjHistoricoFlujoQuery(string $where, string $estadoSql, bool $incluirTracking): string
    {
        $trackingSelect = $incluirTracking
            ? ",
                (SELECT COUNT(*)
                 FROM asigna_horas_tracking_detalle atd
                 WHERE atd.id_credito = o.id_credito) AS tracking_total,
                (SELECT MIN(COALESCE(atd.fecha_agregado, atr.fecha_creacion))
                 FROM asigna_horas_tracking_detalle atd
                 INNER JOIN asigna_horas_tracking atr ON atr.id_ruta = atd.id_ruta
                 WHERE atd.id_credito = o.id_credito) AS fecha_tracking,
                (SELECT atr.nombre_ruta
                 FROM asigna_horas_tracking_detalle atd
                 INNER JOIN asigna_horas_tracking atr ON atr.id_ruta = atd.id_ruta
                 WHERE atd.id_credito = o.id_credito
                 ORDER BY COALESCE(atd.fecha_agregado, atr.fecha_creacion) DESC, atd.id_detalle DESC
                 LIMIT 1) AS ruta_tracking,
                (SELECT atr.estatus_ruta
                 FROM asigna_horas_tracking_detalle atd
                 INNER JOIN asigna_horas_tracking atr ON atr.id_ruta = atd.id_ruta
                 WHERE atd.id_credito = o.id_credito
                 ORDER BY COALESCE(atd.fecha_agregado, atr.fecha_creacion) DESC, atd.id_detalle DESC
                 LIMIT 1) AS estatus_tracking"
            : ",
                0 AS tracking_total,
                NULL AS fecha_tracking,
                NULL AS ruta_tracking,
                NULL AS estatus_tracking";

        return "SELECT
                o.id,
                o.folio,
                o.id_credito,
                o.nombre_cliente,
                COALESCE(NULLIF(TRIM(o.estatus), ''), 'Sin estatus') AS estatus,
                COALESCE(NULLIF(TRIM(o.area_actual), ''), '') AS origen_carga,
                {$estadoSql} AS estado_normalizado,
                COALESCE(NULLIF(TRIM(o.log_ciudad), ''), 'SIN MUNICIPIO') AS municipio,
                COALESCE(NULLIF(TRIM(o.log_direccion), ''), 'Sin direccion') AS direccion,
                TRIM(CONCAT_WS(' ', COALESCE(o.moto_marca, o.marca), COALESCE(o.moto_modelo, o.modelo))) AS unidad,
                COALESCE(o.moto_no_serie, o.serie, '') AS vin,
                COALESCE(o.moto_placas, '') AS placas,
                o.fecha_alta,
                o.fecha_actualizacion,
                o.fecha_llegada_almacen,
                o.recepcion_confirmada_at,
                (SELECT COUNT(*)
                 FROM asigna_creditos_adjudicacion aca
                 WHERE aca.id_credito = o.id_credito) AS asignaciones_total,
                (SELECT MIN(aca.fecha_alta)
                 FROM asigna_creditos_adjudicacion aca
                 WHERE aca.id_credito = o.id_credito) AS fecha_asignacion,
                COALESCE((SELECT TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom))
                 FROM asigna_creditos_adjudicacion aca
                 INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
                 INNER JOIN persona per ON per.id = pa.id_persona
                 WHERE aca.id_credito = o.id_credito
                 ORDER BY (aca.estatus = '1') DESC, aca.id DESC
                 LIMIT 1), NULLIF(TRIM(o.log_responsable), ''), '') AS gestor_nombre,
                (SELECT COUNT(*)
                 FROM adj_evidencia ev
                 WHERE ev.id_operacion = o.id) AS evidencias_total,
                (SELECT MIN(ev.fecha_alta)
                 FROM adj_evidencia ev
                 WHERE ev.id_operacion = o.id) AS fecha_evidencia,
                (SELECT COUNT(*)
                 FROM adj_historial_estatus h
                 WHERE h.id_operacion = o.id
                   AND (
                       LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%recuper%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%recibido%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%transito%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%tránsito%'
                   )) AS recuperacion_total,
                (SELECT MIN(h.fecha)
                 FROM adj_historial_estatus h
                 WHERE h.id_operacion = o.id
                   AND (
                       LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%recuper%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%recibido%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%transito%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%tránsito%'
                   )) AS fecha_recuperacion,
                (SELECT COUNT(*)
                 FROM adj_historial_estatus h
                 WHERE h.id_operacion = o.id
                   AND (
                       LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%cartera%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%document%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%cierre%'
                   )) AS cartera_total,
                (SELECT MIN(h.fecha)
                 FROM adj_historial_estatus h
                 WHERE h.id_operacion = o.id
                   AND (
                       LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%cartera%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%document%'
                       OR LOWER(COALESCE(h.estatus_nuevo, '')) LIKE '%cierre%'
                   )) AS fecha_cartera
                {$trackingSelect}
             FROM adj_operacion o
             WHERE {$where}
             ORDER BY COALESCE(o.fecha_actualizacion, o.fecha_alta) DESC, o.id DESC";
    }

    private function madjHistoricoFlujoClasificar(array $row): array
    {
        $fechaRecepcion = $row['recepcion_confirmada_at'] ?? $row['fecha_llegada_almacen'] ?? null;
        $estatus = strtolower(trim((string) ($row['estatus'] ?? '')));

        if (!empty($fechaRecepcion) || strpos($estatus, 'recepci') !== false || strpos($estatus, 'conclu') !== false) {
            return ['key' => 'recepcion', 'fecha' => $fechaRecepcion ?: ($row['fecha_actualizacion'] ?? $row['fecha_alta'] ?? null)];
        }
        if ((int) ($row['tracking_total'] ?? 0) > 0) {
            return ['key' => 'tracking_recoleccion', 'fecha' => $row['fecha_tracking'] ?? $row['fecha_actualizacion'] ?? null];
        }
        if ((int) ($row['cartera_total'] ?? 0) > 0) {
            return ['key' => 'envio_cartera', 'fecha' => $row['fecha_cartera'] ?? $row['fecha_actualizacion'] ?? null];
        }
        if ((int) ($row['recuperacion_total'] ?? 0) > 0 || strpos($estatus, 'recuper') !== false || strpos($estatus, 'transito') !== false) {
            return ['key' => 'recuperacion', 'fecha' => $row['fecha_recuperacion'] ?? $row['fecha_actualizacion'] ?? null];
        }
        if ((int) ($row['evidencias_total'] ?? 0) > 0) {
            return ['key' => 'evidencias', 'fecha' => $row['fecha_evidencia'] ?? $row['fecha_actualizacion'] ?? null];
        }

        return ['key' => 'asignacion_gestor', 'fecha' => $row['fecha_asignacion'] ?? $row['fecha_alta'] ?? null];
    }

    public function obtenerReporteHistoricoFlujoMotosAdjudicadas(array $filtros = []): array
    {
        $params = [];
        // El histórico solo muestra tickets cuyo flujo ya terminó: recepción
        // confirmada (incluye Retenciones posteriores) o cierre/cancelación explícita.
        $where = '(
            o.recepcion_confirmada_at IS NOT NULL
            OR LOWER(TRIM(COALESCE(o.estatus, \'\'))) IN (
                \'cancelado\', \'cancelada\', \'concluido\', \'concluida\',
                \'finalizado\', \'finalizada\', \'cerrado\', \'cerrada\'
            )
        )';
        $desde = $this->madjFechaFiltro($filtros['desde'] ?? null);
        $hasta = $this->madjFechaFiltro($filtros['hasta'] ?? null);
        if ($desde !== null) {
            $where .= ' AND DATE(COALESCE(o.recepcion_confirmada_at, o.fecha_actualizacion, o.fecha_alta)) >= :desde';
            $params['desde'] = $desde;
        }
        if ($hasta !== null) {
            $where .= ' AND DATE(COALESCE(o.recepcion_confirmada_at, o.fecha_actualizacion, o.fecha_alta)) <= :hasta';
            $params['hasta'] = $hasta;
        }
        $estadoSql = $this->madjEstadoNormalizadoSql('o');

        $estado = trim((string) ($filtros['estado'] ?? ''));
        if ($estado !== '') {
            $where .= " AND {$estadoSql} = :estado";
            $params['estado'] = strtoupper($estado);
        }

        $q = trim((string) ($filtros['q'] ?? ''));
        if ($q !== '') {
            $where .= " AND (
                CAST(o.id_credito AS CHAR) LIKE :q
                OR CAST(o.id AS CHAR) LIKE :q
                OR o.folio LIKE :q
                OR o.nombre_cliente LIKE :q
                OR COALESCE(o.moto_no_serie, o.serie, '') LIKE :q
                OR COALESCE(o.moto_modelo, o.modelo, '') LIKE :q
                OR COALESCE(o.moto_marca, o.marca, '') LIKE :q
            )";
            $params['q'] = '%' . $q . '%';
        }

        $limit = (int) ($filtros['limit'] ?? 100);
        $limit = max(25, min(500, $limit));

        $sql = $this->madjHistoricoFlujoQuery($where, $estadoSql, true) . " LIMIT {$limit}";
        try {
            $rows = $this->db->queryAll($sql, $params) ?: [];
            $trackingDisponible = true;
        } catch (\Throwable $e) {
            $sql = $this->madjHistoricoFlujoQuery($where, $estadoSql, false) . " LIMIT {$limit}";
            $rows = $this->db->queryAll($sql, $params) ?: [];
            $trackingDisponible = false;
        }

        $estadosConteo = [];
        $cerradosPorRecepcion = 0;
        $cerradosPorCancelacion = 0;
        $registros = [];
        foreach ($rows as $row) {
            $estadoLabel = (string) ($row['estado_normalizado'] ?? 'SIN ESTADO');
            $estadosConteo[$estadoLabel] = ($estadosConteo[$estadoLabel] ?? 0) + 1;
            $esRecepcionConfirmada = trim((string) ($row['recepcion_confirmada_at'] ?? '')) !== '';
            $fechaCierre = $esRecepcionConfirmada
                ? $row['recepcion_confirmada_at']
                : ($row['fecha_actualizacion'] ?? $row['fecha_alta'] ?? null);
            if ($esRecepcionConfirmada) {
                $cerradosPorRecepcion++;
            } else {
                $cerradosPorCancelacion++;
            }
            $registros[] = [
                'id_operacion' => (int) ($row['id'] ?? 0),
                'folio' => $row['folio'] ?? '',
                'id_credito' => (int) ($row['id_credito'] ?? 0),
                'nombre_cliente' => $row['nombre_cliente'] ?? '',
                'estatus' => $row['estatus'] ?? '',
                'origen_carga' => $row['origen_carga'] ?? '',
                'estado' => $row['estado_normalizado'] ?? '',
                'municipio' => $row['municipio'] ?? '',
                'direccion' => $row['direccion'] ?? '',
                'unidad' => trim((string) ($row['unidad'] ?? '')) ?: 'Sin unidad',
                'vin' => $row['vin'] ?? '',
                'gestor_nombre' => $row['gestor_nombre'] ?? '',
                'fecha_alta' => $row['fecha_alta'] ?? null,
                'fecha_actualizacion' => $row['fecha_actualizacion'] ?? null,
                'fecha_cierre' => $fechaCierre,
                'fecha_cierre_fmt' => $this->madjFmtFecha($fechaCierre),
                'tipo_cierre' => $esRecepcionConfirmada ? 'Recepción confirmada' : 'Cancelación / cierre',
                'evidencias_total' => (int) ($row['evidencias_total'] ?? 0),
                'tracking_total' => (int) ($row['tracking_total'] ?? 0),
                'ruta_tracking' => $row['ruta_tracking'] ?? '',
                'estatus_tracking' => $row['estatus_tracking'] ?? '',
            ];
        }

        $estadoRows = [];
        foreach ($estadosConteo as $label => $total) {
            $estadoRows[] = ['label' => $label, 'total' => $total];
        }
        usort($estadoRows, static function (array $a, array $b): int {
            return ((int) ($b['total'] ?? 0)) <=> ((int) ($a['total'] ?? 0)) ?: strcmp((string) ($a['label'] ?? ''), (string) ($b['label'] ?? ''));
        });

        return [
            'resumen' => [
                'total' => count($registros),
                'limit' => $limit,
                'tracking_disponible' => $trackingDisponible,
                'recepcion_confirmada' => $cerradosPorRecepcion,
                'cancelados_o_cerrados' => $cerradosPorCancelacion,
            ],
            'rows' => $registros,
            'catalogos' => [
                'estados' => $estadoRows,
            ],
            'actualizado_at' => date('Y-m-d H:i:s'),
        ];
    }

    private function madjFechaEvento(?string $fecha): ?int
    {
        $fecha = trim((string) $fecha);
        if ($fecha === '') {
            return null;
        }
        $ts = strtotime($fecha);
        return $ts !== false ? $ts : null;
    }

    private function madjFmtFecha(?string $fecha): string
    {
        $ts = $this->madjFechaEvento($fecha);
        return $ts ? date('d/m/Y H:i', $ts) : 'Sin fecha';
    }

    private function madjTimelineEstado(bool $done, bool $active = false): string
    {
        if ($done) {
            return 'completado';
        }
        return $active ? 'en_proceso' : 'pendiente';
    }

    private function madjTimelineOrigenLabel(string $origen): string
    {
        $origen = trim($origen);
        $labels = [
            'adj_operacion' => 'Operaciones',
            'asigna_creditos_adjudicacion' => 'Administracion',
            'adj_evidencia' => '1.- Evidencias',
            'adj_historial_estatus' => 'Flujo operativo',
            'asigna_horas_tracking_detalle' => 'Tracking Recoleccion',
            'adj_bitacora' => 'Bitacora operativa',
        ];

        return $labels[$origen] ?? ($origen !== '' ? $origen : 'Sparta');
    }

    private function madjTimelineEvidenciaTitulo(string $slotLabel, int $idCredito): string
    {
        $txt = trim($slotLabel);
        $txt = preg_replace('/^(foto|video|documento|doc)\s+(de\s+)?/iu', '', $txt) ?: $txt;
        $txt = strtr($txt, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
        ]);
        $txt = strtoupper($txt);
        $txt = preg_replace('/[^A-Z0-9]+/', '_', $txt) ?: 'EVIDENCIA';
        $txt = trim($txt, '_');
        if ($txt === '') {
            $txt = 'EVIDENCIA';
        }

        return $txt . '_' . $idCredito;
    }

    private function madjTimelineEvento(string $etapa, string $titulo, string $descripcion, ?string $fecha, string $origen, array $extra = []): array
    {
        return array_merge([
            'etapa' => $etapa,
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'fecha' => $fecha,
            'fecha_fmt' => $this->madjFmtFecha($fecha),
            'timestamp' => $this->madjFechaEvento($fecha) ?? 0,
            'origen' => $origen,
            'origen_label' => $this->madjTimelineOrigenLabel($origen),
        ], $extra);
    }

    public function obtenerTimelineCreditoMotosAdjudicadas(int $idCredito, int $idOperacion = 0): array
    {
        if ($idCredito <= 0 && $idOperacion <= 0) {
            return ['success' => false, 'message' => 'Indica un credito valido.'];
        }

        $whereOperacion = $idOperacion > 0 ? 'o.id = :id_operacion' : 'o.id_credito = :id_credito';
        $paramsOperacion = $idOperacion > 0 ? ['id_operacion' => $idOperacion] : ['id_credito' => $idCredito];

        $op = $this->db->queryOne(
            "SELECT
                o.id,
                o.folio,
                o.id_credito,
                o.nombre_cliente,
                o.estatus,
                o.area_actual,
                o.id_usuario_alta,
                o.responsable_entrega,
                o.telefono_contacto,
                o.direccion_recoleccion,
                o.log_estado,
                o.log_ciudad,
                o.log_direccion,
                o.log_lugar_resguardo,
                o.log_lugar_otro,
                o.log_responsable,
                o.log_telefono,
                o.fecha_alta,
                o.fecha_actualizacion,
                o.fecha_llegada_almacen,
                o.recepcion_ubicacion,
                o.recepcion_observaciones,
                o.recepcion_confirmada_at,
                o.kilometraje,
                o.marca AS factura_marca,
                o.modelo AS factura_modelo,
                o.serie AS factura_serie,
                o.num_motor AS factura_motor,
                COALESCE(o.moto_marca, o.marca, '') AS marca,
                COALESCE(o.moto_modelo, o.modelo, '') AS modelo,
                COALESCE(o.moto_anio, '') AS anio,
                COALESCE(o.moto_color, '') AS color,
                COALESCE(o.moto_no_serie, o.serie, '') AS vin,
                COALESCE(o.moto_no_motor, o.num_motor, '') AS motor,
                COALESCE(o.moto_placas, '') AS placas,
                o.dias_mora,
                o.saldo_capital,
                o.adeudo_total
             FROM adj_operacion o
             WHERE {$whereOperacion}
             ORDER BY o.id DESC
             LIMIT 1",
            $paramsOperacion
        );

        if (!$op) {
            return ['success' => false, 'message' => 'No se encontro una operacion para este credito.'];
        }

        $idOperacion = (int) ($op['id'] ?? 0);
        $idCredito = (int) ($op['id_credito'] ?? 0);
        $esCargaMasiva = trim((string) ($op['area_actual'] ?? '')) === 'Carga masiva historico';
        $eventos = [];
        $asignaciones = [];
        $evidencias = [];
        $historial = [];
        $bitacora = [];
        $tracking = [];

        try {
            $asignaciones = $this->db->queryAll(
                "SELECT
                    aca.id,
                    aca.estatus,
                    aca.fecha_alta,
                    TRIM(CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom)) AS gestor_nombre,
                    per.id AS id_persona,
                    per.numero_empleado
                 FROM asigna_creditos_adjudicacion aca
                 INNER JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
                 INNER JOIN persona per ON per.id = pa.id_persona
                 WHERE aca.id_credito = :id_credito
                 ORDER BY (aca.estatus = '1') DESC, aca.id DESC",
                ['id_credito' => $idCredito]
            ) ?: [];
        } catch (\Throwable $e) {
            $asignaciones = [];
        }

        try {
            $evidencias = $this->db->queryAll(
                "SELECT id, slot, estatus, fecha_alta, url,
                        CASE WHEN TRIM(COALESCE(url, '')) <> '' THEN 1 ELSE 0 END AS tiene_archivo
                 FROM adj_evidencia
                 WHERE id_operacion = :id
                 ORDER BY fecha_alta ASC, id ASC",
                ['id' => $idOperacion]
            ) ?: [];
        } catch (\Throwable $e) {
            $evidencias = [];
        }

        try {
            $historial = $this->db->queryAll(
                "SELECT id, estatus_anterior, estatus_nuevo, id_usuario, fecha
                 FROM adj_historial_estatus
                 WHERE id_operacion = :id
                 ORDER BY fecha ASC, id ASC",
                ['id' => $idOperacion]
            ) ?: [];
        } catch (\Throwable $e) {
            $historial = [];
        }

        try {
            $bitacora = $this->db->queryAll(
                "SELECT id, nombre_usuario, accion, fecha_alta
                 FROM adj_bitacora
                 WHERE id_operacion = :id
                 ORDER BY fecha_alta ASC, id ASC
                 LIMIT 200",
                ['id' => $idOperacion]
            ) ?: [];
        } catch (\Throwable $e) {
            $bitacora = [];
        }

        try {
            $tracking = $this->db->queryAll(
                "SELECT
                    atr.id_ruta,
                    atr.nombre_ruta,
                    atr.estatus_ruta,
                    atr.fecha_programada,
                    atr.fecha_finalizacion,
                    atr.fecha_creacion,
                    atr.fecha_actualizacion,
                    atr.act_hora_1,
                    atd.id_detalle,
                    atd.orden_ruta,
                    atd.estatus_recoleccion,
                    atd.estatus_confirmacion_gestor,
                    atd.fecha_agregado,
                    atd.estado,
                    atd.municipio,
                    atd.direccion,
                    tt.nombre_transportista,
                    tt.tipo_transportista,
                    cedis.nombre_agencia AS cedis_destino
                 FROM asigna_horas_tracking_detalle atd
                 INNER JOIN asigna_horas_tracking atr ON atr.id_ruta = atd.id_ruta
                 LEFT JOIN transportistas_tracking tt ON tt.id_transportista = atr.id_transportista
                 LEFT JOIN agencias_tracking cedis ON cedis.id_agencia = atr.id_cedis_destino
                 WHERE atd.id_credito = :id_credito
                 ORDER BY atr.fecha_creacion ASC, atd.orden_ruta ASC, atd.id_detalle ASC",
                ['id_credito' => $idCredito]
            ) ?: [];
        } catch (\Throwable $e) {
            $tracking = [];
        }

        $eventos[] = $this->madjTimelineEvento(
            'alta_operacion',
            $esCargaMasiva ? 'Registro incorporado por carga masiva' : 'Operacion creada en Motos Adjudicadas',
            $esCargaMasiva
                ? 'Registro historico incorporado desde el archivo de carga masiva.'
                : 'Se registro la operacion adjudicada en Sparta.',
            $op['fecha_alta'] ?? null,
            'adj_operacion'
        );

        foreach ($asignaciones as $asig) {
            $gestor = trim((string) ($asig['gestor_nombre'] ?? ''));
            $eventos[] = $this->madjTimelineEvento(
                'asignacion_gestor',
                'Credito asignado al gestor',
                $gestor !== '' ? ('Gestor: ' . $gestor) : 'Asignacion registrada.',
                $asig['fecha_alta'] ?? null,
                'asigna_creditos_adjudicacion',
                ['gestor_nombre' => $gestor, 'estatus_asignacion' => $asig['estatus'] ?? null]
            );
        }

        foreach ($evidencias as $ev) {
            $slot = trim((string) ($ev['slot'] ?? ''));
            $slotLabel = self::SLOT_LABELS[$slot] ?? ($slot !== '' ? $slot : 'Evidencia');
            $url = trim((string) ($ev['url'] ?? ''));
            $linkTitulo = $url !== '' ? $this->madjTimelineEvidenciaTitulo($slotLabel, $idCredito) : '';
            $eventos[] = $this->madjTimelineEvento(
                'evidencias',
                'Evidencia capturada',
                $slotLabel . ' - ' . ((int) ($ev['tiene_archivo'] ?? 0) === 1 ? 'con archivo' : 'sin archivo'),
                $ev['fecha_alta'] ?? null,
                'adj_evidencia',
                [
                    'slot' => $slot,
                    'slot_label' => $slotLabel,
                    'estatus_evidencia' => $ev['estatus'] ?? null,
                    'evidencia_url' => $url,
                    'evidencia_titulo' => $linkTitulo,
                ]
            );
        }

        foreach ($historial as $h) {
            $nuevo = trim((string) ($h['estatus_nuevo'] ?? ''));
            $anterior = trim((string) ($h['estatus_anterior'] ?? ''));
            $eventos[] = $this->madjTimelineEvento(
                'flujo_operativo',
                'Cambio de estatus',
                ($anterior !== '' ? $anterior . ' -> ' : '') . ($nuevo !== '' ? $nuevo : 'Sin estatus'),
                $h['fecha'] ?? null,
                'adj_historial_estatus',
                ['estatus_anterior' => $anterior, 'estatus_nuevo' => $nuevo]
            );
        }

        foreach ($tracking as $trk) {
            $eventos[] = $this->madjTimelineEvento(
                'tracking_recoleccion',
                'Credito agregado a ruta de recoleccion',
                '#' . (int) ($trk['id_ruta'] ?? 0) . ' - ' . trim((string) ($trk['nombre_ruta'] ?? 'Ruta sin nombre')),
                $trk['fecha_agregado'] ?? $trk['fecha_creacion'] ?? null,
                'asigna_horas_tracking_detalle',
                [
                    'id_ruta' => (int) ($trk['id_ruta'] ?? 0),
                    'id_detalle' => (int) ($trk['id_detalle'] ?? 0),
                    'estatus_ruta' => $trk['estatus_ruta'] ?? null,
                    'estatus_recoleccion' => $trk['estatus_recoleccion'] ?? null,
                    'transportista' => $trk['nombre_transportista'] ?? null,
                    'cedis_destino' => $trk['cedis_destino'] ?? null,
                ]
            );
        }

        foreach ($bitacora as $b) {
            $accion = $this->normalizarAdjBitacoraAccionDisplay((string) ($b['accion'] ?? ''));
            $eventos[] = $this->madjTimelineEvento(
                'bitacora',
                'Bitacora operativa',
                $accion,
                $b['fecha_alta'] ?? null,
                'adj_bitacora',
                ['usuario' => $b['nombre_usuario'] ?? null]
            );
        }

        $fechaRecepcion = $op['recepcion_confirmada_at'] ?? $op['fecha_llegada_almacen'] ?? null;
        if (!empty($fechaRecepcion)) {
            $eventos[] = $this->madjTimelineEvento(
                'recepcion',
                'Recepcion en almacen o CEDIS',
                trim((string) ($op['recepcion_ubicacion'] ?? '')) ?: 'Recepcion registrada.',
                $fechaRecepcion,
                'adj_operacion'
            );
        }

        usort($eventos, static function (array $a, array $b): int {
            return ((int) ($a['timestamp'] ?? 0)) <=> ((int) ($b['timestamp'] ?? 0));
        });

        $estatusActual = strtolower(trim((string) ($op['estatus'] ?? '')));
        $hayEvidencias = count($evidencias) > 0;
        $hayTracking = count($tracking) > 0;
        $hayRecepcion = !empty($fechaRecepcion);
        $hayRecuperacion = false;
        $hayCartera = false;
        foreach ($historial as $h) {
            $txt = strtolower(trim((string) ($h['estatus_nuevo'] ?? '')));
            if (strpos($txt, 'recuper') !== false || strpos($txt, 'recibido') !== false || strpos($txt, 'transito') !== false) {
                $hayRecuperacion = true;
            }
            if (strpos($txt, 'cartera') !== false || strpos($txt, 'document') !== false || strpos($txt, 'cierre') !== false) {
                $hayCartera = true;
            }
        }

        $etapas = [
            [
                'key' => 'asignacion_gestor',
                'titulo' => 'Asignacion de credito al gestor',
                'descripcion' => 'Primer responsable operativo del credito.',
                'estado' => $this->madjTimelineEstado($asignaciones !== [], $asignaciones === []),
                'fecha_fmt' => $asignaciones !== [] ? $this->madjFmtFecha($asignaciones[0]['fecha_alta'] ?? null) : 'Pendiente',
            ],
            [
                'key' => 'evidencias',
                'titulo' => $esCargaMasiva ? 'Evidencias de carga masiva' : 'Evidencias',
                'descripcion' => $esCargaMasiva
                    ? 'No aplica: el registro historico fue incorporado por carga masiva y no contiene archivos adjuntos.'
                    : 'Captura fotografica y documental por parte del gestor.',
                'estado' => $esCargaMasiva ? 'no_aplica' : $this->madjTimelineEstado($hayEvidencias, !$hayEvidencias && $asignaciones !== []),
                'fecha_fmt' => $esCargaMasiva ? 'Carga masiva' : ($hayEvidencias ? $this->madjFmtFecha($evidencias[0]['fecha_alta'] ?? null) : 'Pendiente'),
                'total' => count($evidencias),
            ],
            [
                'key' => 'recuperacion',
                'titulo' => 'Recuperacion',
                'descripcion' => 'Ida por el vehiculo y validacion operativa en domicilio.',
                'estado' => $this->madjTimelineEstado($hayRecuperacion, !$hayRecuperacion && $hayEvidencias),
                'fecha_fmt' => $hayRecuperacion ? 'Con movimientos registrados' : 'Pendiente',
            ],
            [
                'key' => 'envio_cartera',
                'titulo' => 'Envio a cartera',
                'descripcion' => 'Gestion de cartera y cierre documental.',
                'estado' => $this->madjTimelineEstado($hayCartera, !$hayCartera && $hayRecuperacion),
                'fecha_fmt' => $hayCartera ? 'Con movimientos registrados' : 'Pendiente',
            ],
            [
                'key' => 'tracking_recoleccion',
                'titulo' => 'Tracking de recoleccion',
                'descripcion' => 'Ruta, transportista, CEDIS y recorrido de recoleccion.',
                'estado' => $this->madjTimelineEstado($hayTracking, !$hayTracking && ($hayCartera || strpos($estatusActual, 'recepci') !== false || $estatusActual === 'en_transito')),
                'fecha_fmt' => $hayTracking ? $this->madjFmtFecha($tracking[0]['fecha_agregado'] ?? $tracking[0]['fecha_creacion'] ?? null) : 'Pendiente',
                'total' => count($tracking),
            ],
            [
                'key' => 'recepcion',
                'titulo' => 'Recepcion',
                'descripcion' => 'Confirmacion de llegada al almacen o CEDIS.',
                'estado' => $this->madjTimelineEstado($hayRecepcion, !$hayRecepcion && $hayTracking),
                'fecha_fmt' => $hayRecepcion ? $this->madjFmtFecha($fechaRecepcion) : 'Pendiente',
            ],
        ];

        return [
            'success' => true,
            'credito' => [
                'id_operacion' => $idOperacion,
                'folio' => $op['folio'] ?? '',
                'id_credito' => (int) ($op['id_credito'] ?? 0),
                'nombre_cliente' => $op['nombre_cliente'] ?? '',
                'estatus' => $op['estatus'] ?? '',
                'area_actual' => $op['area_actual'] ?? '',
                'es_carga_masiva' => $esCargaMasiva,
                'observaciones_recepcion' => $op['recepcion_observaciones'] ?? '',
                'ubicacion' => [
                    'estado' => $op['log_estado'] ?? '',
                    'municipio' => $op['log_ciudad'] ?? '',
                    'direccion' => $op['log_direccion'] ?? '',
                    'direccion_recoleccion' => $op['direccion_recoleccion'] ?? '',
                    'lugar_resguardo' => $op['log_lugar_resguardo'] ?? '',
                    'lugar_otro' => $op['log_lugar_otro'] ?? '',
                    'responsable_resguardo' => $op['log_responsable'] ?? '',
                    'telefono_resguardo' => $op['log_telefono'] ?? '',
                ],
                'unidad' => [
                    'marca' => $op['marca'] ?? '',
                    'modelo' => $op['modelo'] ?? '',
                    'anio' => $op['anio'] ?? '',
                    'color' => $op['color'] ?? '',
                    'vin' => $op['vin'] ?? '',
                    'motor' => $op['motor'] ?? '',
                    'placas' => $op['placas'] ?? '',
                    'kilometraje' => $op['kilometraje'] ?? '',
                    'factura_marca' => $op['factura_marca'] ?? '',
                    'factura_modelo' => $op['factura_modelo'] ?? '',
                    'factura_serie' => $op['factura_serie'] ?? '',
                    'factura_motor' => $op['factura_motor'] ?? '',
                ],
                'contacto' => [
                    'responsable_entrega' => $op['responsable_entrega'] ?? '',
                    'telefono_contacto' => $op['telefono_contacto'] ?? '',
                ],
                'finanzas' => [
                    'dias_mora' => $op['dias_mora'] ?? null,
                    'saldo_capital' => $op['saldo_capital'] ?? null,
                    'adeudo_total' => $op['adeudo_total'] ?? null,
                ],
                'fecha_alta_fmt' => $this->madjFmtFecha($op['fecha_alta'] ?? null),
                'fecha_actualizacion_fmt' => $this->madjFmtFecha($op['fecha_actualizacion'] ?? null),
            ],
            'etapas' => $etapas,
            'eventos' => array_values($eventos),
            'resumen' => [
                'total_eventos' => count($eventos),
                'total_evidencias' => count($evidencias),
                'total_rutas' => count($tracking),
                'total_asignaciones' => count($asignaciones),
            ],
            'actualizado_at' => date('Y-m-d H:i:s'),
        ];
    }
}
