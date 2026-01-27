<?php

namespace controllers;

use Core\Controller;
use Models\EquivalenciasPuestos as EquivalenciasDAO;

class Equivalencias extends Controller
{
    public function consulta()
    {
        self::set("titulo", "Equivalencia puestos");
        self::render("equivalencias_puestos");
    }

    public function getPuestosLegacy()
    {
        EquivalenciasDAO::getPuestosLegacy();
    }

    public function getPuestosSparta()
    {
        EquivalenciasDAO::getPuestosSparta();
    }

    public function getEquivalencias()
    {
        EquivalenciasDAO::getEquivalencias();
    }

    public function guardarEquivalencias()
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, true);
        $pares = $body['equivalencias'] ?? $_POST['equivalencias'] ?? [];
        if (is_string($pares)) {
            $pares = json_decode($pares, true) ?: [];
        }
        EquivalenciasDAO::guardarEquivalencias($pares);
    }
}
