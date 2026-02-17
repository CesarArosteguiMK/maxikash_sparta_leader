<?php
// controllers/Indicadores.php

namespace Controllers;

use Core\Controller;
use Models\Indicadores as IndicadoresDao;

class Indicadores extends Controller
{
    public function index()
    {
        self::render("indicadores_view");
    }

    public function kpiTotal()
    {
        $data = IndicadoresDao::getKpiTotal();
        self::render("kpi_total", $data);
    }

   public function gestiones1A7()
{
    // Obtener datos
    $gestiones = IndicadoresDao::getGestiones1A7();
    $totales = IndicadoresDao::getTotalesGestiones1A7();
    
    //  Usar set() para cada variable (método correcto del framework)
    $this->set('gestiones', $gestiones['data'] ?? []);
    $this->set('totales', $totales);
    $this->set('success', $gestiones['success'] ?? false);
    
    //  Render sin parámetros extra
    $this->render("gestiones_1_a_7");
}

    public function eficiencia1A7()
    {
        $data = IndicadoresDao::getEficiencia1A7();
        $this->set('eficiencia', $data['data'] ?? []);
        $this->set('success', $data['success'] ?? false);
        $this->render("eficiencia_1_a_7");
    }

    public function gestiones8A21()
    {
        $data = IndicadoresDao::getGestiones8A21();
        self::render("gestiones_8_a_21", $data);
    }

    public function eficiencia8A21()
    {
        $data = IndicadoresDao::getEficiencia8A21();
        self::render("eficiencia_8_a_21", $data);
    }

    public function seguimientoIntensidad()
    {
        $data = IndicadoresDao::getSeguimientoIntensidad();
        self::render("seguimiento_de_intensidad", $data);
    }

    public function detalleClientes()
    {
        $data = IndicadoresDao::getDetalleClientes();
        self::render("detalle_clientes", $data);
    }

    public function detalleEficiencia()
    {
        $data = IndicadoresDao::getDetalleEficiencia();
        self::render("detalle_eficiencia", $data);
    }

    public function carteraInicioSem()
    {
        $data = IndicadoresDao::getCarteraInicioSem();
        self::render("cartera_inicio_de_sem", $data);
    }

    public function seguimientoPromesasPago()
    {
        $data = IndicadoresDao::getSeguimientoPromesasPago();
        self::render("seguimiento_a_promesas_de_pago", $data);
    }

    public function espartanos()
    {
        $data = IndicadoresDao::getEspartanosMatrizBuckets();
        self::render("espartanos", $data);
    }

    public function matrizBuckets()
    {
        $data = IndicadoresDao::getMatrizBuckets();
        self::render("matriz_de_buckets", $data);
    }

    public function matrizBucketsMas1()
    {
        $data = IndicadoresDao::getMatrizBucketsMas1();
        self::render("matriz_de_buckets_mas_1", $data);
    }

    public function auditoria()
    {
        $data = IndicadoresDao::getAuditoria();
        self::render("auditoria", $data);
    }

    public function auditoria2()
    {
        $data = IndicadoresDao::getAuditoria2();
        self::render("auditoria_2", $data);
    }

    public function seguimiento()
    {
        $data = IndicadoresDao::getSeguimiento();
        self::render("seguimiento", $data);
    }

    /**
     * API endpoint para actualización vía AJAX
     */
    public function apiGestiones1A7()
    {
        header('Content-Type: application/json');
        
        $gestiones = IndicadoresDao::getGestiones1A7();
        $totales = IndicadoresDao::getTotalesGestiones1A7();
        
        echo json_encode([
            'success' => $gestiones['success'],
            'data' => $gestiones['data'] ?? [],
            'totales' => $totales
        ]);
        exit;
    }
}