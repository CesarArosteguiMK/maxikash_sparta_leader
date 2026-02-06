<?php

define('TOKEN', '__SPARTA_TOKEN_REDACTED__');  // tu token real

define('ENDPOINT', 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta'); // tu endpoint real

// API key para crear tickets desde el bot de WhatsApp (cabecera X-API-Key o body api_key).
define('TICKET_WHATSAPP_API_KEY', 'cambiar_clave_secreta_whatsapp_' . md5('sparta___SPARTA_SECRET_REDACTED__'));

// --- API keys desde BD (tabla config_api, valor en texto plano). ---
// No se usa clave maestra; se lee directamente. Para no mostrar en pantalla use config_api_for_display().
require_once __DIR__ . '/ConfigApi.php';

$apiConfig = config_api_load_from_db();

if (!defined('OPENAI_API_KEY')) {
    define('OPENAI_API_KEY', $apiConfig['OPENAI_API_KEY'] ?? '');
}
if (!defined('GEMINI_API_KEY')) {
    define('GEMINI_API_KEY', $apiConfig['GEMINI_API_KEY'] ?? '');
}
if (!defined('GOOGLE_MAPS_API_KEY')) {
    define('GOOGLE_MAPS_API_KEY', $apiConfig['GOOGLE_MAPS_API_KEY'] ?? '');
}
if (!defined('OPENAI_SSL_VERIFY')) {
    $v = $apiConfig['OPENAI_SSL_VERIFY'] ?? '1';
    define('OPENAI_SSL_VERIFY', ($v === '0' || $v === 'false') ? false : true);
}
