<?php
namespace Models;

use Core\Database;

/**
 * Modelo para el módulo de Atención a Clientes (adjudicaciones).
 *
 * Tabla requerida (crear antes de usar este módulo):
 * ─────────────────────────────────────────────────────────────────────
 * CREATE TABLE adj_dictamen (
 *     id                  INT          AUTO_INCREMENT PRIMARY KEY,
 *     id_operacion        INT          NOT NULL,
 *     llamada_a           VARCHAR(100) NOT NULL DEFAULT '',
 *     numero              VARCHAR(30)  NOT NULL DEFAULT '',
 *     persona_contactada  VARCHAR(200) NOT NULL DEFAULT '',
 *     tipo_contacto       VARCHAR(100) NOT NULL DEFAULT '',
 *     resultado           VARCHAR(100) NOT NULL DEFAULT '',
 *     dictamen            VARCHAR(100) NOT NULL DEFAULT '',
 *     plataforma          VARCHAR(100) DEFAULT NULL,
 *     comentarios         TEXT         DEFAULT NULL,
 *     id_usuario          INT          DEFAULT NULL,
 *     fecha_alta          DATETIME     NOT NULL,
 *     INDEX idx_id_operacion (id_operacion)
 * );
 * ─────────────────────────────────────────────────────────────────────
 */
class AtencionClientes
{
    private $db;
    private $adjEvidenciaAtnColumnas = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    private function fechaHoraCdmx(): string
    {
        $dt = new \DateTime('now', new \DateTimeZone('America/Mexico_City'));
        return $dt->format('Y-m-d H:i:s');
    }

    private function sqlAhoraCdmxLiteral(): string
    {
        return "'" . addslashes($this->fechaHoraCdmx()) . "'";
    }

    private function sqlConteoEvidenciasFisicas(string $aliasOperacion = 'o'): string
    {
        return "(SELECT COUNT(DISTINCT e.slot)
                  FROM adj_evidencia e
                 WHERE e.id_operacion = {$aliasOperacion}.id
                   AND e.slot IN (
                        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
                        'fis_vin', 'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
                        'fis_tacometro', 'fis_video_cliente_acuerdo', 'fis_360_encendida',
                        'fis_video_vuelta_prueba', 'fis_checklist'
                   ))";
    }

    private function adjEvidenciaTieneColumnasAtn(): bool
    {
        if ($this->adjEvidenciaAtnColumnas !== null) {
            return (bool) $this->adjEvidenciaAtnColumnas;
        }

        try {
            $this->db->queryOne('SELECT val_atn FROM adj_evidencia LIMIT 1');
            $this->adjEvidenciaAtnColumnas = true;
        } catch (\Throwable $e) {
            $this->adjEvidenciaAtnColumnas = false;
        }

        return (bool) $this->adjEvidenciaAtnColumnas;
    }

    private function sqlConteosValidacionAtn(string $aliasOperacion = 'o'): string
    {
        if (!$this->adjEvidenciaTieneColumnasAtn()) {
            return <<<SQL
            0 AS evidencias_aceptadas_count,
            0 AS evidencias_rechazadas_count,
            0 AS evidencias_pendientes_count
SQL;
        }

        $slots = "(
            'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
            'fis_vin', 'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
            'fis_tacometro', 'fis_video_cliente_acuerdo', 'fis_360_encendida',
            'fis_video_vuelta_prueba', 'fis_checklist'
        )";

        return <<<SQL
            (SELECT COUNT(DISTINCT CASE WHEN e.val_atn = 1 THEN e.slot ELSE NULL END)
               FROM adj_evidencia e
              WHERE e.id_operacion = {$aliasOperacion}.id
                AND e.slot IN {$slots}) AS evidencias_aceptadas_count,
            (SELECT COUNT(DISTINCT CASE WHEN e.val_atn = 2 THEN e.slot ELSE NULL END)
               FROM adj_evidencia e
              WHERE e.id_operacion = {$aliasOperacion}.id
                AND e.slot IN {$slots}) AS evidencias_rechazadas_count,
            (SELECT COUNT(DISTINCT CASE WHEN IFNULL(e.val_atn, 0) NOT IN (1, 2) THEN e.slot ELSE NULL END)
               FROM adj_evidencia e
              WHERE e.id_operacion = {$aliasOperacion}.id
                AND e.slot IN {$slots}) AS evidencias_pendientes_count
SQL;
    }

    private function sqlUltimoMovimientoEvidencias(string $aliasOperacion = 'o'): string
    {
        $whereAcciones = "(
            bu.accion LIKE '%VALIDACIÓN EVIDENCIA%'
            OR bu.accion LIKE '%VALIDACION EVIDENCIA%'
            OR bu.accion LIKE '%ENVIÓ EVIDENCIAS%'
            OR bu.accion LIKE '%ENVIO EVIDENCIAS%'
            OR bu.accion LIKE '%REGISTRO RECHAZOS EVIDENCIAS%'
            OR bu.accion LIKE '%REEMPLAZO ESPECIAL DE EVIDENCIA%'
            OR bu.accion LIKE '%SUBIÓ EVIDENCIA%'
            OR bu.accion LIKE '%SUBIO EVIDENCIA%'
        )";

        return <<<SQL
            (
                SELECT DATE_FORMAT(bu.fecha_alta, '%d/%m/%Y %H:%i')
                FROM adj_bitacora bu
                WHERE bu.id_operacion = {$aliasOperacion}.id
                  AND {$whereAcciones}
                ORDER BY bu.fecha_alta DESC, bu.id DESC
                LIMIT 1
            ) AS fecha_ultimo_movimiento_evidencias,
            (
                SELECT bu.accion
                FROM adj_bitacora bu
                WHERE bu.id_operacion = {$aliasOperacion}.id
                  AND {$whereAcciones}
                ORDER BY bu.fecha_alta DESC, bu.id DESC
                LIMIT 1
            ) AS accion_ultimo_movimiento_evidencias
