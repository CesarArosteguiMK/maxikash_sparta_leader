# Arranca en paralelo los servicios locales de Sparta Ledger (Node + Docker API).
# Lo invoca iniciar-todos-los-servicios.bat con -BackendRoot (carpeta backend).
param(
    [Parameter(Mandatory = $true)]
    [string] $BackendRoot
)

$ErrorActionPreference = 'Continue'
$BackendRoot = $BackendRoot.TrimEnd('/', '\')

function Write-Step {
    param([string] $Msg)
    Write-Host $Msg -ForegroundColor Cyan
}

Write-Host ''
Write-Host '============================================' -ForegroundColor Yellow
Write-Host '  Sparta Ledger - arranque de servicios' -ForegroundColor Yellow
Write-Host '============================================' -ForegroundColor Yellow
Write-Host "  Carpeta backend: $BackendRoot"
Write-Host ''

$nodeExe = $null
foreach ($p in @(
        'C:\Program Files\nodejs\node.exe',
        'C:\Program Files (x86)\nodejs\node.exe',
        "$env:LocalAppData\Programs\node\node.exe"
    )) {
    if (Test-Path -LiteralPath $p) {
        $nodeExe = $p
        break
    }
}
if (-not $nodeExe) {
    Write-Host '[AVISO] No se encontro node.exe. Los agentes Node no se arrancaran.' -ForegroundColor DarkYellow
} else {
    Write-Host "[OK] Node: $nodeExe"
}

# --- 1) API documentacion candidato (3001) ---
$docBat = Join-Path $BackendRoot 'API\documentacion-candidato\iniciar-agente.bat'
if (Test-Path -LiteralPath $docBat) {
    Write-Step '[1/4] API documentacion candidato (puerto 3001)...'
    Start-Process -FilePath $docBat -WorkingDirectory (Split-Path -Parent $docBat) -WindowStyle Minimized
} else {
    Write-Host '[SKIP] No existe documentacion-candidato\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 400

# --- 2) Agente Segundometro (3100) ---
$segBat = Join-Path $BackendRoot 'services\segundometro-agent\iniciar-agente.bat'
if (($nodeExe) -and (Test-Path -LiteralPath $segBat)) {
    Write-Step '[2/4] Agente Segundometro (puerto 3100)...'
    Start-Process -FilePath $segBat -WorkingDirectory (Split-Path -Parent $segBat) -WindowStyle Minimized
} elseif (-not $nodeExe) {
    Write-Host '[SKIP] Segundometro (sin Node).' -ForegroundColor DarkYellow
} else {
    Write-Host '[SKIP] No existe segundometro-agent\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 400

# --- 3) Agente correos primeros pagos (3110) - ventana visible para ver logs del cron ---
$corBat = Join-Path $BackendRoot 'services\correos-primeros-pagos-agent\iniciar-agente.bat'
if (($nodeExe) -and (Test-Path -LiteralPath $corBat)) {
    Write-Step '[3/4] Agente correos primeros pagos (puerto 3110)...'
    Start-Process -FilePath $corBat -WorkingDirectory (Split-Path -Parent $corBat) -WindowStyle Normal
} elseif (-not $nodeExe) {
    Write-Host '[SKIP] Correos (sin Node).' -ForegroundColor DarkYellow
} else {
    Write-Host '[SKIP] No existe correos-primeros-pagos-agent\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 400

# --- 4) API Python verificacion documentos (Docker, puerto 8000) ---
$apiDir = Join-Path $BackendRoot 'API'
$compose = Join-Path $apiDir 'docker-compose.yml'
if (Test-Path -LiteralPath $compose) {
    Write-Step '[4/4] API verificacion documentos (Docker -> 8000)...'
    $dockerOk = $false
    try {
        $null = & docker info 2>$null
        if ($LASTEXITCODE -eq 0) { $dockerOk = $true }
    } catch { }
    if ($dockerOk) {
        Push-Location $apiDir
        try {
            & docker compose up -d 2>&1
            if ($LASTEXITCODE -ne 0) {
                Write-Host '[AVISO] docker compose up -d devolvio error. Revise Docker Desktop.' -ForegroundColor DarkYellow
            }
        } finally {
            Pop-Location
        }
    } else {
        Write-Host '[AVISO] Docker no responde. Omitido API Python (8000).' -ForegroundColor DarkYellow
        Write-Host '        Use backend\API\Iniciar-API-Verificacion.bat cuando Docker este listo.' -ForegroundColor DarkYellow
    }
} else {
    Write-Host '[SKIP] No hay docker-compose.yml en API.' -ForegroundColor DarkYellow
}

Write-Host ''
Write-Host 'Resumen de puertos esperados:' -ForegroundColor Green
Write-Host '  3001  documentacion candidato (Node)'
Write-Host '  3100  agente Segundometro (Node)'
Write-Host '  3110  agente correos primeros pagos (Node)'
Write-Host '  8000  API verificacion documentos (Docker / uvicorn)'
Write-Host ''
Write-Host 'Para detener: backend\servicios-locales\cerrar-todos-los-servicios.bat'
Write-Host ''
