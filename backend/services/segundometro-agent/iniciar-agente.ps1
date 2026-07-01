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
# Salida temporal fuera del proyecto: no dejar logs de runtime dentro del repo.
$dataDir = Join-Path ([System.IO.Path]::GetTempPath()) 'sparta___SPARTA_SECRET_REDACTED___segundometro_agent'
if (-not (Test-Path -LiteralPath $dataDir)) {
    New-Item -ItemType Directory -Path $dataDir -Force | Out-Null
}
$outLog = Join-Path $dataDir 'agente-node-out.log'
$errLog = Join-Path $dataDir 'agente-node-err.log'

# Start-Process con redireccion de stdout/stderr puede fallar en algunos Windows
# cuando existen variantes Path/PATH en el ambiente. Sin redireccion queda estable.
Add-Content -LiteralPath $outLog -Value ("[" + (Get-Date -Format 'yyyy-MM-dd HH:mm:ss') + "] Iniciando agente Segundometro: " + $js)
$arg = "`"$js`""
$proc = Start-Process -FilePath $node -ArgumentList $arg -WorkingDirectory $dir -WindowStyle Hidden -PassThru
Start-Sleep -Seconds 2
if ($proc.HasExited) {
    throw "El agente Node termino al arrancar. Codigo de salida: $($proc.ExitCode)"
}
