<?php

namespace Controllers;

use Core\Controller;
use Core\TicketsPanelModuloHelper;

class Plantilla extends Controller
{
    public function paneladmin()
    {
        TicketsPanelModuloHelper::renderModuloPanel($this, 'plantilla');
    }
}
