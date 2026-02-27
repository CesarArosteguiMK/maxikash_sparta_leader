<?php
$error_mensaje = $error_mensaje ?? '';
$token = $token ?? '';
$nombre_candidato = $nombre_candidato ?? '';
$id_candidato = (int)($id_candidato ?? 0);
$documentos = [
    1  => 'SOLICITUD INTERNA',
    2  => 'CV O SOLICITUD DE TRABAJO',
    3  => 'ACTA DE NACIMIENTO',
    4  => 'CURP',
    5  => 'IDENTIFICACIÓN OFICIAL',
    6  => 'COMPROBANTE DE DOMICILIO',
    7  => 'CONSTANCIA DE SITUACION FISCAL',
    8  => 'NÚMERO DE SEGURIDAD SOCIAL',
    9  => 'HOJA DE RETENCION FONACOT O INFONAVIT',
    10 => 'ESTADO DE CUENTA',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir documentos | Maxikash</title>
    <link rel="icon" type="image/x-icon" href="/assets/img/logo_ico2.svg">
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/vendor/fonts/fontawesome.css">
    <link rel="stylesheet" href="/assets/vendor/css/core.css">
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
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            max-width: 560px;
            width: 100%;
            overflow: hidden;
        }
        .form-card-header {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 100%);
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
            font-size: 0.9rem;
            opacity: 0.95;
        }
        .form-card-body {
            padding: 2rem;
            max-height: 70vh;
            overflow-y: auto;
        }
        .candidato-name {
            background: #f1f5f9;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            color: #334155;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.35rem;
            font-size: 0.85rem;
        }
        .form-group .form-control-file {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            background: #f8fafc;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .form-group .form-control-file:hover {
            border-color: #6366f1;
            background: #eef2ff;
        }
        .btn-submit {
            width: 100%;
            padding: 0.9rem 1.5rem;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        .btn-submit:hover {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .small-text {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.5rem;
        }
        .btn-descarga {
            display: inline-flex;
            align-items: center;
            padding: 0.4rem 0.75rem;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-descarga:hover {
            background: #c7d2fe;
            color: #312e81;
        }
    </style>
</head>
<body>
    <div class="form-card">
        <div class="form-card-header">
            <img src="/assets/img/logo_ico2.svg" alt="Maxikash" onerror="this.style.display='none'">
            <h1>Subir documentos</h1>
            <p>Completa tu postulación adjuntando tus documentos</p>
        </div>
        <div class="form-card-body">
            <?php if ($error_mensaje !== ''): ?>
                <div class="alert-error"><?= htmlspecialchars($error_mensaje) ?></div>
            <?php else: ?>
                <div class="candidato-name"><?= htmlspecialchars($nombre_candidato) ?></div>
                <form id="formSubirDocumentos" action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <?php
                    $urlBaseDescarga = '/CapHum/descargarDocumentoCandidato/' . urlencode($token);
                    foreach ($documentos as $num => $nombreDoc):
                        $esSolicitud = ($num === 1);
                        $esCartaAdeudo = ($num === 9);
                    ?>
                    <div class="form-group">
                        <label for="archivo_<?= $num ?>"><?= $num ?>. <?= htmlspecialchars($nombreDoc) ?></label>
                        <?php if ($esSolicitud): ?>
                        <div class="descarga-doc mb-2">
                            <a href="<?= htmlspecialchars($urlBaseDescarga) ?>/solicitud_interna" class="btn-descarga" target="_blank" rel="noopener"><i class="fa fa-download me-1"></i> Descargar solicitud (con tus datos)</a>
                            <span class="d-block small-text mt-1">Descarga el PDF, fírmalo y súbelo aquí.</span>
                        </div>
                        <?php elseif ($esCartaAdeudo): ?>
                        <div class="descarga-doc mb-2">
                            <a href="<?= htmlspecialchars($urlBaseDescarga) ?>/carta_no_adeudo" class="btn-descarga" target="_blank" rel="noopener"><i class="fa fa-download me-1"></i> Descargar carta de no adeudo</a>
                            <span class="d-block small-text mt-1">Si no tienes hoja de retención, descarga esta carta, fírmala y súbela.</span>
                        </div>
                        <?php endif; ?>
                        <input type="file" class="form-control-file" id="archivo_<?= $num ?>" name="archivo_<?= $num ?>" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                    <?php endforeach; ?>
                    <p class="small-text">Formatos permitidos: PDF, DOC, DOCX, JPG, PNG. Sube los documentos que tengas; no es obligatorio llenar todos a la vez.</p>
                    <button type="submit" class="btn-submit" id="btnEnviar">Subir documentos</button>
                </form>
                <div id="mensajeResultado"></div>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($error_mensaje === '' && $token !== ''): ?>
    <script>
        document.getElementById('formSubirDocumentos').addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('btnEnviar');
            var msg = document.getElementById('mensajeResultado');
            var form = document.getElementById('formSubirDocumentos');
            var hasFile = false;
            for (var i = 1; i <= 10; i++) {
                var input = document.getElementById('archivo_' + i);
                if (input && input.files && input.files.length > 0) { hasFile = true; break; }
            }
            if (!hasFile) {
                msg.innerHTML = '<div class="alert-error">Selecciona al menos un documento.</div>';
                return;
            }
            btn.disabled = true;
            btn.textContent = 'Subiendo...';
            msg.innerHTML = '';
            var formData = new FormData(form);
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(function(r) { return r.json(); }).then(function(res) {
                btn.disabled = false;
                btn.textContent = 'Subir documentos';
                if (res.success) {
                    msg.innerHTML = '<div class="alert-success">' + (res.mensaje || 'Documentos subidos correctamente.') + '</div>';
                    form.reset();
                } else {
                    msg.innerHTML = '<div class="alert-error">' + (res.mensaje || 'Error al subir.') + '</div>';
                }
            }).catch(function() {
                btn.disabled = false;
                btn.textContent = 'Subir documentos';
                msg.innerHTML = '<div class="alert-error">Error de conexión. Intenta de nuevo.</div>';
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
