<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Services\LeonidasKnowledgeGapService;

$summary = (new LeonidasKnowledgeGapService())->resumen();
echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
