<?php

namespace Models;

use Core\Model;
use Core\Database;
use Core\DatabaseSegundometro;

class Despachos extends Model
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Obtener dirección completa desde tbl_segundometro_semana
     * Esta función consulta la base de datos db-megae-reporte
     */
    private function obtenerDomicilioCompleto($idCredito)
    {
        try {
            $dbSegundo = new DatabaseSegundometro();
            $query = <<<SQL
            SELECT 
                Domicilio_Completo,
                Id_cliente,
                Nombre_cliente
            FROM tbl_segundometro_semana 
            WHERE Id_credito = :idCredito
            LIMIT 1
SQL;
            
            $resultado = $dbSegundo->queryOne($query, ['idCredito' => $idCredito]);
            return $resultado['Domicilio_Completo'] ?? null;
        } catch (\Exception $e) {
            error_log("Error al obtener Domicilio_Completo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener lista de despachos (Gestores y Supervisores)
     * IDs de puesto: 24 = Gestor, 36 = Supervisor
     * Un despacho = Una persona con cualquiera de estos 2 puestos
     */
    public function obtenerDespachos()
    {
        $query = <<<SQL
        SELECT 
            ap.id_persona,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.id AS id_puesto,
            pu.nombre AS nombre_puesto,
            ap.activo,
            per.estatus,
            per.correo,
            per.telefono_uno
        FROM asigna_puesto ap
        INNER JOIN persona per ON per.id = ap.id_persona
        INNER JOIN puesto pu ON pu.id = ap.id_puesto
        WHERE ap.id_puesto IN (24, 36)
        ORDER BY pu.id, per.nombres
SQL;

        return $this->db->queryAll($query);
    }

    /**
     * Obtener datos de un despacho específico usando la tabla persona directamente
     * Si existe en despachos, traemos esos datos adicionales, sino solo de persona
     */
    public function obtenerDatosDespacho($idPersona)
    {
        $query = <<<SQL
        SELECT 
            per.id,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_completo,
            pu.nombre AS puesto,
            per.telefono_uno AS telefono,
            per.correo AS correo,
            COALESCE(d.direccion, '') AS direccion,
            COALESCE(d.tipo_persona, '') AS tipo_persona,
            d.id AS id_despacho
        FROM persona per
        INNER JOIN asigna_puesto ap ON per.id = ap.id_persona
        INNER JOIN puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN despachos d ON per.id = d.id_persona AND d.estatus = 'Activo'
        WHERE per.id = :idPersona 
          AND ap.id_puesto IN (24, 36)
          AND ap.activo = 1
        LIMIT 1
SQL;

        $result = $this->db->queryOne($query, ['idPersona' => $idPersona]);
        
        if (!$result) {
            return [
                'datos' => [
                    'nombre_completo' => '',
                    'puesto' => '',
                    'telefono' => '',
                    'correo' => '',
                    'direccion' => '',
                    'tipo_persona' => ''
                ],
                'comentarios' => '',
                'id_despacho' => null
            ];
        }
        
        // Obtener el último comentario (si existe y tiene id_despacho)
        $comentario = '';
        if (!empty($result['id_despacho'])) {
            $queryComentario = <<<SQL
            SELECT comentario 
            FROM comentarios_despacho 
            WHERE id_despacho = :idDespacho 
            ORDER BY fecha_comentario DESC 
            LIMIT 1
SQL;
            $comentarioResult = $this->db->queryOne($queryComentario, ['idDespacho' => $result['id_despacho']]);
            $comentario = $comentarioResult['comentario'] ?? '';
        }
        
        return [
            'datos' => $result,
            'comentarios' => $comentario,
            'id_despacho' => $result['id_despacho'] ?? null
        ];
    }

    /**
     * Obtener comentarios de un despacho
     */
    public function obtenerComentarios($idDespacho)
    {
        $query = <<<SQL
        SELECT 
            c.id,
            c.comentario,
            c.fecha_comentario,
            CONCAT_WS(' ', per.nombres, per.segundo_nombre, per.apellidop, per.apellidom) AS nombre_persona
        FROM comentarios_despacho c
        LEFT JOIN persona per ON c.id_persona_comenta = per.id
        WHERE c.id_despacho = ?
        ORDER BY c.fecha_comentario DESC
SQL;

        return $this->db->queryAll($query, [$idDespacho]);
    }

    /**
     * Obtener métricas de un despacho
     */
    public function obtenerMetricasDespacho($idPersona)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        
        if (!$despacho) {
            return [
                'creditos_asignados' => 0,
                'saldo_total' => 0,
                'recuperacion' => 0,
                'promedio_mora' => 0
            ];
        }
        
        $idDespacho = $despacho['id'];
        
        // Créditos asignados activos
        $queryCreditos = <<<SQL
        SELECT COUNT(*) as total 
        FROM asigna_creditos_despacho 
        WHERE id_despacho = :idDespacho AND estatus = 'Activo'
SQL;
        $creditos = $this->db->queryOne($queryCreditos, ['idDespacho' => $idDespacho]);
        
        // Por ahora, saldo, recuperación y mora se calculan cuando tengas acceso a la API de créditos
        // o cuando definas de dónde obtienes esta información
        
        return [
            'creditos_asignados' => $creditos['total'] ?? 0,
            'saldo_total' => 0,
            'recuperacion' => 0,
            'promedio_mora' => 0
        ];
    }

    /**
     * Buscar crédito por ID usando la API externa (como en EstadoCuenta)
     */
    public function buscarCredito($tipo, $valor)
    {
        // Usamos la API externa para obtener información del crédito
        // Similar a como se hace en EstadoCuenta
        $url = "https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta";
        
        $payload = json_encode([
            "idCredito" => intval($valor),
            "fechaCorte" => date('Y-m-d')
        ]);
        
        $headers = [
            "Token: __SPARTA_TOKEN_REDACTED__",
            "Content-Type: application/json"
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $httpCode !== 200) {
            return null;
        }
        
        $json = json_decode($response, true);
        
        if (!isset($json["estadoCuenta"])) {
            return null;
        }
        
        $estadoCuenta = $json["estadoCuenta"];
        $cliente = $estadoCuenta["datosCliente"] ?? [];
        
        // Construir dirección completa desde los campos del cliente
        $direccionParts = array_filter([
            $cliente["calle"] ?? '',
            $cliente["numeroExterior"] ?? '',
            $cliente["numeroInterior"] ?? '',
            $cliente["colonia"] ?? '',
            $cliente["municipio"] ?? '',
            $cliente["estado"] ?? '',
            $cliente["codigoPostal"] ?? ''
        ]);
        $direccionAPI = !empty($direccionParts) ? implode(', ', $direccionParts) : null;
        
        // Intentar obtener dirección desde tbl_segundometro_semana (prioridad)
        $domicilioCompleto = $this->obtenerDomicilioCompleto($valor);
        
        // Usar domicilio completo si existe, sino la de la API, sino mensaje por defecto
        $direccion = $domicilioCompleto ?: ($direccionAPI ?: 'Sin dirección registrada');
        
        return [
            'id_credito' => $estadoCuenta["idCredito"] ?? $valor,
            'nombre_cliente' => $cliente["nombreCliente"] ?? 'Sin nombre',
            'saldo_actual' => $estadoCuenta["datosSaldos"]["saldoTotalVencido"] ?? 0,
            'dias_mora' => $estadoCuenta["datosSaldos"]["diasMoraMaximo"] ?? 0,
            'telefono' => $cliente["celular"] ?? 'Sin teléfono',
            'curp' => $cliente["curp"] ?? 'Sin CURP',
            'direccion' => $direccion,
            'direccion_api' => $direccionAPI ?: 'No disponible en API',
            'direccion_megareporte' => $domicilioCompleto ?: 'No disponible en Megareporte',
            'sucursal' => $cliente["sucursal"] ?? 'Sin sucursal',
            'fecha_desembolso' => $estadoCuenta["fechaDesembolso"] ?? 'Sin fecha'
        ];
    }

    /**
     * Obtener información de asignación de un crédito
     * Devuelve datos del despacho si está asignado, o null si no
     */
    public function obtenerAsignacionCredito($idCredito)
    {
        $query = <<<SQL
        SELECT 
            acd.id_credito,
            acd.estatus,
            DATE_FORMAT(acd.fecha_alta, '%Y-%m-%d %H:%i') as fecha_asignacion,
            DATE_FORMAT(acd.fecha_baja, '%Y-%m-%d %H:%i') as fecha_baja,
            d.id_persona,
            CONCAT_WS(' ', per.nombres, per.apellidop) as nombre_despacho,
            pu.nombre as puesto_despacho,
            per.telefono_uno as telefono_despacho,
            per.correo as correo_despacho,
            CONCAT_WS(' ', per_asigno.nombres, per_asigno.apellidop) as asignado_por
        FROM asigna_creditos_despacho acd
        INNER JOIN despachos d ON acd.id_despacho = d.id
        INNER JOIN persona per ON d.id_persona = per.id
        LEFT JOIN asigna_puesto ap ON per.id = ap.id_persona
        LEFT JOIN puesto pu ON ap.id_puesto = pu.id
        LEFT JOIN persona per_asigno ON acd.alta = per_asigno.id
        WHERE acd.id_credito = :idCredito
        ORDER BY acd.fecha_alta DESC
        LIMIT 1
SQL;
        
        return $this->db->queryOne($query, ['idCredito' => $idCredito]);
    }
    
    /**
     * Verificar si un crédito ya está asignado a un despacho (activo)
     */
    public function verificarAsignacion($idCredito)
    {
        $asignacion = $this->obtenerAsignacionCredito($idCredito);
        return $asignacion && $asignacion['estatus'] === '1';
    }

    /**
     * Asignar crédito a un despacho
     */
    public function asignarCredito($idPersona, $idCredito)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        
        if (!$despacho) {
            return false;
        }
        
        // Verificar si ya existe una asignación previa para este crédito y despacho
        $queryVerificar = "SELECT id FROM asigna_creditos_despacho WHERE id_despacho = :idDespacho AND id_credito = :idCredito LIMIT 1";
        $existente = $this->db->queryOne($queryVerificar, [
            'idDespacho' => $despacho['id'],
            'idCredito' => $idCredito
        ]);
        
        $usuarioAsignacion = $_SESSION['usuario_id'] ?? 1;
        
        if ($existente) {
            // Si ya existe, reactivar (UPDATE)
            $query = <<<SQL
            UPDATE asigna_creditos_despacho 
            SET estatus = '1',
                fecha_baja = NULL,
                fecha_alta = NOW(),
                alta = :usuarioAsignacion
            WHERE id = :id
SQL;
            return $this->db->CRUD($query, [
                'id' => $existente['id'],
                'usuarioAsignacion' => $usuarioAsignacion
            ]) > 0;
        } else {
            // Si no existe, crear nuevo (INSERT)
            $query = <<<SQL
            INSERT INTO asigna_creditos_despacho 
            (id_despacho, id_credito, fecha_alta, alta, estatus)
            VALUES (:idDespacho, :idCredito, NOW(), :usuarioAsignacion, '1')
SQL;
            return $this->db->CRUD($query, [
                'idDespacho' => $despacho['id'],
                'idCredito' => $idCredito,
                'usuarioAsignacion' => $usuarioAsignacion
            ]) > 0;
        }
    }

    /**
     * Cambiar estatus de crédito (activar/desactivar)
     */
    public function cambiarEstatusCredito($idCredito, $nuevoEstatus)
    {
        $fechaBaja = $nuevoEstatus === '0' ? 'NOW()' : 'NULL';
        
        // Actualizar todos los registros de este crédito (un crédito solo puede estar activo en un lugar a la vez)
        $query = <<<SQL
        UPDATE asigna_creditos_despacho 
        SET estatus = :nuevoEstatus, 
            fecha_baja = $fechaBaja
        WHERE id_credito = :idCredito
SQL;
        
        return $this->db->CRUD($query, [
            'idCredito' => $idCredito,
            'nuevoEstatus' => $nuevoEstatus
        ]) > 0;
    }

    /**
     * Obtener créditos asignados a un despacho
     * Consulta directamente desde asigna_creditos_despacho usando id_persona
     */
    public function obtenerCreditosAsignados($idPersona)
    {
        $query = <<<SQL
        SELECT 
            acd.id_credito,
            acd.estatus as estado,
            DATE_FORMAT(acd.fecha_alta, '%Y-%m-%d %H:%i') as fecha_asignacion,
            CONCAT_WS(' ', per.nombres, per.apellidop) as asignado_por
        FROM asigna_creditos_despacho acd
        INNER JOIN despachos d ON acd.id_despacho = d.id
        LEFT JOIN persona per ON acd.alta = per.id
        WHERE d.id_persona = :idPersona
        ORDER BY acd.fecha_alta DESC
SQL;

        return $this->db->queryAll($query, ['idPersona' => $idPersona]);
    }

    /**
     * Guardar comentario sobre un despacho
     */
    public function guardarComentario($idPersona, $comentario)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona AND estatus = 'Activo' LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        
        if (!$despacho) {
            return false;
        }
        
        $query = <<<SQL
        INSERT INTO comentarios_despacho 
        (id_despacho, comentario, id_persona_comenta, fecha_comentario)
        VALUES (:idDespacho, :comentario, :idPersona, NOW())
SQL;

        $idPersonaComenta = $_SESSION['usuario_id'] ?? 1;
        
        return $this->db->CRUD($query, [
            'idDespacho' => $despacho['id'],
            'comentario' => $comentario,
            'idPersona' => $idPersonaComenta
        ]) > 0;
    }

    /**
     * Obtener catálogo de documentos para despachos
     */
    public function obtenerCatalogoDocumentos()
    {
        $query = "SELECT id, nombre_documento, descripcion 
                  FROM catalogo_documentos_despacho 
                  ORDER BY nombre_documento";
        
        return $this->db->queryAll($query);
    }

    /**
     * Obtener documentos cargados para un despacho específico
     */
    public function obtenerDocumentosDespacho($idPersona)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        
        if (!$despacho) {
            return [];
        }

        $query = <<<SQL
        SELECT 
            dd.id,
            dd.id_catalogo_documento,
            dd.nombre_archivo,
            dd.ruta_archivo,
            dd.fecha_carga,
            dd.estatus,
            cd.nombre_documento,
            cd.descripcion,
            'Sistema' as cargado_por
        FROM documentos_despacho dd
        INNER JOIN catalogo_documentos_despacho cd ON dd.id_catalogo_documento = cd.id
        WHERE dd.id_despacho = :idDespacho
        ORDER BY dd.fecha_carga DESC
