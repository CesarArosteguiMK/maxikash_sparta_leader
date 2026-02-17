<?php

namespace Controllers;

use Core\Controller;

class Perfil extends Controller
{
    /** Usuario que ve el dashboard Maxikash (id 878) */
    private static function esUsuarioDashboardMaxikash()
    {
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? 0);
        return $usuarioId === 878;
    }

    public function index()
    {
        if (!self::esUsuarioDashboardMaxikash()) {
            header('Location: /inicio');
            exit;
        }
        self::render('perfil___SPARTA_SECRET_REDACTED__', true);
    }

    /**
     * Guarda cambios de perfil (foto, nombre, correo, etc.).
     * Por ahora redirige a /perfil; luego se puede conectar con BD y subida de foto.
     */
    public function guardar()
    {
        if (!isset($_SESSION['usuario_id']) || !self::esUsuarioDashboardMaxikash()) {
            header('Location: /login');
            exit;
        }
        // TODO: procesar $_POST y $_FILES['foto'], actualizar BD y sesión
        if (!empty($_FILES['foto']['tmp_name'])) {
            // Ejemplo: guardar en backend/storage/fotos_perfil/{usuario_id}.jpg y actualizar sesión
        }
        if (!empty($_POST['nombre'])) {
            $_SESSION['usuario_nombre'] = trim((string) $_POST['nombre']);
        }
        $_SESSION['perfil_flash'] = 'Cambios guardados.';
        header('Location: /perfil');
        exit;
    }
}
