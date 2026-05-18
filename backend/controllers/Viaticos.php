<?php

namespace Controllers;

use Core\Controller;
use Core\TicketsPanelModuloHelper;

class Viaticos extends Controller
{
    public function paneladmin()
    {
        TicketsPanelModuloHelper::renderModuloPanel($this, 'ausencia');
    }
}
