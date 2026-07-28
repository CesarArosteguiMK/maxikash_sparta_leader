<?php
// Módulo de pruebas para descarga de documentos solo por URL externa
// Título: PRUEBAS - Descarga por URL externa

function tryDownloadFromExternalUrl($fileName) {
    $baseUrl = 'https://gv23a4ht7564jqphca5czszrdy0bfsjw.lambda-url.us-east-1.on.aws/?fileName=';
    $externalUrl = $baseUrl . urlencode($fileName);
    $headers = @get_headers($externalUrl);
    if ($headers && strpos($headers[0], '200') !== false) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
        readfile($externalUrl);
        exit;
    }
    return false;
}

$mensaje = '';
if (isset($_GET['fileName'])) {
    $fileName = trim($_GET['fileName']);
    if ($fileName !== '') {
        if (!tryDownloadFromExternalUrl($fileName)) {
            $mensaje = 'No se encontró el archivo en la URL externa.';
        }
    } else {
        $mensaje = 'Debes ingresar un nombre de archivo.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PRUEBAS - Descarga por URL externa</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        .formulario { margin-bottom: 2em; }
        .mensaje { color: #f00; margin-top: 1em; }
    </style>
</head>
<body>
    <h1>PRUEBAS - Descarga por URL externa</h1>
    <form class="formulario" method="get">
        <label>Nombre de archivo (ej: FACTURA/100006_factura.pdf):
            <input type="text" name="fileName" style="width:400px" value="<?= isset($_GET['fileName']) ? htmlspecialchars($_GET['fileName']) : '' ?>">
        </label>
        <button type="submit">Probar descarga</button>
    </form>
    <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
</body>
</html>
