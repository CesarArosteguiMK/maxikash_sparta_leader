<?php

define('TOKEN', '__SPARTA_TOKEN_REDACTED__');  // tu token real

define('ENDPOINT', 'https://servicios.s2movil.net/s2__SPARTA_SECRET_REDACTED__/estadocuenta'); // tu endpoint real

// API key para crear tickets desde el bot de WhatsApp (cabecera X-API-Key o body api_key).
define('TICKET_WHATSAPP_API_KEY', 'cambiar_clave_secreta_whatsapp_' . md5('sparta___SPARTA_SECRET_REDACTED__'));

// API key para OpenAI / análisis IA (legacy). Si se usa Gemini, esta no se usa.
define('OPENAI_API_KEY', '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');
// API key para Google Gemini (Analizar IA, Resumir ubicaciones, Resumen gestiones). Modelo: gemini-2.5-flash
define('GEMINI_API_KEY', 'AIzaSyAJ2QqUSPn8nT1edfwJVXeXGL_9i4vmbsY');
// Si en XAMPP/local da error de SSL, ponga true SOLO para probar (no recomendado en producción).
define('OPENAI_SSL_VERIFY', true);

// Mapa en modal Iniciar rastreo: clave de Google Maps (Maps JavaScript API). Sin clave se usa Leaflet/CartoDB.
define('GOOGLE_MAPS_API_KEY', 'AIzaSyB2oudGwnMDhpyUsO6jGkiblGVlWDV5w1M');
