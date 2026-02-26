<?php

namespace Core;

/**
 * OCR y validación de documento de identificación oficial (INE, Pasaporte, Residencias).
 * Requiere Tesseract OCR instalado. Para PDF opcionalmente Imagick o pdftoppm.
 */
class OcrIdentidad
{
    /** Tipos de identificación permitidos (clave normalizada => etiquetas en el texto) */
    const TIPOS_PERMITIDOS = [
        'INE' => ['credencial para votar', 'instituto nacional electoral', 'ife', 'ine', 'cve electoral'],
        'PASAPORTE' => ['pasaporte', 'passport', 'sre', 'secretaría de relaciones exteriores'],
        'RESIDENCIA_TEMPORAL' => ['residencia temporal', 'residente temporal'],
        'RESIDENCIA_TEMPORAL_ACUMULATIVA' => ['residencia temporal acumulativa', 'acumulativa'],
        'RESIDENCIA_PERMANENTE' => ['residencia permanente', 'residente permanente'],
    ];

    /** Regex CURP México: 4 letras, 6 fecha, 1 sexo, 2 estado, 3 consonantes, 2 dígitos, 1 dígito o A */
    const REGEX_CURP = '/\b([A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d)\b/i';

    /** Número INE: 10 dígitos (clave de elector) o 13 (con OCR) */
    const REGEX_CLAVE_ELECTOR = '/\b\d{10,13}\b/';

    private $rutaTesseract = 'tesseract';
    private $idioma = 'spa';
    private $tmpDir;

    public function __construct($rutaTesseract = null, $tmpDir = null)
    {
        if ($rutaTesseract !== null) {
            $this->rutaTesseract = $rutaTesseract;
        }
        $this->tmpDir = $tmpDir ?? sys_get_temp_dir();
    }

    /**
     * Comprueba si Tesseract está disponible.
     */
    public function tesseractDisponible(): bool
    {
        $cmd = escapeshellcmd($this->rutaTesseract) . ' --version 2>&1';
        $out = @shell_exec($cmd);
        return $out !== null && stripos($out, 'tesseract') !== false;
    }

    /**
     * Ejecuta OCR sobre un archivo (imagen o PDF).
     * @param string $rutaArchivo Ruta absoluta al archivo
     * @return array ['ok' => bool, 'texto' => string, 'error' => string|null]
     */
    public function extraerTexto(string $rutaArchivo): array
    {
        if (!is_file($rutaArchivo)) {
            return ['ok' => false, 'texto' => '', 'error' => 'Archivo no encontrado.'];
        }
        $ext = strtolower(pathinfo($rutaArchivo, PATHINFO_EXTENSION));
        $imagenParaOcr = $rutaArchivo;

        if ($ext === 'pdf') {
            $imagenParaOcr = $this->pdfPrimeraPaginaAImagen($rutaArchivo);
            if ($imagenParaOcr === null) {
                return ['ok' => false, 'texto' => '', 'error' => 'No se pudo convertir el PDF a imagen (instale Imagick o pdftoppm).'];
            }
        } elseif (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
            return ['ok' => false, 'texto' => '', 'error' => 'Formato no soportado para OCR.'];
        }

        $texto = $this->ejecutarTesseract($imagenParaOcr);
        if ($imagenParaOcr !== $rutaArchivo && is_file($imagenParaOcr)) {
            @unlink($imagenParaOcr);
        }
        if ($texto === null) {
            return ['ok' => false, 'texto' => '', 'error' => 'Tesseract no disponible o falló.'];
        }
        return ['ok' => true, 'texto' => $texto, 'error' => null];
    }

    /**
     * Convierte la primera página del PDF a imagen para OCR.
     */
    private function pdfPrimeraPaginaAImagen(string $rutaPdf): ?string
    {
        if (extension_loaded('imagick')) {
            try {
                $im = new \Imagick();
                $im->setResolution(150, 150);
                $im->readImage($rutaPdf . '[0]');
                $im->setImageFormat('png');
                $tmp = $this->tmpDir . '/ocr_' . uniqid() . '.png';
                $im->writeImage($tmp);
                $im->clear();
                $im->destroy();
                return $tmp;
            } catch (\Throwable $e) {
                return null;
            }
        }
        $pdftoppm = $this->buscarComando('pdftoppm');
        if ($pdftoppm !== null) {
            $base = $this->tmpDir . '/ocr_' . uniqid();
            $out = $base . '-1.png';
            $cmd = sprintf(
                '%s -png -f 1 -l 1 %s %s 2>&1',
                escapeshellarg($pdftoppm),
                escapeshellarg($rutaPdf),
                escapeshellarg($base)
            );
            @exec($cmd);
            if (is_file($out)) {
                return $out;
            }
        }
        return null;
    }

