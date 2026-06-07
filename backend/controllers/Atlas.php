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
        self::respuestaJSON(AtlasDAO::getSucursales());
    }

    public function getCatalogos()
    {
        self::respuestaJSON(AtlasDAO::getCatalogos());
    }

    public function guardarSucursal()
    {
        self::respuestaJSON(AtlasDAO::guardarSucursal($this->payload()));
    }

    public function guardarDivision()
    {
        self::respuestaJSON(AtlasDAO::guardarDivision($this->payload()));
    }

    public function guardarDistribuidor()
    {
        self::respuestaJSON(AtlasDAO::guardarDistribuidor($this->payload()));
    }

    public function guardarDiversificacion()
    {
        self::respuestaJSON(AtlasDAO::guardarDiversificacion($this->payload()));
    }

    public function guardarClasificacion()
    {
        self::respuestaJSON(AtlasDAO::guardarClasificacion($this->payload()));
    }

    public function guardarOrdenClasificaciones()
    {
        self::respuestaJSON(AtlasDAO::guardarOrdenClasificaciones($this->payload()));
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
}
