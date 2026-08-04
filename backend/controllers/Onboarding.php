<?php

namespace Controllers;

use Core\Controller;

class Onboarding extends Controller
{
    /** Nombre histórico (copiado desde backend/uploads). */
    private const VIDEO_PREFERRED = 'YTDown.com_YouTube_Onboarding-Video-for-Kissflow-SaaS-Onboa_Media_0N5xAiHiqFY_001_1080p.mp4';

    /** Catálogo explícito: evita que un parámetro permita leer cualquier archivo de uploads. */
    private const VIDEOS_MODULO = [
        'bienvenida'       => 'bienvenida.mp4',
        'legacyapp'        => 'legacyapp.mp4',
        'asistencia'       => 'asistencia.mp4',
        'nomina'           => 'nomine.mp4',
        'bonos'            => 'bonos.mp4',
        'recibos_nomina'   => 'recibos_nomina.mp4',
        'cambio_cuenta'    => 'cambio_cuenta.mp4',
        'incapacidades'    => 'incapacidades.mp4',
        'cultura'          => 'nuestra_cultura.mp4',
        'cultura_corporativa' => 'cultura_corporativa.mp4',
        'cierre_induccion' => 'cierre_induccion.mp4',
    ];

    public function index()
    {
        $this->set('titulo', 'Onboarding | ' . CONFIGURACION['EMPRESA']);
        self::render('onboarding_contenido', false);
    }

    /**
     * Sirve el video desde public/uploads/ (raíz) o public/uploads/onboarding/ (con sesión y módulo 44).
     * Orden: uploads/{VIDEO_PREFERRED} → uploads/onboarding/* → primer *.mp4 en onboarding/.
     * URL: /onboarding/video
     */
    public function video()
    {
        $modulo = isset($_GET['modulo']) ? (string) $_GET['modulo'] : null;
        $file = self::resolverRutaVideoOnboarding($modulo);
        if ($file === null || !is_readable($file)) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=utf-8');
            exit('Video no encontrado. Coloque el .mp4 en public/uploads/ o en public/uploads/onboarding/.');
        }

        $size = filesize($file);
        if ($size === false) {
            http_response_code(500);
            exit;
        }
        $mime = 'video/mp4';

        // Range (seek en <video>); sin esto muchos navegadores no reproducen bien vía PHP
        if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d+)-(\d*)/', (string) $_SERVER['HTTP_RANGE'], $m)) {
            $start = (int) $m[1];
            $end   = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : $size - 1;
            $end = min($end, $size - 1);
            if ($start > $end || $start < 0) {
                header('HTTP/1.1 416 Range Not Satisfiable');
                header("Content-Range: bytes */$size");
                exit;
            }
            $length = $end - $start + 1;

            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$size");
            header("Content-Length: $length");
            header("Content-Type: $mime");
            header('Accept-Ranges: bytes');
            header('Cache-Control: private, max-age=3600');

            $fp = fopen($file, 'rb');
            if ($fp === false) {
                http_response_code(500);
                exit;
            }
            fseek($fp, $start);
            $buf = 1024 * 64;
            $sent = 0;
            while ($sent < $length && !feof($fp)) {
                $chunk = fread($fp, min($buf, $length - $sent));
                if ($chunk === false || $chunk === '') {
                    break;
                }
                echo $chunk;
                $sent += strlen($chunk);
                if (function_exists('ob_get_level') && ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
            fclose($fp);
        } else {
            header("Content-Type: $mime");
            header('Content-Length: ' . (string) $size);
            header('Accept-Ranges: bytes');
            header('Cache-Control: private, max-age=3600');
            readfile($file);
        }
        exit;
    }

    /**
     * Ruta absoluta al .mp4 de onboarding o null.
     * Prioridad: public/uploads/{nombre fijo} → public/uploads/onboarding/…
     */
    public static function resolverRutaVideoOnboarding(?string $modulo = null): ?string
    {
        $root = rtrim(sparta_uploads_root(), DIRECTORY_SEPARATOR . '/\\');

        if ($modulo !== null && isset(self::VIDEOS_MODULO[$modulo])) {
            $archivoModulo = sparta_uploads_join('onboarding', self::VIDEOS_MODULO[$modulo]);
            return is_file($archivoModulo) && is_readable($archivoModulo) ? $archivoModulo : null;
        }
        $enRaiz = $root . DIRECTORY_SEPARATOR . self::VIDEO_PREFERRED;
        if (is_file($enRaiz) && is_readable($enRaiz)) {
            return $enRaiz;
        }

        $dir = sparta_uploads_join('onboarding');
        if (is_dir($dir)) {
            $candidates = [
                $dir . DIRECTORY_SEPARATOR . self::VIDEO_PREFERRED,
                $dir . DIRECTORY_SEPARATOR . 'onboarding.mp4',
            ];
            foreach ($candidates as $p) {
                if (is_file($p) && is_readable($p)) {
                    return $p;
                }
            }
            $glob = glob($dir . DIRECTORY_SEPARATOR . '*.mp4', GLOB_NOSORT) ?: [];
            foreach ($glob as $p) {
                if (is_readable($p)) {
                    return $p;
                }
            }
        }

        return null;
    }
}
