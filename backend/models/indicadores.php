<?php
// models/Indicadores.php - Versión completa actualizada

namespace Models;

use Core\Model;

class Indicadores extends Model
{
    private static function getSegundometroConnection()
    {
        $database = new \Core\DatabaseSegundometro();
        $reflection = new \ReflectionClass($database);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        return $property->getValue($database);
    }

    private static function getLegacyConnection()
    {
        $database = new \Core\DatabaseLegacy();
        $reflection = new \ReflectionClass($database);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        return $property->getValue($database);
    }

    /**
     * ✅ GESTIONES 1 A 7 - VERSIÓN COMPLETA
     */
    public static function getGestiones1A7($filtros = [])
    {
        $dbSegundo = self::getSegundometroConnection();
        $dbLegacy = self::getLegacyConnection();
        
        try {
            // ==========================================
            // PASO 1: Obtener líderes con datos base
            // ==========================================
            $sqlLideres = "
                SELECT 
                    CASE 
                        WHEN Observaciones IS NULL OR TRIM(Observaciones) = '' THEN 'SIN ASIGNAR'
                        ELSE Observaciones 
                    END as lider,
                    COUNT(*) as total_creditos,
                    SUM(CASE WHEN Cierre_Actual = 'a) Current' THEN 1 ELSE 0 END) as current,
                    SUM(CASE WHEN Cierre_Actual = 'b) 1 a 7 dias' THEN 1 ELSE 0 END) as gestiones_1a7,
                    SUM(COALESCE(Saldo_vencido_actualizado, 0)) as saldo_vencido
                FROM tbl_segundometro_semana
                WHERE Cierre_Actual IN ('a) Current', 'b) 1 a 7 dias', 'c) 8 a 14 dias', 'd) 15 a 21 dias')
                GROUP BY 
                    CASE 
                        WHEN Observaciones IS NULL OR TRIM(Observaciones) = '' THEN 'SIN ASIGNAR'
                        ELSE Observaciones 
                    END
                ORDER BY total_creditos DESC
            ";
            
            $stmtLideres = $dbSegundo->prepare($sqlLideres);
            $stmtLideres->execute();
            $lideres = $stmtLideres->fetchAll(\PDO::FETCH_ASSOC);
            
            if (empty($lideres)) {
                return ['success' => true, 'data' => []];
            }
            
            // ==========================================
            // PASO 2: Obtener IDs de créditos por líder
            // ==========================================
            $sqlCreditos = "
                SELECT 
                    CASE 
                        WHEN Observaciones IS NULL OR TRIM(Observaciones) = '' THEN 'SIN ASIGNAR'
                        ELSE Observaciones 
                    END as lider,
                    Id_credito
                FROM tbl_segundometro_semana
                WHERE Cierre_Actual IN ('a) Current', 'b) 1 a 7 dias', 'c) 8 a 14 dias', 'd) 15 a 21 dias')
            ";
            
            $stmtCreditos = $dbSegundo->prepare($sqlCreditos);
            $stmtCreditos->execute();
            $creditosPorLider = [];
            
            while ($row = $stmtCreditos->fetch(\PDO::FETCH_ASSOC)) {
                $creditosPorLider[$row['lider']][] = $row['Id_credito'];
            }
            
            // ==========================================
            // PASO 3: Procesar cada líder
            // ==========================================
            $resultado = [];
            
            foreach ($lideres as $lider) {
                $nombreLider = $lider['lider'];
                $creditos = $creditosPorLider[$nombreLider] ?? [];
                
                // Inicializar contadores
                $campo = 0;
                $telefono = 0;
                $sinGestion = 0;
                $fechaAntigua = null;
                $fechaReciente = null;
                $estatus = 'Sin actividad';
                
                if (!empty($creditos)) {
                    $placeholders = implode(',', array_fill(0, count($creditos), '?'));
                    
                    // ✅ Query con filtro de campaña
                    $sqlLegacy = "
                        SELECT 
                            id_credit,
                            contacto,
                            fecha_dictamen
                        FROM legacy_semanal
                        WHERE id_credit IN ($placeholders)
                          AND campana LIKE 'ASIGNACION_W%_1A7'
                    ";
                    
                    $stmtLegacy = $dbLegacy->prepare($sqlLegacy);
                    $stmtLegacy->execute($creditos);
                    $gestiones = $stmtLegacy->fetchAll(\PDO::FETCH_ASSOC);
                    
                    // Créditos que SÍ tienen gestión
                    $creditosConGestion = [];
                    
                    foreach ($gestiones as $gestion) {
                        $creditosConGestion[$gestion['id_credit']] = true;
                        
                        $contacto = strtolower($gestion['contacto'] ?? '');
                        
                        // ✅ CAMPO = whatsapp + telefono
                        if (in_array($contacto, ['whatsapp', 'telefono'])) {
                            $campo++;
                        }
                        
                        // ✅ TELEFONO = solo telefono
                        if ($contacto === 'telefono') {
                            $telefono++;
                        }
                        
                        // ✅ Calcular fechas
                        if ($gestion['fecha_dictamen']) {
                            if (!$fechaAntigua || strtotime($gestion['fecha_dictamen']) < strtotime($fechaAntigua)) {
                                $fechaAntigua = $gestion['fecha_dictamen'];
                            }
                            if (!$fechaReciente || strtotime($gestion['fecha_dictamen']) > strtotime($fechaReciente)) {
                                $fechaReciente = $gestion['fecha_dictamen'];
                            }
                        }
                    }
                    
                    // ✅ SIN GESTIÓN = créditos sin registros en legacy
                    $sinGestion = count($creditos) - count($creditosConGestion);
                    
                    // ✅ Calcular estatus de actividad
                    if ($fechaReciente) {
                        $minutos = (time() - strtotime($fechaReciente)) / 60;
                        if ($minutos < 30) {
                            $estatus = 'Activo';
                        } else {
                            $estatus = 'Inactivo';
                        }
                    }
                } else {
                    // Si no hay créditos, todos son sin gestión
                    $sinGestion = $lider['total_creditos'];
                }
                
                // Calcular eficiencia
                $total_base = $lider['current'] + $lider['gestiones_1a7'];
                $eficiencia = $total_base > 0 ? round(($lider['current'] * 100) / $total_base, 1) : 0;
                
                // ✅ Armar resultado final
                $resultado[] = [
                    'lider' => $nombreLider,
                    'current' => (int)$lider['current'],
                    'gestiones_1a7' => (int)$lider['gestiones_1a7'],
                    'sin_gestion' => $sinGestion,
                    'eficiencia' => $eficiencia,
                    'total_general' => (int)$lider['total_creditos'],
                    'campo' => $campo,
                    'telefono' => $telefono,
                    'saldo_vencido' => '$' . number_format($lider['saldo_vencido'] ?? 0, 2),
                    'fecha_dictamen_mas_antigua' => $fechaAntigua ? date('d/m/Y H:i', strtotime($fechaAntigua)) : '-',
                    'fecha_dictamen_mas_reciente' => $fechaReciente ? date('d/m/Y H:i', strtotime($fechaReciente)) : '-',
                    'estatus_gestor' => $estatus
                ];
            }
            
            return [
                'success' => true,
                'data' => $resultado
            ];
            
        } catch (\Exception $e) {
            error_log("ERROR en getGestiones1A7: " . $e->getMessage());
            return [
                'success' => false,
                'data' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ✅ TOTALES OPTIMIZADOS - VERSIÓN COMPLETA
     */
    public static function getTotalesGestiones1A7($filtros = [])
    {
        $dbSegundo = self::getSegundometroConnection();
        $dbLegacy = self::getLegacyConnection();
        
        try {
            // Totales de Segundómetro
            $sql = "
                SELECT 
                    SUM(CASE WHEN Cierre_Actual = 'a) Current' THEN 1 ELSE 0 END) as total_current,
                    SUM(CASE WHEN Cierre_Actual = 'b) 1 a 7 dias' THEN 1 ELSE 0 END) as total_1a7,
                    COUNT(*) as total_general,
                    SUM(COALESCE(Saldo_vencido_actualizado, 0)) as total_saldo
                FROM tbl_segundometro_semana
                WHERE Cierre_Actual IN ('a) Current', 'b) 1 a 7 dias', 'c) 8 a 14 dias', 'd) 15 a 21 dias')
            ";
            
            $stmt = $dbSegundo->prepare($sql);
            $stmt->execute();
            $totales = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Obtener créditos para calcular sin gestión, campo y telefono
            $sqlCreditos = "
                SELECT Id_credito
                FROM tbl_segundometro_semana
                WHERE Cierre_Actual IN ('a) Current', 'b) 1 a 7 dias', 'c) 8 a 14 dias', 'd) 15 a 21 dias')
            ";
            
            $stmtCreditos = $dbSegundo->prepare($sqlCreditos);
            $stmtCreditos->execute();
            $creditos = $stmtCreditos->fetchAll(\PDO::FETCH_COLUMN);
            
            $total_campo = 0;
            $total_telefono = 0;
            $total_sin_gestion = count($creditos);
            
            if (!empty($creditos)) {
                $placeholders = implode(',', array_fill(0, count($creditos), '?'));
                
                $sqlLegacy = "
                    SELECT 
                        id_credit,
                        contacto
                    FROM legacy_semanal
                    WHERE id_credit IN ($placeholders)
                      AND campana LIKE 'ASIGNACION_W%_1A7'
                ";
                
                $stmtLegacy = $dbLegacy->prepare($sqlLegacy);
                $stmtLegacy->execute($creditos);
                $gestiones = $stmtLegacy->fetchAll(\PDO::FETCH_ASSOC);
                
                $creditosConGestion = [];
                
                foreach ($gestiones as $gestion) {
                    $creditosConGestion[$gestion['id_credit']] = true;
                    
                    $contacto = strtolower($gestion['contacto'] ?? '');
                    
                    if (in_array($contacto, ['whatsapp', 'telefono'])) {
                        $total_campo++;
                    }
                    if ($contacto === 'telefono') {
                        $total_telefono++;
                    }
                }
                
                $total_sin_gestion = count($creditos) - count($creditosConGestion);
            }
            
            // Calcular eficiencia
            $eficiencia_global = $totales['total_general'] > 0 
                ? ($totales['total_current'] * 100) / $totales['total_general']
                : 0;
            
            return [
                'total_current' => number_format($totales['total_current'] ?? 0),
                'total_1a7' => number_format($totales['total_1a7'] ?? 0),
                'total_sin_gestion' => number_format($total_sin_gestion),
                'total_eficiencia' => round($eficiencia_global, 1) . '%',
                'total_general' => number_format($totales['total_general'] ?? 0),
                'total_campo' => number_format($total_campo),
                'total_telefono' => number_format($total_telefono),
                'total_saldo' => '$' . number_format($totales['total_saldo'] ?? 0, 2)
            ];
            
        } catch (\Exception $e) {
            error_log("Error en getTotalesGestiones1A7: " . $e->getMessage());
            return [
                'total_current' => '0',
                'total_1a7' => '0',
                'total_sin_gestion' => '0',
                'total_eficiencia' => '0%',
                'total_general' => '0',
                'total_campo' => '0',
                'total_telefono' => '0',
                'total_saldo' => '$0.00'
            ];
        }
    }

    // Los demás métodos sin cambios
    public static function getEficiencia1A7() { 
        return [
            'success' => true, 
            'eficiencia' => 0
        ]; 
    }

    public static function getGestiones8A21() { return ['success' => true, 'data' => []]; }
    public static function getEficiencia8A21() { return ['success' => true, 'eficiencia' => 0]; }
    public static function getSeguimientoIntensidad() { return ['success' => true, 'data' => []]; }
    public static function getSeguimientoPromesasPago() { return ['success' => true, 'data' => []]; }
    public static function getKpiTotal() { return []; }
    public static function getDetalleClientes() { return []; }
    public static function getDetalleEficiencia() { return []; }
    public static function getCarteraInicioSem() { return []; }
    public static function getEspartanosMatrizBuckets() { return []; }
    public static function getMatrizBuckets() { return []; }
    public static function getMatrizBucketsMas1() { return []; }
    public static function getAuditoria() { return []; }
    public static function getAuditoria2() { return []; }
    public static function getSeguimiento() { return []; }
}