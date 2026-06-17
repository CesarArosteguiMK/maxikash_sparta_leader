# Instala la API de verificacion documental como tarea programada de Windows.
# Objetivo: que uvicorn no dependa de la sesion RDP/usuario que pulso el boton web.

$ErrorActionPreference = 'Stop'

$here = $PSScriptRoot
if (-not $here) { $here = Split-Path -Parent $MyInvocation.MyCommand.Path }
$apiDir = Split-Path -Parent $here
$bat = Join-Path $here 'iniciar-agente-tarea.bat'
$supervisor = Join-Path $here 'supervisar-api-documentos.ps1'
$taskName = 'Sparta API Verificacion Documentos'
$runtimeDir = Join-Path $apiDir 'runtime'
$portFile = Join-Path $runtimeDir 'api-port.txt'
$apiPort = 8000
try {
    if ($env:SPARTA_API_PORT) {
        $candidate = [int]$env:SPARTA_API_PORT
        if ($candidate -gt 0) { $apiPort = $candidate }
    } elseif (Test-Path -LiteralPath $portFile) {
        $raw = (Get-Content -LiteralPath $portFile -Raw -ErrorAction SilentlyContinue).Trim()
        if ($raw -match '^\d+$') {
            $candidate = [int]$raw
            if ($candidate -gt 0) { $apiPort = $candidate }
        }
    }
} catch {
    $apiPort = 8000
}
try {
    New-Item -ItemType Directory -Path $runtimeDir -Force | Out-Null
    Set-Content -LiteralPath $portFile -Value ([string]$apiPort) -Encoding ASCII
} catch {}

if (-not (Test-Path -LiteralPath $supervisor)) {
    throw "No existe $supervisor"
}

$isAdmin = $false
try {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($identity)
    $isAdmin = $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
} catch {}

if (-not $isAdmin) {
    Write-Host '[WARN] El proceso actual no tiene permisos de Administrador para crear una tarea como SYSTEM.' -ForegroundColor Yellow
    Write-Host '[WARN] El boton 1-click seguira con el arranque directo como respaldo, pero ese modo puede apagarse al cerrar sesion.'
    Write-Host '[WARN] Para que sea persistente, Apache/PHP debe poder crear tareas programadas o el servidor debe tener esta tarea preinstalada.'
    exit 1
}

$cmd = Join-Path $env:SystemRoot 'System32\WindowsPowerShell\v1.0\powershell.exe'
$args = '-NoProfile -ExecutionPolicy Bypass -File "' + $supervisor + '"'

function Grant-WebUserTaskAccess {
    param([Parameter(Mandatory)][string]$Name)

    $taskFile = Join-Path $env:WINDIR ('System32\Tasks\' + $Name)
    if (Test-Path -LiteralPath $taskFile) {
        Write-Host '[INFO] Concediendo lectura/ejecucion de la tarea a usuarios autenticados...'
        & icacls.exe $taskFile /grant '*S-1-5-11:RX' '*S-1-5-32-545:RX' | Out-Host
    }

    try {
        $svc = New-Object -ComObject Schedule.Service
        $svc.Connect()
        $root = $svc.GetFolder('\')
        $task = $root.GetTask($Name)
        $sddl = [string]$task.GetSecurityDescriptor(0)
        $aces = '(A;;GRGX;;;AU)(A;;GRGX;;;BU)'
        if ($sddl -and $sddl -notmatch ';;;AU' -and $sddl -match '^(.*?D:[A-Z]*)(.*)$') {
            $newSddl = $Matches[1] + $aces + $Matches[2]
            $task.SetSecurityDescriptor($newSddl, 0)
            Write-Host '[OK] Descriptor de seguridad de la tarea actualizado para el boton web.' -ForegroundColor Green
        } elseif ($sddl -match ';;;AU') {
            Write-Host '[OK] La tarea ya tenia permisos para usuarios autenticados.'
        } else {
            Write-Host '[WARN] No se pudo interpretar el SDDL de la tarea para anadir permisos.' -ForegroundColor Yellow
        }
    } catch {
        Write-Host ('[WARN] No se pudo ajustar el descriptor COM de la tarea: ' + $_.Exception.Message) -ForegroundColor Yellow
    }
}

Write-Host ('[INFO] Instalando tarea: ' + $taskName)
Write-Host ('[INFO] API_DIR: ' + $apiDir)
Write-Host ('[INFO] Puerto : ' + $apiPort)
Write-Host ('[INFO] Accion : ' + $cmd + ' ' + $args)

try {
    schtasks /End /TN $taskName 2>$null | Out-Null
} catch {}

$create = schtasks /Create /F /TN $taskName /TR "`"$cmd`" $args" /SC ONSTART /RU SYSTEM /RL HIGHEST 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host $create
    throw 'No se pudo crear la tarea programada.'
}

Grant-WebUserTaskAccess -Name $taskName

Write-Host '[OK] Tarea instalada. Probando arranque...' -ForegroundColor Green
$stopper = Join-Path $here 'cerrar-agente.ps1'
if (Test-Path -LiteralPath $stopper) {
    Write-Host ("[INFO] Liberando puerto $apiPort antes de probar la tarea...")
    try {
        $env:SPARTA_API_PORT = [string]$apiPort
        & $stopper -Silent
    } catch {
        Write-Host ('[WARN] No se pudo ejecutar cerrar-agente.ps1: ' + $_.Exception.Message) -ForegroundColor Yellow
    }
}
schtasks /Run /TN $taskName | Out-Host

$ok = $false
for ($i = 0; $i -lt 90; $i++) {
    try {
        $r = Invoke-WebRequest -Uri "http://127.0.0.1:$apiPort/api/v1/health" -UseBasicParsing -TimeoutSec 2
        if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) {
            $ok = $true
            break
        }
    } catch {}
    Start-Sleep -Milliseconds 500
}

if ($ok) {
    Write-Host "[OK] API responde en http://127.0.0.1:$apiPort/api/v1/health" -ForegroundColor Green
    Write-Host '[OK] Desde ahora el boton web usara esta tarea si la detecta.'
    exit 0
}

Write-Host '[WARN] La tarea se instalo, pero la API no respondio aun. Revise logs\api_oculto_startup.log y logs\uvicorn-stderr.log.' -ForegroundColor Yellow
exit 2
