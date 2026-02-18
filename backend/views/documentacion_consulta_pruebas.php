<?php
// Solo flujo de descarga por URL externa S3, obteniendo el nombre del archivo desde la BD
function getNombreArchivoDesdeBD($idOferta, $tipoDocumento, &$conexion_bd = null, &$error_bd = null) {
    // Configuración de conexión (ajusta según tu entorno)
    $host = '__SPARTA_HOST_REDACTED__';
    $db   = '__SPARTA_SECRET_REDACTED__';
    $user = '__SPARTA_SECRET_REDACTED__';
    $pass = '__SPARTA_PASSWORD_REDACTED__';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        // Verifica a qué base de datos está conectado
        $conexion_bd = $pdo->query('SELECT DATABASE()')->fetchColumn();
        $stmt = $pdo->prepare("SELECT nombre_archivo FROM oferta_documentos WHERE fk_oferta = ? AND tipo_documento = ? LIMIT 1");
        $stmt->execute([$idOferta, $tipoDocumento]);
        $row = $stmt->fetch();
        return $row ? $row['nombre_archivo'] : null;
    } catch (Exception $e) {
        $error_bd = $e->getMessage();
        return null;
    }
}

function tryDownloadFromExternalUrl($fileName) {
    $baseUrl = 'http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=';
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idCredito'], $_POST['tipoDocumento']) && $_POST['idCredito'] && $_POST['tipoDocumento']) {
    $idOferta = trim($_POST['idCredito']);
    $tipoDocumento = trim($_POST['tipoDocumento']);
    $conexion_bd = null;
    $error_bd = null;
    $nombreArchivo = getNombreArchivoDesdeBD($idOferta, $tipoDocumento, $conexion_bd, $error_bd);
    $fileName = null;
    $externalUrl = null;
    $success = false;
    if ($nombreArchivo) {
        $fileName = $tipoDocumento . '/' . $nombreArchivo;
        $baseUrl = 'http://98.90.194.116/audit-app-0.0.1-SNAPSHOT_1/s3/downloadS3File?fileName=';
        $externalUrl = $baseUrl . urlencode($fileName);
        $success = true;
        $mensaje = 'Archivo encontrado.';
    } else {
        $mensaje = 'No se encontró el documento para ese ID y tipo.';
    }
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'nombre_archivo' => $fileName,
        'url' => $externalUrl,
        'mensaje' => $mensaje,
        'conexion_bd' => $conexion_bd,
        'error_bd' => $error_bd
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PRUEBAS - Documentación solo URL externa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>body { font-family: Arial, sans-serif; margin: 2em; }</style>
</head>
<body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (!form) return;
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const formData = new FormData(form);
        const data = new URLSearchParams();
        for (const pair of formData) {
            data.append(pair[0], pair[1]);
        }
        const response = await fetch(window.location.pathname, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: data
        });
        const json = await response.json();
        console.log('Respuesta del servidor:', json);
        if (json.success && json.url) {
            // Descargar automáticamente
            window.location.href = json.url;
        } else {
            alert(json.mensaje || 'No se encontró el archivo.');
        }
    });
});
</script>
<div class="container py-4">
    <div class="row mb-3">
        <div class="col-12">
            <h4 class="mb-0">PRUEBAS - Documentación solo URL externa</h4>
            <p class="text-muted small">Busca y descarga documentos directamente desde S3 (solo con ID de crédito y tipo de documento)</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="post" class="row g-3 align-items-end" autocomplete="off">
                <div class="col-12 col-md-6">
                    <label for="idCredito" class="form-label">ID de crédito (oferta)</label>
                    <input type="text" class="form-control" id="idCredito" name="idCredito" value="" placeholder="Ej.: 737196" autocomplete="off">
                </div>
                <div class="col-12 col-md-6">
                    <label for="tipoDocumento" class="form-label">Tipo de documento</label>
                    <select class="form-select" id="tipoDocumento" name="tipoDocumento">
                        <option value="">Selecciona un documento</option>
                        <option value="INE">INE</option>
                        <option value="FACTURA">FACTURA</option>
                        <option value="CONTRATO">VALIDACIONES</option>
                        <option value="FAD_DOC">FAD_DOC</option>
                        <option value="EVIDENCIA">EVIDENCIA</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary w-100">Descargar desde S3</button>
                </div>
            </form>
            <?php if ($mensaje): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>