SQL;
    }

    private function sqlTiempoEnBandejaEvidencias(string $aliasOperacion = 'o'): string
    {
        $ahoraCdmx = $this->sqlAhoraCdmxLiteral();

        return <<<SQL
            (
                SELECT DATE_FORMAT(MIN(bt.fecha_alta), '%d/%m/%Y %H:%i')
                FROM adj_bitacora bt
                WHERE bt.id_operacion = {$aliasOperacion}.id
                  AND bt.accion LIKE '%AL PIPELINE%'
            ) AS fecha_entrada_bandeja_evidencias,
            (
                SELECT GREATEST(0, TIMESTAMPDIFF(MINUTE, MIN(bt.fecha_alta), {$ahoraCdmx}))
                FROM adj_bitacora bt
                WHERE bt.id_operacion = {$aliasOperacion}.id
                  AND bt.accion LIKE '%AL PIPELINE%'
            ) AS minutos_en_bandeja_evidencias
SQL;
    }

    private function sqlTiempoEnCorrecciones(string $aliasOperacion = 'o'): string
    {
        $ahoraCdmx = $this->sqlAhoraCdmxLiteral();

        return <<<SQL
            (
                SELECT DATE_FORMAT(MAX(ht.fecha), '%d/%m/%Y %H:%i')
                FROM adj_historial_estatus ht
                WHERE ht.id_operacion = {$aliasOperacion}.id
                  AND ht.estatus_nuevo = 'Revisión Recuperaciones'
            ) AS fecha_entrada_bandeja_evidencias,
            (
                SELECT GREATEST(0, TIMESTAMPDIFF(MINUTE, MAX(ht.fecha), {$ahoraCdmx}))
                FROM adj_historial_estatus ht
                WHERE ht.id_operacion = {$aliasOperacion}.id
                  AND ht.estatus_nuevo = 'Revisión Recuperaciones'
            ) AS minutos_en_bandeja_evidencias
SQL;
    }

    private function sqlTiempoTotalValidacionEvidencias(string $aliasOperacion = 'o'): string
    {
        return <<<SQL
            (
                SELECT DATE_FORMAT(MIN(bt.fecha_alta), '%d/%m/%Y %H:%i')
                FROM adj_bitacora bt
                WHERE bt.id_operacion = {$aliasOperacion}.id
                  AND bt.accion LIKE '%AL PIPELINE%'
            ) AS fecha_inicio_validacion_evidencias,
            (
                SELECT DATE_FORMAT(MAX(bv.fecha_alta), '%d/%m/%Y %H:%i')
                FROM adj_bitacora bv
                WHERE bv.id_operacion = {$aliasOperacion}.id
                  AND bv.accion LIKE '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%'
            ) AS fecha_fin_validacion_evidencias,
            (
                SELECT TIMESTAMPDIFF(MINUTE, MIN(bt.fecha_alta), MAX(bv.fecha_alta))
                FROM adj_bitacora bt
                INNER JOIN adj_bitacora bv
                        ON bv.id_operacion = bt.id_operacion
                       AND bv.accion LIKE '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%'
                WHERE bt.id_operacion = {$aliasOperacion}.id
                  AND bt.accion LIKE '%AL PIPELINE%'
            ) AS minutos_total_validacion_evidencias
SQL;
    }

    private function sqlTiempoEnBandejaRecuperacion(string $aliasOperacion = 'o'): string
    {
        $ahoraCdmx = $this->sqlAhoraCdmxLiteral();

        return <<<SQL
            (
                SELECT DATE_FORMAT(MAX(br.fecha_alta), '%d/%m/%Y %H:%i')
                FROM adj_bitacora br
                WHERE br.id_operacion = {$aliasOperacion}.id
                  AND br.accion LIKE '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%'
            ) AS fecha_entrada_recuperacion,
            (
                SELECT GREATEST(0, TIMESTAMPDIFF(MINUTE, MAX(br.fecha_alta), {$ahoraCdmx}))
                FROM adj_bitacora br
                WHERE br.id_operacion = {$aliasOperacion}.id
                  AND br.accion LIKE '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%'
            ) AS minutos_en_recuperacion
SQL;
    }

    private function sqlTiempoTotalRecuperacion(string $aliasOperacion = 'o', string $aliasDictamen = 'd'): string
    {
        return <<<SQL
            (
                SELECT DATE_FORMAT(MAX(br.fecha_alta), '%d/%m/%Y %H:%i')
                FROM adj_bitacora br
                WHERE br.id_operacion = {$aliasOperacion}.id
                  AND br.accion LIKE '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%'
            ) AS fecha_inicio_recuperacion,
            DATE_FORMAT(
                COALESCE(
                    {$aliasDictamen}.fecha_alta,
                    (
                        SELECT MAX(hr.fecha)
                        FROM adj_historial_estatus hr
                        WHERE hr.id_operacion = {$aliasOperacion}.id
                          AND hr.estatus_nuevo = 'Cierre Documentado'
                    )
                ),
                '%d/%m/%Y %H:%i'
            ) AS fecha_fin_recuperacion,
            TIMESTAMPDIFF(
                MINUTE,
                (
                    SELECT MAX(br.fecha_alta)
                    FROM adj_bitacora br
                    WHERE br.id_operacion = {$aliasOperacion}.id
                      AND br.accion LIKE '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%'
                ),
                COALESCE(
                    {$aliasDictamen}.fecha_alta,
                    (
                        SELECT MAX(hr.fecha)
                        FROM adj_historial_estatus hr
                        WHERE hr.id_operacion = {$aliasOperacion}.id
                          AND hr.estatus_nuevo = 'Cierre Documentado'
                    )
                )
            ) AS minutos_total_recuperacion
SQL;
    }

    private function sqlTiempoEnCierreDocumentacion(string $aliasOperacion = 'o'): string
    {
        $ahoraCdmx = $this->sqlAhoraCdmxLiteral();
        $inicio = "COALESCE(
            (
                SELECT MAX(hc.fecha)
                FROM adj_historial_estatus hc
                WHERE hc.id_operacion = {$aliasOperacion}.id
                  AND hc.estatus_nuevo = 'Cierre Documentado'
            ),
            {$aliasOperacion}.fecha_actualizacion,
            {$aliasOperacion}.fecha_alta
        )";

        return <<<SQL
            DATE_FORMAT({$inicio}, '%d/%m/%Y %H:%i') AS fecha_entrada_cierre_documentacion,
            GREATEST(0, TIMESTAMPDIFF(MINUTE, {$inicio}, {$ahoraCdmx})) AS minutos_en_cierre_documentacion
