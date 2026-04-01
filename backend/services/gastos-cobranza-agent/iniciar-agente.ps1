# Arranca server.js sin ventana. Lo invoca iniciar-agente.bat (variables NODE_EXE y AGENT_DIR).
$ErrorActionPreference = 'Stop'
$node = $env:NODE_EXE
$dir = $env:AGENT_DIR
if (-not $node -or -not $dir) {
    Write-Error 'NODE_EXE y AGENT_DIR deben estar definidos (use iniciar-agente.bat).'
    exit 1
}
$js = Join-Path $dir 'server.js'
if (-not (Test-Path -LiteralPath $js)) {
    Write-Error "No existe server.js en $dir"
    exit 1
}
Start-Process -FilePath $node -ArgumentList $js -WorkingDirectory $dir -WindowStyle Hidden
