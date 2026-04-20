# Instalacion API verificacion documentos (PowerShell).
# Default: instala en Python GLOBAL (sin venv).
# Opcion: -Venv para instalar dentro de .\venv

param(
    [switch] $Venv
)

$ErrorActionPreference = 'Stop'
$here = $PSScriptRoot
$ApiDir = $here
if (-not (Test-Path -LiteralPath (Join-Path $ApiDir 'requirements.txt'))) {
    $parent = Split-Path -Parent $here
    if (Test-Path -LiteralPath (Join-Path $parent 'requirements.txt')) {
        $ApiDir = $parent
    }
}
Set-Location -LiteralPath $ApiDir

if (-not (Test-Path -LiteralPath (Join-Path $ApiDir 'requirements.txt'))) {
    Write-Error "No se encontro requirements.txt en $ApiDir"
    exit 1
}

$pyExe = $null
$pyArg = $null
py -3 -c "import sys" *> $null
if ($LASTEXITCODE -eq 0) {
    $pyExe = 'py'
    $pyArg = '-3'
} else {
    python -c "import sys" *> $null
    if ($LASTEXITCODE -eq 0) {
        $pyExe = 'python'
    }
}
if (-not $pyExe) {
    Write-Host '[ERROR] Instale Python 3 y deje py -3 o python en PATH.' -ForegroundColor Red
    Write-Host '        https://www.python.org/downloads/' -ForegroundColor Yellow
    exit 1
}

$logs = Join-Path $ApiDir 'logs'
if (-not (Test-Path -LiteralPath $logs)) { New-Item -ItemType Directory -Path $logs | Out-Null }
$envEx = Join-Path $ApiDir '.env.example'
$envFi = Join-Path $ApiDir '.env'
if (-not (Test-Path -LiteralPath $envFi) -and (Test-Path -LiteralPath $envEx)) {
    Copy-Item -LiteralPath $envEx -Destination $envFi
    Write-Host '[OK] Copiado .env desde .env.example' -ForegroundColor Green
}

if ($Venv) {
    $venvPy = Join-Path $ApiDir 'venv\Scripts\python.exe'
    if (-not (Test-Path -LiteralPath $venvPy)) {
        Write-Host '[venv] Creando entorno virtual...' -ForegroundColor Cyan
        if ($pyArg) { & $pyExe $pyArg -m venv (Join-Path $ApiDir 'venv') } else { & $pyExe -m venv (Join-Path $ApiDir 'venv') }
        if ($LASTEXITCODE -ne 0) { throw 'No se pudo crear venv' }
    }
    if (-not (Test-Path -LiteralPath $venvPy)) { throw 'Falta venv\Scripts\python.exe' }
    Write-Host '[pip] Instalando requirements.txt en venv...' -ForegroundColor Cyan
    & $venvPy -m pip install --upgrade pip
    if ($LASTEXITCODE -ne 0) { throw 'pip upgrade fallo en venv' }
    & $venvPy -m pip install -r (Join-Path $ApiDir 'requirements.txt')
    if ($LASTEXITCODE -ne 0) { throw 'pip install fallo en venv' }
} else {
    Write-Host '[pip] Instalando requirements.txt en Python GLOBAL...' -ForegroundColor Cyan
    if ($pyArg) { & $pyExe $pyArg -m pip install --upgrade pip } else { & $pyExe -m pip install --upgrade pip }
    if ($LASTEXITCODE -ne 0) { throw 'pip upgrade global fallo' }
    if ($pyArg) { & $pyExe $pyArg -m pip install -r (Join-Path $ApiDir 'requirements.txt') } else { & $pyExe -m pip install -r (Join-Path $ApiDir 'requirements.txt') }
    if ($LASTEXITCODE -ne 0) {
        Write-Host '[AVISO] Fallo global; reintentando con --user...' -ForegroundColor DarkYellow
        if ($pyArg) { & $pyExe $pyArg -m pip install --user -r (Join-Path $ApiDir 'requirements.txt') } else { & $pyExe -m pip install --user -r (Join-Path $ApiDir 'requirements.txt') }
        if ($LASTEXITCODE -ne 0) { throw 'pip install global/--user fallo' }
    }
}

$tess = @(
    'C:\Program Files\Tesseract-OCR\tesseract.exe',
    'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe'
) | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1

Write-Host ''
if ($tess) {
    Write-Host '[OK] Tesseract detectado.' -ForegroundColor Green
} else {
    Write-Host '[IMPORTANTE] Instale Tesseract para Windows:' -ForegroundColor Yellow
    Write-Host '            https://github.com/UB-Mannheim/tesseract/wiki' -ForegroundColor Gray
}
Write-Host ''
if ($Venv) {
    Write-Host '[OK] Listo (venv). Arranque sin ventana: .\iniciar-agente-oculto.vbs  |  con consola: .\iniciar-agente.bat' -ForegroundColor Green
} else {
    Write-Host '[OK] Listo (global). Arranque sin ventana: .\iniciar-agente-oculto.vbs  |  con consola: .\iniciar-agente.bat' -ForegroundColor Green
}
