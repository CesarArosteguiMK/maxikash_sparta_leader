# Detiene el proceso que escucha en el puerto 8000 (API verificacion documentos, uvicorn).
param(
    [switch]$Silent
)

$ErrorActionPreference = 'SilentlyContinue'
$port = 8000
$pids = New-Object 'System.Collections.Generic.HashSet[int]'

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

$killed = $false
foreach ($procId in @($pids)) {
    try {
        Stop-Process -Id $procId -Force -ErrorAction Stop
        $killed = $true
    } catch {}
}

if (-not $Silent) {
    if ($killed) {
        Write-Host "Listo: se detuvo el proceso en el puerto $port."
    } else {
        Write-Host "No habia ningun proceso escuchando en el puerto $port."
    }
}

exit 0
