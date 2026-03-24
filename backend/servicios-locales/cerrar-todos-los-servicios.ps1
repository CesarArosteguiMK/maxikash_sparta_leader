# Detiene servicios locales: Node (3001, 3100, 3110) y Docker API (8000).
param(
    [Parameter(Mandatory = $true)]
    [string] $BackendRoot
)

$ErrorActionPreference = 'SilentlyContinue'
$BackendRoot = $BackendRoot.TrimEnd('/', '\')

Write-Host ''
Write-Host 'Sparta Ledger - deteniendo servicios...' -ForegroundColor Yellow
Write-Host ''

function Stop-Port {
    param([int] $Port)
    $pids = New-Object 'System.Collections.Generic.HashSet[int]'
    try {
        Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue | ForEach-Object {
            if ($_.OwningProcess -gt 0) { [void]$pids.Add($_.OwningProcess) }
        }
    } catch {}
    if ($pids.Count -eq 0) {
        foreach ($line in (netstat -ano 2>$null)) {
            if ($line -notmatch 'LISTENING') { continue }
            if ($line -notmatch ":$Port\s") { continue }
            $parts = $line.Trim() -split '\s+'
            if ($parts.Count -lt 2) { continue }
            $last = $parts[$parts.Count - 1]
            if ($last -match '^\d+$') { [void]$pids.Add([int]$last) }
        }
    }
    foreach ($procId in @($pids)) {
        try { Stop-Process -Id $procId -Force -ErrorAction SilentlyContinue } catch {}
    }
}

$ps3001 = Join-Path $BackendRoot 'API\documentacion-candidato\cerrar-agente.ps1'
if (Test-Path -LiteralPath $ps3001) {
    & powershell -NoProfile -ExecutionPolicy Bypass -File $ps3001 -Silent
    Write-Host '[OK] Puerto 3001 (documentacion candidato)' -ForegroundColor Gray
} else {
    Stop-Port -Port 3001
    Write-Host '[OK] Puerto 3001 (fallback)' -ForegroundColor Gray
}

$ps3100 = Join-Path $BackendRoot 'services\segundometro-agent\cerrar-agente.ps1'
if (Test-Path -LiteralPath $ps3100) {
    & powershell -NoProfile -ExecutionPolicy Bypass -File $ps3100 -Silent
    Write-Host '[OK] Puerto 3100 (Segundometro)' -ForegroundColor Gray
} else {
    Stop-Port -Port 3100
    Write-Host '[OK] Puerto 3100 (fallback)' -ForegroundColor Gray
}

$batCorreos = Join-Path $BackendRoot 'services\correos-primeros-pagos-agent\cerrar-agente.bat'
if (Test-Path -LiteralPath $batCorreos) {
    Start-Process -FilePath $batCorreos -WorkingDirectory (Split-Path -Parent $batCorreos) -WindowStyle Hidden -Wait
    Write-Host '[OK] Agente correos (PID file)' -ForegroundColor Gray
} else {
    Stop-Port -Port 3110
    Write-Host '[OK] Puerto 3110 (fallback)' -ForegroundColor Gray
}

$apiDir = Join-Path $BackendRoot 'API'
$compose = Join-Path $apiDir 'docker-compose.yml'
if (Test-Path -LiteralPath $compose) {
    try {
        & docker info 2>$null | Out-Null
        if ($LASTEXITCODE -eq 0) {
            $dd = Start-Process -FilePath 'cmd.exe' -ArgumentList '/c', 'docker compose down' -WorkingDirectory $apiDir -Wait -NoNewWindow -PassThru
            if ($dd.ExitCode -eq 0) {
                Write-Host '[OK] Docker compose down (API 8000)' -ForegroundColor Gray
            } else {
                Write-Host '[AVISO] docker compose down codigo' $dd.ExitCode -ForegroundColor DarkYellow
            }
        } else {
            Write-Host '[AVISO] Docker no responde; API 8000 no detenida por compose.' -ForegroundColor DarkYellow
        }
    } catch {
        Write-Host '[AVISO] No se pudo ejecutar docker compose down.' -ForegroundColor DarkYellow
    }
}

Write-Host ''
Write-Host 'Listo.' -ForegroundColor Green
Write-Host ''
