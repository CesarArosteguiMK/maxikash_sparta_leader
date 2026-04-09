# Arranca uvicorn en 8000 sin usar iniciar-agente.bat (sin ventana CMD).
# Lo invoca iniciar-agente-oculto.vbs y el orquestador en modo sin ventanas.

$ErrorActionPreference = 'Stop'
$ApiDir = $PSScriptRoot
$logDir = Join-Path $ApiDir 'logs'
$logFile = Join-Path $logDir 'api_oculto_startup.log'

function Write-Log {
    param([string] $Msg)
    try {
        if (-not (Test-Path -LiteralPath $logDir)) {
            New-Item -ItemType Directory -Path $logDir -Force | Out-Null
        }
        $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
        Add-Content -LiteralPath $logFile -Value "[$ts] $Msg" -Encoding UTF8
    } catch {}
}

$mainPy = Join-Path $ApiDir 'app\main.py'
if (-not (Test-Path -LiteralPath $mainPy)) {
    Write-Log 'ERROR: No esta app\main.py en la carpeta API.'
    exit 1
}

$pyExe = $null
$pyArgs = @()
if (Test-Path -LiteralPath (Join-Path $ApiDir 'venv\Scripts\python.exe')) {
    $pyExe = Join-Path $ApiDir 'venv\Scripts\python.exe'
    Write-Log 'INFO: Usando Python de venv.'
} else {
    try {
        & py -3 -c "import sys" *> $null
        if ($LASTEXITCODE -eq 0) {
            $pyExe = 'py'
            $pyArgs = @('-3')
            Write-Log 'INFO: Usando Python global (py -3).'
        }
    } catch {}
    if (-not $pyExe) {
        try {
            & python -c "import sys" *> $null
            if ($LASTEXITCODE -eq 0) {
                $pyExe = 'python'
                Write-Log 'INFO: Usando Python global (python).'
            }
        } catch {}
    }
}
if (-not $pyExe) {
    Write-Log 'ERROR: No se encontro Python (ni venv ni global).'
    exit 1
}

$listening = $false
try {
    $conns = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
    if ($conns) { $listening = $true }
} catch {}
if (-not $listening) {
    foreach ($line in (netstat -ano 2>$null)) {
        if ($line -match 'LISTENING' -and $line -match ':8000\s') {
            $listening = $true
            break
        }
    }
}
if ($listening) {
    Write-Log 'OK: Puerto 8000 ya en LISTEN; no se inicia de nuevo.'
    exit 0
}

$argList = @()
if ($pyArgs.Count -gt 0) { $argList += $pyArgs }
$argList += @(
    '-m', 'uvicorn',
    'app.main:app',
    '--host', '0.0.0.0',
    '--port', '8000'
)
try {
    Start-Process -FilePath $pyExe -ArgumentList $argList -WorkingDirectory $ApiDir -WindowStyle Hidden
    Write-Log 'OK: uvicorn lanzado en segundo plano (sin ventana).'
} catch {
    Write-Log "ERROR: $($_.Exception.Message)"
    exit 1
}
exit 0
