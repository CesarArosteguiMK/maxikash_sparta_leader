<?php

require_once dirname(__DIR__) . '/backend/core/DatabaseCliSupport.php';

use Core\DatabaseCliSupport;

$originalGet = $_GET;
$originalUri = $_SERVER['REQUEST_URI'] ?? null;

try {
    $_GET = [];
    $_SERVER['REQUEST_URI'] = '/api/analytics/compliance/1600?force=true';
    if (!DatabaseCliSupport::esApiAnalyticsJsonRequest()) {
        fwrite(STDERR, "La ruta de cumplimiento gestor no esta protegida como JSON.\n");
        exit(1);
    }

    $_SERVER['REQUEST_URI'] = '/sparta_ledger/public/api/analytics/spatial/1600';
    if (!DatabaseCliSupport::esApiAnalyticsJsonRequest()) {
        fwrite(STDERR, "La ruta de analitica bajo subdirectorio no esta protegida.\n");
        exit(1);
    }

    $_SERVER['REQUEST_URI'] = '/api/analytics/compliance/no-valido';
    if (DatabaseCliSupport::esApiAnalyticsJsonRequest()) {
        fwrite(STDERR, "Una ruta de analitica invalida no debe marcarse como endpoint valido.\n");
        exit(1);
    }

    $_GET = ['url' => 'api/analytics/payments/1600'];
    $_SERVER['REQUEST_URI'] = '/index.php';
    if (!DatabaseCliSupport::esApiAnalyticsJsonRequest()) {
        fwrite(STDERR, "La ruta reescrita de analitica no esta protegida.\n");
        exit(1);
    }

    $javascript = file_get_contents(dirname(__DIR__) . '/public/assets/js/analytics-modals.js');
    foreach ([
        "response.text()",
        "JSON.parse(raw)",
        "response.status === 503",
        "credentials: 'same-origin'",
        "escapeHtml(err.message",
    ] as $proteccion) {
        if ($javascript === false || strpos($javascript, $proteccion) === false) {
            fwrite(STDERR, "Falta proteccion del modal: {$proteccion}.\n");
            exit(1);
        }
    }

    $api = file_get_contents(dirname(__DIR__) . '/backend/controllers/Api.php');
    foreach ([
        'GestionesDAO::resetHistoricoDbFalloFlag()',
        'GestionesDAO::huboHistoricoDbFallo()',
        "\$out['fuentes_incompletas'] = true",
        'Una fuente histórica de gestiones no respondió.',
    ] as $proteccion) {
        if ($api === false || strpos($api, $proteccion) === false) {
            fwrite(STDERR, "Falta indicar la fuente historica incompleta: {$proteccion}.\n");
            exit(1);
        }
    }
} finally {
    $_GET = $originalGet;
    if ($originalUri === null) {
        unset($_SERVER['REQUEST_URI']);
    } else {
        $_SERVER['REQUEST_URI'] = $originalUri;
    }
}

echo "AnalyticsComplianceResilienceTest OK\n";
