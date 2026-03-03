<?php

namespace Controllers;

use Core\Controller;

class Onboarding extends Controller
{
    public function index()
    {
        $this->set('titulo', 'Onboarding | ' . CONFIGURACION['EMPRESA']);
        self::render('onboarding_contenido', false);
    }

    /**
     * Sirve el archivo de video desde backend/uploads/.
     * URL: /onboarding/video
     * Coloca el archivo en:  backend/uploads/onboarding.mp4
     */
    public function video()
    {
        $file = dirname(__DIR__) . '/uploads/YTDown.com_YouTube_Onboarding-Video-for-Kissflow-SaaS-Onboa_Media_0N5xAiHiqFY_001_1080p.mp4';

        if (!file_exists($file)) {
            http_response_code(404);
            exit('Video no encontrado.');
        }

        $size = filesize($file);
        $mime = 'video/mp4';

        // Soporte para Range Requests (necesario para seek en <video>)
        if (isset($_SERVER['HTTP_RANGE'])) {
            preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $m);
            $start = (int) $m[1];
            $end   = isset($m[2]) && $m[2] !== '' ? (int) $m[2] : $size - 1;
            $length = $end - $start + 1;

            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$size");
            header("Content-Length: $length");
            header("Content-Type: $mime");
            header('Accept-Ranges: bytes');

            $fp = fopen($file, 'rb');
            fseek($fp, $start);
            $buf = 1024 * 64;
            $sent = 0;
            while ($sent < $length && !feof($fp)) {
                echo fread($fp, min($buf, $length - $sent));
                $sent += $buf;
                ob_flush(); flush();
            }
            fclose($fp);
        } else {
            header("Content-Type: $mime");
            header("Content-Length: $size");
            header('Accept-Ranges: bytes');
            readfile($file);
        }
        exit;
    }
}
