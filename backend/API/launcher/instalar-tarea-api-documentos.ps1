# Instala la API de verificacion documental como tarea programada de Windows.
# Objetivo: que uvicorn no dependa de la sesion RDP/usuario que pulso el boton web.

$ErrorActionPreference = 'Stop'

$here = $PSScriptRoot
if (-not $here) { $here = Split-Path -Parent $MyInvocation.MyCommand.Path }
$apiDir = Split-Path -Parent $here
$bat = Join-Path $here 'iniciar-agente.bat'
$taskName = 'Sparta API Verificacion Documentos'

if (-not (Test-Path -LiteralPath $bat)) {
    throw "No existe $bat"
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

$cmd = 'cmd.exe'
$args = '/d /c ""' + $bat + '""'

Write-Host ('[INFO] Instalando tarea: ' + $taskName)
Write-Host ('[INFO] API_DIR: ' + $apiDir)
Write-Host ('[INFO] Accion : ' + $cmd + ' ' + $args)

$create = schtasks /Create /F /TN $taskName /TR "$cmd $args" /SC ONSTART /RU SYSTEM /RL HIGHEST 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host $create
    throw 'No se pudo crear la tarea programada.'
}

Write-Host '[OK] Tarea instalada. Probando arranque...' -ForegroundColor Green
schtasks /Run /TN $taskName | Out-Host

$ok = $false
for ($i = 0; $i -lt 90; $i++) {
    try {
        $r = Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/v1/health' -UseBasicParsing -TimeoutSec 2
        if ($r.StatusCode -ge 200 -and $r.StatusCode -lt 500) {
            $ok = $true
            break
        }
    } catch {}
    Start-Sleep -Milliseconds 500
}

if ($ok) {
    Write-Host '[OK] API responde en http://127.0.0.1:8000/api/v1/health' -ForegroundColor Green
    Write-Host '[OK] Desde ahora el boton web usara esta tarea si la detecta.'
    exit 0
}

Write-Host '[WARN] La tarea se instalo, pero la API no respondio aun. Revise logs\api_oculto_startup.log y logs\uvicorn-stderr.log.' -ForegroundColor Yellow
exit 2
