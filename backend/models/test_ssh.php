<?php
// Crear: backend/models/test_ssh.php
echo "DIR: " . __DIR__ . "\n";
echo "Ruta SSH: " . __DIR__ . '/../config/ssh/jesusssh4.unknown' . "\n";
echo "¿Existe?: " . (file_exists(__DIR__ . '/../config/ssh/jesusssh4.unknown') ? 'SÍ ✅' : 'NO ❌') . "\n";
$real = realpath(__DIR__ . '/../config/ssh/jesusssh4.unknown');
echo "Ruta real: " . ($real ? $real : 'NO ENCONTRADA') . "\n";