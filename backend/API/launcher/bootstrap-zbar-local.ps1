param(
    [switch]$Force
)

$ErrorActionPreference = 'Stop'

$here = Split-Path -Parent $MyInvocation.MyCommand.Path
$apiDir = Split-Path -Parent $here
$targetDir = Join-Path $apiDir 'tools\zbar\bin'
if (-not (Test-Path -LiteralPath $targetDir)) {
    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
}

$required = @('libzbar-64.dll', 'libiconv.dll')
$sources = @(
    (Join-Path $env:WINDIR 'System32'),
    (Join-Path $env:WINDIR 'SysWOW64'),
    (Join-Path $apiDir 'tools'),
    (Join-Path $apiDir 'tools\zbar'),
    (Join-Path $apiDir 'tools\zbar\bin')
)

Write-Host "Destino local: $targetDir"
$copiedAny = $false
foreach ($name in $required) {
    $dst = Join-Path $targetDir $name
    $has = Test-Path -LiteralPath $dst
    if ($has -and -not $Force) {
        Write-Host "[OK] Ya existe: $name"
        continue
    }

    $found = $null
    foreach ($srcDir in $sources) {
        $src = Join-Path $srcDir $name
        if (Test-Path -LiteralPath $src) {
            $found = $src
            break
        }
    }

    if ($found) {
        Copy-Item -LiteralPath $found -Destination $dst -Force
        Write-Host "[OK] Copiado: $name  <=  $found"
        $copiedAny = $true
    } else {
        Write-Host "[WARN] No se encontro: $name"
    }
}

$missing = @()
foreach ($name in $required) {
    if (-not (Test-Path -LiteralPath (Join-Path $targetDir $name))) {
        $missing += $name
    }
}

if ($missing.Count -gt 0) {
    Write-Host ""
    Write-Host "[PENDIENTE] Faltan DLLs para pyzbar: $($missing -join ', ')" -ForegroundColor Yellow
    Write-Host "Colocalas en: $targetDir" -ForegroundColor Yellow
    Write-Host "Recomendado: bajar zbar x64 y copiar solo estas DLLs al proyecto (sin System32)."
    exit 1
}

if ($copiedAny) {
    Write-Host "[OK] zbar local listo en tools\\zbar\\bin"
} else {
    Write-Host "[OK] zbar local ya estaba listo en tools\\zbar\\bin"
}
exit 0
