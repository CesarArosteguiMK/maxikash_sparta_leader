<?php

namespace Controllers;

use Core\Controller;
use Core\TicketsPanelModuloHelper;

class Atencioncliente extends Controller
{
    public function paneladmin()
    {
        TicketsPanelModuloHelper::renderModuloPanel($this, 'atencion_cliente');
    }
}
