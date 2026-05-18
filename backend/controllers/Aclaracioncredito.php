<?php

namespace Controllers;

use Core\Controller;
use Core\TicketsPanelModuloHelper;

class Aclaracioncredito extends Controller
{
    public function paneladmin()
    {
        TicketsPanelModuloHelper::renderModuloPanel($this, 'incidencias_cartera');
    }
}
