# Supervisor persistente de la API documental.
# Corre desde Task Scheduler como SYSTEM y atiende solicitudes del boton web
# mediante backend/API/runtime/api-restart-request.flag.

param([string]$ApiDir = '')

$ErrorActionPreference = 'SilentlyContinue'
$ProgressPreference = 'SilentlyContinue'

$here = $PSScriptRoot
if (-not $here) { $here = Split-Path -Parent $MyInvocation.MyCommand.Path }
if (-not $ApiDir) {
    $ApiDir = Split-Path -Parent $here
}

$logsDir = Join-Path ([System.IO.Path]::GetTempPath()) 'sparta___SPARTA_SECRET_REDACTED___api_logs'
$runtimeDir = Join-Path $ApiDir 'runtime'
$logPath = Join-Path $logsDir 'api-supervisor.log'
$restartFlag = Join-Path $runtimeDir 'api-restart-request.flag'
$stopFlag = Join-Path $runtimeDir 'api-supervisor-stop.flag'
$portFile = Join-Path $runtimeDir 'api-port.txt'

foreach ($p in @($logsDir, $runtimeDir)) {
    try { New-Item -ItemType Directory -Path $p -Force | Out-Null } catch {}
}

$PaddleHome = Join-Path $ApiDir '.paddle_home'
$PaddleCache = Join-Path $PaddleHome '.cache'
$PaddlexCache = Join-Path $ApiDir '.paddlex_cache_runtime'
foreach ($p in @($PaddleHome, $PaddleCache, $PaddlexCache)) {
    try { New-Item -ItemType Directory -Path $p -Force | Out-Null } catch {}
}
$env:USERPROFILE = $PaddleHome
$env:HOME = $PaddleHome
$env:XDG_CACHE_HOME = $PaddleCache
$env:PADDLE_HOME = $PaddleHome
$env:PADDLE_PDX_CACHE_HOME = $PaddlexCache
$env:PADDLE_PDX_ENABLE_MKLDNN_BYDEFAULT = 'False'
$env:FLAGS_use_mkldnn = '0'
$env:FLAGS_use_onednn = '0'

$resolvePython = Join-Path $here '_resolve_python.ps1'
if (Test-Path -LiteralPath $resolvePython) {
    . $resolvePython
}

function Write-SupervisorLog {
    param([string]$Message)
    try {
        if ((Test-Path -LiteralPath $logPath) -and ((Get-Item -LiteralPath $logPath).Length -gt 2097152)) {
            $old = Join-Path $logsDir 'api-supervisor.old.log'
            Remove-Item -LiteralPath $old -Force -ErrorAction SilentlyContinue
            Move-Item -LiteralPath $logPath -Destination $old -Force -ErrorAction SilentlyContinue
        }
        $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
        Add-Content -LiteralPath $logPath -Value "[$ts] $Message" -Encoding UTF8
    } catch {}
}

function Get-ApiPort {
    $port = 8000
    try {
        if ($env:SPARTA_API_PORT) {
            $candidate = [int]$env:SPARTA_API_PORT
            if ($candidate -gt 0) { return $candidate }
        }
    } catch {}
    try {
        if (Test-Path -LiteralPath $portFile) {
            $raw = (Get-Content -LiteralPath $portFile -Raw -ErrorAction SilentlyContinue).Trim()
            if ($raw -match '^\d+$') {
                $candidate = [int]$raw
                if ($candidate -gt 0) { return $candidate }
            }
        }
    } catch {}
    return $port
}

function Test-ApiHealth {
    $port = Get-ApiPort
    try {
        $r = Invoke-WebRequest -Uri "http://127.0.0.1:$port/api/v1/health" -UseBasicParsing -TimeoutSec 3 -ErrorAction Stop
        return ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500)
    } catch {
        return $false
    }
}

function Stop-Api {
    $stopper = Join-Path $here 'cerrar-agente.ps1'
    if (Test-Path -LiteralPath $stopper) {
        $env:SPARTA_API_PORT = [string](Get-ApiPort)
        Write-SupervisorLog ('Deteniendo API actual en puerto ' + $env:SPARTA_API_PORT + '...')
        try { & $stopper -Silent | Out-Null } catch { Write-SupervisorLog ('WARN cerrar-agente: ' + $_.Exception.Message) }
    }
}

