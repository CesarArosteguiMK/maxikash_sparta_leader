<?php

namespace Controllers;

use Core\Controller;
use Models\Empresa as EmpresasDAO;

require_once dirname(__DIR__) . '/cronjobs/PrimerosPagosAutoSwitch.php';

class ReporteriaBI extends Controller
{
    public function FlujoCobranza()
    {
        $script = "";
        self::set("titulo", "Capital Humano");
        self::set("script", $script);
        self::render("reporte_flujocobranza");
    }

}
