<?php

namespace controllers;

use Core\Controller;
use Services\MkValidationsService;

class DynamoValidations extends Controller
{
    public function oferta($idOferta = null, $validationType = null)
    {
        $this->json($this->service()->getByOferta((int)$idOferta, $validationType));
    }

    public function preview($limit = 25)
    {
        $this->json($this->service()->scanPreview((int)$limit));
    }

    public function coordenadas($idOferta = null)
    {
        $this->json($this->service()->getCoordenadasFirma((int)$idOferta));
    }

    private function service(): MkValidationsService
    {
        require_once RAIZ . '/bootstrap_composer.php';
        sparta_require_composer_autoload();

        return new MkValidationsService();
    }

    private function json(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }
}
