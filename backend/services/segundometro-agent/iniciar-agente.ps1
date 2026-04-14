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
# Salida a log (evita que STDOUT/STDERR sin consumidor cuelguen el proceso en algunos entornos Windows).
$dataDir = Join-Path $dir 'data'
if (-not (Test-Path -LiteralPath $dataDir)) {
    New-Item -ItemType Directory -Path $dataDir -Force | Out-Null
}
$outLog = Join-Path $dataDir 'agente-node-out.log'
$errLog = Join-Path $dataDir 'agente-node-err.log'
$arg = "`"$js`""
Start-Process -FilePath $node -ArgumentList $arg -WorkingDirectory $dir -WindowStyle Hidden `
    -RedirectStandardOutput $outLog -RedirectStandardError $errLog
