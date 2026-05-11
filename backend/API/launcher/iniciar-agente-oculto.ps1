# =====================================================================
#  iniciar-agente-oculto.ps1
#  Arranca uvicorn (puerto 8000) en segundo plano y SIN ventana, pero:
#   - Hace smoke import previo (from app.main import app) para no
#     enmascarar el error real cuando uvicorn no arranca.
#   - Captura stdout y stderr de uvicorn a logs/uvicorn-stdout.log y
#     logs/uvicorn-stderr.log (asi se ve el error de verdad si revienta).
#   - Espera hasta 20s a que el puerto 8000 quede LISTENING.
#   - Si no levanta, vuelca los ultimos errores en logs/api_oculto_startup.log
#     y devuelve exit code != 0.
# =====================================================================

$ErrorActionPreference = 'Stop'

$here = $PSScriptRoot
if (-not $here) { $here = Split-Path -Parent $MyInvocation.MyCommand.Path }
$ApiDir = $here
if (-not (Test-Path -LiteralPath (Join-Path $ApiDir 'app\main.py'))) {
    $parent = Split-Path -Parent $here
    if (Test-Path -LiteralPath (Join-Path $parent 'app\main.py')) { $ApiDir = $parent }
}
$logsDir   = Join-Path $ApiDir 'logs'
$startLog  = Join-Path $logsDir 'api_oculto_startup.log'
$outLog    = Join-Path $logsDir 'uvicorn-stdout.log'
$errLog    = Join-Path $logsDir 'uvicorn-stderr.log'

if (-not (Test-Path -LiteralPath $logsDir)) {
    try { New-Item -ItemType Directory -Path $logsDir -Force | Out-Null } catch {}
}

function Write-Start {
    param([string] $Msg)
    try {
        $ts = Get-Date -Format 'yyyy-MM-dd HH:mm:ss'
        Add-Content -LiteralPath $startLog -Value "[$ts] $Msg" -Encoding UTF8
    } catch {}
}

function Repair-ProcessPathEnvironment {
    try {
        $currentPath = $env:Path
        if ([string]::IsNullOrWhiteSpace($currentPath)) {
            $machinePath = [Environment]::GetEnvironmentVariable('Path', 'Machine')
            $userPath = [Environment]::GetEnvironmentVariable('Path', 'User')
            $currentPath = (($machinePath, $userPath) | Where-Object { -not [string]::IsNullOrWhiteSpace($_) }) -join ';'
        }
        [Environment]::SetEnvironmentVariable('PATH', $null, 'Process')
        [Environment]::SetEnvironmentVariable('Path', $currentPath, 'Process')
        $env:Path = $currentPath
    } catch {
        Write-Start "AVISO: no se pudo normalizar PATH/PATH duplicado: $($_.Exception.Message)"
    }
}

function Invoke-ExeCapture {
    param(
        [Parameter(Mandatory)] [string] $FilePath,
        [string[]] $ArgumentList = @(),
        [string] $WorkDir = $null
    )
    $tmpOut = [System.IO.Path]::GetTempFileName()
    $tmpErr = [System.IO.Path]::GetTempFileName()
    try {
        if ($WorkDir) { Push-Location -LiteralPath $WorkDir }
        & $FilePath @ArgumentList > $tmpOut 2> $tmpErr
        $exit = $LASTEXITCODE
        $stdout = (Get-Content -LiteralPath $tmpOut -Raw -ErrorAction SilentlyContinue)
        $stderr = (Get-Content -LiteralPath $tmpErr -Raw -ErrorAction SilentlyContinue)
        return [pscustomobject]@{
            ExitCode = $exit
            All      = (($stdout -as [string]) + "`n" + ($stderr -as [string]))
        }
    } finally {
        if ($WorkDir) { try { Pop-Location } catch {} }
        Remove-Item -LiteralPath $tmpOut, $tmpErr -ErrorAction SilentlyContinue
    }
}

Repair-ProcessPathEnvironment

Write-Start ('=' * 50)
Write-Start "Inicio (PID host PowerShell: $PID)"
Write-Start "ApiDir: $ApiDir"

# ---- 1) Verificar app/main.py ----
$mainPy = Join-Path $ApiDir 'app\main.py'
if (-not (Test-Path -LiteralPath $mainPy)) {
    Write-Start "ERROR: No esta app\main.py. Revise la carpeta API."
    exit 1
}

# ---- 2) Resolver Python (venv > portable sin PATH > py > python en PATH) ----
. (Join-Path $here '_resolve_python.ps1')
$pyResolve = Resolve-SpartaApiPython -ApiDir $ApiDir
$pyExe = $null
$pyArgs = @()
if ($pyResolve) {
    $pyExe = $pyResolve.Exe
    $pyArgs = [string[]]$pyResolve.Args
    Write-Start "Python: $($pyResolve.Source) ($pyExe)"
}
if (-not $pyExe) {
    Write-Start 'ERROR: No se encontro Python (ni venv, ni portable tools\, ni py -3, ni python).'
    Write-Start 'SOLUCION sin instalador/PATH: ponga Python 3.12 en API\tools\PythonPortable\ o una linea en launcher\PYTHON_EXE.txt'
    Write-Start 'Ejecute launcher\Diagnosticar-API.bat para ver el detalle.'
    exit 1
}