    private function buscarComando(string $nombre): ?string
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $r = @shell_exec('where ' . $nombre . ' 2>&1');
        } else {
            $r = @shell_exec('which ' . $nombre . ' 2>&1');
        }
        if ($r === null) {
            return null;
        }
        $line = trim(explode("\n", $r)[0]);
        return $line !== '' ? $line : null;
    }

    private function ejecutarTesseract(string $rutaImagen): ?string
    {
        $baseOut = $this->tmpDir . '/tess_out_' . uniqid();
        $cmd = sprintf(
            '%s %s %s -l %s 2>&1',
            escapeshellcmd($this->rutaTesseract),
            escapeshellarg($rutaImagen),
            escapeshellarg($baseOut),
            escapeshellarg($this->idioma)
        );
        @exec($cmd);
        if (is_file($baseOut . '.txt')) {
            $texto = file_get_contents($baseOut . '.txt');
            @unlink($baseOut . '.txt');
            return $texto;
        }
        return null;
    }

    /**
     * Parsea el texto OCR y extrae tipo, CURP, nombre (aprox.) y número de documento.
     */
    public function parsearTextoIdentidad(string $texto): array
    {
        $textoNorm = $this->normalizarTexto($texto);
        $tipo = $this->detectarTipo($textoNorm);
        $curp = $this->extraerCURP($texto);
        $claveElector = $this->extraerClaveElector($texto);
        $nombre = $this->extraerNombreAproximado($texto);
        return [
            'tipo' => $tipo,
            'tipo_normalizado' => $tipo !== null ? $this->normalizarTipoParaRespuesta($tipo) : null,
            'curp' => $curp,
            'clave_elector' => $claveElector,
            'nombre' => $nombre,
        ];
    }

    private function normalizarTexto(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/\s+/', ' ', $s);
        $s = $this->quitarAcentos($s);
        return $s;
    }

    private function quitarAcentos(string $s): string
    {
        $map = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n'];
        return strtr($s, $map);
    }

    private function detectarTipo(string $textoNorm): ?string
    {
        foreach (self::TIPOS_PERMITIDOS as $clave => $etiquetas) {
            foreach ($etiquetas as $et) {
                if (strpos($textoNorm, $this->quitarAcentos(mb_strtolower($et, 'UTF-8'))) !== false) {
                    return $clave;
                }
            }
        }
        return null;
    }

    private function normalizarTipoParaRespuesta(string $clave): string
    {
        $map = [
            'INE' => 'INE',
            'PASAPORTE' => 'Pasaporte',
            'RESIDENCIA_TEMPORAL' => 'Residencia Temporal',
            'RESIDENCIA_TEMPORAL_ACUMULATIVA' => 'Residencia Temporal (acumulativa)',
            'RESIDENCIA_PERMANENTE' => 'Residencia Permanente',
        ];
        return $map[$clave] ?? $clave;
    }

    private function extraerCURP(string $texto): ?string
    {
        if (preg_match(self::REGEX_CURP, $texto, $m)) {
            return strtoupper($m[1]);
        }
        return null;
    }

    private function extraerClaveElector(string $texto): ?string
    {
        if (preg_match(self::REGEX_CLAVE_ELECTOR, $texto, $m)) {
            return $m[1];
        }
        return null;
    }

    private function extraerNombreAproximado(string $texto): ?string
    {
        if (preg_match('/nombre\s*[:.]?\s*([A-Za-zÁ-Úá-úÑñ\s]{3,60})/ui', $texto, $m)) {
            return trim(preg_replace('/\s+/', ' ', $m[1]));
        }
        return null;
    }

    /**
     * Valida que el tipo detectado esté permitido.
     */
    public function esTipoPermitido(?string $tipoNormalizado): bool
    {
        if ($tipoNormalizado === null) {
            return false;
        }
        $claves = array_keys(self::TIPOS_PERMITIDOS);
        $upper = strtoupper(str_replace([' ', '-', '(', ')'], '_', $tipoNormalizado));
        foreach ($claves as $c) {
            if (strpos($upper, $c) !== false || $c === $upper) {
                return true;
            }
        }
        return in_array($tipoNormalizado, $claves, true);
    }

    /**
     * Valida formato CURP (18 caracteres, estructura estándar).
     */
    public static function validarFormatoCURP(?string $curp): bool
    {
        if ($curp === null || $curp === '') {
            return true;
        }
        $curp = strtoupper(trim($curp));
        return (bool) preg_match('/^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d$/', $curp);
    }

    /**
     * Validación completa del documento de identificación.
     * @param string $rutaArchivo Ruta al archivo subido
     * @param array|null $candidato Datos del candidato ['nombres','apellidop','apellidom','curp'] para cotejar (curp opcional)
     * @return array ['valido' => bool, 'mensaje' => string, 'datos' => array]
     */
    public function validarDocumentoIdentidad(string $rutaArchivo, ?array $candidato = null): array
    {
        if (!$this->tesseractDisponible()) {
            return [
                'valido' => true,
                'mensaje' => 'OCR no disponible; documento aceptado. Instale Tesseract para validar.',
                'datos' => [],
            ];
        }
        $ocr = $this->extraerTexto($rutaArchivo);
        if (!$ocr['ok']) {
            return [
                'valido' => true,
                'mensaje' => 'No se pudo leer el documento; aceptado sin validación OCR.',
                'datos' => [],
            ];
        }
        $datos = $this->parsearTextoIdentidad($ocr['texto']);

        if ($datos['tipo'] === null) {
            return [
                'valido' => false,
                'mensaje' => 'No se reconoció un documento de identificación oficial válido (INE, Pasaporte o Residencia). Verifique que la imagen sea legible.',
                'datos' => $datos,
            ];
        }

        if ($datos['curp'] !== null && !self::validarFormatoCURP($datos['curp'])) {
            return [
                'valido' => false,
                'mensaje' => 'CURP detectado con formato inválido. Revise que el documento sea legible.',
                'datos' => $datos,
            ];
        }

        if ($candidato !== null && !empty($candidato['curp']) && $datos['curp'] !== null) {
            $curpCandidato = strtoupper(trim($candidato['curp']));
            $curpDoc = strtoupper(trim($datos['curp']));
            if ($curpCandidato !== $curpDoc) {
                return [
                    'valido' => false,
                    'mensaje' => 'El CURP del documento no coincide con el registrado del candidato.',
                    'datos' => $datos,
                ];
            }
        }

        return [
            'valido' => true,
            'mensaje' => 'Documento válido: ' . ($datos['tipo_normalizado'] ?? $datos['tipo']),
            'datos' => $datos,
        ];
    }
}
