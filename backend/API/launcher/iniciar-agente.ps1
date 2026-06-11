# Arranca uvicorn sin ventana. Lo invoca iniciar-agente.bat.
$ErrorActionPreference = 'Stop'
$py = $env:PYTHON_EXE
$pyArg = $env:PYTHON_EXE_ARG
$dir = $env:API_DIR

if (-not $dir) {
    Write-Error 'API_DIR debe estar definido (use iniciar-agente.bat).'
    exit 1
}
if (-not $py) {
    Write-Error 'PYTHON_EXE debe estar definido (use iniciar-agente.bat).'
    exit 1
}

# Si PYTHON_EXE es ruta (venv), validar que exista.
if (($py -match '[\\/]') -or ($py -match '\.exe$')) {
    if (-not (Test-Path -LiteralPath $py)) {
        Write-Error "No existe Python configurado: $py"
        exit 1
    }
}

$argList = @()
if ($pyArg) { $argList += $pyArg }
$argList += @(
    '-m', 'uvicorn',
    'app.main:app',
    '--host', '0.0.0.0',
    '--port', '8000',
    '--workers', '1'
)

Start-Process -FilePath $py -ArgumentList $argList -WorkingDirectory $dir -WindowStyle Hidden
