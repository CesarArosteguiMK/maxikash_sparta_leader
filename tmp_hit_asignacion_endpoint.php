<?php

$_SERVER['REQUEST_URI'] = '/analitica/getAsignacionTableroJson';
$_GET['url'] = 'analitica/getAsignacionTableroJson';
$_GET['mostrar'] = 'todas';

if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}
$_SESSION['login'] = true;
$_SESSION['modulos'] = [61];
$_SESSION['usuario_id'] = 1;
$_SESSION['persona_id'] = 1;

require __DIR__ . '/public/index.php';

