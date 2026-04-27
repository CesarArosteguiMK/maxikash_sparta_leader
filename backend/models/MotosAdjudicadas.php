<?php

namespace Models;

use Core\Model;
use Core\Database;
use Models\Adjudicacion as AdjudicacionModel;

class MotosAdjudicadas extends Model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
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
            'doc_repuve',    'doc_factura',
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
        $videoSlots = ['fis_360'];
        $docSlots   = ['doc_repuve', 'doc_factura'];

        if (in_array($slot, $videoSlots, true)) {
            if ($mime !== 'video/mp4' || $ext !== 'mp4') {
                return ['success' => false, 'message' => 'Este campo solo acepta video MP4.'];
            }
            $tipo = 'video';
        } elseif (in_array($slot, $docSlots, true)) {
            $okMimes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!in_array($mime, $okMimes, true)) {
                return ['success' => false, 'message' => 'Solo se aceptan PDF, JPG o PNG.'];
            }
            $tipo = ($mime === 'application/pdf') ? 'pdf' : 'image';
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
                'UPDATE adj_evidencia
                    SET tipo = :tipo, url = :url, fecha_alta = :fecha
                  WHERE id_operacion = :id AND slot = :slot',
                ['tipo' => $tipo, 'url' => $urlRelativa, 'fecha' => $ahora,
                 'id'   => $idOperacion, 'slot' => $slot]
            );
        } else {
            $this->db->CRUD(
                'INSERT INTO adj_evidencia (id_operacion, tipo, slot, url, fecha_alta, alta)
                 VALUES (:id, :tipo, :slot, :url, :fecha, :alta)',
                ['id'   => $idOperacion, 'tipo' => $tipo, 'slot' => $slot,
                 'url'  => $urlRelativa, 'fecha' => $ahora, 'alta' => $idUsuario]
            );
        }

        $slotLabel = self::SLOT_LABELS[$slot] ?? strtoupper($slot);
        $this->registrarBitacora($idOperacion, 'SUBIÓ EVIDENCIA EN ' . $slotLabel, $idUsuario, $nombreUsuario);

        return ['success' => true, 'url' => $urlRelativa];
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

        $op['evidencias']    = $this->db->queryAll(
            "SELECT id, tipo, slot, url, DATE_FORMAT(fecha_alta, '%Y-%m-%d %H:%i') AS fecha_alta
             FROM adj_evidencia WHERE id_operacion = :id ORDER BY id ASC",
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
    ];

    private const ESTATUS_VALIDOS = [
        'Recibido',
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
     * Devuelve los créditos activos asignados al usuario en la tabla
     * asigna_creditos_adjudicacion, enriquecidos con datos del Segundometro
     * (nombre del cliente, saldo, días de mora, bucket).
     */
    public function obtenerMisAdjudicaciones(int $idPersona): array
    {
        $adjModel = new AdjudicacionModel();
        $creditos = $adjModel->obtenerCreditosAsignados($idPersona);

        if (empty($creditos)) {
            return [];
        }

        // Enriquecer con Segundometro
        $ids = array_map('intval', array_column($creditos, 'id_credito'));

        $placeholders = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $key = "id$i";
            $placeholders[] = ":$key";
            $params[$key] = $id;
        }
        $inStr = implode(',', $placeholders);

        // Regla de negocio: sólo mostrar en Mis Adjudicaciones cuando el
        // crédito ya salió de Atención a Clientes (estatus en_transito o posterior).
        $ops = $this->db->queryAll(
            "SELECT ao.id_credito, ao.estatus
             FROM adj_operacion ao
             INNER JOIN (
                SELECT id_credito, MAX(id) AS max_id
                FROM adj_operacion
                WHERE id_credito IN ($inStr)
                GROUP BY id_credito
             ) ult ON ult.max_id = ao.id",
            $params
        ) ?: [];

        $permitidos = array_flip([
            'en_transito',
            'Recibido',
            'Procesando IA',
            'Revisión Recuperaciones',
            'Cierre Documentado',
            'Recepción',
        ]);

        $idsVisibles = [];
        foreach ($ops as $op) {
            $idCredito = (int)($op['id_credito'] ?? 0);
            $estatus = (string)($op['estatus'] ?? '');
            if ($idCredito > 0 && isset($permitidos[$estatus])) {
                $idsVisibles[$idCredito] = true;
            }
        }

        $creditos = array_values(array_filter(
            $creditos,
            fn($c) => isset($idsVisibles[(int)($c['id_credito'] ?? 0)])
        ));

        if (empty($creditos)) {
            return [];
        }

        $ids = array_map('intval', array_column($creditos, 'id_credito'));
        $placeholders = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $key = "id$i";
            $placeholders[] = ":$key";
            $params[$key] = $id;
        }
        $inStr = implode(',', $placeholders);

        try {
            $dbS2 = new \Core\DatabaseSegundometro();

            $rows = $dbS2->queryAll(
                "SELECT Id_credito, Nombre_cliente, Dias_mora, Bucket_Morosidad_Real,
                        Saldo_total_capital AS saldo
                 FROM tbl_segundometro_semana
                 WHERE Id_credito IN ($inStr)",
                $params
            ) ?: [];

            $mapa = [];
            foreach ($rows as $r) {
                $mapa[(int)$r['Id_credito']] = $r;
            }

            // Histórico para los no encontrados en semana
            $faltantes = array_filter($ids, fn($id) => !isset($mapa[$id]));
            if (!empty($faltantes)) {
                $ph2 = []; $p2 = [];
                foreach (array_values($faltantes) as $i => $id) {
                    $k = "h$i"; $ph2[] = ":$k"; $p2[$k] = $id;
                }
                $rowsH = $dbS2->queryAll(
                    "SELECT Id_credito,
                            MAX(Nombre_cliente)       AS Nombre_cliente,
                            MAX(Dias_mora)            AS Dias_mora,
                            MAX(Bucket_Morosidad_Real) AS Bucket_Morosidad_Real,
                            MAX(Saldo_total_capital)  AS saldo
                     FROM tbl_segundometro_histo
                     WHERE Id_credito IN (" . implode(',', $ph2) . ")
                     GROUP BY Id_credito",
                    $p2
                ) ?: [];
                foreach ($rowsH as $r) {
                    $mapa[(int)$r['Id_credito']] = $r;
                }
            }
        } catch (\Exception $e) {
            $mapa = [];
        }

        // Fusionar
        foreach ($creditos as &$c) {
            $id = (int)$c['id_credito'];
            $s2 = $mapa[$id] ?? [];
            $c['nombre_cliente'] = $s2['Nombre_cliente']        ?? 'No disponible';
            $c['dias_mora']      = $s2['Dias_mora']             ?? 0;
            $c['bucket']         = $s2['Bucket_Morosidad_Real'] ?? '—';
            $c['saldo']          = $s2['saldo']                 ?? 0;
        }
        unset($c);

        return $creditos;
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

        $placeholders = [];
        $params = [];
        foreach ($ids as $i => $id) {
            $k = 'id' . $i;
            $placeholders[] = ':' . $k;
            $params[$k] = $id;
        }
        $inStr = implode(',', $placeholders);

        $rows = $this->db->queryAll(
            "SELECT ult.id_credito, COALESCE(ev.total, 0) AS total
             FROM (
                SELECT id_credito, MAX(id) AS max_id
                FROM adj_operacion
                WHERE id_credito IN ($inStr)
                GROUP BY id_credito
             ) ult
             LEFT JOIN (
                SELECT id_operacion, COUNT(*) AS total
                FROM adj_evidencia
                GROUP BY id_operacion
             ) ev ON ev.id_operacion = ult.max_id",
            $params
        ) ?: [];

        $resumen = [];
        foreach ($rows as $r) {
            $id = (int) ($r['id_credito'] ?? 0);
            if ($id > 0) {
                $resumen[$id] = (int) ($r['total'] ?? 0);
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
}
