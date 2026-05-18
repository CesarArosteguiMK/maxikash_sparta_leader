<?php

namespace Controllers;

use Core\Controller;
use Core\TicketsPanelModuloHelper;

class Creditoproblematico extends Controller
{
    public function paneladmin()
    {
        TicketsPanelModuloHelper::renderModuloPanel($this, 'pagos_no_identificados');
    }
}
