<?php

namespace Controllers;

use Core\Controller;
use Core\TicketsPanelModuloHelper;

class Aplicacionespago extends Controller
{
    public function paneladmin()
    {
        TicketsPanelModuloHelper::renderModuloPanel($this, 'aplicaciones_de_pago');
    }
}
