<?php
// models/Indicadores.php - VERSIÓN CORREGIDA SEGÚN POWERBI
// Optimizada con cierre de conexiones y lógica de negocio alineada

namespace Models;

use Core\Model;

class Indicadores extends Model
{
    private static function getSegundometroConnection()
    {
           $database = new \Core\DatabaseSegundometro();
           $reflection = new \ReflectionClass($database);  // Una sola barra
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
     * ✅ GESTIONES 1 A 7 - CORREGIDA SEGÚN POWERBI
     * - Eficiencia = Current / (Current + 1a7 + 8a14 + 15a21)
     * - Fechas solo del día actual
     * - WhatsApp agregado
     */
    public static function getGestiones1A7($filtros = [])
    {
        $dbSegundo = null;
        $dbLegacy = null;
        
        try {
            // ==========================================
            // PASO 1: Obtener líderes con TODOS los datos base
            // ==========================================
            $dbSegundo = self::getSegundometroConnection();
            
            $sqlLideres = "
                SELECT 
                    CASE 
                        WHEN Observaciones IS NULL OR TRIM(Observaciones) = '' THEN 'SIN ASIGNAR'
                        ELSE Observaciones 
                    END as lider,
                    COUNT(*) as total_creditos,
                    SUM(CASE WHEN Cierre_Actual = 'a) Current' THEN 1 ELSE 0 END) as current,
                    SUM(CASE WHEN Cierre_Actual = 'b) 1 a 7 dias' THEN 1 ELSE 0 END) as gestiones_1a7,
                    SUM(CASE WHEN Cierre_Actual = 'c) 8 a 14 dias' THEN 1 ELSE 0 END) as gestiones_8a14,
                    SUM(CASE WHEN Cierre_Actual = 'd) 15 a 21 dias' THEN 1 ELSE 0 END) as gestiones_15a21,
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
            $stmtLideres->closeCursor();
            
            if (empty($lideres)) {
                return [
                    'success' => true,
                    'data' => [],
                    'totales' => [
                        'total_current' => '0',
                        'total_1a7' => '0',
                        'total_8a14' => '0',
                        'total_15a21' => '0',
                        'total_sin_gestion' => '0',
                        'total_eficiencia' => '0%',
                        'total_general' => '0',
                        'total_campo' => '0',
                        'total_telefono' => '0',
                        'total_whatsapp' => '0',
                        'total_saldo' => '$0.00'
                    ]
                ];
            }
            
            // ==========================================
            // PASO 2: Obtener créditos con su líder
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
            $todosLosCreditos = [];
            
            while ($row = $stmtCreditos->fetch(\PDO::FETCH_ASSOC)) {
                $creditosPorLider[$row['lider']][] = $row['Id_credito'];
                $todosLosCreditos[] = $row['Id_credito'];
            }
            $stmtCreditos->closeCursor();
            $dbSegundo = null; // ✅ Cerrar Segundómetro
            
            // ==========================================
            // PASO 3: UNA SOLA query a Legacy para TODO
            // ==========================================
            $dbLegacy = self::getLegacyConnection();
            
            $gestionesPorCredito = [];
            $hoy = date('Y-m-d'); // Para filtrar fechas del día actual
            
            if (!empty($todosLosCreditos)) {
                $chunks = array_chunk($todosLosCreditos, 5000);
                
                foreach ($chunks as $chunk) {
                    $placeholders = implode(',', array_fill(0, count($chunk), '?'));
                    
                    $sqlLegacy = "
                        SELECT 
                            id_credit,
                            contacto,
                            fecha_dictamen
                        FROM legacy_semanal
                        WHERE id_credit IN ($placeholders)
                          AND campana LIKE 'ASIGNACION_W%_1A7'
                          AND DATE(fecha_dictamen) = ?  -- Solo gestiones de HOY
                    ";
                    
                    $stmtLegacy = $dbLegacy->prepare($sqlLegacy);
                    // Agregar la fecha actual al final de los parámetros
                    $params = array_merge($chunk, [$hoy]);
                    $stmtLegacy->execute($params);
                    
                    while ($row = $stmtLegacy->fetch(\PDO::FETCH_ASSOC)) {
                        $gestionesPorCredito[$row['id_credit']][] = [
                            'contacto' => strtolower($row['contacto'] ?? ''),
                            'fecha_dictamen' => $row['fecha_dictamen']
                        ];
                    }
                    $stmtLegacy->closeCursor();
                }
            }
            
            $dbLegacy = null; // ✅ Cerrar Legacy
            
            // ==========================================
            // PASO 4: Procesar por líder + calcular totales
            // ==========================================
            $resultado = [];
            
            // Acumuladores para totales
            $totales_current = 0;
            $totales_1a7 = 0;
            $totales_8a14 = 0;
            $totales_15a21 = 0;
            $totales_sin_gestion = 0;
            $totales_general = 0;
            $totales_campo = 0;
            $totales_telefono = 0;
            $totales_whatsapp = 0;
            $totales_saldo = 0;
            
            foreach ($lideres as $lider) {
                $nombreLider = $lider['lider'];
                $creditos = $creditosPorLider[$nombreLider] ?? [];
                
                // Inicializar contadores
                $campo = 0;
                $telefono = 0;
                $whatsapp = 0;
                $fechaAntigua = null;
                $fechaReciente = null;
                $creditosConGestion = [];
                
                // Procesar gestiones de ESTE líder (solo las de hoy)
                foreach ($creditos as $idCredito) {
                    if (isset($gestionesPorCredito[$idCredito])) {
                        $creditosConGestion[$idCredito] = true;
                        
                        foreach ($gestionesPorCredito[$idCredito] as $gestion) {
                            // Las gestiones ya vienen filtradas por fecha_dictamen = hoy
                            $contacto = $gestion['contacto'];
                            
                            // Contar contactos
                            if (in_array($contacto, ['whatsapp', 'telefono'])) {
                                $campo++;
                            }
                            if ($contacto === 'telefono') {
                                $telefono++;
                            }
                            if ($contacto === 'whatsapp') {
                                $whatsapp++;
                            }
                            
                            // Calcular fechas (solo de hoy)
                            if ($gestion['fecha_dictamen']) {
                                if (!$fechaAntigua || $gestion['fecha_dictamen'] < $fechaAntigua) {
                                    $fechaAntigua = $gestion['fecha_dictamen'];
                                }
                                if (!$fechaReciente || $gestion['fecha_dictamen'] > $fechaReciente) {
                                    $fechaReciente = $gestion['fecha_dictamen'];
                                }
                            }
                        }
                    }
                }
                
                // Calcular sin gestión
                $sinGestion = count($creditos) - count($creditosConGestion);
                
                // Calcular estatus (según PowerBI: 30 minutos)
                $estatus = 'Sin actividad';
                if ($fechaReciente) {
                    $minutos = (time() - strtotime($fechaReciente)) / 60;
                    if ($minutos < 30) {
                        $estatus = 'Activo';
                    } else {
                        $estatus = 'Inactivo';
                    }
                }
                
                // 🔴 CORREGIDO: Eficiencia según PowerBI
                // = Current / (Current + 1a7 + 8a14 + 15a21)
                $total_base = $lider['current'] + $lider['gestiones_1a7'] + 
                              $lider['gestiones_8a14'] + $lider['gestiones_15a21'];
                $eficiencia = $total_base > 0 ? round(($lider['current'] * 100) / $total_base, 1) : 0;
                
                // ✅ Acumular para totales
                $totales_current += $lider['current'];
                $totales_1a7 += $lider['gestiones_1a7'];
                $totales_8a14 += $lider['gestiones_8a14'];
                $totales_15a21 += $lider['gestiones_15a21'];
                $totales_sin_gestion += $sinGestion;
                $totales_general += $lider['total_creditos'];
                $totales_campo += $campo;
                $totales_telefono += $telefono;
                $totales_whatsapp += $whatsapp;
                $totales_saldo += $lider['saldo_vencido'];
                
                // Armar resultado final
                $resultado[] = [
                    'lider' => $nombreLider,
                    'current' => (int)$lider['current'],
                    'gestiones_1a7' => (int)$lider['gestiones_1a7'],
                    'gestiones_8a14' => (int)$lider['gestiones_8a14'],
                    'gestiones_15a21' => (int)$lider['gestiones_15a21'],
                    'sin_gestion' => $sinGestion,
                    'eficiencia' => $eficiencia,
                    'total_general' => (int)$lider['total_creditos'],
                    'campo' => $campo,
                    'telefono' => $telefono,
                    'whatsapp' => $whatsapp,
                    'saldo_vencido' => '$' . number_format($lider['saldo_vencido'] ?? 0, 2),
                    'fecha_dictamen_mas_antigua' => $fechaAntigua ? date('d/m/Y H:i', strtotime($fechaAntigua)) : '-',
                    'fecha_dictamen_mas_reciente' => $fechaReciente ? date('d/m/Y H:i', strtotime($fechaReciente)) : '-',
                    'estatus_gestor' => $estatus
                ];
            }
            
            // Calcular eficiencia global (según PowerBI)
            $total_base_global = $totales_current + $totales_1a7 + $totales_8a14 + $totales_15a21;
            $eficiencia_global = $total_base_global > 0 
                ? ($totales_current * 100) / $total_base_global
                : 0;
            
            return [
                'success' => true,
                'data' => $resultado,
                'totales' => [
                    'total_current' => number_format($totales_current),
                    'total_1a7' => number_format($totales_1a7),
                    'total_8a14' => number_format($totales_8a14),
                    'total_15a21' => number_format($totales_15a21),
                    'total_sin_gestion' => number_format($totales_sin_gestion),
                    'total_eficiencia' => round($eficiencia_global, 1) . '%',
                    'total_general' => number_format($totales_general),
                    'total_campo' => number_format($totales_campo),
                    'total_telefono' => number_format($totales_telefono),
                    'total_whatsapp' => number_format($totales_whatsapp),
                    'total_saldo' => '$' . number_format($totales_saldo, 2)
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("ERROR en getGestiones1A7: " . $e->getMessage());
            $dbSegundo = null;
            $dbLegacy = null;
            
            return [
                'success' => false,
                'data' => [],
                'totales' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ✅ EFICIENCIA 1 A 7 - VERSIÓN COMPLETA OPTIMIZADA
     * (Sin cambios, ya estaba bien)
     */
    public static function getEficiencia1A7()
    {
        $dbSegundo = null;
        $dbLegacy = null;
        
        try {
            // ==========================================
            // PASO 1: Obtener líderes
            // ==========================================
            $dbSegundo = self::getSegundometroConnection();
            
            $sqlLideres = "
                SELECT 
                    CASE 
                        WHEN Observaciones IS NULL OR TRIM(Observaciones) = '' THEN 'SIN ASIGNAR'
                        ELSE Observaciones 
                    END as lider,
                    COUNT(*) as total_creditos,
                    SUM(CASE WHEN Cierre_Actual = 'a) Current' THEN 1 ELSE 0 END) as current,
                    SUM(CASE WHEN Cierre_Actual = 'b) 1 a 7 dias' THEN 1 ELSE 0 END) as gestiones_1a7
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
            $stmtLideres->closeCursor();
            
            if (empty($lideres)) {
                return ['success' => true, 'data' => []];
            }
            
            // ==========================================
            // PASO 2: Obtener créditos con su líder
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
            $todosLosCreditos = [];
            
            while ($row = $stmtCreditos->fetch(\PDO::FETCH_ASSOC)) {
                $creditosPorLider[$row['lider']][] = $row['Id_credito'];
                $todosLosCreditos[] = $row['Id_credito'];
            }
            $stmtCreditos->closeCursor();
            $dbSegundo = null;
            
            // ==========================================
            // PASO 3: UNA SOLA query a Legacy
            // ==========================================
            $dbLegacy = self::getLegacyConnection();
            
            $gestionesPorCredito = [];
            
            if (!empty($todosLosCreditos)) {
                $chunks = array_chunk($todosLosCreditos, 5000);
                
                foreach ($chunks as $chunk) {
                    $placeholders = implode(',', array_fill(0, count($chunk), '?'));
                    
                    $sqlLegacy = "
                        SELECT 
                            id_credit,
                            dictamen_for,
                            contacto
                        FROM legacy_semanal
                        WHERE id_credit IN ($placeholders)
                          AND campana LIKE 'ASIGNACION_W%_1A7'
                    ";
                    
                    $stmtLegacy = $dbLegacy->prepare($sqlLegacy);
                    $stmtLegacy->execute($chunk);
                    
                    while ($row = $stmtLegacy->fetch(\PDO::FETCH_ASSOC)) {
                        $gestionesPorCredito[$row['id_credit']][] = [
                            'dictamen' => $row['dictamen_for'],
                            'contacto' => strtolower($row['contacto'] ?? '')
                        ];
                    }
                    $stmtLegacy->closeCursor();
                }
            }
            
            $dbLegacy = null;
            
            // ==========================================
            // PASO 4: Procesar resultados por líder
            // ==========================================
            $dictamenes = [
                'Pago Recibido' => 'pagos_recibidos',
                'Promesa de Pago' => 'promesa_pago',
                'Negativa de Pago' => 'negativa_pago',
                'Prestanombre' => 'prestanombre',
                'Contacto con Tercero' => 'contacto_tercero',
                'No Contesta la Llamada' => 'no_contesta',
                'Sin Contacto' => 'sin_contacto',
                'Illocalizable' => 'illocalizable',
                'Pago No Identificado' => 'pago_no_identificado',
                'Cambio de Domicilio' => 'cambio_domicilio',
                'Convenio Pago Parcial' => 'convenio_pago_parcial',
                'Moto Recuperada' => 'moto_recuperada',
                'Siniestro' => 'siniestro',
                'Defunción' => 'defuncion'
            ];
            
            $resultado = [];
            
            foreach ($lideres as $lider) {
                $nombreLider = $lider['lider'];
                $creditos = $creditosPorLider[$nombreLider] ?? [];
                
                $conteos = array_fill_keys(array_values($dictamenes), 0);
                $conteos['campo'] = 0;
                $conteos['telefono'] = 0;
                
                $creditosConGestion = [];
                
                foreach ($creditos as $idCredito) {
                    if (isset($gestionesPorCredito[$idCredito])) {
                        $creditosConGestion[$idCredito] = true;
                        
                        foreach ($gestionesPorCredito[$idCredito] as $gestion) {
                            if (isset($dictamenes[$gestion['dictamen']])) {
                                $columna = $dictamenes[$gestion['dictamen']];
                                $conteos[$columna]++;
                            }
                            
                            if (in_array($gestion['contacto'], ['whatsapp', 'telefono'])) {
                                $conteos['campo']++;
                            }
                            if ($gestion['contacto'] === 'telefono') {
                                $conteos['telefono']++;
                            }
                        }
                    }
                }
                
                $sinGestion = count($creditos) - count($creditosConGestion);
                $totalGeneral = $lider['current'] + $lider['gestiones_1a7'];
                $gestionados = max($totalGeneral - $sinGestion, 0);
                
                $eficiencia = $gestionados > 0 
                    ? round((($conteos['pagos_recibidos'] + $conteos['promesa_pago']) * 100) / $gestionados, 1)
                    : 0;
                
                $resultado[] = [
                    'lider' => $nombreLider,
                    'current' => (int)$lider['current'],
                    'gestiones_1a7' => (int)$lider['gestiones_1a7'],
                    'total_general' => $totalGeneral,
                    'sin_gestion' => $sinGestion,
                    'eficiencia' => $eficiencia,
                    'pagos_recibidos' => $conteos['pagos_recibidos'],
                    'promesa_pago' => $conteos['promesa_pago'],
                    'negativa_pago' => $conteos['negativa_pago'],
                    'prestanombre' => $conteos['prestanombre'],
                    'contacto_tercero' => $conteos['contacto_tercero'],
                    'no_contesta' => $conteos['no_contesta'],
                    'sin_contacto' => $conteos['sin_contacto'],
                    'illocalizable' => $conteos['illocalizable'],
                    'pago_no_identificado' => $conteos['pago_no_identificado'],
                    'cambio_domicilio' => $conteos['cambio_domicilio'],
                    'convenio_pago_parcial' => $conteos['convenio_pago_parcial'],
                    'moto_recuperada' => $conteos['moto_recuperada'],
                    'siniestro' => $conteos['siniestro'],
                    'defuncion' => $conteos['defuncion']
                ];
            }
            
            return [
                'success' => true,
                'data' => $resultado
            ];
            
        } catch (\Exception $e) {
            error_log("ERROR en getEficiencia1A7: " . $e->getMessage());
            $dbSegundo = null;
            $dbLegacy = null;
            
            return [
                'success' => false,
                'data' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ✅ GESTIONES 8 A 21 - VERSIÓN COMPLETA OPTIMIZADA
     * (Ya estaba correcta, solo ajustamos fechas)
     */
    public static function getGestiones8A21($filtros = [])
    {
        $dbSegundo = null;
        $dbLegacy = null;
        
        try {
            // ==========================================
            // PASO 1: Obtener líderes con datos base
            // ==========================================
            $dbSegundo = self::getSegundometroConnection();
            
            $sqlLideres = "
                SELECT 
                    CASE 
                        WHEN Observaciones IS NULL OR TRIM(Observaciones) = '' THEN 'SIN ASIGNAR'
                        ELSE Observaciones 
                    END as lider,
                    COUNT(*) as total_creditos,
                    SUM(CASE WHEN Cierre_Actual = 'a) Current' THEN 1 ELSE 0 END) as current,
                    SUM(CASE WHEN Cierre_Actual = 'b) 1 a 7 dias' THEN 1 ELSE 0 END) as gestiones_1a7,
                    SUM(CASE WHEN Cierre_Actual = 'c) 8 a 14 dias' THEN 1 ELSE 0 END) as gestiones_8a14,
                    SUM(CASE WHEN Cierre_Actual = 'd) 15 a 21 dias' THEN 1 ELSE 0 END) as gestiones_15a21,
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
            $stmtLideres->closeCursor();
            
            if (empty($lideres)) {
                return [
                    'success' => true,
                    'data' => [],
                    'totales' => [
                        'total_current' => '0',
                        'total_1a7' => '0',
                        'total_8a14' => '0',
                        'total_15a21' => '0',
                        'total_sin_gestion' => '0',
                        'total_eficiencia' => '0%',
                        'total_general' => '0',
                        'total_campo' => '0',
                        'total_telefono' => '0',
                        'total_whatsapp' => '0',
                        'total_saldo' => '$0.00'
                    ]
                ];
            }
            
            // ==========================================
            // PASO 2: Obtener créditos con su líder
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
            $todosLosCreditos = [];
            
            while ($row = $stmtCreditos->fetch(\PDO::FETCH_ASSOC)) {
                $creditosPorLider[$row['lider']][] = $row['Id_credito'];
                $todosLosCreditos[] = $row['Id_credito'];
            }
            $stmtCreditos->closeCursor();
            $dbSegundo = null;
            
            // ==========================================
            // PASO 3: UNA SOLA query a Legacy (solo hoy)
            // ==========================================
            $dbLegacy = self::getLegacyConnection();
            
            $gestionesPorCredito = [];
            $hoy = date('Y-m-d');
            
            if (!empty($todosLosCreditos)) {
                $chunks = array_chunk($todosLosCreditos, 5000);
                
                foreach ($chunks as $chunk) {
                    $placeholders = implode(',', array_fill(0, count($chunk), '?'));
                    
                    $sqlLegacy = "
                        SELECT 
                            id_credit,
                            contacto,
                            fecha_dictamen
                        FROM legacy_semanal
                        WHERE id_credit IN ($placeholders)
                          AND campana LIKE 'ASIGNACION_W%_8A21'
                          AND DATE(fecha_dictamen) = ?
                    ";
                    
                    $stmtLegacy = $dbLegacy->prepare($sqlLegacy);
                    $params = array_merge($chunk, [$hoy]);
                    $stmtLegacy->execute($params);
                    
                    while ($row = $stmtLegacy->fetch(\PDO::FETCH_ASSOC)) {
                        $gestionesPorCredito[$row['id_credit']][] = [
                            'contacto' => strtolower($row['contacto'] ?? ''),
                            'fecha_dictamen' => $row['fecha_dictamen']
                        ];
                    }
                    $stmtLegacy->closeCursor();
                }
            }
            
            $dbLegacy = null;
            
            // ==========================================
            // PASO 4: Procesar por líder + calcular totales
            // ==========================================
            $resultado = [];
            
            $totales_current = 0;
            $totales_1a7 = 0;
            $totales_8a14 = 0;
            $totales_15a21 = 0;
            $totales_sin_gestion = 0;
            $totales_general = 0;
            $totales_campo = 0;
            $totales_telefono = 0;
            $totales_whatsapp = 0;
            $totales_saldo = 0;
            
            foreach ($lideres as $lider) {
                $nombreLider = $lider['lider'];
                $creditos = $creditosPorLider[$nombreLider] ?? [];
                
                $campo = 0;
                $telefono = 0;
                $whatsapp = 0;
                $fechaAntigua = null;
                $fechaReciente = null;
                $creditosConGestion = [];
                
                foreach ($creditos as $idCredito) {
                    if (isset($gestionesPorCredito[$idCredito])) {
                        $creditosConGestion[$idCredito] = true;
                        
                        foreach ($gestionesPorCredito[$idCredito] as $gestion) {
                            $contacto = $gestion['contacto'];
                            
                            if (in_array($contacto, ['whatsapp', 'telefono'])) {
                                $campo++;
                            }
                            if ($contacto === 'telefono') {
                                $telefono++;
                            }
                            if ($contacto === 'whatsapp') {
                                $whatsapp++;
                            }
                            
                            if ($gestion['fecha_dictamen']) {
                                if (!$fechaAntigua || $gestion['fecha_dictamen'] < $fechaAntigua) {
                                    $fechaAntigua = $gestion['fecha_dictamen'];
                                }
                                if (!$fechaReciente || $gestion['fecha_dictamen'] > $fechaReciente) {
                                    $fechaReciente = $gestion['fecha_dictamen'];
                                }
                            }
                        }
                    }
                }
                
                $sinGestion = count($creditos) - count($creditosConGestion);
                
                $estatus = 'Sin actividad';
                if ($fechaReciente) {
                    $minutos = (time() - strtotime($fechaReciente)) / 60;
                    if ($minutos < 30) {
                        $estatus = 'Activo';
                    } else {
                        $estatus = 'Inactivo';
                    }
                }
                
                // Eficiencia específica para 8-21
                $total_base_8a21 = $lider['gestiones_8a14'] + $lider['gestiones_15a21'];
                $eficiencia = $total_base_8a21 > 0 
                    ? round(($lider['current'] * 100) / $total_base_8a21, 1) 
                    : 0;
                
                $totales_current += $lider['current'];
                $totales_1a7 += $lider['gestiones_1a7'];
                $totales_8a14 += $lider['gestiones_8a14'];
                $totales_15a21 += $lider['gestiones_15a21'];
                $totales_sin_gestion += $sinGestion;
                $totales_general += $lider['total_creditos'];
                $totales_campo += $campo;
                $totales_telefono += $telefono;
                $totales_whatsapp += $whatsapp;
                $totales_saldo += $lider['saldo_vencido'];
                
                $resultado[] = [
                    'lider' => $nombreLider,
                    'current' => (int)$lider['current'],
                    'gestiones_1a7' => (int)$lider['gestiones_1a7'],
                    'gestiones_8a14' => (int)$lider['gestiones_8a14'],
                    'gestiones_15a21' => (int)$lider['gestiones_15a21'],
                    'sin_gestion' => $sinGestion,
                    'estatus_gestor' => $estatus,
                    'total_general' => (int)$lider['total_creditos'],
                    'eficiencia' => $eficiencia,
                    'campo' => $campo,
                    'telefono' => $telefono,
                    'whatsapp' => $whatsapp,
                    'saldo_vencido' => '$' . number_format($lider['saldo_vencido'] ?? 0, 2),
                    'fecha_dictamen_mas_antigua' => $fechaAntigua ? date('d/m/Y H:i', strtotime($fechaAntigua)) : '-',
                    'fecha_dictamen_mas_reciente' => $fechaReciente ? date('d/m/Y H:i', strtotime($fechaReciente)) : '-',
                    'estatus_gestor_detalle' => $estatus
                ];
            }
            
            $total_base_global = $totales_8a14 + $totales_15a21;
            $eficiencia_global = $total_base_global > 0 
                ? ($totales_current * 100) / $total_base_global
                : 0;
            
            return [
                'success' => true,
                'data' => $resultado,
                'totales' => [
                    'total_current' => number_format($totales_current),
                    'total_1a7' => number_format($totales_1a7),
                    'total_8a14' => number_format($totales_8a14),
                    'total_15a21' => number_format($totales_15a21),
                    'total_sin_gestion' => number_format($totales_sin_gestion),
                    'total_eficiencia' => round($eficiencia_global, 1) . '%',
                    'total_general' => number_format($totales_general),
                    'total_campo' => number_format($totales_campo),
                    'total_telefono' => number_format($totales_telefono),
                    'total_whatsapp' => number_format($totales_whatsapp),
                    'total_saldo' => '$' . number_format($totales_saldo, 2)
                ]
            ];
            
        } catch (\Exception $e) {
            error_log("ERROR en getGestiones8A21: " . $e->getMessage());
            $dbSegundo = null;
            $dbLegacy = null;
            
            return [
                'success' => false,
                'data' => [],
                'totales' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    
    // ==========================================
    // MÉTODOS PENDIENTES (placeholder)
    // ==========================================
    public static function getEficiencia8A21() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getSeguimientoIntensidad() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getSeguimientoPromesasPago() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getKpiTotal() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getDetalleClientes() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getDetalleEficiencia() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getCarteraInicioSem() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getEspartanosMatrizBuckets() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getMatrizBuckets() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getMatrizBucketsMas1() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getAuditoria() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getAuditoria2() 
    { 
        return ['success' => true, 'data' => []]; 
    }
    
    public static function getSeguimiento() 
    { 
        return ['success' => true, 'data' => []]; 
    }
}