SQL;

        return $this->db->queryAll($query, ['idDespacho' => $despacho['id']]);
    }

    /**
     * Subir un documento para un despacho
     */
    public function subirDocumento($idPersona, $idCatalogoDocumento, $nombreArchivo, $rutaArchivo)
    {
        // Primero obtener el id del despacho
        $queryDespacho = "SELECT id FROM despachos WHERE id_persona = :idPersona LIMIT 1";
        $despacho = $this->db->queryOne($queryDespacho, ['idPersona' => $idPersona]);
        
        if (!$despacho) {
            return false;
        }

        $query = <<<SQL
        INSERT INTO documentos_despacho 
        (id_despacho, id_catalogo_documento, nombre_archivo, ruta_archivo, fecha_carga, estatus)
        VALUES (:idDespacho, :idCatalogoDocumento, :nombreArchivo, :rutaArchivo, NOW(), 'Vigente')
SQL;
        
        return $this->db->CRUD($query, [
            'idDespacho' => $despacho['id'],
            'idCatalogoDocumento' => $idCatalogoDocumento,
            'nombreArchivo' => $nombreArchivo,
            'rutaArchivo' => $rutaArchivo
        ]) > 0;
    }

    /**
     * Actualizar tipo de persona en la tabla despachos
     */
    public function actualizarTipoPersona($idPersona, $tipoPersona)
    {
        // Primero verificar si existe registro en despachos
        $queryVerificar = "SELECT id FROM despachos WHERE id_persona = :idPersona LIMIT 1";
        $despacho = $this->db->queryOne($queryVerificar, ['idPersona' => $idPersona]);
        
        if ($despacho) {
            // Si existe, actualizar
            $query = "UPDATE despachos SET tipo_persona = :tipoPersona WHERE id = :id";
            return $this->db->CRUD($query, [
                'id' => $despacho['id'],
                'tipoPersona' => $tipoPersona
            ]) > 0;
        } else {
            // Si no existe, crear registro en despachos
            $query = <<<SQL
            INSERT INTO despachos (id_persona, tipo_persona, estatus, fecha_alta)
            VALUES (:idPersona, :tipoPersona, 'Activo', NOW())
SQL;
            return $this->db->CRUD($query, [
                'idPersona' => $idPersona,
                'tipoPersona' => $tipoPersona
            ]) > 0;
        }
    }

    /**
     * Obtener información de un documento por su ID
     */
    public function obtenerInfoDocumento($idDocumento)
    {
        $query = "SELECT nombre_archivo, ruta_archivo FROM documentos_despacho WHERE id = :id";
        return $this->db->queryOne($query, ['id' => $idDocumento]);
    }
}
