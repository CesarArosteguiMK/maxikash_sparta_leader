<?php

namespace Controllers;

use Core\Controller;
use Models\ConfigTicketPuesto as ConfigTicketPuestoDAO;
use Models\ConfigEstadisticasPuesto as ConfigEstadisticasPuestoDAO;
use Models\ConfigPanelUsuario as ConfigPanelUsuarioDAO;

class ConfigTicketPuesto extends Controller
{
    public function consulta()
    {
        self::set('titulo', 'Asignación por puestos');
        self::set('funciones', ConfigTicketPuestoDAO::FUNCIONES);
        self::set('tiposEstadisticas', ConfigEstadisticasPuestoDAO::TIPOS);
        self::set('panelesAdmin', ConfigPanelUsuarioDAO::PANELES);
        self::render('config_ticket_puesto');
    }

    public function getPuestosSparta()
    {
        ConfigTicketPuestoDAO::getPuestosSparta();
    }

    public function getConfig()
    {
        ConfigTicketPuestoDAO::getConfig();
    }

    public function guardar()
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, true);
        $pares = $body['config'] ?? $_POST['config'] ?? [];
        if (is_string($pares)) {
            $pares = json_decode($pares, true) ?: [];
        }
        ConfigTicketPuestoDAO::guardar($pares);
    }

    public function getConfigEstadisticas()
    {
        ConfigEstadisticasPuestoDAO::getConfig();
    }

    public function guardarEstadisticas()
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, true);
        $pares = $body['config'] ?? $_POST['config'] ?? [];
        if (is_string($pares)) {
            $pares = json_decode($pares, true) ?: [];
        }
ConfigEstadisticasPuestoDAO::guardar($pares);
    }

    public function getUsuariosPanel()
    {
        ConfigPanelUsuarioDAO::getUsuarios();
    }

    public function getConfigPanelUsuario()
    {
        ConfigPanelUsuarioDAO::getConfig();
    }

    public function guardarPanelUsuario()
    {
        $json = file_get_contents('php://input');
        $body = json_decode($json, true);
        $pares = $body['config'] ?? $_POST['config'] ?? [];
        if (is_string($pares)) {
            $pares = json_decode($pares, true) ?: [];
        }
        ConfigPanelUsuarioDAO::guardar($pares);
    }
}
