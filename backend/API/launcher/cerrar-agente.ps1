# Detiene la API de verificacion documentos.
# Cierra el proceso que escucha en el puerto 8001 y limpia uvicorns huerfanos
# de esta misma carpeta para evitar que quede codigo viejo en memoria.
param(
    [switch]$Silent
)

$ErrorActionPreference = 'SilentlyContinue'
$port = 8001
try {
    if ($env:SPARTA_API_PORT) { $port = [int]$env:SPARTA_API_PORT }
} catch {
    $port = 8001
}
$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$apiDir = Resolve-Path (Join-Path $here '..') -ErrorAction SilentlyContinue
$apiDirText = if ($apiDir) { $apiDir.Path } else { (Join-Path $here '..') }
$pids = New-Object 'System.Collections.Generic.HashSet[int]'
$protectedPids = New-Object 'System.Collections.Generic.HashSet[int]'
[void]$protectedPids.Add([int]$PID)

try {
    $selfProcMap = @{}
    Get-CimInstance Win32_Process -ErrorAction SilentlyContinue | ForEach-Object {
        if ($_.ProcessId -gt 0) { $selfProcMap[[int]$_.ProcessId] = $_ }
    }
    $curPid = [int]$PID
    for ($guard = 0; $guard -lt 12 -and $selfProcMap.ContainsKey($curPid); $guard++) {
        $parentPid = [int]$selfProcMap[$curPid].ParentProcessId
        if ($parentPid -le 0 -or $protectedPids.Contains($parentPid)) { break }
        [void]$protectedPids.Add($parentPid)
        $curPid = $parentPid
    }
} catch {}

try {
    Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue | ForEach-Object {
        if ($_.OwningProcess -gt 0) { [void]$pids.Add($_.OwningProcess) }
    }
} catch {}

if ($pids.Count -eq 0) {
    foreach ($line in (netstat -ano 2>$null)) {
        if ($line -notmatch 'LISTENING') { continue }
        if ($line -notmatch ":$port\s") { continue }
        $parts = $line.Trim() -split '\s+'
        if ($parts.Count -lt 2) { continue }
        $last = $parts[$parts.Count - 1]
        if ($last -match '^\d+$') {
            [void]$pids.Add([int]$last)
        }
    }
}

$portPids = @($pids)

try {
    $procList = @(Get-CimInstance Win32_Process -ErrorAction SilentlyContinue)
    $procById = @{}
    foreach ($p in $procList) {
        if ($p.ProcessId -gt 0) { $procById[[int]$p.ProcessId] = $p }
    }

    $procList | Where-Object {
        $cmd = [string]$_.CommandLine
        if ([string]::IsNullOrWhiteSpace($cmd)) { return $false }

        $isThisApiPython = $cmd -like "*$apiDirText*" -or $cmd -like "*backend\API\tools\PythonPortable\python.exe*" -or $cmd -like "*backend\API\venv\Scripts\python.exe*"
        $isPythonProcess = ([string]$_.Name) -match '^python'
        $isUvicornDocApi = $cmd -match 'uvicorn\s+app\.main:app' -or $cmd -match 'uvicorn.*app\.main:app' -or $cmd -match 'run-uvicorn-hidden\.cmd'
        $isPortApi = $cmd -match ('--port\s+' + [regex]::Escape([string]$port))
        $isLauncherCmd = $cmd -match 'iniciar-agente-foreground\.bat' -or $cmd -match 'run-uvicorn-hidden\.cmd'

        return ($isThisApiPython -and ($isPythonProcess -or $isUvicornDocApi -or $isPortApi)) -or ($cmd -like "*$apiDirText*" -and $isLauncherCmd)
    } | ForEach-Object {
        if ($_.ProcessId -gt 0) { [void]$pids.Add([int]$_.ProcessId) }
    }

    foreach ($procId in @($pids)) {
        $current = if ($procById.ContainsKey([int]$procId)) { $procById[[int]$procId] } else { $null }
        for ($i = 0; $i -lt 4 -and $current -and $current.ParentProcessId -gt 0; $i++) {
            $parentId = [int]$current.ParentProcessId
            if (-not $procById.ContainsKey($parentId)) { break }
            $parent = $procById[$parentId]
            $parentCmd = [string]$parent.CommandLine
            if ($parentCmd -like "*$apiDirText*" -or $parentCmd -match 'uvicorn.*app\.main:app' -or $parentCmd -match 'run-uvicorn-hidden\.cmd' -or $parentCmd -match 'iniciar-agente-foreground\.bat') {
                [void]$pids.Add($parentId)
                $current = $parent
                continue
            }
            break
        }
    }
} catch {}

try {
    Get-Process python -ErrorAction SilentlyContinue | Where-Object {
        $path = [string]$_.Path
        $path -like "*\sparta___SPARTA_SECRET_REDACTED__\backend\API\*" -or
        $path -like "*\backend\API\tools\PythonPortable\python.exe" -or
        $path -like "*\backend\API\venv\Scripts\python.exe"
    } | ForEach-Object {
        if ($_.Id -gt 0) { [void]$pids.Add([int]$_.Id) }
    }
} catch {}

