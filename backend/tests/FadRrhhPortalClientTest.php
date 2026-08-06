<?php

use PHPUnit\Framework\TestCase;
use Services\FadRrhhHttpTransport;
use Services\FadRrhhPortalClient;

require_once __DIR__ . '/../services/FadRrhhPortalClient.php';

final class FadRrhhFakeTransport implements FadRrhhHttpTransport
{
    public array $requests = [];
    private array $responses;

    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    public function request(string $method, string $url, array $headers = [], $body = null): array
    {
        $this->requests[] = compact('method', 'url', 'headers', 'body');
        if (!$this->responses) {
            throw new RuntimeException('No hay respuesta simulada.');
        }
        return array_shift($this->responses);
    }
}

final class FadRrhhPortalClientTest extends TestCase
{
    public function testAutenticaConBootstrapPublicoSinEnviarPasswordEnClaro(): void
    {
        $transport = new FadRrhhFakeTransport([
            $this->response('<html><script src="main.test.js"></script></html>'),
            $this->response("cisab: 'Basic public-client'; this.LOGIN_KEY = '1234567890abcdef'; this.LOGIN_IV = 'abcdef1234567890';"),
            $this->response('{"access_token":"token-prueba","expires_in":3600}'),
        ]);
        $client = new FadRrhhPortalClient($transport);

        $token = $client->authenticate('usuario-prueba', 'password-muy-secreto');

        self::assertSame('token-prueba', $token['access_token']);
        self::assertCount(3, $transport->requests);
        self::assertContains('Authorization: Basic public-client', $transport->requests[2]['headers']);
        self::assertStringNotContainsString('password-muy-secreto', (string) $transport->requests[2]['body']);
        self::assertStringNotContainsString('usuario-prueba', (string) $transport->requests[2]['body']);
    }

    public function testConsultaUsuarioConBearerYNoExponeDatosAdicionales(): void
    {
        $transport = new FadRrhhFakeTransport([
            $this->response('{"success":true,"data":{"userId":"user-123","name":"Dato privado"}}'),
        ]);
        $client = new FadRrhhPortalClient($transport);

        $user = $client->getCurrentUser('token-prueba');

        self::assertSame(['userId' => 'user-123'], $user);
        self::assertContains('Authorization: Bearer token-prueba', $transport->requests[0]['headers']);
        self::assertStringStartsWith('https://api.firmaautografa.com/clients/users/', $transport->requests[0]['url']);
    }

    public function testRechazaDescargaQueNoSeaPdf(): void
    {
        $transport = new FadRrhhFakeTransport([$this->response('{"error":"not found"}')]);
        $client = new FadRrhhPortalClient($transport);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('PDF válido');
        $client->downloadSignedPdf('token-prueba', 'req-123');
    }

    public function testSimulaCargaFirmanteYSolicitudSinConexionReal(): void
    {
        $transport = new FadRrhhFakeTransport([
            $this->response('{"success":true,"documentId":"doc-simulado"}'),
            $this->response('{"success":true,"signerId":"firmante-simulado"}'),
            $this->response('{"success":true,"requisitionId":"solicitud-simulada","firstTicket":"https://clientes.firmaautografa.com/firma/simulada"}'),
        ]);
        $client = new FadRrhhPortalClient($transport);
        $pdf = tempnam(sys_get_temp_dir(), 'fad_sim_');
        file_put_contents($pdf, "%PDF-1.4\n% simulacion sin envio real\n");
        try {
            $document = $client->uploadDocument('token-simulado', 'usuario-simulado', $pdf, 'contrato_simulado.pdf');
            $signer = $client->createSigner('token-simulado', 'usuario-simulado', [
                'name' => 'PERSONA', 'email' => 'persona@example.com', 'phone' => '5512345678',
            ]);
            $requisition = $client->createRequisition('token-simulado', 'usuario-simulado', [
                'documentId' => 'doc-simulado',
                'reference' => 'SPARTA-SIMULACION',
                'signers' => [['signerId' => 'firmante-simulado', 'order' => 1]],
            ]);
        } finally {
            @unlink($pdf);
        }

        self::assertSame('doc-simulado', $document['documentId']);
        self::assertSame('firmante-simulado', $signer['signerId']);
        self::assertSame('solicitud-simulada', $requisition['requisitionId']);
        self::assertCount(3, $transport->requests);
        self::assertStringEndsWith('/clients/users/usuario-simulado/documents', $transport->requests[0]['url']);
        self::assertInstanceOf(CURLFile::class, $transport->requests[0]['body']['files']);
        self::assertStringEndsWith('/clients/users/usuario-simulado/signers', $transport->requests[1]['url']);
        self::assertStringEndsWith('/clients/users/usuario-simulado/requisitions', $transport->requests[2]['url']);
        self::assertStringContainsString('SPARTA-SIMULACION', (string) $transport->requests[2]['body']);
        self::assertSame('token-simulado', substr($transport->requests[2]['headers'][0], strlen('Authorization: Bearer ')));
    }

    private function response(string $body, int $status = 200): array
    {
        return ['status' => $status, 'headers' => [], 'body' => $body];
    }
}
