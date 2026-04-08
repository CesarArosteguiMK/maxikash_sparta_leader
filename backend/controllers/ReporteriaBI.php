<?php

namespace Controllers;

use Core\Controller;

class ReporteriaBI extends Controller
{
    public function FlujoCobranza()
    {
        self::set('titulo', 'Flujo cobranza');
        self::set('script', '');
        self::render('reporte_flujocobranza');
    }
}
