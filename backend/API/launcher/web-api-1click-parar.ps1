# Detiene trabajo colgado del flujo 1-click / API docs (solo procesos relacionados con esta carpeta API).
# Llamado desde la web usuario 878. No usar para matar otros Python del servidor fuera de este arbol.

param([string]$ApiDir = '')

$ErrorActionPreference = 'SilentlyContinue'
$port = 8000
try {
    if ($env:SPARTA_API_PORT) { $port = [int]$env:SPARTA_API_PORT }
} catch {
    $port = 8000
}

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
$runtimeDir = Join-Path $ApiDir 'runtime'
$stopFlag = Join-Path $runtimeDir 'api-supervisor-stop.flag'
$restartFlag = Join-Path $runtimeDir 'api-restart-request.flag'
$portFile = Join-Path $runtimeDir 'api-port.txt'
try {
    New-Item -ItemType Directory -Path $runtimeDir -Force | Out-Null
    if (-not $env:SPARTA_API_PORT -and (Test-Path -LiteralPath $portFile)) {
        $rawPort = (Get-Content -LiteralPath $portFile -Raw -ErrorAction SilentlyContinue).Trim()
        if ($rawPort -match '^\d+$') {
            $port = [int]$rawPort
        }
    }
    $env:SPARTA_API_PORT = [string]$port
    Set-Content -LiteralPath $stopFlag -Value ("requested_at=" + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + "`nrequested_by=web-api-1click-parar") -Encoding ASCII
    Remove-Item -LiteralPath $restartFlag -Force -ErrorAction SilentlyContinue
} catch {}
function Contains-ApiPath([string]$Text) {
    if (-not $Text) {
        return $false
    }
    return $inv.CompareInfo.IndexOf($Text.Replace('/', '\'), $norm, [System.Globalization.CompareOptions]::OrdinalIgnoreCase) -ge 0
}

Write-Host ('=== Parar ejecución 1-click / API docs ===')
Write-Host ('ApiDir: ' + $norm)
Write-Host ('[OK] Bandera de parada manual activa: ' + $stopFlag)
Write-Host ('[OK] Reinicios pendientes limpiados: ' + $restartFlag)
Write-Host ('---')

$taskName = 'Sparta API Verificacion Documentos'
try {
    schtasks /Query /TN $taskName 2>$null | Out-Null
    if ($LASTEXITCODE -eq 0) {
        schtasks /End /TN $taskName 2>$null | Out-Null
        Write-Host ('[OK] Intentado terminar tarea programada: ' + $taskName)
    }
} catch {}

# 1) Quitar UVICORN / lo que escuche en el puerto configurado.
$p8000 = Join-Path $here 'cerrar-agente.ps1'
if (Test-Path -LiteralPath $p8000) {
    try {
        & $p8000
        Write-Host "[OK] Intentado liberar puerto $port (cerrar-agente)."
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
        if ($p -and $p.ExitCode -eq 0) {
            [void]$killedPids.Add($ProcessId, $true)
            Write-Host "[OK] $Label PID=$ProcessId (taskkill /T)"
            return
        } else {
            $ec = if ($p) { $p.ExitCode } else { 'sin-proceso' }
            Write-Host "[WARN] No se pudo terminar PID $ProcessId ($Label). taskkill exit=$ec"
        }
    } catch {
        Write-Host "[WARN] No se pudo terminar PID $ProcessId ($Label)."
    }
    try {
        Stop-Process -Id $ProcessId -Force -ErrorAction Stop
        [void]$killedPids.Add($ProcessId, $true)
        Write-Host "[OK] $Label PID=$ProcessId (Stop-Process fallback)"
    } catch {
        try {
            $cimProc = Get-CimInstance Win32_Process -Filter "ProcessId=$ProcessId" -ErrorAction Stop
            if ($cimProc) {
                $cimResult = Invoke-CimMethod -InputObject $cimProc -MethodName Terminate -ErrorAction Stop
                if ($cimResult -and [int]$cimResult.ReturnValue -eq 0) {
                    [void]$killedPids.Add($ProcessId, $true)
                    Write-Host "[OK] $Label PID=$ProcessId (CIM Terminate)"
                    return
                }
                Write-Host "[WARN] CIM Terminate fallo PID $ProcessId ($Label): return=$($cimResult.ReturnValue)"
            }
        } catch {
            Write-Host "[WARN] CIM Terminate fallo PID $ProcessId ($Label): $($_.Exception.Message)"
        }
        Write-Host "[WARN] Stop-Process fallback fallo PID $ProcessId ($Label): $($_.Exception.Message)"
    }
}

# 2) cmd.exe lanzando nuestros .bat
$hintsCmd = @('web-api-1click-runner.bat', 'Iniciar-API-Verificacion.bat', 'iniciar-agente-foreground.bat', 'instalar-agente.bat', 'Diagnosticar-API.bat', 'Diagnosticar-api.bat')
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

# 5) Fallback para entornos donde PHP/PowerShell no puede leer Win32_Process/CommandLine:
# matar solo python.exe cuyo ejecutable vive dentro de esta carpeta API (venv o tools\PythonPortable).
Get-Process -ErrorAction SilentlyContinue |
    Where-Object {
        $_.ProcessName -match '^python' -and
        $_.Path -and
        (Contains-ApiPath $_.Path)
    } |
    ForEach-Object { Invoke-TaskKillTree -ProcessId ([int]$_.Id) -Label 'Python API por ruta' }

# Reescribe la bandera al final por si un supervisor viejo la consumio antes de terminar.
try {
    Set-Content -LiteralPath $stopFlag -Value ("requested_at=" + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + "`nrequested_by=web-api-1click-parar-final") -Encoding ASCII
    Remove-Item -LiteralPath $restartFlag -Force -ErrorAction SilentlyContinue
} catch {}

# 6) Confirmacion visible para el panel web.
$stillPort = $false
foreach ($line in (netstat -ano 2>$null)) {
    if ($line -match 'LISTENING' -and $line -match (':' + [regex]::Escape([string]$port) + '\s')) {
        $stillPort = $true
        Write-Host ("[WARN] Puerto $port sigue en LISTEN: " + $line.Trim())
    }
}

Write-Host '---'
if ($stillPort) {
    Write-Host '__FIN_PARAR__:1'
    exit 1
}
Write-Host '__FIN_PARAR__:0'
exit 0
