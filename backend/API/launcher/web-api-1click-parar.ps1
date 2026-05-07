# Detiene trabajo colgado del flujo 1-click / API docs (solo procesos relacionados con esta carpeta API).
# Llamado desde la web usuario 878. No usar para matar otros Python del servidor fuera de este arbol.

param([string]$ApiDir = '')

$ErrorActionPreference = 'SilentlyContinue'

$here = $PSScriptRoot
if (-not $here) {
    $here = Split-Path -Parent $MyInvocation.MyCommand.Path
}
if (-not $ApiDir) {
    $ApiDir = Split-Path -Parent $here
    if (-not (Test-Path -LiteralPath (Join-Path $ApiDir 'app\main.py'))) {
        $parent = Split-Path -Parent $ApiDir
        if (Test-Path -LiteralPath (Join-Path $parent 'app\main.py')) {
            $ApiDir = $parent
        }
    }
}
$norm = ($ApiDir -replace '/', '\').TrimEnd('\')
$inv = [System.Globalization.CultureInfo]::InvariantCulture
function Contains-ApiPath([string]$Text) {
    if (-not $Text) {
        return $false
    }
    return $inv.CompareInfo.IndexOf($Text.Replace('/', '\'), $norm, [System.Globalization.CompareOptions]::OrdinalIgnoreCase) -ge 0
}

Write-Host ('=== Parar ejecución 1-click / API docs ===')
Write-Host ('ApiDir: ' + $norm)
Write-Host ('---')

# 1) Quitar UVICORN / lo que escuche en :8000 (solo esa API habitualmente usa 8000 en este stack)
$p8000 = Join-Path $here 'cerrar-agente.ps1'
if (Test-Path -LiteralPath $p8000) {
    try {
        & $p8000 -Silent
        Write-Host '[OK] Intentado liberar puerto 8000 (cerrar-agente).'
    } catch {
        Write-Host '[WARN] cerrar-agente: ' + $_.Exception.Message
    }
}

$killedPids = @{}

function Invoke-TaskKillTree([int]$ProcessId, [string]$Label) {
    if ($ProcessId -le 0 -or $killedPids.ContainsKey($ProcessId)) {
        return
    }
    $exe = Join-Path ${env:SystemRoot} 'System32\taskkill.exe'
    if (-not (Test-Path -LiteralPath $exe)) {
        try {
            Stop-Process -Id $ProcessId -Force -ErrorAction SilentlyContinue
        } catch {}
        [void]$killedPids.Add($ProcessId, $true)
        Write-Host "[OK] $Label PID=$ProcessId (Stop-Process)"
        return
    }
    try {
        $p = Start-Process -FilePath $exe `
            -ArgumentList @('/F', '/T', '/PID', $ProcessId.ToString()) `
            -Wait -PassThru -WindowStyle Hidden -NoNewWindow -ErrorAction SilentlyContinue
        [void]$killedPids.Add($ProcessId, $true)
        Write-Host "[OK] $Label PID=$ProcessId (taskkill /T)"
    } catch {
        Write-Host "[WARN] No se pudo terminar PID $ProcessId ($Label)."
    }
}

# 2) cmd.exe lanzando nuestros .bat
$hintsCmd = @('web-api-1click-runner.bat', 'Iniciar-API-Verificacion.bat', 'instalar-agente.bat', 'Diagnosticar-API.bat', 'Diagnosticar-api.bat')
Get-CimInstance Win32_Process -Filter "Name='cmd.exe'" -ErrorAction SilentlyContinue |
    Where-Object {
        $cl = $_.CommandLine
        if (-not $cl) {
            return $false
        }
        foreach ($h in $hintsCmd) {
            if ($cl -like "*${h}*") {
                return $true
            }
        }
        return $false
    } |
    ForEach-Object { Invoke-TaskKillTree -ProcessId ([int]$_.ProcessId) -Label 'CMD 1-click' }

# 3) PowerShell con doctor / arranque oculto del mismo proyecto
Get-CimInstance Win32_Process -Filter "Name='powershell.exe'" -ErrorAction SilentlyContinue |
    Where-Object {
        [int]$_.ProcessId -ne $PID -and (Contains-ApiPath $_.CommandLine) -and (
            $_.CommandLine -match 'doctor-api\.ps1|iniciar-agente-oculto\.ps1|Iniciar-API-Verificacion'
        )
    } |
    ForEach-Object { Invoke-TaskKillTree -ProcessId ([int]$_.ProcessId) -Label 'PowerShell lanzador' }

# 4) Python de ESTA instalación (venv/portable/pip/smoke); evita otros sitios si no aparece la ruta en CommandLine.
$pyNames = @('python.exe', 'pythonw.exe', 'python3.14t.exe', 'python3.exe')
foreach ($nm in $pyNames) {
    Get-CimInstance Win32_Process -Filter "Name='$nm'" -ErrorAction SilentlyContinue |
        Where-Object {
            Contains-ApiPath $_.CommandLine -and (
                $_.CommandLine -match 'uvicorn|pip|_smoke_import|launcher\\|launcher/|[\\/]launcher[\\/]'
            )
        } |
        ForEach-Object { Invoke-TaskKillTree -ProcessId ([int]$_.ProcessId) -Label "Python ($nm)" }
}

Write-Host '---'
Write-Host '__FIN_PARAR__:0'
exit 0
