<?php

namespace Services;

use Core\EnvLoader;
use Models\Login;

final class LeonidasWhatsAppService
{
    private LeonidasWhatsAppIdentityService $identity;
    private LeonidasWhatsAppClient $client;

    public function __construct(
        ?LeonidasWhatsAppIdentityService $identity = null,
        ?LeonidasWhatsAppClient $client = null
    ) {
        $this->identity = $identity ?? new LeonidasWhatsAppIdentityService();
        $this->client = $client ?? new LeonidasWhatsAppClient();
    }

    public function procesar(string $body): int
    {
        $processed = 0;
        foreach (LeonidasWhatsAppProtocol::extraerMensajes($body) as $message) {
            if ($message['from'] === '' || $message['id'] === '') {
                continue;
            }
            $processed++;
            $this->procesarMensaje($message);
        }
        return $processed;
    }

    private function procesarMensaje(array $message): void
    {
        $identity = $this->identity->buscarPersonaActiva((string) $message['from']);
        if (($identity['status'] ?? '') === 'not_found') {
            $this->client->enviarTexto(
                (string) $message['from'],
                'No pude vincular este número con un colaborador activo de Sparta. '
                . 'Solicita a Capital Humano que registre o corrija tu teléfono.'
            );
            return;
        }
        if (($identity['status'] ?? '') !== 'ok' || !is_array($identity['persona'] ?? null)) {
            $this->client->enviarTexto(
                (string) $message['from'],
                'Este número aparece asociado a más de un colaborador. '
                . 'Por seguridad no puedo consultar ni modificar información hasta que Capital Humano lo corrija.'
            );
            return;
        }

        $persona = $identity['persona'];
        $personaId = (int) ($persona['id'] ?? 0);
        if (!LeonidasAccessService::tieneAcceso($personaId)) {
            $this->client->enviarTexto(
                (string) $message['from'],
                'Tu usuario no tiene asignado el permiso especial Asistente de Sparta. '
                . 'Un administrador debe autorizarlo antes de usar a Leónidas por WhatsApp.'
            );
            return;
        }

        $this->abrirSesionAislada($persona, (string) $message['from']);
        try {
            $cachedAnswer = $this->respuestaProcesada((string) $message['id']);
            if ($cachedAnswer !== null) {
                $this->client->enviarTexto((string) $message['from'], $cachedAnswer);
                return;
            }

            if (($message['type'] ?? '') !== 'text') {
                $answer = 'Recibí tu mensaje, pero por ahora este canal acepta instrucciones de texto. '
                    . 'Los archivos y cargas masivas deben enviarse desde Leónidas dentro de Sparta.';
            } else {
                $answer = $this->resolverTexto((string) ($message['text'] ?? ''));
            }
            $this->recordarRespuesta((string) $message['id'], $answer);
            $this->client->enviarTexto((string) $message['from'], $answer);
        } catch (\Throwable $error) {
            error_log('[Leonidas WhatsApp] Mensaje fallido para persona ' . $personaId . ': ' . $error->getMessage());
            $this->client->enviarTexto(
                (string) $message['from'],
                'No pude completar la solicitud. No realicé ningún cambio. '
                . 'Intenta nuevamente o abre Leónidas dentro de Sparta para ver más detalle.'
            );
        } finally {
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
        }
    }

    private function resolverTexto(string $text): string
    {
        $command = LeonidasWhatsAppProtocol::comando($text);
        $assistant = new LeonidasAssistantService();

        if ($command === 'confirmar') {
            $token = (string) ($_SESSION['leonidas_whatsapp_pending_token'] ?? '');
            if ($token === '') {
                return 'No hay una acción pendiente por confirmar. Escribe primero lo que necesitas que haga.';
            }
            $response = $assistant->confirmar($token);
            unset($_SESSION['leonidas_whatsapp_pending_token']);
            return LeonidasWhatsAppProtocol::textoRespuesta($response);
        }

        if ($command === 'cancelar') {
            $token = (string) ($_SESSION['leonidas_whatsapp_pending_token'] ?? '');
            if ($token === '') {
                return 'No hay una acción pendiente por cancelar.';
            }
            $response = $assistant->cancelar($token);
            unset($_SESSION['leonidas_whatsapp_pending_token']);
            return LeonidasWhatsAppProtocol::textoRespuesta($response);
        }

        $response = $assistant->conversar($text);
        $proposal = is_array($response['propuesta'] ?? null) ? $response['propuesta'] : [];
        $token = trim((string) ($proposal['token'] ?? ''));
        if ($token !== '') {
            $_SESSION['leonidas_whatsapp_pending_token'] = $token;
        }
        return LeonidasWhatsAppProtocol::textoRespuesta($response);
    }

    private function abrirSesionAislada(array $persona, string $phone): void
    {
        EnvLoader::load();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $secret = trim((string) getenv('WHATSAPP_SESSION_SECRET'));
        if ($secret === '') {
            throw new \RuntimeException('Falta configurar WHATSAPP_SESSION_SECRET.');
        }

        $personaId = (int) ($persona['id'] ?? 0);
        $sessionKey = hash_hmac('sha256', $personaId . '|' . $phone, $secret);
        session_id('lwa' . substr($sessionKey, 0, 23));
        session_start();

        $fullName = trim(implode(' ', array_filter([
            $persona['nombres'] ?? '',
            $persona['segundo_nombre'] ?? '',
            $persona['apellidop'] ?? '',
            $persona['apellidom'] ?? '',
        ])));
        $_SESSION['login'] = true;
        $_SESSION['usuario_id'] = $personaId;
        $_SESSION['persona_id'] = $personaId;
        $_SESSION['usuario'] = (string) ($persona['user_name'] ?? '');
        $_SESSION['usuario_nombre'] = $fullName;
        $_SESSION['session_version'] = (int) ($persona['session_version'] ?? 1);
        $_SESSION['last_session_check'] = time();
        $_SESSION['modulos'] = array_values(array_map('intval', (array) Login::getModulosUsuario($personaId)));
        $_SESSION['leonidas_session_token'] = $_SESSION['leonidas_session_token'] ?? bin2hex(random_bytes(16));
        $_SESSION['leonidas_channel'] = 'whatsapp';
        $_SESSION['leonidas_whatsapp_phone'] = $phone;
    }

    private function respuestaProcesada(string $messageId): ?string
    {
        $responses = (array) ($_SESSION['leonidas_whatsapp_responses'] ?? []);
        if (!array_key_exists($messageId, $responses)) {
            return null;
        }
        return (string) $responses[$messageId];
    }

    private function recordarRespuesta(string $messageId, string $answer): void
    {
        $responses = (array) ($_SESSION['leonidas_whatsapp_responses'] ?? []);
        unset($responses[$messageId]);
        $responses[$messageId] = $answer;
        $_SESSION['leonidas_whatsapp_responses'] = array_slice($responses, -100, null, true);
    }
}
