<?php

use PHPUnit\Framework\TestCase;
use Services\GeminiMediaClient;
use Services\LeonidasMediaService;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

final class LeonidasMediaClientStub extends GeminiMediaClient
{
    public function generateImage(string $prompt): array
    {
        return [
            'success' => true,
            'body' => 'fake-image',
            'mime' => 'image/png',
            'model' => 'gemini-image-test',
        ];
    }

    public function startVideo(string $prompt): array
    {
        return [
            'success' => true,
            'operation' => 'operations/video-test',
            'model' => 'veo-test',
        ];
    }

    public function pollVideo(string $operation): array
    {
        return [
            'success' => true,
            'done' => true,
            'body' => 'fake-video',
            'mime' => 'video/mp4',
        ];
    }

    public function generateMusic(string $prompt): array
    {
        return [
            'success' => true,
            'body' => 'fake-audio',
            'mime' => 'audio/wav',
            'model' => 'lyria-test',
        ];
    }

    public function generateStructuredJson(string $prompt, array $schema): array
    {
        $normalized = mb_strtolower($prompt, 'UTF-8');
        if (str_contains($normalized, 'diagrama')) {
            return [
                'success' => true,
                'model' => 'gemini-structured-test',
                'data' => [
                    'title' => 'Flujo de prueba',
                    'subtitle' => 'Validacion del generador',
                    'nodes' => [
                        ['id' => 'inicio', 'label' => 'Inicio', 'type' => 'start'],
                        ['id' => 'fin', 'label' => 'Fin', 'type' => 'end'],
                    ],
                    'edges' => [
                        ['from' => 'inicio', 'to' => 'fin', 'label' => 'Continuar'],
                    ],
                ],
            ];
        }
        if (str_contains($normalized, 'hoja de calculo')) {
            return [
                'success' => true,
                'model' => 'gemini-structured-test',
                'data' => [
                    'title' => 'Reporte de prueba',
                    'sheet_name' => 'Datos',
                    'columns' => ['Numero de empleado', 'Nombre', 'Estatus'],
                    'rows' => [
                        ['0007', 'PERSONA DE PRUEBA', 'Activo'],
                        ['0012', 'OTRA PERSONA', 'Baja'],
                    ],
                    'notes' => ['Datos generados exclusivamente para la prueba automatizada.'],
                ],
            ];
        }

        return [
            'success' => true,
            'model' => 'gemini-structured-test',
            'data' => [
                'title' => 'Informe de prueba',
                'subtitle' => 'Validacion del generador',
                'sections' => [
                    [
                        'heading' => 'Resumen',
                        'paragraphs' => ['Este documento valida la generacion privada de archivos.'],
                    ],
                ],
                'notes' => ['Contenido de prueba.'],
            ],
        ];
    }
}

final class LeonidasMediaServiceTest extends TestCase
{
    private string $storagePath;
    private LeonidasMediaService $service;

