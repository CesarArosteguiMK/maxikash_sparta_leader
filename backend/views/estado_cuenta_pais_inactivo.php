<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titulo ?? 'País Inactivo') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Flag Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@6.6.6/css/flag-icons.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .error-container {
            max-width: 600px;
            width: 100%;
        }
        
        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem 2.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .flag-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            position: relative;
        }
        
        .flag-container::before {
            content: '';
            position: absolute;
            top: -5px;
            left: -5px;
            right: -5px;
            bottom: -5px;
            background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);
            border-radius: 50%;
            z-index: -1;
            opacity: 0.5;
        }
        
        .flag-container .fi {
            font-size: 4rem;
            line-height: 1;
        }
        
        .warning-icon {
            position: absolute;
            bottom: -5px;
            right: -5px;
            width: 50px;
            height: 50px;
            background: #ffc107;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.5);
            border: 4px solid rgba(255, 255, 255, 0.95);
        }
        
        h1 {
            color: #2c3e50;
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .country-name {
            color: #667eea;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: block;
        }
        
        .error-message {
            color: #5a6c7d;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .info-box {
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            border-left: 4px solid #ffc107;
            border-radius: 12px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .info-box p {
            margin: 0;
            color: #856404;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .info-box strong {
            color: #533f03;
            display: block;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .btn-back {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            padding: 0.875rem 2.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(102, 126, 234, 0.5);
            color: #fff;
        }
        
        .credit-id {
            font-size: 0.85rem;
            color: #95a5a6;
            margin-top: 1.5rem;
        }
        
        @media (max-width: 576px) {
            .error-card {
                padding: 2rem 1.5rem;
            }
            
            h1 {
                font-size: 1.5rem;
            }
            
            .country-name {
                font-size: 1.25rem;
            }
            
            .flag-container {
                width: 100px;
                height: 100px;
            }
            
            .flag-container .fi {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <!-- Bandera con icono de advertencia -->
            <div class="flag-container">
                <span class="fi fi-<?= htmlspecialchars($codigoIsoPais ?? 'xx') ?> fis"></span>
                <div class="warning-icon">
                    <i class="fa-solid fa-exclamation"></i>
                </div>
            </div>
            
            <!-- Título -->
            <h1>
                <i class="fa-solid fa-ban me-2"></i>
                País Inactivo
            </h1>
            
            <!-- Nombre del país -->
            <span class="country-name">
                <?= htmlspecialchars($nombrePais ?? 'País desconocido') ?>
            </span>
            
            <!-- Mensaje de error -->
            <p class="error-message">
                <?= nl2br(htmlspecialchars($errorPais ?? 'Este crédito pertenece a un país que actualmente está inactivo en el sistema.')) ?>
            </p>
            
            <!-- Información adicional -->
            <div class="info-box">
                <strong>
                    <i class="fa-solid fa-circle-info me-2"></i>
                    ¿Qué significa esto?
                </strong>
                <p>
                    El estado de cuenta no puede ser consultado en este momento porque el país asociado 
                    a este crédito está marcado como inactivo. Por favor, contacta al administrador del 
                    sistema para más información.
                </p>
            </div>
            
            <!-- Botón de regreso -->
            <a href="<?= BASE_URL ?>/EstadoCuenta" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
                Volver a la búsqueda
            </a>
            
            <!-- ID del crédito -->
            <?php if (isset($idCredito)): ?>
            <p class="credit-id">
                <i class="fa-solid fa-hashtag me-1"></i>
                ID Crédito: <strong><?= htmlspecialchars($idCredito) ?></strong>
            </p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
