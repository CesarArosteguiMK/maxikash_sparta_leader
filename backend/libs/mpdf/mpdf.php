<?php

require_once dirname(__DIR__, 2) . '/bootstrap_composer.php';
sparta_require_composer_autoload();

class mPDF extends \Mpdf\Mpdf
{
    public function __construct($configuracion = null)
    {
        parent::__construct($configuracion);
    }
}