    protected function setUp(): void
    {
        $this->storagePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'leonidas-media-' . bin2hex(random_bytes(6));
        $this->service = new LeonidasMediaService(new LeonidasMediaClientStub(), $this->storagePath);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->storagePath . DIRECTORY_SEPARATOR . '*') ?: [] as $path) {
            @unlink($path);
        }
        @rmdir($this->storagePath);
    }

    public function testGeneraImagenPrivadaParaElUsuarioSolicitante(): void
    {
        $result = $this->resolve('Genera una imagen de Leonidas frente a una ciudad', 878);

        self::assertSame('media_imagen', $result['tipo']);
        self::assertSame('listo', $result['medio']['estado']);
        self::assertSame('image', $result['medio']['tipo']);
        self::assertSame('fake-image', $this->service->obtener($result['medio']['token'], 878)['body']);

        $this->expectException(DomainException::class);
        $this->service->obtener($result['medio']['token'], 999);
    }

    public function testVideoPasaDeProcesandoAListo(): void
    {
        $result = $this->resolve('Crea un video de Leonidas caminando por Sparta', 878);

        self::assertSame('media_video', $result['tipo']);
        self::assertSame('procesando', $result['medio']['estado']);

        $status = $this->service->estado($result['medio']['token'], 878);
        self::assertSame('listo', $status['medio']['estado']);
        self::assertSame('fake-video', $this->service->obtener($result['medio']['token'], 878)['body']);
    }

    public function testGeneraMusicaCuandoElProveedorEstaConfigurado(): void
    {
        $result = $this->resolve('Compone una musica epica para Sparta', 878);

        self::assertSame('media_audio', $result['tipo']);
        self::assertSame('audio', $result['medio']['tipo']);
        self::assertSame('fake-audio', $this->service->obtener($result['medio']['token'], 878)['body']);
    }

    public function testGeneraDiagramaSvgPrivado(): void
    {
        $result = $this->resolve('Crea un diagrama del flujo de prueba', 878);
        $file = $this->service->obtener($result['medio']['token'], 878);

        self::assertSame('media_diagram', $result['tipo']);
        self::assertSame('diagram', $result['medio']['tipo']);
        self::assertSame('image/svg+xml', $file['mime']);
        self::assertStringContainsString('<svg', $file['body']);
        self::assertStringContainsString('Flujo de prueba', $file['body']);
    }

    public function testGeneraPdfRealDescargable(): void
    {
        $result = $this->resolve('Prepara un PDF con un informe de prueba', 878);
        $file = $this->service->obtener($result['medio']['token'], 878);

        self::assertSame('media_pdf', $result['tipo']);
        self::assertSame('pdf', $result['medio']['tipo']);
        self::assertSame('application/pdf', $file['mime']);
        self::assertStringStartsWith('%PDF-', $file['body']);
        self::assertStringEndsWith('.pdf', $file['name']);
        self::assertNotEmpty($result['medio']['descarga_url']);
    }

    public function testGeneraExcelRealDescargableConIdentificadoresComoTexto(): void
    {
        $result = $this->resolve('Genera un Excel con el reporte de prueba', 878);
        $file = $this->service->obtener($result['medio']['token'], 878);

        self::assertSame('media_excel', $result['tipo']);
        self::assertSame('excel', $result['medio']['tipo']);
        self::assertSame(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $file['mime']
        );
        self::assertStringStartsWith('PK', $file['body']);
        self::assertStringEndsWith('.xlsx', $file['name']);
        self::assertNotEmpty($result['medio']['descarga_url']);

        $tempPath = tempnam(sys_get_temp_dir(), 'leonidas-xlsx-');
        self::assertNotFalse($tempPath);
        file_put_contents($tempPath, $file['body']);

        try {
            $workbook = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempPath);
            self::assertSame('0007', $workbook->getActiveSheet()->getCell('A2')->getValue());
            self::assertSame('0012', $workbook->getActiveSheet()->getCell('A3')->getValue());
            self::assertSame('PERSONA DE PRUEBA', $workbook->getActiveSheet()->getCell('B2')->getValue());
            self::assertSame('Notas', $workbook->getSheet(1)->getTitle());
            $workbook->disconnectWorksheets();
        } finally {
            @unlink($tempPath);
        }
    }

    public function testPreguntaDeCapacidadNoDisparaUnaGeneracion(): void
    {
        foreach (
            [
                'Puedes crear videos?',
                'Quiero saber si puedes crear videos',
                'Puedes generar un PDF?',
                'Eres capaz de crear un Excel?',
            ] as $question
        ) {
            $result = $this->service->resolver(
                $question,
                $this->normalize($question),
                ['actor_id' => 878]
            );

            self::assertNull($result);
        }
    }

    /** @return array<string, mixed> */
    private function resolve(string $message, int $actorId): array
    {
        $result = $this->service->resolver(
            $message,
            $this->normalize($message),
            ['actor_id' => $actorId]
        );
        self::assertIsArray($result);
        return $result;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        return is_string($ascii) ? $ascii : $value;
    }
}
