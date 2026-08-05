<?php

namespace Controllers;

use Core\Controller;
use Core\Database;

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
        'valores'          => 'cultura_corporativa.mp4',
        'cultura'          => 'nuestra_cultura.mp4',
    ];

    /** Ponderaciones visibles del avance global de Onboarding. */
    private const PROGRESS_WEIGHTS = [
        'bienvenida'  => 10,
        'modulo'      => 3,
        'corporativo' => 23,
        'especializado' => 35,
        'feedback'    => 5,
    ];

    private const MODULE_PROGRESS_KEYS = [
        'legacyapp', 'asistencia', 'nomina', 'bonos', 'recibos_nomina',
        'cambio_cuenta', 'incapacidades', 'valores', 'cultura',
    ];

    public function index()
    {
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? 0);
        $nombreDiploma = trim((string) ($_SESSION['usuario_nombre'] ?? ''));
        if ($usuarioId > 0) {
            try {
                $persona = (new Database())->queryOne('SELECT nombres, segundo_nombre, apellidop, apellidom FROM persona WHERE id = :id LIMIT 1', ['id' => $usuarioId]);
                if ($persona) $nombreDiploma = trim(implode(' ', array_filter([$persona['nombres'] ?? '', $persona['segundo_nombre'] ?? '', $persona['apellidop'] ?? '', $persona['apellidom'] ?? ''], static fn($value) => trim((string) $value) !== '')));
            } catch (\Throwable $exception) {
                // Se conserva el nombre disponible en sesión si la consulta no está disponible.
            }
        }
        $this->set('titulo', 'Onboarding | ' . CONFIGURACION['EMPRESA']);
        $this->set('onboardingCertificateName', $nombreDiploma ?: 'Colaborador Maxikash');
        self::render('onboarding_contenido', false);
    }

    /** Genera el diploma horizontal únicamente para evaluaciones aprobadas. */
    public function diploma()
    {
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? 0);
        $tipo = (string) ($_GET['tipo'] ?? 'corporativo');
        $key = $tipo === 'especializado' ? 'especializada' : 'corporativa';
        $database = self::readProgressDatabase();
        $record = $database['users'][(string) $usuarioId] ?? [];
        if ($usuarioId <= 0 || empty($record['evaluaciones'][$key])) {
            http_response_code(403);
            exit('El diploma estará disponible al aprobar la evaluación.');
        }
        $nombre = trim((string) ($_SESSION['usuario_nombre'] ?? 'Colaborador Maxikash'));
        try {
            $persona = (new Database())->queryOne('SELECT nombres, segundo_nombre, apellidop, apellidom FROM persona WHERE id = :id LIMIT 1', ['id' => $usuarioId]);
            if ($persona) $nombre = trim(implode(' ', array_filter([$persona['nombres'] ?? '', $persona['segundo_nombre'] ?? '', $persona['apellidop'] ?? '', $persona['apellidom'] ?? ''], static fn($value) => trim((string) $value) !== '')));
        } catch (\Throwable $exception) { }
        $titulo = $tipo === 'especializado' ? 'DIPLOMA DE COMPETENCIA TÉCNICA' : 'DIPLOMA DE INDUCCIÓN';
        $texto = $tipo === 'especializado' ? 'Acreditó exitosamente la evaluación especializada de su puesto.' : 'Acreditó exitosamente la inducción corporativa sobre políticas de asistencia, nómina y cultura.';
        $fecha = date('d de F de Y');
        $html = '<style>body{font-family:dejavusans;color:#07548a}.frame{border:5px solid #07548a;padding:28px;text-align:center;background:#fff}.brand{font-size:20px;font-weight:bold;color:#0d6efd}.title{font-family:serif;font-size:52px;letter-spacing:4px;margin:30px 0 4px}.name{font-family:serif;font-size:34px;color:#07548a;border-bottom:2px solid #55bdc9;display:inline-block;padding:0 35px 7px;margin:15px 0}.sub{color:#5d7990;font-size:15px}.corners{color:#2bbdcc;font-size:18px;letter-spacing:12px}</style><div class="frame"><div class="corners">■ ■ ■ ■ ■ ■</div><div class="brand">◆ Maxikash</div><div class="title">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</div><div class="sub">otorgado a</div><div class="name">' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</div><p style="font-size:15px">' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</p><p class="sub">Amigo Efectivo S.A. de C.V. &nbsp; | &nbsp; Área de Recursos Humanos<br>Fecha: ' . htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') . '</p><div class="corners">■ ■ ■ ■ ■ ■</div></div>';
        $titulo = 'DIPLOMA';
        $texto = $tipo === 'especializado' ? 'Acreditó exitosamente la evaluación especializada de su puesto.' : 'Acreditó exitosamente la inducción corporativa sobre políticas de asistencia, nómina y cultura.';
        $html = '<style>
            body{font-family:dejavusans;color:#004fd3}.frame{position:relative;min-height:470px;overflow:hidden;border:5px solid #004fd3;padding:52px 72px;text-align:center;background:#fff}.brand{font-size:23px;font-weight:bold;color:#004fd3}.brand-mark{color:#c3d64c}.title{font-family:serif;font-size:62px;letter-spacing:7px;margin:48px 0 3px;color:#004fd3}.award{color:#91a400;font-size:13px;font-weight:bold;letter-spacing:3px}.sub{color:#61758d;font-size:15px}.name{font-family:serif;font-size:37px;color:#004fd3;border-bottom:3px solid #c3d64c;display:inline-block;padding:0 44px 9px;margin:16px 0}.copy{max-width:510px;margin:0 auto 35px;font-size:15px;line-height:1.55;color:#3c536d}.footer{font-size:12px;color:#61758d}.corner{position:absolute;width:108px;height:108px}.tl{top:0;left:0;border-right:22px solid #004fd3;border-bottom:22px solid #004fd3}.tr{top:0;right:0;border-left:22px solid #004fd3;border-bottom:22px solid #004fd3}.bl{bottom:0;left:0;border-right:22px solid #004fd3;border-top:22px solid #004fd3}.br{right:0;bottom:0;border-left:22px solid #004fd3;border-top:22px solid #004fd3}.corner i{position:absolute;display:block;background:#c3d64c}.tl i,.bl i{right:20px;width:38px;height:16px}.tr i,.br i{left:20px;width:38px;height:16px}.tl i,.tr i{bottom:22px}.bl i,.br i{top:22px}</style><div class="frame"><div class="corner tl"><i></i></div><div class="corner tr"><i></i></div><div class="corner bl"><i></i></div><div class="corner br"><i></i></div><div class="brand"><span class="brand-mark">◆</span> Maxikash</div><div class="title">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</div><div class="sub">otorgado a</div><div class="name">' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</div><div class="award">OTORGADO CON HONORES POR MAXIKASH MÉXICO</div><p class="copy">' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</p><div class="footer">Amigo Efectivo S.A. de C.V. &nbsp; | &nbsp; Área de Recursos Humanos &nbsp; | &nbsp; ID: <strong style="color:#91a400">MK-2026-OK</strong></div></div>';
        $reconocimiento = $tipo !== 'especializado' || (int) ($record['evaluaciones']['especializada_score'] ?? 0) === 10
            ? 'OTORGADO CON HONORES POR MAXIKASH MÉXICO'
            : 'ACREDITADO POR MAXIKASH MÉXICO';
        // El alto anterior (en px) excedía el área útil de mPDF y partía el
        // contenido del diploma en una segunda hoja. Se fija en milímetros
        // para que el diploma completo ocupe una única página A4 horizontal.
        $html = str_replace(
            'position:relative;min-height:470px;overflow:hidden;border:5px solid #004fd3;padding:52px 72px;',
            'position:relative;height:168mm;box-sizing:border-box;overflow:hidden;border:5px solid #004fd3;padding:12mm 18mm;',
            $html
        );
        $html = str_replace(
            'font-size:62px;letter-spacing:7px;margin:48px 0 3px;',
            'font-size:52px;letter-spacing:7px;margin:12mm 0 1mm;',
            $html
        );
        $html = str_replace('margin:0 auto 35px;', 'margin:0 auto 8mm;', $html);
        $html = preg_replace('#<div class="award">.*?</div>#', '<div class="award">' . htmlspecialchars($reconocimiento, ENT_QUOTES, 'UTF-8') . '</div>', $html, 1) ?: $html;
        $logoPath = dirname(RAIZ) . '/public/assets/img/logo_nombre.svg';
        $logoUri = 'file:///' . str_replace('\\', '/', $logoPath);
        $logoTag = '<div class="brand"><img src="' . htmlspecialchars($logoUri, ENT_QUOTES, 'UTF-8') . '" style="width:190px;height:auto" alt="Maxikash"></div><div class="title">';
        $html = preg_replace('#<div class="brand">.*?</div><div class="title">#', $logoTag, $html, 1) ?: $html;
        // mPDF gira las dimensiones al usar orientación L; esta base 210 x 297
        // produce de forma explícita una página final A4 horizontal: 297 x 210 mm.
        $mpdf = new \Mpdf\Mpdf(['format' => [210, 297], 'orientation' => 'L', 'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 10]);
        $mpdf->SetTitle('Diploma Maxikash');
        $mpdf->WriteHTML($html);
        $mpdf->Output('diploma-maxikash-' . $tipo . '.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        exit;
    }

    /**
     * Consulta y registra el avance persistente del usuario autenticado.
     * El identificador nunca se recibe desde el navegador: se obtiene de sesión.
     * URL: /onboarding/progress
     */
    public function progress()
    {
        $usuarioId = (int) ($_SESSION['usuario_id'] ?? $_SESSION['persona_id'] ?? $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0);
        if ($usuarioId <= 0) {
            self::respuestaJSON(['success' => false, 'message' => 'No se encontró una sesión de usuario válida.']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $database = self::readProgressDatabase();
            $record = $database['users'][(string) $usuarioId] ?? self::emptyProgressRecord($usuarioId);
            self::respuestaJSON(['success' => true, 'data' => self::formatProgressRecord($record)]);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            self::respuestaJSON(['success' => false, 'message' => 'Método no permitido.']);
        }

        $body = json_decode((string) file_get_contents('php://input'), true);
        $action = is_array($body) ? (string) ($body['action'] ?? '') : '';
        $module = is_array($body) ? (string) ($body['module'] ?? '') : '';
        $score = is_array($body) ? (int) ($body['score'] ?? 0) : 0;
        $allowedActions = ['video_complete', 'corporate_quiz_complete', 'specialized_quiz_complete', 'feedback_sent', 'celebration_shown'];
        if (!in_array($action, $allowedActions, true)) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'message' => 'Acción de avance no válida.']);
        }
        if ($action === 'video_complete' && $module !== 'bienvenida' && !in_array($module, self::MODULE_PROGRESS_KEYS, true)) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'message' => 'Módulo de video no válido.']);
        }
        if ($action === 'specialized_quiz_complete' && ($score < 8 || $score > 10)) {
            http_response_code(422);
            self::respuestaJSON(['success' => false, 'message' => 'La calificación especializada no es válida.']);
        }

        $record = self::updateProgressRecord($usuarioId, $action, $module, $score);
        self::respuestaJSON(['success' => true, 'data' => self::formatProgressRecord($record)]);
    }

    private static function progressDatabasePath(): string
    {
        return RAIZ . '/storage/onboarding/progress.json';
    }

    private static function emptyProgressRecord(int $usuarioId): array
    {
        return [
            'id_usuario' => $usuarioId,
            'videos' => ['bienvenida' => false, 'modulos' => []],
            'evaluaciones' => ['corporativa' => false, 'especializada' => false],
            'feedback' => false,
            'finalizado' => false,
            'celebracion_mostrada' => false,
            'updated_at' => null,
        ];
    }

    private static function readProgressDatabase(): array
    {
        $path = self::progressDatabasePath();
        if (!is_file($path)) {
            return ['version' => 1, 'users' => []];
        }
        $content = file_get_contents($path);
        $database = is_string($content) ? json_decode($content, true) : null;
        return is_array($database) && isset($database['users']) && is_array($database['users'])
            ? $database
            : ['version' => 1, 'users' => []];
    }

    private static function updateProgressRecord(int $usuarioId, string $action, string $module, int $score = 0): array
    {
        $path = self::progressDatabasePath();
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException('No se pudo preparar el almacenamiento de avance.');
        }
        $handle = fopen($path, 'c+');
        if ($handle === false || !flock($handle, LOCK_EX)) {
            throw new \RuntimeException('No se pudo bloquear el archivo de avance.');
        }
        try {
            rewind($handle);
            $content = stream_get_contents($handle);
            $database = is_string($content) ? json_decode($content, true) : null;
            if (!is_array($database) || !isset($database['users']) || !is_array($database['users'])) {
                $database = ['version' => 1, 'users' => []];
            }
            $key = (string) $usuarioId;
            $record = $database['users'][$key] ?? self::emptyProgressRecord($usuarioId);
            if ($action === 'video_complete') {
                if ($module === 'bienvenida') $record['videos']['bienvenida'] = true;
                else $record['videos']['modulos'][$module] = true;
            } elseif ($action === 'corporate_quiz_complete') {
                $record['evaluaciones']['corporativa'] = true;
            } elseif ($action === 'specialized_quiz_complete') {
                $record['evaluaciones']['especializada'] = true;
                $record['evaluaciones']['especializada_score'] = $score;
            } elseif ($action === 'feedback_sent') {
                $record['feedback'] = true;
            } elseif ($action === 'celebration_shown' && !empty($record['finalizado'])) {
                $record['celebracion_mostrada'] = true;
            }
            $completedModules = count(array_intersect(self::MODULE_PROGRESS_KEYS, array_keys(array_filter($record['videos']['modulos'] ?? []))));
            $record['finalizado'] = !empty($record['videos']['bienvenida'])
                && $completedModules === count(self::MODULE_PROGRESS_KEYS)
                && !empty($record['evaluaciones']['corporativa'])
                && !empty($record['evaluaciones']['especializada'])
                && !empty($record['feedback']);
            if ($action === 'celebration_shown' && $record['finalizado']) {
                $record['celebracion_mostrada'] = true;
            }
            $record['id_usuario'] = $usuarioId;
            $record['updated_at'] = date(DATE_ATOM);
            $database['users'][$key] = $record;

            $encoded = json_encode($database, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            if ($encoded === false) throw new \RuntimeException('No se pudo codificar el avance.');
            rewind($handle);
            if (!ftruncate($handle, 0) || fwrite($handle, $encoded) === false || !fflush($handle)) {
                throw new \RuntimeException('No se pudo guardar el avance.');
            }
            return $record;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private static function formatProgressRecord(array $record): array
    {
        $moduleCount = count(array_filter($record['videos']['modulos'] ?? []));
        $moduleCount = min(count(self::MODULE_PROGRESS_KEYS), $moduleCount);
        $total = (empty($record['videos']['bienvenida']) ? 0 : self::PROGRESS_WEIGHTS['bienvenida'])
            + ($moduleCount * self::PROGRESS_WEIGHTS['modulo'])
            + (empty($record['evaluaciones']['corporativa']) ? 0 : self::PROGRESS_WEIGHTS['corporativo'])
            + (empty($record['evaluaciones']['especializada']) ? 0 : self::PROGRESS_WEIGHTS['especializado'])
            + (empty($record['feedback']) ? 0 : self::PROGRESS_WEIGHTS['feedback']);
        $record['progress'] = ['percentage' => min(100, $total), 'completed_modules' => $moduleCount, 'total_modules' => count(self::MODULE_PROGRESS_KEYS)];
        $record['finalizado'] = !empty($record['finalizado']) || $total === 100;
        $record['celebracion_mostrada'] = !empty($record['celebracion_mostrada']);
        return $record;
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
