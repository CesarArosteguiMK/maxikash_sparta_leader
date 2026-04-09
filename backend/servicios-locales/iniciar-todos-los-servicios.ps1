# Arranca en paralelo los servicios locales de Sparta Ledger (Node + API Python local).
# Lo invoca iniciar-todos-los-servicios.bat con -BackendRoot (carpeta backend).
param(
    [Parameter(Mandatory = $true)]
    [string] $BackendRoot,
    # Lo pone iniciar-todos-los-servicios-oculto.vbs: agentes y docker sin ventanas visibles.
    [switch] $SinVentanas
)

$ErrorActionPreference = 'Continue'
$BackendRoot = $BackendRoot.TrimEnd('/', '\')
$winAgent = if ($SinVentanas) { 'Hidden' } else { 'Minimized' }

function Write-Step {
    param([string] $Msg)
    Write-Host $Msg -ForegroundColor Cyan
}

Write-Host ''
Write-Host '============================================' -ForegroundColor Yellow
Write-Host '  Sparta Ledger - arranque de servicios' -ForegroundColor Yellow
Write-Host '============================================' -ForegroundColor Yellow
Write-Host "  Carpeta backend: $BackendRoot"
if ($SinVentanas) {
    Write-Host '  Modo: sin ventanas (agentes y API 8000 ocultos)' -ForegroundColor DarkGray
}
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
    Write-Step '[1/5] API documentacion candidato (puerto 3001)...'
    Start-Process -FilePath $docBat -WorkingDirectory (Split-Path -Parent $docBat) -WindowStyle $winAgent
} else {
    Write-Host '[SKIP] No existe documentacion-candidato\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 400

# --- 2) Agente Segundometro (3100) ---
$segBat = Join-Path $BackendRoot 'services\segundometro-agent\iniciar-agente.bat'
if (($nodeExe) -and (Test-Path -LiteralPath $segBat)) {
    Write-Step '[2/5] Agente Segundometro (puerto 3100)...'
    Start-Process -FilePath $segBat -WorkingDirectory (Split-Path -Parent $segBat) -WindowStyle $winAgent
} elseif (-not $nodeExe) {
    Write-Host '[SKIP] Segundometro (sin Node).' -ForegroundColor DarkYellow
} else {
    Write-Host '[SKIP] No existe segundometro-agent\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 400

# --- 3) Agente correos primeros pagos (3110) ---
$corBat = Join-Path $BackendRoot 'services\correos-primeros-pagos-agent\iniciar-agente.bat'
if (($nodeExe) -and (Test-Path -LiteralPath $corBat)) {
    Write-Step '[3/5] Agente correos primeros pagos (puerto 3110)...'
    Start-Process -FilePath $corBat -WorkingDirectory (Split-Path -Parent $corBat) -WindowStyle $winAgent
} elseif (-not $nodeExe) {
    Write-Host '[SKIP] Correos (sin Node).' -ForegroundColor DarkYellow
} else {
    Write-Host '[SKIP] No existe correos-primeros-pagos-agent\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 400

# --- 4) Agente Gastos cobranza (3120) ---
$gcBat = Join-Path $BackendRoot 'services\gastos-cobranza-agent\iniciar-agente.bat'
if (($nodeExe) -and (Test-Path -LiteralPath $gcBat)) {
    Write-Step '[4/5] Agente Gastos cobranza (puerto 3120)...'
    Start-Process -FilePath $gcBat -WorkingDirectory (Split-Path -Parent $gcBat) -WindowStyle $winAgent
} elseif (-not $nodeExe) {
    Write-Host '[SKIP] Gastos cobranza (sin Node).' -ForegroundColor DarkYellow
} else {
    Write-Host '[SKIP] No existe gastos-cobranza-agent\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 400

# --- 5) API Python verificacion documentos (uvicorn puerto 8000; global o venv si existe) ---
$apiDir = Join-Path $BackendRoot 'API'
$apiOcultoPs1 = Join-Path $apiDir 'iniciar-agente-oculto.ps1'
$apiOcultoVbs = Join-Path $apiDir 'iniciar-agente-oculto.vbs'
if (Test-Path -LiteralPath $apiOcultoPs1) {
    Write-Step '[5/5] API verificacion documentos (Python -> 8000, segundo plano)...'
    Start-Process -FilePath 'powershell.exe' -ArgumentList @(
        '-NoProfile', '-ExecutionPolicy', 'Bypass', '-WindowStyle', 'Hidden', '-File', $apiOcultoPs1
    ) -WorkingDirectory $apiDir -WindowStyle Hidden
} elseif (Test-Path -LiteralPath $apiOcultoVbs) {
    Write-Step '[5/5] API verificacion documentos (solo .vbs -> 8000)...'
    Start-Process -FilePath 'wscript.exe' -ArgumentList @('//nologo', $apiOcultoVbs) -WorkingDirectory $apiDir -WindowStyle Hidden
} else {
    Write-Host '[SKIP] No hay API\iniciar-agente-oculto.ps1 ni .vbs' -ForegroundColor DarkYellow
}

Write-Host ''
Write-Host 'Resumen de puertos esperados:' -ForegroundColor Green
Write-Host '  3001  documentacion candidato (Node)'
Write-Host '  3100  agente Segundometro (Node)'
Write-Host '  3110  agente correos primeros pagos (Node)'
Write-Host '  3120  agente Gastos cobranza (Node)'
Write-Host '  8000  API verificacion documentos (Python global o venv + uvicorn; Docker opcional)'
Write-Host ''
Write-Host 'Para detener: backend\servicios-locales\cerrar-todos-los-servicios.bat'
Write-Host ''
