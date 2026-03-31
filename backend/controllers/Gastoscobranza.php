<?php

namespace Controllers;

use Core\Controller;

/**
 * Shell Gastos Cobranza — agente / reporte_cobranza.py (iterativo).
 * Permisos: módulo web id 31 (rutas en public/index.php).
 */
class Gastoscobranza extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Vista principal (Configuración → Shell Gastos Cobranza).
     * Próximos pasos: agente HTTP, ejecución manual, logs, descarga Excel, correo.
     */
    public function shell()
    {
        $this->set('tituloShell', 'Shell Gastos Cobranza');
        self::render('shell_gastos_cobranza');
    }
}
