<?php

namespace Controllers;

require_once __DIR__ . '/Reporteria.php';

/**
 * Alias publico para las rutas canonicas /analitica/*.
 *
 * El modulo vive historicamente en Reporteria, pero el menu y las vistas
 * apuntan a /analitica/*. Este controlador mantiene esa URL sin duplicar
 * metodos ni tocar el router global.
 */
class Analitica extends Reporteria
{
}
