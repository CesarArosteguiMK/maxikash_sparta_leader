<?php

namespace controllers;

use Core\Controller;
use Models\Atlas as AtlasDAO;

class Atlas extends Controller
{
    public function atlas()
    {
        header('Location: /Atlas/sucursales');
        exit;
    }

    public function sucursales()
    {
        $this->set('titulo', 'Atlas');
        $this->set('google_maps_api_key_js', json_encode(defined('GOOGLE_MAPS_API_KEY') ? (string)GOOGLE_MAPS_API_KEY : '', JSON_UNESCAPED_SLASHES));
        $this->render('atlas');
    }

    public function getSucursales()
    {
        $this->json(AtlasDAO::getSucursales());
    }

    public function getCatalogos()
    {
        $this->json(AtlasDAO::getCatalogos());
    }

    public function guardarSucursal()
    {
        $this->json(AtlasDAO::guardarSucursal($this->payload()));
    }

    public function guardarDivision()
    {
        $this->json(AtlasDAO::guardarDivision($this->payload()));
    }

    public function guardarDistribuidor()
    {
        $this->json(AtlasDAO::guardarDistribuidor($this->payload()));
    }

    public function guardarDiversificacion()
    {
        $this->json(AtlasDAO::guardarDiversificacion($this->payload()));
    }

    public function guardarClasificacion()
    {
        $this->json(AtlasDAO::guardarClasificacion($this->payload()));
    }

    public function guardarOrdenClasificaciones()
    {
        $this->json(AtlasDAO::guardarOrdenClasificaciones($this->payload()));
    }

    private function payload(): array
    {
        $raw = file_get_contents('php://input');
        $json = json_decode((string)$raw, true);
        if (is_array($json)) {
            return $json;
        }
        return $_POST ?: [];
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