SQL;
    }

    private function sqlTiempoTotalCierreDocumentacion(string $aliasOperacion = 'o', string $aliasDictamen = 'd'): string
    {
        $inicio = "COALESCE(
            (
                SELECT MAX(hc.fecha)
                FROM adj_historial_estatus hc
                WHERE hc.id_operacion = {$aliasOperacion}.id
                  AND hc.estatus_nuevo = 'Cierre Documentado'
            ),
            {$aliasOperacion}.fecha_actualizacion,
            {$aliasOperacion}.fecha_alta
        )";

        return <<<SQL
            DATE_FORMAT({$inicio}, '%d/%m/%Y %H:%i') AS fecha_inicio_cierre_documentacion,
            DATE_FORMAT({$aliasDictamen}.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_fin_cierre_documentacion,
            GREATEST(0, TIMESTAMPDIFF(MINUTE, {$inicio}, {$aliasDictamen}.fecha_alta)) AS minutos_total_cierre_documentacion
SQL;
    }

    private function sqlConteoEvidenciasCartera(string $aliasOperacion = 'o'): string
    {
        return "(SELECT COUNT(DISTINCT e.slot)
                  FROM adj_evidencia e
                 WHERE e.id_operacion = {$aliasOperacion}.id
                   AND e.slot IN (
                        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
                        'fis_vin', 'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
                        'fis_tacometro', 'fis_video_cliente_acuerdo', 'fis_360_encendida',
                        'fis_video_vuelta_prueba', 'fis_checklist',
                        'doc_repuve', 'doc_cierre_s2'
                   ))";
    }

    private function sqlConteoExpedienteRecuperacion(string $aliasOperacion = 'o'): string
    {
        return "(SELECT COUNT(DISTINCT e.slot)
                  FROM adj_evidencia e
                 WHERE e.id_operacion = {$aliasOperacion}.id
                   AND e.slot IN (
                        'fis_dacion_hoja_1', 'fis_dacion_hoja_2',
                        'fis_vin', 'fis_frontal', 'fis_lateral_der', 'fis_trasera', 'fis_lateral_izq',
                        'fis_tacometro', 'fis_video_cliente_acuerdo', 'fis_360_encendida',
                        'fis_video_vuelta_prueba', 'fis_checklist',
                        'doc_repuve', 'doc_factura'
                   ))";
    }

    private function sqlNombreGestorConFallback(string $aliasOperacion = 'o'): string
    {
        return "COALESCE(
                    NULLIF(TRIM(CONCAT_WS(' ',
                        per.nombres,
                        per.segundo_nombre,
                        per.apellidop,
                        per.apellidom
                    )), ''),
                    NULLIF(TRIM({$aliasOperacion}.responsable_entrega), '')
                )";
    }

    private function sqlSelectFormularioEvidencias(string $aliasOperacion = 'o'): string
    {
        $a = $aliasOperacion;

        return <<<SQL
            {$a}.moto_marca,
            {$a}.moto_modelo,
            {$a}.moto_anio,
            {$a}.moto_color,
            {$a}.moto_no_serie,
            {$a}.moto_no_motor,
            {$a}.moto_placas,
            {$a}.log_direccion,
            {$a}.log_ciudad,
            {$a}.log_estado,
            {$a}.log_lugar_resguardo,
            {$a}.log_lugar_otro,
            {$a}.log_telefono,
            {$a}.responsable_entrega,
            DATE_FORMAT({$a}.datos_moto_at, '%d/%m/%Y %H:%i') AS datos_moto_fecha
SQL;
    }

    /**
     * Evita duplicar filas si hay más de una asignación por crédito.
     * Prioriza la activa; si ya no existe una activa, conserva la última histórica para no perder el gestor en etapas posteriores.
     */
    private function sqlJoinUnaAsignacionActivaPorCredito(): string
    {
        return <<<'SQL'
LEFT JOIN asigna_creditos_adjudicacion aca
       ON aca.id = (
            SELECT a2.id
            FROM asigna_creditos_adjudicacion a2
            WHERE a2.id_credito = o.id_credito
            ORDER BY
                CASE WHEN a2.estatus = '1' THEN 0 ELSE 1 END,
                a2.fecha_alta DESC,
                a2.id DESC
            LIMIT 1
        )
LEFT JOIN personal_adjudicacion pa ON pa.id = aca.id_personal_adj
LEFT JOIN persona per              ON per.id = pa.id_persona
SQL;
    }

    /**
     * Último adj_dictamen por operación (mismo orden que en Cierre documentación — Dictaminado).
     *
     * @param string $aliasDictamen Alias de adj_dictamen en el SELECT exterior (p. ej. "d").
     */
    private function sqlJoinUltimoDictamenPorOperacion(string $aliasDictamen = 'd'): string
    {
        $a = $aliasDictamen;

        return <<<SQL
INNER JOIN adj_dictamen {$a} ON {$a}.id = (
    SELECT d2.id
    FROM adj_dictamen d2
    WHERE d2.id_operacion = o.id
    ORDER BY
        CASE WHEN TRIM(COALESCE(d2.dictamen, '')) = '' THEN 1 ELSE 0 END,
        d2.fecha_alta DESC,
        d2.id DESC
    LIMIT 1
)
SQL;
    }

    /**
     * Mismo subconjunto operación+dictamen que la pestaña «Dictaminado» de 4.- Cierre documentación.
     */
    private function sqlWhereComoDictaminadoCierreDocumentacion(): string
    {
        return <<<'SQL'
WHERE (
    o.estatus IN ('Cierre Documentado', 'Recepción')
    OR EXISTS (
        SELECT 1
        FROM adj_historial_estatus h
        WHERE h.id_operacion = o.id
          AND h.estatus_nuevo IN ('Cierre Documentado', 'Recepción')
    )
)
  AND (
    o.estatus <> 'Cierre Documentado'
    OR TRIM(COALESCE(d.tipo_contacto, '')) = 'Cierre documentación'
)
SQL;
    }

    private function contarQuery(string $sql, array $params = []): int
    {
        $row = $this->db->queryOne($sql, $params);

        return (int) ($row['total'] ?? 0);
    }

    private function contarOperacionesPorEstatus(string $estatus): int
    {
        return $this->contarQuery(
            'SELECT COUNT(*) AS total FROM adj_operacion WHERE estatus = :estatus',
            ['estatus' => $estatus]
        );
    }

    private function sqlWhereBandejaEvidencias(): string
    {
        return <<<'SQL'
(
      o.estatus IN ('Recibido', 'en_transito')
      OR (
          o.estatus = 'Procesando IA'
          AND NOT EXISTS (
              SELECT 1
              FROM adj_bitacora bv
              WHERE bv.id_operacion = o.id
                AND bv.accion LIKE :pat_validadas
          )
      )
)
  AND EXISTS (
      SELECT 1
      FROM adj_bitacora b
      WHERE b.id_operacion = o.id
        AND b.accion LIKE '%AL PIPELINE%'
  )
SQL;
    }

    private function sqlWhereAprobadosEvidenciasAtencion(): string
    {
        return <<<'SQL'
o.estatus = 'Procesando IA'
AND EXISTS (
      SELECT 1
      FROM adj_bitacora b
      WHERE b.id_operacion = o.id
        AND b.accion LIKE :pat_validadas
  )
SQL;
    }

    /**
     * Dictamen registrado desde 1.- Retenciones (modal de llamada), no el de Cierre S2 ni otros módulos.
     *
     * @param string $alias Prefijo de tabla.columna (p. ej. "d2", "dr")
     */
    private function sqlEsDictamenLlamadaRetenciones(string $alias): string
    {
        return <<<SQL
(
    {$alias}.tipo_contacto IN ('Contacto', 'Sin contacto')
    OR (
        ({$alias}.tipo_contacto IS NULL OR TRIM({$alias}.tipo_contacto) = '')
        AND {$alias}.dictamen IN (
            'Autorizado para recolección',
            'Cancelado, promesa de pago',
            'Pendiente de contacto',
            'No localizado'
        )
    )
)
SQL;
    }

    // =========================================================================
    // ENTRANTES  (estatus = 'Retenciones')
    // =========================================================================

    public function obtenerEntrantes(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $esRet    = $this->sqlEsDictamenLlamadaRetenciones('dr');
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE NOT EXISTS (
              SELECT 1
              FROM adj_dictamen dr
              WHERE dr.id_operacion = o.id
                AND {$esRet}
          )
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    // =========================================================================
    // 2.- EVIDENCIAS — listas por estatus de pipeline
    // =========================================================================

    /**
     * Lista operaciones por estatus de pipeline (shape uniforme para evidencias y recuperación).
     */
    private function listarOperacionesAdjPorEstatus(string $estatus): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $evidenciasCount = $this->sqlConteoEvidenciasFisicas('o');
        $evidenciasValidacion = $this->sqlConteosValidacionAtn('o');
        $ultimoMovimientoEvidencias = $this->sqlUltimoMovimientoEvidencias('o');
        $tiempoEnBandeja = $this->sqlTiempoEnCorrecciones('o');
        $formulario = $this->sqlSelectFormularioEvidencias('o');
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            {$evidenciasCount} AS evidencias_count,
            {$evidenciasValidacion},
            {$ultimoMovimientoEvidencias},
            {$tiempoEnBandeja},
            {$formulario},
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(o.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen_legacy,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE o.estatus = :estatus
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql, ['estatus' => $estatus]) ?: [];
    }

    /**
     * Pestaña Bandeja de entrada (Evidencias): solo operaciones que ya pasaron por
     * «Enviar evidencias» en Mis adjudicaciones (bitácora ENVIÓ EVIDENCIAS AL PIPELINE).
     * Hasta entonces no deben aparecer aquí aunque estén en Recibido / en tránsito / etc.
     */
    public function obtenerRecibidos(bool $sincronizarDictums = false): array
    {
        if ($sincronizarDictums) {
            $ma = new MotosAdjudicadas();
            $ma->sincronizarDictumsAppPendientes(true, true);
        }

        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $where = $this->sqlWhereBandejaEvidencias();
        $evidenciasCount = $this->sqlConteoEvidenciasFisicas('o');
        $evidenciasValidacion = $this->sqlConteosValidacionAtn('o');
        $ultimoMovimientoEvidencias = $this->sqlUltimoMovimientoEvidencias('o');
        $tiempoEnBandeja = $this->sqlTiempoEnBandejaEvidencias('o');
        $formulario = $this->sqlSelectFormularioEvidencias('o');
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            {$evidenciasCount} AS evidencias_count,
            {$evidenciasValidacion},
            {$ultimoMovimientoEvidencias},
            {$tiempoEnBandeja},
            {$formulario},
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(o.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen_legacy,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE {$where}
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql, ['pat_validadas' => '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%']) ?: [];
    }

    /**
     * Misma lista que Evidencias → Aprobados: Procesando IA tras «Enviar evidencias validadas».
     * Si $excluirDictaminadoRecuperacion: no devuelve filas que ya salen en 3.- Recuperación → Dictaminado
     * (Cierre documentado + dictamen, p. ej. ya enviadas a cartera o en Recepción con historial).
     */
    private function listarOperacionesAprobadasEvidenciasAtencion(bool $excluirDictaminadoRecuperacion = false): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $evidenciasCount = $this->sqlConteoEvidenciasFisicas('o');
        $recuperacionExpedienteCount = $this->sqlConteoExpedienteRecuperacion('o');
        $evidenciasValidacion = $this->sqlConteosValidacionAtn('o');
        $ultimoMovimientoEvidencias = $this->sqlUltimoMovimientoEvidencias('o');
        $tiempoTotalValidacion = $this->sqlTiempoTotalValidacionEvidencias('o');
        $tiempoEnRecuperacion = $this->sqlTiempoEnBandejaRecuperacion('o');
        $formulario = $this->sqlSelectFormularioEvidencias('o');
        $exclDictRec = '';
        if ($excluirDictaminadoRecuperacion) {
            $exclDictRec = <<<'SQL'

            AND o.estatus = 'Procesando IA'
            AND NOT (
                (
                    o.estatus = 'Cierre Documentado'
                    OR EXISTS (
                        SELECT 1
                        FROM adj_historial_estatus h_cierre
                        WHERE h_cierre.id_operacion = o.id
                          AND h_cierre.estatus_nuevo = 'Cierre Documentado'
                    )
                )
                AND EXISTS (
                    SELECT 1 FROM adj_dictamen ddr WHERE ddr.id_operacion = o.id
                )
            )
SQL;
        }

        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            {$evidenciasCount} AS evidencias_count,
            {$recuperacionExpedienteCount} AS recuperacion_expediente_count,
            {$evidenciasValidacion},
            {$ultimoMovimientoEvidencias},
            {$tiempoTotalValidacion},
            {$tiempoEnRecuperacion},
            {$formulario},
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(o.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen_legacy,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion,
            (
                SELECT DATE_FORMAT(MAX(bv.fecha_alta), '%d/%m/%Y %H:%i')
                FROM adj_bitacora bv
                WHERE bv.id_operacion = o.id
                  AND bv.accion LIKE :pat_validadas
            ) AS fecha_aprobacion_evidencias,
            (
                SELECT MAX(bv.fecha_alta)
                FROM adj_bitacora bv
                WHERE bv.id_operacion = o.id
                  AND bv.accion LIKE :pat_validadas
            ) AS fecha_aprobacion_evidencias_orden
        FROM adj_operacion o
        {$joinAsig}
        WHERE {$this->sqlWhereAprobadosEvidenciasAtencion()}
          {$exclDictRec}
        ORDER BY fecha_aprobacion_evidencias_orden DESC, o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql, ['pat_validadas' => '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%']) ?: [];
    }

    /**
     * Pestaña Aprobados — solo operaciones enviadas con «Enviar evidencias validadas».
     * La bitácora es la fuente estable del envío validado.
     */
    private function sqlExclusionDictaminadoRecuperacion(): string
    {
        return <<<'SQL'

            AND NOT (
                (
                    o.estatus = 'Cierre Documentado'
                    OR EXISTS (
                        SELECT 1
                        FROM adj_historial_estatus h_cierre
                        WHERE h_cierre.id_operacion = o.id
                          AND h_cierre.estatus_nuevo = 'Cierre Documentado'
                    )
                )
                AND EXISTS (
                    SELECT 1 FROM adj_dictamen ddr WHERE ddr.id_operacion = o.id
                )
            )
SQL;
    }

    private function contarRecibidosEvidencias(): int
    {
        $sql = <<<SQL
        SELECT COUNT(*) AS total
        FROM adj_operacion o
        WHERE {$this->sqlWhereBandejaEvidencias()}
        SQL;

        return $this->contarQuery($sql, ['pat_validadas' => '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%']);
    }

    private function contarOperacionesAprobadasEvidenciasAtencion(bool $excluirDictaminadoRecuperacion = false): int
    {
        $exclDictRec = $excluirDictaminadoRecuperacion ? $this->sqlExclusionDictaminadoRecuperacion() : '';
        $estatusActualRecuperacion = $excluirDictaminadoRecuperacion ? "AND o.estatus = 'Procesando IA'" : '';

        $sql = <<<SQL
        SELECT COUNT(*) AS total
        FROM adj_operacion o
        WHERE {$this->sqlWhereAprobadosEvidenciasAtencion()}
          {$estatusActualRecuperacion}
          {$exclDictRec}
        SQL;

        return $this->contarQuery($sql, ['pat_validadas' => '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%']);
    }

    private function contarDictaminadosRecuperacion(): int
    {
        $sql = <<<'SQL'
        SELECT COUNT(*) AS total
        FROM adj_operacion o
        WHERE (
            o.estatus = 'Cierre Documentado'
            OR EXISTS (
                SELECT 1
                FROM adj_historial_estatus h
                WHERE h.id_operacion = o.id
                  AND h.estatus_nuevo = 'Cierre Documentado'
            )
        )
        SQL;

        return $this->contarQuery($sql);
    }

    private function contarDictaminadosCierreDocumentacion(): int
    {
        $joinUltimo = $this->sqlJoinUltimoDictamenPorOperacion('d');
        $whereDict  = $this->sqlWhereComoDictaminadoCierreDocumentacion();
        $sql        = <<<SQL
        SELECT COUNT(*) AS total
        FROM adj_operacion o
        {$joinUltimo}
        {$whereDict}
        SQL;

        return $this->contarQuery($sql);
    }

    private function contarOperacionesDictamenPorEstatusPipeline(string $estatus, bool $excluirRecepcionSinConfirmarAlmacen = false): int
    {
        $extraRecepcion = '';
        if ($excluirRecepcionSinConfirmarAlmacen && trim($estatus) === 'Recepción') {
            $ma = new MotosAdjudicadas();
            if ($ma->adjOperacionTieneColumnasRecepcionConfirmacion()) {
                $extraRecepcion = <<<'SQL'
 AND NOT (
    o.estatus = 'Recepción'
    AND o.recepcion_confirmada_at IS NULL
)
SQL;
            }
        }

        $sql = <<<SQL
        SELECT COUNT(*) AS total
        FROM adj_operacion o
        WHERE (
            o.estatus = :estatus_actual
            OR EXISTS (
                SELECT 1
                FROM adj_historial_estatus h
                WHERE h.id_operacion = o.id
                  AND h.estatus_nuevo = :estatus_hist
            )
        )
          AND EXISTS (
              SELECT 1
              FROM adj_dictamen d
              WHERE d.id_operacion = o.id
          )
        {$extraRecepcion}
        SQL;

        return $this->contarQuery($sql, [
            'estatus_actual' => $estatus,
            'estatus_hist'   => $estatus,
        ]);
    }

    public function obtenerEvidenciasAprobadas(): array
    {
        return $this->listarOperacionesAprobadasEvidenciasAtencion();
    }

    /**
     * Pestaña Correcciones — operaciones en etapa Revisión Recuperaciones.
     */
    public function obtenerEvidenciasCorrecciones(): array
    {
        return $this->listarOperacionesAdjPorEstatus('Revisión Recuperaciones');
    }

    /**
     * Conteos para badges en pestañas de 2.- Evidencias (misma regla que obtenerRecibidos / Aprobados / Correcciones).
     *
     * @return array{bandeja: int, aprobados: int, correcciones: int}
     */
    public function obtenerConteosPestanasEvidencias(): array
    {
        $sql = <<<SQL
        SELECT
            (
                SELECT COUNT(*)
                FROM adj_operacion o
                WHERE {$this->sqlWhereBandejaEvidencias()}
            ) AS bandeja,
            (
                SELECT COUNT(*)
                FROM adj_operacion o
                WHERE {$this->sqlWhereAprobadosEvidenciasAtencion()}
            ) AS aprobados,
            (
                SELECT COUNT(*)
                FROM adj_operacion o
                WHERE o.estatus = :estatus_correcciones
            ) AS correcciones
        SQL;

        $row = $this->db->queryOne($sql, [
            'pat_validadas'        => '%EVIDENCIAS VALIDADAS (PROCESANDO IA)%',
            'estatus_correcciones' => 'Revisión Recuperaciones',
        ]) ?: [];

        return [
            'bandeja'      => (int) ($row['bandeja'] ?? 0),
            'aprobados'    => (int) ($row['aprobados'] ?? 0),
            'correcciones' => (int) ($row['correcciones'] ?? 0),
        ];
    }

    /**
     * @return array{bandeja: int, dictaminado: int}
     */
    public function obtenerConteosPestanasRecuperacion(): array
    {
        return [
            'bandeja'      => $this->contarOperacionesAprobadasEvidenciasAtencion(true),
            'dictaminado'  => $this->contarDictaminadosRecuperacion(),
        ];
    }

    /**
     * @return array{bandeja: int, dictaminado: int}
     */
    public function obtenerConteosPestanasCierreDocumentacion(): array
    {
        return [
            'bandeja'      => $this->contarOperacionesPorEstatus('Cierre Documentado'),
            'dictaminado'  => $this->contarDictaminadosCierreDocumentacion(),
        ];
    }

    /**
     * @return array{bandeja: int, dictaminado: int}
     */
    public function obtenerConteosPestanasRecepcion(): array
    {
        return [
            'bandeja'      => $this->contarDictaminadosCierreDocumentacion(),
            'dictaminado'  => $this->contarOperacionesDictamenPorEstatusPipeline('Recepción', true),
        ];
    }

    /**
     * 4.- Cierre documentación — Bandeja de entrada: operaciones en etapa Cierre documentado
     * pendientes de trabajo en esta vista (p. ej. llegadas desde Recuperación).
     *
     * No se excluye por tener dictamen antiguo de Retenciones u otras etapas: ese registro no es
     * el dictamen de “Cierre documentación” (confirmación S2); la pestaña Dictaminado filtra aparte.
     */
    public function obtenerRecuperacionCierreDocumentado(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $evidenciasCount = $this->sqlConteoEvidenciasCartera('o');
        $gestorNombre = $this->sqlNombreGestorConFallback('o');
        $tiempoEnCierre = $this->sqlTiempoEnCierreDocumentacion('o');
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            {$evidenciasCount} AS evidencias_count,
            {$tiempoEnCierre},
            {$gestorNombre} AS gestor_nombre,
            DATE_FORMAT(o.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_gestion_legacy,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE o.estatus = 'Cierre Documentado'
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    /**
     * 5.- Recepción — Bandeja de entrada: mismas operaciones que en 4.- Cierre documentación — Dictaminado
     * (confirmación S2 / tipo «Cierre documentación» o ya en Recepción con historial de esa etapa).
     */
    public function obtenerRecuperacionRecepcion(): array
    {
        $joinAsig   = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $joinUltimo = $this->sqlJoinUltimoDictamenPorOperacion('d');
        $whereDict  = $this->sqlWhereComoDictaminadoCierreDocumentacion();
        $evidenciasCount = $this->sqlConteoEvidenciasCartera('o');
        $gestorNombre = $this->sqlNombreGestorConFallback('o');
        $tiempoTotalCierre = $this->sqlTiempoTotalCierreDocumentacion('o', 'd');
        $sql        = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.telefono_contacto,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            DATEDIFF(NOW(), o.fecha_alta) AS dias_en_pipeline,
            {$evidenciasCount} AS evidencias_count,
            {$gestorNombre} AS gestor_nombre,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinUltimo}
        {$joinAsig}
        {$whereDict}
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    /**
     * 3.- Recuperación — Bandeja de entrada: mismas operaciones que Evidencias → Aprobados
     * (Procesando IA con envío validado), excluyendo las que ya están en Dictaminado de esta vista.
     */
    public function obtenerRecuperacionEnTransito(): array
    {
        return $this->listarOperacionesAprobadasEvidenciasAtencion(true);
    }

    /**
     * Pestaña Dictaminado (p. ej. 5.- Recepción): operaciones con dictamen que están o estuvieron
     * en la etapa del pipeline indicada (no desaparecen al avanzar de menú).
     *
     * @param  bool  $excluirRecepcionSinConfirmarAlmacen  Si es true y $estatus es «Recepción», no devuelve
     *          filas que sigan en etapa Recepción pendientes de confirmación en almacén (evita duplicar con
     *          la bandeja: el dictamen de S2 no cuenta como «dictaminado» de recepción hasta confirmar).
     */
    public function obtenerOperacionesDictamenPorEstatusPipeline(string $estatus, bool $excluirRecepcionSinConfirmarAlmacen = false): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $evidenciasCount = $this->sqlConteoEvidenciasCartera('o');
        $gestorNombre = $this->sqlNombreGestorConFallback('o');

        $extraRecepcion = '';
        if ($excluirRecepcionSinConfirmarAlmacen && trim($estatus) === 'Recepción') {
            $ma = new MotosAdjudicadas();
            if ($ma->adjOperacionTieneColumnasRecepcionConfirmacion()) {
                $extraRecepcion = <<<'SQL'
 AND NOT (
    o.estatus = 'Recepción'
    AND o.recepcion_confirmada_at IS NULL
)
SQL;
            }
        }

        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            d.llamada_a,
            d.numero,
            d.persona_contactada,
            d.tipo_contacto,
            d.resultado,
            d.dictamen,
            d.plataforma,
            d.comentarios,
            {$evidenciasCount} AS evidencias_count,
            {$tiempoTotalCierre},
            DATE_FORMAT(o.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_gestion_legacy,
            DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen,
            {$gestorNombre} AS gestor_nombre
        FROM adj_operacion o
        INNER JOIN adj_dictamen d ON d.id = (
            SELECT d2.id
            FROM adj_dictamen d2
            WHERE d2.id_operacion = o.id
            ORDER BY
                CASE WHEN TRIM(COALESCE(d2.dictamen, '')) = '' THEN 1 ELSE 0 END,
                d2.fecha_alta DESC,
                d2.id DESC
            LIMIT 1
        )
        {$joinAsig}
        WHERE (
            o.estatus = :estatus_actual
            OR EXISTS (
                SELECT 1
                FROM adj_historial_estatus h
                WHERE h.id_operacion = o.id
                  AND h.estatus_nuevo = :estatus_hist
            )
        )
        {$extraRecepcion}
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql, [
            'estatus_actual' => $estatus,
            'estatus_hist'   => $estatus,
        ]) ?: [];
    }

    /**
     * 4.- Cierre documentación — Dictaminado:
     * operaciones que pasaron por Cierre / Recepción con historial, mostrando el último dictamen.
     * Si la operación sigue en estatus Cierre documentado, solo entra aquí cuando el dictamen
     * unido es el de esta etapa (confirmación S2, tipo_contacto «Cierre documentación»), no
     * dictámenes previos de Retenciones u otras etapas (evita duplicar en Dictaminado lo que debe
     * verse en bandeja de entrada al llegar desde Recuperación).
     */
    public function obtenerDictaminadosCierreDocumentacionLista(): array
    {
        $joinAsig   = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $joinUltimo = $this->sqlJoinUltimoDictamenPorOperacion('d');
        $whereDict  = $this->sqlWhereComoDictaminadoCierreDocumentacion();
        $evidenciasCount = $this->sqlConteoEvidenciasCartera('o');
        $gestorNombre = $this->sqlNombreGestorConFallback('o');
        $tiempoTotalCierre = $this->sqlTiempoTotalCierreDocumentacion('o', 'd');
        $sql        = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            d.llamada_a,
            d.numero,
            d.persona_contactada,
            d.tipo_contacto,
            d.resultado,
            d.dictamen,
            d.plataforma,
            d.comentarios,
            {$evidenciasCount} AS evidencias_count,
            {$tiempoTotalCierre},
            DATE_FORMAT(o.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_gestion_legacy,
            DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen,
            {$gestorNombre} AS gestor_nombre
        FROM adj_operacion o
        {$joinUltimo}
        {$joinAsig}
        {$whereDict}
        ORDER BY o.fecha_actualizacion DESC, o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    /**
     * 3.- Recuperación — Pestaña Dictaminado: operaciones en Cierre documentado o que ya estuvieron ahí
     * (no desaparecen al pasar a Recepción u otra etapa). Último dictamen si existe.
     */
    public function obtenerDictaminadosRecuperacionLista(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $evidenciasCount = $this->sqlConteoExpedienteRecuperacion('o');
        $evidenciasValidacion = $this->sqlConteosValidacionAtn('o');
        $tiempoTotalRecuperacion = $this->sqlTiempoTotalRecuperacion('o', 'd');
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            d.llamada_a,
            d.numero,
            d.persona_contactada,
            d.tipo_contacto,
            d.resultado,
            d.dictamen,
            d.plataforma,
            d.comentarios,
            {$evidenciasCount} AS evidencias_count,
            {$evidenciasValidacion},
            {$tiempoTotalRecuperacion},
            DATE_FORMAT(o.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen_legacy,
            DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre
        FROM adj_operacion o
        LEFT JOIN adj_dictamen d ON d.id = (
            SELECT d2.id
            FROM adj_dictamen d2
            WHERE d2.id_operacion = o.id
            ORDER BY
                CASE WHEN TRIM(COALESCE(d2.dictamen, '')) = '' THEN 1 ELSE 0 END,
                d2.fecha_alta DESC,
                d2.id DESC
            LIMIT 1
        )
        {$joinAsig}
        WHERE (
            o.estatus = 'Cierre Documentado'
            OR EXISTS (
                SELECT 1
                FROM adj_historial_estatus h
                WHERE h.id_operacion = o.id
                  AND h.estatus_nuevo = 'Cierre Documentado'
            )
        )
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    // =========================================================================
    // DICTAMINADOS — 1.- Retenciones (dictamen de llamada; ver sqlEsDictamenLlamadaRetenciones)
    // =========================================================================

    /**
     * Retenciones — Dictaminado: operaciones con dictamen de llamada registrado en esta vista.
     * Se mantienen visibles aunque el estatus avance (Recibido, Procesando IA, etc.).
     */
    public function obtenerDictaminados(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $predD2   = $this->sqlEsDictamenLlamadaRetenciones('d2');
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            o.saldo_capital,
            o.adeudo_total,
            d.llamada_a,
            d.numero,
            d.persona_contactada,
            d.tipo_contacto,
            d.resultado,
            d.dictamen,
            d.plataforma,
            d.comentarios,
            DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_dictamen,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre
        FROM adj_operacion o
        INNER JOIN adj_dictamen d ON d.id = (
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
        {$joinAsig}
        WHERE COALESCE(d.dictamen, '') NOT LIKE 'Pendiente%'
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    // =========================================================================
    // PENDIENTES  (estatus = 'Retenciones' + último dictamen empieza con 'Pendiente')
    // =========================================================================

    public function obtenerPendientes(): array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $predDp   = $this->sqlEsDictamenLlamadaRetenciones('dp');
        $predDp2  = $this->sqlEsDictamenLlamadaRetenciones('dp2');
        $predDp3  = $this->sqlEsDictamenLlamadaRetenciones('dp3');
        $predDp4  = $this->sqlEsDictamenLlamadaRetenciones('dp4');
        $sql = <<<SQL
        SELECT
            o.id,
            o.folio,
            o.id_credito,
            o.nombre_cliente,
            o.estatus,
            (
                SELECT COUNT(*)
                FROM adj_dictamen dp
                WHERE dp.id_operacion = o.id
                  AND {$predDp}
            ) AS intentos_realizados,
            DATE_FORMAT(
                (
                    SELECT dp2.fecha_alta
                    FROM adj_dictamen dp2
                    WHERE dp2.id_operacion = o.id
                      AND {$predDp2}
                    ORDER BY dp2.id DESC
                    LIMIT 1
                ),
                '%d/%m/%Y %H:%i'
            ) AS fecha_ultimo_intento,
            TRIM(CONCAT_WS(' ',
                per.nombres,
                per.segundo_nombre,
                per.apellidop,
                per.apellidom
            )) AS gestor_nombre,
            DATE_FORMAT(aca.fecha_alta, '%d/%m/%Y') AS fecha_asignacion
        FROM adj_operacion o
        {$joinAsig}
        WHERE EXISTS (
              SELECT 1
              FROM adj_dictamen dp3
              WHERE dp3.id_operacion = o.id
                AND {$predDp3}
          )
          AND (
              SELECT dp4.dictamen
              FROM adj_dictamen dp4
              WHERE dp4.id_operacion = o.id
                AND {$predDp4}
              ORDER BY dp4.id DESC
              LIMIT 1
          ) LIKE 'Pendiente%'
        ORDER BY o.fecha_alta DESC
        SQL;

        return $this->db->queryAll($sql) ?: [];
    }

    // =========================================================================
    // REGISTRAR DICTAMEN
    // =========================================================================

    /**
     * Guarda el dictamen en adj_dictamen y actualiza el estatus de adj_operacion.
     * Estatus resultante (BD):
     *   'Autorizado para recolección'        → 'en_transito' (solo si la operación nunca avanzó por evidencias/recuperación/cierre/recepción; si ya pasó por esas etapas y vuelve a Retenciones al final del flujo, no cambia estatus)
     *   'Cancelado, promesa de pago'         → 'cancelado'
     *   'Cancelamiento total (sin intentos)' → 'cancelado'
     *   'Pendiente, …'                       → sin cambio (devuelve estatus_nuevo='pendiente')
     *   otros valores                        → sin cambio de estatus
     */
    public function registrarDictamen(int $idOperacion, array $data, int $idUsuario): array
    {
        $op = $this->db->queryOne(
            "SELECT id, estatus FROM adj_operacion WHERE id = :id",
            ['id' => $idOperacion]
        );

        if (!$op) {
            return ['success' => false, 'message' => 'Operación no encontrada.'];
        }

        $dictamen     = trim($data['dictamen'] ?? '');
        $ahora        = $this->fechaHoraCdmx();
        $nuevoEstatus = null;

        if ($dictamen === 'Autorizado para recolección') {
            $yaAvanzoEnPipeline = $this->db->queryOne(
                "SELECT 1 AS ok FROM adj_historial_estatus
                 WHERE id_operacion = :id
                   AND estatus_nuevo IN (
                       'Recibido', 'en_transito', 'Procesando IA', 'Revisión Recuperaciones',
                       'Cierre Documentado', 'Recepción'
                   )
                 LIMIT 1",
                ['id' => $idOperacion]
            );
            $nuevoEstatus = !empty($yaAvanzoEnPipeline['ok']) ? null : 'en_transito';
        } elseif ($dictamen === 'Cancelado, promesa de pago') {
            $nuevoEstatus = 'cancelado';
        } elseif ($dictamen === 'Cancelamiento total (sin intentos)') {
            $nuevoEstatus = 'cancelado';
        } elseif (str_starts_with($dictamen, 'Pendiente')) {
            $nuevoEstatus = 'pendiente'; // no cambia estatus en BD, solo señal al frontend
        }

        $this->db->CRUD(
            "INSERT INTO adj_dictamen
                (id_operacion, llamada_a, numero, persona_contactada, tipo_contacto,
                 resultado, dictamen, plataforma, comentarios, id_usuario, fecha_alta)
             VALUES
                (:id_operacion, :llamada_a, :numero, :persona_contactada, :tipo_contacto,
                 :resultado, :dictamen, :plataforma, :comentarios, :id_usuario, :fecha_alta)",
            [
                'id_operacion'       => $idOperacion,
                'llamada_a'          => trim($data['llamada_a']           ?? ''),
                'numero'             => trim($data['numero']              ?? ''),
                'persona_contactada' => trim($data['persona_contactada']  ?? ''),
                'tipo_contacto'      => trim($data['tipo_contacto']       ?? ''),
                'resultado'          => trim($data['resultado']           ?? ''),
                'dictamen'           => $dictamen,
                'plataforma'         => trim($data['plataforma']          ?? '') ?: null,
                'comentarios'        => trim($data['comentarios']         ?? '') ?: null,
                'id_usuario'         => $idUsuario ?: null,
                'fecha_alta'         => $ahora,
            ]
        );

        if ($nuevoEstatus !== null && $nuevoEstatus !== 'pendiente') {
            $this->db->CRUD(
                "UPDATE adj_operacion
                    SET estatus = :estatus, fecha_actualizacion = :fecha
                  WHERE id = :id",
                ['estatus' => $nuevoEstatus, 'fecha' => $ahora, 'id' => $idOperacion]
            );

            $this->db->CRUD(
                "INSERT INTO adj_historial_estatus
                    (id_operacion, estatus_anterior, estatus_nuevo, id_usuario, fecha)
                 VALUES (:id_op, :anterior, :nuevo, :id_usr, :fecha)",
                [
                    'id_op'    => $idOperacion,
                    'anterior' => $op['estatus'],
                    'nuevo'    => $nuevoEstatus,
                    'id_usr'   => $idUsuario ?: null,
                    'fecha'    => $ahora,
                ]
            );
        }

        return ['success' => true, 'estatus_nuevo' => $nuevoEstatus];
    }

    // =========================================================================
    // OBTENER DICTAMEN DE UNA OPERACIÓN
    // =========================================================================

    /**
     * Último dictamen de cualquier tipo (compatibilidad).
     */
    public function obtenerDictamen(int $idOperacion): ?array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        return $this->db->queryOne(
            "SELECT d.*,
                    DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                    o.id_credito,
                    o.folio,
                    o.nombre_cliente,
                    o.estatus AS op_estatus,
                    TRIM(CONCAT_WS(' ',
                        per.nombres,
                        per.segundo_nombre,
                        per.apellidop,
                        per.apellidom
                    )) AS gestor_nombre
             FROM adj_dictamen d
             JOIN adj_operacion o ON o.id = d.id_operacion
             {$joinAsig}
             WHERE d.id_operacion = :id
             ORDER BY d.fecha_alta DESC
             LIMIT 1",
            ['id' => $idOperacion]
        );
    }

    /**
     * Último dictamen registrado desde 1.- Retenciones (llamada), con gestor.
     * Usado en el modal de pipeline para alinear resumen con comentarios por llamada.
     */
    public function obtenerUltimoDictamenLlamadaRetenciones(int $idOperacion): ?array
    {
        $joinAsig = $this->sqlJoinUnaAsignacionActivaPorCredito();
        $pred     = $this->sqlEsDictamenLlamadaRetenciones('d');

        return $this->db->queryOne(
            "SELECT d.*,
                    DATE_FORMAT(d.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
                    o.id_credito,
                    o.folio,
                    o.nombre_cliente,
                    o.estatus AS op_estatus,
                    TRIM(CONCAT_WS(' ',
                        per.nombres,
                        per.segundo_nombre,
                        per.apellidop,
                        per.apellidom
                    )) AS gestor_nombre
             FROM adj_dictamen d
             JOIN adj_operacion o ON o.id = d.id_operacion
             {$joinAsig}
             WHERE d.id_operacion = :id
               AND {$pred}
             ORDER BY
                 CASE WHEN TRIM(COALESCE(d.dictamen, '')) = '' THEN 1 ELSE 0 END,
                 d.fecha_alta DESC,
                 d.id DESC
             LIMIT 1",
            ['id' => $idOperacion]
        );
    }

    /**
     * Hasta 3 dictámenes de llamada (Retenciones), los más recientes, en orden cronológico (1ra → 3ra).
     *
     * @return list<array<string, mixed>>
     */
    public function obtenerHistorialDictamenesLlamadaRetenciones(int $idOperacion): array
    {
        $pred = $this->sqlEsDictamenLlamadaRetenciones('d');

        $sql = <<<SQL
        SELECT
            t.id,
            t.comentarios,
            DATE_FORMAT(t.fecha_alta, '%d/%m/%Y %H:%i') AS fecha_alta_fmt,
            t.dictamen,
            t.llamada_a,
            t.tipo_contacto,
            t.resultado,
            TRIM(CONCAT_WS(' ',
                reg.nombres,
                reg.segundo_nombre,
                reg.apellidop,
                reg.apellidom
            )) AS registrado_nombre
        FROM (
            SELECT d.id, d.id_usuario, d.comentarios, d.fecha_alta, d.dictamen, d.llamada_a, d.tipo_contacto, d.resultado
            FROM adj_dictamen d
            WHERE d.id_operacion = :id
              AND {$pred}
            ORDER BY d.fecha_alta DESC, d.id DESC
            LIMIT 3
        ) AS t
        LEFT JOIN persona reg ON reg.id = t.id_usuario
        ORDER BY t.fecha_alta ASC, t.id ASC
        SQL;

        return $this->db->queryAll($sql, ['id' => $idOperacion]) ?: [];
    }
}