$killed = $false
$failed = @()
foreach ($procId in @($pids)) {
    if ($protectedPids.Contains([int]$procId)) {
        $failed += "PID $procId omitido: pertenece al proceso actual del 1-click."
        continue
    }
    $taskkillFailure = $null
    $taskkill = Join-Path ${env:SystemRoot} 'System32\taskkill.exe'
    if (Test-Path -LiteralPath $taskkill) {
        try {
            $p = Start-Process -FilePath $taskkill `
                -ArgumentList @('/F', '/T', '/PID', $procId.ToString()) `
                -Wait -PassThru -WindowStyle Hidden -ErrorAction Stop
            if ($p.ExitCode -eq 0) {
                $killed = $true
                continue
            }
            $taskkillFailure = "PID $procId taskkill exit=$($p.ExitCode)"
        } catch {}
    }
    try {
        Stop-Process -Id $procId -Force -ErrorAction Stop
        $killed = $true
    } catch {
        $msg = [string]$_.Exception.Message
        if ($msg -match 'No se encuentra|Cannot find|not find|not found') {
            continue
        }
        try {
            $cimProc = Get-CimInstance Win32_Process -Filter "ProcessId=$procId" -ErrorAction Stop
            if ($cimProc) {
                $cimResult = Invoke-CimMethod -InputObject $cimProc -MethodName Terminate -ErrorAction Stop
                if ($cimResult -and [int]$cimResult.ReturnValue -eq 0) {
                    $killed = $true
                    continue
                }
                $failed += "PID $procId CIM Terminate return=$($cimResult.ReturnValue)"
            }
        } catch {
            $failed += "PID $procId CIM Terminate: $($_.Exception.Message)"
        }
        if ($taskkillFailure) { $failed += $taskkillFailure }
        $failed += "PID $procId Stop-Process: $msg"
    }
}

Start-Sleep -Milliseconds 1200
$stillListening = @()
try {
    Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue | ForEach-Object {
        if ($_.OwningProcess -gt 0) { $stillListening += $_.OwningProcess }
    }
} catch {
    foreach ($line in (netstat -ano 2>$null)) {
        if ($line -match 'LISTENING' -and $line -match ":$port\s") {
            $parts = $line.Trim() -split '\s+'
            if ($parts.Count -gt 0) { $stillListening += $parts[$parts.Count - 1] }
        }
    }
}

if ($stillListening.Count -gt 0) {
    foreach ($procId in @($stillListening | Sort-Object -Unique)) {
        if ($protectedPids.Contains([int]$procId)) {
            $failed += "PID $procId omitido en segundo intento: pertenece al proceso actual del 1-click."
            continue
        }
        $taskkill = Join-Path ${env:SystemRoot} 'System32\taskkill.exe'
        if (Test-Path -LiteralPath $taskkill) {
            try {
                Start-Process -FilePath $taskkill `
                    -ArgumentList @('/F', '/T', '/PID', ([string]$procId)) `
                    -Wait -WindowStyle Hidden -ErrorAction SilentlyContinue | Out-Null
            } catch {}
        }
        try { Stop-Process -Id ([int]$procId) -Force -ErrorAction SilentlyContinue } catch {}
        try {
            $cimProc = Get-CimInstance Win32_Process -Filter "ProcessId=$procId" -ErrorAction SilentlyContinue
            if ($cimProc) { Invoke-CimMethod -InputObject $cimProc -MethodName Terminate -ErrorAction SilentlyContinue | Out-Null }
        } catch {}
    }
    Start-Sleep -Milliseconds 1200
    $stillListening = @()
    try {
        Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue | ForEach-Object {
            if ($_.OwningProcess -gt 0) { $stillListening += $_.OwningProcess }
        }
    } catch {
        foreach ($line in (netstat -ano 2>$null)) {
            if ($line -match 'LISTENING' -and $line -match ":$port\s") {
                $parts = $line.Trim() -split '\s+'
                if ($parts.Count -gt 0) { $stillListening += $parts[$parts.Count - 1] }
            }
        }
    }
}

$healthStillUp = $false
try {
    $resp = Invoke-WebRequest -Uri "http://127.0.0.1:$port/api/v1/health" -UseBasicParsing -TimeoutSec 3 -ErrorAction Stop
    if ($resp.StatusCode -ge 200 -and $resp.StatusCode -lt 500) { $healthStillUp = $true }
} catch {}

if (-not $Silent) {
    if ($killed) {
        Write-Host "Listo: se detuvo el proceso en el puerto $port."
        $extra = @($pids | Where-Object { $portPids -notcontains $_ })
        if ($extra.Count -gt 0) {
            Write-Host ("Tambien se limpiaron procesos uvicorn residuales: " + (($extra | Sort-Object -Unique) -join ', '))
        }
    } elseif ($pids.Count -gt 0) {
        Write-Host ("[WARN] Se encontraron procesos de la API, pero Windows no permitio cerrarlos: " + (($pids | Sort-Object -Unique) -join ', '))
    } else {
        Write-Host "No habia ningun proceso escuchando en el puerto $port."
    }
    if ($stillListening.Count -gt 0 -or $healthStillUp) {
        Write-Host ("[WARN] El puerto $port sigue ocupado por PID(s): " + (($stillListening | Sort-Object -Unique) -join ', '))
        if ($healthStillUp) {
            Write-Host "[WARN] /api/v1/health sigue respondiendo; la API no se detuvo por completo."
        }
    } else {
        Write-Host "Verificado: el puerto $port quedo libre."
    }
    foreach ($f in $failed) {
        Write-Host "[WARN] $f"
    }
}

if ($stillListening.Count -gt 0 -or $healthStillUp) { exit 1 }
exit 0
