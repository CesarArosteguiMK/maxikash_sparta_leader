<?php
// Nombre del archivo que queremos incluir
$archivo = __DIR__ . '/../backend/Controllers/Login.php';

// Mostrar la ruta que PHP intentará abrir
echo "Ruta que PHP intenta cargar: $archivo\n\n";

// Verificar si existe
if (file_exists($archivo)) {
    echo "✅ El archivo Login.php existe.\n";
} else {
    echo "❌ No se encontró el archivo Login.php.\n\n";

    // Listar todos los archivos en el directorio para verificar nombres
    $directorio = __DIR__ . '/../backend/Controllers';
    if (is_dir($directorio)) {
        echo "Archivos en $directorio:\n";
        $archivos = scandir($directorio);
        foreach ($archivos as $a) {
            echo " - $a\n";
        }
    } else {
        echo "❌ No se encontró el directorio $directorio\n";
    }
}
