<?php

namespace Controllers;

use Core\Controller;
use Models\Perfil as PerfilDao;

class Perfil extends Controller
{
    /** Combina datos de perfil y persona: si no hay en perfil, usa persona (__SPARTA_SECRET_REDACTED__). */
    private static function mergePerfilPersona($perfil, $persona)
    {
        $nombreCompleto = $_SESSION['usuario_nombre'] ?? 'Usuario';
        $datos = [
            'nombre' => $nombreCompleto,
            'apellidopat' => '',
            'apellidomat' => '',
            'telefono' => '',
            'correo' => '',
            'direccion' => '',
            'username' => $_SESSION['usuario'] ?? '',
        ];
        if ($persona) {
            $datos['nombre'] = trim(($persona['nombres'] ?? '') . ' ' . ($persona['segundo_nombre'] ?? '') . ' ' . ($persona['apellidop'] ?? '') . ' ' . ($persona['apellidom'] ?? ''));
            $datos['apellidopat'] = $persona['apellidop'] ?? '';
            $datos['apellidomat'] = $persona['apellidom'] ?? '';
            $datos['username'] = $persona['user_name'] ?? $datos['username'];
        }
        if ($perfil) {
            if (isset($perfil['nombre']) && $perfil['nombre'] !== null && $perfil['nombre'] !== '') {
                $datos['nombre'] = $perfil['nombre'];
            }
            if (isset($perfil['apellidopat'])) {
                $datos['apellidopat'] = $perfil['apellidopat'];
            }
            if (isset($perfil['apellidomat'])) {
                $datos['apellidomat'] = $perfil['apellidomat'];
            }
            if (isset($perfil['telefono'])) {
                $datos['telefono'] = $perfil['telefono'];
            }
            if (isset($perfil['correo'])) {
                $datos['correo'] = $perfil['correo'];
            }
            if (isset($perfil['direccion'])) {
                $datos['direccion'] = $perfil['direccion'];
            }
            if (isset($perfil['username']) && $perfil['username'] !== null && $perfil['username'] !== '') {
                $datos['username'] = $perfil['username'];
            }
        }
        return $datos;
    }

    public function index()
    {
        $idPersona = (int) ($_SESSION['usuario_id'] ?? 0);
        $perfil = $idPersona > 0 ? PerfilDao::getByPersonaId($idPersona) : null;
        $persona = $idPersona > 0 ? PerfilDao::getPersonaById($idPersona) : null;
        $datos = self::mergePerfilPersona($perfil, $persona);
        $this->set('perfil', $perfil);
        $this->set('datos', $datos);
        self::render('perfil_contenido', false);
    }

