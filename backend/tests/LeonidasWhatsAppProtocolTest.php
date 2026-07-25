<?php

use PHPUnit\Framework\TestCase;
use Services\LeonidasWhatsAppProtocol;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

final class LeonidasWhatsAppProtocolTest extends TestCase
{
    public function testValidaChallengeDeMeta(): void
    {
        $query = [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'token-seguro',
            'hub_challenge' => '123456',
        ];
        self::assertSame('123456', LeonidasWhatsAppProtocol::challenge($query, 'token-seguro'));
        self::assertNull(LeonidasWhatsAppProtocol::challenge($query, 'otro-token'));
    }

    public function testValidaFirmaHmac(): void
    {
        $body = '{"entry":[]}';
        $secret = 'app-secret';
        $signature = 'sha256=' . hash_hmac('sha256', $body, $secret);
        self::assertTrue(LeonidasWhatsAppProtocol::firmaValida($body, $signature, $secret));
        self::assertFalse(LeonidasWhatsAppProtocol::firmaValida($body . 'x', $signature, $secret));
    }

    public function testExtraeSoloMensajesEntrantes(): void
    {
        $body = json_encode([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'contacts' => [[
                            'wa_id' => '5215512345678',
                            'profile' => ['name' => 'Usuario Prueba'],
                        ]],
                        'messages' => [[
                            'from' => '5215512345678',
                            'id' => 'wamid.test',
                            'timestamp' => '1700000000',
                            'type' => 'text',
                            'text' => ['body' => 'Hola Leonidas'],
                        ]],
                    ],
                ]],
            ]],
        ]);

        $messages = LeonidasWhatsAppProtocol::extraerMensajes((string) $body);
        self::assertCount(1, $messages);
        self::assertSame('Hola Leonidas', $messages[0]['text']);
        self::assertSame('Usuario Prueba', $messages[0]['profile_name']);
    }

    public function testComparaFormatoMexicanoConInternacional(): void
    {
        self::assertTrue(LeonidasWhatsAppProtocol::telefonosCoinciden(
            '+52 55 1234 5678',
            '5512345678'
        ));
        self::assertTrue(LeonidasWhatsAppProtocol::telefonosCoinciden(
            '5215512345678',
            '55 1234 5678'
        ));
        self::assertFalse(LeonidasWhatsAppProtocol::telefonosCoinciden(
            '5512345678',
            '5587654321'
        ));
    }

    public function testConfirmarYCancelarSonComandosExplicitos(): void
    {
        self::assertSame('confirmar', LeonidasWhatsAppProtocol::comando('CONFIRMAR'));
        self::assertSame('cancelar', LeonidasWhatsAppProtocol::comando('Cancelar'));
        self::assertNull(LeonidasWhatsAppProtocol::comando('si, revisa ese credito'));
    }

    public function testPropuestaSolicitaConfirmacion(): void
    {
        $text = LeonidasWhatsAppProtocol::textoRespuesta([
            'mensaje' => 'Vista previa lista.',
            'propuesta' => [
                'token' => 'abc',
                'requiere_confirmacion' => true,
            ],
        ]);
        self::assertStringContainsString('CONFIRMAR', $text);
        self::assertStringContainsString('CANCELAR', $text);
    }
}
