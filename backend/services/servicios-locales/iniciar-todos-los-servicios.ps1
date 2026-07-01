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

function Test-PortListening {
    param([int] $Port)
    try {
        $c = Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue
        if ($c) { return $true }
    } catch {}
    foreach ($line in (netstat -ano 2>$null)) {
        if ($line -match 'LISTENING' -and $line -match ":$Port\s") { return $true }
    }
    return $false
}

function Wait-PortListening {
    param(
        [int] $Port,
        [int] $TimeoutSec = 25
    )
    $deadline = (Get-Date).AddSeconds($TimeoutSec)
    while ((Get-Date) -lt $deadline) {
        if (Test-PortListening -Port $Port) { return $true }
        Start-Sleep -Milliseconds 500
    }
    return (Test-PortListening -Port $Port)
}

function Test-NodeAgentReady {
    param(
        [string] $AgentDir,
        [string] $AgentName
    )
    $pkg = Join-Path $AgentDir 'package.json'
    $mods = Join-Path $AgentDir 'node_modules'
    if (-not (Test-Path -LiteralPath $pkg)) {
        Write-Host "      [SKIP] ${AgentName}: falta package.json en $AgentDir" -ForegroundColor DarkYellow
        return $false
    }
    if (-not (Test-Path -LiteralPath $mods)) {
        Write-Host "      [SKIP] ${AgentName}: faltan dependencias (node_modules)." -ForegroundColor DarkYellow
        Write-Host "             Ejecute backend\\services\\servicios-locales\\instalar-todos-deps-node.bat" -ForegroundColor DarkYellow
        return $false
    }
    return $true
}