function Start-Api {
    if (-not (Get-Command Resolve-SpartaApiPython -ErrorAction SilentlyContinue)) {
        Write-SupervisorLog 'ERROR no esta disponible Resolve-SpartaApiPython.'
        return $false
    }

    $resolved = Resolve-SpartaApiPython -ApiDir $ApiDir
    if (-not $resolved -or -not $resolved.Exe) {
        Write-SupervisorLog 'ERROR no se encontro Python viable para la API.'
        return $false
    }

    $outLog = Join-Path $logsDir 'uvicorn-stdout.log'
    $errLog = Join-Path $logsDir 'uvicorn-stderr.log'
    try { Set-Content -LiteralPath $outLog -Value '' -Encoding UTF8 } catch {}
    try { Set-Content -LiteralPath $errLog -Value '' -Encoding UTF8 } catch {}

    $argList = @()
    if ($resolved.Args) { $argList += [string[]]$resolved.Args }
    $port = Get-ApiPort
    $env:SPARTA_API_PORT = [string]$port
    $argList += @('-m', 'uvicorn', 'app.main:app', '--host', '0.0.0.0', '--port', ([string]$port), '--workers', '1')
    $env:PYTHONUNBUFFERED = '1'

    Write-SupervisorLog ('Arrancando API directo. Puerto=' + $port + ' Python=' + $resolved.Source + ' Exe=' + $resolved.Exe)
    try {
        $proc = Start-Process -FilePath $resolved.Exe `
            -ArgumentList $argList `
            -WorkingDirectory $ApiDir `
            -WindowStyle Hidden `
            -RedirectStandardOutput $outLog `
            -RedirectStandardError $errLog `
            -PassThru
        Write-SupervisorLog ('Proceso uvicorn enviado. PID=' + $proc.Id)
    } catch {
        Write-SupervisorLog ('ERROR al lanzar uvicorn: ' + $_.Exception.Message)
        return $false
    }

    for ($i = 0; $i -lt 90; $i++) {
        if (Test-ApiHealth) {
            Write-SupervisorLog ('API saludable en http://127.0.0.1:' + $port + '/api/v1/health')
            return $true
        }
        Start-Sleep -Milliseconds 500
    }

    Write-SupervisorLog 'ERROR la API no confirmo health despues del arranque.'
    return $false
}

function Ensure-Api {
    param([string]$Reason)
    if (Test-ApiHealth) { return $true }
    Write-SupervisorLog ('Health no disponible; intentando levantar API. Motivo: ' + $Reason)
    return (Start-Api)
}

Write-SupervisorLog ('Supervisor iniciado. PID=' + $PID + ' ApiDir=' + $ApiDir + ' Puerto=' + (Get-ApiPort))
[void](Ensure-Api -Reason 'startup')

$failures = 0
while ($true) {
    if (Test-Path -LiteralPath $stopFlag) {
        Write-SupervisorLog 'Stop flag detectado. Saliendo del supervisor.'
        Remove-Item -LiteralPath $stopFlag -Force -ErrorAction SilentlyContinue
        break
    }

    if (Test-Path -LiteralPath $restartFlag) {
        $detail = ''
        try { $detail = (Get-Content -LiteralPath $restartFlag -Raw -ErrorAction SilentlyContinue).Trim() } catch {}
        Write-SupervisorLog ('Restart flag detectado. ' + $detail)
        Remove-Item -LiteralPath $restartFlag -Force -ErrorAction SilentlyContinue
        Stop-Api
        [void](Start-Api)
        $failures = 0
        Start-Sleep -Seconds 2
        continue
    }

    if (Test-ApiHealth) {
        $failures = 0
    } else {
        $failures++
        if ($failures -ge 3) {
            [void](Ensure-Api -Reason 'health-check-loop')
            $failures = 0
        }
    }

    Start-Sleep -Seconds 5
}

Write-SupervisorLog 'Supervisor terminado.'
