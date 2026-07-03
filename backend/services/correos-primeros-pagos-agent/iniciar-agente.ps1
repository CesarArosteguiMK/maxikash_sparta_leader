# Arranca index.js sin ventana. Lo invoca iniciar-agente.bat (variables NODE_EXE y AGENT_DIR).
$ErrorActionPreference = 'Stop'
$node = $env:NODE_EXE
$dir = $env:AGENT_DIR
if (-not $node -or -not $dir) {
    Write-Error 'NODE_EXE y AGENT_DIR deben estar definidos (use iniciar-agente.bat).'
    exit 1
}
$js = Join-Path $dir 'index.js'
if (-not (Test-Path -LiteralPath $js)) {
    Write-Error "No existe index.js en $dir"
    exit 1
}

$dataDir = Join-Path ([System.IO.Path]::GetTempPath()) 'sparta___SPARTA_SECRET_REDACTED___correos_primeros_pagos_agent'
if (-not (Test-Path -LiteralPath $dataDir)) {
    New-Item -ItemType Directory -Path $dataDir -Force | Out-Null
}
$outLog = Join-Path $dataDir 'agente-node-out.log'

Add-Content -LiteralPath $outLog -Value ("[" + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + "] Iniciando agente correos primeros pagos: " + $js)
$arg = "`"$js`""
$proc = Start-Process -FilePath $node -ArgumentList $arg -WorkingDirectory $dir -WindowStyle Hidden -PassThru
Start-Sleep -Seconds 2
if ($proc.HasExited) {
    throw "El agente Node termino al arrancar. Codigo de salida: $($proc.ExitCode)"
}