    /**
     * Guarda cambios de perfil (foto, nombre, correo, teléfono) en tabla perfil.
     * La foto se guarda en public/assets/img/fotos_perfil/{id_persona}.webp y la ruta en BD.
     */
    public function guardar()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit;
        }
        $idPersona = (int) $_SESSION['usuario_id'];
        $datos = [
            'nombre' => isset($_POST['nombre']) ? trim((string) $_POST['nombre']) : null,
            'apellidopat' => isset($_POST['apellidopat']) ? trim((string) $_POST['apellidopat']) : null,
            'apellidomat' => isset($_POST['apellidomat']) ? trim((string) $_POST['apellidomat']) : null,
            'telefono' => isset($_POST['telefono']) ? trim((string) $_POST['telefono']) : null,
            'correo' => isset($_POST['correo']) ? trim((string) $_POST['correo']) : null,
            'direccion' => isset($_POST['direccion']) ? trim((string) $_POST['direccion']) : null,
            'username' => isset($_POST['username']) ? trim((string) $_POST['username']) : null,
        ];
        if (!empty($_POST['pass']) && isset($_POST['pass_confirm']) && $_POST['pass'] === $_POST['pass_confirm']) {
            $datos['pass'] = $_POST['pass'];
        }

        $projectRoot = dirname(dirname(__DIR__));
        $fotosDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'fotos_perfil';
        $fotoGuardada = false;

        if (!empty($_POST['foto_base64']) && preg_match('/^data:image\/(\w+);base64,(.+)$/', $_POST['foto_base64'], $m)) {
            $ext = strtolower($m[1]);
            if ($ext === 'jpeg') $ext = 'jpg';
            if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) $ext = 'png';
            $blob = base64_decode($m[2], true);
            if ($blob !== false && strlen($blob) > 0) {
                if (!is_dir($fotosDir)) {
                    @mkdir($fotosDir, 0755, true);
                }
                $nombreArchivo = $idPersona . '.' . $ext;
                $rutaCompleta = $fotosDir . DIRECTORY_SEPARATOR . $nombreArchivo;
                if (@file_put_contents($rutaCompleta, $blob) !== false) {
                    $rutaUrl = '/assets/img/fotos_perfil/' . $nombreArchivo;
                    $datos['foto'] = $rutaUrl;
                    PerfilDao::actualizarFoto($idPersona, $rutaUrl);
                    $_SESSION['foto_perfil'] = $rutaUrl;
                    $fotoGuardada = true;
                }
            }
        }

        if (!$fotoGuardada && !empty($_FILES['foto']['tmp_name']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
            finfo_close($finfo);
            if (in_array($mime, $allowed, true)) {
                $ext = $mime === 'image/webp' ? 'webp' : ($mime === 'image/png' ? 'png' : 'jpg');
                if (!is_dir($fotosDir)) {
                    @mkdir($fotosDir, 0755, true);
                }
                $nombreArchivo = $idPersona . '.' . $ext;
                $rutaCompleta = $fotosDir . DIRECTORY_SEPARATOR . $nombreArchivo;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaCompleta)) {
                    $rutaUrl = '/assets/img/fotos_perfil/' . $nombreArchivo;
                    $datos['foto'] = $rutaUrl;
                    PerfilDao::actualizarFoto($idPersona, $rutaUrl);
                    $_SESSION['foto_perfil'] = $rutaUrl;
                }
            } else {
                $_SESSION['perfil_flash'] = 'Formato de imagen no válido. Use JPG, PNG o WebP.';
                header('Location: /perfil');
                return;
            }
        }

        $res = PerfilDao::guardar($idPersona, $datos);
        if (!empty($datos['nombre'])) {
            $_SESSION['usuario_nombre'] = $datos['nombre'];
        }
        if (!empty($datos['username'])) {
            $_SESSION['usuario'] = $datos['username'];
        }
        $_SESSION['perfil_flash'] = $res['success'] ? 'Cambios guardados.' : ($res['mensaje'] ?? 'Error al guardar.');
        header('Location: /perfil');
        exit;
    }

    /**
     * Elimina la foto de perfil del usuario: borra el archivo local (si existe), actualiza BD y sesión.
     */
    public function eliminarFoto()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /login');
            exit;
        }
        $idPersona = (int) $_SESSION['usuario_id'];
        $perfil = PerfilDao::getByPersonaId($idPersona);
        $fotoActual = $perfil['foto'] ?? '';

        $projectRoot = dirname(dirname(__DIR__));
        $fotosDir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'fotos_perfil';
        if ($fotoActual && strpos($fotoActual, '/assets/img/fotos_perfil/') === 0) {
            $nombreArchivo = basename($fotoActual);
            $rutaCompleta = $fotosDir . DIRECTORY_SEPARATOR . $nombreArchivo;
            if (is_file($rutaCompleta)) {
                @unlink($rutaCompleta);
            }
        }

        $res = PerfilDao::eliminarFoto($idPersona);
        $_SESSION['foto_perfil'] = '/assets/img/misc/user.svg';
        $_SESSION['perfil_flash'] = $res['success'] ? 'Foto de perfil eliminada.' : ($res['mensaje'] ?? 'Error al eliminar.');
        header('Location: /perfil');
        exit;
    }
}