Write-Host ''
Write-Host '============================================' -ForegroundColor Yellow
Write-Host '  Sparta Ledger - arranque de servicios' -ForegroundColor Yellow
Write-Host '============================================' -ForegroundColor Yellow
Write-Host "  Carpeta backend: $BackendRoot"
if ($SinVentanas) {
    Write-Host '  Modo: sin ventanas (agentes y API 8001 ocultos)' -ForegroundColor DarkGray
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
if (Test-PortListening -Port 3001) {
    Write-Host '[1/5] API documentacion candidato (3001) ya está activa.' -ForegroundColor Gray
} elseif (Test-Path -LiteralPath $docBat) {
    $docDir = Split-Path -Parent $docBat
    if (-not (Test-NodeAgentReady -AgentDir $docDir -AgentName 'API documentacion candidato')) { }
    else {
    Write-Step '[1/5] API documentacion candidato (puerto 3001)...'
    Start-Process -FilePath $docBat -WorkingDirectory $docDir -WindowStyle $winAgent
    if (Wait-PortListening -Port 3001 -TimeoutSec 25) {
        Write-Host '      [OK] Puerto 3001 en LISTEN.' -ForegroundColor Green
    } else {
        Write-Host '      [AVISO] No levantó en 3001 dentro de 25s (revise logs/dependencias).' -ForegroundColor DarkYellow
    }
    }
} else {
    Write-Host '[SKIP] No existe documentacion-candidato\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 300

# --- 2) Agente Segundometro (3100) ---
$segBat = Join-Path $BackendRoot 'services\segundometro-agent\iniciar-agente.bat'
if (-not $nodeExe) {
    Write-Host '[SKIP] Segundómetro (sin Node).' -ForegroundColor DarkYellow
} elseif (Test-PortListening -Port 3100) {
    Write-Host '[2/5] Agente Segundómetro (3100) ya está activo.' -ForegroundColor Gray
} elseif (Test-Path -LiteralPath $segBat) {
    $segDir = Split-Path -Parent $segBat
    if (-not (Test-NodeAgentReady -AgentDir $segDir -AgentName 'Agente Segundómetro')) { }
    else {
    Write-Step '[2/5] Agente Segundómetro (puerto 3100)...'
    Start-Process -FilePath $segBat -WorkingDirectory $segDir -WindowStyle $winAgent
    if (Wait-PortListening -Port 3100 -TimeoutSec 30) {
        Write-Host '      [OK] Puerto 3100 en LISTEN.' -ForegroundColor Green
    } else {
        Write-Host '      [AVISO] No levantó en 3100 dentro de 30s.' -ForegroundColor DarkYellow
    }
    }
} else {
    Write-Host '[SKIP] No existe segundometro-agent\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 300

# --- 3) Agente correos primeros pagos (3110) ---
$corBat = Join-Path $BackendRoot 'services\correos-primeros-pagos-agent\iniciar-agente.bat'
if (-not $nodeExe) {
    Write-Host '[SKIP] Correos primeros pagos (sin Node).' -ForegroundColor DarkYellow
} elseif (Test-PortListening -Port 3110) {
    Write-Host '[3/5] Agente correos primeros pagos (3110) ya está activo.' -ForegroundColor Gray
} elseif (Test-Path -LiteralPath $corBat) {
    $corDir = Split-Path -Parent $corBat
    if (-not (Test-NodeAgentReady -AgentDir $corDir -AgentName 'Agente correos primeros pagos')) { }
    else {
    Write-Step '[3/5] Agente correos primeros pagos (puerto 3110)...'
    Start-Process -FilePath $corBat -WorkingDirectory $corDir -WindowStyle $winAgent
    if (Wait-PortListening -Port 3110 -TimeoutSec 25) {
        Write-Host '      [OK] Puerto 3110 en LISTEN.' -ForegroundColor Green
    } else {
        Write-Host '      [AVISO] No levantó en 3110 dentro de 25s.' -ForegroundColor DarkYellow
    }
    }
} else {
    Write-Host '[SKIP] No existe correos-primeros-pagos-agent\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 300

# --- 4) Agente Gastos cobranza (3120) ---
$gcBat = Join-Path $BackendRoot 'services\gastos-cobranza-agent\iniciar-agente.bat'
if (-not $nodeExe) {
    Write-Host '[SKIP] Gastos cobranza (sin Node).' -ForegroundColor DarkYellow
} elseif (Test-PortListening -Port 3120) {
    Write-Host '[4/5] Agente Gastos cobranza (3120) ya está activo.' -ForegroundColor Gray
} elseif (Test-Path -LiteralPath $gcBat) {
    $gcDir = Split-Path -Parent $gcBat
    if (-not (Test-NodeAgentReady -AgentDir $gcDir -AgentName 'Agente gastos cobranza')) { }
    else {
    Write-Step '[4/5] Agente Gastos cobranza (puerto 3120)...'
    Start-Process -FilePath $gcBat -WorkingDirectory $gcDir -WindowStyle $winAgent
    if (Wait-PortListening -Port 3120 -TimeoutSec 30) {
        Write-Host '      [OK] Puerto 3120 en LISTEN.' -ForegroundColor Green
    } else {
        Write-Host '      [AVISO] No levantó en 3120 dentro de 30s.' -ForegroundColor DarkYellow
    }
    }
} else {
    Write-Host '[SKIP] No existe gastos-cobranza-agent\iniciar-agente.bat' -ForegroundColor DarkYellow
}

Start-Sleep -Milliseconds 300

# --- 5) API Python verificacion documentos (uvicorn puerto 8001) ---
# Usar el flujo 1-click (Iniciar-API-Verificacion.bat) en vez del arranque
# oculto directo: el 1-click incluye bootstrap del Python portable, doctor
# con auto-fix y reintento. Asi, si alguna dependencia o el _socket.pyd
# faltan, se reparan solas en lugar de fallar silenciosamente.
$apiDir = Join-Path $BackendRoot 'API'
$apiOneClick = Join-Path $apiDir 'launcher\Iniciar-API-Verificacion.bat'
$apiOcultoPs1 = Join-Path $apiDir 'launcher\iniciar-agente-oculto.ps1'
$apiOcultoVbs = Join-Path $apiDir 'launcher\iniciar-agente-oculto.vbs'
if (Test-PortListening -Port 8001) {
    Write-Host '[5/5] API verificación documentos (8001) ya está activa.' -ForegroundColor Gray
} elseif (Test-Path -LiteralPath $apiOneClick) {
    Write-Step '[5/5] API verificación documentos (1-click con bootstrap -> 8001)...'
    # Si la maquina recien acaba de descargar Python portable, la primera
    # corrida puede instalar paquetes y tardar varios minutos. Por eso
    # ampliamos el timeout y -ademas- mostramos el progreso si hay consola.
    $apiArgs = @('/c', "`"$apiOneClick`"")
    if ($SinVentanas) {
        Start-Process -FilePath 'cmd.exe' -ArgumentList $apiArgs `
            -WorkingDirectory (Split-Path -Parent $apiOneClick) -WindowStyle Hidden
    } else {
        Start-Process -FilePath 'cmd.exe' -ArgumentList $apiArgs `
            -WorkingDirectory (Split-Path -Parent $apiOneClick) -WindowStyle Minimized
    }
    if (Wait-PortListening -Port 8001 -TimeoutSec 90) {
        Write-Host '      [OK] Puerto 8001 en LISTEN.' -ForegroundColor Green
    } else {
        Write-Host '      [AVISO] No levanto en 8001 dentro de 90s. Si es la primera vez puede seguir instalando dependencias.' -ForegroundColor DarkYellow
        Write-Host '             Logs: %TEMP%\sparta___SPARTA_SECRET_REDACTED___api_logs\bootstrap-python-*.log y instalar-*.log' -ForegroundColor DarkYellow
    }
} elseif (Test-Path -LiteralPath $apiOcultoPs1) {
    Write-Step '[5/5] API verificación documentos (PS oculto -> 8001)...'
    Start-Process -FilePath 'powershell.exe' -ArgumentList @(
        '-NoProfile', '-ExecutionPolicy', 'Bypass', '-WindowStyle', 'Hidden', '-File', $apiOcultoPs1
    ) -WorkingDirectory $apiDir -WindowStyle Hidden
    if (Wait-PortListening -Port 8001 -TimeoutSec 35) {
        Write-Host '      [OK] Puerto 8001 en LISTEN.' -ForegroundColor Green
    } else {
        Write-Host '      [AVISO] No levantó en 8001 dentro de 35s.' -ForegroundColor DarkYellow
    }
} elseif (Test-Path -LiteralPath $apiOcultoVbs) {
    Write-Step '[5/5] API verificación documentos (solo .vbs -> 8001)...'
    Start-Process -FilePath 'wscript.exe' -ArgumentList @('//nologo', $apiOcultoVbs) -WorkingDirectory $apiDir -WindowStyle Hidden
    if (Wait-PortListening -Port 8001 -TimeoutSec 35) {
        Write-Host '      [OK] Puerto 8001 en LISTEN.' -ForegroundColor Green
    } else {
        Write-Host '      [AVISO] No levantó en 8001 dentro de 35s.' -ForegroundColor DarkYellow
    }
} else {
    Write-Host '[SKIP] No hay API\launcher\Iniciar-API-Verificacion.bat ni iniciar-agente-oculto.ps1/.vbs' -ForegroundColor DarkYellow
}

Write-Host ''
Write-Host 'Resumen de puertos esperados:' -ForegroundColor Green
Write-Host '  3001  documentacion candidato (Node)'
Write-Host '  3100  agente Segundometro (Node)'
Write-Host '  3110  agente correos primeros pagos (Node)'
Write-Host '  3120  agente Gastos cobranza (Node)'
Write-Host '  8001  API verificacion documentos (Python global o venv + uvicorn; Docker opcional)'
Write-Host ''
Write-Host 'Para detener: backend\services\servicios-locales\cerrar-todos-los-servicios.bat'
Write-Host ''
