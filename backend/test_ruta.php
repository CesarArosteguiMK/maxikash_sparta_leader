<?php
// Crea esto en: sparta___SPARTA_SECRET_REDACTED__/backend/test_ruta.php

echo "DIR actual: " . __DIR__ . "\n";
echo "Ruta SSH Key: " . __DIR__ . '/../config/ssh/jesusssh4.unknown' . "\n";
echo "¿Existe?: " . (file_exists(__DIR__ . '/../config/ssh/jesusssh4.unknown') ? 'SÍ' : 'NO') . "\n";
echo "Ruta real: " . realpath(__DIR__ . '/../config/ssh/jesusssh4.unknown') . "\n";