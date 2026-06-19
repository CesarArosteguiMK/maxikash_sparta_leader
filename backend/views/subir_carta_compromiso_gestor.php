<?php
$token = $token ?? '';
$nombre_candidato = $nombre_candidato ?? '';
$puesto_candidato = $puesto_candidato ?? 'Gestor';
$carta_pdf_url = $carta_pdf_url ?? '/CapHum/descargarCartaCompromisoGestor';
$mensaje_exito = $mensaje_exito ?? '';
$mensaje_error = $mensaje_error ?? '';
$carta_subida = !empty($carta_subida);
$modo_prueba = !empty($modo_prueba);
$subida_bloqueada = !empty($subida_bloqueada);
$base_url_app = rtrim((string) ($base_url_app ?? ''), '/');
$asset_base = $base_url_app !== '' ? $base_url_app : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta de compromiso del Gestor | Maxikash</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo htmlspecialchars($asset_base . '/assets/img/logo_ico.svg'); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($asset_base . '/assets/vendor/fonts/fontawesome.css'); ?>">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Public Sans', sans-serif;
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .form-card {
            width: 100%;
            max-width: 720px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .form-card-header {
            background: linear-gradient(135deg, #2c5282 0%, #3182ce 100%);
            color: #fff;
            padding: 1.75rem 2rem;
            text-align: center;
        }
        .form-card-header img {
            height: 48px;
            width: auto;
            margin-bottom: 0.75rem;
        }
        .form-card-header h1 {
            margin: 0;
            font-size: 1.35rem;
            font-weight: 700;
        }
        .form-card-header p {
            margin: 0.5rem 0 0;
            font-size: 0.95rem;
            opacity: 0.95;
        }
        .form-card-body { padding: 2rem; }
        .candidato-name {
            background: #edf2f7;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.25rem;
            font-weight: 600;
            color: #2d3748;
        }
        .notice {
            border: 1px solid #f6ad55;
            background: #fffaf0;
            border-radius: 12px;
            padding: 1rem;
            color: #2d3748;
            line-height: 1.55;
            margin-bottom: 1.25rem;
        }
        .download-btn,
        .submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border: 0;
            border-radius: 10px;
            padding: 0.85rem 1.15rem;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }
        .download-btn {
            background: #d99a32;
            color: #fff;
            margin-bottom: 1.25rem;
        }
        .submit-btn {
            width: 100%;
            background: #2f7dc5;
            color: #fff;
            font-size: 1rem;
            margin-top: 1rem;
        }
        .submit-btn:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }
        .upload-box {
            border: 2px dashed #f6ad55;
            border-radius: 12px;
            padding: 1rem;
            background: #fffaf0;
        }
        .upload-box input {
            width: 100%;
            font-size: 0.95rem;
        }
        .alert {
            border-radius: 10px;
            padding: 0.85rem 1rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .alert-success {
            background: #ecfdf3;
            color: #166534;
            border: 1px solid #86efac;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .muted {
            color: #64748b;
            font-size: 0.9rem;
            margin-top: 0.75rem;
        }
    </style>
</head>
<body>
    <main class="form-card">
        <header class="form-card-header">
            <img src="<?php echo htmlspecialchars($asset_base . '/assets/img/logo___SPARTA_SECRET_REDACTED__.png'); ?>" alt="Maxikash">
            <h1>Carta de compromiso del Gestor</h1>
            <p>Descarga, llena, firma y sube tu carta en PDF.</p>
        </header>
        <section class="form-card-body">
            <div class="candidato-name">
                <?php echo htmlspecialchars($nombre_candidato); ?><br>
                <span style="font-weight:500;color:#64748b;"><?php echo htmlspecialchars($puesto_candidato); ?></span>
            </div>

            <?php if ($mensaje_exito !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($mensaje_exito); ?></div>
            <?php endif; ?>
            <?php if ($mensaje_error !== ''): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($mensaje_error); ?></div>
            <?php endif; ?>
            <?php if ($modo_prueba): ?>
                <div class="alert alert-success">Vista de prueba habilitada. Puedes revisar la pantalla y descargar la carta; la subida real requiere un enlace seguro asignado.</div>
            <?php endif; ?>

            <div class="notice">
                Primero descarga el formato, llenalo y firmalo. Despues regresa a esta pagina para subir el PDF firmado y completar tu expediente de Gestor.
            </div>

            <a class="download-btn" href="<?php echo htmlspecialchars($carta_pdf_url); ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-download"></i>
                Descargar carta
            </a>

            <?php if ($carta_subida): ?>
                <div class="alert alert-success">La carta ya fue subida y quedo integrada al expediente.</div>
            <?php else: ?>
                <form method="POST" enctype="multipart/form-data">
                    <label for="archivo_carta"><strong>Archivo PDF firmado:</strong></label>
                    <div class="upload-box">
                        <input type="file" id="archivo_carta" name="archivo_carta" accept="application/pdf,.pdf" required>
                    </div>
                    <p class="muted">Solo se permite un archivo PDF.</p>
                    <button class="submit-btn" type="submit" <?php echo ($modo_prueba || $subida_bloqueada) ? 'disabled' : ''; ?>>
                        <i class="fa-solid fa-upload"></i>
                        <?php echo ($modo_prueba || $subida_bloqueada) ? 'Subida deshabilitada' : 'Subir carta firmada'; ?>
                    </button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