# ---- 3) Si ya hay algo escuchando en 8000, no relanzar ----
$alreadyListening = $false
try {
    $c = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
    if ($c) { $alreadyListening = $true }
} catch {}
if (-not $alreadyListening) {
    foreach ($line in (netstat -ano 2>$null)) {
        if ($line -match 'LISTENING' -and $line -match ':8000\s') { $alreadyListening = $true; break }
    }
}
if ($alreadyListening) {
    Write-Start 'OK: Puerto 8000 ya esta en LISTEN; no se inicia de nuevo.'
    exit 0
}

# ---- 4) Smoke import: si la app no se importa, NO arrancar a ciegas ----
$smokeScript = Join-Path $here '_smoke_import.py'
$smokeOut = ''
$smokeRc  = 1
if (Test-Path -LiteralPath $smokeScript) {
    $sArgs = @()
    if ($pyArgs.Count -gt 0) { $sArgs += $pyArgs }
    $sArgs += $smokeScript
    try {
        $smokeRun = Invoke-ExeCapture -FilePath $pyExe -ArgumentList $sArgs -WorkDir $ApiDir
        $smokeRc = $smokeRun.ExitCode
        $smokeOut = $smokeRun.All
    } catch {
        $smokeOut = "Excepcion al lanzar smoke import: $($_.Exception.Message)"
    }
} else {
    Write-Start "AVISO: no se encontro $smokeScript ; smoke import omitido."
    $smokeRc = 0
    $smokeOut = 'SMOKE_OK (omitido)'
}
if ($smokeRc -ne 0 -or $smokeOut -notmatch 'SMOKE_OK') {
    Write-Start 'ERROR: smoke import fallo. uvicorn no podria arrancar la app. Detalle:'
    foreach ($l in ($smokeOut -split "`r?`n")) {
        if ($l.Trim()) { Write-Start ('  ' + $l.TrimEnd()) }
    }
    Write-Start 'SOLUCION: ejecute launcher\Diagnosticar-API.bat para auto-diagnosticar (use /FIX o /INSTALL).'
    exit 1
}
Write-Start 'OK: smoke import correcto.'

# ---- 5) Lanzar uvicorn capturando stdout y stderr ----
$argList = @()
if ($pyArgs.Count -gt 0) { $argList += $pyArgs }
$argList += @('-m','uvicorn','app.main:app','--host','0.0.0.0','--port','8000')

# Truncar logs anteriores (mantener historial seria sumar tamano sin control).
try { Set-Content -LiteralPath $outLog -Value '' -Encoding UTF8 } catch {}
try { Set-Content -LiteralPath $errLog -Value '' -Encoding UTF8 } catch {}

try {
    $proc = Start-Process -FilePath $pyExe -ArgumentList $argList -WorkingDirectory $ApiDir `
        -WindowStyle Hidden -PassThru `
        -RedirectStandardOutput $outLog `
        -RedirectStandardError  $errLog
    Write-Start "OK: uvicorn lanzado (PID $($proc.Id)). stdout=$outLog stderr=$errLog"
} catch {
    Write-Start "ERROR: no se pudo lanzar Python: $($_.Exception.Message)"
    exit 1
}

# ---- 6) Esperar hasta 20s a que 8000 quede LISTENING ----
$ok = $false
for ($i = 0; $i -lt 90; $i++) {
    Start-Sleep -Milliseconds 500
    try {
        $c = Get-NetTCPConnection -LocalPort 8000 -State Listen -ErrorAction SilentlyContinue
        if ($c) { $ok = $true; break }
    } catch {}
    try {
        foreach ($line in (netstat -ano 2>$null)) {
            if ($line -match 'LISTENING' -and $line -match ':8000\s') { $ok = $true; break }
        }
        if ($ok) { break }
    } catch {}
    try {
        $resp = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/docs' -UseBasicParsing -TimeoutSec 2 -ErrorAction Stop
        if ($resp.StatusCode -eq 200) { $ok = $true; break }
    } catch {}
    # Si el proceso murio, no tiene sentido seguir esperando.
    if ($proc.HasExited) { break }
}

if (-not $ok) {
    Write-Start 'ERROR: uvicorn no quedo escuchando en :8000 tras 45s.'
    if ($proc.HasExited) {
        Write-Start "  ProcessExitCode: $($proc.ExitCode)"
    } else {
        Write-Start "  Proceso sigue vivo (PID $($proc.Id)) pero sin LISTEN. Posible bind a otra IP o tardando demasiado."
    }
    Write-Start '  --- Ultimas lineas de stderr ---'
    try {
        Get-Content -LiteralPath $errLog -Tail 40 -ErrorAction SilentlyContinue | ForEach-Object { Write-Start ('  ' + $_) }
    } catch {}
    Write-Start '  --- Ultimas lineas de stdout ---'
    try {
        Get-Content -LiteralPath $outLog -Tail 20 -ErrorAction SilentlyContinue | ForEach-Object { Write-Start ('  ' + $_) }
    } catch {}
    Write-Start 'SOLUCION: ejecute launcher\Diagnosticar-API.bat (o Diagnosticar-API.bat /FIX) para identificar la causa.'
    exit 1
}

Write-Start 'OK: API escuchando en http://127.0.0.1:8000  (docs: /docs).'
exit 0
